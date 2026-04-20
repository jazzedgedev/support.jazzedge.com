<?php

/**
 * Copyright (c) 2017-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_t4578 {
private object $m4is_r1546;
 private bool $shortcodes_registered =false;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_s2469();
 $this->m4is_d4861();
 
} private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
} public 
function m4is_k94(){

} 
function m4is_d4861(){
add_action('init', [$this, 'm4is_k94']);
 add_action('memberium/shortcodes/add', [$this, 'm4is_s2469']);
 add_action('memberium/shortcodes/remove', [$this, 'm4is_w796']);
 if(!empty($_POST['memb_form_type'])){
add_action('template_redirect', [$this, 'm4is_c12683'], 1 );
 
}if(isset($_GET['memb_actionlink'])&&isset($_GET['verification'])){
add_action('init', [$this, 'm4is_e54']);
 
}
}   
function m4is_s59124(): array {
$m4is_l9321 =[];
 $m4is_l9321['nested']=[];
 $m4is_l9321['standard']=['memb_spiffy_checkout' =>'m4is_y6794', 'memb_video_progress' =>'m4is_r7064',  ];
 return $m4is_l9321;
 
}
function m4is_s2469(){
$m4is_l9321 =$this->m4is_s59124();
  foreach ($m4is_l9321['standard']as $m4is_p786 =>$m4is_j86631){
if(is_array($m4is_j86631)){
add_shortcode($m4is_p786, $m4is_j86631);
 
}else{
add_shortcode($m4is_p786, [$this, $m4is_j86631]);
 
}
} foreach ($m4is_l9321['nested']as $m4is_p786 =>$m4is_j86631){
$m4is_s7349 =$m4is_j86631[1];
 add_shortcode($m4is_p786, [$this, $m4is_s7349]);
 for ($m4is_b3785 =1;
 $m4is_b3785 < (int) $m4is_j86631[0];
 $m4is_b3785++){
add_shortcode("{$m4is_p786
}{$m4is_b3785
}", [$this, $m4is_s7349]);
 
}
}
}
function m4is_w796(){
$m4is_l9321 =$this->m4is_s59124();
 if(isset($m4is_l9321['standard'])&&is_array($m4is_l9321['standard'])){
foreach ($m4is_l9321['standard']as $m4is_p786 =>$m4is_j86631){
remove_shortcode($m4is_p786);
 
}
}if(isset($m4is_l9321['nested'])&&is_array($m4is_l9321['nested'])){
foreach ($m4is_l9321['nested']as $m4is_p786 =>$m4is_j86631){
remove_shortcode($m4is_p786);
 for ($m4is_b3785 =1;
 $m4is_b3785 < (int) $m4is_j86631[0];
 $m4is_b3785++){
remove_shortcode($m4is_p786 . $m4is_b3785);
 
}
}
}
}    
function m4is_y6794($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(!m4is_s52::m4is_w74()){
return '';
 
}if(is_feed()){
return '';
 
}$m4is_y642 =['url' =>'',  'omit_js' =>false, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}m4is_j586::m4is_x7134();
 $m4is_z9628 =$this->m4is_r1546->m4is_j498('settings', 'spiffy_subdomain');
 if(empty($m4is_z9628 )){
if($this->m4is_r1546->m4is_v461()){
return '<p style="color:red;font-weight:bold;">Spiffy Subdomain not defined.</p>';
 
}return '';
 
}if($this->m4is_r1546->m4is_v461()){
return '<p style="color:red;font-weight:bold;">Spiffy Checkout not supported when logged in as Admin.</p>';
 
}$m4is_l17096 =wp_get_current_user();
 $m4is_f4930 =$m4is_l17096 ? $m4is_l17096->user_email : '';
 $m4is_y642 =['url' =>'',  'omit_js' =>false, ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 if($m4is_f4930){
$m4is_l62046['url']=$m4is_l62046['url']. '?email=' . urlencode($m4is_f4930);
 
}if(!$m4is_l62046['omit_js']){
add_action('wp_footer', [$this, 'm4is_b61']);
 
}return '<spiffy-checkout url="' . $m4is_l62046['url']. '"></spiffy-checkout>';
   
}
function m4is_b61(){
$m4is_z9628 =$this->m4is_r1546->m4is_j498('settings', 'spiffy_subdomain');
 if(empty($m4is_z9628 )){
return '';
 
}echo '<script>
		"use strict";!function(t,e){var i=e.spiffy=e.spiffy||[];if(!i.init){
			if(i.invoked)return void(e.console&&console.error&&console.warn("Spiffy Elements included twice."))
			;i.invoked=!0,i.methods=["identify","config","reset","debug","off","on"],i.factory=function(t){
			return function(){var e=Array.prototype.slice.call(arguments);return e.unshift(t),i.push(e),i}},
			i.methods.forEach(function(t){spiffy[t]=i.factory(t)}),i.load=function(e){if(!spiffy.ACCOUNT){
			spiffy.ACCOUNT=e;var i=t.createElement("script");i.type="text/javascript",i.async=!0,
			i.crossorigin="anonymous",i.src="https://js.static.spiffy.co/spiffy.js?a="+e
			;var n=t.getElementsByTagName("script")[0];n.parentNode.insertBefore(i,n)}}}}(document,window),
			spiffy.SNIPPET_VERSION="1.0.2";

			spiffy.load("', $m4is_z9628, '");
		</script>';
 
}
function m4is_r7064($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(!m4is_s52::m4is_w74()){
return '';
 
}if(is_feed()){
return '';
 
}$m4is_y642 =['src' =>'', 'time_tags' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}m4is_j586::m4is_x7134();
 if(!function_exists('m4is_i63417')){
require_once $this->m4is_r1546->m4is_j541(). '/shortcodes/video-progress/video-progress.php';
 
} if(current_user_can('manage_options')||!is_user_logged_in()){
return '';
 
}m4is_f580()->m4is_b61493($m4is_l62046, $m4is_t09761, $m4is_v3458);
 return '';
 
}   
function m4is_c12683(){
m4is_j586::m4is_x7134();
 $m4is_i60 =strtolower($_POST['memb_form_type']);
 $m4is_i10 =['memb_reset_password' =>'m4is_a95', ];
 if(array_key_exists($m4is_i60, $m4is_i10 )){
$m4is_c661 =$m4is_i10[$m4is_i60];
 $this->$m4is_c661();
 
}
}
function m4is_e54(){
if(!wp_verify_nonce($_GET['verification'], $_GET['memb_actionlink'])){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_c05328 =unserialize(base64_decode($_GET['memb_actionlink']));
 $m4is_i8169 =$m4is_c05328['debug'];
 if($m4is_i8169 ){
echo '<pre>';
 echo __LINE__, " - Set ActionSet = {$m4is_c05328['actionset']
}<br />";
 echo __LINE__, " - Set ContactId = {$m4is_c05328['contact_id']
}<br />";
 echo __LINE__, " - Set FUS       = {$m4is_c05328['fus']
}<br />";
 echo __LINE__, " - Set Goals     = {$m4is_c05328['goals']
}<br />";
 echo __LINE__, " - Set Tags      = {$m4is_c05328['tags']
}<br />";
 echo __LINE__, " - Set Tokens    = {$m4is_c05328['tokens']
}<br />";
 echo __LINE__, " - Set Redirect  = {$m4is_c05328['redirect']
}<br />";
 
}if($m4is_c05328['contact_id']){
if(!empty($m4is_c05328['actionset'])){
$this->m4is_r1546->m4is_u71903($m4is_c05328['actionset'], $m4is_c05328['contact_id']);
 
}if(!empty($m4is_c05328['fus'])){
$this->m4is_r1546->m4is_k59073($m4is_c05328['fus'], $m4is_c05328['contact_id']);
 
}if(!empty($m4is_c05328['goals'])){
$this->m4is_r1546->m4is_t64038($m4is_c05328['goals'], $m4is_c05328['contact_id']);
 
}if(!empty($m4is_c05328['tags'])){
$this->m4is_r1546->m4is_k98($m4is_c05328['tags'], $m4is_c05328['contact_id']);
 
}
}if(!empty($m4is_c05328['tokens'])){
$this->m4is_r1546->m4is_s85($m4is_c05328['tokens']);
 
}$this->m4is_r1546->m4is_x4831($m4is_c05328['contact_id']);
 $this->m4is_r1546->m4is_i12($m4is_c05328['contact_id']);
 m4is_q82::m4is_d59(get_current_user_id());
 if($m4is_i8169){
$m4is_k824 =m4is_q82::m4is_d6758($this->m4is_r1546->m4is_x66(), 'keap', 'contact', []);
 echo print_r($m4is_k824, true);
 echo '<a href="', $m4is_c05328['redirect'], '">', _x('Continue...', 'memb_action_link', 'memberium'), '</a>';
 echo '</pre>';
 exit;
 
}if($m4is_c05328['redirect']> ''){
m4is_j586::m4is_k751();
 wp_redirect($m4is_c05328['redirect']);
 exit;
 
}
}
function m4is_a95(){
if(!wp_verify_nonce($_POST['_wpnonce'], 'memb_reset_password_' . $_POST['form_id']. $_POST['parameters'])){
wp_die(_x('Security Check Failed - Nonce Validation Error', 'memb_change_password', 'memberium'));
 exit;
 
}if(is_user_logged_in()){
return;
 
}if(!wp_verify_nonce($_POST['_wpnonce'], 'memb_reset_password_' . $_POST['form_id'])){
wp_die(_x('Invalid Password Reset Request', 'memb_reset_password', 'memberium'));
 exit;
 
}m4is_j586::m4is_x7134();
 global $wpdb;
 $m4is_u897 =unserialize(base64_decode($_POST['params']));
 if(!empty($_POST['email'])){
$m4is_l17096 =get_user_by('email', $_POST['email']);
 if($m4is_l17096){
global $wp;
 $m4is_l9671 =get_password_reset_key($m4is_l17096);
 $m4is_g928 =$m4is_l17096->data->user_login;
 $m4is_y66291 =['action' =>'rp', 'key' =>$m4is_l9671, 'login' =>$m4is_g928, ];
 $m4is_n6062 =home_url(add_query_arg($m4is_y66291, $wp->request));
 if($m4is_u897['template_id']){
$m4is_h167 =$this->m4is_r1546->m4is_a1094($m4is_u897['template_id']);
 $m4is_h167['textBody']=str_ireplace('~Memberium.ResetURL~', $m4is_n6062, $m4is_h167['textBody']);
 $m4is_h167['htmlBody']=str_ireplace('~Memberium.ResetURL~', $m4is_n6062, $m4is_h167['htmlBody']);
 $m4is_v30712 =[m4is_p40::m4is_w58096($m4is_l17096->ID)];
 $this->m4is_r1546->m4is_r1476()->sendEmail($m4is_v30712, $m4is_h167['fromAddress'], $m4is_h167['toAddress'], '', '', $m4is_h167['contentType'], $m4is_h167['subject'], $m4is_h167['htmlBody'], $m4is_h167['txtBody']);
 
}else{
$m4is_h6837 =apply_filters('memberium_password_reset_to_email', $m4is_l17096->data->user_email);
 $m4is_j125 =apply_filters('memberium_password_reset_subject', 'Password Reset Email');
 $m4is_a173 ="Dear ~Contact.FirstName~,\n\nHere is your requested Password Reset Link:\n\n~Memberium.ResetURL~\n\n";
 $m4is_a173 =apply_filters('memberium_password_reset_wpmail_body', $m4is_a173);
 $m4is_a173 =str_ireplace('~Memberium.ResetURL~', $m4is_n6062, $m4is_a173);
 $m4is_a173 =str_ireplace('~Contact.FirstName~', $m4is_l17096->first_name, $m4is_a173);
 wp_mail($m4is_h6837, $m4is_j125, $m4is_a173);
 
}
}else{
 
}
}elseif($_POST['password2']== $_POST['password1']){
$m4is_r6249 =true;
 if(empty($_POST['password1'])||empty($_POST['password2'])){
$m4is_r6249 =false;
 
}if(strlen($_POST['password1'])< $this->m4is_r1546->m4is_j498('settings', 'min_password_length')){
$m4is_r6249 =false;
 
}$m4is_l17096 =check_password_reset_key($_POST['key'], $_POST['login']);
 if('WP_User' == get_class($m4is_l17096 )){
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_l17096->ID);
 $m4is_m676 =$_POST['password1'];
 $m4is_a07615 =wp_hash_password($m4is_m676 );
 $m4is_v2613 ="UPDATE %i SET `user_pass` = %s WHERE `ID` = %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $wpdb->users, $m4is_a07615, $m4is_l17096->ID );
 $wpdb->query($m4is_v2613 );
 if($m4is_h21895){
 $m4is_r2369 =$this->m4is_r1546->m4is_j498('settings', 'local_auth_only');
 if(empty($m4is_r2369)){
$m4is_r6234 =$this->m4is_r1546->m4is_j498('settings', 'password_field');
 $m4is_e32607 =[$m4is_r6234 =>$m4is_m676, ];
 m4is_p40::m4is_x6560($m4is_h21895, $m4is_e32607);
   $m4is_r9613 =$this->m4is_r1546->m4is_i76('appname');
 $m4is_m7426 =m4is_p40::m4is_o1723();
 $m4is_r6234 =$m4is_r6234;
 $m4is_v2613 ="UPDATE {$m4is_m7426
} SET `value` = '%s' WHERE id = %d AND `appname` = '%s' AND `fieldname` = '%s'; ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_m676, $m4is_h21895, $m4is_r9613, $m4is_r6234 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 
}
} get_password_reset_key($m4is_l17096);
 
}else{
 
}
}
}
}

