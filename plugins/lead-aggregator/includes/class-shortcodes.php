<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Aggregator_Shortcodes {
    private $database;
    private $permissions;
    private $billing;
    private $assets_enqueued = false;

    public function __construct($database, $permissions, $billing) {
        $this->database = $database;
        $this->permissions = $permissions;
        $this->billing = $billing;

        add_shortcode('lead_aggregator_inbox', array($this, 'render_inbox'));
        add_shortcode('lead_aggregator_lead_form', array($this, 'render_lead_form'));
        add_shortcode('lead_aggregator_lead_detail', array($this, 'render_lead_detail'));
        add_shortcode('lead_aggregator_calendar', array($this, 'render_calendar'));
        add_shortcode('lead_aggregator_manage_tags', array($this, 'render_manage_tags'));
        add_shortcode('lead_aggregator_export', array($this, 'render_export'));
        add_shortcode('lead_aggregator_business_profile', array($this, 'render_business_profile'));
        add_shortcode('lead_aggregator_login', array($this, 'render_login'));
        add_shortcode('lead_aggregator_dashboard', array($this, 'render_dashboard'));
        add_shortcode('lead_aggregator_register', array($this, 'render_register'));
        add_shortcode('lead_aggregator_pricing', array($this, 'render_pricing'));
        add_shortcode('lead_aggregator_billing', array($this, 'render_billing'));
    }

    private function enqueue_assets() {
        if ($this->assets_enqueued) {
            return;
        }

        $css_path = LEAD_AGGREGATOR_PLUGIN_DIR . 'assets/css/frontend.css';
        $js_path = LEAD_AGGREGATOR_PLUGIN_DIR . 'assets/js/frontend-v2.js';

        wp_enqueue_script(
            'lead-aggregator-chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            array(),
            '4.4.1',
            true
        );

        wp_enqueue_script(
            'sortablejs',
            'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js',
            array(),
            '1.15.0',
            true
        );

        wp_enqueue_style(
            'lead-aggregator-frontend',
            LEAD_AGGREGATOR_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            file_exists($css_path) ? filemtime($css_path) . '-forced' : LEAD_AGGREGATOR_VERSION . '-forced'
        );

        wp_enqueue_script(
            'lead-aggregator-frontend',
            LEAD_AGGREGATOR_PLUGIN_URL . 'assets/js/frontend-v2.js',
            array('jquery', 'lead-aggregator-chartjs', 'sortablejs'),
            file_exists($js_path) ? filemtime($js_path) . '-forced' : LEAD_AGGREGATOR_VERSION . '-forced',
            true
        );

        wp_add_inline_script(
            'lead-aggregator-frontend',
            'window.leadAggregatorVersion="force-' . esc_js(LEAD_AGGREGATOR_VERSION) . '-' . esc_js(file_exists($js_path) ? filemtime($js_path) : time()) . '";' .
            'document.documentElement.setAttribute("data-lead-aggregator-version", window.leadAggregatorVersion);' .
            'console.log("Lead Aggregator JS loaded", window.leadAggregatorVersion);'
        );

        wp_localize_script('lead-aggregator-frontend', 'leadAggregator', array(
            'restUrl' => esc_url_raw(rest_url('lead-aggregator/v1/')),
            'nonce' => wp_create_nonce('wp_rest'),
        ));

        $this->assets_enqueued = true;
    }

    private function ensure_access() {
        if (!$this->permissions->require_user()) {
            $error = $this->permissions->get_last_error();
            $message = isset($error['message']) ? $error['message'] : 'Please log in to access lead tools.';
            return '<p>' . esc_html($message) . '</p>';
        }
        $this->enqueue_assets();
        return '';
    }

    private function ensure_login_only() {
        if (!$this->permissions->require_login()) {
            $error = $this->permissions->get_last_error();
            $message = isset($error['message']) ? $error['message'] : 'Please log in to continue.';
            return '<p>' . esc_html($message) . '</p>';
        }
        $this->enqueue_assets();
        return '';
    }

    public function render_inbox() {
        $notice = $this->ensure_access();
        if ($notice) {
            return $notice;
        }

        return '<div class="lead-aggregator-view" data-view="inbox"></div>';
    }

    public function render_lead_form() {
        $notice = $this->ensure_access();
        if ($notice) {
            return $notice;
        }

        return '<div class="lead-aggregator-view" data-view="lead-form"></div>';
    }

    public function render_lead_detail($atts) {
        $notice = $this->ensure_access();
        if ($notice) {
            return $notice;
        }

        $atts = shortcode_atts(array('lead_id' => 0), $atts);
        $lead_id = (int) $atts['lead_id'];
        if (!$lead_id && isset($_GET['lead_id'])) {
            $lead_id = (int) $_GET['lead_id'];
        }

        return '<div class="lead-aggregator-view" data-view="lead-detail" data-lead-id="' . esc_attr($lead_id) . '"></div>';
    }

    public function render_calendar() {
        $notice = $this->ensure_access();
        if ($notice) {
            return $notice;
        }

        return '<div class="lead-aggregator-view" data-view="calendar"></div>';
    }

    public function render_manage_tags() {
        $notice = $this->ensure_access();
        if ($notice) {
            return $notice;
        }

        return '<div class="lead-aggregator-view" data-view="tags"></div>';
    }

    public function render_export() {
        $notice = $this->ensure_access();
        if ($notice) {
            return $notice;
        }

        return '<div class="lead-aggregator-view" data-view="export"></div>';
    }

    public function render_business_profile() {
        $notice = $this->ensure_access();
        if ($notice) {
            return $notice;
        }

        return '<div class="lead-aggregator-view" data-view="business-profile"></div>';
    }

    public function render_login() {
        if (is_user_logged_in()) {
            return '<p>You are already logged in.</p>';
        }

        $args = array(
            'echo' => false,
            'redirect' => esc_url_raw(add_query_arg(array())),
            'form_id' => 'lead-aggregator-loginform',
            'label_username' => 'Email or Username',
            'label_password' => 'Password',
            'label_remember' => 'Remember Me',
            'label_log_in' => 'Log In',
        );

        return wp_login_form($args);
    }

    public function render_dashboard() {
        $notice = $this->ensure_access();
        if ($notice) {
            return $notice;
        }

        $show_get_started = (int) get_user_meta(get_current_user_id(), 'lead_aggregator_show_get_started', true);
        if ($show_get_started === 0) {
            $show_get_started = 0;
        } else {
            $show_get_started = 1;
        }
        $manager_id = (int) get_user_meta(get_current_user_id(), 'lead_aggregator_manager_id', true);
        $is_manager = $manager_id === 0;
        if (user_can(get_current_user_id(), 'manage_options')) {
            $is_manager = true;
        }
        $tabs = array(
            'get-started' => 'Get Started',
            'overview' => 'Leads',
            'leads' => 'Leads',
            'followups' => 'Follow-ups',
            'won-leads' => 'Won Leads',
            'lost-leads' => 'Lost Leads',
            'calendar' => 'Calendar',
            'ai-tools' => 'AI Tools',
        );
        if ($is_manager) {
            $tabs['activity'] = 'Activity';
        }
        $tabs['settings'] = 'Settings';

        $html = '<div class="mml-app"><div class="lead-aggregator-view lead-aggregator-dashboard" data-view="dashboard" data-get-started="' . ($show_get_started ? '1' : '0') . '">';
        $html .= '<div class="la-dashboard">';
        $html .= '<div class="la-dashboard-header">';
        $html .= '<div><h2>Lead Dashboard</h2><p>Manage leads, follow-ups, and pipeline stages.</p></div>';
        $html .= '</div>';
        $html .= '<div class="la-tabs" role="tablist">';
        $first_tab = $show_get_started ? 'get-started' : 'overview';
        foreach ($tabs as $key => $label) {
            $is_active = $key === $first_tab ? ' is-active' : '';
            $html .= '<button type="button" class="la-tab' . $is_active . '" data-tab="' . esc_attr($key) . '" role="tab">' . esc_html($label) . '</button>';
        }
        $html .= '</div>';
        $html .= '<div class="la-panel la-tab-panel' . ($first_tab === 'get-started' ? ' is-active' : '') . '" data-tab="get-started" id="la-panel-get-started"></div>';
        $html .= '<div class="la-panel la-tab-panel' . ($first_tab === 'overview' ? ' is-active' : '') . '" data-tab="overview">';
        $html .= '<div class="la-dashboard-stats">';
        $html .= '<button type="button" class="la-stat la-stat-button" data-filter="total" aria-pressed="false"><span class="la-stat-label">Total Leads</span><span class="la-stat-value" data-stat="total">0</span></button>';
        $html .= '<button type="button" class="la-stat la-stat-button" data-filter="followup" aria-pressed="false"><span class="la-stat-label">Followups Due</span><span class="la-stat-value" data-stat="followup">0</span></button>';
        $html .= '<button type="button" class="la-stat la-stat-button" data-filter="followed" aria-pressed="false"><span class="la-stat-label">Followed Up Today</span><span class="la-stat-value" data-stat="followed">0</span></button>';
        $html .= '<button type="button" class="la-stat la-stat-button" data-filter="overdue" aria-pressed="false"><span class="la-stat-label">Overdue</span><span class="la-stat-value" data-stat="overdue">0</span></button>';
        $html .= '</div>';
        $html .= '<div class="la-dashboard-overview">';
        $html .= '<section class="la-panel la-panel--inbox" id="la-panel-inbox"></section>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="la-panel la-tab-panel" data-tab="leads" id="la-panel-leads"></div>';
        $html .= '<div class="la-panel la-tab-panel" data-tab="followups" id="la-panel-followups"></div>';
        $html .= '<div class="la-panel la-tab-panel" data-tab="won-leads" id="la-panel-won-leads"></div>';
        $html .= '<div class="la-panel la-tab-panel" data-tab="lost-leads" id="la-panel-lost-leads"></div>';
        $html .= '<div class="la-panel la-tab-panel" data-tab="calendar" id="la-panel-calendar"></div>';
        $html .= '<div class="la-panel la-tab-panel" data-tab="ai-tools" id="la-panel-ai-tools"></div>';
        if ($is_manager) {
            $html .= '<div class="la-panel la-tab-panel" data-tab="activity" id="la-panel-activity"></div>';
        }
        $html .= '<div class="la-panel la-tab-panel" data-tab="notes-tags" id="la-panel-notes-tags"></div>';
        $html .= '<div class="la-panel la-tab-panel" data-tab="settings" id="la-panel-settings">';
        $html .= '<div class="la-settings-grid">';
        $html .= '<section class="la-settings-card" id="la-settings-get-started"></section>';
        $html .= '<section class="la-settings-card" id="la-settings-appearance"></section>';
        $html .= '<section class="la-settings-card" id="la-settings-tags"></section>';
        $html .= '<section class="la-settings-card" id="la-settings-team"></section>';
        $html .= '<section class="la-settings-card" id="la-settings-notifications"></section>';
        $html .= '<section class="la-settings-card" id="la-settings-pipeline-stages"></section>';
        $html .= '<section class="la-settings-card" id="la-settings-quick-action"></section>';
        $html .= '<section class="la-settings-card" id="la-settings-webhooks"></section>';
        $html .= '<section class="la-settings-card" id="la-settings-billing"></section>';
        $html .= '<section class="la-settings-card" id="la-settings-business"></section>';
        $html .= '<section class="la-settings-card" id="la-settings-custom-fields"></section>';
        $html .= '<section class="la-settings-card" id="la-settings-export"></section>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div></div></div>';

        return $html;
    }

    public function render_register($atts) {
        if (is_user_logged_in()) {
            return '<p>Your account is active. You can proceed to the dashboard.</p>';
        }

        $atts = shortcode_atts(array(
            'redirect' => '',
        ), $atts);

        $this->enqueue_assets();
        return '<div class="lead-aggregator-view" data-view="pricing"></div>';
    }

    public function render_pricing() {
        $this->enqueue_assets();
        return '<div class="lead-aggregator-view" data-view="pricing"></div>';
    }

    public function render_billing() {
        $notice = $this->ensure_login_only();
        if ($notice) {
            return $notice;
        }

        return '<div class="lead-aggregator-view" data-view="billing"></div>';
    }
}
