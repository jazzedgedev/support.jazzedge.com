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
class m4is_z37620 {
private const MAX_URL_LENGTH =100;
 private static m4is_r83 $m4is_r1546;
 public static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 
}private 
function __construct(){

} public static 
function m4is_s18(int $m4is_f087, array $m4is_w9618 =[]){
global $wpdb;
 if(self::$m4is_r1546->m4is_y90572()){
return;
 
}if(isset($_POST['createaccount'])&&empty($_POST['createaccount'])){
return;
 
} if($_SERVER['REQUEST_METHOD']== 'POST' &&isset($_POST['memberium_add_contact'])&&is_admin()){
if($_POST['memberium_add_contact']<> 'on' ){
return;
 
}
}if(empty(self::$m4is_r1546->m4is_j498('settings', 'sync_new_wp_users' ))){
return;
 
} if(user_can($m4is_f087, 'edit_others_posts' )){
return;
 
}$m4is_p4935 =self::$m4is_r1546->m4is_j498('settings', 'username_field' );
 $m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field' );
 $m4is_r2369 =self::$m4is_r1546->m4is_j498('settings', 'local_auth_only' );
 $m4is_r01667 =(int) self::$m4is_r1546->m4is_j498('settings', 'new_user_registration_tag' );
 $m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 $m4is_l17096 =get_userdata($m4is_f087 );
 $m4is_l79562 =get_user_meta($m4is_f087 );
 $m4is_e26149 =isset($_POST['action'])&&($_POST['action']== 'createuser' )&&isset($_POST['createuser']);
 $m4is_i935 =[];
 $m4is_s680 =0;
 $m4is_p0872 =m4is_p40::m4is_o1723();
 $m4is_x3066 =['first_name' =>'FirstName', 'last_name' =>'LastName', 'user_email' =>'Email', 'user_pass' =>$m4is_r6234, 'user_url' =>'Website', ];
 foreach($m4is_x3066 as $m4is_d9542 =>$m4is_v43967 ){
if(!empty($m4is_w9618[$m4is_d9542])){
$m4is_i935[$m4is_v43967]=$m4is_w9618[$m4is_d9542];
 
}
}if(empty($m4is_i935[$m4is_r6234])){
$m4is_i935[$m4is_r6234]=self::$m4is_r1546->m4is_h956();
 
} if(isset($_POST['wp-submit'])&&$_POST['wp-submit']== 'Register' ){
$m4is_i935['FirstName']=isset($_POST['firstname'])? trim($_POST['firstname']): $m4is_i935['FirstName'];
 $m4is_i935['LastName']=isset($_POST['lastname'])? trim($_POST['lastname']): $m4is_i935['LastName'];
 
}$m4is_j34 =apply_filters('memberium_registration_field_map', []);
 foreach($m4is_j34 as $m4is_b39640 =>$m4is_b16478 ){
if(isset($m4is_l79562[$m4is_b39640][0])){
$m4is_i935[$m4is_b16478]=trim($m4is_l79562[$m4is_b39640][0]);
 
}
}foreach($m4is_j34 as $m4is_w57280 =>$m4is_c198 ){
if(isset($_POST[$m4is_w57280])){
$m4is_i935[$m4is_c198]=$_POST[$m4is_w57280];
 
}
}if(!empty($m4is_s680 )){
$m4is_i935['LeadSourceId']=$m4is_s680;
 
}$m4is_i935 =apply_filters('memberium/user/register/fields', $m4is_i935, $m4is_l17096 );
 $m4is_i935 =array_filter($m4is_i935 );
 if($m4is_r2369 ){
unset($m4is_i935[$m4is_r6234]);
 
}if(empty($m4is_i935['Email'])){
return;
 
}$m4is_h21895 =m4is_p40::m4is_k82670($m4is_i935 );
 if(empty($m4is_h21895 )){
return;
 
}$m4is_h76842 =m4is_p40::m4is_t609($m4is_h21895 );
 m4is_p40::m4is_y935($m4is_i935['Email'], 'Membership Site Registration' );
 self::$m4is_r1546->m4is_v1694($m4is_h76842 );
 if($m4is_r01667 ){
self::$m4is_r1546->m4is_k98([$m4is_r01667], $m4is_h21895 );
 
}if($m4is_h21895 ){
 self::$m4is_r1546->m4is_d1796($m4is_h21895, $m4is_f087 );
 m4is_q82::m4is_u687($m4is_f087 );
 
}if(get_user_meta($m4is_f087, '_fb_is_sync', true )!= 1){
delete_user_meta($m4is_f087, '_fb_is_sync' );
 add_user_meta($m4is_f087, '_fb_is_sync', 1 );
 
}
} public static 
function m4is_y66($m4is_f087, $m4is_z85, $m4is_l17096 ){
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if($m4is_h21895 ){
m4is_p40::m4is_z62($m4is_h21895 );
 
}m4is_p40::m4is_u42($m4is_f087 );
 
} public static 
function m4is_j602(string $m4is_n6062 ): string {
return mb_substr((string) $m4is_n6062, 0, self::MAX_URL_LENGTH );
 
}   public static 
function m4is_g31(int $m4is_h21895, ?string $m4is_m676 =null ): void {

}public static 
function m4is_h4792(int $m4is_h21895 ): void {

}
}

