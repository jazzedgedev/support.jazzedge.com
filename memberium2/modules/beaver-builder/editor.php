<?php

/**
* Copyright (c) 2018-2022 David J Bullock
* Web Power and Light
*/


 class_exists('m4is_r83')||die();
  final 
class m4is_o50786 {
protected $ext_ver ='1.0.5';
 public $slug ='beaver_builder';
 public $to_json =[];
  public $omitted_blocks =[];
  public $ns ='';
  public $prefix ='';
  public $I18n =[];
  public $access_class;
  public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->access_class =m4is_v679::m4is_c26();
 $this->prefix =$this->access_class::PREFIX;
 $this->ns =$this->access_class::NS;
 $this->m4is_h269();
 
}private 
function m4is_h269(): void {
 add_filter("{$this->ns
}/{$this->slug
}/control/config", [$this, 'm4is_h3216'], 10, 1 );
 $m4is_a95428 =$this->access_class->m4is_j16();
 $this->I18n =$m4is_a95428->m4is_p6406(false, $this->slug );
 $this->to_json['WPAL_BLOCKS_PREFIX']=$this->prefix;
 $this->to_json['WPAL_BLOCKS_KEYS_REMOVED_TEXT']=$this->I18n['keys_removed_text'];
 $this->to_json['controls']=$m4is_a95428->m4is_p43571($this->slug );
 $this->to_json['tags']=$m4is_a95428->m4is_z2906();
  $this->omitted_blocks =apply_filters("{$this->ns
}/{$this->slug
}/settings/omitted_blocks", ['col']);
 add_filter('fl_builder_register_settings_form', [$this, 'm4is_d69132'], PHP_INT_MAX, 2 );
  add_filter('fl_builder_custom_fields', [$this, 'm4is_g78245']);
  add_action('wp_enqueue_scripts', [$this, 'm4is_y53'], PHP_INT_MAX );
  
} public 
function m4is_h3216(array $m4is_l6970 ): array {
foreach ($m4is_l6970 as $m4is_r243 =>$m4is_s53067 ){
if($m4is_s53067['type']=== 'checkbox' ){
$m4is_l6970[$m4is_r243]['type']='wpal_blocks_toggle';
 $m4is_l6970[$m4is_r243]['description']='';
 
}elseif($m4is_s53067['type']=== 'SELECT2' ){
$m4is_l6970[$m4is_r243]['type']='text';
 $m4is_l6970[$m4is_r243]['class']='bb-wpal-blocks-select2';
 
}
}return $m4is_l6970;
 
} 
function m4is_g78245(array $m4is_a89 ){
$m4is_a89['wpal_blocks_toggle']=trailingslashit(__DIR__ ). 'toggle.php';
 return $m4is_a89;
 
} 
function m4is_d69132(array $m4is_m14, string $m4is_j09 ): array {
$m4is_f725 =($m4is_j09 === 'row' ||$m4is_j09 === 'col');
  if($m4is_f725){
$m4is_m14['tabs']=isset($m4is_m14['tabs'])? $m4is_m14['tabs']: [];
 $m4is_m14['tabs']['advanced']=isset($m4is_m14['tabs']['advanced'])? $m4is_m14['tabs']['advanced']: ['title' =>__('Advanced', $this->ns )];
 $m4is_m14['tabs']['advanced']['sections']=isset($m4is_m14['tabs']['advanced']['sections'])? $m4is_m14['tabs']['advanced']['sections']: [];
 $m4is_m14['tabs']['advanced']['sections']=$this->m4is_c28054($m4is_m14['tabs']['advanced']['sections'], $m4is_j09 );
  
}else{
 if($m4is_j09 === 'module_advanced'){
$m4is_m14['sections']=isset($m4is_m14['sections'])? $m4is_m14['sections']: [];
 $m4is_m14['sections']=$this->m4is_c28054($m4is_m14['sections'], $m4is_j09 );
 
}
}return $m4is_m14;
 
} 
function m4is_c28054(array $m4is_n963, string $m4is_j09): array {
$m4is_u7415 =[];
 $m4is_l9671 ='visibility';
 if(array_key_exists($m4is_l9671, $m4is_n963 )){
foreach ($m4is_n963 as $m4is_m4052 =>$m4is_x95460 ){
$m4is_u7415[$m4is_m4052]=$m4is_x95460;
 if($m4is_m4052 === $m4is_l9671 ){
$m4is_a89 =$this->m4is_i1896($m4is_j09 );
 if($m4is_a89 ){
$m4is_u7415['wpal-blocks']=$m4is_a89;
 
}
}
}
}else{
$m4is_u7415 =$m4is_n963;
 $m4is_a89 =$this->m4is_i1896($m4is_j09 );
 if($m4is_a89 ){
$m4is_u7415['wpal-blocks']=$m4is_a89;
 
}
}return $m4is_u7415;
 
} 
function m4is_i1896(string $m4is_j09 ){
$m4is_w4309 =in_array($m4is_j09, $this->omitted_blocks )? false : $this->to_json['controls'];
  if(!$m4is_w4309 ||empty($m4is_w4309 )){
return;
 
}$m4is_h248 =['title' =>$this->I18n['settings_title'], 'fields' =>[]];
 foreach ($m4is_w4309 as $m4is_r243 =>$m4is_s53067 ){
$m4is_j0361 =isset($m4is_s53067['type'])? $m4is_s53067['type']: false;
 $m4is_k52736 =isset($m4is_s53067['name'])? $m4is_s53067['name']: false;
 if($m4is_j0361 &&$m4is_k52736 ){
$m4is_z60 =['type' =>$m4is_j0361, 'label' =>$m4is_s53067['label']];
 $conditional_settings =['class', 'default', 'description', 'help', 'multi-select', 'options', 'placeholder', 'rows', ];
 foreach ($conditional_settings as $m4is_m4052 =>$m4is_d87521 ){
if(isset($m4is_s53067[$m4is_d87521])){
$m4is_z60[$m4is_d87521]=$m4is_s53067[$m4is_d87521];
 
}
}$m4is_z60 =apply_filters("{$this->ns
}/{$this->slug
}/editor/control/args", $m4is_z60, $m4is_k52736, $m4is_j0361, $m4is_j09 );
 $m4is_h248['fields'][$m4is_k52736]=$m4is_z60;
 
}
}return $m4is_h248;
 
} 
function m4is_y53(){
if(!FLBuilderModel::is_builder_active()){
return;
 
}$m4is_b691 ='wpal-blocks-bb';
 $m4is_n6062 =plugin_dir_url(__FILE__ );
 $m4is_x23156 =['jquery', "{$m4is_b691
}_s2js"];
 $m4is_m51 =$m4is_b691 . '-editor-js';
 $m4is_r635 =$m4is_b691 . '-editor-css';
 $this->access_class->m4is_j16()->m4is_d7803(false, $m4is_b691 );
 wp_enqueue_style("{$m4is_b691
}_s2css");
 wp_enqueue_script("{$m4is_b691
}_s2js");
 wp_register_script($m4is_m51, "{$m4is_n6062
}editor.js", $m4is_x23156, $this->ext_ver, true );
 wp_register_style($m4is_r635, "{$m4is_n6062
}editor.css", false, $this->ext_ver, 'all' );
 wp_enqueue_script($m4is_m51 );
 wp_enqueue_style($m4is_r635 );
 wp_localize_script($m4is_m51, 'wpalbb_params', $this->to_json );
 
}
}

