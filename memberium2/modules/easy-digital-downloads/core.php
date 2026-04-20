<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r96' )||die();
 final 
class m4is_r96 {
private $m4is_r1546;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_d4861();
 $this->m4is_f3276();
 
}   private 
function m4is_f3276(){
if(is_admin()){
include_once __DIR__ . '/admin.php';
 m4is_a10::m4is_c26();
 
}
}   public 
function m4is_n6816(){
return ['_memberium_access_tag', '_memberium_canc_tag', '_memberium_main_tag', '_memberium_payf_tag', '_memberium_trial_tag', ];
 
}public 
function m4is_x65($m4is_b4068){
return ['main' =>(string) get_post_meta($m4is_b4068, '_memberium_access_tag', true), 'trial' =>(string) get_post_meta($m4is_b4068, '_memberium_trial_tag', true), 'payf' =>(string) get_post_meta($m4is_b4068, '_memberium_payf_tag', true), 'canc' =>(string) get_post_meta($m4is_b4068, '_memberium_canc_tag', true), ];
 
}public 
function m4is_z794($m4is_o6480, $m4is_f087 =0 ){
$m4is_w04 =['active', 'trialling', 'failing', ];
 $m4is_f087 =empty($m4is_f087 )? $this->m4is_r1546->m4is_x66(): $m4is_f087;
  $m4is_k92 =new \EDD_Recurring_Subscriber($m4is_f087, true );
 $m4is_d74 =$m4is_k92->get_subscriptions($m4is_o6480, $m4is_w04 );
 return count($m4is_d74)> 1;
 
}   
function m4is_r69($m4is_i658){
$m4is_h21895 =0;
 $m4is_f40 =edd_get_payment_meta($m4is_i658);
 $m4is_f087 =empty($m4is_f40['user_info']['id'])? 0 : $m4is_f40['user_info']['id'];
 $m4is_k17856 =edd_get_payment_meta_cart_details($m4is_i658);
  if(!$m4is_f087){
$m4is_q12678 =empty($m4is_f40['user_info']['email'])? '' : $m4is_f40['user_info']['email'];
 if($m4is_q12678){
$m4is_l17096 =get_user_by('email', $m4is_q12678);
 $m4is_f087 =is_a($m4is_l17096, 'WP_User' )?$m4is_l17096->ID : 0;
 
}
} if($m4is_f087){
$m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087);
 if($m4is_h21895){
$m4is_s15437 =[];
 foreach ($m4is_k17856 as $m4is_l13){
$m4is_i1450 =empty($m4is_l13['id'])? 0 : $m4is_l13['id'];
 $m4is_j15 =$this->m4is_x65($m4is_i1450);
 $m4is_s15437[]=$m4is_j15['main'];
 $m4is_s15437[]=(0 - $m4is_j15['canc']);
 $m4is_s15437[]=(0 - $m4is_j15['payf']);
 
}$m4is_s15437 =array_unique($m4is_s15437, SORT_NUMERIC);
 m4is_r83::m4is_c26()->m4is_k98($m4is_s15437, $m4is_h21895 );
 
}
}
}
function m4is_e38275($m4is_u66, $m4is_l91805){
$m4is_e87 =empty($m4is_l91805['customer_id'])? 0 : $m4is_l91805['customer_id'];
 $m4is_c31 =empty($m4is_l91805['status'])? '' : $m4is_l91805['status'];
 $m4is_o6480 =empty($m4is_l91805['product_id'])? 0 : $m4is_l91805['product_id'];
 $m4is_f087 =empty($m4is_l91805['user_id'])? 0 : $m4is_l91805['user_id'];
 if($m4is_f087){
$m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087);
 if($m4is_h21895){
$m4is_j15 =$this->m4is_x65($m4is_o6480);
 $m4is_s15437 =[];
 if($m4is_c31 == 'trialling'){
$m4is_s15437[]=$m4is_j15['trial'];
 
}$m4is_s15437 =array_unique($m4is_s15437, SORT_NUMERIC);
 m4is_r83::m4is_c26()->m4is_k98($m4is_s15437, $m4is_h21895 );
 
}
}
}
function m4is_h834($m4is_m43, $m4is_k49710, $m4is_c12 ){
if($m4is_m43 !== $m4is_k49710){
$m4is_e87 =$m4is_c12->customer_id;
 $m4is_e32189 =new EDD_Customer($m4is_e87);
 $m4is_f087 =$m4is_e32189->user_id;
 $m4is_o6480 =$m4is_c12->product_id;
 $m4is_x104 =$self->m4is_z794($m4is_o6480, $m4is_f087 );
 if($m4is_f087){
$m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087);
 if($m4is_h21895){
$m4is_s15437 =[];
 $m4is_j15 =$this->m4is_x65($m4is_o6480);
 if($m4is_k49710 == 'active'){
$m4is_s15437[]=$m4is_j15['main'];
 $m4is_s15437[]=(0 - $m4is_j15['trial']);
 $m4is_s15437[]=(0 - $m4is_j15['canc']);
 $m4is_s15437[]=(0 - $m4is_j15['payf']);
 
}elseif($m4is_k49710 == 'cancelled'){
$m4is_s15437[]=$m4is_j15['canc'];
 $m4is_s15437[]=(0 - $m4is_j15['trial']);
 
}elseif($m4is_k49710 == 'expired'){
$m4is_s15437[]=$m4is_j15['payf'];
 $m4is_s15437[]=(0 - $m4is_j15['trial']);
 
}elseif($m4is_k49710 == 'completed'){
$m4is_s15437[]=(0 - $m4is_j15['main']);
 $m4is_s15437[]=(0 - $m4is_j15['trial']);
 
}elseif($m4is_k49710 == 'failing'){
 
}m4is_r83::m4is_c26()->m4is_k98($m4is_s15437, $m4is_h21895 );
 
}
}
}
}   private 
function m4is_d4861(){
add_action('edd_complete_purchase', [$this, 'm4is_r69']);
 add_action('edd_subscription_post_create', [$this, 'm4is_e38275'], 10, 2);
 add_action('edd_subscription_status_change', [$this, 'm4is_h834'], 10, 3 );
 
}
}

