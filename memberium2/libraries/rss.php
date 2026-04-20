<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_a391 {
private static $m4is_w36470;
 private static $m4is_r1546;
    static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_w36470 ='memberium/rss_user_id';
 
}private 
function __construct(){

}    public static 
function m4is_v5960($m4is_l17096 =null ){
$m4is_f087 =is_object($m4is_l17096 )&&is_a($m4is_l17096, 'WP_User' )? $m4is_l17096->ID : (int) $m4is_l17096;
 $m4is_f087 =$m4is_f087 ? $m4is_f087 : self::$m4is_r1546->m4is_x66();
 if($m4is_f087 ){
global $wpdb;
 $m4is_m60 =false;
 $m4is_l16 =0;
 do {
$m4is_t584 =wp_generate_password(18, false, false);
 if(!self::m4is_b637($m4is_t584)){
 $m4is_j67631 =self::$m4is_w36470;
 $m4is_l9671 =self::$m4is_w36470 . '/' . $m4is_t584;
 update_user_meta($m4is_f087, $m4is_l9671, $m4is_t584);
 update_user_meta($m4is_f087, self::$m4is_w36470, $m4is_t584);
 $m4is_m60 =true;
 
}
}while ($m4is_m60 == false );
  $m4is_v2613 ="DELETE FROM `{$wpdb->usermeta
}` WHERE `user_id` = %d AND `meta_key` <> %s AND ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f087, $m4is_l9671);
 $m4is_v2613 .= " `meta_key` LIKE '{$m4is_j67631
}%' ";
 $wpdb->query($m4is_v2613);
 do_action('memberium/rssid/set', $m4is_f087);
 
}
} static 
function m4is_s95(){
add_action('do_feed_atom_comments', [__CLASS__, 'm4is_b64'], 1);
 add_action('do_feed_atom', [__CLASS__, 'm4is_b64'], 1);
 add_action('do_feed_rdf', [__CLASS__, 'm4is_b64'], 1);
 add_action('do_feed_rss', [__CLASS__, 'm4is_b64'], 1);
 add_action('do_feed_rss2_comments', [__CLASS__, 'm4is_b64'], 1);
 add_action('do_feed_rss2', [__CLASS__, 'm4is_b64'], 1);
 add_action('do_feed', [__CLASS__, 'm4is_b64'], 1);
 remove_action('wp_head', 'feed_links_extra', 3);
 remove_action('wp_head', 'feed_links', 2);
 remove_action('wp', 'bp_activity_action_favorites_feed', 3);
 remove_action('wp', 'bp_activity_action_friends_feed', 3);
 remove_action('wp', 'bp_activity_action_mentions_feed', 3);
 remove_action('wp', 'bp_activity_action_my_groups_feed', 3);
 remove_action('wp', 'bp_activity_action_personal_feed', 3);
 remove_action('wp', 'bp_activity_action_sitewide_feed', 3);
 remove_action('wp', 'groups_action_group_feed', 3);
 
}  static 
function m4is_e9432($m4is_r02674, $m4is_v76912 ){
if(!is_feed()){
return $m4is_r02674;
 
}$m4is_h4320 =isset($_GET['rss_user'])? $_GET['rss_user']: '';
 if(!$m4is_h4320 ){
return $m4is_r02674;
 
}$m4is_f087 =(int) self::m4is_b637($m4is_h4320 );
 if(!$m4is_f087 ){
return $m4is_r02674;
 
}if(wp_set_current_user($m4is_f087 )){
 do_action('memberium/rssid/rsslogin/', $m4is_f087 );
 
}return $m4is_r02674;
 
} static 
function m4is_g29870(int $m4is_f087 ){
$m4is_f087 =$m4is_f087 ? $m4is_f087 : self::$m4is_r1546->m4is_x66();
 if($m4is_f087){
$m4is_t584 =get_user_meta($m4is_f087, self::$m4is_w36470, true);
 global $wpdb;
 $m4is_j67631 =self::$m4is_w36470;
 $m4is_v2613 ="SELECT `meta_value` FROM `{$wpdb->usermeta
}` WHERE `user_id` = %d AND ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f087);
 $m4is_v2613 .= " `meta_key` LIKE '{$m4is_j67631
}/%' ORDER BY `umeta_id` ASC LIMIT 1";
 $m4is_g415 =$wpdb->get_var($m4is_v2613);
 if(empty($m4is_g415)){
$m4is_g415 =self::m4is_v5960($m4is_f087);
 
}
}if($m4is_g415){
return $m4is_g415;
 
}return false;
 
}public static 
function m4is_c90871($m4is_l17096 =null){
global $wpdb;
 $m4is_f087 =(is_object($m4is_l17096)&&get_class($m4is_l17096)== 'WP_User')? $m4is_l17096->ID : (int) $m4is_l17096;
 $m4is_f087 =($m4is_f087)? $m4is_f087 : self::$m4is_r1546->m4is_x66();
 if(!$m4is_f087 ){
return false;
 
}$m4is_j67631 =self::$m4is_w36470;
 $m4is_v2613 =$wpdb->prepare("DELETE FROM %i WHERE `user_id` = %d AND ", $wpdb->usermeta, $m4is_f087 ). " `meta_key` LIKE '{$m4is_j67631
}/%' ";
 $wpdb->query($m4is_v2613);
 do_action('memberium/rssid/reset/', $m4is_f087 );
 return true;
 
}private static 
function m4is_b637($m4is_h4320 =''){
global $wpdb;
 $m4is_h4320 =sanitize_text_field($m4is_h4320);
 $m4is_l9671 =self::$m4is_w36470 . '/' . $m4is_h4320;
 $m4is_v2613 ="SELECT `user_id` FROM `{$wpdb->usermeta
}` WHERE `meta_key` = %s AND `meta_value` = %s ORDER BY `user_id` ASC LIMIT 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_l9671, $m4is_h4320);
 $m4is_f087 =(int) $wpdb->get_var($m4is_v2613);
 return $m4is_f087;
 
}static 
function m4is_b64(){
$m4is_y66291 =['response' =>403, 'code' =>__('Public RSS Feed Unavailable'), 'exit' =>true, ];
 wp_die(__('No feed available, please visit our homepage.'), __('Access Denied'), $m4is_y66291 );
 
}
}

