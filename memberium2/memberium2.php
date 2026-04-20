<?php
/*
Plugin Name: Memberium for Keap
Description: Membership system for Keap and WordPress
Author URI: http://www.webpowerandlight.com/
Author: David Bullock
License: Copyright (c) 2012-2024 David Bullock, Web Power and Light
Plugin URI: http://www.memberium.com/
Requires at least: 6.2
Requires PHP: 7.4
Text Domain: memberium
Update URI: https://memberium.com/
Version: 4.0.13
*/
 defined( 'ABSPATH' ) || die();
 if ( ! function_exists( 'memberium_app' ) ) {
 if ( include_once __DIR__ . '/classes/system/core.php' ) {
 define( 'MEMBERIUM_HOME',  __FILE__ );
 define( 'MEMBERIUM_HOME_DIR',  __DIR__ . '/' );
 
function memberium_app() : m4is_r83 {
 static $m4is_r1546;
 return $m4is_r1546 ??= m4is_r83::m4is_c26();
 
} memberium_app();
 
} 
}

