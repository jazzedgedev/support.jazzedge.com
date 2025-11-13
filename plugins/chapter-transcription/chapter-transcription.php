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
     * Constructor
     */
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Include required files
        $this->include_files();
        
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
        add_action('wp_ajax_ct_run_transcription_direct', array($this, 'ajax_run_transcription_direct'));
        add_action('wp_ajax_nopriv_ct_run_transcription_direct', array($this, 'ajax_run_transcription_direct')); // Allow non-logged-in requests for background processing
        add_action('wp_ajax_ct_get_debug_log', array($this, 'ajax_get_debug_log'));
        add_action('wp_ajax_ct_clear_all_stuck', array($this, 'ajax_clear_all_stuck'));
        
        // Cron hook for background transcription
        add_action('ct_run_transcription', array($this, 'run_transcription_cron'));
        add_action('ct_process_bulk_download', array($this, 'process_bulk_download_cron'), 10, 3);
        add_action('ct_process_bulk_transcribe', array($this, 'process_bulk_transcribe_cron'), 10, 3);
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_chapter-transcription') {
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
        
        // Set status to processing with detailed info
        $this->update_transcription_status($chapter_id, 'processing', 'Initializing transcription process...', time());
        
        // Try multiple methods to ensure execution
        $nonce = wp_create_nonce('ct_transcription_nonce');
        
        // Method 1: Non-blocking HTTP request (may not work in all environments)
        $response = wp_remote_post(admin_url('admin-ajax.php'), array(
            'timeout' => 0.01,
            'blocking' => false,
            'sslverify' => false,
            'body' => array(
                'action' => 'ct_run_transcription_direct',
                'chapter_id' => $chapter_id,
                'nonce' => $nonce
            )
        ));
        
        // Method 2: Schedule via cron as backup
        $scheduled = wp_schedule_single_event(time() + 2, 'ct_run_transcription', array($chapter_id));
        
        // Method 3: Also try a slightly delayed cron (in case first one fails)
        wp_schedule_single_event(time() + 5, 'ct_run_transcription', array($chapter_id));
        
        if ($scheduled !== false && (!defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON)) {
            // Try to trigger cron immediately
            spawn_cron();
        }
        
        // Log what method we're using
        if (is_wp_error($response)) {
            error_log('Transcription: Direct execution request failed, relying on cron. Error: ' . $response->get_error_message());
        } else {
            error_log('Transcription: Started via direct execution (non-blocking) for chapter ' . $chapter_id);
        }
        
        // Method 4: For web context, also try a curl-based approach as ultimate fallback
        // This will execute in the background even if wp_remote_post fails
        $this->trigger_background_transcription($chapter_id, $nonce);
        
        // Method 5: For local development, also try a synchronous test request to verify handler works
        // This helps debug if background requests are being ignored
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // Test if the handler is reachable (but don't wait for response)
            $test_response = wp_remote_post(admin_url('admin-ajax.php'), array(
                'timeout' => 2,
                'blocking' => false,
                'sslverify' => false,
                'body' => array(
                    'action' => 'ct_test_handler',
                    'chapter_id' => $chapter_id,
                    'nonce' => $nonce
                )
            ));
        }
        
        // Send response immediately
        wp_send_json_success(array(
            'message' => 'Transcription started',
            'chapter_id' => $chapter_id
        ));
    }
    
    /**
     * Trigger background transcription using curl (more reliable fallback)
     */
    private function trigger_background_transcription($chapter_id, $nonce) {
        $url = admin_url('admin-ajax.php');
        $post_data = array(
            'action' => 'ct_run_transcription_direct',
            'chapter_id' => $chapter_id,
            'nonce' => $nonce
        );
        
        // Use curl if available (more reliable than wp_remote_post for background tasks)
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded'
            ));
            
            $result = curl_exec($ch);
            $curl_error = curl_error($ch);
            $curl_info = curl_getinfo($ch);
            curl_close($ch);
            
            error_log('Transcription: Triggered via curl for chapter ' . $chapter_id . '. HTTP Code: ' . $curl_info['http_code'] . ($curl_error ? '. Error: ' . $curl_error : ''));
            
            if ($curl_error) {
                error_log('Transcription: cURL error details: ' . $curl_error);
            }
        } else {
            error_log('Transcription: cURL not available, cannot trigger background transcription');
        }
    }
    
    /**
     * AJAX handler: Run transcription directly (fallback if cron fails)
     */
    public function ajax_run_transcription_direct() {
        // Log that this handler was called (critical for debugging)
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        error_log('Transcription: ajax_run_transcription_direct called for chapter ' . $chapter_id);
        
        // For local development - minimal security checks
        // Just verify we have a chapter_id (basic validation)
        if (!$chapter_id) {
            error_log('Transcription: Invalid chapter ID for direct execution');
            status_header(400);
            exit;
        }
        
        // Close connection to browser immediately so request doesn't timeout
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
            error_log('Transcription: Connection closed via fastcgi_finish_request for chapter ' . $chapter_id);
        } else {
            // Fallback for non-FastCGI environments
            ignore_user_abort(true);
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Connection: close');
            header('Content-Length: 0');
            header('Content-Type: text/plain');
            flush();
            error_log('Transcription: Connection closed via headers for chapter ' . $chapter_id);
        }
        
        // Small delay to ensure connection is closed
        usleep(100000); // 0.1 seconds
        
        error_log('Transcription: About to call run_transcription_cron for chapter ' . $chapter_id);
        
        // Run transcription
        try {
            $this->run_transcription_cron($chapter_id);
            error_log('Transcription: run_transcription_cron completed for chapter ' . $chapter_id);
        } catch (Exception $e) {
            error_log('Transcription: Exception in run_transcription_cron for chapter ' . $chapter_id . ': ' . $e->getMessage());
        } catch (Error $e) {
            error_log('Transcription: Fatal error in run_transcription_cron for chapter ' . $chapter_id . ': ' . $e->getMessage());
        }
        
        exit;
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
        
        foreach ($chapter_ids as $index => $chapter_id) {
            // Start each transcription immediately
            $this->start_transcription_for_bulk($chapter_id, $bulk_id);
        }
        
        wp_send_json_success(array(
            'bulk_id' => $bulk_id,
            'total' => count($chapter_ids),
            'message' => 'Started ' . count($chapter_ids) . ' transcriptions'
        ));
    }
    
    /**
     * Start a single transcription for bulk operation
     */
    private function start_transcription_for_bulk($chapter_id, $bulk_id) {
        // Set status
        $this->update_transcription_status($chapter_id, 'processing', 'Initializing transcription process...', time());
        
        // Use direct execution (same as individual transcriptions)
        $nonce = wp_create_nonce('ct_transcription_nonce');
        
        // Method 1: Non-blocking HTTP request
        $response = wp_remote_post(admin_url('admin-ajax.php'), array(
            'timeout' => 0.01,
            'blocking' => false,
            'sslverify' => false,
            'body' => array(
                'action' => 'ct_run_transcription_direct',
                'chapter_id' => $chapter_id,
                'nonce' => $nonce
            )
        ));
        
        // Method 2: Schedule via cron as backup
        wp_schedule_single_event(time() + 2, 'ct_run_transcription', array($chapter_id));
        wp_schedule_single_event(time() + 5, 'ct_run_transcription', array($chapter_id));
        
        if (!defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON) {
            spawn_cron();
        }
        
        // Method 3: cURL fallback
        $this->trigger_background_transcription($chapter_id, $nonce);
        
        error_log('Bulk transcribe: Started transcription for chapter ' . $chapter_id);
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
        
        // Start transcription using the same direct execution method as individual transcriptions
        $this->update_transcription_status($chapter_id, 'processing', 'Initializing transcription process...', time());
        
        // Use direct execution (same as individual transcriptions)
        $nonce = wp_create_nonce('ct_transcription_nonce');
        
        // Method 1: Non-blocking HTTP request
        $response = wp_remote_post(admin_url('admin-ajax.php'), array(
            'timeout' => 0.01,
            'blocking' => false,
            'sslverify' => false,
            'body' => array(
                'action' => 'ct_run_transcription_direct',
                'chapter_id' => $chapter_id,
                'nonce' => $nonce
            )
        ));
        
        // Method 2: Schedule via cron as backup
        wp_schedule_single_event(time() + 2, 'ct_run_transcription', array($chapter_id));
        wp_schedule_single_event(time() + 5, 'ct_run_transcription', array($chapter_id));
        
        if (!defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON) {
            spawn_cron();
        }
        
        // Method 3: cURL fallback
        $this->trigger_background_transcription($chapter_id, $nonce);
        
        error_log('Bulk transcribe: Started transcription for chapter ' . $chapter_id);
        
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
        
        // For local dev - skip duplicate check since multiple calls are expected
        // Just check if it's been stuck for a very long time (>10 minutes) to avoid infinite loops
        $existing_status = get_transient('ct_transcription_' . $chapter_id);
        if ($existing_status && isset($existing_status['status']) && $existing_status['status'] === 'processing') {
            $elapsed = isset($existing_status['started_at']) ? (time() - $existing_status['started_at']) : 0;
            // Only skip if it's been running for more than 10 minutes (likely stuck or actually processing)
            if ($elapsed > 600) {
                error_log('Transcription: Chapter ' . $chapter_id . ' has been processing for ' . $elapsed . ' seconds, skipping duplicate run');
                return;
            }
            // If it's been less than 10 minutes, allow it to run (might be a retry or the previous one failed)
            error_log('Transcription: Chapter ' . $chapter_id . ' status exists but allowing run (elapsed: ' . $elapsed . 's)');
        }
        
        // Update status
        $this->update_transcription_status($chapter_id, 'processing', 'Starting transcription process...', $start_time);
        
        error_log('Transcription: run_transcription_cron called for chapter ' . $chapter_id . ' at ' . date('Y-m-d H:i:s'));
        
        // Run transcription with error handling
        try {
            error_log('Transcription: About to call transcribe_video for chapter ' . $chapter_id);
            $result = $this->transcribe_video($chapter_id, true);
            error_log('Transcription: transcribe_video returned for chapter ' . $chapter_id . '. Success: ' . ($result['success'] ? 'yes' : 'no'));
        } catch (Exception $e) {
            error_log('Transcription: Exception in transcribe_video for chapter ' . $chapter_id . ': ' . $e->getMessage());
            $result = array('success' => false, 'message' => 'Exception: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('Transcription: Fatal error in transcribe_video for chapter ' . $chapter_id . ': ' . $e->getMessage());
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
        
        // Also log to error log for debugging
        error_log(sprintf(
            'Transcription Status [Chapter %d]: %s - %s (Elapsed: %d seconds)',
            $chapter_id,
            strtoupper($status),
            $message,
            isset($status_data['elapsed_seconds']) ? $status_data['elapsed_seconds'] : 0
        ));
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
        
        error_log('Transcription: Converting MP4 to audio for chapter ' . $chapter_id);
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
        $transcription_result = $whisper_client->transcribe_file($file_to_transcribe, $chapter_id, 3, function($status, $message) use ($chapter_id, $async) {
            if ($async) {
                $this->update_transcription_status($chapter_id, 'processing', $message);
            }
        });
        
        // Clean up temporary audio file if we created one (always, even on error)
        // Store the path before we potentially return early
        $audio_file_to_delete = $temp_audio_file ? $file_to_transcribe : null;
        
        if (!$transcription_result['success']) {
            // Clean up before returning error
            if ($audio_file_to_delete && file_exists($audio_file_to_delete)) {
                @unlink($audio_file_to_delete);
                error_log('Transcription: Deleted temporary audio file after error: ' . basename($audio_file_to_delete));
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
        } else {
            error_log('Transcription: VTT file generated for chapter ' . $chapter_id . ' (' . $segment_count . ' segments)');
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
}

// Initialize the plugin
new Chapter_Transcription();

