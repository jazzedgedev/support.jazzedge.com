<?php
 class_exists('m4is_r83')||die();
 final 
class m4is_y531 {
private $marketplace_version ='1.0.0';
 protected $marketplace_feed_url ='https://licenseserver.webpowerandlight.com/memberium-is/marketplace.php';
 private $marketplace_admin_slug ='memberium-marketplace';
 private $marketplace_opt_key ='memberium/marketplace';
 private $marketplace_cached_time =60 * 5;
 
function __construct(){
 add_action('admin_menu', [$this, 'm4is_i62634'], PHP_INT_MAX);
 add_action('admin_enqueue_scripts', [$this, 'm4is_p6570']);
 
}
function m4is_i62634(){
$m4is_q20175 ='manage_options';
 if(!current_user_can($m4is_q20175)){
return;
 
}$m4is_x160 ='memberium';
 $m4is_b9263 =$m4is_q20175;
 add_submenu_page($m4is_x160, 'Marketplace', 'Marketplace', $m4is_b9263, $this->marketplace_admin_slug, [$this, 'm4is_p14620']);
 
}
function m4is_p6570($m4is_e81053){
if('memberium_page_memberium-marketplace' != $m4is_e81053 ){
return;
 
}wp_register_style('m4is-marketplace-css', plugin_dir_url(__FILE__).'assets/css/styles.css', [], $this->marketplace_version, 'all' );
 wp_enqueue_style('m4is-marketplace-css');
 
}
function m4is_p14620(){
$m4is_s78 =$this->m4is_z763();
 if(!$m4is_s78 ){
echo __('No Listings Found');
 
}else{
$m4is_q49051 =($m4is_s78)? count($m4is_s78): 0;
 $m4is_f5196 =admin_url("admin.php?page=" . $this->marketplace_admin_slug );
 $m4is_p10384 =(isset($_GET['tab']))? $_GET['tab']: false;
 if(!$m4is_p10384 ){
$m4is_j09 =array_column($m4is_s78['content'], 'slug');
 $m4is_p10384 =$m4is_j09[0];
 
}$m4is_e1964 =(isset($m4is_s78['style']))? $m4is_s78['style']: false;
 $m4is_e1964 =($m4is_e1964 > '' )? $m4is_e1964 : false;
 $m4is_f619 =(isset($m4is_s78['header']))? $m4is_s78['header']: false;
 $m4is_p47 =(isset($m4is_s78['content']))? $m4is_s78['content']: false;
 $m4is_g876 =(isset($m4is_s78['footer']))? $m4is_s78['footer']: false;
 require_once __DIR__ . '/marketplace.php';
 
}
}
function m4is_z763(){
$m4is_l91805 =get_option($this->marketplace_opt_key, false);
 $m4is_m62319 =($m4is_l91805 )? false : true;
 if($m4is_l91805 ){
if((time()- $m4is_l91805['timestamp'])> $this->marketplace_cached_time ){
$m4is_m62319 =true;
 
}
}if($m4is_m62319 ){
$m4is_l91805 =$this->m4is_m86();
 
}return $m4is_l91805 ? $m4is_l91805['data']: false;
 
}
function m4is_m86(){
$m4is_y66291 =['user-agent' =>'m4is', ];
 $m4is_n548 =wp_remote_get($this->marketplace_feed_url, $m4is_y66291 );
 if(!is_wp_error($m4is_n548)){
$m4is_l91805 =json_decode(wp_remote_retrieve_body($m4is_n548 ), true );
 $m4is_l91805 =is_array($m4is_l91805)? $m4is_l91805 : false;
 if($m4is_l91805 ){
$m4is_v739 =['timestamp' =>time(), 'data' =>$m4is_l91805 ];
 update_option($this->marketplace_opt_key, $m4is_v739);
 return $m4is_v739;
 
}else{
return false;
 
}
}else{
return false;
 
}
}
}

