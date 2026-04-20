<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 
class m4is_p6152 {
private static $m4is_x25;
 private static $m4is_r1546;
 private static $m4is_b51936;
 static 
function m4is_z95(){
self::m4is_h269();
 self::m4is_b0296();
 self::$m4is_r1546->m4is_s965('view_options' );
 
}private static 
function m4is_h269(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_x25 =m4is_s6729::m4is_c26();
 self::$m4is_b51936 =isset($_GET['tab'])? $_GET['tab']: 'login';
 
}private static 
function m4is_b0296(){
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}if(empty($_POST['memberium_options_nonce'])){
return;
 
}if(!wp_verify_nonce($_POST['memberium_options_nonce'], self::$m4is_r1546->m4is_j541())){
wp_die('nonce error' );
 return;
 
}self::m4is_a4906();
 self::m4is_c6958();
 self::m4is_r8491();
 self::m4is_p87();
 self::m4is_g23();
 
}private static 
function m4is_a4906(){
if(isset($_POST['pages'])&&is_array($_POST['pages'])){
$m4is_e5906 =(array)get_option('memberium_pages', []);
 foreach ($_POST['pages']as $m4is_l9671 =>$m4is_v586 ){
$m4is_e5906[$m4is_l9671]=$m4is_v586;
 
}update_option('memberium_pages', $m4is_e5906, true );
 
}
}private static 
function m4is_c6958(){
if(!isset($_POST['extensions'])){
return;
 
}$m4is_s6178 =get_option('memberium_extensions', []);
 foreach($_POST['extensions']as $m4is_l9671 =>$m4is_v586 ){
$m4is_s6178[$m4is_l9671]=$m4is_v586;
 
}update_option('memberium_extensions', $m4is_s6178, true );
 
}private static 
function m4is_r8491(){
$m4is_e0213 =self::$m4is_r1546->m4is_j498('settings' );
  $m4is_u450 =['allow_autologin', 'allow_local_logins', 'allow_wpadmin', 'attachment_pages', 'autogenerate_excerpts', 'autoupdate', 'beta_update_check',  'cache_bust', 'cache_flush', 'db_sessions', 'disable_displayname_update', 'disable_login_sync', 'disable_lost_password', 'disable_password_reset', 'disable_xframe', 'dynamic_menus', 'enable_slug_update', 'extended_reg_fields', 'fast_user_list', 'force_learndash_inheritance', 'httppost_log', 'known_logins_only', 'local_auth_only', 'login_log', 'microcache_compat_session', 'multi_language', 'page_inheritance', 'persistent_login', 'plaintext_db', 'preview_mode', 'protect_feeds', 'recaptcha_v2', 'require_membership', 'show_advanced_options', 'show_post_columns', 'simultaneous_logins', 'site_lock_enabled', 'sync_affiliate', 'sync_ecommerce', 'sync_meta_updates', 'sync_new_wp_users', 'sync_tag_details', 'telemetry', 'two_pass_shortcode_filter', ];
 foreach($m4is_u450 as $m4is_l9671 ){
$m4is_e0213[$m4is_l9671]=isset($_POST[$m4is_l9671])? (int) (bool) trim($_POST[$m4is_l9671]): $m4is_e0213[$m4is_l9671];
 
} $m4is_u450 =['allow_wpadmin_dashboard', 'allow_wpadmin_role', 'allow_wpadmin_titlebar', 'debug_ip', 'default_page_redirect', 'default_prohibited_action', 'default_reglink_tag', 'displayname_format', 'facebook_app_id', 'global_excerpt', 'include_default_excerpt', 'last_login_field', 'password_field', 'recaptcha_v2_secret_key', 'recaptcha_v2_site_key', 'registration_url', 'spiffy_api_key', 'spiffy_subdomain', 'username_field', ];
 foreach($m4is_u450 as $m4is_l9671 ){
$m4is_e0213[$m4is_l9671]=isset($_POST[$m4is_l9671])? stripslashes(trim($_POST[$m4is_l9671])): $m4is_e0213[$m4is_l9671];
 
} $m4is_u450 =['async_limit', 'async_tag', 'autologout_time', 'bruteforce_check', 'excerpt_length',  'login_actionset', 'login_log_length', 'login_tag', 'login_url', 'logout_actionset', 'logout_tag', 'max_affiliate_age', 'max_contact_age', 'maximum_login_ips', 'maximum_login_timeframe', 'min_password_length', 'new_user_registration_tag', 'password_reset_tag', 'password_strength', 'session_timeout', 'site_ban_tag', 'wp_autop',  ];
 foreach($m4is_u450 as $m4is_l9671 ){
$m4is_e0213[$m4is_l9671]=isset($_POST[$m4is_l9671])? (int) trim($_POST[$m4is_l9671]): $m4is_e0213[$m4is_l9671];
 
} if(isset($m4is_e0213['login_log_length'])&&isset($m4is_e0213['maximum_login_timeframe'])&&$m4is_e0213['login_log_length']> 0 ){
if($m4is_e0213['login_log_length']< ($m4is_e0213['maximum_login_timeframe']/ 24 )){
$m4is_e0213['login_log_length']=ceil($m4is_e0213['maximum_login_timeframe']/ 24 );
 
}
}self::$m4is_r1546->m4is_d64918($m4is_e0213, 'settings' );
 
}private static 
function m4is_p87(){
if(!isset($_POST['autologin_authkeys'])){
return;
 
}$m4is_e0213 =self::$m4is_r1546->m4is_j498('settings' );
 $m4is_t16867 =array_filter(explode(',', $_POST['autologin_authkeys']));
 $m4is_g62 =array_filter(explode(',', self::$m4is_r1546->m4is_z40()->getConfigurationOption('http_post_key' )));
 $m4is_b37 =false;
 foreach ($m4is_t16867 as $m4is_d07693 =>$m4is_l9671 ){
$m4is_l9671 =trim($m4is_l9671 );
 foreach ($m4is_g62 as $m4is_c198 ){
if(strtolower(trim($m4is_c198 )== strtolower(trim($m4is_l9671 )))){
$m4is_b37 =true;
 unset($m4is_t16867[$m4is_d07693]);
 
}
}
}$m4is_e0213['autologin_authkeys']=trim(implode(',', array_filter($m4is_t16867 )), ',' );
 self::$m4is_r1546->m4is_d64918($m4is_e0213, 'settings' );
 
}private static 
function m4is_y50649(){
if(!isset($_POST['page_load'])){
return;
 
}if($_POST['page_load']== 'Load All Templates' ){
$m4is_p8310 =self::$m4is_x25->m4is_n6203();
 foreach($m4is_p8310 as $template_id =>$template ){
self::$m4is_x25->m4is_o68190(0, $template_id );
 
}
}elseif($_POST['page_load']== 'Load Single Template' ){
$m4is_a638 =(int) $_POST['target_post_id'];
 $m4is_o809 =(int) $_POST['template_id']- 1;
 self::$m4is_x25->m4is_o68190($m4is_a638, $m4is_o809 );
 
}elseif($_POST['page_load']== 'Install Email Templates' ){
$m4is_y758 =self::$m4is_x25->m4is_g10548();
 
}
}private static 
function m4is_g23(){
if(!isset($_POST['new_crm_field'])){
return;
 
}$_POST['new_crm_field']=trim($_POST['new_crm_field']);
 m4is_s695::m4is_j2847($_POST['new_crm_field']);
 
}
}m4is_p6152::m4is_z95();
 global $wpdb;
 m4is_s6729::m4is_c26()->m4is_a4215();
 $_GET['tab']=isset($_GET['tab'])? $_GET['tab']: 'login';
 $m4is_x39508 =stripslashes_deep(m4is_r83::m4is_c26()->get_i2sdk_options());
 $m4is_e5906 =get_option('memberium_pages', []);
 $m4is_e0213 =m4is_r83::m4is_c26()->m4is_j498('settings' );
 $m4is_a6935 =m4is_r83::m4is_c26()->m4is_j498('infusionsoft' );
  if($_SERVER['REQUEST_METHOD']== 'POST' ){
if(empty($_POST['memberium_options_nonce'])){
return;
 
}if(!wp_verify_nonce($_POST['memberium_options_nonce'], m4is_r83::m4is_c26()->m4is_j541())){
wp_die('nonce error' );
 return;
 
}if(isset($_GET['tab'])){
if($_GET['tab']== 'general' ){
$m4is_n0216 =explode(',', $m4is_a6935['ignore_contact_fields']);
 $m4is_n0216 =array_flip($m4is_n0216 );
 unset($m4is_n0216[$m4is_e0213['password_field']]);
 unset($m4is_n0216[$m4is_e0213['username_field']]);
 $m4is_n0216 =array_flip($m4is_n0216 );
 $m4is_a6935['ignore_contact_fields']=implode(',', $m4is_n0216 );
 m4is_h65::m4is_z896('General Options Updated' );
 
}elseif($_GET['tab']== 'extensions' ){
m4is_h65::m4is_z896('Updates Options Updated' );
 
}elseif($_GET['tab']== 'http-post' ){
$_POST['autologin_authkeys']=trim(implode(',', $m4is_t16867 ), ',' );
 m4is_h65::m4is_z896('Options Updated' );
 if($m4is_b37 ){
m4is_h65::m4is_z896('You cannot re-use I2SDK Keys as Autologin Keys for security reasons' . m4is_h65::m4is_o64(363 ));
 
}else{
$m4is_e0213['autologin_authkeys']=$_POST['autologin_authkeys'];
 
}unset($m4is_l9671, $m4is_c198, $m4is_b37, $m4is_g62, $m4is_t16867 );
 m4is_h65::m4is_z896('HTTP POST Options Updated' );
 
}
}global $wp_rewrite;
 $wp_rewrite->flush_rules();
 
}$m4is_k0941 =m4is_s6729::m4is_c26()->m4is_s6613();
   $m4is_l9321 =m4is_k865::m4is_z2906(true );
 $m4is_l9321 =$m4is_l9321['mc'];
 $m4is_z470 =[];
 $m4is_z470[]=['id' =>0, 'text' =>'(None)' ];
 foreach ((array)$m4is_l9321 as $m4is_d07693 =>$m4is_p786 ){
$m4is_z470[]=['id' =>$m4is_d07693, 'text' =>$m4is_p786 . ' (' . $m4is_d07693 . ')' ];
 
}$m4is_z470 =json_encode($m4is_z470 );
 unset($m4is_l9321, $m4is_d07693, $m4is_p786 );
 echo '<script>';
 echo 'var pagelist      = ', $m4is_k0941, ';';
 echo 'var actionsetlist = ', m4is_j4156::m4is_s6612(), ';';
 echo 'var taglist       = ', $m4is_z470, ';';
 echo '</script>';
 unset($m4is_z470, $m4is_k0941, $json_actionsets );

