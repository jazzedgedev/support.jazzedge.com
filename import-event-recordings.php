<?php
/**
 * Import Event Recordings CSV to ALM Lessons and Chapters
 * 
 * This script imports academy event recordings from CSV into:
 * - wp_alm_lessons (creating a lesson for each recording)
 * - wp_alm_chapters (creating a single chapter for each recording with video URL)
 * 
 * Usage:
 * - Direct: php import-event-recordings.php [--dry-run]
 * - Web: Navigate to /wp-content/themes/generatepress_child/import-event-recordings.php
 * 
 * CSV Columns:
 * - ID_rec: Record ID
 * - event_id: Original event ID
 * - class_type: Type of class
 * - date: Event date
 * - vimeo_id: Vimeo video ID
 * - assignment: (empty in most cases)
 * - duration: Duration in seconds
 * - Content: HTML content/description
 * - Class Event Bunny URL: Bunny stream URL
 * - Class Replay Vimeo ID: Vimeo ID (alternative column)
 * - title: Title of the lesson
 */

// Check if running from command line or web
$is_cli = (php_sapi_name() === 'cli');

// Load WordPress
define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');

global $wpdb;

// Configuration
$csv_file = '/Users/williemyette/Downloads/academy_event_recordings_joined_unique_titles.csv';
$collection_id = 0; // Set to 0 to create lessons without collection
$membership_level = 2; // Default membership level (2 = Essentials)
$dry_run = $is_cli && in_array('--dry-run', $argv); // Dry run from CLI flag

// Check for admin access if running from web
if (!$is_cli && !current_user_can('manage_options')) {
    wp_die('You must be an administrator to run this import.');
}

// Table names
$lessons_table = $wpdb->prefix . 'alm_lessons';
$chapters_table = $wpdb->prefix . 'alm_chapters';

// Statistics
$stats = array(
    'processed' => 0,
    'inserted' => 0,
    'skipped_no_title' => 0,
    'skipped_no_video' => 0,
    'errors' => 0
);

// Output handling for web or CLI
$output_fn = $is_cli ? 'printf' : function($text) { echo esc_html(str_replace("\n", "<br>\n", $text)); };
$line_end = $is_cli ? "\n" : "<br>\n";

// HTML header if running from web
if (!$is_cli) {
    echo '<!DOCTYPE html><html><head><title>Import Event Recordings</title></head><body>';
    echo '<h1>Import Event Recordings</h1>';
    echo '<pre style="background: #f0f0f0; padding: 20px; font-family: monospace;">';
}

$output_fn("Starting import of event recordings...$line_end");
$output_fn("==============================================$line_end");

// Read CSV file
if (!file_exists($csv_file)) {
    $error = "Error: CSV file not found at $csv_file";
    $output_fn("$error$line_end");
    if (!$is_cli) {
        echo '</pre></body></html>';
    }
    exit(1);
}

$handle = fopen($csv_file, 'r');
if (!$handle) {
    $error = "Error: Could not open CSV file";
    $output_fn("$error$line_end");
    if (!$is_cli) {
        echo '</pre></body></html>';
    }
    exit(1);
}

// Skip header row
$header = fgetcsv($handle);
$header_count = count($header);

$output_fn("CSV Headers: " . implode(', ', $header) . "$line_end");
$output_fn("==============================================$line_end");

