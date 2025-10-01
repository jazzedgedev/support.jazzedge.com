<?php
/**
 * Database operations for JazzEdge Practice Hub
 * 
 * @package JazzEdge_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include database schema
require_once JPH_PLUGIN_PATH . 'includes/database-schema.php';

class JPH_Database {
    
    /**
     * WordPress database object
     */
    private $wpdb;
    
    /**
     * Table names
     */
    private $tables;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        // Define table names
        $this->tables = array(
            'practice_items' => $wpdb->prefix . 'jph_practice_items',
            'practice_sessions' => $wpdb->prefix . 'jph_practice_sessions',
            'user_stats' => $wpdb->prefix . 'jph_user_stats',
            'badges' => $wpdb->prefix . 'jph_badges',
            'user_badges' => $wpdb->prefix . 'jph_user_badges',
            'lesson_favorites' => $wpdb->prefix . 'jph_lesson_favorites',
            'gems_transactions' => $wpdb->prefix . 'jph_gems_transactions'
        );
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        try {
            // Load schema
            $schema = JPH_Database_Schema::get_schema();
            $create_statements = JPH_Database_Schema::get_create_statements();
            
            // Create each table using dbDelta for better compatibility
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            foreach ($create_statements as $table_name => $sql) {
                // Convert to dbDelta format
                $dbdelta_sql = $this->convert_to_dbdelta_format($sql);
                
                $result = dbDelta($dbdelta_sql);
                
                if (empty($result)) {
                    error_log("JPH Database: Table {$table_name} already exists or created successfully");
                } else {
                    error_log("JPH Database: Created/updated table {$table_name}");
                }
            }
            
            // Run database migrations
            $this->run_migrations();
            
            // Verify tables exist
            $this->verify_tables_exist();
            
            return true;
            
        } catch (Exception $e) {
            error_log('JPH Database Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convert SQL to dbDelta format
     */
    private function convert_to_dbdelta_format($sql) {
        // dbDelta expects specific formatting
        $sql = str_replace('CREATE TABLE IF NOT EXISTS', 'CREATE TABLE', $sql);
        $sql = str_replace('ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci', '', $sql);
        return $sql;
    }
    
    /**
     * Run database migrations
     */
    public function run_migrations() {
        // Migration 1: Add earned_at column to user_badges table if it doesn't exist
        $this->migrate_add_earned_at_column();
        
        // Migration 2: Add webhook_url column to badges table if it doesn't exist
        $this->migrate_add_webhook_url_column();
        
        // Migration 3: Add source column to gems_transactions table if it doesn't exist
        $this->migrate_add_source_column_to_gems_transactions();
    }
    
    /**
     * Migration: Add earned_date column to user_badges table (if needed)
     */
    private function migrate_add_earned_at_column() {
        $table_name = $this->tables['user_badges'];
        
        // Check if earned_date column exists (the actual column name)
        $column_exists = $this->wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'earned_date'");
        
        if (empty($column_exists)) {
            // Add the earned_date column
            $sql = "ALTER TABLE {$table_name} ADD COLUMN earned_date DATETIME DEFAULT CURRENT_TIMESTAMP";
            $result = $this->wpdb->query($sql);
            
            if ($result === false) {
                error_log("JPH Migration Error: Failed to add earned_date column: " . $this->wpdb->last_error);
            }
        }
    }
    
    /**
     * Migration: Add webhook_url column to badges table (if needed)
     */
    private function migrate_add_webhook_url_column() {
        $table_name = $this->tables['badges'];
        
        // Check if webhook_url column exists
        $column_exists = $this->wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'webhook_url'");
        
        if (empty($column_exists)) {
            // Add the webhook_url column
            $sql = "ALTER TABLE {$table_name} ADD COLUMN webhook_url VARCHAR(500) DEFAULT NULL";
            
            $result = $this->wpdb->query($sql);
            
            if ($result === false) {
                error_log("JPH Migration Error: Failed to add webhook_url column: " . $this->wpdb->last_error);
            }
        }
    }
    
    /**
     * Migration: Add source column to gems_transactions table (if needed)
     */
    private function migrate_add_source_column_to_gems_transactions() {
        $table_name = $this->tables['gems_transactions'];
        
        // Check if source column exists
        $column_exists = $this->wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'source'");
        
        if (empty($column_exists)) {
            // Add the source column
            $this->wpdb->query("ALTER TABLE {$table_name} ADD COLUMN source VARCHAR(100) NOT NULL DEFAULT '' AFTER amount");
            $this->wpdb->query("ALTER TABLE {$table_name} ADD INDEX source (source)");
            
            error_log('JPH Database: Migration completed - source column added to gems_transactions table');
        }
    }
    
    /**
     * Verify all tables exist
     */
    private function verify_tables_exist() {
        $missing_tables = array();
        
        foreach ($this->tables as $table_name) {
            $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
            
            if (!$table_exists) {
                $missing_tables[] = $table_name;
            }
        }
        
        if (!empty($missing_tables)) {
            error_log('JPH Database: Missing tables: ' . implode(', ', $missing_tables));
            throw new Exception('Missing required tables: ' . implode(', ', $missing_tables));
        }
        
        error_log('JPH Database: All required tables verified (' . count($this->tables) . ' tables)');
    }
    
    /**
     * Check if tables exist
     */
    public function tables_exist() {
        foreach ($this->tables as $table_name) {
            $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
            if (!$table_exists) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Get table names
     */
    public function get_table_names() {
        return $this->tables;
    }
    
    /**
     * Get database status
     */
    public function get_database_status() {
        $status = array(
            'tables' => array(),
            'total_tables' => 0,
            'missing_tables' => array(),
            'plugin_version' => get_option('jph_plugin_version', '0.0.0')
        );
        
        foreach ($this->tables as $table_key => $table_name) {
            $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
            $row_count = $table_exists ? $this->wpdb->get_var("SELECT COUNT(*) FROM {$table_name}") : 0;
            
            $status['tables'][$table_key] = array(
                'name' => $table_name,
                'exists' => (bool) $table_exists,
                'row_count' => (int) $row_count
            );
            
            if (!$table_exists) {
                $status['missing_tables'][] = $table_name;
            }
        }
        
        $status['total_tables'] = count($this->tables);
        
        return $status;
    }
    
    /**
     * Get user practice items
     */
    public function get_user_practice_items($user_id) {
        $table_name = $this->tables['practice_items'];
        
        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d AND is_active = 1 ORDER BY created_at ASC",
            $user_id
        ), ARRAY_A);
        
        return $results ?: array();
    }
    
    /**
     * Add practice item
     */
    public function add_practice_item($user_id, $name, $category = 'custom', $description = '') {
        $table_name = $this->tables['practice_items'];
        
        // Check if user already has this name (only active items)
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d AND name = %s AND is_active = 1",
            $user_id, $name
        ));
        
        // Debug: Log the query and result
        error_log("JPH Debug: Checking for duplicate name '{$name}' for user {$user_id}. Found: " . ($existing ? $existing : 'none'));
        
        if ($existing) {
            return new WP_Error('duplicate_name', 'You already have a practice item with this name');
        }
        
        // Check total item limit (max 3 active items total - neuroscience-based focus)
        $total_count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND is_active = 1",
            $user_id
        ));
        
        if ($total_count >= 3) {
            return new WP_Error('limit_exceeded', 'You can only have 3 active practice items at once. This neuroscience-based limit helps you focus and improve faster. Delete an item to add a new one.');
        }
        
        $result = $this->wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'is_active' => 1
            ),
            array('%d', '%s', '%s', '%s', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to add practice item: ' . $this->wpdb->last_error);
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Update practice item
     */
    public function update_practice_item($item_id, $name, $category = null, $description = null) {
        $table_name = $this->tables['practice_items'];
        
        // Get current item
        $item = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $item_id
        ), ARRAY_A);
        
        if (!$item) {
            return new WP_Error('item_not_found', 'Practice item not found');
        }
        
        // Prepare update data
        $update_data = array();
        $update_format = array();
        
        if ($name !== null) {
            $update_data['name'] = $name;
            $update_format[] = '%s';
        }
        
        if ($category !== null) {
            $update_data['category'] = $category;
            $update_format[] = '%s';
        }
        
        if ($description !== null) {
            $update_data['description'] = $description;
            $update_format[] = '%s';
        }
        
        if (empty($update_data)) {
            return new WP_Error('no_changes', 'No changes provided');
        }
        
        $result = $this->wpdb->update(
            $table_name,
            $update_data,
            array('id' => $item_id),
            $update_format,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update practice item: ' . $this->wpdb->last_error);
        }
        
        return true;
    }
    
    /**
     * Delete practice item
     */
    public function delete_practice_item($item_id) {
        $table_name = $this->tables['practice_items'];
        
        // Check if item exists
        $item = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $item_id
        ), ARRAY_A);
        
        if (!$item) {
            return new WP_Error('item_not_found', 'Practice item not found');
        }
        
        // Soft delete (set is_active = 0)
        $result = $this->wpdb->update(
            $table_name,
            array('is_active' => 0),
            array('id' => $item_id),
            array('%d'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('delete_failed', 'Failed to delete practice item: ' . $this->wpdb->last_error);
        }
        
        return true;
    }
    
    /**
     * Log practice session
     */
    public function log_practice_session($user_id, $practice_item_id, $duration_minutes, $sentiment_score, $improvement_detected = false, $notes = '') {
        $table_name = $this->tables['practice_sessions'];
        
        // Create session hash to prevent duplicates
        $session_hash = md5($user_id . $practice_item_id . $duration_minutes . $sentiment_score . current_time('Y-m-d H:i:s'));
        
        // Check for duplicate session (same hash)
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE session_hash = %s",
            $session_hash
        ));
        
        if ($existing) {
            return new WP_Error('duplicate_session', 'Duplicate practice session detected');
        }
        
        $result = $this->wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'practice_item_id' => $practice_item_id,
                'duration_minutes' => $duration_minutes,
                'sentiment_score' => $sentiment_score,
                'improvement_detected' => $improvement_detected ? 1 : 0,
                'notes' => $notes,
                'session_hash' => $session_hash
            ),
            array('%d', '%d', '%d', '%d', '%d', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to log practice session: ' . $this->wpdb->last_error);
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get user stats
     */
    public function get_user_stats($user_id) {
        $table_name = $this->tables['user_stats'];
        
        $stats = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d",
            $user_id
        ), ARRAY_A);
        
        if (!$stats) {
            // Create default stats if none exist
            $this->wpdb->insert(
                $table_name,
                array('user_id' => $user_id),
                array('%d')
            );
            
            $stats = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d",
                $user_id
            ), ARRAY_A);
        }
        
        return $stats ?: array(
            'total_xp' => 0,
            'current_level' => 1,
            'current_streak' => 0,
            'total_sessions' => 0,
            'total_minutes' => 0
        );
    }
    
    /**
     * Update user stats
     */
    public function update_user_stats($user_id, $stats_data) {
        $table_name = $this->tables['user_stats'];
        
        // Use UPDATE instead of REPLACE to avoid overwriting other fields
        $result = $this->wpdb->update(
            $table_name,
            $stats_data,
            array('user_id' => $user_id),
            array_fill(0, count($stats_data), '%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get practice sessions for a user
     */
    public function get_practice_sessions($user_id, $limit = 50, $offset = 0) {
        try {
            $sessions_table = $this->tables['practice_sessions'];
            $items_table = $this->tables['practice_items'];
            
            $query = $this->wpdb->prepare("
                SELECT 
                    s.*,
                    i.name as item_name,
                    i.category as item_category
                FROM {$sessions_table} s
                LEFT JOIN {$items_table} i ON s.practice_item_id = i.id
                WHERE s.user_id = %d
                ORDER BY s.created_at DESC
                LIMIT %d OFFSET %d
            ", $user_id, $limit, $offset);
            
            $results = $this->wpdb->get_results($query, ARRAY_A);
            
            if ($results === null) {
                return new WP_Error('db_error', 'Database error: ' . $this->wpdb->last_error);
            }
            
            return $results;
        } catch (Exception $e) {
            return new WP_Error('get_sessions_error', 'Error getting practice sessions: ' . $e->getMessage());
        }
    }
    
    /**
     * Get ALL practice sessions for a user (for badge checking)
     */
    public function get_all_practice_sessions($user_id) {
        try {
            $sessions_table = $this->tables['practice_sessions'];
            $items_table = $this->tables['practice_items'];
            
            $query = $this->wpdb->prepare("
                SELECT 
                    s.*,
                    i.name as item_name,
                    i.category as item_category
                FROM {$sessions_table} s
                LEFT JOIN {$items_table} i ON s.practice_item_id = i.id
                WHERE s.user_id = %d
                ORDER BY s.created_at DESC
            ", $user_id);
            
            $results = $this->wpdb->get_results($query, ARRAY_A);
            
            if ($results === null) {
                return new WP_Error('db_error', 'Database error: ' . $this->wpdb->last_error);
            }
            
            return $results;
        } catch (Exception $e) {
            return new WP_Error('get_all_sessions_error', 'Error getting all practice sessions: ' . $e->getMessage());
        }
    }
    
    /**
     * Update practice session with XP earned
     */
    public function update_practice_session_xp($session_id, $xp_earned) {
        try {
            $table_name = $this->tables['practice_sessions'];
            
            $result = $this->wpdb->update(
                $table_name,
                array('xp_earned' => $xp_earned),
                array('id' => $session_id),
                array('%d'),
                array('%d')
            );
            
            if ($result === false) {
                return new WP_Error('db_error', 'Database error: ' . $this->wpdb->last_error);
            }
            
            return true;
        } catch (Exception $e) {
            return new WP_Error('update_xp_error', 'Error updating XP: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a practice session
     */
    public function delete_practice_session($session_id) {
        try {
            $table_name = $this->tables['practice_sessions'];
            
            $result = $this->wpdb->delete(
                $table_name,
                array('id' => $session_id),
                array('%d')
            );
            
            if ($result === false) {
                return new WP_Error('db_error', 'Database error: ' . $this->wpdb->last_error);
            }
            
            if ($result === 0) {
                return new WP_Error('not_found', 'Practice session not found');
            }
            
            return true;
        } catch (Exception $e) {
            return new WP_Error('delete_session_error', 'Error deleting practice session: ' . $e->getMessage());
        }
    }
    
    /**
     * Get all badges
     */
    public function get_badges($active_only = true) {
        $table_name = $this->tables['badges'];
        
        $where_clause = $active_only ? 'WHERE is_active = 1' : '';
        
        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY category, name ASC";
        
        $results = $this->wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
    
    /**
     * Update badge display order
     */
    public function update_badge_display_order($badge_id, $display_order) {
        $table_name = $this->tables['badges'];
        
        $result = $this->wpdb->update(
            $table_name,
            array('display_order' => $display_order),
            array('id' => $badge_id),
            array('%d'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update badge display order: ' . $this->wpdb->last_error);
        }
        
        return true;
    }
    
    /**
     * Update multiple badge display orders
     */
    public function update_badge_display_orders($badge_orders) {
        $table_name = $this->tables['badges'];
        
        foreach ($badge_orders as $badge_id => $display_order) {
            $result = $this->wpdb->update(
                $table_name,
                array('display_order' => $display_order),
                array('id' => $badge_id),
                array('%d'),
                array('%d')
            );
            
            if ($result === false) {
                return new WP_Error('update_failed', 'Failed to update badge display order for badge ' . $badge_id . ': ' . $this->wpdb->last_error);
            }
        }
        
        return true;
    }
    
    /**
     * Get monthly shield purchases for a user
     */
    public function get_monthly_shield_purchases($user_id) {
        $table_name = $this->tables['gems_transactions'];
        
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} 
             WHERE user_id = %d 
             AND transaction_type = 'spent' 
             AND source = 'streak_shield_purchase' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
            $user_id
        ));
        
        return (int) $count;
    }
    
    /**
     * Record a gems transaction
     */
    public function record_gems_transaction($user_id, $transaction_type, $amount, $source, $description = '') {
        $table_name = $this->tables['gems_transactions'];
        
        // Get current user stats to calculate balance after
        $user_stats = $this->get_user_stats($user_id);
        $balance_after = $user_stats['gems_balance'] + $amount;
        
        $result = $this->wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'transaction_type' => $transaction_type,
                'amount' => $amount,
                'source' => $source,
                'description' => $description,
                'balance_after' => $balance_after
            ),
            array('%d', '%s', '%d', '%s', '%s', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to record gems transaction: ' . $this->wpdb->last_error);
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get gems transactions for a user
     */
    public function get_gems_transactions($user_id, $limit = 50) {
        $table_name = $this->tables['gems_transactions'];
        
        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
            $user_id, $limit
        ), ARRAY_A);
        
        return $results ?: array();
    }
    
    /**
     * Get badge by ID
     */
    public function get_badge($badge_id) {
        $table_name = $this->tables['badges'];
        
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $badge_id
        ), ARRAY_A);
    }
    
    /**
     * Get badge by key
     */
    public function get_badge_by_key($badge_key) {
        $table_name = $this->tables['badges'];
        
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE badge_key = %s",
            $badge_key
        ), ARRAY_A);
    }
    
    /**
     * Add new badge
     */
    public function add_badge($badge_data) {
        $table_name = $this->tables['badges'];
        
        // Check if badge name already exists (since there's no badge_key column)
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE name = %s",
            $badge_data['name']
        ));
        
        if ($existing) {
            return new WP_Error('duplicate_badge_name', 'Badge name already exists');
        }
        
        // Set default display_order if not provided
        if (!isset($badge_data['display_order'])) {
            // Get the highest display_order and add 1
            $max_order = $this->wpdb->get_var("SELECT MAX(display_order) FROM {$table_name}");
            $badge_data['display_order'] = ($max_order ? $max_order + 1 : 1);
        }
        
        // Create format array based on the data
        $formats = array();
        foreach ($badge_data as $key => $value) {
            if (in_array($key, array('xp_reward', 'gem_reward', 'criteria_value', 'is_active', 'display_order'))) {
                $formats[] = '%d'; // Integer
            } else {
                $formats[] = '%s'; // String
            }
        }
        
        $result = $this->wpdb->insert(
            $table_name,
            $badge_data,
            $formats
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to add badge: ' . $this->wpdb->last_error);
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Update badge
     */
    public function update_badge($badge_id, $badge_data) {
        $table_name = $this->tables['badges'];
        
        
        // Create format array based on the data
        $formats = array();
        foreach ($badge_data as $key => $value) {
            if (in_array($key, array('xp_reward', 'gem_reward', 'criteria_value', 'is_active'))) {
                $formats[] = '%d'; // Integer
            } else {
                $formats[] = '%s'; // String
            }
        }
        
        
        $result = $this->wpdb->update(
            $table_name,
            $badge_data,
            array('id' => $badge_id),
            $formats,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete badge (soft delete)
     */
    public function delete_badge($badge_id) {
        $table_name = $this->tables['badges'];
        
        $result = $this->wpdb->update(
            $table_name,
            array('is_active' => 0),
            array('id' => $badge_id),
            array('%d'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get user's earned badges
     */
    public function get_user_badges($user_id) {
        $table_name = $this->tables['user_badges'];
        
        // Check if earned_at column exists (the actual column name in the table)
        $column_exists = $this->wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'earned_at'");
        
        if (empty($column_exists)) {
            // Column doesn't exist, use simple query without ORDER BY
            $query = $this->wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d",
                $user_id
            );
        } else {
            // Column exists, use full query with ORDER BY
            $query = $this->wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY earned_at DESC",
                $user_id
            );
        }
        
        $results = $this->wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
    
    /**
     * Award badge to user
     */
    public function award_badge($user_id, $badge_key, $badge_name, $badge_description = '', $badge_icon = '') {
        $table_name = $this->tables['user_badges'];
        
        // Check if user already has this badge
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d AND badge_key = %s",
            $user_id, $badge_key
        ));
        
        if ($existing) {
            return false; // Already has this badge
        }
        
        // Insert the badge with the correct table structure
        $result = $this->wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'badge_key' => $badge_key,
                'badge_name' => $badge_name,
                'badge_description' => $badge_description,
                'badge_icon' => $badge_icon,
                'earned_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result !== false) {
            error_log("JPH Badge Award: Successfully awarded badge {$badge_key} to user {$user_id}");
            // Trigger webhook if badge has one
            $this->trigger_badge_webhook($user_id, $badge_key, $badge_name, $badge_description, $badge_icon);
        } else {
            error_log("JPH Badge Award: Failed to award badge {$badge_key} to user {$user_id}");
        }
        
        return $result !== false;
    }
    
    /**
     * Trigger webhook when badge is awarded
     */
    private function trigger_badge_webhook($user_id, $badge_id, $badge_name, $badge_description, $badge_icon) {
        $this->log_webhook("Attempting to trigger webhook for badge {$badge_id} to user {$user_id}");
        
        // Get badge details including webhook URL
        $badge = $this->get_badge($badge_id);
        
        if (!$badge) {
            $this->log_webhook("Badge {$badge_id} not found", 'error');
            return;
        }
        
        if (empty($badge['webhook_url'])) {
            $this->log_webhook("No webhook URL configured for badge {$badge_id}", 'info');
            return; // No webhook URL configured
        }
        
        $this->log_webhook("Found webhook URL for badge {$badge_id}: {$badge['webhook_url']}");
        
        // Get user details
        $user = get_userdata($user_id);
        if (!$user) {
            $this->log_webhook("User {$user_id} not found", 'error');
            return;
        }
        
        $this->log_webhook("User found: {$user->user_email}");
        
        // Prepare webhook payload
        $payload = array(
            'event' => 'badge_earned',
            'timestamp' => current_time('mysql'),
            'user' => array(
                'id' => $user_id,
                'email' => $user->user_email,
                'display_name' => $user->display_name,
                'username' => $user->user_login
            ),
            'badge' => array(
                'id' => $badge_id,
                'name' => $badge_name,
                'description' => $badge_description,
                'icon' => $badge_icon,
                'category' => $badge['category'],
                'rarity_level' => $badge['rarity_level'],
                'xp_reward' => $badge['xp_reward'],
                'gem_reward' => $badge['gem_reward']
            )
        );
        
        $this->log_webhook("Created payload for badge {$badge_id}: " . json_encode($payload));
        
        // Send webhook request
        $result = $this->send_webhook_request($badge['webhook_url'], $payload);
        
        if ($result) {
            $this->log_webhook("Successfully sent webhook for badge {$badge_id}", 'success');
        } else {
            $this->log_webhook("Failed to send webhook for badge {$badge_id}", 'error');
        }
    }
    
    /**
     * Log webhook activity to dedicated log file
     */
    private function log_webhook($message, $type = 'info') {
        $log_file = WP_CONTENT_DIR . '/jph-webhook.log';
        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] JPH Webhook: {$message}\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Send webhook request
     */
    public function send_webhook_request($url, $payload) {
        // First, try a simple connectivity test
        $this->log_webhook("Testing connectivity to {$url}");
        $test_response = wp_remote_get($url, array('timeout' => 10));
        if (is_wp_error($test_response)) {
            $this->log_webhook("Cannot reach {$url} - " . $test_response->get_error_message(), 'error');
            return false;
        } else {
            $test_code = wp_remote_retrieve_response_code($test_response);
            $this->log_webhook("Connectivity test to {$url} returned code: " . ($test_code ?: 'empty'));
        }
        
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'JazzEdge-Practice-Hub/1.0'
            ),
            'body' => json_encode($payload),
            'timeout' => 30,
            'blocking' => true // Try blocking first to get better error info
        );
        
        $this->log_webhook("Attempting to send webhook to {$url}");
        $this->log_webhook("Payload: " . json_encode($payload));
        
        $response = wp_remote_request($url, $args);
        
        // Log the webhook attempt
        if (is_wp_error($response)) {
            $this->log_webhook("Failed to send webhook to {$url} - " . $response->get_error_message(), 'error');
            return false;
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $response_headers = wp_remote_retrieve_headers($response);
            
            $this->log_webhook("Response code: " . ($response_code ?: 'empty'));
            $this->log_webhook("Response body: " . substr($response_body, 0, 500));
            $this->log_webhook("Response headers: " . print_r($response_headers, true));
            
            if ($response_code >= 200 && $response_code < 300) {
                $this->log_webhook("Sent webhook to {$url} - Response: {$response_code}", 'success');
                return true;
            } else {
                $this->log_webhook("Webhook to {$url} returned status {$response_code}", 'error');
                // For webhook.site and other testing services, we'll consider it successful
                // if we got any response code (even if not 2xx)
                if ($response_code && strpos($url, 'webhook.site') !== false) {
                    $this->log_webhook("Considering webhook successful for webhook.site despite non-2xx code", 'success');
                    return true;
                }
                return false;
            }
        }
    }
    
    /**
     * Get user's lesson favorites
     */
    public function get_lesson_favorites($user_id = null) {
        $table_name = $this->tables['lesson_favorites'];
        
        if ($user_id) {
            // Get favorites for specific user
            $results = $this->wpdb->get_results($this->wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
                $user_id
            ), ARRAY_A);
        } else {
            // Get all favorites (for admin)
            $results = $this->wpdb->get_results("
                SELECT lf.*, u.display_name as user_display_name 
                FROM {$table_name} lf 
                LEFT JOIN {$this->wpdb->users} u ON lf.user_id = u.ID 
                ORDER BY lf.created_at DESC
            ", ARRAY_A);
        }
        
        return $results ?: array();
    }
    
    /**
     * Add lesson favorite
     */
    public function add_lesson_favorite($favorite_data) {
        $table_name = $this->tables['lesson_favorites'];
        
        // Check if user already has this title (prevent duplicates)
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d AND title = %s",
            $favorite_data['user_id'], $favorite_data['title']
        ));
        
        if ($existing) {
            return new WP_Error('duplicate_title', 'You already have a favorite with this title');
        }
        
        $result = $this->wpdb->insert(
            $table_name,
            array(
                'user_id' => $favorite_data['user_id'],
                'title' => $favorite_data['title'],
                'url' => $favorite_data['url'],
                'category' => $favorite_data['category'],
                'description' => $favorite_data['description']
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to add lesson favorite: ' . $this->wpdb->last_error);
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Update lesson favorite
     */
    public function update_lesson_favorite($favorite_id, $favorite_data) {
        $table_name = $this->tables['lesson_favorites'];
        
        // Check if favorite exists
        $favorite = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $favorite_id
        ), ARRAY_A);
        
        if (!$favorite) {
            return new WP_Error('favorite_not_found', 'Lesson favorite not found');
        }
        
        // Prepare update data
        $update_data = array();
        $update_format = array();
        
        if (isset($favorite_data['title'])) {
            $update_data['title'] = $favorite_data['title'];
            $update_format[] = '%s';
        }
        
        if (isset($favorite_data['url'])) {
            $update_data['url'] = $favorite_data['url'];
            $update_format[] = '%s';
        }
        
        if (isset($favorite_data['category'])) {
            $update_data['category'] = $favorite_data['category'];
            $update_format[] = '%s';
        }
        
        if (isset($favorite_data['description'])) {
            $update_data['description'] = $favorite_data['description'];
            $update_format[] = '%s';
        }
        
        if (empty($update_data)) {
            return new WP_Error('no_changes', 'No changes provided');
        }
        
        $result = $this->wpdb->update(
            $table_name,
            $update_data,
            array('id' => $favorite_id),
            $update_format,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update lesson favorite: ' . $this->wpdb->last_error);
        }
        
        return true;
    }
    
    /**
     * Delete lesson favorite
     */
    public function delete_lesson_favorite($favorite_id) {
        $table_name = $this->tables['lesson_favorites'];
        
        // Check if favorite exists
        $favorite = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $favorite_id
        ), ARRAY_A);
        
        if (!$favorite) {
            return new WP_Error('favorite_not_found', 'Lesson favorite not found');
        }
        
        $result = $this->wpdb->delete(
            $table_name,
            array('id' => $favorite_id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('delete_failed', 'Failed to delete lesson favorite: ' . $this->wpdb->last_error);
        }
        
        return true;
    }
    
    
    
}

