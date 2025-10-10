<?php
/**
 * Plugin Name: Katahdin AI Hub
 * Plugin URI: https://katahdin.ai
 * Description: Centralized AI integration hub for all Katahdin AI-powered WordPress plugins. Manages API keys, usage quotas, and provides unified AI services.
 * Version: 1.0.0
 * Author: Katahdin AI - katahdin.ai
 * Author URI: https://katahdin.ai
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: katahdin-ai-hub
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
if (!defined('KATAHDIN_AI_HUB_VERSION')) {
    define('KATAHDIN_AI_HUB_VERSION', '1.0.0');
}
if (!defined('KATAHDIN_AI_HUB_PLUGIN_URL')) {
    define('KATAHDIN_AI_HUB_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('KATAHDIN_AI_HUB_PLUGIN_PATH')) {
    define('KATAHDIN_AI_HUB_PLUGIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('KATAHDIN_AI_HUB_PLUGIN_BASENAME')) {
    define('KATAHDIN_AI_HUB_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

// Include required files
$includes_path = KATAHDIN_AI_HUB_PLUGIN_PATH . 'includes/';

if (file_exists($includes_path . 'class-api-manager.php')) {
    require_once $includes_path . 'class-api-manager.php';
}
if (file_exists($includes_path . 'class-plugin-registry.php')) {
    require_once $includes_path . 'class-plugin-registry.php';
}
if (file_exists($includes_path . 'class-usage-tracker.php')) {
    require_once $includes_path . 'class-usage-tracker.php';
}
if (file_exists($includes_path . 'class-admin-simple.php')) {
    require_once $includes_path . 'class-admin-simple.php';
}
if (file_exists($includes_path . 'class-admin.php')) {
    require_once $includes_path . 'class-admin.php';
}
if (file_exists($includes_path . 'class-rest-api.php')) {
    require_once $includes_path . 'class-rest-api.php';
}

/**
 * Main Katahdin AI Hub Plugin Class
 */
