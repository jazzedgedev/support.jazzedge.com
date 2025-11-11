<?php
/**
 * API Handler Class for Katahdin AI Plugin Template
 */

if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Plugin_Template_API_Handler {
    
    /**
     * Process data using AI
     */
    public function process_data($data) {
        try {
            // Check hub availability
            if (!function_exists('katahdin_ai_hub')) {
                return new WP_Error('hub_not_available', 'Katahdin AI Hub not available');
            }
            
            $hub = katahdin_ai_hub();
            if (!$hub || !$hub->api_manager) {
                return new WP_Error('hub_not_initialized', 'Katahdin AI Hub not properly initialized');
            }
            
            // Prepare AI request
            $ai_data = array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are a helpful AI assistant.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => json_encode($data)
                    )
                ),
                'max_tokens' => 1000,
                'temperature' => 0.7
            );
            
            // Make API call through hub
            $result = $hub->api_manager->make_api_call('chat/completions', $ai_data);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return array(
                'success' => true,
                'result' => $result,
                'timestamp' => current_time('mysql')
            );
            
        } catch (Exception $e) {
            error_log('Katahdin AI Plugin Template API error: ' . $e->getMessage());
            return new WP_Error('api_error', 'API error: ' . $e->getMessage());
        }
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        try {
            if (!function_exists('katahdin_ai_hub')) {
                return new WP_Error('hub_not_available', 'Katahdin AI Hub not available');
            }
            
            $hub = katahdin_ai_hub();
            if (!$hub || !$hub->api_manager) {
                return new WP_Error('hub_not_initialized', 'Katahdin AI Hub not properly initialized');
            }
            
            // Test the connection
            $test_result = $hub->api_manager->test_connection();
            
            if (is_wp_error($test_result)) {
                return $test_result;
            }
            
            return array(
                'success' => true,
                'message' => 'API connection successful',
                'timestamp' => current_time('mysql')
            );
            
        } catch (Exception $e) {
            error_log('Katahdin AI Plugin Template connection test error: ' . $e->getMessage());
            return new WP_Error('connection_error', 'Connection test failed: ' . $e->getMessage());
        }
    }
}
