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
class m4is_s6729 {
private object $m4is_r1546;
 private string $m4is_f4218;
 private string $m4is_r9613;
 private array $m4is_o56 =[];
 private array $m4is_q76 =[];
 private int $m4is_m32916 =0;
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
memberium_service_class::m4is_u684('app_admin', $this );
 $this->m4is_f4218 ='memberium';
 $this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 $this->m4is_m32916 =(int) $this->m4is_r1546->m4is_j498('settings', 'preview_mode' );
 $m4is_e03127 =(string) $this->m4is_r1546->m4is_j498('settings', 'version' );
 $this->m4is_y1648();
 add_action('admin_menu', [$this, 'm4is_p2651']);
  if(!method_exists($this->m4is_r1546->m4is_z40(), 'isVerified' )||!$this->m4is_r1546->m4is_z40()->isVerified()){
return;
 
}if($m4is_e03127 !== $this->m4is_r1546->m4is_w45()){
m4is_n17::m4is_l2147();
 
}if(!m4is_s52::m4is_f27()){
m4is_s52::m4is_a834();
 
}add_action('admin_enqueue_scripts', [$this, 'm4is_l9866'], 1);
 add_action('admin_init', [$this, 'm4is_b726'], 100);
 add_action('admin_notices', [$this, 'm4is_v83']);
 add_action('admin_post_memberium_scan_users', [$this, 'm4is_n84']);
 add_action('wpmu_new_blog', [$this, 'm4is_x67954'], 10, 6);
 add_filter('plugin_action_links', [$this, 'm4is_s716'], 10, 2);
 add_filter('plugin_action_links', [$this, 'm4is_x0231'], 10, 4);
 if(!m4is_s52::m4is_f27()){
return;
 
}add_action('wp_loaded', [$this, 'm4is_e85139'], 1 );
 add_action('admin_init', [$this, 'm4is_m34']);
 add_action('admin_print_scripts-edit.php', [$this, 'm4is_r602']);
 add_action('edit_form_after_title', [$this, 'm4is_a05668']);
 add_action('init', [$this, 'm4is_t1647'], 5 );
 add_action('init', [$this, 'm4is_j27'], 999999 );
 add_action('save_post', [$this, 'm4is_a0432'], 1, 3 );
   if(m4is_s52::m4is_b12067(['unlimited' ])){
if($this->m4is_r1546->m4is_j498('settings', 'fast_user_list' )){
add_filter('manage_users_columns', [$this, 'm4is_h04' ], PHP_INT_MAX );
 
}
} add_post_type_support('page', 'excerpt' );
 if(function_exists('wp_generate_password' )){
if(empty($this->m4is_r1546->m4is_j498('settings', 'random_seed' ))){
$this->m4is_r1546->m4is_d64918(wp_generate_password(16, FALSE, FALSE), 'settings', 'random_seed' );
 
}
}require_once ABSPATH . 'wp-admin/includes/plugin.php';
    add_filter('bulk_actions-edit-post', [$this, 'm4is_f53']);
 add_filter('bulk_actions-edit-page', [$this, 'm4is_f53']);
 add_filter('handle_bulk_actions-edit-post', [$this, 'm4is_u648'], 10, 3 );
 add_filter('handle_bulk_actions-edit-page', [$this, 'm4is_u648'], 10, 3 );
 add_filter('gutenberg_can_edit_post_type', [$this, 'm4is_d70'], PHP_INT_MAX, 2 );
 add_action('user_new_form', [$this, 'm4is_w8471']);
 if(isset($_GET['memberium_ignore_notice'])){
add_action('init', [$this, 'm4is_y7364']);
 
}$this->m4is_d4861();
  
} private 
function m4is_d4861(): void {
$this->m4is_q270();
 $this->m4is_p94();
 add_action('admin_init', [$this, 'm4is_n8660'], 10, 0 );
 add_action('shutdown', [$this, 'm4is_p36198'], 0 );
 add_filter('site_status_tests', [$this, 'm4is_e02'], 1, 1 );
 add_filter('http_response', [$this, 'm4is_q25981'], 1, 3 );
 add_filter('pre_http_request', [$this, 'm4is_b05796'], 1, 3 );
 add_filter('manage_users_columns', [$this, 'm4is_y265']);
 add_filter('manage_users_custom_column', [$this, 'm4is_z01'], 10, 3 );
 add_filter('random_password', [$this, 'm4is_n738'], 30, 4 );
 
}public 
function m4is_p36198(): void {
m4is_p40::m4is_h06();
 
} private 
function m4is_y1648(){
$classes =['m4is_c89756' =>'classes/ui/admin_banner', 'm4is_o9186' =>'classes/access/admin-menu', 'm4is_a98361' =>'classes/ui/admin-post-edit', 'm4is_s31' =>'classes/ui/admin-tabs', 'm4is_m749' =>'classes/access/admin-taxonomy', 'm4is_h65' =>'classes/ui/admin-ui', 'm4is_e41' =>'screens/user-edit', 'm4is_m32784' =>'classes/access/admin-widgets', 'm4is_u4569' =>'classes/diagnostics', 'm4is_b43' =>'classes/post_sherpa', ];
 $this->m4is_r1546->m4is_p39($classes );
 
}    public 
function m4is_n18664(): bool {
return (bool) $this->m4is_r1546->m4is_j498('settings', 'show_advanced_options', 0 );
 
}public 
function m4is_o2636(): int {
$m4is_b4068 =0;
 $m4is_b4068 =empty($_POST['post_ID'])? $m4is_b4068 : (int) $_POST['post_ID'];
 $m4is_b4068 =empty($_GET['post'])? $m4is_b4068 : (int) $_GET['post'];
 return $m4is_b4068;
  
}  
function m4is_m6180(): string {
$m4is_j0361 =isset($_GET['post_type'])? $_GET['post_type']: '';
 $m4is_j0361 =empty($m4is_j0361 )? get_post_type($this->m4is_o2636()): $m4is_j0361;
 $m4is_j0361 =empty($m4is_j0361 )? 'post' : $m4is_j0361;
 return $m4is_j0361;
 
}    public 
function m4is_w54728(int $m4is_b4068, string $m4is_g489, string $m4is_c05328, $m4is_m3605 ='edit_posts' ): bool {
if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE){
return false;
 
}if(empty($_POST[$m4is_g489])){
return false;
 
}if(!wp_verify_nonce($_POST[$m4is_g489], $m4is_c05328 )){
error_log(sprintf('Memberium: [warning] Permission to save Post denied.  Invalid nonce in save request for Post ID %d.', $m4is_b4068 ));
 return false;
 
}$m4is_m3605 =is_array($m4is_m3605 )? $m4is_m3605 : array_filter(explode(',', $m4is_m3605 ));
 foreach($m4is_m3605 as $m4is_d51 ){
if(current_user_can($m4is_d51, $m4is_b4068 )){
return true;
 
}
}error_log(sprintf('Memberium: [warning] Permission to save Post denied.  User %d does not have permissions to edit Post ID %d.', get_current_user_id(), $m4is_b4068 ));
 return false;
 
} public 
function m4is_q72456(int $m4is_b4068, array $m4is_u450, array $m4is_x39 ): void {
foreach ($m4is_u450 as $m4is_l9671 ){
if(isset($m4is_x39[$m4is_l9671])){
if(empty($m4is_x39[$m4is_l9671])){
delete_post_meta($m4is_b4068, $m4is_l9671 );
 
}else{
update_post_meta($m4is_b4068, $m4is_l9671, $m4is_x39[$m4is_l9671]);
 
}
}
}
}    
function m4is_f53(array $m4is_j15 ): array {
$m4is_j15['memberium-page-export']='Page Export';
 return $m4is_j15;
 
}
function m4is_u648(string $m4is_c93, string $m4is_d36852, $m4is_k26): string {
if($m4is_d36852 == 'memberium-page-export' ){
$m4is_s8491 =m4is_b43::m4is_j76($m4is_k26 );
 return get_admin_url(). 'upload.php?item=' . (int) $m4is_s8491;
 return get_permalink($m4is_s8491);
 
}return $m4is_c93;
 
}
function m4is_s716(array $m4is_n4651, string $m4is_k86914 ){
if(stripos($m4is_k86914, '/memberium2.php' )=== false ){
return $m4is_n4651;
 
}$m4is_t752 =get_admin_url(null, 'admin.php?page=memberium-support&tab=support' );
 $m4is_u66139 =get_admin_url(null, 'admin.php?page=memberium-support&tab=updates' );
 $m4is_n4651['updates']=sprintf('<a href="%s"> %s </a>', $m4is_u66139, __('Check Updates', 'plugin_domain' ));
 $m4is_n4651['support']=sprintf('<a href="%s"> %s </a>', $m4is_t752, __('Support', 'plugin_domain' ));
  return $m4is_n4651;
 
}
function m4is_d70(bool $m4is_y35, string $m4is_q485): bool {
if($m4is_y35){
$m4is_c68 =['memb_shortcodeblocks', 'partials', ];
 $m4is_q485 =strtolower($m4is_q485);
 $m4is_y35 =!in_array($m4is_q485, $m4is_c68);
 
}return $m4is_y35;
 
}
function m4is_x0231(array $m4is_j15, string $m4is_h049, $m4is_l4606, $m4is_s90): array {
if($m4is_h049 == 'i2sdk2/i2sdk2.php'){
$m4is_j15['activate']='<em style="color:red;">No longer needed.  Please delete.</em>';
 
}return $m4is_j15;
 
}   
function m4is_t1647(){
add_action('personal_options_update', [$this, 'm4is_y95'], 1 );
 add_action('edit_user_profile_update', [$this, 'm4is_y95'], 1 );
 add_filter('get_sample_permalink_html', [$this, 'm4is_t548'], 10, 5 );
 if(current_user_can('manage_options' )){
add_action('delete_user', ['m4is_z37620', 'm4is_y66'], 10, 3 );
 add_action('load-users.php', [$this, 'm4is_d2407']);
 add_action('edit_user_profile', [$this, 'm4is_v918'], PHP_INT_MIN );
 add_action('show_user_profile', [$this, 'm4is_v918']);
 
}if(current_user_can('manage_options' )){
$m4is_x8341 =defined('MEMBERIUM_BETA' )&&constant('MEMBERIUM_BETA' )== 1;
 if($m4is_x8341 ){
add_action('admin_notices', [$this, 'm4is_w3298']);
 
}
}if(is_plugin_active('memberium2-installer/memberium2-installer.php' )){
deactivate_plugins('memberium2-installer/memberium2-installer.php' );
 
}
}
function m4is_t548($m4is_h248, $m4is_b4068, $m4is_j83, $new_slug, $m4is_m5907 ){
if(property_exists($m4is_m5907, 'post_type' )){
if(in_array($m4is_m5907->post_type, ['partials', 'memb_shortcodeblocks'])){
return null;
 
}
}return $m4is_h248;
 
}
function m4is_j27(){
$this->m4is_o56 =$this->m4is_r1546->m4is_z607();
 $m4is_q485 =isset($_GET['post_type'])? $_GET['post_type']: 'post';
 $m4is_x517 =!in_array($m4is_q485, $this->m4is_o56);
 $m4is_x517 =$m4is_x517 ||in_array($m4is_q485, ['memb_shortcodeblocks']);
 if($m4is_x517 ){
add_action('manage_pages_custom_column', [$this, 'm4is_m16'], 10, 2 );
 add_action('manage_posts_custom_column', [$this, 'm4is_m16'], 10, 2 );
   add_filter('manage_pages_columns', [$this, 'm4is_k5467']);
 add_filter('manage_posts_columns', [$this, 'm4is_k5467']);
 
}
}
function m4is_a05668(){
if(isset($_GET['post'])){
$m4is_m5907 =get_post($_GET['post']);
 if(empty($m4is_m5907->post_name )){
return;
 
}
}else{
return;
 
}$m4is_e63195 ='';
 if($m4is_m5907->post_type == 'partials' ){
$m4is_e63195 ='[memb_include_partial id=' . $m4is_m5907->ID . ']';
 
}elseif($m4is_m5907->post_type == 'memb_shortcodeblocks' ){
$m4is_e63195 ='[membc_' . $m4is_m5907->post_name . ']';
 
}if(!empty($m4is_e63195 )){
echo '<h2>', $m4is_e63195, '</h2>';
 
}
}   private 
function m4is_y658(){
global $wpdb;
 $m4is_v2613 ="DELETE FROM `{$wpdb->options
}` WHERE `option_name` LIKE '%transient_memberium%' OR `option_name` LIKE '%transient_timeout_memberium%' ; ";
 $wpdb->query($m4is_v2613);
 
}
function m4is_b962(){
global $wpdb;
 $this->m4is_y658();
 $m4is_y96521 =m4is_z13097::m4is_d176();
 $this->m4is_r1546->m4is_d64918($m4is_y96521, 'settings', 'referral_partner_order' );
 m4is_j4156::m4is_f7940();
 m4is_s695::m4is_r2903();
 m4is_z6894::m4is_d7492();
 m4is_k865::m4is_l26();
 m4is_u36::m4is_n603();
 m4is_v87365::m4is_a0846();
 m4is_v87365::m4is_x096();
 if(!empty($this->m4is_r1546->m4is_j498('settings', 'sync_ecommerce'))){
m4is_v87365::m4is_l318();
 
}$this->m4is_r1546->m4is_d126();
 $m4is_c1869 =get_option('memberium_tables_updated', []);
 $m4is_c1869['actionsets']=time();
 $m4is_c1869['i2sdk_customfields']=time();
 $m4is_c1869['tagcategories']=time();
 $m4is_c1869['tags']=time();
 $m4is_c1869['products']=time();
 $m4is_c1869['subscriptionplans']=time();
 update_option('memberium_tables_updated', $m4is_c1869);
 m4is_h65::m4is_z896('Keap Synchronized', 'update');
 
}
function m4is_h5012(){
global $wpdb;
 $m4is_p18 =get_option('memberium_tables', []);
 $m4is_p21 =[];
 $m4is_v2613 ='SHOW TABLES';
 $m4is_s766 =$wpdb->get_col($m4is_v2613 );
 foreach($m4is_p18 as $m4is_e80 ){
if(!in_array($m4is_e80, $m4is_s766 )){
$m4is_p21[]=$m4is_e80;
 
}
}return $m4is_p21;
 
}
function m4is_h7256(){
      
}
function m4is_l9866($m4is_e81053){
  $m4is_p95312 =plugin_dir_url(MEMBERIUM_HOME );
 $m4is_k1964 =false;
 $m4is_f85637 =$this->m4is_r1546->m4is_w45();
 $m4is_k1964 =$m4is_k1964 ||strpos($m4is_e81053, 'memberium')!== false;
 $m4is_f602 =['edit.php', 'post-new.php', 'post.php', 'user-edit.php', 'users.php' ];
 $m4is_f602 =apply_filters('memberium/enhanced_admin_scripts', $m4is_f602);
  $m4is_k1964 =$m4is_k1964 ||in_array($m4is_e81053, $m4is_f602);
 wp_register_style('memberium_admin_css', $m4is_p95312 . 'css/admin.css', false, $m4is_f85637, 'all');
 wp_enqueue_style('memberium_admin_css');
 wp_register_script('memberium_adminsettings', $m4is_p95312 . 'js/admin-settings.js', false, $m4is_f85637);
 wp_enqueue_script('memberium_adminsettings');
 wp_register_script('memberium_modal', $m4is_p95312 . 'js/admin-modal.js', false, $m4is_f85637);
 wp_enqueue_script('memberium_modal');
 if($m4is_k1964){
wp_register_style('wpal_s2css', $m4is_p95312 . 'css/wpal-select2.min.css', false, '4.0.3', 'all');
 wp_register_script('wpal_s2js', $m4is_p95312 . 'js/wpal-select2.full.min.js', ['jquery'], '4.0.3', true);
 wp_enqueue_style('wpal_s2css');
 wp_enqueue_script('wpal_s2js');
 wp_enqueue_style('font-awesome', 'https://use.fontawesome.com/releases/v5.12.0/css/all.css', false, '5.12.0');
 
}
}
function m4is_b726(){
 if(is_network_admin()||isset($_GET['activate-multi'])){
return;
 
}if(is_admin()){
$m4is_l9671 ='memberium/activation_timestamp';
 $m4is_y08149 =(time()- get_option($m4is_l9671, time()));
 if($m4is_y08149 < 5){
update_option($m4is_l9671, time());
  wp_safe_redirect(add_query_arg(['page' =>'memberium'], admin_url('admin.php')));
 exit;
 
}
}
}
function m4is_w3298(){
$m4is_j613 =$_GET;
 if(isset($m4is_j613['tab'])&&$m4is_j613['tab']== 'checklist' ||!current_user_can('manage_options')){
return;
 
}if(!get_user_meta($this->m4is_r1546->m4is_x66(), 'memberium_ignore_notice_checklist', true)){
$m4is_h723 =$this->m4is_f47(true);
 if(!empty($m4is_h723['active'])){
$m4is_c67 =m4is_h65::m4is_q62087('checklist');
 $m4is_s6347 ="updated";
 $m4is_a173 ='<h3 style="margin-bottom:6px;">Memberium Setup CheckList</h3>' . '<p>Your next setup checklist step is to ' . $m4is_h723['active'][0]['t']. '</p><p><a href="admin.php?page=dashboard&tab=checklist"><strong>Click here</strong></a> to return to the setup checklist.</p>' . '<div style="text-align:right;"><p><a href="'. $m4is_c67 .'">Hide these Reminders</a></p></div>';
 echo"<div class='{$m4is_s6347
}'>{$m4is_a173
}</div>";
 
}
}
}
function m4is_y7364(){
$m4is_j613 =$_GET;
 $m4is_f087 =$this->m4is_r1546->m4is_x66();
 $m4is_l9671 =isset($m4is_j613['memberium_ignore_notice'])? sanitize_text_field($m4is_j613['memberium_ignore_notice']): '';
 if(!empty($m4is_l9671)){
update_option("memberium/notice/{$m4is_l9671
}", 1);
 if(wp_get_referer()){
wp_safe_redirect(wp_get_referer());
 
}else{
wp_safe_redirect(site_url());
 
}
}
}
function m4is_v83(){
$m4is_c67 =m4is_h65::m4is_o64(1226);
 if(false &&!m4is_s52::m4is_f27()){
echo '<div class="error"><p><strong style="font-size:150%;color:red;">Memberium License Missing / Expired', $m4is_c67, '</strong></p>';
 echo '<p>For possible causes, <a target="_blank" href="https://memberium.com/?p=1226">click here</a>.</p>';
 echo '<p>If you have not yet purchased a license, please <a target="_blank" href="https://memberium.com/pricing/">purchase a license by clicking here</a></p>';
 echo '<p>If you already have a license please <a target="_blank" href="https://memberium.com/support/">contact support by clicking here</a>.</p></div>';
 
}
}private 
function m4is_f6209(){
$m4is_l36716 =false;
 $m4is_f087 =$this->m4is_r1546->m4is_x66();
 $m4is_l186 =array_filter(explode(',', $this->m4is_r1546->m4is_j498('settings', 'allow_wpadmin_dashboard' )));
 if(!empty($m4is_l186 )){
$m4is_l36716 =true;
 foreach ($m4is_l186 as $m4is_d51 ){
if(user_can($m4is_f087, $m4is_d51 )){
$m4is_l36716 =false;
 break;
 
}
}
}else{
if(!$m4is_l36716 ){
$m4is_r297 =$this->m4is_r1546->m4is_j498('settings', 'allow_wpadmin', false );
 if($m4is_r297 ){
$m4is_l36716 =false;
 
}else{
$m4is_l36716 =true;
 
}
}
}return !$m4is_l36716;
 
} 
function m4is_e85139(){
static $m4is_r2806 =false;
 $m4is_r2806 =$m4is_r2806 ||defined('DOING_AJAX' )&&DOING_AJAX;
 $m4is_r2806 =$m4is_r2806 ||function_exists('wp_doing_ajax' )&&wp_doing_ajax();
 $m4is_r2806 =$m4is_r2806 ||isset($_SERVER['SCRIPT_NAME'])&&(basename($_SERVER['SCRIPT_NAME'])== 'admin-post.php' );
 $m4is_r2806 =$m4is_r2806 ||$this->m4is_r1546->m4is_v461();
 $m4is_r2806 =$m4is_r2806 ||$this->m4is_f6209();
 $m4is_r2806 =apply_filters('memberium/wpadmin/allow', $m4is_r2806 );
 if($m4is_r2806 ){
return;
 
}   $m4is_f56 =wp_login_url($_SERVER['REQUEST_URI']);
  if(is_user_logged_in()){
$m4is_k824 =m4is_q82::m4is_d59($this->m4is_r1546->m4is_x66());
 $m4is_k9075 =get_usermeta($this->m4is_r1546->m4is_x66(), 'login_count', 0 );
 if($m4is_k9075 == 1 ){
$m4is_v27639 =isset($m4is_k824['memb_user']['first_login_page'])? $m4is_k824['memb_user']['first_login_page']: 0;
 
}else{
$m4is_v27639 =isset($m4is_k824['memb_user']['login_page'])? $m4is_k824['memb_user']['login_page']: 0;
 
}if($m4is_v27639 === -1){
if(function_exists('bbp_get_user_profile_url' )){
$m4is_f56 =bbp_get_user_profile_url(get_current_user_id());
 
}else{
$m4is_v27639 =0;
 
}
}if($m4is_v27639 > 0){
$m4is_f56 =get_permalink($m4is_v27639 );
 
}else{
$m4is_f56 =get_site_url();
 
} $m4is_n23641 =function_exists('get_current_screen' )? get_current_screen(): false;
 if($m4is_n23641 &&$m4is_n23641->id == 'user-edit' ){
if(function_exists('bp_loggedin_user_domain')){
$m4is_f56 =bp_loggedin_user_domain();
 
}else{
$pages =get_option('memberium_pages', []);
 $m4is_f56 =isset($pages['profile_page'])? get_permalink($pages['profile_page']): $m4is_f56;
 
}
}
}if(!$m4is_f56 ){
$m4is_f56 =get_site_url();
 
}wp_redirect($m4is_f56, 302, 'Memberium Admin Dashboard Protection' );
 exit;
 
}   
function m4is_x67954($m4is_s740, $m4is_f087, $m4is_g36127, $m4is_d04266, $m4is_o351, $m4is_r1692 ){
global $wpdb;
 if(is_plugin_active_for_network('memberium2/memberium2.php')){
$m4is_e75469 =$wpdb->blogid;
 switch_to_blog($m4is_s740);
 m4is_n17::m4is_z126(false );
 switch_to_blog($m4is_e75469);
 
}
}   public 
function admin_menu_alert($count =0, $text ='' ){
$count =(int) $count;
 if(!$count ){
return;
 
}if(empty($text )){
$text =$count;
 
}return "<span class='update-plugins count-{$count
}' title='{$text
}'><span class='update-count'>{$text
}</span></span>";
 
} public 
function admin_menu_logcount(): int {
global $wpdb;
 static $m4is_h973;
 $m4is_l8307 ='memberium/log_count';
 $m4is_q436 =3 * MINUTE_IN_SECONDS;
 $m4is_h973 =(int) get_transient($m4is_l8307 );
 if(!$m4is_h973 ){
$m4is_v2613 ="SELECT count(*) FROM %i WHERE `appname` = %s";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_l5841::m4is_h37(), $this->m4is_r9613 );
 $m4is_h973 =(int) $wpdb->get_var($m4is_v2613 );
 $m4is_v2613 ="SELECT count(*) FROM %i WHERE `appname` = %s";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_q62395::m4is_t66507(), $this->m4is_r9613 );
 $m4is_h973 =$m4is_h973 + (Int) $wpdb->get_var($m4is_v2613 );
 set_transient($m4is_l8307, $m4is_h973, $m4is_q436 );
 
}return $m4is_h973;
 
} private 
function m4is_e7663(): int {
static $m4is_h973;
 if($m4is_h973 ){
return $m4is_h973;
 
}$m4is_x39508 =$this->m4is_r1546->get_i2sdk_options();
 $m4is_h973 =$m4is_x39508['server_verified']? 0 : 1;
 $m4is_h973 =$m4is_h973 + empty($m4is_x39508['app_name'])? 1 : 0;
 $m4is_h973 =$m4is_h973 + empty($m4is_x39508['api_key'])? 1 : 0;
 $m4is_h973 =$m4is_h973 + class_exists('i2sdk_class' )? 0 : 1;
 return $m4is_h973;
 
}public 
function m4is_p2651(){
$m4is_q20175 ='manage_options';
 if(!current_user_can($m4is_q20175)){
return;
 
}$m4is_i406 =m4is_s52::m4is_f27();
 $m4is_d60695 ='dashicons-groups';
  $m4is_x39508 =$this->m4is_r1546->get_i2sdk_options();
 $m4is_x160 ='memberium';
 $m4is_b9263 =$m4is_q20175;
 $m4is_l764 =2;
 $m4is_b3785 =$this->m4is_e7663();
  if(!empty($m4is_b3785)){
add_menu_page('', 'Memberium ' . $this->admin_menu_alert(1, 'Alert'), $m4is_q20175, $m4is_x160, [$this, 'm4is_v669'], $m4is_d60695, $m4is_l764);
 add_submenu_page($m4is_x160, 'Memberium Support', 'Support', $m4is_b9263, 'memberium-support', [$this, 'm4is_z9320']);
 if(class_exists('i2sdk_class')){
add_submenu_page($m4is_x160, 'Keap Connection', 'Keap Connection ' . $this->admin_menu_alert(1, 'Unconfigured'), $m4is_q20175, 'i2sdk-admin', ['i2sdk_admin_menu_class', 'display_i2sdk_admin_menu']);
 
}return;
 
} if(!$m4is_i406){
add_menu_page('Memberium', 'Memberium', $m4is_q20175, $m4is_x160, [$this, 'm4is_v669'], $m4is_d60695, $m4is_l764);
 add_submenu_page($m4is_x160, 'Memberium Support', 'Support', $m4is_b9263, 'memberium-support', [$this, 'm4is_z9320']);
 add_submenu_page($m4is_x160, 'Keap Connection', 'Keap Connection', $m4is_q20175, 'i2sdk-admin', ['i2sdk_admin_menu_class', 'display_i2sdk_admin_menu']);
 add_submenu_page($m4is_x160, '', '', $m4is_q20175, $m4is_x160, [$this, 'm4is_u57638']);
 return;
 
} if(!class_exists('i2sdk_class')||!method_exists($this->m4is_r1546->m4is_z40(), 'isVerified')||!$this->m4is_r1546->m4is_z40()->isVerified()){
add_menu_page('Memberium', 'Memberium', $m4is_q20175, $m4is_x160, [$this, 'm4is_v669'], $m4is_d60695, $m4is_l764);
 add_submenu_page($m4is_x160, 'Memberium Support', 'Support', $m4is_b9263, 'memberium-support', [$this, 'm4is_z9320']);
 if(class_exists('i2sdk_class')){
add_submenu_page($m4is_x160, 'Keap Connection', 'Keap Connection', $m4is_q20175, 'i2sdk-admin', ['i2sdk_admin_menu_class', 'display_i2sdk_admin_menu']);
 
}return;
 
} add_menu_page('', 'Memberium', $m4is_b9263, $m4is_x160, '', $m4is_d60695, $m4is_l764 );
 add_submenu_page($m4is_x160, 'Start Here', 'Start Here', $m4is_b9263, $m4is_x160, [$this, 'm4is_u57638']);
 add_submenu_page($m4is_x160, 'Memberium Support', 'Support', $m4is_b9263, 'memberium-support', [$this, 'm4is_z9320']);
 add_submenu_page($m4is_x160, 'Memberium Settings', 'Settings', $m4is_b9263, 'memberium-options', [$this, 'm4is_l1308']);
 add_submenu_page($m4is_x160, 'Memberium Memberships', 'Memberships', $m4is_b9263, 'memberium-memberships', [$this, 'm4is_b2646']);
 add_submenu_page($m4is_x160, 'Memberium Partials', 'Partials', $m4is_b9263, 'edit.php?post_type=partials');
 add_submenu_page($m4is_x160, 'Memberium Custom Shortcodes', 'Custom Shortcodes', $m4is_b9263, 'edit.php?post_type=memb_shortcodeblocks');
 add_submenu_page($m4is_x160, 'Memberium eCommerce Integration', 'eCommerce', $m4is_b9263, 'memberium-ecommerce', [$this, 'm4is_q158']);
 add_submenu_page($m4is_x160, 'Memberium Remote Files Configuration', 'Remote Files', $m4is_b9263, 'memberium-remote-files', [$this, 'm4is_g09518']);
 add_submenu_page($m4is_x160, 'Sync Options', 'Sync Options', $m4is_b9263, 'memberium-sync-options', [$this, 'm4is_c30871']);
 do_action('memberium_admin_menu_addons', $m4is_x160);
 if(defined('WPSEO_FILE')||defined('GAWP_FILE')){
add_submenu_page($m4is_x160, 'Memberium Google Analytics', 'Google Analytics', $m4is_q20175, 'memberium-ga', [$this, 'm4is_m663']);
 
}$m4is_b3785 =$this->admin_menu_logcount();
 add_submenu_page($m4is_x160, 'Memberium Logs', 'Logs '. $this->admin_menu_alert($m4is_b3785), $m4is_q20175, 'memberium-logs', [$this, 'm4is_d69078']);
 add_submenu_page($m4is_x160, 'Keap Connection', 'Keap Connection', $m4is_q20175, 'i2sdk-admin', ['i2sdk_admin_menu_class', 'display_i2sdk_admin_menu']);
  add_submenu_page('', '', '', $m4is_b9263, 'dashboard', [$this, 'm4is_u57638']);
 add_submenu_page('', '', '', $m4is_b9263, 'memberium-welcome-screen', [$this, 'm4is_u57638']);
 
}    
function m4is_n8660(): void {
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}if(isset($_POST['manual_upgrade'])&&isset($_POST['manual_upgrade_confirm'])){
$this->m4is_k89();
 
}
} private 
function m4is_k89(): void {
if($_POST['manual_upgrade_confirm']!== 'download' ){
return;
 
}$m4is_z9157 =m4is_l9685::m4is_f358();
 $m4is_d07693 =isset($_POST['manual_upgrade'])? (int) $_POST['manual_upgrade']: 0;
 if(!array_key_exists($m4is_d07693, $m4is_z9157 )||empty($m4is_z9157[$m4is_d07693]['url'])){
m4is_h65::m4is_z896('<p><strong>Memberium</strong>:  Unable to download update</p>', 'error' );
 return;
 
}wp_redirect($m4is_z9157[$m4is_d07693]['url'], 302, 'Memberium Update Download' );
 exit;
 
} 
function m4is_z708($m4is_c71 ='memberium2' ): string {
$m4is_z9157 =m4is_l9685::m4is_f358();
 $m4is_w390 =$this->m4is_r1546->m4is_w45();
 $m4is_n0716 =m4is_l9685::m4is_b45381();
 $m4is_u626 ='';
 $m4is_p691 ='';
 if(is_array($m4is_z9157 )){
foreach ($m4is_z9157 as $m4is_d07693=>$m4is_a686 ){
$m4is_a437 =$m4is_w390 == $m4is_a686['version']? ' selected="selected" ' : '';
 $m4is_u626 .= sprintf('<option value="%d" %s>%s %s</option>', $m4is_d07693, $m4is_a437, $m4is_a686['name'], $m4is_a686['comments']);
 
}
}if($m4is_n0716 ){
$m4is_p691 =<<<HTMLBLOCK
				<option value="">Choose your option</option>
				<option value="download">Download</option>
				<option value="install">Install</option>
			HTMLBLOCK;
 
}else{
$m4is_p691 =<<<HTMLBLOCK
				<option value="">Choose your option</option>
				<option value="download">Download</option>
			HTMLBLOCK;
 
}$output =<<<HTMLBLOCK
			<select name="manual_upgrade" style="width:500px !important; margin-bottom:6px;">
				{$m4is_u626
}
			</select><br>
			<select name="manual_upgrade_confirm" style="width:250px !important; margin-bottom:6px;">
				{$m4is_p691
}
			</select>
		HTMLBLOCK;
 return $output;
 
} 
function m4is_p2660(int $m4is_t30467, string $m4is_c71 ){
if(!m4is_l9685::m4is_b45381()){
return;
 
}$m4is_z9157 =m4is_l9685::m4is_f358();
 if(isset($m4is_z9157[$m4is_t30467]['url'])){
$m4is_j04866 =WP_PLUGIN_DIR;
 $m4is_j50281 =ABSPATH . '.maintenance';
 $m4is_l3766 ='PD9waHAgJHVwZ3JhZGluZyA9IHRpbWUoKTs=';
  $m4is_r712 =$m4is_z9157[$m4is_t30467]['url'];
  ignore_user_abort();
  $m4is_z245 =$m4is_n548 =download_url($m4is_r712, 300 );
 if(is_wp_error($m4is_z245 )){
m4is_h65::m4is_z896('<p><strong>Memberium</strong>:  Unable to download update</p>', 'error' );
 return;
 
} if(file_exists($m4is_z245 )){
  require_once ABSPATH .'/wp-admin/includes/file.php';
  WP_Filesystem();
 file_put_contents($m4is_j50281, $m4is_l3766 );
 if(!function_exists('disk_free_space' )){
add_filter('wp_doing_cron', '__return_false', 10, 1 );
 
}unzip_file($m4is_z245, $m4is_j04866);
 remove_filter('wp_doing_cron', '__return_false', 10 );
 unlink(ABSPATH . '.maintenance');
 unlink($m4is_z245);
 
}if(function_exists('opcache_reset' )){
opcache_reset();
 
}echo '<p>Upgrade Process Completed.</p>';
 echo '<p><a href="', admin_url(), 'admin.php?page=memberium">Continue</a></p>';
 exit;
 
}else{
m4is_h65::m4is_z896('<p><strong>Memberium</strong>:  Unable to install update</p>', 'error');
 
}
}    public 
function m4is_s6613($m4is_u897 =[]): string {
global $wpdb;
 $m4is_o14 ='memberium/posts';
 $m4is_q1046 ='admin/list/json';
 $m4is_s82753 =15;
 $m4is_k0941 =wp_cache_get($m4is_q1046, $m4is_o14, false, $m4is_t265 );
 if($m4is_t265 ){
return $m4is_k0941;
 
}else{
$m4is_k0941 =[];
 
}$m4is_n210 =defined('MEMBERIUM_IGNORE_POSTTYPE' )? constant('MEMBERIUM_IGNORE_POSTTYPE' ): '';
 $m4is_y642 =['exclude' =>'topic,reply' . empty($m4is_n210 )? '' : ',' . $m4is_n210, 'entries' =>[], ];
 $m4is_u897 =wp_parse_args($m4is_u897, $m4is_y642);
 $m4is_u897['exclude']="'" . implode("','", explode(',', $m4is_u897['exclude'])). "'";
 unset($m4is_y642);
 $m4is_v2613 ="SELECT `ID`, `post_title` FROM `{$wpdb->posts
}` WHERE `post_status` = 'publish' AND `post_type` IN ('" . implode("','", m4is_h65::m4is_k58()). "') AND `post_type` NOT IN (" . $m4is_u897['exclude']. ") ORDER BY `id` ASC;";
 $m4is_f602 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 $m4is_k0941[]=['id' =>0, 'text' =>'(Default / Homepage)' ];
 foreach ($m4is_f602 as $m4is_d07693=>$m4is_d3012){
$m4is_k0941[]=['id' =>$m4is_d3012['ID'], 'text' =>"{$m4is_d3012['post_title']
} ({$m4is_d3012['ID']
})", ];
 unset($m4is_f602[$m4is_d07693]);
 
}unset($m4is_d3012, $m4is_v2613);
 $m4is_k0941 =json_encode($m4is_k0941, JSON_INVALID_UTF8_SUBSTITUTE);
 wp_cache_set($m4is_q1046, $m4is_k0941, $m4is_o14, $m4is_s82753 );
 return $m4is_k0941;
 
}  public 
function m4is_g641($m4is_y66291 =[]): string {
$m4is_l17096 =get_user_by('id', $this->m4is_r1546->m4is_x66());
 $m4is_e6432 =$m4is_l17096->allcaps;
 $m4is_m3605 =[];
 foreach($m4is_e6432 as $m4is_j09 =>$m4is_t25641 ){
$m4is_m3605[]=['id' =>$m4is_j09, 'text' =>ucwords(strtr($m4is_j09, '_', ' ' ))];
 
}return json_encode($m4is_m3605 );
 
}   public 
function m4is_b543(): void {
$m4is_a173 ='';
 $m4is_u698 =$this->m4is_r1546->m4is_z40()->isVerified();
 if($m4is_u698 ){
$m4is_f04 =m4is_s52::m4is_f27();
 if(!$m4is_f04 ){
$m4is_a173 .= <<<HTMLBLOCK
					<p style="color:red;">
						<strong>License Missing</strong>
					</p>
					<p>
						This site is missing a valid license.   Please contact Memberium support with your domain name, and we will help you get your license activated.<br>
					</p>
					<hr />
				HTMLBLOCK;
 
}
}$m4is_s905 ='7.4';
 $m4is_q70264 ='8.2';
 $m4is_t24096 =phpversion();
 $m4is_h3725 =version_compare(PHP_VERSION, $m4is_s905, '>=' );
 if(!$m4is_h3725 ){
$m4is_a173 .= <<<HTMLBLOCK
				<p>
					Your hosting service PHP install (v{$m4is_t24096
}) is out of date.  Your current version is missing features required by Memberium, and may cause crashes.<br>
					<strong>Please update to PHP v{$m4is_s905
} or later.  We recommend v{$m4is_s905
} - v{$m4is_q70264
}.</strong><br>
					If you have questions, please contact Memberium Support.
				</p>
			HTMLBLOCK;
 
}$m4is_s905 ='6.2';
 $m4is_c36751 =$this->m4is_r1546->m4is_s0578();
 $m4is_d2649 =version_compare($m4is_c36751, $m4is_s905, '>=' );
 if(!$m4is_d2649 ){
$m4is_a173 .= <<<HTMLBLOCK
				<p>
				Your WordPress install (v{$m4is_c36751
}) is out of date.  Your old version is missing features used by Memberium.<br>
				<strong>Please update to WordPress v{$m4is_s905
} or later to ensure maximum reliability.</strong><br>
				If you have questions, please contact Memberium Support.
				</p>
			HTMLBLOCK;
 
}if(empty($m4is_a173 )){
return;
 
}echo <<<HTMLBLOCK
			<div id="memberium-custom-modal" class="memberium-custom-modal">
				<div class="memberium-custom-modal-content">
					<h3>Memberium System Health Alerts</h3>
					{$m4is_a173
}
					<span class="memberium-custom-close">&times;</span>
				</div>
			</div>
		HTMLBLOCK;
 
} public 
function m4is_m34(): void {
$m4is_q485 =$this->m4is_m6180();
 add_action('admin_footer', [$this, 'm4is_n68517']);
 add_action('admin_footer', [$this, 'm4is_b543']);
 if(in_array($m4is_q485, $this->m4is_o56 )){
return;
 
}add_meta_box('is4wp-member-access' , 'Memberium Protection', [$this, 'm4is_s3976'], $m4is_q485, 'side' );
 add_meta_box('is4wp-page-templates', 'Membership Templates', [$this, 'm4is_e243'], $m4is_q485, 'normal' );
 add_meta_box('is4wp-course-grid', 'Membership Course Grid', [$this, 'm4is_d7506'], $m4is_q485, 'side' );
 add_action('save_post', [$this, 'm4is_h076']);
 add_action('save_post', [$this, 'm4is_l13669']);
 add_action('save_post', [$this, 'm4is_y10']);
 if(in_array($m4is_q485, ['partials', 'memb_shortcodeblocks', 'elementor_library'])){
return;
 
}if(!current_user_can('manage_options' )){
return;
 
}$m4is_q76 =(array)m4is_h65::m4is_k58();
 if(!in_array($m4is_q485, $m4is_q76 )){
return;
 
}add_meta_box('is4wp-custom-code', 'Memberium Custom Page Code', [$this, 'm4is_y9035'], $m4is_q485, 'normal' );
 add_action('save_post', [$this, 'm4is_s69']);
 
} public 
function m4is_n68517(): void {
if(!method_exists('m4is_k865', 'm4is_z2906' )){
return;
 
}echo '<!-- metabox footer -->';
  $m4is_l9321 =m4is_k865::m4is_z2906(true );
 $m4is_l9321 =$m4is_l9321['mc'];
 $m4is_z470 =[];
 $m4is_z470[]=['id' =>0, 'text' =>'(None)' ];
 foreach ((array)$m4is_l9321 as $m4is_h8269 =>$m4is_p786){
$m4is_z470[]=['id' =>$m4is_h8269, 'text' =>"{$m4is_p786
} ({$m4is_h8269
})" ];
 
}$m4is_s5498 =[];
 $m4is_s5498[]=['id' =>0, 'text' =>'(None)' ];
 foreach ((array)$m4is_l9321 as $m4is_h8269 =>$m4is_p786){
$m4is_s5498[]=['id' =>$m4is_h8269, 'text' =>"{$m4is_p786
} ({$m4is_h8269
})" ];
 $m4is_s5498[]=['id' =>- $m4is_h8269, 'text' =>"Not {$m4is_p786
} (-{$m4is_h8269
})" ];
 
}$m4is_z470 =json_encode($m4is_z470 );
 $m4is_s5498 =json_encode($m4is_s5498 );
 unset($m4is_l9321, $m4is_h8269, $m4is_p786 );
 echo '<script>';
 echo '	var taglist = ', $m4is_z470, ';';
 echo '	var taglist2 = ', $m4is_s5498, ';';
 echo '	var memb_coursegrid_i18n = '. json_encode(['locked' =>['title' =>__('Select Locked Course Thumbnail', 'memberium'), 'button' =>__('Set Locked Course Thumbnail', 'memberium'), 'remove' =>__('Remove Locked Course Thumbnail', 'memberium'), ], 'unlocked' =>['title' =>__('Select Unlocked Course Thumbnail', 'memberium'), 'button' =>__('Set Unlocked Course Thumbnail', 'memberium'), 'remove' =>__('Remove Unlocked Course Thumbnail', 'memberium'), ]]).';';
 echo '</script>';
 unset($actionsets, $m4is_l9321);
 $m4is_e03127 =$this->m4is_r1546->m4is_j498('settings', 'version' );
 wp_register_script('memberium_postmeta', plugin_dir_url(MEMBERIUM_HOME ). 'js/postmetabox.js', [], $m4is_e03127, true);
 wp_enqueue_script('memberium_postmeta');
 
} public 
function m4is_e243(): void {
$m4is_p8310 =$this->m4is_n6203();
 echo '<div class="memb_template_options">';
 echo '<label for="_is4wp_page_template">' . _e("Install Page Template", 'memberium'). '</label> ';
 echo '<select class="actionset-selector" name="_is4wp_page_template" style="width:100%; max-width:100%">';
 echo '<option value="">(No Template)</option>';
 if(is_array($m4is_p8310 )){
foreach($m4is_p8310 as $m4is_o809 =>$m4is_p6925 ){
echo '<option value="', $m4is_o809 + 1, '">', $m4is_p6925['name'], '</option>';
 
}
}echo '</select>';
 echo '</div>';
 
} public 
function m4is_l13669(): void {
$m4is_m5907 =$_POST;
 if(!empty($m4is_m5907['_is4wp_page_template'])){
$this->m4is_o68190($m4is_m5907['post_ID'], ($m4is_m5907['_is4wp_page_template']- 1 ));
 
}
}
function m4is_d7506(){
global $post;
 $m4is_r1692 =get_post_meta($post->ID, '_memberium/coursegrid/config', true );
 $m4is_x265 =['unlocked' =>__('Unlocked', 'memberium'), 'locked' =>__('Locked', 'memberium')];
 echo '<div class="memb_coursegrid_options">';
 wp_nonce_field($this->m4is_r1546->m4is_j541(), "memberium_coursegrid_nonce_{$post->ID
}");
 $m4is_d786 ='_memb_coursegrid';
 foreach ($m4is_x265 as $m4is_o015 =>$m4is_w42){
$m4is_e456 =empty($m4is_r1692[$m4is_o015])? '' : $m4is_r1692[$m4is_o015];
 $m4is_e98510 =empty($m4is_e456['url'])? '' : esc_url($m4is_e456['url']);
 $m4is_t64375 =empty($m4is_e456['id'])? 0 : (int) $m4is_e456['id'];
 echo "<div class=\"memb_coursegrid_thumbnail\" data-key=\"{$m4is_o015
}\">";
 if(!empty($m4is_e98510 )){
$m4is_s60 =sprintf(__('Remove %s Course Thumbnail', 'memberium' ), $m4is_w42 );
 echo "<a class=\"memb_coursegrid_thumbnail_remove\" href=\"#\" title=\"{$m4is_s60
}\">";
 echo "<span class=\"dashicons dashicons-dismiss\"></span>";
 echo "</a>";
 
}echo "<br><label for=\"{$m4is_o015
}_url\">" . sprintf(__('%s Thumbnail', 'memberium' ), $m4is_w42). "</label> ";
 echo "<div class=\"memb_coursegrid_preview\">";
 echo !empty($m4is_e98510)? "<img src=\"{$m4is_e98510
}\"/>" : '';
 echo "</div>";
 echo "<div class=\"memb_coursegrid_input_button_wrap wp-clearfix\">";
 echo "<input type=\"url\" class=\"large-text memb_coursegrid_thumbnail_url\" name=\"{$m4is_d786
}[{$m4is_o015
}][url]\" id=\"{$m4is_o015
}_url\" value=\"{$m4is_e98510
}\">";
 echo "<button type=\"button\" class=\"button memb_coursegrid_thumbnail_upload\" id=\"{$m4is_e456
}_upload\">";
 echo "<span class=\"dashicons dashicons-format-image\"></span>";
 echo "<span class=\"screen-reader-text\">";
 echo sprintf(__('Set %s Thumbnail', 'memberium' ), $m4is_w42 );
 echo "</span>";
 echo "</button>";
 echo "</div>";
 echo "<input type=\"hidden\" class=\"memb_coursegrid_thumbnail_id\" name=\"{$m4is_d786
}[{$m4is_o015
}][id]\" id=\"{$m4is_o015
}_id\" value=\"{$m4is_t64375
}\">";
 echo "</div>";
 
}$m4is_t2686 =empty($m4is_r1692['locked_url'])? '' : esc_url($m4is_r1692['locked_url']);
  $m4is_i3208 =empty($m4is_r1692['excerpt'])? '' : $m4is_r1692['excerpt'];
  $m4is_y5760 =empty($m4is_r1692['order'])? 0 : $m4is_r1692['order'];
  echo "<br><label for=\"memb_coursegrid_locked_url\">" . __('Locked Course URL', 'memberium' ). "</label> ";
 echo "<input name=\"{$m4is_d786
}[locked_url]\" type=\"text\" id=\"memb_coursegrid_locked_url\" value=\"{$m4is_t2686
}\" class=\"widefat\">";
 echo "<br><br><label for=\"memb_coursegrid_excerpt\">" . __('Course Excerpt', 'memberium' ). "</label> ";
 echo "<textarea id=\"memb_coursegrid_excerpt\" rows=\"4\" name=\"{$m4is_d786
}[excerpt]\" class=\"widefat\">{$m4is_i3208
}</textarea>";
 echo "<br><br><label for=\"memb_coursegrid_order\">" . __('Course Order', 'memberium' ). "</label> ";
 echo "<input name=\"{$m4is_d786
}[order]\" type=\"number\" max=\"9999\" id=\"memb_coursegrid_order\" value=\"{$m4is_y5760
}\">";
 echo '</div>';
 
}
function m4is_y10($m4is_b4068 ){
 if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE){
return;
 
}$m4is_t475 =$_POST;
  if(empty($m4is_t475["memberium_coursegrid_nonce_{$m4is_b4068
}"])||!wp_verify_nonce($m4is_t475["memberium_coursegrid_nonce_{$m4is_b4068
}"], $this->m4is_r1546->m4is_j541())){
return;
 
} if(!empty($m4is_t475['post_type'])&&'page' == $m4is_t475['post_type']){
if(!current_user_can('edit_pages', $m4is_b4068)){
return;
 
}
}else{
if(!current_user_can('edit_posts', $m4is_b4068)){
return;
 
}
} $m4is_l91805 =[];
 if(!empty($m4is_t475['_memb_coursegrid'])){
$m4is_y82037 =$m4is_t475['_memb_coursegrid'];
  foreach (['unlocked', 'locked']as $m4is_o015){
$m4is_e456 =!empty($m4is_y82037[$m4is_o015])? $m4is_y82037[$m4is_o015]: [];
 $m4is_e98510 =!empty($m4is_e456['url'])? esc_url($m4is_e456['url']): '';
 $m4is_t64375 =!empty($m4is_e456['id'])? (int)$m4is_e456['id']: 0;
 if(!empty($m4is_e98510)||!empty($m4is_t64375)){
$m4is_l91805[$m4is_o015]=['url' =>$m4is_e98510, 'id' =>$m4is_t64375 ];
 
}
} if(!empty($m4is_y82037['locked_url'])){
$m4is_t2686 =esc_url($m4is_y82037['locked_url']);
 if(!empty($m4is_t2686)){
$m4is_l91805['locked_url']=$m4is_t2686;
 
}
} if(!empty($m4is_y82037['excerpt'])){
$m4is_i3208 =sanitize_text_field(htmlentities(trim($m4is_y82037['excerpt'])));
 if(!empty($m4is_y82037['excerpt'])){
$m4is_l91805['excerpt']=$m4is_i3208;
 
}
} $m4is_y5760 =!empty($m4is_y82037['order'])? (int)$m4is_y82037['order']: 0;
 if(!empty($m4is_l91805)||$m4is_y5760 > 0 ){
$m4is_l91805['order']=$m4is_y5760;
 
}
}$m4is_h35 ='_memberium/coursegrid/config';
 $m4is_k76508 =get_post_meta($m4is_b4068, $m4is_h35, true);
 if(empty($m4is_l91805)){
if($m4is_k76508 ){
delete_post_meta($m4is_b4068, $m4is_h35);
 
}
}else{
$m4is_k76508 =!$m4is_k76508 ? []: $m4is_k76508;
 if(!empty(array_diff($m4is_l91805, $m4is_k76508))){
update_post_meta($m4is_b4068, $m4is_h35, $m4is_l91805);
 
}
}
}
function m4is_y9035(){
global $post;
 $m4is_r1692 =get_post_meta($post->ID, '_iswp_custom_code', true);
 $defaults =['head' =>'', 'css' =>'', 'js' =>'', ];
 $m4is_r1692 =wp_parse_args($m4is_r1692, $defaults);
 wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memberium_customcode_nonce');
 echo '<p>HTML Head Code</p>';
 echo '<textarea id="is4wp_html_head" name="is4wp_html_head" placeholder="Head HTML Code" rows="3" cols="30" style="width:100%;">', $m4is_r1692['head'], '</textarea>';
 echo '<p>CSS Code</p>';
 echo '<textarea id="is4wp_css" name="is4wp_css" placeholder="Enter your custom CSS code here.  <style> tags are automatically included." rows="3" cols="30" style="width:100%;">', $m4is_r1692['css'], '</textarea>';
 echo '<p>JavaScript Code</p>';
 echo '<textarea id="is4wp_js" name="is4wp_js" rows=3 cols=30 placeholder="JavaScript Code.  <script> tags are automatically included." style="width:100%;">', $m4is_r1692['js'], '</textarea>';
 $m4is_u146 =method_exists('wp_screen', 'is_block_editor')? get_current_screen()->is_block_editor(): false;
  if(!$m4is_u146){
echo '<style>
				.CodeMirror { height: auto !important; border: 1px solid #ddd; }
				.CodeMirror-scroll { min-height: 100px !important; max-height:300px !important; }
				</style>';
  if(get_bloginfo('version')>= '4.9'){
$m4is_s54 =['is4wp_css' =>'text/css', 'is4wp_html_head' =>'text/html',  'is4wp_js' =>'application/javascript', ];
 foreach($m4is_s54 as $m4is_d07693 =>$m4is_z75923){
$m4is_e0213 =wp_enqueue_code_editor(['type' =>$m4is_z75923]);
 wp_add_inline_script('code-editor', sprintf('jQuery(function() { wp.codeEditor.initialize("' . $m4is_d07693 . '", %s); });', wp_json_encode($m4is_e0213)));
 
}
}
}
}
function m4is_s69($m4is_b4068){
$m4is_m5907 =$_POST;
  if(defined('DOING_AUTOSAVE' )&&DOING_AUTOSAVE ){
return;
 
} if(empty($m4is_m5907['memberium_customcode_nonce'])||!wp_verify_nonce($m4is_m5907['memberium_customcode_nonce'], $this->m4is_r1546->m4is_j541())){
return;
 
} if(!empty($m4is_m5907['post_type'])&&'page' == $m4is_m5907['post_type']){
if(!current_user_can('edit_pages', $m4is_b4068)){
return;
 
}
}else{
if(!current_user_can('edit_posts', $m4is_b4068)){
return;
 
}
}$m4is_r1692 =['head' =>isset($m4is_m5907['is4wp_html_head'])? trim($m4is_m5907['is4wp_html_head']): '', 'css' =>isset($m4is_m5907['is4wp_css'])? trim($m4is_m5907['is4wp_css']): '', 'js' =>isset($m4is_m5907['is4wp_js'])? trim($m4is_m5907['is4wp_js']): '', ];
 m4is_w831::m4is_f0691($m4is_b4068, 'custom_code', $m4is_r1692);
 
}
function m4is_r602(){
wp_enqueue_script('memberium-admin-edit', plugins_url('js/quickedit.js', MEMBERIUM_HOME), ['jquery', 'inline-edit-post'], '', TRUE);
 
}      
function m4is_k5467($m4is_g617 ){
$m4is_q485 =isset($_GET['post_type'])? $_GET['post_type']: 'post';
 $m4is_j65023 =m4is_h65::m4is_k58();
 $m4is_j37291 =[];
 $m4is_s0743 =[];
 if(false ){
$m4is_j37291['memberships']=__('Membership Levels');
 $m4is_j37291['tag_ids']=__("Tag ID's" );
 $m4is_j37291['contact_ids']=__("Contact ID's" );
 $m4is_j37291['prohibited_action']=__('Prohibited Action' );
 $m4is_j37291['anonymous_only']=__('Logged Out Only' );
 $m4is_j37291['facebook_crawler']=__('Facebook crawler' );
 $m4is_j37291['google_1stclick']=__('Google First Click Free' );
 
}if($m4is_q485 == 'memb_shortcodeblocks' ){
$m4is_j37291['memb_custom_shortcode']=__('Shortcode' );
 $m4is_s0743[]='categories';
 $m4is_s0743[]='date';
 
}else{
if($m4is_q485 == 'partials'){
$m4is_j37291['memb_partial_shortcode']=__('Shortcode');
 unset($m4is_g617['categories'], $m4is_g617['date']);
 
}$m4is_j37291['memberships']=__('Membership Levels');
 $m4is_j37291['tag_ids']=__('Tag ID\'s');
 $m4is_j37291['prohibited_action']=__('Prohibited Action');
 if(!empty($this->m4is_r1546->m4is_j498('settings', 'show_post_columns'))){
$m4is_j37291['contact_ids']=__('Contact ID\'s');
 $m4is_j37291['anonymous_only']=__('Logged Out Only');
 $m4is_j37291['facebook_crawler']=__('Facebook crawler');
 $m4is_j37291['google_1stclick']=__('Google First Click Free');
 
}
}foreach ($m4is_s0743 as $m4is_s952 ){
unset($m4is_g617[$m4is_s952 ]);
 
} $m4is_g617 =array_merge($m4is_g617, $m4is_j37291 );
 return $m4is_g617;
 
}
function m4is_m16($m4is_a950, $m4is_b4068 ){
switch ($m4is_a950){
case 'memb_custom_shortcode': echo '<div style="white-space:nowrap;">', '[membc_', get_post($m4is_b4068)->post_name, ']', '</div>';
 break;
 case 'memb_partial_shortcode': echo '<div style="white-space:nowrap;">', '[memb_include_partial id=', $m4is_b4068, ']', '</div>';
 break;
 case 'anonymous_only': $m4is_v586 =get_post_meta($m4is_b4068, '_is4wp_anonymous_only', TRUE);
 if($m4is_v586){
$m4is_e63195 ='<strong style="color:green;">Yes</strong>';
 $m4is_t25641 =1;
 
}else{
$m4is_e63195 ='<strong style="color:red;">No</strong>';
 $m4is_t25641 =0;
 
}echo '<div>', $m4is_e63195, '</div>';
  echo '<input type="hidden" id="memb-anonymousonly-', $m4is_b4068, '" value="', $m4is_t25641, '">';
 break;
 case 'contact_ids': $m4is_v586 =get_post_meta($m4is_b4068, '_is4wp_contact_ids', true);
 echo '<div id="memb-contactids-' . $m4is_b4068 . '">' . $m4is_v586 . '</div>';
 break;
 case 'facebook_crawler': $m4is_v586 =get_post_meta($m4is_b4068, '_is4wp_facebook_crawler', true);
 if($m4is_v586){
$m4is_e63195 ='<strong style="color:green;">Yes</strong>';
 $m4is_t25641 =1;
 
}else{
$m4is_e63195 ='<strong style="color:red;">No</strong>';
 $m4is_t25641 =0;
 
}echo '<div>', $m4is_e63195, '</div>';
  echo '<input type="hidden" id="memb-facebookcrawler-', $m4is_b4068, '" value="', $m4is_t25641, '">';
 break;
 case 'google_1stclick': $m4is_v586 =get_post_meta($m4is_b4068, '_is4wp_google_1stclick', true);
 if($m4is_v586){
$m4is_e63195 ='<strong style="color:green;">Yes</strong>';
 $m4is_t25641 =1;
 
}else{
$m4is_e63195 ='<strong style="color:red;">No</strong>';
 $m4is_t25641 =0;
 
}echo '<div>', $m4is_e63195, '</div>';
  echo '<input type="hidden" id="memb-google1stclick-', $m4is_b4068, '" value="', $m4is_t25641, '">';
 break;
 case 'memberships': $m4is_m96240 =array_filter(explode(',', get_post_meta($m4is_b4068, '_is4wp_membership_levels', true)));
 $m4is_t1794 =count($m4is_m96240);
 $m4is_q276 =[];
 $m4is_v4821 =0;
 $m4is_s3826 =get_post_meta($m4is_b4068, '_is4wp_any_loggedin_user', true);
 $m4is_l091 =get_post_meta($m4is_b4068, '_is4wp_any_membership', true);
 $m4is_k13696 =$this->m4is_r1546->m4is_j498('memberships');
 if($m4is_t1794 > 0){
foreach($m4is_m96240 as $m4is_l9671 =>$m4is_w64){
$m4is_q276[]=$m4is_w64;
 echo isset($m4is_k13696[$m4is_w64]['name'])? $m4is_k13696[$m4is_w64]['name']: '';
 if(++$m4is_v4821 < $m4is_t1794){
echo ', ';
 
}
}echo '<input type="hidden" id="memb-memberships-', $m4is_b4068, '" value="', implode(',', $m4is_q276), '" >';
 
}if($m4is_s3826){
echo '<span style="white-space:nowrap;">(Any Logged In User)</span> ';
 
}if($m4is_l091){
echo '<span style="white-space:nowrap;">(Any Membership)</span> ';
 
}break;
 case 'prohibited_action': $m4is_a0523 =get_post_meta($m4is_b4068, '_is4wp_prohibited_action', true );
 $m4is_f56 =trim(get_post_meta($m4is_b4068, '_is4wp_redirect_url', true ));
   echo '<input type="hidden" id="memb-prohibitedaction-', $m4is_b4068, '" value="', $m4is_a0523, '">';
 echo '<input type="hidden" id="memb-redirecturl-', $m4is_b4068, '" value="', $m4is_f56, '">';
 echo ucwords($m4is_a0523), '<br />';
 echo $m4is_f56, '<br />';
 break;
 case 'tag_ids': $m4is_v586 =trim(get_post_meta($m4is_b4068, '_is4wp_access_tags', true ), ',' );
 $m4is_v586 =implode(', ', array_filter(explode(',', $m4is_v586 )));
 echo '<div id="memb-accesstagids-', $m4is_b4068, '">', $m4is_v586, '</div>';
 break;
 
}
}
function m4is_a0432(int $m4is_b4068, WP_Post $m4is_m5907, bool $m4is_a686 ){
$m4is_t634 =['memb_shortcodeblocks', ];
 if(isset($m4is_m5907->post_type )&&!in_array($m4is_m5907->post_type, $m4is_t634 )){
return;
 
};
 if(!current_user_can('edit_post', $m4is_b4068 )){
return;
 
}m4is_f58::m4is_c26()->m4is_j64(true );
  
}   
function m4is_k47(){
if(!current_user_can('manage_options')){
wp_die(__('You do not have sufficient permissions to access this page.'));
 
}
}
function m4is_m663(){
require_once $this->m4is_r1546->m4is_x63587('googleanalytics.php');
 
}
function m4is_t5493(){
  
}
function m4is_p31472($m4is_d6498 ){
if(!is_array($m4is_d6498 )||empty($m4is_d6498 )){
return false;
 
}global $wpdb;
 $m4is_v2613 ="SELECT `post_id`, `meta_value` FROM `{$wpdb->postmeta
}` WHERE `meta_key` = '_is4wp_membership_levels' AND `meta_value` > '' ";
 $m4is_r02674 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 foreach ($m4is_r02674 as $m4is_m5907 ){
$m4is_v5613 =array_filter(explode(',', $m4is_m5907['meta_value']));
 $m4is_t6842 =implode(',', array_diff($m4is_v5613, $m4is_d6498));
 m4is_w831::m4is_f0691($m4is_m5907['post_id'], 'memberships', $m4is_t6842);
 
}$this->m4is_r1546->m4is_v26184();
 return true;
 
}
function m4is_g09518(){
require_once $this->m4is_r1546->m4is_x63587('remotefiles.php');
 
} 
function m4is_n6203(){
$m4is_z984 ='memberium::page_templates';
 $m4is_a4623 ='https://licenseserver.webpowerandlight.com/updates/page-templates.php';
  $m4is_l91805 =get_transient($m4is_z984, false);
 $m4is_u6591 =[];
 if(is_array($m4is_l91805)){
$m4is_u6591 =$m4is_l91805;
 
}else{
$m4is_u897 =['timeout' =>10, ];
 $m4is_l91805 =wp_remote_get($m4is_a4623, $m4is_u897);
 if(is_a($m4is_l91805, 'WP_Error')){
$m4is_q847 =$m4is_l91805->get_error_message();
 if(!empty($m4is_q847)){
echo '<div class="notice notice-error"><h3>', print_r($m4is_q847, true), '</h3></div>';
 return;
 
}
}if(is_array($m4is_l91805)){
$m4is_u6591 =json_decode($m4is_l91805['body'], true);
 if(is_array($m4is_u6591)){
set_transient($m4is_z984, $m4is_u6591, 3600);
 
}
}else{
$m4is_u6591 =[];
 
}
}return $m4is_u6591;
 
}
function m4is_g10548(){
$m4is_d31 ='https://licenseserver.webpowerandlight.com/updates/email-templates.php';
 $m4is_u897 =['timeout' =>10, ];
 $m4is_p8310 =wp_remote_get($m4is_d31, $m4is_u897);
 if(is_a($m4is_p8310, 'WP_Error')){
$m4is_q847 =$m4is_p8310->get_error_message();
 if(!empty($m4is_q847)){
echo '<div class="notice notice-error"><h3>', print_r($m4is_q847, true), '</h3></div>';
 return;
 
}
}$m4is_p8310 =json_decode($m4is_p8310['body'], true);
 if(is_array($m4is_p8310)){
$m4is_h3647 =['Id', 'PieceTitle' ];
 $m4is_v76912 =['PieceTitle' =>'MEMBERIUM TEMPLATE - %' ];
 $m4is_t32086 =m4is_c69807::m4is_o986('Template', 1000, 0, $m4is_v76912, $m4is_h3647 );
 $m4is_l43297 =0;
 array_walk ($m4is_t32086, function(&$m4is_v586){
$m4is_v586['PieceTitle']=strtolower(trim($m4is_v586['PieceTitle']));
 
});
 foreach($m4is_p8310 as $m4is_p6925){
if(is_array($m4is_t32086)){
$m4is_t265 =false;
 foreach($m4is_t32086 as $m4is_o809){
if(strtolower($m4is_o809['PieceTitle'])== strtolower($m4is_p6925['pieceTitle'])){
$m4is_t265 =true;
 
}
}if(!$m4is_t265){
 $id =$this->m4is_r1546->m4is_r1476()->addEmailTemplate($m4is_p6925['pieceTitle'], $m4is_p6925['categories'], $m4is_p6925['fromAddress'], $m4is_p6925['toAddress'], $m4is_p6925['ccAddress'], $m4is_p6925['bccAddress'], $m4is_p6925['subject'], $m4is_p6925['textBody'], $m4is_p6925['htmlBody'], $m4is_p6925['contentType'], $m4is_p6925['mergeContext']);
 $m4is_l43297++;
 
}
}
}
}return $m4is_l43297;
 
}
function m4is_o68190($m4is_b4068, $m4is_o809){
if($m4is_b4068 === ''){
return;
 
}$m4is_p8310 =$this->m4is_n6203();
 if(!isset($m4is_p8310[$m4is_o809])){
return;
 
}$m4is_p6925 =$m4is_p8310[$m4is_o809];
 unset($m4is_p8310);
 if($m4is_b4068 > 0){
$m4is_k417 =get_post($m4is_b4068, 'ARRAY_A');
 
}else{
$m4is_k417 =[];
 
}if(!empty($m4is_p6925['content_url'])){
$m4is_n548 =wp_remote_get($m4is_p6925['content_url']);
 $m4is_g4186 =[];
 if(is_array($m4is_n548)){
$m4is_g4186 =json_decode($m4is_n548['body'], true);
 $m4is_p6925['post']['post_content']=$m4is_g4186['post']['post_content'];
 $m4is_p6925['post']['post_excerpt']=$m4is_g4186['post']['post_excerpt'];
 
}
}$m4is_m5907 =[];
 $m4is_m5907['ID']=$m4is_b4068;
 $m4is_m5907['post_title']=empty($m4is_k417['post_title'])? $m4is_p6925['post']['post_title']: $m4is_k417['post_title'];
 $m4is_m5907['post_content']=$m4is_p6925['post']['post_content'];
 $m4is_m5907['post_type']=empty($m4is_k417['post_type'])? $m4is_p6925['post']['post_type']: $m4is_k417['post_type'];
 $m4is_m5907['post_status']=empty($m4is_k417['post_status'])? 'draft' : $m4is_k417['post_status'];
 $m4is_m5907['meta_input']=$m4is_p6925['meta'];
 $_POST['post_content']=$m4is_m5907['post_content'];
 $_POST['post_excerpt']=$m4is_m5907['post_excerpt'];
 remove_action('save_post', [$this, 'm4is_l13669']);
 $foo =wp_insert_post($m4is_m5907, false);
 foreach($m4is_p6925['meta']as $m4is_l9671 =>$m4is_v586){
update_post_meta($m4is_b4068, $m4is_l9671, $m4is_v586);
 
}add_action('save_post', [$this, 'm4is_l13669']);
 
} 
function m4is_f47($m4is_f536 =false){
$m4is_x8341 =defined('MEMBERIUM_BETA' )&&constant('MEMBERIUM_BETA' )== 1;
 $m4is_z984 ='memberium::setup::checklist';
 if($m4is_x8341 ){
delete_transient($m4is_z984);
 
}$m4is_s9386 =get_transient($m4is_z984, []);
 if(!$m4is_s9386){
$m4is_l91805 =wp_remote_get('https://licenseserver.webpowerandlight.com/welcome/checklist.php');
 if(!is_object($m4is_l91805)&&!empty($m4is_l91805['body'])){
$m4is_s9386 =json_decode($m4is_l91805['body'], true);
 set_transient($m4is_z984, $m4is_s9386, 3600);
 
}
}if(is_array($m4is_s9386)){
usort($m4is_s9386, function($a, $b){
return $a['o']- $b['o'];
 
});
 
} $m4is_p76 =(array)get_option('memberium_setup_completed');
  $m4is_v1568 =get_option($this->m4is_r1546->m4is_e96());
 $m4is_h56893 =m4is_s52::m4is_v91686($m4is_v1568);
 if($m4is_h56893['valid']){
$m4is_p76 =$this->m4is_r1546->m4is_s965('get_license', $m4is_p76);
 
}unset($m4is_h56893);
  $m4is_x39508 =$this->m4is_r1546->get_i2sdk_options();
 if($m4is_x39508['server_verified']){
$m4is_p76 =$this->m4is_r1546->m4is_s965('configure_i2sdk', $m4is_p76);
 
}unset($m4is_x39508);
 if($this->m4is_r1546->m4is_j498('settings', 'default_page_redirect')> '' ){
$m4is_p76 =$this->m4is_r1546->m4is_s965('set_default_redirect', $m4is_p76);
 
}if($this->m4is_r1546->m4is_j498('settings', 'show_advanced_options')> ''){
$m4is_p76 =$this->m4is_r1546->m4is_s965('view_advanced', $m4is_p76);
 
}if(count($this->m4is_r1546->m4is_j498('memberships'))){
$m4is_p76 =$this->m4is_r1546->m4is_s965('create_membership', $m4is_p76);
 
}if(!empty($this->m4is_r1546->m4is_j498('settings', 'global_excerpt'))){
$m4is_p76 =$this->m4is_r1546->m4is_s965('set_default_excerpt', $m4is_p76);
 
}global $wpdb;
  $m4is_v2613 ="SELECT count(*) FROM `{$wpdb->postmeta
}` WHERE `meta_key` LIKE '_is4wp_%' AND `meta_value` > '0';";
 $m4is_h973 =$wpdb->get_col();
  if($m4is_h973 > 0){
$m4is_p76 =$this->m4is_r1546->m4is_s965('protect_pages', $m4is_p76);
 
}unset($m4is_v2613, $m4is_h973);
  $m4is_h973 =(int) m4is_s52::m4is_s326();
 if($m4is_h973 > 0){
$m4is_p76 =$this->m4is_r1546->m4is_s965('1user', $m4is_p76);
 
}if($m4is_h973 > 9){
$m4is_p76 =$this->m4is_r1546->m4is_s965('10users', $m4is_p76);
 
}if($m4is_h973 > 24){
$m4is_p76 =$this->m4is_r1546->m4is_s965('25users', $m4is_p76);
 
}if($m4is_h973 > 99){
$m4is_p76 =$this->m4is_r1546->m4is_s965('100users', $m4is_p76);
 
}if($m4is_h973 > 499){
$m4is_p76 =$this->m4is_r1546->m4is_s965('500users', $m4is_p76);
 
}if($m4is_h973 > 999){
$m4is_p76 =$this->m4is_r1546->m4is_s965('1kusers', $m4is_p76);
 
}if($m4is_h973 > 2499){
$m4is_p76 =$this->m4is_r1546->m4is_s965('2500users', $m4is_p76);
 
}if($m4is_h973 > 9999){
$m4is_p76 =$this->m4is_r1546->m4is_s965('10kusers', $m4is_p76);
 
}if($m4is_h973 > 24999){
$m4is_p76 =$this->m4is_r1546->m4is_s965('25kusers', $m4is_p76);
 
}if($m4is_h973 > 49999){
$m4is_p76 =$this->m4is_r1546->m4is_s965('50kusers', $m4is_p76);
 
}if($m4is_h973 > 99999){
$m4is_p76 =$this->m4is_r1546->m4is_s965('100kusers', $m4is_p76);
 
}unset($m4is_v2613, $m4is_h973);
 update_option('memberium_setup_completed', array_filter(array_unique($m4is_p76)));
 update_option('memberium_checklist', $m4is_s9386);
  if($m4is_f536){
foreach ($m4is_s9386 as $m4is_l9671 =>$m4is_c4069){
if(!isset($m4is_c4069['n'])||$m4is_c4069['n']< 1){
unset($m4is_s9386[$m4is_l9671]);
 
}
}
} $m4is_g617 =[];
 $m4is_g617['active']=[];
 $m4is_g617['completed']=[];
 if(is_array($m4is_s9386)){
foreach ($m4is_s9386 as $m4is_c4069){
$m4is_g508 =false;
 $m4is_b935 =false;
  if(in_array($m4is_c4069['k'], $m4is_p76)){
$m4is_g508 =true;
 
} $m4is_f760 =isset($m4is_c4069['p'])? explode(',', $m4is_c4069['p']): [];
 if(empty($m4is_f760)){
$m4is_b935 =true;
 
}else{
$m4is_b935 =(count(array_intersect($m4is_p76, $m4is_f760))> 0);
 
}  $m4is_f760 =isset($m4is_c4069['r'])? explode(',', $m4is_c4069['r']): [];
 if(empty($m4is_f760)){
$m4is_p31 =false;
 
}else{
$m4is_p31 =(count(array_intersect($m4is_p76, $m4is_f760))> 0);
 
}if(!$m4is_g508){
if($m4is_b935){
$m4is_g617['active'][]=$m4is_c4069;
 
}
}else{
if(!$m4is_p31){
$m4is_g617['completed'][]=$m4is_c4069;
 
}
}
}
}return $m4is_g617;
 
}
function m4is_i73(){
 $m4is_c045 =$this->m4is_f47();
 $m4is_p76 =(array)get_option('memberium_setup_completed');
 $m4is_g617 =[];
 $m4is_g617['active']=[];
 $m4is_g617['completed']=[];
 foreach ($m4is_c045 as $m4is_b752){
$m4is_g508 =false;
 $m4is_b935 =false;
  if(in_array($m4is_b752['k'], $m4is_p76)){
$m4is_g508 =true;
 
} $m4is_f760 =isset($m4is_b752['p'])? explode(',', $m4is_b752['p']): [];
 if(empty($m4is_f760)){
$m4is_b935 =true;
 
}else{
$m4is_b935 =(count(array_intersect($m4is_p76, $m4is_f760))> 0);
 
}  $m4is_f760 =isset($m4is_b752['r'])? explode(',', $m4is_b752['r']): [];
 if(empty($m4is_f760)){
$m4is_p31 =false;
 
}else{
$m4is_p31 =(count(array_intersect($m4is_p76, $m4is_f760))> 0);
 
}if(!$m4is_g508){
if($m4is_b935){
if($m4is_b752['m']== 'm'){
$m4is_e63195 ='<input type="hidden" value="0" name="' . $m4is_b752['k']. '">' . '<input name="' . $m4is_b752['k']. '" id="' . $m4is_b752['k']. '" value="1" type="checkbox"> ' . $m4is_b752['t'];
 if($m4is_b752['l']> 0){
$m4is_e63195 .= ' - ' . m4is_h65::m4is_o64($m4is_b752['l']);
 
}$m4is_g617['active'][]=$m4is_e63195;
 
}elseif($m4is_b752['m']== 'a'){
$m4is_g617['active'][]='<input type="checkbox"> ' . $m4is_b752['t'];
 
}
}
}else{
if(!$m4is_p31){
$m4is_g617['completed'][]='<input type="checkbox" disabled="disabled" checked="checked"> ' . $m4is_b752['t'];
 
}
}
}return $m4is_g617;
 
}
function m4is_g93026(){
$m4is_h723 =$this->m4is_f47();
 echo '<form method="post" style="margin-left:25px;">';
 echo '<p>Complete the checklist below to launch your site, most settings are optional since you can just use the defaults we provide but click the steps below and follow the directions on each page.</p><p>It\'s easier than you think, and you\'ll be done in no time!</p>';
 echo '<div style="float:left; width:400px;">';
 echo '<h3>Upcoming Tasks and Goals</h3>';
 if(!empty($m4is_h723['active'])){
foreach ($m4is_h723['active']as $m4is_s176){
if($m4is_s176['m']== 'm'){
echo '<p style="text-indent:-25px;margin-left:25px;"><input type="hidden" value="0" name="', $m4is_s176['k'], '">';
 echo '<input name="', $m4is_s176['k'], '" id="', $m4is_s176['k'], '" value="1" type="checkbox"> ', $m4is_s176['t'];
 if($m4is_s176['l']> 0){
echo ' ', m4is_h65::m4is_o64($m4is_s176['l']);
 
}'</p>';
 
}elseif($m4is_s176['m']== 'a'){
echo '<p style="text-indent:-25px;margin-left:25px;"><input type="checkbox" class="automatic"> <em>' . $m4is_s176['t'], ' <strong>*</strong></em></p>';
 
}
}
}else{
echo '<p>All goals have been achieved!</p>';
 
}echo '</div>';
 echo '<div style="float:left; width:400px; margin-left:80px;">';
 echo '<h3>Completed Tasks and Goals</h3>';
 if(!empty($m4is_h723['completed'])){
$m4is_h723['completed']=array_reverse($m4is_h723['completed']);
 foreach($m4is_h723['completed']as $m4is_s176 ){
echo '<p style="text-indent:-25px;margin-left:25px;"><strike>', $m4is_s176['t'], '</strike></p>';
 
}
}else{
echo <<<HTMLBLOCK
				<p>
					You have no completed tasks.
				</p>
			HTMLBLOCK;
 
}echo <<<HTMLBLOCK
				</div>
				<div style="clear:both;"></div>
				<input type="submit" value="Mark Complete" class="button-primary"> &nbsp;&nbsp;
				<p>
					View <a href="https://memberium.com/documentation/" target="_blank">More Documentation Online</a>
				</p>
			</form>
			<p>
				<em><strong>*</strong> Automatically detected tasks and goals</em> cannot be manually checked.
			</p>
			<script>
				jQuery("input:checkbox.automatic").click(function() { return false; });
			</script>
		HTMLBLOCK;
 
}
function m4is_b154($m4is_e8216){
$m4is_a0186 =[];
 $m4is_k731 ='memberium::environment_signatures';
 $m4is_a0186 =get_transient($m4is_k731 );
 $m4is_a0186 =false;
 if(!is_array($m4is_a0186 )){
$m4is_n6062 ='https://licenseserver.webpowerandlight.com/memberium/environment-fingerprints.php';
 $m4is_y28166 =wp_remote_get($m4is_n6062 );
 if(is_array($m4is_y28166 )&&!empty($m4is_y28166['body'])){
$m4is_a0186 =json_decode($m4is_y28166['body'], true );
  foreach($m4is_a0186 as $m4is_l9671 =>$m4is_o31859){
if(!empty($m4is_o31859['platforms'])){
if(false === stripos($m4is_o31859['platforms'], $m4is_e8216 )){
unset($m4is_a0186[$m4is_l9671]);
 
}
}
}set_transient($m4is_k731, $m4is_a0186, (12 * HOUR_IN_SECONDS ));
 
}
}return $m4is_a0186;
 
}
function m4is_r86(){
$m4is_c71685 =[];
 $m4is_e8216 ='m4is';
 if(defined('GD_VIP')&&constant('GD_VIP' )){
define('GD_MANAGED_HOSTING', 1 );
 
}  $m4is_a0186 =$this->m4is_b154($m4is_e8216);
 $m4is_i6358 =get_option('active_plugins');
 $m4is_z0761 =wp_get_theme();
 $m4is_z0761 =$m4is_z0761->parent()? $m4is_z0761->parent(): $m4is_z0761;
 foreach ($m4is_a0186 as $m4is_o31859){
$m4is_t265 =false;
 if($m4is_o31859['type']== 'extension'){
$m4is_t265 =extension_loaded($m4is_o31859['fingerprint']);
 
}elseif($m4is_o31859['type']== 'function'){
$m4is_t265 =function_exists($m4is_o31859['fingerprint']);
 
}elseif($m4is_o31859['type']== 'class'){
$m4is_t265 =class_exists($m4is_o31859['fingerprint']);
 
}elseif($m4is_o31859['type']== 'environment'){
$m4is_t265 =isset($_SERVER[$m4is_o31859['fingerprint']]);
 
}elseif($m4is_o31859['type']== 'theme'){
$m4is_t265 =$m4is_z0761->get_stylesheet()== $m4is_o31859['fingerprint'];
 
}elseif($m4is_o31859['type']== 'plugin'){
$m4is_t265 =in_array($m4is_o31859['fingerprint'], $m4is_i6358);
 
}elseif($m4is_o31859['type']== 'constant'){
$m4is_t265 =defined($m4is_o31859['fingerprint']);
 
}if($m4is_t265){
if($m4is_o31859['class']== 'good'){
$m4is_c71685['detected'][]=$m4is_o31859;
 
}else{
$m4is_c71685['problem'][]=$m4is_o31859;
 
}
}else{
if($m4is_o31859['class']== 'good'){
$m4is_c71685['available'][]=$m4is_o31859;
 
}
}
}return $m4is_c71685;
 
}   
function m4is_v669(){
$m4is_b3785 =$this->m4is_e7663();
 echo '<div class="wrap about-wrap">';
  echo '<h3 style="font-size:225%">', __('Invalid License'), '</h3>';
 echo '<p class="about-text" style="margin-bottom:-30px;padding-bottom:0px;">';
 echo 'We are unable to license this site for one of the following reasons:';
 echo '</p>';
 echo '<ul style="margin-left:20px;">';
 if(!empty($m4is_b3785)){
echo '<li>Your i2SDK setup is incomplete or missing.  <a href="admin.php?page=i2sdk-admin">Click here to configure it</a>.</li>';
 
}else{
echo '<li>The domain you installed this on does not match the domain that the license was purchased for.</li>';
 echo '<li>You purchased an unlimited license but used a different Keap app or sandbox to connect to.</li>';
 echo '<li>Your webhost has outbound connections to our license server blocked.</li>';
 
}echo '</ul>';
 echo '<p>';
 echo '&ndash; The Memberium Team<br />';
 echo '<a href="https://memberium.com/support/" target="_blank">https://memberium.com/support/</a>';
 echo '</p>';
 echo '</div>';
 
}
function m4is_u57638(){
$this->m4is_r1546->m4is_t594();
 $m4is_t475 =$_POST;
 $m4is_w63 =$_SERVER;
 $m4is_m7964 =new m4is_s31();
   $m4is_m7964->m4is_h376('fa fa-tasks', 'Setup Checklist', 'checklist', [$this, 'm4is_g93026']);
 $m4is_m7964->m4is_h376('fa fa-users', 'About Memberium', 'about', [$this, 'm4is_q92835']);
 $m4is_m7964->m4is_j40293($this->m4is_r1546->m4is_x63587('header.php' ));
 $m4is_i78603 =$m4is_m7964->m4is_o08();
 if($m4is_w63['REQUEST_METHOD']== 'POST'){
if($m4is_i78603 == 'checklist'){
if(is_array($m4is_t475)){
foreach ($m4is_t475 as $m4is_l9671 =>$m4is_v586){
if($m4is_v586 == '1'){
$this->m4is_r1546->m4is_s965($m4is_l9671);
 
}
}
}
}elseif($m4is_i78603 == 'updates'){
m4is_h65::m4is_z896('Updates Options Updated');
  $m4is_e0213 =['autoupdate', ];
 foreach($m4is_e0213 as $m4is_l9671){
if(isset($m4is_t475[$m4is_l9671])){
$m4is_v586 =(int) (bool) trim($m4is_t475[$m4is_l9671]);
 $this->m4is_r1546->m4is_d64918($m4is_v586, 'settings', $m4is_l9671);
 
}
} if(!empty($m4is_t475['manual_upgrade_confirm'])){
$this->m4is_p2660($m4is_t475['manual_upgrade'], 'memberium2');
 
}
}elseif($m4is_i78603 == 'debug' &&isset($m4is_t475['delete-debug'])&&$m4is_t475['delete-debug']> ''){
unlink(constant('MEMBERIUM_DEBUGLOG' ));
 
}
}m4is_s6729::m4is_c26()->m4is_a4215();
 $m4is_m7964->m4is_s96();
 
}
function m4is_z9320(){
global $wpdb;
 $this->m4is_r1546->m4is_t594();
 $m4is_b732 =$this->m4is_r1546->m4is_x63587();
 $m4is_m7964 =new m4is_s31();
 $m4is_t475 =$_POST;
 $m4is_w63 =$_SERVER;
  $m4is_m7964->m4is_h376('fa fa-life-ring', 'Support', 'support', [$this, 'm4is_w8023']);
 $m4is_m7964->m4is_h376('fa fa-tachometer-alt', 'Dashboard', 'dashboard', $this->m4is_r1546->m4is_x63587('starthere-dashboard-show.php'));
 $m4is_m7964->m4is_h376('fa fa-cogs', 'Integrations', 'integrations', $this->m4is_r1546->m4is_x63587('starthere-integrations-show.php'));
 $m4is_m7964->m4is_h376('fa fa-download', 'Updates', 'updates', $this->m4is_r1546->m4is_x63587('starthere-updates-show.php'));
 $m4is_m7964->m4is_h376('fa fa-bug', 'Debug', 'debug', [$this, 'm4is_v0327']);
 $m4is_i78603 =$m4is_m7964->m4is_o08();
 if($_SERVER['REQUEST_METHOD']== 'POST'){
if($m4is_i78603 == 'dashboard'){
if(isset($m4is_t475['save'])&&$m4is_t475['save']== 'Renew License'){
m4is_s52::m4is_a834(true );
 m4is_h65::m4is_z896('License Updated', 'update');
 
}elseif(isset($m4is_t475['save'])&&$m4is_t475['save']== 'Re-Activate Plugin'){
m4is_n17::m4is_z126(false );
 m4is_h65::m4is_z896('System Activation Re-Run', 'update');
 
}elseif(isset($m4is_t475['save'])&&$m4is_t475['save']== 'Synchronize Keap'){
$this->m4is_b962();
 
}elseif(isset($_GET['purge-contacts'])){
$m4is_m7426 =m4is_p40::m4is_o1723();
 $m4is_v2613 ="TRUNCATE `{$m4is_m7426
}` ";
 $m4is_g6467 =$wpdb->query($m4is_v2613);
 m4is_h65::m4is_z896('Local Contact Database Purged', 'update');
 
}
}elseif($m4is_i78603 == 'updates'){
m4is_h65::m4is_z896('Updates Options Updated');
 $m4is_e0213 =['autoupdate', ];
  foreach($m4is_e0213 as $m4is_l9671){
if(isset($m4is_t475[$m4is_l9671])){
$m4is_v586 =(int) (bool) trim($m4is_t475[$m4is_l9671]);
 $this->m4is_r1546->m4is_d64918($m4is_v586, 'settings', $m4is_l9671);
 
}
} if(!empty($m4is_t475['manual_upgrade_confirm'])){
$this->m4is_p2660($m4is_t475['manual_upgrade'], 'memberium2');
 
}
}elseif($m4is_i78603 == 'debug' &&isset($m4is_t475['delete-debug'])&&$m4is_t475['delete-debug']> ''){
unlink(constant('MEMBERIUM_DEBUGLOG' ));
 
}
}m4is_s6729::m4is_c26()->m4is_a4215();
 $m4is_m7964->m4is_s96();
 
}
function m4is_l1308(){
$this->m4is_r1546->m4is_t594();
 $m4is_b732 =$this->m4is_r1546->m4is_x63587();
 $m4is_m468 =$this->m4is_r1546->m4is_j498('settings', 'multi_language');
 $m4is_m7964 =new m4is_s31();
  $m4is_m7964->m4is_h376('fa fa-users', 'Logins', 'logins', $this->m4is_r1546->m4is_x63587('options-login-show.php'));
 $m4is_m7964->m4is_h376('fa fa-file', 'Content Protection', 'content', $this->m4is_r1546->m4is_x63587('options-content-show.php'));
 $m4is_m7964->m4is_h376('fa fa-unlock-alt', 'Page Handling', 'pagehandling', $this->m4is_r1546->m4is_x63587('options-pagehandling-show.php'));
 if(!empty($m4is_m468)){
$m4is_m7964->m4is_h376('fa fa-language', 'Language', 'language', $this->m4is_r1546->m4is_x63587('options-language-show.php'));
 
}$m4is_m7964->m4is_h376('fa fa-sitemap', 'Pages', 'pages', $this->m4is_r1546->m4is_x63587('options-pages-show.php'));
 $m4is_m7964->m4is_h376('fa fa-shield-alt', 'Security', 'security', $this->m4is_r1546->m4is_x63587('options-sitesecurity-show.php'));
 $m4is_m7964->m4is_h376('fa fa-rocket', 'Performance', 'performance', $this->m4is_r1546->m4is_x63587('options-performance-show.php'));
 $m4is_m7964->m4is_h376('fa fa-plug', 'Extensions', 'extensions', $this->m4is_r1546->m4is_x63587('options-extensions-show.php'));
 $m4is_m7964->m4is_h376('fa fa-exchange-alt', 'HTTP Posts/Links', 'httppost', $this->m4is_r1546->m4is_x63587('options-httppost-show.php'));
 $current_tab =$m4is_m7964->m4is_o08();
 include $this->m4is_r1546->m4is_x63587('options.php');
 $m4is_m7964->m4is_s96();
 
}
function m4is_c30871(){
require_once $this->m4is_r1546->m4is_x63587('sync.php' );
 
}
function m4is_b2646(){
require_once $this->m4is_r1546->m4is_x63587('memberships.php');
 
}
function m4is_q158(){
require_once $this->m4is_r1546->m4is_x63587('ecommerce.php' );
 
}
function m4is_d69078(){
require_once $this->m4is_r1546->m4is_x63587('logs.php');
 
}   
function m4is_q92835(){
echo m4is_h65::m4is_q267('credits');
 
}
function m4is_w8023(){
echo m4is_h65::m4is_q267('support');
 
}
function m4is_v0327(){
require_once $this->m4is_r1546->m4is_x63587('support-debug.php');
 
}   
function m4is_y265($m4is_g617){
$m4is_j37291 =[];
 foreach($m4is_g617 as $m4is_l9671 =>$m4is_w915){
$m4is_j37291[$m4is_l9671]=$m4is_w915;
 if($m4is_l9671 == 'username'){
$m4is_j37291['user_id_contact_id']='User ID / Contact ID';
 
}
}return $m4is_j37291;
 
}
function m4is_z01($m4is_v586, $m4is_w93, $m4is_f087){
if($m4is_w93 === 'user_id_contact_id'){
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087);
 $m4is_v586 =$m4is_f087 . ' / ' . ($m4is_h21895 ? $m4is_h21895 : '(None)');
 
}return $m4is_v586;
 
}
function m4is_w8471(){
if($this->m4is_r1546->m4is_v461()){
$m4is_p6356 =current_filter()== 'user_new_form' ? 'checked = "checked" ' : '';
 ?>
			<table class="form-table">
				<tr class="form-field">
					<th scope="row"><label for="keap">Keap</label></th>
					<td>
						<label for="mail_chimp">
							<input type="hidden" name="memberium_add_contact" value="off">
							<input style="width: auto;" type="checkbox" name="memberium_add_contact" id="memberium_add_contact" <?php echo $m4is_p6356;
 ?> /> Create a matching contact for this user
						</label>
					</td>
				</tr>
			</table>
			<?php
 
}
}
function m4is_d2407(){
 
}
function m4is_v918($m4is_l17096){
 m4is_e41::m4is_o719($m4is_l17096);
  
}
function m4is_y95($m4is_f087 ){
global $wpdb;
 $m4is_t475 =$_POST;
 if(user_can($m4is_f087, 'manage_options' )){
return;
 
}$m4is_l17096 =get_user_by('id', $m4is_f087 );
 $m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
$m4is_h21895 =(int) $this->m4is_r1546->m4is_o70($m4is_l17096->user_email );
 
}if(!$m4is_h21895 ){

}$m4is_i935 =m4is_q82::m4is_d59($m4is_f087 );
 $m4is_x02754 =strtolower(trim($m4is_l17096->login));
 $m4is_e26 =isset($m4is_t475['new_emailaddress'])? strtolower(trim($m4is_t475['new_emailaddress'])): $m4is_x02754;
 $m4is_b8259 =strtolower(trim($m4is_l17096->user_email));
 $m4is_g86 =strtolower(trim($m4is_t475['email']));
  $m4is_u9705 =isset($m4is_t475['memberium_private_comments'])? (int) (bool) $m4is_t475['memberium_private_comments']: 0;
  update_user_meta($m4is_f087, 'memberium_private_comments', $m4is_u9705 );
  if(!empty($m4is_t475['updated_tags'])){
$this->m4is_r1546->m4is_k98($m4is_t475['updated_tags'], $m4is_h21895 );
 
}  if(!empty($m4is_g86 )&&($m4is_b8259 <> $m4is_g86 )||($m4is_g86 <> $m4is_l17096->user_login )){
$m4is_m71365 =!empty($m4is_t475['memb_update_email_confirm']);
 if($m4is_h21895 ){
$this->m4is_r1546->m4is_y6259($m4is_f087, $m4is_g86 );
  
}$m4is_t475['email']=$m4is_g86;
 
}   if(empty($this->m4is_r1546->m4is_j498('settings', 'local_auth_only', false ))){
if($m4is_t475['pass1']> '' &&$m4is_t475['pass1']=== $m4is_t475['pass2']){
$m4is_h21895 =(int) $this->m4is_r1546->m4is_o70($m4is_t475['email']);
 if($m4is_h21895 > 0){
$m4is_e32607 =[$this->m4is_r1546->m4is_j498('settings', 'password_field')=>$m4is_t475['pass1'], ];
 m4is_p40::m4is_x6560($m4is_h21895, $m4is_e32607, true);
 
}
}
} if(!empty($m4is_t475['memberium_sync'])){
$m4is_n195 =m4is_p40::m4is_r804($m4is_l17096->user_email );
 m4is_p40::m4is_m684($m4is_f087, $m4is_n195 );
   $m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 $m4is_m7426 =m4is_p40::m4is_o1723();
 $m4is_v2613 ="SELECT `id` FROM `{$m4is_m7426
}` WHERE `appname` = %s AND `fieldname` = 'email' and `value` = %s AND `id` <> %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_r9613, $m4is_t475['email'], $m4is_n195 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 $m4is_s0258 =[];
 foreach ($m4is_m615 as $row){
$m4is_s0258[]=$row['id'];
 
}if(!empty($m4is_s0258)){
$m4is_s0258 =implode(',', $m4is_s0258);
 $m4is_v2613 ="DELETE FROM `{$m4is_m7426
}` WHERE `appname` = %s AND `id` IN ({$m4is_s0258
}) ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_r9613);
 $m4is_u6591 =$wpdb->query($m4is_v2613);
 
}$m4is_h21895 =$m4is_n195;
 
}$m4is_k824 =m4is_q82::m4is_u687($m4is_f087 );
  
} 
function m4is_i725($m4is_k52736 ='', $m4is_r37596 =[], $m4is_x39 ='', $m4is_y66291 =[]){
 if(empty($m4is_r37596 )||empty($m4is_k52736 )){
return;
 
}$m4is_x39 =$m4is_x39 ?? '';
  if(!is_array($m4is_x39 )){
$m4is_x39 =explode(',', $m4is_x39 );
 
} $m4is_y642 =['autofocus' =>false, 'case_sensitive' =>false, 'class' =>'', 'disabled' =>false, 'echo' =>true, 'form' =>'', 'id' =>$m4is_k52736, 'multiple' =>false, 'required' =>false, 'size' =>1, 'style' =>'', ];
  $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642 );
 $m4is_y66291['size']=(int) $m4is_y66291['size'];
 $m4is_m3162 ='';
 $m4is_u3560 ='';
 $m4is_y72 =['autofocus', 'disabled', 'multiple', 'required', ];
 $m4is_x61 =['class', 'form', 'id', 'size', 'style', ];
  foreach ($m4is_y66291 as $m4is_l9671 =>$m4is_v586 ){
 if(in_array($m4is_l9671, $m4is_y72 )){
if($m4is_v586 ){
$m4is_u3560 .= " {$m4is_l9671
}=\"{$m4is_l9671
}\"";
 
}
} 
}foreach ($m4is_x61 as $m4is_o64169 ){
if(!empty($m4is_y66291[$m4is_o64169])){
$m4is_v586 =$m4is_y66291[$m4is_o64169];
  $m4is_u3560 .= " {$m4is_o64169
}=\"{$m4is_v586
}\" ";
 
}
} foreach($m4is_r37596 as $m4is_v586 =>$m4is_w42 ){
$m4is_a437 =false;
  foreach($m4is_x39 as $m4is_n246){
 if($m4is_y66291['case_sensitive']){
 $m4is_a437 =$m4is_a437 ||(bool) ($m4is_v586 == $m4is_n246);
 
}else{
 $m4is_a437 =$m4is_a437 ||(bool) (0 === strcasecmp($m4is_v586, $m4is_n246));
 
}
} $m4is_m3162 .= '<option value="' . $m4is_v586 . '" ' . ($m4is_a437 ? ' selected="selected" ' : ''). '>' . $m4is_w42 . '</option>';
 
} $m4is_o498 =<<<HTMLBLOCK
			<input type="hidden" name="{$m4is_k52736
}" value="">
			<select name="{$m4is_k52736
}" {$m4is_u3560
}>
			{$m4is_m3162
}
			</select>
		HTMLBLOCK;
  if($m4is_y66291['disabled']){
$m4is_x39 =implode(',', $m4is_x39 );
 $m4is_o498 .= "<input type=\"hidden\" name=\"{$m4is_k52736
}\" value=\"{$m4is_x39
}\">";
 
}$m4is_o498 ="\n\n{$m4is_o498
}\n\n";
  if($m4is_y66291['echo']){
echo $m4is_o498;
 
}else{
return $m4is_o498;
 
}
} 
function m4is_j021(): array {
 global $wp_roles;
  $m4is_m3605 =[];
  foreach($wp_roles->roles as $role){
 foreach($role['capabilities']as $k =>$v){
 $m4is_m3605[]=$k;
 
}
} $m4is_m3605 =array_unique($m4is_m3605);
  sort($m4is_m3605);
  return $m4is_m3605;
 
} private 
function m4is_q6073(string $m4is_l91805 ): string {
$m4is_l91805 =explode(',', $m4is_l91805 );
 $m4is_l91805 =array_filter(array_map('intval', $m4is_l91805 ), function($m4is_v586 ){
return $m4is_v586 !== 0;
 
});
 return implode(',', $m4is_l91805);
 
} public 
function m4is_s3976(): void {
global $post;
 $m4is_m340 =(bool) $this->m4is_r1546->m4is_j498('settings', 'site_lock_enabled' );
 $m4is_f261 =(bool) $this->m4is_r1546->m4is_j498('settings', 'page_inheritance' );
 $m4is_l68 =$this->m4is_r1546->m4is_j498('settings', 'default_prohibited_action' );
  $m4is_l79562 =get_post_meta($post->ID );
  $m4is_a89 =['_is4wp_commenter_action' =>'_is4wp_commenter_action', '_is4wp_commenter_goal' =>'_is4wp_commenter_goal', '_is4wp_commenter_tag' =>'_is4wp_commenter_tag', '_is4wp_hide_from_menu' =>'_is4wp_hide_from_menu', '_is4wp_private_comments' =>'_is4wp_private_comments', 'access_tags' =>'_is4wp_access_tags', 'access_tags2' =>'_is4wp_access_tags2', 'anonymous_only' =>'_is4wp_anonymous_only', 'any_membership' =>'_is4wp_any_membership', 'contact_ids' =>'_is4wp_contact_ids', 'facebook_crawler' =>'_is4wp_facebook_crawler', 'google_1stclick' =>'_is4wp_google_1stclick', 'hide_completely' =>'_is4wp_hide_completely', 'is4wp_can_comment' =>'_is4wp_can_comment', 'is4wp_discourage_cache' =>'_is4wp_discourage_cache', 'is4wp_force_public' =>'_is4wp_force_public', 'logged_in' =>'_is4wp_any_loggedin_user', 'membership_levels' =>'_is4wp_membership_levels', 'prohibited_action' =>'_is4wp_prohibited_action', 'redirect_url' =>'_is4wp_redirect_url', ];
 foreach ($m4is_a89 as $m4is_g7618 =>$m4is_p0458){
if(isset($m4is_l79562[$m4is_p0458][0])){
$m4is_r1692[$m4is_g7618]=$m4is_l79562[$m4is_p0458][0];
 
}else{
$m4is_r1692[$m4is_g7618]='';
 
}
}$m4is_r1692['access_tags']=$this->m4is_q6073($m4is_r1692['access_tags']);
 $m4is_r1692['access_tags2']=$this->m4is_q6073($m4is_r1692['access_tags2']);
 $m4is_r1692['membership_levels']=$this->m4is_q6073($m4is_r1692['membership_levels']);
 $m4is_r1692['contact_ids']=$this->m4is_q6073($m4is_r1692['contact_ids']);
  if($m4is_r1692['hide_completely']== 1){
$m4is_r1692['prohibited_action']='hide';
 
}if($m4is_r1692['prohibited_action']== ''){
if(!empty($m4is_l68)){
$m4is_r1692['prohibited_action']=$m4is_l68;
 
}
}wp_nonce_field($this->m4is_r1546->m4is_j541(), "memberium_membershipaccess_nonce_{$post->ID
}" );
 if($post->post_parent > 0 &&$m4is_f261 ){
echo <<<HTMLBLOCK
				<p style="color:red;text-align:center;">
					<strong>Inherits Access from the
						<a href="post.php?post={$post->post_parent
}&action=edit">Parent Page</a>
					</strong>
				</p>
			HTMLBLOCK;
 
}$m4is_p6356 =$m4is_r1692['any_membership']== 1 ? ' checked="checked" ' : '';
 $m4is_w42 =__('Any Membership Level', $this->m4is_f4218 );
 echo <<<HTMLBLOCK
			<div class="memb_access_options">
			<input type="checkbox" name="is4wp_anymembership" id="is4wp_anymembership" value="1" {$m4is_p6356
}>
			<label for="is4wp_anymembership">{$m4is_w42
}</label><br />
		HTMLBLOCK;
 $m4is_i629 =array_filter(explode(',', $m4is_r1692['membership_levels']));
 $m4is_m96240 =$this->m4is_r1546->m4is_j498('memberships' );
 if(is_array($m4is_m96240)){
foreach ($m4is_m96240 as $m4is_d07693 =>$m4is_w64){
echo '<input type="checkbox" class="memberium_membership_checkbox" name="is4wp_membership_levels[' . $m4is_d07693 . ']" value="' . $m4is_d07693 . '" id="is4wp_membership_' . $m4is_d07693 . '" ' . (in_array($m4is_d07693, $m4is_i629)? ' checked="checked" ' : ''). '>&nbsp;<label class="memberium_membership_checkbox" for="is4wp_membership_' . $m4is_d07693 . '">' . stripslashes($m4is_w64['name']). '&nbsp;(' . $m4is_w64['level']. ')</label><br />';
 
}
}echo <<<HTMLBLOCK
			</div>
			<hr />
		HTMLBLOCK;
 if($m4is_m340 ){
$m4is_p6356 =$m4is_r1692['is4wp_force_public']== 1 ? ' checked="checked" ' : '';
 $m4is_w42 =__('Force Public', 'memberium' );
 echo <<<HTMLBLOCK
				<input type="checkbox" name="is4wp_force_public" id="is4wp_force_public" value="1"{$m4is_p6356
}>
				<label for="is4wp_force_public">{$m4is_w42
}</label><br />
			HTMLBLOCK;
 
}$m4is_v3669 =$m4is_r1692['logged_in']== 1 ? ' checked="checked" ' : '';
 $m4is_h0624 =$m4is_r1692['anonymous_only']== 1 ? ' checked="checked" ' : '';
 $m4is_v30712 =$m4is_r1692['contact_ids']> '' ? $m4is_r1692['contact_ids']: '';
 $m4is_h4706 =$m4is_r1692['access_tags']> '' ? $m4is_r1692['access_tags']: '';
 $m4is_k78 =$m4is_r1692['access_tags2']> '' ? $m4is_r1692['access_tags2']: '';
 $m4is_t316 =$m4is_r1692['_is4wp_hide_from_menu']== 1 ? ' checked="checked" ' : '';
 $m4is_i894 =$m4is_r1692['google_1stclick']== 1 ? ' checked="checked" ' : '';
 $m4is_n6390 =$m4is_r1692['facebook_crawler']== 1 ? ' checked="checked" ' : '';
 $m4is_c136 =__('Any Logged In User', $this->m4is_f4218 );
 $m4is_w61 =__('Logged Out Only', $this->m4is_f4218 );
 $m4is_n57039 =__("Require Contact ID's", $this->m4is_f4218 );
 $m4is_l21756 =__("Require Tag ID&#39;s", $this->m4is_f4218 );
 $m4is_r2461 =__("AND Require Tag ID&#39;s", $this->m4is_f4218 );
 $m4is_o06918 =__('Hide from Menus', $this->m4is_f4218 );
 $m4is_y369 =__('Google 1st Click Free', $this->m4is_f4218 );
 $m4is_c6926 =__('Facebook Crawler Access', $this->m4is_f4218 );
 $m4is_w78 =__('When Prohibited', $this->m4is_f4218 );
 echo <<<HTMLBLOCK
			<div class="memb_access_options">
			<input type="checkbox" name="is4wp_loggedin" id="is4wp_loggedin" value="1"{$m4is_v3669
}> <label for="is4wp_loggedin">{$m4is_c136
}</label><br />
			<input type="checkbox" name="is4wp_anonymous_only" id="is4wp_anonymous_only" value="1"{$m4is_h0624
}> <label for="is4wp_anonymous_only">{$m4is_w61
}</label><br />
			<label for="is4wp_contact_ids">{$m4is_n57039
}</label>
			<textarea name="is4wp_contact_ids" rows="1" style="width:100%; max-width:100%">{$m4is_v30712
}</textarea>
			<label for="is4wp_access_tags">{$m4is_l21756
}</label>
			<input type="text" name="is4wp_access_tags" value="{$m4is_h4706
}" class="multitaglist2" style="width:100%; max-width:100%">
			<label for="is4wp_access_tags2">{$m4is_r2461
}</label>
			<input type="text" name="is4wp_access_tags2" value="{$m4is_k78
}" class="multitaglist2" style="width:100%; max-width:100%">
			</div>
			<hr />
			<input type="checkbox" name="is4wp_hide_from_menu" id="is4wp_hide_from_menu" value="1"{$m4is_t316
}> <label for="is4wp_hide_from_menu">{$m4is_o06918
}</label><br />
			<div class="memb_access_options">
			<input type="checkbox" name="is4wp_google_1stclick" id="is4wp_google_1stclick" value="1"{$m4is_i894
}> <label for="is4wp_google_1stclick">{$m4is_y369
}</label><br />
			<input type="checkbox" name="is4wp_facebook_crawler" id="is4wp_facebook_crawler" value="1"{$m4is_n6390
}> <label for="is4wp_facebook_crawler">{$m4is_c6926
}</label><br />
			</div>
			<hr />
			<div class="memb_access_options">
			{$m4is_w78
}: <select id="is4wp_prohibited_action" name="is4wp_prohibited_action">
		HTMLBLOCK;
 $m4is_h861 =[];
 if($m4is_l68 > ''){
$m4is_h861['default']='Site Default (' . ucwords($m4is_l68). ')';
 
}$m4is_h861['hide']='Hide Completely';
 if(post_type_supports($post->post_type, 'excerpt')){
$m4is_h861['excerpt']='Show Excerpt Only';
 
}$m4is_h861['redirect']='Redirect';
 foreach ($m4is_h861 as $m4is_v586 =>$m4is_w42){
$m4is_a437 =$m4is_r1692['prohibited_action']== $m4is_v586 ? 'selected="selected"' : '';
 echo '<option value="' . $m4is_v586 . '" ' . $m4is_a437 . '>' . $m4is_w42 . '</option>';
 
}echo '</select><br />';
 echo '</div>';
 $m4is_m836 =$this->m4is_r1546->m4is_j498('settings', 'default_page_redirect');
 $m4is_u0837 =($m4is_m836 > '' ? 'Default Redirect to ' . $m4is_m836 : 'Leave blank for sitewide default');
 $m4is_w42 =__('Redirect URL', 'memberium');
 $m4is_v586 =$m4is_r1692['redirect_url'];
 $m4is_u0837 =$m4is_u0837;
 echo <<<HTMLBLOCK
			<div class="memb_redirect_options">
			<label for="is4wp_redirect_url">{$m4is_w42
}</label>
			<input type="text" id="is4wp_redirect_url" name="is4wp_redirect_url" style="width:100%; max-width:100%" rows="1" value="{$m4is_v586
}" placeholder="{$m4is_u0837
}">
			</div>
		HTMLBLOCK;
 if(post_type_supports($post->post_type, 'comments' )){
$m4is_i7193 =m4is_j4156::m4is_z8359();
 $m4is_e86 ='';
 if(is_array($m4is_i7193 )){
foreach ($m4is_i7193 as $m4is_w64602 =>$m4is_t0631 ){
$selected =($m4is_r1692['_is4wp_commenter_action']== $m4is_w64602 )? ' selected="selected" ' : '';
 $m4is_e86 .= "<option value=\"{$m4is_w64602
}\"{$selected
}>{$m4is_t0631
}</option>";
 
}
}$m4is_p6356 =$m4is_r1692['_is4wp_private_comments']== 1 ? ' checked="checked" ' : '';
 $m4is_p7092 =$m4is_r1692['is4wp_can_comment']> '' ? $m4is_r1692['is4wp_can_comment']: '';
 $m4is_b681 =$m4is_r1692['_is4wp_commenter_tag']> '' ? $m4is_r1692['_is4wp_commenter_tag']: '';
 $m4is_a35 =$m4is_r1692['_is4wp_commenter_goal']> '' ? $m4is_r1692['_is4wp_commenter_goal']: '';
 $m4is_m759 =__('Private Commenting', $this->m4is_f4218 );
 $m4is_f75632 =__('Enable Comments if Tag Present', $this->m4is_f4218 );
 $m4is_z162 =__('Apply Tags on Comment', $this->m4is_f4218 );
 $m4is_y96 =__('Achieve Goal on Comment', $this->m4is_f4218 );
 $m4is_e96248 =__('Run Actionset on Comment', $this->m4is_f4218 );
 echo <<<HTMLBLOCK
				<p style="margin-top:20px;"><strong>Advanced Comment Functions</strong></p>

				<input type="hidden" name="_is4wp_private_comments" value="0">
				<input type="checkbox" name="_is4wp_private_comments" id="_is4wp_private_comments" value="1"{$m4is_p6356
}>
				<label for="_is4wp_private_comments">{$m4is_m759
}</label><br />

				<label for="is4wp_can_comment">{$m4is_f75632
}</label>
				<input type="text" name="is4wp_can_comment" value="{$m4is_p7092
}" class="multitaglist" style="width:100%; max-width:100%">

				<label for="_is4wp_commenter_tag">{$m4is_z162
}</label>
				<input type="text" name="_is4wp_commenter_tag" value="{$m4is_b681
}" class="multitaglist" style="width:100%; max-width:100%">

				<label for="_is4wp_commenter_goal">{$m4is_y96
}</label>
				<input type="text" name="_is4wp_commenter_goal" style="width:100%; max-width:100%" value="{$m4is_a35
}">

				<label for="_is4wp_commenter_action">{$m4is_e96248
}</label>
				<select class="actionset-selector" name="_is4wp_commenter_action" style="width:100%; max-width:100%">
				<option value="0">(No Actions)</option>
					{$m4is_e86
}
				</select>
			HTMLBLOCK;
 unset($m4is_i7193, $m4is_w64602, $m4is_t0631, $m4is_e86 );
 
}$m4is_p6356 =$m4is_r1692['is4wp_discourage_cache']== 1 ? ' checked="checked" ' : '';
 $m4is_w42 =__('Discourage Caching', $this->m4is_f4218 );
 echo <<<HTMLBLOCK
			<p>
			<input type="hidden" name="is4wp_discourage_cache" value="0">
			<input type="checkbox" name="is4wp_discourage_cache" id="is4wp_discourage_cache" value="1"{$m4is_p6356
}>
			<label for="is4wp_discourage_cache">{$m4is_w42
}</label><br />
			</p>
		HTMLBLOCK;
 
} public 
function m4is_h076($m4is_b4068 ): void {
 if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE){
return;
 
}$m4is_t475 =$_POST;
  if(empty($m4is_t475["memberium_membershipaccess_nonce_{$m4is_b4068
}"])||!wp_verify_nonce($m4is_t475["memberium_membershipaccess_nonce_{$m4is_b4068
}"], $this->m4is_r1546->m4is_j541())){
return;
 
} if(!empty($m4is_t475['post_type'])&&'page' == $m4is_t475['post_type']){
if(!current_user_can('edit_pages', $m4is_b4068)){
return;
 
}
}else{
if(!current_user_can('edit_posts', $m4is_b4068)){
return;
 
}
}$m4is_t475['is4wp_access_tags']=isset($m4is_t475['is4wp_access_tags'])? $this->m4is_q6073($m4is_t475['is4wp_access_tags']): '';
 $m4is_t475['is4wp_access_tags2']=isset($m4is_t475['is4wp_access_tags2'])? $this->m4is_q6073($m4is_t475['is4wp_access_tags2']): '';
 $m4is_t475['is4wp_contact_ids']=isset($m4is_t475['is4wp_contact_ids'])? $this->m4is_q6073($m4is_t475['is4wp_contact_ids']): '';
 $m4is_t475['is4wp_discourage_cache']=isset($m4is_t475['is4wp_discourage_cache'])? $m4is_t475['is4wp_discourage_cache']: '';
 $m4is_t475['is4wp_anonymous_only']=isset($m4is_t475['is4wp_anonymous_only'])? $m4is_t475['is4wp_anonymous_only']: 0;
 $m4is_t475['is4wp_anymembership']=isset($m4is_t475['is4wp_anymembership'])? $m4is_t475['is4wp_anymembership']: 0;
 $m4is_t475['is4wp_facebook_crawler']=isset($m4is_t475['is4wp_facebook_crawler'])? $m4is_t475['is4wp_facebook_crawler']: 0;
 $m4is_t475['is4wp_force_public']=isset($m4is_t475['is4wp_force_public'])? $m4is_t475['is4wp_force_public']: '';
 $m4is_t475['is4wp_google_1stclick']=isset($m4is_t475['is4wp_google_1stclick'])? $m4is_t475['is4wp_google_1stclick']: 0;
 $m4is_t475['is4wp_hide_completely']=isset($m4is_t475['is4wp_hide_completely'])? $m4is_t475['is4wp_hide_completely']: 0;
 $m4is_t475['is4wp_hide_from_menu']=isset($m4is_t475['is4wp_hide_from_menu'])? $m4is_t475['is4wp_hide_from_menu']: '';
 $m4is_t475['is4wp_loggedin']=isset($m4is_t475['is4wp_loggedin'])? $m4is_t475['is4wp_loggedin']: 0;
 $m4is_t475['is4wp_prohibited_action']=isset($m4is_t475['is4wp_prohibited_action'])? $m4is_t475['is4wp_prohibited_action']: '';
 $m4is_t475['is4wp_redirect_url']=isset($m4is_t475['is4wp_redirect_url'])? trim($m4is_t475['is4wp_redirect_url']): '';
 if($m4is_t475['is4wp_anymembership']== 0){
$m4is_t475['is4wp_membership_levels']=isset($m4is_t475['is4wp_membership_levels'])? $m4is_t475['is4wp_membership_levels']: [];
 
}else{
$m4is_t475['is4wp_membership_levels']=[];
 
}if(isset($m4is_t475['_is4wp_private_comments'])){
m4is_w831::m4is_f0691($m4is_b4068, 'private_comments', $m4is_t475['_is4wp_private_comments']);
 
}if(isset($m4is_t475['is4wp_can_comment'])){
m4is_w831::m4is_f0691($m4is_b4068, 'can_comment', $m4is_t475['is4wp_can_comment']);
 
}if(isset($m4is_t475['_is4wp_commenter_tag'])){
m4is_w831::m4is_f0691($m4is_b4068, 'commenter_tag', $m4is_t475['_is4wp_commenter_tag']);
 
}if(isset($m4is_t475['_is4wp_commenter_action'])){
m4is_w831::m4is_f0691($m4is_b4068, 'commenter_action', $m4is_t475['_is4wp_commenter_action']);
 
}if(isset($m4is_t475['_is4wp_commenter_goal'])){
m4is_w831::m4is_f0691($m4is_b4068, 'commenter_goal', $m4is_t475['_is4wp_commenter_goal']);
 
}if((int)$m4is_t475['is4wp_anonymous_only']== 1){
$m4is_t475['is4wp_membership_levels']='';
 
}$m4is_i629 =implode(',', (array)$m4is_t475['is4wp_membership_levels']);
 $m4is_b9263 =['access_tags' =>$m4is_t475['is4wp_access_tags'], 'access_tags2' =>$m4is_t475['is4wp_access_tags2'], 'anonymous_only' =>(int) $m4is_t475['is4wp_anonymous_only'], 'any_loggedin_user' =>(int) $m4is_t475['is4wp_loggedin'], 'any_membership' =>$m4is_t475['is4wp_anymembership'], 'contact_ids' =>$m4is_t475['is4wp_contact_ids'], 'discourage_cache' =>(int) $m4is_t475['is4wp_discourage_cache'], 'facebook_crawler' =>(int) $m4is_t475['is4wp_facebook_crawler'], 'force_public' =>(int) $m4is_t475['is4wp_force_public'], 'google_1st_click' =>(int) $m4is_t475['is4wp_google_1stclick'], 'hide_from_menu' =>(int) $m4is_t475['is4wp_hide_from_menu'], 'memberships' =>$m4is_i629, 'prohibited_action' =>$m4is_t475['is4wp_prohibited_action'], 'redirect_url' =>$m4is_t475['is4wp_redirect_url'], ];
 m4is_w831::m4is_f0691($m4is_b4068, $m4is_b9263);
 $this->m4is_r1546->m4is_v26184();
 delete_post_meta($m4is_b4068, '_is4wp_hide_completely');
 return;
 
} 
function m4is_n84(){
return;
 
} 
function m4is_h04($m4is_g617 =[]){
static $m4is_f2460 =false;
  if(!is_array($m4is_f2460 )){
 $m4is_n91237 =['cb', 'email', 'name', 'username', ];
  $m4is_n91237 =apply_filters('memberium/admin/user/columns', $m4is_n91237, $m4is_g617 );
  foreach($m4is_g617 as $m4is_l9671 =>$m4is_w42 ){
 if(!in_array($m4is_l9671, $m4is_n91237)){
unset($m4is_g617[$m4is_l9671]);
 
}
} $m4is_f2460 =$m4is_g617;
 
} return $m4is_f2460;
 
}    
function m4is_q25981($m4is_n548, $m4is_x0359, $m4is_n6062 ){
 if(session_status()!== PHP_SESSION_ACTIVE &&!headers_sent()){
 session_start();
 
} return $m4is_n548;
 
} 
function m4is_b05796($m4is_y760, $m4is_y66291, $m4is_n6062 ){
 if(session_status()=== PHP_SESSION_ACTIVE){
 session_write_close();
 
} return $m4is_y760;
 
} 
function m4is_e02($m4is_m16832 ){
 if(session_status()== PHP_SESSION_ACTIVE){
 session_write_close();
 
} return $m4is_m16832;
 
} private 
function m4is_p94(){
  
} private 
function m4is_q270(){
 add_action('admin_notices', ['m4is_c89756', 'm4is_z74']);
 
}public 
function m4is_a4215(): void {
include_once $this->m4is_r1546->m4is_x63587(). '/hero-header.php';
 
}      public 
function m4is_n738(string $m4is_m676, int $m4is_x1486 =0, string $m4is_b0597 ='', string $m4is_c0986 ='' ): string {
global $pagenow;
 $m4is_x60457 =in_array($pagenow, ['user-new.php', 'user-edit.php' ]);
 if(wp_doing_ajax()&&$_POST['action']?? '' == 'generate-password' ){
$m4is_x60457 =true;
 
}if(!$m4is_x60457 ){
return $m4is_m676;
 
}$m4is_i0548 =$this->m4is_r1546->m4is_j498('settings', 'password_strength' );
 $m4is_r6234 =$this->m4is_r1546->m4is_j498('settings', 'password_field' );
 if($m4is_i0548 < 5 ||$m4is_r6234 == 'Password' ){
$m4is_m676 =$this->m4is_r1546->m4is_a601();
 
}else{
$m4is_m676 =$this->m4is_r1546->m4is_z75139($m4is_i0548 - 1 );
 
}return $m4is_m676;
 
}
}