if (!class_exists('Katahdin_AI_Hub')) {
class Katahdin_AI_Hub {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * API Manager
     */
    public $api_manager;
    
    /**
     * Plugin Registry
     */
    public $plugin_registry;
    
    /**
     * Usage Tracker
     */
    public $usage_tracker;
    
    /**
     * Admin Interface
     */
    public $admin;
    
    /**
     * Debug Admin Interface
     */
    public $admin_debug;
    
    /**
     * REST API
     */
    public $rest_api;
    
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
        
        // Initialize REST API early to ensure routes are registered
        if (class_exists('Katahdin_AI_Hub_REST_API')) {
            $this->rest_api = new Katahdin_AI_Hub_REST_API();
            // Ensure routes are registered immediately
            add_action('rest_api_init', array($this->rest_api, 'register_routes'));
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        // REST API is initialized by the REST_API class itself
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        try {
            if (class_exists('Katahdin_AI_Hub_API_Manager')) {
                $this->api_manager = new Katahdin_AI_Hub_API_Manager();
            }
            if (class_exists('Katahdin_AI_Hub_Plugin_Registry')) {
                $this->plugin_registry = new Katahdin_AI_Hub_Plugin_Registry();
            }
            if (class_exists('Katahdin_AI_Hub_Usage_Tracker')) {
                $this->usage_tracker = new Katahdin_AI_Hub_Usage_Tracker();
            }
            if (class_exists('Katahdin_AI_Hub_Admin')) {
                $this->admin = new Katahdin_AI_Hub_Admin();
            }
            if (class_exists('Katahdin_AI_Hub_Admin_Debug')) {
                $this->admin_debug = new Katahdin_AI_Hub_Admin_Debug();
            }
            // REST API is initialized in constructor to ensure routes are registered early
        } catch (Exception $e) {
            error_log('Katahdin AI Hub component initialization error: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('katahdin-ai-hub', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize components safely
        try {
            if ($this->api_manager && method_exists($this->api_manager, 'init')) {
                $this->api_manager->init();
            }
            if ($this->plugin_registry && method_exists($this->plugin_registry, 'init')) {
                $this->plugin_registry->init();
            }
            if ($this->usage_tracker && method_exists($this->usage_tracker, 'init')) {
                $this->usage_tracker->init();
            }
            if ($this->admin && method_exists($this->admin, 'init')) {
                $this->admin->init();
            }
            
            // Fire action for other plugins to register
            do_action('katahdin_ai_hub_init', $this);
        } catch (Exception $e) {
            error_log('Katahdin AI Hub initialization error: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize REST API (handled by REST_API class itself)
     */
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'katahdin-ai-hub') !== false) {
            wp_enqueue_script('katahdin-ai-hub-admin', KATAHDIN_AI_HUB_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), KATAHDIN_AI_HUB_VERSION, true);
            wp_enqueue_style('katahdin-ai-hub-admin', KATAHDIN_AI_HUB_PLUGIN_URL . 'assets/css/admin.css', array(), KATAHDIN_AI_HUB_VERSION);
            
            wp_localize_script('katahdin-ai-hub-admin', 'katahdin_ai_hub', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'rest_url' => rest_url('katahdin-ai-hub/v1/'),
                'nonce' => wp_create_nonce('katahdin_ai_hub_nonce'),
                'strings' => array(
                    'api_test_success' => __('API connection successful!', 'katahdin-ai-hub'),
                    'api_test_failed' => __('API connection failed. Please check your API key.', 'katahdin-ai-hub'),
                    'plugin_registered' => __('Plugin registered successfully!', 'katahdin-ai-hub'),
                    'error_occurred' => __('An error occurred. Please try again.', 'katahdin-ai-hub'),
                )
            ));
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->create_tables();
        
        // Set default options
        add_option('katahdin_ai_hub_openai_key', '');
        add_option('katahdin_ai_hub_debug_mode', false);
        add_option('katahdin_ai_hub_usage_limit', 10000); // Monthly limit
        add_option('katahdin_ai_hub_rate_limit', 60); // Requests per minute
        
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
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Plugin registry table
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            plugin_id varchar(50) NOT NULL,
            plugin_name varchar(100) NOT NULL,
            version varchar(20) NOT NULL,
            features text,
            quota_limit int(11) DEFAULT 1000,
            quota_used int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            registered_at datetime DEFAULT CURRENT_TIMESTAMP,
            last_used datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY plugin_id (plugin_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Usage tracking table
        $usage_table = $wpdb->prefix . 'katahdin_ai_usage';
        $usage_sql = "CREATE TABLE $usage_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            plugin_id varchar(50) NOT NULL,
            endpoint varchar(100) NOT NULL,
            tokens_used int(11) DEFAULT 0,
            cost decimal(10,4) DEFAULT 0.0000,
            response_time int(11) DEFAULT 0,
            success tinyint(1) DEFAULT 1,
            error_message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY plugin_id (plugin_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        dbDelta($usage_sql);
        
        // API logs table
        $logs_table = $wpdb->prefix . 'katahdin_ai_logs';
        $logs_sql = "CREATE TABLE $logs_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            plugin_id varchar(50) NOT NULL,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            context text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY plugin_id (plugin_id),
            KEY level (level),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        dbDelta($logs_sql);
    }
    
    /**
     * Register a plugin with the hub
     */
    public function register_plugin($plugin_id, $config) {
        return $this->plugin_registry->register($plugin_id, $config);
    }
    
    /**
     * Make an API call through the hub
     */
    public function make_api_call($plugin_id, $endpoint, $data, $options = array()) {
        return $this->api_manager->make_call($plugin_id, $endpoint, $data, $options);
    }
    
    /**
     * Get plugin usage statistics
     */
    public function get_plugin_stats($plugin_id) {
        return $this->usage_tracker->get_plugin_stats($plugin_id);
    }
    
    /**
     * Log a message
     */
    public function log($level, $message, $context = array()) {
        $this->usage_tracker->log($level, $message, $context);
    }
}
}

/**
 * Global function to access the hub instance
 */
if (!function_exists('katahdin_ai_hub')) {
function katahdin_ai_hub() {
    return Katahdin_AI_Hub::instance();
}
}

// Initialize the plugin
if (!function_exists('katahdin_ai_hub_init')) {
function katahdin_ai_hub_init() {
    return Katahdin_AI_Hub::instance();
}
}

// Start the plugin
if (!function_exists('katahdin_ai_hub_started')) {
    function katahdin_ai_hub_started() {
        return true;
    }
    katahdin_ai_hub_init();
}
