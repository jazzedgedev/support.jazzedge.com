<?php
/**
 * Plugin Name: Chapter Transcription Manager
 * Plugin URI: https://jazzedge.com
 * Description: Manage chapter video transcriptions using OpenAI Whisper API
 * Version: 1.0.0
 * Author: JazzEdge
 * License: GPL v2 or later
 * Text Domain: chapter-transcription
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CT_VERSION', '1.0.0');
define('CT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CT_PLUGIN_FILE', __FILE__);

/**
 * Main Chapter Transcription Class
 */
class Chapter_Transcription {
    
    /**
     * @var string Queue table name
     */
    private $queue_table;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->queue_table = isset($wpdb) ? $wpdb->prefix . 'alm_transcription_jobs' : null;
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Plugin activation hook
     */
    public static function activate() {
        self::create_queue_table();
    }
    
    /**
     * Plugin deactivation hook
     */
    public static function deactivate() {
        wp_clear_scheduled_hook('ct_process_transcription_queue');
        delete_transient('ct_queue_lock');
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Include required files
        $this->include_files();
        $this->maybe_initialize_queue_table();
        
        add_filter('cron_schedules', array($this, 'register_queue_schedule'));
        add_action('ct_process_transcription_queue', array($this, 'process_transcription_queue'));
        $this->ensure_queue_schedule();
        
        // Initialize admin
        if (is_admin()) {
            $this->init_admin();
        }
    }
    
    /**
     * Include required files
     */
    private function include_files() {
        require_once CT_PLUGIN_DIR . 'includes/class-video-downloader.php';
        require_once CT_PLUGIN_DIR . 'includes/class-whisper-client.php';
        require_once CT_PLUGIN_DIR . 'includes/class-cost-logger.php';
        require_once CT_PLUGIN_DIR . 'includes/class-vtt-generator.php';
    }
    
