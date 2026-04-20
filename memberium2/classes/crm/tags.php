<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_k865 {
private static array $m4is_t51784;
 private static int $m4is_z98;
 private static object $m4is_z59682;
 private static string $m4is_r9613;
 private static string $m4is_u6095;
 private static string $m4is_x92686;
 private static string $m4is_j49501;
 private static object $m4is_r1546;
 private static object $m4is_e6426;
 private const TAG_CACHE_GROUP ='memberium/keap/tags';
  public static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_e6426 =self::$m4is_r1546->m4is_z40()->elf_rest();
 self::$m4is_j49501 ='memberium_tags';
  self::$m4is_u6095 ='ContactGroup';
  self::$m4is_z98 =1000;
 self::$m4is_x92686 ='memberium_contacttags';
 self::$m4is_t51784 =[];
 
} private 
function __construct(){

}    public static 
function m4is_k91873(): string {
return self::$m4is_j49501;
 
} public static 
function m4is_e26587(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::$m4is_j49501;
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) NOT NULL, \n" . "appname varchar(32) NOT NULL, \n" . "name varchar(255) default NULL, \n" . "category int(20) default NULL, \n" . "KEY id (id), \n" . "PRIMARY KEY  (appname,id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
} static 
function m4is_o39864(): string {
return self::$m4is_x92686;
 
} public static 
function m4is_t81(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_o39864();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) NOT NULL AUTO_INCREMENT, \n" . "appname varchar(32) NOT NULL, \n" . "contactid int(20) NOT NULL, \n" . "tagid int(20) NOT NULL, \n" . "created timestamp, \n" . "KEY id (id), \n" . "KEY appname (appname), \n" . "KEY contactid (contactid), \n" . "KEY tagid (tagid), \n" . "PRIMARY KEY  (id,appname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
} public static 
function m4is_i28(int $m4is_h21895, array $m4is_s15437 ): array {
if(empty($m4is_h21895 )||empty($m4is_s15437 )){
return [];
 
}$m4is_i23845 =self::$m4is_e6426->elf_group_batch($m4is_s15437 );
 $m4is_d5472 =['SUCCESS' =>[], 'FAILURE' =>[]];
 foreach($m4is_i23845 as $m4is_l9321 ){
$m4is_n548 =self::$m4is_e6426->m4is_e1258(1, "contacts/{$m4is_h21895
}/tags", ['tagIds' =>$m4is_l9321]);
 if(is_wp_error($m4is_n548 )||!is_object($m4is_n548 )||!empty($m4is_n548->message )){
$m4is_d5472['FAILURE']=array_merge($m4is_d5472['FAILURE'], $m4is_s15437 );
 
}else{
foreach ($m4is_n548 as $tag_id =>$m4is_u6591 ){
$m4is_u6591 =$m4is_u6591 !== 'SUCCESS' ? 'FAILURE' : 'SUCCESS';
 $m4is_d5472[$m4is_u6591][]=$tag_id;
 
}
}
}return $m4is_d5472;
 
} public static 
function m4is_n14(int $m4is_h21895, array $m4is_s15437 ): array {
if(empty($m4is_h21895 )||empty($m4is_s15437 )){
return [];
 
}$m4is_i23845 =self::$m4is_e6426->elf_group_batch($m4is_s15437 );
 $m4is_d5472 =['SUCCESS' =>[], 'FAILURE' =>[]];
 foreach ($m4is_i23845 as $m4is_l9321 ){
$m4is_s15437 =implode(',', $m4is_l9321 );
 $m4is_u86104 =self::$m4is_e6426->m4is_d869(1, "contacts/{$m4is_h21895
}/tags?ids={$m4is_s15437
}" );
 $result =is_wp_error($m4is_u86104 )? 'FAILURE' : 'SUCCESS';
 foreach ($m4is_l9321 as $m4is_d913){
$m4is_d5472[$result][]=$m4is_d913;
 
}
}return $m4is_d5472;
 
} public static 
function m4is_v3784(): int {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT count(`id`) from %i WHERE `appname` = %s", self::$m4is_j49501, self::$m4is_r9613 );
 $m4is_h973 =$wpdb->get_var($m4is_v2613 );
 return (int) $m4is_h973;
 
} static 
function m4is_q136(): int {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT count(`id`) from %i WHERE `appname` = %s", self::$m4is_j49501, self::$m4is_r9613 );
 $m4is_h973 =$wpdb->get_var($m4is_v2613 );
 return (int) $m4is_h973;
 
} static 
function m4is_y19240(array $m4is_s15437 ): array {
global $wpdb;
 $m4is_s15437 =array_filter(array_map('intval', $m4is_s15437 ));
 if(empty($m4is_s15437 )){
return [];
 
}$m4is_y463 =implode(',', $m4is_s15437 );
 $m4is_v2613 ="SELECT DISTINCT(`category`) FROM %i WHERE `appname` = %s AND `id` IN ( {$m4is_y463
} )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_j49501, self::$m4is_r9613 );
 $m4is_x69523 =$wpdb->get_col($m4is_v2613 );
 return $m4is_x69523;
 
} public static 
function m4is_f348(string $m4is_n23617, int $m4is_w66 =0, ?string $m4is_d3069 =null ): int {
global $wpdb;
 $m4is_n23617 =trim($m4is_n23617 );
 if(empty($m4is_n23617 )){
return 0;
 
}$m4is_k95702 =m4is_z6894::m4is_u43();
 if(!in_array($m4is_w66, $m4is_k95702 )){
return 0;
 
}if(is_null($m4is_d3069 )){
$m4is_l17096 =wp_get_current_user();
 $m4is_y193 =is_a($m4is_l17096, 'WP_User' )? $m4is_l17096->user_login : 'System';
 $m4is_d3069 =sprintf("Created by %s using Memberium on %s", $m4is_y193, date('Y-m-d h:i:s' ));
 
}$m4is_q76340 =['GroupCategoryId' =>$m4is_w66, 'GroupDescription' =>$m4is_d3069, 'GroupName' =>$m4is_n23617 ];
 $m4is_d913 =m4is_c69807::m4is_y7501(self::$m4is_u6095, $m4is_q76340 );
 if($m4is_d913 > 0 ){
$m4is_v2613 ='INSERT INTO %i (`id`, `appname`, `name`, `category`) VALUES (%d, %s, %s, %s);';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_j49501, $m4is_d913, self::$m4is_r9613, $m4is_n23617, $m4is_w66 );
 $wpdb->query($m4is_v2613);
 if(wp_cache_supports('flush_group' )){
wp_cache_flush_group(self::TAG_CACHE_GROUP );
 
}return $m4is_d913;
 
}return 0;
 
} public static 
function m4is_o172(?string $m4is_o18692 ='' ): array {
global $wpdb;
 $m4is_q78234 ="%{$m4is_o18692
}%" ;
 $m4is_u9381 =$m4is_o18692 ? $wpdb->prepare(" AND ( `name` LIKE %s OR `id` LIKE %s ) ", $m4is_q78234, $m4is_q78234 ): '';
 $m4is_v2613 =sprintf("SELECT `id`, `name` FROM `%s` WHERE `appname` = '%s' %s ORDER BY `name` ASC ", self::$m4is_j49501, self::$m4is_r9613, $m4is_u9381 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 return (array)$m4is_m615;
 
} public static 
function m4is_c743(array $m4is_p318 ): array {
global $wpdb;
 static $m4is_x16420 =[];
 $m4is_p318 =array_unique(array_filter($m4is_p318 ));
 if(empty($m4is_p318 )){
return [];
 
}$m4is_p65 =[];
 $m4is_e1697 =[];
 foreach($m4is_p318 as $m4is_n23617 ){
if(is_numeric($m4is_n23617 )){
$m4is_p65[]=(int) $m4is_n23617;
 
}else{
$m4is_e1697[]=strtolower(trim($m4is_n23617 ));
 
}
}if(empty($m4is_e1697 )){
return array_filter($m4is_p65 );
 
}$m4is_x475 =[];
 $m4is_o21 =[];
 foreach($m4is_e1697 as $m4is_z18027 ){
if(substr($m4is_z18027, 0, 1 )=== '-' ){
$m4is_o21[]=substr($m4is_z18027, 1 );
 
}else{
$m4is_x475[]=$m4is_z18027;
 
}
}foreach($m4is_x475 as $m4is_l9671 =>$m4is_n23617 ){
if(array_key_exists($m4is_n23617, $m4is_x16420 )){
$m4is_p65[]=(int) $m4is_x16420[$m4is_n23617];
 unset($m4is_x475[$m4is_l9671]);
 
}
}foreach($m4is_o21 as $m4is_l9671 =>$m4is_n23617 ){
if(array_key_exists($m4is_n23617, $m4is_x16420 )){
$m4is_p65[]=(0 - (int) $m4is_x16420[$m4is_n23617]);
 unset($m4is_o21[$m4is_l9671]);
 
}
}if(empty($m4is_x475 )&&empty($m4is_o21 )){
return array_filter($m4is_p65 );
 
}$m4is_l7639 =array_unique(array_merge($m4is_x475, $m4is_o21 ));
 $m4is_z34951 =implode(', ', array_fill(0, count($m4is_l7639 ), '%s' ));
 $m4is_v2613 ="SELECT LOWER(`name`) as `name`, `id` FROM %i WHERE `appname` = %s AND `name` IN ( {$m4is_z34951
} )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_k91873(), self::$m4is_r9613, ...$m4is_l7639 );
 $m4is_l9321 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 foreach($m4is_l9321 as $m4is_p786 ){
$m4is_x16420[$m4is_p786->name]=$m4is_p786->id;
 
}foreach($m4is_x475 as $m4is_l9671 =>$m4is_n23617 ){
if(array_key_exists($m4is_n23617, $m4is_x16420 )){
$m4is_p65[]=(int) $m4is_x16420[$m4is_n23617];
 unset($m4is_x475[$m4is_l9671]);
 
}
}foreach($m4is_o21 as $m4is_l9671 =>$m4is_n23617 ){
if(array_key_exists($m4is_n23617, $m4is_x16420 )){
$m4is_p65[]=(0 - (int) $m4is_x16420[$m4is_n23617]);
 unset($m4is_o21[$m4is_l9671]);
 
}
}return array_filter($m4is_p65 );
 
} public static 
function m4is_k29064(array $m4is_s15437, bool $m4is_p65 =false ): array {
global $wpdb;
 static $m4is_y6710 =[];
 $m4is_s15437 =array_unique(array_filter(array_map('intval', $m4is_s15437 )));
 if(empty($m4is_s15437 )){
return [];
 
}$m4is_n6652 =[];
 foreach($m4is_s15437 as $m4is_d913 ){
if($m4is_d913 < 0 ){
$m4is_y6710[0 - $m4is_d913]=true;
 
}else{
$m4is_n6652[]=$m4is_d913;
 
}
}if(empty($m4is_n6652 )){
return [];
 
}$m4is_z34951 =implode(', ', array_fill(0, count($m4is_n6652 ), '%d' ));
 $m4is_v2613 ="SELECT `id`, `name` FROM %i WHERE `appname` = %s AND `id` IN ( {$m4is_z34951
} )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_k91873(), self::$m4is_r9613, ...$m4is_n6652 );
 $m4is_l9321 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 $m4is_p318 =[];
 foreach($m4is_l9321 as $m4is_p786 ){
if($m4is_p65 ){
$m4is_p318[$m4is_p786->id]=$m4is_p786->name . ' (' . $m4is_p786->id . ')';
 
}else{
$m4is_p318[$m4is_p786->id]=$m4is_p786->name;
 
}
}return $m4is_p318;
 
} public static 
function m4is_q05762(string $m4is_k52736 ): bool {
global $wpdb;
 $m4is_k52736 =trim($m4is_k52736 );
 $m4is_v2613 =$wpdb->prepare("SELECT count(*) FROM %i WHERE appname = %s AND name = %s", self::$m4is_j49501, self::$m4is_r9613, $m4is_k52736 );
 $m4is_h973 =$wpdb->get_var($m4is_v2613 );
 return (bool) $m4is_h973;
 
} public static 
function m4is_f24(bool $m4is_e952 =false ): array {
global $wpdb;
  $m4is_v2613 =$wpdb->prepare("SELECT `name`, `id` FROM %i WHERE `appname` = %s ORDER BY `name`", self::$m4is_j49501, self::$m4is_r9613 );
 $m4is_l9321 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 $m4is_p47016 =[];
 if(is_array($m4is_l9321 )){
foreach($m4is_l9321 as $m4is_p786){
$m4is_p47016[]=sprintf("(%d) %s", $m4is_p786['id'], $m4is_p786['name']);
 if($m4is_e952){
$m4is_p47016[]=sprintf("(-%d) %s", $m4is_p786['id'], $m4is_p786['name']);
 
}
}
}return $m4is_p47016;
 
} public static 
function m4is_y6189(string $m4is_n23617 ): int {
global $wpdb;
 static $m4is_j857 =[];
 $m4is_n23617 =strtolower(trim($m4is_n23617 ));
 if(empty ($m4is_n23617 )){
return 0;
 
}if(is_numeric($m4is_n23617 )){
return (int) $m4is_n23617;
 
}if(array_key_exists($m4is_n23617, $m4is_j857 )){
return (int) $m4is_j857[$m4is_n23617];
 
}$m4is_v2613 ='SELECT `id` FROM %i WHERE `appname` = %s AND `name` = %s ORDER BY `id` LIMIT 1';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_k91873(), self::$m4is_r9613, $m4is_n23617 );
 $m4is_d913 =(int) $wpdb->get_var($m4is_v2613);
 if($m4is_d913 ){
$m4is_j857[$m4is_n23617]=$m4is_d913;
 
}return $m4is_d913;
 
} public static 
function m4is_z2906($m4is_k98670 =false, $m4is_u3028 =false ){
global $wpdb;
 $m4is_s82753 =is_admin()? 1 : 900;
 $m4is_z86 =self::$m4is_r1546->m4is_j498('settings', 'ignore_tag_categories' );
 $m4is_u6591 =[];
 $m4is_u6591['lc']=[];
 $m4is_u6591['mc']=[];
  if($m4is_k98670 == true &&$m4is_z86 > '' ){
$m4is_u630 =sprintf(" AND category NOT IN ( %s ) ", $m4is_z86 );
 
}else{
$m4is_u630 ='';
 
}$m4is_v2613 =$wpdb->prepare("SELECT `id`, `name` FROM %i WHERE `appname` = %s ", self::$m4is_j49501, self::$m4is_r9613 ). " {$m4is_u630
} ORDER BY `category`, `name`";
 $m4is_q1046 =sha1($m4is_v2613 );
 $m4is_t265 =false;
 $m4is_d5472 =[];
 $m4is_l9321 =[];
 if(!is_admin()){
$m4is_d5472 =wp_cache_get($m4is_q1046, self::TAG_CACHE_GROUP, false, $m4is_t265 );
 
}if(!$m4is_t265 ||!is_array($m4is_d5472 )){
$m4is_d5472 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 wp_cache_set($m4is_q1046, $m4is_d5472, self::TAG_CACHE_GROUP, $m4is_s82753 );
 
}foreach ($m4is_d5472 as $m4is_p786 ){
$m4is_l9321['mc'][$m4is_p786['id']]=$m4is_p786['name'];
 $m4is_l9321['lc'][$m4is_p786['id']]=strtolower($m4is_p786['name']);
 
}unset($m4is_d5472 );
  if($m4is_u3028 ){
if(!empty($m4is_l9321['mc'])&&is_array($m4is_l9321['mc'])){
$m4is_l9321['hn']=[];
 $m4is_n6689 =__('Does Not Have', 'memberium');
 foreach ($m4is_l9321['mc']as $m4is_d07693 =>$m4is_p786 ){
$m4is_l9321['hn'][$m4is_d07693]=$m4is_p786;
 $m4is_l9321['hn']['-' . $m4is_d07693]=$m4is_n6689 . ' ' . $m4is_p786;
 
}
}
}return (array)$m4is_l9321;
 
} public static 
function m4is_b94($m4is_n246, $m4is_c48062 ): array {
$m4is_h248 =[];
 $m4is_e952 =$m4is_c48062 === 'oxygen' ? false : true;
 $m4is_l9321 =m4is_k865::m4is_z2906(true, $m4is_e952 );
 $i =$m4is_e952 ? 'hn' : 'mc';
 $m4is_l9321 =isset($m4is_l9321[$i])? $m4is_l9321[$i]: false;
 if($m4is_l9321 ){
foreach ($m4is_l9321 as $m4is_d07693 =>$m4is_p786 ){
$m4is_h248[$m4is_d07693]=$m4is_p786 . ' (' . str_replace('-', '', $m4is_d07693 ). ')';
 
}
}return $m4is_h248;
 
}    public static 
function m4is_l26(){
global $wpdb;
 if(wp_cache_supports('flush_group' )){
wp_cache_flush_group(self::TAG_CACHE_GROUP );
 
}$m4is_y67654 =m4is_z6894::m4is_j6724();
 $m4is_r816 =m4is_z6894::m4is_u43(true, true );
 $m4is_e678 =count($m4is_r816 );
 $m4is_d3012 =0;
 $m4is_m7426 =m4is_k865::m4is_k91873();
 $m4is_b75 =0;
 $m4is_u450 =[];
 $m4is_h3647 =['Id', 'GroupName', 'GroupCategoryId' ];
 $m4is_v76912 =['Id' =>'%', ];
 if($m4is_e678 == 1){
$m4is_v76912 =['GroupCategoryId' =>$m4is_r816[0], ];
 
} do {
$m4is_l9321 =self::$m4is_z59682->dsQueryOrderBy(self::$m4is_u6095, self::$m4is_z98, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', true );
 $m4is_c7260 =is_array($m4is_l9321 )? count($m4is_l9321 ): 0;
 if(!is_array($m4is_l9321 )){
error_log(sprintf("Memberium: [error] Failed to retrieve tags from the CRM system.  Query returned '%s'", (string) $m4is_l9321 ));
 
}if($m4is_c7260 ){
$m4is_i760 =[];
 foreach ($m4is_l9321 as $m4is_p786 ){
$m4is_p786['GroupName']=isset($m4is_p786['GroupName'])? $m4is_p786['GroupName']: '';
 if($m4is_e678 == 1 ||(in_array($m4is_p786['GroupCategoryId'], $m4is_r816 ))){
$m4is_i760[]=$wpdb->prepare('( %d, %s, %s, %d )', (int) $m4is_p786['Id'], self::$m4is_r9613, $m4is_p786['GroupName'], (int) $m4is_p786['GroupCategoryId']);
 $m4is_u450[]=(int) $m4is_p786['Id'];
 
}
}if(count($m4is_i760 )){
$m4is_v2613 ="INSERT INTO `{$m4is_m7426
}` (id, appname, name, category ) VALUES " . implode(', ', $m4is_i760 ). " ON DUPLICATE KEY UPDATE id=VALUES(id), appname=VALUES(appname), name=VALUES(name), category=VALUES(category)";
 $wpdb->query($m4is_v2613 );
 
}$m4is_d3012++;
 
}
}while ($m4is_c7260 == self::$m4is_z98 );
 if(count($m4is_u450 )){
$m4is_v2613 ="DELETE FROM {$m4is_m7426
} WHERE `appname` = %s AND `id` NOT IN ( " . implode(', ', $m4is_u450). " )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_r9613 );
 $wpdb->query($m4is_v2613 );
 
}$m4is_c1869 =get_option('memberium_tables_updated', []);
 $m4is_c1869['tags']=isset($m4is_c1869['tags'])? $m4is_c1869['tags']: 0;
 $m4is_c1869['tags']=time();
 if(wp_cache_supports('flush_group' )){
wp_cache_flush_group(self::TAG_CACHE_GROUP );
 
}set_transient('memberium_tags_updated', time());
 update_option('memberium_tables_updated', $m4is_c1869, false );
 return $m4is_b75;
 
} private static 
function m4is_s7214(bool $m4is_f465 =false ): void {
$m4is_i13 ='memberium/keap/tags/next_sync';
 if(!$m4is_f465 ){
$m4is_d489 =get_option($m4is_i13, 0 );
 if($m4is_d489 > time()){
return;
 
}
}$m4is_b5166 ='memberium/keap/tags/highest_sync_id';
 $m4is_z28 =6 * HOUR_IN_SECONDS;
 $m4is_m41860 =5 * MINUTE_IN_SECONDS;
 $m4is_a21 =(int) get_option($m4is_b5166, 0 );
 $m4is_h3647 =m4is_c69807::m4is_f5248(self::$m4is_u6095, true );
 $m4is_d3012 =0;
 $m4is_v76912 =['Id' =>"~>~ {$m4is_a21
}", ];
 $m4is_l9321 =self::$m4is_z59682->dsQueryOrderBy(self::$m4is_u6095, self::$m4is_z98, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', true);
 if(is_string($m4is_l9321 )){
error_log(sprintf("Memberium: [error] Failed to retrieve tags from the CRM system.  Query returned '%s'", $m4is_l9321 ));
 return;
 
}if(!is_array($m4is_l9321 )){
return;
 
}else{
$m4is_c7260 =count($m4is_l9321 );
 $m4is_z83 =end($m4is_l9321 );
 $m4is_o03 =(int) end($m4is_l9321 )['Id'];
 $m4is_y8656 =self::m4is_l6846($m4is_a21, $m4is_o03 );
 $m4is_l865 =self::m4is_q79045($m4is_l9321 );
 $m4is_u3092 =array_diff($m4is_y8656, $m4is_l865 );
 if($m4is_c7260 < self::$m4is_z98 ){
$m4is_o03 =0;
 $m4is_x19320 =$m4is_z28;
 
}else{
$m4is_x19320 =$m4is_m41860;
 
}self::m4is_o8064($m4is_a21, $m4is_o03, $m4is_u3092 );
 update_option($m4is_b5166, $m4is_o03, false );
 update_option($m4is_i13, time()+ $m4is_x19320, false );
 if(wp_cache_supports('flush_group' )){
wp_cache_flush_group(self::TAG_CACHE_GROUP );
 
}
}
}    private static 
function m4is_q79045(array $m4is_l9321 ): array {
global $wpdb;
 $m4is_i760 =[];
 $m4is_z160 =[];
 foreach ($m4is_l9321 as $m4is_p786 ){
$m4is_p786['GroupName']=isset($m4is_p786['GroupName'])? $m4is_p786['GroupName']: '';
 $m4is_i760[]=$wpdb->prepare('( %d, %s, %s, %d )', (int) $m4is_p786['Id'], self::$m4is_r9613, $m4is_p786['GroupName'], (int) $m4is_p786['GroupCategoryId']);
 $m4is_z160[]=(int) $m4is_p786['Id'];
 
}if(!empty($m4is_i760 )){
$m4is_v2613 =$wpdb->prepare("INSERT INTO %i (id, appname, name, category ) VALUES ", self::$m4is_j49501 ). implode(', ', $m4is_i760 ). " ON DUPLICATE KEY UPDATE id=VALUES(id), appname=VALUES(appname), name=VALUES(name), category=VALUES(category)";
 $wpdb->query($m4is_v2613 );
 
}return $m4is_z160;
 
} private static 
function m4is_l6846(int $m4is_x74, int $m4is_p48691 ): array {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT `id` FROM %i WHERE `appname` = %s AND `id` BETWEEN %d AND %d", self::$m4is_j49501, self::$m4is_r9613, $m4is_x74, $m4is_p48691 );
 $m4is_p65 =$wpdb->get_col($m4is_v2613 );
 return $m4is_p65;
 
} private static 
function m4is_o8064(int $m4is_x74, int $m4is_p48691, array $m4is_u3092 ): void {
global $wpdb;
 if(empty($m4is_u3092 )){
return;
 
}$m4is_d579 =implode(', ', $m4is_u3092 );
 $m4is_v2613 ="DELETE FROM %i WHERE `appname` = %s AND `id` IN ( {$m4is_d579
} )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_j49501, self::$m4is_r9613, $m4is_x74, $m4is_p48691 );
 $wpdb->query($m4is_v2613 );
 echo '<p>', $m4is_v2613, '</p>';
 
}    public static 
function m4is_h894(int $m4is_h21895 ): void {
global $wpdb;
 if(!$m4is_h21895 ){
return;
 
}$m4is_l07426 =self::m4is_o39864();
 $m4is_c92430 =self::$m4is_z98;
 $m4is_u6095 ='ContactGroupAssign';
 $m4is_d3012 =0;
 $m4is_e69637 =0;
 $m4is_z051 ='ContactId';
 $m4is_e207 =$m4is_h21895;
 $m4is_h3647 =['GroupId', 'DateCreated' ];
 $wpdb->delete($m4is_l07426, ['contactid' =>$m4is_h21895, 'appname' =>self::$m4is_r9613 ]);
 do {
$m4is_m615 =self::$m4is_z59682->dsfind($m4is_u6095, (int) $m4is_c92430, (int) $m4is_d3012, $m4is_z051, (string) $m4is_e207, $m4is_h3647 );
 $m4is_h973 =is_array($m4is_m615 )? count($m4is_m615 ): 0;
 if($m4is_h973 ){
$m4is_x39 =[];
 $m4is_z34951 =[];
  foreach ($m4is_m615 as $m4is_g91703){
$m4is_e38261 =(int) strtotime($m4is_g91703['DateCreated']);
 $m4is_x39[]=self::$m4is_r9613;
 $m4is_x39[]=$m4is_h21895;
 $m4is_x39[]=(int) $m4is_g91703['GroupId'];
 $m4is_x39[]=date('Y-m-d H:i:s', $m4is_e38261 );
  $m4is_z34951[]="( %s, %d, %d, %s ) ";
 
} $m4is_v2613 ="INSERT INTO `{$m4is_l07426
}` (appname, contactid, tagid, created) VALUES " . implode(', ', $m4is_z34951 );
  $wpdb->query($wpdb->prepare($m4is_v2613, ...$m4is_x39 ));
 
}
}while ($m4is_h973 == $m4is_c92430 );
 $m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 m4is_q82::m4is_u687($m4is_f087 );
 
}
}

