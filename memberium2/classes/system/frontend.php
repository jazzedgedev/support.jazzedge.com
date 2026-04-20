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
class m4is_f58 {
public $post_type =0;
 private $m4is_s32679;
 private $m4is_x656;
 private $m4is_r1546;
 private $m4is_h30952;
 private $m4is_r15839;
 private $m4is_b4068;
 private $m4is_j168;
 private $m4is_f56;
 private $m4is_d691 =[];
 private $m4is_h02536;
 private $can_cache =FALSE;
 private $contact_count =0;
 private $contact_id =0;
 private $core;
 private $disable_login_redirect =FALSE;
 private $error_message ='';
 private $flash ='';
 private $footer_code;
 private $footer_json =[];
 private $forloop =0;
 private $found_posts;
 private $i2sdk_options =[];
 private $in_init =FALSE;
 private $in_list =0;
 private $index =0;
 private $is_administrator =false;
 private $license_status =false;
 private $login_redirect_enabled =true;
 private $login_redirect_url ='';
 private $nested_shortcodes =[];
 private $optimizepress_page =false;
 private $options =[];
 private $redirect_url;
 private $shortcode_tags =[];
 private $shortcodes =[];
 private $shortcodes_registered =false;
 private $signature =false;
 private $successful_upload;
 private $url_id =0;
 protected $httppost_service;
     public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
memberium_service_class::m4is_u684('app_frontend', $this );
 $this->m4is_i702();
 $this->m4is_y1648();
 $this->m4is_q97523();
 $this->m4is_d4861();
 m4is_j586::m4is_k751();
 
} private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_h30952 =0;
 $this->m4is_s32679 =[];
 $this->m4is_x656 =[];
 $this->m4is_r15839 =[];
 $this->m4is_b4068 =0;
 $this->m4is_j168 =false;
 
}    private 
function m4is_y1648(): void {
 $m4is_l61035 =['m4is_r5369' =>'classes/shortcodes/access', 'm4is_j06' =>'classes/shortcodes/affiliates', 'm4is_t73' =>'classes/shortcodes/aws', 'm4is_z7241' =>'classes/shortcodes/browser', 'm4is_t36568' =>'classes/catchers/catchers', 'm4is_g95873' =>'classes/shortcodes/crm', 'memberium_debug_shortcodes_class' =>'classes/shortcodes/debug', 'm4is_l4568' =>'libraries/ecommerce', 'm4is_h30' =>'classes/shortcodes/ecommerce', 'm4is_b789' =>'classes/shortcodes/filebox', 'm4is_m08356' =>'classes/shortcodes/gamification', 'm4is_j71492' =>'classes/shortcodes/misc', 'm4is_l032' =>'classes/shortcodes/postgrid', 'm4is_d56423' =>'classes/shortcodes/debug', 'm4is_q96705' =>'classes/shortcodes/user', 'm4is_u45762' =>'classes/shortcodes/wordpress', ];
 $this->m4is_r1546->m4is_p39($m4is_l61035 );
 
} private 
function m4is_q97523(): void {
global $wpdb;
 m4is_t4578::m4is_c26();
 do_action('memberium/shortcodes/add' );
  $this->options =$this->m4is_r1546->m4is_j498();
 if(!is_array($this->options)){
 
}$this->i2sdk_options =$this->m4is_r1546->get_i2sdk_options();
 if(!is_array($this->i2sdk_options)){
 
}$this->m4is_s32679 =[];
  if(is_array($_GET)){
foreach ($_GET as $m4is_l9671 =>$m4is_v586){
if(substr($m4is_l9671, 0, 4)== 'amp;'){
$m4is_a17 =substr($m4is_l9671, 4);
 $_GET[$m4is_a17]=$m4is_v586;
 unset($_GET[$m4is_l9671]);
 
}
}
} if($_SERVER['REQUEST_METHOD']== 'GET'){
add_action('template_redirect', [$this, 'm4is_b068'], PHP_INT_MIN );
 
}$this->m4is_y366();
 $this->m4is_v15();
 add_filter('shortcode_atts_memberium', ['m4is_f61', 'm4is_a478']);
 if(class_exists('WooCommerce')){
add_filter('woocommerce_login_redirect', [$this, 'm4is_d5366'], PHP_INT_MAX, 2);
 
} if(m4is_s52::m4is_w74()){
if(!empty($_GET['memb_autologin'])){
$autologin_enabled =!empty($this->m4is_r1546->m4is_j498('settings', 'allow_autologin'));
 $autologin_enabled =$autologin_enabled ||!empty($this->m4is_r1546->m4is_j498('settings', 'thrivecart_secret'));
 if($autologin_enabled){
add_action('init', ['m4is_z7893', 'm4is_t814'], 999999);
 
}
}if(!empty($_GET['memb_setcookie'])){
add_action('send_headers', [$this, 'm4is_r63'], 1);
 
}if(isset($_GET['memb_s3link'])&&isset($_GET['verification'])){
add_action('init', ['m4is_t73', 'm4is_v31']);
 
}
}
} private 
function m4is_d4861(): void {
$this->m4is_s20();
 $this->m4is_w9656();
 $this->m4is_h95321();
 $this->m4is_c16308();
 $this->m4is_v618();
 $this->m4is_w6934();
 $this->m4is_k9570();
 $this->m4is_h6246();
 $this->m4is_q73651();
 $this->m4is_y462();
 $this->m4is_g7642();
 add_action('memberium/shortcodes/add', [$this, 'm4is_s2469']);
 add_action('password_reset', [$this, 'm4is_g68'], 2 );
 add_action('pre_get_posts', [$this, 'm4is_q762'], PHP_INT_MAX );
 add_action('profile_update', [$this, 'm4is_m943'], 10, 3 );
 add_action('register_form', [$this, 'm4is_f82540'], 5 );
 add_action('transition_comment_status', [$this, 'm4is_w271'], 99, 3 );
 add_action('wp_enqueue_scripts', [$this, 'm4is_b79625']);
  add_action('wp_footer', [$this, 'm4is_y06435'], 100 );
 add_action('wp_head', [$this, 'm4is_p39261']);
 add_action('wp_head', [$this, 'm4is_x196'], PHP_INT_MAX );
 add_action('wp_insert_comment', [$this, 'm4is_y6410'], 99, 2 );
 add_filter('document_title_parts', [$this, 'm4is_w70'], PHP_INT_MAX, 1);
 add_filter('memberium/hide_titlebar', [$this, 'm4is_d12'], 10, 1 );
 add_filter('register', [$this, 'm4is_n73018'], 10, 1 );
 add_filter('wp_search_stopwords', [$this, 'm4is_f79'], 10, 1 );
 add_filter('wp_title', [$this, 'm4is_q264'], PHP_INT_MAX, 3);
 add_filter('wpal/blocks/can_access_asset', [$this, 'm4is_w0842'], 10, 4 );
 add_filter('wpal/menu/can_access_item', [$this, 'm4is_w0842'], 10, 4 );
 add_filter('wpal/taxonomy/can_access_term', [$this, 'm4is_w0842'], 10, 4 );
  
}private 
function m4is_h6246(): void {
add_action('init', [$this, 'm4is_k94'], 1000 );
 add_action('plugins_loaded', [$this, 'm4is_n872']);
 add_action('plugins_loaded', [$this, 'm4is_s2469'], 1000 );
 
}private 
function m4is_w6934(): void {
add_filter('gettext', function($m4is_e20478, $m4is_e63195, $m4is_g36127 ){
return $m4is_e20478 == 'Meta' ? 'Login' : $m4is_e20478;
 
}, 10, 3 );
 
}private 
function m4is_v618(): void {
add_filter('widget_meta_poweredby', function(){
return '';
 
});
 add_filter('wpal/widget/can_access_asset', [$this, 'm4is_w0842'], 10, 4 );
 
}private 
function m4is_q73651(): void {
add_action('the_post', [$this, 'm4is_x668'], 10 );
 add_filter('the_posts', [$this, 'm4is_j720'], 10, 2 );
 add_filter('get_pages', [$this, 'm4is_n35962'], 11, 2 );
 add_filter('memberium_has_post_access', [$this, 'm4is_t0612'], 10, 2 );
 add_filter('found_posts', [$this, 'm4is_o67623'], 10, 2 );
 
} private 
function m4is_g7642(): void {
$this->m4is_h02536 =is_user_logged_in()? m4is_q82::m4is_d6758(get_current_user_id(), 'memb_user', 'theme', '' ): '';
 add_action('template_redirect', [$this, 'm4is_g16'], 100 );
 add_action('template_redirect', [$this, 'm4is_n5309'], 15 );
 add_action('template_redirect', [$this, 'm4is_o8672']);
 add_action('template_redirect', [$this, 'm4is_m64359'], 25 );
 add_action('template_redirect', [$this, 'm4is_x28366'], 15 );
 add_filter('option_stylesheet', [$this, 'm4is_l6946']);
 add_filter('option_template', [$this, 'm4is_l6946']);
 add_filter('template', [$this, 'm4is_l6946']);
 
} private 
function m4is_y462(): void {
add_filter('wp_nav_menu_objects', [$this, 'm4is_m665']);
  add_filter('wp_nav_menu_objects', [$this, 'm4is_z80'], 100, 2 );
 add_filter('wp_get_nav_menu_items', [$this, 'm4is_a43'], 1, 3 );
 
}  
function m4is_i26071(): int {
return $this->m4is_h30952;
 static $m4is_b4068 =null;
 if(is_null($m4is_b4068 )){
if(is_single()){
if(!empty($GLOBALS['wp_the_query']->queried_object)&&is_a($GLOBALS['wp_the_query']->queried_object, 'WP_Post' )){
$m4is_b4068 =$GLOBALS['wp_the_query']->queried_object->ID;
 
}elseif(!empty($GLOBALS['wp_the_query']->posts[0])&&is_a($GLOBALS['wp_the_query']->posts[0], 'WP_Post')){
$m4is_b4068 =$GLOBALS['wp_the_query']->posts[0]->ID;
 
}
}else{
$m4is_b4068 =0;
 
}
}return (int) $m4is_b4068;
 
} 
function m4is_o97(): string {
 static $m4is_q485 =null;
  if(is_null($m4is_q485 )){
 if(is_singular()){
 if(!empty($GLOBALS['wp_the_query']->queried_object)&&is_a($GLOBALS['wp_the_query']->queried_object, 'WP_Post')){
 $m4is_q485 =$GLOBALS['wp_the_query']->queried_object->post_type;
 
}
}else{
 $m4is_q485 ='';
 
}
} return (string) $m4is_q485;
 
}   
function m4is_j97318(string $m4is_k52736, array $m4is_u076 ): void {
$this->m4is_x656[$m4is_k52736 ]=$m4is_u076;
 $_COOKIE[$m4is_k52736]=$m4is_u076['value'];
 
} 
function m4is_q60356(){
return $this->error_message;
 
} 
function m4is_o63($m4is_l073 ){
$this->error_message =$m4is_l073;
 
}
function m4is_t73689(){
return $this->flash;
 
}
function m4is_h185($m4is_a173 ){
$this->flash =$m4is_a173;
 
}
function m4is_h6904($m4is_l9671, $m4is_v586 =false){
if($m4is_v586){
$this->footer_json[$m4is_l9671]=$m4is_v586;
 
}else{
unset($this->footer_json[$m4is_l9671]);
 
}
}
function m4is_z482($m4is_l9671 =false){
if($m4is_l9671){
return (isset($this->footer_json[$m4is_l9671]))? $this->footer_json[$m4is_l9671]: NULL;
 
}else{
return $this->footer_json;
 
}
}
function m4is_i615(){
return $this->redirect_url;
 
}
function m4is_w6289($m4is_f56 ){
$this->m4is_f56 =$m4is_f56;
 
}
function m4is_x52676(){
$this->login_redirect_enabled =false;
 
} 
function m4is_l85024(){
$this->login_redirect_enabled =true;
 
}
function m4is_h61($m4is_j168 =true){
$this->m4is_r1546->m4is_h61($m4is_j168);
 
}
function m4is_l5326(){
return (bool) $this->m4is_r1546->m4is_l5326();
 
}
function m4is_o0123($caching){
$this->can_cache =(boolean) $caching;
 if(!$caching){
m4is_j586::m4is_x7134();
 
}
}
function m4is_b52(){
return $this->can_cache;
 
}    
function m4is_y366(){
 global $allowedposttags;
  $tagsAndAttributes =['a' =>['href'], 'iframe' =>['src', 'srcdoc'], 'input' =>['name', 'placeholder', 'value'], 'option' =>['value'], 'progress' =>['max', 'value'], 'source' =>['src'], 'textarea' =>['placeholder', 'value']];
  foreach ($tagsAndAttributes as $tag =>$attributes){
 foreach ($attributes as $attribute){
 $allowedposttags[$tag][$attribute]=1;
 
}
}
}   
function m4is_n872(){
 add_filter('op_check_page_availability', [$this, 'm4is_h9157']);
  if(class_exists('WooCommerce')){
add_action('woocommerce_customer_save_address', [$this, 'm4is_w806'], 10, 2);
 add_action('woocommerce_register_form_start', [$this, 'm4is_t62081']);
 add_action('woocommerce_register_post', [$this, 'm4is_f6835'], 10, 3);
 add_action('woocommerce_created_customer', [$this, 'm4is_z960'], 999999, 3);
 
}  add_filter('ninja_forms_field', [$this, 'm4is_y83'], 10, 2);
 
} 
function m4is_t69746(){
return;
 if(!isset($_SERVER['PHP_AUTH_USER'])){
header('WWW-Authenticate: Basic realm="RSS Feeds"');
 header('HTTP/1.0 401 Unauthorized');
 echo 'Feeds from this site are private';
 exit;
 
}else{
if(is_wp_error(wp_authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']))){
header('WWW-Authenticate: Basic realm="RSS Feeds"');
 header('HTTP/1.0 401 Unauthorized');
 echo 'Username and password were not correct';
 exit;
 
}
}
} 
function m4is_e9153($m4is_o9361){
 $m4is_o9361 =array_merge($m4is_o9361, ['memb_php', 'memb_raw']);
 return $m4is_o9361;
 
} 
function m4is_f79($m4is_z268){
$m4is_z268[]='memb_';
 $m4is_z268[]='membc_';
 return $m4is_z268;
 
} 
function m4is_o67623($m4is_n1860, $m4is_v76912){
if($this->found_posts !== null){
$m4is_v76912->found_posts =$this->found_posts;
 $this->found_posts =null;
 return $m4is_v76912->found_posts;
 
}return $m4is_n1860;
 
}
function m4is_a3860(array $m4is_r02674, $m4is_v76912 =false){
foreach($m4is_r02674 as $m4is_l9671 =>$m4is_m5907){
if(!$this->m4is_x72168($m4is_m5907->ID)){
unset($m4is_r02674[$m4is_l9671]);
 
}
}return $m4is_r02674;
 
} 
function m4is_r63(){
$m4is_k52736 =trim($_GET['memb_setcookie']);
 $m4is_r6654 =isset($_GET['expiration'])? strtotime($_GET['expiration']): time()+ YEAR_IN_SECONDS;
 $m4is_v586 =isset($_GET['value'])? $_GET['value']: '';
 $m4is_z75923 =isset($_GET['mode'])? strtolower($_GET['mode']): 'replace';
 $m4is_q37596 =isset($_GET['redir'])? strtolower($_GET['redir']): '/';
 $m4is_d04266 =isset($_GET['path'])? strtolower($_GET['path']): '/';
 $m4is_g36127 =isset($_GET['domain'])? strtolower($_GET['domain']): $_SERVER['HTTP_HOST'];
  if($m4is_z75923 == 'append'){
$m4is_v586 =implode(',', array_unique(explode(',', $_COOKIE[$m4is_k52736]. ',' . trim($m4is_v586))));
 
}$m4is_u6591 =setcookie($m4is_k52736, $m4is_v586, $m4is_r6654, $m4is_d04266, $m4is_g36127);
 m4is_j586::m4is_x7134();
 wp_redirect($m4is_q37596);
 exit;
 
}
function m4is_d93(){
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}m4is_j586::m4is_x7134();
  if(empty($_GET['operation'])){
return;
 
}m4is_c86031::m4is_c26();
 $this->m4is_r1546->m4is_p39(['m4is_g7460' =>'classes/crm/http-posts/copy-fields', 'm4is_r296' =>'classes/crm/http-posts/delete-contact', 'm4is_c239' =>'classes/crm/http-posts/makepass', 'm4is_p75' =>'classes/crm/http-posts/math', 'm4is_d69852' =>'classes/crm/http-posts/prettify', 'm4is_w3605' =>'classes/crm/http-posts/set-date', 'm4is_w07594' =>'classes/crm/http-posts/get-post', 'm4is_a561' =>'classes/crm/http-posts/update-contact', 'm4is_g93124' =>'classes/crm/http-posts/scan-subscriptions', ]);
 $m4is_g3674 =['add-contact' =>[$this, 'm4is_s6670'], 'add-event' =>[$this, 'm4is_x79053'], 'add-issue' =>[$this, 'm4is_g983'], 'add-tags' =>[$this, 'm4is_g06'], 'add-user' =>[$this, 'post_add_user'], 'copy-next-billing' =>[$this, 'm4is_r66307'], 'expire-subs' =>[$this, 'm4is_j10295'], 'foo' =>[$this, 'm4is_b5614'], 'gdpr-erase' =>[$this, 'httppost_gdpr_erase'], 'gdpr-export' =>[$this, 'httppost_gdpr_export'], 'optin' =>[$this, 'm4is_u40532'], 'optout' =>[$this, 'm4is_c86'], 'contact-delete' =>['m4is_r296', 'm4is_z95'], 'contact-update' =>['m4is_a561', 'm4is_z95'], 'copy-fields' =>['m4is_g7460', 'm4is_z95'],  'delete-contact' =>['m4is_r296', 'm4is_z95'], 'get-post' =>['m4is_w07594', 'm4is_z95'],  'makepass' =>['m4is_c239', 'm4is_z95'], 'math' =>['m4is_p75', 'm4is_z95'], 'prettify-contact' =>['m4is_d69852', 'm4is_z95'], 'set-date' =>['m4is_w3605', 'm4is_z95'], 'update-contact' =>['m4is_a561', 'm4is_z95'],  'scan-subscriptions' =>['m4is_g93124', 'm4is_z95'],  ];
 $m4is_g3674 =apply_filters('memberium/httpppost_services/register', $m4is_g3674);
 $m4is_u820 =strtolower($_GET['operation']);
 if(array_key_exists($m4is_u820, $m4is_g3674 )){
$m4is_a1056 =$m4is_g3674[$m4is_u820];
 $this->m4is_r1546->m4is_a6832(true );
 add_action('i2sdk_http_post', $m4is_a1056, 10, 2 );
 
}
} 
function m4is_g16(){
if(is_404()&&$this->m4is_r1546->m4is_j498('settings', 'cache_flush' )){
flush_rewrite_rules();
 
}
}
function m4is_k94(){
$this->is_administrator =current_user_can('manage_options' );
 if(class_exists('WooCommerce' )){
$this->m4is_w593();
 
}add_action('wp_login_failed', ['m4is_l5841', 'm4is_m56'], -1, 2 );
   $m4is_a86 =array_filter(explode(',', $this->m4is_r1546->m4is_j498('settings', 'allow_wpadmin_titlebar' )));
 $m4is_h7046 =true;
 if(!empty($m4is_a86 )){
$m4is_f087 =$this->m4is_r1546->m4is_x66();
 foreach($m4is_a86 as $m4is_d51 ){
if(user_can($m4is_f087, $m4is_d51 )){
$m4is_h7046 =false;
 
}
}
}$m4is_h7046 =apply_filters('memberium/hide_titlebar', $m4is_h7046 );
 if($m4is_h7046 ){
add_filter('show_admin_bar', '__return_false', 999999 );
 
}
} 
function m4is_d12($m4is_h7046 ){
if($m4is_h7046 ){
 $m4is_n864 =['impersonated_by_', 'wordpress_user_sw_olduser_', 'wordpress_user_sw_secure_', ];
 foreach($m4is_n864 as $m4is_j67631 ){
if(!empty($_COOKIE[$m4is_j67631 . COOKIEHASH])){
return false;
 
}
}
}return $m4is_h7046;
 
} 
function m4is_m64359(){
if(is_user_logged_in()){
return;
 
}$m4is_g56124 =(bool) $this->m4is_r1546->m4is_j498('settings', 'site_lock_enabled' );
 if(!$m4is_g56124 ){
return;
 
}$m4is_b4068 =(int) $this->m4is_i26071();
 $m4is_y0966 =(bool) apply_filters('memberium/sitelock/disable', false, $m4is_b4068 );
  if($m4is_y0966 ){
return;
 
}$m4is_y90287 =(int) $this->m4is_r1546->m4is_j498('settings', 'login_url' );
 if($m4is_b4068 ){
if($m4is_b4068 === $m4is_y90287){
return;
 
} $m4is_h1863 =(bool) get_post_meta($m4is_b4068, '_is4wp_force_public', true );
 if($m4is_h1863 ){
return;
 
}
}if($m4is_y90287 ){
$m4is_f56 =get_permalink($m4is_y90287 );
 $m4is_f56 =add_query_arg('redirect_to', $_SERVER['REQUEST_URI'], $m4is_f56 );
 
}else{
$m4is_f56 =wp_login_url($_SERVER['REQUEST_URI']);
 
}wp_redirect($m4is_f56, 302, 'Memberium Sitelock');
 exit;
 
} public 
function m4is_p48(){
return;
 global $post, $wp_query;
 if(!is_a($post, 'WP_Post' )){
return;
 
}if(current_user_can('manage_options' )){
return;
 
}$m4is_b4068 =$post->ID;
 $m4is_f0174 =$this->m4is_x72168($m4is_b4068 );
 if(!$m4is_f0174 ){
$m4is_p1084 =$this->m4is_a26($m4is_b4068, false );
 if($m4is_p1084 == 'redirect' ){
$this->m4is_m6637($m4is_b4068, 'General Redirect ' . __LINE__ );
 
}elseif($m4is_p1084 == 'hide' ){
global $wp_query;
 $wp_query->set_404();
 status_header(404 );
 get_template_part(404 );
 exit;
 
}elseif($m4is_p1084 == 'excerpt' ){
$post->post_excerpt =$this->m4is_c376($post );
 
}
}return;
 
} 
function m4is_o8672(){
global $bp;
 global $post;
 $m4is_b4068 =$this->m4is_i26071();
 $this->post_type =$this->m4is_o97();
 $_GET['viewable']=isset($_GET['viewable'])? $_GET['viewable']: '';
  if($this->m4is_r1546->m4is_y90572()){
m4is_j586::m4is_x7134();
 return;
 
}if(isset($_GET['action'])&&$_GET['action']== 'confirmregistration' ){
m4is_j586::m4is_x7134();
 m4is_n2918::m4is_m69();
 
} if(!empty($_GET['filebox_id'])&&$_GET['filebox_id']> 0){
m4is_j586::m4is_x7134();
 m4is_t21664::m4is_t372((int) $_GET['filebox_id'], $_GET['filename'], (bool) $_GET['viewable']);
 exit;
 
}if(!empty($this->m4is_r1546->m4is_j498('settings', 'cache_bust'))){
m4is_j586::m4is_x7134();
 
} if(!empty($bp)){
if(!empty($bp->groups->current_group->id)){
return;
 
}
}if(is_404()&&substr($_SERVER['REQUEST_URI'], 0, 7)== '/prtctd'){
$m4is_q37596 =str_ireplace($this->m4is_r1546->m4is_j498('settings', 'default_page_redirect'), '{{current.url}}', get_site_url());
 wp_redirect($m4is_q37596, 302, 'Prtctd Redirect');
 exit;
 
}if(get_post_meta($m4is_b4068, '_is4wp_discourage_cache', true)){
m4is_j586::m4is_x7134();
 
}if(is_singular()){
if($m4is_b4068 > 0 &&!$this->m4is_x72168($m4is_b4068)){
$m4is_p1084 =$this->m4is_a26($m4is_b4068, FALSE);
 if($m4is_p1084 == 'redirect'){
m4is_j586::m4is_x7134();
 $this->m4is_m6637($m4is_b4068, 'General Redirect ' . __LINE__ );
 
}
}
}wp_enqueue_script('jquery');
 
}
function m4is_q762(){
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}if(!function_exists('is_user_logged_in' )||!function_exists('wp_set_current_user' )){
return;
 
} if(strpos('wp-json/', $_SERVER['REQUEST_URI'])!== false ){
return;
 
}if(is_user_logged_in()){
return;
 
}if(defined('COOKIEHASH' )){
$m4is_b6306 ='wordpress_logged_in_' . COOKIEHASH;
 $m4is_m2635 =isset($_COOKIE[$m4is_b6306])? wp_parse_auth_cookie($_COOKIE[$m4is_b6306]): false;
 if(!empty($m4is_m2635['username'])){
wp_set_current_user(null, $m4is_m2635['username']);
 return;
 
}
}
}    
function m4is_x28366(){
if($this->m4is_r1546->m4is_j498('settings', 'two_pass_shortcode_filter')){
$m4is_b4068 =$this->m4is_i26071();
 if(defined('OP_VERSION')){
$this->optimizepress_page =get_post_meta($m4is_b4068, '_optimizepress_pagebuilder', true)== 'Y';
 if($this->optimizepress_page){
add_filter('comment_text', [$this, 'm4is_i861'], 1, 1);
 $this->m4is_w796();
 
}
}else{
add_filter('the_content', [$this, 'm4is_o8940'], 1, 1);
 
}
}add_filter('no_texturize_shortcodes', [$this, 'm4is_e9153']);
 
} 
function m4is_i861($m4is_t09761){
$m4is_t09761 =preg_replace('/\[(\w.*)\]/', '[[$1]]', $m4is_t09761);
 return $m4is_t09761;
 
} 
function m4is_h9157($m4is_k86914 ){
global $op_content_layout;
 $this->m4is_s2469();
 $op_content_layout =do_shortcode($op_content_layout );
 return $m4is_k86914;
 
}    
function m4is_d1452(){

} 
function m4is_f82540(){
if(!$this->m4is_r1546->m4is_j498('settings', 'extended_reg_fields', false )){
return;
 
}$m4is_r08743 =apply_filters('memberium/registration_fields/custom', false );
 if($m4is_r08743 ){
return;
 
}?>
		<script type="text/javascript" src="<?php echo site_url('/wp-includes/js/jquery/jquery.js');
 ?>"></script>
		<script>
		jQuery('#user_email').attr('type', 'email');
		jQuery('#user_reg_login').attr('type', 'email');

		jQuery('#registerform #user_login').parent().remove();
		jQuery('#user_reg_login').parent().remove();

		jQuery('#user_email').blur(function() {
			jQuery("#user_login2").val(jQuery("#user_email").val() );
		});
		jQuery('#user_reg_email').blur(function() {
			jQuery("#user_login2").val(jQuery("#user_reg_email").val() );
		});
		</script>
		<input type="hidden" id="user_login2" name="user_login" value="">
		<p>
		<label>
		First Name<br/>
		<input id="firstname" type="text" tabindex="30" size="25" value="" name="firstname" />
		</label>
		</p>
		<p>
		<label>
		Last Name<br/>
		<input id="lastname" type="text" tabindex="30" size="25" value="" name="lastname" />
		</label>
		</p>
		<?php
 
} 
function m4is_q520($m4is_e63195 ){
if($m4is_e63195 == 'Lost your password?' ){
$m4is_e63195 ='';
 
}return $m4is_e63195;
 
} 
function m4is_y1862(){
return FALSE;
 
}     public 
function m4is_l6946(string $m4is_i86 ): string {
$m4is_f087 =$this->m4is_r1546->m4is_x66();
  if(empty($m4is_f087 )){
return $m4is_i86;
 
}m4is_j586::m4is_x7134();
 $m4is_h02536 =$this->m4is_h02536;
 if(empty($this->m4is_h02536 )){
return $m4is_i86;
 
}$m4is_w9764 =wp_get_themes();
 if(!array_key_exists($this->m4is_h02536, $m4is_w9764 )){
return $m4is_i86;
 
}$m4is_d7912 =current_filter();
 if($m4is_d7912 == 'option_template' ||$m4is_d7912 == 'template' ){
return $m4is_w9764[$this->m4is_h02536]->Template;
 
}return $this->m4is_h02536;
 
}    
function m4is_w702($m4is_y972 =true ){
if(!empty($_GET['action'])&&$_GET['action']== 'logout'){
$m4is_y972 =true;
 
} $m4is_h21895 =$this->m4is_r1546->m4is_d3087('logout/contact_id', 0);
 if(!empty($m4is_h21895)){
$m4is_c65 =(int) $this->m4is_r1546->m4is_j498('settings', 'logout_actionset', 0);
 $m4is_g76248 =(int) $this->m4is_r1546->m4is_j498('settings', 'logout_tag', 0);
 $this->m4is_r1546->m4is_u71903($m4is_c65, $m4is_h21895);
 $this->m4is_r1546->m4is_k98($m4is_g76248, $m4is_h21895);
 
}if(empty($_GET['redirect_to'])){
$url =$this->m4is_r1546->m4is_x6069();
 
}else{
$url =$_GET['redirect_to'];
 
}setcookie('nextend_uniqid', '', -1, '/');
 $this->m4is_r1546->m4is_i80956();
 if($m4is_y972){
wp_redirect($url );
 exit;
 
}
} 
function m4is_g68($m4is_l17096, $m4is_q6570 =false){
$m4is_r2369 =$this->m4is_r1546->m4is_j498('settings', 'local_auth_only');
 if(!$m4is_r2369){
if(!$m4is_q6570){
if(isset($_POST['pass1'])&&$_POST['pass1']> ''){
$m4is_q6570 =$_POST['pass1'];
 
}elseif(isset($_POST['password_1'])&&$_POST['password_1']> ''){
$m4is_q6570 =$_POST['password_1'];
 
}
}if(empty($m4is_q6570)){
return;
 
}$m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_l17096->ID);
 if(!$m4is_h21895){
return;
 
}   $m4is_c67105 =[$this->m4is_r1546->m4is_j498('settings', 'password_field')=>$m4is_q6570, ];
 $m4is_u6591 =m4is_p40::m4is_x6560($m4is_h21895, $m4is_c67105);
  
}
} public 
function m4is_m943(int $m4is_f087, ?WP_User $m4is_d07928, array $m4is_x51046 ): void {
if(is_admin()){
return;
 
}if($this->m4is_r1546->m4is_c6286()){
return;
 
}$m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(empty($m4is_h21895 )){
return;
 
}$m4is_r35 =($m4is_x51046['user_email']!== $m4is_d07928->user_email );
    if($m4is_r35 ){
if(!empty($m4is_x51046['user_email'])){
$this->m4is_r1546->m4is_y6259($m4is_f087, $m4is_x51046['user_email']);
  
}
} $m4is_r2369 =(bool) $this->m4is_r1546->m4is_j498('settings', 'local_auth_only', false );
 if($m4is_r2369 ){
return;
 
}$m4is_r6234 =(string) $this->m4is_r1546->m4is_j498('settings', 'password_field', 'Password' );
 if(empty($m4is_r2369 )){
$new_password ='';
  if(!empty($_POST['pass1'])&&!empty($_POST['pass2'])&&$_POST['pass1']== $_POST['pass2']){
$new_password =$_POST['pass2'];
 
} if(!empty($_POST['password_1'])&&!empty($_POST['password_2'])&&$_POST['password_1']== $_POST['password_2']){
$new_password =$_POST['password_1'];
 
}if(empty($new_password )){
return;
 
}$updated_fields =[$m4is_r6234 =>$new_password, ];
 if(!empty($m4is_e32607 )){
$m4is_u6591 =m4is_p40::m4is_x6560($m4is_h21895, $updated_fields );
  
}     
}
} 
function m4is_e9466(){
 if($this->m4is_j168){
return TRUE;
 
} if(isset($_COOKIE['nextend_uniqid'])&&$_COOKIE['nextend_uniqid']> ''){
return TRUE;
 
}  return FALSE;
 
}    
function m4is_p3689($m4is_h973, $m4is_b4068 =0 ): int {
if($m4is_h973 == 0 ){
return 0;
 
}if(isset($this->m4is_r15839[$m4is_b4068])){
return $this->m4is_r15839[$m4is_b4068];
 
}if(current_user_can('manage_options' )){
return $m4is_h973;
 
}$m4is_b4068 =empty($m4is_b4068 )? get_the_id(): $m4is_b4068;
  $m4is_y66291 =['post_id' =>$m4is_b4068, ];
 $m4is_u987 =get_comments($m4is_y66291);
 $m4is_u987 =$this->m4is_v13206($m4is_u987);
 $m4is_u987 =$this->m4is_a87($m4is_u987);
 return count($m4is_u987 );
 
}
function m4is_v13206($m4is_u987 ){
if(empty($m4is_u987)){
return $m4is_u987;
 
}if(current_user_can('manage_options')){
return $m4is_u987;
 
};
 $m4is_p7646 =(int) get_the_author_meta('ID');
 $m4is_f087 =$this->m4is_r1546->m4is_x66();
 if($m4is_p7646 === $m4is_f087){
return $m4is_u987;
 
}$m4is_b4068 =get_the_id();
 $m4is_h62 =(bool) get_post_meta($m4is_b4068, '_is4wp_private_comments', true);
 if($m4is_h62){
m4is_j586::m4is_x7134();
 if(!$m4is_f087){
return [];
 
}if($m4is_f087 == $m4is_p7646){
return $m4is_u987;
 
}$m4is_r324 =[];
 if(is_array($m4is_u987)){
usort($m4is_u987, function($m4is_l1940, $m4is_h6256){
return $m4is_l1940->comment_ID > $m4is_h6256->comment_ID;
 
});
 foreach($m4is_u987 as $m4is_d07693 =>$m4is_z6401){
$m4is_a32861 =false;
 $m4is_a32861 =$m4is_a32861 ||($m4is_f087 == $m4is_z6401->user_id);
  $m4is_a32861 =$m4is_a32861 ||($m4is_z6401->user_id == $m4is_p7646);
  $m4is_a32861 =$m4is_a32861 ||user_can($m4is_z6401->user_id, 'manage_options');
  if(!$m4is_a32861){
$m4is_r324[]=$m4is_z6401->comment_ID;
 
}
}
}if(!empty($m4is_r324)){
foreach($m4is_u987 as $m4is_d07693 =>$m4is_z6401){
if(in_array($m4is_z6401->comment_ID, $m4is_r324)){
unset($m4is_u987[$m4is_d07693]);
 
}if(in_array($m4is_z6401->comment_parent, $m4is_r324)){
$m4is_r324[]=$m4is_z6401->comment_parent;
 unset($m4is_u987[$m4is_d07693]);
 
}
}
}
}$this->m4is_r15839[$m4is_b4068]=count($m4is_u987);
 return $m4is_u987;
 
}
function m4is_a87($m4is_u987 ){
global $wpdb;
 if(empty($m4is_u987)){
return $m4is_u987;
 
}$m4is_p7646 =(int) get_the_author_meta('ID');
 $m4is_f087 =$this->m4is_r1546->m4is_x66();
 $m4is_b846 =[];
 $m4is_b4068 =(int) get_the_id();
 foreach($m4is_u987 as $m4is_z6401 ){
$m4is_b846[]=(int) $m4is_z6401->comment_ID;
 
}if(!empty($m4is_b846 )){
$m4is_b846 =implode(',', $m4is_b846);
 $m4is_v2613 ="SELECT `comment_id` FROM `{$wpdb->prefix
}commentmeta` WHERE `meta_key` = 'memb_private' AND `meta_value` = '1' AND `comment_id` IN ( {$m4is_b846
} )";
 $m4is_e231 =$wpdb->get_col($m4is_v2613);
 unset($m4is_v2613);
 
}if(!empty($m4is_e231)){
m4is_j586::m4is_x7134();
 $m4is_l7956 =[];
 $m4is_j66 =$m4is_f087 === $m4is_p7646;
 $m4is_e66310 =current_user_can('manage_options');
 $m4is_q50 =$m4is_e66310 ||$m4is_j66;
 $m4is_n966 =is_user_logged_in();
  foreach ($m4is_u987 as $m4is_l9671 =>$m4is_z6401){
$m4is_v73 =$m4is_n966 &&($m4is_f087 == $m4is_z6401->user_id);
 if(!$m4is_v73){
$m4is_c6345 =in_array($m4is_z6401->comment_ID, $m4is_e231);
 if($m4is_c6345){
if($m4is_q50){
$m4is_z6401->comment_content ='<div class="memberium_private_comment"><strong>(' . __('MUTED'). ')</strong> ' . $m4is_z6401->comment_content . '</div>';
 
}else{
$m4is_l7956[]=$m4is_z6401->comment_ID;
 unset($m4is_u987[$m4is_l9671]);
 
}
}
}
} if(!empty($m4is_l7956)){
foreach ($m4is_u987 as $m4is_l9671 =>$m4is_z6401){
if(!empty($m4is_z6401->comment_parent)){
if(in_array($m4is_z6401->comment_parent, $m4is_l7956)){
$m4is_l7956[]=$m4is_z6401->comment_ID;
 unset($m4is_u987[$m4is_l9671]);
 
}
}
}
}
}$this->m4is_r15839[$m4is_b4068]=count($m4is_u987);
 return $m4is_u987;
 
}    public 
function m4is_t0612($m4is_u6591, $m4is_b4068 ){
return $this->m4is_x72168($m4is_b4068 );
 
} public 
function m4is_t749($m4is_m5907 ): bool {
$m4is_b4068 =0;
 $m4is_z653 =0;
 if(is_a($m4is_m5907, 'WP_Post' )){
$m4is_b4068 =$m4is_m5907->ID;
 $m4is_z653 =$m4is_m5907->post_parent;
 
}if(is_int($m4is_m5907 )){
$m4is_b4068 =$m4is_m5907;
 $m4is_z653 =wp_get_post_parent_id($m4is_b4068 );
 
}if(!empty($this->m4is_r1546->m4is_j498('settings', 'page_inheritance'))){
if(!empty($m4is_z653)){
$m4is_q61795 =$this->m4is_t749($m4is_z653);
 if($m4is_q61795){
m4is_j586::m4is_x7134();
 return true;
 
}
}
}$m4is_l79562 =get_post_meta($m4is_b4068 );
 $m4is_u450 =['_is4wp_access_tags', '_is4wp_access_tags2', '_is4wp_anonymous_only', '_is4wp_any_loggedin_user', '_is4wp_any_membership', '_is4wp_contact_ids', '_is4wp_membership_levels', ];
 foreach ($m4is_u450 as $m4is_l9671){
if(!empty($m4is_l79562[$m4is_l9671][0])){
m4is_j586::m4is_x7134();
 return true;
 
}
}return false;
 
} public 
function m4is_w0842($m4is_a32861, $m4is_e05491 =[], $m4is_c48062 ='', $m4is_c864 ='' ){
if(current_user_can('manage_options' )){
return true;
 
}$m4is_f087 =$this->m4is_r1546->m4is_x66();
 $m4is_x51 =is_user_logged_in();
 $m4is_k824 =m4is_q82::m4is_d59($m4is_f087 );
 $m4is_l36716 =false;
 $m4is_y642 =['any_membership' =>0, 'contact_ids' =>'', 'eval' =>'', 'logged_in_only' =>0, 'logged_out_only' =>0, 'memberships' =>'', 'tags1' =>'', 'tags2' =>'', ];
 $m4is_e05491 =wp_parse_args($m4is_e05491, $m4is_y642 );
 foreach($m4is_e05491 as $m4is_l9671 =>$m4is_v586 ){
if(!empty($m4is_v586 )){
m4is_j586::m4is_x7134();
 break;
 
}
}  $m4is_c136 =(empty($m4is_e05491['logged_in_only'])? 0 : (int) (bool) $m4is_e05491['logged_in_only']);
 if($m4is_c136 &&!$m4is_x51 ){
return false;
 
} $m4is_v16 =(empty($m4is_e05491['logged_out_only'])? 0 : (int) (bool) $m4is_e05491['logged_out_only']);
 if($m4is_v16 &&$m4is_x51 ){
return false;
 
} if(!empty($m4is_e05491['any_membership'])){
if(empty($m4is_k824['memb_user']['membership_tags'])){
return false;
 
}
} if(!empty($m4is_e05491['tags1'])){
if(!$this->m4is_r1546->m4is_m2480($m4is_e05491['tags1'])){
return false;
 
}
}if(!empty($m4is_e05491['tags2'])){
if(!$this->m4is_r1546->m4is_m2480($m4is_e05491['tags2'])){
return false;
 
}
} if($m4is_e05491['contact_ids']){
$m4is_e05491['contact_ids']=trim($m4is_e05491['contact_ids'], ', ' );
 $m4is_h21895 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'crm_id', 0 );
 $m4is_c1796 =array_filter(explode(',', $m4is_e05491['contact_ids']));
 if(!in_array($m4is_h21895, $m4is_c1796 )){
return false;
 
}
}  if(!empty($m4is_e05491['memberships'])){
$m4is_l379 =array_filter(explode(',', m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'membership_tags', '' )));
 $m4is_d6687 =array_filter(explode(',', $m4is_e05491['memberships']));
 $m4is_y4793 =(bool) count(array_intersect($m4is_l379, $m4is_d6687 ));
   if(!$m4is_y4793 ){
$m4is_n92646 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'membership_level', 0 );
 $m4is_y6196 =0;
 $m4is_m96240 =$this->m4is_r1546->m4is_j498('memberships' );
  foreach ($m4is_d6687 as $id){
if(isset($m4is_m96240[$id]['level'])&&$m4is_m96240[$id]['level']> 0){
$m4is_y6196 =($m4is_m96240[$id]['level']< $m4is_y6196)? $m4is_m96240[$id]['level']: $m4is_y6196;
 
}
}if($m4is_y6196){
if($m4is_n92646 >= $m4is_y6196){
$m4is_y4793 =true;
 
}
}
}if(!$m4is_y4793){
return false;
 
}
}if(!empty($m4is_e05491['eval'])){
if(!class_exists('m4is_b54' )){
error_log('Memberium: [error] Eval Function Class file missing.' );
 
}else{
$m4is_e05491['eval']='return ' . trim(html_entity_decode($m4is_e05491['eval'], ENT_QUOTES ||ENT_HTML401 ||ENT_XML1 ||ENT_XHTML ||ENT_HTML5)). ';';
 $m4is_u6591 =m4is_b54::m4is_i3025($m4is_e05491['eval']);
 
}if(!$m4is_u6591){
return false;
 
}
}return true;
 
} public 
function m4is_n35962($m4is_r02674, $m4is_t172 ){
global $wp_query;
 if(function_exists('wp_get_current_user')&&current_user_can('manage_options')){
return $m4is_r02674;
 
}if(!is_array($m4is_r02674)){
$m4is_r02674 =[$m4is_r02674];
 
}if(defined('DOING_AJAX')){
return $m4is_r02674;
 
}$m4is_g7623 =$this->m4is_i26071();
 $m4is_n1860 =count($m4is_r02674);
 $m4is_r735 =[];
 $m4is_f92 =is_singular();
 $m4is_t9835 =!$m4is_f92;
 foreach ($m4is_r02674 as $m4is_m5907){
$m4is_b4068 =$m4is_m5907->ID;
 $m4is_n80241 =(int) $m4is_m5907->post_parent;
 $post_visible =$this->m4is_x72168($m4is_b4068);
 $m4is_t9835 =$m4is_t9835 ||($m4is_g7623 <> $m4is_b4068);
 if($post_visible){
$m4is_r735[]=$m4is_m5907;
 
}else{
if($this->m4is_r1546->m4is_j498('settings', 'default_page_redirect')> ''){
$m4is_g4173 =FALSE;
 
}else{
$m4is_g4173 =$this->can_cache;
 $m4is_g4173 =FALSE;
 
} $m4is_p1084 =$this->m4is_a26($m4is_b4068, false);
 if($m4is_p1084 == 'redirect'){
$m4is_p1084 ='hide';
 
}    if($m4is_p1084 == 'excerpt'){
if(trim($m4is_m5907->post_excerpt)> ''){
$m4is_m5907->post_content =$m4is_m5907->post_excerpt;
 
}else{
$m4is_m5907->post_content =$this->m4is_r1546->m4is_j498('settings', 'global_excerpt');
 
}$m4is_m5907->excerpt_only =1;
 $m4is_r735[]=$m4is_m5907;
 
}if($m4is_t9835 &&$m4is_p1084 == 'redirect'){
$m4is_p1084 ='hide';
 
}  if($m4is_f92 ){
if($m4is_p1084 == 'hide'){
if(isset($GLOBALS['the_wp_query'])&&method_exists($GLOBALS['the_wp_query'], 'set_404')&&is_single($m4is_b4068)){
$GLOBALS['the_wp_query']->set_404();
 
}
}if($m4is_p1084 == 'redirect' ){
$this->m4is_m6637($m4is_b4068, 'Memberium Page Protection' );
 
}
}
}
} return $m4is_r735;
 
} public 
function m4is_j720($m4is_r02674, $m4is_v76912 ){
global $wp_query;
 if(current_user_can('manage_options' )){
return $m4is_r02674;
 
} if(isset($m4is_v76912->query['post_type'])&&$m4is_v76912->query['post_type']== 'wp_global_styles' ){
return $m4is_r02674;
 
}if(!is_array($m4is_r02674 )&&!is_object($m4is_r02674 )){
return $m4is_r02674;
 
}if(!is_array($m4is_r02674)){
$m4is_r02674 =[$m4is_r02674];
 
}if(empty($m4is_r02674 )){
return $m4is_r02674;
 
}if(empty($this->m4is_h30952 )){
global $wp_the_query;
 if(!empty($wp_the_query->is_singular )){
if(isset($m4is_v76912->posts[0]->ID )){
$this->m4is_h30952 =$m4is_v76912->posts[0]->ID;
 
}
}
}static $m4is_t493 =0;
 $m4is_t493++;
 $m4is_r735 =[];
 $m4is_n1860 =count($m4is_r02674);
 $m4is_g4173 =false;
 $m4is_b62710 =$this->m4is_r1546->m4is_j498('settings', 'force_learndash_inheritance', 0 );
 $m4is_m836 =$this->m4is_r1546->m4is_j498('settings', 'default_page_redirect', 0 );
 $m4is_v893 =$this->m4is_r1546->m4is_j498('settings', 'global_excerpt', 0 );
 $m4is_d369 =$this->m4is_r1546->m4is_j498('settings', 'autogenerate_excerpts', 0 );
 $m4is_n210 =apply_filters('memberium/ignored_post_types', ['elementor_library']);
  $m4is_q94 =['reply', 'topic', ];
 foreach ($m4is_r02674 as $m4is_m5907 ){
if(in_array($m4is_m5907->post_type, $m4is_n210 )){
$m4is_r735[]=$m4is_m5907;
 break;
 
}$m4is_q485 =$m4is_m5907->post_type;
 $m4is_b4068 =$m4is_m5907->ID;
 $m4is_n80241 =(int) $m4is_m5907->post_parent;
 $m4is_q07 =$this->m4is_x72168($m4is_b4068 );
 if(empty($m4is_m5907->post_excerpt)){
$m4is_m5907->post_excerpt =$this->m4is_c376($m4is_m5907);
 
}if(!empty($m4is_b62710)){
 $m4is_q94 =['sfwd-lessons', 'sfwd-quiz', 'sfwd-topic', ];
 if(in_array($m4is_q485, $m4is_q94 )){
$m4is_n3691 =(int) get_post_meta($m4is_m5907->ID, 'course_id', true);
 $m4is_b1249 =(int) get_post_meta($m4is_m5907->ID, 'lesson_id', true);
 if($m4is_q07 &&$m4is_n3691){
$m4is_q07 =$this->m4is_x72168($m4is_n3691);
 
}if($m4is_q07 &&$m4is_b1249){
$m4is_q07 =$this->m4is_x72168($m4is_b1249);
 
}
}
}if($m4is_q07){
$m4is_m5907 =$this->m4is_e6594($m4is_m5907);
 $m4is_r735[]=$m4is_m5907;
 
}elseif(in_array($m4is_q485, $this->m4is_s32679)){
if(!empty($m4is_m5907->post_excerpt)){
$m4is_m5907->post_excerpt =$this->m4is_c376($m4is_m5907);
 $m4is_m5907->post_content =$m4is_m5907->post_excerpt;
 
}else{
$m4is_m5907->post_content =$this->m4is_p52('', $m4is_m5907);
 
}$m4is_m5907->post_excerpt =$this->m4is_c376($m4is_m5907);
 $m4is_r735[]=$m4is_m5907;
 
}else{
if(!empty($m4is_m836 )){
$m4is_g4173 =false;
 
}else{
$m4is_g4173 =$this->can_cache;
 $m4is_g4173 =false;
 
} $m4is_p1084 =$this->m4is_a26($m4is_b4068, false );
 if($m4is_p1084 == 'redirect' ){
$m4is_p6034 =false;
 $m4is_p6034 =apply_filters('memberium/post_protecting/override_redirect/pre', $m4is_p6034, $m4is_b4068 );
 if(isset($GLOBALS['post'])&&is_a($GLOBALS['post'], 'WP_Post' )){
if($GLOBALS['post']->ID == 0){
$m4is_p6034 =true;
 
}
}$m4is_p6034 =$m4is_p6034 ||(bool) did_action('get_header' );
 $m4is_p6034 =$m4is_p6034 ||$this->m4is_h30952 !== $m4is_b4068;
 $m4is_p6034 =$m4is_p6034 ||headers_sent();
 $m4is_p6034 =$m4is_p6034 ||wp_doing_ajax();
 $m4is_p6034 =$m4is_p6034 ||$this->m4is_r1546->m4is_q8965();
 $m4is_p6034 =apply_filters('memberium/post_protecting/override_redirect/post', $m4is_p6034, $m4is_b4068 );
 if($m4is_p6034 ){
$m4is_p1084 ='hide';
 
}
}    if($m4is_p1084 == 'excerpt'){
if(trim($m4is_m5907->post_excerpt)> ''){
$m4is_m5907->post_content =$m4is_m5907->post_excerpt;
 
}else{
$m4is_m5907->post_excerpt =$this->m4is_c376($m4is_m5907);
 $m4is_m5907->post_content =$m4is_m5907->post_excerpt;
 $m4is_m5907->post_content .= $m4is_v893;
 
}$m4is_m5907->excerpt_only =1;
 $m4is_r735[]=$m4is_m5907;
 
}elseif($m4is_p1084 == 'hide'){
 if(method_exists($m4is_v76912, 'set_404')&&is_single()){
$m4is_v76912->set_404();
 
}
}elseif($m4is_p1084 == 'redirect' ){
$this->m4is_m6637($m4is_b4068, 'Memberium Post Protection' );
 exit;
 
}
}
}if($m4is_d369 &&!is_singular()){
foreach ($m4is_r735 as &$new_post){
$new_post->post_content =$new_post->post_excerpt;
 
}
} $new_count =count($m4is_r735);
 if($new_count <> count($m4is_v76912->posts)){
$m4is_v76912->posts =$m4is_r735;
 $m4is_v76912->found_posts =($m4is_v76912->found_posts - ($m4is_n1860 - count($m4is_r735)));
 if($m4is_v76912->query_vars['posts_per_page']){
$m4is_v76912->max_num_pages =(int) ceil($m4is_v76912->found_posts / $m4is_v76912->query_vars['posts_per_page']);
 
}
}return $m4is_r735;
 
}private 
function m4is_c16308(){
if(is_admin()){
return;
 
}if(!$this->m4is_r1546->m4is_j498('settings', 'attachment_pages', 0 )){
return;
 
}add_filter('attachment_link', function($m4is_c67 ){
return;
 
});
 add_filter('rewrite_rules_array', function($m4is_w6570 ){
foreach ($m4is_w6570 as $m4is_a83 =>$m4is_v76912 ){
if(strpos($m4is_a83, 'attachment' )||strpos($m4is_v76912, 'attachment' )){
unset($m4is_w6570[$m4is_a83]);
 
}
}return $m4is_w6570;
 
});
 
}public 
function m4is_x72168(int $m4is_b4068, int $m4is_f087 =0 ): bool {
static $m4is_w2516 =[];
 static $m4is_m96240;
 static $login_post_id;
 static $m4is_d48;
 static $m4is_h148;
 $m4is_m90 =$this->m4is_r1546->m4is_d3087('prohibited_action_override' );
  if(in_array($m4is_m90, ['show', 'excerpt'])){
return true;
 
}if(empty($m4is_b4068 )){
return false;
 
}$m4is_f087 =$m4is_f087 ? $m4is_f087 : $this->m4is_r1546->m4is_x66();
 if(function_exists('get_userdata' )&&user_can($m4is_f087, 'manage_options' )){
return true;
 
}$m4is_m96240 ??= $this->m4is_r1546->m4is_j498('memberships' );
 $login_post_id ??= $this->m4is_r1546->m4is_j498('settings', 'login_url', 0 );
 $m4is_d48 ??= $this->m4is_r1546->m4is_j498('settings', 'page_inheritance', 0 );
 $m4is_h148 ??= is_admin();
  if(function_exists('powerpress_is_custom_podcast_feed' )&&powerpress_is_custom_podcast_feed()){
return true;
 
}if(is_a($m4is_b4068, 'WP_Post' )){
$m4is_m5907 =$m4is_b4068;
 
}else{
$m4is_m5907 =get_post($m4is_b4068 );
 
}$m4is_b4068 =$m4is_m5907->ID;
 if(empty($m4is_m5907 )){
return true;
 
}$m4is_k824 =m4is_q82::m4is_d59($m4is_f087 );
 $m4is_h21895 =m4is_q82::m4is_t660($m4is_f087 );
 $m4is_t42961 =$this->m4is_r1546->m4is_x66();
 $m4is_g4173 =true;
  if(isset($m4is_w2516[$m4is_f087][$m4is_b4068])){
return !$m4is_w2516[$m4is_f087][$m4is_b4068];
 
} if(!$m4is_h148 ){
if($m4is_m5907->post_type == 'attachment' ){
if($this->m4is_r1546->m4is_j498('settings', 'attachment_pages' )){
$m4is_w2516[$m4is_f087][$m4is_b4068]=false;
 return false;
 
}
}
} $m4is_l36716 =false;
 $m4is_c309 =false;
 $m4is_d35066 =0;
  $m4is_v56269 =0;
 $m4is_l79562 =$this->m4is_r1546->m4is_v20183($m4is_b4068, '' );
 $m4is_l156 =empty($m4is_k824['memb_user']['crm_id'])? 0 : $m4is_k824['memb_user']['crm_id'];
    if(!empty($m4is_l79562['_is4wp_force_public'])){
return true;
 
} if(function_exists('is_user_logged_in' )&&!is_user_logged_in()){
 if($m4is_l79562['_is4wp_facebook_crawler']){
if($this->m4is_l497()||$this->m4is_n49()){
return true;
 
}
}  if($m4is_l79562['_is4wp_google_1stclick']){
if($this->m4is_q06()||$this->m4is_m4521()){
return true;
 
}if(isset($_SERVER['HTTP_REFERER'])&&stristr($_SERVER['HTTP_REFERER'], 'google.' )!== FALSE ){
return true;
 
} if(isset($_SERVER['HTTP_REFERER'])&&stripos($_SERVER['HTTP_REFERER'], 'https://www.facebook.com/' )=== 0 ){
return true;
 
}
}
}else{
 if($m4is_l79562['_is4wp_anonymous_only']&&function_exists('is_user_logged_in' )&&is_user_logged_in()){
return false;
 
}
}  if($login_post_id > 0 &&$login_post_id == $m4is_b4068 ){
return true;
 
} if($m4is_d48 ){
if($m4is_m5907->post_parent ){
if(!$this->m4is_x72168($m4is_m5907->post_parent, $m4is_f087 )){
$m4is_l36716 =true;
 
}
}
} if($m4is_l79562['_is4wp_any_loggedin_user']&&function_exists('is_user_logged_in' )&&!is_user_logged_in()){
$m4is_l36716 =true;
 
} if(!$m4is_l36716 ){
if(!empty(trim($m4is_l79562['_is4wp_contact_ids']))){
$m4is_c1796 =array_filter(explode(',', $m4is_l79562['_is4wp_contact_ids']));
 if(!in_array($m4is_l156, $m4is_c1796 )){
$m4is_l36716 =TRUE;
 
}
}
} if(!$m4is_l36716 ){
if($m4is_l79562['_is4wp_any_membership']&&empty($m4is_k824['memb_user']['membership_tags'])){
$m4is_l36716 =TRUE;
 
}
} if(!$m4is_l36716 ){
if(!empty($m4is_l79562['_is4wp_membership_levels'])){
if(empty($m4is_k824['memb_user']['membership_tags'])){
$m4is_l36716 =true;
 
}else{
$m4is_a538 =array_filter(explode(',', $m4is_l79562['_is4wp_membership_levels']));
 $m4is_p6453 =array_filter(explode(',', $m4is_k824['memb_user']['membership_tags']));
 $m4is_b692 =self::m4is_m2566($m4is_p6453, $m4is_a538 );
 if(!$m4is_b692 ){
$m4is_v697 =self::m4is_l67($m4is_a538, $m4is_m96240 );
 if($m4is_k824['memb_user']['membership_level']<= $m4is_v697 ){
$m4is_l36716 =true;
 
}
}
}
}
} if($m4is_d35066 < 1 &&!$m4is_l36716 &&!empty($m4is_l79562['_is4wp_access_tags'])){
if(!$this->m4is_r1546->m4is_m2480($m4is_l79562['_is4wp_access_tags'])){
$m4is_l36716 =true;
 
}if(!$m4is_l36716 &&!empty($m4is_l79562['_is4wp_access_tags2'])){
if(!$this->m4is_r1546->m4is_m2480($m4is_l79562['_is4wp_access_tags2'])){
$m4is_l36716 =true;
 
}
}
}if($m4is_g4173 ){
$m4is_w2516[$m4is_f087][$m4is_b4068]=(int) $m4is_l36716;
 
}return !$m4is_l36716;
 
}    private 
function m4is_m2566($m4is_p6453, $m4is_a538 ): bool {
if(empty($m4is_p6453 )){
return false;
 
}return (bool) array_intersect($m4is_p6453, $m4is_a538 );
 
} private 
function m4is_l67($m4is_a538, $m4is_m96240 ): int {
$m4is_v697 =0;
 foreach($m4is_a538 as $m4is_z42 ){
$m4is_a81 =$m4is_m96240[$m4is_z42]['level']? $m4is_m96240[$m4is_z42]['level']: 0;
 $m4is_v697 =max($m4is_a81, $m4is_v697 );
 
}return $m4is_v697;
 
} public 
function m4is_m6637(int $m4is_b4068, string $m4is_w42 ='' ): void {
if(headers_sent()){
return;
 
}$m4is_f56 =get_post_meta($m4is_b4068, '_is4wp_redirect_url', true );
 if(empty($m4is_f56 )){
$m4is_f56 =$this->m4is_r1546->m4is_j498('settings', 'default_page_redirect', '' );
 
}if(empty($m4is_f56 )){
$m4is_y90287 =$this->m4is_r1546->m4is_j498('settings', 'login_url', 0 );
 if($m4is_y90287 < 1 ){
$m4is_f56 =wp_login_url(site_url());
 
}else{
$m4is_f56 =get_permalink($m4is_y90287 );
 
}
}if(strpos($m4is_f56, '{{' )!== false ){
$m4is_f087 =$this->m4is_r1546->m4is_x66();
 $m4is_y34791 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'login_page', 0 );
 $m4is_r19664 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'logout_page', 0 );
 $m4is_f56 =str_replace('{{current.url}}', $_SERVER['REQUEST_URI'], $m4is_f56);
 $m4is_f56 =str_replace('{{cachebuster}}', mt_rand(1000000, 9999999 ), $m4is_f56);
 if(!empty($m4is_y34791 )){
$m4is_f56 =str_replace('{{member.homepage}}', get_permalink($m4is_y34791), $m4is_f56);
 
}else{
$m4is_f56 =str_replace('{{member.homepage}}', site_url(), $m4is_f56);
 
}if(!empty($m4is_r19664 )){
$m4is_f56 =str_replace('{{member.logout}}', get_permalink($m4is_r19664 ), $m4is_f56);
 
}else{
$m4is_f56 =str_replace('{{member.logout}}', site_url(), $m4is_f56);
 
}if(stripos($m4is_f56, '{{post.')!== FALSE ){
$m4is_f56 =preg_replace_callback('|({{post\.(.*)}})|U', function($m4is_a157 ){
return get_permalink($m4is_a157[2]);
 
}, $m4is_f56 );
 
}
}$m4is_f56 =trim(do_shortcode($m4is_f56 ));
 $m4is_f56 =apply_filters('memberium_post_redirect_url', $m4is_f56, $m4is_b4068 );
 if(!empty($m4is_f56 )){
$m4is_w42 =$m4is_w42 ? $m4is_w42 : 'Memberium Post Protection';
 wp_redirect($m4is_f56, 302, $m4is_w42 );
 exit;
 
}
} public 
function m4is_a26(int $m4is_b4068, $m4is_d095 =NULL ): string {
 if(!function_exists('wp_get_current_user' )){
return 'granted';
 
}if($this->m4is_r1546->m4is_v461()){
return 'granted';
 
}if(!get_post_status($m4is_b4068 )){
return 'hide';
 
}$m4is_o2804 =true;
 $m4is_x863 =[];
    if($this->m4is_r1546->m4is_d3087('prohibited_action_override' )== '' ){
$m4is_p1084 =strtolower(trim (get_post_meta($m4is_b4068, '_is4wp_prohibited_action', true )));
 
}else{
$m4is_p1084 =$this->m4is_r1546->m4is_d3087('prohibited_action_override');
 $m4is_o2804 =false;
 $m4is_x863[]='Prohibited Action Override';
 
}if($m4is_p1084 == 'show'){
$m4is_p1084 ='granted';
 $m4is_x863[]='Prohibited Action  = Show';
 
} if($m4is_p1084 == 'default' ||$m4is_p1084 == ''){
$m4is_p1084 =$this->m4is_r1546->m4is_j498('settings', 'default_prohibited_action');
 if($m4is_p1084 == ''){
$m4is_p1084 ='hide';
 $m4is_x863[]='Prohibited Action is empty ' . __LINE__;
 
}
}if($m4is_p1084 == ''){
$m4is_p1084 ='hide';
 $m4is_x863[]='Prohibited Action is empty ' . __LINE__;
 
}if($m4is_p1084 == 'excerpt' ){
if(strtolower(wp_get_theme())== 'optimizepress' ){
$m4is_p1084 ='redirect';
 $m4is_x863[]='OptimizePress Theme ' . __LINE__;
 
}
}if($m4is_p1084 == 'redirect'){
if(headers_sent()){
$m4is_p1084 ='hide';
 
}elseif((!empty($post->ID))&&$this->m4is_i26071()!== $m4is_b4068 ){
$m4is_p1084 ='hide';
 
}elseif($this->in_list == 1){
$m4is_p1084 ='hide';
 
}elseif(!is_singular()){
$m4is_p1084 ='hide';
 
}elseif(in_the_loop()){
$m4is_p1084 ='hide';
 
}elseif(is_search()){
$m4is_p1084 ='hide';
 
}elseif(is_archive()){
$m4is_p1084 ='hide';
 
}elseif(is_preview()){
$m4is_p1084 ='hide';
 
}elseif(is_feed()){
$m4is_p1084 ='hide';
 
}elseif(!is_main_query()){
$m4is_p1084 ='hide';
 
}
} if($m4is_p1084 == 'redirect'){

} if($m4is_o2804){
$m4is_w2516[$m4is_b4068]=$m4is_p1084;
 
}return $m4is_p1084;
 
}   
function m4is_c376($m4is_m5907 ){
if(!is_a($m4is_m5907, 'WP_Post')){
return;
 
}if(empty($m4is_m5907->post_excerpt)){
$m4is_d369 =$this->m4is_r1546->m4is_j498('settings', 'autogenerate_excerpts');
 $m4is_o86127 =$this->m4is_r1546->m4is_j498('settings', 'include_default_excerpt');
 if($m4is_d369){
$m4is_m5907->post_excerpt =trim($this->m4is_l36($m4is_m5907->post_excerpt));
 $m4is_r53 =$this->m4is_r1546->m4is_j498('settings', 'excerpt_length', 55);
 $m4is_m5907->post_excerpt =wp_trim_words(strip_shortcodes($this->m4is_l36($m4is_m5907->post_content)), $m4is_r53);
 
}if(!empty($m4is_o86127)){
$m4is_v893 =$this->m4is_r1546->m4is_j498('settings', 'global_excerpt');
 if(!empty($m4is_v893)){
if($m4is_o86127 == 'append'){
$m4is_m5907->post_excerpt .= $m4is_v893;
 
}elseif($m4is_o86127 == 'prepend'){
$m4is_m5907->post_excerpt =$m4is_v893 . $m4is_m5907->post_excerpt;
 
}elseif($m4is_o86127 == 'embed'){
$m4is_m5907->post_excerpt =str_replace('{{excerpt}}', $m4is_m5907->post_excerpt, $m4is_v893);
 
}
}
}
}$m4is_m5907->post_excerpt =str_replace('{{excerpt}}', '', $m4is_m5907->post_excerpt);
 return $m4is_m5907->post_excerpt;
 
}
function m4is_l36($m4is_t09761){
if(strpos($m4is_t09761, '[vc_')!== false){
$m4is_t09761 =preg_replace('/\[vc_.*\]/', '', $m4is_t09761);
 $m4is_t09761 =preg_replace('/\[\/vc_.*\]/', '', $m4is_t09761);
 $m4is_t09761 =preg_replace('/\[et_.*\]/', '', $m4is_t09761);
 $m4is_t09761 =preg_replace('/\[\/et_.*\]/', '', $m4is_t09761);
 
}return $m4is_t09761;
 
}
function m4is_x668($m4is_m5907){
if(is_a($m4is_m5907, 'WP_Post')){
$m4is_m5907->post_excerpt =$this->m4is_c376($m4is_m5907);
 
}return $m4is_m5907;
 
} 
function m4is_d40658($m4is_v068 =false, $m4is_i1645 =[]): bool {
if($this->is_administrator ){
return false;
 
}if($this->m4is_r1546->m4is_z56()== 0){
return false;
 
} $m4is_z69542 =[];
 $m4is_l9321 =array_filter(explode(',', m4is_q82::m4is_d6758($this->m4is_r1546->m4is_x66(), 'memb_user', 'tags', '' )));
 $m4is_m96240 =$this->m4is_r1546->m4is_j498('memberships' );
  if(!is_array($m4is_i1645)&&$m4is_i1645 > ''){
$m4is_i1645 =explode(',', $m4is_i1645);
 
}if(is_array($m4is_i1645)){
foreach ($m4is_i1645 as &$level){
$level =strtolower(trim($level));
 
}
} if(is_array($m4is_m96240)){
foreach ($m4is_m96240 as $m4is_w64){
if(empty($m4is_i1645)||in_array(trim(strtolower($m4is_w64['name'])), $m4is_i1645)){
$m4is_z69542[]=$m4is_w64['payf_id'];
 if(!$m4is_v068){
$m4is_z69542[]=$m4is_w64['cancel_id'];
 $m4is_z69542[]=$m4is_w64['suspend_id'];
 
}
}
}
}return (boolean) count(array_intersect($m4is_l9321, $m4is_z69542 ));
 
}    
function m4is_s067($m4is_o41, $m4is_b4068){
if(!$m4is_o41){
$m4is_v1690 =trim(get_post_meta($m4is_b4068, '_is4wp_can_comment', true));
 if(!empty($m4is_v1690)){
$m4is_o41 =(bool) $this->m4is_r1546->m4is_m2480($m4is_v1690);
 
}
}return $m4is_o41;
 
}
function m4is_e6594($m4is_m5907){
if(is_integer($m4is_m5907)){
$m4is_m5907 =get_post($m4is_m5907);
 
}if(is_a($m4is_m5907, 'WP_Post')){
if($m4is_m5907->comment_status == 'open'){
return $m4is_m5907;
 
}$m4is_g29 =trim(get_post_meta($m4is_m5907->ID, '_is4wp_can_comment', true));
 if(empty($m4is_g29)){
return $m4is_m5907;
 
}else{
if($this->m4is_r1546->m4is_m2480($m4is_g29)){
$m4is_m5907->comment_status ='open';
 return $m4is_m5907;
 
}
}
}return $m4is_m5907;
 
} 
function m4is_w271($m4is_k49710, $m4is_m43, $m4is_z6401){
if($m4is_m43 != $m4is_k49710){
if($m4is_k49710 == 'approved'){
$m4is_b4068 =$m4is_z6401->comment_post_ID;
 $m4is_h21895 =$this->m4is_r1546->m4is_o70($m4is_z6401->comment_author_email);
 if(!$m4is_h21895 ||!$m4is_b4068){
return;
 
}$m4is_l9321 =get_post_meta($m4is_b4068, '_is4wp_commenter_tag', true);
 $m4is_j15 =get_post_meta($m4is_b4068, '_is4wp_commenter_action', true);
 $m4is_j661 =get_post_meta($m4is_b4068, '_is4wp_commenter_goal', true);
 if($m4is_l9321 > ''){
$this->m4is_r1546->m4is_k98($m4is_l9321, $m4is_h21895 );
 
}if($m4is_j15 > ''){
$this->m4is_r1546->m4is_u71903($m4is_j15, $m4is_h21895);
 
}if($m4is_j661 > ''){
$this->m4is_r1546->m4is_t64038($m4is_j661, $m4is_h21895);
 
}
}
}
} 
function m4is_y6410($m4is_h516, $m4is_z6401){
$m4is_f087 =$m4is_z6401->user_id;
 $m4is_b08 =(bool) get_user_meta($m4is_f087, 'memberium_private_comments', true );
 update_comment_meta($m4is_h516, 'memb_private', $m4is_b08);
 if($m4is_z6401->comment_approved == 1){
$m4is_h21895 =$this->m4is_r1546->m4is_z56();
 $m4is_b4068 =$m4is_z6401->comment_post_ID;
 if(!$m4is_h21895 ||!$m4is_b4068){
return;
 
}$m4is_l9321 =get_post_meta($m4is_b4068, '_is4wp_commenter_tag', true);
 $m4is_j15 =get_post_meta($m4is_b4068, '_is4wp_commenter_action', true);
 $m4is_j661 =get_post_meta($m4is_b4068, '_is4wp_commenter_goal', true);
 if($m4is_l9321 > ''){
$this->m4is_r1546->m4is_k98($m4is_l9321, $m4is_h21895 );
 
}if($m4is_j15 > ''){
$this->m4is_r1546->m4is_u71903($m4is_j15, $m4is_h21895);
 
}if($m4is_j661 > ''){
$this->m4is_r1546->m4is_t64038($m4is_j661, $m4is_h21895);
 
}
}
} public 
function m4is_a43(array $m4is_w89066, object $m4is_t8566, array $m4is_y66291 ): array {
$this->in_list =1;
 $m4is_i6429 =[];
 $m4is_e66310 =current_user_can('manage_options');
  if(is_array($m4is_w89066)){
foreach ($m4is_w89066 as $m4is_l9671 =>$m4is_c4069){
$m4is_f0174 =true;
 $m4is_b4068 =(int) $m4is_c4069->ID;
 $m4is_i66802 =(int) $m4is_c4069->menu_item_parent;
 $m4is_k15 =['{{' =>'[', '}}' =>']', ];
 if(!$m4is_e66310){
if($m4is_f0174){
if($m4is_c4069->menu_item_parent > 0 &&in_array($m4is_c4069->menu_item_parent, $m4is_i6429)){
$m4is_f0174 =false;
 
}
}if($m4is_f0174){
$m4is_f0174 =!(boolean) get_post_meta($m4is_b4068, '_is4wp_hide_from_menu', true);
 
} if($m4is_f0174){
if($m4is_c4069->type == 'post_type' &&!$this->m4is_x72168($m4is_b4068)){
$m4is_p1084 =$this->m4is_a26($m4is_b4068);
  if($m4is_p1084 == 'hide' ||$m4is_p1084 == 'redirect'){
$m4is_f0174 =false;
 
}
}
}
}if($m4is_f0174){
if(strpos($m4is_c4069->name, '[')!== false ||strpos($m4is_c4069->name, '{{')!== false){
$m4is_v3458 =strtr($m4is_w89066[$m4is_l9671]->name, $m4is_k15);
 $m4is_w89066[$m4is_l9671]->name =do_shortcode($m4is_v3458);
 
}if(strpos($m4is_c4069->url, '[')!== false ||strpos($m4is_c4069->url, '{{')!== false){
$m4is_v3458 =strtr($m4is_w89066[$m4is_l9671]->url, $m4is_k15);
 $m4is_w89066[$m4is_l9671]->url =do_shortcode(urldecode($m4is_v3458));
 
}
}if(!$m4is_f0174){
$m4is_i6429[]=$m4is_b4068;
 unset($m4is_w89066[$m4is_l9671]);
 
}
}
}$this->in_list =0;
 return $m4is_w89066;
 
} private 
function m4is_l84956(): array {
static $m4is_k26;
 if(is_array($m4is_k26 )){
return $m4is_k26;
 
}$m4is_t265 =false;
 $m4is_o14 ='memberium/posts';
 $m4is_q1046 ='meta/access/hidden_menu_items';
 $m4is_w46279 =MINUTE_IN_SECONDS;
 $m4is_k26 =wp_cache_get($m4is_o14, $m4is_q1046, false, $m4is_t265 );
 if($m4is_t265 === false ||!is_array($m4is_k26 )){
$m4is_u076 =['meta_query' =>[['key' =>'_is4wp_hide_from_menu', 'compare' =>'=', 'value' =>1, ], ], 'posts_per_page' =>-1, 'post_type' =>'any',  'fields' =>'ids',  ];
 $m4is_k26 =get_posts($m4is_u076 );
 $m4is_k26 =is_array($m4is_k26 )? $m4is_k26 : [];
 wp_cache_set($m4is_q1046, $m4is_k26, $m4is_o14, $m4is_w46279 );
 
}return $m4is_k26;
 
} public 
function m4is_m665(array $m4is_x46058 ): array {
 static $m4is_f40385;
 static $m4is_q0795;
 if(empty($m4is_x46058 )){
return $m4is_x46058;
 
}$m4is_q0795 ??= $this->m4is_l84956();
 $m4is_e66310 =current_user_can('manage_options');
 $m4is_f087 =$this->m4is_r1546->m4is_x66();
 $m4is_e1732 =array_column($m4is_x46058, 'ID' );
 $m4is_h04362 =[];
 $m4is_n966 =is_user_logged_in();
 foreach($m4is_x46058 as $m4is_l9671 =>$m4is_g3762 ){
if(in_array($m4is_g3762->object_id, $m4is_q0795 )){
$m4is_h04362[]=$m4is_g3762->ID;
 unset($m4is_x46058[$m4is_l9671]);
 
}
} $m4is_f40385 ??= $this->m4is_r1546->m4is_j498('settings', 'login_url');
 $this->in_list =1;
 $m4is_i6429 =[];
 $m4is_s275 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'logout_page', 0 );
 $m4is_d37 =empty($m4is_s275 )? site_url('/' ): get_permalink($m4is_s275 );
 foreach ($m4is_x46058 as $m4is_l9671 =>$m4is_g3762){
$m4is_f0174 =true;
 $m4is_a29 =(int) $m4is_g3762->ID;
 $m4is_b4068 =(int) $m4is_g3762->object_id;
 $m4is_i66802 =(int) $m4is_g3762->menu_item_parent;
 if(!$m4is_e66310 ){
 if($m4is_f0174 ){
if($m4is_g3762->menu_item_parent > 0 &&in_array($m4is_g3762->menu_item_parent, $m4is_i6429 )){
$m4is_f0174 =false;
 
}
}if($m4is_f0174 &&$m4is_g3762->type == 'post_type' ){
$m4is_f0174 =!(boolean) get_post_meta($m4is_g3762->object_id, '_is4wp_hide_from_menu', true );
 
} if($m4is_f0174 ){
if($m4is_g3762->type == 'post_type' &&!$this->m4is_x72168($m4is_b4068 )){
$m4is_p1084 =$this->m4is_a26($m4is_b4068 );
  if($m4is_p1084 == 'hide' ||$m4is_p1084 == 'redirect'){
$m4is_f0174 =false;
 
}
}
}
}  if($m4is_f0174 ){
if(stripos($m4is_g3762->url, '/memberium:' )=== 0 ){
 if(stripos($m4is_g3762->url, '/memberium:logout' )!== false ){
if($m4is_n966 ){
$m4is_r54196 =substr($m4is_g3762->url, 18 );
 if($m4is_r54196 == '' ){
$m4is_r54196 =$m4is_d37;
 
}$m4is_x46058[$m4is_l9671]->url =wp_logout_url($m4is_r54196 );
 
}else{
$m4is_f0174 =false;
 
}
} if(stripos($m4is_g3762->url, '/memberium:loginlogout')!== false ){
if($m4is_n966 ){
 $m4is_x46058[$m4is_l9671]->url =wp_logout_url(site_url('/'));
 
}else{
 if(!empty($m4is_f40385)){
$m4is_x46058[$m4is_l9671]->url =get_permalink($m4is_f40385);
 
}else{
$m4is_x46058[$m4is_l9671]->url =wp_login_url();
 
}
}$m4is_r54196 =substr($m4is_g3762->url, 18 );
 if(empty($m4is_r54196 )){
$m4is_r54196 =site_url('/' );
 
}
} if(stripos($m4is_g3762->url, '/memberium:autologin')!== FALSE){
  $parts =explode('|', $m4is_g3762->url);
 
} $m4is_x46058[$m4is_l9671]->title =do_shortcode($m4is_g3762->title);
 
}
}if(!$m4is_f0174 ){
$m4is_i6429[]=$m4is_g3762->ID;
 unset($m4is_x46058[$m4is_l9671]);
 
}
}$this->in_list =0;
 return $m4is_x46058;
 
} public 
function m4is_z80(array $m4is_x46058 ): array {
if(empty($m4is_x46058 )){
return $m4is_x46058;
 
}$m4is_e66310 =current_user_can('manage_options' );
 $m4is_f087 =$this->m4is_r1546->m4is_x66();
 $m4is_s275 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'logout_page', 0 );
 $m4is_f40385 ??= $this->m4is_r1546->m4is_j498('settings', 'login_url' );
 $m4is_d37 =empty($m4is_s275 )? site_url('/' ): get_permalink($m4is_s275 );
 foreach ($m4is_x46058 as $m4is_l9671 =>$m4is_g3762){
$m4is_x46058[$m4is_l9671]->title =do_shortcode($m4is_g3762->title);
 if(stripos($m4is_g3762->url, '/memberium:logout' )!== false ){
if(is_user_logged_in()){
$m4is_r54196 =substr($m4is_g3762->url, 18 );
 if($m4is_r54196 == '' ){
$m4is_r54196 =$m4is_d37;
 
}$m4is_x46058[$m4is_l9671]->url =wp_logout_url($m4is_r54196 );
 
}else{
unset($m4is_x46058[$m4is_l9671]);
 
}continue;
 
} if(stripos($m4is_g3762->url, '/memberium:loginlogout')!== false ){
if(is_user_logged_in()){
 $m4is_x46058[$m4is_l9671]->url =wp_logout_url(site_url('/'));
 
}else{
 if(!empty($m4is_f40385)){
$m4is_x46058[$m4is_l9671]->url =get_permalink($m4is_f40385);
 
}else{
$m4is_x46058[$m4is_l9671]->url =wp_login_url();
 
}
}$m4is_r54196 =substr($m4is_g3762->url, 18 );
 if(empty($m4is_r54196)){
$m4is_r54196 =site_url('/');
 
}continue;
 
} if(stripos($m4is_g3762->url, '/memberium:autologin')!== FALSE){
  $parts =explode('|', $m4is_g3762->url);
 
}
}return $m4is_x46058;
 
} 
function m4is_b64(){
$m4is_y66291 =['response' =>403, 'code' =>__('Public RSS Feed Unavailable'), 'exit' =>true, ];
 wp_die(__('No feed available, please visit our homepage.'), __('Access Denied'), $m4is_y66291 );
 
} 
function m4is_b5431($m4is_c05328){
$m4is_c05328 =strtolower(trim($m4is_c05328));
 $m4is_j15 =['default', 'excerpt', 'hide', 'redirect', ];
 if($m4is_c05328 == '' ||in_array($m4is_c05328, $m4is_j15)){
$this->m4is_r1546->m4is_l53('prohibited_action_override', $m4is_c05328);
 return true;
 
}else{
return false;
 
}
} 
function m4is_f4206($m4is_c05328){
$m4is_c05328 =strtolower(trim($m4is_c05328));
 $m4is_a19 =['hide', 'show', 'excerpt', 'default' , 'redirect' ];
 if(in_array($m4is_c05328, $m4is_a19 )){
$this->m4is_r1546->m4is_l53('prohibited_action_override', $m4is_c05328 );
 return true;
 
}return false;
 
}
function m4is_r873($m4is_x1486 ): int {
$m4is_x1486 =(int) $m4is_x1486;
 $m4is_r53 =(int) $this->m4is_r1546->m4is_j498('settings', 'excerpt_length' );
 if($m4is_r53 > 0){
$m4is_x1486 =$m4is_r53;
 
}return $m4is_x1486;
 
}    
function m4is_f67290(): bool {
$i2sdk_options =$this->m4is_r1546->get_i2sdk_options();
 return $i2sdk_options['server_verified']== 1;
 
} 
function m4is_o0764(string $m4is_r637, bool $m4is_g3669 =true ): string {
$m4is_r637 =strtolower(trim($m4is_r637 ));
 $m4is_v586 =m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'affiliate', $m4is_r637, '' );
 if($m4is_g3669 ){
$m4is_v586 =htmlspecialchars($m4is_v586 );
 
}return $m4is_v586;
 
} 
function m4is_v921(string $m4is_r637, bool $m4is_g3669 =true){
$m4is_r637 =strtolower(trim($m4is_r637));
 $m4is_v586 =m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', $m4is_r637 )?? '';
 if($m4is_g3669){
$m4is_v586 =htmlspecialchars($m4is_v586);
 
}return $m4is_v586;
 
} 
function m4is_l86($m4is_r32698){
static $m4is_c31;
 $m4is_r32698 =strtolower(trim($m4is_r32698));
 if($m4is_r32698 == ''){
return 0;
 
}if(isset($m4is_c31[$m4is_r32698])){
return $m4is_c31[$m4is_r32698];
 
}$m4is_c9025 ='memberium_emailstat_' .md5($m4is_r32698);
 $m4is_o16 =get_transient($m4is_c9025);
 if($m4is_o16){
return $m4is_o16;
 
}$m4is_c31[$m4is_r32698]=$this->m4is_r1546->m4is_r1476()->optstatus($m4is_r32698);
 set_transient($m4is_c9025, $m4is_c31[$m4is_r32698], 300);
 return $m4is_c31[$m4is_r32698];
 
} public 
function m4is_j217(string $m4is_j45, string $m4is_j9168 ='ec2' ): bool {
$m4is_y4659 =m4is_a01587::m4is_d29();
 $m4is_j45 =m4is_a01587::m4is_y342();
 if(!is_array($m4is_y4659 )){
return false;
 
}foreach ($m4is_y4659 as $m4is_n80626){
if(strtolower($m4is_n80626->service)== $m4is_j9168){
if(m4is_a01587::m4is_q354($m4is_j45, $m4is_n80626->ip_prefix )){
return true;
 
}
}
}return false;
 
}
function m4is_l497(): bool {
if(empty($_SERVER['HTTP_X_BUFFERBOT'])){
return false;
 
}if($this->m4is_j217(m4is_a01587::m4is_y342())){
return true;
 
}return false;
 
} 
function m4is_n49(): bool {
$m4is_o580 =isset($_SERVER['HTTP_USER_AGENT'])? $_SERVER['HTTP_USER_AGENT']: '';
 if(empty($m4is_o580)){
return false;
 
}if(strpos($m4is_o580, 'facebookexternalhit')!== FALSE){
$m4is_j45 =m4is_a01587::m4is_y342();
     $m4is_y4659 =['103.4.96.0/22', '173.252.64.0/18', '173.252.64.0/19', '173.252.70.0/24', '173.252.96.0/19', '204.15.20.0/22', '31.13.24.0/21', '31.13.64.0/18', '31.13.64.0/19', '31.13.64.0/24', '31.13.65.0/24', '31.13.66.0/24', '31.13.67.0/24', '31.13.68.0/24', '31.13.69.0/24', '31.13.70.0/24', '31.13.71.0/24', '31.13.72.0/24', '31.13.73.0/24', '31.13.74.0/24', '31.13.75.0/24', '31.13.76.0/24', '31.13.77.0/24', '31.13.78.0/24', '31.13.79.0/24', '31.13.80.0/24', '31.13.81.0/24', '31.13.82.0/24', '31.13.83.0/24', '31.13.84.0/24', '31.13.85.0/24', '31.13.86.0/24', '31.13.87.0/24', '31.13.88.0/24', '31.13.89.0/24', '31.13.90.0/24', '31.13.91.0/24', '31.13.92.0/24', '31.13.93.0/24', '31.13.94.0/24', '31.13.95.0/24', '31.13.96.0/19', '66.220.144.0/20', '66.220.144.0/21', '66.220.152.0/21', '66.220.159.0/24', '69.171.224.0/19', '69.171.224.0/20', '69.171.239.0/24', '69.171.240.0/20', '69.171.253.0/24', '69.171.255.0/24', '69.63.176.0/20', '69.63.176.0/20', '69.63.176.0/21', '69.63.176.0/24', '69.63.178.0/24', '69.63.184.0/21', '69.63.186.0/24', '74.119.76.0/22', ];
 foreach ($m4is_y4659 as $m4is_n80626){
if(m4is_a01587::m4is_q354($m4is_j45, $m4is_n80626)){
return true;
 
}
}
}return false;
 
}
function m4is_m4521(): bool {
$m4is_o580 =isset($_SERVER['HTTP_USER_AGENT'])? $_SERVER['HTTP_USER_AGENT']: '';
 if(empty($m4is_o580)){
return false;
 
}  if(stristr($m4is_o580, 'applebot')){
if(substr(m4is_a01587::m4is_y342(), 0, 3)== '17.'){
return true;
 
}
}return false;
 
}
function m4is_i7396(): bool {
$m4is_o580 =isset($_SERVER['HTTP_USER_AGENT'])? $_SERVER['HTTP_USER_AGENT']: '';
 if(empty($m4is_o580)){
return false;
 
}return (stristr($m4is_o580, 'google.')!== false);
 
}
function m4is_q06(): bool {
$m4is_o580 =isset($_SERVER['HTTP_USER_AGENT'])? $_SERVER['HTTP_USER_AGENT']: '';
 if(empty($m4is_o580)){
return false;
 
} if(stristr($m4is_o580, "googlebot")){
if(stristr(gethostbyaddr(m4is_a01587::m4is_y342()), "googlebot.com")){
return true;
 
}
}return false;
 
}
function m4is_e4731(): bool {
return !empty(m4is_q82::m4is_d6758($this->m4is_r1546->m4is_x66(), 'memb_user', 'membership_names', '' ));
 
} 
function m4is_z4619($m4is_t9073 ): bool {
if(!m4is_s52::m4is_f27()){
return false;
 
}if($this->is_administrator ){
return true;
;
 
}$m4is_t9073 =(int) $m4is_t9073;
 $m4is_d34986 =m4is_q82::m4is_d6758($this->m4is_r1546->m4is_x66(), 'memb_user', 'membership_level', 0 );
 return ($m4is_d34986 > $m4is_t9073 );
 
} 
function m4is_u70(string $m4is_i32068, int $m4is_f087 =0 ): bool {
if(!m4is_s52::m4is_f27()){
return false;
 
}$m4is_i32068 =strtolower(trim($m4is_i32068 ));
 if(empty($m4is_i32068 )){
return false;
 
}$m4is_f087 =$m4is_f087 ? $m4is_f087 : $this->m4is_r1546->m4is_x66();
 if(!$m4is_f087 ){
return false;
 
}if(user_can($m4is_f087, 'manage_options' )){
return true;
  
}$m4is_m8534 =array_filter(explode(',', strtolower(m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'membership_names', '' ))));
 return in_array($m4is_i32068, $m4is_m8534 );
 
} 
function m4is_v9648($m4is_y85661 ){
if(!m4is_s52::m4is_f27()){
return FALSE;
 
}$m4is_f087 =$this->m4is_r1546->m4is_x66();
 if($m4is_f087 == 0){
return FALSE;
 
}$m4is_y85661 =strtolower(trim($m4is_y85661));
 if($m4is_y85661 == ''){
return FALSE;
 
}$m4is_v586 =get_user_meta($m4is_f087, 'memberium::field::' . $m4is_y85661, TRUE);
 if($m4is_v586 == ''){
$m4is_v586 =get_user_meta($m4is_f087, 'memberium::counter::' . $m4is_y85661, TRUE);
 if($m4is_v586 > ''){
update_user_meta($m4is_f087, 'memberium::field::' . $m4is_y85661, $m4is_v586);
 delete_user_meta($m4is_f087, 'memberium::counter::' . $m4is_y85661);
 
}
}return (int) $m4is_v586;
 
}
function m4is_c5104($m4is_y85661, $m4is_s634, $m4is_f087 =null ){
if(!m4is_s52::m4is_f27()){
return;
 
}$m4is_f087 =$m4is_f087 ?? $this->m4is_r1546->m4is_x66();
 if($m4is_f087 == 0){
return FALSE;
 
}$m4is_y85661 =strtolower(trim($m4is_y85661 ));
 if(empty($m4is_y85661 )){
return FALSE;
 
}update_user_meta($m4is_f087, 'memberium::field::' . $m4is_y85661, $m4is_s634 );
 
} 
function m4is_t364(): array {
if(!m4is_s52::m4is_f27()){
return [];
 
}return array_filter(explode(',', m4is_q82::m4is_d6758($this->m4is_r1546->m4is_x66(), 'memb_user', 'membership_names', '' )));
 
}    
function m4is_p39261(){
$m4is_i46 ='<script type="text/javascript">';
 $m4is_i46 .= 'var ajaxurl = "' . admin_url('admin-ajax.php'). '"';
 $m4is_i46 .= '</script>';
 echo $m4is_i46;
 
}  
function m4is_x196(){
$m4is_a6814 =$this->m4is_r1546->m4is_w45();
 $this->signature =true;
 printf("<meta name='generator' content='Memberium v%s for WordPress' />\n", $m4is_a6814 );
 if(is_singular()){
global $post;
 $m4is_b4068 =$post->ID ?? 0;
  if(!empty($post->ID )){
$m4is_l79562 =get_post_meta($post->ID, '_iswp_custom_code', true );
 $m4is_y642 =['head' =>'', 'css' =>'', 'js' =>'', ];
 $m4is_l79562 =wp_parse_args($m4is_l79562, $m4is_y642 );
 echo (!empty($m4is_l79562['head']))? do_shortcode($m4is_l79562['head']). "\n" : '';
 echo (!empty($m4is_l79562['css']))? "\n<style>\n" . do_shortcode($m4is_l79562['css']). "\n</style>\n" : '';
 echo (!empty($m4is_l79562['js']))? "\n<script>\n" . do_shortcode($m4is_l79562['js']). "\n</script>\n" : '';
 
}
} return true;
 
} 
function m4is_y06435(){
 if(!$this->signature ){
$this->m4is_x196();
 
}$this->m4is_u960();
  if(!empty($this->footer_json )){
$m4is_u51876 =$this->footer_json;
  $m4is_u51876['home_url']=(!isset($m4is_u51876['home_url']))? get_home_url(): $m4is_u51876['home_url'];
 $m4is_u51876['ajax_url']=(!isset($m4is_u51876['ajax_url']))? admin_url('admin-ajax.php'): $m4is_u51876['ajax_url'];
 $m4is_u51876['contact_id']=(!isset($m4is_u51876['contact_id']))? $this->m4is_r1546->m4is_z56(): $m4is_u51876['contact_id'];
 $json_data =json_encode($m4is_u51876, JSON_PRETTY_PRINT );
  echo sprintf('<script>var %s=%s;</script>', 'memberium_data', $json_data);
 
}
}private 
function m4is_u960(): void {
if(empty($this->m4is_x656 )||!is_array($this->m4is_x656 )){
return;
 
}echo "<script>\n\n";
 foreach ($this->m4is_x656 as $m4is_k08 ){
$m4is_k52736 =rawurlencode($m4is_k08['name']);
 $m4is_v586 =rawurlencode($m4is_k08['value']);
 $m4is_d04266 =$m4is_k08['path'];
  echo "   document.cookie = '{$m4is_k52736
}={$m4is_v586
}; ";
  if(!empty($m4is_k08['domain'])){
echo "domain=", rawurlencode($m4is_k08['domain']), "; ";
 
} if(!empty($m4is_k08['path'])){
echo "path=", $m4is_k08['path'], "; ";
 
} if(!empty($m4is_k08['expiration'])){
echo "expires=", gmdate('D, d M Y H:i:s \G\M\T', strtotime($m4is_k08['expiration'])), "; ";
 
} if(!empty($m4is_k08['secure'])){
echo "secure; ";
 
} echo "';\n";
 
}echo "\n</script>\n\n";
 
} 
function m4is_c710($m4is_o580 =null){
 $m4is_o580 =$m4is_o580 ? strtolower($m4is_o580): strtolower($_SERVER['HTTP_USER_AGENT']);
 static $m4is_f598;
  $cache_key =md5($m4is_o580);
  if(empty($m4is_f598[$cache_key])){
 $m4is_e8216 =['os' =>'windows', 'type' =>'desktop', 'user' =>'browser', 'browser' =>'unknown' ];
  $patterns =["/applebot|bufferbot|adsbot|feedfetcher-google|googlebot|msnbot|pingdom\.com|watchmouse|yahooseeker|yahoobot/" =>['os' =>'bot', 'type' =>'bot', 'user' =>'bot'], "/phone|symbian|htc_|htc-|opera mini|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|mobile|pda;|avantgo|eudoraweb|minimo|netfront|brew|teleca|lg;|lge |wap;| wap /" =>['os' =>'misc', 'type' =>'mobile', 'user' =>'browser'], "/iemobile|windows ce/" =>['os' =>'bot', 'type' =>'bot', 'user' =>'bot'], "/palmos/" =>['os' =>'palmos', 'type' =>'mobile', 'user' =>'browser'], "/blackberry/" =>['os' =>'blackberry', 'type' =>'mobile', 'user' =>'browser'], "/iphone|itouch|ipad|ipod/" =>['os' =>'ios', 'type' =>'mobile', 'user' =>'browser'], "/android/" =>['os' =>'android', 'type' =>'mobile', 'user' =>'browser'], "/chrome/" =>['browser' =>'chrome', 'user' =>'browser'], "/firefox/" =>['browser' =>'firefox', 'user' =>'browser'], "/kindle/" =>['os' =>'kindle', 'type' =>'mobile', 'user' =>'browser'], "/mac os/" =>['os' =>'mac', 'type' =>'desktop'], "/msie/" =>['browser' =>'internetexplorer', 'type' =>'desktop'], "/linux/" =>['os' =>'linux'], "/lynx/" =>['browser' =>'lynx', 'user' =>'browser'], "/netscape/" =>['browser' =>'netscape'], "/nintendo/" =>['browser' =>'nintendo', 'os' =>'nintendo', 'type' =>'mobile', 'user' =>'browser'], "/opera|opr/" =>['browser' =>'opera'], "/safari/" =>['browser' =>'safari'], "/silk/" =>['browser' =>'silk', 'os' =>'android', 'type' =>'mobile', 'user' =>'browser'], ];
  foreach ($patterns as $pattern =>$info){
if(preg_match($pattern, $m4is_o580)){
$m4is_e8216 =array_merge($m4is_e8216, $info);
 
}
} $m4is_f598[$cache_key]=$m4is_e8216;
 
} return $m4is_f598[$cache_key];
 
}    
function m4is_o8940($m4is_t09761 ){
global $shortcode_tags;
  $m4is_n9430 =$shortcode_tags;
  remove_all_shortcodes();
  $this->shortcodes_registered =false;
  do_action('memberium/shortcodes/add');
  $m4is_t09761 =do_shortcode($m4is_t09761);
  $shortcode_tags =$m4is_n9430;
  return $m4is_t09761;
 
} public 
function m4is_j64(bool $m4is_f465 =false ){
global $wpdb;
 $m4is_q1046 ='custom_shortcodes';
 $m4is_o14 ='memberium/core';
 $m4is_s82753 =MINUTE_IN_SECONDS * 10;
 $m4is_t265 =false;
 $m4is_r02674 =wp_cache_get($m4is_q1046, $m4is_o14, false, $m4is_t265 );
 if($m4is_f465 === true ||$m4is_t265 === false ){
$m4is_v2613 ="SELECT `post_name` FROM `{$wpdb->posts
}` WHERE `post_type` = 'memb_shortcodeblocks' and `post_status` = 'publish' ";
 $m4is_m615 =$wpdb->get_col($m4is_v2613 );
 $m4is_m615 =is_array($m4is_m615 )? array_filter($m4is_m615 ): [];
 $m4is_r02674 =[];
 foreach ($m4is_m615 as $m4is_j09 ){
$m4is_r02674[]='membc_' . $m4is_j09;
 
}wp_cache_set($m4is_q1046, $m4is_r02674, $m4is_o14, $m4is_s82753 );
 
} return $m4is_r02674;
 
} private 
function m4is_s59124(){
global $wpdb;
 static $m4is_l9321;
 if(!is_array($m4is_l9321)){
$m4is_z2136 =$this->m4is_r1546->m4is_l9657();
 $m4is_l9321 =[];
 $m4is_l9321['custom']=$this->m4is_j64();
  $m4is_q62 ='m4is_r5369';
 $m4is_o1845 ='m4is_j06';
 $m4is_z548 ='m4is_t73';
 $m4is_k31628 ='m4is_z7241';
 $m4is_e916 ='m4is_g95873';
 $m4is_o450 ='m4is_d56423';
 $m4is_d1268 ='m4is_h30';
 $m4is_v63 ='m4is_b789';
 $m4is_v537 ='m4is_m08356';
 $m4is_i508 ='m4is_j71492';
 $m4is_z0651 ='m4is_q96705';
 $m4is_u4238 ='m4is_u45762';
 $m4is_l9321['nested']=['memb_can_view_post' =>[$m4is_z2136, [$m4is_q62, 'm4is_o643']], 'memb_compare' =>[$m4is_z2136, [$m4is_q62, 'm4is_l96']], 'memb_has_all_roles' =>[$m4is_z2136, [$m4is_q62, 'm4is_g4671']], 'memb_has_all_tags' =>[$m4is_z2136, [$m4is_q62, 'm4is_q8415']], 'memb_has_any_role' =>[$m4is_z2136, [$m4is_q62, 'm4is_c3194']], 'memb_has_any_tag' =>[$m4is_z2136, [$m4is_q62, 'm4is_e63401']], 'memb_has_any_token' =>[$m4is_z2136, [$m4is_q62, 'm4is_w28453']], 'memb_has_membership' =>[$m4is_z2136, [$m4is_q62, 'm4is_u23']], 'memb_has_payf' =>[$m4is_z2136, [$m4is_q62, 'm4is_m02']], 'memb_hide_from' =>[$m4is_z2136, [$m4is_q62, 'm4is_x976']], 'memb_if_cookie' =>[$m4is_z2136, [$m4is_q62, 'm4is_u665']], 'memb_if_get' =>[$m4is_z2136, [$m4is_q62, 'm4is_e23146']], 'memb_if_post' =>[$m4is_z2136, [$m4is_q62, 'm4is_i078']], 'memb_if_request' =>[$m4is_z2136, [$m4is_q62, 'm4is_d68']], 'memb_if_user_counter' =>[$m4is_z2136, [$m4is_q62, 'm4is_w647']], 'memb_if' =>[$m4is_z2136, [$m4is_q62, 'm4is_l96']], 'memb_is_after_tag_date' =>[$m4is_z2136, [$m4is_q62, 'm4is_t1669']], 'memb_show_after' =>[$m4is_z2136, [$m4is_q62, 'm4is_a756']], 'memb_show_between' =>[$m4is_z2136, [$m4is_q62, 'm4is_m73']], 'memb_show_until' =>[$m4is_z2136, [$m4is_q62, 'm4is_b978']], 'memb_is_browser' =>[5, [$m4is_q62, 'm4is_t206']], 'memb_has_affiliate' =>[1, [$m4is_o1845, 'm4is_n7631']], 'memb_is_1x_optin' =>[3, [$m4is_e916, 'm4is_p6621']], 'memb_is_2x_optin' =>[3, [$m4is_e916, 'm4is_p6621']], 'memb_is_no_optin' =>[3, [$m4is_e916, 'm4is_p6621']], ];
 $m4is_l9321['standard']=['memb_expires' =>[$m4is_q62, 'm4is_h12'], 'memb_fade' =>[$m4is_q62, 'm4is_i95364'], 'memb_hide' =>[$m4is_q62, 'm4is_x62485'], 'memb_hidefrom_feed' =>[$m4is_q62, 'm4is_d69835'], 'memb_hidefrom' =>[$m4is_q62, 'm4is_x976'], 'memb_if_lang' =>[$m4is_q62, 'm4is_q675'], 'memb_is_admin' =>[$m4is_q62, 'm4is_l74836'], 'memb_is_autologin' =>[$m4is_q62, 'm4is_y89'], 'memb_is_excerpt_only' =>[$m4is_q62, 'm4is_z66789'], 'memb_is_first_login' =>[$m4is_q62, 'm4is_k75216'], 'memb_is_logged_in' =>[$m4is_q62, 'm4is_h821'], 'memb_is_not_admin' =>[$m4is_q62, 'm4is_l74836'], 'memb_is_post_type' =>[$m4is_q62, 'm4is_z97'], 'memb_is_single' =>[$m4is_q62, 'm4is_j87'], 'memb_is_trackable_link' =>[$m4is_q62, 'm4is_n64829'], 'memb_switch' =>[$m4is_q62, 'm4is_d24196'], 'memb_affiliate_login' =>[$m4is_o1845, 'm4is_k257'], 'memb_affiliate_running_totals' =>[$m4is_o1845, 'm4is_q5489'], 'memb_affiliate' =>[$m4is_o1845, 'm4is_u80'], 'memb_detect_affiliate' =>[$m4is_o1845, 'm4is_w9850'], 'memb_is_affiliate' =>[$m4is_o1845, 'm4is_w91'], 'memb_referral_contact' =>[$m4is_o1845, 'm4is_h76628'], 'memb_s3_link' =>[$m4is_z548, 'm4is_i16236'], 'memb_secure_audio' =>[$m4is_z548, 'm4is_w1932'], 'memb_secure_video' =>[$m4is_z548, 'm4is_v09315'],  'memb_is_applebot' =>[$m4is_k31628, 'm4is_g71'], 'memb_is_chrome' =>[$m4is_k31628, 'm4is_a72031'], 'memb_is_facebook_crawler' =>[$m4is_k31628, 'm4is_q664'], 'memb_is_feed' =>[$m4is_k31628, 'm4is_s10683'], 'memb_is_gecko' =>[$m4is_k31628, 'm4is_h24150'], 'memb_is_google1stclick' =>[$m4is_k31628, 'm4is_i54'], 'memb_is_googlebot' =>[$m4is_k31628, 'm4is_n865'], 'memb_is_ie' =>[$m4is_k31628, 'm4is_n74601'], 'memb_is_ipad' =>[$m4is_k31628, 'm4is_f1960'], 'memb_is_iphone' =>[$m4is_k31628, 'm4is_m7410'], 'memb_is_lynx' =>[$m4is_k31628, 'm4is_z03648'], 'memb_is_macie' =>[$m4is_k31628, 'm4is_f62'], 'memb_is_mobile' =>[$m4is_k31628, 'm4is_u1279'], 'memb_is_ns4' =>[$m4is_k31628, 'm4is_i04269'], 'memb_is_opera' =>[$m4is_k31628, 'm4is_d60138'], 'memb_is_safari' =>[$m4is_k31628, 'm4is_i61207'], 'memb_is_ssl' =>[$m4is_k31628, 'm4is_v62'], 'memb_is_winie' =>[$m4is_k31628, 'm4is_w506'], 'memb_useragent_match' =>[$m4is_k31628, 'm4is_k46'],  'memb_achieve_goal' =>[$m4is_e916, 'm4is_i52671'], 'memb_action_link' =>[$m4is_e916, 'm4is_x867'], 'memb_actionset_button' =>[$m4is_e916, 'm4is_w3614'], 'memb_add_fus' =>[$m4is_e916, 'm4is_q9354'], 'memb_add_tag' =>[$m4is_e916, 'm4is_p453'], 'memb_appname' =>[$m4is_e916, 'm4is_o6159'], 'memb_appointments' =>[$m4is_e916, 'm4is_h06274'], 'memb_count_my_tags' =>[$m4is_e916, 'm4is_z16764'], 'memb_count_tags' =>[$m4is_e916, 'm4is_s7845'], 'memb_infusion_id' =>[$m4is_e916, 'm4is_n1986'], 'memb_link_contacts' =>[$m4is_e916, 'm4is_k763'], 'memb_list_linked_contacts' =>[$m4is_e916, 'm4is_v16967'], 'memb_pause_fus' =>[$m4is_e916, 'm4is_v357'], 'memb_remove_fus' =>[$m4is_e916, 'm4is_g679'], 'memb_remove_tag' =>[$m4is_e916, 'm4is_h96'], 'memb_run_actionset' =>[$m4is_e916, 'm4is_l409'], 'memb_set_tag' =>[$m4is_e916, 'm4is_s0695'], 'memb_sync_contact' =>[$m4is_e916, 'm4is_u32'], 'memb_tag_date' =>[$m4is_e916, 'm4is_u397'], 'memb_tag_name' =>[$m4is_e916, 'm4is_x2976'], 'memb_unlink_contacts' =>[$m4is_e916, 'm4is_t2863'], 'memb_is_app_connected' =>[$m4is_e916, 'm4is_b450'], 'memb_is_appconnected' =>[$m4is_e916, 'm4is_b450'], 'memb_debug' =>[$m4is_o450, 'm4is_q35'], 'memb_list_shortcodes' =>[$m4is_o450, 'm4is_e62'], 'memb_performance' =>[$m4is_o450, 'm4is_y3061'],  'memb_1click_order_product' =>[$m4is_d1268, 'm4is_k8723'], 'memb_add_creditcard' =>[$m4is_d1268, 'm4is_j43185'], 'memb_add_paymentmethod' =>[$m4is_d1268, 'm4is_r927'], 'memb_client_login' =>[$m4is_d1268, 'm4is_c36'], 'memb_creditcard_days_left' =>[$m4is_d1268, 'm4is_r60796'], 'memb_creditcard_expires' =>[$m4is_d1268, 'm4is_b05'], 'memb_creditcard' =>[$m4is_d1268, 'm4is_o93256'], 'memb_has_creditcard' =>[$m4is_d1268, 'm4is_i926'], 'memb_has_product' =>[$m4is_d1268, 'm4is_j467'], 'memb_has_subscription' =>[$m4is_d1268, 'm4is_j614'], 'memb_list_creditcards' =>[$m4is_d1268, 'm4is_x37'], 'memb_list_invoices' =>[$m4is_d1268, 'm4is_o0569'], 'memb_list_subscriptions' =>[$m4is_d1268, 'm4is_v1905'], 'memb_one_click_sale' =>[$m4is_d1268, 'm4is_p72'], 'memb_order_info' =>[$m4is_d1268, 'm4is_u6925'], 'memb_order_product' =>[$m4is_d1268, 'm4is_n67'], 'memb_order_subscription' =>[$m4is_d1268, 'm4is_n6290'], 'memb_orderform' =>[$m4is_d1268, 'm4is_t38'], 'memb_place_order' =>[$m4is_d1268, 'm4is_b658'], 'memb_product' =>[$m4is_d1268, 'm4is_p327'], 'memb_show_receipt' =>[$m4is_d1268, 'm4is_r26'], 'memb_subscriptionplan' =>[$m4is_d1268, 'm4is_j94208'], 'memb_total_lifetime_value' =>[$m4is_d1268, 'm4is_x61024'], 'memb_filebox_link' =>[$m4is_v63, 'm4is_w746'], 'memb_filebox_url' =>[$m4is_v63, 'm4is_d04632'], 'memb_list_filebox' =>[$m4is_v63, 'm4is_j4578'], 'memb_upload_filebox' =>[$m4is_v63, 'm4is_c796'], 'memb_upload_message_filebox' =>[$m4is_v63, 'm4is_v65'], 'memb_capture' =>[$m4is_i508, 'm4is_k4508'], 'memb_country_dropdown' =>[$m4is_i508, 'm4is_c62387'], 'memb_customerhub_autologin' =>[$m4is_i508, 'm4is_o37'], 'memb_days_difference' =>[$m4is_i508, 'm4is_r26038'], 'memb_do_shortcode' =>[$m4is_i508, 'm4is_c0267'], 'memb_e' =>[$m4is_i508, 'm4is_n819'], 'memb_echo' =>[$m4is_i508, 'm4is_l2946'], 'memb_forloop' =>[$m4is_i508, 'm4is_f3549'], 'memb_geoip' =>[$m4is_i508, 'm4is_m68691'], 'memb_http_post' =>[$m4is_i508, 'm4is_h93'], 'memb_js_encode' =>[$m4is_i508, 'm4is_r51978'], 'memb_language_dropdown' =>[$m4is_i508, 'm4is_d076'], 'memb_license_status' =>[$m4is_i508, 'm4is_f76'], 'memb_php' =>[$m4is_i508, 'm4is_s664'], 'memb_plusthis' =>[$m4is_i508, 'm4is_h93'], 'memb_qrcode' =>[$m4is_i508, 'm4is_d218'], 'memb_quotd' =>[$m4is_i508, 'm4is_d28716'], 'memb_show_messages' =>[$m4is_i508, 'm4is_t4932'], 'memb_timezone_dropdown' =>[$m4is_i508, 'm4is_b0163'], 'memb_version' =>[$m4is_i508, 'm4is_g82'], 'memb_coursegrid' =>['m4is_l032', 'm4is_d674'], 'memb_change_email' =>[$m4is_z0651, 'm4is_k80462'], 'memb_change_password' =>[$m4is_z0651, 'm4is_t02'], 'memb_contact' =>[$m4is_z0651, 'm4is_e20845'], 'memb_feedurl' =>[$m4is_z0651, 'm4is_f66081'], 'memb_generate_password' =>[$m4is_z0651, 'm4is_l6860'], 'memb_getfield' =>[$m4is_z0651, 'm4is_y2487'], 'memb_gravatar' =>[$m4is_z0651, 'm4is_n09'], 'memb_json_session' =>[$m4is_z0651, 'm4is_k6589'], 'memb_list_logins' =>[$m4is_z0651, 'm4is_c59043'], 'memb_list_tags' =>[$m4is_z0651, 'm4is_f25'], 'memb_lost_password' =>[$m4is_z0651, 'm4is_p9656'], 'memb_member_listing' =>[$m4is_z0651, 'm4is_q83467'], 'memb_optin_status' =>[$m4is_z0651, 'm4is_p4863'], 'memb_owner' =>[$m4is_z0651, 'm4is_p78'], 'memb_persona' =>[$m4is_z0651, 'm4is_g02'], 'memb_refresh_persona' =>[$m4is_z0651, 'm4is_e70'], 'memb_registration_form' =>[$m4is_z0651, 'm4is_f9305'], 'memb_reset_feedurl' =>[$m4is_z0651, 'm4is_z671'], 'memb_reset_password' =>[$m4is_z0651, 'm4is_p01'], 'memb_send_password' =>[$m4is_z0651, 'm4is_y794'], 'memb_set_token' =>[$m4is_z0651, 'm4is_x248'], 'memb_showfield' =>[$m4is_z0651, 'm4is_y2487'], 'memb_update_contact' =>[$m4is_z0651, 'm4is_b30647'], 'memb_update_form' =>[$m4is_z0651, 'm4is_p09846'], 'memb_user_counter' =>[$m4is_z0651, 'm4is_r06'], 'memb_user_levels' =>[$m4is_z0651, 'm4is_g3142'], 'memb_wp_user' =>[$m4is_z0651, 'm4is_x7438'], 'memb_cookie' =>[$m4is_u4238, 'm4is_y1465'], 'memb_date' =>[$m4is_u4238, 'm4is_i857'], 'memb_get_permalink' =>[$m4is_u4238, 'm4is_u84'], 'memb_get' =>[$m4is_u4238, 'm4is_y1465'], 'memb_include_page' =>[$m4is_u4238, 'm4is_h3651'], 'memb_include_partial' =>[$m4is_u4238, 'm4is_h3651'], 'memb_include_post' =>[$m4is_u4238, 'm4is_h3651'], 'memb_login_url' =>[$m4is_u4238, 'm4is_p6891'], 'memb_loginform' =>[$m4is_u4238, 'm4is_h19'], 'memb_loginlogout' =>[$m4is_u4238, 'm4is_j85296'], 'memb_logout_link' =>[$m4is_u4238, 'm4is_b315'], 'memb_logout' =>[$m4is_u4238, 'm4is_m89'], 'memb_php_include_once' =>[$m4is_u4238, 'm4is_q71065'], 'memb_php_include' =>[$m4is_u4238, 'm4is_q71065'], 'memb_post_meta' =>[$m4is_u4238, 'm4is_d64237'], 'memb_post' =>[$m4is_u4238, 'm4is_y1465'], 'memb_raw' =>[$m4is_u4238, 'm4is_h90372'], 'memb_redirect' =>[$m4is_u4238, 'm4is_f696'], 'memb_request' =>[$m4is_u4238, 'm4is_y1465'], 'memb_server' =>[$m4is_u4238, 'm4is_y1465'], 'memb_session' =>[$m4is_u4238, 'm4is_y1465'], 'memb_set_cookie' =>[$m4is_u4238, 'm4is_h20'], 'memb_default_excerpt' =>[$m4is_u4238, 'm4is_e3465'], 'memb_registration_date' =>[$m4is_u4238, 'm4is_x56'], 'memb_registration_url' =>[$m4is_u4238, 'm4is_e2068'], 'memb_remote_post_get' =>[$m4is_u4238, 'm4is_o6103'], 'memb_system_link' =>[$m4is_u4238, 'm4is_w06'], 'memb_set_prohibited_action' =>[$m4is_u4238, 'm4is_t6407'],    'memb_award_achievement' =>[$m4is_v537, 'm4is_s348'], 'memb_revoke_achievement' =>[$m4is_v537, 'm4is_z019'], 'memb_list_achievements' =>[$m4is_v537, 'm4is_s43708'], ];
 
}return $m4is_l9321;
 
} 
function m4is_s2469($m4is_t09761 ='' ){
 if(!$this->shortcodes_registered){
$m4is_l9321 =$this->m4is_s59124();
  if(isset($m4is_l9321['custom'])&&is_array($m4is_l9321['custom'])){
foreach($m4is_l9321['custom']as $m4is_p786){
 add_shortcode($m4is_p786, [$this, 'm4is_j57803']);
 
}
} foreach($m4is_l9321['standard']as $m4is_p786 =>$m4is_j86631){
 $m4is_s7349 =is_array($m4is_j86631)? $m4is_j86631 : [$this, $m4is_j86631];
  add_shortcode($m4is_p786, $m4is_s7349);
 
} foreach($m4is_l9321['nested']as $m4is_p786 =>$m4is_j86631){
 $m4is_s7349 =is_array($m4is_j86631[1])? $m4is_j86631[1]: [$this, $m4is_j86631[1]];
  add_shortcode($m4is_p786, $m4is_s7349);
  for ($m4is_b3785 =1;
 $m4is_b3785 < (int) $m4is_j86631[0];
 $m4is_b3785++){
add_shortcode("{$m4is_p786
}{$m4is_b3785
}", $m4is_s7349);
 
}
} $this->shortcodes_registered =true;
 
} return $m4is_t09761;
 
} 
function m4is_w796($m4is_t09761 ='' ){
 if($this->shortcodes_registered){
$m4is_l9321 =$this->m4is_s59124();
  $shortcode_types =['custom', 'standard', 'nested'];
  foreach ($shortcode_types as $type){
 if(isset($m4is_l9321[$type])&&is_array($m4is_l9321[$type])){
 foreach($m4is_l9321[$type]as $m4is_p786 =>$m4is_j86631){
remove_shortcode($m4is_p786);
  if($type === 'nested'){
for ($m4is_b3785 =1;
 $m4is_b3785 < (int) $m4is_j86631[0];
 $m4is_b3785++){
remove_shortcode($m4is_p786 . $m4is_b3785);
 
}
}
}
}
} do_action('memberium/shortcodes/remove');
  $this->shortcodes_registered =false;
 
} return $m4is_t09761;
 
}
function m4is_b79625(){
wp_register_style('memb_coursegrid_css', plugin_dir_url(MEMBERIUM_HOME). "css/memb_coursegrid.css", false, $this->m4is_r1546->m4is_w45(), 'all');
 
}    
function m4is_z29675($m4is_t09761 ='', $m4is_v8646 =''){
if(empty($m4is_t09761)){
return;
 
}$m4is_o498 ='';
 $m4is_m86264 =false;
 $m4is_v05 =false;
 $m4is_c8657 ='/(\[case.*\])|(\[else\])/U';
 $m4is_s496 =preg_split($m4is_c8657, $m4is_t09761, 0, PREG_SPLIT_DELIM_CAPTURE);
 foreach ($m4is_s496 as $m4is_a43256){
$m4is_a43256 =trim($m4is_a43256);
 if(empty($m4is_a43256)){
continue;
 
}if(substr($m4is_a43256, 0, 5)== '[case' ||substr($m4is_a43256, 0, 5)== '[else'){
$m4is_v05 =false;
 $m4is_u897 =shortcode_parse_atts(substr($m4is_a43256, 1, -1));
 if(!empty($m4is_u897[0])&&strtolower($m4is_u897[0])== 'case'){
if(!empty($m4is_u897['any_tag'])){
$m4is_v05 =$this->m4is_r1546->m4is_m2480(trim($m4is_u897['any_tag']));
 if($m4is_v05){
$m4is_m86264 =true;
 
}
}if(!empty($m4is_u897['all_tags'])){
$m4is_v05 =$this->m4is_r1546->m4is_x13466(trim($m4is_u897['all_tags']));
 if($m4is_v05){
$m4is_m86264 =true;
 
}
}if(!empty($m4is_u897['not_any_tagid'])){
$m4is_v05 =!$this->m4is_r1546->m4is_m2480(trim($m4is_u897['not_any_tagid']));
 if($m4is_v05){
$m4is_m86264 =true;
 
}
}if(!empty($m4is_u897['not_all_tagids'])){
$m4is_v05 =!$this->m4is_r1546->m4is_x13466(trim($m4is_u897['not_all_tagids']));
 if($m4is_v05){
$m4is_m86264 =true;
 
}
}
}if(!empty($m4is_u897[0])&&strtolower($m4is_u897[0])== 'else'){
if(!$m4is_m86264){
$m4is_v05 =true;
 
}
}
}else{
if($m4is_v05){
$m4is_o498 .= $m4is_a43256;
 
}$m4is_v05 =false;
 
}
}return do_shortcode($m4is_o498);
 
}    
function m4is_j57803($m4is_l62046, $m4is_t09761 =null, $tag =''){
if(!m4is_s52::m4is_w74()){
return;
 
}m4is_j586::m4is_x7134();
 static $m4is_v3458 =[];
 static $m4is_w9678 =[];
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'htmlattr' =>'', 'txtfmt' =>'', ];
 if(is_array($m4is_l62046)){
foreach($m4is_l62046 as $m4is_o015 =>$m4is_k72){
if(!isset($m4is_y642[$m4is_o015])){
$m4is_y642[$m4is_o015]=$m4is_k72;
 
}
}
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
  $m4is_w742 =substr($tag, 6);
  if(!isset($m4is_w9678[$m4is_w742])){
$m4is_w9678[$m4is_w742]=0;
 
}$m4is_w9678[$m4is_w742]++;
 if($m4is_w9678[$m4is_w742]> 10){
wp_die('Custom Shortcode Recursion Limit Exceeded.');
 
}if(!isset($m4is_v3458[$m4is_w742])){
$m4is_y66291 =['name' =>$m4is_w742, 'post_type' =>'memb_shortcodeblocks' ];
 $m4is_r02674 =get_posts($m4is_y66291);
 $m4is_v3458[$m4is_w742]=$m4is_r02674[0]->post_content;
 
}$m4is_o498 =$m4is_v3458[$m4is_w742];
 $m4is_l62046 =m4is_f61::m4is_a478($m4is_l62046);
   if(is_array($m4is_l62046)){
foreach ($m4is_l62046 as $m4is_l9671=>$m4is_v586){
$m4is_l9671 =strtolower($m4is_l9671);
 $m4is_v8646 ='{{atts:' . $m4is_l9671 . '}}';
 $m4is_o498 =str_replace($m4is_v8646, $m4is_v586, $m4is_o498);
 $m4is_v8646 ='{{atts|' . $m4is_l9671 . '}}';
 $m4is_o498 =str_replace($m4is_v8646, $m4is_v586, $m4is_o498);
 
}
}while (stripos($m4is_o498, '{{atts')!== false){
$m4is_o498 =preg_replace_callback('|({{atts\:(.*)}})|U', function($m4is_a157){
return '';
 
}, $m4is_o498);
 $m4is_o498 =preg_replace_callback('|({{atts\|(.*)}})|U', function($m4is_a157){
return '';
 
}, $m4is_o498);
 
}$m4is_t09761 =$m4is_o498;
 unset($m4is_o498);
  global $wp_embed;
 $m4is_t09761 =do_shortcode($wp_embed->run_shortcode($m4is_t09761 ));
 if(!empty($m4is_l62046['txtfmt'])){
$m4is_t09761 =m4is_f61::m4is_n05($m4is_t09761, $m4is_l62046['txtfmt']);
 
}$m4is_t09761 =$m4is_l62046['before']. $m4is_t09761 . $m4is_l62046['after'];
 if(!empty($m4is_l62046['capture'])){
$m4is_t09761 =m4is_f61::m4is_f6513($m4is_t09761, $m4is_l62046['capture']);
 
}if(!empty($m4is_l62046['htmlattr'])){
$m4is_t09761 =$m4is_l62046['htmlattr']. '="' . $m4is_t09761 . '"';
 
}$m4is_w9678[$m4is_w742]--;
 return $m4is_t09761;
 
}   
function m4is_x79053(){
m4is_j586::m4is_x7134();
 $m4is_c3749 =isset($_GET['debug'])? TRUE : FALSE;
 if($m4is_c3749)echo __LINE__, " - Debug Mode Enabled\n";
 if($m4is_c3749)echo __LINE__, " - POST: ", print_r($_POST, true), "\n";
 $m4is_k52736 =empty($_GET['name'])? '' : trim($_GET['name']);
 $m4is_a873 =time();
 $this->m4is_r1546->m4is_u3540($m4is_k52736);
 echo 'Operation Completed';
 return;
 
} 
function m4is_g983(){
m4is_j586::m4is_x7134();
 global $wpdb;
 $m4is_w23965 =[];
 $m4is_o9166 =[];
 $m4is_r9613 =$this->m4is_r1546->m4is_i76('appname');
 $m4is_o076 =date('Y-m-d h:i:s');
 $m4is_h21895 =(int) $_POST['Id'];
 $m4is_c3749 =isset($_GET['debug'])? true : false;
 $m4is_m7912 =empty($_GET['channel'])? 'm' : preg_replace("/[^[:alnum:][:space:]]/u", '', $_GET['channel']);
;
 $m4is_w66 =isset($_GET['cat_id'])&&(int) $_GET['cat_id']> 0 ? (int) $_GET['cat_id']: 0;
 $m4is_c7260 =isset($_GET['tagcount'])&&(int) $_GET['tagcount']> 0 ? (int) $_GET['tagcount']: 6;
 $m4is_j29 =isset($_GET['date_format'])&&$_GET['date_format']> '' ? $_GET['date_format']: 'Ym';
 $m4is_x436 =empty($_GET['interval'])? '' : strtolower(trim($_GET['interval']));
 $m4is_c763 =['YM', 'Ym', 'M', 'm', 'F', 'n'];
 if($m4is_x436 == ''){
if($m4is_j29 == 'Yz'){
$m4is_x436 ='days';
 
}elseif($m4is_j29 == 'YW'){
$m4is_x436 ='weeks';
 
}elseif(in_array($m4is_j29, $m4is_c763)){
$m4is_x436 ='months';
 
}elseif($m4is_j29 == 'Y'){
$m4is_x436 ='years';
 
}
}if(empty($m4is_x436)){
if($m4is_c3749)echo __LINE__, "Premature End - Empty Interval\n";
 exit;
 
}  if($m4is_c3749){
echo __LINE__, " - Channel: ", print_r($m4is_m7912, true), "\n";
 echo __LINE__, " - Category ID: ", print_r($m4is_w66, true), "\n";
 echo __LINE__, " - Tag Count: ", print_r($m4is_c7260, true), "\n";
 echo __LINE__, " - Date Format: ", print_r($m4is_j29, true), "\n";
 echo __LINE__, " - Interval: ", print_r($m4is_x436, true), "\n";
 
} for ($m4is_b3785 =0;
 $m4is_b3785 < $m4is_c7260;
 $m4is_b3785++){
$m4is_w23965[$m4is_b3785]=$m4is_m7912 . date($m4is_j29, strtotime('now + ' . $m4is_b3785 . ' ' . $m4is_x436));
 $m4is_d913 =m4is_k865::m4is_y6189($m4is_w23965[$m4is_b3785]);
 if(!$m4is_d913){
if($m4is_c3749)echo __LINE__, " - Missing Tag: ", $m4is_w23965[$m4is_b3785], "\n";
 $m4is_o9166[$m4is_b3785]=$m4is_b3785;
 
}else{
if($m4is_c3749)echo __LINE__, " - Found Existing Tag: ", $m4is_w23965[$m4is_b3785], "\n";
 
}
}if(is_array($m4is_o9166)&&!empty($m4is_o9166)){
m4is_k865::m4is_l26();
 foreach ($m4is_o9166 as $m4is_b3785){
$m4is_w23965[$m4is_b3785]=$m4is_m7912 . date($m4is_j29, strtotime('now + ' . $m4is_b3785 . ' ' . $m4is_x436));
 $m4is_d913 =m4is_k865::m4is_y6189($m4is_w23965[$m4is_b3785]);
 if($m4is_c3749)echo __LINE__, " - Found Tag: ", $m4is_w23965[$m4is_b3785], "\n";
 if($m4is_d913){
if($m4is_c3749)echo __LINE__, " - Found Missing Tag: ", $m4is_w23965[$m4is_b3785], "\n";
 unset($m4is_o9166[$m4is_b3785]);
 
}
}if($m4is_c3749)echo __LINE__, " - Still Missing Tags: ", print_r($m4is_o9166, true), "\n";
 foreach ($m4is_o9166 as $m4is_b3785){
$m4is_d913 =m4is_k865::m4is_f348($m4is_w23965[$m4is_b3785], (int) $m4is_w66, 'Magazine Issue Tag Auto-created by Memberium on ' . $m4is_o076);
 if($m4is_c3749)echo __LINE__, " - Created Tag : ", $m4is_w23965[$m4is_b3785], "\n";
 
}
}if($m4is_c3749)echo __LINE__, " - Setting Tag: ", $m4is_w23965[0], "\n";
 $m4is_d913 =m4is_k865::m4is_y6189($m4is_w23965[0]);
 $this->m4is_r1546->m4is_k98($m4is_d913, $m4is_h21895 );
 $this->m4is_r1546->m4is_s965('send_http_post');
 exit;
 
}
function m4is_s6670(){
m4is_j586::m4is_x7134();
 $m4is_c3749 =isset($_GET['debug'])? TRUE : FALSE;
 if($m4is_c3749)echo __LINE__, " - Debug Mode Enabled\n";
 if($m4is_c3749)echo __LINE__, " - POST: ", print_r($_POST, true), "\n";
 $this->m4is_r1546->m4is_v1694($_POST);
 $this->m4is_r1546->m4is_s965('send_http_post');
 echo __LINE__, " - Saved Contact Record To Local Cache\n";
 echo 'Operation Completed';
 return;
 
}
function m4is_g06(){
m4is_j586::m4is_x7134();
 if(!empty($_POST['Id'])){
$m4is_h21895 =(int) $_POST['Id'];
 
}if(!empty($_POST['contactId'])){
$m4is_h21895 =(int) $_POST['contactId'];
 
}$m4is_c3749 =isset($_GET['debug'])? true : false;
 $m4is_f2937 =isset($_GET['tagids'])&&$_GET['tagids']> '' ? array_unique(explode(',', $_GET['tagids'])): [];
 $m4is_q26407 =(boolean) strtolower($_GET['sync'])<> 'no';
 if($m4is_c3749){
echo __LINE__, " - Debug Mode Enabled\n";
 echo __LINE__, " - _POST = ", print_r($_POST, true), "\n";
 echo __LINE__, " - Tag ID's = ", print_r($m4is_f2937, true), "\n";
 echo __LINE__, " - Contact Id = ", $m4is_h21895, "\n";
 
}if($m4is_h21895 > 0){
$m4is_i935 =m4is_p40::m4is_p67($m4is_h21895, false);
 if($m4is_c3749)echo __LINE__, " - Contact Tags = ", $m4is_i935['Groups'], "\n";
 if(is_array($m4is_i935)&&(isset($m4is_i935['Groups']))){
$m4is_t87 =array_unique(explode(',', $m4is_i935['Groups']));
 if(is_array($m4is_f2937)&&!empty($m4is_f2937)){
foreach($m4is_f2937 as $m4is_t69863){
$m4is_t69863 =(int) $m4is_t69863;
 if($m4is_t69863 > 0){
$m4is_t87[]=$m4is_t69863;
 
}if($m4is_t69863 < 0){
$m4is_t69863 =abs($m4is_t69863);
 if(($m4is_l9671 =array_search($m4is_t69863, $m4is_t87))!== false){
unset($m4is_t87[$m4is_l9671]);
 
}
}
}$m4is_t87 =array_unique($m4is_t87);
 sort($m4is_t87);
 $m4is_i935['Groups']=implode(',', $m4is_t87);
 $m4is_i935['!LastUpdated']=time();
 if($m4is_c3749){
echo __LINE__, " - Final Groups = ", implode(',', $m4is_t87), "\n";
 echo __LINE__, " - Final Contact = ", print_r($m4is_i935, true), "\n";
 
}$this->m4is_r1546->m4is_v1694($m4is_i935);
 
}else{
if($m4is_c3749)echo __LINE__, " - No Tag ID's passed\n";
 
}
}else{
if($m4is_c3749)echo __LINE__, " - Contact ID ", $m4is_h21895, " not found.\n";
 
}
}
}public 
function m4is_u40532(){
$m4is_c3749 =isset($_GET['debug'])? TRUE : FALSE;
 $m4is_f087 =$this->m4is_r1546->m4is_t9352($_REQUEST['Email']);
 if($m4is_c3749)echo __LINE__, " - Debug Mode Enabled\n";
 if($m4is_f087 > 0 ){
update_user_meta($m4is_f087, 'memberium_optout', 0 );
 if($m4is_c3749)echo __LINE__, " - User ID ", $m4is_f087, " opted in\n";
 
}else{
if($m4is_c3749)echo __LINE__, " - User ID ", $m4is_f087, " not found\n";
 
}
}
function m4is_c86(){
m4is_j586::m4is_x7134();
 $m4is_c3749 =isset($_GET['debug'])? TRUE : FALSE;
 $m4is_f087 =$this->m4is_r1546->m4is_t9352($_REQUEST['Email']);
 if($m4is_c3749)echo __LINE__, " - Debug Mode Enabled\n";
 if($m4is_f087 > 0){
update_user_meta($m4is_f087, 'memberium_optout', 1);
 if($m4is_c3749)echo __LINE__, " - User ID ", $m4is_f087, " opted out\n";
 
}else{
if($m4is_c3749)echo __LINE__, " - User ID ", $m4is_f087, " not found\n";
 
}
}
function m4is_b5614(){
m4is_j586::m4is_x7134();
 global $wpdb;
 $m4is_c3749 =isset($_GET['debug'])? TRUE : FALSE;
 if($m4is_c3749)echo __LINE__, " - Debug Mode Enabled\n";
 $m4is_p4935 =$this->m4is_r1546->m4is_j498('ga_customvars', 'username_field');
 $m4is_y193 =$_POST[$m4is_p4935];
 $m4is_l17096 =get_user_by('email', $m4is_y193);
 $m4is_y85661 =$_GET['countername'];
 $m4is_c05328 =strtolower($_GET['action']);
 $m4is_v586 =$_GET['value'];
 if($m4is_c3749){
echo __LINE__, " - Username Field:  {$m4is_p4935
}\n";
 echo __LINE__, " - Username:  {$m4is_y193
}\n";
 echo __LINE__, " - User:  {$m4is_l17096
}\n";
 echo __LINE__, " - Counter Name:  {$m4is_y85661
}\n";
 echo __LINE__, " - Action:  {$m4is_c05328
}\n";
 echo __LINE__, " - Value:  {$m4is_v586
}\n";
 
}if($m4is_l17096){
if($m4is_c3749)echo __LINE__, " - Found User\n";
 switch ($m4is_c05328){
case 'set': $this->m4is_r1546->m4is_y165($m4is_y85661, $m4is_v586, $m4is_l17096->ID );
 $this->m4is_r1546->m4is_i12();
 if($m4is_c3749)echo __LINE__, " - Custom Counter Updated\n";
 break;
 
} 
}else{
if($m4is_c3749)echo __LINE__, " - Username {$m4is_y193
} not found.\n";
 
}echo 'Operation Completed';
 $this->m4is_r1546->m4is_s965('send_http_post');
 
}  public 
function m4is_r66307(): void {
m4is_j586::m4is_x7134();
 $m4is_c3749 =isset($_GET['debug']);
 if($m4is_c3749 )echo __LINE__, " - Debug Mode Enabled\n";
 $m4is_r9613 =$this->m4is_r1546->m4is_i76('appname');
 $m4is_h21895 =isset($_POST['Id'])? (int) $_POST['Id']: 0;
 $m4is_y6024 =isset($_GET['destfield'])? trim($_GET['destfield']): '';
 $m4is_l543 =isset($_GET['subscriptionplans'])? explode(',', $_GET['subscriptionplans']): [];
 if($m4is_c3749){
echo __LINE__, " - Contact Id: ", $m4is_h21895, "\n";
 echo __LINE__, " - Destination Field: ", $m4is_y6024, "\n";
 echo __LINE__, " - Subscription Types: ", $m4is_l543, "\n";
 
}if($m4is_h21895 > 0 &&$m4is_y6024 > ''){
$m4is_h3647 =['Id', 'BillingCycle', 'Frequency', 'Status', 'contactId', 'EndDate', 'LastBillDate', 'NextBillDate', 'PaidThruDate', 'StartDate', 'SubscriptionPlanId' ];
 $m4is_v76912 =['contactId' =>$m4is_h21895, 'Status' =>'Active', ];
 $m4is_h023 =m4is_c69807::m4is_o986('RecurringOrder', 997, 0, $m4is_v76912, $m4is_h3647 );
 $m4is_y412 ='99991231T12:59:59';
 $m4is_z81 =date('YmdTH:i:s');
 if(is_array($m4is_h023 )){
foreach ($m4is_h023 as $m4is_y362 ){
if($m4is_c3749)echo __LINE__, " - RecurringOrders\n", print_r($m4is_y362, true), "\n";
 if(!empty($m4is_y362['NextBillDate'])){
if($m4is_y362['NextBillDate']< $m4is_y412){
if($m4is_y362['NextBillDate']>= $m4is_z81){
if(empty($m4is_l543 )||in_array($m4is_y362['SubscriptionPlanId'], $m4is_l543 )){
$m4is_y412 =$m4is_y362['NextBillDate'];
 
}
}
}
}
}
}if($m4is_c3749)echo __LINE__, " - Next Billing Date = ", $m4is_y412, "\n";
 if($m4is_y412 <> '99991231T12:59:59'){
$this->m4is_r1546->m4is_s56($m4is_y6024, $m4is_y412, $m4is_h21895);
 if($m4is_c3749)echo __LINE__, " - Contact Updated (", $m4is_h21895, ")\n";
 
}
}
} 
function m4is_j10295(){
m4is_j586::m4is_x7134();
 $m4is_c3749 =isset($_GET['debug'])? true : false;
 if($m4is_c3749)echo __LINE__, " - Debug Mode Enabled\n";
 $m4is_o0379 =m4is_q62395::m4is_v729($_POST['Id'], 'httppost', 'Expire Subscriptions');
 date_default_timezone_set('America/New_York');
 $m4is_r9613 =$this->m4is_r1546->m4is_i76('appname');
 $m4is_z81 =date('Y-m-d');
 $m4is_h3647 =['Id', ];
 $m4is_v76912 =['Status' =>'Active', 'EndDate' =>'~<=~' . $m4is_z81, ];
 $m4is_h023 =m4is_c69807::m4is_o986('RecurringOrder', 995, 0, $m4is_v76912, $m4is_h3647);
 $m4is_o30 =['Status' =>'Inactive' ];
 if($m4is_c3749){
echo '<pre>Query = ', print_r($m4is_v76912, true), '</pre>';
 echo '<pre>Recurring Orders = ', print_r($m4is_h023, true ), '</pre>';
 
}foreach($m4is_h023 as $m4is_y362){
m4is_c69807::m4is_z64('RecurringOrder', (int) $m4is_y362['Id'], $m4is_o30);
 m4is_q62395::m4is_x6835($m4is_o0379, "Deactivating Recurring Order #{$m4is_y362['Id']
}" );
 if($m4is_c3749){
echo 'Deactivating Recurring Order #', $m4is_y362['Id'], '<br>';
 
}sleep(1);
 
}exit;
 
}   
function m4is_y83($m4is_l91805, $m4is_m661){
    if(isset($m4is_l91805['default_value'])){
$m4is_l91805['default_value']=do_shortcode($m4is_l91805['default_value']);
 
} return $m4is_l91805;
 
}   
function m4is_p52($m4is_z06174, $m4is_m5907){
m4is_j586::m4is_x7134();
 if(!m4is_s52::m4is_w74()){
return $m4is_z06174;
 
}$m4is_m60 =$this->m4is_x72168($m4is_m5907->ID);
 if(!$m4is_m60){
return $m4is_m5907->excerpt;
 
}return null;
 
}    
function m4is_w806($m4is_f087, $m4is_i547){
return;
 if(!class_exists('WooCommerce')){
return;
 
}global $woocommerce;
 $m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087);
 if(!$m4is_h21895){
return;
 
}$m4is_d73218 =$this->m4is_r1546->m4is_z56();
 $m4is_y94 =($m4is_h21895 == $m4is_d73218);
 $m4is_e04697 =apply_filters('memberium/usermeta/crm_field_maps', $m4is_e04697);
 $m4is_d69361 =get_user_meta($m4is_f087);
  $m4is_x03294 =[];
 foreach ($m4is_e04697 as $m4is_g61574 =>$isfield){
$session_fieldname =strtolower($isfield);
 if(isset($m4is_d69361[$m4is_g61574][0])){
$m4is_s542 =$m4is_d69361[$m4is_g61574][0];
 if($m4is_g61574 == 'billing_country' ||$m4is_g61574 == 'shipping_country'){
$m4is_s542 =m4is_q6082::m4is_v46($m4is_s542);
 
} if($m4is_y94){
m4is_q82::m4is_g14($m4is_f087, 'contact', $session_fieldname, $m4is_s542 );
 
} $m4is_x03294[$isfield]=$m4is_s542;
 
}
}if(!empty($m4is_x03294)){
 m4is_p40::m4is_x6560($m4is_h21895, $m4is_x03294);
   $m4is_j90523 =m4is_p40::m4is_p67($m4is_h21895);
 foreach ($m4is_x03294 as $m4is_r637=>$m4is_w86){
$m4is_j90523[$m4is_r637]=$m4is_w86;
 
}$this->m4is_r1546->m4is_v1694($m4is_j90523);
 
}
}
function m4is_g38965($m4is_j90523, $m4is_f087 =0){
return;
 if(!class_exists('WooCommerce')){
return;
 
}if(empty($m4is_f087)){
return;
 
}$m4is_e04697 =apply_filters('memberium/usermeta/crm_field_maps', []);
 $m4is_p4935 =$this->m4is_r1546->m4is_j498('settings', 'username_field');
 $m4is_f087 =$m4is_f087 ? $m4is_f087 : $this->m4is_r1546->m4is_x66();
 if((int) $m4is_f087 == 0){
$m4is_f23865 =get_user_by('email', $m4is_j90523[$m4is_p4935]);
 $m4is_f087 =$m4is_f23865->ID;
 
}if((int) $m4is_f087 == 0){
$m4is_f23865 =get_user_by('login', $m4is_j90523[$m4is_p4935]);
 $m4is_f087 =$m4is_f23865->ID;
 
}if((int) $m4is_f087 > 0){
foreach ($m4is_e04697 as $woofield =>$isfield){
if(isset($m4is_j90523[$isfield])){
$m4is_s542 =trim($m4is_j90523[$isfield]);
 if($woofield == 'billing_country' ||$woofield == 'shipping_country'){
$m4is_s542 =m4is_q6082::m4is_v46($m4is_s542);
 
}update_user_meta($m4is_f087, $woofield, $m4is_s542);
 
}
}
}
}
function m4is_w593($user =NULL){
if(!class_exists('WooCommerce')){
return;
 
} if(isset($_POST['password_1'])&&isset($_POST['password_2'])&&$_POST['password_1']> '' &&$_POST['password_1']== $_POST['password_2']){
$user =wp_get_current_user();
 $this->m4is_g68($user);
 
}
} 
function m4is_t62081(){
if(empty($this->m4is_r1546->m4is_j498('settings', 'extended_reg_fields'))){
return;
 
}?>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="reg_billing_first_name">First Name <span class="required">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" value="" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" style="margin-bottom:-1em;">
		<label for="reg_billing_last_name">Last Name <span class="required">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" value="" />
		</p>
			<?php
 
}
function m4is_f6835($m4is_y193, $m4is_f4930, $m4is_w038){
if(empty($this->m4is_r1546->m4is_j498('settings', 'extended_reg_fields'))){
return;
 
}if(isset($_POST['billing_first_name'])&&empty($_POST['billing_first_name'])){
$m4is_w038->add('billing_first_name_error', __('<strong>Error</strong>: First name is required!', 'woocommerce'));
 
}if(isset($_POST['billing_last_name'])&&empty($_POST['billing_last_name'])){
$m4is_w038->add('billing_last_name_error', __('<strong>Error</strong>: Last name is required!.', 'woocommerce'));
 
}return $m4is_w038;
 
}   
function m4is_h236($m4is_l17096 ){
if(is_a($m4is_l17096, 'WP_User' )){
m4is_q82::m4is_d59($m4is_l17096->ID );
 
}return $m4is_l17096;
 
} 
function m4is_h03978($m4is_y193 ){
$m4is_p4935 =$this->m4is_r1546->m4is_j498('settings', 'username_field');
 if(!strpos($m4is_y193, '@')&&!in_array($m4is_p4935, ['Email', 'EmailAddress2', 'EmailAddress3'])){
$m4is_l17096 =get_user_by('login', $m4is_y193);
 if(is_a($m4is_l17096, 'WP_User')){
$m4is_y193 =$m4is_l17096->data->user_email;
 
}
}return $m4is_y193;
 
} public 
function m4is_y2609(): void {
if(!$this->m4is_r1546->m4is_z56()){
return;
 
}$m4is_e613 =(int) $this->m4is_r1546->m4is_j498('settings', 'autologout_time' );
 if(!$m4is_e613 ){
return;
 
}$m4is_b736 =$this->m4is_r1546->m4is_j498('settings', 'default_logout_page' );
 $m4is_s275 =m4is_q82::m4is_d6758($this->m4is_r1546->m4is_x66(), 'memb_user', 'logout_page', 0 );
 if($m4is_s275 ){
$m4is_e93025 =wp_logout_url(get_permalink($m4is_s275 ));
 
}elseif($m4is_b736){
$m4is_e93025 =wp_logout_url($m4is_b736 );
 
}else{
$m4is_e93025 =wp_logout_url(get_site_url());
 
}printf('<meta http-equiv="refresh" content="%d;url=%s">', $m4is_e613, $m4is_e93025 );
 
}
function m4is_q165(){
return $this->login_redirect_enabled;
 
}
function m4is_t25($m4is_c31){
$this->login_redirect_enabled =(bool) $m4is_c31;
 
}   
function m4is_y5879(){
$m4is_c05328 =isset($_POST['memb_form_type'])? trim(strtolower($_POST['memb_form_type'])): '';
 if(!empty($m4is_c05328)){
$m4is_d3056 ='m4is_t36568';
 $m4is_i10 =['memb_change_email' =>[$m4is_d3056, 'm4is_s82596'], 'memb_change_password' =>[$m4is_d3056, 'm4is_j98462'], ];
 $m4is_i10 =apply_filters('memberium/user_catchers/get', $m4is_i10);
 if(!empty($m4is_i10[$m4is_c05328])){
m4is_j586::m4is_x7134();
 call_user_func($m4is_i10[$m4is_c05328]);
 
}
}
}
function m4is_c12683(){
$m4is_c05328 =isset($_POST['memb_form_type'])? trim(strtolower($_POST['memb_form_type'])): '';
 if(!empty($m4is_c05328)){
$m4is_d3056 ='m4is_t36568';
 $m4is_i10 =['memb_add_payment_method' =>[$m4is_d3056, 'm4is_x366'], 'memb_actionset_button' =>[$m4is_d3056, 'm4is_q605'], 'memb_add_creditcard_button' =>[$m4is_d3056, 'm4is_j6451'], 'memb_cancel_subscription' =>[$m4is_d3056, 'm4is_q97'], 'memb_filebox_upload' =>[$m4is_d3056, 'm4is_s9062'], 'memb_pay_invoice' =>[$m4is_d3056, 'm4is_v08392'], 'memb_place_order' =>[$m4is_d3056, 'm4is_c43859'], 'memb_placeorder_button' =>[$m4is_d3056, 'm4is_p780'], 'memb_registration' =>[$m4is_d3056, 'm4is_j48796'], 'memb_resetfeedurl_button' =>[$m4is_d3056, 'm4is_k3167'], 'memb_send_password' =>[$m4is_d3056, 'm4is_q36'], 'memb_update_contact_form' =>[$m4is_d3056, 'm4is_h83'], 'memb_lost_password' =>[$m4is_d3056, 'm4is_j5928'], ];
 $m4is_i10 =apply_filters('memberium/catchers/get', $m4is_i10 );
 if(!empty($m4is_i10[$m4is_c05328])){
m4is_j586::m4is_x7134();
 call_user_func($m4is_i10[$m4is_c05328]);
 
}
}
}
function m4is_n73018($m4is_c67){
if(is_user_logged_in()){
if($this->m4is_r1546->m4is_v461()){
return $m4is_c67;
 
}return '';
 
}return $m4is_c67;
 
}
function m4is_q264($m4is_w915 ='', $m4is_n614 ='', $m4is_w218 ='' ){
return html_entity_decode($m4is_w915, ENT_NOQUOTES );
 
}public 
function m4is_w70(array $m4is_c486 ){
if(!empty($m4is_c486['title'])){
$m4is_c486['title']=html_entity_decode($m4is_c486['title'], ENT_NOQUOTES );
 
}return $m4is_c486;
 
}   private 
function m4is_s20(){
add_action('login_head', [$this, 'm4is_x196'], PHP_INT_MAX );
 add_action('wp_logout', [$this, 'm4is_w702']);
  add_filter('wp_authenticate_user', [$this, 'm4is_h236'], 1 );
 
}private 
function m4is_w9656(){
add_filter('comments_array', [$this, 'm4is_v13206'], 5);
 add_filter('comments_array', [$this, 'm4is_a87'], 7);
 add_filter('comments_open', [$this, 'm4is_s067'], 100, 2);
 add_filter('get_comments_number', [$this, 'm4is_p3689'], 10, 2);
 
} private 
function m4is_c894(){
add_action('do_feed_atom_comments', [$this, 'm4is_b64'], 1 );
 add_action('do_feed_atom', [$this, 'm4is_b64'], 1 );
 add_action('do_feed_rdf', [$this, 'm4is_b64'], 1 );
 add_action('do_feed_rss', [$this, 'm4is_b64'], 1 );
 add_action('do_feed_rss2_comments', [$this, 'm4is_b64'], 1 );
 add_action('do_feed_rss2', [$this, 'm4is_b64'], 1 );
 add_action('do_feed', [$this, 'm4is_b64'], 1 );
 add_filter('feed_link', function(){
return '';
 
});
 add_filter('gettext', function($m4is_e20478, $m4is_e63195, $m4is_g36127 ){
return in_array($m4is_e20478, ['Entries feed', 'Comments feed'])? '' : $m4is_e20478;
 
}, 10, 3 );
 remove_action('wp_head', 'feed_links_extra', 3 );
 remove_action('wp_head', 'feed_links', 2 );
 remove_action('wp', 'bp_activity_action_favorites_feed', 3 );
 remove_action('wp', 'bp_activity_action_friends_feed', 3 );
 remove_action('wp', 'bp_activity_action_mentions_feed', 3 );
 remove_action('wp', 'bp_activity_action_my_groups_feed', 3 );
 remove_action('wp', 'bp_activity_action_personal_feed', 3 );
 remove_action('wp', 'bp_activity_action_sitewide_feed', 3 );
 remove_action('wp', 'groups_action_group_feed', 3 );
 
} private 
function m4is_m491(){
add_action('do_feed_atom', [$this, 'm4is_t69746'], 1);
 add_action('do_feed_rdf', [$this, 'm4is_t69746'], 1);
 add_action('do_feed_rss', [$this, 'm4is_t69746'], 1);
 add_action('do_feed_rss2', [$this, 'm4is_t69746'], 1);
 
} private 
function m4is_h95321(){
$m4is_h79623 =$this->m4is_r1546->m4is_j498('settings', 'wp_autop');
 if($m4is_h79623 == 1){
remove_filter('the_content', 'wpautop');
 
}elseif($m4is_h79623 == 2){
$m4is_d81702 =has_filter('the_content', 'wpautop');
 if($m4is_d81702 !== false &&$m4is_d81702 < 11){
remove_filter('the_content', 'wpautop');
 add_filter('the_content', 'wpautop', 11);
 
}
}
} private 
function m4is_v15(){
$m4is_f786 =$this->m4is_r1546->m4is_j498('settings', 'disable_lost_password' );
 $m4is_t56607 =$this->m4is_r1546->m4is_j498('settings', 'disable_password_reset' );
 $m4is_b628 =$this->m4is_r1546->m4is_j498('settings', 'autologout_time' );
 $m4is_w57 =$this->m4is_r1546->m4is_j498('settings', 'protect_feeds' );
 if($m4is_f786 ){
add_filter('gettext', [$this, 'm4is_q520']);
 
}if($m4is_t56607){
add_filter('allow_password_reset', [$this, 'm4is_y1862']);
 
}if($m4is_b628 ){
add_action('wp_head', [$this, 'm4is_y2609']);
 
}if(!empty($_GET['rss_user'])){
add_filter('posts_pre_query', ['m4is_a391', 'm4is_e9432'], 10, 2);
 
}if($m4is_w57){
 $this->m4is_c894();
 
}else{
$this->m4is_m491();
 
}if(!is_admin()){
 add_filter('widget_text', 'do_shortcode');
 add_filter('widget_title', 'do_shortcode');
 add_filter('the_title', 'do_shortcode');
 add_filter('the_excerpt', 'do_shortcode');
 add_filter('get_the_excerpt', 'do_shortcode', 10);
 add_filter('get_the_author_description', 'do_shortcode');
 add_filter('excerpt_length', [$this, 'm4is_r873'], 999);
 
}
} private 
function m4is_k9570(){
if($_SERVER['REQUEST_METHOD']== 'POST'){
$m4is_z516 =!empty($_GET['operation'])&&!empty($_GET['auth_key'])||(!empty($_GET['i4w_genpass'])||(!empty($_GET['i4w_sync_user'])));
 if($m4is_z516 ){
add_action('plugins_loaded', [$this, 'm4is_d93'], 100 );
 
}else{
add_action('template_redirect', [$this, 'm4is_y5879'], 1);
 add_action('init', [$this, 'm4is_c12683'], 11);
 
}
}
}  
function m4is_d5366($m4is_c93, $m4is_l17096){
if(!empty($_GET['redirect_to'])){
return $_GET['redirect_to'];
 
}if($this->m4is_r1546->m4is_v461()){
return get_dashboard_url();
 
}if(!empty($this->login_redirect_url )){
return $this->login_redirect_url;
 
}return $m4is_c93;
 
}  public 
function m4is_z960($m4is_e87, $m4is_r2586, $m4is_o26310 ){
if(empty($this->m4is_r1546->m4is_j498('settings', 'sync_new_wp_users'))){
return;
 
}if(empty($m4is_r2586['user_pass'])){
return;
 
}$m4is_a89 =apply_filters('memberium/usermeta/crm_field_maps', []);
 $m4is_i935 =[];
 $m4is_l79562 =get_user_meta($m4is_e87 );
 foreach($m4is_a89 as $meta_name =>$crm_name ){
if(isset($m4is_l79562[$meta_name][0])){
$m4is_i935[$crm_name]=trim($m4is_l79562[$meta_name][0]);
 
}
} if(!empty($m4is_i935 )){
$m4is_i935['Email']=$m4is_r2586['user_email'];
 $m4is_h21895 =m4is_p40::m4is_k82670($m4is_i935 );
 
}
}     public 
function m4is_b068(): void {
if($_SERVER['REQUEST_METHOD']!== 'GET' ){
return;
 
}if(defined('WPE_WHITELABEL' )){
return;
 
}if(!$this->m4is_r1546->m4is_j498('settings', 'affiliate_detect' )){
return;
 
}if(is_admin()||$this->m4is_r1546->m4is_v461()){
return;
 
}$m4is_k74139 ='affiliate';
 $m4is_n6310 ='infusionsoft_affiliate';
 $m4is_l43966 ='memberium/keap/affiliate/id';
 $m4is_r38569 =time()+ YEAR_IN_SECONDS;
 $m4is_o56712 =time()+ DAY_IN_SECONDS;
 $m4is_l0436 =remove_query_arg([$m4is_k74139, 'cookieUUID', 'fbclid']);
 $m4is_f087 =get_current_user_id();
 $m4is_h540 =$_SERVER['HTTP_HOST']?? '';
  if(isset($_GET[$m4is_k74139])){
$m4is_t6601 =(int) $_GET[$m4is_k74139];
 $m4is_k91 =$m4is_t6601 > 0 ? $m4is_r38569 : $m4is_o56712;
 setcookie($m4is_n6310, $m4is_t6601, $m4is_k91, '/', $m4is_h540 );
 $_COOKIE[$m4is_n6310]=$m4is_t6601;
 if($m4is_f087 ){
if($m4is_t6601 <> (int) get_user_meta($m4is_f087, $m4is_l43966, true )){
update_user_meta($m4is_f087, $m4is_l43966, $m4is_t6601 );
 
}
}return;
 m4is_j586::m4is_x7134();
 wp_redirect($m4is_l0436, 302, 'Memberium Affiliate Detect' );
 exit;
 
} if(isset($_COOKIE[$m4is_n6310])){
if($m4is_f087 ){
$m4is_v87 =(int) $_COOKIE[$m4is_n6310];
 $m4is_d96124 =(int) get_user_meta($m4is_f087, $m4is_l43966, true );
 if($m4is_v87 &&$m4is_v87 <> $m4is_d96124 ){
update_user_meta($m4is_f087, $m4is_l43966, $m4is_v87 );
 
}
}return;
 
}if($m4is_f087 > 0 ){
$m4is_t6601 =(int) get_user_meta($m4is_f087, $m4is_l43966, true );
 if($m4is_t6601 > 0 ){
$_COOKIE[$m4is_n6310]=$m4is_t6601;
 $m4is_k91 =$m4is_t6601 > 0 ? $m4is_r38569 : $m4is_o56712;
 setcookie($m4is_n6310, $m4is_t6601, $m4is_k91, '/', $m4is_h540 );
 return;
 
}
} $m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 $m4is_z9504 =($_SERVER['HTTPS']== 'on' ? 'https://' : 'http://' ). $_SERVER['SERVER_NAME']. $_SERVER['REQUEST_URI'];
 $m4is_z9504 =urlencode($m4is_z9504 );
 $m4is_o43 =sprintf('https://%s.infusionsoft.com/aff.html?to=%s', $m4is_r9613, $m4is_z9504 );
 m4is_j586::m4is_x7134();
 wp_redirect($m4is_o43, 302, 'Affiliate Autodetect' );
 exit;
 
}public 
function m4is_n5309(): void {
$m4is_k6751 =$this->m4is_r1546->m4is_j498('settings', 'require_membership', 0 );
 if($m4is_k6751 &&$this->m4is_r1546->m4is_z56()> 0 ){
$m4is_m96240 =m4is_q82::m4is_d6758($this->m4is_r1546->m4is_x66(), 'memb_user', 'membership_tags' );
 if(empty($m4is_m96240 )){
wp_logout();
 
}
}$m4is_q7686 =$this->m4is_r1546->m4is_j498('settings', 'site_ban_tag', 0 );
 if($m4is_q7686 ){
$m4is_l9321 =explode(',', m4is_q82::m4is_d6758($this->m4is_r1546->m4is_x66(), 'memb_user', 'tags', '' ));
 if(in_array($m4is_q7686, $m4is_l9321 )){
wp_logout();
 
}
}
} 
}

