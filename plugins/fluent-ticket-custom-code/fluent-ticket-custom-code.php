<?php
/**
 * Plugin Name: Fluent Ticket Custom Code
 * Description: Custom Fluent Support ticket sidebar tools — Keap search, autologin links, and student lookup.
 * Version: 1.0.6
 * Author: Willie Myette
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fluent-ticket-custom-code
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FLUENT_TICKET_CUSTOM_CODE_VERSION', '1.0.6' );
define( 'FLUENT_TICKET_CUSTOM_CODE_FILE', __FILE__ );
define( 'FLUENT_TICKET_CUSTOM_CODE_DIR', plugin_dir_path( __FILE__ ) );
define( 'FLUENT_TICKET_CUSTOM_CODE_URL', plugin_dir_url( __FILE__ ) );

// Optional: override in wp-config.php for environment-specific secrets.
if ( ! defined( 'FTCC_KEAP_API_TOKEN' ) ) {
	define( 'FTCC_KEAP_API_TOKEN', 'KeapAK-d3a9fe4ce598f45741ff08611a8a3cdfb20c5d9cc1ab824fbe' );
}
if ( ! defined( 'FTCC_MEMBERIUM_AUTH_KEY' ) ) {
	define( 'FTCC_MEMBERIUM_AUTH_KEY', 'K9DqpZpAhvqe' );
}

require_once FLUENT_TICKET_CUSTOM_CODE_DIR . 'includes/class-fluent-ticket-custom-code.php';

Fluent_Ticket_Custom_Code::instance();
