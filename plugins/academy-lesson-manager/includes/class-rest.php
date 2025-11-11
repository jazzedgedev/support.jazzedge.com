<?php
/**
 * REST API endpoints for ALM
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Rest_API {
    /** @var wpdb */
    private $wpdb;

    /** @var ALM_Database */
    private $database;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();

        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route('alm/v1', '/search-lessons', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_search_lessons'),
            'permission_callback' => '__return_true',
            'args' => array(
                'q' => array('type' => 'string', 'required' => false),
                'page' => array('type' => 'integer', 'required' => false, 'default' => 1),
                'per_page' => array('type' => 'integer', 'required' => false, 'default' => 20),
                'membership_level' => array('type' => 'integer', 'required' => false),
                'lesson_level' => array('type' => 'string', 'required' => false, 'enum' => array('', 'beginner', 'intermediate', 'advanced', 'pro'), 'default' => ''),
                'tag' => array('type' => 'string', 'required' => false),
                'lesson_style' => array('type' => 'string', 'required' => false),
                'collection_id' => array('type' => 'integer', 'required' => false),
                'has_resources' => array('type' => 'string', 'required' => false, 'enum' => array('', 'has', 'none'), 'default' => ''),
                'song_lesson' => array('type' => 'string', 'required' => false, 'enum' => array('', 'y', 'n'), 'default' => ''),
                'min_duration' => array('type' => 'integer', 'required' => false),
                'max_duration' => array('type' => 'integer', 'required' => false),
                'date_from' => array('type' => 'string', 'required' => false),
                'date_to' => array('type' => 'string', 'required' => false),
                'order_by' => array('type' => 'string', 'required' => false, 'enum' => array('relevance', 'date'), 'default' => 'relevance'),
            ),
        ));
        
        register_rest_route('alm/v1', '/search-lessons/ai-recommend', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_ai_recommendations'),
            'permission_callback' => array($this, 'check_ai_permission'),
            'args' => array(
                'q' => array('type' => 'string', 'required' => true),
                'results' => array('type' => 'array', 'required' => false, 'default' => array()),
            ),
        ));
        
        register_rest_route('alm/v1', '/ai-paths', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_ai_paths'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
        
        register_rest_route('alm/v1', '/ai-paths/(?P<id>\d+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_ai_path'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
        
        register_rest_route('alm/v1', '/user/lesson-level-preference', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_save_lesson_level_preference'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'lesson_level' => array('type' => 'string', 'required' => true, 'enum' => array('beginner', 'intermediate', 'advanced', 'pro')),
            ),
        ));
        
        register_rest_route('alm/v1', '/user/lesson-level-preference', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_lesson_level_preference'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
        
        register_rest_route('alm/v1', '/tags', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_tags'),
            'permission_callback' => '__return_true',
        ));
        
        // Essentials Library endpoints
        register_rest_route('alm/v1', '/library/select', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_library_select'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'lesson_id' => array('type' => 'integer', 'required' => true),
            ),
        ));
        
        register_rest_route('alm/v1', '/library/available', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_library_available'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
        
        register_rest_route('alm/v1', '/library/lessons', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_library_lessons'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
    }
    
    /**
     * Check if user has permission for AI recommendations
     */
    public function check_ai_permission() {
        // Allow logged-in users only
        return is_user_logged_in();
    }
    
    /**
     * Check if user has permission for user preferences
     */
    public function check_user_permission() {
        // Allow logged-in users
        return is_user_logged_in();
    }
    
    /**
     * Handle AI path recommendations
     */
    public function handle_ai_recommendations(WP_REST_Request $request) {
        $query = trim((string) $request->get_param('q'));
        $search_results = $request->get_param('results') ?: array();
        
        if (empty($query)) {
            return new WP_Error('missing_query', 'Search query is required', array('status' => 400));
        }
        
        // Get user's membership level
        $user_id = get_current_user_id();
        $user_membership_level = apply_filters('alm_get_current_user_membership_level', 3);
        
        // Get existing AI recommender instance or create new one
        if (isset($GLOBALS['alm_ai_recommender']) && $GLOBALS['alm_ai_recommender'] instanceof ALM_AI_Recommender) {
            $recommender = $GLOBALS['alm_ai_recommender'];
        } else {
            require_once ALM_PLUGIN_DIR . 'includes/class-ai-recommender.php';
            $recommender = new ALM_AI_Recommender();
        }
        $recommendations = $recommender->generate_path_recommendations($query, $search_results, $user_membership_level);
        
        if (is_wp_error($recommendations)) {
            return $recommendations;
        }
        
        // Get lesson permalinks for recommended lessons
        if (!empty($recommendations['recommended_path'])) {
            foreach ($recommendations['recommended_path'] as &$item) {
                $item['permalink'] = $this->get_lesson_permalink($item['lesson_id']);
            }
        }
        
        if (!empty($recommendations['alternative_lessons'])) {
            foreach ($recommendations['alternative_lessons'] as &$item) {
                $item['permalink'] = $this->get_lesson_permalink($item['lesson_id']);
            }
        }
        
        // Save path to database
        $user_id = get_current_user_id();
        $ai_paths_table = $this->database->get_table_name('ai_paths');
        
        $path_data = array(
            'user_id' => $user_id,
            'search_query' => $query,
            'summary' => isset($recommendations['summary']) ? $recommendations['summary'] : '',
            'recommended_path' => isset($recommendations['recommended_path']) ? json_encode($recommendations['recommended_path']) : '',
            'alternative_lessons' => isset($recommendations['alternative_lessons']) ? json_encode($recommendations['alternative_lessons']) : ''
        );
        
        $path_id = $this->wpdb->insert($ai_paths_table, $path_data);
        
        if ($path_id) {
            $recommendations['path_id'] = $this->wpdb->insert_id;
        }
        
        // Add debug info including the prompt and SQL queries (for debugging)
        $catalog = $recommender->get_last_catalog();
        $catalog_count = count($catalog);
        $lessons_count = $recommender->get_last_catalog_lessons_count();
        
        $recommendations['debug'] = array(
            'prompt_sent' => $recommender->get_last_prompt(),
            'system_message' => $recommender->get_system_message(),
            'catalog_count' => $catalog_count,
            'catalog_lessons_count' => $lessons_count,
            'sql_query' => $recommender->get_last_sql_query(),
            'sql_params' => $recommender->get_last_sql_params()
        );
        
        return rest_ensure_response($recommendations);
    }
    
    /**
     * Get lesson permalink by lesson ID
     */
    private function get_lesson_permalink($lesson_id) {
        $lessons_table = $this->database->get_table_name('lessons');
        $post_id = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT post_id FROM {$lessons_table} WHERE ID = %d",
            $lesson_id
        ));
        
        if ($post_id) {
            return get_permalink($post_id);
        }
        
        return '';
    }
    
    /**
     * Get AI paths for current user
     */
    public function handle_get_ai_paths(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('unauthorized', 'User must be logged in', array('status' => 401));
        }
        
        $ai_paths_table = $this->database->get_table_name('ai_paths');
        
        $paths = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT ID, search_query, path_name, summary, created_at 
            FROM {$ai_paths_table} 
            WHERE user_id = %d 
            ORDER BY created_at DESC 
            LIMIT 50",
            $user_id
        ), ARRAY_A);
        
        return rest_ensure_response($paths);
    }
    
    /**
     * Get single AI path by ID
     */
    public function handle_get_ai_path(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('unauthorized', 'User must be logged in', array('status' => 401));
        }
        
        $path_id = (int) $request->get_param('id');
        $ai_paths_table = $this->database->get_table_name('ai_paths');
        
        $path = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$ai_paths_table} 
            WHERE ID = %d AND user_id = %d",
            $path_id,
            $user_id
        ), ARRAY_A);
        
        if (!$path) {
            return new WP_Error('not_found', 'Path not found', array('status' => 404));
        }
        
        // Decode JSON fields
        if (!empty($path['recommended_path'])) {
            $path['recommended_path'] = json_decode($path['recommended_path'], true);
        }
        if (!empty($path['alternative_lessons'])) {
            $path['alternative_lessons'] = json_decode($path['alternative_lessons'], true);
        }
        
        return rest_ensure_response($path);
    }
    
    /**
     * Save lesson level preference for current user
     */
    public function handle_save_lesson_level_preference(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('unauthorized', 'User must be logged in', array('status' => 401));
        }
        
        $lesson_level = (string) $request->get_param('lesson_level');
        if (!in_array($lesson_level, array('beginner', 'intermediate', 'advanced', 'pro'), true)) {
            return new WP_Error('invalid_level', 'Invalid lesson level', array('status' => 400));
        }
        
        update_user_meta($user_id, 'alm_lesson_level_preference', $lesson_level);
        
        return rest_ensure_response(array(
            'success' => true,
            'lesson_level' => $lesson_level
        ));
    }
    
    /**
     * Get lesson level preference for current user
     */
    public function handle_get_lesson_level_preference(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return rest_ensure_response(array('lesson_level' => 'intermediate'));
        }
        
        $lesson_level = get_user_meta($user_id, 'alm_lesson_level_preference', true);
        if (empty($lesson_level) || !in_array($lesson_level, array('beginner', 'intermediate', 'advanced', 'pro'), true)) {
            $lesson_level = 'intermediate'; // Default
        }
        
        return rest_ensure_response(array('lesson_level' => $lesson_level));
    }
    
    /**
     * Get all tags for frontend dropdown
     */
    public function handle_get_tags(WP_REST_Request $request) {
        $tags_table = $this->database->get_table_name('tags');
        
        $tags = $this->wpdb->get_results("
            SELECT tag_name, tag_slug
            FROM {$tags_table}
            ORDER BY tag_name ASC
        ");
        
        $tags_array = array();
        foreach ($tags as $tag) {
            $tags_array[] = array(
                'name' => $tag->tag_name,
                'slug' => $tag->tag_slug
            );
        }
        
        return rest_ensure_response($tags_array);
    }
    
    /**
     * Check if user is Essentials member
     * 
     * @param int $user_id User ID
     * @return bool True if Essentials member
     */
    private function is_essentials_member($user_id) {
        // Check if user has Essentials membership via Keap/Infusionsoft
        // Essentials members should have membership level 1
        // They should NOT have Studio (2) or Premier (3) access
        
        // First check if they have Studio or Premier (if so, they're not Essentials)
        $studio_access = false;
        $premier_access = false;
        
        if (function_exists('memb_hasAnyTags')) {
            $studio_access = memb_hasAnyTags([9954,10136,9807,9827,9819,9956,10136]);
            $premier_access = memb_hasAnyTags([9821,9813,10142]);
        }
        
        // If they have Studio or Premier, they're not Essentials
        if ($studio_access || $premier_access) {
            return false;
        }
        
        // Check for Essentials membership SKU
        $essentials_skus = array('JA_YEAR_ESSENTIALS', 'ACADEMY_ESSENTIALS');
        foreach ($essentials_skus as $sku) {
            if (function_exists('memb_hasMembership') && memb_hasMembership($sku) === true) {
                return true;
            }
        }
        
        // Fallback: Check if they have active membership but not Studio/Premier
        // This assumes Essentials is the base paid membership
        if (function_exists('je_return_active_member') && je_return_active_member() == 'true') {
            // If they're an active member but don't have Studio/Premier, assume Essentials
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle library select endpoint
     */
    public function handle_library_select(WP_REST_Request $request) {
        if (!class_exists('ALM_Essentials_Library')) {
            return new WP_Error('library_unavailable', 'Library system not available', array('status' => 500));
        }
        
        $user_id = get_current_user_id();
        $lesson_id = $request->get_param('lesson_id');
        
        // Check if user is Essentials member
        if (!$this->is_essentials_member($user_id)) {
            return new WP_Error('unauthorized', 'This feature is available for Essentials members only', array('status' => 403));
        }
        
        $library = new ALM_Essentials_Library();
        $result = $library->add_lesson_to_library($user_id, $lesson_id);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $available = $library->get_available_selections($user_id);
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Lesson added to your library!',
            'available_count' => $available
        ));
    }
    
    /**
     * Handle library available endpoint
     */
    public function handle_library_available(WP_REST_Request $request) {
        if (!class_exists('ALM_Essentials_Library')) {
            return new WP_Error('library_unavailable', 'Library system not available', array('status' => 500));
        }
        
        $user_id = get_current_user_id();
        $library = new ALM_Essentials_Library();
        $available = $library->get_available_selections($user_id);
        $next_grant = $library->get_next_grant_date($user_id);
        
        return rest_ensure_response(array(
            'available_count' => $available,
            'next_grant_date' => $next_grant
        ));
    }
    
    /**
     * Handle library lessons endpoint
     */
    public function handle_library_lessons(WP_REST_Request $request) {
        if (!class_exists('ALM_Essentials_Library')) {
            return new WP_Error('library_unavailable', 'Library system not available', array('status' => 500));
        }
        
        $user_id = get_current_user_id();
        $library = new ALM_Essentials_Library();
        $lessons = $library->get_user_library($user_id);
        
        $lessons_array = array();
        foreach ($lessons as $lesson) {
            $lesson_url = '';
            if ($lesson->post_id) {
                $lesson_url = get_permalink($lesson->post_id);
            } elseif ($lesson->slug) {
                $lesson_url = home_url('/lesson/' . $lesson->slug . '/');
            }
            
            $lessons_array[] = array(
                'id' => $lesson->ID,
                'title' => stripslashes($lesson->lesson_title),
                'description' => stripslashes($lesson->lesson_description),
                'url' => $lesson_url,
                'selected_at' => $lesson->selected_at,
                'duration' => $lesson->duration
            );
        }
        
        return rest_ensure_response($lessons_array);
    }
    

    /**
     * Search lessons with filters and pagination
     */
    public function handle_search_lessons(WP_REST_Request $request) {
        $lessons_table = $this->database->get_table_name('lessons');

        $q = trim((string) $request->get_param('q'));
        // AI: extract filters from natural language
        $ai_filters = ALM_AI::extract_filters($q);
        $page = max(1, intval($request->get_param('page')));
        $per_page = min(100, max(1, intval($request->get_param('per_page')))); // Allow up to 100 per page
        $offset = ($page - 1) * $per_page;

        $membership_param_raw = isset($ai_filters['membership_level']) ? $ai_filters['membership_level'] : $request->get_param('membership_level');
        $membership_param = $membership_param_raw !== null ? intval($membership_param_raw) : null;
        // Allow theme/plugins to define current user's accessible level
        $current_user_level = apply_filters('alm_get_current_user_membership_level', 0);
        $enforce_membership = apply_filters('alm_enforce_membership_in_search', false, $request);
        // Check if membership_param was explicitly provided (including 0 for Free level)
        $membership_param_provided = $membership_param_raw !== null;
        // Only enforce if explicitly enabled or if a membership param is provided
        $apply_membership_filter = $enforce_membership || $membership_param_provided;
        $max_level = $apply_membership_filter
            ? ($membership_param_provided && !isset($ai_filters['membership_level']) ? $membership_param : ($membership_param !== null ? min($membership_param, intval($current_user_level)) : intval($current_user_level)))
            : 0; // 0 disables the filter

        $collection_id = isset($ai_filters['collection_id']) ? intval($ai_filters['collection_id']) : intval($request->get_param('collection_id'));
        $has_resources = isset($ai_filters['has_resources']) ? (string) $ai_filters['has_resources'] : (string) $request->get_param('has_resources');
        $song_lesson = isset($ai_filters['song_lesson']) ? (string) $ai_filters['song_lesson'] : (string) $request->get_param('song_lesson');
        $min_duration = isset($ai_filters['min_duration']) ? intval($ai_filters['min_duration']) : intval($request->get_param('min_duration'));
        $max_duration = isset($ai_filters['max_duration']) ? intval($ai_filters['max_duration']) : intval($request->get_param('max_duration'));
        $date_from = isset($ai_filters['date_from']) ? (string) $ai_filters['date_from'] : (string) $request->get_param('date_from');
        $date_to = isset($ai_filters['date_to']) ? (string) $ai_filters['date_to'] : (string) $request->get_param('date_to');
        $lesson_level = isset($ai_filters['lesson_level']) ? (string) $ai_filters['lesson_level'] : (string) $request->get_param('lesson_level');
        $tag = trim((string) $request->get_param('tag'));
        $lesson_style = trim((string) $request->get_param('lesson_style'));
        $order_by = (string) $request->get_param('order_by');

        $where = array('1=1');
        $params = array();
        $select_relevance = '';
        $order_clause = '';

        // For now, skip FULLTEXT and always use LIKE for search queries (more reliable)
        // FULLTEXT requires indexes and may not work in all environments
        $use_fulltext = false; // Disabled - always use LIKE
        
        // If there's a search query, use LIKE search
        if (!empty($q)) {
            // Extract meaningful keywords (remove common stop words and short words)
            $stop_words = array('i', 'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'has', 'he', 'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the', 'to', 'was', 'will', 'with', 'have', 'my', 'me', 'we', 'they', 'them', 'their', 'this', 'these', 'what', 'which', 'who');
            $tokens = preg_split('/\s+/', strtolower($q));
            $keywords = array();
            foreach ($tokens as $token) {
                $token = trim($token);
                if (empty($token)) continue;
                // Keep words that are 2+ characters and not stop words
                // Changed from 3 to 2 to catch short queries like "TCI"
                if (mb_strlen($token) >= 2 && !in_array($token, $stop_words, true)) {
                    $keywords[] = $token;
                }
            }
            
            // If no keywords after filtering, use original query (might be short like "TCI" or single character)
            if (empty($keywords)) {
                $keywords = array(strtolower(trim($q)));
            }
            
            // Build WHERE condition: ANY keyword can match (OR logic)
            // Use LOWER() for case-insensitive matching since MySQL LIKE is case-sensitive by default
            $or_conditions = array();
            foreach ($keywords as $keyword) {
                $escaped_keyword = '%' . $this->wpdb->esc_like($keyword) . '%';
                $or_conditions[] = "(LOWER(l.lesson_title) LIKE LOWER(%s) OR LOWER(l.lesson_description) LIKE LOWER(%s) OR LOWER(c.collection_title) LIKE LOWER(%s))";
                $params[] = $escaped_keyword;
                $params[] = $escaped_keyword;
                $params[] = $escaped_keyword;
            }
            
            // Use OR logic - find lessons with ANY keyword match
            $where[] = '(' . implode(' OR ', $or_conditions) . ')';
            
            // Build relevance scoring - heavily prioritize title matches
            $select_relevance = ', (';
            $relevance_parts = array();
            foreach ($keywords as $keyword) {
                $escaped_keyword = '%' . $this->wpdb->esc_like($keyword) . '%';
                // Very high weight for title matches (100), description (3), collection (1)
                // Use LOWER() for case-insensitive matching
                $relevance_parts[] = "(CASE WHEN LOWER(l.lesson_title) LIKE LOWER(%s) THEN 100 ELSE 0 END)";
                $params[] = $escaped_keyword;
                $relevance_parts[] = "(CASE WHEN LOWER(l.lesson_description) LIKE LOWER(%s) THEN 3 ELSE 0 END)";
                $params[] = $escaped_keyword;
                $relevance_parts[] = "(CASE WHEN LOWER(c.collection_title) LIKE LOWER(%s) THEN 1 ELSE 0 END)";
                $params[] = $escaped_keyword;
            }
            $select_relevance .= implode(' + ', $relevance_parts) . ') AS relevance';
            
            // Order by relevance if we have keywords
            $order_clause = "ORDER BY relevance DESC, l.post_date DESC";
        }

        if ($apply_membership_filter && $membership_param_provided) {
            // If membership_param was provided directly (from frontend filter), use exact match
            // Otherwise, use <= logic for accessibility (user can see content at their level or below)
            if ($membership_param_provided && !isset($ai_filters['membership_level'])) {
                // Direct frontend filter - exact match (including 0 for Free)
                $where[] = "l.membership_level = %d";
                $params[] = $membership_param;
            } else {
                // AI filter or enforcement - use <= logic for accessibility
                $where[] = "l.membership_level <= %d";
                $params[] = $max_level;
            }
        }
        if ($collection_id > 0) {
            $where[] = "l.collection_id = %d";
            $params[] = $collection_id;
        }
        if ($song_lesson === 'y' || $song_lesson === 'n') {
            $where[] = "l.song_lesson = %s";
            $params[] = $song_lesson;
        }
        if ($has_resources === 'has') {
            $where[] = "(l.resources IS NOT NULL AND l.resources != '' AND l.resources != 'N;')";
        } elseif ($has_resources === 'none') {
            $where[] = "(l.resources IS NULL OR l.resources = '' OR l.resources = 'N;')";
        }
        if ($min_duration > 0) {
            $where[] = "l.duration >= %d";
            $params[] = $min_duration;
        }
        if ($max_duration > 0) {
            $where[] = "l.duration <= %d";
            $params[] = $max_duration;
        }
        if (!empty($date_from)) {
            $where[] = "l.post_date >= %s";
            $params[] = $date_from;
        }
        if (!empty($date_to)) {
            $where[] = "l.post_date <= %s";
            $params[] = $date_to;
        }
        if (!empty($lesson_level) && in_array($lesson_level, array('beginner', 'intermediate', 'advanced', 'pro'), true)) {
            $where[] = "l.lesson_level = %s";
            $params[] = $lesson_level;
        }
        
        // Filter by tag - match exact tag (handles tags at start, middle, or end of comma-separated list)
        if (!empty($tag)) {
            $tag_trimmed = trim($tag);
            // Match: tag at start, tag in middle (preceded by ", "), or tag at end (followed by nothing or end of string)
            $where[] = "(l.lesson_tags = %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s)";
            $params[] = $tag_trimmed;
            $params[] = $tag_trimmed . ',%';
            $params[] = '%, ' . $tag_trimmed . ',%';
            $params[] = '%, ' . $tag_trimmed;
        }
        
        // Filter by lesson style - match exact style (handles styles at start, middle, or end of comma-separated list)
        if (!empty($lesson_style)) {
            $style_trimmed = trim($lesson_style);
            // Match: style at start, style in middle (preceded by ", "), or style at end (followed by nothing or end of string)
            $where[] = "(l.lesson_style = %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s)";
            $params[] = $style_trimmed;
            $params[] = $style_trimmed . ',%';
            $params[] = '%, ' . $style_trimmed . ',%';
            $params[] = '%, ' . $style_trimmed;
        }

        // Set order clause if not already set by keyword search above
        if (empty($order_clause)) {
            if ($order_by === 'date') {
                $order_clause = "ORDER BY l.post_date DESC";
            } else {
                // Default order if no keywords and no specific order_by
                $order_clause = "ORDER BY l.post_date DESC";
            }
        }

        $where_sql = 'WHERE ' . implode(' AND ', $where);

        // Count - always include JOIN for collection_title searches
        $collections_table = $this->database->get_table_name('collections');
        $count_from = "FROM {$lessons_table} l LEFT JOIN {$collections_table} c ON c.ID = l.collection_id";
        $count_sql = $this->wpdb->prepare("SELECT COUNT(*) {$count_from} {$where_sql}", $params);
        $total = intval($this->wpdb->get_var($count_sql));

        // Query with collection title join (always include JOIN since we need collection_title)
        $sql = "SELECT l.ID, l.post_id, l.collection_id, l.lesson_title, l.lesson_description, l.post_date, l.duration, l.song_lesson, l.membership_level, l.lesson_level, l.lesson_tags, l.lesson_style, l.slug, l.vtt, l.resources, l.sample_video_url, c.collection_title{$select_relevance}
                FROM {$lessons_table} l
                LEFT JOIN {$collections_table} c ON c.ID = l.collection_id
                {$where_sql}
                {$order_clause}
                LIMIT %d OFFSET %d";

        $query_params = $params;
        $query_params[] = $per_page;
        $query_params[] = $offset;

        $prepared = $this->wpdb->prepare($sql, $query_params);
        
        // Build readable SQL for debugging by manually substituting parameters
        $debug_sql = $sql;
        $param_index = 0;
        // Replace placeholders in order: %s, %d, %f
        $debug_sql = preg_replace_callback('/(%[sdbf])/', function($matches) use (&$query_params, &$param_index) {
            if ($param_index >= count($query_params)) {
                return $matches[0]; // No more params
            }
            $param = $query_params[$param_index++];
            if (is_string($param)) {
                return "'" . addslashes($param) . "'";
            } elseif (is_int($param) || is_float($param)) {
                return (string) $param;
            }
            return $matches[0];
        }, $debug_sql);
        
        // Execute main query - get results as objects
        $rows = $this->wpdb->get_results($prepared, OBJECT);

        // Fuzzy fallback when no results
        if (!empty($q) && (empty($rows) || $total === 0)) {
            $tokens = preg_split('/\s+/', $q);
            $like_parts = array();
            $score_parts = array();
            $f_params = array();

            foreach ($tokens as $tok) {
                $tok = trim($tok);
                if ($tok === '') continue;
                // Build LIKEs for tokens of 2+ characters (changed from 3 to catch "TCI")
                if (mb_strlen($tok) >= 2) {
                    $like = '%' . $this->wpdb->esc_like($tok) . '%';
                    $like_parts[] = "l.lesson_title LIKE %s"; $f_params[] = $like;
                    $like_parts[] = "l.lesson_description LIKE %s"; $f_params[] = $like;
                    $like_parts[] = "c.collection_title LIKE %s"; $f_params[] = $like; // Fixed: use collection_title, not chapter_title
                    $like_parts[] = "c2.chapter_title LIKE %s"; $f_params[] = $like; // Use c2 for chapters table
                    $like_parts[] = "t.content LIKE %s"; $f_params[] = $like;
                    $score_parts[] = "(l.lesson_title LIKE %s)"; $f_params[] = $like;
                    $score_parts[] = "(c.collection_title LIKE %s)"; $f_params[] = $like; // Fixed: use collection_title
                    $score_parts[] = "(c2.chapter_title LIKE %s)"; $f_params[] = $like; // Use c2 for chapters table
                }
                // SOUNDEX for longer tokens
                if (mb_strlen($tok) >= 4) {
                    $like_parts[] = "SOUNDEX(l.lesson_title) = SOUNDEX(%s)"; $f_params[] = $tok;
                }
            }

            // If no token conditions built, use SOUNDEX of whole query
            if (empty($like_parts)) {
                $like_parts[] = "SOUNDEX(l.lesson_title) = SOUNDEX(%s)"; $f_params[] = $q;
            }

            $f_where = array('1=1');
            $f_params_full = array();
            // Apply the same membership/filters if enabled
            if ($apply_membership_filter && $max_level > 0) { $f_where[] = "l.membership_level <= %d"; $f_params_full[] = $max_level; }
            if ($collection_id > 0) { $f_where[] = "l.collection_id = %d"; $f_params_full[] = $collection_id; }
            if ($song_lesson === 'y' || $song_lesson === 'n') { $f_where[] = "l.song_lesson = %s"; $f_params_full[] = $song_lesson; }
            if (!empty($lesson_level) && in_array($lesson_level, array('beginner', 'intermediate', 'advanced', 'pro'), true)) {
                $f_where[] = "l.lesson_level = %s";
                $f_params_full[] = $lesson_level;
            }
            if ($has_resources === 'has') { $f_where[] = "(l.resources IS NOT NULL AND l.resources != '' AND l.resources != 'N;')"; }
            elseif ($has_resources === 'none') { $f_where[] = "(l.resources IS NULL OR l.resources = '' OR l.resources = 'N;')"; }
            if ($min_duration > 0) { $f_where[] = "l.duration >= %d"; $f_params_full[] = $min_duration; }
            if ($max_duration > 0) { $f_where[] = "l.duration <= %d"; $f_params_full[] = $max_duration; }
            if (!empty($date_from)) { $f_where[] = "l.post_date >= %s"; $f_params_full[] = $date_from; }
            if (!empty($date_to)) { $f_where[] = "l.post_date <= %s"; $f_params_full[] = $date_to; }
            if (!empty($tag)) {
                $tag_trimmed = trim($tag);
                $f_where[] = "(l.lesson_tags = %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s)";
                $f_params_full[] = $tag_trimmed;
                $f_params_full[] = $tag_trimmed . ',%';
                $f_params_full[] = '%, ' . $tag_trimmed . ',%';
                $f_params_full[] = '%, ' . $tag_trimmed;
            }
            if (!empty($lesson_style)) {
                $style_trimmed = trim($lesson_style);
                $f_where[] = "(l.lesson_style = %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s)";
                $f_params_full[] = $style_trimmed;
                $f_params_full[] = $style_trimmed . ',%';
                $f_params_full[] = '%, ' . $style_trimmed . ',%';
                $f_params_full[] = '%, ' . $style_trimmed;
            }

            $like_sql = '(' . implode(' OR ', $like_parts) . ')';
            $f_where[] = $like_sql;
            
            // Build WHERE clause with placeholders - we need to track which placeholders are for filters vs LIKE
            $where_sql2 = 'WHERE ' . implode(' AND ', $f_where);

            $score_sql = (empty($score_parts) ? '0' : implode(' + ', $score_parts));

            // IMPORTANT: The SQL structure has score_sql first (uses $f_params), then WHERE clause (uses $f_params_full then $f_params)
            // But wpdb->prepare() processes placeholders left-to-right, so we need to bind in the correct order:
            // 1. First, $f_params for the score calculation (in score_sql)
            // 2. Then, $f_params_full for the WHERE filters
            // 3. Then, $f_params again for the WHERE LIKE conditions
            // 4. Finally, $per_page and $offset
            
            $sql2 = "SELECT l.ID, l.post_id, l.collection_id, l.lesson_title, l.lesson_description, l.post_date, l.duration, l.song_lesson, l.membership_level, l.lesson_level, l.lesson_tags, l.lesson_style, l.slug, l.vtt, l.resources, l.sample_video_url,
                            c.collection_title, (" . $score_sql . ") AS score
                     FROM {$lessons_table} l
                     LEFT JOIN " . $this->database->get_table_name('chapters') . " c2 ON c2.lesson_id = l.ID
                     LEFT JOIN " . $this->database->get_table_name('transcripts') . " t ON t.lesson_id = l.ID
                     LEFT JOIN {$collections_table} c ON c.ID = l.collection_id
                     {$where_sql2}
                     GROUP BY l.ID
                     ORDER BY score DESC, l.post_date DESC
                     LIMIT %d OFFSET %d";

            // CRITICAL: $f_params is built with LIKE params first (5 per token), then score params (3 per token)
            // SQL needs: score params first (last 3 of $f_params), then $f_params_full, then LIKE params (first 5 of $f_params)
            // For one token "tci": $f_params has 8 values [LIKE_title, LIKE_desc, LIKE_coll, LIKE_chap, LIKE_trans, score_title, score_coll, score_chap]
            // score_sql needs: [score_title, score_coll, score_chap] = last 3
            // WHERE LIKE needs: [LIKE_title, LIKE_desc, LIKE_coll, LIKE_chap, LIKE_trans] = first 5
            
            $like_param_count = count($like_parts); // 5 per token
            $score_param_count = count($score_parts); // 3 per token
            $score_params = array_slice($f_params, $like_param_count); // Last 3 params (score)
            $where_like_params = array_slice($f_params, 0, $like_param_count); // First 5 params (LIKE)
            
            // Bind in order: score params, then filters, then WHERE LIKE params, then LIMIT
            $f_bind = array_merge($score_params, $f_params_full, $where_like_params);
            $f_bind[] = $per_page; $f_bind[] = $offset;
            $prepared2 = $this->wpdb->prepare($sql2, $f_bind);
            
            // Build readable SQL for fuzzy fallback
            // The WHERE clause structure: $f_params_full (filters) come first, then $f_params (LIKE conditions) in $like_sql
            // But $like_sql is built from $like_parts which already have placeholders, so we need to substitute them separately
            
            // First, substitute parameters in score_sql (uses $f_params)
            $score_sql_debug = $score_sql;
            $score_param_idx = 0;
            foreach ($score_parts as $part) {
                if (strpos($part, '%s') !== false && $score_param_idx < count($f_params)) {
                    $param_val = $f_params[$score_param_idx++];
                    $score_sql_debug = str_replace($part, str_replace('%s', "'" . addslashes($param_val) . "'", $part), $score_sql_debug);
                }
            }
            
            // Build WHERE clause manually by substituting each part
            // The structure is: $f_where contains filter conditions (with placeholders from $f_params_full) and $like_sql (with placeholders from $f_params)
            // We need to identify which part is $like_sql and substitute it separately
            $where_debug_parts = array();
            $where_param_idx = 0;
            
            foreach ($f_where as $where_part) {
                // Check if this is the $like_sql part (contains multiple OR conditions)
                if (strpos($where_part, ' OR ') !== false || (substr_count($where_part, 'LIKE') > 1)) {
                    // This is the $like_sql part - substitute from $f_params
                    $like_debug = $where_part;
                    $like_param_idx = 0;
                    $like_debug = preg_replace_callback('/(%[sdbf])/', function($matches) use (&$f_params, &$like_param_idx) {
                        if ($like_param_idx >= count($f_params)) {
                            return $matches[0];
                        }
                        $param = $f_params[$like_param_idx++];
                        if (is_string($param)) {
                            return "'" . addslashes($param) . "'";
                        } elseif (is_int($param) || is_float($param)) {
                            return (string) $param;
                        }
                        return $matches[0];
                    }, $like_debug);
                    $where_debug_parts[] = $like_debug;
                } elseif (strpos($where_part, '%s') !== false || strpos($where_part, '%d') !== false) {
                    // This is a filter condition with placeholder - substitute from $f_params_full
                    if ($where_param_idx < count($f_params_full)) {
                        $param = $f_params_full[$where_param_idx++];
                        if (strpos($where_part, '%s') !== false) {
                            $where_debug_parts[] = str_replace('%s', "'" . addslashes($param) . "'", $where_part);
                        } elseif (strpos($where_part, '%d') !== false) {
                            $where_debug_parts[] = str_replace('%d', (string) $param, $where_part);
                        } else {
                            $where_debug_parts[] = $where_part;
                        }
                    } else {
                        // Shouldn't happen, but keep as is
                        $where_debug_parts[] = $where_part;
                    }
                } else {
                    // No placeholders, keep as is (e.g., "1=1")
                    $where_debug_parts[] = $where_part;
                }
            }
            
            $where_debug = 'WHERE ' . implode(' AND ', $where_debug_parts);
            
            // Build final debug SQL
            $debug_sql = "SELECT l.ID, l.post_id, l.collection_id, l.lesson_title, l.lesson_description, l.post_date, l.duration, l.song_lesson, l.membership_level, l.lesson_level, l.lesson_tags, l.slug, l.vtt, l.resources, c.collection_title, (" . $score_sql_debug . ") AS score
                     FROM {$lessons_table} l
                     LEFT JOIN " . $this->database->get_table_name('chapters') . " c2 ON c2.lesson_id = l.ID
                     LEFT JOIN " . $this->database->get_table_name('transcripts') . " t ON t.lesson_id = l.ID
                     LEFT JOIN {$collections_table} c ON c.ID = l.collection_id
                     {$where_debug}
                     GROUP BY l.ID
                     ORDER BY score DESC, l.post_date DESC
                     LIMIT {$per_page} OFFSET {$offset}";
            
            // Execute query - get results as objects
            // First, let's verify the prepared query has the right parameters
            error_log('ALM Search Fuzzy Fallback: f_params count: ' . count($f_params));
            error_log('ALM Search Fuzzy Fallback: f_params_full count: ' . count($f_params_full));
            error_log('ALM Search Fuzzy Fallback: f_bind count: ' . count($f_bind));
            error_log('ALM Search Fuzzy Fallback: f_bind: ' . print_r($f_bind, true));
            
            // Execute the query
            $rows = $this->wpdb->get_results($prepared2, OBJECT);
            
            // Debug: Check what was actually executed
            $actual_query = $this->wpdb->last_query;
            error_log('ALM Search Fuzzy Fallback: Actual executed query: ' . $actual_query);
            error_log('ALM Search Fuzzy Fallback: Rows returned: ' . (is_array($rows) ? count($rows) : 'not an array'));
            
            // If query works in phpMyAdmin but returns no rows here, try executing it directly
            if (empty($rows) && !$this->wpdb->last_error) {
                error_log('ALM Search Fuzzy Fallback: Query executed but returned no rows. Trying direct execution...');
                // Try executing the query directly without prepare() to see if that works
                $direct_query = str_replace(array('%s', '%d'), array("'%s'", '%d'), $sql2);
                foreach ($f_bind as $i => $param) {
                    if (is_string($param)) {
                        $direct_query = preg_replace('/%s/', "'" . addslashes($param) . "'", $direct_query, 1);
                    } elseif (is_int($param) || is_float($param)) {
                        $direct_query = preg_replace('/%d/', (string) $param, $direct_query, 1);
                    }
                }
                error_log('ALM Search Fuzzy Fallback: Direct query: ' . $direct_query);
                $direct_rows = $this->wpdb->get_results($direct_query, OBJECT);
                error_log('ALM Search Fuzzy Fallback: Direct query returned: ' . (is_array($direct_rows) ? count($direct_rows) : 'not an array'));
                if (!empty($direct_rows)) {
                    // If direct query works, use those results
                    $rows = $direct_rows;
                    error_log('ALM Search Fuzzy Fallback: Using direct query results');
                }
            }
            
            // Debug: Check if query failed
            if ($this->wpdb->last_error) {
                error_log('ALM Search Fuzzy Fallback Error: ' . $this->wpdb->last_error);
            }
            
            // Recalculate total for fuzzy fallback results
            // We need to count the results from the fuzzy query to get accurate pagination
            if (!empty($rows) && is_array($rows)) {
                // Count total matching rows for the fuzzy query (without LIMIT)
                // WHERE clause uses $f_params_full then $f_params (no score_sql in COUNT)
                $count_sql2 = "SELECT COUNT(DISTINCT l.ID) {$count_from} {$where_sql2}";
                $count_params2 = array_merge($f_params_full, $f_params);
                $total = intval($this->wpdb->get_var($this->wpdb->prepare($count_sql2, $count_params2)));
                error_log('ALM Search Fuzzy Fallback: Total count: ' . $total);
            } else {
                // If still no results, keep total at 0
                $total = 0;
                // Debug: Log when no rows returned
                error_log('ALM Search Fuzzy Fallback: No rows returned after query execution');
                error_log('ALM Search Fuzzy Fallback: Rows type: ' . gettype($rows));
                error_log('ALM Search Fuzzy Fallback: Rows value: ' . print_r($rows, true));
                error_log('ALM Search Fuzzy Fallback: Last error: ' . ($this->wpdb->last_error ?: 'none'));
                error_log('ALM Search Fuzzy Fallback: Last query: ' . $this->wpdb->last_query);
            }
        }

        // Get membership level names
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-settings.php';
        
        // Shape response
        $items = array();
        
        // Debug: Log if rows is empty or not an array
        if (empty($rows)) {
            error_log('ALM Search: $rows is empty. Total: ' . $total);
            error_log('ALM Search: Query was: ' . ($this->wpdb->last_query ?? 'N/A'));
            error_log('ALM Search: Last error: ' . ($this->wpdb->last_error ?? 'N/A'));
        } elseif (!is_array($rows)) {
            error_log('ALM Search: $rows is not an array. Type: ' . gettype($rows));
            error_log('ALM Search: Rows value: ' . print_r($rows, true));
        } else {
            error_log('ALM Search: Found ' . count($rows) . ' rows');
        }
        
        foreach ($rows as $r) {
            $membership_level = intval($r->membership_level);
            $membership_level_name = ALM_Admin_Settings::get_membership_level_name($membership_level);
            
            $items[] = array(
                'id' => intval($r->ID),
                'post_id' => intval($r->post_id),
                'collection_id' => intval($r->collection_id),
                'collection_title' => !empty($r->collection_title) ? stripslashes($r->collection_title) : '',
                'title' => stripslashes($r->lesson_title),
                'description' => stripslashes($r->lesson_description),
                'date' => $r->post_date,
                'duration' => intval($r->duration),
                'song' => $r->song_lesson === 'y',
                'membership_level' => $membership_level,
                'membership_level_name' => $membership_level_name,
                'lesson_level' => !empty($r->lesson_level) ? $r->lesson_level : null,
                'lesson_tags' => !empty($r->lesson_tags) ? stripslashes($r->lesson_tags) : '',
                'lesson_style' => !empty($r->lesson_style) ? stripslashes($r->lesson_style) : '',
                'slug' => $r->slug,
                'permalink' => ($r->post_id ? get_permalink($r->post_id) : ''),
                'sample_video_url' => !empty($r->sample_video_url) ? $r->sample_video_url : '',
                'relevance' => isset($r->relevance) ? floatval($r->relevance) : null,
                'has_resources' => (!empty($r->resources) && $r->resources !== 'N;')
            );
        }

        // AI re-ranking hook (no-op by default)
        $items = ALM_AI::rerank_items($items, $q);

        // Always add debug SQL (set it if not already set from fuzzy fallback)
        if (!isset($debug_sql)) {
            $debug_sql = 'No SQL query executed (no search query or error occurred)';
        }
        
        $response = array(
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $per_page > 0 ? (int) ceil($total / $per_page) : 1,
            'debug' => array(
                'sql' => $debug_sql,
                'query' => $q,
                'lesson_level' => $lesson_level,
                'total_found' => $total,
                'items_returned' => count($items),
                'params_count' => count($query_params ?? $params ?? array()),
                'where_clause' => $where_sql ?? 'No WHERE clause'
            )
        );

        return new WP_REST_Response($response, 200);
    }
}

// Bootstrap
new ALM_Rest_API();


