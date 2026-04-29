<?php
/**
 * Plugin Name: JE Sales Banner
 * Description: Promotional sale banners with admin UI, templates, shortcode, and header/footer injection.
 * Version: 1.0.8
 * Author: JazzEdge
 * Requires PHP: 7.4
 * Text Domain: je-sales-banner
 */

if (!defined('ABSPATH')) {
    exit;
}

define('JE_SB_VERSION', '1.0.8');
define('JE_SB_PLUGIN_FILE', __FILE__);
define('JE_SB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JE_SB_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once JE_SB_PLUGIN_DIR . 'includes/class-database.php';
require_once JE_SB_PLUGIN_DIR . 'includes/class-admin.php';
require_once JE_SB_PLUGIN_DIR . 'includes/class-frontend.php';

register_activation_hook(
    __FILE__,
    static function () {
        JE_SB_Database::create_table();
        JE_SB_Database::ensure_schema_columns();
    }
);

add_action('plugins_loaded', static function () {
    JE_SB_Database::maybe_upgrade();
});

JE_SB_Admin::init();
JE_SB_Frontend::init();
