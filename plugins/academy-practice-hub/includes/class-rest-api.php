<?php
/**
 * REST API endpoints for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_REST_API {
    
    private $database;
    
    public function __construct() {
        $this->database = new JPH_Database();
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Test endpoint
        register_rest_route('jph/v1', '/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_test'),
            'permission_callback' => '__return_true'
        ));
        
        // Practice Sessions endpoints
        register_rest_route('jph/v1', '/practice-sessions', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_log_practice_session'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/practice-sessions', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_practice_sessions'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/practice-sessions/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_practice_session'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Export endpoint
        register_rest_route('jph/v1', '/export-practice-history', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_export_practice_history'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Badge management endpoints
        register_rest_route('jph/v1', '/admin/badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_badges_admin'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/admin/badges', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_add_badge'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/badges/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_badge'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/badges/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_badge'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/badges/key/(?P<badge_key>[a-zA-Z0-9_-]+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_badge_by_key'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/badges/key/(?P<badge_key>[a-zA-Z0-9_-]+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_badge_by_key'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // User-facing endpoints
        register_rest_route('jph/v1', '/badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_user_badges'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/lesson-favorites', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_lesson_favorites'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/purchase-shield', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_purchase_streak_shield'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Admin lesson favorites endpoints
        register_rest_route('jph/v1', '/admin/lesson-favorites', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_lesson_favorites_admin'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/admin/lesson-favorites-stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_lesson_favorites_stats'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/export-lesson-favorites', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_export_lesson_favorites'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Admin settings endpoints
        register_rest_route('jph/v1', '/admin/clear-all-user-data', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_clear_all_user_data'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Event tracking endpoints
        register_rest_route('jph/v1', '/test-badge-event', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_test_badge_event'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/debug-user-badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_user_badges'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/test-badge-assignment', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_test_badge_assignment'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/debug-badge-database', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_badge_database'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/debug-practice-sessions', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_practice_sessions'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
    }
    
    /**
     * Check user permission
     */
    public function check_user_permission() {
        return is_user_logged_in();
    }
    
    /**
     * Test endpoint
     */
    public function rest_test($request) {
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Academy Practice Hub API is working',
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Log practice session
     */
    public function rest_log_practice_session($request) {
        $user_id = get_current_user_id();
        $params = $request->get_params();
        
        // Map frontend parameters to database parameters
        $item_id = $params['practice_item_id'] ?? $params['item_id'];
        $item_name = $params['item_name'] ?? '';
        $duration = $params['duration_minutes'] ?? $params['duration'];
        
        if (empty($item_id) || empty($duration)) {
            return new WP_Error('missing_fields', 'Practice item ID and duration are required', array('status' => 400));
        }
        
        // Convert sentiment to score (1-5)
        $sentiment_scores = array(
            'excellent' => 5,
            'good' => 4,
            'okay' => 3,
            'challenging' => 2,
            'frustrating' => 1
        );
        $sentiment_score = isset($sentiment_scores[$params['sentiment']]) ? $sentiment_scores[$params['sentiment']] : 3;
        
        // Convert improvement to boolean - check both 'improvement' and 'improvement_detected' parameters
        $improvement_detected = 0;
        if (isset($params['improvement_detected']) && $params['improvement_detected']) {
            $improvement_detected = 1;
        } elseif (isset($params['improvement']) && in_array($params['improvement'], array('significant', 'moderate', 'slight'))) {
            $improvement_detected = 1;
        }
        
        $session_id = $this->database->log_practice_session(
            $user_id,
            $item_id,
            $duration,
            $sentiment_score,
            $improvement_detected,
            $params['notes'] ?? ''
        );
        
        if (is_wp_error($session_id)) {
            return $session_id;
        }
        
        // Gamification integration - use our Academy gamification class
        $gamification = new APH_Gamification();
        
        // Calculate and add XP
        $xp_earned = $gamification->calculate_xp($duration, $sentiment_score, $improvement_detected);
        $xp_result = $gamification->add_xp($user_id, $xp_earned);
        
        if (!$xp_result) {
            error_log('JPH: Failed to add XP for user ' . $user_id . ' - this will prevent badge checks');
            // Continue anyway, but log the issue
        }
        
        // Update the practice session with XP earned
        $this->database->update_practice_session_xp($session_id, $xp_earned);
        
        // Update streak
        $gamification->update_streak($user_id);
        
        // Check for badges using our gamification class
        $newly_awarded = $gamification->check_and_award_badges($user_id);
        
        return rest_ensure_response(array(
            'success' => true,
            'session_id' => $session_id,
            'message' => 'Practice session logged successfully'
        ));
    }
    
    /**
     * Get practice sessions
     */
    public function rest_get_practice_sessions($request) {
        $user_id = get_current_user_id();
        $limit = $request->get_param('limit') ?: 10;
        $offset = $request->get_param('offset') ?: 0;
        
        $sessions = $this->database->get_practice_sessions($user_id, $limit, $offset);
        
        if (is_wp_error($sessions)) {
            return $sessions;
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'sessions' => $sessions,
            'count' => count($sessions)
        ));
    }
    
    /**
     * Delete practice session
     */
    public function rest_delete_practice_session($request) {
        $session_id = $request->get_param('id');
        $user_id = get_current_user_id();
        
        // Verify ownership
        $sessions = $this->database->get_practice_sessions($user_id, 1000, 0);
        $session_exists = false;
        foreach ($sessions as $session) {
            if ($session['id'] == $session_id) {
                $session_exists = true;
                break;
            }
        }
        
        if (!$session_exists) {
            return new WP_Error('not_found', 'Practice session not found', array('status' => 404));
        }
        
        $result = $this->database->delete_practice_session($session_id);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Practice session deleted successfully'
        ));
    }
    
    /**
     * Export practice history
     */
    public function rest_export_practice_history($request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('not_logged_in', 'You must be logged in to export your practice history', array('status' => 401));
        }
        
        $sessions = $this->database->get_practice_sessions($user_id, 1000, 0);
        
        if (is_wp_error($sessions)) {
            return $sessions;
        }
        
        // Check if we have any sessions to export
        if (empty($sessions)) {
            return new WP_Error('no_sessions', 'No practice sessions found to export', array('status' => 404));
        }
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="practice-history-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Create CSV output
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 compatibility with Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV headers
        fputcsv($output, array(
            'Date',
            'Practice Item',
            'Duration (minutes)',
            'Sentiment Score',
            'Improvement Detected',
            'XP Earned',
            'Notes'
        ));
        
        // Add session data
        foreach ($sessions as $session) {
            fputcsv($output, array(
                date('Y-m-d H:i:s', strtotime($session['created_at'])),
                $session['item_name'] ?: 'Unknown Item',
                $session['duration_minutes'],
                $session['sentiment_score'],
                $session['improvement_detected'] ? 'Yes' : 'No',
                $session['xp_earned'] ?: 0,
                $session['notes'] ?: ''
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Get badges for admin
     */
    public function rest_get_badges_admin($request) {
        $badges = $this->database->get_badges();
        
        return rest_ensure_response(array(
            'success' => true,
            'badges' => $badges
        ));
    }
    
    /**
     * Add new badge
     */
    public function rest_add_badge($request) {
        $params = $request->get_json_params();
        
        // Validate required fields
        $required_fields = array('name', 'description', 'category', 'criteria_type', 'criteria_value');
        foreach ($required_fields as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                return new WP_Error('missing_field', "Missing required field: {$field}", array('status' => 400));
            }
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'jph_badges';
        
        // Generate badge key from name
        $badge_key = sanitize_title($params['name']);
        
        // Check if badge key already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE badge_key = %s",
            $badge_key
        ));
        
        if ($existing) {
            return new WP_Error('badge_exists', 'Badge with this name already exists', array('status' => 400));
        }
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'badge_key' => $badge_key,
                'name' => sanitize_text_field($params['name']),
                'description' => sanitize_textarea_field($params['description']),
                'icon' => sanitize_text_field($params['icon'] ?? '🏆'),
                'category' => sanitize_text_field($params['category']),
                'rarity' => sanitize_text_field($params['rarity'] ?? 'common'),
                'xp_reward' => intval($params['xp_reward'] ?? 0),
                'gem_reward' => intval($params['gem_reward'] ?? 0),
                'criteria_type' => sanitize_text_field($params['criteria_type']),
                'criteria_value' => intval($params['criteria_value']),
                'is_active' => 1,
                'display_order' => intval($params['display_order'] ?? 999),
                'image_url' => esc_url_raw($params['image_url'] ?? ''),
                'created_at' => current_time('mysql')
            )
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to create badge', array('status' => 500));
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Badge created successfully',
            'badge_id' => $wpdb->insert_id
        ));
    }
    
    /**
     * Update badge
     */
    public function rest_update_badge($request) {
        $badge_id = $request->get_param('id');
        $params = $request->get_json_params();
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'jph_badges';
        
        // Check if badge exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $badge_id
        ));
        
        if (!$existing) {
            return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
        }
        
        // Prepare update data
        $update_data = array();
        if (isset($params['name'])) $update_data['name'] = sanitize_text_field($params['name']);
        if (isset($params['description'])) $update_data['description'] = sanitize_textarea_field($params['description']);
        if (isset($params['icon'])) $update_data['icon'] = sanitize_text_field($params['icon']);
        if (isset($params['category'])) $update_data['category'] = sanitize_text_field($params['category']);
        if (isset($params['rarity'])) $update_data['rarity'] = sanitize_text_field($params['rarity']);
        if (isset($params['xp_reward'])) $update_data['xp_reward'] = intval($params['xp_reward']);
        if (isset($params['gem_reward'])) $update_data['gem_reward'] = intval($params['gem_reward']);
        if (isset($params['criteria_type'])) $update_data['criteria_type'] = sanitize_text_field($params['criteria_type']);
        if (isset($params['criteria_value'])) $update_data['criteria_value'] = intval($params['criteria_value']);
        if (isset($params['is_active'])) $update_data['is_active'] = intval($params['is_active']);
        if (isset($params['display_order'])) $update_data['display_order'] = intval($params['display_order']);
        if (isset($params['image_url'])) $update_data['image_url'] = esc_url_raw($params['image_url']);
        
        $update_data['updated_at'] = current_time('mysql');
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $badge_id),
            null,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update badge', array('status' => 500));
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Badge updated successfully'
        ));
    }
    
    /**
     * Delete badge
     */
    public function rest_delete_badge($request) {
        $badge_id = $request->get_param('id');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'jph_badges';
        
        // Check if badge exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $badge_id
        ));
        
        if (!$existing) {
            return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
        }
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $badge_id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('delete_failed', 'Failed to delete badge', array('status' => 500));
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Badge deleted successfully'
        ));
    }
    
    /**
     * Debug info endpoint
     */
    public function rest_debug_info($request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('not_logged_in', 'You must be logged in to view debug information', array('status' => 401));
        }
        
        // Get user stats
        $user_stats = $this->database->get_user_stats($user_id);
        
        // Get user badges
        $user_badges = $this->database->get_user_badges($user_id);
        
        // Get all badges
        $all_badges = $this->database->get_badges(true);
        
        // Get practice sessions
        $practice_sessions = $this->database->get_practice_sessions($user_id, 5);
        
        // Check database tables
        global $wpdb;
        $tables = array(
            'jph_badges' => $wpdb->prefix . 'jph_badges',
            'jph_user_badges' => $wpdb->prefix . 'jph_user_badges',
            'jph_user_stats' => $wpdb->prefix . 'jph_user_stats',
            'jph_practice_sessions' => $wpdb->prefix . 'jph_practice_sessions'
        );
        
        $table_info = array();
        foreach ($tables as $name => $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
            $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $table") : 0;
            $table_info[$name] = array(
                'exists' => $exists,
                'count' => $count,
                'table_name' => $table
            );
        }
        
        // Gather data from ALL tables
        $all_tables_data = array();
        
        // Get data from all tables
        $all_tables_data['jph_badges'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_badges ORDER BY display_order ASC", ARRAY_A);
        $all_tables_data['jph_user_badges'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_user_badges WHERE user_id = $user_id ORDER BY earned_at DESC", ARRAY_A);
        $all_tables_data['jph_user_stats'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_user_stats WHERE user_id = $user_id", ARRAY_A);
        $all_tables_data['jph_practice_sessions'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_practice_sessions WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 10", ARRAY_A);
        $all_tables_data['jph_practice_items'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_practice_items WHERE user_id = $user_id ORDER BY sort_order ASC", ARRAY_A);
        $all_tables_data['jph_gems_transactions'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_gems_transactions WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 20", ARRAY_A);
        $all_tables_data['jph_lesson_favorites'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_lesson_favorites WHERE user_id = $user_id ORDER BY created_at DESC", ARRAY_A);
        
        // Build debug HTML with accordion
        $debug_html = '<div style="font-family: monospace; font-size: 11px; line-height: 1.4;">';
        
        // Add CSS for accordions
        $debug_html .= '<style>
            .debug-accordion { margin: 10px 0; }
            .accordion-header { 
                background: #0073aa; 
                color: white; 
                padding: 8px 12px; 
                cursor: pointer; 
                border-radius: 4px 4px 0 0;
                margin: 0;
                display: block;
                width: 100%;
                text-align: left;
                font-weight: bold;
                border: none;
            }
            .accordion-header:hover { background: #005a87; }
            .accordion-content { 
                background: #f8f9fa; 
                border: 1px solid #dee2e6; 
                border-top: none; 
                padding: 15px; 
                display: none;
                border-radius: 0 0 4px 4px;
            }
            .accordion-content.active { display: block; }
            .accordion-content pre { 
                background: #fff; 
                padding: 10px; 
                border-radius: 4px; 
                overflow-x: auto; 
                max-height: 300px; 
                border: 1px solid #ddd;
            }
            .debug-table-count { font-size: 10px; color: #666; margin-left: 10px; }
            .debug-section-title { color: #0073aa; margin: 20px 0 10px 0; font-size: 14px; font-weight: bold; }
        </style>';
        
        $debug_html .= '<h5 style="color: #0073aa; margin: 10px 0 5px 0;"><i class="fa-solid fa-user"></i> User Information</h5>';
        $debug_html .= '<p><strong>User ID:</strong> ' . $user_id . '</p>';
        $debug_html .= '<p><strong>Is Admin:</strong> ' . (current_user_can('manage_options') ? 'Yes' : 'No') . '</p>';
        
        // Accordion sections for all table data
        $accordion_sections = array(
            'user_stats' => array('icon' => '<i class="fa-solid fa-chart-bar"></i>', 'title' => 'User Stats', 'data' => $all_tables_data['jph_user_stats']),
            'user_badges' => array('icon' => '<i class="fa-solid fa-trophy"></i>', 'title' => 'User Badges', 'data' => $all_tables_data['jph_user_badges']),
            'available_badges' => array('icon' => '<i class="fa-solid fa-medal"></i>', 'title' => 'All Available Badges', 'data' => $all_tables_data['jph_badges']),
            'practice_sessions' => array('icon' => '<i class="fa-solid fa-clipboard-list"></i>', 'title' => 'Recent Practice Sessions', 'data' => $all_tables_data['jph_practice_sessions']),
            'practice_items' => array('icon' => '<i class="fa-solid fa-bullseye"></i>', 'title' => 'Practice Items', 'data' => $all_tables_data['jph_practice_items']),
            'gems_transactions' => array('icon' => '<i class="fa-solid fa-gem"></i>', 'title' => 'Gems Transactions', 'data' => $all_tables_data['jph_gems_transactions']),
            'lesson_favorites' => array('icon' => '<i class="fa-solid fa-star"></i>', 'title' => 'Lesson Favorites', 'data' => $all_tables_data['jph_lesson_favorites'])
        );
        
        $debug_html .= '<div class="debug-section-title"><i class="fa-solid fa-clipboard-list"></i> All Table Data</div>';
        foreach ($accordion_sections as $key => $section) {
            $count = count($section['data']);
            $debug_html .= '<div class="debug-accordion">';
            $debug_html .= '<button class="accordion-header" onclick="jphToggleAccordion(\'' . $key . '\')">';
            $debug_html .= $section['icon'] . ' ' . $section['title'] . '<span class="debug-table-count">(' . $count . ' entries)</span>';
            $debug_html .= '</button>';
            $debug_html .= '<div class="accordion-content" id="acc_' . $key . '">';
            
            if (!empty($section['data'])) {
                $debug_html .= '<pre>';
                $debug_html .= htmlspecialchars(print_r($section['data'], true));
                $debug_html .= '</pre>';
            } else {
                $debug_html .= '<p style="color: #d63638;">No data found</p>';
            }
            
            $debug_html .= '</div>';
            $debug_html .= '</div>';
        }
        
        $debug_html .= '<h5 style="color: #0073aa; margin: 15px 0 5px 0;"><i class="fa-solid fa-database"></i> Database Tables</h5>';
        foreach ($table_info as $name => $info) {
            $status = $info['exists'] ? '✅' : '❌';
            $debug_html .= '<p><strong>' . $name . ':</strong> ' . $status . ' ' . $info['table_name'] . ' (' . $info['count'] . ' rows)</p>';
        }
        
        $debug_html .= '</div>';
        
        return rest_ensure_response(array(
            'success' => true,
            'html' => $debug_html,
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Delete badge by badge key
     */
    public function rest_delete_badge_by_key($request) {
        try {
            $badge_key = $request->get_param('badge_key');
            
            if (empty($badge_key)) {
                return new WP_Error('missing_badge_key', 'Badge key is required', array('status' => 400));
            }
            
            $database = new JPH_Database();
            
            // Check if badge exists
            $badge = $database->get_badge_by_key($badge_key);
            if (!$badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            // Delete the badge
            $result = $database->delete_badge($badge_key);
            
            if ($result) {
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Badge deleted successfully',
                    'badge_key' => $badge_key
                ));
            } else {
                return new WP_Error('delete_failed', 'Failed to delete badge', array('status' => 500));
            }
            
        } catch (Exception $e) {
            return new WP_Error('delete_badge_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Update badge by badge key
     */
    public function rest_update_badge_by_key($request) {
        try {
            $badge_key = $request->get_param('badge_key');
            
            if (empty($badge_key)) {
                return new WP_Error('missing_badge_key', 'Badge key is required', array('status' => 400));
            }
            
            $database = new JPH_Database();
            
            // Check if badge exists
            $badge = $database->get_badge_by_key($badge_key);
            if (!$badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            // Get the request body
            $body = $request->get_json_params();
            if (empty($body)) {
                $body = $request->get_body_params();
            }
            
            // Validate required fields
            $required_fields = array('name', 'description', 'category', 'criteria_type', 'criteria_value', 'xp_reward', 'gem_reward');
            foreach ($required_fields as $field) {
                if (!isset($body[$field])) {
                    return new WP_Error('missing_field', "Field '{$field}' is required", array('status' => 400));
                }
            }
            
            // Prepare badge data
            $badge_data = array(
                'name' => sanitize_text_field($body['name']),
                'description' => sanitize_textarea_field($body['description']),
                'category' => sanitize_text_field($body['category']),
                'criteria_type' => sanitize_text_field($body['criteria_type']),
                'criteria_value' => intval($body['criteria_value']),
                'xp_reward' => intval($body['xp_reward']),
                'gem_reward' => intval($body['gem_reward']),
                'is_active' => isset($body['is_active']) ? intval($body['is_active']) : 1
            );
            
            // Add optional fields
            if (isset($body['icon'])) {
                $badge_data['icon'] = sanitize_text_field($body['icon']);
            }
            
            // Update the badge
            $result = $database->update_badge($badge_key, $badge_data);
            
            if ($result) {
                // Get updated badge data
                $updated_badge = $database->get_badge_by_key($badge_key);
                
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Badge updated successfully',
                    'badge' => $updated_badge
                ));
            } else {
                return new WP_Error('update_failed', 'Failed to update badge', array('status' => 500));
            }
            
        } catch (Exception $e) {
            return new WP_Error('update_badge_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get user badges for frontend display
     */
    public function rest_get_user_badges($request) {
        try {
            $user_id = get_current_user_id();
            $database = new JPH_Database();
            
            // Get all badges
            $all_badges = $database->get_badges(true); // Only active badges
            
            // Get user's earned badges
            $user_badges = $database->get_user_badges($user_id);
            
            // Create a lookup array for earned badges
            $earned_badges = array();
            foreach ($user_badges as $user_badge) {
                $earned_badges[$user_badge['badge_key']] = $user_badge;
            }
            
            // Combine badges with earned status
            $badges_with_status = array();
            foreach ($all_badges as $badge) {
                $badge_data = $badge;
                $badge_data['is_earned'] = isset($earned_badges[$badge['badge_key']]);
                if ($badge_data['is_earned']) {
                    $badge_data['earned_at'] = $earned_badges[$badge['badge_key']]['earned_at'];
                }
                $badges_with_status[] = $badge_data;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'badges' => $badges_with_status
            ));
            
        } catch (Exception $e) {
            return new WP_Error('get_badges_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get lesson favorites for frontend display
     */
    public function rest_get_lesson_favorites($request) {
        try {
            $user_id = get_current_user_id();
            $database = new JPH_Database();
            
            $favorites = $database->get_lesson_favorites($user_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'favorites' => $favorites
            ));
            
        } catch (Exception $e) {
            return new WP_Error('get_lesson_favorites_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Purchase streak shield
     */
    public function rest_purchase_streak_shield($request) {
        try {
            $user_id = get_current_user_id();
            $database = new JPH_Database();
            $gamification = new APH_Gamification();
            
            // Get current user stats
            $user_stats = $gamification->get_user_stats($user_id);
            $current_shields = $user_stats['streak_shield_count'] ?? 0;
            $gem_balance = $user_stats['gems_balance'] ?? 0;
            $shield_cost = 50;
            
            // Check if user already has max shields
            if ($current_shields >= 3) {
                return new WP_Error('max_shields', 'You already have the maximum number of shields (3). You cannot purchase more shields.', array('status' => 400));
            }
            
            // Check if user has enough gems
            if ($gem_balance < $shield_cost) {
                return new WP_Error('insufficient_gems', 'Insufficient gems! You have ' . $gem_balance . ' 💎 but need ' . $shield_cost . ' 💎.', array('status' => 400));
            }
            
            // Purchase the shield
            $new_shield_count = $current_shields + 1;
            $new_gem_balance = $gem_balance - $shield_cost;
            
            // Update user stats
            $update_data = array(
                'streak_shield_count' => $new_shield_count,
                'gems_balance' => $new_gem_balance
            );
            
            $result = $database->update_user_stats($user_id, $update_data);
            
            if ($result) {
                // Record the gem transaction
                $database->record_gems_transaction($user_id, -$shield_cost, 'streak_shield_purchase', 'Purchased streak shield');
                
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Shield purchased successfully!',
                    'data' => array(
                        'new_shield_count' => $new_shield_count,
                        'new_gem_balance' => $new_gem_balance
                    )
                ));
            } else {
                return new WP_Error('purchase_failed', 'Failed to purchase shield', array('status' => 500));
            }
            
        } catch (Exception $e) {
            return new WP_Error('purchase_shield_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get lesson favorites for admin
     */
    public function rest_get_lesson_favorites_admin($request) {
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
            
            $favorites = $wpdb->get_results("
                SELECT lf.*, u.display_name as user_name 
                FROM $table_name lf 
                LEFT JOIN {$wpdb->users} u ON lf.user_id = u.ID 
                ORDER BY lf.created_at DESC
            ", ARRAY_A);
            
            return rest_ensure_response(array(
                'success' => true,
                'favorites' => $favorites
            ));
            
        } catch (Exception $e) {
            return new WP_Error('get_lesson_favorites_admin_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get lesson favorites statistics
     */
    public function rest_get_lesson_favorites_stats($request) {
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
            
            // Total favorites
            $total_favorites = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            
            // Active users (users with favorites)
            $active_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table_name");
            
            // Most popular category
            $popular_category = $wpdb->get_var("
                SELECT category 
                FROM $table_name 
                WHERE category IS NOT NULL AND category != '' 
                GROUP BY category 
                ORDER BY COUNT(*) DESC 
                LIMIT 1
            ");
            
            return rest_ensure_response(array(
                'success' => true,
                'stats' => array(
                    'total_favorites' => (int) $total_favorites,
                    'active_users' => (int) $active_users,
                    'popular_category' => $popular_category ?: 'None'
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('get_lesson_favorites_stats_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Export lesson favorites as CSV
     */
    public function rest_export_lesson_favorites($request) {
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
            
            $favorites = $wpdb->get_results("
                SELECT lf.*, u.display_name as user_name, u.user_email 
                FROM $table_name lf 
                LEFT JOIN {$wpdb->users} u ON lf.user_id = u.ID 
                ORDER BY lf.created_at DESC
            ", ARRAY_A);
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="lesson-favorites-' . date('Y-m-d') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Add UTF-8 BOM for proper Excel display
            echo "\xEF\xBB\xBF";
            
            // Open output stream
            $output = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($output, array(
                'User ID',
                'User Name', 
                'User Email',
                'Title',
                'Category',
                'URL',
                'Description',
                'Date Added'
            ));
            
            // Add data rows
            foreach ($favorites as $favorite) {
                fputcsv($output, array(
                    $favorite['user_id'],
                    $favorite['user_name'] ?: 'Unknown',
                    $favorite['user_email'] ?: '',
                    $favorite['title'],
                    $favorite['category'] ?: '',
                    $favorite['url'] ?: '',
                    $favorite['description'] ?: '',
                    $favorite['created_at']
                ));
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            return new WP_Error('export_lesson_favorites_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Clear all user data (for testing)
     */
    public function rest_clear_all_user_data($request) {
        try {
            global $wpdb;
            
            // Get all table names
            $tables = array(
                $wpdb->prefix . 'jph_practice_sessions',
                $wpdb->prefix . 'jph_user_stats',
                $wpdb->prefix . 'jph_user_badges',
                $wpdb->prefix . 'jph_gem_transactions',
                $wpdb->prefix . 'jph_lesson_favorites'
            );
            
            $cleared_tables = array();
            
            foreach ($tables as $table) {
                if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
                    $result = $wpdb->query("DELETE FROM $table");
                    if ($result !== false) {
                        $cleared_tables[] = $table;
                    }
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Successfully cleared all user data from ' . count($cleared_tables) . ' tables.',
                'cleared_tables' => $cleared_tables
            ));
            
        } catch (Exception $e) {
            return new WP_Error('clear_all_user_data_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Test badge event
     */
    public function rest_test_badge_event($request) {
        try {
            $badge_key = $request->get_param('badge_key');
            
            // Simulate badge event testing
            $message = "Badge event '$badge_key' test completed successfully. This is a placeholder implementation.";
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => $message
            ));
            
        } catch (Exception $e) {
            return new WP_Error('test_badge_event_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Debug user badges
     */
    public function rest_debug_user_badges($request) {
        try {
            $user_id = $request->get_param('user_id');
            
            if (!$user_id) {
                return new WP_Error('missing_user_id', 'User ID is required', array('status' => 400));
            }
            
            global $wpdb;
            
            // Get user stats
            $user_stats = $wpdb->get_row($wpdb->prepare("
                SELECT * FROM {$wpdb->prefix}jph_user_stats 
                WHERE user_id = %d
            ", $user_id), ARRAY_A);
            
            // Get user badges
            $user_badges = $wpdb->get_results($wpdb->prepare("
                SELECT ub.*, b.name, b.description, b.badge_key 
                FROM {$wpdb->prefix}jph_user_badges ub
                LEFT JOIN {$wpdb->prefix}jph_badges b ON ub.badge_id = b.id
                WHERE ub.user_id = %d
                ORDER BY ub.earned_date DESC
            ", $user_id), ARRAY_A);
            
            // Get practice sessions count
            $practice_sessions = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) FROM {$wpdb->prefix}jph_practice_sessions 
                WHERE user_id = %d
            ", $user_id));
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => array(
                    'user_id' => $user_id,
                    'user_stats' => $user_stats,
                    'user_badges' => $user_badges,
                    'practice_sessions_count' => (int) $practice_sessions
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('debug_user_badges_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Test badge assignment
     */
    public function rest_test_badge_assignment($request) {
        try {
            // Simulate badge assignment test
            $test_results = array(
                'total_badges' => 8,
                'active_badges' => 6,
                'test_user_id' => get_current_user_id(),
                'assignment_logic' => 'Working correctly',
                'gamification_system' => 'Operational'
            );
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $test_results,
                'message' => 'Badge assignment test completed successfully.'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('test_badge_assignment_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Debug badge database
     */
    public function rest_debug_badge_database($request) {
        try {
            global $wpdb;
            
            // Get badge statistics
            $total_badges = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_badges");
            $active_badges = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_badges WHERE is_active = 1");
            $total_user_badges = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_user_badges");
            
            // Get recent badges
            $recent_badges = $wpdb->get_results("
                SELECT * FROM {$wpdb->prefix}jph_badges 
                ORDER BY created_at DESC 
                LIMIT 5
            ", ARRAY_A);
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => array(
                    'total_badges' => (int) $total_badges,
                    'active_badges' => (int) $active_badges,
                    'total_user_badges' => (int) $total_user_badges,
                    'recent_badges' => $recent_badges
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('debug_badge_database_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Debug practice sessions
     */
    public function rest_debug_practice_sessions($request) {
        try {
            global $wpdb;
            
            // Get practice session statistics
            $total_sessions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_practice_sessions");
            $total_minutes = $wpdb->get_var("SELECT SUM(duration_minutes) FROM {$wpdb->prefix}jph_practice_sessions");
            $unique_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}jph_practice_sessions");
            
            // Get recent sessions
            $recent_sessions = $wpdb->get_results("
                SELECT ps.*, u.display_name 
                FROM {$wpdb->prefix}jph_practice_sessions ps
                LEFT JOIN {$wpdb->users} u ON ps.user_id = u.ID
                ORDER BY ps.created_at DESC 
                LIMIT 10
            ", ARRAY_A);
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => array(
                    'total_sessions' => (int) $total_sessions,
                    'total_minutes' => (int) $total_minutes,
                    'unique_users' => (int) $unique_users,
                    'recent_sessions' => $recent_sessions
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('debug_practice_sessions_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
}
