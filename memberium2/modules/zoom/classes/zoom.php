<?php

/**
 * Copyright (c) 2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83')||die();
  
class m4is_i01 {
const VERSION ='1.0.0';
 const OPTION_SLUG ='wpal/zoom/settings';
 private $config =[];
 private $options =null;
 private $connected =false;
  
function init($m4is_l6970){
 define('WPAL_ZOOM_HOME_DIR', dirname(__DIR__). '/');
 $m4is_n6062 =trailingslashit(plugins_url('', dirname(__FILE__)));
 define('WPAL_ZOOM_URL', $m4is_n6062);
 $m4is_y642 =['parent_slug' =>'options-general.php', 'menu_slug' =>'wpal-zoom', 'shortcode_prefix' =>'wpal', 'I18n' =>['page_title' =>__('Zoom Settings', 'wpal_ecomm'), 'menu_title' =>__('Zoom', 'wpal_ecomm'), ]];
 $this->config =wp_parse_args($m4is_l6970, $m4is_y642);
 $this->register_wp_hooks();
 
} 
function register_wp_hooks(){
if(is_admin()){
 add_action("admin_menu", function(){
$m4is_l6970 =$this->get_config();
 $m4is_x59 =$m4is_l6970['I18n'];
 add_submenu_page($m4is_l6970['parent_slug'], $m4is_x59['page_title'], $m4is_x59['menu_title'], 'manage_options', $m4is_l6970['menu_slug'], [$this, 'zoom_settings_page']);
 
}, PHP_INT_MAX );
 
}else{
 $m4is_j67631 =$this->config['shortcode_prefix'];
 add_shortcode("{$m4is_j67631
}_zoom_event", function($m4is_l62046, $m4is_t09761, $m4is_p786 ){
$m4is_f683 =$this->frontend();
 $m4is_f683->frontend_scripts();
 return $m4is_f683->zoom_event_func($m4is_l62046, $m4is_t09761, $m4is_p786 );
 
});
 
}
} 
function zoom_settings_page(){
$this->admin()->m4is_k63($this->m4is_j498(), $this->get_config());
 
} 
function frontend(){
static $m4is_f683;
 if(is_null($m4is_f683)){
require_once __DIR__ . '/frontend.php';
 $m4is_f683 =new m4is_q631(self::VERSION );
 
}return $m4is_f683;
 
} 
function admin(){
static $m4is_x25;
 if(is_null($m4is_x25)){
require_once __DIR__ . '/admin.php';
 $m4is_x25 =new m4is_h1986(self::OPTION_SLUG, self::VERSION );
 
}return $m4is_x25;
 
} 
function api(){
static $m4is_l75018 =false;
 if(!$m4is_l75018){
require_once __DIR__ . '/api.php';
 $m4is_l9671 =$this->m4is_j498('api_key');
 $m4is_l21056 =$this->m4is_j498('api_secret');
 $m4is_l75018 =new m4is_w375($m4is_l9671, $m4is_l21056);
 
}return $m4is_l75018;
 
} 
function get_config($m4is_l9671 =false ){
if($m4is_l9671 ){
if(isset($this->config[$m4is_l9671])){
return $this->config[$m4is_l9671];
 
}else{
return false;
 
}
}else{
return $this->config;
 
}
} 
function m4is_j498($m4is_l9671 =false ){
if(is_null($this->options )){
$this->options =get_option(self::OPTION_SLUG, ['default_email' =>get_bloginfo('admin_email'), 'api_key' =>'', 'api_secret' =>'', 'connected' =>false ]);
 
}if($m4is_l9671 ){
if(isset($this->options[$m4is_l9671])){
return $this->options[$m4is_l9671];
 
}else{
return false;
 
}
}else{
return $this->options;
 
}
} private 
function __construct(){

}static 
function get_wpal_zoom_instance(){
static $m4is_t586 =false;
 if(!$m4is_t586 ){
$m4is_t586 =new self;
 
}return $m4is_t586;
 
}
} 
function m4is_i01(){
return m4is_i01::get_wpal_zoom_instance();
 
}

