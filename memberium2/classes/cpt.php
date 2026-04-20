<?php

/**
 * Copyright (c) 2016-4 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_q690 {
private static string $m4is_f4218;
 public static 
function m4is_c961(): void {
self::$m4is_f4218 =m4is_r83::m4is_c26()->m4is_b57926();
 
} public static 
function m4is_s8624(): void {
self::m4is_d97();
 self::m4is_p1246();
 add_filter('et_builder_post_types', ['m4is_q690', 'm4is_x26']);
 
}private static 
function m4is_t16(bool $m4is_f0174, string $m4is_q485 ): bool {
$m4is_f0174 =(bool) apply_filters('memberium/cpt/is_public', $m4is_f0174, $m4is_q485 );
 return $m4is_f0174;
 
} private static 
function m4is_d97(){
$m4is_s07362 =self::m4is_t16(false, 'memb_shortcodeblocks' );
 $m4is_j646 =['name' =>_x('Custom Shortcodes', 'post type general name', self::$m4is_f4218 ), 'singular_name' =>_x('Custom Shortcode', 'post type singular name', self::$m4is_f4218 ), 'menu_name' =>_x('Custom Shortcodes', 'admin menu', self::$m4is_f4218 ), 'name_admin_bar' =>_x('Custom Shortcode', 'add new on admin bar', self::$m4is_f4218 ), 'add_new' =>_x('Add New', 'book', self::$m4is_f4218 ), 'add_new_item' =>__('Add New Custom Shortcode', self::$m4is_f4218 ), 'new_item' =>__('New Custom Shortcode', self::$m4is_f4218 ), 'edit_item' =>__('Edit Custom Shortcode', self::$m4is_f4218 ), 'view_item' =>__('View Custom Shortcode', self::$m4is_f4218 ), 'all_items' =>__('All Custom Shortcodes', self::$m4is_f4218 ), 'search_items' =>__('Search Custom Shortcodes', self::$m4is_f4218 ), 'parent_item_colon' =>__('Parent Custom Shortcodes:', self::$m4is_f4218 ), 'not_found' =>__('No custom shortcodes found.', self::$m4is_f4218 ), 'not_found_in_trash' =>__('No custom shortcodes found in Trash.', self::$m4is_f4218 ), ];
 $m4is_i481 =['title', 'editor', ];
 $m4is_l87469 =['category', ];
 $m4is_y66291 =['can_export' =>true, 'capability_type' =>'post', 'description' =>'Custom Shortcodes', 'exclude_from_search' =>true, 'labels' =>$m4is_j646, 'menu_position' =>21, 'public' =>$m4is_s07362, 'show_in_admin_bar' =>false, 'show_in_menu' =>false, 'show_in_nav_menus' =>false, 'show_ui' =>true, 'supports' =>$m4is_i481, 'taxonomies' =>$m4is_l87469, ];
 register_post_type('memb_shortcodeblocks', $m4is_y66291 );
 
} private static 
function m4is_p1246(){
$m4is_s07362 =self::m4is_t16(false, 'partials' );
 $m4is_j646 =['name' =>_x('Partials', 'post type general name', self::$m4is_f4218 ), 'singular_name' =>_x('Partial', 'post type singular name', self::$m4is_f4218 ), 'menu_name' =>_x('Partials', 'admin menu', self::$m4is_f4218 ), 'name_admin_bar' =>_x('Partial', 'add new on admin bar', self::$m4is_f4218 ), 'add_new' =>_x('Add New', 'book', self::$m4is_f4218 ), 'add_new_item' =>__('Add New Partial', self::$m4is_f4218 ), 'new_item' =>__('New Partial', self::$m4is_f4218 ), 'edit_item' =>__('Edit Partial', self::$m4is_f4218 ), 'view_item' =>__('View Partial', self::$m4is_f4218 ), 'all_items' =>__('All Partials', self::$m4is_f4218 ), 'search_items' =>__('Search Partials', self::$m4is_f4218 ), 'parent_item_colon' =>__('Parent Partials:', self::$m4is_f4218 ), 'not_found' =>__('No partials found.', self::$m4is_f4218 ), 'not_found_in_trash' =>__('No partials found in Trash.', self::$m4is_f4218 ), ];
 $m4is_i481 =['title', 'editor', 'revisions', 'excerpt', ];
 $m4is_y66291 =['can_export' =>true, 'capability_type' =>'post', 'description' =>'Content Snippets for Memberium Membership System', 'exclude_from_search' =>true, 'labels' =>$m4is_j646, 'menu_position' =>20, 'public' =>$m4is_s07362, 'show_in_admin_bar' =>false, 'show_in_menu' =>false, 'show_in_nav_menus' =>false, 'show_ui' =>true, 'supports' =>$m4is_i481, ];
 register_post_type('partials', $m4is_y66291 );
 
} static 
function m4is_x26($m4is_x320 ){
$m4is_w341 =['partials', 'memb_shortcodeblocks', ];
 $m4is_x320 =array_merge($m4is_x320, $m4is_w341 );
 return $m4is_x320;
 
}
}

