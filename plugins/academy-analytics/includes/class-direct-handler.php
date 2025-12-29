<?php
/**
 * Direct Handler Class for Academy Analytics
 * 
 * Handles direct PHP function calls (faster than webhook for same-server calls)
 * 
 * @package Academy_Analytics
 */

if (!defined('ABSPATH')) {
    exit;
}

class Academy_Analytics_Direct_Handler {
    
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
     * Process event data directly (for PHP function calls)
     * 
     * This is faster than webhook since it doesn't go through HTTP/REST API
     * Use this when Flowmattic can call PHP functions directly
     * 
     * @param array $data Event data array
     * @return array|WP_Error Success response with event_id or WP_Error on failure
     */
    public function process_event($data) {
        try {
            if (empty($data) || !is_array($data)) {
                return new WP_Error('invalid_data', 'Data must be a non-empty array');
            }
            
            // Validate event_type is provided
            if (empty($data['event_type'])) {
                return new WP_Error('missing_event_type', 'event_type is required in the data array');
            }
            
            // Extract standard fields
            $event_data = array(
                'event_type' => sanitize_text_field($data['event_type']),
                'user_id' => isset($data['user_id']) ? intval($data['user_id']) : null,
                'email' => isset($data['email']) ? sanitize_email($data['email']) : null,
                'form_name' => isset($data['form_name']) ? sanitize_text_field($data['form_name']) : null,
                'page_title' => isset($data['page_title']) ? sanitize_text_field($data['page_title']) : null,
                'page_url' => isset($data['page_url']) ? esc_url_raw($data['page_url']) : null,
                'referrer' => isset($data['referrer']) ? esc_url_raw($data['referrer']) : null,
                'ip_address' => isset($data['ip_address']) ? sanitize_text_field($data['ip_address']) : null,
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
            $json_data = $data;
            
            // Remove standard fields from JSON data to avoid duplication
            unset($json_data['event_type'], $json_data['user_id'], $json_data['email'], 
                  $json_data['form_name'], $json_data['page_title'], $json_data['page_url'],
                  $json_data['referrer'], $json_data['ip_address']);
            
            $event_data['data'] = $json_data;
            
            // Check throttle before inserting
            $throttle_seconds = get_option('academy_analytics_throttle_seconds', 60);
            if ($this->database->has_recent_event($event_data, $throttle_seconds)) {
                return array(
                    'success' => true,
                    'event_id' => null,
                    'message' => 'Event throttled - similar event recorded recently',
                    'throttled' => true
                );
            }
            
            // Insert event
            $event_id = $this->database->insert_event($event_data, false); // Don't check throttle again
            
            if ($event_id) {
                return array(
                    'success' => true,
                    'event_id' => $event_id,
                    'message' => 'Event recorded successfully'
                );
            } else {
                return new WP_Error('insert_failed', 'Failed to insert event into database');
            }
            
        } catch (Exception $e) {
            error_log('Academy Analytics Direct Handler Error: ' . $e->getMessage());
            return new WP_Error('processing_error', 'Error processing event: ' . $e->getMessage());
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

/**
 * Global function for direct PHP calls
 * 
 * Use this function when Flowmattic can call PHP functions directly
 * This is faster than webhook since it bypasses HTTP/REST API
 * 
 * @param array $data Event data array
 * @return array|WP_Error Success response with event_id or WP_Error on failure
 * 
 * @example
 * // In Flowmattic PHP function action:
 * $result = academy_analytics_record_event(array(
 *     'event_type' => 'form_submission',
 *     'email' => 'user@example.com',
 *     'form_name' => 'Contact Form',
 *     'data' => array(
 *         'name' => 'John Doe',
 *         'message' => 'Hello'
 *     )
 * ));
 */
if (!function_exists('academy_analytics_record_event')) {
    function academy_analytics_record_event($data) {
        $handler = new Academy_Analytics_Direct_Handler();
        return $handler->process_event($data);
    }
}

