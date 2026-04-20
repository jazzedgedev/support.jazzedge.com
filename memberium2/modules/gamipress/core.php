<?php

/**
 * Copyright (c) 2021-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_w6715 {
private $m4is_d2346 =[];
 private $m4is_r1546;
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_d4861();
 $this->m4is_f3276();
 
}private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
}private 
function m4is_f3276(): void {
if(is_admin()){
require_once __DIR__ . '/admin.php';
 m4is_x73604::m4is_c26();
 
}
}private 
function m4is_d4861(): void {
add_filter('memberium/posts/unenhanced', [$this, 'm4is_v2664'], 10, 1 );
 add_filter('memberium/session/updated', [$this, 'm4is_m130'], 10, 2 );
 add_action('memberium/lms/completion', [$this, 'm4is_l347'], 10, 2 );
  add_action('gamipress_award_achievement', [$this, 'm4is_w7691'], 10, 2 );
 add_action('gamipress_update_user_rank', [$this, 'm4is_j71945'], 10, 5 );
 add_action('gamipress_award_rank_to_user', [$this, 'm4is_u78'], 10, 3 );
     
} private 
function m4is_e195(int $m4is_f087 =0 ): array {
$m4is_y66291 =['user_id' =>$m4is_f087, ];
 $m4is_e594 =gamipress_get_user_achievements($m4is_y66291 );
 return array_filter(array_map(function($m4is_g01658 ){
return $m4is_g01658->ID;
 
}, $m4is_e594));
 
} private 
function m4is_c857(int $m4is_f087 ){
$m4is_y66291 =['user_id' =>$m4is_f087, ];
 $m4is_s62034 =gamipress_get_user_achievements($m4is_y66291 );
 return array_map(function($m4is_g215 ){
return $m4is_g215->ID;
 
}, $m4is_s62034 );
 
}public 
function m4is_m130(int $m4is_f087, array $m4is_k824 ){
$m4is_l9321 =empty($m4is_k824['keap']['contact']['groups'])? []: array_filter(explode(',', $m4is_k824['keap']['contact']['groups']));
 if(empty($m4is_l9321 )){
return;
 
}$this->m4is_w1894($m4is_f087, $m4is_l9321 );
 $this->m4is_r823($m4is_f087, $m4is_l9321 );
 
}
function m4is_w1894(int $m4is_f087, array $m4is_l9321 ){
if(!function_exists('gamipress_award_achievement_to_user' )){
return;
 
}$m4is_v623 =get_option('memberium/gamipress/assign_by_tag', []);
 $m4is_v623 =is_array($m4is_v623 )? $m4is_v623 : [];
 if(empty($m4is_v623 )){
return;
 
}$m4is_e594 =$this->m4is_e195($m4is_f087 );
 foreach($m4is_v623 as $m4is_r14606 =>$m4is_d913 ){
if(!in_array($m4is_r14606, $m4is_e594 )){
if(in_array($m4is_d913, $m4is_l9321 )){
gamipress_award_achievement_to_user($m4is_r14606, $m4is_f087 );
 
}
}
}
}   
function m4is_r823(int $m4is_f087, array $m4is_l9321 ){
if(!function_exists('gamipress_award_rank_to_user' )){
return;
 
}$m4is_v623 =get_option('memberium/gamipress/rank/rank_by_tag', []);
 $m4is_v623 =is_array($m4is_v623 )? $m4is_v623 : [];
 if(empty($m4is_v623 )){
return;
 
} $m4is_y69 =$this->m4is_c857($m4is_f087 );
 foreach($m4is_y69 as $m4is_w4136 ){
unset($m4is_v623[$m4is_w4136]);
 
} foreach ($m4is_v623 as $m4is_e45 =>$m4is_d913 ){
if(in_array($m4is_d913, $m4is_l9321 )){
gamipress_award_rank_to_user($m4is_e45, $m4is_f087 );
 
}
}
}
function m4is_w7691(int $m4is_f087, int $m4is_r14606 ){
$m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
return;
 
}$m4is_v623 =get_option('memberium/gamipress/tag_by_badge', []);
 if(empty($m4is_v623 )||!is_array($m4is_v623 )){
return;
 
}if(array_key_exists($m4is_r14606, $m4is_v623 )){
$m4is_p786 =$m4is_v623[$m4is_r14606];
 $this->m4is_r1546->m4is_k98($m4is_p786, $m4is_h21895 );
 
}
}
function m4is_j71945(int $m4is_f087, $m4is_b7650, $old_rank, $admin_id, $achievement_id ){
$m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
return;
 
}$m4is_e45 =$m4is_b7650->ID;
 $m4is_v623 =get_option('memberium/gamipress/rank/tag_by_rank', []);
 if(empty($m4is_v623 )){
return;
 
}if(array_key_exists($m4is_e45, $m4is_v623 )){
$m4is_p786 =$m4is_v623[$m4is_e45];
 $this->m4is_r1546->m4is_k98($m4is_p786, $m4is_h21895 );
 
}
}
function m4is_u78(int $m4is_f087, int $m4is_e45, $m4is_y66291 ){
$m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
return;
 
}$m4is_v623 =get_option('memberium/gamipress/rank/tag_by_rank', []);
 if(empty($m4is_v623 )){
return;
 
}if(array_key_exists($m4is_e45, $m4is_v623 )){
$m4is_p786 =$m4is_v623[$m4is_e45];
 $this->m4is_r1546->m4is_k98($m4is_p786, $m4is_h21895 );
 
}
}   
function m4is_l347(int $m4is_f087, int $m4is_b4068 ){
if(!function_exists('gamipress_maybe_award_achievement_to_user')){
return;
 
}$m4is_y718 =get_post_meta($m4is_b4068, '_is4wp_learndash_achievement', true);
 if($m4is_y718){
gamipress_award_achievement_to_user($m4is_y718, $m4is_f087);
 
}
}
function m4is_v2664(array $m4is_l15 =[]): array {
$m4is_l15[]='achievement-type';
 $m4is_l15[]='nomination';
 $m4is_l15[]='step';
 $m4is_l15[]='submission';
 $achievements =gamipress_get_achievement_types_slugs();
 $m4is_l15 =array_merge($m4is_l15, $achievements);
 return $m4is_l15;
 
}
}

