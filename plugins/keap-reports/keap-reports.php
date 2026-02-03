<?php
/**
 * Plugin Name: Keap Reports
 * Plugin URI: https://jazzedge.com/
 * Description: Connect to Keap API, configure saved searches/reports, fetch data, and store monthly aggregated totals for comparison over time.
 * Version: 1.0.0
 * Author: JazzEdge
 * Author URI: https://jazzedge.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: keap-reports
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KEAP_REPORTS_VERSION', '1.0.0');
define('KEAP_REPORTS_PLUGIN_FILE', __FILE__);
define('KEAP_REPORTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KEAP_REPORTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KEAP_REPORTS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
$includes_path = KEAP_REPORTS_PLUGIN_DIR . 'includes/';

if (file_exists($includes_path . 'class-database.php')) {
    require_once $includes_path . 'class-database.php';
}

if (file_exists($includes_path . 'class-api.php')) {
    require_once $includes_path . 'class-api.php';
}

if (file_exists($includes_path . 'class-reports.php')) {
    require_once $includes_path . 'class-reports.php';
}

if (file_exists($includes_path . 'class-cron.php')) {
    require_once $includes_path . 'class-cron.php';
}

if (file_exists($includes_path . 'class-admin.php')) {
    require_once $includes_path . 'class-admin.php';
}

/**
 * Main plugin class
 */
class Keap_Reports {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Database handler
     */
    public $database;
    
    /**
     * API handler
     */
    public $api;
    
    /**
     * Reports handler
     */
    public $reports;
    
    /**
     * Cron handler
     */
    public $cron;
    
    /**
     * Admin interface
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
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize components
     */
            private function init_components() {
                $this->database = new Keap_Reports_Database();
                $this->api = new Keap_Reports_API();
                // Set database reference in API for logging
                $this->api->set_database($this->database);
                $this->reports = new Keap_Reports_Reports($this->database, $this->api);
                $this->cron = new Keap_Reports_Cron($this->reports);
                $this->admin = new Keap_Reports_Admin($this->api, $this->reports, $this->database, $this->cron);
            }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('keap-reports', false, dirname(KEAP_REPORTS_PLUGIN_BASENAME) . '/languages');
        
        // Ensure tables exist (in case activation didn't run)
        $this->maybe_create_tables();
    }
    
    /**
     * Activation hook
     */
    public function activate() {
        // Create database tables
        if (class_exists('Keap_Reports_Database')) {
            Keap_Reports_Database::create_tables();
        } else {
            // If class not loaded, require it
            require_once KEAP_REPORTS_PLUGIN_DIR . 'includes/class-database.php';
            Keap_Reports_Database::create_tables();
        }
        
        // Set default settings if not exists
        if (get_option('keap_reports_api_key') === false) {
            update_option('keap_reports_api_key', '');
        }
        
        // Enable auto-fetch by default if not set
        if (get_option('keap_reports_auto_fetch_enabled') === false) {
            update_option('keap_reports_auto_fetch_enabled', 1);
        }
        
        // Set default schedule frequency to daily if not set
        if (get_option('keap_reports_schedule_frequency') === false) {
            update_option('keap_reports_schedule_frequency', 'daily');
        }
        
        // Clear any cached schedules (will be recreated on next admin page load)
        wp_clear_scheduled_hook('keap_reports_fetch_scheduled');
        wp_clear_scheduled_hook('keap_reports_fetch_daily_subscriptions');
    }
    
    /**
     * Check and create tables if they don't exist
     */
    public function maybe_create_tables() {
        global $wpdb;
        
        $reports_table = $wpdb->prefix . 'keap_reports';
        $data_table = $wpdb->prefix . 'keap_report_data';
        
        // Check if tables exist
        $reports_exists = $wpdb->get_var("SHOW TABLES LIKE '$reports_table'") == $reports_table;
        $data_exists = $wpdb->get_var("SHOW TABLES LIKE '$data_table'") == $data_table;
        
        if (!$reports_exists || !$data_exists) {
            // Tables don't exist, create them
            if (class_exists('Keap_Reports_Database')) {
                Keap_Reports_Database::create_tables();
            } else {
                require_once KEAP_REPORTS_PLUGIN_DIR . 'includes/class-database.php';
                Keap_Reports_Database::create_tables();
            }
        }
    }
    
    /**
     * Deactivation hook
     */
    public function deactivate() {
        // Disable auto-fetch to stop any running processes
        update_option('keap_reports_auto_fetch_enabled', 0);
        
        // Clear scheduled cron jobs
        wp_clear_scheduled_hook('keap_reports_fetch_scheduled');
        wp_clear_scheduled_hook('keap_reports_fetch_daily_subscriptions');
    }
}

/**
 * Initialize the plugin
 */
function keap_reports() {
    return Keap_Reports::instance();
}

// Start the plugin
keap_reports();

