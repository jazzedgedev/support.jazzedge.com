<?php
/**
 * Plugin Name: Academy AI Assistant
 * Plugin URI: https://jazzedge.com
 * Description: AI-powered chat assistants with multiple personality types to help piano students learn. Provides contextual assistance using lesson transcripts, embeddings, and student progress data.
 * Version: 1.0.1
 * Author: JazzEdge
 * License: GPL v2 or later
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Text Domain: academy-ai-assistant
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants with unique AAA_ prefix
define('AAA_VERSION', '1.0.1');
define('AAA_PLUGIN_FILE', __FILE__);
define('AAA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AAA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AAA_PLUGIN_ID', 'academy-ai-assistant');

/**
 * Main Academy AI Assistant Plugin Class
 * 
 * Provides AI-powered chat assistants with multiple personalities
 */
class Academy_AI_Assistant {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Plugin components
     */
    public $admin;
    public $database;
    public $rest_api;
    public $frontend;
    public $feature_flags;
    public $debug_logger;
    public $personality_manager;
    public $context_builder;
    public $embedding_search;
    
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
            add_action('admin_notices', array($this, 'check_dependencies'));
        }
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Load required files
        $this->load_dependencies();
        
        // Initialize components
        $this->database = new AAA_Database();
        $this->feature_flags = new AAA_Feature_Flags();
        $this->debug_logger = new AAA_Debug_Logger();
        $this->personality_manager = new AI_Personality_Manager(); // Note: Uses AI_ prefix (legacy)
        $this->context_builder = new AI_Context_Builder(); // Note: Uses AI_ prefix (legacy)
        $this->embedding_search = new AI_Embedding_Search(); // Note: Uses AI_ prefix (legacy)
        
        // REST API and Frontend (they check permissions internally)
        if (!class_exists('AAA_REST_API')) {
            error_log('Academy AI Assistant: AAA_REST_API class not found. Check if class-rest-api.php loaded correctly.');
            wp_die('Academy AI Assistant: Critical error - REST API class not found. Please check plugin files.');
        }
        $this->rest_api = new AAA_REST_API();
        
        if (!class_exists('AAA_Frontend')) {
            error_log('Academy AI Assistant: AAA_Frontend class not found. Check if class-frontend.php loaded correctly.');
            wp_die('Academy AI Assistant: Critical error - Frontend class not found. Please check plugin files.');
        }
        $this->frontend = new AAA_Frontend();
        
        if (is_admin()) {
            $this->admin = new AAA_Admin();
        }
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once AAA_PLUGIN_DIR . 'includes/class-database.php';
        require_once AAA_PLUGIN_DIR . 'includes/class-feature-flags.php';
        require_once AAA_PLUGIN_DIR . 'includes/class-debug-logger.php';
        
        // Load personality system
        require_once AAA_PLUGIN_DIR . 'includes/class-personality-base.php';
        require_once AAA_PLUGIN_DIR . 'includes/class-personality-manager.php';
        
        // Load context and embedding search
        require_once AAA_PLUGIN_DIR . 'includes/class-context-builder.php';
        require_once AAA_PLUGIN_DIR . 'includes/class-embedding-search.php';
        require_once AAA_PLUGIN_DIR . 'includes/class-lesson-search.php';
        require_once AAA_PLUGIN_DIR . 'includes/class-keyword-manager.php';
        require_once AAA_PLUGIN_DIR . 'includes/class-token-limits.php';
        
        // Load REST API and Frontend
        $rest_api_path = AAA_PLUGIN_DIR . 'includes/class-rest-api.php';
        if (file_exists($rest_api_path)) {
            require_once $rest_api_path;
        } else {
            error_log('Academy AI Assistant: REST API file not found at: ' . $rest_api_path);
        }
        
        $frontend_path = AAA_PLUGIN_DIR . 'includes/class-frontend.php';
        if (file_exists($frontend_path)) {
            require_once $frontend_path;
        } else {
            error_log('Academy AI Assistant: Frontend file not found at: ' . $frontend_path);
        }
        
        // Load personality implementations (only if they exist)
        $personality_files = array(
            'class-study-buddy.php',
            'class-practice-assistant.php',
            'class-coach.php',
            'class-professor.php',
            'class-mentor.php',
            'class-cheerleader.php'
        );
        
        foreach ($personality_files as $file) {
            $file_path = AAA_PLUGIN_DIR . 'includes/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
        
        if (is_admin()) {
            require_once AAA_PLUGIN_DIR . 'includes/class-admin.php';
        }
        
        // REST API and Frontend are always loaded (they check permissions internally)
        // No conditional loading needed
    }
    
    /**
     * WordPress init hook
     */
    public function init() {
        // Register with Katahdin AI Hub
        $this->register_with_hub();
        
        // Load text domain
        load_plugin_textdomain('academy-ai-assistant', false, dirname(plugin_basename(__FILE__)) . '/languages');
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
            error_log('Academy AI Assistant: Katahdin AI Hub not available');
            return;
        }
        
        $hub = katahdin_ai_hub();
        if (!$hub || !isset($hub->plugin_registry)) {
            error_log('Academy AI Assistant: Hub not properly initialized');
            return;
        }
        
        // Check if the plugin registry has a register method
        if (!method_exists($hub->plugin_registry, 'register')) {
            error_log('Academy AI Assistant: Plugin registry does not have register method');
            return;
        }
        
        // Register plugin with hub
        $config = array(
            'name' => 'Academy AI Assistant',
            'version' => AAA_VERSION,
            'features' => array(
                'chat',
                'completions',
                'embeddings'
            ),
            'quota_limit' => get_option('aaa_ai_quota_limit', 50000) // Monthly token limit
        );
        
        $result = $hub->plugin_registry->register(AAA_PLUGIN_ID, $config);
        
        // Only log errors, not successful registrations
        if (is_wp_error($result)) {
            error_log('Academy AI Assistant registration failed: ' . $result->get_error_message());
        }
    }
    
    
    /**
     * Check for required dependencies and show admin notice
     */
    public function check_dependencies() {
        if (!function_exists('katahdin_ai_hub')) {
            ?>
            <div class="notice notice-error">
                <p><strong>Academy AI Assistant:</strong> Katahdin AI Hub plugin is required but not active. Please activate it to use AI features.</p>
            </div>
            <?php
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Security: Only admins can access
        if (!current_user_can('manage_options')) {
            return;
        }
        
        add_menu_page(
            'AI Assistant',
            'AI Assistant',
            'manage_options',
            'academy-ai-assistant',
            array($this->admin, 'admin_page'),
            'dashicons-format-chat',
            30
        );
        
        add_submenu_page(
            'academy-ai-assistant',
            'Settings',
            'Settings',
            'manage_options',
            'academy-ai-assistant-settings',
            array($this->admin, 'settings_page')
        );
        
        add_submenu_page(
            'academy-ai-assistant',
            'Debug Logs',
            'Debug Logs',
            'manage_options',
            'academy-ai-assistant-debug',
            array($this->admin, 'debug_page')
        );
        
        add_submenu_page(
            'academy-ai-assistant',
            'Keyword Mappings',
            'Keyword Mappings',
            'manage_options',
            'academy-ai-assistant-keywords',
            array($this->admin, 'keywords_page')
        );
        
        add_submenu_page(
            'academy-ai-assistant',
            'AI Prompts',
            'AI Prompts',
            'manage_options',
            'academy-ai-assistant-prompts',
            array($this->admin, 'prompts_page')
        );
        
        add_submenu_page(
            'academy-ai-assistant',
            'Chip Prompts',
            'Chip Prompts',
            'manage_options',
            'academy-ai-assistant-chip-prompts',
            array($this->admin, 'chip_prompts_page')
        );
        
        add_submenu_page(
            'academy-ai-assistant',
            'Chip Suggestions',
            'Chip Suggestions',
            'manage_options',
            'academy-ai-assistant-chip-suggestions',
            array($this->admin, 'chip_suggestions_page')
        );
        
        add_submenu_page(
            'academy-ai-assistant',
            'Token Limits',
            'Token Limits',
            'manage_options',
            'academy-ai-assistant-token-limits',
            array($this->admin, 'token_limits_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'academy-ai-assistant') === false) {
            return;
        }
        
        wp_enqueue_script('aaa-admin', AAA_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), AAA_VERSION, true);
        wp_enqueue_style('aaa-admin', AAA_PLUGIN_URL . 'assets/css/admin.css', array(), AAA_VERSION);
        
        wp_localize_script('aaa-admin', 'aaaAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aaa_admin_nonce'),
            'restUrl' => rest_url('academy-ai-assistant/v1/'),
            'pluginId' => AAA_PLUGIN_ID
        ));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        // Only enqueue if user has access
        if (!$this->feature_flags->user_can_access_ai()) {
            return;
        }
        
        wp_enqueue_script('aaa-assistant', AAA_PLUGIN_URL . 'assets/js/assistant.js', array('jquery'), AAA_VERSION, true);
        wp_enqueue_style('aaa-assistant', AAA_PLUGIN_URL . 'assets/css/assistant.css', array(), AAA_VERSION);
        
        wp_localize_script('aaa-assistant', 'aaaAssistant', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aaa_assistant_nonce'),
            'restUrl' => rest_url('academy-ai-assistant/v1/'),
            'userId' => get_current_user_id(),
            'debugMode' => get_option('aaa_debug_enabled', false)
        ));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Load database class file first (activation runs before full plugin init)
        require_once AAA_PLUGIN_DIR . 'includes/class-database.php';
        
        // Check for required dependencies
        if (!function_exists('katahdin_ai_hub')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die('Academy AI Assistant requires Katahdin AI Hub plugin to be active.');
        }
        
        // Create database tables
        $database = new AAA_Database();
        $database->create_tables();
        
        // Set default options (only if they don't exist)
        if (get_option('aaa_enable_for_all') === false) {
            add_option('aaa_enable_for_all', false);
        }
        if (get_option('aaa_test_user_ids') === false) {
            add_option('aaa_test_user_ids', '');
        }
        if (get_option('aaa_debug_enabled') === false) {
            add_option('aaa_debug_enabled', false);
        }
        if (get_option('aaa_ai_quota_limit') === false) {
            add_option('aaa_ai_quota_limit', 50000);
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
}

/**
 * Initialize the plugin
 */
function academy_ai_assistant_init() {
    return Academy_AI_Assistant::instance();
}

// Initialize the plugin
add_action('plugins_loaded', 'academy_ai_assistant_init', 10);

/**
 * Global function to access plugin instance
 */
function academy_ai_assistant() {
    return Academy_AI_Assistant::instance();
}

