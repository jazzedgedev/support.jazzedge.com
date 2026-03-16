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
                'lead_limit' => 100,
                'fluentcart' => array(
                    'subscription_ids' => array('46'),
                    'allowed_statuses' => array('active', 'trialing', 'grace'),
                ),
            ),
            'core' => array(
                'label' => 'Core',
                'lead_limit' => 500,
                'fluentcart' => array(
                    'subscription_ids' => array('48'),
                    'allowed_statuses' => array('active', 'trialing', 'grace'),
                ),
            ),
            'plus' => array(
                'label' => 'Plus',
                'lead_limit' => 2500,
                'fluentcart' => array(
                    'subscription_ids' => array('50'),
                    'allowed_statuses' => array('active', 'trialing', 'grace'),
                ),
            ),
            'enterprise' => array(
                'label' => 'Enterprise',
                'lead_limit' => 10000,
                'fluentcart' => array(
                    'subscription_ids' => array('52'),
                    'allowed_statuses' => array('active', 'trialing', 'grace'),
                ),
            ),
        );

        $plans = get_option('lead_aggregator_plans', array());
        if (!is_array($plans) || empty($plans)) {
            $legacy = $this->migrate_legacy_plans();
            if (!empty($legacy)) {
                $plans = $legacy;
            } else {
                return $defaults;
            }
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

    public function migrate_legacy_plans() {
        $legacy = get_option('lead_aggregator_plans', array());
        if (empty($legacy) || !is_array($legacy)) {
            return array();
        }
        $first = reset($legacy);
        if (is_array($first) && isset($first['lead_limit'])) {
            return $legacy;
        }
        $migrated = array();
        foreach ($legacy as $key => $plan) {
            if (!is_array($plan)) {
                continue;
            }
            $migrated[$key] = array(
                'label' => isset($plan['label']) ? $plan['label'] : ucfirst($key),
                'lead_limit' => isset($plan['limit']) ? (int) $plan['limit'] : 0,
                'fluentcart' => array(
                    'subscription_ids' => array(),
                    'allowed_statuses' => array('active', 'trialing', 'grace'),
                ),
            );
        }
        update_option('lead_aggregator_plans', $migrated);
        return $migrated;
    }

    public function get_plan($plan_key) {
        $plans = $this->get_plans();
        return isset($plans[$plan_key]) ? $plans[$plan_key] : null;
    }

    public function get_subscription_status($user_id) {
        return (string) get_user_meta($user_id, 'lead_aggregator_subscription_status', true);
    }

    public function get_plan_key($user_id) {
        return (string) get_user_meta($user_id, 'lead_aggregator_plan_key', true);
    }
}
