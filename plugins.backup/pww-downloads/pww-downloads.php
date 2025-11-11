<?php
/**
 * Plugin Name: PWW Downloads
 * Plugin URI: https://pianowithwillie.com
 * Description: Download management for PianoWithWillie.com - MVP Version
 * Version: 1.0.0
 * Author: JazzEdge
 * Text Domain: pww-downloads
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin activation
register_activation_hook(__FILE__, 'pww_downloads_activate');
function pww_downloads_activate() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Product URLs table
    $table = $wpdb->prefix . 'pww_product_urls';
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        product_id bigint(20) unsigned NOT NULL,
        bunny_url text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY product_id (product_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Download logs table (for tracking counts)
    $table = $wpdb->prefix . 'pww_download_logs';
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        product_id bigint(20) unsigned NOT NULL,
        download_count int(11) NOT NULL DEFAULT 0,
        first_download_date datetime DEFAULT NULL,
        last_download_date datetime DEFAULT NULL,
        order_id bigint(20) unsigned DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_product (user_id, product_id),
        KEY user_id (user_id),
        KEY product_id (product_id)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Download history table (for individual download entries)
    $table = $wpdb->prefix . 'pww_download_history';
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        product_id bigint(20) unsigned NOT NULL,
        download_url text NOT NULL,
        ip_address varchar(45) DEFAULT NULL,
        user_agent text DEFAULT NULL,
        success tinyint(1) DEFAULT 1,
        error_message text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY product_id (product_id),
        KEY created_at (created_at),
        KEY success (success)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Set defaults
    if (!get_option('pww_downloads_webhook_secret')) {
        add_option('pww_downloads_webhook_secret', wp_generate_password(32, false));
    }
    if (!get_option('pww_downloads_max_downloads')) {
        add_option('pww_downloads_max_downloads', 3);
    }
    if (!get_option('pww_downloads_download_window_days')) {
        add_option('pww_downloads_download_window_days', 60);
    }
    
    flush_rewrite_rules();
}

// REST API endpoint - register early
add_action('rest_api_init', 'pww_downloads_register_routes', 10);
function pww_downloads_register_routes() {
    register_rest_route('pww-downloads/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'pww_downloads_handle_webhook',
        'permission_callback' => '__return_true'
    ));
    
    register_rest_route('pww-downloads/v1', '/download/(?P<token>[a-zA-Z0-9._%=-]+)', array(
        'methods' => 'GET',
        'callback' => 'pww_downloads_handle_download',
        'permission_callback' => '__return_true'
    ));
}

// Logging function
function pww_downloads_log($message, $data = null) {
    $log_file = WP_CONTENT_DIR . '/pww-downloads-log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message";
    if ($data !== null) {
        $log_entry .= "\n" . print_r($data, true);
    }
    $log_entry .= "\n" . str_repeat('-', 80) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Webhook handler
function pww_downloads_handle_webhook($request) {
    global $wpdb;
    
    // Log incoming request
    pww_downloads_log('=== WEBHOOK RECEIVED ===');
    pww_downloads_log('Headers:', $request->get_headers());
    pww_downloads_log('Method:', $request->get_method());
    pww_downloads_log('URL:', $request->get_route());
    
    // Check auth
    $secret = get_option('pww_downloads_webhook_secret', '');
    $provided = $request->get_header('X-Webhook-Secret');
    pww_downloads_log('Auth Check:', array('secret_set' => !empty($secret), 'provided' => $provided));
    
    if ($secret && $provided !== $secret) {
        pww_downloads_log('ERROR: Authentication failed');
        return new WP_Error('unauthorized', 'Invalid secret', array('status' => 401));
    }
    
    // Get body
    $body = $request->get_body();
    pww_downloads_log('Body length:', strlen($body));
    pww_downloads_log('Body (first 500 chars):', substr($body, 0, 500));
    
    $data = json_decode($body, true);
    pww_downloads_log('Parsed data keys:', $data ? array_keys($data) : 'Failed to parse JSON');
    
    if (!$data) {
        pww_downloads_log('ERROR: Failed to parse JSON body');
        return new WP_Error('invalid_json', 'Invalid JSON', array('status' => 400));
    }
    
    if (!isset($data['order'])) {
        pww_downloads_log('ERROR: No order data in webhook');
        return new WP_Error('no_order', 'No order data', array('status' => 400));
    }
    
    if (!isset($data['order']['payment_status']) || $data['order']['payment_status'] !== 'paid') {
        pww_downloads_log('INFO: Order not paid, skipping', array('payment_status' => $data['order']['payment_status'] ?? 'not set'));
        return rest_ensure_response(array('success' => true, 'message' => 'Order not paid'));
    }
    
    pww_downloads_log('Processing paid order:', array('order_id' => $data['order']['id'] ?? 'unknown'));
    
    $order = $data['order'];
    $customer = $data['customer'] ?? null;
    $order_items = $data['order_items'] ?? array();
    
    // Get customer email
    $email = $customer['email'] ?? $order['billing_address']['email'] ?? $order['shipping_address']['email'] ?? null;
    if (!$email) {
        return new WP_Error('no_email', 'No email found', array('status' => 400));
    }
    
    // Get user
    $user = get_user_by('email', $email);
    if (!$user && isset($customer['user_id'])) {
        $user = get_user_by('id', $customer['user_id']);
    }
    if (!$user) {
        return new WP_Error('no_user', 'User not found', array('status' => 404));
    }
    
    // Process items
    $products = array();
    $table = $wpdb->prefix . 'pww_product_urls';
    $logs_table = $wpdb->prefix . 'pww_download_logs';
    
    foreach ($order_items as $item) {
        if (!isset($item['post_id'])) continue;
        
        $product_id = intval($item['post_id']);
        $urls = $wpdb->get_results($wpdb->prepare("SELECT bunny_url FROM $table WHERE product_id = %d", $product_id));
        
        if (empty($urls)) continue;
        
        // Update download log
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $logs_table WHERE user_id = %d AND product_id = %d",
            $user->ID, $product_id
        ));
        
        $now = current_time('mysql');
        if ($exists) {
            $wpdb->update($logs_table, array(
                'download_count' => $wpdb->get_var($wpdb->prepare("SELECT download_count FROM $logs_table WHERE user_id = %d AND product_id = %d", $user->ID, $product_id)) + 1,
                'last_download_date' => $now
            ), array('user_id' => $user->ID, 'product_id' => $product_id));
        } else {
            $wpdb->insert($logs_table, array(
                'user_id' => $user->ID,
                'product_id' => $product_id,
                'download_count' => 1,
                'first_download_date' => $now,
                'last_download_date' => $now,
                'order_id' => $order['id'] ?? null
            ));
        }
        
        $products[] = array(
            'product_id' => $product_id,
            'title' => $item['post_title'] ?? 'Product',
            'urls' => array_column($urls, 'bunny_url')
        );
    }
    
    if (empty($products)) {
        return rest_ensure_response(array('success' => true, 'message' => 'No products with URLs'));
    }
    
    // Send email with download links for each product
    $subject = get_option('pww_downloads_email_subject', 'Your PianoWithWillie.com Downloads');
    $message = "Hello " . ($user->display_name ?: $user->user_email) . ",\n\n";
    $message .= "Your download links are ready:\n\n";
    
    foreach ($products as $product) {
        // Create token for each product
        $token_data = base64_encode(json_encode(array('user_id' => $user->ID, 'product_id' => $product['product_id'], 'time' => time())));
        $signature = hash_hmac('sha256', $token_data, $secret);
        $token = $token_data . '.' . $signature;
        $download_url = rest_url('pww-downloads/v1/download/' . urlencode($token));
        
        $message .= $product['title'] . ":\n";
        $message .= $download_url . "\n\n";
    }
    $message .= "You can download each product up to " . get_option('pww_downloads_max_downloads', 3) . " times within " . get_option('pww_downloads_download_window_days', 60) . " days.\n\n";
    $message .= "Thank you!";
    
    $email_result = wp_mail($user->user_email, $subject, $message);
    pww_downloads_log('Email sent:', array('to' => $user->user_email, 'success' => $email_result, 'products' => count($products)));
    
    return rest_ensure_response(array('success' => true, 'message' => 'Email sent', 'products' => count($products)));
}

// Helper function to log download attempt
function pww_downloads_log_download($user_id, $product_id, $download_url, $success, $error_message = null) {
    global $wpdb;
    $history_table = $wpdb->prefix . 'pww_download_history';
    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null;
    
    $wpdb->insert($history_table, array(
        'user_id' => $user_id,
        'product_id' => $product_id,
        'download_url' => $download_url,
        'ip_address' => $ip_address,
        'user_agent' => $user_agent,
        'success' => $success ? 1 : 0,
        'error_message' => $error_message
    ));
}

// Download handler
function pww_downloads_handle_download($request) {
    global $wpdb;
    
    // Get token and URL decode it (in case = was encoded as %3D)
    $token = urldecode($request->get_param('token'));
    $parts = explode('.', $token);
    if (count($parts) !== 2) {
        wp_die('Invalid download link. Please use the link from your email.');
    }
    
    $token_data = $parts[0];
    $data = json_decode(base64_decode($token_data), true);
    $secret = get_option('pww_downloads_webhook_secret', '');
    
    // Validate token signature
    $expected_signature = hash_hmac('sha256', $token_data, $secret);
    if (!hash_equals($expected_signature, $parts[1])) {
        // Try to log if we can extract user/product from token
        if ($data && isset($data['user_id']) && isset($data['product_id'])) {
            pww_downloads_log_download($data['user_id'], $data['product_id'], '', false, 'Invalid token signature');
        }
        wp_die('Invalid download link. Please use the link from your email.');
    }
    
    // Validate required data
    if (!$data || !isset($data['user_id']) || !isset($data['product_id'])) {
        wp_die('Invalid download link. Please use the link from your email.');
    }
    
    $user_id = intval($data['user_id']);
    $product_id = intval($data['product_id']);
    
    // Check token age (24 hours)
    if (isset($data['time']) && (time() - $data['time']) > DAY_IN_SECONDS) {
        pww_downloads_log_download($user_id, $product_id, '', false, 'Token expired');
        wp_die('Download link has expired. Please use the link from your email or contact support.');
    }
    
    // Verify user exists (but don't require them to be logged in)
    $user = get_user_by('id', $user_id);
    if (!$user) {
        pww_downloads_log_download($user_id, $product_id, '', false, 'User not found');
        wp_die('Invalid download link. User not found.');
    }
    
    // Check download limits
    $logs_table = $wpdb->prefix . 'pww_download_logs';
    $urls_table = $wpdb->prefix . 'pww_product_urls';
    
    $max_downloads = get_option('pww_downloads_max_downloads', 3);
    $window_days = get_option('pww_downloads_download_window_days', 60);
    
    // Get download log - create if it doesn't exist (for first download)
    $log = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $logs_table WHERE user_id = %d AND product_id = %d",
        $user_id, $product_id
    ));
    
    // If no log exists, create one (this happens when they click the link for the first time)
    if (!$log) {
        $now = current_time('mysql');
        $wpdb->insert($logs_table, array(
            'user_id' => $user_id,
            'product_id' => $product_id,
            'download_count' => 0,
            'first_download_date' => null,
            'last_download_date' => null
        ));
        // Get the newly created log
        $log = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $logs_table WHERE user_id = %d AND product_id = %d",
            $user_id, $product_id
        ));
    }
    
    // Check download count
    if ($log->download_count >= $max_downloads) {
        pww_downloads_log_download($user_id, $product_id, '', false, 'Download limit exceeded (' . $max_downloads . ' max)');
        wp_die('You have reached the maximum download limit of ' . $max_downloads . ' times for this product.');
    }
    
    // Check time window
    if ($log->first_download_date) {
        $days = (time() - strtotime($log->first_download_date)) / DAY_IN_SECONDS;
        if ($days > $window_days) {
            pww_downloads_log_download($user_id, $product_id, '', false, 'Download window expired (' . $window_days . ' days)');
            wp_die('Your download window of ' . $window_days . ' days has expired. Please contact support.');
        }
    }
    
    // Get URLs for this product
    $urls = $wpdb->get_results($wpdb->prepare(
        "SELECT bunny_url FROM $urls_table WHERE product_id = %d",
        $product_id
    ));
    
    if (empty($urls)) {
        pww_downloads_log_download($user_id, $product_id, '', false, 'No URLs configured for product');
        wp_die('No download URLs configured for this product.');
    }
    
    // Increment count
    $wpdb->update($logs_table, array(
        'download_count' => $log->download_count + 1,
        'last_download_date' => current_time('mysql')
    ), array('id' => $log->id));
    
    // Log this successful download to history
    $download_url = count($urls) === 1 ? $urls[0]->bunny_url : 'Multiple files';
    pww_downloads_log_download($user_id, $product_id, $download_url, true);
    
    // Redirect to first URL (or show selection if multiple)
    if (count($urls) === 1) {
        wp_redirect($urls[0]->bunny_url);
        exit;
    } else {
        // Multiple URLs - show selection page
        echo '<!DOCTYPE html><html><head><title>Download Files</title></head><body>';
        echo '<h1>Select a file to download:</h1><ul>';
        foreach ($urls as $url) {
            echo '<li><a href="' . esc_url($url->bunny_url) . '" download>Download File</a></li>';
        }
        echo '</ul></body></html>';
        exit;
    }
}

// Admin menu
add_action('admin_menu', function() {
    add_menu_page('PWW Downloads', 'PWW Downloads', 'manage_options', 'pww-downloads', 'pww_downloads_admin_page', 'dashicons-download', 30);
});

// Enqueue dashicons for admin
add_action('admin_enqueue_scripts', function($hook) {
    if (strpos($hook, 'pww-downloads') !== false) {
        wp_enqueue_style('dashicons');
    }
});

// Admin page
function pww_downloads_admin_page() {
    global $wpdb;
    
    // Show success message if URL was saved
    if (isset($_GET['url_saved']) && $_GET['url_saved'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>URL saved successfully!</p></div>';
    }
    
    // Handle table creation
    if (isset($_POST['create_tables']) && check_admin_referer('pww_create_tables')) {
        pww_downloads_activate();
        echo '<div class="notice notice-success"><p>Database tables created successfully!</p></div>';
    }
    
    // Handle log actions
    if (isset($_POST['clear_log']) && check_admin_referer('pww_clear_log')) {
        $log_file = WP_CONTENT_DIR . '/pww-downloads-log.txt';
        if (file_exists($log_file)) {
            file_put_contents($log_file, '');
            echo '<div class="notice notice-success"><p>Log cleared!</p></div>';
        }
    }
    
    // Check if tables exist (using direct query since table names are safe)
    $urls_table = $wpdb->prefix . 'pww_product_urls';
    $logs_table = $wpdb->prefix . 'pww_download_logs';
    $history_table = $wpdb->prefix . 'pww_download_history';
    
    $urls_exists = $wpdb->get_var("SHOW TABLES LIKE '$urls_table'") == $urls_table;
    $logs_exists = $wpdb->get_var("SHOW TABLES LIKE '$logs_table'") == $logs_table;
    $history_exists = $wpdb->get_var("SHOW TABLES LIKE '$history_table'") == $history_table;
    
    $tables_exist = $urls_exists && $logs_exists && $history_exists;
    
    // If history table doesn't exist but others do, create it automatically
    if ($urls_exists && $logs_exists && !$history_exists) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $history_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            product_id bigint(20) unsigned NOT NULL,
            download_url text NOT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            success tinyint(1) DEFAULT 1,
            error_message text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY product_id (product_id),
            KEY created_at (created_at),
            KEY success (success)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        $tables_exist = true; // Update flag after creating
    }
    
    if (isset($_POST['save_url']) && check_admin_referer('pww_save_url')) {
        $product_id = intval($_POST['product_id']);
        $url = esc_url_raw($_POST['bunny_url']);
        if ($product_id && $url) {
            $table = $wpdb->prefix . 'pww_product_urls';
            $result = $wpdb->insert($table, array('product_id' => $product_id, 'bunny_url' => $url), array('%d', '%s'));
            if ($result !== false) {
                wp_redirect(admin_url('admin.php?page=pww-downloads&url_saved=1'));
                exit;
            } else {
                echo '<div class="notice notice-error"><p>Error saving URL: ' . $wpdb->last_error . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>Please provide both product ID and URL.</p></div>';
        }
    }
    
    if (isset($_GET['url_saved'])) {
        echo '<div class="notice notice-success"><p>URL saved successfully!</p></div>';
    }
    
    if (isset($_GET['delete']) && check_admin_referer('pww_delete_url')) {
        $result = $wpdb->delete($wpdb->prefix . 'pww_product_urls', array('id' => intval($_GET['delete'])), array('%d'));
        if ($result !== false) {
            wp_redirect(admin_url('admin.php?page=pww-downloads&url_deleted=1'));
            exit;
        } else {
            echo '<div class="notice notice-error"><p>Error deleting URL: ' . $wpdb->last_error . '</p></div>';
        }
    }
    
    if (isset($_GET['url_deleted'])) {
        echo '<div class="notice notice-success"><p>URL deleted successfully!</p></div>';
    }
    
    if (isset($_POST['save_settings']) && check_admin_referer('pww_save_settings')) {
        update_option('pww_downloads_webhook_secret', sanitize_text_field($_POST['webhook_secret']));
        update_option('pww_downloads_email_subject', sanitize_text_field($_POST['email_subject']));
        update_option('pww_downloads_max_downloads', intval($_POST['max_downloads']));
        update_option('pww_downloads_download_window_days', intval($_POST['window_days']));
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }
    
    $products = get_posts(array('post_type' => 'fluent-products', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
    $webhook_url = rest_url('pww-downloads/v1/webhook');
    $log_file = WP_CONTENT_DIR . '/pww-downloads-log.txt';
    $log_content = file_exists($log_file) ? file_get_contents($log_file) : 'No log entries yet.';
    ?>
    <div class="wrap">
        <h1>PWW Downloads</h1>
        
        <?php if (!$tables_exist): ?>
        <div class="notice notice-error">
            <p><strong>Database tables are missing!</strong> Please create them by clicking the button below or deactivate and reactivate the plugin.</p>
            <form method="post" style="margin-top: 10px;">
                <?php wp_nonce_field('pww_create_tables'); ?>
                <input type="submit" name="create_tables" class="button button-primary" value="Create Database Tables" />
            </form>
        </div>
        <?php endif; ?>
        
        <h2>Webhook Log</h2>
        <p><strong>Webhook URL:</strong> <code><?php echo esc_url($webhook_url); ?></code></p>
        <p><strong>Test the webhook:</strong> Send a POST request to the URL above with header <code>X-Webhook-Secret</code> set to your secret.</p>
        
        <div style="background: #fff; border: 1px solid #ccd0d4; padding: 15px; margin: 20px 0;">
            <h3>Webhook Log Entries</h3>
            <form method="post" style="margin-bottom: 10px;">
                <?php wp_nonce_field('pww_clear_log'); ?>
                <input type="submit" name="clear_log" class="button" value="Clear Log" onclick="return confirm('Clear all log entries?');" />
            </form>
            <textarea readonly style="width: 100%; height: 200px; font-family: monospace; font-size: 12px; padding: 10px; background: #f5f5f5; border: 1px solid #ddd;" onclick="this.select();"><?php echo esc_textarea($log_content); ?></textarea>
            <p><em>Click the textarea and press Ctrl+A (Cmd+A on Mac) to select all, then Ctrl+C to copy.</em></p>
        </div>
        
        <h2>Download History</h2>
        <?php
        $history_table = $wpdb->prefix . 'pww_download_history';
        $history = $wpdb->get_results(
            "SELECT h.*, u.user_email, u.display_name, p.post_title 
             FROM $history_table h 
             LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID 
             LEFT JOIN {$wpdb->posts} p ON h.product_id = p.ID 
             ORDER BY h.created_at DESC 
             LIMIT 100"
        );
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Product</th>
                    <th>URL</th>
                    <th>IP Address</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                <tr>
                    <td colspan="6">No download history yet.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($history as $entry): 
                    // Get FluentCRM subscriber ID
                    $subscriber_id = null;
                    if ($entry->user_email && function_exists('FluentCrmApi')) {
                        try {
                            $contact = FluentCrmApi('contacts')->getContact($entry->user_email);
                            if ($contact && isset($contact->id)) {
                                $subscriber_id = $contact->id;
                            }
                        } catch (Exception $e) {
                            // Silently fail if FluentCRM is not available
                        }
                    }
                ?>
                <tr>
                    <td><?php echo esc_html(date_i18n('Y-m-d H:i:s', strtotime($entry->created_at))); ?></td>
                    <td>
                        <?php if ($entry->user_email): ?>
                            <?php if ($subscriber_id): ?>
                                <a href="https://pianowithwillie.com/wp-admin/admin.php?page=fluentcrm-admin#/subscribers/<?php echo esc_attr($subscriber_id); ?>/" target="_blank" style="text-decoration: none; color: #2271b1;">
                                    <?php echo esc_html($entry->display_name ?: $entry->user_email); ?>
                                </a>
                            <?php else: ?>
                                <?php echo esc_html($entry->display_name ?: $entry->user_email); ?>
                            <?php endif; ?>
                            <br><small><?php echo esc_html($entry->user_email); ?></small>
                        <?php else: ?>
                            User ID: <?php echo esc_html($entry->user_id); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($entry->post_title): ?>
                            <?php echo esc_html($entry->post_title); ?>
                        <?php else: ?>
                            Product ID: <?php echo esc_html($entry->product_id); ?>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?php echo esc_url($entry->download_url); ?>" target="_blank"><?php echo esc_html(substr($entry->download_url, 0, 50)); ?>...</a></td>
                    <td><?php echo esc_html($entry->ip_address); ?></td>
                    <td>
                        <?php if ($entry->success): ?>
                            <span style="color: green;">✓ Success</span>
                        <?php else: ?>
                            <span style="color: red;">✗ Failed</span>
                            <?php if ($entry->error_message): ?>
                                <br><small><?php echo esc_html($entry->error_message); ?></small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <h2>Settings</h2>
        <form method="post">
            <?php wp_nonce_field('pww_save_settings'); ?>
            <table class="form-table">
                <tr>
                    <th>Webhook URL</th>
                    <td><code><?php echo esc_url($webhook_url); ?></code></td>
                </tr>
                <tr>
                    <th>Webhook Secret</th>
                    <td><input type="text" name="webhook_secret" value="<?php echo esc_attr(get_option('pww_downloads_webhook_secret', '')); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th>Email Subject</th>
                    <td><input type="text" name="email_subject" value="<?php echo esc_attr(get_option('pww_downloads_email_subject', 'Your Downloads')); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th>Max Downloads</th>
                    <td><input type="number" name="max_downloads" value="<?php echo esc_attr(get_option('pww_downloads_max_downloads', 3)); ?>" min="1" /></td>
                </tr>
                <tr>
                    <th>Download Window (Days)</th>
                    <td><input type="number" name="window_days" value="<?php echo esc_attr(get_option('pww_downloads_download_window_days', 60)); ?>" min="1" /></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="save_settings" class="button button-primary" value="Save Settings" /></p>
        </form>
        
        <style>
        .pww-products-container {
            display: grid;
            gap: 20px;
            margin-top: 20px;
        }
        
        .pww-product-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .pww-product-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .pww-product-header h3 {
            margin: 0;
            flex: 1;
            font-size: 16px;
        }
        
        .pww-product-id {
            color: #666;
            font-size: 12px;
        }
        
        .pww-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .pww-urls-section {
            margin-top: 15px;
        }
        
        .pww-urls-list {
            margin-bottom: 15px;
        }
        
        .pww-url-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            margin-bottom: 8px;
            transition: background-color 0.2s;
        }
        
        .pww-url-item:hover {
            background: #f0f0f0;
        }
        
        .pww-url-link {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #2271b1;
            font-size: 13px;
        }
        
        .pww-url-link:hover {
            color: #135e96;
        }
        
        .pww-url-link .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }
        
        .pww-delete-btn {
            color: #a00;
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
        }
        
        .pww-delete-btn:hover {
            background-color: #f5f5f5;
            color: #dc3232;
        }
        
        .pww-delete-btn .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }
        
        .pww-no-urls {
            color: #666;
            font-style: italic;
            margin: 10px 0;
        }
        
        .pww-toggle-add-url {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .pww-toggle-add-url .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }
        
        .pww-add-url-form {
            margin-bottom: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        
        .pww-url-input-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .pww-url-input-group input[type="url"] {
            flex: 1;
        }
        
        .pww-cancel-add-url {
            margin-left: auto;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Close all forms on page load (in case they were open before redirect)
            $('.pww-add-url-form').hide();
            
            $('.pww-toggle-add-url').on('click', function() {
                var productId = $(this).data('product-id');
                $(this).hide();
                $('#pww-form-' + productId).slideDown(200);
                $('#pww-form-' + productId + ' input[name="bunny_url"]').focus();
            });
            
            $('.pww-cancel-add-url').on('click', function() {
                var productId = $(this).data('product-id');
                $('#pww-form-' + productId).slideUp(200);
                $('.pww-toggle-add-url[data-product-id="' + productId + '"]').show();
                $('#pww-form-' + productId + ' input[name="bunny_url"]').val('');
            });
        });
        </script>
        
        <h2>Products</h2>
        <div class="pww-products-container">
            <?php foreach ($products as $product): 
                $urls = $wpdb->get_results($wpdb->prepare("SELECT * FROM $urls_table WHERE product_id = %d", $product->ID));
            ?>
            <div class="pww-product-card">
                <div class="pww-product-header">
                    <h3><?php echo esc_html($product->post_title); ?></h3>
                    <span class="pww-product-id">ID: <?php echo $product->ID; ?></span>
                    <div class="pww-header-actions">
                        <button type="button" class="button button-small pww-toggle-add-url" data-product-id="<?php echo $product->ID; ?>">
                            <span class="dashicons dashicons-plus-alt"></span> Add URL
                        </button>
                        <a href="<?php echo get_edit_post_link($product->ID); ?>" class="button button-small">Edit</a>
                    </div>
                </div>
                
                <div class="pww-urls-section">
                    <form method="post" class="pww-add-url-form" id="pww-form-<?php echo $product->ID; ?>" style="display: none;">
                        <?php wp_nonce_field('pww_save_url'); ?>
                        <input type="hidden" name="product_id" value="<?php echo $product->ID; ?>" />
                        <div class="pww-url-input-group">
                            <input type="url" name="bunny_url" placeholder="https://jazzedge.b-cdn.net/file.zip" class="regular-text" required />
                            <button type="submit" name="save_url" class="button button-primary">Save</button>
                            <button type="button" class="button pww-cancel-add-url" data-product-id="<?php echo $product->ID; ?>">Cancel</button>
                        </div>
                    </form>
                    
                    <?php if ($urls): ?>
                        <div class="pww-urls-list">
                            <?php foreach ($urls as $url): ?>
                            <div class="pww-url-item">
                                <a href="<?php echo esc_url($url->bunny_url); ?>" target="_blank" class="pww-url-link">
                                    <span class="dashicons dashicons-download"></span>
                                    <?php echo esc_html($url->bunny_url); ?>
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=pww-downloads&delete=' . $url->id . '&_wpnonce=' . wp_create_nonce('pww_delete_url')); ?>" 
                                   onclick="return confirm('Delete this URL?')" 
                                   class="pww-delete-btn" 
                                   title="Delete URL">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="pww-no-urls">No download URLs configured</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

// FluentCRM Custom Tab - Download History
add_action('fluent_crm/after_init', function() {
    if (!function_exists('FluentCrmApi')) {
        return;
    }
    
    $key = 'pww_download_history';
    $sectionTitle = 'Download History';
    
    $callback = function($contentArr, $subscriber) {
        global $wpdb;
        
        // Get WordPress user by email
        $user = get_user_by('email', $subscriber->email);
        if (!$user) {
            $contentArr['heading'] = 'Download History';
            $contentArr['content_html'] = '<p>No WordPress user found for this email address.</p>';
            return $contentArr;
        }
        
        // Get download history
        $history_table = $wpdb->prefix . 'pww_download_history';
        $history = $wpdb->get_results($wpdb->prepare(
            "SELECT h.*, p.post_title 
             FROM $history_table h 
             LEFT JOIN {$wpdb->posts} p ON h.product_id = p.ID 
             WHERE h.user_id = %d 
             ORDER BY h.created_at DESC 
             LIMIT 50",
            $user->ID
        ));
        
        $contentArr['heading'] = 'Download History';
        
        if (empty($history)) {
            $contentArr['content_html'] = '<p>No download history found.</p>';
            return $contentArr;
        }
        
        // Build HTML table
        $html = '<div style="overflow-x: auto;">';
        $html .= '<table style="width: 100%; border-collapse: collapse; font-size: 13px;">';
        $html .= '<thead>';
        $html .= '<tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">';
        $html .= '<th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Date</th>';
        $html .= '<th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Product</th>';
        $html .= '<th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Status</th>';
        $html .= '<th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">IP Address</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($history as $entry) {
            $date = date_i18n('Y-m-d H:i:s', strtotime($entry->created_at));
            $product_name = $entry->post_title ? esc_html($entry->post_title) : 'Product ID: ' . $entry->product_id;
            $status = $entry->success ? '<span style="color: #46b450;">✓ Success</span>' : '<span style="color: #dc3232;">✗ Failed</span>';
            $ip = esc_html($entry->ip_address ?: 'N/A');
            
            if (!$entry->success && $entry->error_message) {
                $status .= '<br><small style="color: #666;">' . esc_html($entry->error_message) . '</small>';
            }
            
            $html .= '<tr style="border-bottom: 1px solid #eee;">';
            $html .= '<td style="padding: 10px;">' . esc_html($date) . '</td>';
            $html .= '<td style="padding: 10px;">' . $product_name . '</td>';
            $html .= '<td style="padding: 10px;">' . $status . '</td>';
            $html .= '<td style="padding: 10px;">' . $ip . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        // Add link to full admin page
        $admin_url = admin_url('admin.php?page=pww-downloads');
        $html .= '<p style="margin-top: 15px;"><a href="' . esc_url($admin_url) . '" target="_blank" style="text-decoration: none;">View Full Download History →</a></p>';
        
        $contentArr['content_html'] = $html;
        
        return $contentArr;
    };
    
    FluentCrmApi('extender')->addProfileSection($key, $sectionTitle, $callback);
});
