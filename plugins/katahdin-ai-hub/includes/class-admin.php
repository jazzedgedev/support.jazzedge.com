<?php
/**
 * Admin Interface for Katahdin AI Hub
 * Provides WordPress admin interface for managing the hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Hub_Admin {
    
    /**
     * Initialize Admin Interface
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_katahdin_ai_hub_test_api', array($this, 'ajax_test_api'));
        add_action('wp_ajax_katahdin_ai_hub_reset_quota', array($this, 'ajax_reset_quota'));
        add_action('wp_ajax_katahdin_ai_hub_export_data', array($this, 'ajax_export_data'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Katahdin AI Hub', 'katahdin-ai-hub'),
            __('Katahdin AI Hub', 'katahdin-ai-hub'),
            'manage_options',
            'katahdin-ai-hub',
            array($this, 'admin_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20h18l-9-16-9 16z"/><path d="M8 12l2 2 4-4"/><circle cx="18" cy="6" r="1" fill="currentColor"/><circle cx="20" cy="4" r="0.5" fill="currentColor"/><circle cx="16" cy="8" r="0.5" fill="currentColor"/></svg>'),
            30
        );
        
        add_submenu_page(
            'katahdin-ai-hub',
            __('Dashboard', 'katahdin-ai-hub'),
            __('Dashboard', 'katahdin-ai-hub'),
            'manage_options',
            'katahdin-ai-hub',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'katahdin-ai-hub',
            __('Usage Analytics', 'katahdin-ai-hub'),
            __('Usage Analytics', 'katahdin-ai-hub'),
            'manage_options',
            'katahdin-ai-hub-analytics',
            array($this, 'analytics_page')
        );
        
        add_submenu_page(
            'katahdin-ai-hub',
            __('Plugin Registry', 'katahdin-ai-hub'),
            __('Plugin Registry', 'katahdin-ai-hub'),
            'manage_options',
            'katahdin-ai-hub-plugins',
            array($this, 'plugins_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_openai_key', array(
            'sanitize_callback' => array($this, 'sanitize_api_key')
        ));
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_debug_mode');
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_usage_limit');
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_rate_limit');
    }
    
    /**
     * Sanitize API key
     */
    public function sanitize_api_key($api_key) {
        if (empty($api_key)) {
            return '';
        }
        
        // Store the API key directly in the option
        // The API manager will handle it when needed
        return $api_key;
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        $hub = katahdin_ai_hub();
        $global_stats = $hub->usage_tracker->get_global_stats(30);
        $quota_usage = $hub->usage_tracker->get_quota_usage();
        $registered_plugins = $hub->plugin_registry->get_all_plugins();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Katahdin AI Hub Dashboard', 'katahdin-ai-hub'); ?></h1>
            
            <!-- Settings Form -->
            <div class="katahdin-card">
                <h2><?php _e('API Settings', 'katahdin-ai-hub'); ?></h2>
                <form method="post" action="options.php" id="katahdin-settings-form">
                    <?php
                    settings_fields('katahdin_ai_hub_settings');
                    do_settings_sections('katahdin_ai_hub_settings');
                    ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="katahdin_ai_hub_openai_key"><?php _e('OpenAI API Key', 'katahdin-ai-hub'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="katahdin_ai_hub_openai_key" name="katahdin_ai_hub_openai_key" 
                                       value="<?php echo esc_attr(get_option('katahdin_ai_hub_openai_key')); ?>" 
                                       class="regular-text" />
                                <p class="description">
                                    <?php _e('Get your API key from', 'katahdin-ai-hub'); ?> 
                                    <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="katahdin_ai_hub_usage_limit"><?php _e('Global Usage Limit', 'katahdin-ai-hub'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="katahdin_ai_hub_usage_limit" name="katahdin_ai_hub_usage_limit" 
                                       value="<?php echo esc_attr(get_option('katahdin_ai_hub_usage_limit', 10000)); ?>" 
                                       class="small-text" min="0" />
                                <p class="description">
                                    <?php _e('Maximum tokens per month across all plugins', 'katahdin-ai-hub'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="katahdin_ai_hub_rate_limit"><?php _e('Rate Limit', 'katahdin-ai-hub'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="katahdin_ai_hub_rate_limit" name="katahdin_ai_hub_rate_limit" 
                                       value="<?php echo esc_attr(get_option('katahdin_ai_hub_rate_limit', 60)); ?>" 
                                       class="small-text" min="1" />
                                <p class="description">
                                    <?php _e('Maximum requests per minute', 'katahdin-ai-hub'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="katahdin_ai_hub_debug_mode"><?php _e('Debug Mode', 'katahdin-ai-hub'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="katahdin_ai_hub_debug_mode" name="katahdin_ai_hub_debug_mode" 
                                       value="1" <?php checked(get_option('katahdin_ai_hub_debug_mode'), 1); ?> />
                                <p class="description">
                                    <?php _e('Enable detailed logging for debugging', 'katahdin-ai-hub'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(__('Save Settings', 'katahdin-ai-hub')); ?>
                </form>
            </div>
            
            <div class="katahdin-ai-hub-dashboard">
                <!-- API Status -->
                <div class="katahdin-card">
                    <h2><?php _e('API Status', 'katahdin-ai-hub'); ?></h2>
                    <div class="api-status">
                        <button id="test-api-connection" class="button button-primary">
                            <?php _e('Test API Connection', 'katahdin-ai-hub'); ?>
                        </button>
                        <div id="api-test-result" class="api-test-result"></div>
                    </div>
                </div>
                
                <!-- Global Statistics -->
                <div class="katahdin-card">
                    <h2><?php _e('Global Statistics (Last 30 Days)', 'katahdin-ai-hub'); ?></h2>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo number_format($global_stats['overall']->total_requests ?? 0); ?></span>
                            <span class="stat-label"><?php _e('Total Requests', 'katahdin-ai-hub'); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo number_format($global_stats['overall']->total_tokens ?? 0); ?></span>
                            <span class="stat-label"><?php _e('Total Tokens', 'katahdin-ai-hub'); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">$<?php echo number_format($global_stats['overall']->total_cost ?? 0, 4); ?></span>
                            <span class="stat-label"><?php _e('Total Cost', 'katahdin-ai-hub'); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo round($global_stats['overall']->avg_response_time ?? 0); ?>ms</span>
                            <span class="stat-label"><?php _e('Avg Response Time', 'katahdin-ai-hub'); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Registered Plugins -->
                <div class="katahdin-card">
                    <h2><?php _e('Registered Plugins', 'katahdin-ai-hub'); ?></h2>
                    <div class="plugins-list">
                        <?php if (empty($registered_plugins)): ?>
                            <p><?php _e('No plugins registered yet.', 'katahdin-ai-hub'); ?></p>
                        <?php else: ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th><?php _e('Plugin', 'katahdin-ai-hub'); ?></th>
                                        <th><?php _e('Version', 'katahdin-ai-hub'); ?></th>
                                        <th><?php _e('Quota Usage', 'katahdin-ai-hub'); ?></th>
                                        <th><?php _e('Status', 'katahdin-ai-hub'); ?></th>
                                        <th><?php _e('Last Used', 'katahdin-ai-hub'); ?></th>
                                        <th><?php _e('Actions', 'katahdin-ai-hub'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registered_plugins as $plugin): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo esc_html($plugin['plugin_name']); ?></strong>
                                                <br>
                                                <small><?php echo esc_html($plugin['plugin_id']); ?></small>
                                            </td>
                                            <td><?php echo esc_html($plugin['version']); ?></td>
                                            <td>
                                                <div class="quota-bar">
                                                    <div class="quota-progress" style="width: <?php echo $plugin['quota_limit'] > 0 ? round(($plugin['quota_used'] / $plugin['quota_limit']) * 100) : 0; ?>%"></div>
                                                    <span class="quota-text">
                                                        <?php echo number_format($plugin['quota_used']); ?> / <?php echo number_format($plugin['quota_limit']); ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $plugin['is_active'] ? 'active' : 'inactive'; ?>">
                                                    <?php echo $plugin['is_active'] ? __('Active', 'katahdin-ai-hub') : __('Inactive', 'katahdin-ai-hub'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $plugin['last_used'] ? human_time_diff(strtotime($plugin['last_used'])) . ' ago' : __('Never', 'katahdin-ai-hub'); ?>
                                            </td>
                                            <td>
                                                <button class="button button-small reset-quota" data-plugin-id="<?php echo esc_attr($plugin['plugin_id']); ?>">
                                                    <?php _e('Reset Quota', 'katahdin-ai-hub'); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .katahdin-ai-hub-dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .katahdin-card {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .katahdin-card h2 {
            margin-top: 0;
            color: #1d2327;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f6f7f7;
            border-radius: 4px;
        }
        
        .stat-value {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .stat-label {
            font-size: 12px;
            color: #646970;
            text-transform: uppercase;
        }
        
        .quota-bar {
            position: relative;
            background: #f0f0f1;
            border-radius: 3px;
            height: 20px;
            overflow: hidden;
        }
        
        .quota-progress {
            height: 100%;
            background: linear-gradient(90deg, #00a32a, #ffb900, #d63638);
            transition: width 0.3s ease;
        }
        
        .quota-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 11px;
            font-weight: bold;
            color: #1d2327;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #842029;
        }
        
        .api-test-result {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        
        .api-test-result.success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        
        .api-test-result.error {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Test API connection
            $('#test-api-connection').on('click', function() {
                var button = $(this);
                var result = $('#api-test-result');
                
                button.prop('disabled', true).text('Testing...');
                result.hide();
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_hub_test_api',
                    nonce: katahdin_ai_hub.nonce
                }, function(response) {
                    if (response.success) {
                        result.removeClass('error').addClass('success')
                            .html('<strong>Success:</strong> ' + response.data.message)
                            .show();
                    } else {
                        result.removeClass('success').addClass('error')
                            .html('<strong>Error:</strong> ' + response.data)
                            .show();
                    }
                }).always(function() {
                    button.prop('disabled', false).text('Test API Connection');
                });
            });
            
            // Reset quota
            $('.reset-quota').on('click', function() {
                var button = $(this);
                var pluginId = button.data('plugin-id');
                
                if (!confirm('Are you sure you want to reset the quota for this plugin?')) {
                    return;
                }
                
                button.prop('disabled', true).text('Resetting...');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_hub_reset_quota',
                    plugin_id: pluginId,
                    nonce: katahdin_ai_hub.nonce
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                        button.prop('disabled', false).text('Reset Quota');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Analytics page
     */
    public function analytics_page() {
        $hub = katahdin_ai_hub();
        $global_stats = $hub->usage_tracker->get_global_stats(30);
        $cost_analysis = $hub->usage_tracker->get_cost_analysis(30);
        $performance_metrics = $hub->usage_tracker->get_performance_metrics(30);
        
        ?>
        <div class="wrap">
            <h1><?php _e('Usage Analytics', 'katahdin-ai-hub'); ?></h1>
            
            <div class="katahdin-analytics">
                <!-- Cost Analysis -->
                <div class="katahdin-card">
                    <h2><?php _e('Cost Analysis (Last 30 Days)', 'katahdin-ai-hub'); ?></h2>
                    <div class="cost-summary">
                        <div class="cost-item">
                            <span class="cost-value">$<?php echo number_format($cost_analysis['total_cost'], 4); ?></span>
                            <span class="cost-label"><?php _e('Total Cost', 'katahdin-ai-hub'); ?></span>
                        </div>
                    </div>
                    
                    <h3><?php _e('Cost by Plugin', 'katahdin-ai-hub'); ?></h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Plugin', 'katahdin-ai-hub'); ?></th>
                                <th><?php _e('Cost', 'katahdin-ai-hub'); ?></th>
                                <th><?php _e('Requests', 'katahdin-ai-hub'); ?></th>
                                <th><?php _e('Avg Cost/Request', 'katahdin-ai-hub'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cost_analysis['by_plugin'] as $plugin_cost): ?>
                                <tr>
                                    <td><?php echo esc_html($plugin_cost->plugin_id); ?></td>
                                    <td>$<?php echo number_format($plugin_cost->total_cost, 4); ?></td>
                                    <td><?php echo number_format($plugin_cost->requests); ?></td>
                                    <td>$<?php echo number_format($plugin_cost->avg_cost_per_request, 4); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Performance Metrics -->
                <div class="katahdin-card">
                    <h2><?php _e('Performance Metrics (Last 30 Days)', 'katahdin-ai-hub'); ?></h2>
                    <div class="performance-grid">
                        <div class="perf-item">
                            <span class="perf-value"><?php echo round($performance_metrics['success_rate']->success_percentage ?? 0, 1); ?>%</span>
                            <span class="perf-label"><?php _e('Success Rate', 'katahdin-ai-hub'); ?></span>
                        </div>
                        <div class="perf-item">
                            <span class="perf-value"><?php echo round($performance_metrics['response_times']->avg_response_time ?? 0); ?>ms</span>
                            <span class="perf-label"><?php _e('Avg Response Time', 'katahdin-ai-hub'); ?></span>
                        </div>
                        <div class="perf-item">
                            <span class="perf-value"><?php echo round($performance_metrics['response_times']->min_response_time ?? 0); ?>ms</span>
                            <span class="perf-label"><?php _e('Min Response Time', 'katahdin-ai-hub'); ?></span>
                        </div>
                        <div class="perf-item">
                            <span class="perf-value"><?php echo round($performance_metrics['response_times']->max_response_time ?? 0); ?>ms</span>
                            <span class="perf-label"><?php _e('Max Response Time', 'katahdin-ai-hub'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .katahdin-analytics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .cost-summary {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        
        .cost-item {
            text-align: center;
            padding: 20px;
            background: #f6f7f7;
            border-radius: 8px;
            min-width: 150px;
        }
        
        .cost-value {
            display: block;
            font-size: 32px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .cost-label {
            font-size: 14px;
            color: #646970;
        }
        
        .performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .perf-item {
            text-align: center;
            padding: 15px;
            background: #f6f7f7;
            border-radius: 4px;
        }
        
        .perf-value {
            display: block;
            font-size: 20px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .perf-label {
            font-size: 12px;
            color: #646970;
        }
        </style>
        <?php
    }
    
    /**
     * Plugins page
     */
    public function plugins_page() {
        $hub = katahdin_ai_hub();
        $registered_plugins = $hub->plugin_registry->get_all_plugins();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Plugin Registry', 'katahdin-ai-hub'); ?></h1>
            
            <div class="katahdin-plugins">
                <div class="katahdin-card">
                    <h2><?php _e('Registered Plugins', 'katahdin-ai-hub'); ?></h2>
                    
                    <?php if (empty($registered_plugins)): ?>
                        <p><?php _e('No plugins registered yet.', 'katahdin-ai-hub'); ?></p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Plugin ID', 'katahdin-ai-hub'); ?></th>
                                    <th><?php _e('Name', 'katahdin-ai-hub'); ?></th>
                                    <th><?php _e('Version', 'katahdin-ai-hub'); ?></th>
                                    <th><?php _e('Features', 'katahdin-ai-hub'); ?></th>
                                    <th><?php _e('Quota', 'katahdin-ai-hub'); ?></th>
                                    <th><?php _e('Status', 'katahdin-ai-hub'); ?></th>
                                    <th><?php _e('Actions', 'katahdin-ai-hub'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registered_plugins as $plugin): ?>
                                    <tr>
                                        <td><code><?php echo esc_html($plugin['plugin_id']); ?></code></td>
                                        <td><strong><?php echo esc_html($plugin['plugin_name']); ?></strong></td>
                                        <td><?php echo esc_html($plugin['version']); ?></td>
                                        <td>
                                            <?php if (!empty($plugin['features'])): ?>
                                                <?php echo implode(', ', array_map('esc_html', $plugin['features'])); ?>
                                            <?php else: ?>
                                                <em><?php _e('None', 'katahdin-ai-hub'); ?></em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo number_format($plugin['quota_used']); ?> / <?php echo number_format($plugin['quota_limit']); ?>
                                            (<?php echo $plugin['quota_limit'] > 0 ? round(($plugin['quota_used'] / $plugin['quota_limit']) * 100) : 0; ?>%)
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $plugin['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $plugin['is_active'] ? __('Active', 'katahdin-ai-hub') : __('Inactive', 'katahdin-ai-hub'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="plugin-actions">
                                                <?php if ($plugin['is_active']): ?>
                                                    <button class="button button-small deactivate-plugin" data-plugin-id="<?php echo esc_attr($plugin['plugin_id']); ?>">
                                                        <?php _e('Deactivate', 'katahdin-ai-hub'); ?>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="button button-small activate-plugin" data-plugin-id="<?php echo esc_attr($plugin['plugin_id']); ?>">
                                                        <?php _e('Activate', 'katahdin-ai-hub'); ?>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="button button-small reset-quota" data-plugin-id="<?php echo esc_attr($plugin['plugin_id']); ?>">
                                                    <?php _e('Reset Quota', 'katahdin-ai-hub'); ?>
                                                </button>
                                                
                                                <button class="button button-small button-link-delete delete-plugin" data-plugin-id="<?php echo esc_attr($plugin['plugin_id']); ?>">
                                                    <?php _e('Delete', 'katahdin-ai-hub'); ?>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <style>
        .katahdin-plugins {
            margin-top: 20px;
        }
        
        .plugin-actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .plugin-actions .button {
            font-size: 11px;
            padding: 2px 8px;
            height: auto;
            line-height: 1.4;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Activate/Deactivate plugin
            $('.activate-plugin, .deactivate-plugin').on('click', function() {
                var button = $(this);
                var pluginId = button.data('plugin-id');
                var action = button.hasClass('activate-plugin') ? 'activate' : 'deactivate';
                
                button.prop('disabled', true);
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_hub_' + action + '_plugin',
                    plugin_id: pluginId,
                    nonce: katahdin_ai_hub.nonce
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                        button.prop('disabled', false);
                    }
                });
            });
            
            // Delete plugin
            $('.delete-plugin').on('click', function() {
                var button = $(this);
                var pluginId = button.data('plugin-id');
                
                if (!confirm('Are you sure you want to delete this plugin registration? This action cannot be undone.')) {
                    return;
                }
                
                button.prop('disabled', true);
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_hub_delete_plugin',
                    plugin_id: pluginId,
                    nonce: katahdin_ai_hub.nonce
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                        button.prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX: Test API connection
     */
    public function ajax_test_api() {
        check_ajax_referer('katahdin_ai_hub_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $api_manager = katahdin_ai_hub()->api_manager;
        $result = $api_manager->test_connection();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['error']);
        }
    }
    
    /**
     * AJAX: Reset plugin quota
     */
    public function ajax_reset_quota() {
        check_ajax_referer('katahdin_ai_hub_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $plugin_id = sanitize_text_field($_POST['plugin_id']);
        
        if (empty($plugin_id)) {
            wp_send_json_error('Plugin ID required');
        }
        
        $result = katahdin_ai_hub()->plugin_registry->reset_quota($plugin_id);
        
        if ($result) {
            wp_send_json_success('Quota reset successfully');
        } else {
            wp_send_json_error('Failed to reset quota');
        }
    }
    
    /**
     * AJAX: Export usage data
     */
    public function ajax_export_data() {
        check_ajax_referer('katahdin_ai_hub_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $plugin_id = sanitize_text_field($_POST['plugin_id'] ?? '');
        $days = intval($_POST['days'] ?? 30);
        
        $data = katahdin_ai_hub()->usage_tracker->export_usage_data($plugin_id, $days);
        
        // Generate CSV
        $filename = 'katahdin-ai-hub-usage-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Write headers
            fputcsv($output, array_keys($data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
}
