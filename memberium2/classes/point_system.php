<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_a079 {
static $m4is_r1546;
 public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 
} public static 
function m4is_b6507(int $m4is_f087 =0, int $m4is_j0361 =0 ): int {
$m4is_f087 =$m4is_f087 == 0 ? self::$m4is_r1546->m4is_x66(): (int) $m4is_f087;
 $m4is_j0361 =(int) $m4is_j0361;
 if(function_exists('badgeos_get_users_points' )){
$m4is_f95063 =(int) badgeos_get_users_points($m4is_f087 );
 
}elseif(function_exists('gamipress_get_user_points' )){
$m4is_f95063 =(int) gamipress_get_user_points($m4is_f087 );
 
}else{
$m4is_l9671 =empty($m4is_j0361 )? '_memberium_points' : "_memberium_{$m4is_j0361
}_points";
 $m4is_f95063 =(int) get_user_meta($m4is_f087, $m4is_l9671, true );
 
}return $m4is_f95063;
 
} public static 
function m4is_g65(int $m4is_f087 =0, int $m4is_n69 =0, int $m4is_j0361 =0): int {
$m4is_f087 =$m4is_f087 == 0 ? self::$m4is_r1546->m4is_x66(): $m4is_f087;
 if($m4is_f087 == 0){
return 0;
 
}$m4is_j0361 =self::m4is_y60($m4is_j0361);
 if(function_exists('badgeos_update_users_points')){
$m4is_e0294 =badgeos_update_users_points($m4is_f087, $m4is_n69);
 badgeos_log_users_points($m4is_f087, $m4is_n69, $m4is_e0294, 0, 0);
 
}elseif(function_exists('gamipress_update_user_points')){
$m4is_e0294 =gamipress_update_user_points($m4is_f087, $m4is_n69);
 gamipress_log_user_points($m4is_f087, $m4is_n69, $m4is_e0294, 0, 0);
 
}else{
$m4is_l9671 =empty($m4is_j0361)? '_memberium_points' : "_memberium_{$m4is_j0361
}_points";
 $m4is_e0294 =$m4is_n69 + self::m4is_b6507($m4is_f087);
 update_user_meta($m4is_f087, $m4is_l9671, $m4is_e0294);
 
}return $m4is_e0294;
 
} public static 
function m4is_x2667($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
$m4is_f087 =isset($m4is_l62046['user_id'])? (int) $m4is_l62046['user_id']: self::$m4is_r1546->m4is_x66();
 $m4is_j0361 =isset($m4is_l62046['type'])? (int) $m4is_l62046['type']: '';
 $m4is_f95063 =self::m4is_b6507($m4is_f087, $m4is_j0361);
 return (string) $m4is_f95063;
 
}    private static 
function m4is_y60($m4is_w54186 =null): string {
if(empty($m4is_w54186)){
return '';
 
}if(function_exists('gamipress_update_user_points')){
$m4is_j0361 =gamipress_get_points_type($m4is_w54186 );
 
}else{
$m4is_j0361 =(string) $m4is_w54186;
 
}return $m4is_j0361;
 
}
}

