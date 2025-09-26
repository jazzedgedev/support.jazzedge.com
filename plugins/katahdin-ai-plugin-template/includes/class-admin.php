<?php
/**
 * Admin Class for Katahdin AI Plugin Template
 */

if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Plugin_Template_Admin {
    
    public function __construct() {
        add_action('wp_ajax_katahdin_ai_plugin_template_test', array($this, 'ajax_test'));
        add_action('wp_ajax_katahdin_ai_plugin_template_debug', array($this, 'ajax_debug'));
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Katahdin AI Plugin Template</h1>
            
            <div class="katahdin-plugin-card" style="background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 20px; margin-bottom: 20px;">
                <h2>🔧 Plugin Status</h2>
                <div id="plugin-status">
                    <p>Loading status...</p>
                </div>
                <button type="button" class="button button-primary" id="refresh-status">Refresh Status</button>
            </div>
            
            <div class="katahdin-plugin-card" style="background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 20px; margin-bottom: 20px;">
                <h2>🧪 Testing</h2>
                <p>Test the plugin functionality and integration with Katahdin AI Hub.</p>
                
                <div style="margin: 20px 0;">
                    <button type="button" class="button button-primary" id="test-plugin">Test Plugin</button>
                    <button type="button" class="button button-secondary" id="debug-plugin">Debug Info</button>
                </div>
                
                <div id="test-results" style="margin-top: 20px;"></div>
            </div>
            
            <div class="katahdin-plugin-card" style="background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 20px; margin-bottom: 20px;">
                <h2>📊 Usage Statistics</h2>
                <div id="usage-stats">
                    <p>Loading statistics...</p>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Load initial data
            loadPluginStatus();
            loadUsageStats();
            
            // Refresh status button
            $('#refresh-status').on('click', function() {
                loadPluginStatus();
            });
            
            // Test plugin button
            $('#test-plugin').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('Testing...');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_plugin_template_test',
                    nonce: katahdinAIPluginTemplate.nonce
                }, function(response) {
                    if (response.success) {
                        $('#test-results').html('<div class="notice notice-success"><p><strong>✅ Test passed!</strong><br>' + JSON.stringify(response.data, null, 2) + '</p></div>');
                    } else {
                        $('#test-results').html('<div class="notice notice-error"><p><strong>❌ Test failed:</strong> ' + response.data + '</p></div>');
                    }
                }).fail(function() {
                    $('#test-results').html('<div class="notice notice-error"><p><strong>❌ AJAX Error:</strong> Could not test plugin</p></div>');
                }).always(function() {
                    $btn.prop('disabled', false).text('Test Plugin');
                });
            });
            
            // Debug plugin button
            $('#debug-plugin').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('Debugging...');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_plugin_template_debug',
                    nonce: katahdinAIPluginTemplate.nonce
                }, function(response) {
                    if (response.success) {
                        $('#test-results').html('<div class="notice notice-info"><p><strong>🔍 Debug Info:</strong><br><pre style="background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto;">' + JSON.stringify(response.data, null, 2) + '</pre></p></div>');
                    } else {
                        $('#test-results').html('<div class="notice notice-error"><p><strong>❌ Debug failed:</strong> ' + response.data + '</p></div>');
                    }
                }).fail(function() {
                    $('#test-results').html('<div class="notice notice-error"><p><strong>❌ AJAX Error:</strong> Could not debug plugin</p></div>');
                }).always(function() {
                    $btn.prop('disabled', false).text('Debug Info');
                });
            });
            
            function loadPluginStatus() {
                $('#plugin-status').html('<p>Loading status...</p>');
                
                $.get(katahdinAIPluginTemplate.restUrl + 'status', function(response) {
                    if (response.success) {
                        var html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">';
                        html += '<div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 6px;"><strong>' + response.plugin + '</strong><br>Plugin Name</div>';
                        html += '<div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 6px;"><strong>' + response.version + '</strong><br>Version</div>';
                        html += '<div style="text-align: center; padding: 15px; background: ' + (response.hub_status === 'Connected' ? '#d4edda' : '#f8d7da') + '; border-radius: 6px;"><strong>' + response.hub_status + '</strong><br>Hub Status</div>';
                        html += '</div>';
                        $('#plugin-status').html(html);
                    } else {
                        $('#plugin-status').html('<p style="color: red;">Error loading status</p>');
                    }
                }).fail(function() {
                    $('#plugin-status').html('<p style="color: red;">AJAX Error loading status</p>');
                });
            }
            
            function loadUsageStats() {
                $('#usage-stats').html('<p>Loading statistics...</p>');
                
                // This would typically come from the hub's usage tracking
                var stats = {
                    'total_requests': 0,
                    'successful_requests': 0,
                    'failed_requests': 0,
                    'tokens_used': 0
                };
                
                var html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">';
                html += '<div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 6px;"><strong>' + stats.total_requests + '</strong><br>Total Requests</div>';
                html += '<div style="text-align: center; padding: 15px; background: #d4edda; border-radius: 6px;"><strong>' + stats.successful_requests + '</strong><br>Successful</div>';
                html += '<div style="text-align: center; padding: 15px; background: #f8d7da; border-radius: 6px;"><strong>' + stats.failed_requests + '</strong><br>Failed</div>';
                html += '<div style="text-align: center; padding: 15px; background: #fff3cd; border-radius: 6px;"><strong>' + stats.tokens_used + '</strong><br>Tokens Used</div>';
                html += '</div>';
                $('#usage-stats').html(html);
            }
        });
        </script>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $enabled = get_option('katahdin_ai_plugin_template_enabled', true);
        $debug = get_option('katahdin_ai_plugin_template_debug', false);
        $custom = get_option('katahdin_ai_plugin_template_custom', '');
        ?>
        <div class="wrap">
            <h1>Plugin Template Settings</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('katahdin_ai_plugin_template_settings', 'settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Plugin Enabled</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="1" <?php checked($enabled, true); ?>>
                                Enable the plugin
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Debug Mode</th>
                        <td>
                            <label>
                                <input type="checkbox" name="debug" value="1" <?php checked($debug, true); ?>>
                                Enable debug logging
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Custom Setting</th>
                        <td>
                            <input type="text" name="custom" value="<?php echo esc_attr($custom); ?>" class="regular-text">
                            <p class="description">Custom setting for your plugin</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Logs page
     */
    public function logs_page() {
        ?>
        <div class="wrap">
            <h1>Plugin Template Logs</h1>
            
            <div class="katahdin-plugin-card" style="background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 20px; margin-bottom: 20px;">
                <h2>📊 Statistics</h2>
                <div id="logs-stats">
                    <p>Loading statistics...</p>
                </div>
            </div>
            
            <div class="katahdin-plugin-card" style="background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 20px; margin-bottom: 20px;">
                <h2>📋 Recent Logs</h2>
                <div id="recent-logs">
                    <p>Loading logs...</p>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            loadLogsStats();
            loadRecentLogs();
            
            function loadLogsStats() {
                $('#logs-stats').html('<p>Loading statistics...</p>');
                
                // This would typically come from your logger
                var stats = {
                    'total_logs': 0,
                    'success_logs': 0,
                    'error_logs': 0,
                    'pending_logs': 0
                };
                
                var html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">';
                html += '<div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 6px;"><strong>' + stats.total_logs + '</strong><br>Total Logs</div>';
                html += '<div style="text-align: center; padding: 15px; background: #d4edda; border-radius: 6px;"><strong>' + stats.success_logs + '</strong><br>Success</div>';
                html += '<div style="text-align: center; padding: 15px; background: #f8d7da; border-radius: 6px;"><strong>' + stats.error_logs + '</strong><br>Errors</div>';
                html += '<div style="text-align: center; padding: 15px; background: #fff3cd; border-radius: 6px;"><strong>' + stats.pending_logs + '</strong><br>Pending</div>';
                html += '</div>';
                $('#logs-stats').html(html);
            }
            
            function loadRecentLogs() {
                $('#recent-logs').html('<p>Loading logs...</p>');
                
                // This would typically come from your logger
                var logs = [];
                
                if (logs.length > 0) {
                    var html = '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Time</th><th>Status</th><th>Message</th><th>Actions</th></tr></thead><tbody>';
                    
                    logs.forEach(function(log) {
                        html += '<tr>';
                        html += '<td>' + new Date(log.timestamp).toLocaleString() + '</td>';
                        html += '<td><span class="katahdin-plugin-status ' + log.status + '">' + log.status.charAt(0).toUpperCase() + log.status.slice(1) + '</span></td>';
                        html += '<td>' + log.message + '</td>';
                        html += '<td><button class="button button-small view-log-btn" data-log-id="' + log.id + '">View Details</button></td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    $('#recent-logs').html(html);
                } else {
                    $('#recent-logs').html('<p>No logs found.</p>');
                }
            }
        });
        </script>
        <?php
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        if (!wp_verify_nonce($_POST['settings_nonce'], 'katahdin_ai_plugin_template_settings')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $enabled = isset($_POST['enabled']) ? true : false;
        $debug = isset($_POST['debug']) ? true : false;
        $custom = sanitize_text_field($_POST['custom']);
        
        update_option('katahdin_ai_plugin_template_enabled', $enabled);
        update_option('katahdin_ai_plugin_template_debug', $debug);
        update_option('katahdin_ai_plugin_template_custom', $custom);
        
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    /**
     * AJAX test handler
     */
    public function ajax_test() {
        check_ajax_referer('katahdin_ai_plugin_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        try {
            // Test plugin functionality
            $test_data = array(
                'message' => 'This is a test message',
                'timestamp' => current_time('mysql')
            );
            
            // Test hub integration
            $hub_status = 'Hub not available';
            if (function_exists('katahdin_ai_hub')) {
                $hub = katahdin_ai_hub();
                if ($hub && $hub->api_manager) {
                    $hub_status = 'Hub connected';
                }
            }
            
            wp_send_json_success(array(
                'message' => 'Plugin test completed successfully!',
                'test_data' => $test_data,
                'hub_status' => $hub_status,
                'plugin_version' => KATAHDIN_AI_PLUGIN_TEMPLATE_VERSION,
                'wordpress_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Test failed: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX debug handler
     */
    public function ajax_debug() {
        check_ajax_referer('katahdin_ai_plugin_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        try {
            $debug_info = array(
                'plugin' => 'Katahdin AI Plugin Template',
                'version' => KATAHDIN_AI_PLUGIN_TEMPLATE_VERSION,
                'wordpress_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
                'plugin_enabled' => get_option('katahdin_ai_plugin_template_enabled', true),
                'debug_mode' => get_option('katahdin_ai_plugin_template_debug', false),
                'hub_available' => function_exists('katahdin_ai_hub'),
                'rest_api_available' => rest_url('katahdin-ai-plugin-template/v1/'),
                'timestamp' => current_time('mysql')
            );
            
            wp_send_json_success($debug_info);
            
        } catch (Exception $e) {
            wp_send_json_error('Debug failed: ' . $e->getMessage());
        }
    }
}
