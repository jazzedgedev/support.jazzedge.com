<?php
/**
 * Logger Class for Katahdin AI Plugin Template
 */

if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Plugin_Template_Logger {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'katahdin_ai_plugin_template_logs';
    }
    
    /**
     * Log a request
     */
    public function log_request($data, $result) {
        global $wpdb;
        
        $log_data = array(
            'timestamp' => current_time('mysql'),
            'data' => json_encode($data),
            'result' => json_encode($result),
            'status' => is_wp_error($result) ? 'error' : 'success',
            'error_message' => is_wp_error($result) ? $result->get_error_message() : null,
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($this->table_name, $log_data);
        
        if ($result === false) {
            error_log('Katahdin AI Plugin Template: Failed to log request - ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get logs
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
        
        // Recent activity (last 24 hours)
        $stats['recent_logs'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        
        return $stats;
    }
    
    /**
     * Cleanup old logs
     */
    public function cleanup_logs($retention_days = 30) {
        global $wpdb;
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
        
        return array(
            'deleted_count' => $deleted,
            'retention_days' => $retention_days
        );
    }
}
