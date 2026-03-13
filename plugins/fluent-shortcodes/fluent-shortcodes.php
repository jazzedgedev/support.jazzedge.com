<?php
/**
 * Plugin Name:       FC Shortcodes
 * Plugin URI:        https://katahdin.ai
 * Description:       Professional FluentCart product shortcodes for any theme or builder.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Katahdin AI
 * Author URI:        https://katahdin.ai
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fc-shortcodes
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FC_SC_VERSION', '1.0.0');
define('FC_SC_FILE', __FILE__);
define('FC_SC_DIR', plugin_dir_path(__FILE__));
define('FC_SC_URL', plugin_dir_url(__FILE__));
define('FC_SC_BASENAME', plugin_basename(__FILE__));
define('FC_SC_AUTHOR', 'Katahdin AI');
define('FC_SC_AUTHOR_URL', 'https://katahdin.ai');

/**
 * Rate limiting for AJAX endpoints
 */
function fluent_sc_check_rate_limit($action) {
    $user_id = get_current_user_id();
    $key     = 'fc_sc_rate_' . sanitize_key($action) . '_' . $user_id;
    $count   = (int) get_transient($key);

    if ($count >= 60) {
        wp_send_json_error(array('message' => __('Too many requests. Please wait.', 'fc-shortcodes')), 429);
        wp_die();
    }

    set_transient($key, $count + 1, 60);
}

/**
 * Check for DB errors after write operations
 */
function fluent_sc_db_error_check($context = '') {
    global $wpdb;
    if ($wpdb->last_error) {
        error_log('FC Shortcodes DB error [' . $context . ']: ' . $wpdb->last_error);
        wp_send_json_error(array('message' => __('A database error occurred.', 'fc-shortcodes')), 500);
        wp_die();
    }
}

/**
 * Create saved shortcodes table
 */
function fluent_sc_create_saved_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fluent_sc_saved';
    $charset    = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id           bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        label        varchar(255) NOT NULL,
        shortcode    text NOT NULL,
        pid          bigint(20) unsigned NOT NULL DEFAULT 0,
        product_name varchar(255) NOT NULL DEFAULT '',
        created_at   datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at   datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'fluent_sc_create_saved_table');

/**
 * Maybe upgrade database on version change
 */
function fluent_sc_maybe_upgrade_db() {
    $installed = get_option('fc_sc_db_version', '0');
    if (version_compare($installed, FC_SC_VERSION, '<')) {
        fluent_sc_create_saved_table();
        update_option('fc_sc_db_version', FC_SC_VERSION);
    }
}
add_action('admin_init', 'fluent_sc_maybe_upgrade_db');

/**
 * Get first product image URL from fluent-products-gallery-image meta
 */
function fluent_sc_get_product_image_url($pid) {
    $gallery_meta = get_post_meta($pid, 'fluent-products-gallery-image', true);
    if (!empty($gallery_meta) && is_array($gallery_meta)) {
        $first = reset($gallery_meta);
        return !empty($first['url']) ? $first['url'] : '';
    }
    if (!empty($gallery_meta) && is_string($gallery_meta)) {
        $unserialized = maybe_unserialize($gallery_meta);
        if (is_array($unserialized)) {
            $first = reset($unserialized);
            return !empty($first['url']) ? $first['url'] : '';
        }
    }
    return '';
}

require_once FC_SC_DIR . 'includes/shortcodes.php';

/**
 * Register the [fluent_shortcode] shortcode
 * Uses inline styles for critical layout so theme cannot override.
 */
