<?php
/**
 * Plugin Name: Academy Analytics
 * Plugin URI: https://jazzedge.com
 * Description: Simple analytics system for tracking form submissions and page visits via webhook with CRUD interface and reporting.
 * Version: 1.0.0
 * Author: JazzEdge
 * Text Domain: academy-analytics
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
if (!defined('ACADEMY_ANALYTICS_VERSION')) {
    define('ACADEMY_ANALYTICS_VERSION', '1.0.0');
}
if (!defined('ACADEMY_ANALYTICS_PLUGIN_URL')) {
    define('ACADEMY_ANALYTICS_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('ACADEMY_ANALYTICS_PLUGIN_PATH')) {
    define('ACADEMY_ANALYTICS_PLUGIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('ACADEMY_ANALYTICS_PLUGIN_BASENAME')) {
    define('ACADEMY_ANALYTICS_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

// Include required files
require_once ACADEMY_ANALYTICS_PLUGIN_PATH . 'includes/class-database.php';
require_once ACADEMY_ANALYTICS_PLUGIN_PATH . 'includes/class-webhook.php';
require_once ACADEMY_ANALYTICS_PLUGIN_PATH . 'includes/class-direct-handler.php';
require_once ACADEMY_ANALYTICS_PLUGIN_PATH . 'includes/class-admin.php';

/**
 * Main Academy Analytics Plugin Class
 */
