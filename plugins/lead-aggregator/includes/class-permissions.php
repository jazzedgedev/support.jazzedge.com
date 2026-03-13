<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Aggregator_Permissions {
    private $database;
    private $last_error = array();

    public function __construct($database) {
        $this->database = $database;
    }

    public function require_login($user_id = 0) {
        if (!is_user_logged_in()) {
            $this->set_error('not_logged_in', 'Please log in to access lead tools.', 401);
            return false;
        }

        if ($user_id <= 0) {
            $user_id = get_current_user_id();
        }

        if ($user_id <= 0) {
            $this->set_error('not_logged_in', 'Please log in to access lead tools.', 401);
            return false;
        }

        return true;
    }

    public function require_user($user_id = 0) {
        if (!$this->require_login($user_id)) {
            return false;
        }

        if ($user_id <= 0) {
            $user_id = get_current_user_id();
        }

        if (user_can($user_id, 'manage_options')) {
            return true;
        }

        $access_enabled = $this->get_access_enabled_value($user_id);
        if ($access_enabled === 0) {
            $this->set_error('access_disabled', 'Access disabled. Please contact support.', 403);
            return false;
        }

        if ($access_enabled === null && !$this->is_subscription_active($user_id)) {
            $this->set_error('subscription_inactive', 'Subscription inactive. Please update billing to regain access.', 402);
            return false;
        }

        return true;
    }

    public function can_manage_leads($user_id) {
        if (user_can($user_id, 'manage_options')) {
            return true;
        }

        if ($user_id <= 0) {
            return false;
        }

        return $this->is_subscription_active($user_id) && !$this->is_read_only($user_id);
    }

    public function can_create_lead($user_id) {
        if (!$this->can_manage_leads($user_id)) {
            return false;
        }

        $entitlements = lead_aggregator_get_entitlements($user_id);
        if ($entitlements['lead_limit'] === 0) {
            return true;
        }
        return !$entitlements['is_over_limit'];
    }

    public function get_contact_limit_for_user($user_id) {
        $entitlements = lead_aggregator_get_entitlements($user_id);
        return (int) $entitlements['lead_limit'];
    }

    public function get_last_error() {
        return $this->last_error;
    }

    private function set_error($code, $message, $status = 403) {
        $this->last_error = array(
            'code' => $code,
            'message' => $message,
            'status' => $status,
        );
    }

    private function is_subscription_active($user_id) {
        $status = get_user_meta($user_id, 'lead_aggregator_subscription_status', true);
        return $status === 'active' || $status === 'trialing';
    }

    public function is_access_enabled($user_id) {
        return $this->get_access_enabled_value($user_id) === 1;
    }

    public function is_read_only($user_id) {
        $level = get_user_meta($user_id, 'lead_aggregator_access_level', true);
        return $level === 'read';
    }

    private function get_access_enabled_value($user_id) {
        $value = get_user_meta($user_id, 'lead_aggregator_access_enabled', true);
        if ($value === '') {
            return null;
        }
        return (int) $value;
    }

    private function get_plan_limit_for_user($user_id) {
        $entitlements = lead_aggregator_get_entitlements($user_id);
        if (isset($entitlements['lead_limit'])) {
            return (int) $entitlements['lead_limit'];
        }
        return null;
    }
}
