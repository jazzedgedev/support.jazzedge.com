<?php

/**
 * Copyright (c) 2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 
class m4is_j946 {
private static $m4is_e80;
 private static $m4is_n69720;
  static 
function m4is_c961(){
global $wpdb;
 self::$m4is_e80 ="{$wpdb->prefix
}memberium_relationships";
 self::$m4is_n69720 =constant('MEMBERIUM_DB_RELATIONSHIP_TYPES' );
 
}    static 
function m4is_e785(): string {
return self::$m4is_e80;
 
}static 
function m4is_z6169(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_e785();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(11) NOT NULL AUTO_INCREMENT, \n" . "user_id int(11) NOT NULL, \n" . "type_id int(11) NOT NULL, \n" . "rel_id int(11) NOT NULL, \n" . "created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, \n" . "UNIQUE KEY relationship (user_id,rel_id,type_id), \n" . "PRIMARY KEY  (id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
} static 
function m4is_v83150(): string {
return self::$m4is_n69720;
 
}static 
function m4is_l675(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_v83150();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}    static 
function m4is_o18(int $m4is_f087, int $m4is_y64165 ): array {
  global $wpdb;
   if(!$m4is_f087 ||!$m4is_y64165){
return [];
 
} $m4is_e80 =self::m4is_e785();
   $m4is_v2613 ="SELECT `rel_id` FROM `{$m4is_e80
}` WHERE `user_id` = %d AND `rel_type_id` = %d;";
  $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f087, $m4is_y64165 );
   $m4is_d5472 =$wpdb->get_col($m4is_v2613 );
  return $m4is_d5472;
 
} static 
function m4is_h2310(int $m4is_f087, int $m4is_y64165 ): int {
  global $wpdb;
  $m4is_e80 =self::m4is_e785();
   $m4is_v2613 ="SELECT count(*) FROM `{$m4is_e80
}` WHERE `user_id` = %d AND `rel_type_id` = %d;";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f087, $m4is_y64165 );
   $m4is_d5472 =(int) $wpdb->get_var($m4is_v2613 );
  return $m4is_d5472;
 
} static 
function m4is_a16(int $m4is_f087, int $m4is_y64165, int $m4is_u61532 ): bool {
  global $wpdb;
  $m4is_e80 =self::m4is_e785();
    $m4is_v2613 ="SELECT `rel_id` FROM `{$m4is_e80
}` WHERE `user_id` = %d AND `rel_type_id` = %d AND `rel_id` = %d LIMIT 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f087, $m4is_y64165, $m4is_u61532 );
  $m4is_u6591 =$wpdb->get_var($m4is_v2613 );
    return (bool) $m4is_u6591;
 
} static 
function m4is_u219(int $m4is_f087, int $m4is_y64165, array $m4is_o691 ){
  global $wpdb;
  $m4is_o691 =array_filter($m4is_o691, function($m4is_v586 ){
return is_int($m4is_v586 )&&$m4is_v586 > 0;
 
});
  $m4is_e80 =self::m4is_e785();
  $m4is_x4751 =self::m4is_o18($m4is_f087, $m4is_y64165 );
  $m4is_f2349 =array_diff($m4is_x4751, $m4is_o691 );
 $m4is_z87032 =array_diff($m4is_o691, $m4is_x4751 );
  if(!empty ($m4is_f2349 )){
$m4is_v2613 ="DELETE FROM `{$m4is_e80
}` WHERE `user_id` = %d AND `rel_type_id` = %d AND `rel_id` IN (%s)";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f087, $m4is_y64165, implode(',', $m4is_f2349));
 $wpdb->query($m4is_v2613);
 
} if(!empty($m4is_z87032)){
$m4is_p6925 ="INSERT INTO `{$m4is_e80
}` (`user_id`, `rel_type_id`, `rel_id`) VALUES (%s, %s, %s)";
 foreach ($m4is_z87032 as $m4is_x285){
$m4is_v2613 =$wpdb->prepare($m4is_p6925, $m4is_f087, $m4is_y64165, $m4is_x285);
 $wpdb->query($m4is_v2613);
 
}
}
} static 
function m4is_e64(int $m4is_f087, int $m4is_y64165, int $m4is_u61532 ){
  global $wpdb;
  if(!$m4is_f087 ||!$m4is_y64165 ||!$m4is_u61532 ){
return;
 
} $m4is_e80 =self::m4is_e785();
   $m4is_v2613 ="INSERT INTO `{$m4is_e80
}` (`user_id`, `rel_type_id`, `rel_id`) VALUES (%d, %d, %d);";
   $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f087, $m4is_y64165, $m4is_u61532 );
  $wpdb->query($m4is_v2613 );
 
} static 
function m4is_a7150 (int $m4is_f087, int $m4is_y64165, int $m4is_u61532 =0 ){
  global $wpdb;
  $m4is_e80 =self::m4is_e785();
  if($m4is_u61532 ){
  $m4is_v2613 ="DELETE FROM `{$m4is_e80
}` WHERE `user_id` = %d AND `rel_type_id` = %d AND `rel_id` = %d ;";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f087, $m4is_y64165, $m4is_u61532 );
 
}else{
  $m4is_v2613 ="DELETE FROM `{$m4is_e80
}` WHERE `user_id` = %d AND `rel_type_id` = %d ;";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f087, $m4is_y64165 );
 
} $wpdb->query($m4is_v2613 );
 
} static 
function m4is_v605(int $m4is_y64165 ): bool {
 global $wpdb;
      $m4is_e80 =self::m4is_e785();
 $m4is_v2613 ="SELECT count(*) FROM `{$m4is_e80
}` WHERE `rel_type_id` = %d;";
 $m4is_t6870 =$wpdb->get_var($wpdb->prepare($m4is_v2613, $m4is_y64165 ));
   return (bool) $m4is_t6870;
 
} static 
function m4is_z9806 (string $m4is_k52736 ): int {
  global $wpdb;
  $m4is_e80 =self::m4is_v83150();
   $m4is_v2613 ="INSERT INTO `{$m4is_e80
}` (`name`) VALUES (%s);";
   $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_k52736 );
  $wpdb->query($m4is_v2613 );
  $m4is_d07693 =$wpdb->insert_id;
   return (int) $m4is_d07693;
 
} static 
function m4is_p1520(string $m4is_k52736 ): int {
  global $wpdb;
  $m4is_e80 =self::m4is_v83150();
  $m4is_k52736 =strtolower(trim($m4is_k52736 ));
   $m4is_v2613 ="SELECT `rel_type_id` FROM `{$m4is_e80
}` WHERE `name` = %s LIMIT 1;";
   $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_k52736 );
   $m4is_d07693 =(int) $wpdb->get_var($m4is_v2613 );
  return $m4is_d07693;
 
} static 
function m4is_h664(string $m4is_k52736 ): int {
 global $wpdb;
  $m4is_k52736 =strtolower(trim($m4is_k52736 ));
  if(empty($m4is_k52736 )){
return 0;
 
} $m4is_y64165 =self::m4is_p1520($m4is_k52736 );
  if(!$m4is_y64165 ){
return 0;
 
} if(!self::m4is_v605($m4is_y64165 )){
return 0;
 
} $m4is_e80 =self::m4is_v83150();
  $m4is_v2613 ="DELETE FROM `{$m4is_e80
}` WHERE `type_id` = %d ";
  $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_y64165 );
  $m4is_m615 =$wpdb->query($m4is_v2613 );
  return (int) $m4is_m615;
 
} 
}

