<?php
/**
 * Clear Chapter Transcription Jobs Script
 * 
 * This script stops the transcription queue and clears/resets all jobs.
 * 
 * Usage Options:
 * 1. Run via WP-CLI: wp eval-file clear-transcription-jobs.php
 * 2. Access via browser: add ?clear_transcription_jobs=1&nonce=YOUR_NONCE to any page (not recommended for production)
 * 
 * IMPORTANT: This will stop all transcription processing and reset the queue!
 */

// Prevent direct access in browser without proper authentication
if (!defined('ABSPATH')) {
    // Allow WP-CLI access
    if (php_sapi_name() !== 'cli') {
        die('Direct access not allowed');
    }
    // Try to find wp-load.php
    $wp_load_paths = array(
        dirname(__FILE__) . '/wp-load.php',
        dirname(__FILE__) . '/../wp-load.php',
        dirname(__FILE__) . '/../../wp-load.php',
        dirname(__FILE__) . '/../../../wp-load.php',
        dirname(__FILE__) . '/../../../../wp-load.php',
    );
    
    $wp_load_found = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            $wp_load_found = true;
            break;
        }
    }
    
    if (!$wp_load_found) {
        die("Error: Could not find wp-load.php. Please run this script from the WordPress root directory or specify the path.\n");
    }
}

// Security check for browser access
if (isset($_GET['clear_transcription_jobs']) && php_sapi_name() !== 'cli') {
    if (!current_user_can('manage_options')) {
        die('Insufficient permissions');
    }
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'clear_transcription_jobs_action')) {
        die('Invalid nonce');
    }
}

global $wpdb;
$queue_table = $wpdb->prefix . 'alm_transcription_jobs';

echo "=== Chapter Transcription Job Management ===\n\n";

// Step 1: Stop the cron job
echo "Step 1: Stopping transcription queue cron job...\n";
$cleared = wp_clear_scheduled_hook('ct_process_transcription_queue');
if ($cleared > 0) {
    echo "  ✓ Cleared {$cleared} scheduled instance(s) of 'ct_process_transcription_queue'\n";
} else {
    echo "  ✓ No scheduled cron jobs found (already stopped)\n";
}

// Step 2: Clear the queue lock
echo "\nStep 2: Clearing transcription queue lock...\n";
delete_transient('ct_queue_lock');
echo "  ✓ Queue lock cleared\n";

// Step 3: Check current job status
echo "\nStep 3: Checking current job status...\n";
$table_exists = $wpdb->get_var($wpdb->prepare(
    "SHOW TABLES LIKE %s",
    $queue_table
));

if ($table_exists === $queue_table) {
    $pending = $wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status = 'pending'");
    $processing = $wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status = 'processing'");
    $completed = $wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status = 'completed'");
    $failed = $wpdb->get_var("SELECT COUNT(*) FROM {$queue_table} WHERE status = 'failed'");
    
    echo "  Current queue status:\n";
    echo "    - Pending: {$pending}\n";
    echo "    - Processing: {$processing}\n";
    echo "    - Completed: {$completed}\n";
    echo "    - Failed: {$failed}\n";
    
    // Step 4: Reset pending/processing jobs back to pending
    echo "\nStep 4: Resetting pending and processing jobs...\n";
    $reset_count = $wpdb->query("
        UPDATE {$queue_table} 
        SET status = 'pending', 
            started_at = NULL, 
            completed_at = NULL,
            attempts = 0,
            message = ''
        WHERE status IN ('pending', 'processing')
    ");
    
    if ($reset_count > 0) {
        echo "  ✓ Reset {$reset_count} job(s) back to pending status\n";
    } else {
        echo "  ✓ No jobs to reset\n";
    }
    
    // Optional: Clear all jobs (uncomment if you want to delete all jobs)
    // echo "\nStep 5: Clearing all jobs from queue...\n";
    // $deleted = $wpdb->query("DELETE FROM {$queue_table}");
    // echo "  ✓ Deleted {$deleted} job(s) from queue\n";
    
} else {
    echo "  ⚠ Queue table does not exist yet\n";
}

// Step 5: Clear any stuck transcription transients
echo "\nStep 5: Clearing stuck transcription transients...\n";
$transient_keys = $wpdb->get_col(
    "SELECT option_name FROM {$wpdb->options} 
     WHERE option_name LIKE '_transient_ct_transcription_%' 
     AND option_name NOT LIKE '_transient_timeout_%'"
);

$cleared_transients = 0;
foreach ($transient_keys as $key) {
    $transient_name = str_replace('_transient_', '', $key);
    delete_transient($transient_name);
    $cleared_transients++;
}

if ($cleared_transients > 0) {
    echo "  ✓ Cleared {$cleared_transients} transcription transient(s)\n";
} else {
    echo "  ✓ No transcription transients found\n";
}

// Step 6: Clear last run timestamp
echo "\nStep 6: Clearing last run timestamp...\n";
delete_transient('ct_queue_last_run');
echo "  ✓ Last run timestamp cleared\n";

echo "\n=== Summary ===\n";
echo "✓ Transcription queue cron job stopped\n";
echo "✓ Queue lock cleared\n";
echo "✓ All pending/processing jobs reset to pending\n";
echo "✓ Stuck transients cleared\n";
echo "\nThe queue is now ready to restart. Jobs will be processed when the cron job is rescheduled.\n";
echo "\nTo restart the queue, the plugin will automatically reschedule the cron job on the next page load.\n";

