<?php
/**
 * Plugin Name: Jazzedge Docs
 * Plugin URI: https://jazzedge.com
 * Description: A comprehensive documentation and knowledge base system for Jazzedge Academy support articles
 * Version: 1.0.0
 * Author: JazzEdge
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JAZZEDGE_DOCS_VERSION', '1.0.7');
define('JAZZEDGE_DOCS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JAZZEDGE_DOCS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JAZZEDGE_DOCS_PLUGIN_FILE', __FILE__);

// Include required files
require_once JAZZEDGE_DOCS_PLUGIN_DIR . 'includes/class-database.php';
require_once JAZZEDGE_DOCS_PLUGIN_DIR . 'includes/class-post-type.php';
require_once JAZZEDGE_DOCS_PLUGIN_DIR . 'includes/class-taxonomy.php';
require_once JAZZEDGE_DOCS_PLUGIN_DIR . 'includes/class-admin.php';
require_once JAZZEDGE_DOCS_PLUGIN_DIR . 'includes/class-frontend.php';
require_once JAZZEDGE_DOCS_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once JAZZEDGE_DOCS_PLUGIN_DIR . 'includes/class-search.php';
require_once JAZZEDGE_DOCS_PLUGIN_DIR . 'includes/class-rest-api.php';
require_once JAZZEDGE_DOCS_PLUGIN_DIR . 'includes/class-helpers.php';

/**
 * Main Jazzedge Docs Plugin Class
 */
class Jazzedge_Docs {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Database instance
     */
    public $db;
    
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
        // Initialize database
        $this->db = new Jazzedge_Docs_Database();
        
        // Initialize post type
        $post_type = new Jazzedge_Docs_Post_Type();
        
        // Initialize taxonomy
        $taxonomy = new Jazzedge_Docs_Taxonomy();
        
        // Initialize admin
        $admin = new Jazzedge_Docs_Admin();
        
        // Initialize frontend
        $frontend = new Jazzedge_Docs_Frontend();
        
        // Initialize shortcodes
        $shortcodes = new Jazzedge_Docs_Shortcodes();
        
        // Initialize search
        $search = new Jazzedge_Docs_Search();
        
        // Initialize REST API
        $rest_api = new Jazzedge_Docs_REST_API();
        
        // Register activation hook
        register_activation_hook(JAZZEDGE_DOCS_PLUGIN_FILE, array($this, 'activate'));
        
        // Register deactivation hook
        register_deactivation_hook(JAZZEDGE_DOCS_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Flush rewrite rules when permalink structure changes
        add_action('admin_init', array($this, 'maybe_flush_rewrite_rules'));
        
        // Flush rewrite rules after post type registration
        add_action('init', array($this, 'check_rewrite_rules'), 999);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->db = new Jazzedge_Docs_Database();
        $this->db->create_tables();
        
        // Register post type and taxonomy
        $post_type = new Jazzedge_Docs_Post_Type();
        $post_type->register_post_type();
        
        $taxonomy = new Jazzedge_Docs_Taxonomy();
        $taxonomy->register_taxonomy();
        
        // Force flush rewrite rules
        flush_rewrite_rules(true);
        
        // Set flag to flush again on next admin load
        update_option('jazzedge_docs_flush_rewrite_rules', true);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Maybe flush rewrite rules
     */
    public function maybe_flush_rewrite_rules() {
        if (get_option('jazzedge_docs_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('jazzedge_docs_flush_rewrite_rules');
        }
    }
    
    /**
     * Check and flush rewrite rules if needed
     */
    public function check_rewrite_rules() {
        // Only check in admin to avoid performance issues
        if (!is_admin() && !defined('WP_CLI')) {
            return;
        }
        
        $rules = get_option('rewrite_rules');
        $needs_flush = false;
        
        // Check if our rewrite rules exist (check multiple possible patterns)
        $has_rules = false;
        if ($rules && is_array($rules)) {
            foreach ($rules as $pattern => $rule) {
                if (strpos($pattern, 'docs') !== false || strpos($rule, 'jazzedge_doc') !== false) {
                    $has_rules = true;
                    break;
                }
            }
        }
        
        if (!$has_rules) {
            $needs_flush = true;
        }
        
        // Set flag to flush if needed
        if ($needs_flush) {
            update_option('jazzedge_docs_flush_rewrite_rules', true);
        }
    }
}

// Initialize the plugin
Jazzedge_Docs::get_instance();

