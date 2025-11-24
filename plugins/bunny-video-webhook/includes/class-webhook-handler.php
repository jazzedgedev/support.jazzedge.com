<?php
/**
 * Webhook Handler Class for Bunny Video Webhook
 * 
 * Handles incoming webhooks from Bunny.net
 * 
 * @package Bunny_Video_Webhook
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Bunny_Video_Webhook_Handler {
    
    /**
     * Video processor instance
     */
    private $processor;
    
    /**
     * Logger instance
     */
    private $logger;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->processor = new Bunny_Video_Webhook_Processor();
        $this->logger = new Bunny_Video_Webhook_Logger();
        
        add_action('rest_api_init', array($this, 'register_webhook_endpoints'));
    }
    
    /**
     * Register REST API endpoints
     */
    public function register_webhook_endpoints() {
        // Main webhook endpoint
        register_rest_route('bunny-video-webhook/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => array($this, 'verify_webhook_permission'),
        ));
        
        // Test endpoint (admin only)
        register_rest_route('bunny-video-webhook/v1', '/test', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_webhook'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Status endpoint (admin only)
        register_rest_route('bunny-video-webhook/v1', '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_status'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
    }
    
    /**
     * Verify webhook permission
     * 
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error
     */
    public function verify_webhook_permission($request) {
        // Get webhook secret from settings
        $webhook_secret = get_option('bunny_video_webhook_secret', '');
        
        // If no secret is configured, allow all requests (for initial setup)
        if (empty($webhook_secret)) {
            return true;
        }
        
        // Check for secret in header or query parameter
        $provided_secret = $request->get_header('X-Webhook-Secret');
        if (empty($provided_secret)) {
            $provided_secret = $request->get_param('secret');
        }
        
        if (empty($provided_secret) || $provided_secret !== $webhook_secret) {
            $this->logger->log_error('Webhook authentication failed', array(
                'webhook_data' => array(
                    'headers' => $request->get_headers(),
                    'params' => $request->get_params()
                )
            ));
            return new WP_Error(
                'unauthorized',
                'Invalid webhook secret',
                array('status' => 401)
            );
        }
        
        return true;
    }
    
    /**
     * Check admin permission
     * 
     * @return bool
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Handle webhook request
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function handle_webhook($request) {
        // Get webhook payload
        $payload = $request->get_json_params();
        
        // If no JSON payload, try to get from body
        if (empty($payload)) {
            $body = $request->get_body();
            if (!empty($body)) {
                $payload = json_decode($body, true);
            }
        }
        
        // If still no payload, try params
        if (empty($payload)) {
            $payload = $request->get_params();
        }
        
        if (empty($payload)) {
            $this->logger->log_error('Empty webhook payload', array(
                'headers' => $request->get_headers(),
                'body' => $request->get_body()
            ));
            
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Empty webhook payload'
            ), 400);
        }
        
        // Process video
        $result = $this->processor->process_video($payload);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'error_code' => $result->get_error_code()
            ), 400);
        }
        
        // Check if video was skipped
        if (isset($result['skipped']) && $result['skipped']) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => $result['message'],
                'skipped' => true
            ), 200);
        }
        
        // Success response
        return new WP_REST_Response(array(
            'success' => true,
            'message' => $result['message'],
            'data' => array(
                'lesson_id' => $result['lesson_id'],
                'chapter_id' => $result['chapter_id'],
                'bunny_url' => $result['bunny_url']
            )
        ), 200);
    }
    
    /**
     * Test webhook endpoint
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function test_webhook($request) {
        $test_data = $request->get_json_params() ?: $request->get_params();
        
        if (empty($test_data)) {
            // Create sample test data
            $test_data = array(
                'VideoId' => 'test-video-id-123',
                'Title' => '78-797-id797-Finding-Scale-Secrets-sample.mp4',
                'FileName' => '78-797-id797-Finding-Scale-Secrets-sample.mp4'
            );
        }
        
        $result = $this->processor->process_video($test_data);
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Test webhook processed',
            'test_data' => $test_data,
            'result' => $result
        ), 200);
    }
    
    /**
     * Get webhook status
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_status($request) {
        $webhook_secret = get_option('bunny_video_webhook_secret', '');
        $cdn_hostname = get_option('bunny_video_webhook_cdn_hostname', '');
        
        $webhook_url = rest_url('bunny-video-webhook/v1/webhook');
        
        $log_count = $this->logger->get_log_count();
        $error_count = $this->logger->get_log_count('error');
        $success_count = $this->logger->get_log_count('success');
        
        return new WP_REST_Response(array(
            'success' => true,
            'status' => array(
                'webhook_url' => $webhook_url,
                'webhook_secret_configured' => !empty($webhook_secret),
                'cdn_hostname_configured' => !empty($cdn_hostname),
                'cdn_hostname' => $cdn_hostname,
                'logs' => array(
                    'total' => $log_count,
                    'errors' => $error_count,
                    'success' => $success_count
                )
            )
        ), 200);
    }
}

