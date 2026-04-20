<?php

/**
 * Proprietary Software - All Rights Reserved
 *
 * This file is part of the Memberium plugin, which is proprietary software developed by Web Power and Light.
 * Unauthorized copying, distribution, or modification of this file, via any medium, is strictly prohibited.
 *
 * Copyright (c) 2025-2025 David J Bullock
 * Web Power and Light
 *
 * For licensing information, please contact Web Power and Light.
 */


 declare(strict_types=1 );
 class_exists('m4is_r83')||die();
  final 
class m4is_p93 {
 public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_h269();
 
} public 
function m4is_h269(): void {
$m4is_h50669 =(!empty($_GET['fb'])&&current_user_can('edit_posts' ))||is_admin();
 if($m4is_h50669 ){
add_filter('fusion_builder_all_elements', [$this, 'm4is_y52403'], 0, 1 );
 add_action('admin_enqueue_scripts', [$this, 'm4is_s74'], 10, 0 );
 
}else{
add_filter('do_shortcode_tag', [$this, 'm4is_h07'], 10, 4 );
 
} 
} public 
function m4is_h07(string $m4is_o498, string $m4is_c39, $m4is_a394, $regex_match ): string {
if(substr($m4is_c39, 0, 7 )!== 'fusion_' ){
return $m4is_o498;
  
}static $m4is_g81;
 static $m4is_f087;
 static $m4is_q30624;
 static $m4is_f406;
 $m4is_f087 ??= get_current_user_id();
 $m4is_g81 ??= $m4is_f087 &&current_user_can('administrator' );
 if(!isset ($m4is_a394[self::M4IS_G095])){
return $m4is_o498;
 
}if($m4is_g81 ){
return $m4is_o498;
 
}if(!empty($m4is_a394[self::M4IS_G095])){
if($m4is_a394[self::M4IS_G095]=== 'anonymous' &&$m4is_f087 ){
return '';
  
}elseif($m4is_a394[self::M4IS_G095]=== 'logged-in' &&!$m4is_f087 ){
return '';
  
}
}if(!empty($m4is_a394[self::M4IS_G3167])){
$m4is_q30624 ??= $m4is_f087 ? explode(',', m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'membership_id', '' )): [];
 if(empty($m4is_q30624 )){
return '';
 
}
}else{
if(!empty($m4is_a394[self::M4IS_K196])){
$m4is_q30624 ??= $m4is_f087 ? explode(',', (string) m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'membership_tags', '' )): [];
 $m4is_b5092 =array_filter(array_map('intval', explode(',', (string) $m4is_a394[self::M4IS_K196])));
 if(empty(array_intersect($m4is_b5092, $m4is_q30624 ))){
return '';
 
}
}
}if(!empty($m4is_a394[self::M4IS_O16860])){
$m4is_f406 ??= $m4is_f087 ? explode(',', (string) m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tags', '' )): [];
 $m4is_v8961 =array_filter(array_map('intval', explode(',', (string) $m4is_a394[self::M4IS_O16860])));
 if(empty(array_intersect($m4is_v8961, $m4is_f406 ))){
return '';
 
}
}return $m4is_o498;
 
} public 
function m4is_s74(): void {
wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true );
 wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
 wp_enqueue_script('memberium-avada-select2', plugin_dir_url(__FILE__ ). '/js/select2_injection.js', ['jquery', 'select2'], null, true );
 wp_enqueue_style('memberium-avada-select2', plugin_dir_url(__FILE__ ). '/css/select2_css.css' );
 wp_localize_script('memberium-avada-select2', 'MEMBERIUM_MEMBERSHIPS', $this->m4is_m6617());
 wp_localize_script('memberium-avada-select2', 'MEMBERIUM_TAGS', $this->m4is_m1098());
 
} public 
function m4is_y52403(array $m4is_q87563 ): array {
foreach($m4is_q87563 as $shortcode_name =>$element ){
$m4is_q87563[$shortcode_name]['params'][]=['default' =>'any', 'description' =>'Show or hide this element depending on whether the current visitor is logged in or not.', 'group' =>'Extras',  'heading' =>'Viewer Login Status', 'param_name' =>self::M4IS_G095, 'type' =>'radio_button_set', 'value' =>['anonymous' =>'Logged-out Only', 'any' =>'Any Visitor', 'logged-in' =>'Logged-in Only', ], ];
 $m4is_q87563[$shortcode_name]['params'][]=['default' =>'', 'description' =>'Use this option to restrict this element to a user who has any active memberships.  Users without an active membership will not be able to view this element..', 'group' =>'Extras',  'heading' =>'Visible to any Membership', 'param_name' =>self::M4IS_G3167, 'type' =>'radio_button_set', 'value' =>['any' =>'Must Have Any Active Membership', '' =>'Show to Everyone', ], ];
 $m4is_q87563[$shortcode_name]['params'][]=['type' =>'textfield', 'heading' =>'Required Memberships', 'group' =>'Extras',  'param_name' =>self::M4IS_K196, 'default' =>'', 'options' =>$this->m4is_m6617(), 'description' =>'The user must have one of the selected memberships to view this element. This option is only used if "Visible to any Membership" is set to "Show to Everyone".', ];
 $m4is_q87563[$shortcode_name]['params'][]=['type' =>'textfield', 'heading' =>'Required Tags', 'group' =>'Extras',  'param_name' =>self::M4IS_O16860, 'default' =>'', 'description' =>'The user must have one of the selected tags to view this element.".', ];
 
}return $m4is_q87563;
 
}      private 
function m4is_m6617(): array {
$m4is_n21630 =m4is_e37682::m4is_c26();
 $m4is_m96240 =$m4is_n21630->m4is_s365();
 return $m4is_m96240;
 
} private 
function m4is_m1098(): array {
$m4is_l9321 =m4is_k865::m4is_z2906(true );
 $m4is_l9321 =$m4is_l9321['mc'];
 $m4is_z470 =[];
 $m4is_z470[]=['id' =>0, 'text' =>'(None)' ];
 foreach ((array)$m4is_l9321 as $m4is_h8269 =>$m4is_p786){
$m4is_z470[]=['id' =>$m4is_h8269, 'text' =>"{$m4is_p786
} ({$m4is_h8269
})" ];
 
}return $m4is_z470;
 
}private const M4IS_G3167 ='memberium-any-membership';
 private const M4IS_G095 ='memberium-logged-in-status';
 private const M4IS_K196 ='memberium-memberships';
 private const M4IS_O16860 ='memberium-tags';
 
}

