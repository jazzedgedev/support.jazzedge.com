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
class m4is_l5841 {
static $m4is_q735 =false;
 private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_n71;
 private static $m4is_w0428;
 private static $m4is_f683;
 private static $m4is_f72;
 private static $m4is_r2369;
 private static $m4is_b1723;
 private static $m4is_n723;
 private static $m4is_z30578;
 private static $m4is_g1520;
 private static $m4is_f7298;
 private static $m4is_r6234;
 private static $m4is_x506;
 private static $m4is_u87692;
 private static $m4is_y296;
 static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_f683 =m4is_f58::m4is_c26();
 self::$m4is_y296 =m4is_s52::m4is_f27();
 self::$m4is_r9613 =(string) self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_w0428 =(bool) self::$m4is_r1546->m4is_j498('settings', 'disable_login_sync', 0 );
 self::$m4is_f72 =(bool) self::$m4is_r1546->m4is_j498('settings', 'known_logins_only', 0 );
 self::$m4is_r2369 =(bool) self::$m4is_r1546->m4is_j498('settings', 'local_auth_only', 0 );
 self::$m4is_n723 =(bool) self::$m4is_r1546->m4is_j498('settings', 'login_log', 0 );
 self::$m4is_u87692 =(bool) self::$m4is_r1546->m4is_j498('settings', 'sync_ecommerce', 0 );
 self::$m4is_x506 =(bool) self::$m4is_r1546->m4is_j498('settings', 'sync_affiliate', 0 );
 self::$m4is_n71 =(int) self::$m4is_r1546->m4is_j498('settings', 'bruteforce_check', 0 );
 self::$m4is_g1520 =(int) self::$m4is_r1546->m4is_j498('settings', 'maximum_login_ips', '' );
 self::$m4is_f7298 =(int) self::$m4is_r1546->m4is_j498('settings', 'maximum_login_timeframe', 0 );
 self::$m4is_z30578 =(int) self::$m4is_r1546->m4is_j498('settings', 'max_contact_age' );
 self::$m4is_r6234 =(string) self::$m4is_r1546->m4is_j498('settings', 'password_field', 'Password' );
 self::$m4is_b1723 ='memberium/login_count';
 m4is_j586::m4is_k751();
 
}private 
function __construct(){

}    static 
function m4is_h37(): string {
return defined('MEMBERIUM_DB_LOGINLOG' )? constant ('MEMBERIUM_DB_LOGINLOG' ): '';
 
}static 
function m4is_l68271(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_h37();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(11) NOT NULL AUTO_INCREMENT, \n" . "appname varchar(32) NOT NULL, \n" . "logintime int(11) NOT NULL, \n" . "ipaddress varchar(45) NOT NULL, \n" . "username varchar(64) NOT NULL, \n" . "KEY logintime (logintime), \n" . "KEY username (username), \n" . "PRIMARY KEY  (id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}   public static 
function m4is_l580(){
if(isset($_COOKIE['memberium_autologin_session'])){
setcookie('memberium_autologin_session', 0, 1, COOKIEPATH, COOKIE_DOMAIN, false, true );
 
}$m4is_h21895 =self::$m4is_r1546->m4is_z56();
 self::$m4is_r1546->m4is_l53('logout/contact_id', $m4is_h21895 );
 
}    public static 
function m4is_e3216(): int {
$m4is_z984 ='memberium/logs/logins/count';
 $m4is_q436 =MINUTE_IN_SECONDS;
 $m4is_m615 =get_transient($m4is_z984 );
 if($m4is_m615 === false ||!is_numeric($m4is_m615 )){
global $wpdb;
 $m4is_v2613 ="SELECT count(*)  FROM %i WHERE `appname` = %s ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_h37(), self::$m4is_r9613 );
 $m4is_m615 =$wpdb->get_var($m4is_v2613 );
 if($m4is_m615 > 100 ){
set_transient($m4is_z984, $m4is_m615, $m4is_q436 );
 
}
}return (int) $m4is_m615;
 
} public static 
function m4is_j3746(): void {
$m4is_y67 =(int) self::$m4is_r1546->m4is_j498('settings', 'login_log_length', 30 );
 if($m4is_y67 < 1 ){
return;
 
}global $wpdb;
 $m4is_e80 =self::m4is_h37();
 $m4is_r6208 =time()- ($m4is_y67 * DAY_IN_SECONDS );
 $m4is_v2613 ="DELETE FROM `{$m4is_e80
}` WHERE `logintime` < {$m4is_r6208
}";
 $m4is_u6591 =(int) $wpdb->query($m4is_v2613 );
 delete_transient(self::$m4is_b1723 );
 if($m4is_u6591 ){
$m4is_a173 =sprintf('Login Log trimmed to last %d day(s).  %d rows deleted.', $m4is_y67, $m4is_u6591 );
 m4is_q62395::m4is_v729(0, 'cron', $m4is_a173 );
 
}
} static 
function m4is_t713($m4is_l17096, $m4is_y193 ='', $m4is_m676 ='' ){
if($_SERVER['REQUEST_METHOD']<> 'POST' ){
return $m4is_l17096;
 
}if(empty($m4is_y193 )){
return $m4is_l17096;
 
}if(defined('MEMBERIUM_DISABLE_BOT_CHECK' )&&constant('MEMBERIUM_DISABLE_BOT_CHECK' )){
return $m4is_l17096;
 
}if(!self::$m4is_n71 ){
return $m4is_l17096;
 
}$m4is_n71 =isset($_POST['bruteforcecheck'])? $_POST['bruteforcecheck']: '';
 if($m4is_n71 == self::m4is_t8514()){
return $m4is_l17096;
 
}self::$m4is_q735 =true;
 $m4is_a173 =self::$m4is_r1546->m4is_p6406('i18n/loginfailed' );
 $m4is_f365 =self::$m4is_r1546->m4is_p6406('i18n/contactmemberium' );
 $m4is_l17096 =new WP_Error('authentication_failed', $m4is_a173 );
 $m4is_k698 =defined('MEMBERIUM_SKU' )? constant('MEMBERIUM_SKU' ): '';
 http_response_code(403 );
 header('HTTP/1.1 403 Forbidden' );
 header('Status: 403 Forbidden' );
 echo '<p>', strtoupper($m4is_k698 ), ':', __LINE__, ' Forbidden</p>';
 echo '<p>', $m4is_f365, '</p>';
 exit;
 
} static 
function m4is_u06($m4is_t09761 ='', array $m4is_y66291 =[]): string {
$m4is_v8646 =self::m4is_t8514();
 if(!empty($m4is_v8646 )){
$m4is_o498 ="<input name='bruteforcecheck' type='hidden' value='" . self::m4is_t8514()."' />";
 if(!empty($m4is_y66291['display'])||current_action()== 'login_form'){
echo $m4is_o498;
 return '';
 
}
}else{
$m4is_o498 ='';
 
}return "{$m4is_t09761
}{$m4is_o498
}";
 
}    private static 
function m4is_t8514(): string {
$m4is_v8646 =defined('MEMBERIUM_BRUTEFORCE_TOKEN' )? constant('MEMBERIUM_BRUTEFORCE_TOKEN' ): '';
 $m4is_r12 =wp_salt('secure_auth' );
 if(self::$m4is_n71 == 1 ){
$m4is_v8646 =crc32($_SERVER['DOCUMENT_ROOT']. $m4is_r12 );
 
}elseif(self::$m4is_n71 == 2 ){
$m4is_v8646 =crc32(m4is_a01587::m4is_y342(). $m4is_r12 );
 
}return $m4is_v8646;
 
}    static 
function m4is_m397($m4is_l17096, string $m4is_y193 ='', string $m4is_m676 ='' ){
 if(empty($m4is_y193 )||empty($m4is_m676 )){
return $m4is_l17096;
 
}if(!self::$m4is_y296 ){
return $m4is_l17096;
 
}if(self::$m4is_w0428 ||self::$m4is_f72 ||self::$m4is_r2369 ){
return $m4is_l17096;
 
} $m4is_q03616 =get_user_by('email', $m4is_y193 )or $m4is_q03616 =get_user_by('login', $m4is_y193 );
 if(is_a($m4is_q03616, 'WP_User' )){
return $m4is_l17096;
 
}$m4is_h21895 =self::$m4is_r1546->m4is_n871($m4is_y193, $m4is_m676 );
 $m4is_d02473 =self::$m4is_r1546->m4is_f605($m4is_h21895 );
 $m4is_l17096 =get_user_by('ID', $m4is_d02473 );
 m4is_q82::m4is_u687($m4is_d02473 );
 return $m4is_l17096;
 
} static 
function m4is_i56340($m4is_l17096, string $m4is_y193 ='', string $m4is_m676 ='' ){
if(empty($m4is_l17096 )&&empty($m4is_y193 )&&empty($m4is_m676 )){
return $m4is_l17096;
 
}if(self::$m4is_w0428 ){
return $m4is_l17096;
 
}if(!self::$m4is_y296 ){
return $m4is_l17096;
 
}if(user_can($m4is_l17096, 'manage_options' )){
return $m4is_l17096;
 
}$m4is_y01 =get_user_by('email', $m4is_y193 )or $m4is_y01 =get_user_by('login', $m4is_y193 );
 if(!is_a($m4is_y01, 'WP_User' )){
return $m4is_l17096;
 
}if(user_can($m4is_y01, 'manage_options' )){
return $m4is_l17096;
 
}self::$m4is_r1546->m4is_s07281($m4is_y01->user_email );
  return $m4is_l17096;
 
} static 
function m4is_c58692($m4is_l17096, $m4is_y193 ='', $m4is_m676 ='' ){
if(self::$m4is_r2369 ||self::$m4is_w0428){
return $m4is_l17096;
 
}$m4is_y01 =get_user_by('email', $m4is_y193 )or $m4is_y01 =get_user_by('login', $m4is_y193 );
 if(!$m4is_y01 ){
return $m4is_l17096;
 
}$m4is_f4930 =$m4is_y01->user_email;
 $m4is_v30712 =m4is_p40::m4is_i68($m4is_f4930 );
 $m4is_h21895 =is_array($m4is_v30712 )? $m4is_v30712[0]: $m4is_v30712;
 if(is_array($m4is_v30712 )){
$m4is_h21895 =isset($m4is_v30712[0])? $m4is_v30712[0]: 0;
 
}if($m4is_h21895 ){
$m4is_i935 =m4is_p40::m4is_p67($m4is_h21895 );
 $m4is_z69301 =$m4is_i935[self::$m4is_r6234 ]?? '';
 $m4is_m676 =empty(trim($m4is_m676 ));
 if($m4is_z69301 !== 'PASSWORD_PLACEHOLDER' ){
if($m4is_z69301 !== $m4is_m676 ||empty($m4is_z69301 )){
$m4is_l17096 =new WP_Error ('authentication_failed', __('<strong>ERROR</strong>: Incorrect Password.', 'memberium' ));
 
}else{
$m4is_l17096 =$m4is_y01;
 
}
}
}return $m4is_l17096;
 
}static 
function m4is_l8061($m4is_l17096, $m4is_y193 ='', $m4is_m676 ='' ){
if(!is_a($m4is_l17096, 'WP_User' )){
return $m4is_l17096;
 
}if(user_can($m4is_l17096, 'manage_options' )){
return $m4is_l17096;
 
}if(!self::$m4is_y296 ){
$m4is_o06173 =m4is_q62395::m4is_v729(self::$m4is_r1546->m4is_z56(), 'loginfail', 'Logins Disabled - Invalid License', m4is_a01587::m4is_y342(), true );
 $m4is_l17096 =new WP_Error('authentication_failed', __('<center><strong>All Logins Temporarily Disabled for Maintenance Mode</strong></center>', 'memberium' ));
 
}return $m4is_l17096;
 
}static 
function m4is_w9147(&$m4is_y193 ){
if(empty($m4is_y193 )){
return $m4is_y193;
 
}$m4is_l17096 =get_user_by('email', $m4is_y193 );
 if(!empty($m4is_l17096->user_login )){
$m4is_y193 =$m4is_l17096->user_login;
 
} return $m4is_y193;
 
}static 
function m4is_x62($m4is_l17096, $m4is_y66291 =[]){
global $wpdb;
 $m4is_f087 =is_a('WP_User', $m4is_l17096 )? $m4is_l17096->ID : 0;
 if(is_a('WP_Error', $m4is_l17096 )){
return $m4is_l17096;
 
}if(!self::$m4is_n723 ){
return $m4is_l17096;
 
}if(is_a($m4is_l17096, 'WP_User' )){
$m4is_y193 =$m4is_l17096->user_login;
 $m4is_f087 =$m4is_l17096->ID;
 
}elseif(is_string($m4is_l17096 )){
$m4is_y193 =$m4is_l17096;
 
}if(apply_filters('memberium/loginlog/bypass', false, $m4is_f087 )){
return $m4is_l17096;
 
}$m4is_y642 =['appname' =>self::$m4is_r9613, 'time' =>time(), 'ipaddress' =>m4is_a01587::m4is_y342(), ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642 );
 $m4is_m7426 =self::m4is_h37();
 $m4is_v2613 ="INSERT INTO `{$m4is_m7426
}` (`appname`, `logintime`, `ipaddress`, `username`) VALUES (%s, %d, %s, %s)";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_r9613, $m4is_y66291['time'], $m4is_y66291['ipaddress'], $m4is_y193 );
 $wpdb->query($m4is_v2613);
 delete_transient(self::$m4is_b1723 );
 return $m4is_l17096;
 
}static 
function m4is_w47986($m4is_c93, $m4is_c035, $m4is_l17096 ){
if(is_a($m4is_l17096, 'WP_Error' )){
return $m4is_c93;
  if(empty($m4is_c035 )){
$m4is_c93 =get_admin_url();
 
}return $m4is_c93;
 
}if(!empty($m4is_c035 )){
$m4is_c93 =$m4is_c035;
 
}if(!is_a($m4is_l17096, 'WP_User' )){
return $m4is_c93;
 
}if(empty($_REQUEST['redirect_to'])&&empty($m4is_c035 )){
if(user_can($m4is_l17096, 'manage_options' )){
$m4is_c93 =admin_url();
 
}$m4is_v27639 =m4is_q82::m4is_d6758($m4is_l17096->ID, 'memb_user', 'login_page', 0 );
 if($m4is_v27639 == -1 ){
if(function_exists('bbp_get_user_profile_url' )){
$m4is_c93 =bbp_get_user_profile_url($m4is_l17096->ID );
 
}$m4is_c93 =get_permalink($m4is_v27639 );
 
}elseif($m4is_v27639 ){
$m4is_c93 =get_permalink($m4is_v27639 );
 
}
}if(empty($m4is_c93 )){
$m4is_c93 =admin_url();
 
}return $m4is_c93;
 
} public static 
function m4is_e40(&$m4is_y193 ): void {
global $wpdb;
 $m4is_a09532 =0;
 if(empty($m4is_y193 )){
return;
 
} $m4is_l0649 =get_user_by('login', $m4is_y193);
 if(is_a($m4is_l0649, 'WP_User' )&&(!empty($m4is_l0649->caps['administrator']))||(!empty($m4is_l0649->caps['super_admin']))){
return;
 
}if(!self::$m4is_y296 ){
return;
 
}$m4is_m676 ='';
 if(!headers_sent()&&session_status()!== PHP_SESSION_ACTIVE){
session_start();
 
}if(isset($_POST['pwd'])){
$m4is_m676 =$_POST['pwd'];
 
}if(isset($_POST['password'])){
$m4is_m676 =$_POST['password'];
 
}if(empty($m4is_m676)){
return;
 
}$m4is_f72 =self::$m4is_f72;
 $m4is_g6630 =self::$m4is_f683->m4is_h03978($m4is_y193 );
 $m4is_l0649 =get_user_by('email', $m4is_g6630)or $m4is_l0649 =get_user_by('login', $m4is_g6630 );
 $m4is_f4930 =is_a($m4is_l0649, 'WP_User' )? $m4is_l0649->user_email : $m4is_g6630;
 $m4is_h21895 =self::$m4is_r1546->m4is_n871($m4is_f4930, $m4is_m676 );
 if($m4is_f72 == false &&self::$m4is_w0428 == false ){
self::$m4is_r1546->m4is_s07281($m4is_f4930 );
 
}if($m4is_h21895 > 0 ){
$m4is_l0649 =get_user_by('ID', m4is_p40::m4is_i6158($m4is_h21895 ));
 if($m4is_l0649 ){
if(!username_exists($m4is_g6630 )&&!email_exists($m4is_g6630 )){
if($m4is_l0649->user_email <> $m4is_g6630){
if($m4is_l0649->user_email == $m4is_l0649->user_login){
$m4is_v2613 ="UPDATE `{$wpdb->users
}` SET `user_email` = %s, `user_login` = %s WHERE `ID` = %d; ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, strtolower($m4is_g6630), strtolower($m4is_g6630), $m4is_l0649->ID);
 
}else{
$m4is_v2613 ="UPDATE `{$wpdb->users
}` SET `user_email` = %s WHERE `ID` = %d; ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, strtolower($m4is_g6630), $m4is_l0649->ID);
 
}$wpdb->query($m4is_v2613);
 
}
}
}
}$m4is_l0649 =get_user_by('login', $m4is_y193);
 if(!$m4is_l0649){
$m4is_l0649 =get_user_by('email', $m4is_y193);
 
}if(!$m4is_h21895 ){
global $user;
 $user =new WP_Error('authentication_failed', __('ERROR: Unknown Account.', 'memberium'));
 return;
 
}self::$m4is_z30578 =self::$m4is_z30578 == 0 ? 5 : self::$m4is_z30578;
 $m4is_v2613 ='SELECT `value` FROM `' . m4is_p40::m4is_o1723(). '` WHERE `id` = %d AND `appname` =  %s AND `fieldname` = "!LastUpdated" ';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_h21895, self::$m4is_r9613 );
 $m4is_i3206 =$wpdb->get_col($m4is_v2613);
 $m4is_y01 =get_user_by('login', $m4is_y193 );
 if(!$m4is_y01 ){
$m4is_y01 =get_user_by('email', $m4is_y193 );
 
}if(self::$m4is_w0428 == false ){
if($m4is_i3206 < (time()- self::$m4is_z30578 )){
self::$m4is_r1546->m4is_x4831($m4is_h21895);
 
}
}self::$m4is_r1546->m4is_f605($m4is_h21895 );
 $m4is_r6234 =self::$m4is_r6234;
 $m4is_i935 =m4is_p40::m4is_p67($m4is_h21895 );
 if(stripslashes($m4is_m676 )!= $m4is_i935[$m4is_r6234]){
$user =new WP_Error('authentication_failed', __('<strong>ERROR</strong>: Incorrect Password.', 'memberium'));
 return;
 
}if(self::$m4is_w0428 == false ){
if(self::$m4is_x506 ){
self::$m4is_r1546->m4is_x17402($m4is_h21895);
 
}if(self::$m4is_u87692 ){
m4is_c01675::m4is_a20694($m4is_h21895 );
 self::$m4is_r1546->m4is_c686($m4is_h21895, true );
 
}
}if($m4is_y01){
$user =$m4is_y01;
 $_POST['username']=$user->user_login;
 $m4is_y193 =$user->user_login;
 wp_set_current_user($user->ID);
 m4is_q82::m4is_u687($m4is_l0649->ID );
 
}self::$m4is_r1546->m4is_f7064(true );
 if($m4is_a09532){
$user =wp_set_current_user($m4is_a09532);
 
}$m4is_l9321 =isset($m4is_i935['Groups'])? explode(',', $m4is_i935['Groups']): [];
 self::$m4is_r1546->m4is_f7064(false );
 
}static 
function m4is_q4626($m4is_v64, $m4is_l17096 =NULL){
if(isset($_POST['firstname'])||isset($_POST['lastname'])){
if(isset($_POST['firstname'])){
$m4is_p80796 =$_POST['firstname'];
 
}if(isset($_POST['lastname'])){
$m4is_r27 =$_POST['lastname'];
 
}
}else{
$m4is_p80796 =$m4is_l17096->first_name;
 $m4is_r27 =$m4is_l17096->last_name;
 if(strpos($m4is_l17096->display_name, ' ')> 0){
$m4is_p80796 =trim(substr($m4is_l17096->display_name, 0, strpos($m4is_l17096->display_name, ' ')));
 $m4is_r27 =trim(substr($m4is_l17096->display_name, strpos($m4is_l17096->display_name, ' ')+ 1));
 
}
}$m4is_h391 =trim($m4is_p80796 . ' ' . $m4is_r27);
 $m4is_f087 =$m4is_l17096->ID;
 $m4is_s680 =0;
 $m4is_i935 =['FirstName' =>$m4is_p80796, 'LastName' =>$m4is_r27, 'Email' =>$m4is_l17096->user_email, ];
 if($m4is_s680 > 0){
$m4is_i935['LeadSourceId']=$m4is_s680;
 
}if(get_user_meta($m4is_l17096->ID, '_fb_is_sync', true)!= 1){
if(self::$m4is_f683->m4is_e9466()){
$m4is_h21895 =m4is_p40::m4is_k82670($m4is_i935);
 m4is_p40::m4is_y935($m4is_i935['Email'], 'Membership Site Registration');
 $m4is_r09164 =self::$m4is_r1546->m4is_j498('settings', 'new_user_registration_tag');
 if($m4is_r09164){
self::$m4is_r1546->m4is_k98([$m4is_r09164], $m4is_h21895);
 
}delete_user_meta($m4is_l17096->ID, '_fb_is_sync');
 add_user_meta($m4is_l17096->ID, '_fb_is_sync', 1);
 
}
} $m4is_l17096 =wp_get_current_user();
 if(!is_a($m4is_l17096, 'WP_User' )){
return $m4is_l17096;
 
} 
}static 
function m4is_n6012($m4is_v64, $m4is_l17096 =NULL ){
if($m4is_l17096 == NULL ){
$m4is_l17096 =wp_get_current_user();
 
} if(user_can($m4is_l17096, 'manage_options')){
return;
 
}if(self::$m4is_r1546->m4is_j498('settings', 'simultaneous_logins', false)){
self::$m4is_r1546->m4is_i6163($m4is_l17096->ID);
 
}$_POST['redirect_to']=empty($_POST['redirect_to'])? '' : $_POST['redirect_to'];
 $_POST['redirect_to']=empty($_POST['redirect'])? $_POST['redirect_to']: $_POST['redirect'];
  if(0 == self::$m4is_r1546->m4is_j498('settings', 'allow_wpadmin' )){
if(stripos($_POST['redirect_to'], '/wp-admin')!== false){
$_POST['redirect_to']='';
 
}
}$m4is_f087 =(int) $m4is_l17096->ID;
 $m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087);
 if($m4is_f087 ){
 m4is_c01675::m4is_j6029($m4is_h21895);
  $m4is_k9075 =(int) get_user_meta($m4is_f087, 'login_count', true)+ 1;
 $m4is_u6591 =update_user_meta($m4is_f087, 'login_count', $m4is_k9075);
 $m4is_u6591 =update_user_meta($m4is_f087, 'login_ip_address', m4is_a01587::m4is_y342());
 $m4is_u6591 =update_user_meta($m4is_f087, 'last_login_time', time());
 if($m4is_h21895){
$m4is_i935 =m4is_p40::m4is_p67($m4is_h21895);
  self::$m4is_f683->m4is_g38965($m4is_i935, $m4is_f087);
 if(self::$m4is_w0428 == false ){
$m4is_w2956 =(int) self::$m4is_r1546->m4is_j498('settings', 'login_actionset' );
 $m4is_d2317 =(int) self::$m4is_r1546->m4is_j498('settings', 'login_tag' );
 $m4is_t21630 =trim(self::$m4is_r1546->m4is_j498('settings', 'login_goal' ));
 $m4is_k825 =self::$m4is_r1546->m4is_j498('settings', 'last_login_field' );
 $m4is_v217 =self::$m4is_r1546->m4is_j498('settings', 'sync_affiliate' );
 if(!empty($m4is_t21630)){
self::$m4is_r1546->m4is_z3902($m4is_h21895, $m4is_t21630 );
 
} if($m4is_w2956 ){
self::$m4is_r1546->m4is_u71903($m4is_w2956, (int) $m4is_h21895 );
 
} if($m4is_d2317 ){
self::$m4is_r1546->m4is_k98($m4is_d2317, $m4is_h21895 );
 
}if(!empty($m4is_v217 )){
self::$m4is_r1546->m4is_x17402($m4is_h21895 );
 
} $m4is_a89 =[];
 if(!empty($m4is_k825)){
$m4is_d38091 =date_default_timezone_get();
 date_default_timezone_set('America/New_York');
 $m4is_a89[$m4is_k825]=date('Y-m-d\TH:i:s');
 date_default_timezone_set($m4is_d38091);
 
}if(!empty($m4is_a89)){
m4is_p40::m4is_x6560($m4is_h21895, $m4is_a89);
  
}
}
}
}if(defined('DOING_AJAX' )&&DOING_AJAX ){
self::$m4is_f683->m4is_t25(false);
 
}
}    static 
function m4is_v61($m4is_y193, $m4is_m676 ='', $m4is_r963 =false){
global $wpdb;
 if(is_user_logged_in()){
return false;
 
}$m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field');
 $m4is_f72 =self::$m4is_r1546->m4is_j498('settings', 'known_logins_only');
 $m4is_h21895 =self::$m4is_r1546->m4is_n871($m4is_y193, $m4is_m676 );
  if($m4is_f72 ){
$m4is_v2613 ='SELECT id FROM ' . m4is_p40::m4is_o1723(). ' WHERE id = %d AND appname = %s LIMIT 1;';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_h21895, self::$m4is_r9613 );
 $m4is_t7826 =(int) $wpdb->get_var($m4is_v2613);
 if($m4is_t7826 <> $m4is_h21895 ){
return false;
 
}
}if($m4is_h21895 ){
$m4is_i935 =m4is_p40::m4is_p67($m4is_h21895 );
 self::$m4is_r1546->m4is_f605($m4is_h21895 );
 
} if(self::$m4is_r1546->m4is_z56()== $m4is_h21895){
if($m4is_r963 ){
return self::$m4is_r1546->m4is_x66();
 
}else{
$m4is_v27639 =m4is_q82::m4is_d6758(self::$m4is_r1546->m4is_x66(), 'mmeb_user', 'login_page', 0 );
 if($m4is_v27639 ){
 wp_redirect(get_permalink($m4is_v27639, 302, 'Memberium' ));
 exit;
 
}else{
return;
 
}
}
}$m4is_s6207 =self::$m4is_f683->m4is_h03978($m4is_y193);
 self::$m4is_r1546->m4is_s07281($m4is_s6207 );
 $m4is_l17096 =get_user_by('email', $m4is_s6207);
 $m4is_f087 =$m4is_l17096->ID;
 $_POST['pwd']=$m4is_i935[$m4is_r6234];
 m4is_l5841::m4is_e40($m4is_y193);
 if($m4is_f087 > 0){
wp_set_auth_cookie($m4is_f087);
 wp_set_current_user($m4is_f087);
 m4is_l5841::m4is_e40($m4is_y193);
 m4is_q82::m4is_u687($m4is_f087 );
     if($m4is_r963){
return $m4is_f087;
 
}else{
 $m4is_v27639 =m4is_q82::m4is_d6758($m4is_f087, 'mmeb_user', 'login_page', 0 );
 if($m4is_v27639 ){
wp_redirect(get_permalink($m4is_v27639 ), 302, 'Memberium' );
 exit;
 
}else{
return;
 
}
}
}return false;
 
}static 
function m4is_m56($m4is_y193, $m4is_l0649 =null ){
global $user;
 $m4is_a173 ='';
 self::$m4is_r1546->m4is_i80956();
 if(is_a($m4is_l0649, 'WP_Error' )){
$m4is_h21895 =empty($m4is_y193 )? 0 : self::$m4is_r1546->m4is_o70((string) $m4is_y193 );
 $m4is_e0234 =empty($m4is_l0649->errors )? []: $m4is_l0649->errors;
 foreach($m4is_e0234 as $m4is_l9671 =>$m4is_v586 ){
$m4is_a173 .= $m4is_v586[0]. ' ';
 
}$m4is_a173 =trim(strip_tags($m4is_a173 ));
 $m4is_o06173 =m4is_q62395::m4is_v729($m4is_h21895, 'loginfail', "({$m4is_y193
}) {$m4is_a173
}", m4is_a01587::m4is_y342(), true );
 setcookie('login_error', $m4is_a173, (time()+ 60 ));
 
}  if(isset($_POST['woocommerce-login-nonce'])){
return;
 
}$m4is_m68532 =isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: '';
  if(empty($refererer )){
$m4is_y90287 =self::$m4is_r1546->m4is_j498('settings', 'login_url' );
 $m4is_m68532 =get_permalink($m4is_y90287 );
 
} if(!empty($m4is_m68532 )&&!strstr($m4is_m68532, 'wp-login' )&&!strstr($m4is_m68532, 'wp-admin' )){
$m4is_g2361 =[];
 $m4is_g2361['login']='failed';
 if(!empty($_POST['redirect_to'])&&stripos($_POST['redirect_to'], '/wp-admin/' )=== false ){
$m4is_g2361['redirect_to']=$_POST['redirect_to'];
 
}$m4is_m68532 =add_query_arg($m4is_g2361, $m4is_m68532 );
 wp_redirect($m4is_m68532 );
  exit;
 
}
}   static 
function m4is_c09($m4is_l17096, $m4is_y193 ='', $m4is_m676 ='' ){
if(!is_a($m4is_l17096, 'WP_User' )){
return $m4is_l17096;
 
} if(user_can($m4is_l17096, 'manage_options' )){
return $m4is_l17096;
 
}$m4is_l17096 =self::m4is_u387($m4is_l17096, $m4is_y193, $m4is_m676 );
 if(is_a($m4is_l17096, 'WP_User' )){
$m4is_l17096 =self::m4is_j85316($m4is_l17096, $m4is_y193, $m4is_m676 );
 
}if(is_a($m4is_l17096, 'WP_User' )){
$m4is_l17096 =self::m4is_h325($m4is_l17096, $m4is_y193, $m4is_m676 );
 
}if(is_a($m4is_l17096, 'WP_User' )){
$m4is_l17096 =self::m4is_q41($m4is_l17096, $m4is_y193, $m4is_m676 );
 
}return $m4is_l17096;
 
}private static 
function m4is_u387($m4is_l17096, $m4is_y193 ='', $m4is_m676 =''){
if(self::$m4is_r1546->m4is_j498('settings', 'allow_local_logins')){
return $m4is_l17096;
 
}if(user_can($m4is_l17096, 'manage_options')){
return $m4is_l17096;
 
}$m4is_h21895 =self::$m4is_r1546->m4is_o70($m4is_l17096->user_email);
 if($m4is_h21895){
$m4is_i935 =m4is_p40::m4is_p67($m4is_h21895);
 if(empty($m4is_i935)){
$m4is_h21895 =false;
 
}
}if(empty($m4is_h21895 )){
$m4is_l17096 =new WP_Error('authentication_failed', __('Local logins not permitted.', 'memberium'));
 self::$m4is_r1546->m4is_i80956();
 
}return $m4is_l17096;
 
}private static 
function m4is_j85316($m4is_l17096, $m4is_y193 ='', $m4is_m676 =''){
static $m4is_w671 =[];
 $m4is_q7686 =self::$m4is_r1546->m4is_j498('settings', 'site_ban_tag', 0 );
 if(!$m4is_q7686 ){
return $m4is_l17096;
 
}$m4is_l9321 =array_filter(explode(',', m4is_q82::m4is_d6758($m4is_l17096->ID, 'memb_user', 'tags', '' )));
 if(!in_array($m4is_q7686, $m4is_l9321 )){
return $m4is_l17096;
 
}$m4is_h21895 =m4is_q82::m4is_d6758($m4is_l17096->ID, 'memb_user', 'crm_id', 0 );
 self::$m4is_r1546->m4is_i80956();
 $m4is_l17096 =new WP_Error('authentication_failed', __('Your access is currently suspended. Please contact customer support for further details.', 'memberium' ));
 return $m4is_l17096;
 
}private static 
function m4is_h325($m4is_l17096, $m4is_y193 ='', $m4is_m676 ='' ){
if(!self::$m4is_r1546->m4is_j498('settings', 'require_membership' )){
return $m4is_l17096;
 
}$m4is_l5726 =m4is_q82::m4is_d6758($m4is_l17096->ID, 'memb_user', 'membership_tags', '' );
 if(!empty($m4is_l5726 )){
return $m4is_l17096;
 
} self::$m4is_r1546->m4is_i80956();
 $m4is_l17096 =new WP_Error('authentication_failed', __('<strong>Error</strong>: Access Denied - No Active Membership.', 'memberium'));
 return $m4is_l17096;
 
} private static 
function m4is_q41(WP_User $m4is_l17096, $m4is_y193 ='', $m4is_m676 ='' ){
global $wpdb;
 $m4is_f087 =$m4is_l17096->ID;
 $m4is_h21895 =self::$m4is_r1546->m4is_z56();
 if(!$m4is_h21895 ){
return $m4is_l17096;
 
} if(apply_filters('memberium/loginsecurity/iplimits/precheck', false, $m4is_f087 )){
return $m4is_l17096;
 
} if(apply_filters('memberium/loginsecurity/iplimits/ipaddress', false, m4is_a01587::m4is_y342(), $m4is_f087 )){
return $m4is_l17096;
 
} $m4is_p8623 =(int) apply_filters('memberium/loginsecurity/iplimits/maxips', self::$m4is_g1520, $m4is_f087 );
 $m4is_m305 =(int) apply_filters('memberium/loginsecurity/iplimits/hours', self::$m4is_f7298, $m4is_f087 );
 if($m4is_p8623 &&$m4is_m305 ){
$m4is_m7426 =self::m4is_h37();
 $m4is_n9130 =m4is_a01587::m4is_y342();
 $m4is_r9613 =self::$m4is_r9613;
 $m4is_e57 =time()- (3600 * $m4is_m305);
 $m4is_v2613 ="SELECT count( distinct( `ipaddress` ) ) as `logins` FROM `{$m4is_m7426
}` WHERE `appname` = %s AND `logintime` >= %d AND `username` = %s AND `ipaddress` != %s ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_r9613, $m4is_e57, $m4is_y193, $m4is_n9130 );
 $m4is_u6591 =$wpdb->get_col($m4is_v2613 );
 $m4is_k9075 =end($m4is_u6591 );
 if($m4is_k9075 >= $m4is_p8623 ){
$m4is_q847 ='Login Failed';
  $m4is_a173 ='Maximum Login IPs exceeded.';
  $m4is_f4930 =isset($m4is_l17096->user_email )? $m4is_l17096->user_email : '';
 $m4is_l17096 =new WP_Error('authentication_failed', __("<strong>{$m4is_q847
}</strong>:  {$m4is_a173
}", 'memberium' ));
 return $m4is_l17096;
 
}
}return $m4is_l17096;
 
}   public static 
function m4is_y36650(): string {
return self::$m4is_b1723;
 
} public static 
function m4is_u5823(): void {
if(empty($_POST['pwd'])||empty($_POST['log'])){
return;
 
}$m4is_f36 =(bool) m4is_r83::m4is_c26()->m4is_j498('settings', 'persistent_login', 0 );
 if($m4is_f36 ){
$_POST['rememberme']='forever';
 
}
} 
}

