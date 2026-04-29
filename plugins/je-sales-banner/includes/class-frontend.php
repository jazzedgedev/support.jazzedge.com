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
        add_action('wp_footer', array(__CLASS__, 'inject_popup'), 9999);
        add_action('wp_footer', array(__CLASS__, 'inject_footer_banner'), PHP_INT_MAX - 10);
        add_action('wp_enqueue_scripts', array(__CLASS__, 'maybe_enqueue_assets'));
    }

    /**
     * Oxygen Builder editor / preview iframe — skip banners and assets.
     *
     * @return bool
     */
    private static function is_oxygen_editor()
    {
        return isset($_GET['ct_builder'])
            || isset($_GET['oxygen_iframe'])
            || (defined('SHOW_CT_BUILDER') && SHOW_CT_BUILDER);
    }

    public static function maybe_enqueue_assets()
    {
        if (is_admin()) {
            return;
        }

        if (self::is_oxygen_editor()) {
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
     * Active dismissible popup (independent of header/footer).
     *
     * @return bool
     */
    private static function row_wants_popup($row)
    {
        return (int) ($row->show_popup ?? 0) === 1;
    }

    private static function should_show_popup_layer()
    {
        foreach (JE_SB_Database::get_all_active_rows() as $row) {
            if (!self::row_wants_popup($row)) {
                continue;
            }
            if (self::row_ok_for_frontend($row)) {
                return true;
            }
        }

        return false;
    }

    public static function should_enqueue_assets()
    {
        if (self::is_oxygen_editor()) {
            return false;
        }

        if (self::should_show_header() || self::should_show_footer() || self::should_show_popup_layer()) {
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

    public static function inject_header_banner()
    {
        if (self::is_oxygen_editor()) {
            return;
        }

        if (!self::should_show_header()) {
            return;
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in render.
        echo self::render_header_banners_html();
    }

    public static function inject_footer_banner()
    {
        if (self::is_oxygen_editor()) {
            return;
        }

        foreach (self::get_valid_footer_banners() as $row) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in render.
            echo self::render_banner_html($row, 'footer');
        }
    }

    /**
     * One dismissible popup per page (first eligible banner by ID). Header/footer unaffected.
     */
    public static function inject_popup()
    {
        if (self::is_oxygen_editor()) {
            return;
        }

        foreach (JE_SB_Database::get_all_active_rows() as $row) {
            if (!self::row_wants_popup($row)) {
                continue;
            }
            if (!self::row_ok_for_frontend($row)) {
                continue;
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in render_popup_html.
            echo self::render_popup_html($row);

            return;
        }
    }

    /**
     * @param object $row
     * @return string
     */
    public static function render_popup_html($row)
    {
        if (!$row || !self::row_wants_popup($row)) {
            return '';
        }

        if (JE_SB_Database::is_expired($row) || !JE_SB_Database::is_within_schedule($row)) {
            return '';
        }

        $tpl = max(1, min(6, (int) $row->template));
        $id = (int) $row->id;
        $end_dt = date_create((string) $row->end_date, wp_timezone());
        if (!$end_dt) {
            return '';
        }
        $end_ts = (string) $end_dt->getTimestamp();
        $delay_sec = isset($row->popup_delay_seconds) ? max(0, min(3600, (int) $row->popup_delay_seconds)) : 0;

        $desc = isset($row->description) ? trim((string) $row->description) : '';
        $sale_label = self::format_sale_amount($row);
        $coupon_raw = isset($row->coupon_code) ? $row->coupon_code : null;
        $coupon_code = ($coupon_raw !== null && (string) $coupon_raw !== '') ? trim((string) $coupon_raw) : '';
        $title_id = 'je-sb-popup-title-' . $id;
        $dialog_classes = 'je-sb-popup__dialog je-sale-banner--tpl-' . $tpl;

        ob_start();
        ?>
<div class="je-sb-popup" data-je-sb-popup="1" data-je-sb-popup-id="<?php echo esc_attr((string) $id); ?>" data-je-sb-popup-end="<?php echo esc_attr($end_ts); ?>" data-je-sb-popup-delay="<?php echo esc_attr((string) $delay_sec); ?>" hidden>
    <div class="je-sb-popup__backdrop" data-je-sb-popup-dismiss tabindex="-1" aria-hidden="true"></div>
    <div class="<?php echo esc_attr($dialog_classes); ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr($title_id); ?>">
        <div class="je-sb-popup__head">
            <span class="je-sb-popup__badge"><?php esc_html_e('SALE', 'je-sales-banner'); ?></span>
            <div class="je-sb-popup__head-offer">
                <p class="je-sb-popup__amount"><?php echo wp_kses_post($sale_label); ?></p>
            </div>
            <button type="button" class="je-sb-popup__close" data-je-sb-popup-close aria-label="<?php esc_attr_e('Close', 'je-sales-banner'); ?>">
                <span class="je-sb-popup__close-lines" aria-hidden="true"></span>
            </button>
        </div>
        <div class="je-sb-popup__body">
            <h2 class="je-sb-popup__title" id="<?php echo esc_attr($title_id); ?>"><?php echo esc_html((string) $row->title); ?></h2>
            <?php if ($desc !== '') : ?>
                <p class="je-sb-popup__desc"><?php echo esc_html($desc); ?></p>
            <?php endif; ?>
            <div class="je-sb-popup__timer" aria-live="polite">
                <span class="je-sb-popup__timer-caption"><?php esc_html_e('Offer ends in', 'je-sales-banner'); ?></span>
                <span class="je-sb-popup__timer-digits" data-je-sb-timer-value></span>
            </div>
            <?php if ($coupon_code !== '') : ?>
            <div class="je-sb-popup__coupon-wrap">
                <span class="je-sale-banner__coupon je-sb-popup__coupon">
                    <span class="je-sale-banner__coupon-label"><?php esc_html_e('Use Code:', 'je-sales-banner'); ?></span>
                    <span class="je-sale-banner__coupon-code" data-je-sb-code><?php echo esc_html(strtoupper($coupon_code)); ?></span>
                    <button type="button" class="je-sale-banner__coupon-copy" data-je-sb-copy="<?php echo esc_attr($coupon_code); ?>" aria-label="<?php esc_attr_e('Copy coupon code', 'je-sales-banner'); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                        <span class="je-sale-banner__coupon-tip" data-je-sb-tip hidden><?php esc_html_e('Copied!', 'je-sales-banner'); ?></span>
                    </button>
                </span>
            </div>
            <?php endif; ?>
            <a class="je-sb-popup__cta" href="<?php echo esc_url((string) $row->cta_url); ?>"><?php echo esc_html(self::format_popup_cta_label($row)); ?></a>
        </div>
    </div>
</div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * @param array<string, string>|string $atts
     * @return string
     */
    public static function shortcode_sale_banner($atts)
    {
        if (self::is_oxygen_editor()) {
            return '';
        }

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
     * Popup CTA label from the same sale amount as the banner (e.g. Save 30%, Save $25).
     *
     * @param object $row
     * @return string Plain text (escape with esc_html when outputting).
     */
    public static function format_popup_cta_label($row)
    {
        $amount = isset($row->sale_amount) ? (float) $row->sale_amount : 0.0;
        $type = isset($row->sale_type) ? (string) $row->sale_type : 'percent';
        $n = number_format_i18n($amount, 0);

        if ($type === 'dollar') {
            $save = sprintf(/* translators: %s: dollar amount */ __('Save $%s', 'je-sales-banner'), $n);
        } else {
            $save = sprintf(/* translators: %s: percent */ __('Save %s%%', 'je-sales-banner'), $n);
        }

        $cta = isset($row->cta_label) ? trim((string) $row->cta_label) : '';
        if ($cta === '') {
            $cta = __('Shop Now', 'je-sales-banner');
        }

        return sprintf(
            /* translators: 1: savings phrase (e.g. Save 30%%), 2: CTA label shown in parentheses */
            __('%1$s (%2$s)', 'je-sales-banner'),
            $save,
            $cta
        );
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
        if (self::is_oxygen_editor()) {
            return;
        }

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
