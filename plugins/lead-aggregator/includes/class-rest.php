<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Aggregator_REST {
    private $database;
    private $permissions;
    private $billing;

    public function __construct($database, $permissions, $billing) {
        $this->database = $database;
        $this->permissions = $permissions;
        $this->billing = $billing;

        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route('lead-aggregator/v1', '/webhooks/(?P<source>[a-zA-Z0-9_-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('lead-aggregator/v1', '/webhooks/user/(?P<token>[a-zA-Z0-9_-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_user_webhook'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('lead-aggregator/v1', '/leads', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_leads'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/leads', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_lead'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/leads/bulk-delete', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_delete_leads'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/leads/(?P<id>\\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_lead'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/leads/(?P<id>\\d+)/export', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_lead'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/leads/(?P<id>\\d+)/calendar', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_lead_calendar'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/leads/(?P<id>\\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_lead'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/leads/(?P<id>\\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_lead'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/leads/(?P<id>\\d+)/notes', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_notes'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/leads/(?P<id>\\d+)/notes', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_note'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/leads/(?P<id>\\d+)/notes/(?P<note_id>\\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_note'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/leads/(?P<id>\\d+)/notes/(?P<note_id>\\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_note'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/tags', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_tags'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/tags', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_tag'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/tags/(?P<id>\\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_tag'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/tags/(?P<id>\\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_tag'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/tags/(?P<id>\\d+)/leads', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_tag_leads'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/stages', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_stages'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/stages', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_stage'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/stages/(?P<id>\\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_stage'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/stages/(?P<id>\\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_stage'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/business-profile', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_business_profile'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/business-profile', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_business_profile'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/business-profile/scrape', array(
            'methods' => 'POST',
            'callback' => array($this, 'scrape_business_profile'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/export', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_leads'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/calendar', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_calendar'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/webhook-sources', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_webhook_sources'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/webhook-sources', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_webhook_source'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/webhook-sources/(?P<id>\\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_webhook_source'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/webhook-sources/(?P<id>\\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_webhook_source'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/billing/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_billing_status'),
            'permission_callback' => array($this, 'check_login'),
        ));

        register_rest_route('lead-aggregator/v1', '/billing/plans', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_billing_plans'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('lead-aggregator/v1', '/billing/checkout', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_checkout_session'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('lead-aggregator/v1', '/billing/portal', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_portal_session'),
            'permission_callback' => array($this, 'check_login'),
        ));

        register_rest_route('lead-aggregator/v1', '/billing/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_billing_webhook'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('lead-aggregator/v1', '/notifications/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_notification_settings'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/notifications/settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_notification_settings'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/custom-fields', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_custom_fields'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/custom-fields', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_custom_fields'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/me', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_me'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/team', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_team'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/team', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_team_member'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/team/(?P<id>\\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_team_member'),
            'permission_callback' => array($this, 'check_user_write'),
        ));
    }

    private function get_request_params($request) {
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }
        return is_array($params) ? $params : array();
    }

    public function check_user() {
        if ($this->permissions->require_user()) {
            return true;
        }

        $error = $this->permissions->get_last_error();
        $message = isset($error['message']) ? $error['message'] : 'Unauthorized';
        $status = isset($error['status']) ? (int) $error['status'] : 403;
        $code = isset($error['code']) ? $error['code'] : 'unauthorized';

        return new WP_Error($code, $message, array('status' => $status));
    }

    public function check_user_read() {
        if ($this->permissions->require_user()) {
            return true;
        }

        $error = $this->permissions->get_last_error();
        $message = isset($error['message']) ? $error['message'] : 'Unauthorized';
        $status = isset($error['status']) ? (int) $error['status'] : 403;
        $code = isset($error['code']) ? $error['code'] : 'unauthorized';

        return new WP_Error($code, $message, array('status' => $status));
    }

    public function check_user_write() {
        if ($this->permissions->require_user()) {
            $user_id = get_current_user_id();
            if ($this->permissions->is_read_only($user_id)) {
                return new WP_Error('read_only', 'Read-only access. Please contact your manager to make changes.', array('status' => 403));
            }
            return true;
        }

        $error = $this->permissions->get_last_error();
        $message = isset($error['message']) ? $error['message'] : 'Unauthorized';
        $status = isset($error['status']) ? (int) $error['status'] : 403;
        $code = isset($error['code']) ? $error['code'] : 'unauthorized';

        return new WP_Error($code, $message, array('status' => $status));
    }

    public function check_login() {
        if ($this->permissions->require_login()) {
            return true;
        }

        $error = $this->permissions->get_last_error();
        $message = isset($error['message']) ? $error['message'] : 'Unauthorized';
        $status = isset($error['status']) ? (int) $error['status'] : 403;
        $code = isset($error['code']) ? $error['code'] : 'unauthorized';

        return new WP_Error($code, $message, array('status' => $status));
    }

    public function handle_webhook($request) {
        $source = sanitize_key($request['source']);
        if ($this->is_rate_limited('webhook:source:' . $source, 120, 60)) {
            return new WP_Error('rate_limited', 'Too many requests. Please try again later.', array('status' => 429));
        }
        $secret = $request->get_header('X-Lead-Aggregator-Secret');
        if (!$secret) {
            $secret = $request->get_param('secret');
        }

        $source_record = $this->database->get_webhook_source($source);
        if (!$source_record) {
            $this->maybe_log_webhook(null, $source, 'failed', 'Invalid webhook secret', $this->get_request_params($request));
            return new WP_Error('invalid_secret', 'Invalid webhook secret', array('status' => 401));
        }

        $required_secret = isset($source_record['shared_secret']) ? (string) $source_record['shared_secret'] : '';
        if (!$required_secret) {
            $this->maybe_log_webhook(null, $source, 'failed', 'Shared secret required', $this->get_request_params($request));
            return new WP_Error('secret_required', 'Shared secret not configured. Generate one in settings.', array('status' => 401));
        }
        if (!$secret || $required_secret !== $secret) {
            $this->maybe_log_webhook(null, $source, 'failed', 'Invalid webhook secret', $this->get_request_params($request));
            return new WP_Error('invalid_secret', 'Invalid webhook secret', array('status' => 401));
        }

        $payload = $request->get_json_params();
        if (empty($payload)) {
            $payload = $request->get_body_params();
        }

        $user_id = (int) $source_record['user_id'];
        if (!$this->permissions->can_create_lead($user_id)) {
            $this->maybe_log_webhook($user_id, $source, 'failed', 'Contact limit reached', $payload);
            return new WP_Error('limit_reached', 'Contact limit reached', array('status' => 403));
        }

        $lead_source = 'webhook';
        if (isset($payload['source']) && $payload['source'] !== '') {
            $lead_source = sanitize_text_field($payload['source']);
        }

        $custom_fields = $this->extract_custom_fields($payload);
        $address = $this->extract_address_fields($payload);
        $lead_id = $this->database->insert_lead(array_merge(array(
            'user_id' => $user_id,
            'source' => $lead_source,
            'first_name' => isset($payload['first_name']) ? sanitize_text_field($payload['first_name']) : '',
            'last_name' => isset($payload['last_name']) ? sanitize_text_field($payload['last_name']) : '',
            'email' => isset($payload['email']) ? sanitize_email($payload['email']) : '',
            'phone' => isset($payload['phone']) ? sanitize_text_field($payload['phone']) : '',
            'company' => isset($payload['company']) ? sanitize_text_field($payload['company']) : '',
            'status' => 'open',
            'stage_id' => isset($payload['stage_id']) ? (int) $payload['stage_id'] : null,
            'followup_status' => isset($payload['followup_status']) ? sanitize_text_field($payload['followup_status']) : 'scheduled',
            'skip_reminders' => !empty($payload['skip_reminders']) ? 1 : 0,
            'followup_at' => !empty($payload['followup_at']) ? sanitize_text_field($payload['followup_at']) : null,
            'due_at' => !empty($payload['due_at']) ? sanitize_text_field($payload['due_at']) : null,
            'last_actioned' => !empty($payload['last_actioned']) ? sanitize_text_field($payload['last_actioned']) : null,
            'last_contacted' => !empty($payload['last_contacted']) ? sanitize_text_field($payload['last_contacted']) : null,
        ), $custom_fields, $address));
        if (!$lead_id) {
            $this->maybe_log_webhook($user_id, $source, 'failed', $this->database->get_last_error() ? $this->database->get_last_error() : 'Unable to create lead', $payload);
            return new WP_Error(
                'create_failed',
                $this->database->get_last_error() ? $this->database->get_last_error() : 'Unable to create lead',
                array('status' => 500)
            );
        }

        $this->database->log_activity($lead_id, $user_id, 'webhook_created', array('source' => $lead_source));
        $this->apply_webhook_tags($lead_id, $payload);
        $this->maybe_log_webhook($user_id, $source, 'success', 'Lead created', $payload);

        return rest_ensure_response(array('success' => true, 'lead_id' => $lead_id));
    }

    public function handle_user_webhook($request) {
        $token = sanitize_text_field($request['token']);
        if ($this->is_rate_limited('webhook:token:' . $token, 120, 60)) {
            return new WP_Error('rate_limited', 'Too many requests. Please try again later.', array('status' => 429));
        }
        $source_record = $this->database->get_webhook_source_by_token($token);
        if (!$source_record) {
            $this->maybe_log_webhook(null, 'token:' . $token, 'failed', 'Invalid webhook token', $this->get_request_params($request));
            return new WP_Error('invalid_token', 'Invalid webhook token', array('status' => 401));
        }

        $secret = $request->get_header('X-Lead-Aggregator-Secret');
        $required_secret = isset($source_record['shared_secret']) ? (string) $source_record['shared_secret'] : '';
        if (!$required_secret) {
            $this->maybe_log_webhook((int) $source_record['user_id'], $source_record['source_key'], 'failed', 'Shared secret required', $this->get_request_params($request));
            return new WP_Error('secret_required', 'Shared secret not configured. Generate one in settings.', array('status' => 401));
        }
        if (!$secret || $secret !== $required_secret) {
            $this->maybe_log_webhook((int) $source_record['user_id'], $source_record['source_key'], 'failed', 'Invalid webhook secret', $this->get_request_params($request));
            return new WP_Error('invalid_secret', 'Invalid webhook secret', array('status' => 401));
        }

        $payload = $request->get_json_params();
        if (empty($payload)) {
            $payload = $request->get_body_params();
        }

        $user_id = (int) $source_record['user_id'];
        if (!$this->permissions->can_create_lead($user_id)) {
            $this->maybe_log_webhook($user_id, $source_record['source_key'], 'failed', 'Contact limit reached', $payload);
            return new WP_Error('limit_reached', 'Contact limit reached', array('status' => 403));
        }

        $lead_source = 'webhook';
        if (isset($payload['source']) && $payload['source'] !== '') {
            $lead_source = sanitize_text_field($payload['source']);
        }

        $custom_fields = $this->extract_custom_fields($payload);
        $address = $this->extract_address_fields($payload);
        $lead_id = $this->database->insert_lead(array_merge(array(
            'user_id' => $user_id,
            'source' => $lead_source,
            'first_name' => isset($payload['first_name']) ? sanitize_text_field($payload['first_name']) : '',
            'last_name' => isset($payload['last_name']) ? sanitize_text_field($payload['last_name']) : '',
            'email' => isset($payload['email']) ? sanitize_email($payload['email']) : '',
            'phone' => isset($payload['phone']) ? sanitize_text_field($payload['phone']) : '',
            'company' => isset($payload['company']) ? sanitize_text_field($payload['company']) : '',
            'status' => 'open',
            'stage_id' => isset($payload['stage_id']) ? (int) $payload['stage_id'] : null,
            'followup_status' => isset($payload['followup_status']) ? sanitize_text_field($payload['followup_status']) : 'scheduled',
            'skip_reminders' => !empty($payload['skip_reminders']) ? 1 : 0,
            'followup_at' => !empty($payload['followup_at']) ? sanitize_text_field($payload['followup_at']) : null,
            'due_at' => !empty($payload['due_at']) ? sanitize_text_field($payload['due_at']) : null,
            'last_actioned' => !empty($payload['last_actioned']) ? sanitize_text_field($payload['last_actioned']) : null,
            'last_contacted' => !empty($payload['last_contacted']) ? sanitize_text_field($payload['last_contacted']) : null,
        ), $custom_fields, $address));
        if (!$lead_id) {
            $this->maybe_log_webhook($user_id, $source_record['source_key'], 'failed', $this->database->get_last_error() ? $this->database->get_last_error() : 'Unable to create lead', $payload);
            return new WP_Error(
                'create_failed',
                $this->database->get_last_error() ? $this->database->get_last_error() : 'Unable to create lead',
                array('status' => 500)
            );
        }

        $this->database->log_activity($lead_id, $user_id, 'webhook_created', array('source' => $lead_source));
        $this->apply_webhook_tags($lead_id, $payload);
        $this->maybe_log_webhook($user_id, $source_record['source_key'], 'success', 'Lead created', $payload);

        return rest_ensure_response(array('success' => true, 'lead_id' => $lead_id));
    }

    private function maybe_log_webhook($user_id, $source_key, $status, $message, $payload = null) {
        $enabled = (int) get_option('lead_aggregator_webhook_logging', 1);
        if (!$enabled) {
            return;
        }
        if ($payload !== null) {
            $payload = $this->redact_payload($payload);
        }
        $this->database->log_webhook(array(
            'user_id' => $user_id,
            'source_key' => $source_key,
            'status' => $status,
            'message' => $message,
            'payload' => $payload,
        ));
    }

    private function normalize_tag_ids($tags) {
        if ($tags === null || $tags === '') {
            return array();
        }
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            if (is_array($decoded)) {
                $tags = $decoded;
            } else {
                if (preg_match_all('/\d+/', $tags, $matches)) {
                    $tags = $matches[0];
                } else {
                    $tags = preg_split('/\s*,\s*/', $tags);
                }
            }
        }
        if (!is_array($tags)) {
            return array();
        }
        return array_values(array_filter(array_map('intval', $tags)));
    }

    private function redact_payload($payload) {
        $sensitive_keys = array(
            'first_name',
            'last_name',
            'email',
            'phone',
            'company',
            'note',
            'notes',
            'address',
            'message'
        );
        if (is_array($payload)) {
            $redacted = array();
            foreach ($payload as $key => $value) {
                $key_lower = is_string($key) ? strtolower($key) : $key;
                if (is_string($key_lower) && in_array($key_lower, $sensitive_keys, true)) {
                    $redacted[$key] = '[redacted]';
                    continue;
                }
                $redacted[$key] = $this->redact_payload($value);
            }
            return $redacted;
        }
        if (is_string($payload)) {
            if (strpos($payload, '@') !== false) {
                return '[redacted]';
            }
            if (preg_match('/\+?\d[\d\-\s\(\)]{6,}/', $payload)) {
                return '[redacted]';
            }
        }
        return $payload;
    }

    private function is_rate_limited($bucket, $limit, $window_seconds) {
        $ip = $this->get_client_ip();
        $key = 'lead_agg_rl_' . md5($bucket . '|' . $ip);
        $data = get_transient($key);
        $now = time();
        if (!is_array($data) || empty($data['reset']) || $data['reset'] <= $now) {
            $data = array('count' => 1, 'reset' => $now + $window_seconds);
            set_transient($key, $data, $window_seconds);
            return false;
        }
        $data['count'] = isset($data['count']) ? (int) $data['count'] + 1 : 1;
        if ($data['count'] > $limit) {
            set_transient($key, $data, $data['reset'] - $now);
            return true;
        }
        set_transient($key, $data, $data['reset'] - $now);
        return false;
    }

    private function get_client_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($parts[0]);
        }
        if (!$ip && !empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip ? $ip : 'unknown';
    }

    private function apply_webhook_tags($lead_id, $payload) {
        $raw = null;
        if (isset($payload['tags'])) {
            $raw = $payload['tags'];
        } elseif (isset($payload['tag_ids'])) {
            $raw = $payload['tag_ids'];
        } elseif (isset($payload['tag_id'])) {
            $raw = array($payload['tag_id']);
        } else {
            return;
        }
        $tag_ids = $this->normalize_tag_ids($raw);
        if (empty($tag_ids)) {
            return;
        }
        $this->database->set_lead_tags($lead_id, $tag_ids);
    }

    public function get_leads($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $filters = array(
            'stage_id' => $request->get_param('stage_id'),
            'status' => $request->get_param('status'),
            'search' => $request->get_param('search'),
        );
        $leads = $this->database->get_leads($user_id, $filters);
        return rest_ensure_response($leads);
    }

    public function get_lead($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $lead = $this->database->get_lead((int) $request['id'], $user_id);
        if (!$lead) {
            return new WP_Error('not_found', 'Lead not found', array('status' => 404));
        }
        $lead['tags'] = $this->database->get_lead_tags($lead['id']);
        return rest_ensure_response($lead);
    }

    public function create_lead($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        if (!$this->permissions->can_create_lead($user_id)) {
            return new WP_Error('limit_reached', 'Contact limit reached', array('status' => 403));
        }

        $params = $this->get_request_params($request);
        $custom_fields = $this->extract_custom_fields($params);
        $address = $this->extract_address_fields($params);
        $lead_id = $this->database->insert_lead(array_merge(array(
            'user_id' => $user_id,
            'source' => isset($params['source']) ? sanitize_text_field($params['source']) : 'manual',
            'first_name' => isset($params['first_name']) ? sanitize_text_field($params['first_name']) : '',
            'last_name' => isset($params['last_name']) ? sanitize_text_field($params['last_name']) : '',
            'email' => isset($params['email']) ? sanitize_email($params['email']) : '',
            'phone' => isset($params['phone']) ? sanitize_text_field($params['phone']) : '',
            'company' => isset($params['company']) ? sanitize_text_field($params['company']) : '',
            'status' => isset($params['status']) ? sanitize_text_field($params['status']) : 'open',
            'stage_id' => isset($params['stage_id']) ? (int) $params['stage_id'] : null,
            'followup_status' => isset($params['followup_status']) ? sanitize_text_field($params['followup_status']) : 'scheduled',
            'skip_reminders' => !empty($params['skip_reminders']) ? 1 : 0,
            'followup_at' => !empty($params['followup_at']) ? sanitize_text_field($params['followup_at']) : null,
            'due_at' => !empty($params['due_at']) ? sanitize_text_field($params['due_at']) : null,
            'last_actioned' => !empty($params['last_actioned']) ? sanitize_text_field($params['last_actioned']) : null,
            'last_contacted' => !empty($params['last_contacted']) ? sanitize_text_field($params['last_contacted']) : null,
        ), $custom_fields, $address));
        if (!$lead_id) {
            return new WP_Error(
                'create_failed',
                $this->database->get_last_error() ? $this->database->get_last_error() : 'Unable to create lead',
                array('status' => 500)
            );
        }

        if (!empty($params['tags']) && is_array($params['tags'])) {
            $tag_ids = array_map('intval', $params['tags']);
            $this->database->set_lead_tags($lead_id, $tag_ids);
        }

        $this->database->log_activity($lead_id, $actor_id, 'manual_created');

        return rest_ensure_response(array('success' => true, 'lead_id' => $lead_id));
    }

    public function update_lead($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $lead_id = (int) $request['id'];
        $params = $this->get_request_params($request);

        $data = array();

        if (array_key_exists('first_name', $params)) {
            $data['first_name'] = sanitize_text_field($params['first_name']);
        }
        if (array_key_exists('last_name', $params)) {
            $data['last_name'] = sanitize_text_field($params['last_name']);
        }
        if (array_key_exists('email', $params)) {
            $data['email'] = sanitize_email($params['email']);
        }
        if (array_key_exists('phone', $params)) {
            $data['phone'] = sanitize_text_field($params['phone']);
        }
        if (array_key_exists('company', $params)) {
            $data['company'] = sanitize_text_field($params['company']);
        }
        $address = $this->extract_address_fields($params, true);
        if (!empty($address)) {
            $data = array_merge($data, $address);
        }
        if (array_key_exists('source', $params)) {
            $data['source'] = sanitize_text_field($params['source']);
        }
        if (array_key_exists('status', $params)) {
            $data['status'] = sanitize_text_field($params['status']);
        }
        if (array_key_exists('followup_status', $params)) {
            $data['followup_status'] = sanitize_text_field($params['followup_status']);
        }
        if (array_key_exists('skip_reminders', $params)) {
            $data['skip_reminders'] = !empty($params['skip_reminders']) ? 1 : 0;
        }
        if (array_key_exists('stage_id', $params)) {
            $data['stage_id'] = $params['stage_id'] === '' ? null : (int) $params['stage_id'];
        }
        if (array_key_exists('followup_at', $params)) {
            $data['followup_at'] = $params['followup_at'] ? sanitize_text_field($params['followup_at']) : null;
        }
        if (array_key_exists('due_at', $params)) {
            $data['due_at'] = $params['due_at'] ? sanitize_text_field($params['due_at']) : null;
        }
        if (array_key_exists('last_actioned', $params)) {
            $data['last_actioned'] = $params['last_actioned'] ? sanitize_text_field($params['last_actioned']) : null;
        }
        if (array_key_exists('last_contacted', $params)) {
            $data['last_contacted'] = $params['last_contacted'] ? sanitize_text_field($params['last_contacted']) : null;
        }

        $custom_fields = $this->extract_custom_fields($params, true);
        if (!empty($custom_fields)) {
            $data = array_merge($data, $custom_fields);
        }

        $has_tag_update = array_key_exists('tags', $params) && is_array($params['tags']);

        if (empty($data) && !$has_tag_update) {
            return new WP_Error('missing_fields', 'No fields to update', array('status' => 400));
        }

        if (!empty($data)) {
            $updated = $this->database->update_lead($lead_id, $user_id, $data);

            if ($updated === false) {
                return new WP_Error('update_failed', 'Unable to update lead', array('status' => 500));
            }
        }

        if ($has_tag_update) {
            $tag_ids = array_map('intval', $params['tags']);
            $this->database->set_lead_tags($lead_id, $tag_ids);
        }

        $this->database->log_activity($lead_id, $actor_id, 'updated');

        return rest_ensure_response(array('success' => true));
    }

    public function delete_lead($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $lead_id = (int) $request['id'];
        $deleted = $this->database->delete_lead($lead_id, $user_id);
        if (!$deleted) {
            return new WP_Error('delete_failed', 'Unable to delete lead', array('status' => 500));
        }
        $this->database->log_activity($lead_id, $actor_id, 'deleted');
        return rest_ensure_response(array('success' => true));
    }

    public function bulk_delete_leads($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $params = $this->get_request_params($request);
        $ids = isset($params['ids']) && is_array($params['ids']) ? $params['ids'] : array();
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return new WP_Error('missing_ids', 'No leads selected.', array('status' => 400));
        }
        $deleted = $this->database->delete_leads_by_ids($user_id, $ids);
        if ($deleted === false) {
            return new WP_Error('delete_failed', 'Unable to delete leads', array('status' => 500));
        }
        return rest_ensure_response(array('success' => true, 'deleted' => (int) $deleted));
    }

    public function get_notes($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $notes = $this->database->get_notes((int) $request['id'], $user_id);
        return rest_ensure_response($notes);
    }

    public function add_note($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $lead_id = (int) $request['id'];
        $params = $this->get_request_params($request);
        $note = isset($params['note']) ? wp_kses_post($params['note']) : '';
        if (!$note) {
            return new WP_Error('missing_note', 'Note is required', array('status' => 400));
        }

        $note_id = $this->database->add_note($lead_id, $user_id, $note);
        $this->database->log_activity($lead_id, $actor_id, 'note_added');

        return rest_ensure_response(array('success' => true, 'note_id' => $note_id));
    }

    public function delete_note($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $note_id = (int) $request['note_id'];
        $deleted = $this->database->delete_note($note_id, $user_id);
        if (!$deleted) {
            return new WP_Error('delete_failed', 'Unable to delete note', array('status' => 500));
        }
        $this->database->log_activity((int) $request['id'], $actor_id, 'note_deleted');
        return rest_ensure_response(array('success' => true));
    }

    public function update_note($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $note_id = (int) $request['note_id'];
        $params = $this->get_request_params($request);
        $note = isset($params['note']) ? wp_kses_post($params['note']) : '';
        if (!$note) {
            return new WP_Error('missing_note', 'Note is required', array('status' => 400));
        }
        $updated = $this->database->update_note($note_id, $user_id, $note);
        if ($updated === false) {
            return new WP_Error('update_failed', 'Unable to update note', array('status' => 500));
        }
        $this->database->log_activity((int) $request['id'], $actor_id, 'note_updated');
        return rest_ensure_response(array('success' => true));
    }

    public function get_tags() {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        return rest_ensure_response($this->database->get_tags($user_id));
    }

    public function create_tag($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $params = $this->get_request_params($request);
        $name = isset($params['name']) ? sanitize_text_field($params['name']) : '';
        if (!$name) {
            return new WP_Error('missing_name', 'Tag name is required', array('status' => 400));
        }

        $tag_id = $this->database->create_tag($user_id, $name);
        return rest_ensure_response(array('success' => true, 'tag_id' => $tag_id));
    }

    public function delete_tag($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $tag_id = (int) $request['id'];
        $deleted = $this->database->delete_tag($user_id, $tag_id);
        if (!$deleted) {
            return new WP_Error('delete_failed', 'Unable to delete tag', array('status' => 500));
        }
        return rest_ensure_response(array('success' => true));
    }

    public function update_tag($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $tag_id = (int) $request['id'];
        $params = $this->get_request_params($request);
        $name = isset($params['name']) ? sanitize_text_field($params['name']) : '';
        if (!$name) {
            return new WP_Error('missing_name', 'Tag name is required', array('status' => 400));
        }
        $updated = $this->database->update_tag($user_id, $tag_id, $name);
        if ($updated === false) {
            return new WP_Error('update_failed', 'Unable to update tag', array('status' => 500));
        }
        return rest_ensure_response(array('success' => true));
    }

    public function get_tag_leads($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $tag_id = (int) $request['id'];
        $leads = $this->database->get_leads_by_tag($user_id, $tag_id);
        return rest_ensure_response($leads);
    }

    public function get_stages() {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        return rest_ensure_response($this->database->get_stages($user_id));
    }

    public function create_stage($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $params = $request->get_json_params();
        $name = isset($params['name']) ? sanitize_text_field($params['name']) : '';
        $position = isset($params['position']) ? (int) $params['position'] : 0;
        $outcome = isset($params['outcome']) ? $this->normalize_stage_outcome($params['outcome']) : 'open';
        if (!$name) {
            return new WP_Error('missing_name', 'Stage name is required', array('status' => 400));
        }

        $stage_id = $this->database->create_stage($user_id, $name, $position, $outcome);
        return rest_ensure_response(array('success' => true, 'stage_id' => $stage_id));
    }

    public function update_stage($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $stage_id = (int) $request['id'];
        $params = $this->get_request_params($request);
        $data = array();
        if (isset($params['name'])) {
            $data['name'] = sanitize_text_field($params['name']);
        }
        if (isset($params['position'])) {
            $data['position'] = (int) $params['position'];
        }
        if (isset($params['outcome'])) {
            $data['outcome'] = $this->normalize_stage_outcome($params['outcome']);
        }

        if (empty($data)) {
            return new WP_Error('missing_fields', 'No fields to update', array('status' => 400));
        }

        $updated = $this->database->update_stage($user_id, $stage_id, $data);
        if ($updated === false) {
            return new WP_Error('update_failed', 'Unable to update stage', array('status' => 500));
        }

        return rest_ensure_response(array('success' => true));
    }

    public function delete_stage($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $stage_id = (int) $request['id'];
        $deleted = $this->database->delete_stage($user_id, $stage_id);
        if (!$deleted) {
            return new WP_Error('delete_failed', 'Unable to delete stage', array('status' => 500));
        }
        return rest_ensure_response(array('success' => true));
    }

    private function normalize_stage_outcome($value) {
        $value = sanitize_key($value);
        if ($value === 'won' || $value === 'lost') {
            return $value;
        }
        return 'open';
    }

    public function get_business_profile() {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $profile = $this->database->get_business_profile($user_id);
        if (!$profile) {
            return rest_ensure_response(array());
        }

        $data = json_decode($profile['data'], true);
        return rest_ensure_response($data ? $data : array());
    }

    public function save_business_profile($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $params = $this->get_request_params($request);
        $this->database->save_business_profile($user_id, $params);
        return rest_ensure_response(array('success' => true));
    }

    public function scrape_business_profile($request) {
        $user_id = get_current_user_id();
        $params = $this->get_request_params($request);
        $url = isset($params['website_url']) ? esc_url_raw($params['website_url']) : '';
        if (!$url) {
            return new WP_Error('missing_url', 'Website URL is required.', array('status' => 400));
        }

        $parts = wp_parse_url($url);
        if (empty($parts['scheme']) || !in_array($parts['scheme'], array('http', 'https'), true)) {
            return new WP_Error('invalid_url', 'Website URL must start with http or https.', array('status' => 400));
        }

        $response = wp_remote_get($url, array(
            'timeout' => 12,
            'redirection' => 5,
            'user-agent' => 'LeadAggregatorBot/1.0; ' . home_url('/'),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 400) {
            return new WP_Error('fetch_failed', 'Unable to fetch website content.', array('status' => 400));
        }

        $body = wp_remote_retrieve_body($response);
        if (!$body) {
            return new WP_Error('empty_body', 'Website response was empty.', array('status' => 400));
        }

        $data = $this->extract_business_profile_from_html($body, $url);
        if (empty($data)) {
            return new WP_Error('no_data', 'Unable to extract business details from the website.', array('status' => 400));
        }

        $existing = $this->database->get_business_profile($user_id);
        $existing_data = array();
        if ($existing && !empty($existing['data'])) {
            $decoded = json_decode($existing['data'], true);
            if (is_array($decoded)) {
                $existing_data = $decoded;
            }
        }

        foreach ($data as $key => $value) {
            if (!empty($existing_data[$key])) {
                unset($data[$key]);
            }
        }

        $merged = array_merge($existing_data, $data);
        $this->database->save_business_profile($user_id, $merged);

        return rest_ensure_response(array(
            'success' => true,
            'data' => $merged,
            'extracted' => $data,
        ));
    }

    private function extract_business_profile_from_html($html, $url) {
        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $loaded = $document->loadHTML($html);
        libxml_clear_errors();
        if (!$loaded) {
            return array();
        }

        $xpath = new DOMXPath($document);
        foreach ($xpath->query('//script|//style|//noscript') as $node) {
            $node->parentNode->removeChild($node);
        }

        $get_meta = function ($selector, $attr = 'content') use ($xpath) {
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length > 0) {
                $value = $nodes->item(0)->getAttribute($attr);
                return is_string($value) ? trim($value) : '';
            }
            return '';
        };

        $title = '';
        $title_nodes = $document->getElementsByTagName('title');
        if ($title_nodes->length > 0) {
            $title = trim($title_nodes->item(0)->textContent);
        }

        $og_title = $get_meta('//meta[@property="og:title"]');
        $og_site = $get_meta('//meta[@property="og:site_name"]');
        $meta_desc = $get_meta('//meta[@name="description"]');
        $og_desc = $get_meta('//meta[@property="og:description"]');

        $h1 = '';
        $h1_nodes = $document->getElementsByTagName('h1');
        if ($h1_nodes->length > 0) {
            $h1 = trim($h1_nodes->item(0)->textContent);
        }

        $h2 = '';
        $h2_nodes = $document->getElementsByTagName('h2');
        if ($h2_nodes->length > 0) {
            $h2 = trim($h2_nodes->item(0)->textContent);
        }

        $business_name = $og_site ? $og_site : ($og_title ? $og_title : $title);
        $summary = $og_desc ? $og_desc : $meta_desc;

        $result = array(
            'website_url' => $url,
            'business_name' => $business_name,
            'primary_offer' => $h1 ? $h1 : $h2,
            'industry' => $summary,
            'notes' => $summary,
        );

        foreach ($result as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            $result[$key] = wp_strip_all_tags($value);
        }

        $result = array_filter($result, function ($value) {
            return (string) $value !== '';
        });

        return $result;
    }

    public function export_leads($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $leads = $this->database->get_leads($user_id, array());

        $temp = fopen('php://temp', 'r+');
        fputcsv($temp, array('ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Company', 'Stage', 'Status', 'Followup', 'Due', 'Source', 'Created'));
        foreach ($leads as $lead) {
            fputcsv($temp, array(
                $lead['id'],
                $lead['first_name'],
                $lead['last_name'],
                $lead['email'],
                $lead['phone'],
                $lead['company'],
                $lead['stage_id'],
                $lead['status'],
                $lead['followup_at'],
                $lead['due_at'],
                $lead['source'],
                $lead['created_at'],
            ));
        }
        rewind($temp);
        $csv = stream_get_contents($temp);
        fclose($temp);

        add_filter('rest_pre_serve_request', function ($served, $result, $request, $server) {
            if ($request->get_route() !== '/lead-aggregator/v1/export') {
                return $served;
            }
            $data = $result instanceof WP_REST_Response ? $result->get_data() : $result;
            if (!is_string($data)) {
                return $served;
            }
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="lead-aggregator-export.csv"');
            echo $data;
            return true;
        }, 10, 4);

        $response = new WP_REST_Response($csv, 200);
        $response->header('Content-Type', 'text/csv; charset=utf-8');
        $response->header('Content-Disposition', 'attachment; filename="lead-aggregator-export.csv"');

        return $response;
    }

    public function export_calendar($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $leads = $this->database->get_leads($user_id, array());

        $dashboard_url = home_url('/');
        $pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'lead-aggregator-dashboard.php',
            'number' => 1,
        ));
        if (!empty($pages)) {
            $dashboard_url = get_permalink($pages[0]->ID);
        }

        $lines = array(
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Lead Aggregator//Followups//EN',
            'CALSCALE:GREGORIAN',
            'X-WR-CALNAME:Lead Follow-ups',
            'X-WR-TIMEZONE:' . wp_timezone_string(),
        );

        foreach ($leads as $lead) {
            $when = $lead['followup_at'] ? $lead['followup_at'] : $lead['due_at'];
            if (!$when) {
                continue;
            }
            $start = new DateTime($when, wp_timezone());
            $end = clone $start;
            $end->modify('+30 minutes');

            $name = trim($lead['first_name'] . ' ' . $lead['last_name']);
            if (!$name) {
                $name = $lead['email'] ? $lead['email'] : 'Lead #' . $lead['id'];
            }

            $lead_url = add_query_arg('lead_id', (int) $lead['id'], $dashboard_url);
            $description = 'Lead: ' . $name .
                '\nEmail: ' . $lead['email'] .
                '\nFollowup: ' . ($lead['followup_at'] ?: 'n/a') .
                '\nDue: ' . ($lead['due_at'] ?: 'n/a') .
                '\nSource: ' . ($lead['source'] ?: 'manual') .
                '\nOpen: ' . $lead_url;

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:lead-' . $lead['id'] . '@' . parse_url(home_url('/'), PHP_URL_HOST);
            $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
            $lines[] = 'DTSTART:' . $start->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
            $lines[] = 'DTEND:' . $end->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
            $lines[] = 'SUMMARY:' . $this->ics_escape('Follow up: ' . $name);
            $lines[] = 'DESCRIPTION:' . $this->ics_escape($description);
            $lines[] = 'URL:' . esc_url_raw($lead_url);
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        $ics = implode("\r\n", $lines) . "\r\n";

        add_filter('rest_pre_serve_request', function ($served, $result, $request, $server) {
            if ($request->get_route() !== '/lead-aggregator/v1/calendar') {
                return $served;
            }
            $data = $result instanceof WP_REST_Response ? $result->get_data() : $result;
            if (!is_string($data)) {
                return $served;
            }
            header('Content-Type: text/calendar; charset=utf-8');
            header('Content-Disposition: attachment; filename="lead-followups.ics"');
            echo $data;
            return true;
        }, 10, 4);

        $response = new WP_REST_Response($ics, 200);
        $response->header('Content-Type', 'text/calendar; charset=utf-8');
        $response->header('Content-Disposition', 'attachment; filename="lead-followups.ics"');
        return $response;
    }

    public function export_lead($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $lead_id = (int) $request['id'];
        $lead = $this->database->get_lead($lead_id, $user_id);
        if (!$lead) {
            return new WP_Error('not_found', 'Lead not found', array('status' => 404));
        }
        $stages = $this->database->get_stages($user_id);
        $stage_map = array();
        foreach ($stages as $stage) {
            $stage_map[$stage['id']] = $stage['name'];
        }
        $labels = $this->get_custom_fields_labels($user_id);

        $headers = array(
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Company',
            'Street Address',
            'City',
            'State',
            'Zip',
            'Country',
            'Stage',
            'Status',
            'Followup',
            'Due',
            'Source'
        );
        for ($i = 1; $i <= 10; $i += 1) {
            $key = 'custom_' . $i;
            $headers[] = isset($labels[$key]) ? $labels[$key] : ('Custom Field ' . $i);
        }
        $headers[] = 'Created';

        $row = array(
            $lead['id'],
            $lead['first_name'],
            $lead['last_name'],
            $lead['email'],
            $lead['phone'],
            $lead['company'],
            $lead['address_street'],
            $lead['address_city'],
            $lead['address_state'],
            $lead['address_zip'],
            $lead['address_country'],
            isset($stage_map[$lead['stage_id']]) ? $stage_map[$lead['stage_id']] : '',
            $lead['status'],
            $lead['followup_at'],
            $lead['due_at'],
            $lead['source'],
        );
        for ($i = 1; $i <= 10; $i += 1) {
            $key = 'custom_' . $i;
            $row[] = isset($lead[$key]) ? $lead[$key] : '';
        }
        $row[] = $lead['created_at'];

        $temp = fopen('php://temp', 'r+');
        fputcsv($temp, $headers);
        fputcsv($temp, $row);
        rewind($temp);
        $csv = stream_get_contents($temp);
        fclose($temp);

        add_filter('rest_pre_serve_request', function ($served, $result, $request, $server) {
            if (strpos($request->get_route(), '/lead-aggregator/v1/leads/') !== 0 || substr($request->get_route(), -7) !== '/export') {
                return $served;
            }
            $data = $result instanceof WP_REST_Response ? $result->get_data() : $result;
            if (!is_string($data)) {
                return $served;
            }
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="lead-export.csv"');
            echo $data;
            return true;
        }, 10, 4);

        $response = new WP_REST_Response($csv, 200);
        $response->header('Content-Type', 'text/csv; charset=utf-8');
        $response->header('Content-Disposition', 'attachment; filename="lead-export.csv"');
        return $response;
    }

    public function export_lead_calendar($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $lead_id = (int) $request['id'];
        $lead = $this->database->get_lead($lead_id, $user_id);
        if (!$lead) {
            return new WP_Error('not_found', 'Lead not found', array('status' => 404));
        }

        $dashboard_url = home_url('/');
        $pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'lead-aggregator-dashboard.php',
            'number' => 1,
        ));
        if (!empty($pages)) {
            $dashboard_url = get_permalink($pages[0]->ID);
        }
        $lead_url = add_query_arg('lead_id', (int) $lead['id'], $dashboard_url);

        $lines = array(
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Lead Aggregator//Lead Calendar//EN',
            'CALSCALE:GREGORIAN',
            'X-WR-CALNAME:Lead Follow-ups',
            'X-WR-TIMEZONE:' . wp_timezone_string(),
        );

        $events = array(
            array('type' => 'Follow-up', 'when' => $lead['followup_at']),
            array('type' => 'Due', 'when' => $lead['due_at']),
        );
        foreach ($events as $event) {
            if (!$event['when']) {
                continue;
            }
            $start = new DateTime($event['when'], wp_timezone());
            $end = clone $start;
            $end->modify('+30 minutes');
            $name = trim($lead['first_name'] . ' ' . $lead['last_name']);
            if (!$name) {
                $name = $lead['email'] ? $lead['email'] : 'Lead #' . $lead['id'];
            }
            $uid = 'lead-' . $lead['id'] . '-' . strtolower($event['type']) . '@lead-aggregator';
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
            $lines[] = 'DTSTART:' . $start->format('Ymd\THis');
            $lines[] = 'DTEND:' . $end->format('Ymd\THis');
            $lines[] = 'SUMMARY:' . $this->ics_escape($event['type'] . ': ' . $name);
            $lines[] = 'DESCRIPTION:' . $this->ics_escape('Lead: ' . $name . '\n' . $lead_url);
            $lines[] = 'URL;VALUE=URI:' . $this->ics_escape($lead_url);
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        $ics = implode("\r\n", $lines);

        add_filter('rest_pre_serve_request', function ($served, $result, $request, $server) {
            if (strpos($request->get_route(), '/lead-aggregator/v1/leads/') !== 0 || substr($request->get_route(), -9) !== '/calendar') {
                return $served;
            }
            $data = $result instanceof WP_REST_Response ? $result->get_data() : $result;
            if (!is_string($data)) {
                return $served;
            }
            header('Content-Type: text/calendar; charset=utf-8');
            header('Content-Disposition: attachment; filename="lead-followup.ics"');
            echo $data;
            return true;
        }, 10, 4);

        $response = new WP_REST_Response($ics, 200);
        $response->header('Content-Type', 'text/calendar; charset=utf-8');
        $response->header('Content-Disposition', 'attachment; filename="lead-followup.ics"');
        return $response;
    }

    private function ics_escape($value) {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace("\r\n", '\n', $value);
        $value = str_replace("\n", '\n', $value);
        $value = str_replace(',', '\,', $value);
        $value = str_replace(';', '\;', $value);
        return $value;
    }

    public function get_webhook_sources() {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        return rest_ensure_response($this->database->get_webhook_sources_by_user($user_id));
    }

    public function create_webhook_source($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $existing = $this->database->get_webhook_sources_by_user($user_id);
        if (!empty($existing)) {
            return new WP_Error('limit_reached', 'Only one webhook is allowed per user.', array('status' => 400));
        }
        $params = $this->get_request_params($request);
        $source_key = isset($params['source_key']) ? sanitize_key($params['source_key']) : '';
        $shared_secret = isset($params['shared_secret']) ? sanitize_text_field($params['shared_secret']) : '';

        if (!$source_key) {
            $source_key = 'user-' . $user_id;
        }

        $source_id = $this->database->create_webhook_source($user_id, $source_key, $shared_secret, 1);
        $source = $this->database->get_webhook_source_by_id($source_id, $user_id);
        return rest_ensure_response($source);
    }

    public function delete_webhook_source($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $source_id = (int) $request['id'];
        $source = $this->database->get_webhook_source_by_id($source_id, $user_id);
        if (!$source) {
            return new WP_Error('not_found', 'Webhook source not found', array('status' => 404));
        }
        $deleted = $this->database->delete_webhook_source($source_id);
        if (!$deleted) {
            return new WP_Error('delete_failed', 'Unable to delete webhook source', array('status' => 500));
        }
        return rest_ensure_response(array('success' => true));
    }

    public function update_webhook_source($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $source_id = (int) $request['id'];
        $source = $this->database->get_webhook_source_by_id($source_id, $user_id);
        if (!$source) {
            return new WP_Error('not_found', 'Webhook source not found', array('status' => 404));
        }
        $params = $this->get_request_params($request);
        $data = array();
        if (!empty($params['regenerate'])) {
            $data['shared_secret'] = $this->generate_shared_secret();
        } elseif (array_key_exists('shared_secret', $params)) {
            $value = sanitize_text_field($params['shared_secret']);
            if ($value === '') {
                return new WP_Error('secret_required', 'Shared secret is required.', array('status' => 400));
            }
            $data['shared_secret'] = $value;
        }
        if (empty($data)) {
            return new WP_Error('missing_fields', 'No fields to update', array('status' => 400));
        }
        $updated = $this->database->update_webhook_source($user_id, $source_id, $data);
        if ($updated === false) {
            return new WP_Error('update_failed', 'Unable to update webhook', array('status' => 500));
        }
        return rest_ensure_response(array('success' => true));
    }

    private function generate_shared_secret() {
        return wp_generate_password(24, false, false);
    }

    public function get_billing_status() {
        $user_id = get_current_user_id();
        $plans = $this->billing->get_plans();
        $status = $this->billing->get_subscription_status($user_id);
        $plan_key = $this->billing->get_plan_key($user_id);
        $plan = $plan_key && isset($plans[$plan_key]) ? $plans[$plan_key] : null;

        $public_plans = array();
        foreach ($plans as $key => $plan_data) {
            $public_plans[$key] = array(
                'key' => $key,
                'label' => $plan_data['label'],
                'limit' => isset($plan_data['limit']) ? (int) $plan_data['limit'] : 0,
            );
        }

        return rest_ensure_response(array(
            'status' => $status ? $status : 'inactive',
            'plan_key' => $plan_key,
            'plan_label' => $plan ? $plan['label'] : '',
            'plan_limit' => $plan && isset($plan['limit']) ? (int) $plan['limit'] : 0,
            'current_period_end' => (int) get_user_meta($user_id, 'lead_aggregator_subscription_period_end', true),
            'lead_count' => $this->database->count_leads($user_id),
            'plans' => array_values($public_plans),
        ));
    }

    public function get_billing_plans() {
        $plans = $this->billing->get_plans();
        $public_plans = array();
        foreach ($plans as $key => $plan_data) {
            $public_plans[] = array(
                'key' => $key,
                'label' => $plan_data['label'],
                'limit' => isset($plan_data['limit']) ? (int) $plan_data['limit'] : 0,
            );
        }

        return rest_ensure_response(array('plans' => $public_plans));
    }

    public function create_checkout_session($request) {
        if ($this->is_rate_limited('billing:checkout', 20, 60)) {
            return new WP_Error('rate_limited', 'Too many requests. Please try again later.', array('status' => 429));
        }
        $params = $this->get_request_params($request);
        $plan_key = isset($params['plan_key']) ? sanitize_key($params['plan_key']) : '';
        $interval = isset($params['interval']) ? sanitize_key($params['interval']) : 'monthly';
        if (!in_array($interval, array('monthly', 'annual'), true)) {
            $interval = 'monthly';
        }

        $user_id = get_current_user_id();
        if ($user_id <= 0) {
            $email = isset($params['email']) ? sanitize_email($params['email']) : '';
            $password = isset($params['password']) ? (string) $params['password'] : '';
            $username = isset($params['username']) ? sanitize_user($params['username']) : '';

            if (!$email || !$password) {
                return new WP_Error('missing_fields', 'Email and password are required.', array('status' => 400));
            }

            if (!$username) {
                $username = sanitize_user(strstr($email, '@', true));
            }

            if (!$username) {
                $username = 'leaduser';
            }

            $base_username = $username;
            $suffix = 1;
            while (username_exists($username)) {
                $username = $base_username . $suffix;
                $suffix++;
            }

            if (email_exists($email)) {
                $existing = get_user_by('email', $email);
                if ($existing) {
                    $user_id = (int) $existing->ID;
                }
            }

            if (!$user_id) {
                $user_id = wp_create_user($username, $password, $email);
                if (is_wp_error($user_id)) {
                    $data = $user_id->get_error_data();
                    if (!is_array($data)) {
                        $data = array();
                    }
                    if (!isset($data['status'])) {
                        $data['status'] = 400;
                        $user_id->add_data($data);
                    }
                    return $user_id;
                }
                update_user_meta($user_id, 'lead_aggregator_subscription_status', 'inactive');
            }
        }

        if (!$plan_key) {
            return new WP_Error('missing_plan', 'Please select a plan.', array('status' => 400));
        }

        $url = $this->billing->create_checkout_session($user_id, $plan_key, $interval);
        if (is_wp_error($url)) {
            return $url;
        }

        return rest_ensure_response(array('checkout_url' => esc_url_raw($url)));
    }

    public function create_portal_session() {
        $user_id = get_current_user_id();
        $url = $this->billing->create_portal_session($user_id);
        if (is_wp_error($url)) {
            return $url;
        }

        return rest_ensure_response(array('portal_url' => esc_url_raw($url)));
    }

    public function handle_billing_webhook($request) {
        if ($this->is_rate_limited('billing:webhook', 60, 60)) {
            return new WP_Error('rate_limited', 'Too many requests. Please try again later.', array('status' => 429));
        }
        return $this->billing->handle_webhook($request);
    }

    public function get_notification_settings() {
        $user_id = get_current_user_id();
        $time = get_user_meta($user_id, 'lead_aggregator_digest_time', true);
        if (!$time) {
            $time = get_option('lead_aggregator_digest_time_default', '09:00');
        }
        $timezone = get_user_meta($user_id, 'lead_aggregator_digest_timezone', true);
        if (!$timezone) {
            $timezone = get_option('lead_aggregator_digest_timezone_default', wp_timezone_string());
        }

        return rest_ensure_response(array(
            'time' => $time,
            'timezone' => $timezone,
            'last_sent' => get_user_meta($user_id, 'lead_aggregator_digest_last_sent', true),
        ));
    }

    public function save_notification_settings($request) {
        $user_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($user_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $params = $this->get_request_params($request);
        $time = isset($params['time']) ? sanitize_text_field($params['time']) : '';
        $timezone = isset($params['timezone']) ? sanitize_text_field($params['timezone']) : '';

        if (!$time || !preg_match('/^\d{2}:\d{2}$/', $time)) {
            return new WP_Error('invalid_time', 'Time must be in HH:MM format.', array('status' => 400));
        }
        list($hour, $minute) = array_map('intval', explode(':', $time));
        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            return new WP_Error('invalid_time', 'Time must be a valid 24-hour time.', array('status' => 400));
        }

        if ($timezone && !in_array($timezone, timezone_identifiers_list(), true)) {
            return new WP_Error('invalid_timezone', 'Timezone must be a valid IANA timezone.', array('status' => 400));
        }

        update_user_meta($user_id, 'lead_aggregator_digest_time', $time);
        if ($timezone) {
            update_user_meta($user_id, 'lead_aggregator_digest_timezone', $timezone);
        }

        return rest_ensure_response(array('success' => true));
    }

    public function get_custom_fields() {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $fields = $this->get_custom_fields_labels($user_id);
        return rest_ensure_response(array('fields' => $fields));
    }

    public function save_custom_fields($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $params = $this->get_request_params($request);
        $fields = isset($params['fields']) && is_array($params['fields']) ? $params['fields'] : array();
        $payload = array();
        for ($i = 1; $i <= 10; $i += 1) {
            $key = 'custom_' . $i;
            if (array_key_exists($key, $fields)) {
                $payload[$key] = sanitize_text_field($fields[$key]);
            }
        }
        update_user_meta($user_id, 'lead_aggregator_custom_fields', $payload);
        return rest_ensure_response(array('success' => true));
    }

    public function get_me() {
        $user_id = get_current_user_id();
        return rest_ensure_response(array(
            'id' => (int) $user_id,
            'manager_id' => (int) get_user_meta($user_id, 'lead_aggregator_manager_id', true),
            'access_enabled' => (int) get_user_meta($user_id, 'lead_aggregator_access_enabled', true),
            'access_level' => get_user_meta($user_id, 'lead_aggregator_access_level', true) ?: 'full',
        ));
    }

    public function get_team() {
        $manager_id = get_current_user_id();
        $error = $this->require_manager_user($manager_id);
        if (is_wp_error($error)) {
            return $error;
        }
        $users = get_users(array(
            'meta_key' => 'lead_aggregator_manager_id',
            'meta_value' => $manager_id,
            'number' => 200,
            'fields' => array('ID', 'display_name', 'user_email'),
        ));
        $team = array();
        foreach ($users as $user) {
            $team[] = array(
                'id' => (int) $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'access_enabled' => (int) get_user_meta($user->ID, 'lead_aggregator_access_enabled', true),
                'access_level' => get_user_meta($user->ID, 'lead_aggregator_access_level', true) ?: 'full',
            );
        }
        return rest_ensure_response(array('users' => $team));
    }

    public function create_team_member($request) {
        $manager_id = get_current_user_id();
        $error = $this->require_manager_user($manager_id);
        if (is_wp_error($error)) {
            return $error;
        }
        $params = $this->get_request_params($request);
        $email = isset($params['email']) ? sanitize_email($params['email']) : '';
        $name = isset($params['name']) ? sanitize_text_field($params['name']) : '';
        $access_level = isset($params['access_level']) ? sanitize_key($params['access_level']) : 'full';
        if (!in_array($access_level, array('full', 'read'), true)) {
            $access_level = 'full';
        }
        if (!$email) {
            return new WP_Error('missing_email', 'Email is required', array('status' => 400));
        }
        if (email_exists($email)) {
            return new WP_Error('email_exists', 'User already exists. Please use a different email.', array('status' => 400));
        }
        $username = sanitize_user(strstr($email, '@', true));
        if (!$username) {
            $username = 'leaduser';
        }
        $base_username = $username;
        $suffix = 1;
        while (username_exists($username)) {
            $username = $base_username . $suffix;
            $suffix++;
        }
        $password = wp_generate_password(16, false, false);
        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        if ($name) {
            wp_update_user(array('ID' => $user_id, 'display_name' => $name));
        }
        update_user_meta($user_id, 'lead_aggregator_manager_id', $manager_id);
        update_user_meta($user_id, 'lead_aggregator_access_enabled', 1);
        update_user_meta($user_id, 'lead_aggregator_access_level', $access_level);
        update_user_meta($user_id, 'lead_aggregator_subscription_status', 'active');

        return rest_ensure_response(array(
            'success' => true,
            'user_id' => (int) $user_id,
            'password' => $password,
        ));
    }

    public function update_team_member($request) {
        $manager_id = get_current_user_id();
        $error = $this->require_manager_user($manager_id);
        if (is_wp_error($error)) {
            return $error;
        }
        $user_id = (int) $request['id'];
        $member_manager = (int) get_user_meta($user_id, 'lead_aggregator_manager_id', true);
        if ($member_manager !== $manager_id) {
            return new WP_Error('not_found', 'Team member not found', array('status' => 404));
        }
        $params = $this->get_request_params($request);
        $response = array('success' => true);
        if (isset($params['access_enabled'])) {
            update_user_meta($user_id, 'lead_aggregator_access_enabled', (int) !!$params['access_enabled']);
        }
        if (isset($params['access_level'])) {
            $level = sanitize_key($params['access_level']);
            if (in_array($level, array('full', 'read'), true)) {
                update_user_meta($user_id, 'lead_aggregator_access_level', $level);
            }
        }
        if (!empty($params['reset_password'])) {
            $password = wp_generate_password(16, false, false);
            wp_set_password($password, $user_id);
            $response['password'] = $password;
        }
        return rest_ensure_response($response);
    }

    private function require_manager_user($user_id) {
        $manager_id = (int) get_user_meta($user_id, 'lead_aggregator_manager_id', true);
        if ($manager_id > 0) {
            return new WP_Error('not_allowed', 'Sub-accounts cannot manage team members.', array('status' => 403));
        }
        return true;
    }

    private function extract_custom_fields($params, $only_if_present = false) {
        $data = array();
        $fields = array();
        $has_any = false;
        if (isset($params['custom_fields']) && is_array($params['custom_fields'])) {
            $fields = $params['custom_fields'];
        }
        for ($i = 1; $i <= 10; $i += 1) {
            $key = 'custom_' . $i;
            if (array_key_exists($key, $params)) {
                $value = $params[$key];
                $data[$key] = $value === '' ? null : sanitize_text_field($value);
                $has_any = true;
                continue;
            }
            if (array_key_exists($key, $fields)) {
                $value = $fields[$key];
                $data[$key] = $value === '' ? null : sanitize_text_field($value);
                $has_any = true;
            } elseif (!$only_if_present) {
                $data[$key] = null;
            }
        }
        if ($only_if_present && !$has_any) {
            return array();
        }
        return $data;
    }

    private function get_default_custom_fields() {
        $fields = array();
        for ($i = 1; $i <= 10; $i += 1) {
            $fields['custom_' . $i] = 'Custom Field ' . $i;
        }
        return $fields;
    }

    private function get_custom_fields_labels($user_id) {
        $defaults = $this->get_default_custom_fields();
        $stored = get_user_meta($user_id, 'lead_aggregator_custom_fields', true);
        if (!is_array($stored)) {
            $stored = array();
        }
        $fields = array();
        for ($i = 1; $i <= 10; $i += 1) {
            $key = 'custom_' . $i;
            $label = isset($stored[$key]) ? sanitize_text_field($stored[$key]) : $defaults[$key];
            $fields[$key] = $label;
        }
        return $fields;
    }

    private function get_account_user_id($user_id) {
        $manager_id = (int) get_user_meta($user_id, 'lead_aggregator_manager_id', true);
        return $manager_id > 0 ? $manager_id : $user_id;
    }

    private function ensure_not_read_only($user_id) {
        if ($this->permissions->is_read_only($user_id)) {
            return new WP_Error('read_only', 'Read-only access. Please contact your manager to make changes.', array('status' => 403));
        }
        return true;
    }

    private function extract_address_fields($params, $only_if_present = false) {
        $fields = array(
            'address_street',
            'address_city',
            'address_state',
            'address_zip',
            'address_country'
        );
        $data = array();
        $has_any = false;
        foreach ($fields as $field) {
            if (array_key_exists($field, $params)) {
                $value = $params[$field];
                $data[$field] = $value === '' ? null : sanitize_text_field($value);
                $has_any = true;
            } elseif (!$only_if_present) {
                $data[$field] = null;
            }
        }
        if (!$has_any && isset($params['address']) && $params['address'] !== '') {
            $data['address_street'] = sanitize_text_field($params['address']);
            $has_any = true;
        }
        if ($only_if_present && !$has_any) {
            return array();
        }
        return $data;
    }
}
