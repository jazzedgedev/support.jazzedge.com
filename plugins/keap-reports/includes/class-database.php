<?php
/**
 * Database operations for Keap Reports
 * 
 * @package Keap_Reports
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Keap_Reports_Database {
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for report definitions
        $reports_table = $wpdb->prefix . 'keap_reports';
        $reports_sql = "CREATE TABLE IF NOT EXISTS {$reports_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            report_id int(11) NOT NULL,
            report_uuid varchar(255) NOT NULL,
            report_type varchar(50) NOT NULL DEFAULT 'custom',
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_report (report_id, report_uuid),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        // Table for monthly aggregated report data
        $data_table = $wpdb->prefix . 'keap_report_data';
        $data_sql = "CREATE TABLE IF NOT EXISTS {$data_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            report_id bigint(20) NOT NULL,
            `year` int(4) NOT NULL,
            `month` tinyint(2) NOT NULL,
            value decimal(15,2) NOT NULL DEFAULT 0.00,
            metadata text NULL,
            fetched_at datetime NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_report_month (report_id, `year`, `month`),
            KEY report_id (report_id),
            KEY report_year_month (`year`, `month`)
        ) $charset_collate;";
        
        // Table for logs
        $logs_table = $wpdb->prefix . 'keap_reports_logs';
        $logs_sql = "CREATE TABLE IF NOT EXISTS {$logs_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            log_level varchar(20) NOT NULL DEFAULT 'info',
            message text NOT NULL,
            context text NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY log_level (log_level),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Use dbDelta for reports table (no reserved words)
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($reports_sql);
        
        // For data table with reserved words, use direct SQL execution
        // dbDelta has issues with backticks in index definitions
        $data_exists = $wpdb->get_var("SHOW TABLES LIKE '$data_table'") == $data_table;
        
        if (!$data_exists) {
            // Execute directly - dbDelta doesn't handle reserved words well in indexes
            $result = $wpdb->query($data_sql);
            if ($result === false) {
                error_log('Keap Reports: Failed to create data table. Error: ' . $wpdb->last_error);
                // Try without the problematic index
                $data_sql_simple = "CREATE TABLE IF NOT EXISTS {$data_table} (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    report_id bigint(20) NOT NULL,
                    `year` int(4) NOT NULL,
                    `month` tinyint(2) NOT NULL,
                    value decimal(15,2) NOT NULL DEFAULT 0.00,
                    metadata text NULL,
                    fetched_at datetime NOT NULL,
                    created_at datetime NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY unique_report_month (report_id, `year`, `month`),
                    KEY report_id (report_id)
                ) $charset_collate;";
                $wpdb->query($data_sql_simple);
                // Add the year_month index separately if table was created
                $data_exists = $wpdb->get_var("SHOW TABLES LIKE '$data_table'") == $data_table;
                if ($data_exists) {
                    $wpdb->query("ALTER TABLE {$data_table} ADD INDEX report_year_month (`year`, `month`)");
                }
            }
        }
        
        // Create logs table
        dbDelta($logs_sql);
        
        return true;
    }
    
    /**
     * Get all reports
     * 
     * @param bool $active_only Whether to return only active reports
     * @return array
     */
    public function get_reports($active_only = false) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports';
        $where = $active_only ? 'WHERE is_active = 1' : '';
        
        $results = $wpdb->get_results(
            "SELECT * FROM {$table_name} {$where} ORDER BY name ASC",
            ARRAY_A
        );
        
        return $results ? $results : array();
    }
    
    /**
     * Get a single report by ID
     * 
     * @param int $report_id
     * @return array|null
     */
    public function get_report($report_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports';
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $report_id
            ),
            ARRAY_A
        );
        
        return $result;
    }
    
    /**
     * Save a report (insert or update)
     * 
     * @param array $data Report data
     * @return int|false Report ID on success, false on failure
     */
    public function save_report($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports';
        
        $defaults = array(
            'name' => '',
            'report_id' => 0,
            'report_uuid' => '',
            'report_type' => 'custom',
            'is_active' => 1
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Sanitize data
        $save_data = array(
            'name' => sanitize_text_field($data['name']),
            'report_id' => absint($data['report_id']),
            'report_uuid' => sanitize_text_field($data['report_uuid']),
            'report_type' => sanitize_text_field($data['report_type']),
            'is_active' => isset($data['is_active']) ? 1 : 0
        );
        
        if (empty($save_data['name']) || empty($save_data['report_id']) || empty($save_data['report_uuid'])) {
            return false;
        }
        
        if (isset($data['id']) && !empty($data['id'])) {
            // Update existing report
            $id = absint($data['id']);
            $save_data['updated_at'] = current_time('mysql');
            
            $result = $wpdb->update(
                $table_name,
                $save_data,
                array('id' => $id)
            );
            
            return $result !== false ? $id : false;
        } else {
            // Insert new report
            $save_data['created_at'] = current_time('mysql');
            $save_data['updated_at'] = current_time('mysql');
            
            $result = $wpdb->insert($table_name, $save_data);
            
            return $result !== false ? $wpdb->insert_id : false;
        }
    }
    
    /**
     * Delete a report
     * 
     * @param int $report_id
     * @return bool
     */
    public function delete_report($report_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports';
        
        // Also delete associated data
        $data_table = $wpdb->prefix . 'keap_report_data';
        $wpdb->delete($data_table, array('report_id' => $report_id), array('%d'));
        
        $result = $wpdb->delete($table_name, array('id' => $report_id), array('%d'));
        
        return $result !== false;
    }
    
    /**
     * Get report data for a specific month
     * 
     * @param int $report_id
     * @param int $year
     * @param int $month
     * @return array|null
     */
    public function get_report_data($report_id, $year, $month) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_report_data';
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE report_id = %d AND `year` = %d AND `month` = %d",
                $report_id,
                $year,
                $month
            ),
            ARRAY_A
        );
        
        return $result;
    }
    
    /**
     * Save report data for a month
     * 
     * @param int $report_id
     * @param int $year
     * @param int $month
     * @param float $value
     * @param array $metadata Optional metadata
     * @return bool
     */
    public function save_report_data($report_id, $year, $month, $value, $metadata = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_report_data';
        
        // Check if table exists, create if not
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            error_log('Keap Reports: Table ' . $table_name . ' does not exist. Creating it now...');
            self::create_tables();
            // Verify it was created
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                error_log('Keap Reports: Failed to create table ' . $table_name);
                return false;
            }
        }
        
        // Check if data already exists
        $existing = $this->get_report_data($report_id, $year, $month);
        
        $metadata_json = !empty($metadata) ? json_encode($metadata) : null;
        $fetched_at = current_time('mysql');
        $created_at = current_time('mysql');
        
        if ($existing) {
            // Update existing - use raw SQL with backticks for reserved words
            $result = $wpdb->query($wpdb->prepare(
                "UPDATE {$table_name} 
                SET report_id = %d, `year` = %d, `month` = %d, value = %f, fetched_at = %s, metadata = %s 
                WHERE report_id = %d AND `year` = %d AND `month` = %d",
                absint($report_id),
                absint($year),
                absint($month),
                floatval($value),
                $fetched_at,
                $metadata_json,
                $report_id,
                $year,
                $month
            ));
        } else {
            // Insert new - use raw SQL with backticks for reserved words
            $result = $wpdb->query($wpdb->prepare(
                "INSERT INTO {$table_name} (report_id, `year`, `month`, value, metadata, fetched_at, created_at) 
                VALUES (%d, %d, %d, %f, %s, %s, %s)",
                absint($report_id),
                absint($year),
                absint($month),
                floatval($value),
                $metadata_json,
                $fetched_at,
                $created_at
            ));
        }
        
        return $result !== false;
    }
    
    /**
     * Get monthly comparison data
     * 
     * @param int $report_id
     * @param int $current_year
     * @param int $current_month
     * @return array
     */
    public function get_monthly_comparison($report_id, $current_year, $current_month) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_report_data';
        
        // Get current month data
        $current = $this->get_report_data($report_id, $current_year, $current_month);
        
        // Calculate previous month
        $prev_month = $current_month - 1;
        $prev_year = $current_year;
        if ($prev_month < 1) {
            $prev_month = 12;
            $prev_year = $current_year - 1;
        }
        
        // Get previous month data
        $previous = $this->get_report_data($report_id, $prev_year, $prev_month);
        
        // Calculate year-to-date
        $ytd_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT SUM(value) as total FROM {$table_name} 
                WHERE report_id = %d AND `year` = %d AND `month` <= %d",
                $report_id,
                $current_year,
                $current_month
            ),
            ARRAY_A
        );
        
        $ytd_total = isset($ytd_data[0]['total']) ? floatval($ytd_data[0]['total']) : 0;
        
        return array(
            'current' => $current ? floatval($current['value']) : 0,
            'previous' => $previous ? floatval($previous['value']) : 0,
            'ytd' => $ytd_total,
            'change' => $current && $previous ? floatval($current['value']) - floatval($previous['value']) : 0,
            'change_percent' => $current && $previous && floatval($previous['value']) > 0 
                ? ((floatval($current['value']) - floatval($previous['value'])) / floatval($previous['value'])) * 100 
                : 0
        );
    }
    
    /**
     * Get all reports data for a specific period
     * 
     * @param int $year
     * @param int $month
     * @return array
     */
    public function get_all_reports_data($year, $month) {
        global $wpdb;
        
        $data_table = $wpdb->prefix . 'keap_report_data';
        $reports_table = $wpdb->prefix . 'keap_reports';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, d.value, d.fetched_at 
                FROM {$reports_table} r
                LEFT JOIN {$data_table} d ON r.id = d.report_id AND d.`year` = %d AND d.`month` = %d
                WHERE r.is_active = 1
                ORDER BY r.name ASC",
                $year,
                $month
            ),
            ARRAY_A
        );
        
        return $results ? $results : array();
    }
    
    /**
     * Get historical data for a report
     * 
     * @param int $report_id
     * @param int $limit Number of months to retrieve
     * @return array
     */
    public function get_report_history($report_id, $limit = 12) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_report_data';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} 
                WHERE report_id = %d 
                ORDER BY `year` DESC, `month` DESC 
                LIMIT %d",
                $report_id,
                $limit
            ),
            ARRAY_A
        );
        
        return $results ? $results : array();
    }
    
    /**
     * Add a log entry
     * 
     * @param string $message
     * @param string $level Log level (info, warning, error, debug)
     * @param array $context Additional context data
     * @return bool
     */
    public function add_log($message, $level = 'info', $context = array()) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'keap_reports_logs';
        
        // Check if table exists, create if not
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$logs_table'") == $logs_table;
        if (!$table_exists) {
            self::create_tables();
            // Verify it was created
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$logs_table'") == $logs_table;
            if (!$table_exists) {
                // If table creation failed, fall back to error_log
                error_log('[Keap Reports ' . strtoupper($level) . '] ' . $message);
                return false;
            }
        }
        
        $data = array(
            'log_level' => sanitize_text_field($level),
            'message' => sanitize_text_field($message),
            'created_at' => current_time('mysql')
        );
        
        if (!empty($context)) {
            $data['context'] = json_encode($context);
        }
        
        $result = $wpdb->insert($logs_table, $data);
        
        // Also log to error_log if debug is enabled (for backward compatibility)
        $debug_enabled = get_option('keap_reports_debug_enabled', false);
        if ($debug_enabled) {
            error_log('[Keap Reports ' . strtoupper($level) . '] ' . $message);
        }
        
        return $result !== false;
    }
    
    /**
     * Get logs
     * 
     * @param int $limit Number of logs to retrieve
     * @param string $level Filter by log level
     * @return array
     */
    public function get_logs($limit = 100, $level = null) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'keap_reports_logs';
        
        $where = '';
        if ($level) {
            $where = $wpdb->prepare(' WHERE log_level = %s', $level);
        }
        
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$logs_table} {$where} ORDER BY created_at DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
        
        return $logs ? $logs : array();
    }
    
    /**
     * Clear old logs
     * 
     * @param int $days_to_keep Number of days to keep logs
     * @return int Number of logs deleted
     */
    public function clear_old_logs($days_to_keep = 30) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'keap_reports_logs';
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days_to_keep} days"));
        
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$logs_table} WHERE created_at < %s",
                $cutoff_date
            )
        );
        
        return $deleted;
    }
    
    /**
     * Get log count
     * 
     * @param string $level Filter by log level
     * @return int
     */
    public function get_log_count($level = null) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'keap_reports_logs';
        
        if ($level) {
            return (int)$wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$logs_table} WHERE log_level = %s",
                    $level
                )
            );
        }
        
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$logs_table}");
    }
}

