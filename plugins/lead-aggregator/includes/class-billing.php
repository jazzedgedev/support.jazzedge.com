<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Aggregator_Billing {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function get_plans() {
        $defaults = array(
            'starter' => array(
                'label' => 'Starter',
                'limit' => 100,
                'monthly_price_id' => '',
                'annual_price_id' => '',
            ),
            'growth' => array(
                'label' => 'Growth',
                'limit' => 500,
                'monthly_price_id' => '',
                'annual_price_id' => '',
            ),
            'pro' => array(
                'label' => 'Pro',
                'limit' => 2000,
                'monthly_price_id' => '',
                'annual_price_id' => '',
            ),
        );

        $plans = get_option('lead_aggregator_plans', array());
        if (!is_array($plans) || empty($plans)) {
            return $defaults;
        }

        foreach ($defaults as $key => $plan) {
            if (!isset($plans[$key]) || !is_array($plans[$key])) {
                $plans[$key] = $plan;
            } else {
                $plans[$key] = array_merge($plan, $plans[$key]);
            }
        }

        return $plans;
    }

    public function get_plan($plan_key) {
        $plans = $this->get_plans();
        return isset($plans[$plan_key]) ? $plans[$plan_key] : null;
    }

    public function get_success_url() {
        $url = get_option('lead_aggregator_stripe_success_url', '');
        return $url ? esc_url_raw($url) : esc_url_raw(add_query_arg(array('billing' => 'success')));
    }

    public function get_cancel_url() {
        $url = get_option('lead_aggregator_stripe_cancel_url', '');
        return $url ? esc_url_raw($url) : esc_url_raw(add_query_arg(array('billing' => 'cancel')));
    }

    public function create_checkout_session($user_id, $plan_key, $interval) {
        $plan = $this->get_plan($plan_key);
        if (!$plan) {
            return new WP_Error('invalid_plan', 'Invalid plan selected.', array('status' => 400));
        }

        $price_id = $interval === 'annual' ? $plan['annual_price_id'] : $plan['monthly_price_id'];
        if (!$price_id) {
            return new WP_Error('missing_price', 'Pricing is not configured yet.', array('status' => 400));
        }

        $secret = $this->get_secret_key();
        if (!$secret) {
            return new WP_Error('missing_secret', 'Stripe secret key is not configured.', array('status' => 400));
        }

        $customer_id = get_user_meta($user_id, 'lead_aggregator_stripe_customer_id', true);
        $user = get_userdata($user_id);
        if (!$user) {
            return new WP_Error('invalid_user', 'Unable to locate user for checkout.', array('status' => 400));
        }

        $params = array(
            'mode' => 'subscription',
            'line_items[0][price]' => $price_id,
            'line_items[0][quantity]' => 1,
            'success_url' => $this->get_success_url(),
            'cancel_url' => $this->get_cancel_url(),
            'client_reference_id' => $user_id,
            'metadata[user_id]' => $user_id,
            'metadata[plan_key]' => $plan_key,
            'metadata[interval]' => $interval,
        );

        if ($customer_id) {
            $params['customer'] = $customer_id;
        } else {
            $params['customer_email'] = $user->user_email;
        }

        $response = $this->stripe_request('POST', '/checkout/sessions', $params);
        if (is_wp_error($response)) {
            return $response;
        }

        if (empty($response['url'])) {
            return new WP_Error('checkout_failed', 'Unable to create Stripe checkout session.', array('status' => 500));
        }

        update_user_meta($user_id, 'lead_aggregator_last_checkout_session', sanitize_text_field($response['id']));

        return $response['url'];
    }

    public function create_portal_session($user_id) {
        $secret = $this->get_secret_key();
        if (!$secret) {
            return new WP_Error('missing_secret', 'Stripe secret key is not configured.', array('status' => 400));
        }

        $customer_id = get_user_meta($user_id, 'lead_aggregator_stripe_customer_id', true);
        if (!$customer_id) {
            return new WP_Error('missing_customer', 'No Stripe customer found for this account.', array('status' => 400));
        }

        $response = $this->stripe_request('POST', '/billing_portal/sessions', array(
            'customer' => $customer_id,
            'return_url' => $this->get_success_url(),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        if (empty($response['url'])) {
            return new WP_Error('portal_failed', 'Unable to create Stripe portal session.', array('status' => 500));
        }

        return $response['url'];
    }

    public function handle_webhook($request) {
        $payload = $request->get_body();
        $signature = $request->get_header('stripe-signature');
        $secret = $this->get_webhook_secret();
        if ($secret && !$this->verify_signature($payload, $signature, $secret)) {
            return new WP_Error('invalid_signature', 'Invalid Stripe signature.', array('status' => 400));
        }

        $event = json_decode($payload);
        if (!$event || empty($event->type)) {
            return new WP_Error('invalid_payload', 'Invalid Stripe payload.', array('status' => 400));
        }

        $type = $event->type;
        if ($type === 'checkout.session.completed') {
            $session = $event->data->object;
            $user_id = $this->resolve_user_id_from_session($session);
            if ($user_id) {
                $this->update_from_checkout_session($user_id, $session);
            }
        }

        if ($type === 'customer.subscription.created' || $type === 'customer.subscription.updated' || $type === 'customer.subscription.deleted') {
            $subscription = $event->data->object;
            $this->update_from_subscription($subscription);
        }

        if ($type === 'invoice.payment_failed') {
            $invoice = $event->data->object;
            $this->mark_payment_failed($invoice);
        }

        if ($type === 'invoice.paid') {
            $invoice = $event->data->object;
            $this->mark_payment_paid($invoice);
        }

        return rest_ensure_response(array('received' => true));
    }

    public function get_subscription_status($user_id) {
        return (string) get_user_meta($user_id, 'lead_aggregator_subscription_status', true);
    }

    public function get_plan_key($user_id) {
        return (string) get_user_meta($user_id, 'lead_aggregator_plan_key', true);
    }

    private function get_secret_key() {
        return trim((string) get_option('lead_aggregator_stripe_secret_key', ''));
    }

    private function get_webhook_secret() {
        return trim((string) get_option('lead_aggregator_stripe_webhook_secret', ''));
    }

    private function stripe_request($method, $path, $params) {
        $secret = $this->get_secret_key();
        if (!$secret) {
            return new WP_Error('missing_secret', 'Stripe secret key is not configured.', array('status' => 400));
        }

        $url = 'https://api.stripe.com/v1' . $path;
        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $secret,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => $params,
            'timeout' => 20,
        );

        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($code >= 400) {
            $message = isset($data['error']['message']) ? $data['error']['message'] : 'Stripe request failed.';
            return new WP_Error('stripe_error', $message, array('status' => $code));
        }

        return $data;
    }

    private function resolve_user_id_from_session($session) {
        if (!empty($session->client_reference_id)) {
            return (int) $session->client_reference_id;
        }

        if (!empty($session->metadata->user_id)) {
            return (int) $session->metadata->user_id;
        }

        if (!empty($session->customer_email)) {
            $user = get_user_by('email', sanitize_email($session->customer_email));
            if ($user) {
                return (int) $user->ID;
            }

            return $this->create_user_from_email($session->customer_email);
        }

        return 0;
    }

    private function create_user_from_email($email) {
        $email = sanitize_email($email);
        if (!$email) {
            return 0;
        }

        $base = sanitize_user(strstr($email, '@', true));
        if (!$base) {
            $base = 'leaduser';
        }

        $username = $base;
        $suffix = 1;
        while (username_exists($username)) {
            $username = $base . $suffix;
            $suffix++;
        }

        $password = wp_generate_password(12, true);
        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            return 0;
        }

        update_user_meta($user_id, 'lead_aggregator_subscription_status', 'inactive');

        return (int) $user_id;
    }

    private function update_from_checkout_session($user_id, $session) {
        if (!empty($session->customer)) {
            update_user_meta($user_id, 'lead_aggregator_stripe_customer_id', sanitize_text_field($session->customer));
        }

        $plan_key = '';
        if (!empty($session->metadata->plan_key)) {
            $plan_key = sanitize_key($session->metadata->plan_key);
        }

        $this->set_subscription_meta($user_id, 'active', $plan_key, '', !empty($session->subscription) ? sanitize_text_field($session->subscription) : '');
    }

    private function update_from_subscription($subscription) {
        if (empty($subscription->customer)) {
            return;
        }

        $user_id = $this->find_user_by_customer_id($subscription->customer);
        if (!$user_id) {
            return;
        }

        $price_id = '';
        if (!empty($subscription->items->data[0]->price->id)) {
            $price_id = sanitize_text_field($subscription->items->data[0]->price->id);
        }

        $plan_key = $this->resolve_plan_key_from_price_id($price_id);
        $status = !empty($subscription->status) ? sanitize_text_field($subscription->status) : 'inactive';
        $current_period_end = !empty($subscription->current_period_end) ? (int) $subscription->current_period_end : 0;

        $this->set_subscription_meta($user_id, $status, $plan_key, $price_id, sanitize_text_field($subscription->id), $current_period_end);
    }

    private function mark_payment_failed($invoice) {
        if (empty($invoice->customer)) {
            return;
        }

        $user_id = $this->find_user_by_customer_id($invoice->customer);
        if (!$user_id) {
            return;
        }

        $this->set_subscription_meta($user_id, 'past_due', '', '', '', 0);
    }

    private function mark_payment_paid($invoice) {
        if (empty($invoice->customer)) {
            return;
        }

        $user_id = $this->find_user_by_customer_id($invoice->customer);
        if (!$user_id) {
            return;
        }

        $current_status = get_user_meta($user_id, 'lead_aggregator_subscription_status', true);
        if ($current_status !== 'active') {
            update_user_meta($user_id, 'lead_aggregator_subscription_status', 'active');
        }
    }

    private function set_subscription_meta($user_id, $status, $plan_key, $price_id, $subscription_id, $current_period_end = 0) {
        update_user_meta($user_id, 'lead_aggregator_subscription_status', sanitize_text_field($status));

        if ($plan_key) {
            update_user_meta($user_id, 'lead_aggregator_plan_key', sanitize_key($plan_key));
        }

        if ($price_id) {
            update_user_meta($user_id, 'lead_aggregator_stripe_price_id', sanitize_text_field($price_id));
        }

        if ($subscription_id) {
            update_user_meta($user_id, 'lead_aggregator_stripe_subscription_id', sanitize_text_field($subscription_id));
        }

        if ($current_period_end) {
            update_user_meta($user_id, 'lead_aggregator_subscription_period_end', (int) $current_period_end);
        }
    }

    private function resolve_plan_key_from_price_id($price_id) {
        if (!$price_id) {
            return '';
        }

        $plans = $this->get_plans();
        foreach ($plans as $key => $plan) {
            if (!empty($plan['monthly_price_id']) && $plan['monthly_price_id'] === $price_id) {
                return $key;
            }
            if (!empty($plan['annual_price_id']) && $plan['annual_price_id'] === $price_id) {
                return $key;
            }
        }

        return '';
    }

    private function find_user_by_customer_id($customer_id) {
        if (!$customer_id) {
            return 0;
        }

        $query = new WP_User_Query(array(
            'meta_key' => 'lead_aggregator_stripe_customer_id',
            'meta_value' => sanitize_text_field($customer_id),
            'number' => 1,
            'fields' => array('ID'),
        ));

        if (empty($query->results)) {
            return 0;
        }

        return (int) $query->results[0]->ID;
    }

    private function verify_signature($payload, $signature_header, $secret) {
        if (!$signature_header || !$secret) {
            return false;
        }

        $parts = explode(',', $signature_header);
        $timestamp = '';
        $signatures = array();
        foreach ($parts as $part) {
            $item = explode('=', trim($part), 2);
            if (count($item) !== 2) {
                continue;
            }
            if ($item[0] === 't') {
                $timestamp = $item[1];
            } elseif ($item[0] === 'v1') {
                $signatures[] = $item[1];
            }
        }

        if (!$timestamp || empty($signatures)) {
            return false;
        }

        $signed_payload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signed_payload, $secret);
        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }
}
