<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 new m4is_h8452();
 final 
class m4is_h8452 {
private $m4is_r1546;
 private $m4is_k41;
 private $m4is_x74;
 private $m4is_e0213;
 private $m4is_e1964;
 private $m4is_a9685;
 private $m4is_s926;
 private $m4is_i8236;
 
function __construct(){
$this->m4is_h269();
 $this->m4is_o719();
 
}private 
function m4is_h269(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_e0213 ='settings';
 $this->m4is_i8236 =m4is_s52::m4is_b12067(['unlimited']);
 $this->m4is_a9685 =m4is_s52::m4is_b12067(['trial']);
 $this->m4is_k41 =m4is_s52::m4is_b12067(['domain', 'unlimited', 'icc', 'qatest']);
 $this->m4is_e1964 =['style' =>'text-align:right;width:80px;' ];
 $this->m4is_x74 =['min' =>0 ];
 $this->m4is_s926 =['units' =>'seconds' ];
 
}private 
function m4is_o719(){
echo '<form method="POST" action="">';
 wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memberium_options_nonce' );
 $this->m4is_q689();
 $this->m4is_v29864();
 $this->m4is_m901();
 $this->m4is_p96581();
 $this->m4is_b03866();
 echo '</ul>';
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 echo '</form>';
 
}private 
function m4is_q689(){
$m4is_w0428 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'disable_login_sync', 0 );
 $m4is_z30578 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'max_contact_age', 0 );
 $m4is_d29436 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'max_affiliate_age', 0 );
 $m4is_k73586 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'session_timeout', 0 );
 echo '<ul>';
 echo '<h3>Cache Tuning</h3>';
 m4is_h65::m4is_y648('Disable Login Sync/Actions', 'disable_login_sync', 0, $m4is_w0428 );
 m4is_h65::m4is_y648('Persistent Login', 'persistent_login', 0000, m4is_r83::m4is_c26()->m4is_j498('settings', 'persistent_login', 0));
 m4is_h65::m4is_w6712('Maximum Contact Cache Age', 'max_contact_age', $m4is_z30578, ['help_id' =>1189, $this->m4is_x74, $this->m4is_e1964, $this->m4is_s926 ]);
 m4is_h65::m4is_w6712('Maximum Affiliate Cache Age', 'max_affiliate_age', $m4is_d29436, ['help_id' =>21913, $this->m4is_x74, $this->m4is_e1964, $this->m4is_s926, ]);
  echo '<hr>';
 
}private 
function m4is_v29864(){
$m4is_v3654 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'async_limit', 0 );
 $m4is_t8704 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'async_tag', 0 );
 echo '<h3>Background Contact Sync</h3>';
 $m4is_y66291 =['min' =>0, 'max' =>1000, 'help_id' =>0, 'disabled' =>(int) !$this->m4is_i8236, $this->m4is_e1964, $this->m4is_s926 ];
 m4is_h65::m4is_w6712('Sync Size', 'async_limit', $m4is_v3654, $m4is_y66291 );
 $m4is_y66291 =['help_id' =>0,  ];
 m4is_h65::m4is_h70259('Sync Tag', 'async_tag', $m4is_t8704, 'taglistdropdown', $m4is_y66291 );
 echo '<hr>';
 
}private 
function m4is_m901(){
$m4is_o8936 =defined('I2SDK_VERSION' )&&(I2SDK_VERSION < 4 );
 $m4is_w97538 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'fast_user_list', 0 );
 $m4is_f089 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'plaintext_db', 0 );
  $m4is_p65689 =true;
 echo '<h3>Misc Settings</h3>';
 if($this->m4is_i8236 ){
m4is_h65::m4is_y648('Fast User List (Pro+)', 'fast_user_list', 22093, $m4is_w97538 );
 
}m4is_h65::m4is_y648('Remove Accented Characters', 'plaintext_db', 13296, $m4is_f089 );
  echo '<hr>';
 
}private 
function m4is_p96581(){
$m4is_x506 =(bool) $this->m4is_r1546->m4is_j498('settings', 'sync_affiliate', 0 );
 $m4is_p283 =(bool) $this->m4is_r1546->m4is_j498('settings', 'sync_tag_details', 0 );
 $m4is_u87692 =(bool) $this->m4is_r1546->m4is_j498('settings', 'sync_ecommerce', 0 );
 $m4is_c5710 =(bool) $this->m4is_r1546->m4is_j498('settings', 'sync_meta_updates', 0 );
 echo '<h3>Login-Time Synchronization</h3>';
 echo '<p>Please review the online documentation, or contact support <em>before</em> activating these features.</p>';
 m4is_h65::m4is_y648('Synchronize Affiliate Records', 'sync_affiliate', 2686, $m4is_x506 );
 m4is_h65::m4is_y648('Synchronize Tag Dates', 'sync_tag_details', 4038, $m4is_p283 );
 m4is_h65::m4is_y648('Synchronize eCommerce Records', 'sync_ecommerce', 2689, $m4is_u87692 );
 m4is_h65::m4is_y648('Sync Meta Updates', 'sync_meta_updates', 19152, $m4is_c5710 );
 echo '<hr>';
 
}private 
function m4is_b03866(){
$m4is_a9610 =(bool) $this->m4is_r1546->m4is_j498('settings', 'microcache_compat_session', 0 );
 $m4is_h86253 =(bool) $this->m4is_r1546->m4is_j498('settings', 'db_sessions', 0 );
  echo '<hr>';
 
}
}

