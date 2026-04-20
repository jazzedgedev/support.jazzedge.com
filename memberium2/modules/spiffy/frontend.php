<?php

/**
 * Copyright (c) 2022-2023 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_j766 {
private $m4is_r1546;
 
function __construct(){
$this->m4is_h269();
 $this->m4is_n01();
 
}private 
function m4is_h269(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
}private 
function m4is_n01(){
$this->m4is_z584();
 add_action('memberium/shortcodes/add', [$this, 'm4is_z584'], 10, 0 );
 add_action('memberium/shortcodes/remove', [$this, 'm4is_a67516'], 10, 0 );
 if(!empty($_POST['memb_form_type'])){
add_action('template_redirect', [$this, 'm4is_c12683'], 1 );
 
}
}private 
function m4is_s59124(){
$m4is_o9361['standard']=['memb_spiffy_login' =>'m4is_w4780', 'memb_spiffy_debug' =>'m4is_h56972', ];
 return $m4is_o9361;
 
}
function m4is_c12683(){
$m4is_i60 =strtolower($_POST['memb_form_type']);
 $m4is_i10 =['memberium/spiffy_login_button' =>'m4is_p46852'];
 if(array_key_exists($m4is_i60, $m4is_i10 )){
$m4is_c661 =$m4is_i10[$m4is_i60];
 $this->$m4is_c661();
 
}
}
function m4is_z584(){
$m4is_l9321 =$this->m4is_s59124();
  if(isset($m4is_l9321['standard'])&&is_array($m4is_l9321['standard'])){
foreach($m4is_l9321['standard']as $m4is_p786 =>$m4is_j86631 ){
add_shortcode($m4is_p786, [$this, $m4is_j86631]);
 
}
}
}
function m4is_a67516(){
$m4is_l9321 =$this->m4is_s59124();
 if(isset($m4is_l9321['standard'])&&is_array($m4is_l9321['standard'])){
foreach($m4is_l9321['standard']as $m4is_p786 =>$m4is_j86631 ){
remove_shortcode($m4is_p786 );
 
}
}
}   
function m4is_w4780($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(!m4is_s52::m4is_w74()){
return '';
 
}m4is_j586::m4is_x7134();
 if(!is_user_logged_in()){
return '';
 
}$m4is_z9628 =$this->m4is_r1546->m4is_j498('settings', 'spiffy_subdomain' );
 $m4is_y642 =['button_text' =>'Get Spiffy Login', 'button_url' =>'', 'css_button_class' =>'memb_spiffy_login_button', 'css_button_id' =>'memb_spiffy_login_button', 'css_button_name' =>'memb_spiffy_login_button', 'css_button_style' =>'', 'css_class' =>'memb_spiffy_login_form', 'css_id' =>'memb_spiffy_login_form', 'css_message_class' =>'memb_spiffy_login_message', 'css_message_id' =>'memb_spiffy_login_message', 'css_message_style' =>'', 'css_name' =>'memb_spiffy_login_form', 'css_style' =>'', 'email' =>wp_get_current_user()->user_email, 'message' =>'Please check your email for your Spiffy login link.', 'style' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}if(empty($m4is_z9628 )&&$this->m4is_r1546->m4is_v461()){
return "<strong style='color:red;'>Admin Notice:  The [{$m4is_v3458
}] shortcode has been disabled due to missing configuration.</strong>";
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_u076 =['email' =>$m4is_l62046['email'], 'message' =>$m4is_l62046['message'], ];
 $m4is_u076 =base64_encode(serialize($m4is_u076 ));
 $m4is_o31859 =$this->m4is_r1546->m4is_r626($m4is_u076 );
 $m4is_a27648 =wp_nonce_field('memb_spiffy_login_' . $m4is_l62046['css_id'], '_wpnonce', true, false );
 $m4is_i46 ='';
 $m4is_a173 =$this->m4is_r1546->m4is_d3087('memb_spiffy_login' );
 if(!empty($m4is_a173 )){
$m4is_i46 .= "<div id='{$m4is_l62046['css_message_id']
}' class='{$m4is_l62046['css_message_class']
}' style='{$m4is_l62046['css_message_style']
}'>{$m4is_a173
}</div>";
 
}else{
$m4is_i46 .= "<form name='{$m4is_l62046['css_name']
}' id='{$m4is_l62046['css_id']
}' method='post' action=''>";
 $m4is_i46 .= "<input type='hidden' name='memb_form_type' value='memberium/spiffy_login_button'>";
 $m4is_i46 .= "<input type='hidden' name='form_id' value='{$m4is_l62046['css_id']
}'>";
 $m4is_i46 .= "<input type='hidden' name='parameters' value='{$m4is_u076
}'>";
 $m4is_i46 .= "<input type='hidden' name='signature' value='{$m4is_o31859
}'>";
 $m4is_i46 .= $m4is_a27648;
 if(empty($m4is_l62046['button_url'])){
$m4is_i46 .= "<input type='submit' style='{$m4is_l62046['css_button_style']
}' id='{$m4is_l62046['css_button_id']
}' value='{$m4is_l62046['button_text']
}'>";
 
}else{
$m4is_i46 .= "<input type='image' src='{$m4is_l62046['button_url']
}' class='{$m4is_l62046['css_class']
}' id='{$m4is_l62046['css_button_id']
}' >";
 
}$m4is_i46 .= "</form>";
 
}return $m4is_i46;
 
}
function m4is_h56972($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(!m4is_s52::m4is_w74()){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_z9628 =$this->m4is_r1546->m4is_j498('settings', 'spiffy_subdomain' );
 $m4is_m60531 =$this->m4is_r1546->m4is_j498('settings', 'spiffy_api_key' );
 if(current_user_can('manage_options' )){

} return '';
 
}   
function m4is_p46852(){
if(!m4is_s52::m4is_f27()){
return;
 
}m4is_j586::m4is_x7134();
 if(!wp_verify_nonce($_POST['_wpnonce'], "memb_spiffy_login_{$_POST['form_id']
}")){
wp_die(_x('Security Check Failed - Nonce Validation Error', 'memb_spiffy_login', 'memberium'));
 exit;
 
}if(!$this->m4is_r1546->m4is_f1072($_POST['signature'], $_POST['parameters'])){
wp_die(_x('Security Check Failed - Signature Validation Error', 'memb_spiffy_login', 'memberium'));
 exit;
 
}$m4is_b6896 =$this->m4is_r1546->m4is_j498('settings', 'spiffy_subdomain');
 $m4is_u076 =unserialize(base64_decode($_POST['parameters']));
  $m4is_n6062 ='https://api.spiffy.co/customer-portal/auth';
 $m4is_y66291 =['headers' =>['x-subdomain' =>$m4is_b6896, ], 'body' =>['email' =>$m4is_u076['email'], ], 'sslverify' =>false, ];
 $m4is_u6591 =wp_remote_post($m4is_n6062, $m4is_y66291);
 $this->m4is_r1546->m4is_l53('memb_spiffy_login', $m4is_u076['message']);
 
}
}

