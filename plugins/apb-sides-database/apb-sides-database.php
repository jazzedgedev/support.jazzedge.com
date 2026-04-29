<?php
/**
 * Plugin Name: APB Sides Database
 * Plugin URI: https://www.jazzedge.com/
 * Description: Screenplay sides database with uploads, Claude extraction, review workflow, and public search.
 * Version: 1.0.1
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: APB
 * Text Domain: apb-sides-database
 * Domain Path: /languages
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'APB_SIDES_VERSION', '1.0.1' );
define( 'APB_SIDES_PLUGIN_FILE', __FILE__ );
define( 'APB_SIDES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'APB_SIDES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

$apb_sides_upload = wp_upload_dir();
if ( ! empty( $apb_sides_upload['error'] ) ) {
	$apb_sides_base = WP_CONTENT_DIR . '/uploads';
	define( 'APB_SIDES_UPLOAD_DIR', trailingslashit( $apb_sides_base ) . 'apb-sides/' );
	define( 'APB_SIDES_UPLOAD_URL', content_url( 'uploads/apb-sides/' ) );
} else {
	define( 'APB_SIDES_UPLOAD_DIR', trailingslashit( $apb_sides_upload['basedir'] ) . 'apb-sides/' );
	define( 'APB_SIDES_UPLOAD_URL', trailingslashit( $apb_sides_upload['baseurl'] ) . 'apb-sides/' );
}
unset( $apb_sides_upload );

require_once APB_SIDES_PLUGIN_DIR . 'includes/class-apb-sides-statuses.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/class-apb-sides-helpers.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/class-apb-sides-logger.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/class-apb-sides-loader.php';

require_once APB_SIDES_PLUGIN_DIR . 'includes/repositories/class-apb-sides-upload-repo.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/repositories/class-apb-sides-script-repo.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/repositories/class-apb-sides-character-repo.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/repositories/class-apb-sides-side-repo.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/repositories/class-apb-sides-side-character-repo.php';

require_once APB_SIDES_PLUGIN_DIR . 'includes/services/class-apb-sides-normalizer.php';

require_once APB_SIDES_PLUGIN_DIR . 'includes/class-apb-sides-activator.php';

require_once APB_SIDES_PLUGIN_DIR . 'includes/admin/class-apb-sides-admin.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/admin/class-apb-sides-admin-dashboard.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/admin/class-apb-sides-admin-uploads.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/admin/class-apb-sides-admin-scripts.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/admin/class-apb-sides-admin-characters.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/admin/class-apb-sides-admin-sides.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/admin/class-apb-sides-admin-settings.php';

require_once APB_SIDES_PLUGIN_DIR . 'includes/ajax/class-apb-sides-ajax-admin.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/ajax/class-apb-sides-ajax-frontend.php';

require_once APB_SIDES_PLUGIN_DIR . 'includes/frontend/class-apb-sides-frontend.php';
require_once APB_SIDES_PLUGIN_DIR . 'includes/frontend/class-apb-sides-shortcodes.php';

require_once APB_SIDES_PLUGIN_DIR . 'includes/class-apb-sides-plugin.php';

register_activation_hook( __FILE__, array( 'APB_Sides_Activator', 'activate' ) );
register_deactivation_hook(
	__FILE__,
	static function () {
		// No-op on deactivation.
	}
);

add_action(
	'plugins_loaded',
	static function () {
		APB_Sides_Plugin::instance()->init();
	}
);
