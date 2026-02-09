<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Aggregator_Audit {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function log($workspace_id, $actor_user_id, $action, $entity_type, $entity_id = null, $entity_label = null, $metadata = array()) {
        if (!$workspace_id || !$actor_user_id || !$action || !$entity_type) {
            return false;
        }

        $user = get_user_by('id', $actor_user_id);
        $actor_name = $user ? $user->display_name : '';
        $actor_email = $user ? $user->user_email : '';

        $redacted = $this->redact_metadata($metadata);
        $payload = !empty($redacted) ? wp_json_encode($redacted) : null;

        $data = array(
            'workspace_id' => (int) $workspace_id,
            'actor_user_id' => (int) $actor_user_id,
            'actor_name' => $actor_name ? sanitize_text_field($actor_name) : null,
            'actor_email' => $actor_email ? sanitize_email($actor_email) : null,
            'action' => sanitize_key($action),
            'entity_type' => sanitize_key($entity_type),
            'entity_id' => $entity_id ? (int) $entity_id : null,
            'entity_label' => $entity_label ? sanitize_text_field($entity_label) : null,
            'metadata_json' => $payload,
            'ip_address' => $this->get_ip_address(),
            'user_agent' => $this->get_user_agent(),
            'created_at' => current_time('mysql', true),
        );

        global $wpdb;
        $table = $this->database->table_name('audit_log');
        $result = $wpdb->insert($table, $data);
        return $result !== false;
    }

    public function resolve_workspace_id($user_id) {
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            return 0;
        }
        $manager_id = (int) get_user_meta($user_id, 'lead_aggregator_manager_id', true);
        return $manager_id > 0 ? $manager_id : $user_id;
    }

    private function get_ip_address() {
        $ip = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR'])));
            $ip = trim($parts[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }
        return $ip ?: null;
    }

    private function get_user_agent() {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return null;
        }
        return sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
    }

    private function redact_metadata($data) {
        if (!is_array($data)) {
            return array();
        }
        $blocked = array('password', 'pass', 'secret', 'token', 'api_key', 'authorization', 'auth', 'webhook', 'stripe');
        $redacted = array();
        foreach ($data as $key => $value) {
            $key_string = strtolower((string) $key);
            $should_redact = false;
            foreach ($blocked as $needle) {
                if (strpos($key_string, $needle) !== false) {
                    $should_redact = true;
                    break;
                }
            }
            if ($should_redact) {
                $redacted[$key] = '[redacted]';
                continue;
            }
            if (is_array($value)) {
                $redacted[$key] = $this->redact_metadata($value);
                continue;
            }
            if (is_object($value)) {
                $redacted[$key] = $this->redact_metadata((array) $value);
                continue;
            }
            $redacted[$key] = $value;
        }
        return $redacted;
    }
}