    /**
     * Initialize admin
     */
    private function init_admin() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_transcription_action'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_ct_start_transcription', array($this, 'ajax_start_transcription'));
        add_action('wp_ajax_ct_check_transcription_status', array($this, 'ajax_check_transcription_status'));
        add_action('wp_ajax_ct_bulk_download', array($this, 'ajax_bulk_download'));
        add_action('wp_ajax_ct_bulk_transcribe', array($this, 'ajax_bulk_transcribe'));
        add_action('wp_ajax_ct_check_bulk_status', array($this, 'ajax_check_bulk_status'));
        add_action('wp_ajax_ct_trigger_bulk_download', array($this, 'ajax_trigger_bulk_download'));
        add_action('wp_ajax_ct_process_single_download', array($this, 'ajax_process_single_download'));
        add_action('wp_ajax_ct_get_debug_log', array($this, 'ajax_get_debug_log'));
        add_action('wp_ajax_ct_clear_all_stuck', array($this, 'ajax_clear_all_stuck'));
        add_action('wp_ajax_ct_download_and_transcribe', array($this, 'ajax_download_and_transcribe'));
        add_action('wp_ajax_ct_export_debug_log', array($this, 'ajax_export_debug_log'));
        add_action('wp_ajax_ct_bulk_download_and_transcribe', array($this, 'ajax_bulk_download_and_transcribe'));
        
        add_action('ct_process_bulk_download', array($this, 'process_bulk_download_cron'), 10, 3);
        add_action('ct_process_bulk_transcribe', array($this, 'process_bulk_transcribe_cron'), 10, 3);
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_chapter-transcription' && $hook !== 'transcription_page_chapter-transcription-chapters') {
            return;
        }
        
        wp_enqueue_script(
            'chapter-transcription-admin',
            CT_PLUGIN_URL . 'assets/admin.js',
            array('jquery'),
            CT_VERSION,
            true
        );
        
        wp_localize_script('chapter-transcription-admin', 'ctAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ct_transcription_nonce')
        ));
        
        wp_enqueue_style(
            'chapter-transcription-admin',
            CT_PLUGIN_URL . 'assets/admin.css',
            array(),
            CT_VERSION
        );
    }
    
    /**
     * Handle transcription action (before any output)
     */
    public function handle_transcription_action() {
        // Only handle on our admin page
        if (!isset($_GET['page']) || $_GET['page'] !== 'chapter-transcription') {
            return;
        }
        
        // Handle actions
        if (isset($_GET['action']) && isset($_GET['chapter_id'])) {
            $chapter_id = intval($_GET['chapter_id']);
            $action = sanitize_text_field($_GET['action']);
            $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
            
            // Verify nonce based on action
            $nonce_action = $action . '_chapter_' . $chapter_id;
            if (!wp_verify_nonce($_GET['_wpnonce'], $nonce_action)) {
                wp_die('Security check failed');
            }
            
            if ($action === 'download') {
                // Download video only
                $result = $this->download_video($chapter_id);
                
                // Preserve filter in redirect
                $filter_lesson_id = isset($_GET['filter_lesson']) ? intval($_GET['filter_lesson']) : 0;
                
                // Redirect back to same page after downloading
                $redirect_params = array(
                    'page' => 'chapter-transcription',
                    'paged' => $paged,
                    'downloaded' => $result['success'] ? '1' : '0',
                    'message' => urlencode($result['message'])
                );
                
                if ($filter_lesson_id > 0) {
                    $redirect_params['filter_lesson'] = $filter_lesson_id;
                }
                
                $redirect_url = add_query_arg($redirect_params, admin_url('admin.php'));
                
                wp_safe_redirect($redirect_url);
                exit;
                
            } elseif ($action === 'transcribe') {
                // Legacy synchronous transcription (kept for fallback)
                // Note: This will timeout on long videos. Use AJAX instead.
                // Increase PHP execution time for long transcriptions
                set_time_limit(0); // No time limit
                ini_set('max_execution_time', 0);
                
                $result = $this->transcribe_video($chapter_id, false);
                
                // Preserve filter in redirect
                $filter_lesson_id = isset($_GET['filter_lesson']) ? intval($_GET['filter_lesson']) : 0;
                
                // Redirect back to same page after transcribing
                $redirect_params = array(
                    'page' => 'chapter-transcription',
                    'paged' => $paged,
                    'transcribed' => $result['success'] ? '1' : '0',
                    'message' => urlencode($result['message'])
                );
                
                if ($filter_lesson_id > 0) {
                    $redirect_params['filter_lesson'] = $filter_lesson_id;
                }
                
                $redirect_url = add_query_arg($redirect_params, admin_url('admin.php'));
                
                wp_safe_redirect($redirect_url);
                exit;
            }
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Chapter Transcription',
            'Transcription',
            'manage_options',
            'chapter-transcription',
            array($this, 'admin_page'),
            'dashicons-microphone',
            30
        );
        
        // Add Chapters submenu
        add_submenu_page(
            'chapter-transcription',
            'Chapters',
            'Chapters',
            'manage_options',
            'chapter-transcription-chapters',
            array($this, 'admin_page_chapters')
        );
    }
    
    /**
     * Admin page callback
     */
    public function admin_page() {
        // Show notice if redirected after transcribing
        if (isset($_GET['transcribed'])) {
            $message = isset($_GET['message']) ? urldecode($_GET['message']) : '';
            if ($_GET['transcribed'] === '1') {
                echo '<div class="notice notice-success is-dismissible"><p>✓ ' . esc_html($message) . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>✗ ' . esc_html($message) . '</p></div>';
            }
        }
        
        // Show notice if redirected after downloading
        if (isset($_GET['downloaded'])) {
            $message = isset($_GET['message']) ? urldecode($_GET['message']) : '';
            if ($_GET['downloaded'] === '1') {
                echo '<div class="notice notice-success is-dismissible"><p>✓ ' . esc_html($message) . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>✗ ' . esc_html($message) . '</p></div>';
            }
        }
        
        // Pagination settings
        $per_page = 50; // Items per page
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        // Get filter lesson ID
        $filter_lesson_id = isset($_GET['filter_lesson']) ? intval($_GET['filter_lesson']) : 0;
        
        // Build WHERE clause
        $where_clause = "c.lesson_id > 0 AND ((c.bunny_url != '' AND c.bunny_url IS NOT NULL) OR c.vimeo_id > 0)";
        $where_params = array();
        
        if ($filter_lesson_id > 0) {
            $where_clause .= " AND c.lesson_id = %d";
            $where_params[] = $filter_lesson_id;
        }
        
        // Get total count
        global $wpdb;
        if (!empty($where_params)) {
            $total_items = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*)
                FROM {$wpdb->prefix}alm_chapters c
                WHERE {$where_clause}
            ", $where_params));
        } else {
            $total_items = $wpdb->get_var("
                SELECT COUNT(*)
                FROM {$wpdb->prefix}alm_chapters c
                WHERE {$where_clause}
            ");
        }
        
        $total_pages = ceil($total_items / $per_page);
        
        // Get all lessons for filter dropdown with transcript completion status
        // First, get lessons that have at least one chapter with video (matching filter logic)
        $lessons_with_videos = $wpdb->get_col("
            SELECT DISTINCT l.ID
            FROM {$wpdb->prefix}alm_lessons l
            INNER JOIN {$wpdb->prefix}alm_chapters c ON l.ID = c.lesson_id
            WHERE c.lesson_id > 0 AND ((c.bunny_url != '' AND c.bunny_url IS NOT NULL) OR c.vimeo_id > 0)
        ");
        
        if (empty($lessons_with_videos)) {
            $all_lessons = array();
        } else {
            // Now get all lessons with their complete chapter counts (including chapters without videos)
            $lesson_ids_placeholders = implode(',', array_fill(0, count($lessons_with_videos), '%d'));
            $all_lessons = $wpdb->get_results($wpdb->prepare("
                SELECT 
                    l.ID, 
                    l.lesson_title,
                    (SELECT COUNT(*) FROM {$wpdb->prefix}alm_chapters WHERE lesson_id = l.ID) as total_chapters,
                    (SELECT COUNT(*) FROM {$wpdb->prefix}alm_chapters 
                     WHERE lesson_id = l.ID 
                     AND ((bunny_url != '' AND bunny_url IS NOT NULL) OR vimeo_id > 0)) as chapters_with_video,
                    (SELECT COUNT(*) FROM {$wpdb->prefix}alm_chapters c
                     INNER JOIN {$wpdb->prefix}alm_transcripts t ON c.ID = t.chapter_id AND t.source = 'whisper'
                     WHERE c.lesson_id = l.ID 
                     AND ((c.bunny_url != '' AND c.bunny_url IS NOT NULL) OR c.vimeo_id > 0)) as chapters_with_transcript
                FROM {$wpdb->prefix}alm_lessons l
                WHERE l.ID IN ($lesson_ids_placeholders)
                ORDER BY l.lesson_title ASC
            ", $lessons_with_videos));
        }
        
        // Add status prefixes to lesson titles
        foreach ($all_lessons as $lesson) {
            $total_chapters = intval($lesson->total_chapters);
            $chapters_with_video = intval($lesson->chapters_with_video);
            $chapters_with_transcript = intval($lesson->chapters_with_transcript);
            
            $prefix = '';
            
            // Check if all chapters with videos have transcripts
            if ($chapters_with_video > 0) {
                if ($chapters_with_transcript == $chapters_with_video) {
                    $prefix = '*DONE* ';
                } elseif ($chapters_with_transcript > 0) {
                    $prefix = '*PARTIAL* ';
                }
            }
            
            // Check if there are chapters without videos
            if ($total_chapters > $chapters_with_video) {
                $prefix = '*NO VIDEO* ' . $prefix;
            }
            
            // Add prefix to lesson title
            $lesson->lesson_title = $prefix . $lesson->lesson_title;
        }
        
        // Get chapters with lesson titles (Bunny or Vimeo) - paginated
        $query = "
            SELECT c.*, 
                   l.lesson_title,
                   t.ID as transcript_id,
                   t.content as transcript_content,
                   CASE 
                       WHEN c.bunny_url != '' AND c.bunny_url IS NOT NULL THEN 'bunny'
                       WHEN c.vimeo_id > 0 THEN 'vimeo'
                       ELSE 'none'
                   END as video_source
            FROM {$wpdb->prefix}alm_chapters c
            LEFT JOIN {$wpdb->prefix}alm_lessons l ON c.lesson_id = l.ID
            LEFT JOIN {$wpdb->prefix}alm_transcripts t ON c.ID = t.chapter_id AND t.source = 'whisper'
            WHERE {$where_clause}
            ORDER BY c.lesson_id, c.menu_order
            LIMIT %d OFFSET %d
        ";
        
        $query_params = array_merge($where_params, array($per_page, $offset));
        
        if (!empty($where_params)) {
            $chapters = $wpdb->get_results($wpdb->prepare($query, $query_params));
        } else {
            $chapters = $wpdb->get_results($wpdb->prepare($query, $per_page, $offset));
        }
        
        // Check MP4 file status and verify transcript status for each chapter
        $video_downloader = new Transcription_Video_Downloader();
        foreach ($chapters as $chapter) {
            $chapter->mp4_exists = $video_downloader->get_mp4_path($chapter->lesson_id, $chapter->ID, $chapter->chapter_title) !== false;
            
            // Verify transcript exists in database (double-check if transcript_id is empty)
            if (empty($chapter->transcript_id)) {
                $transcript_check = $wpdb->get_var($wpdb->prepare(
                    "SELECT ID FROM {$wpdb->prefix}alm_transcripts WHERE chapter_id = %d AND source = 'whisper' LIMIT 1",
                    $chapter->ID
                ));
                if ($transcript_check) {
                    $chapter->transcript_id = $transcript_check;
                    // Also get the content if needed
                    $chapter->transcript_content = $wpdb->get_var($wpdb->prepare(
                        "SELECT content FROM {$wpdb->prefix}alm_transcripts WHERE ID = %d",
                        $transcript_check
                    ));
                }
            }
        }
        
        // Get cost summary
        $cost_logger = new Transcription_Cost_Logger();
        $total_cost = $cost_logger->get_total_cost();
        $cost_log = $cost_logger->get_recent_logs(10);
        
        $queue_stats = $this->get_queue_stats();
        
        // Pass pagination data to template
        $pagination_data = array(
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_items' => $total_items,
            'per_page' => $per_page,
            'filter_lesson_id' => $filter_lesson_id
        );
        
        // Pass lessons to template
        $template_all_lessons = $all_lessons;
        
        // Include admin page template
        include CT_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * Download video for a chapter (MP4 only, no transcription)
     */
    private function download_video($chapter_id) {
        global $wpdb;
        
        // Get chapter
        $chapter = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_chapters WHERE ID = %d",
            $chapter_id
        ));
        
        if (!$chapter) {
            return array('success' => false, 'message' => 'Chapter not found.');
        }
        
        // Ensure chapter title has [idX] format and update database if needed
        $title_with_id = $this->ensure_title_has_id($chapter_id, $chapter->chapter_title);
        if ($title_with_id !== $chapter->chapter_title) {
            $this->update_chapter_title_with_id($chapter_id, $title_with_id);
            $chapter->chapter_title = $title_with_id; // Update local copy
        }
        
        // Determine video source
        $video_source = 'none';
        $video_url = '';
        
        if (!empty($chapter->bunny_url)) {
            $video_source = 'bunny';
            $video_url = $chapter->bunny_url;
        } elseif ($chapter->vimeo_id > 0) {
            $video_source = 'vimeo';
            $video_url = $chapter->vimeo_id;
        }
        
        if ($video_source === 'none') {
            return array('success' => false, 'message' => 'Chapter has no video source (Bunny URL or Vimeo ID).');
        }
        
        // Download and convert video
        $video_downloader = new Transcription_Video_Downloader();
        $mp4_path = $video_downloader->download_and_convert($video_url, $chapter->lesson_id, $chapter_id, $chapter->chapter_title, $video_source);
        
        if (!$mp4_path || !file_exists($mp4_path)) {
            return array('success' => false, 'message' => 'Failed to download/convert video.');
        }
        
        return array('success' => true, 'message' => 'Video downloaded successfully. MP4 file saved.');
    }
    
    /**
     * AJAX handler: Start transcription (non-blocking)
     */
    public function ajax_start_transcription() {
        check_ajax_referer('ct_transcription_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        
        if (!$chapter_id) {
            wp_send_json_error('Invalid chapter ID');
        }
        
        $enqueue = $this->enqueue_transcription_job($chapter_id, 'transcribe');
        
        if (!$enqueue['success']) {
            wp_send_json_error($enqueue['message']);
        }
        
        wp_send_json_success(array(
            'message' => $enqueue['message'],
            'chapter_id' => $chapter_id,
            'job_id' => isset($enqueue['job_id']) ? intval($enqueue['job_id']) : null
        ));
    }
    
    /**
     * AJAX handler: Download and transcribe in one operation
     */
    public function ajax_download_and_transcribe() {
        check_ajax_referer('ct_transcription_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        
        if (!$chapter_id) {
            wp_send_json_error('Invalid chapter ID');
        }
        
        $enqueue = $this->enqueue_transcription_job($chapter_id, 'download_transcribe');
        
        if (!$enqueue['success']) {
            wp_send_json_error(array('message' => $enqueue['message']));
        }
        
        wp_send_json_success(array(
            'message' => $enqueue['message'],
            'chapter_id' => $chapter_id,
            'job_id' => isset($enqueue['job_id']) ? intval($enqueue['job_id']) : null
        ));
    }
    
    
    /**
     * AJAX handler: Check transcription status
     */
    public function ajax_check_transcription_status() {
        check_ajax_referer('ct_transcription_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        
        if (!$chapter_id) {
            wp_send_json_error('Invalid chapter ID');
        }
        
        $status = get_transient('ct_transcription_' . $chapter_id);
        
        if ($status === false) {
            wp_send_json_success(array(
                'status' => 'unknown',
                'message' => 'No status found - transcription may not have started'
            ));
        }
        
        // Add current elapsed time if processing
        if ($status['status'] === 'processing' && isset($status['started_at'])) {
            $status['elapsed_seconds'] = time() - $status['started_at'];
        }
        
        if ($status['status'] === 'queued') {
            $queue_info = $this->get_queue_position_for_chapter($chapter_id);
            if ($queue_info) {
                $status['queue_position'] = $queue_info['position'];
                $status['queue_total'] = $queue_info['total'];
            }
        }
        
        wp_send_json_success($status);
    }
    
    /**
     * AJAX handler: Get debug log entries
     */
    public function ajax_get_debug_log() {
        check_ajax_referer('ct_transcription_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        $lines = isset($_POST['lines']) ? intval($_POST['lines']) : 50;
        
        // Get debug log file path
        $log_file = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($log_file)) {
            wp_send_json_success(array(
                'log_entries' => array('Debug log file not found. Enable WP_DEBUG_LOG in wp-config.php'),
                'file_exists' => false
            ));
        }
        
        // Read last N lines from log file
        $log_entries = array();
        if (filesize($log_file) > 0) {
            $file = file($log_file);
            $relevant_lines = array();
            
            // Filter for transcription-related entries
            foreach ($file as $line) {
                if (stripos($line, 'Transcription') !== false || 
                    stripos($line, 'chapter ' . $chapter_id) !== false ||
                    ($chapter_id === 0 && (stripos($line, 'Bulk download') !== false || stripos($line, 'Whisper') !== false))) {
                    $relevant_lines[] = trim($line);
                }
            }
            
            // Get last N lines
            $log_entries = array_slice($relevant_lines, -$lines);
        }
        
        wp_send_json_success(array(
            'log_entries' => $log_entries,
            'file_exists' => true,
            'file_path' => $log_file
        ));
    }
    
    /**
     * AJAX handler: Export debug log for a chapter (copyable format)
     */
    public function ajax_export_debug_log() {
        check_ajax_referer('ct_transcription_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        
        if (!$chapter_id) {
            wp_send_json_error('Invalid chapter ID');
        }
        
        global $wpdb;
        
        // Get chapter info
        $chapter = $wpdb->get_row($wpdb->prepare(
            "SELECT c.*, l.lesson_title FROM {$wpdb->prefix}alm_chapters c
             LEFT JOIN {$wpdb->prefix}alm_lessons l ON c.lesson_id = l.ID
             WHERE c.ID = %d",
            $chapter_id
        ));
        
        $debug_output = "=== CHAPTER TRANSCRIPTION DEBUG LOG ===\n\n";
        $debug_output .= "Chapter ID: " . $chapter_id . "\n";
        if ($chapter) {
            $debug_output .= "Lesson: " . $chapter->lesson_title . " (ID: " . $chapter->lesson_id . ")\n";
            $debug_output .= "Chapter Title: " . $chapter->chapter_title . "\n";
            $debug_output .= "Video Source: " . (!empty($chapter->bunny_url) ? 'Bunny CDN' : ($chapter->vimeo_id > 0 ? 'Vimeo (ID: ' . $chapter->vimeo_id . ')' : 'None')) . "\n";
        }
        $debug_output .= "Export Time: " . date('Y-m-d H:i:s T') . "\n\n";
        
        // Get Whisper API error details if available
        $whisper_error = get_transient('ct_whisper_error_' . $chapter_id);
        if ($whisper_error) {
            $debug_output .= "=== WHISPER API ERROR DETAILS ===\n";
            foreach ($whisper_error as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_PRETTY_PRINT);
                }
                $debug_output .= ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
            }
            $debug_output .= "\n";
        }
        
        // Get transcription status
        $status = get_transient('ct_transcription_' . $chapter_id);
        if ($status) {
            $debug_output .= "=== TRANSCRIPTION STATUS ===\n";
            foreach ($status as $key => $value) {
                if ($key === 'started_at' || $key === 'completed_at' || $key === 'failed_at') {
                    $value = $value ? date('Y-m-d H:i:s', $value) : 'N/A';
                }
                $debug_output .= ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
            }
            $debug_output .= "\n";
        }
        
        // Get recent log entries from debug.log
        $log_file = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($log_file) && filesize($log_file) > 0) {
            $file = file($log_file);
            $relevant_lines = array();
            
            // Get last 100 lines that mention this chapter or Whisper
            foreach ($file as $line) {
                if (stripos($line, 'chapter ' . $chapter_id) !== false || 
                    stripos($line, 'WHISPER') !== false ||
                    (stripos($line, 'Transcription') !== false && stripos($line, (string)$chapter_id) !== false)) {
                    $relevant_lines[] = trim($line);
                }
            }
            
            if (!empty($relevant_lines)) {
                $debug_output .= "=== RECENT LOG ENTRIES (Last " . count($relevant_lines) . " relevant lines) ===\n";
                $debug_output .= implode("\n", array_slice($relevant_lines, -50)); // Last 50 lines
                $debug_output .= "\n\n";
            }
        }
        
        // Check if MP3 file still exists (should be deleted)
        $video_downloader = new Transcription_Video_Downloader();
        if ($chapter) {
            $mp3_filename = $video_downloader->generate_filename($chapter->lesson_id, $chapter_id, $chapter->chapter_title, 'mp3');
            $mp3_path = $video_downloader->get_output_directory() . $mp3_filename;
            if (file_exists($mp3_path)) {
                $debug_output .= "=== WARNING ===\n";
                $debug_output .= "MP3 file still exists (should be deleted): " . $mp3_path . "\n";
                $debug_output .= "File size: " . round(filesize($mp3_path) / 1024 / 1024, 2) . " MB\n";
                $debug_output .= "Last modified: " . date('Y-m-d H:i:s', filemtime($mp3_path)) . "\n\n";
            }
        }
        
        $debug_output .= "=== END DEBUG LOG ===\n";
        
        wp_send_json_success(array(
            'debug_log' => $debug_output,
            'chapter_id' => $chapter_id
        ));
    }
    
    /**
     * AJAX handler: Clear all stuck transcription statuses
     */
    public function ajax_clear_all_stuck() {
        check_ajax_referer('ct_transcription_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        global $wpdb;
        
        // Get all transcription transients
        $transient_keys = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_ct_transcription_%' 
             AND option_name NOT LIKE '_transient_timeout_%'"
        );
        
        $cleared_count = 0;
        foreach ($transient_keys as $key) {
            $chapter_id = str_replace('_transient_ct_transcription_', '', $key);
            delete_transient('ct_transcription_' . $chapter_id);
            $cleared_count++;
        }
        
        error_log('Transcription: Cleared ' . $cleared_count . ' stuck transcription statuses');
        
        wp_send_json_success(array(
            'message' => 'Cleared ' . $cleared_count . ' stuck transcription statuses',
            'count' => $cleared_count
        ));
    }
    
    /**
     * AJAX handler: Bulk download videos
     */
    public function ajax_bulk_download() {
        check_ajax_referer('ct_transcription_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $chapter_ids = isset($_POST['chapter_ids']) ? array_map('intval', $_POST['chapter_ids']) : array();
        
        if (empty($chapter_ids)) {
            wp_send_json_error('No chapters selected');
        }
        
        // Create bulk operation ID
        $bulk_id = 'download_' . time() . '_' . wp_generate_password(8, false);
        
        // Store operation data
        $operation_data = array(
            'type' => 'download',
            'chapter_ids' => $chapter_ids,
            'total' => count($chapter_ids),
            'completed' => 0,
            'failed' => 0,
            'current' => 0,
            'status' => 'processing',
            'started_at' => time()
        );
        
        set_transient('ct_bulk_' . $bulk_id, $operation_data, 7200); // 2 hours
        
        // Return immediately - JavaScript will process items sequentially via AJAX
        wp_send_json_success(array(
            'bulk_id' => $bulk_id,
            'total' => count($chapter_ids),
            'chapter_ids' => $chapter_ids
        ));
    }
    
    /**
     * AJAX handler: Bulk transcribe videos
     */
    public function ajax_bulk_transcribe() {
        check_ajax_referer('ct_transcription_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $chapter_ids = isset($_POST['chapter_ids']) ? array_map('intval', $_POST['chapter_ids']) : array();
        
        if (empty($chapter_ids)) {
            wp_send_json_error('No chapters selected');
        }
        
        // Create bulk operation ID
        $bulk_id = 'transcribe_' . time() . '_' . wp_generate_password(8, false);
        
        // Store operation data
        $operation_data = array(
            'type' => 'transcribe',
            'chapter_ids' => $chapter_ids,
            'total' => count($chapter_ids),
            'completed' => 0,
            'failed' => 0,
            'current' => 0,
            'status' => 'processing',
            'started_at' => time()
        );
        
        set_transient('ct_bulk_' . $bulk_id, $operation_data, 7200); // 2 hours
        
        // Start all transcriptions immediately using direct execution (parallel processing)
        // Since they run in background, we can start them all at once
        error_log('Bulk transcribe: Starting ' . count($chapter_ids) . ' transcriptions');
        
        $queued = 0;
        $queue_errors = array();
        
        foreach ($chapter_ids as $index => $chapter_id) {
            $result = $this->start_transcription_for_bulk($chapter_id, $bulk_id);
            if ($result['success']) {
                $queued++;
            } else {
                $queue_errors[$chapter_id] = $result['message'];
                $operation_data['failed']++;
                if (!isset($operation_data['errors'])) {
                    $operation_data['errors'] = array();
                }
                $operation_data['errors'][$chapter_id] = $result['message'];
            }
        }
        
        set_transient('ct_bulk_' . $bulk_id, $operation_data, 7200);
        
        wp_send_json_success(array(
            'bulk_id' => $bulk_id,
            'total' => count($chapter_ids),
            'queued' => $queued,
            'errors' => $queue_errors,
            'message' => $queued === count($chapter_ids)
                ? 'Queued all transcriptions'
                : sprintf('Queued %d/%d transcriptions', $queued, count($chapter_ids))
        ));
    }
    
    /**
     * Start a single transcription for bulk operation
     */
    private function start_transcription_for_bulk($chapter_id, $bulk_id) {
        $this->update_transcription_status($chapter_id, 'queued', 'Queued via bulk operation...');
        $result = $this->enqueue_transcription_job($chapter_id, 'transcribe', array('bulk_id' => $bulk_id));
        
        if ($result['success']) {
            error_log('Bulk transcribe: Chapter ' . $chapter_id . ' queued successfully');
        } else {
            error_log('Bulk transcribe: Failed to queue chapter ' . $chapter_id . ' - ' . $result['message']);
        }
        
        return $result;
    }
    
    /**
     * AJAX handler: Check bulk operation status
     */
    public function ajax_check_bulk_status() {
        check_ajax_referer('ct_transcription_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $bulk_id = isset($_POST['bulk_id']) ? sanitize_text_field($_POST['bulk_id']) : '';
        
        if (empty($bulk_id)) {
            wp_send_json_error('Invalid bulk operation ID');
        }
        
        $operation_data = get_transient('ct_bulk_' . $bulk_id);
        
        if ($operation_data === false) {
            wp_send_json_success(array(
                'status' => 'not_found',
                'message' => 'Operation not found'
            ));
        }
        
        wp_send_json_success($operation_data);
    }
    
    /**
     * Process a single item in bulk download
     */
    private function process_bulk_download_item($bulk_id, $chapter_id, $index) {
        $operation_data = get_transient('ct_bulk_' . $bulk_id);
        if ($operation_data === false) {
            error_log('Bulk download: Operation data not found for ' . $bulk_id);
            return;
        }
        
        // Update current item
        $operation_data['current'] = $index + 1;
        $operation_data['current_chapter_id'] = $chapter_id;
        set_transient('ct_bulk_' . $bulk_id, $operation_data, 7200);
        
        error_log(sprintf('Bulk download: Processing chapter %d (%d of %d)', $chapter_id, $index + 1, $operation_data['total']));
        
        // Download video
        $result = $this->download_video($chapter_id);
        
        if ($result['success']) {
            $operation_data['completed']++;
            error_log(sprintf('Bulk download: Chapter %d downloaded successfully', $chapter_id));
        } else {
            $operation_data['failed']++;
            if (!isset($operation_data['errors'])) {
                $operation_data['errors'] = array();
            }
            $operation_data['errors'][$chapter_id] = $result['message'];
            error_log(sprintf('Bulk download: Chapter %d failed - %s', $chapter_id, $result['message']));
        }
        
        // Check if more items to process
        if ($index + 1 < count($operation_data['chapter_ids'])) {
            $next_index = $index + 1;
            $next_chapter_id = $operation_data['chapter_ids'][$next_index];
            
            // Schedule next item with a small delay
            $scheduled = wp_schedule_single_event(time() + 2, 'ct_process_bulk_download', array($bulk_id, $next_chapter_id, $next_index));
            
            if ($scheduled === false) {
                error_log('Bulk download: Failed to schedule next item. Trying alternative method.');
                // Alternative: Use wp_remote_post to trigger immediately
                $this->trigger_bulk_download_next($bulk_id, $next_chapter_id, $next_index);
            } else {
                // Force cron to run
                if (!defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON) {
                    spawn_cron();
                }
            }
            
            $operation_data['status'] = 'processing';
            error_log(sprintf('Bulk download: Scheduled next item (chapter %d, index %d)', $next_chapter_id, $next_index));
        } else {
            $operation_data['status'] = 'completed';
            $operation_data['completed_at'] = time();
            error_log(sprintf('Bulk download: All items completed. Total: %d, Completed: %d, Failed: %d', 
                $operation_data['total'], $operation_data['completed'], $operation_data['failed']));
        }
        
        set_transient('ct_bulk_' . $bulk_id, $operation_data, 7200);
    }
    
    /**
     * Alternative method to trigger next bulk download item via HTTP request
     */
    private function trigger_bulk_download_next($bulk_id, $chapter_id, $index) {
        // Use wp_remote_post to trigger the cron hook immediately
        $cron_url = admin_url('admin-ajax.php');
        $response = wp_remote_post($cron_url, array(
            'timeout' => 0.01,
            'blocking' => false,
            'sslverify' => false,
            'body' => array(
                'action' => 'ct_trigger_bulk_download',
                'bulk_id' => $bulk_id,
                'chapter_id' => $chapter_id,
                'index' => $index,
                'nonce' => wp_create_nonce('ct_bulk_trigger')
            )
        ));
    }
    
    /**
     * Process a single item in bulk transcribe
     */
    private function process_bulk_transcribe_item($bulk_id, $chapter_id, $index) {
        $operation_data = get_transient('ct_bulk_' . $bulk_id);
        if ($operation_data === false) {
            error_log('Bulk transcribe: Operation data not found for ' . $bulk_id);
            return;
        }
        
        // Update current item
        $operation_data['current'] = $index + 1;
        $operation_data['current_chapter_id'] = $chapter_id;
        set_transient('ct_bulk_' . $bulk_id, $operation_data, 7200);
        
        error_log(sprintf('Bulk transcribe: Processing chapter %d (%d of %d)', $chapter_id, $index + 1, $operation_data['total']));
        
        $this->update_transcription_status($chapter_id, 'queued', 'Queued via bulk operation...');
        $enqueue = $this->enqueue_transcription_job($chapter_id, 'transcribe', array('bulk_id' => $bulk_id));
        
        if (!$enqueue['success']) {
            if (!isset($operation_data['errors'])) {
                $operation_data['errors'] = array();
            }
            $operation_data['errors'][$chapter_id] = $enqueue['message'];
            $operation_data['failed'] = isset($operation_data['failed']) ? $operation_data['failed'] + 1 : 1;
            error_log('Bulk transcribe: Failed to queue chapter ' . $chapter_id . ' - ' . $enqueue['message']);
        } else {
            error_log('Bulk transcribe: Chapter ' . $chapter_id . ' queued successfully via bulk processor');
        }
        
        // Check if more items to process
        if ($index + 1 < count($operation_data['chapter_ids'])) {
            $operation_data['status'] = 'processing';
        } else {
            $operation_data['status'] = 'processing'; // Still processing transcriptions
        }
        
        set_transient('ct_bulk_' . $bulk_id, $operation_data, 7200);
    }
    
    /**
     * AJAX handler: Process a single download item (called sequentially from JS)
     */
    public function ajax_process_single_download() {
        check_ajax_referer('ct_transcription_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $bulk_id = isset($_POST['bulk_id']) ? sanitize_text_field($_POST['bulk_id']) : '';
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        $index = isset($_POST['index']) ? intval($_POST['index']) : 0;
        
        if (empty($bulk_id) || !$chapter_id) {
            wp_send_json_error('Invalid parameters');
        }
        
        // Get operation data
        $operation_data = get_transient('ct_bulk_' . $bulk_id);
        if ($operation_data === false) {
            wp_send_json_error('Bulk operation not found');
        }
        
        // Update current item
        $operation_data['current'] = $index + 1;
        $operation_data['current_chapter_id'] = $chapter_id;
        set_transient('ct_bulk_' . $bulk_id, $operation_data, 7200);
        
        // Download video
        $result = $this->download_video($chapter_id);
        
        if ($result['success']) {
            $operation_data['completed']++;
        } else {
            $operation_data['failed']++;
            if (!isset($operation_data['errors'])) {
                $operation_data['errors'] = array();
            }
            $operation_data['errors'][$chapter_id] = $result['message'];
        }
        
        // Check if more items to process
        if ($index + 1 < count($operation_data['chapter_ids'])) {
            $operation_data['status'] = 'processing';
        } else {
            $operation_data['status'] = 'completed';
            $operation_data['completed_at'] = time();
        }
        
        set_transient('ct_bulk_' . $bulk_id, $operation_data, 7200);
        
        wp_send_json_success(array(
            'success' => $result['success'],
            'message' => $result['message'],
            'completed' => $operation_data['completed'],
            'failed' => $operation_data['failed'],
            'total' => $operation_data['total'],
            'status' => $operation_data['status'],
            'has_more' => ($index + 1 < count($operation_data['chapter_ids']))
        ));
    }
    
    /**
     * AJAX handler: Trigger next bulk download item (fallback method)
     */
    public function ajax_trigger_bulk_download() {
        // This is a non-blocking trigger, so we don't need to verify nonce strictly
        // But we'll check it for security
        if (!wp_verify_nonce($_POST['nonce'], 'ct_bulk_trigger')) {
            return;
        }
        
        $bulk_id = isset($_POST['bulk_id']) ? sanitize_text_field($_POST['bulk_id']) : '';
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        $index = isset($_POST['index']) ? intval($_POST['index']) : 0;
        
        if (empty($bulk_id) || !$chapter_id) {
            return;
        }
        
        // Process the item
        $this->process_bulk_download_item($bulk_id, $chapter_id, $index);
        
        // Send response immediately (non-blocking)
        wp_send_json_success();
    }
    
    /**
     * Cron hook for bulk download processing
     */
    public function process_bulk_download_cron($bulk_id, $chapter_id, $index) {
        error_log(sprintf('Bulk download cron: Processing chapter %d, index %d', $chapter_id, $index));
        $this->process_bulk_download_item($bulk_id, $chapter_id, $index);
    }
    
    /**
     * Cron hook for bulk transcribe processing
     */
    public function process_bulk_transcribe_cron($bulk_id, $chapter_id, $index) {
        $this->process_bulk_transcribe_item($bulk_id, $chapter_id, $index);
    }
    
    /**
     * Run transcription via WordPress cron (background processing)
     */
    public function run_transcription_cron($chapter_id) {
        $start_time = time();
        
        // Check if it's been stuck for a very long time (>10 minutes) to avoid infinite loops
        $existing_status = get_transient('ct_transcription_' . $chapter_id);
        if ($existing_status && isset($existing_status['status']) && $existing_status['status'] === 'processing') {
            $elapsed = isset($existing_status['started_at']) ? (time() - $existing_status['started_at']) : 0;
            // Only skip if it's been running for more than 10 minutes (likely stuck or actually processing)
            if ($elapsed > 600) {
                error_log('Transcription: Chapter ' . $chapter_id . ' stuck for ' . $elapsed . 's, skipping');
                return;
            }
        }
        
        // Update status
        $this->update_transcription_status($chapter_id, 'processing', 'Starting transcription process...', $start_time);
        
        // Run transcription with error handling
        try {
            $result = $this->transcribe_video($chapter_id, true);
        } catch (Exception $e) {
            error_log('Transcription: Exception for chapter ' . $chapter_id . ': ' . $e->getMessage());
            $result = array('success' => false, 'message' => 'Exception: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('Transcription: Fatal error for chapter ' . $chapter_id . ': ' . $e->getMessage());
            $result = array('success' => false, 'message' => 'Fatal error: ' . $e->getMessage());
        }
        
        // Update final status
        $elapsed = time() - $start_time;
        if ($result['success']) {
            $this->update_transcription_status($chapter_id, 'completed', $result['message'] . ' (Completed in ' . $elapsed . ' seconds)', $start_time, time());
            
            // Update bulk operation if this was part of one
            $this->update_bulk_transcription_status($chapter_id, true);
        } else {
            $this->update_transcription_status($chapter_id, 'failed', $result['message'] . ' (Failed after ' . $elapsed . ' seconds)', $start_time, null, time());
            
            // Update bulk operation if this was part of one
            $this->update_bulk_transcription_status($chapter_id, false);
        }
        
        return $result;
    }
    
    /**
     * Helper method to update transcription status with detailed info
     */
    private function update_transcription_status($chapter_id, $status, $message, $started_at = null, $completed_at = null, $failed_at = null) {
        $status_data = array(
            'status' => $status,
            'message' => $message,
            'timestamp' => time(),
            'time_formatted' => current_time('mysql')
        );
        
        if ($started_at) {
            $status_data['started_at'] = $started_at;
            $status_data['elapsed_seconds'] = time() - $started_at;
        }
        
        if ($completed_at) {
            $status_data['completed_at'] = $completed_at;
        }
        
        if ($failed_at) {
            $status_data['failed_at'] = $failed_at;
        }
        
        set_transient('ct_transcription_' . $chapter_id, $status_data, 3600);
        
        // Only log errors and completions, not every processing step
        if ($status === 'failed' || $status === 'completed') {
            error_log(sprintf(
                'Transcription [Chapter %d]: %s - %s (%ds)',
                $chapter_id,
                strtoupper($status),
                $message,
                isset($status_data['elapsed_seconds']) ? $status_data['elapsed_seconds'] : 0
            ));
        }
    }
    
    /**
     * Update bulk transcription status when individual transcription completes
     */
    private function update_bulk_transcription_status($chapter_id, $success) {
        // Find all active bulk operations that include this chapter
        global $wpdb;
        $transients = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_ct_bulk_%' 
             AND option_name NOT LIKE '_transient_timeout_%'"
        );
        
        foreach ($transients as $transient_name) {
            $bulk_id = str_replace('_transient_ct_bulk_', '', $transient_name);
            $operation_data = get_transient('ct_bulk_' . $bulk_id);
            
            // Only process transcribe operations
            if ($operation_data && isset($operation_data['type']) && $operation_data['type'] === 'transcribe' && in_array($chapter_id, $operation_data['chapter_ids'])) {
                if ($success) {
                    $operation_data['completed']++;
                    error_log(sprintf('Bulk transcribe: Chapter %d completed. Progress: %d/%d', $chapter_id, $operation_data['completed'], $operation_data['total']));
                } else {
                    $operation_data['failed']++;
                    if (!isset($operation_data['errors'])) {
                        $operation_data['errors'] = array();
                    }
                    $operation_data['errors'][$chapter_id] = 'Transcription failed';
                    error_log(sprintf('Bulk transcribe: Chapter %d failed. Progress: %d completed, %d failed', $chapter_id, $operation_data['completed'], $operation_data['failed']));
                }
                
                // Check if all items are done
                if (($operation_data['completed'] + $operation_data['failed']) >= $operation_data['total']) {
                    $operation_data['status'] = 'completed';
                    $operation_data['completed_at'] = time();
                    error_log(sprintf('Bulk transcribe: All items completed! Total: %d, Completed: %d, Failed: %d', 
                        $operation_data['total'], $operation_data['completed'], $operation_data['failed']));
                }
                
                set_transient('ct_bulk_' . $bulk_id, $operation_data, 7200);
                break;
            }
        }
    }
    
    /**
     * Ensure chapter title includes [idX] format for parsing
     * 
     * @param int $chapter_id Chapter ID
     * @param string $current_title Current chapter title
     * @return string Title with ID prefix if not already present
     */
    private function ensure_title_has_id($chapter_id, $current_title) {
        // Check if title already has [idX] format
        if (preg_match('/\[id\d+\]/', $current_title)) {
            // If it has an ID but it's wrong, replace it
            $current_title = preg_replace('/\[id\d+\]\s*/', '', $current_title);
        }
        
        // Add [idX] to the beginning of the title
        return '[id' . $chapter_id . '] ' . trim($current_title);
    }
    
    /**
     * Extract chapter ID from title with [idX] format
     * 
     * @param string $title Chapter title with [idX] format
     * @return int|false Chapter ID if found, false otherwise
     */
    public static function extract_id_from_title($title) {
        if (preg_match('/\[id(\d+)\]/', $title, $matches)) {
            return intval($matches[1]);
        }
        return false;
    }
    
    /**
     * Update chapter title in database to include [idX] format
     * 
     * @param int $chapter_id Chapter ID
     * @param string $title_with_id Title with ID prefix
     */
    private function update_chapter_title_with_id($chapter_id, $title_with_id) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'alm_chapters',
            array('chapter_title' => $title_with_id),
            array('ID' => $chapter_id),
            array('%s'),
            array('%d')
        );
    }
    
    /**
     * Transcribe an existing MP4 file for a chapter
     * 
     * @param int $chapter_id Chapter ID
     * @param bool $async Whether this is running asynchronously (for status updates)
     */
    private function transcribe_video($chapter_id, $async = false) {
        global $wpdb;
        
        // Get chapter
        $chapter = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_chapters WHERE ID = %d",
            $chapter_id
        ));
        
        if (!$chapter) {
            return array('success' => false, 'message' => 'Chapter not found.');
        }
        
        // Ensure chapter title has [idX] format and update database if needed
        $title_with_id = $this->ensure_title_has_id($chapter_id, $chapter->chapter_title);
        if ($title_with_id !== $chapter->chapter_title) {
            $this->update_chapter_title_with_id($chapter_id, $title_with_id);
            $chapter->chapter_title = $title_with_id; // Update local copy
            error_log('Transcription: Updated chapter ' . $chapter_id . ' title to include ID: ' . $title_with_id);
        }
        
        // Check if MP4 file exists
        $video_downloader = new Transcription_Video_Downloader();
        $mp4_filename = $video_downloader->generate_filename($chapter->lesson_id, $chapter_id, $chapter->chapter_title, 'mp4');
        $mp4_path = $video_downloader->get_output_directory() . $mp4_filename;
        
        if (!file_exists($mp4_path)) {
            return array('success' => false, 'message' => 'MP4 file not found. Please download the video first.');
        }
        
        // Always convert to audio for faster uploads and lower bandwidth
        // Whisper API only needs audio anyway, so video data is wasted
        if ($async) {
            $this->update_transcription_status($chapter_id, 'processing', 'Converting video to audio for faster transcription...');
        }
        
        // Log only if conversion is needed (not using existing file)
        $audio_path = $video_downloader->convert_to_audio($mp4_path);
        
        if (!$audio_path || !file_exists($audio_path)) {
            if ($async) {
                $this->update_transcription_status($chapter_id, 'failed', 'Failed to convert video to audio for transcription.');
            }
            return array('success' => false, 'message' => 'Failed to convert video to audio.');
        }
        
        // Validate the audio file
        if ($async) {
            $this->update_transcription_status($chapter_id, 'processing', 'Validating audio file...');
        }
        
        $whisper_client = new Transcription_Whisper_Client();
        $validation = $whisper_client->validate_file($audio_path);
        
        if (!$validation['success']) {
            if ($async) {
                $this->update_transcription_status($chapter_id, 'failed', 'Audio file validation failed: ' . $validation['message']);
            }
            // Clean up audio file
            @unlink($audio_path);
            return array('success' => false, 'message' => 'Audio file validation failed: ' . $validation['message']);
        }
        
        $file_to_transcribe = $audio_path;
        $temp_audio_file = true;
        
        if ($async) {
            $this->update_transcription_status($chapter_id, 'processing', sprintf(
                'Audio ready: %.2f MB (original was %.2f MB). Preparing upload...',
                $validation['file_size_mb'],
                filesize($mp4_path) / 1024 / 1024
            ));
        }
        
        // Log validation info
        error_log(sprintf(
            'Transcription: Validating chapter %d - File: %s, Size: %.2f MB, Duration: %.1f min, Estimated Cost: $%.4f',
            $chapter_id,
            basename($file_to_transcribe),
            $validation['file_size_mb'],
            $validation['duration_minutes'],
            $validation['estimated_cost']
        ));
        
        if ($async) {
            $this->update_transcription_status($chapter_id, 'processing', sprintf(
                'File validated: %.2f MB, %.1f min, $%.4f. Preparing upload...',
                $validation['file_size_mb'],
                $validation['duration_minutes'],
                $validation['estimated_cost']
            ));
        }
        
        if ($async) {
            $this->update_transcription_status($chapter_id, 'processing', 'Uploading file to OpenAI Whisper API...');
        }
        
        // Check if transcript exists for this specific chapter
        $existing_transcript = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}alm_transcripts WHERE lesson_id = %d AND chapter_id = %d AND source = 'whisper'",
            $chapter->lesson_id,
            $chapter_id
        ));
        
        if ($async) {
            $this->update_transcription_status($chapter_id, 'processing', 'File uploaded. Waiting for OpenAI to process transcription... (This may take several minutes)');
        }
        
        // Transcribe with Whisper (with retry logic and progress updates)
        // Store the path before we potentially return early - ensure cleanup in all cases
        $audio_file_to_delete = $temp_audio_file ? $file_to_transcribe : null;
        
        try {
            $transcription_result = $whisper_client->transcribe_file($file_to_transcribe, $chapter_id, 3, function($status, $message) use ($chapter_id, $async) {
                if ($async) {
                    $this->update_transcription_status($chapter_id, 'processing', $message);
                }
            });
        } catch (Exception $e) {
            // Clean up on exception
            if ($audio_file_to_delete && file_exists($audio_file_to_delete)) {
                @unlink($audio_file_to_delete);
            }
            $error_msg = 'Exception during transcription: ' . $e->getMessage();
            error_log('Transcription: ' . $error_msg);
            if ($async) {
                $this->update_transcription_status($chapter_id, 'failed', $error_msg);
            }
            return array('success' => false, 'message' => $error_msg);
        } catch (Error $e) {
            // Clean up on fatal error
            if ($audio_file_to_delete && file_exists($audio_file_to_delete)) {
                @unlink($audio_file_to_delete);
            }
            $error_msg = 'Fatal error during transcription: ' . $e->getMessage();
            error_log('Transcription: ' . $error_msg);
            if ($async) {
                $this->update_transcription_status($chapter_id, 'failed', $error_msg);
            }
            return array('success' => false, 'message' => $error_msg);
        }
        
        // Clean up temporary audio file if we created one (always, even on error)
        if (!$transcription_result['success']) {
            // Clean up before returning error
            if ($audio_file_to_delete && file_exists($audio_file_to_delete)) {
                $deleted = @unlink($audio_file_to_delete);
                if (!$deleted) {
                    error_log('Transcription: WARNING - Failed to delete temporary audio file: ' . basename($audio_file_to_delete));
                }
            }
            
            if ($async) {
                $this->update_transcription_status($chapter_id, 'failed', 'Transcription failed: ' . $transcription_result['message']);
            }
            return array('success' => false, 'message' => 'Transcription failed: ' . $transcription_result['message']);
        }
        
        // Generate VTT file
        if ($async) {
            $this->update_transcription_status($chapter_id, 'processing', 'Transcription received! Generating VTT file...');
        }
        
        $vtt_generator = new Transcription_VTT_Generator();
        $video_downloader = new Transcription_Video_Downloader();
        $vtt_filename = $video_downloader->generate_filename($chapter->lesson_id, $chapter_id, $chapter->chapter_title, 'vtt');
        $vtt_path = $video_downloader->get_transcripts_directory() . $vtt_filename;
        
        // Extract segments (don't log the full array - too verbose)
        $segments = isset($transcription_result['segments']) ? $transcription_result['segments'] : null;
        $segment_count = is_array($segments) ? count($segments) : 0;
        
        $vtt_success = $vtt_generator->generate_vtt(
            $transcription_result['text'],
            $segments,
            $transcription_result['duration_seconds'],
            $vtt_path
        );
        
        if (!$vtt_success) {
            error_log('Transcription: Failed to generate VTT file for chapter ' . $chapter_id);
        }
        
        // Get VTT filename only (no path, so it can be used anywhere)
        $vtt_file_for_db = null;
        if ($vtt_success && file_exists($vtt_path)) {
            // Store just the filename, not the path
            $vtt_file_for_db = basename($vtt_path);
        }
        
        if ($async) {
            $this->update_transcription_status($chapter_id, 'processing', 'VTT file generated. Saving transcript to database...');
        }
        
        // Save to database
        if ($existing_transcript) {
            // Update existing transcript
            $update_data = array(
                'chapter_id' => $chapter_id,
                'content' => $transcription_result['text'],
                'updated_at' => current_time('mysql')
            );
            
            // Add VTT filename if available
            if ($vtt_file_for_db) {
                $update_data['vtt_file'] = $vtt_file_for_db;
            }
            
            $update_result = $wpdb->update(
                $wpdb->prefix . 'alm_transcripts',
                $update_data,
                array('ID' => $existing_transcript),
                $vtt_file_for_db ? array('%d', '%s', '%s', '%s') : array('%d', '%s', '%s'),
                array('%d')
            );
            
            if ($update_result === false) {
                $error = $wpdb->last_error;
                error_log('Transcription: Failed to update transcript: ' . $error);
                return array('success' => false, 'message' => 'Failed to update transcript in database: ' . $error);
            }
            
            // Clean up temporary audio file after successful transcription
            if ($audio_file_to_delete && file_exists($audio_file_to_delete)) {
                $deleted = @unlink($audio_file_to_delete);
                if ($deleted) {
                    error_log('Transcription: Deleted temporary audio file: ' . basename($audio_file_to_delete));
                } else {
                    error_log('Transcription: Warning - Failed to delete temporary audio file: ' . basename($audio_file_to_delete));
                }
            }
            
            return array('success' => true, 'message' => 'Transcript updated successfully. VTT file generated.');
        } else {
            // Insert new transcript
            $insert_data = array(
                'lesson_id' => $chapter->lesson_id,
                'chapter_id' => $chapter_id,
                'source' => 'whisper',
                'content' => $transcription_result['text'],
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );
            
            // Add VTT filename if available
            if ($vtt_file_for_db) {
                $insert_data['vtt_file'] = $vtt_file_for_db;
            }
            
            $insert_result = $wpdb->insert(
                $wpdb->prefix . 'alm_transcripts',
                $insert_data,
                $vtt_file_for_db ? array('%d', '%d', '%s', '%s', '%s', '%s', '%s') : array('%d', '%d', '%s', '%s', '%s', '%s')
            );
            
            if ($insert_result === false) {
                $error = $wpdb->last_error;
                
                // Check if it's a duplicate key error (shouldn't happen with new constraint, but just in case)
                if (strpos($error, 'Duplicate entry') !== false) {
                    // Try to update instead
                    $existing = $wpdb->get_var($wpdb->prepare(
                        "SELECT ID FROM {$wpdb->prefix}alm_transcripts WHERE lesson_id = %d AND chapter_id = %d AND source = 'whisper'",
                        $chapter->lesson_id,
                        $chapter_id
                    ));
                    
                    if ($existing) {
                        $update_data = array(
                            'content' => $transcription_result['text'],
                            'updated_at' => current_time('mysql')
                        );
                        
                        // Add VTT filename if available
                        if ($vtt_file_for_db) {
                            $update_data['vtt_file'] = $vtt_file_for_db;
                        }
                        
                        $wpdb->update(
                            $wpdb->prefix . 'alm_transcripts',
                            $update_data,
                            array('ID' => $existing),
                            $vtt_file_for_db ? array('%s', '%s', '%s') : array('%s', '%s'),
                            array('%d')
                        );
                        return array('success' => true, 'message' => 'Transcript saved successfully. VTT file generated.');
                    }
                }
                
                error_log('Transcription: Failed to insert transcript: ' . $error);
                return array('success' => false, 'message' => 'Failed to save transcript to database: ' . $error);
            }
            
            // Clean up temporary audio file after successful transcription
            if ($audio_file_to_delete && file_exists($audio_file_to_delete)) {
                $deleted = @unlink($audio_file_to_delete);
                if ($deleted) {
                    error_log('Transcription: Deleted temporary audio file: ' . basename($audio_file_to_delete));
                } else {
                    error_log('Transcription: Warning - Failed to delete temporary audio file: ' . basename($audio_file_to_delete));
                }
            }
            
            return array('success' => true, 'message' => 'Transcript saved successfully. VTT file generated.');
        }
    }

    /**
     * Create queue table if it doesn't exist
     */
    private static function create_queue_table() {
        global $wpdb;
        
        if (!isset($wpdb)) {
            return;
        }
        
        $table_name = $wpdb->prefix . 'alm_transcription_jobs';
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $sql = "CREATE TABLE {$table_name} (
            ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            chapter_id bigint(20) unsigned NOT NULL,
            lesson_id bigint(20) unsigned NOT NULL,
            job_type varchar(50) NOT NULL DEFAULT 'transcribe',
            status varchar(20) NOT NULL DEFAULT 'pending',
            priority int(11) NOT NULL DEFAULT 10,
            attempts tinyint(3) unsigned NOT NULL DEFAULT 0,
            message text NULL,
            payload longtext NULL,
            created_at datetime NOT NULL,
            started_at datetime NULL,
            completed_at datetime NULL,
            PRIMARY KEY  (ID),
            KEY status (status),
            KEY chapter_id (chapter_id),
            KEY lesson_id (lesson_id)
        ) {$charset_collate};";
        
        dbDelta($sql);
    }
    
    /**
     * Ensure queue table exists (handles upgrades without reactivation)
     */
    private function maybe_initialize_queue_table() {
        global $wpdb;
        
        if (!$this->queue_table) {
            $this->queue_table = $wpdb->prefix . 'alm_transcription_jobs';
        }
        
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $this->queue_table
        ));
        
        if ($table_exists !== $this->queue_table) {
            self::create_queue_table();
        }
    }
    
    /**
     * Register custom cron schedule
     */
    public function register_queue_schedule($schedules) {
        if (!isset($schedules['ct_queue_minute'])) {
            $schedules['ct_queue_minute'] = array(
                'interval' => 60,
                'display' => __('Every Minute', 'chapter-transcription')
            );
        }
        
        return $schedules;
    }
    
    /**
     * Ensure recurring queue processor is scheduled
     */
    private function ensure_queue_schedule() {
        if (!wp_next_scheduled('ct_process_transcription_queue')) {
            wp_schedule_event(time() + 60, 'ct_queue_minute', 'ct_process_transcription_queue');
        }
    }
    
    /**
     * Schedule a near-term queue runner
     */
    private function schedule_queue_runner($delay = 10) {
        $timestamp = time() + max(5, intval($delay));
        wp_schedule_single_event($timestamp, 'ct_process_transcription_queue');
    }
    
    /**
     * Queue lock helpers
     */
    private function is_queue_locked() {
        return (bool) get_transient('ct_queue_lock');
    }
    
    private function lock_queue() {
        set_transient('ct_queue_lock', 1, 2 * MINUTE_IN_SECONDS);
    }
    
    private function unlock_queue() {
        delete_transient('ct_queue_lock');
    }
    
    /**
     * Enqueue a transcription job
     */
    private function enqueue_transcription_job($chapter_id, $job_type = 'transcribe', $metadata = array()) {
        global $wpdb;
        
        if (!$chapter_id) {
            return array('success' => false, 'message' => 'Invalid chapter ID');
        }
        
        if (!$this->queue_table) {
            $this->queue_table = $wpdb->prefix . 'alm_transcription_jobs';
        }
        
        $chapter = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, lesson_id FROM {$wpdb->prefix}alm_chapters WHERE ID = %d",
            $chapter_id
        ));
        
        if (!$chapter) {
            return array('success' => false, 'message' => 'Chapter not found');
        }
        
        $existing_job_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$this->queue_table} WHERE chapter_id = %d AND status IN ('pending', 'processing') LIMIT 1",
            $chapter_id
        ));
        
        if ($existing_job_id) {
            $this->update_transcription_status($chapter_id, 'queued', 'Chapter already queued for transcription');
            $this->schedule_queue_runner(5);
            return array(
                'success' => true,
                'message' => 'Chapter is already queued for transcription.',
                'job_id' => intval($existing_job_id)
            );
        }
        
        $payload = !empty($metadata) ? wp_json_encode($metadata) : null;
        
        $inserted = $wpdb->insert(
            $this->queue_table,
            array(
                'chapter_id' => $chapter_id,
                'lesson_id' => $chapter->lesson_id,
                'job_type' => $job_type,
                'status' => 'pending',
                'priority' => 10,
                'attempts' => 0,
                'message' => '',
                'payload' => $payload,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s')
        );
        
        if ($inserted === false) {
            return array('success' => false, 'message' => 'Failed to enqueue transcription job.');
        }
        
        $this->update_transcription_status($chapter_id, 'queued', 'Queued for processing');
        $this->schedule_queue_runner(5);
        
        return array(
            'success' => true,
            'message' => 'Chapter added to transcription queue.',
            'job_id' => $wpdb->insert_id
        );
    }
    
    /**
     * Process pending transcription jobs
     */
    public function process_transcription_queue() {
        if ($this->is_queue_locked()) {
            return;
        }
        
        $this->lock_queue();
        $jobs_processed = 0;
        
        while ($jobs_processed < 1) {
            $job = $this->claim_next_job();
            if (!$job) {
                break;
            }
            
            $result = $this->run_queued_job($job);
            $this->finalize_job($job, $result);
            $jobs_processed++;
        }
        
        $this->unlock_queue();
        set_transient('ct_queue_last_run', time(), 12 * HOUR_IN_SECONDS);
        
        if ($this->queue_has_pending()) {
            $this->schedule_queue_runner(15);
        }
    }
    
    /**
     * Claim the next pending job
     */
    private function claim_next_job() {
        global $wpdb;
        
        if (!$this->queue_table) {
            $this->queue_table = $wpdb->prefix . 'alm_transcription_jobs';
        }
        
        $job = $wpdb->get_row("SELECT * FROM {$this->queue_table} WHERE status = 'pending' ORDER BY priority DESC, ID ASC LIMIT 1");
        
        if (!$job) {
            return false;
        }
        
        $updated = $wpdb->update(
            $this->queue_table,
            array(
                'status' => 'processing',
                'started_at' => current_time('mysql'),
                'attempts' => intval($job->attempts) + 1
            ),
            array(
                'ID' => $job->ID,
                'status' => 'pending'
            ),
            array('%s', '%s', '%d'),
            array('%d', '%s')
        );
        
        if (!$updated) {
            return false;
        }
        
        $job->status = 'processing';
        $job->started_at = current_time('mysql');
        $job->attempts = intval($job->attempts) + 1;
        
        return $job;
    }
    
    /**
     * Execute a queued job
     */
    private function run_queued_job($job) {
        $chapter_id = intval($job->chapter_id);
        $metadata = !empty($job->payload) ? json_decode($job->payload, true) : array();
        
        try {
            if ($job->job_type === 'download_transcribe') {
                $this->update_transcription_status($chapter_id, 'processing', 'Downloading video before transcription...');
                $download_result = $this->download_video($chapter_id);
                
                if (!$download_result['success']) {
                    $this->update_transcription_status($chapter_id, 'failed', 'Download failed: ' . $download_result['message'], time(), null, time());
                    return array('success' => false, 'message' => 'Download failed: ' . $download_result['message']);
                }
            }
            
            $result = $this->run_transcription_cron($chapter_id);
            
            if (is_array($result)) {
                return $result;
            }
            
            return array('success' => false, 'message' => 'Unknown transcription result.');
        } catch (Exception $e) {
            $error = 'Queue worker exception: ' . $e->getMessage();
            error_log('Transcription Queue: ' . $error);
            $this->update_transcription_status($chapter_id, 'failed', $error);
            return array('success' => false, 'message' => $error);
        } catch (Error $e) {
            $error = 'Queue worker fatal error: ' . $e->getMessage();
            error_log('Transcription Queue: ' . $error);
            $this->update_transcription_status($chapter_id, 'failed', $error);
            return array('success' => false, 'message' => $error);
        }
    }
    
    /**
     * Finalize a job after processing
     */
    private function finalize_job($job, $result) {
        global $wpdb;
        
        if (!$this->queue_table) {
            $this->queue_table = $wpdb->prefix . 'alm_transcription_jobs';
        }
        
        $wpdb->update(
            $this->queue_table,
            array(
                'status' => $result['success'] ? 'completed' : 'failed',
                'completed_at' => current_time('mysql'),
                'message' => isset($result['message']) ? $result['message'] : ''
            ),
            array('ID' => $job->ID),
            array('%s', '%s', '%s'),
            array('%d')
        );
    }
    
    /**
     * Determine if there are pending jobs
     */
    private function queue_has_pending() {
        global $wpdb;
        
        if (!$this->queue_table) {
            $this->queue_table = $wpdb->prefix . 'alm_transcription_jobs';
        }
        
        $pending = $wpdb->get_var("SELECT COUNT(*) FROM {$this->queue_table} WHERE status = 'pending'");
        return intval($pending) > 0;
    }
    
    /**
     * Retrieve queue statistics for admin display
     */
    private function get_queue_stats() {
        global $wpdb;
        
        if (!$this->queue_table) {
            $this->queue_table = $wpdb->prefix . 'alm_transcription_jobs';
        }
        
        $stats = array(
            'pending' => 0,
            'processing' => 0,
            'completed_24h' => 0,
            'failed_24h' => 0,
            'locked' => $this->is_queue_locked(),
            'next_run' => wp_next_scheduled('ct_process_transcription_queue'),
            'last_run' => get_transient('ct_queue_last_run'),
            'recent_jobs' => array()
        );
        
        if ($this->queue_table && $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $this->queue_table)) === $this->queue_table) {
            $stats['pending'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$this->queue_table} WHERE status = 'pending'"));
            $stats['processing'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$this->queue_table} WHERE status = 'processing'"));
            $stats['completed_24h'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$this->queue_table} WHERE status = 'completed' AND completed_at >= (NOW() - INTERVAL 24 HOUR)"));
            $stats['failed_24h'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$this->queue_table} WHERE status = 'failed' AND completed_at >= (NOW() - INTERVAL 24 HOUR)"));
            $stats['recent_jobs'] = $wpdb->get_results("
                SELECT ID, chapter_id, job_type, status, message, created_at, started_at, completed_at
                FROM {$this->queue_table}
                ORDER BY ID DESC
                LIMIT 5
            ");
        }
        
        return $stats;
    }
    
    /**
     * Calculate queue position for a chapter
     */
    private function get_queue_position_for_chapter($chapter_id) {
        global $wpdb;
        
        if (!$this->queue_table) {
            $this->queue_table = $wpdb->prefix . 'alm_transcription_jobs';
        }
        
        $job = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, priority FROM {$this->queue_table} WHERE chapter_id = %d AND status = 'pending' LIMIT 1",
            $chapter_id
        ));
        
        if (!$job) {
            return null;
        }
        
        $ahead = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->queue_table} 
             WHERE status = 'pending' 
             AND (priority > %d OR (priority = %d AND ID < %d))",
            $job->priority,
            $job->priority,
            $job->ID
        ));
        
        $total_pending = $wpdb->get_var("SELECT COUNT(*) FROM {$this->queue_table} WHERE status = 'pending'");
        
        return array(
            'position' => intval($ahead) + 1,
            'total' => intval($total_pending)
        );
    }
    
    /**
     * Process transcription for a chapter (legacy - kept for backwards compatibility)
     * @deprecated Use download_video() and transcribe_video() separately
     */
    private function process_transcription($chapter_id) {
        global $wpdb;
        
        // Get chapter
        $chapter = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_chapters WHERE ID = %d",
            $chapter_id
        ));
        
        if (!$chapter) {
            return array('success' => false, 'message' => 'Chapter not found.');
        }
        
        // Determine video source
        $video_source = 'none';
        $video_url = '';
        
        if (!empty($chapter->bunny_url)) {
            $video_source = 'bunny';
            $video_url = $chapter->bunny_url;
        } elseif ($chapter->vimeo_id > 0) {
            $video_source = 'vimeo';
            $video_url = $chapter->vimeo_id;
        }
        
        if ($video_source === 'none') {
            return array('success' => false, 'message' => 'Chapter has no video source (Bunny URL or Vimeo ID).');
        }
        
        // Check if transcript exists for this specific chapter
        $existing_transcript = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}alm_transcripts WHERE lesson_id = %d AND chapter_id = %d AND source = 'whisper'",
            $chapter->lesson_id,
            $chapter_id
        ));
        
        // Step 1: Download and convert video
        $video_downloader = new Transcription_Video_Downloader();
        $mp4_path = $video_downloader->download_and_convert($video_url, $chapter->lesson_id, $chapter_id, $chapter->chapter_title, $video_source);
        
        if (!$mp4_path || !file_exists($mp4_path)) {
            return array('success' => false, 'message' => 'Failed to download/convert video.');
        }
        
        // Step 2: Transcribe with Whisper
        $whisper_client = new Transcription_Whisper_Client();
        $transcription_result = $whisper_client->transcribe_file($mp4_path, $chapter_id);
        
        if (!$transcription_result['success']) {
            return array('success' => false, 'message' => 'Transcription failed: ' . $transcription_result['message']);
        }
        
        // Step 3: Generate VTT file
        $vtt_generator = new Transcription_VTT_Generator();
        $video_downloader = new Transcription_Video_Downloader();
        $vtt_filename = $video_downloader->generate_filename($chapter->lesson_id, $chapter_id, $chapter->chapter_title, 'vtt');
        $vtt_path = $video_downloader->get_transcripts_directory() . $vtt_filename;
        
        $vtt_success = $vtt_generator->generate_vtt(
            $transcription_result['text'],
            isset($transcription_result['segments']) ? $transcription_result['segments'] : null,
            $transcription_result['duration_seconds'],
            $vtt_path
        );
        
        if (!$vtt_success) {
            error_log('Transcription: Failed to generate VTT file for chapter ' . $chapter_id);
        }
        
        // Get VTT filename only (no path, so it can be used anywhere)
        $vtt_file_for_db = null;
        if ($vtt_success && file_exists($vtt_path)) {
            // Store just the filename, not the path
            $vtt_file_for_db = basename($vtt_path);
        }
        
        // Step 4: Save to database
        if ($existing_transcript) {
            // Update existing transcript - also update chapter_id in case it was 0 or wrong
            $update_data = array(
                'chapter_id' => $chapter_id,
                'content' => $transcription_result['text'],
                'updated_at' => current_time('mysql')
            );
            
            // Add VTT filename if available
            if ($vtt_file_for_db) {
                $update_data['vtt_file'] = $vtt_file_for_db;
            }
            
            $update_result = $wpdb->update(
                $wpdb->prefix . 'alm_transcripts',
                $update_data,
                array('ID' => $existing_transcript),
                $vtt_file_for_db ? array('%d', '%s', '%s', '%s') : array('%d', '%s', '%s'),
                array('%d')
            );
            
            if ($update_result === false) {
                $error = $wpdb->last_error;
                error_log('Transcription: Database update failed - ' . $error);
                return array('success' => false, 'message' => 'Failed to update transcript in database: ' . $error);
            }
            
            $message = 'Transcript updated successfully.';
        } else {
            $insert_data = array(
                'lesson_id' => $chapter->lesson_id,
                'chapter_id' => $chapter_id,
                'source' => 'whisper',
                'content' => $transcription_result['text'],
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );
            
            // Add VTT filename if available
            if ($vtt_file_for_db) {
                $insert_data['vtt_file'] = $vtt_file_for_db;
            }
            
            $insert_result = $wpdb->insert(
                $wpdb->prefix . 'alm_transcripts',
                $insert_data,
                $vtt_file_for_db ? array('%d', '%d', '%s', '%s', '%s', '%s', '%s') : array('%d', '%d', '%s', '%s', '%s', '%s')
            );
            
            if ($insert_result === false) {
                $error = $wpdb->last_error;
                error_log('Transcription: Database insert failed - ' . $error);
                error_log('Transcription: Attempted to insert - lesson_id: ' . $chapter->lesson_id . ', chapter_id: ' . $chapter_id);
                
                // Check if it's a duplicate key error
                if (strpos($error, 'Duplicate entry') !== false || strpos($error, 'uniq_lesson_source') !== false) {
                    // The unique constraint is (lesson_id, source), so find existing by that
                    $existing = $wpdb->get_var($wpdb->prepare(
                        "SELECT ID FROM {$wpdb->prefix}alm_transcripts WHERE lesson_id = %d AND source = 'whisper'",
                        $chapter->lesson_id
                    ));
                    
                    if ($existing) {
                        // Update the existing record with new chapter_id and content
                        $update_data = array(
                            'chapter_id' => $chapter_id,
                            'content' => $transcription_result['text'],
                            'updated_at' => current_time('mysql')
                        );
                        
                        // Add VTT file path if available
                        if ($vtt_file_url) {
                            $update_data['vtt_file'] = $vtt_file_url;
                        }
                        
                        $update_result = $wpdb->update(
                            $wpdb->prefix . 'alm_transcripts',
                            $update_data,
                            array('ID' => $existing),
                            $vtt_file_url ? array('%d', '%s', '%s', '%s') : array('%d', '%s', '%s'),
                            array('%d')
                        );
                        
                        if ($update_result !== false) {
                            $message = 'Transcript updated successfully (replaced existing transcript for this lesson).';
                        } else {
                            $update_error = $wpdb->last_error;
                            error_log('Transcription: Update after duplicate failed - ' . $update_error);
                            return array('success' => false, 'message' => 'Failed to save transcript: duplicate entry and update failed. ' . $update_error);
                        }
                    } else {
                        return array('success' => false, 'message' => 'Failed to save transcript: duplicate entry but could not find existing record. ' . $error);
                    }
                } else {
                    return array('success' => false, 'message' => 'Failed to save transcript: ' . $error);
                }
            } else {
                $message = 'Transcript saved successfully.';
            }
        }
        
        return array('success' => true, 'message' => $message);
    }
    
    /**
     * Chapters bulk management page
     */
    public function admin_page_chapters() {
        // Pagination settings
        $allowed_per_page = array(5, 15, 20, 25, 30, 100, 150, 200, 300, 400, 500);
        $per_page = isset($_GET['per_page']) && in_array(intval($_GET['per_page']), $allowed_per_page) 
            ? intval($_GET['per_page']) 
            : 50;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        // Hide transcribed filter
        $hide_transcribed = isset($_GET['hide_transcribed']) && $_GET['hide_transcribed'] === '1';
        
        // Build WHERE clause
        global $wpdb;
        $where_clause = "c.lesson_id > 0 AND ((c.bunny_url != '' AND c.bunny_url IS NOT NULL) OR c.vimeo_id > 0)";
        
        // Add hide transcribed filter
        if ($hide_transcribed) {
            $where_clause .= " AND NOT EXISTS (
                SELECT 1 FROM {$wpdb->prefix}alm_transcripts t 
                WHERE t.chapter_id = c.ID
            )";
        }
        
        // Get total count
        $total_items = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->prefix}alm_chapters c
            WHERE {$where_clause}
        ");
        
        $total_pages = ceil($total_items / $per_page);
        
        // Get chapters
        $chapters = $wpdb->get_results($wpdb->prepare("
            SELECT c.*, l.lesson_title
            FROM {$wpdb->prefix}alm_chapters c
            LEFT JOIN {$wpdb->prefix}alm_lessons l ON c.lesson_id = l.ID
            WHERE {$where_clause}
            ORDER BY c.ID ASC
            LIMIT %d OFFSET %d
        ", $per_page, $offset));
        
        // Check which chapters have transcripts
        $chapter_ids = array_map(function($c) { return $c->ID; }, $chapters);
        $transcripted_chapters = array();
        if (!empty($chapter_ids)) {
            $placeholders = implode(',', array_fill(0, count($chapter_ids), '%d'));
            $transcripted = $wpdb->get_col($wpdb->prepare("
                SELECT DISTINCT chapter_id
                FROM {$wpdb->prefix}alm_transcripts
                WHERE chapter_id IN ($placeholders)
            ", $chapter_ids));
            $transcripted_chapters = array_flip($transcripted);
        }
        
        // Include template
        $template_data = array(
            'chapters' => $chapters,
            'transcripted_chapters' => $transcripted_chapters,
            'pagination' => array(
                'current_page' => $current_page,
                'total_pages' => $total_pages,
                'total_items' => $total_items,
                'per_page' => $per_page
            ),
            'hide_transcribed' => $hide_transcribed,
            'allowed_per_page' => $allowed_per_page
        );
        
        include plugin_dir_path(__FILE__) . 'templates/chapters-page.php';
    }
    
    /**
     * AJAX handler for bulk download and transcribe
     */
    public function ajax_bulk_download_and_transcribe() {
        check_ajax_referer('ct_transcription_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $chapter_ids = isset($_POST['chapter_ids']) ? array_map('intval', $_POST['chapter_ids']) : array();
        
        if (empty($chapter_ids)) {
            wp_send_json_error('No chapters selected');
        }
        
        // Limit to prevent timeouts
        if (count($chapter_ids) > 500) {
            wp_send_json_error('Maximum 500 chapters at a time');
        }
        
        $results = array();
        $success_count = 0;
        $error_count = 0;
        
        foreach ($chapter_ids as $chapter_id) {
            $this->update_transcription_status($chapter_id, 'queued', 'Queued for download & transcription');
            $enqueue = $this->enqueue_transcription_job($chapter_id, 'download_transcribe');
            
            if ($enqueue['success']) {
                $success_count++;
            } else {
                $error_count++;
            }
            
            $results[] = array(
                'chapter_id' => $chapter_id,
                'success' => $enqueue['success'],
                'message' => $enqueue['message']
            );
        }
        
        wp_send_json_success(array(
            'message' => sprintf('Processed %d chapters: %d successful, %d failed', count($chapter_ids), $success_count, $error_count),
            'results' => $results,
            'success_count' => $success_count,
            'error_count' => $error_count
        ));
    }
}

// Register activation/deactivation hooks
register_activation_hook(CT_PLUGIN_FILE, array('Chapter_Transcription', 'activate'));
register_deactivation_hook(CT_PLUGIN_FILE, array('Chapter_Transcription', 'deactivate'));

// Initialize the plugin
new Chapter_Transcription();

