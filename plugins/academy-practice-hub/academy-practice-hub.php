<?php
/**
 * Plugin Name: Academy Practice Hub
 * Description: Complete practice tracking and gamification system with leaderboards, badges, and progress analytics for JazzEdge Academy students.
 * Version: 4.0
 * Author: JazzEdge
 * Text Domain: academy-practice-hub
 */
if (!defined('ABSPATH')) { exit; }

// Intentionally minimal by default. Enable wire‑through to test parity without moving code.

// Define a toggle constant in wp-config.php or here to enable wire-through mode.
if (!defined('APH_WIRE_THROUGH')) {
    define('APH_WIRE_THROUGH', false); // Disabled - Academy plugin is now self-contained
}

// Load required classes (we'll instantiate conditionally)
require_once __DIR__ . '/includes/database-schema.php';
require_once __DIR__ . '/includes/class-database.php';
require_once __DIR__ . '/includes/class-gamification.php';
require_once __DIR__ . '/includes/class-logger.php';
require_once __DIR__ . '/includes/class-rate-limiter.php';
require_once __DIR__ . '/includes/class-cache.php';
require_once __DIR__ . '/includes/class-validator.php';
require_once __DIR__ . '/includes/class-audit-logger.php';
require_once __DIR__ . '/includes/class-rest-api.php';
require_once __DIR__ . '/includes/class-admin-pages.php';
require_once __DIR__ . '/includes/class-frontend.php';

// Initialize database schema on activation
register_activation_hook(__FILE__, 'aph_activate');

function aph_activate() {
    // Create tables
    APH_Database_Schema::create_tables();
    
    // Add leaderboard columns to existing tables
    APH_Database_Schema::add_leaderboard_columns();

    // Add timezone columns to practice sessions table
    APH_Database_Schema::add_practice_session_timezone_columns();
    
    // Update badges schema to use image_url instead of icon
    APH_Database_Schema::update_badges_schema();
    
    // Add additional performance indexes
    APH_Database_Schema::add_additional_indexes();
}

// Initialize REST API
new JPH_REST_API();

// Initialize Admin Pages
new JPH_Admin_Pages();

// Ensure timezone columns exist for practice sessions
add_action('init', 'jph_ensure_practice_session_timezone_columns');
function jph_ensure_practice_session_timezone_columns() {
    APH_Database_Schema::add_practice_session_timezone_columns();
}

// Initialize practice session timezone backfill
add_action('init', 'jph_init_practice_session_timezone_backfill');
function jph_init_practice_session_timezone_backfill() {
    if (get_option('jph_practice_session_timezone_backfill_complete')) {
        return;
    }

    if (!wp_next_scheduled('jph_backfill_practice_session_timezones')) {
        wp_schedule_single_event(time() + 60, 'jph_backfill_practice_session_timezones');
    }
}

add_action('jph_backfill_practice_session_timezones', 'jph_run_practice_session_timezone_backfill');
function jph_run_practice_session_timezone_backfill() {
    global $wpdb;

    $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
    $batch_size = 500;
    $last_id = (int) get_option('jph_practice_session_backfill_last_id', 0);
    $wp_timezone = wp_timezone();
    $utc_timezone = new DateTimeZone('UTC');

    $sessions = $wpdb->get_results($wpdb->prepare(
        "SELECT id, user_id, created_at, created_at_utc, user_timezone_at_session
         FROM {$sessions_table}
         WHERE id > %d
         ORDER BY id ASC
         LIMIT %d",
        $last_id,
        $batch_size
    ), ARRAY_A);

    if (empty($sessions)) {
        update_option('jph_practice_session_timezone_backfill_complete', 1);
        delete_option('jph_practice_session_backfill_last_id');
        return;
    }

    foreach ($sessions as $session) {
        $session_id = (int) $session['id'];
        $user_id = (int) $session['user_id'];

        $updates = array();
        $formats = array();

        if (empty($session['created_at_utc']) && !empty($session['created_at'])) {
            $session_datetime = new DateTime($session['created_at'], $wp_timezone);
            $session_datetime->setTimezone($utc_timezone);
            $updates['created_at_utc'] = $session_datetime->format('Y-m-d H:i:s');
            $formats[] = '%s';
        }

        if (empty($session['user_timezone_at_session'])) {
            $timezone_string = get_user_meta($user_id, 'aph_user_timezone', true);
            if (!empty($timezone_string)) {
                try {
                    new DateTimeZone($timezone_string);
                } catch (Exception $e) {
                    $timezone_string = '';
                }
            }
            if (empty($timezone_string)) {
                $timezone_string = wp_timezone_string();
            }
            $updates['user_timezone_at_session'] = $timezone_string;
            $formats[] = '%s';
        }

        if (!empty($updates)) {
            $wpdb->update(
                $sessions_table,
                $updates,
                array('id' => $session_id),
                $formats,
                array('%d')
            );
        }

        $last_id = $session_id;
    }

    update_option('jph_practice_session_backfill_last_id', $last_id);
    wp_schedule_single_event(time() + 60, 'jph_backfill_practice_session_timezones');
}

