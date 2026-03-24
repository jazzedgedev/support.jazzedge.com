<?php
/**
 * Plugin Name: Academy Lesson Manager
 * Plugin URI: https://jazzedge.com
 * Description: Manage Academy courses, lessons, and chapters with CRUD capabilities
 * Version: 1.0.0
 * Author: JazzEdge
 * License: GPL v2 or later
 * Text Domain: academy-lesson-manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ALM_VERSION', '1.0.1');
define('ALM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ALM_PLUGIN_FILE', __FILE__);

/**
 * Main Academy Lesson Manager Class
 */
class Academy_Lesson_Manager {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Include membership checker early so functions are available
        require_once ALM_PLUGIN_DIR . 'includes/class-membership-checker.php';
        if (class_exists('ALM_Membership_Checker')) {
            ALM_Membership_Checker::init();
        }
        
        // Register AJAX handlers immediately - these need to be available for both admin and frontend
        // Register on both constructor and init to ensure they're available
        add_action('init', array($this, 'register_ajax_handlers'), 5);
        $this->register_ajax_handlers();
        
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Register AJAX handlers for frontend actions
     */
    public function register_ajax_handlers() {
        add_action('wp_ajax_alm_toggle_resource_favorite', array($this, 'ajax_toggle_resource_favorite'));
        add_action('wp_ajax_alm_update_favorites_order', array($this, 'ajax_update_favorites_order'));
        add_action('wp_ajax_alm_delete_favorite', array($this, 'ajax_delete_favorite'));
        add_action('wp_ajax_alm_delete_all_favorites', array($this, 'ajax_delete_all_favorites'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Load plugin text domain
        load_plugin_textdomain('academy-lesson-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');

        $this->maybe_bootstrap_assemblyai_api_key();
        
        // Include required files
        $this->include_files();
        
        // Ensure database tables are up to date (for existing installations)
        $this->ensure_database_tables();
        
        // Initialize frontend library
        if (class_exists('ALM_Frontend_Library')) {
            new ALM_Frontend_Library();
        }
        
        // Initialize admin
        if (is_admin()) {
            $this->init_admin();
        }
        
        // Register cron job for Essentials selections
        $this->register_cron_jobs();
    }
    
    /**
     * Ensure database tables are created and up to date
     * Version-gated: only runs when plugin version changes, not on every page load
     */
    private function ensure_database_tables() {
        $db_version = get_option( 'alm_db_version', '0' );
        if ( $db_version === ALM_VERSION ) {
            return;
        }
        $database = new ALM_Database();
        $database->create_tables();
        update_option( 'alm_db_version', ALM_VERSION );
    }
    
    /**
     * Include required files
     */
    private function include_files() {
        require_once ALM_PLUGIN_DIR . 'includes/class-database.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-helpers.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-collections.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-courses.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-lessons.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-chapters.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-settings.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-event-migration.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-events.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-post-sync.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-bunny-api.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-vimeo-api.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-je-crm-sender.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-rest.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-frontend-search.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-ai.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-ai-recommender.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-essentials-library.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-frontend-library.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-essentials-users.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-lesson-samples.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-lesson-analytics.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-membership-pricing.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-faqs.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-notifications.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-notifications.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-teachers.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-credit-log.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-membership-checker.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-whisper-client.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-vtt-generator.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-transcribe.php';
    }
    
    /**
     * Initialize admin functionality
     */
    private function init_admin() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add AJAX handler for chapter reordering
        add_action('wp_ajax_alm_update_chapter_order', array($this, 'ajax_update_chapter_order'));
        
        // Add AJAX handler for lesson reordering in collections
        add_action('wp_ajax_alm_update_lesson_order', array($this, 'ajax_update_lesson_order'));
        
        // Add AJAX handler for Bunny.net metadata fetching
        add_action('wp_ajax_alm_fetch_bunny_metadata', array($this, 'ajax_fetch_bunny_metadata'));
        
        // Add AJAX handler for Vimeo metadata fetching
        add_action('wp_ajax_alm_fetch_vimeo_metadata', array($this, 'ajax_fetch_vimeo_metadata'));
        
        // Add AJAX handler for testing Bunny.net connection
        add_action('wp_ajax_alm_test_bunny_connection', array($this, 'ajax_test_bunny_connection'));
        add_action('wp_ajax_alm_test_assemblyai', array($this, 'ajax_test_assemblyai'));
        add_action('wp_ajax_alm_save_assemblyai_key', array($this, 'ajax_save_assemblyai_key'));
        
        // Add AJAX handler for debugging Bunny.net config
        add_action('wp_ajax_alm_debug_bunny_config', array($this, 'ajax_debug_bunny_config'));
        
        // Add AJAX handlers for resources
        add_action('wp_ajax_alm_add_resource', array($this, 'ajax_add_resource'));
        add_action('wp_ajax_alm_delete_resource', array($this, 'ajax_delete_resource'));
        
        // Add AJAX handler for calculating lesson duration from chapters
        add_action('wp_ajax_alm_calculate_lesson_duration', array($this, 'ajax_calculate_lesson_duration'));
        
        // Add AJAX handlers for transcription
        add_action('wp_ajax_alm_transcribe_chapter', array($this, 'ajax_transcribe_chapter'));
        add_action('wp_ajax_alm_check_transcription_status', array($this, 'ajax_check_transcription_status'));
        add_action('wp_ajax_alm_force_fetch_transcript', array($this, 'ajax_force_fetch_transcript'));
        add_action('wp_ajax_alm_check_vtt_file', array($this, 'ajax_check_vtt_file'));
        add_action('wp_ajax_alm_sync_vtt_file', array($this, 'ajax_sync_vtt_file'));
        add_action('wp_ajax_alm_load_transcript', array($this, 'ajax_load_transcript'));
        add_action('wp_ajax_alm_save_transcript', array($this, 'ajax_save_transcript'));
        add_action('wp_ajax_alm_clear_transcription_status', array($this, 'ajax_clear_transcription_status'));
        add_action('wp_ajax_alm_remove_mp3', array($this, 'ajax_remove_mp3'));
        add_action('wp_ajax_alm_trigger_transcription', array($this, 'ajax_trigger_transcription'));
        add_action('wp_ajax_nopriv_alm_trigger_transcription', array($this, 'ajax_trigger_transcription')); // Allow non-logged-in for background trigger
        
        // Add AJAX handler for AI description generation
        add_action('wp_ajax_alm_generate_lesson_description', array($this, 'ajax_generate_lesson_description'));
        
        // Add AJAX handler for expanding lesson description with AI
        add_action('wp_ajax_alm_expand_lesson_description', array($this, 'ajax_expand_lesson_description'));
        
        // Add AJAX handler for getting combined lesson transcript
        add_action('wp_ajax_alm_get_lesson_transcript', array($this, 'ajax_get_lesson_transcript'));

        // Add AJAX handler for creating FluentCart product from lesson
        add_action('wp_ajax_alm_create_fluentcart_product', array($this, 'ajax_create_fluentcart_product'));
        // Add AJAX handler for generating product description from chapter titles
        add_action('wp_ajax_alm_generate_product_description', array($this, 'ajax_generate_product_description'));
        
        // Add scheduled event for background transcription
        add_action('alm_run_transcription', array($this, 'run_transcription_background'), 10, 2);
        
        // Add AJAX handler to manually trigger transcription (for WPEngine compatibility)
        add_action('wp_ajax_alm_trigger_transcription', array($this, 'ajax_trigger_transcription'));
        
        // Add AJAX handler for calculating all chapter durations from Bunny API
        add_action('wp_ajax_alm_calculate_all_bunny_durations', array($this, 'ajax_calculate_all_bunny_durations'));
        
        // Add AJAX handler for calculating all chapter durations from Vimeo API
        add_action('wp_ajax_alm_calculate_all_vimeo_durations', array($this, 'ajax_calculate_all_vimeo_durations'));

        // Add AJAX handlers for Transcribe tool
        add_action('wp_ajax_alm_transcribe_upload', array($this, 'ajax_transcribe_upload'));
        add_action('wp_ajax_alm_get_transcript', array($this, 'ajax_get_transcript'));
        add_action('wp_ajax_alm_delete_transcript', array($this, 'ajax_delete_transcript'));
        
        // Add AJAX handlers for collection-level duration calculation
        add_action('wp_ajax_alm_calculate_collection_bunny_durations', array($this, 'ajax_calculate_collection_bunny_durations'));
        
        // Add AJAX handlers for credit log admin
        add_action('wp_ajax_alm_search_users', array($this, 'ajax_search_users'));
        add_action('wp_ajax_alm_search_posts', array($this, 'ajax_search_posts'));
        add_action('wp_ajax_alm_calculate_collection_vimeo_durations', array($this, 'ajax_calculate_collection_vimeo_durations'));
        
        // Add AJAX handler for syncing all lessons in a collection
        add_action('wp_ajax_alm_sync_collection_lessons', array($this, 'ajax_sync_collection_lessons'));
        
        // Add AJAX handler for quick pathway addition
        add_action('wp_ajax_alm_quick_add_pathway', array($this, 'ajax_quick_add_pathway'));
        add_action('wp_ajax_alm_save_column_preferences', array($this, 'ajax_save_column_preferences'));
        
        // Note: Frontend AJAX handlers (favorites management) are registered in constructor
        
        // Add AJAX handlers for Essentials library
        add_action('wp_ajax_alm_add_to_library', array($this, 'ajax_add_to_library'));
        add_action('wp_ajax_alm_get_library_status', array($this, 'ajax_get_library_status'));
        
        // Add AJAX handlers for lesson samples (delegates to ALM_Admin_Lesson_Samples)
        add_action('wp_ajax_alm_set_intro_sample', array($this, 'ajax_set_intro_sample'));
        add_action('wp_ajax_alm_set_shortest_sample', array($this, 'ajax_set_shortest_sample'));
        
        // Add AJAX handlers for webhook
        add_action('wp_ajax_alm_get_webhook_logs', array($this, 'ajax_get_webhook_logs'));
        add_action('wp_ajax_alm_clear_webhook_logs', array($this, 'ajax_clear_webhook_logs'));
        add_action('wp_ajax_alm_retry_webhook', array($this, 'ajax_retry_webhook'));
        
        // Add WordPress hooks for reverse sync
        add_action('save_post', array($this, 'handle_post_save'));
        add_action('delete_post', array($this, 'handle_post_delete'));
        
        // Ensure files are included (safety check for AJAX requests)
        $this->include_files();
        
        // Initialize admin classes
        new ALM_Admin_Collections();
        new ALM_Admin_Courses();
        new ALM_Admin_Lessons();
        new ALM_Admin_Chapters();
        new ALM_Admin_Settings();
        new ALM_Admin_Event_Migration();
        new ALM_Admin_Essentials_Users();
        new ALM_Admin_Membership_Pricing();
        new ALM_Admin_Notifications();
        if (class_exists('ALM_Admin_Teachers')) {
            new ALM_Admin_Teachers();
        }
        new ALM_Admin_Lesson_Samples(); // Initialize early so hooks fire
        new ALM_Admin_Lesson_Analytics();
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Lesson Manager', 'academy-lesson-manager'),
            __('Lesson Manager', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager',
            array($this, 'admin_page_collections'),
            'dashicons-book-alt',
            30
        );
        
        // Submenus
        add_submenu_page(
            'academy-manager',
            __('Collections', 'academy-lesson-manager'),
            __('Collections', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager',
            array($this, 'admin_page_collections')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Lessons', 'academy-lesson-manager'),
            __('Lessons', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-lessons',
            array($this, 'admin_page_lessons')
        );

        add_submenu_page(
            'academy-manager',
            __('Lesson Analytics', 'academy-lesson-manager'),
            __('Lesson Analytics', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-lesson-analytics',
            array($this, 'admin_page_lesson_analytics')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Chapters', 'academy-lesson-manager'),
            __('Chapters', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-chapters',
            array($this, 'admin_page_chapters')
        );

        add_submenu_page(
            'academy-manager',
            __('Transcribe', 'academy-lesson-manager'),
            __('Transcribe', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-transcribe',
            array($this, 'admin_page_transcribe')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Event Migration', 'academy-lesson-manager'),
            __('Event Migration', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-event-migration',
            array($this, 'admin_page_event_migration')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Settings', 'academy-lesson-manager'),
            __('Settings', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-settings',
            array($this, 'admin_page_settings')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Essentials Users', 'academy-lesson-manager'),
            __('Essentials Users', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-essentials-users',
            array($this, 'admin_page_essentials_users')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Lesson Samples', 'academy-lesson-manager'),
            __('Lesson Samples', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-lesson-samples',
            array($this, 'admin_page_lesson_samples')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Teachers', 'academy-lesson-manager'),
            __('Teachers', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-teachers',
            array($this, 'admin_page_teachers')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Credit Log', 'academy-lesson-manager'),
            __('Credit Log', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-credit-log',
            array($this, 'admin_page_credit_log')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Search Logs', 'academy-lesson-manager'),
            __('Search Logs', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-search-logs',
            array($this, 'admin_page_search_logs')
        );
    }
    
    /**
     * Admin page callbacks
     */
    public function admin_page_collections() {
        $admin_collections = new ALM_Admin_Collections();
        $admin_collections->render_page();
    }
    
    public function admin_page_courses() {
        $admin_courses = new ALM_Admin_Courses();
        $admin_courses->render_page();
    }
    
    public function admin_page_lessons() {
        $admin_lessons = new ALM_Admin_Lessons();
        $admin_lessons->render_page();
    }

    public function admin_page_lesson_analytics() {
        $admin_analytics = new ALM_Admin_Lesson_Analytics();
        $admin_analytics->render_page();
    }
    
    public function admin_page_chapters() {
        $admin_chapters = new ALM_Admin_Chapters();
        $admin_chapters->render_page();
    }

    public function admin_page_transcribe() {
        $admin_transcribe = new ALM_Admin_Transcribe();
        $admin_transcribe->render_page();
    }
    
    public function admin_page_event_migration() {
        $admin_event_migration = new ALM_Admin_Event_Migration();
        $admin_event_migration->render_page();
    }
    
    public function admin_page_settings() {
        $admin_settings = new ALM_Admin_Settings();
        $admin_settings->render_settings_page();
    }
    
    public function admin_page_essentials_users() {
        $admin_essentials_users = new ALM_Admin_Essentials_Users();
        $admin_essentials_users->render_page();
    }
    
    public function admin_page_lesson_samples() {
        $admin_lesson_samples = new ALM_Admin_Lesson_Samples();
        $admin_lesson_samples->render_page();
    }
    
    public function admin_page_search_logs() {
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-search-logs.php';
        $admin_search_logs = new ALM_Admin_Search_Logs();
        $admin_search_logs->render_page();
    }
    
    public function admin_page_teachers() {
        // Ensure files are included (safety check for AJAX requests)
        $this->include_files();
        
        if (!class_exists('ALM_Admin_Teachers')) {
            wp_die(__('ALM_Admin_Teachers class not found. Please ensure the plugin files are properly loaded.', 'academy-lesson-manager'));
        }
        
        $admin_teachers = new ALM_Admin_Teachers();
        $admin_teachers->render_page();
    }
    
    public function admin_page_credit_log() {
        $admin_credit_log = new ALM_Admin_Credit_Log();
        $admin_credit_log->render_page();
    }
    
    /**
     * AJAX handler for searching users
     */
    public function ajax_search_users() {
        check_ajax_referer('alm_search_users', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        
        if (strlen($query) < 2) {
            wp_send_json_success(array());
        }
        
        global $wpdb;
        
        $users = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, user_login, user_email, display_name 
             FROM {$wpdb->users} 
             WHERE user_login LIKE %s OR user_email LIKE %s OR display_name LIKE %s 
             ORDER BY display_name ASC 
             LIMIT 20",
            '%' . $wpdb->esc_like($query) . '%',
            '%' . $wpdb->esc_like($query) . '%',
            '%' . $wpdb->esc_like($query) . '%'
        ));
        
        $results = array();
        foreach ($users as $user) {
            $results[] = array(
                'ID' => $user->ID,
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
                'display_name' => $user->display_name
            );
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX handler for searching posts/lessons
     * Searches the alm_lessons table to get the correct post_id
     * Also searches by collection title and allows adding entire collections
     */
    public function ajax_search_posts() {
        check_ajax_referer('alm_search_posts', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        $search_type = isset($_POST['search_type']) ? sanitize_text_field($_POST['search_type']) : 'lesson'; // 'lesson' or 'collection'
        
        if (strlen($query) < 2) {
            wp_send_json_success(array());
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $lessons_table = $database->get_table_name('lessons');
        $collections_table = $database->get_table_name('collections');
        
        $results = array();
        
        if ($search_type === 'collection') {
            // Search collections
            $collections = $wpdb->get_results($wpdb->prepare(
                "SELECT c.ID, c.collection_title, c.post_id 
                 FROM {$collections_table} c
                 WHERE c.collection_title LIKE %s 
                 ORDER BY c.collection_title ASC 
                 LIMIT 20",
                '%' . $wpdb->esc_like($query) . '%'
            ));
            
            foreach ($collections as $collection) {
                // Get all lessons in this collection with their post_ids
                // IMPORTANT: Use the post_id from alm_lessons table - this is what shows on the edit page
                $lessons_in_collection = $wpdb->get_results($wpdb->prepare(
                    "SELECT l.ID as lesson_id, l.lesson_title, l.post_id 
                     FROM {$lessons_table} l
                     WHERE l.collection_id = %d AND l.post_id > 0
                     ORDER BY l.ID DESC",
                    $collection->ID
                ));
                
                $lesson_count = count($lessons_in_collection);
                $post_ids = array();
                $lessons_data = array();
                
                foreach ($lessons_in_collection as $l) {
                    $post_id = intval($l->post_id);
                    $lesson_id = intval($l->lesson_id);
                    
                    // Verify post exists and matches
                    $post = get_post($post_id);
                    if ($post_id > 0 && $post) {
                        $post_ids[] = $post_id;
                        $lessons_data[] = array(
                            'lesson_id' => $lesson_id,
                            'post_id' => $post_id,
                            'lesson_title' => $l->lesson_title,
                            'post_title' => $post->post_title
                        );
                    } else {
                        // Log if post doesn't exist but post_id is set
                        error_log("Credit Log Debug: Lesson ID {$lesson_id} has post_id {$post_id} but post doesn't exist");
                    }
                }
                
                $results[] = array(
                    'ID' => 0, // Collection, not a single post
                    'post_title' => $collection->collection_title,
                    'collection_id' => $collection->ID,
                    'collection_title' => $collection->collection_title,
                    'is_collection' => true,
                    'lesson_count' => count($post_ids), // Use actual count of valid posts
                    'post_ids' => $post_ids,
                    'lessons_data' => $lessons_data, // Include full lesson data for better display
                    'display_title' => $collection->collection_title . ' (' . count($post_ids) . ' lessons)'
                );
            }
        } else {
            // Search lessons (default)
            $lessons = $wpdb->get_results($wpdb->prepare(
                "SELECT l.ID, l.lesson_title, l.post_id, l.collection_id, c.collection_title
                 FROM {$lessons_table} l
                 LEFT JOIN {$collections_table} c ON l.collection_id = c.ID
                 WHERE l.lesson_title LIKE %s 
                 ORDER BY c.collection_title ASC, l.lesson_title ASC 
                 LIMIT 50",
                '%' . $wpdb->esc_like($query) . '%'
            ));
            
            foreach ($lessons as $lesson) {
                // Use post_id from alm_lessons table (this is the correct post_id shown on edit page)
                $post_id = intval($lesson->post_id);
                $post_title = $lesson->lesson_title;
                
                // If post_id exists, get the actual post title for display
                if ($post_id > 0) {
                    $post = get_post($post_id);
                    if ($post) {
                        $post_title = $post->post_title;
                    }
                }
                
                $collection_name = $lesson->collection_title ? $lesson->collection_title : 'No Collection';
                
                $results[] = array(
                    'ID' => $post_id, // This is the post_id that should be used in credit log
                    'post_title' => $post_title,
                    'lesson_id' => $lesson->ID, // Include lesson ID for reference
                    'collection_id' => $lesson->collection_id,
                    'collection_title' => $collection_name,
                    'is_collection' => false,
                    'display_title' => $lesson->lesson_title . ($post_id > 0 ? ' (Post ID: ' . $post_id . ')' : ' (No Post ID)') . ' - ' . $collection_name
                );
            }
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'academy-manager') === false) {
            return;
        }
        
        wp_enqueue_style(
            'alm-admin-css',
            ALM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            ALM_VERSION
        );
        
        wp_enqueue_script(
            'alm-admin-js',
            ALM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            ALM_VERSION,
            true
        );
        
        // Enqueue media uploader scripts
        wp_enqueue_media();
        
        // Localize script with AJAX data
        wp_localize_script('alm-admin-js', 'alm_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('alm_admin_nonce')
        ));
        
        // Add JavaScript for duration formatting and chapter reordering
        wp_add_inline_script('alm-admin-js', '
            jQuery(document).ready(function($) {
                function formatDuration(seconds) {
                    if (!seconds || seconds <= 0) return "00:00:00";
                    
                    var hours = Math.floor(seconds / 3600);
                    var minutes = Math.floor((seconds % 3600) / 60);
                    var secs = seconds % 60;
                    
                    return String(hours).padStart(2, "0") + ":" + 
                           String(minutes).padStart(2, "0") + ":" + 
                           String(secs).padStart(2, "0");
                }
                
                // Update duration display when input changes
                $("#duration").on("input", function() {
                    var seconds = parseInt($(this).val()) || 0;
                    var formatted = formatDuration(seconds);
                    $(this).next(".description").text("(" + formatted + ")");
                });
                
                // Chapter reordering functionality
                if ($(".alm-chapter-reorder").length > 0) {
                    $(".alm-chapter-reorder tbody").sortable({
                        handle: ".chapter-drag-handle",
                        placeholder: "chapter-placeholder",
                        update: function(event, ui) {
                            var chapterIds = [];
                            $(this).find("tr").each(function(index) {
                                var chapterId = $(this).data("chapter-id");
                                if (chapterId) {
                                    chapterIds.push(chapterId);
                                }
                            });
                            
                            // Update order numbers in the display
                            $(this).find("tr").each(function(index) {
                                $(this).find(".chapter-order").text(index + 1);
                            });
                            
                            // Send AJAX request to update database
                            $.ajax({
                                url: ajaxurl,
                                type: "POST",
                                data: {
                                    action: "alm_update_chapter_order",
                                    chapter_ids: chapterIds,
                                    nonce: alm_admin.nonce
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Show success message
                                        $("<div class=\"notice notice-success is-dismissible\"><p>Chapter order updated successfully.</p></div>")
                                            .insertAfter(".alm-chapter-reorder")
                                            .delay(3000)
                                            .fadeOut();
                                    } else {
                                        console.error("AJAX Error:", response);
                                        alert("Error updating chapter order: " + (response.data || "Unknown error"));
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX Request Failed:", xhr, status, error);
                                    alert("Error updating chapter order. Please refresh the page.");
                                }
                            });
                        }
                    });
                }
                
                // Lesson reordering functionality in collections
                if ($(".alm-collection-lessons").length > 0) {
                    $(".alm-collection-lessons tbody.ui-sortable").sortable({
                        handle: ".dashicons-menu",
                        cursor: "move",
                        placeholder: "sortable-placeholder",
                        tolerance: "pointer",
                        opacity: 0.6,
                        update: function(event, ui) {
                            var lessonIds = [];
                            $(this).find("tr").each(function(index) {
                                var lessonId = $(this).data("lesson-id");
                                if (lessonId) {
                                    lessonIds.push(lessonId);
                                }
                            });
                            
                            // Send AJAX request to update database
                            $.ajax({
                                url: ajaxurl,
                                type: "POST",
                                data: {
                                    action: "alm_update_lesson_order",
                                    lesson_ids: lessonIds,
                                    nonce: alm_admin.nonce
                                },
                                success: function(response) {
                                    if (!response.success) {
                                        console.error("AJAX Error:", response);
                                        alert("Error updating lesson order: " + (response.data || "Unknown error"));
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX Request Failed:", xhr, status, error);
                                    alert("Error updating lesson order. Please refresh the page.");
                                }
                            });
                        }
                    });
                }
            });
        ');
        
        // Add JavaScript for Bunny.net metadata fetching
        wp_add_inline_script('alm-admin-js', '
            jQuery(document).ready(function($) {
                // Bunny.net metadata fetching
                $(".fetch-bunny-metadata").on("click", function(e) {
                    e.preventDefault();
                    
                    var $button = $(this);
                    var $urlField = $button.siblings("input[name=\'bunny_url\']");
                    var $durationField = $("input[name=\'duration\']");
                    var bunnyUrl = $urlField.val();
                    
                    if (!bunnyUrl) {
                        alert("Please enter a Bunny URL first.");
                        return;
                    }
                    
                    $button.prop("disabled", true).text("Fetching...");
                    
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "alm_fetch_bunny_metadata",
                            bunny_url: bunnyUrl,
                            nonce: alm_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                var data = response.data;
                                var updates = [];
                                
                                // Update duration field
                                if (data.duration > 0) {
                                    $durationField.val(data.duration);
                                    
                                    // Update duration display
                                    var hours = Math.floor(data.duration / 3600);
                                    var minutes = Math.floor((data.duration % 3600) / 60);
                                    var seconds = data.duration % 60;
                                    var formatted = String(hours).padStart(2, "0") + ":" + 
                                                   String(minutes).padStart(2, "0") + ":" + 
                                                   String(seconds).padStart(2, "0");
                                    $durationField.next(".description").text("(" + formatted + ")");
                                    updates.push("Duration: " + Math.floor(data.duration / 60) + " minutes");
                                }
                                
                                // Update release date field
                                if (data.created_at && data.created_at !== "0000-00-00" && data.created_at !== "0000-00-00T00:00:00Z") {
                                    var $releaseDateField = $("input[name=\'post_date\']");
                                    console.log("Release date field found:", $releaseDateField.length);
                                    console.log("Bunny.net created_at:", data.created_at);
                                    
                                    if ($releaseDateField.length > 0) {
                                        // Convert Bunny.net date to YYYY-MM-DD format
                                        var date = new Date(data.created_at);
                                        
                                        // Check if date is valid
                                        if (!isNaN(date.getTime())) {
                                            var formattedDate = date.getFullYear() + "-" + 
                                                               String(date.getMonth() + 1).padStart(2, "0") + "-" + 
                                                               String(date.getDate()).padStart(2, "0");
                                            console.log("Formatted date:", formattedDate);
                                            $releaseDateField.val(formattedDate);
                                            updates.push("Release Date: " + formattedDate);
                                        } else {
                                            console.log("Invalid date from Bunny.net:", data.created_at);
                                        }
                                    } else {
                                        console.log("Release date field not found!");
                                    }
                                } else {
                                    console.log("No valid created_at date from Bunny.net:", data.created_at);
                                }
                                
                                // Show success message with all updates
                                var message = "Video metadata fetched successfully! " + updates.join(", ");
                                $("<div class=\"notice notice-success is-dismissible\"><p>" + message + "</p></div>")
                                    .insertAfter($button.closest("tr"))
                                    .delay(3000)
                                    .fadeOut();
                            } else {
                                alert("Error: " + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error fetching video metadata. Please check your Bunny.net API configuration.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Fetch Metadata");
                        }
                    });
                });
                
                // Vimeo metadata fetching
                $(".fetch-vimeo-metadata").on("click", function(e) {
                    e.preventDefault();
                    
                    var $button = $(this);
                    var $vimeoField = $button.siblings("input[name=\'vimeo_id\']");
                    var $durationField = $("input[name=\'duration\']");
                    var vimeoId = $vimeoField.val();
                    
                    if (!vimeoId) {
                        alert("Please enter a Vimeo ID first.");
                        return;
                    }
                    
                    $button.prop("disabled", true).text("Fetching...");
                    
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "alm_fetch_vimeo_metadata",
                            vimeo_id: vimeoId,
                            nonce: alm_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                var data = response.data;
                                var updates = [];
                                
                                // Update duration field
                                if (data.duration > 0) {
                                    $durationField.val(data.duration);
                                    
                                    // Update duration display
                                    var hours = Math.floor(data.duration / 3600);
                                    var minutes = Math.floor((data.duration % 3600) / 60);
                                    var seconds = data.duration % 60;
                                    var formatted = String(hours).padStart(2, "0") + ":" + 
                                                   String(minutes).padStart(2, "0") + ":" + 
                                                   String(seconds).padStart(2, "0");
                                    $durationField.next(".description").text("(" + formatted + ")");
                                    updates.push("Duration: " + Math.floor(data.duration / 60) + " minutes");
                                }
                                
                                // Show success message with all updates
                                var message = "Video metadata fetched successfully! " + updates.join(", ");
                                $("<div class=\"notice notice-success is-dismissible\"><p>" + message + "</p></div>")
                                    .insertAfter($button.closest("tr"))
                                    .delay(3000)
                                    .fadeOut();
                            } else {
                                alert("Error: " + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error fetching video metadata. Please check the Vimeo ID.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Fetch Metadata");
                        }
                    });
                });
            });
        ');
        
        // Add JavaScript for calculating all Bunny durations
        wp_add_inline_script('alm-admin-js', '
            jQuery(document).ready(function($) {
                $("#alm-calculate-bunny-durations").on("click", function() {
                    var $button = $(this);
                    var lessonId = $button.data("lesson-id");
                    
                    if (!lessonId) {
                        alert("Error: No lesson ID found");
                        return;
                    }
                    
                    $button.prop("disabled", true).text("Calculating...");
                    
                    $.ajax({
                        url: alm_admin.ajax_url,
                        type: "POST",
                        data: {
                            action: "alm_calculate_all_bunny_durations",
                            nonce: alm_admin.nonce,
                            lesson_id: lessonId
                        },
                        success: function(response) {
                            if (response.success) {
                                var message = "Successfully updated " + response.data.updated + " chapters. ";
                                if (response.data.total_duration > 0) {
                                    var hours = Math.floor(response.data.total_duration / 3600);
                                    var minutes = Math.floor((response.data.total_duration % 3600) / 60);
                                    message += "Total lesson duration: " + (hours > 0 ? hours + "h " : "") + minutes + "m";
                                } else {
                                    message += "Total lesson duration: 0";
                                }
                                
                                // Show warnings if there were errors but some chapters were updated
                                if (response.data.errors_count > 0) {
                                    message += " (" + response.data.errors_count + " chapters had errors)";
                                }
                                
                                $("<div class=\"notice notice-success is-dismissible\"><p>" + message + "</p></div>")
                                    .insertAfter($button.closest("h3"))
                                    .delay(5000)
                                    .fadeOut();
                                
                                // Show detailed errors if any
                                if (response.data.errors && response.data.errors.length > 0) {
                                    var errorMessage = "Some chapters failed to update:\n\n" + response.data.errors.slice(0, 5).join("\n");
                                    if (response.data.errors.length > 5) {
                                        errorMessage += "\n... and " + (response.data.errors.length - 5) + " more errors";
                                    }
                                    console.warn("Bunny API Duration Calculation Errors:", response.data.errors);
                                    console.warn("Debug Info:", response.data.debug_info);
                                }
                                
                                // Reload page after a short delay
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                var errorMsg = "Error calculating durations.";
                                if (response.data && response.data.message) {
                                    errorMsg = response.data.message;
                                } else if (typeof response.data === "string") {
                                    errorMsg = response.data;
                                }
                                
                                if (response.data && response.data.errors && response.data.errors.length > 0) {
                                    errorMsg += "\n\nErrors:\n" + response.data.errors.slice(0, 3).join("\n");
                                    console.error("Bunny API Errors:", response.data.errors);
                                    console.error("Debug Info:", response.data.debug_info);
                                }
                                
                                alert(errorMsg);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            var errorMsg = "Error calculating durations. Please check your Bunny.net API configuration.";
                            
                            // Try to get more details from the response
                            if (xhr.responseJSON && xhr.responseJSON.data) {
                                if (typeof xhr.responseJSON.data === "string") {
                                    errorMsg = xhr.responseJSON.data;
                                } else if (xhr.responseJSON.data.message) {
                                    errorMsg = xhr.responseJSON.data.message;
                                }
                                
                                if (xhr.responseJSON.data.errors) {
                                    errorMsg += "\n\nErrors:\n" + xhr.responseJSON.data.errors.slice(0, 3).join("\n");
                                    console.error("Bunny API Errors:", xhr.responseJSON.data.errors);
                                }
                            } else if (xhr.responseText) {
                                try {
                                    var response = JSON.parse(xhr.responseText);
                                    if (response.data && typeof response.data === "string") {
                                        errorMsg = response.data;
                                    }
                                } catch(e) {
                                    // Not JSON, use default message
                                }
                            }
                            
                            alert(errorMsg);
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Calculate All Bunny Durations");
                        }
                    });
                });
            });
        ');
        
        // Add JavaScript for calculating all Vimeo durations
        wp_add_inline_script('alm-admin-js', '
            jQuery(document).ready(function($) {
                $("#alm-calculate-vimeo-durations").on("click", function() {
                    var $button = $(this);
                    var lessonId = $button.data("lesson-id");
                    
                    if (!lessonId) {
                        alert("Error: No lesson ID found");
                        return;
                    }
                    
                    $button.prop("disabled", true).text("Calculating...");
                    
                    $.ajax({
                        url: alm_admin.ajax_url,
                        type: "POST",
                        data: {
                            action: "alm_calculate_all_vimeo_durations",
                            nonce: alm_admin.nonce,
                            lesson_id: lessonId
                        },
                        success: function(response) {
                            if (response.success) {
                                var message = "Successfully updated " + response.data.updated + " chapters. ";
                                if (response.data.total_duration > 0) {
                                    var hours = Math.floor(response.data.total_duration / 3600);
                                    var minutes = Math.floor((response.data.total_duration % 3600) / 60);
                                    message += "Total lesson duration: " + (hours > 0 ? hours + "h " : "") + minutes + "m";
                                } else {
                                    message += "Total lesson duration: 0";
                                }
                                $("<div class=\"notice notice-success is-dismissible\"><p>" + message + "</p></div>")
                                    .insertAfter($button.closest("h3"))
                                    .delay(2000)
                                    .fadeOut();
                                
                                // Reload page to show updated durations
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                alert("Error: " + (response.data ? response.data : "Unknown error"));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error calculating Vimeo durations. Please check the console for details.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Calculate All Vimeo Durations");
                        }
                    });
                });
                
                // Handle "Find VTT File" button click
                $("#alm-sync-vtt-file").on("click", function() {
                    var $button = $(this);
                    var chapterId = $button.data("chapter-id");
                    var lessonId = $button.data("lesson-id");
                    var $status = $("#alm-sync-vtt-status");
                    var $input = $("#transcript_file");
                    
                    if (!chapterId || !lessonId) {
                        alert("Error: Missing chapter or lesson ID");
                        return;
                    }
                    
                    $button.prop("disabled", true).text("Searching...");
                    $status.html("");
                    
                    $.ajax({
                        url: alm_admin.ajax_url,
                        type: "POST",
                        data: {
                            action: "alm_sync_vtt_file",
                            chapter_id: chapterId,
                            lesson_id: lessonId,
                            nonce: alm_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update the input field with the filename
                                $input.val(response.data.vtt_filename);
                                $status.html("<span style=\"color: #46b450; font-weight: bold;\">✓ " + response.data.message + "</span>");
                                $button.prop("disabled", false).text("Find VTT File");
                            } else {
                                $status.html("<span style=\"color: #dc3232;\">✗ " + (response.data && response.data.message ? response.data.message : "Error finding VTT file") + "</span>");
                                $button.prop("disabled", false).text("Find VTT File");
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            $status.html("<span style=\"color: #dc3232;\">✗ Error: " + error + "</span>");
                            $button.prop("disabled", false).text("Find VTT File");
                        }
                    });
                });
            });
        ');
        
        // Add JavaScript for collection-level duration calculation
        wp_add_inline_script('alm-admin-js', '
            jQuery(document).ready(function($) {
                // Collection Bunny durations - use delegation in case button loads dynamically
                $(document).on("click", "#alm-calculate-collection-bunny-durations", function() {
                    var $button = $(this);
                    var collectionId = $button.data("collection-id");
                    
                    if (!collectionId) {
                        alert("Error: No collection ID found");
                        return false;
                    }
                    
                    if (!confirm("This will calculate durations for ALL lessons in this collection. This may take a while for large collections. Continue?")) {
                        return false;
                    }
                    
                    $button.prop("disabled", true).text("Calculating...");
                    
                    $.ajax({
                        url: alm_admin.ajax_url,
                        type: "POST",
                        data: {
                            action: "alm_calculate_collection_bunny_durations",
                            nonce: alm_admin.nonce,
                            collection_id: collectionId
                        },
                        success: function(response) {
                            if (response.success) {
                                var message = "Collection processed: " + response.data.lessons_processed + " lessons, " + response.data.lessons_updated + " updated. ";
                                message += response.data.chapters_updated + " chapters updated. ";
                                if (response.data.total_duration > 0) {
                                    var hours = Math.floor(response.data.total_duration / 3600);
                                    var minutes = Math.floor((response.data.total_duration % 3600) / 60);
                                    message += "Total duration: " + (hours > 0 ? hours + "h " : "") + minutes + "m";
                                }
                                $("<div class=\"notice notice-success is-dismissible\"><p>" + message + "</p></div>")
                                    .insertAfter($button.closest("h3"))
                                    .delay(5000)
                                    .fadeOut();
                                
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                alert("Error: " + (response.data ? response.data : "Unknown error"));
                            }
                        },
                        error: function(xhr, status, error) {
                            alert("Error calculating durations. Please try again.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Calculate All Bunny Durations");
                        }
                    });
                    
                    return false;
                });
                
                // Collection Vimeo durations - use delegation
                $(document).on("click", "#alm-calculate-collection-vimeo-durations", function() {
                    var $button = $(this);
                    var collectionId = $button.data("collection-id");
                    
                    if (!collectionId) {
                        alert("Error: No collection ID found");
                        return false;
                    }
                    
                    if (!confirm("This will calculate durations for ALL lessons in this collection. This may take a while for large collections. Continue?")) {
                        return false;
                    }
                    
                    $button.prop("disabled", true).text("Calculating...");
                    
                    $.ajax({
                        url: alm_admin.ajax_url,
                        type: "POST",
                        data: {
                            action: "alm_calculate_collection_vimeo_durations",
                            nonce: alm_admin.nonce,
                            collection_id: collectionId
                        },
                        success: function(response) {
                            if (response.success) {
                                var message = "Collection processed: " + response.data.lessons_processed + " lessons, " + response.data.lessons_updated + " updated. ";
                                message += response.data.chapters_updated + " chapters updated. ";
                                if (response.data.total_duration > 0) {
                                    var hours = Math.floor(response.data.total_duration / 3600);
                                    var minutes = Math.floor((response.data.total_duration % 3600) / 60);
                                    message += "Total duration: " + (hours > 0 ? hours + "h " : "") + minutes + "m";
                                }
                                $("<div class=\"notice notice-success is-dismissible\"><p>" + message + "</p></div>")
                                    .insertAfter($button.closest("h3"))
                                    .delay(5000)
                                    .fadeOut();
                                
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                alert("Error: " + (response.data ? response.data : "Unknown error"));
                            }
                        },
                        error: function(xhr, status, error) {
                            alert("Error calculating durations. Please try again.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Calculate All Vimeo Durations");
                        }
                    });
                    
                    return false;
                });
                
                // Collection lesson sync
                $("#alm-sync-collection-lessons").on("click", function() {
                    var $button = $(this);
                    var collectionId = $button.data("collection-id");
                    
                    if (!collectionId) {
                        alert("Error: No collection ID found");
                        return;
                    }
                    
                    if (!confirm("This will sync ALL lessons in this collection to their WordPress posts and update ACF fields. Continue?")) {
                        return;
                    }
                    
                    $button.prop("disabled", true).text("Syncing...");
                    
                    $.ajax({
                        url: alm_admin.ajax_url,
                        type: "POST",
                        data: {
                            action: "alm_sync_collection_lessons",
                            nonce: alm_admin.nonce,
                            collection_id: collectionId
                        },
                        success: function(response) {
                            if (response.success) {
                                var message = "Sync completed: " + response.data.lessons_processed + " lessons processed, " + response.data.lessons_synced + " synced successfully.";
                                if (response.data.lessons_failed > 0) {
                                    message += " " + response.data.lessons_failed + " failed.";
                                }
                                $("<div class=\"notice notice-success is-dismissible\"><p>" + message + "</p></div>")
                                    .insertAfter($button.closest("h3"))
                                    .delay(5000)
                                    .fadeOut();
                                
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                alert("Error: " + (response.data ? response.data : "Unknown error"));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error syncing lessons. Please check the console for details.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Sync All Lessons");
                        }
                    });
                });
            });
        ');
        
        // Add JavaScript for resource management
        wp_add_inline_script('alm-admin-js', '
            jQuery(document).ready(function($) {
                // Toggle add resource form
                $("#alm-add-resource-btn").on("click", function() {
                    $("#alm-add-resource-form").slideToggle();
                });
                
                // Show/hide fields based on resource type
                function toggleResourceFields() {
                    var resourceType = $("#alm-resource-type").val();
                    if (resourceType === "note") {
                        $(".alm-resource-file-row").hide();
                        $(".alm-resource-note-row").show();
                    } else {
                        $(".alm-resource-file-row").show();
                        $(".alm-resource-note-row").hide();
                    }
                }
                
                // Trigger on load and when type changes
                toggleResourceFields();
                $("#alm-resource-type").on("change", toggleResourceFields);
                
                // Cancel add resource form
                $("#alm-cancel-resource-btn").on("click", function() {
                    $("#alm-add-resource-form").slideUp();
                    $("#alm-resource-type").val("").prop("disabled", false);
                    $("#alm-resource-url").val("");
                    $("#alm-resource-attachment-id").val("");
                    $("#alm-resource-label").val("");
                    $("#alm-resource-note").val("");
                    $("#alm-edit-resource-type").val("");
                    $("#alm-clear-media-btn").hide();
                    $(".alm-resource-file-row").show();
                    $(".alm-resource-note-row").hide();
                    $("#alm-add-resource-form h4").text("Add New Resource");
                    $("#alm-save-resource-btn").text("Add Resource");
                });
                
                // Media library selector
                var mediaUploader;
                $("#alm-select-media-btn").on("click", function(e) {
                    e.preventDefault();
                    
                    // If the uploader object has already been created, reopen it
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    
                    // Create the media uploader
                    mediaUploader = wp.media({
                        title: "Select Resource File",
                        button: {
                            text: "Use this file"
                        },
                        multiple: false
                        // Note: No type filter allows all file types including HTML
                    });
                    
                    // When a file is selected, run a callback
                    mediaUploader.on("select", function() {
                        var attachment = mediaUploader.state().get("selection").first().toJSON();
                        $("#alm-resource-attachment-id").val(attachment.id);
                        $("#alm-resource-url").val(attachment.url);
                        $("#alm-clear-media-btn").show();
                        
                        // Auto-detect file type based on extension
                        var fileName = attachment.filename || attachment.url || "";
                        var fileExtension = fileName.toLowerCase().split(".").pop();
                        
                        // Auto-select resource type based on file extension
                        if (fileExtension === "html") {
                            $("#alm-resource-type").val("ireal");
                        } else if (fileExtension === "mp3") {
                            $("#alm-resource-type").val("jam");
                        } else if (fileExtension === "zip") {
                            $("#alm-resource-type").val("zip");
                        }
                    });
                    
                    // Open the uploader
                    mediaUploader.open();
                });
                
                // Clear selected media
                $("#alm-clear-media-btn").on("click", function() {
                    $("#alm-resource-attachment-id").val("");
                    $("#alm-resource-url").val("");
                    $(this).hide();
                });
                
                // Allow manual URL entry (double-click to enable)
                $("#alm-resource-url").on("dblclick", function() {
                    $(this).prop("readonly", false).css("background-color", "#fff");
                });
                
                // Auto-detect resource type when manually entering URL
                $("#alm-resource-url").on("input paste", function() {
                    var url = $(this).val();
                    if (url) {
                        var fileExtension = url.toLowerCase().split(".").pop().split("?")[0]; // Remove query params
                        
                        // Only auto-select if resource type is not already chosen or is disabled (edit mode)
                        if (!$("#alm-resource-type").prop("disabled")) {
                            if (fileExtension === "html") {
                                $("#alm-resource-type").val("ireal");
                            } else if (fileExtension === "mp3") {
                                $("#alm-resource-type").val("jam");
                            } else if (fileExtension === "zip") {
                                $("#alm-resource-type").val("zip");
                            }
                        }
                    }
                });
                
                // Add/Edit resource
                $("#alm-save-resource-btn").on("click", function() {
                    var resourceType = $("#alm-resource-type").val();
                    var resourceUrl = $("#alm-resource-url").val();
                    var attachmentId = $("#alm-resource-attachment-id").val();
                    var resourceLabel = $("#alm-resource-label").val();
                    var resourceNote = $("#alm-resource-note").val();
                    var editResourceType = $("#alm-edit-resource-type").val();
                    var lessonId = ' . (isset($_GET['id']) ? intval($_GET['id']) : 0) . ';
                    var isEdit = editResourceType !== "";
                    
                    // Use editResourceType if editing and resourceType is empty (disabled field)
                    if (isEdit && !resourceType) {
                        resourceType = editResourceType;
                    }
                    
                    // For note type, use note content as URL
                    if (resourceType === "note" && resourceNote) {
                        resourceUrl = resourceNote;
                    }
                    
                    if (!resourceType || (!resourceUrl && resourceType !== "note")) {
                        alert("Please select a resource type and choose a file or enter note content.");
                        return;
                    }
                    
                    var $button = $(this);
                    $button.prop("disabled", true).text(isEdit ? "Updating..." : "Adding...");
                    
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "alm_add_resource",
                            lesson_id: lessonId,
                            resource_type: resourceType,
                            resource_url: resourceUrl,
                            attachment_id: attachmentId,
                            resource_label: resourceLabel,
                            old_resource_type: editResourceType,
                            nonce: alm_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Reload page to show updated resources
                                location.reload();
                            } else {
                                alert("Error: " + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error " + (isEdit ? "updating" : "adding") + " resource. Please try again.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text(isEdit ? "Update Resource" : "Add Resource");
                        }
                    });
                });
                
                // Edit resource
                $(document).on("click", ".alm-edit-resource", function(e) {
                    e.preventDefault();
                    
                    var $link = $(this);
                    var resourceType = $link.data("type");
                    var resourceUrl = $link.data("url");
                    var attachmentId = $link.data("attachment-id");
                    var resourceLabel = $link.data("label") || "";
                    
                    // Populate form with existing data
                    $("#alm-edit-resource-type").val(resourceType);
                    $("#alm-resource-type").val(resourceType).prop("disabled", true);
                    $("#alm-resource-url").val(resourceUrl);
                    $("#alm-resource-attachment-id").val(attachmentId);
                    $("#alm-resource-label").val(resourceLabel);
                    
                    // Handle note type - if its a note, populate note field with url content
                    if (resourceType === "note") {
                        $("#alm-resource-note").val(resourceUrl);
                        $(".alm-resource-file-row").hide();
                        $(".alm-resource-note-row").show();
                    } else {
                        $(".alm-resource-file-row").show();
                        $(".alm-resource-note-row").hide();
                    }
                    
                    if (attachmentId > 0) {
                        $("#alm-clear-media-btn").show();
                    }
                    
                    // Change button text and form title
                    $("#alm-add-resource-form h4").text("Edit Resource");
                    $("#alm-save-resource-btn").text("Update Resource");
                    
                    // Show form
                    $("#alm-add-resource-form").slideDown();
                    
                    // Scroll to form
                    $("html, body").animate({
                        scrollTop: $("#alm-add-resource-form").offset().top - 50
                    }, 500);
                });
                
                // Delete resource
                $(document).on("click", ".alm-delete-resource", function(e) {
                    e.preventDefault();
                    
                    if (!confirm("Are you sure you want to delete this resource?")) {
                        return;
                    }
                    
                    var $link = $(this);
                    var resourceType = $link.data("type");
                    var lessonId = $link.data("lesson-id");
                    
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "alm_delete_resource",
                            lesson_id: lessonId,
                            resource_type: resourceType,
                            nonce: alm_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Reload page to show updated resources
                                location.reload();
                            } else {
                                alert("Error: " + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error deleting resource. Please try again.");
                        }
                    });
                });
                
                // Update duration from chapters
                $("#alm-update-duration-btn").on("click", function() {
                    var lessonId = ' . (isset($_GET['id']) ? intval($_GET['id']) : 0) . ';
                    
                    if (!lessonId) {
                        alert("Error: Lesson ID not found.");
                        return;
                    }
                    
                    var $button = $(this);
                    $button.prop("disabled", true).text("Calculating...");
                    
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "alm_calculate_lesson_duration",
                            lesson_id: lessonId,
                            nonce: alm_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update the duration field
                                $("#duration").val(response.data.duration);
                                // Update the displayed duration
                                $("#duration").next(".description").text("(" + response.data.formatted + ")");
                                alert("Duration updated successfully to " + response.data.formatted + "!");
                            } else {
                                alert("Error: " + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error calculating duration. Please try again.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Update from Chapters");
                        }
                    });
                });
            });
        ');
        
        // Add CSS for drag and drop
        wp_add_inline_style('alm-admin-css', '
                .chapter-drag-handle {
                    cursor: move !important;
                    color: #666;
                    font-weight: bold;
                    user-select: none;
                }
                .chapter-drag-handle:hover {
                    color: #0073aa;
                }
                .chapter-placeholder {
                    background-color: #f0f0f1 !important;
                    border: 2px dashed #c3c4c7 !important;
                    height: 40px;
                }
                .ui-sortable-helper {
                    background-color: #fff !important;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
                }
                
                /* Icon button styling */
                .column-actions .button {
                    min-width: 32px;
                    height: 32px;
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .column-actions .dashicons {
                    font-size: 16px;
                    width: 16px;
                    height: 16px;
                    line-height: 1;
                }
                .column-actions .button:hover .dashicons {
                    color: #0073aa;
                }
            ');
    }
    
    /**
     * AJAX handler for updating chapter order
     */
    public function ajax_update_chapter_order() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $chapter_ids = $_POST['chapter_ids'];
        
        if (!is_array($chapter_ids)) {
            wp_send_json_error('Invalid chapter IDs');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('chapters');
        
        // Update each chapter's menu_order
        foreach ($chapter_ids as $order => $chapter_id) {
            $wpdb->update(
                $table_name,
                array('menu_order' => $order + 1),
                array('ID' => intval($chapter_id)),
                array('%d'),
                array('%d')
            );
        }
        
        wp_send_json_success('Chapter order updated');
    }
    
    /**
     * AJAX handler for updating lesson order in collections
     */
    public function ajax_update_lesson_order() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_ids = $_POST['lesson_ids'];
        
        if (!is_array($lesson_ids)) {
            wp_send_json_error('Invalid lesson IDs');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('lessons');
        
        // Ensure menu_order column exists
        $database->check_and_add_menu_order_column();
        
        // Update each lesson's menu_order
        foreach ($lesson_ids as $order => $lesson_id) {
            $wpdb->update(
                $table_name,
                array('menu_order' => $order), // Use 0-based order (0, 1, 2, etc.)
                array('ID' => intval($lesson_id)),
                array('%d'),
                array('%d')
            );
        }
        
        wp_send_json_success('Lesson order updated');
    }
    
    /**
     * AJAX handler for fetching Bunny.net metadata
     */
    public function ajax_fetch_bunny_metadata() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $bunny_url = sanitize_text_field($_POST['bunny_url']);
        
        if (empty($bunny_url)) {
            wp_send_json_error('Bunny URL is required');
        }
        
        $bunny_api = new ALM_Bunny_API();
        
        if (!$bunny_api->is_configured()) {
            wp_send_json_error('Bunny.net API not configured. Please set Library ID and API Key in settings.');
        }
        
        $video_info = $bunny_api->get_video_info($bunny_url);
        
        if (!$video_info) {
            wp_send_json_error('Could not fetch video metadata. Please check the URL and API configuration.');
        }
        
        wp_send_json_success($video_info);
    }
    
    /**
     * AJAX handler for fetching Vimeo metadata
     */
    public function ajax_fetch_vimeo_metadata() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $vimeo_id = sanitize_text_field($_POST['vimeo_id']);
        
        if (empty($vimeo_id)) {
            wp_send_json_error('Vimeo ID is required');
        }
        
        $vimeo_api = new ALM_Vimeo_API();
        $video_info = $vimeo_api->get_video_info($vimeo_id);
        
        if (!$video_info) {
            wp_send_json_error('Could not fetch video metadata. Please check the Vimeo ID.');
        }
        
        wp_send_json_success($video_info);
    }
    
    /**
     * AJAX handler for testing Bunny.net connection
     */
    public function ajax_test_bunny_connection() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $bunny_api = new ALM_Bunny_API();
        $result = $bunny_api->test_connection();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX: save AssemblyAI API key from settings (avoids password-field browser quirks).
     */
    public function ajax_save_assemblyai_key() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        $key = isset($_POST['key']) ? sanitize_text_field(trim(wp_unslash($_POST['key']))) : '';
        if ($key !== '') {
            update_option('alm_assemblyai_api_key', $key);
            wp_send_json_success(array('message' => __('API key saved successfully.', 'academy-lesson-manager')));
        }
        delete_option('alm_assemblyai_api_key');
        wp_send_json_success(array('message' => __('API key cleared.', 'academy-lesson-manager')));
    }

    /**
     * AJAX: verify AssemblyAI API key (list transcripts probe).
     */
    public function ajax_test_assemblyai() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }

        $api_key = '';
        if (isset($_POST['api_key'])) {
            $api_key = sanitize_text_field(trim(wp_unslash($_POST['api_key'])));
        }
        if ($api_key === '') {
            $api_key = get_option('alm_assemblyai_api_key', '');
        }
        if ($api_key === '') {
            wp_send_json_error(array('message' => __('No API key provided.', 'academy-lesson-manager')));
        }

        $response = wp_remote_get(
            'https://api.assemblyai.com/v2/transcript?limit=1',
            array(
                'timeout' => 10,
                'headers' => array('authorization' => $api_key),
            )
        );

        if (is_wp_error($response)) {
            wp_send_json_error(
                array(
                    'message' => sprintf(
                        /* translators: %s: WP HTTP error message */
                        __('Connection error: %s', 'academy-lesson-manager'),
                        $response->get_error_message()
                    ),
                )
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code === 200) {
            wp_send_json_success(array('message' => __('Connected successfully ✓', 'academy-lesson-manager')));
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $err  = (is_array($body) && isset($body['error'])) ? $body['error'] : 'HTTP ' . $code;
        wp_send_json_error(
            array(
                'message' => sprintf(
                    /* translators: %s: API error detail or HTTP code */
                    __('API error: %s', 'academy-lesson-manager'),
                    $err
                ),
            )
        );
    }
    
    /**
     * AJAX handler for debugging Bunny.net configuration
     */
    public function ajax_debug_bunny_config() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $bunny_api = new ALM_Bunny_API();
        $debug_info = $bunny_api->debug_request();
        
        wp_send_json_success($debug_info);
    }
    
    /**
     * AJAX handler for adding a resource
     */
    public function ajax_add_resource() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_id = intval($_POST['lesson_id']);
        $resource_type = sanitize_text_field($_POST['resource_type']);
        $resource_url = sanitize_text_field($_POST['resource_url']);
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        $old_resource_type = isset($_POST['old_resource_type']) ? sanitize_text_field($_POST['old_resource_type']) : '';
        
        if (empty($lesson_id) || empty($resource_type) || empty($resource_url)) {
            wp_send_json_error('All fields are required');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('lessons');
        
        // Get current resources
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT resources FROM {$table_name} WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            wp_send_json_error('Lesson not found');
        }
        
        // Unserialize resources
        $resources = maybe_unserialize($lesson->resources);
        if (!is_array($resources)) {
            $resources = array();
        }
        
        // If editing, remove the old resource first
        if (!empty($old_resource_type) && isset($resources[$old_resource_type])) {
            unset($resources[$old_resource_type]);
        }
        
        // For certain resource types, support multiple entries (jam, ireal, etc.)
        $multi_resource_types = array('jam', 'ireal', 'sheet_music', 'zip');
        
        if (in_array($resource_type, $multi_resource_types)) {
            // Find all existing resources of this type to determine the count
            $count = 1;
            foreach ($resources as $key => $value) {
                if (strpos($key, $resource_type) === 0) {
                    $count++;
                }
            }
            
            // If there's already one of this type, append the count
            $resource_key = ($count > 1) ? $resource_type . $count : $resource_type;
        } else {
            $resource_key = $resource_type;
        }
        
        // Get resource label if provided
        $resource_label = isset($_POST['resource_label']) ? sanitize_text_field(substr($_POST['resource_label'], 0, 30)) : '';
        
        // Store both URL and attachment ID if provided
        if ($attachment_id > 0) {
            $resources[$resource_key] = array(
                'url' => $resource_url,
                'attachment_id' => $attachment_id,
                'label' => $resource_label
            );
        } else {
            $resources[$resource_key] = array(
                'url' => $resource_url,
                'label' => $resource_label
            );
        }
        
        // Update database
        $result = $wpdb->update(
            $table_name,
            array('resources' => serialize($resources)),
            array('ID' => $lesson_id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Resource added successfully',
                'resources' => ALM_Helpers::format_serialized_resources(serialize($resources))
            ));
        } else {
            wp_send_json_error('Failed to add resource');
        }
    }
    
    /**
     * AJAX handler for deleting a resource
     */
    public function ajax_delete_resource() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_id = intval($_POST['lesson_id']);
        $resource_type = sanitize_text_field($_POST['resource_type']);
        
        if (empty($lesson_id) || empty($resource_type)) {
            wp_send_json_error('Invalid parameters');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('lessons');
        
        // Get current resources
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT resources FROM {$table_name} WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            wp_send_json_error('Lesson not found');
        }
        
        // Unserialize resources
        $resources = maybe_unserialize($lesson->resources);
        if (!is_array($resources)) {
            $resources = array();
        }
        
        // Remove resource
        unset($resources[$resource_type]);
        
        // Update database
        $result = $wpdb->update(
            $table_name,
            array('resources' => serialize($resources)),
            array('ID' => $lesson_id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Resource deleted successfully',
                'resources' => ALM_Helpers::format_serialized_resources(serialize($resources))
            ));
        } else {
            wp_send_json_error('Failed to delete resource');
        }
    }
    
    /**
     * AJAX handler for calculating lesson duration from chapters
     */
    public function ajax_calculate_lesson_duration() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_id = intval($_POST['lesson_id']);
        
        if (empty($lesson_id)) {
            wp_send_json_error('Invalid lesson ID');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $chapters_table = $database->get_table_name('chapters');
        
        // Get all chapters for this lesson
        $chapters = $wpdb->get_results($wpdb->prepare(
            "SELECT duration FROM {$chapters_table} WHERE lesson_id = %d",
            $lesson_id
        ));
        
        if (empty($chapters)) {
            wp_send_json_error('No chapters found for this lesson');
        }
        
        // Calculate total duration
        $total_duration = 0;
        foreach ($chapters as $chapter) {
            $total_duration += intval($chapter->duration);
        }
        
        // Format the duration
        $formatted_duration = ALM_Helpers::format_duration($total_duration);
        
        wp_send_json_success(array(
            'duration' => $total_duration,
            'formatted' => $formatted_duration,
            'chapters_count' => count($chapters)
        ));
    }
    
    /**
     * AJAX handler for calculating all chapter durations from Bunny API
     */
    public function ajax_calculate_all_bunny_durations() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_id = intval($_POST['lesson_id']);
        
        if (empty($lesson_id)) {
            wp_send_json_error('Invalid lesson ID');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $chapters_table = $database->get_table_name('chapters');
        $lessons_table = $database->get_table_name('lessons');
        
        // Get all chapters for this lesson that have Bunny URLs
        $chapters = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, bunny_url FROM {$chapters_table} WHERE lesson_id = %d AND bunny_url != '' AND bunny_url IS NOT NULL",
            $lesson_id
        ));
        
        if (empty($chapters)) {
            wp_send_json_error('No chapters with Bunny URLs found for this lesson');
        }
        
        $bunny_api = new ALM_Bunny_API();
        
        if (!$bunny_api->is_configured()) {
            wp_send_json_error('Bunny.net API not configured. Please set Library ID and API Key in settings.');
        }
        
        $updated_count = 0;
        $total_duration = 0;
        $errors = array();
        $debug_info = array();
        
        foreach ($chapters as $chapter) {
            // Extract video ID for debugging
            $video_id = $bunny_api->extract_video_id_from_url($chapter->bunny_url);
            
            if (!$video_id) {
                $errors[] = "Chapter ID {$chapter->ID}: Could not extract video ID from URL: {$chapter->bunny_url}";
                $debug_info[] = "Chapter {$chapter->ID}: Failed to extract video ID from URL";
                continue;
            }
            
            $debug_info[] = "Chapter {$chapter->ID}: Extracted video ID: {$video_id}";
            
            // Get video metadata to check for errors
            $metadata = $bunny_api->get_video_metadata($video_id);
            
            if ($metadata === false) {
                $errors[] = "Chapter ID {$chapter->ID}: Failed to fetch metadata for video ID: {$video_id}";
                $debug_info[] = "Chapter {$chapter->ID}: API request failed for video ID {$video_id}";
                continue;
            }
            
            if (!isset($metadata['length'])) {
                $errors[] = "Chapter ID {$chapter->ID}: Video metadata missing 'length' field. Video ID: {$video_id}";
                $debug_info[] = "Chapter {$chapter->ID}: Metadata missing length field";
                continue;
            }
            
            $duration = intval($metadata['length']);
            
            if ($duration > 0) {
                // Update the chapter duration
                $wpdb->update(
                    $chapters_table,
                    array('duration' => $duration),
                    array('ID' => $chapter->ID),
                    array('%d'),
                    array('%d')
                );
                
                $updated_count++;
                $total_duration += $duration;
                $debug_info[] = "Chapter {$chapter->ID}: Updated duration to {$duration} seconds";
            } else {
                $errors[] = "Chapter ID {$chapter->ID}: Duration is 0 or invalid. Video ID: {$video_id}";
                $debug_info[] = "Chapter {$chapter->ID}: Duration is 0";
            }
        }
        
        // Update the lesson's total duration
        if ($total_duration > 0) {
            $wpdb->update(
                $lessons_table,
                array('duration' => $total_duration),
                array('ID' => $lesson_id),
                array('%d'),
                array('%d')
            );
        }
        
        $response_data = array(
            'updated' => $updated_count,
            'total_duration' => $total_duration,
            'chapters_count' => count($chapters),
            'errors_count' => count($errors)
        );
        
        // Include errors and debug info if there were any issues
        if (!empty($errors)) {
            $response_data['errors'] = $errors;
            $response_data['debug_info'] = $debug_info;
        }
        
        // If no chapters were updated and there were errors, send as error
        if ($updated_count === 0 && !empty($errors)) {
            wp_send_json_error(array(
                'message' => 'Failed to calculate durations. ' . implode(' ', array_slice($errors, 0, 3)),
                'errors' => $errors,
                'debug_info' => $debug_info
            ));
        }
        
        wp_send_json_success($response_data);
    }
    
    /**
     * AJAX handler for calculating all chapter durations from Bunny API for a collection
     */
    public function ajax_calculate_collection_bunny_durations() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $collection_id = intval($_POST['collection_id']);
        
        if (empty($collection_id)) {
            wp_send_json_error('Invalid collection ID');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $lessons_table = $database->get_table_name('lessons');
        $chapters_table = $database->get_table_name('chapters');
        
        // Get all lessons in this collection
        $lessons = $wpdb->get_results($wpdb->prepare(
            "SELECT ID FROM {$lessons_table} WHERE collection_id = %d",
            $collection_id
        ));
        
        if (empty($lessons)) {
            wp_send_json_error('No lessons found in this collection');
        }
        
        $bunny_api = new ALM_Bunny_API();
        
        if (!$bunny_api->is_configured()) {
            wp_send_json_error('Bunny.net API not configured. Please set Library ID and API Key in settings.');
        }
        
        $lessons_processed = 0;
        $lessons_updated = 0;
        $chapters_updated = 0;
        $total_duration = 0;
        
        foreach ($lessons as $lesson) {
            $lesson_id = $lesson->ID;
            
            // Get all chapters for this lesson
            $chapters = $wpdb->get_results($wpdb->prepare(
                "SELECT ID, bunny_url FROM {$chapters_table} WHERE lesson_id = %d",
                $lesson_id
            ));
            
            if (empty($chapters)) {
                $lessons_processed++;
                continue;
            }
            
            // Find the original event if chapters don't have bunny_url
            $event_id = null;
            $event_bunny_url = null;
            $has_bunny_in_chapters = false;
            
            foreach ($chapters as $chapter) {
                if (!empty($chapter->bunny_url)) {
                    $has_bunny_in_chapters = true;
                    break;
                }
            }
            
            // If no chapters have bunny_url, try to get it from the original event
            if (!$has_bunny_in_chapters) {
                // Find event that was converted to this lesson
                $event_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_converted_to_alm_lesson_id' AND meta_value = %d LIMIT 1",
                    $lesson_id
                ));
                
                if ($event_id) {
                    $event_bunny_url = get_post_meta($event_id, 'je_event_bunny_url', true);
                    
                    // If event has bunny_url, update the first chapter that doesn't have one
                    if (!empty($event_bunny_url)) {
                        foreach ($chapters as $chapter) {
                            if (empty($chapter->bunny_url)) {
                                $wpdb->update(
                                    $chapters_table,
                                    array('bunny_url' => sanitize_text_field($event_bunny_url)),
                                    array('ID' => $chapter->ID),
                                    array('%s'),
                                    array('%d')
                                );
                                $chapter->bunny_url = $event_bunny_url;
                                break; // Only update first chapter
                            }
                        }
                    }
                }
            }
            
            $lesson_duration = 0;
            $lesson_chapters_updated = 0;
            
            foreach ($chapters as $chapter) {
                // Skip if chapter doesn't have bunny_url
                if (empty($chapter->bunny_url)) {
                    continue;
                }
                
                $duration = $bunny_api->get_video_duration($chapter->bunny_url);
                
                if ($duration !== false && $duration > 0) {
                    // Update the chapter duration
                    $wpdb->update(
                        $chapters_table,
                        array('duration' => $duration),
                        array('ID' => $chapter->ID),
                        array('%d'),
                        array('%d')
                    );
                    
                    $lesson_chapters_updated++;
                    $lesson_duration += $duration;
                    $chapters_updated++;
                }
            }
            
            // Update the lesson's total duration
            if ($lesson_duration > 0) {
                $wpdb->update(
                    $lessons_table,
                    array('duration' => $lesson_duration),
                    array('ID' => $lesson_id),
                    array('%d'),
                    array('%d')
                );
                $lessons_updated++;
                $total_duration += $lesson_duration;
            }
            
            $lessons_processed++;
        }
        
        wp_send_json_success(array(
            'lessons_processed' => $lessons_processed,
            'lessons_updated' => $lessons_updated,
            'chapters_updated' => $chapters_updated,
            'total_duration' => $total_duration
        ));
    }
    
    /**
     * AJAX handler for calculating all chapter durations from Vimeo API for a collection
     */
    public function ajax_calculate_collection_vimeo_durations() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $collection_id = intval($_POST['collection_id']);
        
        if (empty($collection_id)) {
            wp_send_json_error('Invalid collection ID');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $lessons_table = $database->get_table_name('lessons');
        $chapters_table = $database->get_table_name('chapters');
        
        // Get all lessons in this collection
        $lessons = $wpdb->get_results($wpdb->prepare(
            "SELECT ID FROM {$lessons_table} WHERE collection_id = %d",
            $collection_id
        ));
        
        if (empty($lessons)) {
            wp_send_json_error('No lessons found in this collection');
        }
        
        $vimeo_api = new ALM_Vimeo_API();
        
        $lessons_processed = 0;
        $lessons_updated = 0;
        $chapters_updated = 0;
        $total_duration = 0;
        $debug_info = array();
        
        foreach ($lessons as $lesson) {
            $lesson_id = $lesson->ID;
            $debug_info[] = "Processing lesson ID: {$lesson_id}";
            
            // Get all chapters for this lesson that have Vimeo IDs
            $chapters = $wpdb->get_results($wpdb->prepare(
                "SELECT ID, vimeo_id FROM {$chapters_table} WHERE lesson_id = %d AND vimeo_id > 0 AND vimeo_id IS NOT NULL",
                $lesson_id
            ));
            
            if (empty($chapters)) {
                $debug_info[] = "  - No chapters with Vimeo IDs found";
                $lessons_processed++;
                continue;
            }
            
            $lesson_duration = 0;
            $lesson_chapters_updated = 0;
            
            foreach ($chapters as $chapter) {
                $metadata = $vimeo_api->get_video_metadata($chapter->vimeo_id);
                $duration = false;
                
                if ($metadata !== false && isset($metadata['duration'])) {
                    $duration = intval($metadata['duration']);
                }
                
                if ($duration !== false && $duration > 0) {
                    // Update the chapter duration
                    $wpdb->update(
                        $chapters_table,
                        array('duration' => $duration),
                        array('ID' => $chapter->ID),
                        array('%d'),
                        array('%d')
                    );
                    
                    $lesson_chapters_updated++;
                    $lesson_duration += $duration;
                    $chapters_updated++;
                }
            }
            
            // Update the lesson's total duration
            if ($lesson_duration > 0) {
                $wpdb->update(
                    $lessons_table,
                    array('duration' => $lesson_duration),
                    array('ID' => $lesson_id),
                    array('%d'),
                    array('%d')
                );
                $lessons_updated++;
                $total_duration += $lesson_duration;
                $debug_info[] = "  - Updated lesson duration: " . ALM_Helpers::format_duration($lesson_duration) . " ({$lesson_chapters_updated} chapters)";
            } else {
                $debug_info[] = "  - No valid durations found for chapters";
            }
            
            $lessons_processed++;
        }
        
        // Log debug info
        error_log("ALM Collection Vimeo Duration Debug: " . implode("\n", $debug_info));
        
        wp_send_json_success(array(
            'lessons_processed' => $lessons_processed,
            'lessons_updated' => $lessons_updated,
            'chapters_updated' => $chapters_updated,
            'total_duration' => $total_duration,
            'debug_info' => implode("\n", $debug_info)
        ));
    }
    
    /**
     * AJAX handler for syncing all lessons in a collection
     */
    public function ajax_sync_collection_lessons() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $collection_id = intval($_POST['collection_id']);
        
        if (empty($collection_id)) {
            wp_send_json_error('Invalid collection ID');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $lessons_table = $database->get_table_name('lessons');
        
        // Get all lessons in this collection
        $lessons = $wpdb->get_results($wpdb->prepare(
            "SELECT ID FROM {$lessons_table} WHERE collection_id = %d",
            $collection_id
        ));
        
        if (empty($lessons)) {
            wp_send_json_error('No lessons found in this collection');
        }
        
        $sync = new ALM_Post_Sync();
        
        $lessons_processed = 0;
        $lessons_synced = 0;
        $lessons_failed = 0;
        
        foreach ($lessons as $lesson) {
            $lesson_id = $lesson->ID;
            $result = $sync->sync_lesson_to_post($lesson_id);
            
            if ($result !== false) {
                $lessons_synced++;
            } else {
                $lessons_failed++;
            }
            
            $lessons_processed++;
        }
        
        wp_send_json_success(array(
            'lessons_processed' => $lessons_processed,
            'lessons_synced' => $lessons_synced,
            'lessons_failed' => $lessons_failed
        ));
    }
    
    /**
     * AJAX handler for calculating all chapter durations from Vimeo API
     */
    public function ajax_calculate_all_vimeo_durations() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_id = intval($_POST['lesson_id']);
        
        if (empty($lesson_id)) {
            wp_send_json_error('Invalid lesson ID');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $chapters_table = $database->get_table_name('chapters');
        $lessons_table = $database->get_table_name('lessons');
        
        // Get all chapters for this lesson that have Vimeo IDs
        $chapters = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, vimeo_id FROM {$chapters_table} WHERE lesson_id = %d AND vimeo_id > 0 AND vimeo_id IS NOT NULL",
            $lesson_id
        ));
        
        if (empty($chapters)) {
            wp_send_json_error('No chapters with Vimeo IDs found for this lesson');
        }
        
        $vimeo_api = new ALM_Vimeo_API();
        
        $updated_count = 0;
        $total_duration = 0;
        $debug_info = array();
        
        foreach ($chapters as $chapter) {
            $debug_info[] = "Chapter ID: {$chapter->ID}, Vimeo ID: {$chapter->vimeo_id}";
            
            // Get full metadata to see what we're getting
            $metadata = $vimeo_api->get_video_metadata($chapter->vimeo_id);
            $duration = false;
            
            if ($metadata !== false && isset($metadata['duration'])) {
                $duration = intval($metadata['duration']);
                $debug_info[] = "  - Metadata found. Duration: {$duration}, Status: " . (isset($metadata['status']) ? $metadata['status'] : 'unknown');
                
                // Check if video is private/unavailable
                if (isset($metadata['status']) && $metadata['status'] !== 'available') {
                    $debug_info[] = "  - Video status: {$metadata['status']} (may require authentication)";
                }
            } else {
                $debug_info[] = "  - Metadata fetch failed or duration missing";
                if ($metadata !== false) {
                    $debug_info[] = "  - Available fields: " . implode(', ', array_keys($metadata));
                }
            }
            
            if ($duration !== false && $duration > 0) {
                // Update the chapter duration
                $update_result = $wpdb->update(
                    $chapters_table,
                    array('duration' => $duration),
                    array('ID' => $chapter->ID),
                    array('%d'),
                    array('%d')
                );
                
                $debug_info[] = "  - Update result: " . ($update_result !== false ? "success" : "failed");
                
                $updated_count++;
                $total_duration += $duration;
            } else {
                $debug_info[] = "  - Update skipped: duration is " . ($duration === false ? "false/error" : "0 or invalid");
            }
        }
        
        // Log debug info
        error_log("ALM Vimeo Duration Debug: " . implode("\n", $debug_info));
        
        // Update the lesson's total duration
        if ($total_duration > 0) {
            $wpdb->update(
                $lessons_table,
                array('duration' => $total_duration),
                array('ID' => $lesson_id),
                array('%d'),
                array('%d')
            );
        }
        
        wp_send_json_success(array(
            'updated' => $updated_count,
            'total_duration' => $total_duration,
            'chapters_count' => count($chapters)
        ));
    }
    
    /**
     * Handle WordPress post save (reverse sync)
     */
    public function handle_post_save($post_id) {
        // Skip autosaves and revisions
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        
        $sync = new ALM_Post_Sync();
        
        // Sync based on post type
        if ($post->post_type === 'lesson') {
            $sync->sync_post_to_lesson($post_id);
        } elseif ($post->post_type === 'lesson-collection') {
            $sync->sync_post_to_collection($post_id);
        }
    }
    
    /**
     * Handle WordPress post deletion (reverse sync)
     */
    public function handle_post_delete($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        
        // Only handle our post types
        if (!in_array($post->post_type, array('lesson', 'lesson-collection'))) {
            return;
        }
        
        // Get ALM ID from ACF
        $alm_id = get_field('alm_lesson_id', $post_id) ?: get_field('alm_collection_id', $post_id);
        
        if (!$alm_id) {
            return;
        }
        
        // Delete from ALM table
        global $wpdb;
        $database = new ALM_Database();
        
        if ($post->post_type === 'lesson') {
            $table_name = $database->get_table_name('lessons');
        } else {
            $table_name = $database->get_table_name('collections');
        }
        
        $wpdb->delete($table_name, array('ID' => $alm_id));
    }
    
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Include required files first
        $this->include_files();
        
        // Create database tables
        $database = new ALM_Database();
        $database->create_tables();
        $database->check_and_add_assemblyai_transcript_id_column();

        // Set activation flag
        update_option('alm_plugin_activated', true);
        update_option('alm_version', ALM_VERSION);
        update_option( 'alm_db_version', ALM_VERSION );

        // AssemblyAI API key: set via wp-config.php constant ALM_ASSEMBLYAI_API_KEY (recommended), not committed to the repo
        if ( ! get_option( 'alm_assemblyai_api_key' ) && defined( 'ALM_ASSEMBLYAI_API_KEY' ) && ALM_ASSEMBLYAI_API_KEY ) {
            update_option( 'alm_assemblyai_api_key', ALM_ASSEMBLYAI_API_KEY );
        }
    }

    /**
     * If the API key option is empty, copy from ALM_ASSEMBLYAI_API_KEY when defined (e.g. in wp-config.php).
     */
    private function maybe_bootstrap_assemblyai_api_key() {
        if ( get_option( 'alm_assemblyai_api_key' ) ) {
            return;
        }
        if ( defined( 'ALM_ASSEMBLYAI_API_KEY' ) && ALM_ASSEMBLYAI_API_KEY ) {
            update_option( 'alm_assemblyai_api_key', ALM_ASSEMBLYAI_API_KEY );
        }
    }
    
    /**
     * AJAX handler for updating favorites order
     */
    public function ajax_update_favorites_order() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_favorites_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $order = isset($_POST['order']) ? array_map('intval', $_POST['order']) : array();
        $table_type = isset($_POST['table_type']) ? sanitize_text_field($_POST['table_type']) : '';
        
        if (empty($order)) {
            wp_send_json_error('No order provided');
        }
        
        global $wpdb;
        
        // Determine table name
        if ($table_type === 'jph_lesson_favorites') {
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        } elseif ($table_type === 'jf_favorites') {
            $table_name = $wpdb->prefix . 'jf_favorites';
        } else {
            // Default fallback
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        }
        
        // Check if display_order column exists, if not we'll skip the order save
        // For now, just return success since we don't have the column yet
        // The order will be maintained by the frontend DOM order
        // TODO: Add display_order column to favorites tables if needed
        
        wp_send_json_success('Order updated successfully');
    }
    
