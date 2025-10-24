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
    private $logger;
    private $rate_limiter;
    private $cache;
    private $validator;
    private $audit_logger;
    
    public function __construct() {
        $this->database = new JPH_Database();
        $this->logger = JPH_Logger::get_instance();
        $this->rate_limiter = JPH_Rate_Limiter::get_instance();
        $this->cache = JPH_Cache::get_instance();
        $this->validator = JPH_Validator::get_instance();
        $this->audit_logger = JPH_Audit_Logger::get_instance();
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Register AJAX handlers for JPC modal functionality
        add_action('wp_ajax_jpc_verify_step_completion', array($this, 'ajax_verify_step_completion'));
        add_action('wp_ajax_jpc_mark_step_complete', array($this, 'ajax_mark_step_complete'));
        add_action('wp_ajax_jpc_get_fvplayer', array($this, 'ajax_get_fvplayer'));
        add_action('wp_ajax_jpc_submit_milestone', array($this, 'ajax_submit_milestone'));
        add_action('wp_ajax_ai_cleanup_feedback', array($this, 'ajax_ai_cleanup_feedback'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Test endpoint (admin only)
        register_rest_route('aph/v1', '/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_test'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Debug endpoint to check if routes are registered (admin only)
        register_rest_route('aph/v1', '/debug/routes', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_routes'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Leaderboard endpoints
        register_rest_route('aph/v1', '/leaderboard', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_leaderboard'),
            'permission_callback' => array($this, 'check_rate_limit_permission')
        ));
        
        register_rest_route('aph/v1', '/leaderboard/position', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_user_position'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/leaderboard/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_leaderboard_stats'),
            'permission_callback' => array($this, 'check_rate_limit_permission')
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
        
        // JPC endpoints
        register_rest_route('aph/v1', '/jpc/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_jpc_test'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('aph/v1', '/jpc/debug', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_jpc_debug'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('aph/v1', '/jpc/complete', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_mark_jpc_complete'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('aph/v1', '/jpc/reset', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_jpc_reset'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('aph/v1', '/jpc/debug-complete', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_jpc_debug_complete'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('aph/v1', '/jpc/progress/(?P<user_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_jpc_progress'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('aph/v1', '/jpc/migrate', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_migrate_jpc_data'),
            'permission_callback' => array($this, 'rest_check_admin_permissions'),
            'args' => array(
                'dry_run' => array(
                    'type' => 'string',
                    'default' => 'false',
                    'description' => 'If true, only analyze without making changes'
                ),
                'batch_size' => array(
                    'type' => 'integer',
                    'default' => 100,
                    'description' => 'Number of records to process per batch'
                ),
                'skip_existing' => array(
                    'type' => 'string',
                    'default' => 'true',
                    'description' => 'If true, skip records that already exist'
                ),
                'include_milestones' => array(
                    'type' => 'string',
                    'default' => 'true',
                    'description' => 'If true, include milestone submissions in migration'
                ),
                'log_level' => array(
                    'type' => 'string',
                    'default' => 'info',
                    'description' => 'Logging level (debug, info, warning, error)'
                )
            )
        ));
        
        // Register JPC progress analysis endpoint
        register_rest_route('aph/v1', '/jpc/analyze-progress', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_analyze_jpc_progress'),
            'permission_callback' => array($this, 'rest_check_admin_permissions')
        ));
        
        // Register JPC assignment fix endpoint
        register_rest_route('aph/v1', '/jpc/fix-assignments', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_fix_jpc_assignments'),
            'permission_callback' => array($this, 'rest_check_admin_permissions')
        ));
        
        // Register JPC specific student fix endpoint
        register_rest_route('aph/v1', '/jpc/fix-specific-student', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_fix_specific_student'),
            'permission_callback' => array($this, 'rest_check_admin_permissions')
        ));
        
        // Register JPC student self-fix endpoint
        register_rest_route('aph/v1', '/jpc/fix-my-progress', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_fix_my_progress'),
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
        
        register_rest_route('aph/v1', '/lesson-favorites', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_add_lesson_favorite'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/lesson-favorites/remove', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_remove_lesson_favorite'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/lesson-favorites/check', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_check_lesson_favorite'),
            'permission_callback' => array($this, 'check_user_permission')
        ));

        // Beta disclaimer endpoints
        register_rest_route('aph/v1', '/beta-disclaimer/shown', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_mark_beta_disclaimer_shown'),
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
        
        register_rest_route('aph/v1', '/admin/lesson-favorites', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_lesson_favorite'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/admin/lesson-favorites', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_lesson_favorite'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/export-lesson-favorites', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_export_lesson_favorites'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Admin settings endpoints
        register_rest_route('aph/v1', '/admin/clear-all-user-data', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_clear_all_user_data'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Event tracking endpoints
        
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
        
        register_rest_route('aph/v1', '/manual-badge-check', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_manual_badge_check'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/debug-frontend-badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_frontend_badges'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/fix-missing-badges', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_fix_missing_badges'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/get-badges-list', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_badges_list'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/debug-database-tables', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_database_tables'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/debug-fluentcrm-events', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_fluentcrm_events'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/remove-user-badge', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_remove_user_badge'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('aph/v1', '/debug-gem-balance', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_gem_balance'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Analytics endpoints
        register_rest_route('aph/v1', '/analytics/overview', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_analytics_overview'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/analytics/students', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_analytics_students'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/analytics/send-outreach', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_send_outreach_email'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/analytics/export-at-risk', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_export_at_risk_students'),
            'permission_callback' => array($this, 'check_admin_permission')
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
            'permission_callback' => array($this, 'check_admin_permission')
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
        
        register_rest_route('aph/v1', '/admin/generate-test-students', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_generate_test_students'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/admin/clear-test-data', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_clear_test_data'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/admin/clear-cache', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_clear_cache'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('aph/v1', '/admin/debug-timezone', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_timezone'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
    }
    
    /**
     * Check user permission
     */
    public function check_user_permission($request = null) {
        // For WP Engine, we need to handle authentication differently
        // Check if we have a valid nonce first
        if ($request) {
            $nonce = $request->get_header('X-WP-Nonce');
            if ($nonce && wp_verify_nonce($nonce, 'wp_rest')) {
                // If nonce is valid, we can trust the user is authenticated
                return true;
            }
        }
        
        // Fallback: check if user is logged in normally
        if (is_user_logged_in()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check admin permission
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * REST API admin permission callback
     */
    public function rest_check_admin_permissions() {
        return $this->check_admin_permission();
    }
    
    /**
     * Check rate limit for public endpoints
     */
    public function check_rate_limit_permission($request) {
        $endpoint = $request->get_route();
        $user_id = get_current_user_id();
        
        $rate_limit_check = $this->rate_limiter->check_rate_limit($endpoint, $user_id);
        
        if (is_wp_error($rate_limit_check)) {
            return $rate_limit_check;
        }
        
        return true;
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
        
        // Handle sentiment score - can be numeric (1-5) or string
        $sentiment_score = 3; // default
        
        if (isset($params['sentiment_score'])) {
            // Frontend sends numeric score directly
            $sentiment_score = intval($params['sentiment_score']);
            // Ensure it's within valid range
            $sentiment_score = max(1, min(5, $sentiment_score));
        } elseif (isset($params['sentiment'])) {
            // Legacy support for string-based sentiment
            $sentiment_scores = array(
                'excellent' => 5,
                'good' => 4,
                'okay' => 3,
                'challenging' => 2,
                'frustrating' => 1
            );
            $sentiment_score = isset($sentiment_scores[$params['sentiment']]) ? $sentiment_scores[$params['sentiment']] : 3;
        }
        
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
            $this->logger->error('Failed to add XP for user', array('user_id' => $user_id));
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
        
        $this->logger->debug('Practice Sessions API request', array(
            'user_id' => $user_id,
            'limit' => $limit,
            'offset' => $offset,
            'start_date' => $start_date,
            'end_date' => $end_date
        ));
        
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
        
        $this->logger->debug('Practice Sessions API response', array('session_count' => count($sessions)));
        
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
                'category' => sanitize_text_field($params['category']),
                'xp_reward' => intval($params['xp_reward'] ?? 0),
                'gem_reward' => intval($params['gem_reward'] ?? 0),
                'criteria_type' => sanitize_text_field($params['criteria_type']),
                'criteria_value' => intval($params['criteria_value']),
                'is_active' => 1,
                'display_order' => intval($params['display_order'] ?? 999),
                'image_url' => esc_url_raw($params['image_url'] ?? ''),
                'fluentcrm_enabled' => intval($params['fluentcrm_enabled'] ?? 0),
                'fluentcrm_event_key' => sanitize_text_field($params['fluentcrm_event_key'] ?? ''),
                'fluentcrm_event_title' => sanitize_text_field($params['fluentcrm_event_title'] ?? ''),
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
            if (isset($body['image_url'])) {
                $badge_data['image_url'] = esc_url_raw($body['image_url']);
            }
            
            // Add FluentCRM fields
            if (isset($body['fluentcrm_enabled'])) {
                $badge_data['fluentcrm_enabled'] = intval($body['fluentcrm_enabled']);
            }
            if (isset($body['fluentcrm_event_key'])) {
                $badge_data['fluentcrm_event_key'] = sanitize_text_field($body['fluentcrm_event_key']);
            }
            if (isset($body['fluentcrm_event_title'])) {
                $badge_data['fluentcrm_event_title'] = sanitize_text_field($body['fluentcrm_event_title']);
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
     * Add lesson favorite
     */
    public function rest_add_lesson_favorite($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to add lesson favorites', array('status' => 401));
            }
            
            $params = $request->get_params();
            $title = sanitize_text_field($params['title']);
            $url = esc_url_raw($params['url']);
            $category = sanitize_text_field($params['category'] ?? 'lesson');
            $description = sanitize_textarea_field($params['description'] ?? '');
            
            // Validate required fields
            if (empty($title) || empty($url)) {
                return new WP_Error('missing_fields', 'Title and URL are required', array('status' => 400));
            }
            
            // Validate category
            $allowed_categories = array('lesson', 'technique', 'theory', 'ear-training', 'repertoire', 'improvisation', 'other');
            if (!in_array($category, $allowed_categories)) {
                $category = 'lesson';
            }
            
            $result = $this->database->add_lesson_favorite($user_id, $title, $url, $category, $description);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'favorite_id' => $result,
                'message' => 'Lesson favorite added successfully'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('add_lesson_favorite_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Remove lesson favorite
     */
    public function rest_remove_lesson_favorite($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to remove lesson favorites', array('status' => 401));
            }
            
            $params = $request->get_params();
            $title = sanitize_text_field($params['title']);
            
            // Validate required fields
            if (empty($title)) {
                return new WP_Error('missing_fields', 'Title is required', array('status' => 400));
            }
            
            $result = $this->database->remove_lesson_favorite($user_id, $title);
            
            if (!$result) {
                return new WP_Error('remove_failed', 'Failed to remove lesson favorite', array('status' => 500));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Lesson favorite removed successfully'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('remove_lesson_favorite_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Check if lesson is favorited
     */
    public function rest_check_lesson_favorite($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to check lesson favorites', array('status' => 401));
            }
            
            $params = $request->get_params();
            $title = sanitize_text_field($params['title']);
            
            // Validate required fields
            if (empty($title)) {
                return new WP_Error('missing_fields', 'Title is required', array('status' => 400));
            }
            
            $favorite_id = $this->database->is_lesson_favorited($user_id, $title);
            
            return rest_ensure_response(array(
                'success' => true,
                'is_favorited' => $favorite_id !== false,
                'favorite_id' => $favorite_id
            ));
            
        } catch (Exception $e) {
            return new WP_Error('check_lesson_favorite_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Mark beta disclaimer as shown for user
     */
    public function rest_mark_beta_disclaimer_shown($request) {
        try {
            $user_id = get_current_user_id();
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in', array('status' => 401));
            }

            // Mark disclaimer as shown using user meta
            update_user_meta($user_id, 'jph_beta_disclaimer_shown', true);

            $this->logger->info('Beta disclaimer marked as shown', array(
                'user_id' => $user_id,
                'timestamp' => current_time('mysql')
            ));

            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Beta disclaimer marked as shown'
            ));

        } catch (Exception $e) {
            $this->logger->error('Failed to mark beta disclaimer as shown', array(
                'error' => $e->getMessage(),
                'user_id' => get_current_user_id()
            ));
            return new WP_Error('mark_disclaimer_shown_error', 'Error: ' . $e->getMessage(), array('status' => 500));
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
            error_log('=== SHIELD PURCHASE DEBUG START ===');
            error_log('JPH: Shield purchase attempt for user ' . $user_id);
            error_log('JPH: User stats: ' . print_r($user_stats, true));
            $current_shields = $user_stats['streak_shield_count'] ?? 0;
            $gem_balance = $user_stats['gems_balance'] ?? 0;
            $shield_cost = 50;
            
            error_log('JPH: Current shields: ' . $current_shields);
            error_log('JPH: Current gem balance: ' . $gem_balance);
            error_log('JPH: Shield cost: ' . $shield_cost);
            
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
            
            error_log('JPH: Calculating new values:');
            error_log('JPH: New shield count: ' . $new_shield_count . ' (was ' . $current_shields . ')');
            error_log('JPH: New gem balance: ' . $new_gem_balance . ' (was ' . $gem_balance . ', deducted ' . $shield_cost . ')');
            
            // Update user stats
            $update_data = array(
                'streak_shield_count' => $new_shield_count,
                'gems_balance' => $new_gem_balance
            );
            
            error_log('JPH: Update data: ' . print_r($update_data, true));
            $result = $database->update_user_stats($user_id, $update_data);
            error_log('JPH: Update result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            
            if ($result) {
                // Record the gem transaction
                $database->record_gems_transaction($user_id, 'debit', -$shield_cost, 'streak_shield_purchase', 'Purchased streak shield');
                
                error_log('JPH: Shield purchase completed successfully');
                error_log('JPH: Final values - Shields: ' . $new_shield_count . ', Gems: ' . $new_gem_balance);
                error_log('=== SHIELD PURCHASE DEBUG END ===');
                
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Shield purchased successfully!',
                    'data' => array(
                        'new_shield_count' => $new_shield_count,
                        'new_gem_balance' => $new_gem_balance
                    )
                ));
            } else {
                error_log('JPH: Shield purchase FAILED - database update failed');
                error_log('=== SHIELD PURCHASE DEBUG END ===');
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
                $wpdb->prefix . 'jph_gems_transactions',
                $wpdb->prefix . 'jph_lesson_favorites',
                $wpdb->prefix . 'jph_jpc_user_assignments',
                $wpdb->prefix . 'jph_jpc_user_progress',
                $wpdb->prefix . 'jph_jpc_milestone_submissions',
                $wpdb->prefix . 'jph_audit_logs'
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
            
            // Get recent gem transactions for debugging
            $recent_transactions = $wpdb->get_results($wpdb->prepare("
                SELECT * FROM {$wpdb->prefix}jph_gems_transactions 
                WHERE user_id = %d 
                ORDER BY created_at DESC 
                LIMIT 10
            ", $user_id), ARRAY_A);
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => array(
                    'user_id' => $user_id,
                    'user_stats' => $user_stats,
                    'user_badges' => $user_badges,
                    'practice_sessions_count' => (int) $practice_sessions,
                    'current_gem_balance' => $user_stats ? $user_stats['gems_balance'] : 'No stats record',
                    'recent_gem_transactions' => $recent_transactions,
                    'debug_timestamp' => current_time('mysql')
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
            $user_id = $request->get_param('user_id') ?: get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('missing_user_id', 'User ID is required', array('status' => 400));
            }
            
            // Get user stats and badges
            $user_stats = $this->database->get_user_stats($user_id);
            $user_badges = $this->database->get_user_badges($user_id);
            $all_badges = $this->database->get_badges(true);
            
            // Test badge assignment logic
            $gamification = new APH_Gamification();
            $test_results = array(
                'test_user_id' => $user_id,
                'user_stats' => $user_stats,
                'current_badges' => count($user_badges),
                'total_available_badges' => count($all_badges),
                'active_badges' => count(array_filter($all_badges, function($b) { return $b['is_active']; })),
                'assignment_logic' => 'Working correctly',
                'gamification_system' => 'Operational',
                'badge_analysis' => array()
            );
            
            // Analyze each badge to see if user should have it
            foreach ($all_badges as $badge) {
                $has_badge = in_array($badge['badge_key'], array_column($user_badges, 'badge_key'));
                $should_have = false;
                $criteria_type = $badge['criteria_type'] ?? '';
                $criteria_value = intval($badge['criteria_value'] ?? 0);
                
                switch ($criteria_type) {
                    case 'practice_sessions':
                        $should_have = $user_stats['total_sessions'] >= $criteria_value;
                        break;
                    case 'total_xp':
                        $should_have = $user_stats['total_xp'] >= $criteria_value;
                        break;
                    case 'streak':
                        $should_have = $user_stats['current_streak'] >= $criteria_value;
                        break;
                    case 'long_session_count':
                        // Would need session data to check this
                        $should_have = false;
                        break;
                    case 'time_of_day':
                    case 'comeback':
                        // Would need session data to check this
                        $should_have = false;
                        break;
                }
                
                $test_results['badge_analysis'][] = array(
                    'badge_key' => $badge['badge_key'],
                    'name' => $badge['name'],
                    'criteria_type' => $criteria_type,
                    'criteria_value' => $criteria_value,
                    'user_has_badge' => $has_badge,
                    'should_have_badge' => $should_have,
                    'status' => $has_badge ? 'earned' : ($should_have ? 'missing' : 'not_qualified'),
                    'is_active' => $badge['is_active']
                );
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $test_results,
                'message' => 'Badge assignment test completed for user ' . $user_id
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
     * Manual badge check for debugging
     */
    public function rest_manual_badge_check($request) {
        try {
            $user_id = $request->get_param('user_id') ?: get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('missing_user_id', 'User ID is required', array('status' => 400));
            }
            
            // Initialize gamification system
            $gamification = new APH_Gamification();
            
            // Get current user stats and badges before checking
            $user_stats_before = $this->database->get_user_stats($user_id);
            $user_badges_before = $this->database->get_user_badges($user_id);
            
            // Run badge check
            $newly_awarded = $gamification->check_and_award_badges($user_id);
            
            // Get stats and badges after checking
            $user_stats_after = $this->database->get_user_stats($user_id);
            $user_badges_after = $this->database->get_user_badges($user_id);
            
            // Get all badges for reference
            $all_badges = $this->database->get_badges(true);
            
            // Get practice sessions count
            global $wpdb;
            $practice_sessions_count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) FROM {$wpdb->prefix}jph_practice_sessions 
                WHERE user_id = %d
            ", $user_id));
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => array(
                    'user_id' => $user_id,
                    'practice_sessions_count' => (int) $practice_sessions_count,
                    'user_stats_before' => $user_stats_before,
                    'user_stats_after' => $user_stats_after,
                    'user_badges_before' => $user_badges_before,
                    'user_badges_after' => $user_badges_after,
                    'newly_awarded_badges' => $newly_awarded,
                    'all_available_badges' => $all_badges,
                    'badge_check_completed' => true
                ),
                'message' => 'Manual badge check completed. ' . count($newly_awarded) . ' new badges awarded.'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('manual_badge_check_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Debug frontend badge display
     */
    public function rest_debug_frontend_badges($request) {
        try {
            $user_id = $request->get_param('user_id') ?: get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('missing_user_id', 'User ID is required', array('status' => 400));
            }
            
            // Get user badges as they would appear on frontend
            $user_badges = $this->database->get_user_badges($user_id);
            
            // Get all badges for comparison
            $all_badges = $this->database->get_badges(true);
            
            // Simulate frontend badge display logic
            $frontend_badges = array();
            $earned_badge_keys = array_column($user_badges, 'badge_key');
            
            foreach ($all_badges as $badge) {
                $is_earned = in_array($badge['badge_key'], $earned_badge_keys);
                $earned_badge = null;
                
                if ($is_earned) {
                    $earned_badge = $user_badges[array_search($badge['badge_key'], $earned_badge_keys)];
                }
                
                $frontend_badges[] = array(
                    'badge_key' => $badge['badge_key'],
                    'name' => $badge['name'],
                    'description' => $badge['description'],
                    'image_url' => $badge['image_url'],
                    'category' => $badge['category'],
                    'xp_reward' => $badge['xp_reward'],
                    'gem_reward' => $badge['gem_reward'],
                    'is_earned' => $is_earned,
                    'earned_date' => $is_earned ? $earned_badge['earned_at'] : null,
                    'display_status' => $is_earned ? 'earned' : 'locked'
                );
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => array(
                    'user_id' => $user_id,
                    'total_badges' => count($all_badges),
                    'earned_badges' => count($user_badges),
                    'frontend_badges' => $frontend_badges,
                    'user_badges_raw' => $user_badges,
                    'all_badges_raw' => $all_badges
                ),
                'message' => 'Frontend badge display debug completed'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('debug_frontend_badges_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Fix missing badge records
     */
    public function rest_fix_missing_badges($request) {
        try {
            $user_id = $request->get_param('user_id') ?: get_current_user_id();
            $badge_key = $request->get_param('badge_key');
            
            if (!$user_id) {
                return new WP_Error('missing_user_id', 'User ID is required', array('status' => 400));
            }
            
            global $wpdb;
            
            if ($badge_key) {
                // Fix specific badge - use full gamification process
                $gamification = new APH_Gamification();
                
                // Get badge details
                $badge = $this->database->get_badge_by_key($badge_key);
                if (!$badge) {
                    return new WP_Error('badge_not_found', "Badge '{$badge_key}' not found", array('status' => 404));
                }
                
                // Get user stats before
                $user_stats_before = $this->database->get_user_stats($user_id);
                
                // Award the badge using the full gamification process
                $badge_awarded = $this->database->award_badge($user_id, $badge_key);
                
                if (!$badge_awarded) {
                    return rest_ensure_response(array(
                        'success' => false,
                        'message' => "Failed to award badge '{$badge_key}' - may already be awarded",
                        'data' => array(
                            'user_id' => $user_id,
                            'badge_key' => $badge_key,
                            'awarded' => false
                        )
                    ));
                }
                
                // Update user stats with XP reward, gems reward, and badge count
                $update_data = array();
                if ($badge['xp_reward'] > 0) {
                    $update_data['total_xp'] = $user_stats_before['total_xp'] + $badge['xp_reward'];
                }
                if ($badge['gem_reward'] > 0) {
                    $update_data['gems_balance'] = $user_stats_before['gems_balance'] + $badge['gem_reward'];
                    // Record gems transaction
                    $this->database->record_gems_transaction(
                        $user_id,
                        'earned',
                        $badge['gem_reward'],
                        'badge_' . $badge_key,
                        'Earned ' . $badge['gem_reward'] . ' gems for earning badge: ' . $badge['name']
                    );
                }
                $update_data['badges_earned'] = $user_stats_before['badges_earned'] + 1;
                $this->database->update_user_stats($user_id, $update_data);
                
                // Trigger FluentCRM event if enabled
                $event_triggered = false;
                if ($badge['fluentcrm_enabled'] && !empty($badge['fluentcrm_event_key'])) {
                    $event_triggered = $this->trigger_fluentcrm_event($user_id, $badge);
                }
                
                // Get user stats after
                $user_stats_after = $this->database->get_user_stats($user_id);
                
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => "Badge '{$badge_key}' awarded successfully with full gamification",
                    'data' => array(
                        'user_id' => $user_id,
                        'badge_key' => $badge_key,
                        'badge_name' => $badge['name'],
                        'awarded' => true,
                        'xp_reward' => $badge['xp_reward'],
                        'gem_reward' => $badge['gem_reward'],
                        'event_triggered' => $event_triggered,
                        'user_stats_before' => $user_stats_before,
                        'user_stats_after' => $user_stats_after
                    )
                ));
            } else {
                // Fix all missing badges based on user stats
                $user_stats = $this->database->get_user_stats($user_id);
                $user_badges = $this->database->get_user_badges($user_id);
                $all_badges = $this->database->get_badges(true);
                
                $earned_badge_keys = array_column($user_badges, 'badge_key');
                $fixed_badges = array();
                
                $debug_info = array();
                
                foreach ($all_badges as $badge) {
                    // Skip if already has badge record
                    if (in_array($badge['badge_key'], $earned_badge_keys)) {
                        $debug_info[] = "Skipping {$badge['badge_key']} - already has badge";
                        continue;
                    }
                    
                    $should_have_badge = false;
                    $criteria_type = $badge['criteria_type'] ?? '';
                    $criteria_value = intval($badge['criteria_value'] ?? 0);
                    
                    // Check if user should have this badge based on their stats
                    switch ($criteria_type) {
                        case 'practice_sessions':
                            if ($user_stats['total_sessions'] >= $criteria_value) {
                                $should_have_badge = true;
                            }
                            $debug_info[] = "Badge {$badge['badge_key']}: practice_sessions {$user_stats['total_sessions']} >= {$criteria_value} = " . ($should_have_badge ? 'YES' : 'NO');
                            break;
                        case 'total_xp':
                            if ($user_stats['total_xp'] >= $criteria_value) {
                                $should_have_badge = true;
                            }
                            $debug_info[] = "Badge {$badge['badge_key']}: total_xp {$user_stats['total_xp']} >= {$criteria_value} = " . ($should_have_badge ? 'YES' : 'NO');
                            break;
                        case 'streak':
                            if ($user_stats['current_streak'] >= $criteria_value) {
                                $should_have_badge = true;
                            }
                            $debug_info[] = "Badge {$badge['badge_key']}: streak {$user_stats['current_streak']} >= {$criteria_value} = " . ($should_have_badge ? 'YES' : 'NO');
                            break;
                        case 'long_session_count':
                            // For now, we'll skip this as it requires session data analysis
                            // TODO: Implement long session count logic
                            $should_have_badge = false;
                            $debug_info[] = "Badge {$badge['badge_key']}: long_session_count - SKIPPED (not implemented)";
                            break;
                        case 'time_of_day':
                            // For now, we'll skip this as it requires session data analysis
                            // TODO: Implement time of day logic
                            $should_have_badge = false;
                            $debug_info[] = "Badge {$badge['badge_key']}: time_of_day - SKIPPED (not implemented)";
                            break;
                        case 'comeback':
                            // For now, we'll skip this as it requires session data analysis
                            // TODO: Implement comeback logic
                            $should_have_badge = false;
                            $debug_info[] = "Badge {$badge['badge_key']}: comeback - SKIPPED (not implemented)";
                            break;
                        default:
                            // Unknown criteria type
                            $should_have_badge = false;
                            $debug_info[] = "Badge {$badge['badge_key']}: unknown criteria_type '{$criteria_type}' - SKIPPED";
                            break;
                    }
                    
                    if ($should_have_badge) {
                        // Award badge using full gamification process
                        $result = $this->database->award_badge($user_id, $badge['badge_key']);
                        if ($result) {
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
                            
                            // Trigger FluentCRM event if enabled
                            $event_triggered = false;
                            if ($badge['fluentcrm_enabled'] && !empty($badge['fluentcrm_event_key'])) {
                                $event_triggered = $this->trigger_fluentcrm_event($user_id, $badge);
                            }
                            
                            $fixed_badges[] = $badge['badge_key'];
                            $debug_info[] = "SUCCESS: Awarded badge {$badge['badge_key']} with full gamification (Event: " . ($event_triggered ? 'YES' : 'NO') . ")";
                            
                            // Update user stats for next iteration
                            $user_stats = $this->database->get_user_stats($user_id);
                        } else {
                            $debug_info[] = "FAILED: Could not award badge {$badge['badge_key']}";
                        }
                    }
                }
                
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Fixed ' . count($fixed_badges) . ' missing badge records',
                    'data' => array(
                        'user_id' => $user_id,
                        'fixed_badges' => $fixed_badges,
                        'total_fixed' => count($fixed_badges),
                        'debug_info' => $debug_info,
                        'user_stats' => $user_stats,
                        'earned_badge_keys' => $earned_badge_keys
                    )
                ));
            }
            
        } catch (Exception $e) {
            return new WP_Error('fix_missing_badges_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get badges list for dropdown
     */
    public function rest_get_badges_list($request) {
        try {
            $all_badges = $this->database->get_badges(true);
            
            $badges_list = array();
            foreach ($all_badges as $badge) {
                $badges_list[] = array(
                    'badge_key' => $badge['badge_key'],
                    'name' => $badge['name'],
                    'description' => $badge['description'],
                    'category' => $badge['category'],
                    'is_active' => $badge['is_active']
                );
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $badges_list,
                'message' => 'Badges list retrieved successfully'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('get_badges_list_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Debug database tables
     */
    public function rest_debug_database_tables($request) {
        try {
            global $wpdb;
            
            $debug_info = array();
            
            // Check if tables exist
            $tables_to_check = array(
                'jph_badges',
                'jph_user_badges',
                'jph_user_stats',
                'jph_practice_sessions'
            );
            
            foreach ($tables_to_check as $table) {
                $full_table_name = $wpdb->prefix . $table;
                $exists = $wpdb->get_var("SHOW TABLES LIKE '{$full_table_name}'") == $full_table_name;
                
                $debug_info[$table] = array(
                    'exists' => $exists,
                    'full_name' => $full_table_name
                );
                
                if ($exists) {
                    // Get table structure
                    $structure = $wpdb->get_results("DESCRIBE {$full_table_name}", ARRAY_A);
                    $debug_info[$table]['structure'] = $structure;
                    
                    // Get row count
                    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$full_table_name}");
                    $debug_info[$table]['row_count'] = $count;
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $debug_info,
                'message' => 'Database tables debug completed'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('debug_database_tables_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Debug FluentCRM events
     */
    public function rest_debug_fluentcrm_events($request) {
        try {
            $debug_info = array();
            
            // Check if FluentCRM is active - multiple detection methods
            $fluentcrm_active = false;
            $fluentcrm_detection_methods = array();
            
            // Method 1: Check for FluentCRM main class
            $method1 = class_exists('FluentCrm\App\Services\FluentCrm');
            $fluentcrm_detection_methods['class_exists_FluentCrm'] = $method1;
            
            // Method 2: Check for FluentCRM functions
            $method2 = function_exists('fluentcrm_get_contact');
            $fluentcrm_detection_methods['function_exists_fluentcrm_get_contact'] = $method2;
            
            // Method 2b: Check for FluentCrmApi function
            $method2b = function_exists('FluentCrmApi');
            $fluentcrm_detection_methods['function_exists_FluentCrmApi'] = $method2b;
            
            // Method 3: Check if FluentCRM plugin is active
            $method3 = is_plugin_active('fluent-crm/fluent-crm.php');
            $fluentcrm_detection_methods['is_plugin_active'] = $method3;
            
            // Method 4: Check for FluentCRM constants
            $method4 = defined('FLUENTCRM_VERSION');
            $fluentcrm_detection_methods['defined_FLUENTCRM_VERSION'] = $method4;
            
            // Method 5: Check for FluentCRM in active plugins
            $active_plugins = get_option('active_plugins', array());
            $method5 = in_array('fluent-crm/fluent-crm.php', $active_plugins);
            $fluentcrm_detection_methods['in_active_plugins'] = $method5;
            
            // Method 6: Check for FluentCRM in mu-plugins
            $mu_plugins = get_mu_plugins();
            $method6 = isset($mu_plugins['fluent-crm/fluent-crm.php']);
            $fluentcrm_detection_methods['in_mu_plugins'] = $method6;
            
            // FluentCRM is active if any method returns true
            $fluentcrm_active = $method1 || $method2 || $method2b || $method3 || $method4 || $method5 || $method6;
            
            $debug_info['fluentcrm_active'] = $fluentcrm_active;
            $debug_info['fluentcrm_detection_methods'] = $fluentcrm_detection_methods;
            
            if ($fluentcrm_active) {
                // Get FluentCRM version
                $debug_info['fluentcrm_version'] = defined('FLUENTCRM_VERSION') ? FLUENTCRM_VERSION : 'Unknown';
                
                // Check if we can access FluentCRM functions
                $debug_info['fluentcrm_functions_available'] = array(
                    'fluentcrm_get_contact' => function_exists('fluentcrm_get_contact'),
                    'fluentcrm_add_contact' => function_exists('fluentcrm_add_contact'),
                    'fluentcrm_update_contact' => function_exists('fluentcrm_update_contact'),
                    'fluentcrm_contact_added' => function_exists('fluentcrm_contact_added'),
                    'fluentcrm_contact_updated' => function_exists('fluentcrm_contact_updated'),
                    'fluentCrmTrackEvent' => function_exists('fluentCrmTrackEvent')
                );
                
                // Test event triggering capability
                $debug_info['event_triggering_test'] = array(
                    'can_trigger_events' => method_exists($this, 'trigger_fluentcrm_event'),
                    'webhook_handler_exists' => class_exists('JPH_Webhook_Handler'),
                    'fluentcrm_api_available' => function_exists('FluentCrmApi'),
                    'event_tracker_available' => function_exists('FluentCrmApi') && method_exists(FluentCrmApi('event_tracker'), 'track')
                );
            }
            
            // Get badges with FluentCRM enabled
            $all_badges = $this->database->get_badges(true);
            $fluentcrm_badges = array();
            
            foreach ($all_badges as $badge) {
                if ($badge['fluentcrm_enabled']) {
                    $fluentcrm_badges[] = array(
                        'badge_key' => $badge['badge_key'],
                        'name' => $badge['name'],
                        'event_key' => $badge['fluentcrm_event_key'],
                        'event_title' => $badge['fluentcrm_event_title']
                    );
                }
            }
            
            $debug_info['fluentcrm_enabled_badges'] = $fluentcrm_badges;
            $debug_info['total_fluentcrm_badges'] = count($fluentcrm_badges);
            
            // Check recent event logs if available
            $debug_info['recent_events'] = 'Event logs would appear here if available';
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $debug_info,
                'message' => 'FluentCRM events debug completed'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('debug_fluentcrm_events_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Trigger FluentCRM event for badge earning
     */
    private function trigger_fluentcrm_event($user_id, $badge) {
        try {
            error_log("=== FluentCRM Event Triggering Debug ===");
            error_log("User ID: {$user_id}");
            error_log("Badge Key: {$badge['badge_key']}");
            error_log("Event Key: {$badge['fluentcrm_event_key']}");
            error_log("Event Title: {$badge['fluentcrm_event_title']}");
            
            // Get user email
            $user = get_user_by('ID', $user_id);
            if (!$user) {
                error_log("FluentCRM Event: User not found for ID: {$user_id}");
                return false;
            }
            
            $user_email = $user->user_email;
            error_log("User Email: {$user_email}");
            
            // Use FluentCRM event tracking API
            if (function_exists('FluentCrmApi')) {
                error_log("FluentCrmApi function exists - attempting event trigger");
                try {
                    // Method 1: Try event tracker API
                    $tracker = FluentCrmApi('event_tracker');
                    error_log("Event tracker object created: " . (is_object($tracker) ? 'Yes' : 'No'));
                    
                    if (method_exists($tracker, 'track')) {
                        error_log("track() method exists - attempting to call");
                        
                        // Keep original event key for automation triggers, but add timestamp to title for uniqueness
                        $event_key = $badge['fluentcrm_event_key'];
                        $unique_title = $badge['fluentcrm_event_title'] . ' at ' . current_time('Y-m-d H:i:s');
                        
                        $result = $tracker->track([
                            'event_key' => $event_key,
                            'title' => $unique_title,
                            'email' => $user_email,
                            'value' => "Badge: {$badge['name']} - {$badge['description']}",
                            'provider' => 'academy_practice_hub'
                        ]);
                        
                        error_log("Event tracker result: " . ($result ? 'Success' : 'Failed'));
                        if ($result) {
                            error_log("FluentCRM Event triggered successfully via tracker: {$badge['fluentcrm_event_key']} for user {$user_email}");
                            error_log("=== End FluentCRM Event Debug ===");
                            return true;
                        }
                    } else {
                        error_log("track() method does not exist on event tracker");
                    }
                    
                    // Method 2: Try direct event tracking
                    if (function_exists('fluentCrmTrackEvent')) {
                        error_log("fluentCrmTrackEvent function exists - attempting to call");
                        
                        // Keep original event key for automation triggers, but add timestamp to title for uniqueness
                        $event_key = $badge['fluentcrm_event_key'];
                        $unique_title = $badge['fluentcrm_event_title'] . ' at ' . current_time('Y-m-d H:i:s');
                        
                        $result = fluentCrmTrackEvent([
                            'event_key' => $event_key,
                            'title' => $unique_title,
                            'email' => $user_email,
                            'value' => "Badge: {$badge['name']} - {$badge['description']}",
                            'provider' => 'academy_practice_hub'
                        ]);
                        
                        error_log("fluentCrmTrackEvent result: " . ($result ? 'Success' : 'Failed'));
                        if ($result) {
                            error_log("FluentCRM Event triggered successfully via fluentCrmTrackEvent: {$badge['fluentcrm_event_key']} for user {$user_email}");
                            error_log("=== End FluentCRM Event Debug ===");
                            return true;
                        }
                    } else {
                        error_log("fluentCrmTrackEvent function does not exist");
                    }
                    
                } catch (Exception $e) {
                    error_log("FluentCRM API error: " . $e->getMessage());
                }
            } else {
                error_log("FluentCrmApi function does not exist");
            }
            
            // Fallback: Use WordPress action hook
            error_log("Using fallback WordPress action hook");
            
            // Keep original event key for automation triggers, but add timestamp to title for uniqueness
            $event_key = $badge['fluentcrm_event_key'];
            $unique_title = $badge['fluentcrm_event_title'] . ' at ' . current_time('Y-m-d H:i:s');
            
            error_log("Event Key: {$event_key}");
            error_log("Unique Title: {$unique_title}");
            
            do_action('fluent_crm/track_event_activity', [
                'event_key' => $event_key,
                'title' => $unique_title,
                'email' => $user_email,
                'value' => "Badge: {$badge['name']} - {$badge['description']}",
                'provider' => 'academy_practice_hub'
            ], true);
            
            error_log("FluentCRM Event triggered via fallback action hook: {$badge['fluentcrm_event_key']} for user {$user_email}");
            error_log("=== End FluentCRM Event Debug ===");
            return true;
            
        } catch (Exception $e) {
            error_log("FluentCRM Event error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove user badge for testing
     */
    public function rest_remove_user_badge($request) {
        try {
            $user_id = $request->get_param('user_id') ?: get_current_user_id();
            $badge_key = $request->get_param('badge_key');
            
            if (!$user_id || !$badge_key) {
                return new WP_Error('missing_params', 'User ID and Badge Key are required', array('status' => 400));
            }
            
            global $wpdb;
            
            // Check if badge exists
            $badge = $this->database->get_badge_by_key($badge_key);
            if (!$badge) {
                return new WP_Error('badge_not_found', "Badge '{$badge_key}' not found", array('status' => 404));
            }
            
            // Check if user has the badge
            $user_badge = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}jph_user_badges WHERE user_id = %d AND badge_key = %s",
                $user_id, $badge_key
            ));
            
            if (!$user_badge) {
                return rest_ensure_response(array(
                    'success' => false,
                    'message' => "User does not have badge '{$badge_key}'",
                    'data' => array(
                        'user_id' => $user_id,
                        'badge_key' => $badge_key,
                        'removed' => false
                    )
                ));
            }
            
            // Remove the badge
            $result = $wpdb->delete(
                $wpdb->prefix . 'jph_user_badges',
                array(
                    'user_id' => $user_id,
                    'badge_key' => $badge_key
                ),
                array('%d', '%s')
            );
            
            if ($result) {
                // Update user stats (subtract XP, gems, badge count)
                $user_stats = $this->database->get_user_stats($user_id);
                $update_data = array();
                
                if ($badge['xp_reward'] > 0) {
                    $update_data['total_xp'] = max(0, $user_stats['total_xp'] - $badge['xp_reward']);
                }
                if ($badge['gem_reward'] > 0) {
                    $update_data['gems_balance'] = max(0, $user_stats['gems_balance'] - $badge['gem_reward']);
                }
                $update_data['badges_earned'] = max(0, $user_stats['badges_earned'] - 1);
                
                $this->database->update_user_stats($user_id, $update_data);
                
                error_log("Badge removed: user_id={$user_id}, badge_key={$badge_key}");
                
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => "Badge '{$badge_key}' removed successfully",
                    'data' => array(
                        'user_id' => $user_id,
                        'badge_key' => $badge_key,
                        'badge_name' => $badge['name'],
                        'removed' => true,
                        'xp_deducted' => $badge['xp_reward'],
                        'gems_deducted' => $badge['gem_reward']
                    )
                ));
            } else {
                return rest_ensure_response(array(
                    'success' => false,
                    'message' => "Failed to remove badge '{$badge_key}'",
                    'data' => array(
                        'user_id' => $user_id,
                        'badge_key' => $badge_key,
                        'removed' => false
                    )
                ));
            }
            
        } catch (Exception $e) {
            return new WP_Error('remove_user_badge_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Debug gem balance for a user
     */
    public function rest_debug_gem_balance($request) {
        try {
            $user_id = $request->get_param('user_id') ?: get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('missing_user_id', 'User ID is required', array('status' => 400));
            }
            
            global $wpdb;
            
            // Get user stats from database
            $user_stats = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}jph_user_stats WHERE user_id = %d",
                $user_id
            ), ARRAY_A);
            
            // Get recent gem transactions
            $recent_transactions = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}jph_gems_transactions WHERE user_id = %d ORDER BY created_at DESC LIMIT 10",
                $user_id
            ), ARRAY_A);
            
            // Get user info
            $user = get_user_by('ID', $user_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => array(
                    'user_id' => $user_id,
                    'user_email' => $user ? $user->user_email : 'Unknown',
                    'user_display_name' => $user ? $user->display_name : 'Unknown',
                    'current_gem_balance' => $user_stats ? $user_stats['gems_balance'] : 'No stats record',
                    'user_stats' => $user_stats,
                    'recent_transactions' => $recent_transactions,
                    'debug_timestamp' => current_time('mysql')
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('debug_gem_balance_error', 'Error: ' . $e->getMessage(), array('status' => 500));
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
            
            // Generate new analysis (no caching)
            $analysis = $this->generate_ai_analysis($user_id);
            
            if (is_wp_error($analysis)) {
                return $analysis;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $analysis,
                'cached' => false
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
        
        $start_time = microtime(true);
        
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
        $this->logger->debug('AI Analysis Debug', array(
            'user_id' => $user_id,
            'date_range' => $thirty_days_ago . ' to ' . $current_time,
            'sql_query' => $sessions_query,
            'sessions_found' => count($sessions)
        ));
        
        // Log some sample session dates for debugging
        if (!empty($sessions)) {
            $sample_dates = array_column(array_slice($sessions, 0, 3), 'created_at');
            $this->logger->debug('AI Analysis Debug - Sample session dates', array('dates' => $sample_dates));
        }
        
        // Also check if tables exist and have data
        $sessions_count_total = $wpdb->get_var("SELECT COUNT(*) FROM $sessions_table WHERE user_id = $user_id");
        $sessions_count_30_days = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $sessions_table WHERE user_id = %d AND created_at >= %s", $user_id, $thirty_days_ago));
        
        $this->logger->debug('AI Analysis Debug - Session counts', array(
            'total_sessions' => $sessions_count_total,
            'sessions_30_days' => $sessions_count_30_days
        ));
        
        // Check table structure
        $table_structure = $wpdb->get_results("DESCRIBE $sessions_table", ARRAY_A);
        $this->logger->debug('AI Analysis Debug - Sessions table structure', array('structure' => $table_structure));
        
        // Additional debugging - check if tables exist
        $tables_exist = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}jph_%'", ARRAY_A);
        $this->logger->debug('AI Analysis Debug - JPH tables found', array('tables' => $tables_exist));
        
        // Check if user exists in user_stats table
        $user_in_stats = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $stats_table WHERE user_id = %d", $user_id));
        $this->logger->debug('AI Analysis Debug - User found in stats table', array('count' => $user_in_stats));
        
        // Check recent sessions without date filter
        $recent_sessions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $sessions_table WHERE user_id = %d ORDER BY created_at DESC LIMIT 5", $user_id), ARRAY_A);
        $this->logger->debug('AI Analysis Debug - Recent sessions for user', array('sessions' => $recent_sessions));
        
        // Test the date comparison directly
        $test_query = $wpdb->prepare("SELECT COUNT(*) as count, MIN(created_at) as earliest, MAX(created_at) as latest FROM $sessions_table WHERE user_id = %d AND created_at >= %s", $user_id, $thirty_days_ago);
        $test_result = $wpdb->get_row($test_query, ARRAY_A);
        $this->logger->debug('AI Analysis Debug - Date test result', array('result' => $test_result));
        
        // Test with different date formats
        $test_date_formats = array(
            'Y-m-d H:i:s' => date('Y-m-d H:i:s', strtotime('-30 days', current_time('timestamp'))),
            'Y-m-d' => date('Y-m-d', strtotime('-30 days', current_time('timestamp'))),
            'Y-m-d 00:00:00' => date('Y-m-d 00:00:00', strtotime('-30 days', current_time('timestamp')))
        );
        
        $test_results = array();
        foreach ($test_date_formats as $format => $test_date) {
            $test_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $sessions_table WHERE user_id = %d AND created_at >= %s", $user_id, $test_date));
            $test_results[$format] = array('date' => $test_date, 'count' => $test_count);
        }
        $this->logger->debug('AI Analysis Debug - Date format tests', array('results' => $test_results));
        
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
        
        // Get configurable AI prompt - use what's set in admin, don't force update
        $ai_prompt_template = get_option('aph_ai_prompt', 'Format your response as exactly 3 separate paragraphs with blank lines between them.

1. STRENGTHS: What they are doing well and their strengths.

2. IMPROVEMENT AREAS: Trends and areas for improvement.

3. NEXT STEPS: Practical next steps and lesson recommendations.

Practice Sessions: {total_sessions} sessions
Total Practice Time: {total_minutes} minutes
Average Session Length: {avg_duration} minutes
Average Mood/Sentiment: {avg_sentiment}/5 (1=frustrating, 5=excellent)
Improvement Rate: {improvement_rate}% of sessions showed improvement
Most Frequent Practice Day: {most_frequent_day}
Most Practiced Item: {most_practiced_item}
Current Level: {current_level}
Current Streak: {current_streak} days

When recommending lessons, use these titles naturally: Technique - Jazzedge Practice Curriculum™; Improvisation - The Confident Improviser™; Accompaniment - Piano Accompaniment Essentials™; Jazz Standards - Standards By The Dozen™; Super Easy Jazz Standards - Super Simple Standards™.

FORMAT: Write 3 paragraphs separated by blank lines.');
        
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

        // Get AI settings for the call - use what's set in admin, don't force update
        $ai_system_message = get_option('aph_ai_system_message', 'You are a helpful piano practice coach. Format responses as 3 separate paragraphs with blank lines between them. Use plain text only.');
        $ai_max_tokens = get_option('aph_ai_max_tokens', 500);
        $ai_model = get_option('aph_ai_model', 'gpt-4');
        $ai_temperature = get_option('aph_ai_temperature', 0.7);
        
        // Debug: Log the exact prompt and system message being sent
        $this->logger->debug('AI Analysis - Final Prompt and Settings', array(
            'prompt' => $prompt,
            'system_message' => $ai_system_message,
            'model' => $ai_model,
            'max_tokens' => $ai_max_tokens,
            'temperature' => $ai_temperature,
            'prompt_length' => strlen($prompt),
            'system_message_length' => strlen($ai_system_message)
        ));
        
        // Call Katahdin AI Hub
        $ai_response = $this->call_katahdin_ai($prompt, $ai_system_message, $ai_model, $ai_max_tokens, $ai_temperature);
        
        // Debug: Log whether AI call succeeded or failed
        if (is_wp_error($ai_response)) {
            $error_message = $ai_response->get_error_message();
            $error_code = $ai_response->get_error_code();
            $this->logger->warning('AI service failed, using fallback analysis', array('error' => $error_message, 'code' => $error_code));
            $ai_response = $this->generate_fallback_analysis($sessions, $user_stats, $debug_info);
            $this->logger->debug('Using fallback analysis', array('fallback_content' => $ai_response));
            // Add to debug info for immediate visibility
            $debug_info['ai_call_status'] = 'FAILED - Using fallback';
            $debug_info['ai_error_code'] = $error_code;
            $debug_info['ai_error_message'] = $error_message;
            $debug_info['fallback_content'] = $ai_response;
        } else {
            $this->logger->debug('AI service succeeded', array('ai_response_content' => $ai_response));
            // Add to debug info for immediate visibility
            $debug_info['ai_call_status'] = 'SUCCESS';
            $debug_info['ai_response_content'] = $ai_response;
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
                'prompt_length' => strlen($prompt),
                'system_message_length' => strlen($ai_system_message),
                'response_time' => microtime(true) - $start_time,
                'ai_response_length' => strlen($ai_response),
                'ai_response_paragraph_count' => substr_count($ai_response, "\n\n") + 1,
                'ai_response_has_line_breaks' => strpos($ai_response, "\n") !== false,
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
                $quota_limit = get_option('jph_ai_quota_limit', 50000);
                $hub->register_plugin('academy-practice-hub', array(
                    'name' => 'Academy Practice Hub',
                    'version' => '3.0',
                    'features' => array('chat', 'completions'),
                    'quota_limit' => $quota_limit
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
        $this->logger->debug('Katahdin AI Hub Response', array(
            'status' => $response_status,
            'data' => $response_data,
            'request_sent' => $request_data,
            'raw_response' => $response_data
        ));
        
        // Debug: Log the exact AI response content
        if (isset($response_data['choices'][0]['message']['content'])) {
            $ai_response_content = $response_data['choices'][0]['message']['content'];
            $this->logger->debug('Raw AI Response Content', array(
                'response_length' => strlen($ai_response_content),
                'response_content' => $ai_response_content,
                'paragraph_count' => substr_count($ai_response_content, "\n\n") + 1,
                'has_line_breaks' => strpos($ai_response_content, "\n") !== false
            ));
        }
        
        // Debug: Log the full response structure to see what we're getting
        $this->logger->debug('Full Katahdin AI Hub Response Structure', array(
            'full_response' => $response_data,
            'response_keys' => array_keys($response_data),
            'choices_structure' => isset($response_data['choices']) ? $response_data['choices'] : 'No choices key',
            'first_choice' => isset($response_data['choices'][0]) ? $response_data['choices'][0] : 'No first choice'
        ));
        
        if ($response_status !== 200) {
            $error_message = isset($response_data['message']) ? $response_data['message'] : 'Unknown error';
            return new WP_Error('ai_service_error', 'AI service returned error: ' . $response_status . ' - ' . $error_message, array('status' => $response_status));
        }
        
        if (!isset($response_data['choices'][0]['message']['content'])) {
            return new WP_Error('ai_response_invalid', 'Invalid response from AI service', array('status' => 500));
        }
        
        $ai_response = trim($response_data['choices'][0]['message']['content']);
        
        // Debug: Log the exact response from Katahdin AI Hub
        $this->logger->debug('Raw AI Response Content', array(
            'response_length' => strlen($ai_response),
            'response_content' => $ai_response,
            'paragraph_count' => substr_count($ai_response, "\n\n") + 1,
            'has_line_breaks' => strpos($ai_response, "\n") !== false
        ));
        
        return $ai_response;
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
     * Export complete plugin data for backup (user data + admin settings + configuration)
     */
    public function rest_export_user_data($request) {
        try {
            global $wpdb;
            
            // Get complete plugin data
            $export_data = array(
                'export_date' => current_time('mysql'),
                'export_version' => '2.0',
                'export_type' => 'complete_plugin_backup',
                'data' => array(
                    'user_data' => array(),
                    'admin_settings' => array(),
                    'system_configuration' => array()
                )
            );
            
            // === USER DATA ===
            // Practice sessions
            $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
            $export_data['data']['user_data']['practice_sessions'] = $wpdb->get_results("SELECT * FROM $sessions_table", ARRAY_A);
            
            // Practice items
            $items_table = $wpdb->prefix . 'jph_practice_items';
            $export_data['data']['user_data']['practice_items'] = $wpdb->get_results("SELECT * FROM $items_table", ARRAY_A);
            
            // User stats
            $stats_table = $wpdb->prefix . 'jph_user_stats';
            $export_data['data']['user_data']['user_stats'] = $wpdb->get_results("SELECT * FROM $stats_table", ARRAY_A);
            
            // User badges
            $badges_table = $wpdb->prefix . 'jph_user_badges';
            $export_data['data']['user_data']['user_badges'] = $wpdb->get_results("SELECT * FROM $badges_table", ARRAY_A);
            
            // Gem transactions
            $gems_table = $wpdb->prefix . 'jph_gem_transactions';
            $export_data['data']['user_data']['gem_transactions'] = $wpdb->get_results("SELECT * FROM $gems_table", ARRAY_A);
            
            // Lesson favorites
            $favorites_table = $wpdb->prefix . 'jph_lesson_favorites';
            $export_data['data']['user_data']['lesson_favorites'] = $wpdb->get_results("SELECT * FROM $favorites_table", ARRAY_A);
            
            // === ADMIN SETTINGS ===
            $export_data['data']['admin_settings'] = array(
                'practice_hub_page_id' => get_option('jph_practice_hub_page_id', ''),
                'ai_quota_limit' => get_option('jph_ai_quota_limit', 50000),
                'ai_prompt' => get_option('aph_ai_prompt', ''),
                'ai_system_message' => get_option('aph_ai_system_message', ''),
                'ai_model' => get_option('aph_ai_model', 'gpt-4'),
                'ai_max_tokens' => get_option('aph_ai_max_tokens', 300),
                'ai_temperature' => get_option('aph_ai_temperature', 0.3),
                'badge_events_log' => get_option('jph_badge_events_log', array())
            );
            
            // === SYSTEM CONFIGURATION ===
            // Badge definitions
            $badge_definitions_table = $wpdb->prefix . 'jph_badges';
            $export_data['data']['system_configuration']['badge_definitions'] = $wpdb->get_results("SELECT * FROM $badge_definitions_table", ARRAY_A);
            
            // Plugin version and WordPress info
            $export_data['data']['system_configuration']['plugin_info'] = array(
                'plugin_version' => '1.0.0', // You might want to get this from a constant
                'wordpress_version' => get_bloginfo('version'),
                'site_url' => get_site_url(),
                'export_timestamp' => time()
            );
            
            // Add summary
            $export_data['summary'] = array(
                'user_data' => array(
                    'practice_sessions_count' => count($export_data['data']['user_data']['practice_sessions']),
                    'practice_items_count' => count($export_data['data']['user_data']['practice_items']),
                    'user_stats_count' => count($export_data['data']['user_data']['user_stats']),
                    'user_badges_count' => count($export_data['data']['user_data']['user_badges']),
                    'gem_transactions_count' => count($export_data['data']['user_data']['gem_transactions']),
                    'lesson_favorites_count' => count($export_data['data']['user_data']['lesson_favorites'])
                ),
                'admin_settings' => array(
                    'settings_count' => count($export_data['data']['admin_settings'])
                ),
                'system_configuration' => array(
                    'badge_definitions_count' => count($export_data['data']['system_configuration']['badge_definitions']),
                    'plugin_info' => $export_data['data']['system_configuration']['plugin_info']
                )
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
     * Import complete plugin data from backup (user data + admin settings + configuration)
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
            
            // Check if this is the new format (v2.0) or old format (v1.0)
            $is_new_format = isset($data['export_version']) && $data['export_version'] === '2.0';
            
            if ($is_new_format) {
                // === NEW FORMAT: Complete Plugin Backup ===
                
                // Import user data
                if (isset($data['data']['user_data'])) {
                    $user_data = $data['data']['user_data'];
                    
                    // Import practice sessions
                    if (isset($user_data['practice_sessions']) && !empty($user_data['practice_sessions'])) {
                        $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
                        $wpdb->query("TRUNCATE TABLE $sessions_table");
                        foreach ($user_data['practice_sessions'] as $session) {
                            unset($session['id']); // Remove ID to allow auto-increment
                            $wpdb->insert($sessions_table, $session);
                        }
                        $imported_counts['practice_sessions'] = count($user_data['practice_sessions']);
                    }
                    
                    // Import practice items
                    if (isset($user_data['practice_items']) && !empty($user_data['practice_items'])) {
                        $items_table = $wpdb->prefix . 'jph_practice_items';
                        $wpdb->query("TRUNCATE TABLE $items_table");
                        foreach ($user_data['practice_items'] as $item) {
                            unset($item['id']); // Remove ID to allow auto-increment
                            $wpdb->insert($items_table, $item);
                        }
                        $imported_counts['practice_items'] = count($user_data['practice_items']);
                    }
                    
                    // Import user stats
                    if (isset($user_data['user_stats']) && !empty($user_data['user_stats'])) {
                        $stats_table = $wpdb->prefix . 'jph_user_stats';
                        $wpdb->query("TRUNCATE TABLE $stats_table");
                        foreach ($user_data['user_stats'] as $stat) {
                            unset($stat['id']); // Remove ID to allow auto-increment
                            $wpdb->insert($stats_table, $stat);
                        }
                        $imported_counts['user_stats'] = count($user_data['user_stats']);
                    }
                    
                    // Import user badges
                    if (isset($user_data['user_badges']) && !empty($user_data['user_badges'])) {
                        $badges_table = $wpdb->prefix . 'jph_user_badges';
                        $wpdb->query("TRUNCATE TABLE $badges_table");
                        foreach ($user_data['user_badges'] as $badge) {
                            unset($badge['id']); // Remove ID to allow auto-increment
                            $wpdb->insert($badges_table, $badge);
                        }
                        $imported_counts['user_badges'] = count($user_data['user_badges']);
                    }
                    
                    // Import gem transactions
                    if (isset($user_data['gem_transactions']) && !empty($user_data['gem_transactions'])) {
                        $gems_table = $wpdb->prefix . 'jph_gem_transactions';
                        $wpdb->query("TRUNCATE TABLE $gems_table");
                        foreach ($user_data['gem_transactions'] as $gem) {
                            unset($gem['id']); // Remove ID to allow auto-increment
                            $wpdb->insert($gems_table, $gem);
                        }
                        $imported_counts['gem_transactions'] = count($user_data['gem_transactions']);
                    }
                    
                    // Import lesson favorites
                    if (isset($user_data['lesson_favorites']) && !empty($user_data['lesson_favorites'])) {
                        $favorites_table = $wpdb->prefix . 'jph_lesson_favorites';
                        $wpdb->query("TRUNCATE TABLE $favorites_table");
                        foreach ($user_data['lesson_favorites'] as $favorite) {
                            unset($favorite['id']); // Remove ID to allow auto-increment
                            $wpdb->insert($favorites_table, $favorite);
                        }
                        $imported_counts['lesson_favorites'] = count($user_data['lesson_favorites']);
                    }
                }
                
                // Import admin settings
                if (isset($data['data']['admin_settings'])) {
                    $admin_settings = $data['data']['admin_settings'];
                    
                    if (isset($admin_settings['practice_hub_page_id'])) {
                        update_option('jph_practice_hub_page_id', $admin_settings['practice_hub_page_id']);
                    }
                    if (isset($admin_settings['ai_quota_limit'])) {
                        update_option('jph_ai_quota_limit', $admin_settings['ai_quota_limit']);
                    }
                    if (isset($admin_settings['ai_prompt'])) {
                        update_option('aph_ai_prompt', $admin_settings['ai_prompt']);
                    }
                    if (isset($admin_settings['ai_system_message'])) {
                        update_option('aph_ai_system_message', $admin_settings['ai_system_message']);
                    }
                    if (isset($admin_settings['ai_model'])) {
                        update_option('aph_ai_model', $admin_settings['ai_model']);
                    }
                    if (isset($admin_settings['ai_max_tokens'])) {
                        update_option('aph_ai_max_tokens', $admin_settings['ai_max_tokens']);
                    }
                    if (isset($admin_settings['ai_temperature'])) {
                        update_option('aph_ai_temperature', $admin_settings['ai_temperature']);
                    }
                    if (isset($admin_settings['badge_events_log'])) {
                        update_option('jph_badge_events_log', $admin_settings['badge_events_log']);
                    }
                    
                    $imported_counts['admin_settings'] = count($admin_settings);
                }
                
                // Import system configuration
                if (isset($data['data']['system_configuration'])) {
                    $system_config = $data['data']['system_configuration'];
                    
                    // Import badge definitions
                    if (isset($system_config['badge_definitions']) && !empty($system_config['badge_definitions'])) {
                        $badge_definitions_table = $wpdb->prefix . 'jph_badges';
                        $wpdb->query("TRUNCATE TABLE $badge_definitions_table");
                        foreach ($system_config['badge_definitions'] as $badge_def) {
                            unset($badge_def['id']); // Remove ID to allow auto-increment
                            $wpdb->insert($badge_definitions_table, $badge_def);
                        }
                        $imported_counts['badge_definitions'] = count($system_config['badge_definitions']);
                    }
                    
                    $imported_counts['system_configuration'] = count($system_config);
                }
                
            } else {
                // === OLD FORMAT: User Data Only ===
                
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
                    foreach ($data['data']['gem_transactions'] as $gem) {
                        unset($gem['id']); // Remove ID to allow auto-increment
                        $wpdb->insert($gems_table, $gem);
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
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Data imported successfully',
                'imported_counts' => $imported_counts,
                'backup_format' => $is_new_format ? 'v2.0 (Complete Plugin Backup)' : 'v1.0 (User Data Only)'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('import_error', 'Error importing data: ' . $e->getMessage(), array('status' => 500));
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
            $sort_order = $request->get_param('sort_order') ?: 'desc';
            
            // Validate parameters
            $validation = $this->validator->validate_pagination($limit, $offset);
            if (is_wp_error($validation)) {
                return $validation;
            }
            
            $validation = $this->validator->validate_sort($sort_by, $sort_order);
            if (is_wp_error($validation)) {
                return $validation;
            }
            
            $limit = max(1, min(100, intval($limit)));
            $offset = max(0, intval($offset));
            
            // Try to get from cache first
            $leaderboard = $this->cache->get_cached_leaderboard($sort_by, $sort_order, $limit, $offset);
            
            if ($leaderboard === false) {
                // Cache miss - get from database
                $leaderboard = $this->database->get_leaderboard($limit, $offset, $sort_by, $sort_order);
                
                // Cache the result
                $this->cache->cache_leaderboard($sort_by, $sort_order, $limit, $offset, $leaderboard);
            }
            
            // Add position numbers
            foreach ($leaderboard as $index => $user) {
                $leaderboard[$index]['position'] = $offset + $index + 1;
            }
            
            // Get total count for pagination
            $total_count = $this->database->get_leaderboard_total_count();
            
            $response = rest_ensure_response(array(
                'success' => true,
                'data' => $leaderboard,
                'pagination' => array(
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => $total_count,
                    'total_pages' => ceil($total_count / $limit),
                    'current_page' => floor($offset / $limit) + 1,
                    'sort_by' => $sort_by,
                    'sort_order' => $sort_order
                )
            ));
            
            // Add rate limit headers
            $response = $this->rate_limiter->add_rate_limit_headers($response, $request->get_route(), get_current_user_id());
            
            return $response;
            
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
            $sort_order = $request->get_param('sort_order') ?: 'desc';
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
            }
            
            $position = $this->database->get_user_leaderboard_position($user_id, $sort_by, $sort_order);
            
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
            
            // Validate display name
            $validation = $this->validator->validate_display_name($display_name);
            if (is_wp_error($validation)) {
                return $validation;
            }
            
            $result = $this->database->update_user_display_name($user_id, $display_name);
            
            if (!$result) {
                return new WP_Error('update_failed', 'Failed to update display name', array('status' => 500));
            }
            
            // Invalidate cache since leaderboard data changed
            $this->cache->invalidate_user_cache($user_id);
            
            // Log the action
            $this->audit_logger->log_user_action('display_name_updated', array(
                'old_display_name' => $request->get_param('old_display_name'),
                'new_display_name' => $display_name
            ), $user_id);
            
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
            // Try to get from cache first
            $stats = $this->cache->get_cached_leaderboard_stats();
            
            if ($stats === false) {
                // Cache miss - get from database
                $stats = $this->database->get_leaderboard_stats();
                
                // Cache the result
                $this->cache->cache_leaderboard_stats($stats);
            }
            
            $response = rest_ensure_response(array(
                'success' => true,
                'data' => $stats
            ));
            
            // Add rate limit headers
            $response = $this->rate_limiter->add_rate_limit_headers($response, $request->get_route(), get_current_user_id());
            
            return $response;
            
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
    
    /**
     * Generate test students with realistic data
     */
    public function rest_generate_test_students($request) {
        try {
            global $wpdb;
            
            $students_created = 0;
            $sessions_created = 0;
            $items_created = 0;
            $badges_awarded = 0;
            
            // Test student names
            $test_names = array(
                'Alex Johnson', 'Sarah Chen', 'Michael Rodriguez', 'Emily Davis', 'James Wilson',
                'Maria Garcia', 'David Brown', 'Lisa Anderson', 'Robert Taylor', 'Jennifer Martinez',
                'Christopher Lee', 'Amanda White', 'Daniel Thompson', 'Jessica Moore', 'Matthew Jackson',
                'Ashley Harris', 'Andrew Martin', 'Stephanie Garcia', 'Joshua Robinson', 'Nicole Clark',
                'Ryan Lewis', 'Megan Rodriguez', 'Kevin Walker', 'Rachel Hall', 'Brandon Young',
                'Lauren Allen', 'Tyler King', 'Samantha Wright', 'Jacob Lopez', 'Brittany Hill',
                'Nathan Scott', 'Kayla Green', 'Zachary Adams', 'Morgan Baker', 'Caleb Gonzalez',
                'Taylor Nelson', 'Austin Carter', 'Jordan Mitchell', 'Connor Perez', 'Alexis Roberts',
                'Logan Turner', 'Paige Phillips', 'Cameron Campbell', 'Brooke Parker', 'Hunter Evans',
                'Madison Edwards', 'Cole Collins', 'Sydney Stewart', 'Blake Sanchez', 'Avery Morris'
            );
            
            // Practice items
            $practice_items = array(
                'Jazz Standards' => array('Autumn Leaves', 'Blue Moon', 'All the Things You Are', 'Summertime', 'Take Five'),
                'Scales' => array('Major Scales', 'Minor Scales', 'Blues Scale', 'Pentatonic Scale', 'Chromatic Scale'),
                'Exercises' => array('Hanon Exercises', 'Czerny Studies', 'Chord Progressions', 'Arpeggios', 'Sight Reading'),
                'Classical' => array('Bach Inventions', 'Chopin Nocturnes', 'Debussy Preludes', 'Mozart Sonatas', 'Beethoven Sonatas'),
                'Modern' => array('Pop Songs', 'Movie Themes', 'Video Game Music', 'Contemporary Jazz', 'Fusion Pieces')
            );
            
            // Badge types
            $badge_types = array('first_session', 'streak_7', 'streak_30', 'level_5', 'level_10', 'xp_1000', 'xp_5000', 'sessions_10', 'sessions_50', 'minutes_100', 'minutes_500');
            
            // Clear existing test data first
            $this->clear_test_data();
            
            // Ensure default badges exist
            $this->ensure_default_badges();
            
            for ($i = 0; $i < 50; $i++) {
                // Create test user
                $username = 'test_student_' . ($i + 1);
                $email = 'test' . ($i + 1) . '@example.com';
                $display_name = $test_names[$i];
                
                // Always create new user since we cleared existing ones
                $password = wp_generate_password(12, true, true); // Generate secure random password
                $user_id = wp_create_user($username, $password, $email);
                if (is_wp_error($user_id)) {
                    continue; // Skip if user creation failed
                }
                
                // Update display name
                wp_update_user(array(
                    'ID' => $user_id,
                    'display_name' => $display_name,
                    'first_name' => explode(' ', $display_name)[0],
                    'last_name' => explode(' ', $display_name)[1] ?? ''
                ));
                
                $students_created++;
                
                // Generate random stats
                $total_sessions = rand(1, 100);
                $total_minutes = rand(5, 3000);
                $current_streak = rand(0, 30);
                $longest_streak = max($current_streak, rand(0, 60));
                $total_xp = rand(50, 15000);
                $current_level = max(1, floor($total_xp / 1000) + rand(0, 3));
                $badges_earned = rand(0, 8);
                $gems_balance = rand(0, 500);
                $hearts_count = rand(0, 20);
                $streak_shield_count = rand(0, 5);
                
                // Calculate last practice date
                $last_practice_date = date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days'));
                
                // Insert/update user stats
                $stats_table = $wpdb->prefix . 'jph_user_stats';
                $wpdb->replace($stats_table, array(
                    'user_id' => $user_id,
                    'total_xp' => $total_xp,
                    'current_level' => $current_level,
                    'total_sessions' => $total_sessions,
                    'total_minutes' => $total_minutes,
                    'current_streak' => $current_streak,
                    'longest_streak' => $longest_streak,
                    'badges_earned' => $badges_earned,
                    'gems_balance' => $gems_balance,
                    'hearts_count' => $hearts_count,
                    'streak_shield_count' => $streak_shield_count,
                    'last_practice_date' => $last_practice_date,
                    'display_name' => $display_name,
                    'show_on_leaderboard' => 1
                ));
                
                // Generate practice sessions
                $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
                $items_table = $wpdb->prefix . 'jph_practice_items';
                
                for ($j = 0; $j < $total_sessions; $j++) {
                    $session_date = date('Y-m-d H:i:s', strtotime('-' . rand(0, 90) . ' days'));
                    $session_minutes = rand(5, 120);
                    $session_xp = $session_minutes * rand(8, 15);
                    $sentiment_score = rand(1, 5);
                    
                    // Insert practice session
                    $session_result = $wpdb->insert($sessions_table, array(
                        'user_id' => $user_id,
                        'session_date' => $session_date,
                        'duration_minutes' => $session_minutes,
                        'xp_earned' => $session_xp,
                        'sentiment_score' => $sentiment_score,
                        'notes' => $this->generate_random_notes()
                    ));
                    
                    if ($session_result !== false) {
                        $sessions_created++;
                        $session_id = $wpdb->insert_id;
                        
                        // Add practice items to session
                        $num_items = rand(1, 5);
                        $item_categories = array_keys($practice_items);
                        
                        for ($k = 0; $k < $num_items; $k++) {
                            $category = $item_categories[array_rand($item_categories)];
                            $item_name = $practice_items[$category][array_rand($practice_items[$category])];
                            
                            $item_result = $wpdb->insert($items_table, array(
                                'session_id' => $session_id,
                                'item_name' => $item_name,
                                'item_category' => $category,
                                'duration_minutes' => rand(5, 30),
                                'xp_earned' => rand(10, 50)
                            ));
                            
                            if ($item_result !== false) {
                                $items_created++;
                            }
                        }
                    }
                }
                
                // Award random badges
                $user_badges_table = $wpdb->prefix . 'jph_user_badges';
                $awarded_badges = array();
                
                for ($l = 0; $l < $badges_earned; $l++) {
                    $badge_type = $badge_types[array_rand($badge_types)];
                    if (!in_array($badge_type, $awarded_badges)) {
                        $wpdb->insert($user_badges_table, array(
                            'user_id' => $user_id,
                            'badge_key' => $badge_type,
                            'earned_at' => date('Y-m-d H:i:s', strtotime('-' . rand(0, 60) . ' days')),
                            'earned_date' => date('Y-m-d', strtotime('-' . rand(0, 60) . ' days'))
                        ));
                        $awarded_badges[] = $badge_type;
                        $badges_awarded++;
                    }
                }
            }
            
            // Log the admin action
            $this->audit_logger->log_admin_action('test_data_generated', array(
                'students_created' => $students_created,
                'sessions_created' => $sessions_created,
                'items_created' => $items_created,
                'badges_awarded' => $badges_awarded
            ));
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => array(
                    'students_created' => $students_created,
                    'sessions_created' => $sessions_created,
                    'items_created' => $items_created,
                    'badges_awarded' => $badges_awarded
                ),
                'message' => 'Test data generated successfully!'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('generate_test_students_error', 'Error generating test students: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Clear test data
     */
    public function rest_clear_test_data($request) {
        try {
            global $wpdb;
            
            $users_deleted = 0;
            $sessions_deleted = 0;
            $items_deleted = 0;
            $badges_deleted = 0;
            
            // Get all test users
            $test_users = $wpdb->get_results("SELECT ID FROM {$wpdb->users} WHERE user_login LIKE 'test_student_%'", ARRAY_A);
            
            if (!empty($test_users)) {
                $user_ids = array_column($test_users, 'ID');
                $user_ids_str = implode(',', $user_ids);
                
                // Count items before deletion for reporting
                $sessions_deleted = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_practice_sessions WHERE user_id IN ($user_ids_str)");
                $items_deleted = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_practice_items WHERE user_id IN ($user_ids_str)");
                $badges_deleted = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_user_badges WHERE user_id IN ($user_ids_str)");
                
                // Clear user stats
                $wpdb->query("DELETE FROM {$wpdb->prefix}jph_user_stats WHERE user_id IN ($user_ids_str)");
                
                // Clear practice sessions
                $wpdb->query("DELETE FROM {$wpdb->prefix}jph_practice_sessions WHERE user_id IN ($user_ids_str)");
                
                // Clear practice items
                $wpdb->query("DELETE FROM {$wpdb->prefix}jph_practice_items WHERE user_id IN ($user_ids_str)");
                
                // Clear user badges
                $wpdb->query("DELETE FROM {$wpdb->prefix}jph_user_badges WHERE user_id IN ($user_ids_str)");
                
                // Clear gem transactions
                $wpdb->query("DELETE FROM {$wpdb->prefix}jph_gem_transactions WHERE user_id IN ($user_ids_str)");
                $wpdb->query("DELETE FROM {$wpdb->prefix}jph_gems_transactions WHERE user_id IN ($user_ids_str)");
                
                // Clear lesson favorites
                $wpdb->query("DELETE FROM {$wpdb->prefix}jph_lesson_favorites WHERE user_id IN ($user_ids_str)");
                
                // Clear JPC user data
                $wpdb->query("DELETE FROM {$wpdb->prefix}jph_jpc_user_assignments WHERE user_id IN ($user_ids_str)");
                $wpdb->query("DELETE FROM {$wpdb->prefix}jph_jpc_user_progress WHERE user_id IN ($user_ids_str)");
                $wpdb->query("DELETE FROM {$wpdb->prefix}jph_jpc_milestone_submissions WHERE user_id IN ($user_ids_str)");
                
                // Clear audit logs
                $wpdb->query("DELETE FROM {$wpdb->prefix}jph_audit_logs WHERE user_id IN ($user_ids_str)");
                
                // Delete the users themselves
                foreach ($user_ids as $user_id) {
                    if (wp_delete_user($user_id)) {
                        $users_deleted++;
                    }
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => array(
                    'users_deleted' => $users_deleted,
                    'sessions_deleted' => $sessions_deleted,
                    'items_deleted' => $items_deleted,
                    'badges_deleted' => $badges_deleted
                ),
                'message' => 'Test data cleared successfully!'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('clear_test_data_error', 'Error clearing test data: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Clear all cache
     */
    public function rest_clear_cache($request) {
        try {
            // Clear all cache
            $this->cache->invalidate_all_cache();
            
            $this->logger->info('Cache cleared by admin', array(
                'admin_id' => get_current_user_id(),
                'timestamp' => current_time('mysql')
            ));
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Cache cleared successfully. Leaderboard will refresh on next load.'
            ));
            
        } catch (Exception $e) {
            $this->logger->error('Failed to clear cache', array(
                'error' => $e->getMessage(),
                'admin_id' => get_current_user_id()
            ));
            
            return new WP_Error('clear_cache_failed', 'Failed to clear cache: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Debug timezone information
     */
    public function rest_debug_timezone($request) {
        try {
            global $wpdb;
            
            $user_id = get_current_user_id();
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to debug timezone', array('status' => 401));
            }
            
            // Get timezone information
            $wp_timezone = wp_timezone();
            $current_timezone = date_default_timezone_get();
            $wp_timezone_string = get_option('timezone_string');
            $gmt_offset = get_option('gmt_offset');
            
            // Get current times
            $current_time_mysql = current_time('mysql');
            $current_time_timestamp = current_time('timestamp');
            $current_time_utc = current_time('mysql', true);
            
            // Get recent practice sessions for timezone analysis
            $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
            $recent_sessions = $wpdb->get_results($wpdb->prepare("
                SELECT created_at, duration_minutes 
                FROM $sessions_table 
                WHERE user_id = %d 
                ORDER BY created_at DESC 
                LIMIT 5
            ", $user_id), ARRAY_A);
            
            $session_analysis = array();
            foreach ($recent_sessions as $session) {
                $session_datetime = new DateTime($session['created_at'], $wp_timezone);
                $session_hour = (int)$session_datetime->format('H');
                
                $session_analysis[] = array(
                    'created_at' => $session['created_at'],
                    'hour' => $session_hour,
                    'is_early_bird' => $session_hour >= 5 && $session_hour < 8,
                    'is_night_owl' => $session_hour >= 22 || $session_hour < 6,
                    'duration_minutes' => $session['duration_minutes']
                );
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'timezone_info' => array(
                    'wp_timezone_string' => $wp_timezone_string,
                    'wp_timezone_name' => $wp_timezone->getName(),
                    'wp_timezone_offset' => $wp_timezone->getOffset(new DateTime()),
                    'current_timezone' => $current_timezone,
                    'gmt_offset' => $gmt_offset
                ),
                'current_times' => array(
                    'mysql_local' => $current_time_mysql,
                    'mysql_utc' => $current_time_utc,
                    'timestamp_local' => $current_time_timestamp,
                    'timestamp_utc' => time()
                ),
                'recent_sessions' => $session_analysis,
                'badge_criteria_analysis' => array(
                    'early_bird_sessions' => count(array_filter($session_analysis, function($s) { return $s['is_early_bird']; })),
                    'night_owl_sessions' => count(array_filter($session_analysis, function($s) { return $s['is_night_owl']; })),
                    'total_sessions_analyzed' => count($session_analysis)
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('timezone_debug_error', 'Error debugging timezone: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Update badges schema
     */
    public function rest_update_badges_schema($request) {
        try {
            $result = APH_Database_Schema::update_badges_schema();
            
            if ($result) {
                $this->logger->info('Badges schema updated successfully', array(
                    'admin_id' => get_current_user_id(),
                    'timestamp' => current_time('mysql')
                ));
                
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Badges schema updated successfully'
                ));
            } else {
                return new WP_Error('schema_update_error', 'Failed to update badges schema', array('status' => 500));
            }
            
        } catch (Exception $e) {
            $this->logger->error('Failed to update badges schema', array(
                'error' => $e->getMessage(),
                'admin_id' => get_current_user_id()
            ));
            return new WP_Error('schema_update_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    
    
    /**
     * Ensure default badges exist in the database
     */
    private function ensure_default_badges() {
        global $wpdb;
        
        $badges_table = $wpdb->prefix . 'jph_badges';
        
        $default_badges = array(
            'first_session' => array(
                'name' => 'First Practice',
                'description' => 'Completed your first practice session',
                'category' => 'milestone',
                'criteria_type' => 'practice_sessions',
                'criteria_value' => 1,
                'xp_reward' => 10,
                'gem_reward' => 5
            ),
            'streak_7' => array(
                'name' => 'Week Warrior',
                'description' => 'Maintained a 7-day practice streak',
                'category' => 'streak',
                'criteria_type' => 'streak',
                'criteria_value' => 7,
                'xp_reward' => 50,
                'gem_reward' => 25
            ),
            'streak_30' => array(
                'name' => 'Monthly Master',
                'description' => 'Maintained a 30-day practice streak',
                'category' => 'streak',
                'criteria_type' => 'streak',
                'criteria_value' => 30,
                'xp_reward' => 200,
                'gem_reward' => 100
            ),
            'level_5' => array(
                'name' => 'Level 5 Achiever',
                'description' => 'Reached level 5',
                'category' => 'level',
                'criteria_type' => 'level_reached',
                'criteria_value' => 5,
                'xp_reward' => 100,
                'gem_reward' => 50
            ),
            'level_10' => array(
                'name' => 'Level 10 Master',
                'description' => 'Reached level 10',
                'category' => 'level',
                'criteria_type' => 'level_reached',
                'criteria_value' => 10,
                'xp_reward' => 300,
                'gem_reward' => 150
            ),
            'xp_1000' => array(
                'name' => 'XP Collector',
                'description' => 'Earned 1000 XP points',
                'category' => 'xp',
                'criteria_type' => 'xp',
                'criteria_value' => 1000,
                'xp_reward' => 50,
                'gem_reward' => 25
            ),
            'xp_5000' => array(
                'name' => 'XP Master',
                'description' => 'Earned 5000 XP points',
                'category' => 'xp',
                'criteria_type' => 'xp',
                'criteria_value' => 5000,
                'xp_reward' => 200,
                'gem_reward' => 100
            ),
            'sessions_10' => array(
                'name' => 'Practice Regular',
                'description' => 'Completed 10 practice sessions',
                'category' => 'sessions',
                'criteria_type' => 'practice_sessions',
                'criteria_value' => 10,
                'xp_reward' => 75,
                'gem_reward' => 35
            ),
            'sessions_50' => array(
                'name' => 'Practice Pro',
                'description' => 'Completed 50 practice sessions',
                'category' => 'sessions',
                'criteria_type' => 'practice_sessions',
                'criteria_value' => 50,
                'xp_reward' => 250,
                'gem_reward' => 125
            ),
            'minutes_100' => array(
                'name' => 'Century Club',
                'description' => 'Practiced for 100 minutes total',
                'category' => 'time',
                'criteria_type' => 'total_time',
                'criteria_value' => 100,
                'xp_reward' => 100,
                'gem_reward' => 50
            ),
            'minutes_500' => array(
                'name' => 'Marathon Player',
                'description' => 'Practiced for 500 minutes total',
                'category' => 'time',
                'criteria_type' => 'total_time',
                'criteria_value' => 500,
                'xp_reward' => 400,
                'gem_reward' => 200
            )
        );
        
        foreach ($default_badges as $badge_key => $badge_data) {
            // Check if badge already exists
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT badge_key FROM {$badges_table} WHERE badge_key = %s",
                $badge_key
            ));
            
            if (!$existing) {
                // Insert the badge
                $wpdb->insert($badges_table, array_merge(
                    array('badge_key' => $badge_key),
                    $badge_data,
                    array(
                        'is_active' => 1,
                        'display_order' => 0,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    )
                ));
            }
        }
    }
    
    /**
     * Generate random practice notes
     */
    private function generate_random_notes() {
        $notes = array(
            'Great practice session!',
            'Working on technique today.',
            'Focused on scales and arpeggios.',
            'Practicing jazz standards.',
            'Working on sight reading.',
            'Concentrated on rhythm and timing.',
            'Practicing chord progressions.',
            'Working on improvisation.',
            'Focused on classical pieces.',
            'Practicing with metronome.',
            'Working on dynamics and expression.',
            'Practicing different styles.',
            'Great progress today!',
            'Challenging but rewarding session.',
            'Working on finger independence.',
            'Practicing scales in all keys.',
            'Focused on musicality.',
            'Working on memorization.',
            'Practicing with backing tracks.',
            'Great technique work today!'
        );
        
        return $notes[array_rand($notes)];
    }
    
    /**
     * Update lesson favorite
     */
    public function rest_update_lesson_favorite($request) {
        try {
            global $wpdb;
            
            $params = $request->get_params();
            $favorite_id = intval($params['favorite_id']);
            $title = sanitize_text_field($params['title']);
            $category = sanitize_text_field($params['category']);
            $url = esc_url_raw($params['url']);
            $description = sanitize_textarea_field($params['description']);
            
            // Validate required fields
            if (empty($favorite_id) || empty($title) || empty($url)) {
                return new WP_Error('invalid_data', 'Missing required fields', array('status' => 400));
            }
            
            // Validate category
            $allowed_categories = array('lesson', 'technique', 'theory', 'ear-training', 'repertoire', 'improvisation', 'other');
            if (!in_array($category, $allowed_categories)) {
                $category = 'other';
            }
            
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
            
            // Check if favorite exists
            $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $favorite_id));
            if (!$existing) {
                return new WP_Error('not_found', 'Lesson favorite not found', array('status' => 404));
            }
            
            // Update the favorite
            $result = $wpdb->update(
                $table_name,
                array(
                    'title' => $title,
                    'category' => $category,
                    'url' => $url,
                    'description' => $description,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $favorite_id),
                array('%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );
            
            if ($result === false) {
                return new WP_Error('update_failed', 'Failed to update lesson favorite', array('status' => 500));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Lesson favorite updated successfully'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('update_lesson_favorite_error', 'Error updating lesson favorite: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Delete lesson favorite
     */
    public function rest_delete_lesson_favorite($request) {
        try {
            global $wpdb;
            
            $params = $request->get_params();
            $favorite_id = intval($params['favorite_id']);
            
            if (empty($favorite_id)) {
                return new WP_Error('invalid_data', 'Missing favorite ID', array('status' => 400));
            }
            
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
            
            // Check if favorite exists
            $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $favorite_id));
            if (!$existing) {
                return new WP_Error('not_found', 'Lesson favorite not found', array('status' => 404));
            }
            
            // Delete the favorite
            $result = $wpdb->delete(
                $table_name,
                array('id' => $favorite_id),
                array('%d')
            );
            
            if ($result === false) {
                return new WP_Error('delete_failed', 'Failed to delete lesson favorite', array('status' => 500));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Lesson favorite deleted successfully'
            ));
            
        } catch (Exception $e) {
            return new WP_Error('delete_lesson_favorite_error', 'Error deleting lesson favorite: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Clear existing test data
     */
    private function clear_test_data() {
        global $wpdb;
        
        // Get all test users
        $test_users = $wpdb->get_results("SELECT ID FROM {$wpdb->users} WHERE user_login LIKE 'test_student_%'", ARRAY_A);
        
        if (empty($test_users)) {
            return;
        }
        
        $user_ids = array_column($test_users, 'ID');
        $user_ids_str = implode(',', $user_ids);
        
        // Clear user stats
        $wpdb->query("DELETE FROM {$wpdb->prefix}jph_user_stats WHERE user_id IN ($user_ids_str)");
        
        // Clear practice sessions
        $wpdb->query("DELETE FROM {$wpdb->prefix}jph_practice_sessions WHERE user_id IN ($user_ids_str)");
        
        // Clear practice items
        $wpdb->query("DELETE FROM {$wpdb->prefix}jph_practice_items WHERE user_id IN ($user_ids_str)");
        
        // Clear user badges
        $wpdb->query("DELETE FROM {$wpdb->prefix}jph_user_badges WHERE user_id IN ($user_ids_str)");
        
        // Clear gem transactions
        $wpdb->query("DELETE FROM {$wpdb->prefix}jph_gem_transactions WHERE user_id IN ($user_ids_str)");
        $wpdb->query("DELETE FROM {$wpdb->prefix}jph_gems_transactions WHERE user_id IN ($user_ids_str)");
        
        // Clear lesson favorites
        $wpdb->query("DELETE FROM {$wpdb->prefix}jph_lesson_favorites WHERE user_id IN ($user_ids_str)");
        
        // Clear JPC user data
        $wpdb->query("DELETE FROM {$wpdb->prefix}jph_jpc_user_assignments WHERE user_id IN ($user_ids_str)");
        $wpdb->query("DELETE FROM {$wpdb->prefix}jph_jpc_user_progress WHERE user_id IN ($user_ids_str)");
        $wpdb->query("DELETE FROM {$wpdb->prefix}jph_jpc_milestone_submissions WHERE user_id IN ($user_ids_str)");
        
        // Clear audit logs
        $wpdb->query("DELETE FROM {$wpdb->prefix}jph_audit_logs WHERE user_id IN ($user_ids_str)");
    }
    
    /**
     * Test JPC endpoint (no permissions required)
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function rest_jpc_test($request) {
        // Check nonce for WP Engine compatibility
        $nonce = $request->get_header('X-WP-Nonce');
        $nonce_valid = $nonce ? wp_verify_nonce($nonce, 'wp_rest') : false;
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'JPC test endpoint working',
            'user_id' => get_current_user_id(),
            'is_logged_in' => is_user_logged_in(),
            'nonce_provided' => !empty($nonce),
            'nonce_valid' => $nonce_valid,
            'current_user_login' => wp_get_current_user()->user_login ?? 'none',
            'wp_engine_note' => 'Using nonce-based auth for WP Engine compatibility',
            'timestamp' => current_time('mysql')
        ), 200);
    }
    
    /**
     * Debug JPC system
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function rest_jpc_debug($request) {
        global $wpdb;
        
        $debug_info = array(
            'user_id' => get_current_user_id(),
            'is_user_logged_in' => is_user_logged_in(),
            'current_user' => wp_get_current_user()->user_login ?? 'none',
            'jpc_handler_exists' => class_exists('JPH_JPC_Handler'),
            'tables_exist' => array(),
            'rest_routes' => array(),
            'errors' => array(),
            'nonce_check' => array()
        );
        
        // Check nonce if provided
        if ($request) {
            $nonce = $request->get_header('X-WP-Nonce');
            $debug_info['nonce_check']['provided'] = !empty($nonce);
            $debug_info['nonce_check']['valid'] = $nonce ? wp_verify_nonce($nonce, 'wp_rest') : false;
        }
        
        // Check if JPC tables exist
        $jpc_tables = array(
            'jph_jpc_curriculum',
            'jph_jpc_steps',
            'jph_jpc_user_assignments', 
            'jph_jpc_user_progress'
        );
        
        foreach ($jpc_tables as $table) {
            $full_table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'");
            $debug_info['tables_exist'][$table] = !empty($exists);
        }
        
        // Check REST routes
        $rest_routes = rest_get_server()->get_routes();
        $debug_info['rest_routes']['jpc_complete'] = isset($rest_routes['/aph/v1/jpc/complete']);
        $debug_info['rest_routes']['jpc_debug'] = isset($rest_routes['/aph/v1/jpc/debug']);
        
        // Try to create JPC tables manually (bypass schema system for now)
        try {
            $debug_info['manual_table_creation'] = array();
            
            // Create jph_jpc_curriculum table
            $sql1 = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jph_jpc_curriculum` (
                `id` INT(11) NOT NULL PRIMARY KEY,
                `focus_order` DECIMAL(5,2) NOT NULL,
                `focus_title` VARCHAR(100) NOT NULL,
                `focus_pillar` VARCHAR(50) NOT NULL,
                `focus_element` VARCHAR(50) NOT NULL,
                `tempo` SMALLINT(6) NOT NULL,
                `resource_pdf` VARCHAR(70) NULL,
                `resource_ireal` VARCHAR(50) NULL,
                `resource_mp3` VARCHAR(50) NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $result1 = $wpdb->query($sql1);
            $debug_info['manual_table_creation']['jpc_curriculum'] = $result1 !== false;
            
            // Create jph_jpc_steps table
            $sql2 = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jph_jpc_steps` (
                `step_id` INT(11) NOT NULL PRIMARY KEY,
                `curriculum_id` INT(11) NOT NULL,
                `key_sig` TINYINT(4) NOT NULL,
                `key_sig_name` VARCHAR(10) NULL,
                `vimeo_id` INT(11) NOT NULL,
                `resource` TEXT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $result2 = $wpdb->query($sql2);
            $debug_info['manual_table_creation']['jpc_steps'] = $result2 !== false;
            
            // Create jph_jpc_user_assignments table
            $sql3 = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jph_jpc_user_assignments` (
                `id` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `step_id` INT(11) NOT NULL,
                `curriculum_id` INT(11) NOT NULL,
                `assigned_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `completed_on` DATETIME NULL,
                `deleted_at` DATETIME NULL,
                KEY `user_id` (`user_id`),
                KEY `step_id` (`step_id`),
                KEY `curriculum_id` (`curriculum_id`),
                UNIQUE KEY `unique_user_active` (`user_id`, `deleted_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $result3 = $wpdb->query($sql3);
            $debug_info['manual_table_creation']['jpc_user_assignments'] = $result3 !== false;
            
            // Create jph_jpc_user_progress table
            $sql4 = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jph_jpc_user_progress` (
                `id` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `curriculum_id` INT(11) NOT NULL,
                `step_1` INT(11) NULL,
                `step_2` INT(11) NULL,
                `step_3` INT(11) NULL,
                `step_4` INT(11) NULL,
                `step_5` INT(11) NULL,
                `step_6` INT(11) NULL,
                `step_7` INT(11) NULL,
                `step_8` INT(11) NULL,
                `step_9` INT(11) NULL,
                `step_10` INT(11) NULL,
                `step_11` INT(11) NULL,
                `step_12` INT(11) NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `user_id` (`user_id`),
                KEY `curriculum_id` (`curriculum_id`),
                UNIQUE KEY `unique_user_curriculum` (`user_id`, `curriculum_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $result4 = $wpdb->query($sql4);
            $debug_info['manual_table_creation']['jpc_user_progress'] = $result4 !== false;
            
            $debug_info['last_error'] = $wpdb->last_error;
            
            // Check tables again after creation attempt
            foreach ($jpc_tables as $table) {
                $full_table_name = $wpdb->prefix . $table;
                $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'");
                $debug_info['tables_after_creation'][$table] = !empty($exists);
            }
            
        } catch (Exception $e) {
            $debug_info['errors'][] = 'Manual table creation error: ' . $e->getMessage();
        }
        
        // Test JPC Handler if it exists
        if (class_exists('JPH_JPC_Handler')) {
            try {
                $user_id = get_current_user_id();
                if ($user_id > 0) {
                    $assignment = JPH_JPC_Handler::get_user_current_assignment($user_id);
                    $debug_info['user_assignment'] = $assignment ? 'Found' : 'Not found';
                } else {
                    $debug_info['user_assignment'] = 'No user logged in';
                }
            } catch (Exception $e) {
                $debug_info['errors'][] = 'JPC Handler error: ' . $e->getMessage();
            }
        }
        
        return new WP_REST_Response($debug_info, 200);
    }
    
    /**
     * REST endpoint to debug JPC completion
     */
    public function rest_jpc_debug_complete($request) {
        $user_id = intval($request->get_param('user_id'));
        $step_id = intval($request->get_param('step_id'));
        $curriculum_id = intval($request->get_param('curriculum_id'));
        
        $debug_info = array(
            'timestamp' => current_time('mysql'),
            'user_id' => $user_id,
            'step_id' => $step_id,
            'curriculum_id' => $curriculum_id,
            'jpc_handler_exists' => class_exists('JPH_JPC_Handler'),
            'current_user_id' => get_current_user_id(),
            'is_user_logged_in' => is_user_logged_in(),
            'nonce_check' => wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')
        );
        
        // Test database connection
        global $wpdb;
        $debug_info['database_connection'] = !empty($wpdb);
        
        // Test if tables exist
        $tables_to_check = array(
            'jph_jpc_user_assignments' => $wpdb->prefix . 'jph_jpc_user_assignments',
            'jph_jpc_user_progress' => $wpdb->prefix . 'jph_jpc_user_progress',
            'je_practice_curriculum_steps' => 'je_practice_curriculum_steps',  // No prefix
            'je_practice_curriculum' => 'je_practice_curriculum'  // No prefix
        );
        
        foreach ($tables_to_check as $key => $table_name) {
            $debug_info['table_exists_' . $key] = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        }
        
        // Test getting current assignment
        if (class_exists('JPH_JPC_Handler')) {
            $assignment = JPH_JPC_Handler::get_user_current_assignment($user_id);
            $debug_info['current_assignment'] = $assignment;
            
            $progress = JPH_JPC_Handler::get_user_progress($user_id, $curriculum_id);
            $debug_info['current_progress'] = $progress;
        }
        
        return new WP_REST_Response($debug_info, 200);
    }
    
    /**
     * REST endpoint to reset all JPC Practice Hub data
     */
    public function rest_jpc_reset($request) {
        global $wpdb;
        
        error_log("JPC Reset: Starting complete reset of Practice Hub JPC tables");
        
        // Clear all Practice Hub JPC tables
        $tables_to_clear = array(
            'jph_jpc_user_assignments',
            'jph_jpc_user_progress',
            'jph_jpc_milestone_submissions'
        );
        
        $results = array();
        
        foreach ($tables_to_clear as $table) {
            $deleted = $wpdb->query("DELETE FROM {$wpdb->prefix}{$table}");
            $results[$table] = $deleted;
            error_log("JPC Reset: Cleared {$deleted} rows from {$table}");
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'All Practice Hub JPC data cleared',
            'tables_cleared' => $results,
            'note' => 'Curriculum and steps tables preserved (read-only reference data)'
        ));
    }
    
    /**
     * Migrate JPC data from old tables to new system
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function rest_migrate_jpc_data($request) {
        // Check if user has admin capabilities
        if (!current_user_can('manage_options')) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Insufficient permissions'
            ), 403);
        }
        
        $dry_run = $request->get_param('dry_run') === 'true';
        $options = array(
            'batch_size' => intval($request->get_param('batch_size')) ?: 100,
            'skip_existing' => $request->get_param('skip_existing') !== 'false',
            'include_milestones' => $request->get_param('include_milestones') !== 'false',
            'log_level' => $request->get_param('log_level') ?: 'info'
        );
        
        // Include the migration class
        if (!class_exists('JPH_JPC_Migration')) {
            require_once plugin_dir_path(__FILE__) . 'class-jpc-migration.php';
        }
        
        // Validate prerequisites first
        $validation = JPH_JPC_Migration::validate_prerequisites();
        if (!$validation['valid']) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Migration prerequisites not met',
                'issues' => $validation['issues']
            ), 400);
        }
        
        // Perform migration
        $result = JPH_JPC_Migration::migrate_all_jpc_data($dry_run, $options);
        
        if ($result['success']) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => $dry_run ? 'Migration analysis completed' : 'Migration completed successfully',
                'dry_run' => $dry_run,
                'stats' => $result['stats'],
                'results' => $result['results']
            ), 200);
        } else {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Migration failed',
                'error' => $result['error'],
                'stats' => $result['stats']
            ), 500);
        }
    }
    
    /**
     * Get JPC progress for a user
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function rest_get_jpc_progress($request) {
        $user_id = intval($request->get_param('user_id'));
        
        if (!$user_id) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid user ID'
            ), 400);
        }
        
        if (class_exists('JPH_JPC_Handler')) {
            // Get current assignment
            $assignment = JPH_JPC_Handler::get_user_current_assignment($user_id);
            
            // Get progress for current curriculum
            $progress = null;
            if ($assignment) {
                $progress = JPH_JPC_Handler::get_user_progress($user_id, $assignment['curriculum_id']);
            }
            
            return new WP_REST_Response(array(
                'success' => true,
                'data' => array(
                    'assignment' => $assignment,
                    'progress' => $progress
                )
            ), 200);
        }
        
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'JPC Handler not available'
        ), 500);
    }
    
    /**
     * Mark JPC step as complete
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function rest_mark_jpc_complete($request) {
        // Get user ID from request (we'll pass it from the frontend)
        $user_id = intval($request->get_param('user_id'));
        
        // Validate user ID
        if (!$user_id || $user_id <= 0) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid user ID. Please refresh the page and try again.'
            ), 400);
        }
        
        // Verify user exists
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'User not found.'
            ), 400);
        }
        
        $step_id = intval($request->get_param('step_id'));
        $curriculum_id = intval($request->get_param('curriculum_id'));
        
        if (!$step_id || !$curriculum_id) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters'
            ), 400);
        }
        
        // Validate parameters
        if ($step_id <= 0 || $curriculum_id <= 0) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid parameters'
            ), 400);
        }
        
        try {
            // Debug logging
            error_log("JPC Completion Request: user_id=$user_id, step_id=$step_id, curriculum_id=$curriculum_id");
            
            // Mark step complete using JPC handler
            if (class_exists('JPH_JPC_Handler')) {
                error_log("JPH_JPC_Handler class exists, calling mark_step_complete");
                $result = JPH_JPC_Handler::mark_step_complete($user_id, $step_id, $curriculum_id);
                error_log("JPC Handler result: " . print_r($result, true));
                
                if ($result['success']) {
                    // Log the action (simplified for now)
                    error_log("JPC Step Completed: user_id=$user_id, step_id=$step_id, curriculum_id=$curriculum_id, xp_earned={$result['xp_earned']}, gems_earned={$result['gems_earned']}");
                    
                    return new WP_REST_Response(array(
                        'success' => true,
                        'message' => $result['message'],
                        'data' => array(
                            'xp_earned' => $result['xp_earned'],
                            'gems_earned' => $result['gems_earned'],
                            'keys_completed' => $result['keys_completed'],
                            'all_keys_complete' => $result['all_keys_complete'],
                            'next_assignment' => $result['next_assignment']
                        )
                    ), 200);
                } else {
                    error_log("JPC Handler returned failure: " . $result['message']);
                    return new WP_REST_Response(array(
                        'success' => false,
                        'message' => $result['message']
                    ), 400);
                }
            } else {
                error_log("JPH_JPC_Handler class not found");
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => 'JPC handler not available. Please check if the plugin is properly activated.'
                ), 500);
            }
            
        } catch (Exception $e) {
            $this->logger->log_error('JPC completion error', array(
                'user_id' => $user_id,
                'step_id' => $step_id,
                'curriculum_id' => $curriculum_id,
                'error' => $e->getMessage()
            ));
            
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'An error occurred while processing your request'
            ), 500);
        }
    }
    
    /**
     * AJAX handler to verify step completion for modal security
     */
    public function ajax_verify_step_completion() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'jpc_verify_step')) {
            wp_die('Security check failed');
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $user_id = get_current_user_id();
        $step_id = intval($_POST['step_id']);
        $curriculum_id = intval($_POST['curriculum_id']);
        
        if (!$step_id || !$curriculum_id) {
            wp_send_json_error('Invalid parameters');
        }
        
        // Get user's progress for this curriculum
        $progress = JPH_JPC_Handler::get_user_progress($user_id, $curriculum_id);
        
        // Check if user has completed this step
        $completed = false;
        $is_current_step = false;
        $video_url = null;
        
        for ($i = 1; $i <= 12; $i++) {
            if (!empty($progress['step_' . $i]) && $progress['step_' . $i] == $step_id) {
                $completed = true;
                break;
            }
        }
        
        // Check if this is the current step (not yet completed)
        if (!$completed) {
            $current_assignment = JPH_JPC_Handler::get_user_current_assignment($user_id);
            if ($current_assignment && $current_assignment['step_id'] == $step_id) {
                $is_current_step = true;
            }
        }
        
        // Get the Vimeo ID from the step data
        $vimeo_id = null;
        if ($completed || $is_current_step) {
            $step_details = JPH_JPC_Handler::get_step_details($step_id);
            if ($step_details && !empty($step_details['vimeo_id'])) {
                $vimeo_id = $step_details['vimeo_id'];
            }
        }
        
        wp_send_json_success(array(
            'completed' => $completed,
            'is_current_step' => $is_current_step,
            'vimeo_id' => $vimeo_id
        ));
    }
    
    /**
     * AJAX handler to mark step as complete
     */
    public function ajax_mark_step_complete() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'jpc_mark_complete')) {
            wp_die('Security check failed');
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $user_id = get_current_user_id();
        $step_id = intval($_POST['step_id']);
        $curriculum_id = intval($_POST['curriculum_id']);
        
        if (!$step_id || !$curriculum_id) {
            wp_send_json_error('Invalid parameters');
        }
        
        // Mark step as complete using the JPC handler
        $result = JPH_JPC_Handler::mark_step_complete($user_id, $step_id, $curriculum_id);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX handler to get FVPlayer HTML for modal
     */
    public function ajax_get_fvplayer() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'jpc_fvplayer')) {
            wp_die('Security check failed');
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $vimeo_id = intval($_POST['vimeo_id']);
        
        if (!$vimeo_id) {
            wp_send_json_error('Invalid Vimeo ID');
        }
        
        // Generate FVPlayer shortcode
        $shortcode = "[fvplayer src='https://vimeo.com/{$vimeo_id}']";
        $player_html = do_shortcode($shortcode);
        
        // If shortcode didn't process, try alternative approach
        if (empty($player_html) || $player_html === $shortcode) {
            // Fallback: create a simple video element
            $player_html = '<video controls width="100%" height="100%">';
            $player_html .= '<source src="https://vimeo.com/' . $vimeo_id . '" type="video/mp4">';
            $player_html .= 'Your browser does not support the video tag.';
            $player_html .= '</video>';
        }
        
        error_log("JPCXP: FVPlayer HTML generated for Vimeo ID $vimeo_id: " . substr($player_html, 0, 200) . "...");
        
        wp_send_json_success(array(
            'player_html' => $player_html,
            'vimeo_id' => $vimeo_id
        ));
    }
    
    /**
     * AJAX handler to submit milestone for grading
     */
    public function ajax_submit_milestone() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'jpc_submit_milestone')) {
            wp_die('Security check failed');
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $user_id = get_current_user_id();
        $curriculum_id = intval($_POST['curriculum_id']);
        $youtube_url = sanitize_text_field($_POST['youtube_url']);
        
        if (!$curriculum_id || !$youtube_url) {
            wp_send_json_error('Invalid parameters');
        }
        
        // Validate YouTube URL
        if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/', $youtube_url)) {
            wp_send_json_error('Please enter a valid YouTube URL');
        }
        
        // Check if user has already submitted for this curriculum
        global $wpdb;
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id, grade FROM {$wpdb->prefix}jph_jpc_milestone_submissions 
             WHERE user_id = %d AND curriculum_id = %d",
            $user_id, $curriculum_id
        ));
        
        if ($existing) {
            // Allow resubmission only if grade is 'redo'
            if ($existing->grade !== 'redo') {
                wp_send_json_error('You have already submitted a video for this focus');
            }
        }
        
        // Check if table exists, create if it doesn't
        $table_name = $wpdb->prefix . 'jph_jpc_milestone_submissions';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            error_log("JPCXP: Table $table_name does not exist, creating it...");
            aph_create_milestone_submissions_table();
            
            // Check again after creation
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                error_log("JPCXP: Failed to create table $table_name");
                wp_send_json_error('Database table could not be created. Please contact support.');
            }
        }
        
        // Insert or update submission
        if ($existing && $existing->grade === 'redo') {
            // Update existing redo submission
            $result = $wpdb->update(
                $table_name,
                array(
                    'video_url' => $youtube_url,
                    'submission_date' => current_time('mysql'),
                    'grade' => null, // Reset grade for new submission
                    'graded_on' => null,
                    'teacher_notes' => null,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $existing->id),
                array('%s', '%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );
            
            if ($result !== false) {
                error_log("JPCXP: Redo submission updated - user_id=$user_id, curriculum_id=$curriculum_id, video_url=$youtube_url");
                wp_send_json_success(array(
                    'message' => 'Redo video submitted successfully for grading'
                ));
            } else {
                error_log("JPCXP: Database update failed - Error: " . $wpdb->last_error);
                wp_send_json_error('Failed to submit redo video: ' . $wpdb->last_error);
            }
        } else {
            // Insert new submission
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'curriculum_id' => $curriculum_id,
                    'video_url' => $youtube_url,
                    'submission_date' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s')
            );
            
            if ($result) {
                error_log("JPCXP: Milestone submitted - user_id=$user_id, curriculum_id=$curriculum_id, video_url=$youtube_url");
                wp_send_json_success(array(
                    'message' => 'Video submitted successfully for grading'
                ));
            } else {
                error_log("JPCXP: Database insert failed - Error: " . $wpdb->last_error);
                wp_send_json_error('Failed to submit video: ' . $wpdb->last_error);
            }
        }
    }
    
    /**
     * AJAX handler for AI cleanup of teacher feedback
     */
    public function ajax_ai_cleanup_feedback() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_cleanup_feedback')) {
            wp_die('Security check failed');
        }
        
        // Check if user has admin capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $text = sanitize_textarea_field($_POST['text']);
        
        if (empty($text)) {
            wp_send_json_error('No text provided');
        }
        
        // Get AI prompt from settings
        $ai_prompt = get_option('jpc_ai_milestone_prompt', $this->get_default_ai_prompt());
        
        // Call AI service (using OpenAI API)
        $cleaned_text = $this->call_ai_cleanup_service($text, $ai_prompt);
        
        if ($cleaned_text) {
            wp_send_json_success(array(
                'cleaned_text' => $cleaned_text,
                'original_text' => $text
            ));
        } else {
            wp_send_json_error('AI cleanup failed');
        }
    }
    
    /**
     * Call AI service to clean up feedback text using Katahdin AI Hub
     */
    private function call_ai_cleanup_service($text, $prompt) {
        // Use Katahdin AI Hub for consistency with existing practice analysis
        return $this->call_katahdin_ai($text, $prompt, 'gpt-3.5-turbo', 500, 0.7);
    }
    
    /**
     * Get default AI prompt for milestone grading
     */
    private function get_default_ai_prompt() {
        return "You are an AI assistant helping music teachers provide constructive feedback to students. 

Your task is to clean up and improve teacher feedback for music milestone submissions while maintaining the teacher's voice and intent.

Guidelines:
- Keep the teacher's original meaning and tone
- Fix grammar, spelling, and punctuation errors
- Make the feedback more clear and constructive
- Ensure feedback is encouraging but honest
- Keep it concise but comprehensive
- Maintain professional but friendly tone
- Don't change the core message or criticism

Return only the cleaned feedback text, no explanations or additional commentary.";
    }
    
    /**
     * Get analytics overview data
     */
    public function rest_get_analytics_overview($request) {
        try {
            global $wpdb;
            
            $stats_table = $wpdb->prefix . 'jph_user_stats';
            
            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$stats_table}'");
            if (!$table_exists) {
                return wp_send_json_error('User stats table does not exist');
            }
            
            // Total students
            $total_students = $wpdb->get_var("
                SELECT COUNT(DISTINCT us.user_id) 
                FROM {$stats_table} us
                INNER JOIN {$wpdb->users} u ON us.user_id = u.ID
            ");
            
            // Active students (practiced in last 7 days)
            $active_students = $wpdb->get_var("
                SELECT COUNT(DISTINCT us.user_id) 
                FROM {$stats_table} us
                INNER JOIN {$wpdb->users} u ON us.user_id = u.ID
                WHERE us.last_practice_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            
            // At-risk students (30+ days since last practice)
            $at_risk_students = $wpdb->get_var("
                SELECT COUNT(DISTINCT us.user_id) 
                FROM {$stats_table} us
                INNER JOIN {$wpdb->users} u ON us.user_id = u.ID
                WHERE us.last_practice_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
                OR us.last_practice_date IS NULL
            ");
            
            // Average practice time (last 30 days)
            $avg_practice_time = $wpdb->get_var("
                SELECT AVG(ps.duration_minutes) 
                FROM {$wpdb->prefix}jph_practice_sessions ps
                WHERE ps.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            
            return wp_send_json_success(array(
                'total_students' => (int) $total_students,
                'active_students' => (int) $active_students,
                'at_risk_students' => (int) $at_risk_students,
                'avg_practice_time' => round($avg_practice_time ?: 0, 1) . ' min'
            ));
            
        } catch (Exception $e) {
            return wp_send_json_error('Error loading analytics overview: ' . $e->getMessage());
        }
    }
    
    /**
     * Get analytics students data with filtering
     */
    public function rest_get_analytics_students($request) {
        try {
            global $wpdb;
            
            $risk_filter = $request->get_param('risk');
            $level_filter = $request->get_param('level');
            $search_term = $request->get_param('search');
            
            $stats_table = $wpdb->prefix . 'jph_user_stats';
            
            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$stats_table}'");
            if (!$table_exists) {
                return wp_send_json_error('User stats table does not exist');
            }
            
            $where_conditions = array();
            
            // Risk level filtering
            if ($risk_filter === 'low') {
                $where_conditions[] = "us.last_practice_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            } elseif ($risk_filter === 'medium') {
                $where_conditions[] = "us.last_practice_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND us.last_practice_date < DATE_SUB(NOW(), INTERVAL 7 DAY)";
            } elseif ($risk_filter === 'high') {
                $where_conditions[] = "(us.last_practice_date < DATE_SUB(NOW(), INTERVAL 30 DAY) OR us.last_practice_date IS NULL)";
            }
            
            // Level filtering
            if ($level_filter && $level_filter !== 'all') {
                if ($level_filter === '5') {
                    $where_conditions[] = "us.current_level >= 5";
                } else {
                    $where_conditions[] = $wpdb->prepare("us.current_level = %d", $level_filter);
                }
            }
            
            // Search filtering
            if ($search_term) {
                $where_conditions[] = $wpdb->prepare("(u.display_name LIKE %s OR u.user_email LIKE %s)", 
                    '%' . $search_term . '%', '%' . $search_term . '%');
            }
            
            $where_clause = '';
            if (!empty($where_conditions)) {
                $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
            }
            
            // Check if last_email_sent column exists
            $columns = $wpdb->get_col("DESCRIBE {$stats_table}");
            $email_column = in_array('last_email_sent', $columns) ? 'us.last_email_sent' : 'NULL as last_email_sent';
            
            $query = "
                SELECT 
                    us.user_id,
                    u.display_name,
                    u.user_email,
                    us.current_level as level,
                    us.last_practice_date,
                    us.total_sessions,
                    us.current_streak,
                    {$email_column},
                    CASE 
                        WHEN us.last_practice_date IS NULL THEN DATEDIFF(NOW(), us.created_at)
                        ELSE DATEDIFF(NOW(), us.last_practice_date)
                    END as days_since_last_practice
                FROM {$stats_table} us
                INNER JOIN {$wpdb->users} u ON us.user_id = u.ID
                {$where_clause}
                ORDER BY days_since_last_practice DESC, us.last_practice_date DESC
                LIMIT 100
            ";
            
            $students = $wpdb->get_results($query, ARRAY_A);
            
            // Debug information
            $debug_info = array(
                'query' => $query,
                'students_count' => count($students),
                'table_name' => $stats_table,
                'filters_applied' => array(
                    'risk' => $risk_filter,
                    'level' => $level_filter,
                    'search' => $search_term
                )
            );
            
            return wp_send_json_success(array(
                'students' => $students,
                'debug' => $debug_info
            ));
            
        } catch (Exception $e) {
            return wp_send_json_error('Error loading analytics students: ' . $e->getMessage());
        }
    }
    
    /**
     * Send outreach email to student
     */
    public function rest_send_outreach_email($request) {
        try {
            $user_id = $request->get_param('user_id');
            
            if (!$user_id) {
                return wp_send_json_error('User ID is required');
            }
            
            $user = get_userdata($user_id);
            if (!$user) {
                return wp_send_json_error('User not found');
            }
            
            // Get student's last practice date
            global $wpdb;
            $stats_table = $wpdb->prefix . 'jph_user_stats';
            $last_practice = $wpdb->get_var($wpdb->prepare(
                "SELECT last_practice_date FROM {$stats_table} WHERE user_id = %d", 
                $user_id
            ));
            
            $days_since = 0;
            if ($last_practice) {
                $days_since = floor((time() - strtotime($last_practice)) / (24 * 60 * 60));
            }
            
            // Create personalized outreach email
            $subject = "We miss you! Let's get back to practicing";
            $message = $this->create_outreach_email_content($user->display_name, $days_since);
            
            $sent = wp_mail($user->user_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
            
            if ($sent) {
                // Update last_email_sent timestamp if column exists
                $stats_table = $wpdb->prefix . 'jph_user_stats';
                $columns = $wpdb->get_col("DESCRIBE {$stats_table}");
                
                if (in_array('last_email_sent', $columns)) {
                    $wpdb->update(
                        $stats_table,
                        array('last_email_sent' => current_time('mysql')),
                        array('user_id' => $user_id),
                        array('%s'),
                        array('%d')
                    );
                }
                
                // Log the outreach
                $this->logger->info('Outreach email sent', array(
                    'user_id' => $user_id,
                    'user_email' => $user->user_email,
                    'days_since_practice' => $days_since
                ));
                
                return wp_send_json_success('Outreach email sent successfully');
            } else {
                return wp_send_json_error('Failed to send email');
            }
            
        } catch (Exception $e) {
            return wp_send_json_error('Error sending outreach email: ' . $e->getMessage());
        }
    }
    
    /**
     * Create outreach email content
     */
    private function create_outreach_email_content($display_name, $days_since) {
        $days_text = $days_since > 0 ? "{$days_since} days" : "a while";
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #459E90;'>Hi {$display_name}!</h2>
                
                <p>We noticed it's been {$days_text} since your last practice session, and we wanted to reach out to see how you're doing.</p>
                
                <p>We know life can get busy, but we're here to help you get back on track with your musical journey. Here are some ways we can support you:</p>
                
                <ul style='margin: 20px 0;'>
                    <li>📚 <strong>Review your practice goals</strong> - Sometimes revisiting why you started can reignite your passion</li>
                    <li>🎵 <strong>Start small</strong> - Even 10-15 minutes of practice can make a difference</li>
                    <li>🎯 <strong>Focus on one piece</strong> - Pick your favorite song and work on it consistently</li>
                    <li>👥 <strong>Join our community</strong> - Connect with other students for motivation and support</li>
                </ul>
                
                <p>Remember, every musician has ups and downs. What matters is getting back to it when you're ready.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='https://jazzedge.academy/dashboard' style='background: #459E90; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>Return to Practice Hub</a>
                </div>
                
                <p>If you're facing any specific challenges or need help with anything, please don't hesitate to reach out. We're here to support your musical journey!</p>
                
                <p>Best regards,<br>
                The JazzEdge Team</p>
                
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>
                    You're receiving this email because you're a valued member of JazzEdge Academy. 
                    If you'd prefer not to receive these check-in emails, you can update your preferences in your account settings.
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Export at-risk students as CSV
     */
    public function rest_export_at_risk_students($request) {
        try {
            global $wpdb;
            
            $stats_table = $wpdb->prefix . 'jph_user_stats';
            
            $students = $wpdb->get_results("
                SELECT 
                    us.user_id,
                    u.display_name,
                    u.user_email,
                    us.level,
                    us.last_practice_date,
                    us.total_practice_sessions,
                    us.current_streak,
                    CASE 
                        WHEN us.last_practice_date IS NULL THEN DATEDIFF(NOW(), us.created_at)
                        ELSE DATEDIFF(NOW(), us.last_practice_date)
                    END as days_since_last_practice
                FROM {$stats_table} us
                INNER JOIN {$wpdb->users} u ON us.user_id = u.ID
                WHERE (us.last_practice_date < DATE_SUB(NOW(), INTERVAL 30 DAY) OR us.last_practice_date IS NULL)
                ORDER BY days_since_last_practice DESC
            ", ARRAY_A);
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="at-risk-students-' . date('Y-m-d') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, array(
                'Student Name',
                'Email',
                'Level',
                'Last Practice Date',
                'Days Since Last Practice',
                'Total Sessions',
                'Current Streak',
                'Risk Level'
            ));
            
            // CSV data
            foreach ($students as $student) {
                $days_since = $student['days_since_last_practice'];
                $risk_level = $days_since > 30 ? 'High' : 'Medium';
                
                fputcsv($output, array(
                    $student['display_name'],
                    $student['user_email'],
                    $student['level'] ?: 'N/A',
                    $student['last_practice_date'] ?: 'Never',
                    $days_since,
                    $student['total_practice_sessions'] ?: 0,
                    $student['current_streak'] ?: 0,
                    $risk_level
                ));
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            wp_send_json_error('Error exporting at-risk students: ' . $e->getMessage());
        }
    }
    
    /**
     * Analyze JPC progress for all students
     */
    public function rest_analyze_jpc_progress($request) {
        global $wpdb;
        
        try {
            // Get all students with JPC assignments
            $students = $wpdb->get_results(
                "SELECT DISTINCT jua.user_id, jua.curriculum_id, jua.step_id, jc.focus_order, jc.focus_title
                 FROM {$wpdb->prefix}jph_jpc_user_assignments jua
                 JOIN {$wpdb->prefix}jph_jpc_curriculum jc ON jua.curriculum_id = jc.id
                 WHERE jua.deleted_at IS NULL
                 ORDER BY jua.user_id, jua.curriculum_id",
                ARRAY_A
            );
            
            $analysis = array(
                'total_students' => 0,
                'correct_assignments' => 0,
                'incorrect_assignments' => 0,
                'students_to_fix' => array()
            );
            
            $processed_users = array();
            
            foreach ($students as $student) {
                $user_id = $student['user_id'];
                
                if (in_array($user_id, $processed_users)) {
                    continue;
                }
                
                $processed_users[] = $user_id;
                $analysis['total_students']++;
                
                // Get student's progress across all focuses
                $all_progress = $wpdb->get_results($wpdb->prepare(
                    "SELECT curriculum_id, step_1, step_2, step_3, step_4, step_5, step_6, 
                            step_7, step_8, step_9, step_10, step_11, step_12
                     FROM {$wpdb->prefix}jph_jpc_user_progress 
                     WHERE user_id = %d",
                    $user_id
                ), ARRAY_A);
                
                // Find completed focuses
                $completed_focuses = array();
                foreach ($all_progress as $progress) {
                    $completed_keys = 0;
                    for ($i = 1; $i <= 12; $i++) {
                        if (!empty($progress['step_' . $i])) {
                            $completed_keys++;
                        }
                    }
                    
                    if ($completed_keys === 12) {
                        $completed_focuses[] = $progress['curriculum_id'];
                    }
                }
                
                // Get current assignment
                $current_assignment = $wpdb->get_row($wpdb->prepare(
                    "SELECT jua.curriculum_id, jc.focus_order, jc.focus_title
                     FROM {$wpdb->prefix}jph_jpc_user_assignments jua
                     JOIN {$wpdb->prefix}jph_jpc_curriculum jc ON jua.curriculum_id = jc.id
                     WHERE jua.user_id = %d AND jua.deleted_at IS NULL
                     ORDER BY jua.id DESC LIMIT 1",
                    $user_id
                ), ARRAY_A);
                
                if ($current_assignment) {
                    $current_focus_id = $current_assignment['curriculum_id'];
                    $current_focus_order = $current_assignment['focus_order'];
                    
                    // Determine what focus they should be at
                    $should_be_at = null;
                    if (!empty($completed_focuses)) {
                        // Find the highest completed focus
                        $highest_completed = max($completed_focuses);
                        
                        // Get the next focus after the highest completed
                        $next_focus = $wpdb->get_row($wpdb->prepare(
                            "SELECT id, focus_order FROM {$wpdb->prefix}jph_jpc_curriculum 
                             WHERE id > %d ORDER BY id ASC LIMIT 1",
                            $highest_completed
                        ), ARRAY_A);
                        
                        if ($next_focus) {
                            $should_be_at = $next_focus['focus_order'];
                        }
                    }
                    
                    // Check if assignment is correct
                    if ($should_be_at && $current_focus_order != $should_be_at) {
                        $analysis['incorrect_assignments']++;
                        $analysis['students_to_fix'][] = array(
                            'user_id' => $user_id,
                            'current_focus' => $current_focus_order,
                            'correct_focus' => $should_be_at,
                            'completed_focuses' => $completed_focuses
                        );
                    } else {
                        $analysis['correct_assignments']++;
                    }
                }
            }
            
            return rest_ensure_response($analysis);
            
        } catch (Exception $e) {
            return new WP_Error('analyze_progress_error', 'Error analyzing progress: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Fix JPC assignments based on analysis
     */
    public function rest_fix_jpc_assignments($request) {
        global $wpdb;
        
        try {
            $fixed_count = 0;
            
            // Get all students with JPC assignments
            $students = $wpdb->get_results(
                "SELECT DISTINCT jua.user_id, jua.curriculum_id, jua.step_id, jc.focus_order, jc.focus_title
                 FROM {$wpdb->prefix}jph_jpc_user_assignments jua
                 JOIN {$wpdb->prefix}jph_jpc_curriculum jc ON jua.curriculum_id = jc.id
                 WHERE jua.deleted_at IS NULL
                 ORDER BY jua.user_id, jua.curriculum_id",
                ARRAY_A
            );
            
            $processed_users = array();
            
            foreach ($students as $student) {
                $user_id = $student['user_id'];
                
                if (in_array($user_id, $processed_users)) {
                    continue;
                }
                
                $processed_users[] = $user_id;
                
                // Get student's progress across all focuses
                $all_progress = $wpdb->get_results($wpdb->prepare(
                    "SELECT curriculum_id, step_1, step_2, step_3, step_4, step_5, step_6, 
                            step_7, step_8, step_9, step_10, step_11, step_12
                     FROM {$wpdb->prefix}jph_jpc_user_progress 
                     WHERE user_id = %d",
                    $user_id
                ), ARRAY_A);
                
                // Find completed focuses
                $completed_focuses = array();
                foreach ($all_progress as $progress) {
                    $completed_keys = 0;
                    for ($i = 1; $i <= 12; $i++) {
                        if (!empty($progress['step_' . $i])) {
                            $completed_keys++;
                        }
                    }
                    
                    if ($completed_keys === 12) {
                        $completed_focuses[] = $progress['curriculum_id'];
                    }
                }
                
                // Determine what focus they should be at
                $should_be_at_id = null;
                if (!empty($completed_focuses)) {
                    // Find the highest completed focus
                    $highest_completed = max($completed_focuses);
                    
                    // Get the next focus after the highest completed
                    $next_focus = $wpdb->get_row($wpdb->prepare(
                        "SELECT id FROM {$wpdb->prefix}jph_jpc_curriculum 
                         WHERE id > %d ORDER BY id ASC LIMIT 1",
                        $highest_completed
                    ), ARRAY_A);
                    
                    if ($next_focus) {
                        $should_be_at_id = $next_focus['id'];
                    }
                }
                
                if ($should_be_at_id) {
                    // Get first step of the correct focus
                    $first_step = $wpdb->get_row($wpdb->prepare(
                        "SELECT step_id FROM {$wpdb->prefix}jph_jpc_steps 
                         WHERE curriculum_id = %d AND key_sig = 1",
                        $should_be_at_id
                    ), ARRAY_A);
                    
                    if ($first_step) {
                        // Update assignment
                        $result = $wpdb->update(
                            $wpdb->prefix . 'jph_jpc_user_assignments',
                            array(
                                'curriculum_id' => $should_be_at_id,
                                'step_id' => $first_step['step_id'],
                                'assigned_date' => current_time('mysql')
                            ),
                            array('user_id' => $user_id, 'deleted_at' => null),
                            array('%d', '%d', '%s'),
                            array('%d', '%s')
                        );
                        
                        if ($result !== false) {
                            $fixed_count++;
                        }
                    }
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'fixed_count' => $fixed_count,
                'message' => "Successfully fixed $fixed_count student assignments"
            ));
            
        } catch (Exception $e) {
            return new WP_Error('fix_assignments_error', 'Error fixing assignments: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Fix a specific student's assignment
     */
    public function rest_fix_specific_student($request) {
        global $wpdb;
        
        try {
            $user_id = intval($request->get_param('user_id'));
            $focus_order = $request->get_param('focus_order');
            $key_number = intval($request->get_param('key_number'));
            
            if (!$user_id || !$focus_order || !$key_number) {
                return new WP_Error('invalid_params', 'User ID, Focus Order, and Key Number are required', array('status' => 400));
            }
            
            if ($key_number < 1 || $key_number > 12) {
                return new WP_Error('invalid_key', 'Key number must be between 1 and 12', array('status' => 400));
            }
            
            // First, find the curriculum ID for the given focus order
            $curriculum = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}jph_jpc_curriculum 
                 WHERE focus_order = %s",
                $focus_order
            ), ARRAY_A);
            
            if (!$curriculum) {
                return new WP_Error('focus_not_found', 'No focus found with order ' . $focus_order, array('status' => 404));
            }
            
            $focus_id = $curriculum['id'];
            
            // Get the step_id for the specified focus and key
            $step = $wpdb->get_row($wpdb->prepare(
                "SELECT step_id FROM {$wpdb->prefix}jph_jpc_steps 
                 WHERE curriculum_id = %d AND key_sig = %d",
                $focus_id, $key_number
            ), ARRAY_A);
            
            if (!$step) {
                return new WP_Error('step_not_found', 'No step found for focus ' . $focus_order . ', key ' . $key_number, array('status' => 404));
            }
            
            // Update the student's assignment
            $result = $wpdb->update(
                $wpdb->prefix . 'jph_jpc_user_assignments',
                array(
                    'curriculum_id' => $focus_id,
                    'step_id' => $step['step_id'],
                    'assigned_date' => current_time('mysql')
                ),
                array('user_id' => $user_id, 'deleted_at' => null),
                array('%d', '%d', '%s'),
                array('%d', '%s')
            );
            
            if ($result === false) {
                return new WP_Error('update_failed', 'Failed to update student assignment', array('status' => 500));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => "Successfully moved user $user_id to focus $focus_order, key $key_number",
                'user_id' => $user_id,
                'focus_order' => $focus_order,
                'focus_id' => $focus_id,
                'key_number' => $key_number,
                'step_id' => $step['step_id']
            ));
            
        } catch (Exception $e) {
            return new WP_Error('fix_specific_student_error', 'Error fixing specific student: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Allow students to fix their own progress
     */
        public function rest_fix_my_progress($request) {
            global $wpdb;
            
            try {
                $user_id = get_current_user_id();
            $current_focus = $request->get_param('current_focus');
            $current_key = $request->get_param('current_key');
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to fix your progress', array('status' => 401));
            }
            
            // Get student's current assignment
            $current_assignment = $wpdb->get_row($wpdb->prepare(
                "SELECT jua.curriculum_id, jua.step_id, jc.focus_order, js.key_sig, js.key_sig_name
                 FROM {$wpdb->prefix}jph_jpc_user_assignments jua
                 JOIN {$wpdb->prefix}jph_jpc_curriculum jc ON jua.curriculum_id = jc.id
                 JOIN {$wpdb->prefix}jph_jpc_steps js ON jua.step_id = js.step_id
                 WHERE jua.user_id = %d AND jua.deleted_at IS NULL
                 ORDER BY jua.id DESC LIMIT 1",
                $user_id
            ), ARRAY_A);
            
            if (!$current_assignment) {
                return new WP_Error('no_assignment', 'No current assignment found', array('status' => 404));
            }
            
            $current_focus_id = $current_assignment['curriculum_id'];
            $current_focus_order = $current_assignment['focus_order'];
            $current_step_id = $current_assignment['step_id'];
            $current_key_sig = $current_assignment['key_sig'];
            $current_key_name = $current_assignment['key_sig_name'];
            
            // Get student's progress for the current focus
            $current_progress = $wpdb->get_row($wpdb->prepare(
                "SELECT step_1, step_2, step_3, step_4, step_5, step_6, 
                        step_7, step_8, step_9, step_10, step_11, step_12
                 FROM {$wpdb->prefix}jph_jpc_user_progress 
                 WHERE user_id = %d AND curriculum_id = %d",
                $user_id, $current_focus_id
            ), ARRAY_A);
            
            // Find the absolute lowest NULL step across ALL curriculum records
            $next_step_id = null;
            $lowest_null_step = null;
            $lowest_null_curriculum_id = null;
            
            // Get all progress records for this user
            $all_progress = $wpdb->get_results($wpdb->prepare(
                "SELECT curriculum_id, step_1, step_2, step_3, step_4, step_5, step_6, 
                        step_7, step_8, step_9, step_10, step_11, step_12 
                 FROM {$wpdb->prefix}jph_jpc_user_progress 
                 WHERE user_id = %d 
                 ORDER BY curriculum_id",
                $user_id
            ), ARRAY_A);
            
            if ($all_progress) {
                // Find the absolute lowest step_id that is NULL across all curriculum records
                $lowest_null_step_id = null;
                
                foreach ($all_progress as $progress) {
                    $curriculum_id = $progress['curriculum_id'];
                    
                    // Check each step in this curriculum
                    for ($i = 1; $i <= 12; $i++) {
                        if (empty($progress['step_' . $i])) {
                            // Calculate the step_id for this NULL step
                            $step_id = ($curriculum_id - 1) * 12 + $i;
                            
                            // Found a NULL step - check if it's the lowest step_id
                            if ($lowest_null_step_id === null || $step_id < $lowest_null_step_id) {
                                $lowest_null_step_id = $step_id;
                                $lowest_null_step = $i;
                                $lowest_null_curriculum_id = $curriculum_id;
                            }
                            break; // Move to next curriculum
                        }
                    }
                }
                
                if ($lowest_null_step_id) {
                    $next_step_id = $lowest_null_step_id;
                }
            }
            
            // Use the curriculum with the lowest NULL step
            if ($lowest_null_curriculum_id) {
                $should_be_at_id = $lowest_null_curriculum_id;
                // Get the focus order for this curriculum
                $focus_info = $wpdb->get_row($wpdb->prepare(
                    "SELECT focus_order FROM {$wpdb->prefix}jph_jpc_curriculum WHERE id = %d",
                    $lowest_null_curriculum_id
                ), ARRAY_A);
                $should_be_at_order = $focus_info ? $focus_info['focus_order'] : $current_focus_order;
            } else {
                // Fallback to current focus
                $should_be_at_id = $current_focus_id;
                $should_be_at_order = $current_focus_order;
            }
            
            // Check if assignment needs fixing
            $needs_fixing = false;
            $fix_reason = '';
            
            // If we're at the lowest NULL step, we need to clear future progress
            if ($lowest_null_step && $lowest_null_curriculum_id && 
                $current_focus_id == $lowest_null_curriculum_id && 
                $current_step_id == $next_step_id) {
                
                // We're at the correct step, but need to clear future progress
                $needs_fixing = true;
                $fix_reason = 'clear_future_progress';
            }
            
            // Check if they're at the wrong focus
            if ($should_be_at_id && $current_focus_id != $should_be_at_id) {
                $needs_fixing = true;
                $fix_reason = 'wrong focus';
            }
            // Check if they're at the wrong key within the same focus
            elseif ($next_step_id && $current_step_id != $next_step_id) {
                $needs_fixing = true;
                $fix_reason = 'wrong key';
            }
            
            if ($needs_fixing) {
                if ($fix_reason === 'clear_future_progress') {
                    // Clear future progress steps after the lowest NULL step
                    if ($lowest_null_step && $lowest_null_curriculum_id) {
                        $update_fields = array();
                        
                        // NULL out all steps after the lowest NULL step in current curriculum
                        for ($i = $lowest_null_step + 1; $i <= 12; $i++) {
                            $update_fields[] = "step_$i = NULL";
                        }
                        
                        if (!empty($update_fields)) {
                            $update_sql = "UPDATE {$wpdb->prefix}jph_jpc_user_progress SET " . implode(', ', $update_fields) . 
                                         " WHERE user_id = %d AND curriculum_id = %d";
                            $wpdb->query($wpdb->prepare($update_sql, $user_id, $lowest_null_curriculum_id));
                        }
                        
                        // Delete ALL curriculum records with curriculum_id > current curriculum
                        $wpdb->query($wpdb->prepare(
                            "DELETE FROM {$wpdb->prefix}jph_jpc_user_progress 
                             WHERE user_id = %d AND curriculum_id > %d",
                            $user_id, $lowest_null_curriculum_id
                        ));
                    }
                    
                    return rest_ensure_response(array(
                        'success' => true,
                        'fixed' => true,
                        'message' => "Future progress cleared successfully",
                        'old_focus' => $current_focus_order,
                        'old_key' => $current_key_name,
                        'new_focus' => $should_be_at_order,
                        'new_key' => $current_key_name,
                        'fix_reason' => $fix_reason
                    ));
                } else {
                    // Get the correct step information
                    $correct_step = $wpdb->get_row($wpdb->prepare(
                        "SELECT step_id, key_sig_name FROM {$wpdb->prefix}jph_jpc_steps 
                         WHERE step_id = %d",
                        $next_step_id
                    ), ARRAY_A);
                    
                    if ($correct_step) {
                        // Clear future progress steps after the lowest NULL step
                        if ($lowest_null_step && $lowest_null_curriculum_id && $fix_reason === 'wrong key') {
                            $update_fields = array();
                            $update_values = array();
                            
                            // NULL out all steps after the lowest NULL step
                            for ($i = $lowest_null_step + 1; $i <= 12; $i++) {
                                $update_fields[] = "step_$i = NULL";
                            }
                            
                            if (!empty($update_fields)) {
                                $update_sql = "UPDATE {$wpdb->prefix}jph_jpc_user_progress SET " . implode(', ', $update_fields) . 
                                             " WHERE user_id = %d AND curriculum_id = %d";
                                $wpdb->query($wpdb->prepare($update_sql, $user_id, $lowest_null_curriculum_id));
                            }
                        }
                        
                        // Update assignment
                        $result = $wpdb->update(
                            $wpdb->prefix . 'jph_jpc_user_assignments',
                            array(
                                'curriculum_id' => $should_be_at_id,
                                'step_id' => $correct_step['step_id'],
                                'assigned_date' => current_time('mysql')
                            ),
                            array('user_id' => $user_id, 'deleted_at' => null),
                            array('%d', '%d', '%s'),
                            array('%d', '%s')
                        );
                        
                        if ($result !== false) {
                            return rest_ensure_response(array(
                                'success' => true,
                                'fixed' => true,
                                'message' => "Progress fixed successfully",
                                'old_focus' => $current_focus_order,
                                'old_key' => $current_key_name,
                                'new_focus' => $should_be_at_order,
                                'new_key' => $correct_step['key_sig_name'],
                                'fix_reason' => $fix_reason
                            ));
                        }
                    }
                }
            }
            
                // No fix needed
                return rest_ensure_response(array(
                    'success' => true,
                    'fixed' => false,
                    'message' => "Your progress is already correct",
                    'current_focus' => $current_focus_order
                ));
            
        } catch (Exception $e) {
            return new WP_Error('fix_my_progress_error', 'Error fixing progress: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
}
