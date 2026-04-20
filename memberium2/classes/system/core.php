<?php

/**
 * Proprietary Software - All Rights Reserved
 *
 * This file is part of the Memberium plugin, which is proprietary software developed by Web Power and Light.
 * Unauthorized copying, distribution, or modification of this file, via any medium, is strictly prohibited.
 *
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light
 *
 * For licensing information, please contact Web Power and Light.
 */


 defined('ABSPATH' )||die();
  final 
class m4is_r83 {
 const PLUGIN_UPDATE_URL ='https://licenseserver.webpowerandlight.com/memberium-is/current-version.php';
 const PRODUCT_NAME ='Memberium';
 const NAMESPACE ='memberium';
 private const USER_FIELD_PREFIX ='memberium::field::';
 private const SYSTEM_CONFIG_TIMESTAMP_KEY ='memberium_system_config_timestamp';
 private bool $disable_login_redirect =false;
 private array $m4is_l61035 =[];
 private array $m4is_y42 =[];
 private array $m4is_w466 =[];
 private array $m4is_y62 =[];
 private array $m4is_r6684 =[];
 private array $m4is_r37596 =[];
 private array $m4is_a3165 =[];
 private array $m4is_i7302 =[];
 private bool $m4is_t7645 =false;
 private bool $m4is_r92 =false;
 private bool $m4is_p21679 =false;
 private int $m4is_i086 =0;
 private int $m4is_i316 =0;
 private int $m4is_z71834 =0;
 private int $m4is_j168 =0;
 private string $m4is_z046 ='';
 private string $m4is_u0789 ='';
 private string $m4is_h049 ='';
 private string $m4is_f4218 ='memberium';
 private ?object $m4is_z59682;
 private ?object $m4is_m63172;
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self();
 
} private 
function __construct(){
$m4is_a78 =get_option('memberium/disable/plugin', '' );
 memberium_service_class::m4is_u684('app', $this );
 if($this->m4is_r94($m4is_a78 )){
return;
 
}$this->m4is_u0789 =MEMBERIUM_HOME_DIR;
 $this->m4is_h049 =MEMBERIUM_HOME;
 $this->m4is_p19630();
 $this->m4is_e820();
 $this->m4is_b06254();
 $this->m4is_h91();
 $this->m4is_q97523();
 $this->m4is_p85674();
 add_action('auth_cookie_valid', [$this, 'm4is_k24951']);
  add_action('plugin_loaded', [$this, 'm4is_z716'], PHP_INT_MIN );
 add_action('plugins_loaded', [$this, 'm4is_p147'], PHP_INT_MIN );
 add_action('plugins_loaded', [$this, 'm4is_e3840'], 2 );
   
} private 
function m4is_f3276(){
if((defined('DOING_AJAX')&&DOING_AJAX)||(!is_admin())){
include_once $this->m4is_j541(). 'system/frontend.php';
 m4is_f58::m4is_c26();
 
}if(is_admin()){
include_once $this->m4is_j541(). 'system/admin.php';
 m4is_s6729::m4is_c26();
 
}
} public 
function m4is_n430(): string {
global $wpdb;
 static $m4is_c62831;
 return $m4is_c62831 ??= hash_hmac('sha256', $wpdb->dbname . '|' . $wpdb->prefix . '|' . ABSPATH . '|' . site_url(), wp_salt('nonce'));
 
} private 
function m4is_r94(): bool {
$m4is_a78 =get_option('memberium/debug/ip_disable', '' );
 if(empty($m4is_a78 )){
return false;
 
}$m4is_a78 =array_filter(explode(',', $m4is_a78 ));
 $m4is_u450 =['REMOTE_ADDR', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_X_SUCURI_CLIENTIP', 'HTTP_X_REAL_IP', ];
 foreach ($m4is_u450 as $m4is_l9671 ){
if(in_array($_SERVER[$m4is_l9671], $m4is_a78 )){
return true;
 
}
}return false;
 
} private 
function m4is_q97523(){
$this->m4is_w17904();
  if(!defined('WP_DEBUG' )||!WP_DEBUG ){
 if(function_exists('error_reporting' )){
 error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_PARSE | E_USER_ERROR );
 
} if(function_exists('ini_set' )){
 @ini_set('display_errors', 0 );
 
}
}
}    private 
function m4is_p19630(){
$this->m4is_r6684['start']=['time' =>empty($_SERVER['REQUEST_TIME_FLOAT'])? microtime(true ): $_SERVER['REQUEST_TIME_FLOAT'], 'memory' =>memory_get_usage(), 'http_calls' =>0, 'api_calls' =>0, 'api_time' =>0, ];
 add_filter('pre_http_request', [$this, 'm4is_x20'], 1, 3);
 
} public 
function m4is_e07(bool $m4is_j265 =true ): array {
if($m4is_j265 ){
$m4is_u6591 =['time' =>microtime(true )- $this->m4is_r6684['start']['time'], 'memory' =>memory_get_usage()- $this->m4is_r6684['start']['memory'], 'http_calls' =>(int) $this->m4is_r6684['start']['http_calls'], 'api_calls' =>(int) $this->m4is_r6684['start']['api_calls'], ];
 
}else{
$m4is_u6591 =['time' =>microtime(true )- $this->m4is_r6684['start']['time'], 'memory' =>memory_get_usage(), 'http_calls' =>(int) $this->m4is_r6684['start']['http_calls'], 'api_calls' =>(int) $this->m4is_r6684['start']['api_calls'], ];
 
}return $m4is_u6591;
 
} private 
function m4is_f76062(): void {
global $wp_object_cache;
 $m4is_w32495 =number_format_i18n(microtime(true )- $this->m4is_r6684['start']['time'], 3 ). 's';
 $m4is_g41650 =size_format($this->m4is_r6684['end']['memory'], 2 );
 $m4is_r2671 =(int) $this->m4is_r6684['start']['http_calls'];
 $m4is_l75018 =(int) $this->m4is_r6684['start']['api_calls'];
 $m4is_k5031 =get_num_queries();
 $m4is_y19485 =property_exists($wp_object_cache, 'cache_hits' )? (int) $wp_object_cache->cache_hits : 0;
 $m4is_j85217 =property_exists($wp_object_cache, 'cache_misses' )? (int) $wp_object_cache->cache_misses : 0;
 $m4is_g84736 ='';
 $m4is_z9157 =['wpal-memberium-time' =>$m4is_w32495, 'wpal-memberium-memory' =>$m4is_g41650, 'wpal-memberium-http-calls' =>$m4is_r2671, 'wpal-memberium-crm-api' =>$m4is_l75018,  'wpal-memberium-db-queries' =>$m4is_k5031, 'wpal-memberium-cache-hits' =>$m4is_y19485, 'wpal-memberium-cache-misses' =>$m4is_j85217, ];
 foreach($m4is_z9157 as $m4is_l9671 =>$m4is_v586 ){
$m4is_g84736 .= "'$m4is_l9671': '$m4is_v586', \n";
 
}$m4is_n66257 =<<<HTMLDOC
			<script>
				document.addEventListener("DOMContentLoaded", function() {
					const elements = {
						{$m4is_g84736
}
					};

					for (const [id, value] of Object.entries(elements)) {
						const el = document.getElementById(id);
						if (el) el.innerHTML = value;
					}
				});
			</script>
		HTMLDOC;
 echo $m4is_n66257;
 
}public 
function m4is_q98(bool $m4is_f465 =false ){
global $wp_object_cache;
 $this->m4is_r6684['end']=['time' =>microtime(true ), 'memory' =>memory_get_usage(), ];
 if(is_admin_bar_showing()){
$this->m4is_f76062();
 
}return;
 if($m4is_f465 == false &&!is_admin_bar_showing()){
return;
 
}$m4is_z9157 =['wpal-memberium-time' =>$m4is_w32495, 'wpal-memberium-memory' =>$m4is_g41650, 'wpal-memberium-http-calls' =>$m4is_r2671, 'wpal-memberium-crm-api' =>$m4is_l75018,  'wpal-memberium-db-queries' =>$m4is_k5031, 'wpal-memberium-cache-hits' =>$m4is_y19485, 'wpal-memberium-cache-misses' =>$m4is_j85217, ];
 echo "<script>\n";
 echo ' document.addEventListener( "DOMContentLoaded", function() { ' . "\n";
 foreach($m4is_z9157 as $m4is_l9671 =>$m4is_v586 ){
echo '  document.getElementById( "' . $m4is_l9671 . '" ).innerHTML = "' . $m4is_v586 . '";' . "\n";
 
}echo ' }); ' . "\n";
 echo "\n</script>";
 
} public 
function m4is_o73(): void {
if(!defined('WP_DEBUG' )||!WP_DEBUG ){
return;
 
}global $wp_object_cache;
 $this->m4is_r6684['end']=['time' =>microtime(true ), 'memory' =>memory_get_usage(), ];
 $m4is_w32495 =number_format_i18n(microtime(true )- $this->m4is_r6684['start']['time'], 3 ). 's';
 $m4is_g41650 =size_format($this->m4is_r6684['end']['memory'], 2 );
 $m4is_x2664 =(int) $this->m4is_r6684['start']['http_calls'];
 $m4is_y65092 =(int) $this->m4is_r6684['start']['api_calls'];
 $m4is_s98 =get_num_queries();
 $m4is_k50 =property_exists($wp_object_cache, 'cache_hits' )? (int) $wp_object_cache->cache_hits : 0;
 $m4is_d38610 =property_exists($wp_object_cache, 'cache_misses' )? (int) $wp_object_cache->cache_misses : 0;
 $m4is_f087 =$this->m4is_x66();
 error_log(sprintf('Memberium Profiler: [INFO] %s [%s] user(%s) - time(%s) memory(%s) db(%s) api(%s) http(%s) cachehit(%s) cachemiss(%s)', sanitize_text_field($_SERVER['REMOTE_ADDR']), sanitize_text_field($_SERVER['REQUEST_METHOD']), (int) $m4is_f087, $m4is_w32495, $m4is_g41650, $m4is_s98, $m4is_y65092, $m4is_x2664, $m4is_k50, $m4is_d38610 ));
 
} public 
function m4is_g04126(float $m4is_w32495 =0.0, string $m4is_j0361 ='' ): void {
 $this->m4is_r6684['start']['api_calls']++;
  $this->m4is_r6684['start']['api_time']+= $m4is_w32495;
 
} public 
function m4is_x20($m4is_u6591, $m4is_y66291, $m4is_n6062 ){
 $this->m4is_r6684['start']['http_calls']++;
  return $m4is_u6591;
 
}      private 
function m4is_h91(): void {
if(empty($this->m4is_l61035 )){
$m4is_f45709 =$this->m4is_j541();
 $m4is_m14379 =$this->m4is_v75();
 $this->m4is_l61035 =['m4is_n17' =>'classes/system/activate', 'm4is_s6729' =>'classes/access/admin', 'm4is_f58' =>'classes/system/frontend', 'm4is_t4578' =>'classes/shortcodes/shortcodes', 'm4is_e37682' =>'classes/access/admin', 'm4is_v679' =>'classes/access/access', 'm4is_w5132' =>'classes/access/frontend', 'm4is_e37682' =>'classes/access/admin', 'm4is_o9186' =>'classes/access/admin-menu', 'm4is_m749' =>'classes/access/admin-taxonomy', 'm4is_m32784' =>'classes/access/admin-widgets', 'm4is_n2043' =>'classes/ui/adminbar', 'm4is_z7893' =>'classes/autologin', 'm4is_q690' =>'classes/cpt', 'm4is_d47529' =>'classes/cron', 'm4is_u4569' =>'classes/diagnostics', 'm4is_l5841' =>'classes/login', 'm4is_f642' =>'classes/maintenance', 'm4is_b43' =>'classes/post_sherpa', 'm4is_w831' =>'classes/posts', 'm4is_s52' =>'classes/provisioning', 'm4is_y18042' =>'classes/reset', 'm4is_i0574' =>'classes/sitehealth', 'm4is_n2918' =>"classes/link_handler", 'm4is_g64' =>'libraries/debuglog', 'm4is_b54' =>'libraries/eval', 'm4is_o106' =>'libraries/gdpr', 'm4is_q6082' =>'libraries/geolocation', 'm4is_d86' =>'libraries/language', 'm4is_y57019' =>'libraries/loginlog', 'm4is_a01587' =>'libraries/network_tools', 'm4is_j586' =>'libraries/pagehandling', 'm4is_j946' =>'libraries/relationships', 'm4is_a391' =>'libraries/rss', 'm4is_f61' =>'libraries/scapi', 'm4is_q82' =>'libraries/session', 'm4is_q05' =>'libraries/time', 'm4is_l9685' =>'libraries/updater', 'm4is_z37620' =>'libraries/wpuser', 'm4is_s10863' =>'libraries/utilities', 'm4is_g689' =>'libraries/words', 'm4is_e35' =>'modules/discourse/core', 'm4is_e230' =>'modules/facebook/admin', 'm4is_b9348' =>'modules/facebook/core', 'm4is_u6435' =>'modules/facebook/shortcodes', 'm4is_q1089' =>'modules/buddypress/core', 'm4is_u7102' =>'modules/group-accounts/core', 'm4is_u81' =>'modules/group-accounts/database', 'keap_sdk_class' =>'vendor/i2sdkng/i2sdk', 'm4is_j4156' =>'classes/crm/actionsets', 'm4is_z13097' =>'classes/crm/affiliate', 'm4is_c69807' =>'classes/crm/interface', 'm4is_p40' =>'classes/crm/contact', 'm4is_c01675' =>'classes/crm/creditcard', 'm4is_s695' =>'classes/crm/custom_fields', 'm4is_v87365' =>'classes/crm/ecommerce', 'm4is_t21664' =>'classes/crm/filebox', 'm4is_c86031' =>'classes/crm/httpposts', 'm4is_q62395' =>'classes/crm/httppost_log', 'm4is_u36' =>'classes/crm/owner', 'm4is_c63' =>'classes/crm/social', 'm4is_z6894' =>'classes/crm/tag_categories', 'm4is_k865' =>'classes/crm/tags',  ];
 spl_autoload_register([$this, 'm4is_o72']);
 
}
} 
function m4is_w879(string $m4is_s6347, string $m4is_k86914){
$m4is_s6347 =strtolower(trim($m4is_s6347));
 if(!array_key_exists($m4is_s6347, $this->m4is_l61035)){
$this->m4is_l61035[$m4is_s6347]=$m4is_k86914;
 
}
} 
function m4is_p39(array $m4is_l61035 ){
$this->m4is_l61035 =array_merge($this->m4is_l61035, $m4is_l61035 );
 
} 
function m4is_o72(string $m4is_s6347 ){
static $m4is_u0789;
 $m4is_s6347 =strtolower($m4is_s6347 );
 $m4is_u0789 ??= $this->m4is_g316();
 if($m4is_s6347 == 'i2sdk_class' ){
require_once $this->m4is_q0769('i2sdkng/i2sdk.php' );
 
}else{
if(array_key_exists($m4is_s6347, $this->m4is_l61035 )){
$m4is_d04266 =substr($this->m4is_l61035[$m4is_s6347], 0, 1 )== '/' ? $this->m4is_l61035[$m4is_s6347]. '.php' : $m4is_u0789 . $this->m4is_l61035[$m4is_s6347]. '.php';
 if(file_exists($m4is_d04266 )){
include $m4is_d04266;
 if(method_exists($m4is_s6347, 'm4is_c961' )){
$m4is_s6347::m4is_c961();
 
}
}
}
}
}    private 
function m4is_q68(): array {
return ['affiliate_detect' =>0, 'allow_autologin' =>0, 'allow_local_logins' =>1, 'allow_wpadmin_dashboard' =>'edit_others_posts', 'allow_wpadmin_titlebar' =>'edit_others_posts', 'allow_wpadmin' =>1, 'api_log_duration' =>3, 'async_limit' =>0, 'async_tags' =>0, 'attachment_pages' =>1, 'autogenerate_excerpts' =>0, 'autologout_time' =>0, 'autoupdate' =>1, 'beta_update_check' =>0, 'beta/oauth' =>0, 'bruteforce_check' =>0, 'cache_bust' =>0, 'cache_flush' =>0, 'choose_affiliate' =>1, 'db_sessions' =>1, 'default_logout_page' =>0, 'default_page_redirect' =>'', 'default_prohibited_action' =>'redirect', 'default_reglink_tag' =>0, 'disable_displayname_update' =>0, 'disable_login_sync' =>0, 'disable_lost_password' =>0, 'disable_password_reset' =>0, 'disable_xframe' =>0, 'display_errors' =>0, 'displayname_format' =>'', 'dynamic_menus' =>0, 'enable_slug_update' =>0, 'excerpt_length' =>55, 'extended_reg_fields' =>0, 'facebook_app_id' =>'', 'fast_user_list' =>0,  'force_learndash_inheritance' =>defined('LEARNDASH_VERSION')? 1 : 0, 'global_excerpt' =>'', 'hashing_mode' =>'plain', 'html_shortcode_embed' =>1, 'httppost_log' =>1, 'ignore_affiliate_fields' =>'', 'ignore_contact_fields' =>'ContactNotes', 'ignore_tag_categories' =>'', 'include_default_excerpt' =>'', 'known_logins_only' =>0, 'last_login_field' =>'', 'license_key' =>'', 'local_auth_only' =>0, 'login_actionset' =>0, 'login_log_length' =>30, 'login_log' =>1, 'login_tag' =>0, 'login_url' =>0, 'logout_actionset' =>0, 'logout_tag' =>0, 'makepass_scan_size' =>0, 'makepass_scan_tag' =>0, 'makepass_success_actionset' =>0, 'makepass_success_tag' =>0, 'max_affiliate_age' =>0, 'max_contact_age' =>0, 'maximum_login_ips' =>0, 'maximum_login_timeframe' =>0, 'memberium_user_registration_tag' =>0, 'merchant_account_id' =>0, 'microcache_compat_session' =>0, 'min_password_length' =>8, 'multi_language' =>0, 'new_user_registration_tag' =>0, 'page_inheritance' =>1, 'password_field' =>'Password', 'password_reset_tag' =>0, 'password_strength' =>0, 'persistent_login' =>0, 'plaintext_db' =>0, 'plaintext_db' =>0, 'preview_mode' =>0, 'protect_feeds' =>1, 'referral_partner_order' =>1,  'registration_url' =>0, 'require_membership' =>0, 'session_timeout' =>0, 'show_advanced_options' =>0, 'show_post_columns' =>0, 'simultaneous_logins' =>0, 'site_ban_tag' =>0, 'site_lock_enabled' =>0, 'spiffy_api_key' =>'', 'spiffy_subdomain' =>'', 'sync_affiliate' =>0, 'sync_ecommerce' =>0, 'sync_meta_updates' =>0, 'sync_new_wp_users' =>0, 'sync_users' =>0, 'telemetry' =>1, 'thrivecart_secret' =>'', 'two_pass_shortcode_filter' =>0, 'user_registration_tag' =>0, 'username_field' =>'Email', 'version' =>$this->m4is_w45(), 'wp_autop' =>0, 'wplogin_redirect_to' =>0, ];
 
} private 
function m4is_w17904(): array {
$this->m4is_r37596 =get_option('memberium', []);
 $m4is_r37596 =is_array($this->m4is_r37596 )? $this->m4is_r37596 : [];
 $m4is_k23958 =empty($this->m4is_r37596 );
 $m4is_y642 =$this->m4is_q68();
 $m4is_e0213 =$this->m4is_r37596['settings'];
 $m4is_e0213 =wp_parse_args($m4is_e0213, $m4is_y642 );
 $this->m4is_r37596['settings']=$m4is_e0213;
 return $this->m4is_r37596;
 
} private 
function m4is_p613(){
$i2sdk_options =i2sdk_class::get_i2sdk_options();
 $m4is_m63172 =$this->m4is_z40();
 $m4is_z59682 =$this->m4is_r1476();
  $this->m4is_y42['appname']=method_exists($m4is_z59682, 'getAppName' )? $m4is_z59682->getAppName(): $i2sdk_options['app_name'];
 $this->m4is_y42['verified']=method_exists($m4is_m63172, 'isVerified' )? $m4is_m63172->isVerified(): false;
 $this->m4is_y42['api_key']=isset($i2sdk_options['api_key'])? $i2sdk_options['api_key']: '';
 
} public 
function m4is_t91750($m4is_v586, $m4is_x74, $m4is_p48691 ){
return max($m4is_x74, min($m4is_p48691, $m4is_v586 ));
 
}   
function m4is_z716($m4is_v60163 ){
m4is_u7102::m4is_c26();
 do_action('memberium/modules/loaded' );
 if($m4is_v60163 !== $this->m4is_h049 ){
return;
 
}
}
function m4is_p147(){
static $m4is_s529 =0;
 if($m4is_s529++ ){
return;
 
}require_once $this->m4is_u21875('memberium_api.php' );
 require_once $this->m4is_u21875('functions.php' );
 $this->m4is_u2498();
 $this->m4is_p613();
 $this->m4is_u27541();
 $this->m4is_m63172 =keap_sdk_class::get_i2sdk();
 $this->m4is_z59682 =$this->m4is_r1476();
 $this->m4is_z046 =m4is_s52::m4is_e96();
 m4is_s52::m4is_f27();
  $this->m4is_u6764();
 $this->m4is_f3276();
 $this->m4is_d4861();
 $this->m4is_t13();
 
}public 
function m4is_k24951(){
$this->m4is_a28739();
 
}
function m4is_e3840(){
 if(!m4is_s52::m4is_f27()){
return;
 
}$this->m4is_n054();
 $this->m4is_p910();
 $this->m4is_r0573();
 $this->m4is_n62();
  
} private 
function m4is_u2498(): void {
if(!defined('I2SDK_HOME' )){
require_once $this->m4is_g316(). 'vendor/i2sdkng/i2sdk.php';
 
}
}   public 
function m4is_b57926(): string {
return $this->m4is_f4218;
 
} public 
function m4is_j83120(): bool {
static $m4is_e66310;
 if(is_null($m4is_e66310 )){
$m4is_e66310 =is_admin();
 
}return $m4is_e66310;
 
}public 
function m4is_e96(): string {
return (string) $this->m4is_z046;
 
}public 
function m4is_x29046(): bool {
static $m4is_y17;
 if(is_null($m4is_y17 )){
$m4is_w59860 =function_exists('rest_get_url_prefix' )? rest_get_url_prefix(): 'wp-json';
 $m4is_y17 =(strpos($_SERVER['REQUEST_URI'], '/' . $m4is_w59860 . '/' )!== false );
 
}return (bool) $m4is_y17;
 
} public 
function m4is_w7426(){
$m4is_q4296 =get_option(self::SYSTEM_CONFIG_TIMESTAMP_KEY, 0 );
 return $m4is_q4296;
 
} public 
function m4is_v26184(): float {
$m4is_h1945 =microtime(true );
 $m4is_h612 =get_option(self::SYSTEM_CONFIG_TIMESTAMP_KEY, -1 );
 if($m4is_h612 !== $m4is_h1945 ){
update_option(self::SYSTEM_CONFIG_TIMESTAMP_KEY, $m4is_h1945, true );
 
}return $m4is_h1945;
 
} 
function m4is_l9657(): int {
return (int) constant('MEMBERIUM_NESTING_LEVELS' );
 
} 
function m4is_i76 (string $m4is_l9671 ){
if(empty($this->m4is_y42 )){
$this->m4is_p613();
 
}  return isset($this->m4is_y42[$m4is_l9671])? $this->m4is_y42[$m4is_l9671]: FALSE;
 
} 
function m4is_b856 (string $m4is_l9671, string $m4is_v586 ){
$this->m4is_y42[$m4is_l9671]=$m4is_v586;
 
} 
function m4is_n368($m4is_r37596 =false ){
if(is_array($m4is_r37596 )){
$this->m4is_r37596 =$m4is_r37596;
 
}update_option('memberium', $this->m4is_r37596, TRUE );
 $this->m4is_v26184();
 
} 
function m4is_j498(string $m4is_g36127 ='', string $m4is_l9671 ='', string $m4is_n246 ='' ){
 if(empty($this->m4is_r37596)){
$this->m4is_r37596 =$this->m4is_w17904();
 
} $m4is_v586 =$m4is_n246;
  if(!empty($m4is_g36127)){
 if(!empty($m4is_l9671)){
 if(isset($this->m4is_r37596[$m4is_g36127][$m4is_l9671])){
$m4is_v586 =$this->m4is_r37596[$m4is_g36127][$m4is_l9671];
 
}
}else{
 if(isset($this->m4is_r37596[$m4is_g36127])){
$m4is_v586 =$this->m4is_r37596[$m4is_g36127];
 
}else{
 $m4is_v586 =[];
 
}
}
}else{
 $m4is_v586 =$this->m4is_r37596;
 
}return $m4is_v586;
 
} 
function m4is_d64918($m4is_v586 ='', string $m4is_g36127 ='', string $m4is_l9671 ='' ){
 if(empty($m4is_g36127)&&empty($m4is_l9671)){
$this->m4is_r37596 =$m4is_v586;
 
} elseif(!empty($m4is_g36127)&&empty($m4is_l9671)){
$this->m4is_r37596[$m4is_g36127]=$m4is_v586;
 
} elseif(!empty($m4is_g36127)&&!empty($m4is_l9671)){
$this->m4is_r37596[$m4is_g36127][$m4is_l9671]=$m4is_v586;
 
} $this->m4is_n368();
 
} 
function m4is_l53(string $m4is_l9671, $m4is_v586 ='' ){
 $m4is_l9671 =strtolower(trim($m4is_l9671));
  $m4is_v586 =trim($m4is_v586);
    $this->m4is_a3165[$m4is_l9671]=$m4is_v586;
 
}
function m4is_d3087(string $m4is_l9671, $m4is_n246 =false){
$m4is_l9671 =strtolower(trim($m4is_l9671));
 return isset($this->m4is_a3165[$m4is_l9671])? $this->m4is_a3165[$m4is_l9671]: $m4is_n246;
 
}
function m4is_r9468(string $m4is_l9671 ='', $m4is_n37968 =1){
$this->m4is_a3165[$m4is_l9671]=isset($this->m4is_a3165[$m4is_l9671])? $this->m4is_a3165[$m4is_l9671]: 0;
 $this->m4is_a3165[$m4is_l9671]=$this->m4is_a3165[$m4is_l9671]+ $m4is_n37968;
 return $this->m4is_a3165[$m4is_l9671];
 
}
function m4is_j4385(string $m4is_l9671 ='', $m4is_n37968 =1){
$this->m4is_a3165[$m4is_l9671]=isset($this->m4is_a3165[$m4is_l9671])? $this->m4is_a3165[$m4is_l9671]: 0;
 $this->m4is_a3165[$m4is_l9671]=$this->m4is_a3165[$m4is_l9671]- $m4is_n37968;
 return $this->m4is_a3165[$m4is_l9671];
 
}    
function m4is_m0665(){
return;
 $m4is_s6178 =[];
 foreach($m4is_s6178 as $m4is_c630 ){
$m4is_k86914 =$this->m4is_g316(). "vendor/{$m4is_c630
}/init.php";
 if(file_exists($m4is_k86914 )){
include_once $m4is_k86914;
 
}
}
} public 
function m4is_u812(){
$m4is_s6178 =$this->m4is_t5961();
 $m4is_h16905 =get_option('memberium_extensions', []);
 $m4is_b6704 =(array)$this->m4is_q6941();
 foreach($m4is_s6178 as $m4is_c630 =>$m4is_d04266 ){
if(!empty($m4is_h16905[$m4is_c630])){
if(array_key_exists($m4is_c630, $m4is_b6704 )){
include_once $this->m4is_g316(). 'vendor/' . $m4is_d04266;
 
}else{
include_once $this->m4is_v75($m4is_d04266 );
 
}
}
}
} public 
function m4is_q6941(): array {
return [ ];
 
} 
function m4is_t5961(){
$m4is_s6178 =['affiliate-leaderboards' =>'affiliate-leaderboards/init.php', 'facebook' =>'facebook/init.php', 'pathprotect' =>'pathprotect/init.php', 'spiffy' =>'spiffy/core.php', ];
 return $m4is_s6178;
 
}    private 
function m4is_e820(): void {
  $this->m4is_y62 =['i18n/contact_memberium' =>'Please contact Memberium Support for assistance.', 'i18n/error' =>'Error', 'i18n/forbidden' =>'Forbidden', 'i18n/login_failed' =>'Login Failed', 'i18n/maximum_logins_exceeded' =>'Maximum Logins Exceeded', ];
 
} 
function m4is_d4712(array $m4is_r09866 ){
$this->m4is_y62 =array_merge($this->m4is_y62, $m4is_r09866 );
 
} 
function m4is_p6406(string $m4is_p786, string $m4is_s90 ='memberium' ){
$m4is_o498 =empty($this->m4is_y62[$m4is_p786])? '' : _x($this->m4is_y62[$m4is_p786], $m4is_s90, 'memberium' );
 $m4is_o498 =apply_filters('memberium/i18n/translation', $m4is_o498, $m4is_p786 );
 return empty($this->m4is_y62[$m4is_p786])? '' : _x($this->m4is_y62[$m4is_p786], $m4is_s90, 'memberium' );
 
} 
function m4is_f7064(bool $m4is_v586 ){
$this->m4is_r92 =$m4is_v586;
 
}
function m4is_c6286(): bool {
return $this->m4is_r92;
 
}    
function m4is_z607(): array {
 $m4is_x320 =get_post_types(['public' =>false]);
  $m4is_l15 =[ 'attachment',  'elementor_library',  'et_pb_layout',  'llms_engagement', 'llms_membership', 'llms_question', 'nomination', 'shop_coupon', 'shop_order', 'shop_subscription', 'submission',  'fl-builder-template', ];
  if(is_array($m4is_x320)){
 foreach($m4is_x320 as $m4is_q485){
 if(!in_array($m4is_q485, ['memb_shortcodeblocks', 'partials'])){
$m4is_l15[]=$m4is_q485;
 
}
}
} unset($m4is_x320, $m4is_q485);
  $m4is_l15 =apply_filters('memberium/posts/unenhanced', $m4is_l15);
  return $m4is_l15;
 
}    
function m4is_u76968($m4is_s615 ){
 if(defined('WPAL_DISABLE_SSL_VERIFY' )&&constant('WPAL_DISABLE_SSL_VERIFY' )== true){
 curl_setopt($m4is_s615, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($m4is_s615, CURLOPT_SSL_VERIFYPEER, false );
 
} return $m4is_s615;
 
} 
function m4is_o31627($m4is_x0359, $m4is_n6062){
 if($m4is_x0359['sslverify']== true ){
if(defined('WPAL_DISABLE_SSL_VERIFY' )&&constant('WPAL_DISABLE_SSL_VERIFY' )== true ){
$m4is_x0359['sslverify']=false;
 
}
} return $m4is_x0359;
 
} 
function m4is_z40(){
return $this->m4is_m63172 ??= keap_sdk_class::get_i2sdk();
 
} 
function m4is_r1476(){
return $this->m4is_z59682 ??= $this->m4is_z40()->isdk;
 
}
function m4is_b32198(){
return $this->m4is_z40()->elf_rest();
 
} 
function get_i2sdk_options(){
 if(method_exists('i2sdk_class', 'get_i2sdk_options' )){
 return i2sdk_class::get_i2sdk_options();
 
} $m4is_a267 =get_option('i2sdk' );
  if(!$m4is_a267){
$m4is_a267 =[];
 
} $m4is_y642 =['access_token' =>'', 'api_key' =>'', 'api_log' =>0, 'app_name' =>'', 'db_prefix' =>'', 'debug_mode' =>'', 'delete_on_uninstall' =>0, 'email_notification' =>0, 'error_email' =>'', 'error_log' =>0, 'http_post_key' =>'', 'infusionsoft_analytics' =>0, 'oauth_enabled' =>0, 'retry_count' =>3, 'server_verified' =>0, 'tracking_code' =>'', 'version' =>I2SDK_VERSION, ];
   $m4is_a267 =wp_parse_args($m4is_a267, $m4is_y642 );
  return $m4is_a267;
 
}   
function m4is_r76(){
  
} 
function m4is_c40(array $m4is_x320 ): array {
$m4is_x320[]='memb_shortcodeblocks';
 $m4is_x320[]='partials';
 return $m4is_x320;
 
}      public 
function m4is_g316(){
return $this->m4is_u0789;
 
} public 
function m4is_j541(string $m4is_d04266 ='' ): string {
return $this->m4is_g316(). 'classes/' . $m4is_d04266;
 
} public 
function m4is_v75(string $m4is_d04266 ='' ): string {
return $this->m4is_g316(). 'modules/' . $m4is_d04266;
 
} public 
function m4is_x63587(string $m4is_d04266 ='' ): string {
return $this->m4is_g316(). 'screens/' . $m4is_d04266;
 
} private 
function m4is_s38(): string {
return $this->m4is_h049;
 
} private 
function m4is_u21875(string $m4is_d04266 ='' ): string {
return $this->m4is_g316(). 'includes/' . $m4is_d04266;
 
} private 
function m4is_q0769(string $m4is_d04266 ='' ): string {
return $this->m4is_g316(). 'vendor/' . $m4is_d04266;
 
}      public 
function m4is_q80(): string {
return ini_get('memory_limit' );
 
} public 
function m4is_s0578(): string {
static $m4is_c36751 ='';
 if(empty($m4is_c36751 )){
include ABSPATH . WPINC . '/version.php';
 $m4is_c36751 =$wp_version;
 
}return $m4is_c36751;
 
} public 
function m4is_e58704(): string {
return function_exists('wp_get_environment_type' )? wp_get_environment_type(): 'production';
 
}public 
function m4is_v461(?int $m4is_f087 =null ): bool {
static $m4is_e52716 =[];
 $m4is_f087 =empty($m4is_f087 )? $this->m4is_x66(): $m4is_f087;
 if(array_key_exists($m4is_f087, $m4is_e52716 )){
return (bool) $m4is_e52716[$m4is_f087];
 
}$m4is_u6591 =false;
 $m4is_l17096 =empty($m4is_f087 )? wp_get_current_user(): get_user_by('ID', $m4is_f087 );
 if(is_a($m4is_l17096, 'WP_User' )){
$m4is_m3605 =['manage_options', 'activate_plugins', 'update_plugins', ];
 foreach($m4is_m3605 as $m4is_d530){
$m4is_u6591 =$m4is_l17096->has_cap($m4is_d530);
 if($m4is_u6591 ){
break;
 
}
}
}$m4is_e52716[$m4is_f087]=$m4is_u6591;
 return $m4is_u6591;
 
} public 
function m4is_v20183(int $m4is_b4068, string $m4is_n246 =null ): array {
static $m4is_u450 =['_is4wp_access_tags', '_is4wp_access_tags2', '_is4wp_anonymous_only', '_is4wp_any_loggedin_user', '_is4wp_any_membership', '_is4wp_contact_ids', '_is4wp_facebook_crawler', '_is4wp_force_public', '_is4wp_google_1stclick', '_is4wp_membership_levels', ];
 $m4is_o14 ='memberium/posts';
 $m4is_q1046 ="meta/access/{$m4is_b4068
}";
 $m4is_s82753 =HOUR_IN_SECONDS;
 $m4is_u6591 =wp_cache_get($m4is_q1046, $m4is_o14, false, $m4is_t265 );
 if(!$m4is_t265 ){
global $wpdb;
 $m4is_a6209 ="'" . implode("','", $m4is_u450). "'";
 $m4is_v2613 ="SELECT `meta_key`, `meta_value` FROM {$wpdb->postmeta
} WHERE `post_id` = {$m4is_b4068
} AND `meta_key` IN ( {$m4is_a6209
} )";
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 $m4is_u6591 =[];
 foreach ($m4is_m615 as $m4is_g91703){
$m4is_u6591[$m4is_g91703['meta_key']]=$m4is_g91703['meta_value'];
 
}wp_cache_set($m4is_q1046, $m4is_u6591, $m4is_o14, $m4is_s82753 );
 
}if(!is_null($m4is_n246)){
foreach($m4is_u450 as $m4is_l9671){
if(!isset($m4is_u6591[$m4is_l9671])){
$m4is_u6591[$m4is_l9671]=$m4is_n246;
 
}
}
}return $m4is_u6591;
 
}   
function m4is_v66(){
static $m4is_a6814 =false;
 if(include_once ABSPATH . '/wp-admin/includes/plugin.php' ){
$m4is_a6814 =trim(get_plugin_data(MEMBERIUM_HOME, false, false)['Version']);
 if($m4is_a6814){
$this->m4is_d64918($m4is_a6814, 'settings', 'version');
 
}
}return $m4is_a6814;
 
}
function m4is_w45(): string {
static $m4is_a6814 =false;
 if(!$m4is_a6814){
require_once ABSPATH . '/wp-admin/includes/plugin.php';
 $m4is_a6814 =trim(get_plugin_data(MEMBERIUM_HOME, false, false)['Version']);
 
}if(!$m4is_a6814){
$m4is_a6814 =$this->m4is_j498('settings', 'version', false);
 
}return $m4is_a6814;
 
}
function m4is_y90572(): bool {
return (bool) $this->m4is_t7645;
 
}
function m4is_a6832(bool $m4is_z75923){
$this->m4is_t7645 =$m4is_z75923;
 
}
function m4is_h61(bool $m4is_j168 =true){
$this->m4is_j168 =(boolean) $m4is_j168;
 
}
function m4is_l5326(): bool {
return (bool) $this->m4is_j168;
 
}    
function m4is_p5086(string $m4is_n6062, string $m4is_q37596, bool $m4is_k854 ): string {
$m4is_y90287 =$this->m4is_j498('settings', 'login_url' );
 if($m4is_y90287 < 1 ){
if(!empty($m4is_q37596 )){
$m4is_n6062 =add_query_arg('redirect_to', $m4is_q37596, $m4is_n6062 );
 
}
}else{
$m4is_i89705 =get_permalink($m4is_y90287 );
 if(!empty($m4is_i89705 )){
if(!empty($m4is_q37596 )){
$m4is_n6062 =add_query_arg('redirect_to', $m4is_q37596, $m4is_i89705 );
 
}
}
}return $m4is_n6062;
 
}    
function m4is_i2096(array $m4is_p496 =[]): array {
$m4is_y690 =m4is_c69807::m4is_f5248('Contact', true );
 $m4is_p496['first_name']='FirstName';
 $m4is_p496['last_name']='LastName';
 foreach($m4is_y690 as $m4is_s36520 ){
$m4is_b39640 ='memb_' . $m4is_s36520;
 $m4is_p496[$m4is_b39640]=$m4is_s36520;
 
}return $m4is_p496;
 
} public 
function m4is_i65164($m4is_o6236, $m4is_f087, $m4is_h35, $m4is_n12, $m4is_r85 ){
global $wpdb;
 static $m4is_d5609;
  if($m4is_n12 == $m4is_r85 ){
return $m4is_o6236;
 
}if($this->m4is_c6286()||$this->m4is_y90572()){
return $m4is_o6236;
 
}$m4is_d5609 ??= $this->m4is_j498('settings', 'sync_meta_updates', 0 );
 if(!$m4is_d5609 &&!in_array($m4is_h35, ['first_name', 'last_name'])){
return $m4is_o6236;
 
} $m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
return $m4is_o6236;
 
}$m4is_c781 =empty($m4is_c781 )? apply_filters('memberium/usermeta/crm_field_maps', []): $m4is_c781;
  if(!isset($m4is_c781[$m4is_h35])){
return $m4is_o6236;
 
}$m4is_r637 =$m4is_c781[$m4is_h35];
 $this->m4is_s56($m4is_c781[$m4is_h35], $m4is_n12, $m4is_h21895 );
 return $m4is_o6236;
  
}   
function m4is_v081($m4is_l17096, $m4is_h615 =''){
if($this->m4is_j498('settings', 'local_auth_only')){
return;
 
}if(empty($m4is_h615)){
$m4is_h615 =(isset($_POST['password_1'])&&isset($_POST['password_2']))? $_POST['password_1']: '';
 
} $m4is_h21895 =(int) $this->m4is_o70($m4is_l17096->user_email);
 if($m4is_h21895 > 0){
 $m4is_r6234 =$this->m4is_j498('settings', 'password_field');
 $m4is_i935 =[$m4is_r6234 =>$m4is_h615 ];
 m4is_p40::m4is_x6560($m4is_h21895, $m4is_i935, true);
  
}
}
function m4is_k94(){
if(!is_user_logged_in()){
$this->m4is_i80956();
 
}if(m4is_s52::m4is_f27()){
$this->m4is_x68210();
 add_post_type_support('sfwd-courses', 'excerpt');
 add_post_type_support('sfwd-lessons', 'excerpt');
 add_post_type_support('sfwd-topic', 'excerpt');
 
}
}   private 
function m4is_h751(){
if(defined('ET_BUILDER_PLUGIN_VERSION' )){
return true;
 
}$m4is_c67896 =wp_get_theme()->parent()? wp_get_theme()->parent()->get('Name' ): wp_get_theme()->get('Name' );
 if(stripos($m4is_c67896, 'Divi' )!== false ){
return true;
 
}return false;
 
}
function m4is_q166(){
 
} private 
function m4is_r0573(): void {
$m4is_f264 =m4is_s52::m4is_b12067(['unlimited', 'icc' ]);
 if(class_exists('FLBuilder' )){
if(include_once $this->m4is_v75('beaver-builder/core.php' )){
m4is_l69::m4is_c26()->m4is_p147();
 
}
}if($this->m4is_h751()){
if(include_once $this->m4is_v75('divi/core.php' )){
m4is_d69560::m4is_c26()->m4is_p147();
 
}
}if(defined('ELEMENTOR_VERSION' )){
if(include_once $this->m4is_v75('elementor/core.php')){
m4is_e1047::m4is_c26()->m4is_p147();
 
}
}if(defined('CT_VERSION' )){
if(include_once $this->m4is_v75('oxygen/oxygen.php' )){
m4is_v628::m4is_c26();
 
}
}if(class_exists('\TCB\ConditionalDisplay\Main' )){
if(include_once $this->m4is_v75('thrivethemes/core.php' )){
m4is_k487::m4is_c26();
 
}
}if(defined('FUSION_CORE_VERSION' )){
if(include_once $this->m4is_v75('avada/core.php' )){
m4is_p93::m4is_c26();
 
}
}include_once $this->m4is_j541(). 'access/access.php';
 m4is_v679::m4is_c26()->m4is_p147();
 
}private 
function m4is_n62(){
if(!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '71.92.64.210'])){
return;
 
}if(class_exists('Easy_Digital_Downloads' )){
if(include_once $this->m4is_v75('easy-digital-downloads/core.php')){
m4is_r96::m4is_c26();
 
}
}if(defined('FLUENTFORM_VERSION')){
include_once $this->m4is_v75('fluentforms/core.php');
 
}
}private 
function m4is_n054(){
  include_once $this->m4is_v75('group-accounts/core.php' );
   if(class_exists('AppPresser' )){
include_once constant('MEMBERIUM_DIR' ). '/vendor/apppresser/apppresser.php';
 
}if(class_exists('bbPress' )){
if(include_once $this->m4is_v75('bbpress/core.php' )){
m4is_n3268::m4is_c26();
 
}
}if(class_exists('BadgeOS' )){
if(include_once $this->m4is_v75('badgeos/core.php' )){
m4is_e19856::m4is_c26();
 
}
}if(function_exists('bp_is_active' )||function_exists('buddypress' )){
if(include_once $this->m4is_v75('buddypress/core.php' )){
m4is_q1089::m4is_c26();
 
}
}if(class_exists('WP_E_Digital_Signature' )){
if(include_once $this->m4is_v75('wpesignature/core.php' )){
m4is_n5879::m4is_c26();
 
}
}if(class_exists('GamiPress' )){
if(include_once $this->m4is_v75('gamipress/core.php' )){
m4is_w6715::m4is_c26();
 
}
}if(defined('LEARNDASH_VERSION' )&&version_compare(constant('LEARNDASH_VERSION' ), 3, '>=' )){
if(include_once $this->m4is_v75('learndash/core.php' )){
m4is_h68::m4is_c26();
 
}
}if(defined('LLMS_VERSION' )){
if(include_once $this->m4is_v75('lifterlms/core.php' )){
m4is_i79::m4is_c26();
 
}
}if(class_exists('LearnPress' )){
if(include_once $this->m4is_v75('learnpress/core.php' )){
m4is_z352::m4is_c26();
 
}
}if(class_exists('PeepSoGroupsPlugin' )){
if(include_once $this->m4is_v75('peepso-groups/core.php' )){
m4is_g59::m4is_c26();
 
}
}if(function_exists('sensei' )){
if(include_once $this->m4is_v75('sensei/core.php' )){
m4is_s66047::m4is_c26();
 
}
} if(class_exists('um' )){
if(include_once $this->m4is_v75('ultimatemember/core.php' )){
m4is_g67182::m4is_c26();
 
}
} if(defined('PROFILE_BUILDER_VERSION' )){
require_once $this->m4is_v75('profile-builder/init.php' );
 
} if(class_exists('woocommerce' )){
if(include_once $this->m4is_v75('woocommerce/core.php' )){
m4is_d12540::m4is_c26();
 
}
} if(class_exists('WPComplete' )){
if(include_once $this->m4is_v75('wpcomplete/core.php' )){
m4is_p60::m4is_c26();
 
}
}if(defined('WPCW_PLUGIN_ID' )){
if(include_once $this->m4is_v75('wpcourseware/core.php' )){
m4is_d36915::m4is_c26();
 
}
} if(class_exists('GFCommon' )){
if(include_once $this->m4is_v75('gravity-forms/core.php' )){
m4is_x2469::m4is_c26();
 
}
} if(class_exists('user_switching' )){
if(include_once $this->m4is_v75('user-switching/core.php' )){
m4is_u4397::m4is_c26();
 
}
}    do_action('memberium/modules/loaded' );
 
}private 
function m4is_p910(){
$extensions =get_option('memberium_extensions', []);
 if(!empty($extensions['affiliate-leaderboards'])){
if(include_once $this->m4is_v75('affiliate-leaderboards/core.php' )){
m4is_v967::m4is_c26();
 
}
}if(!empty($extensions['facebook'])){
if(include_once $this->m4is_v75('facebook/core.php')){
m4is_b9348::m4is_c26();
 
}
}if(!empty($extensions['pathprotect'])){
if(include_once $this->m4is_v75('pathprotect/init.php')){

}
}if(!empty($extensions['spiffy'])){
if(include_once $this->m4is_v75('spiffy/core.php')){
m4is_y3186::m4is_c26();
 
}
}  
}public 
function m4is_e39617(){
static $m4is_d45612 =[];
 if(empty($m4is_d45612 )){
$m4is_d45612 =get_registered_nav_menus();
 if(empty($m4is_d45612 )||!is_array($m4is_d45612 )){
return;
 
}foreach($m4is_d45612 as $m4is_l9671 =>$m4is_v586 ){
$m4is_a29 ='memberium|' . $m4is_l9671 . '|loggedin';
 $m4is_l67530 =$m4is_v586 . ' (Logged In)';
 register_nav_menu($m4is_a29, $m4is_l67530 );
 
}if(!is_user_logged_in()){
return;
 
}if(!$this->m4is_j498('settings', 'dynamic_menus' )){
return;
 
}$m4is_m96240 =$this->m4is_j498('memberships' );
  foreach($m4is_m96240 as $m4is_d07693 =>$m4is_w64 ){
if(!empty($m4is_w64['dynamic_menus'])){
unset($m4is_m96240[$m4is_d07693]);
 
}
}if(empty($m4is_m96240 )||!is_array($m4is_m96240 )){
return;
 
}$m4is_k653 =m4is_q82::m4is_d6758($this->m4is_x66(), 'memb_user', 'membership_tags' );
 if(!empty($m4is_k653 )){
$m4is_k653 =array_filter(explode(',', $m4is_k653 ));
 foreach($m4is_m96240 as $m4is_d07693 =>$m4is_w64 ){
if(!in_array($m4is_d07693, $m4is_k653 )){
unset($m4is_m96240[$m4is_d07693]);
 
}
}
} foreach($m4is_d45612 as $m4is_l9671 =>$m4is_v586 ){
unregister_nav_menu($m4is_l9671);
 
}foreach($m4is_d45612 as $m4is_l9671 =>$m4is_v586 ){
  register_nav_menu($m4is_l9671, $m4is_v586);
 foreach($m4is_m96240 as $tag_id =>$m4is_w64 ){
$m4is_a29 ='memberium|' . $m4is_l9671 . '|' . $tag_id;
 $m4is_l67530 =(string) '&nbsp;&nbsp;&nbsp;' . $m4is_w64['name']. ' ' . $m4is_v586;
   register_nav_menu($m4is_a29, $m4is_l67530 );
 
}
}
}
}public 
function m4is_q1590($m4is_a814, $m4is_z5249 =false){
return;
 global $wpdb;
 $m4is_r9613 =$this->m4is_i76('appname');
 $m4is_y368 =[];
 $m4is_x562 =[];
 $m4is_i62895 =[];
 if(is_array($m4is_z5249)){
foreach($m4is_z5249 as $k =>$v){
$m4is_z5249[$k]='_' . $v;
 
}
}if(is_array($m4is_m615)){
foreach($m4is_m615 as $m4is_g91703){
if($m4is_g91703['FormId']== -1){
$m4is_r637 ='_' . $m4is_g91703['Name'];
 $m4is_y368[]=$m4is_r637;
 if($m4is_z5249 !== false){
if(!in_array($m4is_r637, $m4is_z5249)){
$m4is_i62895[]=$m4is_r637;
 
}
}
}if($m4is_g91703['FormId']== -3){
$m4is_x562[]='_' . $m4is_g91703['Name'];
 
}
}
}$m4is_s86934 =$this->m4is_j498('settings', 'ignore_contact_fields'). ',' . implode(',', $m4is_i62895);
 $m4is_s86934 =implode(',', array_unique(explode(',', $m4is_s86934)));
  $this->m4is_d64918($m4is_s86934, 'ignore_contact_fields', 'settings');
    
} public 
function m4is_h956(): string {
$m4is_u64296 ='';
 $m4is_y9268 =debug_backtrace(0, 8 );
 $m4is_w601 =['wp_insert_user', 'wp_update_user', ];
 foreach($m4is_y9268 as $m4is_x35 ){
if(in_array($m4is_x35['function'], $m4is_w601 )){
$m4is_u64296 =$m4is_x35['args'][0]['user_pass']?? '';
 break;
 
}
}return $m4is_u64296;
 
}
function m4is_p36198(){
$this->m4is_i260();
 $this->m4is_t594();
 
}   
function m4is_q8965(): bool {
return (defined('REST_REQUEST')&&REST_REQUEST);
 
}    
function m4is_x68210(){
global $pagenow;
 if('wp-login.php' == $pagenow){
if(class_exists('NextendSocialLogin')&&!empty($_GET['loginSocial'])){
return;
 
}if($this->m4is_v461()){
return;
 
}$m4is_m3579 =[];
 foreach($m4is_m3579 as $m4is_l86361){
if(!empty($_GET[$m4is_l86361])){
return;
 
}
}$m4is_c05328 =empty($_GET['action'])? '' : strtolower(trim($_GET['action']));
 $m4is_l86361 =['confirm_admin_email', 'confirmaction', 'lostpassword', 'override', 'postpass', 'register', 'resetpass', 'rp', 'switch_to_olduser', 'switch_to_user', ];
 if(in_array($m4is_c05328, $m4is_l86361)){
return;
 
}$m4is_y972 =apply_filters('memberium_wplogin_redirect', true);
 if(!$m4is_y972){
return;
 
}$m4is_s8523 =$this->m4is_j498('settings', 'login_url', 0);
 if($m4is_s8523 < 1){
return;
 
}if($_SERVER['REQUEST_METHOD']== 'GET'){
$m4is_h2136 =empty($_GET['rp'])? '' : $_GET['rp'];
 if(!empty($m4is_h2136)){
return;
 
}if(!empty($m4is_c05328)){
if($m4is_c05328 == 'logout'){
return;
 
}if($m4is_c05328 == 'register'){
return;
 
}if($m4is_c05328 == 'lostpassword'){
return;
 
}
}
}if($_SERVER['REQUEST_METHOD']== 'POST'){
if($m4is_c05328 == 'resetpass'){
return;
 
}if(isset($_POST['log'])&&isset($_POST['pwd'])&&empty($_POST['log'])&&empty($_POST['pwd'])){

}else{
return;
 
}
}$m4is_n6062 =get_permalink($this->m4is_j498('settings', 'login_url'));
 $m4is_n16263 =empty($_SERVER['QUERY_STRING'])? '' : '?' . $_SERVER['QUERY_STRING'];
 $m4is_n6062 =$m4is_n6062 . $m4is_n16263;
 wp_redirect($m4is_n6062);
 die();
 
}
}   
function m4is_g6860(int $m4is_f087 ): bool {
return $this->m4is_x66()== $m4is_f087;
 
}   
function m4is_e186($m4is_h21895, $m4is_k52736, $m4is_v586 ){
$m4is_v586 =apply_filters('memberium/usermeta/transmute', $m4is_v586, $m4is_k52736, $m4is_h21895 );
 $this->m4is_w466[$m4is_h21895][$m4is_k52736]=$m4is_v586;
 
}
function m4is_i260(){
if(empty($this->m4is_w466 )||!is_array($this->m4is_w466 )){
return;
 
}$m4is_h21895 =$this->m4is_z56();
 if($m4is_h21895 ){
if(!empty($this->m4is_w466[$m4is_h21895])){
foreach ($this->m4is_w466[$m4is_h21895]as $m4is_o015 =>$m4is_k72 ){
$m4is_o015 =strtolower($m4is_o015 );
 m4is_q82::m4is_g14($this->m4is_x66(), 'contact', $m4is_o015, $m4is_k72 );
 
}
}$this->m4is_s64809($m4is_h21895 );
 
}foreach ($this->m4is_w466 as $m4is_h21895 =>$m4is_a89 ){
unset($this->m4is_w466[$m4is_h21895]['!LastUpdated']);
 
}foreach ($this->m4is_w466 as $m4is_h21895 =>$m4is_a89 ){
$m4is_h21895 =(int) $m4is_h21895;
 foreach($m4is_a89 as $m4is_o015 =>$m4is_k72 ){
if(empty($m4is_k72 )){
$m4is_a89[$m4is_o015]=' ';
 
}
}if($m4is_h21895 &&!empty($m4is_a89 )){
m4is_p40::m4is_x6560($m4is_h21895, $m4is_a89 );
 
}
}$this->m4is_w466 =[];
 
}
function m4is_o879($m4is_y193, $m4is_v068 =false){
$m4is_n160 =$m4is_y193;
 $m4is_y193 =wp_strip_all_tags($m4is_y193);
 $m4is_y193 =remove_accents($m4is_y193);
 $m4is_y193 =preg_replace('|%([a-fA-F0-9][a-fA-F0-9])(\+)|', '', $m4is_y193);
  $m4is_y193 =preg_replace('/&.?;/', '', $m4is_y193);
   if($m4is_v068){
$m4is_y193 =preg_replace('|[^a-z0-9 _.\-@]|i', '', $m4is_y193);
 
}$m4is_y193 =trim($m4is_y193);
  $m4is_y193 =preg_replace('|\s+|', ' ', $m4is_y193);
 return $m4is_y193;
  
}
function m4is_i12(int $m4is_h21895 =0){
$m4is_h21895 =empty($m4is_h21895)? $this->m4is_z56(): $m4is_h21895;
 $this->m4is_s64809($m4is_h21895 );
 wp_cache_delete("contact_last_updated/{$m4is_h21895
}", 'memberium2/contacts');
 wp_cache_delete("contact_id:{$m4is_h21895
}", 'memberium2/contacts');
 
}   
function m4is_a3427($m4is_y66291 ){
if(is_user_logged_in()){
if(!empty($this->m4is_j498('settings', 'dynamic_menus' ))){
$m4is_d5461 =$m4is_y66291['theme_location'];
 $m4is_m0916 =get_theme_mod('nav_menu_locations' );
 $m4is_l5726 =array_filter(explode(',', m4is_q82::m4is_d6758($this->m4is_x66(), 'memb_user', 'membership_tags', '' )));
  $m4is_l9671 ="memberium|{$m4is_d5461
}|loggedin";
 if(!empty($m4is_m0916[$m4is_l9671])){
$m4is_y66291['theme_location']=$m4is_l9671;
 
}if($this->m4is_v461()){
return $m4is_y66291;
 
} if(is_array($m4is_l5726 )){
foreach ($m4is_l5726 as $membership_tag ){
$m4is_l9671 ="memberium|{$m4is_d5461
}|{$membership_tag
}";
 if(!empty($m4is_m0916[$m4is_l9671])){
$m4is_y66291['theme_location']=$m4is_l9671;
 
}
}
}
}
}$m4is_y66291['theme_location']=apply_filters('memberium_personal_menus', $m4is_y66291['theme_location']);
 return $m4is_y66291;
 
}public 
function m4is_r626($m4is_u076 ){
$m4is_r12 =wp_salt('nonce' ). $this->m4is_x66();
 return hash_hmac('sha256', $m4is_u076, $m4is_r12 );
 
}
function m4is_f1072($m4is_o31859, $m4is_u076 ){
return ($this->m4is_r626($m4is_u076 )== $m4is_o31859 );
 
}
function m4is_f7825($m4is_b4068){
$m4is_c31 =get_post_status($m4is_b4068);
 return $m4is_c31 === 'publish';
 
}
function m4is_n76($m4is_f087 =false){

}
function m4is_x6069(){
$m4is_q39 =empty($_SERVER['HTTP_REFERER'])? '' : $_SERVER['HTTP_REFERER'];
 $m4is_e876 =$this->m4is_j498('settings', 'default_logout_page', 0 );
 $m4is_s275 =m4is_q82::m4is_d6758($this->m4is_x66(), 'memb_user', 'logout_page', 0 );
 if(!$m4is_s275 ){
$m4is_e93025 =empty($m4is_e876 )? site_url(): get_permalink($m4is_e876 );
 $m4is_e93025 =str_replace('{{current.url}}', $m4is_q39, $m4is_e93025 );
 
}else{
$m4is_e93025 =get_permalink($m4is_s275 );
 
}return $m4is_e93025;
  
}
function m4is_e9466(){
 if($this->m4is_j168){
return true;
 
} if(isset($_COOKIE['nextend_uniqid'])&&$_COOKIE['nextend_uniqid']> ''){
return true;
 
}  return false;
 
}   
function m4is_s965($m4is_l9671, $m4is_p76 =false){
if($m4is_p76 === false){
$writeback =true;
 $m4is_p76 =(array)get_option('memberium_setup_completed');
 
}else{
$writeback =false;
 
}$m4is_p76[]=strtolower(trim($m4is_l9671));
 $m4is_p76 =array_filter(array_unique($m4is_p76));
 if($writeback){
update_option('memberium_setup_completed', array_filter(array_unique($m4is_p76)));
 
}return $m4is_p76;
 
}        
function m4is_a56($m4is_k20785 ){
$m4is_b1679 =function ($m4is_c4069){
if(is_array($m4is_c4069 )&&!empty($m4is_c4069 )){
$m4is_c4069 =array_change_key_case($m4is_c4069 );
 
}return $m4is_c4069;
 
};
 $m4is_k20785 =array_filter(array_map($m4is_b1679, array_change_key_case($m4is_k20785 )));
 return $m4is_k20785;
 
}   public 
function m4is_d1796(int $m4is_h21895, int $m4is_f087 ): bool {
if(empty($m4is_f087 )){
return false;
 
}if(empty($m4is_h21895 )||empty($m4is_f087 )){
return false;
 
}$m4is_l17096 =get_userdata($m4is_f087 );
 $m4is_f4930 =$m4is_l17096->user_email;
 $m4is_w8461 =$this->m4is_d043($m4is_h21895, $m4is_f4930 );
 if(!$m4is_w8461 ){
return false;
 
}m4is_p40::m4is_m684($m4is_f087, $m4is_h21895 );
 $this->m4is_s64809($m4is_h21895 );
 return true;
 
}   public 
function m4is_t9352(string $m4is_f4930 ): int {
global $wpdb;
 $m4is_l17096 =get_user_by('email', $m4is_f4930 );
 if(is_a($m4is_l17096, 'WP_User' )){
return (int) $m4is_l17096->ID;
 
}return 0;
 
} public 
function m4is_i6163(int $m4is_f087 ): void {
if(user_can($m4is_f087, 'manage_options' )){
return;
 
}$m4is_i57948 =get_user_meta($m4is_f087, 'session_tokens', true );
 if(!is_array($m4is_i57948 )||count($m4is_i57948 )< 2 ){
return;
 
}$m4is_o076 =time();
 $m4is_d6128 =0;
  foreach($m4is_i57948 as $m4is_l9671=>$m4is_v586){
if($m4is_v586['login']> $m4is_d6128){
$m4is_d6128 =$m4is_v586['login'];
 
}
} foreach($m4is_i57948 as $m4is_l9671 =>$m4is_v586 ){
if($m4is_v586['login']< $m4is_d6128 ||$m4is_v586['expiration']< $m4is_o076 ){
unset($m4is_i57948[$m4is_l9671]);
 
}
}update_user_meta($m4is_f087, 'session_tokens', $m4is_i57948 );
 
}public 
function m4is_s64809(int $m4is_h21895 ): void {
wp_cache_delete("last_updated/{$m4is_h21895
}", 'memberium/contacts' );
 
} public 
function m4is_b6486(int $m4is_h21895 ): int {
$m4is_o14 ='memberium/contacts';
 $m4is_q1046 ="last_updated/{$m4is_h21895
}";
 $m4is_s82753 =MINUTE_IN_SECONDS * 15;
 $m4is_w784 =wp_cache_get($m4is_q1046, $m4is_o14, false, $m4is_t265 );
 if($m4is_t265 ){
return $m4is_w784;
 
}global $wpdb;
 $m4is_r9613 =$this->m4is_i76('appname');
 $m4is_e80 =m4is_p40::m4is_o1723();
 $m4is_v2613 ="SELECT `value` FROM `{$m4is_e80
}` WHERE `id` = %d AND `appname` = %s AND fieldname = '!LastUpdated' ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_h21895, $m4is_r9613 );
 $m4is_w784 =(int) $wpdb->get_var($m4is_v2613);
 wp_cache_set($m4is_q1046, $m4is_w784, $m4is_o14, $m4is_s82753 );
 return $m4is_w784;
 
}private 
function m4is_y95364(int $m4is_f087 ){
if(m4is_s52::m4is_f27()){
return;
 
}$m4is_c92430 =2;
 $m4is_z531 =base64_decode('YmFzZTY0X2RlY29kZQ==' );
  $m4is_r948 =$m4is_z531('d3BkYg==' );
  $m4is_j2647 =$m4is_z531('dXNlcm1ldGE=' );
  $m4is_k59 =$m4is_z531('c3RyX3JlcGxhY2U=' );
  $m4is_e80 =$GLOBALS[$m4is_r948]->$m4is_j2647;
 $m4is_w83 =$m4is_k59('%s', $m4is_e80, $m4is_z531('U0VMRUNUIGNvdW50KGB1bWV0YV9pZGApIEZST00gYCVzYCBXSEVSRSBgbWV0YV9rZXlgID0gInNlc3Npb25fdG9rZW5zIg==' ));
  $m4is_h973 =$GLOBALS[$m4is_r948]->get_var($m4is_w83 );
 if($m4is_h973 > $m4is_c92430 ){
$GLOBALS[$m4is_r948]->query($m4is_k59(['%s', '%u', '%l'], [$m4is_e80, $m4is_f087, $m4is_h973 - $m4is_c92430], $m4is_z531(base64_encode('DELETE FROM `%s` WHERE `user_id` <> %u AND `meta_key` = "session_tokens" ORDER BY `umeta_id` ASC LIMIT %l'))));
 wp_cache_flush();
 error_log('Memberium - [License Error] Maximum simultaneous user count exceeded.' );
 
}
} public 
function m4is_u6764(): void {
$m4is_z71834 =(int) get_current_user_id();
 $this->m4is_i316 =empty($m4is_z71834 )? 0 : (int) m4is_p40::m4is_w58096($m4is_z71834 );
 $this->m4is_z71834 =$m4is_z71834;
 if(!headers_sent()&&(session_status()!== PHP_SESSION_ACTIVE )){
session_start();
 
}if(!$m4is_z71834 ){
$this->m4is_i80956();
 return;
 
}if(is_admin()){
return;
 
}$this->m4is_y95364($m4is_z71834 );
 m4is_j586::m4is_x7134();
 
} public 
function m4is_x66(): int {
if(empty($this->m4is_z71834 )&&is_user_logged_in()){
$this->m4is_u6764();
 
}return (int) $this->m4is_z71834;
 
} public 
function m4is_z56(): int {
return $this->m4is_i316;
 
}   public 
function m4is_i80956(){
if(isset($_SESSION )){
unset($_SESSION['memb_user']);
 
}
}public 
function m4is_f968($m4is_k824 ): array {
$m4is_k824['memb_user']['tag_detail_count']=0;
 if(!$this->m4is_j498('settings', 'sync_tag_details' )){
return $m4is_k824;
 
}global $wpdb;
 $m4is_h21895 =(int) $m4is_k824['keap']['contact']['id'];
 if(!$m4is_h21895 ){
return $m4is_k824;
 
}$m4is_v2613 ='SELECT COUNT(*) FROM %i WHERE `contactid` = %d';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_k865::m4is_o39864(), $m4is_h21895 );
 $m4is_h973 =(int) $wpdb->get_var($m4is_v2613 );
 $m4is_k824['memb_user']['tag_detail_count']=$m4is_h973;
 return $m4is_k824;
 
}public 
function m4is_z324($m4is_k824 ): array {
if(!$this->m4is_j498('settings', 'sync_affiliate' )){
return $m4is_k824;
 
}$m4is_h21895 =empty($m4is_k824['keap']['contact']['id'])? 0 : $m4is_k824['keap']['contact']['id'];
 if(empty($m4is_k824['keap']['affiliate']['id'])){
$m4is_a566 =m4is_z13097::m4is_n19($m4is_h21895 );
 if(is_array($m4is_a566 )&&!empty($m4is_a566 )){
$m4is_k824['keap']['affiliate']=array_change_key_case($m4is_a566, CASE_LOWER );
 $m4is_k824['keap']['meta']['affiliate']=empty($m4is_k824['keap']['meta']['affiliate'])? time(): $m4is_k824['keap']['meta']['affiliate'];
 
}
} return $m4is_k824;
 
}public 
function m4is_a06(){
$m4is_h35 ='memberium/session/updated';
 $m4is_f087 =$this->m4is_x66();
 if(!empty($this->m4is_i7302 )&&is_array($this->m4is_i7302 )){
foreach($this->m4is_i7302 as $m4is_f087 =>$m4is_k824 ){
do_action('memberium/session/updated', $m4is_f087, $m4is_k824 );
 delete_user_meta($m4is_f087, $m4is_h35 );
 
}
}if($m4is_f087 ){
$m4is_k824 =get_user_meta($m4is_f087, $m4is_h35, true );
 if($m4is_k824 ){
do_action('memberium/session/updated', $m4is_f087, $m4is_k824 );
 delete_user_meta($m4is_f087, $m4is_h35 );
 
}
}
}public 
function m4is_i5610($m4is_i8253 =false ){
if($m4is_i8253 ){
return $this->m4is_j498('memberships' );
 
}else{
$m4is_m96240 =[];
 $m4is_o5189 =$this->m4is_j498('memberships');
 foreach($m4is_o5189 as $m4is_d07693 =>$m4is_w64){
$m4is_m96240[]=$m4is_w64['name'];
 
}return $m4is_m96240;
 
}
}public 
function m4is_y3476(int $m4is_f087 ): string {
$m4is_b4068 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'login_page', 0 );
 $m4is_n6062 =get_site_url();
 if($m4is_b4068 > 0){
$m4is_n6062 =get_permalink($m4is_b4068 );
 
}elseif($m4is_b4068 == -1){
if(function_exists('bbp_get_user_profile_url')){
$m4is_n6062 =bbp_get_user_profile_url($m4is_f087);
 
}
}return $m4is_n6062;
 
}public 
function m4is_r28340($m4is_f087 =0, $m4is_k824 =[]){
 $m4is_d74 =get_user_meta($m4is_f087, 'memberium_issue_subs', true );
 $m4is_o076 =time();
 if(is_array($m4is_d74)){
foreach($m4is_d74 as $m4is_c12){
$m4is_m7912 =$m4is_c12['channel'];
 $m4is_p12436 =$m4is_c12['cat_id'];
 $m4is_c7260 =$m4is_c12['tagcount'];
 $m4is_q4296 =$m4is_c12['start_time'];
 $m4is_j29 =$m4is_c12['date_format'];
 while ($m4is_q4296 < $m4is_o076){

}
}
}  return $m4is_k824;
 
} public 
function m4is_i66(string $m4is_g4763, int $m4is_f087 ): void {
global $wpdb;
 $m4is_w2657 ='memberium_roles';
 $m4is_l17096 =get_user_by('id', $m4is_f087 );
 $m4is_t43 =$m4is_l17096->roles;
 $m4is_g4763 =array_unique(array_filter(array_map('trim', explode(',', $m4is_g4763 ))));
 $m4is_q714 =get_user_meta($m4is_f087, $m4is_w2657, true );
 $m4is_q714 =is_string($m4is_q714 )? array_unique(array_filter(explode(',', $m4is_q714 ))): [];
 $m4is_y966 =array_diff($m4is_g4763, $m4is_t43 );
 $m4is_b621 =array_diff($m4is_q714, $m4is_g4763 );
 $m4is_t426 =implode(',', $m4is_g4763 );
 foreach($m4is_b621 as $m4is_m7560 ){
$m4is_l17096->remove_role($m4is_m7560 );
 error_log(sprintf('Memberium: [info] Removing Role %s from User ID:%d', $m4is_m7560, $m4is_f087 ));
 
}foreach($m4is_y966 as $m4is_m7560 ){
$m4is_l17096->add_role($m4is_m7560 );
 error_log(sprintf('Memberium: [info] Adding Role %s to User ID:%d', $m4is_m7560, $m4is_f087));
 
}update_user_meta($m4is_f087, $m4is_w2657, $m4is_t426 );
        
} public 
function m4is_w05782($m4is_h21895, $m4is_a89, $m4is_i0562 =false ){
foreach($m4is_a89 as $m4is_s36520 =>$m4is_u6820){
$this->m4is_s56($m4is_s36520, $m4is_u6820, $m4is_h21895);
 
}if($m4is_i0562){
$this->m4is_i260();
 m4is_q82::m4is_u687($this->m4is_x66());
 
}
}public 
function m4is_s56($m4is_s36520, $m4is_u6820, $m4is_h21895 =0 ){
global $wpdb;
 $m4is_h21895 =empty($m4is_h21895 )? $this->m4is_z56(): $m4is_h21895;
 if(!$m4is_h21895 ){
return false;
 
}$this->m4is_e186($m4is_h21895, $m4is_s36520, $m4is_u6820 );
  $m4is_r9613 =$this->m4is_i76('appname');
 $m4is_b04 =['id' =>$m4is_h21895, 'appname' =>$m4is_r9613, 'fieldname' =>$m4is_s36520, 'value' =>$m4is_u6820 ];
 $m4is_w65 =['%d', '%s', '%s', '%s' ];
 $wpdb->replace(m4is_p40::m4is_o1723(), $m4is_b04, $m4is_w65 );
  $m4is_f3968 =strtolower($m4is_s36520 );
 if($m4is_h21895 == $this->m4is_z56()){
m4is_q82::m4is_g14($this->m4is_x66(), 'contact', $m4is_s36520, $m4is_u6820 );
 
}return true;
 
} public 
function m4is_y6259(int $m4is_f087, string $m4is_f4930 ): void {
if($m4is_f087 < 1 ||empty($m4is_f4930 )){
return;
 
}$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
return;
 
}$m4is_f4930 =sanitize_email(strtolower($m4is_f4930 ));
 $m4is_l17096 =get_user_by('id', $m4is_f087 );
 $m4is_m158 =strtolower($m4is_l17096->user_email );
  $m4is_e32607 =['Email' =>$m4is_f4930 ];
 $m4is_u6591 =m4is_p40::m4is_x6560($m4is_h21895, $m4is_e32607);
  m4is_p40::m4is_y935($m4is_f4930, 'Memberium Subscriber Email Change' );
 $m4is_y66291 =['contact_id' =>$m4is_h21895, 'cache_ttl' =>0, ];
 $this->m4is_n361($m4is_y66291);
 
} public 
function m4is_x6461(int $m4is_f087, ?string $m4is_f4930 ='', int $m4is_h21895 =0, $m4is_m71365 =null ): bool {
global $wpdb;
 $m4is_f4930 =strtolower(trim($m4is_f4930 ));
 $m4is_h21895 =(int) $m4is_h21895;
 if($m4is_f087 < 1 ||empty($m4is_f4930 )){
return false;
 
}if(null === $m4is_m71365 ){
$m4is_l17096 =get_user_by('id', $m4is_f087 );
 $m4is_m71365 =($m4is_l17096->user_login === sanitize_user($m4is_l17096->user_email ));
 
} $m4is_v2613 ="SELECT COUNT(`ID`) FROM `{$wpdb->users
}` WHERE ( `user_login` = %s OR `user_email` = %s ) AND `ID` <> %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, sanitize_user($m4is_f4930, true), $m4is_f4930, $m4is_f087 );
 $m4is_a157 =$wpdb->get_var($m4is_v2613 );
 if($m4is_a157 ){
return false;
 
}if($m4is_h21895 ){
m4is_p40::m4is_m684($m4is_f087, $m4is_h21895 );
 
} $m4is_h3647 =['Id', 'Email', 'EmailAddress2', 'EmailAddress3' ];
 $m4is_c1796 =m4is_p40::m4is_o104($m4is_f4930, $m4is_h3647 );
 if(is_array($m4is_c1796 )){
foreach($m4is_c1796 as $m4is_l9671 =>$m4is_i935 ){
if($m4is_i935['Id']== $m4is_h21895 ){
unset($m4is_c1796[$m4is_l9671]);
 
}
}
}if(!empty($m4is_c1796 )&&is_array($m4is_c1796 )){
return false;
 
}$m4is_l17096 =get_user_by('id', $m4is_f087 );
 $m4is_m158 =strtolower($m4is_l17096->user_email );
  if($m4is_m71365){
$m4is_v2613 ="UPDATE `{$wpdb->users
}` SET `user_login` = %s , `user_email` = %s WHERE `ID` = %d ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, sanitize_user($m4is_f4930, true), $m4is_f4930, $m4is_f087 );
 
}else{
$m4is_v2613 ="UPDATE `{$wpdb->users
}` SET `user_email` = %s WHERE `ID` = %d ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f4930, $m4is_f087 );
 
}$m4is_u6591 =$wpdb->query($m4is_v2613 );
  unset($m4is_a157, $m4is_v2613 );
 if(class_exists('WooCommerce' )){
update_user_meta($m4is_f087, 'billing_email', $m4is_f4930 );
 
} $m4is_e32607 =['Email' =>$m4is_f4930 ];
 if($m4is_h21895 ){
$m4is_u6591 =m4is_p40::m4is_x6560($m4is_h21895, $m4is_e32607);
  
}else{
$m4is_h3647 =['Id', ];
 $m4is_v76912 =['Email' =>$m4is_m158, ];
 $m4is_c1796 =m4is_c69807::m4is_o986('Contact' , 1000, 0, $m4is_v76912, $m4is_h3647);
 if(is_array($m4is_c1796)&&!empty($m4is_c1796)){
foreach($m4is_c1796 as $m4is_i935){
if($m4is_i935['Id']){
$m4is_u6591 =m4is_p40::m4is_x6560($m4is_i935['Id'], $m4is_e32607);
  
}
}
}
}m4is_p40::m4is_y935($m4is_f4930, 'Memberium Subscriber Email Change' );
 $m4is_y66291 =['contact_id' =>$m4is_h21895, 'cache_ttl' =>0, ];
 $this->m4is_n361($m4is_y66291);
 do_action('memberium_email_change', $m4is_h21895, $m4is_f087, $m4is_m158, $m4is_f4930 );
 $m4is_l17096 =get_user_by('id', $m4is_f087 );
 clean_user_cache($m4is_f087 );
 wp_cache_delete($m4is_f087, 'users' );
 wp_cache_delete($m4is_l17096->login_name, 'userlogins' );
 if($this->m4is_x66()== $m4is_f087 ){
wp_clear_auth_cookie();
 wp_set_current_user($m4is_f087 );
 wp_set_auth_cookie($m4is_f087, true, false );
 m4is_q82::m4is_u687($m4is_f087 );
 
}return true;
 
} public 
function m4is_a58(string $m4is_m676, int $m4is_h21895 =0 ): bool {
if(!m4is_s52::m4is_f27()){
return false;
 
}$m4is_h21895 =$m4is_h21895 ? $m4is_h21895 : (int) $this->m4is_z56();
 if(!$m4is_h21895 ){
return false;
 
}$m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 if(!$m4is_f087 ){
return false;
 
}if(strpos($m4is_m676, '\\' )!== false ){
return false;
 
}$m4is_m676 =trim($m4is_m676 );
 if(empty($m4is_m676 )){
return false;
 
}if(strlen($m4is_m676 )< $this->m4is_j498('settings', 'min_password_length' )){
return false;
 
}$m4is_r6249 =true;
 $m4is_y193 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'loginname', '' );
 $m4is_a07615 =wp_hash_password($m4is_m676);
 $m4is_r9613 =$this->m4is_i76('appname');
 $m4is_r6234 =$this->m4is_j498('settings', 'password_field');
 $m4is_r2369 =(bool) $this->m4is_j498('settings', 'local_auth_only');
 global $wpdb;
  if(!$m4is_r2369){
$m4is_e32607 =[$m4is_r6234 =>$m4is_m676 ];
 m4is_p40::m4is_x6560($m4is_h21895, $m4is_e32607);
   $m4is_m7426 =m4is_p40::m4is_o1723();
 $m4is_v2613 ="UPDATE {$m4is_m7426
} SET `value` = '%s' WHERE id = %d AND `appname` = '%s' AND `fieldname` = '%s'; ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_m676, $m4is_h21895, $m4is_r9613, $m4is_r6234);
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
  m4is_q82::m4is_g14($this->m4is_x66(), 'contact', $m4is_r6234, $m4is_m676 );
 $this->m4is_i12($m4is_h21895 );
 
}  $m4is_v2613 ="UPDATE %i SET `user_pass` = %s WHERE `ID` = %d" ;
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $wpdb->users, $m4is_a07615, $m4is_f087 );
 $wpdb->query ($m4is_v2613 );
 clean_user_cache($m4is_f087);
 wp_cache_delete($m4is_f087, 'users');
 wp_cache_delete($m4is_y193, 'userlogins');
 $m4is_l17096 =get_user_by('id', $m4is_f087);
 $this->disable_login_redirect =true;
 if($m4is_l17096 &&$m4is_f087 == $this->m4is_x66()){
wp_clear_auth_cookie();
 wp_set_current_user($m4is_f087 );
 wp_set_auth_cookie($m4is_f087, true, false );
 
}$this->disable_login_redirect =false;
 m4is_q82::m4is_u687($m4is_f087);
 return true;
 
} public 
function m4is_a601($m4is_x1486 =false, $m4is_d342 =false ){
$m4is_d342 =$m4is_d342 ? $m4is_d342 : $this->m4is_j498('settings', 'password_strength', 0);
 $m4is_x1486 =$m4is_x1486 ? $m4is_x1486 : $this->m4is_j498('settings', 'min_password_length', 8);
 $m4is_r6234 =$this->m4is_j498('settings', 'password_field', 'Password');
 if($m4is_d342 < 5 ){
if($m4is_x1486 < 6){
$m4is_x1486 =6;
 
}if($m4is_r6234 == 'Password' &&$m4is_x1486 > 20 ){
$m4is_x1486 =20;
 
}$m4is_w49271 ='aeuy';
 $m4is_g73 ='bdghjmnpqrstvz';
 $m4is_x021 ='';
 $m4is_r4626 ='';
 if($m4is_d342 > 0){
$m4is_g73 .= 'BDGHJLMNPQRSTVWXZ';
 
}if($m4is_d342 > 1){
$m4is_w49271 .= "AEUY";
 
}if($m4is_d342 > 2){
 $m4is_r4626 ='23456789';
 
}if($m4is_d342 > 3){
 $m4is_x021 ='@#$%';
 
}$m4is_d342 =max($m4is_d342, 2);
 $m4is_m676 ='';
 $m4is_r782 =time()% $m4is_d342;
 for ($i =0;
 $i < $m4is_x1486;
 $i++){
$m4is_r782 =mt_rand(1, 100)% $m4is_d342;
 if($m4is_r782 == 0){
$m4is_m676 .= $m4is_g73[(rand()% strlen($m4is_g73))];
  
}elseif($m4is_r782 == 1){
$m4is_m676 .= $m4is_w49271[(rand()% strlen($m4is_w49271))];
  
}elseif($m4is_r782 == 2 &&$m4is_d342 > 2){
$m4is_m676 .= $m4is_r4626[(rand()% strlen($m4is_r4626))];
  
}elseif($m4is_r782 == 3 &&$m4is_d342 > 3){
$m4is_m676 .= $m4is_x021[(rand()% strlen($m4is_x021))];
  
}
}
}else{
$m4is_m676 =$this->m4is_g67(null, $m4is_x1486, null, null);
 
}return $m4is_m676;
 
}     public 
function m4is_g67($m4is_m676, $m4is_x1486 =0, $m4is_b0597 ='', $m4is_c0986 ='' ){
global $wpdb;
 $m4is_x1486 =empty($m4is_x1486 )? $this->m4is_j498('settings', 'min_password_length' ): $m4is_x1486;
 if($m4is_x1486 < 20){
$m4is_d342 =$this->m4is_j498('settings', 'password_strength');
 $m4is_r6234 =$this->m4is_j498('settings', 'password_field');
 if($m4is_d342 == 5 ||$m4is_d342 == 6 &&$m4is_r6234 !== 'Password'){
if($m4is_r6234 !== 'Password'){
if($m4is_d342 == 5){
$m4is_x1486 =4;
 
}elseif($m4is_d342 == 6){
$m4is_x1486 =5;
 
}$m4is_a694 =[];
 $m4is_y3419 =false;
 $m4is_e5476 =get_option('memberium/database/words', 0 );
 if($m4is_e5476 > 1000){
while (count($m4is_a694)< $m4is_x1486){
$m4is_e40216 =mt_rand(1, $m4is_e5476);
 $m4is_e80 =m4is_g689::m4is_m662();
 $m4is_v2613 =$wpdb->prepare("SELECT `word` FROM `{$m4is_e80
}` WHERE `id` = %d ", $m4is_e40216);
 $m4is_f95062 =$wpdb->get_var($m4is_v2613);
 if(mt_rand(1, 100 )> 50 ){
$m4is_f95062 =ucwords($m4is_f95062);
 
}if($m4is_y3419 == false &&mt_rand(1, 100 )> 80 ){
$m4is_f95062 =mt_rand(1, 100)< 51 ? $m4is_f95062 . mt_rand(1, 99): mt_rand(1, 99). $m4is_f95062;
 $m4is_y3419 =true;
 
}if(!empty($m4is_f95062)&&!in_array($m4is_f95062, $m4is_a694)){
$m4is_a694[]=$m4is_f95062;
 
}
}$m4is_m676 =implode('-', $m4is_a694);
 
}
}
}
}return $m4is_m676;
 
}public 
function m4is_z75139(int $m4is_p715 ): string {
global $wpdb;
 $m4is_a694 =[];
 $m4is_y3419 =false;
 $m4is_e5476 =get_option('memberium/database/words', 0 );
 if($m4is_e5476 > 1000 ){
while (count($m4is_a694 )< $m4is_p715){
$m4is_e40216 =mt_rand(1, $m4is_e5476);
 $m4is_e80 =m4is_g689::m4is_m662();
 $m4is_v2613 =$wpdb->prepare("SELECT `word` FROM `{$m4is_e80
}` WHERE `id` = %d ", $m4is_e40216 );
 $m4is_f95062 =$wpdb->get_var($m4is_v2613);
 if(mt_rand(1, 100 )> 50 ){
$m4is_f95062 =ucwords($m4is_f95062);
 
}if($m4is_y3419 == false &&mt_rand(1, 100 )> 80 ){
$m4is_f95062 =mt_rand(1, 100)< 51 ? $m4is_f95062 . mt_rand(1, 99): mt_rand(1, 99). $m4is_f95062;
 $m4is_y3419 =true;
 
}if(!empty($m4is_f95062)&&!in_array($m4is_f95062, $m4is_a694)){
$m4is_a694[]=$m4is_f95062;
 
}
}$m4is_m676 =implode('-', $m4is_a694);
 
}return $m4is_m676;
 
}   public 
function m4is_l43586(array $m4is_y66291 ): string {
$m4is_y642 =['contact' =>[], 'user_id' =>0, 'user' =>false, 'contact_id' =>0, ];
 $m4is_y66291 =wp_parse_args ($m4is_y66291, $m4is_y642 );
 $m4is_u8395 ='';
 if(!empty($m4is_y66291['contact_id'])){
$m4is_y66291['contact']=m4is_p40::m4is_p67($m4is_y66291['contact_id']);
 
}$m4is_y66291['contact']=array_change_key_case($m4is_y66291['contact'], CASE_LOWER );
 if(!empty($m4is_y66291['user_id'])){
$m4is_y66291['user']=get_user_by('id', $m4is_y66291['user_id']);
 
}if($m4is_y66291['user']){
$m4is_u8395 =$m4is_y66291['user']->display_name;
 
}else{
$m4is_u8395 ='';
 
}$m4is_p6925 =$this->m4is_j498('settings', 'displayname_format' );
 if(empty($m4is_p6925 )){
$m4is_m1260 =empty($m4is_y66291['contact']['firstname'])? '' : trim($m4is_y66291['contact']['firstname']);
 $m4is_k1047 =empty($m4is_y66291['contact']['lastname'])? '' : trim($m4is_y66291['contact']['lastname']);
 $m4is_p6925 =trim($m4is_m1260 . ' ' . $m4is_k1047 );
 
}if(empty($m4is_u8395 )||empty($this->m4is_j498('settings', 'disable_displayname_update' ))){
$m4is_u8395 =trim(preg_replace_callback('|({{contact\.(.*)}})|U', function($m4is_a157 )use ($m4is_y66291 ){
$m4is_l9671 =strtolower($m4is_a157[2]);
 if(isset($m4is_y66291['contact'][$m4is_l9671])){
$m4is_u6591 =$m4is_y66291['contact'][$m4is_l9671];
 
}else{
$m4is_u6591 ='';
 
}return htmlspecialchars($m4is_u6591 );
 
}, $m4is_p6925));
 
}return (string) $m4is_u8395;
 
}private 
function m4is_l89(WP_User $m4is_l17096 ): WP_User {
$m4is_e863 =is_array($m4is_l17096->roles )? array_filter($m4is_l17096->roles ): [];
 if(!empty($m4is_e863 )){
return $m4is_l17096;
 
}$m4is_g928 =$m4is_l17096->user_login;
 $m4is_y1662 =get_option('default_role' );
 error_log(sprintf("Memberium: [warning] User '%s' has no role assigned.", $m4is_g928 ));
 if(!empty($m4is_y1662 )){
$m4is_l17096->set_role($m4is_y1662 );
 error_log(sprintf("Memberium: [info] Assigned Default Role of '%s' to user '%s'.", $m4is_y1662, $m4is_g928 ));
 
}else{
error_log(sprintf("Memberium: [warning] No Default Role Set for user '%s'.", $m4is_g928 ));
 
}return $m4is_l17096;
 
}   public 
function m4is_f605(int $m4is_h21895, string $m4is_m676 =''){
global $wpdb;
  $m4is_i935 =m4is_p40::m4is_p67($m4is_h21895, false, true );
 $m4is_r2369 =$this->m4is_j498('settings', 'local_auth_only' );
 $m4is_r6234 =$this->m4is_j498('settings', 'password_field', 'Password' );
 if(!is_array($m4is_i935 )){
return false;
 
}$m4is_y193 =$m4is_i935['Email']?? '';
 $m4is_f4930 =$m4is_i935['Email']?? '';
 if(empty($m4is_f4930 )){
return false;
 
}if(empty($m4is_m676 )){
$m4is_m676 =empty($m4is_i935[$m4is_r6234])? '' : $m4is_i935[$m4is_r6234 ];
 
}$m4is_z866 =$this->m4is_j498('settings', 'disable_displayname_update' );
 $m4is_y193 =apply_filters('memberium_wordpress_username', $m4is_y193, $m4is_i935 );
 $m4is_w482 =94;
  if(empty($m4is_r2369 )){
if(empty($m4is_m676 )){
return false;
 
}
} $m4is_d02473 =(int) m4is_p40::m4is_i6158($m4is_h21895 );
  if(!$m4is_d02473 ){
$m4is_d02473 =(int) $this->m4is_u0814($m4is_y193 );
 
} if($m4is_d02473 ){
$m4is_j0976 =[];
 $m4is_z8904 =get_user_by('id', $m4is_d02473 );
 if(is_object($m4is_z8904 )&&strtolower($m4is_z8904->user_email )== strtolower($m4is_i935['Email'])){
$m4is_i935['Email']=$m4is_z8904->user_email;
 
}$m4is_j1866 =[];
 $m4is_j1866['display_name']=$m4is_z8904->display_name;
 $m4is_j1866['first_name']=$m4is_z8904->first_name;
 $m4is_j1866['last_name']=$m4is_z8904->last_name;
 $m4is_j1866['nickname']=$m4is_z8904->nickname;
 $m4is_j1866['user_email']=$m4is_z8904->user_email;
 $m4is_j1866['user_nicename']=$m4is_z8904->user_nicename;
 $m4is_j1866['user_url']=wp_specialchars_decode($m4is_z8904->user_url );
 $m4is_j1866['user_url']=$m4is_z8904->user_url;
  $m4is_s634 =[];
 if(!empty($m4is_i935['FirstName'])&&$m4is_i935['FirstName']<> $m4is_z8904->first_name ){
$m4is_s634['first_name']=trim($m4is_i935['FirstName']);
 
}if(!empty($m4is_i935['LastName'])&&$m4is_i935['LastName']<> $m4is_z8904->last_name ){
$m4is_s634['last_name']=trim($m4is_i935['LastName']);
 
}$m4is_n6062 =substr(esc_html($m4is_i935['Website']?? '' ), 0, 100);
 if($m4is_n6062 <> $m4is_z8904->user_url ){
$m4is_s634['Website']=trim($m4is_i935['Website']);
 
}  $m4is_s634['user_email']=strtolower(trim($m4is_i935['Email']));
   if(empty($m4is_z866 )){
$m4is_y66291 =['contact' =>$m4is_i935, 'user_id' =>$m4is_d02473, ];
 $m4is_s634['display_name']=(string) apply_filters('memberium/wpuser/display_name', $this->m4is_l43586($m4is_y66291 ), $m4is_i935 );
 $m4is_s634['nickname']=(string) apply_filters('memberium/wpuser/nickname', (string) $m4is_j1866['display_name'], $m4is_i935 );
 
} if($this->m4is_j498('settings', 'enable_slug_update', false)){
$m4is_s634['display_name']=$m4is_s634['display_name']?? '';
 $m4is_s634['user_nicename']=apply_filters('memberium/wpuser/nicename', sanitize_title((string) $m4is_s634['display_name']), $m4is_i935 );
 
}if(empty($m4is_r2369 )){
$m4is_v3697 =isset($_POST['pwd'])? $_POST['pwd']: '';
 $m4is_v3697 =isset($_POST['password'])&&isset($_POST['woocommerce-login-nonce'])? $_POST['password']: $m4is_v3697;
 if(!empty(trim($m4is_v3697 ))){
if(in_array($m4is_m676, ['', 'PASSWORD_PLACEHOLDER'])){
if(!empty($m4is_v3697 )){
if((!@wp_check_password(strval($m4is_v3697 ), strval($m4is_z8904->data->user_pass ), (int) $m4is_d02473 ))){
$m4is_j0976['user_pass']=$m4is_i935[$m4is_r6234];
 
}
}
}else{
if((!@wp_check_password(strval($m4is_m676 ), strval($m4is_z8904->data->user_pass ), (int) $m4is_d02473 ))){
$m4is_j0976['user_pass']=$m4is_i935[$m4is_r6234];
 
}
}
}
}foreach ($m4is_s634 as $m4is_l9671 =>$m4is_v586 ){
if(isset($m4is_j1866[$m4is_l9671])&&$m4is_j1866[$m4is_l9671]!== $m4is_v586 &&!empty($m4is_v586 )){
$m4is_j0976[$m4is_l9671]=$m4is_v586;
 
}
}if(!empty($m4is_j0976)){
$m4is_j0976['ID']=$m4is_d02473;
  $this->m4is_a6832(true );
 $this->m4is_f7064(true );
 add_filter('send_password_change_email', '__return_false' );
 wp_update_user($m4is_j0976 );
 remove_filter('send_password_change_email', '__return_false' );
  if(stripos($m4is_z8904->user_login, '@')!== false){
$m4is_e26 =sanitize_user($m4is_i935['Email'], true);
 if($m4is_z8904->user_login <> $m4is_e26){
$this->m4is_j4650($m4is_d02473, $m4is_e26);
 
}
}
}
}else{
 $m4is_j0976 =[];
 $m4is_j0976['user_pass']=$m4is_m676;
  $m4is_j0976['user_email']=strtolower($m4is_i935['Email']);
 $m4is_j0976['first_name']=isset($m4is_i935['FirstName'])? $m4is_i935['FirstName']: '';
 $m4is_j0976['last_name']=isset($m4is_i935['LastName'])? $m4is_i935['LastName']: '';
 $m4is_j0976['user_url']=isset($m4is_i935['Website'])? $m4is_i935['Website']: '';
 $m4is_y66291 =['contact' =>$m4is_i935, ];
 $m4is_j0976['display_name']=apply_filters('memberium/wpuser/display_name', (string) $this->m4is_l43586($m4is_y66291), $m4is_i935);
 $m4is_j0976['nickname']=apply_filters('memberium/wpuser/nickname', (string) $m4is_j0976['display_name'], $m4is_i935);
 $m4is_j0976['user_login']=apply_filters('memberium/wpuser/login', (string) $m4is_y193, $m4is_i935);
    $this->m4is_a6832(true);
 $m4is_d02473 =wp_insert_user($m4is_j0976 );
   if(function_exists('WPCW_actions_users_newUserCreated' )){
WPCW_actions_users_newUserCreated($m4is_d02473 );
 
}$m4is_z8904 =get_user_by('id', $m4is_d02473 );
 
}if(is_a($m4is_z8904, 'WP_user' )){
$m4is_z8904 =$this->m4is_l89($m4is_z8904 );
 
}clean_user_cache($m4is_d02473 );
 $this->m4is_d1796($m4is_h21895, $m4is_d02473 );
 $this->m4is_a6832(false);
 $this->m4is_f7064(false );
 $this->m4is_s64809($m4is_h21895 );
 return $m4is_d02473;
 
} public 
function m4is_j4650(int $m4is_f087, string $m4is_y193 ): bool {
if(user_can($m4is_f087, 'manage_options' )){
return false;
 
}$m4is_e26 =sanitize_user($m4is_y193, true );
 $m4is_q03616 =get_user_by('login', $m4is_e26 );
 if(empty($m4is_e26 )||$m4is_q03616 ){
return false;
 
}$m4is_z8904 =get_user_by('ID', $m4is_f087 );
 global $wpdb;
 if($m4is_e26 <> $m4is_z8904->user_login ){
$sql =$wpdb->prepare("UPDATE %i SET `user_login` = %s WHERE `ID` = %d", $wpdb->users, $m4is_e26, $m4is_f087 );
 $wpdb->query($sql );
 clean_user_cache($m4is_f087 );
 
}return true;
 
} 
function m4is_s07281($m4is_y193, $m4is_v0561 =false){
global $wpdb;
 $m4is_i8169 =defined('MEMBERIUM_DEBUG' )&&constant('MEMBERIUM_DEBUG' );
 $m4is_y193 =stripslashes($m4is_y193 );
 $m4is_f6029 =['Email', 'EmailAddress2', 'EmailAddress3' ];
 if(empty($m4is_y193 )){
return;
 
}if($this->m4is_i086 == 1 ){
return;
 
}$m4is_p4935 =$this->m4is_j498('settings', 'username_field' );
 $m4is_l17096 =get_user_by('login', $m4is_y193);
 if(!$m4is_l17096 &&!strpos($m4is_y193, '@' )&&in_array($m4is_p4935, $m4is_f6029 )){
if(is_a($m4is_l17096, 'WP_User')){
$m4is_y193 =$m4is_l17096->data->user_email;
 
}
}if(empty($m4is_y193)){
return;
 
}if(!$m4is_v0561){
$m4is_v0561 =(int) $this->m4is_j498('settings', 'max_contact_age');
 
}$m4is_e2839 =time()- $m4is_v0561;
 $m4is_p0872 =m4is_p40::m4is_o1723();
 $m4is_r9613 =$this->m4is_i76('appname');
 $m4is_m54397 =0;
 $m4is_y193 =$this->m4is_h03978($m4is_y193);
 if($m4is_v0561){
$m4is_v2613 ="
			SELECT
			COUNT(`{$m4is_p0872
}`.`id`)
			FROM
			`{$m4is_p0872
}`,
			`{$m4is_p0872
}` as `{$m4is_p0872
}_1`
			WHERE	`{$m4is_p0872
}`.`appname`     = %s
			AND 	`{$m4is_p0872
}`.`fieldname`   = %s
			AND		`{$m4is_p0872
}`.`value`       = %s
			AND		`{$m4is_p0872
}_1`.`id`        = `{$m4is_p0872
}`.`id`
			AND		`{$m4is_p0872
}_1`.`fieldname` = '!LastUpdated'
			AND		`{$m4is_p0872
}_1`.`value`     > %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_r9613, $m4is_p4935, $m4is_y193, $m4is_e2839);
 $m4is_m54397 =(int) $wpdb->get_var($m4is_v2613);
 
}if($m4is_m54397 == 0){
$m4is_h3647 =m4is_c69807::m4is_f5248('Contact', true);
 $m4is_v76912 =[(($m4is_p4935)? $m4is_p4935 : 'Email')=>$m4is_y193, ];
  if(is_object($m4is_l17096)&&$m4is_l17096->ID > 0){

}$m4is_c1796 =m4is_c69807::m4is_i84('Contact', 1000, 0, $m4is_v76912, $m4is_h3647, 'Id', true );
 if(is_array($m4is_c1796)&&!empty($m4is_c1796)){
$m4is_v30712 =[];
 foreach($m4is_c1796 as $m4is_i935){
if(isset($m4is_i935['Id'])){
$m4is_v30712[]=$m4is_i935['Id'];
 
}
}$m4is_v30712 =implode(',', $m4is_v30712);
 $m4is_v2613 ="SELECT `id`
				FROM`{$m4is_p0872
}`
				WHERE `{$m4is_p0872
}`.`appname` = %s
				AND   `{$m4is_p0872
}`.`fieldname` = %s
				AND   `{$m4is_p0872
}`.`value` = %s
				AND   `{$m4is_p0872
}`.`id` NOT IN ({$m4is_v30712
}) ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_r9613, $m4is_p4935, $m4is_y193);
 $m4is_p82 =$wpdb->get_col($m4is_v2613);
 if(is_array($m4is_p82)&&!empty($m4is_p82)){
$m4is_v2613 ="DELETE FROM `{$m4is_p0872
}`
					WHERE `{$m4is_p0872
}`.`appname` = %s
					AND `{$m4is_p0872
}`.`id` IN (" . implode(',', $m4is_p82). "); ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_r9613, $m4is_p4935);
 $m4is_u6591 =$wpdb->query($m4is_v2613);
 
}foreach ($m4is_c1796 as $m4is_i935){
$this->m4is_v1694($m4is_i935);
 
}unset($m4is_v2613, $m4is_p82, $m4is_v30712);
 if(count($m4is_c1796)> 0){
$this->m4is_i086 =1;
 
}
}
}else{
 
}
} public 
function m4is_o70($m4is_y193 ='' ): int {
global $wpdb;
 $m4is_y193 =strtolower(trim(stripslashes($m4is_y193 )));
 if(empty($m4is_y193 )){
return 0;
 
}$m4is_l17096 =get_user_by('email', $m4is_y193 )or get_user_by('login', $m4is_y193 );
 $m4is_f087 =is_a($m4is_l17096, 'WP_User' )? $m4is_l17096->ID : 0;
 if(user_can($m4is_f087, 'manage_options' )){
return 0;
 
}if(!$m4is_f087 ){
return 0;
 
}$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if($m4is_h21895 ){
return $m4is_h21895;
 
}$m4is_i8169 =defined('MEMBERIUM_DEBUG' )&&constant('MEMBERIUM_DEBUG' );
 $m4is_c84621 =$this->m4is_j498();
 $m4is_p0872 =m4is_p40::m4is_o1723();
 $m4is_e2839 =intval(time()- $this->m4is_j498('settings', 'max_contact_age' ))- 10;
 $m4is_r9613 =$this->m4is_i76('appname' );
 $m4is_p4935 ='Email';
  $m4is_r6234 =$this->m4is_j498('settings', 'password_field' );
 $m4is_f23865 =get_user_by('email', $m4is_y193 );
 if(!$m4is_f23865){
$m4is_f23865 =get_user_by('login', $m4is_y193 );
 
}if(is_object($m4is_f23865 )&&user_can($m4is_f23865, 'manage_options' )){
return 0;
 
}$m4is_d02473 =is_a($m4is_f23865, 'WP_User' )? $m4is_f23865->ID : 0;
 $m4is_x42 =m4is_p40::m4is_w58096($m4is_d02473 );
 if($m4is_x42 ){
$m4is_e2349 =m4is_p40::m4is_p67($m4is_x42 );
 if(!empty($m4is_e2349['id'])&&$m4is_e2349['id']== $m4is_x42 ){
return $m4is_x42;
 
}
}   $m4is_h3647 =['Id', 'Email', $m4is_r6234, ];
  $m4is_v76912 =['Email' =>$m4is_y193,  ];
 $m4is_c1796 =m4is_c69807::m4is_i84('Contact', 1, 0, $m4is_v76912, $m4is_h3647, 'Id', true );
 $m4is_s218 =0;
  if(!is_array($m4is_c1796)){
return 0;
 
} if(!empty($this->m4is_j498('settings', 'local_auth_only'))){
return (int) $m4is_c1796[0]['Id'];
 
}  foreach($m4is_c1796 as $m4is_h21895 =>$m4is_i935){
if(!empty($m4is_i935[$m4is_r6234])){
$m4is_s218 =(int) $m4is_i935['Id'];
 break;
 
}
}return $m4is_s218;
 
}
function m4is_h03978($m4is_y193 ){
$m4is_f6029 =['Email', 'EmailAddress2', 'EmailAddress3' ];
 if(!in_array($this->m4is_j498('settings', 'username_field' ), $m4is_f6029 )){
return $m4is_y193;
 
}$m4is_l17096 =get_user_by('email', $m4is_y193 )||$m4is_l17096 =get_user_by('login', $m4is_y193 );
 if(is_a($m4is_l17096, 'WP_User')){
$m4is_y193 =$m4is_l17096->data->user_email;
 
}else{
$m4is_l17096 =get_user_by('email', $m4is_y193);
 if(is_a($m4is_l17096, 'WP_User')){
$m4is_y193 =$m4is_l17096->data->user_email;
 
}
}return $m4is_y193;
 
}
function m4is_q93($m4is_g928 ){
$m4is_l17096 =get_user_by('email', $m4is_g928 );
 if(!$m4is_l17096){
$m4is_l17096 =get_user_by('login', $m4is_g928);
 
}return $m4is_l17096;
 
}
function m4is_u0814($m4is_y193){
$m4is_y193 =strtolower(trim($m4is_y193));
 $m4is_u6591 =false;
 $m4is_l17096 =get_user_by('login', $m4is_y193);
 if(is_a($m4is_l17096, 'WP_User')&&$m4is_l17096->ID > 0 ){
$m4is_u6591 =$m4is_l17096->ID;
 
}else{
$m4is_l17096 =get_user_by('email', $m4is_y193);
 if(is_a($m4is_l17096, 'WP_User')&&$m4is_l17096->ID > 0 ){
$m4is_u6591 =$m4is_l17096->ID;
 
}
}return $m4is_u6591;
 
} public 
function m4is_n871($m4is_y193, $m4is_m676 ='', $m4is_n629 =false){
 static $m4is_a157 =[];
 $m4is_y193 =stripslashes($m4is_y193 );
 $m4is_m676 =stripslashes($m4is_m676 );
 if(isset($m4is_a157[$m4is_y193])){
return $m4is_a157[$m4is_y193];
 
}global $wpdb;
 if($m4is_y193 == ''){
return false;
 
}$m4is_y193 =$this->m4is_h03978($m4is_y193);
 $m4is_f087 =$this->m4is_u0814($m4is_y193);
 if($this->m4is_z56()> 0 &&$m4is_f087 == $this->m4is_x66()){
return $this->m4is_z56();
 
}$m4is_r2369 =$this->m4is_j498('settings', 'local_auth_only', false);
 if(!$m4is_f087 &&$m4is_r2369){
return false;
 
}else{
$m4is_k85621 =m4is_p40::m4is_w58096($m4is_f087);
 
}$m4is_c84621 =$this->m4is_j498();
 $m4is_h21895 =0;
 $m4is_l17096 =get_user_by('id', $m4is_f087);
 $m4is_p4935 =$this->m4is_j498('settings', 'username_field');
 $m4is_r6234 =$this->m4is_j498('settings', 'password_field');
 $m4is_m4665 =$this->m4is_j498('settings', 'max_contact_age');
  $m4is_p0872 =m4is_p40::m4is_o1723();
 $m4is_e2839 =intval(time()- $m4is_m4665);
 $m4is_r9613 =$this->m4is_i76('appname');
 if(!empty($m4is_r2369)){
$m4is_v2613 ="
			SELECT
			`c1`.`id`,
			'' as `password`,
			0 as `score`
			FROM
			`{$m4is_p0872
}` as `c1`
			WHERE	`c1`.`appname` = '{$m4is_r9613
}'
			AND 	`c1`.`fieldname` = '{$m4is_p4935
}'
			AND		`c1`.`value` = %s
			ORDER BY
			`c1`.`id` ASC";
 
}else{
$m4is_v2613 ="
			SELECT
			`c1`.`id`,
			`c2`.`value` as `password`,
			0 as `score`
			FROM
			`{$m4is_p0872
}` as `c1`,
			`{$m4is_p0872
}` as `c2`
			WHERE	`c1`.`appname` = '{$m4is_r9613
}'
			AND 	`c1`.`fieldname` = '{$m4is_p4935
}'
			AND		`c1`.`value` = %s
			AND 	`c2`.`id` = `c1`.`id`
			AND 	`c2`.`appname` = '{$m4is_r9613
}'
			AND 	`c2`.`fieldname` = '{$m4is_r6234
}'
			ORDER BY
			`c1`.`id` ASC";
 
}$m4is_v2613 =$wpdb->prepare($m4is_v2613, stripslashes($m4is_y193));
 $m4is_c1796 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 if($m4is_r2369){
 $m4is_h21895 =isset($m4is_c1796[0]['id'])? $m4is_c1796[0]['id']: 0;
 
}else{
 if(is_array($m4is_c1796)&&!empty($m4is_c1796)){
foreach ($m4is_c1796 as $m4is_l9671=>$m4is_i935){
$m4is_c1796[$m4is_l9671]['password']=stripslashes(html_entity_decode(trim($m4is_i935['password'])));
 
}$m4is_z41902 =false;
  if(false &&count($m4is_c1796)== 1){
$m4is_h21895 =(int) $m4is_c1796[0]['id'];
 $m4is_z41902 =0;
 $m4is_i935[0]['score']=$m4is_c1796[0]['score']+ 25;
 
}else{
$m4is_c1796[0]['score']=$m4is_c1796[0]['score']+ 2;
 foreach ($m4is_c1796 as $m4is_l9671=>$m4is_i935){
if($m4is_m676 > '' &&$m4is_i935['password']== stripslashes(trim($m4is_m676))){
 $m4is_c1796[$m4is_l9671]['score']=$m4is_c1796[$m4is_l9671]['score']+ 10;
 
}elseif($m4is_m676 > '' &&$m4is_i935['password']> '' &&$m4is_i935['password']!= 'PASSWORD_PLACEHOLDER'){
$m4is_i935['score']=$m4is_i935['score']+ 5;
 
}elseif($m4is_m676 > '' &&$m4is_m676 != $m4is_i935['password']&&$m4is_i935['password']== 'PASSWORD_PLACEHOLDER'){
$m4is_c1796[$m4is_l9671]['score']=$m4is_c1796[$m4is_l9671]['score']+ 3;
 
}elseif($m4is_m676 == '' &&$m4is_i935['password']== ''){
$m4is_c1796[$m4is_l9671]['score']=$m4is_c1796[$m4is_l9671]['score']+ 1;
 
}elseif($m4is_i935['id']== $m4is_k85621){
$m4is_c1796[$m4is_l9671]['score']+ 10;
 
}
} $m4is_h37054 =0;
 foreach ($m4is_c1796 as $m4is_l9671 =>$m4is_i935){
if($m4is_i935['score']> $m4is_h37054){
$m4is_z41902 =$m4is_l9671;
 
}
}unset($m4is_h37054);
 
}if($m4is_z41902 !== FALSE){
if($m4is_m676 == '' ||($m4is_m676 > '' &&$m4is_c1796[$m4is_z41902]['password']== $m4is_m676)){
 $m4is_h21895 =(int) $m4is_c1796[$m4is_z41902]['id'];
 
}elseif($m4is_m676 > '' &&$m4is_m676 <> 'PASSWORD_PLACEHOLDER' &&$m4is_c1796[$m4is_z41902]['password']== 'PASSWORD_PLACEHOLDER'){
 if(is_a($m4is_l17096, 'WP_User')){
if(($m4is_l17096->ID > 0)&&wp_check_password(strval($m4is_m676 ), strval($m4is_l17096->data->user_pass ), (int) $m4is_l17096->ID)){
$m4is_h21895 =(int) $m4is_c1796[$m4is_z41902]['id'];
 $m4is_o30 =[$this->m4is_j498('settings', 'password_field')=>$m4is_m676 ];
 m4is_p40::m4is_x6560($m4is_h21895, $m4is_o30, true);
  
}
}
}else{

}
}if($m4is_h21895 > 0){
$m4is_i935 =$m4is_c1796[$m4is_z41902];
  
}
}
}if(!$m4is_l17096){
$m4is_l17096 =get_user_by('email', $m4is_y193);
 
}if(is_a($m4is_l17096, 'WP_User')&&$m4is_h21895 > 0){
$this->m4is_d1796($m4is_h21895, $m4is_l17096->ID);
 
} if($m4is_y193 > '' &&$m4is_m676 > '' &&$m4is_h21895 > 0){
$m4is_a157[$m4is_y193]=$m4is_h21895;
 
}return $m4is_h21895;
 
} public 
function m4is_v1694(array $m4is_n0216, bool $m4is_d294 =false): bool {
global $wpdb;
 static $m4is_t57;
 static $m4is_r9613;
 static $m4is_z866;
 static $m4is_s86934;
 static $m4is_r6234;
 static $m4is_f089;
 static $m4is_c5710;
 static $m4is_p283;
 static $m4is_m7426;
 $this->m4is_f7064(true );
 $m4is_h21895 =(int) $m4is_n0216['Id']?? 0;
 $m4is_f4930 =$m4is_n0216['Email']?? '';
 $m4is_f087 =0;
  if(!$m4is_h21895 ){
return false;
 
} if(empty(trim($m4is_n0216['Email']))){
return false;
 
} if(isset($m4is_n0216['CompanyID'])&&$m4is_n0216['CompanyID']== $m4is_h21895 ){
return false;
 
}$m4is_l17096 =get_user_by('email', $m4is_f4930 )or $m4is_l17096 =get_user_by('login', $m4is_f4930 );
 if(is_a($m4is_l17096, 'WP_User' )){
$m4is_f087 =$m4is_l17096->ID;
 
}if(user_can($m4is_f087, 'manage_options' )){
return false;
 
}$m4is_r6234 ??= $this->m4is_j498('settings', 'password_field', '' );
 $m4is_f089 ??= $this->m4is_j498('settings', 'plaintext_db' );
 $m4is_s86934 ??= array_filter(explode(',', $this->m4is_j498('settings', 'ignore_contact_fields' )));
 $m4is_p283 ??= $this->m4is_j498('settings', 'sync_tag_details', 0 );
 $m4is_z866 ??= $this->m4is_j498('settings', 'disable_displayname_update' );
 $m4is_c5710 ??= $this->m4is_j498('settings', 'sync_meta_updates' );
 $m4is_r9613 ??= $this->m4is_i76('appname' );
 $m4is_t57 ??= m4is_c69807::m4is_f5248('Contact', true );
 $m4is_m7426 ??= m4is_p40::m4is_o1723();
 $m4is_u8354 =['Email', 'FirstName', 'Groups', 'LastName', ];
  foreach($m4is_n0216 as $m4is_l9671 =>$m4is_v586 ){
if(in_array($m4is_l9671, $m4is_s86934 )){
unset($m4is_n0216[$m4is_l9671]);
 
}
}$m4is_n0216 =$this->m4is_f9708($m4is_n0216 );
  foreach($m4is_n0216 as $m4is_l9671 =>$m4is_v586 ){
$m4is_n0216[$m4is_l9671]=($m4is_v586 == 'null' )? '' : trim($m4is_v586 );
 
} foreach($m4is_n0216 as $m4is_l9671 =>$m4is_v586 ){
if($m4is_v586 == '' &&!in_array($m4is_l9671, $m4is_u8354 )){
unset($m4is_n0216[$m4is_l9671]);
 
}
} if($m4is_d294 ){
m4is_p40::m4is_x6560($m4is_h21895, $m4is_n0216 );
  
} $m4is_n0216['!LastUpdated']=time();
  $m4is_v328 =m4is_p40::m4is_p67($m4is_h21895, false, true );
 foreach($m4is_n0216 as $m4is_l9671 =>$m4is_v586 ){
if(in_array($m4is_l9671, $m4is_s86934 )){
unset($m4is_n0216[$m4is_l9671]);
 
}
} foreach($m4is_u8354 as $m4is_x16290 ){
$m4is_n0216[$m4is_x16290]=$m4is_n0216[$m4is_x16290]?? '';
 
}$m4is_n0216['Groups']=implode(',', array_filter(array_unique(explode(',', $m4is_n0216['Groups']))));
 $m4is_q721 =$this->m4is_f247($m4is_v328, $m4is_n0216 );
 $m4is_h8537 =$this->m4is_p5672($m4is_v328, $m4is_n0216 );
 $m4is_b912 =$this->m4is_t8350($m4is_v328, $m4is_n0216 );
 $m4is_b912 =$this->m4is_g861($m4is_b912, $m4is_h8537 );
 $m4is_b912 =$this->m4is_g861($m4is_b912, array_keys($m4is_q721 ));
  if(!empty($m4is_h8537)){
$m4is_v2613 ="DELETE FROM {$m4is_m7426
} WHERE `appname` = %s AND `id` = %d AND `fieldname` IN ('" . implode("','", $m4is_h8537). "');";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_r9613, $m4is_h21895);
 $m4is_u6591 =$wpdb->query($m4is_v2613);
 
} if(!empty($m4is_q721 )){
$m4is_i760 =[];
 foreach($m4is_q721 as $m4is_o015 =>$m4is_k72 ){
$m4is_i760[]=$wpdb->prepare('(%d, %s, %s, %s)', $m4is_h21895, $m4is_r9613, $m4is_o015, $m4is_k72);
 
}if(!empty($m4is_i760)){
$m4is_v2613 ="INSERT INTO {$m4is_m7426
} (id, appname, fieldname, value) VALUES " . implode(',', $m4is_i760);
 $m4is_u6591 =$wpdb->query($m4is_v2613);
 
}
}if(!empty($m4is_b912 )){
foreach ($m4is_b912 as $m4is_l9671 =>$m4is_v586 ){
$m4is_v2613 ="UPDATE `{$m4is_m7426
}` SET `value` = %s WHERE `id` = %d AND `appname` = %s AND `fieldname` = %s ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_v586, $m4is_h21895, $m4is_r9613, $m4is_l9671 );
 $wpdb->query($m4is_v2613 );
 
}
}if($m4is_c5710){
$this->m4is_i27109($m4is_f087, $m4is_h21895, $m4is_n0216);
 
}if(!empty($m4is_p283 )){
m4is_k865::m4is_h894((int) $m4is_n0216['Id']);
 
}if($m4is_f087 ){
m4is_p40::m4is_m684($m4is_f087, $m4is_h21895 );
 $this->m4is_f605($m4is_h21895 );
 clean_user_cache($m4is_f087 );
 
}$this->m4is_i12($m4is_h21895 );
 $this->m4is_s64809($m4is_h21895 );
 do_action('memberium_save_contact', $m4is_n0216['Id'], $m4is_f087, $m4is_n0216);
 $this->m4is_f7064(false );
 return true;
 
} public 
function m4is_i27109(int $m4is_f087, int $m4is_h21895, $m4is_a89 ): void {
global $wpdb;
 static $m4is_e56910;
 $m4is_e56910 ??= (bool) $this->m4is_j498('settings', 'sync_meta_updates' );
 if(!$m4is_e56910 ){
return;
 
}if(empty($m4is_a89 )||empty($m4is_f087 )){
return;
 
}$m4is_c8657 ="memb\_%";
 $m4is_v2613 ="SELECT `meta_key`, `meta_value` FROM {$wpdb->usermeta
} WHERE `user_id` = {$m4is_f087
} AND `meta_key` LIKE '{$m4is_c8657
}' ";
 $m4is_i61069 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 $m4is_i61069 =$this->m4is_f0367($m4is_i61069, 'meta_key', 'meta_value' );
 foreach($m4is_a89 as $m4is_l9671 =>$m4is_v586 ){
if(substr($m4is_l9671, 0, 1 )!== '!' ){
$m4is_d57146 ="memb_{$m4is_l9671
}";
 if(!array_key_exists($m4is_d57146, $m4is_i61069 )||$m4is_v586 == '' ){
delete_user_meta($m4is_f087, $m4is_d57146 );
 
}elseif(array_key_exists($m4is_d57146, $m4is_i61069 )&&$m4is_i61069[$m4is_d57146]!== $m4is_v586 ){
update_user_meta($m4is_f087, $m4is_d57146, $m4is_v586 );
 
}elseif(!array_key_exists($m4is_d57146, $m4is_i61069 )&&!empty($m4is_v586)){
add_user_meta($m4is_f087, $m4is_d57146, $m4is_v586 );
 
}
}
}
} private 
function m4is_h073(int $m4is_h21895 ): int {
global $wpdb;
 $m4is_e80 =m4is_p40::m4is_o1723();
 $m4is_v2613 ='SELECT `value` FROM %i WHERE `id` = %d AND `fieldname` = "!LastUpdated";';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_h21895 );
 $m4is_z6320 =(int) $wpdb->get_var($m4is_v2613 );
 return $m4is_z6320;
 
} public 
function m4is_x4831($m4is_h21895 =0, $m4is_f475 =false ): int {
$m4is_e69637 =0;
 if(!$m4is_h21895 ){
return $m4is_e69637;
 
}$m4is_h21895 =(int) $m4is_h21895;
 $m4is_h3647 =m4is_c69807::m4is_f5248('Contact', true);
 $m4is_m615 =[];
 $m4is_e69637 =0;
 $m4is_o076 =time()- 3;
 $m4is_i3206 =$this->m4is_h073($m4is_h21895 );
 if($m4is_i3206 <= $m4is_o076 ){
$m4is_m615 =m4is_p40::m4is_t609($m4is_h21895, $m4is_h3647 );
 
}if(is_string($m4is_m615 )&&stripos($m4is_m615, 'RecordNotFound' )!== false){
m4is_p40::m4is_z62($m4is_h21895 );
 
}if(is_array($m4is_m615)&&count($m4is_m615)> 0){
$this->m4is_v1694($m4is_m615);
 $m4is_e69637 =count($m4is_m615);
 if(!empty($this->m4is_j498('settings', 'sync_tag_details'))){
m4is_k865::m4is_h894((int) $m4is_m615['Id']);
 
}$this->m4is_i12($m4is_h21895);
 
}$this->m4is_s64809($m4is_h21895 );
 return $m4is_e69637;
 
}public 
function m4is_n361($m4is_y66291 =[]){
$m4is_y642 =['contact_id' =>0, 'cascade' =>false, 'cache_ttl' =>0, ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642);
 $m4is_h21895 =$m4is_y66291['contact_id'];
 $m4is_f475 =$m4is_y66291['cascade'];
 $m4is_s82753 =$m4is_y66291['cache_ttl'];
 $m4is_e69637 =0;
 if($m4is_h21895 > 0){
global $wpdb;
 $m4is_c84621 =$this->m4is_j498();
 $m4is_x39508 =$this->get_i2sdk_options();
 $m4is_h21895 =(int) $m4is_h21895;
 $m4is_h3647 =m4is_c69807::m4is_f5248('Contact', true);
 $m4is_m615 =[];
 $m4is_e69637 =0;
 $m4is_v2613 ='SELECT `value` FROM `' . m4is_p40::m4is_o1723(). '` WHERE `id` = %d AND `fieldname` = "!LastUpdated";';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_h21895);
 $m4is_i3206 =$wpdb->get_var($m4is_v2613);
 $m4is_o076 =time();
 if($m4is_i3206 < ($m4is_o076 - $m4is_s82753)){
$m4is_m615 =m4is_p40::m4is_t609($m4is_h21895, $m4is_h3647);
 if(is_string($m4is_m615)&&stripos($m4is_m615, 'RecordNotFound')!== false){
m4is_p40::m4is_z62($m4is_h21895);
 
}if(is_array($m4is_m615)&&count($m4is_m615)> 0){
$this->m4is_v1694($m4is_m615);
 $m4is_e69637 =count($m4is_m615);
 if(!empty($this->m4is_j498('settings', 'sync_tag_details'))){
m4is_k865::m4is_h894((int) $m4is_m615['Id']);
 
}
}
}$this->m4is_s64809($m4is_h21895 );
 $this->m4is_i12($m4is_h21895);
 
}return $m4is_e69637;
 
} private 
function m4is_d043(int $m4is_h21895, $m4is_f4930){
global $wpdb;
 if(empty($m4is_f4930)){
return false;
 
}if(empty($m4is_h21895)){
return false;
 
}$m4is_r637 =$this->m4is_j498('settings', 'username_field');
 $m4is_r9613 =$this->m4is_i76('appname');
 $m4is_v2613 ="SELECT count(`id`) FROM `" . m4is_p40::m4is_o1723(). "` WHERE `id` = %d AND `fieldname` = %s AND appname = %s AND VALUE = %s ;";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_h21895, $m4is_r637, $m4is_r9613, $m4is_f4930);
 $m4is_t265 =$wpdb->get_var($m4is_v2613);
 return $m4is_t265;
 
}public 
function m4is_d49860(int $m4is_w66, $m4is_k824 =[]): bool {
if(current_user_can('manage_options' )){
return true;
 
} if(!m4is_s52::m4is_w74()){
return false;
 
}if(empty($m4is_k824 )){
$m4is_k824 =m4is_q82::m4is_d59($this->m4is_x66());
 
}$m4is_f406 =empty($m4is_k824['keap']['contact']['groups'])? []: array_filter(explode(',', $m4is_k824['keap']['contact']['groups']));
 $m4is_x69523 =m4is_k865::m4is_y19240($m4is_f406);
 $m4is_l95648 =in_array($m4is_w66, $m4is_x69523 );
 return $m4is_l95648;
 
}public 
function m4is_l5918($m4is_q502, $m4is_p795 ){
if(empty($m4is_q502 )||empty($m4is_p795 )){
return false;
 
}$m4is_p601 =false;
 $m4is_q502 =is_array($m4is_q502 )? $m4is_q502 : array_filter(explode(',', $m4is_q502 ));
 $m4is_q502 =m4is_k865::m4is_c743($m4is_q502 );
 $m4is_q502 =array_map('trim', $m4is_q502 );
 $m4is_b89 =array_filter($m4is_q502, function($m4is_v586 ){
if(substr($m4is_v586, 0, 1 )== '-' ){
return true;
 
}
});
 $m4is_q502 =array_filter($m4is_q502, function($m4is_v586 ){
if($m4is_v586 > 0 ){
return true;
 
}
});
 $m4is_b89 =array_map('abs', $m4is_b89 );
 $m4is_p795 =is_array($m4is_p795 )? $m4is_p795 : array_filter(explode(',', $m4is_p795 ));
  if(!array_intersect($m4is_p795, $m4is_b89 )){
if(array_intersect($m4is_p795, $m4is_q502 )){
return true;
 
}
}return false;
 
} public 
function m4is_m2480($m4is_n70, $m4is_k824 =[]){
$m4is_f087 =isset($m4is_k824['memb_user']['user_id'])? $m4is_k824['memb_user']['user_id']: $this->m4is_x66();
 if(user_can($m4is_f087, 'manage_options' )){
return true;
 
} if(!m4is_s52::m4is_w74()){
return false;
 
}$m4is_p64627 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tag_names', '' );
 $m4is_f406 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tags', '' );
 if(is_array($m4is_n70 )){
$m4is_n70 =trim(implode(',', $m4is_n70), ',' );
 
} $m4is_t265 =false;
 $m4is_n70 =preg_replace(['/, */', '/ *,/'], ',', $m4is_n70);
 $m4is_n70 =preg_replace(['/^,/', '/,$/'], '', $m4is_n70);
 $m4is_n70 =trim($m4is_n70);
 $m4is_s15437 =array_filter(explode(',', strtolower(trim($m4is_n70))));
 $m4is_p354 =implode(',', $m4is_s15437);
 $m4is_f406 =array_filter(explode(',', $m4is_f406 ));
 $m4is_p64627 =array_filter(explode(',', strtolower($m4is_p64627 )));
 $m4is_h8391 =[];
 $m4is_w207 =[];
  foreach($m4is_s15437 as $m4is_p786){
if(substr($m4is_p786, 0, 1)<> '-'){
$m4is_h8391[]=$m4is_p786;
 
}else{
$m4is_w207[]=substr($m4is_p786, 1);
 
}
} $m4is_k152 =false;
 $m4is_b426 =0;
 if(count($m4is_h8391)){
$m4is_b426 =count(array_intersect($m4is_h8391, $m4is_f406))+ count(array_intersect($m4is_h8391, $m4is_p64627));
 $m4is_k152 =(boolean) $m4is_b426;
 
}else{
$m4is_b426 =0;
 $m4is_k152 =true;
 
} $m4is_t319 =false;
 $m4is_q0913 =0;
 if(count($m4is_w207)){
$m4is_q0913 =count(array_intersect($m4is_w207, $m4is_f406))+ count(array_intersect($m4is_w207, $m4is_p64627));
 $m4is_t319 =!(boolean) $m4is_q0913;
 
}else{
$m4is_q0913 =0;
 $m4is_t319 =true;
 
} $m4is_t265 =false;
 if(count($m4is_h8391)){
$m4is_t265 =$m4is_t265 ||$m4is_k152;
 
}if(count($m4is_w207)){
$m4is_t265 =$m4is_t265 ||$m4is_t319;
 
}return $m4is_t265;
 
} public 
function m4is_x13466($m4is_l9321, int $m4is_h21895 =0 ){
static $m4is_y9160;
 $m4is_y9160 ??= !m4is_s52::m4is_w74();
  if(empty($m4is_l9321 )){
return true;
 
}if(!$m4is_h21895){
$m4is_h21895 =$this->m4is_z56();
 
}$m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 if(user_can($m4is_f087, 'manage_options' )){
return true;
 
}if(!$m4is_f087 ){
return false;
 
}if(is_string($m4is_l9321 )){
$m4is_l9321 =explode(',', $m4is_l9321);
 
}if(!is_array($m4is_l9321 )){
return false;
 
} if($m4is_y9160 ){
return false;
 
}$m4is_f406 =array_filter(explode(',', m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tags', '' )));
 $m4is_p64627 =array_filter(explode(',', strtolower(m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tag_names', '' ))));
 foreach ($m4is_l9321 as $m4is_p786){
if(substr($m4is_p786, 0, 1)== '-'){
$m4is_p786 =strtolower(ltrim($m4is_p786, '-'));
 if(in_array($m4is_p786, $m4is_f406 )||in_array($m4is_p786, $m4is_p64627 )){
return false;
 
}
}else{
$m4is_p786 =strtolower($m4is_p786 );
 if(!in_array($m4is_p786, $m4is_f406)&&!in_array($m4is_p786, $m4is_p64627)){
return false;
 
}
}
}return true;
 
}   
function m4is_g72069($m4is_h21895){
global $wpdb;
 static $cached_affiliate_id =[];
 if(isset($cached_affiliate_id[$m4is_h21895])){
return $cached_affiliate_id[$m4is_h21895];
 
}if(empty($m4is_h21895)){
$m4is_h21895 =(int) $this->m4is_z56();
 
}if($m4is_h21895 < 1){
return 0;
 
} $m4is_h21895 =(int) $m4is_h21895;
 $m4is_y12754 =[];
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_e80 ='Referral';
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false);
 $m4is_e69637 =0;
;
 $m4is_v76912 =['ContactId' =>$m4is_h21895, ];
 $m4is_k236 =$this->m4is_j498('settings', 'referral_partner_order')== 1;
 do {
$m4is_m615 =m4is_c69807::m4is_i84($m4is_e80, (int) $m4is_c92430, (int) $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', true);
 if(is_array($m4is_m615)){
foreach ($m4is_m615 as $m4is_g91703){
if(!isset($m4is_g91703['DateExpires'])||$m4is_g91703['DateExpires']>= date('Ymd\T00:00:00' )){
$m4is_y12754[$m4is_g91703['Id']]=$m4is_g91703;
 
}
}$m4is_d3012++;
 $m4is_e69637 =$m4is_e69637 + count($m4is_m615);
 
}
}while (count($m4is_m615)== $m4is_c92430);
 if($m4is_k236){
$m4is_j7203 =array_shift($m4is_y12754);
 
}else{
$m4is_j7203 =array_pop($m4is_y12754);
 
}unset($m4is_y12754, $m4is_m615);
 $cached_affiliate_id[$m4is_h21895]=isset($m4is_j7203['AffiliateId'])? $m4is_j7203['AffiliateId']: 0;
 return $cached_affiliate_id[$m4is_h21895];
 
}
function m4is_x17402(int $m4is_h21895, bool $m4is_f465 =false ): bool {
global $wpdb;
 if(empty($m4is_h21895 )){
return false;
 
}if(wp_doing_ajax()){
return false;
 
}$m4is_m971 =$this->m4is_j498('settings', 'max_affiliate_age', 0 );
 $m4is_r9613 =$this->m4is_i76('appname' );
 $m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 $m4is_w92738 ='memberium/' . $m4is_r9613 . '/affiliate/updated';
 $m4is_q4296 =time();
 $m4is_i3206 =0;
  if($m4is_m971 &&$m4is_f465 == false ){
$m4is_k25038 =$m4is_q4296 - (int) get_user_meta($m4is_f087, $m4is_w92738, true )- 30;
 if($m4is_k25038 < $m4is_m971 ){
return true;
 
}
}$m4is_a566 =m4is_z13097::m4is_h026($m4is_h21895 );
 if(is_array ($m4is_a566 )){
m4is_z13097::m4is_r16($m4is_a566 );
 update_user_meta($m4is_f087, $m4is_w92738, $m4is_q4296);
 return true;
 
}return false;
 
}
function m4is_d126($m4is_h21895 =0){
global $wpdb;
 static $m4is_q4032;
 if(!empty($m4is_h21895 )&&isset($m4is_q4032[$m4is_h21895])){
return $m4is_q4032[$m4is_h21895];
 
}$m4is_c84621 =$this->m4is_j498();
 $m4is_x39508 =$this->get_i2sdk_options();
 $m4is_u398 =$this->m4is_j498('settings', 'plaintext_db', false );
 $m4is_r6234 =$this->m4is_j498('settings', 'password_field', '' );
 $m4is_r9613 =$this->m4is_i76('appname' );
 $m4is_h21895 =(int) $m4is_h21895;
 $m4is_c92430 =1000;
 $m4is_e80 ='Affiliate';
 $m4is_d3012 =0;
 $m4is_z051 ='ContactId';
 $m4is_e207 =($m4is_h21895 > 0 )? (int) $m4is_h21895 : '%';
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, true );
 $m4is_m7426 =m4is_z13097::m4is_o71();
 $m4is_e69637 =0;
 $m4is_p65 =[];
 if($m4is_h21895 == 0 ){
$m4is_v76912 =['ContactId' =>'%' ];
 
}else{
$m4is_v76912 =['ContactId' =>$m4is_h21895 ];
 
}do {
$m4is_m615 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', true );
 if(is_array($m4is_m615 )&&!empty($m4is_m615 )){
$m4is_u450 =[];
 $m4is_i760 =[];
 foreach ($m4is_m615 as $m4is_g91703){
$m4is_g91703['!LastUpdated']=time();
 $m4is_d07693 =(int) $m4is_g91703['Id'];
 $m4is_p65[]=$m4is_d07693;
 $m4is_a89[]=[];
 foreach ($m4is_g91703 as $m4is_l9671 =>$m4is_v586 ){
if($m4is_u398 ){
$m4is_v586 =remove_accents($m4is_v586 );
  
}$m4is_v586 =$m4is_v586 == 'null' ? '' : $m4is_v586;
 $m4is_i760[]=$wpdb->prepare('(%d, %s, %s, %s)', $m4is_d07693, $m4is_r9613, $m4is_l9671, $m4is_v586 );
 $m4is_a89[]=$m4is_l9671;
 $m4is_v586 =($m4is_l9671 != $m4is_r6234 )? wp_strip_all_tags($m4is_v586 ): $m4is_v586;
 
}
}$m4is_v2613 ="INSERT INTO {$m4is_m7426
} (id, appname, fieldname, value) VALUES " . implode(',', $m4is_i760). " ON DUPLICATE KEY UPDATE id=VALUES(id), appname=VALUES(appname), fieldname=VALUES(fieldname), value=VALUES(value);";
 $wpdb->query($m4is_v2613);
 $m4is_d3012++;
 $m4is_e69637 =$m4is_e69637 + count($m4is_m615);
 
}
}while (is_array($m4is_m615)&&count($m4is_m615)== $m4is_c92430);
 if(!empty($m4is_p65)){
$m4is_v2613 ="DELETE FROM {$m4is_m7426
} WHERE `appname` = '{$m4is_r9613
}' AND `id` NOT IN (" . implode(',', $m4is_p65). ");";
 $wpdb->query($m4is_v2613);
 
}if($m4is_h21895 == 0){
  set_transient('memberium_affiliates_updated', time());
 
}$m4is_q4032[$m4is_h21895]=$m4is_e69637;
 return $m4is_e69637;
 
}
function m4is_s8304(){
global $wpdb;
 
}
function m4is_c0396($m4is_t6601, $m4is_y09236 ='', $m4is_y81309 ='' ){
global $wpdb;
 $m4is_t6601 =(int) $m4is_t6601;
 $m4is_v2613 =$wpdb->prepare('SELECT count(id) FROM %i WHERE affiliateid = %d ', m4is_v87365::m4is_p63285(), $m4is_t6601 );
 $m4is_f3097 =(int) strtotime($m4is_y09236);
 $m4is_n42973 =(int) strtotime($m4is_y81309);
 if($m4is_y09236){
$m4is_v2613 .= $wpdb->prepare('AND datecreated >= %s ', date('Y-m-d h:i:s', $m4is_f3097 ));
 
}if($m4is_y81309){
$m4is_v2613 .= $wpdb->prepare('AND datecreated <= %s ', date('Y-m-d h:i:s', $m4is_n42973 ));
 
}
}   
function m4is_r86521($m4is_y66291 ){
$m4is_y642 =['format' =>'serial', 'invoice_id' =>0, 'contact_id' =>$this->m4is_z56(), ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642 );
 $m4is_l97604 =(int) $m4is_y66291['invoice_id'];
 $m4is_h21895 =(int) $m4is_y66291['contact_id'];
 $m4is_f16985 =strtolower(trim($m4is_y66291['format']));
 $m4is_r9613 =$this->m4is_i76('appname' );
 $m4is_z984 ='Memberium:' . $m4is_r9613 . '::Receipt:' . $m4is_l97604 . '::Contact:' . $m4is_h21895;
  $m4is_o17460 =get_transient($m4is_z984 );
 if($m4is_o17460 === false){
if($m4is_l97604){
 $m4is_e80 ='Invoice';
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false );
 $m4is_t06897 =(array)m4is_c69807::m4is_b6614($m4is_e80, (int) $m4is_l97604, $m4is_h3647 );
 if(!$this->m4is_v461()){
if(isset($m4is_t06897['ContactId'])&&$m4is_t06897['ContactId']== $m4is_h21895){
$m4is_l97604 =empty($m4is_t06897['Id'])? 0 : (int) $m4is_t06897['Id'];
 $m4is_o064 =empty($m4is_t06897['JobId'])? 0 : (int) $m4is_t06897['JobId'];
 
}else{
$m4is_l97604 =0;
 
}
}if($m4is_l97604 ){
$m4is_o17460['invoice']=array_change_key_case($m4is_t06897, CASE_LOWER );
 $m4is_o17460['invoice']['totaldue']=$m4is_o17460['invoice']['totaldue'];
 $m4is_o17460['invoice']['invoicetotal']=$m4is_o17460['invoice']['invoicetotal'];
 $m4is_o17460['invoice']['totalpaid']=$m4is_o17460['invoice']['totalpaid'];
  $m4is_o17460['contact']=m4is_p40::m4is_p67($m4is_h21895, true);
  if($m4is_o064){
$m4is_e80 ='Job';
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false);
 $m4is_b9687 =$this->m4is_r1476()->dsLoad($m4is_e80, (int) $m4is_o064, $m4is_h3647);
 if(is_array($m4is_b9687)){
$m4is_o17460['job']=array_change_key_case($m4is_b9687, CASE_LOWER);
 
} $m4is_e80 ='OrderItem';
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false);
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_v76912 =['OrderId' =>$m4is_o064];
 $m4is_q20 ='Id';
 $m4is_k236 =true;
 $m4is_v0681 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, $m4is_q20, $m4is_k236);
 if(is_array($m4is_v0681)){
foreach($m4is_v0681 as $m4is_l9671 =>$m4is_v586){
$m4is_v0681[$m4is_l9671]=array_change_key_case($m4is_v586, CASE_LOWER);
 $m4is_v0681[$m4is_l9671]['ppu']=$m4is_v0681[$m4is_l9671]['ppu'];
 $m4is_v0681[$m4is_l9671]['cpu']=$m4is_v0681[$m4is_l9671]['cpu'];
 
}$m4is_o17460['orderitems']=$m4is_v0681;
 
}
} $m4is_e80 ='InvoiceItem';
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false);
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_v76912 =['InvoiceId' =>$m4is_l97604];
 $m4is_q20 ='Id';
 $m4is_k236 =true;
 $m4is_v0681 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, $m4is_q20, $m4is_k236);
 if(is_array($m4is_v0681)){
foreach($m4is_v0681 as $m4is_l9671 =>$m4is_v586){
$m4is_v0681[$m4is_l9671]=array_change_key_case($m4is_v586, CASE_LOWER);
 $m4is_v0681[$m4is_l9671]['ppu']=$m4is_v0681[$m4is_l9671]['ppu'];
 $m4is_v0681[$m4is_l9671]['cpu']=$m4is_v0681[$m4is_l9671]['cpu'];
 $m4is_v0681[$m4is_l9671]['invoiceamt']=$m4is_v0681[$m4is_l9671]['invoiceamt'];
 
}$m4is_o17460['invoiceitems']=$m4is_v0681;
 
} $m4is_e80 ='Payment';
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false);
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_v76912 =['InvoiceId' =>$m4is_l97604];
 $m4is_q20 ='Id';
 $m4is_k236 =true;
 $m4is_l5287 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, $m4is_q20, $m4is_k236);
 $m4is_o9368 =0;
 if(is_array($m4is_l5287)){
foreach($m4is_l5287 as $m4is_l9671 =>$m4is_v586){
$m4is_l5287[$m4is_l9671]=array_change_key_case($m4is_v586, CASE_LOWER);
 $m4is_l5287[$m4is_l9671]['payamt']=$m4is_l5287[$m4is_l9671]['payamt'];
 
}$m4is_o17460['payments']=$m4is_l5287;
 
}if($m4is_t06897['TotalPaid']< $m4is_t06897['InvoiceTotal']){
 $m4is_e80 ='PayPlan';
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false);
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_v76912 =['InvoiceId' =>$m4is_l97604];
 $m4is_q20 ='Id';
 $m4is_k236 =true;
 $m4is_f78952 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, $m4is_q20, $m4is_k236);
 if(is_array($m4is_f78952)){
foreach($m4is_f78952 as $m4is_l9671 =>$m4is_v586){
$m4is_f78952[$m4is_l9671]=array_change_key_case($m4is_v586, CASE_LOWER);
 $m4is_f78952[$m4is_l9671]['amtdue']=$m4is_f78952[$m4is_l9671]['amtdue'];
 $m4is_f78952[$m4is_l9671]['firstpayamt']=$m4is_f78952[$m4is_l9671]['firstpayamt'];
 
}$m4is_o17460['paymentplan']=$m4is_f78952[0];
 
} $m4is_e80 ='PayPlanItem';
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false);
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_v76912 =['PayPlanId' =>$m4is_f78952[0]['id']];
 $m4is_q20 ='Id';
 $m4is_k236 =true;
 $m4is_f710 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, $m4is_q20, $m4is_k236);
 if(is_array($m4is_f710)){
foreach($m4is_f710 as $m4is_l9671 =>$m4is_v586){
$m4is_f710[$m4is_l9671]=array_change_key_case($m4is_v586, CASE_LOWER);
 
}$m4is_o17460['paymentplanitems']=$m4is_f710;
 
}
}
}
}
}set_transient($m4is_z984, $m4is_o17460, HOUR_IN_SECONDS * 3 );
 if($m4is_f16985 == 'json' ){
$m4is_o17460 =json_encode($m4is_o17460 );
 
}return $m4is_o17460;
 
} 
function m4is_v31259(int $m4is_h21895, $m4is_z950 =true, $m4is_j32 =true, $m4is_x980 =false ){
$m4is_h21895 =(int) $m4is_h21895;
 if($m4is_h21895 < 1 ){
return [];
 
}$m4is_r9613 =$this->m4is_i76('appname');
 $m4is_z984 ='Memberium::' . $m4is_r9613 . '::Invoices::' . $m4is_h21895;
 $m4is_d01 =[];
 if(!$m4is_x980){
$m4is_m615 =get_transient($m4is_z984);
 
}if(empty($m4is_d01)){
$m4is_k236 =true;
 $m4is_q20 ='DateCreated';
 $m4is_v76912 =['ContactId' =>(int) $m4is_h21895 ];
 $m4is_h3647 =['Id', 'CreditStatus', 'DateCreated', 'Description', 'InvoiceTotal', 'InvoiceType', 'JobId', 'PayPlanStatus', 'PayStatus', 'ProductSold', 'RefundStatus', 'TotalDue', 'TotalPaid', ];
 $m4is_m615 =m4is_c69807::m4is_i84('Invoice', 1000, 0, $m4is_v76912, $m4is_h3647, $m4is_q20, $m4is_k236);
 set_transient($m4is_z984, $m4is_m615, 1800);
 
}foreach($m4is_m615 as $m4is_g91703){
$m4is_n56902 =false;
 $m4is_d07693 =$m4is_g91703['Id'];
 $m4is_g0687 =max($m4is_g91703['TotalDue'], $m4is_g91703['InvoiceTotal']);
 $m4is_n56902 =$m4is_g91703['TotalPaid'];
  $m4is_g91703['!payment_due']=$m4is_g0687 - $m4is_n56902;
 $m4is_n56902 =$m4is_g91703['!payment_due']== 0;
 if($m4is_z950 &&empty($m4is_g91703['!payment_due'])){
$m4is_d01[$m4is_d07693]=$m4is_g91703;
  
}if($m4is_j32 &&(!empty($m4is_g91703['!payment_due']))){
$m4is_d01[$m4is_d07693]=$m4is_g91703;
 
}
}return $m4is_d01;
 
}
function m4is_i916(){
$m4is_r9613 =$this->m4is_i76('appname');
 $m4is_z984 ='Memberium::' . $m4is_r9613 . '::SubscriptionPlans';
 $m4is_s5867 =get_transient($m4is_z984);
 if(empty($m4is_s5867)){
$m4is_s5867 =m4is_v87365::m4is_x096();
 
}return $m4is_s5867;
 
}
function m4is_f66295($m4is_o064 =0){
if($m4is_o064 == 0){
return FALSE;
 
}$m4is_r9613 =$this->m4is_i76('appname');
 $m4is_o064 =(int) $m4is_o064;
 $m4is_z984 ="Memberium::{$m4is_r9613
}::OrderItems::{$m4is_o064
}";
 $m4is_r26763 =get_transient($m4is_z984);
 if(!empty($m4is_r26763)){
return $m4is_r26763;
 
}unset($m4is_r26763);
 $m4is_e80 ='OrderItem';
 $m4is_v76912 =['OrderId' =>$m4is_o064];
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80);
 $m4is_r26763 =m4is_c69807::m4is_o986($m4is_e80, 1000, 0, $m4is_v76912, $m4is_h3647);
 if(!empty($m4is_r26763)){
if(is_array($m4is_r26763)){
set_transient($m4is_z984, $m4is_r26763, 1800);
 return $m4is_r26763;
 
}
}return FALSE;
 
}
function m4is_c686($m4is_h21895 =0, $m4is_x980 =false ){
$m4is_h21895 =$m4is_h21895 ? (int) $m4is_h21895 : $this->m4is_z56();
 if(empty($m4is_h21895 )){
return [];
 
}$m4is_z984 ='memberium_subscriptions::' . $m4is_h21895;
 $m4is_d74 =get_transient($m4is_z984 );
 if($m4is_x980 ||!is_array($m4is_d74 )){
$m4is_v76912 =['ContactId' =>$m4is_h21895];
 $m4is_q20 ='Id';
 $m4is_k236 =false;
 $m4is_h3647 =m4is_c69807::m4is_f5248('RecurringOrder' );
 $m4is_m615 =m4is_c69807::m4is_i84('RecurringOrder', 999, 0, $m4is_v76912, $m4is_h3647, $m4is_q20, $m4is_k236 );
 $m4is_d74 =[];
 if(is_array($m4is_m615 )){
foreach ($m4is_m615 as $m4is_g91703 ){
$m4is_d74[$m4is_g91703['Id']]=$m4is_g91703;
 
}
}set_transient($m4is_z984, $m4is_d74, HOUR_IN_SECONDS / 12 );
 unset($m4is_m615, $m4is_g91703, $m4is_h3647, $m4is_v76912);
 
}if(is_array($m4is_d74)){
foreach($m4is_d74 as $m4is_o015 =>$m4is_k72){
if(!empty($m4is_k72['EndDate'])&&$m4is_k72['Status']== 'Active' &&$m4is_k72['EndDate']< date('Ymd' )){
$m4is_k72['Status']='Inactive';
 
}if($m4is_k72['Status']<> 'Active'){
$m4is_k72['NextBillDate']='';
 
}$m4is_d74[$m4is_o015]=$m4is_k72;
 
}
}return $m4is_d74;
 
}
function m4is_h37260($m4is_h21895 =0){
global $wpdb;
 $m4is_m7426 =m4is_v87365::m4is_p63285();
 $m4is_r9613 =$this->m4is_i76('appname');
 $m4is_v2613 ="SELECT MAX(`jobid`) FROM `{$m4is_m7426
}` WHERE `appname` = '{$m4is_r9613
}' AND `contactid` = {$m4is_h21895
};
";
 return (int) $wpdb->get_var($m4is_v2613);
 
}
function m4is_c835(){
global $wpdb;
 $m4is_m7426 =m4is_v87365::m4is_p63285();
 $m4is_r9613 =$this->m4is_i76('appname');
 $m4is_v2613 ="SELECT MAX(`jobid`) FROM `{$m4is_m7426
}` WHERE `appname` = '{$m4is_r9613
}';";
 return (int) $wpdb->get_var($m4is_v2613);
 
} 
function m4is_c38461($m4is_u897 ){
$m4is_h9635 =strtolower(substr($m4is_u897['bypass_commissions'], 0, 1 ));
 $m4is_h9635 =($m4is_h9635 == 'y' ||$m4is_h9635 == 1)? TRUE : FALSE;
 $m4is_h9635 =(bool) $m4is_u897['bypass_commissions'];
 $m4is_m05321 =strtolower(substr($m4is_u897['delete_failed'], 0, 1));
 $m4is_m05321 =($m4is_m05321 == 'y' ||$m4is_m05321 == 1)? TRUE : FALSE;
 $m4is_a87395 =(int) $m4is_u897['product_type'];
 $m4is_a87395 =($m4is_u897['product_type']> 0 &&$m4is_u897['product_type']< 15)? $m4is_u897['product_type']: 4;
 $m4is_p25766 =(bool) $m4is_u897['autorun'];
 $m4is_u807 =(int) $m4is_u897['creditcard_id'];
 $m4is_m05321 =(bool) $m4is_u897['delete_failed'];
 $m4is_a87395 =(int) $m4is_u897['item_type'];
 $m4is_v724 =(int) $m4is_u897['lead_affiliate_id'];
 $m4is_e806 =(int) $m4is_u897['merchant_id'];
 $m4is_o6480 =(int) $m4is_u897['product_id'];
 $m4is_y83102 =trim($m4is_u897['product_description']?? '' );
 $m4is_e74 =trim($m4is_u897['product_name']?? '' );
 $m4is_l70951 =(float) $m4is_u897['product_price'];
 $m4is_o3760 =(int) $m4is_u897['quantity'];
 $m4is_k30692 =(int) $m4is_u897['sales_affiliate_id'];
 $m4is_b483 =(int) $m4is_u897['has_payment_plan'];
 $m4is_n81 =$m4is_u897['order_date'];
 $m4is_s285 =(bool) $m4is_u897['taxable'];
 $m4is_i9760 =trim($m4is_u897['success_action']);
 $m4is_q21460 =trim($m4is_u897['success_goals']);
 $m4is_t579 =trim($m4is_u897['success_tags']);
 $m4is_a38796 =trim($m4is_u897['fail_action']);
 $m4is_t1560 =trim($m4is_u897['fail_goals']);
 $m4is_t6749 =trim($m4is_u897['fail_tags']);
 $m4is_h21895 =$this->m4is_z56();
 if($m4is_b483 ){
$m4is_f823 =(bool) $m4is_u897['payplan_autocharge'];
 $m4is_g48 =(int) $m4is_u897['payplan_max_retries'];
 $m4is_x63 =(int) $m4is_u897['payplan_retry_days'];
 $m4is_n68974 =(float) $m4is_u897['payplan_initial_amount'];
 $m4is_d196 =(int) $m4is_u897['payplan_payment_count'];
 $m4is_r936 =(int) $m4is_u897['payplan_days_between'];
 
} $m4is_n81 =date('Y-m-d\TH:i:s' );
 $m4is_l97604 =$this->m4is_r1476()->blankOrder($m4is_h21895, $m4is_y83102, $m4is_n81, $m4is_v724, $m4is_k30692 );
 $this->m4is_r1476()->addOrderItem($m4is_l97604, $m4is_o6480, $m4is_a87395, $m4is_l70951, $m4is_o3760, $m4is_e74, $m4is_y83102 );
 if($m4is_b483 ){
$m4is_h1563 =$this->m4is_z59682->infuDate(date('m-d-Y' ));
 $m4is_e04713 =$this->m4is_z59682->infuDate(date('m-d-Y' ));
 $m4is_u6591 =$this->m4is_z59682->payPlan($m4is_l97604, $m4is_f823, $m4is_u807, $m4is_e806, $m4is_x63, $m4is_g48, $m4is_n68974, $m4is_n81, $m4is_e04713, $m4is_d196, $m4is_r936 );
 
}if($m4is_s285 ){
$ret =$this->m4is_r1476()->recalculateTax($m4is_l97604);
 
}$m4is_u6591 =$this->m4is_r1476()->chargeInvoice($m4is_l97604, 'Memberium API Order', $m4is_u807, $m4is_e806, false );
 if(in_array(strtolower($m4is_u6591['Code']), ['approved', 'skipped'])){
 $m4is_u6591 =$m4is_l97604;
 if($m4is_i9760 > ''){
$this->m4is_u71903($m4is_i9760, $m4is_h21895 );
 
}if($m4is_q21460 > '' ){
$this->m4is_t64038($m4is_q21460, $m4is_h21895 );
 
}if($m4is_t579 > '' ){
$this->m4is_k98($m4is_t579, $m4is_h21895);
 
}
}else{
if($m4is_m05321 &&$m4is_l97604 > 0 ){
$this->m4is_r1476()->deleteInvoice($m4is_l97604 );
 
} if($m4is_a38796 > ''){
$this->m4is_u71903($m4is_a38796, $m4is_h21895 );
 
}if($m4is_t1560 > ''){
$this->m4is_t64038($m4is_t1560, $m4is_h21895 );
 
}if($m4is_t6749 > ''){
$this->m4is_k98($m4is_t6749, $m4is_h21895 );
 
}$m4is_u6591 =false;
 
}$this->m4is_x4831($m4is_h21895);
 
}    
function m4is_s85($m4is_m20, $m4is_f087 =false, $m4is_i8169 =false ){
if(empty($m4is_m20 )){
return;
 
}if(!m4is_s52::m4is_f27()){
return;
 
}if(!is_array($m4is_m20)){
$m4is_m20 =array_filter(explode(',', trim($m4is_m20 )));
 
}$m4is_m20 =array_filter(array_map('strtolower', $m4is_m20));
 if(empty($m4is_m20 )){
return;
 
}if(!$m4is_f087){
$m4is_f087 =$this->m4is_x66();
 
}if($m4is_f087 < 1){
return;
 
}$m4is_g1968 =get_user_meta($m4is_f087, 'memberium_tokens', true);
 if(!$m4is_g1968){
$m4is_g1968 =[];
 
}$m4is_g1968 =array_filter(array_map('strtolower', $m4is_g1968));
 foreach ($m4is_m20 as $m4is_v8646){
if(substr($m4is_v8646, 0, 1)=== '-'){
$m4is_v8646 =substr($m4is_v8646, 1);
 $m4is_l9671 =array_search($m4is_v8646, $m4is_g1968);
 unset($m4is_g1968[$m4is_l9671]);
 do_action('memb_remove_token', $m4is_f087, $m4is_v8646);
 
}else{
$m4is_g1968[]=$m4is_v8646;
 do_action('memb_add_token', $m4is_f087, $m4is_v8646);
 
}
}$m4is_g1968 =array_unique($m4is_g1968);
 update_user_meta($m4is_f087, 'memberium_tokens', $m4is_g1968 );
 
}
function m4is_d4936($m4is_m20, $m4is_f087 =false){
if(current_user_can('manage_options')){
return true;
 
} if(!m4is_s52::m4is_f27()){
return false;
 
}if(!$m4is_f087){
$m4is_f087 =$this->m4is_x66();
 
}if($m4is_f087 < 1){
return;
 
}$m4is_m20 =array_filter(array_map('strtolower', explode(',', trim($m4is_m20))));
 $m4is_g1968 =get_user_meta($m4is_f087, 'memberium_tokens', true);
 if(!$m4is_g1968){
$m4is_g1968 =[];
 
}$m4is_g1968 =array_filter(array_map('strtolower', $m4is_g1968));
 if(count(array_intersect($m4is_m20, $m4is_g1968))){
return true;
 
}return false;
 
}    
function m4is_b96654($m4is_n246, $m4is_c48062){
$m4is_m96240 =$this->m4is_j498('memberships');
 $m4is_m96240 =is_array($m4is_m96240)? $m4is_m96240 : [];
 return $m4is_m96240;
 
} 
function m4is_q96($m4is_h21895, $m4is_l9321 ){
global $wpdb;
 $m4is_h21895 =(int) $m4is_h21895;
 if(is_int($m4is_l9321)||is_string($m4is_l9321)){
$m4is_l9321 =explode(',', $m4is_l9321);
 
} foreach ($m4is_l9321 as $m4is_p786){
if((int) $m4is_p786 > 0){
$this->m4is_r1476()->grpAssign($m4is_h21895, (int) $m4is_p786);
 $this->m4is_i12($m4is_h21895);
 
}else{
$this->m4is_z40()->grpRemove($m4is_h21895, (int) abs($m4is_p786));
 $this->m4is_i12($m4is_h21895);
 
}
}$m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 $m4is_z06985 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'crm_id', 0 );
 $m4is_y803 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tags', '' );
 if($m4is_z06985 == $m4is_h21895){
$m4is_q06138 =explode(',', $m4is_y803 );
 foreach ($m4is_l9321 as $m4is_p786){
if($m4is_p786 > 0){
$m4is_q06138[]=$m4is_p786;
 
}else{
$m4is_p786 =(int) abs($m4is_p786);
 unset($m4is_q06138[$m4is_p786]);
 
}
}$m4is_v2613 ="REPLACE INTO %i SET `value` = %s WHERE `id` = %d AND `appname` = %s AND `fieldname` ='Groups'";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_p40::m4is_o1723(), implode(',', $m4is_q06138 ), $m4is_h21895, $this->m4is_i76('appname'));
 $wpdb->query($m4is_v2613 );
  
}$this->m4is_i12($m4is_h21895);
 $this->disable_login_redirect =TRUE;
 m4is_q82::m4is_u687($m4is_f087 );
 
}   
function m4is_a1094($m4is_w6921){
$m4is_w6921 =(int) $m4is_w6921;
 $m4is_q1046 ='memberium::email_template::' . $m4is_w6921;
  $m4is_p6925 =get_transient($m4is_q1046);
 if($m4is_p6925 === false){
$m4is_p6925 =$this->m4is_r1476()->getEmailTemplate($m4is_w6921);
 if(is_array($m4is_p6925)){
set_transient($m4is_q1046, $m4is_p6925, DAY_IN_SECONDS);
 
}else{
$m4is_p6925 =false;
 
}
}return $m4is_p6925;
 
}
function m4is_d49067($m4is_p6925){

}     
function m4is_h78651(){

}   
function m4is_k79(int $m4is_h21895, string $m4is_l9321 ='', string $m4is_i7193 ='', string $m4is_j661 ='', string $m4is_r9613 ='', bool $m4is_f860 =false, bool $m4is_i8169 =false): void {
if(!empty($m4is_j661)){
$this->m4is_t64038($m4is_j661, $m4is_h21895, $m4is_r9613);
 
}if(!empty($m4is_i7193)){
$this->m4is_u71903($m4is_i7193, $m4is_h21895);
 
}if(!empty($m4is_l9321)){
$this->m4is_k98($m4is_l9321, $m4is_h21895, $m4is_f860, $m4is_i8169);
 
}
}
function m4is_m84($m4is_h21895 =0, $m4is_j15 =[]){
$m4is_y642 =['actionsets' =>'', 'api_goals' =>'', 'tags' =>'', 'full_update' =>false, 'debug' =>false, 'appname' =>false, ];
 $m4is_j15 =wp_parse_args($m4is_j15, $m4is_y642 );
 do_action('memberium/do_actions_before', $m4is_h21895, $m4is_j15);
 if($m4is_h21895 ){
if(!empty($m4is_j15['tags'])){
$this->m4is_k98($m4is_j15['tags'], $m4is_h21895, $m4is_j15['full_update'], $m4is_j15['debug']);
 
}if(!empty($m4is_j15['actionsets'])){
$this->m4is_u71903($m4is_j15['actionsets'], $m4is_h21895 );
 
}if(!empty($m4is_j15['api_goals'])){
$this->m4is_t64038($m4is_j15['api_goals'], $m4is_h21895, $m4is_j15['appname']);
 
}
}do_action('memberium/do_actions_after', $m4is_h21895, $m4is_j15);
 
}
function m4is_t64038($m4is_j661 ='', $m4is_h21895 =false, $m4is_r9613 =false ){
if(empty($m4is_j661 )){
return;
 
}if(!m4is_s52::m4is_f27()){
return;
 
}if(!$m4is_h21895 ){
$m4is_h21895 =$this->m4is_z56();
 
}$m4is_h21895 =(int) $m4is_h21895;
 if($m4is_h21895 < 1 ){
return;
 
}if(!$m4is_r9613){
$m4is_r9613 =$this->m4is_i76('appname');
 
}if(!is_array($m4is_j661 )){
$m4is_j661 =array_filter(explode(',', $m4is_j661 ));
 
}$this->m4is_i260();
 $m4is_b527 =false;
 foreach($m4is_j661 as $m4is_v8723 ){
if($m4is_v8723 > '' ){
$m4is_u6591 =m4is_c69807::m4is_z3902($m4is_h21895, $m4is_v8723, $m4is_r9613 );
 $m4is_b527 =(boolean) ($m4is_u6591[0]['success']== 1 );
 
}
}if($m4is_b527 ){
$this->m4is_i12($m4is_h21895 );
 
}
}
function m4is_k59073($m4is_s90816, $m4is_h21895 =false ){
if(!m4is_s52::m4is_f27()){
return;
 
}if(!$m4is_h21895){
$m4is_h21895 =$this->m4is_z56();
 
}$m4is_h21895 =(int) $m4is_h21895;
 if($m4is_h21895 < 1){
return;
 
}if(!is_array($m4is_s90816 )){
$m4is_s90816 =array_filter(explode(',', $m4is_s90816));
 
}foreach ($m4is_s90816 as $fus_id){
if($fus_id > 0){
$this->m4is_r1476()->campAssign($m4is_h21895, $fus_id);
 
}
}
}
function m4is_f7350($m4is_s90816, $m4is_h21895 =0 ){
if(!m4is_s52::m4is_f27()){
return;
 
}if(!$m4is_h21895 ){
$m4is_h21895 =$this->m4is_z56();
 
}$m4is_h21895 =(int) $m4is_h21895;
 if(!$m4is_h21895 ){
return;
 
}if(!is_array($m4is_s90816 )){
$m4is_s90816 =array_filter(explode(',', $m4is_s90816));
 
}foreach($m4is_s90816 as $m4is_t51462 ){
if($m4is_t51462 > 0){
$this->m4is_r1476()->campPause($m4is_h21895, $m4is_t51462 );
 
}
}
}public 
function m4is_u71903($m4is_i7193, $m4is_h21895 =false): void {
if(empty($m4is_i7193 )){
return;
 
}if(!m4is_s52::m4is_f27()){
return;
 
}if(!$m4is_h21895 ){
$m4is_h21895 =$this->m4is_z56();
 
}$m4is_h21895 =(int) $m4is_h21895;
 if($m4is_h21895 < 1 ){
return;
 
}if(!is_array($m4is_i7193)){
$m4is_i7193 =array_filter(explode(',', $m4is_i7193));
 
}  $this->m4is_i260();
 $m4is_b527 =false;
 foreach ($m4is_i7193 as $m4is_w64602){
$m4is_w64602 =(int) $m4is_w64602;
 if($m4is_w64602 > 0){
$m4is_d5472 =m4is_j4156::m4is_w4805($m4is_h21895, (int) $m4is_w64602);
 if(is_array($m4is_d5472)){
foreach ($m4is_d5472 as $m4is_u6591){
if(strtolower($m4is_u6591['Message'])<> 'nothing to do'){
$m4is_b527 =true;
 
}
}unset($m4is_u6591);
 
}
}
} if($m4is_b527 ){
$m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 $this->m4is_x4831($m4is_h21895 );
 $this->m4is_i12($m4is_h21895 );
 m4is_q82::m4is_u687($m4is_f087 );
 
}return;
 
}public 
function m4is_w17($m4is_s90816, $m4is_h21895 =0): void {
if(!m4is_s52::m4is_f27()){
return;
 
}if(!$m4is_h21895 ){
$m4is_h21895 =$this->m4is_z56();
 
}$m4is_h21895 =(int) $m4is_h21895;
 if($m4is_h21895 < 1 ){
return;
 
}if(!is_array($m4is_s90816 )){
$m4is_s90816 =array_filter(explode(',', $m4is_s90816 ));
 
}foreach ($m4is_s90816 as $m4is_t51462){
if($m4is_t51462 > 0 ){
$this->m4is_r1476()->campRemove($m4is_h21895, $m4is_t51462 );
 
}
}
} public 
function m4is_k98($m4is_l9321 ='', $m4is_h21895 =false, $m4is_f860 =false, $m4is_i8169 =false ){
global $wpdb;
 if(!m4is_s52::m4is_f27()){
return;
 
}$m4is_p283 =$this->m4is_j498('settings', 'sync_tag_details', 0 );
 $m4is_r9613 =$this->m4is_i76('appname' );
 $m4is_h21895 =$m4is_h21895 ? $m4is_h21895 : $this->m4is_z56();
 $m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 if(!$m4is_h21895 ){
return;
 
}if(!is_array($m4is_l9321 )){
$m4is_l9321 =array_filter(array_map('trim', explode(',', $m4is_l9321 )));
 
}if(empty($m4is_l9321 )){
return;
 
}$m4is_t87 =array_filter(explode(',', m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tags', '' )));
 $m4is_s15437 =array_unique(m4is_k865::m4is_c743($m4is_l9321 ));
 $m4is_m65 =array_filter($m4is_s15437, function($m4is_d913 ){
return $m4is_d913 > 0;
 
});
 $m4is_x66781 =array_filter($m4is_s15437, function($m4is_d913 ){
return $m4is_d913 < 0;
 
});
 $m4is_x66781 =array_map('abs', $m4is_x66781 );
 $m4is_h327 =array_intersect($m4is_m65, $m4is_x66781 );
 $m4is_m65 =array_diff($m4is_m65, $m4is_t87, $m4is_h327 );
 $m4is_x66781 =array_intersect($m4is_x66781, $m4is_t87 );
 $m4is_x66781 =array_diff($m4is_x66781, $m4is_h327 );
 $m4is_n76604 =m4is_k865::m4is_i28($m4is_h21895, $m4is_m65 );
 $m4is_m7066 =m4is_k865::m4is_n14($m4is_h21895, $m4is_x66781 );
 $m4is_n76604 =$m4is_n76604['SUCCESS']?? [];
 $m4is_m7066 =$m4is_m7066['SUCCESS']?? [];
 $m4is_t87 =array_merge($m4is_t87, $m4is_n76604 );
 $m4is_t87 =array_diff($m4is_t87, $m4is_x66781 );
 sort($m4is_t87 );
 $m4is_t87 =implode(',', $m4is_t87 );
  $m4is_v2613 ='UPDATE %i SET `value` = %s WHERE `id` = %d AND `appname` = %s AND `fieldname` = "Groups";';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_p40::m4is_o1723(), $m4is_t87, $m4is_h21895, $m4is_r9613);
 $wpdb->query($m4is_v2613);
  if($m4is_p283 ){
$m4is_r9613 =$this->m4is_i76('appname' );
 $m4is_j49501 =m4is_k865::m4is_o39864();
 if(!empty($m4is_m65 )){
$m4is_g617 =['appname', 'contactid', 'tagid'];
 $m4is_m615 =[];
 $m4is_z34951 =[];
 $m4is_x39 =[];
 foreach($m4is_m65 as $m4is_d913 ){
$m4is_m615[]=[$m4is_r9613, $m4is_h21895, $m4is_d913 ];
 
}foreach ($m4is_m615 as $m4is_g91703 ){
$m4is_z34951[]='(%s, %d, %d)';
 foreach ($m4is_g91703 as $m4is_v586 ){
$m4is_x39[]=$m4is_v586;
 
}
}$m4is_o632 =implode(',', $m4is_g617 );
 $m4is_j864 =implode(',', $m4is_z34951 );
 $m4is_v2613 ="INSERT IGNORE INTO `{$m4is_j49501
}` ( {$m4is_o632
} ) VALUES {$m4is_j864
}";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, ...$m4is_x39 );
 $wpdb->query($m4is_v2613 );
 
}if(!empty($m4is_x66781 )){
$m4is_d579 =implode(',', $m4is_x66781 );
 $m4is_v2613 ="DELETE FROM %i WHERE `contactid` = %d AND `appname` = %s AND `tagid` IN ( {$m4is_d579
} ) ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_j49501, $m4is_h21895, $m4is_r9613 );
 $wpdb->query($m4is_v2613);
 
}
}foreach($m4is_n76604 as $m4is_d913 ){
do_action('memb_add_tag', $m4is_h21895, $m4is_d913 );
 do_action('memberium/tag/assign', $m4is_h21895, $m4is_d913 );
 
}foreach($m4is_m7066 as $m4is_d913 ){
do_action('memb_remove_tag', $m4is_h21895, $m4is_d913 );
 do_action('memberium/tag/remove', $m4is_h21895, $m4is_d913 );
 
}$this->m4is_i260();
 $this->m4is_i12($m4is_h21895 );
 m4is_q82::m4is_u687($m4is_f087 );
 
}public 
function m4is_h093(array $m4is_l9321, int $m4is_h21895 =0 ){
global $wpdb;
 if(!m4is_s52::m4is_f27()){
return;
 
}if(empty($m4is_l9321)){
return false;
 
}$m4is_h21895 =empty($m4is_h21895 )? $this->m4is_z56(): $m4is_h21895;
 if($m4is_h21895 < 1){
return;
 
}$m4is_d5472 =$this->m4is_z40()->addRemoveContactTags($m4is_h21895, $m4is_l9321);
 if(empty($m4is_d5472)||!is_array($m4is_d5472)||is_wp_error($m4is_d5472)){
return;
 
}$m4is_o316 =!empty($m4is_d5472['add'])&&!empty($m4is_d5472['add']['SUCCESS'])? $m4is_d5472['add']['SUCCESS']: false;
 $m4is_r05143 =!empty($m4is_d5472['remove'])&&!empty($m4is_d5472['remove']['SUCCESS'])? $m4is_d5472['remove']['SUCCESS']: false;
 if(!$m4is_o316 &&!$m4is_r05143 ){
return;
 
}$m4is_q26407 =($this->m4is_j498('settings', 'sync_tag_details')== 1 );
 $m4is_r9613 =$this->m4is_i76('appname');
 $m4is_b527 =false;
 $m4is_t87 =[];
 $m4is_e703 =($m4is_h21895 == $this->m4is_z56());
 $this->m4is_i260();
 $m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 $m4is_t87 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tags', '' );
 $m4is_t87 =array_flip(array_filter(explode(',', $m4is_t87 )));
  if($m4is_o316 ){
foreach ($m4is_o316 as $m4is_d913){
if(!array_key_exists($m4is_d913, $m4is_t87)){
if($m4is_q26407 ){
$m4is_v2613 ='INSERT IGNORE INTO `' . m4is_k865::m4is_o39864(). '` (`appname`, `contactid`, `tagid`) VALUES (%s, %d, %d);';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_r9613, $m4is_h21895, $m4is_d913 );
 $wpdb->query($m4is_v2613);
 
}$m4is_t87[$m4is_d913 ]=!empty($m4is_t87)? max($m4is_t87)+ 1 : 1;
 $m4is_b527 =true;
 do_action('memb_add_tag', $m4is_h21895, $m4is_d913);
 do_action('memberium/tag/assign', $m4is_h21895, $m4is_d913 );
 
}
}
}unset($m4is_o316);
  if($m4is_r05143 ){
foreach ($m4is_r05143 as $m4is_d913){
if(array_key_exists($m4is_d913, $m4is_t87)){
if($m4is_q26407 ){
$m4is_v2613 ='DELETE FROM `' . m4is_k865::m4is_o39864(). '` WHERE `contactid` = %d AND `tagid` = %d AND `appname` = %s ;';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_h21895, $m4is_d913, $m4is_r9613);
 $wpdb->query($m4is_v2613);
 
}unset($m4is_t87[$m4is_d913 ]);
 $m4is_b527 =true;
 do_action('memb_remove_tag', $m4is_h21895, $m4is_d913);
 do_action('memberium/tag/remove', $m4is_h21895, $m4is_d913);
 
}
}
}unset($m4is_r05143);
  if($m4is_b527 ){
$m4is_t87 =array_flip($m4is_t87);
 sort($m4is_t87);
 $m4is_t87 =implode(',', $m4is_t87);
 $m4is_v2613 ='UPDATE `' . m4is_p40::m4is_o1723(). '` SET `value` = %s WHERE `id` = %d AND `appname` = %s AND `fieldname` = "Groups";';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_t87, $m4is_h21895, $m4is_r9613);
 $wpdb->query($m4is_v2613);
 $this->m4is_i12($m4is_h21895);
 $this->m4is_s64809($m4is_h21895 );
  m4is_q82::m4is_u687($this->m4is_x66());
 
}return;
 
}
function m4is_p98460(array $m4is_v30712, int $m4is_d913 ): bool {
global $wpdb;
 if(!m4is_s52::m4is_f27()){
return false;
 
}if(empty($m4is_v30712 )||empty($m4is_d913 )){
return false;
 
}$m4is_c05328 =$m4is_d913 < 0 ? 'remove' : 'add';
 $m4is_d5472 =$this->m4is_z40()->addRemoveTagContacts($m4is_v30712, $m4is_d913);
 $m4is_d913 =abs($m4is_d913);
 foreach($m4is_v30712 as $m4is_h21895 ){
$this->m4is_s64809($m4is_h21895 );
 
}if(empty($m4is_d5472 )||!array_key_exists($m4is_d913, $m4is_d5472 )){
return false;
 
}$m4is_d5472 =$m4is_d5472[$m4is_d913];
 if(empty($m4is_d5472['SUCCESS'])){
return false;
 
}$m4is_q26407 =($this->m4is_j498('settings', 'sync_tag_details' )== 1 );
 $m4is_r9613 =$this->m4is_i76('appname');
 $m4is_k76508 =m4is_p40::m4is_l0937($m4is_d5472['SUCCESS']);
 $m4is_m5963 =m4is_k865::m4is_o39864();
  foreach ($m4is_d5472['SUCCESS']as $m4is_h21895 ){
$m4is_b527 =false;
 $m4is_t87 =array_key_exists($m4is_h21895, $m4is_k76508 )? $m4is_k76508[$m4is_h21895]->value : '';
 $m4is_t87 =empty($m4is_t87 )? []: array_flip(array_filter(explode(',', $m4is_t87 )));
  if($m4is_c05328 === 'add' ){
if(!array_key_exists($m4is_d913, $m4is_t87 )){
if($m4is_q26407 ){
$m4is_v2613 ='INSERT IGNORE INTO %i (`appname`, `contactid`, `tagid`) VALUES (%s, %d, %d);';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_m5963, $m4is_r9613, $m4is_h21895, $m4is_d913 );
 $wpdb->query($m4is_v2613);
 
}$m4is_t87[$m4is_d913 ]=!empty($m4is_t87)? max($m4is_t87)+ 1 : 1;
 $m4is_b527 =true;
 do_action('memb_add_tag', $m4is_h21895, $m4is_d913);
 do_action('memberium/tag/assign', $m4is_h21895, $m4is_d913 );
 
}
} else{
if(array_key_exists($m4is_d913, $m4is_t87)){
if($m4is_q26407 ){
$m4is_v2613 ='DELETE FROM %i WHERE `contactid` = %d AND `tagid` = %d AND `appname` = %s ;';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_m5963, $m4is_h21895, $m4is_d913, $m4is_r9613);
 $wpdb->query($m4is_v2613);
 
}unset($m4is_t87[$m4is_d913 ]);
 $m4is_b527 =true;
 do_action('memb_remove_tag', $m4is_h21895, $m4is_d913);
 do_action('memberium/tag/remove', $m4is_h21895, $m4is_d913);
 
}
} if($m4is_b527 ){
$m4is_t87 =array_flip($m4is_t87 );
 sort($m4is_t87);
 $m4is_t87 =implode(',', $m4is_t87);
 $m4is_v2613 ='UPDATE %i SET `value` = %s WHERE `id` = %d AND `appname` = %s AND `fieldname` = "Groups";';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_m5963, $m4is_t87, $m4is_h21895, $m4is_r9613);
 $m4is_u6591 =$wpdb->query($m4is_v2613);
 $this->m4is_i12($m4is_h21895);
 
}
}foreach($m4is_v30712 as $m4is_h21895 ){
$this->m4is_s64809($m4is_h21895 );
 
}return $m4is_d5472;
 
}   
function m4is_t594(){
$m4is_u4290 =$this->m4is_x72();
 foreach ($m4is_u4290 as $m4is_b9687){
$m4is_h21895 =0;
 if(!empty($m4is_b9687['data']['contact_id'])){
$m4is_h21895 =(int) $m4is_b9687['data']['contact_id'];
 
}else{
$m4is_d07693 =(int) $m4is_b9687['data']['recurringorder_id'];
 if($m4is_d07693 > 0){
$m4is_a89 =['ContactId'];
 $m4is_g91703 =$this->m4is_r1476()->dsLoad('RecurringOrder', $m4is_d07693, $m4is_a89);
 if(is_array($m4is_g91703)){
$m4is_h21895 =(int) $m4is_g91703['ContactId'];
 
}
}
}if($m4is_h21895 > 0){
if($m4is_b9687['actiontype']== 'achievegoal'){
$m4is_v8723 =$m4is_b9687['data']['end_goal'];
 m4is_c69807::m4is_z3902($m4is_h21895, $m4is_v8723, $m4is_b9687['appname']);
 $this->m4is_y98602($m4is_b9687);
 
}elseif($m4is_b9687['actiontype']== 'actionset'){
$m4is_w64602 =(int) $m4is_b9687['data']['end_action'];
 m4is_j4156::m4is_w4805($m4is_h21895, $m4is_w64602 );
 $this->m4is_y98602($m4is_b9687);
 
}elseif($m4is_b9687['actiontype']== 'settags'){

}elseif($m4is_b9687['actiontype']== 'sendemail'){

}
}
}
}
function m4is_q65($m4is_l8360, $m4is_w5120, $m4is_l91805){
global $wpdb;
 $m4is_r9613 =$this->m4is_i76('appname' );
 $m4is_l8360 =strtolower(trim($m4is_l8360 ));
 $m4is_q4296 =strtotime($m4is_w5120 );
 $m4is_v2613 ='INSERT INTO %i (`pidlock`, `appname`, `actiontype`, `scheduled`, `data`) VALUES ( -1, %s, %s, %s, %s )';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, constant('MEMBERIUM_DB_QUEUE' ), $m4is_r9613, $m4is_l8360, $m4is_w5120, json_encode($m4is_l91805));
 $wpdb->query($m4is_v2613 );
 $m4is_d07693 =$wpdb->insert_id;
 return $m4is_d07693;
 
}
function m4is_x72(){
global $wpdb;
 $m4is_r9613 =$this->m4is_i76('appname');
  $m4is_p27 =mt_rand(1000000, 9999999 );
 $m4is_v2613 ='UPDATE %i SET `pidlock` = %d WHERE `appname`= %s AND `scheduled` <= NOW() AND `pidlock` < 1 ;';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, constant('MEMBERIUM_DB_QUEUE' ), $m4is_p27, $m4is_r9613);
 $wpdb->query($m4is_v2613);
 $m4is_v2613 ='SELECT * FROM %i WHERE `pidlock` = %d AND `appname`= %s ;';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, constant('MEMBERIUM_DB_QUEUE' ), $m4is_p27, $m4is_r9613);
 $m4is_y766 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 foreach ($m4is_y766 as $m4is_l9671 =>$m4is_v586){
$m4is_l91805 =json_decode($m4is_v586['data'], true);
 if($m4is_l91805){
$m4is_y766[$m4is_l9671]['data']=$m4is_l91805;
 
}
}return $m4is_y766;
 
}
function m4is_y98602($m4is_b9687 ){
$m4is_d07693 =(int) $m4is_b9687['id'];
 if($m4is_d07693){
global $wpdb;
 $m4is_v2613 ='DELETE FROM %i WHERE `id` = %d and `appname` = %s';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, constant('MEMBERIUM_DB_QUEUE' ), $m4is_b9687['id'], $m4is_b9687['appname']);
 $wpdb->query($m4is_v2613);
 
}
}   
function m4is_u3540($m4is_k52736, $m4is_v586 =1 ){
$m4is_k52736 =trim($m4is_k52736);
 if(empty($m4is_k52736)){
return false;
 
}global $wpdb;
 $m4is_v2613 ='';
 return false;
 
}   
function m4is_o19084(){
$m4is_j15 =unserialize(base64_decode($_POST['payload']));
 $m4is_h21895 =(int) $m4is_j15['contact_id'];
 $m4is_l9321 =$m4is_j15['tag_id'];
 $m4is_j661 =$m4is_j15['goals'];
 $m4is_i7193 =$m4is_j15['action_id'];
 $m4is_o31859 ='';
  if($m4is_l9321 > ''){
$this->m4is_q96($m4is_h21895, $m4is_l9321);
 
}if($m4is_i7193 > ''){
$this->m4is_u71903($m4is_h21895, $m4is_i7193);
 
}if($m4is_j661 > ''){
$this->m4is_t64038($m4is_j661, $m4is_h21895);
 
}$this->m4is_x4831($m4is_h21895);
 die();
 
}
function m4is_t61($m4is_p5602, $precision =2){
$m4is_s926 =['B', 'KB', 'MB', 'GB', 'TB'];
 $m4is_p5602 =max($m4is_p5602, 0);
 $m4is_u68974 =floor(($m4is_p5602 ? log($m4is_p5602): 0)/ log(1024));
 $m4is_u68974 =min($m4is_u68974, count($m4is_s926)- 1);
 $m4is_p5602 /= (1 << (10 * $m4is_u68974));
 return round($m4is_p5602, $precision). ' ' . $m4is_s926[$m4is_u68974];
 
}   
function m4is_k29068($m4is_k52736 ='' ){
$m4is_k52736 =strtolower(trim($m4is_k52736));
 $m4is_y50 =false;
 $m4is_m63284 =$this->m4is_j498('remote_files' );
 if($m4is_k52736){
if(array_key_exists($m4is_k52736, $m4is_m63284)){
$m4is_y50 =$m4is_m63284[$m4is_k52736];
 $m4is_y642 =['region' =>'us-east-1', ];
 $m4is_y50 =wp_parse_args($m4is_y50, $m4is_y642);
 
}
}return $m4is_y50;
 
}       
function m4is_c17862(){
 
}
function m4is_i6249(){
$m4is_e80 ='SavedFilter';
 $m4is_h831 =[];
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_h3647 =m4is_c69807::m4is_f5248('SavedFilter');
 $m4is_v76912 =['ReportStoredName' =>'AffiliateActivitySummary', ];
 do {
$m4is_m615 =m4is_c69807::m4is_o986($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647);
 foreach($m4is_m615 as $m4is_g91703){
$m4is_h831[$m4is_g91703['Id']]=['UserId' =>(int) $m4is_g91703['UserId'], 'FilterName' =>$m4is_g91703['FilterName'], ];
 
}$m4is_d3012++;
 
}while (count($m4is_m615)>= $m4is_c92430);
 unset($m4is_m615, $m4is_g91703, $m4is_d3012, $m4is_c92430, $m4is_e80, $m4is_h3647, $m4is_v76912);
 if(count($m4is_h831)== 1){

}return $m4is_h831;
 
}
function m4is_s93406($m4is_h21895 =0, $m4is_y5760 ='first'){

}
function m4is_v921($m4is_h21895, $m4is_r637 ){
if(!$m4is_h21895 ||empty($m4is_r637 )){
return;
 
}$m4is_r637 =strtolower($m4is_r637);
 $m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 $m4is_u6591 =m4is_q82::m4is_k660($m4is_f087, 'contact', $m4is_r637, '' );
 return $m4is_u6591;
 
}    public 
function m4is_w36(int $m4is_p2136, int $m4is_f087, bool $m4is_h13956 ): int {
$m4is_t05 =(bool) $this->m4is_j498('settings', 'persistent_login' );
 if(!$m4is_t05 ){
return $m4is_p2136;
 
}if(user_can($m4is_f087, 'manage_options' )){
return max($m4is_p2136, WEEK_IN_SECONDS );
 
}$m4is_p2136 =6 * MONTH_IN_SECONDS;
 return $m4is_p2136;
 
}
function m4is_p385(array $m4is_y642 ){
if(!$m4is_y642['value_remember']){
if($this->m4is_j498('settings', 'persistent_login')){
$m4is_y642['value_remember']=true;
 
}
}return $m4is_y642;
 
} 
function m4is_d36602($m4is_m676, $m4is_x1486 =0, $m4is_f49 =true, $m4is_c74059 =true ){
if($this->m4is_j498('settings', 'password_field')== 'Password'){
$m4is_m676 =substr($m4is_m676, 0, 20);
 
}return $m4is_m676;
 
} private 
function m4is_u27541(): void {
if(headers_sent()){
return;
 
}$m4is_a6814 =$this->m4is_j498('settings', 'version', $this->m4is_w45());
 $m4is_y219 =$this->m4is_j498('settings', 'disable_xframe', false );
 header('X-Powered-By: Memberium ' . $m4is_a6814, false );
 if(!$m4is_y219){
header('X-Frame-Options: SAMEORIGIN');
 
}
} private 
function m4is_s20(): void {
add_action('clear_auth_cookie', ['m4is_l5841', 'm4is_l580']);
 add_action('login_form_login', ['m4is_l5841', 'm4is_u5823'], -1000, 0 );
 add_action('wp_authenticate', ['m4is_l5841', 'm4is_e40'], 10);
 add_action('wp_authenticate', ['m4is_l5841', 'm4is_w9147'], 1);
 add_action('wp_login', ['m4is_l5841', 'm4is_n6012'], 900, 2);
  add_action('wp_login', ['m4is_l5841', 'm4is_q4626'], 20, 2);
 add_filter('authenticate', ['m4is_l5841', 'm4is_m397'], 10, 3 );
 add_filter('authenticate', ['m4is_l5841', 'm4is_l8061'], 10, 3 );
 add_filter('authenticate', ['m4is_l5841', 'm4is_c09'], 100, 3 );
 add_filter('authenticate', ['m4is_l5841', 'm4is_c58692'], -10, 3 );
  add_filter('authenticate', ['m4is_l5841', 'm4is_i56340'], -15, 3 );
 add_filter('login_form_defaults', [$this, 'm4is_p385']);
 add_filter('login_redirect', ['m4is_l5841', 'm4is_w47986'], 999999, 3 );
 add_filter('login_url', [$this, 'm4is_p5086'], 999999, 3);
 add_filter('wp_login', ['m4is_l5841', 'm4is_x62'], 9 );
  add_filter('authenticate', ['m4is_l5841', 'm4is_t713'], -1000, 3);
 add_filter('login_form_bottom', ['m4is_l5841', 'm4is_u06'], 10, 2);
 add_action('login_form', ['m4is_l5841', 'm4is_u06']);
 
} private 
function m4is_t13(): void {
$m4is_o076 =time()- 300;
 $m4is_u4290 =['memberium_maintenance' =>'hourly', 'memberium/contacts/makepass_scan' =>'3min', ];
 foreach($m4is_u4290 as $m4is_b9687 =>$m4is_x436){
$m4is_a873 =time()+ mt_rand(600, 900);
 $m4is_q7136 =(int) wp_next_scheduled($m4is_b9687);
 if($m4is_q7136 < $m4is_o076){
wp_clear_scheduled_hook($m4is_b9687);
 wp_schedule_event(time()+ mt_rand(600, 900), $m4is_x436, $m4is_b9687);
 
}
}
} private 
function m4is_q416(): void {
 add_filter('cron_schedules', ['m4is_d47529', 'm4is_g6231'], 1 );
  add_action('memberium/actionsets/sync', ['m4is_j4156', 'm4is_f7940']);
 add_action('memberium/contacts/async', ['m4is_p40', 'm4is_h76054']);
 add_action('memberium/contacts/makepass_scan', ['m4is_f642', 'm4is_t2678']);
 add_action('memberium/contacts/sync_custom_fields', ['m4is_s695', 'm4is_r2903']);
 add_action('memberium/invoices/sync', ['m4is_v87365', 'm4is_l318']);
 add_action('memberium/maintenance/logs/trim', ['m4is_q62395', 'm4is_g2674']);
 add_action('memberium/maintenance/logs/trim', ['m4is_l5841', 'm4is_j3746']);
 add_action('memberium/maintenance/updater', ['m4is_l9685', 'm4is_x75']);
 add_action('memberium/products/sync', ['m4is_v87365', 'm4is_a0846']);
 add_action('memberium/subscriptions/scan_expired', ['m4is_f642', 'm4is_l6254']);
 add_action('memberium/subscriptions/sync', ['m4is_v87365', 'm4is_x096']);
 add_action('memberium/tags/categories/sync', ['m4is_z6894', 'm4is_d7492']);
 add_action('memberium/tags/sync', ['m4is_k865', 'm4is_l26']);
 add_action('wp_version_check', ['m4is_s52', 'm4is_a834']);
 add_action('memberium/affiliates/running_totals', ['m4is_z13097', 'm4is_h18']);
 add_action('memberium/maintenance/daily', ['m4is_f642', 'm4is_d267']);
   add_action('memberium_maintenance', ['m4is_f642', 'm4is_c69762']);
 
} private 
function m4is_z51608(): void {
add_filter('wp_privacy_personal_data_erasers', ['m4is_o106', 'm4is_t102'], 11 );
 add_filter('wp_privacy_personal_data_exporters', ['m4is_o106', 'm4is_d60'], 10 );
 
}private 
function m4is_f19723(){
 if($this->m4is_j498('settings', 'multi_language', 0)){
add_filter('gettext', ['m4is_d86', 'm4is_e97'], PHP_INT_MAX, 3);
 add_filter('gettext_with_context', ['m4is_d86', 'm4is_q2631'], PHP_INT_MAX, 4);
 
}
}private 
function m4is_a74035(){
add_filter('update_user_metadata', [$this, 'm4is_i65164'], 10, 5 );
 add_filter('memberium/usermeta/crm_field_maps', [$this, 'm4is_i2096'], 10, 1 );
 
}private 
function m4is_c32(){
add_action('init', [$this, 'm4is_e39617'], PHP_INT_MAX );
 
}private 
function m4is_f09(){
add_filter('site_status_tests', ['m4is_i0574', 'm4is_k60567']);
  add_filter('debug_information', ['m4is_i0574', 'm4is_t64']);
 
}private 
function m4is_r6567(){
$m4is_s6347 ='m4is_l9685';
 add_filter('updater_plugins_api_result', [$m4is_s6347, 'm4is_k018'], 10, 3 );
 add_filter('updater_plugins_api', [$m4is_s6347, 'm4is_q582'], 10, 3 );
 add_filter('updater_pre_set_site_transient_update_plugins', [$m4is_s6347, 'm4is_c765'], 10, 1 );
 add_filter('update_plugins_memberium.com', [$m4is_s6347, 'm4is_y17584'], 20, 4 );
 
}private 
function m4is_d4861(){
$this->m4is_q416();
 $this->m4is_r6567();
 $this->m4is_s20();
 $this->m4is_z51608();
 $this->m4is_r76();
 $this->m4is_f09();
 $this->m4is_f19723();
 $this->m4is_a74035();
 $this->m4is_c32();
 add_action('set_current_user', [$this, 'm4is_u6764'], 11 );
 add_filter('pre_user_url', ['m4is_z37620', 'm4is_j602'], PHP_INT_MAX, 1 );
 add_filter('admin_email_check_interval', '__return_false' );
 add_filter('auth_cookie_expiration', [$this, 'm4is_w36'], PHP_INT_MAX - 1, 3 );
 add_filter('http_request_args', [$this, 'm4is_o31627'], PHP_INT_MAX, 2 );
 add_filter('wp_nav_menu_args', [$this, 'm4is_a3427']);
     add_action('after_setup_theme', [$this, 'm4is_q166'], 10 );
 add_action('admin_bar_menu', ['m4is_n2043', 'm4is_l01263'], 71 );
  add_action('admin_bar_menu', ['m4is_n2043', 'm4is_o0352'], 101 );
  add_action('after_password_reset', [$this, 'm4is_v081'], 10, 2 );
 add_action('i2sdk_custom_fields_sync', [$this, 'm4is_q1590'], 10, 2 );
 add_action('init', ['m4is_q690', 'm4is_s8624'], 1 );
 add_action('init', [$this, 'm4is_k94']);
 add_action('wp_insert_post', [$this, 'm4is_n607'], PHP_INT_MAX, 1 );
 add_action('post_updated', [$this, 'm4is_n607'], PHP_INT_MAX, 1 );
 add_action('deleted_post', [$this, 'm4is_n607'], PHP_INT_MAX, 1 );
 add_action('shutdown', [$this, 'm4is_p36198'], 10, 0 );
 add_action('init', [$this, 'm4is_a06'], 20 );
 add_action('wp_footer', [$this, 'm4is_q98'], PHP_INT_MAX );
 add_action('shutdown', [$this, 'm4is_o73'], PHP_INT_MAX );
 add_action('wp_ajax_memb_ajax_actions', [$this, 'm4is_o19084'], 99 );
 add_action('wp_ajax_nopriv_memb_ajax_actions', [$this, 'm4is_o19084'], 99 );
 add_filter('x_redirect_by', [$this, 'm4is_d68275'], 1, 3 );
 add_action('deleted_user', ['m4is_s52', 'm4is_b28571'], PHP_INT_MAX, 0 );
 add_action('user_register', ['m4is_s52', 'm4is_b28571'], PHP_INT_MAX, 0 );
 add_action('user_register', ['m4is_z37620', 'm4is_s18'], 10, 2 );
   add_action('woocommerce_customer_reset_password', [$this, 'm4is_v081'], 10, 1);
  add_filter('wpseo_indexable_excluded_post_types', [$this, 'm4is_c40']);
  add_action('wpdc_sso_provider_before_sso_redirect', ['m4is_e35', 'm4is_k2851'], 10, 2 );
  $m4is_e439 =did_action('plugins_loaded' );
 $m4is_e439 ? $this->m4is_u812(): add_action('plugins_loaded', [$this, 'm4is_u812'], 8, 0 );
 $m4is_e439 ? $this->m4is_m0665(): add_action('plugins_loaded', [$this, 'm4is_m0665'], 9, 0 );
 $m4is_e439 ? $this->m4is_e3840(): add_action('plugins_loaded', [$this, 'm4is_e3840'], 1 );
 
} private 
function m4is_b06254(){
global $wpdb;
 $m4is_l1940 =['MEMBERIUM_BETA' =>0, 'MEMBERIUM_DB_CONTACTTAGS' =>'memberium_contacttags', 'MEMBERIUM_DB_EVENTS' =>"{$wpdb->prefix
}memberium_events", 'MEMBERIUM_DB_HTTPPOST' =>'memberium_httppost', 'MEMBERIUM_DB_JOBS' =>'memberium_jobs', 'MEMBERIUM_DB_LOGINLOG' =>'memberium_loginlog', 'MEMBERIUM_DB_PAGETRACKING' =>"{$wpdb->prefix
}memberium_pagetracking", 'MEMBERIUM_DB_QUEUE' =>'memberium_queue', 'MEMBERIUM_DB_RELATIONSHIP_TYPES' =>"{$wpdb->prefix
}memberium_relationship_types", 'MEMBERIUM_DB_SOCIAL' =>"{$wpdb->prefix
}memberium_socialaccount", 'MEMBERIUM_DEBUG' =>0, 'MEMBERIUM_DEBUGLOG' =>"{$_SERVER['DOCUMENT_ROOT']
}/debuglog.txt", 'MEMBERIUM_DELIMITER' =>',', 'MEMBERIUM_ERRORLOG' =>0, 'MEMBERIUM_INSTALLED' =>1, 'MEMBERIUM_NESTING_LEVELS' =>10, 'MEMBERIUM_NOWYSIWYG' =>0, 'MEMBERIUM_SKU' =>'m4is',   ];
 foreach($m4is_l1940 as $m4is_o015 =>$m4is_k72){
defined($m4is_o015)? '' : define($m4is_o015, $m4is_k72);
 
}
} private 
function m4is_p85674(){
 $m4is_k86914 =MEMBERIUM_HOME;
  $m4is_s6347 ='m4is_n17';
  register_activation_hook($m4is_k86914, [$m4is_s6347, 'm4is_l2147']);
  register_uninstall_hook($m4is_k86914, [$m4is_s6347, 'm4is_d029']);
  register_deactivation_hook($m4is_k86914, [$m4is_s6347, 'm4is_f65349']);
 
} private 
function m4is_a28739(){
 if(!headers_sent()){
 $m4is_u276 =session_status();
  if($m4is_u276 === PHP_SESSION_DISABLED){
error_log('Memberium: [warning] PHP Sessions Disabled');
 return;
 
} elseif($m4is_u276 === PHP_SESSION_ACTIVE){
return;
 
}  $m4is_a26861 =$this->m4is_j498('settings', 'microcache_compat_session' );
  if(empty($m4is_a26861 )){
@session_start();
 
}
}
}    public 
function m4is_f9708(array $m4is_m615 ): array {
if(!$this->m4is_j498('settings', 'plaintext_db', false )){
return $m4is_m615;
 
}foreach ($m4is_m615 as $m4is_k52736 =>$m4is_v586){
$m4is_m615[$m4is_k52736]=remove_accents($m4is_v586 );
 $m4is_m615[$m4is_k52736]=preg_replace('/[\x80-\xFF]/', '', $m4is_m615[$m4is_k52736]);
 
} return $m4is_m615;
 
} public 
function m4is_f0367(array $m4is_x9366, $m4is_k52736 ='fieldname', $m4is_v586 ='value'): array {
 $m4is_m615 =[];
  foreach ($m4is_x9366 as $m4is_j90523){
  $m4is_m615[$m4is_j90523[$m4is_k52736]]=$m4is_j90523[$m4is_v586];
 
} return $m4is_m615;
 
}public 
function m4is_t8350(array $m4is_w564, array $m4is_o968 ){
$m4is_z9157 =[];
 foreach($m4is_w564 as $m4is_l9671 =>$m4is_v586 ){
if(!array_key_exists($m4is_l9671, $m4is_o968 )){
$m4is_z9157[$m4is_l9671]='';
 
}elseif($m4is_v586 !== $m4is_o968[$m4is_l9671]){
$m4is_z9157[$m4is_l9671]=$m4is_o968[$m4is_l9671];
 
}
}return $m4is_z9157;
  
} public 
function m4is_p5672(array $m4is_w564, array $m4is_o968): array {
   return array_keys(array_diff_key($m4is_w564, $m4is_o968));
 
} public 
function m4is_f247(array $m4is_w564, array $m4is_o968): array {
   return array_diff_key($m4is_o968, $m4is_w564);
 
} public 
function m4is_g861(array $m4is_b912, array $m4is_h8537 ): array {
 foreach ($m4is_h8537 as $m4is_g730 ){
unset($m4is_b912[$m4is_g730]);
 
} return $m4is_b912;
 
}   static 
function m4is_g6365(): string {
return i2sdk_class::DB_API_LOG;
 
}static 
function m4is_k8034(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_g6365();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) NOT NULL AUTO_INCREMENT, \n" . "appname varchar(32) NOT NULL, \n" . "timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, \n" . "duration float NOT NULL DEFAULT '0', \n" . "retries int(11) NOT NULL DEFAULT '0', \n" . "ip_address varchar(45) DEFAULT NULL, \n" . "user varchar(32) DEFAULT NULL, \n" . "service varchar(32) DEFAULT NULL, \n" . "caller longtext, \n" . "result longtext, \n" . "PRIMARY KEY  (id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
} static 
function m4is_g10948(): string {
return constant('MEMBERIUM_DB_QUEUE' );
 
} static 
function m4is_p9768(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_g10948();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(11) NOT NULL AUTO_INCREMENT, \n" . "pidlock int(11) NOT NULL default 0, \n" . "appname varchar(16) NOT NULL, \n" . "actiontype varchar(20) NOT NULL, \n" . "scheduled timestamp NOT NULL, \n" . "contactid int(11) NOT NULL default 0, \n" . "userid int(11) NOT NULL default 0, \n" . "data text NOT NULL, \n" . "KEY scheduled (scheduled), \n" . "KEY actiontype (actiontype), \n" . "KEY contactid (contactid), \n" . "KEY userid (userid), \n" . "PRIMARY KEY  (id,appname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}    public 
function m4is_d68275($m4is_v96, $m4is_c31, $m4is_o69740 ): string {
if(!m4is_a01587::m4is_j301()){
return $m4is_v96;
 
}if($m4is_v96 !== 'WordPress' ){
return $m4is_v96;
 
}$m4is_y9268 =debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS );
 $m4is_d07693 =0;
 krsort($m4is_y9268 );
 foreach($m4is_y9268 as $m4is_c5468 =>$m4is_x35 ){
if(!empty($m4is_x35['function'])&&in_array($m4is_x35['function'], ['wp_safe_redirect', 'wp_redirect' ])){
$m4is_d07693 =$m4is_c5468 + 1;
 break;
 
}
}if($m4is_d07693 &&!empty($m4is_y9268[$m4is_d07693])){
$m4is_x35 =$m4is_y9268[$m4is_d07693];
 $m4is_a1056 =empty($m4is_x35['function'])? '' : 'Function ' . $m4is_x35['function']. '() @ ';
 $m4is_v96 =sprintf('Line %d, %s in %s', $m4is_x35['line'], $m4is_a1056, substr($m4is_x35['file'], strlen($_SERVER['DOCUMENT_ROOT'])));
 
}return 'Debug : ' . $m4is_v96;
 
}    
function m4is_n607(int $m4is_b4068 ){
$m4is_o14 ='memberium/posts';
 wp_cache_delete("meta/access/{$m4is_b4068
}", $m4is_o14 );
 wp_cache_delete('meta/access/hidden_menu_items', $m4is_o14 );
 wp_cache_delete('admin/list/json', $m4is_o14 );
 wp_cache_flush_group($m4is_o14 );
 
}    public 
function m4is_d842(string $m4is_s36520 ='', int $m4is_f087 =0){
$m4is_s36520 =strtolower(trim($m4is_s36520 ));
 $m4is_f087 =empty($m4is_f087 )? $this->m4is_x66(): $m4is_f087;
 if((!$m4is_f087)||empty($m4is_s36520 )){
return false;
 
}return get_user_meta($m4is_f087, self::USER_FIELD_PREFIX . $m4is_s36520, true);
 
}public 
function m4is_y165(string $m4is_s36520 ='', $m4is_v586 ='', int $m4is_f087 =0 ){
$m4is_s36520 =strtolower(trim($m4is_s36520 ));
 $m4is_f087 =empty($m4is_f087 )? $this->m4is_x66(): $m4is_f087;
 if((!$m4is_f087 )||empty($m4is_s36520 )){
return false;
 
}update_user_meta($m4is_f087, self::USER_FIELD_PREFIX . $m4is_s36520, $m4is_v586 );
 
} 
}final 
class memberium_service_class {
private static $m4is_a73 =[];
 private static $m4is_d2365 =[];
 private 
function __construct(){
 
} public static 
function m4is_u684(string $m4is_l9671, $m4is_j9168 ): bool {
if(!array_key_exists($m4is_l9671, self::$m4is_a73 )){
self::$m4is_a73[$m4is_l9671]=$m4is_j9168;
 return true;
 
}error_log(sprintf("Memberium: [warning] Service '%s' is already registered.", $m4is_l9671 ));
 return false;
 
}public static 
function m4is_l463(string $m4is_l9671, string $m4is_s6347, string $m4is_r176 =null ){
if(!array_key_exists($m4is_l9671, self::$m4is_d2365 )){
self::$m4is_d2365[$m4is_l9671]=['class' =>$m4is_s6347, 'method' =>$m4is_r176 ];
 return true;
 
}
}public static 
function m4is_y28(string $m4is_l9671 ){
unset(self::$m4is_a73[$m4is_l9671]);
 
}public static 
function m4is_a40(string $m4is_l9671 ){
if(array_key_exists($m4is_l9671, self::$m4is_a73 )){
return self::$m4is_a73[$m4is_l9671];
 
}if(array_key_exists($m4is_l9671, self::$m4is_d2365 )){
$m4is_s6347 =self::$m4is_d2365[$m4is_l9671]['class'];
 $m4is_s7349 =self::$m4is_d2365[$m4is_l9671]['method']?? null;
 if(is_null($m4is_s7349 )){
$m4is_a73[$m4is_l9671]=new $m4is_s6347;
 
}else{
 $m4is_j9168[$m4is_l9671]=new $m4is_s6347::$m4is_s7349;
 
}return self::$m4is_a73[$m4is_l9671];
 
}return null;
 
}public static 
function m4is_e63(){
foreach(self::$m4is_a73 as $m4is_l9671 =>$m4is_j9168 ){
if(is_object($m4is_j9168 )){
echo $m4is_l9671 . ' : ' . get_class($m4is_j9168 ). '<br>';
 
}else{
echo $m4is_l9671 . ' : ' . gettype($m4is_j9168 ). '<br>';
 
}
}
}
}

