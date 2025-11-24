<?php
/**
 * Logger Class for Bunny Video Webhook
 * 
 * Handles logging of webhook events and errors for manual review
 * 
 * @package Bunny_Video_Webhook
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Bunny_Video_Webhook_Logger {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Table name for logs
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'bunny_video_webhook_logs';
        $this->create_table();
    }
    
    /**
     * Create logs table
     */
    private function create_table() {
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            log_type varchar(50) NOT NULL DEFAULT 'info',
            message text NOT NULL,
            video_id varchar(255) DEFAULT NULL,
            video_filename varchar(500) DEFAULT NULL,
            lesson_id int(11) DEFAULT NULL,
            chapter_id int(11) DEFAULT NULL,
            bunny_url varchar(500) DEFAULT NULL,
            webhook_data longtext DEFAULT NULL,
            error_details longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY log_type (log_type),
            KEY video_id (video_id),
            KEY lesson_id (lesson_id),
            KEY chapter_id (chapter_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Log an event
     * 
     * @param string $log_type Type of log (info, error, warning, success)
     * @param string $message Log message
     * @param array $data Additional data to log
     * @return int|false Log ID or false on failure
     */
    public function log($log_type, $message, $data = array()) {
        $insert_data = array(
            'log_type' => sanitize_text_field($log_type),
            'message' => sanitize_text_field($message),
            'created_at' => current_time('mysql')
        );
        
        // Add optional fields from data array
        if (isset($data['video_id'])) {
            $insert_data['video_id'] = sanitize_text_field($data['video_id']);
        }
        if (isset($data['video_filename'])) {
            $insert_data['video_filename'] = sanitize_text_field($data['video_filename']);
        }
        if (isset($data['lesson_id'])) {
            $insert_data['lesson_id'] = intval($data['lesson_id']);
        }
        if (isset($data['chapter_id'])) {
            $insert_data['chapter_id'] = intval($data['chapter_id']);
        }
        if (isset($data['bunny_url'])) {
            $insert_data['bunny_url'] = esc_url_raw($data['bunny_url']);
        }
        if (isset($data['webhook_data'])) {
            $insert_data['webhook_data'] = wp_json_encode($data['webhook_data']);
        }
        if (isset($data['error_details'])) {
            $insert_data['error_details'] = wp_kses_post($data['error_details']);
        }
        
        $result = $this->wpdb->insert(
            $this->table_name,
            $insert_data,
            array('%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s')
        );
        
        if ($result) {
            return $this->wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Log an error for manual review
     * 
     * @param string $message Error message
     * @param array $data Additional error data
     * @return int|false Log ID or false on failure
     */
    public function log_error($message, $data = array()) {
        return $this->log('error', $message, $data);
    }
    
    /**
     * Log a success event
     * 
     * @param string $message Success message
     * @param array $data Additional data
     * @return int|false Log ID or false on failure
     */
    public function log_success($message, $data = array()) {
        return $this->log('success', $message, $data);
    }
    
    /**
     * Log an info event
     * 
     * @param string $message Info message
     * @param array $data Additional data
     * @return int|false Log ID or false on failure
     */
    public function log_info($message, $data = array()) {
        return $this->log('info', $message, $data);
    }
    
    /**
     * Get logs
     * 
     * @param array $args Query arguments
     * @return array Log entries
     */
    public function get_logs($args = array()) {
        $defaults = array(
            'log_type' => '',
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $where_values = array();
        
        if (!empty($args['log_type'])) {
            $where[] = 'log_type = %s';
            $where_values[] = $args['log_type'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        if (!empty($where_values)) {
            $where_clause = $this->wpdb->prepare($where_clause, $where_values);
        }
        
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        if (!$orderby) {
            $orderby = 'created_at DESC';
        }
        
        $query = "SELECT * FROM {$this->table_name} 
                  WHERE {$where_clause} 
                  ORDER BY {$orderby} 
                  LIMIT %d OFFSET %d";
        
        $query = $this->wpdb->prepare(
            $query,
            $args['limit'],
            $args['offset']
        );
        
        return $this->wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Get log count
     * 
     * @param string $log_type Optional log type filter
     * @return int Log count
     */
    public function get_log_count($log_type = '') {
        if (!empty($log_type)) {
            return $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE log_type = %s",
                $log_type
            ));
        }
        
        return $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }
    
    /**
     * Delete old logs
     * 
     * @param int $days Number of days to keep logs
     * @return int Number of deleted logs
     */
    public function cleanup_logs($days = 30) {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->wpdb->query($this->wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE created_at < %s",
            $date
        ));
    }
    
    /**
     * Delete all logs
     * 
     * @return int Number of deleted logs
     */
    public function delete_all_logs() {
        return $this->wpdb->query("DELETE FROM {$this->table_name}");
    }
    
    /**
     * Check if we've already logged a success for this video_id
     * 
     * @param string $video_id Video ID
     * @return bool True if success already logged
     */
    public function has_success_log($video_id) {
        if (empty($video_id)) {
            return false;
        }
        
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
             WHERE video_id = %s AND log_type = 'success' 
             AND message LIKE %s",
            $video_id,
            '%Video processed and lesson updated successfully%'
        ));
        
        return $count > 0;
    }
    
    /**
     * Check if we've already logged this error recently (within last hour)
     * 
     * @param string $video_id Video ID
     * @param string $error_message Error message to check
     * @return bool True if error already logged recently
     */
    public function has_recent_error_log($video_id, $error_message) {
        if (empty($video_id) || empty($error_message)) {
            return false;
        }
        
        $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
             WHERE video_id = %s AND log_type = 'error' 
             AND message = %s AND created_at > %s",
            $video_id,
            $error_message,
            $one_hour_ago
        ));
        
        return $count > 0;
    }
    
    /**
     * Check if we've already logged info for this video_id recently (within last hour)
     * 
     * @param string $video_id Video ID
     * @param string $info_message Info message to check
     * @return bool True if info already logged recently
     */
    public function has_recent_info_log($video_id, $info_message) {
        if (empty($video_id) || empty($info_message)) {
            return false;
        }
        
        $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
             WHERE video_id = %s AND log_type = 'info' 
             AND message = %s AND created_at > %s",
            $video_id,
            $info_message,
            $one_hour_ago
        ));
        
        return $count > 0;
    }
}

