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


 class_exists('m4is_r83' )||die();
  final 
class m4is_n17 {
private static $m4is_r1546;
 private static $m4is_y76859;
  static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_y76859 ='';
 
} static 
function m4is_l2147($m4is_l051 =false ){
ob_start();
 self::m4is_j0752('Starting network activation: ' . date('Y-m-d h:i:s' ), false );
  self::m4is_z126(false );
  if(function_exists('is_multisite' )&&is_multisite()){
global $wpdb;
  if($m4is_l051 ){
$m4is_c7268 =get_current_blog_id();
  $m4is_c342 =get_sites();
 if(is_array($m4is_c342 )){
foreach ($m4is_c342 as $m4is_s740 ){
switch_to_blog($m4is_s740 );
 self::m4is_z126(true );
 
} switch_to_blog($m4is_c7268 );
 
}
}
}$m4is_b132 =ob_get_contents();
 ob_end_clean();
 if(!empty($m4is_b132 )){
self::m4is_j0752('Unexpected output during network activation: ' . $m4is_b132, true );
 
}self::m4is_j0752('Ending network activation: ' . date('Y-m-d h:i:s' ), false );
 
} static 
function m4is_z126(bool $m4is_x1269 =false ){
global $wpdb;
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
 $m4is_o076 =time();
  add_option('memberium/activation_timestamp', $m4is_o076, false );
  update_option('memberium/system/config/timestamp', microtime(true ), true );
  self::m4is_y967();
  self::m4is_r6902();
  $m4is_x39508 =get_option('i2sdk');
  self::m4is_g75480();
  self::m4is_n8097();
  self::m4is_j19();
  self::m4is_p04631();
  self::m4is_v14567();
  m4is_d47529::m4is_x0769();
  self::m4is_z06();
  self::m4is_i437();
  m4is_g689::m4is_y46135();
  if(!defined('I2SDK_HOME' )){
require_once self::$m4is_r1546->m4is_g316(). 'vendor/i2sdkng/i2sdk.php';
 wpal_i2sdk_activate();
 
} if(!$m4is_x1269){
  if(defined('MEMBERIUM_DEFER_ACTIVATION_SYNC')){
return;
 
} if(function_exists('wpal_i2sdk_activate')&&function_exists('wp_generate_password')){
 if(!function_exists('wp_generate_password')){
include ABSPATH . 'wp-includes/pluggable.php';
 
} m4is_s52::m4is_a834(true );
 
}  if(!method_exists(self::$m4is_r1546->m4is_z40(), 'isVerified')){
return;
 
}
} $m4is_h16905 =get_option('memberium_extensions', []);
 $m4is_s6178 =glob(self::$m4is_r1546->m4is_v75('*/init.php' ));
 $expensive_extensions =['page-tracking', ];
  if(!empty($m4is_s6178)){
foreach ($m4is_s6178 as $extension){
$m4is_j09 =basename(dirname($extension ));
  if(!isset($m4is_s6178[$m4is_j09])){
 if(in_array($m4is_j09, $expensive_extensions)){
$m4is_h16905[$m4is_j09]=0;
 
}else{
 $m4is_h16905[$m4is_j09]=1;
 
}
}
}
} update_option('memberium_extensions', $m4is_h16905, true );
  if(!isset($m4is_x39508['server_verified'])||$m4is_x39508['server_verified']<> 1 ){
return;
 
}
} static 
function m4is_d029(){
 $m4is_m512 =get_option('memberium_cron' );
  if($m4is_m512 > 0 ){
wp_unschedule_event($m4is_m512, 'memberium_scanmakepass', 0 );
 wp_unschedule_event($m4is_m512, 'memberium/contacts/makepass_scan', 0 );
 wp_unschedule_event($m4is_m512, 'memberium_maintenance', 0 );
 wp_unschedule_event($m4is_m512, 'memberium_licensecheck', 0 );
 
} delete_option('memberium_cron' );
  $m4is_e0213 =get_option('memberium' );
  if(!empty($m4is_e0213['delete_configuration'])){
delete_option('memberium' );
 
}
} static 
function m4is_f65349(){
 self::m4is_f7360();
  self::m4is_r60();
 
}    static 
function m4is_j0752(string $m4is_a173, bool $m4is_g26713 =false ): void {
self::$m4is_y76859 .= $m4is_a173 . "\n";
 update_option('memberium/activation_log', self::$m4is_y76859, false );
 if($m4is_g26713 ){
error_log('Memberium: [info] ' . $m4is_a173 );
 
}
} static private 
function m4is_n8097(): string {
global $wpdb;
 $m4is_i6268 ='memberium_site_id';
 $m4is_c62831 =get_option($m4is_i6268, false );
 if(!$m4is_c62831 ){
$m4is_i6816 =$wpdb->dbname . '|' . $wpdb->prefix . '|' . ABSPATH . '|' . site_url();
 $m4is_c62831 =hash_hmac('sha256', $m4is_i6816, wp_salt('nonce' ));
 update_option($m4is_i6268, $m4is_c62831, 'yes');
 
}return $m4is_c62831;
 
}    static private 
function m4is_f70659(){
static $m4is_c0873;
 if(is_null($m4is_c0873 )){
global $wpdb;
 $m4is_c0873 =method_exists($wpdb, 'get_charset_collate' )? $wpdb->get_charset_collate(): '';
 
}return $m4is_c0873;
 
} static private 
function m4is_n196(){
global $wpdb;
  $m4is_k48 =['memberium_appname', ];
 foreach($m4is_k48 as $m4is_e80 ){
$wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i ", $m4is_e80 ));
 error_log('Memberium: [info] Dropping orphaned table ' . $m4is_e80 );
 self::m4is_j0752('Memberium: Dropping orphaned table ' . $m4is_e80, true );
 
}
} static private 
function m4is_m3521(){
 
} static private 
function m4is_j19(){
global $wpdb;
 self::m4is_m3521();
 require_once ABSPATH . 'wp-admin/includes/upgrade.php';
 $m4is_c0873 =self::m4is_f70659();
 $m4is_t469 =[];
 $m4is_r9062 =[];
    $m4is_k48 =[['m4is_j4156', 'm4is_g29346'], ['m4is_z13097', 'm4is_y65892'], ['m4is_z13097', 'm4is_k169'], ['m4is_p40', 'm4is_l93016'], ['m4is_p40', 'm4is_a24695'], ['m4is_s695', 'm4is_r7665'], ['m4is_v87365', 'm4is_z32'], ['m4is_v87365', 'm4is_d45621'], ['m4is_t21664', 'm4is_m64352'], ['m4is_q62395', 'm4is_s7462'], ['m4is_u36', 'm4is_p37'], ['m4is_c63', 'm4is_j293'], ['m4is_z6894', 'm4is_r19'], ['m4is_k865', 'm4is_t81'], ['m4is_k865', 'm4is_e26587'], ['m4is_r83', 'm4is_k8034'], ['m4is_r83', 'm4is_p9768'], ['m4is_d86', 'm4is_w13'], ['m4is_l5841', 'm4is_l68271'], ['m4is_j946', 'm4is_z6169'], ['m4is_u81', 'm4is_k43'], ['m4is_u81', 'm4is_i960'], ['m4is_g689', 'm4is_t59042'], ];
 foreach($m4is_k48 as $m4is_e80 ){
$m4is_r60569 =call_user_func($m4is_e80 );
 $m4is_t469[]=$m4is_r60569['table'];
 $m4is_v2613 =$wpdb->prepare("SELECT COUNT(*) FROM %i", $m4is_r60569['table']);
 $m4is_e665 =$wpdb->get_var($m4is_v2613 );
 if($m4is_e665 == 0 ){
$m4is_v2613 =$wpdb->prepare("DROP TABLE IF EXISTS %i", $m4is_r60569['table']);
 $wpdb->query("DROP TABLE IF EXISTS `{$m4is_r60569['table']
}`" );
 self::m4is_j0752('Dropping Empty table ' . $m4is_r60569['table'], true );
 
}$m4is_i6541 =dbDelta($m4is_r60569['sql']);
 if(empty($m4is_i6541 )){
self::m4is_j0752('No changes to table ' . $m4is_r60569['table'], true );
 
}else{
self::m4is_j0752('Updating table ' . $m4is_r60569['table'], true );
 foreach ($m4is_i6541 as $m4is_b18924 ){
self::m4is_j0752('Database Changelog: ' . $m4is_b18924, true );
 
}
}
}self::m4is_s58196();
 self::m4is_n196();
  update_option('memberium_tables', $m4is_t469, false );
 
}    private static 
function m4is_s58196(): void {
global $wpdb;
 $m4is_j315 ='i2sdk_dataformfields';
 $m4is_y209 =m4is_s695::m4is_w52();
  $m4is_d1836 =(int) $wpdb->get_var("SELECT COUNT(`id`) FROM `{$m4is_j315
}`" );
 $m4is_o31974 =(int) $wpdb->get_var("SELECT COUNT(`id`) FROM `{$m4is_y209
}`" );
 if($m4is_o31974 == 0 &&$m4is_d1836 > 0 ){
$m4is_v2613 ="INSERT INTO `{$m4is_y209
}` SELECT * FROM `{$m4is_j315
}`";
 $wpdb->query($m4is_v2613 );
 error_log(sprintf("Memberium: [info] Migrating %d Keap custom fields from '%s' to '%s'.", $m4is_d1836, $m4is_j315, $m4is_y209 ));
 
}m4is_s695::m4is_r2903();
 
} static private 
function m4is_r6902(): void {
if(!defined('MEMBERIUM_DEV')){
$m4is_d04266 =dirname(MEMBERIUM_HOME). '/lib/ext-dev/';
 if(file_exists($m4is_d04266)){
self::m4is_n1905($m4is_d04266);
 
}$m4is_d04266 =dirname(MEMBERIUM_HOME). '/lib7/ext-dev/';
 if(file_exists($m4is_d04266)){
self::m4is_n1905($m4is_d04266);
 
}
}
} static private 
function m4is_y967(): void {
$m4is_d93178 =['memberium2-installer/memberium2-installer.php', 'memberium-install-wizard/memberium-install-wizard.php', ];
 foreach($m4is_d93178 as $m4is_g78){
if(is_plugin_active($m4is_g78)){
deactivate_plugins($m4is_g78);
 
}
}
} static private 
function m4is_f7360(){
$m4is_m7560 =get_role('administrator');
 $m4is_m7560->remove_cap('memberium_view_user_info');
 $m4is_m7560->remove_cap('memberium_edit_user_info');
 $m4is_m7560->remove_cap('memberium_view_private_comments');
 
} static private 
function m4is_r60(): void {
$m4is_m512 =get_option('memberium_cron');
 if($m4is_m512 > 0){
$m4is_y025 =['memberium_scanmakepass', 'memberium_license_check', 'memberium_maintenance', 'memberium_maintenance12', 'memberium/contacts/makepass_scan', ];
 foreach($m4is_y025 as $m4is_b50618){
wp_clear_scheduled_hook($m4is_b50618);
 
}
}delete_option('memberium_cron');
 
} static private 
function m4is_g75480(): array {
$m4is_r37596 =self::$m4is_r1546->m4is_j498();
 $m4is_f85637 =trim(get_plugin_data(MEMBERIUM_HOME, false, false)['Version']);
 $m4is_f85637 =function_exists('get_plugin_data')? trim(get_plugin_data(MEMBERIUM_HOME, false, false)['Version']): false;
 if(empty($m4is_r37596['settings'])||!is_array($m4is_r37596['settings'])){
$m4is_r37596['settings']=[];
 
}   $m4is_d06 =['max_record_age', 'paypal_api_password', 'paypal_api_signature', 'paypal_api_username', 'paypal_api_verified', 'stripe_live', 'stripe_public_key', 'stripe_secret_key', 'stripe_verified', 'username_field', ];
 foreach ($m4is_d06 as $m4is_l9671){
unset($m4is_r37596['settings'][$m4is_l9671]);
 
}$m4is_r37596['settings']['autologin_authkeys']=empty($m4is_r37596['settings']['autologin_authkeys'])? self::m4is_x820(12): $m4is_r37596['settings']['autologin_authkeys'];
 $m4is_r37596['settings']['random_seed']=empty($m4is_r37596['settings']['random_seed'])? self::m4is_x820(16): $m4is_r37596['settings']['random_seed'];
  $m4is_a65639 =[  ];
 foreach ($m4is_a65639 as $m4is_l9671 =>$m4is_v586){
if(!isset($m4is_r37596['settings'][$m4is_l9671])){
$m4is_r37596['settings'][$m4is_l9671]=$m4is_v586;
 
}
}if(empty($m4is_r37596['infusionsoft'])){
$m4is_r37596['infusionsoft']=[];
 
} $m4is_d06 =[];
 foreach ($m4is_d06 as $m4is_l9671){
unset($m4is_r37596['infusionsoft'][$m4is_l9671]);
 
}$m4is_a65639 =[];
 foreach ($m4is_a65639 as $m4is_l9671 =>$m4is_v586){
if(empty($m4is_r37596['infusionsoft'][$m4is_l9671])){
$m4is_r37596['infusionsoft'][$m4is_l9671]=$m4is_v586;
 
}
}$m4is_r37596 =self::m4is_q37($m4is_r37596);
 $m4is_r37596 =self::m4is_e37626($m4is_r37596);
 $m4is_r37596['settings']['version']=trim(get_plugin_data(MEMBERIUM_HOME, false, false)['Version']);
 add_option('memberium', $m4is_r37596, '', 'yes');
 update_option('memberium', $m4is_r37596, 'yes');
 self::$m4is_r1546->m4is_d64918($m4is_r37596);
 return $m4is_r37596;
 
} static private 
function m4is_q37(array $m4is_r37596 =[]): array {
$m4is_d06 =[];
 $m4is_a65639 =[];
 foreach ($m4is_d06 as $m4is_l9671){
unset($m4is_r37596['sync'][$m4is_l9671]);
 
}if(empty($m4is_r37596['sync'])){
$m4is_r37596['sync']=[];
 
}$m4is_g7928 =['Email', 'FirstName', 'Groups', 'Id', 'LastName', 'LastUpdated' ];
 $m4is_a89 =m4is_c69807::m4is_f5248('Contact');
 $m4is_j108 =array_diff($m4is_a89, $m4is_g7928);
 $m4is_a65639['required_fields']['Contact']=implode(',', $m4is_g7928);
 $m4is_a65639['ignored_sync_fields']['Contact']=implode(',', $m4is_j108);
  foreach ($m4is_a65639 as $m4is_l9671 =>$m4is_v586){
if(empty($m4is_r37596['sync'][$m4is_l9671])){
$m4is_r37596['sync'][$m4is_l9671]=$m4is_v586;
 
}
}return $m4is_r37596;
 
} static private 
function m4is_e37626(array $m4is_r37596 =[]): array {
if(is_array($m4is_r37596['memberships'])){
foreach ($m4is_r37596['memberships']as $m4is_l9671 =>$m4is_v586){
if(!isset($m4is_r37596['memberships'][$m4is_l9671]['main_id'])){
$m4is_r37596['memberships'][$m4is_l9671]['main_id']=$m4is_l9671;
 
}$m4is_x40 ='Memberium ' . $m4is_v586['name'];
 $m4is_e05248 =sanitize_key('memberium_' . $m4is_v586['name']);
 $m4is_m7560 =get_role($m4is_e05248);
 if(!$m4is_m7560){
$m4is_m7560 =add_role($m4is_e05248, $m4is_x40);
 
}$m4is_m7560->add_cap('read');
 
}
} if(is_array($m4is_r37596['memberships'])){
foreach ($m4is_r37596['memberships']as $m4is_l9671 =>$m4is_c5468){
$m4is_c5468['main_id']=isset($m4is_c5468['main_id'])? $m4is_c5468['main_id']: $m4is_l9671;
 $m4is_c5468['cancel_id']=isset($m4is_c5468['cancel_id'])? $m4is_c5468['cancel_id']: 0;
 $m4is_c5468['level']=isset($m4is_c5468['level'])? $m4is_c5468['level']: 0;
 $m4is_c5468['login_page']=isset($m4is_c5468['login_page'])? $m4is_c5468['login_page']: 0;
 $m4is_c5468['first_login_page']=isset($m4is_c5468['first_login_page'])? $m4is_c5468['first_login_page']: 0;
 $m4is_c5468['logout_page']=isset($m4is_c5468['logout_page'])? $m4is_c5468['logout_page']: 0;
 $m4is_c5468['payf_id']=isset($m4is_c5468['payf_id'])? $m4is_c5468['payf_id']: 0;
 $m4is_c5468['roles']=is_array($m4is_c5468['roles'])? $m4is_c5468['roles']: [];
 $m4is_c5468['suspend_id']=isset($m4is_c5468['suspend_id'])? $m4is_c5468['suspend_id']: 0;
 $m4is_c5468['theme']=isset($m4is_c5468['theme'])? $m4is_c5468['theme']: '';
 $m4is_c5468['login_redirect_priority']=isset($m4is_c5468['login_redirect_priority'])? $m4is_c5468['login_redirect_priority']: $m4is_c5468['level'];
 $m4is_c5468['payf_homepage']=isset($m4is_c5468['payf_homepage'])? $m4is_c5468['payf_homepage']: 0;
 $m4is_c5468['susp_homepage']=isset($m4is_c5468['susp_homepage'])? $m4is_c5468['susp_homepage']: 0;
 $m4is_c5468['canc_homepage']=isset($m4is_c5468['canc_homepage'])? $m4is_c5468['canc_homepage']: 0;
 $m4is_c5468['dynamic_menus']=isset($m4is_c5468['dynamic_menus'])? $m4is_c5468['dynamic_menus']: 0;
 $m4is_r37596['memberships'][$m4is_l9671]=$m4is_c5468;
 
}
}return $m4is_r37596;
 
} static private 
function m4is_n1905($m4is_m06 ): void {
if(is_dir($m4is_m06 )){
$m4is_i61 =glob($m4is_m06 . '*', GLOB_MARK );
 foreach($m4is_i61 as $m4is_k86914 ){
self::m4is_n1905($m4is_k86914 );
 
}rmdir($m4is_m06 );
 
}elseif(is_file($m4is_m06 )){
unlink($m4is_m06 );
 
}
} static private 
function m4is_p04631(){
global $wpdb;
 $m4is_v2613 ="SELECT SUBSTRING(`option_name`, 12) as `transient` FROM `{$wpdb->options
}` WHERE `option_name` like '%_transient_memberium%';";
 $m4is_m615 =$wpdb->get_col($m4is_v2613);
 foreach($m4is_m615 as $m4is_g91703){
delete_transient($m4is_g91703);
 
}
} static private 
function m4is_v14567(){
global $wpdb;
 $m4is_v2613 ='DELETE FROM `' . $wpdb->options . '` WHERE `option_name` LIKE "%telemetry%" ';
 $wpdb->query($m4is_v2613);
 
} static private 
function m4is_z06(){

} static private 
function m4is_i437(){
$m4is_m7560 =get_role('administrator');
 if($m4is_m7560){
$m4is_m7560->add_cap('memberium_view_user_info');
 $m4is_m7560->add_cap('memberium_edit_user_info');
 $m4is_m7560->add_cap('memberium_view_private_comments');
 
}
} static private 
function m4is_x820($m4is_x1486 =8 ){
if(function_exists('wp_generate_password' )){
return wp_generate_password($m4is_x1486, false, false );
 
}else{
return substr(md5(wp_salt('auth' ). wp_salt('logged_in' ). wp_salt('secure_auth' ). microtime(). mt_rand(0, 999999999 )), 0, $m4is_x1486 );
 
}
} private static 
function m4is_q90(): array {
global $wpdb;
 $m4is_t613 =$wpdb->get_col($wpdb->prepare("SELECT blog_id FROM %i", $wpdb->blogs ));
 return $m4is_t613;
 
} 
}

