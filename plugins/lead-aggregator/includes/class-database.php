<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Aggregator_Database {
    private $wpdb;
    private $table_prefix;
    private $required_tables = array(
        'leads',
        'notes',
        'tags',
        'lead_tags',
        'stages',
        'webhook_sources',
        'business_profile',
        'activity',
        'email_logs',
        'webhook_logs',
    );

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_prefix = $wpdb->prefix . 'lead_aggregator_';
    }

    public function table_name($suffix) {
        return $this->table_prefix . $suffix;
    }

    public function create_tables() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $this->wpdb->get_charset_collate();

        $tables = array();
        $tables[] = "CREATE TABLE {$this->table_name('leads')} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            source VARCHAR(100) NOT NULL DEFAULT 'manual',
            first_name VARCHAR(100) NULL,
            last_name VARCHAR(100) NULL,
            email VARCHAR(190) NULL,
            phone VARCHAR(50) NULL,
            company VARCHAR(190) NULL,
            address_street VARCHAR(190) NULL,
            address_city VARCHAR(100) NULL,
            address_state VARCHAR(100) NULL,
            address_zip VARCHAR(40) NULL,
            address_country VARCHAR(100) NULL,
            last_actioned DATETIME NULL,
            last_contacted DATETIME NULL,
            custom_1 VARCHAR(190) NULL,
            custom_2 VARCHAR(190) NULL,
            custom_3 VARCHAR(190) NULL,
            custom_4 VARCHAR(190) NULL,
            custom_5 VARCHAR(190) NULL,
            custom_6 VARCHAR(190) NULL,
            custom_7 VARCHAR(190) NULL,
            custom_8 VARCHAR(190) NULL,
            custom_9 VARCHAR(190) NULL,
            custom_10 VARCHAR(190) NULL,
            stage_id BIGINT UNSIGNED NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'open',
            followup_status VARCHAR(40) NOT NULL DEFAULT 'scheduled',
            skip_reminders TINYINT(1) NOT NULL DEFAULT 0,
            followup_at DATETIME NULL,
            due_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY stage_id (stage_id),
            KEY followup_at (followup_at),
            KEY due_at (due_at)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$this->table_name('notes')} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            lead_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            note LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY lead_id (lead_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$this->table_name('tags')} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_tag (user_id, name)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$this->table_name('lead_tags')} (
            lead_id BIGINT UNSIGNED NOT NULL,
            tag_id BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (lead_id, tag_id),
            KEY tag_id (tag_id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$this->table_name('stages')} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(100) NOT NULL,
            position INT NOT NULL DEFAULT 0,
            outcome VARCHAR(20) NOT NULL DEFAULT 'open',
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_stage (user_id, name)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$this->table_name('webhook_sources')} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            source_key VARCHAR(100) NOT NULL,
            shared_secret VARCHAR(190) NOT NULL,
            webhook_token VARCHAR(190) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY source_key (source_key),
            UNIQUE KEY webhook_token (webhook_token)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$this->table_name('business_profile')} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            data LONGTEXT NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$this->table_name('activity')} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            lead_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            action VARCHAR(100) NOT NULL,
            meta LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY lead_id (lead_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$this->table_name('email_logs')} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NULL,
            recipient_email VARCHAR(190) NOT NULL,
            subject VARCHAR(190) NOT NULL,
            message LONGTEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'sent',
            error_message TEXT NULL,
            meta LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$this->table_name('webhook_logs')} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NULL,
            source_key VARCHAR(100) NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'received',
            message TEXT NULL,
            payload LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY source_key (source_key),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        foreach ($tables as $sql) {
            dbDelta($sql);
        }
    }

    public function maybe_create_tables() {
        $missing = false;
        foreach ($this->required_tables as $suffix) {
            $table = $this->table_name($suffix);
            $exists = $this->wpdb->get_var($this->wpdb->prepare('SHOW TABLES LIKE %s', $table));
            if ($exists !== $table) {
                $missing = true;
                break;
            }
        }

        if ($missing) {
            $this->create_tables();
        }

        $leads_table = $this->table_name('leads');
        if (!$this->has_column($leads_table, 'followup_status')) {
            $this->create_tables();
        }
        if (!$this->has_column($leads_table, 'skip_reminders')) {
            $this->create_tables();
        }
        if (!$this->has_column($leads_table, 'address_street')) {
            $this->create_tables();
        }
        if (!$this->has_column($leads_table, 'last_actioned')) {
            $this->create_tables();
        }
        if (!$this->has_column($leads_table, 'last_contacted')) {
            $this->create_tables();
        }
        if (!$this->has_column($leads_table, 'address_street')) {
            $this->create_tables();
        }
        for ($i = 1; $i <= 10; $i += 1) {
            if (!$this->has_column($leads_table, 'custom_' . $i)) {
                $this->create_tables();
                break;
            }
        }

        $stages_table = $this->table_name('stages');
        if (!$this->has_column($stages_table, 'outcome')) {
            $this->create_tables();
        }

        if ($missing && !empty($this->wpdb->last_error)) {
            error_log('Lead Aggregator table creation error: ' . $this->wpdb->last_error);
        }
    }

    private function has_column($table, $column) {
        $result = $this->wpdb->get_var(
            $this->wpdb->prepare("SHOW COLUMNS FROM {$table} LIKE %s", $column)
        );
        return !empty($result);
    }

    public function get_leads($user_id, $filters = array()) {
        $this->maybe_create_tables();
        $where = $this->wpdb->prepare('WHERE user_id = %d', $user_id);
        $params = array();

        if (!empty($filters['stage_id'])) {
            $where .= $this->wpdb->prepare(' AND stage_id = %d', $filters['stage_id']);
        }

        if (!empty($filters['status'])) {
            $where .= $this->wpdb->prepare(' AND status = %s', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $this->wpdb->esc_like($filters['search']) . '%';
            $where .= $this->wpdb->prepare(' AND (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s)', $search, $search, $search);
        }

        $sql = "SELECT * FROM {$this->table_name('leads')} {$where} ORDER BY updated_at DESC";
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function get_lead($lead_id, $user_id) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name('leads')} WHERE id = %d AND user_id = %d", $lead_id, $user_id);
        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    public function insert_lead($data) {
        $this->maybe_create_tables();
        $now = current_time('mysql');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $result = $this->wpdb->insert($this->table_name('leads'), $data);
        if ($result === false) {
            return false;
        }
        return $this->wpdb->insert_id;
    }

    public function update_lead($lead_id, $user_id, $data) {
        $this->maybe_create_tables();
        $data['updated_at'] = current_time('mysql');
        return $this->wpdb->update(
            $this->table_name('leads'),
            $data,
            array('id' => $lead_id, 'user_id' => $user_id)
        );
    }

    public function delete_lead($lead_id, $user_id) {
        $this->maybe_create_tables();
        return $this->wpdb->delete($this->table_name('leads'), array('id' => $lead_id, 'user_id' => $user_id));
    }

    public function delete_leads_by_ids($user_id, $lead_ids) {
        $this->maybe_create_tables();
        $lead_ids = array_values(array_filter(array_map('intval', (array) $lead_ids)));
        if (empty($lead_ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($lead_ids), '%d'));
        $sql = $this->wpdb->prepare(
            "DELETE FROM {$this->table_name('leads')} WHERE user_id = %d AND id IN ($placeholders)",
            array_merge(array((int) $user_id), $lead_ids)
        );
        return $this->wpdb->query($sql);
    }

    public function get_notes($lead_id, $user_id) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name('notes')} WHERE lead_id = %d AND user_id = %d ORDER BY created_at DESC", $lead_id, $user_id);
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function add_note($lead_id, $user_id, $note) {
        $this->maybe_create_tables();
        $this->wpdb->insert($this->table_name('notes'), array(
            'lead_id' => $lead_id,
            'user_id' => $user_id,
            'note' => $note,
            'created_at' => current_time('mysql'),
        ));
        return $this->wpdb->insert_id;
    }

    public function update_note($note_id, $user_id, $note) {
        $this->maybe_create_tables();
        return $this->wpdb->update($this->table_name('notes'), array(
            'note' => $note,
        ), array(
            'id' => $note_id,
            'user_id' => $user_id,
        ));
    }

    public function delete_note($note_id, $user_id) {
        $this->maybe_create_tables();
        return $this->wpdb->delete($this->table_name('notes'), array(
            'id' => $note_id,
            'user_id' => $user_id,
        ));
    }

    public function get_tags($user_id) {
        $this->maybe_create_tables();
        $tags_table = $this->table_name('tags');
        $lead_tags_table = $this->table_name('lead_tags');
        $sql = $this->wpdb->prepare(
            "SELECT {$tags_table}.*, COUNT({$lead_tags_table}.tag_id) as lead_count
             FROM {$tags_table}
             LEFT JOIN {$lead_tags_table} ON {$tags_table}.id = {$lead_tags_table}.tag_id
             WHERE {$tags_table}.user_id = %d
             GROUP BY {$tags_table}.id
             ORDER BY {$tags_table}.name ASC",
            $user_id
        );
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function create_tag($user_id, $name) {
        $this->maybe_create_tables();
        $this->wpdb->insert($this->table_name('tags'), array(
            'user_id' => $user_id,
            'name' => $name,
            'created_at' => current_time('mysql'),
        ));
        return $this->wpdb->insert_id;
    }

    public function delete_tag($user_id, $tag_id) {
        $this->maybe_create_tables();
        $this->wpdb->delete($this->table_name('lead_tags'), array('tag_id' => $tag_id));
        return $this->wpdb->delete($this->table_name('tags'), array('id' => $tag_id, 'user_id' => $user_id));
    }

    public function update_tag($user_id, $tag_id, $name) {
        $this->maybe_create_tables();
        return $this->wpdb->update(
            $this->table_name('tags'),
            array('name' => $name),
            array('id' => $tag_id, 'user_id' => $user_id)
        );
    }

    public function set_lead_tags($lead_id, $tag_ids) {
        $this->maybe_create_tables();
        $this->wpdb->delete($this->table_name('lead_tags'), array('lead_id' => $lead_id));
        foreach ($tag_ids as $tag_id) {
            $this->wpdb->insert($this->table_name('lead_tags'), array(
                'lead_id' => $lead_id,
                'tag_id' => $tag_id,
            ));
        }
    }

    public function get_lead_tags($lead_id) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare("SELECT tag_id FROM {$this->table_name('lead_tags')} WHERE lead_id = %d", $lead_id);
        return $this->wpdb->get_col($sql);
    }

    public function get_leads_by_tag($user_id, $tag_id) {
        $this->maybe_create_tables();
        $leads_table = $this->table_name('leads');
        $lead_tags_table = $this->table_name('lead_tags');
        $sql = $this->wpdb->prepare(
            "SELECT {$leads_table}.*
             FROM {$leads_table}
             INNER JOIN {$lead_tags_table} ON {$leads_table}.id = {$lead_tags_table}.lead_id
             WHERE {$leads_table}.user_id = %d AND {$lead_tags_table}.tag_id = %d
             ORDER BY {$leads_table}.updated_at DESC",
            $user_id,
            $tag_id
        );
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function get_stages($user_id) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name('stages')} WHERE user_id = %d ORDER BY position ASC, name ASC", $user_id);
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function create_stage($user_id, $name, $position = 0, $outcome = 'open') {
        $this->maybe_create_tables();
        $this->wpdb->insert($this->table_name('stages'), array(
            'user_id' => $user_id,
            'name' => $name,
            'position' => $position,
            'outcome' => $outcome,
            'created_at' => current_time('mysql'),
        ));
        return $this->wpdb->insert_id;
    }

    public function update_stage($user_id, $stage_id, $data) {
        $this->maybe_create_tables();
        return $this->wpdb->update($this->table_name('stages'), $data, array('id' => $stage_id, 'user_id' => $user_id));
    }

    public function delete_stage($user_id, $stage_id) {
        $this->maybe_create_tables();
        return $this->wpdb->delete($this->table_name('stages'), array('id' => $stage_id, 'user_id' => $user_id));
    }

    public function get_webhook_source($source_key) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name('webhook_sources')} WHERE source_key = %s AND is_active = 1", $source_key);
        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    public function get_webhook_source_by_token($token) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name('webhook_sources')} WHERE webhook_token = %s AND is_active = 1", $token);
        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    public function get_webhook_sources() {
        $this->maybe_create_tables();
        $sql = "SELECT * FROM {$this->table_name('webhook_sources')} ORDER BY created_at DESC";
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function get_webhook_sources_by_user($user_id) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name('webhook_sources')} WHERE user_id = %d ORDER BY created_at DESC", $user_id);
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function get_webhook_source_by_id($source_id, $user_id) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name('webhook_sources')} WHERE id = %d AND user_id = %d", $source_id, $user_id);
        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    public function create_webhook_source($user_id, $source_key, $shared_secret, $is_active = 1, $webhook_token = '') {
        $this->maybe_create_tables();
        if (!$webhook_token) {
            $webhook_token = wp_generate_password(24, false, false);
        }
        if (!$shared_secret) {
            $shared_secret = wp_generate_password(24, false, false);
        }
        $this->wpdb->insert($this->table_name('webhook_sources'), array(
            'user_id' => $user_id,
            'source_key' => $source_key,
            'shared_secret' => $shared_secret,
            'webhook_token' => $webhook_token,
            'is_active' => $is_active ? 1 : 0,
            'created_at' => current_time('mysql'),
        ));
        return $this->wpdb->insert_id;
    }

    public function delete_webhook_source($source_id) {
        $this->maybe_create_tables();
        return $this->wpdb->delete($this->table_name('webhook_sources'), array('id' => $source_id));
    }

    public function update_webhook_source($user_id, $source_id, $data) {
        $this->maybe_create_tables();
        return $this->wpdb->update(
            $this->table_name('webhook_sources'),
            $data,
            array('id' => $source_id, 'user_id' => (int) $user_id)
        );
    }

    public function get_business_profile($user_id) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name('business_profile')} WHERE user_id = %d", $user_id);
        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    public function save_business_profile($user_id, $data) {
        $this->maybe_create_tables();
        $existing = $this->get_business_profile($user_id);
        $payload = array(
            'user_id' => $user_id,
            'data' => wp_json_encode($data),
            'updated_at' => current_time('mysql'),
        );

        if ($existing) {
            return $this->wpdb->update($this->table_name('business_profile'), $payload, array('id' => $existing['id']));
        }

        $this->wpdb->insert($this->table_name('business_profile'), $payload);
        return $this->wpdb->insert_id;
    }

    public function log_activity($lead_id, $user_id, $action, $meta = null) {
        $this->maybe_create_tables();
        $this->wpdb->insert($this->table_name('activity'), array(
            'lead_id' => $lead_id,
            'user_id' => $user_id,
            'action' => $action,
            'meta' => $meta ? wp_json_encode($meta) : null,
            'created_at' => current_time('mysql'),
        ));
    }

    public function log_email($data) {
        $this->maybe_create_tables();
        $recipient = isset($data['recipient_email']) ? $data['recipient_email'] : '';
        $masked_recipient = $recipient ? $this->mask_email($recipient) : '';
        $message = isset($data['message']) ? $data['message'] : '';
        if ($message) {
            $message = '[redacted]';
        }
        $payload = array(
            'user_id' => isset($data['user_id']) ? (int) $data['user_id'] : null,
            'recipient_email' => $masked_recipient,
            'subject' => isset($data['subject']) ? $data['subject'] : '',
            'message' => $message,
            'status' => isset($data['status']) ? $data['status'] : 'sent',
            'error_message' => isset($data['error_message']) ? $data['error_message'] : null,
            'meta' => !empty($data['meta']) ? wp_json_encode($data['meta']) : null,
            'created_at' => current_time('mysql'),
        );
        $this->wpdb->insert($this->table_name('email_logs'), $payload);
        return $this->wpdb->insert_id;
    }

    private function mask_email($email) {
        $email = (string) $email;
        if (!strpos($email, '@')) {
            return $email;
        }
        list($local, $domain) = explode('@', $email, 2);
        if ($local === '') {
            return $email;
        }
        $visible = substr($local, 0, 2);
        return $visible . '***@' . $domain;
    }

    public function get_email_logs($limit = 100) {
        $this->maybe_create_tables();
        $limit = max(1, (int) $limit);
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name('email_logs')} ORDER BY created_at DESC LIMIT %d",
            $limit
        );
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function clear_email_logs() {
        $this->maybe_create_tables();
        return $this->wpdb->query("TRUNCATE TABLE {$this->table_name('email_logs')}");
    }

    public function log_webhook($data) {
        $this->maybe_create_tables();
        $payload = isset($data['payload']) ? $data['payload'] : null;
        if (is_array($payload)) {
            $payload = wp_json_encode($payload);
        }
        $this->wpdb->insert($this->table_name('webhook_logs'), array(
            'user_id' => isset($data['user_id']) ? (int) $data['user_id'] : null,
            'source_key' => isset($data['source_key']) ? $data['source_key'] : null,
            'status' => isset($data['status']) ? $data['status'] : 'received',
            'message' => isset($data['message']) ? $data['message'] : null,
            'payload' => $payload,
            'created_at' => current_time('mysql'),
        ));
        return $this->wpdb->insert_id;
    }

    public function get_webhook_logs($limit = 200) {
        $this->maybe_create_tables();
        $limit = max(1, (int) $limit);
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name('webhook_logs')} ORDER BY created_at DESC LIMIT %d",
            $limit
        );
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function clear_webhook_logs() {
        $this->maybe_create_tables();
        return $this->wpdb->query("TRUNCATE TABLE {$this->table_name('webhook_logs')}");
    }

    public function count_leads($user_id) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name('leads')} WHERE user_id = %d", $user_id);
        return (int) $this->wpdb->get_var($sql);
    }

    public function get_due_leads($cutoff) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name('leads')} WHERE skip_reminders = 0 AND ((followup_at IS NOT NULL AND followup_at <= %s) OR (due_at IS NOT NULL AND due_at <= %s))",
            $cutoff,
            $cutoff
        );
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function get_scheduled_followups() {
        $this->maybe_create_tables();
        $sql = "SELECT * FROM {$this->table_name('leads')} WHERE followup_at IS NOT NULL OR due_at IS NOT NULL ORDER BY followup_at ASC, due_at ASC";
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function get_last_error() {
        return $this->wpdb->last_error;
    }

    public function get_lead_users() {
        $sql = "SELECT DISTINCT user_id FROM {$this->table_name('leads')} ORDER BY user_id DESC";
        $user_ids = $this->wpdb->get_col($sql);
        if (empty($user_ids)) {
            return array();
        }

        $placeholders = implode(',', array_fill(0, count($user_ids), '%d'));
        $prepared = $this->wpdb->prepare(
            "SELECT ID as user_id, display_name, user_email FROM {$this->wpdb->users} WHERE ID IN ($placeholders) ORDER BY display_name ASC",
            $user_ids
        );

        return $this->wpdb->get_results($prepared, ARRAY_A);
    }
}
