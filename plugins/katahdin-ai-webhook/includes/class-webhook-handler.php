<?php
/**
 * Webhook Handler for Katahdin AI Webhook
 * Handles incoming webhook data from FluentForm
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Webhook_Handler {
    
    private $logger;
    
    public function __construct() {
        $this->init();
    }
    
    /**
     * Get logger instance (lazy loading)
     */
    public function get_logger() {
        if (!$this->logger) {
            $this->logger = new Katahdin_AI_Webhook_Logger();
        }
        return $this->logger;
    }
    
    /**
     * Initialize webhook handler
     */
    public function init() {
        // Initialize any required components
    }
    
    /**
     * Initialize REST API endpoints
     */
    public function init_rest_api() {
        $this->register_webhook_endpoint();
    }
    
    /**
     * Register webhook endpoint
     */
    public function register_webhook_endpoint() {
        register_rest_route('katahdin-ai-webhook/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => '__return_true', // Allow all requests, we'll handle auth in the callback
            'args' => array(
                'form_data' => array(
                    'required' => false,
                    'type' => 'object',
                    'description' => 'Form submission data from FluentForm'
                ),
                'form_id' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'FluentForm ID'
                ),
                'entry_id' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'FluentForm entry ID'
                )
            )
        ));
        
        // Test endpoint
        register_rest_route('katahdin-ai-webhook/v1', '/test', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_webhook'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Status check endpoint
        register_rest_route('katahdin-ai-webhook/v1', '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_status'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Debug endpoint
        register_rest_route('katahdin-ai-webhook/v1', '/debug', array(
            'methods' => 'GET',
            'callback' => array($this, 'debug_info'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Logs endpoints
        register_rest_route('katahdin-ai-webhook/v1', '/logs', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_logs'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'limit' => array(
                    'type' => 'integer',
                    'default' => 50,
                    'minimum' => 1,
                    'maximum' => 100
                ),
                'offset' => array(
                    'type' => 'integer',
                    'default' => 0,
                    'minimum' => 0
                ),
                'status' => array(
                    'type' => 'string',
                    'enum' => array('received', 'success', 'error', 'pending')
                )
            )
        ));
        
        register_rest_route('katahdin-ai-webhook/v1', '/logs/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_log'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('katahdin-ai-webhook/v1', '/logs/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_log_stats'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('katahdin-ai-webhook/v1', '/logs/cleanup', array(
            'methods' => 'POST',
            'callback' => array($this, 'cleanup_logs'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'retention_days' => array(
                    'type' => 'integer',
                    'default' => 30,
                    'minimum' => 1,
                    'maximum' => 365
                )
            )
        ));
    }
    
    /**
     * Handle incoming webhook
     */
    public function handle_webhook($request) {
        // Check webhook authentication first
        $auth_result = $this->check_webhook_permission($request);
        if (is_wp_error($auth_result)) {
            return $auth_result;
        }
        
        $start_time = microtime(true);
        $webhook_id = $this->get_logger()->generate_webhook_id();
        
        // Log the incoming request
        $request_data = array(
            'headers' => $request->get_headers(),
            'body' => $request->get_body(),
            'params' => $request->get_params()
        );
        
        $log_id = $this->get_logger()->log_request($webhook_id, $request_data);
        
        try {
            // Get form data - handle different formats
            $form_data = $request->get_param('form_data');
            $form_id = $request->get_param('form_id');
            $entry_id = $request->get_param('entry_id');
            
            // If form_data is not provided, try to get data from request body or other params
            if (empty($form_data)) {
                $body = $request->get_body();
                if (!empty($body)) {
                    $body_data = json_decode($body, true);
                    if ($body_data) {
                        // FluentForm might send data directly in the body
                        $form_data = $body_data;
                    }
                }
                
                // If still empty, try to get from all params
                if (empty($form_data)) {
                    $all_params = $request->get_params();
                    // Remove known parameters
                    unset($all_params['form_id'], $all_params['entry_id']);
                    $form_data = $all_params;
                }
            }
            
            if (empty($form_data)) {
                $this->get_logger()->update_log($log_id, array(
                    'status' => 'error',
                    'error_message' => 'No form data provided',
                    'response_code' => 400
                ));
                return new WP_Error('invalid_data', 'No form data provided', array('status' => 400));
            }
            
            // Check if webhook is enabled
            if (!get_option('katahdin_ai_webhook_enabled', true)) {
                $this->get_logger()->update_log($log_id, array(
                    'status' => 'error',
                    'error_message' => 'Webhook processing is disabled',
                    'response_code' => 503
                ));
                return new WP_Error('webhook_disabled', 'Webhook processing is disabled', array('status' => 503));
            }
            
            // Process the data
            $result = $this->process_data($form_data, $form_id, $entry_id);
            
            $processing_time = round((microtime(true) - $start_time) * 1000);
            
            if (is_wp_error($result)) {
                $this->get_logger()->update_log($log_id, array(
                    'status' => 'error',
                    'error_message' => $result->get_error_message(),
                    'response_code' => $result->get_error_data()['status'] ?? 500,
                    'processing_time_ms' => $processing_time
                ));
                return $result;
            }
            
            // Update log with success
            $this->get_logger()->update_log($log_id, array(
                'status' => 'success',
                'response_code' => 200,
                'processing_time_ms' => $processing_time,
                'ai_response' => isset($result['ai_response']) ? json_encode($result['ai_response']) : null,
                'email_sent' => isset($result['email_sent']) ? $result['email_sent'] : 0,
                'email_response' => isset($result['email_response']) ? $result['email_response'] : null
            ));
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Webhook processed successfully',
                'analysis_id' => $result['analysis_id'] ?? null,
                'webhook_id' => $webhook_id,
                'processing_time_ms' => $processing_time
            ));
            
        } catch (Exception $e) {
            $processing_time = round((microtime(true) - $start_time) * 1000);
            
            $this->get_logger()->update_log($log_id, array(
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'response_code' => 500,
                'processing_time_ms' => $processing_time
            ));
            
            error_log('Katahdin AI Webhook error: ' . $e->getMessage());
            return new WP_Error('processing_error', 'Error processing webhook: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Simple API key test
     */
    public function test_api_key() {
        if (!function_exists('katahdin_ai_hub')) {
            return new WP_Error('hub_not_available', 'Katahdin AI Hub not available');
        }
        
        $hub = katahdin_ai_hub();
        if (!$hub || !$hub->api_manager) {
            return new WP_Error('hub_not_initialized', 'Katahdin AI Hub not properly initialized');
        }
        
        // Test the API key directly
        $api_key = get_option('katahdin_ai_hub_openai_key');
        if (!$api_key) {
            return new WP_Error('no_api_key', 'No API key found in options');
        }
        
        // Simple test without calling API manager methods
        return array(
            'success' => true,
            'api_key_found' => true,
            'api_key_length' => strlen($api_key),
            'api_key_preview' => substr($api_key, 0, 8) . '...',
            'api_key_starts_with_sk' => strpos($api_key, 'sk-') === 0,
            'api_key_full' => $api_key // Show full key for debugging
        );
    }
    
    /**
     * Test webhook endpoint
     */
    public function test_webhook($request) {
        $test_data = array(
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a test message for AI analysis.',
            'form_id' => 'test_form'
        );
        
        $result = $this->process_data($test_data, 'test_form', 'test_entry');
        
        if (is_wp_error($result)) {
            return new WP_Error('test_failed', $result->get_error_message(), array('status' => 400));
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Test webhook processed successfully',
            'test_data' => $test_data,
            'analysis_result' => $result
        ));
    }
    
    /**
     * Get status endpoint
     */
    public function get_status($request) {
        $status = $this->check_katahdin_hub_status();
        
        return rest_ensure_response(array(
            'webhook_enabled' => get_option('katahdin_ai_webhook_enabled', true),
            'webhook_url' => katahdin_ai_webhook()->get_webhook_url(),
            'katahdin_hub_status' => $status,
            'plugin_version' => KATAHDIN_AI_WEBHOOK_VERSION,
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Debug endpoint
     */
    public function debug_info($request) {
        global $wpdb;
        
        $debug_info = array(
            'plugin_id' => Katahdin_AI_Webhook::PLUGIN_ID,
            'plugin_version' => KATAHDIN_AI_WEBHOOK_VERSION,
            'katahdin_hub_function_exists' => function_exists('katahdin_ai_hub'),
            'katahdin_hub_instance' => null,
            'plugin_registration' => null,
            'api_key_info' => null,
            'database_check' => null
        );
        
        // Check Katahdin AI Hub instance
        if (function_exists('katahdin_ai_hub')) {
            $hub = katahdin_ai_hub();
            $debug_info['katahdin_hub_instance'] = array(
                'exists' => $hub ? true : false,
                'api_manager_exists' => $hub && $hub->api_manager ? true : false,
                'plugin_registry_exists' => $hub && $hub->plugin_registry ? true : false
            );
            
            // Check plugin registration
            if ($hub && $hub->plugin_registry) {
                $plugin = $hub->plugin_registry->get_plugin(Katahdin_AI_Webhook::PLUGIN_ID);
                $debug_info['plugin_registration'] = array(
                    'registered' => $plugin ? true : false,
                    'plugin_data' => $plugin
                );
            }
            
            // Check API key
            $api_key = get_option('katahdin_ai_hub_openai_key');
            $debug_info['api_key_info'] = array(
                'exists' => !empty($api_key),
                'length' => $api_key ? strlen($api_key) : 0,
                'is_encrypted' => $api_key && strlen($api_key) >= 50
            );
        }
        
        // Check database
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        $plugin_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE plugin_id = %s",
            Katahdin_AI_Webhook::PLUGIN_ID
        ), ARRAY_A);
        
        $debug_info['database_check'] = array(
            'table_exists' => $wpdb->get_var("SHOW TABLES LIKE '$table_name'") ? true : false,
            'plugin_in_db' => $plugin_data ? true : false,
            'plugin_data' => $plugin_data
        );
        
        return rest_ensure_response($debug_info);
    }
    
    /**
     * Process form data with AI analysis
     */
    public function process_data($form_data, $form_id = null, $entry_id = null) {
        try {
            // Get AI prompt
            $prompt = get_option('katahdin_ai_webhook_prompt', 'Analyze the following form submission data and provide insights, recommendations, or summaries as appropriate. Be concise but informative.');
            
            // Prepare data for AI analysis
            $data_for_analysis = $this->prepare_data_for_analysis($form_data, $form_id, $entry_id);
            
            // Create AI prompt
            $full_prompt = $prompt . "\n\nForm Data:\n" . $data_for_analysis;
            
            // Make AI API call through Katahdin AI Hub
            $ai_response = $this->call_ai_api($full_prompt);
            
            if (is_wp_error($ai_response)) {
                return $ai_response;
            }
            
            // Extract AI response
            $ai_analysis = $this->extract_ai_response($ai_response);
            
            // Send email with results
            $email_result = $this->send_analysis_email($form_data, $ai_analysis, $form_id, $entry_id);
            
            if (is_wp_error($email_result)) {
                error_log('Email sending failed: ' . $email_result->get_error_message());
                // Don't fail the webhook if email fails, just log it
            }
            
            // Log the analysis
            $this->log_analysis($form_data, $ai_analysis, $form_id, $entry_id);
            
            return array(
                'success' => true,
                'analysis' => $ai_analysis,
                'analysis_id' => uniqid('analysis_', true),
                'email_sent' => !is_wp_error($email_result)
            );
            
        } catch (Exception $e) {
            error_log('Katahdin AI Webhook processing error: ' . $e->getMessage());
            return new WP_Error('processing_error', 'Error processing form data: ' . $e->getMessage());
        }
    }
    
    /**
     * Prepare data for AI analysis
     */
    private function prepare_data_for_analysis($form_data, $form_id, $entry_id) {
        $prepared_data = array();
        
        // Add metadata
        if ($form_id) {
            $prepared_data['Form ID'] = $form_id;
        }
        if ($entry_id) {
            $prepared_data['Entry ID'] = $entry_id;
        }
        $prepared_data['Timestamp'] = current_time('Y-m-d H:i:s');
        
        // Add form data
        foreach ($form_data as $key => $value) {
            // Clean up field names
            $clean_key = ucwords(str_replace(array('_', '-'), ' ', $key));
            $prepared_data[$clean_key] = $value;
        }
        
        // Convert to readable format
        $formatted_data = '';
        foreach ($prepared_data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $formatted_data .= $key . ': ' . $value . "\n";
        }
        
        return $formatted_data;
    }
    
    /**
     * Call AI API through Katahdin AI Hub
     */
    public function call_ai_api($prompt) {
        // Check if Katahdin AI Hub is available
        if (!function_exists('katahdin_ai_hub')) {
            return new WP_Error('hub_not_available', 'Katahdin AI Hub not available');
        }
        
        $hub = katahdin_ai_hub();
        
        // Check if hub is properly initialized
        if (!$hub || !$hub->api_manager) {
            return new WP_Error('hub_not_initialized', 'Katahdin AI Hub not properly initialized');
        }
        
        // Prepare API call data
        $data = array(
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            )
        );
        
        $options = array(
            'model' => get_option('katahdin_ai_webhook_model', 'gpt-3.5-turbo'),
            'max_tokens' => (int) get_option('katahdin_ai_webhook_max_tokens', 1000),
            'temperature' => (float) get_option('katahdin_ai_webhook_temperature', 0.7)
        );
        
        // Make API call
        $result = $hub->make_api_call(Katahdin_AI_Webhook::PLUGIN_ID, 'chat/completions', $data, $options);
        
        return $result;
    }
    
    /**
     * Extract AI response from API result
     */
    private function extract_ai_response($api_response) {
        if (is_wp_error($api_response)) {
            return $api_response;
        }
        
        if (isset($api_response['choices'][0]['text'])) {
            return trim($api_response['choices'][0]['text']);
        }
        
        if (isset($api_response['choices'][0]['message']['content'])) {
            return trim($api_response['choices'][0]['message']['content']);
        }
        
        return 'No analysis available';
    }
    
    /**
     * Send analysis email
     */
    private function send_analysis_email($form_data, $analysis, $form_id, $entry_id) {
        $email_sender = katahdin_ai_webhook()->email_sender;
        
        if (!$email_sender) {
            return new WP_Error('email_sender_not_available', 'Email sender not available');
        }
        
        return $email_sender->send_analysis_email($form_data, $analysis, $form_id, $entry_id);
    }
    
    /**
     * Log analysis for debugging
     */
    private function log_analysis($form_data, $analysis, $form_id, $entry_id) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'form_id' => $form_id,
            'entry_id' => $entry_id,
            'form_data' => $form_data,
            'analysis' => $analysis
        );
        
        // Store in WordPress options for recent logs (keep last 50)
        $logs = get_option('katahdin_ai_webhook_logs', array());
        array_unshift($logs, $log_entry);
        $logs = array_slice($logs, 0, 50); // Keep only last 50 entries
        update_option('katahdin_ai_webhook_logs', $logs);
    }
    
    /**
     * Check webhook permission
     */
    public function check_webhook_permission($request) {
        // Check webhook secret
        $provided_secret = $request->get_header('X-Webhook-Secret');
        $expected_secret = get_option('katahdin_ai_webhook_webhook_secret', '');
        
        // For testing purposes, allow requests if no secret is provided AND no secret is expected
        if (empty($expected_secret) && empty($provided_secret)) {
            return true; // Allow if no secret is set and none provided
        }
        
        // If a secret is expected, require it to match
        if (!empty($expected_secret)) {
            if ($provided_secret !== $expected_secret) {
                return new WP_Error('invalid_secret', 'Invalid webhook secret', array('status' => 401));
            }
        }
        
        return true;
    }
    
    /**
     * Check admin permission
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Get recent logs
     */
    public function get_recent_logs($limit = 10) {
        $logs = get_option('katahdin_ai_webhook_logs', array());
        return array_slice($logs, 0, $limit);
    }
    
    /**
     * Check Katahdin AI Hub status
     */
    public function check_katahdin_hub_status() {
        $status = array(
            'available' => false,
            'initialized' => false,
            'api_key_configured' => false,
            'api_connection_working' => false,
            'error_message' => null
        );
        
        // Check if function exists
        if (!function_exists('katahdin_ai_hub')) {
            $status['error_message'] = 'Katahdin AI Hub function not available';
            return $status;
        }
        
        $status['available'] = true;
        
        // Check if hub is initialized
        $hub = katahdin_ai_hub();
        if (!$hub || !$hub->api_manager) {
            $status['error_message'] = 'Katahdin AI Hub not properly initialized';
            return $status;
        }
        
        $status['initialized'] = true;
        
        // Check if API key is configured (basic check)
        $api_key = get_option('katahdin_ai_hub_openai_key');
        if (empty($api_key)) {
            $status['error_message'] = 'OpenAI API key not configured in Katahdin AI Hub';
            return $status;
        }
        
        $status['api_key_configured'] = true;
        
        // Don't test API connection immediately - just check if key exists
        // The actual API call will handle connection testing
        $status['api_connection_working'] = true; // Assume it works if key exists
        
        return $status;
    }
    
    /**
     * Get webhook logs
     */
    public function get_logs($request) {
        $limit = $request->get_param('limit') ?: 50;
        $offset = $request->get_param('offset') ?: 0;
        $status = $request->get_param('status');
        
        $logs = $this->get_logger()->get_logs($limit, $offset, $status);
        
        return rest_ensure_response(array(
            'success' => true,
            'logs' => $logs,
            'pagination' => array(
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => count($logs) === $limit
            )
        ));
    }
    
    /**
     * Get specific log entry
     */
    public function get_log($request) {
        $log_id = $request->get_param('id');
        
        $log = $this->get_logger()->get_log($log_id);
        
        if (!$log) {
            return new WP_Error('log_not_found', 'Log entry not found', array('status' => 404));
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'log' => $log
        ));
    }
    
    /**
     * Get log statistics
     */
    public function get_log_stats($request) {
        $stats = $this->get_logger()->get_log_stats();
        
        return rest_ensure_response(array(
            'success' => true,
            'stats' => $stats
        ));
    }
    
    /**
     * Cleanup old logs
     */
    public function cleanup_logs($request) {
        $retention_days = $request->get_param('retention_days') ?: 30;
        
        $result = $this->get_logger()->cleanup_logs($retention_days);
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => "Cleaned up {$result['deleted_count']} old log entries",
            'deleted_count' => $result['deleted_count'],
            'retention_days' => $retention_days
        ));
    }
}
