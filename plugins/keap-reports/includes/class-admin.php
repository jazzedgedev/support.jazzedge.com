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
        add_action('wp_ajax_keap_reports_get_subscription_chart_data', array($this, 'ajax_get_subscription_chart_data'));
        add_action('wp_ajax_keap_reports_check_duplicates', array($this, 'ajax_check_duplicates'));
        add_action('wp_ajax_keap_reports_clear_duplicates', array($this, 'ajax_clear_duplicates'));
        add_action('wp_ajax_keap_reports_trigger_cron', array($this, 'ajax_trigger_cron'));
        add_action('wp_ajax_keap_reports_reschedule_cron', array($this, 'ajax_reschedule_cron'));
        add_action('wp_ajax_keap_reports_bulk_action', array($this, 'ajax_bulk_action'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Income Reports',
            'Income Reports',
            'manage_options',
            'keap-reports',
            array($this, 'render_dashboard_page'),
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
            'Subscriptions',
            'Subscriptions',
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
            'Products',
            'Products',
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
        
        register_setting('keap_reports_settings', 'keap_reports_logging_level', array(
            'default' => 'light'
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
            
            $schedule_frequency = isset($_POST['keap_reports_schedule_frequency']) ? sanitize_text_field($_POST['keap_reports_schedule_frequency']) : 'daily';
            $debug_enabled = isset($_POST['keap_reports_debug_enabled']) ? 1 : 0;
            $logging_level = isset($_POST['keap_reports_logging_level']) ? sanitize_text_field($_POST['keap_reports_logging_level']) : 'light';
            $auto_fetch_enabled = isset($_POST['keap_reports_auto_fetch_enabled']) ? 1 : 0;
            $daily_fetch_time = isset($_POST['keap_reports_daily_fetch_time']) ? sanitize_text_field($_POST['keap_reports_daily_fetch_time']) : '08:00';
            
            // Get iSDK credentials (for XML-RPC saved searches)
            $app_name = isset($_POST['keap_reports_app_name']) ? sanitize_text_field($_POST['keap_reports_app_name']) : '';
            $app_key = isset($_POST['keap_reports_app_key']) ? sanitize_text_field($_POST['keap_reports_app_key']) : '';
            
            // Update API key (empty string is valid - user might want to clear it)
            $result = update_option('keap_reports_api_key', $api_key);
            update_option('keap_reports_schedule_frequency', $schedule_frequency);
            update_option('keap_reports_debug_enabled', $debug_enabled);
            update_option('keap_reports_logging_level', $logging_level);
            update_option('keap_reports_auto_fetch_enabled', $auto_fetch_enabled);
            update_option('keap_reports_daily_fetch_time', $daily_fetch_time);
            update_option('keap_reports_app_name', $app_name);
            update_option('keap_reports_app_key', $app_key);
            
            // Handle cron scheduling based on auto_fetch setting
            $cron = new Keap_Reports_Cron($this->reports);
            if ($auto_fetch_enabled) {
                // Reschedule cron with new time
                $cron->reschedule_daily($daily_fetch_time);
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
        $schedule_frequency = get_option('keap_reports_schedule_frequency', 'daily');
        $debug_enabled = get_option('keap_reports_debug_enabled', false);
        $logging_level = get_option('keap_reports_logging_level', 'light');
        $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
        $daily_fetch_time = get_option('keap_reports_daily_fetch_time', '08:00');
        $app_name = get_option('keap_reports_app_name', 'ft217'); // Default to ft217
        $app_key = get_option('keap_reports_app_key', '');
        $cron = new Keap_Reports_Cron($this->reports);
        $next_run = $cron->get_next_run_time();
        $next_daily_run = $cron->get_next_daily_subscription_run_time();
        $is_scheduled = $cron->is_daily_subscription_scheduled();
        
        ?>
        <div class="wrap">
            <h1>Income Reports Settings</h1>
            
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
                                <label for="keap_reports_logging_level">Logging Level</label>
                            </th>
                            <td>
                                <select id="keap_reports_logging_level" name="keap_reports_logging_level">
                                    <option value="light" <?php selected($logging_level, 'light'); ?>>Light (Essential Only)</option>
                                    <option value="verbose" <?php selected($logging_level, 'verbose'); ?>>Verbose (All Logs)</option>
                                </select>
                                <p class="description">
                                    <strong>Light:</strong> Only logs errors, warnings, cron executions, and essential info. Saves database space.<br>
                                    <strong>Verbose:</strong> Logs all debug information including API request details, data processing steps, etc.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="keap_reports_debug_enabled">Enable WordPress Debug Log</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           id="keap_reports_debug_enabled" 
                                           name="keap_reports_debug_enabled" 
                                           value="1" 
                                           <?php checked($debug_enabled, true); ?> />
                                    Also log to WordPress error_log
                                </label>
                                <p class="description">
                                    When enabled, logs will also be written to WordPress error_log (requires WP_DEBUG_LOG in wp-config.php).
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
                                    (<?php echo human_time_diff($next_run, current_time('timestamp', true)); ?> from now)
                                    <br><small style="color: #646970;">Timezone: <?php echo esc_html(wp_timezone_string()); ?></small>
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
                                    (<?php echo human_time_diff($next_daily_run, current_time('timestamp', true)); ?> from now)
                                    <br><small style="color: #646970;">Timezone: <?php echo esc_html(wp_timezone_string()); ?></small>
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
            if (empty($_POST['keap_report_id']) || empty($_POST['report_year']) || empty($_POST['report_month'])) {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to save report. Report ID, Year, and Month are required.</p></div>';
            } else {
                // Auto-generate report name from month/year
                $report_year = absint($_POST['report_year']);
                $report_month = absint($_POST['report_month']);
                $report_type = sanitize_text_field($_POST['report_type']);
                
                // Generate name based on report type
                // Map old types to new types for backward compatibility
                $old_type = $report_type;
                if ($report_type === 'sales') {
                    $report_type = 'monthly_revenue';
                } elseif ($report_type === 'paid_starter') {
                    $report_type = 'count';
                } elseif ($report_type === 'intensives') {
                    $report_type = 'count_revenue';
                }
                
                // Check for title override - always allow override for all report types
                $title_override = isset($_POST['report_title_override']) ? trim(sanitize_text_field($_POST['report_title_override'])) : '';
                
                // If title override is provided, use it; otherwise auto-generate
                if (!empty($title_override)) {
                    $auto_name = $title_override;
                } else {
                    // Auto-generate based on report type
                    if ($report_type === 'monthly_revenue') {
                        // Monthly Revenue: auto-generate from month/year
                        $month_name = date('F', mktime(0, 0, 0, $report_month, 1));
                        $auto_name = $month_name . ' ' . $report_year . ' Sales';
                    } elseif ($report_type === 'count') {
                        // Count: auto-generate based on old type for backward compatibility
                        if ($old_type === 'paid_starter') {
                            $auto_name = 'Paid Starter Signups';
                        } else {
                            $auto_name = 'Count Report';
                        }
                    } elseif ($report_type === 'count_revenue') {
                        // Count/Revenue: auto-generate based on old type for backward compatibility
                        if ($old_type === 'intensives') {
                            $auto_name = 'Intensives';
                        } else {
                            $auto_name = 'Count/Revenue Report';
                        }
                    } else {
                        // Fallback
                        $month_name = date('F', mktime(0, 0, 0, $report_month, 1));
                        $auto_name = $month_name . ' ' . $report_year . ' Sales';
                    }
                }
                
                $report_data = array(
                    'id' => isset($_POST['report_id']) ? absint($_POST['report_id']) : 0,
                    'name' => $auto_name,
                    'report_id' => absint($_POST['keap_report_id']),
                    'report_uuid' => isset($_POST['report_uuid']) && !empty($_POST['report_uuid']) ? sanitize_text_field($_POST['report_uuid']) : null,
                    'report_type' => $report_type,
                    'filter_product_id' => isset($_POST['filter_product_id']) ? sanitize_text_field($_POST['filter_product_id']) : '',
                    'report_year' => $report_year,
                    'report_month' => $report_month,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'show_on_dashboard' => isset($_POST['show_on_dashboard']) ? 1 : 0
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
        
        // Debug: Check if query is working
        global $wpdb;
        $table_name = $wpdb->prefix . 'keap_reports';
        $direct_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        
        // If get_reports() is empty but we have reports in DB, try a simpler query
        if (empty($reports) && $direct_count > 0) {
            error_log('Keap Reports: get_reports() returned empty but DB has ' . $direct_count . ' reports. Trying fallback query.');
            // Fallback: simple query without complex sorting
            $reports = $wpdb->get_results(
                "SELECT * FROM {$table_name} ORDER BY name ASC",
                ARRAY_A
            );
            $reports = $reports ? $reports : array();
        }
        
        // Get report to edit
        $edit_report = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['report_id'])) {
            $edit_report = $this->database->get_report(absint($_GET['report_id']));
        }
        
        ?>
        <div class="wrap">
            <h1>Manage Reports</h1>
            
            <?php
            // Debug output (remove after testing)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'keap_reports';
                $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
                echo '<!-- Debug: Reports count: ' . $count . ', get_reports() returned: ' . count($reports) . ' -->';
            }
            ?>
            
            <h2><?php echo $edit_report ? 'Edit Report' : 'Add New Report'; ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=keap-reports-manage')); ?>">
                <?php wp_nonce_field('keap_reports_save_report'); ?>
                <?php if ($edit_report): ?>
                    <input type="hidden" name="report_id" value="<?php echo esc_attr($edit_report['id']); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="report_year">Report Year <span style="color: red;">*</span></label>
                        </th>
                        <td>
                            <select id="report_year" name="report_year" required>
                                <option value="">-- Select Year --</option>
                                <?php
                                $current_year = intval(date('Y'));
                                $selected_year = $edit_report && isset($edit_report['report_year']) ? intval($edit_report['report_year']) : null;
                                for ($y = $current_year - 2; $y <= $current_year + 1; $y++):
                                    ?>
                                    <option value="<?php echo $y; ?>" <?php selected($selected_year, $y); ?>><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                            <p class="description">The year this report represents (required)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="report_month">Report Month <span style="color: red;">*</span></label>
                        </th>
                        <td>
                            <select id="report_month" name="report_month" required>
                                <option value="">-- Select Month --</option>
                                <?php
                                $selected_month = $edit_report && isset($edit_report['report_month']) ? intval($edit_report['report_month']) : null;
                                $months = array(
                                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                );
                                foreach ($months as $num => $name):
                                    ?>
                                    <option value="<?php echo $num; ?>" <?php selected($selected_month, $num); ?>><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">The month this report represents (required). Report name will be auto-generated as "Month Year Sales" (or "Paid Starter Signups" for paid starter reports)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="keap_report_id">Keap Report ID <span style="color: red;">*</span></label>
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
                            <label for="report_type">Report Type</label>
                        </th>
                        <td>
                            <select id="report_type" name="report_type">
                                <option value="monthly_revenue" <?php echo ($edit_report && in_array($edit_report['report_type'], array('sales', 'monthly_revenue'))) ? 'selected' : ''; ?>>Monthly Revenue</option>
                                <option value="count" <?php echo ($edit_report && in_array($edit_report['report_type'], array('paid_starter', 'count'))) ? 'selected' : ''; ?>>Count</option>
                                <option value="count_revenue" <?php echo ($edit_report && in_array($edit_report['report_type'], array('intensives', 'count_revenue'))) ? 'selected' : ''; ?>>Count/Revenue</option>
                            </select>
                            <p class="description">Type of report (affects how data is aggregated)</p>
                        </td>
                    </tr>
                    <tr id="title-override-row">
                        <th scope="row">
                            <label for="report_title_override">Report Title</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="report_title_override" 
                                   name="report_title_override" 
                                   value="<?php echo $edit_report && isset($edit_report['name']) ? esc_attr($edit_report['name']) : ''; ?>" 
                                   class="regular-text" />
                            <p class="description">Optional: Override the auto-generated title. Leave blank to use auto-generated title based on report type.</p>
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
                    <tr id="show-dashboard-row" style="<?php echo ($edit_report && in_array($edit_report['report_type'], array('intensives'))) ? '' : 'display: none;'; ?>">
                        <th scope="row">
                            <label for="show_on_dashboard">Show on Dashboard</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       id="show_on_dashboard" 
                                       name="show_on_dashboard" 
                                       value="1" 
                                       <?php checked($edit_report && isset($edit_report['show_on_dashboard']) && $edit_report['show_on_dashboard'], true); ?> />
                                Display this report's data as a KPI card on the dashboard
                            </label>
                            <p class="description">When enabled, this report will show as a card on the dashboard with orders and revenue.</p>
                        </td>
                    </tr>
                </table>
                
                <input type="submit" name="keap_reports_save_report" class="button button-primary" value="<?php echo $edit_report ? 'Update Report' : 'Add Report'; ?>" />
                <?php if ($edit_report): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=keap-reports-manage')); ?>" class="button">Cancel</a>
                <?php endif; ?>
            </form>
            
            <h2>Existing Reports</h2>
            
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="bulk_action" id="bulk-action-selector">
                        <option value="">Bulk Actions</option>
                        <option value="activate">Enable Auto-Fetch</option>
                        <option value="deactivate">Disable Auto-Fetch</option>
                    </select>
                    <input type="button" id="do-bulk-action" class="button action" value="Apply" />
                </div>
            </div>
            
            <style>
                .wp-list-table th.column-actions,
                .wp-list-table td.column-actions {
                    width: 280px !important;
                    min-width: 280px;
                }
                .wp-list-table th.check-column,
                .wp-list-table td.check-column {
                    width: 2.2em;
                }
            </style>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" id="cb-select-all" /></th>
                        <th>Name</th>
                        <th>Report ID</th>
                        <th>Type</th>
                        <th>Month/Year</th>
                        <th>Status</th>
                        <th>Last Fetch</th>
                        <th class="column-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="8">No reports configured yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $report): ?>
                            <?php
                            // Get the most recent fetch time across all months
                            $last_fetch_time = $this->database->get_last_fetch_time($report['id']);
                            ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="report_ids[]" value="<?php echo esc_attr($report['id']); ?>" class="report-checkbox" />
                                </th>
                                <td><strong><?php echo esc_html($report['name']); ?></strong></td>
                                <td><?php echo esc_html($report['report_id']); ?></td>
                                <td><?php echo esc_html(ucfirst($report['report_type'])); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($report['report_year']) && !empty($report['report_month'])) {
                                        $month_name = date('M', mktime(0, 0, 0, $report['report_month'], 1));
                                        echo esc_html($month_name . ' ' . $report['report_year']);
                                    } else {
                                        echo '<span style="color: gray;">Not set</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo $report['is_active'] ? '<span style="color: green;">Active</span>' : '<span style="color: gray;">Inactive</span>'; ?></td>
                                <td>
                                    <?php 
                                    if ($last_fetch_time) {
                                        echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_fetch_time)));
                                    } else {
                                        echo '<span style="color: gray;">Never</span>';
                                    }
                                    ?>
                                </td>
                                <td class="column-actions">
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
                // Show/hide fields based on report type
                function toggleReportTypeFields() {
                    var reportType = $('#report_type').val();
                    
                    // Hide product filter (no longer used)
                    $('#product-filter-row').hide();
                    
                    // Title override is always shown for all report types
                    $('#title-override-row').show();
                    
                    // Show/hide "Show on Dashboard" row for count and count_revenue
                    if (reportType === 'count' || reportType === 'count_revenue') {
                        $('#show-dashboard-row').show();
                    } else {
                        $('#show-dashboard-row').hide();
                    }
                }
                
                // Keep old function name for compatibility
                function toggleProductFilter() {
                    toggleReportTypeFields();
                }
            
            $('#report_type').on('change', toggleReportTypeFields);
            toggleReportTypeFields(); // Run on page load
            
            // Select all checkbox functionality
            $('#cb-select-all').on('change', function() {
                $('.report-checkbox').prop('checked', $(this).prop('checked'));
            });
            
            // Update select all checkbox when individual checkboxes change
            $('.report-checkbox').on('change', function() {
                var total = $('.report-checkbox').length;
                var checked = $('.report-checkbox:checked').length;
                $('#cb-select-all').prop('checked', total === checked);
            });
            
            // Bulk actions
            $('#do-bulk-action').on('click', function() {
                var action = $('#bulk-action-selector').val();
                var selectedReports = $('.report-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();
                
                if (!action) {
                    alert('Please select a bulk action.');
                    return;
                }
                
                if (selectedReports.length === 0) {
                    alert('Please select at least one report.');
                    return;
                }
                
                var actionText = action === 'activate' ? 'enable auto-fetch for' : 'disable auto-fetch for';
                if (!confirm('Are you sure you want to ' + actionText + ' ' + selectedReports.length + ' report(s)?')) {
                    return;
                }
                
                var $btn = $(this);
                $btn.prop('disabled', true).val('Processing...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'keap_reports_bulk_action',
                        nonce: '<?php echo wp_create_nonce('keap_reports_bulk_action'); ?>',
                        bulk_action: action,
                        report_ids: selectedReports
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + (response.data.message || 'Unknown error occurred'));
                            $btn.prop('disabled', false).val('Apply');
                        }
                    },
                    error: function() {
                        alert('An error occurred while processing the bulk action.');
                        $btn.prop('disabled', false).val('Apply');
                    }
                });
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
        
        // Check if user wants to see specific month or latest data
        $show_specific_month = isset($_GET['year']) || isset($_GET['month']);
        
        if ($show_specific_month) {
            // Get all reports data for the selected period
            $reports_data = $this->reports->get_all_reports_data($year, $month);
        } else {
            // Get latest data for each report (regardless of month)
            $reports_data = $this->database->get_all_reports_latest_data();
        }
        
        ?>
        <div class="wrap">
            <h1>Income Reports</h1>
            
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
                            if ($show_specific_month) {
                                // Use the selected month/year for comparison
                                $data_year = $year;
                                $data_month = $month;
                            } else {
                                // Use the month/year from the fetched data
                                $data_year = isset($report['data_year']) ? intval($report['data_year']) : $year;
                                $data_month = isset($report['data_month']) ? intval($report['data_month']) : $month;
                            }
                            
                            // Get comparison data for the actual data month
                            $comparison = $this->reports->get_monthly_comparison($report['id'], $data_year, $data_month);
                            $current_value = $comparison['current'];
                            $previous_value = $comparison['previous'];
                            $change = $comparison['change'];
                            $change_percent = $comparison['change_percent'];
                            $ytd = $comparison['ytd'];
                            
                            // If showing latest data, show which month it's for
                            $month_label = '';
                            if (!$show_specific_month && isset($report['data_year']) && isset($report['data_month'])) {
                                $month_label = ' (' . date('M Y', mktime(0, 0, 0, $report['data_month'], 1, $report['data_year'])) . ')';
                            }
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($report['name']); ?><?php echo esc_html($month_label); ?></strong></td>
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
            <h1>Income Reports Logs</h1>
            
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
                    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=keap-reports-logs')); ?>" style="display: inline-block; margin-right: 10px;">
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
                    
                    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=keap-reports-logs')); ?>" style="display: inline-block;">
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
                                            $context_json = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                            $context_text = print_r($context, true);
                                    ?>
                                        <details>
                                            <summary style="cursor: pointer; color: #2271b1;">View Context</summary>
                                            <div style="position: relative; margin-top: 5px;">
                                                <button type="button" class="button button-small copy-context-btn" 
                                                        style="position: absolute; top: 5px; right: 5px; z-index: 10;"
                                                        data-context-json='<?php echo esc_attr($context_json); ?>'
                                                        data-context-text='<?php echo esc_attr($context_text); ?>'
                                                        title="Copy context to clipboard">
                                                    <span class="dashicons dashicons-clipboard" style="font-size: 14px; width: 14px; height: 14px;"></span> Copy
                                                </button>
                                                <pre style="background: #f0f0f1; padding: 10px; margin-top: 5px; border-radius: 3px; font-size: 11px; max-height: 200px; overflow: auto; padding-right: 80px;"><?php echo esc_html($context_text); ?></pre>
                                            </div>
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
            // Copy individual context button
            $('.copy-context-btn').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $btn = $(this);
                // Use attr() instead of data() to get the raw string value
                // data() tries to parse JSON, which causes [object Object] issue
                var contextText = $btn.attr('data-context-text');
                var contextJson = $btn.attr('data-context-json');
                
                // Prefer text format (print_r) as it's more readable
                var textToCopy = contextText || contextJson || '';
                
                // Create temporary textarea
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(textToCopy).select();
                
                try {
                    var success = document.execCommand('copy');
                    if (success) {
                        var originalHtml = $btn.html();
                        $btn.html('<span class="dashicons dashicons-yes" style="color: #00a32a; font-size: 14px; width: 14px; height: 14px;"></span> Copied!');
                        setTimeout(function() {
                            $btn.html(originalHtml);
                        }, 2000);
                    } else {
                        alert('Failed to copy. Please select and copy manually.');
                    }
                } catch (err) {
                    // Fallback: show in alert or prompt
                    prompt('Copy this text:', textToCopy);
                }
                
                $temp.remove();
            });
            
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
     * Render dashboard page - Marketer-focused metrics
     */
    public function render_dashboard_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get current period
        $current_year = intval(date('Y'));
        $current_month = intval(date('n'));
        $last_month = $current_month - 1;
        $last_month_year = $current_year;
        if ($last_month < 1) {
            $last_month = 12;
            $last_month_year = $current_year - 1;
        }
        
        // Get all active reports (exclude count and count_revenue - they're tracked separately for KPIs only)
        $all_reports = $this->database->get_reports(true);
        $reports = array_filter($all_reports, function($report) {
            $type = isset($report['report_type']) ? $report['report_type'] : '';
            // Map old types for backward compatibility
            if ($type === 'paid_starter') $type = 'count';
            if ($type === 'intensives') $type = 'count_revenue';
            // Exclude count and count_revenue from revenue calculations
            return !in_array($type, array('count', 'count_revenue'));
        });
        
        // Get count and count_revenue reports that should show on dashboard
        $dashboard_reports = array_filter($all_reports, function($report) {
            $type = isset($report['report_type']) ? $report['report_type'] : '';
            // Map old types
            if ($type === 'paid_starter') $type = 'count';
            if ($type === 'intensives') $type = 'count_revenue';
            return in_array($type, array('count', 'count_revenue')) 
                && isset($report['show_on_dashboard']) && $report['show_on_dashboard'] == 1;
        });
        
        // Calculate total revenue metrics
        $total_current_revenue = 0;
        $total_last_revenue = 0;
        $total_current_orders = 0;
        $total_last_orders = 0;
        $total_ytd_revenue = 0;
        $total_ytd_orders = 0;
        
        $reports_data = array();
        foreach ($reports as $report) {
            $comparison = $this->reports->get_monthly_comparison($report['id'], $current_year, $current_month);
            $reports_data[] = array_merge($report, $comparison);
            
            $total_current_revenue += isset($comparison['current_revenue']) ? $comparison['current_revenue'] : 0;
            $total_last_revenue += isset($comparison['previous_revenue']) ? $comparison['previous_revenue'] : 0;
            $total_current_orders += isset($comparison['current_orders']) ? $comparison['current_orders'] : 0;
            $total_last_orders += isset($comparison['previous_orders']) ? $comparison['previous_orders'] : 0;
            $total_ytd_revenue += isset($comparison['ytd']) ? $comparison['ytd'] : 0;
            $total_ytd_orders += isset($comparison['ytd_orders']) ? $comparison['ytd_orders'] : 0;
        }
        
        $revenue_change = $total_current_revenue - $total_last_revenue;
        $revenue_change_percent = $total_last_revenue > 0 ? ($revenue_change / $total_last_revenue) * 100 : 0;
        $orders_change = $total_current_orders - $total_last_orders;
        $orders_change_percent = $total_last_orders > 0 ? ($orders_change / $total_last_orders) * 100 : 0;
        
        // Same month last year (for comparison that matches Revenue Trend chart "Last Year")
        $last_year = $current_year - 1;
        $total_last_year_revenue = 0;
        foreach ($reports as $report) {
            $ly_comparison = $this->reports->get_monthly_comparison($report['id'], $last_year, $current_month);
            $total_last_year_revenue += isset($ly_comparison['current_revenue']) ? $ly_comparison['current_revenue'] : 0;
        }
        $revenue_change_vs_last_year = $total_current_revenue - $total_last_year_revenue;
        $revenue_change_vs_last_year_percent = $total_last_year_revenue > 0 ? ($revenue_change_vs_last_year / $total_last_year_revenue) * 100 : 0;
        $last_year_month_name = date('M Y', mktime(0, 0, 0, $current_month, 1, $last_year));
        
        // Get subscription metrics - sum across all products for latest date
        $subscription_count = $this->database->get_total_active_subscriptions();
        
        // Get last month subscription for comparison - use the last day of last month
        $last_month_date = new DateTime();
        $last_month_date->modify('last day of last month');
        $last_month_subscription = $this->database->get_total_active_subscriptions_for_date(
            intval($last_month_date->format('Y')),
            intval($last_month_date->format('n')),
            intval($last_month_date->format('j'))
        );
        $subscription_change = $subscription_count - $last_month_subscription;
        $subscription_change_percent = $last_month_subscription > 0 ? ($subscription_change / $last_month_subscription) * 100 : 0;
        
        // Get free trial signups comparison
        $trial_comparison = $this->database->get_free_trial_comparison(48);
        
        // Get cron status
        $next_report_run = $this->cron ? $this->cron->get_next_run_time() : false;
        $next_subscription_run = $this->cron ? $this->cron->get_next_daily_subscription_run_time() : false;
        $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
        
        // Get chart filter settings
        $chart_period = isset($_GET['chart_period']) ? sanitize_text_field($_GET['chart_period']) : date('Y');
        $chart_view = isset($_GET['chart_view']) ? sanitize_text_field($_GET['chart_view']) : 'monthly';
        $chart_compare = isset($_GET['chart_compare']) ? sanitize_text_field($_GET['chart_compare']) : 'lastyear';
        
        // Get starter signups chart filter settings
        $starter_chart_period = isset($_GET['starter_chart_period']) ? sanitize_text_field($_GET['starter_chart_period']) : '12months';
        $starter_chart_view = isset($_GET['starter_chart_view']) ? sanitize_text_field($_GET['starter_chart_view']) : 'monthly';
        $starter_chart_compare = isset($_GET['starter_chart_compare']) ? sanitize_text_field($_GET['starter_chart_compare']) : 'none';
        
        // Get revenue history for chart based on filter
        $revenue_history = $this->get_revenue_history_data($reports, $chart_period, $chart_view, $chart_compare);
        
        // Get starter signups history for chart (both free and paid)
        $starter_history = $this->get_starter_signups_history_data($starter_chart_period, $starter_chart_view, $starter_chart_compare);
        
        // Get current month free and paid starter signups for KPI cards
        $current_year = intval(date('Y'));
        $current_month = intval(date('n'));
        $free_starter_current = $this->database->get_free_trial_signups(48, 'month', $current_year, $current_month);
        $paid_starter_current = $this->database->get_starter_signups('paid_starter', $current_year, $current_month);
        
        // Get previous month for comparison
        $prev_month = $current_month - 1;
        $prev_year = $current_year;
        if ($prev_month < 1) {
            $prev_month = 12;
            $prev_year = $current_year - 1;
        }
        $free_starter_prev = $this->database->get_free_trial_signups(48, 'month', $prev_year, $prev_month);
        $paid_starter_prev = $this->database->get_starter_signups('paid_starter', $prev_year, $prev_month);
        
        // Calculate changes
        $free_starter_change = $free_starter_current['count'] - $free_starter_prev['count'];
        $free_starter_change_percent = $free_starter_prev['count'] > 0 ? ($free_starter_change / $free_starter_prev['count']) * 100 : ($free_starter_change > 0 ? 100 : 0);
        
        $paid_starter_change = $paid_starter_current['count'] - $paid_starter_prev['count'];
        $paid_starter_change_percent = $paid_starter_prev['count'] > 0 ? ($paid_starter_change / $paid_starter_prev['count']) * 100 : ($paid_starter_change > 0 ? 100 : 0);
        
        // FluentCart orders (completed only) for dashboard card
        $fluentcart_current = $this->database->get_fluentcart_month_totals($current_year, $current_month);
        $fluentcart_prev = $this->database->get_fluentcart_month_totals($prev_year, $prev_month);
        $fluentcart_last_year = $this->database->get_fluentcart_month_totals($last_year, $current_month);
        $fluentcart_orders_change = $fluentcart_current['orders'] - $fluentcart_prev['orders'];
        $fluentcart_orders_change_percent = $fluentcart_prev['orders'] > 0 ? ($fluentcart_orders_change / $fluentcart_prev['orders']) * 100 : ($fluentcart_orders_change > 0 ? 100 : 0);
        $fluentcart_revenue_change = $fluentcart_current['revenue'] - $fluentcart_prev['revenue'];
        $fluentcart_revenue_change_percent = $fluentcart_prev['revenue'] > 0 ? ($fluentcart_revenue_change / $fluentcart_prev['revenue']) * 100 : ($fluentcart_revenue_change > 0 ? 100 : 0);
        
        // Combined income (Keap + FluentCart) for "This Month Revenue" card
        $total_income_current = $total_current_revenue + $fluentcart_current['revenue'];
        $total_income_last = $total_last_revenue + $fluentcart_prev['revenue'];
        $total_income_last_year = $total_last_year_revenue + $fluentcart_last_year['revenue'];
        $revenue_change_combined = $total_income_current - $total_income_last;
        $revenue_change_combined_percent = $total_income_last > 0 ? ($revenue_change_combined / $total_income_last) * 100 : ($revenue_change_combined > 0 ? 100 : 0);
        $revenue_change_vs_last_year_combined = $total_income_current - $total_income_last_year;
        $revenue_change_vs_last_year_combined_percent = $total_income_last_year > 0 ? ($revenue_change_vs_last_year_combined / $total_income_last_year) * 100 : ($revenue_change_vs_last_year_combined > 0 ? 100 : 0);
        
        // YTD includes FluentCart (Jan through current month)
        $fluentcart_ytd = $this->database->get_fluentcart_ytd($current_year, $current_month);
        $total_ytd_revenue_display = $total_ytd_revenue + $fluentcart_ytd['revenue'];
        $total_ytd_orders_display = $total_ytd_orders + $fluentcart_ytd['orders'];
        
        // Total orders (all sources) for replacement KPI card
        $total_orders_combined_current = $total_current_orders + $fluentcart_current['orders'];
        $total_orders_combined_prev = $total_last_orders + $fluentcart_prev['orders'];
        $total_orders_combined_change = $total_orders_combined_current - $total_orders_combined_prev;
        $total_orders_combined_change_percent = $total_orders_combined_prev > 0 ? ($total_orders_combined_change / $total_orders_combined_prev) * 100 : ($total_orders_combined_change > 0 ? 100 : 0);
        
        ?>
        <div class="wrap">
            <h1>Business Performance Dashboard</h1>
            
            <style>
                .keap-dashboard-kpi {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 20px;
                    margin: 20px 0;
                }
                .keap-kpi-card {
                    background: #fff;
                    border: 1px solid #ccd0d4;
                    border-left: 4px solid #2271b1;
                    padding: 20px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                .keap-kpi-card.revenue { border-left-color: #00a32a; }
                .keap-kpi-card.orders { border-left-color: #2271b1; }
                .keap-kpi-card.subscriptions { border-left-color: #d63638; }
                .keap-kpi-card.trials { border-left-color: #8c8f94; }
                .keap-kpi-card.paid-starter { border-left-color: #646970; }
                .keap-kpi-card.intensives { border-left-color: #d63638; }
                .keap-kpi-card.fluentcart { border-left-color: #7c3aed; }
                .keap-kpi-card.ytd { border-left-color: #f0b849; }
                .keap-kpi-label {
                    font-size: 14px;
                    color: #646970;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 8px;
                }
                .keap-kpi-value {
                    font-size: 32px;
                    font-weight: 600;
                    color: #1d2327;
                    margin-bottom: 8px;
                }
                .keap-kpi-change {
                    font-size: 14px;
                    font-weight: 500;
                }
                .keap-kpi-change.positive { color: #00a32a; }
                .keap-kpi-change.negative { color: #d63638; }
                .keap-dashboard-section {
                    background: #fff;
                    border: 1px solid #ccd0d4;
                    padding: 20px;
                    margin: 20px 0;
                }
                .keap-dashboard-section h2 {
                    margin-top: 0;
                    border-bottom: 1px solid #ccd0d4;
                    padding-bottom: 10px;
                }
                .keap-status-badge {
                    display: inline-block;
                    padding: 4px 8px;
                    border-radius: 3px;
                    font-size: 12px;
                    font-weight: 600;
                }
                .keap-status-badge.success {
                    background: #00a32a;
                    color: #fff;
                }
                .keap-status-badge.warning {
                    background: #f0b849;
                    color: #1d2327;
                }
                .keap-status-badge.error {
                    background: #d63638;
                    color: #fff;
                }
                .keap-dashboard-section table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                    background: #fff;
                }
                .keap-dashboard-section table thead {
                    background: #f6f7f7;
                }
                .keap-dashboard-section table th {
                    padding: 12px 15px;
                    text-align: left;
                    font-weight: 600;
                    border-bottom: 2px solid #c3c4c7;
                    color: #1d2327;
                }
                .keap-dashboard-section table td {
                    padding: 12px 15px;
                    border-bottom: 1px solid #dcdcde;
                    color: #2c3338;
                }
                .keap-dashboard-section table tbody tr:nth-child(even) {
                    background: #f9f9f9;
                }
                .keap-dashboard-section table tbody tr:nth-child(odd) {
                    background: #fff;
                }
                .keap-dashboard-section table tbody tr:hover {
                    background: #f0f6fc !important;
                }
                .keap-dashboard-section table tbody tr:last-child td {
                    border-bottom: none;
                }
            </style>
            
            <!-- KPI Cards -->
            <div class="keap-dashboard-kpi">
                <div class="keap-kpi-card revenue">
                    <div class="keap-kpi-label">This Month Revenue</div>
                    <div class="keap-kpi-value">$<?php echo number_format($total_income_current, 2); ?></div>
                    <div class="keap-kpi-change <?php echo $revenue_change_vs_last_year_combined >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $revenue_change_vs_last_year_combined >= 0 ? '↑' : '↓'; ?> 
                        $<?php echo number_format(abs($revenue_change_vs_last_year_combined), 2); ?>
                        (<?php echo number_format(abs($revenue_change_vs_last_year_combined_percent), 1); ?>%)
                        vs <?php echo esc_html($last_year_month_name); ?>
                    </div>
                    <div class="keap-kpi-change keap-kpi-change-secondary <?php echo $revenue_change_combined >= 0 ? 'positive' : 'negative'; ?>" style="font-size: 0.85em; margin-top: 4px; opacity: 0.9;">
                        <?php echo $revenue_change_combined >= 0 ? '↑' : '↓'; ?> 
                        $<?php echo number_format(abs($revenue_change_combined), 2); ?> (<?php echo number_format(abs($revenue_change_combined_percent), 1); ?>%) vs last month
                    </div>
                </div>
                
                <div class="keap-kpi-card orders">
                    <div class="keap-kpi-label">This Month Orders</div>
                    <div class="keap-kpi-value"><?php echo number_format($total_current_orders); ?></div>
                    <div class="keap-kpi-change <?php echo $orders_change >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $orders_change >= 0 ? '↑' : '↓'; ?> 
                        <?php echo number_format(abs($orders_change_percent), 1); ?>% 
                        vs last month
                    </div>
                </div>
                
                <div class="keap-kpi-card subscriptions">
                    <div class="keap-kpi-label">Active Subscriptions</div>
                    <div class="keap-kpi-value"><?php echo number_format($subscription_count); ?></div>
                    <div class="keap-kpi-change <?php echo $subscription_change >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $subscription_change >= 0 ? '↑' : '↓'; ?> 
                        <?php echo number_format(abs($subscription_change_percent), 1); ?>% 
                        vs last month
                    </div>
                </div>
                
                <div class="keap-kpi-card trials">
                    <div class="keap-kpi-label">Free Trial Signups</div>
                    <div class="keap-kpi-value"><?php echo number_format($trial_comparison['current']); ?></div>
                    <div class="keap-kpi-change <?php echo $trial_comparison['change'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $trial_comparison['change'] >= 0 ? '↑' : '↓'; ?> 
                        <?php echo number_format(abs($trial_comparison['change_percent']), 1); ?>% 
                        vs last month
                    </div>
                </div>
                
                <div class="keap-kpi-card paid-starter">
                    <div class="keap-kpi-label">Paid Starter Signups</div>
                    <div class="keap-kpi-value"><?php echo number_format($paid_starter_current['count']); ?></div>
                    <?php $paid_starter_revenue = (isset($paid_starter_current['revenue']) && $paid_starter_current['revenue'] > 0) ? floatval($paid_starter_current['revenue']) : ($paid_starter_current['count'] * 7.0); ?>
                    <div class="keap-kpi-value" style="font-size: 20px; margin-top: 5px;">$<?php echo number_format($paid_starter_revenue, 2); ?></div>
                    <div class="keap-kpi-change <?php echo $paid_starter_change >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $paid_starter_change >= 0 ? '↑' : '↓'; ?> 
                        <?php echo number_format(abs($paid_starter_change_percent), 1); ?>% 
                        vs last month
                    </div>
                </div>
                
                <div class="keap-kpi-card fluentcart">
                    <div class="keap-kpi-label">FluentCart Orders</div>
                    <div class="keap-kpi-value"><?php echo number_format($fluentcart_current['orders']); ?> orders</div>
                    <div class="keap-kpi-value" style="font-size: 20px; margin-top: 5px;">$<?php echo number_format($fluentcart_current['revenue'], 2); ?></div>
                    <div class="keap-kpi-change <?php echo $fluentcart_orders_change >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $fluentcart_orders_change >= 0 ? '↑' : '↓'; ?> 
                        <?php echo number_format(abs($fluentcart_orders_change_percent), 1); ?>% 
                        vs last month
                    </div>
                </div>
                
                <?php
                // Display dashboard reports that have show_on_dashboard enabled (skip All Intensives Bundle - replaced by FluentCart card; replace COCKTAIL_INTENSIVE_2026 with Total Orders card)
                foreach ($dashboard_reports as $dashboard_report) {
                    if (isset($dashboard_report['name']) && $dashboard_report['name'] === 'All Intensives Bundle') {
                        continue;
                    }
                    if (isset($dashboard_report['name']) && $dashboard_report['name'] === 'COCKTAIL_INTENSIVE_2026') {
                        ?>
                        <div class="keap-kpi-card orders">
                            <div class="keap-kpi-label">Total Orders (All Sources)</div>
                            <div class="keap-kpi-value"><?php echo number_format($total_orders_combined_current); ?></div>
                            <div class="keap-kpi-change <?php echo $total_orders_combined_change >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $total_orders_combined_change >= 0 ? '↑' : '↓'; ?>
                                <?php echo number_format(abs($total_orders_combined_change_percent), 1); ?>%
                                vs last month
                            </div>
                        </div>
                        <?php
                        continue;
                    }
                    $dashboard_comparison = $this->reports->get_monthly_comparison($dashboard_report['id'], $current_year, $current_month);
                    $dashboard_current_orders = $dashboard_comparison['current_orders'];
                    $dashboard_current_revenue = $dashboard_comparison['current_revenue'];
                    $dashboard_prev_orders = $dashboard_comparison['previous_orders'];
                    $dashboard_prev_revenue = $dashboard_comparison['previous_revenue'];
                    
                    $dashboard_orders_change = $dashboard_current_orders - $dashboard_prev_orders;
                    $dashboard_orders_change_percent = $dashboard_prev_orders > 0 ? ($dashboard_orders_change / $dashboard_prev_orders) * 100 : ($dashboard_orders_change > 0 ? 100 : 0);
                    
                    $dashboard_revenue_change = $dashboard_current_revenue - $dashboard_prev_revenue;
                    $dashboard_revenue_change_percent = $dashboard_prev_revenue > 0 ? ($dashboard_revenue_change / $dashboard_prev_revenue) * 100 : ($dashboard_revenue_change > 0 ? 100 : 0);
                    
                    // Determine card type based on report type
                    $card_type = 'intensives';
                    $report_type = isset($dashboard_report['report_type']) ? $dashboard_report['report_type'] : '';
                    if ($report_type === 'count' || $report_type === 'paid_starter') {
                        $card_type = 'count';
                    }
                    ?>
                    <div class="keap-kpi-card <?php echo esc_attr($card_type); ?>">
                        <div class="keap-kpi-label"><?php echo esc_html($dashboard_report['name']); ?></div>
                        <?php if ($report_type === 'count_revenue' || $report_type === 'intensives'): ?>
                            <div class="keap-kpi-value"><?php echo number_format($dashboard_current_orders); ?> orders</div>
                            <div class="keap-kpi-value" style="font-size: 20px; margin-top: 5px;">$<?php echo number_format($dashboard_current_revenue, 2); ?></div>
                            <div class="keap-kpi-change <?php echo $dashboard_orders_change >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $dashboard_orders_change >= 0 ? '↑' : '↓'; ?> 
                                <?php echo number_format(abs($dashboard_orders_change_percent), 1); ?>% 
                                vs last month
                            </div>
                        <?php else: ?>
                            <div class="keap-kpi-value"><?php echo number_format($dashboard_current_orders); ?></div>
                            <div class="keap-kpi-change <?php echo $dashboard_orders_change >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $dashboard_orders_change >= 0 ? '↑' : '↓'; ?> 
                                <?php echo number_format(abs($dashboard_orders_change_percent), 1); ?>% 
                                vs last month
                            </div>
                        <?php endif; ?>
                    </div>
                <?php } ?>
                
                <div class="keap-kpi-card ytd">
                    <div class="keap-kpi-label">Year-to-Date Revenue</div>
                    <div class="keap-kpi-value">$<?php echo number_format($total_ytd_revenue_display, 2); ?></div>
                    <div class="keap-kpi-change">
                        <?php echo number_format($total_ytd_orders_display); ?> orders
                    </div>
                </div>
            </div>
            
            <!-- Revenue Trend Chart -->
            <div class="keap-dashboard-section">
                <h2>Revenue Trend</h2>
                
                <!-- Chart Filters -->
                <div style="margin-bottom: 20px; padding: 15px; background: #f6f7f7; border-radius: 4px;">
                    <form method="get" action="" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
                        <input type="hidden" name="page" value="keap-reports">
                        <?php if (isset($_GET['sort'])): ?>
                            <input type="hidden" name="sort" value="<?php echo esc_attr($_GET['sort']); ?>">
                            <input type="hidden" name="order" value="<?php echo esc_attr($_GET['order']); ?>">
                        <?php endif; ?>
                        
                        <label style="display: flex; align-items: center; gap: 5px;">
                            <strong>Period:</strong>
                            <select name="chart_period" onchange="this.form.submit()">
                                <option value="<?php echo date('Y'); ?>" <?php selected($chart_period, date('Y')); ?>><?php echo date('Y'); ?></option>
                                <option value="30days" <?php selected($chart_period, '30days'); ?>>Last 30 Days</option>
                                <option value="60days" <?php selected($chart_period, '60days'); ?>>Last 60 Days</option>
                                <option value="90days" <?php selected($chart_period, '90days'); ?>>Last 90 Days</option>
                                <option value="12months" <?php selected($chart_period, '12months'); ?>>Last 12 Months</option>
                                <option value="2025" <?php selected($chart_period, '2025'); ?>>2025</option>
                                <option value="2026" <?php selected($chart_period, '2026'); ?>>2026</option>
                                <option value="2027" <?php selected($chart_period, '2027'); ?>>2027</option>
                                <option value="2028" <?php selected($chart_period, '2028'); ?>>2028</option>
                            </select>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 5px;">
                            <strong>View:</strong>
                            <select name="chart_view" onchange="this.form.submit()">
                                <option value="monthly" <?php selected($chart_view, 'monthly'); ?>>Monthly</option>
                                <option value="quarterly" <?php selected($chart_view, 'quarterly'); ?>>Quarterly</option>
                            </select>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 5px;">
                            <strong>Compare:</strong>
                            <select name="chart_compare" onchange="this.form.submit()">
                                <option value="lastyear" <?php selected($chart_compare, 'lastyear'); ?>>Last Year</option>
                                <option value="none" <?php selected($chart_compare, 'none'); ?>>None</option>
                                <option value="quarter" <?php selected($chart_compare, 'quarter'); ?>>Same Quarter Last Year</option>
                                <option value="month" <?php selected($chart_compare, 'month'); ?>>Same Month Last Year</option>
                            </select>
                        </label>
                    </form>
                </div>
                
                <canvas id="revenueChart" style="max-height: 400px;"></canvas>
            </div>
            
            <!-- Starter Signups Section -->
            <div class="keap-dashboard-section" style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #ddd;">
                <h1 style="margin-bottom: 20px;">Starter Signups</h1>
                
                <!-- KPI Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="keap-kpi-card">
                        <div class="keap-kpi-label">Free Starter Signups (This Month)</div>
                        <div class="keap-kpi-value"><?php echo number_format($free_starter_current['count']); ?></div>
                        <div class="keap-kpi-change <?php echo $free_starter_change >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $free_starter_change >= 0 ? '↑' : '↓'; ?> 
                            <?php echo number_format(abs($free_starter_change_percent), 1); ?>% 
                            vs last month
                        </div>
                    </div>
                    
                    <div class="keap-kpi-card">
                        <div class="keap-kpi-label">Paid Starter Signups (This Month)</div>
                        <div class="keap-kpi-value"><?php echo number_format($paid_starter_current['count']); ?></div>
                        <?php $paid_starter_revenue_section = (isset($paid_starter_current['revenue']) && $paid_starter_current['revenue'] > 0) ? floatval($paid_starter_current['revenue']) : ($paid_starter_current['count'] * 7.0); ?>
                        <div class="keap-kpi-value" style="font-size: 20px; margin-top: 5px;">$<?php echo number_format($paid_starter_revenue_section, 2); ?></div>
                        <div class="keap-kpi-change <?php echo $paid_starter_change >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $paid_starter_change >= 0 ? '↑' : '↓'; ?> 
                            <?php echo number_format(abs($paid_starter_change_percent), 1); ?>% 
                            vs last month
                        </div>
                    </div>
                </div>
                
                <!-- Starter Signups Chart -->
                <h2>Starter Signups Trend</h2>
                
                <!-- Chart Filters -->
                <div style="margin-bottom: 20px; padding: 15px; background: #f6f7f7; border-radius: 4px;">
                    <form method="get" action="" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
                        <input type="hidden" name="page" value="keap-reports">
                        <?php if (isset($_GET['sort'])): ?>
                            <input type="hidden" name="sort" value="<?php echo esc_attr($_GET['sort']); ?>">
                            <input type="hidden" name="order" value="<?php echo esc_attr($_GET['order']); ?>">
                        <?php endif; ?>
                        <?php if (isset($_GET['chart_period'])): ?>
                            <input type="hidden" name="chart_period" value="<?php echo esc_attr($_GET['chart_period']); ?>">
                            <input type="hidden" name="chart_view" value="<?php echo esc_attr($_GET['chart_view']); ?>">
                            <input type="hidden" name="chart_compare" value="<?php echo esc_attr($_GET['chart_compare']); ?>">
                        <?php endif; ?>
                        <label style="display: flex; align-items: center; gap: 5px;">
                            <strong>Period:</strong>
                            <select name="starter_chart_period" onchange="this.form.submit()">
                                <option value="30days" <?php selected($starter_chart_period, '30days'); ?>>Last 30 Days</option>
                                <option value="60days" <?php selected($starter_chart_period, '60days'); ?>>Last 60 Days</option>
                                <option value="90days" <?php selected($starter_chart_period, '90days'); ?>>Last 90 Days</option>
                                <option value="12months" <?php selected($starter_chart_period, '12months'); ?>>Last 12 Months</option>
                                <option value="2025" <?php selected($starter_chart_period, '2025'); ?>>2025</option>
                                <option value="2026" <?php selected($starter_chart_period, '2026'); ?>>2026</option>
                                <option value="2027" <?php selected($starter_chart_period, '2027'); ?>>2027</option>
                                <option value="2028" <?php selected($starter_chart_period, '2028'); ?>>2028</option>
                            </select>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 5px;">
                            <strong>View:</strong>
                            <select name="starter_chart_view" onchange="this.form.submit()">
                                <option value="monthly" <?php selected($starter_chart_view, 'monthly'); ?>>Monthly</option>
                                <option value="quarterly" <?php selected($starter_chart_view, 'quarterly'); ?>>Quarterly</option>
                                <option value="yearly" <?php selected($starter_chart_view, 'yearly'); ?>>Yearly</option>
                            </select>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 5px;">
                            <strong>Compare:</strong>
                            <select name="starter_chart_compare" onchange="this.form.submit()">
                                <option value="none" <?php selected($starter_chart_compare, 'none'); ?>>None</option>
                                <option value="lastyear" <?php selected($starter_chart_compare, 'lastyear'); ?>>Last Year</option>
                                <option value="quarter" <?php selected($starter_chart_compare, 'quarter'); ?>>Same Quarter Last Year</option>
                                <option value="month" <?php selected($starter_chart_compare, 'month'); ?>>Same Month Last Year</option>
                            </select>
                        </label>
                    </form>
                </div>
                
                <!-- Chart Legend with Toggle -->
                <div style="margin-bottom: 15px; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                    <strong>Show/Hide:</strong>
                    <label style="margin-left: 15px; cursor: pointer;">
                        <input type="checkbox" id="toggle-free-starter" checked> Free Starter Signups
                    </label>
                    <label style="margin-left: 15px; cursor: pointer;">
                        <input type="checkbox" id="toggle-paid-starter" checked> Paid Starter Signups
                    </label>
                </div>
                
                <canvas id="starterSignupsChart" style="max-height: 400px;"></canvas>
            </div>
            
            <!-- Sales Performance Table -->
            <div class="keap-dashboard-section">
                <h2>Sales Performance by Report</h2>
                <?php
                // Get all reports data for display (exclude count and count_revenue - they're only for KPIs)
                $all_reports_data = $this->database->get_all_reports_latest_data();
                $display_reports_data = array_filter($all_reports_data, function($report) {
                    $type = isset($report['report_type']) ? $report['report_type'] : '';
                    // Map old types for backward compatibility
                    if ($type === 'paid_starter') $type = 'count';
                    if ($type === 'intensives') $type = 'count_revenue';
                    // Exclude count and count_revenue from Sales Performance table
                    return !in_array($type, array('count', 'count_revenue'));
                });
                
                // Process and prepare data with YTD calculations
                $processed_reports = array();
                global $wpdb;
                $data_table = $wpdb->prefix . 'keap_report_data';
                
                foreach ($display_reports_data as $report) {
                    $data_year = isset($report['data_year']) ? intval($report['data_year']) : $current_year;
                    $data_month = isset($report['data_month']) ? intval($report['data_month']) : $current_month;
                    
                    // Get the current month revenue
                    $comparison = $this->reports->get_monthly_comparison($report['id'], $data_year, $data_month);
                    $current_value = $comparison['current'];
                    
                    // Calculate YTD: Sum ALL revenue reports for the same year from January through the current month
                    // This sums across all report_ids for the year, not just this one report
                    // Exclude count and count_revenue reports - they're only for KPI/metrics, not revenue
                    $reports_table = $wpdb->prefix . 'keap_reports';
                    $ytd_result = $wpdb->get_var($wpdb->prepare(
                        "SELECT COALESCE(SUM(d.total_amt_sold), 0) 
                        FROM {$data_table} d
                        INNER JOIN {$reports_table} r ON d.report_id = r.id
                        WHERE d.`year` = %d AND d.`month` <= %d 
                        AND r.report_type NOT IN ('count', 'count_revenue', 'paid_starter', 'intensives')",
                        $data_year,
                        $data_month
                    ));
                    $ytd = floatval($ytd_result);
                    
                    $processed_reports[] = array(
                        'name' => $report['name'],
                        'revenue' => $current_value,
                        'ytd' => $ytd,
                        'fetched_at' => isset($report['fetched_at']) ? $report['fetched_at'] : null,
                        'data_year' => $data_year,
                        'data_month' => $data_month,
                        'sort_date' => mktime(0, 0, 0, $data_month, 1, $data_year) // For sorting by date
                    );
                }
                
                // Handle sorting
                $sort_by = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'date';
                $sort_order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'asc' : 'desc';
                
                usort($processed_reports, function($a, $b) use ($sort_by, $sort_order) {
                    if ($sort_by === 'revenue') {
                        $result = $a['revenue'] <=> $b['revenue'];
                    } elseif ($sort_by === 'name') {
                        $result = strcmp($a['name'], $b['name']);
                    } else { // date
                        $result = $a['sort_date'] <=> $b['sort_date'];
                    }
                    return $sort_order === 'asc' ? $result : -$result;
                });
                ?>
                <?php if (empty($processed_reports)): ?>
                    <p>No reports configured or no data available.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <a href="?page=keap-reports&sort=date&order=<?php echo $sort_by === 'date' && $sort_order === 'desc' ? 'asc' : 'desc'; ?>" style="text-decoration: none; color: inherit;">
                                        Report Name
                                        <?php if ($sort_by === 'date'): ?>
                                            <?php echo $sort_order === 'desc' ? '↓' : '↑'; ?>
                                        <?php else: ?>
                                            (by date)
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?page=keap-reports&sort=revenue&order=<?php echo $sort_by === 'revenue' && $sort_order === 'asc' ? 'desc' : 'asc'; ?>" style="text-decoration: none; color: inherit;">
                                        Revenue
                                        <?php if ($sort_by === 'revenue'): ?>
                                            <?php echo $sort_order === 'asc' ? '↑' : '↓'; ?>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>YTD</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($processed_reports as $report): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($report['name']); ?></strong></td>
                                    <td><strong>$<?php echo number_format($report['revenue'], 2); ?></strong></td>
                                    <td><strong>$<?php echo number_format($report['ytd'], 2); ?></strong></td>
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
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Auto-Fetch Status -->
            <div class="keap-dashboard-section">
                <h2>Automatic Data Fetching Status</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Auto-Fetch Enabled</th>
                        <td>
                            <?php if ($auto_fetch_enabled): ?>
                                <span class="keap-status-badge success">✓ Enabled</span>
                            <?php else: ?>
                                <span class="keap-status-badge error">✗ Disabled</span>
                                <p class="description">Enable auto-fetch in <a href="<?php echo admin_url('admin.php?page=keap-reports-settings'); ?>">Settings</a> to automatically fetch reports.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Saved Reports Fetch</th>
                        <td>
                            <?php if ($next_report_run): ?>
                                <span class="keap-status-badge success">✓ Scheduled</span>
                                <p class="description">Next run: <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_report_run); ?> 
                                (<?php echo human_time_diff($next_report_run, current_time('timestamp', true)); ?> from now)
                                <br><small style="color: #646970;">Timezone: <?php echo esc_html(wp_timezone_string()); ?></small></p>
                                <p style="margin-top: 10px;">
                                    <button type="button" class="button button-small trigger-cron-btn" data-hook="keap_reports_fetch_scheduled" data-name="Saved Reports Fetch">Run Now (Test)</button>
                                    <button type="button" class="button button-small reschedule-cron-btn" data-hook="keap_reports_fetch_scheduled" data-name="Saved Reports Fetch">Reschedule</button>
                                </p>
                            <?php else: ?>
                                <span class="keap-status-badge warning">Not Scheduled</span>
                                <p class="description">Visit <a href="<?php echo admin_url('admin.php?page=keap-reports-settings'); ?>">Settings</a> to enable.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Subscription Reports Fetch</th>
                        <td>
                            <?php if ($next_subscription_run): ?>
                                <span class="keap-status-badge success">✓ Scheduled</span>
                                <p class="description">Next run: <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_subscription_run); ?> 
                                (<?php echo human_time_diff($next_subscription_run, current_time('timestamp', true)); ?> from now)
                                <br><small style="color: #646970;">Timezone: <?php echo esc_html(wp_timezone_string()); ?></small></p>
                                <p style="margin-top: 10px;">
                                    <button type="button" class="button button-small trigger-cron-btn" data-hook="keap_reports_fetch_daily_subscriptions" data-name="Subscription Reports Fetch">Run Now (Test)</button>
                                    <button type="button" class="button button-small reschedule-cron-btn" data-hook="keap_reports_fetch_daily_subscriptions" data-name="Subscription Reports Fetch">Reschedule</button>
                                </p>
                            <?php else: ?>
                                <span class="keap-status-badge warning">Not Scheduled</span>
                                <p class="description">Visit <a href="<?php echo admin_url('admin.php?page=keap-reports-settings'); ?>">Settings</a> to enable.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <?php if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON): ?>
                <div style="background: #d1ecf1; border-left: 4px solid #0c5460; padding: 12px; margin-top: 20px;">
                    <strong>ℹ️ WP Engine Cron:</strong> The <code>DISABLE_WP_CRON</code> constant is set to <code>true</code>, which is normal for WP Engine hosting. 
                    WP Engine uses their "Better WP Cron" system to handle scheduled events, so your cron jobs will run automatically through their system.
                    <br><br>
                    <strong>Note:</strong> The "Paused" status shown in the WP-Cron plugin interface is just a UI state in that plugin - it doesn't affect whether your events actually run. 
                    Your events are properly scheduled (as shown by the "Next run" times above) and will execute via WP Engine's cron system.
                </div>
                <?php endif; ?>
                
                <h3 style="margin-top: 30px;">Recent Cron Execution Log</h3>
                <div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                    <?php
                    $cron_logs = $this->database->get_logs(50, null);
                    $cron_entries = array();
                    
                    // Filter for CRON entries - check both possible field names
                    foreach ($cron_logs as $log) {
                        $message = isset($log['message']) ? $log['message'] : (isset($log['log_message']) ? $log['log_message'] : '');
                        if (strpos($message, 'CRON:') === 0) {
                            $cron_entries[] = $log;
                        }
                    }
                    
                    if (empty($cron_entries)) {
                        echo '<p style="color: #666; margin: 0;">No cron executions logged yet.</p>';
                        echo '<p style="color: #999; font-size: 11px; margin-top: 5px;">Total logs in database: ' . count($cron_logs) . '</p>';
                    } else {
                        foreach (array_slice($cron_entries, 0, 10) as $log) {
                            $time = date('M j, Y g:i:s A', strtotime($log['created_at']));
                            $level = isset($log['log_level']) ? $log['log_level'] : (isset($log['level']) ? $log['level'] : 'info');
                            $message = isset($log['log_message']) ? $log['log_message'] : (isset($log['message']) ? $log['message'] : '');
                            
                            $level_color = '#666';
                            if ($level === 'error') {
                                $level_color = '#d63638';
                            } elseif ($level === 'warning') {
                                $level_color = '#dba617';
                            } elseif ($level === 'info') {
                                $level_color = '#00a32a';
                            }
                            echo '<div style="margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #ddd;">';
                            echo '<span style="color: #666;">[' . esc_html($time) . ']</span> ';
                            echo '<span style="color: ' . esc_attr($level_color) . '; font-weight: bold;">[' . strtoupper(esc_html($level)) . ']</span> ';
                            echo '<span>' . esc_html($message) . '</span>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Trigger cron manually
            $('.trigger-cron-btn').on('click', function() {
                var $btn = $(this);
                var hook = $btn.data('hook');
                var name = $btn.data('name');
                
                if (!confirm('Are you sure you want to manually trigger "' + name + '" now? This will run the cron job immediately.')) {
                    return;
                }
                
                $btn.prop('disabled', true).text('Running...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'keap_reports_trigger_cron',
                        hook: hook,
                        nonce: '<?php echo wp_create_nonce('keap_reports_trigger_cron'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Cron job triggered successfully! Check the log above for results.');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
                            $btn.prop('disabled', false).text('Run Now (Test)');
                        }
                    },
                    error: function() {
                        alert('Error triggering cron job. Please try again.');
                        $btn.prop('disabled', false).text('Run Now (Test)');
                    }
                });
            });
            
            // Reschedule cron
            $('.reschedule-cron-btn').on('click', function() {
                var $btn = $(this);
                var hook = $btn.data('hook');
                var name = $btn.data('name');
                
                if (!confirm('Are you sure you want to reschedule "' + name + '"? This will clear and recreate the scheduled event.')) {
                    return;
                }
                
                $btn.prop('disabled', true).text('Rescheduling...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'keap_reports_reschedule_cron',
                        hook: hook,
                        nonce: '<?php echo wp_create_nonce('keap_reports_reschedule_cron'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Cron job rescheduled successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
                            $btn.prop('disabled', false).text('Reschedule');
                        }
                    },
                    error: function() {
                        alert('Error rescheduling cron job. Please try again.');
                        $btn.prop('disabled', false).text('Reschedule');
                    }
                });
            });
        });
        </script>
        
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Revenue Trend Chart
            const ctx = document.getElementById('revenueChart');
            if (ctx) {
                try {
                    const chartData = <?php echo json_encode($revenue_history); ?>;
                    console.log('Chart data:', chartData);
                    
                    // Handle both old format (array) and new format (object with data key)
                    let revenueData, compareData, hasCompare;
                    
                    if (Array.isArray(chartData)) {
                        // Old format - direct array
                        revenueData = chartData;
                        compareData = [];
                        hasCompare = false;
                    } else if (chartData && chartData.data) {
                        // New format - object with data key
                        revenueData = chartData.data;
                        compareData = chartData.compare || [];
                        hasCompare = chartData.has_compare || false;
                    } else {
                        console.error('Invalid chart data format:', chartData);
                        revenueData = [];
                        compareData = [];
                        hasCompare = false;
                    }
                    
                    if (!revenueData || revenueData.length === 0) {
                        console.warn('No revenue data available');
                        ctx.parentElement.innerHTML += '<p style="color: #d63638; padding: 20px;">No data available for the selected period.</p>';
                        return;
                    }
                    
                    const datasets = [{
                        label: 'Revenue ($)',
                        data: revenueData.map(d => d.revenue || 0),
                        borderColor: '#00a32a',
                        backgroundColor: 'rgba(0, 163, 42, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'FluentCart ($)',
                        data: revenueData.map(d => (d.fluentcart_revenue !== undefined ? d.fluentcart_revenue : 0)),
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.1)',
                        tension: 0.4,
                        fill: false
                    }];
                    
                if (hasCompare && compareData.length > 0) {
                    let compareLabel = 'Previous Period ($)';
                    const chartCompare = '<?php echo esc_js($chart_compare); ?>';
                    if (chartCompare === 'lastyear') {
                        compareLabel = 'Last Year ($)';
                    } else if (chartCompare === 'quarter') {
                        compareLabel = 'Same Quarter Last Year ($)';
                    } else if (chartCompare === 'month') {
                        compareLabel = 'Same Month Last Year ($)';
                    }
                    
                    datasets.push({
                        label: compareLabel,
                        data: compareData.map(d => d.revenue || 0),
                        borderColor: '#646970',
                        backgroundColor: 'rgba(100, 105, 112, 0.1)',
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: false
                    });
                }
                    
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: revenueData.map(d => d.month || ''),
                            datasets: datasets
                        },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const dataIndex = context.dataIndex;
                                        const fmt = function(n) { return n.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };
                                        // Primary Revenue line: show Keap vs FluentCart breakdown if available
                                        if (context.dataset.label === 'Revenue ($)' && revenueData[dataIndex]) {
                                            const d = revenueData[dataIndex];
                                            if (d.keap_revenue !== undefined && d.fluentcart_revenue !== undefined) {
                                                const lines = [
                                                    'Keap: $' + fmt(d.keap_revenue) + ' (' + (d.keap_orders || 0).toLocaleString('en-US') + ' orders)',
                                                    'FluentCart: $' + fmt(d.fluentcart_revenue) + ' (' + (d.fluentcart_orders || 0).toLocaleString('en-US') + ' orders)',
                                                    'Total: $' + fmt(d.revenue || context.parsed.y) + ' (' + (d.orders || 0).toLocaleString('en-US') + ' orders)'
                                                ];
                                                if (hasCompare && compareData[dataIndex]) {
                                                    const curr = parseFloat(d.revenue) || 0;
                                                    const prev = parseFloat(compareData[dataIndex].revenue) || 0;
                                                    const diff = curr - prev;
                                                    let pct = '';
                                                    if (prev > 0) {
                                                        const change = (diff / prev) * 100;
                                                        pct = (diff >= 0 ? '↑ ' : '↓ ') + '$' + Math.abs(diff).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + Math.abs(change).toFixed(1) + '%)';
                                                    } else {
                                                        pct = curr > 0 ? '↑ $' + curr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (—)' : '—';
                                                    }
                                                    lines.push('vs last year: ' + pct);
                                                }
                                                return lines;
                                            }
                                            const label = context.dataset.label + ': $' + fmt(context.parsed.y);
                                            const fallbackLines = [label];
                                            if (d.orders !== undefined) fallbackLines.push('Orders: ' + d.orders.toLocaleString('en-US'));
                                            if (hasCompare && compareData[dataIndex]) {
                                                const curr = parseFloat(d.revenue) || context.parsed.y || 0;
                                                const prev = parseFloat(compareData[dataIndex].revenue) || 0;
                                                const diff = curr - prev;
                                                if (prev > 0) {
                                                    const change = (diff / prev) * 100;
                                                    fallbackLines.push('vs last year: ' + (diff >= 0 ? '↑ ' : '↓ ') + '$' + Math.abs(diff).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + Math.abs(change).toFixed(1) + '%)');
                                                } else if (curr > 0) fallbackLines.push('vs last year: ↑ $' + curr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (—)');
                                            }
                                            return fallbackLines.length > 1 ? fallbackLines : fallbackLines[0];
                                        }
                                        if (hasCompare && compareData.length > 0 && context.dataset.label !== 'Revenue ($)') {
                                            const label = context.dataset.label + ': $' + fmt(context.parsed.y);
                                            if (compareData[dataIndex] && compareData[dataIndex].orders !== undefined) {
                                                return [label, 'Orders: ' + compareData[dataIndex].orders.toLocaleString('en-US')];
                                            }
                                            return label;
                                        }
                                        return context.dataset.label + ': $' + fmt(context.parsed.y);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
                } catch (error) {
                    console.error('Error rendering chart:', error);
                    if (ctx && ctx.parentElement) {
                        ctx.parentElement.innerHTML += '<p style="color: #d63638; padding: 20px;">Error loading chart: ' + error.message + '</p>';
                    }
                }
            }
            
            // Starter Signups Chart
            const starterCtx = document.getElementById('starterSignupsChart');
            if (starterCtx) {
                try {
                    const starterChartData = <?php echo json_encode($starter_history); ?>;
                    console.log('Starter chart data:', starterChartData);
                    
                    let starterData, starterCompareData, starterHasCompare;
                    
                    if (starterChartData && starterChartData.data) {
                        starterData = starterChartData.data;
                        starterCompareData = starterChartData.compare || [];
                        starterHasCompare = starterChartData.has_compare || false;
                    } else {
                        console.error('Invalid starter chart data format:', starterChartData);
                        starterData = [];
                        starterCompareData = [];
                        starterHasCompare = false;
                    }
                    
                    if (!starterData || starterData.length === 0) {
                        console.warn('No starter signups data available');
                        starterCtx.parentElement.innerHTML += '<p style="color: #d63638; padding: 20px;">No data available for the selected period.</p>';
                        return;
                    }
                    
                    const starterDatasets = [
                        {
                            label: 'Free Starter Signups',
                            data: starterData.map(d => d.free_signups || 0),
                            borderColor: '#2271b1',
                            backgroundColor: 'rgba(34, 113, 177, 0.1)',
                            tension: 0.4,
                            fill: true,
                            hidden: false
                        },
                        {
                            label: 'Paid Starter Signups',
                            data: starterData.map(d => d.paid_signups || 0),
                            borderColor: '#00a32a',
                            backgroundColor: 'rgba(0, 163, 42, 0.1)',
                            tension: 0.4,
                            fill: true,
                            hidden: false
                        }
                    ];
                    
                    if (starterHasCompare && starterCompareData.length > 0) {
                        let compareLabel = 'Previous Period';
                        const starterChartCompare = '<?php echo esc_js($starter_chart_compare); ?>';
                        if (starterChartCompare === 'lastyear') {
                            compareLabel = 'Last Year';
                        } else if (starterChartCompare === 'quarter') {
                            compareLabel = 'Same Quarter Last Year';
                        } else if (starterChartCompare === 'month') {
                            compareLabel = 'Same Month Last Year';
                        }
                        
                        starterDatasets.push({
                            label: compareLabel + ' (Free)',
                            data: starterCompareData.map(d => d.free_signups || 0),
                            borderColor: '#2271b1',
                            backgroundColor: 'rgba(34, 113, 177, 0.05)',
                            borderDash: [5, 5],
                            tension: 0.4,
                            fill: false,
                            hidden: false
                        });
                        
                        starterDatasets.push({
                            label: compareLabel + ' (Paid)',
                            data: starterCompareData.map(d => d.paid_signups || 0),
                            borderColor: '#00a32a',
                            backgroundColor: 'rgba(0, 163, 42, 0.05)',
                            borderDash: [5, 5],
                            tension: 0.4,
                            fill: false,
                            hidden: false
                        });
                    }
                    
                    const starterChart = new Chart(starterCtx, {
                        type: 'line',
                        data: {
                            labels: starterData.map(d => d.month || ''),
                            datasets: starterDatasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const dataIndex = context.dataIndex;
                                            if (context.dataset.label === 'Paid Starter Signups' && starterData[dataIndex]) {
                                                const d = starterData[dataIndex];
                                                const rev = (d.paid_revenue !== undefined && d.paid_revenue > 0) ? d.paid_revenue : ((d.paid_signups || 0) * 7);
                                                return context.dataset.label + ': ' + context.parsed.y.toLocaleString('en-US') + ' ($' + rev.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')';
                                            }
                                            if (context.dataset.label.indexOf('(Paid)') !== -1 && starterCompareData[dataIndex]) {
                                                const d = starterCompareData[dataIndex];
                                                const rev = (d.paid_revenue !== undefined && d.paid_revenue > 0) ? d.paid_revenue : ((d.paid_signups || 0) * 7);
                                                return context.dataset.label + ': ' + context.parsed.y.toLocaleString('en-US') + ' ($' + rev.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')';
                                            }
                                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString('en-US');
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        callback: function(value) {
                                            return value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    // Toggle functionality
                    $('#toggle-free-starter').on('change', function() {
                        const isChecked = $(this).is(':checked');
                        starterChart.setDatasetVisibility(0, isChecked);
                        if (starterHasCompare && starterCompareData.length > 0) {
                            starterChart.setDatasetVisibility(2, isChecked);
                        }
                        starterChart.update();
                    });
                    
                    $('#toggle-paid-starter').on('change', function() {
                        const isChecked = $(this).is(':checked');
                        starterChart.setDatasetVisibility(1, isChecked);
                        if (starterHasCompare && starterCompareData.length > 0) {
                            starterChart.setDatasetVisibility(3, isChecked);
                        }
                        starterChart.update();
                    });
                } catch (error) {
                    console.error('Error rendering starter chart:', error);
                    if (starterCtx && starterCtx.parentElement) {
                        starterCtx.parentElement.innerHTML += '<p style="color: #d63638; padding: 20px;">Error loading chart: ' + error.message + '</p>';
                    }
                }
            }
        });
        </script>
        
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
     * Render subscriptions page (detailed view with charts)
     */
    public function render_subscriptions_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get filter parameters
        $product_id = isset($_GET['product_id']) ? sanitize_text_field($_GET['product_id']) : '';
        $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : '30'; // 7, 30, or 90 days
        
        // Calculate date range
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-{$period} days"));
        
        // Get all products for filter dropdown
        $products = $this->database->get_products();
        
        // Get latest total (sum of all products for today or most recent day)
        $latest_total = $this->database->get_total_subscriptions_count();
        
        // Get chart data via AJAX (will be loaded dynamically)
        
        ?>
        <div class="wrap">
            <h1>Subscriptions Analytics</h1>
            
            <!-- Manual Fetch Section -->
            <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <h2 style="margin-top: 0;">Daily Subscription Fetch</h2>
                <p>Manually fetch today's subscription snapshot. This will update or create today's data for all products.</p>
                <button type="button" id="fetch-daily-subscriptions-btn" class="button button-primary">Fetch Today's Subscriptions</button>
                <span id="fetch-status" style="margin-left: 10px;"></span>
            </div>
            
            <!-- Running Total Card -->
            <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; border-left: 4px solid #2271b1;">
                <h2 style="margin-top: 0;">Total Active Subscriptions</h2>
                <div style="font-size: 48px; font-weight: bold; color: #2271b1; margin: 10px 0;" id="total-subscriptions-count">
                    <?php echo number_format($latest_total); ?>
                </div>
                <p style="color: #666; margin: 0;">Across all products (as of latest snapshot)</p>
            </div>
            
            <!-- Filters -->
            <div class="keap-reports-filters" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" id="subscriptions-filter-form">
                    <input type="hidden" name="page" value="keap-reports-subscriptions">
                    <table class="form-table">
                        <tr>
                            <th><label for="product_id">Filter by Product/Membership</label></th>
                            <td>
                                <select name="product_id" id="product_id" style="width: 300px;">
                                    <option value="">All Products (Total)</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo esc_attr($product['product_id']); ?>" <?php selected($product_id, $product['product_id']); ?>>
                                            <?php echo esc_html($product['product_name']); ?> (ID: <?php echo esc_html($product['product_id']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="period">Time Period</label></th>
                            <td>
                                <select name="period" id="period">
                                    <option value="7" <?php selected($period, '7'); ?>>Last 7 Days</option>
                                    <option value="30" <?php selected($period, '30'); ?>>Last 30 Days</option>
                                    <option value="90" <?php selected($period, '90'); ?>>Last 90 Days</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="Update Chart">
                    </p>
                </form>
            </div>
            
            <!-- Chart Container -->
            <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <h2>Subscription Trends</h2>
                <div style="position: relative; height: 400px;">
                    <canvas id="subscriptions-chart"></canvas>
                </div>
            </div>
            
            <!-- Data Table -->
            <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <h2>Daily Subscription Data</h2>
                <div id="subscriptions-table-container">
                    <p>Loading data...</p>
                </div>
            </div>
            
            <!-- Duplicate Management -->
            <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <h2>Duplicate Management</h2>
                <p>Check for and clear duplicate entries for a specific date. Duplicates can occur if the fetch runs multiple times on the same day.</p>
                <table class="form-table">
                    <tr>
                        <th><label for="duplicate-date">Date to Check/Clear</label></th>
                        <td>
                            <input type="date" id="duplicate-date" value="<?php echo date('Y-m-d'); ?>" style="width: 200px;">
                            <button type="button" id="check-duplicates-btn" class="button">Check for Duplicates</button>
                            <button type="button" id="clear-duplicates-btn" class="button button-secondary" style="display: none;">Clear Duplicates for This Date</button>
                            <span id="duplicate-status" style="margin-left: 10px;"></span>
                        </td>
                    </tr>
                </table>
                <div id="duplicate-results" style="margin-top: 15px;"></div>
            </div>
        </div>
        
        <!-- Chart.js Library -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            let subscriptionsChart = null;
            
            // Load chart data
            function loadChartData() {
                const productId = $('#product_id').val();
                const period = $('#period').val();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'keap_reports_get_subscription_chart_data',
                        nonce: '<?php echo wp_create_nonce('keap_reports_get_subscription_chart_data'); ?>',
                        product_id: productId,
                        period: period
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            updateChart(response.data);
                            updateTable(response.data);
                            // Always update total - it's now always the overall total (not filtered)
                            // The AJAX handler now always returns the overall total regardless of filter
                            updateTotal(response.data.total);
                        } else {
                            $('#subscriptions-table-container').html('<p style="color: red;">Error loading data: ' + (response.data && response.data.message ? response.data.message : 'Unknown error') + '</p>');
                        }
                    },
                    error: function() {
                        $('#subscriptions-table-container').html('<p style="color: red;">Error loading chart data. Please try again.</p>');
                    }
                });
            }
            
            function updateChart(data) {
                const ctx = document.getElementById('subscriptions-chart').getContext('2d');
                
                // Destroy existing chart if it exists
                if (subscriptionsChart) {
                    subscriptionsChart.destroy();
                }
                
                subscriptionsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: data.product_name || 'Total Active Subscriptions',
                            data: data.values,
                            borderColor: 'rgb(34, 113, 177)',
                            backgroundColor: 'rgba(34, 113, 177, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            function updateTable(data) {
                if (!data.table_data || data.table_data.length === 0) {
                    $('#subscriptions-table-container').html('<p>No data available for the selected period.</p>');
                    return;
                }
                
                let html = '<table class="wp-list-table widefat fixed striped">';
                html += '<thead><tr>';
                html += '<th>Date</th>';
                html += '<th style="text-align: right;">Active Subscriptions</th>';
                html += '<th style="text-align: right;">Total Change</th>';
                html += '<th>Change Breakdown by Product</th>';
                html += '</tr></thead><tbody>';
                
                let previousValue = null;
                data.table_data.forEach(function(row) {
                    const change = previousValue !== null ? row.count - previousValue : null;
                    const changeClass = change !== null ? (change > 0 ? 'color: green; font-weight: bold;' : change < 0 ? 'color: red; font-weight: bold;' : '') : '';
                    const changeText = change !== null ? (change > 0 ? '+' + change : change) : '-';
                    
                    // Build product breakdown
                    let breakdownHtml = '';
                    if (row.product_changes && row.product_changes.length > 0) {
                        // Show top 5 increases and top 5 decreases
                        const increases = row.product_changes.filter(p => p.change > 0).slice(0, 5);
                        const decreases = row.product_changes.filter(p => p.change < 0).slice(0, 5);
                        
                        if (increases.length > 0 || decreases.length > 0) {
                            breakdownHtml += '<div style="font-size: 11px; line-height: 1.6;">';
                            
                            if (increases.length > 0) {
                                breakdownHtml += '<div style="margin-bottom: 4px;"><strong style="color: #00a32a;">↑ Increases:</strong></div>';
                                increases.forEach(function(p) {
                                    breakdownHtml += '<div style="color: #00a32a; margin-left: 10px; margin-bottom: 4px;">';
                                    breakdownHtml += '<span style="font-weight: bold;">+' + p.change + '</span> ';
                                    breakdownHtml += '<span style="color: #666;">' + p.product_name + '</span>';
                                    breakdownHtml += ' <span style="color: #999;">(' + p.previous + ' → ' + p.current + ')</span>';
                                    
                                    // Show new subscriptions (students who subscribed)
                                    if (p.new_subscriptions && p.new_subscriptions.length > 0) {
                                        breakdownHtml += '<div style="margin-left: 20px; margin-top: 2px; font-size: 10px;">';
                                        p.new_subscriptions.forEach(function(sub) {
                                            const name = sub.name || 'Unknown';
                                            const email = sub.email || '';
                                            breakdownHtml += '<div style="color: #666;">';
                                            breakdownHtml += '<span style="font-weight: 500;">' + name + '</span>';
                                            if (email) {
                                                breakdownHtml += ' <span style="color: #999;">(' + email + ')</span>';
                                            }
                                            breakdownHtml += '</div>';
                                        });
                                        breakdownHtml += '</div>';
                                    }
                                    
                                    breakdownHtml += '</div>';
                                });
                            }
                            
                            if (decreases.length > 0) {
                                breakdownHtml += '<div style="margin-top: 6px;"><strong style="color: #d63638;">↓ Decreases:</strong></div>';
                                decreases.forEach(function(p) {
                                    breakdownHtml += '<div style="color: #d63638; margin-left: 10px; margin-bottom: 4px;">';
                                    breakdownHtml += '<span style="font-weight: bold;">' + p.change + '</span> ';
                                    breakdownHtml += '<span style="color: #666;">' + p.product_name + '</span>';
                                    breakdownHtml += ' <span style="color: #999;">(' + p.previous + ' → ' + p.current + ')</span>';
                                    
                                    // Show cancelled subscriptions (students who cancelled)
                                    if (p.cancelled_subscriptions && p.cancelled_subscriptions.length > 0) {
                                        breakdownHtml += '<div style="margin-left: 20px; margin-top: 2px; font-size: 10px;">';
                                        p.cancelled_subscriptions.forEach(function(sub) {
                                            const name = sub.name || 'Unknown';
                                            const email = sub.email || '';
                                            breakdownHtml += '<div style="color: #666;">';
                                            breakdownHtml += '<span style="font-weight: 500;">' + name + '</span>';
                                            if (email) {
                                                breakdownHtml += ' <span style="color: #999;">(' + email + ')</span>';
                                            }
                                            breakdownHtml += '</div>';
                                        });
                                        breakdownHtml += '</div>';
                                    }
                                    
                                    breakdownHtml += '</div>';
                                });
                            }
                            
                            // Show count of additional changes if there are more
                            const totalChanges = row.product_changes.length;
                            const shownChanges = increases.length + decreases.length;
                            if (totalChanges > shownChanges) {
                                breakdownHtml += '<div style="margin-top: 4px; color: #666; font-style: italic;">';
                                breakdownHtml += '+ ' + (totalChanges - shownChanges) + ' more product change' + (totalChanges - shownChanges > 1 ? 's' : '');
                                breakdownHtml += '</div>';
                            }
                            
                            breakdownHtml += '</div>';
                        } else {
                            breakdownHtml = '<span style="color: #999;">No product changes</span>';
                        }
                    } else if (change === null) {
                        breakdownHtml = '<span style="color: #999;">First day (no comparison)</span>';
                    } else if (change === 0) {
                        breakdownHtml = '<span style="color: #999;">No change</span>';
                    } else {
                        breakdownHtml = '<span style="color: #999;">Product data not available</span>';
                    }
                    
                    html += '<tr>';
                    html += '<td><strong>' + row.date + '</strong></td>';
                    html += '<td style="text-align: right;"><strong>' + row.count.toLocaleString() + '</strong></td>';
                    html += '<td style="text-align: right; ' + changeClass + '">' + changeText + '</td>';
                    html += '<td style="max-width: 400px;">' + breakdownHtml + '</td>';
                    html += '</tr>';
                    
                    previousValue = row.count;
                });
                
                html += '</tbody></table>';
                $('#subscriptions-table-container').html(html);
            }
            
            function updateTotal(total) {
                $('#total-subscriptions-count').text(total.toLocaleString());
            }
            
            // Load data on page load
            loadChartData();
            
            // Reload on filter change
            $('#subscriptions-filter-form').on('submit', function(e) {
                e.preventDefault();
                loadChartData();
            });
            
            // Manual fetch button
            $('#fetch-daily-subscriptions-btn').on('click', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $status = $('#fetch-status');
                $btn.prop('disabled', true).text('Fetching...');
                $status.css('color', '#666').text('Fetching today\'s subscriptions... This may take a minute.');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    timeout: 300000, // 5 minutes
                    data: {
                        action: 'keap_reports_fetch_daily_subscriptions',
                        nonce: '<?php echo wp_create_nonce('keap_reports_fetch_daily_subscriptions'); ?>'
                    },
                    success: function(response) {
                        if (response && response.success) {
                            $status.css('color', 'green').text('✓ ' + (response.data && response.data.message ? response.data.message : 'Success'));
                            setTimeout(function() {
                                loadChartData(); // Reload chart data
                                updateTotal(response.data && response.data.total ? response.data.total : 0);
                            }, 1000);
                        } else {
                            var errorMsg = response && response.data && response.data.message ? response.data.message : 'Unknown error';
                            $status.css('color', 'red').text('✗ ' + errorMsg);
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMsg = '✗ Error fetching subscriptions';
                        if (status === 'timeout') {
                            errorMsg = '✗ Request timed out. The API call may be taking too long.';
                        } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            errorMsg += ': ' + xhr.responseJSON.data.message;
                        }
                        $status.css('color', 'red').text(errorMsg);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Fetch Today\'s Subscriptions');
                    }
                });
            });
            
            // Check for duplicates
            $('#check-duplicates-btn').on('click', function(e) {
                e.preventDefault();
                var date = $('#duplicate-date').val();
                if (!date) {
                    alert('Please select a date');
                    return;
                }
                
                var $btn = $(this);
                var $status = $('#duplicate-status');
                $btn.prop('disabled', true).text('Checking...');
                $status.text('');
                $('#duplicate-results').html('');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'keap_reports_check_duplicates',
                        nonce: '<?php echo wp_create_nonce('keap_reports_check_duplicates'); ?>',
                        date: date
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            var html = '<div style="margin-top: 10px;">';
                            if (response.data.has_duplicates) {
                                html += '<p style="color: #d63638;"><strong>Duplicates Found:</strong> ' + response.data.duplicate_count + ' duplicate entries for ' + date + '</p>';
                                html += '<p>Total unique product entries: ' + response.data.unique_count + '</p>';
                                html += '<p>Total duplicate entries: ' + response.data.duplicate_count + '</p>';
                                $('#clear-duplicates-btn').show();
                            } else {
                                html += '<p style="color: #00a32a;"><strong>No duplicates found</strong> for ' + date + '</p>';
                                html += '<p>Total entries: ' + response.data.unique_count + '</p>';
                                $('#clear-duplicates-btn').hide();
                            }
                            html += '</div>';
                            $('#duplicate-results').html(html);
                            $status.css('color', 'green').text('✓ Check complete');
                        } else {
                            $status.css('color', 'red').text('✗ Error: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
                        }
                    },
                    error: function() {
                        $status.css('color', 'red').text('✗ Error checking for duplicates');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Check for Duplicates');
                    }
                });
            });
            
            // Clear duplicates
            $('#clear-duplicates-btn').on('click', function(e) {
                e.preventDefault();
                var date = $('#duplicate-date').val();
                if (!date) {
                    alert('Please select a date');
                    return;
                }
                
                if (!confirm('Are you sure you want to clear duplicate entries for ' + date + '? This will keep only the most recent entry for each product.')) {
                    return;
                }
                
                var $btn = $(this);
                var $status = $('#duplicate-status');
                $btn.prop('disabled', true).text('Clearing...');
                $status.text('');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'keap_reports_clear_duplicates',
                        nonce: '<?php echo wp_create_nonce('keap_reports_clear_duplicates'); ?>',
                        date: date
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            $status.css('color', 'green').text('✓ ' + (response.data.message || 'Duplicates cleared successfully'));
                            $('#clear-duplicates-btn').hide();
                            $('#duplicate-results').html('<p style="color: #00a32a;">Duplicates cleared. ' + response.data.deleted_count + ' duplicate entries removed.</p>');
                            // Reload chart data
                            setTimeout(function() {
                                loadChartData();
                            }, 1000);
                        } else {
                            $status.css('color', 'red').text('✗ Error: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
                        }
                    },
                    error: function() {
                        $status.css('color', 'red').text('✗ Error clearing duplicates');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Clear Duplicates for This Date');
                    }
                });
            });
        });
        </script>
        <?php
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
            <h1>Products</h1>
            
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
     * AJAX handler for getting subscription chart data
     */
    /**
     * AJAX handler for manually triggering a cron job
     */
    public function ajax_trigger_cron() {
        check_ajax_referer('keap_reports_trigger_cron', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        $hook = isset($_POST['hook']) ? sanitize_text_field($_POST['hook']) : '';
        
        if (empty($hook)) {
            wp_send_json_error(array('message' => 'No hook specified'));
            return;
        }
        
        // Trigger the cron hook manually
        do_action($hook);
        
        $this->database->add_log(sprintf('CRON: Manually triggered %s', $hook), 'info');
        
        wp_send_json_success(array('message' => 'Cron job triggered successfully'));
    }
    
    /**
     * AJAX handler for rescheduling a cron job
     */
    public function ajax_reschedule_cron() {
        check_ajax_referer('keap_reports_reschedule_cron', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        $hook = isset($_POST['hook']) ? sanitize_text_field($_POST['hook']) : '';
        
        if (empty($hook)) {
            wp_send_json_error(array('message' => 'No hook specified'));
            return;
        }
        
        $cron = new Keap_Reports_Cron($this->reports);
        
        // Clear existing schedule
        wp_clear_scheduled_hook($hook);
        
        // Reschedule based on hook type
        if ($hook === 'keap_reports_fetch_scheduled') {
            $frequency = get_option('keap_reports_schedule_frequency', 'daily');
            $timezone = get_option('timezone_string');
            if (empty($timezone)) {
                $timezone = 'America/New_York';
            }
            
            $now = new DateTime('now', new DateTimeZone($timezone));
            $scheduled_time = clone $now;
            $scheduled_time->setTime(3, 0, 0);
            
            if ($now >= $scheduled_time) {
                $scheduled_time->modify('+1 day');
            }
            
            wp_schedule_event($scheduled_time->getTimestamp(), $frequency, $hook);
            $this->database->add_log(sprintf('CRON: Rescheduled %s for %s', $hook, $scheduled_time->format('Y-m-d H:i:s')), 'info');
        } elseif ($hook === 'keap_reports_fetch_daily_subscriptions') {
            $timezone = get_option('timezone_string');
            if (empty($timezone)) {
                $timezone = 'America/New_York';
            }
            
            $now = new DateTime('now', new DateTimeZone($timezone));
            $scheduled_time = clone $now;
            $scheduled_time->setTime(2, 0, 0);
            
            if ($now >= $scheduled_time) {
                $scheduled_time->modify('+1 day');
            }
            
            wp_schedule_event($scheduled_time->getTimestamp(), 'daily', $hook);
            $this->database->add_log(sprintf('CRON: Rescheduled %s for %s', $hook, $scheduled_time->format('Y-m-d H:i:s')), 'info');
        }
        
        wp_send_json_success(array('message' => 'Cron job rescheduled successfully'));
    }
    
    public function ajax_get_subscription_chart_data() {
        check_ajax_referer('keap_reports_get_subscription_chart_data', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        $product_id = isset($_POST['product_id']) ? sanitize_text_field($_POST['product_id']) : '';
        $period = isset($_POST['period']) ? absint($_POST['period']) : 30;
        
        // Calculate date range
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-{$period} days"));
        
        // Get subscription data
        $data = $this->database->get_daily_subscriptions_range($product_id, $start_date, $end_date);
        
        // Get product name if filtering by product
        $product_name = 'Total Active Subscriptions';
        if ($product_id) {
            $product = $this->database->get_product($product_id);
            if ($product) {
                $product_name = $product['product_name'] . ' Subscriptions';
            }
        }
        
        // Process data for chart
        $labels = array();
        $values = array();
        $table_data = array();
        
        // Group by date and product, storing product-level data
        $daily_data = array(); // date => array('total' => count, 'products' => array(product_id => count))
        foreach ($data as $row) {
            $date_key = sprintf('%04d-%02d-%02d', $row['year'], $row['month'], $row['day']);
            if (!isset($daily_data[$date_key])) {
                $daily_data[$date_key] = array(
                    'total' => 0,
                    'products' => array()
                );
            }
            $product_id = $row['product_id'];
            $count = intval($row['active_count']);
            $daily_data[$date_key]['total'] += $count;
            $daily_data[$date_key]['products'][$product_id] = $count;
        }
        
        // Sort by date
        ksort($daily_data);
        
        // Build chart data and calculate product-level changes
        $previous_day_products = array();
        foreach ($daily_data as $date => $day_info) {
            $labels[] = date('M j', strtotime($date));
            $values[] = $day_info['total'];
            
            // Calculate product-level changes
            $product_changes = array();
            if (!empty($previous_day_products)) {
                // Get date parts for current and previous day
                $date_parts = explode('-', $date);
                $current_year = intval($date_parts[0]);
                $current_month = intval($date_parts[1]);
                $current_day = intval($date_parts[2]);
                
                // Get previous day date
                $prev_date = date('Y-m-d', strtotime($date . ' -1 day'));
                $prev_parts = explode('-', $prev_date);
                $prev_year = intval($prev_parts[0]);
                $prev_month = intval($prev_parts[1]);
                $prev_day = intval($prev_parts[2]);
                
                // Compare with previous day
                foreach ($day_info['products'] as $prod_id => $current_count) {
                    $previous_count = isset($previous_day_products[$prod_id]) ? $previous_day_products[$prod_id] : 0;
                    $change = $current_count - $previous_count;
                    if ($change != 0) {
                        $product = $this->database->get_product($prod_id);
                        $current_product_name = $product ? $product['product_name'] : 'Product ' . $prod_id;
                        
                        // Get subscription details for current and previous day
                        $current_details = $this->database->get_subscription_details($prod_id, $current_year, $current_month, $current_day);
                        $previous_details = $this->database->get_subscription_details($prod_id, $prev_year, $prev_month, $prev_day);
                        
                        // Create maps of subscription IDs
                        $current_sub_ids = array();
                        $current_contacts = array();
                        foreach ($current_details as $detail) {
                            $sub_id = $detail['subscription_id'];
                            $current_sub_ids[$sub_id] = true;
                            $current_contacts[] = array(
                                'name' => $detail['contact_name'],
                                'email' => $detail['contact_email'],
                                'contact_id' => $detail['contact_id']
                            );
                        }
                        
                        $previous_sub_ids = array();
                        $previous_contacts = array();
                        foreach ($previous_details as $detail) {
                            $sub_id = $detail['subscription_id'];
                            $previous_sub_ids[$sub_id] = true;
                            $previous_contacts[] = array(
                                'name' => $detail['contact_name'],
                                'email' => $detail['contact_email'],
                                'contact_id' => $detail['contact_id']
                            );
                        }
                        
                        // Find new subscriptions (in current but not in previous)
                        $new_subscriptions = array();
                        foreach ($current_details as $detail) {
                            if (!isset($previous_sub_ids[$detail['subscription_id']])) {
                                $new_subscriptions[] = array(
                                    'name' => $detail['contact_name'],
                                    'email' => $detail['contact_email'],
                                    'contact_id' => $detail['contact_id']
                                );
                            }
                        }
                        
                        // Find cancelled subscriptions (in previous but not in current)
                        $cancelled_subscriptions = array();
                        foreach ($previous_details as $detail) {
                            if (!isset($current_sub_ids[$detail['subscription_id']])) {
                                $cancelled_subscriptions[] = array(
                                    'name' => $detail['contact_name'],
                                    'email' => $detail['contact_email'],
                                    'contact_id' => $detail['contact_id']
                                );
                            }
                        }
                        
                        $product_changes[] = array(
                            'product_id' => $prod_id,
                            'product_name' => $current_product_name,
                            'change' => $change,
                            'current' => $current_count,
                            'previous' => $previous_count,
                            'new_subscriptions' => $new_subscriptions,
                            'cancelled_subscriptions' => $cancelled_subscriptions
                        );
                    }
                }
                
                // Also check for products that existed yesterday but not today (decreased to 0)
                foreach ($previous_day_products as $prod_id => $previous_count) {
                    if (!isset($day_info['products'][$prod_id])) {
                        $product = $this->database->get_product($prod_id);
                        $current_product_name = $product ? $product['product_name'] : 'Product ' . $prod_id;
                        
                        // Get cancelled subscriptions
                        $previous_details = $this->database->get_subscription_details($prod_id, $prev_year, $prev_month, $prev_day);
                        $cancelled_subscriptions = array();
                        foreach ($previous_details as $detail) {
                            $cancelled_subscriptions[] = array(
                                'name' => $detail['contact_name'],
                                'email' => $detail['contact_email'],
                                'contact_id' => $detail['contact_id']
                            );
                        }
                        
                        $product_changes[] = array(
                            'product_id' => $prod_id,
                            'product_name' => $current_product_name,
                            'change' => -$previous_count,
                            'current' => 0,
                            'previous' => $previous_count,
                            'new_subscriptions' => array(),
                            'cancelled_subscriptions' => $cancelled_subscriptions
                        );
                    }
                }
            }
            
            // Sort by absolute change (biggest changes first)
            usort($product_changes, function($a, $b) {
                return abs($b['change']) - abs($a['change']);
            });
            
            $table_data[] = array(
                'date' => date('Y-m-d', strtotime($date)),
                'count' => $day_info['total'],
                'product_changes' => $product_changes
            );
            
            // Store current day's products for next iteration
            $previous_day_products = $day_info['products'];
        }
        
        // Get latest total - always get overall total (not filtered by product_id)
        // This ensures the total displayed is always the sum across all products
        $total = $this->database->get_total_subscriptions_count(null);
        
        // If filtering by a specific product, also get that product's total for reference
        $product_total = null;
        if ($product_id) {
            $product_total = $this->database->get_total_subscriptions_count($product_id);
        }
        
        wp_send_json_success(array(
            'labels' => $labels,
            'values' => $values,
            'table_data' => $table_data,
            'product_name' => $product_name,
            'total' => $total, // Always overall total
            'product_total' => $product_total, // Product-specific total if filtering
            'period' => $period,
            'product_id' => $product_id
        ));
    }
    
    /**
     * AJAX handler for checking duplicates
     */
    public function ajax_check_duplicates() {
        check_ajax_referer('keap_reports_check_duplicates', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        if (empty($date)) {
            wp_send_json_error(array('message' => 'Date is required'));
            return;
        }
        
        $date_parts = explode('-', $date);
        if (count($date_parts) !== 3) {
            wp_send_json_error(array('message' => 'Invalid date format'));
            return;
        }
        
        $year = intval($date_parts[0]);
        $month = intval($date_parts[1]);
        $day = intval($date_parts[2]);
        
        $result = $this->database->check_duplicate_subscriptions($year, $month, $day);
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX handler for clearing duplicates
     */
    public function ajax_clear_duplicates() {
        check_ajax_referer('keap_reports_clear_duplicates', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        if (empty($date)) {
            wp_send_json_error(array('message' => 'Date is required'));
            return;
        }
        
        $date_parts = explode('-', $date);
        if (count($date_parts) !== 3) {
            wp_send_json_error(array('message' => 'Invalid date format'));
            return;
        }
        
        $year = intval($date_parts[0]);
        $month = intval($date_parts[1]);
        $day = intval($date_parts[2]);
        
        $result = $this->database->clear_duplicate_subscriptions($year, $month, $day);
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => 'Duplicates cleared successfully',
                'deleted_count' => $result['deleted_count']
            ));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    /**
     * Handle bulk actions for reports
     */
    public function ajax_bulk_action() {
        check_ajax_referer('keap_reports_bulk_action', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        $bulk_action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
        $report_ids = isset($_POST['report_ids']) ? array_map('absint', $_POST['report_ids']) : array();
        
        if (empty($bulk_action)) {
            wp_send_json_error(array('message' => 'No action specified'));
            return;
        }
        
        if (empty($report_ids)) {
            wp_send_json_error(array('message' => 'No reports selected'));
            return;
        }
        
        $updated_count = 0;
        $is_active = ($bulk_action === 'activate') ? 1 : 0;
        
        foreach ($report_ids as $report_id) {
            $report = $this->database->get_report($report_id);
            if ($report) {
                $report['is_active'] = $is_active;
                // Ensure id is set for update
                if (!isset($report['id'])) {
                    $report['id'] = $report_id;
                }
                $result = $this->database->save_report($report);
                if ($result) {
                    $updated_count++;
                } else {
                    error_log('Keap Reports: Failed to update report ID ' . $report_id . '. Error: ' . (isset($GLOBALS['wpdb']) ? $GLOBALS['wpdb']->last_error : 'Unknown'));
                }
            } else {
                error_log('Keap Reports: Report ID ' . $report_id . ' not found');
            }
        }
        
        if ($updated_count > 0) {
            $action_text = $bulk_action === 'activate' ? 'enabled' : 'disabled';
            wp_send_json_success(array(
                'message' => sprintf('Successfully %s auto-fetch for %d report(s).', $action_text, $updated_count)
            ));
        } else {
            wp_send_json_error(array('message' => 'No reports were updated'));
        }
    }
    
    /**
     * Get revenue history data based on filters
     */
    private function get_revenue_history_data($reports, $period, $view, $compare) {
        $data = array();
        $compare_data = array();
        
        // Determine date range based on period
        $start_date = new DateTime();
        $end_date = new DateTime();
        
        if ($period === '30days') {
            $start_date->modify('-30 days');
        } elseif ($period === '60days') {
            $start_date->modify('-60 days');
        } elseif ($period === '90days') {
            $start_date->modify('-90 days');
        } elseif ($period === '12months') {
            $start_date->modify('-12 months');
        } elseif (in_array($period, array('2025', '2026', '2027', '2028'))) {
            $year = intval($period);
            $start_date = new DateTime("$year-01-01");
            $end_date = new DateTime("$year-12-31");
        }
        
        // Generate data points based on view type
        if ($view === 'quarterly') {
            // Quarterly view
            $current = clone $start_date;
            while ($current <= $end_date) {
                $quarter = ceil($current->format('n') / 3);
                $year = intval($current->format('Y'));
                $quarter_label = "Q$quarter $year";
                
                // Sum all months in this quarter
                $quarter_revenue = 0;
                $quarter_orders = 0;
                $quarter_fc_revenue = 0;
                $quarter_fc_orders = 0;
                for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                    foreach ($reports as $report) {
                        $month_data = $this->database->get_report_data($report['id'], $year, $m);
                        if ($month_data) {
                            $quarter_revenue += floatval($month_data['total_amt_sold']);
                            $quarter_orders += intval($month_data['num_orders']);
                        }
                    }
                    $fc = $this->database->get_fluentcart_month_totals($year, $m);
                    $quarter_fc_revenue += $fc['revenue'];
                    $quarter_fc_orders += $fc['orders'];
                }
                $data[] = array(
                    'month' => $quarter_label,
                    'revenue' => $quarter_revenue + $quarter_fc_revenue,
                    'orders' => $quarter_orders + $quarter_fc_orders,
                    'keap_revenue' => $quarter_revenue,
                    'keap_orders' => $quarter_orders,
                    'fluentcart_revenue' => $quarter_fc_revenue,
                    'fluentcart_orders' => $quarter_fc_orders,
                    'year' => $year,
                    'quarter' => $quarter
                );
                
                // Move to next quarter
                $current->modify('+3 months');
                $current->modify('first day of this month');
            }
        } else {
            // Monthly view
            $current = clone $start_date;
            $current->modify('first day of this month');
            $end_date->modify('last day of this month');
            
            while ($current <= $end_date) {
                $year = intval($current->format('Y'));
                $month = intval($current->format('n'));
                $month_name = $current->format('M Y');
                
                $month_revenue = 0;
                $month_orders = 0;
                foreach ($reports as $report) {
                    $month_data = $this->database->get_report_data($report['id'], $year, $month);
                    if ($month_data) {
                        $month_revenue += floatval($month_data['total_amt_sold']);
                        $month_orders += intval($month_data['num_orders']);
                    }
                }
                $fc = $this->database->get_fluentcart_month_totals($year, $month);
                $data[] = array(
                    'month' => $month_name,
                    'revenue' => $month_revenue + $fc['revenue'],
                    'orders' => $month_orders + $fc['orders'],
                    'keap_revenue' => $month_revenue,
                    'keap_orders' => $month_orders,
                    'fluentcart_revenue' => $fc['revenue'],
                    'fluentcart_orders' => $fc['orders'],
                    'year' => $year,
                    'month_num' => $month
                );
                
                $current->modify('+1 month');
            }
        }
        
        // Generate comparison data if needed
        if ($compare === 'lastyear') {
            // Compare entire period to same period last year
            $compare_start = clone $start_date;
            $compare_start->modify('-1 year');
            $compare_end = clone $end_date;
            $compare_end->modify('-1 year');
            
            $current = clone $compare_start;
            if ($view === 'quarterly') {
                while ($current <= $compare_end) {
                    $quarter = ceil($current->format('n') / 3);
                    $year = intval($current->format('Y'));
                    $quarter_label = "Q$quarter $year";
                    
                    $quarter_revenue = 0;
                    $quarter_orders = 0;
                    $quarter_fc_revenue = 0;
                    $quarter_fc_orders = 0;
                    for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                        foreach ($reports as $report) {
                            $month_data = $this->database->get_report_data($report['id'], $year, $m);
                            if ($month_data) {
                                $quarter_revenue += floatval($month_data['total_amt_sold']);
                                $quarter_orders += intval($month_data['num_orders']);
                            }
                        }
                        $fc = $this->database->get_fluentcart_month_totals($year, $m);
                        $quarter_fc_revenue += $fc['revenue'];
                        $quarter_fc_orders += $fc['orders'];
                    }
                    $compare_data[] = array(
                        'month' => $quarter_label,
                        'revenue' => $quarter_revenue + $quarter_fc_revenue,
                        'orders' => $quarter_orders + $quarter_fc_orders,
                        'keap_revenue' => $quarter_revenue,
                        'keap_orders' => $quarter_orders,
                        'fluentcart_revenue' => $quarter_fc_revenue,
                        'fluentcart_orders' => $quarter_fc_orders
                    );
                    
                    $current->modify('+3 months');
                    $current->modify('first day of this month');
                }
            } else {
                $current->modify('first day of this month');
                $compare_end->modify('last day of this month');
                
                while ($current <= $compare_end) {
                    $year = intval($current->format('Y'));
                    $month = intval($current->format('n'));
                    $month_name = $current->format('M Y');
                    
                    $month_revenue = 0;
                    $month_orders = 0;
                    foreach ($reports as $report) {
                        $month_data = $this->database->get_report_data($report['id'], $year, $month);
                        if ($month_data) {
                            $month_revenue += floatval($month_data['total_amt_sold']);
                            $month_orders += intval($month_data['num_orders']);
                        }
                    }
                    $fc = $this->database->get_fluentcart_month_totals($year, $month);
                    $compare_data[] = array(
                        'month' => $month_name,
                        'revenue' => $month_revenue + $fc['revenue'],
                        'orders' => $month_orders + $fc['orders'],
                        'keap_revenue' => $month_revenue,
                        'keap_orders' => $month_orders,
                        'fluentcart_revenue' => $fc['revenue'],
                        'fluentcart_orders' => $fc['orders']
                    );
                    
                    $current->modify('+1 month');
                }
            }
        } elseif ($compare === 'quarter') {
            // Compare each period to same quarter last year
            foreach ($data as $data_point) {
                $compare_year = $data_point['year'] - 1;
                $compare_revenue = 0;
                $compare_orders = 0;
                $compare_fc_revenue = 0;
                $compare_fc_orders = 0;
                
                if ($view === 'quarterly') {
                    $quarter = $data_point['quarter'];
                    for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                        foreach ($reports as $report) {
                            $month_data = $this->database->get_report_data($report['id'], $compare_year, $m);
                            if ($month_data) {
                                $compare_revenue += floatval($month_data['total_amt_sold']);
                                $compare_orders += intval($month_data['num_orders']);
                            }
                        }
                        $fc = $this->database->get_fluentcart_month_totals($compare_year, $m);
                        $compare_fc_revenue += $fc['revenue'];
                        $compare_fc_orders += $fc['orders'];
                    }
                } else {
                    $month = $data_point['month_num'];
                    foreach ($reports as $report) {
                        $month_data = $this->database->get_report_data($report['id'], $compare_year, $month);
                        if ($month_data) {
                            $compare_revenue += floatval($month_data['total_amt_sold']);
                            $compare_orders += intval($month_data['num_orders']);
                        }
                    }
                    $fc = $this->database->get_fluentcart_month_totals($compare_year, $month);
                    $compare_fc_revenue = $fc['revenue'];
                    $compare_fc_orders = $fc['orders'];
                }
                
                $compare_data[] = array(
                    'month' => $data_point['month'],
                    'revenue' => $compare_revenue + $compare_fc_revenue,
                    'orders' => $compare_orders + $compare_fc_orders
                );
            }
        } elseif ($compare === 'month') {
            // Compare each month to same month last year
            foreach ($data as $data_point) {
                $compare_year = $data_point['year'] - 1;
                $compare_revenue = 0;
                $compare_orders = 0;
                $compare_fc_revenue = 0;
                $compare_fc_orders = 0;
                
                if ($view === 'quarterly') {
                    $quarter = $data_point['quarter'];
                    for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                        foreach ($reports as $report) {
                            $month_data = $this->database->get_report_data($report['id'], $compare_year, $m);
                            if ($month_data) {
                                $compare_revenue += floatval($month_data['total_amt_sold']);
                                $compare_orders += intval($month_data['num_orders']);
                            }
                        }
                        $fc = $this->database->get_fluentcart_month_totals($compare_year, $m);
                        $compare_fc_revenue += $fc['revenue'];
                        $compare_fc_orders += $fc['orders'];
                    }
                } else {
                    $month = $data_point['month_num'];
                    foreach ($reports as $report) {
                        $month_data = $this->database->get_report_data($report['id'], $compare_year, $month);
                        if ($month_data) {
                            $compare_revenue += floatval($month_data['total_amt_sold']);
                            $compare_orders += intval($month_data['num_orders']);
                        }
                    }
                    $fc = $this->database->get_fluentcart_month_totals($compare_year, $month);
                    $compare_fc_revenue = $fc['revenue'];
                    $compare_fc_orders = $fc['orders'];
                }
                
                $compare_data[] = array(
                    'month' => $data_point['month'],
                    'revenue' => $compare_revenue + $compare_fc_revenue,
                    'orders' => $compare_orders + $compare_fc_orders
                );
            }
        }
        
        return array(
            'data' => $data,
            'compare' => $compare_data,
            'has_compare' => !empty($compare_data)
        );
    }
    
    /**
     * Get free trial signups history data for chart
     * 
     * @param string $period Period filter (30days, 60days, 90days, 12months, or year)
     * @param string $view View type (monthly, quarterly)
     * @param string $compare Comparison type (none, lastyear, quarter, month)
     * @return array Chart data with labels and values
     */
    private function get_trial_signups_history_data($period, $view, $compare) {
        $data = array();
        $compare_data = array();
        
        // Determine date range based on period
        $start_date = new DateTime();
        $end_date = new DateTime();
        
        if ($period === '30days') {
            $start_date->modify('-30 days');
        } elseif ($period === '60days') {
            $start_date->modify('-60 days');
        } elseif ($period === '90days') {
            $start_date->modify('-90 days');
        } elseif ($period === '12months') {
            $start_date->modify('-12 months');
        } elseif (in_array($period, array('2025', '2026', '2027', '2028'))) {
            $year = intval($period);
            $start_date = new DateTime("$year-01-01");
            $end_date = new DateTime("$year-12-31");
        }
        
        // Generate data points based on view type
        if ($view === 'quarterly') {
            // Quarterly view
            $current = clone $start_date;
            $current->modify('first day of this month');
            while ($current <= $end_date) {
                $quarter = ceil($current->format('n') / 3);
                $year = intval($current->format('Y'));
                $quarter_label = "Q$quarter $year";
                
                // Sum all months in this quarter
                $quarter_signups = 0;
                for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                    $month_data = $this->database->get_free_trial_signups(48, 'month', $year, $m);
                    $quarter_signups += $month_data['count'];
                }
                
                $data[] = array(
                    'month' => $quarter_label,
                    'signups' => $quarter_signups,
                    'year' => $year,
                    'quarter' => $quarter
                );
                
                // Move to next quarter
                $current->modify('+3 months');
                $current->modify('first day of this month');
            }
        } else {
            // Monthly view
            $current = clone $start_date;
            $current->modify('first day of this month');
            $end_date->modify('last day of this month');
            
            while ($current <= $end_date) {
                $year = intval($current->format('Y'));
                $month = intval($current->format('n'));
                $month_name = $current->format('M Y');
                
                $month_data = $this->database->get_free_trial_signups(48, 'month', $year, $month);
                $month_signups = $month_data['count'];
                
                $data[] = array(
                    'month' => $month_name,
                    'signups' => $month_signups,
                    'year' => $year,
                    'month_num' => $month
                );
                
                $current->modify('+1 month');
            }
        }
        
        // Generate comparison data if needed
        if ($compare === 'lastyear') {
            // Compare entire period to same period last year
            $compare_start = clone $start_date;
            $compare_start->modify('-1 year');
            $compare_end = clone $end_date;
            $compare_end->modify('-1 year');
            
            $current = clone $compare_start;
            if ($view === 'quarterly') {
                $current->modify('first day of this month');
                while ($current <= $compare_end) {
                    $quarter = ceil($current->format('n') / 3);
                    $year = intval($current->format('Y'));
                    $quarter_label = "Q$quarter $year";
                    
                    $quarter_signups = 0;
                    for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                        $month_data = $this->database->get_free_trial_signups(48, 'month', $year, $m);
                        $quarter_signups += $month_data['count'];
                    }
                    
                    $compare_data[] = array(
                        'month' => $quarter_label,
                        'signups' => $quarter_signups
                    );
                    
                    $current->modify('+3 months');
                    $current->modify('first day of this month');
                }
            } else {
                $current->modify('first day of this month');
                $compare_end->modify('last day of this month');
                
                while ($current <= $compare_end) {
                    $year = intval($current->format('Y'));
                    $month = intval($current->format('n'));
                    $month_name = $current->format('M Y');
                    
                    $month_data = $this->database->get_free_trial_signups(48, 'month', $year, $month);
                    $month_signups = $month_data['count'];
                    
                    $compare_data[] = array(
                        'month' => $month_name,
                        'signups' => $month_signups
                    );
                    
                    $current->modify('+1 month');
                }
            }
        } elseif ($compare === 'quarter') {
            // Compare each period to same quarter last year
            foreach ($data as $data_point) {
                $compare_year = $data_point['year'] - 1;
                $compare_signups = 0;
                
                if ($view === 'quarterly') {
                    // For quarterly view, compare to same quarter last year
                    $quarter = $data_point['quarter'];
                    for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                        $month_data = $this->database->get_free_trial_signups(48, 'month', $compare_year, $m);
                        $compare_signups += $month_data['count'];
                    }
                } else {
                    // For monthly view, compare to same month last year
                    $month = $data_point['month_num'];
                    $month_data = $this->database->get_free_trial_signups(48, 'month', $compare_year, $month);
                    $compare_signups = $month_data['count'];
                }
                
                $compare_data[] = array(
                    'month' => $data_point['month'],
                    'signups' => $compare_signups
                );
            }
        } elseif ($compare === 'month') {
            // Compare each month to same month last year
            foreach ($data as $data_point) {
                $compare_year = $data_point['year'] - 1;
                $compare_signups = 0;
                
                if ($view === 'quarterly') {
                    // For quarterly view, still compare quarter to same quarter last year
                    $quarter = $data_point['quarter'];
                    for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                        $month_data = $this->database->get_free_trial_signups(48, 'month', $compare_year, $m);
                        $compare_signups += $month_data['count'];
                    }
                } else {
                    // For monthly view, compare to same month last year
                    $month = $data_point['month_num'];
                    $month_data = $this->database->get_free_trial_signups(48, 'month', $compare_year, $month);
                    $compare_signups = $month_data['count'];
                }
                
                $compare_data[] = array(
                    'month' => $data_point['month'],
                    'signups' => $compare_signups
                );
            }
        }
        
        return array(
            'data' => $data,
            'compare' => $compare_data,
            'has_compare' => !empty($compare_data)
        );
    }
    
    /**
     * Get starter signups history data for chart (both free and paid)
     * 
     * @param string $period Period filter (30days, 60days, 90days, 12months, or year)
     * @param string $view View type (monthly, quarterly, yearly)
     * @param string $compare Comparison type (none, lastyear, quarter, month)
     * @return array Chart data with labels and values
     */
    private function get_starter_signups_history_data($period, $view, $compare) {
        $data = array();
        $compare_data = array();
        
        // Determine date range based on period
        $start_date = new DateTime();
        $end_date = new DateTime();
        
        if ($period === '30days') {
            $start_date->modify('-30 days');
        } elseif ($period === '60days') {
            $start_date->modify('-60 days');
        } elseif ($period === '90days') {
            $start_date->modify('-90 days');
        } elseif ($period === '12months') {
            $start_date->modify('-12 months');
        } elseif (in_array($period, array('2025', '2026', '2027', '2028'))) {
            $year = intval($period);
            $start_date = new DateTime("$year-01-01");
            $end_date = new DateTime("$year-12-31");
        }
        
        // Generate data points based on view type
        if ($view === 'yearly') {
            // Yearly view
            $current = clone $start_date;
            $current->modify('first day of January');
            $end_date->modify('last day of December');
            
            while ($current <= $end_date) {
                $year = intval($current->format('Y'));
                $year_label = $year;
                
                // Sum all months in this year
                $year_free = 0;
                $year_paid = 0;
                $year_paid_revenue = 0.0;
                for ($m = 1; $m <= 12; $m++) {
                    $free_data = $this->database->get_free_trial_signups(48, 'month', $year, $m);
                    $paid_data = $this->database->get_starter_signups('paid_starter', $year, $m);
                    $year_free += $free_data['count'];
                    $year_paid += $paid_data['count'];
                    $year_paid_revenue += isset($paid_data['revenue']) ? floatval($paid_data['revenue']) : ($paid_data['count'] * 7.0);
                }
                
                $data[] = array(
                    'month' => $year_label,
                    'free_signups' => $year_free,
                    'paid_signups' => $year_paid,
                    'paid_revenue' => $year_paid_revenue,
                    'year' => $year
                );
                
                $current->modify('+1 year');
            }
        } elseif ($view === 'quarterly') {
            // Quarterly view
            $current = clone $start_date;
            $current->modify('first day of this month');
            while ($current <= $end_date) {
                $quarter = ceil($current->format('n') / 3);
                $year = intval($current->format('Y'));
                $quarter_label = "Q$quarter $year";
                
                // Sum all months in this quarter
                $quarter_free = 0;
                $quarter_paid = 0;
                $quarter_paid_revenue = 0.0;
                for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                    $free_data = $this->database->get_free_trial_signups(48, 'month', $year, $m);
                    $paid_data = $this->database->get_starter_signups('paid_starter', $year, $m);
                    $quarter_free += $free_data['count'];
                    $quarter_paid += $paid_data['count'];
                    $quarter_paid_revenue += isset($paid_data['revenue']) ? floatval($paid_data['revenue']) : ($paid_data['count'] * 7.0);
                }
                
                $data[] = array(
                    'month' => $quarter_label,
                    'free_signups' => $quarter_free,
                    'paid_signups' => $quarter_paid,
                    'paid_revenue' => $quarter_paid_revenue,
                    'year' => $year,
                    'quarter' => $quarter
                );
                
                // Move to next quarter
                $current->modify('+3 months');
                $current->modify('first day of this month');
            }
        } else {
            // Monthly view
            $current = clone $start_date;
            $current->modify('first day of this month');
            $end_date->modify('last day of this month');
            
            while ($current <= $end_date) {
                $year = intval($current->format('Y'));
                $month = intval($current->format('n'));
                $month_name = $current->format('M Y');
                
                $free_data = $this->database->get_free_trial_signups(48, 'month', $year, $month);
                $paid_data = $this->database->get_starter_signups('paid_starter', $year, $month);
                $paid_revenue = isset($paid_data['revenue']) && $paid_data['revenue'] > 0 ? floatval($paid_data['revenue']) : ($paid_data['count'] * 7.0);
                
                $data[] = array(
                    'month' => $month_name,
                    'free_signups' => $free_data['count'],
                    'paid_signups' => $paid_data['count'],
                    'paid_revenue' => $paid_revenue,
                    'year' => $year,
                    'month_num' => $month
                );
                
                $current->modify('+1 month');
            }
        }
        
        // Generate comparison data if needed
        if ($compare === 'lastyear') {
            // Compare entire period to same period last year
            $compare_start = clone $start_date;
            $compare_start->modify('-1 year');
            $compare_end = clone $end_date;
            $compare_end->modify('-1 year');
            
            $current = clone $compare_start;
            if ($view === 'yearly') {
                $current->modify('first day of January');
                $compare_end->modify('last day of December');
                
                while ($current <= $compare_end) {
                    $year = intval($current->format('Y'));
                    $year_label = $year;
                    
                    $year_free = 0;
                    $year_paid = 0;
                    for ($m = 1; $m <= 12; $m++) {
                        $free_data = $this->database->get_free_trial_signups(48, 'month', $year, $m);
                        $paid_data = $this->database->get_starter_signups('paid_starter', $year, $m);
                        $year_free += $free_data['count'];
                        $year_paid += $paid_data['count'];
                    }
                    
                    $compare_data[] = array(
                        'month' => $year_label,
                        'free_signups' => $year_free,
                        'paid_signups' => $year_paid
                    );
                    
                    $current->modify('+1 year');
                }
            } elseif ($view === 'quarterly') {
                $current->modify('first day of this month');
                while ($current <= $compare_end) {
                    $quarter = ceil($current->format('n') / 3);
                    $year = intval($current->format('Y'));
                    $quarter_label = "Q$quarter $year";
                    
                    $quarter_free = 0;
                    $quarter_paid = 0;
                    for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                        $free_data = $this->database->get_free_trial_signups(48, 'month', $year, $m);
                        $paid_data = $this->database->get_starter_signups('paid_starter', $year, $m);
                        $quarter_free += $free_data['count'];
                        $quarter_paid += $paid_data['count'];
                    }
                    
                    $compare_data[] = array(
                        'month' => $quarter_label,
                        'free_signups' => $quarter_free,
                        'paid_signups' => $quarter_paid
                    );
                    
                    $current->modify('+3 months');
                    $current->modify('first day of this month');
                }
            } else {
                $current->modify('first day of this month');
                $compare_end->modify('last day of this month');
                
                while ($current <= $compare_end) {
                    $year = intval($current->format('Y'));
                    $month = intval($current->format('n'));
                    $month_name = $current->format('M Y');
                    
                    $free_data = $this->database->get_free_trial_signups(48, 'month', $year, $month);
                    $paid_data = $this->database->get_starter_signups('paid_starter', $year, $month);
                    
                    $compare_data[] = array(
                        'month' => $month_name,
                        'free_signups' => $free_data['count'],
                        'paid_signups' => $paid_data['count']
                    );
                    
                    $current->modify('+1 month');
                }
            }
        } elseif ($compare === 'quarter') {
            // Compare each period to same quarter last year
            foreach ($data as $data_point) {
                $compare_year = $data_point['year'] - 1;
                $compare_free = 0;
                $compare_paid = 0;
                
                if ($view === 'yearly') {
                    for ($m = 1; $m <= 12; $m++) {
                        $free_data = $this->database->get_free_trial_signups(48, 'month', $compare_year, $m);
                        $paid_data = $this->database->get_starter_signups('paid_starter', $compare_year, $m);
                        $compare_free += $free_data['count'];
                        $compare_paid += $paid_data['count'];
                    }
                } elseif ($view === 'quarterly') {
                    $quarter = $data_point['quarter'];
                    for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                        $free_data = $this->database->get_free_trial_signups(48, 'month', $compare_year, $m);
                        $paid_data = $this->database->get_starter_signups('paid_starter', $compare_year, $m);
                        $compare_free += $free_data['count'];
                        $compare_paid += $paid_data['count'];
                    }
                } else {
                    $month = $data_point['month_num'];
                    $free_data = $this->database->get_free_trial_signups(48, 'month', $compare_year, $month);
                    $paid_data = $this->database->get_starter_signups('paid_starter', $compare_year, $month);
                    $compare_free = $free_data['count'];
                    $compare_paid = $paid_data['count'];
                }
                
                $compare_data[] = array(
                    'month' => $data_point['month'],
                    'free_signups' => $compare_free,
                    'paid_signups' => $compare_paid
                );
            }
        } elseif ($compare === 'month') {
            // Compare each month to same month last year
            foreach ($data as $data_point) {
                $compare_year = $data_point['year'] - 1;
                $compare_free = 0;
                $compare_paid = 0;
                
                if ($view === 'yearly') {
                    for ($m = 1; $m <= 12; $m++) {
                        $free_data = $this->database->get_free_trial_signups(48, 'month', $compare_year, $m);
                        $paid_data = $this->database->get_starter_signups('paid_starter', $compare_year, $m);
                        $compare_free += $free_data['count'];
                        $compare_paid += $paid_data['count'];
                    }
                } elseif ($view === 'quarterly') {
                    $quarter = $data_point['quarter'];
                    for ($m = ($quarter - 1) * 3 + 1; $m <= $quarter * 3; $m++) {
                        $free_data = $this->database->get_free_trial_signups(48, 'month', $compare_year, $m);
                        $paid_data = $this->database->get_starter_signups('paid_starter', $compare_year, $m);
                        $compare_free += $free_data['count'];
                        $compare_paid += $paid_data['count'];
                    }
                } else {
                    $month = $data_point['month_num'];
                    $free_data = $this->database->get_free_trial_signups(48, 'month', $compare_year, $month);
                    $paid_data = $this->database->get_starter_signups('paid_starter', $compare_year, $month);
                    $compare_free = $free_data['count'];
                    $compare_paid = $paid_data['count'];
                }
                
                $compare_data[] = array(
                    'month' => $data_point['month'],
                    'free_signups' => $compare_free,
                    'paid_signups' => $compare_paid
                );
            }
        }
        
        return array(
            'data' => $data,
            'compare' => $compare_data,
            'has_compare' => !empty($compare_data)
        );
    }
    
    /**
     * Get individual report history data based on filters
     * Similar to get_revenue_history_data but for a single report
     */
    private function get_individual_report_history_data($reports, $period, $view, $compare) {
        // Use the same logic as get_revenue_history_data but for a single report
        return $this->get_revenue_history_data($reports, $period, $view, $compare);
    }
}

