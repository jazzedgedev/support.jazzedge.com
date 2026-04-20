<?php
 class_exists('m4is_r83' )||die();
 m4is_x6312::m4is_i702();
 final 
class m4is_x6312 {
private static $m4is_z59682;
 private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_m7426;
 public const TAG =1;
 public const ACTIONSET =2;
 public const CAMPAIGN_GOAL =3;
 public const CONTACT_FIELD_UPDATE =4;
 public static 
function m4is_i702(): void {
global $wpdb;
 self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_m7426 =$wpdb->prefix . 'memberium_api_queue';
 
}public static 
function m4is_e4625(): string {
return self::$m4is_m7426;
 
}static 
function m4is_l687(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_e4625();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) NOT NULL, \n" . "appname varchar(32) NOT NULL, \n" . "contact_id int(20) NOT NULL, \n" . "action int NOT NULL, \n" . "action_date datetime NOT NULL, \n" . "value longtext, \n" . "KEY id (id), \n" . "KEY appname (appname), \n" . "KEY fieldname (fieldname), \n" . "KEY value (value(64) ), \n" . "PRIMARY KEY  (appname,id,fieldname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}
}

