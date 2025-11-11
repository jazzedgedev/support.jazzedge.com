<?php
/**
 * OpenAI Client class for Fluent Support AI Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class FluentSupportAI_OpenAI_Client {
    
    private $api_key;
    private $api_url = 'https://api.openai.com/v1/chat/completions';
    private $model = 'gpt-3.5-turbo';
    
    public function __construct($api_key = null) {
        $this->api_key = $api_key ? $api_key : get_option('fluent_support_ai_openai_key', '');
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('API key is not set', 'fluent-support-ai')
            );
        }
        
        $test_prompt = 'Hello, this is a test message. Please respond with "API connection successful".';
        
        $response = $this->make_request($test_prompt);
        
        if ($response['success']) {
            return array(
                'success' => true,
                'message' => __('API connection successful', 'fluent-support-ai')
            );
        } else {
            return array(
                'success' => false,
                'message' => $response['message']
            );
        }
    }
    
    /**
     * Generate AI response
     */
    public function generate_response($prompt, $ticket_content = '') {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('API key is not configured', 'fluent-support-ai')
            );
        }
        
        // Replace ticket content placeholder
        $full_prompt = str_replace('{ticket_content}', $ticket_content, $prompt);
        
        $response = $this->make_request($full_prompt);
        
        return $response;
    }
    
    /**
     * Make API request to OpenAI
     */
    private function make_request($prompt) {
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->api_key
        );
        
        $data = array(
            'model' => $this->model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 1000,
            'temperature' => 0.7,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        );
        
        $args = array(
            'method' => 'POST',
            'headers' => $headers,
            'body' => wp_json_encode($data),
            'timeout' => 30
        );
        
        $response = wp_remote_request($this->api_url, $args);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => sprintf(__('API request failed: %s', 'fluent-support-ai'), $response->get_error_message())
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            $error_data = json_decode($response_body, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : __('Unknown API error', 'fluent-support-ai');
            
            return array(
                'success' => false,
                'message' => sprintf(__('API error (%d): %s', 'fluent-support-ai'), $response_code, $error_message)
            );
        }
        
        $data = json_decode($response_body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return array(
                'success' => false,
                'message' => __('Invalid API response format', 'fluent-support-ai')
            );
        }
        
        $content = trim($data['choices'][0]['message']['content']);
        
        return array(
            'success' => true,
            'content' => $content,
            'usage' => isset($data['usage']) ? $data['usage'] : null
        );
    }
    
    /**
     * Set API key
     */
    public function set_api_key($api_key) {
        $this->api_key = $api_key;
    }
    
    /**
     * Get API key status
     */
    public function get_api_key_status() {
        return !empty($this->api_key);
    }
    
    /**
     * Get available models
     */
    public function get_available_models() {
        return array(
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Fast, Cost-effective)',
            'gpt-4' => 'GPT-4 (More capable, Higher cost)',
            'gpt-4-turbo' => 'GPT-4 Turbo (Latest, Best performance)'
        );
    }
    
    /**
     * Set model
     */
    public function set_model($model) {
        $this->model = $model;
    }
    
    /**
     * Get current model
     */
    public function get_model() {
        return $this->model;
    }
}
