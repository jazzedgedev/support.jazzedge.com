<?php
/**
 * Gamification system for JazzEdge Practice Hub
 * 
 * @package JazzEdge_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Gamification {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Coming Soon - Gamification initialization
    }
    
    /**
     * Calculate XP for practice session
     */
    public function calculate_xp($duration_minutes, $sentiment_score, $improvement_detected = false) {
        // Base XP from duration (1 XP per minute, max 60 XP)
        $duration_xp = min($duration_minutes, 60);
        
        // Sentiment multiplier (1-5 scale)
        $sentiment_multiplier = $sentiment_score / 5.0;
        
        // Improvement bonus (25% extra XP)
        $improvement_bonus = $improvement_detected ? 1.25 : 1.0;
        
        // Calculate total XP
        $total_xp = round($duration_xp * $sentiment_multiplier * $improvement_bonus);
        
        // Minimum 1 XP for any session
        return max($total_xp, 1);
    }
    
    /**
     * Add XP to user
     */
    public function add_xp($user_id, $xp_amount) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        
        // Debug: Log the operation
        error_log("JPH: add_xp called for user $user_id with $xp_amount XP");
        
        // Get current stats or create new record
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d",
            $user_id
        ));
        
        if (!$stats) {
            error_log("JPH: No stats found, creating new record for user $user_id");
            // Create new stats record
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'total_xp' => $xp_amount,
                    'current_level' => 1,
                    'current_streak' => 0,
                    'longest_streak' => 0,
                    'total_sessions' => 1,
                    'total_minutes' => 0,
                    'hearts_count' => 5,
                    'gems_balance' => 0,
                    'badges_earned' => 0,
                    'last_practice_date' => current_time('Y-m-d')
                ),
                array('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s')
            );
            error_log("JPH: Insert result: " . print_r($result, true));
        } else {
            error_log("JPH: Found existing stats, updating for user $user_id");
            // Update existing stats
            $result = $wpdb->update(
                $table_name,
                array(
                    'total_xp' => $stats->total_xp + $xp_amount,
                    'total_sessions' => $stats->total_sessions + 1
                ),
                array('user_id' => $user_id),
                array('%d', '%d'),
                array('%d')
            );
            error_log("JPH: Update result: " . print_r($result, true));
        }
        
        return true;
    }
    
    /**
     * Check for level up
     */
    public function check_level_up($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d",
            $user_id
        ));
        
        if (!$stats) {
            return false;
        }
        
        $current_level = $stats->current_level;
        $total_xp = $stats->total_xp;
        
        // Calculate what level they should be based on XP
        $new_level = $this->calculate_level_from_xp($total_xp);
        
        if ($new_level > $current_level) {
            // Level up!
            $wpdb->update(
                $table_name,
                array('current_level' => $new_level),
                array('user_id' => $user_id),
                array('%d'),
                array('%d')
            );
            
            return array(
                'leveled_up' => true,
                'old_level' => $current_level,
                'new_level' => $new_level
            );
        }
        
        return array('leveled_up' => false, 'current_level' => $current_level);
    }
    
    /**
     * Calculate level from XP (exponential growth)
     */
    private function calculate_level_from_xp($xp) {
        // Level formula: Level = floor(sqrt(XP / 100)) + 1
        // This means: Level 1 = 0-99 XP, Level 2 = 100-399 XP, Level 3 = 400-899 XP, etc.
        return floor(sqrt($xp / 100)) + 1;
    }
    
    /**
     * Check for badges
     */
    public function check_badges($user_id) {
        // Coming Soon - Badge checking
        return array();
    }
    
    /**
     * Update streak
     */
    public function update_streak($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        $today = current_time('Y-m-d');
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d",
            $user_id
        ));
        
        if (!$stats) {
            return false;
        }
        
        $last_practice_date = $stats->last_practice_date;
        $current_streak = $stats->current_streak;
        $longest_streak = $stats->longest_streak;
        
        // Check if they practiced today
        if ($last_practice_date === $today) {
            // Already practiced today, no streak change
            return array('streak_updated' => false, 'current_streak' => $current_streak);
        }
        
        // Check if yesterday was their last practice
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        if ($last_practice_date === $yesterday) {
            // Continue streak
            $new_streak = $current_streak + 1;
        } else {
            // Streak broken, start over
            $new_streak = 1;
        }
        
        // Update longest streak if needed
        $new_longest_streak = max($longest_streak, $new_streak);
        
        // Update database
        $wpdb->update(
            $table_name,
            array(
                'current_streak' => $new_streak,
                'longest_streak' => $new_longest_streak,
                'last_practice_date' => $today
            ),
            array('user_id' => $user_id),
            array('%d', '%d', '%s'),
            array('%d')
        );
        
        return array(
            'streak_updated' => true,
            'current_streak' => $new_streak,
            'longest_streak' => $new_longest_streak,
            'streak_continued' => ($last_practice_date === $yesterday)
        );
    }
    
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
            // Create default stats record if none exists
            $this->create_default_user_stats($user_id);
            
            // Try to get stats again
            $stats = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d",
                $user_id
            ));
        }
        
        if (!$stats) {
            // Return default stats if still no record exists
            return array(
                'total_xp' => 0,
                'current_level' => 1,
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_sessions' => 0,
                'total_minutes' => 0,
                'badges_earned' => 0,
                'hearts_count' => 5,
                'gems_balance' => 0,
                'last_practice_date' => null
            );
        }
        
        return array(
            'total_xp' => (int) $stats->total_xp,
            'current_level' => (int) $stats->current_level,
            'current_streak' => (int) $stats->current_streak,
            'longest_streak' => (int) $stats->longest_streak,
            'total_sessions' => (int) $stats->total_sessions,
            'total_minutes' => (int) $stats->total_minutes,
            'badges_earned' => (int) $stats->badges_earned,
            'hearts_count' => (int) $stats->hearts_count,
            'gems_balance' => (int) $stats->gems_balance,
            'last_practice_date' => $stats->last_practice_date
        );
    }
    
    /**
     * Create default user stats record
     */
    private function create_default_user_stats($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'total_xp' => 0,
                'current_level' => 1,
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_sessions' => 0,
                'total_minutes' => 0,
                'hearts_count' => 5,
                'gems_balance' => 0,
                'badges_earned' => 0,
                'last_practice_date' => null
            ),
            array('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s')
        );
    }
}