    /**
     * AJAX handler for deleting a favorite
     */
    public function ajax_delete_favorite() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_favorites_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $favorite_id = isset($_POST['favorite_id']) ? intval($_POST['favorite_id']) : 0;
        $table_type = isset($_POST['table_type']) ? sanitize_text_field($_POST['table_type']) : '';
        
        if (empty($favorite_id)) {
            wp_send_json_error('No favorite ID provided');
        }
        
        global $wpdb;
        
        // Determine table name
        if ($table_type === 'jph_lesson_favorites') {
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        } elseif ($table_type === 'jf_favorites') {
            $table_name = $wpdb->prefix . 'jf_favorites';
        } else {
            // Default fallback
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        }
        
        // Check if user owns this favorite
        $user_id = get_current_user_id();
        $favorite = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d AND user_id = %d",
            $favorite_id,
            $user_id
        ));
        
        if (!$favorite) {
            wp_send_json_error('Favorite not found or access denied');
        }
        
        // Delete the favorite
        $deleted = $wpdb->delete(
            $table_name,
            array('id' => $favorite_id, 'user_id' => $user_id),
            array('%d', '%d')
        );
        
        if ($deleted) {
            wp_send_json_success('Favorite deleted successfully');
        } else {
            wp_send_json_error('Failed to delete favorite');
        }
    }
    
    /**
     * AJAX handler for deleting all favorites for the current user
     */
    public function ajax_delete_all_favorites() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_favorites_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $table_type = isset($_POST['table_type']) ? sanitize_text_field($_POST['table_type']) : '';
        
        global $wpdb;
        
        // Determine table name based on table_type
        if ($table_type === 'jph') {
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        } elseif ($table_type === 'jf') {
            $table_name = $wpdb->prefix . 'jf_favorites';
        } else {
            // Default fallback - try jph first
            $jph_table = $wpdb->prefix . 'jph_lesson_favorites';
            $jf_table = $wpdb->prefix . 'jf_favorites';
            
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $jph_table));
            if ($exists) {
                $table_name = $jph_table;
            } else {
                $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $jf_table));
                if ($exists) {
                    $table_name = $jf_table;
                } else {
                    wp_send_json_error('Favorites table not found');
                    return;
                }
            }
        }
        
        // Get current user ID
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
            return;
        }
        
        // Delete all favorites for this user
        // For jf table, we set is_active to 0 instead of deleting
        if ($table_type === 'jf') {
            $deleted = $wpdb->update(
                $table_name,
                array('is_active' => 0),
                array('user_id' => $user_id),
                array('%d'),
                array('%d')
            );
        } else {
            // For jph table, delete the records
            $deleted = $wpdb->delete(
                $table_name,
                array('user_id' => $user_id),
                array('%d')
            );
        }
        
        if ($deleted !== false) {
            wp_send_json_success(array(
                'message' => 'All favorites deleted successfully',
                'deleted_count' => $deleted
            ));
        } else {
            wp_send_json_error('Failed to delete favorites');
        }
    }
    
    /**
     * AJAX handler for toggling resource favorites
     */
    public function ajax_toggle_resource_favorite() {
        try {
            // Check if user is logged in first
            $user_id = get_current_user_id();
            if (!$user_id) {
                wp_send_json_error('User not logged in');
                return;
            }
            
            // Verify nonce
            if (!isset($_POST['nonce'])) {
                wp_send_json_error('Security check failed: nonce not set');
                return;
            }
            
            $nonce_check = wp_verify_nonce($_POST['nonce'], 'alm_resource_favorite_nonce');
            if (!$nonce_check) {
                wp_send_json_error('Security check failed');
                return;
            }
            
            if (!isset($_POST['resource_url'])) {
                wp_send_json_error('Resource URL is required');
                return;
            }
            
            $resource_url = sanitize_text_field($_POST['resource_url']);
            $is_favorite = isset($_POST['is_favorite']) ? intval($_POST['is_favorite']) : 0;
            
            global $wpdb;
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        
        if ($is_favorite) {
            // Add favorite
            $resource_name = isset($_POST['resource_name']) ? sanitize_text_field($_POST['resource_name']) : 'Resource';
            $resource_link = isset($_POST['resource_link']) ? sanitize_text_field($_POST['resource_link']) : '';
            $resource_type = isset($_POST['resource_type']) ? sanitize_text_field($_POST['resource_type']) : '';
            
            // Check if already favorited
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE user_id = %d AND url = %s",
                $user_id,
                $resource_url
            ));
            
            if ($existing) {
                wp_send_json_success(array('id' => $existing, 'message' => 'Already favorited'));
                return;
            }
            
            // Insert new favorite - build array dynamically to handle optional fields
            $insert_data = array(
                'user_id' => $user_id,
                'title' => $resource_name,
                'url' => $resource_url,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );
            $insert_format = array('%d', '%s', '%s', '%s', '%s');
            
            // Add resource_link if provided
            if (!empty($resource_link)) {
                $insert_data['resource_link'] = $resource_link;
                $insert_format[] = '%s';
            }
            
            // Add resource_type if provided
            if (!empty($resource_type)) {
                $insert_data['resource_type'] = $resource_type;
                $insert_format[] = '%s';
            }
            
            // Add category (try to set it, but it might be optional)
            $insert_data['category'] = 'lesson';
            $insert_format[] = '%s';
            
            $result = $wpdb->insert($table_name, $insert_data, $insert_format);
            
            if ($result !== false) {
                $favorite_id = $wpdb->insert_id;
                wp_send_json_success(array('id' => $favorite_id, 'message' => 'Added to favorites'));
            } else {
                $error = $wpdb->last_error ? $wpdb->last_error : 'Database insert failed';
                wp_send_json_error($error);
            }
        } else {
            // Remove favorite
            $deleted = $wpdb->delete(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'url' => $resource_url
                ),
                array('%d', '%s')
            );
            
            if ($deleted) {
                wp_send_json_success(array('message' => 'Removed from favorites'));
            } else {
                wp_send_json_error('Failed to remove favorite');
            }
        }
        } catch (Exception $e) {
            wp_send_json_error('An error occurred: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for quick pathway addition
     */
    public function ajax_quick_add_pathway() {
        check_ajax_referer('alm_quick_add_pathway', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        $pathway = isset($_POST['pathway']) ? sanitize_key($_POST['pathway']) : '';
        $rank = isset($_POST['rank']) ? intval($_POST['rank']) : 1;
        
        // Debug logging
        error_log('ALM Quick Add Pathway Debug - Received: lesson_id=' . $lesson_id . ', pathway=' . $pathway . ', rank=' . $rank);
        error_log('ALM Quick Add Pathway Debug - POST data: ' . print_r($_POST, true));
        
        if (empty($lesson_id) || empty($pathway) || $rank < 1 || $rank > 5) {
            error_log('ALM Quick Add Pathway Debug - Invalid parameters');
            wp_send_json_error('Invalid parameters: lesson_id=' . $lesson_id . ', pathway=' . $pathway . ', rank=' . $rank);
            return;
        }
        
        $database = new ALM_Database();
        $pathways_table = $database->get_table_name('lesson_pathways');
        
        global $wpdb;
        
        // Verify pathway exists in allowed pathways (check against option)
        $allowed_pathways = ALM_Admin_Settings::get_pathways();
        if (!isset($allowed_pathways[$pathway])) {
            error_log('ALM Quick Add Pathway Debug - Pathway not in allowed list: ' . $pathway);
            wp_send_json_error('Invalid pathway: ' . $pathway . '. Please add it via Settings > AI Settings first.');
            return;
        }
        
        // Ensure pathway column is VARCHAR (not ENUM) - convert if needed
        $column_info = $wpdb->get_row($wpdb->prepare(
            "SHOW COLUMNS FROM {$pathways_table} WHERE Field = %s",
            'pathway'
        ));
        
        if ($column_info && strpos($column_info->Type, 'enum') !== false) {
            error_log('ALM Quick Add Pathway Debug - Converting ENUM to VARCHAR for dynamic pathways');
            // Convert ENUM to VARCHAR to support dynamic pathways
            $wpdb->query("ALTER TABLE {$pathways_table} MODIFY COLUMN pathway VARCHAR(100) NOT NULL");
            error_log('ALM Quick Add Pathway Debug - Conversion complete');
        }
        
        // Check if pathway already exists for this lesson
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$pathways_table} WHERE lesson_id = %d AND pathway = %s",
            $lesson_id,
            $pathway
        ));
        
        error_log('ALM Quick Add Pathway Debug - Existing pathway ID: ' . ($existing ? $existing : 'none'));
        
        if ($existing) {
            // Update existing
            $result = $wpdb->update(
                $pathways_table,
                array('pathway_rank' => $rank),
                array('ID' => $existing),
                array('%d'),
                array('%d')
            );
            error_log('ALM Quick Add Pathway Debug - Update result: ' . ($result !== false ? 'SUCCESS' : 'FAILED') . ', rows affected: ' . $result);
        } else {
            // Insert new
            $result = $wpdb->insert(
                $pathways_table,
                array(
                    'lesson_id' => $lesson_id,
                    'pathway' => $pathway,
                    'pathway_rank' => $rank
                ),
                array('%d', '%s', '%d')
            );
            error_log('ALM Quick Add Pathway Debug - Insert result: ' . ($result !== false ? 'SUCCESS' : 'FAILED') . ', insert ID: ' . $wpdb->insert_id);
            error_log('ALM Quick Add Pathway Debug - Last error: ' . $wpdb->last_error);
            error_log('ALM Quick Add Pathway Debug - Last query: ' . $wpdb->last_query);
        }
        
        if ($result !== false) {
            // Verify what was saved
            $saved = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$pathways_table} WHERE lesson_id = %d AND pathway = %s",
                $lesson_id,
                $pathway
            ));
            error_log('ALM Quick Add Pathway Debug - Verified saved data: ' . print_r($saved, true));
            
            wp_send_json_success(array(
                'message' => 'Pathway added successfully',
                'lesson_id' => $lesson_id,
                'pathway' => $pathway,
                'rank' => $rank
            ));
        } else {
            error_log('ALM Quick Add Pathway Debug - Save failed, last error: ' . $wpdb->last_error);
            wp_send_json_error('Failed to save pathway: ' . $wpdb->last_error);
        }
    }
    
    /**
     * AJAX handler for saving column visibility preferences
     */
    public function ajax_save_column_preferences() {
        check_ajax_referer('alm_column_visibility', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $user_id = get_current_user_id();
        $hidden_columns = isset($_POST['hidden_columns']) ? (array) $_POST['hidden_columns'] : array();
        
        // Sanitize column IDs
        $hidden_columns = array_map('sanitize_key', $hidden_columns);
        
        update_user_meta($user_id, 'alm_lesson_list_hidden_columns', $hidden_columns);
        
        wp_send_json_success(array(
            'message' => 'Column preferences saved successfully',
            'hidden_columns' => $hidden_columns
        ));
    }
    
    /**
     * Register cron jobs
     */
    private function register_cron_jobs() {
        // Schedule daily check for Essentials selections
        if (!wp_next_scheduled('alm_check_essentials_selections')) {
            wp_schedule_event(time(), 'daily', 'alm_check_essentials_selections');
        }
        
        add_action('alm_check_essentials_selections', array($this, 'cron_check_essentials_selections'));
    }
    
    /**
     * Cron job to check and grant Essentials selections
     */
    public function cron_check_essentials_selections() {
        if (class_exists('ALM_Essentials_Library')) {
            $library = new ALM_Essentials_Library();
            $library->process_all_members();
        }
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
     * AJAX handler for adding lesson to library
     */
    public function ajax_add_to_library() {
        check_ajax_referer('alm_library_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in.'));
            return;
        }
        
        $user_id = get_current_user_id();
        
        // Check if user is Essentials member
        if (!$this->is_essentials_member($user_id)) {
            wp_send_json_error(array('message' => 'This feature is available for Essentials members only.'));
            return;
        }
        
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        
        if (!$lesson_id) {
            wp_send_json_error(array('message' => 'Invalid lesson ID.'));
            return;
        }
        
        if (!class_exists('ALM_Essentials_Library')) {
            wp_send_json_error(array('message' => 'Library system not available.'));
            return;
        }
        
        $library = new ALM_Essentials_Library();
        $result = $library->add_lesson_to_library($user_id, $lesson_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
            return;
        }
        
        // Get updated available count
        $available = $library->get_available_selections($user_id);
        
        wp_send_json_success(array(
            'message' => 'Lesson added to your library!',
            'available_count' => $available
        ));
    }
    
    /**
     * AJAX handler for getting library status
     */
    public function ajax_get_library_status() {
        check_ajax_referer('alm_library_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in.'));
            return;
        }
        
        $user_id = get_current_user_id();
        
        if (!class_exists('ALM_Essentials_Library')) {
            wp_send_json_error(array('message' => 'Library system not available.'));
            return;
        }
        
        $library = new ALM_Essentials_Library();
        $available = $library->get_available_selections($user_id);
        $next_grant = $library->get_next_grant_date($user_id);
        
        wp_send_json_success(array(
            'available_count' => $available,
            'next_grant_date' => $next_grant
        ));
    }
    
    /**
     * AJAX handler for setting introduction as sample
     * Delegates to ALM_Admin_Lesson_Samples class
     */
    public function ajax_set_intro_sample() {
        $samples_manager = new ALM_Admin_Lesson_Samples();
        $samples_manager->ajax_set_intro_sample();
    }
    
    /**
     * AJAX handler for setting shortest chapter as sample
     * Delegates to ALM_Admin_Lesson_Samples class
     */
    public function ajax_set_shortest_sample() {
        $samples_manager = new ALM_Admin_Lesson_Samples();
        $samples_manager->ajax_set_shortest_sample();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
        delete_option('alm_plugin_activated');
        
        // Clear scheduled cron
        $timestamp = wp_next_scheduled('alm_check_essentials_selections');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'alm_check_essentials_selections');
        }
    }
    
    /**
     * AJAX handler for getting webhook logs
     */
    public function ajax_get_webhook_logs() {
        check_ajax_referer('alm_webhook_settings', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        
        require_once ALM_PLUGIN_DIR . 'includes/class-zoom-webhook.php';
        $webhook = new ALM_Zoom_Webhook();
        
        $logs = $webhook->get_debug_logs();
        
        wp_send_json_success($logs);
    }
    
    /**
     * AJAX handler for clearing webhook logs
     */
    public function ajax_clear_webhook_logs() {
        check_ajax_referer('alm_webhook_settings', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        
        require_once ALM_PLUGIN_DIR . 'includes/class-zoom-webhook.php';
        $webhook = new ALM_Zoom_Webhook();
        
        $webhook->clear_debug_logs();
        
        wp_send_json_success(array('message' => __('Logs cleared successfully.', 'academy-lesson-manager')));
    }
    
    /**
     * AJAX handler for retrying a failed webhook
     */
    public function ajax_retry_webhook() {
        check_ajax_referer('alm_webhook_settings', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        
        $log_index = isset($_POST['log_index']) ? intval($_POST['log_index']) : -1;
        
        if ($log_index < 0) {
            wp_send_json_error(array('message' => __('Invalid log index.', 'academy-lesson-manager')));
        }
        
        require_once ALM_PLUGIN_DIR . 'includes/class-zoom-webhook.php';
        $webhook = new ALM_Zoom_Webhook();
        
        // Get all logs
        $logs = $webhook->get_debug_logs();
        
        if (!isset($logs[$log_index])) {
            wp_send_json_error(array('message' => __('Log entry not found.', 'academy-lesson-manager')));
        }
        
        $log = $logs[$log_index];
        
        // Check if log has payload
        if (!isset($log['payload']) || empty($log['payload'])) {
            wp_send_json_error(array('message' => __('No payload found in log entry.', 'academy-lesson-manager')));
        }
        
        // Retry processing the webhook with the original payload
        $result = $webhook->process_webhook($log['payload'], false);
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => __('Webhook processed successfully.', 'academy-lesson-manager'),
                'debug' => $result['debug']
            ));
        } else {
            wp_send_json_error(array(
                'message' => isset($result['error']) ? $result['error'] : __('Webhook processing failed.', 'academy-lesson-manager'),
                'debug' => isset($result['debug']) ? $result['debug'] : null
            ));
        }
    }
    
    /**
     * AJAX: Submit chapter MP3 to AssemblyAI and return immediately
     */
    public function ajax_transcribe_chapter() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }

        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        if (!$chapter_id) {
            wp_send_json_error(array('message' => __('Invalid chapter ID.', 'academy-lesson-manager')));
        }

        global $wpdb;
        $database       = new ALM_Database();
        $database->check_and_add_assemblyai_transcript_id_column();
        $chapters_table = $database->get_table_name('chapters');
        $chapter        = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$chapters_table} WHERE ID = %d", $chapter_id));

        if (!$chapter || empty($chapter->mp3_file_url)) {
            wp_send_json_error(array('message' => __('Chapter or MP3 not found.', 'academy-lesson-manager')));
        }

        $upload_dir = wp_upload_dir();
        $mp3_path   = $upload_dir['basedir'] . '/alm_mp3s/' . $chapter->mp3_file_url;

        if (!file_exists($mp3_path)) {
            wp_send_json_error(array('message' => __('MP3 file not found on disk.', 'academy-lesson-manager')));
        }

        $api_key = get_option('alm_assemblyai_api_key', '');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('AssemblyAI API key not configured. Define ALM_ASSEMBLYAI_API_KEY in wp-config.php or set the option.', 'academy-lesson-manager')));
        }

        // Clear old log
        $log_dir  = $upload_dir['basedir'] . '/alm_logs';
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        $log_file = $log_dir . '/transcription_' . $chapter_id . '.log';
        if (file_exists($log_file)) {
            unlink($log_file);
        }

        $this->update_transcription_status($chapter_id, 'processing', 'Uploading MP3 to AssemblyAI...', 5);

        $file_contents = file_get_contents($mp3_path);
        if ($file_contents === false) {
            wp_send_json_error(array('message' => __('Could not read MP3 file.', 'academy-lesson-manager')));
        }

        $upload_response = wp_remote_post(
            'https://api.assemblyai.com/v2/upload',
            array(
                'timeout' => 300,
                'headers' => array(
                    'authorization' => $api_key,
                    'content-type'  => 'application/octet-stream',
                ),
                'body'    => $file_contents,
            )
        );

        if (is_wp_error($upload_response)) {
            $this->update_transcription_status($chapter_id, 'failed', 'Upload failed: ' . $upload_response->get_error_message(), 0);
            wp_send_json_error(array('message' => 'Upload error: ' . $upload_response->get_error_message()));
        }

        $upload_body = json_decode(wp_remote_retrieve_body($upload_response), true);
        if (empty($upload_body['upload_url'])) {
            $this->update_transcription_status($chapter_id, 'failed', 'Upload failed: no upload_url returned', 0);
            wp_send_json_error(array('message' => __('AssemblyAI upload did not return a URL.', 'academy-lesson-manager')));
        }

        $audio_url = $upload_body['upload_url'];
        $this->update_transcription_status($chapter_id, 'processing', 'File uploaded. Submitting transcription job...', 20);

        $transcript_response = wp_remote_post(
            'https://api.assemblyai.com/v2/transcript',
            array(
                'timeout' => 30,
                'headers' => array(
                    'authorization' => $api_key,
                    'content-type'  => 'application/json',
                ),
                'body'    => wp_json_encode(
                    array(
                        'audio_url'     => $audio_url,
                        'speech_models' => array('universal-2'),
                        'punctuate'     => true,
                        'format_text'   => true,
                    )
                ),
            )
        );

        if (is_wp_error($transcript_response)) {
            $this->update_transcription_status($chapter_id, 'failed', 'Job submit failed: ' . $transcript_response->get_error_message(), 0);
            wp_send_json_error(array('message' => 'Submit error: ' . $transcript_response->get_error_message()));
        }

        $transcript_raw  = wp_remote_retrieve_body($transcript_response);
        $transcript_http = wp_remote_retrieve_response_code($transcript_response);
        $transcript_body = json_decode($transcript_raw, true);
        if (empty($transcript_body['id'])) {
            $detail = 'HTTP ' . $transcript_http . ': ' . $transcript_raw;
            $this->update_transcription_status($chapter_id, 'failed', 'No transcript ID — ' . $detail, 0);
            wp_send_json_error(array('message' => 'AssemblyAI submit failed — ' . $detail));
        }

        $transcript_id = sanitize_text_field($transcript_body['id']);
        $this->update_transcription_status($chapter_id, 'processing', 'Job queued. Transcript ID: ' . $transcript_id, 25);

        $wpdb->update(
            $chapters_table,
            array('assemblyai_transcript_id' => $transcript_id),
            array('ID' => $chapter_id),
            array('%s'),
            array('%d')
        );

        wp_send_json_success(
            array(
                'message'       => __('Transcription job submitted to AssemblyAI. Polling for completion...', 'academy-lesson-manager'),
                'transcript_id' => $transcript_id,
                'chapter_id'    => $chapter_id,
            )
        );
    }

    /**
     * AJAX: Poll AssemblyAI for status, finalize when complete
     */
    public function ajax_check_transcription_status() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }

        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        if (!$chapter_id) {
            wp_send_json_error(array('message' => __('Invalid chapter ID.', 'academy-lesson-manager')));
        }

        $api_key = get_option('alm_assemblyai_api_key', '');
        global $wpdb;
        $database       = new ALM_Database();
        $database->check_and_add_assemblyai_transcript_id_column();
        $chapters_table = $database->get_table_name('chapters');
        $chapter        = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$chapters_table} WHERE ID = %d", $chapter_id));

        if (!$chapter) {
            wp_send_json_error(array('message' => __('Chapter not found.', 'academy-lesson-manager')));
        }

        $upload_dir = wp_upload_dir();
        $log_file   = $upload_dir['basedir'] . '/alm_logs/transcription_' . $chapter_id . '.log';

        $tid = isset($chapter->assemblyai_transcript_id) ? trim((string) $chapter->assemblyai_transcript_id) : '';
        if ($tid === '') {
            $transcript_id_recovered = '';
            if (file_exists($log_file)) {
                $log_contents = file_get_contents($log_file);
                if (is_string($log_contents) && preg_match('/Transcript ID:\s*([a-f0-9\-]{36})/i', $log_contents, $matches)) {
                    $transcript_id_recovered = sanitize_text_field($matches[1]);
                    $wpdb->update(
                        $chapters_table,
                        array('assemblyai_transcript_id' => $transcript_id_recovered),
                        array('ID' => $chapter_id),
                        array('%s'),
                        array('%d')
                    );
                    $chapter->assemblyai_transcript_id = $transcript_id_recovered;
                }
            }
            if ($transcript_id_recovered === '') {
                $log = file_exists($log_file) ? file_get_contents($log_file) : __('No transcription job found.', 'academy-lesson-manager');
                wp_send_json_success(array('aai_status' => 'none', 'log' => $log));
                return;
            }
            $tid = $transcript_id_recovered;
        }

        $transcript_id = $tid;

        $status_response = wp_remote_get(
            'https://api.assemblyai.com/v2/transcript/' . rawurlencode($transcript_id),
            array(
                'timeout' => 15,
                'headers' => array('authorization' => $api_key),
            )
        );

        if (is_wp_error($status_response)) {
            $this->update_transcription_status($chapter_id, 'processing', 'Status check error: ' . $status_response->get_error_message(), 30);
            $upload_dir = wp_upload_dir();
            $log_file   = $upload_dir['basedir'] . '/alm_logs/transcription_' . $chapter_id . '.log';
            wp_send_json_success(array('aai_status' => 'processing', 'log' => file_exists($log_file) ? file_get_contents($log_file) : ''));
        }

        $result     = json_decode(wp_remote_retrieve_body($status_response), true);
        $aai_status = isset($result['status']) ? $result['status'] : 'unknown';

        $progress_map = array('queued' => 30, 'processing' => 60, 'completed' => 100, 'error' => 0);
        $progress     = isset($progress_map[ $aai_status ]) ? $progress_map[ $aai_status ] : 40;

        $this->update_transcription_status(
            $chapter_id,
            $aai_status === 'error' ? 'failed' : 'processing',
            'AssemblyAI status: ' . strtoupper($aai_status),
            $progress
        );

        if ($aai_status === 'completed') {
            $vtt_result = $this->save_assemblyai_transcript($chapter_id, $result);
            if (is_wp_error($vtt_result)) {
                $this->update_transcription_status($chapter_id, 'failed', 'VTT generation failed: ' . $vtt_result->get_error_message(), 0);
                $wpdb->update(
                    $chapters_table,
                    array('assemblyai_transcript_id' => ''),
                    array('ID' => $chapter_id),
                    array('%s'),
                    array('%d')
                );
                wp_send_json_success(array('aai_status' => 'error', 'log' => $vtt_result->get_error_message(), 'vtt_saved' => false));
            }
            $wpdb->update(
                $chapters_table,
                array('assemblyai_transcript_id' => ''),
                array('ID' => $chapter_id),
                array('%s'),
                array('%d')
            );
            $this->update_transcription_status($chapter_id, 'completed', 'Transcription saved successfully!', 100);
        }

        if ($aai_status === 'error') {
            $error_msg = isset($result['error']) ? $result['error'] : 'Unknown AssemblyAI error';
            $this->update_transcription_status($chapter_id, 'failed', 'AssemblyAI error: ' . $error_msg, 0);
            $wpdb->update(
                $chapters_table,
                array('assemblyai_transcript_id' => ''),
                array('ID' => $chapter_id),
                array('%s'),
                array('%d')
            );
        }

        $upload_dir = wp_upload_dir();
        $log_file   = $upload_dir['basedir'] . '/alm_logs/transcription_' . $chapter_id . '.log';
        $log        = file_exists($log_file) ? file_get_contents($log_file) : '';

        wp_send_json_success(
            array(
                'aai_status' => $aai_status,
                'log'        => $log,
                'vtt_saved'  => ($aai_status === 'completed'),
            )
        );
    }

    /**
     * AJAX: force-fetch a transcript by AssemblyAI ID (manual recovery).
     */
    public function ajax_force_fetch_transcript() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }

        $chapter_id    = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        $transcript_id = isset($_POST['transcript_id']) ? sanitize_text_field(trim(wp_unslash($_POST['transcript_id']))) : '';
        if (!$chapter_id || $transcript_id === '') {
            wp_send_json_error(array('message' => __('Missing chapter ID or transcript ID.', 'academy-lesson-manager')));
        }

        $api_key = get_option('alm_assemblyai_api_key', '');
        if ($api_key === '') {
            wp_send_json_error(array('message' => __('AssemblyAI API key not configured.', 'academy-lesson-manager')));
        }

        global $wpdb;
        $database       = new ALM_Database();
        $database->check_and_add_assemblyai_transcript_id_column();
        $chapters_table = $database->get_table_name('chapters');

        $response = wp_remote_get(
            'https://api.assemblyai.com/v2/transcript/' . rawurlencode($transcript_id),
            array(
                'timeout' => 15,
                'headers' => array('authorization' => $api_key),
            )
        );

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $result     = json_decode(wp_remote_retrieve_body($response), true);
        $aai_status = isset($result['status']) ? $result['status'] : 'unknown';

        if ($aai_status !== 'completed') {
            wp_send_json_error(
                array(
                    'message' => sprintf(
                        /* translators: %s: AssemblyAI job status */
                        __('Transcript not complete yet. Status: %s', 'academy-lesson-manager'),
                        $aai_status
                    ),
                )
            );
        }

        $wpdb->update(
            $chapters_table,
            array('assemblyai_transcript_id' => $transcript_id),
            array('ID' => $chapter_id),
            array('%s'),
            array('%d')
        );

        $vtt_result = $this->save_assemblyai_transcript($chapter_id, $result);
        if (is_wp_error($vtt_result)) {
            wp_send_json_error(
                array(
                    'message' => sprintf(
                        /* translators: %s: error message */
                        __('VTT save failed: %s', 'academy-lesson-manager'),
                        $vtt_result->get_error_message()
                    ),
                )
            );
        }

        $wpdb->update(
            $chapters_table,
            array('assemblyai_transcript_id' => ''),
            array('ID' => $chapter_id),
            array('%s'),
            array('%d')
        );

        wp_send_json_success(array('message' => __('Transcript saved successfully!', 'academy-lesson-manager')));
    }
    
    /**
     * AJAX handler for clearing stuck transcription status
     */
    public function ajax_clear_transcription_status() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        
        if (!$chapter_id) {
            wp_send_json_error(array('message' => __('Invalid chapter ID.', 'academy-lesson-manager')));
        }
        
        // Clear the status transient
        delete_transient('alm_transcription_status_' . $chapter_id);

        $upload_dir = wp_upload_dir();
        $log_file   = $upload_dir['basedir'] . '/alm_logs/transcription_' . $chapter_id . '.log';
        if (file_exists($log_file)) {
            @unlink($log_file);
        }

        global $wpdb;
        $database       = new ALM_Database();
        $database->check_and_add_assemblyai_transcript_id_column();
        $chapters_table = $database->get_table_name('chapters');
        $wpdb->update(
            $chapters_table,
            array('assemblyai_transcript_id' => ''),
            array('ID' => $chapter_id),
            array('%s'),
            array('%d')
        );
        
        wp_send_json_success(array('message' => __('Status cleared. You can now retry transcription.', 'academy-lesson-manager')));
    }

    /**
     * AJAX handler: Remove the uploaded MP3 file from a chapter
     */
    public function ajax_remove_mp3() {
        check_ajax_referer('alm_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }

        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        if (!$chapter_id) {
            wp_send_json_error(array('message' => 'Invalid chapter ID.'));
        }

        global $wpdb;
        $database = new ALM_Database();
        $chapters_table = $database->get_table_name('chapters');

        $chapter = $wpdb->get_row($wpdb->prepare(
            "SELECT mp3_file_url FROM {$chapters_table} WHERE ID = %d",
            $chapter_id
        ));

        if (!$chapter || empty($chapter->mp3_file_url)) {
            wp_send_json_error(array('message' => 'No MP3 file found on this chapter.'));
        }

        // Delete physical file
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/alm_mp3s/' . basename($chapter->mp3_file_url);
        if (file_exists($file_path)) {
            @unlink($file_path);
        }

        // Clear DB column
        $wpdb->update(
            $chapters_table,
            array('mp3_file_url' => ''),
            array('ID' => $chapter_id),
            array('%s'),
            array('%d')
        );

        // Also clear any stuck transcription status
        delete_transient('alm_transcription_status_' . $chapter_id);

        $log_file = $upload_dir['basedir'] . '/alm_logs/transcription_' . $chapter_id . '.log';
        if (file_exists($log_file)) {
            @unlink($log_file);
        }

        wp_send_json_success(array('message' => 'MP3 removed successfully.'));
    }
    
    /**
     * AJAX handler for checking if VTT file exists
     */
    public function ajax_check_vtt_file() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        
        if (!$chapter_id) {
            wp_send_json_error(array('message' => __('Invalid chapter ID.', 'academy-lesson-manager')));
        }
        
        // Check if VTT file exists
        $upload_dir = wp_upload_dir();
        $vtt_dir = $upload_dir['basedir'] . '/alm_transcriptions';
        $vtt_filename = 'chapter-' . $chapter_id . '.vtt';
        $vtt_path = $vtt_dir . '/' . $vtt_filename;
        
        $exists = file_exists($vtt_path);
        
        wp_send_json_success(array(
            'exists' => $exists,
            'vtt_path' => $vtt_path,
            'vtt_filename' => $vtt_filename
        ));
    }
    
    /**
     * AJAX handler for syncing VTT file from filesystem to database
     */
    public function ajax_sync_vtt_file() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        
        if (!$chapter_id || !$lesson_id) {
            wp_send_json_error(array('message' => __('Invalid chapter or lesson ID.', 'academy-lesson-manager')));
        }
        
        global $wpdb;
        $transcripts_table = $wpdb->prefix . 'alm_transcripts';
        
        // Check if VTT file exists
        $upload_dir = wp_upload_dir();
        $vtt_dir = $upload_dir['basedir'] . '/alm_transcriptions';
        $vtt_filename = 'chapter-' . $chapter_id . '.vtt';
        $vtt_path = $vtt_dir . '/' . $vtt_filename;
        
        if (!file_exists($vtt_path)) {
            wp_send_json_error(array('message' => __('VTT file not found: ' . $vtt_filename, 'academy-lesson-manager')));
        }
        
        // Read VTT content to extract plain text
        $vtt_content = file_get_contents($vtt_path);
        if ($vtt_content === false) {
            wp_send_json_error(array('message' => __('Could not read VTT file.', 'academy-lesson-manager')));
        }
        
        // Extract plain text from VTT
        $transcript_text = $this->extract_text_from_vtt($vtt_content);
        
        // Check if transcript already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT ID FROM {$transcripts_table} WHERE chapter_id = %d AND (source = 'whisper' OR source = 'zoom') LIMIT 1",
            $chapter_id
        ));
        
        if ($existing) {
            // Update existing transcript
            $wpdb->update(
                $transcripts_table,
                array(
                    'vtt_file' => $vtt_filename,
                    'content' => $transcript_text,
                    'updated_at' => current_time('mysql')
                ),
                array('ID' => $existing->ID),
                array('%s', '%s', '%s'),
                array('%d')
            );
        } else {
            // Create new transcript record
            $wpdb->insert(
                $transcripts_table,
                array(
                    'lesson_id' => $lesson_id,
                    'chapter_id' => $chapter_id,
                    'source' => 'zoom',
                    'vtt_file' => $vtt_filename,
                    'content' => $transcript_text,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s', '%s')
            );
        }
        
        wp_send_json_success(array(
            'vtt_filename' => $vtt_filename,
            'message' => __('VTT file synced successfully!', 'academy-lesson-manager')
        ));
    }

    /**
     * AJAX: load chapter VTT file content for editor (alm_transcripts).
     */
    public function ajax_load_transcript() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }

        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        if (!$chapter_id) {
            wp_send_json_error(array('message' => __('Invalid chapter ID.', 'academy-lesson-manager')));
        }

        global $wpdb;
        $database          = new ALM_Database();
        $transcripts_table = $database->get_table_name('transcripts');
        $vtt_file          = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT vtt_file FROM {$transcripts_table} WHERE chapter_id = %d AND (source = 'whisper' OR source = 'zoom') LIMIT 1",
                $chapter_id
            )
        );
        if (empty($vtt_file)) {
            $vtt_file = 'chapter-' . $chapter_id . '.vtt';
        }
        $vtt_file = basename($vtt_file);

        $upload_dir = wp_upload_dir();
        $vtt_path   = $upload_dir['basedir'] . '/alm_transcriptions/' . $vtt_file;

        if (!file_exists($vtt_path)) {
            wp_send_json_error(array('message' => sprintf(/* translators: %s: filename */ __('VTT file not found on disk: %s', 'academy-lesson-manager'), $vtt_file)));
        }

        $raw = file_get_contents($vtt_path);
        if ($raw === false) {
            wp_send_json_error(array('message' => __('Could not read VTT file.', 'academy-lesson-manager')));
        }

        wp_send_json_success(array('content' => $raw));
    }

    /**
     * AJAX: save chapter VTT file from editor (updates file + alm_transcripts.content).
     */
    public function ajax_save_transcript() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }

        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        $content    = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
        if ($chapter_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid chapter ID.', 'academy-lesson-manager')));
        }
        if (trim($content) === '') {
            wp_send_json_error(array('message' => __('Content cannot be empty.', 'academy-lesson-manager')));
        }
        if (strpos($content, 'WEBVTT') === false) {
            wp_send_json_error(array('message' => __('Invalid VTT format — must include WEBVTT.', 'academy-lesson-manager')));
        }

        global $wpdb;
        $database          = new ALM_Database();
        $transcripts_table = $database->get_table_name('transcripts');
        $vtt_file          = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT vtt_file FROM {$transcripts_table} WHERE chapter_id = %d AND (source = 'whisper' OR source = 'zoom') LIMIT 1",
                $chapter_id
            )
        );
        if (empty($vtt_file)) {
            $vtt_file = 'chapter-' . $chapter_id . '.vtt';
        }
        $vtt_file = basename($vtt_file);

        $upload_dir = wp_upload_dir();
        $vtt_path   = $upload_dir['basedir'] . '/alm_transcriptions/' . $vtt_file;

        if (!file_exists($vtt_path)) {
            wp_send_json_error(array('message' => sprintf(/* translators: %s: filename */ __('No VTT file on disk to update: %s', 'academy-lesson-manager'), $vtt_file)));
        }

        if (file_put_contents($vtt_path, $content) === false) {
            wp_send_json_error(array('message' => __('Could not write to VTT file.', 'academy-lesson-manager')));
        }

        $plain = $this->extract_text_from_vtt($content);
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$transcripts_table} SET content = %s, updated_at = %s WHERE chapter_id = %d AND (source = 'whisper' OR source = 'zoom')",
                $plain,
                current_time('mysql'),
                $chapter_id
            )
        );

        wp_send_json_success(array('message' => __('Transcript saved.', 'academy-lesson-manager')));
    }
    
    /**
     * Extract plain text from VTT content (removes timestamps and formatting)
     * 
     * @param string $vtt_content VTT file content
     * @return string Plain text transcript
     */
    private function extract_text_from_vtt($vtt_content) {
        $lines = explode("\n", $vtt_content);
        $text_lines = array();
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines, WEBVTT header, and timestamp lines
            if (empty($line) || 
                $line === 'WEBVTT' || 
                preg_match('/^\d{2}:\d{2}:\d{2}/', $line) || 
                preg_match('/^-->$/', $line)) {
                continue;
            }
            
            // Remove VTT formatting tags like <c>, <v>, etc.
            $line = preg_replace('/<[^>]+>/', '', $line);
            
            if (!empty($line)) {
                $text_lines[] = $line;
            }
        }
        
        return implode(' ', $text_lines);
    }
    
    /**
     * Convert AssemblyAI completed transcript to VTT and save (alm_transcripts + file on disk).
     *
     * @param int   $chapter_id Chapter ID.
     * @param array $result     AssemblyAI transcript JSON when status is completed.
     * @return true|WP_Error
     */
    private function save_assemblyai_transcript($chapter_id, $result) {
        $words = isset($result['words']) && is_array($result['words']) ? $result['words'] : array();
        $text  = isset($result['text']) ? $result['text'] : '';

        if (empty($words) && $text === '') {
            return new WP_Error('no_transcript', 'AssemblyAI returned no words or text.');
        }

        $vtt           = "WEBVTT\n\n";
        $segment_words = array();
        $segment_start = null;
        $segment_end   = null;
        $max_words     = 10;
        $max_duration  = 4000; // ms

        foreach ($words as $word) {
            if (!is_array($word)) {
                continue;
            }
            $start = isset($word['start']) ? floatval($word['start']) : 0;
            $end   = isset($word['end']) ? floatval($word['end']) : 0;
            $w     = isset($word['text']) ? $word['text'] : '';

            if ($segment_start === null) {
                $segment_start = $start;
            }

            $segment_words[] = $w;
            $segment_end     = $end;

            $word_count    = count($segment_words);
            $duration      = $segment_end - $segment_start;
            $ends_sentence = (bool) preg_match('/[.!?]$/', rtrim($w));

            if ($word_count >= $max_words || $duration >= $max_duration || ($ends_sentence && $word_count >= 4)) {
                $vtt .= $this->format_vtt_cue($segment_start, $segment_end, implode(' ', $segment_words));
                $segment_words = array();
                $segment_start = null;
                $segment_end   = null;
            }
        }

        if (!empty($segment_words) && $segment_start !== null) {
            $vtt .= $this->format_vtt_cue($segment_start, $segment_end, implode(' ', $segment_words));
        }

        if (empty($words) && $text !== '') {
            $vtt .= "00:00:00.000 --> 99:59:59.000\n" . $text . "\n\n";
        }

        $upload_dir  = wp_upload_dir();
        $vtt_dir     = $upload_dir['basedir'] . '/alm_transcriptions';
        if (!file_exists($vtt_dir)) {
            wp_mkdir_p($vtt_dir);
        }

        $vtt_filename = 'chapter-' . $chapter_id . '.vtt';
        $vtt_path     = $vtt_dir . '/' . $vtt_filename;

        if (file_put_contents($vtt_path, $vtt) === false) {
            return new WP_Error('write_failed', 'Could not write VTT file to disk.');
        }

        global $wpdb;
        $database          = new ALM_Database();
        $chapters_table    = $database->get_table_name('chapters');
        $transcripts_table = $database->get_table_name('transcripts');
        $chapter           = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$chapters_table} WHERE ID = %d", $chapter_id));
        if (!$chapter) {
            return new WP_Error('no_chapter', 'Chapter not found.');
        }

        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT ID FROM {$transcripts_table} WHERE chapter_id = %d AND source = 'whisper' LIMIT 1",
                $chapter_id
            )
        );

        if ($existing) {
            $wpdb->update(
                $transcripts_table,
                array(
                    'content'    => $text,
                    'vtt_file'   => $vtt_filename,
                    'updated_at' => current_time('mysql'),
                ),
                array('ID' => $existing->ID),
                array('%s', '%s', '%s'),
                array('%d')
            );
        } else {
            $wpdb->insert(
                $transcripts_table,
                array(
                    'lesson_id'  => $chapter->lesson_id,
                    'chapter_id' => $chapter_id,
                    'source'     => 'whisper',
                    'content'    => $text,
                    'vtt_file'   => $vtt_filename,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s', '%s')
            );
        }

        if (!empty($chapter->mp3_file_url)) {
            $mp3_full = $upload_dir['basedir'] . '/alm_mp3s/' . basename($chapter->mp3_file_url);
            if (file_exists($mp3_full)) {
                @unlink($mp3_full);
            }
        }

        $wpdb->update(
            $chapters_table,
            array('mp3_file_url' => ''),
            array('ID' => $chapter_id),
            array('%s'),
            array('%d')
        );

        $log_dir  = $upload_dir['basedir'] . '/alm_logs';
        $log_file = $log_dir . '/transcription_' . $chapter_id . '.log';
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        $aai_id  = isset($result['id']) ? (string) $result['id'] : 'unknown';
        $summary = '[' . current_time('Y-m-d H:i:s') . '] [COMPLETED] (100%) VTT saved: ' . $vtt_filename . ' | AssemblyAI ID: ' . $aai_id . ' | Words: ' . count($words) . PHP_EOL;
        file_put_contents($log_file, $summary, FILE_APPEND | LOCK_EX);

        return true;
    }

    /**
     * Format a single VTT cue block (times in milliseconds).
     *
     * @param float|int $start_ms Start ms.
     * @param float|int $end_ms   End ms.
     * @param string    $text     Cue text.
     * @return string
     */
    private function format_vtt_cue($start_ms, $end_ms, $text) {
        return $this->ms_to_vtt_time($start_ms) . ' --> ' . $this->ms_to_vtt_time($end_ms) . "\n" . trim($text) . "\n\n";
    }

    /**
     * Convert milliseconds to VTT timestamp format.
     *
     * @param float|int $ms Milliseconds.
     * @return string
     */
    private function ms_to_vtt_time($ms) {
        $ms   = max(0, (int) round(floatval($ms)));
        $h    = (int) floor($ms / 3600000);
        $m    = (int) floor(($ms % 3600000) / 60000);
        $s    = (int) floor(($ms % 60000) / 1000);
        $mill = $ms % 1000;
        return sprintf('%02d:%02d:%02d.%03d', $h, $m, $s, $mill);
    }

    /**
     * Legacy hook: transcription is now AssemblyAI async from the admin UI.
     *
     * @param int    $chapter_id Chapter ID.
     * @param string $mp3_path   Unused.
     */
    public function run_transcription_background($chapter_id, $mp3_path) {
        error_log('ALM: run_transcription_background is unused; use AssemblyAI flow from chapter edit screen.');
    }
    
    /**
     * Helper to update transcription status
     */
    private function update_transcription_status($chapter_id, $status, $message, $progress) {
        $upload_dir = wp_upload_dir();
        $log_dir    = $upload_dir['basedir'] . '/alm_logs';
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        $log_file = $log_dir . '/transcription_' . intval($chapter_id) . '.log';
        $line = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($status) . '] (' . $progress . '%) ' . $message . PHP_EOL;
        file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);

        // Keep legacy transient for pollForVTTFile compatibility
        set_transient('alm_transcription_status_' . $chapter_id, array(
            'status'     => $status,
            'message'    => $message,
            'progress'   => $progress,
            'timestamp'  => time(),
            'start_time' => time(),
            'last_update'=> time(),
        ), 7200);

        error_log(sprintf('ALM Transcription [Chapter %d]: %s - %s (%d%%)', $chapter_id, strtoupper($status), $message, $progress));
    }
    
    /**
     * AJAX handler to generate lesson description using AI
     */
    public function ajax_generate_lesson_description() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        
        if (!$lesson_id) {
            wp_send_json_error(array('message' => __('Invalid lesson ID.', 'academy-lesson-manager')));
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $lessons_table = $database->get_table_name('lessons');
        $chapters_table = $database->get_table_name('chapters');
        $transcripts_table = $wpdb->prefix . 'alm_transcripts';
        
        // Get lesson
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$lessons_table} WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            wp_send_json_error(array('message' => __('Lesson not found.', 'academy-lesson-manager')));
        }
        
        // Get all chapters for this lesson
        $chapters = $wpdb->get_results($wpdb->prepare(
            "SELECT ID FROM {$chapters_table} WHERE lesson_id = %d",
            $lesson_id
        ));
        
        if (empty($chapters)) {
            wp_send_json_error(array('message' => __('No chapters found for this lesson.', 'academy-lesson-manager')));
        }
        
        // Get all transcripts for all chapters
        $chapter_ids = array_map(function($chapter) {
            return $chapter->ID;
        }, $chapters);
        
        $placeholders = implode(',', array_fill(0, count($chapter_ids), '%d'));
        $transcripts = $wpdb->get_results($wpdb->prepare(
            "SELECT content FROM {$transcripts_table} WHERE chapter_id IN ($placeholders) AND source = 'whisper' AND content IS NOT NULL AND content != ''",
            ...$chapter_ids
        ));
        
        if (empty($transcripts)) {
            wp_send_json_error(array('message' => __('No transcripts found for this lesson. Please transcribe chapters first.', 'academy-lesson-manager')));
        }
        
        // Combine all transcript content
        $transcript_text = '';
        foreach ($transcripts as $transcript) {
            $transcript_text .= strip_tags($transcript->content) . ' ';
        }
        $transcript_text = trim($transcript_text);
        
        // Limit transcript length to avoid token limits (keep first 5000 characters)
        if (strlen($transcript_text) > 5000) {
            $transcript_text = substr($transcript_text, 0, 5000) . '...';
        }
        
        // Get the prompt from settings
        $default_prompt = 'Create a compelling lesson description based on the following transcript. Limit to 100 words or less. No emojis.';
        $prompt = get_option('alm_ai_lesson_description_prompt', $default_prompt);
        
        // Build the full prompt
        $full_prompt = $prompt . "\n\nTranscript:\n" . $transcript_text;
        
        // Get OpenAI API key
        $api_key = get_option('katahdin_ai_hub_openai_key');
        if (empty($api_key)) {
            $api_key = get_option('fluent_support_ai_openai_key');
        }
        
        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('OpenAI API key not found. Please configure it in settings.', 'academy-lesson-manager')));
        }
        
        // Call OpenAI API
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'gpt-4o-mini',
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $full_prompt
                    )
                ),
                'max_tokens' => 200,
                'temperature' => 0.7,
            )),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => __('Error calling OpenAI API: ', 'academy-lesson-manager') . $response->get_error_message()));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($body['choices'][0]['message']['content'])) {
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : __('Unknown error from OpenAI API.', 'academy-lesson-manager');
            wp_send_json_error(array('message' => __('OpenAI API error: ', 'academy-lesson-manager') . $error_message));
        }
        
        $generated_description = trim($body['choices'][0]['message']['content']);
        
        // Update lesson description
        $wpdb->update(
            $lessons_table,
            array('lesson_description' => $generated_description),
            array('ID' => $lesson_id),
            array('%s'),
            array('%d')
        );
        
        wp_send_json_success(array(
            'description' => $generated_description,
            'message' => __('Description generated successfully!', 'academy-lesson-manager')
        ));
    }
    
    /**
     * AJAX handler to expand lesson description using AI
     */
    public function ajax_expand_lesson_description() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        $current_description = isset($_POST['current_description']) ? wp_kses_post($_POST['current_description']) : '';
        
        if (!$lesson_id) {
            wp_send_json_error(array('message' => __('Invalid lesson ID.', 'academy-lesson-manager')));
        }
        
        if (empty($current_description)) {
            wp_send_json_error(array('message' => __('Please enter a description first to expand.', 'academy-lesson-manager')));
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $lessons_table = $database->get_table_name('lessons');
        
        // Get lesson to verify it exists
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$lessons_table} WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            wp_send_json_error(array('message' => __('Lesson not found.', 'academy-lesson-manager')));
        }
        
        // Strip HTML tags for the prompt
        $description_text = wp_strip_all_tags($current_description);
        $description_text = trim($description_text);
        
        if (empty($description_text)) {
            wp_send_json_error(array('message' => __('Description is empty after stripping HTML.', 'academy-lesson-manager')));
        }
        
        // Get the prompt from settings or use default
        $default_prompt = 'Your job: expand what the user wrote and clean it up. Do NOT rewrite it as marketing copy.

