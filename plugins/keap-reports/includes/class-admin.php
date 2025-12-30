<?php
/**
 * Admin Interface for Keap Reports
 * 
 * @package Keap_Reports
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Keap_Reports_Admin {
    
    /**
     * API instance
     */
    private $api;
    
    /**
     * Reports instance
     */
    private $reports;
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Cron instance
     */
    private $cron;
    
    /**
     * Constructor
     * 
     * @param Keap_Reports_API $api
     * @param Keap_Reports_Reports $reports
     * @param Keap_Reports_Database $database
     * @param Keap_Reports_Cron $cron
     */
    public function __construct($api, $reports, $database, $cron = null) {
        $this->api = $api;
        $this->reports = $reports;
        $this->database = $database;
        $this->cron = $cron;
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_keap_reports_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_keap_reports_fetch', array($this, 'ajax_fetch_report'));
        add_action('wp_ajax_keap_reports_get_logs_for_copy', array($this, 'ajax_get_logs_for_copy'));
        add_action('wp_ajax_keap_reports_fetch_daily_subscriptions', array($this, 'ajax_fetch_daily_subscriptions'));
        add_action('wp_ajax_keap_reports_import_csv', array($this, 'ajax_import_csv'));
        add_action('wp_ajax_keap_reports_save_product', array($this, 'ajax_save_product'));
        add_action('wp_ajax_keap_reports_delete_product', array($this, 'ajax_delete_product'));
        add_action('wp_ajax_keap_reports_scan_tag_mismatches', array($this, 'ajax_scan_tag_mismatches'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Keap Reports',
            'Keap Reports',
            'manage_options',
            'keap-reports',
            array($this, 'render_display_page'),
            'dashicons-chart-line',
            30
        );
        
        add_submenu_page(
            'keap-reports',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'keap-reports',
            array($this, 'render_dashboard_page')
        );
        
        add_submenu_page(
            'keap-reports',
            'Product Subscriptions',
            'Product Subscriptions',
            'manage_options',
            'keap-reports-subscriptions',
            array($this, 'render_subscriptions_page')
        );
        
        add_submenu_page(
            'keap-reports',
            'Manage Reports',
            'Manage Reports',
            'manage_options',
            'keap-reports-manage',
            array($this, 'render_manage_page')
        );
        
        add_submenu_page(
            'keap-reports',
            'Product Management',
            'Product Management',
            'manage_options',
            'keap-reports-products',
            array($this, 'render_products_page')
        );
        
        add_submenu_page(
            'keap-reports',
            'Settings',
            'Settings',
            'manage_options',
            'keap-reports-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'keap-reports',
            'Tag Audit',
            'Tag Audit',
            'manage_options',
            'keap-reports-tag-audit',
            array($this, 'render_tag_audit_page')
        );
        
        add_submenu_page(
            'keap-reports',
            'Logs',
            'Logs',
            'manage_options',
            'keap-reports-logs',
            array($this, 'render_logs_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('keap_reports_settings', 'keap_reports_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));
        
        register_setting('keap_reports_settings', 'keap_reports_schedule_frequency', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'monthly'
        ));
        
        register_setting('keap_reports_settings', 'keap_reports_debug_enabled', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'keap-reports') === false) {
            return;
        }
        
        wp_enqueue_script('jquery');
    }
    
    /**
     * AJAX handler for testing connection
     */
    public function ajax_test_connection() {
        check_ajax_referer('keap_reports_test_connection', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        // Update API key if provided
        if (isset($_POST['api_key'])) {
            $api_key = sanitize_text_field($_POST['api_key']);
            update_option('keap_reports_api_key', $api_key);
        }
        
        // Update app name and key if provided
        if (isset($_POST['app_name'])) {
            $app_name = sanitize_text_field($_POST['app_name']);
            update_option('keap_reports_app_name', $app_name);
        }
        if (isset($_POST['app_key'])) {
            $app_key = sanitize_text_field($_POST['app_key']);
            update_option('keap_reports_app_key', $app_key);
        }
        
        $result = $this->api->test_connection();
        
        // Return both results
        wp_send_json_success($result);
    }
    
    /**
     * AJAX handler for fetching report
     */
    public function ajax_fetch_report() {
        check_ajax_referer('keap_reports_fetch', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        $report_id = isset($_POST['report_id']) ? absint($_POST['report_id']) : 0;
        
        if (!$report_id) {
            wp_send_json_error(array('message' => 'Invalid report ID'));
            return;
        }
        
        // Explicitly mark this as a manual fetch (from AJAX)
        $result = $this->reports->fetch_report($report_id, true);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle form submission
        if (isset($_POST['keap_reports_save_settings']) && check_admin_referer('keap_reports_save_settings')) {
            // Get API key - preserve all characters (alphanumeric, hyphens, underscores)
            // sanitize_text_field can strip hyphens, so we use a more permissive approach
            $raw_api_key = isset($_POST['keap_reports_api_key']) ? $_POST['keap_reports_api_key'] : '';
            $api_key = trim($raw_api_key);
            // Only remove potentially dangerous characters, but keep alphanumeric, hyphens, underscores
            $api_key = preg_replace('/[^a-zA-Z0-9\-_]/', '', $api_key);
            
            $schedule_frequency = isset($_POST['keap_reports_schedule_frequency']) ? sanitize_text_field($_POST['keap_reports_schedule_frequency']) : 'monthly';
            $debug_enabled = isset($_POST['keap_reports_debug_enabled']) ? 1 : 0;
            $auto_fetch_enabled = isset($_POST['keap_reports_auto_fetch_enabled']) ? 1 : 0;
            
            // Get iSDK credentials (for XML-RPC saved searches)
            $app_name = isset($_POST['keap_reports_app_name']) ? sanitize_text_field($_POST['keap_reports_app_name']) : '';
            $app_key = isset($_POST['keap_reports_app_key']) ? sanitize_text_field($_POST['keap_reports_app_key']) : '';
            
            // Update API key (empty string is valid - user might want to clear it)
            $result = update_option('keap_reports_api_key', $api_key);
            update_option('keap_reports_schedule_frequency', $schedule_frequency);
            update_option('keap_reports_debug_enabled', $debug_enabled);
            update_option('keap_reports_auto_fetch_enabled', $auto_fetch_enabled);
            update_option('keap_reports_app_name', $app_name);
            update_option('keap_reports_app_key', $app_key);
            
            // Handle cron scheduling based on auto_fetch setting
            $cron = new Keap_Reports_Cron($this->reports);
            if ($auto_fetch_enabled) {
                // Reschedule cron if frequency changed
                $cron->reschedule($schedule_frequency);
            } else {
                // Clear all scheduled events if auto-fetch is disabled
                $cron->clear_scheduled_events();
                $cron->clear_daily_subscription_events();
            }
            
            // Verify it was saved
            $saved_key = get_option('keap_reports_api_key', '');
            
            if ($saved_key === $api_key && !empty($saved_key)) {
                echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully! API key saved (' . strlen($saved_key) . ' characters)</p></div>';
            } elseif (empty($api_key) && empty($saved_key)) {
                echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully! API key cleared.</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Warning: API key may not have saved correctly. Please try again.</p></div>';
            }
        }
        
        // Handle stop all fetches action
        if (isset($_POST['stop_all_fetches']) && check_admin_referer('keap_reports_stop_all_fetches')) {
            $cron = new Keap_Reports_Cron($this->reports);
            $cron->clear_scheduled_events();
            $cron->clear_daily_subscription_events();
            update_option('keap_reports_auto_fetch_enabled', 0);
            // Set a kill switch timestamp to force-stop all processes immediately
            update_option('keap_reports_kill_switch', time());
            echo '<div class="notice notice-success is-dismissible"><p>All scheduled fetches have been stopped and cleared. Running processes will stop within seconds.</p></div>';
        }
        
        // Get current settings
        $api_key = get_option('keap_reports_api_key', '');
        $schedule_frequency = get_option('keap_reports_schedule_frequency', 'monthly');
        $debug_enabled = get_option('keap_reports_debug_enabled', false);
        $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
        $app_name = get_option('keap_reports_app_name', 'ft217'); // Default to ft217
        $app_key = get_option('keap_reports_app_key', '');
        $cron = new Keap_Reports_Cron($this->reports);
        $next_run = $cron->get_next_run_time();
        $next_daily_run = $cron->get_next_daily_subscription_run_time();
        $is_scheduled = $cron->is_daily_subscription_scheduled();
        
        ?>
        <div class="wrap">
            <h1>Keap Reports Settings</h1>
            
            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=keap-reports-settings')); ?>">
                <?php wp_nonce_field('keap_reports_save_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="keap_reports_api_key">Keap API Key</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="keap_reports_api_key" 
                                   name="keap_reports_api_key" 
                                   value="<?php echo esc_attr($api_key); ?>" 
                                   class="regular-text" 
                                   placeholder="KeapAK-..." 
                                   autocomplete="off"
                                   style="font-family: monospace; width: 100%; max-width: 600px;" />
                            <p class="description">
                                Enter your Keap API key (starts with "KeapAK-")
                            </p>
                            <?php if (!empty($api_key)): ?>
                                <p class="description" style="color: green;">
                                    ✓ API key is configured (<?php echo esc_html(substr($api_key, 0, 15)) . '...'; ?>)
                                </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="keap_reports_app_name">Keap App Name (for XML-RPC)</label>
                            </th>
                            <td>
                                <input type="text"
                                       id="keap_reports_app_name"
                                       name="keap_reports_app_name"
                                       value="<?php echo esc_attr($app_name); ?>"
                                       class="regular-text"
                                       placeholder="ft217"
                                       style="font-family: monospace; width: 100%; max-width: 300px;" />
                                <p class="description">
                                    Your Keap app name (e.g., "ft217"). Required for fetching saved search reports via XML-RPC.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="keap_reports_app_key">Keap App Key (for XML-RPC)</label>
                            </th>
                            <td>
                                <input type="text"
                                       id="keap_reports_app_key"
                                       name="keap_reports_app_key"
                                       value="<?php echo esc_attr($app_key); ?>"
                                       class="regular-text"
                                       placeholder="Your app API key"
                                       autocomplete="off"
                                       style="font-family: monospace; width: 100%; max-width: 600px;" />
                                <p class="description">
                                    Your Keap app API key (legacy API key format). Required for fetching saved search reports via XML-RPC. This is different from the REST API key above.
                                </p>
                                <?php if (!empty($app_key)): ?>
                                    <p class="description" style="color: green;">
                                        ✓ App key is configured (<?php echo esc_html(substr($app_key, 0, 15)) . '...'; ?>)
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="keap_reports_debug_enabled">Enable Debug Logging</label>
                            </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       id="keap_reports_debug_enabled" 
                                       name="keap_reports_debug_enabled" 
                                       value="1" 
                                       <?php checked($debug_enabled, true); ?> />
                                Enable debug logging for troubleshooting
                            </label>
                            <p class="description">
                                When enabled, all API requests will be logged to the WordPress debug log. Make sure WordPress debug logging is enabled in wp-config.php.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label>Test Connection</label>
                        </th>
                        <td>
                            <button type="button" id="test-connection-btn" class="button">
                                Test Connection
                            </button>
                            <span id="connection-status" style="margin-left: 10px;"></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="keap_reports_auto_fetch_enabled">Enable Automatic Fetching</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       id="keap_reports_auto_fetch_enabled" 
                                       name="keap_reports_auto_fetch_enabled" 
                                       value="1" 
                                       <?php checked($auto_fetch_enabled, true); ?> />
                                Enable automatic scheduled fetching of reports
                            </label>
                            <p class="description">
                                When enabled, reports will be automatically fetched on the schedule below. When disabled, you must manually fetch reports.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="keap_reports_schedule_frequency">Schedule Frequency</label>
                        </th>
                        <td>
                            <select id="keap_reports_schedule_frequency" name="keap_reports_schedule_frequency" <?php echo !$auto_fetch_enabled ? 'disabled' : ''; ?>>
                                <option value="hourly" <?php selected($schedule_frequency, 'hourly'); ?>>Hourly</option>
                                <option value="daily" <?php selected($schedule_frequency, 'daily'); ?>>Daily</option>
                                <option value="weekly" <?php selected($schedule_frequency, 'weekly'); ?>>Weekly</option>
                                <option value="monthly" <?php selected($schedule_frequency, 'monthly'); ?>>Monthly</option>
                            </select>
                            <p class="description">
                                How often to automatically fetch report data from Keap (only applies when automatic fetching is enabled)
                            </p>
                            <?php if ($next_run && $auto_fetch_enabled): ?>
                                <p class="description">
                                    Next scheduled run: <strong><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_run)); ?></strong> (<?php echo esc_html(wp_timezone_string()); ?>)
                                </p>
                            <?php elseif (!$auto_fetch_enabled): ?>
                                <p class="description" style="color: #d63638;">
                                    Automatic fetching is disabled. No reports will be fetched automatically.
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="keap_reports_save_settings" class="button button-primary" value="Save Settings" />
                </p>
            </form>
            
            <hr style="margin: 20px 0;">
            
            <h2>Cron Job Management</h2>
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4;">
                <h3>Current Scheduled Jobs</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row">Report Fetching</th>
                        <td>
                            <?php if ($next_run): ?>
                                <span style="color: #00a32a;">✓ Scheduled</span>
                                <p class="description">
                                    Next run: <strong><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_run)); ?></strong>
                                    (<?php echo human_time_diff($next_run, current_time('timestamp')); ?> from now)
                                </p>
                            <?php else: ?>
                                <span style="color: #d63638;">✗ Not Scheduled</span>
                                <p class="description">No automatic report fetching is scheduled.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Daily Subscriptions</th>
                        <td>
                            <?php if ($is_scheduled && $next_daily_run): ?>
                                <span style="color: #00a32a;">✓ Scheduled</span>
                                <p class="description">
                                    Next run: <strong><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_daily_run)); ?></strong>
                                    (<?php echo human_time_diff($next_daily_run, current_time('timestamp')); ?> from now)
                                </p>
                            <?php else: ?>
                                <span style="color: #d63638;">✗ Not Scheduled</span>
                                <p class="description">No daily subscription fetching is scheduled.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=keap-reports-settings')); ?>" style="margin-top: 20px;">
                    <?php wp_nonce_field('keap_reports_stop_all_fetches'); ?>
                    <p>
                        <button type="submit" name="stop_all_fetches" class="button button-secondary" onclick="return confirm('Are you sure you want to stop all scheduled fetches? This will clear all cron jobs and disable automatic fetching.');">
                            Stop All Scheduled Fetches
                        </button>
                        <span class="description" style="margin-left: 10px;">
                            This will immediately stop and clear all scheduled cron jobs. Automatic fetching will be disabled.
                        </span>
                    </p>
                    <p class="description" style="margin-top: 10px; color: #d63638;">
                        <strong>Note:</strong> If you see logs continuing after disabling auto-fetch, those are from processes that were already running. They will stop at the next batch boundary (within a few seconds). New processes will not start.
                    </p>
                </form>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Enable/disable schedule frequency based on auto fetch checkbox
            $('#keap_reports_auto_fetch_enabled').on('change', function() {
                $('#keap_reports_schedule_frequency').prop('disabled', !$(this).is(':checked'));
            });
            
            $('#test-connection-btn').on('click', function() {
                var $btn = $(this);
                var $status = $('#connection-status');
                
                $btn.prop('disabled', true).text('Testing...');
                $status.html('<span class="spinner is-active" style="float:none;margin:0 5px;"></span> Testing connections...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'keap_reports_test_connection',
                        nonce: '<?php echo wp_create_nonce('keap_reports_test_connection'); ?>',
                        api_key: $('#keap_reports_api_key').val(),
                        app_name: $('#keap_reports_app_name').val(),
                        app_key: $('#keap_reports_app_key').val()
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            var messages = [];
                            
                            // REST API result
                            if (response.data.rest_api) {
                                var restMsg = response.data.rest_api.success 
                                    ? '<span style="color: green;">✓ REST API: ' + response.data.rest_api.message + '</span>'
                                    : '<span style="color: red;">✗ REST API: ' + response.data.rest_api.message + '</span>';
                                messages.push(restMsg);
                            }
                            
                            // XML-RPC result
                            if (response.data.xmlrpc) {
                                var xmlrpcMsg = response.data.xmlrpc.success 
                                    ? '<span style="color: green;">✓ XML-RPC: ' + response.data.xmlrpc.message + '</span>'
                                    : '<span style="color: red;">✗ XML-RPC: ' + response.data.xmlrpc.message + '</span>';
                                messages.push(xmlrpcMsg);
                            }
                            
                            $status.html(messages.join('<br>'));
                        } else {
                            $status.html('<span style="color: red;">✗ Connection test failed</span>');
                        }
                    },
                    error: function() {
                        $status.html('<span style="color: red;">✗ Connection test failed</span>');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Test Connection');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render manage reports page
     */
    public function render_manage_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle delete
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['report_id'])) {
            check_admin_referer('delete_report_' . $_GET['report_id']);
            $this->database->delete_report(absint($_GET['report_id']));
            echo '<div class="notice notice-success is-dismissible"><p>Report deleted successfully!</p></div>';
        }
        
        // Handle form submission
        if (isset($_POST['keap_reports_save_report']) && check_admin_referer('keap_reports_save_report')) {
            // Validate required fields first
            if (empty($_POST['report_name']) || empty($_POST['keap_report_id']) || empty($_POST['report_uuid'])) {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to save report. All fields (Name, Report ID, and UUID) are required.</p></div>';
            } else {
                $report_data = array(
                    'id' => isset($_POST['report_id']) ? absint($_POST['report_id']) : 0,
                    'name' => sanitize_text_field($_POST['report_name']),
                    'report_id' => absint($_POST['keap_report_id']),
                    'report_uuid' => sanitize_text_field($_POST['report_uuid']),
                    'report_type' => sanitize_text_field($_POST['report_type']),
                    'filter_product_id' => isset($_POST['filter_product_id']) ? sanitize_text_field($_POST['filter_product_id']) : '',
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                );
                
                $result = $this->database->save_report($report_data);
                
                if ($result) {
                    // Redirect to avoid resubmission and show success
                    wp_redirect(admin_url('admin.php?page=keap-reports-manage&saved=1'));
                    exit;
                } else {
                    global $wpdb;
                    $error_msg = $wpdb->last_error ? $wpdb->last_error : 'Unknown error';
                    echo '<div class="notice notice-error is-dismissible"><p>Failed to save report. Please check all fields are filled correctly. Database error: ' . esc_html($error_msg) . '</p></div>';
                }
            }
        }
        
        // Show success message if redirected
        if (isset($_GET['saved']) && $_GET['saved'] == '1') {
            echo '<div class="notice notice-success is-dismissible"><p>Report saved successfully!</p></div>';
        }
        
        // Get all reports
        $reports = $this->database->get_reports();
        
        // Get report to edit
        $edit_report = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['report_id'])) {
            $edit_report = $this->database->get_report(absint($_GET['report_id']));
        }
        
        ?>
        <div class="wrap">
            <h1>Manage Reports</h1>
            
            <h2><?php echo $edit_report ? 'Edit Report' : 'Add New Report'; ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=keap-reports-manage')); ?>">
                <?php wp_nonce_field('keap_reports_save_report'); ?>
                <?php if ($edit_report): ?>
                    <input type="hidden" name="report_id" value="<?php echo esc_attr($edit_report['id']); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="report_name">Report Name</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="report_name" 
                                   name="report_name" 
                                   value="<?php echo $edit_report ? esc_attr($edit_report['name']) : ''; ?>" 
                                   class="regular-text" 
                                   required />
                            <p class="description">Display name for this report (e.g., "Dec 25 Sales")</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="keap_report_id">Keap Report ID</label>
                        </th>
                        <td>
                            <input type="number" 
                                   id="keap_report_id" 
                                   name="keap_report_id" 
                                   value="<?php echo $edit_report ? esc_attr($edit_report['report_id']) : ''; ?>" 
                                   class="regular-text" 
                                   required />
                            <p class="description">The saved search ID from Keap (e.g., 2055)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="keap_report_url">Keap Report URL</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="keap_report_url" 
                                   name="keap_report_url" 
                                   value="" 
                                   class="regular-text" 
                                   placeholder="Paste Keap report URL here to auto-fill UUID" />
                            <button type="button" id="extract-uuid-btn" class="button button-small">Extract UUID</button>
                            <p class="description">Paste the full Keap report URL and click "Extract UUID" to automatically fill the UUID field below</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="report_uuid">Report UUID</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="report_uuid" 
                                   name="report_uuid" 
                                   value="<?php echo $edit_report ? esc_attr($edit_report['report_uuid']) : ''; ?>" 
                                   class="regular-text" 
                                   required />
                            <p class="description">The UUID from Keap (e.g., "a5c1a584-4311-4a71-b1f8-bd52f638de8d") - or paste URL above to auto-extract</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="report_type">Report Type</label>
                        </th>
                        <td>
                            <select id="report_type" name="report_type">
                                <option value="sales" <?php echo ($edit_report && $edit_report['report_type'] === 'sales') ? 'selected' : ''; ?>>Sales</option>
                                <option value="memberships" <?php echo ($edit_report && $edit_report['report_type'] === 'memberships') ? 'selected' : ''; ?>>Memberships</option>
                                <option value="subscriptions" <?php echo ($edit_report && $edit_report['report_type'] === 'subscriptions') ? 'selected' : ''; ?>>Subscriptions</option>
                                <option value="custom" <?php echo (!$edit_report || $edit_report['report_type'] === 'custom') ? 'selected' : ''; ?>>Custom</option>
                            </select>
                            <p class="description">Type of report (affects how data is aggregated)</p>
                        </td>
                    </tr>
                    <tr id="product-filter-row" style="<?php echo ($edit_report && $edit_report['report_type'] === 'subscriptions') ? '' : 'display: none;'; ?>">
                        <th scope="row">
                            <label for="filter_product_id">Filter by Product ID(s)</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="filter_product_id" 
                                   name="filter_product_id" 
                                   value="<?php echo $edit_report && isset($edit_report['filter_product_id']) ? esc_attr($edit_report['filter_product_id']) : ''; ?>" 
                                   class="regular-text" 
                                   placeholder="e.g., 62332 or 62332,62358,62291" />
                            <p class="description">Optional: Comma-separated list of product IDs to filter subscriptions. Leave empty to get all active subscriptions.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="is_active">Active</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       <?php checked(!$edit_report || $edit_report['is_active'], true); ?> />
                                Include this report in scheduled fetches
                            </label>
                        </td>
                    </tr>
                </table>
                
                <input type="submit" name="keap_reports_save_report" class="button button-primary" value="<?php echo $edit_report ? 'Update Report' : 'Add Report'; ?>" />
                <?php if ($edit_report): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=keap-reports-manage')); ?>" class="button">Cancel</a>
                <?php endif; ?>
            </form>
            
            <h2>Existing Reports</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Report ID</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Last Fetch</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="6">No reports configured yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $report): ?>
                            <?php
                            $current_year = intval(date('Y'));
                            $current_month = intval(date('n'));
                            $last_data = $this->database->get_report_data($report['id'], $current_year, $current_month);
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($report['name']); ?></strong></td>
                                <td><?php echo esc_html($report['report_id']); ?></td>
                                <td><?php echo esc_html(ucfirst($report['report_type'])); ?></td>
                                <td><?php echo $report['is_active'] ? '<span style="color: green;">Active</span>' : '<span style="color: gray;">Inactive</span>'; ?></td>
                                <td>
                                    <?php 
                                    if ($last_data && $last_data['fetched_at']) {
                                        echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_data['fetched_at'])));
                                    } else {
                                        echo '<span style="color: gray;">Never</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=keap-reports-manage&action=edit&report_id=' . $report['id'])); ?>" class="button button-small">Edit</a>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=keap-reports-manage&action=delete&report_id=' . $report['id']), 'delete_report_' . $report['id'])); ?>" 
                                       class="button button-small" 
                                       onclick="return confirm('Are you sure you want to delete this report? This will also delete all associated data.');">Delete</a>
                                    <button type="button" 
                                            class="button button-small fetch-report-btn" 
                                            data-report-id="<?php echo esc_attr($report['id']); ?>"
                                            data-report-name="<?php echo esc_attr($report['name']); ?>">Fetch Now</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Extract UUID from Keap URL
            $('#extract-uuid-btn').on('click', function() {
                var url = $('#keap_report_url').val().trim();
                
                if (!url) {
                    alert('Please paste a Keap report URL first.');
                    return;
                }
                
                try {
                    // Parse the URL to extract reportStateId
                    var urlObj = new URL(url);
                    var reportStateId = urlObj.searchParams.get('reportStateId');
                    
                    if (reportStateId) {
                        var $uuidField = $('#report_uuid');
                        $uuidField.val(reportStateId);
                        $uuidField.focus().blur(); // Trigger change events
                        $('#keap_report_url').val('').attr('placeholder', 'UUID extracted! Paste another URL if needed.');
                        // Use setTimeout to ensure value is set before alert
                        setTimeout(function() {
                            alert('UUID extracted successfully: ' + reportStateId);
                            // Verify it's still there after alert
                            if ($uuidField.val() !== reportStateId) {
                                $uuidField.val(reportStateId);
                            }
                        }, 10);
                    } else {
                        // Try alternative parsing if URL parsing doesn't work
                        var match = url.match(/reportStateId=([a-f0-9\-]+)/i);
                        if (match && match[1]) {
                            var $uuidField = $('#report_uuid');
                            $uuidField.val(match[1]);
                            $uuidField.focus().blur(); // Trigger change events
                            $('#keap_report_url').val('').attr('placeholder', 'UUID extracted! Paste another URL if needed.');
                            setTimeout(function() {
                                alert('UUID extracted successfully: ' + match[1]);
                                if ($uuidField.val() !== match[1]) {
                                    $uuidField.val(match[1]);
                                }
                            }, 10);
                        } else {
                            alert('Could not find reportStateId in the URL. Please check the URL and try again.');
                        }
                    }
                } catch (e) {
                    // Fallback: try regex parsing
                    var match = url.match(/reportStateId=([a-f0-9\-]+)/i);
                    if (match && match[1]) {
                        $('#report_uuid').val(match[1]);
                        $('#keap_report_url').val('').attr('placeholder', 'UUID extracted! Paste another URL if needed.');
                        alert('UUID extracted successfully: ' + match[1]);
                    } else {
                        alert('Error parsing URL. Please make sure you pasted a valid Keap report URL.');
                    }
                }
            });
            
            // Auto-extract on paste if URL field has focus
            $('#keap_report_url').on('paste', function() {
                var $field = $(this);
                setTimeout(function() {
                    var url = $field.val().trim();
                    if (url && url.indexOf('reportStateId=') !== -1) {
                        // Auto-extract after paste
                        $('#extract-uuid-btn').trigger('click');
                    }
                }, 100);
            });
            
            // Show/hide product filter based on report type
            function toggleProductFilter() {
                var reportType = $('#report_type').val();
                if (reportType === 'subscriptions') {
                    $('#product-filter-row').show();
                } else {
                    $('#product-filter-row').hide();
                }
            }
            
            $('#report_type').on('change', toggleProductFilter);
            toggleProductFilter(); // Run on page load
            
            $('.fetch-report-btn').on('click', function() {
                var $btn = $(this);
                var reportId = $btn.data('report-id');
                var reportName = $btn.data('report-name');
                
                $btn.prop('disabled', true).text('Fetching...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'keap_reports_fetch',
                        nonce: '<?php echo wp_create_nonce('keap_reports_fetch'); ?>',
                        report_id: reportId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Report "' + reportName + '" fetched successfully!\n\n' + response.data.message);
                            location.reload();
                        } else {
                            alert('Failed to fetch report "' + reportName + '":\n\n' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('Error fetching report. Please try again.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Fetch Now');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render display page
     */
    public function render_display_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get current month/year or from query params
        $year = isset($_GET['year']) ? absint($_GET['year']) : intval(date('Y'));
        $month = isset($_GET['month']) ? absint($_GET['month']) : intval(date('n'));
        
        // Get all reports data for this period
        $reports_data = $this->reports->get_all_reports_data($year, $month);
        
        ?>
        <div class="wrap">
            <h1>Keap Reports</h1>
            
            <div style="margin: 20px 0;">
                <form method="get" action="">
                    <input type="hidden" name="page" value="keap-reports">
                    <label>
                        Year: 
                        <select name="year">
                            <?php
                            $current_year = intval(date('Y'));
                            for ($y = $current_year - 2; $y <= $current_year + 1; $y++):
                                ?>
                                <option value="<?php echo $y; ?>" <?php selected($year, $y); ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </label>
                    <label style="margin-left: 20px;">
                        Month: 
                        <select name="month">
                            <?php
                            for ($m = 1; $m <= 12; $m++):
                                $month_name = date('F', mktime(0, 0, 0, $m, 1));
                                ?>
                                <option value="<?php echo $m; ?>" <?php selected($month, $m); ?>><?php echo $month_name; ?></option>
                            <?php endfor; ?>
                        </select>
                    </label>
                    <input type="submit" class="button" value="View">
                </form>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Report Name</th>
                        <th>Type</th>
                        <th>Current Month</th>
                        <th>Previous Month</th>
                        <th>Change</th>
                        <th>YTD</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports_data)): ?>
                        <tr>
                            <td colspan="7">No reports configured or no data available for this period.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports_data as $report): ?>
                            <?php
                            $comparison = $this->reports->get_monthly_comparison($report['id'], $year, $month);
                            $current_value = $comparison['current'];
                            $previous_value = $comparison['previous'];
                            $change = $comparison['change'];
                            $change_percent = $comparison['change_percent'];
                            $ytd = $comparison['ytd'];
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($report['name']); ?></strong></td>
                                <td><?php echo esc_html(ucfirst($report['report_type'])); ?></td>
                                <td><strong><?php echo esc_html($this->reports->format_value($current_value, $report['report_type'])); ?></strong></td>
                                <td><?php echo esc_html($this->reports->format_value($previous_value, $report['report_type'])); ?></td>
                                <td>
                                    <?php if ($change != 0): ?>
                                        <span style="color: <?php echo $change > 0 ? 'green' : 'red'; ?>;">
                                            <?php echo $change > 0 ? '+' : ''; ?>
                                            <?php echo esc_html($this->reports->format_value($change, $report['report_type'])); ?>
                                            (<?php echo number_format($change_percent, 1); ?>%)
                                        </span>
                                    <?php else: ?>
                                        <span style="color: gray;">No change</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo esc_html($this->reports->format_value($ytd, $report['report_type'])); ?></strong></td>
                                <td>
                                    <?php 
                                    if (!empty($report['fetched_at'])) {
                                        echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($report['fetched_at'])));
                                    } else {
                                        echo '<span style="color: gray;">Never</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render logs page
     */
    public function render_logs_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle clear logs action
        if (isset($_POST['clear_logs']) && check_admin_referer('keap_reports_clear_logs')) {
            $days_to_keep = isset($_POST['days_to_keep']) ? absint($_POST['days_to_keep']) : 30;
            $deleted = $this->database->clear_old_logs($days_to_keep);
            echo '<div class="notice notice-success is-dismissible"><p>Cleared ' . $deleted . ' old log entries (kept last ' . $days_to_keep . ' days).</p></div>';
        }
        
        // Handle clear all logs action
        if (isset($_POST['clear_all_logs']) && check_admin_referer('keap_reports_clear_all_logs')) {
            $deleted = $this->database->clear_all_logs();
            echo '<div class="notice notice-success is-dismissible"><p>Cleared all ' . $deleted . ' log entries.</p></div>';
        }
        
        // Handle export logs action
        if (isset($_GET['export_logs']) && check_admin_referer('keap_reports_export_logs')) {
            $limit = isset($_GET['limit']) ? absint($_GET['limit']) : 1000;
            $level = isset($_GET['level']) ? sanitize_text_field($_GET['level']) : null;
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="keap-reports-logs-' . date('Y-m-d-His') . '.json"');
            
            echo $this->database->export_logs_json($limit, $level);
            exit;
        }
        
        // Get filter
        $filter_level = isset($_GET['level']) ? sanitize_text_field($_GET['level']) : null;
        $limit = isset($_GET['limit']) ? absint($_GET['limit']) : 100;
        
        // Get logs
        $logs = $this->database->get_logs($limit, $filter_level);
        $total_logs = $this->database->get_log_count();
        $error_count = $this->database->get_log_count('error');
        $warning_count = $this->database->get_log_count('warning');
        $debug_count = $this->database->get_log_count('debug');
        $info_count = $this->database->get_log_count('info');
        
        ?>
        <div class="wrap">
            <h1>Keap Reports Logs</h1>
            
            <div class="keap-reports-logs-stats" style="margin: 20px 0;">
                <h2>Log Statistics</h2>
                <ul style="list-style: none; padding: 0;">
                    <li style="display: inline-block; margin-right: 20px;"><strong>Total:</strong> <?php echo number_format($total_logs); ?></li>
                    <li style="display: inline-block; margin-right: 20px; color: #d63638;"><strong>Errors:</strong> <?php echo number_format($error_count); ?></li>
                    <li style="display: inline-block; margin-right: 20px; color: #dba617;"><strong>Warnings:</strong> <?php echo number_format($warning_count); ?></li>
                    <li style="display: inline-block; margin-right: 20px; color: #2271b1;"><strong>Info:</strong> <?php echo number_format($info_count); ?></li>
                    <li style="display: inline-block; margin-right: 20px; color: #50575e;"><strong>Debug:</strong> <?php echo number_format($debug_count); ?></li>
                </ul>
            </div>
            
            <div class="keap-reports-logs-filters" style="margin: 20px 0;">
                <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                    <input type="hidden" name="page" value="keap-reports-logs" />
                    <label>
                        Filter by level:
                        <select name="level">
                            <option value="">All Levels</option>
                            <option value="error" <?php selected($filter_level, 'error'); ?>>Errors</option>
                            <option value="warning" <?php selected($filter_level, 'warning'); ?>>Warnings</option>
                            <option value="info" <?php selected($filter_level, 'info'); ?>>Info</option>
                            <option value="debug" <?php selected($filter_level, 'debug'); ?>>Debug</option>
                        </select>
                    </label>
                    <label style="margin-left: 10px;">
                        Limit:
                        <select name="limit">
                            <option value="50" <?php selected($limit, 50); ?>>50</option>
                            <option value="100" <?php selected($limit, 100); ?>>100</option>
                            <option value="200" <?php selected($limit, 200); ?>>200</option>
                            <option value="500" <?php selected($limit, 500); ?>>500</option>
                        </select>
                    </label>
                    <input type="submit" class="button" value="Filter" />
                    <a href="<?php echo esc_url(admin_url('admin.php?page=keap-reports-logs')); ?>" class="button">Reset</a>
                </form>
            </div>
            
            <div class="keap-reports-logs-actions" style="margin: 20px 0; padding: 15px; background: #f0f0f1; border-radius: 4px;">
                <h3 style="margin-top: 0;">Log Actions</h3>
                
                <div style="margin-bottom: 15px;">
                    <button type="button" id="copy-logs-btn" class="button button-primary" style="margin-right: 10px;">
                        <span class="dashicons dashicons-clipboard" style="vertical-align: middle;"></span> Copy All Logs for Debugging
                    </button>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=keap-reports-logs&export_logs=1&limit=' . $limit . ($filter_level ? '&level=' . $filter_level : '')), 'keap_reports_export_logs')); ?>" class="button">
                        <span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Export as JSON
                    </a>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=keap-reports-logs')); ?>" style="display: inline-block; margin-right: 10px;" onsubmit="return confirm('Are you sure you want to clear old logs? This action cannot be undone.');">
                        <?php wp_nonce_field('keap_reports_clear_logs'); ?>
                        <label>
                            Clear logs older than:
                            <select name="days_to_keep">
                                <option value="7">7 days</option>
                                <option value="14">14 days</option>
                                <option value="30" selected>30 days</option>
                                <option value="60">60 days</option>
                                <option value="90">90 days</option>
                            </select>
                        </label>
                        <input type="submit" name="clear_logs" class="button" value="Clear Old Logs" />
                    </form>
                    
                    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=keap-reports-logs')); ?>" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete ALL logs? This action cannot be undone!');">
                        <?php wp_nonce_field('keap_reports_clear_all_logs'); ?>
                        <input type="submit" name="clear_all_logs" class="button button-secondary" value="Clear All Logs" style="color: #d63638;" />
                    </form>
                </div>
                
                <div id="copy-status" style="display: none; margin-top: 10px; padding: 10px; background: #fff; border-left: 4px solid #2271b1; border-radius: 3px;">
                    <strong>Logs copied to clipboard!</strong> You can now paste them anywhere.
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 150px;">Date/Time</th>
                        <th style="width: 100px;">Level</th>
                        <th>Message</th>
                        <th style="width: 200px;">Context</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4">No logs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $log_time = strtotime($log['created_at'] . ' UTC');
                                    echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $log_time));
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $level_colors = array(
                                        'error' => '#d63638',
                                        'warning' => '#dba617',
                                        'info' => '#2271b1',
                                        'debug' => '#50575e'
                                    );
                                    $color = isset($level_colors[$log['log_level']]) ? $level_colors[$log['log_level']] : '#000';
                                    ?>
                                    <span style="color: <?php echo esc_attr($color); ?>; font-weight: bold;">
                                        <?php echo esc_html(strtoupper($log['log_level'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <code style="background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                                        <?php echo esc_html($log['message']); ?>
                                    </code>
                                </td>
                                <td>
                                    <?php if (!empty($log['context'])): 
                                        $context = json_decode($log['context'], true);
                                        if ($context && is_array($context)):
                                    ?>
                                        <details>
                                            <summary style="cursor: pointer; color: #2271b1;">View Context</summary>
                                            <pre style="background: #f0f0f1; padding: 10px; margin-top: 5px; border-radius: 3px; font-size: 11px; max-height: 200px; overflow: auto;"><?php echo esc_html(print_r($context, true)); ?></pre>
                                        </details>
                                    <?php 
                                        endif;
                                    endif; 
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#copy-logs-btn').on('click', function() {
                var $btn = $(this);
                var $status = $('#copy-status');
                
                $btn.prop('disabled', true).text('Copying...');
                $status.hide();
                
                // Get all logs via AJAX
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'keap_reports_get_logs_for_copy',
                        nonce: '<?php echo wp_create_nonce('keap_reports_get_logs'); ?>',
                        limit: <?php echo absint($limit); ?>,
                        level: '<?php echo esc_js($filter_level ? $filter_level : ''); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data.logs_text) {
                            // Create temporary textarea to copy
                            var $temp = $('<textarea>');
                            $('body').append($temp);
                            $temp.val(response.data.logs_text).select();
                            
                            try {
                                document.execCommand('copy');
                                $status.show().fadeOut(5000);
                            } catch (err) {
                                alert('Failed to copy. Please select and copy manually from the textarea that will appear.');
                                $temp.css({position: 'fixed', top: '50%', left: '50%', width: '80%', height: '60%', zIndex: 9999});
                            }
                            
                            $temp.remove();
                        } else {
                            alert('Failed to get logs: ' + (response.data ? response.data.message : 'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.status + ' ' + xhr.statusText);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-clipboard" style="vertical-align: middle;"></span> Copy All Logs for Debugging');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler to get logs for copying
     */
    public function ajax_get_logs_for_copy() {
        check_ajax_referer('keap_reports_get_logs', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 1000;
        $level = isset($_POST['level']) && !empty($_POST['level']) ? sanitize_text_field($_POST['level']) : null;
        
        $logs = $this->database->get_logs($limit, $level);
        
        // Format logs for easy copying
        $logs_text = "=== KEAP REPORTS DEBUG LOGS ===\n";
        $logs_text .= "Export Date: " . current_time('mysql') . "\n";
        $logs_text .= "WordPress Version: " . get_bloginfo('version') . "\n";
        $logs_text .= "Plugin Version: " . (defined('KEAP_REPORTS_VERSION') ? KEAP_REPORTS_VERSION : 'unknown') . "\n";
        $logs_text .= "Timezone: " . wp_timezone_string() . "\n";
        $logs_text .= "Total Logs: " . count($logs) . "\n";
        $logs_text .= "Filter Level: " . ($level ? $level : 'All') . "\n";
        $logs_text .= "\n" . str_repeat("=", 50) . "\n\n";
        
        foreach ($logs as $log) {
            $log_time = strtotime($log['created_at'] . ' UTC');
            $formatted_time = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $log_time);
            
            $logs_text .= "[{$formatted_time}] [{$log['log_level']}] {$log['message']}\n";
            
            if (!empty($log['context'])) {
                $context = json_decode($log['context'], true);
                if ($context && is_array($context)) {
                    $logs_text .= "Context: " . print_r($context, true) . "\n";
                }
            }
            
            $logs_text .= "\n" . str_repeat("-", 50) . "\n\n";
        }
        
        // Also include JSON export for structured data
        $logs_text .= "\n\n=== JSON EXPORT ===\n";
        $logs_text .= $this->database->export_logs_json($limit, $level);
        
        wp_send_json_success(array('logs_text' => $logs_text));
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get date filters
        $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'day';
        $product_id = isset($_GET['product_id']) ? sanitize_text_field($_GET['product_id']) : '';
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : date('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date('Y-m-d');
        
        // Get subscription aggregates
        $aggregates = $this->database->get_subscription_aggregates($product_id, $period, $start_date, $end_date);
        
        // Get all products for filter dropdown
        $products = $this->database->get_products();
        
        // Get latest daily snapshot
        $latest_snapshot = $this->database->get_daily_subscription($product_id);
        
        ?>
        <div class="wrap">
            <h1>Keap Reports Dashboard</h1>
            
            <div class="keap-reports-filters" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                    <input type="hidden" name="page" value="keap-reports">
                    <table class="form-table">
                        <tr>
                            <th><label for="period">View Period</label></th>
                            <td>
                                <select name="period" id="period">
                                    <option value="day" <?php selected($period, 'day'); ?>>Daily</option>
                                    <option value="week" <?php selected($period, 'week'); ?>>Weekly</option>
                                    <option value="month" <?php selected($period, 'month'); ?>>Monthly</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="product_id">Filter by Product</label></th>
                            <td>
                                <select name="product_id" id="product_id">
                                    <option value="">All Products</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo esc_attr($product['product_id']); ?>" <?php selected($product_id, $product['product_id']); ?>>
                                            <?php echo esc_html($product['product_name']); ?> (<?php echo esc_html($product['product_id']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="start_date">Start Date</label></th>
                            <td><input type="date" name="start_date" id="start_date" value="<?php echo esc_attr($start_date); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="end_date">End Date</label></th>
                            <td><input type="date" name="end_date" id="end_date" value="<?php echo esc_attr($end_date); ?>"></td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="Apply Filters">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=keap-reports')); ?>" class="button">Reset</a>
                    </p>
                </form>
            </div>
            
            <div class="keap-reports-status" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <h2>Daily Subscription Fetch Status</h2>
                <table class="form-table">
                    <?php
                    // Get cron status
                    $next_run_time = $this->cron ? $this->cron->get_next_daily_subscription_run_time() : false;
                    $is_scheduled = $this->cron ? $this->cron->is_daily_subscription_scheduled() : false;
                    $last_fetch_time = $this->database->get_last_daily_subscription_fetch_time();
                    
                    // Format next run time
                    $next_run_display = 'Not scheduled';
                    $next_run_status = 'warning';
                    if ($next_run_time) {
                        $next_run_timestamp = $next_run_time;
                        $next_run_display = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_run_timestamp);
                        $next_run_status = 'success';
                    }
                    
                    // Format last fetch time
                    $last_fetch_display = 'Never';
                    $last_fetch_status = 'warning';
                    if ($last_fetch_time) {
                        $last_fetch_timestamp = strtotime($last_fetch_time . ' UTC');
                        $last_fetch_display = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_fetch_timestamp);
                        $time_diff = time() - $last_fetch_timestamp;
                        if ($time_diff < 86400) { // Less than 24 hours
                            $last_fetch_status = 'success';
                        } elseif ($time_diff < 172800) { // Less than 48 hours
                            $last_fetch_status = 'warning';
                        } else {
                            $last_fetch_status = 'error';
                        }
                    }
                    ?>
                    <tr>
                        <th scope="row">Cron Status</th>
                        <td>
                            <?php if ($is_scheduled): ?>
                                <span style="color: #00a32a;">✓ Scheduled</span>
                            <?php else: ?>
                                <span style="color: #d63638;">✗ Not Scheduled</span>
                                <p class="description">The daily fetch may not run automatically. Visit any admin page to trigger scheduling.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Next Scheduled Run</th>
                        <td>
                            <strong><?php echo esc_html($next_run_display); ?></strong>
                            <?php if ($next_run_time): ?>
                                <p class="description">(<?php echo human_time_diff($next_run_time, current_time('timestamp')); ?> from now)</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Last Successful Fetch</th>
                        <td>
                            <strong><?php echo esc_html($last_fetch_display); ?></strong>
                            <?php if ($last_fetch_time): ?>
                                <?php 
                                $time_diff = time() - strtotime($last_fetch_time . ' UTC');
                                $time_ago = human_time_diff(strtotime($last_fetch_time . ' UTC'), current_time('timestamp'));
                                ?>
                                <p class="description">(<?php echo $time_ago; ?> ago)</p>
                            <?php else: ?>
                                <p class="description">Click "Fetch Daily Subscriptions Now" to get started.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="keap-reports-actions" style="margin: 20px 0;">
                <button type="button" id="fetch-daily-subscriptions-btn" class="button button-primary">Fetch Daily Subscriptions Now</button>
                <span id="fetch-status" style="margin-left: 10px;"></span>
            </div>
            
            <h2>Active Subscriptions by Product</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Period</th>
                        <th>Avg Count</th>
                        <th>Max Count</th>
                        <th>Min Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aggregates)): ?>
                        <tr>
                            <td colspan="6">No data found for the selected period. Try fetching daily subscriptions first.</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $grouped = array();
                        foreach ($aggregates as $agg) {
                            $key = $agg['product_id'];
                            if (!isset($grouped[$key])) {
                                $grouped[$key] = array(
                                    'product_id' => $agg['product_id'],
                                    'product_name' => '',
                                    'periods' => array()
                                );
                                $product = $this->database->get_product($agg['product_id']);
                                if ($product) {
                                    $grouped[$key]['product_name'] = $product['product_name'];
                                }
                            }
                            $grouped[$key]['periods'][] = $agg;
                        }
                        
                        foreach ($grouped as $product_id => $data):
                            $avg_total = 0;
                            $max_total = 0;
                            $min_total = 0;
                            foreach ($data['periods'] as $p) {
                                $avg_total += floatval($p['avg_count']);
                                $max_total = max($max_total, floatval($p['max_count']));
                                $min_total = $min_total == 0 ? floatval($p['min_count']) : min($min_total, floatval($p['min_count']));
                            }
                            $avg_total = count($data['periods']) > 0 ? $avg_total / count($data['periods']) : 0;
                        ?>
                            <tr>
                                <td><?php echo esc_html($product_id); ?></td>
                                <td><strong><?php echo esc_html($data['product_name'] ?: 'Unknown Product'); ?></strong></td>
                                <td><?php echo esc_html(ucfirst($period)); ?></td>
                                <td><?php echo number_format($avg_total, 0); ?></td>
                                <td><?php echo number_format($max_total, 0); ?></td>
                                <td><?php echo number_format($min_total, 0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Verify button exists
            if ($('#fetch-daily-subscriptions-btn').length === 0) {
                console.error('Keap Reports: Fetch button not found!');
            } else {
                console.log('Keap Reports: Fetch button found, attaching click handler');
            }
            
            // Verify ajaxurl is defined
            if (typeof ajaxurl === 'undefined') {
                console.error('Keap Reports: ajaxurl is not defined!');
            } else {
                console.log('Keap Reports: ajaxurl =', ajaxurl);
            }
            
            $('#fetch-daily-subscriptions-btn').on('click', function(e) {
                e.preventDefault();
                console.log('Keap Reports: Button clicked!');
                
                var $btn = $(this);
                var $status = $('#fetch-status');
                $btn.prop('disabled', true).text('Fetching...');
                $status.css('color', '#666').text('Fetching daily subscriptions... This may take a minute.');
                
                console.log('Keap Reports: Starting daily subscription fetch...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    timeout: 300000, // 5 minutes timeout
                    data: {
                        action: 'keap_reports_fetch_daily_subscriptions',
                        nonce: '<?php echo wp_create_nonce('keap_reports_fetch_daily_subscriptions'); ?>'
                    },
                    beforeSend: function() {
                        console.log('Keap Reports: Sending AJAX request...');
                    },
                    success: function(response) {
                        console.log('Keap Reports: AJAX response received:', response);
                        if (response && response.success) {
                            $status.css('color', 'green').text('✓ ' + (response.data && response.data.message ? response.data.message : 'Success'));
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            var errorMsg = response && response.data && response.data.message ? response.data.message : 'Unknown error';
                            $status.css('color', 'red').text('✗ ' + errorMsg);
                            console.error('Keap Reports: Fetch failed:', response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Keap Reports: AJAX Error:', {
                            xhr: xhr,
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status
                        });
                        
                        var errorMsg = '✗ Error fetching subscriptions';
                        if (status === 'timeout') {
                            errorMsg = '✗ Request timed out. The API call may be taking too long. Check logs for details.';
                        } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            errorMsg += ': ' + xhr.responseJSON.data.message;
                        } else if (xhr.responseText) {
                            try {
                                var jsonResponse = JSON.parse(xhr.responseText);
                                if (jsonResponse.data && jsonResponse.data.message) {
                                    errorMsg += ': ' + jsonResponse.data.message;
                                }
                            } catch(e) {
                                errorMsg += ' (HTTP ' + xhr.status + ')';
                            }
                        } else {
                            errorMsg += ' (HTTP ' + (xhr.status || 'unknown') + ')';
                        }
                        $status.css('color', 'red').text(errorMsg);
                    },
                    complete: function() {
                        console.log('Keap Reports: AJAX request completed');
                        $btn.prop('disabled', false).text('Fetch Daily Subscriptions Now');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render subscriptions page (detailed view)
     */
    public function render_subscriptions_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // This can be a more detailed view if needed
        $this->render_dashboard_page();
    }
    
    /**
     * Render products management page
     */
    public function render_products_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Ensure tables exist
        Keap_Reports_Database::create_tables();
        
        // Handle single delete
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['product_id'])) {
            check_admin_referer('delete_product_' . $_GET['product_id']);
            $this->database->delete_product(sanitize_text_field($_GET['product_id']));
            echo '<div class="notice notice-success is-dismissible"><p>Product deleted successfully!</p></div>';
        }
        
        // Handle bulk delete
        if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete' && isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
            check_admin_referer('bulk_delete_products');
            
            $deleted_count = 0;
            $failed_count = 0;
            
            foreach ($_POST['product_ids'] as $product_id) {
                $product_id = sanitize_text_field($product_id);
                if ($this->database->delete_product($product_id)) {
                    $deleted_count++;
                } else {
                    $failed_count++;
                }
            }
            
            if ($deleted_count > 0) {
                $message = sprintf('%d product(s) deleted successfully.', $deleted_count);
                if ($failed_count > 0) {
                    $message .= ' ' . sprintf('%d product(s) failed to delete.', $failed_count);
                }
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to delete products.</p></div>';
            }
        }
        
        // Handle form submission
        if (isset($_POST['keap_reports_save_product']) && check_admin_referer('keap_reports_save_product')) {
            $product_data = array(
                'product_id' => sanitize_text_field($_POST['product_id']),
                'product_name' => sanitize_text_field($_POST['product_name']),
                'sku' => isset($_POST['sku']) ? sanitize_text_field($_POST['sku']) : '',
                'price' => isset($_POST['price']) ? floatval($_POST['price']) : null,
                'status' => isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '',
                'category' => isset($_POST['category']) ? sanitize_text_field($_POST['category']) : ''
            );
            
            $result = $this->database->save_product($product_data);
            
            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>Product saved successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to save product.</p></div>';
            }
        }
        
        $products = $this->database->get_products();
        $edit_product = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['product_id'])) {
            $edit_product = $this->database->get_product(sanitize_text_field($_GET['product_id']));
        }
        
        ?>
        <div class="wrap">
            <h1>Product Management</h1>
            
            <h2><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=keap-reports-products')); ?>">
                <?php wp_nonce_field('keap_reports_save_product'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="product_id">Product ID</label></th>
                        <td>
                            <input type="text" id="product_id" name="product_id" value="<?php echo $edit_product ? esc_attr($edit_product['product_id']) : ''; ?>" class="regular-text" required <?php echo $edit_product ? 'readonly' : ''; ?> />
                            <p class="description">Keap Product ID (cannot be changed after creation)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="product_name">Product Name</label></th>
                        <td>
                            <input type="text" id="product_name" name="product_name" value="<?php echo $edit_product ? esc_attr($edit_product['product_name']) : ''; ?>" class="regular-text" required />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sku">SKU</label></th>
                        <td>
                            <input type="text" id="sku" name="sku" value="<?php echo $edit_product ? esc_attr($edit_product['sku']) : ''; ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="price">Price</label></th>
                        <td>
                            <input type="number" id="price" name="price" value="<?php echo $edit_product ? esc_attr($edit_product['price']) : ''; ?>" class="regular-text" step="0.01" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="status">Status</label></th>
                        <td>
                            <input type="text" id="status" name="status" value="<?php echo $edit_product ? esc_attr($edit_product['status']) : ''; ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="category">Category</label></th>
                        <td>
                            <input type="text" id="category" name="category" value="<?php echo $edit_product ? esc_attr($edit_product['category']) : ''; ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                <input type="submit" name="keap_reports_save_product" class="button button-primary" value="<?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>" />
                <?php if ($edit_product): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=keap-reports-products')); ?>" class="button">Cancel</a>
                <?php endif; ?>
            </form>
            
            <h2>Import from CSV</h2>
            <form id="import-csv-form" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('keap_reports_import_csv'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="csv_file">CSV File</label></th>
                        <td>
                            <input type="file" id="csv_file" name="csv_file" accept=".csv" required />
                            <p class="description">Upload a CSV file with columns: Id, Product Name, SKU, Price, ProductStatus, Product Category</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="keap_reports_import_csv" class="button button-primary" value="Import CSV" />
                </p>
            </form>
            
            <?php
            // Handle CSV import
            if (isset($_POST['keap_reports_import_csv']) && check_admin_referer('keap_reports_import_csv')) {
                if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                    $csv_content = file_get_contents($_FILES['csv_file']['tmp_name']);
                    $result = $this->database->import_products_from_csv($csv_content);
                    
                    if ($result['success']) {
                        echo '<div class="notice notice-success is-dismissible"><p>';
                        echo 'Import completed! ' . $result['imported'] . ' products imported, ' . $result['updated'] . ' products updated.';
                        if (!empty($result['errors'])) {
                            echo '<br>Errors: ' . implode(', ', $result['errors']);
                        }
                        echo '</p></div>';
                    } else {
                        echo '<div class="notice notice-error is-dismissible"><p>Import failed.</p></div>';
                    }
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Please select a valid CSV file.</p></div>';
                }
            }
            ?>
            
            <h2>Existing Products</h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=keap-reports-products')); ?>" id="bulk-products-form">
                <?php wp_nonce_field('bulk_delete_products'); ?>
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector" class="screen-reader-text">Select bulk action</label>
                        <select name="action" id="bulk-action-selector">
                            <option value="">Bulk Actions</option>
                            <option value="bulk_delete">Delete</option>
                        </select>
                        <input type="submit" id="doaction" class="button action" value="Apply">
                    </div>
                </div>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all">
                            </td>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Category</th>
                            <th>Latest Active Count</th>
                            <th>Last Fetched</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="10">No products found. Import from CSV or add manually.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): 
                                $latest_count = isset($product['latest_active_count']) ? $product['latest_active_count'] : null;
                                $latest_fetched = isset($product['latest_fetched_at']) ? $product['latest_fetched_at'] : null;
                                $count_display = $latest_count !== null ? number_format($latest_count) : '<span style="color: #999;">Never fetched</span>';
                                $count_style = '';
                                if ($latest_count === 0) {
                                    $count_style = 'color: #d63638; font-weight: bold;';
                                } elseif ($latest_count > 0) {
                                    $count_style = 'color: #00a32a; font-weight: bold;';
                                }
                                
                                $fetched_display = '-';
                                if ($latest_fetched) {
                                    $fetched_time = strtotime($latest_fetched . ' UTC');
                                    $fetched_display = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $fetched_time);
                                }
                            ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="product_ids[]" value="<?php echo esc_attr($product['product_id']); ?>" class="product-checkbox">
                                    </th>
                                    <td><?php echo esc_html($product['product_id']); ?></td>
                                    <td><strong><?php echo esc_html($product['product_name']); ?></strong></td>
                                    <td><?php echo esc_html($product['sku']); ?></td>
                                    <td><?php echo $product['price'] ? '$' . number_format($product['price'], 2) : '-'; ?></td>
                                    <td><?php echo esc_html($product['status']); ?></td>
                                    <td><?php echo esc_html($product['category']); ?></td>
                                    <td style="<?php echo esc_attr($count_style); ?>">
                                        <?php echo $count_display; ?>
                                    </td>
                                    <td style="font-size: 11px; color: #666;">
                                        <?php echo esc_html($fetched_display); ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=keap-reports-products&action=edit&product_id=' . $product['product_id'])); ?>" class="button button-small">Edit</a>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=keap-reports-products&action=delete&product_id=' . $product['product_id']), 'delete_product_' . $product['product_id'])); ?>" 
                                           class="button button-small" 
                                           onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
            
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Select all checkbox functionality
                $('#cb-select-all').on('change', function() {
                    $('.product-checkbox').prop('checked', $(this).prop('checked'));
                });
                
                // Update select all when individual checkboxes change
                $('.product-checkbox').on('change', function() {
                    var total = $('.product-checkbox').length;
                    var checked = $('.product-checkbox:checked').length;
                    $('#cb-select-all').prop('checked', total === checked);
                });
                
                // Handle bulk action form submission
                $('#bulk-products-form').on('submit', function(e) {
                    var action = $('#bulk-action-selector').val();
                    var checked = $('.product-checkbox:checked').length;
                    
                    if (action === 'bulk_delete') {
                        if (checked === 0) {
                            e.preventDefault();
                            alert('Please select at least one product to delete.');
                            return false;
                        }
                        
                        if (!confirm('Are you sure you want to delete ' + checked + ' product(s)? This action cannot be undone.')) {
                            e.preventDefault();
                            return false;
                        }
                    } else if (action !== '') {
                        if (checked === 0) {
                            e.preventDefault();
                            alert('Please select at least one product.');
                            return false;
                        }
                    }
                });
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for fetching daily subscriptions
     */
    public function ajax_fetch_daily_subscriptions() {
        // Set longer execution time for API calls
        set_time_limit(300); // 5 minutes
        
        // Log the request
        $this->database->add_log('AJAX: Daily subscription fetch requested', 'info');
        
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'keap_reports_fetch_daily_subscriptions')) {
            $this->database->add_log('AJAX: Nonce verification failed', 'error');
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'));
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            $this->database->add_log('AJAX: Insufficient permissions', 'error');
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        try {
            $result = $this->reports->fetch_daily_subscriptions();
            
            if ($result['success']) {
                $this->database->add_log('AJAX: Daily subscription fetch completed successfully', 'info');
                wp_send_json_success($result);
            } else {
                $this->database->add_log('AJAX: Daily subscription fetch failed: ' . (isset($result['message']) ? $result['message'] : 'Unknown error'), 'error');
                wp_send_json_error($result);
            }
        } catch (Exception $e) {
            $error_message = 'Exception: ' . $e->getMessage();
            $this->database->add_log('AJAX: Exception during daily subscription fetch: ' . $error_message, 'error', array(
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error(array('message' => $error_message));
        } catch (Error $e) {
            $error_message = 'Fatal Error: ' . $e->getMessage();
            $this->database->add_log('AJAX: Fatal error during daily subscription fetch: ' . $error_message, 'error', array(
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error(array('message' => $error_message));
        }
    }
    
    /**
     * AJAX handler for saving product
     */
    public function ajax_save_product() {
        check_ajax_referer('keap_reports_save_product', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        $product_data = array(
            'product_id' => sanitize_text_field($_POST['product_id']),
            'product_name' => sanitize_text_field($_POST['product_name']),
            'sku' => isset($_POST['sku']) ? sanitize_text_field($_POST['sku']) : '',
            'price' => isset($_POST['price']) ? floatval($_POST['price']) : null,
            'status' => isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '',
            'category' => isset($_POST['category']) ? sanitize_text_field($_POST['category']) : ''
        );
        
        $result = $this->database->save_product($product_data);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Product saved successfully', 'product_id' => $result));
        } else {
            wp_send_json_error(array('message' => 'Failed to save product'));
        }
    }
    
    /**
     * AJAX handler for deleting product
     */
    public function ajax_delete_product() {
        check_ajax_referer('keap_reports_delete_product', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        $product_id = sanitize_text_field($_POST['product_id']);
        $result = $this->database->delete_product($product_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Product deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete product'));
        }
    }
    
    /**
     * AJAX handler for CSV import
     */
    public function ajax_import_csv() {
        check_ajax_referer('keap_reports_import_csv', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => 'Please select a valid CSV file'));
            return;
        }
        
        $csv_content = file_get_contents($_FILES['csv_file']['tmp_name']);
        $result = $this->database->import_products_from_csv($csv_content);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * Render Tag Audit page
     */
    public function render_tag_audit_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get access tags from Academy Manager settings
        $keap_tags = get_option('alm_keap_tags', array());
        $all_tag_ids = array();
        foreach ($keap_tags as $level => $tags_string) {
            if (!empty($tags_string)) {
                $tag_ids = array_map('trim', explode(',', $tags_string));
                $all_tag_ids = array_merge($all_tag_ids, $tag_ids);
            }
        }
        $all_tag_ids = array_unique(array_filter($all_tag_ids));
        
        ?>
        <div class="wrap">
            <h1>Tag Audit - Membership Mismatches</h1>
            <p class="description">Find students whose membership is inactive but they still have access tags.</p>
            
            <div class="keap-reports-actions" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <h2>Access Tags Configuration</h2>
                <p>Found <strong><?php echo count($all_tag_ids); ?></strong> access tag ID(s) configured:</p>
                <ul>
                    <?php foreach ($keap_tags as $level => $tags_string): ?>
                        <?php if (!empty($tags_string)): ?>
                            <li><strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $level))); ?>:</strong> <?php echo esc_html($tags_string); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <p><a href="<?php echo esc_url(admin_url('admin.php?page=academy-manager-settings&tab=keap-tags')); ?>" target="_blank">Edit Access Tags Settings</a></p>
            </div>
            
            <div class="keap-reports-actions" style="margin: 20px 0;">
                <button type="button" id="scan-tag-mismatches-btn" class="button button-primary button-large">Scan for Mismatches</button>
                <span id="scan-status" style="margin-left: 10px;"></span>
                <p class="description" style="margin-top: 10px;">This will scan all contacts with access tags and check their subscription status. This may take several minutes.</p>
            </div>
            
            <div id="scan-results" style="display: none;">
                <h2>Scan Results</h2>
                <div id="scan-summary" style="background: #fff; padding: 15px; margin: 20px 0; border: 1px solid #ccd0d4;"></div>
                <table id="mismatches-table" class="wp-list-table widefat fixed striped" style="display: none;">
                    <thead>
                        <tr>
                            <th>Contact ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Access Tags</th>
                            <th>Subscription Status</th>
                            <th>Active</th>
                            <th>Expired</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="mismatches-tbody">
                    </tbody>
                </table>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#scan-tag-mismatches-btn').on('click', function() {
                var $btn = $(this);
                var $status = $('#scan-status');
                var $results = $('#scan-results');
                var $table = $('#mismatches-table');
                var $tbody = $('#mismatches-tbody');
                var $summary = $('#scan-summary');
                
                $btn.prop('disabled', true).text('Scanning...');
                $status.css('color', '#666').text('Scanning contacts... This may take several minutes.');
                $results.hide();
                $tbody.empty();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    timeout: 600000, // 10 minutes timeout
                    data: {
                        action: 'keap_reports_scan_tag_mismatches',
                        nonce: '<?php echo wp_create_nonce('keap_reports_scan_tag_mismatches'); ?>'
                    },
                    beforeSend: function() {
                        console.log('Keap Reports: Starting tag mismatch scan...');
                    },
                    success: function(response) {
                        console.log('Keap Reports: Scan response received:', response);
                        
                        if (response && response.success) {
                            $status.css('color', 'green').text('✓ ' + response.data.message);
                            
                            if (response.data.mismatches && response.data.mismatches.length > 0) {
                                $results.show();
                                $summary.html(
                                    '<h3>Summary</h3>' +
                                    '<p><strong>Contacts Checked:</strong> ' + response.data.contacts_checked + '</p>' +
                                    '<p><strong>Mismatches Found:</strong> ' + response.data.total_mismatches + '</p>'
                                );
                                
                                $table.show();
                                
                                $.each(response.data.mismatches, function(index, mismatch) {
                                    var row = '<tr>';
                                    row += '<td>' + mismatch.contact_id + '</td>';
                                    row += '<td><strong>' + (mismatch.name || 'N/A') + '</strong></td>';
                                    row += '<td>' + (mismatch.email || 'N/A') + '</td>';
                                    row += '<td>' + (mismatch.access_tags ? mismatch.access_tags.join(', ') : 'N/A') + '</td>';
                                    row += '<td>' + mismatch.subscription_status + '</td>';
                                    row += '<td>' + (mismatch.has_active ? 'Yes (' + mismatch.active_count + ')' : 'No') + '</td>';
                                    row += '<td>' + (mismatch.has_expired ? 'Yes (' + mismatch.expired_count + ')' : 'No') + '</td>';
                                    row += '<td>' + mismatch.subscription_count + '</td>';
                                    row += '<td><a href="https://app.infusionsoft.com/core/Contact/manageContact.jsp?view=edit&ID=' + mismatch.contact_id + '" target="_blank" class="button button-small">View in Keap</a></td>';
                                    row += '</tr>';
                                    $tbody.append(row);
                                });
                            } else {
                                $results.show();
                                $summary.html(
                                    '<h3>No Mismatches Found</h3>' +
                                    '<p>All contacts with access tags have active subscriptions.</p>' +
                                    '<p><strong>Contacts Checked:</strong> ' + response.data.contacts_checked + '</p>'
                                );
                            }
                        } else {
                            var errorMsg = response && response.data && response.data.message ? response.data.message : 'Unknown error';
                            $status.css('color', 'red').text('✗ ' + errorMsg);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Keap Reports: Scan Error:', xhr);
                        var errorMsg = '✗ Error scanning contacts';
                        if (status === 'timeout') {
                            errorMsg = '✗ Request timed out. The scan may be taking too long. Check logs for details.';
                        } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            errorMsg += ': ' + xhr.responseJSON.data.message;
                        }
                        $status.css('color', 'red').text(errorMsg);
                    },
                    complete: function() {
                        console.log('Keap Reports: Scan request completed');
                        $btn.prop('disabled', false).text('Scan for Mismatches');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for scanning tag mismatches
     */
    public function ajax_scan_tag_mismatches() {
        // Set longer execution time for API calls
        set_time_limit(600); // 10 minutes
        
        // Log the request
        $this->database->add_log('AJAX: Tag mismatch scan requested', 'info');
        
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'keap_reports_scan_tag_mismatches')) {
            $this->database->add_log('AJAX: Nonce verification failed', 'error');
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'));
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            $this->database->add_log('AJAX: Insufficient permissions', 'error');
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        try {
            $result = $this->reports->scan_tag_mismatches();
            
            if ($result['success']) {
                $this->database->add_log('AJAX: Tag mismatch scan completed successfully', 'info');
                wp_send_json_success($result);
            } else {
                $this->database->add_log('AJAX: Tag mismatch scan failed: ' . (isset($result['message']) ? $result['message'] : 'Unknown error'), 'error');
                wp_send_json_error($result);
            }
        } catch (Exception $e) {
            $error_message = 'Exception: ' . $e->getMessage();
            $this->database->add_log('AJAX: Exception during tag mismatch scan: ' . $error_message, 'error', array(
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error(array('message' => $error_message));
        } catch (Error $e) {
            $error_message = 'Fatal Error: ' . $e->getMessage();
            $this->database->add_log('AJAX: Fatal error during tag mismatch scan: ' . $error_message, 'error', array(
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error(array('message' => $error_message));
        }
    }
}

