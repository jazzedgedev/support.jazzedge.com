<?php
/**
 * Admin menu, list, forms, preview.
 */

if (!defined('ABSPATH')) {
    exit;
}

class JE_SB_Admin
{
    const PAGE_SLUG = 'je-sales-banner';

    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_init', array(__CLASS__, 'handle_actions'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }

    public static function register_menu()
    {
        add_menu_page(
            __('JE Sales Banner', 'je-sales-banner'),
            __('JE Sales Banner', 'je-sales-banner'),
            'manage_options',
            self::PAGE_SLUG,
            array(__CLASS__, 'render_screen'),
            'dashicons-megaphone',
            58
        );
    }

    /**
     * @return string
     */
    public static function base_url()
    {
        return admin_url('admin.php?page=' . self::PAGE_SLUG);
    }

    public static function enqueue_assets($hook)
    {
        if ($hook !== 'toplevel_page_' . self::PAGE_SLUG) {
            return;
        }

        wp_enqueue_style(
            'je-sb-admin',
            JE_SB_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            JE_SB_VERSION
        );

        wp_enqueue_script(
            'je-sb-admin',
            JE_SB_PLUGIN_URL . 'assets/js/admin.js',
            array(),
            JE_SB_VERSION,
            true
        );

        $preview_row = null;
        if (isset($_GET['je_sb_preview']) && isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            if ($id && current_user_can('manage_options') && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? '')), 'je_sb_preview_' . $id)) {
                $preview_row = JE_SB_Database::get_row($id);
            }
        }

        wp_localize_script(
            'je-sb-admin',
            'jeSbAdmin',
            array(
                'i18n' => array(
                    'shopNow' => __('Shop Now', 'je-sales-banner'),
                    'useCode' => __('Use Code:', 'je-sales-banner'),
                    'copied' => __('Copied!', 'je-sales-banner'),
                    'copyShortcode' => __('Copy Shortcode', 'je-sales-banner'),
                ),
                'defaults' => array(
                    'ctaLabel' => __('Shop Now', 'je-sales-banner'),
                    'ctaUrl' => 'https://shop.jazzedge.com/shop',
                    'couponColor' => '#F04E23',
                ),
                'previewContext' => $preview_row ? 'preview' : '',
                'previewEndTs' => $preview_row && !empty($preview_row->end_date)
                    ? (string) strtotime((string) $preview_row->end_date)
                    : '',
            )
        );

        if ($preview_row) {
            wp_enqueue_style('je-sb-frontend', JE_SB_PLUGIN_URL . 'assets/css/frontend.css', array(), JE_SB_VERSION);
            wp_enqueue_script('je-sb-frontend', JE_SB_PLUGIN_URL . 'assets/js/frontend.js', array(), JE_SB_VERSION, true);
        }
    }

    public static function handle_actions()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== self::PAGE_SLUG) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        // Save banner (POST).
        if (!empty($_POST['je_sb_action']) && $_POST['je_sb_action'] === 'save') {
            check_admin_referer('je_sb_save_banner', 'je_sb_nonce');

            $id = isset($_POST['je_sb_id']) ? (int) $_POST['je_sb_id'] : 0;

            $data = self::sanitize_banner_post();

            if ($id > 0) {
                JE_SB_Database::update($id, $data);
                $redirect_id = $id;
            } else {
                $new_id = JE_SB_Database::insert($data);
                $redirect_id = $new_id ? (int) $new_id : 0;
            }

            $url = add_query_arg(
                array(
                    'page' => self::PAGE_SLUG,
                    'message' => 'saved',
                    'id' => $redirect_id,
                ),
                admin_url('admin.php')
            );
            wp_safe_redirect($url);
            exit;
        }

        // Toggle active (GET).
        if (!empty($_GET['je_sb_action']) && $_GET['je_sb_action'] === 'toggle' && !empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            check_admin_referer('je_sb_toggle_' . $id);

            $existing = JE_SB_Database::get_row($id);
            if ($existing && (int) $existing->is_active === 1) {
                JE_SB_Database::update($id, array('is_active' => 0));
            } else {
                JE_SB_Database::update($id, array('is_active' => 1));
            }

            wp_safe_redirect(self::base_url());
            exit;
        }

        // Delete (GET).
        if (!empty($_GET['je_sb_action']) && $_GET['je_sb_action'] === 'delete' && !empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            check_admin_referer('je_sb_delete_' . $id);

            JE_SB_Database::delete($id);

            wp_safe_redirect(add_query_arg('message', 'deleted', self::base_url()));
            exit;
        }

        // Duplicate (GET).
        if (!empty($_GET['je_sb_action']) && $_GET['je_sb_action'] === 'duplicate' && !empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            check_admin_referer('je_sb_duplicate_' . $id);

            $new_id = JE_SB_Database::duplicate_from_id($id);
            if (!$new_id) {
                wp_safe_redirect(add_query_arg('message', 'duplicate_failed', self::base_url()));
                exit;
            }

            wp_safe_redirect(
                add_query_arg(
                    array(
                        'page' => self::PAGE_SLUG,
                        'action' => 'edit',
                        'id' => (int) $new_id,
                        'message' => 'duplicated',
                    ),
                    admin_url('admin.php')
                )
            );
            exit;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function sanitize_banner_post()
    {
        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';
        $sale_type = isset($_POST['sale_type']) ? sanitize_key(wp_unslash($_POST['sale_type'])) : 'percent';
        $sale_amount = isset($_POST['sale_amount']) ? (float) wp_unslash($_POST['sale_amount']) : 0.0;
        $cta_label = isset($_POST['cta_label']) ? sanitize_text_field(wp_unslash($_POST['cta_label'])) : 'Shop Now';
        $cta_url = isset($_POST['cta_url']) ? esc_url_raw(wp_unslash($_POST['cta_url'])) : 'https://shop.jazzedge.com/shop';
        $template = isset($_POST['template']) ? (int) $_POST['template'] : 1;
        $display_location = isset($_POST['display_location']) ? sanitize_key(wp_unslash($_POST['display_location'])) : 'shortcode';
        $start = isset($_POST['start_date']) ? self::datetime_local_to_mysql(wp_unslash($_POST['start_date'])) : current_time('mysql');
        $end = isset($_POST['end_date']) ? self::datetime_local_to_mysql(wp_unslash($_POST['end_date'])) : current_time('mysql');
        $is_active = !empty($_POST['is_active']) ? 1 : 0;
        $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field(wp_unslash($_POST['coupon_code'])) : '';
        $coupon_highlight = isset($_POST['coupon_highlight_color']) ? sanitize_hex_color(wp_unslash($_POST['coupon_highlight_color'])) : '';
        if ($coupon_highlight === '') {
            $coupon_highlight = '#F04E23';
        }

        return array(
            'title' => $title,
            'description' => $description,
            'sale_type' => $sale_type,
            'sale_amount' => $sale_amount,
            'cta_label' => $cta_label,
            'cta_url' => $cta_url,
            'template' => $template,
            'display_location' => $display_location,
            'start_date' => $start,
            'end_date' => $end,
            'is_active' => $is_active,
            'coupon_code' => $coupon_code !== '' ? $coupon_code : null,
            'coupon_highlight_color' => $coupon_highlight,
        );
    }

    /**
     * @param string $value
     * @return string
     */
    private static function datetime_local_to_mysql($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return current_time('mysql');
        }

        $value = str_replace('T', ' ', $value);
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $value)) {
            return current_time('mysql');
        }

        $dt = date_create($value, wp_timezone());
        if (!$dt) {
            return current_time('mysql');
        }

        return $dt->format('Y-m-d H:i:s');
    }

    public static function render_screen()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!empty($_GET['je_sb_preview']) && !empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            if ($id && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? '')), 'je_sb_preview_' . $id)) {
                self::render_preview($id);

                return;
            }
        }

        $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : 'list';
        if ($action === 'edit') {
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            self::render_edit($id);

            return;
        }

        self::render_list();
    }

    public static function render_list()
    {
        $rows = JE_SB_Database::get_all();

        if (!empty($_GET['message']) && $_GET['message'] === 'saved') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Banner saved.', 'je-sales-banner') . '</p></div>';
        }
        if (!empty($_GET['message']) && $_GET['message'] === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Banner deleted.', 'je-sales-banner') . '</p></div>';
        }
        if (!empty($_GET['message']) && $_GET['message'] === 'duplicate_failed') {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Could not duplicate the banner.', 'je-sales-banner') . '</p></div>';
        }

        echo '<div class="wrap je-sb-wrap">';
        echo '<h1 class="wp-heading-inline">' . esc_html__('JE Sales Banner', 'je-sales-banner') . '</h1>';
        echo ' <a href="' . esc_url(add_query_arg('action', 'edit', self::base_url())) . '" class="page-title-action">' . esc_html__('Add New Banner', 'je-sales-banner') . '</a>';
        echo '<hr class="wp-header-end" />';

        if (empty($rows)) {
            echo '<p>' . esc_html__('No banners yet.', 'je-sales-banner') . '</p>';
            echo '</div>';

            return;
        }

        echo '<table class="widefat striped je-sb-list-table"><thead><tr>';
        echo '<th>' . esc_html__('Title', 'je-sales-banner') . '</th>';
        echo '<th>' . esc_html__('Sale Amount', 'je-sales-banner') . '</th>';
        echo '<th>' . esc_html__('Template', 'je-sales-banner') . '</th>';
        echo '<th>' . esc_html__('Display Location', 'je-sales-banner') . '</th>';
        echo '<th>' . esc_html__('Start Date', 'je-sales-banner') . '</th>';
        echo '<th>' . esc_html__('End Date', 'je-sales-banner') . '</th>';
        echo '<th>' . esc_html__('Status', 'je-sales-banner') . '</th>';
        echo '<th>' . esc_html__('Actions', 'je-sales-banner') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $tid = (int) $row->id;
            $edit_url = add_query_arg(array('action' => 'edit', 'id' => $tid), self::base_url());
            $preview_url = wp_nonce_url(
                add_query_arg(array('je_sb_preview' => '1', 'id' => $tid), self::base_url()),
                'je_sb_preview_' . $tid
            );
            $toggle_url = wp_nonce_url(
                add_query_arg(array('je_sb_action' => 'toggle', 'id' => $tid), self::base_url()),
                'je_sb_toggle_' . $tid
            );
            $delete_url = wp_nonce_url(
                add_query_arg(array('je_sb_action' => 'delete', 'id' => $tid), self::base_url()),
                'je_sb_delete_' . $tid
            );
            $duplicate_url = wp_nonce_url(
                add_query_arg(array('je_sb_action' => 'duplicate', 'id' => $tid), self::base_url()),
                'je_sb_duplicate_' . $tid
            );

            $active = !empty($row->is_active);
            $status_label = $active
                ? '<span class="je-sb-badge je-sb-badge--active">' . esc_html__('Active', 'je-sales-banner') . '</span>'
                : '<span class="je-sb-badge je-sb-badge--inactive">' . esc_html__('Inactive', 'je-sales-banner') . '</span>';

            $loc = self::display_location_label((string) $row->display_location);

            echo '<tr>';
            echo '<td><strong>' . esc_html((string) $row->title) . '</strong></td>';
            echo '<td>' . esc_html(strip_tags(JE_SB_Frontend::format_sale_amount($row))) . '</td>';
            echo '<td><span class="je-sb-swatch je-sb-swatch--tpl-' . esc_attr((string) (int) $row->template) . '" title="' . esc_attr(sprintf(/* translators: %d template number */ __('Template %d', 'je-sales-banner'), (int) $row->template)) . '"></span></td>';
            echo '<td>' . esc_html($loc) . '</td>';
            echo '<td>' . esc_html(self::format_dt_display((string) $row->start_date)) . '</td>';
            echo '<td>' . esc_html(self::format_dt_display((string) $row->end_date)) . '</td>';
            echo '<td>' . wp_kses(
                $status_label,
                array(
                    'span' => array(
                        'class' => true,
                    ),
                )
            ) . '</td>';
            $shortcode_text = '[sale_banner id="' . $tid . '"]';
            $toggle_icon = $active ? 'dashicons-controls-pause' : 'dashicons-controls-play';

            echo '<td class="je-sb-actions"><div class="je-sb-actions-row">';
            echo '<a href="' . esc_url($edit_url) . '" class="je-sb-action-btn je-sb-action-btn--edit" title="' . esc_attr__('Edit', 'je-sales-banner') . '" aria-label="' . esc_attr__('Edit', 'je-sales-banner') . '"><span class="dashicons dashicons-edit" aria-hidden="true"></span></a>';
            echo '<button type="button" class="je-sb-action-btn je-sb-action-btn--copy-shortcode" data-je-sb-shortcode="' . esc_attr($shortcode_text) . '" title="' . esc_attr__('Copy Shortcode', 'je-sales-banner') . '" aria-label="' . esc_attr__('Copy Shortcode', 'je-sales-banner') . '"><span class="dashicons dashicons-clipboard" aria-hidden="true"></span></button>';
            echo '<a href="' . esc_url($duplicate_url) . '" class="je-sb-action-btn je-sb-action-btn--duplicate" title="' . esc_attr__('Duplicate', 'je-sales-banner') . '" aria-label="' . esc_attr__('Duplicate', 'je-sales-banner') . '"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span></a>';
            echo '<a href="' . esc_url($preview_url) . '" target="_blank" rel="noopener noreferrer" class="je-sb-action-btn je-sb-action-btn--preview" title="' . esc_attr__('Preview', 'je-sales-banner') . '" aria-label="' . esc_attr__('Preview', 'je-sales-banner') . '"><span class="dashicons dashicons-visibility" aria-hidden="true"></span></a>';
            echo '<a href="' . esc_url($toggle_url) . '" class="je-sb-action-btn je-sb-action-btn--toggle' . ($active ? ' is-active' : '') . '" title="' . esc_attr__('Toggle Active', 'je-sales-banner') . '" aria-label="' . esc_attr__('Toggle Active', 'je-sales-banner') . '"><span class="dashicons ' . esc_attr($toggle_icon) . '" aria-hidden="true"></span></a>';
            echo '<a href="' . esc_url($delete_url) . '" class="je-sb-action-btn je-sb-action-btn--delete je-sb-delete-link" data-confirm="' . esc_attr__('Delete this banner?', 'je-sales-banner') . '" title="' . esc_attr__('Delete', 'je-sales-banner') . '" aria-label="' . esc_attr__('Delete', 'je-sales-banner') . '"><span class="dashicons dashicons-trash" aria-hidden="true"></span></a>';
            echo '</div></td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
    }

    /**
     * @param string $loc
     */
    private static function display_location_label($loc)
    {
        switch ($loc) {
            case 'header':
                return __('Header', 'je-sales-banner');
            case 'footer':
                return __('Footer', 'je-sales-banner');
            case 'both':
                return __('Header + Footer', 'je-sales-banner');
            case 'shortcode':
            default:
                return __('Shortcode Only', 'je-sales-banner');
        }
    }

    /**
     * @param string $mysql
     */
    private static function format_dt_display($mysql)
    {
        $ts = strtotime($mysql);
        if ($ts === false) {
            return $mysql;
        }

        return wp_date(get_option('date_format') . ' ' . get_option('time_format'), $ts);
    }

    /**
     * @param int $id 0 = new
     */
    public static function render_edit($id)
    {
        $row = null;
        if ($id > 0) {
            $row = JE_SB_Database::get_row($id);
        }

        $default_end = (new DateTimeImmutable('now', wp_timezone()))->modify('+7 days')->format('Y-m-d H:i:s');

        $defaults = array(
            'title' => '',
            'description' => '',
            'sale_type' => 'percent',
            'sale_amount' => 0,
            'cta_label' => __('Shop Now', 'je-sales-banner'),
            'cta_url' => 'https://shop.jazzedge.com/shop',
            'template' => 1,
            'display_location' => 'shortcode',
            'start_date' => current_time('mysql'),
            'end_date' => $default_end,
            'is_active' => 0,
            'coupon_code' => '',
            'coupon_highlight_color' => '#F04E23',
        );

        $data = $defaults;
        if ($row) {
            foreach ($defaults as $k => $_v) {
                if (isset($row->$k)) {
                    $data[$k] = $row->$k;
                }
            }
            $data['coupon_code'] = $row->coupon_code !== null ? (string) $row->coupon_code : '';
        }

        $start_local = self::mysql_to_datetime_local((string) $data['start_date']);
        $end_local = self::mysql_to_datetime_local((string) $data['end_date']);

        echo '<div class="wrap je-sb-wrap je-sb-edit">';
        if (!empty($_GET['message']) && sanitize_key(wp_unslash($_GET['message'])) === 'duplicated') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Banner duplicated. Update the details below.', 'je-sales-banner') . '</p></div>';
        }
        echo '<h1>' . esc_html($id ? __('Edit Banner', 'je-sales-banner') : __('Add New Banner', 'je-sales-banner')) . '</h1>';
        echo '<form method="post" action="' . esc_url(self::base_url()) . '" class="je-sb-form">';
        wp_nonce_field('je_sb_save_banner', 'je_sb_nonce');
        echo '<input type="hidden" name="je_sb_action" value="save" />';
        echo '<input type="hidden" name="je_sb_id" value="' . esc_attr((string) $id) . '" />';

        echo '<table class="form-table" role="presentation"><tbody>';

        echo '<tr><th><label for="je_sb_title">' . esc_html__('Banner Title', 'je-sales-banner') . '</label></th><td>';
        echo '<input name="title" id="je_sb_title" type="text" class="regular-text je-sb-field" value="' . esc_attr((string) $data['title']) . '" required />';
        echo '</td></tr>';

        if ($id > 0) {
            $shortcode_text = '[sale_banner id="' . (int) $id . '"]';
            echo '<tr><th scope="row">' . esc_html__('Shortcode', 'je-sales-banner') . '</th><td>';
            echo '<div class="je-sb-shortcode-display">';
            echo '<code>' . esc_html($shortcode_text) . '</code>';
            echo '<button type="button" data-je-sb-shortcode="' . esc_attr($shortcode_text) . '" class="je-sb-action-btn" title="' . esc_attr__('Copy Shortcode', 'je-sales-banner') . '" aria-label="' . esc_attr__('Copy Shortcode', 'je-sales-banner') . '">';
            echo '<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>';
            echo '</button>';
            echo '</div>';
            echo '</td></tr>';
        }

        echo '<tr><th><label for="je_sb_description">' . esc_html__('Description', 'je-sales-banner') . '</label></th><td>';
        echo '<textarea name="description" id="je_sb_description" class="large-text je-sb-field" rows="3">' . esc_textarea((string) $data['description']) . '</textarea>';
        echo '</td></tr>';

        echo '<tr><th>' . esc_html__('Sale Type', 'je-sales-banner') . '</th><td>';
        echo '<label><input type="radio" name="sale_type" value="percent" class="je-sb-field" ' . checked($data['sale_type'], 'percent', false) . ' /> ' . esc_html__('Percent Off (%)', 'je-sales-banner') . '</label><br />';
        echo '<label><input type="radio" name="sale_type" value="dollar" class="je-sb-field" ' . checked($data['sale_type'], 'dollar', false) . ' /> ' . esc_html__('Dollar Off ($)', 'je-sales-banner') . '</label>';
        echo '</td></tr>';

        echo '<tr><th><label for="je_sb_amount">' . esc_html__('Sale Amount', 'je-sales-banner') . '</label></th><td>';
        echo '<input name="sale_amount" id="je_sb_amount" type="number" step="0.01" min="0" class="small-text je-sb-field" value="' . esc_attr((string) $data['sale_amount']) . '" />';
        echo '</td></tr>';

        echo '<tr><th><label for="je_sb_cta_label">' . esc_html__('CTA Button Label', 'je-sales-banner') . '</label></th><td>';
        echo '<input name="cta_label" id="je_sb_cta_label" type="text" class="regular-text je-sb-field" value="' . esc_attr((string) $data['cta_label']) . '" />';
        echo '</td></tr>';

        echo '<tr><th><label for="je_sb_cta_url">' . esc_html__('CTA Button URL', 'je-sales-banner') . '</label></th><td>';
        echo '<input name="cta_url" id="je_sb_cta_url" type="url" class="regular-text je-sb-field" value="' . esc_attr((string) $data['cta_url']) . '" />';
        echo '</td></tr>';

        echo '<tr><th><label for="je_sb_start">' . esc_html__('Start Date', 'je-sales-banner') . '</label></th><td>';
        echo '<input name="start_date" id="je_sb_start" type="datetime-local" class="je-sb-field" value="' . esc_attr($start_local) . '" />';
        echo '</td></tr>';

        echo '<tr><th><label for="je_sb_end">' . esc_html__('End Date', 'je-sales-banner') . '</label></th><td>';
        echo '<input name="end_date" id="je_sb_end" type="datetime-local" class="je-sb-field" value="' . esc_attr($end_local) . '" />';
        echo '</td></tr>';

        echo '<tr><th>' . esc_html__('Display Location', 'je-sales-banner') . '</th><td>';
        echo '<label><input type="radio" name="display_location" value="shortcode" class="je-sb-field" ' . checked($data['display_location'], 'shortcode', false) . ' /> ' . esc_html__('Shortcode Only', 'je-sales-banner') . '</label><br />';
        echo '<label><input type="radio" name="display_location" value="header" class="je-sb-field" ' . checked($data['display_location'], 'header', false) . ' /> ' . esc_html__('Header', 'je-sales-banner') . '</label><br />';
        echo '<label><input type="radio" name="display_location" value="footer" class="je-sb-field" ' . checked($data['display_location'], 'footer', false) . ' /> ' . esc_html__('Footer', 'je-sales-banner') . '</label><br />';
        echo '<label><input type="radio" name="display_location" value="both" class="je-sb-field" ' . checked($data['display_location'], 'both', false) . ' /> ' . esc_html__('Header + Footer', 'je-sales-banner') . '</label>';
        echo '</td></tr>';

        echo '<tr><th>' . esc_html__('Active', 'je-sales-banner') . '</th><td>';
        echo '<label><input type="checkbox" name="is_active" value="1" class="je-sb-field" ' . checked(!empty($data['is_active']), true, false) . ' /> ' . esc_html__('Mark this banner as active', 'je-sales-banner') . '</label>';
        echo '</td></tr>';

        echo '<tr><th><label for="je_sb_coupon">' . esc_html__('Coupon Code', 'je-sales-banner') . '</label></th><td>';
        echo '<input name="coupon_code" id="je_sb_coupon" type="text" class="regular-text je-sb-field" value="' . esc_attr((string) $data['coupon_code']) . '" autocomplete="off" />';
        echo '<p class="description">' . esc_html__('Leave blank to hide', 'je-sales-banner') . '</p>';
        echo '</td></tr>';

        echo '<tr><th><label for="je_sb_coupon_color">' . esc_html__('Coupon Highlight Color', 'je-sales-banner') . '</label></th><td>';
        echo '<input name="coupon_highlight_color" id="je_sb_coupon_color" type="color" class="je-sb-field" value="' . esc_attr((string) $data['coupon_highlight_color']) . '" />';
        echo '</td></tr>';

        echo '</tbody></table>';

        self::render_template_picker((int) $data['template']);

        echo '<p class="submit"><button type="submit" class="button button-primary">' . esc_html__('Save Banner', 'je-sales-banner') . '</button></p>';

        echo '<div class="je-sb-live-preview-wrap">';
        echo '<h2>' . esc_html__('Live Preview', 'je-sales-banner') . '</h2>';
        echo '<div id="je-sb-live-preview" class="je-sb-live-preview" data-preview-root></div>';
        echo '</div>';

        echo '</form></div>';
    }

    /**
     * @param string $mysql
     * @return string Y-m-d\TH:i for datetime-local in site TZ
     */
    private static function mysql_to_datetime_local($mysql)
    {
        $dt = date_create($mysql, wp_timezone());
        if (!$dt) {
            return '';
        }

        return $dt->format('Y-m-d\TH:i');
    }

    /**
     * @param int $selected
     */
    private static function render_template_picker($selected)
    {
        echo '<div class="je-sb-template-picker"><h2>' . esc_html__('Template', 'je-sales-banner') . '</h2><p class="description">' . esc_html__('Choose a visual style for the banner bar.', 'je-sales-banner') . '</p>';
        echo '<div class="je-sb-template-grid">';

        for ($i = 1; $i <= 6; $i++) {
            $is_sel = $selected === $i;
            echo '<label class="je-sb-template-card">';
            echo '<input type="radio" name="template" value="' . esc_attr((string) $i) . '" class="je-sb-template-input" ' . checked($is_sel, true, false) . ' />';
            echo '<span class="je-sb-template-card__preview je-sb-template-card__preview--' . esc_attr((string) $i) . '">';
            echo '<span class="je-sb-mini-banner je-sb-mini-banner--' . esc_attr((string) $i) . '">';
            echo '<span class="je-sb-mini-badge">SALE</span>';
            echo '<span class="je-sb-mini-text"><span class="je-sb-mini-title">Spring Sale</span><span class="je-sb-mini-desc">Save on lessons</span></span>';
            echo '<span class="je-sb-mini-meta"><span class="je-sb-mini-amount">20% OFF</span><span class="je-sb-mini-cta">Shop</span></span>';
            echo '</span></span>';
            echo '<span class="je-sb-template-card__name">' . esc_html(sprintf(/* translators: %d */ __('Template %d', 'je-sales-banner'), $i)) . '</span>';
            echo '</label>';
        }

        echo '</div></div>';
    }

    /**
     * @param int $id
     */
    private static function render_preview($id)
    {
        $row = JE_SB_Database::get_row($id);
        echo '<div class="wrap je-sb-preview-wrap"><p><a href="' . esc_url(self::base_url()) . '">&larr; ' . esc_html__('Back to list', 'je-sales-banner') . '</a></p>';

        if (!$row) {
            echo '<p>' . esc_html__('Banner not found.', 'je-sales-banner') . '</p></div>';

            return;
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo JE_SB_Frontend::render_banner_html($row, 'preview', true);
        echo '</div>';
    }
}