// Process each row
while (($row = fgetcsv($handle)) !== false) {
    $stats['processed']++;
    
    // Map columns to array
    $data = array();
    foreach ($header as $index => $column) {
        $data[$column] = isset($row[$index]) ? $row[$index] : '';
    }
    
    // Skip empty rows
    if (empty($data['title']) || empty(trim($data['title']))) {
        $stats['skipped_no_title']++;
        continue;
    }
    
    // Clean the title
    $title = trim($data['title']);
    
    // Extract video information
    $vimeo_id = !empty($data['vimeo_id']) ? trim($data['vimeo_id']) : '';
    $bunny_url = !empty($data['Class Event Bunny URL']) ? trim($data['Class Event Bunny URL']) : '';
    $replay_vimeo_id = !empty($data['Class Replay Vimeo ID']) ? trim($data['Class Replay Vimeo ID']) : '';
    
    // Skip if no video URL
    if (empty($vimeo_id) && empty($bunny_url) && empty($replay_vimeo_id)) {
        $stats['skipped_no_video']++;
        if ($stats['processed'] % 100 == 0) {
            $output_fn("Skipped (no video): $title$line_end");
        }
        continue;
    }
    
    // Use vimeo_id or replay_vimeo_id
    $final_vimeo_id = !empty($vimeo_id) ? intval($vimeo_id) : (!empty($replay_vimeo_id) ? intval($replay_vimeo_id) : 0);
    
    // Duration
    $duration = !empty($data['duration']) ? intval($data['duration']) : 0;
    
    // Description
    $description = !empty($data['Content']) ? $data['Content'] : '';
    
    // Date
    $post_date = !empty($data['date']) ? $data['date'] : null;
    
    if ($dry_run) {
        $output_fn("[DRY RUN] Would create lesson: $title$line_end");
        $output_fn("  - Vimeo ID: $final_vimeo_id$line_end");
        $output_fn("  - Bunny URL: " . (!empty($bunny_url) ? "Yes" : "No") . "$line_end");
        $output_fn("  - Duration: $duration seconds$line_end");
        $output_fn("  - Post Date: $post_date$line_end");
        $output_fn("$line_end");
        $stats['inserted']++;
        continue;
    }
    
    // Insert lesson
    $lesson_data = array(
        'collection_id' => $collection_id,
        'lesson_title' => $title,
        'lesson_description' => $description,
        'post_date' => $post_date,
        'duration' => $duration,
        'song_lesson' => 'n',
        'slug' => sanitize_title($title),
        'membership_level' => $membership_level,
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql')
    );
    
    $result = $wpdb->insert($lessons_table, $lesson_data);
    
    if ($result === false) {
        $output_fn("ERROR inserting lesson: $title - " . $wpdb->last_error . "$line_end");
        $stats['errors']++;
        continue;
    }
    
    $lesson_id = $wpdb->insert_id;
    $stats['inserted']++;
    
    // Create slug for chapter title (use lesson title)
    $chapter_title = $title;
    
    // Insert chapter
    $chapter_data = array(
        'lesson_id' => $lesson_id,
        'chapter_title' => $chapter_title,
        'menu_order' => 1,
        'vimeo_id' => $final_vimeo_id,
        'bunny_url' => $bunny_url,
        'youtube_id' => '', // Not used in this import
        'duration' => $duration,
        'free' => 'n',
        'slug' => sanitize_title($title),
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql')
    );
    
    $chapter_result = $wpdb->insert($chapters_table, $chapter_data);
    
    if ($chapter_result === false) {
        $output_fn("ERROR inserting chapter for lesson: $title - " . $wpdb->last_error . "$line_end");
        $stats['errors']++;
    }
    
    // Progress indicator
    if ($stats['inserted'] % 100 == 0) {
        $output_fn("Processed {$stats['inserted']} lessons...$line_end");
        // Flush output for web
        if (!$is_cli) {
            ob_flush();
            flush();
        }
    }
}

fclose($handle);

$output_fn("$line_end==============================================$line_end");
$output_fn("Import complete!$line_end");
$output_fn("==============================================$line_end");
$output_fn("Total rows processed: {$stats['processed']}$line_end");
$output_fn("Lessons inserted: {$stats['inserted']}$line_end");
$output_fn("Skipped (no title): {$stats['skipped_no_title']}$line_end");
$output_fn("Skipped (no video): {$stats['skipped_no_video']}$line_end");
$output_fn("Errors: {$stats['errors']}$line_end");
$output_fn("$line_end");

// Close HTML if running from web
if (!$is_cli) {
    echo '</pre>';
    echo '<p><a href="' . admin_url('admin.php?page=academy-manager-lessons') . '">View Lessons</a></p>';
    echo '</body></html>';
}
