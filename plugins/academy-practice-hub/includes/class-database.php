<?php
/**
 * Database operations for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Database {
    
    /**
     * Get user stats
     */
    public function get_user_stats($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d",
            $user_id
        ));
        
        if (!$stats) {
            return array(
                'total_xp' => 0,
                'current_level' => 1,
                'total_sessions' => 0,
                'total_minutes' => 0,
                'current_streak' => 0,
                'longest_streak' => 0,
                'badges_earned' => 0,
                'gems_balance' => 0,
                'hearts_count' => 5,
                'streak_shield_count' => 0,
                'last_practice_date' => null,
                'display_name' => null,
                'show_on_leaderboard' => 1
            );
        }
        
        return array(
            'total_xp' => (int) $stats->total_xp,
            'current_level' => (int) $stats->current_level,
            'total_sessions' => (int) $stats->total_sessions,
            'total_minutes' => (int) $stats->total_minutes,
            'current_streak' => (int) $stats->current_streak,
            'longest_streak' => (int) $stats->longest_streak,
            'badges_earned' => (int) $stats->badges_earned,
            'gems_balance' => (int) $stats->gems_balance,
            'hearts_count' => (int) $stats->hearts_count,
            'streak_shield_count' => (int) $stats->streak_shield_count,
            'last_practice_date' => $stats->last_practice_date,
            'display_name' => $stats->display_name,
            'show_on_leaderboard' => (int) $stats->show_on_leaderboard
        );
    }
    
    /**
     * Update user stats
     */
    public function update_user_stats($user_id, $stats_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        
        // Check if stats exist
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d",
            $user_id
        ));
        
        if (!$existing) {
            // Create new stats record
            $result = $wpdb->insert(
                $table_name,
                array_merge(
                    array('user_id' => $user_id),
                    $stats_data
                )
            );
        } else {
            // Update existing stats
            $result = $wpdb->update(
                $table_name,
                $stats_data,
                array('user_id' => $user_id)
            );
        }
        
        return $result !== false;
    }
    
    /**
     * Log practice session
     */
    public function log_practice_session($user_id, $practice_item_id, $duration_minutes, $sentiment_score, $improvement_detected, $notes = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_practice_sessions';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'practice_item_id' => $practice_item_id,
                'duration_minutes' => $duration_minutes,
                'sentiment_score' => $sentiment_score,
                'improvement_detected' => $improvement_detected ? 1 : 0,
                'notes' => $notes,
                'session_hash' => md5(uniqid(rand(), true)),
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : new WP_Error('insert_failed', 'Failed to log practice session');
    }
    
    /**
     * Get practice sessions
     */
    public function get_practice_sessions($user_id, $limit = 10, $offset = 0) {
        global $wpdb;
        
        $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
        $items_table = $wpdb->prefix . 'jph_practice_items';
        
        $query = $wpdb->prepare(
            "SELECT s.*, i.name as item_name 
             FROM {$sessions_table} s 
             LEFT JOIN {$items_table} i ON s.practice_item_id = i.id 
             WHERE s.user_id = %d 
             ORDER BY s.created_at DESC 
             LIMIT %d OFFSET %d",
            $user_id, $limit, $offset
        );
        
        error_log('Practice Sessions Query: ' . $query);
        
        $sessions = $wpdb->get_results($query, ARRAY_A);
        
        error_log('Practice Sessions Result: ' . print_r($sessions, true));
        
        return $sessions ?: array();
    }
    
    /**
     * Delete practice session
     */
    public function delete_practice_session($session_id, $user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_practice_sessions';
        
        $result = $wpdb->delete(
            $table_name,
            array(
                'id' => $session_id,
                'user_id' => $user_id
            ),
            array('%d', '%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Update practice session XP
     */
    public function update_practice_session_xp($session_id, $xp_earned) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_practice_sessions';
        
        $result = $wpdb->update(
            $table_name,
            array('xp_earned' => $xp_earned),
            array('id' => $session_id),
            array('%d'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get user practice items
     */
    public function get_user_practice_items($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_practice_items';
        
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d AND is_active = 1 ORDER BY sort_order ASC, name ASC",
            $user_id
        ), ARRAY_A);
        
        return $items ?: array();
    }
    
    /**
     * Get lesson favorites
     */
    public function get_lesson_favorites($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        
        $favorites = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ), ARRAY_A);
        
        return $favorites ?: array();
    }
    
    /**
     * Get badges
     */
    public function get_badges($active_only = false) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_badges';
        
        $where = $active_only ? "WHERE is_active = 1" : "";
        
        $badges = $wpdb->get_results(
            "SELECT * FROM {$table_name} {$where} ORDER BY display_order ASC, name ASC",
            ARRAY_A
        );
        
        return $badges ?: array();
    }
    
    /**
     * Get user badges
     */
    public function get_user_badges($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_badges';
        
        $badges = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY earned_at DESC",
            $user_id
        ), ARRAY_A);
        
        return $badges ?: array();
    }
    
    /**
     * Award badge
     */
    public function award_badge($user_id, $badge_key) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_badges';
        
        // Check if already awarded
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d AND badge_key = %s",
            $user_id, $badge_key
        ));
        
        if ($existing) {
            return false; // Already awarded
        }
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'badge_key' => $badge_key,
                'earned_at' => current_time('mysql'),
                'earned_date' => current_time('Y-m-d H:i:s')
            ),
            array('%d', '%s', '%s', '%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Record gems transaction
     */
    public function record_gems_transaction($user_id, $transaction_type, $amount, $source, $description) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_gems_transactions';
        
        // Get current balance
        $user_stats = $this->get_user_stats($user_id);
        $current_balance = $user_stats['gems_balance'];
        $new_balance = $current_balance + $amount;
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'transaction_type' => $transaction_type,
                'amount' => $amount,
                'source' => $source,
                'description' => $description,
                'balance_after' => $new_balance,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%d', '%s', '%s', '%d', '%s')
        );
        
        // Update user stats with new balance
        if ($result) {
            $this->update_user_stats($user_id, array('gems_balance' => $new_balance));
        }
        
        return $result !== false;
    }
    
    /**
     * Get badge by badge key
     */
    public function get_badge_by_key($badge_key) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_badges';
        
        $badge = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE badge_key = %s",
            $badge_key
        ), ARRAY_A);
        
        return $badge;
    }
    
    /**
     * Delete badge by badge key
     */
    public function delete_badge($badge_key) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_badges';
        
        $result = $wpdb->delete(
            $table_name,
            array('badge_key' => $badge_key),
            array('%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Update badge by badge key
     */
    public function update_badge($badge_key, $badge_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_badges';
        
        // Prepare the data for update
        $update_data = array();
        $format = array();
        
        if (isset($badge_data['name'])) {
            $update_data['name'] = sanitize_text_field($badge_data['name']);
            $format[] = '%s';
        }
        
        if (isset($badge_data['description'])) {
            $update_data['description'] = sanitize_textarea_field($badge_data['description']);
            $format[] = '%s';
        }
        
        if (isset($badge_data['category'])) {
            $update_data['category'] = sanitize_text_field($badge_data['category']);
            $format[] = '%s';
        }
        
        if (isset($badge_data['criteria_type'])) {
            $update_data['criteria_type'] = sanitize_text_field($badge_data['criteria_type']);
            $format[] = '%s';
        }
        
        if (isset($badge_data['criteria_value'])) {
            $update_data['criteria_value'] = intval($badge_data['criteria_value']);
            $format[] = '%d';
        }
        
        if (isset($badge_data['xp_reward'])) {
            $update_data['xp_reward'] = intval($badge_data['xp_reward']);
            $format[] = '%d';
        }
        
        if (isset($badge_data['gem_reward'])) {
            $update_data['gem_reward'] = intval($badge_data['gem_reward']);
            $format[] = '%d';
        }
        
        if (isset($badge_data['is_active'])) {
            $update_data['is_active'] = intval($badge_data['is_active']);
            $format[] = '%d';
        }
        
        if (isset($badge_data['icon'])) {
            $update_data['icon'] = sanitize_text_field($badge_data['icon']);
            $format[] = '%s';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('badge_key' => $badge_key),
            $format,
            array('%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Get last practice session for an item
     */
    public function get_last_practice_session($user_id, $item_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_practice_sessions';
        
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND practice_item_id = %d ORDER BY created_at DESC LIMIT 1",
            $user_id,
            $item_id
        ), ARRAY_A);
        
        return $session;
    }
    
    /**
     * Get monthly shield purchases for a user
     */
    public function get_monthly_shield_purchases($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_gem_transactions';
        
        $purchases = $wpdb->get_results($wpdb->prepare(
            "SELECT COUNT(*) as count FROM $table_name 
             WHERE user_id = %d 
             AND source = 'streak_shield_purchase' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
            $user_id
        ), ARRAY_A);
        
        return $purchases[0]['count'] ?? 0;
    }
    
    /**
     * Add practice item
     */
    public function add_practice_item($user_id, $name, $category = 'custom', $description = '', $url = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_practice_items';
        
        // Check if user already has this name (only active items)
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d AND name = %s AND is_active = 1",
            $user_id, $name
        ));
        
        if ($existing) {
            return new WP_Error('duplicate_name', 'You already have a practice item with this name');
        }
        
        // Check total item limit (max 6 active items total)
        $total_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND is_active = 1",
            $user_id
        ));
        
        if ($total_count >= 6) {
            return new WP_Error('limit_exceeded', 'You can only have 6 active practice items at once. Delete an item to add a new one.');
        }
        
        // Prepare insert data
        $insert_data = array(
            'user_id' => $user_id,
            'name' => $name,
            'category' => $category,
            'description' => $description,
            'is_active' => 1,
            'sort_order' => $total_count
        );
        
        // Add URL if provided (check if column exists)
        $columns = $wpdb->get_col("DESCRIBE {$table_name}");
        if (in_array('url', $columns) && !empty($url)) {
            $insert_data['url'] = $url;
        }
        
        $format = array('%d', '%s', '%s', '%s', '%d', '%d');
        if (in_array('url', $columns) && !empty($url)) {
            $format[] = '%s';
        }
        
        $result = $wpdb->insert($table_name, $insert_data, $format);
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to add practice item: ' . $wpdb->last_error);
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update practice item
     */
    public function update_practice_item($item_id, $name, $category = null, $description = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_practice_items';
        
        // Get current item
        $item = $wpdb->get_row($wpdb->prepare(
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
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $item_id),
            $update_format,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update practice item: ' . $wpdb->last_error);
        }
        
        return true;
    }
    
    /**
     * Delete practice item
     */
    public function delete_practice_item($item_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_practice_items';
        
        // Check if item exists
        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $item_id
        ), ARRAY_A);
        
        if (!$item) {
            return new WP_Error('item_not_found', 'Practice item not found');
        }
        
        // Soft delete (set is_active = 0)
        $result = $wpdb->update(
            $table_name,
            array('is_active' => 0),
            array('id' => $item_id),
            array('%d'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('delete_failed', 'Failed to delete practice item: ' . $wpdb->last_error);
        }
        
        return true;
    }
    
    /**
     * Get lesson favorite by ID
     */
    public function get_lesson_favorite($favorite_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        
        $favorite = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $favorite_id
        ), ARRAY_A);
        
        return $favorite;
    }
    
    /**
     * Get leaderboard data
     */
    public function get_leaderboard($limit = 50, $offset = 0, $sort_by = 'total_xp', $sort_order = 'desc') {
        global $wpdb;
        
        $stats_table = $wpdb->prefix . 'jph_user_stats';
        $users_table = $wpdb->users;
        
        // Validate sort_by parameter
        $allowed_sorts = array('total_xp', 'current_level', 'current_streak', 'total_sessions', 'total_minutes', 'badges_earned');
        if (!in_array($sort_by, $allowed_sorts)) {
            $sort_by = 'total_xp';
        }
        
        // Validate sort_order parameter
        $sort_order = strtoupper($sort_order);
        if (!in_array($sort_order, array('ASC', 'DESC'))) {
            $sort_order = 'DESC';
        }
        
        $query = $wpdb->prepare(
            "SELECT 
                s.user_id,
                s.total_xp,
                s.current_level,
                s.current_streak,
                s.total_sessions,
                s.total_minutes,
                s.badges_earned,
                s.display_name,
                COALESCE(s.display_name, u.display_name, u.user_login) as leaderboard_name
             FROM {$stats_table} s
             LEFT JOIN {$users_table} u ON s.user_id = u.ID
             WHERE s.show_on_leaderboard = 1 
                AND s.total_xp > 0
                ORDER BY s.{$sort_by} {$sort_order}, s.total_xp DESC
             LIMIT %d OFFSET %d",
            $limit, $offset
        );
        
        $leaderboard = $wpdb->get_results($query, ARRAY_A);
        
        return $leaderboard ?: array();
    }
    
    /**
     * Get user's leaderboard position
     */
    public function get_user_leaderboard_position($user_id, $sort_by = 'total_xp', $sort_order = 'desc') {
        global $wpdb;
        
        $stats_table = $wpdb->prefix . 'jph_user_stats';
        
        // Validate sort_by parameter
        $allowed_sorts = array('total_xp', 'current_level', 'current_streak', 'total_sessions', 'total_minutes', 'badges_earned');
        if (!in_array($sort_by, $allowed_sorts)) {
            $sort_by = 'total_xp';
        }
        
        // Validate sort_order parameter
        $sort_order = strtoupper($sort_order);
        if (!in_array($sort_order, array('ASC', 'DESC'))) {
            $sort_order = 'DESC';
        }
        
        // Get user's stats
        $user_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT {$sort_by} FROM {$stats_table} WHERE user_id = %d",
            $user_id
        ), ARRAY_A);
        
        if (!$user_stats) {
            return null;
        }
        
        $user_value = $user_stats[$sort_by];
        
        // Count users with better stats based on sort order
        if ($sort_order === 'DESC') {
            $position = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) + 1 
                 FROM {$stats_table} 
                 WHERE show_on_leaderboard = 1 
                 AND total_xp > 0
                 AND {$sort_by} > %d",
                $user_value
            ));
        } else {
            $position = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) + 1 
                 FROM {$stats_table} 
                 WHERE show_on_leaderboard = 1 
                 AND total_xp > 0
                 AND {$sort_by} < %d",
                $user_value
            ));
        }
        
        return (int) $position;
    }
    
    /**
     * Update user display name
     */
    public function update_user_display_name($user_id, $display_name) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        
        // Sanitize display name
        $display_name = sanitize_text_field($display_name);
        
        // Check if user stats exist
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d",
            $user_id
        ));
        
        if (!$existing) {
            // Create new stats record with display name
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'display_name' => $display_name,
                    'show_on_leaderboard' => 1
                ),
                array('%d', '%s', '%d')
            );
        } else {
            // Update existing stats
            $result = $wpdb->update(
                $table_name,
                array('display_name' => $display_name),
                array('user_id' => $user_id),
                array('%s'),
                array('%d')
            );
        }
        
        return $result !== false;
    }
    
    /**
     * Update user leaderboard visibility
     */
    public function update_user_leaderboard_visibility($user_id, $show_on_leaderboard) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        
        // Convert boolean to int
        $show_on_leaderboard = $show_on_leaderboard ? 1 : 0;
        
        // Check if user stats exist
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d",
            $user_id
        ));
        
        if (!$existing) {
            // Create new stats record
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'show_on_leaderboard' => $show_on_leaderboard
                ),
                array('%d', '%d')
            );
        } else {
            // Update existing stats
            $result = $wpdb->update(
                $table_name,
                array('show_on_leaderboard' => $show_on_leaderboard),
                array('user_id' => $user_id),
                array('%d'),
                array('%d')
            );
        }
        
        return $result !== false;
    }
    
    /**
     * Get leaderboard stats summary
     */
    public function get_leaderboard_stats() {
        global $wpdb;
        
        $stats_table = $wpdb->prefix . 'jph_user_stats';
        
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN show_on_leaderboard = 1 AND total_xp > 0 THEN 1 END) as leaderboard_users,
                AVG(CASE WHEN total_xp > 0 THEN total_xp END) as avg_xp,
                MAX(total_xp) as max_xp,
                AVG(CASE WHEN total_xp > 0 THEN current_level END) as avg_level,
                MAX(current_level) as max_level,
                AVG(CASE WHEN total_xp > 0 THEN current_streak END) as avg_streak,
                MAX(current_streak) as max_streak
             FROM {$stats_table}",
            ARRAY_A
        );
        
        return $stats ?: array();
    }
}