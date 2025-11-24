<?php
/**
 * Plugin Name: Bunny Video Webhook
 * Plugin URI: https://jazzedge.com
 * Description: Captures Bunny.net video uploads via webhook and automatically updates lesson database with sample video URLs.
 * Version: 1.0.0
 * Author: JazzEdge
 * License: GPL v2 or later
 * Text Domain: bunny-video-webhook
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BUNNY_VIDEO_WEBHOOK_VERSION', '1.0.0');
define('BUNNY_VIDEO_WEBHOOK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BUNNY_VIDEO_WEBHOOK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BUNNY_VIDEO_WEBHOOK_PLUGIN_FILE', __FILE__);

// Include required files
require_once BUNNY_VIDEO_WEBHOOK_PLUGIN_DIR . 'includes/class-logger.php';
require_once BUNNY_VIDEO_WEBHOOK_PLUGIN_DIR . 'includes/class-video-processor.php';
require_once BUNNY_VIDEO_WEBHOOK_PLUGIN_DIR . 'includes/class-webhook-handler.php';
require_once BUNNY_VIDEO_WEBHOOK_PLUGIN_DIR . 'includes/class-admin.php';

/**
 * Main Bunny Video Webhook Plugin Class
 */
class Bunny_Video_Webhook {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Webhook Handler
     */
    public $webhook_handler;
    
    /**
     * Admin Interface
     */
    public $admin;
    
    /**
     * Get single instance
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
     * Initialize plugin
     */
    private function init() {
        // Initialize webhook handler
        $this->webhook_handler = new Bunny_Video_Webhook_Handler();
        
        // Initialize admin interface
        if (is_admin()) {
            $this->admin = new Bunny_Video_Webhook_Admin();
        }
    }
}

/**
 * Initialize the plugin
 */
function bunny_video_webhook() {
    return Bunny_Video_Webhook::get_instance();
}

// Initialize plugin
bunny_video_webhook();

