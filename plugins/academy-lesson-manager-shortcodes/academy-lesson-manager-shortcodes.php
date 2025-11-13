<?php
/**
 * Plugin Name: Academy Lesson Manager Shortcodes
 * Plugin URI: https://jazzedge.com
 * Description: Shortcodes for Academy Lesson Manager - displays lesson videos and content
 * Version: 1.0.0
 * Author: JazzEdge
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ALM_SHORTCODES_VERSION', '1.0.1');
define('ALM_SHORTCODES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALM_SHORTCODES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once ALM_SHORTCODES_PLUGIN_DIR . 'includes/class-chapter-handler.php';

class ALM_Shortcodes_Plugin {
    
    /**
     * Ensure ALM_Admin_Settings class is loaded
     */
    private function ensure_alm_settings_loaded() {
        if (!class_exists('ALM_Admin_Settings')) {
            // Try to load from academy-lesson-manager plugin
            $alm_path = WP_PLUGIN_DIR . '/academy-lesson-manager/includes/class-admin-settings.php';
            if (file_exists($alm_path)) {
                require_once $alm_path;
            }
        }
        return class_exists('ALM_Admin_Settings');
    }
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        
        // Register AJAX handlers
        add_action('wp_ajax_alm_mark_chapter_complete', array($this, 'ajax_mark_chapter_complete'));
        add_action('wp_ajax_alm_mark_chapter_incomplete', array($this, 'ajax_mark_chapter_incomplete'));
        add_action('wp_ajax_alm_mark_lesson_complete', array($this, 'ajax_mark_lesson_complete'));
        add_action('wp_ajax_alm_mark_lesson_incomplete', array($this, 'ajax_mark_lesson_incomplete'));
        add_action('wp_ajax_alm_save_lesson_notes', array($this, 'ajax_save_lesson_notes'));
        add_action('wp_ajax_alm_toggle_resource_favorite', array($this, 'ajax_toggle_resource_favorite'));
        add_action('wp_ajax_alm_create_note', array($this, 'ajax_create_note'));
        add_action('wp_ajax_alm_update_note', array($this, 'ajax_update_note'));
        add_action('wp_ajax_alm_delete_note', array($this, 'ajax_delete_note'));
        add_action('wp_ajax_alm_get_lessons_list', array($this, 'ajax_get_lessons_list'));
        
        // Create ALM notes table on activation
        add_action('init', array($this, 'create_alm_notes_table'));
    }
    
    /**
     * Create ALM Notes Table
     */
    public function create_alm_notes_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'alm_user_notes';
        
        // Check if table already exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id int(11) NOT NULL AUTO_INCREMENT,
                user_id int(11) NOT NULL,
                post_id int(11) NULL,
                lesson_id int(11) NULL,
                notes_content longtext,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY post_id (post_id),
                KEY lesson_id (lesson_id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            
            // Migrate existing notes from academy_user_notes
            $migration_result = $wpdb->query("
                INSERT INTO $table_name (user_id, post_id, lesson_id, notes_content, created_at, updated_at)
                SELECT user_id, post_id, lesson_id, user_notes, NOW(), NOW()
                FROM academy_user_notes 
                WHERE user_notes IS NOT NULL 
                  AND TRIM(user_notes) != ''
            ");
            
            // Add admin notice
            add_action('admin_notices', function() use ($migration_result) {
                $count = $migration_result ? $migration_result : 0;
                echo '<div class="notice notice-success is-dismissible"><p>ALM Notes table created and migrated ' . $count . ' notes successfully!</p></div>';
            });
        }
    }
    
    /**
     * Get the required membership level for a lesson
     */
    private function get_lesson_required_level($lesson_id) {
        global $wpdb;
        
        // Get lesson membership level from ALM lessons table
        $membership_level = $wpdb->get_var($wpdb->prepare(
            "SELECT membership_level FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
            $lesson_id
        ));
        
        // Return the membership level directly, default to Studio (2) if not set
        return !empty($membership_level) ? intval($membership_level) : 2;
    }
    
    /**
     * Get VTT subtitle URL for a chapter
     * 
     * @param int $chapter_id Chapter ID
     * @return string|false VTT file URL or false if not found
     */
    private function get_chapter_subtitle_url($chapter_id) {
        global $wpdb;
        
        if (empty($chapter_id)) {
            return false;
        }
        
        // Get VTT filename from transcripts table
        $vtt_file = $wpdb->get_var($wpdb->prepare(
            "SELECT vtt_file FROM {$wpdb->prefix}alm_transcripts WHERE chapter_id = %d AND source = 'whisper' LIMIT 1",
            $chapter_id
        ));
        
        if (empty($vtt_file)) {
            return false;
        }
        
        // Construct full URL to VTT file
        // VTT files are stored in /wp-content/alm_transcripts/ folder
        // Try wp-content first (as specified by user), then fallback to WordPress root
        $vtt_path_wpcontent = WP_CONTENT_DIR . '/alm_transcripts/' . $vtt_file;
        $vtt_path_root = ABSPATH . 'alm_transcripts/' . $vtt_file;
        
        // Check which location has the file
        if (file_exists($vtt_path_wpcontent)) {
            $vtt_url = content_url('alm_transcripts/' . $vtt_file);
        } elseif (file_exists($vtt_path_root)) {
            // Fallback: files in WordPress root (ABSPATH/alm_transcripts/)
            // Construct URL relative to site root
            $vtt_url = site_url('alm_transcripts/' . $vtt_file);
        } else {
            // File doesn't exist, but we'll still return the URL in case it's accessible
            // Default to wp-content location
            $vtt_url = content_url('alm_transcripts/' . $vtt_file);
        }
        
        return $vtt_url;
    }
    
    /**
     * Get user's membership level
     * This is a placeholder - you'll need to implement this based on your membership system
     */
    private function get_user_membership_level($user_id) {
        // TODO: Implement based on your membership system
        // For now, return a default level - you should replace this with actual logic
        // Examples:
        // - Check user meta: get_user_meta($user_id, 'membership_level', true)
        // - Check custom table: SELECT membership_level FROM user_memberships WHERE user_id = $user_id
        // - Check plugin data: if using a membership plugin
        
        // Default to free level (0) - this should be replaced with actual implementation
        return 0;
    }
    
    /**
     * Get membership level name
     */
    private function get_membership_level_name($level) {
        $levels = array(
            0 => 'Free',
            1 => 'Essentials', 
            2 => 'Studio',
            3 => 'Premier'
        );
        
        return isset($levels[$level]) ? $levels[$level] : 'Free';
    }
    
    /**
     * Format duration in seconds to human-readable format (e.g., "1hr 20min")
     * 
     * @param int $seconds Duration in seconds
     * @return string Human-readable duration
     */
    private function format_duration_human_readable($seconds) {
        if (empty($seconds) || $seconds <= 0) {
            return '0min';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        $parts = array();
        
        if ($hours > 0) {
            $parts[] = $hours . 'hr';
        }
        
        if ($minutes > 0) {
            $parts[] = $minutes . 'min';
        }
        
        // If less than a minute, show as minutes anyway
        if (empty($parts)) {
            return '0min';
        }
        
        return implode(' ', $parts);
    }
    
    public function init() {
        // Register shortcodes
        add_shortcode('alm_test', array($this, 'test_shortcode'));
        add_shortcode('alm_lesson_video', array($this, 'lesson_video_shortcode'));
        add_shortcode('alm_lesson_chapters', array($this, 'lesson_chapters_shortcode'));
        add_shortcode('alm_lesson_complete', array($this, 'lesson_complete_shortcode'));
        add_shortcode('alm_lesson_resources', array($this, 'lesson_resources_shortcode'));
        add_shortcode('alm_lesson_progress', array($this, 'lesson_progress_shortcode'));
        add_shortcode('alm_mark_complete', array($this, 'mark_complete_shortcode'));
        add_shortcode('alm_collection_complete', array($this, 'collection_complete_shortcode'));
        add_shortcode('alm_collections_dropdown', array($this, 'collections_dropdown_shortcode'));
        add_shortcode('alm_collections_page', array($this, 'collections_page_shortcode'));
        add_shortcode('alm_favorites_management', array($this, 'favorites_management_shortcode'));
        add_shortcode('alm_user_notes_manager', array($this, 'user_notes_manager_shortcode'));
        add_shortcode('alm_membership_list', array($this, 'membership_list_shortcode'));
        
        // Add debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ALM Shortcodes: Plugin initialized and shortcodes registered');
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'ALM Shortcodes',
            'ALM Shortcodes',
            'manage_options',
            'alm-shortcodes',
            array($this, 'render_admin_page'),
            'dashicons-shortcode',
            30
        );
    }
    
    public function enqueue_frontend_styles() {
        // Only load on frontend
        if (!is_admin()) {
            wp_enqueue_style(
                'alm-shortcodes-frontend',
                ALM_SHORTCODES_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                ALM_SHORTCODES_VERSION
            );
        }
    }
    
    public function enqueue_frontend_scripts() {
        // Only load on frontend
        if (!is_admin()) {
            // Enqueue jQuery UI for drag and drop
            wp_enqueue_script('jquery-ui-sortable');
            
            wp_enqueue_script(
                'alm-shortcodes-frontend',
                ALM_SHORTCODES_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery', 'jquery-ui-sortable'),
                ALM_SHORTCODES_VERSION,
                true
            );
            
            // Localize script with AJAX URL and nonce
            wp_localize_script('alm-shortcodes-frontend', 'almAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('alm_completion_nonce')
            ));
        }
    }
    
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>ALM Shortcodes</h1>
            
            <div class="alm-shortcodes-grid">
                <div class="alm-shortcode-card">
                    <h3>Test Shortcode</h3>
                    <p>Simple test to verify shortcodes are working</p>
                    <div class="shortcode-example">
                        <code>[alm_test]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[alm_test]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Lesson Video</h3>
                    <p>Display lesson video with membership restrictions</p>
                    <div class="shortcode-example">
                        <code>[alm_lesson_video lesson_id="123" chapter_id="456" user_membership_level="2"]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[alm_lesson_video lesson_id="123" chapter_id="456" user_membership_level="2"]'>Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Lesson Chapters</h3>
                    <p>Display lesson chapters list with navigation</p>
                    <div class="shortcode-example">
                        <code>[alm_lesson_chapters lesson_id="123" format="well"]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[alm_lesson_chapters lesson_id="123" format="well"]'>Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Complete Lesson Experience</h3>
                    <p>Title + Video + Progress + Chapters all in one</p>
                    <div class="shortcode-example">
                        <code>[alm_lesson_complete lesson_id="123" user_membership_level="2"]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[alm_lesson_complete lesson_id="123" user_membership_level="2"]'>Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Lesson Resources</h3>
                    <p>Sheet music, backing tracks, MIDI files, and more</p>
                    <div class="shortcode-example">
                        <code>[alm_lesson_resources lesson_id="123" user_membership_level="2"]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[alm_lesson_resources lesson_id="123" user_membership_level="2"]'>Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Lesson Progress</h3>
                    <p>Display lesson completion percentage</p>
                    <div class="shortcode-example">
                        <code>[alm_lesson_progress lesson_id="123" format="percent"]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[alm_lesson_progress lesson_id="123" format="percent"]'>Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Mark Complete</h3>
                    <p>Button to mark chapter or lesson complete</p>
                    <div class="shortcode-example">
                        <code>[alm_mark_complete lesson_id="123" chapter_id="456" type="chapter"]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[alm_mark_complete lesson_id="123" chapter_id="456" type="chapter"]'>Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Collections Dropdown</h3>
                    <p>Dropdown list of all lesson collections grouped by membership level</p>
                    <div class="shortcode-example">
                        <code>[alm_collections_dropdown placeholder="Select a collection..."]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[alm_collections_dropdown placeholder="Select a collection..."]'>Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Favorites Management</h3>
                    <p>View and manage your favorite lessons and resources</p>
                    <div class="shortcode-example">
                        <code>[alm_favorites_management]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[alm_favorites_management]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>User Notes Manager</h3>
                    <p>CRUD interface for creating, editing, and deleting user notes</p>
                    <div class="shortcode-example">
                        <code>[alm_user_notes_manager]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[alm_user_notes_manager]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Membership List</h3>
                    <p>Display active subscriptions and memberships</p>
                    <div class="shortcode-example">
                        <code>[alm_membership_list]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[alm_membership_list]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Collections Page</h3>
                    <p>Display membership levels with collections and lesson hours</p>
                    <div class="shortcode-example">
                        <code>[alm_collections_page]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[alm_collections_page]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Lesson Search</h3>
                    <p>Full-featured lesson search page with filters</p>
                    <div class="shortcode-example">
                        <code>[alm_lesson_search]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[alm_lesson_search]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Lesson Search Compact</h3>
                    <p>Compact lesson search widget</p>
                    <div class="shortcode-example">
                        <code>[alm_lesson_search_compact]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[alm_lesson_search_compact]">Copy</button>
                    </div>
                </div>
            </div>
            
            <h2 style="margin-top: 40px;">Practice Hub Shortcodes</h2>
            
            <div class="alm-shortcodes-grid">
                <div class="alm-shortcode-card">
                    <h3>Leaderboard</h3>
                    <p>Display top practice session leaderboard</p>
                    <div class="shortcode-example">
                        <code>[jph_leaderboard]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[jph_leaderboard]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Leaderboard Widget</h3>
                    <p>Compact leaderboard widget</p>
                    <div class="shortcode-example">
                        <code>[jph_leaderboard_widget]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[jph_leaderboard_widget]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Dashboard</h3>
                    <p>Full practice hub dashboard</p>
                    <div class="shortcode-example">
                        <code>[jph_dashboard]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[jph_dashboard]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Stats Widget</h3>
                    <p>Display user practice statistics</p>
                    <div class="shortcode-example">
                        <code>[jph_stats_widget]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[jph_stats_widget]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Recent Practice Widget</h3>
                    <p>Show recent practice sessions</p>
                    <div class="shortcode-example">
                        <code>[jph_recent_practice_widget]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[jph_recent_practice_widget]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Practice Items Widget</h3>
                    <p>Display practice items list</p>
                    <div class="shortcode-example">
                        <code>[jph_practice_items_widget]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[jph_practice_items_widget]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Progress Chart Widget</h3>
                    <p>Visual progress chart display</p>
                    <div class="shortcode-example">
                        <code>[jph_progress_chart_widget]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[jph_progress_chart_widget]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Badges Widget</h3>
                    <p>Display user badges and achievements</p>
                    <div class="shortcode-example">
                        <code>[jph_badges_widget]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[jph_badges_widget]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Gems Widget</h3>
                    <p>Display user gems/points</p>
                    <div class="shortcode-example">
                        <code>[jph_gems_widget]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[jph_gems_widget]">Copy</button>
                    </div>
                </div>
                
                <div class="alm-shortcode-card">
                    <h3>Streak Widget</h3>
                    <p>Display practice streak information</p>
                    <div class="shortcode-example">
                        <code>[jph_streak_widget]</code>
                        <button class="button button-small copy-shortcode" data-shortcode="[jph_streak_widget]">Copy</button>
                    </div>
                </div>
            </div>
            
            <h2>Shortcode Details</h2>
            
            <h3>alm_test</h3>
            <p>Simple test shortcode that outputs "ALM Shortcodes are working!"</p>
            
            <h3>alm_lesson_video</h3>
            <p>Displays lesson video with membership level restrictions and smart chapter detection</p>
            <h4>Parameters:</h4>
            <ul>
                <li><strong>lesson_id</strong> (required): The lesson ID from ALM database</li>
                <li><strong>chapter_id</strong> (optional): Specific chapter ID to display</li>
                <li><strong>user_membership_level</strong> (required): User's membership level (0=Free, 1=Essentials, 2=Studio, 3=Premier)</li>
            </ul>
            
            <h4>Smart Chapter Detection:</h4>
            <p>The shortcode automatically detects the current chapter using:</p>
            <ul>
                <li><strong>URL Parameter:</strong> <code>?c=chapter-slug</code> - Uses chapter slug from URL</li>
                <li><strong>Explicit chapter_id:</strong> If provided in shortcode parameters</li>
                <li><strong>Default:</strong> First chapter of the lesson</li>
            </ul>
            
            <h4>Example Usage:</h4>
            <pre><code>[alm_lesson_video lesson_id="123" user_membership_level="2"]</code></pre>
            <pre><code>[alm_lesson_video lesson_id="123" chapter_id="456" user_membership_level="1"]</code></pre>
            <pre><code>[alm_lesson_video lesson_id="123" user_membership_level="2"]</code> <!-- With ?c=chapter-slug in URL --></pre>
            
            <h3>alm_lesson_chapters</h3>
            <p>Displays a list of lesson chapters with navigation links and completion status</p>
            <h4>Parameters:</h4>
            <ul>
                <li><strong>lesson_id</strong> (required): The lesson ID from ALM database</li>
                <li><strong>format</strong> (optional): Display format - "well" (default) or "list"</li>
            </ul>
            
            <h4>Format Options:</h4>
            <ul>
                <li><strong>well</strong>: Styled list with chapter numbers, titles, durations, and active state highlighting</li>
                <li><strong>list</strong>: Simple unordered list with chapter links</li>
            </ul>
            
            <h4>Features:</h4>
            <ul>
                <li><strong>Active Chapter Highlighting</strong>: Highlights current chapter based on URL parameter</li>
                <li><strong>Chapter Navigation</strong>: Clickable links to jump between chapters</li>
                <li><strong>Duration Display</strong>: Shows individual and total lesson duration</li>
                <li><strong>Completion Status</strong>: Visual indicators for completed chapters</li>
            </ul>
            
            <h4>Example Usage:</h4>
            <pre><code>[alm_lesson_chapters lesson_id="123"]</code></pre>
            <pre><code>[alm_lesson_chapters lesson_id="123" format="well"]</code></pre>
            <pre><code>[alm_lesson_chapters lesson_id="123" format="list"]</code></pre>
            
            <h3>alm_lesson_complete</h3>
            <p>Complete lesson experience with title, video player, progress tracking, and chapter navigation all in one</p>
            <h4>Parameters:</h4>
            <ul>
                <li><strong>lesson_id</strong> (required): The lesson ID from ALM database</li>
                <li><strong>user_membership_level</strong> (required): User's membership level (0=Free, 1=Essentials, 2=Studio, 3=Premier)</li>
            </ul>
            
            <h4>Layout Order:</h4>
            <ol>
                <li><strong>Lesson Title</strong> - Prominent lesson name with chapter count and duration</li>
                <li><strong>Video Player</strong> - Current chapter video with splash screen</li>
                <li><strong>Progress Bar</strong> - Visual completion tracking</li>
                <li><strong>Chapter List</strong> - Interactive chapter navigation</li>
            </ol>
            
            <h4>Features:</h4>
            <ul>
                <li><strong>Smart Chapter Detection</strong> - Automatically shows current chapter based on URL</li>
                <li><strong>Membership Restrictions</strong> - Shows upgrade message if user level too low</li>
                <li><strong>Progress Tracking</strong> - Real completion progress based on your completion system</li>
                <li><strong>Responsive Design</strong> - Works perfectly on all devices</li>
                <li><strong>Brand Colors</strong> - Uses your JazzEdge color palette</li>
            </ul>
            
            <h4>Example Usage:</h4>
            <pre><code>[alm_lesson_complete lesson_id="123" user_membership_level="2"]</code></pre>
            <pre><code>[alm_lesson_complete lesson_id="123" user_membership_level="1"]</code></pre>
            
            <h3>alm_lesson_resources</h3>
            <p>Displays lesson resources including sheet music, backing tracks, MIDI files, and more</p>
            <h4>Parameters:</h4>
            <ul>
                <li><strong>lesson_id</strong> (required): The lesson ID from ALM database</li>
                <li><strong>user_membership_level</strong> (required): User's membership level (0=Free, 1=Essentials, 2=Studio, 3=Premier)</li>
            </ul>
            
            <h4>Resource Types:</h4>
            <ul>
                <li><strong>Sheet Music</strong> (PDF) - Music notation and tabs</li>
                <li><strong>Backing Track</strong> (JAM) - Play-along audio</li>
                <li><strong>MIDI Files</strong> (MID) - MIDI sequences</li>
                <li><strong>iRealPro</strong> (IRE) - iRealPro format files</li>
                <li><strong>Lesson Audio</strong> (MP3) - Audio-only versions</li>
                <li><strong>Call & Response</strong> (CAL) - Interactive exercises</li>
                <li><strong>Notes</strong> - Additional lesson notes</li>
            </ul>
            
            <h4>Features:</h4>
            <ul>
                <li><strong>Membership Restrictions</strong> - Shows upgrade message if user level too low</li>
                <li><strong>Resource Icons</strong> - Visual icons for each resource type</li>
                <li><strong>Secure Links</strong> - Resources accessed through secure link system</li>
                <li><strong>Favorites System</strong> - Users can favorite/unfavorite resources</li>
                <li><strong>Access Control</strong> - Integrates with existing access control functions</li>
            </ul>
            
            <h4>Example Usage:</h4>
            <pre><code>[alm_lesson_resources lesson_id="123" user_membership_level="2"]</code></pre>
            <pre><code>[alm_lesson_resources lesson_id="123" user_membership_level="1"]</code></pre>
            
            <h3>alm_lesson_progress</h3>
            <p>Displays lesson completion progress as a percentage or formatted text</p>
            <h4>Parameters:</h4>
            <ul>
                <li><strong>lesson_id</strong> (required): The lesson ID from ALM database</li>
                <li><strong>format</strong> (optional): Display format - "percent" (default) or "full"</li>
            </ul>
            
            <h4>Format Options:</h4>
            <ul>
                <li><strong>percent</strong>: Returns just the percentage (e.g., "75")</li>
                <li><strong>full</strong>: Returns formatted text (e.g., "You've Completed: 3 / 4 chapters")</li>
            </ul>
            
            <h4>Integration:</h4>
            <p>Uses your existing <code>je_return_lesson_progress_percentage</code> function if available, otherwise calculates progress from the <code>wp_alm_chapters</code> table and <code>academy_completed_chapters</code> table.</p>
            
            <h4>Example Usage:</h4>
            <pre><code>[alm_lesson_progress lesson_id="123"]</code></pre>
            <pre><code>[alm_lesson_progress lesson_id="123" format="percent"]</code></pre>
            <pre><code>[alm_lesson_progress lesson_id="123" format="full"]</code></pre>
            
            <h3>alm_mark_complete</h3>
            <p>Button to mark chapters or lessons as complete/incomplete</p>
            <h4>Parameters:</h4>
            <ul>
                <li><strong>lesson_id</strong> (required): The lesson ID from ALM database</li>
                <li><strong>chapter_id</strong> (optional): Chapter ID for chapter completion</li>
                <li><strong>type</strong> (optional): Type of completion - "chapter" (default) or "lesson"</li>
            </ul>
            
            <h4>Features:</h4>
            <ul>
                <li><strong>Smart Button Text</strong>: Shows "Mark Complete" or "Restart" based on current status</li>
                <li><strong>Auto-Lesson Completion</strong>: If lesson has only 1 chapter, automatically marks lesson complete too</li>
                <li><strong>Restart Functionality</strong>: Can restart chapters or lessons (marks all chapters incomplete for lesson restart)</li>
                <li><strong>Success Messages</strong>: Shows confirmation message after completion without page redirect</li>
                <li><strong>Soft Delete</strong>: Uses <code>deleted_at</code> column for soft deletes</li>
            </ul>
            
            <h4>Chapter Completion:</h4>
            <p>For marking individual chapters complete:</p>
            <pre><code>[alm_mark_complete lesson_id="123" chapter_id="456" type="chapter"]</code></pre>
            
            <h4>Lesson Completion:</h4>
            <p>For marking entire lessons complete:</p>
            <pre><code>[alm_mark_complete lesson_id="123" type="lesson"]</code></pre>
            
            <h4>Database Integration:</h4>
            <p>Integrates with your existing tables:</p>
            <ul>
                <li><code>academy_completed_chapters</code> - For chapter completion</li>
                <li><code>academy_completed_lessons</code> - For lesson completion</li>
                <li>Uses <code>deleted_at</code> column for soft deletes (restart functionality)</li>
            </ul>
            
            <h3>alm_collections_dropdown</h3>
            <p>Dropdown list of all lesson collections grouped by membership level</p>
            <h4>Parameters:</h4>
            <ul>
                <li><strong>placeholder</strong> (optional): Placeholder text for the dropdown (default: "Select a collection...")</li>
                <li><strong>class</strong> (optional): Additional CSS class for the dropdown</li>
                <li><strong>style</strong> (optional): Inline CSS styles</li>
            </ul>
            <h4>Features:</h4>
            <ul>
                <li>Groups collections by membership level</li>
                <li>Redirects to collection page on selection</li>
            </ul>
            <h4>Example Usage:</h4>
            <pre><code>[alm_collections_dropdown placeholder="Select a collection..."]</code></pre>
            
            <h3>alm_favorites_management</h3>
            <p>View and manage your favorite lessons and resources</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Features:</h4>
            <ul>
                <li>Displays all user favorites from <code>wp_jph_lesson_favorites</code> table</li>
                <li>Shows lesson favorites and resource favorites with distinct styling</li>
                <li>Delete functionality with confirmation</li>
                <li>Shows date added for each favorite</li>
                <li>Links to lesson or resource URLs</li>
                <li>Only visible to logged-in users</li>
            </ul>
            <h4>Database:</h4>
            <p>Reads from <code>wp_jph_lesson_favorites</code> table, dynamically detects table structure</p>
            <h4>Example Usage:</h4>
            <pre><code>[alm_favorites_management]</code></pre>
            
            <h3>alm_user_notes_manager</h3>
            <p>Full CRUD (Create, Read, Update, Delete) interface for managing user notes</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Features:</h4>
            <ul>
                <li><strong>Create Notes:</strong> "New Note" button opens form with optional lesson association</li>
                <li><strong>View Notes:</strong> Displays all user notes sorted by most recent update</li>
                <li><strong>Edit Notes:</strong> Click "Edit" button to modify note content and lesson association</li>
                <li><strong>Delete Notes:</strong> Delete button with confirmation, immediate UI feedback</li>
                <li><strong>Lesson Association:</strong> Optionally link notes to specific lessons</li>
                <li><strong>Note Previews:</strong> Shows note content preview with date/time</li>
                <li><strong>Empty State:</strong> Displays helpful message when no notes exist</li>
                <li>Only visible to logged-in users</li>
            </ul>
            <h4>Database:</h4>
            <p>Stores notes in <code>wp_alm_user_notes</code> table with fields:</p>
            <ul>
                <li><code>user_id</code> - User who owns the note</li>
                <li><code>lesson_id</code> - Optional lesson association</li>
                <li><code>post_id</code> - Optional post association</li>
                <li><code>notes_content</code> - Note content (supports HTML)</li>
                <li><code>created_at</code> - Creation timestamp</li>
                <li><code>updated_at</code> - Last update timestamp</li>
            </ul>
            <h4>Example Usage:</h4>
            <pre><code>[alm_user_notes_manager]</code></pre>
            
            <h3>alm_collections_page</h3>
            <p>Displays membership levels (Essentials, Studio, Premier) with collection counts and total lesson hours</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Features:</h4>
            <ul>
                <li>Shows three membership tiers with collection counts and total hours</li>
                <li>Premier level shows "Access to All Collections" badge</li>
                <li>Collections dropdown for each membership level</li>
                <li>Responsive card layout</li>
            </ul>
            <h4>Example Usage:</h4>
            <pre><code>[alm_collections_page]</code></pre>
            
            <h3>alm_lesson_search</h3>
            <p>Full-featured lesson search page with advanced filtering options</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Features:</h4>
            <ul>
                <li>Search by lesson title, description, collection, chapters, and transcripts</li>
                <li>Filter by skill level (Beginner, Intermediate, Advanced, Pro)</li>
                <li>Filter by tags, lesson style, and membership level</li>
                <li>Pagination support</li>
                <li>Favorite lessons functionality</li>
                <li>Responsive card-based layout</li>
            </ul>
            <h4>Example Usage:</h4>
            <pre><code>[alm_lesson_search]</code></pre>
            
            <h3>alm_lesson_search_compact</h3>
            <p>Compact lesson search widget with minimal interface</p>
            <h4>Parameters:</h4>
            <ul>
                <li><strong>view_all_url</strong> (optional): URL to full search page</li>
                <li><strong>placeholder</strong> (optional): Placeholder text for search input</li>
                <li><strong>max_items</strong> (optional): Maximum results to display (default: 10)</li>
            </ul>
            <h4>Example Usage:</h4>
            <pre><code>[alm_lesson_search_compact]</code></pre>
            <pre><code>[alm_lesson_search_compact view_all_url="/search" placeholder="Search lessons..." max_items="5"]</code></pre>
            
            <h2 style="margin-top: 40px;">Practice Hub Shortcode Details</h2>
            
            <h3>jph_leaderboard</h3>
            <p>Displays the top practice session leaderboard with user rankings</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Features:</h4>
            <ul>
                <li>Shows top users by practice time</li>
                <li>Displays user display names and practice statistics</li>
                <li>Opens in new tab when clicked</li>
            </ul>
            <h4>Example Usage:</h4>
            <pre><code>[jph_leaderboard]</code></pre>
            
            <h3>jph_leaderboard_widget</h3>
            <p>Compact leaderboard widget for sidebars or smaller spaces</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Example Usage:</h4>
            <pre><code>[jph_leaderboard_widget]</code></pre>
            
            <h3>jph_dashboard</h3>
            <p>Full practice hub dashboard with all user statistics and widgets</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Example Usage:</h4>
            <pre><code>[jph_dashboard]</code></pre>
            
            <h3>jph_stats_widget</h3>
            <p>Display user practice statistics in a widget format</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Example Usage:</h4>
            <pre><code>[jph_stats_widget]</code></pre>
            
            <h3>jph_recent_practice_widget</h3>
            <p>Shows recent practice sessions for the current user</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Example Usage:</h4>
            <pre><code>[jph_recent_practice_widget]</code></pre>
            
            <h3>jph_practice_items_widget</h3>
            <p>Displays a list of practice items</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Example Usage:</h4>
            <pre><code>[jph_practice_items_widget]</code></pre>
            
            <h3>jph_progress_chart_widget</h3>
            <p>Visual progress chart displaying practice progress over time</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Example Usage:</h4>
            <pre><code>[jph_progress_chart_widget]</code></pre>
            
            <h3>jph_badges_widget</h3>
            <p>Display user badges and achievements</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Example Usage:</h4>
            <pre><code>[jph_badges_widget]</code></pre>
            
            <h3>jph_gems_widget</h3>
            <p>Display user gems/points balance</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Example Usage:</h4>
            <pre><code>[jph_gems_widget]</code></pre>
            
            <h3>jph_streak_widget</h3>
            <p>Display practice streak information (consecutive days practiced)</p>
            <h4>Parameters:</h4>
            <p>No parameters required</p>
            <h4>Example Usage:</h4>
            <pre><code>[jph_streak_widget]</code></pre>
        </div>
        
        <style>
        .alm-shortcodes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .alm-shortcode-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .alm-shortcode-card h3 {
            margin-top: 0;
            color: #23282d;
        }
        
        .shortcode-example {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .shortcode-example code {
            background: #f1f1f1;
            padding: 8px;
            border-radius: 3px;
            flex: 1;
            font-size: 12px;
        }
        
        .copy-shortcode {
            white-space: nowrap;
        }
        
        /* ALM Chapter List Styles */
        .alm-chapter-list {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .alm-chapter-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        
        .alm-lesson-title {
            margin: 0 0 12px 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .alm-lesson-meta {
            display: flex;
            justify-content: center;
            gap: 24px;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .alm-progress-container {
            padding: 20px 24px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .alm-progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .alm-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .alm-progress-text {
            font-size: 14px;
            color: #6c757d;
            text-align: center;
        }
        
        .alm-chapters-container {
            padding: 0;
        }
        
        .alm-chapter-item {
            border-bottom: 1px solid #f1f3f4;
            transition: all 0.2s ease;
        }
        
        .alm-chapter-item:last-child {
            border-bottom: none;
        }
        
        .alm-chapter-item:hover {
            background: #f8f9fa;
        }
        
        .alm-chapter-item.active {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        
        .alm-chapter-item.completed {
            background: #f1f8e9;
        }
        
        .alm-chapter-link {
            display: flex;
            align-items: center;
            padding: 20px 24px;
            text-decoration: none;
            color: inherit;
        }
        
        .alm-chapter-number {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            flex-shrink: 0;
        }
        
        .alm-status-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }
        
        .alm-status-icon.completed {
            background: #28a745;
            color: white;
        }
        
        .alm-status-icon.current {
            background: #2196f3;
            color: white;
        }
        
        .alm-status-icon.pending {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .alm-chapter-content {
            flex: 1;
            min-width: 0;
        }
        
        .alm-chapter-title {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
            color: #212529;
            line-height: 1.4;
        }
        
        .alm-chapter-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }
        
        .alm-duration {
            color: #6c757d;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .alm-current-badge {
            background: #2196f3;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .alm-completed-badge {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .alm-chapter-action {
            margin-left: 16px;
            flex-shrink: 0;
        }
        
        .alm-play-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 16px;
            transition: all 0.2s ease;
        }
        
        .alm-chapter-item:hover .alm-play-icon {
            background: #2196f3;
            color: white;
            transform: scale(1.1);
        }
        
        .alm-chapter-item.active .alm-play-icon {
            background: #2196f3;
            color: white;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .alm-lesson-meta {
                flex-direction: column;
                gap: 8px;
            }
            
            .alm-chapter-link {
                padding: 16px;
            }
            
            .alm-chapter-number {
                width: 40px;
                height: 40px;
                margin-right: 12px;
            }
            
            .alm-status-icon {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
            
            .alm-chapter-title {
                font-size: 15px;
            }
            
            .alm-chapter-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }
        }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copyButtons = document.querySelectorAll('.copy-shortcode');
            copyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const shortcode = this.getAttribute('data-shortcode');
                    navigator.clipboard.writeText(shortcode).then(() => {
                        this.textContent = 'Copied!';
                        setTimeout(() => {
                            this.textContent = 'Copy';
                        }, 2000);
                    });
                });
            });
        });
        </script>
        <?php
    }
    
    public function test_shortcode($atts) {
        return '<p style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; border: 1px solid #c3e6cb;">✅ ALM Shortcodes are working!</p>';
    }
    
    public function lesson_video_shortcode($atts) {
        $atts = shortcode_atts(array(
            'lesson_id' => '',
            'chapter_id' => '',
            'user_membership_level' => '0'
        ), $atts);
        
        // Auto-detect lesson_id from current post if not provided
        if (empty($atts['lesson_id'])) {
            $post_id = get_the_ID();
            if ($post_id) {
                // Try ACF field first
                if (function_exists('get_field')) {
                    $lesson_id = get_field('alm_lesson_id', $post_id);
                }
                
                // Fallback to post meta
                if (empty($lesson_id)) {
                    $lesson_id = get_post_meta($post_id, 'alm_lesson_id', true);
                }
                
                if (!empty($lesson_id)) {
                    $atts['lesson_id'] = $lesson_id;
                }
            }
        }
        
        if (empty($atts['lesson_id'])) {
            return '<p style="color: red;">Error: lesson_id is required</p>';
        }
        
        global $wpdb;
        
        // Get lesson data
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
            $atts['lesson_id']
        ));
        
        if (!$lesson) {
            return '<p style="color: red;">Error: Lesson not found</p>';
        }
        
        // Check membership level - CRITICAL SECURITY CHECK
        $user_level = intval($atts['user_membership_level']);
        $lesson_level = intval($lesson->membership_level);
        
        $has_access = $user_level >= $lesson_level;
        
        // Check Essentials library access for Studio-level lessons
        if (!$has_access && $user_level == 1 && $lesson_level == 2) {
            global $user_id;
            if ($user_id && class_exists('ALM_Essentials_Library')) {
                $library = new ALM_Essentials_Library();
                if ($library->has_lesson_in_library($user_id, intval($atts['lesson_id']))) {
                    $has_access = true;
                }
            }
        }
        
        if (!$has_access) {
            return '<p style="color: red;">Access denied: Insufficient membership level</p>';
        }
        
        // Initialize chapter handler
        $chapter_handler = new ALM_Chapter_Handler();
        
        // Get chapter data - support both explicit chapter_id and URL parameter 'c'
        $chapter_slug = isset($_GET['c']) ? sanitize_text_field($_GET['c']) : null;
        $chapter_data = $chapter_handler->get_chapter_data($atts['lesson_id'], $chapter_slug);
        
        if (!$chapter_data['success']) {
            return '<p style="color: red;">Error: ' . $chapter_data['error'] . '</p>';
        }
        
        // Use explicit chapter_id if provided, otherwise use resolved chapter_id
        $final_chapter_id = !empty($atts['chapter_id']) ? $atts['chapter_id'] : $chapter_data['chapter_id'];
        
        // Get chapter data
        $chapter = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_chapters WHERE ID = %d",
            $final_chapter_id
        ));
        
        if (!$chapter) {
            return '<p style="color: red;">Error: Chapter not found</p>';
        }
        
        // Get video URL with priority: Bunny > YouTube > Vimeo
        $video_url = '';
        if (!empty($chapter->bunny_url)) {
            $video_url = $chapter->bunny_url;
        } elseif (!empty($chapter->youtube_id)) {
            $video_url = 'https://www.youtube.com/watch?v=' . $chapter->youtube_id;
        } elseif (!empty($chapter->vimeo_id)) {
            $video_url = 'https://vimeo.com/' . $chapter->vimeo_id;
        }
        
        if (empty($video_url)) {
            return '<p style="color: red;">Error: No video URL found for this chapter</p>';
        }
        
        // Get VTT subtitle URL if available
        $subtitle_url = $this->get_chapter_subtitle_url($final_chapter_id);
        
        // Build FV Player shortcode
        $shortcode = '[fvplayer src="' . esc_url($video_url) . '" width="100%" height="400" splash="https://jazzedge.academy/wp-content/uploads/2023/12/splash-play-video.jpg"';
        
        // Add subtitles if VTT file exists
        if ($subtitle_url) {
            $shortcode .= ' subtitles="' . esc_url($subtitle_url) . '"';
        }
        
        $shortcode .= ']';
        
        // Use fvplayer shortcode with splash screen and subtitles
        return do_shortcode($shortcode);
    }
    
    public function lesson_chapters_shortcode($atts) {
        $atts = shortcode_atts(array(
            'lesson_id' => '',
            'format' => 'well'
        ), $atts);
        
        // Auto-detect lesson_id from current post if not provided
        if (empty($atts['lesson_id'])) {
            $post_id = get_the_ID();
            if ($post_id) {
                // Try ACF field first
                if (function_exists('get_field')) {
                    $lesson_id = get_field('alm_lesson_id', $post_id);
                }
                
                // Fallback to post meta
                if (empty($lesson_id)) {
                    $lesson_id = get_post_meta($post_id, 'alm_lesson_id', true);
                }
                
                if (!empty($lesson_id)) {
                    $atts['lesson_id'] = $lesson_id;
                }
            }
        }
        
        if (empty($atts['lesson_id'])) {
            return '<p style="color: red;">Error: lesson_id is required</p>';
        }
        
        global $wpdb;
        
        // Get lesson data
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
            $atts['lesson_id']
        ));
        
        if (!$lesson) {
            return '<p style="color: red;">Error: Lesson not found</p>';
        }
        
        // Get chapters using our chapter handler
        $chapter_handler = new ALM_Chapter_Handler();
        $chapters = $chapter_handler->get_lesson_chapters($atts['lesson_id']);
        
        if (empty($chapters)) {
            return '<div style="text-align: center; width:100%;">No chapters found.</div>';
        }
        
        // Get current chapter slug from URL
        $current_chapter_slug = isset($_GET['c']) ? sanitize_text_field($_GET['c']) : '';
        
        // Format time helper function
        $format_time = function($seconds) {
            if (!$seconds || $seconds <= 0) return '0:00';
            
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = $seconds % 60;
            
            if ($hours > 0) {
                return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
            } else {
                return sprintf('%d:%02d', $minutes, $secs);
            }
        };
        
        // Calculate total duration
        $total_duration = 0;
        foreach ($chapters as $chapter) {
            $total_duration += intval($chapter->duration);
        }
        
        // WELL FORMAT
        if ($atts['format'] == 'well') {
            $return = '<div class="alm-chapter-list">';
            
            // Header with lesson info
            $return .= '<div class="alm-chapter-header">';
            $return .= '<h3 class="alm-lesson-title">' . esc_html(stripslashes($lesson->lesson_title)) . '</h3>';
            $return .= '<div class="alm-lesson-meta">';
            $return .= '<span class="alm-chapter-count">' . count($chapters) . ' ' . (count($chapters) == 1 ? 'Chapter' : 'Chapters') . '</span>';
            $return .= '<span class="alm-total-duration">Total Duration: ' . $format_time($total_duration) . '</span>';
            $return .= '</div>';
            $return .= '</div>';
            
            // Progress bar - calculate based on actual completion
            $completed_chapters = 0;
            $total_chapters = count($chapters);
            
            foreach ($chapters as $chapter) {
                if (function_exists('je_is_chapter_complete') && je_is_chapter_complete($chapter->ID)) {
                    $completed_chapters++;
                }
            }
            
            $progress_percentage = $total_chapters > 0 ? ($completed_chapters / $total_chapters) * 100 : 0;
            
            $return .= '<div class="alm-progress-container">';
            $return .= '<div class="alm-progress-bar">';
            $return .= '<div class="alm-progress-fill" style="width: ' . $progress_percentage . '%"></div>';
            $return .= '</div>';
            $return .= '<div class="alm-progress-text">Progress: ' . $completed_chapters . ' of ' . $total_chapters . ' chapters completed</div>';
            $return .= '</div>';
            
            // Chapter list
            $return .= '<div class="alm-chapters-container">';
            
            foreach ($chapters as $index => $chapter) {
                $counter = $index + 1;
                $chapter_link = '?c=' . $chapter->slug;
                $is_active_chapter = ($chapter->slug == $current_chapter_slug) ? 'active' : '';
                $is_completed = function_exists('je_is_chapter_complete') ? je_is_chapter_complete($chapter->ID) : false;
                
                $return .= '<div class="alm-chapter-item ' . $is_active_chapter . ($is_completed ? ' completed' : '') . '">';
                $return .= '<a href="' . $chapter_link . '" class="alm-chapter-link">';
                
                // Chapter number with status icon
                $return .= '<div class="alm-chapter-number">';
                if ($is_completed) {
                    $return .= '<span class="alm-status-icon completed">✓</span>';
                } elseif ($is_active_chapter) {
                    $return .= '<span class="alm-status-icon current">▶</span>';
                } else {
                    $return .= '<span class="alm-status-icon pending">' . $counter . '</span>';
                }
                $return .= '</div>';
                
                // Chapter content
                $return .= '<div class="alm-chapter-content">';
                $return .= '<h4 class="alm-chapter-title">' . esc_html(stripslashes($chapter->chapter_title)) . '</h4>';
                $return .= '<div class="alm-chapter-meta">';
                $return .= '<span class="alm-duration">' . $format_time($chapter->duration) . '</span>';
                if ($is_active_chapter) {
                    $return .= '<span class="alm-current-badge">Current</span>';
                } elseif ($is_completed) {
                    $return .= '<span class="alm-completed-badge">Completed</span>';
                }
                $return .= '</div>';
                $return .= '</div>';
                
                // Play button
                $return .= '<div class="alm-chapter-action">';
                if ($is_active_chapter) {
                    $return .= '<span class="alm-play-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg></span>';
                } else {
                    $return .= '<span class="alm-play-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg></span>';
                }
                $return .= '</div>';
                
                $return .= '</a>';
                $return .= '</div>';
            }
            
            $return .= '</div>'; // End chapters-container
            $return .= '</div>'; // End alm-chapter-list
        }
        
        // LIST FORMAT
        if ($atts['format'] == 'list') {
            $return = '<ul>';
            foreach ($chapters as $chapter) {
                $chapter_link = '?c=' . $chapter->slug;
                $return .= '<li><a href="' . $chapter_link . '">' . esc_html(stripslashes($chapter->chapter_title)) . '</a></li>';
            }
            $return .= '</ul>';
        }
        
        return $return;
    }
    
    public function lesson_complete_shortcode($atts) {
        $atts = shortcode_atts(array(
            'lesson_id' => '',
            'user_membership_level' => '0'
        ), $atts);
        
        // Auto-detect lesson_id from current post if not provided
        if (empty($atts['lesson_id'])) {
            $post_id = get_the_ID();
            if ($post_id) {
                // Try ACF field first
                if (function_exists('get_field')) {
                    $lesson_id = get_field('alm_lesson_id', $post_id);
                }
                
                // Fallback to post meta
                if (empty($lesson_id)) {
                    $lesson_id = get_post_meta($post_id, 'alm_lesson_id', true);
                }
                
                if (!empty($lesson_id)) {
                    $atts['lesson_id'] = $lesson_id;
                }
            }
        }
        
        // Initialize return variable
        $return = '';
        
        if (empty($atts['lesson_id'])) {
            return $return . '<p style="color: red;">Error: lesson_id is required</p>';
        }
        
        // Access check moved to after lesson data is loaded
        
        global $wpdb;
        
        // Get lesson data
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
            $atts['lesson_id']
        ));
        
        if (!$lesson) {
            return '<p style="color: red;">Error: Lesson not found</p>';
        }
        
        // Check membership level - set access flag instead of early return
        $user_level = intval($atts['user_membership_level']);
        $lesson_level = intval($lesson->membership_level);
        $has_access = $user_level >= $lesson_level;
        
        // Check Essentials library access for Studio-level lessons
        if (!$has_access && $user_level == 1 && $lesson_level == 2) {
            global $user_id;
            if ($user_id && class_exists('ALM_Essentials_Library')) {
                $library = new ALM_Essentials_Library();
                if ($library->has_lesson_in_library($user_id, intval($atts['lesson_id']))) {
                    $has_access = true;
                }
            }
        }
        
        // Get level names for restricted content messages
        $current_level_name = $this->get_membership_level_name($user_level);
        $required_level_name = $this->get_membership_level_name($lesson_level);
        
        // Get chapters using our chapter handler
        $chapter_handler = new ALM_Chapter_Handler();
        $chapters = $chapter_handler->get_lesson_chapters($atts['lesson_id']);
        
        if (empty($chapters)) {
            return '<div style="text-align: center; width:100%;">No chapters found.</div>';
        }
        
        // Get current chapter data - use same logic as alm_lesson_video
        $chapter_slug = isset($_GET['c']) ? sanitize_text_field($_GET['c']) : null;
        $chapter_data = $chapter_handler->get_chapter_data($atts['lesson_id'], $chapter_slug);
        
        if (!$chapter_data['success']) {
            return '<p style="color: red;">Error: ' . $chapter_data['error'] . '</p>';
        }
        
        // Use the resolved chapter_id to get the actual chapter data
        $final_chapter_id = $chapter_data['chapter_id'];
        
        // Get chapter data directly from database (same as alm_lesson_video)
        $current_chapter = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_chapters WHERE ID = %d",
            $final_chapter_id
        ));
        
        if (!$current_chapter) {
            return '<p style="color: red;">Error: Chapter not found</p>';
        }
        
        // Format time helper function
        $format_time = function($seconds) {
            if (!$seconds || $seconds <= 0) return '0:00';
            
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = $seconds % 60;
            
            if ($hours > 0) {
                return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
            } else {
                return sprintf('%d:%02d', $minutes, $secs);
            }
        };
        
        // Calculate total duration
        $total_duration = 0;
        foreach ($chapters as $chapter) {
            $total_duration += intval($chapter->duration);
        }
        
        // Calculate progress
        $completed_chapters = 0;
        $total_chapters = count($chapters);
        
        foreach ($chapters as $chapter) {
            if (function_exists('je_is_chapter_complete') && je_is_chapter_complete($chapter->ID)) {
                $completed_chapters++;
            }
        }
        
        $progress_percentage = $total_chapters > 0 ? ($completed_chapters / $total_chapters) * 100 : 0;
        
        // Get video URL for current chapter
        $video_url = '';
        if (!empty($current_chapter->bunny_url)) {
            $video_url = $current_chapter->bunny_url;
        } elseif (!empty($current_chapter->youtube_id)) {
            $video_url = 'https://www.youtube.com/watch?v=' . $current_chapter->youtube_id;
        } elseif (!empty($current_chapter->vimeo_id)) {
            $video_url = 'https://vimeo.com/' . $current_chapter->vimeo_id;
        }
        
        // Get collection data
        $collection = null;
        $collection_lessons = array();
        if (!empty($lesson->collection_id)) {
            $collection = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}alm_collections WHERE ID = %d",
                $lesson->collection_id
            ));
            
            // Get all lessons in this collection for navigation, ordered by menu_order then title
            // This preserves the drag-and-drop order set in the admin collection page
            $collection_lessons = $wpdb->get_results($wpdb->prepare(
                "SELECT ID, lesson_title, slug, post_id 
                 FROM {$wpdb->prefix}alm_lessons 
                 WHERE collection_id = %d 
                 ORDER BY menu_order ASC, lesson_title ASC",
                $lesson->collection_id
            ));
        }
        
        // Check if using legacy academy_lessons table (for course_id)
        $course_id = null;
        if (empty($lesson->collection_id)) {
            // Try to get course_id from academy_lessons table
            $course_data = $wpdb->get_row($wpdb->prepare(
                "SELECT course_id FROM academy_lessons WHERE ID = %d",
                $atts['lesson_id']
            ));
            if ($course_data && !empty($course_data->course_id)) {
                $course_id = $course_data->course_id;
            }
        }
        
        // Start building the complete lesson experience
        $return = '<div class="alm-lesson-complete">';
        
        // 1. VIDEO PLAYER SECTION (Full Width)
        if (!empty($video_url) && $has_access) {
            $return .= '<div class="alm-video-section">';
            $lesson_title = stripslashes($lesson->lesson_title);
            $chapter_title = stripslashes($current_chapter->chapter_title);
            $return .= '<div class="alm-video-title-bar">';
            $return .= '<div style="display: flex; align-items: center; gap: 8px;">';
            $return .= '<span class="alm-lesson-name">' . esc_html($lesson_title) . '</span>';
            $return .= '<span class="alm-chapter-name">(' . esc_html($chapter_title) . ')</span>';
            $return .= '</div>';
            
            // Display progress percentage (use calculated value from line 1121)
            $progress_badge_value = $total_chapters > 0 ? intval(($completed_chapters / $total_chapters) * 100) : 0;
            $return .= '<div class="alm-progress-badge">' . $progress_badge_value . '% Complete</div>';
            $return .= '</div>';
            
            // Get VTT subtitle URL if available
            $subtitle_url = $this->get_chapter_subtitle_url($current_chapter->ID);
            
            // Build FV Player shortcode
            $shortcode = '[fvplayer src="' . esc_url($video_url) . '" width="100%" height="600" splash="https://jazzedge.academy/wp-content/uploads/2023/12/splash-play-video.jpg"';
            
            // Add subtitles if VTT file exists
            if ($subtitle_url) {
                $shortcode .= ' subtitles="' . esc_url($subtitle_url) . '"';
            }
            
            $shortcode .= ']';
            
            $return .= do_shortcode($shortcode);
            
            // Add buttons and progress in 3-column layout - Mobile responsive with inline styles
            $return .= '<style>
            @media screen and (max-width: 768px) {
                body { overflow-x: hidden !important; max-width: 100vw !important; }
                .alm-lesson-complete { max-width: 100% !important; width: 100% !important; overflow-x: hidden !important; }
                .alm-video-section { width: 100% !important; max-width: 100% !important; overflow-x: hidden !important; }
                .alm-video-title-bar { padding: 12px 16px !important; flex-wrap: wrap !important; }
                .alm-video-section iframe, .alm-video-section video { width: 100% !important; max-width: 100% !important; }
                .alm-actions-section {
                    flex-direction: column !important;
                    gap: 16px !important;
                    padding: 16px !important;
                    align-items: stretch !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                }
                .alm-action-left, .alm-action-right {
                    width: 100% !important;
                    max-width: 100% !important;
                    flex-shrink: 1 !important;
                    box-sizing: border-box !important;
                }
                .alm-action-center {
                    order: -1 !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    margin-bottom: 8px !important;
                    flex: none !important;
                    box-sizing: border-box !important;
                }
                .alm-action-left button, .alm-action-right button {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                }
            }
            </style>';
            $return .= '<div class="alm-actions-section' . (!$has_access ? ' alm-restricted' : '') . '" style="display: flex; align-items: center; gap: 24px; padding: 16px 24px; background: #fff;">';
            
            if ($has_access) {
                // Left: Mark Complete Button
                $return .= '<div class="alm-action-left" style="flex-shrink: 0; width: 180px;">';
                $return .= do_shortcode('[alm_mark_complete lesson_id="' . $atts['lesson_id'] . '" chapter_id="' . $final_chapter_id . '" type="chapter"]');
                $return .= '</div>';
                
                // Center: Progress Bar
                $return .= '<div class="alm-action-center" style="flex: 1; display: flex; flex-direction: column; gap: 6px; align-items: center;">';
                $return .= '<div class="alm-progress-bar">';
                $return .= '<div class="alm-progress-fill" style="width: ' . $progress_percentage . '%"></div>';
                $return .= '</div>';
                $return .= '<div class="alm-progress-text">Progress: ' . $completed_chapters . ' of ' . $total_chapters . ' chapters completed</div>';
                $return .= '</div>';
                
                // Right: Save Favorite Button
                $return .= '<div class="alm-action-right" style="flex-shrink: 0; width: 180px;">';
                $post_id = !empty($lesson->post_id) ? $lesson->post_id : get_the_ID();
                $title = stripslashes($lesson->lesson_title);
                $url = get_permalink($post_id);
                $return .= '<button id="save-lesson-favorite-' . $atts['lesson_id'] . '" class="save-favorite-btn" data-post-id="' . $post_id . '" data-title="' . esc_attr($title) . '" data-url="' . esc_url($url) . '" style="background: #f04e23; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; width: 100%; justify-content: center;">';
                $return .= '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="btn-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>';
                $return .= 'Save as Favorite';
                $return .= '</button>';
                $return .= '</div>';
            } else {
                // Show restricted access message
                $return .= '<div class="alm-action-center">';
                $return .= '<div class="alm-restricted-message">';
                $return .= '<svg style="width: 32px; height: 32px; margin-bottom: 12px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>';
                $return .= '<p style="margin-bottom: 16px; font-size: 15px; color: #212529; font-weight: 500;">Get full access to video lessons, sheet music, backing tracks, and more</p>';
                $return .= '<a href="/upgrade" class="alm-upgrade-cta-btn">Get Full Access →</a>';
                $return .= '</div>';
                $return .= '</div>';
            }
            
            $return .= '</div>';
            
            $return .= '</div>';
        } elseif (!$has_access) {
            // NO ACCESS: Show sample video if available, otherwise show upgrade message
            $sample_video_url = !empty($lesson->sample_video_url) ? $lesson->sample_video_url : '';
            
            $return .= '<div class="alm-video-section">';
            $lesson_title = stripslashes($lesson->lesson_title);
            $return .= '<div class="alm-video-title-bar">';
            $return .= '<span class="alm-lesson-name">' . esc_html($lesson_title) . '</span>';
            $return .= '</div>';
            
            if (!empty($sample_video_url)) {
                // Show sample video with overlay message
                $return .= '<div class="alm-video-placeholder" style="position: relative;">';
                $return .= do_shortcode('[fvplayer src="' . esc_url($sample_video_url) . '" width="100%" height="600" splash="https://jazzedge.academy/wp-content/uploads/2023/12/splash-play-video.jpg"]');
                $return .= '<div class="alm-sample-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.7) 100%); display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding: 30px; pointer-events: none;">';
                $return .= '<div style="background: rgba(255,255,255,0.95); padding: 20px 30px; border-radius: 12px; text-align: center; max-width: 500px; pointer-events: auto;">';
                $return .= '<svg style="width: 32px; height: 32px; margin-bottom: 12px; color: #239B90;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>';
                $return .= '<h3 style="margin: 0 0 8px 0; font-size: 18px; color: #004555; font-weight: 600;">Sample Video</h3>';
                $return .= '<p style="margin: 0 0 16px 0; font-size: 14px; color: #495057;">This is a preview. Get full access to all video lessons, sheet music, backing tracks, and more.</p>';
                $return .= '<a href="/upgrade" class="alm-upgrade-button" style="display: inline-block; padding: 12px 24px; background: #239B90; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;">Upgrade to ' . esc_html($required_level_name) . '</a>';
                $return .= '</div>';
                $return .= '</div>';
                $return .= '</div>';
            } else {
                // No sample video - show upgrade message only
                $return .= '<div class="alm-video-placeholder">';
                $return .= '<div class="alm-restricted-overlay">';
                $return .= '<svg style="width: 48px; height: 48px; margin-bottom: 16px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>';
                $return .= '<h3 style="margin: 0 0 12px 0; font-size: 24px; color: #004555;">Premium Content</h3>';
                $return .= '<p style="margin: 0 0 24px 0; font-size: 16px; color: #495057;">Get full access to video lessons, sheet music, backing tracks, and more</p>';
                $return .= '<a href="/upgrade" class="alm-upgrade-button">Upgrade to ' . esc_html($required_level_name) . '</a>';
                $return .= '</div>';
                $return .= '</div>';
            }
            $return .= '</div>';
            
            // Add restricted actions section
            $return .= '<div class="alm-actions-section alm-restricted">';
            $return .= '<div class="alm-action-center">';
            $return .= '<div class="alm-restricted-message">';
            $return .= '<svg style="width: 32px; height: 32px; margin-bottom: 12px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>';
            $return .= '<p style="margin-bottom: 16px; font-size: 15px; color: #212529; font-weight: 500;">Get full access to video lessons, sheet music, backing tracks, and more</p>';
            $return .= '<a href="/upgrade" class="alm-upgrade-cta-btn">Get Full Access →</a>';
            $return .= '</div>';
            $return .= '</div>';
            $return .= '</div>';
        } else {
            // No video source available
            $return .= '<div class="alm-video-section">';
            $return .= '<h3 class="alm-current-chapter-title">' . esc_html(stripslashes($current_chapter->chapter_title)) . '</h3>';
            $return .= '<div class="alm-video-placeholder">';
            $return .= '<h3>No Video Available</h3>';
            $return .= '<p>This chapter does not have a video source configured.</p>';
            $return .= '</div>';
            $return .= '</div>';
        }
        
        // 3. TWO-COLUMN LAYOUT: Chapters (Left) + Resources/Details (Right)
        $return .= '<div class="alm-main-content-grid">';
        
        // LEFT COLUMN: Chapters List
        $return .= '<div class="alm-chapters-column">';
        $return .= '<div class="alm-chapters-container">';
        
        foreach ($chapters as $index => $chapter) {
            $counter = $index + 1;
            $chapter_link = '?c=' . $chapter->slug;
            $is_active_chapter = ($chapter->slug == $chapter_slug) ? 'active' : '';
            $is_completed = function_exists('je_is_chapter_complete') ? je_is_chapter_complete($chapter->ID) : false;
            
            $return .= '<div class="alm-chapter-item ' . $is_active_chapter . ($is_completed ? ' completed' : '') . (!$has_access ? ' alm-restricted' : '') . '" data-chapter-id="' . $chapter->ID . '">';
            
            if ($has_access) {
                $return .= '<a href="' . $chapter_link . '" class="alm-chapter-link">';
            } else {
                $return .= '<div class="alm-chapter-link alm-restricted-link">';
            }
            
            // Chapter number with status icon
            $return .= '<div class="alm-chapter-number" data-chapter-number="' . $counter . '">';
            if (!$has_access) {
                $return .= '<svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>';
            } elseif ($is_completed) {
                $return .= '<svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
            } elseif ($is_active_chapter) {
                $return .= '<span class="alm-status-icon current">▶</span>';
            } else {
                $return .= '<span class="alm-status-icon pending">' . $counter . '</span>';
            }
            $return .= '</div>';
            
            // Chapter content
            $return .= '<div class="alm-chapter-content">';
            $return .= '<h4 class="alm-chapter-title">' . esc_html(stripslashes($chapter->chapter_title)) . '</h4>';
            $return .= '<div class="alm-chapter-meta">';
            $return .= '<span class="alm-duration">' . $format_time($chapter->duration) . '</span>';
            if (!$has_access) {
                $return .= '<span class="alm-locked-badge">Locked</span>';
            } elseif ($is_active_chapter) {
                $return .= '<span class="alm-current-badge">Current</span>';
            } elseif ($is_completed) {
                $return .= '<span class="alm-completed-badge">Completed</span>';
            }
            $return .= '</div>';
            $return .= '</div>';
            
            // Play button
            $return .= '<div class="alm-chapter-action">';
            $return .= '<span class="alm-play-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg></span>';
            $return .= '</div>';
            
            if ($has_access) {
                $return .= '</a>';
            } else {
                $return .= '</div>';
            }
            $return .= '</div>';
        }
        
        $return .= '</div>'; // End chapters-container
        
        // Add Notes Section
        global $wpdb, $user_id;
        $lesson_id = $atts['lesson_id'];
        
        if ($user_id > 0 && $has_access) {
            // Get existing notes for this lesson from ALM notes table
            $existing_note = $wpdb->get_row($wpdb->prepare(
                "SELECT notes_content FROM {$wpdb->prefix}alm_user_notes WHERE user_id = %d AND lesson_id = %d",
                $user_id,
                $lesson_id
            ));
            
            $return .= '<div class="alm-notes-section">';
            $return .= '<div class="alm-notes-header">LESSON NOTES</div>';
            $return .= '<div class="alm-notes-content">';
            $return .= '<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>';
            $return .= '<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">';
            $return .= '<div id="editor-' . $lesson_id . '">';
            $return .= $existing_note ? $existing_note->notes_content : '';
            $return .= '</div>';
            $return .= '<div class="alm-notes-actions">';
            $return .= '<div class="alm-notes-status" style="font-size: 12px; display:none; margin-bottom: 10px;"></div>';
            $return .= '<button type="button" id="save-notes-' . $lesson_id . '" class="alm-save-notes-btn" data-lesson-id="' . $lesson_id . '">';
            $return .= '<svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
            $return .= ' Save Notes';
            $return .= '</button>';
            $return .= '</div>';
            $return .= '</div>';
            $return .= '</div>';
            
            $return .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const quill_' . $lesson_id . ' = new Quill("#editor-' . $lesson_id . '", {
                    theme: "snow",
                    placeholder: "Click in here to take notes. Click SAVE NOTES when finished.",
                    modules: {
                        toolbar: [
                            [{ "header": [1, 2, 3, false] }],
                            ["bold", "italic", "underline"],
                            [{ "color": [] }, { "background": [] }],
                            [{ "list": "ordered"}, { "list": "bullet" }],
                            [{ "indent": "-1"}, { "indent": "+1" }],
                            ["blockquote", "code-block"],
                            ["link"],
                            ["clean"]
                        ]
                    }
                });
                
                const saveBtn = document.getElementById("save-notes-' . $lesson_id . '");
                const statusDiv = document.querySelector(".alm-notes-status");
                
                saveBtn.addEventListener("click", function() {
                    const content = quill_' . $lesson_id . '.root.innerHTML;
                    
                    // Show loading state
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = `
                        <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                        </svg>
                        Saving...
                    `;
                    
                    // Send AJAX request
                    fetch("' . admin_url('admin-ajax.php') . '", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: new URLSearchParams({
                            action: "alm_save_lesson_notes",
                            nonce: "' . wp_create_nonce('alm_notes_nonce') . '",
                            lesson_id: ' . $lesson_id . ',
                            user_notes: content
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            statusDiv.textContent = "✓ Notes saved successfully";
                            statusDiv.style.color = "#28a745";
                            statusDiv.style.display = "block";
                            
                            setTimeout(() => {
                                statusDiv.style.display = "none";
                            }, 3000);
                        } else {
                            statusDiv.textContent = "✗ Error saving notes: " + data.data.message;
                            statusDiv.style.color = "#dc3545";
                            statusDiv.style.display = "block";
                        }
                    })
                    .catch(error => {
                        statusDiv.textContent = "✗ Network error occurred";
                        statusDiv.style.color = "#dc3545";
                        statusDiv.style.display = "block";
                    })
                    .finally(() => {
                        // Reset button
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = `
                            <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            Save Notes
                        `;
                    });
                });
            });
            </script>';
        } elseif (!$has_access) {
            $return .= '<div class="alm-notes-section alm-restricted">';
            $return .= '<div class="alm-notes-header">LESSON NOTES</div>';
            $return .= '<div class="alm-notes-content">';
            $return .= '<div class="alm-restricted-overlay">';
            $return .= '<svg style="width: 32px; height: 32px; margin-bottom: 12px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>';
            $return .= '<p>Take notes and save your progress</p>';
            $return .= '<a href="/upgrade" class="alm-upgrade-link">Upgrade to ' . esc_html($required_level_name) . '</a>';
            $return .= '</div>';
            $return .= '</div>';
            $return .= '</div>';
        } else {
            $return .= '<div class="alm-notes-section">';
            $return .= '<div class="alm-notes-header">LESSON NOTES</div>';
            $return .= '<div class="alm-notes-content">';
            $return .= '<div style="padding: 20px">Only <a href="/login" class="hover-black">logged-in</a> students can take notes.</div>';
            $return .= '</div>';
            $return .= '</div>';
        }
        
        $return .= '</div>'; // End alm-chapters-column
        
        // RIGHT COLUMN: Resources, Details, Collection
        $return .= '<div class="alm-sidebar-column">';
        
        // Lesson Resources
        if ($has_access) {
            $return .= do_shortcode('[alm_lesson_resources lesson_id="' . $atts['lesson_id'] . '" user_membership_level="' . $atts['user_membership_level'] . '"]');
        } else {
            $return .= '<div class="alm-sidebar-card alm-restricted">';
            $return .= '<div class="alm-card-header">LESSON RESOURCES</div>';
            $return .= '<div class="alm-card-content">';
            $return .= '<div class="alm-restricted-overlay">';
            $return .= '<svg style="width: 24px; height: 24px; margin-bottom: 8px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>';
            $return .= '<p>Download lesson materials</p>';
            $return .= '<a href="/upgrade" class="alm-upgrade-link">Upgrade to ' . esc_html($required_level_name) . '</a>';
            $return .= '</div>';
            $return .= '</div>';
            $return .= '</div>';
        }
        
        // Lesson Details Card
        $post_id = !empty($lesson->post_id) ? $lesson->post_id : get_the_ID();
        $title = stripslashes($lesson->lesson_title);
        $url = get_permalink($post_id);
        
        $return .= '<div class="alm-sidebar-card">';
        $return .= '<div class="alm-card-header alm-lesson-header">LESSON DETAILS</div>';
        $return .= '<div class="alm-card-content">';
        $return .= '<h3 class="alm-card-title">' . esc_html(stripslashes($lesson->lesson_title)) . '</h3>';
        if (!empty($lesson->lesson_description)) {
            // Strip all HTML tags for clean display
            $clean_description = wp_strip_all_tags(stripslashes($lesson->lesson_description));
            $return .= '<p class="alm-card-description">' . esc_html($clean_description) . '</p>';
        }
        
        // Debug: Show membership level (only if debug parameter is set)
        if (isset($_GET['debug']) && !empty($_GET['debug'])) {
            if (!$this->ensure_alm_settings_loaded()) {
                $return .= '<p>Error: Membership settings not available.</p>';
                return $return;
            }
            
            global $wpdb, $user_id;
            
            $user_level = intval($atts['user_membership_level']);
            $lesson_level = intval($lesson->membership_level);
            $required_level_name = ALM_Admin_Settings::get_membership_level_name($lesson_level);
            $user_level_name = ALM_Admin_Settings::get_membership_level_name($user_level);
            
            $return .= '<div style="background: #f9f9f9; padding: 10px; margin-top: 10px; border-left: 3px solid #0073aa; font-size: 12px;">';
            $return .= '<strong>DEBUG - Membership Levels:</strong><br>';
            $return .= 'User Level: ' . $user_level . ' (' . esc_html($user_level_name) . ')<br>';
            $return .= 'Lesson Level: ' . $lesson_level . ' (' . esc_html($required_level_name) . ')<br>';
            $return .= 'Has Access: ' . ($has_access ? 'YES' : 'NO') . '<br>';
            
            // Essentials Library Debug Info
            if ($user_level == 1 && $lesson_level == 2) {
                $return .= '<br><strong>Essentials Library Check:</strong><br>';
                $return .= 'User ID: ' . ($user_id ? esc_html($user_id) : 'NOT SET') . '<br>';
                $return .= 'ALM Lesson ID: ' . esc_html($atts['lesson_id']) . '<br>';
                
                // Get post ID
                $post_id = !empty($lesson->post_id) ? $lesson->post_id : get_the_ID();
                $return .= 'Post ID: ' . esc_html($post_id) . '<br>';
                
                // Check if in library
                if ($user_id && class_exists('ALM_Essentials_Library')) {
                    $library = new ALM_Essentials_Library();
                    $in_library = $library->has_lesson_in_library($user_id, intval($atts['lesson_id']));
                    $return .= 'In Library: ' . ($in_library ? 'YES' : 'NO') . '<br>';
                    
                    // Direct database check
                    $db_check = $wpdb->get_var($wpdb->prepare(
                        "SELECT id FROM {$wpdb->prefix}alm_essentials_library WHERE user_id = %d AND lesson_id = %d",
                        $user_id, intval($atts['lesson_id'])
                    ));
                    $return .= 'Database Check: ' . ($db_check ? 'FOUND (ID: ' . $db_check . ')' : 'NOT FOUND') . '<br>';
                } else {
                    $return .= 'Library Check: ' . (class_exists('ALM_Essentials_Library') ? 'Class exists' : 'Class NOT FOUND') . '<br>';
                }
            }
            
            $return .= '</div>';
        }
        
        $return .= '</div>';
        $return .= '</div>';
        
        // Collection Details Card (if collection exists)
        if ($collection) {
            $return .= '<div class="alm-sidebar-card">';
            $return .= '<div class="alm-card-header alm-collection-header">LESSON COLLECTION DETAILS</div>';
            $return .= '<div class="alm-card-content">';
            
            // Make collection title a clickable link to the collection listing page
            $collection_title = esc_html(stripslashes($collection->collection_title));
            $collection_url = null;
            if (!empty($collection->post_id)) {
                $collection_url = get_permalink($collection->post_id);
                $return .= '<h3 class="alm-card-title"><a href="' . esc_url($collection_url) . '" style="text-decoration: none; color: inherit; display: block;">' . $collection_title . '</a></h3>';
            } else {
                $return .= '<h3 class="alm-card-title">' . $collection_title . '</h3>';
            }
            
            if (!empty($collection->collection_description)) {
                $return .= '<p class="alm-card-description">' . esc_html(stripslashes($collection->collection_description)) . '</p>';
            }
            
            // Lesson navigation dropdown (always show if collection has multiple lessons)
            if (!empty($collection_lessons) && count($collection_lessons) > 1) {
                $return .= '<div class="alm-lesson-nav">';
                $return .= '<select class="alm-lesson-selector" onchange="javascript:location.href = this.value;">';
                $return .= '<option value="">Lessons...</option>';
                
                foreach ($collection_lessons as $nav_lesson) {
                    // Use post_id from wp_alm_lessons table
                    $lesson_post_id = !empty($nav_lesson->post_id) ? $nav_lesson->post_id : null;
                    
                    // Check if lesson is completed
                    $lesson_complete = '';
                    if (function_exists('je_is_lesson_marked_complete')) {
                        $lesson_complete = je_is_lesson_marked_complete($nav_lesson->ID) ? ' (done)' : '';
                    }
                    
                    // Build URL using the lesson's actual post_id
                    if ($lesson_post_id) {
                        $lesson_url = get_permalink($lesson_post_id);
                    } else {
                        $lesson_url = '#';
                    }
                    
                    // Mark current lesson
                    $mark = ($nav_lesson->ID == $atts['lesson_id']) ? '» ' : '';
                    
                    // Format title - show start and end with ... in middle if too long
                    $lesson_title = stripslashes($nav_lesson->lesson_title);
                    $title_length = mb_strlen($lesson_title);
                    $max_length = 40; // Maximum total length
                    
                    if ($title_length > $max_length) {
                        // Take first part (about 18 chars) and last part (about 18 chars) with ... in middle
                        $start_len = 18;
                        $end_len = 18;
                        $start = mb_substr($lesson_title, 0, $start_len);
                        $end = mb_substr($lesson_title, -$end_len);
                        $trimmed_title = $start . '...' . $end;
                    } else {
                        $trimmed_title = $lesson_title;
                    }
                    
                    $selected = ($nav_lesson->ID == $atts['lesson_id']) ? ' selected' : '';
                    $return .= '<option value="' . esc_url($lesson_url) . '"' . $selected . '>' . $mark . esc_html($trimmed_title) . $lesson_complete . '</option>';
                }
                
                $return .= '</select>';
                $return .= '</div>';
                
                // Add CSS to prevent dropdown from being cut off
                $return .= '<style>
                .alm-lesson-nav {
                    position: relative;
                    z-index: 1000;
                }
                .alm-lesson-selector {
                    width: 100%;
                    padding: 10px 12px;
                    border: 1px solid #e5e7eb;
                    border-radius: 6px;
                    font-size: 14px;
                    background: white;
                    cursor: pointer;
                }
                .alm-lesson-selector:focus {
                    outline: 2px solid #667eea;
                    outline-offset: 2px;
                }
                </style>';
            }
            
            // Add button to go back to collection listing
            if (!empty($collection_url)) {
                $return .= '<div class="alm-collection-back-button" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">';
                $return .= '<a href="' . esc_url($collection_url) . '" class="alm-btn-collection-back" style="display: block; text-align: center; padding: 12px 20px; background: #229B90; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 14px; transition: background 0.2s ease;">';
                $return .= '← Back to Collection';
                $return .= '</a>';
                $return .= '</div>';
                // Add hover effect
                $return .= '<style>
                .alm-btn-collection-back:hover {
                    background: #1d7a70 !important;
                }
                </style>';
            }
            
            $return .= '</div>';
            $return .= '</div>';
        }
        
        $return .= '</div>'; // End alm-sidebar-column
        $return .= '</div>'; // End alm-main-content-grid
        $return .= '</div>'; // End alm-lesson-complete
        
        // Add JavaScript for favorite button
        $return .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const saveBtn = document.getElementById("save-lesson-favorite-' . $atts['lesson_id'] . '");
            if (!saveBtn) return;
            
            let isFavorited = false;
            let favoriteId = null;
            const title = saveBtn.getAttribute("data-title");
            const url = saveBtn.getAttribute("data-url");

            // Check if lesson is already favorited on page load
            function checkFavoriteStatus() {
                const lessonData = {
                    title: title
                };

                fetch("/wp-json/aph/v1/lesson-favorites/check", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-WP-Nonce": "' . wp_create_nonce('wp_rest') . '"
                    },
                    body: JSON.stringify(lessonData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        isFavorited = data.is_favorited;
                        favoriteId = data.favorite_id;
                        updateButtonState();
                    }
                })
                .catch(error => {
                    console.error("Error checking favorite status:", error);
                });
            }

            // Update button appearance based on favorite status
            function updateButtonState() {
                if (isFavorited) {
                    saveBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="btn-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Remove Favorite
                    `;
                    saveBtn.style.cssText = "background: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; width: 100%; justify-content: center;";
                } else {
                    saveBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="btn-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                        </svg>
                        Save as Favorite
                    `;
                    saveBtn.style.cssText = "background: #f04e23; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; width: 100%; justify-content: center;";
                }
            }

            // Handle button click
            saveBtn.addEventListener("click", function() {
                const lessonData = {
                    title: title,
                    url: url,
                    category: "lesson"
                };

                // Validate required fields
                if (!lessonData.title || !lessonData.url) {
                    alert("Missing lesson title or URL");
                    return;
                }

                // Show loading state
                const action = isFavorited ? "Removing" : "Saving";
                saveBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="btn-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    ${action}...
                `;
                saveBtn.disabled = true;

                // Determine endpoint and action
                const endpoint = isFavorited ? "/wp-json/aph/v1/lesson-favorites/remove" : "/wp-json/aph/v1/lesson-favorites";
                const actionType = isFavorited ? "remove" : "add";

                // Send to REST API
                fetch(endpoint, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-WP-Nonce": "' . wp_create_nonce('wp_rest') . '"
                    },
                    body: JSON.stringify(lessonData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Toggle favorite status
                        isFavorited = !isFavorited;
                        
                        // Show success message
                        const successAction = actionType === "add" ? "Saved" : "Removed";
                        saveBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="btn-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            ${successAction}!
                        `;
                        saveBtn.style.cssText = "background: #28a745; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; width: 100%; justify-content: center;";
                        
                        setTimeout(() => {
                            updateButtonState();
                            saveBtn.disabled = false;
                        }, 2000);
                    } else {
                        // Handle specific error cases
                        if (data.code === "duplicate_favorite") {
                            saveBtn.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="btn-icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                                Already Saved
                            `;
                            saveBtn.style.cssText = "background: #ffc107; color: #000; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; width: 100%; justify-content: center;";
                        } else {
                            throw new Error(data.message || "Failed to " + actionType + " favorite");
                        }
                        setTimeout(() => {
                            updateButtonState();
                            saveBtn.disabled = false;
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    saveBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="btn-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Error
                    `;
                    saveBtn.style.cssText = "background: #dc3545; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; width: 100%; justify-content: center;";
                    setTimeout(() => {
                        updateButtonState();
                        saveBtn.disabled = false;
                    }, 2000);
                });
            });

            // Check favorite status on page load
            checkFavoriteStatus();
        });
        </script>';
        
        return $return;
    }
    
    public function lesson_resources_shortcode($atts) {
        $atts = shortcode_atts(array(
            'lesson_id' => '',
            'user_membership_level' => '0'
        ), $atts);
        
        // Auto-detect lesson_id from current post if not provided
        if (empty($atts['lesson_id'])) {
            $post_id = get_the_ID();
            if ($post_id) {
                // Try ACF field first
                if (function_exists('get_field')) {
                    $lesson_id = get_field('alm_lesson_id', $post_id);
                }
                
                // Fallback to post meta
                if (empty($lesson_id)) {
                    $lesson_id = get_post_meta($post_id, 'alm_lesson_id', true);
                }
                
                if (!empty($lesson_id)) {
                    $atts['lesson_id'] = $lesson_id;
                }
            }
        }
        
        if (empty($atts['lesson_id'])) {
            return '<p style="color: red;">Error: lesson_id is required</p>';
        }
        
        global $wpdb;
        
        // Get lesson data
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
            $atts['lesson_id']
        ));
        
        if (!$lesson) {
            return '<p style="color: red;">Error: Lesson not found</p>';
        }
        
        // SECURITY: Check membership level
        $user_level = intval($atts['user_membership_level']);
        $lesson_level = intval($lesson->membership_level);
        
        $has_access = $user_level >= $lesson_level;
        
        // Check Essentials library access for Studio-level lessons
        if (!$has_access && $user_level == 1 && $lesson_level == 2) {
            global $user_id;
            if ($user_id && class_exists('ALM_Essentials_Library')) {
                $library = new ALM_Essentials_Library();
                if ($library->has_lesson_in_library($user_id, intval($atts['lesson_id']))) {
                    $has_access = true;
                }
            }
        }
        
        if (!$has_access) {
            return '<p style="color: red;">Access denied: Insufficient membership level</p>';
        }
        
        // Additional access checks (from original shortcode)
        $user_id = get_current_user_id();
        $access = false;
        
        // Check if user has membership level 1 or higher
        if ($user_level >= 1) {
            $access = true;
        }
        
        // Check for special post IDs (30-Day Playbook)
        $post_id = get_the_ID();
        if ($post_id == 587 || $post_id == 547 || $post_id == 548) {
            $access = true;
        }
        
        // Check lesson access and academy credit access
        if (function_exists('je_has_lesson_access') && function_exists('je_check_academy_credit_access')) {
            $je_has_lesson_access = je_has_lesson_access();
            $academy_credit_access = je_check_academy_credit_access();
            
            if ($je_has_lesson_access === 'true' && $user_level > 1) {
                $access = true;
            }
            if ($academy_credit_access === 'true') {
                $access = true;
            }
        }
        
        // Check for payment failed tag
        if (function_exists('do_shortcode')) {
            $payment_failed = do_shortcode('[memb_has_any_tag tagid=7772]');
            if ($payment_failed === 'Yes') {
                $access = false;
            }
        }
        
        // Check if membership expired
        if (function_exists('je_return_membership_expired') && je_return_membership_expired() == 'true') {
            $access = false;
        }
        
        if (!$access) {
            return '<div style="text-align: center; padding: 20px;">
                        <h4 style="color:#F04E23; text-align: center; width: 100%;">Looking for sheet music and backing tracks?</h4>
                        <p><a href="/signup" class="hover-black">Upgrade</a> your membership to gain access to the sheet music and resources.</p>
                        <p style="font-size: 10pt;">If you are seeing this message, and you are a member, <strong>make sure you are logged in</strong>.</p>
                    </div>';
        }
        
        // Get resources from database (using NEW ALM table)
        $resources_serialized = $wpdb->get_var($wpdb->prepare(
            "SELECT resources FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
            $atts['lesson_id']
        ));
        
        // Always build the card structure
        $return = '<div class="alm-sidebar-card">';
        $return .= '<div class="alm-card-header">LESSON RESOURCES</div>';
        $return .= '<div class="alm-card-content">';
        
        // Check if there are resources
        if (empty($resources_serialized)) {
            $return .= '<p style="text-align: center; color: #6c757d; padding: 20px 0;">No resources available for this lesson.</p>';
            $return .= '</div>'; // End alm-card-content
            $return .= '</div>'; // End alm-sidebar-card
            return $return;
        }
        
        $resources = unserialize($resources_serialized);
        
        if (empty($resources) || !is_array($resources)) {
            $return .= '<p style="text-align: center; color: #6c757d; padding: 20px 0;">No resources available for this lesson.</p>';
            $return .= '</div>'; // End alm-card-content
            $return .= '</div>'; // End alm-sidebar-card
            return $return;
        }
        
        // Build resources list
        $return .= '<ul class="alm-resources-list" style="list-style: none; margin: 0; padding: 0;">';
        
        $found_resources = false;
        
        // Sort resources by type to group them together
        $resource_types_order = ['sheet_music', 'pdf', 'ireal', 'jam', 'zip', 'midi', 'note'];
        $sorted_resources = array();
        
        // First pass: get all resources sorted by type
        foreach ($resource_types_order as $type) {
            foreach ($resources as $k => $v) {
                $key_lower = strtolower($k);
                if (strpos($key_lower, $type) === 0) {
                    $sorted_resources[$k] = $v;
                }
            }
        }
        
        // Add any resources that didn't match our known types
        foreach ($resources as $k => $v) {
            if (!isset($sorted_resources[$k])) {
                $sorted_resources[$k] = $v;
            }
        }
        
        foreach ($sorted_resources as $k => $v) {
            if (empty($v)) {
                continue;
            }
            
            // Handle both old (string) and new (array) resource formats
            $resource_url = '';
            $resource_label = '';
            if (is_array($v)) {
                $resource_url = isset($v['url']) ? $v['url'] : '';
                $resource_label = isset($v['label']) ? $v['label'] : '';
                
                // Safety check: if url is still an array, try to extract from it
                if (is_array($resource_url)) {
                    $resource_url = isset($resource_url['url']) ? $resource_url['url'] : (isset($resource_url[0]) ? $resource_url[0] : '');
                }
            } else {
                $resource_url = $v;
            }
            
            // Ensure resource_url is a string before proceeding
            if (!is_string($resource_url) && !is_numeric($resource_url)) {
                // Try to convert to string or skip
                if (is_array($resource_url)) {
                    continue; // Skip invalid array resources
                }
                $resource_url = (string) $resource_url;
            }
            
            if (empty($resource_url)) {
                continue;
            }
            
            // Filter out unwanted resource types
            $excluded_types = ['map', 'mp3', 'mid'];
            if (in_array(strtolower($k), $excluded_types)) {
                continue;
            }
            
            $found_resources = true;
            
            // Get resource type and icon based on the resource key
            $icon = '';
            $resource_name = '';
            
            // Check if this is a numbered resource (jam2, jam3, ireal2, etc.)
            $is_numbered = preg_match('/^(jam|ireal|sheet_music|pdf)(\d+)$/', strtolower($k), $matches);
            
            if ($is_numbered) {
                $base_type = $matches[1];
                $number = $matches[2];
                
                switch ($base_type) {
                    case 'jam':
                        $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.369 4.369 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/></svg>';
                        $resource_name = 'Backing Track ' . $number;
                        break;
                    case 'ireal':
                        $icon = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" /></svg>';
                        $resource_name = 'iRealPro ' . $number;
                        break;
                    case 'sheet_music':
                    case 'pdf':
                        $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>';
                        $resource_name = 'Sheet Music ' . $number;
                        break;
                }
            } else {
                switch (strtolower($k)) {
                    case 'ireal':
                        $icon = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" /></svg>';
                        $resource_name = 'iRealPro';
                        break;
                    case 'sheet_music':
                    case 'pdf':
                        $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>';
                        $resource_name = 'Sheet Music';
                        break;
                    case 'jam':
                        $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.369 4.369 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/></svg>';
                        $resource_name = 'Backing Track';
                        break;
                    case 'zip':
                        $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
                        $resource_name = 'Zip File';
                        break;
                    case 'midi':
                        $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>';
                        $resource_name = 'Midi';
                        break;
                    case 'note':
                        $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>';
                        $resource_name = 'Note';
                        break;
                    default:
                        // Default icon for unknown types
                        $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>';
                        $resource_name = ucfirst(str_replace('_', ' ', $k));
                        break;
                }
            }
            
            // Final validation: ensure resource_url is definitely a string at this point
            // Re-check and extract if still an array
            if (is_array($resource_url)) {
                // Try one more time to extract
                $resource_url = isset($resource_url['url']) ? $resource_url['url'] : (isset($resource_url[0]) ? $resource_url[0] : '');
            }
            
            // Convert to string if numeric
            if (is_numeric($resource_url)) {
                $resource_url = (string) $resource_url;
            }
            
            // Final check: skip if still not a string or empty
            if (!is_string($resource_url) || empty($resource_url)) {
                error_log("ALM Shortcodes: Skipping invalid resource for lesson {$atts['lesson_id']}. Resource type: {$k}, Resource value type: " . gettype($resource_url));
                continue;
            }
            
            // Handle note type - display as text, not a link
            if ($k === 'note') {
                $return .= '<li class="alm-resource-item" style="background: #fff8e1; border-radius: 6px; margin-bottom: 8px; padding: 12px 16px; border-left: 4px solid #f57c00; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                $return .= '<span style="color: #f57c00; flex-shrink: 0; margin-right: 8px;">📝</span>';
                $return .= '<div style="flex-grow: 1; color: #333; white-space: pre-wrap;">' . esc_html($resource_url) . '</div>';
                $return .= '</li>';
                continue;
            }
            
            // Build resource link for file-based resources
            // At this point, $resource_url must be a string, but double-check before urlencode
            if (!is_string($resource_url)) {
                error_log("ALM Shortcodes: Resource URL is not a string before urlencode. Type: " . gettype($resource_url) . ", Value: " . print_r($resource_url, true));
                continue;
            }
            $final_resource_url = 'https://jazzedge.academy/je_link.php?id=' . $atts['lesson_id'] . '&link=' . urlencode($resource_url);
            
            // Check if resource is favorited in wp_jph_lesson_favorites
            $is_favorited = false;
            $favorite_id = 0;
            if ($user_id) {
                $favorite_check = $wpdb->get_row($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}jph_lesson_favorites WHERE user_id = %d AND url = %s",
                    $user_id,
                    $final_resource_url
                ));
                
                if ($favorite_check) {
                    $is_favorited = true;
                    $favorite_id = $favorite_check->id;
                }
            }
            
            $return .= '<li class="alm-resource-item" style="margin-bottom: 12px; display: flex; align-items: stretch; gap: 8px;">';
            
            // Main clickable card (reduced width)
            $return .= '<a href="' . esc_url($final_resource_url) . '" target="_blank" style="flex: 1; background: white; border-radius: 8px; padding: 16px; border-left: 4px solid #10b981; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; cursor: pointer; transition: all 0.2s ease;">';
            $return .= '<span style="color: #10b981; flex-shrink: 0;">' . $icon . '</span>';
            $return .= '<div style="flex-grow: 1;">';
            $return .= '<span style="color: #111827; font-weight: 600; display: block; font-size: 15px; margin-bottom: 4px;">' . esc_html($resource_name) . '</span>';
            if (!empty($resource_label)) {
                $return .= '<span style="display: block; font-size: 13px; color: #6b7280; margin-top: 2px;">' . esc_html($resource_label) . '</span>';
            }
            $return .= '</div>';
            $return .= '</a>';
            
            // Favorite button (outside the card) - just icon, no bounding box
            // Title format: "Lesson Title (Resource Type)"
            $lesson_title = stripslashes($lesson->lesson_title);
            $final_resource_name = $lesson_title . ' (' . $resource_name . ')';
            
            // Determine resource_type for database
            $resource_type_for_db = $k;
            if ($k === 'sheet_music' || $k === 'pdf') {
                $resource_type_for_db = 'pdf';
            }
            
            // Extract resource_link from resource_url (file path) - preserve exactly as stored
            $resource_link = $resource_url;
            
            $return .= '<button class="alm-resource-favorite-btn" data-resource-url="' . esc_attr($final_resource_url) . '" data-resource-name="' . esc_attr($final_resource_name) . '" data-resource-link="' . esc_attr($resource_link) . '" data-resource-type="' . esc_attr($resource_type_for_db) . '" data-favorite-id="' . $favorite_id . '" onclick="almToggleResourceFavorite(event, this); return false;">';
            
            if ($is_favorited) {
                $return .= '<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ef4444"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>';
            } else {
                $return .= '<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#9ca3af"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>';
            }
            $return .= '</button>';
            
            $return .= '</li>';
        }
        
        $return .= '</ul>';
        $return .= '</div>'; // End alm-card-content
        $return .= '</div>'; // End alm-sidebar-card
        
        // Add CSS for resource favorite button
        $return .= '<style>
        .alm-resource-favorite-btn {
            background: none !important;
            border: none !important;
            padding: 4px !important;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .alm-resource-favorite-btn:hover svg {
            transform: scale(1.1);
        }
        .alm-resource-favorite-btn.favorited svg path {
            stroke: #ef4444 !important;
        }
        </style>';
        
        // Add JavaScript for resource favorites
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('alm_resource_favorite_nonce');
        
        $return .= '<script>
        function almToggleResourceFavorite(event, btn) {
            event.preventDefault();
            event.stopPropagation();
            
            var $btn = jQuery(btn);
            var resourceUrl = $btn.data("resource-url");
            var resourceName = $btn.data("resource-name");
            var resourceLink = $btn.data("resource-link");
            var resourceType = $btn.data("resource-type");
            
            console.log("ALM: Toggle resource favorite", {
                url: resourceUrl,
                name: resourceName,
                link: resourceLink,
                type: resourceType
            });
            
            // Toggle UI immediately
            var isFavorited = $btn.find("path").attr("stroke") === "#ef4444";
            
            if (isFavorited) {
                // Unfavorite
                $btn.find("svg path").attr("stroke", "#9ca3af");
                $btn.removeClass("favorited");
                
                var postData = {
                    action: "alm_toggle_resource_favorite",
                    resource_url: resourceUrl,
                    is_favorite: 0,
                    nonce: "' . $nonce . '"
                };
                console.log("ALM: Unfavorite request", postData);
                
                // AJAX call to remove favorite
                jQuery.post("' . $ajax_url . '", postData, function(response) {
                    console.log("ALM: Unfavorite response", response);
                    if (!response.success) {
                        // Revert on error
                        $btn.find("svg path").attr("stroke", "#ef4444");
                        $btn.addClass("favorited");
                        alert("Error: " + response.data);
                    }
                }).fail(function(xhr, status, error) {
                    console.error("ALM: Unfavorite failed", xhr, status, error);
                    // Revert on error
                    $btn.find("svg path").attr("stroke", "#ef4444");
                    $btn.addClass("favorited");
                    alert("Error removing favorite");
                });
            } else {
                // Favorite
                $btn.find("svg path").attr("stroke", "#ef4444");
                $btn.addClass("favorited");
                
                var postData = {
                    action: "alm_toggle_resource_favorite",
                    resource_url: resourceUrl,
                    resource_name: resourceName,
                    resource_link: resourceLink,
                    resource_type: resourceType,
                    is_favorite: 1,
                    nonce: "' . $nonce . '"
                };
                console.log("ALM: Favorite request", postData);
                
                // AJAX call to add favorite
                jQuery.ajax({
                    url: "' . $ajax_url . '",
                    type: "POST",
                    dataType: "json",
                    data: postData,
                    success: function(response) {
                        console.log("ALM: Favorite success response", response);
                        if (response && response.success && response.data && response.data.id) {
                            $btn.data("favorite-id", response.data.id);
                            console.log("ALM: Favorite added successfully, ID:", response.data.id);
                        } else {
                            console.error("ALM: Favorite response missing success/data", response);
                            // Revert on error
                            $btn.find("svg path").attr("stroke", "#9ca3af");
                            $btn.removeClass("favorited");
                            var errorMsg = (response && response.data) ? response.data : "Error adding favorite";
                            alert("Error: " + errorMsg);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("ALM: Favorite AJAX error", {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            responseJSON: xhr.responseJSON,
                            error: error,
                            allResponseHeaders: xhr.getAllResponseHeaders()
                        });
                        
                        // Revert on error
                        $btn.find("svg path").attr("stroke", "#9ca3af");
                        $btn.removeClass("favorited");
                        
                        var errorMsg = "Error adding favorite";
                        
                        // Check for WordPress "0" response (action not found)
                        if (xhr.responseText === "0" || xhr.responseText.trim() === "0") {
                            errorMsg = "AJAX action not found. The action \\"alm_toggle_resource_favorite\\" may not be registered. Check server logs.";
                            console.error("ALM: WordPress returned \\"0\\" - action may not be registered or function not called");
                        } else if (xhr.responseJSON && xhr.responseJSON.data) {
                            errorMsg = xhr.responseJSON.data;
                        } else if (xhr.responseText && xhr.responseText !== "0") {
                            try {
                                var json = jQuery.parseJSON(xhr.responseText);
                                if (json && json.data) {
                                    errorMsg = json.data;
                                }
                            } catch(e) {
                                console.error("ALM: Could not parse error response", xhr.responseText);
                                errorMsg = "HTTP " + xhr.status + ": " + xhr.statusText + " - Response: " + xhr.responseText.substring(0, 200);
                            }
                        }
                        
                        console.error("ALM: Final error message:", errorMsg);
                        alert(errorMsg);
                    }
                });
            }
        }
        </script>';
        
        return $return;
    }
    
    public function lesson_progress_shortcode($atts) {
        $atts = shortcode_atts(array(
            'lesson_id' => '',
            'format' => 'percent'
        ), $atts);
        
        if (empty($atts['lesson_id'])) {
            return '<p style="color: red;">Error: lesson_id is required</p>';
        }
        
        // SECURITY: Verify user has access to this lesson
        global $wpdb;
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT membership_level FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
            $atts['lesson_id']
        ));
        
        if (!$lesson) {
            return '<p style="color: red;">Lesson not found.</p>';
        }
        
        // Note: This shortcode doesn't receive user_membership_level parameter
        // It should only be used within contexts that already have access control
        // For now, we'll allow it but recommend adding proper access control
        
        // Use existing function if available, otherwise implement our own
        if (function_exists('je_return_lesson_progress_percentage')) {
            return je_return_lesson_progress_percentage($atts['lesson_id'], $atts['format']);
        }
        
        // Fallback: Calculate progress ourselves
        global $wpdb;
        $user_id = get_current_user_id();
        
        // Get chapters from NEW ALM table
        $chapters = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_chapters WHERE lesson_id = %d ORDER BY menu_order ASC",
            $atts['lesson_id']
        ));
        
        $total_chapters = count($chapters);
        $completed_chapters = 0;
        
        foreach ($chapters as $chapter) {
            if (function_exists('je_is_chapter_complete') && je_is_chapter_complete($chapter->ID)) {
                $completed_chapters++;
            }
        }
        
        if ($atts['format'] == 'percent') {
            return percent_complete($completed_chapters, $total_chapters);
        } else {
            return "You've Completed: $completed_chapters / $total_chapters chapters";
        }
    }
    
    public function mark_complete_shortcode($atts) {
        $atts = shortcode_atts(array(
            'lesson_id' => '',
            'chapter_id' => '',
            'type' => 'chapter' // 'chapter' or 'lesson'
        ), $atts);
        
        global $wpdb, $user_id;
        
        // Get user ID
        $user_id = get_current_user_id();
        if (!$user_id) {
            return '<p style="color: red;">You must be logged in to mark items complete.</p>';
        }
        
        // Get current chapter and lesson IDs
        $current_lesson_id = !empty($atts['lesson_id']) ? intval($atts['lesson_id']) : 0;
        $current_chapter_id = !empty($atts['chapter_id']) ? intval($atts['chapter_id']) : 0;
        
        // SECURITY: If lesson_id is provided, verify user has access to this lesson
        if ($current_lesson_id > 0) {
            $lesson = $wpdb->get_row($wpdb->prepare(
                "SELECT membership_level FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
                $current_lesson_id
            ));
            
            if (!$lesson) {
                return '<p style="color: red;">Lesson not found.</p>';
            }
            
            // Note: This shortcode doesn't receive user_membership_level parameter
            // It should only be used within contexts that already have access control
            // For now, we'll rely on the AJAX handlers to provide the security
        }
        
        // Get collection_id from lesson if not provided
        $collection_id = 0;
        if ($current_lesson_id > 0) {
            $lesson = $wpdb->get_row($wpdb->prepare(
                "SELECT collection_id FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
                $current_lesson_id
            ));
            if ($lesson) {
                $collection_id = $lesson->collection_id;
            }
        }
        
        // Get current chapter slug from URL
        $current_chapter_slug = isset($_GET['c']) ? sanitize_text_field($_GET['c']) : '';
        
        // Build the form
        $return = '';
        
        // Chapter mark complete - AJAX version
        if ($atts['type'] == 'chapter' && $current_chapter_id > 0) {
            // Check if chapter is already complete
            $is_complete = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM academy_completed_chapters WHERE chapter_id = %d AND user_id = %d AND deleted_at IS NULL",
                $current_chapter_id,
                $user_id
            ));
            
            $return .= '<button class="alm-completion-btn' . ($is_complete ? ' restart' : '') . '" data-action="' . ($is_complete ? 'mark_chapter_incomplete' : 'mark_chapter_complete') . '" data-lesson-id="' . $current_lesson_id . '" data-chapter-id="' . $current_chapter_id . '" data-collection-id="' . $collection_id . '" style="background: ' . ($is_complete ? '#6c757d' : '#239B90') . '; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; width: 100%; justify-content: center;">';
            
            if ($is_complete) {
                $return .= '<svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg> ';
                $return .= 'Restart Chapter';
            } else {
                $return .= '<svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> ';
                $return .= 'Mark Complete';
            }
            
            $return .= '</button>';
        }
        
        // Lesson mark complete - AJAX version
        if ($atts['type'] == 'lesson' && $current_lesson_id > 0) {
            // Check if lesson is already complete
            $is_complete = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM academy_completed_lessons WHERE lesson_id = %d AND user_id = %d AND deleted_at IS NULL",
                $current_lesson_id,
                $user_id
            ));
            
            $return .= '<button class="alm-completion-btn' . ($is_complete ? ' restart' : '') . '" data-action="' . ($is_complete ? 'mark_lesson_incomplete' : 'mark_lesson_complete') . '" data-lesson-id="' . $current_lesson_id . '" data-collection-id="' . $collection_id . '" style="background: ' . ($is_complete ? '#6c757d' : '#239B90') . '; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; width: 100%; justify-content: center;">';
            
            if ($is_complete) {
                $return .= '<svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg> ';
                $return .= 'Restart Lesson';
            } else {
                $return .= '<svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> ';
                $return .= 'Mark Lesson Complete';
            }
            
            $return .= '</button>';
        }
        
        return $return;
    }
    
    /**
     * AJAX Handler: Save Lesson Notes
     */
    public function ajax_save_lesson_notes() {
        check_ajax_referer('alm_notes_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        $lesson_id = intval($_POST['lesson_id']);
        $post_id = get_the_ID(); // Get current post ID
        $user_notes = wp_kses_post($_POST['user_notes']);
        
        if (!$user_id || (!$lesson_id && !$post_id)) {
            wp_send_json_error(array('message' => 'Invalid user, lesson ID, or post ID'));
            return;
        }
        
        // Verify lesson exists if lesson_id is provided
        if ($lesson_id) {
            global $wpdb;
            $lesson = $wpdb->get_row($wpdb->prepare(
                "SELECT ID FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
                $lesson_id
            ));
            
            if (!$lesson) {
                wp_send_json_error(array('message' => 'Lesson not found.'));
                return;
            }
        }
        
        global $wpdb;
        
        // Check if note already exists (by lesson_id if available, otherwise by post_id)
        $existing_note = null;
        if ($lesson_id) {
            $existing_note = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}alm_user_notes WHERE user_id = %d AND lesson_id = %d",
                $user_id,
                $lesson_id
            ));
        } else {
            $existing_note = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}alm_user_notes WHERE user_id = %d AND post_id = %d AND lesson_id IS NULL",
                $user_id,
                $post_id
            ));
        }
        
        if ($existing_note) {
            // Update existing note
            $result = $wpdb->update(
                $wpdb->prefix . 'alm_user_notes',
                array(
                    'notes_content' => $user_notes,
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'user_id' => $user_id,
                    'lesson_id' => $lesson_id
                ),
                array('%s', '%s'),
                array('%d', '%d')
            );
        } else {
            // Insert new note
            $result = $wpdb->insert(
                $wpdb->prefix . 'alm_user_notes',
                array(
                    'user_id' => $user_id,
                    'post_id' => $post_id,
                    'lesson_id' => $lesson_id ? $lesson_id : null,
                    'notes_content' => $user_notes,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%d', '%d', '%d', '%s', '%s', '%s')
            );
        }
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Notes saved successfully'));
        } else {
            wp_send_json_error(array('message' => 'Database error occurred: ' . $wpdb->last_error));
        }
    }
    
    /**
     * AJAX Handler: Toggle Resource Favorite
     */
    public function ajax_toggle_resource_favorite() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
            return;
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'alm_resource_favorite_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!isset($_POST['resource_url'])) {
            wp_send_json_error('Resource URL is required');
            return;
        }
        
        global $wpdb;
        $resource_url = sanitize_text_field($_POST['resource_url']);
        $is_favorite = isset($_POST['is_favorite']) ? intval($_POST['is_favorite']) : 0;
        $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        
        if ($is_favorite) {
            // Add favorite
            $resource_name = isset($_POST['resource_name']) ? sanitize_text_field($_POST['resource_name']) : 'Resource';
            // Preserve resource_link - it's a file path, so we need to keep / characters
            $resource_link = isset($_POST['resource_link']) ? $_POST['resource_link'] : '';
            if (!empty($resource_link)) {
                // Normalize path separators and remove null bytes for safety
                $resource_link = str_replace('\\', '/', $resource_link);
                $resource_link = str_replace("\0", '', $resource_link);
                $resource_link = trim($resource_link);
            }
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
            
            // Insert new favorite
            $insert_data = array(
                'user_id' => $user_id,
                'title' => $resource_name,
                'url' => $resource_url,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );
            $insert_format = array('%d', '%s', '%s', '%s', '%s');
            
            if (!empty($resource_link)) {
                $insert_data['resource_link'] = $resource_link;
                $insert_format[] = '%s';
            }
            
            if (!empty($resource_type)) {
                $insert_data['resource_type'] = $resource_type;
                $insert_format[] = '%s';
            }
            
            $insert_data['category'] = 'lesson';
            $insert_format[] = '%s';
            
            $result = $wpdb->insert($table_name, $insert_data, $insert_format);
            
            if ($result !== false) {
                $favorite_id = $wpdb->insert_id;
                wp_send_json_success(array('id' => $favorite_id, 'message' => 'Added to favorites'));
            } else {
                wp_send_json_error('Database insert failed: ' . $wpdb->last_error);
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
    }
    
    /**
     * AJAX Handler: Mark Chapter Complete
     */
    public function ajax_mark_chapter_complete() {
        check_ajax_referer('alm_completion_nonce', 'nonce');
        
        global $wpdb;
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            wp_send_json_error(array('message' => 'You must be logged in.'));
        }
        
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        $collection_id = isset($_POST['collection_id']) ? intval($_POST['collection_id']) : 0;
        
        if (!$chapter_id || !$lesson_id) {
            wp_send_json_error(array('message' => 'Invalid parameters.'));
        }
        
        // Verify lesson exists
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            wp_send_json_error(array('message' => 'Lesson not found.'));
        }
        
        // Check if already marked complete
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM academy_completed_chapters WHERE chapter_id = %d AND user_id = %d AND deleted_at IS NULL",
            $chapter_id,
            $user_id
        ));
        
        if (!empty($existing)) {
            wp_send_json_success(array('message' => 'Chapter already marked complete.', 'already_complete' => true));
        }
        
        // Insert completion record
        $result = $wpdb->insert(
            'academy_completed_chapters',
            array(
                'user_id' => $user_id,
                'chapter_id' => $chapter_id,
                'lesson_id' => $lesson_id,
                'course_id' => $collection_id,
                'datetime' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%d', '%s')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
        }
        
        // Auto-complete lesson if it's the only chapter
        $chapter_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}alm_chapters WHERE lesson_id = %d",
            $lesson_id
        ));
        
        if ($chapter_count == 1) {
            $existing_lesson = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM academy_completed_lessons WHERE lesson_id = %d AND user_id = %d AND deleted_at IS NULL",
                $lesson_id,
                $user_id
            ));
            
            if (empty($existing_lesson)) {
                $wpdb->insert(
                    'academy_completed_lessons',
                    array(
                        'user_id' => $user_id,
                        'lesson_id' => $lesson_id,
                        'course_id' => $collection_id,
                        'datetime' => current_time('mysql')
                    ),
                    array('%d', '%d', '%d', '%s')
                );
            }
        }
        
        wp_send_json_success(array('message' => 'Chapter marked as complete!'));
    }
    
    /**
     * AJAX Handler: Mark Chapter Incomplete (Restart)
     */
    public function ajax_mark_chapter_incomplete() {
        check_ajax_referer('alm_completion_nonce', 'nonce');
        
        global $wpdb;
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            wp_send_json_error(array('message' => 'You must be logged in.'));
        }
        
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        
        if (!$chapter_id) {
            wp_send_json_error(array('message' => 'Invalid parameters.'));
        }
        
        // Get lesson_id from chapter
        $chapter = $wpdb->get_row($wpdb->prepare(
            "SELECT lesson_id FROM {$wpdb->prefix}alm_chapters WHERE ID = %d",
            $chapter_id
        ));
        
        if (!$chapter) {
            wp_send_json_error(array('message' => 'Chapter not found.'));
        }
        
        $lesson_id = $chapter->lesson_id;
        
        $result = $wpdb->update(
            'academy_completed_chapters',
            array('deleted_at' => current_time('mysql')),
            array(
                'chapter_id' => $chapter_id,
                'user_id' => $user_id
            ),
            array('%s'),
            array('%d', '%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
        }
        
        wp_send_json_success(array('message' => 'Chapter restarted!'));
    }
    
    /**
     * AJAX Handler: Mark Lesson Complete
     */
    public function ajax_mark_lesson_complete() {
        check_ajax_referer('alm_completion_nonce', 'nonce');
        
        global $wpdb;
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            wp_send_json_error(array('message' => 'You must be logged in.'));
        }
        
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        $collection_id = isset($_POST['collection_id']) ? intval($_POST['collection_id']) : 0;
        
        if (!$lesson_id) {
            wp_send_json_error(array('message' => 'Invalid parameters.'));
        }
        
        // Verify lesson exists
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            wp_send_json_error(array('message' => 'Lesson not found.'));
        }
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM academy_completed_lessons WHERE lesson_id = %d AND user_id = %d AND deleted_at IS NULL",
            $lesson_id,
            $user_id
        ));
        
        if (!empty($existing)) {
            wp_send_json_success(array('message' => 'Lesson already marked complete.', 'already_complete' => true));
        }
        
        $result = $wpdb->insert(
            'academy_completed_lessons',
            array(
                'user_id' => $user_id,
                'lesson_id' => $lesson_id,
                'course_id' => $collection_id,
                'datetime' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%s')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
        }
        
        wp_send_json_success(array('message' => 'Lesson marked as complete!'));
    }
    
    /**
     * AJAX Handler: Mark Lesson Incomplete (Restart)
     */
    public function ajax_mark_lesson_incomplete() {
        check_ajax_referer('alm_completion_nonce', 'nonce');
        
        global $wpdb;
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            wp_send_json_error(array('message' => 'You must be logged in.'));
        }
        
        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        
        if (!$lesson_id) {
            wp_send_json_error(array('message' => 'Invalid parameters.'));
        }
        
        // Verify lesson exists
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            wp_send_json_error(array('message' => 'Lesson not found.'));
        }
        
        // Mark lesson incomplete
        $wpdb->update(
            'academy_completed_lessons',
            array('deleted_at' => current_time('mysql')),
            array(
                'lesson_id' => $lesson_id,
                'user_id' => $user_id
            ),
            array('%s'),
            array('%d', '%d')
        );
        
        // Also mark all chapters incomplete
        $chapter_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}alm_chapters WHERE lesson_id = %d",
            $lesson_id
        ));
        
        if (!empty($chapter_ids)) {
            foreach ($chapter_ids as $chapter_id) {
                $wpdb->update(
                    'academy_completed_chapters',
                    array('deleted_at' => current_time('mysql')),
                    array(
                        'chapter_id' => $chapter_id,
                        'user_id' => $user_id
                    ),
                    array('%s'),
                    array('%d', '%d')
                );
            }
        }
        
        wp_send_json_success(array('message' => 'Lesson restarted!'));
    }
    
    /**
     * Collection Complete Shortcode
     * Lists all lessons in a collection with progress tracking
     */
    public function collection_complete_shortcode($atts) {
        $atts = shortcode_atts(array(
            'collection_id' => ''
        ), $atts);
        
        // Auto-detect collection_id from current post if not provided
        if (empty($atts['collection_id'])) {
            $post_id = get_the_ID();
            if ($post_id) {
                // Try ACF field first
                if (function_exists('get_field')) {
                    $collection_id = get_field('alm_collection_id', $post_id);
                }
                
                // Fallback to post meta
                if (empty($collection_id)) {
                    $collection_id = get_post_meta($post_id, 'course_id', true);
                }
                
                if (!empty($collection_id)) {
                    $atts['collection_id'] = $collection_id;
                }
            }
        }
        
        if (empty($atts['collection_id'])) {
            return '<p style="color: red;">Error: collection_id is required</p>';
        }
        
        global $wpdb;
        
        // Get collection data
        $collection = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_collections WHERE ID = %d",
            $atts['collection_id']
        ));
        
        if (!$collection) {
            return '<p style="color: red;">Error: Collection not found</p>';
        }
        
        // Get lessons in this collection
        $lessons = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}alm_lessons WHERE collection_id = %d ORDER BY menu_order ASC, lesson_title ASC",
            $atts['collection_id']
        ));
        
        $return = '<div class="alm-course-listing">';
        // Minimal inline style for favorite indicator
        $return .= '<style>.alm-fav-indicator{display:inline-flex;align-items:center;gap:6px;color:#f59e0b;font-weight:600;font-size:12px;margin-top:6px}.alm-fav-indicator .dashicons{color:#f59e0b}</style>';
        
        if (empty($lessons)) {
            $return .= '<p class="alm-no-lessons">No lessons in this course yet.</p>';
            $return .= '</div>';
            return $return;
        }
        
        // Course Stats with Collection Progress
        $total_lessons = count($lessons);
        $total_duration = 0; // Track total duration in seconds
        $completed_lessons = array();
        $total_lessons_progress = 0;
        $user_id = get_current_user_id();
        
        // Prepare favorites cache for current user (from JPH lesson favorites table)
        $favorites_titles = array();
        if ($user_id) {
            $jph_table = $wpdb->prefix . 'jph_lesson_favorites';
            // Ensure table exists before querying
            $jph_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $jph_table));
            if (!empty($jph_exists)) {
                $rows = $wpdb->get_results($wpdb->prepare(
                    "SELECT title FROM {$jph_table} WHERE user_id = %d",
                    $user_id
                ));
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        if (!empty($row->title)) {
                            $favorites_titles[strtolower(trim($row->title))] = true;
                        }
                    }
                }
            }
        }

        // Calculate progress for each lesson by checking academy_completed_chapters table
        foreach ($lessons as $lesson) {
            $progress = 0;
            
            // Add lesson duration to total
            $total_duration += intval($lesson->duration);
            
            // Get all chapters for this lesson
            $chapters = $wpdb->get_results($wpdb->prepare(
                "SELECT ID FROM {$wpdb->prefix}alm_chapters WHERE lesson_id = %d",
                $lesson->ID
            ));
            
            if (!empty($chapters) && $user_id) {
                $total_chapters = count($chapters);
                $completed_chapters = 0;
                
                // Count completed chapters from academy_completed_chapters table
                foreach ($chapters as $chapter) {
                    $completed = $wpdb->get_var($wpdb->prepare(
                        "SELECT ID FROM academy_completed_chapters WHERE chapter_id = %d AND user_id = %d AND deleted_at IS NULL",
                        $chapter->ID,
                        $user_id
                    ));
                    if (!empty($completed)) {
                        $completed_chapters++;
                    }
                }
                
                $progress = $total_chapters > 0 ? ($completed_chapters / $total_chapters) * 100 : 0;
            }
            
            $total_lessons_progress += $progress;
            if ($progress == 100) {
                $completed_lessons[] = $lesson->ID;
            }
        }
        
        $completed_count = count($completed_lessons);
        $collection_progress = $total_lessons > 0 ? round($total_lessons_progress / $total_lessons) : 0;
        
        // Get all collections for dropdown (including membership_level)
        $all_collections = $wpdb->get_results(
            "SELECT ID, collection_title, membership_level FROM {$wpdb->prefix}alm_collections ORDER BY membership_level ASC, collection_title ASC"
        );
        
        // NEW HERO SECTION
        $return .= '<div class="alm-collection-hero">';
        $return .= '<div class="alm-hero-content">';
        
        // Compute membership badge text
        $membership_level = isset($collection->membership_level) ? intval($collection->membership_level) : 2;
        
        if (!$this->ensure_alm_settings_loaded()) {
            $membership_name = 'Unknown';
        } else {
            $membership_levels = ALM_Admin_Settings::get_membership_levels();
            $membership_name = 'Unknown';
            foreach ($membership_levels as $level_key => $level_data) {
                if ($level_data['numeric'] == $membership_level) {
                    $membership_name = $level_data['name'];
                    break;
                }
            }
        }

        // Top row: badges left, dropdown right - Mobile: dropdown on top
        $return .= '<style>
        @media screen and (max-width: 768px) {
            .alm-hero-top {
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 16px !important;
                justify-content: flex-start !important;
            }
            .alm-hero-collection-card {
                order: 1 !important;
                width: 100% !important;
                max-width: 100% !important;
                flex-shrink: 0 !important;
            }
            .alm-hero-top > div:last-child {
                order: 2 !important;
                width: 100% !important;
                flex-shrink: 0 !important;
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 8px !important;
                align-items: center !important;
            }
            .alm-collection-badge,
            .alm-membership-badge {
                font-size: 11px !important;
                padding: 6px 12px !important;
                margin-bottom: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 6px !important;
            }
        }
        </style>';
        $return .= '<div class="alm-hero-top" style="display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:24px;">';

        // Dropdown first (will appear first on mobile via CSS order)
        if (!empty($all_collections) && count($all_collections) > 1) {
            $return .= '<div class="alm-hero-collection-card">';
            $return .= '<p class="alm-hero-collection-label">Browse Other Collections</p>';
            $return .= '<select class="alm-hero-collection-dropdown" onchange="if(this.value) window.location.href = this.value;">';
            $return .= '<option value="">Select a collection...</option>';

            if (!$this->ensure_alm_settings_loaded()) {
                $membership_levels = array();
            } else {
                $membership_levels = ALM_Admin_Settings::get_membership_levels();
            }
            $current_level = null;
            foreach ($all_collections as $coll) {
                $coll_post_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->prefix}alm_collections WHERE ID = %d",
                    $coll->ID
                ));
                if ($coll_post_id) {
                    $coll_url = get_permalink($coll_post_id);
                    $selected = ($coll->ID == $atts['collection_id']) ? 'selected' : '';
                    $membership_level = intval($coll->membership_level);
                    $level_name = 'Unknown';
                    foreach ($membership_levels as $level_key => $level_data) {
                        if ($level_data['numeric'] == $membership_level) { $level_name = $level_data['name']; break; }
                    }
                    if ($current_level !== $membership_level) {
                        if ($current_level !== null) { $return .= '</optgroup>'; }
                        $return .= '<optgroup label="' . esc_attr($level_name) . '">';
                        $current_level = $membership_level;
                    }
                    $return .= '<option value="' . esc_url($coll_url) . '" ' . $selected . '>' . esc_html(stripslashes($coll->collection_title)) . '</option>';
                }
            }
            if ($current_level !== null) { $return .= '</optgroup>'; }
            $return .= '</select>';
            $return .= '</div>';
        } else {
            $return .= '<div></div>';
        }

        // Badges second (will appear second on mobile via CSS order)
        $return .= '<div style="display:flex; align-items:center; gap:8px;">';
        $return .= '<div class="alm-collection-badge">';
        $return .= '<svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" clip-rule="evenodd"/></svg>';
        $return .= '<span>Lesson Collection</span>';
        $return .= '</div>';
        $return .= '<div class="alm-membership-badge"><span>' . esc_html($membership_name) . '</span></div>';
        $return .= '</div>';

        $return .= '</div>'; // end top row
        
        // Title and Description Container (centered with background)
        $return .= '<div class="alm-hero-text-container">';
        $return .= '<h1 class="alm-hero-title">' . esc_html(stripslashes($collection->collection_title)) . '</h1>';
        
        // Description
        if (!empty($collection->collection_description)) {
            $return .= '<div class="alm-hero-description">' . nl2br(esc_html(stripslashes($collection->collection_description))) . '</div>';
        }
        $return .= '</div>';
        
        // Calculate formatted duration in human-readable format
        $formatted_duration = self::format_duration_human_readable($total_duration);
        
        // Hero Stats (Large Cards)
        $return .= '<div class="alm-hero-stats">';
        $return .= '<div class="alm-hero-stat-card">';
        $return .= '<div class="alm-hero-stat-icon lessons">';
        $return .= '<svg width="28" height="28" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>';
        $return .= '</div>';
        $return .= '<div class="alm-hero-stat-content">';
        $return .= '<div class="alm-hero-stat-number">' . $total_lessons . ' Lessons</div>';
        $return .= '<div class="alm-hero-stat-label" style="color: rgba(255, 255, 255, 0.8); font-weight: 500;">' . $formatted_duration . ' Total</div>';
        $return .= '</div>';
        $return .= '</div>';
        
        $return .= '<div class="alm-hero-stat-card primary">';
        $return .= '<div class="alm-hero-stat-icon progress">';
        $return .= '<svg width="28" height="28" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>';
        $return .= '</div>';
        $return .= '<div class="alm-hero-stat-content">';
        $return .= '<div class="alm-hero-stat-number">' . $collection_progress . '<span class="percent">%</span></div>';
        $return .= '<div class="alm-hero-stat-label">Complete</div>';
        // Progress bar for hero
        $return .= '<div class="alm-hero-progress-bar">';
        $return .= '<div class="alm-hero-progress-fill" style="width: ' . $collection_progress . '%"></div>';
        $return .= '</div>';
        $return .= '</div>';
        $return .= '</div>';
        
        if ($completed_count > 0) {
            $return .= '<div class="alm-hero-stat-card success">';
            $return .= '<div class="alm-hero-stat-icon completed">';
            $return .= '<svg width="28" height="28" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
            $return .= '</div>';
            $return .= '<div class="alm-hero-stat-content">';
            $return .= '<div class="alm-hero-stat-number">' . $completed_count . '</div>';
            $return .= '<div class="alm-hero-stat-label">Completed</div>';
            $return .= '</div>';
            $return .= '</div>';
        }
        
        $return .= '</div>'; // End alm-hero-stats
        $return .= '</div>'; // End alm-hero-content
        $return .= '</div>'; // End alm-collection-hero
        
        // Lessons Section Header
        $return .= '<div class="alm-lessons-section-header">';
        $return .= '<h2 class="alm-lessons-section-title">Lessons in This Collection</h2>';
        $return .= '<p class="alm-lessons-section-subtitle">Start with any lesson below and track your progress</p>';
        $return .= '</div>';
        
        // Lessons Grid
        $return .= '<div class="alm-lessons-grid-course">';
        
        $lesson_number = 0;
        foreach ($lessons as $lesson) {
            $lesson_number++;
            $is_completed = in_array($lesson->ID, $completed_lessons);
            $progress = 0;
            
            // Calculate progress for this specific lesson
            $chapters = $wpdb->get_results($wpdb->prepare(
                "SELECT ID FROM {$wpdb->prefix}alm_chapters WHERE lesson_id = %d",
                $lesson->ID
            ));
            
            if (!empty($chapters) && $user_id) {
                $total_chapters = count($chapters);
                $completed_chapters = 0;
                
                foreach ($chapters as $chapter) {
                    $completed = $wpdb->get_var($wpdb->prepare(
                        "SELECT ID FROM academy_completed_chapters WHERE chapter_id = %d AND user_id = %d AND deleted_at IS NULL",
                        $chapter->ID,
                        $user_id
                    ));
                    if (!empty($completed)) {
                        $completed_chapters++;
                    }
                }
                
                $progress = $total_chapters > 0 ? round(($completed_chapters / $total_chapters) * 100) : 0;
            }
            
            // Detect if lesson is favorited for current user
            $is_favorited = false;
            if ($user_id && !empty($favorites_titles)) {
                // Check by lesson title (case-insensitive)
                $ltitle = strtolower(trim(stripslashes($lesson->lesson_title)));
                if (isset($favorites_titles[$ltitle])) {
                    $is_favorited = true;
                }
            }

            // Detect if lesson has resources
            $has_resources = false;
            if (!empty($lesson->resources)) {
                $parsed_resources = ALM_Helpers::format_serialized_resources($lesson->resources);
                if (!empty($parsed_resources)) {
                    $has_resources = true;
                }
            }

            // Card wrapper (position relative to allow bottom-right badges)
            $return .= '<div class="alm-lesson-card-course' . ($is_completed ? ' alm-completed' : '') . '" style="position: relative;">';
            
            // Link to lesson if post exists
            if ($lesson->post_id) {
                $lesson_url = get_permalink($lesson->post_id);
                $return .= '<a href="' . esc_url($lesson_url) . '" class="alm-lesson-card-link">';
                $return .= '<div class="alm-lesson-card-content" style="position: relative;">';
            } else {
                $return .= '<div class="alm-lesson-card-content" style="position: relative;">';
            }
            
            // Favorite button - positioned top right
            $lesson_title = esc_attr(stripslashes($lesson->lesson_title));
            $lesson_description = !empty($lesson->lesson_description) ? esc_attr(stripslashes($lesson->lesson_description)) : '';
            $lesson_url_for_fav = $lesson->post_id ? esc_url(get_permalink($lesson->post_id)) : '';
            
            $return .= '<button type="button" class="alm-favorite-btn-collection' . ($is_favorited ? ' is-favorited' : '') . '" 
                data-title="' . $lesson_title . '" 
                data-url="' . $lesson_url_for_fav . '" 
                data-description="' . $lesson_description . '"
                onclick="almToggleCollectionFavorite(event, this); return false;"
                style="position: absolute; top: 20px; right: 20px; background: ' . ($is_favorited ? 'rgba(240, 78, 35, 0.1)' : 'transparent') . '; border: none; cursor: pointer; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 10; transition: all 0.2s ease; width: 36px; height: 36px; opacity: ' . ($is_favorited ? '1' : '0.6') . ';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="' . ($is_favorited ? '#f04e23' : 'none') . '" stroke="' . ($is_favorited ? '#f04e23' : '#6b7280') . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </button>';
            
            // Lesson number and title
            $return .= '<div class="alm-lesson-number">' . __('Lesson', 'academy-lesson-manager') . ' ' . $lesson_number . '</div>';
            $return .= '<h3 class="alm-lesson-title" style="padding-right: 64px;">' . esc_html(stripslashes($lesson->lesson_title)) . '</h3>';
            
            // Lesson description
            if (!empty($lesson->lesson_description)) {
                $return .= '<div class="alm-lesson-description-course">' . esc_html(stripslashes($lesson->lesson_description)) . '</div>';
            }
            
            // Progress bar - always show progress
            $return .= '<div class="alm-progress-bar-course">';
            $return .= '<div class="alm-progress-fill" style="width: ' . $progress . '%;"></div>';
            $return .= '</div>';
            $return .= '<div class="alm-progress-text">' . $progress . '% ' . __('Complete', 'academy-lesson-manager') . '</div>';
            
            // Completion badge
            if ($is_completed) {
                $return .= '<div class="alm-completion-badge-course">';
                $return .= '<span class="dashicons dashicons-yes"></span> ' . __('Completed', 'academy-lesson-manager');
                $return .= '</div>';
            }
            
            // Bottom section with duration and resource icon - push to bottom with margin-top: auto
            $return .= '<div class="alm-lesson-card-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 15px;">';
            
            // Duration on the left
            if ($lesson->duration > 0) {
                $duration_hours = floor($lesson->duration / 3600);
                $duration_minutes = floor(($lesson->duration % 3600) / 60);
                $duration_str = '';
                if ($duration_hours > 0) {
                    $duration_str .= $duration_hours . 'h ';
                }
                if ($duration_minutes > 0) {
                    $duration_str .= $duration_minutes . 'm';
                }
                if ($duration_str) {
                    $return .= '<div class="alm-lesson-duration" style="margin-top: 0;">';
                    $return .= '<span class="dashicons dashicons-clock"></span> ';
                    $return .= esc_html(trim($duration_str));
                    $return .= '</div>';
                }
            } else {
                $return .= '<div></div>'; // Spacer for flex layout
            }
            
            // Resource badge icon on the right
            if ($has_resources) {
                $return .= '<div class="alm-lesson-resource-badge" title="Resources available" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: #f0f8f7; border: 1px solid #d4e8e5; color: #239B90; pointer-events: none;">';
                $return .= '<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" /></svg>';
                $return .= '</div>';
            }
            
            $return .= '</div>'; // Close bottom section flex container

            $return .= '</div>'; // Close alm-lesson-card-content
            
            if ($lesson->post_id) {
                $return .= '</a>'; // Close lesson link
            }
            
            $return .= '</div>'; // Close alm-lesson-card-course
        }
        
        $return .= '</div>'; // Close alm-lessons-grid-course
        $return .= '</div>'; // Close alm-course-listing
        
        // Add JavaScript for favorite functionality
        $rest_nonce = wp_create_nonce('wp_rest');
        $favorites_add_url = rest_url('aph/v1/lesson-favorites');
        $favorites_remove_url = rest_url('aph/v1/lesson-favorites/remove');
        
        $return .= '<script>
        function almToggleCollectionFavorite(event, btn) {
            event.preventDefault();
            event.stopPropagation();
            
            var isFavorited = btn.classList.contains("is-favorited");
            var title = btn.getAttribute("data-title");
            var url = btn.getAttribute("data-url");
            var description = btn.getAttribute("data-description");
            var starPath = btn.querySelector("svg path");
            
            if (!url) {
                alert("This lesson is not available");
                return;
            }
            
            var endpoint = isFavorited 
                ? "' . esc_js($favorites_remove_url) . '"
                : "' . esc_js($favorites_add_url) . '";
            
            var data = isFavorited 
                ? { title: title }
                : { title: title, url: url, description: description, category: "lesson" };
            
            // Show loading state
            btn.style.opacity = "0.5";
            btn.style.pointerEvents = "none";
            
            fetch(endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": "' . esc_js($rest_nonce) . '"
                },
                body: JSON.stringify(data),
                credentials: "same-origin"
            })
            .then(function(response){ return response.json(); })
            .then(function(result){
                if (result.success) {
                    var svg = btn.querySelector("svg");
                    if (isFavorited) {
                        btn.classList.remove("is-favorited");
                        svg.setAttribute("fill", "none");
                        svg.setAttribute("stroke", "#6b7280");
                        btn.style.opacity = "0.6";
                        btn.style.background = "transparent";
                    } else {
                        btn.classList.add("is-favorited");
                        svg.setAttribute("fill", "#f04e23");
                        svg.setAttribute("stroke", "#f04e23");
                        btn.style.opacity = "1";
                        btn.style.background = "rgba(240, 78, 35, 0.1)";
                    }
                } else {
                    alert(result.message || "Failed to update favorite");
                }
            })
            .catch(function(error){
                console.error("Favorite error:", error);
                alert("Error updating favorite");
            })
            .finally(function(){
                btn.style.pointerEvents = "auto";
            });
        }
        
        // Add hover effects to favorite buttons
        document.addEventListener("DOMContentLoaded", function() {
            var favoriteBtns = document.querySelectorAll(".alm-favorite-btn-collection");
            favoriteBtns.forEach(function(btn) {
                btn.addEventListener("mouseenter", function() {
                    if (!this.classList.contains("is-favorited")) {
                        this.style.opacity = "1";
                        this.style.background = "rgba(240, 78, 35, 0.1)";
                        this.style.transform = "scale(1.1)";
                    }
                });
                btn.addEventListener("mouseleave", function() {
                    if (!this.classList.contains("is-favorited")) {
                        this.style.opacity = "0.6";
                        this.style.background = "transparent";
                        this.style.transform = "scale(1)";
                    }
                });
            });
        });
        </script>';
        
        return $return;
    }
    
    /**
     * Favorites Management Page Shortcode
     * 
     * Displays a drag-and-drop list of user's favorite lessons
     * Allows reordering and deleting favorites
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function favorites_management_shortcode($atts) {
        // Only show to logged in users
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your favorites.', 'academy-lesson-manager') . '</p>';
        }
        
        $user_id = get_current_user_id();
        global $wpdb;
        
        // Check which favorites table exists
        $jph_table = $wpdb->prefix . 'jph_lesson_favorites';
        $jf_table = $wpdb->prefix . 'jf_favorites';
        
        $table_exists = false;
        $table_name = '';
        $table_type = '';
        
        // Try jph first
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $jph_table));
        if ($exists) {
            $table_name = $jph_table;
            $table_type = 'jph';
            $table_exists = true;
        } else {
            // Try jf
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $jf_table));
            if ($exists) {
                $table_name = $jf_table;
                $table_type = 'jf';
                $table_exists = true;
            }
        }
        
        if (!$table_exists) {
            return '<p>' . __('Favorites system not found.', 'academy-lesson-manager') . '</p>';
        }
        
        // Get user's favorites
        if ($table_type === 'jph') {
            $favorites = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
                $user_id
            ));
        } else {
            $favorites = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d AND is_active = 1 ORDER BY created_at DESC",
                $user_id
            ));
        }
        
        $return = '<div class="alm-favorites-management">';
        $return .= '<div class="alm-favorites-header">';
        $return .= '<h2>' . __('My Favorites', 'academy-lesson-manager') . '</h2>';
        $return .= '<span class="alm-favorites-count">' . count($favorites) . ' ' . __('lessons', 'academy-lesson-manager') . '</span>';
        $return .= '</div>';
        
        if (empty($favorites)) {
            $return .= '<div class="alm-favorites-empty">';
            $return .= '<svg width="64" height="64" fill="currentColor" viewBox="0 0 20 20" style="opacity: 0.3; margin-bottom: 16px;"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>';
            $return .= '<p>' . __('You haven\'t added any favorites yet.', 'academy-lesson-manager') . '</p>';
            $return .= '</div>';
        } else {
            $return .= '<div class="alm-favorites-list" data-user-id="' . $user_id . '" data-table="' . $table_name . '" data-table-type="' . $table_type . '">';
            
            foreach ($favorites as $favorite) {
                $title = stripslashes($favorite->title);
                $created_date = !empty($favorite->created_at) ? date('M j, Y', strtotime($favorite->created_at)) : '';
                
                // Build URL: if resource_link exists and url contains je_link.php, rebuild using resource_link
                $favorite_url = $favorite->url;
                if (!empty($favorite->resource_link)) {
                    // Check if url contains je_link.php - if so, extract lesson ID and rebuild with resource_link
                    if (strpos($favorite->url, 'je_link.php') !== false) {
                        // Extract lesson ID from existing URL
                        parse_str(parse_url($favorite->url, PHP_URL_QUERY), $params);
                        if (!empty($params['id']) && !empty($favorite->resource_link)) {
                            // Rebuild URL with properly encoded resource_link
                            $favorite_url = 'https://jazzedge.academy/je_link.php?id=' . intval($params['id']) . '&link=' . urlencode($favorite->resource_link);
                        }
                    } elseif (strpos($favorite->resource_link, 'http://') !== 0 && strpos($favorite->resource_link, 'https://') !== 0) {
                        // resource_link is a file path and url is direct S3 URL - use resource_link to rebuild
                        // But we need lesson ID... Actually, if url is S3 direct, we might not have lesson ID
                        // For now, if url is S3 direct with broken path, try to fix it
                        if (strpos($favorite->url, 's3.amazonaws.com') !== false && strpos($favorite->url, $favorite->resource_link) === false) {
                            // Rebuild S3 URL with proper resource_link
                            $favorite_url = 'https://s3.amazonaws.com/jazzedge-resources/' . $favorite->resource_link;
                        }
                    }
                }
                
                // Check if it's a resource favorite
                $is_resource = !empty($favorite->resource_link);
                
                $return .= '<div class="alm-favorite-item' . ($is_resource ? ' alm-favorite-resource' : '') . '" data-favorite-id="' . $favorite->id . '">';
                $return .= '<a href="' . esc_url($favorite_url) . '" class="alm-favorite-link' . ($is_resource ? ' alm-favorite-resource-link' : '') . '">';
                if ($is_resource) {
                    $return .= '<span class="alm-favorite-resource-icon">📄</span>';
                }
                $return .= '<span class="alm-favorite-title">' . esc_html($title) . '</span>';
                if ($created_date) {
                    $return .= '<span class="alm-favorite-date">' . esc_html($created_date) . '</span>';
                }
                $return .= '</a>';
                $return .= '<button class="alm-favorite-delete" data-favorite-id="' . $favorite->id . '" title="' . __('Remove favorite', 'academy-lesson-manager') . '">';
                $return .= '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>';
                $return .= '</button>';
                $return .= '</div>';
            }
            
            $return .= '</div>';
        }
        
        $return .= '</div>';
        
        // Add CSS
        $return .= '<style>
            .alm-favorites-management {
                max-width: 900px;
                margin: 20px auto;
                padding: 0 20px;
            }
            .alm-favorites-header {
                display: flex;
                align-items: baseline;
                gap: 12px;
                margin-bottom: 20px;
            }
            .alm-favorites-header h2 {
                margin: 0;
                font-size: 28px;
                font-weight: 700;
                color: #111827;
            }
            .alm-favorites-count {
                font-size: 14px;
                color: #6b7280;
                font-weight: 500;
            }
            .alm-favorites-hint {
                font-size: 13px;
                color: #9ca3af;
                margin: 0 0 16px 0;
            }
            .alm-favorites-empty {
                text-align: center;
                padding: 60px 20px;
                color: #6b7280;
            }
            .alm-favorites-empty p {
                font-size: 15px;
                margin: 0;
            }
            .alm-favorites-list {
                padding: 0;
                margin: 0;
            }
            .alm-favorite-item {
                display: flex;
                align-items: center;
                margin-bottom: 12px;
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                overflow: hidden;
                transition: all 0.2s ease;
                width: 100%;
            }
            .alm-favorite-item:hover {
                border-color: #d1d5db;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            .alm-favorite-link {
                flex: 1 1 auto;
                padding: 16px 20px;
                text-decoration: none;
                color: #374151;
                font-size: 16px;
                font-weight: 500;
                transition: color 0.2s ease;
                line-height: 24px;
                min-width: 0;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: space-between;
                height: 56px;
                gap: 16px;
            }
            .alm-favorite-link:hover {
                color: #059669;
            }
            .alm-favorite-title {
                flex: 1;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .alm-favorite-date {
                font-size: 13px;
                color: #9ca3af;
                font-weight: normal;
                white-space: nowrap;
                flex-shrink: 0;
            }
            .alm-favorite-link:hover .alm-favorite-date {
                color: #6b7280;
            }
            .alm-favorite-delete {
                background: none;
                border: none;
                color: #9ca3af;
                cursor: pointer;
                padding: 16px;
                transition: all 0.2s ease;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                height: 56px;
            }
            .alm-favorite-delete:hover {
                color: #ef4444;
                background: #fef2f2;
            }
            .alm-favorite-delete:active {
                transform: scale(0.95);
            }
            .alm-favorite-delete svg {
                width: 18px;
                height: 18px;
            }
        </style>';
        
        // Add JavaScript for delete functionality
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('alm_favorites_nonce');
        
        $return .= '<script>
        jQuery(document).ready(function($) {
            $(document).on("click", ".alm-favorite-delete", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $button = $(this);
                var $item = $button.closest(".alm-favorite-item");
                var favoriteId = $button.data("favorite-id");
                var list = $item.closest(".alm-favorites-list");
                var tableType = list.data("table-type");
                
                // Prevent multiple clicks
                if ($item.hasClass("deleting")) {
                    return;
                }
                
                if (!confirm("Are you sure you want to remove this favorite?")) {
                    return;
                }
                
                // Mark as deleting and hide immediately
                $item.addClass("deleting");
                $button.prop("disabled", true);
                $item.css("opacity", "0.5");
                $item.fadeOut(300);
                
                $.ajax({
                    url: "' . $ajax_url . '",
                    type: "POST",
                    data: {
                        action: "alm_delete_favorite",
                        favorite_id: favoriteId,
                        table_type: tableType,
                        nonce: "' . $nonce . '"
                    },
                    success: function(response) {
                        if (response.success) {
                            // Item already hidden, just remove from DOM
                            $item.remove();
                            // Update count
                            var count = list.find(".alm-favorite-item").length;
                            $(".alm-favorites-count").text(count + " lessons");
                        } else {
                            // Error: show item again
                            $item.removeClass("deleting");
                            $button.prop("disabled", false);
                            $item.stop().css("opacity", "1").show();
                            alert("Error: " + response.data);
                        }
                    },
                    error: function() {
                        // Error: show item again
                        $item.removeClass("deleting");
                        $button.prop("disabled", false);
                        $item.stop().css("opacity", "1").show();
                        alert("Error removing favorite. Please try again.");
                    }
                });
            });
        });
        </script>';
        
        return $return;
    }
    
    /**
     * Collections Dropdown Shortcode
     * Displays a dropdown list of all lesson collections grouped by membership level
     */
    public function collections_dropdown_shortcode($atts) {
        $atts = shortcode_atts(array(
            'placeholder' => 'Select a collection...',
            'class' => '',
            'style' => ''
        ), $atts);
        
        global $wpdb;
        
        // Get all collections with membership levels
        $all_collections = $wpdb->get_results(
            "SELECT ID, collection_title, membership_level, post_id FROM {$wpdb->prefix}alm_collections ORDER BY membership_level ASC, collection_title ASC"
        );
        
        if (empty($all_collections)) {
            return '<p>No collections found.</p>';
        }
        
        // Get membership level names - ensure class is loaded
        if (!$this->ensure_alm_settings_loaded()) {
            return '<p>Error: Membership settings not available.</p>';
        }
        
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        
        $return = '<div class="alm-collections-dropdown-wrapper">';
        $return .= '<select class="alm-collections-dropdown ' . esc_attr($atts['class']) . '" style="' . esc_attr($atts['style']) . '" onchange="if(this.value) window.location.href = this.value;">';
        $return .= '<option value="">' . esc_html($atts['placeholder']) . '</option>';
        
        $current_level = null;
        
        foreach ($all_collections as $coll) {
            // Only include collections that have a post_id (are published/accessible)
            if (empty($coll->post_id)) {
                continue;
            }
            
            $coll_url = get_permalink($coll->post_id);
            if (!$coll_url) {
                continue;
            }
            
            $membership_level = intval($coll->membership_level);
            
            // Find the membership level name by numeric value
            $level_name = 'Unknown';
            foreach ($membership_levels as $level_key => $level_data) {
                if ($level_data['numeric'] == $membership_level) {
                    $level_name = $level_data['name'];
                    break;
                }
            }
            
            // Start new optgroup when membership level changes
            if ($current_level !== $membership_level) {
                // Close previous optgroup if it exists
                if ($current_level !== null) {
                    $return .= '</optgroup>';
                }
                $return .= '<optgroup label="' . esc_attr($level_name) . '">';
                $current_level = $membership_level;
            }
            
            $return .= '<option value="' . esc_url($coll_url) . '">' . esc_html(stripslashes($coll->collection_title)) . '</option>';
        }
        
        // Close last optgroup
        if ($current_level !== null) {
            $return .= '</optgroup>';
        }
        
        $return .= '</select>';
        $return .= '</div>';
        
        return $return;
    }
    
    /**
     * Collections Page Shortcode
     * Displays information about lesson collections and membership levels
     */
    public function collections_page_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Lesson Collections',
            'show_all_dropdown' => 'true',
            'show_level_dropdowns' => 'true'
        ), $atts);
        
        global $wpdb;
        
        // Get all collections with membership levels
        $all_collections = $wpdb->get_results(
            "SELECT ID, collection_title, membership_level, post_id FROM {$wpdb->prefix}alm_collections WHERE post_id > 0 ORDER BY membership_level ASC, collection_title ASC"
        );
        
        // Get membership level names
        if (!$this->ensure_alm_settings_loaded()) {
            return '<p>Error: Membership settings not available.</p>';
        }
        
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        
        // Count collections by membership level
        $level_counts = array();
        $level_collections = array();
        
        foreach ($all_collections as $coll) {
            if (empty($coll->post_id)) continue;
            
            $membership_level = intval($coll->membership_level);
            
            if (!isset($level_counts[$membership_level])) {
                $level_counts[$membership_level] = 0;
                $level_collections[$membership_level] = array();
            }
            
            $level_counts[$membership_level]++;
            $coll_url = get_permalink($coll->post_id);
            if ($coll_url) {
                $level_collections[$membership_level][] = array(
                    'id' => $coll->ID,
                    'title' => stripslashes($coll->collection_title),
                    'url' => $coll_url
                );
            }
        }
        
        // Get level names
        $level_names = array();
        foreach ($membership_levels as $level_key => $level_data) {
            $level_names[$level_data['numeric']] = $level_data['name'];
        }
        
        // Calculate total lesson hours for each membership level
        $level_hours = array();
        $lessons_table = $wpdb->prefix . 'alm_lessons';
        
        // Essentials (level 1) - gets lessons with membership_level <= 1
        $essentials_total_seconds = $wpdb->get_var(
            "SELECT SUM(duration) FROM {$lessons_table} WHERE membership_level <= 1 AND duration > 0"
        );
        $level_hours[1] = $essentials_total_seconds ? round($essentials_total_seconds / 3600, 0) : 0;
        
        // Studio (level 2) - gets lessons with membership_level <= 2
        $studio_total_seconds = $wpdb->get_var(
            "SELECT SUM(duration) FROM {$lessons_table} WHERE membership_level <= 2 AND duration > 0"
        );
        $level_hours[2] = $studio_total_seconds ? round($studio_total_seconds / 3600, 0) : 0;
        
        // Premier (level 3) - gets all lessons (membership_level <= 3)
        $premier_total_seconds = $wpdb->get_var(
            "SELECT SUM(duration) FROM {$lessons_table} WHERE membership_level <= 3 AND duration > 0"
        );
        $level_hours[3] = $premier_total_seconds ? round($premier_total_seconds / 3600, 0) : 0;
        
        ob_start();
        ?>
        <div class="alm-collections-page">
            <style>
                .alm-collections-page {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 40px 20px;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                }
                
                .alm-collections-intro {
                    text-align: center;
                    margin-bottom: 50px;
                }
                
                .alm-collections-intro h1 {
                    font-size: 2.5em;
                    color: #1f2937;
                    margin: 0 0 20px 0;
                    font-weight: 700;
                }
                
                .alm-collections-intro p {
                    font-size: 1.2em;
                    color: #64748b;
                    line-height: 1.6;
                    max-width: 800px;
                    margin: 0 auto;
                }
                
                .alm-main-dropdown-section {
                    background: linear-gradient(135deg, #004555 0%, #006b7a 50%, #239B90 100%);
                    border-radius: 16px;
                    padding: 40px;
                    margin-bottom: 50px;
                    box-shadow: 0 10px 30px rgba(0, 69, 85, 0.3);
                }
                
                .alm-main-dropdown-section h2 {
                    color: white;
                    font-size: 1.8em;
                    margin: 0 0 20px 0;
                    text-align: center;
                    font-weight: 700;
                }
                
                .alm-main-dropdown-wrapper {
                    background: white;
                    border-radius: 12px;
                    padding: 20px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                }
                
                .alm-main-dropdown-wrapper select {
                    width: 100%;
                    padding: 16px 20px;
                    font-size: 1.1em;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    background: white;
                    color: #1f2937;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                
                .alm-main-dropdown-wrapper select:hover {
                    border-color: #239B90;
                    box-shadow: 0 0 0 3px rgba(35, 155, 144, 0.1);
                }
                
                .alm-main-dropdown-wrapper select:focus {
                    outline: none;
                    border-color: #239B90;
                    box-shadow: 0 0 0 3px rgba(35, 155, 144, 0.2);
                }
                
                .alm-membership-levels {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 30px;
                    margin-bottom: 40px;
                    align-items: start;
                }
                
                .alm-level-card {
                    background: white;
                    border-radius: 16px;
                    padding: 30px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                    border: 2px solid #e5e7eb;
                    transition: all 0.3s ease;
                    position: relative;
                    overflow: visible;
                    min-height: 320px;
                    display: flex;
                    flex-direction: column;
                }
                
                .alm-level-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 5px;
                    background: linear-gradient(90deg, #004555, #239B90);
                }
                
                .alm-level-card.essentials::before {
                    background: linear-gradient(90deg, #004555, #239B90);
                }
                
                .alm-level-card.studio::before {
                    background: linear-gradient(90deg, #6B2B60, #8B4A7A);
                }
                
                .alm-level-card.premier::before {
                    background: linear-gradient(90deg, #F04E23, #D93E1A);
                }
                
                .alm-level-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
                }
                
                .alm-level-header {
                    margin-bottom: 20px;
                }
                
                .alm-level-header h3 {
                    font-size: 1.8em;
                    color: #1f2937;
                    margin: 0 0 10px 0;
                    font-weight: 700;
                }
                
                .alm-level-count {
                    display: inline-block;
                    background: #f1f5f9;
                    color: #475569;
                    padding: 6px 14px;
                    border-radius: 20px;
                    font-size: 0.9em;
                    font-weight: 600;
                    margin-top: 10px;
                }
                
                .alm-premier-card-wrapper {
                    position: relative;
                    display: flex;
                    flex-direction: column;
                }
                
                .alm-premier-access-badge {
                    display: inline-block;
                    background: linear-gradient(135deg, #F04E23 0%, #D93E1A 100%);
                    color: white;
                    padding: 8px 16px;
                    border-radius: 20px;
                    font-size: 0.85em;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    box-shadow: 0 2px 8px rgba(240, 78, 35, 0.3);
                    text-align: center;
                    width: calc(100% - 20px);
                    box-sizing: border-box;
                    position: absolute;
                    top: -18px;
                    left: 50%;
                    transform: translateX(-50%);
                    z-index: 10;
                }
                
                .alm-level-description {
                    color: #64748b;
                    line-height: 1.6;
                    margin-bottom: 20px;
                    font-size: 1em;
                    flex-grow: 1;
                }
                
                .alm-level-dropdown-wrapper {
                    margin-top: auto;
                }
                
                .alm-level-dropdown-wrapper select {
                    width: 100%;
                    padding: 12px 16px;
                    font-size: 1em;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    background: white;
                    color: #1f2937;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                
                .alm-level-dropdown-wrapper select:hover {
                    border-color: #239B90;
                }
                
                .alm-level-dropdown-wrapper select:focus {
                    outline: none;
                    border-color: #239B90;
                    box-shadow: 0 0 0 3px rgba(35, 155, 144, 0.1);
                }
                
                .alm-premier-note {
                    background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
                    border: 2px solid #F04E23;
                    border-radius: 12px;
                    padding: 20px;
                    margin-top: 30px;
                    text-align: center;
                }
                
                .alm-premier-note p {
                    margin: 0;
                    color: #8B4513;
                    font-weight: 600;
                    font-size: 1.1em;
                }
                
                .alm-premier-note p strong {
                    color: #F04E23;
                }
                
                @media (max-width: 768px) {
                    .alm-collections-page {
                        padding: 20px 15px;
                    }
                    
                    .alm-collections-intro h1 {
                        font-size: 2em;
                    }
                    
                    .alm-collections-intro p {
                        font-size: 1.1em;
                    }
                    
                    .alm-main-dropdown-section {
                        padding: 30px 20px;
                    }
                    
                    .alm-membership-levels {
                        grid-template-columns: 1fr;
                        gap: 20px;
                    }
                    
                    .alm-level-card {
                        padding: 25px;
                    }
                }
            </style>
            
            <div class="alm-collections-intro">
                <h1><?php echo esc_html($atts['title']); ?></h1>
                <p>Lesson collections are our structured courses that guide you through comprehensive learning paths. Each collection contains multiple lessons organized to help you master specific topics, techniques, or songs systematically.</p>
            </div>
            
            <?php if ($atts['show_all_dropdown'] === 'true'): ?>
            <div class="alm-main-dropdown-section">
                <h2>Browse All Collections</h2>
                <div class="alm-main-dropdown-wrapper">
                    <?php echo $this->collections_dropdown_shortcode(array('placeholder' => 'Select a collection to explore...')); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="alm-membership-levels">
                <?php
                // Essentials (level 1)
                $essentials_count = $level_counts[1] ?? 0;
                $essentials_collections = $level_collections[1] ?? array();
                ?>
                <div class="alm-level-card essentials">
                    <div class="alm-level-header">
                        <h3>Essentials</h3>
                        <span class="alm-level-count"><?php echo $essentials_count; ?> Collection<?php echo $essentials_count !== 1 ? 's' : ''; ?> • <?php echo number_format($level_hours[1]); ?>hrs</span>
                    </div>
                    <div class="alm-level-description">
                        <p>Perfect for beginners and those looking to build a solid foundation. Essentials collections cover fundamental concepts and techniques to get you started on your musical journey.</p>
                    </div>
                    <?php if ($atts['show_level_dropdowns'] === 'true' && !empty($essentials_collections)): ?>
                    <div class="alm-level-dropdown-wrapper">
                        <select onchange="if(this.value) window.location.href = this.value;">
                            <option value="">Browse Collections...</option>
                            <?php foreach ($essentials_collections as $coll): ?>
                                <option value="<?php echo esc_url($coll['url']); ?>"><?php echo esc_html($coll['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php
                // Studio (level 2)
                $studio_count = $level_counts[2] ?? 0;
                $studio_collections = $level_collections[2] ?? array();
                ?>
                <div class="alm-level-card studio">
                    <div class="alm-level-header">
                        <h3>Studio</h3>
                        <span class="alm-level-count"><?php echo $studio_count; ?> Collection<?php echo $studio_count !== 1 ? 's' : ''; ?> • <?php echo number_format($level_hours[2]); ?>hrs</span>
                    </div>
                    <div class="alm-level-description">
                        <p>Take your skills to the next level with Studio collections. These intermediate to advanced courses dive deeper into techniques, theory, and repertoire to help you grow as a musician.</p>
                    </div>
                    <?php if ($atts['show_level_dropdowns'] === 'true' && !empty($studio_collections)): ?>
                    <div class="alm-level-dropdown-wrapper">
                        <select onchange="if(this.value) window.location.href = this.value;">
                            <option value="">Browse Collections...</option>
                            <?php foreach ($studio_collections as $coll): ?>
                                <option value="<?php echo esc_url($coll['url']); ?>"><?php echo esc_html($coll['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php
                // Premier (level 3)
                $premier_count = $level_counts[3] ?? 0;
                $premier_collections = $level_collections[3] ?? array();
                $total_accessible = $essentials_count + $studio_count + $premier_count;
                ?>
                <div class="alm-premier-card-wrapper">
                    <div class="alm-premier-access-badge">Access to All Collections</div>
                    <div class="alm-level-card premier">
                        <div class="alm-level-header">
                            <h3>Premier</h3>
                            <span class="alm-level-count"><?php echo $premier_count; ?> Premier Collection<?php echo $premier_count !== 1 ? 's' : ''; ?> • <?php echo number_format($level_hours[3]); ?>hrs</span>
                        </div>
                    <div class="alm-level-description">
                        <p>Our most comprehensive membership tier. Premier collections feature advanced techniques, master classes, and exclusive content for serious students.</p>
                    </div>
                    <?php if ($atts['show_level_dropdowns'] === 'true' && !empty($premier_collections)): ?>
                    <div class="alm-level-dropdown-wrapper">
                        <select onchange="if(this.value) window.location.href = this.value;">
                            <option value="">Browse Collections...</option>
                            <?php foreach ($premier_collections as $coll): ?>
                                <option value="<?php echo esc_url($coll['url']); ?>"><?php echo esc_html($coll['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="alm-premier-note">
                <p><strong>Premier Membership = Complete Access</strong><br>
                Premier members have access to <strong>all <?php echo $total_accessible; ?> collections</strong> across Essentials, Studio, and Premier levels. Upgrade to Premier to unlock everything.</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * User Notes Manager Shortcode
     * CRUD interface for managing user notes
     */
    public function user_notes_manager_shortcode($atts) {
        // Only show to logged in users
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your notes.', 'academy-lesson-manager') . '</p>';
        }
        
        $user_id = get_current_user_id();
        global $wpdb;
        $table_name = $wpdb->prefix . 'alm_user_notes';
        
        // Get all user's notes
        $notes = $wpdb->get_results($wpdb->prepare(
            "SELECT n.*, 
                    l.lesson_title,
                    p.post_title
             FROM {$table_name} n
             LEFT JOIN {$wpdb->prefix}alm_lessons l ON n.lesson_id = l.ID
             LEFT JOIN {$wpdb->posts} p ON n.post_id = p.ID
             WHERE n.user_id = %d
             ORDER BY n.updated_at DESC",
            $user_id
        ));
        
        $nonce = wp_create_nonce('alm_notes_crud_nonce');
        $ajax_url = admin_url('admin-ajax.php');
        
        $return = '<div class="alm-notes-manager">';
        $return .= '<div class="alm-notes-header">';
        $return .= '<div class="alm-notes-header-content">';
        $return .= '<h1 class="alm-notes-title">' . __('My Notes', 'academy-lesson-manager') . '</h1>';
        $return .= '<div class="alm-notes-actions">';
        $return .= '<button class="alm-btn-export-csv" style="background: #6b7280; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; margin-right: 12px;">';
        $return .= '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
        $return .= 'Export CSV</button>';
        $return .= '<button class="alm-btn-create-note" style="background: #229B90; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 14px;">+ New Note</button>';
        $return .= '</div>';
        $return .= '</div>';
        $return .= '</div>';
        
        // Create/Edit form (hidden by default)
        $return .= '<div class="alm-note-form-container" style="display: none; margin-top: 20px; padding: 20px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">';
        $return .= '<form class="alm-note-form">';
        $return .= '<input type="hidden" name="note_id" id="alm-note-id" value="">';
        $return .= '<div style="margin-bottom: 15px;">';
        $return .= '<label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Note Title</label>';
        $return .= '<input type="text" name="title" id="alm-note-title" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;" placeholder="Enter note title..." maxlength="255">';
        $return .= '</div>';
        $return .= '<div style="margin-bottom: 15px;">';
        $return .= '<label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Note Content</label>';
        $return .= '<textarea name="notes_content" id="alm-note-content" rows="6" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; font-family: inherit;" placeholder="Enter your note..."></textarea>';
        $return .= '</div>';
        $return .= '<div style="display: flex; gap: 10px;">';
        $return .= '<button type="submit" class="alm-btn-save-note" style="background: #229B90; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 500;">Save Note</button>';
        $return .= '<button type="button" class="alm-btn-cancel-note" style="background: #6b7280; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancel</button>';
        $return .= '</div>';
        $return .= '</form>';
        $return .= '</div>';
        
        // Notes list
        if (empty($notes)) {
            $return .= '<div class="alm-notes-empty" style="text-align: center; padding: 60px 20px; color: #6b7280;">';
            $return .= '<svg width="64" height="64" fill="currentColor" viewBox="0 0 20 20" style="opacity: 0.3; margin-bottom: 16px;"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>';
            $return .= '<p>' . __('You haven\'t created any notes yet.', 'academy-lesson-manager') . '</p>';
            $return .= '</div>';
        } else {
            $return .= '<div class="alm-notes-list" style="margin-top: 20px;">';
            foreach ($notes as $note) {
                $note_date = date('M j, Y g:i a', strtotime($note->updated_at));
                $created_date = date('M j, Y g:i a', strtotime($note->created_at));
                $note_preview = wp_strip_all_tags($note->notes_content);
                $note_preview = mb_strimwidth($note_preview, 0, 150, '...');
                
                $return .= '<div class="alm-note-item" data-note-id="' . $note->id . '" data-lesson-id="' . ($note->lesson_id ? $note->lesson_id : '') . '" data-created-at="' . esc_attr($created_date) . '" data-updated-at="' . esc_attr($note_date) . '" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 12px; transition: all 0.2s ease;">';
                
                // Note header
                $return .= '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">';
                $return .= '<div style="flex: 1;">';
                $note_title = !empty($note->title) ? $note->title : (!empty($note->lesson_title) ? $note->lesson_title : (!empty($note->post_title) ? $note->post_title : __('General Note', 'academy-lesson-manager')));
                $return .= '<div style="font-weight: 600; color: #111827; margin-bottom: 4px; font-size: 16px;">' . esc_html($note_title) . '</div>';
                $return .= '<div style="font-size: 12px; color: #6b7280;">' . esc_html($note_date) . '</div>';
                $return .= '</div>';
                $return .= '<div style="display: flex; gap: 8px; align-items: center;">';
                $return .= '<button class="alm-btn-print-note" data-note-id="' . $note->id . '" style="background: none; border: 1px solid #d1d5db; color: #374151; padding: 6px 8px; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;" title="Print">';
                $return .= '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>';
                $return .= '</button>';
                $return .= '<button class="alm-btn-edit-note" data-note-id="' . $note->id . '" style="background: none; border: 1px solid #d1d5db; color: #374151; padding: 6px 8px; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;" title="Edit">';
                $return .= '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>';
                $return .= '</button>';
                $return .= '<button class="alm-btn-delete-note" data-note-id="' . $note->id . '" style="background: none; border: 1px solid #ef4444; color: #ef4444; padding: 6px 8px; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;" title="Delete">';
                $return .= '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>';
                $return .= '</button>';
                $return .= '</div>';
                $return .= '</div>';
                
                // Note content preview - store full content in data attribute for editing
                $return .= '<div class="alm-note-content" style="color: #4b5563; line-height: 1.6; margin-top: 10px;" data-full-content="' . esc_attr($note->notes_content) . '">';
                $return .= '<div style="max-height: 100px; overflow: hidden;">' . wp_kses_post($note->notes_content) . '</div>';
                $return .= '</div>';
                
                $return .= '</div>';
            }
            $return .= '</div>';
        }
        
        $return .= '</div>';
        
        // Add CSS
        $return .= '<style>
        .alm-notes-manager {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .alm-notes-header {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .alm-notes-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .alm-notes-title {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }
        .alm-notes-actions {
            display: flex;
            align-items: center;
        }
        .alm-note-item:hover {
            border-color: #d1d5db !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .alm-note-content {
            color: #4b5563;
        }
        .alm-note-content p {
            margin: 0 0 8px 0;
        }
        .alm-note-content p:last-child {
            margin-bottom: 0;
        }
        </style>';
        
        // Add JavaScript
        $return .= '<script>
        jQuery(document).ready(function($) {
            var $formContainer = $(".alm-note-form-container");
            var $form = $(".alm-note-form");
            var $createBtn = $(".alm-btn-create-note");
            var $cancelBtn = $(".alm-btn-cancel-note");
            var allLessons = [];
            
            // Show create form
            $createBtn.on("click", function() {
                $form[0].reset();
                $("#alm-note-id").val("");
                $formContainer.slideDown();
            });
            
            // Hide form
            $cancelBtn.on("click", function() {
                $formContainer.slideUp();
                $form[0].reset();
                $("#alm-note-id").val("");
            });
            
            // Edit note
            $(document).on("click", ".alm-btn-edit-note", function() {
                var noteId = $(this).data("note-id");
                var $noteItem = $(this).closest(".alm-note-item");
                
                // Get note title (from the header)
                var noteTitle = $noteItem.find(".alm-note-content").closest(".alm-note-item").find("div[style*=\"font-weight: 600\"]").first().text().trim();
                
                // Get full content from data attribute to preserve HTML
                var fullContent = $noteItem.find(".alm-note-content").data("full-content") || "";
                // Fallback to text if data attribute not available
                if (!fullContent) {
                    fullContent = $noteItem.find(".alm-note-content").text();
                }
                
                $("#alm-note-id").val(noteId);
                $("#alm-note-title").val(noteTitle);
                
                // Use a temporary div to convert HTML to text while preserving line breaks
                var $temp = $("<div>").html(fullContent);
                var textContent = $temp.text();
                $("#alm-note-content").val(textContent);
                
                $formContainer.slideDown();
                $("html, body").animate({ scrollTop: $formContainer.offset().top - 20 }, 300);
            });
            
            // Print note
            $(document).on("click", ".alm-btn-print-note", function() {
                var noteId = $(this).data("note-id");
                var $noteItem = $(this).closest(".alm-note-item");
                
                // Create print window content
                var noteTitle = $noteItem.find("div[style*=\"font-weight: 600\"]").first().text();
                var noteDate = $noteItem.find("div[style*=\"font-size: 12px\"]").first().text();
                var noteContent = $noteItem.find(".alm-note-content").html();
                
                var printWindow = window.open("", "_blank");
                printWindow.document.write("<!DOCTYPE html><html><head><title>" + noteTitle + "</title>");
                printWindow.document.write("<style>body { font-family: Arial, sans-serif; padding: 40px; max-width: 800px; margin: 0 auto; }");
                printWindow.document.write("h1 { font-size: 24px; margin-bottom: 10px; color: #111827; }");
                printWindow.document.write(".note-meta { color: #6b7280; font-size: 14px; margin-bottom: 20px; }");
                printWindow.document.write(".note-content { line-height: 1.6; color: #4b5563; }");
                printWindow.document.write("@media print { body { padding: 20px; } }</style></head><body>");
                printWindow.document.write("<h1>" + noteTitle + "</h1>");
                printWindow.document.write("<div class=\"note-meta\">" + noteDate + "</div>");
                printWindow.document.write("<div class=\"note-content\">" + noteContent + "</div>");
                printWindow.document.write("</body></html>");
                printWindow.document.close();
                
                setTimeout(function() {
                    printWindow.print();
                }, 250);
            });
            
            // Export CSV
            $(".alm-btn-export-csv").on("click", function() {
                // Helper function to properly escape CSV fields
                function escapeCsvField(field) {
                    if (field === null || field === undefined) {
                        return "";
                    }
                    field = String(field);
                    // Normalize whitespace - replace all types of whitespace/newlines with single space
                    field = field.replace(/[\\s\\r\\n\\t]+/g, " ").trim();
                    // If field contains comma, quote, or starts/ends with space, wrap in quotes
                    if (field.indexOf(",") !== -1 || field.indexOf("\"") !== -1 || field.indexOf("\\n") !== -1) {
                        // Escape quotes by doubling them
                        return "\"" + field.replace(/"/g, "\"\"") + "\"";
                    }
                    return field;
                }
                
                // CSV header
                var csvRows = ["Title,Content,Date Created,Date Updated"];
                
                // Process each note
                $(".alm-note-item").each(function() {
                    var $item = $(this);
                    var title = $item.find("div[style*=\"font-weight: 600\"]").first().text().trim();
                    var content = $item.find(".alm-note-content").text().trim();
                    var dateCreated = $item.data("created-at") || "";
                    var dateUpdated = $item.data("updated-at") || "";
                    
                    // Escape each field
                    var escapedTitle = escapeCsvField(title);
                    var escapedContent = escapeCsvField(content);
                    var escapedDateCreated = escapeCsvField(dateCreated);
                    var escapedDateUpdated = escapeCsvField(dateUpdated);
                    
                    // Build CSV row
                    var row = escapedTitle + "," + escapedContent + "," + escapedDateCreated + "," + escapedDateUpdated;
                    csvRows.push(row);
                });
                
                // Join all rows with newline
                var csv = csvRows.join("\\n");
                
                // Create download link
                var blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
                var link = document.createElement("a");
                var url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "my-notes-" + new Date().toISOString().split("T")[0] + ".csv");
                link.style.visibility = "hidden";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
            
            // Delete note
            $(document).on("click", ".alm-btn-delete-note", function() {
                if (!confirm("Are you sure you want to delete this note?")) {
                    return;
                }
                
                var $btn = $(this);
                var noteId = $btn.data("note-id");
                var $noteItem = $btn.closest(".alm-note-item");
                
                $btn.prop("disabled", true).text("Deleting...");
                
                $.ajax({
                    url: "' . $ajax_url . '",
                    type: "POST",
                    data: {
                        action: "alm_delete_note",
                        nonce: "' . $nonce . '",
                        note_id: noteId
                    },
                    success: function(response) {
                        if (response.success) {
                            $noteItem.fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            alert("Error: " + (response.data || "Failed to delete note"));
                            $btn.prop("disabled", false).text("Delete");
                        }
                    },
                    error: function() {
                        alert("Error deleting note");
                        $btn.prop("disabled", false).text("Delete");
                    }
                });
            });
            
            // Save note (create or update)
            $form.on("submit", function(e) {
                e.preventDefault();
                
                var noteId = $("#alm-note-id").val();
                var notesContent = $("#alm-note-content").val();
                var noteTitle = $("#alm-note-title").val();
                
                if (!notesContent.trim()) {
                    alert("Please enter note content");
                    return;
                }
                
                if (!noteTitle.trim()) {
                    alert("Please enter note title");
                    return;
                }
                
                var $submitBtn = $(".alm-btn-save-note");
                $submitBtn.prop("disabled", true).text("Saving...");
                
                var action = noteId ? "alm_update_note" : "alm_create_note";
                var data = {
                    action: action,
                    nonce: "' . $nonce . '",
                    notes_content: notesContent,
                    title: noteTitle
                };
                
                if (noteId) {
                    data.note_id = noteId;
                }
                
                $.ajax({
                    url: "' . $ajax_url . '",
                    type: "POST",
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert("Error: " + (response.data || "Failed to save note"));
                            $submitBtn.prop("disabled", false).text("Save Note");
                        }
                    },
                    error: function() {
                        alert("Error saving note");
                        $submitBtn.prop("disabled", false).text("Save Note");
                    }
                });
            });
        });
        </script>';
        
        return $return;
    }
    
    /**
     * AJAX handler: Create note
     */
    public function ajax_create_note() {
        check_ajax_referer('alm_notes_crud_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }
        
        $notes_content = isset($_POST['notes_content']) ? wp_kses_post($_POST['notes_content']) : '';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        
        if (empty($notes_content)) {
            wp_send_json_error('Note content is required');
        }
        
        if (empty($title)) {
            wp_send_json_error('Note title is required');
        }
        
        global $wpdb;
        $post_id = get_the_ID();
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'alm_user_notes',
            array(
                'user_id' => $user_id,
                'post_id' => $post_id ? $post_id : 0,
                'lesson_id' => null,
                'title' => $title,
                'notes_content' => $notes_content,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%s', '%s', '%s', '%s')
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Note created successfully'));
        } else {
            wp_send_json_error('Failed to create note');
        }
    }
    
    /**
     * AJAX handler: Update note
     */
    public function ajax_update_note() {
        check_ajax_referer('alm_notes_crud_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }
        
        $note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;
        $notes_content = isset($_POST['notes_content']) ? wp_kses_post($_POST['notes_content']) : '';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        
        if (!$note_id) {
            wp_send_json_error('Note ID is required');
        }
        
        if (empty($notes_content)) {
            wp_send_json_error('Note content is required');
        }
        
        if (empty($title)) {
            wp_send_json_error('Note title is required');
        }
        
        global $wpdb;
        
        // Verify note belongs to user
        $note = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}alm_user_notes WHERE id = %d AND user_id = %d",
            $note_id,
            $user_id
        ));
        
        if (!$note) {
            wp_send_json_error('Note not found or access denied');
        }
        
        $result = $wpdb->update(
            $wpdb->prefix . 'alm_user_notes',
            array(
                'title' => $title,
                'notes_content' => $notes_content,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $note_id),
            array('%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Note updated successfully'));
        } else {
            wp_send_json_error('Failed to update note');
        }
    }
    
    /**
     * AJAX handler: Delete note
     */
    public function ajax_delete_note() {
        check_ajax_referer('alm_notes_crud_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }
        
        $note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;
        
        if (!$note_id) {
            wp_send_json_error('Note ID is required');
        }
        
        global $wpdb;
        
        // Verify note belongs to user before deleting
        $note = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}alm_user_notes WHERE id = %d AND user_id = %d",
            $note_id,
            $user_id
        ));
        
        if (!$note) {
            wp_send_json_error('Note not found or access denied');
        }
        
        $deleted = $wpdb->delete(
            $wpdb->prefix . 'alm_user_notes',
            array('id' => $note_id),
            array('%d')
        );
        
        if ($deleted) {
            wp_send_json_success(array('message' => 'Note deleted successfully'));
        } else {
            wp_send_json_error('Failed to delete note');
        }
    }
    
    /**
     * AJAX handler: Get lessons list for dropdown
     */
    public function ajax_get_lessons_list() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }
        
        global $wpdb;
        $lessons = $wpdb->get_results(
            "SELECT ID, lesson_title FROM {$wpdb->prefix}alm_lessons ORDER BY lesson_title ASC"
        );
        
        $lessons_list = array();
        foreach ($lessons as $lesson) {
            $lessons_list[$lesson->ID] = stripslashes($lesson->lesson_title);
        }
        
        wp_send_json_success($lessons_list);
    }
    
    /**
     * Membership List Shortcode
     * Display active subscriptions and memberships
     */
    public function membership_list_shortcode($atts, $content = NULL) {
        // Check if step parameter exists (prevent execution if cancel flow is active)
        if (!empty($_GET['step'])) {
            return;
        }

        // Ensure required functions exist
        if (!function_exists('memb_getContactId') || !function_exists('keap_get_contact_fields') || !function_exists('convert_infusionsoft_date') || !function_exists('je_return_billing_cycle')) {
            return '<p class="center bold_red">Membership functions are not available. Please contact support.</p>';
        }

        global $install, $app;
        
        // Initialize Keap connection if needed
        if (!isset($app) || !is_object($app)) {
            // Try to include Keap connection
            $keap_path = '/nas/content/live/' . (defined('INSTALL') ? INSTALL : $install) . '/keap_isdk/infusion_connect.php';
            if (file_exists($keap_path)) {
                include($keap_path);
            } else {
                return '<p class="center bold_red">Keap connection not available. Please contact support.</p>';
            }
        }

        $returnFields = array('ContactId', 'Id', 'AutoCharge', 'BillingAmt', 'BillingCycle', 'LastBillDate', 'PaidThruDate', 'ProductId', 'StartDate', 'Status', 'BillingCycle', 'MerchantAccountId', 'MaxRetry', 'NumDaysBetweenRetry', 'PaymentGatewayId', 'ReasonStopped', 'SubscriptionPlanId', 'OriginatingOrderId', 'EndDate', 'NextBillDate');

        $contact_id = memb_getContactId();
        
        if (!$contact_id) {
            return '<p class="center bold_red">You must be logged in to view your memberships.</p>';
        }

        $ecd = keap_get_contact_fields($contact_id, array('_AcademyEligibleCancelDate'));
        $eligible_cancel_date = convert_infusionsoft_date($ecd['_AcademyEligibleCancelDate'] ?? '');

        if (!empty($_GET['id'])) {
            $query = array('ContactId' => $contact_id, 'Status' => 'Active', 'Id' => intval($_GET['id']));
        } else {
            $query = array('ContactId' => $contact_id);
        }

        $subscriptions = $app->dsQuery("RecurringOrder", 100, 0, $query, $returnFields);

        $has_1_year = function_exists('memb_hasAnyTags') ? memb_hasAnyTags(array(9813, 9815, 9817, 9819)) : false;
        $academy_expiration_date = function_exists('memb_getContactField') ? memb_getContactField('_AcademyExpirationDate') : '';

        if (!empty($subscriptions)) {
            $return = "
            <div class='rg-container hover-black'>
        <table class='rg-table zebra' summary='Memberships'>
            <caption class='rg-header'>
                <span class='rg-dek'><p>You can scroll within this box to see all of your memberships.</p></span>
            </caption>
            <thead>
                <tr>
                    <th class='text'>Status</th>
                    <th class='text'>Membership</th>
                    <th class='text '>Amount</th>
                    <th class='text'>Start Date</th>
                    <th class='text'>Next Billing</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>";

            krsort($subscriptions);

            foreach ($subscriptions as $subscription) {
                $id = $subscription['Id'];

                $returnFields = array('ProductName', 'ProductPrice', 'Sku');
                $pid = $subscription['ProductId'];
                $query = array('Id' => $pid);

                $next_bill_date = ($subscription['Status'] == 'Active' && $subscription['AutoCharge'] == 1) ? convert_infusionsoft_date($subscription['NextBillDate']) : 'Cancelled';
                $next_bill_date_raw = $subscription['NextBillDate'];
                $mysql_friendly_date = date('Y-m-d', strtotime($next_bill_date_raw));

                $start_date = convert_infusionsoft_date($subscription['StartDate']);

                $product = $app->dsQuery("Product", 1, 0, $query, $returnFields);
                $product_name = $product[0]['ProductName'] ?? 'Unknown Product';
                $product_name = ($product_name == 'JA_MONTHLY_STUDIO_DMP') ? 'Annual Studio Membership' : $product_name;

                $product_sku = $product[0]['Sku'] ?? '';

                $billing_amount = number_format($subscription['BillingAmt'], 2, '.', ',');
                $billing_cycle = je_return_billing_cycle($subscription['BillingCycle']);

                $payment_gateway = $subscription['PaymentGatewayId'];

                $billing = ($subscription['AutoCharge'] == 1 && $subscription['Status'] == 'Active') ? '<strong style="color:green">Active</strong>' : '<strong style="color:red">Cancelled*</strong>';

                $return .= "<tr>
                <td>$billing</td>
                <td>$product_name ($pid)</td>
                <td>$$billing_amount/$billing_cycle</td>
                <td>$start_date</td>
                <td>$next_bill_date</td>";

                // Academy PIDs that can show cancel option
                $academy_pids = array(62332, 62334, 62285, 62293, 62323, 62321, 62319, 62317, 62315, 62313, 62291, 62289, 62287, 62283, 62281, 62279, 62259, 62257, 62251, 62249, 62243, 62241, 62239, 62237);

                if ($subscription['Status'] == 'Active' && $subscription['AutoCharge'] == 1 && empty($_GET['step'])) {
                    // Show cancel button with modal trigger
                    if (in_array($pid, $academy_pids)) {
                        $return .= "<td><a href='#' class='alm-cancel-membership-btn' data-subscription-id='$id' data-product-id='$pid' data-product-name='" . esc_attr($product_name) . "'>Cancel</a></td>";
                    } elseif ($pid === 62350 || $pid === 62352) {
                        $return .= "<td>Can cancel after<br />$eligible_cancel_date</td>";
                    } else {
                        $return .= "<td><a href='#' class='alm-cancel-membership-btn' data-subscription-id='$id' data-product-id='$pid' data-product-name='" . esc_attr($product_name) . "'>Cancel</a></td>";
                    }
                } elseif ($subscription['Status'] == 'Active' && $payment_gateway === 5 && $subscription['AutoCharge'] == 1 && empty($_GET['step'])) {
                    // PayPal subscriptions
                    if (in_array($pid, $academy_pids)) {
                        $return .= "<td><a href='#' class='alm-cancel-membership-btn' data-subscription-id='$id' data-product-id='$pid' data-product-name='" . esc_attr($product_name) . "'>Cancel Membership</a></td>";
                    } elseif ($pid === 62350 || $pid === 62352) {
                        $return .= "<td>Can cancel after<br />$eligible_cancel_date</td>";
                    } else {
                        $return .= "<td><a href='#' class='alm-cancel-membership-btn' data-subscription-id='$id' data-product-id='$pid' data-product-name='" . esc_attr($product_name) . "'>Cancel</a></td>";
                    }
                } else {
                    $return .= '<td></td>';
                }

                $return .= '</tr>';
            }

            $return .= '</tbody></table></div>';
            
            // Add Cancel Membership Modal
            $return .= '
            <div id="alm-cancel-membership-modal" class="alm-modal-overlay" style="display: none;">
                <div class="alm-modal-content">
                    <span class="alm-modal-close">&times;</span>
                    <h2>Cancel Membership</h2>
                    <div class="alm-modal-body">
                        <p>Are you sure you want to cancel your membership?</p>
                        <p><strong>Membership:</strong> <span id="alm-modal-product-name"></span></p>
                        
                        <div class="alm-cancel-disclaimer">
                            <p><strong>Important Information:</strong></p>
                            <ul>
                                <li>There are <strong>no refunds</strong> for membership renewals that have already been processed.</li>
                                <li>If any payments come in after you submit your cancellation request, they will be refunded if they meet our <a href="https://jazzedge.academy/terms/" target="_blank">terms and conditions</a>.</li>
                            </ul>
                        </div>
                        
                        <div class="alm-cancel-form-container">
                            ' . do_shortcode('[fluentform id="47"]') . '
                        </div>
                    </div>
                </div>
            </div>
            
            <style>
            .alm-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .alm-modal-content {
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                max-width: 600px;
                max-height: 90vh;
                width: 90%;
                position: relative;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
                overflow-y: auto;
            }
            .alm-modal-close {
                position: absolute;
                top: 10px;
                right: 15px;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
                color: #999;
            }
            .alm-modal-close:hover {
                color: #000;
            }
            .alm-modal-content {
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                max-width: 800px;
                max-height: 90vh;
                width: 90%;
                position: relative;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
                overflow-y: auto;
                text-align: left;
            }
            .alm-modal-content h2 {
                text-align: left;
                margin-top: 0;
                margin-bottom: 20px;
            }
            .alm-modal-body {
                margin-top: 20px;
                text-align: left;
            }
            .alm-modal-body p {
                text-align: left;
            }
            .alm-cancel-disclaimer {
                background: #fff4cc;
                border: 1px solid #ffc107;
                border-radius: 4px;
                padding: 15px;
                margin: 20px 0;
            }
            .alm-cancel-disclaimer p {
                margin-top: 0;
                margin-bottom: 10px;
            }
            .alm-cancel-disclaimer ul {
                margin: 10px 0 0 20px;
                padding: 0;
            }
            .alm-cancel-disclaimer li {
                margin-bottom: 8px;
            }
            .alm-cancel-disclaimer a {
                color: #0073aa;
                text-decoration: underline;
            }
            .alm-cancel-disclaimer a:hover {
                color: #005177;
            }
            .alm-cancel-form-container {
                margin-top: 20px;
            }
            .alm-cancel-membership-btn {
                color: #dc3232;
                text-decoration: underline;
                cursor: pointer;
            }
            .alm-cancel-membership-btn:hover {
                color: #a00;
            }
            </style>
            
            <script>
            jQuery(document).ready(function($) {
                $(".alm-cancel-membership-btn").on("click", function(e) {
                    e.preventDefault();
                    var productName = $(this).data("product-name");
                    var subscriptionId = $(this).data("subscription-id");
                    var productId = $(this).data("product-id");
                    
                    $("#alm-modal-product-name").text(productName);
                    $("#alm-cancel-membership-modal").show();
                    
                    // Populate hidden fields in FluentForm if they exist
                    // Note: You may need to adjust these selectors based on your FluentForm field IDs
                    $("#alm-cancel-membership-modal input[name*=\"subscription_id\"], #alm-cancel-membership-modal input[name*=\"subscription-id\"]").val(subscriptionId);
                    $("#alm-cancel-membership-modal input[name*=\"product_id\"], #alm-cancel-membership-modal input[name*=\"product-id\"]").val(productId);
                    $("#alm-cancel-membership-modal input[name*=\"product_name\"], #alm-cancel-membership-modal input[name*=\"product-name\"]").val(productName);
                    
                    // Scroll to top of modal
                    $(".alm-modal-content").scrollTop(0);
                });
                
                $(".alm-modal-close").on("click", function() {
                    $("#alm-cancel-membership-modal").hide();
                });
                
                $(document).on("click", ".alm-modal-overlay", function(e) {
                    if ($(e.target).hasClass("alm-modal-overlay")) {
                        $("#alm-cancel-membership-modal").hide();
                    }
                });
                
                // Handle form submission success - close modal after a delay
                $(document).on("fluentform_submission_success", function(e, response) {
                    // Close modal after 3 seconds if form submission is successful
                    setTimeout(function() {
                        $("#alm-cancel-membership-modal").hide();
                    }, 3000);
                });
            });
            </script>';

        } else {
            if (function_exists('memb_hasAnyTags')) {
                if (memb_hasAnyTags(array(7754))) {
                    return '<p class="center bold_red">You are part of a HomeSchoolPiano membership. Billing is handled through the master account.</p>';
                }
                if (memb_hasAnyTags(array(7746))) {
                    return '<p class="center bold_red">You purchased through the HomeSchool Buyers Co-op. Please visit their site for your invoice and payment info.</p>';
                }
                if (memb_hasAnyTags(array(9661)) && empty($academy_expiration_date)) {
                    return '<p class="center bold_red" style="font-size: 18pt;">You currently have a free trial to Jazzedge Academy. <br /><a href="/signup" class="hover-black ">Click here to upgrade your membership</a>.</p>';
                }
            }
            
            if (!empty($academy_expiration_date)) {
                return '<div style="background:#fff4cc; text-align: center; padding: 15px;"><p>You have a non-recurring membership with access to Jazzedge Academy until: <strong>' . convert_infusionsoft_date($academy_expiration_date) . '</strong></p></div>';
            }

            $return = '<p class="center">You do not have any <u>active</u>, recurring, memberships in the system. Check your invoices for 1x payments. Please contact us if this is in error.</p><p class="center" style="font-size: 14pt; color: red;" >If you have access to the site, this likely means that you purchased a 1x, non-recurring membership.</p>';
        }

        return $return;
    }
}

// Initialize the plugin
new ALM_Shortcodes_Plugin();
