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
     * Constructor
     * 
     * @param Keap_Reports_API $api
     * @param Keap_Reports_Reports $reports
     * @param Keap_Reports_Database $database
     */
    public function __construct($api, $reports, $database) {
        $this->api = $api;
        $this->reports = $reports;
        $this->database = $database;
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_keap_reports_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_keap_reports_fetch', array($this, 'ajax_fetch_report'));
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
            'Reports',
            'Reports',
            'manage_options',
            'keap-reports',
            array($this, 'render_display_page')
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
            'Settings',
            'Settings',
            'manage_options',
            'keap-reports-settings',
            array($this, 'render_settings_page')
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
        
        $result = $this->api->test_connection();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
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
        
        $result = $this->reports->fetch_report($report_id);
        
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
            
            // Update API key (empty string is valid - user might want to clear it)
            $result = update_option('keap_reports_api_key', $api_key);
            update_option('keap_reports_schedule_frequency', $schedule_frequency);
            update_option('keap_reports_debug_enabled', $debug_enabled);
            
            // Reschedule cron if frequency changed
            $cron = new Keap_Reports_Cron($this->reports);
            $cron->reschedule($schedule_frequency);
            
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
        
        // Get current settings
        $api_key = get_option('keap_reports_api_key', '');
        $schedule_frequency = get_option('keap_reports_schedule_frequency', 'monthly');
        $debug_enabled = get_option('keap_reports_debug_enabled', false);
        $cron = new Keap_Reports_Cron($this->reports);
        $next_run = $cron->get_next_run_time();
        
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
                            <label for="keap_reports_schedule_frequency">Schedule Frequency</label>
                        </th>
                        <td>
                            <select id="keap_reports_schedule_frequency" name="keap_reports_schedule_frequency">
                                <option value="hourly" <?php selected($schedule_frequency, 'hourly'); ?>>Hourly</option>
                                <option value="daily" <?php selected($schedule_frequency, 'daily'); ?>>Daily</option>
                                <option value="weekly" <?php selected($schedule_frequency, 'weekly'); ?>>Weekly</option>
                                <option value="monthly" <?php selected($schedule_frequency, 'monthly'); ?>>Monthly</option>
                            </select>
                            <p class="description">
                                How often to automatically fetch report data from Keap
                            </p>
                            <?php if ($next_run): ?>
                                <p class="description">
                                    Next scheduled run: <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_run)); ?> (<?php echo esc_html(wp_timezone_string()); ?>)
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <input type="submit" name="keap_reports_save_settings" class="button button-primary" value="Save Settings" />
            </form>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#test-connection-btn').on('click', function() {
                var $btn = $(this);
                var $status = $('#connection-status');
                
                $btn.prop('disabled', true).text('Testing...');
                $status.html('');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'keap_reports_test_connection',
                        nonce: '<?php echo wp_create_nonce('keap_reports_test_connection'); ?>',
                        api_key: $('#keap_reports_api_key').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.html('<span style="color: green;">✓ ' + response.data.message + '</span>');
                        } else {
                            $status.html('<span style="color: red;">✗ ' + response.data.message + '</span>');
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
            
            <div class="keap-reports-logs-clear" style="margin: 20px 0;">
                <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=keap-reports-logs')); ?>" onsubmit="return confirm('Are you sure you want to clear old logs? This action cannot be undone.');">
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
        <?php
    }
}

