<?php
/**
 * Plugin Name: Academy Starter for Microsites
 * Description: SEO-optimized landing page shortcode for piano lesson microsites with built-in analytics.
 * Version: 1.0.0
 * Author: Jazzedge
 * Text Domain: academy-starter
 *
 * @package Academy_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACADEMY_STARTER_VERSION', '1.0.0' );
define( 'ACADEMY_STARTER_PLUGIN_FILE', __FILE__ );
define( 'ACADEMY_STARTER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ACADEMY_STARTER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once ACADEMY_STARTER_PLUGIN_DIR . 'includes/class-styles.php';
require_once ACADEMY_STARTER_PLUGIN_DIR . 'includes/class-analytics.php';
require_once ACADEMY_STARTER_PLUGIN_DIR . 'includes/class-rest-api.php';
require_once ACADEMY_STARTER_PLUGIN_DIR . 'includes/class-admin.php';
require_once ACADEMY_STARTER_PLUGIN_DIR . 'includes/class-shortcode.php';

register_activation_hook( __FILE__, array( 'AcademyStarterAnalytics', 'create_tables' ) );

add_action(
	'plugins_loaded',
	static function () {
		AcademyStarterAdmin::init();
		AcademyStarterShortcode::init();
		AcademyStarterRestApi::init();
		$upgraded = get_option( 'academy_starter_db_version', '0' );

		if ( version_compare( $upgraded, '1.1', '<' ) ) {
			AcademyStarterAnalytics::maybe_upgrade_tables();
			update_option( 'academy_starter_db_version', '1.1' );
		}
	}
);
