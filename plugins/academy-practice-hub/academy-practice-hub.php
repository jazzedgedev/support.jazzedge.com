<?php
/**
 * Plugin Name: Academy Practice Hub
 * Description: Refactor scaffold for JazzEdge Practice Hub (no behavior yet).
 * Version: 0.0.1
 * Author: JazzEdge
 * Text Domain: academy-practice-hub
 */
if (!defined('ABSPATH')) { exit; }

// Intentionally minimal by default. Enable wire‑through to test parity without moving code.

// Define a toggle constant in wp-config.php or here to enable wire-through mode.
if (!defined('APH_WIRE_THROUGH')) {
    define('APH_WIRE_THROUGH', true); // temporarily enable for parity testing
}

// Load required classes (we'll instantiate conditionally)
require_once __DIR__ . '/includes/class-database.php';
require_once __DIR__ . '/includes/class-gamification.php';
require_once __DIR__ . '/includes/class-rest-api.php';

// Initialize REST API
new JPH_REST_API();

// Add asset enqueuing hooks to match original plugin
add_action('wp_enqueue_scripts', 'aph_enqueue_frontend_assets');
add_action('admin_enqueue_scripts', 'aph_enqueue_admin_assets');

function aph_enqueue_frontend_assets() {
    // Enqueue the same scripts as original plugin
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-dialog');
}

function aph_enqueue_admin_assets($hook) {
    // Only enqueue on our admin pages
    if (strpos($hook, 'jph-') !== false) {
        // Enqueue admin scripts if needed
    }
}

if (!defined('APH_FRONTEND_SEPARATED')) {
    define('APH_FRONTEND_SEPARATED', false); // keep false until assets are ported
}
if (!APH_FRONTEND_SEPARATED) {
    // Do not instantiate our frontend yet; rely on wire-through to keep CSS/JS and admin menus
} else {
    require_once __DIR__ . '/includes/class-frontend.php';
    new JPH_Frontend();
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
