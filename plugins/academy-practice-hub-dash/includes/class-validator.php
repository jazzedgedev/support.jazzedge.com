<?php
/**
 * Input Validator for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Validator {
    
    private static $instance = null;
    private $logger;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->logger = JPH_Logger::get_instance();
    }
    
    /**
     * Validate display name
     */
    public function validate_display_name($display_name) {
        if ($display_name === null || $display_name === '') {
            return true; // Empty is allowed (will use WordPress display name)
        }
        
        if (strlen($display_name) > 100) {
            return new WP_Error('invalid_display_name', 'Display name must be 100 characters or less', array('status' => 400));
        }
        
        // Check for potentially harmful content
        if (preg_match('/[<>"\']/', $display_name)) {
            return new WP_Error('invalid_display_name', 'Display name contains invalid characters', array('status' => 400));
        }
        
        return true;
    }
    
    /**
     * Validate practice session data
     */
    public function validate_practice_session($data) {
        $errors = array();
        
        // Validate duration
        if (!isset($data['duration_minutes']) || !is_numeric($data['duration_minutes'])) {
            $errors[] = 'Duration is required and must be numeric';
        } elseif ($data['duration_minutes'] < 1 || $data['duration_minutes'] > 480) { // Max 8 hours
            $errors[] = 'Duration must be between 1 and 480 minutes';
        }
        
        // Validate sentiment score
        if (isset($data['sentiment_score'])) {
            if (!is_numeric($data['sentiment_score']) || $data['sentiment_score'] < 1 || $data['sentiment_score'] > 5) {
                $errors[] = 'Sentiment score must be between 1 and 5';
            }
        }
        
        // Validate notes
        if (isset($data['notes']) && strlen($data['notes']) > 1000) {
            $errors[] = 'Notes must be 1000 characters or less';
        }
        
        // Validate practice item ID
        if (!isset($data['practice_item_id']) || !is_numeric($data['practice_item_id'])) {
            $errors[] = 'Practice item ID is required and must be numeric';
        }
        
        if (!empty($errors)) {
            return new WP_Error('validation_error', implode(', ', $errors), array('status' => 400));
        }
        
        return true;
    }
    
    /**
     * Validate practice item data
     */
    public function validate_practice_item($data) {
        $errors = array();
        
        // Validate name
        if (!isset($data['name']) || empty(trim($data['name']))) {
            $errors[] = 'Practice item name is required';
        } elseif (strlen($data['name']) > 100) {
            $errors[] = 'Practice item name must be 100 characters or less';
        }
        
        // Validate category
        if (isset($data['category']) && strlen($data['category']) > 50) {
            $errors[] = 'Category must be 50 characters or less';
        }
        
        // Validate description
        if (isset($data['description']) && strlen($data['description']) > 500) {
            $errors[] = 'Description must be 500 characters or less';
        }
        
        // Validate URL
        if (isset($data['url']) && !empty($data['url'])) {
            if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
                $errors[] = 'Invalid URL format';
            } elseif (strlen($data['url']) > 500) {
                $errors[] = 'URL must be 500 characters or less';
            }
        }
        
        if (!empty($errors)) {
            return new WP_Error('validation_error', implode(', ', $errors), array('status' => 400));
        }
        
        return true;
    }
    
    /**
     * Validate lesson favorite data
     */
    public function validate_lesson_favorite($data) {
        $errors = array();
        
        // Validate title
        if (!isset($data['title']) || empty(trim($data['title']))) {
            $errors[] = 'Lesson title is required';
        } elseif (strlen($data['title']) > 200) {
            $errors[] = 'Lesson title must be 200 characters or less';
        }
        
        // Validate URL
        if (!isset($data['url']) || empty($data['url'])) {
            $errors[] = 'Lesson URL is required';
        } elseif (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid URL format';
        } elseif (strlen($data['url']) > 500) {
            $errors[] = 'URL must be 500 characters or less';
        }
        
        // Validate category
        if (isset($data['category']) && strlen($data['category']) > 100) {
            $errors[] = 'Category must be 100 characters or less';
        }
        
        if (!empty($errors)) {
            return new WP_Error('validation_error', implode(', ', $errors), array('status' => 400));
        }
        
        return true;
    }
    
    /**
     * Validate badge data
     */
    public function validate_badge($data) {
        $errors = array();
        
        // Validate name
        if (!isset($data['name']) || empty(trim($data['name']))) {
            $errors[] = 'Badge name is required';
        } elseif (strlen($data['name']) > 100) {
            $errors[] = 'Badge name must be 100 characters or less';
        }
        
        // Validate description
        if (!isset($data['description']) || empty(trim($data['description']))) {
            $errors[] = 'Badge description is required';
        } elseif (strlen($data['description']) > 500) {
            $errors[] = 'Badge description must be 500 characters or less';
        }
        
        // Validate category
        if (!isset($data['category']) || empty(trim($data['category']))) {
            $errors[] = 'Badge category is required';
        } elseif (strlen($data['category']) > 50) {
            $errors[] = 'Badge category must be 50 characters or less';
        }
        
        // Validate criteria
        if (!isset($data['criteria_type']) || empty(trim($data['criteria_type']))) {
            $errors[] = 'Badge criteria type is required';
        }
        
        if (!isset($data['criteria_value']) || !is_numeric($data['criteria_value']) || $data['criteria_value'] < 1) {
            $errors[] = 'Badge criteria value must be a positive number';
        }
        
        // Validate rewards
        if (isset($data['xp_reward']) && (!is_numeric($data['xp_reward']) || $data['xp_reward'] < 0)) {
            $errors[] = 'XP reward must be a non-negative number';
        }
        
        if (isset($data['gem_reward']) && (!is_numeric($data['gem_reward']) || $data['gem_reward'] < 0)) {
            $errors[] = 'Gem reward must be a non-negative number';
        }
        
        if (!empty($errors)) {
            return new WP_Error('validation_error', implode(', ', $errors), array('status' => 400));
        }
        
        return true;
    }
    
    /**
     * Validate pagination parameters
     */
    public function validate_pagination($limit, $offset) {
        $errors = array();
        
        if (!is_numeric($limit) || $limit < 1 || $limit > 100) {
            $errors[] = 'Limit must be between 1 and 100';
        }
        
        if (!is_numeric($offset) || $offset < 0) {
            $errors[] = 'Offset must be 0 or greater';
        }
        
        if (!empty($errors)) {
            return new WP_Error('validation_error', implode(', ', $errors), array('status' => 400));
        }
        
        return true;
    }
    
    /**
     * Validate sort parameters
     */
    public function validate_sort($sort_by, $sort_order) {
        $allowed_sorts = array('total_xp', 'current_level', 'current_streak', 'total_sessions', 'total_minutes', 'badges_earned');
        $allowed_orders = array('asc', 'desc');
        
        if (!in_array($sort_by, $allowed_sorts)) {
            return new WP_Error('validation_error', 'Invalid sort field', array('status' => 400));
        }
        
        if (!in_array(strtolower($sort_order), $allowed_orders)) {
            return new WP_Error('validation_error', 'Invalid sort order', array('status' => 400));
        }
        
        return true;
    }
    
    /**
     * Sanitize text input
     */
    public function sanitize_text($text) {
        return sanitize_text_field($text);
    }
    
    /**
     * Sanitize textarea input
     */
    public function sanitize_textarea($text) {
        return sanitize_textarea_field($text);
    }
    
    /**
     * Sanitize URL
     */
    public function sanitize_url($url) {
        return esc_url_raw($url);
    }
    
    /**
     * Sanitize integer
     */
    public function sanitize_int($value) {
        return intval($value);
    }
    
    /**
     * Sanitize float
     */
    public function sanitize_float($value) {
        return floatval($value);
    }
    
    /**
     * Log validation attempt
     */
    public function log_validation($type, $data, $result) {
        if (is_wp_error($result)) {
            $this->logger->warning('Validation failed', array(
                'type' => $type,
                'data' => $data,
                'error' => $result->get_error_message()
            ));
        } else {
            $this->logger->debug('Validation passed', array('type' => $type));
        }
    }
}
