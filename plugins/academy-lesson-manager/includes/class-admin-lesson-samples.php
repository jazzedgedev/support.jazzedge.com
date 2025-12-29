<?php
/**
 * Lesson Samples Manager Admin Class
 * 
 * Helps admins quickly add samples to lessons that don't have them
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Lesson_Samples {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Table name
     */
    private $table_name;
    
    /**
     * Meta key for hiding lessons
     */
    const HIDE_META_KEY = '_alm_hidden_from_samples';
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();
        $this->table_name = $this->database->get_table_name('lessons');
        
        // Handle actions - use early priority to catch download requests before redirects
        add_action('admin_init', array($this, 'handle_actions'), 1);
        
        // Hook into init for download requests (very early, before any output)
        add_action('init', array($this, 'handle_download_request'), 1);
        
        // Also hook into template_redirect as a fallback (runs after WordPress loads but before output)
        add_action('template_redirect', array($this, 'handle_download_request'), 1);
        
        // Hook into plugins_loaded as earliest possible (before init)
        add_action('plugins_loaded', array($this, 'handle_download_request'), 1);
        
        // Debug: Log that class was instantiated
        // DISABLED: Debug logging
        // $debug_msg = 'ALM Download Debug: ALM_Admin_Lesson_Samples class instantiated at ' . date('Y-m-d H:i:s') . "\n";
        // error_log($debug_msg);
        // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
        
        // Note: AJAX handlers are registered in main plugin class (academy-lesson-manager.php)
        // to ensure they're available for AJAX requests
    }
    
    /**
     * Handle download requests early (before admin_init)
     */
    public function handle_download_request() {
        // Check if this is a download request
        $has_download_param = isset($_GET['download_chapter']);
        $has_chapter_id = isset($_GET['chapter_id']);
        $has_nonce = isset($_GET['nonce']);
        
        // If this is not a download request, skip it
        if (!$has_download_param || !$has_chapter_id || !$has_nonce) {
            return;
        }
        
        // Check if we're in admin (by checking if admin.php is in the request or page parameter)
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $is_admin_request = (strpos($request_uri, '/wp-admin/') !== false) || 
                           (isset($_GET['page']) && strpos($_GET['page'], 'academy-manager') !== false);
        
        if (!$is_admin_request) {
            return;
        }
        
        // IMPORTANT: Stop WordPress from outputting anything else
        // Remove all output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Disable WordPress default output
        remove_all_actions('wp_head');
        remove_all_actions('wp_footer');
        remove_all_actions('admin_head');
        remove_all_actions('admin_footer');
        
        // Check user capabilities
        if (!function_exists('current_user_can') || !current_user_can('manage_options')) {
            status_header(403);
            wp_die(__('You do not have permission to download this file.', 'academy-lesson-manager'));
        }
        
        // Verify nonce
        $nonce_check = wp_verify_nonce($_GET['nonce'], 'alm_download_chapter');
        
        if (!$nonce_check) {
            status_header(403);
            wp_die(__('Security check failed.', 'academy-lesson-manager'));
        }
        
        $chapter_id = intval($_GET['chapter_id']);
        
        if (!$chapter_id) {
            status_header(400);
            wp_die(__('Invalid chapter ID.', 'academy-lesson-manager'));
        }
        
        // Clear all output buffers before starting download
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Disable error display during download to prevent output
        $old_error_reporting = error_reporting(0);
        $old_display_errors = ini_get('display_errors');
        ini_set('display_errors', '0');
        
        try {
            $this->download_chapter_for_sample_by_id($chapter_id);
        } catch (Exception $e) {
            // Restore error settings
            error_reporting($old_error_reporting);
            ini_set('display_errors', $old_display_errors);
            
            status_header(500);
            wp_die(sprintf(__('Error downloading chapter: %s', 'academy-lesson-manager'), $e->getMessage()));
        } catch (Error $e) {
            // Restore error settings
            error_reporting($old_error_reporting);
            ini_set('display_errors', $old_display_errors);
            
            status_header(500);
            wp_die(sprintf(__('Fatal error downloading chapter: %s', 'academy-lesson-manager'), $e->getMessage()));
        }
        
        // Restore error settings (shouldn't reach here if download succeeds)
        error_reporting($old_error_reporting);
        ini_set('display_errors', $old_display_errors);
        
        exit;
    }
    
    /**
     * Handle admin actions
     */
    public function handle_actions() {
        // DISABLED: Debug logging
        // error_log('ALM Download Debug: handle_actions called');
        
        if (!current_user_can('manage_options')) {
            // error_log('ALM Download Debug: handle_actions - User does not have manage_options');
            return;
        }
        
        // Handle download chapter for sample FIRST (before any redirects)
        // Check if this is a download request (can come from any admin page)
        if (isset($_GET['download_chapter']) && isset($_GET['chapter_id']) && isset($_GET['nonce'])) {
            // error_log('ALM Download Debug: handle_actions - Download request detected');
            
            // Verify nonce
            $nonce_check = wp_verify_nonce($_GET['nonce'], 'alm_download_chapter');
            // error_log('ALM Download Debug: handle_actions - Nonce check = ' . ($nonce_check ? 'PASSED' : 'FAILED'));
            
            if (!$nonce_check) {
                wp_die(__('Security check failed.', 'academy-lesson-manager'));
            }
            
            $chapter_id = intval($_GET['chapter_id']);
            // error_log('ALM Download Debug: handle_actions - Calling download with chapter_id = ' . $chapter_id);
            
            $this->download_chapter_for_sample_by_id($chapter_id);
            exit;
        }
        
        // error_log('ALM Download Debug: handle_actions - No download request found');
        
        // Handle hide action (single or bulk)
        if (isset($_POST['alm_hide_lessons']) && isset($_POST['lesson_ids'])) {
            check_admin_referer('alm_hide_lessons', 'alm_hide_nonce');
            
            $lesson_ids = array_map('intval', $_POST['lesson_ids']);
            $this->hide_lessons($lesson_ids);
            
            wp_redirect(add_query_arg('message', 'lessons_hidden', admin_url('admin.php?page=academy-manager-lesson-samples')));
            exit;
        }
        
        // Handle delete hide meta
        if (isset($_POST['alm_delete_hide_meta'])) {
            check_admin_referer('alm_delete_hide_meta', 'alm_delete_hide_nonce');
            
            $this->delete_all_hide_meta();
            
            wp_redirect(add_query_arg('message', 'hide_meta_deleted', admin_url('admin.php?page=academy-manager-lesson-samples')));
            exit;
        }
        
    }
    
    /**
     * Hide lessons by setting meta
     * 
     * @param array $lesson_ids Array of lesson IDs
     */
    private function hide_lessons($lesson_ids) {
        foreach ($lesson_ids as $lesson_id) {
            // Get lesson to check if it has post_id
            $lesson = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT post_id FROM {$this->table_name} WHERE ID = %d",
                $lesson_id
            ));
            
            if ($lesson && $lesson->post_id) {
                // Use post meta if post_id exists
                update_post_meta($lesson->post_id, self::HIDE_META_KEY, '1');
            } else {
                // Store in option for lessons without post_id
                $hidden_lessons = get_option('alm_hidden_lessons_no_post', array());
                if (!in_array($lesson_id, $hidden_lessons)) {
                    $hidden_lessons[] = $lesson_id;
                    update_option('alm_hidden_lessons_no_post', $hidden_lessons);
                }
            }
        }
    }
    
    /**
     * Check if lesson is hidden
     * 
     * @param int $lesson_id Lesson ID
     * @param int|null $post_id Post ID (optional, will be fetched if not provided)
     * @return bool True if hidden
     */
    private function is_lesson_hidden($lesson_id, $post_id = null) {
        if ($post_id === null) {
            $lesson = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT post_id FROM {$this->table_name} WHERE ID = %d",
                $lesson_id
            ));
            $post_id = $lesson ? intval($lesson) : 0;
        }
        
        if ($post_id) {
            return get_post_meta($post_id, self::HIDE_META_KEY, true) === '1';
        } else {
            $hidden_lessons = get_option('alm_hidden_lessons_no_post', array());
            return in_array($lesson_id, $hidden_lessons);
        }
    }
    
    /**
     * Delete all hide meta entries
     */
    private function delete_all_hide_meta() {
        // Delete from post meta
        $this->wpdb->delete(
            $this->wpdb->postmeta,
            array('meta_key' => self::HIDE_META_KEY),
            array('%s')
        );
        
        // Delete from options
        delete_option('alm_hidden_lessons_no_post');
    }
    
    /**
     * Get all lessons (with or without samples)
     * 
     * @param int $per_page Items per page
     * @param int $offset Offset
     * @param string $filter Filter: 'all', 'with_samples', 'without_samples'
     * @param int $selected_lesson_id Selected lesson ID (0 for all)
     * @return array Array of lesson objects
     */
    private function get_all_lessons($per_page = 50, $offset = 0, $filter = 'without_samples', $selected_lesson_id = 0) {
        // Get hidden lesson IDs
        $hidden_post_ids = $this->wpdb->get_col($this->wpdb->prepare(
            "SELECT post_id FROM {$this->wpdb->postmeta} WHERE meta_key = %s AND meta_value = '1'",
            self::HIDE_META_KEY
        ));
        
        $hidden_lesson_ids = get_option('alm_hidden_lessons_no_post', array());
        
        // If we have hidden post_ids, get their lesson IDs
        if (!empty($hidden_post_ids)) {
            $placeholders = implode(',', array_fill(0, count($hidden_post_ids), '%d'));
            $hidden_by_post = $this->wpdb->get_col($this->wpdb->prepare(
                "SELECT ID FROM {$this->table_name} WHERE post_id IN ({$placeholders})",
                ...array_map('intval', $hidden_post_ids)
            ));
            $hidden_lesson_ids = array_merge($hidden_lesson_ids, $hidden_by_post);
        }
        
        $where_conditions = array();
        
        // Filter by sample status
        if ($filter === 'with_samples') {
            $where_conditions[] = "((l.sample_video_url IS NOT NULL AND l.sample_video_url != '' AND l.sample_video_url != '0') OR (l.sample_chapter_id IS NOT NULL AND l.sample_chapter_id > 0))";
        } elseif ($filter === 'without_samples') {
            $where_conditions[] = "((l.sample_video_url IS NULL OR l.sample_video_url = '' OR l.sample_video_url = '0') AND (l.sample_chapter_id IS NULL OR l.sample_chapter_id = 0))";
        }
        // 'all' doesn't add a filter condition
        
        // Filter by selected lesson
        if ($selected_lesson_id > 0) {
            $where_conditions[] = $this->wpdb->prepare("l.ID = %d", $selected_lesson_id);
        }
        
        // Exclude hidden lessons
        if (!empty($hidden_lesson_ids)) {
            $hidden_lesson_ids = array_unique(array_map('intval', $hidden_lesson_ids));
            $placeholders = implode(',', array_fill(0, count($hidden_lesson_ids), '%d'));
            $where_conditions[] = $this->wpdb->prepare("l.ID NOT IN ({$placeholders})", ...$hidden_lesson_ids);
        }
        
        $where = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : '';
        
        $sql = "SELECT l.ID, l.lesson_title, l.post_id, l.sample_video_url, l.sample_chapter_id
                FROM {$this->table_name} l
                {$where}
                ORDER BY l.ID ASC
                LIMIT %d OFFSET %d";
        
        $lessons = $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $per_page, $offset)
        );
        
        return $lessons ? $lessons : array();
    }
    
    /**
     * Get lessons without samples (and not hidden) - kept for backward compatibility
     * 
     * @param int $per_page Items per page
     * @param int $offset Offset
     * @return array Array of lesson objects
     */
    private function get_lessons_without_samples($per_page = 50, $offset = 0) {
        // Get hidden lesson IDs
        $hidden_post_ids = $this->wpdb->get_col($this->wpdb->prepare(
            "SELECT post_id FROM {$this->wpdb->postmeta} WHERE meta_key = %s AND meta_value = '1'",
            self::HIDE_META_KEY
        ));
        
        $hidden_lesson_ids = get_option('alm_hidden_lessons_no_post', array());
        
        // If we have hidden post_ids, get their lesson IDs
        if (!empty($hidden_post_ids)) {
            $placeholders = implode(',', array_fill(0, count($hidden_post_ids), '%d'));
            $hidden_by_post = $this->wpdb->get_col($this->wpdb->prepare(
                "SELECT ID FROM {$this->table_name} WHERE post_id IN ({$placeholders})",
                ...array_map('intval', $hidden_post_ids)
            ));
            $hidden_lesson_ids = array_merge($hidden_lesson_ids, $hidden_by_post);
        }
        
        $where_conditions = array(
            "((l.sample_video_url IS NULL OR l.sample_video_url = '' OR l.sample_video_url = '0') AND (l.sample_chapter_id IS NULL OR l.sample_chapter_id = 0))"
        );
        
        // Exclude hidden lessons
        if (!empty($hidden_lesson_ids)) {
            $hidden_lesson_ids = array_unique(array_map('intval', $hidden_lesson_ids));
            $placeholders = implode(',', array_fill(0, count($hidden_lesson_ids), '%d'));
            $where_conditions[] = $this->wpdb->prepare("l.ID NOT IN ({$placeholders})", ...$hidden_lesson_ids);
        }
        
        $where = "WHERE " . implode(' AND ', $where_conditions);
        
        $sql = "SELECT l.ID, l.lesson_title, l.post_id 
                FROM {$this->table_name} l
                {$where}
                ORDER BY l.ID ASC
                LIMIT %d OFFSET %d";
        
        $lessons = $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $per_page, $offset)
        );
        
        return $lessons ? $lessons : array();
    }
    
    /**
     * Get total count of lessons (with optional filter)
     * 
     * @param string $filter Filter: 'all', 'with_samples', 'without_samples'
     * @param int $selected_lesson_id Selected lesson ID (0 for all)
     * @return int Count
     */
    private function get_total_count($filter = 'without_samples', $selected_lesson_id = 0) {
        // Get hidden lesson IDs
        $hidden_post_ids = $this->wpdb->get_col($this->wpdb->prepare(
            "SELECT post_id FROM {$this->wpdb->postmeta} WHERE meta_key = %s AND meta_value = '1'",
            self::HIDE_META_KEY
        ));
        
        $hidden_lesson_ids = get_option('alm_hidden_lessons_no_post', array());
        
        // If we have hidden post_ids, get their lesson IDs
        if (!empty($hidden_post_ids)) {
            $hidden_by_post = $this->wpdb->get_col($this->wpdb->prepare(
                "SELECT ID FROM {$this->table_name} WHERE post_id IN (" . implode(',', array_map('intval', $hidden_post_ids)) . ")"
            ));
            $hidden_lesson_ids = array_merge($hidden_lesson_ids, $hidden_by_post);
        }
        
        $where_conditions = array();
        
        // Filter by sample status
        if ($filter === 'with_samples') {
            $where_conditions[] = "((l.sample_video_url IS NOT NULL AND l.sample_video_url != '' AND l.sample_video_url != '0') OR (l.sample_chapter_id IS NOT NULL AND l.sample_chapter_id > 0))";
        } elseif ($filter === 'without_samples') {
            $where_conditions[] = "((l.sample_video_url IS NULL OR l.sample_video_url = '' OR l.sample_video_url = '0') AND (l.sample_chapter_id IS NULL OR l.sample_chapter_id = 0))";
        }
        
        // Filter by selected lesson
        if ($selected_lesson_id > 0) {
            $where_conditions[] = $this->wpdb->prepare("l.ID = %d", $selected_lesson_id);
        }
        
        // Exclude hidden lessons
        if (!empty($hidden_lesson_ids)) {
            $hidden_lesson_ids = array_unique(array_map('intval', $hidden_lesson_ids));
            $placeholders = implode(',', array_fill(0, count($hidden_lesson_ids), '%d'));
            $where_conditions[] = $this->wpdb->prepare("l.ID NOT IN ({$placeholders})", ...$hidden_lesson_ids);
        }
        
        $where = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : '';
        
        $count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} l {$where}");
        
        return intval($count);
    }
    
    /**
     * Get Introduction chapter for a lesson
     * 
     * @param int $lesson_id Lesson ID
     * @return object|null Chapter object or null
     */
    private function get_introduction_chapter($lesson_id) {
        $chapters_table = $this->database->get_table_name('chapters');
        
        $chapter = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID, chapter_title, duration, bunny_url, vimeo_id, youtube_id 
             FROM {$chapters_table} 
             WHERE lesson_id = %d 
             AND LOWER(TRIM(chapter_title)) LIKE %s 
             ORDER BY menu_order ASC 
             LIMIT 1",
            $lesson_id,
            '%introduction%'
        ));
        
        return $chapter;
    }
    
    /**
     * Get shortest chapter for a lesson (with video)
     * 
     * @param int $lesson_id Lesson ID
     * @return object|null Chapter object or null
     */
    private function get_shortest_chapter($lesson_id) {
        $chapters_table = $this->database->get_table_name('chapters');
        
        $chapter = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID, chapter_title, duration, bunny_url, vimeo_id, youtube_id 
             FROM {$chapters_table} 
             WHERE lesson_id = %d 
             AND duration > 0
             AND ((bunny_url IS NOT NULL AND bunny_url != '') OR (vimeo_id > 0) OR (youtube_id IS NOT NULL AND youtube_id != ''))
             AND LOWER(TRIM(chapter_title)) NOT LIKE %s
             ORDER BY duration ASC 
             LIMIT 1",
            $lesson_id,
            '%scorch plug in%'
        ));
        
        return $chapter;
    }
    
    /**
     * Get lesson chapter count and total duration
     * 
     * @param int $lesson_id Lesson ID
     * @return array Array with 'count' and 'total_duration' keys
     */
    private function get_lesson_chapter_info($lesson_id) {
        $chapters_table = $this->database->get_table_name('chapters');
        
        $chapters = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT duration FROM {$chapters_table} WHERE lesson_id = %d",
            $lesson_id
        ));
        
        $total_duration = 0;
        foreach ($chapters as $chapter) {
            $total_duration += intval($chapter->duration);
        }
        
        return array(
            'count' => count($chapters),
            'total_duration' => $total_duration
        );
    }
    
    /**
     * AJAX handler for setting introduction as sample
     */
    public function ajax_set_intro_sample() {
        // Prevent any output before JSON response
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'alm_set_intro_sample')) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'));
            wp_die();
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            wp_die();
        }
        
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        
        if (!$lesson_id) {
            wp_send_json_error(array('message' => 'Invalid lesson ID'));
            wp_die();
        }
        
        // Debug: Check if introduction chapter exists
        $chapter = $this->get_introduction_chapter($lesson_id);
        if (!$chapter) {
            wp_send_json_error(array(
                'message' => 'No Introduction chapter found for this lesson.',
                'debug' => 'Lesson ID: ' . $lesson_id
            ));
            wp_die();
        }
        
        // Debug: Check if chapter has video
        $chapter_video_url = $this->get_chapter_video_url($chapter);
        if (!$chapter_video_url) {
            wp_send_json_error(array(
                'message' => 'Introduction chapter found but has no video URL. Check bunny_url, vimeo_id, or youtube_id.',
                'debug' => array(
                    'chapter_id' => $chapter->ID,
                    'chapter_title' => $chapter->chapter_title,
                    'has_bunny' => !empty($chapter->bunny_url),
                    'has_vimeo' => !empty($chapter->vimeo_id) && $chapter->vimeo_id > 0,
                    'has_youtube' => !empty($chapter->youtube_id)
                )
            ));
            wp_die();
        }
        
        $result = $this->set_introduction_as_sample($lesson_id);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'Introduction chapter has been set as the sample video.',
                'lesson_id' => $lesson_id,
                'chapter_id' => $chapter->ID,
                'video_url' => $chapter_video_url
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Database update failed. Check database connection and table structure.',
                'debug' => array(
                    'lesson_id' => $lesson_id,
                    'chapter_id' => $chapter->ID,
                    'video_url' => $chapter_video_url
                )
            ));
        }
        
        wp_die(); // Always die after AJAX response
    }
    
    /**
     * AJAX handler for setting shortest chapter as sample
     */
    public function ajax_set_shortest_sample() {
        // Prevent any output before JSON response
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'alm_set_shortest_sample')) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'));
            wp_die();
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            wp_die();
        }
        
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        
        if (!$lesson_id) {
            wp_send_json_error(array('message' => 'Invalid lesson ID'));
            wp_die();
        }
        
        // Check chapter count - don't allow if only 1 chapter
        $chapter_info = $this->get_lesson_chapter_info($lesson_id);
        if ($chapter_info['count'] <= 1) {
            wp_send_json_error(array(
                'message' => 'Cannot set sample for lesson with only 1 chapter (would give full access).',
                'debug' => 'Lesson ID: ' . $lesson_id . ', Chapter count: ' . $chapter_info['count']
            ));
            wp_die();
        }
        
        // Debug: Check if shortest chapter exists
        $chapter = $this->get_shortest_chapter($lesson_id);
        if (!$chapter) {
            wp_send_json_error(array(
                'message' => 'No shortest chapter found for this lesson.',
                'debug' => 'Lesson ID: ' . $lesson_id
            ));
            wp_die();
        }
        
        // Debug: Check if chapter has video
        $chapter_video_url = $this->get_chapter_video_url($chapter);
        if (!$chapter_video_url) {
            wp_send_json_error(array(
                'message' => 'Shortest chapter found but has no video URL. Check bunny_url, vimeo_id, or youtube_id.',
                'debug' => array(
                    'chapter_id' => $chapter->ID,
                    'chapter_title' => $chapter->chapter_title,
                    'has_bunny' => !empty($chapter->bunny_url),
                    'has_vimeo' => !empty($chapter->vimeo_id) && $chapter->vimeo_id > 0,
                    'has_youtube' => !empty($chapter->youtube_id)
                )
            ));
            wp_die();
        }
        
        $result = $this->set_chapter_as_sample($lesson_id, $chapter->ID, $chapter_video_url);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'Shortest chapter has been set as the sample video.',
                'lesson_id' => $lesson_id,
                'chapter_id' => $chapter->ID,
                'video_url' => $chapter_video_url
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Database update failed. Check database connection and table structure.',
                'debug' => array(
                    'lesson_id' => $lesson_id,
                    'chapter_id' => $chapter->ID,
                    'video_url' => $chapter_video_url
                )
            ));
        }
        
        wp_die(); // Always die after AJAX response
    }
    
    /**
     * Set a chapter as sample video for a lesson (generic method)
     * 
     * @param int $lesson_id Lesson ID
     * @param int $chapter_id Chapter ID
     * @param string $chapter_video_url Chapter video URL
     * @return bool True on success, false on failure
     */
    private function set_chapter_as_sample($lesson_id, $chapter_id, $chapter_video_url) {
        // Update lesson with sample chapter
        $result = $this->wpdb->update(
            $this->table_name,
            array(
                'sample_chapter_id' => $chapter_id,
                'sample_video_url' => $chapter_video_url
            ),
            array('ID' => $lesson_id),
            array('%d', '%s'),
            array('%d')
        );
        
        // Check if update was successful (result can be 0 if no change, false on error)
        if ($result === false) {
            return false;
        }
        
        // Sync to WordPress post if exists
        $lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT post_id FROM {$this->table_name} WHERE ID = %d",
            $lesson_id
        ));
        
        if ($lesson && $lesson->post_id) {
            $sync = new ALM_Post_Sync();
            $sync->sync_lesson_to_post($lesson_id);
        }
        
        // Return true if update succeeded (even if 0 rows changed, meaning it was already set)
        return true;
    }
    
    /**
     * Set Introduction chapter as sample video for a lesson
     * 
     * @param int $lesson_id Lesson ID
     * @return bool True on success, false on failure
     */
    private function set_introduction_as_sample($lesson_id) {
        $chapter = $this->get_introduction_chapter($lesson_id);
        
        if (!$chapter) {
            return false;
        }
        
        // Get chapter video URL
        $chapter_video_url = $this->get_chapter_video_url($chapter);
        
        if (!$chapter_video_url) {
            return false;
        }
        
        return $this->set_chapter_as_sample($lesson_id, $chapter->ID, $chapter_video_url);
    }
    
    /**
     * Get chapter video URL (similar to method in class-admin-lessons.php)
     * 
     * @param object $chapter Chapter object
     * @return string|false Video URL or false
     */
    private function get_chapter_video_url($chapter) {
        if (!empty($chapter->bunny_url)) {
            return $chapter->bunny_url;
        }
        
        if (!empty($chapter->vimeo_id) && $chapter->vimeo_id > 0) {
            return 'https://vimeo.com/' . intval($chapter->vimeo_id);
        }
        
        if (!empty($chapter->youtube_id)) {
            return 'https://www.youtube.com/watch?v=' . esc_attr($chapter->youtube_id);
        }
        
        return false;
    }
    
    /**
     * Render the admin page
     */
    public function render_page() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Handle messages
        $message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
        
        // Get filter and selected lesson
        $filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'without_samples';
        $selected_lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
        
        // Pagination
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 50;
        $offset = ($paged - 1) * $per_page;
        
        // Get lessons
        $lessons = $this->get_all_lessons($per_page, $offset, $filter, $selected_lesson_id);
        $total = $this->get_total_count($filter, $selected_lesson_id);
        $total_pages = ceil($total / $per_page);
        
        // Get all lessons for dropdown
        $all_lessons = $this->wpdb->get_results(
            "SELECT ID, lesson_title, sample_video_url, sample_chapter_id 
             FROM {$this->table_name} 
             ORDER BY lesson_title ASC"
        );
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Lesson Samples Manager', 'academy-lesson-manager'); ?></h1>
            
            <?php if ($message === 'lessons_hidden'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html__('Lessons have been hidden from the list.', 'academy-lesson-manager'); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($message === 'hide_meta_deleted'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html__('All hide meta entries have been deleted. Hidden lessons will now appear in the list again.', 'academy-lesson-manager'); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($message === 'intro_sample_set'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html__('Introduction chapter has been set as the sample video.', 'academy-lesson-manager'); ?></p>
            </div>
            <?php endif; ?>
            
            <div style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 4px; margin: 20px 0;">
                <p style="margin: 0 0 10px 0;">
                    <strong>Purpose:</strong> This page helps you manage sample videos for lessons. Use the filters below to find lessons with or without samples.
                </p>
                <p style="margin: 0;">
                    <strong>Total lessons shown:</strong> <strong style="color: #F04E23;"><?php echo esc_html($total); ?></strong>
                </p>
            </div>
            
            <!-- Filters -->
            <div style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 4px; margin: 20px 0;">
                <form method="get" action="" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <input type="hidden" name="page" value="academy-manager-lesson-samples" />
                    
                    <div style="flex: 1; min-width: 200px;">
                        <label for="lesson-dropdown" style="display: block; margin-bottom: 5px; font-weight: 600;"><?php echo esc_html__('Select Lesson:', 'academy-lesson-manager'); ?></label>
                        <select id="lesson-dropdown" name="lesson_id" style="width: 100%; padding: 8px;">
                            <option value="0"><?php echo esc_html__('All Lessons', 'academy-lesson-manager'); ?></option>
                            <?php foreach ($all_lessons as $lesson_option): 
                                $has_sample = (!empty($lesson_option->sample_video_url) && $lesson_option->sample_video_url != '0') || (!empty($lesson_option->sample_chapter_id) && $lesson_option->sample_chapter_id > 0);
                                $selected = ($selected_lesson_id == $lesson_option->ID) ? 'selected' : '';
                                $sample_indicator = $has_sample ? ' ✓' : '';
                            ?>
                                <option value="<?php echo esc_attr($lesson_option->ID); ?>" <?php echo $selected; ?>>
                                    <?php echo esc_html(stripslashes($lesson_option->lesson_title)); ?> (ID: <?php echo esc_html($lesson_option->ID); ?>)<?php echo $sample_indicator; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="flex: 1; min-width: 200px;">
                        <label for="filter-dropdown" style="display: block; margin-bottom: 5px; font-weight: 600;"><?php echo esc_html__('Filter by Sample Status:', 'academy-lesson-manager'); ?></label>
                        <select id="filter-dropdown" name="filter" style="width: 100%; padding: 8px;">
                            <option value="all" <?php selected($filter, 'all'); ?>><?php echo esc_html__('All Lessons', 'academy-lesson-manager'); ?></option>
                            <option value="with_samples" <?php selected($filter, 'with_samples'); ?>><?php echo esc_html__('With Samples', 'academy-lesson-manager'); ?></option>
                            <option value="without_samples" <?php selected($filter, 'without_samples'); ?>><?php echo esc_html__('Without Samples', 'academy-lesson-manager'); ?></option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="button button-primary"><?php echo esc_html__('Apply Filters', 'academy-lesson-manager'); ?></button>
                        <?php if ($selected_lesson_id > 0 || $filter !== 'without_samples'): ?>
                            <a href="<?php echo admin_url('admin.php?page=academy-manager-lesson-samples'); ?>" class="button"><?php echo esc_html__('Reset', 'academy-lesson-manager'); ?></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <?php if (!empty($lessons)): ?>
            <div style="margin: 20px 0;">
                <button type="button" class="button" id="alm-select-all">Select All</button>
                <button type="button" class="button" id="alm-deselect-all">Deselect All</button>
            </div>
            
            <form method="post" action="" id="alm-samples-form">
                <?php wp_nonce_field('alm_hide_lessons', 'alm_hide_nonce'); ?>
                
                <div style="margin-bottom: 10px;">
                    <button type="submit" name="alm_hide_lessons" class="button" onclick="return confirm('Hide selected lessons from this list?');">Hide Selected</button>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" id="alm-check-all" />
                            </th>
                            <th style="width: 80px;">ID</th>
                            <th>Lesson Title</th>
                            <th style="width: 100px;">Has Sample</th>
                            <th style="width: 200px;">Introduction Chapter</th>
                            <th style="width: 250px;">Shortest Chapter</th>
                            <th style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lessons as $lesson): 
                            // Check if lesson has sample
                            $has_sample = (!empty($lesson->sample_video_url) && $lesson->sample_video_url != '0') || (!empty($lesson->sample_chapter_id) && $lesson->sample_chapter_id > 0);
                            
                            $intro_chapter = $this->get_introduction_chapter($lesson->ID);
                            $has_video = false;
                            if ($intro_chapter) {
                                $has_video = !empty($intro_chapter->bunny_url) || ($intro_chapter->vimeo_id > 0) || !empty($intro_chapter->youtube_id);
                            }
                            
                            $shortest_chapter = $this->get_shortest_chapter($lesson->ID);
                            $chapter_info = $this->get_lesson_chapter_info($lesson->ID);
                            $shortest_has_video = false;
                            if ($shortest_chapter) {
                                $shortest_has_video = !empty($shortest_chapter->bunny_url) || ($shortest_chapter->vimeo_id > 0) || !empty($shortest_chapter->youtube_id);
                            }
                        ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="lesson_ids[]" value="<?php echo esc_attr($lesson->ID); ?>" class="alm-lesson-checkbox" />
                            </td>
                            <td><?php echo esc_html($lesson->ID); ?></td>
                            <td><strong><?php echo esc_html(stripslashes($lesson->lesson_title)); ?></strong></td>
                            <td style="text-align: center;">
                                <?php if ($has_sample): ?>
                                    <span style="color: #28a745; font-weight: bold; font-size: 18px;" title="Has Sample Video">✓</span>
                                <?php else: ?>
                                    <span style="color: #dc3545; font-weight: bold; font-size: 18px;" title="No Sample Video">✗</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($intro_chapter): 
                                    $duration = intval($intro_chapter->duration);
                                    $duration_display = $duration > 0 ? ALM_Helpers::format_duration_short($duration) : '';
                                ?>
                                    <div style="font-size: 13px;">
                                        <strong><?php echo esc_html(stripslashes($intro_chapter->chapter_title)); ?></strong>
                                        <?php if ($duration_display): ?>
                                            <span style="color: #666;">(<?php echo esc_html($duration_display); ?>)</span>
                                        <?php endif; ?>
                                        <?php if (!$has_video): ?>
                                            <span style="color: #dc3232; font-size: 11px;"> - No video</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($has_video && (!empty($intro_chapter->bunny_url) || ($intro_chapter->vimeo_id > 0))): ?>
                                        <?php
                                        $download_url = wp_nonce_url(
                                            add_query_arg(array(
                                                'page' => 'academy-manager-lesson-samples',
                                                'download_chapter' => '1',
                                                'chapter_id' => $intro_chapter->ID
                                            ), admin_url('admin.php')),
                                            'alm_download_chapter',
                                            'nonce'
                                        );
                                        ?>
                                        <div style="margin-top: 5px;">
                                            <a href="<?php echo esc_url($download_url); ?>" class="button button-small button-secondary" title="<?php echo esc_attr__('Download Chapter for Sample', 'academy-lesson-manager'); ?>">
                                                <?php echo __('Download', 'academy-lesson-manager'); ?>
                                            </a>
                                        </div>
                                    <?php elseif ($has_video && !empty($intro_chapter->youtube_id)): ?>
                                        <div style="margin-top: 5px;">
                                            <span class="button button-small" style="cursor: help; opacity: 0.7;" title="<?php echo esc_attr__('YouTube videos cannot be downloaded directly.', 'academy-lesson-manager'); ?>">
                                                <?php echo __('YouTube', 'academy-lesson-manager'); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($chapter_info['count'] == 1): ?>
                                    <div style="font-size: 13px; color: #dc3232;">
                                        <strong>1 chapter only</strong>
                                        <br><span style="font-size: 11px; color: #666;">(Would give full access)</span>
                                    </div>
                                    <?php if ($shortest_chapter && $shortest_has_video): ?>
                                        <?php
                                        $has_bunny_or_vimeo = !empty($shortest_chapter->bunny_url) || ($shortest_chapter->vimeo_id > 0);
                                        $has_youtube = !empty($shortest_chapter->youtube_id);
                                        ?>
                                        <?php if ($has_bunny_or_vimeo): ?>
                                            <?php
                                            $download_url = wp_nonce_url(
                                                add_query_arg(array(
                                                    'page' => 'academy-manager-lesson-samples',
                                                    'download_chapter' => '1',
                                                    'chapter_id' => $shortest_chapter->ID
                                                ), admin_url('admin.php')),
                                                'alm_download_chapter',
                                                'nonce'
                                            );
                                            ?>
                                            <div style="margin-top: 5px;">
                                                <a href="<?php echo esc_url($download_url); ?>" class="button button-small button-secondary" title="<?php echo esc_attr__('Download Chapter for Sample', 'academy-lesson-manager'); ?>">
                                                    <?php echo __('Download', 'academy-lesson-manager'); ?>
                                                </a>
                                            </div>
                                        <?php elseif ($has_youtube): ?>
                                            <div style="margin-top: 5px;">
                                                <span class="button button-small" style="cursor: help; opacity: 0.7;" title="<?php echo esc_attr__('YouTube videos cannot be downloaded directly.', 'academy-lesson-manager'); ?>">
                                                    <?php echo __('YouTube', 'academy-lesson-manager'); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php elseif ($shortest_chapter && $shortest_has_video): 
                                    $shortest_duration = intval($shortest_chapter->duration);
                                    $shortest_duration_display = $shortest_duration > 0 ? ALM_Helpers::format_duration_short($shortest_duration) : '';
                                    $total_duration_display = $chapter_info['total_duration'] > 0 ? ALM_Helpers::format_duration_short($chapter_info['total_duration']) : '';
                                    $percentage = $chapter_info['total_duration'] > 0 ? round(($shortest_duration / $chapter_info['total_duration']) * 100, 1) : 0;
                                    $is_over_10_percent = $percentage > 10;
                                ?>
                                    <div style="font-size: 13px;">
                                        <strong><?php echo esc_html(stripslashes($shortest_chapter->chapter_title)); ?></strong>
                                        <?php if ($shortest_duration_display): ?>
                                            <span style="color: #666;">(<?php echo esc_html($shortest_duration_display); ?>)</span>
                                        <?php endif; ?>
                                        <br>
                                        <span style="font-size: 11px; <?php echo $is_over_10_percent ? 'color: #dc3232; font-weight: bold;' : 'color: #666;'; ?>">
                                            Total: <?php echo esc_html($total_duration_display); ?>
                                            <?php if ($percentage > 0): ?>
                                                (<?php echo esc_html($percentage); ?>%)
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <?php
                                    $has_bunny_or_vimeo = !empty($shortest_chapter->bunny_url) || ($shortest_chapter->vimeo_id > 0);
                                    $has_youtube = !empty($shortest_chapter->youtube_id);
                                    ?>
                                    <?php if ($has_bunny_or_vimeo): ?>
                                        <?php
                                        $download_url = wp_nonce_url(
                                            add_query_arg(array(
                                                'page' => 'academy-manager-lesson-samples',
                                                'download_chapter' => '1',
                                                'chapter_id' => $shortest_chapter->ID
                                            ), admin_url('admin.php')),
                                            'alm_download_chapter',
                                            'nonce'
                                        );
                                        ?>
                                        <div style="margin-top: 5px;">
                                            <a href="<?php echo esc_url($download_url); ?>" class="button button-small button-secondary" title="<?php echo esc_attr__('Download Chapter for Sample', 'academy-lesson-manager'); ?>">
                                                <?php echo __('Download', 'academy-lesson-manager'); ?>
                                            </a>
                                        </div>
                                    <?php elseif ($has_youtube): ?>
                                        <div style="margin-top: 5px;">
                                            <span class="button button-small" style="cursor: help; opacity: 0.7;" title="<?php echo esc_attr__('YouTube videos cannot be downloaded directly.', 'academy-lesson-manager'); ?>">
                                                <?php echo __('YouTube', 'academy-lesson-manager'); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <?php if ($intro_chapter && $has_video): ?>
                                        <button type="button" 
                                                class="button button-small button-primary alm-set-intro-btn" 
                                                data-lesson-id="<?php echo esc_attr($lesson->ID); ?>"
                                                data-nonce="<?php echo wp_create_nonce('alm_set_intro_sample'); ?>"
                                                title="Set Introduction as Sample">
                                            Use Intro
                                        </button>
                                    <?php endif; ?>
                                    <?php 
                                    // Only show button if chapter count > 1, has shortest chapter with video, and percentage <= 35%
                                    $show_shortest_button = false;
                                    if ($chapter_info['count'] > 1 && $shortest_chapter && $shortest_has_video) {
                                        $shortest_duration = intval($shortest_chapter->duration);
                                        $percentage = $chapter_info['total_duration'] > 0 ? round(($shortest_duration / $chapter_info['total_duration']) * 100, 1) : 0;
                                        $show_shortest_button = $percentage <= 35;
                                    }
                                    if ($show_shortest_button): ?>
                                        <button type="button" 
                                                class="button button-small button-primary alm-set-shortest-btn" 
                                                data-lesson-id="<?php echo esc_attr($lesson->ID); ?>"
                                                data-nonce="<?php echo wp_create_nonce('alm_set_shortest_sample'); ?>"
                                                title="Set Shortest Chapter as Sample">
                                            Use Shortest
                                        </button>
                                    <?php endif; ?>
                                    <a href="<?php echo admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . $lesson->ID . '#sample-video'); ?>" 
                                       class="button button-small alm-edit-lesson-btn" 
                                       target="_blank" 
                                       data-lesson-id="<?php echo esc_attr($lesson->ID); ?>">
                                        Edit Lesson
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
            
            <?php if ($total_pages > 1): ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php
                    $pagination_args = array(
                        'base' => add_query_arg(array('paged' => '%#%', 'filter' => $filter, 'lesson_id' => $selected_lesson_id), admin_url('admin.php?page=academy-manager-lesson-samples')),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $total_pages,
                        'current' => $paged
                    );
                    echo paginate_links($pagination_args);
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="notice notice-info">
                <p><?php echo esc_html__('No lessons found matching the current filters.', 'academy-lesson-manager'); ?></p>
            </div>
            <?php endif; ?>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin-top: 30px; max-width: 600px;">
                <h3 style="margin-top: 0;">Delete Hide Meta</h3>
                <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                    <strong>What this does:</strong> Removes all hide flags from lessons. This will make all previously hidden lessons appear in the list again. This is useful when you're done adding samples and want to clean up.
                </p>
                <form method="post" action="" onsubmit="return confirm('Are you sure you want to delete all hide meta? This will restore all hidden lessons to the list.');">
                    <?php wp_nonce_field('alm_delete_hide_meta', 'alm_delete_hide_nonce'); ?>
                    <button type="submit" name="alm_delete_hide_meta" class="button button-secondary">
                        Delete All Hide Meta
                    </button>
                </form>
            </div>
        </div>
        
        <style>
        .alm-lesson-row-processed {
            background-color: #e8f5e9 !important;
            opacity: 0.7;
        }
        .alm-lesson-row-processed:hover {
            background-color: #c8e6c9 !important;
        }
        .alm-lesson-row-success {
            background-color: #d4edda !important;
            transition: background-color 0.3s ease;
        }
        .alm-set-intro-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        </style>
        
        <script>
        (function() {
            var checkAll = document.getElementById('alm-check-all');
            var selectAllBtn = document.getElementById('alm-select-all');
            var deselectAllBtn = document.getElementById('alm-deselect-all');
            var checkboxes = document.querySelectorAll('.alm-lesson-checkbox');
            
            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    checkboxes.forEach(function(cb) {
                        cb.checked = this.checked;
                    }, this);
                });
            }
            
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    checkboxes.forEach(function(cb) {
                        cb.checked = true;
                    });
                    if (checkAll) checkAll.checked = true;
                });
            }
            
            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', function() {
                    checkboxes.forEach(function(cb) {
                        cb.checked = false;
                    });
                    if (checkAll) checkAll.checked = false;
                });
            }
            
            // Handle edit button clicks - shade the row
            var editButtons = document.querySelectorAll('.alm-edit-lesson-btn');
            editButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var row = this.closest('tr');
                    if (row) {
                        row.classList.add('alm-lesson-row-processed');
                    }
                });
            });
            
            // Handle "Use Intro" button clicks with AJAX
            var setIntroButtons = document.querySelectorAll('.alm-set-intro-btn');
            setIntroButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var button = this;
                    var row = button.closest('tr');
                    var lessonId = button.getAttribute('data-lesson-id');
                    var nonce = button.getAttribute('data-nonce');
                    var originalText = button.textContent;
                    
                    // Disable button and show loading state
                    button.disabled = true;
                    button.textContent = 'Setting...';
                    
                    // Shade row green immediately
                    if (row) {
                        row.classList.add('alm-lesson-row-success');
                    }
                    
                    // Make AJAX request
                    var formData = new FormData();
                    formData.append('action', 'alm_set_intro_sample');
                    formData.append('lesson_id', lessonId);
                    formData.append('nonce', nonce);
                    
                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            // Success - update button and row
                            button.textContent = '✓ Set';
                            button.classList.remove('button-primary');
                            button.classList.add('button-secondary');
                            
                            // Keep green shading
                            if (row) {
                                row.classList.remove('alm-lesson-row-success');
                                row.style.backgroundColor = '#d4edda';
                            }
                            
                            // Optionally fade out the row after a delay
                            setTimeout(function() {
                                if (row) {
                                    row.style.opacity = '0.5';
                                    row.style.transition = 'opacity 0.5s ease';
                                }
                            }, 2000);
                        } else {
                            // Error - restore button and show error
                            button.disabled = false;
                            button.textContent = originalText;
                            if (row) {
                                row.classList.remove('alm-lesson-row-success');
                            }
                            
                            // Build detailed error message
                            var errorMsg = 'Error: ' + (data.data && data.data.message ? data.data.message : 'Failed to set introduction as sample');
                            
                            // Add debug info if available
                            if (data.data && data.data.debug) {
                                console.error('Debug info:', data.data.debug);
                                if (typeof data.data.debug === 'object') {
                                    errorMsg += '\n\nDebug details:\n' + JSON.stringify(data.data.debug, null, 2);
                                } else {
                                    errorMsg += '\n\n' + data.data.debug;
                                }
                            }
                            
                            alert(errorMsg);
                            console.error('AJAX Error Response:', data);
                        }
                    })
                    .catch(function(error) {
                        // Network error - restore button
                        button.disabled = false;
                        button.textContent = originalText;
                        if (row) {
                            row.classList.remove('alm-lesson-row-success');
                        }
                        alert('Network error. Please try again.');
                        console.error('Error:', error);
                    });
                });
            });
            
            // Handle "Use Shortest" button clicks with AJAX
            var setShortestButtons = document.querySelectorAll('.alm-set-shortest-btn');
            setShortestButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var button = this;
                    var row = button.closest('tr');
                    var lessonId = button.getAttribute('data-lesson-id');
                    var nonce = button.getAttribute('data-nonce');
                    var originalText = button.textContent;
                    
                    // Disable button and show loading state
                    button.disabled = true;
                    button.textContent = 'Setting...';
                    
                    // Shade row green immediately
                    if (row) {
                        row.classList.add('alm-lesson-row-success');
                    }
                    
                    // Make AJAX request
                    var formData = new FormData();
                    formData.append('action', 'alm_set_shortest_sample');
                    formData.append('lesson_id', lessonId);
                    formData.append('nonce', nonce);
                    
                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            // Success - update button and row
                            button.textContent = '✓ Set';
                            button.classList.remove('button-primary');
                            button.classList.add('button-secondary');
                            
                            // Keep green shading
                            if (row) {
                                row.classList.remove('alm-lesson-row-success');
                                row.style.backgroundColor = '#d4edda';
                            }
                            
                            // Optionally fade out the row after a delay
                            setTimeout(function() {
                                if (row) {
                                    row.style.opacity = '0.5';
                                    row.style.transition = 'opacity 0.5s ease';
                                }
                            }, 2000);
                        } else {
                            // Error - restore button and show error
                            button.disabled = false;
                            button.textContent = originalText;
                            if (row) {
                                row.classList.remove('alm-lesson-row-success');
                            }
                            
                            // Build detailed error message
                            var errorMsg = 'Error: ' + (data.data && data.data.message ? data.data.message : 'Failed to set shortest chapter as sample');
                            
                            // Add debug info if available
                            if (data.data && data.data.debug) {
                                console.error('Debug info:', data.data.debug);
                                if (typeof data.data.debug === 'object') {
                                    errorMsg += '\n\nDebug details:\n' + JSON.stringify(data.data.debug, null, 2);
                                } else {
                                    errorMsg += '\n\n' + data.data.debug;
                                }
                            }
                            
                            alert(errorMsg);
                            console.error('AJAX Error Response:', data);
                        }
                    })
                    .catch(function(error) {
                        // Network error - restore button
                        button.disabled = false;
                        button.textContent = originalText;
                        if (row) {
                            row.classList.remove('alm-lesson-row-success');
                        }
                        alert('Network error. Please try again.');
                        console.error('Error:', error);
                    });
                });
            });
        })();
        </script>
        <?php
    }
    
    /**
     * Get single chapter for a lesson (when lesson has only one chapter)
     * 
     * @param int $lesson_id Lesson ID
     * @return object|null Chapter object or null
     */
    private function get_single_chapter($lesson_id) {
        $chapters_table = $this->database->get_table_name('chapters');
        
        $chapter = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID, chapter_title, duration, bunny_url, vimeo_id, youtube_id 
             FROM {$chapters_table} 
             WHERE lesson_id = %d 
             ORDER BY menu_order ASC 
             LIMIT 1",
            $lesson_id
        ));
        
        return $chapter;
    }
    
    /**
     * Download chapter video for sample creation by chapter ID
     * 
     * @param int $chapter_id Chapter ID
     */
    public function download_chapter_for_sample_by_id($chapter_id) {
        // Prevent any output buffering issues
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Ensure no headers have been sent
        if (headers_sent($file, $line)) {
            throw new Exception(sprintf(__('Headers already sent in %s on line %d. Cannot start download.', 'academy-lesson-manager'), $file, $line));
        }
        
        // Clear any existing headers
        if (!headers_sent()) {
            header_remove();
        }
        
        $chapters_table = $this->database->get_table_name('chapters');
        
        // DISABLED: Debug logging
        // $debug_msg = 'ALM Download Debug: Getting chapter info for chapter_id = ' . $chapter_id . "\n";
        // error_log($debug_msg);
        // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
        
        // Get chapter info
        $chapter = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT c.ID, c.chapter_title, c.bunny_url, c.vimeo_id, c.youtube_id, c.lesson_id, l.lesson_title 
             FROM {$chapters_table} c
             LEFT JOIN {$this->table_name} l ON c.lesson_id = l.ID
             WHERE c.ID = %d",
            $chapter_id
        ));
        
        if (!$chapter) {
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: Chapter not found for chapter_id = ' . $chapter_id . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            wp_die(__('Chapter not found.', 'academy-lesson-manager'));
        }
        
        // DISABLED: Debug logging
        // $debug_msg = 'ALM Download Debug: Chapter found - bunny_url: ' . (!empty($chapter->bunny_url) ? 'YES' : 'NO') . ', vimeo_id: ' . $chapter->vimeo_id . ', youtube_id: ' . (!empty($chapter->youtube_id) ? $chapter->youtube_id : 'NO') . "\n";
        // error_log($debug_msg);
        // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
        
        // Check if chapter has video
        $video_url = $this->get_chapter_video_url($chapter);
        
        if (!$video_url) {
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: Chapter does not have a video URL' . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            wp_die(__('Chapter does not have a video.', 'academy-lesson-manager'));
        }
        
        // DISABLED: Debug logging
        // $debug_msg = 'ALM Download Debug: Video URL found: ' . $video_url . "\n";
        // error_log($debug_msg);
        // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
        
        // Generate filename: {lessonid}-{chapterid}-id{chapterid}-{lesson-slug}
        $lesson_slug = sanitize_file_name($chapter->lesson_title);
        $lesson_slug = str_replace(' ', '-', $lesson_slug);
        $lesson_slug = preg_replace('/[^a-z0-9\-]/i', '', $lesson_slug);
        $filename = sprintf('%d-%d-id%d-%s', 
            $chapter->lesson_id, 
            $chapter->ID, 
            $chapter->ID, 
            $lesson_slug
        );
        
        // DISABLED: Debug logging
        // $debug_msg = 'ALM Download Debug: Generated filename: ' . $filename . "\n";
        // error_log($debug_msg);
        // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
        
        // Determine video source and handle download
        if (!empty($chapter->bunny_url)) {
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: Calling download_bunny_video' . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            // Bunny.net video - try to download via API
            $this->download_bunny_video($chapter->bunny_url, $filename);
        } elseif (!empty($chapter->vimeo_id) && $chapter->vimeo_id > 0) {
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: Calling download_vimeo_video with vimeo_id = ' . $chapter->vimeo_id . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            // Vimeo video - download via Vimeo API
            $this->download_vimeo_video($chapter->vimeo_id, $filename);
        } elseif (!empty($chapter->youtube_id)) {
            // YouTube video - provide download link/instructions
            wp_die(sprintf(
                __('YouTube videos cannot be downloaded directly. Please use a YouTube download tool or contact support. Video ID: %s', 'academy-lesson-manager'),
                esc_html($chapter->youtube_id)
            ));
        } else {
            wp_die(__('Unable to determine video source.', 'academy-lesson-manager'));
        }
    }
    
    /**
     * Download Bunny.net video
     * 
     * @param string $bunny_url Bunny.net video URL
     * @param string $filename Desired filename
     */
    private function download_bunny_video($bunny_url, $filename) {
        // Prevent any output buffering issues
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Extract video ID from URL
        $bunny_api = new ALM_Bunny_API();
        $video_id = $bunny_api->extract_video_id_from_url($bunny_url);
        
        if (!$video_id) {
            wp_die(__('Could not extract video ID from Bunny.net URL.', 'academy-lesson-manager'));
        }
        
        // Get library ID and API key
        $library_id = get_option('alm_bunny_library_id', '');
        $api_key = get_option('alm_bunny_api_key', '');
        
        if (empty($library_id) || empty($api_key)) {
            wp_die(__('Bunny.net API is not configured. Please configure it in Lesson Manager settings.', 'academy-lesson-manager'));
        }
        
        // Get video metadata from Bunny.net API to find source file
        $api_base_url = 'https://video.bunnycdn.com';
        $url = $api_base_url . '/library/' . $library_id . '/videos/' . $video_id;
        
        $args = array(
            'headers' => array(
                'AccessKey' => $api_key,
                'accept' => 'application/json'
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            wp_die(sprintf(
                __('Error fetching video metadata from Bunny.net: %s', 'academy-lesson-manager'),
                $response->get_error_message()
            ));
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            wp_die(sprintf(
                __('Bunny.net API returned error status: %d', 'academy-lesson-manager'),
                $status_code
            ));
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_die(__('Failed to parse JSON response from Bunny.net API.', 'academy-lesson-manager'));
        }
        
        // Construct MP4 URL using Bunny.net's MP4 fallback URL pattern:
        // https://{pull_zone_hostname}/{video_id}/play_{height}p.mp4
        // Most common: play_720p.mp4
        
        // Get pull zone hostname (playback hostname)
        $pull_zone_hostname = null;
        
        // First, try to extract from bunny_url
        $parsed = parse_url($bunny_url);
        if (isset($parsed['host']) && strpos($parsed['host'], 'vz-') === 0) {
            $pull_zone_hostname = $parsed['host'];
        }
        
        // If not found, try to get from settings or API response
        if (!$pull_zone_hostname) {
            $cdn_hostname = get_option('bunny_video_webhook_cdn_hostname', '');
            if (!empty($cdn_hostname) && strpos($cdn_hostname, 'vz-') === 0) {
                $pull_zone_hostname = $cdn_hostname;
            }
        }
        
        // If still not found, check API response for hostname
        if (!$pull_zone_hostname && isset($data['hostname']) && !empty($data['hostname'])) {
            $pull_zone_hostname = $data['hostname'];
        }
        
        // Last resort: try to extract from any URL in the response
        if (!$pull_zone_hostname && isset($data['thumbnailFileName'])) {
            // Sometimes the thumbnail URL contains the hostname
            if (isset($data['thumbnailUrl']) && !empty($data['thumbnailUrl'])) {
                $thumb_parsed = parse_url($data['thumbnailUrl']);
                if (isset($thumb_parsed['host']) && strpos($thumb_parsed['host'], 'vz-') === 0) {
                    $pull_zone_hostname = $thumb_parsed['host'];
                }
            }
        }
        
        if (!$pull_zone_hostname) {
            wp_die(__('Could not determine Bunny.net pull zone hostname. Please check your Bunny.net URL format or settings.', 'academy-lesson-manager'));
        }
        
        // Construct the MP4 URL using the correct pattern
        // Use 720p as default (most common)
        $download_url = sprintf('https://%s/%s/play_720p.mp4', $pull_zone_hostname, $video_id);
        
        // Stream the file from Bunny.net with download headers to force download
        // Clear any output buffers first
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Disable error display
        @ini_set('display_errors', 0);
        
        // Set headers to force download
        if (!headers_sent()) {
            // Generate filename from chapter title or use video ID
            $safe_filename = sanitize_file_name($filename);
            if (empty($safe_filename)) {
                $safe_filename = 'video-' . $video_id;
            }
            if (strpos($safe_filename, '.mp4') === false) {
                $safe_filename .= '.mp4';
            }
            
            // Set download headers
            header('Content-Type: video/mp4');
            header('Content-Disposition: attachment; filename="' . $safe_filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            
            // Stream the file from Bunny.net with proper headers to avoid 403
            $ch = curl_init($download_url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HEADER => false,
                CURLOPT_FAILONERROR => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                CURLOPT_REFERER => home_url(),
                CURLOPT_HTTPHEADER => array(
                    'Accept: video/mp4,video/*;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.9',
                    'Accept-Encoding: identity',
                    'Connection: keep-alive',
                ),
                CURLOPT_WRITEFUNCTION => function($ch, $data) {
                    echo $data;
                    flush();
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    return strlen($data);
                }
            ));
            
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if (!$result || $http_code !== 200) {
                // If streaming failed, try wp_remote_get with proper headers
                header_remove();
                
                // Reset headers for download
                header('Content-Type: video/mp4');
                header('Content-Disposition: attachment; filename="' . $safe_filename . '"');
                header('Content-Transfer-Encoding: binary');
                
                // Try using wp_remote_get to fetch and stream
                $response = wp_remote_get($download_url, array(
                    'timeout' => 300,
                    'stream' => true,
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'headers' => array(
                        'Referer' => home_url(),
                        'Accept' => 'video/mp4,video/*;q=0.9,*/*;q=0.8',
                    ),
                    'sslverify' => true,
                ));
                
                if (!is_wp_error($response)) {
                    $response_code = wp_remote_retrieve_response_code($response);
                    if ($response_code === 200) {
                        // Stream the body in chunks
                        $body = wp_remote_retrieve_body($response);
                        if (!empty($body)) {
                            echo $body;
                            flush();
                            exit;
                        }
                    }
                }
                
                // Last resort: output JavaScript to force download client-side
                header_remove();
                echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Downloading...</title></head><body>';
                echo '<script>';
                echo 'var link = document.createElement("a");';
                echo 'link.href = ' . json_encode($download_url) . ';';
                echo 'link.download = ' . json_encode($safe_filename) . ';';
                echo 'link.target = "_blank";';
                echo 'document.body.appendChild(link);';
                echo 'link.click();';
                echo 'document.body.removeChild(link);';
                echo 'setTimeout(function(){ window.close(); }, 1000);';
                echo '</script>';
                echo '<p>Download should start automatically. <a href="' . esc_url($download_url) . '" download="' . esc_attr($safe_filename) . '">Click here if it doesn\'t</a>.</p>';
                echo '</body></html>';
                exit;
            }
            
            exit;
        } else {
            // If headers already sent, fall back to redirect
            wp_redirect($download_url, 302);
            exit;
        }
    }
    
    /**
     * Download Vimeo video
     * 
     * @param int $vimeo_id Vimeo video ID
     * @param string $filename Desired filename
     */
    private function download_vimeo_video($vimeo_id, $filename) {
        // Prevent any output buffering issues
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // DISABLED: Debug logging
        // $debug_msg = 'ALM Download Debug: download_vimeo_video called with vimeo_id = ' . $vimeo_id . "\n";
        // error_log($debug_msg);
        // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
        
        // Try to use the existing vimeo_return_download_link function
        if (function_exists('vimeo_return_download_link')) {
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: Using vimeo_return_download_link function' . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            
            $vimeo_data = vimeo_return_download_link($vimeo_id, 'array');
            
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: vimeo_return_download_link result: ' . print_r($vimeo_data, true) . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            
            if ($vimeo_data && !empty($vimeo_data['url'])) {
                $download_url = $vimeo_data['url'];
                // DISABLED: Debug logging
                // $debug_msg = 'ALM Download Debug: Got Vimeo download URL: ' . $download_url . "\n";
                // error_log($debug_msg);
                // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            } else {
                // DISABLED: Debug logging
                // $debug_msg = 'ALM Download Debug: vimeo_return_download_link returned no URL' . "\n";
                // error_log($debug_msg);
                // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
                wp_die(__('Could not get Vimeo download URL. The video may not have downloads enabled or API access may be limited.', 'academy-lesson-manager'));
            }
        } else {
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: vimeo_return_download_link function not found, using ALM_Vimeo_API' . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            // Fallback: Use ALM_Vimeo_API class
            if (!class_exists('ALM_Vimeo_API')) {
                require_once ALM_PLUGIN_DIR . 'includes/class-vimeo-api.php';
            }
            
            $vimeo_api = new ALM_Vimeo_API();
            $metadata = $vimeo_api->get_video_metadata($vimeo_id);
            
            if (!$metadata || !isset($metadata['files'])) {
                wp_die(__('Could not get Vimeo video metadata. The video may not have downloads enabled.', 'academy-lesson-manager'));
            }
            
            // Get download URLs from metadata
            $downloadUrls = array();
            foreach ($metadata['files'] as $file) {
                if (isset($file['quality'], $file['link']) && $file['quality'] !== 'hls' && $file['quality'] !== 'dash') {
                    $downloadUrls[$file['quality'] . $file['rendition']] = $file['link'];
                }
            }
            
            // Prefer highest quality
            $preferredQualityOrder = array('sourcesource', 'hd1080p', 'hd720p', 'sd540p', 'sd360p', 'sd240p');
            $download_url = '';
            foreach ($preferredQualityOrder as $quality) {
                if (isset($downloadUrls[$quality])) {
                    $download_url = $downloadUrls[$quality];
                    break;
                }
            }
            
            if (empty($download_url)) {
                wp_die(__('No suitable Vimeo download URL found. The video may not have downloads enabled.', 'academy-lesson-manager'));
            }
        }
        
        // DISABLED: Debug logging
        // $debug_msg = 'ALM Download Debug: Setting download headers and streaming file' . "\n";
        // error_log($debug_msg);
        // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
        
        // Disable WordPress redirects
        remove_action('admin_init', 'wp_admin_headers');
        
        // Set headers to force download (not play in browser)
        nocache_headers();
        header('Content-Type: application/octet-stream'); // Force download
        header('Content-Disposition: attachment; filename="' . $filename . '.mp4"');
        header('Content-Transfer-Encoding: binary');
        
        // DISABLED: Debug logging
        // $debug_msg = 'ALM Download Debug: Attempting to stream file from URL: ' . $download_url . "\n";
        // error_log($debug_msg);
        // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
        
        // Use fopen/fpassthru for streaming large files to avoid memory issues
        // This also handles redirects automatically
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => array(
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ),
                'timeout' => 300,
                'follow_location' => true,
                'max_redirects' => 5
            )
        ));
        
        $handle = @fopen($download_url, 'rb', false, $context);
        if ($handle) {
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: fopen successful, streaming in chunks.' . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            
            // Get content length if available
            $meta = stream_get_meta_data($handle);
            $content_length = 0;
            if (isset($meta['wrapper_data'])) {
                foreach ($meta['wrapper_data'] as $header) {
                    if (stripos($header, 'content-length:') === 0) {
                        $content_length = intval(trim(substr($header, 15)));
                        break;
                    }
                }
            }
            
            if ($content_length > 0) {
                header('Content-Length: ' . $content_length);
                // DISABLED: Debug logging
                // $debug_msg = 'ALM Download Debug: Content-Length: ' . $content_length . "\n";
                // error_log($debug_msg);
                // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            }
            
            // Stream file using fpassthru (most efficient)
            fpassthru($handle);
            fclose($handle);
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: File streamed successfully using fpassthru.' . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            exit;
        } else {
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: fopen failed, falling back to wp_remote_get.' . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            
            // Fallback to wp_remote_get if fopen fails
            $file_response = wp_remote_get($download_url, array(
                'timeout' => 300,
                'redirection' => 5
            ));
            
            if (is_wp_error($file_response)) {
                // DISABLED: Debug logging
                // $debug_msg = 'ALM Download Debug: wp_remote_get error: ' . $file_response->get_error_message() . "\n";
                // error_log($debug_msg);
                // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
                wp_die(sprintf(
                    __('Error downloading video: %s', 'academy-lesson-manager'),
                    $file_response->get_error_message()
                ));
            }
            
            $status_code = wp_remote_retrieve_response_code($file_response);
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: wp_remote_get HTTP status code: ' . $status_code . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            
            if ($status_code !== 200) {
                wp_die(sprintf(
                    __('Error downloading video. HTTP status: %d', 'academy-lesson-manager'),
                    $status_code
                ));
            }
            
            $content_length = wp_remote_retrieve_header($file_response, 'content-length');
            if ($content_length) {
                header('Content-Length: ' . $content_length);
            }
            
            $body = wp_remote_retrieve_body($file_response);
            echo $body;
            // DISABLED: Debug logging
            // $debug_msg = 'ALM Download Debug: File body output complete via wp_remote_get, ' . strlen($body) . ' bytes.' . "\n";
            // error_log($debug_msg);
            // @file_put_contents(WP_CONTENT_DIR . '/alm-download-debug.log', $debug_msg, FILE_APPEND);
            exit;
        }
    }
}

