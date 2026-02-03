<?php
/**
 * Zoom Webhook Handler
 * 
 * Handles incoming Zoom recording webhooks from Zapier
 * Automates event migration by matching recordings to events and updating Vimeo IDs
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Zoom_Webhook {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Table names
     */
    private $lessons_table;
    private $chapters_table;
    private $collections_table;
    
    /**
     * Debug log option name
     */
    private $debug_log_option = 'alm_zoom_webhook_debug_log';
    
    /**
     * Maximum number of debug entries to keep
     */
    private $max_debug_entries = 25;
    
    /**
     * Last SQL query executed (for debugging)
     */
    private $last_sql_query = null;
    
    /**
     * Last checked events (for debugging)
     */
    private $last_checked_events = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();
        $this->lessons_table = $this->database->get_table_name('lessons');
        $this->chapters_table = $this->database->get_table_name('chapters');
        $this->collections_table = $this->database->get_table_name('collections');
    }
    
    /**
     * Process incoming webhook payload
     * 
     * @param array $payload Webhook payload from Zapier
     * @param bool $dry_run If true, don't make actual changes
     * @return array Result with success status and debug info
     */
    public function process_webhook($payload, $dry_run = false) {
        $debug = array(
            'timestamp' => current_time('mysql'),
            'payload' => $payload,
            'dry_run' => $dry_run,
            'steps' => array()
        );
        
        // Step 1: Validate shared secret
        $secret = get_option('alm_zoom_webhook_secret', '');
        $provided_secret = isset($payload['code']) ? sanitize_text_field($payload['code']) : '';
        
        if (empty($secret) || $provided_secret !== $secret) {
            $error = 'Invalid or missing shared secret';
            $debug['error'] = $error;
            $debug['steps'][] = array('step' => 'secret_validation', 'status' => 'failed', 'message' => $error);
            $this->add_debug_log($debug);
            return array('success' => false, 'error' => $error, 'debug' => $debug);
        }
        
        $debug['steps'][] = array('step' => 'secret_validation', 'status' => 'passed');
        
        // Step 2: Parse payload fields
        $title = isset($payload['title']) ? sanitize_text_field($payload['title']) : '';
        $date_str = isset($payload['date']) ? sanitize_text_field($payload['date']) : '';
        $vimeo_id_raw = isset($payload['vimeo_id']) ? sanitize_text_field($payload['vimeo_id']) : '';
        $vtt_content = isset($payload['vtt']) ? $payload['vtt'] : ''; // VTT content (can be multiline, don't sanitize)
        
        if (empty($title) || empty($date_str) || empty($vimeo_id_raw)) {
            $error = 'Missing required fields: title, date, or vimeo_id';
            $debug['error'] = $error;
            $debug['steps'][] = array('step' => 'payload_validation', 'status' => 'failed', 'message' => $error);
            $this->add_debug_log($debug);
            return array('success' => false, 'error' => $error, 'debug' => $debug);
        }
        
        // Extract Vimeo ID (handle formats like "3. ID: 1011320817" or just "1011320817")
        $vimeo_id = $this->extract_vimeo_id($vimeo_id_raw);
        if (empty($vimeo_id)) {
            $error = 'Could not extract valid Vimeo ID from: ' . $vimeo_id_raw;
            $debug['error'] = $error;
            $debug['steps'][] = array('step' => 'vimeo_extraction', 'status' => 'failed', 'message' => $error);
            $this->add_debug_log($debug);
            return array('success' => false, 'error' => $error, 'debug' => $debug);
        }
        
        // Parse date (handle formats like "1. Start Time: 2025-11-27T12:50:26Z" or ISO 8601)
        $recording_date = $this->parse_date($date_str);
        if (!$recording_date) {
            $error = 'Could not parse date from: ' . $date_str;
            $debug['error'] = $error;
            $debug['steps'][] = array('step' => 'date_parsing', 'status' => 'failed', 'message' => $error);
            $this->add_debug_log($debug);
            return array('success' => false, 'error' => $error, 'debug' => $debug);
        }
        
        // Extract collection ID and zoom identifier from title (format: {id123|willie-coaching} or {id123|willie-coaching|teacher})
        $collection_id = $this->extract_collection_id($title);
        $zoom_identifier = $this->extract_zoom_identifier($title);
        
        $debug['parsed'] = array(
            'title' => $title,
            'collection_id' => $collection_id,
            'zoom_identifier' => $zoom_identifier,
            'vimeo_id' => $vimeo_id,
            'recording_date' => $recording_date->format('Y-m-d H:i:s')
        );
        
        $debug['steps'][] = array('step' => 'payload_parsing', 'status' => 'passed', 'data' => $debug['parsed']);
        
        if (empty($collection_id)) {
            $error = 'Could not extract collection ID from title. Expected format: {id123|willie-coaching} or {id123|willie-coaching|teacher}. Title: ' . $title;
            $debug['error'] = $error;
            $debug['steps'][] = array('step' => 'collection_extraction', 'status' => 'failed', 'message' => $error);
            $this->add_debug_log($debug);
            return array('success' => false, 'error' => $error, 'debug' => $debug);
        }
        
        if (empty($zoom_identifier)) {
            $error = 'Could not extract zoom identifier from title. Expected format: {id123|willie-coaching} or {id123|willie-coaching|teacher}. Title: ' . $title;
            $debug['error'] = $error;
            $debug['steps'][] = array('step' => 'zoom_identifier_extraction', 'status' => 'failed', 'message' => $error);
            $this->add_debug_log($debug);
            return array('success' => false, 'error' => $error, 'debug' => $debug);
        }
        
        // Step 3: Find matching event
        $event = $this->find_matching_event($zoom_identifier, $recording_date);
        
        // Add SQL query and checked events to debug
        $debug['sql_query'] = $this->last_sql_query;
        $debug['checked_events'] = $this->last_checked_events;
        
        if (!$event) {
            $error = 'No matching event found for zoom_identifier: ' . $zoom_identifier . ' within ±2 hours of ' . $recording_date->format('Y-m-d H:i:s');
            $debug['error'] = $error;
            $debug['steps'][] = array('step' => 'event_matching', 'status' => 'failed', 'message' => $error);
            $this->add_debug_log($debug);
            return array('success' => false, 'error' => $error, 'debug' => $debug);
        }
        
        $debug['matched_event'] = array(
            'event_id' => $event->ID,
            'event_title' => $event->post_title,
            'event_date' => get_field('je_event_start', $event->ID)
        );
        $debug['steps'][] = array('step' => 'event_matching', 'status' => 'passed', 'event_id' => $event->ID);
        
        // Step 4: Update Vimeo ID on event
        if (!$dry_run) {
            update_post_meta($event->ID, 'je_event_replay_vimeo_id', $vimeo_id);
            $debug['steps'][] = array('step' => 'update_vimeo_id', 'status' => 'completed', 'vimeo_id' => $vimeo_id);
            
            // Save VTT to event meta for later use (if migration happens later)
            if (!empty($vtt_content)) {
                update_post_meta($event->ID, 'je_event_replay_vtt', $vtt_content);
                $debug['steps'][] = array('step' => 'save_vtt_to_event', 'status' => 'completed');
            }
        } else {
            $debug['steps'][] = array('step' => 'update_vimeo_id', 'status' => 'skipped', 'reason' => 'dry_run');
        }
        
        // Step 5: Check if event was already converted - if so, ensure chapter exists/updated
        $existing_lesson_id = get_post_meta($event->ID, '_converted_to_alm_lesson_id', true);
        
        if (!empty($existing_lesson_id) && !$dry_run) {
            // Event already converted - ensure chapter exists and is updated with Vimeo ID
            $chapter_result = $this->ensure_chapter_for_lesson($existing_lesson_id, $vimeo_id, $vtt_content, $event->ID);
            if ($chapter_result['success']) {
                $debug['steps'][] = array('step' => 'update_existing_chapter', 'status' => 'completed', 'chapter_id' => $chapter_result['chapter_id']);
            } else {
                $debug['steps'][] = array('step' => 'update_existing_chapter', 'status' => 'failed', 'error' => $chapter_result['error']);
            }
        }
        
        // Step 6: Check if auto-migrate is enabled
        $auto_migrate = get_option('alm_zoom_webhook_auto_migrate', false);
        
        if ($auto_migrate && !$dry_run) {
            if (empty($existing_lesson_id)) {
                // Trigger migration (pass VTT content)
                $migration_result = $this->migrate_event_to_collection($event->ID, $collection_id, $vimeo_id, $vtt_content);
                
                if ($migration_result['success']) {
                    $debug['migration'] = array(
                        'lesson_id' => $migration_result['lesson_id'],
                        'collection_id' => $collection_id
                    );
                    $debug['steps'][] = array('step' => 'migration', 'status' => 'completed', 'lesson_id' => $migration_result['lesson_id']);
                } else {
                    $debug['migration'] = array('error' => $migration_result['error']);
                    $debug['steps'][] = array('step' => 'migration', 'status' => 'failed', 'error' => $migration_result['error']);
                }
            } else {
                // Event already converted - chapter was updated in Step 5
                $debug['migration'] = array('skipped' => 'already_converted', 'existing_lesson_id' => $existing_lesson_id);
                $debug['steps'][] = array('step' => 'migration', 'status' => 'skipped', 'reason' => 'already_converted');
            }
        } else {
            $debug['migration'] = array('skipped' => $dry_run ? 'dry_run' : 'auto_migrate_disabled');
            $debug['steps'][] = array('step' => 'migration', 'status' => 'skipped', 'reason' => $dry_run ? 'dry_run' : 'auto_migrate_disabled');
        }
        
        $debug['success'] = true;
        $this->add_debug_log($debug);
        
        return array(
            'success' => true,
            'event_id' => $event->ID,
            'vimeo_id' => $vimeo_id,
            'collection_id' => $collection_id,
            'migration' => isset($debug['migration']) ? $debug['migration'] : null,
            'debug' => $debug
        );
    }
    
    /**
     * Extract collection ID from title (format: {id123|willie-coaching} or {id123|willie-coaching|teacher})
     * 
     * @param string $title Title containing collection ID and zoom identifier
     * @return int|null Collection ID or null if not found
     */
    private function extract_collection_id($title) {
        // Look for pattern {id123|willie-coaching} or {id123|willie-coaching|teacher} where left side of first pipe is collection ID
        if (preg_match('/\{id\s*(\d+)\s*\|\s*[^}]+\}/i', $title, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }
    
    /**
     * Extract zoom identifier from title (format: {id123|willie-coaching} or {id123|willie-coaching|teacher})
     * 
     * @param string $title Title containing collection ID and zoom identifier
     * @return string|null Zoom identifier or null if not found
     */
    private function extract_zoom_identifier($title) {
        // Valid identifiers: willie-coaching, willie-special, willie-community, paul-class
        $valid_identifiers = array('willie-coaching', 'willie-special', 'willie-community', 'paul-class');
        
        // Look for pattern {id123|willie-coaching} or {id123|willie-coaching|teacher}
        // Capture only the second part (zoom identifier), ignoring optional third part (teacher)
        if (preg_match('/\{id\s*\d+\s*\|\s*([^|}]+)(?:\s*\|\s*[^}]+)?\}/i', $title, $matches)) {
            $identifier = strtolower(trim($matches[1]));
            
            // Check if it's a valid zoom identifier
            if (in_array($identifier, $valid_identifiers)) {
                return $identifier;
            }
        }
        return null;
    }
    
    /**
     * Extract Vimeo ID from various formats
     * 
     * @param string $vimeo_id_raw Raw Vimeo ID string
     * @return int|null Vimeo ID or null if not found
     */
    private function extract_vimeo_id($vimeo_id_raw) {
        // Handle formats like "3. ID: 1011320817" or just "1011320817"
        if (preg_match('/(\d+)/', $vimeo_id_raw, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }
    
    /**
     * Parse date from various formats
     * 
     * @param string $date_str Date string (from Zapier, typically UTC)
     * @return DateTime|null Parsed date in UTC timezone or null if invalid
     */
    private function parse_date($date_str) {
        // Handle formats like "1. Start Time: 2025-11-27T12:50:26Z"
        if (preg_match('/(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2})/', $date_str, $matches)) {
            $date_str = $matches[1];
        }
        
        try {
            // Create DateTime in UTC timezone (Zapier sends UTC)
            // If the string ends with Z, it's already UTC
            if (substr($date_str, -1) === 'Z' || strpos($date_str, 'Z') !== false) {
                $date = new DateTime($date_str, new DateTimeZone('UTC'));
            } else {
                // Assume UTC if no timezone specified
                $date = new DateTime($date_str, new DateTimeZone('UTC'));
            }
            return $date;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Find matching event by zoom identifier and date
     * 
     * @param string $zoom_identifier Zoom identifier (e.g., 'willie-coaching')
     * @param DateTime $recording_date Recording date/time
     * @return WP_Post|null Matching event or null
     */
    private function find_matching_event($zoom_identifier, $recording_date) {
        global $wpdb;
        
        // Build SQL query manually to log it
        // WordPress get_posts() uses meta_query which is complex, so we'll query directly
        $meta_key = 'zoom_identifier';
        $meta_value = $zoom_identifier;
        
        // SQL query to find posts with matching zoom_identifier
        $sql = $wpdb->prepare(
            "SELECT p.ID, p.post_title, p.post_status
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'je_event'
             AND pm.meta_key = %s
             AND pm.meta_value = %s
             ORDER BY p.post_date DESC",
            $meta_key,
            $meta_value
        );
        
        // Store SQL for debug logging
        $this->last_sql_query = $sql;
        
        $event_posts = $wpdb->get_results($sql);
        
        if (empty($event_posts)) {
            return null;
        }
        
        // Convert to WP_Post objects for compatibility
        $events = array();
        foreach ($event_posts as $post_data) {
            $events[] = get_post($post_data->ID);
        }
        
        // Find the event with start time closest to recording date (within ±2 hours)
        $best_match = null;
        $smallest_diff = null;
        $two_hours = 2 * 60 * 60; // 2 hours in seconds
        $checked_events = array();
        
        foreach ($events as $event) {
            $event_start = get_field('je_event_start', $event->ID);
            
            if (empty($event_start)) {
                $checked_events[] = array(
                    'event_id' => $event->ID,
                    'event_title' => $event->post_title,
                    'event_start' => null,
                    'reason' => 'No je_event_start field found'
                );
                continue;
            }
            
            // Convert event start to DateTime in UTC for comparison
            // ACF date fields are typically stored as timestamps or date strings
            // Important: ACF stores dates in WordPress's local timezone, not UTC
            try {
                // Get WordPress timezone
                $wp_timezone = wp_timezone();
                
                if (is_numeric($event_start)) {
                    // It's a timestamp - ACF timestamps are in WordPress timezone
                    // Create DateTime in WordPress timezone first, then convert to UTC
                    $event_date = new DateTime('@' . (int)$event_start);
                    $event_date->setTimezone($wp_timezone);
                    // Now convert to UTC for comparison
                    $event_date->setTimezone(new DateTimeZone('UTC'));
                } else {
                    // It's a date string - ACF stores in WordPress timezone
                    // Parse it assuming WordPress timezone, then convert to UTC
                    $event_date = new DateTime((string)$event_start, $wp_timezone);
                    // Convert to UTC for comparison
                    $event_date->setTimezone(new DateTimeZone('UTC'));
                }
            } catch (Exception $e) {
                $checked_events[] = array(
                    'event_id' => $event->ID,
                    'event_title' => $event->post_title,
                    'event_start' => $event_start,
                    'reason' => 'Could not parse event_start: ' . $e->getMessage()
                );
                continue;
            }
            
            // Both dates are now in UTC, compare timestamps
            $event_timestamp = $event_date->getTimestamp();
            $recording_timestamp = $recording_date->getTimestamp();
            $diff = abs($recording_timestamp - $event_timestamp);
            $diff_hours = round($diff / 3600, 2);
            
            // Get WordPress timezone for display
            $wp_timezone = wp_timezone();
            $event_date_local = clone $event_date;
            $event_date_local->setTimezone($wp_timezone);
            $recording_date_local = clone $recording_date;
            $recording_date_local->setTimezone($wp_timezone);
            
            $checked_events[] = array(
                'event_id' => $event->ID,
                'event_title' => $event->post_title,
                'event_start_utc' => $event_date->format('Y-m-d H:i:s') . ' UTC',
                'event_start_local' => $event_date_local->format('Y-m-d H:i:s T'),
                'recording_date_utc' => $recording_date->format('Y-m-d H:i:s') . ' UTC',
                'recording_date_local' => $recording_date_local->format('Y-m-d H:i:s T'),
                'event_timestamp' => $event_timestamp,
                'recording_timestamp' => $recording_timestamp,
                'diff_seconds' => $diff,
                'diff_hours' => $diff_hours,
                'within_window' => $diff <= $two_hours
            );
            
            // Must be within ±2 hours
            if ($diff <= $two_hours) {
                if ($smallest_diff === null || $diff < $smallest_diff) {
                    $smallest_diff = $diff;
                    $best_match = $event;
                }
            }
        }
        
        // Store checked events for debug
        $this->last_checked_events = $checked_events;
        
        return $best_match;
    }
    
    /**
     * Migrate event to collection (reuses logic from ALM_Admin_Event_Migration)
     * 
     * @param int $event_id Event post ID
     * @param int $collection_id Collection ID
     * @param int $vimeo_id Vimeo ID to get duration from
     * @param string $vtt_content Optional VTT transcript content
     * @return array Result with success status and lesson_id
     */
    private function migrate_event_to_collection($event_id, $collection_id, $vimeo_id = 0, $vtt_content = '') {
        $event = get_post($event_id);
        
        if (!$event || $event->post_type !== 'je_event') {
            return array('success' => false, 'error' => 'Invalid event');
        }
        
        // Check if collection exists
        $collection = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID FROM {$this->collections_table} WHERE ID = %d",
            $collection_id
        ));
        
        if (!$collection) {
            return array('success' => false, 'error' => 'Collection not found');
        }
        
        // Check if already converted
        $existing_lesson_id = get_post_meta($event_id, '_converted_to_alm_lesson_id', true);
        if (!empty($existing_lesson_id)) {
            return array('success' => true, 'lesson_id' => $existing_lesson_id, 'already_converted' => true);
        }
        
        // Get formatted lesson title with date
        $lesson_title = $this->get_formatted_lesson_title($event_id, $event->post_title);
        
        // Get event date (from ACF field, not post_date)
        $event_date = $this->get_event_date($event_id);
        
        // Default membership level (Essentials = 2)
        $membership_level = 2;
        
        // Create lesson
        $lesson_data = array(
            'collection_id' => $collection_id,
            'lesson_title' => $lesson_title,
            'lesson_description' => ALM_Helpers::clean_html_content($event->post_content),
            'post_date' => $event_date,
            'duration' => 0, // Will be calculated from chapter
            'song_lesson' => 'n',
            'slug' => sanitize_title($lesson_title),
            'membership_level' => $membership_level,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $result = $this->wpdb->insert($this->lessons_table, $lesson_data);
        
        if ($result === false) {
            return array('success' => false, 'error' => 'Failed to create lesson');
        }
        
        $lesson_id = $this->wpdb->insert_id;
        
        // Convert resources
        $resources = $this->convert_event_resources($event_id);
        if (!empty($resources)) {
            $this->wpdb->update(
                $this->lessons_table,
                array('resources' => serialize($resources)),
                array('ID' => $lesson_id),
                array('%s'),
                array('%d')
            );
        }
        
        // Get video URLs from event
        $replay_vimeo_id = get_post_meta($event_id, 'je_event_replay_vimeo_id', true);
        $bunny_url = get_post_meta($event_id, 'je_event_bunny_url', true);
        
        // Use provided vimeo_id if available, otherwise fall back to event meta
        $chapter_vimeo_id = !empty($vimeo_id) ? intval($vimeo_id) : (!empty($replay_vimeo_id) ? intval($replay_vimeo_id) : 0);
        
        // Get VTT from event meta if not passed directly (for backward compatibility)
        if (empty($vtt_content)) {
            $vtt_content = get_post_meta($event_id, 'je_event_replay_vtt', true);
        }
        
        // Get Vimeo duration if we have a Vimeo ID
        $chapter_duration = 0;
        if (!empty($chapter_vimeo_id)) {
            require_once ALM_PLUGIN_DIR . 'includes/class-vimeo-api.php';
            $vimeo_api = new ALM_Vimeo_API();
            $vimeo_duration = $vimeo_api->get_video_duration($chapter_vimeo_id);
            if ($vimeo_duration !== false && $vimeo_duration > 0) {
                $chapter_duration = intval($vimeo_duration);
            }
        }
        
        // Create chapter if either Vimeo ID or Bunny URL exists
        if (!empty($chapter_vimeo_id) || !empty($bunny_url)) {
            $chapter_data = array(
                'lesson_id' => $lesson_id,
                'chapter_title' => $lesson_title,
                'menu_order' => 1,
                'vimeo_id' => $chapter_vimeo_id,
                'youtube_id' => '',
                'bunny_url' => !empty($bunny_url) ? sanitize_text_field($bunny_url) : '',
                'duration' => $chapter_duration,
                'free' => 'n',
                'slug' => sanitize_title($lesson_title),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );
            
            $this->wpdb->insert($this->chapters_table, $chapter_data);
            $chapter_id = $this->wpdb->insert_id;
            
            // Save VTT file and create transcript record if VTT content exists
            if (!empty($vtt_content) && $chapter_id) {
                $this->save_vtt_transcript($chapter_id, $lesson_id, $vtt_content);
            }
            
            // Update lesson duration from chapters
            if ($chapter_duration > 0) {
                $this->wpdb->update(
                    $this->lessons_table,
                    array('duration' => $chapter_duration),
                    array('ID' => $lesson_id),
                    array('%d'),
                    array('%d')
                );
            }
        }
        
        // Sync lesson to WordPress post
        $sync = new ALM_Post_Sync();
        $post_id = $sync->sync_lesson_to_post($lesson_id);
        
        // Copy featured image if exists
        $thumbnail_id = get_post_thumbnail_id($event_id);
        if ($thumbnail_id && $post_id) {
            set_post_thumbnail($post_id, $thumbnail_id);
        }
        
        // Mark event as converted
        update_post_meta($event_id, '_converted_to_alm_lesson_id', $lesson_id);
        
        // Insert new lesson in correct position based on date
        $this->insert_lesson_in_order($collection_id, $lesson_id);
        
        return array('success' => true, 'lesson_id' => $lesson_id);
    }
    
    /**
     * Get formatted lesson title with event date (reused from migration class)
     */
    private function get_formatted_lesson_title($event_id, $base_title) {
        $event_start = get_field('je_event_start', $event_id);
        
        if (!empty($event_start)) {
            if (is_numeric($event_start)) {
                $date_ts = (int)$event_start;
            } else {
                $date_ts = strtotime((string)$event_start);
            }
            
            if ($date_ts) {
                $formatted_date = date_i18n('F j, Y', $date_ts);
                return $base_title . ' - ' . $formatted_date;
            }
        }
        
        return $base_title;
    }
    
    /**
     * Get event date from ACF field (reused from migration class)
     */
    private function get_event_date($event_id) {
        $event_start = get_field('je_event_start', $event_id);
        
        if (!empty($event_start)) {
            if (is_numeric($event_start)) {
                $date_ts = (int)$event_start;
            } else {
                $date_ts = strtotime((string)$event_start);
            }
            
            if ($date_ts) {
                return date('Y-m-d', $date_ts);
            }
        }
        
        $event = get_post($event_id);
        if ($event && $event->post_date) {
            return date('Y-m-d', strtotime($event->post_date));
        }
        
        return date('Y-m-d');
    }
    
    /**
     * Convert event resources to ALM format (reused from migration class)
     */
    private function convert_event_resources($event_id) {
        $resources = array();
        
        if (function_exists('get_field')) {
            $acf_resources = get_field('je_event_resource_repeater', $event_id);
            if (!empty($acf_resources) && is_array($acf_resources)) {
                foreach ($acf_resources as $index => $resource) {
                    $resource_type = isset($resource['je_event_resource_type']) ? $resource['je_event_resource_type'] : '';
                    $resource_file = isset($resource['je_event_resource_file']) ? $resource['je_event_resource_file'] : '';
                    $resource_youtube = isset($resource['je_event_resource_youtube']) ? $resource['je_event_resource_youtube'] : '';
                    
                    $alm_type = $this->map_resource_type($resource_type);
                    
                    $file_url = '';
                    $attachment_id = 0;
                    if (!empty($resource_file)) {
                        if (is_array($resource_file)) {
                            $file_url = isset($resource_file['url']) ? $resource_file['url'] : '';
                            $attachment_id = isset($resource_file['id']) ? intval($resource_file['id']) : (isset($resource_file['ID']) ? intval($resource_file['ID']) : 0);
                        } elseif (is_numeric($resource_file)) {
                            $attachment_id = intval($resource_file);
                            $file_url = wp_get_attachment_url($attachment_id);
                        } elseif (is_string($resource_file)) {
                            $file_url = $resource_file;
                        }
                    }
                    
                    $final_url = '';
                    $final_attachment_id = 0;
                    
                    if (!empty($file_url) && is_string($file_url)) {
                        $final_url = $file_url;
                        $final_attachment_id = $attachment_id;
                    } elseif (!empty($resource_youtube) && is_string($resource_youtube)) {
                        $final_url = esc_url_raw($resource_youtube);
                        $final_attachment_id = 0;
                    }
                    
                    if (!empty($final_url)) {
                        $resource_key = $this->get_resource_key($resources, $alm_type);
                        
                        $resources[$resource_key] = array(
                            'url' => $final_url,
                            'attachment_id' => $final_attachment_id,
                            'label' => ''
                        );
                    }
                }
            }
        }
        
        return $resources;
    }
    
    /**
     * Map resource type (reused from migration class)
     */
    private function map_resource_type($event_type) {
        $type_map = array(
            'coaching-sheet' => 'sheet_music',
            'sheet-music' => 'sheet_music',
            'sheet' => 'sheet_music',
            'ireal' => 'ireal',
            'irealpro' => 'ireal',
            'jam' => 'jam',
            'backing-track' => 'jam',
            'mp3' => 'jam',
            'zip' => 'zip'
        );
        
        $event_type = strtolower(trim($event_type));
        return isset($type_map[$event_type]) ? $type_map[$event_type] : 'sheet_music';
    }
    
    /**
     * Get resource key (reused from migration class)
     */
    private function get_resource_key($existing_resources, $type) {
        $count = 1;
        foreach ($existing_resources as $key => $value) {
            if (strpos($key, $type) === 0) {
                $count++;
            }
        }
        
        return ($count > 1) ? $type . $count : $type;
    }
    
    /**
     * Insert lesson in order (reused from migration class)
     */
    private function insert_lesson_in_order($collection_id, $new_lesson_id) {
        $database = new ALM_Database();
        $database->check_and_add_menu_order_column();
        
        $new_lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID, post_date, menu_order FROM {$this->lessons_table} WHERE ID = %d",
            $new_lesson_id
        ));
        
        if (!$new_lesson) {
            return;
        }
        
        $existing_lessons = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT ID, post_date, menu_order FROM {$this->lessons_table} WHERE collection_id = %d AND ID != %d ORDER BY menu_order ASC",
            $collection_id,
            $new_lesson_id
        ));
        
        $insert_position = count($existing_lessons);
        foreach ($existing_lessons as $index => $lesson) {
            if ($lesson->post_date >= $new_lesson->post_date) {
                $insert_position = $index;
                break;
            }
        }
        
        foreach ($existing_lessons as $index => $lesson) {
            if ($index >= $insert_position) {
                $this->wpdb->update(
                    $this->lessons_table,
                    array('menu_order' => $index + 1),
                    array('ID' => $lesson->ID),
                    array('%d'),
                    array('%d')
                );
            }
        }
        
        $this->wpdb->update(
            $this->lessons_table,
            array('menu_order' => $insert_position),
            array('ID' => $new_lesson_id),
            array('%d'),
            array('%d')
        );
    }
    
    /**
     * Add debug log entry
     * 
     * @param array $debug_entry Debug entry data
     */
    public function add_debug_log($debug_entry) {
        $logs = get_option($this->debug_log_option, array());
        
        if (!is_array($logs)) {
            $logs = array();
        }
        
        // Add new entry at the beginning
        array_unshift($logs, $debug_entry);
        
        // Keep only the most recent entries
        $logs = array_slice($logs, 0, $this->max_debug_entries);
        
        update_option($this->debug_log_option, $logs);
    }
    
    /**
     * Get debug logs
     * 
     * @return array Debug logs
     */
    public function get_debug_logs() {
        $logs = get_option($this->debug_log_option, array());
        return is_array($logs) ? $logs : array();
    }
    
    /**
     * Clear debug logs
     */
    public function clear_debug_logs() {
        delete_option($this->debug_log_option);
    }
    
    /**
     * Save VTT transcript to file and database
     * 
     * @param int $chapter_id Chapter ID
     * @param int $lesson_id Lesson ID
     * @param string $vtt_content VTT file content
     * @return array Result with success status
     */
    private function save_vtt_transcript($chapter_id, $lesson_id, $vtt_content) {
        $transcripts_table = $this->database->get_table_name('transcripts');
        
        // Ensure VTT directory exists
        $upload_dir = wp_upload_dir();
        $vtt_dir = $upload_dir['basedir'] . '/alm_transcriptions';
        if (!file_exists($vtt_dir)) {
            wp_mkdir_p($vtt_dir);
        }
        
        // Generate VTT filename
        $vtt_filename = 'chapter-' . $chapter_id . '.vtt';
        $vtt_path = $vtt_dir . '/' . $vtt_filename;
        
        // Save VTT file
        $file_saved = file_put_contents($vtt_path, $vtt_content);
        
        if ($file_saved === false) {
            return array('success' => false, 'error' => 'Failed to save VTT file');
        }
        
        // Extract plain text from VTT for content field (remove timestamps and WEBVTT header)
        $transcript_text = $this->extract_text_from_vtt($vtt_content);
        
        // Check if transcript already exists
        $existing = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID FROM {$transcripts_table} WHERE chapter_id = %d AND source = 'zoom' LIMIT 1",
            $chapter_id
        ));
        
        if ($existing) {
            // Update existing transcript
            $this->wpdb->update(
                $transcripts_table,
                array(
                    'content' => $transcript_text,
                    'vtt_file' => $vtt_filename,
                    'updated_at' => current_time('mysql')
                ),
                array('ID' => $existing->ID),
                array('%s', '%s', '%s'),
                array('%d')
            );
        } else {
            // Create new transcript
            $this->wpdb->insert(
                $transcripts_table,
                array(
                    'lesson_id' => $lesson_id,
                    'chapter_id' => $chapter_id,
                    'source' => 'zoom',
                    'content' => $transcript_text,
                    'vtt_file' => $vtt_filename,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s', '%s')
            );
        }
        
        return array('success' => true, 'vtt_file' => $vtt_filename);
    }
    
    /**
     * Save VTT to existing chapter (when event was already converted)
     * 
     * @param int $lesson_id Lesson ID
     * @param string $vtt_content VTT file content
     * @return array Result with success status and chapter_id
     */
    private function save_vtt_to_existing_chapter($lesson_id, $vtt_content) {
        // Find the first chapter for this lesson
        $chapter = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID FROM {$this->chapters_table} WHERE lesson_id = %d ORDER BY menu_order ASC LIMIT 1",
            $lesson_id
        ));
        
        if (!$chapter) {
            return array('success' => false, 'error' => 'No chapter found for lesson');
        }
        
        $result = $this->save_vtt_transcript($chapter->ID, $lesson_id, $vtt_content);
        
        if ($result['success']) {
            return array('success' => true, 'chapter_id' => $chapter->ID);
        }
        
        return $result;
    }
    
    /**
     * Ensure chapter exists for lesson and update/create it with Vimeo ID
     * 
     * @param int $lesson_id Lesson ID
     * @param int $vimeo_id Vimeo ID
     * @param string $vtt_content Optional VTT content
     * @param int $event_id Event ID (for getting lesson title and Bunny URL)
     * @return array Result with success status and chapter_id
     */
    private function ensure_chapter_for_lesson($lesson_id, $vimeo_id, $vtt_content = '', $event_id = 0) {
        // Get lesson info
        $lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID, lesson_title FROM {$this->lessons_table} WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            return array('success' => false, 'error' => 'Lesson not found');
        }
        
        // Get lesson title - use event title if available, otherwise use lesson title
        $chapter_title = $lesson->lesson_title;
        if ($event_id) {
            $event = get_post($event_id);
            if ($event) {
                $chapter_title = $this->get_formatted_lesson_title($event_id, $event->post_title);
            }
        }
        
        // Check if chapter already exists
        $existing_chapter = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID, vimeo_id FROM {$this->chapters_table} WHERE lesson_id = %d ORDER BY menu_order ASC LIMIT 1",
            $lesson_id
        ));
        
        // Get Bunny URL from event if available
        $bunny_url = '';
        if ($event_id) {
            $bunny_url = get_post_meta($event_id, 'je_event_bunny_url', true);
        }
        
        // Get Vimeo duration if we have a Vimeo ID
        $chapter_duration = 0;
        if (!empty($vimeo_id)) {
            require_once ALM_PLUGIN_DIR . 'includes/class-vimeo-api.php';
            $vimeo_api = new ALM_Vimeo_API();
            $vimeo_duration = $vimeo_api->get_video_duration($vimeo_id);
            if ($vimeo_duration !== false && $vimeo_duration > 0) {
                $chapter_duration = intval($vimeo_duration);
            }
        }
        
        if ($existing_chapter) {
            // Update existing chapter with Vimeo ID and Bunny URL
            $update_data = array(
                'vimeo_id' => intval($vimeo_id),
                'duration' => $chapter_duration,
                'updated_at' => current_time('mysql')
            );
            
            // Update Bunny URL if provided
            if (!empty($bunny_url)) {
                $update_data['bunny_url'] = sanitize_text_field($bunny_url);
            }
            
            // Only update title if it's different (to preserve custom titles)
            if ($chapter_title !== $lesson->lesson_title) {
                $update_data['chapter_title'] = $chapter_title;
            }
            
            // Build format array based on what we're updating
            $format = array('%d', '%d', '%s'); // vimeo_id, duration, updated_at
            if (isset($update_data['bunny_url'])) {
                $format[] = '%s'; // bunny_url
            }
            if (isset($update_data['chapter_title'])) {
                $format[] = '%s'; // chapter_title
            }
            
            $this->wpdb->update(
                $this->chapters_table,
                $update_data,
                array('ID' => $existing_chapter->ID),
                $format,
                array('%d')
            );
            
            $chapter_id = $existing_chapter->ID;
            
            // Save VTT if provided
            if (!empty($vtt_content)) {
                $this->save_vtt_transcript($chapter_id, $lesson_id, $vtt_content);
            }
            
            // Update lesson duration from chapter
            if ($chapter_duration > 0) {
                $this->wpdb->update(
                    $this->lessons_table,
                    array('duration' => $chapter_duration),
                    array('ID' => $lesson_id),
                    array('%d'),
                    array('%d')
                );
            }
            
            return array('success' => true, 'chapter_id' => $chapter_id, 'action' => 'updated');
        } else {
            // Create new chapter
            $chapter_data = array(
                'lesson_id' => $lesson_id,
                'chapter_title' => $chapter_title,
                'menu_order' => 1,
                'vimeo_id' => intval($vimeo_id),
                'youtube_id' => '',
                'bunny_url' => !empty($bunny_url) ? sanitize_text_field($bunny_url) : '',
                'duration' => $chapter_duration,
                'free' => 'n',
                'slug' => sanitize_title($chapter_title),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );
            
            $result = $this->wpdb->insert($this->chapters_table, $chapter_data);
            
            if ($result === false) {
                return array('success' => false, 'error' => 'Failed to create chapter');
            }
            
            $chapter_id = $this->wpdb->insert_id;
            
            // Save VTT if provided
            if (!empty($vtt_content)) {
                $this->save_vtt_transcript($chapter_id, $lesson_id, $vtt_content);
            }
            
            // Update lesson duration from chapter
            if ($chapter_duration > 0) {
                $this->wpdb->update(
                    $this->lessons_table,
                    array('duration' => $chapter_duration),
                    array('ID' => $lesson_id),
                    array('%d'),
                    array('%d')
                );
            }
            
            return array('success' => true, 'chapter_id' => $chapter_id, 'action' => 'created');
        }
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
}

