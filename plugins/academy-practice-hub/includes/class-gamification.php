<?php
/**
 * Gamification system for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class APH_Gamification {
    
    private $database;
    private $logger;
    
    public function __construct() {
        $this->database = new JPH_Database();
        $this->logger = JPH_Logger::get_instance();
    }
    
    /**
     * Calculate XP for a practice session
     */
    public function calculate_xp($duration_minutes, $sentiment_score, $improvement_detected = false) {
        // Base XP: 1 XP per minute
        $base_xp = $duration_minutes;
        
        // Sentiment bonus: higher sentiment = more XP
        $sentiment_multiplier = 1.0;
        switch ($sentiment_score) {
            case 5: $sentiment_multiplier = 1.5; break; // Excellent
            case 4: $sentiment_multiplier = 1.3; break; // Good
            case 3: $sentiment_multiplier = 1.0; break; // Okay
            case 2: $sentiment_multiplier = 0.8; break; // Challenging
            case 1: $sentiment_multiplier = 0.6; break; // Frustrating
        }
        
        // Improvement bonus: 25% extra XP when improvement detected
        $improvement_bonus = $improvement_detected ? 1.25 : 1.0;
        
        $total_xp = round($base_xp * $sentiment_multiplier * $improvement_bonus);
        
        // Minimum 1 XP for any session
        return max(1, $total_xp);
    }
    
    /**
     * Add XP to user
     */
    public function add_xp($user_id, $xp_amount) {
        $user_stats = $this->get_user_stats($user_id);
        
        $new_total_xp = $user_stats['total_xp'] + $xp_amount;
        $new_total_sessions = $user_stats['total_sessions'] + 1;
        
        $update_data = array(
            'total_xp' => $new_total_xp,
            'total_sessions' => $new_total_sessions
        );
        
        return $this->database->update_user_stats($user_id, $update_data);
    }
    
    /**
     * Check for level up
     */
    public function check_level_up($user_id) {
        $user_stats = $this->get_user_stats($user_id);
        $current_level = $user_stats['current_level'];
        $total_xp = $user_stats['total_xp'];
        
        $new_level = $this->calculate_level_from_xp($total_xp);
        
        if ($new_level > $current_level) {
            $this->database->update_user_stats($user_id, array('current_level' => $new_level));
            return array('leveled_up' => true, 'old_level' => $current_level, 'new_level' => $new_level);
        }
        
        return array('leveled_up' => false, 'current_level' => $current_level);
    }
    
    /**
     * Calculate level from XP
     */
    public function calculate_level_from_xp($xp) {
        // Level formula: Level = floor(sqrt(XP / 100)) + 1
        return floor(sqrt($xp / 100)) + 1;
    }

    /**
     * Get the timezone string for a user, falling back to site timezone
     *
     * @param int $user_id
     * @return string
     */
    public static function get_user_timezone_string($user_id = 0) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $timezone = '';
        if ($user_id) {
            $timezone = get_user_meta($user_id, 'aph_user_timezone', true);
        }

        if (!empty($timezone)) {
            try {
                new DateTimeZone($timezone);
                return $timezone;
            } catch (Exception $e) {
                // Invalid timezone stored, fall back to site timezone
            }
        }

        $wp_timezone = wp_timezone();
        return $wp_timezone instanceof DateTimeZone ? $wp_timezone->getName() : 'UTC';
    }

    /**
     * Get a DateTimeZone instance for a user
     *
     * @param int $user_id
     * @return DateTimeZone
     */
    public static function get_user_timezone($user_id = 0) {
        $timezone_string = self::get_user_timezone_string($user_id);
        return new DateTimeZone($timezone_string);
    }
    
    /**
     * Update streak
     * Recalculates streak from all practice sessions to ensure accuracy
     * Uses timestamp-based calculations to handle timezones correctly
     */
    public function update_streak($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
        
        // Get user stats
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d",
            $user_id
        ));
        
        if (!$stats) {
            return false;
        }
        
        $current_shields = $stats->streak_shield_count ?? 0;
        
        // Get all practice sessions
        $sessions = $wpdb->get_results($wpdb->prepare(
            "SELECT created_at FROM {$sessions_table} 
             WHERE user_id = %d 
             ORDER BY created_at ASC",
            $user_id
        ));
        
        if (empty($sessions)) {
            // No practice sessions, reset streak to 0
            $wpdb->update(
                $table_name,
                array(
                    'current_streak' => 0,
                    'last_practice_date' => null,
                    'updated_at' => current_time('mysql')
                ),
                array('user_id' => $user_id),
                array('%d', '%s', '%s'),
                array('%d')
            );
            return array('streak_updated' => false, 'current_streak' => 0);
        }
        
        // Get user timezone
        $user_timezone = self::get_user_timezone($user_id);
        $wp_timezone = $user_timezone;
        
        // Get unique practice dates (calendar days) in user's timezone
        $practice_dates = array();
        foreach ($sessions as $session) {
            // created_at is stored in WordPress timezone (via current_time('mysql'))
            // Convert to user timezone and get date
            $session_datetime = new DateTime($session->created_at, $wp_timezone);
            $date = $session_datetime->format('Y-m-d');
            if (!in_array($date, $practice_dates)) {
                $practice_dates[] = $date;
            }
        }
        
        if (empty($practice_dates)) {
            return false;
        }
        
        // Sort dates ascending
        sort($practice_dates);
        
        // Get current date in user's timezone
        $now_timestamp = current_time('timestamp');
        $now_datetime = new DateTime('@' . $now_timestamp);
        $now_datetime->setTimezone($user_timezone);
        $today = $now_datetime->format('Y-m-d');
        
        // Calculate yesterday
        $yesterday_timestamp = $now_timestamp - DAY_IN_SECONDS;
        $yesterday_datetime = new DateTime('@' . $yesterday_timestamp);
        $yesterday_datetime->setTimezone($user_timezone);
        $yesterday = $yesterday_datetime->format('Y-m-d');
        
        // Calculate current streak (from most recent date backwards, counting consecutive calendar days)
        $current_streak = 0;
        $most_recent_date = end($practice_dates);
        
        // Check if they have an active streak (practiced today or yesterday)
        if ($most_recent_date === $today || $most_recent_date === $yesterday) {
            $current_streak = 1;
            $check_date = $most_recent_date;
            
            // Count backwards for consecutive calendar days
            $date_index = array_search($most_recent_date, $practice_dates);
            for ($i = $date_index - 1; $i >= 0; $i--) {
                // Calculate expected previous date
                $check_datetime = new DateTime($check_date, $wp_timezone);
                $check_datetime->modify('-1 day');
                $expected_date = $check_datetime->format('Y-m-d');
                
                if ($practice_dates[$i] === $expected_date) {
                    // Consecutive day found
                    $current_streak++;
                    $check_date = $expected_date;
                } else {
                    // Gap found, streak broken
                    break;
                }
            }
        }
        
        // Calculate longest streak (consecutive calendar days)
        $longest_streak = 1;
        $temp_streak = 1;
        for ($i = 1; $i < count($practice_dates); $i++) {
            $prev_date = $practice_dates[$i - 1];
            $curr_date = $practice_dates[$i];
            
            // Check if dates are consecutive
            $prev_datetime = new DateTime($prev_date, $wp_timezone);
            $prev_datetime->modify('+1 day');
            $expected_next_date = $prev_datetime->format('Y-m-d');
            
            if ($curr_date === $expected_next_date) {
                // Consecutive day
                $temp_streak++;
            } else {
                // Gap found, reset streak
                $longest_streak = max($longest_streak, $temp_streak);
                $temp_streak = 1;
            }
        }
        $longest_streak = max($longest_streak, $temp_streak);
        
        // Determine if streak continued or was reset
        $old_streak = intval($stats->current_streak);
        $streak_continued = ($current_streak > 0 && ($current_streak >= $old_streak || $old_streak == 0));
        $shield_used = false;
        
        // If streak was broken and user has shields, we could use one here
        // But for now, we'll just recalculate accurately
        // Shield logic can be added later if needed for the 36-hour grace period
        
        // Update database
        $wpdb->update(
            $table_name,
            array(
                'current_streak' => $current_streak,
                'longest_streak' => $longest_streak,
                'last_practice_date' => $most_recent_date,
                'updated_at' => current_time('mysql')
            ),
            array('user_id' => $user_id),
            array('%d', '%d', '%s', '%s'),
            array('%d')
        );
        
        return array(
            'streak_updated' => true,
            'current_streak' => $current_streak,
            'longest_streak' => $longest_streak,
            'streak_continued' => $streak_continued,
            'shield_used' => $shield_used,
            'shields_remaining' => $current_shields
        );
    }
    
    /**
     * Get user stats
     */
    public function get_user_stats($user_id) {
        return $this->database->get_user_stats($user_id);
    }
    
    /**
     * Check and award badges
     */
    public function check_and_award_badges($user_id) {
        // Get user stats
        $user_stats = $this->get_user_stats($user_id);
        
        // Get user's practice sessions (ALL sessions for badge checking, not limited)
        $sessions = $this->database->get_practice_sessions($user_id, 1000, 0);
        $user_timezone = self::get_user_timezone($user_id);
        
        // Get all available badges
        $all_badges = $this->database->get_badges(true);
        
        // Get user's already earned badges
        $earned_badges = $this->database->get_user_badges($user_id);
        $earned_badge_keys = array_column($earned_badges, 'badge_key');
        
        $newly_awarded = array();
        
        foreach ($all_badges as $badge) {
            // Skip if already earned
            if (in_array($badge['badge_key'], $earned_badge_keys)) {
                continue;
            }
            
            $should_award = false;
            
            // Use criteria_type instead of badge_key
            $criteria_type = $badge['criteria_type'] ?? '';
            $criteria_value = intval($badge['criteria_value'] ?? 0);
            
            switch ($criteria_type) {
                case 'total_xp':
                    if ($user_stats['total_xp'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'level_reached':
                    if ($user_stats['current_level'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'practice_sessions':
                    if ($user_stats['total_sessions'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'total_time':
                case 'long_session':
                    // Check if user has any session >= criteria_value minutes
                    foreach ($sessions as $session) {
                        if ($session['duration_minutes'] >= $criteria_value) {
                            $should_award = true;
                            break;
                        }
                    }
                    break;
                    
                case 'improvement_count':
                    // Check sessions with improvement detected
                    $improvement_count = 0;
                    foreach ($sessions as $session) {
                        if ($session['improvement_detected']) {
                            $improvement_count++;
                        }
                    }
                    if ($improvement_count >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'streak':
                    if ($user_stats['current_streak'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'long_session_count':
                    // Count sessions over 30 minutes
                    $long_sessions = 0;
                    foreach ($sessions as $session) {
                        if ($session['duration_minutes'] >= 30) {
                            $long_sessions++;
                        }
                    }
                    if ($long_sessions >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'comeback':
                    // Check if user returned after 7+ day break and completed 3 sessions in a week
                    $should_award = $this->check_comeback_badge($user_id, $sessions, $user_timezone);
                    break;
                    
                case 'time_of_day':
                    // Check practice time patterns (early bird or night owl)
                    $should_award = $this->check_time_of_day_badge($user_id, $sessions, $criteria_value, $user_timezone);
                    break;
            }
            
            if ($should_award) {
                // Award the badge
                $badge_awarded = $this->database->award_badge(
                    $user_id,
                    $badge['badge_key']
                );
                
                // Log badge awarding attempt
                $this->logger->info('Badge awarding attempt', array(
                    'user_id' => $user_id,
                    'badge_key' => $badge['badge_key'],
                    'badge_name' => $badge['name'],
                    'awarded' => $badge_awarded
                ));
                
                if (!$badge_awarded) {
                    $this->logger->error('Failed to award badge', array(
                        'user_id' => $user_id,
                        'badge_key' => $badge['badge_key'],
                        'badge_name' => $badge['name']
                    ));
                    continue; // Skip to next badge if this one failed
                }
                
                // Update user stats with XP reward, gems reward, and badge count
                $update_data = array();
                if ($badge['xp_reward'] > 0) {
                    $update_data['total_xp'] = $user_stats['total_xp'] + $badge['xp_reward'];
                }
                if ($badge['gem_reward'] > 0) {
                    $update_data['gems_balance'] = $user_stats['gems_balance'] + $badge['gem_reward'];
                    // Record gems transaction
                    $this->database->record_gems_transaction(
                        $user_id,
                        'earned',
                        $badge['gem_reward'],
                        'badge_' . $badge['badge_key'],
                        'Earned ' . $badge['gem_reward'] . ' gems for earning badge: ' . $badge['name']
                    );
                }
                $update_data['badges_earned'] = $user_stats['badges_earned'] + 1;
                $this->database->update_user_stats($user_id, $update_data);
                
                // CRITICAL FIX: Update user_stats array with new values for next badge calculation
                if ($badge['xp_reward'] > 0) {
                    $user_stats['total_xp'] += $badge['xp_reward'];
                }
                if ($badge['gem_reward'] > 0) {
                    $user_stats['gems_balance'] += $badge['gem_reward'];
                }
                $user_stats['badges_earned'] += 1;
                
                // Debug logging for gem accumulation
                $this->logger->info('Badge awarded - updated user stats', array(
                    'user_id' => $user_id,
                    'badge_key' => $badge['badge_key'],
                    'badge_name' => $badge['name'],
                    'gem_reward' => $badge['gem_reward'],
                    'new_gem_balance' => $user_stats['gems_balance'],
                    'new_xp_total' => $user_stats['total_xp'],
                    'new_badge_count' => $user_stats['badges_earned']
                ));
                
                // Trigger FluentCRM event if enabled
                if ($badge['fluentcrm_enabled'] == '1') {
                    $this->trigger_fluentcrm_event($user_id, $badge);
                }
                
                $newly_awarded[] = $badge;
            }
        }
        
        return $newly_awarded;
    }
    
    /**
     * Trigger FluentCRM event for badge earning
     */
    private function trigger_fluentcrm_event($user_id, $badge) {
        try {
            $this->logger->debug('Triggering FluentCRM event', array(
                'user_id' => $user_id,
                'badge_key' => $badge['badge_key'],
                'event_key' => $badge['fluentcrm_event_key']
            ));
            
            // Get user email
            $user = get_user_by('ID', $user_id);
            if (!$user) {
                $this->logger->error('FluentCRM Event: User not found', array('user_id' => $user_id));
                return false;
            }
            
            $user_email = $user->user_email;
            
            // Keep original event key for automation triggers, but add timestamp to title for uniqueness
            $event_key = $badge['fluentcrm_event_key'];
            $unique_title = $badge['fluentcrm_event_title'] . ' at ' . current_time('Y-m-d H:i:s');
            $event_value = "Badge: {$badge['name']} - {$badge['description']}";
            
            // Use FluentCRM event tracking API
            if (function_exists('FluentCrmApi')) {
                try {
                    // Method 1: Try event tracker API
                    $tracker = FluentCrmApi('event_tracker');
                    if (method_exists($tracker, 'track')) {
                        $result = $tracker->track([
                            'event_key' => $event_key,
                            'title' => $unique_title,
                            'email' => $user_email,
                            'value' => $event_value,
                            'provider' => 'academy_practice_hub'
                        ]);
                        
                        if ($result) {
                            $this->logger->info('FluentCRM Event triggered successfully via tracker', array(
                                'event_key' => $event_key,
                                'user_email' => $user_email
                            ));
                            // Ensure email is saved directly to database
                            $this->ensure_event_email_saved($event_key, $unique_title, $event_value, $user_email);
                            return true;
                        }
                    }
                    
                    // Method 2: Try direct event tracking
                    if (function_exists('fluentCrmTrackEvent')) {
                        $result = fluentCrmTrackEvent([
                            'event_key' => $event_key,
                            'title' => $unique_title,
                            'email' => $user_email,
                            'value' => $event_value,
                            'provider' => 'academy_practice_hub'
                        ]);
                        
                        if ($result) {
                            $this->logger->info('FluentCRM Event triggered successfully via fluentCrmTrackEvent', array(
                                'event_key' => $event_key,
                                'user_email' => $user_email
                            ));
                            // Ensure email is saved directly to database
                            $this->ensure_event_email_saved($event_key, $unique_title, $event_value, $user_email);
                            return true;
                        }
                    }
                    
                } catch (Exception $e) {
                    $this->logger->error('FluentCRM API error', array('error' => $e->getMessage()));
                }
            }
            
            // Fallback: Use WordPress action hook
            do_action('fluent_crm/track_event_activity', [
                'event_key' => $event_key,
                'title' => $unique_title,
                'email' => $user_email,
                'value' => $event_value,
                'provider' => 'academy_practice_hub'
            ], true);
            
            // Ensure email is saved directly to database
            $this->ensure_event_email_saved($event_key, $unique_title, $event_value, $user_email);
            
            $this->logger->info('FluentCRM Event triggered via fallback action hook', array(
                'event_key' => $event_key,
                'user_email' => $user_email
            ));
            return true;
            
        } catch (Exception $e) {
            $this->logger->error('FluentCRM Event error', array('error' => $e->getMessage()));
            return false;
        }
    }
    
    /**
     * Ensure event email is saved directly to fc_event_tracking table
     * This ensures the email is always visible in the event logs
     */
    private function ensure_event_email_saved($event_key, $title, $value, $email) {
        global $wpdb;
        
        try {
            $table_name = $wpdb->prefix . 'fc_event_tracking';
            
            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
            if (!$table_exists) {
                $this->logger->debug('FluentCRM event tracking table does not exist', array('table' => $table_name));
                return false;
            }
            
            // Check if event was already inserted (within last 5 seconds to avoid duplicates)
            $recent_event = $wpdb->get_row($wpdb->prepare(
                "SELECT id, email FROM {$table_name} 
                WHERE event_key = %s 
                AND title = %s 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 5 SECOND)
                ORDER BY id DESC LIMIT 1",
                $event_key,
                $title
            ));
            
            if ($recent_event) {
                // Update email if it's missing or different
                if (empty($recent_event->email) || $recent_event->email !== $email) {
                    $wpdb->update(
                        $table_name,
                        array('email' => $email),
                        array('id' => $recent_event->id),
                        array('%s'),
                        array('%d')
                    );
                    $this->logger->debug('Updated event email in database', array(
                        'event_id' => $recent_event->id,
                        'email' => $email
                    ));
                }
            } else {
                // Insert new event directly if it doesn't exist
                $wpdb->insert(
                    $table_name,
                    array(
                        'event_key' => $event_key,
                        'title' => $title,
                        'value' => $value,
                        'email' => $email,
                        'provider' => 'academy_practice_hub',
                        'created_at' => current_time('mysql')
                    ),
                    array('%s', '%s', '%s', '%s', '%s', '%s')
                );
                $this->logger->debug('Inserted event directly into database', array(
                    'event_key' => $event_key,
                    'email' => $email
                ));
            }
            
            return true;
        } catch (Exception $e) {
            $this->logger->error('Error ensuring event email saved', array('error' => $e->getMessage()));
            return false;
        }
    }
    
    /**
     * Check comeback badge criteria
     * User must have returned after 7+ day break and completed 3 sessions in a week
     */
    private function check_comeback_badge($user_id, $sessions, $timezone = null) {
        if (count($sessions) < 3) {
            $this->logger->debug('Comeback badge check - insufficient sessions', array(
                'user_id' => $user_id,
                'session_count' => count($sessions),
                'required_minimum' => 3
            ));
            return false;
        }
        
        if (!($timezone instanceof DateTimeZone)) {
            $timezone = self::get_user_timezone($user_id);
        }
        
        // Sort sessions by date (newest first)
        usort($sessions, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Check if there's a gap of 7+ days in recent sessions
        $recent_sessions = array_slice($sessions, 0, 10); // Check last 10 sessions
        
        $debug_gaps = array();
        
        for ($i = 0; $i < count($recent_sessions) - 1; $i++) {
            $current_datetime = new DateTime($recent_sessions[$i]['created_at'], $timezone);
            $next_datetime = new DateTime($recent_sessions[$i + 1]['created_at'], $timezone);
            $days_diff = ($current_datetime->getTimestamp() - $next_datetime->getTimestamp()) / DAY_IN_SECONDS;
            
            $debug_gaps[] = array(
                'current_session' => $recent_sessions[$i]['created_at'],
                'next_session' => $recent_sessions[$i + 1]['created_at'],
                'days_diff' => $days_diff,
                'is_gap' => $days_diff >= 7
            );
            
            if ($days_diff >= 7) {
                // Found a 7+ day gap, check if user completed 3 sessions in the week after the gap
                $sessions_after_gap = array_slice($recent_sessions, 0, $i + 1);
                $week_after_gap = $current_datetime->getTimestamp() + (7 * DAY_IN_SECONDS);
                
                $sessions_in_week = 0;
                $week_sessions_debug = array();
                
                foreach ($sessions_after_gap as $session) {
                    $session_time = (new DateTime($session['created_at'], $timezone))->getTimestamp();
                    $is_in_week = $session_time <= $week_after_gap;
                    
                    $week_sessions_debug[] = array(
                        'session_date' => $session['created_at'],
                        'session_time' => $session_time,
                        'week_end' => $week_after_gap,
                        'is_in_week' => $is_in_week
                    );
                    
                    if ($is_in_week) {
                        $sessions_in_week++;
                    }
                }
                
                $result = $sessions_in_week >= 3;
                
                $this->logger->debug('Comeback badge check - gap found', array(
                    'user_id' => $user_id,
                    'gap_days' => $days_diff,
                    'sessions_in_week' => $sessions_in_week,
                    'required_sessions' => 3,
                    'badge_earned' => $result,
                    'user_timezone' => $timezone->getName(),
                    'week_sessions' => $week_sessions_debug
                ));
                
                return $result;
            }
        }
        
        $this->logger->debug('Comeback badge check - no qualifying gap', array(
            'user_id' => $user_id,
            'session_count' => count($sessions),
            'gaps_checked' => $debug_gaps
        ));
        
        return false;
    }
    
    /**
     * Check time of day badge criteria
     * criteria_value: 1 = early bird (5 AM - 8 AM), 2 = night owl (10 PM - 6 AM)
     */
    private function check_time_of_day_badge($user_id, $sessions, $criteria_value, $timezone = null) {
        $target_sessions = 0;
        $debug_sessions = array();
        
        if (!($timezone instanceof DateTimeZone)) {
            $timezone = self::get_user_timezone($user_id);
        }
        
        foreach ($sessions as $session) {
            // Parse the session time using WordPress timezone
            $session_datetime = new DateTime($session['created_at'], $timezone);
            $hour = (int)$session_datetime->format('H');
            
            $session_debug = array(
                'created_at' => $session['created_at'],
                'hour' => $hour,
                'criteria_value' => $criteria_value
            );
            
            if ($criteria_value == 1) { // Early bird (5 AM - 8 AM)
                if ($hour >= 5 && $hour < 8) {
                    $target_sessions++;
                    $session_debug['matches'] = true;
                } else {
                    $session_debug['matches'] = false;
                }
            } elseif ($criteria_value == 2) { // Night owl (10 PM - 6 AM)
                if ($hour >= 22 || $hour < 6) {
                    $target_sessions++;
                    $session_debug['matches'] = true;
                } else {
                    $session_debug['matches'] = false;
                }
            }
            
            $debug_sessions[] = $session_debug;
        }
        
        // Debug logging
        $this->logger->debug('Time of day badge check', array(
            'user_id' => $user_id,
            'criteria_value' => $criteria_value,
            'criteria_name' => $criteria_value == 1 ? 'Early Bird' : 'Night Owl',
            'target_sessions' => $target_sessions,
            'required_sessions' => 10,
            'user_timezone' => $timezone->getName(),
            'session_samples' => array_slice($debug_sessions, 0, 5)
        ));
        
        return $target_sessions >= 10; // Need 10 sessions in the target time period
    }
}
