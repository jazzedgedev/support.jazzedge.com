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
            'user_badges' => $wpdb->prefix . 'jph_user_badges'
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
            
            // Create each table
            foreach ($create_statements as $table_name => $sql) {
                $result = $this->wpdb->query($sql);
                
                if ($result === false) {
                    throw new Exception("Failed to create table {$table_name}: " . $this->wpdb->last_error);
                }
                
                error_log("JPH Database: Created table {$table_name}");
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
     * Run database migrations
     */
    public function run_migrations() {
        // Migration 1: Add earned_at column to user_badges table if it doesn't exist
        $this->migrate_add_earned_at_column();
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
            } else {
                error_log("JPH Migration: Successfully added earned_date column to user_badges table");
            }
        } else {
            error_log("JPH Migration: earned_date column already exists in user_badges table");
        }
    }
    
    /**
     * Verify all tables exist
     */
    private function verify_tables_exist() {
        foreach ($this->tables as $table_name) {
            $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
            
            if (!$table_exists) {
                throw new Exception("Table {$table_name} was not created successfully");
            }
        }
        
        error_log('JPH Database: All tables verified successfully');
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
    public function get_practice_sessions($user_id, $limit = 10) {
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
                LIMIT %d
            ", $user_id, $limit);
            
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
        
        // DEBUG: Log the query and results
        error_log("JPH DEBUG: get_badges query: " . $query);
        $results = $this->wpdb->get_results($query, ARRAY_A);
        error_log("JPH DEBUG: get_badges results: " . print_r($results, true));
        
        return $results;
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
        
        // Check if badge_key already exists
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE badge_key = %s",
            $badge_data['badge_key']
        ));
        
        if ($existing) {
            return new WP_Error('duplicate_badge_key', 'Badge key already exists');
        }
        
        $result = $this->wpdb->insert(
            $table_name,
            $badge_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d')
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
        
        $result = $this->wpdb->update(
            $table_name,
            $badge_data,
            array('id' => $badge_id),
            array_fill(0, count($badge_data), '%s'),
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
        
        // Check if earned_date column exists (the actual column name in the table)
        $column_exists = $this->wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'earned_date'");
        
        if (empty($column_exists)) {
            // Column doesn't exist, use simple query without ORDER BY
            $query = $this->wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d",
                $user_id
            );
        } else {
            // Column exists, use full query with ORDER BY
            $query = $this->wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY earned_date DESC",
                $user_id
            );
        }
        
        // DEBUG: Log the query and results
        error_log("JPH DEBUG: get_user_badges query: " . $query);
        $results = $this->wpdb->get_results($query, ARRAY_A);
        error_log("JPH DEBUG: get_user_badges results: " . print_r($results, true));
        
        return $results;
    }
    
    /**
     * Award badge to user
     */
    public function award_badge($user_id, $badge_key, $badge_name, $badge_description = '', $badge_icon = '') {
        $table_name = $this->tables['user_badges'];
        
        // Check if user already has this badge (using badge_id instead of badge_key)
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d AND badge_id = %s",
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
                'badge_id' => $badge_key, // Use badge_id instead of badge_key
                'earned_date' => current_time('mysql'), // Use earned_date instead of earned_at
                'is_displayed' => 1,
                'showcase_position' => 0
            ),
            array('%d', '%s', '%s', '%d', '%d')
        );
        
        return $result !== false;
    }
}
