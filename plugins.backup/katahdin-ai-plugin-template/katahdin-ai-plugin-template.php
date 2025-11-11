<?php
/**
 * Plugin Name: Katahdin AI Plugin Template
 * Plugin URI: https://github.com/your-org/katahdin-ai-plugin-template
 * Description: Template plugin for creating new Katahdin AI Hub plugins. Copy this template and modify for your specific needs.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Text Domain: katahdin-ai-plugin-template
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KATAHDIN_AI_PLUGIN_TEMPLATE_VERSION', '1.0.0');
define('KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_FILE', __FILE__);
define('KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_ID', 'katahdin-ai-plugin-template');

/**
 * Main Plugin Class
 * 
 * This template provides a solid foundation for Katahdin AI Hub plugins.
 * Copy this file and modify the class name, constants, and functionality.
 */
class Katahdin_AI_Plugin_Template {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Plugin components
     */
    public $admin;
    public $api_handler;
    public $logger;
    public $database;
    
    /**
     * Get plugin instance
     */
    public static function instance() {
        if (self::$instance === null) {
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
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // WordPress init hook
        add_action('init', array($this, 'init'), 5);
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        }
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Load required files
        $this->load_dependencies();
        
        // Initialize components
        $this->admin = new Katahdin_AI_Plugin_Template_Admin();
        $this->api_handler = new Katahdin_AI_Plugin_Template_API_Handler();
        $this->logger = new Katahdin_AI_Plugin_Template_Logger();
        $this->database = new Katahdin_AI_Plugin_Template_Database();
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_DIR . 'includes/class-admin.php';
        require_once KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_DIR . 'includes/class-api-handler.php';
        require_once KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_DIR . 'includes/class-logger.php';
        require_once KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_DIR . 'includes/class-database.php';
    }
    
