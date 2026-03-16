<?php
/**
 * Jazzedge Marketing - Shortcodes & AJAX
 *
 * @package Jazzedge_Marketing
 */

if (!defined('ABSPATH')) {
    exit;
}

class JEM_Shortcodes {

    private $database;
    private $coupon;
    private $webhook;

    public function __construct($database, $coupon, $webhook) {
        $this->database = $database;
        $this->coupon = $coupon;
        $this->webhook = $webhook;

        add_shortcode('jem_marketing', array($this, 'render_marketing'));
        add_shortcode('jem_thank_you', array($this, 'render_thank_you'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_css_global'), 5);
        add_action('wp_ajax_jem_optin', array($this, 'ajax_optin'));
        add_action('wp_ajax_nopriv_jem_optin', array($this, 'ajax_optin'));
        add_action('wp_ajax_jem_track_purchase_click', array($this, 'ajax_track_purchase_click'));
        add_action('wp_ajax_nopriv_jem_track_purchase_click', array($this, 'ajax_track_purchase_click'));
        add_action('wp_ajax_jem_track_event', array($this, 'ajax_track_event'));
        add_action('wp_ajax_nopriv_jem_track_event', array($this, 'ajax_track_event'));
    }

    /**
     * Enqueue frontend CSS globally so it loads even when has_shortcode misses
     * (e.g. block editor, dynamic content). Ensures form styling always applies.
     */
    public function enqueue_frontend_css_global() {
        if (is_admin()) {
            return;
        }
        $css_url = JEM_PLUGIN_URL . 'assets/css/frontend.css';
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('JEM CSS URL: ' . $css_url);
        }
        wp_enqueue_style(
            'jem-frontend',
            $css_url,
            array(),
            JEM_VERSION
        );
    }

    public function enqueue_frontend_assets() {
        if (is_admin()) {
            return;
        }
        wp_enqueue_script(
            'jem-frontend',
            JEM_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            JEM_VERSION,
            true
        );
        wp_localize_script('jem-frontend', 'jemAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jem_nonce'),
        ));

    }

    public function render_marketing($atts) {
        $invite_code = isset($_GET['invite_code']) ? sanitize_text_field(wp_unslash($_GET['invite_code'])) : '';
        if (empty($invite_code)) {
            return '<p class="jem-inactive">' . esc_html__('This offer is not currently available.', 'jazzedge-marketing') . '</p>';
        }

        $funnel = $this->database->get_funnel_by_invite_code($invite_code);
        if (!$funnel) {
            return '<p class="jem-inactive">' . esc_html__('This offer is not currently available.', 'jazzedge-marketing') . '</p>';
        }
        if (!(int) $funnel->active) {
            $msg = get_option('jem_inactive_msg', '');
            if (empty(trim($msg))) {
                $msg = __('This offer is not currently available.', 'jazzedge-marketing');
            }
            return '<p class="jem-inactive">' . esc_html($msg) . '</p>';
        }

        ob_start();
        include JEM_PLUGIN_DIR . 'templates/optin-form.php';
        return ob_get_clean();
    }

    public function render_thank_you($atts) {
        $token = isset($_GET['jem_lead']) ? sanitize_text_field(wp_unslash($_GET['jem_lead'])) : '';
        if (empty($token)) {
            return '<p class="jem-error">' . esc_html__('Invalid or expired link.', 'jazzedge-marketing') . '</p>';
        }

        $lead = $this->database->get_lead_by_token($token);
        if (!$lead) {
            return '<p class="jem-error">' . esc_html__('Invalid or expired link.', 'jazzedge-marketing') . '</p>';
        }

        $funnel = $this->database->get_funnel($lead->funnel_id);
        if (!$funnel) {
            return '<p class="jem-error">' . esc_html__('Invalid or expired link.', 'jazzedge-marketing') . '</p>';
        }

        $now = current_time('mysql');
        $expired = !empty($lead->coupon_expires) && $lead->coupon_expires < $now;
        $product_url = add_query_arg('coupon', $lead->coupon_code, $funnel->product_url);

        // Look up pricing from FluentCart product variations (prices in cents)
        global $wpdb;
        $variation = null;
        if ( ! empty( $funnel->product_id ) ) {
            $variation = $wpdb->get_row( $wpdb->prepare(
                "SELECT item_price, compare_price, variation_title 
                 FROM {$wpdb->prefix}fct_product_variations 
                 WHERE post_id = %d 
                 LIMIT 1",
                absint( $funnel->product_id )
            ) );
        }
        $regular_price   = $variation ? (float) $variation->compare_price / 100 : 0;
        $sale_price      = $variation ? (float) $variation->item_price / 100 : 0;
        $discount_amount = $sale_price * ( (float) $funnel->discount_pct / 100 );
        $your_price      = $sale_price - $discount_amount;
        $total_savings   = $regular_price - $your_price;
        $savings_pct     = $regular_price > 0 ? (int) round( ( $total_savings / $regular_price ) * 100 ) : (int) $funnel->discount_pct;
        $product_title   = $variation ? $variation->variation_title : $funnel->name;

        ob_start();
        include JEM_PLUGIN_DIR . 'templates/thank-you.php';
        return ob_get_clean();
    }

    public function ajax_optin() {
        check_ajax_referer('jem_nonce', 'nonce');

        $invite_code = isset($_POST['invite_code']) ? sanitize_text_field(wp_unslash($_POST['invite_code'])) : '';
        $first_name = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $honeypot = isset($_POST['jem_hp']) ? sanitize_text_field(wp_unslash($_POST['jem_hp'])) : '';

        if (!empty($honeypot)) {
            wp_send_json_success(array('redirect' => add_query_arg('jem_lead', 'ok', wp_get_referer() ?: home_url())));
        }

        if (empty($invite_code)) {
            wp_send_json_error(array('message' => __('Invalid request.', 'jazzedge-marketing')));
        }
        if (empty($first_name) || empty($last_name) || empty($email)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'jazzedge-marketing')));
        }
        if (!is_email($email)) {
            wp_send_json_error(array('message' => __('Please enter a valid email address.', 'jazzedge-marketing')));
        }

        $funnel = $this->database->get_funnel_by_invite_code($invite_code);
        if (!$funnel) {
            wp_send_json_error(array('message' => __('This offer is no longer available.', 'jazzedge-marketing')));
        }

        $existing = $this->database->get_lead_by_email_funnel($email, $funnel->id);
        if ($existing) {
            wp_send_json_error(array('message' => __("You've already signed up for this offer. Check your email for your coupon code.", 'jazzedge-marketing')));
        }

        $prefix = strtoupper($funnel->coupon_prefix);
        $coupon_code = strtoupper($prefix . '-' . wp_generate_password(8, false));
        $coupon_code = preg_replace('/[^A-Z0-9\-]/', '', $coupon_code);

        $coupon_days = max(1, (int) $funnel->coupon_days);
        $coupon_expires = gmdate('Y-m-d H:i:s', strtotime('+' . $coupon_days . ' days'));
        $download_token = bin2hex(random_bytes(32));

        $lead_data = array(
            'funnel_id' => $funnel->id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'coupon_code' => $coupon_code,
            'coupon_expires' => $coupon_expires,
            'download_token' => $download_token,
        );

        $lead_id = $this->database->insert_lead($lead_data);
        if (!$lead_id) {
            wp_send_json_error(array('message' => __('Something went wrong. Please try again.', 'jazzedge-marketing')));
        }

        $coupon_insert = $this->coupon->insert($funnel, $coupon_code, $coupon_expires, $email);
        if (!$coupon_insert) {
            $this->database->delete_lead($lead_id);
            wp_send_json_error(array('message' => __('Something went wrong creating your access. Please contact support.', 'jazzedge-marketing')));
        }

        $lead = (object) array_merge($lead_data, array('id' => $lead_id));
        $webhook_ok = $this->webhook->send($funnel, $lead);
        if (!$webhook_ok) {
            $this->coupon->delete_by_code($coupon_code);
            $this->database->delete_lead($lead_id);
            wp_send_json_error(array('message' => __('We had trouble processing your request. Please try again in a moment.', 'jazzedge-marketing')));
        }

        $this->database->log_event($lead_id, $funnel->id, 'opt_in');
        $this->database->update_lead($lead_id, array('webhook_sent' => 1));

        $thankyou_page_id = get_option( 'jem_thankyou_page_id', 0 );
        $thankyou_url     = $thankyou_page_id ? get_permalink( $thankyou_page_id ) : home_url( '/' );
        $redirect         = add_query_arg( 'jem_lead', $download_token, $thankyou_url );
        wp_send_json_success( array( 'redirect' => $redirect ) );
    }

    public function ajax_track_purchase_click() {
        check_ajax_referer('jem_nonce', 'nonce');
        $lead_id = isset($_POST['lead_id']) ? (int) $_POST['lead_id'] : 0;
        $funnel_id = isset($_POST['funnel_id']) ? (int) $_POST['funnel_id'] : 0;
        if ($lead_id > 0 && $funnel_id > 0) {
            $this->database->log_event($lead_id, $funnel_id, 'purchase_click');
        }
        wp_send_json_success();
    }

    /**
     * Unified event tracking (download_click, purchase_click, etc.).
     */
    public function ajax_track_event() {
        check_ajax_referer('jem_nonce', 'nonce');
        $lead_id = isset($_POST['lead_id']) ? (int) $_POST['lead_id'] : 0;
        $funnel_id = isset($_POST['funnel_id']) ? (int) $_POST['funnel_id'] : 0;
        $event = isset($_POST['event']) ? sanitize_key($_POST['event']) : '';
        if ($lead_id > 0 && $funnel_id > 0 && in_array($event, array('download_click', 'purchase_click'), true)) {
            $this->database->log_event($lead_id, $funnel_id, $event);
        }
        wp_send_json_success();
    }
}
