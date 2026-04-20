<?php
if (!defined('ABSPATH')) exit;

/**
 * Follow-up tasks tied to a student email (Find Student / support workflow).
 */
class JECM_Tasks {

    public static function table() {
        global $wpdb;
        return $wpdb->prefix . 'jecm_tasks';
    }

    public static function create_table() {
        global $wpdb;
        $table   = self::table();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            student_email VARCHAR(255) NOT NULL,
            contact_id BIGINT(20) UNSIGNED NULL,
            student_name VARCHAR(255) NOT NULL DEFAULT '',
            notes TEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'open',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME NULL,
            created_by BIGINT(20) UNSIGNED NOT NULL,
            completed_by BIGINT(20) UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY student_email (student_email(191)),
            KEY status_created (status, created_at),
            KEY contact_id (contact_id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * @return int Insert ID or 0 on failure.
     */
    public static function insert_open_task($email, $contact_id, $student_name, $notes) {
        global $wpdb;
        $email = sanitize_email((string) $email);
        if ($email === '') {
            return 0;
        }
        $notes = wp_kses_post((string) $notes);
        if (trim(wp_strip_all_tags($notes)) === '') {
            return 0;
        }
        $cid = (int) $contact_id;
        if ($cid < 1) {
            $cid = null;
        }
        $sn = sanitize_text_field((string) $student_name);
        $uid = (int) get_current_user_id();
        if ($uid < 1) {
            return 0;
        }

        $row = array(
            'student_email' => $email,
            'student_name'  => $sn,
            'notes'         => $notes,
            'status'        => 'open',
            'created_by'    => $uid,
            'created_at'    => current_time('mysql'),
        );
        $formats = array('%s', '%s', '%s', '%s', '%d', '%s');

        if ($cid !== null && $cid > 0) {
            $row = array(
                'student_email' => $email,
                'contact_id'    => $cid,
                'student_name'  => $sn,
                'notes'         => $notes,
                'status'        => 'open',
                'created_by'    => $uid,
                'created_at'    => current_time('mysql'),
            );
            $formats = array('%s', '%d', '%s', '%s', '%s', '%d', '%s');
        }

        $ok = $wpdb->insert(self::table(), $row, $formats);
        if (false === $ok) {
            return 0;
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * Mark task completed and write audit log entry.
     *
     * @return array{success: bool, message?: string}
     */
    public static function complete_task($task_id) {
        global $wpdb;
        $task_id = (int) $task_id;
        if ($task_id < 1) {
            return array('success' => false, 'message' => 'Invalid task.');
        }
        $uid = (int) get_current_user_id();
        if ($uid < 1) {
            return array('success' => false, 'message' => 'Not logged in.');
        }

        $table = self::table();
        $row   = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $task_id), ARRAY_A);
        if (!is_array($row)) {
            return array('success' => false, 'message' => 'Task not found.');
        }
        if (($row['status'] ?? '') !== 'open') {
            return array('success' => false, 'message' => 'Task is not open.');
        }

        $now = current_time('mysql');
        $upd = $wpdb->update(
            $table,
            array(
                'status'        => 'completed',
                'completed_at'  => $now,
                'completed_by'  => $uid,
            ),
            array('id' => $task_id),
            array('%s', '%s', '%d'),
            array('%d')
        );
        if (false === $upd) {
            return array('success' => false, 'message' => 'Could not update task.');
        }

        $email = sanitize_email($row['student_email'] ?? '');
        $cid   = isset($row['contact_id']) && $row['contact_id'] !== null && (int) $row['contact_id'] > 0
            ? (int) $row['contact_id'] : null;
        $sname = trim((string) ($row['student_name'] ?? ''));
        $plain = wp_strip_all_tags((string) ($row['notes'] ?? ''));
        $snippet = function_exists('mb_substr')
            ? mb_substr($plain, 0, 500)
            : substr($plain, 0, 500);

        $log = array(
            'action'  => 'task_complete',
            'email'   => $email,
            'details' => array(
                'task_id'       => $task_id,
                'notes_excerpt' => $snippet,
            ),
        );
        if ($cid !== null) {
            $log['contact_id'] = $cid;
        }
        if ($sname !== '') {
            $log['contact_name'] = $sname;
        }
        JECM_Logger::log_action($log);

        return array('success' => true);
    }

    /**
     * @param string $status 'open', 'completed', or '' for all
     * @return list<array<string, mixed>>
     */
    public static function get_tasks($status = 'open', $limit = 50, $offset = 0) {
        global $wpdb;
        $table  = self::table();
        $limit  = max(1, min(200, (int) $limit));
        $offset = max(0, (int) $offset);
        $status = sanitize_key($status);

        $where = '1=1';
        $args  = array();
        if ($status === 'open' || $status === 'completed') {
            $where .= ' AND t.status = %s';
            $args[] = $status;
        }
        $args[] = $limit;
        $args[] = $offset;

        $sql = "SELECT t.*, uc.display_name AS creator_name, uc.user_login AS creator_login,
                uo.display_name AS completer_name, uo.user_login AS completer_login
                FROM {$table} t
                LEFT JOIN {$wpdb->users} uc ON uc.ID = t.created_by
                LEFT JOIN {$wpdb->users} uo ON uo.ID = t.completed_by
                WHERE {$where}
                ORDER BY t.created_at DESC
                LIMIT %d OFFSET %d";

        return $wpdb->get_results($wpdb->prepare($sql, $args), ARRAY_A) ?: array();
    }

    /**
     * @param string $status 'open', 'completed', or ''
     */
    public static function get_total($status = '') {
        global $wpdb;
        $table  = self::table();
        $status = sanitize_key($status);

        if ($status === 'open' || $status === 'completed') {
            return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE status = %s", $status));
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    /**
     * Open task count (nav badge optional — not used yet).
     */
    public static function count_open() {
        return self::get_total('open');
    }
}