if (!class_exists('Academy_Analytics')) {
    class Academy_Analytics {
        
        /**
         * Single instance of the class
         */
        private static $instance = null;
        
        /**
         * Database instance
         */
        public $database;
        
        /**
         * Webhook Handler
         */
        public $webhook;
        
        /**
         * Admin Interface
         */
        public $admin;
        
        /**
         * Get single instance
         */
        public static function instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        /**
         * Constructor
         */
        private function __construct() {
            $this->init_hooks();
            $this->init_components();
        }
        
        /**
         * Initialize hooks
         */
        private function init_hooks() {
            add_action('init', array($this, 'init'));
            add_action('rest_api_init', array($this, 'init_rest_api'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        }
        
        /**
         * Initialize components
         */
        private function init_components() {
            $this->database = new Academy_Analytics_Database();
            $this->webhook = new Academy_Analytics_Webhook();
            $this->admin = new Academy_Analytics_Admin();
        }
        
        /**
         * Initialize plugin
         */
        public function init() {
            // Load text domain
            load_plugin_textdomain('academy-analytics', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }
        
        /**
         * Initialize REST API
         */
        public function init_rest_api() {
            if ($this->webhook && method_exists($this->webhook, 'init_rest_api')) {
                $this->webhook->init_rest_api();
            }
        }
        
        /**
         * Enqueue admin scripts
         */
        public function enqueue_admin_scripts($hook) {
            if (strpos($hook, 'academy-analytics') !== false) {
                // Load admin script after Chart.js if on reports page
                $deps = array('jquery');
                if (strpos($hook, 'academy-analytics-reports') !== false) {
                    $deps[] = 'chart-js';
                }
                wp_enqueue_script('academy-analytics-admin', ACADEMY_ANALYTICS_PLUGIN_URL . 'assets/js/admin.js', $deps, ACADEMY_ANALYTICS_VERSION, true);
                wp_enqueue_style('academy-analytics-admin', ACADEMY_ANALYTICS_PLUGIN_URL . 'assets/css/admin.css', array(), ACADEMY_ANALYTICS_VERSION);
                
                // Enqueue Chart.js for reports page (load before our admin script)
                // Enqueue Chart.js for reports and dashboard pages
                if (strpos($hook, 'academy-analytics-reports') !== false || strpos($hook, 'academy-analytics-dashboard') !== false) {
                    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', false);
                }
                
                wp_localize_script('academy-analytics-admin', 'academyAnalytics', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'rest_url' => rest_url('academy-analytics/v1/'),
                    'nonce' => wp_create_nonce('academy_analytics_nonce'),
                    'strings' => array(
                        'delete_confirm' => __('Are you sure you want to delete this event?', 'academy-analytics'),
                        'bulk_delete_confirm' => __('Are you sure you want to delete the selected events?', 'academy-analytics'),
                    )
                ));
            }
        }
        
        /**
         * Plugin activation
         */
        public function activate() {
            // Ensure database class is loaded
            if (!class_exists('Academy_Analytics_Database')) {
                require_once ACADEMY_ANALYTICS_PLUGIN_PATH . 'includes/class-database.php';
            }
            
            // Create database tables
            $database = new Academy_Analytics_Database();
            $database->create_tables();
            
            // Set default options
            add_option('academy_analytics_webhook_secret', wp_generate_password(32, false));
            add_option('academy_analytics_enabled', true);
            
            // Set default event types
            $default_event_types = array(
                'form_submission' => array(
                    'label' => 'Form Submission',
                    'description' => 'Triggered when a form is submitted'
                ),
                'page_visit' => array(
                    'label' => 'Page Visit',
                    'description' => 'Triggered when a page is visited'
                )
            );
            
            if (get_option('academy_analytics_event_types') === false) {
                add_option('academy_analytics_event_types', $default_event_types);
            }
            
            // Set default throttle (60 seconds)
            if (get_option('academy_analytics_throttle_seconds') === false) {
                add_option('academy_analytics_throttle_seconds', 60);
            }
            
            // Flush rewrite rules
            flush_rewrite_rules();
        }
        
        /**
         * Plugin deactivation
         */
        public function deactivate() {
            // Flush rewrite rules
            flush_rewrite_rules();
        }
        
        /**
         * Get webhook URL
         */
        public function get_webhook_url() {
            return rest_url('academy-analytics/v1/webhook');
        }
        
        /**
         * Get webhook secret
         */
        public function get_webhook_secret() {
            return get_option('academy_analytics_webhook_secret', '');
        }
        
        /**
         * Get available event types
         * 
         * @return array Event types array
         */
        public function get_event_types() {
            $event_types = get_option('academy_analytics_event_types', array());
            
            // Ensure defaults exist
            if (empty($event_types)) {
                $defaults = array(
                    'form_submission' => array(
                        'label' => 'Form Submission',
                        'description' => 'Triggered when a form is submitted'
                    ),
                    'page_visit' => array(
                        'label' => 'Page Visit',
                        'description' => 'Triggered when a page is visited'
                    )
                );
                update_option('academy_analytics_event_types', $defaults);
                return $defaults;
            }
            
            return $event_types;
        }
    }
}

/**
 * Global function to access the plugin instance
 */
if (!function_exists('academy_analytics')) {
    function academy_analytics() {
        return Academy_Analytics::instance();
    }
}

/**
 * Plugin activation function
 * Must be outside the class for register_activation_hook to work properly
 */
function academy_analytics_activate() {
    // Ensure database class is loaded
    require_once ACADEMY_ANALYTICS_PLUGIN_PATH . 'includes/class-database.php';
    
    // Create database tables
    $database = new Academy_Analytics_Database();
    $database->create_tables();
    
    // Set default options
    add_option('academy_analytics_webhook_secret', wp_generate_password(32, false));
    add_option('academy_analytics_enabled', true);
    
    // Set default event types
    $default_event_types = array(
        'form_submission' => array(
            'label' => 'Form Submission',
            'description' => 'Triggered when a form is submitted'
        ),
        'page_visit' => array(
            'label' => 'Page Visit',
            'description' => 'Triggered when a page is visited'
        )
    );
    
    if (get_option('academy_analytics_event_types') === false) {
        add_option('academy_analytics_event_types', $default_event_types);
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Plugin deactivation function
 */
function academy_analytics_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register activation and deactivation hooks (must be at file level)
register_activation_hook(__FILE__, 'academy_analytics_activate');
register_deactivation_hook(__FILE__, 'academy_analytics_deactivate');

// Initialize the plugin
add_action('plugins_loaded', 'academy_analytics_init');

function academy_analytics_init() {
    return Academy_Analytics::instance();
}

