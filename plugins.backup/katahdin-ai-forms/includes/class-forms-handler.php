<?php
/**
 * Forms Handler for Katahdin AI Forms
 * Handles incoming form data from FluentForm with security and prompt_id support
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Forms_Handler {
    
    private $logger;
    
    public function __construct() {
        $this->init();
    }
    
    /**
     * Get logger instance (lazy loading)
     */
    public function get_logger() {
        if (!$this->logger) {
            $this->logger = new Katahdin_AI_Forms_Logger();
        }
        return $this->logger;
    }
    
    /**
     * Initialize forms handler
     */
    public function init() {
        // Initialize any required components
    }
    
    /**
     * Initialize REST API endpoints
     */
    public function init_rest_api() {
        $this->register_forms_endpoint();
        $this->register_test_endpoints();
    }
    
    /**
     * Register forms endpoint
     */
    public function register_forms_endpoint() {
        register_rest_route('katahdin-ai-forms/v1', '/forms', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => array($this, 'check_forms_permission'),
            'args' => array(
                'form_data' => array(
                    'required' => false,
                    'type' => 'object',
                    'description' => 'Form submission data from FluentForm'
                ),
                'entry_id' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'FluentForm entry ID'
                )
            )
        ));
    }
    
    /**
     * Register test endpoints
     */
    public function register_test_endpoints() {
        register_rest_route('katahdin-ai-forms/v1', '/test', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_endpoint'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('katahdin-ai-forms/v1', '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'status_endpoint'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('katahdin-ai-forms/v1', '/debug', array(
            'methods' => 'GET',
            'callback' => array($this, 'debug_endpoint'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('katahdin-ai-forms/v1', '/logs', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_logs_endpoint'),
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
        
        register_rest_route('katahdin-ai-forms/v1', '/logs/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_log'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('katahdin-ai-forms/v1', '/logs/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_log_stats'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('katahdin-ai-forms/v1', '/logs/cleanup', array(
            'methods' => 'POST',
            'callback' => array($this, 'cleanup_logs'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'retention_days' => array(
                    'type' => 'integer',
                    'default' => 30,
                    'minimum' => 0
                )
            )
        ));
    }
    
    /**
     * Handle webhook request
     */
    public function handle_webhook($request) {
        // Check forms authentication first
        $auth_result = $this->check_forms_permission($request);
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
            // Get form data - handle different formats with proper sanitization
            $form_data = $request->get_param('form_data');
            $prompt_id = sanitize_text_field($request->get_param('prompt_id'));
            $entry_id = sanitize_text_field($request->get_param('entry_id'));
            
            // Check if prompt_id is in headers (primary method for FluentForm compatibility)
            if (empty($prompt_id)) {
                // Try different header name variations (WordPress converts to lowercase with underscores)
                $header_variations = [
                    'prompt_id',           // Standard format
                    'prompt-id',           // Hyphen format
                    'Prompt-ID',           // Capitalized hyphen
                    'x-prompt-id',         // X-prefixed format
                    'x-webhook-prompt-id', // Full X-webhook format
                    'X-Prompt-ID',         // Capitalized X format
                    'X-Webhook-Prompt-ID'  // Full capitalized format
                ];
                
                foreach ($header_variations as $header_name) {
                    $prompt_id = sanitize_text_field($request->get_header($header_name));
                    if (!empty($prompt_id)) {
                        if (defined('WP_DEBUG') && WP_DEBUG) {
                            error_log('Katahdin AI Forms Debug - Found prompt_id in header: ' . $header_name . ' = ' . $prompt_id);
                        }
                        break;
                    }
                }
            }
            
            // Debug: Log all headers to see what we're receiving
            $all_headers = $request->get_headers();
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Debug - All Headers: ' . print_r($all_headers, true));
                error_log('Katahdin AI Forms Debug - prompt_id from param: ' . ($prompt_id ?: 'empty'));
                error_log('Katahdin AI Forms Debug - prompt_id from header: ' . ($request->get_header('prompt_id') ?: 'empty'));
            }
            
            // Validate required parameters
            if (empty($prompt_id)) {
                $error_message = 'Prompt ID is missing! Check your FluentForm webhook configuration. Headers received: ' . implode(', ', array_keys($all_headers)) . '. Expected: prompt_id header with value "pirate"';
                // Don't try to update log if there are database issues
                if ($log_id && $log_id > 0) {
                    try {
                        $this->get_logger()->update_log($log_id, array('status' => 'error', 'error_message' => $error_message));
                    } catch (Exception $e) {
                        // Ignore logging errors for now
                    }
                }
                return new WP_Error('missing_prompt_id', $error_message, array('status' => 400));
            }
            
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
                    unset($all_params['prompt_id'], $all_params['entry_id']);
                    $form_data = $all_params;
                }
                
                // Sanitize form data recursively
                $form_data = $this->sanitize_form_data($form_data);
            }
            
            // Extract email and name from form data for easier database storage
            $extracted_data = $this->extract_form_data($form_data, $request->get_body());
            
            if (empty($form_data)) {
                $this->get_logger()->update_log($log_id, array(
                    'status' => 'error',
                    'response_code' => 400,
                    'error_message' => 'No form data provided',
                    'processing_time_ms' => round((microtime(true) - $start_time) * 1000)
                ));
                
                return new WP_Error('no_form_data', 'No form data provided', array('status' => 400));
            }
            
            // Process the data
            $result = $this->process_data($form_data, $prompt_id, $entry_id);
            
            $processing_time = round((microtime(true) - $start_time) * 1000);
            
            if (is_wp_error($result)) {
                $this->get_logger()->update_log($log_id, array(
                    'status' => 'error',
                    'response_code' => 500,
                    'error_message' => $result->get_error_message(),
                    'processing_time_ms' => $processing_time
                ));
                
                return $result;
            }
            
            // Update log with success
            $this->get_logger()->update_log($log_id, array(
                'status' => 'success',
                'response_code' => 200,
                'ai_response' => $result['analysis'],
                'email_sent' => $result['email_sent'],
                'processing_time_ms' => $processing_time,
                'form_email' => $extracted_data['email'],
                'form_name' => $extracted_data['name'],
                'form_id' => $prompt_id,
                'entry_id' => $entry_id
            ));
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Form processed successfully',
                'analysis_id' => uniqid('analysis_', true),
                'email_sent' => $result['email_sent'],
                'prompt_used' => $result['prompt_title']
            ));
            
        } catch (Exception $e) {
            $processing_time = round((microtime(true) - $start_time) * 1000);
            
            $this->get_logger()->update_log($log_id, array(
                'status' => 'error',
                'response_code' => 500,
                'error_message' => $e->getMessage(),
                'processing_time_ms' => $processing_time
            ));
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms error: ' . $e->getMessage());
            }
            return new WP_Error('processing_error', 'Error processing form data: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Process form data
     */
    public function process_data($form_data, $prompt_id = null, $entry_id = null) {
        try {
            // Get prompt data for this form
            $prompt_data = $this->get_prompt_for_form($prompt_id);
            if (!$prompt_data) {
                return new WP_Error('prompt_not_found', 'No prompt found for prompt_id: ' . $prompt_id);
            }
            
            $prompt = $prompt_data['prompt'];
            $prompt_title = $prompt_data['title'];
            $email_address = $prompt_data['email_address'];
            $email_subject = $prompt_data['email_subject'];
            
            // Prepare data for AI analysis
            $data_for_analysis = $this->prepare_data_for_analysis($form_data, $prompt_id, $entry_id);
            
            // Get AI analysis
            $ai_analysis = $this->get_ai_analysis($data_for_analysis, $prompt);
            if (is_wp_error($ai_analysis)) {
                return $ai_analysis;
            }
            
            // Send email with results (including prompt metadata)
            $email_result = $this->send_analysis_email($form_data, $ai_analysis, $prompt_id, $entry_id, $prompt_title, $prompt, $email_address, $email_subject);
            
            if (is_wp_error($email_result)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Email sending failed: ' . $email_result->get_error_message());
                }
                // Don't fail the webhook if email fails, just log it
            }
            
            // Log the analysis
            $this->log_analysis($form_data, $ai_analysis, $prompt_id, $entry_id);
            
            return array(
                'analysis' => $ai_analysis,
                'analysis_id' => uniqid('analysis_', true),
                'email_sent' => !is_wp_error($email_result),
                'prompt_used' => $prompt_title
            );
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms processing error: ' . $e->getMessage());
            }
            return new WP_Error('processing_error', 'Error processing form data: ' . $e->getMessage());
        }
    }
    
    /**
     * Get prompt for form processing
     */
    private function get_prompt_for_form($prompt_id) {
        // Get prompt from database
        if ($prompt_id && class_exists('Katahdin_AI_Forms_Form_Prompts')) {
            $form_prompts = new Katahdin_AI_Forms_Form_Prompts();
            $prompt_data = $form_prompts->get_prompt_by_prompt_id($prompt_id);
            
            if ($prompt_data) {
                return array(
                    'prompt' => $prompt_data['prompt'],
                    'title' => $prompt_data['title'],
                    'prompt_id' => $prompt_data['prompt_id'],
                    'email_address' => $prompt_data['email_address'],
                    'email_subject' => $prompt_data['email_subject']
                );
            }
        }
        
        // Return null if no prompt found - forms must specify a valid prompt_id
        return null;
    }
    
    /**
     * Prepare data for AI analysis
     */
    private function prepare_data_for_analysis($form_data, $prompt_id, $entry_id) {
        $prepared_data = $form_data;
        
        // Add metadata
        if ($prompt_id) {
            $prepared_data['Prompt ID'] = $prompt_id;
        }
        if ($entry_id) {
            $prepared_data['Entry ID'] = $entry_id;
        }
        
        $prepared_data['Timestamp'] = current_time('mysql');
        $prepared_data['Site'] = get_bloginfo('name');
        
        return $prepared_data;
    }
    
    /**
     * Get AI analysis using Katahdin AI Hub
     */
    private function get_ai_analysis($data, $prompt) {
        if (!function_exists('katahdin_ai_hub') || !is_plugin_active('katahdin-ai-hub/katahdin-ai-hub.php')) {
            return new WP_Error('hub_not_active', __('Katahdin AI Hub is not active.', 'katahdin-ai-forms'));
        }

        $hub = katahdin_ai_hub();
        if (!$hub || !isset($hub->api_manager)) {
            return new WP_Error('hub_api_manager_not_available', __('Katahdin AI Hub API manager not available.', 'katahdin-ai-forms'));
        }

        $full_prompt = $prompt . "\n\nForm Submission Data:\n" . json_encode($data, JSON_PRETTY_PRINT);

        $model = get_option('katahdin_ai_forms_model', 'gpt-3.5-turbo');
        $max_tokens = get_option('katahdin_ai_forms_max_tokens', 1000);
        $temperature = get_option('katahdin_ai_forms_temperature', 0.7);

        $messages = array(
            array('role' => 'system', 'content' => 'You are a helpful assistant that analyzes form submissions.'),
            array('role' => 'user', 'content' => $full_prompt)
        );

        $api_options = array(
            'model' => $model,
            'max_tokens' => intval($max_tokens),
            'temperature' => floatval($temperature),
        );

        try {
            // Use the correct Katahdin AI Hub API call method
            $response = $hub->make_api_call('katahdin-ai-forms', 'chat/completions', array(
                'messages' => $messages
            ), $api_options);

            if (is_wp_error($response)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Katahdin AI Forms: AI API call failed: ' . $response->get_error_message());
                }
                return new WP_Error('ai_api_error', __('AI analysis failed: ', 'katahdin-ai-forms') . $response->get_error_message());
            }

            if (isset($response['choices'][0]['message']['content'])) {
                return $response['choices'][0]['message']['content'];
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Katahdin AI Forms: Unexpected AI API response structure: ' . print_r($response, true));
                }
                return new WP_Error('ai_response_error', __('Unexpected AI response structure.', 'katahdin-ai-forms'));
            }
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms: Exception during AI analysis: ' . $e->getMessage());
            }
            return new WP_Error('ai_exception', __('An unexpected error occurred during AI analysis.', 'katahdin-ai-forms') . ' ' . $e->getMessage());
        }
    }
    
    /**
     * Send analysis email
     */
    private function send_analysis_email($form_data, $analysis, $prompt_id, $entry_id, $prompt_title = null, $prompt_text = null, $email_address = null, $email_subject = null) {
        if (!class_exists('Katahdin_AI_Forms_Email_Sender')) {
            return new WP_Error('email_sender_not_available', 'Email sender not available');
        }
        
        $email_sender = new Katahdin_AI_Forms_Email_Sender();
        return $email_sender->send_analysis_email($form_data, $analysis, $prompt_id, $entry_id, $prompt_title, $prompt_text, $email_address, $email_subject);
    }
    
    /**
     * Log analysis
     */
    private function log_analysis($form_data, $analysis, $prompt_id, $entry_id) {
        $log_data = array(
            'form_data' => $form_data,
            'analysis' => $analysis,
            'prompt_id' => $prompt_id,
            'entry_id' => $entry_id,
            'timestamp' => current_time('mysql')
        );
        
        // Store in WordPress options for recent logs
        $recent_logs = get_option('katahdin_ai_forms_recent_logs', array());
        array_unshift($recent_logs, $log_data);
        $recent_logs = array_slice($recent_logs, 0, 50); // Keep only last 50
        update_option('katahdin_ai_forms_recent_logs', $recent_logs);
    }
    
    /**
     * Check forms permission
     */
    public function check_forms_permission($request) {
        // Check if forms processing is enabled
        if (!get_option('katahdin_ai_forms_enabled', true)) {
            return new WP_Error('forms_disabled', 'Forms processing is disabled', array('status' => 503));
        }
        
        // Check webhook secret if provided
        $secret = $request->get_header('X-Webhook-Secret');
        if ($secret) {
            $expected_secret = $this->get_forms_secret();
            if (!hash_equals($expected_secret, $secret)) {
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
     * Sanitize form data recursively
     */
    private function sanitize_form_data($data) {
        if (is_array($data)) {
            $sanitized = array();
            foreach ($data as $key => $value) {
                $clean_key = sanitize_key($key);
                $sanitized[$clean_key] = $this->sanitize_form_data($value);
            }
            return $sanitized;
        } elseif (is_string($data)) {
            return sanitize_text_field($data);
        } else {
            return $data;
        }
    }
    
    /**
     * Extract form data
     */
    private function extract_form_data($form_data, $request_body = '') {
        $email = '';
        $name = '';
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Katahdin AI Forms - extract_form_data called');
            error_log('Katahdin AI Forms - form_data type: ' . gettype($form_data));
            error_log('Katahdin AI Forms - form_data: ' . print_r($form_data, true));
            error_log('Katahdin AI Forms - request_body: ' . substr($request_body, 0, 200) . '...');
        }
        
        // Try to parse form_data if it's an array
        if (is_array($form_data)) {
            // Direct email field
            if (isset($form_data['email'])) {
                $email = sanitize_email($form_data['email']);
            }
            
            // Direct name field
            if (isset($form_data['name'])) {
                $name = sanitize_text_field($form_data['name']);
            }
            
            // Try common email field variations
            if (empty($email)) {
                $email_fields = array('email_address', 'e_mail', 'mail', 'contact_email', 'user_email');
                foreach ($email_fields as $field) {
                    if (isset($form_data[$field]) && is_email($form_data[$field])) {
                        $email = sanitize_email($form_data[$field]);
                        break;
                    }
                }
            }
            
            // Try common name field variations
            if (empty($name)) {
                $name_fields = array('full_name', 'first_name', 'last_name', 'contact_name', 'user_name', 'display_name');
                foreach ($name_fields as $field) {
                    if (isset($form_data[$field]) && !empty($form_data[$field])) {
                        $name = sanitize_text_field($form_data[$field]);
                        break;
                    }
                }
            }
        }
        
        // Try to parse request body if it's JSON
        if (empty($email) || empty($name)) {
            $body_data = json_decode($request_body, true);
            if ($body_data && is_array($body_data)) {
                if (empty($email) && isset($body_data['email'])) {
                    $email = sanitize_email($body_data['email']);
                }
                if (empty($name) && isset($body_data['name'])) {
                    $name = sanitize_text_field($body_data['name']);
                }
            }
        }
        
        $result = array(
            'email' => $email ?: 'N/A',
            'name' => $name ?: 'N/A'
        );
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Katahdin AI Forms - extracted result: ' . print_r($result, true));
        }
        
        return $result;
    }
    
    /**
     * Get forms secret
     */
    private function get_forms_secret() {
        return get_option('katahdin_ai_forms_webhook_secret', '');
    }
    
    /**
     * Test endpoint
     */
    public function test_endpoint($request) {
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Forms endpoint is working',
            'timestamp' => current_time('mysql'),
            'plugin_version' => KATAHDIN_AI_FORMS_VERSION
        ));
    }
    
    /**
     * Status endpoint
     */
    public function status_endpoint($request) {
        $status = array(
            'plugin_active' => true,
            'forms_enabled' => get_option('katahdin_ai_forms_enabled', true),
            'hub_available' => function_exists('katahdin_ai_hub'),
            'timestamp' => current_time('mysql')
        );
        
        return rest_ensure_response($status);
    }
    
    /**
     * Debug endpoint
     */
    public function debug_endpoint($request) {
        $debug_info = array(
            'plugin_version' => KATAHDIN_AI_FORMS_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'forms_enabled' => get_option('katahdin_ai_forms_enabled', true),
            'hub_available' => function_exists('katahdin_ai_hub'),
            'timestamp' => current_time('mysql')
        );
        
        return rest_ensure_response($debug_info);
    }
    
    /**
     * Get logs endpoint
     */
    public function get_logs_endpoint($request) {
        $limit = $request->get_param('limit');
        $offset = $request->get_param('offset');
        $status = $request->get_param('status');
        
        $logs = $this->get_logger()->get_logs($limit, $offset, $status);
        
        return rest_ensure_response($logs);
    }
    
    /**
     * Get single log
     */
    public function get_log($request) {
        $log_id = $request->get_param('id');
        $log = $this->get_logger()->get_log_by_id($log_id);
        
        if (!$log) {
            return new WP_Error('log_not_found', 'Log not found', array('status' => 404));
        }
        
        return rest_ensure_response($log);
    }
    
    /**
     * Get log stats
     */
    public function get_log_stats($request) {
        $stats = $this->get_logger()->get_log_stats();
        return rest_ensure_response($stats);
    }
    
    /**
     * Cleanup logs
     */
    public function cleanup_logs($request) {
        $retention_days = $request->get_param('retention_days');
        $result = $this->get_logger()->cleanup_logs($retention_days);
        
        return rest_ensure_response($result);
    }
    
    /**
     * Get recent logs
     */
    public function get_recent_logs($limit = 10) {
        $logs = get_option('katahdin_ai_forms_logs', array());
        return array_slice($logs, 0, $limit);
    }
    
    /**
     * Check Katahdin AI Hub status
     */
    public function check_katahdin_hub_status() {
        $status = array(
            'available' => function_exists('katahdin_ai_hub'),
            'initialized' => false,
            'api_key_configured' => false,
            'api_connection_working' => false,
            'error_message' => ''
        );
        
        if ($status['available']) {
            $hub = katahdin_ai_hub();
            if ($hub) {
                $status['initialized'] = true;
                
                // Check if API key is configured
                $api_key = get_option('katahdin_ai_hub_openai_key', '');
                $status['api_key_configured'] = !empty($api_key);
                
                // Test API connection if key is configured
                if ($status['api_key_configured']) {
                    try {
                        $test_result = $hub->test_api_connection();
                        $status['api_connection_working'] = !is_wp_error($test_result);
                        if (is_wp_error($test_result)) {
                            $status['error_message'] = $test_result->get_error_message();
                        }
                    } catch (Exception $e) {
                        $status['error_message'] = $e->getMessage();
                    }
                } else {
                    $status['error_message'] = 'OpenAI API key not configured';
                }
            }
        } else {
            $status['error_message'] = 'Katahdin AI Hub not available';
        }
        
        return $status;
    }
}
