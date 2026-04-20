<?php

/**
 * Proprietary Software - All Rights Reserved
 *
 * This file is part of the Memberium plugin, which is proprietary software developed by Web Power and Light.
 * Unauthorized copying, distribution, or modification of this file, via any medium, is strictly prohibited.
 *
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 *
 * For licensing information, please contact Web Power and Light.
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_i0574 {
 static private $m4is_g6152 ='production';
 static private $m4is_y96480 ='<a href="https://memberium.com/support/" target="_blank">contact support</a>';
 static private $m4is_r1546;
  private 
function __construct(){

} static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 
}    static 
function m4is_t64(array $m4is_k617 ): array {
self::$m4is_g6152 =defined('WP_ENVIRONMENT_TYPE' )? constant('WP_ENVIRONMENT_TYPE' ): self::$m4is_g6152;
 self::$m4is_g6152 =function_exists('WP_ENVIRONMENT_TYPE' )? wp_get_environment_type(): self::$m4is_g6152;
 $m4is_z9157 =wp_get_update_data();
 $m4is_v32 =php_uname('n' );
 $m4is_d3192 =defined('I2SDK_VERSION' )? constant('I2SDK_VERSION' ): 'Unknown';
 $m4is_k698 =defined('MEMBERIUM_SKU' )? strtoupper(constant('MEMBERIUM_SKU' )): 'Unknown';
 $m4is_g41650 =self::$m4is_r1546->m4is_q80();
 $m4is_w390 =self::$m4is_r1546->m4is_w45();
 $m4is_b96781 =property_exists('PhpXmlRpc\PhpXmlRpc', 'xmlrpcVersion' )? PhpXmlRpc\PhpXmlRpc::$xmlrpcVersion : 'Unknown';
 $m4is_v1568 =m4is_s52::m4is_v91686(get_option(self::$m4is_r1546->m4is_e96(), '' ));
 $m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 $m4is_m96240 =count(self::$m4is_r1546->m4is_j498('memberships' ));
 $m4is_j5612 =self::$m4is_r1546->m4is_s0578();
 $m4is_k8764 =self::m4is_z27593();
 $m4is_l42369 =self::m4is_f86();
 $m4is_y09652 =self::m4is_w43106();
 $m4is_x64 =count(array_filter(explode(',', self::$m4is_r1546->m4is_j498('ignore_contact_fields' )['settings'])));
 $m4is_q30 =self::$m4is_r1546->m4is_j498('settings', 'autoupdate' );
 $m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field' );
 $m4is_p96063 =self::$m4is_r1546->m4is_j498('settings', 'local_auth_only' )? 'Yes' : 'No';
 $m4is_k6751 =self::$m4is_r1546->m4is_j498('settings', 'require_membership' )? 'Yes' : 'No';
 $m4is_j75 =self::$m4is_r1546->m4is_j498('settings', 'httppost_log' )? 'Yes' : 'No';
 if(isset($m4is_k617['wp-core']['fields']['version'])){
$m4is_k617['wp-core']['fields']['version']['value']=$m4is_j5612;
 $m4is_k617['wp-core']['fields']['version']['debug']=$m4is_j5612;
 
}if(is_array($m4is_v1568 )){
$m4is_v2790 =['Active' =>empty($m4is_v1568['active'])? 'No' : 'Yes', 'Max Users' =>empty($m4is_v1568['max_users'])? 0 : $m4is_v1568['max_users'], 'Hostname' =>isset($m4is_v1568['hostname'])? $m4is_v1568['hostname']: 'Unknown', 'Owner Email' =>isset($m4is_v1568['owner_email'])? $m4is_v1568['owner_email']: 'Unknown', 'License Name' =>isset($m4is_v1568['license_name'])? $m4is_v1568['license_name']: 'Unknown', 'Tags' =>isset($m4is_v1568['tags'])? $m4is_v1568['tags']: 'None', ];
 
}else{
$m4is_v2790 ='None';
 
}$m4is_f29137 =['wp-core' =>$m4is_k617['wp-core'], 'wpal-memberium' =>['label' =>'Memberium', 'fields' =>['sku' =>['label' =>'SKU', 'value' =>$m4is_k698, 'private' =>false, ], 'version' =>['label' =>'Installed Version', 'value' =>$m4is_w390, 'private' =>false, ], 'license' =>['label' =>'License', 'value' =>$m4is_v2790, 'private' =>false, ], 'autoupdate' =>['label' =>'Autoupdate', 'value' =>empty($m4is_q30)? 'No' : 'Yes', 'private' =>false, ], 'i2sdk/version' =>['label' =>'Keap Connection Version', 'value' =>$m4is_d3192, 'private' =>false, ], 'i2sdk/app' =>['label' =>'Keap App Name', 'value' =>$m4is_r9613, 'private' =>false, ], 'i2sdk/xmlrpcversion' =>['label' =>'XML/RPC Library Version', 'value' =>$m4is_b96781, 'private' =>false, ], 'hostname' =>['label' =>'Hostname', 'value' =>$m4is_v32, 'private' =>false, ], 'proxies' =>['label' =>'Proxies', 'value' =>empty($m4is_k8764['data'])? 'None' : $m4is_k8764['data'], 'private' =>false, ], 'ssl' =>['label' =>'SSL', 'value' =>is_ssl()? 'Yes' : 'No', 'private' =>false, ], 'php_version' =>['label' =>'PHP Version', 'value' =>phpversion(), 'private' =>false, ], 'wp_version' =>['label' =>'WordPress Version', 'value' =>self::$m4is_r1546->m4is_s0578(), 'private' =>false, ], 'plugins/caching' =>['label' =>'Caching Plugins', 'value' =>empty($m4is_l42369['data'])? 'None' : $m4is_l42369['data'], 'private' =>false, ], 'plugins/membership' =>['label' =>'Membership Plugins', 'value' =>empty($m4is_y09652['data'])? 'None' : $m4is_y09652['data'], 'private' =>false, ], 'updates/plugins' =>['label' =>'Outdated Plugins', 'value' =>empty($m4is_z9157['plugins'])? 0 : $m4is_z9157['plugins'], 'private' =>false, ], 'updates/themes' =>['label' =>'Outdated Themes', 'value' =>empty($m4is_z9157['themes'])? 0 : $m4is_z9157['themes'], 'private' =>false, ], 'updates/core' =>['label' =>'Outdated WordPress Core', 'value' =>empty($m4is_z9157['wordpress'])? 0 : $m4is_z9157['wordpress'], 'private' =>false, ], 'object_cache' =>['label' =>'Object Cache', 'value' =>wp_using_ext_object_cache()? 'Yes' : 'No', 'private' =>false, ], 'users' =>['label' =>'Users', 'value' =>m4is_s52::m4is_s326(), 'private' =>false, ], 'tags/count' =>['label' =>'Tags', 'value' =>m4is_k865::m4is_q136(), 'private' =>false, ], 'categories/count' =>['label' =>'Tag Categories', 'value' =>m4is_z6894::m4is_o53614(), 'private' =>false, ], 'fields/custom/count' =>['label' =>'Custom Fields', 'value' =>m4is_s695::m4is_r56126(), 'private' =>false, ], 'fields/synced/count' =>['label' =>'Synced Contact Fields', 'value' =>count(m4is_c69807::m4is_f5248('Contact', true)), 'private' =>false, ], 'fields/blocked/count' =>['label' =>'Blocked Contact Fields', 'value' =>$m4is_x64, 'private' =>false, ], 'fields/password' =>['label' =>'Password Field', 'value' =>$m4is_r6234, 'private' =>false, ], 'settings/secure_password' =>['label' =>'Secure Passwords', 'value' =>$m4is_p96063, 'private' =>false, ], 'settings/require_membership' =>['label' =>'Require Membership', 'value' =>$m4is_k6751, 'private' =>false, ], 'settings/httppost_log' =>['label' =>'HTTP POST Log', 'value' =>$m4is_j75, 'private' =>false, ], 'actionsets/count' =>['label' =>'Actionsets', 'value' =>m4is_j4156::m4is_c289(), 'private' =>false, ], 'products/count' =>['label' =>'Products', 'value' =>self::m4is_b62149(), 'private' =>false, ], 'invoices/count' =>['label' =>'Invoices', 'value' =>self::m4is_d02196(), 'private' =>false, ], 'merchant_id' =>['label' =>'Merchant ID', 'value' =>self::$m4is_r1546->m4is_j498('settings', 'merchant_account_id'), 'private' =>false, ], 'memberships/count' =>['label' =>'Membership Levels', 'value' =>$m4is_m96240, 'private' =>false, ],               'shortcodes/nesting' =>['label' =>'Shortcode Nesting Levels', 'value' =>constant('MEMBERIUM_NESTING_LEVELS' ), 'private' =>false, ], ], ]];
 return array_merge($m4is_f29137, $m4is_k617);
 
}static 
function m4is_k60567(array $m4is_m16832 ): array {
self::$m4is_g6152 =defined('WP_ENVIRONMENT_TYPE' )? constant('WP_ENVIRONMENT_TYPE' ): self::$m4is_g6152;
 self::$m4is_g6152 =function_exists('WP_ENVIRONMENT_TYPE' )? wp_get_environment_type(): self::$m4is_g6152;
 if(self::$m4is_g6152 === 'staging' ||self::$m4is_g6152 == 'local' ){
unset($m4is_m16832['direct']['wpal/server/phpinfo']);
  
}$m4is_m16832['direct']['wpal/memberium/elementor/caching']=['label' =>'Elementor Caching', 'test' =>[__CLASS__, 'm4is_s94526'], ];
  $m4is_m16832['direct']['wpal/memberium/wordpress/securitykeys']=['label' =>'Security Keys', 'test' =>[__CLASS__, 'm4is_u42793'], ];
 $m4is_m16832['direct']['wpal/memberium/security/plaintext_password']=['label' =>'Plaintext Password', 'test' =>[__CLASS__, 'm4is_d73'], ];
 $m4is_m16832['direct']['wpal/memberium/performance/memory']=['label' =>'Memory Allocation', 'test' =>[__CLASS__, 'm4is_d436'], ];
 $m4is_m16832['direct']['wpal/memberium/security/default_role']=['label' =>'Default Role', 'test' =>[__CLASS__, 'm4is_c480'], ];
 $m4is_m16832['direct']['wpal/memberium/security/caching']=['label' =>'Caching Plugins', 'test' =>[__CLASS__, 'm4is_v14068'], ];
 $m4is_m16832['direct']['wpal/memberium/security/cloudflare']=['label' =>'Cloudflare', 'test' =>[__CLASS__, 'm4is_z27593'], ];
 $m4is_m16832['direct']['wpal/memberium/tables']=['label' =>'Database Tables', 'test' =>[__CLASS__, 'm4is_k54'], ];
 $m4is_m16832['direct']['wpal/memberium/flywheel']=['label' =>'FlyWheel Caching', 'test' =>[__CLASS__, 'm4is_a6817'], ];
 $m4is_m16832['direct']['wpal/memberiums/security/licenseserver']=['label' =>'Memberium License Server', 'test' =>[__CLASS__, 'm4is_h06765'], ];
 $m4is_m16832['direct']['wpal/memberium/network_activation']=['label' =>'Memberium Network Activation', 'test' =>[__CLASS__, 'm4is_q61938'], ];
 $m4is_m16832['direct']['wpal/memberium/memberships']=['label' =>'Membership Setup', 'test' =>[__CLASS__, 'm4is_l38'], ];
 $m4is_m16832['direct']['wpal/memberium/contact/contactnotes']=['label' =>'ContactNotes Sync', 'test' =>[__CLASS__, 'm4is_t097'], ];
 $m4is_m16832['direct']['wpal/memberium/tags']=['label' =>'CRM Tag Sync', 'test' =>[__CLASS__, 'm4is_r532'], ];
 $m4is_m16832['direct']['wpal/memberium/installer/active']=['label' =>'Memberium Installer Plugin', 'test' =>[__CLASS__, 'm4is_f854'], ];
 $m4is_m16832['direct']['wpal/memberium/file_permissions']=['label' =>'Memberium File Permissions', 'test' =>[__CLASS__, 'm4is_e05696'], ];
 $m4is_m16832['direct']['wpal/i2sdk/installed']=['label' =>'i2SDK Plugin Installed', 'test' =>[__CLASS__, 'm4is_i015'], ];
  $m4is_m16832['direct']['wpal/memberium/version']=['label' =>'Memberium Version', 'test' =>[__CLASS__, 'm4is_r01'], ];
 $m4is_m16832['direct']['wpal/wordpress/admin_is_admin']=['label' =>'Admin User should be renamed', 'test' =>[__CLASS__, 'm4is_g845'], ];
 $m4is_m16832['direct']['wpal/memberium/support_account']=['label' =>'Memberium Support users should be removed', 'test' =>[__CLASS__, 'm4is_g1967'], ];
 $m4is_m16832['direct']['wpal/wordpress/missing_autoincrement']=['label' =>'WordPress Database Corruption', 'test' =>[__CLASS__, 'm4is_v13549'], ];
 $m4is_m16832['direct']['wpal/server/phpinfo']=['label' =>'PHPinfo() Security Issue', 'test' =>[__CLASS__, 'm4is_s92'], ];
 $m4is_m16832['direct']['wpal/wordpress/open_registration']=['label' =>'Open Registration', 'test' =>[__CLASS__, 'm4is_u46'], ];
 $m4is_m16832['direct']['wpal/wordpress/object_cache']=['label' =>'Object Caching', 'test' =>[__CLASS__, 'm4is_s130'], ];
 $m4is_m16832['direct']['wpal/wordpress/administrator_email']=['label' =>'Site Administrator Email', 'test' =>[__CLASS__, 'm4is_t173'], ];
 $m4is_m16832['direct']['wpal/wordpress/goaddy/managed']=['label' =>'GoDaddy Managed Hosting', 'test' =>[__CLASS__, 'm4is_l1566'], ];
 $m4is_m16832['direct']['wpal/wordpress/plugins/caching']=['label' =>'Caching Plugins', 'test' =>[__CLASS__, 'm4is_f86'], ];
 $m4is_m16832['direct']['wpal/wordpress/plugins/membership']=['label' =>'Membership Plugins', 'test' =>[__CLASS__, 'm4is_w43106'], ];
 $m4is_m16832['direct']['wpal/wordpress/plugins/buddypress_private_network']=['label' =>'Possible BuddyPress Site Security Conflict', 'test' =>[__CLASS__, 'm4is_v17368'], ];
 $m4is_m16832['direct']['wpal/memberium/password/blocked']=['label' =>'Memberium Password Sync', 'test' =>[__CLASS__, 'm4is_u64'], ];
 $m4is_m16832['direct'][]=['label' =>'wpal/woocommerce/guest_checkout', 'test' =>[__CLASS__, 'm4is_y05614'], ];
 return $m4is_m16832;
 
}   static 
function m4is_b62149(): int {
global $wpdb;
 $m4is_r9613 =self::$m4is_r1546->m4is_i76('appname');
 $m4is_m7426 =m4is_v87365::m4is_a36489();
 $m4is_v2613 ="SELECT count(`id`) from `{$m4is_m7426
}` WHERE `appname` = '{$m4is_r9613
}'";
 return (int) $wpdb->get_var($m4is_v2613);
 
}static 
function m4is_d02196(): int {
global $wpdb;
 $m4is_r9613 =self::$m4is_r1546->m4is_i76('appname');
 $m4is_m7426 =m4is_v87365::m4is_p63285();
 $m4is_v2613 ="SELECT count(`id`) from `{$m4is_m7426
}` WHERE `appname` = '{$m4is_r9613
}'";
 return (int) $wpdb->get_var($m4is_v2613);
 
}static 
function m4is_o794(): string {
$m4is_h540 ='Unknown';
 $m4is_h540 =empty($_SERVER['PRESSIDIUM_INSTALL_NAME'])? $m4is_h540 : 'Pressidium';
 $m4is_h540 =empty($_SERVER['PRESSIDIUM_ACCOUNT_NAME'])? $m4is_h540 : 'Pressidium';
 $m4is_h540 =empty($_SERVER['PRESSIDIUM_ENVIRONMENT'])? $m4is_h540 : 'Pressidium';
 return $m4is_h540;
 
} static 
function m4is_v17368(): array {
$m4is_u6591 =[];
 if(function_exists('bp_is_active')||function_exists('buddypress')){
$m4is_r216 =get_option('bp-enable-private-network', 1);
 if($m4is_r216 == 0){
$m4is_u6591 =['label' =>'BuddyPress Private Networking', 'status' =>'recommended', 'badge' =>['label' =>__('Security'), 'color' =>'orange', ], 'description' =>"<p>Memberium has detected that you're using BuddyPress/BuddyBoss's private network feature.</p>", 'actions' =>"<p>This feature may cause problems with page access controls.  We recommmend disabling this feature and letting Memberium manage site access.</p>", 'test' =>'wpal_wordpress_plugins_buddypress_private_network', ];
 
}
}return $m4is_u6591;
 
}static 
function m4is_f86(): array {
if(!function_exists('get_plugins')){
require_once ABSPATH . 'wp-admin/includes/plugin.php';
 
}$m4is_u6591 =[];
 $m4is_x536 =[];
 $m4is_i6358 =array_merge(get_plugins(), get_mu_plugins());
 if(!is_array($m4is_i6358 )||empty ($m4is_i6358 )){
$m4is_u6591 =['label' =>'No Plugins Found', 'status' =>'critical', 'badge' =>['label' =>__('Security'), 'color' =>'red', ], 'description' =>"<p>No installed plugins were found when checking for caching plugin conflicts.</p>", 'test' =>'wpal_wordpress_plugins_caching', 'data' =>'', ];
 return $m4is_u6591;
 
}$m4is_z57318 =['endurance-page-cache.php', 'breeze/breeze.php', 'cache-enabler/cache-enabler.php', 'cachify/cachify.php', 'comet-cache/comet-cache.php', 'ezcache/ezcache.php', 'hummingbird-performance/wp-hummingbird.php', 'litespeed-cache/litespeed-cache.php', 'nginx-cache/nginx-cache.php', 'nginx-helper/nginx-helper.php', 'preload-fullpage-cache/preload-fullpage-cache.php', 'purge-varnish/class_purge_varnish.php', 'rapid-cache/rapid-cache.php', 'sg-cachepress/sg-cachepress.php', 'simple-cache/simple-cache.php', 'swift-performance-lite/performance.php', 'vcaching/vcaching.php', 'widget-output-cache/widget-output-cache.php', 'wp-cloudflare-page-cache/wp-cloudflare-super-page-cache.php', 'wp-fastest-cache/wpFastestCache.php', 'wp-super-cache/wp-cache.php', 'wpcacheon/wp-cache-on.php', ];
 foreach($m4is_z57318 as $m4is_a1052 ){
if(array_key_exists($m4is_a1052, $m4is_i6358)){
$m4is_g78 =$m4is_i6358[$m4is_a1052];
 $m4is_k85713 =isset($m4is_g78['Name'])? $m4is_g78['Name']: $m4is_n65;
 $m4is_f85637 =isset($m4is_g78['Version'])? $m4is_g78['Version']: '';
 $m4is_x536[]=$m4is_k85713 . ' v' . $m4is_f85637;
 
}
}if(!empty($m4is_x536)){
$m4is_u6591 =['label' =>'Caching Plugins Found', 'status' =>'recommended', 'badge' =>['label' =>__('Security'), 'color' =>'red', ], 'description' =>"<p>The following caching plugins were found installed on your system.  " . "Improper configuration of these plugins can cause problems including access controls being broken, data corruption, and the wrong information displayed to the wrong user.</p>", 'actions' =>'<p>We recommend deactivating and removing these plugins.  ' . 'If the plugin is required, please ensure it is not caching logged in users or pages with secure forms.</p>', 'test' =>'wpal_wordpress_plugins_caching', 'data' =>$m4is_x536, ];
 foreach($m4is_x536 as $m4is_n91){
$m4is_u6591['description'].= $m4is_n91 . '<br />';
 
}
}return $m4is_u6591;
 
}static 
function m4is_t097(): array {
$m4is_n72966 =self::$m4is_r1546->m4is_j498('settings', 'ignore_contact_fields' );
 $m4is_n72966 =array_filter (explode(',', $m4is_n72966 ));
 $m4is_p7504 =count($m4is_n72966 );
 $m4is_x01966 =get_admin_url(null, 'admin.php?page=memberium-sync-options&tab=contactfields' );
 $m4is_u6591 =['label' =>'Contact Field Sync Options', 'status' =>'good', 'badge' =>['label' =>__('Performance'), 'color' =>'green', ], 'description' =>'', 'actions' =>'', 'test' =>'wpal_memberium_contact_contactnotes', ];
 if(empty($m4is_n72966)){
$m4is_u6591['status']='recommended';
 $m4is_u6591['badge']['color']='orange';
 $m4is_u6591['description']='<p>Your system is set to sync all contact fields.  Most sites do not need all fields, and can save memory, disk space, and increase performance by only syncing the needed contact fields from Keap.</p>';
 $m4is_u6591['description'].= '<p>At a minimum, the <strong>ContactNotes</strong> field should be blocked from syncing.</p>';
 $m4is_u6591['actions']="<p><a href='{$m4is_x01966
}'>Click Here</a> to choose which fields to block from being synced.</p>";
 
}else{
if(!in_array('ContactNotes', $m4is_n72966)){
$m4is_u6591['status']='recommended';
 $m4is_u6591['badge']['color']='orange';
 $m4is_u6591['description']='<p>Your Memberium system is set to sync the <strong>ContactNotes</strong> field.  The <strong>ContactNotes</strong> field is almost never used in membership sites, however it can also be very large causing performance and memory problems.</p>';
 $m4is_u6591['actions']="<p><a href='{$m4is_x01966
}'>Click Here</a> to choose which fields to block from being synced.</p>";
 
}
}if($m4is_u6591['status']== 'good'){
$m4is_u6591['description']='<p>Your contact field sync options are setup correctly.</p>';
 
}return $m4is_u6591;
 
}static 
function m4is_v13549(): array {
global $wpdb;
 $m4is_d5472 =[];
 $m4is_e0234 =[];
 $m4is_k48 =[$wpdb->commentmeta =>'meta_id', $wpdb->comments =>'comment_ID', $wpdb->postmeta =>'meta_id', $wpdb->posts =>'ID', $wpdb->usermeta =>'umeta_id', $wpdb->users =>'ID', ];
 foreach($m4is_k48 as $m4is_e80 =>$m4is_a950){
$m4is_v2613 ="SELECT count(`{$m4is_a950
}`) FROM `{$m4is_e80
}` WHERE `{$m4is_a950
}` = 0";
 $m4is_h973 =(int) $wpdb->get_var($m4is_v2613);
 if($m4is_h973){
$m4is_e0234[$m4is_e80]=$m4is_a950;
 
}
}if(count($m4is_e0234)){
$m4is_d5472 =['label' =>'WordPress Database AutoIncrement Corruption', 'status' =>'critical', 'badge' =>['label' =>__('Database'), 'color' =>'red', ], 'description' =>"<p>Your WordPress database AutoIncrement columns are corrupted or missing, and is causing data loss.</p><p>The following tables are affected:</p>", 'actions' =>'<p>We recommend you contact your hosting service and ask them to repair the damaged columns and indexes.</p>', 'test' =>'wpal_wordpress_autoincrement', ];
 foreach($m4is_e0234 as $m4is_e80 =>$m4is_a950){
$m4is_d5472['description'].= "Table: '{$m4is_e80
}', on column '{$m4is_a950
}'<br />";
 
}
}return $m4is_d5472;
 
}static 
function m4is_c480(): array {
$m4is_y1662 =get_option('default_role' );
 $m4is_m7560 =get_role($m4is_y1662 );
 $m4is_v35176 =$m4is_m7560->has_cap('read' );
 $m4is_w53 =$m4is_m7560->has_cap('manage_options' );
 $m4is_u6591 =null;
 $m4is_y1662 =ucwords($m4is_y1662 );
 $m4is_u6591 =['label' =>'Default Role Permissions', 'status' =>'good', 'badge' =>['label' =>__('Security'), 'color' =>'green' ], 'description' =>'', 'actions' =>'', 'test' =>'wpal_default_role', ];
 if($m4is_w53){
$m4is_u6591['badge']['color']='red';
 $m4is_u6591['status']='critical';
 $m4is_u6591['description'].= "<p>Your default role ({$m4is_y1662
}) appears to be mis-configured as an admin user.  The default role should have minimum access.</p>";
 
}if(!$m4is_v35176){
$m4is_u6591['badge']['color']='red';
 $m4is_u6591['status']='critical';
 $m4is_u6591['description'].= "<p>Your default role ({$m4is_y1662
}) appears to be mis-configured without the 'Read' capability.  This capability should be added to the role.</p>";
 
}if($m4is_u6591['status']== 'good'){
$m4is_u6591['description']='Your default role appears to be properly configured.';
 
}else{
$m4is_u6591['actions']='<p>Please contact <a href="https://memberium.com/support/" target="_blank">Memberium Support</a> for assistance and include a copy of this message.</p>';
 
}return $m4is_u6591;
 
}static 
function m4is_s94526(): array {
if(!defined('ELEMENTOR_VERSION' )){
return [];
 
}$m4is_t265 =false;
 $m4is_u450 =['elementor_experiment-e_element_cache', 'elementor_element_cache', ];
 foreach($m4is_u450 as $m4is_l9671 ){
if(get_option($m4is_l9671, '' )== 'active' ){
$m4is_t265 =true;
 break;
 
}
}if(!$m4is_t265 ){
return [];
 
}$m4is_u6591 =['label' =>'Elementor Element Caching', 'status' =>'critical', 'badge' =>['label' =>__('Security'), 'color' =>'red' ], 'description' =>'Elementor element caching is enabled in your Elementor plugin.  This feauture is designed for static sites and may cause personal data to be displayed to the wrong user.', 'actions' =>'Go to Elementor settings and disable element caching.', 'test' =>'wpal_elementor_caching', ];
 return $m4is_u6591;
 
}static 
function m4is_i015(): array {
$m4is_f6067 =get_option('active_plugins');
 $m4is_t265 =false;
 $m4is_u6591 =[];
 foreach($m4is_f6067 as $m4is_g78){
if(stripos($m4is_g78, 'i2sdk')!== false){
$m4is_t265 =true;
 break;
 
}
}if($m4is_t265){
$m4is_p95312 =get_admin_url(null, 'plugins.php?s=i2sdk&plugin_status=all');
 $m4is_u6591 =['label' =>'Deactivate External i2SDK Plugin', 'status' =>'critical', 'badge' =>['label' =>__('Security'), 'color' =>'red', ], 'description' =>'<p>Memberium has detected that you have the old version of the i2SDK plugin installed, and activated.  This version is no longer maintained, and is missing performance improvements, features and bug fixes.</p>', 'actions' =>"<p>We recommend <a href='{$m4is_p95312
}'>deactivating</a> the i2SDK plugin to avoid conflicts.</p>", 'test' =>'wpal_memberium_external_i2sdk', ];
 
}return $m4is_u6591;
 
}static 
function m4is_e05696(): array {
$m4is_w2973 =new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::$m4is_r1546->m4is_g316()));
 $m4is_v309 =true;
 $m4is_u6591 =[];
 foreach ($m4is_w2973 as $m4is_k86914){
$m4is_g34786 =$m4is_k86914->getPathname();
 if(!stripos($m4is_g34786, '.git')){
if(!is_writeable($m4is_g34786)){
$m4is_v309 =false;
 break;
 
}
}
}if(!$m4is_v309){
$m4is_u6591 =['label' =>'Memberium Plugin Folder is not Writeable', 'status' =>'recommended', 'badge' =>['label' =>__('Updates'), 'color' =>'orange', ], 'description' =>'<p>One or more files in your Memberium plugin folder are not writeable.</p><p>This may prevent updates or debugging builds from working.</p>', 'actions' =>'<p>Reset the permissions on the Memberium plugin folder to be writeable.</p>', 'test' =>'m4is_e05696', ];
 
}return $m4is_u6591;
 
}static 
function m4is_a6817(): array {
$m4is_i1656 =isset($_SERVER['SERVER_SOFTWARE'])? $_SERVER['SERVER_SOFTWARE']: '';
 $m4is_u6591 =[];
 if(stripos($m4is_i1656, 'flywheel')!== false){
$m4is_u6591 =['label' =>'FlyWheel Hosting detected', 'status' =>'critical', 'badge' =>['label' =>__('Performance'), 'color' =>'orange' ], 'description' =>'FlyWheel hosting was detected.  Fly/Wheel uses aggressive caching which may display private member info to other members, break autologins, and other problems.  Turning on Dev Mode will often fix these issues.', 'actions' =>'', 'test' =>'wpal_healthcheck_flywheel', ];
 
}return $m4is_u6591;
 
}static 
function m4is_l1566(): array {
$m4is_d5472 =[];
 $m4is_t265 =false;
 $m4is_r6438 =defined('GD_PLAN_NAME' )? strtolower (constant('GD_PLAN_NAME' )): '';
 if(strpos($m4is_r6438, 'managed' )!== false){
$m4is_d5472 =['label' =>'GoDaddy Managed WordPress Hosting', 'status' =>'critical', 'badge' =>['label' =>__('Security'), 'color' =>'red' ], 'description' =>'<p>GoDaddy Managed WordPress hosting was detected.   GoDaddy Managed WordPress is incompatible with Memberium and is not a supported hosting plan.  This will cause Memberium to not work properly.</p>', 'actions' =>'<p>We recommend switching to another hosting provider, or to GoDaddy cPanel Linux hosting.</p>', 'test' =>'wpal_hosting_godaddy_managed', ];
 
}return $m4is_d5472;
 
}static 
function m4is_h06765(): array {
 $m4is_u6591 =['label' =>'Can Communicate with Memberium License Server', 'status' =>'good', 'badge' =>['label' =>__('Memberium' ), 'color' =>'green'], 'description' =>'Your connection to the license server appears to be working properly.', 'actions' =>'', 'test' =>'wpal_healthcheck_hostblocking', ];
 if(defined('WP_HTTP_BLOCK_EXTERNAL')&&constant('WP_HTTP_BLOCK_EXTERNAL')== 1){
$m4is_x60591 =defined('WP_ACCESSIBLE_HOSTS')? strtolower(constant('WP_ACCESSIBLE_HOSTS')): '';
 $m4is_p89401 =array_filter(explode(',', $m4is_x60591));
 $m4is_p89401 =array_filter($m4is_p89401);
 $m4is_r9613 =self::$m4is_r1546->m4is_i76('appname');
 $m4is_w0275 =['licenseserver.webpowerandlight.com', "{$m4is_r9613
}.infusionsoft.com" ];
 foreach($m4is_w0275 as $k =>$m4is_k72){
if(in_array($m4is_k72, $m4is_p89401)){
unset($m4is_w0275[$k]);
 
}
}if(!empty($m4is_w0275)){
$m4is_k87416 =trim($m4is_x60591 . "," . implode(',', $m4is_w0275), ',');
 $m4is_s6347 ='notice notice-error';
 $m4is_a173 ='<h3>External Hosts Blocked</h3>' . '<p>Memberium has detected that you are blocking access to external hosts, through your wp-config.php file.' . 'You will either need to remove the <strong>WP_HTTP_BLOCK_EXTERNAL</strong> setting, or add our hosts to the <strong>WP_ACCESSIBLE_HOSTS</strong> setting.' . 'Leaving this problem unaddressed will cause your plugin to stop working.</p>' . "<p style='font-family:\"courier new\",monospace;font-size:120%;'>define('WP_ACCESSIBLE_HOSTS', '" . $m4is_k87416 . "');</p>" . '<p>If you would like assistance, please contact <a target="_blank" href="https://memberium.com/support/">Memberium Support.</a></p>';
 $m4is_u6591['label']='Memberium License Server is blocked';
 $m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description']=$m4is_a173;
 
}
}return $m4is_u6591;
 
}static 
function m4is_f854(): array {
  $m4is_f6067 =get_option('active_plugins');
 $m4is_m621 =get_plugins();
 $m4is_u6591 =[];
 $m4is_m3169 =[];
 $m4is_i6358 =[];
 $m4is_p95312 =get_admin_url(null, 'plugins.php?s=memberium install&plugin_status=all');
 $m4is_u6591 =['label' =>'Remove Memberium Installer Plugin', 'status' =>'good', 'badge' =>['label' =>__('Security'), 'color' =>'green', ], 'description' =>'', 'actions' =>'', 'test' =>'wpal_memberium_installer', ];
 foreach($m4is_m621 as $m4is_g78 =>$m4is_l62046){
if(stripos($m4is_g78, 'memberium')!== false &&(stripos($m4is_g78, 'install')!== false ||stripos($m4is_g78, 'wizard')!== false)){
$m4is_m3169[]=$m4is_g78;
 $m4is_u6591['status']='recommended';
 $m4is_u6591['badge']['color']='orange';
 
}
}foreach($m4is_f6067 as $m4is_g78){
if(stripos($m4is_g78, 'memberium')!== false &&(stripos($m4is_g78, 'install')!== false ||stripos($m4is_g78, 'wizard')!== false)){
$m4is_m3169[]=$m4is_g78;
 $m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 
}
}if(!empty($m4is_m3169)){
foreach($m4is_m3169 as $m4is_g78){
if(isset($m4is_m621[$m4is_g78])){
$m4is_i6358[]="<em>{$m4is_m621[$m4is_g78]['Name']
}</em> by {$m4is_m621[$m4is_g78]['Author']
}";
 
}
}
}if(!empty($m4is_i6358)){
$m4is_u6591['description']='<p>The Memberium installation wizard plugin was found in your system.  This plugin is no longer needed once Memberium is installed, and will only slow down your system.</p>';
 $m4is_u6591['actions']='<p>We recommend <a href="' . $m4is_p95312 . '">deactivating and removing</a> the following plugins:</p>';
 foreach($m4is_i6358 as $m4is_g78){
$m4is_u6591['actions'].= "<p>{$m4is_g78
}</p>";
 
}
}if($m4is_u6591['status']!== 'good'){
return $m4is_u6591;
 
}return [];
 
}static 
function m4is_t173(): array {
$m4is_u6591 =[];
 if(self::$m4is_r1546->m4is_e58704()=== 'production' ){
$m4is_d039 =false;
 $m4is_f4930 =strtolower(trim(get_bloginfo('admin_email' )));
 $m4is_u6184 =['.secureserver.net', '.sg-host.com', '.wpengine.com', 'liquidwebsites.com', '@cloudways', '@example', '@flywheel.local', '@localhost', '@entre.cloud', '@admin.com', '@admin.com', ];
 if(empty($m4is_f4930)){
$m4is_d039 =true;
 
}foreach($m4is_u6184 as $m4is_c8657){
if(stripos($m4is_f4930, $m4is_c8657)!== false){
$m4is_d039 =true;
 break;
 
}
}if($m4is_d039){
$m4is_x01966 ='<a href="' . get_admin_url(null, 'options-general.php'). '"> target="_blank">here</a>';
 $m4is_f4930 =esc_html($m4is_f4930);
 $m4is_u6591 =['label' =>'Invalid Site Administrator Contact Email', 'status' =>'critical', 'badge' =>['label' =>__('Security'), 'color' =>'red', ], 'description' =>"<p>An invalid email ({$m4is_f4930
}) was detected in the site's <strong>Administration Email Address</strong> setting.  This may affect your software license management.</p>", 'actions' =>'<p>We recommend setting your Administrator email address to a valid email.  You can update this setting ' . $m4is_x01966 . '</p>', 'test' =>'wpal_wordpress_administrator_email', ];
 
}
}return $m4is_u6591;
 
}static 
function m4is_g845(): array {
$m4is_u6591 =[];
 $m4is_h48639 =[ 'administrator', 'memberium', 'sitemanager', 'support', 'test', ];
 foreach ($m4is_h48639 as $m4is_g928){
$m4is_l17096 =get_user_by('login', $m4is_g928);
 if($m4is_l17096 &&self::$m4is_r1546->m4is_v461($m4is_l17096->ID)){
break;
 
}$m4is_l17096 =false;
 
}if($m4is_l17096 &&is_a($m4is_l17096, 'WP_User')){
$m4is_g928 =$m4is_l17096->user_login;
 $m4is_c67 ='<a href="' . get_admin_url(null, "user-edit.php?user_id={$m4is_l17096->ID
}"). '">view the user here</a>';
 $m4is_u6591 =['label' =>'Default Admin Account Logins', 'status' =>'recommended', 'badge' =>['label' =>__('Security'), 'color' =>'orange', ], 'description' =>"<p>Your install of Memberium has one or more admin user using the default admin name of '{$m4is_g928
}'.  This increases the exposure of your site to attack by hackers bruteforcing your admin login.</p>", 'actions' =>'<p>You can ' . $m4is_c67 . '. We recommend changing your WordPress admin username to something non-obvious.</p><p>If you have questions, please ' . self::$m4is_y96480 . '</p>', 'test' =>'wpal_wordpress_admin_is_admin', ];
 
}return $m4is_u6591;
 
}static 
function m4is_r01(): array {
$m4is_u6591 =[];
 $m4is_s90641 =self::$m4is_r1546->m4is_w45();
 $m4is_e6560 =m4is_l9685::m4is_n8610();
 $m4is_x01966 =get_admin_url(null, 'admin.php?page=memberium-support&tab=updates' );
 $m4is_u6591 =['label' =>'Memberium Plugin Version', 'status' =>'good', 'badge' =>['label' =>__('Performance'), 'color' =>'green', ], 'description' =>"<p>Your install of Memberium is running the latest available version.</p>", 'actions' =>'', 'test' =>'wpal_memberium_outdated', ];
 if(version_compare($m4is_e6560, $m4is_s90641, '>' )){
$m4is_u6591['label']='Memberium Plugin Requires Update';
 $m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description']="<p>Your install of Memberium is running an older version (v{$m4is_s90641
}).  Your version is missing features, performance, and security improvements.   Only current versions are eligible for some kinds of support.</p>";
 $m4is_u6591['actions']="<p>We recommend <a href='{$m4is_x01966
}'>updating to the latest version</a> v{$m4is_e6560
}.</p><p>If you would like assistance, please contact <a href='https://memberium.com/support/' target='_blank'>Memberium Support</a>.</p>";
 
}return $m4is_u6591;
 
}static 
function m4is_g1967(): array {
$m4is_u6591 =[];
 $m4is_h48639 =['support@memberium.com', 'support@memberium.zendesk.com', ];
 foreach ($m4is_h48639 as $m4is_g928){
$m4is_l17096 =get_user_by('email', $m4is_g928);
 if($m4is_l17096 &&self::$m4is_r1546->m4is_v461($m4is_l17096->ID)){
break;
 
}$m4is_l17096 =false;
 
}if($m4is_l17096){
$m4is_g928 =$m4is_l17096->user_login;
 $m4is_c67 ='<a href="' . get_admin_url(null, "user-edit.php?user_id={$m4is_l17096->ID
}"). '">view the user here</a>';
 $m4is_u6591 =['label' =>'Memberium Support Admins', 'status' =>'recommended', 'badge' =>['label' =>__('Security'), 'color' =>'orange', ], 'description' =>"<p>Your install of Memberium has one or more admin users belonging to Memberium support.</p>", 'actions' =>"<p>We recommend changing the user's role to 'Subscriber', or deleting the user completely once the login is no longer needed.</p><p>If you have questions, please " . self::$m4is_y96480 . "</p><p>You can {$m4is_c67
}.</p>", 'test' =>'wpal_memberium_support_account', ];
 
}return $m4is_u6591;
 
}static 
function m4is_w43106(): array {
if(!function_exists('get_plugins')){
require_once ABSPATH . 'wp-admin/includes/plugin.php';
 
}$m4is_u6591 =[];
 $m4is_x536 =[];
 $m4is_i6358 =array_merge(get_plugins(), get_mu_plugins());
 if(!is_array($m4is_i6358 )||empty ($m4is_i6358 )){
$m4is_u6591 =['label' =>'No Plugins Found', 'status' =>'critical', 'badge' =>['label' =>__('Security'), 'color' =>'red', ], 'description' =>"<p>No installed plugins were found when checking for membership plugin conflicts.</p>", 'test' =>'wpal_wordpress_plugins_caching', 'data' =>'', ];
 return $m4is_u6591;
 
}$m4is_z57318 =['accessally/accessally.php', 'infusion4wp/infusion4wpload.php', 'memberful-wp/memberful-wp.php', 'members/members.php', 'paid-member-subscriptions/index.php', 'paid-memberships-pro/paid-memberships-pro.php', 'restrict-content/restrictcontent.php', 's2member/s2member.php', 'simple-membership-after-login-redirection/swpm-after-login-redirection.php', 'simple-membership/simple-wp-membership.php', 'wishlist-member/wpm.php', 'wp-members/wp-members.php', ];
 foreach($m4is_z57318 as $m4is_a1052 ){
if(array_key_exists($m4is_a1052, $m4is_i6358)){
$m4is_g78 =$m4is_i6358[$m4is_a1052];
 $m4is_k85713 =isset($m4is_g78['Name'])? $m4is_g78['Name']: $m4is_n65;
 $m4is_f85637 =isset($m4is_g78['Version'])? $m4is_g78['Version']: '';
 $m4is_x536[]=$m4is_k85713 . ' v' . $m4is_f85637;
 
}
}if(!empty($m4is_x536)){
$m4is_u6591 =['label' =>'Conflicting Membership Plugins Found', 'status' =>'critical', 'badge' =>['label' =>__('Security'), 'color' =>'red', ], 'description' =>"<p>The following membership plugins were found installed on your system.  " . "Running multiple membership plugins is not supported. " . "Installation and of these plugins can cause problems including access controls being broken, data corruption, and the wrong information displayed to the wrong users.</p>", 'actions' =>'<p>We recommend deactivating and removing these plugins.</p>', 'test' =>'wpal_wordpress_plugins_membership', 'data' =>$m4is_x536, ];
 foreach($m4is_x536 as $m4is_n91){
$m4is_u6591['description'].= $m4is_n91 . '<br />';
 
}
}return $m4is_u6591;
 
}static 
function m4is_l38(): array {
$m4is_m96240 =self::$m4is_r1546->m4is_j498('memberships' );
 $m4is_m96240 =is_array($m4is_m96240 )? $m4is_m96240 : [];
 $m4is_h973 =count($m4is_m96240 );
 $m4is_u6591 =['label' =>'Memberium Membership Levels', 'status' =>'good', 'badge' =>['label' =>__('Security' ), 'color' =>'green', ], 'description' =>'', 'actions' =>'', 'test' =>'wpal_memberium_membership_levels', ];
 if($m4is_h973 == 0 ){
$m4is_u6591['status']='recommended';
 $m4is_u6591['badge']['color']='orange';
 $m4is_u6591['description'].= '<p>No Memberium membership levels were found.</p>';
 $m4is_u6591['actions'].= '<p>We recommend creating one or more Memberium membership levels to properly secure your site.</p>';
 
}else{
$m4is_l9321 =m4is_k865::m4is_z2906(false );
 $m4is_l9321 =is_array($m4is_l9321 )? $m4is_l9321 : [];
 if(empty($m4is_l9321['mc'])){
$m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description'].= '<p>No tags were found in your system.  Memberium requires at least one tag to be present in order to function properly.</p>';
 $m4is_u6591['actions'].= '<p>We recommend creating a tag in your Keap account, and then syncing it with Memberium.</p>';
 return $m4is_u6591;
 
}$m4is_l16435 =['main_id', 'payf_id', 'cancel_id', 'suspend_id' ];
 $m4is_l5726 =[];
 $m4is_o9166 =[];
 foreach($m4is_m96240 as $m4is_w64 ){
foreach($m4is_l16435 as $m4is_l9671){
if(!empty($m4is_w64[$m4is_l9671])){
$m4is_p786 =$m4is_w64[$m4is_l9671];
 $m4is_l5726[$m4is_p786]=$m4is_w64['name'];
 
}
}if(!empty($m4is_w64['addltag_ids'])){
$m4is_t76542 =array_filter(explode(',', $m4is_w64['addltag_ids']));
 foreach($m4is_t76542 as $m4is_p786){
$m4is_l5726[$m4is_p786]=$m4is_w64['name'];
 
}
}
}array_unique($m4is_l5726 );
 uasort($m4is_l5726, function($a, $b){
return (int) ($a > $b);
 
});
 foreach($m4is_l5726 as $m4is_p786 =>$m4is_k52736 ){
if(!array_key_exists($m4is_p786, $m4is_l9321['mc'])){
$m4is_o9166[$m4is_k52736][]=$m4is_p786;
 
}
}if(!empty($m4is_o9166)){
$m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 foreach($m4is_o9166 as $m4is_w64 =>$m4is_l9321){
if(!empty($m4is_l9321)){
$m4is_u6591['description'].= "<p>Your {$m4is_w64
} membership level is missing the following tag IDs: " . implode(',', $m4is_l9321). "</p>";
 
}
}$m4is_u6591['actions']='<p>Check your tag sync options to ensure you are not blocking any tag categories used by your membership levels.</p>';
 $m4is_u6591['actions'].= '<p>If the referenced tags no longer exist, remove them from your membership, or delete the membership level.</p>';
 
}
}if($m4is_u6591['status']== 'good'){
$m4is_u6591['description']="<p>Your {$m4is_h973
} membership levels appear to be present and properly configured.</p>";
 
}return $m4is_u6591;
 
}static 
function m4is_d436(): array {
$m4is_g41650 =self::$m4is_r1546->m4is_q80();
 $m4is_o92031 =intval(wp_convert_hr_to_bytes($m4is_g41650 )/ 1024 / 1024);
 $m4is_l8430 =intval(wp_convert_hr_to_bytes($m4is_g41650 )/ 1024 / 1024);
 $m4is_u6591 =['label' =>'Sufficient Memory Allocated', 'status' =>'good', 'badge' =>['label' =>__('Performance'), 'color' =>'green', ], 'description' =>"You have {$m4is_o92031
}MB allocated to the frontend, and {$m4is_l8430
}MB allocated to the admin dashboard.", 'actions' =>'', 'test' =>'wpal_healthcheck_memory', ];
 if($m4is_o92031 < 41){
$m4is_a173 ="<p>Your site is configured with a memory limit of {$m4is_o92031
}MB<br />" . 'You can increase your WordPress memory limit by adding or updating the following line in your wp-config.php:<br />' . "<code>define('WP_MEMORY_LIMIT', '64M');</code><br />" . 'For sites with many or complex plugins or themes, you may want to set it to 96M, 128M, 160M or more.</p>';
 $m4is_u6591['label']='Memory Warning';
 $m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description']=$m4is_a173;
 
}if($m4is_o92031 > 768){
$m4is_a173 ="<p>Your site is configured with WP_MEMORY_LIMIT set to {$m4is_o92031
}MB.<br />" . 'Sites needing high memory allocations often can cause performance issues due to misconfiguration and this is not normal even for large sites.  ' . 'We recommend contacting <a href="https:://memberium.com/support/">Memberium support</a> for assistance.</p>';
 $m4is_u6591['label']='Memory Warning';
 $m4is_u6591['status']='recommended';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description']=$m4is_a173;
 
}if($m4is_l8430 < 40 ){
$m4is_a173 ="<p>Your site is configured with an Admin memory limit of {$m4is_l8430
}MB<br />" . 'You can increase your WordPress memory limit by adding or updating the following line in your wp-config.php:<br />' . "<code>define('WP_MAX_MEMORY_LIMIT', '64M');</code><br />" . 'For sites with many or complex plugins or themes, you may want to set it to 96M, 128M, 160M or more.</p>';
 $m4is_u6591['label']='Memory Warning';
 $m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description'].= $m4is_a173;
 
} return $m4is_u6591;
 
}static 
function m4is_k54(): array {
global $wpdb;
 $m4is_k48 =get_option('memberium_tables', []);
 $m4is_k48[]='i2sdk_apilog';
 $m4is_p21 =[];
 $m4is_u6591 =['label' =>__('Memberium Database Tables'), 'status' =>'good', 'badge' =>['label' =>__('Database'), 'color' =>'green', ], 'description' =>'', 'actions' =>'', 'test' =>'wpal_database_tables', ];
 if(is_array($m4is_k48)){
foreach($m4is_k48 as $m4is_v379){
$m4is_v2613 ="SELECT 1 FROM `{$m4is_v379
}` LIMIT 1;";
 $wpdb->get_var($m4is_v2613);
 $m4is_c31 =$wpdb->last_error;
 if($m4is_c31){
$m4is_p21[]=$m4is_v379;
 
}
}sort($m4is_p21);
 
}if(!empty($m4is_p21)){
$m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description']='<p>The following essential database tables are missing or unreadable.</p>';
 foreach($m4is_p21 as $m4is_k52736){
$m4is_u6591['description'].= "{$m4is_k52736
}<br />";
 
}
}else{
$m4is_u6591['description']='<p>All Memberium and i2SDK database tables are present.</p>';
 
}return $m4is_u6591;
 
}static 
function m4is_z0162(): array {
$m4is_f6067 =get_option('active_plugins');
 $m4is_t265 =false;
 $m4is_u6591 =[];
 foreach($m4is_f6067 as $m4is_g78){
if(stripos($m4is_g78, 'menu-items-visibility-control')!== false){
$m4is_t265 =true;
 break;
 
}
}if($m4is_t265){
$m4is_p95312 =get_admin_url(null, 'plugins.php?s=menu items visibility control&plugin_status=search');
 $m4is_u6591 =['label' =>'Deactivate Menu Items Visibility Control Plugin', 'status' =>'critical', 'badge' =>['label' =>__('Performance'), 'color' =>'red', ], 'description' =>"<p>Memberium has detected that you have the Menu Items Visibility Control plugin installed.  The function provided by this plugin has been replaced by Memberium's enhanced native Menu visibility controls with more features and better security.</p><p>Memberium has already imported the settings from this plugin.</p>", 'actions' =>"<p>We recommend <a href='{$m4is_p95312
}'>deactivating</a> this plugin.</p>", 'test' =>'wpal_memberium_mivc_plugin', ];
 
}return $m4is_u6591;
 
}static 
function m4is_q61938(): array {
$m4is_u6591 =[];
 if(defined('MULTISITE')&&MULTISITE){
$m4is_u6591 =['label' =>'Memberium is not Network Activated', 'status' =>'good', 'badge' =>['label' =>__('Performance'), 'color' =>'green', ], 'description' =>'', 'actions' =>'', 'test' =>'wpal_m4is_network_activation', ];
 $m4is_e592 =(array)get_site_option('active_sitewide_plugins');
 $m4is_h863 =isset($m4is_e592['memberium2/memberium2.php'])? $m4is_e592['memberium2/memberium2.php']> 0 : false;
 if($m4is_h863){
$m4is_u6591['label']='Memberium is Network Activated';
 $m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='orange';
 $m4is_u6591['description'].= '<p>It is recommended to only activate Memberium only on a per-site basis, unless ALL sites in the network are always actively being used for memberships.</pre>';
 $m4is_u6591['actions'].= '<p>We recommend Network Deactivating Memberium, and activating it on any individual network sites it is actively being used on.</p>';
 
}
}return $m4is_u6591;
 
} static 
function m4is_s130(): array {
$m4is_u6591 =[];
 $m4is_h39 =function_exists('wp_using_ext_object_cache')? wp_using_ext_object_cache(): 'production';
 $m4is_u6591 =['label' =>'Object Caching Missing / Inactive', 'status' =>'recommended', 'badge' =>['label' =>__('Performance'), 'color' =>'red', ], 'description' =>'<p>Object caching was not detected.  Memberium is designed to use object caching to both speed up operations and reduce database loads.</p>', 'actions' =>'<p>Install and activate object caching.</p>', 'test' =>'wpal_wordpress_object_cache', ];
 if($m4is_h39){
$m4is_u6591['label']='Object Caching Active';
 $m4is_u6591['status']='good';
 $m4is_u6591['badge']['color']='green';
 $m4is_u6591['description']='<p>Object Caching was detected.  Most Excellent!</p>';
 $m4is_u6591['actions']='';
 
}return $m4is_u6591;
 
} static 
function m4is_u46(): array {
$m4is_d5472 =[];
 $m4is_o41 =get_option('users_can_register', false);
 if($m4is_o41){
$m4is_d5472 =['label' =>'Open Registration', 'status' =>'recommended', 'badge' =>['label' =>__('Security'), 'color' =>'red', ], 'description' =>'<p>Your WordPress site has been configured to allow any viewer to register.</p>', 'test' =>'wpal_wordpress_open_registration', 'actions' =>'<p>It is recommended that you disable this setting, and use Keap to handle new user registrations.</p>' . '<p>You can change this setting <a href="options-general.php">here</a>, by unchecking "<strong>Anyone can register</strong>".</p>', ];
 
}return $m4is_d5472;
 
} static 
function m4is_s92(): array {
$m4is_d5472 =[];
 if(self::$m4is_r1546->m4is_e58704()=== 'production' ){
$m4is_i61 =array_diff(scandir(ABSPATH ), ['.', '..']);
 $m4is_p2043 =[];
 foreach($m4is_i61 as $m4is_k86914){
$m4is_k86914 =ABSPATH . $m4is_k86914;
 if(!is_dir($m4is_k86914)){
if(stripos(file_get_contents($m4is_k86914, false, null, 0, 256), '<?')!== false){
if(stripos(file_get_contents($m4is_k86914, false, null, 0, 256), 'phpinfo(')!== false){
$m4is_p2043[]=$m4is_k86914;
 
}
}
}
}if(count($m4is_p2043)){
$m4is_d5472 =['label' =>'PHPinfo() Security Issue', 'status' =>'critical', 'badge' =>['label' =>__('Security'), 'color' =>'red', ], 'description' =>"<p>Files with the phpinfo() debug code have been detected in your top level WordPress directory.  These files are not used by WordPress, and they expose sensitive security information about your server and site that can be used by attackers.</p>", 'actions' =>'<p>Please ask your web host to review and remove the following files:</p>', 'test' =>'wpal_server_phpinfo', ];
 foreach($m4is_p2043 as $m4is_k86914){
$m4is_d5472['actions'].= "{$m4is_k86914
}<br>";
 
}
}
}return $m4is_d5472;
 
}static 
function m4is_d73(): array {
$m4is_u6591 =['label' =>'Membership Password Security', 'status' =>'good', 'badge' =>['label' =>__('Security'), 'color' =>'green', ], 'description' =>'Passwords are securely stored in WordPress only.', 'actions' =>'', 'test' =>'wpal_memberium_plaintext_password', ];
 $m4is_x01966 =get_admin_url(null, 'admin.php?page=memberium-options');
 $m4is_a62 ='https://memberium.com/?p=6818';
 $m4is_d45 =self::$m4is_r1546->m4is_j498('settings', 'local_auth_only');
 if(empty($m4is_d45)){
$m4is_u6591['status']='recommended';
 $m4is_u6591['badge']['color']='orange';
 $m4is_u6591['description']='<p>Your Memberium system is set to sync passwords in plaintext from WordPress to Keap.  ';
 $m4is_u6591['description'].= 'This may put members at risk who carelessly re-use passwords on multiple sites.  ';
 $m4is_u6591['description'].= 'It may also not be compliant with various privacy regulations and laws governing you or your members.</p>';
 $m4is_u6591['actions']="<p><strong>Please review our <a href='{$m4is_a62
}' target='_blank'>documentation</a> and <a href='https://memberium.com/support/' target='_blank'>contact support</a> to understand this feature <span style='color:red;'>before</span> making any changes.</strong></p>";
 $m4is_u6591['actions'].= "<p><a href='{$m4is_x01966
}'>Click Here</a> to review your setting for 'Secure Passwords / Local Auth Only'.</p>";
 
}return $m4is_u6591;
 
}static 
function m4is_z27593(): array {
$m4is_u6591 =['label' =>'No Proxies detected', 'status' =>'good', 'badge' =>['label' =>__('Security'), 'color' =>'green' ], 'description' =>'No web proxies were detected.  Web Proxies like Cloudflare and Securi are services to secure and speed up your website, however they may interfere with HTTP POSTs coming from Keap.', 'actions' =>'', 'test' =>'wpal_healthcheck_cloudflare', ];
 if(!empty($_SERVER['HTTP_CF_RAY'])){
$m4is_a173 ='<p>Memberium has detected that you are using CloudFlare.  We recommend reviewing your CloudFlare configuration to ensure that page caching is disabled.</p>';
 $m4is_u6591['label']='CloudFlare Detected';
 $m4is_u6591['status']='recommended';
 $m4is_u6591['badge']['color']='orange';
 $m4is_u6591['description']=$m4is_a173;
 $m4is_u6591['data']='CloudFlare';
 
}return $m4is_u6591;
 
} static 
function m4is_r532(): array {
$m4is_l9321 =m4is_k865::m4is_z2906(false );
 $m4is_h973 =isset($m4is_l9321['mc'])&&is_array($m4is_l9321['mc'])? count($m4is_l9321['mc']): 0;
 $m4is_c1869 =get_option('memberium_tables_updated', []);
 $m4is_n746 =empty($m4is_c1869['tags'])? 0 : $m4is_c1869['tags'];
 $m4is_u6591 =['label' =>'Memberium Tags', 'status' =>'good', 'badge' =>['label' =>__('Security'), 'color' =>'green', ], 'description' =>"<p>We found {$m4is_h973
} tags synced from Keap.</p>", 'actions' =>'', 'test' =>'wpal_memberium_tags', ];
 if($m4is_h973){
$m4is_x01966 =get_admin_url(null, 'admin.php?page=memberium-support&tab=dashboard');
 $m4is_u6591['actions']='<p>If you are missing some tags, you can <a href="' . $m4is_x01966 . '"> resynchronize the tag list here</a>:</p>';
 if($m4is_n746){
$m4is_u6591['description'].= '<p>Your tags were last synced ' . human_time_diff($m4is_n746). ' ago.</p>';
 
}
}else{
$m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description']='<p>No tags have been synced from Keap.</p>';
 $m4is_u6591['actions']='';
 
}return $m4is_u6591;
 
}  static 
function m4is_u42793(): array {
$m4is_u450 =['AUTH_KEY', 'AUTH_SALT', 'LOGGED_IN_KEY', 'LOGGED_IN_SALT', 'NONCE_KEY', 'NONCE_SALT', 'SECURE_AUTH_KEY', 'SECURE_AUTH_SALT', ];
 $m4is_e0234 =0;
 $m4is_u6591 =['label' =>'WordPress Security Keys', 'status' =>'good', 'badge' =>['label' =>__('Security'), 'color' =>'green', ], 'description' =>"<p>Your WordPress security keys need to be defined with unique, secure values.</p>", 'actions' =>'', 'test' =>'wpal_wp_security_keys', ];
 foreach($m4is_u450 as $m4is_l9671){
if(!defined($m4is_l9671)){
$m4is_e0234++;
 
}else{
$m4is_v586 =trim(strtolower(constant($m4is_l9671)));
 if(empty($m4is_v586)||$m4is_v586 == 'put your unique phrase here' ){
$m4is_e0234++;
 
}
}
}if($m4is_e0234){
$m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['actions']='Please review your wp-config.php file to update your KEYS and SALTS.  ' . 'You can find a new set of keys here <a href="https://api.wordpress.org/secret-key/1.1/salt/" target="_blank">here</a>. ' . 'Please contact your webhost if you would like assistance making thsi change.';
 
}return $m4is_u6591;
 
}static 
function m4is_y05614(): array {
if(!class_exists('woocommerce' )){
return [];
 
}$m4is_x086 =get_option('woocommerce_enable_guest_checkout' );
 $m4is_v59810 =get_option('woocommerce_enable_signup_and_login_from_checkout' );
   if($m4is_x086 == 'no' &&$m4is_v59810 == 'yes' ){
return [];
 
}$m4is_d3069 ='';
 if($m4is_x086 == 'yes' ){
$m4is_d3069 .= '<p>Guest checkout is enabled.  This option should be turned off to require customers to create an account/login during checkout.</p>';
 
}if($m4is_v59810 == 'no' ){
$m4is_d3069 .= '<p>Account signup is disabled.  This enbales customers to create an account during checking out.</p>';
 
}$m4is_d3069 .= '<p>In order for Memberium to sync new purchases to your CRM and run actions or apply tags, an account must be created.  Please configure your WooCommerce settings to enforce account creation during checkout.</p>';
 $m4is_u6591 =['label' =>'WooCommerce Guest Checkout', 'status' =>'critical', 'badge' =>['label' =>__('Security'), 'color' =>'red' ], 'description' =>$m4is_d3069, 'actions' =>'', 'test' =>'wpal_healthcheck_wooocommerce_guest_checkout', 'data' =>false, ];
 return $m4is_u6591;
 
}    static 
function m4is_v14068(): array {
$m4is_u6591 =['label' =>'No Page Caching Plugins Found', 'status' =>'good', 'badge' =>['label' =>__('Security' ), 'color' =>'green', ], 'description' =>'', 'actions' =>'', 'test' =>'wpal_healthcheck_caching', ];
 if(function_exists('wpsc_init' )){
$m4is_u6591['label']='Page Caching Plugins Found';
 $m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description'].= '<p>WP Super Cache is installed and active.</p>';
 
}if(defined('W3TC' )){
$m4is_u6591['label']='Page Caching Plugins Found';
 $m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description'].= '<p>W3 Total Cache is installed and active.</p>p>';
 
}if(defined('WP_ROCKET_VERSION')){
$m4is_u6591['label']='Page Caching Plugins Found';
 $m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description'].= '<p>WP Rocket is installed and active.</p>';
 
}if(class_exists('divirocket' )){
$m4is_u6591['label']='Page Caching Plugins Found';
 $m4is_u6591['status']='critical';
 $m4is_u6591['badge']['color']='red';
 $m4is_u6591['description'].= '<p>Divi Rocket is installed and active.</p>';
 
}if(empty($m4is_u6591['description'])){
$m4is_u6591['description']='<p>No caching plugin conflicts detected.<br />Installing Caching plugins can cause data corruption and security issues.</p>';
 
}else{
$m4is_u6591['description'].= '<p>Running caching plugins with your membership site can cause corruption, access problems, and security problems.  To fix this problem, disable your caching plugins.</p>';
 
}return $m4is_u6591;
 
}static 
function m4is_u64(): array {
$m4is_j108 =array_filter(explode(',', self::$m4is_r1546->m4is_j498('settings', 'ignore_contact_fields' )));
 $m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field', 'Password' );
 $m4is_u6591 =[];
 $m4is_j15 =[];
 if(in_array($m4is_r6234, $m4is_j108 )){
$m4is_j15[]=sprintf('<p>You need to unblock the <em>%s</em> field from being synced with your CRM, or choose a different password field.</p>', $m4is_r6234 );
 $m4is_j15[]=sprintf('<p>Password field settings are <strong><a href="%s">here</a></strong>.</p>', admin_url('admin.php?page=memberium-options' ));
 $m4is_j15[]=sprintf('<p>Blocked field settings are <strong><a href="%s">here</a></strong>.</p>', admin_url('admin.php?page=memberium-sync-options&tab=contactfields' ));
 $m4is_j15[]='<p></p>';
 $m4is_u6591 =['actions' =>implode("\n", $m4is_j15 ), 'description' =>"<p>The CRM field you are using to store the password has been blocked from syncing.  This may cause problems with the password being generated or updated.</p>", 'label' =>'Memberium Password Field Sync Blocked', 'status' =>'critical', 'test' =>'wpal_memberium_password_blocked', 'badge' =>['label' =>__('Server'), 'color' =>'red', ], ];
 
}return $m4is_u6591;
 
}        
}

