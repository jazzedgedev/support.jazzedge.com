<?php
/**
 * Feature Flags Class for Academy AI Assistant
 * 
 * Controls visibility of AI features based on user whitelist or global setting
 * Security: Only allows access to authorized users
 */

if (!defined('ABSPATH')) {
    exit;
}

class AAA_Feature_Flags {
    
    /**
     * Check if current user can access AI features
     * 
     * @return bool True if user can access, false otherwise
     */
    public function user_can_access_ai() {
        // Get user ID - works in both regular and REST API contexts
        $user_id = get_current_user_id();
        if (!$user_id || $user_id === 0) {
            return false;
        }
        
        // Check if enabled for all users (production mode)
        $enable_for_all = get_option('aaa_enable_for_all', false);
        if ($enable_for_all) {
            return true;
        }
        
        // Check if user is in test whitelist
        $test_user_ids = get_option('aaa_test_user_ids', '');
        if (empty($test_user_ids)) {
            return false;
        }
        
        // Parse comma-separated user IDs
        $whitelist = array_map('trim', explode(',', $test_user_ids));
        $whitelist = array_map('absint', $whitelist); // Sanitize
        $whitelist = array_filter($whitelist); // Remove empty values
        
        return in_array($user_id, $whitelist, true);
    }
    
    /**
     * Check if debug mode is enabled
     * 
     * @return bool True if debug mode is enabled
     */
    public function is_debug_enabled() {
        return (bool) get_option('aaa_debug_enabled', false);
    }
    
    /**
     * Check if current user can see debug panel
     * 
     * @return bool True if user can see debug panel
     */
    public function user_can_see_debug_panel() {
        // Must have AI access and debug enabled
        if (!$this->user_can_access_ai() || !$this->is_debug_enabled()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get test user IDs from settings
     * 
     * @return array Array of user IDs
     */
    public function get_test_user_ids() {
        $test_user_ids = get_option('aaa_test_user_ids', '');
        if (empty($test_user_ids)) {
            return array();
        }
        
        $ids = array_map('trim', explode(',', $test_user_ids));
        $ids = array_map('absint', $ids);
        $ids = array_filter($ids);
        
        return array_values($ids);
    }
    
    /**
     * Validate and save test user IDs
     * Security: Validates user IDs exist
     * 
     * @param string $user_ids Comma-separated user IDs
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function save_test_user_ids($user_ids) {
        // Security: Only admins can save
        if (!current_user_can('manage_options')) {
            return new WP_Error('unauthorized', 'Only administrators can modify test user IDs');
        }
        
        // Parse and validate user IDs
        $ids = array_map('trim', explode(',', $user_ids));
        $ids = array_map('absint', $ids);
        $ids = array_filter($ids);
        
        // Validate that all user IDs exist
        foreach ($ids as $user_id) {
            if (!get_userdata($user_id)) {
                return new WP_Error('invalid_user', sprintf('User ID %d does not exist', $user_id));
            }
        }
        
        // Save as comma-separated string
        $value = implode(',', $ids);
        update_option('aaa_test_user_ids', $value);
        
        return true;
    }
    
    /**
     * Get feature flag status for admin display
     * 
     * @return array Status information
     */
    public function get_status() {
        return array(
            'enable_for_all' => (bool) get_option('aaa_enable_for_all', false),
            'test_user_ids' => $this->get_test_user_ids(),
            'test_user_count' => count($this->get_test_user_ids()),
            'debug_enabled' => $this->is_debug_enabled(),
            'current_user_can_access' => $this->user_can_access_ai(),
            'current_user_id' => get_current_user_id()
        );
    }
}