function fluent_shortcode_callback($atts) {
    $atts = shortcode_atts(
        array(
            'pid'               => 0,
            'item_id'           => '',
            'show_featured_img' => 'false',
            'image_height'      => '380',
            'layout'            => 'vertical',
            'image_width'       => '40',
            'show_price'        => 'false',
            'sale_price'        => '',
            'regular_price'     => '',
            'featured'          => 'false',
            'featured_label'    => 'Featured',
            'featured_style'    => 'ribbon',
            'featured_color'    => '#e65c00',
            'featured_position' => 'left',
            'checkout_link'     => '',
            'product_link'      => '',
            'show_links'        => 'both',
            'checkout_btn_text' => 'Buy Now',
            'product_btn_text'  => 'Learn More',
            'new_tab'           => 'false',
            'margin_top'        => '0',
            'margin_bottom'     => '0',
        ),
        $atts,
        'fluent_shortcode'
    );

    $pid = absint($atts['pid']);
    if (!$pid) {
        return '';
    }

    $product = get_post($pid);
    if (!$product || $product->post_type !== 'fluent-products' || $product->post_status !== 'publish') {
        return '';
    }

    $checkout_url = '';
    if (!empty($atts['checkout_link'])) {
        $checkout_url = esc_url($atts['checkout_link']);
    } elseif (!empty($atts['item_id'])) {
        $checkout_url = esc_url('https://jazzedge.academy/?fluent-cart=instant_checkout&item_id=' . absint($atts['item_id']) . '&quantity=1');
    }

    $product_url = !empty($atts['product_link']) ? esc_url($atts['product_link']) : get_permalink($pid);
    $target     = $atts['new_tab'] === 'true' ? ' target="_blank" rel="noopener noreferrer"' : '';
    $show       = $atts['show_links'];

    $layout = $atts['layout'] === 'horizontal' ? 'horizontal' : 'vertical';

    $margin_top    = absint($atts['margin_top']);
    $margin_bottom = absint($atts['margin_bottom']);

    $excerpt = !empty($product->post_excerpt) ? $product->post_excerpt : wp_trim_words($product->post_content, 30);

    $label    = esc_html($atts['featured_label']);
    $color    = sanitize_hex_color($atts['featured_color']);
    if (empty($color)) {
        $color = '#e65c00';
    }
    $style    = $atts['featured_style'] === 'pill' ? 'pill' : 'ribbon';
    $position = $atts['featured_position'] === 'right' ? 'right' : 'left';

    // Outer wrapper — inline styles so theme cannot override
    $wrapper_style = implode(';', array(
        'position:relative',
        'overflow:hidden',
        'width:100%',
        'max-width:100%',
        'box-sizing:border-box',
        'border:1px solid #e0e0e0',
        'border-radius:10px',
        'padding:0',
        'background:#fff',
        'box-shadow:0 2px 12px rgba(0,0,0,0.08)',
        'font-family:inherit',
    ));
    if ($margin_top > 0) {
        $wrapper_style .= ';margin-top:' . $margin_top . 'px';
    }
    if ($margin_bottom > 0) {
        $wrapper_style .= ';margin-bottom:' . $margin_bottom . 'px';
    }
    if ($layout === 'horizontal') {
        $wrapper_style .= ';display:flex;flex-direction:row;align-items:stretch';
    }

    $output = '<div class="fc-sc-wrap"><div class="fluent-shortcode-product fluent-sc-layout--' . esc_attr($layout) . '" style="' . esc_attr($wrapper_style) . '">';

    // Ribbon — inline styles
    if ($atts['featured'] === 'true' && $style === 'ribbon') {
        $ribbon_base = implode(';', array(
            'position:absolute',
            'top:20px',
            'width:140px',
            'text-align:center',
            'padding:6px 0',
            'z-index:10',
            'box-shadow:0 2px 4px rgba(0,0,0,0.25)',
            'background:' . $color,
        ));
        if ($position === 'right') {
            $ribbon_base .= ';right:-35px;transform:rotate(45deg)';
        } else {
            $ribbon_base .= ';left:-35px;transform:rotate(-45deg)';
        }
        $output .= '<div class="fluent-sc-ribbon fluent-sc-ribbon--' . esc_attr($position) . '" style="' . esc_attr($ribbon_base) . '">';
        $output .= '<span style="color:#fff;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;white-space:nowrap;display:block;">' . $label . '</span></div>';
    }

    // Image — inline styles
    if ($atts['show_featured_img'] === 'true') {
        $img_url = fluent_sc_get_product_image_url($pid);
        if ($img_url) {
            $img_height = max(100, min(800, absint($atts['image_height']) ?: 320));

            if ($layout === 'horizontal') {
                $img_width = max(20, min(60, absint($atts['image_width']) ?: 40));
                $img_wrap_style = implode(';', array(
                    'flex-shrink:0',
                    'overflow:hidden',
                    'border-radius:10px 0 0 10px',
                    'line-height:0',
                    'width:' . $img_width . '%',
                    'min-width:' . $img_width . '%',
                ));
                $img_style = implode(';', array(
                    'width:100%',
                    'height:100%',
                    'min-height:280px',
                    'max-height:' . $img_height . 'px',
                    'object-fit:cover',
                    'object-position:center',
                    'display:block',
                    'border-radius:10px 0 0 10px',
                ));
            } else {
                $img_wrap_style = implode(';', array(
                    'width:100%',
                    'overflow:hidden',
                    'border-radius:10px 10px 0 0',
                    'line-height:0',
                    'margin:0',
                    'padding:0',
                ));
                $img_style = implode(';', array(
                    'width:100%',
                    'height:' . $img_height . 'px',
                    'object-fit:cover',
                    'object-position:center',
                    'display:block',
                    'border-radius:10px 10px 0 0',
                ));
            }

            $output .= '<div class="fluent-sc-image" style="' . esc_attr($img_wrap_style) . '">';
            $output .= '<img src="' . esc_url($img_url) . '" alt="' . esc_attr($product->post_title) . '" class="fluent-sc-img" style="' . esc_attr($img_style) . '" />';
            $output .= '</div>';
        }
    }

    // Body — inline styles
    if ($layout === 'horizontal') {
        $body_style = implode(';', array(
            'flex:1',
            'min-width:0',
            'padding:24px 28px',
            'display:flex',
            'flex-direction:column',
            'justify-content:center',
            'box-sizing:border-box',
        ));
    } else {
        $body_style = 'padding:20px 24px 24px;box-sizing:border-box';
    }
    $output .= '<div class="fluent-sc-body" style="' . esc_attr($body_style) . '">';

    // Pill
    if ($atts['featured'] === 'true' && $style === 'pill') {
        $output .= '<div style="margin-bottom:10px;">';
        $output .= '<span style="display:inline-block;color:#fff;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;padding:4px 12px;border-radius:50px;background:' . esc_attr($color) . ';">' . $label . '</span></div>';
    }

    // Title
    $output .= '<h3 class="fluent-sc-title" style="margin:0 0 8px;font-size:1.15em;font-weight:700;line-height:1.3;color:#1a1a1a;">' . esc_html($product->post_title) . '</h3>';

    // Price
    if ($atts['show_price'] === 'true') {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT min_price, max_price FROM {$wpdb->prefix}fct_product_details WHERE post_id = %d LIMIT 1",
            $pid
        ));

        $regular = !empty($atts['regular_price']) ? floatval($atts['regular_price']) : ($row ? $row->max_price / 100 : 0);
        $sale    = !empty($atts['sale_price']) ? floatval($atts['sale_price']) : ($row ? $row->min_price / 100 : 0);

        if ($regular <= 0 && $sale <= 0 && $row) {
            $regular = $row->max_price / 100;
            $sale    = $row->min_price / 100;
        }
        if ($regular <= 0 && $sale > 0) {
            $regular = $sale;
        }

        $has_sale = $sale > 0 && $sale < $regular;

        if ($has_sale) {
            $output .= '<div class="fluent-sc-price-wrap" style="margin:0 0 12px;display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;">';
            $output .= '<span class="fluent-sc-price-original" style="font-size:1em;color:#999;text-decoration:line-through;font-weight:500;">$' . esc_html(number_format($regular, 2)) . '</span>';
            $output .= '<span class="fluent-sc-price-sale" style="font-size:1.4em;font-weight:700;color:#c0392b;">$' . esc_html(number_format($sale, 2)) . '</span>';
            $output .= '<span class="fluent-sc-price-savings" style="font-size:0.8em;background:#fde8e8;color:#c0392b;padding:2px 8px;border-radius:20px;font-weight:600;">Save $' . esc_html(number_format($regular - $sale, 2)) . '</span>';
            $output .= '</div>';
        } else {
            $display_price = $regular > 0 ? $regular : $sale;
            if ($display_price > 0) {
                $output .= '<div class="fluent-sc-price" style="font-size:1.3em;font-weight:700;color:#1a1a1a;margin:0 0 10px;">$' . esc_html(number_format($display_price, 2)) . '</div>';
            }
        }
    }

    // Excerpt
    if (!empty($excerpt)) {
        $output .= '<div class="fluent-sc-excerpt" style="color:#555;font-size:0.95em;line-height:1.6;margin:0 0 20px;">' . esc_html($excerpt) . '</div>';
    }

    // Buttons
    $output .= '<div class="fluent-sc-actions" style="display:flex;flex-direction:row;flex-wrap:wrap;gap:10px;margin-top:auto;align-items:center;">';

    if (($show === 'checkout' || $show === 'both') && $checkout_url) {
        $output .= '<a href="' . esc_url($checkout_url) . '" class="fluent-sc-btn fluent-sc-btn--buy" style="display:inline-block;padding:11px 24px;border-radius:5px;text-decoration:none;font-weight:700;font-size:0.95em;background:#e65c00;color:#fff;border:2px solid #e65c00;white-space:nowrap;line-height:1.2;"' . $target . '>' . esc_html($atts['checkout_btn_text']) . '</a>';
    }

    if ($show === 'product' || $show === 'both') {
        $output .= '<a href="' . esc_url($product_url) . '" class="fluent-sc-btn fluent-sc-btn--view" style="display:inline-block;padding:11px 24px;border-radius:5px;text-decoration:none;font-weight:700;font-size:0.95em;background:transparent;color:#2271b1;border:2px solid #2271b1;white-space:nowrap;line-height:1.2;"' . $target . '>' . esc_html($atts['product_btn_text']) . '</a>';
    }

    $output .= '</div></div></div></div>';

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('FC Shortcodes HTML: ' . substr($output, 0, 500) . '...');
    }

    return $output;
}
add_shortcode('fluent_shortcode', 'fluent_shortcode_callback');

