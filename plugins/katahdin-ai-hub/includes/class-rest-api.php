<?php
/**
 * REST API for Katahdin AI Hub
 * Provides REST endpoints for AI services
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Hub_REST_API {
    
    /**
     * Initialize REST API
     */
    public function init() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Test API connection
        register_rest_route('katahdin-ai-hub/v1', '/test-connection', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_connection'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Chat completions
        register_rest_route('katahdin-ai-hub/v1', '/chat/completions', array(
            'methods' => 'POST',
            'callback' => array($this, 'chat_completions'),
            'permission_callback' => array($this, 'check_plugin_permission'),
            'args' => array(
                'messages' => array(
                    'required' => true,
                    'type' => 'array',
                    'validate_callback' => array($this, 'validate_messages')
                ),
                'model' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'gpt-3.5-turbo',
                    'enum' => array('gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo')
                ),
                'max_tokens' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 1000,
                    'minimum' => 1,
                    'maximum' => 4000
                ),
                'temperature' => array(
                    'required' => false,
                    'type' => 'number',
                    'default' => 0.7,
                    'minimum' => 0,
                    'maximum' => 2
                )
            )
        ));
        
        // Text completions
        register_rest_route('katahdin-ai-hub/v1', '/completions', array(
            'methods' => 'POST',
            'callback' => array($this, 'text_completions'),
            'permission_callback' => array($this, 'check_plugin_permission'),
            'args' => array(
                'prompt' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field'
                ),
                'model' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'text-davinci-003',
                    'enum' => array('text-davinci-003', 'text-davinci-002', 'text-curie-001')
                ),
                'max_tokens' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 1000,
                    'minimum' => 1,
                    'maximum' => 4000
                ),
                'temperature' => array(
                    'required' => false,
                    'type' => 'number',
                    'default' => 0.7,
                    'minimum' => 0,
                    'maximum' => 2
                )
            )
        ));
        
        // Embeddings
        register_rest_route('katahdin-ai-hub/v1', '/embeddings', array(
            'methods' => 'POST',
            'callback' => array($this, 'embeddings'),
            'permission_callback' => array($this, 'check_plugin_permission'),
            'args' => array(
                'input' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field'
                ),
                'model' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'text-embedding-ada-002',
                    'enum' => array('text-embedding-ada-002')
                )
            )
        ));
        
        // Get usage statistics
        register_rest_route('katahdin-ai-hub/v1', '/usage', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_usage_stats'),
            'permission_callback' => array($this, 'check_plugin_permission'),
            'args' => array(
                'days' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 30,
                    'minimum' => 1,
                    'maximum' => 365
                )
            )
        ));
        
        // Get plugin quota status
        register_rest_route('katahdin-ai-hub/v1', '/quota', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_quota_status'),
            'permission_callback' => array($this, 'check_plugin_permission')
        ));
        
        // Register plugin
        register_rest_route('katahdin-ai-hub/v1', '/register-plugin', array(
            'methods' => 'POST',
            'callback' => array($this, 'register_plugin'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'plugin_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'name' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'version' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'features' => array(
                    'required' => false,
                    'type' => 'array',
                    'default' => array()
                ),
                'quota_limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 1000,
                    'minimum' => 0
                )
            )
        ));
    }
    
    /**
     * Test API connection
     */
    public function test_connection($request) {
        $api_manager = katahdin_ai_hub()->api_manager;
        $result = $api_manager->test_connection();
        
        if ($result['success']) {
            return rest_ensure_response($result);
        } else {
            return new WP_Error('connection_failed', $result['error'], array('status' => 400));
        }
    }
    
    /**
     * Chat completions endpoint
     */
    public function chat_completions($request) {
        $plugin_id = $this->get_plugin_id_from_request($request);
        
        if (!$plugin_id) {
            return new WP_Error('plugin_not_registered', 'Plugin not registered with Katahdin AI Hub', array('status' => 403));
        }
        
        // Check quota
        if (!katahdin_ai_hub()->plugin_registry->has_quota_available($plugin_id)) {
            return new WP_Error('quota_exceeded', 'Plugin quota exceeded', array('status' => 429));
        }
        
        $data = array(
            'messages' => $request->get_param('messages')
        );
        
        $options = array(
            'model' => $request->get_param('model'),
            'max_tokens' => $request->get_param('max_tokens'),
            'temperature' => $request->get_param('temperature')
        );
        
        $result = katahdin_ai_hub()->make_api_call($plugin_id, 'chat/completions', $data, $options);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return rest_ensure_response($result);
    }
    
    /**
     * Text completions endpoint
     */
    public function text_completions($request) {
        $plugin_id = $this->get_plugin_id_from_request($request);
        
        if (!$plugin_id) {
            return new WP_Error('plugin_not_registered', 'Plugin not registered with Katahdin AI Hub', array('status' => 403));
        }
        
        // Check quota
        if (!katahdin_ai_hub()->plugin_registry->has_quota_available($plugin_id)) {
            return new WP_Error('quota_exceeded', 'Plugin quota exceeded', array('status' => 429));
        }
        
        $data = array(
            'prompt' => $request->get_param('prompt')
        );
        
        $options = array(
            'model' => $request->get_param('model'),
            'max_tokens' => $request->get_param('max_tokens'),
            'temperature' => $request->get_param('temperature')
        );
        
        $result = katahdin_ai_hub()->make_api_call($plugin_id, 'completions', $data, $options);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return rest_ensure_response($result);
    }
    
    /**
     * Embeddings endpoint
     */
    public function embeddings($request) {
        $plugin_id = $this->get_plugin_id_from_request($request);
        
        if (!$plugin_id) {
            return new WP_Error('plugin_not_registered', 'Plugin not registered with Katahdin AI Hub', array('status' => 403));
        }
        
        // Check quota
        if (!katahdin_ai_hub()->plugin_registry->has_quota_available($plugin_id)) {
            return new WP_Error('quota_exceeded', 'Plugin quota exceeded', array('status' => 429));
        }
        
        $data = array(
            'input' => $request->get_param('input')
        );
        
        $options = array(
            'model' => $request->get_param('model')
        );
        
        $result = katahdin_ai_hub()->make_api_call($plugin_id, 'embeddings', $data, $options);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return rest_ensure_response($result);
    }
    
    /**
     * Get usage statistics
     */
    public function get_usage_stats($request) {
        $plugin_id = $this->get_plugin_id_from_request($request);
        $days = $request->get_param('days');
        
        if (!$plugin_id) {
            return new WP_Error('plugin_not_registered', 'Plugin not registered with Katahdin AI Hub', array('status' => 403));
        }
        
        $stats = katahdin_ai_hub()->usage_tracker->get_plugin_stats($plugin_id, $days);
        
        return rest_ensure_response($stats);
    }
    
    /**
     * Get quota status
     */
    public function get_quota_status($request) {
        $plugin_id = $this->get_plugin_id_from_request($request);
        
        if (!$plugin_id) {
            return new WP_Error('plugin_not_registered', 'Plugin not registered with Katahdin AI Hub', array('status' => 403));
        }
        
        $quota_status = katahdin_ai_hub()->plugin_registry->get_quota_status($plugin_id);
        
        return rest_ensure_response($quota_status);
    }
    
    /**
     * Register plugin
     */
    public function register_plugin($request) {
        $plugin_id = $request->get_param('plugin_id');
        $config = array(
            'name' => $request->get_param('name'),
            'version' => $request->get_param('version'),
            'features' => $request->get_param('features'),
            'quota_limit' => $request->get_param('quota_limit')
        );
        
        $result = katahdin_ai_hub()->register_plugin($plugin_id, $config);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Plugin registered successfully',
            'plugin_id' => $plugin_id
        ));
    }
    
    /**
     * Check admin permission
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Check plugin permission
     */
    public function check_plugin_permission($request) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Get plugin ID from request headers or body
        $plugin_id = $this->get_plugin_id_from_request($request);
        
        if (!$plugin_id) {
            return false;
        }
        
        // Check if plugin is registered
        if (!katahdin_ai_hub()->plugin_registry->is_registered($plugin_id)) {
            return false;
        }
        
        // Check if plugin is active
        $plugin = katahdin_ai_hub()->plugin_registry->get_plugin($plugin_id);
        if (!$plugin || !$plugin['is_active']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get plugin ID from request
     */
    private function get_plugin_id_from_request($request) {
        // Try to get from X-Plugin-ID header
        $plugin_id = $request->get_header('X-Plugin-ID');
        
        if (!$plugin_id) {
            // Try to get from request body
            $plugin_id = $request->get_param('plugin_id');
        }
        
        return $plugin_id;
    }
    
    /**
     * Validate messages array
     */
    public function validate_messages($messages) {
        if (!is_array($messages)) {
            return false;
        }
        
        foreach ($messages as $message) {
            if (!isset($message['role']) || !isset($message['content'])) {
                return false;
            }
            
            if (!in_array($message['role'], array('system', 'user', 'assistant'))) {
                return false;
            }
        }
        
        return true;
    }
}
