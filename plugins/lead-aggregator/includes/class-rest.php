<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Aggregator_REST {
    private $database;
    private $permissions;
    private $billing;
    private $audit;
    private $fluentcart;

    public function __construct($database, $permissions, $billing, $audit, $fluentcart) {
        $this->database = $database;
        $this->permissions = $permissions;
        $this->billing = $billing;
        $this->audit = $audit;
        $this->fluentcart = $fluentcart;

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

        register_rest_route('lead-aggregator/v1', '/leads/bulk-assign', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_assign_leads'),
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

        register_rest_route('lead-aggregator/v1', '/leads/(?P<id>\\d+)/followup-history', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_followup_history'),
            'permission_callback' => array($this, 'check_user_read'),
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

        register_rest_route('lead-aggregator/v1', '/pipeline-stages', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_pipeline_stages'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/pipeline-stages', array(
            'methods' => 'PUT',
            'callback' => array($this, 'save_pipeline_stages'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/pipeline-stages/lead-count', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_lead_count_by_stage'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/pipeline-stages/migrate', array(
            'methods' => 'POST',
            'callback' => array($this, 'migrate_leads_stage'),
            'permission_callback' => array($this, 'check_user_write'),
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

        register_rest_route('lead-aggregator/v1', '/fluentcart/plans', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_fluentcart_plans'),
            'permission_callback' => array($this, 'check_admin'),
        ));

        register_rest_route('lead-aggregator/v1', '/fluentcart/debug', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_fluentcart_debug'),
            'permission_callback' => array($this, 'check_admin'),
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

        register_rest_route('lead-aggregator/v1', '/fluentcart/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_fluentcart_webhook'),
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

        register_rest_route('lead-aggregator/v1', '/get-started/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_get_started_settings'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/get-started/settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_get_started_settings'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/quick-action/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_quick_action_settings'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/quick-action/settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_quick_action_settings'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/appearance/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_appearance_settings'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/appearance/settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_appearance_settings'),
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

        register_rest_route('lead-aggregator/v1', '/activity/log', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_activity_log'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/activity/reporting', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_activity_reporting'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/activity/export', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_activity_log'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/activity/seed', array(
            'methods' => 'POST',
            'callback' => array($this, 'seed_activity_log'),
            'permission_callback' => array($this, 'check_user_write'),
        ));

        register_rest_route('lead-aggregator/v1', '/analytics/won', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_won_analytics'),
            'permission_callback' => array($this, 'check_user_read'),
        ));

        register_rest_route('lead-aggregator/v1', '/analytics/lost', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_lost_analytics'),
            'permission_callback' => array($this, 'check_user_read'),
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

        register_rest_route('lead-aggregator/v1', '/team/assignable', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_team_assignable'),
            'permission_callback' => array($this, 'check_user_read'),
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

    public function check_admin() {
        if (current_user_can('manage_options')) {
            return true;
        }
        return new WP_Error('forbidden', 'Insufficient permissions.', array('status' => 403));
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
        $entitlements = lead_aggregator_get_entitlements($user_id);
        if (!empty($entitlements['is_over_limit'])) {
            $this->maybe_log_webhook($user_id, $source, 'failed', 'Lead limit reached', $payload);
            return new WP_Error('over_limit', 'Lead limit reached. Please upgrade your plan.', array('status' => 403));
        }

        $lead_source = 'webhook';
        if (isset($payload['source']) && $payload['source'] !== '') {
            $lead_source = sanitize_text_field($payload['source']);
        }

        $custom_fields = $this->extract_custom_fields($payload);
        $address = $this->extract_address_fields($payload);
        $status = isset($payload['status']) ? $this->normalize_pipeline_stage($payload['status']) : 'new';
        $followup_status = isset($payload['followup_status']) ? $this->normalize_followup_status($payload['followup_status']) : 'not_set';
        $followup_at = $this->to_utc_datetime(isset($payload['followup_at']) ? $payload['followup_at'] : null);
        $due_at = $this->to_utc_datetime(isset($payload['due_at']) ? $payload['due_at'] : null);
        if ($followup_at && !$due_at) {
            $due_at = $followup_at;
        }
        $last_actioned = $this->to_utc_datetime(isset($payload['last_actioned']) ? $payload['last_actioned'] : null);
        $last_contacted = $this->to_utc_datetime(isset($payload['last_contacted']) ? $payload['last_contacted'] : null);
        $lead_id = $this->database->insert_lead(array_merge(array(
            'user_id' => $user_id,
            'source' => $lead_source,
            'first_name' => isset($payload['first_name']) ? sanitize_text_field($payload['first_name']) : '',
            'last_name' => isset($payload['last_name']) ? sanitize_text_field($payload['last_name']) : '',
            'email' => isset($payload['email']) ? sanitize_email($payload['email']) : '',
            'phone' => isset($payload['phone']) ? sanitize_text_field($payload['phone']) : '',
            'company' => isset($payload['company']) ? sanitize_text_field($payload['company']) : '',
            'status' => $status,
            'followup_status' => $followup_status,
            'skip_reminders' => !empty($payload['skip_reminders']) ? 1 : 0,
            'followup_at' => $followup_at,
            'due_at' => $due_at,
            'last_actioned' => $last_actioned,
            'last_contacted' => $last_contacted,
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
            $this->audit_log('webhook_failed', 'webhook', (int) $source_record['id'], $source_record['source_key'], array(
                'error' => 'Shared secret required',
            ), (int) $source_record['user_id']);
            return new WP_Error('secret_required', 'Shared secret not configured. Generate one in settings.', array('status' => 401));
        }
        if (!$secret || $secret !== $required_secret) {
            $this->maybe_log_webhook((int) $source_record['user_id'], $source_record['source_key'], 'failed', 'Invalid webhook secret', $this->get_request_params($request));
            $this->audit_log('webhook_failed', 'webhook', (int) $source_record['id'], $source_record['source_key'], array(
                'error' => 'Invalid webhook secret',
            ), (int) $source_record['user_id']);
            return new WP_Error('invalid_secret', 'Invalid webhook secret', array('status' => 401));
        }

        $payload = $request->get_json_params();
        if (empty($payload)) {
            $payload = $request->get_body_params();
        }

        $user_id = (int) $source_record['user_id'];
        $entitlements = lead_aggregator_get_entitlements($user_id);
        if (!empty($entitlements['is_over_limit'])) {
            $this->maybe_log_webhook($user_id, $source_record['source_key'], 'failed', 'Lead limit reached', $payload);
            $this->audit_log('webhook_failed', 'webhook', (int) $source_record['id'], $source_record['source_key'], array(
                'error' => 'Lead limit reached',
            ), $user_id);
            return new WP_Error('over_limit', 'Lead limit reached. Please upgrade your plan.', array('status' => 403));
        }

        $lead_source = 'webhook';
        if (isset($payload['source']) && $payload['source'] !== '') {
            $lead_source = sanitize_text_field($payload['source']);
        }

        $custom_fields = $this->extract_custom_fields($payload);
        $address = $this->extract_address_fields($payload);
        $status = isset($payload['status']) ? $this->normalize_pipeline_stage($payload['status']) : 'new';
        $followup_status = isset($payload['followup_status']) ? $this->normalize_followup_status($payload['followup_status']) : 'not_set';
        $followup_at = $this->to_utc_datetime(isset($payload['followup_at']) ? $payload['followup_at'] : null);
        $due_at = $this->to_utc_datetime(isset($payload['due_at']) ? $payload['due_at'] : null);
        if ($followup_at && !$due_at) {
            $due_at = $followup_at;
        }
        $last_actioned = $this->to_utc_datetime(isset($payload['last_actioned']) ? $payload['last_actioned'] : null);
        $last_contacted = $this->to_utc_datetime(isset($payload['last_contacted']) ? $payload['last_contacted'] : null);
        $lead_id = $this->database->insert_lead(array_merge(array(
            'user_id' => $user_id,
            'source' => $lead_source,
            'first_name' => isset($payload['first_name']) ? sanitize_text_field($payload['first_name']) : '',
            'last_name' => isset($payload['last_name']) ? sanitize_text_field($payload['last_name']) : '',
            'email' => isset($payload['email']) ? sanitize_email($payload['email']) : '',
            'phone' => isset($payload['phone']) ? sanitize_text_field($payload['phone']) : '',
            'company' => isset($payload['company']) ? sanitize_text_field($payload['company']) : '',
            'status' => $status,
            'followup_status' => $followup_status,
            'skip_reminders' => !empty($payload['skip_reminders']) ? 1 : 0,
            'followup_at' => $followup_at,
            'due_at' => $due_at,
            'last_actioned' => $last_actioned,
            'last_contacted' => $last_contacted,
        ), $custom_fields, $address));
        if (!$lead_id) {
            $this->maybe_log_webhook($user_id, $source_record['source_key'], 'failed', $this->database->get_last_error() ? $this->database->get_last_error() : 'Unable to create lead', $payload);
            $this->audit_log('webhook_failed', 'webhook', (int) $source_record['id'], $source_record['source_key'], array(
                'error' => $this->database->get_last_error() ? $this->database->get_last_error() : 'Unable to create lead',
            ), $user_id);
            return new WP_Error(
                'create_failed',
                $this->database->get_last_error() ? $this->database->get_last_error() : 'Unable to create lead',
                array('status' => 500)
            );
        }

        $this->database->log_activity($lead_id, $user_id, 'webhook_created', array('source' => $lead_source));
        $this->apply_webhook_tags($lead_id, $payload);
        $this->maybe_log_webhook($user_id, $source_record['source_key'], 'success', 'Lead created', $payload);
        $lead_label = $this->format_lead_label(array(
            'id' => $lead_id,
            'first_name' => $payload['first_name'] ?? '',
            'last_name' => $payload['last_name'] ?? '',
            'email' => $payload['email'] ?? '',
        ));
        $this->audit_log('webhook_fired', 'webhook', (int) $source_record['id'], $source_record['source_key'], array(
            'lead_id' => $lead_id,
            'source' => $lead_source,
        ), $user_id);
        $this->audit_log('lead_created', 'lead', $lead_id, $lead_label, array(
            'source' => $lead_source,
            'status' => $status,
            'followup_status' => $followup_status,
        ), $user_id);

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
        $status = $request->get_param('status');
        $normalized = $status ? $this->normalize_pipeline_stage($status) : null;
        $filters = array(
            'status' => $normalized,
            'search' => $request->get_param('search'),
        );
        /* Resolve won/lost to actual stage keys for custom pipelines */
        if ($normalized && in_array($normalized, array('won', 'lost'), true)) {
            $filters['status_keys'] = $this->get_stage_keys_by_type($user_id, $normalized);
        }
        /* Follow-ups: exclude won/lost so they never appear in Follow-ups tab */
        $exclude_status = $request->get_param('exclude_status');
        if ($exclude_status) {
            $exclude = is_array($exclude_status) ? $exclude_status : array_map('trim', explode(',', $exclude_status));
            $filters['exclude_status'] = array_values(array_filter($exclude));
        }
        $range_param = $request->get_param('range');
        if (!empty($filters['status']) && in_array($filters['status'], array('won', 'lost'), true) && $range_param) {
            $range = $this->parse_activity_range(array('range' => sanitize_text_field($range_param)));
            $filters['outcome_date_from'] = $range['start'];
            $filters['outcome_date_to'] = $range['end'];
        }
        $leads = $this->database->get_leads($user_id, $filters);
        $leads = array_map(array($this, 'normalize_lead_for_response'), $leads);
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
        return rest_ensure_response($this->normalize_lead_for_response($lead));
    }

    public function create_lead($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $entitlements = lead_aggregator_get_entitlements($user_id);
        if (!empty($entitlements['is_over_limit'])) {
            return new WP_Error('over_limit', 'Lead limit reached. Please upgrade your plan.', array('status' => 403));
        }

        $params = $this->get_request_params($request);
        $custom_fields = $this->extract_custom_fields($params);
        $address = $this->extract_address_fields($params);
        $status = isset($params['status']) ? $this->normalize_pipeline_stage($params['status']) : 'new';
        $followup_status = isset($params['followup_status']) ? $this->normalize_followup_status($params['followup_status']) : 'not_set';
        $followup_at = $this->to_utc_datetime(isset($params['followup_at']) ? $params['followup_at'] : null);
        $due_at = $this->to_utc_datetime(isset($params['due_at']) ? $params['due_at'] : null);
        if ($followup_at && !$due_at) {
            $due_at = $followup_at;
        }
        $last_actioned = $this->to_utc_datetime(isset($params['last_actioned']) ? $params['last_actioned'] : null);
        $last_contacted = $this->to_utc_datetime(isset($params['last_contacted']) ? $params['last_contacted'] : null);
        $lead_id = $this->database->insert_lead(array_merge(array(
            'user_id' => $user_id,
            'source' => isset($params['source']) ? sanitize_text_field($params['source']) : 'manual',
            'first_name' => isset($params['first_name']) ? sanitize_text_field($params['first_name']) : '',
            'last_name' => isset($params['last_name']) ? sanitize_text_field($params['last_name']) : '',
            'email' => isset($params['email']) ? sanitize_email($params['email']) : '',
            'phone' => isset($params['phone']) ? sanitize_text_field($params['phone']) : '',
            'company' => isset($params['company']) ? sanitize_text_field($params['company']) : '',
            'status' => $status,
            'followup_status' => $followup_status,
            'skip_reminders' => !empty($params['skip_reminders']) ? 1 : 0,
            'followup_at' => $followup_at,
            'due_at' => $due_at,
            'last_actioned' => $last_actioned,
            'last_contacted' => $last_contacted,
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
        $lead_label = $this->format_lead_label(array(
            'id' => $lead_id,
            'first_name' => $params['first_name'] ?? '',
            'last_name' => $params['last_name'] ?? '',
            'email' => $params['email'] ?? '',
        ));
        $this->audit_log('lead_created', 'lead', $lead_id, $lead_label, array(
            'source' => $params['source'] ?? 'manual',
            'status' => $status,
            'followup_status' => $followup_status,
        ), $actor_id);

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
        $before = $this->database->get_lead($lead_id, $user_id);
        $before_tags = array();
        if (isset($params['tags']) && is_array($params['tags'])) {
            $before_tags = $this->database->get_lead_tags($lead_id);
        }

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
            $data['status'] = $this->normalize_pipeline_stage($params['status']);
        }
        if (array_key_exists('followup_status', $params)) {
            $data['followup_status'] = $this->normalize_followup_status($params['followup_status']);
        }
        if (array_key_exists('skip_reminders', $params)) {
            $data['skip_reminders'] = !empty($params['skip_reminders']) ? 1 : 0;
        }
        if (array_key_exists('followup_at', $params)) {
            $data['followup_at'] = $this->to_utc_datetime($params['followup_at']);
        }
        if (array_key_exists('due_at', $params)) {
            $data['due_at'] = $this->to_utc_datetime($params['due_at']);
        }
        if (array_key_exists('last_actioned', $params)) {
            $data['last_actioned'] = $this->to_utc_datetime($params['last_actioned']);
        }
        if (array_key_exists('last_contacted', $params)) {
            $data['last_contacted'] = $this->to_utc_datetime($params['last_contacted']);
        }
        if (array_key_exists('assigned_to', $params)) {
            $assigned_to = $params['assigned_to'];
            if ($assigned_to === null || $assigned_to === '' || $assigned_to === false) {
                $data['assigned_to'] = null;
            } else {
                $assigned_id = (int) $assigned_to;
                if ($assigned_id <= 0 || !$this->is_valid_team_member_for_assignment($user_id, $assigned_id)) {
                    return new WP_Error('invalid_assigned_to', 'Invalid team member for assignment.', array('status' => 400));
                }
                $data['assigned_to'] = $assigned_id;
            }
        }

        $custom_fields = $this->extract_custom_fields($params, true);
        if (!empty($custom_fields)) {
            $data = array_merge($data, $custom_fields);
        }

        $has_tag_update = array_key_exists('tags', $params) && is_array($params['tags']);
        $followup_save = !empty($params['followup_save']);
        $followup_note = isset($params['followup_note']) ? wp_kses_post(trim($params['followup_note'])) : '';

        if (empty($data) && !$has_tag_update && !$followup_save) {
            return new WP_Error('missing_fields', 'No fields to update', array('status' => 400));
        }

        if (!empty($data)) {
            $updated = $this->database->update_lead($lead_id, $user_id, $data);

            if ($updated === false) {
                return new WP_Error('update_failed', 'Unable to update lead', array('status' => 500));
            }
        }

        if ($followup_save) {
            $after = $this->database->get_lead($lead_id, $user_id);
            $history_data = array(
                'status_before' => $before ? $before['status'] : null,
                'status_after' => $after ? $after['status'] : null,
                'followup_status_before' => $before ? $before['followup_status'] : null,
                'followup_status_after' => $after ? $after['followup_status'] : null,
                'followup_at' => $after ? $after['followup_at'] : null,
                'due_at' => $after ? $after['due_at'] : null,
                'note' => $followup_note ? $followup_note : null,
            );
            $this->database->insert_followup_history($lead_id, $user_id, $actor_id, $history_data);
            if ($followup_note) {
                $this->database->add_note($lead_id, $user_id, $followup_note);
                $this->database->log_activity($lead_id, $actor_id, 'note_added');
            }
        }

        if ($has_tag_update) {
            $tag_ids = array_map('intval', $params['tags']);
            $this->database->set_lead_tags($lead_id, $tag_ids);
        }

        $this->database->log_activity($lead_id, $actor_id, 'updated');
        if ($before) {
            $lead_label = $this->format_lead_label($before);
            $changes = array_keys($data);
            if ($has_tag_update) {
                $changes[] = 'tags';
            }
            $this->audit_log('lead_updated', 'lead', $lead_id, $lead_label, array(
                'changed_fields' => $changes,
            ), $actor_id);

            if (isset($data['status']) && $before['status'] !== $data['status']) {
                $this->audit_log('pipeline_stage_changed', 'lead', $lead_id, $lead_label, array(
                    'from' => $before['status'],
                    'to' => $data['status'],
                ), $actor_id);
            }

            if (isset($data['followup_status']) && $before['followup_status'] !== $data['followup_status']) {
                $this->audit_log('followup_status_changed', 'lead', $lead_id, $lead_label, array(
                    'from' => $before['followup_status'],
                    'to' => $data['followup_status'],
                ), $actor_id);
            }

            if (isset($data['followup_at']) || isset($data['due_at'])) {
                $this->audit_log('followup_rescheduled', 'followup', $lead_id, $lead_label, array(
                    'followup_at' => $data['followup_at'] ?? $before['followup_at'],
                    'due_at' => $data['due_at'] ?? $before['due_at'],
                ), $actor_id);
            }
        }

        if ($has_tag_update && $before) {
            $new_tags = array_map('intval', $params['tags']);
            $removed = array_values(array_diff($before_tags, $new_tags));
            $added = array_values(array_diff($new_tags, $before_tags));
            $lead_label = $this->format_lead_label($before);
            if (!empty($added)) {
                $this->audit_log('tag_added_to_lead', 'tag', $lead_id, $lead_label, array(
                    'tag_ids' => $added,
                ), $actor_id);
            }
            if (!empty($removed)) {
                $this->audit_log('tag_removed_from_lead', 'tag', $lead_id, $lead_label, array(
                    'tag_ids' => $removed,
                ), $actor_id);
            }
        }

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
        $lead = $this->database->get_lead($lead_id, $user_id);
        $deleted = $this->database->delete_lead($lead_id, $user_id);
        if (!$deleted) {
            return new WP_Error('delete_failed', 'Unable to delete lead', array('status' => 500));
        }
        $this->database->log_activity($lead_id, $actor_id, 'deleted');
        if ($lead) {
            $this->audit_log('lead_deleted', 'lead', $lead_id, $this->format_lead_label($lead), array(), $actor_id);
        }
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
        $this->audit_log('lead_bulk_deleted', 'lead', null, null, array('count' => (int) $deleted), $actor_id);
        return rest_ensure_response(array('success' => true, 'deleted' => (int) $deleted));
    }

    public function bulk_assign_leads($request) {
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
        $assigned_to = isset($params['assigned_to']) ? $params['assigned_to'] : null;
        if ($assigned_to !== null && $assigned_to !== '' && $assigned_to !== false) {
            $assigned_id = (int) $assigned_to;
            if ($assigned_id <= 0 || !$this->is_valid_team_member_for_assignment($user_id, $assigned_id)) {
                return new WP_Error('invalid_assigned_to', 'Invalid team member for assignment.', array('status' => 400));
            }
            $assigned_to = $assigned_id;
        } else {
            $assigned_to = null;
        }
        $updated = $this->database->bulk_update_assigned_to($user_id, $ids, $assigned_to);
        if ($updated === false) {
            return new WP_Error('update_failed', 'Unable to update assignments.', array('status' => 500));
        }
        $this->audit_log('lead_bulk_assigned', 'lead', null, null, array('count' => (int) $updated, 'assigned_to' => $assigned_to), $actor_id);
        return rest_ensure_response(array('success' => true, 'updated' => (int) $updated));
    }

    public function get_notes($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $notes = $this->database->get_notes((int) $request['id'], $user_id);
        return rest_ensure_response($notes);
    }

    public function get_followup_history($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $history = $this->database->get_followup_history((int) $request['id'], $user_id);
        return rest_ensure_response($history);
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
        $this->audit_log('note_added', 'note', $note_id, wp_trim_words(wp_strip_all_tags($note), 8, '...'), array(
            'lead_id' => $lead_id,
        ), $actor_id);

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
        $this->audit_log('note_deleted', 'note', $note_id, null, array(), $actor_id);
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
        $this->audit_log('note_updated', 'note', $note_id, wp_trim_words(wp_strip_all_tags($note), 8, '...'), array(), $actor_id);
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
        $this->audit_log('tag_created', 'tag', $tag_id, $name, array(), $actor_id);
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
        $this->audit_log('tag_deleted', 'tag', $tag_id, null, array(), $actor_id);
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
        $this->audit_log('tag_updated', 'tag', $tag_id, $name, array(), $actor_id);
        return rest_ensure_response(array('success' => true));
    }

    public function get_tag_leads($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $tag_id = (int) $request['id'];
        $leads = $this->database->get_leads_by_tag($user_id, $tag_id);
        $leads = array_map(array($this, 'normalize_lead_for_response'), $leads);
        return rest_ensure_response($leads);
    }

    public function get_stages() {
        return rest_ensure_response(array());
    }

    public function get_pipeline_stages($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $stages = $this->get_pipeline_stages_for_user($user_id);
        return rest_ensure_response(array('stages' => $stages));
    }

    public function save_pipeline_stages($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $params = $this->get_request_params($request);
        if (empty($params)) {
            $params = $request->get_params();
        }
        $stages = isset($params['stages']) && is_array($params['stages']) ? $params['stages'] : array();
        $validated = $this->validate_and_sanitize_pipeline_stages($stages);
        if (is_wp_error($validated)) {
            return $validated;
        }
        update_user_meta($user_id, 'lead_aggregator_pipeline_stages', $validated);
        $this->audit_log('settings_changed', 'settings', null, 'Pipeline Stages', array('keys' => array('pipeline_stages')), $actor_id);
        return rest_ensure_response(array('stages' => $validated));
    }

    public function get_lead_count_by_stage($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $params = $this->get_request_params($request);
        if (empty($params)) {
            $params = $request->get_params();
        }
        $stage_key = isset($params['stage']) ? sanitize_key($params['stage']) : '';
        if (!$stage_key) {
            return new WP_Error('missing_stage', 'Stage key required', array('status' => 400));
        }
        $count = $this->database->count_leads_by_status($user_id, $stage_key);
        return rest_ensure_response(array('stage' => $stage_key, 'count' => (int) $count));
    }

    public function migrate_leads_stage($request) {
        $actor_id = get_current_user_id();
        $readonly = $this->ensure_not_read_only($actor_id);
        if (is_wp_error($readonly)) {
            return $readonly;
        }
        $user_id = $this->get_account_user_id($actor_id);
        $params = $this->get_request_params($request);
        $from_status = isset($params['from']) ? sanitize_key($params['from']) : '';
        $to_status = isset($params['to']) ? sanitize_key($params['to']) : '';
        if (!$from_status || !$to_status || $from_status === $to_status) {
            return new WP_Error('invalid_migrate', 'Invalid from/to stage', array('status' => 400));
        }
        $stages = $this->get_pipeline_stages_for_user($user_id);
        $valid_keys = wp_list_pluck($stages, 'key');
        if (!in_array($to_status, $valid_keys, true)) {
            return new WP_Error('invalid_to_stage', 'Target stage not in pipeline', array('status' => 400));
        }
        $updated = $this->database->migrate_leads_to_stage($user_id, $from_status, $to_status);
        $this->audit_log('settings_changed', 'settings', null, 'Pipeline stage migration', array(
            'from' => $from_status,
            'to' => $to_status,
            'count' => (int) $updated,
        ), $actor_id);
        return rest_ensure_response(array('migrated' => (int) $updated));
    }

    private function get_stage_keys_by_type($user_id, $type) {
        $stages = $this->get_pipeline_stages_for_user($user_id);
        $keys = array();
        foreach ($stages as $s) {
            if (isset($s['type']) && $s['type'] === $type && isset($s['key'])) {
                $keys[] = $s['key'];
            }
        }
        return !empty($keys) ? $keys : array($type);
    }

    private function get_pipeline_stages_for_user($user_id) {
        $stored = get_user_meta($user_id, 'lead_aggregator_pipeline_stages', true);
        if (is_array($stored) && !empty($stored)) {
            $sorted = $stored;
            usort($sorted, function ($a, $b) {
                $oa = isset($a['order']) ? (int) $a['order'] : 0;
                $ob = isset($b['order']) ? (int) $b['order'] : 0;
                return $oa - $ob;
            });
            return $sorted;
        }
        return $this->get_default_pipeline_stages();
    }

    private function get_default_pipeline_stages() {
        return array(
            array('key' => 'new', 'label' => 'New', 'order' => 1, 'type' => 'active'),
            array('key' => 'contacted', 'label' => 'Contacted', 'order' => 2, 'type' => 'active'),
            array('key' => 'qualified', 'label' => 'Qualified', 'order' => 3, 'type' => 'active'),
            array('key' => 'won', 'label' => 'Won', 'order' => 99, 'type' => 'won'),
            array('key' => 'lost', 'label' => 'Lost', 'order' => 100, 'type' => 'lost'),
        );
    }

    private function validate_and_sanitize_pipeline_stages($stages) {
        $seen_keys = array();
        $has_won = false;
        $has_lost = false;
        $result = array();
        foreach ($stages as $idx => $stage) {
            if (!is_array($stage)) {
                continue;
            }
            $key = isset($stage['key']) ? sanitize_key($stage['key']) : '';
            if (!$key || preg_match('/[^a-z0-9_]/', $key)) {
                return new WP_Error('invalid_stage_key', 'Invalid or missing stage key at position ' . ($idx + 1), array('status' => 400));
            }
            if (isset($seen_keys[$key])) {
                return new WP_Error('duplicate_stage_key', 'Duplicate stage key: ' . $key, array('status' => 400));
            }
            $seen_keys[$key] = true;
            $type = isset($stage['type']) ? sanitize_key($stage['type']) : 'active';
            if (!in_array($type, array('active', 'won', 'lost'), true)) {
                $type = 'active';
            }
            if ($type === 'won') {
                if ($has_won) {
                    return new WP_Error('multiple_won', 'Only one Won stage allowed', array('status' => 400));
                }
                $has_won = true;
            }
            if ($type === 'lost') {
                if ($has_lost) {
                    return new WP_Error('multiple_lost', 'Only one Lost stage allowed', array('status' => 400));
                }
                $has_lost = true;
            }
            $label = isset($stage['label']) ? sanitize_text_field($stage['label']) : ucfirst($key);
            if (!$label) {
                $label = ucfirst($key);
            }
            $order = isset($stage['order']) ? (int) $stage['order'] : ($idx + 1);
            $result[] = array(
                'key' => $key,
                'label' => $label,
                'order' => $order,
                'type' => $type,
            );
        }
        if (empty($result)) {
            return new WP_Error('empty_stages', 'At least one stage required', array('status' => 400));
        }
        return $result;
    }

    public function create_stage($request) {
        return new WP_Error('stages_disabled', 'Stage customization is disabled.', array('status' => 403));
    }

    public function update_stage($request) {
        return new WP_Error('stages_disabled', 'Stage customization is disabled.', array('status' => 403));
    }

    public function delete_stage($request) {
        return new WP_Error('stages_disabled', 'Stage customization is disabled.', array('status' => 403));
    }

    private function normalize_stage_outcome($value) {
        $value = sanitize_key($value);
        if ($value === 'won' || $value === 'lost') {
            return $value;
        }
        return 'open';
    }

    private function normalize_pipeline_stage($value) {
        $value = sanitize_key($value);
        $map = array(
            'open' => 'new',
            'new' => 'new',
            'contacted' => 'contacted',
            'qualified' => 'qualified',
            'proposal' => 'proposal',
            'won' => 'won',
            'lost' => 'lost',
        );
        if (isset($map[$value])) {
            return $map[$value];
        }
        return $value ? $value : 'new';
    }

    private function normalize_followup_status($value) {
        $value = sanitize_key($value);
        if ($value === 'not_set') {
            return 'not_set';
        }
        if ($value === 'completed' || $value === 'canceled') {
            return $value;
        }
        if ($value === 'scheduled') {
            return 'scheduled';
        }
        return 'not_set';
    }

    private function pipeline_stage_label($value, $user_id = null) {
        if ($user_id) {
            $stages = $this->get_pipeline_stages_for_user($user_id);
            $normalized = $this->normalize_pipeline_stage($value);
            foreach ($stages as $s) {
                if (isset($s['key']) && $s['key'] === $normalized) {
                    return isset($s['label']) ? $s['label'] : ucfirst($normalized);
                }
            }
        }
        $map = array(
            'new' => 'New',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'proposal' => 'Proposal',
            'won' => 'Won',
            'lost' => 'Lost',
        );
        $normalized = $this->normalize_pipeline_stage($value);
        return isset($map[$normalized]) ? $map[$normalized] : (ucfirst(str_replace('_', ' ', $normalized)) ?: 'New');
    }

    private function followup_status_label($value) {
        $map = array(
            'not_set' => 'Not set',
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'canceled' => 'Canceled',
        );
        $normalized = $this->normalize_followup_status($value);
        return isset($map[$normalized]) ? $map[$normalized] : 'Scheduled';
    }

    private function to_utc_datetime($value) {
        if (!$value) {
            return null;
        }
        try {
            $timezone = wp_timezone();
            $date = new DateTime($value, $timezone);
            $date->setTimezone(new DateTimeZone('UTC'));
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }

    private function to_wp_datetime($value) {
        if (!$value) {
            return null;
        }
        try {
            $date = new DateTime($value, new DateTimeZone('UTC'));
            $date->setTimezone(wp_timezone());
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }

    private function normalize_lead_for_response($lead) {
        if (!$lead) {
            return $lead;
        }
        $lead['status'] = $this->normalize_pipeline_stage(isset($lead['status']) ? $lead['status'] : 'new');
        $lead['followup_status'] = $this->normalize_followup_status(isset($lead['followup_status']) ? $lead['followup_status'] : 'not_set');
        $lead['followup_at'] = $this->to_wp_datetime(isset($lead['followup_at']) ? $lead['followup_at'] : null);
        $lead['due_at'] = $this->to_wp_datetime(isset($lead['due_at']) ? $lead['due_at'] : null);
        $lead['last_actioned'] = $this->to_wp_datetime(isset($lead['last_actioned']) ? $lead['last_actioned'] : null);
        $lead['last_contacted'] = $this->to_wp_datetime(isset($lead['last_contacted']) ? $lead['last_contacted'] : null);
        $lead['created_at'] = $this->to_wp_datetime(isset($lead['created_at']) ? $lead['created_at'] : null);
        $lead['updated_at'] = $this->to_wp_datetime(isset($lead['updated_at']) ? $lead['updated_at'] : null);
        $lead['stage_id'] = null;
        return $lead;
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
        $this->audit_log('settings_changed', 'settings', null, 'Business Profile', array(
            'keys' => array_keys($params),
        ), $actor_id);
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
        $leads = array_map(array($this, 'normalize_lead_for_response'), $leads);
        $leads = array_map(array($this, 'normalize_lead_for_response'), $leads);
        $this->audit_log('export_completed', 'export', null, 'Leads CSV', array(
            'count' => count($leads),
        ), $actor_id);

        $temp = fopen('php://temp', 'r+');
        fputcsv($temp, array('ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Company', 'Pipeline Stage', 'Follow-up Status', 'Followup', 'Due', 'Source', 'Created'));
        foreach ($leads as $lead) {
            fputcsv($temp, array(
                $lead['id'],
                $lead['first_name'],
                $lead['last_name'],
                $lead['email'],
                $lead['phone'],
                $lead['company'],
                $this->pipeline_stage_label($lead['status'], $user_id),
                $this->followup_status_label($lead['followup_status']),
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
        $this->audit_log('export_completed', 'export', null, 'Calendar ICS', array(
            'count' => count($leads),
        ), $actor_id);

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

    /**
     * Fetch FluentCart products via HTTP request (fallback when rest_do_request fails).
     *
     * @return array Product list or empty array
     */
    private function fetch_fluentcart_products_via_http() {
        $nonce = wp_create_nonce('wp_rest');
        $url = rest_url('fluent-cart/v2/products');
        $url = add_query_arg(array(
            'per_page' => 100,
            '_wpnonce' => $nonce,
        ), $url);
        $headers = array('X-WP-Nonce' => $nonce);
        if (!empty($_COOKIE)) {
            $parts = array();
            foreach ($_COOKIE as $name => $value) {
                $parts[] = $name . '=' . urlencode($value);
            }
            $headers['Cookie'] = implode('; ', $parts);
        }
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => $headers,
        ));
        if (is_wp_error($response)) {
            return array();
        }
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if ($code !== 200 || empty($body)) {
            return array();
        }
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return array();
        }
        return $this->extract_fluentcart_products_from_response($data);
    }

    /**
     * Extract product array from FluentCart REST API response (handles nested structures).
     *
     * @param mixed $data Response from get_data() or fetch_subscriptions()
     * @return array Product list or empty array
     */
    private function extract_fluentcart_products_from_response($data) {
        if (empty($data)) {
            return array();
        }
        $is_product = function ($item) {
            if (is_array($item)) {
                return isset($item['ID']) || isset($item['id']);
            }
            if (is_object($item)) {
                return isset($item->ID) || isset($item->id);
            }
            return false;
        };
        if (is_array($data)) {
            $first = reset($data);
            if ($first !== false && $is_product($first)) {
                return $data;
            }
        }
        $paths = array(
            array('products', 'products', 'data'),
            array('products', 'data'),
            array('data', 'products', 'products', 'data'),
            array('data', 'products', 'data'),
            array('data'),
            array('items'),
        );
        foreach ($paths as $path) {
            $current = $data;
            foreach ($path as $key) {
                if (is_array($current) && isset($current[$key])) {
                    $current = $current[$key];
                } elseif (is_object($current) && isset($current->$key)) {
                    $current = $current->$key;
                } else {
                    $current = null;
                    break;
                }
            }
            if (is_array($current) && !empty($current)) {
                $first = reset($current);
                if ($first !== false && $is_product($first)) {
                    return $current;
                }
            }
        }
        return array();
    }

    public function get_fluentcart_plans() {
        $helper_available = $this->fluentcart && $this->fluentcart->is_active();
        $items = $helper_available ? $this->fluentcart->fetch_subscriptions() : array();
        $items = $this->extract_fluentcart_products_from_response($items);
        if (empty($items)) {
            $items = array();
            if (function_exists('rest_do_request')) {
                $routes = array(
                    '/fluent-cart/v2/products',
                    '/fluent-cart/v1/products',
                );
                foreach ($routes as $route) {
                    $request = new WP_REST_Request('GET', $route);
                    $request->set_param('per_page', 100);
                    $response = rest_do_request($request);
                    if (is_wp_error($response)) {
                        continue;
                    }
                    if ($response instanceof WP_REST_Response) {
                        $data = $response->get_data();
                        $items = $this->extract_fluentcart_products_from_response($data);
                        if (!empty($items)) {
                            break;
                        }
                    }
                }
            }
            if (empty($items)) {
                $items = $this->fetch_fluentcart_products_via_http();
            }
        }
        if (empty($items)) {
            $message = $helper_available
                ? 'No subscriptions returned from FluentCart (API helper).'
                : 'FluentCart API helper not available. REST fallback also returned no subscriptions.';
            return rest_ensure_response(array(
                'success' => false,
                'data' => array(),
                'message' => $message,
            ));
        }

        $items = $this->extract_fluentcart_products_from_response($items);
        if (empty($items)) {
            $items = array();
        }
        $normalized = array();
        $subscriptionOnly = array();
        if (is_array($items)) {
            foreach ($items as $item) {
                $id = '';
                $label = '';
                $is_subscription = false;
                if (is_array($item)) {
                    $id = isset($item['id']) ? (string) $item['id'] : (isset($item['ID']) ? (string) $item['ID'] : '');
                    $label = isset($item['title']) ? (string) $item['title'] : (isset($item['post_title']) ? (string) $item['post_title'] : (isset($item['name']) ? (string) $item['name'] : ''));
                    $is_subscription = !empty($item['is_subscription']) || (isset($item['product_type']) && $item['product_type'] === 'subscription');
                    if (isset($item['post_status']) && $item['post_status'] !== 'publish') {
                        $is_subscription = false;
                    }
                } elseif (is_object($item)) {
                    $id = isset($item->id) ? (string) $item->id : (isset($item->ID) ? (string) $item->ID : '');
                    $label = isset($item->title) ? (string) $item->title : (isset($item->post_title) ? (string) $item->post_title : (isset($item->name) ? (string) $item->name : ''));
                    $is_subscription = !empty($item->is_subscription) || (isset($item->product_type) && $item->product_type === 'subscription');
                    if (isset($item->post_status) && $item->post_status !== 'publish') {
                        $is_subscription = false;
                    }
                }
                if ($id) {
                    $row = array(
                        'id' => $id,
                        'label' => $label ?: 'Subscription ' . $id,
                    );
                    $normalized[] = $row;
                    if ($is_subscription) {
                        $subscriptionOnly[] = $row;
                    }
                }
            }
        }
        if (!empty($subscriptionOnly)) {
            $normalized = $subscriptionOnly;
        }
        return rest_ensure_response(array(
            'success' => true,
            'data' => $normalized,
        ));
    }

    public function get_fluentcart_debug() {
        $debug = array(
            'helper_available' => $this->fluentcart ? $this->fluentcart->is_active() : false,
            'helpers' => array(
                'fluentCartApi' => function_exists('fluentCartApi'),
                'FluentCartApi' => function_exists('FluentCartApi'),
                'fluentcart_api' => function_exists('fluentcart_api'),
            ),
            'classes' => array(
                'FluentCart\\App\\App' => class_exists('\\FluentCart\\App\\App'),
                'FluentCart\\App\\Models\\Product' => class_exists('\\FluentCart\\App\\Models\\Product'),
                'FluentCart\\App\\Models\\Subscription' => class_exists('\\FluentCart\\App\\Models\\Subscription'),
            ),
            'routes' => array(),
            'products' => array(),
        );

        if (function_exists('rest_get_server')) {
            $routes = rest_get_server()->get_routes();
            foreach ($routes as $route => $handlers) {
                if (strpos($route, 'fluentcart') !== false || strpos($route, 'fluent-cart') !== false) {
                    $debug['routes'][] = $route;
                }
            }
        }

        if (function_exists('rest_do_request')) {
            $routes = array(
                '/fluent-cart/v2/products',
                '/fluent-cart/v1/products',
            );
            foreach ($routes as $route) {
                $request = new WP_REST_Request('GET', $route);
                $request->set_param('per_page', 10);
                $response = rest_do_request($request);
                if ($response instanceof WP_REST_Response) {
                    $data = $response->get_data();
                    if (is_array($data)) {
                        if (isset($data['data']) && is_array($data['data'])) {
                            $debug['products'] = array_slice($data['data'], 0, 10);
                            $debug['products_source'] = $route;
                            break;
                        }
                        if (isset($data['items']) && is_array($data['items'])) {
                            $debug['products'] = array_slice($data['items'], 0, 10);
                            $debug['products_source'] = $route;
                            break;
                        }
                        if (!empty($data)) {
                            $debug['products'] = $data;
                            $debug['products_source'] = $route;
                            break;
                        }
                    }
                }
            }
        }

        return rest_ensure_response(array(
            'success' => true,
            'data' => $debug,
        ));
    }

    public function export_lead($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $lead_id = (int) $request['id'];
        $lead = $this->database->get_lead($lead_id, $user_id);
        if (!$lead) {
            return new WP_Error('not_found', 'Lead not found', array('status' => 404));
        }
        $this->audit_log('export_completed', 'export', $lead_id, $this->format_lead_label($lead), array(
            'type' => 'lead_csv',
        ), $actor_id);
        $lead = $this->normalize_lead_for_response($lead);
        $lead = $this->normalize_lead_for_response($lead);
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
            'Pipeline Stage',
            'Follow-up Status',
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
            $this->pipeline_stage_label($lead['status'], $user_id),
            $this->followup_status_label($lead['followup_status']),
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
        $this->audit_log('export_completed', 'export', $lead_id, $this->format_lead_label($lead), array(
            'type' => 'lead_calendar',
        ), $actor_id);

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

    private function extract_fluentcart_email($data) {
        $candidates = array(
            $data['data']['customer']['email'] ?? null,
            $data['customer']['email'] ?? null,
            $data['customer_email'] ?? null,
            $data['email'] ?? null,
            $data['subscriber']['email'] ?? null,
        );
        foreach ($candidates as $candidate) {
            if ($candidate) {
                return sanitize_email($candidate);
            }
        }
        return '';
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
        $this->audit_log('webhook_created', 'webhook', $source_id, $source_key, array(), $actor_id);
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
        $this->audit_log('webhook_deleted', 'webhook', $source_id, $source ? $source['source_key'] : null, array(), $actor_id);
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
        $this->audit_log('webhook_updated', 'webhook', $source_id, $source['source_key'], array(
            'regenerated' => !empty($params['regenerate']),
        ), $actor_id);
        return rest_ensure_response(array('success' => true));
    }

    private function generate_shared_secret() {
        return wp_generate_password(24, false, false);
    }

    public function get_billing_status() {
        $user_id = get_current_user_id();
        $plans = $this->billing->get_plans();
        $status = $this->billing->get_subscription_status($user_id);
        $entitlements = lead_aggregator_get_entitlements($user_id);
        $plan_key = $entitlements['plan_key'];
        $plan = $plan_key && isset($plans[$plan_key]) ? $plans[$plan_key] : null;

        $public_plans = array();
        foreach ($plans as $key => $plan_data) {
            $public_plans[$key] = array(
                'key' => $key,
                'label' => $plan_data['label'],
                'limit' => isset($plan_data['lead_limit']) ? (int) $plan_data['lead_limit'] : 0,
            );
        }

        return rest_ensure_response(array(
            'status' => $status ? $status : 'inactive',
            'plan_key' => $plan_key,
            'plan_label' => $plan ? $plan['label'] : '',
            'plan_limit' => $entitlements['lead_limit'],
            'current_period_end' => (int) get_user_meta($user_id, 'lead_aggregator_subscription_period_end', true),
            'lead_count' => $entitlements['current_lead_count'],
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
                'limit' => isset($plan_data['lead_limit']) ? (int) $plan_data['lead_limit'] : 0,
            );
        }

        return rest_ensure_response(array('plans' => $public_plans));
    }

    public function handle_fluentcart_webhook($request) {
        if ($this->is_rate_limited('fluentcart:webhook', 60, 60)) {
            return new WP_Error('rate_limited', 'Too many requests. Please try again later.', array('status' => 429));
        }
        if (!$this->fluentcart) {
            return new WP_Error('missing_integration', 'FluentCart integration is not available.', array('status' => 400));
        }
        $secret = $this->fluentcart->get_webhook_secret();
        if (!$secret) {
            return new WP_Error('missing_secret', 'Webhook secret not configured.', array('status' => 400));
        }
        $payload = $request->get_body();
        $signature = $request->get_header('x-fluentcart-signature');
        if (!$signature) {
            $signature = $request->get_header('x-fc-signature');
        }
        if (!$signature) {
            $signature = $request->get_header('x-signature');
        }
        $secret_header = $request->get_header('x-webhook-secret');
        if (!$secret_header) {
            $secret_header = $request->get_header('x-fluentcart-secret');
        }
        if (is_string($secret_header)) {
            $secret_header = trim($secret_header);
        }
        $valid = false;
        if ($signature) {
            $valid = $this->fluentcart->verify_signature($payload, $signature, $secret);
        }
        if (!$valid && $secret_header && hash_equals($secret, $secret_header)) {
            $valid = true;
        }
        if (!$valid) {
            $debug = $this->fluentcart_webhook_debug($request, $signature, $secret_header, $secret);
            $this->log_fluentcart_webhook(null, 'failed', 'Invalid webhook signature. ' . $debug, $payload);
            return new WP_Error('invalid_signature', 'Invalid webhook signature.', array('status' => 401));
        }

        $data = json_decode($payload, true);
        if (!is_array($data)) {
            $this->log_fluentcart_webhook(null, 'failed', 'Invalid webhook payload', $payload);
            return new WP_Error('invalid_payload', 'Invalid webhook payload.', array('status' => 400));
        }
        $event_id = $data['event_id'] ?? ($data['id'] ?? '');
        $event_id = $event_id ? sanitize_text_field($event_id) : '';
        if ($event_id) {
            $key = 'lead_aggregator_fc_event_' . md5($event_id);
            if (get_transient($key)) {
                return rest_ensure_response(array('success' => true, 'skipped' => true));
            }
            set_transient($key, 1, DAY_IN_SECONDS);
        }

        $email = $this->extract_fluentcart_email($data);
        if (!$email) {
            $this->log_fluentcart_webhook(null, 'success', 'No user email in payload', $payload);
            return rest_ensure_response(array('success' => true, 'message' => 'No user email found.'));
        }
        $user = get_user_by('email', $email);
        if (!$user) {
            $this->log_fluentcart_webhook(null, 'success', 'No matching WordPress user: ' . $email, $payload);
            return rest_ensure_response(array('success' => true, 'message' => 'No matching user.'));
        }

        $before = array(
            'plan_key' => get_user_meta($user->ID, 'lead_aggregator_plan_key', true),
            'lead_limit' => (int) get_user_meta($user->ID, 'lead_aggregator_lead_limit', true),
        );
        $plans = $this->billing->get_plans();
        $resolved = $this->fluentcart->resolve_plan_for_user($user->ID, $plans);

        if ((!$resolved['plan_key'] || $resolved['plan_key'] === 'none') && !empty($data['subscriptions']) && is_array($data['subscriptions'])) {
            $from_payload = $this->fluentcart->match_plan_from_subscriptions($data['subscriptions'], $plans);
            if ($from_payload['plan_key'] && $from_payload['plan_key'] !== 'none') {
                $resolved = $from_payload;
            }
        }

        $this->fluentcart->set_cached_plan($user->ID, $resolved['plan_key'], $resolved['lead_limit'], $resolved['source']);

        if ($resolved['plan_key'] && $resolved['plan_key'] !== 'none') {
            update_user_meta($user->ID, 'lead_aggregator_access_enabled', 1);
        } else {
            update_user_meta($user->ID, 'lead_aggregator_access_enabled', 0);
        }

        $after = array(
            'plan_key' => $resolved['plan_key'],
            'lead_limit' => $resolved['lead_limit'],
        );
        $this->audit_log('plan_changed', 'billing', $user->ID, $user->user_email, array(
            'before' => $before,
            'after' => $after,
        ), $user->ID);

        $this->log_fluentcart_webhook($user->ID, 'success', 'Plan updated: ' . $resolved['plan_key'], $payload);
        return rest_ensure_response(array('success' => true));
    }

    /**
     * Build debug string for FluentCart webhook verification failure. Never includes secret values.
     *
     * @param \WP_REST_Request $request
     * @param string|null      $signature
     * @param string|null      $secret_header
     * @param string          $stored_secret
     * @return string
     */
    private function fluentcart_webhook_debug($request, $signature, $secret_header, $stored_secret) {
        $headers = $request->get_headers();
        $names = is_array($headers) ? array_keys($headers) : array();
        $auth_related = array();
        foreach ($names as $name) {
            $lower = strtolower($name);
            if (strpos($lower, 'signature') !== false || strpos($lower, 'secret') !== false
                || strpos($lower, 'auth') !== false || strpos($lower, 'webhook') !== false) {
                $auth_related[] = $name;
            }
        }
        $parts = array(
            'headers_with_secret/signature/auth: ' . (count($auth_related) ? implode(', ', $auth_related) : 'none'),
            'x-fluentcart-signature: ' . ($signature ? 'present(len=' . strlen($signature) . ')' : 'absent'),
            'x-webhook-secret: ' . ($secret_header !== null && $secret_header !== '' ? 'present(len=' . strlen($secret_header) . ')' : 'absent'),
            'stored_secret_len: ' . strlen($stored_secret),
        );
        if ($secret_header !== null && $secret_header !== '') {
            $parts[] = 'length_match: ' . (strlen($secret_header) === strlen($stored_secret) ? 'yes' : 'no');
            $parts[] = 'first_char_match: ' . (substr($secret_header, 0, 1) === substr($stored_secret, 0, 1) ? 'yes' : 'no');
        }
        return '[DEBUG ' . implode('; ', $parts) . ']';
    }

    private function log_fluentcart_webhook($user_id, $status, $message, $payload = null) {
        if ((int) get_option('lead_aggregator_webhook_logging', 1) !== 1) {
            return;
        }
        if (is_string($payload) && strlen($payload) > 10000) {
            $payload = substr($payload, 0, 10000) . '...[truncated]';
        }
        $this->database->log_webhook(array(
            'user_id' => $user_id ? (int) $user_id : null,
            'source_key' => 'fluentcart',
            'status' => $status,
            'message' => $message,
            'payload' => $payload,
        ));
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

        $this->audit_log('settings_changed', 'settings', null, 'Notifications', array(
            'time' => $time,
            'timezone' => $timezone,
        ), $user_id);
        return rest_ensure_response(array('success' => true));
    }

    public function get_custom_fields() {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $config = $this->get_custom_fields_config($user_id);
        return rest_ensure_response(array(
            'fields' => $config['labels'],
            'followup' => $config['followup'],
        ));
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
        $followup = isset($params['followup']) && is_array($params['followup']) ? $params['followup'] : array();
        $payload = array();
        for ($i = 1; $i <= 10; $i += 1) {
            $key = 'custom_' . $i;
            if (array_key_exists($key, $fields)) {
                $payload[$key] = sanitize_text_field($fields[$key]);
            }
        }
        $followup_payload = array();
        for ($i = 1; $i <= 10; $i += 1) {
            $key = 'custom_' . $i;
            $followup_payload[$key] = !empty($followup[$key]) ? 1 : 0;
        }
        update_user_meta($user_id, 'lead_aggregator_custom_fields', array(
            'labels' => $payload,
            'followup' => $followup_payload,
        ));
        $this->audit_log('settings_changed', 'settings', null, 'Custom Fields', array(
            'fields' => $payload,
            'followup' => $followup_payload,
        ), $actor_id);
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

    public function get_get_started_settings() {
        $user_id = get_current_user_id();
        $enabled = get_user_meta($user_id, 'lead_aggregator_show_get_started', true);
        if ($enabled === '') {
            $enabled = 1;
        }
        return rest_ensure_response(array(
            'enabled' => (int) $enabled,
        ));
    }

    public function save_get_started_settings($request) {
        $user_id = get_current_user_id();
        $params = $this->get_request_params($request);
        $enabled = isset($params['enabled']) ? (int) $params['enabled'] : 1;
        update_user_meta($user_id, 'lead_aggregator_show_get_started', $enabled ? 1 : 0);
        $this->audit_log('settings_changed', 'settings', null, 'Get Started', array(
            'enabled' => $enabled ? 1 : 0,
        ), $user_id);
        return rest_ensure_response(array(
            'enabled' => $enabled ? 1 : 0,
        ));
    }

    public function get_quick_action_settings() {
        $user_id = get_current_user_id();
        $settings = get_user_meta($user_id, 'lead_aggregator_quick_action', true);
        $status = isset($settings['status']) ? $this->normalize_pipeline_stage($settings['status']) : 'contacted';
        $followup_status = isset($settings['followup_status']) ? $this->normalize_followup_status($settings['followup_status']) : 'scheduled';
        return rest_ensure_response(array(
            'status' => $status,
            'followup_status' => $followup_status,
        ));
    }

    public function save_quick_action_settings($request) {
        $user_id = get_current_user_id();
        $params = $this->get_request_params($request);
        $status = isset($params['status']) ? $this->normalize_pipeline_stage($params['status']) : 'contacted';
        $followup_status = isset($params['followup_status']) ? $this->normalize_followup_status($params['followup_status']) : 'scheduled';
        update_user_meta($user_id, 'lead_aggregator_quick_action', array(
            'status' => $status,
            'followup_status' => $followup_status,
        ));
        $this->audit_log('settings_changed', 'settings', null, 'Quick Action', array(
            'status' => $status,
            'followup_status' => $followup_status,
        ), $user_id);
        return rest_ensure_response(array(
            'status' => $status,
            'followup_status' => $followup_status,
        ));
    }

    public function get_appearance_settings() {
        $user_id = get_current_user_id();
        $enabled = get_user_meta($user_id, 'lead_aggregator_dark_mode', true);
        if ($enabled === '') {
            $enabled = 0;
        }
        return rest_ensure_response(array(
            'dark_mode' => (int) $enabled,
        ));
    }

    public function save_appearance_settings($request) {
        $user_id = get_current_user_id();
        $params = $this->get_request_params($request);
        $enabled = isset($params['dark_mode']) ? (int) $params['dark_mode'] : 0;
        update_user_meta($user_id, 'lead_aggregator_dark_mode', $enabled ? 1 : 0);
        $this->audit_log('settings_changed', 'settings', null, 'Appearance', array(
            'dark_mode' => $enabled ? 1 : 0,
        ), $user_id);
        return rest_ensure_response(array(
            'dark_mode' => $enabled ? 1 : 0,
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

    public function get_team_assignable() {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $manager = get_userdata($user_id);
        if (!$manager) {
            return rest_ensure_response(array('users' => array()));
        }
        $users = get_users(array(
            'meta_key' => 'lead_aggregator_manager_id',
            'meta_value' => $user_id,
            'number' => 200,
            'fields' => array('ID', 'display_name', 'user_email'),
        ));
        $team = array();
        $team[] = array(
            'id' => (int) $manager->ID,
            'name' => $manager->display_name,
            'email' => $manager->user_email,
        );
        foreach ($users as $user) {
            $team[] = array(
                'id' => (int) $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email,
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

        $this->audit_log('team_member_added', 'team_member', (int) $user_id, $name ?: $email, array(
            'email' => $email,
            'access_level' => $access_level,
        ), $manager_id);
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
            $this->audit_log('team_password_reset', 'team_member', $user_id, null, array(), $manager_id);
        }
        if (isset($params['access_level'])) {
            $this->audit_log('team_role_changed', 'team_member', $user_id, null, array(
                'access_level' => sanitize_key($params['access_level']),
            ), $manager_id);
        }
        if (isset($params['access_enabled'])) {
            $this->audit_log('team_access_toggled', 'team_member', $user_id, null, array(
                'access_enabled' => (int) !!$params['access_enabled'],
            ), $manager_id);
        }
        return rest_ensure_response($response);
    }

    public function get_activity_log($request) {
        $manager_id = get_current_user_id();
        $error = $this->require_manager_user($manager_id);
        if (is_wp_error($error)) {
            return $error;
        }
        $workspace_id = $this->audit->resolve_workspace_id($manager_id);
        if (!$workspace_id) {
            return new WP_Error('invalid_workspace', 'Workspace not found', array('status' => 404));
        }

        $params = $this->get_request_params($request);
        if (empty($params)) {
            $params = $request->get_params();
        }
        $range = $this->parse_activity_range($params);
        $page = isset($params['page']) ? max(1, (int) $params['page']) : 1;
        $per_page = isset($params['per_page']) ? (int) $params['per_page'] : 25;
        if (!in_array($per_page, array(25, 50, 100), true)) {
            $per_page = 25;
        }
        $offset = ($page - 1) * $per_page;

        $filters = $this->build_activity_filters($workspace_id, $params, $range);
        global $wpdb;
        $table = $this->database->table_name('audit_log');
        $count_sql = "SELECT COUNT(*) FROM {$table} {$filters['where']}";
        $count = (int) $wpdb->get_var($wpdb->prepare($count_sql, $filters['params']));

        $query_sql = "SELECT * FROM {$table} {$filters['where']} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $query_params = array_merge($filters['params'], array($per_page, $offset));
        $rows = $wpdb->get_results($wpdb->prepare($query_sql, $query_params), ARRAY_A);
        foreach ($rows as &$row) {
            $row['metadata'] = $row['metadata_json'] ? json_decode($row['metadata_json'], true) : null;
            unset($row['metadata_json']);
            $row['action_display'] = $this->format_activity_action_display($row['action'], $row['metadata'], $row['entity_label'], $row['entity_type'], $workspace_id);
        }

        return rest_ensure_response(array(
            'rows' => $rows,
            'total' => $count,
            'page' => $page,
            'per_page' => $per_page,
            'pages' => $per_page ? (int) ceil($count / $per_page) : 1,
            'range' => $range,
        ));
    }

    public function export_activity_log($request) {
        $manager_id = get_current_user_id();
        $error = $this->require_manager_user($manager_id);
        if (is_wp_error($error)) {
            return $error;
        }
        $workspace_id = $this->audit->resolve_workspace_id($manager_id);
        if (!$workspace_id) {
            return new WP_Error('invalid_workspace', 'Workspace not found', array('status' => 404));
        }
        $params = $this->get_request_params($request);
        $range = $this->parse_activity_range($params);
        $filters = $this->build_activity_filters($workspace_id, $params, $range);

        global $wpdb;
        $table = $this->database->table_name('audit_log');
        $query_sql = "SELECT * FROM {$table} {$filters['where']} ORDER BY created_at DESC LIMIT 5000";
        $rows = $wpdb->get_results($wpdb->prepare($query_sql, $filters['params']), ARRAY_A);

        $temp = fopen('php://temp', 'r+');
        fputcsv($temp, array('Time', 'Team Member', 'Email', 'Action', 'Description', 'Entity Type', 'Entity Label', 'Metadata'));
        foreach ($rows as $row) {
            $meta = !empty($row['metadata_json']) ? json_decode($row['metadata_json'], true) : null;
            $action_display = $this->format_activity_action_display($row['action'], $meta, $row['entity_label'], $row['entity_type'], $workspace_id);
            fputcsv($temp, array(
                $row['created_at'],
                $row['actor_name'],
                $row['actor_email'],
                $row['action'],
                $action_display,
                $row['entity_type'],
                $row['entity_label'],
                $row['metadata_json'],
            ));
        }
        rewind($temp);
        $csv = stream_get_contents($temp);
        fclose($temp);

        add_filter('rest_pre_serve_request', function ($served, $result, $request, $server) {
            if ($request->get_route() !== '/lead-aggregator/v1/activity/export') {
                return $served;
            }
            $data = $result instanceof WP_REST_Response ? $result->get_data() : $result;
            if (!is_string($data)) {
                return $served;
            }
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="activity-audit-log.csv"');
            echo $data;
            return true;
        }, 10, 4);

        $this->audit_log('export_completed', 'audit_log', null, 'Activity Export', array(), $manager_id);
        $response = new WP_REST_Response($csv, 200);
        $response->header('Content-Type', 'text/csv; charset=utf-8');
        $response->header('Content-Disposition', 'attachment; filename="activity-audit-log.csv"');
        return $response;
    }

    public function get_activity_reporting($request) {
        $manager_id = get_current_user_id();
        $error = $this->require_manager_user($manager_id);
        if (is_wp_error($error)) {
            return $error;
        }
        $workspace_id = $this->audit->resolve_workspace_id($manager_id);
        if (!$workspace_id) {
            return new WP_Error('invalid_workspace', 'Workspace not found', array('status' => 404));
        }
        $params = $this->get_request_params($request);
        if (empty($params)) {
            $params = $request->get_params();
        }
        $range = $this->parse_activity_range($params);
        $team_member_id = isset($params['team_member_id']) ? (int) $params['team_member_id'] : 0;
        if ($team_member_id > 0 && !$this->is_valid_team_member_for_assignment($workspace_id, $team_member_id)) {
            $team_member_id = 0;
        }

        global $wpdb;
        $table = $this->database->table_name('audit_log');
        $leads_table = $this->database->table_name('leads');
        $start = $range['start'];
        $end = $range['end'];
        $actor_filter = $team_member_id > 0 ? $wpdb->prepare(' AND actor_user_id = %d', $team_member_id) : '';

        $leads_created = 0;
        $leads_assigned = 0;
        if ($team_member_id > 0) {
            $leads_assigned = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$leads_table} WHERE user_id = %d AND assigned_to = %d AND created_at BETWEEN %s AND %s",
                $workspace_id,
                $team_member_id,
                $start,
                $end
            ));
            $leads_created = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE workspace_id = %d AND action = %s AND created_at BETWEEN %s AND %s" . $actor_filter,
                $workspace_id,
                'lead_created',
                $start,
                $end
            ));
        } else {
            $leads_created = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE workspace_id = %d AND action = %s AND created_at BETWEEN %s AND %s",
                $workspace_id,
                'lead_created',
                $start,
                $end
            ));
        }
        $followups_completed = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE workspace_id = %d AND action = %s AND metadata_json LIKE %s AND created_at BETWEEN %s AND %s" . $actor_filter,
            $workspace_id,
            'followup_status_changed',
            '%\"to\":\"completed\"%',
            $start,
            $end
        ));
        $leads_contacted = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE workspace_id = %d AND action = %s AND metadata_json LIKE %s AND created_at BETWEEN %s AND %s" . $actor_filter,
            $workspace_id,
            'pipeline_stage_changed',
            '%\"to\":\"contacted\"%',
            $start,
            $end
        ));
        $won_keys = $this->get_stage_keys_by_type($workspace_id, 'won');
        $lost_keys = $this->get_stage_keys_by_type($workspace_id, 'lost');
        $won_like = array();
        foreach ($won_keys as $k) {
            $won_like[] = '%"to":"' . $wpdb->esc_like($k) . '"%';
        }
        $lost_like = array();
        foreach ($lost_keys as $k) {
            $lost_like[] = '%"to":"' . $wpdb->esc_like($k) . '"%';
        }
        $won_count = 0;
        if (!empty($won_like)) {
            $won_conds = implode(' OR ', array_fill(0, count($won_like), 'metadata_json LIKE %s'));
            $won_count = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE workspace_id = %d AND action = %s AND ({$won_conds}) AND created_at BETWEEN %s AND %s" . $actor_filter,
                array_merge(array($workspace_id, 'pipeline_stage_changed'), $won_like, array($start, $end))
            ));
        }
        $lost_count = 0;
        if (!empty($lost_like)) {
            $lost_conds = implode(' OR ', array_fill(0, count($lost_like), 'metadata_json LIKE %s'));
            $lost_count = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE workspace_id = %d AND action = %s AND ({$lost_conds}) AND created_at BETWEEN %s AND %s" . $actor_filter,
                array_merge(array($workspace_id, 'pipeline_stage_changed'), $lost_like, array($start, $end))
            ));
        }
        $active_members = $team_member_id > 0 ? 1 : (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT actor_user_id) FROM {$table} WHERE workspace_id = %d AND created_at BETWEEN %s AND %s",
            $workspace_id,
            $start,
            $end
        ));

        $avg_followup_minutes = $this->calculate_avg_followup_time($workspace_id, $start, $end, $team_member_id);
        $win_rate = ($won_count + $lost_count) > 0 ? round(($won_count / ($won_count + $lost_count)) * 100, 1) : 0;

        $events_over_time = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as day, COUNT(*) as total FROM {$table} WHERE workspace_id = %d AND created_at BETWEEN %s AND %s" . $actor_filter . " GROUP BY day ORDER BY day ASC",
            $workspace_id,
            $start,
            $end
        ), ARRAY_A);

        $by_member = $wpdb->get_results($wpdb->prepare(
            "SELECT actor_user_id, actor_name, COUNT(*) as total FROM {$table} WHERE workspace_id = %d AND created_at BETWEEN %s AND %s" . $actor_filter . " GROUP BY actor_user_id ORDER BY total DESC LIMIT 10",
            $workspace_id,
            $start,
            $end
        ), ARRAY_A);

        $top_actions = $wpdb->get_results($wpdb->prepare(
            "SELECT action, COUNT(*) as total FROM {$table} WHERE workspace_id = %d AND created_at BETWEEN %s AND %s" . $actor_filter . " GROUP BY action ORDER BY total DESC LIMIT 10",
            $workspace_id,
            $start,
            $end
        ), ARRAY_A);

        $stage_changes = $this->aggregate_stage_changes($workspace_id, $start, $end, $team_member_id);
        $followup_completion = $this->aggregate_followup_completion($workspace_id, $start, $end, $team_member_id);

        return rest_ensure_response(array(
            'range' => $range,
            'summary' => array(
                'leads_created' => $leads_created,
                'leads_assigned' => $leads_assigned,
                'leads_contacted' => $leads_contacted,
                'followups_completed' => $followups_completed,
                'avg_time_to_first_followup' => $avg_followup_minutes,
                'win_rate' => $win_rate,
                'active_team_members' => $active_members,
            ),
            'charts' => array(
                'events_over_time' => $events_over_time,
                'by_member' => $by_member,
                'stage_changes' => $stage_changes,
                'followup_completion' => $followup_completion,
                'top_actions' => $top_actions,
            ),
        ));
    }

    public function get_won_analytics($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $params = $this->get_request_params($request);
        if (empty($params)) {
            $params = $request->get_params();
        }
        $range = $this->parse_activity_range($params);
        $won_keys = $this->get_stage_keys_by_type($user_id, 'won');
        $lost_keys = $this->get_stage_keys_by_type($user_id, 'lost');
        $data = $this->database->get_won_lost_analytics($user_id, 'won', $range['start'], $range['end'], $won_keys, $lost_keys);
        $win_rate = $data['total_leads'] > 0 ? round(($data['won_count'] / $data['total_leads']) * 100, 1) : null;
        return rest_ensure_response(array(
            'range' => $range,
            'win_rate' => $win_rate,
            'won_count' => $data['won_count'],
            'total_leads' => $data['total_leads'],
            'avg_days_to_close' => $data['avg_days_to_close'],
            'wins_in_period' => $data['wins_in_period'],
        ));
    }

    public function get_lost_analytics($request) {
        $actor_id = get_current_user_id();
        $user_id = $this->get_account_user_id($actor_id);
        $params = $this->get_request_params($request);
        if (empty($params)) {
            $params = $request->get_params();
        }
        $range = $this->parse_activity_range($params);
        $won_keys = $this->get_stage_keys_by_type($user_id, 'won');
        $lost_keys = $this->get_stage_keys_by_type($user_id, 'lost');
        $data = $this->database->get_won_lost_analytics($user_id, 'lost', $range['start'], $range['end'], $won_keys, $lost_keys);
        $loss_rate = $data['total_leads'] > 0 ? round(($data['lost_count'] / $data['total_leads']) * 100, 1) : null;
        return rest_ensure_response(array(
            'range' => $range,
            'loss_rate' => $loss_rate,
            'lost_count' => $data['lost_count'],
            'total_leads' => $data['total_leads'],
            'avg_days_to_loss' => $data['avg_days_to_loss'],
            'top_loss_reason' => $data['top_loss_reason'],
            'top_loss_reason_pct' => $data['top_loss_reason_pct'],
        ));
    }

    public function seed_activity_log($request) {
        if (!current_user_can('manage_options')) {
            return new WP_Error('not_allowed', 'Not allowed.', array('status' => 403));
        }
        $manager_id = get_current_user_id();
        $workspace_id = $this->audit->resolve_workspace_id($manager_id);
        if (!$workspace_id) {
            return new WP_Error('invalid_workspace', 'Workspace not found', array('status' => 404));
        }
        $actions = array('lead_created', 'lead_updated', 'followup_status_changed', 'note_added', 'tag_created', 'webhook_fired', 'settings_changed');
        for ($i = 0; $i < 25; $i++) {
            $action = $actions[array_rand($actions)];
            $this->audit->log($workspace_id, $manager_id, $action, 'seed', null, 'Seed Event', array('seed' => true));
        }
        return rest_ensure_response(array('success' => true));
    }

    private function parse_activity_range($params) {
        $range = isset($params['range']) ? sanitize_text_field($params['range']) : 'last_30';
        $now = current_time('timestamp', true);
        $start = $now - DAY_IN_SECONDS * 30;
        if ($range === 'last_7') {
            $start = $now - DAY_IN_SECONDS * 7;
        } elseif ($range === 'last_90') {
            $start = $now - DAY_IN_SECONDS * 90;
        } elseif ($range === 'custom') {
            $start_param = isset($params['start']) ? sanitize_text_field($params['start']) : '';
            $end_param = isset($params['end']) ? sanitize_text_field($params['end']) : '';
            if ($start_param) {
                $start = strtotime($start_param . ' 00:00:00');
            }
            if ($end_param) {
                $now = strtotime($end_param . ' 23:59:59');
            }
        }
        $start_mysql = gmdate('Y-m-d H:i:s', $start);
        $end_mysql = gmdate('Y-m-d H:i:s', $now);
        return array(
            'range' => $range,
            'start' => $start_mysql,
            'end' => $end_mysql,
        );
    }

    private function build_activity_filters($workspace_id, $params, $range) {
        $where = array('workspace_id = %d', 'created_at BETWEEN %s AND %s');
        $values = array($workspace_id, $range['start'], $range['end']);
        $actor = isset($params['actor']) ? (int) $params['actor'] : 0;
        if ($actor > 0) {
            $where[] = 'actor_user_id = %d';
            $values[] = $actor;
        }
        if (!empty($params['action'])) {
            $where[] = 'action = %s';
            $values[] = sanitize_key($params['action']);
        }
        if (!empty($params['entity_type'])) {
            $where[] = 'entity_type = %s';
            $values[] = sanitize_key($params['entity_type']);
        }
        if (!empty($params['search'])) {
            $like = '%' . $this->esc_like(sanitize_text_field($params['search'])) . '%';
            $where[] = '(entity_label LIKE %s OR metadata_json LIKE %s OR actor_name LIKE %s OR actor_email LIKE %s)';
            $values[] = $like;
            $values[] = $like;
            $values[] = $like;
            $values[] = $like;
        }
        return array(
            'where' => 'WHERE ' . implode(' AND ', $where),
            'params' => $values,
        );
    }

    private function esc_like($text) {
        global $wpdb;
        return $wpdb->esc_like($text);
    }

    private function aggregate_stage_changes($workspace_id, $start, $end, $actor_id = 0) {
        global $wpdb;
        $table = $this->database->table_name('audit_log');
        $actor_filter = $actor_id > 0 ? $wpdb->prepare(' AND actor_user_id = %d', $actor_id) : '';
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT created_at, metadata_json FROM {$table} WHERE workspace_id = %d AND action = %s AND created_at BETWEEN %s AND %s" . $actor_filter,
            $workspace_id,
            'pipeline_stage_changed',
            $start,
            $end
        ), ARRAY_A);
        $bucket = array();
        foreach ($rows as $row) {
            $day = substr($row['created_at'], 0, 10);
            $meta = $row['metadata_json'] ? json_decode($row['metadata_json'], true) : array();
            $to = isset($meta['to']) ? $meta['to'] : 'unknown';
            if (!isset($bucket[$day])) {
                $bucket[$day] = array();
            }
            if (!isset($bucket[$day][$to])) {
                $bucket[$day][$to] = 0;
            }
            $bucket[$day][$to] += 1;
        }
        $output = array();
        foreach ($bucket as $day => $values) {
            $output[] = array(
                'day' => $day,
                'values' => $values,
            );
        }
        return $output;
    }

    private function aggregate_followup_completion($workspace_id, $start, $end, $actor_id = 0) {
        global $wpdb;
        $table = $this->database->table_name('audit_log');
        $actor_filter = $actor_id > 0 ? $wpdb->prepare(' AND actor_user_id = %d', $actor_id) : '';
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT created_at, metadata_json FROM {$table} WHERE workspace_id = %d AND action = %s AND created_at BETWEEN %s AND %s" . $actor_filter,
            $workspace_id,
            'followup_status_changed',
            $start,
            $end
        ), ARRAY_A);
        $bucket = array();
        foreach ($rows as $row) {
            $day = substr($row['created_at'], 0, 10);
            $meta = $row['metadata_json'] ? json_decode($row['metadata_json'], true) : array();
            if (empty($meta['to'])) {
                continue;
            }
            if (!isset($bucket[$day])) {
                $bucket[$day] = array('completed' => 0, 'total' => 0);
            }
            $bucket[$day]['total'] += 1;
            if ($meta['to'] === 'completed') {
                $bucket[$day]['completed'] += 1;
            }
        }
        $output = array();
        foreach ($bucket as $day => $values) {
            $rate = $values['total'] ? round(($values['completed'] / $values['total']) * 100, 1) : 0;
            $output[] = array(
                'day' => $day,
                'completed' => $values['completed'],
                'total' => $values['total'],
                'rate' => $rate,
            );
        }
        return $output;
    }

    private function calculate_avg_followup_time($workspace_id, $start, $end, $assigned_to = 0) {
        global $wpdb;
        $leads_table = $this->database->table_name('leads');
        $assigned_filter = $assigned_to > 0 ? $wpdb->prepare(' AND assigned_to = %d', $assigned_to) : '';
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT created_at, followup_at FROM {$leads_table} WHERE user_id = %d AND created_at BETWEEN %s AND %s AND followup_at IS NOT NULL" . $assigned_filter,
            $workspace_id,
            $start,
            $end
        ), ARRAY_A);
        if (empty($rows)) {
            return 0;
        }
        $total = 0;
        $count = 0;
        foreach ($rows as $row) {
            $created = strtotime($row['created_at']);
            $followup = strtotime($row['followup_at']);
            if ($created && $followup && $followup >= $created) {
                $total += ($followup - $created);
                $count += 1;
            }
        }
        if (!$count) {
            return 0;
        }
        return (int) round($total / $count / 60);
    }

    private function require_manager_user($user_id) {
        $manager_id = (int) get_user_meta($user_id, 'lead_aggregator_manager_id', true);
        if ($manager_id > 0) {
            return new WP_Error('not_allowed', 'Sub-accounts cannot manage team members.', array('status' => 403));
        }
        return true;
    }

    private function is_valid_team_member_for_assignment($manager_id, $assigned_id) {
        if ($assigned_id <= 0) {
            return false;
        }
        if ((int) $assigned_id === (int) $manager_id) {
            return true;
        }
        $member_manager = (int) get_user_meta($assigned_id, 'lead_aggregator_manager_id', true);
        return $member_manager === (int) $manager_id;
    }

    private function get_team_member_ids_for_assignment($manager_id) {
        $ids = array((int) $manager_id);
        $users = get_users(array(
            'meta_key' => 'lead_aggregator_manager_id',
            'meta_value' => $manager_id,
            'number' => 200,
            'fields' => 'ID',
        ));
        foreach ($users as $user) {
            $ids[] = (int) $user->ID;
        }
        return $ids;
    }

    private function audit_log($action, $entity_type, $entity_id = null, $entity_label = null, $metadata = array(), $actor_id = null) {
        if (!$this->audit) {
            return;
        }
        $actor_id = $actor_id ? (int) $actor_id : (int) get_current_user_id();
        $workspace_id = $this->audit->resolve_workspace_id($actor_id);
        if (!$workspace_id) {
            return;
        }
        $this->audit->log($workspace_id, $actor_id, $action, $entity_type, $entity_id, $entity_label, $metadata);
    }

    private function format_lead_label($lead) {
        if (!is_array($lead)) {
            return '';
        }
        $name = trim(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? ''));
        if ($name) {
            return $name;
        }
        if (!empty($lead['email'])) {
            return $lead['email'];
        }
        if (!empty($lead['id'])) {
            return 'Lead #' . $lead['id'];
        }
        return 'Lead';
    }

    private function format_activity_action_display($action, $metadata, $entity_label, $entity_type, $workspace_id) {
        $meta = is_array($metadata) ? $metadata : array();
        $label = $entity_label ? sanitize_text_field($entity_label) : '';
        $resolve_user = function ($user_id) {
            if (!$user_id) {
                return null;
            }
            $user = get_user_by('id', (int) $user_id);
            return $user ? ($user->display_name ?: $user->user_email) : null;
        };

        $format_stage = function ($val) use ($workspace_id) {
            $stages = $this->get_pipeline_stages_for_user($workspace_id);
            foreach ($stages as $s) {
                if (isset($s['key']) && $s['key'] === $val) {
                    return isset($s['label']) ? $s['label'] : ucfirst($val);
                }
            }
            return ucfirst(str_replace('_', ' ', (string) $val));
        };

        $format_followup_status = function ($val) {
            $map = array('not_set' => 'Not set', 'scheduled' => 'Scheduled', 'completed' => 'Completed', 'canceled' => 'Canceled');
            return isset($map[$val]) ? $map[$val] : ucfirst(str_replace('_', ' ', (string) $val));
        };

        switch ($action) {
            case 'lead_bulk_assigned':
                $count = isset($meta['count']) ? (int) $meta['count'] : 0;
                $assigned_to = isset($meta['assigned_to']) ? $meta['assigned_to'] : null;
                $n = $count === 1 ? '1 lead' : $count . ' leads';
                if ($assigned_to === null || $assigned_to === '' || $assigned_to === false) {
                    return $n . ' unassigned';
                }
                $name = $resolve_user($assigned_to);
                return $n . ' assigned to ' . ($name ?: 'User #' . $assigned_to);

            case 'lead_bulk_deleted':
                $count = isset($meta['count']) ? (int) $meta['count'] : 0;
                return ($count === 1 ? '1 lead' : $count . ' leads') . ' deleted';

            case 'pipeline_stage_changed':
                $from = isset($meta['from']) ? $format_stage($meta['from']) : '';
                $to = isset($meta['to']) ? $format_stage($meta['to']) : '';
                if ($from && $to) {
                    return 'Pipeline stage changed from ' . $from . ' to ' . $to;
                }
                return $to ? 'Pipeline stage changed to ' . $to : 'Pipeline stage changed';

            case 'followup_status_changed':
                $from = isset($meta['from']) ? $format_followup_status($meta['from']) : '';
                $to = isset($meta['to']) ? $format_followup_status($meta['to']) : '';
                if ($from && $to) {
                    return 'Follow-up status changed from ' . $from . ' to ' . $to;
                }
                return $to ? 'Follow-up status changed to ' . $to : 'Follow-up status changed';

            case 'followup_rescheduled':
                return 'Follow-up rescheduled';

            case 'tag_added_to_lead':
                return 'Tag added to lead';

            case 'tag_removed_from_lead':
                return 'Tag removed from lead';

            case 'team_member_added':
                return $label ? 'Team member added: ' . $label : 'Team member added';

            case 'team_role_changed':
                $from = isset($meta['from']) ? $meta['from'] : '';
                $to = isset($meta['to']) ? $meta['to'] : '';
                if ($from && $to) {
                    return 'Team role changed from ' . $from . ' to ' . $to;
                }
                return 'Team role changed';

            case 'team_access_toggled':
                $enabled = isset($meta['access_enabled']) ? (bool) $meta['access_enabled'] : null;
                if ($enabled !== null) {
                    return 'Team access ' . ($enabled ? 'enabled' : 'disabled');
                }
                return 'Team access toggled';

            case 'webhook_fired':
                return $label ? 'Webhook fired: ' . $label : 'Webhook fired';

            case 'webhook_failed':
                return $label ? 'Webhook failed: ' . $label : 'Webhook failed';

            case 'export_completed':
                return $label ? 'Export completed: ' . $label : 'Export completed';

            case 'settings_changed':
                return $label ? 'Settings changed: ' . $label : 'Settings changed';

            case 'lead_created':
                return $label ? 'Lead created: ' . $label : 'Lead created';

            case 'lead_updated':
                return $label ? 'Lead updated: ' . $label : 'Lead updated';

            case 'lead_deleted':
                return $label ? 'Lead deleted: ' . $label : 'Lead deleted';

            case 'note_added':
                return $label ? 'Note added: ' . $label : 'Note added';

            case 'note_updated':
                return $label ? 'Note updated: ' . $label : 'Note updated';

            case 'note_deleted':
                return 'Note deleted';

            case 'tag_created':
                return $label ? 'Tag created: ' . $label : 'Tag created';

            case 'tag_updated':
                return $label ? 'Tag updated: ' . $label : 'Tag updated';

            case 'tag_deleted':
                return 'Tag deleted';

            case 'webhook_created':
                return $label ? 'Webhook created: ' . $label : 'Webhook created';

            case 'webhook_updated':
                return $label ? 'Webhook updated: ' . $label : 'Webhook updated';

            case 'webhook_deleted':
                return $label ? 'Webhook deleted: ' . $label : 'Webhook deleted';

            case 'team_password_reset':
                return 'Team password reset';

            default:
                return ucfirst(str_replace('_', ' ', (string) $action));
        }
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
        $config = $this->get_custom_fields_config($user_id);
        return $config['labels'];
    }

    private function get_custom_fields_config($user_id) {
        $defaults = $this->get_default_custom_fields();
        $stored = get_user_meta($user_id, 'lead_aggregator_custom_fields', true);
        if (!is_array($stored)) {
            $stored = array();
        }

        $labels = array();
        $followup = array();
        $stored_labels = array();
        $stored_followup = array();

        if (isset($stored['labels']) && is_array($stored['labels'])) {
            $stored_labels = $stored['labels'];
        } elseif (isset($stored['fields']) && is_array($stored['fields'])) {
            $stored_labels = $stored['fields'];
        } else {
            $stored_labels = $stored;
        }

        if (isset($stored['followup']) && is_array($stored['followup'])) {
            $stored_followup = $stored['followup'];
        }

        for ($i = 1; $i <= 10; $i += 1) {
            $key = 'custom_' . $i;
            $label = isset($stored_labels[$key]) ? sanitize_text_field($stored_labels[$key]) : $defaults[$key];
            $labels[$key] = $label;
            $followup[$key] = !empty($stored_followup[$key]) ? 1 : 0;
        }

        return array(
            'labels' => $labels,
            'followup' => $followup,
        );
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