    /**
     * WordPress init hook
     */
    public function init() {
        // Register with Katahdin AI Hub
        $this->register_with_hub();
        
        // Initialize REST API
        $this->init_rest_api();
        
        // Load text domain
        load_plugin_textdomain('katahdin-ai-plugin-template', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Register with Katahdin AI Hub
     * 
     * This is the CRITICAL integration point with the hub.
     * Follow this pattern exactly for all Katahdin AI plugins.
     */
    private function register_with_hub() {
        // Check if hub is available
        if (!function_exists('katahdin_ai_hub')) {
            error_log('Katahdin AI Plugin Template: Hub not available');
            return;
        }
        
        $hub = katahdin_ai_hub();
        if (!$hub || !isset($hub->plugin_registry)) {
            error_log('Katahdin AI Plugin Template: Hub not properly initialized');
            return;
        }
        
        // Check if the plugin registry has a register method
        if (!method_exists($hub->plugin_registry, 'register')) {
            error_log('Katahdin AI Plugin Template: Plugin registry does not have register method');
            return;
        }
        
        // Register plugin with hub
        $config = array(
            'name' => 'Katahdin AI Plugin Template',
            'version' => KATAHDIN_AI_PLUGIN_TEMPLATE_VERSION,
            'features' => array(
                'ai_processing',
                'data_analysis',
                'custom_endpoints'
            ),
            'quota_limit' => 1000 // Monthly token limit
        );
        
        $result = $hub->plugin_registry->register(KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_ID, $config);
        
        // Only log errors, not successful registrations
        if (is_wp_error($result)) {
            error_log('Katahdin AI Plugin Template registration failed: ' . $result->get_error_message());
        }
    }
    
    /**
     * Initialize REST API endpoints
     */
    private function init_rest_api() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Status endpoint
        register_rest_route('katahdin-ai-plugin-template/v1', '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_status'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Process endpoint
        register_rest_route('katahdin-ai-plugin-template/v1', '/process', array(
            'methods' => 'POST',
            'callback' => array($this, 'process_data'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'data' => array(
                    'required' => true,
                    'type' => 'object',
                    'description' => 'Data to process'
                )
            )
        ));
        
        // Debug endpoint
        register_rest_route('katahdin-ai-plugin-template/v1', '/debug', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_debug_info'),
            'permission_callback' => array($this, 'check_permissions')
        ));
    }
    
    /**
     * Check REST API permissions
     */
    public function check_permissions($request) {
        // For admin endpoints, require admin capabilities
        if (is_admin()) {
            return current_user_can('manage_options');
        }
        
        // For public endpoints, implement your own authentication logic
        return true;
    }
    
    /**
     * Get plugin status
     */
    public function get_status($request) {
        $hub_status = $this->check_hub_status();
        
        return rest_ensure_response(array(
            'success' => true,
            'plugin' => 'Katahdin AI Plugin Template',
            'version' => KATAHDIN_AI_PLUGIN_TEMPLATE_VERSION,
            'hub_status' => $hub_status,
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Process data using AI
     */
    public function process_data($request) {
        try {
            $data = $request->get_param('data');
            
            if (empty($data)) {
                return new WP_Error('no_data', 'No data provided', array('status' => 400));
            }
            
            // Check hub availability
            if (!function_exists('katahdin_ai_hub')) {
                return new WP_Error('hub_not_available', 'Katahdin AI Hub not available', array('status' => 503));
            }
            
            $hub = katahdin_ai_hub();
            if (!$hub || !$hub->api_manager) {
                return new WP_Error('hub_not_initialized', 'Katahdin AI Hub not properly initialized', array('status' => 503));
            }
            
            // Prepare AI request
            $ai_data = array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are a helpful AI assistant.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => json_encode($data)
                    )
                ),
                'max_tokens' => 1000,
                'temperature' => 0.7
            );
            
            // Make API call through hub
            $result = $hub->api_manager->make_api_call('chat/completions', $ai_data);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            // Log the request
            $this->logger->log_request($data, $result);
            
            return rest_ensure_response(array(
                'success' => true,
                'result' => $result,
                'timestamp' => current_time('mysql')
            ));
            
        } catch (Exception $e) {
            error_log('Katahdin AI Plugin Template error: ' . $e->getMessage());
            return new WP_Error('processing_error', 'Error processing data: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get debug information
     */
    public function get_debug_info($request) {
        $debug_info = array(
            'plugin' => 'Katahdin AI Plugin Template',
            'version' => KATAHDIN_AI_PLUGIN_TEMPLATE_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'hub_available' => function_exists('katahdin_ai_hub'),
            'hub_status' => $this->check_hub_status(),
            'database_tables' => $this->database->get_table_status(),
            'plugin_options' => $this->get_plugin_options(),
            'timestamp' => current_time('mysql')
        );
        
        return rest_ensure_response($debug_info);
    }
    
    /**
     * Check Katahdin AI Hub status
     */
    private function check_hub_status() {
        if (!function_exists('katahdin_ai_hub')) {
            return 'Hub not available';
        }
        
        $hub = katahdin_ai_hub();
        if (!$hub) {
            return 'Hub not initialized';
        }
        
        if (!$hub->api_manager) {
            return 'API manager not available';
        }
        
        // Test API connection
        $test_result = $hub->api_manager->test_connection();
        if (is_wp_error($test_result)) {
            return 'API connection failed: ' . $test_result->get_error_message();
        }
        
        return 'Connected';
    }
    
    /**
     * Get plugin options
     */
    private function get_plugin_options() {
        return array(
            'plugin_enabled' => get_option('katahdin_ai_plugin_template_enabled', true),
            'debug_mode' => get_option('katahdin_ai_plugin_template_debug', false),
            'custom_setting' => get_option('katahdin_ai_plugin_template_custom', '')
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'AI Plugin Template',
            'AI Plugin Template',
            'manage_options',
            'katahdin-ai-plugin-template',
            array($this->admin, 'admin_page'),
            'dashicons-admin-generic',
            30
        );
        
        add_submenu_page(
            'katahdin-ai-plugin-template',
            'Settings',
            'Settings',
            'manage_options',
            'katahdin-ai-plugin-template-settings',
            array($this->admin, 'settings_page')
        );
        
        add_submenu_page(
            'katahdin-ai-plugin-template',
            'Logs',
            'Logs',
            'manage_options',
            'katahdin-ai-plugin-template-logs',
            array($this->admin, 'logs_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'katahdin-ai-plugin-template') === false) {
            return;
        }
        
        wp_enqueue_script('katahdin-ai-plugin-template-admin', KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), KATAHDIN_AI_PLUGIN_TEMPLATE_VERSION, true);
        wp_enqueue_style('katahdin-ai-plugin-template-admin', KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_URL . 'assets/css/admin.css', array(), KATAHDIN_AI_PLUGIN_TEMPLATE_VERSION);
        
        wp_localize_script('katahdin-ai-plugin-template-admin', 'katahdinAIPluginTemplate', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('katahdin_ai_plugin_template_nonce'),
            'restUrl' => rest_url('katahdin-ai-plugin-template/v1/'),
            'pluginId' => KATAHDIN_AI_PLUGIN_TEMPLATE_PLUGIN_ID
        ));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->database->create_tables();
        
        // Set default options
        add_option('katahdin_ai_plugin_template_enabled', true);
        add_option('katahdin_ai_plugin_template_debug', false);
        
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
}

/**
 * Initialize the plugin
 */
function katahdin_ai_plugin_template_init() {
    return Katahdin_AI_Plugin_Template::instance();
}

// Initialize the plugin
add_action('plugins_loaded', 'katahdin_ai_plugin_template_init');

/**
 * Global function to access plugin instance
 */
function katahdin_ai_plugin_template() {
    return Katahdin_AI_Plugin_Template::instance();
}
