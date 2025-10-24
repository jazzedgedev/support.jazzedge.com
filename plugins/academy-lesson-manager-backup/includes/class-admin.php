<?php
/**
 * Admin Interface for Academy Lesson Manager
 * 
 * Provides a simple admin interface for migration and lesson management
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin {
    
    private $migration;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->migration = new ALM_Migration();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_alm_run_migration', array($this, 'ajax_run_migration'));
        add_action('wp_ajax_alm_get_stats', array($this, 'ajax_get_stats'));
        add_action('wp_ajax_alm_import_sql', array($this, 'ajax_import_sql'));
        add_action('wp_ajax_alm_debug_stats', array($this, 'ajax_debug_stats'));
        add_action('wp_ajax_alm_migrate_recordings', array($this, 'ajax_migrate_recordings'));
        add_action('wp_ajax_alm_fix_legacy_ids', array($this, 'ajax_fix_legacy_ids'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Academy Lesson Manager',
            'Lesson Manager',
            'manage_options',
            'academy-lesson-manager',
            array($this, 'admin_page'),
            'dashicons-book-alt',
            30
        );
        
        add_submenu_page(
            'academy-lesson-manager',
            'Lessons',
            'Lessons',
            'manage_options',
            'edit.php?post_type=lesson'
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_academy-lesson-manager') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_style(
            'alm-admin',
            ALM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            ALM_VERSION
        );
        
        wp_enqueue_script(
            'alm-admin',
            ALM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            ALM_VERSION,
            true
        );
        
        wp_localize_script('alm-admin', 'almAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('alm_admin_nonce')
        ));
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        $stats = $this->migration->get_stats();
        $log = $this->migration->get_log();
        ?>
        <div class="wrap">
            <h1>Academy Lesson Manager</h1>
            
            <!-- Status Overview -->
            <div class="alm-status-cards">
                <div class="alm-card">
                    <h3>Courses</h3>
                    <div class="alm-stat">
                        <span class="alm-number"><?php echo intval($stats['courses']['original']); ?></span>
                        <span class="alm-label">Original</span>
                    </div>
                    <div class="alm-stat">
                        <span class="alm-number"><?php echo intval($stats['courses']['prepared']); ?></span>
                        <span class="alm-label">Prepared</span>
                    </div>
                </div>
                
                <div class="alm-card">
                    <h3>Lessons</h3>
                    <div class="alm-stat">
                        <span class="alm-number"><?php echo intval($stats['lessons']['original']); ?></span>
                        <span class="alm-label">Original</span>
                    </div>
                    <div class="alm-stat">
                        <span class="alm-number"><?php echo intval($stats['lessons']['migrated']); ?></span>
                        <span class="alm-label">Migrated</span>
                    </div>
                </div>
                
                <div class="alm-card">
                    <h3>Chapters</h3>
                    <div class="alm-stat">
                        <span class="alm-number"><?php echo intval($stats['chapters']['original']); ?></span>
                        <span class="alm-label">Original</span>
                    </div>
                    <div class="alm-stat">
                        <span class="alm-number"><?php echo intval($stats['chapters']['migrated']); ?></span>
                        <span class="alm-label">Migrated</span>
                    </div>
                </div>
                
                <div class="alm-card">
                    <h3>Studio Events</h3>
                    <div class="alm-stat">
                        <span class="alm-number"><?php echo intval($stats['studio_events']['original']); ?></span>
                        <span class="alm-label">Original</span>
                    </div>
                    <div class="alm-stat">
                        <span class="alm-number"><?php echo intval($stats['studio_events']['migrated']); ?></span>
                        <span class="alm-label">Migrated</span>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="alm-actions">
                <button id="run-migration" class="button button-primary button-large">
                    Run Migration
                </button>
                <button id="get-stats" class="button button-secondary">
                    Refresh Stats
                </button>
                <button id="import-sql" class="button button-secondary">
                    Import Studio Events SQL
                </button>
                <button id="cleanup-legacy" class="button button-secondary" style="background: #dc3232; color: white;">
                    Cleanup Legacy Lessons
                </button>
                <button id="debug-stats" class="button button-secondary" style="background: #0073aa; color: white;">
                    Debug Stats
                </button>
                <button id="migrate-recordings" class="button button-secondary" style="background: #00a32a; color: white;">
                    Migrate Studio Recordings
                </button>
                <button id="fix-legacy-ids" class="button button-secondary" style="background: #ff6900; color: white;">
                    Fix Studio Legacy IDs
                </button>
            </div>
            
            <!-- Migration Log -->
            <div class="alm-log-section">
                <h2>Migration Log</h2>
                <div class="alm-log-controls">
                    <button id="clear-log" class="button">Clear Log</button>
                    <button id="copy-log" class="button">Copy Log</button>
                </div>
                <div id="migration-log" class="alm-log">
                    <?php foreach ($log as $message): ?>
                        <div class="alm-log-message"><?php echo esc_html($message); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- System Status -->
            <div class="alm-status">
                <h2>System Status</h2>
                <ul>
                    <li><?php echo post_type_exists('lesson') ? '✓' : '✗'; ?> lesson post type exists</li>
                    <li><?php echo class_exists('ACF') ? '✓' : '✗'; ?> ACF plugin active</li>
                    <li><?php echo $this->check_tables_exist() ? '✓' : '✗'; ?> Academy tables exist</li>
                </ul>
            </div>
            
            <!-- Debug Stats Section -->
            <div class="alm-debug-section" style="display: none; margin-top: 20px;">
                <h2>Debug Statistics</h2>
                <div id="debug-results" style="background: #f1f1f1; padding: 15px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; max-height: 400px; overflow-y: auto;"></div>
                <button id="copy-debug" class="button button-secondary" style="margin-top: 10px;">
                    Copy Debug Results
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: Run migration
     */
    public function ajax_run_migration() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $result = $this->migration->migrate_all();
        
        wp_send_json(array(
            'success' => $result,
            'log' => $this->migration->get_log(),
            'stats' => $this->migration->get_stats()
        ));
    }
    
    /**
     * AJAX: Get stats
     */
    public function ajax_get_stats() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        wp_send_json(array(
            'success' => true,
            'stats' => $this->migration->get_stats()
        ));
    }
    
    /**
     * AJAX: Import SQL file
     */
    public function ajax_import_sql() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // For now, we'll use a hardcoded path - you can upload the file to the plugin directory
        $sql_file_path = ALM_PLUGIN_DIR . 'studio-events-import.sql';
        
        // Copy the uploaded file to plugin directory
        if (isset($_FILES['sql_file']) && $_FILES['sql_file']['error'] === UPLOAD_ERR_OK) {
            $uploaded_file = $_FILES['sql_file']['tmp_name'];
            $target_path = ALM_PLUGIN_DIR . 'studio-events-import.sql';
            
            if (move_uploaded_file($uploaded_file, $target_path)) {
                $sql_file_path = $target_path;
            }
        }
        
        $result = $this->migration->import_studio_events_from_sql($sql_file_path);
        
        wp_send_json(array(
            'success' => $result,
            'log' => $this->migration->get_log(),
            'stats' => $this->migration->get_stats()
        ));
    }
    
    /**
     * AJAX: Debug stats
     */
    public function ajax_debug_stats() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        
        $debug_results = "=== ACADEMY LESSON MANAGER DEBUG STATS ===\n\n";
        
        // 1. Lesson counts by source
        $debug_results .= "1. LESSON COUNTS BY SOURCE:\n";
        $lesson_sources = $wpdb->get_results("
            SELECT 
                COALESCE(pm.meta_value, 'NULL') as source,
                COUNT(*) as count
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_lesson_source'
            WHERE p.post_type = 'lesson'
            GROUP BY pm.meta_value
        ");
        
        foreach ($lesson_sources as $source) {
            $debug_results .= "   {$source->source}: {$source->count}\n";
        }
        
        // 2. Total lesson count
        $debug_results .= "\n2. TOTAL LESSON COUNT:\n";
        $total_lessons = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'lesson'");
        $debug_results .= "   Total lessons: {$total_lessons}\n";
        
        // 3. Studio events metadata
        $debug_results .= "\n3. STUDIO EVENTS METADATA:\n";
        $studio_events_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_lesson_legacy_type' AND meta_value = 'studio-event'");
        $debug_results .= "   Studio events metadata: {$studio_events_count}\n";
        
        // 4. Chapters metadata - DETAILED DEBUGGING
        $debug_results .= "\n4. CHAPTERS METADATA (DETAILED):\n";
        $chapters_meta_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_lesson_chapters'");
        $debug_results .= "   Total _lesson_chapters meta entries: {$chapters_meta_count}\n";
        
        // Count actual chapters within the metadata
        $total_chapters_in_meta = 0;
        $lessons_with_chapters = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_lesson_chapters'");
        foreach ($lessons_with_chapters as $lesson) {
            $chapters_array = maybe_unserialize($lesson->meta_value);
            if (is_array($chapters_array)) {
                $total_chapters_in_meta += count($chapters_array);
            }
        }
        $debug_results .= "   Total chapters counted from meta: {$total_chapters_in_meta}\n";
        
        // Sample of chapter metadata
        $debug_results .= "   Sample chapter metadata (first 3):\n";
        $sample_chapters = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_lesson_chapters' LIMIT 3");
        foreach ($sample_chapters as $sample) {
            $chapters_array = maybe_unserialize($sample->meta_value);
            $chapter_count = is_array($chapters_array) ? count($chapters_array) : 0;
            $debug_results .= "   - Post ID {$sample->post_id}: {$chapter_count} chapters\n";
        }
        
        // 5. Original academy table counts
        $debug_results .= "\n5. ORIGINAL ACADEMY TABLE COUNTS:\n";
        $academy_lessons = $wpdb->get_var("SELECT COUNT(*) FROM academy_lessons");
        $academy_courses = $wpdb->get_var("SELECT COUNT(*) FROM academy_courses");
        $academy_chapters = $wpdb->get_var("SELECT COUNT(*) FROM academy_chapters");
        $debug_results .= "   Academy lessons: {$academy_lessons}\n";
        $debug_results .= "   Academy courses: {$academy_courses}\n";
        $debug_results .= "   Academy chapters: {$academy_chapters}\n";
        
        // 6. Studio event posts
        $debug_results .= "\n6. STUDIO EVENT POSTS:\n";
        $studio_event_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'studio-event'");
        $debug_results .= "   Studio event posts: {$studio_event_posts}\n";
        
        // 7. Plugin stats
        $debug_results .= "\n7. PLUGIN STATS:\n";
        $plugin_stats = $this->migration->get_stats();
        foreach ($plugin_stats as $type => $stats) {
            $debug_results .= "   {$type}: {$stats['original']} original, {$stats['migrated']} migrated\n";
        }
        
        $debug_results .= "\n9. STUDIO EVENT RECORDINGS DEBUG:\n";
        $recordings_count = $wpdb->get_var("SELECT COUNT(*) FROM academy_event_recordings");
        $debug_results .= "   Total recordings in academy_event_recordings: {$recordings_count}\n";
        
        // Check how many recordings have matching lessons
        $recordings_with_lessons = 0;
        $recordings_without_lessons = 0;
        $sample_recordings = $wpdb->get_results("SELECT event_id, title FROM academy_event_recordings LIMIT 10");
        
        foreach ($sample_recordings as $recording) {
            $lesson_exists = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) FROM {$wpdb->posts} p
                JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'lesson'
                AND pm.meta_key = '_lesson_legacy_id'
                AND pm.meta_value = %d
            ", $recording->event_id));
            
            if ($lesson_exists) {
                $recordings_with_lessons++;
            } else {
                $recordings_without_lessons++;
            }
        }
        
        $debug_results .= "   Sample check (first 10): {$recordings_with_lessons} have matching lessons, {$recordings_without_lessons} don't\n";
        
        // Show sample event_ids
        $debug_results .= "   Sample event_ids from recordings: ";
        $event_ids = $wpdb->get_col("SELECT DISTINCT event_id FROM academy_event_recordings ORDER BY event_id LIMIT 5");
        $debug_results .= implode(', ', $event_ids) . "\n";
        
        // Show sample legacy_ids from lessons
        $debug_results .= "   Sample legacy_ids from lessons: ";
        $legacy_ids = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_lesson_legacy_id' ORDER BY meta_value LIMIT 5");
        $debug_results .= implode(', ', $legacy_ids) . "\n";
        
        // Show sample legacy_ids from studio events specifically
        $debug_results .= "   Sample legacy_ids from studio events: ";
        $studio_legacy_ids = $wpdb->get_col("
            SELECT DISTINCT pm.meta_value 
            FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->postmeta} pm2 ON pm.post_id = pm2.post_id
            WHERE pm.meta_key = '_lesson_legacy_id'
            AND pm2.meta_key = '_lesson_legacy_type'
            AND pm2.meta_value = 'studio-event'
            ORDER BY CAST(pm.meta_value AS UNSIGNED)
            LIMIT 5
        ");
        $debug_results .= implode(', ', $studio_legacy_ids) . "\n";
        
        // Check if any studio events have legacy_id metadata at all
        $debug_results .= "   Studio events with legacy_id metadata: ";
        $studio_with_legacy_id = $wpdb->get_var("
            SELECT COUNT(DISTINCT pm.post_id)
            FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->postmeta} pm2 ON pm.post_id = pm2.post_id
            WHERE pm.meta_key = '_lesson_legacy_id'
            AND pm2.meta_key = '_lesson_legacy_type'
            AND pm2.meta_value = 'studio-event'
        ");
        $debug_results .= $studio_with_legacy_id . "\n";
        
        $debug_results .= "\n=== END DEBUG STATS ===\n";
        
        wp_send_json(array(
            'success' => true,
            'debug_results' => $debug_results
        ));
    }
    
    /**
     * AJAX: Migrate studio recordings
     */
    public function ajax_migrate_recordings() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $result = $this->migration->migrate_studio_event_recordings();
        
        wp_send_json(array(
            'success' => $result !== false,
            'log' => $this->migration->get_log(),
            'stats' => $this->migration->get_stats()
        ));
    }
    
    /**
     * AJAX: Fix studio legacy IDs
     */
    public function ajax_fix_legacy_ids() {
        // Add error logging
        error_log('AJAX ajax_fix_legacy_ids called');
        
        try {
            check_ajax_referer('alm_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                error_log('User not authorized');
                wp_die('Unauthorized');
            }
            
            error_log('About to call fix_studio_event_legacy_ids');
            $result = $this->migration->fix_studio_event_legacy_ids();
            error_log('fix_studio_event_legacy_ids returned: ' . var_export($result, true));
            
            wp_send_json(array(
                'success' => $result !== false,
                'log' => $this->migration->get_log(),
                'stats' => $this->migration->get_stats()
            ));
            
        } catch (Exception $e) {
            error_log('Exception in ajax_fix_legacy_ids: ' . $e->getMessage());
            wp_send_json(array(
                'success' => false,
                'error' => $e->getMessage(),
                'log' => array('Exception: ' . $e->getMessage())
            ));
        }
    }
    
    /**
     * AJAX: Cleanup legacy lessons
     */
    public function ajax_cleanup_legacy() {
        check_ajax_referer('alm_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $deleted = $this->migration->cleanup_legacy_lessons();
        
        wp_send_json(array(
            'success' => true,
            'deleted' => $deleted,
            'log' => $this->migration->get_log()
        ));
    }
    
    /**
     * Check if academy tables exist
     */
    private function check_tables_exist() {
        global $wpdb;
        
        $tables = array('academy_lessons', 'academy_courses', 'academy_chapters');
        
        foreach ($tables as $table) {
            $result = $wpdb->get_var("SHOW TABLES LIKE '{$table}'");
            if (!$result) {
                return false;
            }
        }
        
        return true;
    }
}
