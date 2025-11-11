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
        $current_shields = $stats->streak_shield_count ?? 0;
        
        // Check if they practiced today
        if ($last_practice_date === $today) {
            // Already practiced today, no streak change
            return array('streak_updated' => false, 'current_streak' => $current_streak);
        }
        
        // Check if yesterday was their last practice
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        if ($last_practice_date === $yesterday) {
            // Continue streak normally
            $new_streak = $current_streak + 1;
            $shield_used = false;
        } else if ($last_practice_date < $yesterday) {
            // Streak broken (more than 1 day gap) - check if we can use a shield
            if ($current_shields > 0) {
                // Use shield to maintain streak
                $new_streak = $current_streak + 1;
                $current_shields = $current_shields - 1;
                $shield_used = true;
            } else {
                // No shield available, reset streak
                $new_streak = 1;
                $shield_used = false;
            }
        } else {
            // Last practice was today or in the future (shouldn't happen)
            $new_streak = $current_streak;
            $shield_used = false;
        }
        
        // Update longest streak if needed
        $new_longest_streak = max($longest_streak, $new_streak);
        
        // Update database
        $wpdb->update(
            $table_name,
            array(
                'current_streak' => $new_streak,
                'longest_streak' => $new_longest_streak,
                'last_practice_date' => $today,
                'streak_shield_count' => $current_shields
            ),
            array('user_id' => $user_id),
            array('%d', '%d', '%s', '%d'),
            array('%d')
        );
        
        return array(
            'streak_updated' => true,
            'current_streak' => $new_streak,
            'longest_streak' => $new_longest_streak,
            'streak_continued' => ($last_practice_date === $yesterday),
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
                    $should_award = $this->check_comeback_badge($user_id, $sessions);
                    break;
                    
                case 'time_of_day':
                    // Check practice time patterns (early bird or night owl)
                    $should_award = $this->check_time_of_day_badge($user_id, $sessions, $criteria_value);
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
                            'value' => "Badge: {$badge['name']} - {$badge['description']}",
                            'provider' => 'academy_practice_hub'
                        ]);
                        
                        if ($result) {
                            $this->logger->info('FluentCRM Event triggered successfully via tracker', array(
                                'event_key' => $event_key,
                                'user_email' => $user_email
                            ));
                            return true;
                        }
                    }
                    
                    // Method 2: Try direct event tracking
                    if (function_exists('fluentCrmTrackEvent')) {
                        $result = fluentCrmTrackEvent([
                            'event_key' => $event_key,
                            'title' => $unique_title,
                            'email' => $user_email,
                            'value' => "Badge: {$badge['name']} - {$badge['description']}",
                            'provider' => 'academy_practice_hub'
                        ]);
                        
                        if ($result) {
                            $this->logger->info('FluentCRM Event triggered successfully via fluentCrmTrackEvent', array(
                                'event_key' => $event_key,
                                'user_email' => $user_email
                            ));
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
                'value' => "Badge: {$badge['name']} - {$badge['description']}",
                'provider' => 'academy_practice_hub'
            ], true);
            
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
     * Check comeback badge criteria
     * User must have returned after 7+ day break and completed 3 sessions in a week
     */
    private function check_comeback_badge($user_id, $sessions) {
        if (count($sessions) < 3) {
            $this->logger->debug('Comeback badge check - insufficient sessions', array(
                'user_id' => $user_id,
                'session_count' => count($sessions),
                'required_minimum' => 3
            ));
            return false;
        }
        
        // Get WordPress timezone for proper time calculation
        $wp_timezone = wp_timezone();
        
        // Sort sessions by date (newest first)
        usort($sessions, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Check if there's a gap of 7+ days in recent sessions
        $recent_sessions = array_slice($sessions, 0, 10); // Check last 10 sessions
        
        $debug_gaps = array();
        
        for ($i = 0; $i < count($recent_sessions) - 1; $i++) {
            $current_date = strtotime($recent_sessions[$i]['created_at']);
            $next_date = strtotime($recent_sessions[$i + 1]['created_at']);
            $days_diff = ($current_date - $next_date) / (24 * 60 * 60);
            
            $debug_gaps[] = array(
                'current_session' => $recent_sessions[$i]['created_at'],
                'next_session' => $recent_sessions[$i + 1]['created_at'],
                'days_diff' => $days_diff,
                'is_gap' => $days_diff >= 7
            );
            
            if ($days_diff >= 7) {
                // Found a 7+ day gap, check if user completed 3 sessions in the week after the gap
                $sessions_after_gap = array_slice($recent_sessions, 0, $i + 1);
                $week_after_gap = strtotime($recent_sessions[$i]['created_at']) + (7 * 24 * 60 * 60);
                
                $sessions_in_week = 0;
                $week_sessions_debug = array();
                
                foreach ($sessions_after_gap as $session) {
                    $session_time = strtotime($session['created_at']);
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
                    'wp_timezone' => $wp_timezone->getName(),
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
    private function check_time_of_day_badge($user_id, $sessions, $criteria_value) {
        $target_sessions = 0;
        $debug_sessions = array();
        
        // Get WordPress timezone for proper time calculation
        $wp_timezone = wp_timezone();
        $current_timezone = date_default_timezone_get();
        
        foreach ($sessions as $session) {
            // Parse the session time using WordPress timezone
            $session_datetime = new DateTime($session['created_at'], $wp_timezone);
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
            'wp_timezone' => $wp_timezone->getName(),
            'current_timezone' => $current_timezone,
            'session_samples' => array_slice($debug_sessions, 0, 5)
        ));
        
        return $target_sessions >= 10; // Need 10 sessions in the target time period
    }
    
    /**
     * Award JPC completion rewards
     * 
     * @param int $user_id User ID
     * @param int $step_id Step ID that was completed
     * @param int $curriculum_id Curriculum focus ID
     * @param int $keys_completed Number of keys completed for this focus
     * @param int $xp_earned XP earned for this completion
     * @param int $gems_earned Gems earned for this completion
     * @return array Result with rewards awarded
     */
    public static function award_jpc_completion($user_id, $step_id, $curriculum_id, $keys_completed, $xp_earned, $gems_earned) {
        global $wpdb;
        
        $result = array(
            'xp_awarded' => 0,
            'gems_awarded' => 0,
            'badges_earned' => array(),
            'level_up' => false
        );
        
        // Award XP
        if ($xp_earned > 0) {
            $user_stats = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}jph_user_stats WHERE user_id = %d",
                $user_id
            ), ARRAY_A);
            
            if (!$user_stats) {
                // Create user stats record if it doesn't exist
                $wpdb->insert(
                    $wpdb->prefix . 'jph_user_stats',
                    array(
                        'user_id' => $user_id,
                        'total_xp' => $xp_earned,
                        'current_level' => 1,
                        'current_streak' => 0,
                        'longest_streak' => 0,
                        'total_sessions' => 0,
                        'total_minutes' => 0,
                        'badges_earned' => 0,
                        'gems_balance' => 0,
                        'hearts_count' => 5,
                        'streak_shield_count' => 0,
                        'show_on_leaderboard' => 1,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ),
                    array('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s')
                );
                $result['xp_awarded'] = $xp_earned;
                error_log("JPCXP: Created new user stats record for user $user_id with $xp_earned XP");
            } else {
                $new_total_xp = $user_stats['total_xp'] + $xp_earned;
                $wpdb->update(
                    $wpdb->prefix . 'jph_user_stats',
                    array('total_xp' => $new_total_xp),
                    array('user_id' => $user_id),
                    array('%d'),
                    array('%d')
                );
                $result['xp_awarded'] = $xp_earned;
                error_log("JPCXP: Updated user $user_id XP from {$user_stats['total_xp']} to $new_total_xp");
                
                // Check for level up
                $old_level = $user_stats['current_level'];
                $new_level = floor(sqrt($new_total_xp / 100)) + 1;
                if ($new_level > $old_level) {
                    $wpdb->update(
                        $wpdb->prefix . 'jph_user_stats',
                        array('current_level' => $new_level),
                        array('user_id' => $user_id),
                        array('%d'),
                        array('%d')
                    );
                    $result['level_up'] = true;
                    error_log("JPCXP: User $user_id leveled up from $old_level to $new_level");
                }
            }
        }
        
        // Award gems
        if ($gems_earned > 0) {
            $user_stats = $wpdb->get_row($wpdb->prepare(
                "SELECT gems_balance FROM {$wpdb->prefix}jph_user_stats WHERE user_id = %d",
                $user_id
            ), ARRAY_A);
            
            if (!$user_stats) {
                // Create user stats record if it doesn't exist
                $wpdb->insert(
                    $wpdb->prefix . 'jph_user_stats',
                    array(
                        'user_id' => $user_id,
                        'total_xp' => 0,
                        'current_level' => 1,
                        'current_streak' => 0,
                        'longest_streak' => 0,
                        'total_sessions' => 0,
                        'total_minutes' => 0,
                        'badges_earned' => 0,
                        'gems_balance' => $gems_earned,
                        'hearts_count' => 5,
                        'streak_shield_count' => 0,
                        'show_on_leaderboard' => 1,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ),
                    array('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s')
                );
                
                // Log gem transaction
                $wpdb->insert(
                    $wpdb->prefix . 'jph_gems_transactions',
                    array(
                        'user_id' => $user_id,
                        'transaction_type' => 'earned',
                        'amount' => $gems_earned,
                        'source' => 'jpc_focus_completion',
                        'description' => "Completed all 12 keys for JPC focus #{$curriculum_id}",
                        'balance_after' => $gems_earned,
                        'created_at' => current_time('mysql')
                    ),
                    array('%d', '%s', '%d', '%s', '%s', '%d', '%s')
                );
                
                $result['gems_awarded'] = $gems_earned;
                error_log("JPCXP: Created new user stats record for user $user_id with $gems_earned gems");
            } else {
                $new_balance = $user_stats['gems_balance'] + $gems_earned;
                $wpdb->update(
                    $wpdb->prefix . 'jph_user_stats',
                    array('gems_balance' => $new_balance),
                    array('user_id' => $user_id),
                    array('%d'),
                    array('%d')
                );
                
                // Log gem transaction
                $wpdb->insert(
                    $wpdb->prefix . 'jph_gems_transactions',
                    array(
                        'user_id' => $user_id,
                        'transaction_type' => 'earned',
                        'amount' => $gems_earned,
                        'source' => 'jpc_focus_completion',
                        'description' => "Completed all 12 keys for JPC focus #{$curriculum_id}",
                        'balance_after' => $new_balance,
                        'created_at' => current_time('mysql')
                    ),
                    array('%d', '%s', '%d', '%s', '%s', '%d', '%s')
                );
                
                $result['gems_awarded'] = $gems_earned;
                error_log("JPCXP: Updated user $user_id gems balance to $new_balance (+$gems_earned)");
            }
        }
        
        // Check for JPC-specific badges
        $badges_earned = self::check_jpc_badges($user_id, $step_id, $curriculum_id, $keys_completed);
        $result['badges_earned'] = $badges_earned;
        
        return $result;
    }
    
    /**
     * Check for JPC-specific badges
     * 
     * @param int $user_id User ID
     * @param int $step_id Step ID
     * @param int $curriculum_id Curriculum focus ID
     * @param int $keys_completed Number of keys completed
     * @return array Badges earned
     */
    private static function check_jpc_badges($user_id, $step_id, $curriculum_id, $keys_completed) {
        global $wpdb;
        
        $badges_earned = array();
        
        // First Scale badge - Complete first key of first focus
        if ($curriculum_id == 1 && $keys_completed == 1) {
            $badge_key = 'jpc_first_scale';
            if (!self::user_has_badge($user_id, $badge_key)) {
                self::award_badge($user_id, $badge_key);
                $badges_earned[] = $badge_key;
            }
        }
        
        // Key Master badge - Complete all 12 keys in any focus
        if ($keys_completed == 12) {
            $badge_key = 'jpc_key_master';
            if (!self::user_has_badge($user_id, $badge_key)) {
                self::award_badge($user_id, $badge_key);
                $badges_earned[] = $badge_key;
            }
        }
        
        // Technique Expert badge - Complete 10 full focuses (120 keys)
        $total_completed_focuses = self::count_completed_focuses($user_id);
        if ($total_completed_focuses >= 10) {
            $badge_key = 'jpc_technique_expert';
            if (!self::user_has_badge($user_id, $badge_key)) {
                self::award_badge($user_id, $badge_key);
                $badges_earned[] = $badge_key;
            }
        }
        
        // JPC Graduate badge - Complete all 41 focuses
        if ($total_completed_focuses >= 41) {
            $badge_key = 'jpc_graduate';
            if (!self::user_has_badge($user_id, $badge_key)) {
                self::award_badge($user_id, $badge_key);
                $badges_earned[] = $badge_key;
            }
        }
        
        return $badges_earned;
    }
    
    /**
     * Check if user has a specific badge
     */
    private static function user_has_badge($user_id, $badge_key) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}jph_user_badges 
             WHERE user_id = %d AND badge_key = %s",
            $user_id, $badge_key
        ));
        
        return $count > 0;
    }
    
    /**
     * Award a badge to a user
     */
    private static function award_badge($user_id, $badge_key) {
        global $wpdb;
        
        // Get badge details
        $badge = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}jph_badges WHERE badge_key = %s",
            $badge_key
        ), ARRAY_A);
        
        if ($badge) {
            // Award the badge
            $wpdb->insert(
                $wpdb->prefix . 'jph_user_badges',
                array(
                    'user_id' => $user_id,
                    'badge_key' => $badge_key,
                    'earned_at' => current_time('mysql'),
                    'earned_date' => current_time('Y-m-d')
                ),
                array('%d', '%s', '%s', '%s')
            );
            
            // Award badge rewards
            if ($badge['xp_reward'] > 0) {
                $user_stats = $wpdb->get_row($wpdb->prepare(
                    "SELECT total_xp FROM {$wpdb->prefix}jph_user_stats WHERE user_id = %d",
                    $user_id
                ), ARRAY_A);
                
                if ($user_stats) {
                    $new_total_xp = $user_stats['total_xp'] + $badge['xp_reward'];
                    $wpdb->update(
                        $wpdb->prefix . 'jph_user_stats',
                        array('total_xp' => $new_total_xp),
                        array('user_id' => $user_id),
                        array('%d'),
                        array('%d')
                    );
                }
            }
            
            if ($badge['gem_reward'] > 0) {
                $user_stats = $wpdb->get_row($wpdb->prepare(
                    "SELECT gems_balance FROM {$wpdb->prefix}jph_user_stats WHERE user_id = %d",
                    $user_id
                ), ARRAY_A);
                
                if ($user_stats) {
                    $new_balance = $user_stats['gems_balance'] + $badge['gem_reward'];
                    $wpdb->update(
                        $wpdb->prefix . 'jph_user_stats',
                        array('gems_balance' => $new_balance),
                        array('user_id' => $user_id),
                        array('%d'),
                        array('%d')
                    );
                    
                    // Log gem transaction
                    $wpdb->insert(
                        $wpdb->prefix . 'jph_gems_transactions',
                        array(
                            'user_id' => $user_id,
                            'transaction_type' => 'earned',
                            'amount' => $badge['gem_reward'],
                            'source' => 'badge_reward',
                            'description' => "Badge reward: {$badge['name']}",
                            'balance_after' => $new_balance,
                            'created_at' => current_time('mysql')
                        ),
                        array('%d', '%s', '%d', '%s', '%s', '%d', '%s')
                    );
                }
            }
            
            // Update badges_earned count
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}jph_user_stats 
                 SET badges_earned = badges_earned + 1 
                 WHERE user_id = %d",
                $user_id
            ));
        }
    }
    
    /**
     * Count completed focuses for a user
     */
    private static function count_completed_focuses($user_id) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}jph_jpc_user_progress 
             WHERE user_id = %d 
             AND step_1 IS NOT NULL 
             AND step_2 IS NOT NULL 
             AND step_3 IS NOT NULL 
             AND step_4 IS NOT NULL 
             AND step_5 IS NOT NULL 
             AND step_6 IS NOT NULL 
             AND step_7 IS NOT NULL 
             AND step_8 IS NOT NULL 
             AND step_9 IS NOT NULL 
             AND step_10 IS NOT NULL 
             AND step_11 IS NOT NULL 
             AND step_12 IS NOT NULL",
            $user_id
        ));
        
        return intval($count);
    }
}
