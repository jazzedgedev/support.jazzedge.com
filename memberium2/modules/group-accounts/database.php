<?php

/**
 * Copyright (c) 2023-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_u81 {
private static object $m4is_v845;
 private static object $m4is_r1546;
 private static string $m4is_r9613;
 private const USER_META_SOURCE_KEY ='memberium/groups/source';
 private 
function __construct(){
 
}public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_v845 =m4is_u7102::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 
}    static 
function m4is_a6804(): string {
global $wpdb;
 return $wpdb->prefix . 'memberium_group_account';
 
} static 
function m4is_k43(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_a6804();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(11) NOT NULL AUTO_INCREMENT, \n" . "parent_uid bigint(20) NOT NULL, \n" . "child_uid bigint(20) NOT NULL, \n" . "active tinyint(1) NOT NULL, \n" . "sync tinyint(1) NOT NULL, \n" . "KEY parent_uid (parent_uid), \n" . "KEY child_uid (child_uid), \n" . "UNIQUE KEY uids (parent_uid,child_uid), \n" . "PRIMARY KEY  (id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}static 
function m4is_t135(): string {
global $wpdb;
 return $wpdb->prefix . 'memberium_team_membership';
 
}static 
function m4is_i960(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_t135();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(11) NOT NULL AUTO_INCREMENT, \n" . "team_id bigint(20) NOT NULL, \n" . "parent_uid bigint(20) NOT NULL, \n" . "child_uid bigint(20) NOT NULL, \n" . "active tinyint(1) NOT NULL, \n" . "KEY team_id (team_id), \n" . "KEY child_uid (child_uid), \n" . "UNIQUE KEY uids (team_id,child_uid), \n" . "PRIMARY KEY  (id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}   private static 
function m4is_h0665(): array {
global $wpdb;
 $m4is_v61358 =self::$m4is_v845->m4is_d326('parent_field' );
 $m4is_v2613 ="SELECT `id` as 'contact_id', `value` as 'code' FROM %i WHERE `appname` = %s AND `fieldname` = %s AND `value` LIKE 'prnt-%' ORDER BY `id` ASC";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_p40::m4is_o1723(), self::$m4is_r9613, $m4is_v61358 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 return (array)$m4is_m615;
 
}public static 
function m4is_d38602(int $m4is_f087 ): void {
global $wpdb;
 if(!self::$m4is_v845->m4is_r195($m4is_f087 )){
return;
 
}$m4is_v61358 =self::$m4is_v845->m4is_s54627();
 $m4is_b66932 =m4is_q82::m4is_k660($m4is_f087, 'contact', $m4is_v61358, '' );
 $m4is_b66932 =substr($m4is_b66932, 5 );
 if(empty($m4is_b66932 )){
return;
 
}$m4is_v3458 ='chld-' . $m4is_b66932 ;
 $m4is_v2613 ="SELECT `id` as 'contact_id', `value` as 'code' FROM %i WHERE `appname` = %s AND `fieldname` = %s AND `value` LIKE %s ORDER BY `id` ASC";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_p40::m4is_o1723(), self::$m4is_r9613, $m4is_v61358, $m4is_v3458 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 if(empty($m4is_m615 )){
return;
 
}$m4is_b6438 =self::$m4is_v845->m4is_y9164($m4is_f087 );
 foreach ($m4is_m615 as $m4is_h21895 =>$m4is_i935 ){
$m4is_a62514 =m4is_p40::m4is_i6158($m4is_h21895 );
 if($m4is_a62514 &&!in_array($m4is_a62514, $m4is_b6438 )){
self::$m4is_v845->m4is_b69781($m4is_a62514, $m4is_f087 );
 
}
}$m4is_v2613 ="DELETE FROM %i WHERE (`parent_uid` = 0 OR `child_uid` = 0) AND `sync` = 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_a6804());
 $wpdb->query($m4is_v2613 );
 
} public static 
function m4is_u4372(): void {
global $wpdb;
 $m4is_v2613 ="SELECT count(*) FROM %i WHERE 1=1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_a6804());
 $m4is_h973 =$wpdb->get_var($m4is_v2613 );
 if($m4is_h973 ){
return;
 
}$m4is_v61358 =self::$m4is_v845->m4is_s54627();
 $m4is_e29 =[];
 $m4is_y89162 =[];
 $m4is_v2613 ="SELECT `id` as 'contact_id', `value` as 'code' FROM %i WHERE `appname` = %s AND `fieldname` = %s AND `value` LIKE 'prnt-%' ORDER BY `id` ASC";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_p40::m4is_o1723(), self::$m4is_r9613, $m4is_v61358 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 foreach ($m4is_m615 as $m4is_h21895 =>$m4is_i935 ){
$m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 $m4is_e29[$m4is_f087]=substr($m4is_i935->code, 5 );
 
}$m4is_v2613 ="SELECT `id` as 'contact_id', `value` as 'code' FROM %i WHERE `appname` = %s AND `fieldname` = %s AND `value` LIKE 'chld-%' ORDER BY `id` ASC";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_p40::m4is_o1723(), self::$m4is_r9613, $m4is_v61358 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 foreach ($m4is_m615 as $m4is_h21895 =>$m4is_i935 ){
$m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 $m4is_y89162[$m4is_f087]=substr($m4is_i935->code, 5 );
 
}$m4is_v2613 ="SELECT `child_uid` FROM %i WHERE 1=1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_a6804());
 $m4is_l90 =$wpdb->get_col($m4is_v2613 );
 $m4is_e29 =array_flip($m4is_e29 );
 $m4is_y89162 =array_filter($m4is_y89162, function ($m4is_v586, $m4is_l9671)use ($m4is_l90 ){
return !in_array($m4is_l9671, $m4is_l90);
 
}, ARRAY_FILTER_USE_BOTH );
 foreach($m4is_y89162 as $m4is_t6056 =>$m4is_v3458 ){
$m4is_h850 =$m4is_e29[$m4is_v3458]?? 0;
 if(!$m4is_h850 ){
continue;
 
}$m4is_v2613 ="INSERT INTO %i (`parent_uid`, `child_uid`, `active` ) VALUES ( %d, %d, 1 )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_a6804(), $m4is_h850, $m4is_t6056 );
 $wpdb->query($m4is_v2613 );
 
}
}    public static 
function m4is_r43(int $m4is_x09186, int $m4is_h850, bool $m4is_m9654 ): void {
global $wpdb;
 $m4is_e80 =self::m4is_a6804();
 $m4is_m9654 =(int) $m4is_m9654;
 $m4is_v2613 ="INSERT INTO %i (`parent_uid`, `child_uid`, `active`, `sync` ) VALUES (%d, %d, %d, 0 ) ON DUPLICATE KEY UPDATE `active` = %d, `sync` = 0";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_h850, $m4is_x09186, $m4is_m9654, $m4is_m9654 );
 update_user_meta($m4is_x09186, self::USER_META_SOURCE_KEY, 'relationship_table' );
 $wpdb->query($m4is_v2613 );
 
} 
}

