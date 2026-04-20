<?php

/**
 * Copyright (c) 2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_s4820 {
private static string $m4is_f4218;
  private 
function __construct(){
 
} public static 
function m4is_c961(): void {
self::$m4is_f4218 =m4is_r83::m4is_c26()->m4is_b57926();
 
} static 
function m4is_s8624(){
self::m4is_o07();
 
} private static 
function m4is_o07(){
 $m4is_s07362 =false;
 $m4is_c4069 ='Team';
 $m4is_w89066 ='Teams';
 $m4is_j646 =['name' =>_x("Memberium {$m4is_w89066
}", 'teams', self::$m4is_f4218 ), 'singular_name' =>_x("Memberium {$m4is_c4069
}", 'team', self::$m4is_f4218 ), 'menu_name' =>_x("{$m4is_w89066
}", 'admin menu', self::$m4is_f4218 ), 'name_admin_bar' =>_x("Memberium {$m4is_c4069
}", 'add new on admin bar', self::$m4is_f4218 ), 'add_new' =>_x("Add {$m4is_c4069
}", 'team', self::$m4is_f4218 ), 'add_new_item' =>__("Add New {$m4is_c4069
}", self::$m4is_f4218 ), 'new_item' =>__("New {$m4is_c4069
}", self::$m4is_f4218 ), 'edit_item' =>__("Edit {$m4is_c4069
}", self::$m4is_f4218 ), 'view_item' =>__("View {$m4is_c4069
}", self::$m4is_f4218 ), 'all_items' =>__("All {$m4is_w89066
}", self::$m4is_f4218 ), 'search_items' =>__("Search {$m4is_w89066
}", self::$m4is_f4218 ), 'parent_item_colon' =>__("Parent {$m4is_w89066
}:", self::$m4is_f4218 ), 'not_found' =>__("No {$m4is_w89066
} found.", self::$m4is_f4218 ), 'not_found_in_trash' =>__("No {$m4is_w89066
} found in Trash.", self::$m4is_f4218 ), ];
 $m4is_i481 =['title', 'author',  ];
 $m4is_l87469 =[ ];
 $m4is_y66291 =['can_export' =>true, 'capability_type' =>'post', 'delete_with_user' =>true, 'description' =>"Memberium {$m4is_w89066
} for Group Accounts", 'exclude_from_search' =>true, 'labels' =>$m4is_j646, 'menu_position' =>99, 'public' =>$m4is_s07362, 'rewrite' =>false, 'show_in_admin_bar' =>false, 'show_in_menu' =>true, 'show_in_nav_menus' =>false, 'show_in_rest' =>true, 'show_ui' =>true, 'supports' =>$m4is_i481, 'taxonomies' =>$m4is_l87469, ];
 register_post_type('memb_team', $m4is_y66291 );
 
}
}

