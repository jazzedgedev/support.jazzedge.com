<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_o106 {
private static m4is_r83 $m4is_r1546;
 private static string $m4is_r9613;
  private 
function __construct(){
 
} public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 
}    public static 
function m4is_d60(array $m4is_a0895 ): array {
$m4is_a0895['memberium2']=['exporter_friendly_name' =>'Memberium', 'callback' =>['m4is_o106', 'm4is_u46570'], ];
 return $m4is_a0895;
 
} public static 
function m4is_t102(array $m4is_i5476 ): array {
$m4is_i5476['memberium2']=['eraser_friendly_name' =>'Memberium', 'callback' =>['m4is_o106', 'm4is_t18073'], ];
 return $m4is_i5476;
 
} public static 
function m4is_u46570(string $m4is_f4930, int $m4is_d3012 =1 ){
global $wpdb;
 $m4is_f4930 =strtolower(trim ($m4is_f4930));
 $m4is_l17096 =get_user_by('email', $m4is_f4930);
 $m4is_f087 =$m4is_l17096->ID;
 $m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087);
 $m4is_d3012 =(int) $m4is_d3012;
 $m4is_i2668 =[];
 $m4is_l91805 =[];
  $m4is_l79562 =['login_ip_address' =>'Login IP Address', 'last_login_time' =>'Last Login Time', 'login_count' =>'Login Count', ];
 foreach((array)$m4is_l79562 as $m4is_r1692 =>$m4is_w42){
$m4is_k72 =get_user_meta($m4is_f087, $m4is_r1692, true);
 if(!empty($m4is_k72)){
$m4is_l91805[]=['name' =>$m4is_w42, 'value' =>$m4is_k72, ];
 
}
} if($m4is_h21895){
$m4is_i935 =m4is_p40::m4is_p67($m4is_h21895);
 if(!empty($m4is_i935)){
foreach($m4is_i935 as $m4is_o015 =>$m4is_k72){
if(!empty($m4is_k72)){
$m4is_l91805[]=['name' =>$m4is_o015, 'value' =>$m4is_k72, ];
 
}
}
}
}$m4is_j4976 ='memberium';
 $m4is_f4720 ='Memberium';
 $m4is_k09 ="memberium-user";
 $m4is_i2668[]=['group_id' =>$m4is_j4976, 'group_label' =>$m4is_f4720, 'item_id' =>$m4is_k09, 'data' =>$m4is_l91805, ];
 $m4is_l91805 =[];
 $m4is_v2613 ='SELECT DISTINCT `ipaddress` FROM `' . m4is_l5841::m4is_h37(). '` WHERE `username` = %s ';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f4930);
 $m4is_m615 =$wpdb->get_col($m4is_v2613);
 if(is_array($m4is_m615)&&!empty($m4is_m615)){
foreach($m4is_m615 as $row){
$m4is_l91805[]=['name' =>'IP Address', 'value' =>$row ];
 
}$m4is_j4976 ='memberium-ip-history';
 $m4is_f4720 ='Memberium IP History';
 $m4is_k09 ="memberium-ip-history";
 $m4is_i2668[]=['group_id' =>$m4is_j4976, 'group_label' =>$m4is_f4720, 'item_id' =>$m4is_k09, 'data' =>$m4is_l91805, ];
 
}$m4is_m765 =true;
 return ['data' =>$m4is_i2668, 'done' =>$m4is_m765, ];
 
} static 
function m4is_t18073(string $m4is_f4930, int $m4is_d3012 =1 ){
global $wpdb;
 $m4is_f4930 =strtolower(trim($m4is_f4930 ));
 $m4is_l17096 =get_user_by('email', $m4is_f4930 );
 $m4is_f087 =$m4is_l17096->ID;
 $m4is_l063 =false;
 $m4is_d3012 =(int) $m4is_d3012;
 $m4is_r9613 =self::$m4is_r1546->m4is_i76('appname');
 $m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 $m4is_t6601 =m4is_z13097::m4is_y876($m4is_h21895 );
 if($m4is_h21895 ){
self::m4is_y6561($m4is_h21895 );
 $m4is_v2613 ='DELETE FROM %i WHERE `appname` = %s AND `id` = %d ';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_p40::m4is_o1723(), $m4is_r9613, $m4is_h21895 );
 $m4is_l063 += $wpdb->query($m4is_v2613);
  $m4is_v2613 ='DELETE FROM %i WHERE `appname` = %s AND `contactid` = %d ';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_k865::m4is_o39864(), $m4is_r9613, $m4is_h21895);
 $m4is_l063 += $wpdb->query($m4is_v2613);
   if($m4is_t6601 ){
$m4is_v2613 ='DELETE FROM `' . m4is_z13097::m4is_o71(). '` WHERE `appname` = %s AND `id` = %d ';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_r9613, $m4is_t6601);
 $m4is_l063 += $wpdb->query($m4is_v2613);
 
}
}if($m4is_f087){
$m4is_v2613 ='DELETE FROM %i WHERE `userid` = %d ';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, constant('MEMBERIUM_DB_PAGETRACKING' ), $m4is_f087 );
 $m4is_l063 += $wpdb->query($m4is_v2613);
 
} $m4is_v2613 ='DELETE FROM %i WHERE `appname` = %s AND `username` = %s';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_l5841::m4is_h37(), $m4is_r9613, $m4is_f4930);
 $m4is_l063 += $wpdb->query($m4is_v2613);
  return ['done' =>true, 'items_removed' =>$m4is_l063, 'items_retained' =>false, 'messages' =>[], ];
 
} private static 
function m4is_y6561(int $m4is_h21895 ): bool {
$m4is_d913 =(int) self::$m4is_r1546->m4is_j498('settings', 'gdpr_deleted_tag', 0 );
 if($m4is_d913 ){
self::$m4is_r1546->elf_add_remove_crm_tags($m4is_d913, $m4is_h21895 );
 
}return true;
 
} 
}

