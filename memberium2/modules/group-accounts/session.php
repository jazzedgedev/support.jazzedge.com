<?php

/**
 * Copyright (c) 2023-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_r82473 {
private static array $m4is_e0213;
 private static object $m4is_e6426;
 private static object $m4is_r1546;
 private static object $m4is_a186;
 private 
function __construct(){
 
} public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_a186 =m4is_u7102::m4is_c26();
 self::$m4is_e0213 =self::$m4is_a186->m4is_f27408();
 self::$m4is_e6426 =self::$m4is_r1546->m4is_b32198();
 
}    public static 
function m4is_z67842(array $m4is_k824 ): array {
if(empty($m4is_k824['memb_user']['crm_id'])){
return $m4is_k824;
 
}$m4is_k824['umbrella']=[];
 $m4is_p6457 =explode(',', $m4is_k824['memb_user']['tags']?? '' );
 $m4is_r8695 =explode(',', self::$m4is_a186->m4is_d412());
 $m4is_w01347 =(int) (bool) array_intersect($m4is_p6457, $m4is_r8695);
 $m4is_i297 =self::$m4is_a186->m4is_y8645($m4is_k824['memb_user']['user_id'], $m4is_k824['keap']['contact']);
 $m4is_k824['umbrella']['is_parent']=$m4is_w01347;
 $m4is_k824['umbrella']['max_children']=$m4is_i297;
 return $m4is_k824;
 
}    public static 
function m4is_j43(array $m4is_i935 =[]): array {
 if(empty($m4is_i935 )||self::$m4is_a186->m4is_y8765()){
return $m4is_i935;
 
} $m4is_l9321 =isset($m4is_i935['Groups'])? $m4is_i935['Groups']: '';
 if(self::$m4is_a186->m4is_l274($m4is_l9321 )){
return $m4is_i935;
 
} self::$m4is_e0213 =self::$m4is_a186->m4is_f27408();
 $m4is_c897 =self::$m4is_a186->m4is_s54627();
 $m4is_z54 =self::$m4is_a186->m4is_s54627();
 $m4is_m18965 =self::$m4is_a186->m4is_h106();
  $m4is_w574 =empty($m4is_i935[$m4is_c897])? '' : $m4is_i935[$m4is_c897];
 if(empty($m4is_w574 )){
return $m4is_i935;
 
} $m4is_y66291 =['parent_id' =>$m4is_w574,  'contact_id' =>$m4is_i935['Id'],  'contact' =>$m4is_i935,  ];
 $m4is_i341 =self::$m4is_a186->m4is_a6938($m4is_y66291 );
 if(!$m4is_i341 ){
return $m4is_i935;
 
} $m4is_y66291 =['contact_id' =>$m4is_i341,  'cache_ttl' =>self::$m4is_a186->m4is_l2370() ];
 self::$m4is_r1546->m4is_n361($m4is_y66291 );
  $m4is_i935['!parent_id']=$m4is_i341;
 $m4is_i01365 =m4is_p40::m4is_p67($m4is_i341, false, true );
    $m4is_q25 =array_filter(explode(',', self::$m4is_a186->m4is_p782()));
  if(!empty($m4is_q25 )){
foreach($m4is_q25 as $m4is_r637 ){
if(empty($m4is_i935[$m4is_r637])){
$m4is_i935[$m4is_r637]=$m4is_i01365[$m4is_r637]?? '';
 
}
}
}   $m4is_a146 =array_filter(explode(',', self::$m4is_e0213['parent_tags']));
  $m4is_r8695 =isset($m4is_i01365['Groups'])? array_filter(explode(',', $m4is_i01365['Groups'])): [];
 $m4is_y1274 =isset($m4is_i01365[$m4is_m18965])? array_filter(explode(',', $m4is_i01365[$m4is_m18965])): [];
 $m4is_v4638 =array_filter(explode(',', self::$m4is_a186->m4is_p6046()));
 $m4is_x491 =self::$m4is_e0213['tag_translation'];
 $m4is_r325 =isset(self::$m4is_e0213['whitelist_memberships'])? array_filter(explode(',', self::$m4is_e0213['whitelist_memberships'])): [];
 $m4is_r8695 =array_diff($m4is_r8695, $m4is_a146 );
   if(!empty(self::$m4is_e0213['tag_whitelist'])||!empty(self::$m4is_e0213['whitelist_memberships'])){
 $m4is_r325 =array_filter(explode(',', self::$m4is_e0213['tag_whitelist']));
  if(!empty(self::$m4is_e0213['whitelist_memberships'])){
 $m4is_m96240 =self::$m4is_r1546->m4is_j498();
 $m4is_m96240 =$m4is_m96240['memberships'];
  foreach($m4is_m96240 as $m4is_w64 ){
 $m4is_r325[]=(int) $m4is_w64['main_id'];
 $m4is_r325[]=(int) $m4is_w64['addltag_ids'];
 $m4is_r325[]=(int) $m4is_w64['payf_id'];
 $m4is_r325[]=(int) $m4is_w64['cancel_id'];
 $m4is_r325[]=(int) $m4is_w64['suspend_id'];
 
} unset($m4is_m96240, $m4is_w64 );
  $m4is_r325 =array_filter($m4is_r325 );
 
} $m4is_r8695 =array_intersect($m4is_r8695, $m4is_r325 );
 
}else{
    $m4is_r8695 =array_diff($m4is_r8695, $m4is_v4638, $m4is_y1274 );
 
}  if(is_array($m4is_x491 )){
 foreach($m4is_x491 as $m4is_o129 =>$m4is_f574 ){
 $m4is_l9671 =array_search($m4is_o129, $m4is_r8695 );
  if($m4is_l9671 !== false ){
 $m4is_r8695[$m4is_l9671]=$m4is_f574;
 
}
}
}  $m4is_i935['Groups']=$m4is_i935['Groups']?? '';
 $m4is_i935['Groups']=implode(',', array_unique(array_filter(array_merge(explode(',', $m4is_i935['Groups']), $m4is_r8695 ))));
 return $m4is_i935;
 
} public static 
function m4is_q53702(array $m4is_i935 ): array {
$m4is_v61358 =self::$m4is_a186->m4is_s54627();
 if(!empty($m4is_i935[$m4is_v61358])){
return $m4is_i935;
 
}$m4is_l9321 =isset($m4is_i935['Groups'])? $m4is_i935['Groups']: '';
 if(!self::$m4is_a186->m4is_l274($m4is_l9321 )){
return $m4is_i935;
 
}$m4is_g249 =self::$m4is_a186->m4is_d6406()? self::m4is_y92(): $m4is_i935[$m4is_v61358];
 $m4is_i935[$m4is_v61358]=$m4is_g249;
 self::$m4is_r1546->m4is_s56($m4is_v61358, $m4is_g249, $m4is_i935['Id']);
  return $m4is_i935;
 
} private static 
function m4is_y92(){
return uniqid('prnt-', true );
 
}    public static 
function m4is_p8901(int $m4is_f087, array $m4is_k824 ): void {
if(user_can($m4is_f087, 'manage_options' )){
return;
 
}if(!self::$m4is_a186->m4is_r195($m4is_f087 )){
return;
 
} $m4is_l43966 ='memberium/group-accounts/membership/active';
 $m4is_s654 =(int) (!empty($m4is_k824['memb_user']['membership_tags']));
 $m4is_q6219 =get_user_meta($m4is_f087, $m4is_l43966, true );
 if($m4is_q6219 <> $m4is_s654 ){
update_user_meta($m4is_f087, $m4is_l43966, $m4is_s654 );
 self::m4is_l054($m4is_k824 );
 
}
} public static 
function m4is_l054(array $m4is_k824 ): void {
global $wpdb;
 $m4is_z765 =intval(self::$m4is_e0213['active_child_tag']?? 0 );
 $m4is_w01347 =(bool) ($m4is_k824['umbrella']['is_parent']?? 0 );
 if(!$m4is_z765 ||!$m4is_w01347 ){
return;
 
}$m4is_y7043 =!empty($m4is_k824['memb_user']['membership_tags']);
 $m4is_o80491 =$m4is_k824['umbrella']['max_children']?? 0;
 $m4is_f087 =$m4is_k824['memb_user']['user_id'];
 $m4is_r8695 =explode(',' , $m4is_k824['keap']['contact']['groups']?? '' );
 $m4is_e80 =m4is_u81::m4is_a6804();
 if($m4is_y7043 === false ||$m4is_o80491 === 0 ){
 $m4is_v2613 ="UPDATE %i SET `active` = 0, `sync` = 0 WHERE `parent_uid` = %d AND `active` = 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_f087 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 
}if($m4is_y7043 === true &&$m4is_o80491 > 0 ){
 $m4is_v2613 ="SELECT `child_uid` FROM %i WHERE `parent_uid` = %d AND `active` = 1 ORDER BY `id` ASC LIMIT %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_f087, $m4is_o80491 );
 $m4is_g3486 =$wpdb->get_col($m4is_v2613 );
 $m4is_q69832 =count($m4is_g3486 );
  $m4is_v2613 ="SELECT `child_uid` FROM %i WHERE `parent_uid` = %d AND `active` = 0 ORDER BY `id` ASC LIMIT %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_f087, ($m4is_o80491 - $m4is_q69832 ));
 $m4is_b6438 =$wpdb->get_col($m4is_v2613 );
 if(!empty($m4is_b6438 )){
 $m4is_v2613 ="UPDATE %i SET `active` = 1, `sync` = 0 WHERE `child_uid` IN ( " . implode(',', $m4is_b6438 ). " )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 
} $m4is_h72069 =array_merge($m4is_g3486, $m4is_b6438 );
 $m4is_v2613 ="UPDATE %i SET `active` = 0, `sync` = 0 WHERE `parent_uid` = %d AND `active` = 1 AND `child_uid` NOT IN ( " . implode(',', $m4is_h72069 ). " )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 
}self::m4is_k6873();
 
}     private static 
function m4is_i01735(): void {
global $wpdb;
 $m4is_e80 =m4is_u81::m4is_a6804();
 $m4is_v2613 ="DELETE FROM  %i WHERE `parent_uid` = 0 AND `sync` = 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80 );
 $m4is_u712 =$wpdb->query($m4is_v2613 );
 
} private static 
function m4is_d430(): void {
global $wpdb;
 $m4is_e80 =m4is_u81::m4is_a6804();
 $m4is_v2613 ="UPDATE %i SET `sync` = 1 WHERE `sync` = 0";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80 );
 $m4is_u712 =$wpdb->query($m4is_v2613 );
 
} public static 
function m4is_k6873(): void {
global $wpdb;
 $m4is_z765 =self::$m4is_e0213['active_child_tag']?? 0;
 if(!$m4is_z765 ){
self::m4is_d430();
 return;
 
}$m4is_w314 =100;
 $m4is_b0528 =0 - self::$m4is_e0213['active_child_tag'];
 $m4is_e80 =m4is_u81::m4is_a6804();
 $m4is_v2613 ="SELECT `child_uid` FROM %i WHERE `active` = 1 AND `sync` = 0 ORDER BY `id` ASC LIMIT %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_w314 );
 $m4is_b6438 =$wpdb->get_col($m4is_v2613 );
 if(count($m4is_b6438 )){
 $m4is_l8931 =self::m4is_u13($m4is_b6438 );
 $m4is_u6591 =self::$m4is_e6426->elf_add_remove_tag_contacts($m4is_z765, $m4is_l8931 );
  $m4is_v2613 ="UPDATE %i SET `sync` = 1 WHERE `child_uid` IN ( " . implode(',', $m4is_b6438 ). " )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 
} $m4is_v2613 ="SELECT `child_uid` FROM %i WHERE `active` = 0 AND `sync` = 0 ORDER BY `id` ASC LIMIT %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_w314 );
 $m4is_b6438 =$wpdb->get_col($m4is_v2613 );
 if(count($m4is_b6438 )){
 $m4is_l8931 =self::m4is_u13($m4is_b6438 );
 $m4is_d5472 =self::$m4is_e6426->elf_add_remove_tag_contacts($m4is_b0528, $m4is_l8931 );
  $m4is_v2613 ="UPDATE %i SET `sync` = 1 WHERE `child_uid` IN ( " . implode(',', $m4is_b6438 ). " )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 
}self::m4is_i01735();
 
} private static 
function m4is_u13(array $m4is_c3205 ): array {
$m4is_w2368 =[];
 foreach($m4is_c3205 as $m4is_t6056 ){
$m4is_w2368[]=(int) m4is_p40::m4is_w58096($m4is_t6056 );
 
}return array_filter($m4is_w2368 );
 
}    public static 
function m4is_y14($m4is_i935 =[]){
$m4is_r17206 =self::$m4is_e0213['teams']?? [];
 if(empty($m4is_r17206 )){
return $m4is_i935;
 
}if(self::$m4is_a186->m4is_y8765()){
return $m4is_i935;
 
}$m4is_h21895 =$m4is_i935['Id']?? 0;
 $m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 if(empty($m4is_f087 )){
return $m4is_i935;
 
}$m4is_r66 =m4is_l870::m4is_e38($m4is_f087 );
 if(empty($m4is_r66 )){
return $m4is_i935;
 
}$m4is_l9321 =explode(',', $m4is_i935['Groups']?? '' );
 foreach($m4is_r66 as $m4is_z30692 ){
$m4is_k66 =m4is_l870::m4is_t8025($m4is_z30692 );
 $m4is_l9321 =array_merge($m4is_l9321, $m4is_k66 );
 
}$m4is_l9321 =array_filter($m4is_l9321 );
 sort($m4is_l9321 );
 $m4is_i935['Groups']=implode(',', array_unique(array_filter($m4is_l9321 )));
  return $m4is_i935;
 
}
}

