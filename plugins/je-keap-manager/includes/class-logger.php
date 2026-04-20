<?php
if (!defined('ABSPATH')) exit;

class JECM_Logger {

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . 'jecm_cancellation_log';
    }

    public static function create_table() {
        global $wpdb;
        $table   = self::table();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL,
            membership_academy TINYINT(1) NOT NULL DEFAULT 0,
            membership_hsp TINYINT(1) NOT NULL DEFAULT 0,
            membership_other TINYINT(1) NOT NULL DEFAULT 0,
            other_note VARCHAR(50) NULL,
            action VARCHAR(50) NOT NULL DEFAULT 'cancel',
            contact_id BIGINT(20) UNSIGNED NULL,
            contact_name VARCHAR(255) NULL,
            cancel_reason VARCHAR(50) NULL,
            details TEXT NULL,
            keap_success TINYINT(1) NULL COMMENT '1=success, 0=fail, NULL=not attempted',
            fc_success TINYINT(1) NULL COMMENT '1=success, 0=fail, NULL=not attempted',
            processed_by BIGINT(20) UNSIGNED NOT NULL,
            processed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email),
            KEY processed_at (processed_at),
            KEY action (action)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Ensure columns added after initial release exist (dbDelta fallback).
        $existing_columns = $wpdb->get_col("DESCRIBE {$table}", 0);
        if (!is_array($existing_columns)) {
            $existing_columns = array();
        }

        $migrations = array(
            "ALTER TABLE {$table} ADD COLUMN `action` VARCHAR(50) NOT NULL DEFAULT 'cancel' AFTER `other_note`",
            "ALTER TABLE {$table} ADD COLUMN `contact_id` BIGINT(20) UNSIGNED NULL AFTER `action`",
            "ALTER TABLE {$table} ADD COLUMN `contact_name` VARCHAR(255) NULL AFTER `contact_id`",
            "ALTER TABLE {$table} ADD COLUMN `cancel_reason` VARCHAR(50) NULL AFTER `contact_name`",
            "ALTER TABLE {$table} ADD COLUMN `details` TEXT NULL AFTER `cancel_reason`",
            "ALTER TABLE {$table} ADD COLUMN `keap_success` TINYINT(1) NULL COMMENT '1=success, 0=fail, NULL=not attempted' AFTER `details`",
            "ALTER TABLE {$table} ADD COLUMN `fc_success` TINYINT(1) NULL COMMENT '1=success, 0=fail, NULL=not attempted' AFTER `keap_success`",
        );

        $column_map = array(
            'action'        => $migrations[0],
            'contact_id'    => $migrations[1],
            'contact_name'  => $migrations[2],
            'cancel_reason' => $migrations[3],
            'details'       => $migrations[4],
            'keap_success'  => $migrations[5],
            'fc_success'    => $migrations[6],
        );

        foreach ($column_map as $col => $alter_sql) {
            if (!in_array($col, $existing_columns, true)) {
                $wpdb->query($alter_sql);
            }
        }
    }

    /**
     * Backwards-compatible wrapper: full membership cancellation row with action=cancel.
     */
    public static function log($data) {
        self::log_action(array_merge(
            array('action' => 'cancel'),
            is_array($data) ? $data : array()
        ));
    }

    /**
     * @param array $data Keys: action, email, contact_id?, contact_name?, cancel_reason?, details (array|object|string), membership_academy?, membership_hsp?, membership_other?, other_note?, keap_success?, fc_success?
     */
    public static function log_action($data) {
        global $wpdb;

        if (!is_array($data)) {
            return;
        }

        $details = $data['details'] ?? null;
        if (is_array($details) || is_object($details)) {
            $details = wp_json_encode($details);
        } elseif ($details !== null) {
            $details = (string) $details;
        }

        $contact_id = isset($data['contact_id']) && $data['contact_id'] !== '' && $data['contact_id'] !== null
            ? absint($data['contact_id'])
            : null;
        if ($contact_id === 0) {
            $contact_id = null;
        }

        $row = array(
            'email'        => sanitize_email($data['email'] ?? '') ?: '',
            'action'       => sanitize_key($data['action'] ?? 'cancel') ?: 'cancel',
            'processed_by' => (int) get_current_user_id(),
            'processed_at' => current_time('mysql'),
        );
        $formats = array('%s', '%s', '%d', '%s');

        if ($contact_id !== null) {
            $row['contact_id'] = $contact_id;
            $formats[]         = '%d';
        }

        if (isset($data['contact_name']) && $data['contact_name'] !== '' && $data['contact_name'] !== null) {
            $row['contact_name'] = sanitize_text_field($data['contact_name']);
            $formats[]           = '%s';
        }

        if (isset($data['cancel_reason']) && $data['cancel_reason'] !== '' && $data['cancel_reason'] !== null) {
            $row['cancel_reason'] = sanitize_text_field($data['cancel_reason']);
            $formats[]            = '%s';
        }

        if ($details !== null && $details !== '') {
            $row['details'] = $details;
            $formats[]      = '%s';
        }

        if (array_key_exists('keap_success', $data) && $data['keap_success'] !== null && $data['keap_success'] !== '') {
            $row['keap_success'] = (int) $data['keap_success'];
            $formats[]           = '%d';
        }

        if (array_key_exists('fc_success', $data) && $data['fc_success'] !== null && $data['fc_success'] !== '') {
            $row['fc_success'] = (int) $data['fc_success'];
            $formats[]         = '%d';
        }

        if (array_key_exists('membership_academy', $data) && $data['membership_academy'] !== null && $data['membership_academy'] !== '') {
            $row['membership_academy'] = !empty($data['membership_academy']) ? 1 : 0;
            $formats[]                 = '%d';
        }

        if (array_key_exists('membership_hsp', $data) && $data['membership_hsp'] !== null && $data['membership_hsp'] !== '') {
            $row['membership_hsp'] = !empty($data['membership_hsp']) ? 1 : 0;
            $formats[]             = '%d';
        }

        if (array_key_exists('membership_other', $data) && $data['membership_other'] !== null && $data['membership_other'] !== '') {
            $row['membership_other'] = !empty($data['membership_other']) ? 1 : 0;
            $formats[]               = '%d';
        }

        $other_note = isset($data['other_note']) ? substr(sanitize_text_field((string) $data['other_note']), 0, 50) : '';
        if ($other_note !== '') {
            $row['other_note'] = $other_note;
            $formats[]         = '%s';
        }

        $ok = $wpdb->insert(self::table(), $row, $formats);
        if (false === $ok) {
            error_log('JECM log_action failed: ' . $wpdb->last_error);
        }
    }

    /**
     * @param string $filter_action Sanitized action key or empty for all.
     * @param int    $filter_user   User ID or 0 for all.
     */
    public static function get_logs($limit = 50, $offset = 0, $filter_action = '', $filter_user = 0) {
        global $wpdb;
        $where           = array('1=1');
        $prepare_values  = array();

        if ($filter_action !== '') {
            if ($filter_action === 'apply_tag') {
                $where[] = 'l.action IN (%s, %s)';
                $prepare_values[] = 'apply_tag';
                $prepare_values[] = 'add_tag';
            } else {
                $where[] = 'l.action = %s';
                $prepare_values[] = $filter_action;
            }
        }
        if ($filter_user > 0) {
            $where[] = 'l.processed_by = %d';
            $prepare_values[] = $filter_user;
        }

        $where_sql       = implode(' AND ', $where);
        $prepare_values[] = (int) $limit;
        $prepare_values[] = (int) $offset;

        $sql = "SELECT l.*, u.user_login, u.display_name FROM {$wpdb->prefix}jecm_cancellation_log l
             LEFT JOIN {$wpdb->users} u ON u.ID = l.processed_by
             WHERE {$where_sql}
             ORDER BY l.processed_at DESC
             LIMIT %d OFFSET %d";

        return $wpdb->get_results($wpdb->prepare($sql, $prepare_values), ARRAY_A);
    }

    /**
     * @param string $filter_action
     * @param int    $filter_user
     */
    public static function get_total($filter_action = '', $filter_user = 0) {
        global $wpdb;
        $table            = self::table();
        $where            = array('1=1');
        $prepare_values   = array();

        if ($filter_action !== '') {
            if ($filter_action === 'apply_tag') {
                $where[] = 'action IN (%s, %s)';
                $prepare_values[] = 'apply_tag';
                $prepare_values[] = 'add_tag';
            } else {
                $where[] = 'action = %s';
                $prepare_values[] = $filter_action;
            }
        }
        if ($filter_user > 0) {
            $where[] = 'processed_by = %d';
            $prepare_values[] = $filter_user;
        }

        $where_sql = implode(' AND ', $where);
        $sql       = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";

        if (empty($prepare_values)) {
            return (int) $wpdb->get_var($sql);
        }

        return (int) $wpdb->get_var($wpdb->prepare($sql, $prepare_values));
    }

    /**
     * Distinct admin users who appear in the audit log.
     *
     * @return list<array{id: string, display_name: string, user_login: string}>
     */
    public static function get_distinct_users() {
        global $wpdb;
        $table = self::table();

        return $wpdb->get_results(
            "SELECT DISTINCT l.processed_by AS id, u.display_name, u.user_login
             FROM {$table} l
             LEFT JOIN {$wpdb->users} u ON u.ID = l.processed_by
             WHERE l.processed_by IS NOT NULL AND l.processed_by > 0
             ORDER BY u.display_name ASC, u.user_login ASC",
            ARRAY_A
        );
    }
}
