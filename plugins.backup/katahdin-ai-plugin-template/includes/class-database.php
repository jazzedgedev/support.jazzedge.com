<?php
/**
 * Database Class for Katahdin AI Plugin Template
 */

if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Plugin_Template_Database {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'katahdin_ai_plugin_template_logs';
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            data longtext,
            result longtext,
            status varchar(20) DEFAULT 'pending',
            error_message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY timestamp (timestamp),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get table status
     */
    public function get_table_status() {
        global $wpdb;
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name;
        
        if (!$table_exists) {
            return 'Table does not exist';
        }
        
        $row_count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        return array(
            'table_exists' => true,
            'table_name' => $this->table_name,
            'row_count' => $row_count
        );
    }
}
