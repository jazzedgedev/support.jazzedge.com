<?php
/**
 * Clear Leaderboard Data Script
 * 
 * This script resets leaderboard-specific statistics without affecting other user data.
 * 
 * Usage Options:
 * 1. Run via WP-CLI: wp eval-file clear-leaderboard-data.php
 * 2. Access via browser: add ?clear_leaderboard=1&nonce=YOUR_NONCE to any page (not recommended for production)
 * 3. Include in functions.php or run directly
 * 
 * IMPORTANT: Backup your database before running this script!
 */

// Prevent direct access in browser without proper authentication
if (!defined('ABSPATH')) {
    // Allow WP-CLI access
    if (php_sapi_name() !== 'cli') {
        die('Direct access not allowed');
    }
    // Load WordPress for WP-CLI
    require_once(dirname(__FILE__) . '/../../../wp-load.php');
}

// Security check for browser access
if (isset($_GET['clear_leaderboard']) && php_sapi_name() !== 'cli') {
    if (!current_user_can('manage_options')) {
        die('Insufficient permissions');
    }
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'clear_leaderboard_action')) {
        die('Invalid nonce');
    }
}

global $wpdb;

$stats_table = $wpdb->prefix . 'jph_user_stats';
$options_table = $wpdb->options;

echo "Starting leaderboard data clearing process...\n\n";

// Step 1: Reset leaderboard statistics
echo "Step 1: Resetting leaderboard statistics...\n";
$stats_updated = $wpdb->query($wpdb->prepare(
    "UPDATE {$stats_table} 
    SET 
        total_xp = 0,
        current_level = 1,
        current_streak = 0,
        longest_streak = 0,
        total_sessions = 0,
        total_minutes = 0,
        badges_earned = 0,
        last_practice_date = NULL
    WHERE 1=1"
));
echo "✓ Reset statistics for {$stats_updated} users\n\n";

// Step 2: Clear leaderboard cache
echo "Step 2: Clearing leaderboard cache...\n";
$cache_deleted = $wpdb->query($wpdb->prepare(
    "DELETE FROM {$options_table} 
    WHERE option_name LIKE %s 
       OR option_name LIKE %s",
    '_transient%aph_cache_leaderboard%',
    '_transient_timeout%aph_cache_leaderboard%'
));
echo "✓ Cleared {$cache_deleted} cache entries\n\n";

// Step 3: Verification
echo "Step 3: Verifying data was cleared...\n";
$users_with_xp = $wpdb->get_var("SELECT COUNT(*) FROM {$stats_table} WHERE total_xp > 0");
$remaining_cache = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$options_table} 
    WHERE option_name LIKE %s OR option_name LIKE %s",
    '_transient%aph_cache_leaderboard%',
    '_transient_timeout%aph_cache_leaderboard%'
));

echo "Verification Results:\n";
echo "- Users with XP > 0: {$users_with_xp} (should be 0)\n";
echo "- Remaining cache entries: {$remaining_cache} (should be 0)\n\n";

if ($users_with_xp == 0 && $remaining_cache == 0) {
    echo "✓ SUCCESS: Leaderboard data cleared successfully!\n";
} else {
    echo "⚠ WARNING: Some data may not have been cleared. Please check manually.\n";
}

echo "\nDone!\n";

// Optional: Clear display names (uncomment if needed)
// echo "\nStep 4: Clearing display names...\n";
// $display_names_cleared = $wpdb->query("UPDATE {$stats_table} SET display_name = NULL WHERE display_name IS NOT NULL");
// echo "✓ Cleared {$display_names_cleared} display names\n";

// Optional: Reset visibility (uncomment if needed)
// echo "\nStep 5: Resetting leaderboard visibility...\n";
// $visibility_reset = $wpdb->query("UPDATE {$stats_table} SET show_on_leaderboard = 1 WHERE show_on_leaderboard = 0");
// echo "✓ Reset visibility for {$visibility_reset} users\n";

