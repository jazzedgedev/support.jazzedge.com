<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 m4is_u36::m4is_i702();
  final 
class m4is_u36 {
private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_z59682;
 private static $m4is_e80;
 private static $m4is_h85;
 static 
function m4is_i702(){
global $wpdb;
 self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_e80 =$wpdb->prefix . 'memberium_owners';
 self::$m4is_h85 =1000;
 
}   static 
function m4is_p85732(): string {
return self::$m4is_e80;
 
}static 
function m4is_p37(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::$m4is_e80;
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) NOT NULL, \n" . "appname varchar(32) NOT NULL, \n" . "fieldname varchar(64) NOT NULL DEFAULT '', \n" . "value longtext, \n" . "KEY id (id), \n" . "KEY fieldname (fieldname), \n" . "KEY appname (appname), \n" . "KEY value (value(64) ), \n" . "PRIMARY KEY  (appname,id,fieldname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}   public static 
function m4is_b6056(int $m4is_p71360, bool $m4is_r896 =false ): array {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT `fieldname`, `value` FROM %i WHERE `appname` = %s AND `id` = %d", self::$m4is_e80, self::$m4is_r9613, $m4is_p71360 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 $m4is_w597 =m4is_c69807::m4is_n146($m4is_m615 );
 if($m4is_r896 ){
$m4is_w597 =array_change_key_case($m4is_w597, CASE_LOWER );
 
}return $m4is_w597;
 
}public static 
function m4is_e9031(array $m4is_w597 ){
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("DELETE FROM %i WHERE `appname` = %s AND `id` = %d", self::$m4is_e80, self::$m4is_r9613, $m4is_w597['Id']);
 $wpdb->query($m4is_v2613 );
  $m4is_d07693 =$m4is_w597['Id'];
 $m4is_z34951 =[];
 foreach ($m4is_w597 as $m4is_o015 =>$m4is_k72 ){
$m4is_x39 =[];
 $m4is_x39[]=$m4is_d07693;
 $m4is_x39[]=self::$m4is_r9613;
 $m4is_x39[]=$m4is_o015;
 $m4is_x39[]=$m4is_k72;
 $m4is_z34951[]=$wpdb->prepare('(%d, %s, %s, %s)', $m4is_x39 );
 
} $m4is_v2613 =$wpdb->prepare("INSERT INTO %i ( `id`, `appname`, `fieldname`, `value` ) VALUES " . implode(', ', $m4is_z34951 ), self::$m4is_e80 );
 $m4is_v2613 =$wpdb->query($m4is_v2613 );
 
} public static 
function m4is_n603(){
$m4is_r31625 =self::m4is_s59348();
 foreach ($m4is_r31625 as $m4is_w597 ){
self::m4is_e9031($m4is_w597 );
 
} 
}   public static 
function m4is_s59348(): array {
$m4is_e80 ='User';
 $m4is_r31625 =[];
 $m4is_h3647 =m4is_c69807::m4is_f5248('User', false );
 $m4is_v76912 =['Id' =>'%' ];
 $m4is_u39687 =self::$m4is_z59682->dsQuery('User', self::$m4is_h85, 0, $m4is_v76912, $m4is_h3647 );
 $m4is_u39687 =is_array($m4is_u39687 )? $m4is_u39687 : [];
 if(is_string($m4is_u39687 )){
error_log('Memberium: [error] Keap User Sync API Error - ' . $m4is_u39687 );
 return 0;
 
} return $m4is_u39687;
 
}
}

