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
            filter_product_id varchar(255) NULL,
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
        
        // Table for product mappings
        $products_table = $wpdb->prefix . 'keap_reports_products';
        $products_sql = "CREATE TABLE IF NOT EXISTS {$products_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id varchar(50) NOT NULL,
            product_name varchar(255) NOT NULL,
            sku varchar(255) NULL,
            price decimal(10,2) NULL,
            status varchar(50) NULL,
            category varchar(255) NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_product_id (product_id),
            KEY status (status),
            KEY category (category)
        ) $charset_collate;";
        
        // Table for daily subscription snapshots
        $daily_table = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        $daily_sql = "CREATE TABLE IF NOT EXISTS {$daily_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id varchar(50) NOT NULL,
            `year` int(4) NOT NULL,
            `month` tinyint(2) NOT NULL,
            `day` tinyint(2) NOT NULL,
            active_count int(11) NOT NULL DEFAULT 0,
            fetched_at datetime NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_product_date (product_id, `year`, `month`, `day`),
            KEY product_id (product_id),
            KEY date_index (`year`, `month`, `day`),
            KEY fetched_at (fetched_at)
        ) $charset_collate;";
        
        // Use dbDelta for reports table (no reserved words)
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($reports_sql);
        dbDelta($products_sql);
        
        // Add filter_product_id column if it doesn't exist (for existing installations)
        $reports_table = $wpdb->prefix . 'keap_reports';
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$reports_table} LIKE 'filter_product_id'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE {$reports_table} ADD COLUMN filter_product_id varchar(255) NULL AFTER report_type");
        }
        
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
        
        // For daily table with reserved words, use direct SQL execution
        $daily_exists = $wpdb->get_var("SHOW TABLES LIKE '$daily_table'") == $daily_table;
        if (!$daily_exists) {
            $result = $wpdb->query($daily_sql);
            if ($result === false) {
                error_log('Keap Reports: Failed to create daily subscriptions table. Error: ' . $wpdb->last_error);
                // Try without the problematic index
                $daily_sql_simple = "CREATE TABLE IF NOT EXISTS {$daily_table} (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    product_id varchar(50) NOT NULL,
                    `year` int(4) NOT NULL,
                    `month` tinyint(2) NOT NULL,
                    `day` tinyint(2) NOT NULL,
                    active_count int(11) NOT NULL DEFAULT 0,
                    fetched_at datetime NOT NULL,
                    created_at datetime NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY unique_product_date (product_id, `year`, `month`, `day`),
                    KEY product_id (product_id)
                ) $charset_collate;";
                $wpdb->query($daily_sql_simple);
                // Add indexes separately if table was created without them
                $index_exists = $wpdb->get_results("SHOW INDEX FROM {$daily_table} WHERE Key_name = 'date_index'");
                if (empty($index_exists)) {
                    $wpdb->query("CREATE INDEX date_index ON {$daily_table} (`year`, `month`, `day`)");
                }
                $index_exists = $wpdb->get_results("SHOW INDEX FROM {$daily_table} WHERE Key_name = 'fetched_at'");
                if (empty($index_exists)) {
                    $wpdb->query("CREATE INDEX fetched_at ON {$daily_table} (fetched_at)");
                }
            }
        }
        
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
        
        // Ensure column exists before querying
        $this->maybe_add_filter_product_id_column();
        
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
        
        // Ensure column exists before querying
        $this->maybe_add_filter_product_id_column();
        
        $table_name = $wpdb->prefix . 'keap_reports';
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $report_id
            ),
            ARRAY_A
        );
        
        // Ensure filter_product_id exists (for backward compatibility)
        if ($result && !isset($result['filter_product_id'])) {
            $result['filter_product_id'] = null;
        }
        
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
        
        // Ensure column exists before saving
        $this->maybe_add_filter_product_id_column();
        
        $defaults = array(
            'name' => '',
            'report_id' => 0,
            'report_uuid' => '',
            'report_type' => 'custom',
            'filter_product_id' => '',
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
        
        // Handle filter_product_id - can be comma-separated list
        if (!empty($data['filter_product_id'])) {
            $product_ids = array_map('trim', explode(',', $data['filter_product_id']));
            $product_ids = array_filter($product_ids, 'is_numeric');
            $save_data['filter_product_id'] = implode(',', $product_ids);
        } else {
            $save_data['filter_product_id'] = null;
        }
        
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
     * Clear all logs
     * 
     * @return int Number of logs deleted
     */
    public function clear_all_logs() {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'keap_reports_logs';
        
        $deleted = $wpdb->query("DELETE FROM {$logs_table}");
        
        return $deleted;
    }
    
    /**
     * Export logs as JSON
     * 
     * @param int $limit Number of logs to export
     * @param string $level Filter by log level
     * @return string JSON encoded logs
     */
    public function export_logs_json($limit = 1000, $level = null) {
        $logs = $this->get_logs($limit, $level);
        
        // Get system information for debugging
        global $wpdb;
        
        $export = array(
            'export_date' => current_time('mysql'),
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'plugin_version' => defined('KEAP_REPORTS_VERSION') ? KEAP_REPORTS_VERSION : 'unknown',
            'timezone' => wp_timezone_string(),
            'gmt_offset' => get_option('gmt_offset'),
            'site_url' => get_site_url(),
            'mysql_version' => $wpdb->db_version(),
            'total_logs' => count($logs),
            'filter_level' => $level,
            'limit' => $limit,
            'system_info' => array(
                'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown',
                'php_memory_limit' => ini_get('memory_limit'),
                'wp_memory_limit' => defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : 'unknown',
                'max_execution_time' => ini_get('max_execution_time'),
            ),
            'plugin_settings' => array(
                'api_key_configured' => !empty(get_option('keap_reports_api_key', '')),
                'api_key_length' => strlen(get_option('keap_reports_api_key', '')),
                'schedule_frequency' => get_option('keap_reports_schedule_frequency', 'monthly'),
                'debug_enabled' => get_option('keap_reports_debug_enabled', false),
            ),
            'total_logs_in_db' => $this->get_log_count(),
            'error_count' => $this->get_log_count('error'),
            'warning_count' => $this->get_log_count('warning'),
            'info_count' => $this->get_log_count('info'),
            'debug_count' => $this->get_log_count('debug'),
            'logs' => $logs
        );
        
        return json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
    
    /**
     * Ensure filter_product_id column exists (for backward compatibility)
     */
    private function maybe_add_filter_product_id_column() {
        global $wpdb;
        
        $reports_table = $wpdb->prefix . 'keap_reports';
        
        // Check if table exists first
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$reports_table'") == $reports_table;
        if (!$table_exists) {
            return; // Table doesn't exist, will be created with column
        }
        
        // Check if column exists
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$reports_table} LIKE 'filter_product_id'");
        if (empty($column_exists)) {
            // Add the column
            $wpdb->query("ALTER TABLE {$reports_table} ADD COLUMN filter_product_id varchar(255) NULL AFTER report_type");
        }
    }
    
    // ============================================
    // Product Mapping Methods
    // ============================================
    
    /**
     * Get all products with latest subscription counts
     * 
     * @return array
     */
    public function get_products() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_products';
        $daily_table = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        
        $results = $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY product_name ASC",
            ARRAY_A
        );
        
        if (empty($results)) {
            return array();
        }
        
        // Get latest subscription count for each product
        foreach ($results as &$product) {
            $product_id = $product['product_id'];
            
            // Get the most recent daily subscription record for this product
            $latest = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT active_count, fetched_at FROM {$daily_table} WHERE product_id = %s ORDER BY `year` DESC, `month` DESC, `day` DESC LIMIT 1",
                    $product_id
                ),
                ARRAY_A
            );
            
            if ($latest) {
                $product['latest_active_count'] = intval($latest['active_count']);
                $product['latest_fetched_at'] = $latest['fetched_at'];
            } else {
                $product['latest_active_count'] = null;
                $product['latest_fetched_at'] = null;
            }
        }
        
        return $results;
    }
    
    /**
     * Get a single product by ID
     * 
     * @param string $product_id
     * @return array|null
     */
    public function get_product($product_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_products';
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE product_id = %s",
                $product_id
            ),
            ARRAY_A
        );
        
        return $result;
    }
    
    /**
     * Save or update a product
     * 
     * @param array $data Product data
     * @return int|false Product ID on success, false on failure
     */
    public function save_product($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_products';
        
        // Ensure table exists before proceeding
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            error_log('Keap Reports: Products table ' . $table_name . ' does not exist. Creating it now...');
            self::create_tables();
            // Verify it was created
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                error_log('Keap Reports: Failed to create products table ' . $table_name);
                return false;
            }
        }
        
        $save_data = array(
            'product_id' => sanitize_text_field($data['product_id']),
            'product_name' => sanitize_text_field($data['product_name']),
            'sku' => isset($data['sku']) ? sanitize_text_field($data['sku']) : null,
            'price' => isset($data['price']) ? floatval($data['price']) : null,
            'status' => isset($data['status']) ? sanitize_text_field($data['status']) : null,
            'category' => isset($data['category']) ? sanitize_text_field($data['category']) : null,
            'updated_at' => current_time('mysql')
        );
        
        $existing = $this->get_product($save_data['product_id']);
        
        if ($existing) {
            // Update
            $result = $wpdb->update(
                $table_name,
                $save_data,
                array('id' => $existing['id']),
                array('%s', '%s', '%s', '%f', '%s', '%s', '%s'),
                array('%d')
            );
            
            if ($result === false) {
                error_log('Keap Reports: Failed to update product. Error: ' . $wpdb->last_error);
            }
            
            return $result !== false ? $existing['id'] : false;
        } else {
            // Insert
            $save_data['created_at'] = current_time('mysql');
            $result = $wpdb->insert(
                $table_name,
                $save_data,
                array('%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s')
            );
            
            if ($result === false) {
                error_log('Keap Reports: Failed to insert product. Error: ' . $wpdb->last_error);
                error_log('Keap Reports: Product data: ' . print_r($save_data, true));
            }
            
            return $result !== false ? $wpdb->insert_id : false;
        }
    }
    
    /**
     * Delete a product
     * 
     * @param string $product_id
     * @return bool
     */
    public function delete_product($product_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_products';
        
        $result = $wpdb->delete(
            $table_name,
            array('product_id' => $product_id),
            array('%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Import products from CSV data
     * 
     * @param string $csv_content CSV file content
     * @return array Array with 'success', 'imported', 'updated', 'errors' keys
     */
    public function import_products_from_csv($csv_content) {
        global $wpdb;
        
        // Ensure products table exists
        $table_name = $wpdb->prefix . 'keap_reports_products';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            error_log('Keap Reports: Products table does not exist. Creating it now...');
            self::create_tables();
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                return array(
                    'success' => false,
                    'imported' => 0,
                    'updated' => 0,
                    'errors' => array('Failed to create products table. Please try again.')
                );
            }
        }
        
        $lines = explode("\n", $csv_content);
        $imported = 0;
        $updated = 0;
        $errors = array();
        
        // Skip header row
        $header = array_shift($lines);
        
        foreach ($lines as $line_num => $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            // Parse CSV line (handle quoted values)
            $data = str_getcsv($line);
            
            if (count($data) < 2) {
                $errors[] = "Line " . ($line_num + 2) . ": Invalid format";
                continue;
            }
            
            // Map CSV columns: Id, Product Name, SKU, Price, ProductStatus, Product Category
            $product_data = array(
                'product_id' => trim($data[0]),
                'product_name' => trim($data[1]),
                'sku' => isset($data[2]) ? trim($data[2]) : '',
                'price' => isset($data[3]) ? $this->parse_price($data[3]) : null,
                'status' => isset($data[4]) ? trim($data[4]) : '',
                'category' => isset($data[5]) ? trim($data[5]) : ''
            );
            
            if (empty($product_data['product_id']) || empty($product_data['product_name'])) {
                $errors[] = "Line " . ($line_num + 2) . ": Missing product ID or name";
                continue;
            }
            
            $existing = $this->get_product($product_data['product_id']);
            $result = $this->save_product($product_data);
            
            if ($result !== false) {
                if ($existing) {
                    $updated++;
                } else {
                    $imported++;
                }
            } else {
                $db_error = $wpdb->last_error ? $wpdb->last_error : 'Unknown database error';
                $errors[] = "Line " . ($line_num + 2) . ": Failed to save product " . $product_data['product_id'] . " - " . $db_error;
            }
        }
        
        return array(
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors
        );
    }
    
    /**
     * Parse price string to float
     * 
     * @param string $price_string
     * @return float|null
     */
    private function parse_price($price_string) {
        if (empty($price_string)) {
            return null;
        }
        
        // Remove currency symbols and commas
        $price = preg_replace('/[^0-9.]/', '', $price_string);
        
        return !empty($price) ? floatval($price) : null;
    }
    
    // ============================================
    // Daily Subscription Data Methods
    // ============================================
    
    /**
     * Save daily subscription snapshot
     * 
     * @param string $product_id
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $active_count
     * @return bool
     */
    public function save_daily_subscription($product_id, $year, $month, $day, $active_count) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        
        // Ensure table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            error_log('Keap Reports: Table ' . $table_name . ' does not exist. Creating it now...');
            self::create_tables();
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                error_log('Keap Reports: Failed to create table ' . $table_name);
                return false;
            }
        }
        
        $data = array(
            'product_id' => sanitize_text_field($product_id),
            '`year`' => absint($year),
            '`month`' => absint($month),
            '`day`' => absint($day),
            'active_count' => absint($active_count),
            'fetched_at' => current_time('mysql')
        );
        
        $existing = $this->get_daily_subscription($product_id, $year, $month, $day);
        
        if ($existing) {
            // Update existing using raw SQL to handle reserved words
            $sql = $wpdb->prepare(
                "UPDATE {$table_name} SET active_count = %d, fetched_at = %s WHERE product_id = %s AND `year` = %d AND `month` = %d AND `day` = %d",
                $data['active_count'],
                $data['fetched_at'],
                $data['product_id'],
                $data['`year`'],
                $data['`month`'],
                $data['`day`']
            );
            $result = $wpdb->query($sql);
        } else {
            // Insert new using raw SQL
            $data['created_at'] = current_time('mysql');
            $sql = $wpdb->prepare(
                "INSERT INTO {$table_name} (product_id, `year`, `month`, `day`, active_count, fetched_at, created_at) VALUES (%s, %d, %d, %d, %d, %s, %s)",
                $data['product_id'],
                $data['`year`'],
                $data['`month`'],
                $data['`day`'],
                $data['active_count'],
                $data['fetched_at'],
                $data['created_at']
            );
            $result = $wpdb->query($sql);
        }
        
        return $result !== false;
    }
    
    /**
     * Get the last successful fetch time for daily subscriptions
     * 
     * @return string|false Last fetch datetime or false if never fetched
     */
    public function get_last_daily_subscription_fetch_time() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        
        // Ensure table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            return false;
        }
        
        $result = $wpdb->get_var(
            "SELECT MAX(fetched_at) FROM {$table_name}"
        );
        
        return $result ? $result : false;
    }
    
    /**
     * Get daily subscription data
     * 
     * @param string $product_id Optional product ID filter
     * @param int $year Optional year filter
     * @param int $month Optional month filter
     * @param int $day Optional day filter
     * @return array
     */
    public function get_daily_subscription($product_id = null, $year = null, $month = null, $day = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        
        $where = array();
        $where_values = array();
        
        if ($product_id) {
            $where[] = 'product_id = %s';
            $where_values[] = $product_id;
        }
        if ($year) {
            $where[] = '`year` = %d';
            $where_values[] = $year;
        }
        if ($month) {
            $where[] = '`month` = %d';
            $where_values[] = $month;
        }
        if ($day) {
            $where[] = '`day` = %d';
            $where_values[] = $day;
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        if (!empty($where_values)) {
            $sql = "SELECT * FROM {$table_name} {$where_clause} ORDER BY `year` DESC, `month` DESC, `day` DESC LIMIT 1";
            $sql = $wpdb->prepare($sql, $where_values);
        } else {
            $sql = "SELECT * FROM {$table_name} ORDER BY `year` DESC, `month` DESC, `day` DESC LIMIT 1";
        }
        
        $result = $wpdb->get_row($sql, ARRAY_A);
        
        return $result;
    }
    
    /**
     * Get daily subscription data for a date range
     * 
     * @param string $product_id Optional product ID filter
     * @param string $start_date YYYY-MM-DD format
     * @param string $end_date YYYY-MM-DD format
     * @return array
     */
    public function get_daily_subscriptions_range($product_id = null, $start_date = null, $end_date = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        
        $where = array();
        $where_values = array();
        
        if ($product_id) {
            $where[] = 'product_id = %s';
            $where_values[] = $product_id;
        }
        
        if ($start_date) {
            $start_parts = explode('-', $start_date);
            if (count($start_parts) == 3) {
                $where[] = "(`year` > %d OR (`year` = %d AND `month` > %d) OR (`year` = %d AND `month` = %d AND `day` >= %d))";
                $where_values[] = intval($start_parts[0]);
                $where_values[] = intval($start_parts[0]);
                $where_values[] = intval($start_parts[1]);
                $where_values[] = intval($start_parts[0]);
                $where_values[] = intval($start_parts[1]);
                $where_values[] = intval($start_parts[2]);
            }
        }
        
        if ($end_date) {
            $end_parts = explode('-', $end_date);
            if (count($end_parts) == 3) {
                $where[] = "(`year` < %d OR (`year` = %d AND `month` < %d) OR (`year` = %d AND `month` = %d AND `day` <= %d))";
                $where_values[] = intval($end_parts[0]);
                $where_values[] = intval($end_parts[0]);
                $where_values[] = intval($end_parts[1]);
                $where_values[] = intval($end_parts[0]);
                $where_values[] = intval($end_parts[1]);
                $where_values[] = intval($end_parts[2]);
            }
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        if (!empty($where_values)) {
            $sql = "SELECT * FROM {$table_name} {$where_clause} ORDER BY `year` ASC, `month` ASC, `day` ASC";
            $sql = $wpdb->prepare($sql, $where_values);
        } else {
            $sql = "SELECT * FROM {$table_name} ORDER BY `year` ASC, `month` ASC, `day` ASC";
        }
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        return $results ? $results : array();
    }
    
    /**
     * Get aggregated subscription counts by period
     * 
     * @param string $product_id Optional product ID filter
     * @param string $period 'day', 'week', or 'month'
     * @param string $start_date YYYY-MM-DD format
     * @param string $end_date YYYY-MM-DD format
     * @return array
     */
    public function get_subscription_aggregates($product_id = null, $period = 'day', $start_date = null, $end_date = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        
        $where = array();
        $where_values = array();
        
        if ($product_id) {
            $where[] = 'product_id = %s';
            $where_values[] = $product_id;
        }
        
        if ($start_date) {
            $start_parts = explode('-', $start_date);
            if (count($start_parts) == 3) {
                $where[] = "(`year` > %d OR (`year` = %d AND `month` > %d) OR (`year` = %d AND `month` = %d AND `day` >= %d))";
                $where_values[] = intval($start_parts[0]);
                $where_values[] = intval($start_parts[0]);
                $where_values[] = intval($start_parts[1]);
                $where_values[] = intval($start_parts[0]);
                $where_values[] = intval($start_parts[1]);
                $where_values[] = intval($start_parts[2]);
            }
        }
        
        if ($end_date) {
            $end_parts = explode('-', $end_date);
            if (count($end_parts) == 3) {
                $where[] = "(`year` < %d OR (`year` = %d AND `month` < %d) OR (`year` = %d AND `month` = %d AND `day` <= %d))";
                $where_values[] = intval($end_parts[0]);
                $where_values[] = intval($end_parts[0]);
                $where_values[] = intval($end_parts[1]);
                $where_values[] = intval($end_parts[0]);
                $where_values[] = intval($end_parts[1]);
                $where_values[] = intval($end_parts[2]);
            }
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Group by period
        switch ($period) {
            case 'week':
                $group_by = "YEAR(fetched_at), WEEK(fetched_at), product_id";
                $select = "YEAR(fetched_at) as year, WEEK(fetched_at) as week, product_id, AVG(active_count) as avg_count, MAX(active_count) as max_count, MIN(active_count) as min_count";
                break;
            case 'month':
                $group_by = "`year`, `month`, product_id";
                $select = "`year`, `month`, product_id, AVG(active_count) as avg_count, MAX(active_count) as max_count, MIN(active_count) as min_count";
                break;
            case 'day':
            default:
                $group_by = "`year`, `month`, `day`, product_id";
                $select = "`year`, `month`, `day`, product_id, active_count as avg_count, active_count as max_count, active_count as min_count";
                break;
        }
        
        if (!empty($where_values)) {
            $sql = "SELECT {$select} FROM {$table_name} {$where_clause} GROUP BY {$group_by} ORDER BY `year` ASC, `month` ASC, `day` ASC";
            $sql = $wpdb->prepare($sql, $where_values);
        } else {
            $sql = "SELECT {$select} FROM {$table_name} {$where_clause} GROUP BY {$group_by} ORDER BY `year` ASC, `month` ASC, `day` ASC";
        }
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        return $results ? $results : array();
    }
}

