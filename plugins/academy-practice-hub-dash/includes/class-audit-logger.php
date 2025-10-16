<?php
/**
 * Audit Logger for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Audit_Logger {
    
    private static $instance = null;
    private $logger;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->logger = JPH_Logger::get_instance();
    }
    
    /**
     * Log admin action
     */
    public function log_admin_action($action, $details = array(), $user_id = null) {
        $user_id = $user_id ?: get_current_user_id();
        $user = get_user_by('id', $user_id);
        
        $audit_data = array(
            'action' => $action,
            'user_id' => $user_id,
            'user_login' => $user ? $user->user_login : 'unknown',
            'user_email' => $user ? $user->user_email : 'unknown',
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => current_time('mysql'),
            'details' => $details
        );
        
        $this->logger->info('Admin action', $audit_data);
        
        // Also store in database for audit trail
        $this->store_audit_log($audit_data);
    }
    
    /**
     * Log user action
     */
    public function log_user_action($action, $details = array(), $user_id = null) {
        $user_id = $user_id ?: get_current_user_id();
        $user = get_user_by('id', $user_id);
        
        $audit_data = array(
            'action' => $action,
            'user_id' => $user_id,
            'user_login' => $user ? $user->user_login : 'unknown',
            'ip_address' => $this->get_client_ip(),
            'timestamp' => current_time('mysql'),
            'details' => $details
        );
        
        $this->logger->info('User action', $audit_data);
        
        // Store in database for audit trail
        $this->store_audit_log($audit_data);
    }
    
    /**
     * Log system event
     */
    public function log_system_event($event, $details = array()) {
        $audit_data = array(
            'event' => $event,
            'timestamp' => current_time('mysql'),
            'details' => $details
        );
        
        $this->logger->info('System event', $audit_data);
        
        // Store in database for audit trail
        $this->store_audit_log($audit_data);
    }
    
    /**
     * Store audit log in database
     */
    private function store_audit_log($audit_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_audit_logs';
        
        // Create table if it doesn't exist
        $this->create_audit_table();
        
        $wpdb->insert($table_name, array(
            'action' => $audit_data['action'] ?? $audit_data['event'] ?? 'unknown',
            'user_id' => $audit_data['user_id'] ?? null,
            'user_login' => $audit_data['user_login'] ?? null,
            'user_email' => $audit_data['user_email'] ?? null,
            'ip_address' => $audit_data['ip_address'] ?? null,
            'user_agent' => $audit_data['user_agent'] ?? null,
            'details' => json_encode($audit_data['details'] ?? array()),
            'created_at' => $audit_data['timestamp']
        ));
    }
    
    /**
     * Create audit logs table
     */
    private function create_audit_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_audit_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            action varchar(100) NOT NULL,
            user_id bigint(20) NULL,
            user_login varchar(60) NULL,
            user_email varchar(100) NULL,
            ip_address varchar(45) NULL,
            user_agent text NULL,
            details longtext NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get audit logs
     */
    public function get_audit_logs($limit = 100, $offset = 0, $user_id = null, $action = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_audit_logs';
        
        $where_conditions = array();
        $where_values = array();
        
        if ($user_id) {
            $where_conditions[] = 'user_id = %d';
            $where_values[] = $user_id;
        }
        
        if ($action) {
            $where_conditions[] = 'action = %s';
            $where_values[] = $action;
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $query = "SELECT * FROM $table_name $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $where_values[] = $limit;
        $where_values[] = $offset;
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        } else {
            $query = $wpdb->prepare($query, $limit, $offset);
        }
        
        return $wpdb->get_results($query, ARRAY_A);
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
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Clean up old audit logs
     */
    public function cleanup_audit_logs($days = 90) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_audit_logs';
        
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE created_at < %s",
            date('Y-m-d H:i:s', strtotime("-{$days} days"))
        ));
        
        $this->logger->info('Audit logs cleaned up', array(
            'days' => $days,
            'deleted_rows' => $result
        ));
        
        return $result;
    }
}
