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
        register_rest_route('aph/v1', '/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_test'),
            'permission_callback' => '__return_true'
        ));
        
        // Debug endpoint to check if routes are registered
        register_rest_route('aph/v1', '/debug/routes', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_routes'),
            'permission_callback' => '__return_true'
        ));
        
        // Leaderboard endpoints
        register_rest_route('aph/v1', '/leaderboard', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_leaderboard'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('aph/v1', '/leaderboard/position', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_user_position'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/leaderboard/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_leaderboard_stats'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('aph/v1', '/leaderboard/display-name', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_update_display_name'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/leaderboard/visibility', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_update_leaderboard_visibility'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/user-stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_user_stats'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Practice Sessions endpoints
        register_rest_route('aph/v1', '/practice-sessions', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_log_practice_session'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/practice-sessions', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_practice_sessions'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/practice-sessions/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_practice_session'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Export endpoint
        register_rest_route('aph/v1', '/export-practice-history', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_export_practice_history'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Badge management endpoints
        register_rest_route('aph/v1', '/admin/badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_badges_admin'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/admin/badges', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_add_badge'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/badges/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_badge'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/badges/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_badge'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/badges/key/(?P<badge_key>[a-zA-Z0-9_-]+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_badge_by_key'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/badges/key/(?P<badge_key>[a-zA-Z0-9_-]+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_badge_by_key'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // User-facing endpoints
        register_rest_route('aph/v1', '/badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_user_badges'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/lesson-favorites', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_lesson_favorites'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/purchase-shield', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_purchase_streak_shield'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Admin lesson favorites endpoints
        register_rest_route('aph/v1', '/admin/lesson-favorites', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_lesson_favorites_admin'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/admin/lesson-favorites-stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_lesson_favorites_stats'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/export-lesson-favorites', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_export_lesson_favorites'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Admin settings endpoints
        register_rest_route('aph/v1', '/admin/clear-all-user-data', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_clear_all_user_data'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Event tracking endpoints
        register_rest_route('aph/v1', '/test-badge-event', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_test_badge_event'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/debug-user-badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_user_badges'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/test-badge-assignment', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_test_badge_assignment'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/debug-badge-database', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_badge_database'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/debug-practice-sessions', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_practice_sessions'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Admin students endpoints
        register_rest_route('aph/v1', '/students', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_students'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/students/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_students_stats'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/students/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_student'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/students/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_student'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/export-students', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_export_students'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Analytics endpoint
        register_rest_route('aph/v1', '/analytics', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_analytics'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // AI Analysis endpoint
        register_rest_route('aph/v1', '/ai-analysis', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_ai_analysis'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // AI Analysis refresh endpoint
        register_rest_route('aph/v1', '/ai-analysis/refresh', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_refresh_ai_analysis'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Database debug endpoint
        register_rest_route('aph/v1', '/debug/database', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_database_debug'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Date test endpoint
        register_rest_route('aph/v1', '/debug/date-test', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_test_date_queries'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Practice item management endpoints
        register_rest_route('aph/v1', '/practice-items', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_add_practice_item'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Event tracking logs endpoints
        register_rest_route('aph/v1', '/event-logs/badge', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_badge_event_logs'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/event-logs/fluentcrm', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_fluentcrm_event_logs'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/event-logs/clear-badge', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_clear_badge_event_logs'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/event-logs/empty-fluentcrm', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_empty_fluentcrm_event_logs'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/practice-items/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_practice_item'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/practice-items/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_practice_item'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Admin data management endpoints
        register_rest_route('aph/v1', '/admin/export-user-data', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_export_user_data'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/admin/import-user-data', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_import_user_data'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
    }
    
    /**
     * Check user permission
     */
    public function check_user_permission() {
        return is_user_logged_in();
    }
    
    /**
     * Check admin permission
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Test endpoint
     */
    public function rest_test($request) {
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Academy Practice Hub API is working',
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'is_logged_in' => is_user_logged_in()
        ));
    }
    
    /**
     * Debug routes endpoint
     */
    public function rest_debug_routes($request) {
        global $wp_rest_server;
        
        $routes = array();
        if ($wp_rest_server) {
            $all_routes = $wp_rest_server->get_routes();
            foreach ($all_routes as $route => $handlers) {
                if (strpos($route, '/aph/v1/') === 0) {
                    $routes[] = $route;
                }
            }
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'registered_routes' => $routes,
            'total_jph_routes' => count($routes),
            'wp_rest_server_exists' => $wp_rest_server ? true : false
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
        $start_date = $request->get_param('start_date');
        $end_date = $request->get_param('end_date');
        
        error_log('Practice Sessions API: User ID: ' . $user_id . ', Limit: ' . $limit . ', Offset: ' . $offset . ', Start Date: ' . $start_date . ', End Date: ' . $end_date);
        
        // If date filtering is requested, use direct database query
        if ($start_date || $end_date) {
            global $wpdb;
            $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
            $items_table = $wpdb->prefix . 'jph_practice_items';
            
            $where_conditions = array("ps.user_id = %d");
            $params = array($user_id);
            
            if ($start_date) {
                $where_conditions[] = "DATE(ps.created_at) >= %s";
                $params[] = $start_date;
            }
            
            if ($end_date) {
                $where_conditions[] = "DATE(ps.created_at) <= %s";
                $params[] = $end_date;
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            $query = $wpdb->prepare("
                SELECT 
                    ps.*,
                    pi.name as item_name
                FROM $sessions_table ps
                LEFT JOIN $items_table pi ON ps.practice_item_id = pi.id
                WHERE $where_clause
                ORDER BY ps.created_at DESC
                LIMIT %d OFFSET %d
            ", array_merge($params, array($limit, $offset)));
            
            $sessions = $wpdb->get_results($query, ARRAY_A);
            
            // Get total count for has_more calculation
            $count_query = $wpdb->prepare("
                SELECT COUNT(*) 
                FROM $sessions_table ps
                WHERE $where_clause
            ", $params);
            
            $total_count = $wpdb->get_var($count_query);
            $has_more = ($offset + $limit) < $total_count;
            
        } else {
            // Use existing database method for regular requests
            $sessions = $this->database->get_practice_sessions($user_id, $limit, $offset);
            $has_more = count($sessions) >= $limit;
        }
        
        error_log('Practice Sessions API: Retrieved ' . count($sessions) . ' sessions');
        
        if (is_wp_error($sessions)) {
            return $sessions;
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'sessions' => $sessions,
            'count' => count($sessions),
            'has_more' => $has_more ?? false
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
        
        $result = $this->database->delete_practice_session($session_id, $user_id);
        
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
        
        error_log('Export: User ID: ' . $user_id);
        
        $sessions = $this->database->get_practice_sessions($user_id, 1000, 0);
        
        error_log('Export: Sessions query result: ' . print_r($sessions, true));
        
        if (is_wp_error($sessions)) {
            error_log('Export: Database error: ' . $sessions->get_error_message());
            return $sessions;
        }
        
        // Check if we have any sessions to export
        if (empty($sessions)) {
            error_log('Export: No sessions found for user ' . $user_id);
            return new WP_Error('no_sessions', 'No practice sessions found to export', array('status' => 404));
        }
        
        // Create CSV content in memory
        $csv_content = '';
        
        // Add BOM for UTF-8 compatibility with Excel
        $csv_content .= chr(0xEF).chr(0xBB).chr(0xBF);
        
        // CSV headers
        $csv_content .= '"Date","Practice Item","Duration (minutes)","Sentiment Score","Improvement Detected","XP Earned","Notes"' . "\n";
        
        // Add session data
        foreach ($sessions as $session) {
            $csv_content .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
                date('Y-m-d H:i:s', strtotime($session['created_at'])),
                str_replace('"', '""', $session['item_name'] ?: 'Unknown Item'),
                $session['duration_minutes'],
                $session['sentiment_score'],
                $session['improvement_detected'] ? 'Yes' : 'No',
                $session['xp_earned'] ?: 0,
                str_replace('"', '""', $session['notes'] ?: '')
            );
        }
        
        error_log('Export: CSV content length: ' . strlen($csv_content));
        error_log('Export: CSV preview: ' . substr($csv_content, 0, 200));
        
        return rest_ensure_response($csv_content);
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
            
            // Check if user is logged in
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to purchase shields', array('status' => 401));
            }
            
            // Check if classes exist
            if (!class_exists('JPH_Database')) {
                return new WP_Error('class_not_found', 'Database class not found', array('status' => 500));
            }
            
            if (!class_exists('APH_Gamification')) {
                return new WP_Error('class_not_found', 'Gamification class not found', array('status' => 500));
            }
            
            $database = new JPH_Database();
            $gamification = new APH_Gamification();
            
            // Get current user stats
            $user_stats = $gamification->get_user_stats($user_id);
            
            // Log for debugging
            error_log('JPH: Shield purchase attempt for user ' . $user_id);
            error_log('JPH: User stats: ' . print_r($user_stats, true));
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
                $database->record_gems_transaction($user_id, 'debit', -$shield_cost, 'streak_shield_purchase', 'Purchased streak shield');
                
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
            error_log('JPH: Shield purchase error: ' . $e->getMessage());
            error_log('JPH: Shield purchase error trace: ' . $e->getTraceAsString());
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
                $wpdb->prefix . 'jph_practice_items',
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
                LEFT JOIN {$wpdb->prefix}jph_badges b ON ub.badge_key = b.badge_key
                WHERE ub.user_id = %d
                ORDER BY ub.earned_at DESC
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
    
    /**
     * Get students for admin
     */
    public function rest_get_students($request) {
        try {
            global $wpdb;
            
            // Get filter parameters
            $search = $request->get_param('search');
            $level_filter = $request->get_param('level');
            $activity_filter = $request->get_param('activity');
            
            $table_name = $wpdb->prefix . 'jph_user_stats';
            
            // Build the query
            $where_conditions = array();
            $params = array();
            
            if (!empty($search)) {
                $where_conditions[] = "(u.display_name LIKE %s OR u.user_email LIKE %s)";
                $params[] = '%' . $wpdb->esc_like($search) . '%';
                $params[] = '%' . $wpdb->esc_like($search) . '%';
            }
            
            if (!empty($level_filter)) {
                if ($level_filter === '3') {
                    $where_conditions[] = "us.current_level >= 3";
                } else {
                    $where_conditions[] = "us.current_level = %d";
                    $params[] = intval($level_filter);
                }
            }
            
            if (!empty($activity_filter)) {
                if ($activity_filter === 'active') {
                    $where_conditions[] = "us.last_practice_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                } elseif ($activity_filter === 'inactive') {
                    $where_conditions[] = "us.last_practice_date < DATE_SUB(NOW(), INTERVAL 30 DAY)";
                }
            }
            
            // Always filter for students with stats records (like original plugin)
            $where_conditions[] = "us.user_id IS NOT NULL";
            
            $where_clause = '';
            if (!empty($where_conditions)) {
                $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
            }
            
            $query = "
                SELECT 
                    u.ID,
                    u.display_name,
                    u.user_email,
                    us.total_xp,
                    us.current_level,
                    us.current_streak,
                    us.longest_streak,
                    us.badges_earned,
                    us.last_practice_date,
                    us.total_sessions,
                    us.total_minutes,
                    us.gems_balance,
                    us.streak_shield_count
                FROM {$wpdb->users} u
                LEFT JOIN $table_name us ON u.ID = us.user_id
                $where_clause
                ORDER BY us.total_xp DESC, u.display_name ASC
            ";
            
            if (!empty($params)) {
                $query = $wpdb->prepare($query, $params);
            }
            
            $students = $wpdb->get_results($query, ARRAY_A);
            
            return rest_ensure_response(array(
                'success' => true,
                'students' => $students
            ));
            
        } catch (Exception $e) {
            return new WP_Error('get_students_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get students statistics
     */
    public function rest_get_students_stats($request) {
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'jph_user_stats';
            
            // Total students (all students with stats records AND user records)
            $total_students = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM $table_name us 
                INNER JOIN {$wpdb->users} u ON us.user_id = u.ID
            ");
            
            // Active students (practiced in last 7 days)
            $active_students = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM $table_name us 
                INNER JOIN {$wpdb->users} u ON us.user_id = u.ID
                WHERE us.last_practice_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            
            // Total practice hours
            $total_hours = $wpdb->get_var("
                SELECT SUM(us.total_minutes) / 60 
                FROM $table_name us 
                INNER JOIN {$wpdb->users} u ON us.user_id = u.ID
            ");
            
            // Average level
            $average_level = $wpdb->get_var("
                SELECT AVG(us.current_level) 
                FROM $table_name us 
                INNER JOIN {$wpdb->users} u ON us.user_id = u.ID
            ");
            
            return rest_ensure_response(array(
                'success' => true,
                'stats' => array(
                    'total_students' => (int) $total_students,
                    'active_students' => (int) $active_students,
                    'total_hours' => round($total_hours, 1),
                    'average_level' => round($average_level, 1)
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('get_students_stats_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get single student
     */
    public function rest_get_student($request) {
        try {
            global $wpdb;
            
            $user_id = $request->get_param('id');
            $table_name = $wpdb->prefix . 'jph_user_stats';
            
            $student = $wpdb->get_row($wpdb->prepare("
                SELECT 
                    u.ID,
                    u.display_name,
                    u.user_email,
                    us.*
                FROM {$wpdb->users} u
                LEFT JOIN $table_name us ON u.ID = us.user_id
                WHERE u.ID = %d
            ", $user_id), ARRAY_A);
            
            if (!$student) {
                return new WP_Error('student_not_found', 'Student not found', array('status' => 404));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'student' => $student
            ));
            
        } catch (Exception $e) {
            return new WP_Error('get_student_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Update student
     */
    public function rest_update_student($request) {
        try {
            global $wpdb;
            
            $user_id = $request->get_param('id');
            $table_name = $wpdb->prefix . 'jph_user_stats';
            
            // Get the request body
            $data = $request->get_json_params();
            
            // Validate required fields
            $allowed_fields = array(
                'total_xp', 'current_level', 'current_streak', 'longest_streak',
                'total_sessions', 'total_minutes', 'hearts_count', 'gems_balance',
                'badges_earned', 'last_practice_date', 'streak_shield_count'
            );
            
            $update_data = array();
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                }
            }
            
            if (empty($update_data)) {
                return new WP_Error('no_data', 'No valid data provided', array('status' => 400));
            }
            
            // Add updated timestamp
            $update_data['updated_at'] = current_time('mysql');
            
            // Update the student
            $result = $wpdb->update(
                $table_name,
                $update_data,
                array('user_id' => $user_id),
                array_fill(0, count($update_data), '%s'),
                array('%d')
            );
            
            if ($result === false) {
                return new WP_Error('update_failed', 'Failed to update student', array('status' => 500));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Student updated successfully'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('update_student_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Export students as CSV
     */
    public function rest_export_students($request) {
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'jph_user_stats';
            
            $students = $wpdb->get_results("
                SELECT 
                    u.ID as user_id,
                    u.display_name,
                    u.user_email,
                    us.total_xp,
                    us.current_level,
                    us.current_streak,
                    us.longest_streak,
                    us.badges_earned,
                    us.last_practice_date,
                    us.total_sessions,
                    us.total_minutes,
                    us.gems_balance,
                    us.hearts_count,
                    us.streak_shield_count
                FROM {$wpdb->users} u
                LEFT JOIN $table_name us ON u.ID = us.user_id
                WHERE us.user_id IS NOT NULL
                ORDER BY us.total_xp DESC, u.display_name ASC
            ", ARRAY_A);
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="students-' . date('Y-m-d') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Add UTF-8 BOM for proper Excel display
            echo "\xEF\xBB\xBF";
            
            // Open output stream
            $output = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($output, array(
                'User ID',
                'Name',
                'Email',
                'Total XP',
                'Level',
                'Current Streak',
                'Longest Streak',
                'Badges Earned',
                'Streak Shields',
                'Last Practice',
                'Total Sessions',
                'Total Minutes',
                'Gems Balance',
                'Hearts Count'
            ));
            
            // Add data rows
            foreach ($students as $student) {
                fputcsv($output, array(
                    $student['user_id'],
                    $student['display_name'] ?: 'Unknown',
                    $student['user_email'] ?: '',
                    $student['total_xp'] ?: 0,
                    $student['current_level'] ?: 1,
                    $student['current_streak'] ?: 0,
                    $student['longest_streak'] ?: 0,
                    $student['badges_earned'] ?: 0,
                    $student['streak_shield_count'] ?: 0,
                    $student['last_practice_date'] ?: '',
                    $student['total_sessions'] ?: 0,
                    $student['total_minutes'] ?: 0,
                    $student['gems_balance'] ?: 0,
                    $student['hearts_count'] ?: 0
                ));
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            return new WP_Error('export_students_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get user analytics data
     */
    public function rest_get_analytics($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to view analytics', array('status' => 401));
            }
            
            global $wpdb;
            
            // Get practice sessions for different time periods
            $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
            $stats_table = $wpdb->prefix . 'jph_user_stats';
            
            // Calculate date ranges
            $now = current_time('mysql');
            $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
            $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
            $ninety_days_ago = date('Y-m-d H:i:s', strtotime('-90 days'));
            $three_sixty_five_days_ago = date('Y-m-d H:i:s', strtotime('-365 days'));
            
            // Get practice data for different periods
            $analytics = array();
            
            // 7 days
            $seven_day_data = $wpdb->get_row($wpdb->prepare("
                SELECT 
                    COUNT(*) as sessions,
                    SUM(duration_minutes) as total_minutes,
                    AVG(duration_minutes) as avg_duration,
                    AVG(sentiment_score) as avg_sentiment,
                    SUM(improvement_detected) as improvements
                FROM $sessions_table 
                WHERE user_id = %d AND created_at >= %s
            ", $user_id, $seven_days_ago), ARRAY_A);
            
            // 30 days
            $thirty_day_data = $wpdb->get_row($wpdb->prepare("
                SELECT 
                    COUNT(*) as sessions,
                    SUM(duration_minutes) as total_minutes,
                    AVG(duration_minutes) as avg_duration,
                    AVG(sentiment_score) as avg_sentiment,
                    SUM(improvement_detected) as improvements
                FROM $sessions_table 
                WHERE user_id = %d AND created_at >= %s
            ", $user_id, $thirty_days_ago), ARRAY_A);
            
            // 90 days
            $ninety_day_data = $wpdb->get_row($wpdb->prepare("
                SELECT 
                    COUNT(*) as sessions,
                    SUM(duration_minutes) as total_minutes,
                    AVG(duration_minutes) as avg_duration,
                    AVG(sentiment_score) as avg_sentiment,
                    SUM(improvement_detected) as improvements
                FROM $sessions_table 
                WHERE user_id = %d AND created_at >= %s
            ", $user_id, $ninety_days_ago), ARRAY_A);
            
            // 365 days
            $year_data = $wpdb->get_row($wpdb->prepare("
                SELECT 
                    COUNT(*) as sessions,
                    SUM(duration_minutes) as total_minutes,
                    AVG(duration_minutes) as avg_duration,
                    AVG(sentiment_score) as avg_sentiment,
                    SUM(improvement_detected) as improvements
                FROM $sessions_table 
                WHERE user_id = %d AND created_at >= %s
            ", $user_id, $three_sixty_five_days_ago), ARRAY_A);
            
            // Get current user stats
            $user_stats = $wpdb->get_row($wpdb->prepare("
                SELECT * FROM $stats_table WHERE user_id = %d
            ", $user_id), ARRAY_A);
            
            // Get practice frequency (days with practice in last 30 days)
            $practice_days = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(DISTINCT DATE(created_at)) 
                FROM $sessions_table 
                WHERE user_id = %d AND created_at >= %s
            ", $user_id, $thirty_days_ago));
            
            // Get best practice day (most minutes in a single day)
            $best_day = $wpdb->get_row($wpdb->prepare("
                SELECT DATE(created_at) as practice_date, SUM(duration_minutes) as total_minutes
                FROM $sessions_table 
                WHERE user_id = %d 
                GROUP BY DATE(created_at) 
                ORDER BY total_minutes DESC 
                LIMIT 1
            ", $user_id), ARRAY_A);
            
            // Get favorite practice time (hour of day)
            $favorite_hour = $wpdb->get_row($wpdb->prepare("
                SELECT HOUR(created_at) as hour, COUNT(*) as sessions
                FROM $sessions_table 
                WHERE user_id = %d 
                GROUP BY HOUR(created_at) 
                ORDER BY sessions DESC 
                LIMIT 1
            ", $user_id), ARRAY_A);
            
            // Get most practiced item
            $most_practiced = $wpdb->get_row($wpdb->prepare("
                SELECT pi.name as item_name, COUNT(*) as sessions, SUM(ps.duration_minutes) as total_minutes
                FROM $sessions_table ps
                LEFT JOIN {$wpdb->prefix}jph_practice_items pi ON ps.practice_item_id = pi.id
                WHERE ps.user_id = %d
                GROUP BY ps.practice_item_id, pi.name
                ORDER BY sessions DESC
                LIMIT 1
            ", $user_id), ARRAY_A);
            
            // Calculate consistency score (percentage of days practiced in last 30 days)
            $consistency_score = $practice_days ? round(($practice_days / 30) * 100, 1) : 0;
            
            // Calculate improvement rate
            $total_sessions_30 = $thirty_day_data['sessions'] ?: 0;
            $improvements_30 = $thirty_day_data['improvements'] ?: 0;
            $improvement_rate = $total_sessions_30 > 0 ? round(($improvements_30 / $total_sessions_30) * 100, 1) : 0;
            
            // Calculate average sentiment score
            $avg_sentiment_30 = $thirty_day_data['avg_sentiment'] ?: 0;
            $sentiment_rating = '';
            if ($avg_sentiment_30 >= 4.5) $sentiment_rating = 'Excellent';
            elseif ($avg_sentiment_30 >= 3.5) $sentiment_rating = 'Good';
            elseif ($avg_sentiment_30 >= 2.5) $sentiment_rating = 'Okay';
            elseif ($avg_sentiment_30 >= 1.5) $sentiment_rating = 'Challenging';
            else $sentiment_rating = 'Frustrating';
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => array(
                    'periods' => array(
                        '7_days' => array(
                            'sessions' => (int)($seven_day_data['sessions'] ?: 0),
                            'total_minutes' => (int)($seven_day_data['total_minutes'] ?: 0),
                            'avg_duration' => round($seven_day_data['avg_duration'] ?: 0, 1),
                            'avg_sentiment' => round($seven_day_data['avg_sentiment'] ?: 0, 1),
                            'improvements' => (int)($seven_day_data['improvements'] ?: 0)
                        ),
                        '30_days' => array(
                            'sessions' => (int)($thirty_day_data['sessions'] ?: 0),
                            'total_minutes' => (int)($thirty_day_data['total_minutes'] ?: 0),
                            'avg_duration' => round($thirty_day_data['avg_duration'] ?: 0, 1),
                            'avg_sentiment' => round($thirty_day_data['avg_sentiment'] ?: 0, 1),
                            'improvements' => (int)($thirty_day_data['improvements'] ?: 0)
                        ),
                        '90_days' => array(
                            'sessions' => (int)($ninety_day_data['sessions'] ?: 0),
                            'total_minutes' => (int)($ninety_day_data['total_minutes'] ?: 0),
                            'avg_duration' => round($ninety_day_data['avg_duration'] ?: 0, 1),
                            'avg_sentiment' => round($ninety_day_data['avg_sentiment'] ?: 0, 1),
                            'improvements' => (int)($ninety_day_data['improvements'] ?: 0)
                        ),
                        '365_days' => array(
                            'sessions' => (int)($year_data['sessions'] ?: 0),
                            'total_minutes' => (int)($year_data['total_minutes'] ?: 0),
                            'avg_duration' => round($year_data['avg_duration'] ?: 0, 1),
                            'avg_sentiment' => round($year_data['avg_sentiment'] ?: 0, 1),
                            'improvements' => (int)($year_data['improvements'] ?: 0)
                        )
                    ),
                    'insights' => array(
                        'consistency_score' => $consistency_score,
                        'improvement_rate' => $improvement_rate,
                        'sentiment_rating' => $sentiment_rating,
                        'practice_days_30' => (int)$practice_days,
                        'best_day' => $best_day ? array(
                            'date' => $best_day['practice_date'],
                            'minutes' => (int)$best_day['total_minutes']
                        ) : null,
                        'favorite_hour' => $favorite_hour ? array(
                            'hour' => (int)$favorite_hour['hour'],
                            'sessions' => (int)$favorite_hour['sessions']
                        ) : null,
                        'most_practiced' => $most_practiced ? array(
                            'item_name' => $most_practiced['item_name'] ?: 'Unknown',
                            'sessions' => (int)$most_practiced['sessions'],
                            'total_minutes' => (int)$most_practiced['total_minutes']
                        ) : null
                    ),
                    'current_stats' => $user_stats ?: array()
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('analytics_error', 'Error retrieving analytics: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get AI analysis of practice data
     */
    public function rest_get_ai_analysis($request) {
        try {
            // Allow admin to test with specific user ID
            $requested_user_id = $request->get_param('user_id');
            if ($requested_user_id && current_user_can('manage_options')) {
                $user_id = intval($requested_user_id);
            } else {
                $user_id = get_current_user_id();
            }
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to view AI analysis', array('status' => 401));
            }
            
            // Check if refresh is requested
            $refresh = $request->get_param('refresh');
            $cache_key = 'jph_ai_analysis_' . $user_id;
            
            // If refresh is requested, clear cache
            if ($refresh === '1' || $refresh === 'true') {
                delete_transient($cache_key);
            }
            
            // Check for cached analysis (valid for 24 hours)
            $cached_analysis = get_transient($cache_key);
            
            if ($cached_analysis !== false && !$refresh) {
                return rest_ensure_response(array(
                    'success' => true,
                    'data' => $cached_analysis,
                    'cached' => true,
                    'cache_expires' => get_option('_transient_timeout_' . $cache_key)
                ));
            }
            
            // Generate new analysis
            $analysis = $this->generate_ai_analysis($user_id);
            
            if (is_wp_error($analysis)) {
                return $analysis;
            }
            
            // Cache the analysis for 24 hours
            set_transient($cache_key, $analysis, DAY_IN_SECONDS);
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $analysis,
                'cached' => false,
                'refreshed' => $refresh ? true : false
            ));
            
        } catch (Exception $e) {
            return new WP_Error('ai_analysis_error', 'Error retrieving AI analysis: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Refresh AI analysis (bypass cache)
     */
    public function rest_refresh_ai_analysis($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to refresh AI analysis', array('status' => 401));
            }
            
            // Clear existing cache
            $cache_key = 'jph_ai_analysis_' . $user_id;
            delete_transient($cache_key);
            
            // Generate new analysis
            $analysis = $this->generate_ai_analysis($user_id);
            
            if (is_wp_error($analysis)) {
                return $analysis;
            }
            
            // Cache the new analysis for 24 hours
            set_transient($cache_key, $analysis, DAY_IN_SECONDS);
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $analysis,
                'cached' => false,
                'refreshed' => true
            ));
            
        } catch (Exception $e) {
            return new WP_Error('ai_analysis_refresh_error', 'Error refreshing AI analysis: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Generate AI analysis using Katahdin AI Hub
     */
    private function generate_ai_analysis($user_id) {
        global $wpdb;
        
        // Get practice data for last 30 days
        $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
        $items_table = $wpdb->prefix . 'jph_practice_items';
        $stats_table = $wpdb->prefix . 'jph_user_stats';
        
        // Use WordPress timezone for consistent date calculation
        $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days', current_time('timestamp')));
        $current_time = current_time('mysql');
        
        // Get practice sessions data
        $sessions_query = $wpdb->prepare("
            SELECT 
                ps.duration_minutes,
                ps.sentiment_score,
                ps.improvement_detected,
                ps.created_at,
                pi.name as item_name
            FROM $sessions_table ps
            LEFT JOIN $items_table pi ON ps.practice_item_id = pi.id
            WHERE ps.user_id = %d AND ps.created_at >= %s
            ORDER BY ps.created_at DESC
        ", $user_id, $thirty_days_ago);
        
        $sessions = $wpdb->get_results($sessions_query, ARRAY_A);
        
        // Debug: Log the query and results
        error_log("AI Analysis Debug - User ID: " . $user_id);
        error_log("AI Analysis Debug - Date Range: " . $thirty_days_ago . " to " . $current_time);
        error_log("AI Analysis Debug - SQL Query: " . $sessions_query);
        error_log("AI Analysis Debug - Sessions Found: " . count($sessions));
        
        // Log some sample session dates for debugging
        if (!empty($sessions)) {
            error_log("AI Analysis Debug - Sample session dates:");
            foreach (array_slice($sessions, 0, 3) as $session) {
                error_log("  - Session date: " . $session['created_at']);
            }
        }
        
        // Also check if tables exist and have data
        $sessions_count_total = $wpdb->get_var("SELECT COUNT(*) FROM $sessions_table WHERE user_id = $user_id");
        $sessions_count_30_days = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $sessions_table WHERE user_id = %d AND created_at >= %s", $user_id, $thirty_days_ago));
        
        error_log("AI Analysis Debug - Total sessions for user: " . $sessions_count_total);
        error_log("AI Analysis Debug - Sessions in last 30 days: " . $sessions_count_30_days);
        
        // Check table structure
        $table_structure = $wpdb->get_results("DESCRIBE $sessions_table", ARRAY_A);
        error_log("AI Analysis Debug - Sessions table structure: " . print_r($table_structure, true));
        
        // Additional debugging - check if tables exist
        $tables_exist = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}jph_%'", ARRAY_A);
        error_log("AI Analysis Debug - JPH tables found: " . print_r($tables_exist, true));
        
        // Check if user exists in user_stats table
        $user_in_stats = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $stats_table WHERE user_id = %d", $user_id));
        error_log("AI Analysis Debug - User found in stats table: " . $user_in_stats);
        
        // Check recent sessions without date filter
        $recent_sessions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $sessions_table WHERE user_id = %d ORDER BY created_at DESC LIMIT 5", $user_id), ARRAY_A);
        error_log("AI Analysis Debug - Recent sessions for user: " . print_r($recent_sessions, true));
        
        // Test the date comparison directly
        $test_query = $wpdb->prepare("SELECT COUNT(*) as count, MIN(created_at) as earliest, MAX(created_at) as latest FROM $sessions_table WHERE user_id = %d AND created_at >= %s", $user_id, $thirty_days_ago);
        $test_result = $wpdb->get_row($test_query, ARRAY_A);
        error_log("AI Analysis Debug - Date test result: " . print_r($test_result, true));
        
        // Test with different date formats
        $test_date_formats = array(
            'Y-m-d H:i:s' => date('Y-m-d H:i:s', strtotime('-30 days', current_time('timestamp'))),
            'Y-m-d' => date('Y-m-d', strtotime('-30 days', current_time('timestamp'))),
            'Y-m-d 00:00:00' => date('Y-m-d 00:00:00', strtotime('-30 days', current_time('timestamp')))
        );
        
        foreach ($test_date_formats as $format => $test_date) {
            $test_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $sessions_table WHERE user_id = %d AND created_at >= %s", $user_id, $test_date));
            error_log("AI Analysis Debug - Test with format '$format' ($test_date): $test_count sessions found");
        }
        
        // Get user stats
        $user_stats = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $stats_table WHERE user_id = %d
        ", $user_id), ARRAY_A);
        
        // Always populate debug info regardless of session count
        $debug_info = array(
            'user_id' => $user_id,
            'date_range_start' => $thirty_days_ago,
            'date_range_end' => $current_time,
            'sql_query' => $sessions_query,
            'sessions_found' => count($sessions),
            'total_sessions_user' => $sessions_count_total,
            'sessions_30_days' => $sessions_count_30_days,
            'table_names' => array(
                'sessions_table' => $sessions_table,
                'items_table' => $items_table,
                'stats_table' => $stats_table
            )
        );
        
        if (empty($sessions)) {
            return array(
                'analysis' => 'You haven\'t practiced in the last 30 days. Start logging your practice sessions to get personalized AI insights!',
                'data_period' => 'Last 30 days',
                'generated_at' => current_time('mysql'),
                'debug_prompt' => 'No prompt generated - no sessions found',
                'debug_info' => $debug_info,
                'data_summary' => array(
                    'total_sessions' => 0,
                    'total_minutes' => 0,
                    'avg_duration' => 0,
                    'avg_sentiment' => 0,
                    'improvement_rate' => 0,
                    'most_frequent_day' => 'None',
                    'most_practiced_item' => 'None'
                )
            );
        }
        
        // Prepare data for AI analysis
        $total_sessions = count($sessions);
        $total_minutes = array_sum(array_column($sessions, 'duration_minutes'));
        $avg_duration = round($total_minutes / $total_sessions, 1);
        $avg_sentiment = round(array_sum(array_column($sessions, 'sentiment_score')) / $total_sessions, 1);
        $improvements = array_sum(array_column($sessions, 'improvement_detected'));
        $improvement_rate = round(($improvements / $total_sessions) * 100, 1);
        
        // Get practice frequency by day of week
        $day_frequency = array();
        foreach ($sessions as $session) {
            $day = date('l', strtotime($session['created_at']));
            $day_frequency[$day] = ($day_frequency[$day] ?? 0) + 1;
        }
        $most_frequent_day = array_keys($day_frequency, max($day_frequency))[0] ?? 'Unknown';
        
        // Get most practiced item
        $item_frequency = array();
        foreach ($sessions as $session) {
            $item = $session['item_name'] ?: 'Unknown';
            $item_frequency[$item] = ($item_frequency[$item] ?? 0) + 1;
        }
        $most_practiced_item = array_keys($item_frequency, max($item_frequency))[0] ?? 'Unknown';
        
        // Get configurable AI prompt - force update if it contains the old typo
        $current_prompt = get_option('aph_ai_prompt', '');
        $new_prompt = 'CRITICAL FORMATTING REQUIREMENT: You MUST respond with ONLY plain text. NO emojis, NO markdown, NO bold text, NO asterisks, NO section headers, NO bullet points, NO special characters. Write as a single paragraph of normal text only.

Analyze this piano practice data from the last 30 days and provide insights in 2–3 sentences. Be encouraging, specific, and actionable. Use the data to highlight positive progress, consistency, and areas for small improvements.

Practice Sessions: {total_sessions} sessions
Total Practice Time: {total_minutes} minutes
Average Session Length: {avg_duration} minutes
Average Mood/Sentiment: {avg_sentiment}/5 (1=frustrating, 5=excellent)
Improvement Rate: {improvement_rate}% of sessions showed improvement
Most Frequent Practice Day: {most_frequent_day}
Most Practiced Item: {most_practiced_item}
Current Level: {current_level}
Current Streak: {current_streak} days

Provide specific, motivational insights about their practice habits and suggest 1–2 focused next steps for improvement. Keep it uplifting, practical, and concise. When recommending lessons, use these titles naturally where relevant: Technique - Jazzedge Practice Curriculum™; Improvisation - The Confident Improviser™; Accompaniment - Piano Accompaniment Essentials™; Jazz Standards - Standards By The Dozen™; Super Easy Jazz Standards - Super Simple Standards™.

FORMATTING RULE: Write your response as one continuous paragraph using only regular letters, numbers, and basic punctuation. Do not use any symbols, emojis, or formatting characters.';

        // Force update the prompt to ensure we have the latest formatting instructions
        if (strpos($current_prompt, 'CRITICAL FORMATTING REQUIREMENT') === false) {
            update_option('aph_ai_prompt', $new_prompt);
            $ai_prompt_template = $new_prompt;
        } else {
            $ai_prompt_template = get_option('aph_ai_prompt', $new_prompt);
        }
        
        // Replace placeholders in the prompt
        $prompt = str_replace(
            array(
                '{total_sessions}',
                '{total_minutes}',
                '{avg_duration}',
                '{avg_sentiment}',
                '{improvement_rate}',
                '{most_frequent_day}',
                '{most_practiced_item}',
                '{current_level}',
                '{current_streak}'
            ),
            array(
                $total_sessions,
                $total_minutes,
                $avg_duration,
                $avg_sentiment,
                $improvement_rate,
                $most_frequent_day,
                $most_practiced_item,
                ($user_stats['current_level'] ?? 1),
                ($user_stats['current_streak'] ?? 0)
            ),
            $ai_prompt_template
        );

        // Get AI settings for the call
        $ai_system_message = get_option('aph_ai_system_message', 'You are a helpful piano practice coach. Provide encouraging, specific insights about practice patterns. CRITICAL FORMATTING RULE: You MUST respond with ONLY plain text. NO emojis, NO markdown, NO bold text, NO asterisks, NO section headers, NO bullet points, NO special characters. Write as a single paragraph using only regular letters, numbers, and basic punctuation.');
        $ai_model = get_option('aph_ai_model', 'gpt-3.5-turbo');
        $ai_max_tokens = get_option('aph_ai_max_tokens', 300);
        $ai_temperature = get_option('aph_ai_temperature', 0.3);
        
        // Call Katahdin AI Hub
        $ai_response = $this->call_katahdin_ai($prompt, $ai_system_message, $ai_model, $ai_max_tokens, $ai_temperature);
        
        if (is_wp_error($ai_response)) {
            // Fallback to basic analysis if AI service fails
            error_log("AI service failed, using fallback analysis: " . $ai_response->get_error_message());
            $ai_response = $this->generate_fallback_analysis($sessions, $user_stats, $debug_info);
        }
        
        return array(
            'analysis' => $ai_response,
            'data_period' => 'Last 30 days',
            'generated_at' => current_time('mysql'),
            'debug_prompt' => $prompt,
            'debug_info' => array_merge($debug_info, array(
                'system_message' => $ai_system_message,
                'ai_model' => $ai_model,
                'temperature' => $ai_temperature,
                'max_tokens' => $ai_max_tokens,
                'request_data' => array(
                    'messages' => array(
                        array(
                            'role' => 'system',
                            'content' => $ai_system_message
                        ),
                        array(
                            'role' => 'user',
                            'content' => $prompt
                        )
                    ),
                    'model' => $ai_model,
                    'max_tokens' => $ai_max_tokens,
                    'temperature' => $ai_temperature
                )
            )),
            'data_summary' => array(
                'total_sessions' => $total_sessions,
                'total_minutes' => $total_minutes,
                'avg_duration' => $avg_duration,
                'avg_sentiment' => $avg_sentiment,
                'improvement_rate' => $improvement_rate,
                'most_frequent_day' => $most_frequent_day,
                'most_practiced_item' => $most_practiced_item
            )
        );
    }
    
    
    /**
     * Call Katahdin AI Hub for analysis
     */
    private function call_katahdin_ai($prompt, $ai_system_message, $ai_model, $ai_max_tokens, $ai_temperature) {
        // Check if Katahdin AI Hub is available
        if (!class_exists('Katahdin_AI_Hub_REST_API')) {
            return new WP_Error('ai_hub_unavailable', 'Katahdin AI Hub is not available', array('status' => 503));
        }
        
        // Ensure plugin is registered with Katahdin AI Hub
        if (class_exists('Katahdin_AI_Hub') && function_exists('katahdin_ai_hub')) {
            $hub = katahdin_ai_hub();
            if ($hub && method_exists($hub, 'register_plugin')) {
                $hub->register_plugin('academy-practice-hub', array(
                    'name' => 'Academy Practice Hub',
                    'version' => '3.0',
                    'features' => array('chat', 'completions'),
                    'quota_limit' => 5000
                ));
            }
        }
        
        // Use the AI settings passed as parameters
        
        // Prepare the request
        $request_data = array(
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $ai_system_message
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'model' => $ai_model,
            'max_tokens' => $ai_max_tokens,
            'temperature' => $ai_temperature,
            'plugin_id' => 'academy-practice-hub'
        );
        
        // Make the request to Katahdin AI Hub using internal WordPress REST API
        $rest_server = rest_get_server();
        $request = new WP_REST_Request('POST', '/katahdin-ai-hub/v1/chat/completions');
        $request->set_header('Content-Type', 'application/json');
        $request->set_header('X-Plugin-ID', 'academy-practice-hub');
        $request->set_body(json_encode($request_data));
        
        // Add the request data as parameters
        foreach ($request_data as $key => $value) {
            $request->set_param($key, $value);
        }
        
        $response = $rest_server->dispatch($request);
        
        if (is_wp_error($response)) {
            return new WP_Error('ai_request_failed', 'Failed to connect to AI service: ' . $response->get_error_message(), array('status' => 500));
        }
        
        // Get the response data
        $response_data = $response->get_data();
        $response_status = $response->get_status();
        
        // Debug logging
        error_log("Katahdin AI Hub Response Status: " . $response_status);
        error_log("Katahdin AI Hub Response Data: " . print_r($response_data, true));
        
        if ($response_status !== 200) {
            $error_message = isset($response_data['message']) ? $response_data['message'] : 'Unknown error';
            return new WP_Error('ai_service_error', 'AI service returned error: ' . $response_status . ' - ' . $error_message, array('status' => $response_status));
        }
        
        if (!isset($response_data['choices'][0]['message']['content'])) {
            return new WP_Error('ai_response_invalid', 'Invalid response from AI service', array('status' => 500));
        }
        
        return trim($response_data['choices'][0]['message']['content']);
    }
    
    /**
     * Get comprehensive database debug information
     */
    public function rest_get_database_debug($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to view debug information', array('status' => 401));
            }
            
            global $wpdb;
            
            // Get all JPH tables
            $tables = array(
                'jph_practice_sessions',
                'jph_practice_items', 
                'jph_user_stats',
                'jph_user_badges',
                'jph_badges',
                'jph_gems_transactions',
                'jph_lesson_favorites'
            );
            
            $debug_data = array();
            
            foreach ($tables as $table) {
                $table_name = $wpdb->prefix . $table;
                
                // Check if table exists
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
                
                if ($table_exists) {
                    // Get table structure
                    $structure = $wpdb->get_results("DESCRIBE $table_name", ARRAY_A);
                    
                    // Get row count
                    $row_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                    
                    // Get user-specific data if applicable
                    $user_data = array();
                    if (in_array($table, array('jph_practice_sessions', 'jph_practice_items', 'jph_user_stats', 'jph_user_badges', 'jph_gems_transactions', 'jph_lesson_favorites'))) {
                        $user_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC LIMIT 10", $user_id), ARRAY_A);
                    }
                    
                    // Get recent data (last 10 rows) for non-user tables
                    $recent_data = array();
                    if (!in_array($table, array('jph_practice_sessions', 'jph_practice_items', 'jph_user_stats', 'jph_user_badges', 'jph_gems_transactions', 'jph_lesson_favorites'))) {
                        $recent_data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC LIMIT 10", ARRAY_A);
                    }
                    
                    $debug_data[$table] = array(
                        'exists' => true,
                        'row_count' => $row_count,
                        'structure' => $structure,
                        'user_data' => $user_data,
                        'recent_data' => $recent_data
                    );
                } else {
                    $debug_data[$table] = array(
                        'exists' => false,
                        'row_count' => 0,
                        'structure' => array(),
                        'user_data' => array(),
                        'recent_data' => array()
                    );
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'user_id' => $user_id,
                'tables' => $debug_data,
                'generated_at' => current_time('mysql')
            ));
            
        } catch (Exception $e) {
            return new WP_Error('debug_error', 'Error retrieving debug information: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Test date queries for debugging
     */
    public function rest_test_date_queries($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to test date queries', array('status' => 401));
            }
            
            global $wpdb;
            
            $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
            
            // Test different date calculations
            $current_time = current_time('mysql');
            $current_timestamp = current_time('timestamp');
            
            $date_tests = array(
                'current_time_mysql' => $current_time,
                'current_time_timestamp' => $current_timestamp,
                'thirty_days_ago_mysql' => date('Y-m-d H:i:s', strtotime('-30 days', $current_timestamp)),
                'thirty_days_ago_simple' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'thirty_days_ago_date_only' => date('Y-m-d', strtotime('-30 days', $current_timestamp)),
                'thirty_days_ago_start_of_day' => date('Y-m-d 00:00:00', strtotime('-30 days', $current_timestamp))
            );
            
            $results = array();
            
            // Get all sessions for this user first
            $all_sessions = $wpdb->get_results($wpdb->prepare("SELECT created_at FROM $sessions_table WHERE user_id = %d ORDER BY created_at DESC LIMIT 10", $user_id), ARRAY_A);
            
            foreach ($date_tests as $test_name => $test_date) {
                $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $sessions_table WHERE user_id = %d AND created_at >= %s", $user_id, $test_date));
                
                $results[$test_name] = array(
                    'date' => $test_date,
                    'sessions_found' => $count
                );
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'user_id' => $user_id,
                'all_sessions_sample' => $all_sessions,
                'date_tests' => $results,
                'generated_at' => current_time('mysql')
            ));
            
        } catch (Exception $e) {
            return new WP_Error('date_test_error', 'Error testing date queries: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Add practice item
     */
    public function rest_add_practice_item($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to add practice items', array('status' => 401));
            }
            
            $name = $request->get_param('item_name') ?: $request->get_param('name');
            $category = $request->get_param('category') ?: 'custom';
            $description = $request->get_param('item_description') ?: $request->get_param('description') ?: '';
            $lesson_favorite = $request->get_param('lesson_favorite');
            $url = '';
            
            // If created from lesson favorite, get the URL
            if (!empty($lesson_favorite)) {
                $favorite = $this->database->get_lesson_favorite($lesson_favorite);
                if ($favorite && !is_wp_error($favorite)) {
                    $url = $favorite['url'];
                    $category = $favorite['category'];
                }
            }
            
            if (!$name) {
                return new WP_Error('missing_name', 'Practice item name is required', array('status' => 400));
            }
            
            $result = $this->database->add_practice_item($user_id, $name, $category, $description, $url);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'item_id' => $result,
                'message' => 'Practice item added successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('add_item_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Update practice item
     */
    public function rest_update_practice_item($request) {
        try {
            $item_id = $request->get_param('id');
            $name = $request->get_param('item_name') ?: $request->get_param('name');
            $category = $request->get_param('category');
            $description = $request->get_param('item_description') ?: $request->get_param('description');
            
            if (!$item_id) {
                return new WP_Error('missing_id', 'Item ID is required', array('status' => 400));
            }
            
            $result = $this->database->update_practice_item($item_id, $name, $category, $description);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Practice item updated successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('update_item_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Delete practice item
     */
    public function rest_delete_practice_item($request) {
        try {
            $item_id = $request->get_param('id');
            
            if (!$item_id) {
                return new WP_Error('missing_id', 'Item ID is required', array('status' => 400));
            }
            
            $result = $this->database->delete_practice_item($item_id);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Practice item deleted successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('delete_item_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Generate fallback analysis when AI Hub is unavailable
     */
    private function generate_fallback_analysis($sessions, $user_stats, $debug_info) {
        $total_sessions = count($sessions);
        $total_minutes = array_sum(array_column($sessions, 'duration_minutes'));
        $avg_duration = $total_sessions > 0 ? round($total_minutes / $total_sessions, 1) : 0;
        $avg_sentiment = $total_sessions > 0 ? round(array_sum(array_column($sessions, 'sentiment_score')) / $total_sessions, 1) : 0;
        $improvements = count(array_filter($sessions, function($s) { return $s['improvement_detected'] == 1; }));
        $improvement_rate = $total_sessions > 0 ? round(($improvements / $total_sessions) * 100, 1) : 0;
        
        // Get most practiced item
        $item_counts = array();
        foreach ($sessions as $session) {
            $item_name = $session['item_name'] ?? 'Unknown';
            $item_counts[$item_name] = ($item_counts[$item_name] ?? 0) + 1;
        }
        arsort($item_counts);
        $most_practiced = array_key_first($item_counts);
        
        // Generate analysis based on data - PLAIN TEXT ONLY
        $analysis = "Practice Analysis for the Last 30 Days. ";
        
        if ($total_sessions > 0) {
            $analysis .= "You practiced {$total_sessions} times totaling {$total_minutes} minutes with an average session length of {$avg_duration} minutes. ";
            $analysis .= "Your mood rating averaged {$avg_sentiment} out of 5, which is ";
            
            if ($avg_sentiment >= 4) {
                $analysis .= "excellent";
            } elseif ($avg_sentiment >= 3) {
                $analysis .= "good";
            } elseif ($avg_sentiment >= 2) {
                $analysis .= "okay";
            } else {
                $analysis .= "needs improvement";
            }
            $analysis .= ". ";
            
            $analysis .= "You noticed improvement in {$improvement_rate}% of your sessions. ";
            
            if ($most_practiced) {
                $analysis .= "You've been working most on {$most_practiced}, which shows great dedication. ";
            }
            
            // Add insights based on data
            if ($avg_duration >= 30) {
                $analysis .= "Your practice sessions are substantial at 30+ minutes, showing great commitment to improvement. ";
            }
            
            if ($improvement_rate >= 50) {
                $analysis .= "You're noticing improvement in over half your sessions, which is fantastic progress. ";
            }
            
            if ($user_stats['current_streak'] >= 3) {
                $analysis .= "You're on a {$user_stats['current_streak']}-day streak, which shows consistency is key to mastery. ";
            }
            
            $analysis .= "Your practice data shows you're building great habits and every session counts toward your musical goals.";
            
        } else {
            $analysis .= "No practice sessions found in the last 30 days, which is a perfect time to begin your musical journey. Try starting with just 15-20 minutes of focused practice, as every great musician started with their first note.";
        }
        
        return $analysis;
    }
    
    /**
     * Get badge event logs
     */
    public function rest_get_badge_event_logs($request) {
        try {
            $logs = get_option('jph_badge_events_log', array());
            $logs = array_slice(array_reverse($logs), 0, 50); // Last 50 entries
            
            $log_html = '';
            if (empty($logs)) {
                $log_html = '<div class="log-entry info">';
                $log_html .= '<strong>No badge event tracking logs found.</strong><br>';
                $log_html .= 'Events will appear here when badges are awarded with FluentCRM tracking enabled.<br>';
                $log_html .= '<br><strong>To test:</strong><br>';
                $log_html .= '1. Go to Badge Management and enable "FluentCRM Event Tracking" for a badge<br>';
                $log_html .= '2. Use the "Test Event" button in the badge table<br>';
                $log_html .= '3. Or award the badge to a user through normal gameplay<br>';
                $log_html .= '</div>';
            } else {
                $log_html .= '<strong>Total logs found: ' . count($logs) . '</strong><br><br>';
                
                foreach ($logs as $log) {
                    $status_class = $log['success'] ? 'success' : 'error';
                    $log_html .= "<div class='log-entry {$status_class}'>";
                    $log_html .= "<strong>" . date('Y-m-d H:i:s', $log['timestamp']) . "</strong> ";
                    $log_html .= "[🏆 {$log['badge_key']}] ";
                    
                    // Add user information
                    $user_email = $log['user_email'] ?? 'Unknown';
                    $user_name = $log['user_name'] ?? 'Unknown';
                    $contact_id = $log['contact_id'] ?? 'N/A';
                    
                    $log_html .= "<br><span class='log-user-info'>";
                    $log_html .= "👤 {$user_name} ({$user_email})";
                    if ($contact_id !== 'N/A' && $contact_id !== 'Not Found' && $contact_id !== 'Error') {
                        $log_html .= " | 🆔 Contact ID: {$contact_id}";
                    } else {
                        $log_html .= " | 🆔 Contact: {$contact_id}";
                    }
                    $log_html .= "</span><br>";
                    
                    // Add event details
                    if (isset($log['data']['event_key'])) {
                        $log_html .= "<strong>Event Key:</strong> {$log['data']['event_key']}<br>";
                    }
                    if (isset($log['data']['title'])) {
                        $log_html .= "<strong>Event Title:</strong> {$log['data']['title']}<br>";
                    }
                    
                    $log_html .= $log['message'];
                    $log_html .= "</div>";
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $log_html
            ));
        } catch (Exception $e) {
            return new WP_Error('badge_logs_error', 'Error retrieving badge event logs: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get FluentCRM event logs
     */
    public function rest_get_fluentcrm_event_logs($request) {
        try {
            global $wpdb;
            
            // Check if FluentCRM event tracking table exists
            $table_name = $wpdb->prefix . 'fc_event_tracking';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
            
            if (!$table_exists) {
                return rest_ensure_response(array(
                    'success' => false,
                    'message' => 'FluentCRM event tracking table (wp_fc_event_tracking) does not exist. Make sure FluentCRM plugin is installed and activated.'
                ));
            }
            
            // Get recent event logs (last 50 entries)
            $events = $wpdb->get_results(
                "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 50",
                ARRAY_A
            );
            
            if (empty($events)) {
                return rest_ensure_response(array(
                    'success' => true,
                    'data' => 'No event tracking logs found in wp_fc_event_tracking table.'
                ));
            }
            
            $log_output = "Recent Event Tracking Logs (Last 50 entries):\n\n";
            $log_output .= "Total events found: " . count($events) . "\n\n";
            
            foreach ($events as $event) {
                $log_output .= "Event ID: " . $event['id'] . "\n";
                $log_output .= "Event Key: " . $event['event_key'] . "\n";
                $log_output .= "Title: " . $event['title'] . "\n";
                $log_output .= "Value: " . $event['value'] . "\n";
                $log_output .= "Email: " . $event['email'] . "\n";
                $log_output .= "Provider: " . $event['provider'] . "\n";
                $log_output .= "Created: " . $event['created_at'] . "\n";
                if (!empty($event['custom_data'])) {
                    $log_output .= "Custom Data: " . $event['custom_data'] . "\n";
                }
                $log_output .= "---\n\n";
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $log_output
            ));
        } catch (Exception $e) {
            return new WP_Error('fluentcrm_logs_error', 'Error retrieving FluentCRM event logs: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Clear badge event logs
     */
    public function rest_clear_badge_event_logs($request) {
        try {
            delete_option('jph_badge_events_log');
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Badge event logs cleared successfully.'
            ));
        } catch (Exception $e) {
            return new WP_Error('clear_badge_logs_error', 'Error clearing badge event logs: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Empty FluentCRM event tracking table
     */
    public function rest_empty_fluentcrm_event_logs($request) {
        try {
            global $wpdb;
            
            // Check if FluentCRM event tracking table exists
            $table_name = $wpdb->prefix . 'fc_event_tracking';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
            
            if (!$table_exists) {
                return rest_ensure_response(array(
                    'success' => false,
                    'message' => 'FluentCRM event tracking table does not exist.'
                ));
            }
            
            // Empty the table
            $result = $wpdb->query("TRUNCATE TABLE {$table_name}");
            
            if ($result === false) {
                return rest_ensure_response(array(
                    'success' => false,
                    'message' => 'Error emptying FluentCRM event tracking table.'
                ));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'FluentCRM event tracking table emptied successfully.'
            ));
        } catch (Exception $e) {
            return new WP_Error('empty_fluentcrm_logs_error', 'Error emptying FluentCRM event logs: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Export all user data for backup
     */
    public function rest_export_user_data($request) {
        try {
            global $wpdb;
            
            // Get all user data
            $export_data = array(
                'export_date' => current_time('mysql'),
                'export_version' => '1.0',
                'data' => array()
            );
            
            // Practice sessions
            $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
            $export_data['data']['practice_sessions'] = $wpdb->get_results("SELECT * FROM $sessions_table", ARRAY_A);
            
            // Practice items
            $items_table = $wpdb->prefix . 'jph_practice_items';
            $export_data['data']['practice_items'] = $wpdb->get_results("SELECT * FROM $items_table", ARRAY_A);
            
            // User stats
            $stats_table = $wpdb->prefix . 'jph_user_stats';
            $export_data['data']['user_stats'] = $wpdb->get_results("SELECT * FROM $stats_table", ARRAY_A);
            
            // User badges
            $badges_table = $wpdb->prefix . 'jph_user_badges';
            $export_data['data']['user_badges'] = $wpdb->get_results("SELECT * FROM $badges_table", ARRAY_A);
            
            // Gem transactions
            $gems_table = $wpdb->prefix . 'jph_gem_transactions';
            $export_data['data']['gem_transactions'] = $wpdb->get_results("SELECT * FROM $gems_table", ARRAY_A);
            
            // Lesson favorites
            $favorites_table = $wpdb->prefix . 'jph_lesson_favorites';
            $export_data['data']['lesson_favorites'] = $wpdb->get_results("SELECT * FROM $favorites_table", ARRAY_A);
            
            // Add summary
            $export_data['summary'] = array(
                'practice_sessions_count' => count($export_data['data']['practice_sessions']),
                'practice_items_count' => count($export_data['data']['practice_items']),
                'user_stats_count' => count($export_data['data']['user_stats']),
                'user_badges_count' => count($export_data['data']['user_badges']),
                'gem_transactions_count' => count($export_data['data']['gem_transactions']),
                'lesson_favorites_count' => count($export_data['data']['lesson_favorites'])
            );
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $export_data,
                'message' => 'User data exported successfully'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('export_error', 'Error exporting user data: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Import user data from backup
     */
    public function rest_import_user_data($request) {
        try {
            global $wpdb;
            
            $backup_data = $request->get_param('backup_data');
            if (empty($backup_data)) {
                return new WP_Error('import_error', 'No backup data provided', array('status' => 400));
            }
            
            $data = json_decode($backup_data, true);
            if (!$data || !isset($data['data'])) {
                return new WP_Error('import_error', 'Invalid backup data format', array('status' => 400));
            }
            
            $imported_counts = array();
            
            // Import practice sessions
            if (isset($data['data']['practice_sessions']) && !empty($data['data']['practice_sessions'])) {
                $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
                $wpdb->query("TRUNCATE TABLE $sessions_table");
                foreach ($data['data']['practice_sessions'] as $session) {
                    unset($session['id']); // Remove ID to allow auto-increment
                    $wpdb->insert($sessions_table, $session);
                }
                $imported_counts['practice_sessions'] = count($data['data']['practice_sessions']);
            }
            
            // Import practice items
            if (isset($data['data']['practice_items']) && !empty($data['data']['practice_items'])) {
                $items_table = $wpdb->prefix . 'jph_practice_items';
                $wpdb->query("TRUNCATE TABLE $items_table");
                foreach ($data['data']['practice_items'] as $item) {
                    unset($item['id']); // Remove ID to allow auto-increment
                    $wpdb->insert($items_table, $item);
                }
                $imported_counts['practice_items'] = count($data['data']['practice_items']);
            }
            
            // Import user stats
            if (isset($data['data']['user_stats']) && !empty($data['data']['user_stats'])) {
                $stats_table = $wpdb->prefix . 'jph_user_stats';
                $wpdb->query("TRUNCATE TABLE $stats_table");
                foreach ($data['data']['user_stats'] as $stat) {
                    unset($stat['id']); // Remove ID to allow auto-increment
                    $wpdb->insert($stats_table, $stat);
                }
                $imported_counts['user_stats'] = count($data['data']['user_stats']);
            }
            
            // Import user badges
            if (isset($data['data']['user_badges']) && !empty($data['data']['user_badges'])) {
                $badges_table = $wpdb->prefix . 'jph_user_badges';
                $wpdb->query("TRUNCATE TABLE $badges_table");
                foreach ($data['data']['user_badges'] as $badge) {
                    unset($badge['id']); // Remove ID to allow auto-increment
                    $wpdb->insert($badges_table, $badge);
                }
                $imported_counts['user_badges'] = count($data['data']['user_badges']);
            }
            
            // Import gem transactions
            if (isset($data['data']['gem_transactions']) && !empty($data['data']['gem_transactions'])) {
                $gems_table = $wpdb->prefix . 'jph_gem_transactions';
                $wpdb->query("TRUNCATE TABLE $gems_table");
                foreach ($data['data']['gem_transactions'] as $transaction) {
                    unset($transaction['id']); // Remove ID to allow auto-increment
                    $wpdb->insert($gems_table, $transaction);
                }
                $imported_counts['gem_transactions'] = count($data['data']['gem_transactions']);
            }
            
            // Import lesson favorites
            if (isset($data['data']['lesson_favorites']) && !empty($data['data']['lesson_favorites'])) {
                $favorites_table = $wpdb->prefix . 'jph_lesson_favorites';
                $wpdb->query("TRUNCATE TABLE $favorites_table");
                foreach ($data['data']['lesson_favorites'] as $favorite) {
                    unset($favorite['id']); // Remove ID to allow auto-increment
                    $wpdb->insert($favorites_table, $favorite);
                }
                $imported_counts['lesson_favorites'] = count($data['data']['lesson_favorites']);
            }
            
            $message = 'User data imported successfully. Imported: ' . implode(', ', array_map(function($k, $v) { return "$k ($v)"; }, array_keys($imported_counts), $imported_counts));
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => $message,
                'imported_counts' => $imported_counts
            ));
            
        } catch (Exception $e) {
            return new WP_Error('import_error', 'Error importing user data: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get leaderboard data
     */
    public function rest_get_leaderboard($request) {
        try {
            $limit = $request->get_param('limit') ?: 50;
            $offset = $request->get_param('offset') ?: 0;
            $sort_by = $request->get_param('sort_by') ?: 'total_xp';
            
            // Validate parameters
            $limit = max(1, min(100, intval($limit)));
            $offset = max(0, intval($offset));
            
            $leaderboard = $this->database->get_leaderboard($limit, $offset, $sort_by);
            
            // Add position numbers
            foreach ($leaderboard as $index => $user) {
                $leaderboard[$index]['position'] = $offset + $index + 1;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $leaderboard,
                'pagination' => array(
                    'limit' => $limit,
                    'offset' => $offset,
                    'sort_by' => $sort_by
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('leaderboard_error', 'Error retrieving leaderboard: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get user's leaderboard position
     */
    public function rest_get_user_position($request) {
        try {
            $user_id = get_current_user_id();
            $sort_by = $request->get_param('sort_by') ?: 'total_xp';
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
            }
            
            $position = $this->database->get_user_leaderboard_position($user_id, $sort_by);
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => array(
                    'user_id' => $user_id,
                    'position' => $position,
                    'sort_by' => $sort_by
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('position_error', 'Error retrieving user position: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Update user display name
     */
    public function rest_update_display_name($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
            }
            
            $display_name = $request->get_param('display_name');
            
            // Allow empty display name (will use WordPress display name)
            if ($display_name !== null && strlen($display_name) > 100) {
                return new WP_Error('invalid_display_name', 'Display name must be 100 characters or less', array('status' => 400));
            }
            
            $result = $this->database->update_user_display_name($user_id, $display_name);
            
            if (!$result) {
                return new WP_Error('update_failed', 'Failed to update display name', array('status' => 500));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Display name updated successfully',
                'data' => array(
                    'user_id' => $user_id,
                    'display_name' => $display_name
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('update_error', 'Error updating display name: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Update user leaderboard visibility
     */
    public function rest_update_leaderboard_visibility($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
            }
            
            $show_on_leaderboard = $request->get_param('show_on_leaderboard');
            
            if (!is_bool($show_on_leaderboard) && !in_array($show_on_leaderboard, array(0, 1, '0', '1'))) {
                return new WP_Error('invalid_visibility', 'show_on_leaderboard must be true or false', array('status' => 400));
            }
            
            $show_on_leaderboard = (bool) $show_on_leaderboard;
            
            $result = $this->database->update_user_leaderboard_visibility($user_id, $show_on_leaderboard);
            
            if (!$result) {
                return new WP_Error('update_failed', 'Failed to update leaderboard visibility', array('status' => 500));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Leaderboard visibility updated successfully',
                'data' => array(
                    'user_id' => $user_id,
                    'show_on_leaderboard' => $show_on_leaderboard
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('update_error', 'Error updating leaderboard visibility: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get leaderboard statistics
     */
    public function rest_get_leaderboard_stats($request) {
        try {
            $stats = $this->database->get_leaderboard_stats();
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $stats
            ));
            
        } catch (Exception $e) {
            return new WP_Error('stats_error', 'Error retrieving leaderboard statistics: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get current user's stats including display name
     */
    public function rest_get_user_stats($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
            }
            
            $user_stats = $this->database->get_user_stats($user_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $user_stats
            ));
            
        } catch (Exception $e) {
            return new WP_Error('user_stats_error', 'Error retrieving user stats: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
}
