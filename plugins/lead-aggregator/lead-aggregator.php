<?php
/**
 * Plugin Name: Lead Aggregator
 * Description: Aggregate and manage leads from multiple sources with shortcodes.
 * Version: 1.0.2
 * Author: Jazzedge
 * License: GPL v2 or later
 * Text Domain: lead-aggregator
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LEAD_AGGREGATOR_VERSION', '1.0.2');
define('LEAD_AGGREGATOR_PLUGIN_FILE', __FILE__);
define('LEAD_AGGREGATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LEAD_AGGREGATOR_PLUGIN_URL', plugin_dir_url(__FILE__));

class Lead_Aggregator_Plugin {
    private static $instance = null;

    public $database;
    public $rest;
    public $shortcodes;
    public $admin;
    public $notifications;
    public $permissions;
    public $billing;

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
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-database.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-permissions.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-billing.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-rest.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-admin.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-notifications.php';
    }

    private function init_components() {
        $this->database = new Lead_Aggregator_Database();
        $this->permissions = new Lead_Aggregator_Permissions($this->database);
        $this->billing = new Lead_Aggregator_Billing($this->database);
        $this->rest = new Lead_Aggregator_REST($this->database, $this->permissions, $this->billing);
        $this->shortcodes = new Lead_Aggregator_Shortcodes($this->database, $this->permissions, $this->billing);
        $this->admin = new Lead_Aggregator_Admin($this->database, $this->permissions);
        $this->notifications = new Lead_Aggregator_Notifications($this->database);
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_filter('theme_page_templates', array($this, 'register_page_template'));
        add_filter('template_include', array($this, 'load_page_template'));
        add_filter('body_class', array($this, 'add_body_class'));
        add_action('init', array($this, 'maybe_create_tables'));
        add_action('init', array($this->notifications, 'schedule_cron'));
        add_action('rest_api_init', array($this->database, 'maybe_create_tables'));
        add_action('wp_enqueue_scripts', array($this, 'maybe_dequeue_theme_styles'), 100);
        add_filter('show_admin_bar', array($this, 'maybe_hide_admin_bar'));
    }

    public function activate() {
        $this->database->create_tables();
        $this->notifications->schedule_cron();
        flush_rewrite_rules();
    }

    public function deactivate() {
        $this->notifications->clear_cron();
        flush_rewrite_rules();
    }

    public function maybe_create_tables() {
        $this->database->maybe_create_tables();
    }


    public function register_page_template($templates) {
        $templates['lead-aggregator-dashboard.php'] = 'Lead Aggregator Dashboard';
        return $templates;
    }

    public function load_page_template($template) {
        if (!is_singular('page')) {
            return $template;
        }

        $page_id = get_queried_object_id();
        if (!$page_id) {
            return $template;
        }

        $selected = get_page_template_slug($page_id);
        if ($selected !== 'lead-aggregator-dashboard.php') {
            return $template;
        }

        $custom_template = LEAD_AGGREGATOR_PLUGIN_DIR . 'templates/lead-aggregator-dashboard.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }

        return $template;
    }

    public function add_body_class($classes) {
        if (!is_singular('page')) {
            return $classes;
        }

        $page_id = get_queried_object_id();
        if (!$page_id) {
            return $classes;
        }

        $selected = get_page_template_slug($page_id);
        if ($selected === 'lead-aggregator-dashboard.php') {
            $classes[] = 'lead-aggregator-template';
        }

        return $classes;
    }

    public function maybe_dequeue_theme_styles() {
        if (!is_page_template('lead-aggregator-dashboard.php')) {
            return;
        }

        if (!get_option('lead_aggregator_app_mode', true)) {
            return;
        }

        global $wp_styles;
        if (!$wp_styles || empty($wp_styles->queue)) {
            return;
        }

        foreach ($wp_styles->queue as $handle) {
            if (in_array($handle, array('lead-aggregator-frontend', 'dashicons'), true)) {
                continue;
            }
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
    }

    public function maybe_hide_admin_bar($show) {
        if (is_page_template('lead-aggregator-dashboard.php') && get_option('lead_aggregator_app_mode', true)) {
            return false;
        }
        return $show;
    }
}

function lead_aggregator_init() {
    return Lead_Aggregator_Plugin::instance();
}

add_action('plugins_loaded', 'lead_aggregator_init');

function lead_aggregator() {
    return Lead_Aggregator_Plugin::instance();
}
