<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_w831 {
 
function __construct(){

} private static 
function m4is_l214(): array {
$m4is_k698 =defined('MEMBERIUM_SKU' )? constant('MEMBERIUM_SKU' ): '';
 if($m4is_k698 == 'm4is' ){
$m4is_x3066 =['access_tags' =>'_is4wp_access_tags', 'access_tags2' =>'_is4wp_access_tags2', 'anonymous_only' =>'_is4wp_anonymous_only', 'any_loggedin_user' =>'_is4wp_any_loggedin_user', 'any_membership' =>'_is4wp_any_membership', 'can_comment' =>'_is4wp_can_comment', 'commenter_action' =>'_is4wp_commenter_action', 'commenter_goal' =>'_is4wp_commenter_goal', 'commenter_tag' =>'_is4wp_commenter_tag', 'contact_ids' =>'_is4wp_contact_ids', 'custom_code' =>'_iswp_custom_code', 'discourage_cache' =>'_is4wp_discourage_cache', 'facebook_crawler' =>'_is4wp_facebook_crawler', 'force_public' =>'_is4wp_force_public', 'google_1st_click' =>'_is4wp_google_1stclick', 'hide_from_menu' =>'_is4wp_hide_from_menu', 'memberships' =>'_is4wp_membership_levels', 'private_comments' =>'_is4wp_private_comments', 'prohibited_action' =>'_is4wp_prohibited_action', 'redirect_url' =>'_is4wp_redirect_url', ];
 
}elseif($m4is_k698 == 'm4ac' ){
$m4is_x3066 =[];
 
}return $m4is_x3066;
 
}static 
function m4is_e513(int $m4is_b4068 ): array {
$m4is_x3066 =self::m4is_l214();
 $m4is_l79562 =get_post_meta($m4is_b4068 );
 $m4is_e0213 =false;
 if(is_array($m4is_l79562 )&&!empty($m4is_l79562 )){
$m4is_e0213 =[];
 foreach ($m4is_x3066 as $m4is_o015 =>$m4is_k72 ){
if(isset($m4is_l79562[$m4is_k72][0])){
$m4is_e0213[$m4is_o015]=$m4is_l79562[$m4is_k72][0];
 
}
}
}return $m4is_e0213;
 
}static 
function m4is_f0691(int $m4is_b4068 =0, $m4is_b9263 =[], $m4is_v586 =null ){
if(empty($m4is_b9263 )||empty($m4is_b4068 )){
return false;
 
}if(!current_user_can('edit_post', $m4is_b4068 )){
return false;
 
}if(is_string($m4is_b9263 )){
$m4is_b9263 =[$m4is_b9263 =>$m4is_v586, ];
 
}$m4is_e396 =['any_loggedin_user', 'any_membership', 'facebook_crawler', 'google_1st_click', 'hide_completely', 'hide_from_menu', 'private_comments', ];
 $m4is_x3066 =self::m4is_l214();
  foreach($m4is_b9263 as $m4is_l9671 =>$m4is_v586 ){
if(array_key_exists($m4is_l9671, $m4is_x3066 )){
$m4is_v586 =array_key_exists($m4is_l9671, $m4is_e396 )? (int) (bool) trim($m4is_v586 ): $m4is_v586;
 $m4is_v586 =is_string($m4is_v586 )? trim($m4is_v586 ): $m4is_v586;
 add_post_meta($m4is_b4068, $m4is_x3066[$m4is_l9671], $m4is_v586, true )or update_post_meta($m4is_b4068, $m4is_x3066[$m4is_l9671], $m4is_v586 );
 
}else{
 
}
}
} 
}

