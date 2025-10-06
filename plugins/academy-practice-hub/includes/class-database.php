<?php
// Proxy wrapper for JPH_Database to enable gradual refactor without behavior changes.
if (!defined('ABSPATH')) { exit; }

// If the original class is already loaded (wire-through), reuse it.
if (!class_exists('JPH_Database')) {
    // Define the missing constant that the original database class expects
    if (!defined('JPH_PLUGIN_PATH')) {
        define('JPH_PLUGIN_PATH', WP_PLUGIN_DIR . '/jazzedge-practice-hub/');
    }
    
    // Attempt to load from original plugin if available.
    $original = WP_PLUGIN_DIR . '/jazzedge-practice-hub/includes/class-database.php';
    if (file_exists($original)) {
        require_once $original;
    }
} else {
    // Class already exists, just ensure the constant is defined
    if (!defined('JPH_PLUGIN_PATH')) {
        define('JPH_PLUGIN_PATH', WP_PLUGIN_DIR . '/jazzedge-practice-hub/');
    }
}

// Expose the same class name, deferring to the original implementation.
// This file ensures the class exists within the Academy plugin namespace path.

