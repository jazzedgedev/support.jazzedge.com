<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Aggregator_FluentCart {
    private $database;
    private $cache_ttl = 3600;

    public function __construct($database) {
        $this->database = $database;
    }

    public function is_active() {
        return class_exists('\\FluentCart\\App\\App') && $this->has_api_helper();
    }

    private function has_api_helper() {
        return function_exists('fluentCartApi') || function_exists('FluentCartApi') || function_exists('fluentcart_api');
    }

    private function api($resource) {
        if (function_exists('fluentCartApi')) {
            return fluentCartApi($resource);
        }
        if (function_exists('FluentCartApi')) {
            return FluentCartApi($resource);
        }
        if (function_exists('fluentcart_api')) {
            return fluentcart_api($resource);
        }
        return null;
    }

    public function get_webhook_secret() {
        if (defined('LEAD_AGGREGATOR_FLUENTCART_WEBHOOK_SECRET')) {
            return (string) LEAD_AGGREGATOR_FLUENTCART_WEBHOOK_SECRET;
        }
        return (string) get_option('lead_aggregator_fluentcart_webhook_secret', '');
    }

    public function fetch_subscriptions() {
        if (!$this->is_active()) {
            return array();
        }
        try {
            $api = $this->api('products');
            if (!$api) {
                return array();
            }
            $products = $api->get(array('type' => 'subscription'));
            return is_array($products) ? $products : array();
        } catch (Exception $e) {
            return array();
        }
    }

    public function resolve_plan_for_user($user_id, $plans) {
        if (!$this->is_active()) {
            return array('plan_key' => 'none', 'lead_limit' => 0, 'source' => 'fluentcart');
        }
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return array('plan_key' => 'none', 'lead_limit' => 0, 'source' => 'fluentcart');
        }
        $subscriptions = $this->get_user_subscriptions($user->user_email);
        return $this->match_plan_from_subscriptions($subscriptions, $plans);
    }

    public function get_user_subscriptions($email) {
        if (!$this->is_active()) {
            return array();
        }
        if (!$email) {
            return array();
        }
        try {
            $customer_api = $this->api('customers');
            $subs_api = $this->api('subscriptions');
            if (!$customer_api || !$subs_api) {
                return array();
            }
            $customer = $customer_api->get(array('email' => $email));
            if (empty($customer['data'][0]['id'])) {
                return array();
            }
            $customer_id = (int) $customer['data'][0]['id'];
            $subs = $subs_api->get(array('customer_id' => $customer_id));
            return is_array($subs) && isset($subs['data']) ? $subs['data'] : array();
        } catch (Exception $e) {
            return array();
        }
    }

    public function match_plan_from_subscriptions($subscriptions, $plans) {
        if (!is_array($plans)) {
            return array('plan_key' => 'none', 'lead_limit' => 0, 'source' => 'fluentcart');
        }
        $best = array('plan_key' => 'none', 'lead_limit' => 0, 'source' => 'fluentcart');
        foreach ($plans as $plan_key => $plan) {
            $allowed_ids = isset($plan['fluentcart']['subscription_ids']) ? (array) $plan['fluentcart']['subscription_ids'] : array();
            $allowed_statuses = isset($plan['fluentcart']['allowed_statuses']) ? (array) $plan['fluentcart']['allowed_statuses'] : array('active', 'trialing', 'grace');
            $limit = isset($plan['lead_limit']) ? (int) $plan['lead_limit'] : 0;
            foreach ($subscriptions as $subscription) {
                $subscription_id = isset($subscription['product_id']) ? (string) $subscription['product_id'] : '';
                $plan_id = isset($subscription['plan_id']) ? (string) $subscription['plan_id'] : '';
                $status = isset($subscription['status']) ? (string) $subscription['status'] : '';
                if (!in_array($status, $allowed_statuses, true)) {
                    continue;
                }
                if (in_array($subscription_id, $allowed_ids, true) || in_array($plan_id, $allowed_ids, true)) {
                    if ($limit > $best['lead_limit']) {
                        $best = array(
                            'plan_key' => $plan_key,
                            'lead_limit' => $limit,
                            'source' => 'fluentcart',
                        );
                    }
                }
            }
        }
        return $best;
    }

    public function get_cached_plan($user_id) {
        $expires = (int) get_user_meta($user_id, 'lead_aggregator_plan_cache_expires_at', true);
        if ($expires && $expires > time()) {
            return array(
                'plan_key' => get_user_meta($user_id, 'lead_aggregator_plan_key', true),
                'lead_limit' => (int) get_user_meta($user_id, 'lead_aggregator_lead_limit', true),
                'source' => get_user_meta($user_id, 'lead_aggregator_plan_source', true),
            );
        }
        return null;
    }

    public function set_cached_plan($user_id, $plan_key, $lead_limit, $source = 'fluentcart') {
        update_user_meta($user_id, 'lead_aggregator_plan_key', $plan_key);
        update_user_meta($user_id, 'lead_aggregator_lead_limit', (int) $lead_limit);
        update_user_meta($user_id, 'lead_aggregator_plan_source', $source);
        update_user_meta($user_id, 'lead_aggregator_plan_updated_at', time());
        update_user_meta($user_id, 'lead_aggregator_plan_cache_expires_at', time() + $this->cache_ttl);
        if ($plan_key && $plan_key !== 'none') {
            update_user_meta($user_id, 'lead_aggregator_subscription_status', 'active');
        } else {
            update_user_meta($user_id, 'lead_aggregator_subscription_status', 'inactive');
        }
    }

    public function verify_signature($payload, $signature, $secret) {
        if (!$secret || !$signature) {
            return false;
        }
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }
}
