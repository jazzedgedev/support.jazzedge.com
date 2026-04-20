<?php

/**
 * Copyright (c) 2017-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_m08356 {
static private object $m4is_r1546;
 static private bool $m4is_r02639;
 static private int $m4is_f087;
  static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_f087 =(int) get_current_user_id();
 self::$m4is_r02639 =!m4is_s52::m4is_w74();
 
} static 
function m4is_s348($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(!function_exists('badgeos_award_achievement_to_user' )){
return '';
 
}$m4is_y642 =['id' =>0,  'uid' =>self::$m4is_f087,  ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}if(!class_exists('BadgeOS' )){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['id']=(int) $m4is_l62046['id'];
 $m4is_y718 =(int) $m4is_l62046['id'];
 $m4is_f087 =(int) trim($m4is_l62046['uid']);
 if(!$m4is_f087 ||!$m4is_y718 ){
return '';
 
}$m4is_v687 =array_filter(explode(',', get_user_meta($m4is_f087, 'memberium_achievement_uids', true)));
  if(in_array($m4is_y718, $m4is_v687 )){
return '';
 
}badgeos_award_achievement_to_user($m4is_y718, $m4is_f087 );
 $m4is_v687 =array_filter(explode(',', get_user_meta($m4is_f087, 'memberium_achievement_uids', true)));
 $m4is_v687[]=$m4is_y718;
 $m4is_v687 =array_unique($m4is_v687 );
 sort($m4is_v687 );
 $m4is_v687 =implode(',', $m4is_v687 );
 update_user_meta($m4is_f087, 'memberium_achievement_uids', $m4is_v687 );
 return '';
 
} static 
function m4is_s43708($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return 'n/a';
 
}if(!class_exists('BadgeOS')){
return '';
 
}m4is_j586::m4is_x7134();
 if(!is_user_logged_in()){
return '';
 
}$m4is_v3458 ='';
 $m4is_s740 =get_current_blog_id();
 $m4is_e594 =get_user_meta(self::$m4is_r1546->m4is_x66(), '_badgeos_achievements', true);
 if(empty($m4is_e594)){
return '';
 
}$m4is_e594 =$m4is_e594[$m4is_s740];
 $m4is_o86564 =[];
 if(is_array($m4is_e594)){
foreach ($m4is_e594 as $achievement){
@$m4is_o86564[$achievement->ID]=$achievement->ID;
 
}
}unset($m4is_e594);
 $m4is_o86564 =array_flip($m4is_o86564);
 foreach ($m4is_o86564 as $m4is_c4069){
$m4is_v3458 .= '[badgeos_achievement id=' . $m4is_c4069 . ']';
 
}$m4is_o498 =do_shortcode($m4is_v3458);
 return $m4is_o498;
 
} static 
function m4is_z019($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(!class_exists('BadgeOS' )){
return '';
 
}if(!function_exists('badgeos_revoke_achievement_from_user' )){
return '';
 
}if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['id' =>0,  'uid' =>self::$m4is_f087,  ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_f087 =(int) $m4is_l62046['uid'];
 $m4is_d07693 =(int) $m4is_l62046['id'];
 if(!$m4is_f087 ){
return '';
 
}if($m4is_d07693 ){
badgeos_revoke_achievement_from_user($m4is_d07693 , $m4is_f087 );
 if(!empty($m4is_f087 )){
$m4is_c3205 =array_filter(explode(',', get_user_meta(get_current_user_id(), 'memberium_achievement_uids', true )));
 if(($m4is_l9671 =array_search($m4is_f087, $m4is_c3205 ))!== false ){
unset($m4is_c3205[$m4is_l9671]);
 
}$m4is_c3205 =implode(',', array_unique($m4is_c3205 ));
 update_user_meta(get_current_user_id(), 'memberium_achievement_uids', $m4is_c3205 );
 
}
}return '';
 
}
}

