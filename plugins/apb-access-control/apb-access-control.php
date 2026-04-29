<?php
/**
 * Plugin Name: APB Access Control
 * Description: Gates pages by FluentCRM tag with user meta caching.
 * Version: 1.0.0
 * Author: JazzEdge
 *
 * @package APB_Access_Control
 */

defined( 'ABSPATH' ) || exit;

define( 'APB_ACCESS_VERSION', '1.0.0' );
define( 'APB_ACCESS_PATH', plugin_dir_path( __FILE__ ) );

require_once APB_ACCESS_PATH . 'includes/class-settings.php';
require_once APB_ACCESS_PATH . 'includes/class-access-checker.php';
require_once APB_ACCESS_PATH . 'includes/class-meta-box.php';
require_once APB_ACCESS_PATH . 'includes/class-user-profile.php';

APB_Settings::init();
APB_Access_Checker::init();
APB_Meta_Box::init();
APB_User_Profile::init();
