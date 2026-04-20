<?php

/**
 * Copyright (c) 2014-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_m32784 {
 public static 
function m4is_w5179($m4is_m348, $m4is_h248, $m4is_b30146 ){
$m4is_a89 =self::m4is_u602();
 foreach ($m4is_a89 as $m4is_b3785 =>$m4is_q523){
$m4is_a89[$m4is_b3785]['id']=$m4is_m348->get_field_id($m4is_q523['name']);
 $m4is_a89[$m4is_b3785]['field_name']=$m4is_m348->get_field_name($m4is_q523['name']);
 $m4is_a89[$m4is_b3785]['value']=isset($m4is_b30146[$m4is_q523['name']])? $m4is_b30146[$m4is_q523['name']]: '';
 
}$m4is_j67631 ='wpal-widget-access';
 $m4is_c864 =$m4is_m348->id;
 $m4is_v5730 ='widget';
 $m4is_f3967 =isset($m4is_b30146['status'])? $m4is_b30146['status']: '';
 $m4is_y91086 =m4is_e37682::m4is_c26()->m4is_i60238();
 include m4is_r83::m4is_c26()->m4is_x63587('core-wp-asset-access-meta.php');
 return;
 
}static 
function m4is_u2634($m4is_b30146, $m4is_e293 ){
$m4is_a89 =self::m4is_u602();
 if(is_array($m4is_a89)&&!empty($m4is_a89)){
$m4is_f3967 =0;
  foreach ($m4is_a89 as $m4is_q523){
$m4is_k52736 =$m4is_q523['name'];
 $m4is_v586 ='';
 if(isset($m4is_e293[$m4is_k52736])){
$m4is_j0361 =$m4is_q523['type'];
 $m4is_v586 =$m4is_e293[$m4is_k52736];
  if($m4is_j0361 === 'select2' &&!empty($m4is_v586)){
$m4is_v586 =trim($m4is_v586, ',');
  if($m4is_k52736 === 'memberships' &&!empty($m4is_v586)){
$m4is_l091 =m4is_e37682::m4is_c26()->m4is_y91($m4is_v586);
 $m4is_v586 =$m4is_l091 ? $m4is_l091 : $m4is_v586;
 $m4is_b30146['any_membership']=$m4is_l091 ? 1 : 0;
 
}
}if($m4is_k52736 === 'status' ){
$m4is_f3967 =(int)$m4is_v586;
 
}$m4is_b30146[$m4is_k52736]=$m4is_v586;
 
}
} if($m4is_f3967 === 1 ){
$m4is_b30146['logged_in_only']=1;
 $m4is_b30146['logged_out_only']=0;
 
} elseif($m4is_f3967 === 2 ){
$m4is_b30146['logged_in_only']=0;
 $m4is_b30146['logged_out_only']=1;
 $m4is_b30146 =m4is_e37682::m4is_c26()->m4is_c0667($m4is_b30146);
 
} else{
$m4is_b30146['logged_in_only']=0;
 $m4is_b30146['logged_out_only']=0;
 $m4is_b30146 =m4is_e37682::m4is_c26()->m4is_c0667($m4is_b30146);
 
}
}m4is_r83::m4is_c26()->m4is_v26184();
 return $m4is_b30146;
 
}static 
function m4is_m0697(){
static $m4is_j1620 =false;
 if($m4is_j1620){
return;
 
}m4is_e37682::m4is_c26()->m4is_a91('widget');
 $m4is_j1620 =true;
 
} static 
function m4is_u602(){
static $m4is_q71 =false;
 if($m4is_q71 ){
return $m4is_q71;
 
}$m4is_q71 =m4is_e37682::m4is_c26()->m4is_l6016('widget');
 return apply_filters('memberium/widget/fields', $m4is_q71 );
 
}
}

