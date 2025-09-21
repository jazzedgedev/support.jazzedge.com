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
            
            // Verify tables exist
            $this->verify_tables_exist();
            
            return true;
            
        } catch (Exception $e) {
            error_log('JPH Database Error: ' . $e->getMessage());
            return false;
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
        
        $result = $this->wpdb->replace(
            $table_name,
            array_merge(array('user_id' => $user_id), $stats_data),
            array_fill(0, count($stats_data) + 1, '%s')
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
}
