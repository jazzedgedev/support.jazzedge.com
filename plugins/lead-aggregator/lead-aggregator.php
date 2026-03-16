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
    public $fluentcart;
    public $audit;

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
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-audit.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-fluentcart.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-permissions.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-billing.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-rest.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-admin.php';
        require_once LEAD_AGGREGATOR_PLUGIN_DIR . 'includes/class-notifications.php';
    }

    private function init_components() {
        $this->database = new Lead_Aggregator_Database();
        $this->audit = new Lead_Aggregator_Audit($this->database);
        $this->fluentcart = new Lead_Aggregator_FluentCart($this->database);
        $this->permissions = new Lead_Aggregator_Permissions($this->database);
        $this->billing = new Lead_Aggregator_Billing($this->database);
        $this->rest = new Lead_Aggregator_REST($this->database, $this->permissions, $this->billing, $this->audit, $this->fluentcart);
        $this->shortcodes = new Lead_Aggregator_Shortcodes($this->database, $this->permissions, $this->billing);
        $this->admin = new Lead_Aggregator_Admin($this->database, $this->permissions);
        $this->notifications = new Lead_Aggregator_Notifications($this->database);
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_filter('cron_schedules', array($this, 'register_cron_schedules'));
        add_filter('theme_page_templates', array($this, 'register_page_template'));
        add_filter('template_include', array($this, 'load_page_template'));
        add_filter('body_class', array($this, 'add_body_class'));
        add_action('init', array($this, 'maybe_create_tables'));
        add_action('init', array($this->notifications, 'schedule_cron'));
        add_action('init', array($this, 'schedule_fluentcart_reconcile'));
        add_action('rest_api_init', array($this->database, 'maybe_create_tables'));
        add_action('wp_enqueue_scripts', array($this, 'maybe_dequeue_theme_styles'), 100);
        add_filter('show_admin_bar', array($this, 'maybe_hide_admin_bar'));
        add_action('lead_aggregator_fluentcart_reconcile', array($this, 'run_fluentcart_reconcile'));
    }

    public function activate() {
        $this->database->create_tables();
        $this->notifications->schedule_cron();
        $this->schedule_fluentcart_reconcile();
        flush_rewrite_rules();
    }

    public function deactivate() {
        $this->notifications->clear_cron();
        $this->clear_fluentcart_reconcile();
        flush_rewrite_rules();
    }

    public function maybe_create_tables() {
        $this->database->maybe_create_tables();
    }


    public function register_page_template($templates) {
        if (!is_array($templates)) {
            $templates = array();
        }
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
            $user_id = get_current_user_id();
            if ($user_id && get_user_meta($user_id, 'lead_aggregator_dark_mode', true)) {
                $classes[] = 'dark-mode';
            }
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

    public function register_cron_schedules($schedules) {
        if (!isset($schedules['lead_aggregator_half_hour'])) {
            $schedules['lead_aggregator_half_hour'] = array(
                'interval' => 1800,
                'display' => 'Every 30 Minutes',
            );
        }
        return $schedules;
    }

    public function schedule_fluentcart_reconcile() {
        if (!wp_next_scheduled('lead_aggregator_fluentcart_reconcile')) {
            wp_schedule_event(time() + 300, 'lead_aggregator_half_hour', 'lead_aggregator_fluentcart_reconcile');
        }
    }

    public function clear_fluentcart_reconcile() {
        $timestamp = wp_next_scheduled('lead_aggregator_fluentcart_reconcile');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'lead_aggregator_fluentcart_reconcile');
        }
    }

    /**
     * Force reconciliation now: expire plan cache for all users with a FluentCart plan,
     * then run the normal reconcile. Use this to sync access immediately after a cancellation
     * (e.g. when the webhook did not fire) without waiting for the cron.
     *
     * @param bool $collect_debug If true, returns array of debug info instead of running silently.
     * @return array|null Debug data when $collect_debug is true, else null.
     */
    public function run_fluentcart_reconcile_now($collect_debug = false) {
        if (!$this->fluentcart || !$this->fluentcart->is_active()) {
            if ($collect_debug) {
                $diag = $this->fluentcart ? $this->fluentcart->get_active_diagnostic() : array('message' => 'FluentCart module not loaded');
                return array('error' => 'FluentCart not active', 'diagnostic' => $diag);
            }
            return null;
        }
        $debug = array(
            'timestamp' => current_time('mysql'),
            'expired' => array(),
            'reconcile_batch' => array(),
            'processed' => array(),
        );

        $users = get_users(array(
            'fields' => array('ID'),
            'number' => 1000,
            'meta_query' => array(
                'relation' => 'OR',
                array('key' => 'lead_aggregator_plan_source', 'value' => 'fluentcart', 'compare' => '='),
                array('key' => 'lead_aggregator_plan_key', 'compare' => 'EXISTS'),
            ),
        ));
        foreach ($users as $user) {
            $u = get_user_by('id', $user->ID);
            $debug['expired'][] = array('user_id' => $user->ID, 'email' => $u ? $u->user_email : '');
            update_user_meta($user->ID, 'lead_aggregator_plan_cache_expires_at', 0);
        }

        $reconcile_debug = $collect_debug ? array() : null;
        $this->run_fluentcart_reconcile($reconcile_debug);

        if ($collect_debug && is_array($reconcile_debug)) {
            $debug['reconcile_batch'] = $reconcile_debug['batch'] ?? array();
            $debug['processed'] = $reconcile_debug['processed'] ?? array();
        }

        return $collect_debug ? $debug : null;
    }

    public function run_fluentcart_reconcile(&$debug = null) {
        if (!$this->fluentcart || !$this->fluentcart->is_active()) {
            return;
        }
        $now = time();
        $users = get_users(array(
            'fields' => array('ID'),
            'number' => 500,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'lead_aggregator_plan_cache_expires_at',
                    'value' => $now,
                    'compare' => '<',
                    'type' => 'NUMERIC',
                ),
                array(
                    'key' => 'lead_aggregator_plan_cache_expires_at',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        ));

        if (is_array($debug)) {
            $debug['batch'] = array();
            $debug['processed'] = array();
        }

        $workspace_ids = array();
        foreach ($users as $user) {
            $workspace_id = $this->audit ? $this->audit->resolve_workspace_id($user->ID) : $user->ID;
            if ($workspace_id) {
                $workspace_ids[$workspace_id] = true;
            }
            if (is_array($debug)) {
                $u = get_user_by('id', $user->ID);
                $debug['batch'][] = array('user_id' => $user->ID, 'workspace_id' => $workspace_id, 'email' => $u ? $u->user_email : '');
            }
        }

        $plans = $this->billing ? $this->billing->get_plans() : array();
        foreach (array_keys($workspace_ids) as $workspace_id) {
            $before_source = get_user_meta($workspace_id, 'lead_aggregator_plan_source', true);
            $before_key = get_user_meta($workspace_id, 'lead_aggregator_plan_key', true);
            $before_limit = (int) get_user_meta($workspace_id, 'lead_aggregator_lead_limit', true);
            $before_access = get_user_meta($workspace_id, 'lead_aggregator_access_enabled', true);

            if ($before_source === 'fluentcart') {
                $plan = lead_aggregator_get_user_plan($workspace_id);
                $this->fluentcart->set_cached_plan($workspace_id, $plan['plan_key'], $plan['lead_limit'], $plan['source']);
                $access_val = ($plan['plan_key'] && $plan['plan_key'] !== 'none') ? 1 : 0;
                update_user_meta($workspace_id, 'lead_aggregator_access_enabled', $access_val);
            } else {
                $plan_key = get_user_meta($workspace_id, 'lead_aggregator_plan_key', true);
                $limit = 0;
                if ($plan_key && $plans && isset($plans[$plan_key])) {
                    $limit = isset($plans[$plan_key]['lead_limit']) ? (int) $plans[$plan_key]['lead_limit'] : 0;
                }
                $this->fluentcart->set_cached_plan($workspace_id, $plan_key ?: 'none', $limit, 'legacy');
                $plan = array('plan_key' => $plan_key ?: 'none', 'lead_limit' => $limit, 'source' => 'legacy');
            }

            if (is_array($debug)) {
                $u = get_user_by('id', $workspace_id);
                $email = $u ? $u->user_email : '';
                $raw_subs = array();
                if ($email && $this->fluentcart) {
                    $raw = $this->fluentcart->get_user_subscriptions($email);
                    foreach ($raw as $s) {
                        $raw_subs[] = array(
                            'product_id' => isset($s['product_id']) ? $s['product_id'] : '',
                            'status' => isset($s['status']) ? $s['status'] : '',
                        );
                    }
                }
                $debug['processed'][] = array(
                    'workspace_id' => $workspace_id,
                    'email' => $email,
                    'plan_key_before' => $before_key,
                    'plan_key_after' => $plan['plan_key'],
                    'lead_limit_after' => $plan['lead_limit'],
                    'source' => $plan['source'],
                    'access_enabled_before' => $before_access,
                    'access_enabled_after' => $before_source === 'fluentcart' ? (string) get_user_meta($workspace_id, 'lead_aggregator_access_enabled', true) : '(not updated - manual/legacy)',
                    'raw_subscriptions_from_api' => $raw_subs,
                );
            }
        }
    }
}

