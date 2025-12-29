<?php
/**
 * Token Limits Class for Academy AI Assistant
 * 
 * Handles token usage limits based on membership levels
 * Security: Validates user membership and enforces limits
 */

if (!defined('ABSPATH')) {
    exit;
}

class AAA_Token_Limits {
    
    private $database;
    
    public function __construct() {
        $this->database = new AAA_Database();
    }
    
    /**
     * Get user's membership level
     * @param int $user_id User ID
     * @return string Membership level (free, essentials, studio, premier)
     */
    public function get_user_membership_level($user_id) {
        if (!class_exists('ALM_Membership_Checker')) {
            return 'free';
        }
        
        $membership_level = ALM_Membership_Checker::return_membership_level('nicename');
        
        // Convert to lowercase for consistency
        $level = strtolower($membership_level);
        
        // Map to our levels
        $level_map = array(
            'free' => 'free',
            'essentials' => 'essentials',
            'studio' => 'studio',
            'premier' => 'premier'
        );
        
        return isset($level_map[$level]) ? $level_map[$level] : 'free';
    }
    
    /**
     * Get token limit for a membership level
     * @param string $membership_level Membership level
     * @param string $period Period type: 'daily' or 'monthly'
     * @return int Token limit (0 = unlimited)
     */
    public function get_token_limit($membership_level, $period = 'daily') {
        $option_key = 'aaa_token_limit_' . $membership_level . '_' . $period;
        $limit = get_option($option_key, 0);
        
        return absint($limit);
    }
    
    /**
     * Check if user can use tokens
     * @param int $user_id User ID
     * @param int $tokens_to_use Tokens that will be used
     * @param string $period Period type: 'daily' or 'monthly'
     * @return array Result with 'allowed' boolean and 'message' string
     */
    public function check_token_limit($user_id, $tokens_to_use = 0, $period = 'daily') {
        if (!$user_id) {
            return array(
                'allowed' => false,
                'message' => 'User not authenticated.',
                'current_usage' => 0,
                'limit' => 0,
                'remaining' => 0
            );
        }
        
        // Get user's membership level
        $membership_level = $this->get_user_membership_level($user_id);
        
        // Get token limit for this membership level
        $limit = $this->get_token_limit($membership_level, $period);
        
        // If limit is 0, unlimited access
        if ($limit === 0) {
            return array(
                'allowed' => true,
                'message' => 'Unlimited access.',
                'current_usage' => 0,
                'limit' => 0,
                'remaining' => 0,
                'membership_level' => $membership_level
            );
        }
        
        // Get current usage
        if ($period === 'daily') {
            $current_usage = $this->database->get_token_usage($user_id);
        } else {
            $current_usage = $this->database->get_monthly_token_usage($user_id);
        }
        
        // Calculate remaining tokens
        $remaining = max(0, $limit - $current_usage);
        
        // Check if user can use the requested tokens
        if ($current_usage + $tokens_to_use > $limit) {
            $period_text = $period === 'daily' ? 'today' : 'this month';
            $membership_name = ucfirst($membership_level);
            
            return array(
                'allowed' => false,
                'message' => sprintf(
                    'You have reached your %s token limit for %s (%d tokens). Please upgrade your membership or try again tomorrow.',
                    $membership_name,
                    $period_text,
                    $limit
                ),
                'current_usage' => $current_usage,
                'limit' => $limit,
                'remaining' => $remaining,
                'membership_level' => $membership_level
            );
        }
        
        return array(
            'allowed' => true,
            'message' => 'Token usage allowed.',
            'current_usage' => $current_usage,
            'limit' => $limit,
            'remaining' => $remaining,
            'membership_level' => $membership_level
        );
    }
    
    /**
     * Record token usage after a request
     * @param int $user_id User ID
     * @param int $tokens_used Tokens used
     * @return bool Success
     */
    public function record_usage($user_id, $tokens_used) {
        if (!$user_id || $tokens_used <= 0) {
            return false;
        }
        
        $membership_level = $this->get_user_membership_level($user_id);
        
        return $this->database->record_token_usage($user_id, $tokens_used, $membership_level);
    }
    
    /**
     * Get usage statistics for a user
     * @param int $user_id User ID
     * @return array Statistics
     */
    public function get_usage_stats($user_id) {
        if (!$user_id) {
            return array();
        }
        
        $membership_level = $this->get_user_membership_level($user_id);
        $daily_limit = $this->get_token_limit($membership_level, 'daily');
        $monthly_limit = $this->get_token_limit($membership_level, 'monthly');
        
        $daily_usage = $this->database->get_token_usage($user_id);
        $monthly_usage = $this->database->get_monthly_token_usage($user_id);
        
        return array(
            'membership_level' => $membership_level,
            'daily_limit' => $daily_limit,
            'daily_usage' => $daily_usage,
            'daily_remaining' => $daily_limit > 0 ? max(0, $daily_limit - $daily_usage) : 0,
            'monthly_limit' => $monthly_limit,
            'monthly_usage' => $monthly_usage,
            'monthly_remaining' => $monthly_limit > 0 ? max(0, $monthly_limit - $monthly_usage) : 0,
            'stats' => $this->database->get_token_usage_stats($user_id, 30)
        );
    }
}

