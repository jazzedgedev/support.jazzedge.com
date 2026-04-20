<?php

/**
* Copyright (c) 2018-2022 David J Bullock
* Web Power and Light
*/


  class_exists('m4is_r83' )||die();
  final 
class m4is_q1089 {
private $m4is_d2346 =[];
 private $m4is_r1546;
  public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_d4861();
 if(is_admin()){
require_once __DIR__ . '/admin.php';
 m4is_z6342::m4is_c26();
 
}else{
require_once __DIR__ . '/frontend.php';
 m4is_q02::m4is_c26();
 
}
} private 
function m4is_d4861(): void {
 $this->m4is_w16759();
 add_action('bp_init', [$this, 'm4is_r36']);
 add_action('bp_init', [$this, 'm4is_m67']);
 add_action('xprofile_updated_profile', [$this, 'm4is_z9217'], 0, 5 );
 add_action('memberium/session/updated', [$this, 'm4is_h6317'], 11, 2 );
 add_action('memberium/session/updated', [$this, 'm4is_b65643'], 10, 2 );
 add_action('after_setup_theme', [$this, 'm4is_e3651']);
  add_filter('memberium/modules/active/names', [$this, 'm4is_f809'], 10, 1 );
 add_filter('memberium/user/register/fields', [$this, 'm4is_t97'], 10, 2 );
 add_filter('memberium/wpuser/nickname', [$this, 'm4is_m4389'], PHP_INT_MAX, 2 );
 add_filter('send_email_change_email', [$this, 'm4is_g5087'], 1, 3 );
 if(true ||!empty($this->m4is_r1546->m4is_j498('memberships' ))){
remove_filter('bp_login_redirect', 'bb_login_redirect', PHP_INT_MAX );
 remove_filter('login_redirect', 'bp_login_redirect', PHP_INT_MAX );
 remove_filter('logout_redirect', 'bb_logout_redirect', PHP_INT_MAX );
 
}
} public 
function m4is_r36(): void {
if(bp_is_active('settings' )){
add_filter('send_password_change_email', [$this, 'm4is_o70416'], 1, 3 );
 
}
} private 
function m4is_w16759(): void {
if(bp_is_active('groups' )){
require_once __DIR__ . '/groups.php';
 memberium_buddypress_groups_class::m4is_c26();
 
}
} public 
function m4is_b65643(int $m4is_f087, array $m4is_k824 ): void {
$this->m4is_d2346[$m4is_f087 ]=$m4is_k824;
 if(!did_action('bp_init' )){
add_action('bp_init', [$this, 'm4is_m67']);
 
}else{
$this->m4is_m67();
 
}
} public 
function m4is_o70416(bool $m4is_a9807, array $m4is_l17096, array $m4is_h615 ){
if(empty($_POST['submit'])||empty($_POST['pass1'])||empty($_POST['pass2'])){
return $m4is_a9807;
 
}if(!bp_is_active('settings' )||!bp_is_settings_component()||!bp_is_current_action('general' )){
return $m4is_a9807;
 
}$m4is_r2369 =(bool) $this->m4is_r1546->m4is_j498('settings', 'local_auth_only', false );
 if($m4is_r2369 ){
return $m4is_a9807;
 
}$m4is_h21895 =m4is_p40::m4is_w58096($m4is_l17096['ID']);
 if(empty($m4is_h21895 )){
return $m4is_a9807;
 
}$m4is_q523 =$this->m4is_r1546->m4is_j498('settings', 'password_field', '' );
 if(empty($m4is_q523 )){
return $m4is_a9807;
 
}$m4is_i935 =[];
 $m4is_i935[$m4is_q523]=$_POST['pass1'];
 m4is_p40::m4is_x6560($m4is_h21895, $m4is_i935 );
 return $m4is_a9807;
 
} public 
function m4is_m67(): void {
 $this->m4is_w16759();
 foreach($this->m4is_d2346 as $m4is_f087 =>$m4is_k824 ){
if(!empty($m4is_k824['keap']['contact'])){
$this->m4is_t50($m4is_k824['keap']['contact'], $m4is_f087 );
 
}
}
} public 
function m4is_m4389(string $m4is_a621 ='', array $m4is_i935 =[]){
$m4is_a621 =sanitize_title_with_dashes($m4is_a621, null, 'save' );
 return $m4is_a621;
 
} public 
function m4is_t97(array $m4is_i935, $m4is_l17096 ): array {
if(!is_a($m4is_l17096, 'WP_User' )){
return $m4is_i935;
 
}if(!function_exists('bp_xprofile_firstname_field_id' )||!function_exists('bp_xprofile_lastname_field_id' )){
return $m4is_i935;
 
}$m4is_f087 =$m4is_l17096->ID;
 $m4is_r6234 =$this->m4is_r1546->m4is_j498('settings', 'password_field' );
  $m4is_d45 =$this->m4is_r1546->m4is_j498('settings', 'local_auth_only' );
 $m4is_o971 =bp_xprofile_firstname_field_id();
 $m4is_g84693 =bp_xprofile_lastname_field_id();
 $m4is_q087 =bp_xprofile_nickname_field_id();
 $m4is_m1260 =empty($_POST["field_{$m4is_o971
}"])? '' : $_POST["field_{$m4is_o971
}"];
 $m4is_k1047 =empty($_POST["field_{$m4is_g84693
}"])? '' : $_POST["field_{$m4is_g84693
}"];
 $m4is_a621 =empty($_POST["field_{$m4is_q087
}"])? '' : $_POST["field_{$m4is_q087
}"];
 $m4is_i935['FirstName']=empty($m4is_i935['FirstName'])? $m4is_m1260 : $m4is_i935['FirstName'];
 $m4is_i935['LastName']=empty($m4is_i935['LastName'])? $m4is_k1047 : $m4is_i935['LastName'];
 $m4is_i935['Nickname']=empty($m4is_i935['Nickname'])? $m4is_a621 : $m4is_i935['Nickname'];
 if(!$m4is_d45 ){
$m4is_m676 =empty($_POST['signup_password'])? '' : trim($_POST['signup_password']);
 $m4is_i935[$m4is_r6234]=empty($m4is_i935[$m4is_r6234])? $m4is_m676 : $m4is_i935[$m4is_r6234];
 
}return $m4is_i935;
 
} public 
function m4is_e3651(): void {
remove_filter('login_redirect', 'buddyboss_redirect_previous_page', 10 , 3 );
 
} public 
function m4is_f809(array $m4is_y634 ): array {
global $buddyboss_platform_plugin_file;
 if(!empty($buddyboss_platform_plugin_file )){
$m4is_k52736 ='BuddyBoss Platform Support';
 
}else{
$m4is_k52736 ='BuddyPress Support';
 
}return array_merge($m4is_y634, [$m4is_k52736, ]);
 
} public 
function m4is_h6317(int $m4is_f087, array $m4is_k824 ): void {
if(!function_exists('bp_set_member_type' )||!function_exists('bp_get_member_type' )){
return;
 
}$m4is_n73190 =$m4is_k824['memb_user']['membership_id']??= 0;
 if(!$m4is_n73190 ){
return;
 
}$m4is_w64 =(array)$this->m4is_r1546->m4is_j498('memberships' )[$m4is_n73190];
 if(empty($m4is_w64['buddypress_profile_type'])){
return;
 
}$m4is_i6671 =bp_get_member_type($m4is_f087 );
 $m4is_s93784 =$m4is_w64['buddypress_profile_type'];
 if($m4is_s93784 == $m4is_i6671 ){
return;
 
}bp_set_member_type($m4is_f087, $m4is_s93784 );
 
} public 
function m4is_z9217(int $m4is_f087, $m4is_e59647, $m4is_e0234, $m4is_h89, $m4is_p6521 ){
if(!class_exists('BP_XProfile_Field' )){
return;
 
}$m4is_p496 =get_option('memberium_xprofile_map', []);
 $m4is_x0645 =m4is_c69807::m4is_n5367();
 foreach ($m4is_p496 as $m4is_u680 =>$m4is_m661 ){
if(empty($m4is_m661 )||empty($m4is_p6521[$m4is_m661])){
continue;
 
}$m4is_g89163 =empty($m4is_x0645[$m4is_u680])? 15 : $m4is_x0645[$m4is_u680];
 $m4is_u6820 =empty($m4is_p6521[$m4is_m661]['value'])? '' : $m4is_p6521[$m4is_m661]['value'];
 $m4is_b39640 ='memb_' . $m4is_u680;
 if($m4is_g89163 == 13 ||$m4is_g89163 == 14 ){
if(!empty($m4is_u6820 )){
$m4is_u6820 =date('Y-m-d\TH:i:s', strtotime($m4is_u6820 ));
 
}
}update_user_meta($m4is_f087, $m4is_b39640, $m4is_u6820 );
 
}
} public 
function m4is_t50(array $m4is_i935, int $m4is_f087 ): void {
if(!function_exists('xprofile_set_field_data' )){
return;
 
}if(empty($m4is_i935 )){
return;
 
}$m4is_p496 =get_option('memberium_xprofile_map', []);
 $m4is_x0645 =m4is_c69807::m4is_n5367();
 if(empty($m4is_p496 )||empty($m4is_x0645 )||!is_array($m4is_x0645 )||!is_array($m4is_p496 )){
return;
 
}$m4is_p496 =array_change_key_case($m4is_p496, CASE_LOWER );
 $m4is_x0645 =array_change_key_case($m4is_x0645, CASE_LOWER );
 foreach ($m4is_p496 as $m4is_u680 =>$m4is_m661 ){
if($m4is_m661 ){
$m4is_g89163 =empty($m4is_x0645[$m4is_u680 ])? 15 : $m4is_x0645[$m4is_u680 ];
 $m4is_v586 =empty($m4is_i935[$m4is_u680])? '' : $m4is_i935[$m4is_u680 ];
 if(in_array($m4is_g89163, [13, 14 ])){
if(!empty($m4is_v586 )){
$m4is_v586 =date('Y-m-d H:i:s', strtotime($m4is_v586 ));
 
}
}$m4is_u6591 =xprofile_set_field_data($m4is_m661, $m4is_f087, $m4is_v586 );
 
}
}
} public 
function m4is_g5087(bool $m4is_a9807 , array $m4is_l17096, array $m4is_q6269 ): bool {
$m4is_v08746 =['bp_is_active', 'bp_is_settings_component', 'bp_is_current_action' ];
 foreach ($m4is_v08746 as $m4is_a1056 ){
if(!function_exists($m4is_a1056 )){
return $m4is_a9807;
 
}
}if(!bp_is_active('settings' )||!bp_is_settings_component()||!bp_is_current_action('general' )){
return $m4is_a9807;
 
}$m4is_f087 =$m4is_l17096['ID'];
 $m4is_m166 =$m4is_q6269['user_email'];
 if(empty($m4is_m166 )){
return $m4is_a9807;
 
}$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if(empty($m4is_h21895 )){
return $m4is_a9807;
 
}$this->m4is_r1546->m4is_y6259($m4is_f087, $m4is_m166 );
 return $m4is_a9807;
 
}  public 
function m4is_c712($user_id ){

} public 
function m4is_p626(int $m4is_f087 ): void {
$m4is_l17096 =get_user_by('id', $m4is_f087);
 if(!$m4is_l17096 ){
return;
 
}$m4is_g928 =$m4is_l17096->user_login;
 $m4is_h65906 =new BP_Signup;
 $m4is_l91805 =$m4is_h65906->get(['user_login' =>$m4is_g928]);
 $m4is_o436 =isset($m4is_l91805['signups'][0]->signup_id )? $m4is_l91805['signups'][0]->signup_id : 0;
 if(!$m4is_o436 ){
return;
 
}$m4is_h65906->activate([$m4is_o436]);
 
}
}

