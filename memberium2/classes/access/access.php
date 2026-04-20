<?php

/**
 * Proprietary Software - All Rights Reserved
 *
 * This file is part of the Memberium plugin, which is proprietary software developed by Web Power and Light.
 * Unauthorized copying, distribution, or modification of this file, via any medium, is strictly prohibited.
 *
 * Copyright (c) 2015-2024 David J Bullock
 * Web Power and Light
 *
 * For licensing information, please contact Web Power and Light.
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_v679 {
const NS ='memberium';
 const PREFIX ='is4wp';
 static 
function m4is_c26(){
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){

}
function m4is_p147(){
 add_filter('rest_pre_dispatch', [$this, 'm4is_m0795'], 10, 3 );
 add_action('wpal/block/access/init', [$this, 'm4is_q3661'], 1 );
 do_action('wpal/block/access/init' );
   if(version_compare(get_bloginfo('version' ), '5.4', '>=' )){
$this->m4is_c32();
 
}$this->m4is_h7519();
  $this->m4is_q169();
  
}   
function m4is_q3661(){
if(is_admin()){
add_action('enqueue_block_editor_assets', [$this->m4is_j16(), 'm4is_a3466' ], 1 );
  
}else{
 add_filter('render_block', [$this->m4is_b679(), 'm4is_w56' ], PHP_INT_MAX, 2 );
 
}
} 
function m4is_m0795($m4is_u6591, $m4is_i1656, $m4is_s0432 ){
if(strpos($m4is_s0432->get_route(), '/wp/v2/block-renderer' )!== false){
if(isset($m4is_s0432['attributes'])){
$m4is_b23 =$m4is_s0432['attributes'];
 if(is_array($m4is_b23 )&&!empty($m4is_b23 )){
foreach ($m4is_b23 as $m4is_o015 =>$m4is_k72){
if(strpos($m4is_o015, self::PREFIX )=== 0 ){
unset($m4is_b23[$m4is_o015]);
 
}
}$m4is_s0432['attributes']=$m4is_b23;
 
}
}
}return $m4is_u6591;
 
}   
function m4is_c32(){
$m4is_c05328 =false;
 if(wp_doing_ajax()){
$m4is_c05328 =isset($_POST['action'])? $_POST['action']: false;
 
}if(is_admin()||$m4is_c05328 === 'add-menu-item' ){
add_action('load-nav-menus.php', ['m4is_o9186', 'm4is_d4861'], 1);
   if($m4is_c05328 === 'add-menu-item' ){
m4is_o9186::m4is_d4861();
 
}
}elseif(!is_admin()||$m4is_c05328 ){
add_filter('wp_get_nav_menu_items', [$this->m4is_b679(), 'm4is_v671'], 1, 3);
 
}
}
function m4is_v0631($m4is_k09 ){
$m4is_r1692 =get_post_meta($m4is_k09, '_wpal/menu/access', true );
 return (!$m4is_r1692 ||!is_array($m4is_r1692)||empty($m4is_r1692))? []: $m4is_r1692;
 
}    public 
function m4is_h7519(): void {
$m4is_g35 ='m4is_m32784';
 add_action('in_widget_form', [$m4is_g35, 'm4is_w5179'], 10, 3 );
  add_filter('widget_update_callback', [$m4is_g35, 'm4is_u2634'], 10, 2 );
  if(is_admin()){
add_action('load-widgets.php', [$m4is_g35, 'm4is_m0697'], 1 );
  
}else{
add_filter('sidebars_widgets', [$this->m4is_b679(), 'm4is_y621'], 10 );
  add_filter('widget_display_callback', [$this->m4is_b679(), 'm4is_o635'], 10, 3 );
  
}
}   
function m4is_q169(){
if(is_admin()&&!wp_doing_ajax()){
add_action('load-term.php', ['m4is_m749', 'm4is_p20'], 1);
   $m4is_a27648 ='memberium/taxonomy/access';
 $m4is_b916 =isset($_POST["_{$m4is_a27648
}_name"])? $_POST["_{$m4is_a27648
}_name"]: false;
 if($m4is_b916 &&wp_verify_nonce($_POST["_{$m4is_a27648
}_name"], $m4is_a27648)){
m4is_m749::m4is_p20();
 
}
}else{
if(!m4is_r83::m4is_c26()->m4is_v461()){
add_action('pre_get_posts', [$this->m4is_b679(), 'm4is_h162']);
 add_filter('get_terms', [$this->m4is_b679(), 'm4is_o17590'], -1, 4);
 
}
}
} 
function m4is_t68274(){
static $m4is_q5696;
 if(is_null($m4is_q5696)){
$m4is_y66291 =['public' =>true, 'show_ui' =>true, ];
 $m4is_l87469 =get_taxonomies($m4is_y66291, 'names');
 foreach($m4is_l87469 as $m4is_l9671 =>$m4is_v586){
if(substr($m4is_l9671, -4, 4)== '_tag'){
unset($m4is_l87469[$m4is_l9671]);
 
}
}$m4is_l87469 =apply_filters('memberium/controlled/access/taxonomies', $m4is_l87469);
  $m4is_q5696 =is_array($m4is_l87469)? $m4is_l87469 : [];
 
}return $m4is_q5696;
 
}
function m4is_r89563($m4is_r538 ){
$m4is_r1692 =get_term_meta($m4is_r538, '_wpal/taxonomy/access', true);
 return (!$m4is_r1692 ||!is_array($m4is_r1692)||empty($m4is_r1692))? []: $m4is_r1692;
 
}   
function m4is_j16(): m4is_e37682 {
static $m4is_x25;
 return isset($m4is_x25 )? $m4is_x25 : $m4is_x25 =m4is_e37682::m4is_c26();
 
}
function m4is_b679(): m4is_w5132 {
static $m4is_f683;
 return isset($m4is_f683 )? $m4is_f683 : $m4is_f683 =m4is_w5132::m4is_c26();
 
}
}

