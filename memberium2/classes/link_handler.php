<?php

/**
 * Copyright (c) 2015-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
   final 
class m4is_n2918 {
private static $m4is_r1546;
 private static $m4is_i935;
 private static $m4is_i8169;
 private static $m4is_y36;
 private static $m4is_o0379;
 private static $m4is_y50;
 private static $m4is_f56;
 private static $m4is_m60;
    public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_i8169 =self::m4is_m960();
 self::$m4is_y36 =isset($_GET['fail_url'])? $_GET['fail_url']: site_url('/');
 self::$m4is_y50 =[];
 self::$m4is_f56 =site_url('/' );
 self::$m4is_m60 =true;
 self::$m4is_o0379 =0;
 
}public static 
function m4is_m69(): void {
global $wpdb;
 if(self::$m4is_i8169 ){
printf("%d :: Debug Mode Enabled<br>", __LINE__ );
 ini_set('display_errors', 1 );
 
}else{
ini_set('display_errors', 0 );
 
}$m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field' );
 $m4is_r01667 =self::$m4is_r1546->m4is_j498('settings', 'default_reglink_tag' );
 $m4is_c05328 =isset($_GET['action'])? strtolower(trim($_GET['action'])): '';
 $m4is_h21895 =isset($_GET['cid'])? (int) trim($_GET['cid']): 0;
 $m4is_f4930 =isset($_GET['email'])? strtolower(trim($_GET['email'])): '';
 $m4is_r69245 =isset($_GET['pid'])? strtolower(trim($_GET['pid'])): '';
 $m4is_f56 =isset($_GET['redir'])? $_GET['redir']: site_url('/');
 $m4is_f56 =isset($_GET['redirect'])? $_GET['redirect']: $m4is_f56;
 self::$m4is_o0379 =m4is_q62395::m4is_v729($m4is_h21895, 'autologin', sprintf('Confirmation Link for user %s', $m4is_f4930 ));
 m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Redirecting to ' . $m4is_f56 );
 self::m4is_u8945();
 if(self::$m4is_i8169 ){
printf('%d - Email = %s<br>', __LINE__, $m4is_f4930 );
 printf('%d - Contact ID = %d<br>', __LINE__, $m4is_h21895 );
 printf('%d - Profile ID = %d<br>', __LINE__, $m4is_r69245 );
 
}if(empty($m4is_r6234 )){
$m4is_l073 =self::$m4is_i8169 ? 'Password field not defined.' : 'Memberium';
 if(self::$m4is_i8169 ){
printf('%d :: %s<br />', __LINE__, $m4is_l073 );
 
}else{
wp_safe_redirect(self::$m4is_y36, 302, 'Memberium' );
 
}die();
 
}self::$m4is_y50 =self::m4is_f801($m4is_r69245 );
 self::m4is_r17894($m4is_f4930 );
 self::m4is_j965();
 self::m4is_e16290($m4is_h21895, $m4is_f4930 );
 self::m4is_c46($m4is_h21895, $m4is_f4930 );
 $m4is_f087 =self::m4is_g31($m4is_h21895 );
 if(empty($m4is_f087 )){
if(self::$m4is_i8169 ){
printf('%d :: %s<br />', __LINE__, 'Failed to create user.' );
 printf('%d :: Redirect to %s<br>', __LINE__, self::$m4is_y36 );
 
}else{
wp_redirect(self::$m4is_y36 );
 
}exit;
 
}self::$m4is_r1546->m4is_k98(self::$m4is_y50['tag'], $m4is_h21895);
  self::m4is_h617($m4is_f087 );
 if(self::$m4is_i8169 ){
echo __LINE__, ' Success Redirect<br>';
 echo __LINE__, ' :: Redirect to ', $m4is_f56, '<br>';
 printf('<p><a href="%s">Click here to continue as the user</a></p>', $m4is_f56 );
 die();
 
}wp_redirect($m4is_f56 );
 exit;
 
}    static 
function m4is_m960(): bool {
if(!empty($_GET['debug'])){
return m4is_a01587::m4is_j301();
 
}return false;
 
} private static 
function m4is_u8945(): void {
$m4is_o9460 =array_filter(explode(',', self::$m4is_r1546->m4is_j498('settings', 'autologin_authkeys' )));
 $m4is_o86 =isset($_GET['authkey'])? trim($_GET['authkey']): '';
 if(!in_array($m4is_o86, $m4is_o9460 )){
m4is_q62395::m4is_x6835(self::$m4is_o0379, 'Invalid Auth Key ' . $_GET['authkey']);
 if(self::$m4is_i8169 ){
echo __LINE__, ' :: Auth Key = ', $m4is_o86, '<br>';
 echo __LINE__, ' :: Invalid Authentication Key<br>';
 
}else{
wp_safe_redirect(self::$m4is_y36, 302, 'Memberium' );
 
}die();
 
}else{
if(self::$m4is_i8169 ){
echo __LINE__, ' :: Auth Key Validated<br>';
 
}
}
}private static 
function m4is_f801($m4is_r69245 ){
 $m4is_y50 =[];
 $m4is_y50['tag']=self::$m4is_r1546->m4is_j498('settings', 'default_reglink_tag' , '' );
 $m4is_y50['goal']='';
 $m4is_y50['login_post_id']=0;
 if(!empty($m4is_r69245 )){
$m4is_m63284 =get_option('memberium_registration_profiles' );
 $m4is_m63284[$m4is_r69245]=['goal' =>'', 'login_post_id' =>0, 'tag' =>'', ];
 if(isset($m4is_m63284[$m4is_r69245])&&is_array($m4is_m63284[$m4is_r69245])){
$m4is_y50 =$m4is_m63284[$m4is_r69245];
 
}else{
if(self::$m4is_i8169 ){
echo __LINE__, ' :: Invalid Profile Id<br>';
 die();
 
}else{
wp_safe_redirect(self::$m4is_y36, 302, 'Memberium' );
 
}self::$m4is_m60 =false;
 
}
}if(self::$m4is_i8169 ){
echo __LINE__, ' :: Profile Goal = ', $m4is_y50['goal'], '<br>';
 echo __LINE__, ' :: Profile Tag = ', $m4is_y50['tag'], '<br>';
 echo __LINE__, ' :: Login Post ID = ', $m4is_y50['login_post_id'], '<br>';
 
}return $m4is_y50;
 
} private static 
function m4is_r17894(string $m4is_f4930 ){
$m4is_f087 =get_user_by('email', $m4is_f4930 );
 if(!$m4is_f087 ){
return;
 
}$m4is_l073 =self::$m4is_i8169 ? 'Confirmation Link: User already exists.' : 'Memberium';
 m4is_q62395::m4is_x6835(self::$m4is_o0379, $m4is_l073 );
 if(self::$m4is_i8169 ){
printf('%d :: User with email address already "%s" Exists<br />', __LINE__, $m4is_f4930 );
 printf('%d :: Redirect to %s<br>', __LINE__, self::$m4is_y36 );
 
}else{
wp_safe_redirect(self::$m4is_y36, 302, $m4is_l073 );
 
}die();
 
} private static 
function m4is_j965(){
if(!is_user_logged_in()){
return;
 
}$m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_w280 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'login_page', 0 );
 $m4is_f56 =$m4is_w280 ? get_permalink($m4is_w280 ): self::$m4is_f56;
 $m4is_l073 =self::$m4is_i8169 ? 'Confirmation Link: User Logged In' : 'Memberium';
 m4is_q62395::m4is_x6835(self::$m4is_o0379, $m4is_l073 );
 if(self::$m4is_i8169 ){
echo __LINE__, ' :: User Logged In<br>';
 echo __LINE__, ' :: Redirecting to ', $m4is_f56, '<br>';
 echo '<pre>Session = ', print_r(m4is_q82::m4is_d59($m4is_f087 ), true), '</pre>';
 
}else{
wp_safe_redirect($m4is_f56, 302, $m4is_l073 );
 
}exit;
 
} private static 
function m4is_e16290(int $m4is_h21895, string $m4is_f4930 ){
self::$m4is_r1546->m4is_x4831($m4is_h21895, false );
 self::$m4is_i935 =m4is_p40::m4is_p67($m4is_h21895, false );
 $m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field' );
 if(empty($m4is_r6234 )){
if(self::$m4is_i8169 ){
echo __LINE__, ' :: Password Field Not Defined<br>';
 
}else{
wp_safe_redirect(self::$m4is_y36, 302, 'Memberium' );
 
}die();
 
} if(empty(self::$m4is_i935 )||!is_array(self::$m4is_i935 )){
$m4is_l073 =self::$m4is_i8169 ? 'Confirmation Link: Missing contact data' : 'Memberium';
 m4is_q62395::m4is_x6835(self::$m4is_o0379, $m4is_l073 );
 if(self::$m4is_i8169 ){
printf('%d :: %s<br>', __LINE__, $m4is_l073 );
 
}else{
wp_safe_redirect(self::$m4is_y36, 302, $m4is_l073 );
 
}exit;
 
} if($m4is_h21895 <> self::$m4is_i935['Id']){
$m4is_l073 =self::$m4is_i8169 ? 'Confirmation Link: Contact ID mismatch' : 'Memberium';
 m4is_q62395::m4is_x6835(self::$m4is_o0379, $m4is_l073 );
 if(self::$m4is_i8169 ){
printf('%d :: %s<br>', __LINE__, $m4is_l073 );
 
}else{
wp_safe_redirect(self::$m4is_y36, 302, $m4is_l073 );
 
}exit;
 
} if($m4is_f4930 <> self::$m4is_i935['Email']){
$m4is_l073 =self::$m4is_i8169 ? 'Confirmation Link: Email address mismatch' : 'Memberium';
 m4is_q62395::m4is_x6835(self::$m4is_o0379, $m4is_l073 );
 if(self::$m4is_i8169 ){
printf('%d :: %s<br>', __LINE__, $m4is_l073 );
 
}else{
wp_safe_redirect(self::$m4is_y36, 302, $m4is_l073 );
 
}exit;
 
} if(empty(self::$m4is_i935['FirstName'])){
$m4is_l073 =self::$m4is_i8169 ? 'Confirmation Link: First Name missing' : 'Memberium';
 m4is_q62395::m4is_x6835(self::$m4is_o0379, $m4is_l073 );
 if(self::$m4is_i8169 ){
printf('%d :: %s<br>', __LINE__, $m4is_l073 );
 
}else{
wp_safe_redirect(self::$m4is_y36, 302, $m4is_l073 );
 
}exit;
 
}
} private static 
function m4is_c46(int $m4is_h21895, string $m4is_f4930 ){
$m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field' );
 $m4is_m676 =isset(self::$m4is_i935[$m4is_r6234])? trim(self::$m4is_i935[$m4is_r6234]): '';
 if(empty($m4is_m676 )){
self::$m4is_i935[$m4is_r6234]=self::$m4is_r1546->m4is_a601();
 $m4is_e32607 =[$m4is_r6234 =>self::$m4is_i935[$m4is_r6234], ];
 $m4is_u6591 =m4is_p40::m4is_x6560($m4is_h21895, $m4is_e32607 );
 self::$m4is_r1546->m4is_v1694(self::$m4is_i935 );
 if(self::$m4is_i8169 ){
printf("%d :: Password '%s' generated and saved.<br />", __LINE__, self::$m4is_i935[$m4is_r6234]);
 
}
}else{
if(self::$m4is_i8169 )echo __LINE__, ' Password Already Exists<br>';
 
}
} private static 
function m4is_g31(int $m4is_h21895 ): int {
$m4is_f087 =self::$m4is_r1546->m4is_f605($m4is_h21895 );
 $m4is_a173 =sprintf('Created User ID %d', $m4is_f087 );
 m4is_q62395::m4is_x6835(self::$m4is_o0379, $m4is_a173 );
 if(self::$m4is_i8169 ){
printf('%d :: %s<br />', __LINE__, $m4is_a173 );
 
}return (int) $m4is_f087;
 
} private static 
function m4is_h617(int $m4is_f087 ): void {
$m4is_l17096 =get_userdata($m4is_f087 );
 $m4is_v64 =$m4is_l17096->user_login;
 wp_set_auth_cookie($m4is_f087);
 wp_set_current_user($m4is_f087 );
 m4is_l5841::m4is_e40($m4is_v64 );
 do_action('wp_login', $m4is_v64, $m4is_l17096 );
 m4is_q82::m4is_d59($m4is_f087);
 session_write_close();
 
} 
}

