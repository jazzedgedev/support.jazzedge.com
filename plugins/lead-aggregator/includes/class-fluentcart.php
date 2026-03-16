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
        return $this->has_fluentcart_loaded() && $this->has_api_helper();
    }

    /**
     * Return diagnostic info for why FluentCart might be inactive (for debug output).
     *
     * @return array
     */
    public function get_active_diagnostic() {
        $classes = array(
            'FluentCart\\App\\App',
            '\\FluentCart\\App\\App',
            'FluentCartPro\\App\\App',
            'FluentCart\\Framework\\Foundation\\Application',
        );
        $class_checks = array();
        foreach ($classes as $class) {
            $class_checks[$class] = class_exists($class);
        }
        $funcs = array('fluentCartApi', 'FluentCartApi', 'fluentcart_api', 'fluent_cart_api');
        $func_checks = array();
        foreach ($funcs as $func) {
            $func_checks[$func] = function_exists($func);
        }
        return array(
            'classes' => $class_checks,
            'functions' => $func_checks,
            'any_class' => !empty(array_filter($class_checks)),
            'any_function' => !empty(array_filter($func_checks)),
        );
    }

    private function has_fluentcart_loaded() {
        if (class_exists('\\FluentCart\\App\\App')) {
            return true;
        }
        if (class_exists('FluentCart\\App\\App')) {
            return true;
        }
        if (class_exists('FluentCartPro\\App\\App')) {
            return true;
        }
        if ($this->has_api_helper()) {
            return true;
        }
        return false;
    }

    private function has_api_helper() {
        return function_exists('fluentCartApi') || function_exists('FluentCartApi') || function_exists('fluentcart_api') || function_exists('fluent_cart_api');
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
        if (function_exists('fluent_cart_api')) {
            return fluent_cart_api($resource);
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
            $products = $this->api_get($api, array('type' => 'subscription'));
            return is_array($products) ? $products : array();
        } catch (Throwable $e) {
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
                return $this->get_user_subscriptions_via_rest($email);
            }
            $customer = $this->api_get($customer_api, array('email' => $email));
            if (empty($customer['data'][0]['id'])) {
                return $this->get_user_subscriptions_via_rest($email);
            }
            $customer_id = (int) $customer['data'][0]['id'];
            $subs = $this->api_get($subs_api, array('customer_id' => $customer_id));
            return is_array($subs) && isset($subs['data']) ? $subs['data'] : array();
        } catch (Throwable $e) {
            return $this->get_user_subscriptions_via_rest($email);
        }
    }

    /**
     * Call API resource with params; FluentCart may expose get(), list(), or all().
     *
     * @param object $api API resource object from fluentCartApi()
     * @param array  $params Query params
     * @return array|null Response array or null
     */
    private function api_get($api, $params) {
        if (!is_object($api)) {
            return null;
        }
        foreach (array('get', 'list', 'all', 'fetch') as $method) {
            if (is_callable(array($api, $method))) {
                $out = $api->$method($params);
                return is_array($out) ? $out : (array) $out;
            }
        }
        return null;
    }

    /**
     * Fallback: fetch customer and subscriptions via FluentCart REST API.
     *
     * @param string $email Customer email
     * @return array Subscription items (same shape as get_user_subscriptions).
     */
    private function get_user_subscriptions_via_rest($email) {
        $customers = $this->rest_get('customers', array('email' => $email, 'search' => $email, 'per_page' => 5));
        $data = isset($customers['data']) ? $customers['data'] : $customers;
        if (!is_array($data) || empty($data[0]['id'])) {
            return array();
        }
        $customer_id = (int) $data[0]['id'];
        $subs = $this->rest_get('subscriptions', array('customer_id' => $customer_id, 'per_page' => 50));
        $sub_data = isset($subs['data']) ? $subs['data'] : $subs;
        return is_array($sub_data) ? $sub_data : array();
    }

    /**
     * GET a FluentCart REST API resource (fluent-cart/v2).
     *
     * @param string $resource e.g. customers, subscriptions
     * @param array  $params Query params
     * @return array Decoded JSON or empty array
     */
    private function rest_get($resource, $params = array()) {
        $url = rest_url('fluent-cart/v2/' . $resource);
        $params['_wpnonce'] = wp_create_nonce('wp_rest');
        $url = add_query_arg($params, $url);
        $headers = array('X-WP-Nonce' => $params['_wpnonce']);
        if (!empty($_COOKIE)) {
            $parts = array();
            foreach ($_COOKIE as $name => $value) {
                $parts[] = $name . '=' . urlencode($value);
            }
            $headers['Cookie'] = implode('; ', $parts);
        }
        $response = wp_remote_get($url, array('timeout' => 15, 'headers' => $headers));
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return array();
        }
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : array();
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
