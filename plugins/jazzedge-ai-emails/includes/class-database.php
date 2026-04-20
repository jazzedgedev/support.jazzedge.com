<?php
/**
 * Database helpers for Jazzedge AI Emails.
 */

if (!defined('ABSPATH')) {
    exit;
}

class JE_Emails_Database {

    /**
     * Table name without prefix.
     */
    const TABLE = 'je_emails';

    /**
     * @return string
     */
    public function table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE;
    }

    public function maybe_create_tables() {
        global $wpdb;
        $table = $this->table_name();
        $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($found !== $table) {
            $this->create_tables();
        }
    }

    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table = $this->table_name();

        $sql = "CREATE TABLE $table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL DEFAULT '',
            subject text,
            body longtext,
            status varchar(20) NOT NULL DEFAULT 'draft',
            subject_thread longtext,
            body_thread longtext,
            prompt_points longtext,
            prompt_about longtext,
            prompt_base_email longtext,
            prompt_design longtext,
            prompt_urls longtext,
            subject_approved tinyint(1) NOT NULL DEFAULT 0,
            body_approved tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY status (status)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Add columns introduced after first release (dbDelta is unreliable for some alters).
     */
    public function maybe_add_columns() {
        global $wpdb;
        $table = $this->table_name();

        $columns = array(
            'prompt_points' => 'LONGTEXT',
            'prompt_about' => 'LONGTEXT',
            'prompt_base_email' => 'LONGTEXT',
            'prompt_design' => 'LONGTEXT',
            'prompt_urls' => 'LONGTEXT',
        );

        foreach ($columns as $column => $type) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- column name from fixed whitelist.
            $exists = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s',
                    DB_NAME,
                    $table,
                    $column
                )
            );
            if (empty($exists)) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- identifiers validated above.
                $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}");
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return int|false Insert ID.
     */
    public function save_email($data) {
        global $wpdb;
        $table = $this->table_name();
        $defaults = array(
            'title' => '',
            'subject' => '',
            'body' => '',
            'status' => 'draft',
            'subject_thread' => '[]',
            'body_thread' => '[]',
            'subject_approved' => 0,
            'body_approved' => 0,
            'prompt_points' => '',
            'prompt_about' => '',
            'prompt_base_email' => '',
            'prompt_design' => '',
            'prompt_urls' => '',
        );
        $row = wp_parse_args($data, $defaults);
        $ok = $wpdb->insert(
            $table,
            array(
                'title' => sanitize_text_field($row['title']),
                'subject' => $row['subject'],
                'body' => $row['body'],
                'status' => in_array($row['status'], array('draft', 'approved'), true) ? $row['status'] : 'draft',
                'subject_thread' => is_string($row['subject_thread']) ? $row['subject_thread'] : wp_json_encode($row['subject_thread']),
                'body_thread' => is_string($row['body_thread']) ? $row['body_thread'] : wp_json_encode($row['body_thread']),
                'subject_approved' => (int) $row['subject_approved'],
                'body_approved' => (int) $row['body_approved'],
                'prompt_points' => is_string($row['prompt_points']) ? $row['prompt_points'] : '',
                'prompt_about' => is_string($row['prompt_about']) ? $row['prompt_about'] : '',
                'prompt_base_email' => is_string($row['prompt_base_email']) ? $row['prompt_base_email'] : '',
                'prompt_design' => is_string($row['prompt_design']) ? $row['prompt_design'] : '',
                'prompt_urls' => is_string($row['prompt_urls']) ? $row['prompt_urls'] : '',
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s')
        );
        if (!$ok) {
            return false;
        }
        return (int) $wpdb->insert_id;
    }

    /**
     * @param int                  $id
     * @param array<string, mixed> $data
     */
    public function update_email($id, $data) {
        global $wpdb;
        $table = $this->table_name();
        $id = absint($id);
        if (!$id) {
            return false;
        }
        $fields = array();
        $formats = array();
        if (array_key_exists('title', $data)) {
            $fields['title'] = sanitize_text_field($data['title']);
            $formats[] = '%s';
        }
        if (array_key_exists('subject', $data)) {
            $fields['subject'] = $data['subject'];
            $formats[] = '%s';
        }
        if (array_key_exists('body', $data)) {
            $fields['body'] = $data['body'];
            $formats[] = '%s';
        }
        if (array_key_exists('status', $data)) {
            $st = $data['status'];
            $fields['status'] = in_array($st, array('draft', 'approved'), true) ? $st : 'draft';
            $formats[] = '%s';
        }
        if (array_key_exists('subject_thread', $data)) {
            $fields['subject_thread'] = is_string($data['subject_thread']) ? $data['subject_thread'] : wp_json_encode($data['subject_thread']);
            $formats[] = '%s';
        }
        if (array_key_exists('body_thread', $data)) {
            $fields['body_thread'] = is_string($data['body_thread']) ? $data['body_thread'] : wp_json_encode($data['body_thread']);
            $formats[] = '%s';
        }
        if (array_key_exists('subject_approved', $data)) {
            $fields['subject_approved'] = (int) $data['subject_approved'];
            $formats[] = '%d';
        }
        if (array_key_exists('body_approved', $data)) {
            $fields['body_approved'] = (int) $data['body_approved'];
            $formats[] = '%d';
        }
        if (empty($fields)) {
            return false;
        }
        return (bool) $wpdb->update($table, $fields, array('id' => $id), $formats, array('%d'));
    }

    /**
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function get_email($id) {
        global $wpdb;
        $table = $this->table_name();
        $id = absint($id);
        if (!$id) {
            return null;
        }
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
        return $row ?: null;
    }

    /**
     * History: saved (approved status) emails only.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_all_emails() {
        global $wpdb;
        $table = $this->table_name();
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, title, subject, body, status, created_at, updated_at,
                    prompt_points, prompt_about, prompt_base_email, prompt_design, prompt_urls
                FROM $table WHERE status = %s ORDER BY created_at DESC",
                'approved'
            ),
            ARRAY_A
        );
        return is_array($results) ? $results : array();
    }

    public function delete_email($id) {
        global $wpdb;
        $table = $this->table_name();
        $id = absint($id);
        if (!$id) {
            return false;
        }
        return (bool) $wpdb->delete($table, array('id' => $id), array('%d'));
    }

    /**
     * Final save: mark email approved for history.
     */
    public function approve_email($id) {
        return $this->update_email(
            absint($id),
            array(
                'status' => 'approved',
            )
        );
    }

    /**
     * Mark subject or body approved in the editor flow.
     *
     * @param int    $id
     * @param string $field subject|body
     */
    public function approve_field($id, $field) {
        $id = absint($id);
        if (!$id) {
            return false;
        }
        if ($field === 'subject') {
            return $this->update_email($id, array('subject_approved' => 1));
        }
        if ($field === 'body') {
            return $this->update_email($id, array('body_approved' => 1));
        }
        return false;
    }
}
