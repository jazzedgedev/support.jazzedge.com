<?php
/**
 * Webhook Logging Class
 * 
 * Handles logging of all webhook requests and responses for testing and retention
 */

if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Webhook_Logger {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'katahdin_ai_webhook_logs';
    }
    
    /**
     * Create the webhook logs table
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            webhook_id varchar(50) NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            method varchar(10) NOT NULL,
            url text NOT NULL,
            headers longtext,
            body longtext,
            ip_address varchar(45),
            user_agent text,
            response_code int(3),
            response_body longtext,
            processing_time_ms int(10),
            ai_response longtext,
            email_sent tinyint(1) DEFAULT 0,
            email_response text,
            error_message text,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY webhook_id (webhook_id),
            KEY timestamp (timestamp),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Log an incoming webhook request
     */
    public function log_request($webhook_id, $request_data) {
        global $wpdb;
        
        $headers = isset($request_data['headers']) ? json_encode($request_data['headers']) : null;
        $body = isset($request_data['body']) ? json_encode($request_data['body']) : null;
        $ip_address = $this->get_client_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'webhook_id' => $webhook_id,
                'method' => $_SERVER['REQUEST_METHOD'],
                'url' => $this->get_current_url(),
                'headers' => $headers,
                'body' => $body,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'status' => 'received'
            ),
            array(
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
            )
        );
        
        if ($result === false) {
            error_log('Katahdin AI Webhook: Failed to log request - ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update log with processing results
     */
    public function update_log($log_id, $data) {
        global $wpdb;
        
        $update_data = array();
        $update_format = array();
        
        if (isset($data['response_code'])) {
            $update_data['response_code'] = $data['response_code'];
            $update_format[] = '%d';
        }
        
        if (isset($data['response_body'])) {
            $update_data['response_body'] = $data['response_body'];
            $update_format[] = '%s';
        }
        
        if (isset($data['processing_time_ms'])) {
            $update_data['processing_time_ms'] = $data['processing_time_ms'];
            $update_format[] = '%d';
        }
        
        if (isset($data['ai_response'])) {
            $update_data['ai_response'] = $data['ai_response'];
            $update_format[] = '%s';
        }
        
        if (isset($data['email_sent'])) {
            $update_data['email_sent'] = $data['email_sent'];
            $update_format[] = '%d';
        }
        
        if (isset($data['email_response'])) {
            $update_data['email_response'] = $data['email_response'];
            $update_format[] = '%s';
        }
        
        if (isset($data['error_message'])) {
            $update_data['error_message'] = $data['error_message'];
            $update_format[] = '%s';
        }
        
        if (isset($data['status'])) {
            $update_data['status'] = $data['status'];
            $update_format[] = '%s';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $log_id),
            $update_format,
            array('%d')
        );
        
        if ($result === false) {
            error_log('Katahdin AI Webhook: Failed to update log - ' . $wpdb->last_error);
            return false;
        }
        
        return true;
    }
    
    /**
     * Get webhook logs with pagination
     */
    public function get_logs($limit = 50, $offset = 0, $status = null) {
        global $wpdb;
        
        $where_clause = '';
        $where_values = array();
        
        if ($status) {
            $where_clause = 'WHERE status = %s';
            $where_values[] = $status;
        }
        
        $sql = "SELECT * FROM {$this->table_name} {$where_clause} ORDER BY timestamp DESC LIMIT %d OFFSET %d";
        
        // Always add limit and offset to the values array
        $where_values[] = $limit;
        $where_values[] = $offset;
        
        // Prepare the SQL with all values
        $sql = $wpdb->prepare($sql, $where_values);
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get a specific log entry
     */
    public function get_log($log_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $log_id
        ));
    }
    
    /**
     * Get log statistics
     */
    public function get_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Total logs
        $stats['total_logs'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        // Logs by status
        $status_counts = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM {$this->table_name} GROUP BY status"
        );
        
        $stats['by_status'] = array();
        foreach ($status_counts as $status) {
            $stats['by_status'][$status->status] = $status->count;
        }
        
        // Recent activity (last 24 hours)
        $stats['recent_24h'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        // Recent activity (last 7 days)
        $stats['recent_7d'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        return $stats;
    }
    
    /**
     * Clean up old logs based on retention policy
     */
    public function cleanup_old_logs($retention_days = 30) {
        global $wpdb;
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
        
        return $deleted;
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    }
    
    /**
     * Get current URL
     */
    private function get_current_url() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        return $protocol . '://' . $host . $uri;
    }
    
    /**
     * Get log statistics
     */
    public function get_log_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Total logs
        $stats['total_logs'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        // Success logs
        $stats['success_logs'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'success'");
        
        // Error logs
        $stats['error_logs'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'error'");
        
        // Pending logs
        $stats['pending_logs'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'pending'");
        
        // Recent activity (last 24 hours)
        $stats['recent_logs'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        
        return $stats;
    }
    
    /**
     * Cleanup old logs
     */
    public function cleanup_logs($retention_days = 30) {
        global $wpdb;
        
        // Debug: Log the cleanup attempt
        error_log("Katahdin AI Webhook - cleanup_logs called with retention_days: $retention_days");
        
        // Special case: if retention_days is 0, delete all logs
        if ($retention_days == 0) {
            $deleted = $wpdb->query("DELETE FROM {$this->table_name}");
            error_log("Katahdin AI Webhook - deleted all logs: $deleted");
        } else {
            $deleted = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $retention_days
            ));
            error_log("Katahdin AI Webhook - deleted old logs: $deleted");
        }
        
        return array(
            'deleted_count' => $deleted,
            'retention_days' => $retention_days
        );
    }
    
    /**
     * Generate unique webhook ID
     */
    public function generate_webhook_id() {
        return 'webhook_' . time() . '_' . wp_generate_password(8, false);
    }
}
