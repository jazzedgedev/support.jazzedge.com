<?php
 class_exists('m4is_r83' )||die();
 m4is_p75092::m4is_i702();
 final 
class m4is_p75092 {
private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_z59682;
 private static $m4is_z98;
 private static $m4is_m7426;
 public const NOTE_TYPE =-1;
 public const TASK_TYPE =-2;
 public const APPOINTMENT_TYPE =-3;
 public const ACCEPTED =1;
 public const DECLINED =0;
  static 
function m4is_i702(){
global $wpdb;
 self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_m7426 =$wpdb->prefix . 'memberium_contactactions';
 self::$m4is_z98 =1000;
 
}private 
function __construct(){

}   static 
function m4is_t53(): string {
return self::$m4is_m7426;
  
}static 
function m4is_l687(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_t53();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) NOT NULL, \n" . "fieldname varchar(64) NOT NULL DEFAULT '', \n" . "value longtext, \n" . "KEY id (id), \n" . "KEY fieldname (fieldname), \n" . "KEY appname (appname), \n" . "KEY value (value(64) ), \n" . "PRIMARY KEY  (appname,id,fieldname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}   
}

