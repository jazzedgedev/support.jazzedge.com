<?php

/**
 * Copyright (c) 2017-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_o9186 {
 public static 
function m4is_d4861(): void {
add_action('wp_nav_menu_item_custom_fields', [__CLASS__, 'm4is_q89'], 10, 4 );
 add_action('wp_update_nav_menu_item', [__CLASS__, 'm4is_z20571'], 10, 3 );
 add_action('admin_enqueue_scripts', [__CLASS__, 'm4is_j60']);
 add_filter('clean_url', [__CLASS__, 'm4is_t328'], 99, 3 );
 
} public static 
function m4is_j60(): void {
static $m4is_d13895 =false;
 if($m4is_d13895 ){
return;
 
}m4is_e37682::m4is_c26()->m4is_a91('menu');
 $m4is_d13895 =true;
 
} public static 
function m4is_q89($m4is_k09, $m4is_c4069, $m4is_z2136, $m4is_y66291 ): void {
$m4is_a89 =self::m4is_c690();
 if(!empty($m4is_a89)&&is_array($m4is_a89)){
$m4is_r1692 =m4is_v679::m4is_c26()->m4is_v0631($m4is_k09);
 $m4is_f3967 ='';
 foreach ($m4is_a89 as $m4is_b3785 =>$m4is_q523){
$m4is_k52736 =$m4is_q523['name'];
 $m4is_a89[$m4is_b3785]['id']=esc_attr("{$m4is_k52736
}-{$m4is_k09
}");
 $m4is_a89[$m4is_b3785]['field_name']="wpal_menu[{$m4is_k52736
}][{$m4is_k09
}]";
 $m4is_a89[$m4is_b3785]['value']=isset($m4is_r1692[$m4is_k52736])? $m4is_r1692[$m4is_k52736]: '';
 $m4is_f3967 =$m4is_k52736 === 'status' ? $m4is_a89[$m4is_b3785]['value']: $m4is_f3967;
 
} static $m4is_f2096 =false;
 if(!$m4is_f2096 ){
$m4is_a27648 ='memberium/menu/access';
 $m4is_f2096 =true;
 wp_nonce_field($m4is_a27648, "_{$m4is_a27648
}_name");
 
} $m4is_j67631 ='wpal-menu-access';
 $m4is_c864 =$m4is_k09;
 $m4is_v5730 ='menu';
 include m4is_r83::m4is_c26()->m4is_x63587('core-wp-asset-access-meta.php' );
 
}
} public static 
function m4is_z20571(int $m4is_a29, int $m4is_k09, array $m4is_y66291 ){
 $m4is_a27648 ='memberium/menu/access';
 if(!isset($_POST["_{$m4is_a27648
}_name"])||!wp_verify_nonce($_POST["_{$m4is_a27648
}_name"], $m4is_a27648)){
return $m4is_a29;
 
}$m4is_r1692 =[];
 $m4is_e53897 =[];
 $m4is_i207 =false;
 $m4is_b63 =false;
 $m4is_t69852 =self::m4is_c690();
 if(is_array($m4is_t69852)&&!empty($m4is_t69852)){
$m4is_p2695 =isset($_POST['wpal_menu'])? $_POST['wpal_menu']: [];
 $m4is_e53897 =m4is_v679::m4is_c26()->m4is_v0631($m4is_k09);
 $m4is_i207 =!empty($m4is_e53897);
 $m4is_r1692 =$m4is_e53897;
 $m4is_f3967 =0;
  foreach ($m4is_t69852 as $m4is_c663 =>$m4is_q523){
$m4is_k52736 =$m4is_q523['name'];
 $m4is_d87521 =isset($m4is_p2695[$m4is_k52736])? $m4is_p2695[$m4is_k52736]: [];
 $m4is_v586 =isset($m4is_d87521[$m4is_k09])? $m4is_d87521[$m4is_k09]: '';
 $m4is_m217 =isset($m4is_e53897[$m4is_k52736])? $m4is_e53897[$m4is_k52736]: '';
 if($m4is_q523['type']=== 'select2' &&$m4is_v586 > '' ){
$m4is_v586 =trim($m4is_v586, ',');
  if($m4is_k52736 === 'memberships' &&$m4is_v586 > '' ){
$m4is_l091 =m4is_e37682::m4is_c26()->m4is_y91($m4is_v586);
 $m4is_v586 =$m4is_l091 ? $m4is_l091 : $m4is_v586;
 $m4is_r1692['any_membership']=$m4is_l091 ? 1 : 0;
 
}
}if($m4is_v586 != $m4is_m217 ){
$m4is_r1692[$m4is_k52736]=esc_attr($m4is_v586);
 $m4is_b63 =true;
 
}if($m4is_k52736 === 'status' ){
$m4is_f3967 =(int) $m4is_v586;
 
}
} if($m4is_f3967 === 1){
$m4is_r1692['logged_in_only']=1;
 $m4is_r1692['logged_out_only']=0;
 
} elseif($m4is_f3967 === 2){
$m4is_r1692['logged_in_only']=0;
 $m4is_r1692['logged_out_only']=1;
 $m4is_r1692 =m4is_e37682::m4is_c26()->m4is_c0667($m4is_r1692);
 
} else{
$m4is_r1692['logged_in_only']=0;
 $m4is_r1692['logged_out_only']=0;
 $m4is_r1692 =m4is_e37682::m4is_c26()->m4is_c0667($m4is_r1692);
 
}
}if($m4is_b63){
 if(!array_filter($m4is_r1692)){
 if($m4is_i207){
delete_post_meta($m4is_k09, '_wpal/menu/access');
 
}
} else{
update_post_meta($m4is_k09, '_wpal/menu/access', $m4is_r1692);
 
}
}m4is_r83::m4is_c26()->m4is_v26184();
 
} static 
function m4is_t328($m4is_n6062, $m4is_v53, $m4is_s90 ){
$m4is_s16968 =false !== strpos($m4is_v53, '[' );
 $m4is_l109 =false !== strpos($m4is_v53, ']' );
 return $m4is_s16968 &&$m4is_l109 ? $m4is_v53 : $m4is_n6062;
 
} public static 
function m4is_c690(): array {
static $m4is_w6305;
 if(!is_null($m4is_w6305 )){
return $m4is_w6305;
 
}$m4is_w6305 =m4is_e37682::m4is_c26()->m4is_l6016('menu' );
 return apply_filters('memberium/menu/access/fields', $m4is_w6305 );
 
}
}

