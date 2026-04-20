<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_z7893 {
   static private $m4is_r1546;
 static private $m4is_w2649 =false;
 static private $m4is_f683;
 static private $m4is_o0379 =0;
 static private $m4is_s71 =0;
 public static 
function m4is_t814(){
if(!m4is_s52::m4is_w74()){
return;
 
}global $wpdb, $wp_rewrite;
 self::$m4is_s71 =microtime(true );
 if(empty($wp_rewrite )){
$wp_rewrite =new WP_Rewrite();
 
}$m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field' );
 $m4is_r2369 =self::$m4is_r1546->m4is_j498('settings', 'local_auth_only', false );
 $m4is_q7686 =self::$m4is_r1546->m4is_j498('settings', 'site_ban_tag' );
 $m4is_o86 =empty($_GET['auth_key'])? '' : trim($_GET['auth_key']);
 $m4is_h21895 =empty($_GET['Id'])? 0 : (int) $_GET['Id'];
 $m4is_h21895 =empty($_GET['contactId'])? $m4is_h21895 : (int) trim($_GET['contactId']);
 $m4is_c6016 =empty($_GET['orderId'])? 0 : (int) $_GET['orderId'];
 $m4is_v64 =empty($_GET['Email'])? '' :$_GET['Email'];
 $m4is_v64 =empty($_GET['Contact0Email'])? $m4is_v64 : $_GET['Contact0Email'];
 $m4is_v64 =empty($_GET['inf_field_Email'])? $m4is_v64 : $_GET['inf_field_Email'];
 $m4is_v64 =strtolower(trim(strtr($m4is_v64, ' ', '+' )));
 $m4is_s15437 =empty($_GET['tag_ids'])? '' : trim($_GET['tag_ids']);
 $m4is_l17096 =false;
 $m4is_c736 =false;
 $m4is_p96836 =!empty($_GET['inf_field_BrowserLanguage']);
 $m4is_p708 =false;
 $m4is_q37596 =self::m4is_z66($_GET);
 self::m4is_k3269('Starting Request at ' . self::$m4is_s71 );
  if(!empty($m4is_q37596 )){
self::$m4is_f683->m4is_w6289($m4is_q37596 );
 
} self::m4is_k3269('Starting Order Check at ' . microtime(true ). ' / ' . (microtime(true )- self::$m4is_s71 ));
 if(empty($m4is_h21895 )&&!empty($m4is_c6016 )){
$m4is_h21895 =self::m4is_k12($m4is_c6016 );
 
}self::m4is_k3269('Completing Order Check at ' . microtime(true ). ' / ' . (microtime(true )- self::$m4is_s71 ));
 self::$m4is_o0379 =m4is_q62395::m4is_v729($m4is_h21895, 'autologin', 'Autologin' );
 $m4is_u076 =empty($_SERVER['REQUEST_URI'])? serialize($_GET ): $_SERVER['REQUEST_URI'];
 m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Parameters ' . $m4is_u076 );
 self::m4is_k3269(__LINE__ . ' - NOTICE:  Email = ' . $m4is_v64);
 self::m4is_k3269(__LINE__ . ' - NOTICE:  Contact ID = ' . $m4is_h21895);
 self::m4is_k3269(__LINE__ . ' - NOTICE:  Order ID = ' . $m4is_c6016);
 self::m4is_k3269(__LINE__ . ' - NOTICE:  Redirect URL set to ' . $m4is_q37596);
 self::m4is_m136($m4is_o86 );
 self::m4is_b1672($m4is_h21895 );
 self::m4is_x38429($m4is_v64 );
 $m4is_i935 =self::m4is_c78693($m4is_h21895 );
 self::m4is_u583($m4is_i935 );
 $m4is_i935 =self::m4is_i867($m4is_h21895, $m4is_v64, $m4is_i935 );
 self::m4is_n40($m4is_h21895, $m4is_v64, $m4is_i935);
 $m4is_l17096 =get_user_by('email', $m4is_v64 );
 self::m4is_o30142($m4is_l17096);
 self::m4is_k3269('Starting WP User Update at ' . microtime(true ). ' / ' . (microtime(true )- self::$m4is_s71));
 self::$m4is_r1546->m4is_f605($m4is_h21895 );
 $m4is_l17096 =get_user_by('email', $m4is_v64 );
 self::m4is_k3269('Completing WP User Update at ' . microtime(true ). ' / ' . (microtime(true )- self::$m4is_s71 ));
 self::m4is_h46($m4is_l17096 );
 self::m4is_d13($m4is_l17096 );
 self::m4is_b16495($m4is_h21895, $m4is_q37596 );
 self::m4is_k3269('Starting Login at ' . microtime(true ). ' / ' . (microtime(true )- self::$m4is_s71 ));
 self::m4is_h60689($m4is_s15437, $m4is_h21895 );
 $m4is_f087 =$m4is_l17096->ID;
 $_POST['pwd']=$m4is_i935[$m4is_r6234]?? '';
 m4is_q82::m4is_u687($m4is_f087 );
 $m4is_l17096 =m4is_l5841::m4is_c09($m4is_l17096);
 if(is_a($m4is_l17096, 'WP_User' )){
m4is_l5841::m4is_e40($m4is_v64 );
 wp_set_current_user($m4is_f087);
 $m4is_l17096 =apply_filters('wp_authenticate_user', $m4is_l17096, '');
 self::$m4is_r1546->m4is_s965('do_autologin');
 
}self::m4is_k3269('Completing Login at ' . microtime(true ). ' / ' . (microtime(true )- self::$m4is_s71 ));
 self::m4is_q91423($m4is_l17096);
 setcookie('memberium_autologin_session', 1, 0, COOKIEPATH, COOKIE_DOMAIN, false, true );
 wp_set_auth_cookie($m4is_f087 );
 self::$m4is_f683->m4is_g38965($m4is_i935, $m4is_f087);
 $m4is_q37596 =self::m4is_w46036($m4is_q37596, $m4is_l17096 );
 if(self::$m4is_w2649){
echo 'Completing Successful Login at ', microtime(true ), ' / ', microtime(true )- self::$m4is_s71 , '<br />';
 echo '<pre>', print_r(m4is_q82::m4is_d59($m4is_f087 ), true), '</pre>';
 die();
 
}do_action('wp_login', $m4is_v64, $m4is_l17096 );
 session_write_close();
 wp_redirect($m4is_q37596, 302, 'Memberium Autologin' );
 exit;
 
} public static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_f683 =m4is_f58::m4is_c26();
 self::$m4is_w2649 =!empty($_GET['debug']);
 m4is_j586::m4is_k751();
 
}static private 
function m4is_k3269($m4is_a173 ){
if(self::$m4is_w2649){
echo $m4is_a173, '<br>';
 
}
}static private 
function m4is_k12($m4is_c6016 =0 ){
$m4is_h21895 =0;
 $m4is_a89 =['ContactId'];
 $m4is_b9687 =m4is_c69807::m4is_b6614('Job', $m4is_c6016, $m4is_a89 );
 if(is_array($m4is_b9687 )){
$m4is_h21895 =(int) $m4is_b9687['ContactId'];
 
}else{
sleep(1 );
 $m4is_b9687 =m4is_c69807::m4is_b6614('Job', $m4is_c6016, $m4is_a89);
 if(is_array($m4is_b9687 )){
$m4is_h21895 =(int) $m4is_b9687['ContactId'];
 
}
}return $m4is_h21895;
 
}static private 
function m4is_w827($m4is_l17096 ){
 $m4is_a519 =false;
 if(is_a($m4is_l17096, 'WP_User')){
$m4is_k82 =['manage_options', 'edit_plugins', 'edit_themes', 'edit_users', 'create_users', 'delete_users', 'delete_others_pages', 'delete_others_posts', 'edit_others_pages', 'edit_others_posts', ];
 foreach($m4is_k82 as $m4is_d51){
if(user_can($m4is_l17096, $m4is_d51)){
$m4is_a519 =true;
 m4is_q62395::m4is_x6835(self::$m4is_o0379, 'User is a non-subscriber:  ' . $m4is_d51);
 if(self::$m4is_w2649)echo __LINE__, ' - Blocked Capability:  ' . $m4is_d51 . '<br />';
 break;
 
}
}
}return $m4is_a519;
 
} static private 
function m4is_z66($m4is_j613 ){
$m4is_q37596 =empty($m4is_j613['redir'])? '' : $m4is_j613['redir'];
 $m4is_u897 =self::m4is_m49($m4is_j613 );
 $m4is_x3645 =apply_filters('memberium/autologin/redirect/parameters', $m4is_u897, $m4is_j613);
 foreach($m4is_x3645 as $m4is_l9671 =>$m4is_v586){
$m4is_x3645[$m4is_l9671]=rawurlencode($m4is_v586);
 
}return add_query_arg($m4is_x3645, $m4is_q37596);
 
} static private 
function m4is_m49($m4is_j613 =[]){
$m4is_u450 =['affiliate', 'utm_campaign', 'utm_content', 'utm_medium', 'utm_source', 'utm_term', ];
 $m4is_u450 =apply_filters('memberium/autologin/redirect/whitelised_params', $m4is_u450);
 $m4is_u897 =[];
 foreach($m4is_u450 as $m4is_l9671 ){
if(isset($m4is_j613[$m4is_l9671])){
$m4is_u897[$m4is_l9671]=$m4is_j613[$m4is_l9671];
 
}
}return $m4is_u897;
 
} static private 
function m4is_m136($m4is_o86 ='' ){
$m4is_u4657 =array_filter(explode(',', self::$m4is_r1546->m4is_j498('settings', 'autologin_authkeys' )));
 if(empty($m4is_o86)||!in_array($m4is_o86, $m4is_u4657)){
m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Failure - Auth Key Mismatch');
 self::m4is_k3269(__LINE__ . ' - FAILURE:  Auth Key mismatch');
 if(!self::$m4is_w2649){
wp_redirect(get_bloginfo('wpurl'), 302, 'Memberium Autologin Failure');
 
}exit;
 
}
}static private 
function m4is_b1672($m4is_h21895 =0 ){
if(!$m4is_h21895){
m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Failure - No Contact Id');
 self::m4is_k3269(__LINE__ . ' - FAILURE:  Invalid Contact ID');
 if(!self::$m4is_w2649){
wp_redirect(get_bloginfo('wpurl'), 302, 'Memberium Autologin Failure');
 
}exit;
 
}
}static private 
function m4is_x38429($m4is_v64 ){
if(empty($m4is_v64)){
m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Failure - Email address not populated');
 self::m4is_k3269(__LINE__ . ' - FAILURE:  Email address not populated');
 if(!self::$m4is_w2649){
wp_redirect(get_bloginfo('wpurl'), 302, 'Memberium Autologin Failure');
 
}exit;
 
}
}static private 
function m4is_u583($m4is_i935 ){
if(empty($m4is_i935)){
m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Failure - No contact data found');
 self::m4is_k3269(__LINE__ . ' - FAILURE:  Empty Contact Record');
 if(!self::$m4is_w2649){
wp_redirect(get_bloginfo('wpurl'), 302, 'Memberium Autologin Failure');
 
}exit;
 
}
} static private 
function m4is_h46($m4is_l17096){
if(!$m4is_l17096){
m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Failure - User not found');
 self::m4is_k3269(__LINE__ . ' - FAILURE:  User not found');
 if(!self::$m4is_w2649){
wp_redirect(get_bloginfo('wpurl'), 302, 'Memberium Autologin Failure');
 
}exit;
 
}
} static private 
function m4is_d13($m4is_l17096){
$m4is_a519 =self::m4is_w827($m4is_l17096);
 if($m4is_a519){
$m4is_q847 ='FAILURE:  Auto-Login attempt by non-subscriber';
 self::m4is_k3269($m4is_q847);
 m4is_q62395::m4is_x6835(self::$m4is_o0379, $m4is_q847);
  wp_redirect(get_bloginfo('wpurl'), 302, 'Memberium Autologin Failure');
 exit;
 
}self::m4is_k3269('Role Check Complete');
 
} static private 
function m4is_c78693(int $m4is_h21895 ){
$m4is_z30578 =self::$m4is_r1546->m4is_j498('settings', 'max_contact_age', 0 );
 $m4is_i935 =m4is_p40::m4is_p67($m4is_h21895, false, true );
 $m4is_p708 =empty($m4is_i935 );
 $m4is_p708 =$m4is_p708 ||empty($m4is_i935['!LastUpdated']);
 $m4is_p708 =$m4is_p708 ||(time()- $m4is_i935['!LastUpdated']> $m4is_z30578 );
 if($m4is_p708 ){
self::m4is_k3269('Starting Contact Sync at ' . microtime(true ). ' / ' . (microtime(true )- self::$m4is_s71 ));
 self::$m4is_r1546->m4is_x4831($m4is_h21895 );
 $m4is_i935 =m4is_p40::m4is_p67($m4is_h21895 );
 self::m4is_k3269('Completing Contact Sync at ' . microtime(true ). ' / ' . (microtime(true )- self::$m4is_s71 ));
 
}return $m4is_i935;
 
} static private 
function m4is_n40($m4is_h21895, $m4is_v64, $m4is_i935 ){
$m4is_e75 =empty($m4is_i935['Id'])? 0 : (int) $m4is_i935['Id'];
 $m4is_q617 =empty($m4is_i935['Email'])? 0 : strtolower(trim($m4is_i935['Email']));
 if($m4is_e75 <> $m4is_h21895 ||$m4is_v64 <> $m4is_q617){
m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Failure - Autologin does not match CRM');
 self::m4is_k3269('Autologin contact does not match CRM contact');
 if(!self::$m4is_w2649){
wp_redirect(get_bloginfo('wpurl'), 302, 'Memberium Autologin Failure');
 
}exit;
 
}
} static private 
function m4is_i867($m4is_h21895, $m4is_v64, $m4is_i935){
$m4is_s8913 =empty($_GET['forcelogin'])? '' : $_GET['forcelogin'];
 if($m4is_s8913){
$m4is_l17096 =get_user_by('email', $m4is_v64);
 $m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field');
 if(empty($m4is_i935[$m4is_r6234])){
$m4is_q6570 =self::$m4is_r1546->m4is_a601();
 $m4is_i935[$m4is_r6234]=$m4is_q6570;
 self::$m4is_r1546->m4is_w05782($m4is_h21895, [$m4is_r6234 =>$m4is_q6570], true);
 self::$m4is_r1546->m4is_v1694($m4is_i935);
 
}if(!$m4is_l17096){
self::$m4is_r1546->m4is_f605($m4is_h21895);
 
}self::m4is_k3269('Force Login at ' . microtime(true ). ' / ' . (microtime(true )- self::$m4is_s71));
 
}return $m4is_i935;
 
} static private 
function m4is_o30142($m4is_l17096){
$m4is_f72 =self::$m4is_r1546->m4is_j498('settings', 'known_logins_only', false);
 if($m4is_f72){
if(!$m4is_l17096){
self::m4is_k3269(__LINE__ . ' - FAILURE:  No matching WP User, known logins only');
 m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Known Login Check Failed.');
 if(!self::$m4is_w2649){
wp_redirect(get_bloginfo('wpurl'), 302, 'Memberium Autologin Failure');
 
}exit;
 
}
}
} static private 
function m4is_q91423($m4is_l17096){
if(!is_a($m4is_l17096, 'WP_User')){
$m4is_q847 =is_a($m4is_l17096, 'WP_Error')? strip_tags($m4is_l17096->get_error_message()): 'Authentication / Login Error';
 wp_set_current_user(0);
 self::m4is_k3269('FAILURE:  ' . $m4is_q847);
 m4is_q62395::m4is_x6835(self::$m4is_o0379, $m4is_q847);
 if(!self::$m4is_w2649){
wp_redirect(get_bloginfo('wpurl'), 302, 'Memberium Autologin Failure');
 
}exit;
 
}
} static private 
function m4is_b16495($m4is_h21895, $m4is_q37596 ){
if(is_user_logged_in()){
$m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_q847 ='Login Session Exists, Logging Out';
 $m4is_i316 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'crm_id', 0 );
 m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Already Logged In, Redirecting');
 self::m4is_k3269($m4is_q847);
 if($m4is_i316 === $m4is_h21895){
$m4is_v27639 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'login_page', 0 );
 if(empty($m4is_q37596 )){
if(!empty($m4is_v27639 )){
$m4is_q37596 =get_permalink($m4is_v27639 );
 
}
}if(empty($m4is_q37596)){
$m4is_q37596 =get_site_url();
 
}wp_redirect($m4is_q37596, 302, 'Memberium Autologin - User Logged In');
 exit;
 
}m4is_q62395::m4is_x6835(self::$m4is_o0379, $m4is_q847);
 wp_destroy_current_session();
 wp_clear_auth_cookie();
 self::$m4is_r1546->m4is_i80956();
 
}
} static private 
function m4is_w46036($m4is_q37596, $m4is_l17096){
$m4is_f087 =$m4is_l17096->ID;
 if(!empty($m4is_q37596 )){
return $m4is_q37596;
 
}$m4is_b4068 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'login_page', 0 );
 $m4is_q37596 =get_permalink($m4is_b4068 );
 if(!empty($m4is_q37596 )){
return $m4is_q37596;
 
}return get_home_url();
 
} static private 
function m4is_h60689($m4is_s15437, $m4is_h21895 ){
if(!empty($m4is_s15437 )){
self::m4is_k3269('Starting Adding Tags at ' . microtime(true ). ' / ' . (microtime(true )- self::$m4is_s71));
 self::m4is_k3269('Setting Tags: ' . $m4is_s15437);
 m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Setting Tags', $m4is_s15437 );
 self::$m4is_r1546->m4is_k98($m4is_s15437, $m4is_h21895 );
 self::m4is_k3269('Completing Adding Tags at ' . microtime(true ). ' / ' . (microtime(true )- self::$m4is_s71 ));
 
}
} 
}

