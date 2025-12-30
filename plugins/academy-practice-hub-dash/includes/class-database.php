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
        
        // Debug logging for gem balance changes
        if (isset($stats_data['gems_balance'])) {
            $current_stats = $this->get_user_stats($user_id);
            error_log("=== GEM BALANCE UPDATE DEBUG ===");
            error_log("User ID: {$user_id}");
            error_log("Current gems_balance: " . ($current_stats['gems_balance'] ?? 'N/A'));
            error_log("New gems_balance: " . $stats_data['gems_balance']);
            error_log("Update data: " . print_r($stats_data, true));
            error_log("Stack trace: " . wp_debug_backtrace_summary());
            error_log("=== END GEM BALANCE UPDATE DEBUG ===");
        }
        
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
        
        // Debug logging for gem balance changes - after update
        if (isset($stats_data['gems_balance'])) {
            error_log("Update result: " . ($result !== false ? 'SUCCESS' : 'FAILED'));
            if ($result === false) {
                error_log("Database error: " . $wpdb->last_error);
            }
            
            // Verify the update worked
            $updated_stats = $this->get_user_stats($user_id);
            error_log("Verification - gems_balance after update: " . ($updated_stats['gems_balance'] ?? 'N/A'));
        }
        
        // Invalidate cache when user stats are updated
        if ($result !== false) {
            $this->invalidate_user_cache($user_id);
        }
        
        return $result !== false;
    }
    
    /**
     * Invalidate user cache when stats change
     */
    private function invalidate_user_cache($user_id) {
        // Check if cache class is available
        if (class_exists('JPH_Cache')) {
            $cache = JPH_Cache::get_instance();
            $cache->invalidate_user_cache($user_id);
        }
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
        
        if ($result) {
            // Update last_practiced_date in user_plans
            $plans_table = $wpdb->prefix . 'jph_user_plans';
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$plans_table} (user_id, last_practiced_date, updated_at)
                 VALUES (%d, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE last_practiced_date = NOW(), updated_at = NOW()",
                $user_id
            ));
            
            // Trigger action for reminder system
            do_action('jph_practice_session_logged', $user_id);
        }
        
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
            "SELECT s.*, i.name as item_name, i.category as item_category
             FROM {$sessions_table} s 
             LEFT JOIN {$items_table} i ON s.practice_item_id = i.id 
             WHERE s.user_id = %d 
             ORDER BY s.created_at DESC 
             LIMIT %d OFFSET %d",
            $user_id, $limit, $offset
        );
        
        $sessions = $wpdb->get_results($query, ARRAY_A);
        
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
        
        // Return all favorites (lessons and collections) - filtering by category happens in the frontend
        $favorites = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ), ARRAY_A);
        
        // Normalize URLs - convert /lesson/{id} to proper permalinks
        foreach ($favorites as &$favorite) {
            if (!empty($favorite['url']) && preg_match('#^/lesson/(\d+)/?$#', $favorite['url'], $matches)) {
                $lesson_id = intval($matches[1]);
                $permalink = $this->get_lesson_permalink_from_id($lesson_id);
                if ($permalink) {
                    $favorite['url'] = $permalink;
                }
            }
        }
        unset($favorite); // Break reference
        
        return $favorites ?: array();
    }
    
    /**
     * Get lesson permalink from lesson ID
     */
    private function get_lesson_permalink_from_id($lesson_id) {
        global $wpdb;
        
        $lessons_table = $wpdb->prefix . 'alm_lessons';
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$lessons_table} WHERE ID = %d",
            $lesson_id
        ));
        
        if ($post_id) {
            $permalink = get_permalink($post_id);
            if ($permalink) {
                return $permalink;
            }
        }
        
        return null;
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
        
        $user_badges_table = $wpdb->prefix . 'jph_user_badges';
        $badges_table = $wpdb->prefix . 'jph_badges';
        
        $badges = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                ub.*,
                b.name,
                b.description,
                b.image_url,
                b.category
             FROM {$user_badges_table} ub
             LEFT JOIN {$badges_table} b ON ub.badge_key = b.badge_key
             WHERE ub.user_id = %d 
             ORDER BY ub.earned_at DESC",
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
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
        if (!$table_exists) {
            error_log("Table does not exist: {$table_name}");
            return false;
        }
        
        // Check if already awarded
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d AND badge_key = %s",
            $user_id, $badge_key
        ));
        
        if ($existing) {
            error_log("Badge already awarded: user_id={$user_id}, badge_key={$badge_key}");
            return false; // Already awarded
        }
        
        // Check if badge exists in badges table
        $badge_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}jph_badges WHERE badge_key = %s",
            $badge_key
        ));
        
        if (!$badge_exists) {
            error_log("Badge does not exist in badges table: badge_key={$badge_key}");
            return false;
        }
        
        $insert_data = array(
            'user_id' => $user_id,
            'badge_key' => $badge_key,
            'earned_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert(
            $table_name,
            $insert_data,
            array('%d', '%s', '%s')
        );
        
        if ($result === false) {
            error_log("Failed to insert badge: user_id={$user_id}, badge_key={$badge_key}");
            error_log("Insert data: " . print_r($insert_data, true));
            error_log("WP_Error: " . $wpdb->last_error);
            error_log("Last query: " . $wpdb->last_query);
        } else {
            error_log("Successfully awarded badge: user_id={$user_id}, badge_key={$badge_key}, insert_id=" . $wpdb->insert_id);
        }
        
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
        
        // CRITICAL FIX: Don't update user stats here - just record the transaction
        // The calling function should handle the gem balance update to avoid double updates
        if ($result) {
            error_log("=== GEMS TRANSACTION DEBUG ===");
            error_log("Recording transaction - User ID: {$user_id}, Type: {$transaction_type}, Amount: {$amount}");
            error_log("Current balance: {$current_balance}, Calculated new balance: {$new_balance}");
            error_log("Source: {$source}, Description: {$description}");
            error_log("NOTE: Not updating user stats here to avoid double updates");
            error_log("=== END GEMS TRANSACTION DEBUG ===");
            
            // REMOVED: $this->update_user_stats($user_id, array('gems_balance' => $new_balance));
            // The calling function should handle the gem balance update
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
        $existing_active = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d AND name = %s AND is_active = 1",
            $user_id, $name
        ));
        
        if ($existing_active) {
            return new WP_Error('duplicate_name', 'You already have a practice item with this name');
        }
        
        // Check if there's an inactive item with the same name (reactivate instead of insert)
        $existing_inactive = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d AND name = %s AND is_active = 0",
            $user_id, $name
        ));
        
        if ($existing_inactive) {
            // Reactivate the existing inactive item
            $total_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND is_active = 1",
                $user_id
            ));
            
            // Check total item limit (max 6 active items total)
            if ($total_count >= 6) {
                return new WP_Error('limit_exceeded', 'You can only have 6 active practice items at once. Delete an item to add a new one.');
            }
            
            // Prepare update data
            $update_data = array(
                'is_active' => 1,
                'category' => $category,
                'description' => $description,
                'sort_order' => $total_count,
                'updated_at' => current_time('mysql')
            );
            
            // Add URL if provided (check if column exists)
            $columns = $wpdb->get_col("DESCRIBE {$table_name}");
            if (in_array('url', $columns) && !empty($url)) {
                $update_data['url'] = $url;
            }
            
            $update_format = array('%d', '%s', '%s', '%d', '%s');
            if (in_array('url', $columns) && !empty($url)) {
                $update_format[] = '%s';
            }
            
            $result = $wpdb->update(
                $table_name,
                $update_data,
                array('id' => $existing_inactive),
                $update_format,
                array('%d')
            );
            
            if ($result === false) {
                return new WP_Error('update_failed', 'Failed to reactivate practice item: ' . $wpdb->last_error);
            }
            
            return $existing_inactive;
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
     * Update the display order for a user's practice items
     */
    public function update_practice_item_order($user_id, $ordered_ids) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'jph_practice_items';

        if (!$user_id) {
            return new WP_Error('missing_user', 'User ID is required to update practice items');
        }

        if (empty($ordered_ids) || !is_array($ordered_ids)) {
            return new WP_Error('invalid_order', 'No practice items were supplied');
        }

        // Normalize IDs
        $ordered_ids = array_values(array_unique(array_filter(array_map('intval', $ordered_ids))));

        if (empty($ordered_ids)) {
            return new WP_Error('invalid_order', 'No valid practice items were supplied');
        }

        // Fetch all active items for this user so we can append any that were not included in the payload
        $all_items = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d AND is_active = 1 ORDER BY sort_order ASC, id ASC",
            $user_id
        ));

        if (empty($all_items)) {
            return new WP_Error('no_items', 'No practice items found for this user');
        }

        // Preserve existing order for any items not referenced in the drag/drop payload
        $remaining_items = array_values(array_diff($all_items, $ordered_ids));
        $final_order = array_merge($ordered_ids, $remaining_items);

        $position = 0;
        foreach ($final_order as $item_id) {
            $result = $wpdb->update(
                $table_name,
                array('sort_order' => $position),
                array('id' => $item_id, 'user_id' => $user_id),
                array('%d'),
                array('%d', '%d')
            );

            if ($result === false) {
                return new WP_Error('order_update_failed', 'Failed to update practice item order: ' . $wpdb->last_error);
            }

            $position++;
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
     * Check if lesson is already favorited
     */
    public function is_lesson_favorited($user_id, $title) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d AND title = %s",
            $user_id, $title
        ));
        
        return $existing ? $existing->id : false;
    }
    
    /**
     * Add lesson favorite
     */
    public function add_lesson_favorite($user_id, $title, $url, $category = 'lesson', $description = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        
        // Check if favorite already exists (unique constraint on user_id + title)
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d AND title = %s",
            $user_id, $title
        ));
        
        if ($existing) {
            return new WP_Error('duplicate_favorite', 'This lesson is already in your favorites');
        }
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'title' => $title,
                'url' => $url,
                'category' => $category,
                'description' => $description,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to add lesson favorite');
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Remove lesson favorite
     */
    public function remove_lesson_favorite($user_id, $title) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        
        $result = $wpdb->delete(
            $table_name,
            array(
                'user_id' => $user_id,
                'title' => $title
            ),
            array('%d', '%s')
        );
        
        return $result !== false;
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
                COALESCE(s.display_name, u.display_name, u.user_login) as leaderboard_name,
                u.user_email
             FROM {$stats_table} s
             INNER JOIN {$users_table} u ON s.user_id = u.ID
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
        
        // Get user's stats - only if they're on the leaderboard
        $user_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT {$sort_by} FROM {$stats_table} WHERE user_id = %d AND show_on_leaderboard = 1 AND total_xp > 0",
            $user_id
        ), ARRAY_A);
        
        if (!$user_stats) {
            return null; // User is not on leaderboard
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
        $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
        
        // Get base stats
        $base_stats = $wpdb->get_row(
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
        
        // Get 7-day practice stats
        $stats_7days = $wpdb->get_row(
            "SELECT 
                COALESCE(SUM(duration_minutes), 0) as practice_minutes_7days,
                COUNT(DISTINCT user_id) as active_users_7days,
                COUNT(*) as total_sessions_7days
             FROM {$sessions_table}
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            ARRAY_A
        );
        
        // Get 30-day practice stats
        $stats_30days = $wpdb->get_row(
            "SELECT 
                COALESCE(SUM(duration_minutes), 0) as practice_minutes_30days,
                COUNT(DISTINCT user_id) as active_users_30days,
                COUNT(*) as total_sessions_30days
             FROM {$sessions_table}
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            ARRAY_A
        );
        
        // Merge all stats
        $stats = array_merge(
            $base_stats ?: array(),
            $stats_7days ?: array(),
            $stats_30days ?: array()
        );
        
        return $stats;
    }
    
    /**
     * Get total count of leaderboard users
     */
    public function get_leaderboard_total_count() {
        global $wpdb;
        
        $stats_table = $wpdb->prefix . 'jph_user_stats';
        
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$stats_table} WHERE show_on_leaderboard = 1 AND total_xp > 0"
        );
        
        return (int) $count;
    }
    
    /**
     * Get user's repertoire items
     */
    public function get_user_repertoire($user_id, $order_by = 'last_practiced', $order = 'DESC') {
        global $wpdb;
        
        $table_name = 'academy_user_repertoire';
        
        // Validate order_by
        $allowed_order_by = array('last_practiced', 'title', 'date_added');
        $order_by = in_array($order_by, $allowed_order_by) ? $order_by : 'last_practiced';
        
        // Validate order
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d AND deleted_at IS NULL ORDER BY {$order_by} {$order}",
            $user_id
        ), ARRAY_A);
        
        return $items ?: array();
    }
    
    /**
     * Add repertoire item
     */
    public function add_repertoire_item($user_id, $title, $composer, $notes = '') {
        global $wpdb;
        
        $table_name = 'academy_user_repertoire';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'title' => $title,
                'composer' => $composer,
                'notes' => $notes,
                'date_added' => current_time('mysql'),
                'last_practiced' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update repertoire item
     */
    public function update_repertoire_item($item_id, $user_id, $title, $composer, $notes = '') {
        global $wpdb;
        
        $table_name = 'academy_user_repertoire';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'title' => $title,
                'composer' => $composer,
                'notes' => $notes
            ),
            array(
                'ID' => $item_id,
                'user_id' => $user_id
            ),
            array('%s', '%s', '%s'),
            array('%d', '%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete repertoire item (soft delete)
     */
    public function delete_repertoire_item($item_id, $user_id) {
        global $wpdb;
        
        $table_name = 'academy_user_repertoire';
        
        $result = $wpdb->update(
            $table_name,
            array('deleted_at' => current_time('mysql')),
            array(
                'ID' => $item_id,
                'user_id' => $user_id
            ),
            array('%s'),
            array('%d', '%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Mark repertoire as practiced
     */
    public function mark_repertoire_practiced($item_id, $user_id) {
        global $wpdb;
        
        $table_name = 'academy_user_repertoire';
        
        $result = $wpdb->update(
            $table_name,
            array('last_practiced' => current_time('mysql')),
            array(
                'ID' => $item_id,
                'user_id' => $user_id
            ),
            array('%s'),
            array('%d', '%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Update repertoire sort order
     */
    public function update_repertoire_order($user_id, $item_orders) {
        global $wpdb;
        
        $table_name = 'academy_user_repertoire';
        
        foreach ($item_orders as $item_id => $order) {
            $wpdb->update(
                $table_name,
                array('last_practiced' => current_time('mysql')), // Using last_practiced as sort order
                array(
                    'ID' => $item_id,
                    'user_id' => $user_id
                ),
                array('%s'),
                array('%d', '%d')
            );
        }
        
        return true;
    }
    
    /**
     * Ensure user_plans table exists
     */
    private function ensure_user_plans_table_exists() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_plans';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            // Table doesn't exist, create it
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL,
                transformation text DEFAULT NULL,
                goal_90_day text DEFAULT NULL,
                weekly_focus_item_id bigint(20) unsigned DEFAULT NULL,
                practice_steps text DEFAULT NULL,
                deadline date DEFAULT NULL,
                sessions_this_week int(11) unsigned DEFAULT 0,
                last_practiced_date datetime DEFAULT NULL,
                week_start_date date DEFAULT NULL,
                reminder_enabled tinyint(1) unsigned DEFAULT 1,
                reminder_threshold_days int(11) unsigned DEFAULT 3,
                last_reminder_sent datetime DEFAULT NULL,
                reminder_cooldown_days int(11) unsigned DEFAULT 7,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY user_id (user_id),
                KEY weekly_focus_item_id (weekly_focus_item_id),
                KEY week_start_date (week_start_date)
            ) $charset_collate;";
            
            dbDelta($sql);
            error_log("APH: Created user_plans table: $table_name");
        } else {
            // Table exists, check if we need to add reminder columns
            $this->maybe_add_reminder_columns($table_name);
        }
    }
    
    /**
     * Add reminder columns to existing user_plans table if they don't exist
     */
    private function maybe_add_reminder_columns($table_name) {
        global $wpdb;
        
        $columns_to_add = array(
            'reminder_enabled' => "ALTER TABLE $table_name ADD COLUMN reminder_enabled tinyint(1) unsigned DEFAULT 1",
            'reminder_threshold_days' => "ALTER TABLE $table_name ADD COLUMN reminder_threshold_days int(11) unsigned DEFAULT 3",
            'last_reminder_sent' => "ALTER TABLE $table_name ADD COLUMN last_reminder_sent datetime DEFAULT NULL",
            'reminder_cooldown_days' => "ALTER TABLE $table_name ADD COLUMN reminder_cooldown_days int(11) unsigned DEFAULT 7"
        );
        
        foreach ($columns_to_add as $column_name => $sql) {
            $column_exists = $wpdb->get_results($wpdb->prepare(
                "SHOW COLUMNS FROM $table_name LIKE %s",
                $column_name
            ));
            
            if (empty($column_exists)) {
                $wpdb->query($sql);
                error_log("APH: Added column $column_name to user_plans table");
            }
        }
    }
    
    /**
     * Get user's plan data
     */
    public function get_user_plan($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_plans';
        
        // Ensure table exists
        $this->ensure_user_plans_table_exists();
        
        $plan = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d",
            $user_id
        ), ARRAY_A);
        
        if ($plan && !empty($plan['practice_steps'])) {
            $plan['practice_steps'] = json_decode($plan['practice_steps'], true);
            if (!is_array($plan['practice_steps'])) {
                $plan['practice_steps'] = array();
            }
        } else if ($plan) {
            $plan['practice_steps'] = array();
        }
        
        return $plan ?: null;
    }
    
    /**
     * Save or update user's plan data
     */
    public function save_user_plan($user_id, $plan_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_plans';
        
        // Ensure table exists
        $this->ensure_user_plans_table_exists();
        
        // Check if plan exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d",
            $user_id
        ));
        
        // Prepare data
        $data = array(
            'user_id' => $user_id,
            'updated_at' => current_time('mysql')
        );
        $format = array('%d', '%s');
        
        if (isset($plan_data['transformation'])) {
            $data['transformation'] = $plan_data['transformation'];
            $format[] = '%s';
        }
        
        if (isset($plan_data['goal_90_day'])) {
            $data['goal_90_day'] = $plan_data['goal_90_day'];
            $format[] = '%s';
        }
        
        if (isset($plan_data['weekly_focus_item_id'])) {
            $data['weekly_focus_item_id'] = $plan_data['weekly_focus_item_id'];
            $format[] = '%d';
        }
        
        if (isset($plan_data['practice_steps'])) {
            $data['practice_steps'] = json_encode($plan_data['practice_steps']);
            $format[] = '%s';
        }
        
        if (isset($plan_data['deadline'])) {
            $data['deadline'] = $plan_data['deadline'];
            $format[] = '%s';
        }
        
        if (isset($plan_data['week_start_date'])) {
            $data['week_start_date'] = $plan_data['week_start_date'];
            $format[] = '%s';
        }
        
        if ($existing) {
            // Update existing plan
            $result = $wpdb->update(
                $table_name,
                $data,
                array('user_id' => $user_id),
                $format,
                array('%d')
            );
            
            if ($result === false) {
                error_log('PLAN Update Error: ' . $wpdb->last_error);
                error_log('PLAN Update Data: ' . print_r($data, true));
                error_log('PLAN Update Format: ' . print_r($format, true));
                return new WP_Error('update_failed', 'Failed to update plan: ' . $wpdb->last_error, array('status' => 500));
            }
            
            return true;
        } else {
            // Create new plan
            $data['created_at'] = current_time('mysql');
            $format[] = '%s';
            
            $result = $wpdb->insert($table_name, $data, $format);
            
            if ($result === false) {
                error_log('PLAN Insert Error: ' . $wpdb->last_error);
                error_log('PLAN Insert Data: ' . print_r($data, true));
                error_log('PLAN Insert Format: ' . print_r($format, true));
                return new WP_Error('insert_failed', 'Failed to create plan: ' . $wpdb->last_error, array('status' => 500));
            }
            
            return $wpdb->insert_id;
        }
    }
    
    /**
     * Update plan goal
     */
    public function update_plan_goal($user_id, $goal) {
        return $this->save_user_plan($user_id, array('goal_90_day' => $goal));
    }
    
    /**
     * Update weekly focus item
     */
    public function update_weekly_focus($user_id, $item_id) {
        // Reset week if needed
        $this->maybe_reset_week($user_id);
        
        return $this->save_user_plan($user_id, array('weekly_focus_item_id' => $item_id));
    }
    
    /**
     * Update practice steps
     */
    public function update_practice_steps($user_id, $steps) {
        if (!is_array($steps)) {
            $steps = array();
        }
        
        // Limit to 5 steps
        $steps = array_slice($steps, 0, 5);
        
        return $this->save_user_plan($user_id, array('practice_steps' => $steps));
    }
    
    /**
     * Mark plan as practiced today
     */
    public function mark_plan_practiced($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_plans';
        
        // Reset week if needed
        $this->maybe_reset_week($user_id);
        
        // Get current plan
        $plan = $this->get_user_plan($user_id);
        
        if (!$plan) {
            // Create plan if it doesn't exist
            $this->save_user_plan($user_id, array());
            $plan = $this->get_user_plan($user_id);
        }
        
        // Check if already practiced today
        $today = current_time('Y-m-d');
        $last_practiced = $plan['last_practiced_date'] ? date('Y-m-d', strtotime($plan['last_practiced_date'])) : null;
        
        if ($last_practiced === $today) {
            // Already practiced today, don't increment
            return true;
        }
        
        // Increment session count
        $sessions = intval($plan['sessions_this_week']) + 1;
        
        $result = $wpdb->update(
            $table_name,
            array(
                'sessions_this_week' => $sessions,
                'last_practiced_date' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('user_id' => $user_id),
            array('%d', '%s', '%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get weekly session count
     */
    public function get_weekly_session_count($user_id, $week_start = null) {
        $plan = $this->get_user_plan($user_id);
        
        if (!$plan) {
            return 0;
        }
        
        // Check if week needs reset
        $this->maybe_reset_week($user_id);
        
        // Get updated plan
        $plan = $this->get_user_plan($user_id);
        
        return intval($plan['sessions_this_week']);
    }
    
    /**
     * Check if week needs to be reset and reset if necessary
     */
    private function maybe_reset_week($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_plans';
        
        $plan = $this->get_user_plan($user_id);
        
        if (!$plan) {
            return;
        }
        
        // Calculate current week start (Monday)
        $today = new DateTime(current_time('Y-m-d'));
        $day_of_week = $today->format('w'); // 0 = Sunday, 1 = Monday, etc.
        $days_to_monday = ($day_of_week == 0) ? 6 : ($day_of_week - 1);
        $current_week_start = clone $today;
        $current_week_start->modify("-{$days_to_monday} days");
        $current_week_start_str = $current_week_start->format('Y-m-d');
        
        // Check if stored week_start_date is different from current week
        $stored_week_start = $plan['week_start_date'];
        
        if (!$stored_week_start || $stored_week_start !== $current_week_start_str) {
            // Reset week
            $wpdb->update(
                $table_name,
                array(
                    'sessions_this_week' => 0,
                    'week_start_date' => $current_week_start_str,
                    'updated_at' => current_time('mysql')
                ),
                array('user_id' => $user_id),
                array('%d', '%s', '%s'),
                array('%d')
            );
        }
    }
}