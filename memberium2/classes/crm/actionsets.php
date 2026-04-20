<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_j4156 {
private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_z59682;
 private static $m4is_z98;
 private static $m4is_m7426;
  static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_z98 =1000;
 self::$m4is_m7426 ='memberium_actionsets';
 
}private 
function __construct(){
 
}     public static 
function m4is_l21360(): string {
return self::$m4is_m7426;
 
}public static 
function m4is_g29346(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_l21360();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(11) NOT NULL, \n" . "appname varchar(32) NOT NULL, \n" . "name varchar(64) NOT NULL, \n" . "KEY appname (appname), \n" . "KEY id (id), \n" . "PRIMARY KEY  (appname,id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}    public static 
function m4is_c289(): int {
global $wpdb;
 static $m4is_h973;
 if(isset($m4is_h973 )){
return $m4is_h973;
 
}$m4is_v2613 =$wpdb->prepare("SELECT count(`id`) from %i WHERE `appname` = %s", self::m4is_l21360(), self::$m4is_r9613 );
 $m4is_h973 =(int) $wpdb->get_var($m4is_v2613 );
 return $m4is_h973;
 
} public static 
function m4is_x03(): array {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT `id`, `name` FROM %i WHERE `appname` = %s AND `name` > '' ORDER BY `name` ASC", self::m4is_l21360(), self::$m4is_r9613 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 return $m4is_m615;
 
} public static 
function m4is_s6612(): string {
static $m4is_i7193;
 if(isset($m4is_i7193 )){
return $m4is_i7193;
 
}$m4is_i7193 =self::m4is_x03();
 foreach ($m4is_i7193 as $m4is_l9671 =>$m4is_g91703 ){
$m4is_i7193[$m4is_l9671]['text']=$m4is_g91703['name'];
 unset($m4is_i7193[$m4is_l9671]['name']);
 
}$m4is_h69684 =['id' =>0, 'text' =>'(No Action)' ];
 $m4is_i7193 =array_merge([$m4is_h69684 ], $m4is_i7193 );
 $m4is_i7193 =json_encode($m4is_i7193 );
 return $m4is_i7193;
 
} public static 
function m4is_z8359(): array {
$m4is_m615 =self::m4is_x03();
 $m4is_i7193 =[];
 foreach ($m4is_m615 as $m4is_g91703 ){
$m4is_d07693 =$m4is_g91703['id'];
 $m4is_i7193[$m4is_d07693]=$m4is_g91703['name'];
 
}return $m4is_i7193;
 
} public static 
function m4is_w4805(int $m4is_h21895, int $m4is_w64602, bool $m4is_q26407 =false ){
$m4is_u6591 =self::$m4is_z59682->runAS($m4is_h21895, $m4is_w64602 );
 if($m4is_q26407 ){
self::$m4is_r1546->m4is_x4831($m4is_h21895 );
 
}return $m4is_u6591;
 
}private static 
function m4is_k8790(): array {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT `id` FROM %i WHERE `appname` = %s", self::m4is_l21360(), self::$m4is_r9613 );
 $m4is_p65 =$wpdb->get_col($m4is_v2613 );
 return $m4is_p65;
 
} static 
function m4is_f7940(){
global $wpdb;
 $m4is_c1869 =get_option('memberium_tables_updated', []);
 $m4is_c1869['actionsets']=isset($m4is_c1869['actionsets'])? $m4is_c1869['actionsets']: 0;
 $m4is_b10 =self::m4is_k8790();
 $m4is_e80 ='ActionSequence';
 $m4is_d3012 =0;
 $m4is_z051 ='Id';
 $m4is_e207 ='%';
 $m4is_m7426 =self::m4is_l21360();
 $m4is_e69637 =0;
 $m4is_n857 =[];
 $m4is_q42669 =false;
 $m4is_h69504 =0;
 $m4is_v76912 =['Id' =>'%', 'TemplateName' =>'%',  ];
 $m4is_h3647 =['Id', 'TemplateName', 'VisibleToTheseUsers' ];
  do {
$m4is_m615 =self::$m4is_z59682->dsQuery($m4is_e80, self::$m4is_z98, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', true );
 if(is_string($m4is_m615 )){
error_log('Memberium: [error] Actionset Sync API Error - ' . $m4is_m615 );
 $m4is_q42669 =true;
 break;
 
}$m4is_o1402 =is_array($m4is_m615 )? count($m4is_m615 ): 0;
 if($m4is_o1402){
$m4is_a1846 =reset($m4is_m615 )['Id'];
 $m4is_h69504 =end($m4is_m615 )['Id'];
 $m4is_i760 =[];
 $m4is_u450 =[];
 foreach ($m4is_m615 as $k =>$m4is_g91703){
$m4is_f07829 =substr($m4is_g91703['TemplateName']?? '', 0, 63 );
 $m4is_m95 =intval($m4is_g91703['Id']?? 0 );
 $m4is_k52736 =substr($m4is_f07829, 0, 63 );
 $m4is_i760[]=$wpdb->prepare(" ( %d, %s, %s ) ", $m4is_m95, self::$m4is_r9613, $m4is_k52736 );
 $m4is_u450[]=$m4is_g91703['Id'];
 $m4is_n857[]=$m4is_g91703['Id'];
 
}if(!empty($m4is_i760 )){
$m4is_x39 =implode(', ', $m4is_i760 );
 $m4is_v2613 =$wpdb->prepare("INSERT INTO %i (id, appname, name) VALUES {$m4is_x39
} ON DUPLICATE KEY UPDATE id=VALUES(id), appname=VALUES(appname), name=VALUES(name)", self::m4is_l21360());
 $wpdb->query($m4is_v2613 );
 
}
}$m4is_d3012++;
 $m4is_e69637 =$m4is_e69637 + $m4is_o1402;
 
}while ($m4is_o1402 == self::$m4is_z98 );
 $m4is_i785 =array_diff($m4is_b10, $m4is_n857 );
 if($m4is_e69637 > 0 &&!empty($m4is_i785 )){
$m4is_h5648 =implode(',', $m4is_i785 );
 $m4is_v2613 ="DELETE FROM %i WHERE `appname` = %s AND `id` IN ( {$m4is_h5648
} )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_l21360(), self::$m4is_r9613 );
 $wpdb->query($m4is_v2613 );
 
}$m4is_c1869['actionsets']=time();
 update_option('memberium_tables_updated', $m4is_c1869, false );
 wp_cache_delete('actionsets', 'memberium2/keap' );
 return $m4is_e69637;
 
} 
}

