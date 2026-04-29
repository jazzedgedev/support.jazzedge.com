<?php
/**
 * Database table and CRUD for sale banners.
 */

if (!defined('ABSPATH')) {
    exit;
}

class JE_SB_Database
{
    const DB_VERSION = '1.2.0';

    /**
     * @return string
     */
    public static function table_name()
    {
        global $wpdb;

        return $wpdb->prefix . 'je_sale_banners';
    }

    public static function create_table()
    {
        global $wpdb;

        $table = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL DEFAULT '',
            description text NULL,
            sale_type varchar(20) NOT NULL DEFAULT 'percent',
            sale_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            cta_url varchar(500) NOT NULL DEFAULT 'https://shop.jazzedge.com/shop',
            cta_label varchar(100) NOT NULL DEFAULT 'Shop Now',
            template tinyint(3) unsigned NOT NULL DEFAULT 1,
            display_location varchar(50) NOT NULL DEFAULT 'shortcode',
            show_popup tinyint(1) NOT NULL DEFAULT 0,
            popup_delay_seconds smallint unsigned NOT NULL DEFAULT 0,
            start_date datetime NOT NULL,
            end_date datetime NOT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 0,
            coupon_code varchar(100) NULL,
            coupon_highlight_color varchar(20) NOT NULL DEFAULT '#F04E23',
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY is_active (is_active),
            KEY end_date (end_date)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option('je_sb_db_version', self::DB_VERSION);
    }

    /**
     * Add missing columns even when je_sb_db_version already matches (fixes stuck upgrades).
     */
    public static function ensure_schema_columns()
    {
        global $wpdb;
        $table = self::table_name();
        $table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($table_exists !== $table) {
            return;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $cols = $wpdb->get_col("SHOW COLUMNS FROM `{$table}`", 0);
        if (!is_array($cols)) {
            return;
        }

        if (!in_array('show_popup', $cols, true)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN show_popup tinyint(1) NOT NULL DEFAULT 0 AFTER display_location");
            $cols[] = 'show_popup';
        }
        if (!in_array('popup_delay_seconds', $cols, true)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN popup_delay_seconds smallint unsigned NOT NULL DEFAULT 0 AFTER show_popup");
        }
    }

    public static function maybe_upgrade()
    {
        self::ensure_schema_columns();

        $ver = get_option('je_sb_db_version', '');
        if ($ver === self::DB_VERSION) {
            return;
        }

        self::create_table();
        self::ensure_schema_columns();
        update_option('je_sb_db_version', self::DB_VERSION);
    }

    /**
     * @return object|null
     */
    public static function get_row($id)
    {
        global $wpdb;
        $table = self::table_name();

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", (int) $id)
        );
    }

    /**
     * @return array<int, object>
     */
    public static function get_all()
    {
        global $wpdb;
        $table = self::table_name();

        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC, id DESC");
    }

    /**
     * All rows marked active (may be expired — caller filters).
     *
     * @return array<int, object>
     */
    public static function get_all_active_rows()
    {
        global $wpdb;
        $table = self::table_name();

        return $wpdb->get_results("SELECT * FROM {$table} WHERE is_active = 1 ORDER BY id ASC");
    }

    /**
     * @param object $row
     */
    public static function is_within_schedule($row)
    {
        if (empty($row->start_date) || empty($row->end_date)) {
            return false;
        }

        $now = (new DateTimeImmutable('now', wp_timezone()))->getTimestamp();
        $start_dt = date_create((string) $row->start_date, wp_timezone());
        $end_dt = date_create((string) $row->end_date, wp_timezone());
        if (!$start_dt || !$end_dt) {
            return false;
        }

        return $now >= $start_dt->getTimestamp() && $now <= $end_dt->getTimestamp();
    }

    /**
     * @param object $row
     */
    public static function is_expired($row)
    {
        if (empty($row->end_date)) {
            return true;
        }

        $now = (new DateTimeImmutable('now', wp_timezone()))->getTimestamp();
        $end_dt = date_create((string) $row->end_date, wp_timezone());
        if (!$end_dt) {
            return true;
        }

        return $now > $end_dt->getTimestamp();
    }

