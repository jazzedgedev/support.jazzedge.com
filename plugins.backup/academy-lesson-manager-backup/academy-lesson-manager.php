<?php
/**
 * Plugin Name: Academy Lesson Manager
 * Description: Simple lesson migration and management for JazzEdge Academy. Migrates academy tables to lesson posts with ACF fields.
 * Version: 2.2.2
 * Author: JazzEdge
 * Text Domain: academy-lesson-manager
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ALM_VERSION', '2.2.2');
define('ALM_PLUGIN_FILE', __FILE__);
define('ALM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ALM_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load required files
require_once __DIR__ . '/includes/class-migration.php';
require_once __DIR__ . '/includes/class-admin.php';
require_once __DIR__ . '/includes/class-frontend.php';

/**
 * Main Academy Lesson Manager Plugin Class
 */
class Academy_Lesson_Manager {
    
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('academy-lesson-manager', false, dirname(ALM_PLUGIN_BASENAME) . '/languages');
        
        // Initialize components
        $this->init_components();
        
        // Add hooks
        $this->add_hooks();
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize admin interface
        if (is_admin()) {
            new ALM_Admin();
        }
        
        // Initialize frontend
        new ALM_Frontend();
    }
    
    /**
     * Add WordPress hooks
     */
    private function add_hooks() {
        add_action('init', array($this, 'check_requirements'));
    }
    
    /**
     * Check plugin requirements
     */
    public function check_requirements() {
        // Check if lesson post type exists
        if (!post_type_exists('lesson')) {
            add_action('admin_notices', array($this, 'missing_post_type_notice'));
        }
        
        // Check if ACF is active
        if (!class_exists('ACF')) {
            add_action('admin_notices', array($this, 'missing_acf_notice'));
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create user progress table
        $this->create_tables();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ja_user_progress';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            lesson_id bigint(20) NOT NULL,
            progress_percent int(3) DEFAULT 0,
            completed tinyint(1) DEFAULT 0,
            last_accessed datetime DEFAULT CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_lesson (user_id, lesson_id),
            KEY user_id (user_id),
            KEY lesson_id (lesson_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Admin notice for missing post type
     */
    public function missing_post_type_notice() {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong>Academy Lesson Manager:</strong> 
                The <code>lesson</code> post type is required. Please ensure it exists via MetaBox > Post Types.
            </p>
        </div>
        <?php
    }
    
    /**
     * Admin notice for missing ACF
     */
    public function missing_acf_notice() {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong>Academy Lesson Manager:</strong> 
                Advanced Custom Fields (ACF) plugin is required for lesson field management.
            </p>
        </div>
        <?php
    }
}

// Initialize the plugin
Academy_Lesson_Manager::get_instance();

/**
 * Helper function to get plugin instance
 */
function alm() {
    return Academy_Lesson_Manager::get_instance();
}