/**
 * Admin menu
 */
function fluent_sc_admin_menu() {
    add_menu_page(
        __('FC Shortcodes', 'fc-shortcodes'),
        __('FC Shortcodes', 'fc-shortcodes'),
        'manage_options',
        'fluent-shortcodes',
        'fluent_sc_admin_page',
        'dashicons-shortcode',
        30
    );
}
add_action('admin_menu', 'fluent_sc_admin_menu');

/**
 * Admin page callback
 */
function fluent_sc_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(
            esc_html__('You do not have permission to access this page.', 'fc-shortcodes'),
            '',
            array('response' => 403)
        );
    }
    require_once FC_SC_DIR . 'admin/admin-page.php';
}

/**
 * Admin scripts and styles
 */
function fluent_sc_admin_scripts($hook) {
    if (strpos($hook, 'fluent-shortcodes') === false) {
        return;
    }

    wp_enqueue_style(
        'fc-sc-admin',
        FC_SC_URL . 'admin/css/admin.css',
        array(),
        FC_SC_VERSION
    );

    wp_enqueue_style(
        'fc-sc-frontend',
        FC_SC_URL . 'assets/css/frontend.css',
        array(),
        FC_SC_VERSION
    );

    wp_enqueue_style(
        'select2',
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
        array(),
        '4.1.0'
    );

    wp_enqueue_script(
        'select2',
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
        array('jquery'),
        '4.1.0',
        true
    );

    wp_enqueue_script(
        'fc-sc-admin',
        FC_SC_URL . 'admin/js/admin.js',
        array('jquery', 'select2'),
        FC_SC_VERSION,
        true
    );

    wp_localize_script('fc-sc-admin', 'fluentSC', array(
        'ajax_url'         => admin_url('admin-ajax.php'),
        'nonce'            => wp_create_nonce('fluent_sc_nonce'),
        'currentItemId'    => null,
        'currentPermalink' => null,
        'strings'          => array(
            'copied'         => __('Copied!', 'fc-shortcodes'),
            'saving'         => __('Saving...', 'fc-shortcodes'),
            'saved'          => __('Saved!', 'fc-shortcodes'),
            'delete_confirm' => __('Delete this saved shortcode? This cannot be undone.', 'fc-shortcodes'),
            'error'          => __('An error occurred. Please try again.', 'fc-shortcodes'),
        ),
    ));
}
add_action('admin_enqueue_scripts', 'fluent_sc_admin_scripts');

