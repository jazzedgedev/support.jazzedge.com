<?php
/**
 * Plugin Name: SJE CRM Webhook
 * Description: Generic FluentCRM proxy endpoint. Accepts contact actions from external sites.
 * Version: 1.0.0
 * Author: Willie Myette
 *
 * @package SJE_CRM_Webhook
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SJE_CRM_WEBHOOK_VERSION', '1.0.0' );
define( 'SJE_CRM_WEBHOOK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SJE_CRM_WEBHOOK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SJE_CRM_WEBHOOK_PLUGIN_FILE', __FILE__ );

require_once SJE_CRM_WEBHOOK_PLUGIN_DIR . 'includes/class-logger.php';
require_once SJE_CRM_WEBHOOK_PLUGIN_DIR . 'includes/class-endpoint.php';
require_once SJE_CRM_WEBHOOK_PLUGIN_DIR . 'includes/class-admin-page.php';

/**
 * Initialize plugin on load
 */
add_action( 'plugins_loaded', 'sje_crm_webhook_init' );

function sje_crm_webhook_init() {
	// Register REST API route
	$endpoint = new SJE_CRM_Webhook_Endpoint();
	$endpoint->register_routes();

	// Register admin menu
	if ( is_admin() ) {
		$admin = new SJE_CRM_Webhook_Admin_Page();
		$admin->init();
	}
}

/**
 * Activation hook: create log table and schedule cron
 */
register_activation_hook( __FILE__, 'sje_crm_webhook_activate' );

function sje_crm_webhook_activate() {
	SJE_CRM_Webhook_Logger::create_table();
	wp_schedule_event( time(), 'daily', 'sje_crm_purge_logs' );
}

/**
 * Deactivation hook: clear scheduled cron
 */
register_deactivation_hook( __FILE__, 'sje_crm_webhook_deactivate' );

function sje_crm_webhook_deactivate() {
	wp_clear_scheduled_hook( 'sje_crm_purge_logs' );
}

/**
 * Cron callback: purge old logs
 */
add_action( 'sje_crm_purge_logs', array( 'SJE_CRM_Webhook_Logger', 'purge_old_logs' ) );
