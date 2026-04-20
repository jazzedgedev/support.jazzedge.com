<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_b5630::m4is_c26();
 final 
class m4is_b5630 {
private $m4is_r1546;
 private $m4is_e0213;
 public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_o719();
 
}
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_e0213 ='settings';
 
}private 
function m4is_o719(){
echo <<<HTMLBLOCK
			<form method="POST" action="">
		HTMLBLOCK;
 wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memberium_options_nonce' );
 $this->m4is_s841();
 $this->m4is_w83661();
 $this->m4is_q286();
 $this->m4is_w136();
 $this->m4is_x85936();
 echo <<<HTMLBLOCK
			<p><input type="submit" value="Update" class="button-primary"></p>
			</form>
		HTMLBLOCK;
 $this->m4is_w15();
 
}private 
function m4is_s841(){
$m4is_p2187 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'allow_wpadmin', 'manage_options' );
 $m4is_j61 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'attachment_pages', 0 );
 $m4is_w57 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'protect_feeds', 0 );
 $m4is_y219 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'disable_xframe', 0 );
 $m4is_l186 =(string) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'allow_wpadmin_dashboard', 'manage_options' );
 $m4is_a86 =(string) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'allow_wpadmin_titlebar', 'manage_options' );
 echo '<h3>Site Restrictions</h3>';
 echo '<ul>';
 if(empty($m4is_l186 )){
m4is_h65::m4is_y648('Allow Access to WP Dashboard', 'allow_wpadmin', 1192, $m4is_p2187 );
 
}m4is_h65::m4is_h70259('Allow WP Admin Dashboard', 'allow_wpadmin_dashboard', $m4is_l186, 'capabilitylistdropdown', ['style' =>'width:400px !important;', 'multiple' =>'multiple', 'help_id' =>1192]);
 m4is_h65::m4is_h70259('Show Titlebar', 'allow_wpadmin_titlebar', $m4is_a86, 'capabilitylistdropdown', ['style' =>'width:400px !important;', 'multiple' =>'multiple', 'help_id' =>21894]);
 m4is_h65::m4is_y648('Disable Attachment Pages', 'attachment_pages', 17276, $m4is_j61 );
 m4is_h65::m4is_y648('Disable RSS Feeds', 'protect_feeds', 1214, $m4is_w57 );
 m4is_h65::m4is_y648('Disable Clickjacking Protection', 'disable_xframe', 26493, $m4is_y219 );
 echo '</ul>';
 echo '<hr>';
 
}private 
function m4is_w83661(){
$m4is_x54 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'show_post_columns', true );
 echo '<h3>Page Handling</h3>';
 echo '<ul>';
 m4is_h65::m4is_y648('Show All Columns in Post List', 'show_post_columns', 13294, $m4is_x54 );
 echo '</ul>';
 echo '<hr>';
 
}private 
function m4is_q286(){
$m4is_w63962 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'registration_url', 0 );
 $m4is_r09164 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'new_user_registration_tag', 0 );
 $m4is_p9540 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'sync_new_wp_users', 0 );
 $m4is_r2656 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'extended_reg_fields', 0 );
 echo '<h3>New User Registration</h3>';
 echo '<ul>';
 m4is_h65::m4is_h70259('Registration Page', 'registration_url', $m4is_w63962, 'pagelistdropdown', ['help_id' =>1211, 'style' =>'width:400px !important;']);
 m4is_h65::m4is_h70259('New User Registration Tag', 'new_user_registration_tag', $m4is_r09164, 'taglistdropdown', ['help_id' =>1181, 'style' =>'width:400px !important;']);
 m4is_h65::m4is_y648('Sync New Registrations to Keap', 'sync_new_wp_users', 21903, $m4is_p9540 );
 m4is_h65::m4is_y648('Extended Registration Fields', 'extended_reg_fields', 21908, $m4is_r2656 );
 echo '</ul>';
 
}private 
function m4is_w15(){
$m4is_t39480 =m4is_s6729::m4is_c26()->m4is_g641();
 echo '<script>';
 echo 'var capabilitylist = ', $m4is_t39480, ';';
 echo '</script>';
 
}private 
function m4is_w136(){
$m4is_l60 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'recaptcha_v2', 0 );
 $m4is_q76201 =(string) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'recaptcha_v2_site_key', '' );
 $m4is_j2467 =(string) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'recaptcha_v2_secret_key', '' );
 echo '<hr />';
 echo '<h3>reCAPTCHA v2</h3>';
 echo '<ul>';
 m4is_h65::m4is_y648('Enable reCAPTCHA v2', 'recaptcha_v2', 0, $m4is_l60 );
 m4is_h65::m4is_i014('Site Key', 'recaptcha_v2_site_key', $m4is_q76201, ['help_id' =>0, 'style' =>'text-align:left;width:400px !important' ]);
 m4is_h65::m4is_i014('Secret Key', 'recaptcha_v2_secret_key', $m4is_j2467, ['help_id' =>0, 'style' =>'text-align:left;width:400px !important;' ]);
 echo '</ul>';
 
}private 
function m4is_x85936(){
echo '<hr>';
 echo '<h3>Secure Debug</h3>';
 $m4is_y66291 =['label' =>'Debug IP Addresses', 'value' =>(string) $this->m4is_r1546->m4is_j498('settings', 'debug_ip', '' ), 'style' =>'width:375px;', 'help_id' =>0000, 'placeholder' =>'Comma separated list of IP addresses', 'help_text' =>null,  ];
 m4is_h65::m4is_a9861('debug_ip', $m4is_y66291 );
 echo '<p style="margin-left: 300px;">Current IP:  ', m4is_a01587::m4is_y342(), '</p>';
 
}
}

