<?php
/**
 * Plugin Name: Jazzedge Marketing
 * Plugin URI:
 * Description: Email opt-in funnels with timed coupon discounts and conversion tracking
 * Version: 1.0.0
 * Author: Willie Myette
 * Text Domain: jazzedge-marketing
 *
 * @package Jazzedge_Marketing
 */

if (!defined('ABSPATH')) {
    exit;
}

define('JEM_VERSION', '1.0.0');
define('JEM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JEM_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class.
 */
class JEM_Plugin {

    private static $instance = null;
    public $database;
    public $admin;
    public $shortcodes;
    public $coupon;
    public $webhook;
    public $download;
    public $metrics;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_components();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once JEM_PLUGIN_DIR . 'includes/class-database.php';
        require_once JEM_PLUGIN_DIR . 'includes/class-coupon.php';
        require_once JEM_PLUGIN_DIR . 'includes/class-webhook.php';
        require_once JEM_PLUGIN_DIR . 'includes/class-download.php';
        require_once JEM_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once JEM_PLUGIN_DIR . 'includes/class-admin.php';
        require_once JEM_PLUGIN_DIR . 'includes/class-metrics.php';
    }

    private function init_components() {
        $this->database = new JEM_Database();
        $this->coupon = new JEM_Coupon();
        $this->webhook = new JEM_Webhook($this->database);
        $this->download = new JEM_Download($this->database);
        $this->shortcodes = new JEM_Shortcodes($this->database, $this->coupon, $this->webhook);
        $this->admin = new JEM_Admin($this->database);
        $this->metrics = new JEM_Metrics($this->database);
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('init', array($this, 'maybe_create_tables'));
        add_action('admin_notices', array($this, 'check_fluentcart_table'));
        add_action('template_redirect', array($this->download, 'handle'));
    }

    public function maybe_create_tables() {
        global $wpdb;
        $tables = array($wpdb->prefix . 'jem_funnels', $wpdb->prefix . 'jem_leads', $wpdb->prefix . 'jem_events');
        foreach ($tables as $table) {
            if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
                $this->database->create_tables();
                break;
            }
        }
    }

    public function activate() {
        $this->database->create_tables();
        flush_rewrite_rules();
    }

    /**
     * Warn in admin if FluentCart fct_coupons table is missing.
     */
    public function check_fluentcart_table() {
        if (!current_user_can('manage_options')) {
            return;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'fct_coupons';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            echo '<div class="notice notice-warning"><p><strong>Jazzedge Marketing:</strong> The FluentCart coupons table (<code>' . esc_html($table) . '</code>) was not found. Ensure FluentCart is active. Opt-ins will fail until this table exists.</p></div>';
        }
    }
}

function jem_plugin() {
    return JEM_Plugin::instance();
}

add_action('plugins_loaded', 'jem_plugin');
