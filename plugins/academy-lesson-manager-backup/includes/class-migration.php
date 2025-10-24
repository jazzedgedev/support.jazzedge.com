<?php
/**
 * Simple Migration Class for Academy Lesson Manager
 * 
 * Handles migration from academy tables to lesson posts with ACF fields
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Migration {
    
    private $log_messages = array();
    
    /**
     * Run the complete migration
     */
    public function migrate_all() {
        $this->log("Starting Academy Lesson Migration...");
        
        try {
            // Phase 1: Migrate courses (prepare data for ACF)
            $this->migrate_courses();
            
            // Phase 2: Migrate lessons
            $this->migrate_lessons();
            
            // Phase 3: Migrate studio events
            $this->migrate_studio_events();
            
            // Phase 4: Migrate chapters
            $this->migrate_chapters();
            
            // Phase 5: Migrate studio event recordings
            $this->migrate_studio_event_recordings();
            
            $this->log("Migration completed successfully!");
            return true;
            
        } catch (Exception $e) {
            $this->log("Migration failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Migrate academy_courses data (prepare for ACF)
     */
    private function migrate_courses() {
        global $wpdb;
        
        $this->log("Phase 1: Preparing course data...");
        
        $courses = $wpdb->get_results("SELECT * FROM academy_courses ORDER BY menu_order");
        
        foreach ($courses as $course) {
            // Store course data for ACF field mapping
            $this->course_data[$course->ID] = array(
                'title' => $course->course_title,
                'description' => $course->course_description,
                'menu_order' => $course->menu_order,
                'legacy_id' => $course->ID
            );
        }
        
        $this->log("Prepared " . count($courses) . " courses for ACF mapping");
    }
    
    /**
     * Migrate academy_lessons to lesson posts
     */
    private function migrate_lessons() {
        global $wpdb;
        
        $this->log("Phase 2: Migrating lessons...");
        
        $lessons = $wpdb->get_results("SELECT * FROM academy_lessons ORDER BY course_sort_order");
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($lessons as $lesson) {
            // Check if lesson already exists (by slug)
            $existing = get_page_by_path($lesson->slug, OBJECT, 'lesson');
            
            if ($existing) {
                // Mark existing lesson as legacy
                update_post_meta($existing->ID, '_lesson_source', 'legacy');
                $skipped++;
                continue;
            }
            
            // Create new lesson post
            $post_data = array(
                'post_title' => $lesson->lesson_title,
                'post_content' => $lesson->lesson_description,
                'post_name' => $lesson->slug,
                'post_type' => 'lesson',
                'post_status' => 'publish',
                'post_date' => $lesson->post_date
            );
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id) {
                // Add legacy metadata
                update_post_meta($post_id, '_lesson_source', 'migrated');
                update_post_meta($post_id, '_lesson_legacy_id', $lesson->ID);
                update_post_meta($post_id, '_lesson_legacy_course_id', $lesson->course_id);
                
                // Add ACF fields
                $this->update_acf_fields($post_id, $lesson);
                
                $migrated++;
            }
        }
        
        $this->log("Migrated {$migrated} lessons, skipped {$skipped} existing");
    }
    
    /**
     * Migrate studio-event posts to lesson posts
     */
    private function migrate_studio_events() {
        $this->log("Phase 3: Migrating studio events...");
        
        $events = get_posts(array(
            'post_type' => 'studio-event',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_lesson_legacy_type',
                    'compare' => 'NOT EXISTS'
                )
            )
        ));
        
        $migrated = 0;
        
        foreach ($events as $event) {
            // Check if lesson already exists
            $existing = get_page_by_path($event->post_name, OBJECT, 'lesson');
            
            if ($existing) {
                update_post_meta($existing->ID, '_lesson_source', 'legacy');
                continue;
            }
            
            // Create lesson post
            $post_data = array(
                'post_title' => $event->post_title,
                'post_content' => $event->post_content,
                'post_name' => $event->post_name,
                'post_type' => 'lesson',
                'post_status' => 'publish',
                'post_date' => $event->post_date
            );
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id) {
                // Add metadata
                update_post_meta($post_id, '_lesson_source', 'migrated');
                update_post_meta($post_id, '_lesson_legacy_type', 'studio-event');
                update_post_meta($post_id, '_lesson_legacy_id', $event->ID);
                
                // Mark studio event as migrated
                update_post_meta($event->ID, '_lesson_legacy_type', 'studio-event');
                
                $migrated++;
            }
        }
        
        $this->log("Migrated {$migrated} studio events");
    }
    
    /**
     * Migrate academy_chapters to lesson meta
     */
    private function migrate_chapters() {
        global $wpdb;
        
        $this->log("Phase 4: Migrating chapters...");
        
        // Get all migrated lessons
        $lessons = get_posts(array(
            'post_type' => 'lesson',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_lesson_legacy_id',
                    'compare' => 'EXISTS'
                )
            )
        ));
        
        $chapters_migrated = 0;
        
        foreach ($lessons as $lesson) {
            $legacy_id = get_post_meta($lesson->ID, '_lesson_legacy_id', true);
            
            // Get chapters for this lesson
            $chapters = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM academy_chapters WHERE lesson_id = %d ORDER BY menu_order",
                $legacy_id
            ));
            
            if ($chapters) {
                $chapter_data = array();
                
                foreach ($chapters as $chapter) {
                    $chapter_data[] = array(
                        'title' => $chapter->chapter_title,
                        'vimeo_id' => $chapter->vimeo_id,
                        'youtube_id' => $chapter->youtube_id,
                        'duration' => $chapter->duration,
                        'menu_order' => $chapter->menu_order,
                        'slug' => $chapter->slug
                    );
                }
                
                // Store chapters as serialized meta
                update_post_meta($lesson->ID, '_lesson_chapters', $chapter_data);
                $chapters_migrated += count($chapters);
            }
        }
        
        $this->log("Migrated {$chapters_migrated} chapters");
    }
    
    /**
     * Fix missing legacy_id metadata for studio events
     */
    public function fix_studio_event_legacy_ids() {
        global $wpdb;
        
        $this->log("=== STARTING fix_studio_event_legacy_ids ===");
        $this->log("Fixing missing legacy_id metadata for studio events...");
        
        try {
            // Try to read the SQL file from the server
            $sql_file_paths = array(
                ABSPATH . 'wp_posts-studio-events-with-meta.sql',
                plugin_dir_path(__FILE__) . '../../wp_posts-studio-events-with-meta.sql',
                '/Users/williemyette/dev/support.jazzedge.com/wp_posts-studio-events-with-meta.sql',
                '/nas/content/live/jazzacademy/wp_posts-studio-events-with-meta.sql'
            );
            
            $sql_file_path = null;
            foreach ($sql_file_paths as $path) {
                $this->log("Checking path: {$path}");
                if (file_exists($path)) {
                    $sql_file_path = $path;
                    break;
                }
            }
            
            if (!$sql_file_path) {
                $this->log("SQL file not found. Please upload wp_posts-studio-events-with-meta.sql to your WordPress root directory.");
                return false;
            }
            
            $this->log("Found SQL file: {$sql_file_path}");
            
            // Read and parse the SQL file
            $sql_content = file_get_contents($sql_file_path);
            $this->log("SQL file size: " . strlen($sql_content) . " bytes");
            
            // Extract studio event data using regex
            $original_events = array();
            preg_match_all('/INSERT INTO `?p`?\s+VALUES\s*\(([^)]+)\);/', $sql_content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $values = $match[1];
                // Parse the VALUES to extract ID and post_name
                // ID is first value, post_name is 3rd value (after title)
                $parts = str_getcsv($values, ',', "'");
                if (count($parts) >= 3 && isset($parts[0]) && isset($parts[2])) {
                    $id = trim($parts[0], "'");
                    $post_name = trim($parts[2], "'");
                    $original_events[$post_name] = $id;
                }
            }
            
            $this->log("Parsed " . count($original_events) . " original studio events from SQL");
            
            // Show first few examples
            $sample_count = 0;
            foreach ($original_events as $slug => $id) {
                if ($sample_count < 3) {
                    $this->log("Sample: {$slug} -> {$id}");
                    $sample_count++;
                }
            }
            
            // Find all studio events that are missing legacy_id metadata
            $studio_events = $wpdb->get_results("
                SELECT p.ID, p.post_name
                FROM {$wpdb->posts} p
                JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'lesson'
                AND pm.meta_key = '_lesson_legacy_type'
                AND pm.meta_value = 'studio-event'
                AND p.ID NOT IN (
                    SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_lesson_legacy_id'
                )
            ");
            
            $this->log("Found " . count($studio_events) . " studio events missing legacy_id metadata");
            
            $fixed = 0;
            foreach ($studio_events as $event) {
                if (isset($original_events[$event->post_name])) {
                    $original_id = $original_events[$event->post_name];
                    update_post_meta($event->ID, '_lesson_legacy_id', $original_id);
                    $fixed++;
                    $this->log("Fixed legacy_id for lesson {$event->ID} -> {$original_id} ({$event->post_name})");
                } else {
                    $this->log("Could not find original studio-event for lesson {$event->ID} ({$event->post_name})");
                }
            }
            
            $this->log("Fixed {$fixed} studio event legacy IDs");
            return $fixed;
            
        } catch (Exception $e) {
            $this->log("Error in fix_studio_event_legacy_ids: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Migrate studio event recordings to lesson chapters
     */
    public function migrate_studio_event_recordings() {
        global $wpdb;
        
        $this->log("Phase 5: Migrating studio event recordings...");
        
        // Get all studio event recordings
        $recordings = $wpdb->get_results("SELECT * FROM academy_event_recordings ORDER BY event_id, ID");
        
        $this->log("Found " . count($recordings) . " studio event recordings to process");
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($recordings as $recording) {
            // First, try to find lesson by legacy ID (original studio-event post ID)
            $lesson_posts = $wpdb->get_results($wpdb->prepare("
                SELECT p.ID 
                FROM {$wpdb->posts} p
                JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'lesson'
                AND pm.meta_key = '_lesson_legacy_id'
                AND pm.meta_value = %d
            ", $recording->event_id));
            
            // If not found by legacy ID, try to find by legacy type (studio-event)
            if (empty($lesson_posts)) {
                $lesson_posts = $wpdb->get_results($wpdb->prepare("
                    SELECT p.ID 
                    FROM {$wpdb->posts} p
                    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                    WHERE p.post_type = 'lesson'
                    AND pm.meta_key = '_lesson_legacy_type'
                    AND pm.meta_value = 'studio-event'
                    AND p.ID IN (
                        SELECT post_id FROM {$wpdb->postmeta} 
                        WHERE meta_key = '_lesson_legacy_id' 
                        AND meta_value = %d
                    )
                ", $recording->event_id));
            }
            
            if (!empty($lesson_posts)) {
                $lesson_post_id = $lesson_posts[0]->ID;
                
                // Get existing chapters or create new array
                $existing_chapters = get_post_meta($lesson_post_id, '_lesson_chapters', true);
                if (!is_array($existing_chapters)) {
                    $existing_chapters = array();
                }
                
                // Add recording as chapter
                $existing_chapters[] = array(
                    'title' => $recording->title,
                    'content' => $recording->assignment ?: '',
                    'vimeo_id' => $recording->vimeo_id,
                    'duration' => $recording->duration,
                    'class_type' => $recording->class_type,
                    'date' => $recording->date,
                    'order' => count($existing_chapters) + 1
                );
                
                // Save chapters
                update_post_meta($lesson_post_id, '_lesson_chapters', $existing_chapters);
                $migrated++;
                
                $this->log("Added recording '{$recording->title}' to lesson {$lesson_post_id}");
            } else {
                $skipped++;
                $this->log("Skipped recording '{$recording->title}' - no matching lesson found for event_id {$recording->event_id}");
            }
        }
        
        $this->log("Migrated {$migrated} studio event recordings, skipped {$skipped}");
        return $migrated;
    }
    
    /**
     * Update ACF fields for a lesson
     */
    private function update_acf_fields($post_id, $lesson) {
        if (!function_exists('update_field')) {
            return;
        }
        
        // Map lesson data to ACF fields
        update_field('lesson_id', $lesson->ID, $post_id);
        update_field('lesson_required_membership', $this->get_membership_level($lesson), $post_id);
        update_field('lesson_type', $this->get_lesson_type($lesson), $post_id);
        update_field('free_lesson', $this->is_free_lesson($lesson), $post_id);
        
        // Add course data if available
        if (isset($this->course_data[$lesson->course_id])) {
            $course = $this->course_data[$lesson->course_id];
            update_field('lesson_course_title', $course['title'], $post_id);
            update_field('lesson_course_description', $course['description'], $post_id);
        }
    }
    
    /**
     * Get membership level for lesson
     */
    private function get_membership_level($lesson) {
        // Default to Essential for now
        return 'essential';
    }
    
    /**
     * Get lesson type
     */
    private function get_lesson_type($lesson) {
        if ($lesson->song_lesson === 'y') {
            return 'song_lesson';
        }
        if ($lesson->success_lesson === 'y') {
            return 'success_lesson';
        }
        return 'standard';
    }
    
    /**
     * Check if lesson is free
     */
    private function is_free_lesson($lesson) {
        return ($lesson->song_lesson !== 'y' && $lesson->success_lesson !== 'y');
    }
    
    /**
     * Get migration statistics
     */
    public function get_stats() {
        global $wpdb;
        
        return array(
            'courses' => array(
                'original' => $wpdb->get_var("SELECT COUNT(*) FROM academy_courses"),
                'prepared' => isset($this->course_data) ? count($this->course_data) : 0
            ),
            'lessons' => array(
                'original' => $wpdb->get_var("SELECT COUNT(*) FROM academy_lessons"),
                'migrated' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_type = 'lesson' AND pm.meta_key = '_lesson_source' AND pm.meta_value IN ('migrated', 'legacy')")
            ),
            'chapters' => array(
                'original' => $wpdb->get_var("SELECT COUNT(*) FROM academy_chapters"),
                'migrated' => $this->count_migrated_chapters()
            ),
            'studio_events' => array(
                'original' => wp_count_posts('studio-event')->publish,
                'migrated' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_lesson_legacy_type' AND meta_value = 'studio-event'")
            )
        );
    }
    
    /**
     * Count migrated chapters by counting actual chapters in metadata
     */
    private function count_migrated_chapters() {
        global $wpdb;
        
        $total_chapters = 0;
        $lessons_with_chapters = $wpdb->get_results("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_lesson_chapters'");
        
        foreach ($lessons_with_chapters as $lesson) {
            $chapters_array = maybe_unserialize($lesson->meta_value);
            if (is_array($chapters_array)) {
                $total_chapters += count($chapters_array);
            }
        }
        
        return $total_chapters;
    }
    
    /**
     * Get migration log
     */
    public function get_log() {
        return $this->log_messages;
    }
    
    /**
     * Clear migration log
     */
    public function clear_log() {
        $this->log_messages = array();
    }
    
    /**
     * Add message to log
     */
    private function log($message) {
        $timestamp = current_time('Y-m-d H:i:s');
        $this->log_messages[] = "[{$timestamp}] {$message}";
    }
    
    /**
     * Import studio events from SQL export - Direct approach
     */
    public function import_studio_events_from_sql($sql_file_path) {
        $this->log("Starting studio events import from SQL file...");
        
        if (!file_exists($sql_file_path)) {
            $this->log("ERROR: SQL file not found at: {$sql_file_path}");
            return false;
        }
        
        global $wpdb;
        
        // First, let's directly query the existing studio-event posts
        $this->log("Checking existing studio-event posts...");
        
        $studio_events = $wpdb->get_results("
            SELECT ID, post_title, post_content, post_name, post_status, post_date, post_author
            FROM {$wpdb->posts} 
            WHERE post_type = 'studio-event'
            ORDER BY post_date DESC
        ");
        
        $imported = 0;
        $skipped = 0;
        
        $this->log("Found " . count($studio_events) . " studio-event posts to migrate");
        
        foreach ($studio_events as $event) {
            // Check if lesson already exists
            $existing = get_page_by_path($event->post_name, OBJECT, 'lesson');
            
            if ($existing) {
                update_post_meta($existing->ID, '_lesson_source', 'legacy');
                $skipped++;
                $this->log("Skipped existing: {$event->post_title}");
                continue;
            }
            
            // Create lesson post
            $post_data = array(
                'post_title' => $event->post_title,
                'post_content' => $event->post_content,
                'post_name' => $event->post_name,
                'post_type' => 'lesson',
                'post_status' => $event->post_status,
                'post_date' => $event->post_date,
                'post_author' => $event->post_author
            );
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id) {
                // Add metadata
                update_post_meta($post_id, '_lesson_source', 'migrated');
                update_post_meta($post_id, '_lesson_legacy_type', 'studio-event');
                update_post_meta($post_id, '_lesson_legacy_id', $event->ID);
                
                $imported++;
                $this->log("Imported: {$event->post_title}");
            }
        }
        
        $this->log("Import complete: {$imported} studio events imported, {$skipped} skipped");
        return true;
    }
    
    /**
     * Parse CSV line handling quoted strings
     */
    private function parse_csv_line($line) {
        $values = array();
        $current = '';
        $in_quotes = false;
        $quote_char = '';
        
        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];
            
            if (!$in_quotes && ($char === "'" || $char === '"')) {
                $in_quotes = true;
                $quote_char = $char;
                $current .= $char;
            } elseif ($in_quotes && $char === $quote_char) {
                // Check if it's escaped
                if ($i > 0 && $line[$i-1] === '\\') {
                    $current .= $char;
                } else {
                    $in_quotes = false;
                    $quote_char = '';
                    $current .= $char;
                }
            } elseif (!$in_quotes && $char === ',') {
                $values[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }
        
        // Add the last value
        if ($current !== '') {
            $values[] = $current;
        }
        
        return $values;
    }
    
    /**
     * Parse SQL VALUES string into array
     */
    private function parse_sql_values($values_string) {
        $values = array();
        $current_value = '';
        $in_quotes = false;
        $quote_char = '';
        
        for ($i = 0; $i < strlen($values_string); $i++) {
            $char = $values_string[$i];
            
            if (!$in_quotes && ($char === "'" || $char === '"')) {
                $in_quotes = true;
                $quote_char = $char;
                $current_value .= $char;
            } elseif ($in_quotes && $char === $quote_char) {
                // Check if it's escaped
                if ($i > 0 && $values_string[$i-1] === '\\') {
                    $current_value .= $char;
                } else {
                    $in_quotes = false;
                    $quote_char = '';
                    $current_value .= $char;
                }
            } elseif (!$in_quotes && $char === ',') {
                $values[] = $current_value;
                $current_value = '';
            } else {
                $current_value .= $char;
            }
        }
        
        // Add the last value
        if ($current_value !== '') {
            $values[] = $current_value;
        }
        
        return $values;
    }
    
    /**
     * Clean up old lessons (delete legacy lessons after testing)
     */
    public function cleanup_legacy_lessons() {
        $legacy_lessons = get_posts(array(
            'post_type' => 'lesson',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_lesson_source',
                    'value' => 'legacy',
                    'compare' => '='
                )
            ),
            'fields' => 'ids'
        ));
        
        $deleted = 0;
        foreach ($legacy_lessons as $lesson_id) {
            if (wp_delete_post($lesson_id, true)) {
                $deleted++;
            }
        }
        
        $this->log("Deleted {$deleted} legacy lessons");
        return $deleted;
    }
}
