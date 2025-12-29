<?php
/**
 * Webhook Handler Class for Academy Analytics
 * 
 * Handles incoming webhook requests from Flowmattic
 * 
 * @package Academy_Analytics
 */

if (!defined('ABSPATH')) {
    exit;
}

class Academy_Analytics_Webhook {
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->database = new Academy_Analytics_Database();
    }
    
    /**
     * Initialize REST API
     */
    public function init_rest_api() {
        register_rest_route('academy-analytics/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'event_type' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Event type (any string - form_submission, page_visit, button_click, etc.)'
                )
            )
        ));
    }
    
    /**
     * Check webhook permissions
     * 
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error
     */
    public function check_permissions($request) {
        // Option 1: Check for secret key in header or body
        $secret = academy_analytics()->get_webhook_secret();
        
        if (!empty($secret)) {
            $provided_secret = $request->get_header('X-Webhook-Secret');
            if (empty($provided_secret)) {
                $provided_secret = $request->get_param('secret');
            }
            
            if ($provided_secret !== $secret) {
                return new WP_Error('invalid_secret', 'Invalid webhook secret', array('status' => 401));
            }
        }
        
        // Option 2: Allow public webhooks (for Flowmattic, you might want this)
        // For now, we'll allow it but log it
        return true;
    }
    
    /**
     * Handle webhook request
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function handle_webhook($request) {
        try {
            $body = $request->get_json_params();
            
            if (empty($body)) {
                $body = $request->get_body_params();
            }
            
            if (empty($body)) {
                return new WP_Error('no_data', 'No data provided', array('status' => 400));
            }
            
            // Validate event_type is provided
            if (empty($body['event_type'])) {
                return new WP_Error('missing_event_type', 'event_type is required in the payload', array('status' => 400));
            }
            
            // Extract standard fields
            $event_data = array(
                'event_type' => sanitize_text_field($body['event_type']),
                'user_id' => isset($body['user_id']) ? intval($body['user_id']) : null,
                'email' => isset($body['email']) ? sanitize_email($body['email']) : null,
                'form_name' => isset($body['form_name']) ? sanitize_text_field($body['form_name']) : null,
                'page_title' => isset($body['page_title']) ? sanitize_text_field($body['page_title']) : null,
                'page_url' => isset($body['page_url']) ? esc_url_raw($body['page_url']) : null,
                'referrer' => isset($body['referrer']) ? esc_url_raw($body['referrer']) : null,
                'ip_address' => isset($body['ip_address']) ? sanitize_text_field($body['ip_address']) : null,
            );
            
            // Get IP from request if not provided
            if (empty($event_data['ip_address'])) {
                $event_data['ip_address'] = $this->get_client_ip();
            }
            
            // Get user_id from email if not provided
            if (empty($event_data['user_id']) && !empty($event_data['email'])) {
                $user = get_user_by('email', $event_data['email']);
                if ($user) {
                    $event_data['user_id'] = $user->ID;
                }
            }
            
            // Store all other data in JSON field
            $json_data = $body;
            
            // Remove standard fields from JSON data to avoid duplication
            unset($json_data['event_type'], $json_data['user_id'], $json_data['email'], 
                  $json_data['form_name'], $json_data['page_title'], $json_data['page_url'],
                  $json_data['referrer'], $json_data['ip_address'], $json_data['secret']);
            
            $event_data['data'] = $json_data;
            
            // Check throttle before inserting
            $throttle_seconds = get_option('academy_analytics_throttle_seconds', 60);
            if ($this->database->has_recent_event($event_data, $throttle_seconds)) {
                return rest_ensure_response(array(
                    'success' => true,
                    'event_id' => null,
                    'message' => 'Event throttled - similar event recorded recently',
                    'throttled' => true
                ));
            }
            
            // Insert event
            $event_id = $this->database->insert_event($event_data, false); // Don't check throttle again
            
            if ($event_id) {
                return rest_ensure_response(array(
                    'success' => true,
                    'event_id' => $event_id,
                    'message' => 'Event recorded successfully'
                ));
            } else {
                return new WP_Error('insert_failed', 'Failed to insert event', array('status' => 500));
            }
            
        } catch (Exception $e) {
            error_log('Academy Analytics Webhook Error: ' . $e->getMessage());
            return new WP_Error('webhook_error', 'Error processing webhook: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = sanitize_text_field($_SERVER[$key]);
                // Handle comma-separated IPs (from proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '';
    }
}

