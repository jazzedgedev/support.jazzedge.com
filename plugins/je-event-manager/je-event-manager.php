<?php
/**
 * Plugin Name: JE Event Manager
 * Plugin URI: https://jazzedge.com
 * Description: Manage JE Events with add, copy, delete, and column visibility controls
 * Version: 1.0.0
 * Author: JazzEdge
 * License: GPL v2 or later
 * Text Domain: je-event-manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JE_EVENT_MANAGER_VERSION', '1.0.0');
define('JE_EVENT_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JE_EVENT_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JE_EVENT_MANAGER_PLUGIN_FILE', __FILE__);

// Include required files
require_once JE_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-admin.php';

/**
 * Main JE Event Manager Class
 */
class JE_Event_Manager {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize admin
        if (is_admin()) {
            new JE_Event_Manager_Admin();
        }
    }
}

// Initialize the plugin
JE_Event_Manager::get_instance();

