<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_q62395 {
private static $m4is_r1546;
 private static $m4is_b1723;
 static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_b1723 ='memberium/log_count';
 
}    static 
function m4is_t66507(): string {
return defined('MEMBERIUM_DB_HTTPPOST' )? constant('MEMBERIUM_DB_HTTPPOST' ): 'memberium_httppost';
 
}static 
function m4is_s7462(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_t66507();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(11) NOT NULL AUTO_INCREMENT, \n" . "time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, \n" . "appname varchar(16) NOT NULL, \n" . "contactid int(11) NOT NULL, \n" . "ipaddress varchar(45) NOT NULL, \n" . "type varchar(16) NOT NULL, \n" . "log longtext NOT NULL, \n" . "KEY appname (appname), \n" . "KEY contactid (contactid), \n" . "KEY type (type), \n" . "KEY ipaddress (ipaddress), \n" . "PRIMARY KEY  (id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}    static 
function m4is_v729(int $m4is_h21895, string $m4is_j0361 ='event', string $m4is_e63195 ='', string $m4is_j6305 ='', bool $m4is_f36 =false ): int {
if($m4is_j0361 == 'httppost' ){
$m4is_m482 =self::$m4is_r1546->m4is_j498('settings', 'httppost_log' );
 if($m4is_f36 === false &&empty($m4is_m482 )){
return false;
 
}
}global $wpdb;
 $m4is_e80 =self::m4is_t66507();
 $m4is_l91805 =['contactid' =>(int) $m4is_h21895, 'type' =>trim($m4is_j0361 ), 'appname' =>self::$m4is_r1546->m4is_i76('appname' ), 'ipaddress' =>$m4is_j6305, 'log' =>trim($m4is_e63195). "\n", ];
 $m4is_f16985 =['%d', '%s', '%s', '%s', '%s', ];
 $wpdb->insert($m4is_e80, $m4is_l91805, $m4is_f16985);
 $m4is_m95 =(int) $wpdb->insert_id;
 delete_transient(self::$m4is_b1723 );
 return $m4is_m95;
 
} static 
function m4is_x6835(int $m4is_m95, string $m4is_e63195 ): bool {
if($m4is_m95 ){
global $wpdb;
 $m4is_m95 =(int) $m4is_m95;
 $m4is_e63195 =trim($m4is_e63195);
 if(!empty($m4is_e63195)&&!empty($m4is_m95)){
$m4is_e80 =self::m4is_t66507();
 $m4is_v2613 ="UPDATE `{$m4is_e80
}` SET `log` = CONCAT( IFNULL( `log`, '' ), %s ) WHERE `id` = %d;";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e63195 . "\n", $m4is_m95);
 $wpdb->query($m4is_v2613);
 delete_transient(self::$m4is_b1723 );
 return true;
 
}
}return false;
 
}    static 
function m4is_g2674(){
global $wpdb;
 $m4is_e80 =self::m4is_t66507();
 $m4is_t21468 =defined('HTTPPOST_LOG_DAYS' )? (int) constant('HTTPPOST_LOG_DAYS' ): 7;
 $m4is_v2613 ="DELETE FROM `{$m4is_e80
}` WHERE `time` < NOW() - INTERVAL {$m4is_t21468
} DAY ";
 $wpdb->query($m4is_v2613);
 delete_transient(self::$m4is_b1723 );
 
}    public static 
function m4is_r8051(): void {
global $wpdb;
 $m4is_v2613 ="DELETE FROM %i WHERE `type` = 'loginfail'";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_t66507());
 $wpdb->query($m4is_v2613 );
 delete_transient(self::$m4is_b1723 );
 
} public static 
function m4is_g03(): void {
global $wpdb;
 $m4is_v2613 ="DELETE FROM %i WHERE `type` = 'autologin'";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_t66507());
 $wpdb->query($m4is_v2613 );
 
} public static 
function m4is_n32950(): void {
global $wpdb;
 $m4is_v2613 ="DELETE FROM %i WHERE `type` = 'httppost'";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_t66507());
 $wpdb->query($m4is_v2613 );
 
} 
}

