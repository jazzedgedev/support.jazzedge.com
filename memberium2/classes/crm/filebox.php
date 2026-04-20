<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_t21664 {
private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_z59682;
 private static $m4is_s51039;
 private static $m4is_b21;
 private static $m4is_h85;
 private static $m4is_u6095;
 private static $m4is_w4103;
 private static $m4is_k5049;
  public static 
function m4is_c961(){
global $wpdb;
 self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_b21 =$wpdb->prefix . 'memberium_filebox';
 self::$m4is_u6095 ='FileBox';
 self::$m4is_k5049 ='memberium/filebox/lastupdate';
 self::$m4is_h85 =1000;
 self::$m4is_w4103 =900;
 m4is_j586::m4is_k751();
 
}   static 
function m4is_c03(): string {
return self::$m4is_b21;
 
}static 
function m4is_m64352(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_c03();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) unsigned NOT NULL, \n" . "contactid int(20) unsigned NOT NULL, \n" . "appname varchar(32) NOT NULL, \n" . "filename varchar(255) NOT NULL, \n" . "extension varchar(255) NOT NULL, \n" . "filesize int(20) unsigned NOT NULL, \n" . "public tinyint(1) unsigned NOT NULL, \n" . "KEY id (id), \n" . "KEY contactid (contactid), \n" . "KEY appname (appname), \n" . "KEY filename (filename), \n" . "PRIMARY KEY  (appname,id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}   private static 
function m4is_k01(): array {
if(is_null(self::$m4is_s51039 )){
self::$m4is_s51039 =['3gp' =>'video/3gpp', 'avi' =>'video/x-msvideo', 'css' =>'text/css', 'doc' =>'application/msword', 'docx' =>'application/msword', 'exe' =>'application/octet-stream', 'gif' =>'image/gif', 'htm' =>'text/html', 'html' =>'text/html', 'jpeg' =>'image/jpg', 'jpg' =>'image/jpg', 'js' =>'application/javascript', 'jsc' =>'application/javascript', 'mov' =>'video/quicktime', 'mp3' =>'audio/mpeg', 'mpe' =>'video/mpeg', 'mpeg' =>'video/mpeg', 'mpg' =>'video/mpeg', 'pdf' =>'application/pdf', 'php' =>'text/html', 'png' =>'image/png', 'ppt' =>'application/vnd.ms-powerpoint', 'wav' =>'audio/x-wav', 'xls' =>'application/vnd.ms-excel', 'zip' =>'application/zip', ];
 
}return self::$m4is_s51039;
 
} private static 
function m4is_q580(string $m4is_u264 ): string {
$m4is_c630 =strtolower(end(explode('.', $m4is_u264 )));
 return array_key_exists($m4is_c630, self::m4is_k01())? self::$m4is_s51039[$m4is_c630]: 'application/octet-stream';
 
}    private static 
function m4is_u34(int $m4is_h21895 ): array {
global $wpdb;
 $m4is_v76912 =$wpdb->prepare("SELECT id FROM %i WHERE contactid = %d", self::$m4is_b21, $m4is_h21895 );
 return $wpdb->get_col($m4is_v76912 );
 
} private static 
function m4is_a36426(array $m4is_i785, array $m4is_q0668 ): void {
global $wpdb;
 $m4is_p65 =array_filter(array_diff($m4is_i785, $m4is_q0668 ));
 $m4is_d579 =implode(',', $m4is_p65 );
 $m4is_v2613 =$wpdb->prepare("DELETE FROM %i WHERE `appname` = %s AND `id` IN ( {$m4is_d579
} )", self::$m4is_r9613, self::$m4is_b21 );
 
}private static 
function m4is_a34(int $m4is_h21895 ): void {
$m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 update_user_meta($m4is_f087, self::$m4is_k5049, time());
 
}private static 
function m4is_n14307(int $m4is_h21895 ): bool {
$m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 $m4is_w46279 =(int) get_user_meta($m4is_f087, self::$m4is_k5049, true );
 return (time()> ($m4is_w46279 + self::$m4is_w4103 ));
 
}   public static 
function m4is_g9543(int $m4is_h21895 ): int {
global $wpdb;
 $m4is_v76912 =$wpdb->prepare("SELECT COUNT(`id`) FROM %i WHERE `appname` = %s and `contactid` = %d", self::$m4is_b21, self::$m4is_r9613, $m4is_h21895 );
 return (int) $wpdb->get_var($m4is_v76912 );
 
} public static 
function m4is_p86456(int $m4is_h21895, bool $m4is_r896 =false ): array {
global $wpdb;
 $m4is_i61 =[];
 $m4is_v76912 =$wpdb->prepare("SELECT * FROM %i WHERE contactid = %d", self::$m4is_b21, $m4is_h21895 );
 $m4is_x9366 =$wpdb->get_results($m4is_v76912, ARRAY_A );
 if(!is_array($m4is_x9366 )){
return [];
 
}foreach($m4is_x9366 as $m4is_j90523 ){
$m4is_i61[$m4is_j90523['id']]=['ContactId' =>$m4is_j90523['contactid'], 'FileName' =>$m4is_j90523['filename'], 'Extension' =>$m4is_j90523['extension'], 'FileSize' =>$m4is_j90523['filesize'], 'Id' =>$m4is_j90523['id'], 'Public' =>$m4is_j90523['public']];
 
}$m4is_i61 =$m4is_r896 ? array_change_key_case($m4is_i61, CASE_LOWER ): $m4is_i61;
 return $m4is_i61;
 
} public static 
function m4is_m57(int $m4is_h21895, bool $m4is_f465 =false ): array {
global $wpdb;
 if(!$m4is_f465 &&!self::m4is_n14307($m4is_h21895 )){
$m4is_i61 =self::m4is_p86456($m4is_h21895 );
 if(!empty($m4is_i61 )){
return self::m4is_p86456($m4is_h21895 );
 
}
}$m4is_h3647 =m4is_c69807::m4is_f5248(self::$m4is_u6095, false );
 $m4is_d3012 =0;
 $m4is_i61 =[];
 $m4is_p65 =[];
 $m4is_v76912 =['ContactId' =>$m4is_h21895 ];
 $m4is_k623 =self::m4is_u34($m4is_h21895 );
 do {
$m4is_u39687 =self::$m4is_z59682->dsQuery(self::$m4is_u6095, self::$m4is_h85, $m4is_d3012, $m4is_v76912, $m4is_h3647 );
 if(is_string($m4is_u39687 )){
error_log('Memberium: [error] Filebox Sync API Error - ' . $m4is_u39687 );
 break;
 
}$m4is_f1236 =is_array($m4is_u39687 )? count($m4is_u39687): 0;
 if($m4is_f1236 ){
foreach ($m4is_u39687 as $m4is_e03627 ){
if($m4is_e03627['FileSize']== 0 ){
continue;
 
}$m4is_d07693 =(int) $m4is_e03627['Id'];
 $m4is_p65[]=$m4is_d07693;
 $m4is_i61[$m4is_d07693]=$m4is_e03627;
 $m4is_j90523 =['appname' =>self::$m4is_r9613, 'contactid' =>$m4is_h21895, 'filename' =>$m4is_e03627['FileName'], 'extension' =>$m4is_e03627['Extension'], 'filesize' =>$m4is_e03627['FileSize'], 'id' =>$m4is_d07693, 'public' =>$m4is_e03627['Public']];
 if(in_array($m4is_d07693, $m4is_k623 )){
$wpdb->update(self::$m4is_b21, $m4is_j90523, ['id' =>$m4is_d07693 ]);
 
}else{
$wpdb->insert(self::$m4is_b21, $m4is_j90523 );
 
}
}
}$m4is_d3012++;
 
}while ($m4is_f1236 == self::$m4is_h85 );
 self::m4is_a36426($m4is_k623, $m4is_p65 );
 self::m4is_a34($m4is_h21895 );
 return $m4is_i61;
 
}public static 
function m4is_g561(int $m4is_d07693, bool $m4is_r896 =false ): array {
global $wpdb;
 $m4is_v76912 =$wpdb->prepare("SELECT * FROM %i WHERE `appname` = %s AND `id` = %d", self::$m4is_b21, self::$m4is_r9613, $m4is_d07693 );
 $m4is_j90523 =$wpdb->get_row($m4is_v76912, ARRAY_A );
 if(!is_array($m4is_j90523 )){
return [];
 
}$m4is_u17986 =['ContactId' =>$m4is_j90523['contactid'], 'Extension' =>$m4is_j90523['extension'], 'FileName' =>$m4is_j90523['filename'], 'FileSize' =>$m4is_j90523['filesize'], 'Id' =>$m4is_j90523['id'], 'Public' =>$m4is_j90523['public']];
 $m4is_u17986 =$m4is_r896 ? array_change_key_case($m4is_u17986, CASE_LOWER ): $m4is_u17986;
 return $m4is_u17986;
 
} public static 
function m4is_t372(int $m4is_b85479, string $m4is_u264, bool $m4is_d271 =false ){
$m4is_u264 =trim($m4is_u264 );
 $m4is_d271 =(bool) $m4is_d271;
 $m4is_a27648 =$_REQUEST['signature'];
 if(!wp_verify_nonce($m4is_a27648, 'filebox_download::' . $m4is_b85479 )){
wp_die('Invalid Filebox Download Link' );
 exit;
 
}if(is_user_logged_in()&&($m4is_b85479 < 1 ||empty($m4is_u264 ))){
return;
 
}$m4is_o6120 =$m4is_d271 ? self::m4is_q580($m4is_u264 ): 'application/octet-stream';
 header('Content-Type: ' . $m4is_o6120 );
 header('Content-Disposition: attachment; filename="' . $m4is_u264 . '"' );
 echo base64_decode(self::$m4is_z59682->getFile((int) $_GET['filebox_id']));
 exit;
 
}   public static 
function m4is_y8956(){
$m4is_d3012 =(int) get_option('', 0 );
 $m4is_a89 =m4is_c69807::m4is_f5248(self::$m4is_u6095, false );
 $m4is_v76912 =['Id' =>'%', ];
 $m4is_u39687 =self::$m4is_z59682->dsQuery(self::$m4is_u6095, self::$m4is_h85, $m4is_d3012, $m4is_v76912, $m4is_a89 );
  if(is_array($m4is_u39687 )){

}
}
}

