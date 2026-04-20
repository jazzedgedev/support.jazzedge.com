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
class m4is_l9685 {
const DEV_UPDATE_URL ='https://licenseserver.webpowerandlight.com/memberium-is/current-version.php';
 const PLUGIN_UPDATE_URL ='https://licenseserver.webpowerandlight.com/memberium-is/current-version.php';
 const PRODUCTION_UPDATE_URL ='https://licenseserver.webpowerandlight.com/memberium-is/current-version.php';
 const PLUGIN_FULLSLUG ='memberium2/memberium2.php';
 const PLUGIN_HOME =MEMBERIUM_HOME;
 const PLUGIN_SLUG ='memberium2';
 const PLUGIN_URL ='https://memberium.com/';
 const UPDATE_ID =31415926547;
 static private $m4is_r1546;
 static private $m4is_d64;
 static private $m4is_r657;
 static private $m4is_t30467;
  static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_d64 ='memberium2/memberium2.php';
 self::$m4is_t30467 =31415926547;
 self::$m4is_r657 ='https://licenseserver.webpowerandlight.com/memberium-is/current-version.php';
 
}private 
function __construct(){

} static 
function m4is_n8610(): string {
self::m4is_o735();
 return get_option('memberium/updater/version', 0 );
 
} static 
function m4is_v913(): string {
$m4is_g6152 =function_exists('wp_get_environment_type')? wp_get_environment_type(): 'production';
  $m4is_j3627 =['local' =>self::DEV_UPDATE_URL, 'development' =>self::DEV_UPDATE_URL, 'staging' =>self::PRODUCTION_UPDATE_URL, 'production' =>self::PRODUCTION_UPDATE_URL, ];
 $m4is_n6062 =isset($m4is_j3627[$m4is_g6152])? $m4is_j3627[$m4is_g6152]: self::PRODUCTION_UPDATE_URL;
 $m4is_a6814 =self::$m4is_r1546->m4is_w45();
 $m4is_q63 =get_bloginfo('admin_email');
 $m4is_o280 =m4is_s52::m4is_s326();
 $m4is_t24096 =phpversion();
 $m4is_g36127 =parse_url(get_bloginfo('url'), PHP_URL_HOST);
 $m4is_c36751 =self::$m4is_r1546->m4is_s0578();
 $m4is_u076 =['admin' =>rawurlencode($m4is_q63), 'domain' =>rawurlencode($m4is_g36127), 'env' =>rawurlencode($m4is_g6152), 'm4ac' =>rawurlencode($m4is_a6814), 'php' =>rawurlencode($m4is_t24096), 'users' =>rawurlencode($m4is_o280), 'wp' =>rawurlencode($m4is_c36751), ];
 $m4is_n6062 =add_query_arg($m4is_u076, $m4is_n6062);
 return $m4is_n6062;
 
}static 
function m4is_o735(bool $m4is_f465 =false){
if(!$m4is_f465){
$m4is_m78 =time()- get_option('memberium/updater/timestamp', 0);
 if($m4is_m78 > 0 &&$m4is_m78 < 3600){
return false;
 
}
}$m4is_t2487 =['timeout' =>10, ];
 $m4is_l6524 =self::$m4is_r1546->m4is_w45();
 $m4is_l59 =self::m4is_v913();
 $m4is_n548 =wp_remote_get($m4is_l59, $m4is_t2487);
 if(!is_a($m4is_n548, 'WP_Error')&&isset($m4is_n548['body'])){
$m4is_x02 =unserialize($m4is_n548['body']);
 if(isset($m4is_x02->version)){
update_option('memberium/updater/data', $m4is_x02, false);
 update_option('memberium/updater/version', $m4is_x02->version, false);
 update_option('memberium/updater/timestamp', time(), false);
 
}else{
error_log('Memberium: [error] Plugin update check failed.  Cannot fetch current version information.  Please contact support@memberium.com');
 return false;
 
}
}return true;
 
}static 
function m4is_q582($m4is_t846, $m4is_y66291, $m4is_c05328){
if(property_exists($m4is_c05328, 'slug')){
if($m4is_c05328->slug == self::PLUGIN_SLUG){
return true;
 
}
}return false;
 
}static 
function m4is_k018($m4is_t846, $m4is_y66291, $m4is_c05328){
if(property_exists($m4is_c05328, 'slug')){
if($m4is_c05328->slug == self::PLUGIN_SLUG){
$m4is_i569 =unserialize(m4is_a01587::m4is_f46823(self::PLUGIN_UPDATE_URL));
 if(is_object($m4is_i569)){
return $m4is_i569;
 
}
}
}return $m4is_t846;
 
}static 
function m4is_c765($m4is_e09){
$m4is_t846 =unserialize(m4is_a01587::m4is_f46823(self::PLUGIN_UPDATE_URL));
 if(is_object($m4is_t846)){
if(!function_exists('get_plugin_data')){
require_once ABSPATH . 'wp-admin/includes/plugin.php';
 
}$m4is_g78 =get_plugin_data(self::PLUGIN_HOME, false, false);
 $m4is_d64 =self::PLUGIN_FULLSLUG;
 $m4is_l6893 =$m4is_t846->version;
 $m4is_g06918 =$m4is_g78['Version'];
 $m4is_a686 =new stdClass;
 $m4is_a686->id =self::UPDATE_ID;
 $m4is_a686->slug =self::PLUGIN_SLUG;
 $m4is_a686->plugin =$m4is_d64;
 $m4is_a686->new_version =$m4is_t846->version;
 $m4is_a686->url =self::PLUGIN_URL;
 $m4is_a686->package =$m4is_t846->download_link;
 $m4is_a686->upgrade_notice =$m4is_t846->upgrade_notice;
 $m4is_a686->tested =$m4is_t846->tested;
 $m4is_a686->icons =$m4is_t846->icons;
 if(version_compare($m4is_l6893, $m4is_g06918, 'gt')){
$m4is_e09->response[$m4is_a686->plugin]=$m4is_a686;
 
}elseif(!empty($m4is_e09->response)){
unset($m4is_e09->response[$m4is_d64]);
 
}
}return $m4is_e09;
 
} private static 
function m4is_a420(): bool {
if(!self::m4is_b45381()){
return false;
 
}$m4is_q182 =strtolower(trim(self::$m4is_r1546->m4is_w45()));
 $m4is_v95316 =(bool) self::$m4is_r1546->m4is_j498('settings', 'autoupdate' );
 $m4is_v95316 =$m4is_v95316 ||!m4is_s52::m4is_f27();
 if(!$m4is_v95316 ){
$m4is_c07 =['dev', 'alpha', 'beta', 'rc', 'pl'];
 foreach ($m4is_c07 as $m4is_t25641 ){
if(!$m4is_v95316 ){
$m4is_v95316 =(bool) strpos($m4is_q182, $m4is_t25641 );
 
}
}
}return $m4is_v95316;
 
} static 
function m4is_x75(){
if(!self::m4is_a420()){
return;
 
}if(!function_exists('get_plugin_data' )){
require_once ABSPATH . 'wp-admin/includes/plugin.php';
 
}$m4is_t846 =wp_remote_get(self::PLUGIN_UPDATE_URL );
 $m4is_t846 =is_array($m4is_t846 )? $m4is_t846 =unserialize($m4is_t846['body']): [];
 $m4is_m60 =false;
 $m4is_c5632 =get_plugin_data(self::PLUGIN_HOME, false, false );
 $m4is_l6893 =is_object($m4is_t846 )&&property_exists($m4is_t846, 'version' )? $m4is_t846->version : '';
 $m4is_g06918 =self::$m4is_r1546->m4is_w45();
 if(version_compare($m4is_l6893, $m4is_g06918, 'gt' )){
 require_once ABSPATH .'/wp-admin/includes/file.php';
  $m4is_j04866 =WP_PLUGIN_DIR;
 $m4is_z245 =download_url($m4is_t846->download_link, 300 );
 if(is_wp_error($m4is_z245 )){
error_log('Memberium: [error] Plugin update failed.  Cannot download update file.  Please contact support.' );
 return;
 
} if(file_exists($m4is_z245 )){
  require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
 require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
 WP_Filesystem();
 $m4is_d06865 =new wp_filesystem_direct(null);
 $m4is_k0621 =self::$m4is_r1546->m4is_g316();
 ignore_user_abort();
  file_put_contents(ABSPATH . '.maintenance', '<?php $upgrading = time();' );
 $m4is_d06865->delete($m4is_k0621, true );
 if(!function_exists('disk_free_space' )){
add_filter('wp_doing_cron', '__return_false', 10, 1 );
 
}unzip_file($m4is_z245, $m4is_j04866 );
 remove_filter('wp_doing_cron', '__return_false', 10 );
 if(function_exists('opcache_reset' )){
opcache_reset();
 wp_cache_flush();
 
}unlink($m4is_z245 );
 if(file_exists(ABSPATH . '.maintenance')){
unlink(ABSPATH . '.maintenance');
 
}$m4is_m60 =true;
 
}
}return $m4is_m60;
 
} static 
function m4is_y17584($m4is_b534, $m4is_l4606, $m4is_h049, $m4is_y5601 ){
if($m4is_b534 === false ){
self::m4is_o735(false );
 $m4is_c69 =get_option('memberium/updater/data', []);
 $m4is_v43021 =$m4is_l4606['Version']?? '0.0.0';
 $m4is_l6893 =$m4is_c69->version;
 $m4is_h163 =version_compare($m4is_v43021, $m4is_l6893, '<' );
 if($m4is_h163 ){
$m4is_b534 =['id' =>$m4is_l4606['UpdateURI'], 'slug' =>plugin_basename(MEMBERIUM_HOME ), 'version' =>$m4is_l6893, 'url' =>'https://memberium.com/activecampaign/', 'package' =>$m4is_c69->download_link, 'tested' =>$m4is_c69->tested, 'requires_php' =>$m4is_c69->requires_php, 'autoupdate' =>true, ];
 
}
} return $m4is_b534;
 
} static 
function m4is_b45381(): bool {
return is_writable(MEMBERIUM_HOME )&&!class_exists(base64_decode('bWVtYmVyaXVtX2NvcmVfY2xhc3M=' ));
 
} static 
function m4is_f358(){
$m4is_q436 =600;
 $m4is_z984 ='memberium/updates/available';
 $m4is_m12895 =base64_decode('aHR0cHM6Ly9saWNlbnNlc2VydmVyLndlYnBvd2VyYW5kbGlnaHQuY29tL3VwZGF0ZXMvdXBkYXRlLWxpc3QucGhw' );
  $m4is_l91805 =get_transient($m4is_z984 );
 $m4is_a85 =(int) self::$m4is_r1546->m4is_w45();
 if(!is_array($m4is_l91805 )){
$m4is_l91805 =wp_remote_get($m4is_m12895 );
  $m4is_l91805 =is_array($m4is_l91805 )? $m4is_l91805 : [];
 if(!empty($m4is_l91805['body'])){
$m4is_l91805 =json_decode($m4is_l91805['body'], true );
 
}$m4is_l91805 =is_array($m4is_l91805 )? $m4is_l91805 : [];
 
}foreach($m4is_l91805 as $m4is_l9671 =>$m4is_v586 ){
if(isset($m4is_v586['version'])&&$m4is_v586['version']< $m4is_a85 ){
unset($m4is_l91805[$m4is_l9671]);
 
}
}set_transient($m4is_z984, $m4is_l91805, $m4is_q436 );
 return $m4is_l91805;
 
}
}

