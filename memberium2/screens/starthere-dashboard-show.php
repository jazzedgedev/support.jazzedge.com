<?php

/**
 * Copyright (c) 2017-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_w66547::m4is_z95();
 
class m4is_w66547 {
private $m4is_r1546;
 private $m4is_r9613;
 private $m4is_n96734;
 private $m4is_q4873;
 private $m4is_c1869;
 public static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_r1546->m4is_s965('view_dashboard' );
 $this->m4is_d4861();
 $this->m4is_z603();
 $this->m4is_j25867();
 $this->m4is_w85();
 $this->m4is_g08();
 $this->m4is_l3674();
 $this->m4is_h3807();
 $this->m4is_m67539();
 $this->m4is_u618();
 
}private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 $this->m4is_n96734 =false;
 $this->m4is_q4873 =false;
 $this->m4is_c1869 =[];
 
}private 
function m4is_d4861(){
add_filter('human_time_diff', [$this, 'm4is_l678'], 10, 4 );
  
}
function m4is_l678($m4is_q1450, $m4is_c29861, $m4is_q7924, $m4is_v10 ){
return ($m4is_q7924 < 1325376000 ||$m4is_c29861 > (20 * YEAR_IN_SECONDS ))? 'Never' : $m4is_q1450;
 
} private 
function m4is_z603(){
$m4is_p21 =m4is_s6729::m4is_c26()->m4is_h5012();
 if(count($m4is_p21 )){
m4is_n17::m4is_z126(false );
 
}
} 
function m4is_w74(){
 if(m4is_s52::m4is_f27()){
$m4is_i406 ='<strong class="membGood">Active</strong>';
 $m4is_h56893 =m4is_s52::m4is_v91686(get_option($this->m4is_r1546->m4is_e96(), '' ));
 if($m4is_h56893['trial_mode']){
$m4is_i406 ='<strong class="membGood">Test Mode</strong> <strong class="membWarning">(Limited to ' . $m4is_h56893['max_users']. ' Users )</strong>';
 
}
}else{
$m4is_i406 ='<strong class="membWarning">Inactive</strong> ';
 
}return $m4is_i406;
 
} 
function m4is_a973(){
$m4is_y205 ='memberium_tables_updated';
 $m4is_h56320 =get_option($m4is_y205, []);
 $m4is_h56320 =is_array($m4is_h56320 )? $m4is_h56320 : [];
 $m4is_k48 =['actionsets', 'affiliates', 'i2sdk_customfields', 'invoices', 'products', 'social', 'tagcategories', 'tags', ];
 foreach ($m4is_k48 as $m4is_e80 ){
$m4is_h56320[$m4is_e80]=isset($m4is_h56320[$m4is_e80])? $m4is_h56320[$m4is_e80]: 0;
 
}update_option($m4is_y205, $m4is_h56320 );
 return $m4is_h56320;
 
}
function m4is_e13467(){
global $wpdb;
 $m4is_m7426 =m4is_p40::m4is_o1723();
 $m4is_v2613 ="SELECT count(`id`) from `{$m4is_m7426
}` WHERE `fieldname` = 'Id' AND `appname` = '{$this->m4is_r9613
}' ";
 return (int) $wpdb->get_var($m4is_v2613 );
 
}
function m4is_b15(){
global $wpdb;
 $m4is_m7426 =m4is_v87365::m4is_a36489();
 $m4is_v2613 ="SELECT count(`id`) from `{$m4is_m7426
}` WHERE `appname` = '{$this->m4is_r9613
}' ";
 return (int) $wpdb->get_var($m4is_v2613 );
 
}
function m4is_g167(){
global $wpdb;
 $m4is_m7426 =i2sdk_class::DB_API_LOG;
 $m4is_v2613 ="SELECT count(*) from {$m4is_m7426
} WHERE `appname` = '{$this->m4is_r9613
}' ";
 return (int) $wpdb->get_var($m4is_v2613 );
 
}
function m4is_j92537(){
global $wpdb;
 $m4is_m7426 =m4is_z13097::m4is_o71();
 $m4is_v2613 ="SELECT count(*) from `{$m4is_m7426
}` WHERE `fieldname` = 'Id' AND `appname` = '{$this->m4is_r9613
}'";
 return (int) $wpdb->get_var($m4is_v2613 );
 
}
function m4is_d7312(){
global $wpdb;
 $m4is_m7426 =m4is_v87365::m4is_p63285();
 $m4is_v2613 ="SELECT count(*) from `{$m4is_m7426
}` WHERE `appname` = '{$this->m4is_r9613
}'";
 return (int) $wpdb->get_var($m4is_v2613 );
 
}
function m4is_u473(){
return 0;
 
}
function m4is_m67539(){
$m4is_x8341 =defined('MEMBERIUM_BETA' )&&constant('MEMBERIUM_BETA' );
 $m4is_i8169 =defined('MEMBERIUM_DEBUG' )&&constant('MEMBERIUM_DEBUG' );
 $m4is_v708 =defined('WP_DEBUG' )&&constant('WP_DEBUG' );
 if($m4is_x8341 ){
echo '<label>Beta Mode</label><span class="metric"><strong class="membWarning">Beta</strong></span><br />';
 
}if($m4is_i8169 ){
echo '<label>Debug Mode</label><span class="metric"><strong class="membWarning">ON</strong></span><br />';
 
}if($m4is_v708 ){
echo '<label>WordPress Debug Mode</label><span class="metric"><strong class="membWarning">ON</strong></span><br />';
 
}
}
function m4is_a6280(){
global $wpdb;
 $m4is_m7426 =$wpdb->postmeta;
 $m4is_v2613 ="SELECT count(DISTINCT `post_id` ) FROM `{$m4is_m7426
}` WHERE meta_key IN ('_is4wp_membership_levels', '_is4wp_access_tags', '_is4wp_access_tags2', '_is4wp_any_membership', '_is4wp_hide_completely', '_is4wp_force_public') AND meta_value > '' ";
 return (int)$wpdb->get_var($m4is_v2613 );
 
}
function m4is_u618(){
require ABSPATH . WPINC . '/version.php';
 $m4is_k698 =defined('MEMBERIUM_SKU' )? constant('MEMBERIUM_SKU' ): 'Unknown';
 $m4is_d3192 =defined('I2SDK_VERSION' )? constant('I2SDK_VERSION' ): '';
 $m4is_d8531 =0;
 $m4is_m93 =ini_get('memory_limit' );
 $m4is_q79615 =intval(WP_MAX_MEMORY_LIMIT );
 $m4is_u1409 =(int) $this->m4is_r1546->m4is_q80();
 $m4is_d3192 =empty($m4is_d3192 )? '<strong class="membWarning">None</strong>' : $m4is_d3192;
 $m4is_z697 =php_uname('s'). ' ' . php_uname('m');
 $m4is_t24096 =phpversion();
 $m4is_e4176 =ini_get('display_errors')? '<strong class="membWarning">Yes' : '<strong class="membGood">No';
 $m4is_k698 =strtoupper($m4is_k698 );
 $m4is_y09471 =count(get_plugin_updates());
 $m4is_b64138 =count(get_theme_updates());
 $m4is_a5794 =count($this->m4is_r1546->m4is_j498('memberships' ));
 $m4is_y025 =count(get_option('cron', []));
 $m4is_g96 =json_decode(get_transient('health-check-site-status-result' ), true);
 $m4is_i9358 =wp_using_ext_object_cache()? '<strong style="color:green;">Enabled</strong>' : '<strong style="color:red;">Unavailable</strong>';
 $m4is_r62 =get_core_updates();
 $m4is_q182 =get_bloginfo('version' );
 $m4is_z519 =esc_url(admin_url('site-health.php'));
 $m4is_l14 =is_ssl()? '<strong style="color:green;">Yes</strong>' : '<strong style="color:red;">No</strong>';
 $m4is_s15347 =is_multisite()? 'Yes' : 'No';
 $m4is_e66310 =$this->m4is_r1546->m4is_v461()? '<strong class="membGood">Yes' : '<strong class="membWarning">No';
 $m4is_t43578 =ucwords($this->m4is_r1546->m4is_e58704());
 $m4is_w390 =$this->m4is_r1546->m4is_w45();
 $m4is_o280 =m4is_s52::m4is_s326();
 $m4is_a46 =m4is_a01587::m4is_y342()<> $_SERVER['REMOTE_ADDR'];
 $m4is_k02 =$this->m4is_a6280();
 $m4is_b96781 =$this->m4is_n83();
 $m4is_c36751 =$wp_version;
 $m4is_w918 =empty($m4is_g96['recommended'])? "None" : "<a href='{$m4is_z519
}'><strong style='color:red;'>{$m4is_g96['recommended']
}</strong></a>";
 $m4is_e8194 =empty($m4is_g96['critical'])? "None" : "<a href='{$m4is_z519
}'><strong style='color:red;'>{$m4is_g96['critical']
}</strong></a>";
 if(is_array($m4is_r62 )){
foreach($m4is_r62 as $m4is_a686 ){
$m4is_d8531 += (int) version_compare($m4is_a686->version, $m4is_q182, '>' );
 
}
}unset($m4is_r62, $m4is_a686, $m4is_q182 );
 echo '<hr>';
 echo '<h3>System Metrics</h3>';
 if($m4is_d8531 ||$m4is_y09471 ||$m4is_b64138 ){
echo '<label>Missing Core Updates</label><span class="metric membWarning">', $m4is_d8531, '</span><br />';
 echo '<label>Missing Plugin Updates</label><span class="metric membWarning">', $m4is_y09471, '</span><br />';
 echo '<label>Missing Theme Updates</label><span class="metric membWarning">', $m4is_b64138, '</span><br />';
 echo '<hr>';
 
}echo '<label>WordPress User Count</label><span class="metric">', $m4is_o280, '</span><br />';
 echo '<label>Membership Levels</label><span class="metric">', $m4is_a5794, '</span><br />';
 echo '<label>Protected Pages/Posts</label><span class="metric">', $m4is_k02, '</span><br />';
 echo '<hr>';
 echo '<label>WordPress Version</label><span class="metric">', $m4is_c36751, '</span><br />';
 echo '<label>WordPress Environment</label><span class="metric">', $m4is_t43578, '</span><br />';
 echo '<label>WordPress Recommended Issues</label><span class="metric">', $m4is_w918, '</span><br />';
 echo '<label>WordPress Critical Issues</label><span class="metric">', $m4is_e8194, '</span><br />';
 echo '<label>WordPress SSL</label><span class="metric">', $m4is_l14, '</span><br />';
 echo '<label>Wordpress Multisite</label><span class="metric"><strong>', $m4is_s15347, '</strong></span><br />';
 echo '<label>Wordpress Super Admin</label><span class="metric">', $m4is_e66310, '</strong></span><br />';
 echo '<label>WordPress Cron Jobs</label><span class="metric">', $m4is_y025, '</span><br />';
 echo '<label>Wordpress Object Caching</label><span class="metric">', $m4is_i9358, '</strong></span><br />';
 echo '<hr>';
 echo '<label>PHP Memory Allocated</label><span class="metric">', $m4is_m93, 'B</span><br />';
 echo '<label>WordPress Memory Limit</label><span class="metric">', $m4is_u1409, 'MB</span><br />';
 echo '<label>Admin Dashboard Memory Limit</label><span class="metric">', $m4is_q79615, 'MB</span><br />';
 echo '<hr>';
 echo '<label>Memberium SKU</label><span class="metric">', $m4is_k698, '</span><br />';
 echo '<label>i2SDK Version</label><span class="metric">', $m4is_d3192, '</span><br />';
 echo '<label>XML RPC Library Version</label><span class="metric">', $m4is_b96781, '</span><br />';
 echo '<label>PHP Version</label><span class="metric">', $m4is_t24096, '</span><br />';
 echo '<label>Operating System</label><span class="metric">', $m4is_z697, '</span><br />';
 echo '<label>Display Errors</label><span class="metric">', $m4is_e4176, '</strong></span><br />';
 echo '<label>Load Balancer / Proxy</label><span class="metric">', $m4is_a46 ? '<strong class="membGood">Yes</strong>' : 'No' , '</span><br />';
 echo '</form>';
 
}
function m4is_n83(): string {
$m4is_a6814 ='Uknonwn';
 if(class_exists('i2sdk_ixr_client_class' )){
$m4is_a6814 ='<strong style="color:green;">i2sdk-IXR</strong>';
 
} return $m4is_a6814;
 
}
function m4is_d10268(){
$m4is_h56320 =$this->m4is_a973();
 $m4is_h56320['tagcategories']=time();
 $m4is_h56320['tags']=time();
 $this->m4is_n96734 =true;
 m4is_z6894::m4is_d7492();
 m4is_k865::m4is_l26();
 update_option('memberium_tables_updated', $m4is_h56320, false );
 
}
function m4is_j25867(){
if(isset($_GET['action'])){
if($_GET['action']== 'sync-tags' ){
$this->m4is_d10268();
 
}elseif($_GET['action']== 'sync-actionsets'){
m4is_j4156::m4is_f7940();
 
}elseif($_GET['action']== 'sync-fields'){
m4is_s695::m4is_r2903();
 
}elseif($_GET['action']== 'update-license'){
m4is_s52::m4is_a834(true );
 
}
}
}
function m4is_w85(){
if(!isset($_GET['nosync'])){
return;
 
}if($this->m4is_n96734 ){
return;
 
}$m4is_h56320 =$this->m4is_a973();
 if((!$this->m4is_n96734 )&&(time()- $m4is_h56320['tags']> 300 )){
m4is_k865::m4is_l26();
 $m4is_h56320['tags']=time();
 $this->m4is_n96734 =true;
 update_option('memberium_tables_updated', $m4is_h56320, false );
 
}if((!$this->m4is_n96734 )&&(time()- $m4is_h56320['tagcategories']> 300 )){
m4is_z6894::m4is_d7492();
 $m4is_h56320['tagcategories']=time();
 $this->m4is_n96734 =true;
 update_option('memberium_tables_updated', $m4is_h56320, false );
 
}if((!$this->m4is_n96734 )&&(time()- $m4is_h56320['actionsets']> 300 )){
m4is_j4156::m4is_f7940();
 $m4is_h56320['actionsets']=time();
 $this->m4is_n96734 =true;
 update_option('memberium_tables_updated', $m4is_h56320, false );
 
}if((!$this->m4is_n96734 )&&(time()- $m4is_h56320['i2sdk_customfields']> 300 )){
m4is_s695::m4is_r2903();
 $m4is_h56320['i2sdk_customfields']=time();
 $this->m4is_n96734 =true;
 update_option('memberium_tables_updated', $m4is_h56320, false );
 
}if((!$this->m4is_n96734 )&&(time()- $m4is_h56320['products']> 300 )){
m4is_v87365::m4is_a0846();
 $m4is_h56320['products']=time();
 $this->m4is_n96734 =true;
 update_option('memberium_tables_updated', $m4is_h56320, false );
 
}if((!$this->m4is_n96734 )&&(time()- $m4is_h56320['invoices']> 300 )){
m4is_v87365::m4is_l318();
 $m4is_h56320['invoices']=time();
 $this->m4is_n96734 =true;
 update_option('memberium_tables_updated', $m4is_h56320, false );
 
}
}
function m4is_g08(){
$m4is_r9613 =strtoupper($this->m4is_r1546->m4is_i76('appname' ));
 $m4is_r9613 =empty($m4is_r9613 )? '<span class="membWarning">Missing</span>' : $m4is_r9613;
 $m4is_h67 =(bool) $this->m4is_r1546->get_i2sdk_options()['server_verified'];
 $m4is_h67 =$m4is_h67 ? '<strong class="membGood">Connected</strong>' : '<strong class="membWarning">Not Connected</strong>';
 $m4is_i406 =$this->m4is_w74();
 echo '<h3>Keap Connection</h3>';
 echo '<form method="post" action="">';
 echo '<label>App Name</label><strong>', $m4is_r9613, '</strong><br />';
 echo '<label>API Status</label>', $m4is_h67, '<br />';
 echo '<label>License Status</label>', $m4is_i406, '<br />';
 echo '<p>';
 echo '<input type="submit" name="save" value="Renew License" class="button-primary" style="margin-right:20px;">';
 echo '<input type="submit" name="save" value="Re-Activate Plugin" class="button-primary">';
 echo '</p>';
 
} private 
function m4is_l3674(){
echo '<h3>Keap API Metrics</h3>';
 $m4is_u83742 =get_option(i2sdk_class::API_METRICS_KEY, []);
 $m4is_u83742 =is_array($m4is_u83742 )? $m4is_u83742 : [];
 $m4is_k25038 =isset($m4is_u83742['timestamp'])? time()- $m4is_u83742['timestamp']: time();
 if($m4is_k25038 > 3600 ){
echo '<P>No Current API Metrics Available</p>';
 return;
 
}$m4is_c92430 =isset($m4is_u83742['product']['quota_limit'])? $m4is_u83742['product']['quota_limit']: 0;
 $m4is_o061 =isset($m4is_u83742['product']['quota_time_unit'])? $m4is_u83742['product']['quota_time_unit']: '';
 $m4is_v586 =number_format($m4is_c92430 ). ' / ' . $m4is_o061;
 echo '<label>Quota Limit</label><span class="metric">', $m4is_v586, '</span><br />';
 $m4is_c92430 =isset($m4is_u83742['product']['throttle_limit'])? $m4is_u83742['product']['throttle_limit']: 0;
 $m4is_o061 =isset($m4is_u83742['product']['throttle_time_unit'])? $m4is_u83742['product']['throttle_time_unit']: '';
 $m4is_v586 =number_format($m4is_c92430 ). ' / ' . $m4is_o061;
 echo '<label>Throttle Limit</label><span class="metric">', $m4is_v586, '</span><br />';
 $m4is_v586 =isset($m4is_u83742['product']['quota_available'])? number_format((int) $m4is_u83742['product']['quota_available']): 'Unknown';
 echo '<label>Quota Available</label><span class="metric">', $m4is_v586, '</span><br />';
 $m4is_v586 =isset($m4is_u83742['product']['throttle_available'])? number_format((int) $m4is_u83742['product']['throttle_available']): 'Unknown';
 $m4is_v586 =$m4is_v586 . '  / ' . $m4is_u83742['product']['throttle_time_unit'];
 echo '<label>API Calls left before Throttling</label><span class="metric">', $m4is_v586, '</span><br />';
 $m4is_v586 =empty($m4is_u83742['product']['quota_expiry_time'])? 0 : ($m4is_u83742['product']['quota_expiry_time']/ 1000 );
 $m4is_v586 =empty($m4is_v586 )? 'Unknown' : human_time_diff($m4is_v586 );
 echo '<label>Time Until Quota Reset</label><span class="metric">', $m4is_v586, '</span><br />';
 
}private 
function m4is_h3807(){
$m4is_c1869 =$this->m4is_a973();
 $m4is_d36 =m4is_j4156::m4is_c289();
 $m4is_y4158 =$this->m4is_g167();
 $m4is_r04 =m4is_z6894::m4is_o53614();
 $m4is_g6467 =$this->m4is_e13467();
 $m4is_e5246 =m4is_s695::m4is_r56126();
 $m4is_j14 =$this->m4is_b15();
 $m4is_c7260 =m4is_k865::m4is_q136();
 $m4is_c30 =$this->m4is_j92537();
 $m4is_c76 =$this->m4is_d7312();
 $m4is_q46 =$this->m4is_u473();
 $m4is_o7636 =$this->m4is_r1546->m4is_j498('settings', 'ignore_tag_categories' );
 $m4is_x64 =$this->m4is_r1546->m4is_j498('settings', 'ignore_contact_fields' );
 $m4is_s54781 =$this->m4is_r1546->m4is_j498('settings', 'db_sessions' );
 $m4is_p98 =count(array_filter(explode(',', trim($m4is_o7636, ',' ))));
 $m4is_x64 =count(array_filter(explode(',', trim($m4is_x64 ))));
 $m4is_m8176 =count(m4is_v87365::m4is_x096());
 if($m4is_y4158 ){
$m4is_y4158 ="<strong style='color:red;''>{$m4is_y4158
}</strong>";
 
}echo '<h3>Keap Cache Metrics</h3>';
 echo '<label>Synced Actionsets</label><span class="metric">', $m4is_d36, '</span> (', human_time_diff($m4is_c1869['actionsets']), ')<br />';
 echo '<label>Synced Custom Fields</label><span class="metric">', $m4is_e5246, '</span> (', human_time_diff($m4is_c1869['i2sdk_customfields']), ')<br />';
 echo '<label>Synced Tag Categories</label><span class="metric">', $m4is_r04, '</span> (', human_time_diff($m4is_c1869['tagcategories']), ')<br />';
 echo '<label>Synced Tags</label><span class="metric">', $m4is_c7260, '</span> (', human_time_diff($m4is_c1869['tags']), ')<br />';
 echo '<label>Synced Products</label><span class="metric">', m4is_v87365::m4is_i194(), '</span> (', human_time_diff($m4is_c1869['products']), ')<br />';
 echo '<label>Synced Subscription Plans</label><span class="metric">', $m4is_m8176, '</span><br />';
 echo '<label>Synced Invoices</label><span class="metric">', $m4is_c76, '</span> (', human_time_diff($m4is_c1869['invoices']), ')<br />';
 echo '<br />';
 echo '<label>Cached Affiliates</label><span class="metric">', $m4is_c30, '</span><br />';
 echo '<label>Cached Contacts</label><span class="metric">', $m4is_g6467, '</span><br />';
  echo '<label>Blocked Fields</label><span class="metric">', (empty($m4is_x64 )? "<strong class='membWarning'>0</strong>" : $m4is_x64 ), '</span><br />';
 echo '<label>Blocked Tag Categories</label><span class="metric">', (empty($m4is_p98 )? 0 : '<strong class="membWarning">' . $m4is_p98 .'</strong>' ), '</span><br />';
  echo '<p>';
 echo '<input type="submit" name="save" value="Synchronize Keap" class="button-primary"> ', m4is_h65::m4is_o64(8470 );
 echo '</p>';
 
}
}?>
<style>
	.membWarning {
		font-weight:bold;
		color:red;
	}
	.membGood {
		font-weight:bold;
		color:green;
	}
</style>
<?php

