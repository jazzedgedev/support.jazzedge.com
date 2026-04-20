<?php

/**
 * Copyright © 2017–2025 David J Bullock
 * Web Power and Light. All rights reserved.
 *
 * This file is part of the Memberium for WooCommerce integration.
 * Unauthorized copying, modification, distribution, or use of this file,
 * via any medium, is strictly prohibited without prior written permission.
 *
 * For licensing inquiries, please contact: support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_d12540 {
private array $m4is_f48;
 private array $m4is_o9835;
 private array $m4is_l58;
 private array $m4is_w60;
 private m4is_r83 $m4is_r1546;
 public const M4IS_X267 ='_memberium_checkout_redirect';
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_i702();
 $this->m4is_y1648();
 $this->m4is_f3276();
 $this->m4is_d4861();
 
} private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_f48 =['active', 'completed', 'pending-cancel', 'processing', ];
 $this->m4is_f48 =wc_get_is_paid_statuses();
 $this->m4is_o9835 =['cancelled', 'expired',  ];
 $this->m4is_l58 =['pending', 'on-hold', ];
 $this->m4is_w60 =['account_first_name' =>'FirstName', 'account_last_name' =>'LastName', 'first_name' =>'FirstName', 'last_name' =>'LastName', 'billing_address_1' =>'StreetAddress1', 'billing_address_2' =>'StreetAddress2', 'billing_city' =>'City', 'billing_company' =>'Company', 'billing_country' =>'Country', 'billing_email' =>'Email', 'billing_phone' =>'Phone1', 'billing_postcode' =>'PostalCode', 'billing_state' =>'State', 'shipping_address_1' =>'Address2Street1', 'shipping_address_2' =>'Address2Street2', 'shipping_city' =>'City2', 'shipping_country' =>'Country2', 'shipping_email' =>'Email2', 'shipping_phone' =>'Phone2', 'shipping_postcode' =>'PostalCode2', 'shipping_state' =>'State2', ];
 
} private 
function m4is_y1648(): void {
$m4is_l61035 =['m4is_c34' =>__DIR__ . '/admin', 'm4is_v46135' =>__DIR__ . '/frontend', 'memberium_woocommerce_shortcodes_class' =>__DIR__ . '/shortcodes', ];
 $this->m4is_r1546->m4is_p39($m4is_l61035 );
 
} private 
function m4is_d4861(): void {
add_action('woocommerce_order_status_changed', [$this, 'm4is_o6427'], 10, 3 );
 add_action('woocommerce_subscription_status_updated', [$this, 'm4is_q216'], 10, 3 );
 add_filter('memberium/registration/field_map', [$this, 'm4is_s48760'], 10, 1 );
 add_filter('memberium/usermeta/crm_field_maps', [$this, 'm4is_s48760'], 10, 1 );
 add_filter('memberium/usermeta/transmute', [$this, 'm4is_n692'], 10, 3 );
 add_filter('woocommerce_new_customer_data', [$this, 'm4is_y06'], 10 );
 
} private 
function m4is_f3276(): void {
if(is_admin()){
require_once __DIR__ . '/admin.php';
 m4is_c34::m4is_c26();
 
}else{
require_once __DIR__ . '/frontend.php';
 m4is_v46135::m4is_c26();
 
}
} public 
function m4is_o6427($m4is_c6016, $m4is_m43, $m4is_k49710 ): void {
$m4is_y5760 =wc_get_order($m4is_c6016 );
 $m4is_f087 =$m4is_y5760->get_user_id();
 if(!$m4is_f087 ){
return;
 
}$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
return;
 
}$m4is_w89066 =$m4is_y5760->get_items();
 if(is_array($m4is_w89066 )&&!empty($m4is_w89066 )){
foreach ($m4is_w89066 as $item_key =>$m4is_c4069 ){
 $m4is_o6480 =$m4is_c4069->get_product_id();
 $this->m4is_e98($m4is_h21895, $m4is_f087, $m4is_o6480, $m4is_c6016, $m4is_k49710, $m4is_m43 );
 
}
}
} private 
function m4is_h32($m4is_b4068 =0, $m4is_f087 =0, $m4is_e63195 ='' ): void {
$m4is_b4068 =(int) $m4is_b4068;
 $m4is_f087 =(int) $m4is_f087;
 if(empty($m4is_b4068 )||empty($m4is_f087 )||empty($m4is_e63195 )){
return;
 
}if($m4is_f087 ){
$m4is_l17096 =get_user_by('id', $m4is_f087 );
 $m4is_f087 =$m4is_l17096->ID;
 $m4is_v64 =$m4is_l17096->user_login;
 $m4is_q12678 =$m4is_l17096->user_email;
 
}else{
$m4is_f087 =0;
 $m4is_v64 ='Memberium';
 $m4is_q12678 ='';
 
}$m4is_z6401 =['comment_agent' =>'Memberium', 'comment_approved' =>1, 'comment_author_email' =>$m4is_q12678, 'comment_author_IP' =>'', 'comment_author_url' =>'', 'comment_author' =>$m4is_v64, 'comment_content' =>trim($m4is_e63195 ), 'comment_date' =>current_time('mysql' ), 'comment_parent' =>0, 'comment_post_ID' =>(int) $m4is_b4068, 'comment_type' =>'order_note', 'user_id' =>$m4is_f087, ];
 wp_insert_comment($m4is_z6401 );
 
} private 
function m4is_e98(int $m4is_h21895, int $m4is_f087, int $m4is_o6480, int $m4is_c6016, string $m4is_c31, string $m4is_m43 ): void {
$m4is_h21895 =(int) $m4is_h21895;
 $m4is_f087 =(int) $m4is_f087;
 $m4is_o6480 =(int) $m4is_o6480;
 $m4is_c6016 =(int) $m4is_c6016;
 $m4is_c31 =(string) $m4is_c31;
 $m4is_m43 =(string) $m4is_m43;
 if($m4is_c31 <> $m4is_m43 ){
if($m4is_h21895 &&$m4is_o6480){
$m4is_l9321 =$this->m4is_j143($m4is_o6480);
 $m4is_b2415 ='';
 $m4is_j67631 ='Memberium ';
 $m4is_b9631 =in_array($m4is_m43, $this->m4is_f48);
 if(in_array($m4is_c31, $this->m4is_f48 )){
if($m4is_l9321['main']){
m4is_r83::m4is_c26()->m4is_k98($m4is_l9321['main'], $m4is_h21895);
 $m4is_b2415 .= "{$m4is_j67631
} added tag {$m4is_l9321['main']
}<br>";
 
} if($m4is_l9321['canc']){
m4is_r83::m4is_c26()->m4is_k98(-abs($m4is_l9321['canc']), $m4is_h21895);
 $m4is_b2415 .= "{$m4is_j67631
} removed tag {$m4is_l9321['canc']
}<br>";
 
}if($m4is_l9321['payf']){
m4is_r83::m4is_c26()->m4is_k98(-abs($m4is_l9321['payf']), $m4is_h21895);
 $m4is_b2415 .= "{$m4is_j67631
} removed tag {$m4is_l9321['payf']
}<br>";
 
} if($m4is_l9321['susp']){
m4is_r83::m4is_c26()->m4is_k98(-abs($m4is_l9321['susp']), $m4is_h21895);
 $m4is_b2415 .= "{$m4is_j67631
} removed tag {$m4is_l9321['susp']
}<br>";
 
}
}elseif($m4is_c31 == 'failed'){
m4is_r83::m4is_c26()->m4is_k98($m4is_l9321['payf'], $m4is_h21895);
 if($m4is_l9321['main'])$m4is_b2415 .= "{$m4is_j67631
} added tag {$m4is_l9321['payf']
}<br>";
 
}elseif(in_array($m4is_c31, $this->m4is_o9835)){
m4is_r83::m4is_c26()->m4is_k98($m4is_l9321['canc'], $m4is_h21895);
 if($m4is_l9321['main'])$m4is_b2415 .= "{$m4is_j67631
} added tag {$m4is_l9321['canc']
}<br>";
 
}elseif($m4is_c31 == 'expired'){
m4is_r83::m4is_c26()->m4is_k98(0 - $m4is_l9321['main'], $m4is_h21895);
 if($m4is_l9321['main'])$m4is_b2415 .= "{$m4is_j67631
} removed tag {$m4is_l9321['main']
}<br>";
 
}elseif($m4is_c31 == 'on-hold'){
m4is_r83::m4is_c26()->m4is_k98($m4is_l9321['susp'], $m4is_h21895);
 if($m4is_l9321['main'])$m4is_b2415 .= "{$m4is_j67631
} added tag {$m4is_l9321['susp']
}<br>";
 
}$this->m4is_h32($m4is_c6016, $m4is_f087, $m4is_b2415);
 
}
}
} private 
function m4is_g69(int $m4is_f087, int $m4is_o6480, int $m4is_e16784 ): bool {
$m4is_f087 =(int) $m4is_f087;
 $m4is_o6480 =(int) $m4is_o6480;
 $m4is_e16784 =(int) $m4is_e16784;
 $m4is_y66291 =['subscriptions_per_page' =>-1, 'customer_id' =>$m4is_f087, 'product_id' =>$m4is_o6480, 'subscription_status' =>$this->m4is_f48, ];
 $m4is_d74 =wcs_get_subscriptions($m4is_y66291 );
 unset($m4is_d74[$m4is_e16784]);
 return (bool) count($m4is_d74 );
 
} private 
function m4is_b29(int $m4is_f087, int $m4is_o6480, int $m4is_d621 ): bool {
$m4is_y66291 =['customer_id' =>$m4is_f087, 'return' =>'ids', 'status' =>$this->m4is_f48, 'product_id' =>$m4is_o6480, ];
 $m4is_p98327 =wc_get_orders($m4is_y66291 );
  $m4is_p98327 =array_diff($m4is_p98327, [$m4is_d621 ]);
 foreach($m4is_p98327 as $m4is_c6016 ){
$m4is_y5760 =wc_get_order($m4is_c6016 );
 $m4is_w89066 =$m4is_y5760->get_items();
 foreach($m4is_w89066 as $m4is_c4069 ){
 if($m4is_c4069->get_product_id()== $m4is_o6480 ){
return true;
 
}
}
}return false;
 
}      private 
function m4is_e71536(): array {
static $m4is_z64709;
 return $m4is_z64709 ??= $m4is_z64709 ?? ['canc' =>'_memberium_canc_tag', 'main' =>'_memberium_main_tag', 'payf' =>'_memberium_payf_tag', 'susp' =>'_memberium_susp_tag', ];
 
} private 
function m4is_j143(int $m4is_o6480 ): array {
$m4is_l9321 =[];
 if(empty($m4is_o6480 )){
return $m4is_l9321;
 
}$m4is_l79562 =get_post_meta($m4is_o6480 );
 if(!is_array($m4is_l79562 )){
return $m4is_l9321;
 
}foreach($this->m4is_e71536()as $m4is_k52736 =>$m4is_l9671 ){
$m4is_l9321[$m4is_k52736]=empty($m4is_l79562[$m4is_l9671][0])? 0 : (int) $m4is_l79562[$m4is_l9671][0];
 
}return $m4is_l9321;
 
} public 
function m4is_y06(array $m4is_t1826 ): array {
if(empty($m4is_t1826['user_pass'])){
$m4is_t1826['user_pass']=m4is_r83::m4is_c26()->m4is_a601();
 
}if(isset($_POST['account_first_name'])&&!empty($_POST['account_first_name'])){
$m4is_t1826['first_name']=trim($_POST['account_first_name']);
 
}if(isset($_POST['account_last_name'])&&!empty($_POST['account_last_name'])){
$m4is_t1826['last_name']=trim($_POST['account_last_name']);
 
}if(get_option('woocommerce_registration_generate_username' )!== 'no' ){
if(!username_exists($m4is_t1826['user_email'])){
$m4is_t1826['user_login']=$m4is_t1826['user_email'];
 
}
}return $m4is_t1826;
 
} public 
function m4is_q216(WC_Subscription $m4is_c12, string $m4is_k49710, string $m4is_m43 ): void {
$m4is_u66 =$m4is_c12->get_id();
 $m4is_f087 =$m4is_c12->get_user_id();
 if(!$m4is_f087){
$this->m4is_h32($m4is_u66, 0, 'Memberium skipped applying tags due to no assigned WordPress user.' );
 return;
 
}$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895){
$this->m4is_h32($m4is_u66, $m4is_f087, 'Memberium skipped applying tags due to no assigned CRM contact.' );
 return;
 
}$m4is_w89066 =$m4is_c12->get_items();
 if(empty($m4is_w89066 )||!is_array($m4is_w89066 )){
return;
 
}foreach($m4is_w89066 as $m4is_h256 =>$m4is_c4069 ){
$m4is_o6480 =$m4is_c4069->get_product_id();
 if(!$m4is_o6480 ){
continue;
 
}if(!in_array($m4is_k49710, $this->m4is_f48 )){
$m4is_v6650 =$this->m4is_g69($m4is_f087, $m4is_o6480, $m4is_u66 );
 if($m4is_v6650 ){
$this->m4is_h32($m4is_u66, $m4is_f087, 'Memberium skipped applying deactivation tag due to other active subscriptions.' );
 continue;
 
}
}$this->m4is_e98($m4is_h21895, $m4is_f087, $m4is_o6480, $m4is_u66, $m4is_k49710, $m4is_m43 );
 
}
} public 
function m4is_s48760(array $m4is_c781 ): array {
return array_merge($m4is_c781, $this->m4is_w60 );
 
} private 
function m4is_a637($m4is_v586, $m4is_r637, $m4is_h21895 =0 ){
return $m4is_v586;
 
} public 
function m4is_n692($m4is_v586, $m4is_r637, $m4is_h21895 =0 ){
if(in_array($m4is_r637, ['Country', 'Country2', 'Country3'])){
if(strlen($m4is_v586)< 4){
$m4is_v586 =m4is_q6082::m4is_e669($m4is_v586);
 
}
}elseif(in_array($m4is_r637, ['PostalCode', 'PostalCode2', 'PostalCode3'])){
$m4is_v586 =strtoupper($m4is_v586);
 
}return trim($m4is_v586);
 
}  
}

