<?php
/**
 * API Manager for Katahdin AI Hub
 * Handles all OpenAI API communications
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Hub_API_Manager {
    
    /**
     * OpenAI API base URL
     */
    private $api_base_url = 'https://api.openai.com/v1';
    
    /**
     * Rate limiting
     */
    private $rate_limit = 60; // requests per minute
    private $rate_limit_window = 60; // seconds
    
    /**
     * Initialize API Manager
     */
    public function init() {
        // Initialize rate limiting
        $this->rate_limit = get_option('katahdin_ai_hub_rate_limit', 60);
    }
    
    /**
     * Make an API call to OpenAI
     */
    public function make_call($plugin_id, $endpoint, $data, $options = array()) {
        // Check rate limiting
        if (!$this->check_rate_limit($plugin_id)) {
            return new WP_Error('rate_limit_exceeded', 'Rate limit exceeded. Please try again later.');
        }
        
        // Get API key
        $api_key = $this->get_api_key();
        if (!$api_key) {
            return new WP_Error('no_api_key', 'OpenAI API key not configured');
        }
        
        // Prepare request
        $request_data = $this->prepare_request_data($endpoint, $data, $options);
        
        // Make the API call
        $start_time = microtime(true);
        $response = wp_remote_post($this->api_base_url . '/' . $endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'User-Agent' => 'Katahdin-AI-Hub/' . KATAHDIN_AI_HUB_VERSION
            ),
            'body' => json_encode($request_data),
            'timeout' => 30,
            'sslverify' => true
        ));
        
        $response_time = round((microtime(true) - $start_time) * 1000); // milliseconds
        
        // Handle response
        if (is_wp_error($response)) {
            $this->log_usage($plugin_id, $endpoint, 0, 0, $response_time, false, $response->get_error_message());
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        // Calculate usage
        $tokens_used = $this->calculate_tokens_used($response_data);
        $cost = $this->calculate_cost($tokens_used, $options);
        
        // Log usage
        $success = $response_code === 200;
        $error_message = $success ? null : ($response_data['error']['message'] ?? 'Unknown error');
        $this->log_usage($plugin_id, $endpoint, $tokens_used, $cost, $response_time, $success, $error_message);
        
        if ($success) {
            return $response_data;
        } else {
            return new WP_Error('api_error', $error_message, array('status' => $response_code));
        }
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        $response = wp_remote_get($this->api_base_url . '/models', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->get_api_key(),
                'Content-Type' => 'application/json'
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        if ($response_code === 200) {
            return array(
                'success' => true,
                'models_count' => count($response_data['data'] ?? []),
                'message' => 'Connection successful!'
            );
        } else {
            return array(
                'success' => false,
                'error' => $response_data['error']['message'] ?? 'Connection failed'
            );
        }
    }
    
    /**
     * Debug method to test API key retrieval
     */
    public function debug_api_key() {
        $encrypted_key = get_option('katahdin_ai_hub_openai_key');
        
        $result = array(
            'raw_key_exists' => !empty($encrypted_key),
            'raw_key_length' => $encrypted_key ? strlen($encrypted_key) : 0,
            'raw_key_preview' => $encrypted_key ? substr($encrypted_key, 0, 8) . '...' : 'Not found',
            'starts_with_sk' => $encrypted_key ? strpos($encrypted_key, 'sk-') === 0 : false
        );
        
        // Safely test get_api_key method
        try {
            $retrieved_key = $this->get_api_key();
            $result['get_api_key_result'] = $retrieved_key ? 'SUCCESS' : 'FAILED';
            $result['retrieved_key_length'] = $retrieved_key ? strlen($retrieved_key) : 0;
        } catch (Exception $e) {
            $result['get_api_key_result'] = 'ERROR: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Get API key (encrypted)
     */
    private function get_api_key() {
        $encrypted_key = get_option('katahdin_ai_hub_openai_key');
        if (!$encrypted_key) {
            return false;
        }
        
        // Check if the key looks like a raw OpenAI API key (starts with sk-)
        if (strpos($encrypted_key, 'sk-') === 0) {
            return $encrypted_key; // Return raw key if it starts with sk-
        }
        
        // Otherwise, try to decrypt it
        return $this->decrypt_api_key($encrypted_key);
    }
    
    // Force reload - updated API key detection logic
    
    /**
     * Set API key (encrypted)
     */
    public function set_api_key($api_key) {
        if (empty($api_key)) {
            return false;
        }
        
        // For now, store the key directly (we can add encryption later if needed)
        return update_option('katahdin_ai_hub_openai_key', $api_key);
    }
    
    /**
     * Encrypt API key
     */
    private function encrypt_api_key($api_key) {
        $key = wp_salt('AUTH_KEY');
        $iv = wp_salt('SECURE_AUTH_SALT');
        
        $encrypted = openssl_encrypt($api_key, 'AES-256-CBC', $key, 0, substr($iv, 0, 16));
        return base64_encode($encrypted);
    }
    
    /**
     * Decrypt API key
     */
    private function decrypt_api_key($encrypted_key) {
        $key = wp_salt('AUTH_KEY');
        $iv = wp_salt('SECURE_AUTH_SALT');
        
        $decrypted = base64_decode($encrypted_key);
        return openssl_decrypt($decrypted, 'AES-256-CBC', $key, 0, substr($iv, 0, 16));
    }
    
    /**
     * Prepare request data based on endpoint
     */
    private function prepare_request_data($endpoint, $data, $options) {
        switch ($endpoint) {
            case 'chat/completions':
                return array(
                    'model' => $options['model'] ?? 'gpt-3.5-turbo',
                    'messages' => $data['messages'] ?? array(),
                    'max_tokens' => $options['max_tokens'] ?? 1000,
                    'temperature' => $options['temperature'] ?? 0.7,
                    'stream' => false
                );
                
            case 'completions':
                return array(
                    'model' => $options['model'] ?? 'text-davinci-003',
                    'prompt' => $data['prompt'] ?? '',
                    'max_tokens' => $options['max_tokens'] ?? 1000,
                    'temperature' => $options['temperature'] ?? 0.7
                );
                
            case 'embeddings':
                return array(
                    'model' => $options['model'] ?? 'text-embedding-ada-002',
                    'input' => $data['input'] ?? ''
                );
                
            default:
                return $data;
        }
    }
    
    /**
     * Calculate tokens used from response
     */
    private function calculate_tokens_used($response_data) {
        if (isset($response_data['usage']['total_tokens'])) {
            return $response_data['usage']['total_tokens'];
        }
        
        // Fallback estimation
        $prompt_tokens = $response_data['usage']['prompt_tokens'] ?? 0;
        $completion_tokens = $response_data['usage']['completion_tokens'] ?? 0;
        
        return $prompt_tokens + $completion_tokens;
    }
    
    /**
     * Calculate cost based on tokens and model
     */
    private function calculate_cost($tokens, $options) {
        $model = $options['model'] ?? 'gpt-3.5-turbo';
        
        // Pricing per 1K tokens (as of 2024)
        $pricing = array(
            'gpt-4' => 0.03, // $0.03 per 1K tokens
            'gpt-4-turbo' => 0.01,
            'gpt-3.5-turbo' => 0.002,
            'text-davinci-003' => 0.02,
            'text-embedding-ada-002' => 0.0001
        );
        
        $price_per_1k = $pricing[$model] ?? 0.002;
        return ($tokens / 1000) * $price_per_1k;
    }
    
    /**
     * Check rate limiting
     */
    private function check_rate_limit($plugin_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_usage';
        $window_start = date('Y-m-d H:i:s', time() - $this->rate_limit_window);
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE plugin_id = %s AND created_at > %s",
            $plugin_id, $window_start
        ));
        
        return $count < $this->rate_limit;
    }
    
    /**
     * Log API usage
     */
    private function log_usage($plugin_id, $endpoint, $tokens_used, $cost, $response_time, $success, $error_message) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_usage';
        
        $wpdb->insert(
            $table_name,
            array(
                'plugin_id' => $plugin_id,
                'endpoint' => $endpoint,
                'tokens_used' => $tokens_used,
                'cost' => $cost,
                'response_time' => $response_time,
                'success' => $success ? 1 : 0,
                'error_message' => $error_message,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%f', '%d', '%d', '%s', '%s')
        );
        
        // Update plugin quota usage
        $this->update_plugin_quota($plugin_id, $tokens_used);
    }
    
    /**
     * Update plugin quota usage
     */
    private function update_plugin_quota($plugin_id, $tokens_used) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name 
             SET quota_used = quota_used + %d, last_used = %s 
             WHERE plugin_id = %s",
            $tokens_used, current_time('mysql'), $plugin_id
        ));
    }
    
    /**
     * Get usage statistics
     */
    public function get_usage_stats($plugin_id = null, $days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_usage';
        $date_from = date('Y-m-d H:i:s', time() - ($days * 24 * 60 * 60));
        
        $where_clause = "WHERE created_at > %s";
        $params = array($date_from);
        
        if ($plugin_id) {
            $where_clause .= " AND plugin_id = %s";
            $params[] = $plugin_id;
        }
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_requests,
                SUM(tokens_used) as total_tokens,
                SUM(cost) as total_cost,
                AVG(response_time) as avg_response_time,
                SUM(success) as successful_requests,
                COUNT(*) - SUM(success) as failed_requests
             FROM $table_name $where_clause",
            $params
        ));
        
        return $stats;
    }
}
