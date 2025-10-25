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
define('ALM_SHORTCODES_VERSION', '1.0.0');
define('ALM_SHORTCODES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALM_SHORTCODES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once ALM_SHORTCODES_PLUGIN_DIR . 'includes/class-chapter-handler.php';

class ALM_Shortcodes_Plugin {
    
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
    
    public function init() {
        // Register shortcodes
        add_shortcode('alm_test', array($this, 'test_shortcode'));
        add_shortcode('alm_lesson_video', array($this, 'lesson_video_shortcode'));
        add_shortcode('alm_lesson_chapters', array($this, 'lesson_chapters_shortcode'));
        add_shortcode('alm_lesson_complete', array($this, 'lesson_complete_shortcode'));
        add_shortcode('alm_lesson_resources', array($this, 'lesson_resources_shortcode'));
        add_shortcode('alm_lesson_progress', array($this, 'lesson_progress_shortcode'));
        add_shortcode('alm_mark_complete', array($this, 'mark_complete_shortcode'));
        
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
            wp_enqueue_script(
                'alm-shortcodes-frontend',
                ALM_SHORTCODES_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
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
        
        if ($user_level < $lesson_level) {
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
        
        // Use fvplayer shortcode with splash screen
        return do_shortcode('[fvplayer src="' . esc_url($video_url) . '" width="100%" height="400" splash="https://jazzedge.academy/wp-content/uploads/2023/12/splash-play-video.jpg"]');
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
                    $return .= '<span class="alm-play-icon">▶</span>';
                } else {
                    $return .= '<span class="alm-play-icon">▶</span>';
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
            
            // Get all lessons in this collection for navigation
            $collection_lessons = $wpdb->get_results($wpdb->prepare(
                "SELECT ID, lesson_title, slug FROM {$wpdb->prefix}alm_lessons WHERE collection_id = %d ORDER BY ID ASC",
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
            
            // Calculate and display progress percentage
            $progress_percentage = 0;
            if (function_exists('je_return_lesson_progress_percentage')) {
                $progress_percentage = je_return_lesson_progress_percentage($atts['lesson_id'], 'percent');
            }
            
            $return .= '<div class="alm-progress-badge">' . intval($progress_percentage) . '% Complete</div>';
            $return .= '</div>';
            $return .= do_shortcode('[fvplayer src="' . esc_url($video_url) . '" width="100%" height="600" splash="https://jazzedge.academy/wp-content/uploads/2023/12/splash-play-video.jpg"]');
            
            // Add buttons and progress in 3-column layout
            $return .= '<div class="alm-actions-section' . (!$has_access ? ' alm-restricted' : '') . '">';
            
            if ($has_access) {
                // Left: Mark Complete Button
                $return .= '<div class="alm-action-left">';
                $return .= do_shortcode('[alm_mark_complete lesson_id="' . $atts['lesson_id'] . '" chapter_id="' . $final_chapter_id . '" type="chapter"]');
                $return .= '</div>';
                
                // Center: Progress Bar
                $return .= '<div class="alm-action-center">';
                $return .= '<div class="alm-progress-bar">';
                $return .= '<div class="alm-progress-fill" style="width: ' . $progress_percentage . '%"></div>';
                $return .= '</div>';
                $return .= '<div class="alm-progress-text">Progress: ' . $completed_chapters . ' of ' . $total_chapters . ' chapters completed</div>';
                $return .= '</div>';
                
                // Right: Save Favorite Button
                $return .= '<div class="alm-action-right">';
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
        } elseif (!$has_access && !empty($chapters)) {
            // PREVIEW MODE: Show shortest chapter as preview for users without access
            $shortest_chapter = null;
            $shortest_duration = PHP_INT_MAX;
            
            // Find the shortest chapter
            foreach ($chapters as $chapter) {
                $duration = intval($chapter->duration);
                if ($duration > 0 && $duration < $shortest_duration) {
                    $shortest_duration = $duration;
                    $shortest_chapter = $chapter;
                }
            }
            
            if ($shortest_chapter) {
                // Get video URL for shortest chapter
                $preview_video_url = '';
                if (!empty($shortest_chapter->bunny_url)) {
                    $preview_video_url = $shortest_chapter->bunny_url;
                } elseif (!empty($shortest_chapter->youtube_id)) {
                    $preview_video_url = 'https://www.youtube.com/watch?v=' . $shortest_chapter->youtube_id;
                } elseif (!empty($shortest_chapter->vimeo_id)) {
                    $preview_video_url = 'https://vimeo.com/' . $shortest_chapter->vimeo_id;
                }
                
                if (!empty($preview_video_url)) {
                    $return .= '<div class="alm-video-section">';
                    $lesson_title = stripslashes($lesson->lesson_title);
                    $chapter_title = stripslashes($shortest_chapter->chapter_title);
                    $return .= '<div class="alm-video-title-bar">';
                    $return .= '<div style="display: flex; align-items: center; gap: 8px;">';
                    $return .= '<span class="alm-lesson-name">' . esc_html($lesson_title) . '</span>';
                    $return .= '<span class="alm-chapter-name">(' . esc_html($chapter_title) . ' - Preview)</span>';
                    $return .= '</div>';
                    
                    // Calculate and display progress percentage
                    $progress_percentage = 0;
                    if (function_exists('je_return_lesson_progress_percentage')) {
                        $progress_percentage = je_return_lesson_progress_percentage($atts['lesson_id'], 'percent');
                    }
                    
                    $return .= '<div class="alm-progress-badge">' . intval($progress_percentage) . '% Complete</div>';
                    $return .= '</div>';
                    
                    // Add preview notice
                    $return .= '<div style="background: #fff3cd; color: #856404; padding: 12px 20px; border-bottom: 1px solid #ffeaa7; font-size: 14px; font-weight: 500;">';
                    $return .= '🎬 <strong>Preview Mode:</strong> This video will stop after 2 minutes. Upgrade to ' . esc_html($required_level_name) . ' for full access.';
                    $return .= '</div>';
                    
                    // Add the video player with preview JavaScript
                    $return .= do_shortcode('[fvplayer src="' . esc_url($preview_video_url) . '" width="100%" height="600" splash="https://jazzedge.academy/wp-content/uploads/2023/12/splash-play-video.jpg"]');
                    
                    // Add preview control JavaScript
                    $return .= '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        // Wait for FV Player to load
                        if (typeof(flowplayer) != "undefined") {
                            flowplayer(function(api, root) {
                                var previewPaused = false;
                                api.bind("ready", function(e, api, video) {
                                    console.log("Preview video loaded - will stop after 2 minutes");
                                });
                                api.bind("progress", function(e, api, time) {
                                    if (!previewPaused && time > 120) { // 2 minutes = 120 seconds
                                        api.pause();
                                        previewPaused = true;
                                        
                                        // Show upgrade message
                                        var upgradeDiv = document.createElement("div");
                                        upgradeDiv.style.cssText = "position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.9); color: white; padding: 30px; border-radius: 10px; text-align: center; z-index: 1000; max-width: 400px;";
                                        upgradeDiv.innerHTML = `
                                            <h3 style="margin: 0 0 15px 0; color: #ff6b35;">Preview Complete</h3>
                                            <p style="margin: 0 0 20px 0; font-size: 16px;">You\'ve watched 2 minutes of this lesson.</p>
                                            <a href="/upgrade" style="background: linear-gradient(135deg, #ff6b35 0%, #ff4500 100%); color: white; padding: 15px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; display: inline-block;">Upgrade to ' . esc_html($required_level_name) . '</a>
                                        `;
                                        
                                        // Add to video container
                                        var videoContainer = root.querySelector(".flowplayer");
                                        if (videoContainer) {
                                            videoContainer.style.position = "relative";
                                            videoContainer.appendChild(upgradeDiv);
                                        }
                                    }
                                });
                            });
                        }
                    });
                    </script>';
                    
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
                    $return .= '</div>';
                } else {
                    // Fallback to placeholder if no video URL
                    $return .= '<div class="alm-video-section">';
                    $lesson_title = stripslashes($lesson->lesson_title);
                    $chapter_title = stripslashes($shortest_chapter->chapter_title);
                    $return .= '<div class="alm-video-title-bar">';
                    $return .= '<div style="display: flex; align-items: center; gap: 8px;">';
                    $return .= '<span class="alm-lesson-name">' . esc_html($lesson_title) . '</span>';
                    $return .= '<span class="alm-chapter-name">(' . esc_html($chapter_title) . ')</span>';
                    $return .= '</div>';
                    
                    // Calculate and display progress percentage
                    $progress_percentage = 0;
                    if (function_exists('je_return_lesson_progress_percentage')) {
                        $progress_percentage = je_return_lesson_progress_percentage($atts['lesson_id'], 'percent');
                    }
                    
                    $return .= '<div class="alm-progress-badge">' . intval($progress_percentage) . '% Complete</div>';
                    $return .= '</div>';
                    $return .= '<div class="alm-video-placeholder">';
                    $return .= '<div class="alm-restricted-overlay">';
                    $return .= '<svg style="width: 48px; height: 48px; margin-bottom: 16px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>';
                    $return .= '<h3>Video Preview</h3>';
                    $return .= '<p>Watch the full lesson with ' . esc_html($required_level_name) . ' membership</p>';
                    $return .= '<a href="/upgrade" class="alm-upgrade-button">Upgrade to ' . esc_html($required_level_name) . '</a>';
                    $return .= '</div>';
                    $return .= '</div>';
                    $return .= '</div>';
                }
            } else {
                // Fallback to original placeholder
                $return .= '<div class="alm-video-section">';
                $lesson_title = stripslashes($lesson->lesson_title);
                $chapter_title = stripslashes($current_chapter->chapter_title);
                $return .= '<div class="alm-video-title-bar">';
                $return .= '<span class="alm-lesson-name">' . esc_html($lesson_title) . '</span>';
                $return .= '<span class="alm-chapter-name">(' . esc_html($chapter_title) . ')</span>';
                $return .= '</div>';
                $return .= '<div class="alm-video-placeholder">';
                $return .= '<div class="alm-restricted-overlay">';
                $return .= '<svg style="width: 48px; height: 48px; margin-bottom: 16px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>';
                $return .= '<h3>Video Preview</h3>';
                $return .= '<p>Watch the full lesson with ' . esc_html($required_level_name) . ' membership</p>';
                $return .= '<a href="/upgrade" class="alm-upgrade-button">Upgrade to ' . esc_html($required_level_name) . '</a>';
                $return .= '</div>';
                $return .= '</div>';
                $return .= '</div>';
            }
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
            $return .= '<span class="alm-play-icon">▶</span>';
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
            $return .= '<p class="alm-card-description">' . esc_html(stripslashes($lesson->lesson_description)) . '</p>';
        }
        
        $return .= '</div>';
        $return .= '</div>';
        
        // Collection Details Card (if collection exists)
        if ($collection) {
            $return .= '<div class="alm-sidebar-card">';
            $return .= '<div class="alm-card-header alm-collection-header">LESSON COLLECTION DETAILS</div>';
            $return .= '<div class="alm-card-content">';
            $return .= '<h3 class="alm-card-title">' . esc_html(stripslashes($collection->collection_title)) . '</h3>';
            if (!empty($collection->collection_description)) {
                $return .= '<p class="alm-card-description">' . esc_html(stripslashes($collection->collection_description)) . '</p>';
            }
            
            // Lesson navigation dropdown (restricted)
            if (!empty($collection_lessons) && count($collection_lessons) > 1 && $has_access) {
                $return .= '<div class="alm-lesson-nav">';
                $return .= '<select class="alm-lesson-selector" onchange="javascript:location.href = this.value;">';
                $return .= '<option value="">Lessons...</option>';
                
                foreach ($collection_lessons as $nav_lesson) {
                    // Get lesson post_id from postmeta
                    $lesson_post_id = $wpdb->get_var($wpdb->prepare(
                        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'lesson_id' AND meta_value = %d",
                        $nav_lesson->ID
                    ));
                    
                    // Determine chapter slug
                    $chapter_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(ID) FROM {$wpdb->prefix}alm_chapters WHERE lesson_id = %d",
                        $nav_lesson->ID
                    ));
                    
                    if ($chapter_count == 1) {
                        $chapter_slug = $wpdb->get_var($wpdb->prepare(
                            "SELECT slug FROM {$wpdb->prefix}alm_chapters WHERE lesson_id = %d",
                            $nav_lesson->ID
                        ));
                    } else {
                        $chapter_slug = $nav_lesson->slug;
                    }
                    
                    // Check if lesson is completed
                    $lesson_complete = '';
                    if (function_exists('je_is_lesson_marked_complete')) {
                        $lesson_complete = je_is_lesson_marked_complete($nav_lesson->ID) ? ' (done)' : '';
                    }
                    
                    // Build URL
                    $permalink = get_permalink($lesson_post_id);
                    $lesson_url = $permalink . '?c=' . $chapter_slug;
                    
                    // Mark current lesson
                    $mark = ($nav_lesson->ID == $atts['lesson_id']) ? '» ' : '';
                    
                    // Format title
                    $lesson_title = stripslashes($nav_lesson->lesson_title);
                    $trimmed_title = mb_strimwidth($lesson_title, 0, 28, '...');
                    
                    $selected = ($nav_lesson->ID == $atts['lesson_id']) ? ' selected' : '';
                    $return .= '<option value="' . esc_url($lesson_url) . '"' . $selected . '>' . $mark . esc_html($trimmed_title) . $lesson_complete . '</option>';
                }
                
                $return .= '</select>';
                $return .= '</div>';
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
        
        if ($user_level < $lesson_level) {
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
                        <p><a href="#" class="show-modal-sheet-music-sample hover-black">View a sheet music sample</a></p>
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
        $return .= '<ul class="alm-resources-list">';
        
        $found_resources = false;
        $note_content = '';
        
        foreach ($resources as $k => $v) {
            if (empty($v)) {
                continue;
            }
            
            // Handle both old (string) and new (array) resource formats
            $resource_url = '';
            if (is_array($v)) {
                $resource_url = isset($v['url']) ? $v['url'] : '';
            } else {
                $resource_url = $v;
            }
            
            if (empty($resource_url)) {
                continue;
            }
            
            // Filter out unwanted resource types
            $resource_type = substr($k, 0, 3);
            $excluded_types = ['map', 'mp3', 'mid', 'zip'];
            if (in_array(strtolower($k), $excluded_types) || in_array($resource_type, $excluded_types)) {
                continue;
            }
            
            $found_resources = true;
            
            // Get resource type and icon
            $icon = '';
            $resource_name = '';
            
            switch ($resource_type) {
                case 'ire':
                    $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>';
                    $resource_name = 'iRealPro';
                    break;
                case 'pdf':
                    $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>';
                    $resource_name = 'Sheet Music';
                    break;
                case 'jam':
                    $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.369 4.369 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/></svg>';
                    $resource_name = 'Backing Track';
                    break;
                case 'cal':
                    $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/></svg>';
                    $resource_name = 'Call & Response';
                    break;
                case 'zip':
                    $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
                    $resource_name = 'Zip File';
                    break;
            }
            
            // Handle notes separately
            if ($k === 'note') {
                $note_content = '<li class="alm-resource-item note"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg> ' . esc_html($resource_url) . '</li>';
                continue;
            }
            
            // Skip zip files (as in original)
            if ($resource_name === 'Zip File') {
                continue;
            }
            
            // Build favorite/unfavorite links
            $favorite_link = '';
            if (function_exists('je_is_resource_favorited')) {
                $is_favorited = $wpdb->get_var($wpdb->prepare(
                    "SELECT user_id FROM {$wpdb->prefix}academy_favorites WHERE course_or_lesson_id = %d AND type = 'resource' AND user_id = %d AND link = %s",
                    $atts['lesson_id'],
                    $user_id,
                    $resource_url
                ));
                
                if (!empty($is_favorited)) {
                    $favorite_link = ' <span class="unfavorite_resource"><a href="/willie/favorite_resource.php?action=unfavorite&link_type=' . $k . '&lesson_id=' . $atts['lesson_id'] . '"><i class="fa-solid fa-folder-minus"></i></a></span>';
                } else {
                    $favorite_link = ' <span class="favorite_resource"><a href="/willie/favorite_resource.php?action=favorite&link_type=' . $k . '&lesson_id=' . $atts['lesson_id'] . '&v=' . urlencode($resource_url) . '"><i class="fa-solid fa-folder-plus"></i></a></span>';
                }
            }
            
            // Build resource link
            $final_resource_url = 'https://jazzedge.academy/je_link.php?id=' . $atts['lesson_id'] . '&link=' . urlencode($resource_url);
            
            $return .= '<li class="alm-resource-item">';
            $return .= $icon . ' ';
            $return .= '<a href="' . esc_url($final_resource_url) . '" class="alm-resource-link" target="_blank">' . esc_html($resource_name) . '</a>';
            $return .= ' <span class="alm-resource-type">' . esc_html($k) . '</span>';
            $return .= $favorite_link;
            $return .= '</li>';
        }
        
        // Add note content if exists
        if (!empty($note_content)) {
            $return .= $note_content;
        }
        
        $return .= '</ul>';
        $return .= '</div>'; // End alm-card-content
        $return .= '</div>'; // End alm-sidebar-card
        
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
}

// Initialize the plugin
new ALM_Shortcodes_Plugin();
