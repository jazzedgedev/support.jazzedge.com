<?php
/**
 * Plugin Name: Keap To Fluent Tagging
 * Plugin URI: https://jazzedge.com/
 * Description: HTTP POST endpoint to tag FluentCRM contacts from Keap. Receives email, tag_id, and authentication code.
 * Version: 1.0.0
 * Author: JazzEdge
 * Author URI: https://jazzedge.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: keap-to-fluent-tagging
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
define('KTF_VERSION', '1.0.0');
define('KTF_PLUGIN_FILE', __FILE__);
define('KTF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KTF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KTF_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
$includes_path = KTF_PLUGIN_DIR . 'includes/';

if (file_exists($includes_path . 'class-rest-api.php')) {
    require_once $includes_path . 'class-rest-api.php';
}

if (file_exists($includes_path . 'class-admin.php')) {
    require_once $includes_path . 'class-admin.php';
}

/**
 * Main plugin class
 */
class Keap_To_Fluent_Tagging {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * REST API handler
     */
    public $rest_api;
    
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
        $this->rest_api = new KTF_REST_API();
        $this->admin = new KTF_Admin();
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check if FluentCRM is active
        if (!function_exists('FluentCrmApi')) {
            add_action('admin_notices', array($this, 'fluentcrm_missing_notice'));
        }
    }
    
    /**
     * Show notice if FluentCRM is not active
     */
    public function fluentcrm_missing_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="notice notice-error">
            <p><strong>Keap To Fluent Tagging</strong> requires <strong>FluentCRM</strong> to be installed and activated.</p>
        </div>
        <?php
    }
    
    /**
     * Activation hook
     */
    public function activate() {
        // Set default settings if not exists
        if (get_option('ktf_auth_code') === false) {
            update_option('ktf_auth_code', '');
        }
        if (get_option('ktf_debug_enabled') === false) {
            update_option('ktf_debug_enabled', false);
        }
    }
    
    /**
     * Deactivation hook
     */
    public function deactivate() {
        // Clean up if needed
    }
}

/**
 * Initialize the plugin
 */
function keap_to_fluent_tagging() {
    return Keap_To_Fluent_Tagging::instance();
}

// Start the plugin
keap_to_fluent_tagging();

