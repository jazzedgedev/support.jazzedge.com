<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_p40 {
private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_p0872;
 private static $m4is_o430;
 private static $m4is_z59682;
 private static object $m4is_e6426;
  public static 
function m4is_c961(){
global $wpdb;
 self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_e6426 =self::$m4is_r1546->m4is_z40()->elf_rest();
 self::$m4is_p0872 ='memberium_contacts';
 self::$m4is_o430 =$wpdb->prefix . 'memberium_uidcid';
 
}private 
function __construct(){
 
}      public static 
function m4is_o1723(): string {
return self::$m4is_p0872;
 
} public static 
function m4is_l93016(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_o1723();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) NOT NULL, \n" . "appname varchar(32) NOT NULL, \n" . "fieldname varchar(64) NOT NULL DEFAULT '', \n" . "value longtext, \n" . "KEY id (id), \n" . "KEY fieldname (fieldname), \n" . "KEY appname (appname), \n" . "KEY value (value(64) ), \n" . "PRIMARY KEY  (appname,id,fieldname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
} public static 
function m4is_t6965(): string {
return self::$m4is_o430;
 
} public static 
function m4is_a24695(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_t6965();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) unsigned NOT NULL AUTO_INCREMENT, \n" . "uid bigint(20) NOT NULL, \n" . "cid bigint(20) NOT NULL, \n" . "UNIQUE KEY relationship (uid,cid), \n" . "PRIMARY KEY  (id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}      public static 
function m4is_x715(): void {
global $wpdb;
  $m4is_f23865 =$wpdb->users;
 $m4is_v294 =self::m4is_t6965();
 $m4is_v2613 ="DELETE FROM %i WHERE NOT EXISTS ( SELECT 1 FROM %i WHERE %i.`uid` = %i.`ID` )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_v294, $m4is_f23865, $m4is_v294, $m4is_f23865 );
 $m4is_d5472 =$wpdb->query($m4is_v2613 );
  $m4is_v2613 ="DELETE FROM %i WHERE `appname` = %s AND ( `value` = '' OR `value` IS NULL )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_p0872, self::$m4is_r9613 );
 $m4is_d5472 =$wpdb->query($m4is_v2613 );
  $m4is_j108 =array_filter(explode(',', self::$m4is_r1546->m4is_j498('settings', 'ignore_contact_fields', '' )));
 if(!empty($m4is_j108 )){
$m4is_d579 =implode("','", $m4is_j108 );
 $m4is_v2613 ="DELETE FROM %i WHERE `appname` = %s AND `fieldname` IN ( '{$m4is_d579
}' )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_p0872, self::$m4is_r9613 );
 $m4is_d5472 =$wpdb->query($m4is_v2613 );
 
}
} public static 
function m4is_u09816(): void {
global $wpdb;
 $m4is_j108 =self::$m4is_r1546->m4is_j498('settings', 'ignore_contact_fields' );
 if(empty($m4is_j108 )){
return;
 
}$m4is_j108 =array_filter(explode(',', $m4is_j108 ));
 $m4is_v2613 ="DELETE FROM %i WHERE `appname` = %s AND `fieldname` IN ('" . implode("','", $m4is_j108 ). "')";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_p0872, self::$m4is_r9613 );
 $wpdb->query($m4is_v2613 );
 
}      public static 
function m4is_w58096(int $m4is_f087 ): int {
static $m4is_e52716 =[];
 if(!$m4is_f087 ){
return 0;
 
}if(user_can($m4is_f087, 'manage_options' )){
return 0;
 
}if(array_key_exists($m4is_f087, $m4is_e52716 )){
return $m4is_e52716[$m4is_f087];
 
}global $wpdb;
  $m4is_v2613 ="SELECT `cid` FROM %i WHERE `uid` = %d ORDER BY `id` ASC LIMIT 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_t6965(), $m4is_f087 );
 $m4is_h21895 =$wpdb->get_var($m4is_v2613 );
 if($m4is_h21895 ){
$m4is_e52716[$m4is_f087]=$m4is_h21895;
 $m4is_i07 =false;
 
}else{
$m4is_i07 =true;
 
} if(!$m4is_h21895 ){
$m4is_h21895 =get_user_meta($m4is_f087, 'infusionsoft_user_id', true );
 if($m4is_h21895 ){
$m4is_e52716[$m4is_f087]=$m4is_h21895;
 
}
} if(!$m4is_h21895 ){
$m4is_l17096 =get_user_by('id', $m4is_f087 );
 if(is_a($m4is_l17096, 'WP_User' )){
$m4is_f4930 =(string) $m4is_l17096->user_email;
 $m4is_h21895 =m4is_p40::m4is_i68($m4is_f4930 );
 if($m4is_h21895 ){
$m4is_e52716[$m4is_f087]=$m4is_h21895;
 
}
}
}if($m4is_h21895 > 0 &&true == $m4is_i07 ){
self::m4is_m684($m4is_f087, $m4is_h21895 );
 
}return (int) $m4is_h21895;
 
} public static 
function m4is_i6158(int $m4is_h21895 ): int{
if(!$m4is_h21895 ){
return 0;
 
}global $wpdb;
 static $m4is_e52716 =[];
  $m4is_j329 =false;
 if(array_key_exists($m4is_h21895, $m4is_e52716 )){
return $m4is_e52716[$m4is_h21895];
 
} $m4is_v2613 ="SELECT `uid` FROM %i WHERE `cid` = %d ORDER BY `id` ASC LIMIT 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_t6965(), $m4is_h21895 );
 $m4is_f087 =$wpdb->get_var($m4is_v2613 );
 if($m4is_f087 ){
$m4is_e52716[$m4is_h21895]=$m4is_f087;
 return $m4is_f087;
 
} $m4is_l43966 ='memberium/contact_id/' . $m4is_h21895;
 $m4is_v2613 ="SELECT `user_id` FROM `{$wpdb->usermeta
}` WHERE `meta_key` = %s ORDER BY `umeta_id` DESC";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_l43966 );
 $m4is_f087 =$wpdb->get_var($m4is_v2613 );
   if(user_can($m4is_f087, 'manage_options' )){
return 0;
 
}if($m4is_f087 == 0 ){
return 0;
 
}$m4is_e52716[$m4is_h21895]=$m4is_f087;
 if($m4is_j329){
m4is_p40::m4is_m684($m4is_f087, $m4is_h21895 );
 
}return $m4is_f087;
 
} public static 
function m4is_i68(string $m4is_f4930 ): int {
global $wpdb;
 $m4is_o14 ='memberium2/contacts/queries/crm_id';
 $m4is_s82753 =300;
 $m4is_t265 =false;
 $m4is_f4930 =strtolower(trim($m4is_f4930 ));
 $m4is_v2613 ="SELECT `id` FROM %i WHERE `appname` = %s AND `fieldname` = 'Email' AND `value` = %s ORDER BY `id` ASC LIMIT 1;";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_p0872, self::$m4is_r9613, $m4is_f4930 );
 $m4is_q1046 =md5($m4is_v2613 );
 $m4is_h21895 =wp_cache_get($m4is_q1046, $m4is_o14, false, $m4is_t265 );
 if(!$m4is_t265 ){
$m4is_h21895 =(int) $wpdb->get_var($m4is_v2613 );
 if(!empty($m4is_h21895 )){
wp_cache_set($m4is_q1046, $m4is_h21895, $m4is_o14, $m4is_s82753 );
 
}
}return $m4is_h21895;
 
} public static 
function m4is_m684(int $m4is_t6056, int $m4is_e95021 ): void {
global $wpdb;
 $m4is_e80 =self::m4is_t6965();
 $m4is_v2613 ='SELECT count(`id`) FROM %i WHERE ( `uid` <> %d AND `cid` = %d ) OR ( `uid` = %d AND `cid` <> %d )  LIMIT 1';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_t6056, $m4is_e95021, $m4is_t6056, $m4is_e95021 );
 $m4is_u6591 =$wpdb->get_var($m4is_v2613 );
 if($m4is_u6591 > 0 ){
$m4is_v2613 ='DELETE FROM %i WHERE (`uid` != %d AND `cid` = %d ) OR (`uid` = %d AND `cid` <> %d ) -- TEST';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_t6056, $m4is_e95021, $m4is_t6056, $m4is_e95021 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 
}$m4is_v2613 ="INSERT INTO %i (`uid`, `cid`) VALUES (%d, %d) ON DUPLICATE KEY UPDATE `uid` = VALUES(`uid`), `cid` = VALUES(`cid`)";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_t6056, $m4is_e95021 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 delete_user_meta($m4is_t6056, 'infusionsoft_user_id' );
 delete_user_meta($m4is_t6056, "memberium/contact_id/{$m4is_e95021
}" );
 
} public static 
function m4is_u42(int $m4is_f087 ): void {
global $wpdb;
 $m4is_v2613 ="DELETE FROM %i WHERE `uid` = %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_t6965(), $m4is_f087 );
 $m4is_m615 =$wpdb->query($m4is_v2613 );
 
} public static 
function m4is_h06(): void {
global $wpdb;
 $m4is_c92430 =25;
 $m4is_o430 =self::m4is_t6965();
 $m4is_s03654 =$wpdb->usermeta;
 $m4is_v2613 ="SELECT `um`.`user_id`, `um`.`meta_value` FROM %i `um` LEFT JOIN %i `uidcid` ON `um`.`user_id` = `uidcid`.`uid` WHERE `um`.`meta_key` = 'infusionsoft_user_id' AND `um`.`meta_value` > 0 AND `uidcid`.`uid` IS NULL LIMIT %d ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_s03654, $m4is_o430, $m4is_c92430 );
 $m4is_r403 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 $m4is_r403 =is_array($m4is_r403 )? array_filter($m4is_r403 ): $m4is_r403;
  foreach ($m4is_r403 as $m4is_f087 =>$m4is_t366 ){
$m4is_h21895 =(int) $m4is_t366->meta_value;
 self::m4is_m684($m4is_f087, $m4is_h21895 );
 
}
}      public static 
function m4is_h76054(){
global $wpdb;
 if(!m4is_s52::m4is_f27()){
return;
 
}$m4is_s92076 ='memberium/contact/async/locked';
 $m4is_b06 ='memberium/contact/async/last_update';
 $m4is_c92430 =(int) self::$m4is_r1546->m4is_j498('settings', 'async_limit', 0 );
 $m4is_t8704 =(int) self::$m4is_r1546->m4is_j498('settings', 'async_tag', 0 );
 $m4is_w0428 =(int) self::$m4is_r1546->m4is_j498('settings', 'disable_login_sync', 0 );
 $m4is_z30578 =(int) self::$m4is_r1546->m4is_j498('settings', 'max_contact_age', 0 );
 if(empty($m4is_c92430 )&&empty($m4is_t8704 )){
return;
 
}if(empty($m4is_c92430 )){
m4is_q62395::m4is_v729(0, 'cron', 'Background Contact Update Skipped.  No limit set.' );
 return;
 
}if(empty($m4is_t8704 )){
m4is_q62395::m4is_v729(0, 'cron', 'Background Contact Update Skipped.  No sync tag set.' );
 return;
 
}if(empty($m4is_w0428 )){
m4is_q62395::m4is_v729(0, 'cron', 'Background Contact Update Skipped.  Login sync not disabled.' );
 return;
 
}if(empty($m4is_z30578 )){
m4is_q62395::m4is_v729(0, 'cron', 'Background Contact Update Skipped.  Max Contact Age not set.' );
 return;
 
}if(!update_option($m4is_s92076, 1, true )){
m4is_q62395::m4is_v729(0, 'cron', 'Background Contact Update Locked.  Bulk Sync Skipped' );
 return;
 
}update_option($m4is_s92076, 1 );
 $m4is_c53 =time();
 $m4is_c1796 =[];
 $m4is_d3012 =0;
 $m4is_e80 ='Contact';
 $m4is_h973 =0;
 $m4is_p65 =[];
 $m4is_u68057 =get_option($m4is_b06, '' );
 $m4is_u68057 =empty($m4is_u68057 )? '2000-01-01 00:00:00' : $m4is_u68057;
 $m4is_z59682 =self::$m4is_z59682;
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, true );
 $m4is_v76912 =['LastUpdated' =>"~>=~ {$m4is_u68057
}", 'Groups' =>$m4is_t8704, ];
 $m4is_c1796 =self::$m4is_z59682->dsQueryOrderBy($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'LastUpdated', true );
 $m4is_c1796 =is_array($m4is_c1796 )? array_filter($m4is_c1796 ): $m4is_c1796;
 $m4is_h973 =is_array($m4is_c1796 )? count($m4is_c1796 ): 0;
 if(is_string($m4is_c1796 )){
error_log('Memberium: [error] Contact Sync API Error - ' . $m4is_c1796 );
 
}if($m4is_h973 ){
foreach ($m4is_c1796 as $m4is_i935 ){
$m4is_p65[]=$m4is_i935['Id'];
 
}$m4is_h5648 =implode(',', $m4is_p65 );
 $m4is_v2613 ="SELECT `id`, `value` FROM %i WHERE `id` IN ({$m4is_h5648
}) AND `appname` = %s AND `fieldname` = 'LastUpdated' ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_p0872, self::$m4is_r9613 );
 $m4is_z9157 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 foreach($m4is_c1796 as $m4is_l9671 =>$m4is_i935 ){
$m4is_h21895 =$m4is_i935['Id'];
 $m4is_i09 =$m4is_i935['LastUpdated'];
 $m4is_w06419 =$m4is_z9157[$m4is_h21895]->value;
 if($m4is_w06419 >= $m4is_i09 ){
unset($m4is_c1796[$m4is_l9671]);
 
}
}foreach($m4is_c1796 as $m4is_i935 ){
self::$m4is_r1546->m4is_v1694($m4is_i935 );
 if(!empty($m4is_i935['LastUpdated'])){
$m4is_l91 =date('Y-m-d H:i:s', strtotime($m4is_i935['LastUpdated']));
 
}
}
}if($m4is_h973 < $m4is_c92430 ){
$m4is_l91 ='2000-01-01 00:00:00';
 
}update_option($m4is_b06, $m4is_l91, true );
 $m4is_c28 =sprintf('Background Contact Sync Ran: %d contacts synced from %s.', $m4is_h973, $m4is_u68057 );
 m4is_q62395::m4is_v729(0, 'cron', $m4is_c28 );
 update_option($m4is_s92076, 0, true );
 
} public static 
function m4is_r804(string $m4is_f4930 ): int {
$m4is_f4930 =strtolower(trim($m4is_f4930 ));
 if(empty($m4is_f4930 )){
return 0;
 
}$m4is_u21 =(bool) self::$m4is_r1546->m4is_j498('settings', 'sync_tag_details' );
 $m4is_e80 ='Contact';
 $m4is_c92430 =1;
 $m4is_d3012 =0;
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, true );
 $m4is_m7426 =m4is_p40::m4is_o1723();
 $m4is_e69637 =0;
 $contact_count =0;
 $contact_ids =[];
 $contacts =[];
 $m4is_v76912 =['Email' =>$m4is_f4930 ];
 $m4is_c1796 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', true );
 $m4is_i935 =empty($m4is_c1796[0])? []: $m4is_c1796[0];
 $m4is_h21895 =empty($m4is_i935['Id'])? 0 : (int) $m4is_i935['Id'];
 if(empty($m4is_i935 )||empty($m4is_h21895 )){
return 0;
 
}self::$m4is_r1546->m4is_v1694($m4is_i935 );
 return $m4is_i935['Id'];
 
}      public static 
function m4is_a56278(?string $m4is_f4930 ): int {
global $wpdb;
 $m4is_p4935 ='Email';
  $m4is_v2613 ="SELECT COUNT( `id` ) FROM %i WHERE `appname` = %s AND `fieldname` = %s AND `value` = %s ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_p0872, self::$m4is_r9613, $m4is_p4935, $m4is_f4930 );
 $m4is_u6591 =(int) $wpdb->get_var($m4is_v2613 );
 return $m4is_u6591;
 
} public static 
function m4is_l0937(?array $m4is_v30712 =[]){
global $wpdb;
 $m4is_v2613 ="SELECT `id`, `value` FROM %i WHERE `appname` = %s AND `fieldname` = 'Groups'";
 if(!empty($m4is_v30712 )){
$m4is_v2613 .= " AND `id` IN ('%s')";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_p0872, self::$m4is_r9613, implode(',', $m4is_v30712 ));
 
}else{
$m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_p0872, self::$m4is_r9613 );
 
}return $wpdb->get_results($m4is_v2613, OBJECT_K );
 
} public static 
function m4is_e82065(string $m4is_f4930 ): array {
global $wpdb;
 $m4is_v2613 ="SELECT `id` FROM %i WHERE `fieldname` == 'Email' = %s AND `appname` = %s ORDER BY `id` ASC";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_o1723(), $m4is_f4930, self::$m4is_r9613 );
 $m4is_i935 =$wpdb->get_var($m4is_v2613 );
 return (array)$m4is_i935;
 
} public static 
function m4is_z62(?int $m4is_h21895, ?bool $m4is_f475 =false ){
global $wpdb;
 $m4is_v2613 ="DELETE FROM %i WHERE `id` = %d AND `appname` = %s";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_p0872, $m4is_h21895, self::$m4is_r9613 );
 $wpdb->query($m4is_v2613 );
 if($m4is_h21895 ){
self::$m4is_r1546->m4is_i12($m4is_h21895 );
 if($m4is_f475 ){
self::m4is_l921($m4is_h21895 );
 $m4is_t6601 =m4is_z13097::m4is_y876($m4is_h21895 );
 m4is_z13097::m4is_c90626($m4is_t6601 );
 
}
}self::$m4is_r1546->m4is_s64809($m4is_h21895 );
 
} private static 
function m4is_l921(int $m4is_h21895 ){
global $wpdb;
 $m4is_v2613 ="DELETE FROM %i WHERE `contactid` = %d AND `appname` = %s";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_k865::m4is_o39864(), $m4is_h21895, self::$m4is_r9613 );
 $wpdb->query($m4is_v2613 );
 self::$m4is_r1546->m4is_s64809($m4is_h21895 );
 
} static 
function m4is_x6560(?int $m4is_h21895, ?array $m4is_a89 =[], ?bool $m4is_f1926 =false ){
$m4is_h21895 =(int) $m4is_h21895;
 if($m4is_h21895 ){
self::$m4is_z59682->updatecon($m4is_h21895, $m4is_a89 );
 if($m4is_f1926 ){
self::$m4is_r1546->m4is_x4831($m4is_h21895 );
 
}
}self::$m4is_r1546->m4is_s64809($m4is_h21895 );
 return $m4is_h21895;
 
} static 
function m4is_y935(?string $m4is_f4930 ='', ?string $m4is_e70689 ='Memberium'){
if(!empty($m4is_f4930 )){
self::$m4is_z59682->optin($m4is_f4930, $m4is_e70689);
 
}
} static 
function m4is_k82670(?array $m4is_i935, ?string $m4is_d039 ='Email' ): int {
return (int) self::$m4is_z59682->addWithDupCheck($m4is_i935, $m4is_d039 );
 
} static 
function m4is_t609(?int $m4is_h21895, ?array $m4is_a89 =[]){
if(empty($m4is_a89 )){
$m4is_a89 =m4is_c69807::m4is_f5248('Contact', true );
 
}return self::$m4is_z59682->loadCon($m4is_h21895, $m4is_a89 );
 
} static 
function m4is_o104(?string $m4is_f4930 ='', ?array $m4is_a89 =[]){
if(empty ($m4is_a89 )){
$m4is_a89 =m4is_c69807::m4is_f5248('Contact', true );
 
}return self::$m4is_z59682->findByEmail($m4is_f4930, $m4is_a89 );
 
} static 
function m4is_x4831(int $m4is_h21895 ){
$m4is_i935 =self::m4is_t609($m4is_h21895 );
 if(is_array($m4is_i935 )){
self::$m4is_r1546->m4is_v1694($m4is_i935 );
 
}return $m4is_i935;
 
}      static 
function m4is_p67(?int $m4is_h21895, ?bool $m4is_r896 =false, $m4is_l669 =false ): array {
global $wpdb;
 $m4is_r637 =$m4is_r896 ? 'LOWER(`fieldname`) as `fieldname`' : '`fieldname`';
 $m4is_v2613 ="SELECT {$m4is_r637
}, `value` FROM %i WHERE `id` = %d AND `appname` = %s";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_p0872, $m4is_h21895, self::$m4is_r9613 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 $m4is_i935 =[];
 if(is_array($m4is_m615 )){
foreach ($m4is_m615 as $m4is_d07693 =>$m4is_g91703 ){
$m4is_i935[$m4is_g91703['fieldname']]=$m4is_g91703['value'];
 
}
}if(!$m4is_l669 ){
$m4is_i935 =apply_filters('memberium_contact_load', $m4is_i935 );
 
}return $m4is_i935;
 
} public static 
function m4is_o8403(){
$m4is_q1470 ='ignore_contact_fields';
 $m4is_j0361 ='Contact';
 $m4is_w96028 =m4is_c69807::m4is_f5248($m4is_j0361, false );
 $m4is_v45136 =self::$m4is_r1546->m4is_j498('settings', $m4is_q1470, '' );
 $m4is_v45136 =is_string($m4is_v45136 )? $m4is_v45136 : '';
 $m4is_v45136 =array_filter(explode(',', $m4is_v45136 ));
 $m4is_v45136 =array_intersect($m4is_w96028, $m4is_v45136 );
 $m4is_v45136 =implode(',', $m4is_v45136 );
 self::$m4is_r1546->m4is_d64918($m4is_v45136, 'settings', $m4is_q1470 );
 
}      public static 
function m4is_u650(string $m4is_f4930 ): stdClass {
$m4is_i935 =self::$m4is_e6426->elf_list_contacts(['email' =>$m4is_f4930]);
 if(isset($m4is_i935->code )&&$m4is_i935->code == 404 ){
$m4is_v30712 =(array)self::m4is_e82065($m4is_f4930 );
 foreach($m4is_v30712 as $m4is_h21895 ){
self::m4is_z62((int) $m4is_h21895, true );
 
}
}return $m4is_i935;
 
} public static 
function m4is_g6357(int $m4is_h21895 ): stdClass {
if(empty($m4is_h21895 )){
return new stdclass();
 
}$m4is_i935 =self::$m4is_e6426->elf_retrieve_contact($m4is_h21895 );
 if(isset($m4is_i935->code )&&$m4is_i935->code == 404 ){
self::m4is_z62($m4is_h21895, true );
 
}return $m4is_i935;
 
} public static 
function m4is_q96120(stdClass $m4is_i935 ): array {
static $m4is_l66;
 if(isset($m4is_i935->code )&&$m4is_i935->code == 404 ){
return [];
 
}$m4is_i592 =[];
 $m4is_p496 =['anniversary_date' =>'Anniversary', 'birth_date' =>'BirthDay', 'contact_type' =>'ContactType', 'create_time' =>'DateCreated', 'family_name' =>'LastName', 'given_name' =>'FirstName', 'id' =>'Id', 'job_title' =>'JobTitle', 'leadsource_id' =>'LeadSourceId', 'middle_name' =>'MiddleName', 'notes' =>'ContactNotes', 'owner_id' =>'OwnerID', 'preferred_locale' =>'Language', 'preferred_name' =>'Nickname', 'referral_code' =>'ReferralCode', 'spouse_name' =>'SpouseName', 'suffix' =>'Suffix', 'time_zone' =>'TimeZone', 'update_time' =>'LastUpdated', 'website' =>'Website', ];
 foreach($m4is_p496 as $m4is_x832 =>$m4is_x38 ){
if(isset($m4is_i935->$m4is_x832 )){
$m4is_i592['contact'][$m4is_x38]=$m4is_i935->$m4is_x832;
 
}
} if(!empty($m4is_i935->tag_ids )){
$m4is_i592['contact']['Groups']=implode(',', $m4is_i935->tag_ids );
 
} if(!empty($m4is_i935->email_addresses )){
$m4is_i962 =['EMAIL1' =>'Email', 'EMAIL2' =>'EmailAddress2', 'EMAIL3' =>'EmailAddress3', ];
 foreach ($m4is_i935->email_addresses as $m4is_p64 ){
$m4is_r637 =$m4is_i962[$m4is_p64->field];
 $m4is_i592['contact'][$m4is_r637]=$m4is_p64->email;
 
}
} if(!empty($m4is_i935->custom_fields )){
$m4is_l66 ??= m4is_s695::m4is_c63758('contact' );
 foreach($m4is_i935->custom_fields as $m4is_b64329 ){
$m4is_r637 ='_' . $m4is_l66[$m4is_b64329->id]->name ?? '';
 if(!empty($m4is_r637 )){
$m4is_i592['contact'][$m4is_r637]=$m4is_b64329->content;
 
}
}
}     return $m4is_i592;
 
}
}

