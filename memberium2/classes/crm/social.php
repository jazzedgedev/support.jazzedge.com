<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 m4is_c63::m4is_h269();
  final 
class m4is_c63 {
private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_u6095;
 private static $m4is_h85;
  public static 
function m4is_h269(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_u6095 ='SocialAccount';
 self::$m4is_h85 =1000;
 
} private 
function __construct(){

}    public static 
function m4is_r6495(): string {
global $wpdb;
 return $wpdb->prefix . 'memberium_socialaccount';
 
} public static 
function m4is_j293(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_r6495();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) NOT NULL, \n" . "contactid int(20) NOT NULL, \n" . "fieldname varchar(64) default '', \n" . "fieldvalue varchar(20) default '', \n" . "KEY id (id), \n" . "KEY contactid (contactid), \n" . "KEY fieldname (fieldname), \n" . "PRIMARY KEY  (id,contactid,fieldname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}    public static 
function m4is_e8572(int $m4is_h21895, bool $m4is_r896 =false ): array {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT `id`, `fieldname`, `fieldvalue` FROM %i WHERE `appname` = %s AND `contactid` = %d", self::m4is_r6495(), self::$m4is_r9613, $m4is_h21895 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 $m4is_t1826 =[];
 foreach($m4is_m615 as $m4is_g91703 ){
$m4is_d07693 =$m4is_g91703['id'];
 $m4is_r637 =$m4is_r896 ? strtolower($m4is_g91703['fieldname']): $m4is_g91703['fieldname'];
 $m4is_t1826[$m4is_d07693][$m4is_r637]=$m4is_g91703['fieldvalue'];
 
}if($m4is_r896 ){
$m4is_t1826 =array_change_key_case($m4is_t1826, CASE_LOWER );
 
}return $m4is_t1826;
 
} public static 
function m4is_z02(int $m4is_h21895 ): array {
$m4is_h85 =1000;
 $m4is_v76912 =['ContactId' =>$m4is_h21895 ];
 $m4is_d3012 =0;
 $m4is_q66975 =m4is_c69807::m4is_o986(self::$m4is_u6095, $m4is_h85, $m4is_d3012, $m4is_v76912 );
 if(!is_array($m4is_q66975 )){
error_log(sprintf("Memberium: [error] Failed to retrieve social accounts for contact '%d'.  Query returned '%s'", $m4is_h21895, (string) $m4is_q66975 ));
 
}$m4is_q66975 =is_array($m4is_q66975 )? $m4is_q66975 : [];
 return $m4is_q66975;
 
} public static 
function m4is_x05(int $m4is_h21895 ): void {
$m4is_i97638 =self::m4is_z02($m4is_h21895 );
 if(!empty($m4is_i97638 )){
foreach ($m4is_i97638 as $m4is_p78592 ){
self::m4is_w35($m4is_p78592 );
 
}
}
} public static 
function m4is_w35(array $m4is_p78592 ): void {
global $wpdb;
 $m4is_d07693 =$m4is_p78592['Id'];
 $m4is_h21895 =$m4is_p78592['ContactId'];
 $m4is_v2613 =$wpdb->prepare("DELETE FROM %i WHERE `id` = %d", self::m4is_r6495(), $m4is_d07693 );
 $wpdb->query($m4is_v2613 );
 if(empty($m4is_p78592['AccountName'])){
return;
 
}$m4is_z34951 =[];
 foreach ($m4is_p78592 as $m4is_r637 =>$m4is_v586 ){
$m4is_z34951[]=$wpdb->prepare('(%d, %s, %d, %s, %s)', $m4is_d07693, self::$m4is_r9613, $m4is_h21895, $m4is_r637, $m4is_v586 );
 
}$m4is_x39 =implode(', ', $m4is_z34951 );
 $m4is_v2613 =$wpdb->prepare("INSERT INTO %i ( `id`, `appname`, `contactid`, `fieldname`, `fieldvalue` ) VALUES {$m4is_x39
}", self::m4is_r6495());
 $wpdb->query($m4is_v2613 );
 
} public static 
function m4is_h0593(): bool {
$m4is_m2531 ='memberium/keap/sync/social/highwatermark';
 $m4is_d460 =get_option($m4is_m2531, '20000101T01:01:01' );
 $m4is_d3012 =0;
 $m4is_v76912 =['AccountType' =>'%', 'LastUpdated' =>"~>=~ {$m4is_d460
}" ];
 $m4is_q66975 =m4is_c69807::m4is_i84(self::$m4is_u6095, self::$m4is_h85, $m4is_d3012, $m4is_v76912, [], 'LastUpdated', true );
 if(!is_array($m4is_q66975 )){
error_log(sprintf("Memberium: [error] Failed to sync updated social accounts.  Query returned '%s'", (string) $m4is_q66975 ));
 return false;
 
}foreach ($m4is_q66975 as $m4is_n23165 ){
$m4is_d460 =$m4is_n23165['LastUpdated']> $m4is_d460 ? $m4is_n23165['LastUpdated']: $m4is_d460;
 self::m4is_w35($m4is_n23165 );
 
}update_option($m4is_m2531, $m4is_d460, false );
 return true;
 
} public static 
function m4is_c3926(int $m4is_h21895 ): void {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("DELETE FROM %i WHERE `appname` = %s AND `contactid` = %d", self::m4is_r6495(), self::$m4is_r9613, $m4is_h21895 );
 $wpdb->query($m4is_v2613 );
 
}    private static 
function m4is_n4058(int $m4is_h21895, array $m4is_i97638 ): void {
if(empty($m4is_i97638 )){
m4is_c63::m4is_c3926($m4is_h21895 );
 return;
 
}
}
}

