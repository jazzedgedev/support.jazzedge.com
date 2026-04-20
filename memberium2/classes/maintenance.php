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
class m4is_f642 {
private static object $m4is_r1546;
 private static string $m4is_r9613;
 static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 
}static 
function m4is_c69762(){
m4is_s52::m4is_b28571();
 self::m4is_l302();
 self::$m4is_r1546->m4is_t594();
 
}static 
function m4is_l302(){
if(!self::$m4is_r1546->m4is_j498('settings', 'sync_ecommerce', false )){
return;
 
}m4is_v87365::m4is_l318();
 
} static 
function m4is_l6254(){
$m4is_u6095 ='RecurringOrder';
 $m4is_h85 =1000;
 $m4is_r9613 =self::$m4is_r9613;
 $m4is_o0379 =m4is_q62395::m4is_v729(0, 'cron', 'Expiring Subscriptions' );
 date_default_timezone_set('America/New_York' );
 $m4is_z81 =date('Y-m-d');
 $m4is_h3647 =['Id', ];
 $m4is_v76912 =['Status' =>'Active', 'EndDate' =>'~<=~' . $m4is_z81, ];
 $m4is_h023 =m4is_c69807::m4is_o986($m4is_u6095, $m4is_h85, 0, $m4is_v76912, $m4is_h3647 );
 if(is_array($m4is_h023 )){
$m4is_o30 =['Status' =>'Inactive'];
 if(empty($m4is_h90661 )){
m4is_q62395::m4is_x6835($m4is_o0379, "No active expired recurring orders found.\n" );
 
}else{
foreach($m4is_h023 as $m4is_y362 ){
m4is_c69807::m4is_z64($m4is_u6095, (int) $m4is_y362['Id'], $m4is_o30);
 m4is_q62395::m4is_x6835($m4is_o0379, "Deactivating Recurring Order #{$m4is_y362['Id']
}\n" );
 usleep(250000 );
 
}
}
}
} static 
function m4is_t2678(){
$m4is_p6456 =(int) self::$m4is_r1546->m4is_j498('settings', 'makepass_scan_tag', 0 );
 $m4is_q69206 =(int) self::$m4is_r1546->m4is_j498('settings', 'makepass_success_tag', 0 );
 $m4is_y261 =(int) self::$m4is_r1546->m4is_j498('settings', 'makepass_success_actionset', 0 );
 $m4is_l23 =(int) self::$m4is_r1546->m4is_j498('settings', 'makepass_scan_size', 0 );
 $m4is_p4935 =self::$m4is_r1546->m4is_j498('settings', 'username_field', 'Email' );
 $m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field', 'Password' );
 if($m4is_l23 < 1 ||$m4is_l23 > 5 ){
return;
 
}if((!$m4is_p6456 )||(!$m4is_q69206 &&!$m4is_y261 )){
return;
 
}$m4is_o0379 =m4is_q62395::m4is_v729(0, 'cron', 'scanMakePass Started' );
  $m4is_h3647 =m4is_c69807::m4is_f5248('Contact', true );
 $m4is_v76912 =['Groups' =>$m4is_p6456, ];
 $m4is_c1796 =m4is_c69807::m4is_i84('Contact', $m4is_l23, 0, $m4is_v76912, $m4is_h3647, 'LastUpdated', false);
 if(is_array($m4is_c1796)){
foreach($m4is_c1796 as $m4is_i935){
$m4is_h21895 =isset($m4is_i935['Id'])? (int) $m4is_i935['Id']: 0;
 $m4is_v7162 =isset($m4is_i935[$m4is_p4935])? strtolower(trim($m4is_i935[$m4is_p4935])): '';
 $m4is_i2397 =isset($m4is_i935['Email'])? strtolower(trim($m4is_i935['Email'])): '';
 $m4is_z69301 =isset($m4is_i935[$m4is_r6234])? $m4is_i935[$m4is_r6234]: '';
 $m4is_q06138 =$m4is_i935['Groups'];
 $m4is_c8216 =false;
 $m4is_o6956 =false;
 $m4is_e32607 =[];
 m4is_q62395::m4is_x6835($m4is_o0379, "Contact ID = {$m4is_h21895
}, Username = {$m4is_v7162
}");
 if(empty($m4is_h21895)){
break;
 
}if(empty($m4is_v7162)){
$m4is_e32607[$m4is_p4935]=$m4is_i2397;
 $m4is_i935[$m4is_p4935]=$m4is_i2397;
 $m4is_y193 =$m4is_i2397;
 
} if(username_exists($m4is_y193)||email_exists($m4is_y193)){
$m4is_u6591 =m4is_p40::m4is_x6560($m4is_h21895, $m4is_e32607);
  self::$m4is_r1546->m4is_k98("-{$m4is_p6456
}", $m4is_h21895);
 self::$m4is_r1546->m4is_k98("{$m4is_q69206
}", $m4is_h21895);
 break;
 
}if(empty($m4is_i935[$m4is_r6234])){
$m4is_i935[$m4is_r6234]=self::$m4is_r1546->m4is_a601();
 $m4is_e32607[$m4is_r6234]=$m4is_i935[$m4is_r6234];
 $m4is_u6591 =m4is_p40::m4is_x6560($m4is_h21895, $m4is_e32607);
  
}self::$m4is_r1546->m4is_v1694($m4is_i935);
  $m4is_j0976 =[];
 $m4is_j0976['user_login']=$m4is_i935[$m4is_p4935];
 $m4is_j0976['user_pass']=$m4is_i935[$m4is_r6234];
 $m4is_j0976['first_name']=isset($m4is_i935['FirstName'])? trim($m4is_i935['FirstName']): '';
 $m4is_j0976['last_name']=isset($m4is_i935['LastName'])? trim($m4is_i935['LastName']): '';
 $m4is_j0976['user_url']=isset($m4is_i935['Website'])? trim($m4is_i935['Website']): '';
 $m4is_j0976['user_email']=isset($m4is_i935['Email'])? strtolower(trim($m4is_i935['Email'])): '';
 $m4is_y66291 =['contact' =>$m4is_i935 ];
 $_POST['pass1']=$m4is_i935[$m4is_r6234];
 $m4is_j0976['display_name']=apply_filters('memberium/wpuser/display_name', self::$m4is_r1546->m4is_l43586($m4is_y66291 ), $m4is_i935 );
 $m4is_j0976['nickname']=apply_filters('memberium/wpuser/nickname', $m4is_j0976['display_name'], $m4is_i935);
 $m4is_j0976['user_nicename']=apply_filters('memberium/wpuser/nicename', sanitize_title($m4is_j0976['nickname'], $m4is_j0976['display_name']), $m4is_i935 );
 $m4is_a09532 =self::$m4is_r1546->m4is_f605($m4is_h21895);
 if(is_int($m4is_a09532)&&$m4is_a09532 > 0){
self::$m4is_r1546->m4is_d1796($m4is_h21895, $m4is_a09532);
 do_action('user_register', $m4is_a09532);
 if(is_multisite()&&!is_user_member_of_blog($m4is_a09532)){
$m4is_s740 =get_current_blog_id();
 $m4is_y1662 =get_blog_option($m4is_s740, 'default_role', 'subscriber' );
 add_user_to_blog($m4is_s740, $m4is_a09532, $m4is_y1662 );
 
}if(function_exists('WPCW_actions_users_newUserCreated')){
WPCW_actions_users_newUserCreated($m4is_a09532);
 
}
}if(!empty($m4is_p6456)){
self::$m4is_r1546->m4is_k98("-{$m4is_p6456
}", $m4is_h21895);
 
}if(!empty($m4is_q69206)){
self::$m4is_r1546->m4is_k98($m4is_q69206, $m4is_h21895);
 
}if(!empty($m4is_y261)){
self::$m4is_r1546->m4is_u71903($m4is_y261, $m4is_h21895);
 
}usleep(250000 );
  
}
}
}   private static 
function m4is_a203(){
global $wpdb;
 $m4is_e80 =constant('MEMBERIUM_DB_QUEUE' );
 $m4is_r9613 =self::$m4is_r9613;
 $m4is_v2613 ="SELECT count(*) FROM `{$m4is_e80
}` WHERE `appname` = '{$m4is_r9613
}' AND `actiontype` = 'contactupdate'";
 return $wpdb->get_var($m4is_v2613);
 
}private static 
function m4is_k6914(){
global $wpdb;
 $m4is_k164 =0;
 if(self::m4is_a203()){
$m4is_e80 =constant('MEMBERIUM_DB_QUEUE' );
 $m4is_v2613 ="SELECT `id`, `data` FROM `{$m4is_e80
}` WHERE `actiontype` = 'contactupdate' ORDER BY `id` ASC ";
 $m4is_m615 =$wpdb->get_results($m4is_v2613, OBJECT_K);
 if(is_array($m4is_m615)){
foreach($m4is_m615 as $m4is_d07693 =>$m4is_g91703){
$m4is_k164++;
 
}
}
}return $m4is_k164;
 
}private static 
function m4is_f43598($m4is_h21895){
global $wpdb;
 $m4is_r9613 =self::$m4is_r9613;
 $m4is_e80 =m4is_p40::m4is_o1723();
 $m4is_v2613 ="SELECT `value` FROM `{$m4is_e80
}` WHERE `id` = {$m4is_h21895
} AND `appname` = '{$m4is_r9613
}' AND `fieldname` = 'LastUpdated'";
 return $wpdb->get_var($m4is_v2613);
 
}private static 
function m4is_e4769($m4is_i935){
global $wpdb;
 $m4is_h21895 =isset($m4is_i935['Id'])? $m4is_i935['Id']: 0;
 if($m4is_h21895){
$m4is_e80 =constant('MEMBERIUM_DB_QUEUE' );
 $m4is_v2613 ="DELETE FROM `{$m4is_e80
}` WHERE `contactid` = {$m4is_h21895
} AND `action` = 'contactupdate';";
 $m4is_v2613 ="INSERT INTO `{$m4is_e80
}` () VALUES ()";
 
}
}private static 
function m4is_g668(){
global $wpdb;
 $m4is_d460 =get_option('memberium/contact/scan', '2000-01-01T00:00:00');
 $m4is_e80 ='Contact';
 $m4is_a89 =m4is_c69807::m4is_f5248($m4is_e80, true);
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_t042 ='LastUpdated';
 $m4is_k236 =1;
 $m4is_v76912 =['Groups' =>'%', 'LastUpdated' =>"~>=~ {$m4is_d460
}",  ];
 $m4is_c1796 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_a89, $m4is_t042, $m4is_k236);
 if(is_array($m4is_c1796)){
foreach($m4is_c1796 as $m4is_i935){
$m4is_h21895 =$m4is_i935['Id'];
 $m4is_i3206 =$m4is_i935['LastUpdated'];
 
}
}echo '<Pre>', count($m4is_c1796), '</Pre>';
 echo '<Pre>', print_r($m4is_c1796, true), '</Pre>';
 echo '<Pre>', print_r($m4is_a89, true), '</Pre>';
 
}private static 
function m4is_s921(){
self::m4is_k6914();
 self::m4is_g668();
 
} public static 
function m4is_d267(): void {
global $wpdb;
 $m4is_n246 =3;
 $m4is_w32495 =(int) self::$m4is_r1546->m4is_j498('settings', 'api_log_duration', $m4is_n246 );
 $m4is_w32495 =max(1, min(7, $m4is_w32495 ));
 $m4is_e80 =m4is_r83::m4is_g6365();
 $m4is_v2613 =$wpdb->prepare("DELETE FROM `{$m4is_e80
}` WHERE `appname` = %s AND `timestamp` < DATE_SUB( NOW(), INTERVAL %d DAY )", self::$m4is_r9613, $m4is_w32495 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 delete_transient(m4is_l5841::m4is_y36650());
 if($m4is_u6591 ){
$m4is_a173 =sprintf('API Log trimmed to last %d day(s). %d rows deleted.', $m4is_w32495, $m4is_u6591 );
 m4is_q62395::m4is_v729(0, 'cron', $m4is_a173 );
 
}
}
}

