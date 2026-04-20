<?php

/**
 * Copyright (c) 2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83')||die();
  
class m4is_h1986 {
private $key;
 private $version;
  
function __construct($m4is_l9671, $m4is_a6814 ){
$this->key =$m4is_l9671;
 $this->version =$m4is_a6814;
 add_filter('memberium/modules/active/names', [$this, 'm4is_f809'], 10, 1);
 
}
function m4is_f809($m4is_y634){
return array_merge($m4is_y634, ['Zoom for Memberium Support' ]);
 
} 
function m4is_w9064(){
$m4is_k72 =$this->version;
 $m4is_w43796 =WPAL_ZOOM_URL . 'assets/';
 wp_enqueue_style("wpal-zoom-admin-css", "{$m4is_w43796
}wpal-zoom-admin.css", [], $m4is_k72, 'all');
 
} 
function m4is_k63($m4is_r37596, $m4is_l6970){
$m4is_a27648 =$this->key;
 $m4is_d3012 =$m4is_l6970['menu_slug'];
 if($_SERVER['REQUEST_METHOD']== 'POST'){
if(isset($_POST["{$m4is_d3012
}-submit"])){
 if(isset($_POST["_{$m4is_a27648
}_name"])){
if(wp_verify_nonce($_POST["_{$m4is_a27648
}_name"], $m4is_a27648)){
$m4is_r37596 =$this->m4is_h17923($_POST, $m4is_r37596 );
 
}
}
}
}$this->m4is_w9064();
 $m4is_x59 =$m4is_l6970['I18n'];
 $m4is_l9671 =$m4is_r37596['api_key'];
 $m4is_l21056 =$m4is_r37596['api_secret'];
 require_once WPAL_ZOOM_HOME_DIR . 'templates/auth-screen.php';
 
}
function m4is_h17923($m4is_l91805, $m4is_r37596){
$m4is_e0213 =['api_key', 'api_secret'];
 $m4is_b63 =false;
 foreach ($m4is_e0213 as $m4is_k52736){
$m4is_e53897 =$m4is_r37596[$m4is_k52736];
 $m4is_b1586 =isset($m4is_l91805[$m4is_k52736])? esc_attr($m4is_l91805[$m4is_k52736]): '';
 if($m4is_b1586 != $m4is_e53897 ){
$m4is_r37596[$m4is_k52736]=$m4is_b1586;
 $m4is_b63 =true;
 
}
}if($m4is_b63){
update_option($this->key, $m4is_r37596);
 
}return $m4is_r37596;
 
}
}

