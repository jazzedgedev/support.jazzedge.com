<?php
/**
 * Debug Logger Class for Academy AI Assistant
 * 
 * Logs all AI interactions, context data, API calls, and errors for debugging
 * Security: Never logs sensitive data (API keys, passwords)
 */

if (!defined('ABSPATH')) {
    exit;
}

class AAA_Debug_Logger {
    
    private $debug_logs_table;
    
    public function __construct() {
        global $wpdb;
        $this->debug_logs_table = $wpdb->prefix . 'aaa_debug_logs';
    }
    
    /**
     * Check if debug mode is enabled
     * 
     * @return bool True if debug mode is enabled
     */
    public function is_enabled() {
        return (bool) get_option('aaa_debug_enabled', false);
    }
    
    /**
     * Log debug information
     * Security: Sanitizes all input, never logs sensitive data
     * 
     * @param string $log_type Type of log (api_call, context_build, embedding_search, error, etc.)
     * @param string $message Log message
     * @param array $data Additional data (will be JSON encoded)
     * @param int $user_id User ID (optional)
     * @param int $session_id Session ID (optional)
     * @param string $location Location used (optional, was personality)
     * @param int $response_time Response time in milliseconds (optional)
     * @param int $tokens_used Tokens used (optional)
     * @return bool|int Log ID on success, false on failure
     */
    public function log($log_type, $message, $data = null, $user_id = null, $session_id = null, $location = null, $response_time = null, $tokens_used = null) {
        // Only log if debug mode is enabled
        if (!get_option('aaa_debug_enabled', false)) {
            return false;
        }
        
        global $wpdb;
        
        // Security: Sanitize inputs
        $log_type = sanitize_text_field($log_type);
        $message = sanitize_textarea_field($message);
        
        // Get current user if not provided
        if (!$user_id && is_user_logged_in()) {
            $user_id = get_current_user_id();
        }
        $user_id = $user_id ? absint($user_id) : 0;
        
        $session_id = $session_id ? absint($session_id) : null;
        $location = $location ? sanitize_text_field($location) : null;
        $response_time = $response_time ? absint($response_time) : null;
        $tokens_used = $tokens_used ? absint($tokens_used) : null;
        
        // Check which column exists (location or personality) for migration compatibility
        $location_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s 
             AND TABLE_NAME = %s 
             AND COLUMN_NAME = 'location'",
            DB_NAME,
            $this->debug_logs_table
        ));
        
        $personality_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s 
             AND TABLE_NAME = %s 
             AND COLUMN_NAME = 'personality'",
            DB_NAME,
            $this->debug_logs_table
        ));
        
        // Use location if it exists, otherwise fall back to personality
        $location_column = (!empty($location_exists)) ? 'location' : 'personality';
        if (!in_array($location_column, array('location', 'personality'), true)) {
            $location_column = 'location'; // Default fallback
        }
        
        // Security: Remove sensitive data from $data array
        if (is_array($data)) {
            $data = $this->sanitize_debug_data($data);
            $data_json = wp_json_encode($data);
        } elseif ($data !== null) {
            // If data is not an array, try to sanitize it
            $data_json = wp_json_encode($this->sanitize_debug_data(array('data' => $data)));
        } else {
            $data_json = null;
        }
        
        // Build insert array with correct column name
        $insert_data = array(
            'user_id' => $user_id,
            'session_id' => $session_id,
            'log_type' => $log_type,
            'message' => $message,
            'data' => $data_json,
            'response_time' => $response_time,
            'tokens_used' => $tokens_used
        );
        
        $insert_format = array('%d', '%d', '%s', '%s', '%s', '%d', '%d');
        
        // Add location/personality column
        if ($location_column === 'location') {
            $insert_data['location'] = $location;
        } else {
            $insert_data['personality'] = $location; // For backward compatibility
        }
        $insert_format[] = '%s';
        
        // Insert log
        $result = $wpdb->insert(
            $this->debug_logs_table,
            $insert_data,
            $insert_format
        );
        
        if ($result === false && !empty($wpdb->last_error)) {
            error_log('Academy AI Assistant: Debug log insert error: ' . $wpdb->last_error);
            error_log('Academy AI Assistant: Insert data: ' . print_r($insert_data, true));
        }
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Sanitize debug data to remove sensitive information
     * Security: Removes API keys, passwords, and other sensitive data
     * 
     * @param array $data Data to sanitize
     * @return array Sanitized data
     */
    private function sanitize_debug_data($data) {
        if (!is_array($data)) {
            return $data;
        }
        
        $sensitive_keys = array(
            'api_key',
            'apiKey',
            'password',
            'pass',
            'secret',
            'token',
            'auth',
            'authorization',
            'bearer'
        );
        
        $sanitized = array();
        
        foreach ($data as $key => $value) {
            $key_lower = strtolower($key);
            
            // Skip sensitive keys
            foreach ($sensitive_keys as $sensitive) {
                if (strpos($key_lower, $sensitive) !== false) {
                    $sanitized[$key] = '[REDACTED]';
                    continue 2;
                }
            }
            
            // Recursively sanitize nested arrays
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize_debug_data($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Log API call
     * 
     * @param array $request_data Request data sent to API
     * @param array|WP_Error $response Response from API
     * @param int $response_time Response time in milliseconds
     * @param int $tokens_used Tokens used
     * @param int $user_id User ID
     * @param int $session_id Session ID
     * @param string $location Location used
     */
    public function log_api_call($request_data, $response, $response_time, $tokens_used = null, $user_id = null, $session_id = null, $location = null) {
        $message = is_wp_error($response) 
            ? 'API call failed: ' . $response->get_error_message()
            : 'API call successful';
        
        $data = array(
            'request' => $request_data,
            'response' => is_wp_error($response) ? $response->get_error_message() : 'Success'
        );
        
        $this->log('api_call', $message, $data, $user_id, $session_id, $location, $response_time, $tokens_used);
    }
    
    /**
     * Log context building
     * 
     * @param array $context_data Context data built
     * @param int $user_id User ID
     * @param string $query User query
     */
    public function log_context_build($context_data, $user_id = null, $query = '') {
        $data = array(
            'context' => $context_data,
            'query' => $query
        );
        
        $this->log('context_build', 'Context built for query', $data, $user_id);
    }
    
    /**
     * Log embedding search
     * 
     * @param string $query Search query
     * @param array $results Search results
     * @param int $response_time Response time in milliseconds
     * @param int $user_id User ID
     */
    public function log_embedding_search($query, $results, $response_time = null, $user_id = null) {
        $data = array(
            'query' => $query,
            'results_count' => count($results),
            'results' => $results
        );
        
        $this->log('embedding_search', 'Embedding search performed', $data, $user_id, null, null, $response_time);
    }
    
    /**
     * Log error
     * 
     * @param string $message Error message
     * @param array $context Error context
     * @param int $user_id User ID
     */
    public function log_error($message, $context = array(), $user_id = null) {
        $data = array(
            'error_context' => $context
        );
        
        $this->log('error', $message, $data, $user_id);
    }
    
    /**
     * Get debug logs
     * Security: Only admins can view logs
     * Performance: Pagination and filtering
     * 
     * @param array $args Query arguments
     * @return array Logs
     */
    public function get_logs($args = array()) {
        // Security: Only admins can view logs
        if (!current_user_can('manage_options')) {
            return array();
        }
        
        global $wpdb;
        
        $defaults = array(
            'user_id' => null,
            'log_type' => null,
            'limit' => 50,
            'offset' => 0,
            'order_by' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Security: Validate and sanitize
        $where = array('1=1');
        $where_values = array();
        
        if ($args['user_id']) {
            $user_id = absint($args['user_id']);
            $where[] = 'user_id = %d';
            $where_values[] = $user_id;
        }
        
        if ($args['log_type']) {
            $log_type = sanitize_text_field($args['log_type']);
            $where[] = 'log_type = %s';
            $where_values[] = $log_type;
        }
        
        // Performance: Limit results
        $limit = min(absint($args['limit']), 200); // Max 200
        $offset = absint($args['offset']);
        
        // Security: Validate order_by
        $allowed_order_by = array('id', 'user_id', 'log_type', 'created_at');
        $order_by = in_array($args['order_by'], $allowed_order_by, true) ? $args['order_by'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        
        $where_clause = implode(' AND ', $where);
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare(
                "SELECT * FROM {$this->debug_logs_table} 
                 WHERE {$where_clause}
                 ORDER BY {$order_by} {$order}
                 LIMIT %d OFFSET %d",
                array_merge($where_values, array($limit, $offset))
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM {$this->debug_logs_table} 
                 WHERE {$where_clause}
                 ORDER BY {$order_by} {$order}
                 LIMIT %d OFFSET %d",
                $limit,
                $offset
            );
        }
        
        $logs = $wpdb->get_results($query);
        
        return $logs ? $logs : array();
    }
    
    /**
     * Clear old debug logs
     * Security: Only admins can clear logs
     * 
     * @param int $days_old Delete logs older than this many days
     * @return int Number of logs deleted
     */
    public function clear_old_logs($days_old = 30) {
        // Security: Only admins can clear logs
        if (!current_user_can('manage_options')) {
            return 0;
        }
        
        global $wpdb;
        
        $days_old = absint($days_old);
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->debug_logs_table} 
             WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days_old
        ));
        
        return $deleted;
    }
    
    /**
     * Delete all debug logs
     * Security: Only admins can delete all logs
     * 
     * @return int Number of logs deleted
     */
    public function delete_all_logs() {
        // Security: Only admins can delete all logs
        if (!current_user_can('manage_options')) {
            return 0;
        }
        
        global $wpdb;
        
        $deleted = $wpdb->query("DELETE FROM {$this->debug_logs_table}");
        
        return $deleted;
    }
}

