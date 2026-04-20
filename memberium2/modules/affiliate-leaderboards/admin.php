<?php
 class_exists('m4is_r83')||die();
 
class m4is_w28 {
static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
add_filter('memberium/modules/active/names', [$this, 'm4is_f809'], 10, 1 );
 add_action('memberium_admin_menu_addons', [$this, 'm4is_p2651']);
 
}
function m4is_f809($m4is_y634 ){
return array_merge($m4is_y634, ['Affiliate Leaderboards for Keap' ]);
 
}
function m4is_p2651($m4is_x160 ){
add_submenu_page($m4is_x160, 'Affiliates', 'Affiliates', 'manage_options', 'memberium-affiliate-leaderboards', [$this, 'm4is_g63']);
 
}
function m4is_g63(){
require_once __DIR__ . '/screen.php';
 
} 
}

