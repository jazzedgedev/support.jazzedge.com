<?php

/**
 * Copyright (c) 2017-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_u45762 {
private static $m4is_r1546;
 private static $m4is_h21895;
 private static $m4is_f683;
 private static $m4is_r02639;
 private static $m4is_f4218;
  static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_h21895 =(int) self::$m4is_r1546->m4is_z56();
 self::$m4is_r02639 =!m4is_s52::m4is_w74();
 self::$m4is_f683 =m4is_f58::m4is_c26();
 self::$m4is_f4218 ='memberium';
 
} static 
function m4is_u84($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'htmlattr' =>'', 'leavename' =>0, 'post_id' =>0, 'txtfmt' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]=== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_x032 =m4is_f61::m4is_d8195($m4is_l62046['leavename'], false );
 $m4is_b4068 =(int) $m4is_l62046['post_id'];
 $m4is_t09761 =$m4is_b4068 == 0 ? get_permalink(NULL, $m4is_l62046['leavename']): get_permalink($m4is_b4068, $m4is_x032 );
  if($m4is_t09761 === false ){
error_log(sprintf('Memberium:  [error] %s failed to retrieve permalink for post ID %d', $m4is_v3458, $m4is_b4068 ));
 return 'Error: Failed to retrieve the permalink.';
 
} return m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} static 
function m4is_h19($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 if(is_feed()){
return '';
 
}$m4is_o74081 =(int) m4is_r83::m4is_c26()->m4is_j498('settings', 'persistent_login', 0 );
  $m4is_y642 =['redirect' =>isset($_GET['redirect_to'])? $_GET['redirect_to']: get_admin_url(), 'button_label' =>'Log In', 'form_id' =>'loginform', 'password_label' =>'Password:', 'remember_label' =>'Remember Me', 'username_label' =>'Username:', 'remember_value' =>'forever',  'remember' =>false,  'secure' =>false, 'show' =>false, 'error_message' =>_x('Login Failed', 'memberium_loginform', self::$m4is_f4218 ), ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_q37596 =$m4is_l62046['redirect'];
 $m4is_m92735 =$m4is_l62046['form_id'];
 $m4is_s10 =$m4is_l62046['button_label'];
 $m4is_j86 =$m4is_l62046['password_label'];
 $m4is_s7503 =$m4is_l62046['remember_label'];
 $m4is_a459 =$m4is_l62046['username_label'];
 $m4is_l073 =trim($m4is_l62046['error_message']);
 $m4is_m6628 =m4is_f61::m4is_d8195($m4is_l62046['secure'], false );
 $m4is_h13956 =m4is_f61::m4is_d8195($m4is_l62046['remember'], false );
 $m4is_p16 =m4is_f61::m4is_d8195($m4is_l62046['remember_value'], false );
 $m4is_a36901 =m4is_f61::m4is_d8195($m4is_l62046['show'], false );
 $m4is_d82350 =function_exists('stopbadbots_addfieldlogin' );
 $m4is_u6591 ='';
  if(is_user_logged_in()&&!$m4is_a36901 ){
if(self::$m4is_r1546->m4is_v461()){
return '<p>' . esc_html(_x('Logged in as Admin', 'memb_loginform', self::$m4is_f4218 )). '</p>';
 
}return '';
 
} if(!empty($_GET['login'])&&$_GET['login']== 'failed' ){
$m4is_o9461 =isset($_COOKIE['login_error'])? __($_COOKIE['login_error']): __('Login Failed', 'memb_loginform' );
 $m4is_o9461 =empty($m4is_l62046['error_message'])? $m4is_o9461 : $m4is_l073;
 $m4is_o9461 =apply_filters('login_errors', $m4is_o9461 );
 if(!empty ($m4is_o9461 )){
$m4is_u6591 .= '<p class="memberium-login-error">' . esc_html($m4is_o9461 ). '</pre>';
 
}
} $m4is_y66291 =['echo' =>false, 'form_id' =>$m4is_m92735, 'label_log_in' =>$m4is_s10, 'label_password' =>$m4is_j86, 'label_remember' =>$m4is_s7503, 'label_username' =>$m4is_a459, 'redirect' =>$m4is_q37596, 'remember' =>$m4is_h13956, 'value_remember' =>$m4is_p16, ];
  $m4is_u6591 .= "\n\n<!-- Memberium-WordPress Login Form -->\n";
  if($m4is_d82350 ){
add_filter('login_form_middle', function($m4is_t09761, $m4is_y66291 ){
return '<input name=stopbadbots_key type=hidden value=1 />';
 
}, 10, 2);
 
} $m4is_u6591 .= wp_login_form($m4is_y66291 );
  $m4is_u6591 =do_shortcode($m4is_u6591 );
  if($m4is_d82350 ){
remove_filter('login_form_middle', 'stopbadbots_addfieldlogin' );
 
} if($m4is_m6628 ){
$m4is_u6591 =m4is_f61::m4is_b07361($m4is_u6591 );
 
} return $m4is_u6591;
 
} static 
function m4is_d64237($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'date_format' =>'', 'default' =>'', 'fields' =>'', 'htmlattr' =>'', 'post_id' =>0, 'separator' =>' ', 'txtfmt' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  $m4is_b4068 =empty($m4is_l62046['post_id'])? get_the_ID(): (int) $m4is_l62046['post_id'];
  $m4is_a89 =array_filter(explode(',', $m4is_l62046['fields']));
  $m4is_j29 =$m4is_l62046['date_format'];
  $m4is_n246 =$m4is_l62046['default'];
  $m4is_o9861 =$m4is_l62046['separator'];
  if(empty($m4is_b4068 )||empty($m4is_a89 )){
return '';
 
} $m4is_s12460 =count($m4is_a89 );
  $m4is_m5907 =get_post($m4is_b4068 );
  $m4is_o498 ='';
  foreach ($m4is_a89 as $m4is_r637 ){
 $m4is_a76 =get_post_meta($m4is_b4068, $m4is_r637, true );
  if(!empty($m4is_a76 )){
 $m4is_o076 =strtotime($m4is_a76 );
   if(empty($m4is_j29 )||empty($m4is_o076 )){
$m4is_w86 =$m4is_a76;
 
}else{
$m4is_w86 =date($m4is_j29, $m4is_o076 );
 
}
} elseif(!empty($m4is_m5907->$m4is_r637 )){
$m4is_w86 =$m4is_m5907->$m4is_r637;
 
} else{
$m4is_w86 =$m4is_n246;
 
} $m4is_o498 .= $m4is_w86;
  if(--$m4is_s12460 > 0 ){
$m4is_o498 .= $m4is_o9861;
 
}
} return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} static 
function m4is_p6891($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} $m4is_y642 =['after' =>'',  'before' =>'',  'htmlattr' =>'',  ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  $m4is_s8523 =self::$m4is_r1546->m4is_j498('settings', 'login_url' );
  $m4is_t09761 =$m4is_s8523 < 1 ? wp_login_url(): get_permalink($m4is_s8523 );
  return m4is_f61::m4is_u150(false, $m4is_t09761, '', '', $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} static 
function m4is_h3651($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
global $wp_embed;
 if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['bypass_permissions' =>0, 'capture' =>'', 'field' =>'post_content', 'id' =>0, 'length' =>0, 'tagid' =>'', 'txtfmt' =>'', 'type' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_s15437 =array_filter(explode(',', $m4is_l62046['tagid']));
 $m4is_q485 =strtolower(trim($m4is_l62046['type']));
 $m4is_r637 =trim($m4is_l62046['field']);
 $m4is_r637 =empty($m4is_r637 )? 'post_content' : $m4is_r637;
 $m4is_b4068 =(int) $m4is_l62046['id'];
 if(empty($m4is_b4068 )){
return '';
 
}if(!empty($m4is_s15437 )){
if(!self::$m4is_r1546->m4is_m2480($m4is_s15437 )){
return '';
 
}
}if(empty($m4is_q485 )){
switch ($m4is_v3458 ){
case 'memb_include_partial': $m4is_q485 ='partials';
 break;
 case 'memb_include_page': $m4is_q485 ='page';
 break;
 default: $m4is_q485 ='post';
 break;
 
}
} if(is_int($m4is_b4068 )){
$m4is_b86 =get_post($m4is_b4068, ARRAY_A );
 $m4is_b4068 =$m4is_b86['ID'];
 
}elseif($m4is_b4068 == 0 ){
$m4is_b86 =get_page_by_path($m4is_b4068, ARRAY_A, $m4is_q485 );
 $m4is_b4068 =empty($m4is_b86['ID'])? 0 : $m4is_b86['ID'];
 
}elseif($m4is_b4068 == 0 ){
$m4is_b86 =get_page_by_title($m4is_b4068, ARRAY_A, $m4is_q485 );
 
} if(self::$m4is_f683->m4is_x72168((int) $m4is_b86['ID'])){
$m4is_o498 =do_shortcode($wp_embed->run_shortcode($m4is_b86[$m4is_r637]));
 
}else{
$m4is_o498 =do_shortcode($wp_embed->run_shortcode($m4is_b86['post_excerpt']));
 
}if($m4is_l62046['length']){
$m4is_o498 =substr($m4is_o498, 0, $m4is_l62046['length']);
 
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
}static 
function m4is_j85296($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['logintext' =>'Login', 'logouttext' =>'Logout', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 return is_user_logged_in()? $m4is_l62046['logouttext']: $m4is_l62046['logintext'];
 
}static 
function m4is_b315($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'htmlattr' =>'', 'linktext' =>'Logout', 'url' =>'' ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_n6062 =trim($m4is_l62046['url']);
 $m4is_n6062 =empty($m4is_n6062 )? self::$m4is_r1546->m4is_x6069(): $m4is_n6062;
 $m4is_v0863 =trim($m4is_l62046['htmlattr']);
 $m4is_e93025 =wp_logout_url($m4is_n6062 );
 $m4is_t049 =trim($m4is_l62046['linktext']);
 if(empty($m4is_v0863 )){
$m4is_s64 =_x('Logout', 'memb_logout_link', self::$m4is_f4218 );
 $m4is_t09761 =sprintf('<a href="%s" title="%s" class="memb_logout_link">%s</a>', $m4is_e93025, $m4is_s64, $m4is_t049 );
 
}else{
$m4is_t09761 =$m4is_e93025;
 
}return m4is_f61::m4is_u150(false, $m4is_t09761, '', $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} public static 
function m4is_m89($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return 'n/a';
 
}if(!is_singular()){
return '';
 
}self::$m4is_r1546->m4is_i80956();
 wp_destroy_current_session();
 wp_clear_auth_cookie();
 wp_logout();
 return '';
 
} static 
function m4is_f696($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} if(is_feed()||self::$m4is_r1546->m4is_v461()){
return '';
 
} $m4is_y642 =['automatic' =>true, 'delay' =>0, 'forcejs' =>true, 'target' =>'_self', 'url' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  $m4is_n6062 =trim($m4is_l62046['url']);
 $m4is_b4068 =get_the_id();
 $m4is_i215 =(int) $m4is_l62046['delay']* 1000;
 $m4is_a04 =m4is_f61::m4is_d8195($m4is_l62046['automatic'], true );
 $m4is_f4207 =m4is_f61::m4is_d8195($m4is_l62046['forcejs'], true );
 $m4is_m06 =strtolower(trim($m4is_l62046['target']));
 $m4is_z61 =!headers_sent();
 $m4is_z61 =$m4is_z61 &&empty($m4is_i215 );
 $m4is_z61 =$m4is_z61 &&(is_single($m4is_b4068 )||is_page($m4is_b4068 ));
 $m4is_z61 =$m4is_z61 &&$m4is_a04;
 $m4is_z61 =$m4is_z61 &&!$m4is_f4207;
  if($m4is_z61 ){
wp_redirect(html_entity_decode($m4is_n6062 ));
 exit;
 
} $m4is_b72658 =$m4is_m06 == '_self' ||empty($m4is_m06 )? sprintf('window.location  = ("%s");', $m4is_n6062 ): sprintf('window.open( "%s", "%s" );', $m4is_n6062, $m4is_m06 );
  if($m4is_i215 ){
$m4is_r41 =<<<JAVASCRIPTBLOCK
				'<script>
					jQuery(document).ready(function() {
						setTimeout(function() {
							{$m4is_b72658
}
						}, {$m4is_i215
} );
					});
				</script>
			JAVASCRIPTBLOCK;
 
}else{
$m4is_r41 ="<script> {$m4is_b72658
} </script>";
 
} return html_entity_decode($m4is_r41 );
 
} static 
function m4is_q71065($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} $m4is_y642 =['capture' =>'', 'txtfmt' =>'', 'filename' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
} $m4is_v3458 =strtolower($m4is_v3458 );
  $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  if(substr($m4is_l62046['filename'], 0, 1 )!== '/' &&substr($m4is_l62046['filename'], 0, 1 )!== '\\' ){
$m4is_l62046['filename']=ABSPATH . '/' . $m4is_l62046['filename'];
 
} $m4is_l62046['filename'].= '.php';
  if(!file_exists($m4is_l62046['filename'])){
if(self::$m4is_r1546->m4is_v461()){
return _x('Not Found.', 'memb_php_include', self::$m4is_f4218 );
 
}else{
return '';
 
}
} ob_start();
  if($m4is_v3458 == 'memb_phpinclude_once' ){
include_once $m4is_l62046['filename'];
 
}else{
include $m4is_l62046['filename'];
 
} $m4is_o498 =ob_get_clean();
  return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
} static 
function m4is_y1465($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'default' =>'', 'htmlattr' =>'', 'name' =>'', 'txtfmt' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
} $m4is_x3066 =['memb_cookie' =>$_COOKIE, 'memb_get' =>$_GET, 'memb_post' =>$_POST, 'memb_request' =>$_REQUEST, 'memb_server' =>$_SERVER, 'memb_session' =>isset($_SESSION )? $_SESSION : [], ];
  $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  $m4is_o498 ='';
 $m4is_v3458 =strtolower($m4is_v3458 );
  $m4is_i680 =array_key_exists($m4is_v3458, $m4is_x3066 )? $m4is_x3066[$m4is_v3458 ]: false;
  $m4is_v52 =array_filter(explode(',', $m4is_l62046['name']));
  if(!$m4is_i680 ){
return '';
 
} foreach ($m4is_v52 as $m4is_v81756 ){
if(!empty($m4is_i680[$m4is_v81756])){
$m4is_i680 =$m4is_i680[$m4is_v81756];
 
}else{
$m4is_i680 =$m4is_l62046['default'];
 break;
 
}
} $m4is_o498 =is_array($m4is_i680 )? htmlspecialchars(print_r($m4is_i680, true )): htmlspecialchars($m4is_i680 );
  return (string) m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} static 
function m4is_h90372($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} $m4is_y642 =['capture' =>'', 'shortcodes' =>false, 'txtfmt' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  $m4is_t09761 =preg_replace('#</p>\s*<p>#', '', $m4is_t09761 );
  $m4is_o9361 =m4is_f61::m4is_d8195($m4is_l62046['shortcodes'], false );
  $m4is_t09761 =$m4is_o9361 ? do_shortcode($m4is_t09761 ): $m4is_t09761;
  return m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
} static 
function m4is_h20($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} if(is_feed()){
return '';
 
} $m4is_v59 =['__utma', '__utmc', '__utmz', 'NREUM', 'PHPSESSID', 'wordpress_test_cookie', 'wp-settings-1', 'wp-settings-time-1', ];
  $m4is_y642 =['domain' =>$_SERVER['HTTP_HOST'], 'expiration' =>'forever', 'httponly' =>false, 'name' =>'', 'path' =>'/', 'secure' =>false, 'value' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_b6306 =trim($m4is_l62046['name']);
 if(empty($m4is_b6306 )||in_array($m4is_b6306, $m4is_v59 )){
return '';
 
} $m4is_l62046['expiration']=strtolower(trim($m4is_l62046['expiration']));
 if($m4is_l62046['expiration']=== 'forever' ){
$m4is_l62046['expiration']='2038-01-19 00:00:00';
 
}elseif($m4is_l62046['expiration']== '' ||$m4is_l62046['expiration']== 'session' ){
$m4is_l62046['expiration']=0;
 
}elseif($m4is_l62046['expiration']=== (int) $m4is_l62046['expiration']){
$m4is_l62046['expiration']+= time();
 
}else{
$m4is_l62046['expiration']=strtotime($m4is_l62046['expiration']);
 
} $_COOKIE[$m4is_b6306 ]=$m4is_l62046['name'];
  m4is_f58::m4is_c26()->m4is_j97318($m4is_b6306, ['name' =>$m4is_b6306, 'value' =>$m4is_l62046['value'], 'expiration' =>$m4is_l62046['expiration'], 'path' =>$m4is_l62046['path'], 'domain' =>$m4is_l62046['domain'], 'secure' =>m4is_f61::m4is_d8195($m4is_l62046['secure'], false ), 'httponly' =>m4is_f61::m4is_d8195($m4is_l62046['httponly'], false ), ]);
 return '';
 
} static 
function m4is_i857($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
  $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'date' =>'now', 'format' =>'l, F dS, Y, g:sA e', 'host_timezone' =>get_option('timezone_string' ), 'htmlattr' =>'', 'modifier' =>'', 'txtfmt' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_o498 ='';
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_u6385 =timezone_identifiers_list();
  $m4is_l62046['host_timezone']=empty($m4is_l62046['host_timezone'])? 'UTC' : $m4is_l62046['host_timezone'];
  if(!in_array($m4is_l62046['host_timezone'], $m4is_u6385 )){
$m4is_m96356 =array_search(strtolower($m4is_l62046['host_timezone']), array_map('strtolower', $m4is_u6385 ));
 if($m4is_m96356 !== false ){
$m4is_l62046['host_timezone']=$m4is_u6385[$m4is_m96356 ];
 
}
} if(in_array($m4is_l62046['host_timezone'], $m4is_u6385 )){
$m4is_d38091 =date_default_timezone_get();
 date_default_timezone_set($m4is_l62046['host_timezone']);
 $m4is_o498 =date($m4is_l62046['format'], strtotime($m4is_l62046['date']. ' ' . $m4is_l62046['modifier']));
 date_default_timezone_set($m4is_d38091 );
 
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} static 
function m4is_e3465($m4is_l62046 =[], $m4is_t09761 =null, $tag ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} m4is_j586::m4is_x7134();
  $m4is_y642 =['capture' =>'', 'txtfmt' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
} $m4is_o498 ='';
  $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  $m4is_i3208 =self::$m4is_r1546->m4is_j498('settings', 'global_excerpt' );
  if(!empty($m4is_i3208 )){
$m4is_o498 =do_shortcode($m4is_i3208 );
 
} return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
} static 
function m4is_x56($m4is_l62046 =[], $m4is_t09761 =null, $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'date_format' =>'F jS, Y', 'htmlattr' =>'', 'txtfmt' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
} if(!is_user_logged_in()){
return '';
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  $m4is_o498 =date($m4is_l62046['date_format'], strtotime(get_userdata(self::$m4is_r1546->m4is_x66())->user_registered ));
  return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} static 
function m4is_e2068($m4is_l62046 =[], $m4is_t09761 =null, $m4is_v3458 =''): string {
 if(self::$m4is_r02639){
return '';
 
}m4is_j586::m4is_x7134();
  if(!m4is_s52::m4is_w74()){
return wp_login_url();
 
} $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'htmlattr' =>'', 'txtfmt' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218);
  $m4is_b4068 =self::$m4is_r1546->m4is_j498('settings', 'registration_url');
 $m4is_y90287 =self::$m4is_r1546->m4is_j498('settings', 'login_url');
  if(!$m4is_b4068 &&$m4is_y90287 > 0){
$m4is_b4068 =$m4is_y90287;
 
} $m4is_o498 =$m4is_b4068 ? get_permalink($m4is_b4068): get_site_url();
  return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} static 
function m4is_o6103($m4is_l62046 =[], $m4is_t09761 =null, $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} m4is_j586::m4is_x7134();
  $m4is_y642 =['authkey' =>'', 'cachetime' =>3600, 'capture' =>'', 'field' =>'post_content', 'id' =>0, 'txtfmt' =>'', 'url' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218);
  if(empty($m4is_l62046['id'])||empty($m4is_l62046['url'])||empty($m4is_l62046['authkey'])||empty($m4is_l62046['field'])){
return '';
 
} $m4is_y086 =['user-agent' =>'Memberium', 'body' =>['contactId' =>self::$m4is_h21895 ], ];
  $m4is_x70 =$m4is_l62046['url']. '?operation=get-post&auth_key=' . urlencode(trim($m4is_l62046['authkey'])). '&post_id=' . $m4is_l62046['id']. '&field=' . urlencode(trim($m4is_l62046['field']));
  $response =wp_remote_post($m4is_x70, $m4is_y086);
  $m4is_o498 =do_shortcode($response['body']);
  return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
} static 
function m4is_w06($m4is_l62046 =[], $m4is_t09761 =null, $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} m4is_j586::m4is_x7134();
  $m4is_y642 =['type' =>'lostpassword', 'htmlattr' =>'', 'redirect' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  $m4is_j0361 =strtolower(trim($m4is_l62046['type']));
  $m4is_f602 =get_option('memberium_pages' );
    $m4is_o498 =$m4is_j0361 == 'lostpassword' ? wp_lostpassword_url($m4is_l62046['redirect']): (array_key_exists($m4is_j0361, $m4is_f602 )? get_permalink($m4is_f602[$m4is_j0361 ]): '' );
  return m4is_f61::m4is_u150(false, $m4is_o498, '', '', $m4is_l62046['htmlattr']);
 
} static 
function m4is_t6407($m4is_l62046 =[], $m4is_t09761 =null, $m4is_v3458 ='' ): string {
 if(self::$m4is_r02639 ){
return '';
 
} m4is_j586::m4is_x7134();
  $m4is_y642 =['action' =>'default', ];
  if(array_key_exists(0, $m4is_l62046)&&$m4is_l62046[0]=== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  $m4is_w04 =['default', 'excerpt', 'hide', 'redirect', 'show', ];
  $m4is_c05328 =in_array(strtolower(trim($m4is_l62046['action'])), $m4is_w04 )? strtolower(trim($m4is_l62046['action'])): 'default';
  m4is_f58::m4is_c26()->m4is_f4206($m4is_c05328 );
  return '';
 
} 
}

