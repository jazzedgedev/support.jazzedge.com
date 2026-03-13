<?php
/**
 * Fluent Shortcodes - Modular product shortcodes
 *
 * @package Fluent_Shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Resolve product ID — supports dynamic detection on product pages
 * All shortcodes work with or without pid when on a fluent-products singular page
 *
 * @param int $pid Product post ID (0 = auto-detect)
 * @return int Resolved post ID or 0
 */
function fluent_sc_resolve_pid($pid = 0) {
    if (!empty($pid)) {
        return absint($pid);
    }

    if (is_singular('fluent-products')) {
        return get_the_ID();
    }

    $obj = get_queried_object();
    if ($obj && isset($obj->post_type) && $obj->post_type === 'fluent-products') {
        return $obj->ID;
    }

    return 0;
}

/**
 * [fluent_price] — Display product price from wp_fct_product_details
 */
function fluent_sc_price($atts) {
    $atts = shortcode_atts(array(
        'pid'           => 0,
        'show_original' => 'false',
        'format'        => '$%s',
        'class'         => '',
    ), $atts, 'fluent_price');

    $pid = fluent_sc_resolve_pid($atts['pid']);
    if (!$pid) {
        return '';
    }

    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT min_price, max_price FROM {$wpdb->prefix}fct_product_details WHERE post_id = %d LIMIT 1",
        $pid
    ));
    if (!$row) {
        return '';
    }

    $min   = number_format($row->min_price / 100, 2);
    $max   = number_format($row->max_price / 100, 2);
    $class = esc_attr($atts['class']);
    $fmt   = $atts['format'];

    $output = '<span class="fluent-sc-price-wrap ' . $class . '">';

    if ($atts['show_original'] === 'true' && $row->min_price != $row->max_price) {
        $output .= '<span class="fluent-sc-price-original"><s>' . esc_html(sprintf($fmt, $max)) . '</s></span> ';
        $output .= '<span class="fluent-sc-price-sale">' . esc_html(sprintf($fmt, $min)) . '</span>';
    } else {
        $output .= '<span class="fluent-sc-price">' . esc_html(sprintf($fmt, $min)) . '</span>';
    }

    $output .= '</span>';
    return $output;
}
add_shortcode('fluent_price', 'fluent_sc_price');

/**
 * [fluent_add_to_cart] — Add to Cart button
 */
function fluent_sc_add_to_cart($atts) {
    $atts = shortcode_atts(array(
        'pid'      => 0,
        'label'    => 'Add to Cart',
        'class'    => '',
        'style'    => 'button',
        'redirect' => 'false',
    ), $atts, 'fluent_add_to_cart');

    $pid = fluent_sc_resolve_pid($atts['pid']);
    if (!$pid) {
        return '';
    }

    global $wpdb;
    $item_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}fct_product_details WHERE post_id = %d LIMIT 1",
        $pid
    ));
    if (!$item_id) {
        return '';
    }

    $checkout_url = home_url('/?fluent-cart=instant_checkout&item_id=' . absint($item_id) . '&quantity=1');
    $cart_url     = home_url('/?fluent-cart=add_to_cart&item_id=' . absint($item_id) . '&quantity=1');
    $href         = $atts['redirect'] === 'true' ? $checkout_url : $cart_url;

    $btn_class = $atts['style'] === 'link' ? 'fluent-sc-cart-link' : 'fluent-sc-cart-btn';
    $btn_class .= ' ' . esc_attr($atts['class']);

    return '<a href="' . esc_url($href) . '" class="' . trim($btn_class) . '">' . esc_html($atts['label']) . '</a>';
}
add_shortcode('fluent_add_to_cart', 'fluent_sc_add_to_cart');

/**
 * [fluent_stock] — Stock status
 */