/**
 * Frontend styles — only when shortcode is present
 */
function fluent_sc_frontend_scripts() {
    wp_register_style(
        'fc-sc-frontend',
        FC_SC_URL . 'assets/css/frontend.css',
        array('wp-block-library'),
        FC_SC_VERSION
    );

    $shortcodes = array(
        'fluent_shortcode', 'fluent_price', 'fluent_add_to_cart',
        'fluent_stock', 'fluent_product_image',
        'fluent_product_title', 'fluent_product_excerpt',
    );

    global $post;
    if (is_a($post, 'WP_Post')) {
        foreach ($shortcodes as $sc) {
            if (has_shortcode($post->post_content, $sc)) {
                wp_enqueue_style('fc-sc-frontend');
                break;
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'fluent_sc_frontend_scripts');

/**
 * Admin footer credit
 */
function fluent_sc_admin_footer($text) {
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'fluent-shortcodes') !== false) {
        return 'FC Shortcodes by <a href="' . esc_url(FC_SC_AUTHOR_URL) . '" target="_blank" rel="noopener">' . esc_html(FC_SC_AUTHOR) . '</a>';
    }
    return $text;
}
add_filter('admin_footer_text', 'fluent_sc_admin_footer');

/* ==================== AJAX HANDLERS ==================== */

function fluent_sc_ajax_get_item_id() {
    if (!check_ajax_referer('fluent_sc_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error(array('message' => __('Invalid request method.', 'fc-shortcodes')), 405);
        wp_die();
    }

    fluent_sc_check_rate_limit('get_item_id');

    $pid = absint($_POST['pid'] ?? 0);
    if (!$pid) {
        wp_send_json_error(array('message' => 'No PID'));
    }

    global $wpdb;
    $item_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}fct_product_details WHERE post_id = %d LIMIT 1",
        $pid
    ));

    if ($item_id) {
        wp_send_json_success(array('item_id' => $item_id));
    } else {
        wp_send_json_error(array('message' => 'Not found'));
    }
}
add_action('wp_ajax_fluent_sc_get_item_id', 'fluent_sc_ajax_get_item_id');

