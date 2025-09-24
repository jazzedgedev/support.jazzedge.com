<?php
/**
 * Admin Interface for Katahdin AI Webhook
 * Provides admin settings and management interface
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Webhook_Admin {
    
    /**
     * Initialize admin interface
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_katahdin_ai_webhook_test_api_key', array($this, 'ajax_test_api_key'));
        add_action('wp_ajax_katahdin_ai_webhook_test_webhook', array($this, 'ajax_test_webhook'));
        add_action('wp_ajax_katahdin_ai_webhook_test_email', array($this, 'ajax_test_email'));
        add_action('wp_ajax_katahdin_ai_webhook_regenerate_secret', array($this, 'ajax_regenerate_secret'));
        add_action('wp_ajax_katahdin_ai_webhook_comprehensive_debug', array($this, 'ajax_comprehensive_debug'));
        add_action('admin_head', array($this, 'add_admin_styles'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            'Katahdin AI Webhook',
            'AI Webhook',
            'manage_options',
            'katahdin-ai-webhook',
            array($this, 'admin_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>'),
            30
        );
        
        // Submenu for main settings
        add_submenu_page(
            'katahdin-ai-webhook',
            'Settings',
            'Settings',
            'manage_options',
            'katahdin-ai-webhook',
            array($this, 'admin_page')
        );
        
        // Submenu for logs
        add_submenu_page(
            'katahdin-ai-webhook',
            'Webhook Logs',
            'Logs',
            'manage_options',
            'katahdin-ai-webhook-logs',
            array($this, 'logs_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('katahdin_ai_webhook_settings', 'katahdin_ai_webhook_prompt');
        register_setting('katahdin_ai_webhook_settings', 'katahdin_ai_webhook_email');
        register_setting('katahdin_ai_webhook_settings', 'katahdin_ai_webhook_email_subject');
        register_setting('katahdin_ai_webhook_settings', 'katahdin_ai_webhook_enabled');
        register_setting('katahdin_ai_webhook_settings', 'katahdin_ai_webhook_model');
        register_setting('katahdin_ai_webhook_settings', 'katahdin_ai_webhook_max_tokens');
        register_setting('katahdin_ai_webhook_settings', 'katahdin_ai_webhook_temperature');
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        $webhook_url = katahdin_ai_webhook()->get_webhook_url();
        $webhook_secret = katahdin_ai_webhook()->get_webhook_secret();
        $recent_logs = katahdin_ai_webhook()->webhook_handler->get_recent_logs(5);
        $katahdin_status = katahdin_ai_webhook()->webhook_handler->check_katahdin_hub_status();
        
        ?>
        <div class="wrap katahdin-webhook-admin">
            <div class="katahdin-webhook-header">
                <h1>🤖 Katahdin AI Webhook</h1>
                <p>Configure and manage your AI-powered webhook for form processing and analysis</p>
            </div>
            
            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Settings saved successfully!</strong></p>
                </div>
            <?php endif; ?>
            
            <?php if (!$katahdin_status['api_connection_working']): ?>
                <div class="notice notice-warning is-dismissible">
                    <p><strong>⚠️ Katahdin AI Hub Configuration Required</strong></p>
                    <p>The webhook requires Katahdin AI Hub to be properly configured with a valid OpenAI API key. 
                    <a href="<?php echo admin_url('admin.php?page=katahdin-ai-hub'); ?>">Configure Katahdin AI Hub</a> or 
                    <button type="button" class="button button-small check-status-btn">Check Status</button> for more details.</p>
                </div>
            <?php endif; ?>
            
            <div class="katahdin-ai-webhook-admin">
                <!-- Status Cards -->
                <div class="katahdin-webhook-card">
                    <h2>📊 System Status</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                            <h3 style="margin: 0 0 10px 0; color: #333;">📡 Webhook Status</h3>
                            <span class="katahdin-webhook-status <?php echo get_option('katahdin_ai_webhook_enabled', true) ? 'success' : 'error'; ?>">
                                <?php echo get_option('katahdin_ai_webhook_enabled', true) ? 'Enabled' : 'Disabled'; ?>
                            </span>
                        </div>
                        <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                            <h3 style="margin: 0 0 10px 0; color: #333;">🤖 Katahdin AI Hub</h3>
                            <span class="katahdin-webhook-status <?php echo $katahdin_status['api_connection_working'] ? 'success' : 'error'; ?>" id="katahdin-status">
                                <?php 
                                if ($katahdin_status['api_connection_working']) {
                                    echo 'Connected';
                                } elseif ($katahdin_status['api_key_configured']) {
                                    echo 'API Key Set';
                                } elseif ($katahdin_status['initialized']) {
                                    echo 'Initialized';
                                } elseif ($katahdin_status['available']) {
                                    echo 'Available';
                                } else {
                                    echo 'Not Available';
                                }
                                ?>
                            </span>
                            <?php if ($katahdin_status['error_message']): ?>
                                <p style="margin: 10px 0 0 0; color: #dc3545; font-size: 12px;"><?php echo esc_html($katahdin_status['error_message']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                            <h3 style="margin: 0 0 10px 0; color: #333;">🔗 Webhook URL</h3>
                            <p style="margin: 0; font-family: monospace; font-size: 12px; word-break: break-all;">
                                <?php echo esc_url($webhook_url); ?>
                            </p>
                            <button type="button" class="katahdin-webhook-button copy-url-btn" data-url="<?php echo esc_attr($webhook_url); ?>" style="margin-top: 10px; padding: 6px 12px; font-size: 12px;">Copy URL</button>
                        </div>
                        <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                            <h3 style="margin: 0 0 10px 0; color: #333;">🔐 Secret Key</h3>
                            <p style="margin: 0; font-family: monospace; font-size: 12px;">
                                <?php echo esc_html(substr($webhook_secret, 0, 8) . '...'); ?>
                            </p>
                            <button type="button" class="katahdin-webhook-button secondary regenerate-secret-btn" style="margin-top: 10px; padding: 6px 12px; font-size: 12px;">Regenerate</button>
                        </div>
                    </div>
                </div>
                
                <!-- Settings Form -->
                <div class="katahdin-webhook-card">
                    <h2>⚙️ Configuration Settings</h2>
                    <form method="post" action="options.php">
                        <?php settings_fields('katahdin_ai_webhook_settings'); ?>
                        <table class="form-table katahdin-webhook-form-table">
                            <tr>
                                <th scope="row">Enable Webhook</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="katahdin_ai_webhook_enabled" value="1" 
                                               <?php checked(get_option('katahdin_ai_webhook_enabled', true)); ?> />
                                        Enable webhook processing
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="settings-section">
                        <h2>🤖 AI Settings</h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">AI Prompt</th>
                                <td>
                                    <textarea name="katahdin_ai_webhook_prompt" rows="5" cols="50" class="large-text"><?php echo esc_textarea(get_option('katahdin_ai_webhook_prompt', 'Analyze the following form submission data and provide insights, recommendations, or summaries as appropriate. Be concise but informative.')); ?></textarea>
                                    <p class="description">The prompt that will be sent to the AI along with the form data.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">AI Model</th>
                                <td>
                                    <select name="katahdin_ai_webhook_model">
                                        <option value="gpt-3.5-turbo" <?php selected(get_option('katahdin_ai_webhook_model', 'gpt-3.5-turbo'), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                                        <option value="gpt-4" <?php selected(get_option('katahdin_ai_webhook_model', 'gpt-3.5-turbo'), 'gpt-4'); ?>>GPT-4</option>
                                        <option value="gpt-4-turbo" <?php selected(get_option('katahdin_ai_webhook_model', 'gpt-3.5-turbo'), 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Max Tokens</th>
                                <td>
                                    <input type="number" name="katahdin_ai_webhook_max_tokens" value="<?php echo esc_attr(get_option('katahdin_ai_webhook_max_tokens', 1000)); ?>" min="1" max="4000" />
                                    <p class="description">Maximum number of tokens for AI response (1-4000).</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Temperature</th>
                                <td>
                                    <input type="number" name="katahdin_ai_webhook_temperature" value="<?php echo esc_attr(get_option('katahdin_ai_webhook_temperature', 0.7)); ?>" min="0" max="2" step="0.1" />
                                    <p class="description">Controls randomness in AI response (0-2). Lower values are more focused and deterministic.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="settings-section">
                        <h2>📧 Email Settings</h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">Email Address</th>
                                <td>
                                    <input type="email" name="katahdin_ai_webhook_email" value="<?php echo esc_attr(get_option('katahdin_ai_webhook_email', get_option('admin_email'))); ?>" class="regular-text" />
                                    <p class="description">Email address to receive AI analysis results.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Email Subject</th>
                                <td>
                                    <input type="text" name="katahdin_ai_webhook_email_subject" value="<?php echo esc_attr(get_option('katahdin_ai_webhook_email_subject', 'AI Analysis Results - Form Submission')); ?>" class="regular-text" />
                                    <p class="description">Subject line for analysis emails.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <?php submit_button('Save Settings'); ?>
                </form>
                
                <!-- Test Section -->
                <div class="katahdin-webhook-card">
                    <h2>🧪 Testing</h2>
                    <p>Test the webhook and email functionality to ensure everything is working correctly.</p>
                    
                    <div style="margin: 20px 0;">
                        <button type="button" class="katahdin-webhook-button test-api-key-btn" style="margin-right: 10px;">🔑 Test API Key</button>
                        <button type="button" class="katahdin-webhook-button test-webhook-btn" style="margin-right: 10px;">Test Webhook</button>
                        <button type="button" class="katahdin-webhook-button secondary test-email-btn" style="margin-right: 10px;">Test Email</button>
                        <button type="button" class="katahdin-webhook-button secondary check-status-btn" style="margin-right: 10px;">Check Status</button>
                        <button type="button" class="katahdin-webhook-button secondary debug-info-btn" style="margin-right: 10px;">Debug Info</button>
                    </div>
                    
                    <div style="margin: 20px 0; padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px;">
                        <h3 style="margin-top: 0; color: #856404;">🔧 Advanced Debugging Tools</h3>
                        <p style="margin-bottom: 15px; color: #856404;">Use these tools to diagnose integration issues with Katahdin AI Hub:</p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                            <button type="button" class="katahdin-webhook-button primary comprehensive-debug-btn">🔍 Comprehensive Debug</button>
                        </div>
                    </div>
                    
                    <div id="test-results" style="display: none;">
                        <h3>Test Results</h3>
                        <div style="margin-bottom: 10px;"><button type="button" class="katahdin-webhook-button secondary copy-debug-btn" data-target="test-output" style="font-size: 12px; padding: 6px 12px;">📋 Copy Results</button></div>
                        <div id="test-output" class="katahdin-webhook-test-results"></div>
                    </div>
                </div>
                
                <!-- Recent Logs -->
                <?php if (!empty($recent_logs)): ?>
                <div class="katahdin-webhook-card">
                    <h2>📋 Recent Activity</h2>
                    <div style="overflow-x: auto;">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Method</th>
                                    <th>Response Code</th>
                                    <th>Processing Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td><?php echo esc_html(date('M j, Y H:i:s', strtotime($log['timestamp']))); ?></td>
                                    <td>
                                        <span class="katahdin-webhook-status <?php echo $log['status'] === 'success' ? 'success' : ($log['status'] === 'error' ? 'error' : 'warning'); ?>">
                                            <?php echo esc_html(ucfirst($log['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($log['method']); ?></td>
                                    <td><?php echo esc_html($log['response_code'] ?: 'N/A'); ?></td>
                                    <td><?php echo esc_html($log['processing_time_ms'] ? $log['processing_time_ms'] . 'ms' : 'N/A'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p style="margin-top: 15px;">
                        <a href="<?php echo admin_url('admin.php?page=katahdin-ai-webhook-logs'); ?>" class="katahdin-webhook-button">View All Logs</a>
                    </p>
                </div>
                <?php endif; ?>
                
                <!-- Integration Instructions -->
                <div class="katahdin-webhook-card">
                    <h2>🔗 Integration Instructions</h2>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 6px;">
                        <h3>FluentForm Integration</h3>
                        <p>To integrate with FluentForm, add this webhook URL to your form's webhook settings:</p>
                        <code style="background: #e9ecef; padding: 8px; border-radius: 4px; display: block; margin: 10px 0; word-break: break-all;"><?php echo esc_url($webhook_url); ?></code>
                        
                        <h4>Required Headers:</h4>
                        <ul>
                            <li><code>X-Webhook-Secret: <?php echo esc_html($webhook_secret); ?></code></li>
                            <li><code>Content-Type: application/json</code></li>
                        </ul>
                        
                        <h4>Expected Payload Format:</h4>
                        <pre style="background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 12px;"><code>{
    "form_data": {
        "field_name": "field_value",
        "email": "user@example.com",
        "message": "User message"
    },
    "form_id": "your_form_id",
    "entry_id": "entry_id"
}</code></pre>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .katahdin-ai-webhook-admin {
            max-width: 1200px;
        }
        
        .status-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .status-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .status-card h3 {
            margin-top: 0;
            color: #0073aa;
        }
        
        .status-indicator.enabled {
            color: #46b450;
            font-weight: bold;
        }
        
        .status-indicator.disabled {
            color: #dc3232;
            font-weight: bold;
        }
        
        .webhook-url, .secret-key {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .webhook-url code, .secret-key code {
            flex: 1;
            background: #f1f1f1;
            padding: 5px 10px;
            border-radius: 4px;
            word-break: break-all;
        }
        
        .copy-url-btn, .regenerate-secret-btn {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .settings-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .settings-section h2 {
            margin-top: 0;
            color: #0073aa;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        
        .test-section, .logs-section, .integration-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .test-section h2, .logs-section h2, .integration-section h2 {
            margin-top: 0;
            color: #0073aa;
        }
        
        .test-buttons {
            margin: 15px 0;
        }
        
        .test-results {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .logs-container {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .log-entry {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 10px;
            background: #f9f9f9;
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .log-form-id {
            color: #0073aa;
            font-weight: bold;
        }
        
        .log-analysis {
            font-style: italic;
            color: #666;
        }
        
        .integration-content code {
            background: #f1f1f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        
        .integration-content pre {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Copy webhook URL
            $('.copy-url-btn').on('click', function() {
                var url = $(this).data('url');
                navigator.clipboard.writeText(url).then(function() {
                    alert('Webhook URL copied to clipboard!');
                });
            });
            
            // Regenerate secret
            $('.regenerate-secret-btn').on('click', function() {
                if (confirm('Are you sure you want to regenerate the webhook secret? This will break existing integrations until they are updated.')) {
                    $.post(ajaxurl, {
                        action: 'katahdin_ai_webhook_regenerate_secret',
                        nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error regenerating secret: ' + response.data);
                        }
                    });
                }
            });
            
            // Test API key
            $('.test-api-key-btn').on('click', function() {
                $('#test-results').show();
                $('#test-output').html('<p>Testing API key...</p>');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_test_api_key',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        $('#test-output').html('<div style="color: green;"><strong>✓ API Key test successful!</strong><br>' + JSON.stringify(response.data, null, 2) + '</div>');
                    } else {
                        $('#test-output').html('<div style="color: red;"><strong>✗ API Key test failed:</strong><br>' + response.data + '</div>');
                    }
                });
            });
            
            // Test webhook
            $('.test-webhook-btn').on('click', function() {
                $('#test-results').show();
                $('#test-output').html('<p>Testing webhook...</p>');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_test_webhook',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        $('#test-output').html('<div style="color: green;"><strong>✓ Webhook test successful!</strong><br>' + JSON.stringify(response.data, null, 2) + '</div>');
                    } else {
                        $('#test-output').html('<div style="color: red;"><strong>✗ Webhook test failed:</strong><br>' + response.data + '</div>');
                    }
                });
            });
            
            // Test email
            $('.test-email-btn').on('click', function() {
                $('#test-results').show();
                $('#test-output').html('<p>Testing email...</p>');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_test_email',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        $('#test-output').html('<div style="color: green;"><strong>✓ Email test successful!</strong><br>' + JSON.stringify(response.data, null, 2) + '</div>');
                    } else {
                        $('#test-output').html('<div style="color: red;"><strong>✗ Email test failed:</strong><br>' + response.data + '</div>');
                    }
                });
            });
            
            // Check status
            $('.check-status-btn').on('click', function() {
                var $btn = $(this);
                $('#test-results').show();
                $('#test-output').html('<p>Checking status...</p>');
                
                $btn.prop('disabled', true).text('Checking...');
                
                $.get('<?php echo rest_url('katahdin-ai-webhook/v1/status'); ?>', {
                    _wpnonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
                }, function(response) {
                    $btn.prop('disabled', false).text('Check Status');
                    
                    var html = '<div class="status-check-results">';
                    html += '<h4>System Status</h4>';
                    html += '<ul>';
                    html += '<li><strong>Webhook Enabled:</strong> ' + (response.webhook_enabled ? '✅ Yes' : '❌ No') + '</li>';
                    html += '<li><strong>Katahdin AI Hub Available:</strong> ' + (response.katahdin_hub_status.available ? '✅ Yes' : '❌ No') + '</li>';
                    html += '<li><strong>Hub Initialized:</strong> ' + (response.katahdin_hub_status.initialized ? '✅ Yes' : '❌ No') + '</li>';
                    html += '<li><strong>API Key Configured:</strong> ' + (response.katahdin_hub_status.api_key_configured ? '✅ Yes' : '❌ No') + '</li>';
                    html += '<li><strong>API Connection:</strong> ' + (response.katahdin_hub_status.api_connection_working ? '✅ Working' : '❌ Failed') + '</li>';
                    html += '</ul>';
                    
                    if (response.katahdin_hub_status.error_message) {
                        html += '<div style="color: red; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;"><strong>Error:</strong> ' + response.katahdin_hub_status.error_message + '</div>';
                    }
                    
                    html += '<h4>Full Status Response</h4>';
                    html += '<pre style="background: #f1f1f1; padding: 15px; border-radius: 4px; overflow-x: auto;">' + JSON.stringify(response, null, 2) + '</pre>';
                    html += '</div>';
                    
                    $('#test-output').html(html);
                    
                    // Update the status indicator on the page
                    var $statusIndicator = $('#katahdin-status');
                    if (response.katahdin_hub_status.api_connection_working) {
                        $statusIndicator.text('✅ Connected').removeClass('disabled').addClass('enabled');
                    } else if (response.katahdin_hub_status.api_key_configured) {
                        $statusIndicator.text('⚠️ API Key Set').removeClass('enabled').addClass('disabled');
                    } else if (response.katahdin_hub_status.initialized) {
                        $statusIndicator.text('⚠️ Initialized').removeClass('enabled').addClass('disabled');
                    } else if (response.katahdin_hub_status.available) {
                        $statusIndicator.text('⚠️ Available').removeClass('enabled').addClass('disabled');
                    } else {
                        $statusIndicator.text('❌ Not Available').removeClass('enabled').addClass('disabled');
                    }
                    
                }).fail(function() {
                    $btn.prop('disabled', false).text('Check Status');
                    $('#test-output').html('<div style="color: red;"><strong>✗ Network error occurred during status check</strong></div>');
                });
            });
            
            // Debug info
            $('.debug-info-btn').on('click', function() {
                var $btn = $(this);
                $('#test-results').show();
                $('#test-output').html('<p>Gathering debug information...</p>');
                
                $btn.prop('disabled', true).text('Gathering...');
                
                $.get('<?php echo rest_url('katahdin-ai-webhook/v1/debug'); ?>', {
                    _wpnonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
                }, function(response) {
                    $btn.prop('disabled', false).text('Debug Info');
                    
                    var html = '<div class="debug-info-results">';
                    html += '<h4>Debug Information</h4>';
                    html += '<div style="margin-bottom: 10px;"><button type="button" class="katahdin-webhook-button secondary copy-debug-btn" data-target="info-debug-content" style="font-size: 12px; padding: 6px 12px;">📋 Copy Results</button></div>';
                    html += '<pre id="info-debug-content" style="background: #f1f1f1; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px;">' + JSON.stringify(response, null, 2) + '</pre>';
                    html += '</div>';
                    
                    $('#test-output').html(html);
                    
                }).fail(function() {
                    $btn.prop('disabled', false).text('Debug Info');
                    $('#test-output').html('<div style="color: red;"><strong>✗ Network error occurred during debug check</strong></div>');
                });
            });
            
            // Debug Katahdin Hub
            $('.debug-hub-btn').on('click', function() {
                var $btn = $(this);
                $('#test-results').show();
                $('#test-output').html('<p>Debugging Katahdin AI Hub integration...</p>');
                
                $btn.prop('disabled', true).text('Debugging...');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_debug_hub',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                }, function(response) {
                    $btn.prop('disabled', false).text('Debug Katahdin Hub');
                    
                    var html = '<div class="debug-hub-results">';
                    html += '<h4>🔍 Katahdin AI Hub Debug Report</h4>';
                    html += '<div style="margin-bottom: 10px;"><button type="button" class="katahdin-webhook-button secondary copy-debug-btn" data-target="hub-debug-content" style="font-size: 12px; padding: 6px 12px;">📋 Copy Results</button></div>';
                    html += '<pre id="hub-debug-content" style="background: #f1f1f1; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; max-height: 500px; overflow-y: auto;">' + JSON.stringify(response, null, 2) + '</pre>';
                    html += '</div>';
                    
                    $('#test-output').html(html);
                    
                }).fail(function() {
                    $btn.prop('disabled', false).text('Debug Katahdin Hub');
                    $('#test-output').html('<div style="color: red;"><strong>✗ Error occurred during hub debugging</strong></div>');
                });
            });
            
            // Debug API Call
            $('.debug-api-call-btn').on('click', function() {
                var $btn = $(this);
                $('#test-results').show();
                $('#test-output').html('<p>Testing direct API call to Katahdin AI Hub...</p>');
                
                $btn.prop('disabled', true).text('Testing...');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_debug_api_call',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                }, function(response) {
                    $btn.prop('disabled', false).text('Test API Call');
                    
                    var html = '<div class="debug-api-results">';
                    html += '<h4>🔌 Direct API Call Test</h4>';
                    html += '<div style="margin-bottom: 10px;"><button type="button" class="katahdin-webhook-button secondary copy-debug-btn" data-target="api-debug-content" style="font-size: 12px; padding: 6px 12px;">📋 Copy Results</button></div>';
                    html += '<pre id="api-debug-content" style="background: #f1f1f1; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; max-height: 500px; overflow-y: auto;">' + JSON.stringify(response, null, 2) + '</pre>';
                    html += '</div>';
                    
                    $('#test-output').html(html);
                    
                }).fail(function() {
                    $btn.prop('disabled', false).text('Test API Call');
                    $('#test-output').html('<div style="color: red;"><strong>✗ Error occurred during API call test</strong></div>');
                });
            });
            
            // Debug REST Routes
            $('.debug-rest-routes-btn').on('click', function() {
                var $btn = $(this);
                $('#test-results').show();
                $('#test-output').html('<p>Checking REST API routes...</p>');
                
                $btn.prop('disabled', true).text('Checking...');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_debug_rest_routes',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                }, function(response) {
                    $btn.prop('disabled', false).text('Check REST Routes');
                    
                    var html = '<div class="debug-routes-results">';
                    html += '<h4>🛣️ REST API Routes Debug</h4>';
                    html += '<div style="margin-bottom: 10px;"><button type="button" class="katahdin-webhook-button secondary copy-debug-btn" data-target="routes-debug-content" style="font-size: 12px; padding: 6px 12px;">📋 Copy Results</button></div>';
                    html += '<pre id="routes-debug-content" style="background: #f1f1f1; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; max-height: 500px; overflow-y: auto;">' + JSON.stringify(response, null, 2) + '</pre>';
                    html += '</div>';
                    
                    $('#test-output').html(html);
                    
                }).fail(function() {
                    $btn.prop('disabled', false).text('Check REST Routes');
                    $('#test-output').html('<div style="color: red;"><strong>✗ Error occurred during routes check</strong></div>');
                });
            });
            
            // Debug Plugin Loading
            $('.debug-plugin-loading-btn').on('click', function() {
                var $btn = $(this);
                $('#test-results').show();
                $('#test-output').html('<p>Checking plugin loading and dependencies...</p>');
                
                $btn.prop('disabled', true).text('Checking...');
                
                var debugInfo = {
                    'WordPress Version': '<?php echo get_bloginfo("version"); ?>',
                    'PHP Version': '<?php echo PHP_VERSION; ?>',
                    'Plugin Directory': '<?php echo plugin_dir_path(__FILE__); ?>',
                    'Plugin URL': '<?php echo plugin_dir_url(__FILE__); ?>',
                    'Active Plugins': <?php echo json_encode(get_option('active_plugins')); ?>,
                    'Katahdin AI Hub Active': <?php echo is_plugin_active('katahdin-ai-hub/katahdin-ai-hub.php') ? 'true' : 'false'; ?>,
                    'Katahdin AI Webhook Active': <?php echo is_plugin_active('katahdin-ai-webhook/katahdin-ai-webhook.php') ? 'true' : 'false'; ?>,
                    'Function Exists katahdin_ai_hub': <?php echo function_exists('katahdin_ai_hub') ? 'true' : 'false'; ?>,
                    'Function Exists katahdin_ai_webhook': <?php echo function_exists('katahdin_ai_webhook') ? 'true' : 'false'; ?>,
                    'Class Exists Katahdin_AI_Hub': <?php echo class_exists('Katahdin_AI_Hub') ? 'true' : 'false'; ?>,
                    'Class Exists Katahdin_AI_Webhook': <?php echo class_exists('Katahdin_AI_Webhook') ? 'true' : 'false'; ?>,
                    'Class Exists Katahdin_AI_Webhook_Handler': <?php echo class_exists('Katahdin_AI_Webhook_Handler') ? 'true' : 'false'; ?>,
                    'Class Exists Katahdin_AI_Webhook_Logger': <?php echo class_exists('Katahdin_AI_Webhook_Logger') ? 'true' : 'false'; ?>,
                    'REST API Available': <?php echo rest_url() ? 'true' : 'false'; ?>,
                    'Site URL': '<?php echo get_site_url(); ?>',
                    'Admin URL': '<?php echo admin_url(); ?>',
                    'REST URL': '<?php echo rest_url(); ?>'
                };
                
                $btn.prop('disabled', false).text('Check Plugin Loading');
                
                var html = '<div class="debug-loading-results">';
                html += '<h4>🔧 Plugin Loading Debug Report</h4>';
                html += '<div style="margin-bottom: 10px;"><button type="button" class="katahdin-webhook-button secondary copy-debug-btn" data-target="loading-debug-content" style="font-size: 12px; padding: 6px 12px;">📋 Copy Results</button></div>';
                html += '<pre id="loading-debug-content" style="background: #f1f1f1; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; max-height: 500px; overflow-y: auto;">' + JSON.stringify(debugInfo, null, 2) + '</pre>';
                html += '</div>';
                
                $('#test-output').html(html);
            });
            
            // Simple test
            $('.simple-test-btn').on('click', function() {
                var $btn = $(this);
                $('#test-results').show();
                $('#test-output').html('<p>Running simple test...</p>');
                
                $btn.prop('disabled', true).text('Testing...');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_simple_test',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                }, function(response) {
                    $btn.prop('disabled', false).text('🔧 Simple Test');
                    
                    var html = '<div class="simple-test-results">';
                    html += '<h4>🔧 Simple Test Results</h4>';
                    html += '<div style="margin-bottom: 10px;"><button type="button" class="katahdin-webhook-button secondary copy-debug-btn" data-target="simple-test-content" style="font-size: 12px; padding: 6px 12px;">📋 Copy Results</button></div>';
                    html += '<pre id="simple-test-content" style="background: #f1f1f1; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px;">' + JSON.stringify(response, null, 2) + '</pre>';
                    html += '</div>';
                    
                    $('#test-output').html(html);
                    
                }).fail(function(xhr, status, error) {
                    $btn.prop('disabled', false).text('🔧 Simple Test');
                    $('#test-output').html('<div style="color: red;"><strong>✗ Simple test failed</strong><br>Status: ' + status + '<br>Error: ' + error + '<br>Response: ' + xhr.responseText + '</div>');
                });
            });
            
            // Comprehensive Debug
            $('.comprehensive-debug-btn').on('click', function() {
                var $btn = $(this);
                $('#test-results').show();
                $('#test-output').html('<p>Running comprehensive debug check...</p>');
                
                $btn.prop('disabled', true).text('Running Debug...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'katahdin_ai_webhook_comprehensive_debug',
                        nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                    },
                    success: function(response) {
                        $btn.prop('disabled', false).text('🔍 Comprehensive Debug');
                        
                        if (response.success) {
                            var html = '<div class="comprehensive-debug-results">';
                            html += '<h4>🔍 Comprehensive Debug Report</h4>';
                            html += '<div style="margin-bottom: 15px;">';
                            html += '<strong>Plugin:</strong> ' + response.data.plugin_name + '<br>';
                            html += '<strong>Generated:</strong> ' + response.data.timestamp + '<br>';
                            html += '</div>';
                            
                            // Summary section
                            html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px;">';
                            html += '<h5>📊 Quick Summary</h5>';
                            
                            // API Key Status
                            var apiStatus = response.data.api_key_status;
                            html += '<p><strong>API Key:</strong> ';
                            if (apiStatus.api_key_option_exists && apiStatus.api_key_format_valid) {
                                html += '<span style="color: green;">✅ Configured & Valid</span><br>';
                                html += '<small style="font-family: monospace; background: #f0f0f0; padding: 2px 4px; border-radius: 3px;">' + apiStatus.api_key_full + '</small>';
                            } else if (apiStatus.api_key_option_exists) {
                                html += '<span style="color: orange;">⚠️ Configured but Invalid Format</span><br>';
                                html += '<small style="font-family: monospace; background: #f0f0f0; padding: 2px 4px; border-radius: 3px;">' + apiStatus.api_key_full + '</small>';
                            } else {
                                html += '<span style="color: red;">❌ Not Configured</span>';
                            }
                            html += '</p>';
                            
                            // Hub Integration
                            var hubStatus = response.data.katahdin_hub_integration;
                            html += '<p><strong>Katahdin Hub:</strong> ';
                            if (hubStatus.hub_function_exists && hubStatus.hub_instance_available) {
                                html += '<span style="color: green;">✅ Connected</span>';
                            } else {
                                html += '<span style="color: red;">❌ Not Available</span>';
                            }
                            html += '</p>';
                            
                            // REST Routes
                            var routesStatus = response.data.rest_api_routes;
                            html += '<p><strong>REST Routes:</strong> ';
                            if (routesStatus.plugin_routes_found > 0) {
                                html += '<span style="color: green;">✅ ' + routesStatus.plugin_routes_found + ' Routes Found</span>';
                            } else {
                                html += '<span style="color: red;">❌ No Routes Found</span>';
                            }
                            html += '</p>';
                            
                            html += '</div>';
                            
                            // Detailed sections
                            html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">';
                            
                            // API Connectivity
                            html += '<div style="border: 1px solid #ddd; padding: 15px; border-radius: 6px;">';
                            html += '<h6>🔌 API Connectivity</h6>';
                            html += '<pre style="font-size: 12px;">' + JSON.stringify(response.data.api_connectivity, null, 2) + '</pre>';
                            html += '</div>';
                            
                            // Plugin Loading
                            html += '<div style="border: 1px solid #ddd; padding: 15px; border-radius: 6px;">';
                            html += '<h6>📦 Plugin Loading</h6>';
                            html += '<pre style="font-size: 12px;">' + JSON.stringify(response.data.plugin_loading, null, 2) + '</pre>';
                            html += '</div>';
                            
                            // REST Routes
                            html += '<div style="border: 1px solid #ddd; padding: 15px; border-radius: 6px;">';
                            html += '<h6>🛣️ REST Routes</h6>';
                            html += '<pre style="font-size: 12px;">' + JSON.stringify(response.data.rest_api_routes, null, 2) + '</pre>';
                            html += '</div>';
                            
                            // Error Logs
                            html += '<div style="border: 1px solid #ddd; padding: 15px; border-radius: 6px;">';
                            html += '<h6>📋 Error Logs</h6>';
                            html += '<pre style="font-size: 12px;">' + JSON.stringify(response.data.error_logs, null, 2) + '</pre>';
                            html += '</div>';
                            
                            html += '</div>';
                            
                            // Full debug data (collapsible)
                            html += '<details style="margin-top: 20px;">';
                            html += '<summary style="cursor: pointer; font-weight: bold; padding: 10px; background: #f0f0f0; border-radius: 6px;">📄 Full Debug Data</summary>';
                            html += '<pre style="margin-top: 10px; font-size: 11px; max-height: 400px; overflow-y: auto;">' + JSON.stringify(response.data, null, 2) + '</pre>';
                            html += '</details>';
                            
                            html += '</div>';
                            
                            $('#test-output').html(html);
                        } else {
                            $('#test-output').html('<div style="color: red;"><strong>✗ Comprehensive debug failed:</strong> ' + (response.data.error || 'Unknown error') + '</div>');
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).text('🔍 Comprehensive Debug');
                        $('#test-output').html('<div style="color: red;"><strong>✗ Error occurred during comprehensive debug</strong></div>');
                    }
                });
            });
            
            // Copy debug results functionality
            $(document).on('click', '.copy-debug-btn', function() {
                var targetId = $(this).data('target');
                var content = $('#' + targetId).text();
                
                // Create a temporary textarea to copy the content
                var tempTextarea = document.createElement('textarea');
                tempTextarea.value = content;
                document.body.appendChild(tempTextarea);
                tempTextarea.select();
                document.execCommand('copy');
                document.body.removeChild(tempTextarea);
                
                // Show feedback
                var $btn = $(this);
                var originalText = $btn.text();
                $btn.text('✅ Copied!').css('background', '#28a745').css('color', 'white');
                
                setTimeout(function() {
                    $btn.text(originalText).css('background', '').css('color', '');
                }, 2000);
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for testing API key
     */
    public function ajax_test_api_key() {
        check_ajax_referer('katahdin_ai_webhook_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $webhook_handler = katahdin_ai_webhook()->webhook_handler;
        $result = $webhook_handler->test_api_key();
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
    }
    
    /**
     * AJAX handler for testing webhook
     */
    public function ajax_test_webhook() {
        check_ajax_referer('katahdin_ai_webhook_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $webhook_handler = katahdin_ai_webhook()->webhook_handler;
        $result = $webhook_handler->test_webhook(new WP_REST_Request());
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result->get_data());
        }
    }
    
    /**
     * AJAX handler for testing email
     */
    public function ajax_test_email() {
        check_ajax_referer('katahdin_ai_webhook_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $email_sender = katahdin_ai_webhook()->email_sender;
        $result = $email_sender->test_email();
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
    }
    
    /**
     * AJAX handler for regenerating webhook secret
     */
    public function ajax_regenerate_secret() {
        check_ajax_referer('katahdin_ai_webhook_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $new_secret = wp_generate_password(32, false);
        $updated = update_option('katahdin_ai_webhook_webhook_secret', $new_secret);
        
        if ($updated) {
            wp_send_json_success('Secret regenerated successfully');
        } else {
            wp_send_json_error('Failed to regenerate secret');
        }
    }
    
    /**
     * AJAX handler for debugging Katahdin AI Hub
     */
    public function ajax_debug_hub() {
        try {
            // Log the start of debug
            error_log('Katahdin AI Webhook Debug Hub: Starting debug process');
            
            check_ajax_referer('katahdin_ai_webhook_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            $debug_info = array();
        
        // Check if Katahdin AI Hub function exists
        $debug_info['katahdin_ai_hub_function_exists'] = function_exists('katahdin_ai_hub');
        
        if (function_exists('katahdin_ai_hub')) {
            try {
                $hub = katahdin_ai_hub();
                $debug_info['hub_instance'] = $hub ? 'Available' : 'Null';
                
                if ($hub) {
                    $debug_info['hub_class'] = get_class($hub);
                    $debug_info['api_manager_exists'] = isset($hub->api_manager) ? 'Yes' : 'No';
                    $debug_info['plugin_registry_exists'] = isset($hub->plugin_registry) ? 'Yes' : 'No';
                    
                    // Check API manager
                    if (isset($hub->api_manager)) {
                        $debug_info['api_manager_class'] = get_class($hub->api_manager);
                        
                        // Check API key
                        $api_key_option = get_option('katahdin_ai_hub_openai_key');
                        $debug_info['api_key_option_exists'] = !empty($api_key_option);
                        $debug_info['api_key_length'] = $api_key_option ? strlen($api_key_option) : 0;
                        $debug_info['api_key_preview'] = $api_key_option ? substr($api_key_option, 0, 8) . '...' : 'Not set';
                        
                        // Test connection
                        if (method_exists($hub->api_manager, 'test_connection')) {
                            $connection_test = $hub->api_manager->test_connection();
                            $debug_info['connection_test'] = is_wp_error($connection_test) ? $connection_test->get_error_message() : 'Success';
                        }
                    }
                    
                    // Check plugin registry
                    if (isset($hub->plugin_registry)) {
                        $debug_info['plugin_registry_class'] = get_class($hub->plugin_registry);
                        
                        // Check if webhook plugin is registered (check if method exists first)
                        if (method_exists($hub->plugin_registry, 'get_registered_plugins')) {
                            $registered_plugins = $hub->plugin_registry->get_registered_plugins();
                            $debug_info['registered_plugins'] = array_keys($registered_plugins);
                            $debug_info['webhook_plugin_registered'] = isset($registered_plugins['katahdin-ai-webhook']);
                            
                            if (isset($registered_plugins['katahdin-ai-webhook'])) {
                                $debug_info['webhook_plugin_config'] = $registered_plugins['katahdin-ai-webhook'];
                            }
                        } else {
                            $debug_info['plugin_registry_methods'] = get_class_methods($hub->plugin_registry);
                            $debug_info['webhook_plugin_registered'] = 'Method not available';
                        }
                    }
                    
                    // Test make_api_call method
                    if (method_exists($hub, 'make_api_call')) {
                        $debug_info['make_api_call_method_exists'] = true;
                        
                        // Try a test call
                        $test_result = $hub->make_api_call(
                            'katahdin-ai-webhook',
                            'chat/completions',
                            array('messages' => array(array('role' => 'user', 'content' => 'Test'))),
                            array('model' => 'gpt-3.5-turbo', 'max_tokens' => 10)
                        );
                        
                        $debug_info['test_api_call_result'] = is_wp_error($test_result) ? $test_result->get_error_message() : 'Success';
                    } else {
                        $debug_info['make_api_call_method_exists'] = false;
                    }
                }
            } catch (Exception $e) {
                $debug_info['hub_error'] = $e->getMessage();
            }
        }
        
        // Check webhook plugin
        $debug_info['webhook_plugin_function_exists'] = function_exists('katahdin_ai_webhook');
        
        if (function_exists('katahdin_ai_webhook')) {
            try {
                $webhook = katahdin_ai_webhook();
                $debug_info['webhook_instance'] = $webhook ? 'Available' : 'Null';
                
                if ($webhook) {
                    $debug_info['webhook_class'] = get_class($webhook);
                    $debug_info['webhook_handler_exists'] = isset($webhook->webhook_handler) ? 'Yes' : 'No';
                }
            } catch (Exception $e) {
                $debug_info['webhook_error'] = $e->getMessage();
            }
        }
        
        wp_send_json_success($debug_info);
        
        } catch (Exception $e) {
            error_log('Katahdin AI Webhook Debug Hub Error: ' . $e->getMessage());
            error_log('Katahdin AI Webhook Debug Hub Stack Trace: ' . $e->getTraceAsString());
            wp_send_json_error(array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
        } catch (Error $e) {
            error_log('Katahdin AI Webhook Debug Hub Fatal Error: ' . $e->getMessage());
            error_log('Katahdin AI Webhook Debug Hub Stack Trace: ' . $e->getTraceAsString());
            wp_send_json_error(array(
                'error' => 'Fatal Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    
    /**
     * AJAX handler for debugging API call
     */
    public function ajax_debug_api_call() {
        try {
            error_log('Katahdin AI Webhook Debug API Call: Starting debug process');
            
            check_ajax_referer('katahdin_ai_webhook_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            $debug_info = array();
        
        if (!function_exists('katahdin_ai_hub')) {
            wp_send_json_error('Katahdin AI Hub function not available');
        }
        
        $hub = katahdin_ai_hub();
        if (!$hub) {
            wp_send_json_error('Katahdin AI Hub instance not available');
        }
        
        // Test different API call methods
        $test_data = array(
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => 'This is a test message for debugging. Please respond with "Test successful".'
                )
            )
        );
        
        $test_options = array(
            'model' => 'gpt-3.5-turbo',
            'max_tokens' => 50,
            'temperature' => 0.7
        );
        
        // Test 1: Direct make_api_call
        try {
            $result1 = $hub->make_api_call(
                'katahdin-ai-webhook',
                'chat/completions',
                $test_data,
                $test_options
            );
            
            $debug_info['direct_api_call'] = array(
                'success' => !is_wp_error($result1),
                'result' => is_wp_error($result1) ? $result1->get_error_message() : 'Success',
                'data' => $result1
            );
        } catch (Exception $e) {
            $debug_info['direct_api_call'] = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        
        // Test 2: Through webhook handler
        if (function_exists('katahdin_ai_webhook')) {
            try {
                $webhook = katahdin_ai_webhook();
                if ($webhook && isset($webhook->webhook_handler)) {
                    $test_prompt = 'This is a test prompt for debugging. Please respond with "Test successful".';
                    
                    // Check if method is accessible
                    if (method_exists($webhook->webhook_handler, 'call_ai_api')) {
                        $reflection = new ReflectionMethod($webhook->webhook_handler, 'call_ai_api');
                        if ($reflection->isPublic()) {
                            $result2 = $webhook->webhook_handler->call_ai_api($test_prompt);
                            
                            $debug_info['webhook_handler_call'] = array(
                                'success' => !is_wp_error($result2),
                                'result' => is_wp_error($result2) ? $result2->get_error_message() : 'Success',
                                'data' => $result2
                            );
                        } else {
                            $debug_info['webhook_handler_call'] = array(
                                'success' => false,
                                'error' => 'call_ai_api method is not public'
                            );
                        }
                    } else {
                        $debug_info['webhook_handler_call'] = array(
                            'success' => false,
                            'error' => 'call_ai_api method does not exist'
                        );
                    }
                } else {
                    $debug_info['webhook_handler_call'] = array(
                        'success' => false,
                        'error' => 'Webhook handler not available'
                    );
                }
            } catch (Exception $e) {
                $debug_info['webhook_handler_call'] = array(
                    'success' => false,
                    'error' => $e->getMessage()
                );
            }
        }
        
        wp_send_json_success($debug_info);
        
        } catch (Exception $e) {
            error_log('Katahdin AI Webhook Debug API Call Error: ' . $e->getMessage());
            error_log('Katahdin AI Webhook Debug API Call Stack Trace: ' . $e->getTraceAsString());
            wp_send_json_error(array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
        } catch (Error $e) {
            error_log('Katahdin AI Webhook Debug API Call Fatal Error: ' . $e->getMessage());
            error_log('Katahdin AI Webhook Debug API Call Stack Trace: ' . $e->getTraceAsString());
            wp_send_json_error(array(
                'error' => 'Fatal Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    
    /**
     * AJAX handler for debugging REST routes
     */
    public function ajax_debug_rest_routes() {
        check_ajax_referer('katahdin_ai_webhook_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $debug_info = array();
        
        // Check if REST API is available
        $debug_info['rest_api_available'] = rest_url() ? true : false;
        $debug_info['rest_url'] = rest_url();
        
        // Check specific webhook routes
        $webhook_routes = array(
            'webhook' => rest_url('katahdin-ai-webhook/v1/webhook'),
            'status' => rest_url('katahdin-ai-webhook/v1/status'),
            'debug' => rest_url('katahdin-ai-webhook/v1/debug'),
            'logs' => rest_url('katahdin-ai-webhook/v1/logs'),
            'logs_stats' => rest_url('katahdin-ai-webhook/v1/logs/stats')
        );
        
        $debug_info['webhook_routes'] = $webhook_routes;
        
        // Test route accessibility
        foreach ($webhook_routes as $name => $url) {
            $response = wp_remote_get($url, array(
                'timeout' => 10,
                'headers' => array(
                    'X-WP-Nonce' => wp_create_nonce('wp_rest')
                )
            ));
            
            if (is_wp_error($response)) {
                $debug_info['route_tests'][$name] = array(
                    'accessible' => false,
                    'error' => $response->get_error_message()
                );
            } else {
                $debug_info['route_tests'][$name] = array(
                    'accessible' => true,
                    'status_code' => wp_remote_retrieve_response_code($response),
                    'response' => wp_remote_retrieve_body($response)
                );
            }
        }
        
        // Check registered routes
        global $wp_rest_server;
        if ($wp_rest_server) {
            $routes = $wp_rest_server->get_routes();
            $webhook_routes_found = array();
            
            foreach ($routes as $route => $handlers) {
                if (strpos($route, 'katahdin-ai-webhook') !== false) {
                    $webhook_routes_found[$route] = array_keys($handlers);
                }
            }
            
            $debug_info['registered_webhook_routes'] = $webhook_routes_found;
        }
        
        wp_send_json_success($debug_info);
    }
    
    /**
     * Logs page
     */
    public function logs_page() {
        ?>
        <div class="wrap">
            <h1>Webhook Logs</h1>
            
            <div class="katahdin-webhook-logs-container">
                <!-- Log Statistics -->
                <div class="logs-stats">
                    <h2>Statistics</h2>
                    <div id="logs-stats-content">
                        <p>Loading statistics...</p>
                    </div>
                </div>
                
                <!-- Log Filters -->
                <div class="logs-filters">
                    <h2>Filters</h2>
                    <select id="status-filter">
                        <option value="">All Statuses</option>
                        <option value="received">Received</option>
                        <option value="success">Success</option>
                        <option value="error">Error</option>
                        <option value="pending">Pending</option>
                    </select>
                    <button id="refresh-logs" class="button">Refresh</button>
                    <button id="cleanup-logs" class="button button-secondary">Cleanup Old Logs</button>
                </div>
                
                <!-- Logs Table -->
                <div class="logs-table">
                    <h2>Recent Logs</h2>
                    <div id="logs-content">
                        <p>Loading logs...</p>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="logs-pagination">
                    <button id="load-more-logs" class="button" style="display: none;">Load More</button>
                </div>
            </div>
        </div>
        
        <style>
        .katahdin-webhook-logs-container {
            max-width: 1200px;
        }
        
        .logs-stats, .logs-filters, .logs-table {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .logs-stats h2, .logs-filters h2, .logs-table h2 {
            margin-top: 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .stat-card {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 4px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .logs-filters {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logs-filters select {
            min-width: 150px;
        }
        
        .logs-table table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .logs-table th,
        .logs-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .logs-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-received {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .log-details {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .log-actions {
            display: flex;
            gap: 5px;
        }
        
        .log-actions button {
            padding: 4px 8px;
            font-size: 12px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            let currentOffset = 0;
            let currentStatus = '';
            const limit = 20;
            
            // Load initial data
            loadStats();
            loadLogs();
            
            // Refresh button
            $('#refresh-logs').on('click', function() {
                currentOffset = 0;
                loadLogs();
            });
            
            // Status filter
            $('#status-filter').on('change', function() {
                currentStatus = $(this).val();
                currentOffset = 0;
                loadLogs();
            });
            
            // Load more button
            $('#load-more-logs').on('click', function() {
                currentOffset += limit;
                loadLogs(true);
            });
            
            // Cleanup logs
            $('#cleanup-logs').on('click', function() {
                if (confirm('Are you sure you want to cleanup old logs? This will delete logs older than 30 days.')) {
                    cleanupLogs();
                }
            });
            
            function loadStats() {
                $.get('<?php echo rest_url('katahdin-ai-webhook/v1/logs/stats'); ?>')
                    .done(function(response) {
                        if (response.success) {
                            displayStats(response.stats);
                        }
                    })
                    .fail(function() {
                        $('#logs-stats-content').html('<p>Error loading statistics</p>');
                    });
            }
            
            function loadLogs(append = false) {
                const url = '<?php echo rest_url('katahdin-ai-webhook/v1/logs'); ?>' + 
                    '?limit=' + limit + 
                    '&offset=' + currentOffset + 
                    (currentStatus ? '&status=' + currentStatus : '');
                
                $.get(url)
                    .done(function(response) {
                        if (response.success) {
                            displayLogs(response.logs, append);
                            $('#load-more-logs').toggle(response.pagination.has_more);
                        }
                    })
                    .fail(function() {
                        $('#logs-content').html('<p>Error loading logs</p>');
                    });
            }
            
            function displayStats(stats) {
                let html = '<div class="stats-grid">';
                html += '<div class="stat-card"><div class="stat-number">' + stats.total_logs + '</div><div class="stat-label">Total Logs</div></div>';
                html += '<div class="stat-card"><div class="stat-number">' + stats.recent_24h + '</div><div class="stat-label">Last 24 Hours</div></div>';
                html += '<div class="stat-card"><div class="stat-number">' + stats.recent_7d + '</div><div class="stat-label">Last 7 Days</div></div>';
                
                if (stats.by_status) {
                    for (const [status, count] of Object.entries(stats.by_status)) {
                        html += '<div class="stat-card"><div class="stat-number">' + count + '</div><div class="stat-label">' + status.charAt(0).toUpperCase() + status.slice(1) + '</div></div>';
                    }
                }
                
                html += '</div>';
                $('#logs-stats-content').html(html);
            }
            
            function displayLogs(logs, append) {
                if (!append) {
                    $('#logs-content').html('');
                }
                
                if (logs.length === 0) {
                    $('#logs-content').html('<p>No logs found</p>');
                    return;
                }
                
                let html = '<table><thead><tr><th>ID</th><th>Timestamp</th><th>Status</th><th>Method</th><th>Processing Time</th><th>Details</th><th>Actions</th></tr></thead><tbody>';
                
                logs.forEach(function(log) {
                    html += '<tr>';
                    html += '<td>' + log.id + '</td>';
                    html += '<td>' + new Date(log.timestamp).toLocaleString() + '</td>';
                    html += '<td><span class="status-badge status-' + log.status + '">' + log.status + '</span></td>';
                    html += '<td>' + log.method + '</td>';
                    html += '<td>' + (log.processing_time_ms ? log.processing_time_ms + 'ms' : '-') + '</td>';
                    html += '<td class="log-details">' + (log.error_message || 'Success') + '</td>';
                    html += '<td class="log-actions">';
                    html += '<button class="button button-small view-log" data-id="' + log.id + '">View</button>';
                    html += '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                
                if (append) {
                    $('#logs-content table tbody').append($(html).find('tbody').html());
                } else {
                    $('#logs-content').html(html);
                }
                
                // Bind view log buttons
                $('.view-log').on('click', function() {
                    const logId = $(this).data('id');
                    viewLogDetails(logId);
                });
            }
            
            function viewLogDetails(logId) {
                $.get('<?php echo rest_url('katahdin-ai-webhook/v1/logs/'); ?>' + logId)
                    .done(function(response) {
                        if (response.success) {
                            showLogModal(response.log);
                        }
                    });
            }
            
            function showLogModal(log) {
                let html = '<div id="log-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">';
                html += '<div style="background: white; padding: 20px; border-radius: 5px; max-width: 80%; max-height: 80%; overflow: auto;">';
                html += '<h3>Log Details - ID: ' + log.id + '</h3>';
                html += '<p><strong>Webhook ID:</strong> ' + log.webhook_id + '</p>';
                html += '<p><strong>Timestamp:</strong> ' + new Date(log.timestamp).toLocaleString() + '</p>';
                html += '<p><strong>Status:</strong> <span class="status-badge status-' + log.status + '">' + log.status + '</span></p>';
                html += '<p><strong>Method:</strong> ' + log.method + '</p>';
                html += '<p><strong>URL:</strong> ' + log.url + '</p>';
                html += '<p><strong>IP Address:</strong> ' + log.ip_address + '</p>';
                html += '<p><strong>Processing Time:</strong> ' + (log.processing_time_ms ? log.processing_time_ms + 'ms' : 'N/A') + '</p>';
                
                if (log.error_message) {
                    html += '<p><strong>Error:</strong> ' + log.error_message + '</p>';
                }
                
                if (log.ai_response) {
                    html += '<p><strong>AI Response:</strong></p>';
                    html += '<pre style="background: #f5f5f5; padding: 10px; border-radius: 3px; max-height: 200px; overflow: auto;">' + log.ai_response + '</pre>';
                }
                
                if (log.body) {
                    html += '<p><strong>Request Body:</strong></p>';
                    html += '<pre style="background: #f5f5f5; padding: 10px; border-radius: 3px; max-height: 200px; overflow: auto;">' + log.body + '</pre>';
                }
                
                html += '<button onclick="jQuery(\'#log-modal\').remove()" class="button" style="margin-top: 15px;">Close</button>';
                html += '</div></div>';
                
                $('body').append(html);
            }
            
            function cleanupLogs() {
                $.post('<?php echo rest_url('katahdin-ai-webhook/v1/logs/cleanup'); ?>', {
                    retention_days: 30
                })
                .done(function(response) {
                    if (response.success) {
                        alert('Cleaned up ' + response.deleted_count + ' old log entries');
                        loadStats();
                        loadLogs();
                    } else {
                        alert('Error cleaning up logs');
                    }
                })
                .fail(function() {
                    alert('Error cleaning up logs');
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * Add custom admin styles
     */
    public function add_admin_styles() {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'katahdin-ai-webhook') === false) {
            return;
        }
        ?>
        <style>
        /* Custom icon for the admin menu */
        #adminmenu .toplevel_page_katahdin-ai-webhook .wp-menu-image:before {
            content: "\f237" !important; /* WordPress webhook icon */
            font-family: dashicons !important;
        }
        
        /* Custom styling for the admin pages */
        .katahdin-webhook-admin {
            max-width: 1200px;
        }
        
        .katahdin-webhook-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .katahdin-webhook-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
        }
        
        .katahdin-webhook-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .katahdin-webhook-card {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .katahdin-webhook-card h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            font-size: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        .katahdin-webhook-form-table th {
            width: 200px;
            padding: 15px 10px 15px 0;
            vertical-align: top;
        }
        
        .katahdin-webhook-form-table td {
            padding: 15px 0;
        }
        
        .katahdin-webhook-form-table input[type="text"],
        .katahdin-webhook-form-table input[type="email"],
        .katahdin-webhook-form-table input[type="password"],
        .katahdin-webhook-form-table textarea,
        .katahdin-webhook-form-table select {
            width: 100%;
            max-width: 500px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .katahdin-webhook-form-table textarea {
            height: 120px;
            resize: vertical;
        }
        
        .katahdin-webhook-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .katahdin-webhook-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .katahdin-webhook-button.secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }
        
        .katahdin-webhook-button.secondary:hover {
            background: #e9ecef;
            color: #495057;
        }
        
        .katahdin-webhook-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .katahdin-webhook-status.success {
            background: #d4edda;
            color: #155724;
        }
        
        .katahdin-webhook-status.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .katahdin-webhook-status.warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .katahdin-webhook-test-results {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            margin-top: 20px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .katahdin-webhook-test-results.success {
            background: #d4edda;
            border-color: #c3e6cb;
        }
        
        .katahdin-webhook-test-results.error {
            background: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .katahdin-webhook-test-results.loading {
            background: #d1ecf1;
            border-color: #bee5eb;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .katahdin-webhook-form-table th,
            .katahdin-webhook-form-table td {
                display: block;
                width: 100%;
                padding: 10px 0;
            }
            
            .katahdin-webhook-form-table th {
                font-weight: bold;
                margin-bottom: 5px;
            }
        }
        </style>
        <?php
    }
    
    /**
     * AJAX handler for simple test
     */
    public function ajax_simple_test() {
        try {
            error_log('Katahdin AI Webhook Simple Test: Starting');
            
            check_ajax_referer('katahdin_ai_webhook_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            $test_results = array(
                'timestamp' => current_time('Y-m-d H:i:s'),
                'wordpress_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
                'ajax_working' => true,
                'nonce_valid' => true,
                'user_can_manage_options' => true,
                'katahdin_ai_hub_function_exists' => function_exists('katahdin_ai_hub'),
                'katahdin_ai_webhook_function_exists' => function_exists('katahdin_ai_webhook'),
                'test_message' => 'Simple test completed successfully!'
            );
            
            // Try to get hub instance
            if (function_exists('katahdin_ai_hub')) {
                try {
                    $hub = katahdin_ai_hub();
                    $test_results['hub_instance_available'] = $hub ? true : false;
                    $test_results['hub_class'] = $hub ? get_class($hub) : 'Not available';
                } catch (Exception $e) {
                    $test_results['hub_error'] = $e->getMessage();
                }
            }
            
            // Try to get webhook instance
            if (function_exists('katahdin_ai_webhook')) {
                try {
                    $webhook = katahdin_ai_webhook();
                    $test_results['webhook_instance_available'] = $webhook ? true : false;
                    $test_results['webhook_class'] = $webhook ? get_class($webhook) : 'Not available';
                } catch (Exception $e) {
                    $test_results['webhook_error'] = $e->getMessage();
                }
            }
            
            error_log('Katahdin AI Webhook Simple Test: Completed successfully');
            wp_send_json_success($test_results);
            
        } catch (Exception $e) {
            error_log('Katahdin AI Webhook Simple Test Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ));
        } catch (Error $e) {
            error_log('Katahdin AI Webhook Simple Test Fatal Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'error' => 'Fatal Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ));
        }
    }
    
    /**
     * AJAX handler for comprehensive debug
     */
    public function ajax_comprehensive_debug() {
        try {
            error_log('Katahdin AI Webhook Comprehensive Debug: Starting');
            check_ajax_referer('katahdin_ai_webhook_nonce', 'nonce');
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            $webhook = katahdin_ai_webhook();
            if (!$webhook || !$webhook->debugger) {
                wp_send_json_error('Debugger not available');
            }
            
            $debug_results = $webhook->debugger->run_comprehensive_debug();
            
            error_log('Katahdin AI Webhook Comprehensive Debug: Completed successfully');
            wp_send_json_success($debug_results);
            
        } catch (Exception $e) {
            error_log('Katahdin AI Webhook Comprehensive Debug Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ));
        } catch (Error $e) {
            error_log('Katahdin AI Webhook Comprehensive Debug Fatal Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'error' => 'Fatal Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ));
        }
    }
}
