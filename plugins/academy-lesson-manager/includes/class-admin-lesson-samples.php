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
        
        // Handle actions
        add_action('admin_init', array($this, 'handle_actions'));
        
        // Note: AJAX handlers are registered in main plugin class (academy-lesson-manager.php)
        // to ensure they're available for AJAX requests
    }
    
    /**
     * Handle admin actions
     */
    public function handle_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
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
}

