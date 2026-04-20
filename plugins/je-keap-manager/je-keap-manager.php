<?php
/**
 * Plugin Name: Jazzedge Keap Manager
 * Description: Manage Keap contacts — find students, cancel memberships, and review account data.
 * Version: 1.0
 * Author: JazzEdge
 */
if (!defined('ABSPATH')) exit;

define('JECM_PLUGIN_DIR', __DIR__ . '/');

require_once JECM_PLUGIN_DIR . 'includes/class-keap-api.php';
require_once JECM_PLUGIN_DIR . 'includes/class-logger.php';
require_once JECM_PLUGIN_DIR . 'includes/class-tasks.php';
require_once JECM_PLUGIN_DIR . 'includes/class-webhook.php';
require_once JECM_PLUGIN_DIR . 'includes/class-admin.php';

register_activation_hook(
    __FILE__,
    static function () {
        JECM_Logger::create_table();
        JECM_Tasks::create_table();
        JECM_FluentCart_Webhook::create_tables();
        if (get_option('jecm_fc_webhook_secret', '') === '') {
            update_option('jecm_fc_webhook_secret', wp_generate_password(48, false, false));
        }
    }
);

// Also ensure tables exist on every load (handles manual installs)
add_action('init', array('JECM_Logger', 'create_table'));
add_action('init', array('JECM_Tasks', 'create_table'));
add_action('init', array('JECM_FluentCart_Webhook', 'create_tables'));

JECM_FluentCart_Webhook::bootstrap();

new JECM_Admin();
