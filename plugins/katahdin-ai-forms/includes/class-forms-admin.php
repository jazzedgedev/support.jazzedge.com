<?php
/**
 * Admin Interface for Katahdin AI Forms
 * Provides admin settings and management interface
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Forms_Admin {
    
    /**
     * Initialize admin interface
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_katahdin_ai_forms_test_api_key', array($this, 'ajax_test_api_key'));
        add_action('wp_ajax_katahdin_ai_forms_test_forms', array($this, 'ajax_test_forms'));
        add_action('wp_ajax_katahdin_ai_forms_test_email', array($this, 'ajax_test_email'));
        add_action('wp_ajax_katahdin_ai_forms_regenerate_secret', array($this, 'ajax_regenerate_secret'));
        add_action('wp_ajax_katahdin_ai_forms_comprehensive_debug', array($this, 'ajax_comprehensive_debug'));
        add_action('wp_ajax_katahdin_ai_forms_get_logs', array($this, 'ajax_get_logs'));
        add_action('wp_ajax_katahdin_ai_forms_get_log_stats', array($this, 'ajax_get_log_stats'));
        add_action('wp_ajax_katahdin_ai_forms_cleanup_logs', array($this, 'ajax_cleanup_logs'));
        add_action('wp_ajax_katahdin_ai_forms_test_log', array($this, 'ajax_test_log'));
        add_action('wp_ajax_katahdin_ai_forms_test_ajax', array($this, 'ajax_test_ajax'));
        add_action('wp_ajax_katahdin_ai_forms_create_table', array($this, 'ajax_create_table'));
        add_action('wp_ajax_katahdin_ai_forms_debug_logs', array($this, 'ajax_debug_logs'));
        add_action('wp_ajax_katahdin_ai_forms_get_log_details', array($this, 'ajax_get_log_details'));
        add_action('wp_ajax_katahdin_ai_forms_clear_all_logs', array($this, 'ajax_clear_all_logs'));
        add_action('wp_ajax_katahdin_ai_forms_delete_log', array($this, 'ajax_delete_log'));
        add_action('wp_ajax_katahdin_ai_forms_add_prompt', array($this, 'ajax_add_prompt'));
        add_action('wp_ajax_katahdin_ai_forms_update_prompt', array($this, 'ajax_update_prompt'));
        add_action('wp_ajax_katahdin_ai_forms_delete_prompt', array($this, 'ajax_delete_prompt'));
        add_action('wp_ajax_katahdin_ai_forms_toggle_prompt', array($this, 'ajax_toggle_prompt'));
        add_action('wp_ajax_katahdin_ai_forms_get_prompts', array($this, 'ajax_get_prompts'));
        add_action('wp_ajax_katahdin_ai_forms_get_prompt_by_id', array($this, 'ajax_get_prompt_by_id'));
        add_action('admin_head', array($this, 'add_admin_styles'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            'Katahdin AI Forms',
            'AI Forms',
            'manage_options',
            'katahdin-ai-forms',
            array($this, 'admin_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>'),
            30
        );
        
        // Submenu for main settings
        add_submenu_page(
            'katahdin-ai-forms',
            'Settings',
            'Settings',
            'manage_options',
            'katahdin-ai-forms',
            array($this, 'admin_page')
        );
        
        // Submenu for logs
        add_submenu_page(
            'katahdin-ai-forms',
            'Forms Logs',
            'Logs',
            'manage_options',
            'katahdin-ai-forms-logs',
            array($this, 'logs_page')
        );
        
        // Submenu for form prompts
        add_submenu_page(
            'katahdin-ai-forms',
            'Form Prompts',
            'Form Prompts',
            'manage_options',
            'katahdin-ai-forms-prompts',
            array($this, 'prompts_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('katahdin_ai_forms_settings', 'katahdin_ai_forms_enabled');
        register_setting('katahdin_ai_forms_settings', 'katahdin_ai_forms_model');
        register_setting('katahdin_ai_forms_settings', 'katahdin_ai_forms_max_tokens');
        register_setting('katahdin_ai_forms_settings', 'katahdin_ai_forms_temperature');
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        $forms_url = katahdin_ai_forms()->get_forms_url();
        $forms_secret = katahdin_ai_forms()->get_forms_secret();
        $katahdin_status = katahdin_ai_forms()->forms_handler->check_katahdin_hub_status();
        
        ?>
        <div class="wrap katahdin-forms-admin">
            <div class="katahdin-forms-header">
                <h1>🤖 Katahdin AI Forms</h1>
                <p>Configure and manage your AI-powered forms for processing and analysis</p>
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
            
            <div class="katahdin-ai-forms-admin">
                <!-- Status Cards -->
                <div class="katahdin-forms-card">
                    <h2>📊 System Status</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                            <h3 style="margin: 0 0 10px 0; color: #333;">📡 Forms Status</h3>
                            <span class="katahdin-forms-status <?php echo get_option('katahdin_ai_forms_enabled', true) ? 'success' : 'error'; ?>">
                                <?php echo get_option('katahdin_ai_forms_enabled', true) ? 'Enabled' : 'Disabled'; ?>
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
                        <p class="description">AI prompts are now configured per form in the <a href="<?php echo admin_url('admin.php?page=katahdin-ai-forms-prompts'); ?>">Form Prompts</a> section.</p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">AI Model</th>
                                <td>
                                    <select name="katahdin_ai_forms_model">
                                        <option value="gpt-3.5-turbo" <?php selected(get_option('katahdin_ai_forms_model', 'gpt-3.5-turbo'), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                                        <option value="gpt-4" <?php selected(get_option('katahdin_ai_forms_model', 'gpt-3.5-turbo'), 'gpt-4'); ?>>GPT-4</option>
                                        <option value="gpt-4-turbo" <?php selected(get_option('katahdin_ai_forms_model', 'gpt-3.5-turbo'), 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Max Tokens</th>
                                <td>
                                    <input type="number" name="katahdin_ai_forms_max_tokens" value="<?php echo esc_attr(get_option('katahdin_ai_forms_max_tokens', 1000)); ?>" min="1" max="4000" />
                                    <p class="description">Maximum number of tokens for AI response (1-4000).</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Temperature</th>
                                <td>
                                    <input type="number" name="katahdin_ai_forms_temperature" value="<?php echo esc_attr(get_option('katahdin_ai_forms_temperature', 0.7)); ?>" min="0" max="2" step="0.1" />
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
                
                <!-- Core Katahdin AI Hub Testing -->
                <div class="katahdin-webhook-card">
                    <h2>🔧 Katahdin AI Hub Diagnostics</h2>
                    <p>Core system checks for Katahdin AI Hub integration. These tests are available across all Katahdin AI plugins.</p>
                    
                    <div style="margin: 20px 0;">
                        <button type="button" class="katahdin-webhook-button test-api-key-btn" style="margin-right: 10px;">🔑 Test API Key</button>
                        <button type="button" class="katahdin-webhook-button secondary check-status-btn" style="margin-right: 10px;">Check Status</button>
                        <button type="button" class="katahdin-webhook-button primary comprehensive-debug-btn">🔍 Comprehensive Debug</button>
                    </div>
                </div>

                <!-- Plugin-Specific Testing -->
                <div class="katahdin-webhook-card">
                    <h2>🧪 Webhook Plugin Testing</h2>
                    <p>Test the webhook and email functionality specific to this plugin.</p>
                    
                    <div style="margin: 20px 0;">
                        <button type="button" class="katahdin-webhook-button test-webhook-btn" style="margin-right: 10px;">Test Webhook</button>
                        <button type="button" class="katahdin-webhook-button secondary test-email-btn" style="margin-right: 10px;">Test Email</button>
                        <button type="button" class="katahdin-webhook-button secondary debug-info-btn" style="margin-right: 10px;">Debug Info</button>
                    </div>
                </div>
                    
                    <div id="test-results" style="display: none;">
                        <h3>Test Results</h3>
                        <div style="margin-bottom: 10px;"><button type="button" class="katahdin-webhook-button secondary copy-debug-btn" data-target="test-output" style="font-size: 12px; padding: 6px 12px;">📋 Copy Results</button></div>
                        <div id="test-output" class="katahdin-webhook-test-results"></div>
                    </div>
                </div>
                
                
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
                        nonce: '<?php echo wp_create_nonce('katahdin_ai_forms_nonce'); ?>'
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
                        nonce: '<?php echo wp_create_nonce('katahdin_ai_forms_nonce'); ?>'
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
     * AJAX handler for getting logs
     */
    public function ajax_get_logs() {
        try {
            check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            $limit = intval($_POST['limit'] ?? 50);
            $offset = intval($_POST['offset'] ?? 0);
            $status = sanitize_text_field($_POST['status'] ?? '');
            
            if (!function_exists('katahdin_ai_webhook')) {
                wp_send_json_error('Katahdin AI Webhook function not available');
                return;
            }
            
            $webhook_instance = katahdin_ai_webhook();
            if (!$webhook_instance) {
                wp_send_json_error('Katahdin AI Webhook instance not available');
                return;
            }
            
            if (!isset($webhook_instance->webhook_handler)) {
                wp_send_json_error('Webhook handler not available');
                return;
            }
            
            $webhook_handler = $webhook_instance->webhook_handler;
            $logger = $webhook_handler->get_logger();
            
            if (!$logger) {
                wp_send_json_error('Logger not available');
                return;
            }
            
            $logs = $logger->get_logs($limit, $offset, $status);
            
            wp_send_json_success($logs);
            
        } catch (Exception $e) {
            wp_send_json_error('Error getting logs: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for getting log statistics
     */
    public function ajax_get_log_stats() {
        try {
            check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            if (!function_exists('katahdin_ai_webhook')) {
                wp_send_json_error('Katahdin AI Webhook function not available');
                return;
            }
            
            $webhook_instance = katahdin_ai_webhook();
            if (!$webhook_instance) {
                wp_send_json_error('Katahdin AI Webhook instance not available');
                return;
            }
            
            if (!isset($webhook_instance->webhook_handler)) {
                wp_send_json_error('Webhook handler not available');
                return;
            }
            
            $webhook_handler = $webhook_instance->webhook_handler;
            $logger = $webhook_handler->get_logger();
            
            if (!$logger) {
                wp_send_json_error('Logger not available');
                return;
            }
            
            $stats = $logger->get_log_stats();
            
            wp_send_json_success($stats);
            
        } catch (Exception $e) {
            wp_send_json_error('Error getting log stats: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for cleaning up logs
     */
    public function ajax_cleanup_logs() {
        try {
            check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            $retention_days = intval($_POST['retention_days'] ?? 30);
            
            // Debug: Log the AJAX call
            error_log("Katahdin AI Webhook - ajax_cleanup_logs called with retention_days: $retention_days");
            
            $webhook_handler = katahdin_ai_webhook()->webhook_handler;
            $result = $webhook_handler->get_logger()->cleanup_logs($retention_days);
            
            // Debug: Log the result
            error_log("Katahdin AI Webhook - cleanup result: " . print_r($result, true));
            
            wp_send_json_success($result);
            
        } catch (Exception $e) {
            error_log("Katahdin AI Webhook - cleanup error: " . $e->getMessage());
            wp_send_json_error('Error cleaning up logs: ' . $e->getMessage());
        }
    }
    
    /**
     * Get logs debug info directly (no AJAX needed) - SAFE VERSION
     */
    private function get_logs_debug_info() {
        try {
            $debug_info = array(
                'timestamp' => current_time('mysql'),
                'wordpress_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
                'plugin_status' => array(),
                'database_status' => array(),
                'ajax_status' => array(),
                'class_status' => array(),
                'file_status' => array(),
                'error_logs' => array()
            );
            
            // 1. Plugin Status - SAFE CHECKS
            $debug_info['plugin_status'] = array(
                'plugin_active' => function_exists('is_plugin_active') ? is_plugin_active('katahdin-ai-webhook/katahdin-ai-webhook.php') : 'Unknown',
                'plugin_file_exists' => file_exists(WP_PLUGIN_DIR . '/katahdin-ai-webhook/katahdin-ai-webhook.php'),
                'function_exists_katahdin_ai_webhook' => function_exists('katahdin_ai_webhook'),
                'class_exists_katahdin_ai_webhook' => class_exists('Katahdin_AI_Webhook'),
                'class_exists_katahdin_ai_webhook_admin' => class_exists('Katahdin_AI_Webhook_Admin'),
                'class_exists_katahdin_ai_webhook_logger' => class_exists('Katahdin_AI_Webhook_Logger'),
                'class_exists_katahdin_ai_webhook_handler' => class_exists('Katahdin_AI_Webhook_Handler')
            );
            
            // 2. Database Status - SAFE CHECKS
            global $wpdb;
            $table_name = $wpdb->prefix . 'katahdin_ai_webhook_logs';
            
            $debug_info['database_status'] = array(
                'table_name' => $table_name,
                'database_connection' => !empty($wpdb->dbh),
                'wpdb_last_error' => $wpdb->last_error ?: 'None',
                'wpdb_last_query' => $wpdb->last_query ?: 'None'
            );
            
            // Safe table check
            try {
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
                $debug_info['database_status']['table_exists'] = ($table_exists == $table_name);
                
                if ($debug_info['database_status']['table_exists']) {
                    $columns = $wpdb->get_results("DESCRIBE $table_name");
                    $debug_info['database_status']['table_structure'] = $columns;
                    $debug_info['database_status']['row_count'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                }
            } catch (Exception $e) {
                $debug_info['database_status']['table_check_error'] = $e->getMessage();
            }
            
            // 3. AJAX Status - SAFE CHECKS
            $debug_info['ajax_status'] = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'user_can_manage_options' => current_user_can('manage_options'),
                'current_user_id' => get_current_user_id(),
                'is_admin' => is_admin()
            );
            
            // Test AJAX handlers - SAFE CHECKS
            $ajax_handlers = array(
                'katahdin_ai_webhook_get_log_stats',
                'katahdin_ai_webhook_get_logs',
                'katahdin_ai_webhook_test_log',
                'katahdin_ai_webhook_create_table',
                'katahdin_ai_webhook_debug_logs'
            );
            
            foreach ($ajax_handlers as $handler) {
                $debug_info['ajax_status']['handlers'][$handler] = has_action("wp_ajax_$handler");
            }
            
            // 4. Class Status - SAFE CHECKS
            if (function_exists('katahdin_ai_webhook')) {
                try {
                    $webhook_instance = katahdin_ai_webhook();
                    $debug_info['class_status'] = array(
                        'webhook_instance_available' => !empty($webhook_instance),
                        'webhook_instance_class' => $webhook_instance ? get_class($webhook_instance) : 'N/A',
                        'admin_instance_available' => !empty($webhook_instance->admin),
                        'webhook_handler_available' => !empty($webhook_instance->webhook_handler)
                    );
                    
                    if (!empty($webhook_instance->webhook_handler) && method_exists($webhook_instance->webhook_handler, 'get_logger')) {
                        $logger = $webhook_instance->webhook_handler->get_logger();
                        $debug_info['class_status']['logger_instance_available'] = !empty($logger);
                        $debug_info['class_status']['logger_class'] = get_class($logger);
                    }
                } catch (Exception $e) {
                    $debug_info['class_status']['error'] = $e->getMessage();
                }
            }
            
            // 5. File Status - SAFE CHECKS
            $plugin_path = WP_PLUGIN_DIR . '/katahdin-ai-webhook/';
            $debug_info['file_status'] = array(
                'plugin_directory_exists' => is_dir($plugin_path),
                'plugin_directory_readable' => is_readable($plugin_path),
                'includes_directory_exists' => is_dir($plugin_path . 'includes/'),
                'admin_file_exists' => file_exists($plugin_path . 'includes/class-admin.php'),
                'logger_file_exists' => file_exists($plugin_path . 'includes/class-webhook-logger.php'),
                'handler_file_exists' => file_exists($plugin_path . 'includes/class-webhook-handler.php')
            );
            
            // 6. Error Logs - SAFE CHECKS
            $debug_info['error_logs'] = array(
                'wp_debug' => defined('WP_DEBUG') ? WP_DEBUG : false,
                'wp_debug_log' => defined('WP_DEBUG_LOG') ? WP_DEBUG_LOG : false,
                'error_log_location' => ini_get('error_log') ?: 'Not set'
            );
            
            // 7. Test Logger Methods - SAFE CHECKS
            if (class_exists('Katahdin_AI_Webhook_Logger')) {
                try {
                    $logger = new Katahdin_AI_Webhook_Logger();
                    $debug_info['logger_tests'] = array(
                        'logger_instantiated' => !empty($logger),
                        'create_table_method_exists' => method_exists($logger, 'create_table'),
                        'get_log_stats_method_exists' => method_exists($logger, 'get_log_stats'),
                        'get_logs_method_exists' => method_exists($logger, 'get_logs'),
                        'cleanup_logs_method_exists' => method_exists($logger, 'cleanup_logs')
                    );
                } catch (Exception $e) {
                    $debug_info['logger_tests']['error'] = $e->getMessage();
                }
            }
            
            return json_encode($debug_info, JSON_PRETTY_PRINT);
            
        } catch (Exception $e) {
            return 'Debug error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
        } catch (Error $e) {
            return 'Fatal error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
        }
    }
    
    /**
     * Comprehensive logs system debug
     */
    public function ajax_debug_logs() {
        try {
            $debug_info = array(
                'timestamp' => current_time('mysql'),
                'wordpress_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
                'plugin_status' => array(),
                'database_status' => array(),
                'ajax_status' => array(),
                'class_status' => array(),
                'file_status' => array(),
                'error_logs' => array()
            );
            
            // 1. Plugin Status
            $debug_info['plugin_status'] = array(
                'plugin_active' => is_plugin_active('katahdin-ai-webhook/katahdin-ai-webhook.php'),
                'plugin_file_exists' => file_exists(WP_PLUGIN_DIR . '/katahdin-ai-webhook/katahdin-ai-webhook.php'),
                'function_exists_katahdin_ai_webhook' => function_exists('katahdin_ai_webhook'),
                'class_exists_katahdin_ai_webhook' => class_exists('Katahdin_AI_Webhook'),
                'class_exists_katahdin_ai_webhook_admin' => class_exists('Katahdin_AI_Webhook_Admin'),
                'class_exists_katahdin_ai_webhook_logger' => class_exists('Katahdin_AI_Webhook_Logger'),
                'class_exists_katahdin_ai_webhook_handler' => class_exists('Katahdin_AI_Webhook_Handler')
            );
            
            // 2. Database Status
            global $wpdb;
            $table_name = $wpdb->prefix . 'katahdin_ai_webhook_logs';
            
            $debug_info['database_status'] = array(
                'table_name' => $table_name,
                'table_exists' => $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name,
                'database_connection' => !empty($wpdb->dbh),
                'wpdb_last_error' => $wpdb->last_error,
                'wpdb_last_query' => $wpdb->last_query
            );
            
            // Try to get table structure if it exists
            if ($debug_info['database_status']['table_exists']) {
                $columns = $wpdb->get_results("DESCRIBE $table_name");
                $debug_info['database_status']['table_structure'] = $columns;
                $debug_info['database_status']['row_count'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            }
            
            // 3. AJAX Status
            $debug_info['ajax_status'] = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce_valid' => wp_verify_nonce($_POST['nonce'] ?? '', 'katahdin_ai_webhook_nonce'),
                'user_can_manage_options' => current_user_can('manage_options'),
                'current_user_id' => get_current_user_id(),
                'is_admin' => is_admin()
            );
            
            // Test AJAX handlers
            $ajax_handlers = array(
                'katahdin_ai_webhook_get_log_stats',
                'katahdin_ai_webhook_get_logs',
                'katahdin_ai_webhook_test_log',
                'katahdin_ai_webhook_create_table',
                'katahdin_ai_webhook_debug_logs'
            );
            
            foreach ($ajax_handlers as $handler) {
                $debug_info['ajax_status']['handlers'][$handler] = has_action("wp_ajax_$handler");
            }
            
            // 4. Class Status
            if (function_exists('katahdin_ai_webhook')) {
                $webhook_instance = katahdin_ai_webhook();
                $debug_info['class_status'] = array(
                    'webhook_instance_available' => !empty($webhook_instance),
                    'webhook_instance_class' => $webhook_instance ? get_class($webhook_instance) : 'N/A',
                    'admin_instance_available' => !empty($webhook_instance->admin),
                    'webhook_handler_available' => !empty($webhook_instance->webhook_handler),
                    'logger_instance_available' => !empty($webhook_instance->webhook_handler) && method_exists($webhook_instance->webhook_handler, 'get_logger')
                );
                
                if ($debug_info['class_status']['logger_instance_available']) {
                    $logger = $webhook_instance->webhook_handler->get_logger();
                    $debug_info['class_status']['logger_class'] = get_class($logger);
                    $debug_info['class_status']['logger_methods'] = get_class_methods($logger);
                }
            }
            
            // 5. File Status
            $plugin_path = WP_PLUGIN_DIR . '/katahdin-ai-webhook/';
            $debug_info['file_status'] = array(
                'plugin_directory_exists' => is_dir($plugin_path),
                'plugin_directory_readable' => is_readable($plugin_path),
                'includes_directory_exists' => is_dir($plugin_path . 'includes/'),
                'admin_file_exists' => file_exists($plugin_path . 'includes/class-admin.php'),
                'logger_file_exists' => file_exists($plugin_path . 'includes/class-webhook-logger.php'),
                'handler_file_exists' => file_exists($plugin_path . 'includes/class-webhook-handler.php')
            );
            
            // 6. Error Logs
            $debug_info['error_logs'] = array(
                'wp_debug' => defined('WP_DEBUG') ? WP_DEBUG : false,
                'wp_debug_log' => defined('WP_DEBUG_LOG') ? WP_DEBUG_LOG : false,
                'error_log_location' => ini_get('error_log'),
                'recent_errors' => array()
            );
            
            // Get recent error log entries
            $error_log_file = ini_get('error_log');
            if ($error_log_file && file_exists($error_log_file)) {
                $lines = file($error_log_file);
                $recent_lines = array_slice($lines, -20); // Last 20 lines
                $webhook_errors = array_filter($recent_lines, function($line) {
                    return strpos($line, 'katahdin') !== false || strpos($line, 'webhook') !== false;
                });
                $debug_info['error_logs']['recent_errors'] = array_values($webhook_errors);
            }
            
            // 7. Test Logger Methods
            if (class_exists('Katahdin_AI_Webhook_Logger')) {
                try {
                    $logger = new Katahdin_AI_Webhook_Logger();
                    $debug_info['logger_tests'] = array(
                        'logger_instantiated' => !empty($logger),
                        'create_table_method_exists' => method_exists($logger, 'create_table'),
                        'get_log_stats_method_exists' => method_exists($logger, 'get_log_stats'),
                        'get_logs_method_exists' => method_exists($logger, 'get_logs'),
                        'cleanup_logs_method_exists' => method_exists($logger, 'cleanup_logs')
                    );
                    
                    // Test create_table method
                    if (method_exists($logger, 'create_table')) {
                        $debug_info['logger_tests']['create_table_result'] = $logger->create_table();
                    }
                    
                } catch (Exception $e) {
                    $debug_info['logger_tests']['error'] = $e->getMessage();
                }
            }
            
            wp_send_json_success($debug_info);
            
        } catch (Exception $e) {
            wp_send_json_error('Debug error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for creating the logs table
     */
    public function ajax_create_table() {
        try {
            check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            if (!class_exists('Katahdin_AI_Webhook_Logger')) {
                wp_send_json_error('Logger class not available');
                return;
            }
            
            $logger = new Katahdin_AI_Webhook_Logger();
            $result = $logger->create_table();
            
            if ($result === false) {
                global $wpdb;
                wp_send_json_error('Failed to create table: ' . $wpdb->last_error);
                return;
            }
            
            wp_send_json_success(array(
                'message' => 'Logs table created successfully!',
                'table_name' => $wpdb->prefix . 'katahdin_ai_webhook_logs'
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Error creating table: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for getting log details
     */
    public function ajax_get_log_details() {
        try {
            check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            $log_id = intval($_POST['log_id']);
            if (!$log_id) {
                wp_send_json_error('Invalid log ID');
                return;
            }
            
            global $wpdb;
            $table_name = $wpdb->prefix . 'katahdin_ai_webhook_logs';
            
            $log = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $log_id
            ), ARRAY_A);
            
            if (!$log) {
                wp_send_json_error('Log entry not found');
                return;
            }
            
            wp_send_json_success($log);
            
        } catch (Exception $e) {
            wp_send_json_error('Error retrieving log details: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for clearing all logs
     */
    public function ajax_clear_all_logs() {
        try {
            check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            global $wpdb;
            $table_name = $wpdb->prefix . 'katahdin_ai_webhook_logs';
            
            // Simple delete all query
            $deleted = $wpdb->query("DELETE FROM $table_name");
            
            wp_send_json_success(array(
                'message' => 'All logs cleared successfully',
                'deleted_count' => $deleted
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Error clearing logs: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for deleting a single log entry
     */
    public function ajax_delete_log() {
        try {
            check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            $log_id = intval($_POST['log_id']);
            if (!$log_id) {
                wp_send_json_error('Invalid log ID');
                return;
            }
            
            global $wpdb;
            $table_name = $wpdb->prefix . 'katahdin_ai_webhook_logs';
            
            $deleted = $wpdb->delete($table_name, array('id' => $log_id));
            
            if ($deleted === false) {
                wp_send_json_error('Failed to delete log entry');
                return;
            }
            
            wp_send_json_success(array(
                'message' => 'Log entry deleted successfully',
                'deleted_count' => $deleted
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Error deleting log: ' . $e->getMessage());
        }
    }
    
    /**
     * Simple AJAX test handler
     */
    public function ajax_test_ajax() {
        wp_send_json_success(array(
            'message' => 'AJAX is working!',
            'timestamp' => current_time('mysql'),
            'user_can_manage_options' => current_user_can('manage_options')
        ));
    }
    
    /**
     * AJAX handler for testing log entry
     */
    public function ajax_test_log() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        try {
            $webhook_handler = katahdin_ai_webhook()->webhook_handler;
            $logger = $webhook_handler->get_logger();
            
            // Generate a test webhook ID
            $webhook_id = $logger->generate_webhook_id();
            
            // Create test log data
            $test_data = array(
                'webhook_id' => $webhook_id,
                'method' => 'POST',
                'url' => rest_url('katahdin-ai-webhook/v1/webhook'),
                'headers' => json_encode(array(
                    'Content-Type' => 'application/json',
                    'X-Webhook-Secret' => 'test_secret'
                )),
                'body' => json_encode(array(
                    'form_data' => array(
                        'name' => 'Test User',
                        'email' => 'test@example.com',
                        'message' => 'This is a test log entry created from the admin interface.'
                    ),
                    'form_id' => 'test_form',
                    'entry_id' => 'test_entry_' . time()
                )),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'WordPress Admin Test',
                'response_code' => 200,
                'response_body' => json_encode(array(
                    'success' => true,
                    'message' => 'Test log entry created successfully',
                    'test_id' => $webhook_id
                )),
                'processing_time_ms' => rand(100, 500),
                'ai_response' => json_encode(array(
                    'analysis' => 'This is a test AI analysis response.',
                    'sentiment' => 'positive',
                    'confidence' => 0.95
                )),
                'email_sent' => 1,
                'email_response' => 'Test email sent successfully',
                'status' => 'success'
            );
            
            // Insert the test log
            global $wpdb;
            $table_name = $wpdb->prefix . 'katahdin_ai_webhook_logs';
            
            $result = $wpdb->insert($table_name, $test_data);
            
            if ($result === false) {
                wp_send_json_error('Failed to create test log entry: ' . $wpdb->last_error);
            }
            
            wp_send_json_success(array(
                'message' => 'Test log entry created successfully!',
                'webhook_id' => $webhook_id,
                'log_id' => $wpdb->insert_id
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Error creating test log: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for testing API key
     */
    public function ajax_test_api_key() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
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
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
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
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
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
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
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
            
            check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
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
            
            check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
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
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
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
        // Check if table exists and create it if needed
        global $wpdb;
        $table_name = $wpdb->prefix . 'katahdin_ai_webhook_logs';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            // Table doesn't exist, create it
            if (class_exists('Katahdin_AI_Webhook_Logger')) {
                $logger = new Katahdin_AI_Webhook_Logger();
                $result = $logger->create_table();
                if ($result !== false) {
                    echo '<div class="notice notice-success"><p><strong>✅ Logs table created successfully!</strong></p></div>';
                } else {
                    echo '<div class="notice notice-error"><p><strong>❌ Failed to create logs table:</strong> ' . $wpdb->last_error . '</p></div>';
                }
            } else {
                echo '<div class="notice notice-error"><p><strong>❌ Logger class not available</strong></p></div>';
            }
        }
        
        ?>
        <div class="wrap">
            <h1>Webhook Logs</h1>
            
            <!-- Debug Section -->
            <div class="katahdin-webhook-debug-section" style="background: #f8f9fa; border: 1px solid #ddd; border-radius: 6px; padding: 20px; margin-bottom: 20px;">
                <h2>🔍 Logs System Debug 
                    <button type="button" class="button button-secondary copy-debug-btn" data-target="logs-debug-content" style="font-size: 12px; padding: 6px 12px; margin-left: 10px;">📋 Copy Debug Results</button>
                    <button type="button" class="button button-secondary toggle-debug-btn" style="font-size: 12px; padding: 6px 12px; margin-left: 5px;">👁️ Toggle Debug</button>
                </h2>
                <div id="logs-debug-output" style="display: none;">
                    <pre id="logs-debug-content" style="background: #fff; border: 1px solid #ccc; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 11px; max-height: 200px; overflow-y: auto; line-height: 1.2;"><?php 
                    try {
                        echo $this->get_logs_debug_info();
                    } catch (Exception $e) {
                        echo 'Debug method failed: ' . $e->getMessage();
                    } catch (Error $e) {
                        echo 'Fatal error in debug: ' . $e->getMessage();
                    }
                    ?></pre>
                </div>
            </div>
            
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
                    <button id="create-table-btn" class="button button-secondary">Create Logs Table</button>
                    <button id="test-log-btn" class="button button-primary">Test Log Entry</button>
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
        
        <script>
        jQuery(document).ready(function($) {
            // Load initial data
            loadLogStats();
            loadLogs();
            
            // Copy debug button
            $('.copy-debug-btn').on('click', function() {
                var targetId = $(this).data('target');
                var content = $('#' + targetId).text();
                
                // Create a temporary textarea to copy the content
                var tempTextarea = $('<textarea>');
                tempTextarea.val(content);
                $('body').append(tempTextarea);
                tempTextarea.select();
                
                try {
                    document.execCommand('copy');
                    $(this).text('✅ Copied!').css('color', 'green');
                    setTimeout(function() {
                        $('.copy-debug-btn').text('📋 Copy Debug Results').css('color', '');
                    }, 2000);
                } catch (err) {
                    alert('Failed to copy. Please select and copy manually.');
                }
                
                tempTextarea.remove();
            });
            
            // Toggle debug button
            $('.toggle-debug-btn').on('click', function() {
                var $debugOutput = $('#logs-debug-output');
                var $btn = $(this);
                
                if ($debugOutput.is(':visible')) {
                    $debugOutput.slideUp();
                    $btn.text('👁️ Show Debug');
                } else {
                    $debugOutput.slideDown();
                    $btn.text('👁️ Hide Debug');
                }
            });
            
            // Refresh button
            $('#refresh-logs').on('click', function() {
                loadLogStats();
                loadLogs();
            });
            
            // Status filter
            $('#status-filter').on('change', function() {
                loadLogs();
            });
            
            // Clear all logs
            $('#cleanup-logs').on('click', function() {
                if (confirm('Are you sure you want to delete ALL logs? This cannot be undone.')) {
                    $.post(ajaxurl, {
                        action: 'katahdin_ai_webhook_clear_all_logs',
                        nonce: '<?php echo wp_create_nonce('katahdin_ai_forms_nonce'); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('All logs cleared successfully!');
                            loadLogStats();
                            loadLogs();
                        } else {
                            alert('Error clearing logs: ' + response.data);
                        }
                    }).fail(function() {
                        alert('AJAX Error: Could not clear logs');
                    });
                }
            });
            
            // Create table button
            $('#create-table-btn').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('Creating Table...');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_create_table',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('✅ ' + response.data.message + '\nTable: ' + response.data.table_name);
                        loadLogStats();
                        loadLogs();
                    } else {
                        alert('❌ Error creating table: ' + response.data);
                    }
                }).always(function() {
                    $btn.prop('disabled', false).text('Create Logs Table');
                });
            });
            
            // Debug logs system
            $('#debug-logs-system').on('click', function() {
                var $btn = $(this);
                var $output = $('#logs-debug-output');
                var $pre = $output.find('pre');
                
                $btn.prop('disabled', true).text('Running Debug...');
                $output.show();
                $pre.text('Running comprehensive logs system debug...');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_debug_logs',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        $pre.text(JSON.stringify(response.data, null, 2));
                    } else {
                        $pre.text('❌ Debug failed: ' + response.data);
                    }
                }).always(function() {
                    $btn.prop('disabled', false).text('Run Logs Debug');
                });
            });
            
            // Test log button
            $('#test-log-btn').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('Creating Test Log...');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_test_log',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('✅ ' + response.data.message + '\nWebhook ID: ' + response.data.webhook_id);
                        loadLogStats();
                        loadLogs();
                    } else {
                        alert('❌ Error creating test log: ' + response.data);
                    }
                }).always(function() {
                    $btn.prop('disabled', false).text('Test Log Entry');
                });
            });
            
            // View log details
            $(document).on('click', '.view-log-btn', function() {
                var logId = $(this).data('log-id');
                showLogDetails(logId);
            });
            
            // Delete log entry
            $(document).on('click', '.delete-log-btn', function() {
                var logId = $(this).data('log-id');
                var $btn = $(this);
                
                if (confirm('Are you sure you want to delete this log entry? This cannot be undone.')) {
                    $btn.prop('disabled', true).text('Deleting...');
                    
                    $.post(ajaxurl, {
                        action: 'katahdin_ai_webhook_delete_log',
                        nonce: '<?php echo wp_create_nonce('katahdin_ai_forms_nonce'); ?>',
                        log_id: logId
                    }, function(response) {
                        if (response.success) {
                            alert('Log entry deleted successfully!');
                            loadLogStats();
                            loadLogs();
                        } else {
                            alert('Error deleting log: ' + response.data);
                            $btn.prop('disabled', false).html('🗑️');
                        }
                    }).fail(function() {
                        alert('AJAX Error: Could not delete log');
                        $btn.prop('disabled', false).html('🗑️');
                    });
                }
            });
            
            function showLogDetails(logId) {
                // Create modal overlay
                var modalHtml = '<div id="log-details-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">';
                modalHtml += '<div style="background: white; border-radius: 8px; padding: 20px; max-width: 80%; max-height: 80%; overflow-y: auto; position: relative;">';
                modalHtml += '<button id="close-log-modal" style="position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>';
                modalHtml += '<h3>Log Details</h3>';
                modalHtml += '<div id="log-details-content">Loading...</div>';
                modalHtml += '</div></div>';
                
                $('body').append(modalHtml);
                
                // Close modal handlers
                $('#close-log-modal, #log-details-modal').on('click', function(e) {
                    if (e.target === this) {
                        $('#log-details-modal').remove();
                    }
                });
                
                // Load log details
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_get_log_details',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>',
                    log_id: logId
                }, function(response) {
                    if (response.success) {
                        var log = response.data;
                        var detailsHtml = '<div style="font-family: monospace; font-size: 12px;">';
                        detailsHtml += '<h4>Basic Info</h4>';
                        detailsHtml += '<p><strong>ID:</strong> ' + log.id + '</p>';
                        detailsHtml += '<p><strong>Webhook ID:</strong> ' + log.webhook_id + '</p>';
                        detailsHtml += '<p><strong>Timestamp:</strong> ' + new Date(log.timestamp).toLocaleString() + '</p>';
                        detailsHtml += '<p><strong>Status:</strong> ' + log.status + '</p>';
                        detailsHtml += '<p><strong>Method:</strong> ' + log.method + '</p>';
                        detailsHtml += '<p><strong>Response Code:</strong> ' + (log.response_code || 'N/A') + '</p>';
                        detailsHtml += '<p><strong>Processing Time:</strong> ' + (log.processing_time_ms ? log.processing_time_ms + 'ms' : 'N/A') + '</p>';
                        
                        if (log.headers) {
                            detailsHtml += '<h4>Headers</h4>';
                            detailsHtml += '<pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto;">' + log.headers + '</pre>';
                        }
                        
                        if (log.body) {
                            detailsHtml += '<h4>Request Body</h4>';
                            detailsHtml += '<pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto;">' + log.body + '</pre>';
                        }
                        
                        if (log.response_body) {
                            detailsHtml += '<h4>Response Body</h4>';
                            detailsHtml += '<pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto;">' + log.response_body + '</pre>';
                        }
                        
                        if (log.ai_response) {
                            detailsHtml += '<h4>AI Response</h4>';
                            detailsHtml += '<pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto;">' + log.ai_response + '</pre>';
                        }
                        
                        if (log.error_message) {
                            detailsHtml += '<h4>Error Message</h4>';
                            detailsHtml += '<pre style="background: #ffe6e6; padding: 10px; border-radius: 4px; overflow-x: auto;">' + log.error_message + '</pre>';
                        }
                        
                        detailsHtml += '</div>';
                        $('#log-details-content').html(detailsHtml);
                    } else {
                        $('#log-details-content').html('<p style="color: red;">Error loading log details: ' + response.data + '</p>');
                    }
                });
            }
            
            function loadLogStats() {
                $('#logs-stats-content').html('<p>Loading statistics...</p>');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_get_log_stats',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        var stats = response.data;
                        var html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">';
                        html += '<div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 6px;"><strong>' + stats.total_logs + '</strong><br>Total Logs</div>';
                        html += '<div style="text-align: center; padding: 15px; background: #d4edda; border-radius: 6px;"><strong>' + stats.success_logs + '</strong><br>Success</div>';
                        html += '<div style="text-align: center; padding: 15px; background: #f8d7da; border-radius: 6px;"><strong>' + stats.error_logs + '</strong><br>Errors</div>';
                        html += '<div style="text-align: center; padding: 15px; background: #fff3cd; border-radius: 6px;"><strong>' + stats.pending_logs + '</strong><br>Pending</div>';
                        html += '</div>';
                        $('#logs-stats-content').html(html);
                    } else {
                        $('#logs-stats-content').html('<p style="color: red;">❌ Error loading statistics: ' + (response.data || 'Unknown error') + '</p>');
                    }
                }).fail(function(xhr, status, error) {
                    $('#logs-stats-content').html('<p style="color: red;">❌ AJAX Error loading statistics: ' + error + '</p>');
                });
            }
            
            function loadLogs() {
                $('#logs-content').html('<p>Loading logs...</p>');
                
                var status = $('#status-filter').val();
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_get_logs',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>',
                    limit: 50,
                    offset: 0,
                    status: status
                }, function(response) {
                    if (response.success) {
                        var logs = response.data;
                        if (logs.length > 0) {
                            var html = '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Time</th><th>Status</th><th>Email</th><th>Name</th><th>Response Code</th><th>Actions</th></tr></thead><tbody>';
                            
                            logs.forEach(function(log) {
                                // Use the extracted form data from database columns
                                var email = log.form_email || 'N/A';
                                var name = log.form_name || 'N/A';
                                
                                html += '<tr>';
                                html += '<td>' + new Date(log.timestamp).toLocaleString() + '</td>';
                                html += '<td><span class="katahdin-webhook-status ' + log.status + '">' + log.status.charAt(0).toUpperCase() + log.status.slice(1) + '</span></td>';
                                html += '<td style="font-size: 12px;">' + email + '</td>';
                                html += '<td style="font-size: 12px;">' + name + '</td>';
                                html += '<td>' + (log.response_code || 'N/A') + '</td>';
                                html += '<td style="white-space: nowrap;">';
                                html += '<button class="button button-small view-log-btn" data-log-id="' + log.id + '" style="padding: 2px 6px; margin-right: 3px;" title="View Details">👁️</button>';
                                html += '<button class="button button-small delete-log-btn" data-log-id="' + log.id + '" style="padding: 2px 6px; background: transparent; color: #dc3545; border: 1px solid #dc3545;" title="Delete Log">🗑️</button>';
                                html += '</td>';
                                html += '</tr>';
                            });
                            
                            html += '</tbody></table>';
                            $('#logs-content').html(html);
                        } else {
                            $('#logs-content').html('<p>No logs found.</p>');
                        }
                    } else {
                        $('#logs-content').html('<p style="color: red;">❌ Error loading logs: ' + (response.data || 'Unknown error') + '</p>');
                    }
                }).fail(function(xhr, status, error) {
                    $('#logs-content').html('<p style="color: red;">❌ AJAX Error loading logs: ' + error + '</p>');
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for simple test
     */
    public function ajax_simple_test() {
        try {
            error_log('Katahdin AI Webhook Simple Test: Starting');
            
            check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
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
            check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
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
    
    /**
     * Prompts management page
     */
    public function prompts_page() {
        ?>
        <div class="wrap katahdin-webhook-admin">
            <div class="katahdin-webhook-header">
                <h1>📝 Form Prompts Management</h1>
                <p>Manage AI prompts for different forms. Each form can have its own custom prompt for analysis.</p>
            </div>
            
            <div class="katahdin-webhook-content">
                <div class="katahdin-webhook-section">
                    <div class="section-header">
                        <h2>Add New Prompt</h2>
                        <button type="button" class="button button-primary" id="add-prompt-btn">Add New Prompt</button>
                    </div>
                    
                    <div id="prompt-form-container" style="display: none;">
                        <form id="prompt-form" class="katahdin-form">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Title</th>
                                    <td>
                                        <input type="text" id="prompt-title" name="title" class="regular-text" placeholder="e.g., Contact Form Analysis" required>
                                        <p class="description">A descriptive title to identify this prompt</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Prompt ID</th>
                                    <td>
                                        <input type="text" id="prompt-prompt-id" name="prompt_id" class="regular-text" placeholder="e.g., contact_form_123" required>
                                        <p class="description">The prompt ID that will be passed via the form to trigger this prompt</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Prompt</th>
                                    <td>
                                        <textarea id="prompt-text" name="prompt" rows="8" cols="50" class="large-text" placeholder="Enter your AI prompt here..." required></textarea>
                                        <p class="description">The prompt that will be sent to the AI along with the form data</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Email Address</th>
                                    <td>
                                        <input type="email" id="prompt-email-address" name="email_address" class="regular-text" placeholder="e.g., admin@example.com" required>
                                        <p class="description">Email address where the AI analysis results will be sent</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Email Subject</th>
                                    <td>
                                        <input type="text" id="prompt-email-subject" name="email_subject" class="regular-text" placeholder="e.g., AI Analysis Results - Contact Form" required>
                                        <p class="description">Subject line for the email containing AI analysis results</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Status</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="prompt-active" name="is_active" checked> Active
                                        </label>
                                        <p class="description">Only active prompts will be used for form processing</p>
                                    </td>
                                </tr>
                            </table>
                            <p class="submit">
                                <button type="submit" class="button button-primary">Save Prompt</button>
                                <button type="button" class="button" id="cancel-prompt-form">Cancel</button>
                            </p>
                        </form>
                    </div>
                </div>
                
                <div class="katahdin-webhook-section">
                    <div class="section-header">
                        <h2>Existing Prompts</h2>
                        <div class="prompt-stats" id="prompt-stats">
                            <span class="stat-item">Total: <strong id="total-prompts">0</strong></span>
                            <span class="stat-item">Active: <strong id="active-prompts">0</strong></span>
                            <span class="stat-item">Inactive: <strong id="inactive-prompts">0</strong></span>
                        </div>
                    </div>
                    
                    <div id="prompts-list">
                        <p class="loading">Loading prompts...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Load prompts on page load
            loadPrompts();
            
            // Add prompt button
            $('#add-prompt-btn').on('click', function() {
                $('#prompt-form-container').show();
                $('#prompt-form')[0].reset();
                $('#prompt-active').prop('checked', true);
                $('#prompt-prompt-id').focus();
            });
            
            // Cancel form
            $('#cancel-prompt-form').on('click', function() {
                $('#prompt-form-container').hide();
                resetForm();
            });
            
            // Submit form
            $('#prompt-form').on('submit', function(e) {
                e.preventDefault();
                savePrompt();
            });
            
            function loadPrompts() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'katahdin_ai_forms_get_prompts',
                        nonce: '<?php echo wp_create_nonce('katahdin_ai_forms_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            displayPrompts(response.data.prompts);
                            updateStats(response.data.stats);
                        } else {
                            $('#prompts-list').html('<p class="error">Error loading prompts: ' + response.data + '</p>');
                        }
                    },
                    error: function() {
                        $('#prompts-list').html('<p class="error">Failed to load prompts</p>');
                    }
                });
            }
            
            function displayPrompts(prompts) {
                if (prompts.length === 0) {
                    $('#prompts-list').html('<p class="no-data">No prompts found. Add your first prompt above.</p>');
                    return;
                }
                
                var html = '<table class="wp-list-table widefat fixed striped">';
                html += '<thead><tr>';
                html += '<th>Title</th><th>Form ID</th><th>Status</th><th>Created</th><th>Actions</th>';
                html += '</tr></thead><tbody>';
                
                prompts.forEach(function(prompt) {
                    var statusClass = prompt.is_active == 1 ? 'active' : 'inactive';
                    var statusText = prompt.is_active == 1 ? 'Active' : 'Inactive';
                    var createdDate = new Date(prompt.created_at).toLocaleDateString();
                    
                    html += '<tr>';
                    html += '<td><strong>' + escapeHtml(prompt.title) + '</strong></td>';
                    html += '<td><code>' + escapeHtml(prompt.form_id) + '</code></td>';
                    html += '<td><span class="status-' + statusClass + '">' + statusText + '</span></td>';
                    html += '<td>' + createdDate + '</td>';
                    html += '<td>';
                    html += '<button class="button button-small edit-prompt" data-id="' + prompt.id + '">Edit</button> ';
                    html += '<button class="button button-small toggle-prompt" data-id="' + prompt.id + '" data-status="' + prompt.is_active + '">' + (prompt.is_active == 1 ? 'Deactivate' : 'Activate') + '</button> ';
                    html += '<button class="button button-small button-link-delete delete-prompt" data-id="' + prompt.id + '">Delete</button>';
                    html += '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#prompts-list').html(html);
                
                // Bind event handlers
                $('.edit-prompt').on('click', function() {
                    editPrompt($(this).data('id'));
                });
                
                $('.toggle-prompt').on('click', function() {
                    togglePrompt($(this).data('id'));
                });
                
                $('.delete-prompt').on('click', function() {
                    if (confirm('Are you sure you want to delete this prompt?')) {
                        deletePrompt($(this).data('id'));
                    }
                });
            }
            
            function updateStats(stats) {
                $('#total-prompts').text(stats.total);
                $('#active-prompts').text(stats.active);
                $('#inactive-prompts').text(stats.inactive);
            }
            
            function savePrompt() {
                var isEdit = $('#prompt-id').length > 0;
                var action = isEdit ? 'katahdin_ai_webhook_update_prompt' : 'katahdin_ai_webhook_add_prompt';
                
                var formData = {
                    action: action,
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_webhook_nonce'); ?>',
                    title: $('#prompt-title').val(),
                    form_id: $('#prompt-form-id').val(),
                    prompt: $('#prompt-text').val(),
                    is_active: $('#prompt-active').is(':checked') ? 1 : 0
                };
                
                // Add ID for edit mode
                if (isEdit) {
                    formData.id = $('#prompt-id').val();
                }
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#prompt-form-container').hide();
                            resetForm();
                            loadPrompts();
                            alert(isEdit ? 'Prompt updated successfully!' : 'Prompt saved successfully!');
                        } else {
                            alert('Error ' + (isEdit ? 'updating' : 'saving') + ' prompt: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Failed to ' + (isEdit ? 'update' : 'save') + ' prompt');
                    }
                });
            }
            
            function editPrompt(id) {
                // Load prompt data and populate form for editing
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'katahdin_ai_webhook_get_prompt_by_id',
                        nonce: '<?php echo wp_create_nonce('katahdin_ai_forms_nonce'); ?>',
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            var prompt = response.data;
                            $('#prompt-title').val(prompt.title);
                            $('#prompt-form-id').val(prompt.form_id);
                            $('#prompt-text').val(prompt.prompt);
                            $('#prompt-active').prop('checked', prompt.is_active == 1);
                            
                            // Add hidden field for edit mode
                            if (!$('#prompt-id').length) {
                                $('#prompt-form').append('<input type="hidden" id="prompt-id" name="id">');
                            }
                            $('#prompt-id').val(id);
                            
                            // Change form title and button text
                            $('.section-header h2').text('Edit Prompt');
                            $('#prompt-form button[type="submit"]').text('Update Prompt');
                            
                            $('#prompt-form-container').show();
                            $('#prompt-title').focus();
                        } else {
                            alert('Error loading prompt: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Failed to load prompt');
                    }
                });
            }
            
            function resetForm() {
                $('#prompt-form')[0].reset();
                $('#prompt-active').prop('checked', true);
                $('#prompt-id').remove();
                $('.section-header h2').text('Add New Prompt');
                $('#prompt-form button[type="submit"]').text('Save Prompt');
            }
            
            function togglePrompt(id) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'katahdin_ai_webhook_toggle_prompt',
                        nonce: '<?php echo wp_create_nonce('katahdin_ai_forms_nonce'); ?>',
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            loadPrompts();
                        } else {
                            alert('Error toggling prompt: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Failed to toggle prompt');
                    }
                });
            }
            
            function deletePrompt(id) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'katahdin_ai_webhook_delete_prompt',
                        nonce: '<?php echo wp_create_nonce('katahdin_ai_forms_nonce'); ?>',
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            loadPrompts();
                        } else {
                            alert('Error deleting prompt: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Failed to delete prompt');
                    }
                });
            }
            
            function escapeHtml(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        });
        </script>
        
        <style>
        .prompt-stats {
            display: inline-block;
            margin-left: 20px;
        }
        .stat-item {
            margin-right: 15px;
            color: #666;
        }
        .status-active {
            color: #46b450;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3232;
            font-weight: bold;
        }
        .no-data, .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .error {
            color: #dc3232;
        }
        </style>
        <?php
    }
    
    /**
     * AJAX handler to get all prompts
     */
    public function ajax_get_prompts() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            if (!class_exists('Katahdin_AI_Forms_Form_Prompts')) {
                wp_send_json_error('Form prompts class not available');
            }
            
            $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
            $prompts = $form_prompts->get_all_prompts();
            $stats = $form_prompts->get_stats();
            
            wp_send_json_success(array(
                'prompts' => $prompts,
                'stats' => $stats
            ));
            
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * AJAX handler to add a new prompt
     */
    public function ajax_add_prompt() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $title = sanitize_text_field($_POST['title']);
            $prompt_id = sanitize_text_field($_POST['prompt_id']);
            $prompt = sanitize_textarea_field($_POST['prompt']);
            $email_address = sanitize_email($_POST['email_address']);
            $email_subject = sanitize_text_field($_POST['email_subject']);
            $is_active = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 1;
            
            if (empty($title) || empty($prompt_id) || empty($prompt) || empty($email_address) || empty($email_subject)) {
                wp_send_json_error('All fields are required');
            }
            
            if (!class_exists('Katahdin_AI_Forms_Form_Prompts')) {
                wp_send_json_error('Form prompts class not available');
            }
            
            $form_prompts = new Katahdin_AI_Webhook_Form_Prompts();
            
            // Check if table exists first
            if (!$form_prompts->table_exists()) {
                $form_prompts->force_create_table();
                
                // Check again
                if (!$form_prompts->table_exists()) {
                    wp_send_json_error('Failed to create prompts table. Please check database permissions.');
                }
            }
            
            $result = $form_prompts->add_prompt($title, $prompt_id, $prompt, $email_address, $email_subject);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }
            
            wp_send_json_success('Prompt added successfully');
            
        } catch (Exception $e) {
            wp_send_json_error('Exception: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler to update a prompt
     */
    public function ajax_update_prompt() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $id = (int) $_POST['id'];
            $title = sanitize_text_field($_POST['title']);
            $prompt_id = sanitize_text_field($_POST['prompt_id']);
            $prompt = sanitize_textarea_field($_POST['prompt']);
            $email_address = sanitize_email($_POST['email_address']);
            $email_subject = sanitize_text_field($_POST['email_subject']);
            $is_active = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 1;
            
            if (empty($id) || empty($title) || empty($prompt_id) || empty($prompt) || empty($email_address) || empty($email_subject)) {
                wp_send_json_error('All fields are required');
            }
            
            if (!class_exists('Katahdin_AI_Forms_Form_Prompts')) {
                wp_send_json_error('Form prompts class not available');
            }
            
            $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
            $result = $form_prompts->update_prompt($id, $title, $prompt_id, $prompt, $email_address, $email_subject, $is_active);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }
            
            wp_send_json_success('Prompt updated successfully');
            
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * AJAX handler to delete a prompt
     */
    public function ajax_delete_prompt() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $id = (int) $_POST['id'];
            
            if (empty($id)) {
                wp_send_json_error('Invalid prompt ID');
            }
            
            if (!class_exists('Katahdin_AI_Forms_Form_Prompts')) {
                wp_send_json_error('Form prompts class not available');
            }
            
            $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
            $result = $form_prompts->delete_prompt($id);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }
            
            wp_send_json_success('Prompt deleted successfully');
            
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * AJAX handler to toggle prompt status
     */
    public function ajax_toggle_prompt() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $id = (int) $_POST['id'];
            
            if (empty($id)) {
                wp_send_json_error('Invalid prompt ID');
            }
            
            if (!class_exists('Katahdin_AI_Forms_Form_Prompts')) {
                wp_send_json_error('Form prompts class not available');
            }
            
            $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
            $result = $form_prompts->toggle_prompt_status($id);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }
            
            wp_send_json_success(array(
                'new_status' => $result,
                'message' => $result ? 'Prompt activated' : 'Prompt deactivated'
            ));
            
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * AJAX handler to get prompt by ID
     */
    public function ajax_get_prompt_by_id() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $id = (int) $_POST['id'];
            
            if (empty($id)) {
                wp_send_json_error('Invalid prompt ID');
            }
            
            if (!class_exists('Katahdin_AI_Forms_Form_Prompts')) {
                wp_send_json_error('Form prompts class not available');
            }
            
            $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
            $prompt = $form_prompts->get_prompt_by_id($id);
            
            if (!$prompt) {
                wp_send_json_error('Prompt not found');
            }
            
            wp_send_json_success($prompt);
            
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
}
