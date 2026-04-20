<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 new m4is_y52476();
 final 
class m4is_y52476 {
private $m4is_r1546;
 private $m4is_e0213;
 
function __construct(){
$this->m4is_h269();
 $this->m4is_o719();
 
}private 
function m4is_h269(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_e0213 ='settings';
 
}private 
function m4is_o719(){
echo '<form method="POST" action="">';
 echo '<ul>';
 wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memberium_options_nonce' );
 $this->m4is_w83661();
 echo '</ul>';
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 echo '</form>';
 
}private 
function m4is_w83661(){
$m4is_q376 =[0 =>'No action', 1 =>'Disable Automatic Paragraphs', 2 =>'Delay Automatic Paragraphs', ];
 $m4is_f81662 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'dynamic_menus', 0 );
 $m4is_v6012 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'two_pass_shortcode_filter', 0 );
 $m4is_m468 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'multi_language', 0 );
 $m4is_m728 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'cache_flush', 0 );
 $m4is_y928 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'cache_bust', 0 );
 $m4is_x27 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'wp_autop', 0 );
  echo '<h3>Page Handling</h3>';
 m4is_h65::m4is_y648('Personal Menus', 'dynamic_menus', 11934, $m4is_f81662 );
 m4is_h65::m4is_z30162('Automatic Paragraphs', 'wp_autop', $m4is_x27, $m4is_q376, ['help_id' =>21886 ]);
 m4is_h65::m4is_y648('Two Pass Shortcode Handling', 'two_pass_shortcode_filter', 8227, $m4is_v6012 );
 m4is_h65::m4is_y648('Multi-Language Support', 'multi_language', 14684, $m4is_m468 );
 m4is_h65::m4is_y648('Force Rewrite Cache Flush', 'cache_flush', 9636, $m4is_m728 );
 m4is_h65::m4is_y648('Discourage Browser Caching', 'cache_bust', 13292, $m4is_y928 );
  
}
}

