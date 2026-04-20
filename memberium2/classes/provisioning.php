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
class m4is_s52 {
 const LICENSE_SERVER_URL ='https://licenseserver.webpowerandlight.com/getlicense.php';
 private static m4is_r83 $m4is_r1546;
 private static $m4is_v6514;
 private static $m4is_i406;
 private static $m4is_f14893;
 private static $m4is_q186;
  private 
function __construct(){

} public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_v6514 ='wpal/census';
 self::$m4is_q186 ='memberium/licenseserver/updated';
 self::$m4is_i406 =false;
 self::$m4is_f14893 =false;
 
} public static 
function m4is_b28571(): int {
global $wpdb;
 $m4is_o280 =(int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM %i", $wpdb->users ));
 update_option(self::$m4is_v6514, base_convert($m4is_o280, 10, 36 ), true );
 return $m4is_o280;
 
} public static 
function m4is_s326(){
$m4is_o280 =base_convert(get_option(self::$m4is_v6514, 0 ), 36, 10 );
 if(!$m4is_o280 ){
$m4is_o280 =self::m4is_b28571();
 
}return $m4is_o280;
 
} public static 
function m4is_w74(){
return self::$m4is_i406;
 
} public static 
function m4is_y108(): array {
$m4is_v1568 =self::m4is_v91686();
 $m4is_l9321 =isset($m4is_v1568['tags'])? $m4is_v1568['tags']: '';
 $m4is_l9321 =array_filter(explode(',', strtolower($m4is_l9321 )));
 return $m4is_l9321;
 
} public static 
function m4is_e24(): bool {
return (boolean) self::$m4is_f14893;
 
} public static 
function m4is_v91686(string $m4is_u60473 ='' ): array {
$m4is_z046 =self::m4is_e96();
 $m4is_k60849 =self::$m4is_r1546->m4is_j498('settings', 'random_seed' );
 if(empty($m4is_u60473 )){
$m4is_u60473 =self::m4is_n34();
 
}$m4is_z75923 =3;
 $m4is_f34607 =32;
 $m4is_u6591['valid']=0;
 $m4is_u6591['trial_mode']=0;
 $m4is_v32 =self::m4is_c46516();
 $m4is_r12 =strtolower(trim($m4is_k60849 . $m4is_v32 ));
 $m4is_s482 =$m4is_k60849;
 if($m4is_z75923 == 3 ){
$m4is_x10946 =unserialize(base64_decode(substr($m4is_u60473, 32 )));
 $m4is_k470 =substr($m4is_u60473, 0, 32 );
 $m4is_t866 =md5(serialize($m4is_x10946 ). strtolower($m4is_s482 ). strtolower($m4is_v32 ));
 $m4is_u6591 =$m4is_x10946;
 $m4is_u6591 =is_array($m4is_u6591 )? $m4is_u6591 : [];
 if($m4is_t866 == $m4is_k470){
$m4is_u6591['valid']=1;
 
}else{
$m4is_u6591['valid']=0;
 
}$m4is_u6591['active']=isset($m4is_x10946['active'])? $m4is_x10946['active']: 0;
 $m4is_u6591['kill']=isset($m4is_x10946['kill'])? $m4is_x10946['kill']: 0;
 $m4is_u6591['max_users']=isset($m4is_x10946['max_users'])? $m4is_x10946['max_users']: 999999999;
 $m4is_u6591['max_version']=isset($m4is_x10946['max_version'])? $m4is_x10946['max_version']: 0;
 $m4is_u6591['min_version']=isset($m4is_x10946['min_version'])? $m4is_x10946['min_version']: 0;
 $m4is_u6591['next_check']=isset($m4is_x10946['next_check'])? $m4is_x10946['next_check']: 0;
 $m4is_u6591['renewal_date']=isset($m4is_x10946['renewal_date'])? $m4is_x10946['renewal_date']: 0;
 $m4is_u6591['tags']=isset($m4is_x10946['tags'])? $m4is_x10946['tags']: '';
 $m4is_u6591['trial_mode']=isset($m4is_x10946['trial_mode'])? $m4is_x10946['trial_mode']: 0;
 $m4is_u6591['version']=isset($m4is_x10946['version'])? $m4is_x10946['version']: 0;
 $m4is_u6591['now']=time();
 $m4is_u6591['check_ttl']=$m4is_u6591['next_check']- time();
 $m4is_u6591['expiration_ttl']=$m4is_u6591['renewal_date']- time();
 if(!empty($m4is_u6591['kill'])){
if(!function_exists('deactivate_plugins')){
require_once ABSPATH . '/wp-admin/includes/plugin.php';
 
}$m4is_u6591['active']=0;
 $m4is_h049 =plugin_basename(MEMBERIUM_HOME );
 deactivate_plugins($m4is_h049, true, false );
 deactivate_plugins($m4is_h049, true, true );
 wp_cache_flush();
 if(!headers_sent()){
$m4is_n6062 =is_admin()? admin_url(): get_home_url();
 $m4is_n6062 =esc_url_raw($m4is_n6062 );
 header("Location: {$m4is_n6062
}" );
 
}exit;
 
}
} if($m4is_u6591['expiration_ttl']< 1 ){
$m4is_u6591['active']=0;
 
}if($m4is_u6591['max_users']< self::m4is_s326()){
$m4is_u6591['active']=0;
 
}self::$m4is_f14893 =(int) $m4is_u6591['trial_mode'];
 return $m4is_u6591;
 
} public static 
function m4is_e96(): string {
static $m4is_l9671 ='';
 if(empty($m4is_l9671 )){
$m4is_n6062 =self::m4is_c46516();
  $m4is_l9671 ='memberium/license/' . strtolower(soundex($m4is_n6062 ). metaphone($m4is_n6062, 16 )). '_' . abs(crc32($m4is_n6062 ));
 
}return $m4is_l9671;
 
} public static 
function m4is_n34(): string {
return get_option(m4is_s52::m4is_e96());
 
} private static 
function m4is_c46516(): string {
static $m4is_l64 ='';
 if(empty($m4is_l64 )){
$m4is_l64 =preg_replace('/^www\./', '', strtolower(parse_url(get_option('home' ), PHP_URL_HOST )));
 
}return $m4is_l64;
 
} public static 
function m4is_a834(bool $m4is_f465 =false ){
$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname');
 if(empty($m4is_r9613 )){
return false;
 
}if($m4is_f465 === false ){
$m4is_o30926 =time()- (int) get_option(self::$m4is_q186, 0 );
 $m4is_j80 =function_exists('is_admin' )&&is_admin()? 300 : HOUR_IN_SECONDS;
 if($m4is_o30926 < $m4is_j80 ){
return self::m4is_n34();
 
}
}$m4is_k60849 =self::$m4is_r1546->m4is_j498('settings', 'random_seed');
 $m4is_z046 =self::m4is_e96();
 $m4is_g6152 =function_exists('wp_get_environment_type')? wp_get_environment_type(): 'production';
 $m4is_z1852 ='None';
 $m4is_v1568 ='';
 $m4is_l6970 =['method' =>'POST', 'timeout' =>10, 'redirection' =>5, 'httpversion' =>'1.0', 'blocking' =>true, 'headers' =>[], 'cookies' =>[], 'body' =>['admin_email' =>get_bloginfo('admin_email'), 'appname' =>$m4is_r9613, 'environment' =>$m4is_g6152, 'checksum' =>$m4is_k60849, 'hostname' =>self::m4is_c46516(), 'interval' =>32, 'ioncube_version' =>$m4is_z1852, 'ip_address' =>$_SERVER['SERVER_ADDR']?? '0.0.0.0', 'license_key' =>'', 'php_version' =>phpversion(), 'protocol_version' =>3, 'sku' =>'M4IS', 'url' =>get_bloginfo('url'), 'user_count' =>self::m4is_s326(), 'version' =>self::$m4is_r1546->m4is_w45(), ], ];
 $m4is_n548 =wp_remote_post(self::LICENSE_SERVER_URL, $m4is_l6970 );
 if(is_array($m4is_n548 )){
$m4is_v1568 =$m4is_n548['body'];
 $m4is_h56893 =self::m4is_v91686($m4is_v1568 );
 update_option('memberium/licenseserver/status', 'pass', true );
 if($m4is_h56893['valid']== 1 ){
update_option($m4is_z046, $m4is_v1568 );
 
}else{
$m4is_v1568 =self::m4is_n34();
 
}
}elseif(is_a($m4is_n548, 'WP_ERROR' )){
update_option('memberium/licenseserver/status', 'fail', true);
 
}update_option(self::$m4is_q186, time(), 'yes');
 return $m4is_v1568;
 
} static 
function m4is_f27(): bool {
static $m4is_i406 =-1;
  if(is_bool($m4is_i406 )){
return (boolean) $m4is_i406;
 
}$m4is_v1568 =self::m4is_n34();
 $m4is_h56893 =self::m4is_v91686($m4is_v1568 );
 $m4is_u0258 =false;
 if(!$m4is_h56893['active']){
$m4is_u0258 =true;
 
}if(!$m4is_h56893['valid']){
$m4is_u0258 =true;
 
}if($m4is_h56893['check_ttl']< 10800 ){
$m4is_u0258 =true;
 
}if(false &&$m4is_u0258 ){
$m4is_v1568 =self::m4is_a834();
 $m4is_h56893 =self::m4is_v91686($m4is_v1568 );
 
} $m4is_i406 =isset($m4is_h56893['active'])? (int) $m4is_h56893['active']: 0;
  if($m4is_i406 &&isset($m4is_h56893['max_users'])){
if($m4is_h56893['max_users']< self::m4is_s326()){
$m4is_i406 =0;
 
}
}if($m4is_i406 &&isset($m4is_h56893['max_version'])){
if(version_compare($m4is_h56893['max_version'], self::$m4is_r1546->m4is_w45())<= 0){
$m4is_i406 =0;
 
}
}self::$m4is_i406 =(bool) $m4is_i406;
 return self::$m4is_i406;
 
} static 
function m4is_b12067(array $m4is_r8763 =[]): bool {
$m4is_w267 =self::m4is_y108();
 foreach($m4is_r8763 as $m4is_p786 ){
if(in_array($m4is_p786, $m4is_w267 )){
return true;
 
}
}return false;
 
}
}

