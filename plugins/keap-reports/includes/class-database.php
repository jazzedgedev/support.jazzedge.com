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
            report_uuid varchar(255) NULL,
            report_type varchar(50) NOT NULL DEFAULT 'custom',
            filter_product_id varchar(255) NULL,
            report_year int(4) NULL,
            report_month tinyint(2) NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_report_id (report_id),
            KEY is_active (is_active),
            KEY report_date (report_year, report_month)
        ) $charset_collate;";
        
        // Table for monthly aggregated report data
        $data_table = $wpdb->prefix . 'keap_report_data';
        $data_sql = "CREATE TABLE IF NOT EXISTS {$data_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            report_id bigint(20) NOT NULL,
            `year` int(4) NOT NULL,
            `month` tinyint(2) NOT NULL,
            num_orders int(11) NOT NULL DEFAULT 0,
            total_amt_sold decimal(15,2) NOT NULL DEFAULT 0.00,
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
        
        // Table for individual subscription records (to track which contacts subscribed/cancelled)
        $details_table = $wpdb->prefix . 'keap_reports_daily_subscription_details';
        $details_sql = "CREATE TABLE IF NOT EXISTS {$details_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            subscription_id varchar(50) NOT NULL,
            contact_id varchar(50) NOT NULL,
            contact_name varchar(255) NULL,
            contact_email varchar(255) NULL,
            product_id varchar(50) NOT NULL,
            `year` int(4) NOT NULL,
            `month` tinyint(2) NOT NULL,
            `day` tinyint(2) NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_subscription_date (subscription_id, `year`, `month`, `day`),
            KEY contact_id (contact_id),
            KEY product_id (product_id),
            KEY date_index (`year`, `month`, `day`),
            KEY status (status)
        ) $charset_collate;";
        
        // Table for starter signups (free and paid)
        $starter_table = $wpdb->prefix . 'keap_starter_signups';
        $starter_sql = "CREATE TABLE IF NOT EXISTS {$starter_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            signup_type varchar(50) NOT NULL,
            year int(4) NOT NULL,
            month tinyint(2) NOT NULL,
            signup_count int(11) NOT NULL DEFAULT 0,
            revenue decimal(10,2) NOT NULL DEFAULT 0.00,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_signup (signup_type, year, month)
        ) $charset_collate;";
        
        // Use dbDelta for reports table (no reserved words)
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($reports_sql);
        dbDelta($products_sql);
        dbDelta($starter_sql);
        
        // Add revenue column to starter signups if missing (existing installations)
        $starter_cols = $wpdb->get_results("SHOW COLUMNS FROM {$starter_table} LIKE 'revenue'");
        if (empty($starter_cols)) {
            $wpdb->query("ALTER TABLE {$starter_table} ADD COLUMN revenue decimal(10,2) NOT NULL DEFAULT 0.00 AFTER signup_count");
        }
        
        // Add filter_product_id column if it doesn't exist (for existing installations)
        $reports_table = $wpdb->prefix . 'keap_reports';
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$reports_table} LIKE 'filter_product_id'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE {$reports_table} ADD COLUMN filter_product_id varchar(255) NULL AFTER report_type");
        }
        
        // Add report_year and report_month columns if they don't exist (for existing installations)
        $year_column = $wpdb->get_results("SHOW COLUMNS FROM {$reports_table} LIKE 'report_year'");
        if (empty($year_column)) {
            $wpdb->query("ALTER TABLE {$reports_table} ADD COLUMN report_year int(4) NULL AFTER filter_product_id");
        }
        $month_column = $wpdb->get_results("SHOW COLUMNS FROM {$reports_table} LIKE 'report_month'");
        if (empty($month_column)) {
            $wpdb->query("ALTER TABLE {$reports_table} ADD COLUMN report_month tinyint(2) NULL AFTER report_year");
        }
        
        // Add index for sorting if it doesn't exist
        $date_index = $wpdb->get_results("SHOW INDEX FROM {$reports_table} WHERE Key_name = 'report_date'");
        if (empty($date_index)) {
            $wpdb->query("ALTER TABLE {$reports_table} ADD INDEX report_date (report_year, report_month)");
        }
        
        // Make report_uuid nullable if it's currently NOT NULL (migration)
        $uuid_column = $wpdb->get_results("SHOW COLUMNS FROM {$reports_table} WHERE Field = 'report_uuid'");
        if (!empty($uuid_column) && $uuid_column[0]->Null === 'NO') {
            $wpdb->query("ALTER TABLE {$reports_table} MODIFY COLUMN report_uuid varchar(255) NULL");
        }
        
        // Update unique constraint if old one exists (migration from unique_report to unique_report_id)
        $indexes = $wpdb->get_results("SHOW INDEX FROM {$reports_table} WHERE Key_name = 'unique_report'");
        if (!empty($indexes)) {
            // Drop old unique constraint
            $wpdb->query("ALTER TABLE {$reports_table} DROP INDEX unique_report");
            // Add new unique constraint on report_id only
            $wpdb->query("ALTER TABLE {$reports_table} ADD UNIQUE KEY unique_report_id (report_id)");
        }
        
        // For data table with reserved words, use direct SQL execution
        // dbDelta has issues with backticks in index definitions
        $data_exists = $wpdb->get_var("SHOW TABLES LIKE '$data_table'") == $data_table;
        
        if (!$data_exists) {
            // Execute directly - dbDelta doesn't handle reserved words well in indexes
            $result = $wpdb->query($data_sql);
            if ($result === false) {
                error_log('Keap Reports: Failed to create data table. Error: ' . $wpdb->last_error);
                // Try without the problematic index, but WITH num_orders and total_amt_sold
                $data_sql_simple = "CREATE TABLE IF NOT EXISTS {$data_table} (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    report_id bigint(20) NOT NULL,
                    `year` int(4) NOT NULL,
                    `month` tinyint(2) NOT NULL,
                    num_orders int(11) NOT NULL DEFAULT 0,
                    total_amt_sold decimal(15,2) NOT NULL DEFAULT 0.00,
                    value decimal(15,2) NOT NULL DEFAULT 0.00,
                    metadata text NULL,
                    fetched_at datetime NOT NULL,
                    created_at datetime NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY unique_report_month (report_id, `year`, `month`),
                    KEY report_id (report_id)
                ) $charset_collate;";
                $result2 = $wpdb->query($data_sql_simple);
                if ($result2 === false) {
                    error_log('Keap Reports: Failed to create data table (simple version). Error: ' . $wpdb->last_error);
                } else {
                    // Add the year_month index separately if table was created
                    $data_exists = $wpdb->get_var("SHOW TABLES LIKE '$data_table'") == $data_table;
                    if ($data_exists) {
                        $index_result = $wpdb->query("ALTER TABLE {$data_table} ADD INDEX report_year_month (`year`, `month`)");
                        if ($index_result === false) {
                            error_log('Keap Reports: Failed to add report_year_month index. Error: ' . $wpdb->last_error);
                        }
                    }
                }
            }
        }
        
        // Add num_orders and total_amt_sold columns to data table if they don't exist (migration)
        $data_table = $wpdb->prefix . 'keap_report_data';
        $data_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$data_table'") == $data_table;
        if ($data_table_exists) {
            // Get all columns to check
            $columns = $wpdb->get_results("SHOW COLUMNS FROM {$data_table}", ARRAY_A);
            $column_names = array_column($columns, 'Field');
            
            if (!in_array('num_orders', $column_names)) {
                $result = $wpdb->query("ALTER TABLE {$data_table} ADD COLUMN num_orders int(11) NOT NULL DEFAULT 0 AFTER `month`");
                if ($result === false) {
                    error_log('Keap Reports: Failed to add num_orders column. Error: ' . $wpdb->last_error);
                }
            }
            
            if (!in_array('total_amt_sold', $column_names)) {
                $result = $wpdb->query("ALTER TABLE {$data_table} ADD COLUMN total_amt_sold decimal(15,2) NOT NULL DEFAULT 0.00 AFTER num_orders");
                if ($result === false) {
                    error_log('Keap Reports: Failed to add total_amt_sold column. Error: ' . $wpdb->last_error);
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
        
        // For details table with reserved words, use direct SQL execution
        $details_exists = $wpdb->get_var("SHOW TABLES LIKE '$details_table'") == $details_table;
        if (!$details_exists) {
            $result = $wpdb->query($details_sql);
            if ($result === false) {
                error_log('Keap Reports: Failed to create subscription details table. Error: ' . $wpdb->last_error);
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
        
        // Ensure columns exist before querying
        $this->maybe_add_filter_product_id_column();
        
        // Ensure report_year and report_month columns exist
        $table_name = $wpdb->prefix . 'keap_reports';
        
        // Check if table exists first
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            error_log('Keap Reports: Table does not exist: ' . $table_name);
            return array();
        }
        
        $year_column = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'report_year'");
        $month_column = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'report_month'");
        
        $has_year_month = !empty($year_column) && !empty($month_column);
        
        $where = $active_only ? 'WHERE is_active = 1' : '';
        
        // Sort by report_year, report_month (descending - most recent first), then by name
        // Reports without year/month go to the end
        if ($has_year_month) {
            $sql = "SELECT * FROM {$table_name} {$where} 
                ORDER BY 
                    CASE WHEN report_year IS NULL THEN 1 ELSE 0 END,
                    report_year DESC, 
                    report_month DESC, 
                    name ASC";
            $results = $wpdb->get_results($sql, ARRAY_A);
            
            // If query failed, log error and fall back to simple sort
            if ($results === false) {
                error_log('Keap Reports: get_reports() query failed: ' . $wpdb->last_error);
                $results = $wpdb->get_results(
                    "SELECT * FROM {$table_name} {$where} ORDER BY name ASC",
                    ARRAY_A
                );
            }
        } else {
            // Fallback to simple name sort if columns don't exist yet
            $results = $wpdb->get_results(
                "SELECT * FROM {$table_name} {$where} ORDER BY name ASC",
                ARRAY_A
            );
        }
        
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
        
        // Ensure fields exist (for backward compatibility)
        if ($result) {
            if (!isset($result['filter_product_id'])) {
                $result['filter_product_id'] = null;
            }
            if (!isset($result['report_year'])) {
                $result['report_year'] = null;
            }
            if (!isset($result['report_month'])) {
                $result['report_month'] = null;
            }
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
        
        // Ensure columns exist before saving
        $this->maybe_add_filter_product_id_column();
        
        // Ensure report_year and report_month columns exist
        $year_column = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'report_year'");
        if (empty($year_column)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN report_year int(4) NULL AFTER filter_product_id");
        }
        $month_column = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'report_month'");
        if (empty($month_column)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN report_month tinyint(2) NULL AFTER report_year");
        }
        
        // Add show_on_dashboard column if it doesn't exist
        $show_dashboard_column = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'show_on_dashboard'");
        if (empty($show_dashboard_column)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN show_on_dashboard tinyint(1) NOT NULL DEFAULT 0 AFTER is_active");
        }
        
        // Add index for sorting if it doesn't exist
        $date_index = $wpdb->get_results("SHOW INDEX FROM {$table_name} WHERE Key_name = 'report_date'");
        if (empty($date_index)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD INDEX report_date (report_year, report_month)");
        }
        
        // Ensure report_uuid column is nullable (migration fix)
        $uuid_column = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} WHERE Field = 'report_uuid'");
        if (!empty($uuid_column) && $uuid_column[0]->Null === 'NO') {
            $wpdb->query("ALTER TABLE {$table_name} MODIFY COLUMN report_uuid varchar(255) NULL");
        }
        
        $defaults = array(
            'name' => '',
            'report_id' => 0,
            'report_uuid' => null,
            'report_type' => 'custom',
            'filter_product_id' => '',
            'report_year' => null,
            'report_month' => null,
            'is_active' => 1,
            'show_on_dashboard' => 0
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Sanitize data
        $save_data = array(
            'name' => sanitize_text_field($data['name']),
            'report_id' => absint($data['report_id']),
            'report_uuid' => !empty($data['report_uuid']) ? sanitize_text_field($data['report_uuid']) : null,
            'report_type' => sanitize_text_field($data['report_type']),
            'report_year' => !empty($data['report_year']) ? absint($data['report_year']) : null,
            'report_month' => !empty($data['report_month']) ? absint($data['report_month']) : null,
            'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'show_on_dashboard' => isset($data['show_on_dashboard']) ? (int) $data['show_on_dashboard'] : 0
        );
        
        // Handle filter_product_id - can be comma-separated list
        if (!empty($data['filter_product_id'])) {
            $product_ids = array_map('trim', explode(',', $data['filter_product_id']));
            $product_ids = array_filter($product_ids, 'is_numeric');
            $save_data['filter_product_id'] = implode(',', $product_ids);
        } else {
            $save_data['filter_product_id'] = null;
        }
        
        // Auto-generate name from month/year if not provided
        if (empty($save_data['name']) && !empty($save_data['report_year']) && !empty($save_data['report_month'])) {
            // For paid_starter reports, use a fixed name
            if ($save_data['report_type'] === 'paid_starter') {
                $save_data['name'] = 'Paid Starter Signups';
            } else {
                $month_name = date('F', mktime(0, 0, 0, $save_data['report_month'], 1));
                $save_data['name'] = $month_name . ' ' . $save_data['report_year'] . ' Sales';
            }
        }
        
        // Validate required fields
        if (empty($save_data['name']) || empty($save_data['report_id'])) {
            return false;
        }
        
        if (isset($data['id']) && !empty($data['id'])) {
            // Update existing report
            $id = absint($data['id']);
            $save_data['updated_at'] = current_time('mysql');
            
            // Handle NULL values properly - $wpdb->update doesn't handle NULL well
            // Use raw SQL to properly update with NULL values
            $result = $wpdb->query($wpdb->prepare(
                "UPDATE {$table_name} 
                SET name = %s, report_id = %d, report_uuid = %s, report_type = %s, filter_product_id = %s, 
                    report_year = %s, report_month = %s, is_active = %d, show_on_dashboard = %d, updated_at = %s 
                WHERE id = %d",
                $save_data['name'],
                $save_data['report_id'],
                $save_data['report_uuid'] === null ? null : $save_data['report_uuid'],
                $save_data['report_type'],
                $save_data['filter_product_id'],
                $save_data['report_year'] === null ? null : $save_data['report_year'],
                $save_data['report_month'] === null ? null : $save_data['report_month'],
                $save_data['is_active'],
                $save_data['show_on_dashboard'],
                $save_data['updated_at'],
                $id
            ));
            return $result !== false ? $id : false;
        } else {
            // Insert new report
            $save_data['created_at'] = current_time('mysql');
            $save_data['updated_at'] = current_time('mysql');
            
            // Handle NULL values properly - $wpdb->insert doesn't handle NULL well
            // Use raw SQL to properly insert with NULL values
            $result = $wpdb->query($wpdb->prepare(
                "INSERT INTO {$table_name} (name, report_id, report_uuid, report_type, filter_product_id, report_year, report_month, is_active, show_on_dashboard, created_at, updated_at) 
                VALUES (%s, %d, %s, %s, %s, %s, %s, %d, %d, %s, %s)",
                $save_data['name'],
                $save_data['report_id'],
                $save_data['report_uuid'] === null ? null : $save_data['report_uuid'],
                $save_data['report_type'],
                $save_data['filter_product_id'],
                $save_data['report_year'] === null ? null : $save_data['report_year'],
                $save_data['report_month'] === null ? null : $save_data['report_month'],
                $save_data['is_active'],
                $save_data['show_on_dashboard'],
                $save_data['created_at'],
                $save_data['updated_at']
            ));
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
    public function save_report_data($report_id, $year, $month, $value, $metadata = array(), $num_orders = 0, $total_amt_sold = 0.00) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_report_data';
        
        // Verify table structure - check if num_orders and total_amt_sold columns exist
        $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}", ARRAY_A);
        $column_names = array_column($columns, 'Field');
        
        if (!in_array('num_orders', $column_names) || !in_array('total_amt_sold', $column_names)) {
            error_log('Keap Reports: Missing columns in keap_report_data table. Columns found: ' . implode(', ', $column_names));
            error_log('Keap Reports: Attempting to add missing columns...');
            
            // Try to add missing columns
            if (!in_array('num_orders', $column_names)) {
                $result = $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN num_orders int(11) NOT NULL DEFAULT 0 AFTER `month`");
                if ($result === false) {
                    error_log('Keap Reports: Failed to add num_orders column. Error: ' . $wpdb->last_error);
                    return false;
                }
            }
            
            if (!in_array('total_amt_sold', $column_names)) {
                $result = $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN total_amt_sold decimal(15,2) NOT NULL DEFAULT 0.00 AFTER num_orders");
                if ($result === false) {
                    error_log('Keap Reports: Failed to add total_amt_sold column. Error: ' . $wpdb->last_error);
                    return false;
                }
            }
            
            error_log('Keap Reports: Successfully added missing columns. Retrying save...');
        }
        
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
                SET report_id = %d, `year` = %d, `month` = %d, num_orders = %d, total_amt_sold = %f, value = %f, fetched_at = %s, metadata = %s 
                WHERE report_id = %d AND `year` = %d AND `month` = %d",
                absint($report_id),
                absint($year),
                absint($month),
                absint($num_orders),
                floatval($total_amt_sold),
                floatval($value),
                $fetched_at,
                $metadata_json,
                $report_id,
                $year,
                $month
            ));
            
            if ($result === false) {
                error_log('Keap Reports: UPDATE failed. Error: ' . $wpdb->last_error . ' | Query: ' . $wpdb->last_query);
                return false;
            }
        } else {
            // Insert new - use raw SQL with backticks for reserved words
            $result = $wpdb->query($wpdb->prepare(
                "INSERT INTO {$table_name} (report_id, `year`, `month`, num_orders, total_amt_sold, value, metadata, fetched_at, created_at) 
                VALUES (%d, %d, %d, %d, %f, %f, %s, %s, %s)",
                absint($report_id),
                absint($year),
                absint($month),
                absint($num_orders),
                floatval($total_amt_sold),
                floatval($value),
                $metadata_json,
                $fetched_at,
                $created_at
            ));
            
            if ($result === false) {
                error_log('Keap Reports: INSERT failed. Error: ' . $wpdb->last_error . ' | Query: ' . $wpdb->last_query);
                // Check if columns exist
                $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
                error_log('Keap Reports: Table columns: ' . print_r($columns, true));
                return false;
            }
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
                "SELECT SUM(total_amt_sold) as total_revenue, SUM(num_orders) as total_orders FROM {$table_name} 
                WHERE report_id = %d AND `year` = %d AND `month` <= %d",
                $report_id,
                $current_year,
                $current_month
            ),
            ARRAY_A
        );
        
        $ytd_revenue = isset($ytd_data[0]['total_revenue']) ? floatval($ytd_data[0]['total_revenue']) : 0;
        $ytd_orders = isset($ytd_data[0]['total_orders']) ? intval($ytd_data[0]['total_orders']) : 0;
        
        $current_revenue = $current ? floatval($current['total_amt_sold']) : 0;
        $current_orders = $current ? intval($current['num_orders']) : 0;
        $previous_revenue = $previous ? floatval($previous['total_amt_sold']) : 0;
        $previous_orders = $previous ? intval($previous['num_orders']) : 0;
        
        return array(
            'current' => $current ? floatval($current['value']) : 0,
            'previous' => $previous ? floatval($previous['value']) : 0,
            'ytd' => $ytd_revenue,
            'change' => $current && $previous ? floatval($current['value']) - floatval($previous['value']) : 0,
            'change_percent' => $current && $previous && floatval($previous['value']) > 0 
                ? ((floatval($current['value']) - floatval($previous['value'])) / floatval($previous['value'])) * 100 
                : 0,
            'current_revenue' => $current_revenue,
            'previous_revenue' => $previous_revenue,
            'revenue_change' => $current_revenue - $previous_revenue,
            'revenue_change_percent' => $previous_revenue > 0 ? (($current_revenue - $previous_revenue) / $previous_revenue) * 100 : 0,
            'current_orders' => $current_orders,
            'previous_orders' => $previous_orders,
            'orders_change' => $current_orders - $previous_orders,
            'orders_change_percent' => $previous_orders > 0 ? (($current_orders - $previous_orders) / $previous_orders) * 100 : 0,
            'ytd_orders' => $ytd_orders
        );
    }
    
    /**
     * Get FluentCart completed orders count and revenue for a given month.
     * Uses wp_fct_orders; only status = 'completed'. total_amount stored in cents.
     *
     * @param int $year
     * @param int $month
     * @return array { 'revenue' => float, 'orders' => int }
     */
    public function get_fluentcart_month_totals($year, $month) {
        global $wpdb;
        $table = $wpdb->prefix . 'fct_orders';
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) !== $table) {
            return array('revenue' => 0.0, 'orders' => 0);
        }
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT COUNT(*) AS orders, COALESCE(SUM(total_amount), 0) AS total FROM {$table} 
                WHERE status = %s AND YEAR(completed_at) = %d AND MONTH(completed_at) = %d",
                'completed',
                $year,
                $month
            ),
            ARRAY_A
        );
        if (!$row) {
            return array('revenue' => 0.0, 'orders' => 0);
        }
        $orders = isset($row['orders']) ? intval($row['orders']) : 0;
        $total_cents = isset($row['total']) ? floatval($row['total']) : 0;
        return array(
            'revenue' => $total_cents / 100.0,
            'orders'  => $orders
        );
    }
    
    /**
     * Get FluentCart YTD revenue and orders (Jan 1 through end of given month).
     *
     * @param int $year Year
     * @param int $through_month Month (1-12) — through this month inclusive
     * @return array { 'revenue' => float, 'orders' => int }
     */
    public function get_fluentcart_ytd($year, $through_month) {
        global $wpdb;
        $table = $wpdb->prefix . 'fct_orders';
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) !== $table) {
            return array('revenue' => 0.0, 'orders' => 0);
        }
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT COUNT(*) AS orders, COALESCE(SUM(total_amount), 0) AS total FROM {$table} 
                WHERE status = %s AND YEAR(completed_at) = %d AND MONTH(completed_at) <= %d",
                'completed',
                $year,
                $through_month
            ),
            ARRAY_A
        );
        if (!$row) {
            return array('revenue' => 0.0, 'orders' => 0);
        }
        $orders = isset($row['orders']) ? intval($row['orders']) : 0;
        $total_cents = isset($row['total']) ? floatval($row['total']) : 0;
        return array(
            'revenue' => $total_cents / 100.0,
            'orders'  => $orders
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
                "SELECT r.*, d.value, d.num_orders, d.total_amt_sold, d.fetched_at, d.`year` as data_year, d.`month` as data_month
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
     * Get all reports with their most recent data (regardless of month)
     * 
     * @return array
     */
    public function get_all_reports_latest_data() {
        global $wpdb;
        
        $data_table = $wpdb->prefix . 'keap_report_data';
        $reports_table = $wpdb->prefix . 'keap_reports';
        
        // Get all active reports first
        $reports = $wpdb->get_results(
            "SELECT * FROM {$reports_table} WHERE is_active = 1 ORDER BY name ASC",
            ARRAY_A
        );
        
        if (empty($reports)) {
            return array();
        }
        
        // For each report, get the most recent data
        $results = array();
        foreach ($reports as $report) {
            $latest_data = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$data_table} 
                    WHERE report_id = %d 
                    ORDER BY `year` DESC, `month` DESC, fetched_at DESC 
                    LIMIT 1",
                    $report['id']
                ),
                ARRAY_A
            );
            
            if ($latest_data) {
                $report['value'] = $latest_data['value'];
                $report['num_orders'] = $latest_data['num_orders'];
                $report['total_amt_sold'] = $latest_data['total_amt_sold'];
                $report['fetched_at'] = $latest_data['fetched_at'];
                $report['data_year'] = $latest_data['year'];
                $report['data_month'] = $latest_data['month'];
            } else {
                $report['value'] = null;
                $report['num_orders'] = null;
                $report['total_amt_sold'] = null;
                $report['fetched_at'] = null;
                $report['data_year'] = null;
                $report['data_month'] = null;
            }
            
            $results[] = $report;
        }
        
        return $results;
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
                "SELECT *, num_orders, total_amt_sold FROM {$table_name} 
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
     * Get the most recent fetch timestamp for a report
     * 
     * @param int $report_id
     * @return string|null Fetched at timestamp or null if never fetched
     */
    public function get_last_fetch_time($report_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_report_data';
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT fetched_at FROM {$table_name} 
            WHERE report_id = %d 
            ORDER BY fetched_at DESC 
            LIMIT 1",
            $report_id
        ));
        
        return $result ? $result : null;
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
        
        // Check logging level - skip debug logs in light mode
        $logging_level = get_option('keap_reports_logging_level', 'light');
        
        // In light mode, skip debug level logs (but always log errors, warnings, info, and cron)
        if ($logging_level === 'light' && $level === 'debug') {
            // Still log to error_log if debug is enabled (for backward compatibility)
            $debug_enabled = get_option('keap_reports_debug_enabled', false);
            if ($debug_enabled) {
                error_log('[Keap Reports DEBUG] ' . $message);
            }
            return true; // Return true to indicate "logged" but we're skipping database storage
        }
        
        // Also skip verbose info logs in light mode (unless they're essential like CRON:)
        if ($logging_level === 'light' && $level === 'info') {
            // Always log CRON executions, but skip other verbose info logs
            if (strpos($message, 'CRON:') !== 0 && 
                strpos($message, '[API]') !== 0 &&
                strpos($message, 'Successfully') === false &&
                strpos($message, 'Failed') === false) {
                // Skip verbose info logs in light mode
                $debug_enabled = get_option('keap_reports_debug_enabled', false);
                if ($debug_enabled) {
                    error_log('[Keap Reports INFO] ' . $message);
                }
                return true;
            }
        }
        
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
     * Save individual subscription record
     * 
     * @param string $subscription_id
     * @param string $contact_id
     * @param string $contact_name
     * @param string $contact_email
     * @param string $product_id
     * @param int $year
     * @param int $month
     * @param int $day
     * @param string $status
     * @return bool
     */
    public function save_subscription_detail($subscription_id, $contact_id, $contact_name, $contact_email, $product_id, $year, $month, $day, $status = 'active') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscription_details';
        
        // Ensure table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            self::create_tables();
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                return false;
            }
        }
        
        $data = array(
            'subscription_id' => sanitize_text_field($subscription_id),
            'contact_id' => sanitize_text_field($contact_id),
            'contact_name' => sanitize_text_field($contact_name),
            'contact_email' => sanitize_email($contact_email),
            'product_id' => sanitize_text_field($product_id),
            '`year`' => absint($year),
            '`month`' => absint($month),
            '`day`' => absint($day),
            'status' => sanitize_text_field($status),
            'created_at' => current_time('mysql')
        );
        
        // Check if record already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE subscription_id = %s AND `year` = %d AND `month` = %d AND `day` = %d",
            $subscription_id, $year, $month, $day
        ));
        
        if ($existing) {
            // Update existing
            $result = $wpdb->query($wpdb->prepare(
                "UPDATE {$table_name} SET contact_id = %s, contact_name = %s, contact_email = %s, product_id = %s, status = %s WHERE subscription_id = %s AND `year` = %d AND `month` = %d AND `day` = %d",
                $data['contact_id'], $data['contact_name'], $data['contact_email'], $data['product_id'], $data['status'], $subscription_id, $year, $month, $day
            ));
        } else {
            // Insert new
            $result = $wpdb->query($wpdb->prepare(
                "INSERT INTO {$table_name} (subscription_id, contact_id, contact_name, contact_email, product_id, `year`, `month`, `day`, status, created_at) VALUES (%s, %s, %s, %s, %s, %d, %d, %d, %s, %s)",
                $data['subscription_id'], $data['contact_id'], $data['contact_name'], $data['contact_email'], $data['product_id'], $data['`year`'], $data['`month`'], $data['`day`'], $data['status'], $data['created_at']
            ));
        }
        
        return $result !== false;
    }
    
    /**
     * Get subscription details for a specific date
     * 
     * @param string $product_id
     * @param int $year
     * @param int $month
     * @param int $day
     * @return array
     */
    public function get_subscription_details($product_id, $year, $month, $day) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscription_details';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE product_id = %s AND `year` = %d AND `month` = %d AND `day` = %d AND status = 'active'",
            $product_id, $year, $month, $day
        ), ARRAY_A);
        
        return $results ? $results : array();
    }
    
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
     * Get total active subscriptions across all products for the latest date
     * 
     * @return int Total active subscriptions
     */
    public function get_total_active_subscriptions() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        
        // First, get the latest date
        $latest_date = $wpdb->get_row(
            "SELECT `year`, `month`, `day` FROM {$table_name} ORDER BY `year` DESC, `month` DESC, `day` DESC LIMIT 1",
            ARRAY_A
        );
        
        if (!$latest_date) {
            return 0;
        }
        
        // Sum all active_count for that date across all products
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(active_count) FROM {$table_name} WHERE `year` = %d AND `month` = %d AND `day` = %d",
            intval($latest_date['year']),
            intval($latest_date['month']),
            intval($latest_date['day'])
        ));
        
        return intval($total ? $total : 0);
    }
    
    /**
     * Get total active subscriptions for a specific date
     * 
     * @param int $year
     * @param int $month
     * @param int $day
     * @return int Total active subscriptions
     */
    public function get_total_active_subscriptions_for_date($year, $month, $day) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(active_count) FROM {$table_name} WHERE `year` = %d AND `month` = %d AND `day` = %d",
            $year,
            $month,
            $day
        ));
        
        return intval($total ? $total : 0);
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
     * Get total subscriptions count (sum across all products or for a specific product)
     * Returns the latest available count
     * 
     * @param string|null $product_id Optional product ID filter
     * @return int
     */
    public function get_total_subscriptions_count($product_id = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        
        // Ensure table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            return 0;
        }
        
        // Get the most recent date
        $latest_date = $wpdb->get_row(
            "SELECT `year`, `month`, `day` FROM {$table_name} ORDER BY `year` DESC, `month` DESC, `day` DESC LIMIT 1",
            ARRAY_A
        );
        
        if (!$latest_date) {
            return 0;
        }
        
        // Build where clause
        $where = array();
        $where_values = array();
        
        $where[] = '`year` = %d AND `month` = %d AND `day` = %d';
        $where_values[] = intval($latest_date['year']);
        $where_values[] = intval($latest_date['month']);
        $where_values[] = intval($latest_date['day']);
        
        if ($product_id) {
            $where[] = 'product_id = %s';
            $where_values[] = $product_id;
        }
        
        $where_clause = 'WHERE ' . implode(' AND ', $where);
        
        $sql = "SELECT SUM(active_count) as total FROM {$table_name} {$where_clause}";
        $result = $wpdb->get_var($wpdb->prepare($sql, $where_values));
        
        return intval($result);
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
    
    /**
     * Check for duplicate subscription entries for a specific date
     * 
     * @param int $year
     * @param int $month
     * @param int $day
     * @return array
     */
    public function check_duplicate_subscriptions($year, $month, $day) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            return array(
                'has_duplicates' => false,
                'unique_count' => 0,
                'duplicate_count' => 0
            );
        }
        
        // Get all entries for this date
        $entries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE `year` = %d AND `month` = %d AND `day` = %d ORDER BY fetched_at DESC",
                $year,
                $month,
                $day
            ),
            ARRAY_A
        );
        
        if (empty($entries)) {
            return array(
                'has_duplicates' => false,
                'unique_count' => 0,
                'duplicate_count' => 0
            );
        }
        
        // Count unique products
        $unique_products = array();
        $total_entries = count($entries);
        
        foreach ($entries as $entry) {
            $product_id = $entry['product_id'];
            if (!isset($unique_products[$product_id])) {
                $unique_products[$product_id] = 0;
            }
            $unique_products[$product_id]++;
        }
        
        $unique_count = count($unique_products);
        $duplicate_count = $total_entries - $unique_count;
        
        return array(
            'has_duplicates' => $duplicate_count > 0,
            'unique_count' => $unique_count,
            'duplicate_count' => $duplicate_count,
            'total_entries' => $total_entries,
            'details' => $unique_products
        );
    }
    
    /**
     * Clear duplicate subscription entries for a specific date
     * Keeps only the most recent entry for each product
     * 
     * @param int $year
     * @param int $month
     * @param int $day
     * @return array
     */
    public function clear_duplicate_subscriptions($year, $month, $day) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_reports_daily_subscriptions';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            return array(
                'success' => false,
                'message' => 'Table does not exist',
                'deleted_count' => 0
            );
        }
        
        // Get all entries for this date, ordered by fetched_at DESC (most recent first)
        $entries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, product_id, fetched_at FROM {$table_name} WHERE `year` = %d AND `month` = %d AND `day` = %d ORDER BY fetched_at DESC",
                $year,
                $month,
                $day
            ),
            ARRAY_A
        );
        
        if (empty($entries)) {
            return array(
                'success' => true,
                'message' => 'No entries found for this date',
                'deleted_count' => 0
            );
        }
        
        // Track which products we've kept (keep the first/most recent entry for each product)
        $kept_products = array();
        $ids_to_delete = array();
        
        foreach ($entries as $entry) {
            $product_id = $entry['product_id'];
            if (!isset($kept_products[$product_id])) {
                // Keep this entry (first one we see for this product)
                $kept_products[$product_id] = $entry['id'];
            } else {
                // This is a duplicate, mark for deletion
                $ids_to_delete[] = $entry['id'];
            }
        }
        
        if (empty($ids_to_delete)) {
            return array(
                'success' => true,
                'message' => 'No duplicates found',
                'deleted_count' => 0
            );
        }
        
        // Delete duplicate entries
        $placeholders = implode(',', array_fill(0, count($ids_to_delete), '%d'));
        $sql = "DELETE FROM {$table_name} WHERE id IN ($placeholders)";
        $deleted = $wpdb->query($wpdb->prepare($sql, $ids_to_delete));
        
        return array(
            'success' => true,
            'message' => 'Duplicates cleared successfully',
            'deleted_count' => $deleted,
            'kept_count' => count($kept_products)
        );
    }
    
    /**
     * Get free trial signups from Fluent Forms
     * 
     * @param int $form_id Form ID (default: 48)
     * @param string $period Period to filter: 'day', 'week', 'month', 'all'
     * @param int|null $year Year (for month/week filtering)
     * @param int|null $month Month (for month filtering)
     * @param int|null $day Day (for day filtering)
     * @return array Array with count and details
     */
    public function get_free_trial_signups($form_id = 48, $period = 'all', $year = null, $month = null, $day = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fluentform_submissions';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            return array(
                'count' => 0,
                'signups' => array(),
                'error' => 'Fluent Forms table not found'
            );
        }
        
        $where = $wpdb->prepare('form_id = %d', $form_id);
        
        // Add date filtering based on period
        if ($period === 'day' && $year && $month && $day) {
            $start_date = sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day);
            $end_date = sprintf('%04d-%02d-%02d 23:59:59', $year, $month, $day);
            $where .= $wpdb->prepare(' AND created_at >= %s AND created_at <= %s', $start_date, $end_date);
        } elseif ($period === 'week' && $year) {
            // Get start of week (Monday)
            $date = new DateTime();
            $date->setISODate($year, isset($_GET['week']) ? intval($_GET['week']) : date('W'));
            $start_date = $date->format('Y-m-d 00:00:00');
            $date->modify('+6 days');
            $end_date = $date->format('Y-m-d 23:59:59');
            $where .= $wpdb->prepare(' AND created_at >= %s AND created_at <= %s', $start_date, $end_date);
        } elseif ($period === 'month' && $year && $month) {
            $start_date = sprintf('%04d-%02d-01 00:00:00', $year, $month);
            $end_date = sprintf('%04d-%02d-%d 23:59:59', $year, $month, date('t', mktime(0, 0, 0, $month, 1, $year)));
            $where .= $wpdb->prepare(' AND created_at >= %s AND created_at <= %s', $start_date, $end_date);
        }
        
        $signups = $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE {$where} ORDER BY created_at DESC",
            ARRAY_A
        );
        
        return array(
            'count' => count($signups),
            'signups' => $signups ? $signups : array()
        );
    }
    
    /**
     * Get free trial signups aggregated by day for current month
     * 
     * @param int $form_id Form ID (default: 48)
     * @return array Array of day => count
     */
    public function get_free_trial_signups_by_day($form_id = 48) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fluentform_submissions';
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            return array();
        }
        
        $current_year = intval(date('Y'));
        $current_month = intval(date('n'));
        $start_date = sprintf('%04d-%02d-01 00:00:00', $current_year, $current_month);
        $end_date = sprintf('%04d-%02d-%d 23:59:59', $current_year, $current_month, date('t'));
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(created_at) as signup_date, COUNT(*) as count 
                 FROM {$table_name} 
                 WHERE form_id = %d AND created_at >= %s AND created_at <= %s 
                 GROUP BY DATE(created_at) 
                 ORDER BY signup_date ASC",
                $form_id,
                $start_date,
                $end_date
            ),
            ARRAY_A
        );
        
        $by_day = array();
        foreach ($results as $result) {
            $day = intval(date('j', strtotime($result['signup_date'])));
            $by_day[$day] = intval($result['count']);
        }
        
        return $by_day;
    }
    
    /**
     * Get free trial signups for current month vs last month
     * 
     * @param int $form_id Form ID (default: 48)
     * @return array Array with current, previous, and change
     */
    public function get_free_trial_comparison($form_id = 48) {
        $current_year = intval(date('Y'));
        $current_month = intval(date('n'));
        
        $last_month = $current_month - 1;
        $last_month_year = $current_year;
        if ($last_month < 1) {
            $last_month = 12;
            $last_month_year = $current_year - 1;
        }
        
        $current = $this->get_free_trial_signups($form_id, 'month', $current_year, $current_month);
        $previous = $this->get_free_trial_signups($form_id, 'month', $last_month_year, $last_month);
        
        $current_count = $current['count'];
        $previous_count = $previous['count'];
        $change = $current_count - $previous_count;
        $change_percent = $previous_count > 0 ? ($change / $previous_count) * 100 : 0;
        
        return array(
            'current' => $current_count,
            'previous' => $previous_count,
            'change' => $change,
            'change_percent' => $change_percent
        );
    }
    
    /**
     * Get starter signups (paid) from stored data
     * 
     * @param string $type Type: 'paid_starter'
     * @param int $year Year
     * @param int $month Month
     * @return array Array with count
     */
    public function get_starter_signups($type, $year, $month) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_starter_signups';
        
        // Check if table exists, if not return empty
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            return array('count' => 0, 'revenue' => 0.0);
        }
        
        // Check if revenue column exists (added in a later update)
        $has_revenue = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'revenue'");
        if (!empty($has_revenue)) {
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT signup_count, COALESCE(revenue, 0) AS revenue FROM {$table_name} 
                WHERE signup_type = %s AND year = %d AND month = %d",
                $type,
                $year,
                $month
            ), ARRAY_A);
        } else {
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT signup_count FROM {$table_name} 
                WHERE signup_type = %s AND year = %d AND month = %d",
                $type,
                $year,
                $month
            ), ARRAY_A);
            if ($row && is_array($row)) {
                $row['revenue'] = 0;
            }
        }
        
        if (!$row) {
            return array('count' => 0, 'revenue' => 0.0);
        }
        
        $count = intval($row['signup_count']);
        $revenue = isset($row['revenue']) ? floatval($row['revenue']) : 0.0;
        // Paid starter is always $7 per signup; use stored revenue if present, else count × 7
        if ($revenue <= 0 && $count > 0) {
            $revenue = $count * 7.0;
        }
        
        return array(
            'count' => $count,
            'revenue' => $revenue
        );
    }
    
    /**
     * Save starter signups data
     *
     * @param string $type Type: 'paid_starter'
     * @param int $year Year
     * @param int $month Month
     * @param int $count Signup count
     * @param float $revenue Revenue for the month (default 0)
     * @return bool Success
     */
    public function save_starter_signups($type, $year, $month, $count, $revenue = 0.0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_starter_signups';
        
        // Create table if it doesn't exist
        $this->create_starter_signups_table();
        
        // Check if record exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} 
            WHERE signup_type = %s AND year = %d AND month = %d",
            $type,
            $year,
            $month
        ));
        
        $revenue = floatval($revenue);
        
        if ($existing) {
            // Update
            return $wpdb->update(
                $table_name,
                array(
                    'signup_count' => $count,
                    'revenue' => $revenue,
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'signup_type' => $type,
                    'year' => $year,
                    'month' => $month
                ),
                array('%d', '%f', '%s'),
                array('%s', '%d', '%d')
            ) !== false;
        } else {
            // Insert
            return $wpdb->insert(
                $table_name,
                array(
                    'signup_type' => $type,
                    'year' => $year,
                    'month' => $month,
                    'signup_count' => $count,
                    'revenue' => $revenue,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%d', '%d', '%d', '%f', '%s', '%s')
            ) !== false;
        }
    }
    
    /**
     * Create starter signups table
     * Called from create_tables() and save_starter_signups()
     */
    public function create_starter_signups_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'keap_starter_signups';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            signup_type varchar(50) NOT NULL,
            year int(4) NOT NULL,
            month tinyint(2) NOT NULL,
            signup_count int(11) NOT NULL DEFAULT 0,
            revenue decimal(10,2) NOT NULL DEFAULT 0.00,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_signup (signup_type, year, month)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        // Ensure revenue column exists (migration for existing installs)
        $cols = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'revenue'");
        if (empty($cols)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN revenue decimal(10,2) NOT NULL DEFAULT 0.00 AFTER signup_count");
        }
    }
}