    /**
     * Clone a banner. The duplicate is always inactive (is_active = 0).
     *
     * @param int $source_id
     * @return int|false New banner ID
     */
    public static function duplicate_from_id($source_id)
    {
        $row = self::get_row((int) $source_id);
        if (!$row) {
            return false;
        }

        $orig_title = isset($row->title) ? (string) $row->title : '';
        $new_title = sprintf(
            /* translators: %s: original banner title */
            __('Copy of %s', 'je-sales-banner'),
            $orig_title
        );

        $coupon_raw = isset($row->coupon_code) ? $row->coupon_code : null;
        $coupon_code = ($coupon_raw !== null && $coupon_raw !== '') ? (string) $coupon_raw : null;

        $data = array(
            'title' => $new_title,
            'description' => isset($row->description) ? (string) $row->description : '',
            'sale_type' => isset($row->sale_type) ? (string) $row->sale_type : 'percent',
            'sale_amount' => isset($row->sale_amount) ? (float) $row->sale_amount : 0.0,
            'cta_url' => isset($row->cta_url) ? (string) $row->cta_url : 'https://shop.jazzedge.com/shop',
            'cta_label' => isset($row->cta_label) ? (string) $row->cta_label : 'Shop Now',
            'template' => isset($row->template) ? (int) $row->template : 1,
            'display_location' => isset($row->display_location) ? (string) $row->display_location : 'shortcode',
            'show_popup' => (isset($row->show_popup) && (int) $row->show_popup === 1) ? 1 : 0,
            'popup_delay_seconds' => isset($row->popup_delay_seconds) ? max(0, min(3600, (int) $row->popup_delay_seconds)) : 0,
            'start_date' => isset($row->start_date) ? (string) $row->start_date : current_time('mysql'),
            'end_date' => isset($row->end_date) ? (string) $row->end_date : current_time('mysql'),
            'is_active' => 0,
            'coupon_code' => $coupon_code,
            'coupon_highlight_color' => isset($row->coupon_highlight_color) && (string) $row->coupon_highlight_color !== ''
                ? (string) $row->coupon_highlight_color
                : '#F04E23',
        );

        return self::insert($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return int|false Insert ID
     */
    public static function insert($data)
    {
        global $wpdb;
        $table = self::table_name();

        $defaults = array(
            'title' => '',
            'description' => '',
            'sale_type' => 'percent',
            'sale_amount' => 0,
            'cta_url' => 'https://shop.jazzedge.com/shop',
            'cta_label' => 'Shop Now',
            'template' => 1,
            'display_location' => 'shortcode',
            'show_popup' => 0,
            'popup_delay_seconds' => 0,
            'start_date' => current_time('mysql'),
            'end_date' => current_time('mysql'),
            'is_active' => 0,
            'coupon_code' => null,
            'coupon_highlight_color' => '#F04E23',
            'created_at' => current_time('mysql'),
        );

        $row = wp_parse_args($data, $defaults);
        $row['sale_type'] = in_array($row['sale_type'], array('percent', 'dollar'), true)
            ? $row['sale_type']
            : 'percent';
        $row['display_location'] = in_array(
            $row['display_location'],
            array('shortcode', 'header', 'footer', 'both'),
            true
        )
            ? $row['display_location']
            : 'shortcode';
        $row['template'] = max(1, min(6, (int) $row['template']));
        $row['is_active'] = !empty($row['is_active']) ? 1 : 0;
        $row['show_popup'] = !empty($row['show_popup']) ? 1 : 0;
        $row['popup_delay_seconds'] = max(0, min(3600, (int) $row['popup_delay_seconds']));

        if ($row['coupon_code'] === '' || $row['coupon_code'] === null) {
            $row['coupon_code'] = null;
        }

        $insert = array(
            'title' => $row['title'],
            'description' => $row['description'],
            'sale_type' => $row['sale_type'],
            'sale_amount' => $row['sale_amount'],
            'cta_url' => $row['cta_url'],
            'cta_label' => $row['cta_label'],
            'template' => $row['template'],
            'display_location' => $row['display_location'],
            'show_popup' => $row['show_popup'],
            'popup_delay_seconds' => $row['popup_delay_seconds'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'is_active' => $row['is_active'],
            'coupon_code' => $row['coupon_code'],
            'coupon_highlight_color' => $row['coupon_highlight_color'],
            'created_at' => $row['created_at'],
        );

        $formats = array('%s', '%s', '%s', '%f', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s');

        $ok = $wpdb->insert($table, $insert, $formats);
        if (!$ok) {
            return false;
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     * @return bool
     */
    public static function update($id, $data)
    {
        global $wpdb;
        $table = self::table_name();
        $id = (int) $id;

        if (isset($data['sale_type'])) {
            $data['sale_type'] = in_array($data['sale_type'], array('percent', 'dollar'), true)
                ? $data['sale_type']
                : 'percent';
        }
        if (isset($data['display_location'])) {
            $data['display_location'] = in_array(
                $data['display_location'],
                array('shortcode', 'header', 'footer', 'both'),
                true
            )
                ? $data['display_location']
                : 'shortcode';
        }
        if (isset($data['template'])) {
            $data['template'] = max(1, min(6, (int) $data['template']));
        }
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = !empty($data['is_active']) ? 1 : 0;
        }
        if (array_key_exists('show_popup', $data)) {
            $data['show_popup'] = !empty($data['show_popup']) ? 1 : 0;
        }
        if (array_key_exists('popup_delay_seconds', $data)) {
            $data['popup_delay_seconds'] = max(0, min(3600, (int) $data['popup_delay_seconds']));
        }
        if (array_key_exists('coupon_code', $data)) {
            if ($data['coupon_code'] === '' || $data['coupon_code'] === null) {
                $data['coupon_code'] = null;
            }
        }

        $formats_map = array(
            'title' => '%s',
            'description' => '%s',
            'sale_type' => '%s',
            'sale_amount' => '%f',
            'cta_url' => '%s',
            'cta_label' => '%s',
            'template' => '%d',
            'display_location' => '%s',
            'show_popup' => '%d',
            'popup_delay_seconds' => '%d',
            'start_date' => '%s',
            'end_date' => '%s',
            'is_active' => '%d',
            'coupon_code' => '%s',
            'coupon_highlight_color' => '%s',
        );

        $update = array();
        $formats = array();
        foreach ($data as $key => $val) {
            if (isset($formats_map[$key])) {
                $update[$key] = $val;
                $formats[] = $formats_map[$key];
            }
        }

        if (empty($update)) {
            return false;
        }

        $wpdb->update($table, $update, array('id' => $id), $formats, array('%d'));

        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        global $wpdb;
        $table = self::table_name();

        return (bool) $wpdb->delete($table, array('id' => (int) $id), array('%d'));
    }

}
