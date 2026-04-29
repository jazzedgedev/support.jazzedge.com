<?php
/**
 * Plugin Name: APB Resources
 * Description: Manage and display actor resources by category.
 * Version: 1.0.0
 * Author: JazzEdge
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

define( 'APB_RES_VERSION', '1.0.0' );
define( 'APB_RES_PATH', plugin_dir_path( __FILE__ ) );
define( 'APB_RES_URL', plugin_dir_url( __FILE__ ) );

require_once APB_RES_PATH . 'includes/class-db.php';
require_once APB_RES_PATH . 'includes/class-categories-table.php';
require_once APB_RES_PATH . 'includes/class-resources-table.php';
require_once APB_RES_PATH . 'includes/class-import.php';
require_once APB_RES_PATH . 'includes/class-shortcodes.php';
require_once APB_RES_PATH . 'includes/class-admin.php';

register_activation_hook( __FILE__, [ 'APB_DB', 'create_tables' ] );

add_action( 'plugins_loaded', [ 'APB_DB', 'db_version_check' ] );
add_action( 'plugins_loaded', [ 'APB_DB', 'maybe_add_image_column' ] );
add_action( 'plugins_loaded', [ 'APB_DB', 'maybe_split_image_columns' ] );
add_action( 'plugins_loaded', [ 'APB_DB', 'maybe_add_resource_image_column' ] );

APB_Admin::init();
APB_Import::init();
APB_Shortcodes::init();