function fluent_sc_ajax_get_permalink() {
    if (!check_ajax_referer('fluent_sc_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error(array('message' => __('Invalid request method.', 'fc-shortcodes')), 405);
        wp_die();
    }

    fluent_sc_check_rate_limit('get_permalink');

    $pid = absint($_POST['pid'] ?? 0);
    if (!$pid) {
        wp_send_json_error(array('message' => 'No PID'));
    }

    $post = get_post($pid);
    if (!$post || $post->post_type !== 'fluent-products') {
        wp_send_json_error(array('message' => 'Invalid product'));
    }

    wp_send_json_success(array('permalink' => get_permalink($pid)));
}
add_action('wp_ajax_fluent_sc_get_permalink', 'fluent_sc_ajax_get_permalink');

function fluent_sc_ajax_preview() {
    if (!check_ajax_referer('fluent_sc_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error(array('message' => __('Invalid request method.', 'fc-shortcodes')), 405);
        wp_die();
    }

    fluent_sc_check_rate_limit('preview');

    $shortcode = sanitize_text_field(wp_unslash($_POST['shortcode'] ?? ''));
    if (empty($shortcode)) {
        wp_send_json_error(array('message' => __('No shortcode.', 'fc-shortcodes')), 400);
        wp_die();
    }

    $allowed_tags = array(
        'fluent_shortcode', 'fluent_price', 'fluent_add_to_cart',
        'fluent_stock', 'fluent_product_image',
        'fluent_product_title', 'fluent_product_excerpt',
    );

    $valid = false;
    foreach ($allowed_tags as $tag) {
        if (strpos($shortcode, '[' . $tag) === 0) {
            $valid = true;
            break;
        }
    }

    if (!$valid) {
        wp_send_json_error(array('message' => __('Invalid shortcode.', 'fc-shortcodes')), 400);
        wp_die();
    }

    $output = do_shortcode($shortcode);

    $styles = '';
    $css_file = FC_SC_DIR . 'assets/css/frontend.css';
    if (file_exists($css_file)) {
        $styles = file_get_contents($css_file);
    }

    wp_send_json_success(array('html' => $output, 'styles' => $styles));
}
add_action('wp_ajax_fluent_sc_preview', 'fluent_sc_ajax_preview');

function fluent_sc_ajax_save() {
    if (!check_ajax_referer('fluent_sc_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error(array('message' => __('Invalid request method.', 'fc-shortcodes')), 405);
        wp_die();
    }

    fluent_sc_check_rate_limit('save');

    $label        = sanitize_text_field(wp_unslash($_POST['label'] ?? ''));
    $shortcode    = sanitize_text_field(wp_unslash($_POST['shortcode'] ?? ''));
    $pid          = absint($_POST['pid'] ?? 0);
    $product_name = sanitize_text_field(wp_unslash($_POST['product_name'] ?? ''));

    if (empty($label) || empty($shortcode)) {
        wp_send_json_error(array('message' => __('Label and shortcode are required.', 'fc-shortcodes')));
    }

    global $wpdb;
    $result = $wpdb->insert(
        $wpdb->prefix . 'fluent_sc_saved',
        array(
            'label'        => $label,
            'shortcode'    => $shortcode,
            'pid'          => $pid,
            'product_name' => $product_name,
        ),
        array('%s', '%s', '%d', '%s')
    );

    fluent_sc_db_error_check('save_shortcode');

    if ($result) {
        wp_send_json_success(array(
            'id'           => $wpdb->insert_id,
            'label'        => $label,
            'shortcode'    => $shortcode,
            'product_name' => $product_name,
            'created_at'   => current_time('mysql'),
        ));
    } else {
        wp_send_json_error(array('message' => __('Failed to save.', 'fc-shortcodes')));
    }
}
add_action('wp_ajax_fluent_sc_save', 'fluent_sc_ajax_save');

function fluent_sc_ajax_load_saved() {
    if (!check_ajax_referer('fluent_sc_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error(array('message' => __('Invalid request method.', 'fc-shortcodes')), 405);
        wp_die();
    }

    fluent_sc_check_rate_limit('load_saved');

    global $wpdb;
    $table   = $wpdb->prefix . 'fluent_sc_saved';
    $results = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC");
    wp_send_json_success($results);
}
add_action('wp_ajax_fluent_sc_load_saved', 'fluent_sc_ajax_load_saved');

function fluent_sc_ajax_update_label() {
    if (!check_ajax_referer('fluent_sc_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error(array('message' => __('Invalid request method.', 'fc-shortcodes')), 405);
        wp_die();
    }

    fluent_sc_check_rate_limit('update_label');

    $id    = absint($_POST['id'] ?? 0);
    $label = sanitize_text_field(wp_unslash($_POST['label'] ?? ''));

    if (!$id || empty($label)) {
        wp_send_json_error(array('message' => __('Invalid request.', 'fc-shortcodes')));
    }

    global $wpdb;
    $result = $wpdb->update(
        $wpdb->prefix . 'fluent_sc_saved',
        array('label' => $label),
        array('id' => $id),
        array('%s'),
        array('%d')
    );

    fluent_sc_db_error_check('update_label');

    $result !== false ? wp_send_json_success() : wp_send_json_error(array('message' => __('Update failed.', 'fc-shortcodes')));
}
add_action('wp_ajax_fluent_sc_update_label', 'fluent_sc_ajax_update_label');

function fluent_sc_ajax_delete_saved() {
    if (!check_ajax_referer('fluent_sc_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions.', 'fc-shortcodes')), 403);
        wp_die();
    }
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error(array('message' => __('Invalid request method.', 'fc-shortcodes')), 405);
        wp_die();
    }

    fluent_sc_check_rate_limit('delete_saved');

    $id = absint($_POST['id'] ?? 0);
    if (!$id) {
        wp_send_json_error(array('message' => __('Invalid request.', 'fc-shortcodes')));
    }

    global $wpdb;
    $result = $wpdb->delete(
        $wpdb->prefix . 'fluent_sc_saved',
        array('id' => $id),
        array('%d')
    );

    fluent_sc_db_error_check('delete_saved');

    $result !== false ? wp_send_json_success() : wp_send_json_error(array('message' => __('Delete failed.', 'fc-shortcodes')));
}
add_action('wp_ajax_fluent_sc_delete_saved', 'fluent_sc_ajax_delete_saved');