Rules:
- PRESERVE every fact, topic, name, and detail from the original. Do not remove, change, or invent content.
- Clean up: fix grammar, punctuation, run-on sentences, and typos. Turn rough notes or fragments into clear, complete sentences.
- Flesh out only for clarity: if something is vague or abbreviated, you may add a short clarifying phrase—but only using information that is already implied or stated. Do not add new topics or claims.
- Use the author\'s tone: straightforward and factual. No marketing fluff. No phrases like "embark on a journey," "unlock," "discover," "empowering," "immersive," "transform," or similar. No hype.
- Output only the expanded, cleaned-up description. No preamble, no "Here is the expanded version," no bullets unless the original had them.';
        $prompt = get_option('alm_ai_expand_description_prompt', $default_prompt);
        
        // Build the full prompt
        $full_prompt = $prompt . "\n\nCurrent Description:\n" . $description_text;
        
        // Get OpenAI API key
        $api_key = get_option('katahdin_ai_hub_openai_key');
        if (empty($api_key)) {
            $api_key = get_option('fluent_support_ai_openai_key');
        }
        
        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('OpenAI API key not found. Please configure it in settings.', 'academy-lesson-manager')));
        }
        
        // Call OpenAI API
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'gpt-4o-mini',
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $full_prompt
                    )
                ),
                'max_tokens' => 400,
                'temperature' => 0.7,
            )),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => __('Error calling OpenAI API: ', 'academy-lesson-manager') . $response->get_error_message()));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($body['choices'][0]['message']['content'])) {
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : __('Unknown error from OpenAI API.', 'academy-lesson-manager');
            wp_send_json_error(array('message' => __('OpenAI API error: ', 'academy-lesson-manager') . $error_message));
        }
        
        $expanded_description = trim($body['choices'][0]['message']['content']);
        
        // Update lesson description
        $wpdb->update(
            $lessons_table,
            array('lesson_description' => $expanded_description),
            array('ID' => $lesson_id),
            array('%s'),
            array('%d')
        );
        
        wp_send_json_success(array(
            'description' => $expanded_description,
            'message' => __('Description expanded successfully!', 'academy-lesson-manager')
        ));
    }
    
    /**
     * AJAX handler for getting combined lesson transcript
     */
    public function ajax_get_lesson_transcript() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }
        
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        
        if (!$lesson_id) {
            wp_send_json_error(array('message' => __('Invalid lesson ID.', 'academy-lesson-manager')));
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $lesson_transcript_table = $database->get_table_name('lesson_transcript');
        
        // Check if transcript exists in database
        $stored_transcript = $wpdb->get_row($wpdb->prepare(
            "SELECT transcript_text FROM {$lesson_transcript_table} WHERE lesson_id = %d",
            $lesson_id
        ));
        
        // If not in database or empty, generate it
        if (empty($stored_transcript) || empty($stored_transcript->transcript_text)) {
            $admin_lessons = new ALM_Admin_Lessons();
            $combined_text = $admin_lessons->combine_lesson_vtt_files($lesson_id);
            
            if (empty($combined_text)) {
                wp_send_json_error(array('message' => __('No VTT files found for this lesson.', 'academy-lesson-manager')));
            }
            
            // Store in database
            $wpdb->replace(
                $lesson_transcript_table,
                array(
                    'lesson_id' => $lesson_id,
                    'transcript_text' => $combined_text,
                    'updated_at' => current_time('mysql')
                ),
                array('%d', '%s', '%s')
            );
            
            wp_send_json_success(array('transcript' => $combined_text));
        } else {
            // Return stored transcript
            wp_send_json_success(array('transcript' => $stored_transcript->transcript_text));
        }
    }

    /**
     * AJAX: Generate product description from chapter titles using Katahdin AI
     */
    public function ajax_generate_product_description() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }

        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        if (!$lesson_id) {
            wp_send_json_error(array('message' => __('Invalid lesson ID.', 'academy-lesson-manager')));
        }

        global $wpdb;
        $database = new ALM_Database();
        $chapters_table = $database->get_table_name('chapters');

        $chapters = $wpdb->get_results($wpdb->prepare(
            "SELECT chapter_title FROM {$chapters_table} WHERE lesson_id = %d ORDER BY menu_order ASC",
            $lesson_id
        ));

        if (empty($chapters)) {
            wp_send_json_error(array('message' => __('No chapters found for this lesson.', 'academy-lesson-manager')));
        }

        $chapter_titles = array_map(function ($c) {
            return stripslashes($c->chapter_title);
        }, $chapters);
        $titles_text = implode("\n", $chapter_titles);

        $default_prompt = 'Create a compelling product description for an online piano lesson based on the following chapter titles. Write 2-4 paragraphs that highlight what students will learn and why this lesson is valuable. Use a professional, engaging tone. No emojis.';
        $prompt = get_option('alm_ai_product_description_prompt', $default_prompt);
        $full_prompt = $prompt . "\n\nChapter titles:\n" . $titles_text;

        $api_key = get_option('katahdin_ai_hub_openai_key');
        if (empty($api_key)) {
            $api_key = get_option('fluent_support_ai_openai_key');
        }
        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('OpenAI API key not found. Please configure it in settings.', 'academy-lesson-manager')));
        }

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'gpt-4o-mini',
                'messages' => array(array('role' => 'user', 'content' => $full_prompt)),
                'max_tokens' => 500,
                'temperature' => 0.7,
            )),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => __('Error calling OpenAI API: ', 'academy-lesson-manager') . $response->get_error_message()));
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($body['choices'][0]['message']['content'])) {
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : __('Unknown error from OpenAI API.', 'academy-lesson-manager');
            wp_send_json_error(array('message' => __('OpenAI API error: ', 'academy-lesson-manager') . $error_message));
        }

        $generated = trim($body['choices'][0]['message']['content']);
        wp_send_json_success(array('description' => $generated));
    }

    /**
     * AJAX: Create a FluentCart product from a lesson
     */
    public function ajax_create_fluentcart_product() {
        check_ajax_referer('alm_create_fc_product', '_ajax_nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions.');
        }

        global $wpdb;

        $lesson_id       = isset($_POST['lesson_id'])       ? intval($_POST['lesson_id'])              : 0;
        $title           = isset($_POST['title'])           ? sanitize_text_field($_POST['title'])     : '';
        $description     = isset($_POST['description'])     ? wp_kses_post($_POST['description'])      : '';
        $sample_video_url = isset($_POST['sample_video_url']) ? esc_url_raw($_POST['sample_video_url']) : '';
        $price           = isset($_POST['price'])           ? round(floatval($_POST['price']), 2)      : 0.00;
        $compare_price   = isset($_POST['compare_price'])  ? round(floatval($_POST['compare_price']), 2) : 0.00;

        if (empty($title)) {
            wp_send_json_error('Product title is required.');
        }

        // FluentCart stores prices in cents
        $price_cents = (int) round($price * 100);
        $compare_price_cents = (int) round($compare_price * 100);

        // Build post_content: if sample video URL is set, add fvplayer shortcode before the text description
        $post_content = $description;
        if (!empty($sample_video_url)) {
            $splash_url = 'https://jazzedge.academy/wp-content/uploads/2023/12/splash-play-video.jpg';
            $video_shortcode = '[fvplayer src="' . esc_url($sample_video_url) . '" splash="' . esc_url($splash_url) . '"]';
            $post_content = $video_shortcode . "\n\n" . $description;
        }

        // Short description: up to 55 words from the full description (text only, no video)
        $short_description = wp_trim_words(wp_strip_all_tags($description), 55);

        // 1. Create the WordPress post (post_type = fluent-products)
        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $post_content,
            'post_excerpt' => $short_description,
            'post_status'  => 'draft',
            'post_type'    => 'fluent-products',
        ], true);

        if (is_wp_error($post_id)) {
            wp_send_json_error('Failed to create post: ' . $post_id->get_error_message());
        }

        // 2. Insert product detail row
        $detail_inserted = $wpdb->insert(
            $wpdb->prefix . 'fct_product_details',
            [
                'post_id'            => $post_id,
                'fulfillment_type'   => 'digital',
                'variation_type'     => 'simple',
                'min_price'          => $price_cents,
                'max_price'          => $price_cents,
                'stock_availability' => 'in-stock',
                'manage_stock'       => '0',
                'manage_downloadable'=> '0',
            ],
            ['%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s']
        );

        if ($detail_inserted === false) {
            wp_delete_post($post_id, true);
            wp_send_json_error('Failed to create product details: ' . $wpdb->last_error);
        }

        // 3. Insert product variation row
        $variation_inserted = $wpdb->insert(
            $wpdb->prefix . 'fct_product_variations',
            [
                'post_id'          => $post_id,
                'variation_title'  => 'Default',
                'item_price'       => $price_cents,
                'compare_price'    => $compare_price_cents,
                'payment_type'     => 'onetime',
                'fulfillment_type' => 'digital',
                'stock_status'     => 'in-stock',
            ],
            ['%d', '%s', '%d', '%d', '%s', '%s', '%s']
        );

        if ($variation_inserted === false) {
            wp_delete_post($post_id, true);
            $wpdb->delete($wpdb->prefix . 'fct_product_details', ['post_id' => $post_id], ['%d']);
            wp_send_json_error('Failed to create product variation: ' . $wpdb->last_error);
        }

        // 4. Store lesson_id as post meta for traceability
        if (!empty($lesson_id)) {
            update_post_meta($post_id, '_alm_lesson_id', $lesson_id);
        } else {
            delete_post_meta($post_id, '_alm_lesson_id');
        }

        // 4b. Set featured image (piano image, not video)
        $featured_image_url = 'https://jazzedge.academy/wp-content/uploads/2026/02/piano-image-8.jpg';
        $attachment_id = attachment_url_to_postid($featured_image_url);
        if (!$attachment_id) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $tmp = download_url($featured_image_url);
            if (!is_wp_error($tmp)) {
                $file_array = array(
                    'name'     => basename($featured_image_url),
                    'tmp_name' => $tmp,
                );
                $attach_id = media_handle_sideload($file_array, $post_id);
                if (!is_wp_error($attach_id)) {
                    $attachment_id = $attach_id;
                }
            }
        }
        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
            $img_url = wp_get_attachment_url($attachment_id) ?: $featured_image_url;
            $gallery_arr = array(array('id' => $attachment_id, 'url' => $img_url, 'title' => ''));
            update_post_meta($post_id, 'fluent-products-gallery-image', $gallery_arr);
        }

        // 5. Store FluentCart product ID on the lesson
        if ($lesson_id > 0) {
            $database = new ALM_Database();
            $lessons_table = $database->get_table_name('lessons');
            $wpdb->update(
                $lessons_table,
                ['fluentcart_product_id' => $post_id],
                ['ID' => $lesson_id],
                ['%d'],
                ['%d']
            );
        }

        // 6. Build response URLs
        $edit_url = admin_url('admin.php?page=fluent-cart#/products/' . $post_id);
        $view_url = get_permalink($post_id) ?: admin_url('admin.php?page=fluent-cart#/products');

        wp_send_json_success([
            'post_id'  => $post_id,
            'edit_url' => $edit_url,
            'view_url' => $view_url,
        ]);
    }

    /**
     * AJAX handler for Transcribe tool - upload and transcribe audio
     */
    public function ajax_transcribe_upload() {
        if (!wp_verify_nonce(isset($_POST['nonce']) ? $_POST['nonce'] : '', 'alm_transcribe_upload')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'academy-lesson-manager')));
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }

        if (empty($_FILES['audio_file']['tmp_name'])) {
            wp_send_json_error(array('message' => __('No file uploaded.', 'academy-lesson-manager')));
        }

        $output_format = isset($_POST['output_format']) && in_array($_POST['output_format'], array('text', 'vtt')) ? $_POST['output_format'] : 'text';
        $tmp_path = $_FILES['audio_file']['tmp_name'];
        $original_name = sanitize_file_name($_FILES['audio_file']['name']);

        $whisper = new ALM_Whisper_Client();
        $result = $whisper->transcribe_upload($tmp_path, $output_format);

        if (!$result['success']) {
            wp_send_json_error(array('message' => $result['message']));
        }

        global $wpdb;
        $database = new ALM_Database();
        $table = $database->get_table_name('whisper_transcripts');
        if (!$table) {
            wp_send_json_error(array('message' => __('Database table not available.', 'academy-lesson-manager')));
        }

        $inserted = $wpdb->insert(
            $table,
            array(
                'file_name' => $original_name,
                'output_format' => $output_format,
                'content' => $result['content']
            ),
            array('%s', '%s', '%s')
        );

        if ($inserted === false) {
            wp_send_json_error(array('message' => __('Failed to save transcript.', 'academy-lesson-manager')));
        }

        wp_send_json_success(array('message' => __('Transcript saved successfully.', 'academy-lesson-manager')));
    }

    /**
     * AJAX handler for getting a saved transcript
     */
    public function ajax_get_transcript() {
        if (!wp_verify_nonce(isset($_POST['nonce']) ? $_POST['nonce'] : '', 'alm_transcribe_upload')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'academy-lesson-manager')));
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$id) {
            wp_send_json_error(array('message' => __('Invalid ID.', 'academy-lesson-manager')));
        }

        $database = new ALM_Database();
        $table = $database->get_table_name('whisper_transcripts');
        if (!$table) {
            wp_send_json_error(array('message' => __('Database table not available.', 'academy-lesson-manager')));
        }

        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT file_name, content FROM {$table} WHERE ID = %d", $id));
        if (!$row) {
            wp_send_json_error(array('message' => __('Transcript not found.', 'academy-lesson-manager')));
        }

        wp_send_json_success(array('file_name' => $row->file_name, 'content' => $row->content));
    }

    /**
     * AJAX handler for deleting a transcript
     */
    public function ajax_delete_transcript() {
        if (!wp_verify_nonce(isset($_POST['nonce']) ? $_POST['nonce'] : '', 'alm_transcribe_upload')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'academy-lesson-manager')));
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'academy-lesson-manager')));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$id) {
            wp_send_json_error(array('message' => __('Invalid ID.', 'academy-lesson-manager')));
        }

        $database = new ALM_Database();
        $table = $database->get_table_name('whisper_transcripts');
        if (!$table) {
            wp_send_json_error(array('message' => __('Database table not available.', 'academy-lesson-manager')));
        }

        global $wpdb;
        $deleted = $wpdb->delete($table, array('ID' => $id), array('%d'));
        if ($deleted === false) {
            wp_send_json_error(array('message' => __('Failed to delete.', 'academy-lesson-manager')));
        }

        wp_send_json_success();
    }
}

// Initialize the plugin
new Academy_Lesson_Manager();