function lead_aggregator_init() {
    return Lead_Aggregator_Plugin::instance();
}

add_action('plugins_loaded', 'lead_aggregator_init');

function lead_aggregator() {
    return Lead_Aggregator_Plugin::instance();
}

function lead_aggregator_get_user_plan($user_id) {
    $plugin = lead_aggregator();
    if (!$plugin || !$plugin->billing) {
        return array('plan_key' => 'none', 'lead_limit' => 0, 'source' => 'unknown');
    }
    $plans = $plugin->billing->get_plans();
    $fluentcart = $plugin->fluentcart;
    if ($fluentcart) {
        $cached = $fluentcart->get_cached_plan($user_id);
        if ($cached) {
            return $cached;
        }
        if ($fluentcart->is_active()) {
            $resolved = $fluentcart->resolve_plan_for_user($user_id, $plans);
            $fluentcart->set_cached_plan($user_id, $resolved['plan_key'], $resolved['lead_limit'], $resolved['source']);
            return $resolved;
        }
    }
    $plan_key = get_user_meta($user_id, 'lead_aggregator_plan_key', true);
    if ($plan_key && isset($plans[$plan_key])) {
        $limit = isset($plans[$plan_key]['lead_limit']) ? (int) $plans[$plan_key]['lead_limit'] : 0;
        return array('plan_key' => $plan_key, 'lead_limit' => $limit, 'source' => 'legacy');
    }
    return array('plan_key' => 'none', 'lead_limit' => 0, 'source' => 'legacy');
}

function lead_aggregator_get_entitlements($user_id) {
    $plugin = lead_aggregator();
    $database = $plugin ? $plugin->database : null;
    $workspace_id = $user_id;
    if ($plugin && $plugin->audit) {
        $workspace_id = $plugin->audit->resolve_workspace_id($user_id);
    }
    $plan = lead_aggregator_get_user_plan($workspace_id);
    $lead_limit = isset($plan['lead_limit']) ? (int) $plan['lead_limit'] : 0;
    $current = $database ? (int) $database->count_leads($workspace_id) : 0;
    $is_over = $lead_limit > 0 && $current >= $lead_limit;
    return array(
        'plan_key' => $plan['plan_key'],
        'lead_limit' => $lead_limit,
        'current_lead_count' => $current,
        'is_over_limit' => $is_over,
    );
}