// Initialize Frontend (conditionally based on wire-through setting)
if (!defined('APH_FRONTEND_SEPARATED')) {
    define('APH_FRONTEND_SEPARATED', true); // Enable our frontend class
}
if (APH_FRONTEND_SEPARATED) {
    new JPH_Frontend();
}

// Register with Katahdin AI Hub if available
add_action('katahdin_ai_hub_init', function($hub) {
    $hub->register_plugin('academy-practice-hub', array(
        'name' => 'Academy Practice Hub',
        'version' => '4.0',
        'features' => array('chat', 'completions'),
        'quota_limit' => 5000 // tokens per month
    ));
});

// Also try to register on init in case the hook was already fired
add_action('init', function() {
    if (class_exists('Katahdin_AI_Hub') && function_exists('katahdin_ai_hub')) {
        $hub = katahdin_ai_hub();
        if ($hub && method_exists($hub, 'register_plugin')) {
            $hub->register_plugin('academy-practice-hub', array(
                'name' => 'Academy Practice Hub',
                'version' => '4.0',
                'features' => array('chat', 'completions'),
                'quota_limit' => 5000 // tokens per month
            ));
        }
    }
});

// Register plugin on admin init (when admin permissions are available)
add_action('admin_init', function() {
    if (class_exists('Katahdin_AI_Hub') && function_exists('katahdin_ai_hub')) {
        $hub = katahdin_ai_hub();
        if ($hub && method_exists($hub, 'register_plugin')) {
            $hub->register_plugin('academy-practice-hub', array(
                'name' => 'Academy Practice Hub',
                'version' => '4.0',
                'features' => array('chat', 'completions'),
                'quota_limit' => 5000 // tokens per month
            ));
        }
    }
});

// Add asset enqueuing hooks to match original plugin
add_action('wp_enqueue_scripts', 'aph_enqueue_frontend_assets');
add_action('admin_enqueue_scripts', 'aph_enqueue_admin_assets');

/**
 * Enqueue frontend assets
 */
function aph_enqueue_frontend_assets() {
    // Only enqueue on pages that might have our shortcode
    if (is_singular() || is_home() || is_front_page()) {
        wp_enqueue_script('jquery');
    }
}

/**
 * Enqueue admin assets
 */
function aph_enqueue_admin_assets() {
    // Enqueue jQuery for admin pages
    wp_enqueue_script('jquery');
}


// When enabled, bootstrap the existing JazzEdge Practice Hub code paths for parity testing.
if (APH_WIRE_THROUGH) {
    // Only run if the original plugin is not active to avoid double-loading.
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    $original_plugin_slug = 'jazzedge-practice-hub/jazzedge-practice-hub.php';
    if (!is_plugin_active($original_plugin_slug)) {
        // Load the original plugin main file to restore admin menus, assets, and shortcode rendering
        $original_main = WP_PLUGIN_DIR . '/jazzedge-practice-hub/jazzedge-practice-hub.php';
        if (file_exists($original_main)) {
            require_once $original_main;
        }
    }
}
