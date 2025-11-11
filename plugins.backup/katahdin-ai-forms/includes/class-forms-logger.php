<?php
/**
 * Forms Logger for Katahdin AI Forms
 * Handles logging of form submissions and AI analysis results
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Forms_Logger {
    
    /**
     * Table name for logs
     */
    private $table_name;
    
    /**
     * Initialize the logger
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'katahdin_ai_forms_logs';
    }
    
    /**
     * Create the logs table
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            webhook_id varchar(50) NOT NULL,
            status varchar(20) DEFAULT 'received',
            request_data longtext,
            response_code int(11) DEFAULT 200,
            ai_response longtext,
            error_message text,
            email_sent tinyint(1) DEFAULT 0,
            processing_time_ms int(11) DEFAULT 0,
            form_email varchar(255),
            form_name varchar(255),
            form_id varchar(100),
            entry_id varchar(100),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY webhook_id (webhook_id),
            KEY status (status),
            KEY created_at (created_at),
            KEY form_id (form_id),
            KEY entry_id (entry_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Log a request
     */
    public function log_request($webhook_id, $request_data) {
        global $wpdb;
        
        try {
            $result = $wpdb->insert(
                $this->table_name,
                array(
                    'webhook_id' => sanitize_text_field($webhook_id),
                    'status' => 'received',
                    'request_data' => wp_json_encode($request_data),
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s')
            );
            
            if ($result === false) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Katahdin AI Forms Logger: Failed to insert log - ' . $wpdb->last_error);
                }
                return false;
            }
            
            return $wpdb->insert_id;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Logger error: ' . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Update a log entry
     */
    public function update_log($log_id, $data) {
        global $wpdb;
        
        try {
            $update_data = array();
            $format = array();
            
            if (isset($data['status'])) {
                $update_data['status'] = sanitize_text_field($data['status']);
                $format[] = '%s';
            }
            
            if (isset($data['response_code'])) {
                $update_data['response_code'] = intval($data['response_code']);
                $format[] = '%d';
            }
            
            if (isset($data['ai_response'])) {
                $update_data['ai_response'] = sanitize_textarea_field($data['ai_response']);
                $format[] = '%s';
            }
            
            if (isset($data['error_message'])) {
                $update_data['error_message'] = sanitize_textarea_field($data['error_message']);
                $format[] = '%s';
            }
            
            if (isset($data['email_sent'])) {
                $update_data['email_sent'] = $data['email_sent'] ? 1 : 0;
                $format[] = '%d';
            }
            
            if (isset($data['processing_time_ms'])) {
                $update_data['processing_time_ms'] = intval($data['processing_time_ms']);
                $format[] = '%d';
            }
            
            if (isset($data['form_email'])) {
                $update_data['form_email'] = sanitize_email($data['form_email']);
                $format[] = '%s';
            }
            
            if (isset($data['form_name'])) {
                $update_data['form_name'] = sanitize_text_field($data['form_name']);
                $format[] = '%s';
            }
            
            if (isset($data['form_id'])) {
                $update_data['form_id'] = sanitize_text_field($data['form_id']);
                $format[] = '%s';
            }
            
            if (isset($data['entry_id'])) {
                $update_data['entry_id'] = sanitize_text_field($data['entry_id']);
                $format[] = '%s';
            }
            
            $update_data['updated_at'] = current_time('mysql');
            $format[] = '%s';
            
            if (empty($update_data)) {
                return false;
            }
            
            $result = $wpdb->update(
                $this->table_name,
                $update_data,
                array('id' => intval($log_id)),
                $format,
                array('%d')
            );
            
            if ($result === false) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Katahdin AI Forms Logger: Failed to update log - ' . $wpdb->last_error);
                }
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Logger error: ' . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Get logs with pagination and filtering
     */
    public function get_logs($limit = 50, $offset = 0, $status = null) {
        global $wpdb;
        
        try {
            $where_clause = '';
            $params = array();
            
            if ($status) {
                $where_clause = 'WHERE status = %s';
                $params[] = sanitize_text_field($status);
            }
            
            $sql = "SELECT * FROM {$this->table_name} {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
            $params[] = intval($limit);
            $params[] = intval($offset);
            
            if (!empty($params)) {
                $results = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
            } else {
                $results = $wpdb->get_results($sql, ARRAY_A);
            }
            
            // Decode JSON fields
            foreach ($results as &$log) {
                if (!empty($log['request_data'])) {
                    $log['request_data'] = json_decode($log['request_data'], true);
                }
            }
            
            return $results;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Logger error: ' . $e->getMessage());
            }
            return array();
        }
    }
    
    /**
     * Get a single log by ID
     */
    public function get_log_by_id($log_id) {
        global $wpdb;
        
        try {
            $log = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", intval($log_id)),
                ARRAY_A
            );
            
            if ($log && !empty($log['request_data'])) {
                $log['request_data'] = json_decode($log['request_data'], true);
            }
            
            return $log;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Logger error: ' . $e->getMessage());
            }
            return null;
        }
    }
    
    /**
     * Get log statistics
     */
    public function get_log_stats() {
        global $wpdb;
        
        try {
            $stats = array();
            
            // Total logs
            $total = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
            $stats['total'] = intval($total);
            
            // Logs by status
            $status_counts = $wpdb->get_results(
                "SELECT status, COUNT(*) as count FROM {$this->table_name} GROUP BY status",
                ARRAY_A
            );
            
            $stats['by_status'] = array();
            foreach ($status_counts as $status) {
                $stats['by_status'][$status['status']] = intval($status['count']);
            }
            
            // Recent activity (last 24 hours)
            $recent = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->table_name} WHERE created_at >= %s",
                    date('Y-m-d H:i:s', strtotime('-24 hours'))
                )
            );
            $stats['recent_24h'] = intval($recent);
            
            // Average processing time
            $avg_time = $wpdb->get_var(
                "SELECT AVG(processing_time_ms) FROM {$this->table_name} WHERE processing_time_ms > 0"
            );
            $stats['avg_processing_time_ms'] = round(floatval($avg_time), 2);
            
            // Email success rate
            $email_stats = $wpdb->get_row(
                "SELECT 
                    COUNT(*) as total,
                    SUM(email_sent) as sent
                FROM {$this->table_name} 
                WHERE status = 'success'",
                ARRAY_A
            );
            
            if ($email_stats && $email_stats['total'] > 0) {
                $stats['email_success_rate'] = round(
                    ($email_stats['sent'] / $email_stats['total']) * 100, 
                    2
                );
            } else {
                $stats['email_success_rate'] = 0;
            }
            
            return $stats;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Logger error: ' . $e->getMessage());
            }
            return array();
        }
    }
    
    /**
     * Cleanup old logs
     */
    public function cleanup_logs($retention_days = 30) {
        global $wpdb;
        
        try {
            $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
            
            $deleted = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$this->table_name} WHERE created_at < %s",
                    $cutoff_date
                )
            );
            
            return array(
                'success' => true,
                'deleted_count' => intval($deleted),
                'retention_days' => intval($retention_days),
                'cutoff_date' => $cutoff_date
            );
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Logger error: ' . $e->getMessage());
            }
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Delete a specific log
     */
    public function delete_log($log_id) {
        global $wpdb;
        
        try {
            $result = $wpdb->delete(
                $this->table_name,
                array('id' => intval($log_id)),
                array('%d')
            );
            
            return $result !== false;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Logger error: ' . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Clear all logs
     */
    public function clear_all_logs() {
        global $wpdb;
        
        try {
            $result = $wpdb->query("TRUNCATE TABLE {$this->table_name}");
            return $result !== false;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Logger error: ' . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Generate unique webhook ID
     */
    public function generate_webhook_id() {
        return 'webhook_' . uniqid() . '_' . time();
    }
    
    /**
     * Get recent logs (from WordPress options)
     */
    public function get_recent_logs($limit = 10) {
        $logs = get_option('katahdin_ai_forms_recent_logs', array());
        return array_slice($logs, 0, $limit);
    }
    
    /**
     * Get logs by form ID
     */
    public function get_logs_by_form_id($form_id, $limit = 50) {
        global $wpdb;
        
        try {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$this->table_name} WHERE form_id = %s ORDER BY created_at DESC LIMIT %d",
                    sanitize_text_field($form_id),
                    intval($limit)
                ),
                ARRAY_A
            );
            
            // Decode JSON fields
            foreach ($results as &$log) {
                if (!empty($log['request_data'])) {
                    $log['request_data'] = json_decode($log['request_data'], true);
                }
            }
            
            return $results;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Logger error: ' . $e->getMessage());
            }
            return array();
        }
    }
    
    /**
     * Get logs by entry ID
     */
    public function get_logs_by_entry_id($entry_id, $limit = 50) {
        global $wpdb;
        
        try {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$this->table_name} WHERE entry_id = %s ORDER BY created_at DESC LIMIT %d",
                    sanitize_text_field($entry_id),
                    intval($limit)
                ),
                ARRAY_A
            );
            
            // Decode JSON fields
            foreach ($results as &$log) {
                if (!empty($log['request_data'])) {
                    $log['request_data'] = json_decode($log['request_data'], true);
                }
            }
            
            return $results;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Logger error: ' . $e->getMessage());
            }
            return array();
        }
    }
}
