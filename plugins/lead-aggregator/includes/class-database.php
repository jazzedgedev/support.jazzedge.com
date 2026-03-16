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
        'audit_log',
        'email_logs',
        'webhook_logs',
        'followup_history',
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
            followup_status VARCHAR(40) NOT NULL DEFAULT 'not_set',
            skip_reminders TINYINT(1) NOT NULL DEFAULT 0,
            followup_at DATETIME NULL,
            due_at DATETIME NULL,
            assigned_to BIGINT UNSIGNED NULL,
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

        $tables[] = "CREATE TABLE {$this->table_name('audit_log')} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            workspace_id BIGINT UNSIGNED NOT NULL,
            actor_user_id BIGINT UNSIGNED NOT NULL,
            actor_name VARCHAR(190) NULL,
            actor_email VARCHAR(190) NULL,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(60) NOT NULL,
            entity_id BIGINT UNSIGNED NULL,
            entity_label VARCHAR(190) NULL,
            metadata_json LONGTEXT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY workspace_id (workspace_id),
            KEY created_at (created_at),
            KEY actor_user_id (actor_user_id),
            KEY action (action)
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

        $tables[] = "CREATE TABLE {$this->table_name('followup_history')} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            lead_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            actor_id BIGINT UNSIGNED NOT NULL,
            status_before VARCHAR(40) NULL,
            status_after VARCHAR(40) NULL,
            followup_status_before VARCHAR(40) NULL,
            followup_status_after VARCHAR(40) NULL,
            followup_at DATETIME NULL,
            due_at DATETIME NULL,
            note LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY lead_id (lead_id),
            KEY user_id (user_id),
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
        if (!$this->has_column($leads_table, 'assigned_to')) {
            $this->wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN assigned_to BIGINT UNSIGNED NULL AFTER due_at");
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

        $status_keys = !empty($filters['status_keys']) ? $filters['status_keys'] : null;
        if (!empty($status_keys) && is_array($status_keys)) {
            $placeholders = implode(',', array_fill(0, count($status_keys), '%s'));
            $where .= $this->wpdb->prepare(" AND status IN ($placeholders)", $status_keys);
        } elseif (!empty($filters['status'])) {
            $where .= $this->wpdb->prepare(' AND status = %s', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $this->wpdb->esc_like($filters['search']) . '%';
            $where .= $this->wpdb->prepare(' AND (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s)', $search, $search, $search);
        }

        $outcome_keys = $status_keys ? $status_keys : (in_array($filters['status'] ?? '', array('won', 'lost'), true) ? array($filters['status']) : null);
        if (!empty($filters['outcome_date_from']) && !empty($filters['outcome_date_to']) && !empty($outcome_keys)) {
            $audit_table = $this->table_name('audit_log');
            $like_conditions = array();
            $like_params = array($user_id, 'pipeline_stage_changed', 'lead');
            foreach ($outcome_keys as $key) {
                $like_conditions[] = 'metadata_json LIKE %s';
                $like_params[] = '%"to":"' . $this->wpdb->esc_like($key) . '"%';
            }
            $like_params[] = $filters['outcome_date_from'];
            $like_params[] = $filters['outcome_date_to'];
            $like_clause = implode(' OR ', $like_conditions);
            $where .= $this->wpdb->prepare(
                " AND id IN (SELECT entity_id FROM {$audit_table} WHERE workspace_id = %d AND action = %s AND entity_type = %s AND ({$like_clause}) AND created_at BETWEEN %s AND %s)",
                $like_params
            );
        }

        /* Follow-ups exclusion: exclude won/lost stages so they never appear in Follow-ups tab */
        if (!empty($filters['exclude_status'])) {
            $exclude = is_array($filters['exclude_status']) ? $filters['exclude_status'] : array_map('trim', explode(',', $filters['exclude_status']));
            $exclude = array_filter($exclude);
            if (!empty($exclude)) {
                $placeholders = implode(',', array_fill(0, count($exclude), '%s'));
                $where .= $this->wpdb->prepare(" AND status NOT IN ($placeholders)", $exclude);
            }
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
        $now = current_time('mysql', true);
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
        $data['updated_at'] = current_time('mysql', true);
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

    public function count_leads_by_status($user_id, $status) {
        $this->maybe_create_tables();
        $table = $this->table_name('leads');
        return (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND status = %s",
            (int) $user_id,
            $status
        ));
    }

    public function migrate_leads_to_stage($user_id, $from_status, $to_status) {
        $this->maybe_create_tables();
        $table = $this->table_name('leads');
        $now = current_time('mysql', true);
        return $this->wpdb->query($this->wpdb->prepare(
            "UPDATE {$table} SET status = %s, updated_at = %s WHERE user_id = %d AND status = %s",
            $to_status,
            $now,
            (int) $user_id,
            $from_status
        ));
    }

    public function bulk_update_assigned_to($user_id, $lead_ids, $assigned_to) {
        $this->maybe_create_tables();
        $lead_ids = array_values(array_filter(array_map('intval', (array) $lead_ids)));
        if (empty($lead_ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($lead_ids), '%d'));
        $now = current_time('mysql', true);
        $table = $this->table_name('leads');
        if ($assigned_to === null) {
            $sql = $this->wpdb->prepare(
                "UPDATE {$table} SET assigned_to = NULL, updated_at = %s WHERE user_id = %d AND id IN ($placeholders)",
                array_merge(array($now, (int) $user_id), $lead_ids)
            );
        } else {
            $sql = $this->wpdb->prepare(
                "UPDATE {$table} SET assigned_to = %d, updated_at = %s WHERE user_id = %d AND id IN ($placeholders)",
                array_merge(array((int) $assigned_to, $now, (int) $user_id), $lead_ids)
            );
        }
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

    public function insert_followup_history($lead_id, $user_id, $actor_id, $data) {
        $this->maybe_create_tables();
        $table = $this->table_name('followup_history');
        $this->wpdb->insert($table, array(
            'lead_id' => (int) $lead_id,
            'user_id' => (int) $user_id,
            'actor_id' => (int) $actor_id,
            'status_before' => isset($data['status_before']) ? $data['status_before'] : null,
            'status_after' => isset($data['status_after']) ? $data['status_after'] : null,
            'followup_status_before' => isset($data['followup_status_before']) ? $data['followup_status_before'] : null,
            'followup_status_after' => isset($data['followup_status_after']) ? $data['followup_status_after'] : null,
            'followup_at' => isset($data['followup_at']) ? $data['followup_at'] : null,
            'due_at' => isset($data['due_at']) ? $data['due_at'] : null,
            'note' => isset($data['note']) ? $data['note'] : null,
            'created_at' => current_time('mysql'),
        ));
        return $this->wpdb->insert_id;
    }

    public function get_followup_history($lead_id, $user_id) {
        $this->maybe_create_tables();
        $sql = $this->wpdb->prepare(
            "SELECT fh.*, u.display_name as actor_name FROM {$this->table_name('followup_history')} fh
             LEFT JOIN {$this->wpdb->users} u ON fh.actor_id = u.ID
             WHERE fh.lead_id = %d AND fh.user_id = %d
             ORDER BY fh.created_at DESC",
            $lead_id,
            $user_id
        );
        return $this->wpdb->get_results($sql, ARRAY_A);
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

    /**
     * @param int $user_id
     * @param string $type 'won' or 'lost'
     * @param string $start
     * @param string $end
     * @param array $won_keys Stage keys with type=won (default: ['won'])
     * @param array $lost_keys Stage keys with type=lost (default: ['lost'])
     */
    public function get_won_lost_analytics($user_id, $type, $start, $end, $won_keys = null, $lost_keys = null) {
        $this->maybe_create_tables();
        $leads_table = $this->table_name('leads');
        $audit_table = $this->table_name('audit_log');
        $won_keys = is_array($won_keys) && !empty($won_keys) ? $won_keys : array('won');
        $lost_keys = is_array($lost_keys) && !empty($lost_keys) ? $lost_keys : array('lost');

        $total_leads = (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$leads_table} WHERE user_id = %d",
            $user_id
        ));

        $won_count = 0;
        if (!empty($won_keys)) {
            $won_ph = implode(',', array_fill(0, count($won_keys), '%s'));
            $won_count = (int) $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$leads_table} WHERE user_id = %d AND status IN ({$won_ph})",
                array_merge(array($user_id), $won_keys)
            ));
        }
        $lost_count = 0;
        if (!empty($lost_keys)) {
            $lost_ph = implode(',', array_fill(0, count($lost_keys), '%s'));
            $lost_count = (int) $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$leads_table} WHERE user_id = %d AND status IN ({$lost_ph})",
                array_merge(array($user_id), $lost_keys)
            ));
        }

        $wins_like = array();
        foreach ($won_keys as $k) {
            $wins_like[] = '%"to":"' . $this->wpdb->esc_like($k) . '"%';
        }
        $losses_like = array();
        foreach ($lost_keys as $k) {
            $losses_like[] = '%"to":"' . $this->wpdb->esc_like($k) . '"%';
        }
        $wins_in_period = 0;
        if (!empty($wins_like)) {
            $wins_conds = implode(' OR ', array_fill(0, count($wins_like), 'metadata_json LIKE %s'));
            $wins_in_period = (int) $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$audit_table} WHERE workspace_id = %d AND action = %s AND ({$wins_conds}) AND created_at BETWEEN %s AND %s",
                array_merge(array($user_id, 'pipeline_stage_changed'), $wins_like, array($start, $end))
            ));
        }
        $losses_in_period = 0;
        if (!empty($losses_like)) {
            $loss_conds = implode(' OR ', array_fill(0, count($losses_like), 'metadata_json LIKE %s'));
            $losses_in_period = (int) $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$audit_table} WHERE workspace_id = %d AND action = %s AND ({$loss_conds}) AND created_at BETWEEN %s AND %s",
                array_merge(array($user_id, 'pipeline_stage_changed'), $losses_like, array($start, $end))
            ));
        }

        $target_keys = $type === 'won' ? $won_keys : $lost_keys;
        $like_patterns = $type === 'won' ? $wins_like : $losses_like;
        $avg_days = null;
        if (!empty($target_keys) && !empty($like_patterns)) {
            $sub_conds = implode(' OR ', array_map(function () { return 'al.metadata_json LIKE %s'; }, $like_patterns));
            $subquery = $this->wpdb->prepare(
                "SELECT al.entity_id, MIN(al.created_at) as changed_at FROM {$audit_table} al
                 WHERE al.workspace_id = %d AND al.action = %s AND al.entity_type = %s AND ({$sub_conds})
                 GROUP BY al.entity_id",
                array_merge(array($user_id, 'pipeline_stage_changed', 'lead'), $like_patterns)
            );
            $status_placeholders = implode(',', array_fill(0, count($target_keys), '%s'));
            $avg_sql = "SELECT AVG(DATEDIFF(won_lost.changed_at, l.created_at)) as avg_days
                FROM {$leads_table} l
                INNER JOIN ({$subquery}) won_lost ON l.id = won_lost.entity_id
                WHERE l.user_id = %d AND l.status IN ({$status_placeholders})";
            $result = $this->wpdb->get_var($this->wpdb->prepare($avg_sql, array_merge(array($user_id), $target_keys)));
            if ($result !== null && $result !== '' && is_numeric($result)) {
                $avg_days = (float) $result;
            }
        }

        $top_loss_reason = 'No response';
        $top_loss_pct = 100;
        if ($type === 'lost' && $lost_count > 0 && !empty($lost_keys)) {
            $reason_placeholders = implode(',', array_fill(0, count($lost_keys), '%s'));
            $reasons = $this->wpdb->get_results($this->wpdb->prepare(
                "SELECT custom_1 as reason, COUNT(*) as cnt FROM {$leads_table}
                 WHERE user_id = %d AND status IN ({$reason_placeholders}) AND custom_1 IS NOT NULL AND custom_1 != ''
                 GROUP BY custom_1 ORDER BY cnt DESC LIMIT 1",
                array_merge(array($user_id), $lost_keys)
            ), ARRAY_A);
            if (!empty($reasons) && !empty($reasons[0]['reason'])) {
                $top_loss_reason = $reasons[0]['reason'];
                $top_loss_pct = round((int) $reasons[0]['cnt'] / $lost_count * 100, 1);
            }
        }

        return array(
            'total_leads' => $total_leads,
            'won_count' => $won_count,
            'lost_count' => $lost_count,
            'wins_in_period' => $wins_in_period,
            'losses_in_period' => $losses_in_period,
            'avg_days_to_close' => $avg_days,
            'avg_days_to_loss' => $avg_days,
            'top_loss_reason' => $top_loss_reason,
            'top_loss_reason_pct' => $top_loss_pct,
        );
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