function fluent_sc_stock($atts) {
    $atts = shortcode_atts(array(
        'pid'                => 0,
        'show_quantity'      => 'false',
        'in_stock_label'     => 'In Stock',
        'out_of_stock_label' => 'Out of Stock',
        'class'              => '',
    ), $atts, 'fluent_stock');

    $pid = fluent_sc_resolve_pid($atts['pid']);
    if (!$pid) {
        return '';
    }

    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT stock_availability, manage_stock FROM {$wpdb->prefix}fct_product_details WHERE post_id = %d LIMIT 1",
        $pid
    ));
    if (!$row) {
        return '';
    }

    $in_stock = isset($row->stock_availability) && $row->stock_availability === 'in-stock';
    $label    = $in_stock ? esc_html($atts['in_stock_label']) : esc_html($atts['out_of_stock_label']);
    $status   = $in_stock ? 'in-stock' : 'out-of-stock';
    $class    = 'fluent-sc-stock fluent-sc-stock--' . $status . ' ' . esc_attr($atts['class']);

    return '<span class="' . trim($class) . '">' . $label . '</span>';
}
add_shortcode('fluent_stock', 'fluent_sc_stock');

/**
 * [fluent_product_title] — Product title
 */
function fluent_sc_title($atts) {
    $atts = shortcode_atts(array(
        'pid'   => 0,
        'tag'   => 'h2',
        'class' => '',
        'link'  => 'false',
    ), $atts, 'fluent_product_title');

    $pid = fluent_sc_resolve_pid($atts['pid']);
    if (!$pid) {
        return '';
    }

    $post = get_post($pid);
    if (!$post || $post->post_type !== 'fluent-products') {
        return '';
    }

    $title = esc_html($post->post_title);
    $tag   = sanitize_key($atts['tag']);
    if (!in_array($tag, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span'))) {
        $tag = 'h2';
    }
    $class = esc_attr($atts['class']);

    if ($atts['link'] === 'true') {
        $title = '<a href="' . esc_url(get_permalink($pid)) . '">' . $title . '</a>';
    }

    return '<' . $tag . ' class="fluent-sc-title ' . $class . '">' . $title . '</' . $tag . '>';
}
add_shortcode('fluent_product_title', 'fluent_sc_title');

/**
 * [fluent_product_excerpt] — Product excerpt or trimmed content
 */
function fluent_sc_excerpt($atts) {
    $atts = shortcode_atts(array(
        'pid'   => 0,
        'words' => '30',
        'class' => '',
    ), $atts, 'fluent_product_excerpt');

    $pid = fluent_sc_resolve_pid($atts['pid']);
    if (!$pid) {
        return '';
    }

    $post = get_post($pid);
    if (!$post || $post->post_type !== 'fluent-products') {
        return '';
    }

    $words = absint($atts['words']);
    if ($words < 1) {
        $words = 30;
    }

    $excerpt = !empty($post->post_excerpt) ? $post->post_excerpt : wp_trim_words($post->post_content, $words);
    $class   = esc_attr($atts['class']);

    return '<div class="fluent-sc-excerpt ' . $class . '">' . esc_html($excerpt) . '</div>';
}
add_shortcode('fluent_product_excerpt', 'fluent_sc_excerpt');

/**
 * [fluent_product_image] — Product image from gallery meta
 */
function fluent_sc_image($atts) {
    $atts = shortcode_atts(array(
        'pid'           => 0,
        'class'         => '',
        'link'          => 'false',
        'index'         => 0,
        'image_height'  => '380',
    ), $atts, 'fluent_product_image');

    $pid = fluent_sc_resolve_pid($atts['pid']);
    if (!$pid) {
        return '';
    }

    $gallery = get_post_meta($pid, 'fluent-products-gallery-image', true);
    $gallery = maybe_unserialize($gallery);
    if (empty($gallery) || !is_array($gallery)) {
        return '';
    }

    $index = absint($atts['index']);
    $items = array_values($gallery);
    if (!isset($items[$index]['url'])) {
        return '';
    }

    $img_height = absint($atts['image_height']);
    if ($img_height < 100) {
        $img_height = 380;
    }
    if ($img_height > 800) {
        $img_height = 800;
    }

    $img_url = esc_url($items[$index]['url']);
    $alt     = esc_attr(get_the_title($pid));
    $class   = 'fluent-sc-img ' . esc_attr($atts['class']);

    $img = '<img src="' . $img_url . '" alt="' . $alt . '" class="' . trim($class) . '" style="height:' . $img_height . 'px;" />';

    if ($atts['link'] === 'true') {
        $img = '<a href="' . esc_url(get_permalink($pid)) . '">' . $img . '</a>';
    }

    return '<div class="fluent-sc-image-wrap" style="max-height:' . $img_height . 'px;">' . $img . '</div>';
}
add_shortcode('fluent_product_image', 'fluent_sc_image');
