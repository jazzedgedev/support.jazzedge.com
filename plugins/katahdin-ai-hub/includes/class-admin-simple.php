<?php
/**
 * Admin Interface for Katahdin AI Hub
 * Provides WordPress admin interface for managing the hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Katahdin_AI_Hub_Admin')) {
class Katahdin_AI_Hub_Admin {
    
    /**
     * Initialize Admin Interface
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_katahdin_ai_hub_test_api', array($this, 'ajax_test_api'));
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
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_openai_key');
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_debug_mode');
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_usage_limit');
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_rate_limit');
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
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
        </div>
        
        <style>
        .katahdin-card {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            margin-bottom: 20px;
        }
        
        .katahdin-card h2 {
            margin-top: 0;
            color: #1d2327;
            border-bottom: 1px solid #f0f0f1;
            padding-bottom: 10px;
        }
        
        .api-test-result {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
            display: none;
            border: 1px solid;
        }
        
        .api-test-result.success {
            background: #d1e7dd;
            color: #0f5132;
            border-color: #badbcc;
        }
        
        .api-test-result.error {
            background: #f8d7da;
            color: #842029;
            border-color: #f5c2c7;
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
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_hub_nonce'); ?>'
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
        
        $api_key = get_option('katahdin_ai_hub_openai_key');
        
        if (empty($api_key)) {
            wp_send_json_error('API key not configured');
        }
        
        // Test API connection
        $response = wp_remote_get('https://api.openai.com/v1/models', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        if ($response_code === 200) {
            wp_send_json_success(array(
                'message' => 'Connection successful!',
                'models_count' => count($response_data['data'] ?? [])
            ));
        } else {
            wp_send_json_error($response_data['error']['message'] ?? 'Connection failed');
        }
    }
}
}
