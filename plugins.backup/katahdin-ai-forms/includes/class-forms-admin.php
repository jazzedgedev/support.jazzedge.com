<?php
/**
 * Forms Admin Interface for Katahdin AI Forms
 * Provides admin settings and management interface with working functionality
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
        add_action('admin_head', array($this, 'add_admin_styles'));
        
        // AJAX handlers
        $this->register_ajax_handlers();
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers() {
        // Settings AJAX
        add_action('wp_ajax_katahdin_ai_forms_test_api_key', array($this, 'ajax_test_api_key'));
        add_action('wp_ajax_katahdin_ai_forms_test_forms', array($this, 'ajax_test_forms'));
        add_action('wp_ajax_katahdin_ai_forms_test_email', array($this, 'ajax_test_email'));
        add_action('wp_ajax_katahdin_ai_forms_regenerate_secret', array($this, 'ajax_regenerate_secret'));
        add_action('wp_ajax_katahdin_ai_forms_comprehensive_debug', array($this, 'ajax_comprehensive_debug'));
        
        // Logs AJAX
        add_action('wp_ajax_katahdin_ai_forms_get_logs', array($this, 'ajax_get_logs'));
        add_action('wp_ajax_katahdin_ai_forms_get_log_stats', array($this, 'ajax_get_log_stats'));
        add_action('wp_ajax_katahdin_ai_forms_cleanup_logs', array($this, 'ajax_cleanup_logs'));
        add_action('wp_ajax_katahdin_ai_forms_get_log_details', array($this, 'ajax_get_log_details'));
        add_action('wp_ajax_katahdin_ai_forms_clear_all_logs', array($this, 'ajax_clear_all_logs'));
        add_action('wp_ajax_katahdin_ai_forms_delete_log', array($this, 'ajax_delete_log'));
        
        // Prompts AJAX
        add_action('wp_ajax_katahdin_ai_forms_add_prompt', array($this, 'ajax_add_prompt'));
        add_action('wp_ajax_katahdin_ai_forms_update_prompt', array($this, 'ajax_update_prompt'));
        add_action('wp_ajax_katahdin_ai_forms_delete_prompt', array($this, 'ajax_delete_prompt'));
        add_action('wp_ajax_katahdin_ai_forms_toggle_prompt', array($this, 'ajax_toggle_prompt'));
        add_action('wp_ajax_katahdin_ai_forms_get_prompts', array($this, 'ajax_get_prompts'));
        add_action('wp_ajax_katahdin_ai_forms_get_prompt_by_id', array($this, 'ajax_get_prompt_by_id'));
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
            'dashicons-email-alt',
            30
        );
        
        // Settings submenu
        add_submenu_page(
            'katahdin-ai-forms',
            'Settings',
            'Settings',
            'manage_options',
            'katahdin-ai-forms',
            array($this, 'admin_page')
        );
        
        // Prompts submenu
        add_submenu_page(
            'katahdin-ai-forms',
            'Form Prompts',
            'Form Prompts',
            'manage_options',
            'katahdin-ai-forms-prompts',
            array($this, 'prompts_page')
        );
        
        // Logs submenu
        add_submenu_page(
            'katahdin-ai-forms',
            'Form Logs',
            'Form Logs',
            'manage_options',
            'katahdin-ai-forms-logs',
            array($this, 'logs_page')
        );
        
        // Instructions submenu
        add_submenu_page(
            'katahdin-ai-forms',
            'Instructions',
            'Instructions',
            'manage_options',
            'katahdin-ai-forms-instructions',
            array($this, 'instructions_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Register settings group
        register_setting('katahdin_ai_forms_settings', 'katahdin_ai_forms_enabled');
        register_setting('katahdin_ai_forms_settings', 'katahdin_ai_forms_model');
        register_setting('katahdin_ai_forms_settings', 'katahdin_ai_forms_max_tokens');
        register_setting('katahdin_ai_forms_settings', 'katahdin_ai_forms_temperature');
        register_setting('katahdin_ai_forms_settings', 'katahdin_ai_forms_log_retention_days');
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $forms_enabled = get_option('katahdin_ai_forms_enabled', true);
        $forms_url = rest_url('katahdin-ai-forms/v1/forms');
        $webhook_secret = get_option('katahdin_ai_forms_webhook_secret', '');
        $model = get_option('katahdin_ai_forms_model', 'gpt-3.5-turbo');
        $max_tokens = get_option('katahdin_ai_forms_max_tokens', 1000);
        $temperature = get_option('katahdin_ai_forms_temperature', 0.7);
        $retention_days = get_option('katahdin_ai_forms_log_retention_days', 30);
        
        ?>
        <div class="wrap">
            <div class="katahdin-forms-header">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <p class="description">Configure AI-powered form processing with per-prompt email settings.</p>
                </div>
                
            <div class="katahdin-forms-content">
                    <form method="post" action="options.php">
                    <?php settings_fields('katahdin_ai_forms_settings'); ?>
                    
                    <div class="katahdin-forms-section">
                        <h2>General Settings</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Enable Forms Processing</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="katahdin_ai_forms_enabled" value="1" <?php checked($forms_enabled, 1); ?>>
                                        Enable AI-powered form processing
                                    </label>
                                    <p class="description">When enabled, forms will be processed and analyzed by AI.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">AI Model</th>
                                <td>
                                    <select name="katahdin_ai_forms_model">
                                        <option value="gpt-3.5-turbo" <?php selected($model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                                        <option value="gpt-4" <?php selected($model, 'gpt-4'); ?>>GPT-4</option>
                                        <option value="gpt-4-turbo" <?php selected($model, 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                                    </select>
                                    <p class="description">Choose the AI model for analysis.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Max Tokens</th>
                                <td>
                                    <input type="number" name="katahdin_ai_forms_max_tokens" value="<?php echo esc_attr($max_tokens); ?>" min="100" max="4000" class="small-text">
                                    <p class="description">Maximum tokens for AI response (100-4000).</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Temperature</th>
                                <td>
                                    <input type="number" name="katahdin_ai_forms_temperature" value="<?php echo esc_attr($temperature); ?>" min="0" max="2" step="0.1" class="small-text">
                                    <p class="description">AI creativity level (0.0 = focused, 2.0 = creative).</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Log Retention (Days)</th>
                                <td>
                                    <input type="number" name="katahdin_ai_forms_log_retention_days" value="<?php echo esc_attr($retention_days); ?>" min="1" max="365" class="small-text">
                                    <p class="description">How long to keep form logs (1-365 days).</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="katahdin-forms-section">
                        <h2>Forms Endpoint</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Forms URL</th>
                                <td>
                                    <div class="copy-field-container">
                                        <input type="text" id="forms-url" value="<?php echo esc_url($forms_url); ?>" readonly class="regular-text copy-field">
                                        <button type="button" class="button katahdin-forms-button secondary copy-btn" data-target="forms-url" title="Copy to clipboard">
                                            <span class="copy-icon">📋</span> Copy
                                        </button>
                                    </div>
                                    <p class="description">Use this URL in your FluentForm webhook settings.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Webhook Secret</th>
                                <td>
                                    <div class="copy-field-container">
                                        <input type="text" id="webhook-secret" value="<?php echo esc_attr($webhook_secret); ?>" readonly class="regular-text copy-field">
                                        <button type="button" class="button katahdin-forms-button secondary copy-btn" data-target="webhook-secret" title="Copy to clipboard">
                                            <span class="copy-icon">📋</span> Copy
                                        </button>
                                        <button type="button" class="button katahdin-forms-button secondary" id="regenerate-secret">Regenerate Secret</button>
                                    </div>
                                    <p class="description">Use this secret in your webhook headers (X-Webhook-Secret).</p>
                                </td>
                            </tr>
                        </table>
                        
                        <div class="endpoint-instructions">
                            <h3>📋 Quick Setup Guide</h3>
                            <p>For detailed setup instructions, visit the <a href="<?php echo admin_url('admin.php?page=katahdin-ai-forms-instructions'); ?>" class="button button-primary">📋 Instructions Page</a></p>
                            
                            <div class="quick-steps">
                                <div class="quick-step">
                                    <strong>1.</strong> Create a prompt in <a href="<?php echo admin_url('admin.php?page=katahdin-ai-forms-prompts'); ?>">Form Prompts</a>
                                </div>
                                <div class="quick-step">
                                    <strong>2.</strong> Configure FluentForm webhook with the URL above
                                </div>
                                <div class="quick-step">
                                    <strong>3.</strong> Add <code>prompt_id</code> as a <strong>Request Header</strong> (recommended)
                                </div>
                                <div class="quick-step">
                                    <strong>4.</strong> Test and check <a href="<?php echo admin_url('admin.php?page=katahdin-ai-forms-logs'); ?>">Form Logs</a>
                                </div>
                            </div>
                        </div>
        </div>
        
                    <div class="katahdin-forms-section">
                        <h2>Email Configuration</h2>
                        <p>Email settings are now configured individually for each prompt. Go to <a href="<?php echo admin_url('admin.php?page=katahdin-ai-forms-prompts'); ?>">Form Prompts</a> to set up email addresses and subjects for each prompt.</p>
                    </div>
                    
                    <?php submit_button('Save Settings'); ?>
                </form>
                
                <div class="katahdin-forms-section">
                    <h2>Testing & Debugging</h2>
                    <div class="katahdin-forms-card">
                        <h3>Quick Tests</h3>
                        <p>
                            <button type="button" class="button katahdin-forms-button primary" id="test-forms">Test Forms Endpoint</button>
                            <button type="button" class="button katahdin-forms-button secondary" id="test-email">Test Email</button>
                            <button type="button" class="button katahdin-forms-button secondary" id="comprehensive-debug">Comprehensive Debug</button>
                        </p>
                        <div id="test-results" class="katahdin-forms-status"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .katahdin-forms-header {
            margin-bottom: 20px;
        }
        
        .katahdin-forms-content {
            max-width: 1200px;
        }
        
        .katahdin-forms-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .katahdin-forms-section h2 {
            margin-top: 0;
            color: #23282d;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .katahdin-forms-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .katahdin-forms-button {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .katahdin-forms-button.primary {
            background: #0073aa;
            border-color: #0073aa;
            color: #fff;
        }
        
        .katahdin-forms-button.secondary {
            background: #f1f1f1;
            border-color: #ccc;
            color: #333;
        }
        
        .katahdin-forms-status {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        
        .katahdin-forms-status.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .katahdin-forms-status.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Test forms endpoint
            $('#test-forms').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('Testing...');
                
                $.ajax({
                    url: katahdin_ai_forms.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'katahdin_ai_forms_test_forms',
                        nonce: katahdin_ai_forms.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#test-results').removeClass('error').addClass('success').html('<strong>Success:</strong> ' + response.data.message).show();
                        } else {
                            $('#test-results').removeClass('success').addClass('error').html('<strong>Error:</strong> ' + response.data).show();
                        }
                    },
                    error: function() {
                        $('#test-results').removeClass('success').addClass('error').html('<strong>Error:</strong> Request failed').show();
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Test Forms Endpoint');
                    }
                });
            });
            
            // Test email
            $('#test-email').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('Testing...');
                
                $.ajax({
                    url: katahdin_ai_forms.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'katahdin_ai_forms_test_email',
                        nonce: katahdin_ai_forms.nonce
                    },
                    success: function(response) {
                    if (response.success) {
                            $('#test-results').removeClass('error').addClass('success').html('<strong>Success:</strong> ' + response.data.message).show();
                    } else {
                            $('#test-results').removeClass('success').addClass('error').html('<strong>Error:</strong> ' + response.data).show();
                        }
                    },
                    error: function() {
                        $('#test-results').removeClass('success').addClass('error').html('<strong>Error:</strong> Request failed').show();
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Test Email');
                    }
                });
            });
            
            // Comprehensive debug
            $('#comprehensive-debug').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('Debugging...');
                
                $.ajax({
                    url: katahdin_ai_forms.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'katahdin_ai_forms_comprehensive_debug',
                        nonce: katahdin_ai_forms.nonce
                    },
                    success: function(response) {
                    if (response.success) {
                            $('#test-results').removeClass('error').addClass('success').html('<strong>Debug Complete:</strong><br><pre>' + JSON.stringify(response.data, null, 2) + '</pre>').show();
                    } else {
                            $('#test-results').removeClass('success').addClass('error').html('<strong>Debug Error:</strong> ' + response.data).show();
                        }
                    },
                    error: function() {
                        $('#test-results').removeClass('success').addClass('error').html('<strong>Error:</strong> Debug request failed').show();
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Comprehensive Debug');
                    }
                });
            });
            
            // Copy buttons
            $('.copy-btn').on('click', function() {
                var button = $(this);
                var targetId = button.data('target');
                var targetField = $('#' + targetId);
                var textToCopy = targetField.val();
                
                // Copy to clipboard
                navigator.clipboard.writeText(textToCopy).then(function() {
                    // Show success state
                    button.addClass('copied');
                    button.find('.copy-icon').text('✓');
                    
                    // Reset after 2 seconds
                    setTimeout(function() {
                        button.removeClass('copied');
                        button.find('.copy-icon').text('📋');
                    }, 2000);
                    
                    // Show success message
                    $('#test-results').removeClass('error').addClass('success').html('<strong>Success:</strong> Copied to clipboard!').show();
                    setTimeout(function() {
                        $('#test-results').fadeOut();
                    }, 3000);
                    
                }).catch(function(err) {
                    // Fallback for older browsers
                    targetField.select();
                    document.execCommand('copy');
                    
                    button.addClass('copied');
                    button.find('.copy-icon').text('✓');
                    
                    setTimeout(function() {
                        button.removeClass('copied');
                        button.find('.copy-icon').text('📋');
                    }, 2000);
                    
                    $('#test-results').removeClass('error').addClass('success').html('<strong>Success:</strong> Copied to clipboard!').show();
                    setTimeout(function() {
                        $('#test-results').fadeOut();
                    }, 3000);
                });
            });
            
            // Regenerate secret
            $('#regenerate-secret').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('Regenerating...');
                
                $.ajax({
                    url: katahdin_ai_forms.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'katahdin_ai_forms_regenerate_secret',
                        nonce: katahdin_ai_forms.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#webhook-secret').val(response.data.secret);
                            $('#test-results').removeClass('error').addClass('success').html('<strong>Success:</strong> ' + response.data.message).show();
                            } else {
                            $('#test-results').removeClass('success').addClass('error').html('<strong>Error:</strong> ' + response.data).show();
                        }
                    },
                    error: function() {
                        $('#test-results').removeClass('success').addClass('error').html('<strong>Error:</strong> Request failed').show();
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Regenerate Secret');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Add admin styles
     */
    public function add_admin_styles() {
        ?>
        <style>
        .katahdin-forms-header h1 {
            color: #23282d;
        }
        
        .katahdin-forms-section {
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .katahdin-forms-button:hover {
            opacity: 0.8;
        }
        
        .katahdin-forms-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        </style>
        <?php
    }
    
    /**
     * Prompts management page
     */
    public function prompts_page() {
            if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
        $prompts = $form_prompts->get_all_prompts();
        $stats = $form_prompts->get_prompt_stats();
        
        ?>
        <div class="wrap">
            <div class="katahdin-forms-header">
                <h1>Form Prompts</h1>
                <p class="description">Manage AI prompts for form processing. Each prompt can have its own email settings.</p>
            </div>
            
            <div class="katahdin-forms-content">
                <div class="katahdin-forms-section">
                    <h2>Add New Prompt</h2>
                    <form id="add-prompt-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">Title</th>
                                <td><input type="text" id="prompt-title" name="title" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th scope="row">Prompt ID</th>
                                <td><input type="text" id="prompt-prompt-id" name="prompt_id" class="regular-text" placeholder="e.g., contact_form_123" required></td>
                            </tr>
                            <tr>
                                <th scope="row">AI Prompt</th>
                                <td><textarea id="prompt-text" name="prompt" rows="5" class="large-text" required></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row">Email Address</th>
                                <td><input type="email" id="prompt-email" name="email_address" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th scope="row">Email Subject</th>
                                <td><input type="text" id="prompt-subject" name="email_subject" class="regular-text" required></td>
                            </tr>
                        </table>
                        <p class="submit">
                            <button type="submit" class="button katahdin-forms-button primary">Add Prompt</button>
                        </p>
                    </form>
                </div>
                
                <div class="katahdin-forms-section">
                    <h2>Existing Prompts (<?php echo intval($stats['total_prompts']); ?>)</h2>
                    <div id="prompts-list">
                        <?php $this->render_prompts_list($prompts); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Add prompt
            $('#add-prompt-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = {
                    action: 'katahdin_ai_forms_add_prompt',
                    nonce: katahdin_ai_forms.nonce,
                    title: $('#prompt-title').val(),
                    prompt_id: $('#prompt-prompt-id').val(),
                    prompt: $('#prompt-text').val(),
                    email_address: $('#prompt-email').val(),
                    email_subject: $('#prompt-subject').val()
                };
                
                $.ajax({
                    url: katahdin_ai_forms.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Request failed');
                    }
                });
            });
            
            // Delete prompt
            $(document).on('click', '.delete-prompt', function() {
                if (confirm('Are you sure you want to delete this prompt?')) {
                    var id = $(this).data('id');
                    
                    $.ajax({
                        url: katahdin_ai_forms.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'katahdin_ai_forms_delete_prompt',
                            nonce: katahdin_ai_forms.nonce,
                            id: id
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('Request failed');
                        }
                    });
                }
            });
            
            // Toggle prompt status
            $(document).on('click', '.toggle-prompt', function() {
                var id = $(this).data('id');
                
                $.ajax({
                    url: katahdin_ai_forms.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'katahdin_ai_forms_toggle_prompt',
                        nonce: katahdin_ai_forms.nonce,
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Request failed');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Logs management page
     */
    public function logs_page() {
            if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $logger = new Katahdin_AI_Forms_Logger();
        $logs = $logger->get_logs(50);
        $stats = $logger->get_log_stats();
        
        ?>
        <div class="wrap katahdin-forms-logs-page">
            <div class="katahdin-forms-header">
                <h1>Form Logs</h1>
                <p class="description">View and manage form submission logs.</p>
            </div>
            
            <div class="katahdin-forms-content">
                <div class="katahdin-forms-section">
                    <h2>Log Statistics</h2>
                    <div class="katahdin-forms-card">
                        <div class="log-stats-grid">
                            <div class="log-stat-item">
                                <div class="log-stat-value"><?php echo intval($stats['total']); ?></div>
                                <div class="log-stat-label">Total Logs</div>
                            </div>
                            <div class="log-stat-item">
                                <div class="log-stat-value"><?php echo intval($stats['recent_24h']); ?></div>
                                <div class="log-stat-label">Recent (24h)</div>
                            </div>
                            <div class="log-stat-item">
                                <div class="log-stat-value"><?php echo floatval($stats['email_success_rate']); ?>%</div>
                                <div class="log-stat-label">Email Success Rate</div>
                            </div>
                            <div class="log-stat-item">
                                <div class="log-stat-value"><?php echo floatval($stats['avg_processing_time_ms']); ?>ms</div>
                                <div class="log-stat-label">Avg Processing Time</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="katahdin-forms-section">
                    <h2>Recent Logs</h2>
                    <div class="logs-table-container">
                        <div id="logs-list">
                            <?php $this->render_logs_list($logs); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render prompts list
     */
    private function render_prompts_list($prompts) {
        if (empty($prompts)) {
            echo '<p>No prompts found. Add your first prompt above.</p>';
                return;
            }
            
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Prompt ID</th>
                    <th>Email Address</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prompts as $prompt): ?>
                <tr>
                    <td><strong><?php echo esc_html($prompt['title']); ?></strong></td>
                    <td><code><?php echo esc_html($prompt['prompt_id']); ?></code></td>
                    <td><?php echo esc_html($prompt['email_address']); ?></td>
                    <td>
                        <span class="katahdin-forms-status <?php echo $prompt['is_active'] ? 'success' : 'error'; ?>">
                            <?php echo $prompt['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td><?php echo esc_html(date('M j, Y', strtotime($prompt['created_at']))); ?></td>
                    <td>
                        <button class="button button-small toggle-prompt" data-id="<?php echo intval($prompt['id']); ?>">
                            <?php echo $prompt['is_active'] ? 'Deactivate' : 'Activate'; ?>
                        </button>
                        <button class="button button-small delete-prompt" data-id="<?php echo intval($prompt['id']); ?>">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    
    /**
     * Render logs list
     */
    private function render_logs_list($logs) {
        if (empty($logs)) {
            echo '<div class="no-logs-message">No logs found.</div>';
                return;
            }
            
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Form ID</th>
                    <th>Email</th>
                    <th>Processing Time</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><span class="log-id">#<?php echo intval($log['id']); ?></span></td>
                    <td>
                        <span class="status-badge <?php echo esc_attr($log['status']); ?>">
                            <?php echo esc_html(ucfirst($log['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($log['form_id']): ?>
                            <span class="form-id"><?php echo esc_html($log['form_id']); ?></span>
                        <?php else: ?>
                            <span class="log-date">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($log['form_email']): ?>
                            <?php echo esc_html($log['form_email']); ?>
                        <?php else: ?>
                            <span class="log-date">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="processing-time"><?php echo intval($log['processing_time_ms']); ?>ms</span>
                    </td>
                    <td>
                        <span class="log-date"><?php echo esc_html(date('M j, Y H:i', strtotime($log['created_at']))); ?></span>
                    </td>
                    <td>
                        <button class="button button-small view-log-btn view-log" data-id="<?php echo intval($log['id']); ?>">View</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    
    // AJAX Handlers
    public function ajax_test_forms() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        try {
            $forms_handler = katahdin_ai_forms()->forms_handler;
            if (!$forms_handler) {
                wp_send_json_error('Forms handler not available');
            }
            
            $test_data = array(
                        'name' => 'Test User',
                        'email' => 'test@example.com',
                'message' => 'This is a test form submission'
            );
            
            $result = $forms_handler->process_data($test_data, 'test_prompt', 'test_entry_123');
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }
            
            wp_send_json_success(array(
                'message' => 'Forms endpoint test successful',
                'analysis_id' => $result['analysis_id'] ?? 'N/A'
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Test failed: ' . $e->getMessage());
        }
    }
    
    public function ajax_test_email() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        try {
            $email_sender = new Katahdin_AI_Forms_Email_Sender();
            $result = $email_sender->send_test_email();
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            }
            
            wp_send_json_success(array(
                'message' => 'Test email sent successfully'
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Email test failed: ' . $e->getMessage());
        }
    }
    
    public function ajax_regenerate_secret() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        try {
        $new_secret = wp_generate_password(32, false);
            update_option('katahdin_ai_forms_webhook_secret', $new_secret);
            
            wp_send_json_success(array(
                'secret' => $new_secret,
                'message' => 'Webhook secret regenerated successfully'
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Failed to regenerate secret: ' . $e->getMessage());
        }
    }
    
    public function ajax_comprehensive_debug() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
        try {
            $debug_info = array(
                'plugin_version' => KATAHDIN_AI_FORMS_VERSION,
                'wordpress_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
                'forms_enabled' => get_option('katahdin_ai_forms_enabled', true),
                'hub_available' => function_exists('katahdin_ai_hub'),
                'forms_url' => rest_url('katahdin-ai-forms/v1/forms'),
                'timestamp' => current_time('mysql')
            );
        
        wp_send_json_success($debug_info);
        
        } catch (Exception $e) {
            wp_send_json_error('Debug failed: ' . $e->getMessage());
        }
    }
    
    public function ajax_add_prompt() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
        try {
            $title = sanitize_text_field($_POST['title']);
            $prompt_id = sanitize_text_field($_POST['prompt_id']);
            $prompt = sanitize_textarea_field($_POST['prompt']);
            $email_address = sanitize_email($_POST['email_address']);
            $email_subject = sanitize_text_field($_POST['email_subject']);
            
            if (empty($title) || empty($prompt_id) || empty($prompt) || empty($email_address) || empty($email_subject)) {
                wp_send_json_error('All fields are required');
            }
            
            $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
            $result = $form_prompts->add_prompt($title, $prompt_id, $prompt, $email_address, $email_subject);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }
            
            wp_send_json_success(array(
                'message' => 'Prompt added successfully',
                'id' => $result
            ));
        
        } catch (Exception $e) {
            wp_send_json_error('Failed to add prompt: ' . $e->getMessage());
        }
    }
    
    public function ajax_delete_prompt() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        try {
            $id = intval($_POST['id']);
            
            $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
            $result = $form_prompts->delete_prompt($id);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }
            
            wp_send_json_success(array(
                'message' => 'Prompt deleted successfully'
            ));
            
                    } catch (Exception $e) {
            wp_send_json_error('Failed to delete prompt: ' . $e->getMessage());
        }
    }
    
    public function ajax_toggle_prompt() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        try {
            $id = intval($_POST['id']);
            
            $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
            $result = $form_prompts->toggle_prompt_status($id);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }
            
            wp_send_json_success(array(
                'message' => 'Prompt status updated successfully',
                'new_status' => $result
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Failed to toggle prompt: ' . $e->getMessage());
        }
    }
    
    // Additional AJAX handlers for logs
    public function ajax_get_logs() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $limit = intval($_POST['limit'] ?? 50);
        $offset = intval($_POST['offset'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        
        $logger = new Katahdin_AI_Forms_Logger();
        $logs = $logger->get_logs($limit, $offset, $status);
        
        wp_send_json_success($logs);
    }
    
    public function ajax_get_log_stats() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $logger = new Katahdin_AI_Forms_Logger();
        $stats = $logger->get_log_stats();
        
        wp_send_json_success($stats);
    }
    
    public function ajax_cleanup_logs() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $retention_days = intval($_POST['retention_days'] ?? 30);
        
        $logger = new Katahdin_AI_Forms_Logger();
        $result = $logger->cleanup_logs($retention_days);
        
        wp_send_json_success($result);
    }
    
    public function ajax_get_log_details() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $log_id = intval($_POST['log_id']);
        
        $logger = new Katahdin_AI_Forms_Logger();
        $log = $logger->get_log_by_id($log_id);
        
        if (!$log) {
            wp_send_json_error('Log not found');
        }
        
        wp_send_json_success($log);
    }
    
    public function ajax_clear_all_logs() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
        $logger = new Katahdin_AI_Forms_Logger();
        $result = $logger->clear_all_logs();
        
        wp_send_json_success(array(
            'success' => $result,
            'message' => $result ? 'All logs cleared' : 'Failed to clear logs'
        ));
    }
    
    public function ajax_delete_log() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
        $log_id = intval($_POST['log_id']);
        
        $logger = new Katahdin_AI_Forms_Logger();
        $result = $logger->delete_log($log_id);
        
        wp_send_json_success(array(
            'success' => $result,
            'message' => $result ? 'Log deleted' : 'Failed to delete log'
        ));
    }
    
    public function ajax_get_prompts() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
            $prompts = $form_prompts->get_all_prompts();
        
        wp_send_json_success($prompts);
    }
    
    public function ajax_get_prompt_by_id() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $id = intval($_POST['id']);
        
        $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
        $prompt = $form_prompts->get_prompt_by_id($id);
        
        if (!$prompt) {
            wp_send_json_error('Prompt not found');
        }
        
        wp_send_json_success($prompt);
    }
    
    public function ajax_update_prompt() {
        check_ajax_referer('katahdin_ai_forms_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        try {
            $id = intval($_POST['id']);
            $title = sanitize_text_field($_POST['title']);
            $prompt_id = sanitize_text_field($_POST['prompt_id']);
            $prompt = sanitize_textarea_field($_POST['prompt']);
            $email_address = sanitize_email($_POST['email_address']);
            $email_subject = sanitize_text_field($_POST['email_subject']);
            $is_active = intval($_POST['is_active'] ?? 1);
            
            if (empty($title) || empty($prompt_id) || empty($prompt) || empty($email_address) || empty($email_subject)) {
                wp_send_json_error('All fields are required');
            }
            
            $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
            $result = $form_prompts->update_prompt($id, $title, $prompt_id, $prompt, $email_address, $email_subject, $is_active);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }
            
            wp_send_json_success(array(
                'message' => 'Prompt updated successfully'
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Failed to update prompt: ' . $e->getMessage());
        }
    }
    
    /**
     * Instructions page
     */
    public function instructions_page() {
        $forms_url = rest_url('katahdin-ai-forms/v1/forms');
        $webhook_secret = get_option('katahdin_ai_forms_webhook_secret', '');
        ?>
        <div class="wrap katahdin-forms-admin">
            <div class="instructions-hero">
                <div class="hero-content">
                    <h1>🚀 Katahdin AI Forms Setup</h1>
                    <p class="hero-subtitle">Transform your FluentForm submissions with AI-powered analysis and automated email notifications</p>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">4</div>
                        <div class="stat-label">Simple Steps</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">5</div>
                        <div class="stat-label">Minutes Setup</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">∞</div>
                        <div class="stat-label">AI Analysis</div>
                    </div>
                </div>
            </div>
            
            <div class="instructions-container">
                <!-- Quick Start Section -->
                <div class="quick-start-section">
                    <div class="section-header">
                        <h2>⚡ Quick Start</h2>
                        <p>Get your AI-powered forms running in minutes</p>
                    </div>
                    
                    <div class="endpoint-cards">
                        <div class="endpoint-card">
                            <div class="card-header">
                                <div class="card-icon">🔗</div>
                                <h3>Forms URL</h3>
                            </div>
                            <div class="card-content">
                                <div class="copy-field-container">
                                    <input type="text" id="forms-url" value="<?php echo esc_url($forms_url); ?>" readonly class="copy-field">
                                    <button type="button" class="copy-btn" data-target="forms-url">
                                        <span class="copy-icon">📋</span>
                                        <span class="copy-text">Copy</span>
                                    </button>
                                </div>
                                <p class="field-description">Use this URL in your FluentForm webhook settings</p>
                            </div>
                        </div>
                        
                        <div class="endpoint-card">
                            <div class="card-header">
                                <div class="card-icon">🔐</div>
                                <h3>Webhook Secret</h3>
                            </div>
                            <div class="card-content">
                                <div class="copy-field-container">
                                    <input type="text" id="webhook-secret" value="<?php echo esc_attr($webhook_secret); ?>" readonly class="copy-field">
                                    <button type="button" class="copy-btn" data-target="webhook-secret">
                                        <span class="copy-icon">📋</span>
                                        <span class="copy-text">Copy</span>
                                    </button>
                                </div>
                                <p class="field-description">Add this as X-Webhook-Secret header</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step-by-Step Instructions -->
                <div class="steps-section">
                    <div class="section-header">
                        <h2>📋 Step-by-Step Setup</h2>
                        <p>Follow these steps to configure your AI-powered forms</p>
                    </div>
                    
                    <div class="steps-container">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <div class="step-header">
                                    <h3>Create Your AI Prompt</h3>
                                    <div class="step-badge">Foundation</div>
                                </div>
                                <p>Set up your AI analysis prompt and get your unique Prompt ID</p>
                                <div class="step-actions">
                                    <a href="<?php echo admin_url('admin.php?page=katahdin-ai-forms-prompts'); ?>" class="action-button primary">
                                        <span class="button-icon">➕</span>
                                        Create Prompt
                                    </a>
                                </div>
                                <div class="step-tip">
                                    <div class="tip-icon">💡</div>
                                    <div class="tip-content">
                                        <strong>Pro Tip:</strong> Use descriptive Prompt IDs like "contact_form_analysis" or "support_ticket_review"
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <div class="step-header">
                                    <h3>Configure FluentForm Webhook</h3>
                                    <div class="step-badge">Integration</div>
                                </div>
                                <p>Add a webhook action to your FluentForm with these settings:</p>
                                <div class="settings-grid">
                                    <div class="setting-card">
                                        <div class="setting-icon">🌐</div>
                                        <div class="setting-info">
                                            <div class="setting-label">Webhook URL</div>
                                            <div class="setting-value">Use the Forms URL above</div>
                                        </div>
                                    </div>
                                    <div class="setting-card">
                                        <div class="setting-icon">📤</div>
                                        <div class="setting-info">
                                            <div class="setting-label">Method</div>
                                            <div class="setting-value">POST</div>
                                        </div>
                                    </div>
                                    <div class="setting-card">
                                        <div class="setting-icon">📋</div>
                                        <div class="setting-info">
                                            <div class="setting-label">Body</div>
                                            <div class="setting-value">All Fields</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="step-card highlight">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <div class="step-header">
                                    <h3>Add Prompt ID Header</h3>
                                    <div class="step-badge recommended">Recommended</div>
                                </div>
                                <div class="recommendation-banner">
                                    <div class="banner-icon">✅</div>
                                    <div class="banner-content">
                                        <strong>Best Practice:</strong> Add <code>prompt_id</code> as a Request Header for easy FluentForm configuration!
                                    </div>
                                </div>
                                
                                <p>In FluentForm webhook settings, add these headers:</p>
                                <div class="code-block">
                                    <div class="code-header">
                                        <div class="code-title">
                                            <span class="code-icon">📝</span>
                                            Request Headers Configuration
                                        </div>
                                        <button type="button" class="copy-code-btn" data-target="webhook-headers-code">
                                            <span class="copy-icon">📋</span>
                                            Copy Headers
                                        </button>
                                    </div>
                                    <div class="code-content">
                                        <pre id="webhook-headers-code"><code>Header Name: X-Webhook-Secret
Header Value: [Your Webhook Secret]

Header Name: prompt_id
Header Value: your_prompt_id_here</code></pre>
                                    </div>
                                </div>
                                
                                <div class="step-tip">
                                    <div class="tip-icon">💡</div>
                                    <div class="tip-content">
                                        <strong>Remember:</strong> Replace "your_prompt_id_here" with the actual Prompt ID from step 1
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="step-card">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <div class="step-header">
                                    <h3>Test & Monitor</h3>
                                    <div class="step-badge">Validation</div>
                                </div>
                                <p>Submit a test form and monitor the results to ensure everything works perfectly</p>
                                <div class="step-actions">
                                    <a href="<?php echo admin_url('admin.php?page=katahdin-ai-forms-logs'); ?>" class="action-button secondary">
                                        <span class="button-icon">📊</span>
                                        View Logs
                                    </a>
                                </div>
                                <div class="step-tip">
                                    <div class="tip-icon">📧</div>
                                    <div class="tip-content">
                                        <strong>Automatic:</strong> AI analysis results are automatically emailed to the address configured in your prompt
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Important Notes -->
                <div class="important-section">
                    <div class="important-header">
                        <div class="important-icon">⚠️</div>
                        <h2>Important Notes</h2>
                    </div>
                    <div class="important-content">
                        <div class="note-grid">
                            <div class="note-item">
                                <div class="note-icon">🔑</div>
                                <div class="note-content">
                                    <strong>Prompt ID Required</strong>
                                    <p>Without it, form submissions will fail</p>
                                </div>
                            </div>
                            <div class="note-item">
                                <div class="note-icon">✅</div>
                                <div class="note-content">
                                    <strong>Active Prompts Only</strong>
                                    <p>Only active prompts will process forms</p>
                                </div>
                            </div>
                            <div class="note-item">
                                <div class="note-icon">📧</div>
                                <div class="note-content">
                                    <strong>Automatic Emails</strong>
                                    <p>Results are sent to prompt's configured address</p>
                                </div>
                            </div>
                            <div class="note-item">
                                <div class="note-icon">📊</div>
                                <div class="note-content">
                                    <strong>Monitor Logs</strong>
                                    <p>Check processing results in Form Logs</p>
                                </div>
                            </div>
                            <div class="note-item">
                                <div class="note-icon">🎯</div>
                                <div class="note-content">
                                    <strong>Headers Preferred</strong>
                                    <p>Using prompt_id in headers is easier in FluentForm</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Copy functionality
            $('.copy-btn').on('click', function() {
                var button = $(this);
                var targetId = button.data('target');
                var targetElement = $('#' + targetId);
                var textToCopy = targetElement.val();
                
                navigator.clipboard.writeText(textToCopy).then(function() {
                    button.addClass('copied');
                    button.find('.copy-icon').text('✓');
                    
                    setTimeout(function() {
                        button.removeClass('copied');
                        button.find('.copy-icon').text('📋');
                    }, 2000);
                }).catch(function(err) {
                    // Fallback for older browsers
                    targetElement.select();
                    document.execCommand('copy');
                    
                    button.addClass('copied');
                    button.find('.copy-icon').text('✓');
                    
                    setTimeout(function() {
                        button.removeClass('copied');
                        button.find('.copy-icon').text('📋');
                    }, 2000);
                });
            });
            
            // Copy code buttons
            $('.copy-code-btn').on('click', function() {
                var button = $(this);
                var targetId = button.data('target');
                var targetElement = $('#' + targetId);
                var textToCopy = targetElement.text();
                
                navigator.clipboard.writeText(textToCopy).then(function() {
                    button.addClass('copied');
                    button.text('✓ Copied');
                    
                    setTimeout(function() {
                        button.removeClass('copied');
                        button.text('📋 Copy');
                    }, 2000);
                }).catch(function(err) {
                    // Fallback for older browsers
                    targetElement.select();
                    document.execCommand('copy');
                    
                    button.addClass('copied');
                    button.text('✓ Copied');
                    
                    setTimeout(function() {
                        button.removeClass('copied');
                        button.text('📋 Copy');
                    }, 2000);
                });
            });
        });
        </script>
        <?php
    }
}
