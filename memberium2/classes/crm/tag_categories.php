<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83')||die();
 m4is_z6894::m4is_h269();
  final 
class m4is_z6894 {
private static object $m4is_r1546;
 private static object $m4is_z59682;
 private static string $m4is_r9613;
 private static string $m4is_v40368;
 private const PAGE_SIZE =1000;
  static 
function m4is_h269(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_v40368 ='memberium_contactgroupcategories';
 
} private 
function __construct(){
 
}    private static 
function m4is_s3208(): string {
return self::$m4is_v40368;
 
} public static 
function m4is_r19(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_s3208();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(11) NOT NULL, \n" . "appname varchar(32) NOT NULL, \n" . "name varchar(64) NOT NULL, \n" . "KEY id (id), \n" . "PRIMARY KEY  (appname,id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}    public static 
function m4is_o53614(): int {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT count(id) from %i WHERE `appname` = %s", self::m4is_s3208(), self::$m4is_r9613 );
 $m4is_x69523 =$wpdb->get_var($m4is_v2613 );
 return (int) $m4is_x69523;
 
} private static 
function m4is_h510(string $m4is_k52736 ): bool {
global $wpdb;
 $m4is_k52736 =trim($m4is_k52736 );
 $m4is_v2613 ="SELECT count(*) FROM %i WHERE appname = %s AND name = %s";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_s3208(), self::$m4is_r9613, $m4is_k52736 );
 $m4is_h973 =$wpdb->get_var($m4is_v2613 );
 return (bool) $m4is_h973;
 
} public static 
function m4is_j6724(): array {
$m4is_l53261 =array_filter(explode(',', self::$m4is_r1546->m4is_j498('settings', 'ignore_tag_categories', '' )), 'is_numeric' );
 return $m4is_l53261;
 
} public static 
function m4is_u43(bool $m4is_v15639 =true, bool $m4is_p02 =true ): array {
global $wpdb;
 $m4is_l53261 =[];
 $m4is_v2613 ="SELECT `id` FROM %i WHERE appname = %s ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_s3208(), self::$m4is_r9613 );
 if($m4is_v15639 ){
$m4is_l53261 =self::m4is_j6724();
 if(!empty($m4is_l53261 )){
$m4is_n526 =implode(',', $m4is_l53261 );
 $m4is_v2613 .= " AND `id` NOT IN ( {$m4is_n526
} ) ";
 
}
}$m4is_v2613 .= " ORDER BY `id` ASC";
 $m4is_x69523 =$wpdb->get_col($m4is_v2613 );
 if($m4is_p02 &&!in_array(0, $m4is_l53261 )){
$m4is_x69523 =array_merge([0], $m4is_x69523 );
 
}return $m4is_x69523;
 
} public static 
function m4is_a38740(bool $m4is_v15639 =true, bool $m4is_p02 =true ): array {
global $wpdb;
 $m4is_l53261 =[];
 $m4is_v2613 =$wpdb->prepare("SELECT `id`, `name` FROM %i WHERE appname = %s ", self::$m4is_v40368, self::$m4is_r9613 );
 if($m4is_v15639 ){
$m4is_l53261 =self::m4is_j6724();
 if(!empty($m4is_l53261 )){
$m4is_n526 =implode(',', $m4is_l53261 );
 $m4is_v2613 .= " AND `id` NOT IN ( {$m4is_n526
} ) ";
 
}
}$m4is_v2613 .= " ORDER BY `name` ASC";
 $m4is_x69523 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 if($m4is_p02 &&!in_array(0, $m4is_l53261 )){
$m4is_x69523 =array_merge([['id' =>0, 'name' =>'(Uncategorized)']], $m4is_x69523 );
 
}return $m4is_x69523;
 
} public static 
function m4is_d7492(): int {
global $wpdb;
 $m4is_c1869 =get_option('memberium_tables_updated', []);
 $m4is_c1869['tagcategories']=isset($m4is_c1869['tagcategories'])? $m4is_c1869['tagcategories']: 0;
 $m4is_s0258 ='';
 $m4is_c92430 =self::PAGE_SIZE;
 $m4is_d3012 =0;
 $m4is_e69637 =0;
 $m4is_e80 ='ContactGroupCategory';
 $m4is_m7426 =self::$m4is_v40368;
 $m4is_v76912 =['CategoryName' =>'%' ];
 $m4is_h3647 =['Id', 'CategoryName' ];
 $m4is_m615 =self::$m4is_z59682->dsQueryOrderBy($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', true );
 if(is_string($m4is_m615 )){
error_log('Memberium: [error] Tag Category Sync API Error - ' . $m4is_m615 );
 
}if(is_array($m4is_m615 )){
$m4is_i760 =[];
 if(is_array($m4is_m615 )&&!empty($m4is_m615 )){
foreach ($m4is_m615 as $m4is_g91703 ){
$m4is_i760[]=$wpdb->prepare("(%d, %s, %s)", intval($m4is_g91703['Id']), self::$m4is_r9613, $m4is_g91703['CategoryName']);
 $m4is_s0258 .= $m4is_g91703['Id']. ',';
 
}
}if(!empty($m4is_i760 )){
$m4is_v2613 ="INSERT INTO {$m4is_m7426
} (id, appname, name) VALUES " . implode(', ', $m4is_i760). " ON DUPLICATE KEY UPDATE id=VALUES(id), appname=VALUES(appname), name=VALUES(name);";
 $wpdb->query($m4is_v2613);
 
}if(!empty($m4is_s0258 )){
$m4is_s0258 =trim($m4is_s0258, ',' );
 $m4is_v2613 ="DELETE FROM {$m4is_m7426
} WHERE `appname` = %s AND `id` NOT IN ({$m4is_s0258
}) ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_r9613 );
 $wpdb->query($m4is_v2613 );
 
}set_transient('memberium_tagcategories_updated', time());
 
}$m4is_c1869['tagcategories']=time();
 update_option('memberium_tables_updated', $m4is_c1869, false );
 return $m4is_e69637;
 
} public static 
function m4is_f37(string $m4is_n3512 ): int {
$m4is_n3512 =trim($m4is_n3512 );
 if(empty($m4is_n3512 )){
return 0;
 
}if(self::m4is_h510($m4is_n3512 )){
return 0;
 
}$m4is_g91703 =['CategoryName' =>$m4is_n3512 ];
 $m4is_d07693 =(int) m4is_c69807::m4is_y7501('ContactGroupCategory', $m4is_g91703 );
 self::m4is_d7492();
 return $m4is_d07693;
 
}
}

