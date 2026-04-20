<?php
/**
 * Shortcode, header/footer injection, template rendering.
 */

if (!defined('ABSPATH')) {
    exit;
}

class JE_SB_Frontend
{
    public static function init()
    {
        add_shortcode('sale_banner', array(__CLASS__, 'shortcode_sale_banner'));

        add_action('wp_body_open', array(__CLASS__, 'inject_header_banner'), 0);
        add_action('template_redirect', array(__CLASS__, 'maybe_start_body_buffer'), 0);
        add_action('wp_footer', array(__CLASS__, 'inject_footer_banner'), PHP_INT_MAX - 10);
        add_action('wp_enqueue_scripts', array(__CLASS__, 'maybe_enqueue_assets'));
    }

    public static function maybe_enqueue_assets()
    {
        if (is_admin()) {
            return;
        }

        if (self::should_enqueue_assets()) {
            self::enqueue_frontend_assets();
        }
    }

    /**
     * Active, in schedule, not expired.
     *
     * @param object $row
     */
    private static function row_ok_for_frontend($row)
    {
        return $row
            && (int) $row->is_active === 1
            && !JE_SB_Database::is_expired($row)
            && JE_SB_Database::is_within_schedule($row);
    }

    /**
     * @return array<int, object>
     */
    private static function get_valid_header_banners()
    {
        $out = array();
        foreach (JE_SB_Database::get_all_active_rows() as $row) {
            if (!self::row_ok_for_frontend($row)) {
                continue;
            }
            $loc = (string) $row->display_location;
            if ($loc === 'header' || $loc === 'both') {
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * @return array<int, object>
     */
    private static function get_valid_footer_banners()
    {
        $out = array();
        foreach (JE_SB_Database::get_all_active_rows() as $row) {
            if (!self::row_ok_for_frontend($row)) {
                continue;
            }
            $loc = (string) $row->display_location;
            if ($loc === 'footer' || $loc === 'both') {
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * @return string
     */
    private static function render_header_banners_html()
    {
        $html = '';
        foreach (self::get_valid_header_banners() as $row) {
            $html .= self::render_banner_html($row, 'header');
        }

        return $html;
    }

    /**
     * @return bool
     */
    public static function should_show_header()
    {
        return count(self::get_valid_header_banners()) > 0;
    }

    /**
     * @return bool
     */
    public static function should_show_footer()
    {
        foreach (self::get_valid_footer_banners() as $row) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function should_enqueue_assets()
    {
        if (self::should_show_header() || self::should_show_footer()) {
            return true;
        }

        foreach (JE_SB_Database::get_all_active_rows() as $row) {
            if (self::row_ok_for_frontend($row) && (string) $row->display_location === 'shortcode') {
                return true;
            }
        }

        global $post;
        if ($post instanceof WP_Post && has_shortcode((string) $post->post_content, 'sale_banner')) {
            return true;
        }

        return false;
    }

    public static function maybe_start_body_buffer()
    {
        if (is_admin()) {
            return;
        }

        if (!self::should_show_header()) {
            return;
        }

        ob_start(array(__CLASS__, 'buffer_inject_after_body'));
    }

    /**
     * @param string $html
     * @return string
     */
    public static function buffer_inject_after_body($html)
    {
        if (strpos($html, 'data-je-sb-header="1"') !== false) {
            return $html;
        }

        $banners = self::render_header_banners_html();
        if ($banners === '') {
            return $html;
        }

        $replaced = preg_replace('/(<body[^>]*>)/i', '$1' . $banners, $html, 1, $count);
        if ($count) {
            return $replaced;
        }

        return $html;
    }

    public static function inject_header_banner()
    {
        if (!self::should_show_header()) {
            return;
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in render.
        echo self::render_header_banners_html();
    }

    public static function inject_footer_banner()
    {
        foreach (self::get_valid_footer_banners() as $row) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in render.
            echo self::render_banner_html($row, 'footer');
        }
    }

    /**
     * @param array<string, string>|string $atts
     * @return string
     */
    public static function shortcode_sale_banner($atts)
    {
        $atts = shortcode_atts(
            array('id' => 0),
            $atts,
            'sale_banner'
        );

        $id = (int) $atts['id'];
        if ($id < 1) {
            return '';
        }

        $row = JE_SB_Database::get_row($id);
        if (!$row || (int) $row->is_active !== 1) {
            return '';
        }

        if (JE_SB_Database::is_expired($row) || !JE_SB_Database::is_within_schedule($row)) {
            return '';
        }

        self::enqueue_frontend_assets();

        return self::render_banner_html($row, 'shortcode');
    }

    /**
     * @param object $row
     * @param string $context header|footer|shortcode|preview
     * @param bool $bypass_schedule Admin preview only.
     * @return string
     */
    public static function render_banner_html($row, $context, $bypass_schedule = false)
    {
        if (!$bypass_schedule) {
            if (JE_SB_Database::is_expired($row)) {
                return '';
            }

            if (!JE_SB_Database::is_within_schedule($row)) {
                return '';
            }
        }

        $tpl = max(1, min(6, (int) $row->template));
        $file = JE_SB_PLUGIN_DIR . 'templates/template-' . $tpl . '.php';
        if (!is_readable($file)) {
            return '';
        }

        $end_dt = date_create((string) $row->end_date, wp_timezone());
        if (!$end_dt) {
            return '';
        }
        $end_ts = $end_dt->getTimestamp();

        $classes = array('je-sale-banner', 'je-sale-banner--tpl-' . $tpl, 'je-sale-banner--ctx-' . $context);
        if ($context === 'header') {
            $classes[] = 'je-sale-banner--header';
        }
        if ($context === 'footer') {
            $classes[] = 'je-sale-banner--footer';
        }

        $attrs = array(
            'class' => implode(' ', $classes),
            'data-je-sb-end' => (string) $end_ts,
            'data-je-sb-active' => '1',
        );

        if ($context === 'header') {
            $attrs['data-je-sb-header'] = '1';
        }

        $attr_str = '';
        foreach ($attrs as $k => $v) {
            $attr_str .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
        }

        ob_start();
        $sale_label = self::format_sale_amount($row);
        $coupon_code = isset($row->coupon_code) ? trim((string) $row->coupon_code) : '';
        $coupon_highlight = isset($row->coupon_highlight_color) && $row->coupon_highlight_color !== ''
            ? (string) $row->coupon_highlight_color
            : '#F04E23';

        /** @psalm-suppress UnresolvableInclude */
        include $file; // Variables: $row, $sale_label, $coupon_code, $coupon_highlight, $context.

        $inner = ob_get_clean();

        return '<div' . $attr_str . ' role="region" aria-label="' . esc_attr__('Sale announcement', 'je-sales-banner') . '">' . $inner . '</div>';
    }

    /**
     * @param object $row
     */
    public static function format_sale_amount($row)
    {
        $amount = isset($row->sale_amount) ? (float) $row->sale_amount : 0.0;
        $type = isset($row->sale_type) ? (string) $row->sale_type : 'percent';

        if ($type === 'dollar') {
            $n = number_format_i18n($amount, 0);

            return sprintf(/* translators: %s: amount */ esc_html__('$%s OFF', 'je-sales-banner'), $n);
        }

        $n = number_format_i18n($amount, 0);

        return sprintf(/* translators: %s: percent */ esc_html__('%s%% OFF', 'je-sales-banner'), $n);
    }

    /**
     * @param string $hex
     * @return string
     */
    public static function contrast_text_color($hex)
    {
        $hex = ltrim((string) $hex, '#');
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return '#ffffff';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return $yiq >= 160 ? '#002A34' : '#ffffff';
    }

    public static function enqueue_frontend_assets()
    {
        wp_enqueue_style(
            'je-sb-frontend',
            JE_SB_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            JE_SB_VERSION
        );

        wp_enqueue_script(
            'je-sb-frontend',
            JE_SB_PLUGIN_URL . 'assets/js/frontend.js',
            array(),
            JE_SB_VERSION,
            true
        );
    }
}
