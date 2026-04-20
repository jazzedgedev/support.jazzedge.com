<?php

/**
 * Copyright (c) 2018-2022 David J Bullock
 * Web Power and Light
*/


 class_exists('m4is_r83')||die();
  
class m4is_t26 {
public $slug ='divi';
  public $script_version ='1.0.7';
  public $to_json =[];
  public $omitted_blocks =[];
  public $ns ='';
  public $prefix ='';
  public $I18n ='';
  public $access_class;
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->access_class =m4is_v679::m4is_c26();
 $this->prefix =$this->access_class::PREFIX;
 $this->ns =$this->access_class::NS;
 
}
function m4is_h269(){
add_filter("{$this->ns
}/{$this->slug
}/control/config", [$this, 'm4is_h4925'], 10, 1 );
 $m4is_a95428 =$this->access_class->m4is_j16();
 $this->I18n =$m4is_a95428->m4is_p6406(false, $this->slug );
 $this->to_json['WPAL_BLOCKS_SETTINGS_TITLE']=$this->I18n['settings_title'];
 $this->to_json['WPAL_BLOCKS_PREFIX']=$this->prefix;
 $this->to_json['WPAL_BLOCKS_KEYS_REMOVED_TEXT']=$this->I18n['keys_removed_text'];
 $this->to_json['tags']=$m4is_a95428->m4is_z2906();
  $this->to_json['controls']=$m4is_a95428->m4is_p43571($this->slug );
  $this->omitted_blocks =apply_filters("{$this->ns
}/{$this->slug
}/settings/omitted_blocks", ['et_pb_column', 'et_pb_column_inner']);
  add_action('et_builder_ready', [$this, 'm4is_v68'], 9999 );
  add_action(is_admin()? 'admin_enqueue_scripts' : 'wp_enqueue_scripts', [$this, 'm4is_y53'], 9999);
  
} 
function m4is_v68(){
global $shortcode_tags;
 $m4is_t82560 =[];
 foreach ($shortcode_tags as $m4is_p786 =>$m4is_v2366 ){
if(is_array($m4is_v2366 )){
if($m4is_v2366[0]instanceof ET_Builder_Element ||$m4is_v2366[0]instanceof ET_Builder_Module ){
$m4is_t82560[$m4is_p786]=$m4is_v2366;
 remove_shortcode($m4is_p786, $m4is_v2366);
 
}
}
}if(!empty($m4is_t82560)){
foreach ($m4is_t82560 as $m4is_p786 =>$m4is_v2366 ){
$m4is_r64160 =$m4is_v2366[0];
 $m4is_x26467 =$m4is_v2366[1];
 $m4is_r64160->settings_modal_toggles['custom_css']['toggles']['wpal-blocks']=['title' =>$this->I18n['settings_title'], 'priority' =>100];
 if(!isset($m4is_r64160->fields_unprocessed["{$this->prefix
}_anymembership"])){
$m4is_w4309 =in_array($m4is_p786, $this->omitted_blocks)? []: $this->to_json['controls'];
 if(is_array($m4is_w4309)&&!empty($m4is_w4309)){
$m4is_r64160->fields_unprocessed =array_merge($m4is_r64160->fields_unprocessed, $m4is_w4309);
 
}
}add_shortcode($m4is_p786, function($m4is_l62046, $m4is_t09761, $m4is_c5764)use ($m4is_r64160, $m4is_x26467){
return $m4is_r64160->$m4is_x26467($m4is_l62046, $m4is_t09761, $m4is_c5764 );
 
});
 
}
}
} 
function m4is_h4925(array $m4is_l6970 ): array {
$m4is_w95 =[];
 if(is_array($m4is_l6970)&&!empty($m4is_l6970)){
foreach($m4is_l6970 as $m4is_f3968 =>$m4is_q523 ){
$m4is_j0361 =!empty($m4is_q523['type'])? $m4is_q523['type']: false;
 $m4is_k52736 =!empty($m4is_q523['name'])? $m4is_q523['name']: false;
 $m4is_o53628 =!empty($m4is_q523['description'])? $m4is_q523['description']: false;
 $m4is_g3669 =!empty($m4is_q523['sanitize'])? $m4is_q523['sanitize']: false;
 if($m4is_j0361 &&$m4is_k52736 ){
switch ($m4is_j0361){
case 'checkbox': $m4is_w95[$m4is_k52736]=['wpald' =>'toggle', 'wpald_level' =>empty($m4is_q523['level'])? '' : $m4is_q523['level'], 'wpald_toggles' =>isset($m4is_q523['toggles'])&&is_array($m4is_q523['toggles'])? $m4is_q523['toggles']: false, 'type' =>'yes_no_button', 'label' =>$m4is_q523['label'], 'default' =>isset($m4is_q523['default'])&&(int) $m4is_q523['default']> 0 ? 'on' : 'off', 'options' =>['off' =>empty($m4is_q523['label_off'])? _x('Off', 'divi', $this->ns ): $m4is_q523['label_off'], 'on' =>empty($m4is_q523['label_on'])? _x('On', 'divi', $this->ns ): $m4is_q523['label_on'], ], ];
 break;
 case 'textarea': $m4is_w95[$m4is_k52736]=['wpald' =>'textarea', 'type' =>'text', 'label' =>$m4is_q523['label'], 'default' =>'' ];
 break;
 case 'text': $m4is_w95[$m4is_k52736]=['wpald' =>'text', 'type' =>'text', 'label' =>$m4is_q523['label'], 'default' =>'' ];
 break;
 case 'SELECT2': $m4is_w95[$m4is_k52736]=['wpald' =>'select2', 'type' =>'text', 'label' =>$m4is_q523['label']];
 break;
 default: break;
 
}if($m4is_o53628 ){
$m4is_w95[$m4is_k52736]['description']=$m4is_o53628;
 
}if($m4is_g3669 ){
$m4is_w95[$m4is_k52736]['sanitize']=$m4is_g3669;
 
}$m4is_w95[$m4is_k52736]['additional_att']=empty($m4is_q523['additional_att'])? $m4is_k52736 : $m4is_q523['additional_att'];
 $m4is_w95[$m4is_k52736]['option_category']=empty($m4is_q523['option_category'])? 'configuration' : $m4is_q523['option_category'];
 $m4is_w95[$m4is_k52736]['tab_slug']=empty($m4is_q523['tab_slug'])? 'custom_css' : $m4is_q523['tab_slug'];
 $m4is_w95[$m4is_k52736]['toggle_slug']=empty($m4is_q523['toggle_slug'])? 'wpal-blocks' : $m4is_q523['toggle_slug'];
 
}
}
}return $m4is_w95;
 
} 
function m4is_y53(){
$m4is_b691 ='wpal-blocks-divi-editor';
 $m4is_u0789 =plugin_dir_url(__FILE__ );
 $m4is_h06829 =$m4is_b691 . '_main';
 $m4is_u3681 ='select2css_divi';
 $this->access_class->m4is_j16()->m4is_d7803(false, $m4is_b691 );
 wp_register_style($m4is_u3681, $m4is_u0789 . 'select2_divi.css', false, $this->script_version, 'all' );
 wp_register_script($m4is_h06829, $m4is_u0789 . 'editor.js', ['jquery'], $this->script_version, true);
 wp_enqueue_style($m4is_u3681 );
 wp_enqueue_script("{$m4is_b691
}_s2js" );
 wp_enqueue_script($m4is_h06829 );
 wp_localize_script($m4is_h06829, 'wpald_params', $this->to_json );
 
}
}

