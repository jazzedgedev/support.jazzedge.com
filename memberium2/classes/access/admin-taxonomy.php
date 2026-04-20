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
class m4is_m749 {
private static $m4is_f015;
  private static $m4is_d913;
  static 
function m4is_p20 (){
$m4is_f015 =isset($_REQUEST['taxonomy'])? $_REQUEST['taxonomy']: false;
 if(!$m4is_f015 ){
return;
 
}  add_action('edited_term_taxonomy', [__CLASS__, 'm4is_u69864'], 10, 2 );
 $m4is_l87469 =m4is_v679::m4is_c26()->m4is_t68274();
 if(!in_array($m4is_f015, $m4is_l87469 )){
return;
 
}self::$m4is_f015 =$m4is_f015;
 self::$m4is_d913 =isset($_REQUEST['tag_ID'])? $_REQUEST['tag_ID']: 0;
 add_action('admin_enqueue_scripts', [__CLASS__, 'm4is_l90178']);
 add_action("{$m4is_f015
}_edit_form_fields", [__CLASS__, 'm4is_r6456'], 10, 2 );
 
} static 
function m4is_l90178(){
static $m4is_t6834 =false;
 if(!$m4is_t6834 ){
m4is_e37682::m4is_c26()->m4is_a91('taxonomy' );
 $m4is_t6834 =true;
 
}
} static 
function m4is_r6456($m4is_p786, $m4is_f015 ){
 static $m4is_f2096 =false;
 $m4is_r538 =$m4is_p786->term_id;
 $m4is_a89 =self::m4is_j19470();
 if(is_array($m4is_a89 )&&!empty($m4is_a89 )){
$m4is_r1692 =m4is_v679::m4is_c26()->m4is_r89563($m4is_r538 );
 $m4is_f3967 ='';
 $m4is_p1084 ='';
 foreach ($m4is_a89 as $m4is_b3785 =>$m4is_q523 ){
$m4is_k52736 =$m4is_q523['name'];
 $m4is_a89[$m4is_b3785]['id']=esc_attr("{$m4is_k52736
}-{$m4is_r538
}" );
 $m4is_a89[$m4is_b3785]['field_name']="wpal_taxonomy[$m4is_k52736]";
 $m4is_a89[$m4is_b3785]['value']=isset($m4is_r1692[$m4is_k52736])? $m4is_r1692[$m4is_k52736]: '';
 $m4is_f3967 =$m4is_k52736 === 'status' ? $m4is_a89[$m4is_b3785]['value']: $m4is_f3967;
 $m4is_p1084 =$m4is_k52736 === 'prohibited_action' ? $m4is_a89[$m4is_b3785]['value']: $m4is_p1084;
 
}if(!$m4is_f2096 ){
$m4is_a27648 ='memberium/taxonomy/access';
 $m4is_f2096 =true;
 wp_nonce_field($m4is_a27648, "_{$m4is_a27648
}_name" );
 
} $m4is_j67631 ='wpal-taxonomy-access';
 $m4is_c864 =$m4is_r538;
 $m4is_v5730 ='taxonomy';
 echo "</tbody><tbody class=\"memberium-taxonomy-access-tbody\" data-prohibited-action=\"{$m4is_p1084
}\">";
 echo "<tr><td colspan=\"2\">";
 include m4is_r83::m4is_c26()->m4is_x63587('core-wp-asset-access-meta.php' );
 echo "</td></tr></tbody>";
 
}
}static 
function m4is_u69864($m4is_r538, $m4is_t742 ){
$m4is_r1692 =[];
 $m4is_e53897 =[];
 $m4is_i207 =false;
 $m4is_b63 =false;
 $m4is_t69852 =self::m4is_j19470();
 if(is_array($m4is_t69852 )&&!empty($m4is_t69852 )){
$m4is_p2695 =isset($_POST['wpal_taxonomy'])? $_POST['wpal_taxonomy']: [];
 $m4is_e53897 =m4is_v679::m4is_c26()->m4is_r89563($m4is_r538 );
 $m4is_i207 =!empty($m4is_e53897 );
 $m4is_r1692 =[];
 $m4is_f3967 =0;
 $m4is_p1084 ='';
  foreach ($m4is_t69852 as $m4is_c663 =>$m4is_q523 ){
$m4is_k52736 =$m4is_q523['name'];
 $m4is_v586 =isset($m4is_p2695[$m4is_k52736])? $m4is_p2695[$m4is_k52736]: [];
 $m4is_m217 =isset($m4is_e53897[$m4is_k52736])? $m4is_e53897[$m4is_k52736]: '';
 if($m4is_q523['type']=== 'select2' &&!empty($m4is_v586 )){
$m4is_v586 =trim($m4is_v586, ',' );
  if($m4is_k52736 === 'memberships' &&!empty($m4is_v586 )){
$m4is_l091 =m4is_e37682::m4is_c26()->m4is_y91($m4is_v586 );
 $m4is_v586 =$m4is_l091 ? $m4is_l091 : $m4is_v586;
 $m4is_r1692['any_membership']=$m4is_l091 ? 1 : 0;
 
}
}if($m4is_v586 != $m4is_m217 ){
$m4is_r1692[$m4is_k52736]=esc_attr($m4is_v586 );
 $m4is_b63 =true;
 
}else{
$m4is_r1692[$m4is_k52736]=$m4is_m217;
 
}if($m4is_k52736 === 'status' ){
$m4is_f3967 =(int) $m4is_v586;
 
}if($m4is_k52736 === 'prohibited_action' ){
$m4is_v586 =empty($m4is_v586 )? '' : $m4is_v586;
 $m4is_p1084 =$m4is_r1692[$m4is_k52736]=$m4is_v586;
 
}if($m4is_k52736 === 'redirect_url' ){
 if(esc_url_raw($m4is_v586 )!= $m4is_v586 ){
$m4is_r1692['prohibited_action']=$m4is_p1084 ='';
 
}
}
} if($m4is_f3967 === 1 ){
$m4is_r1692['logged_in_only']=1;
 $m4is_r1692['logged_out_only']=0;
 
} elseif($m4is_f3967 === 2 ){
$m4is_r1692['logged_in_only']=0;
 $m4is_r1692['logged_out_only']=1;
 $m4is_r1692 =m4is_e37682::m4is_c26()->m4is_c0667($m4is_r1692 );
 
} else{
$m4is_r1692['logged_in_only']=0;
 $m4is_r1692['logged_out_only']=0;
 $m4is_r1692 =m4is_e37682::m4is_c26()->m4is_c0667($m4is_r1692 );
 
} if((int) $m4is_p1084 === 0 ){
unset($m4is_r1692['redirect_url']);
 $m4is_b63 =true;
 
}
}if($m4is_b63 ){
$m4is_j09 ='_wpal/taxonomy/access';
  if(!array_filter($m4is_r1692 )){
if($m4is_i207 ){
delete_term_meta($m4is_r538, $m4is_j09 );
  
}
}else{
update_term_meta($m4is_r538, $m4is_j09, $m4is_r1692 );
  
}
}m4is_r83::m4is_c26()->m4is_v26184();
 
}static 
function m4is_j19470(){
static $m4is_o95 =false;
 if($m4is_o95 ){
return $m4is_o95;
 
}$m4is_o95 =m4is_e37682::m4is_c26()->m4is_l6016('taxonomy' );
 return apply_filters('memberium/taxonomy/access/fields', $m4is_o95 );
 
}
}

