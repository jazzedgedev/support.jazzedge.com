<?php

/**
 * Copyright (c) 2018-2022 David J Bullock
 * Web Power and Light
*/


 class_exists('m4is_r83')||die();
  
class m4is_i651{
private $pre_render_tags =[];
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$m4is_p6841 =['et_pb_tab'];
 $this->pre_render_tags =(array)apply_filters('wpal/blocks/divi/pre_render_tags', $m4is_p6841 );
 
}
function m4is_h269(){
add_filter('do_shortcode_tag', [$this, 'm4is_w9268'], PHP_INT_MAX, 3 );
 add_action('et_builder_module_loaded', [$this, 'm4is_w38265'], PHP_INT_MAX, 2 );
 $this->m4is_j48();
 
} 
function m4is_w9268($m4is_o498, $m4is_p786, $m4is_l62046 ){
return $this->m4is_e19((array)$m4is_l62046)? $m4is_o498 : '';
 
} 
function m4is_e19(array $m4is_j296 ): bool {
$m4is_j86631 =m4is_v679::PREFIX;
 $m4is_m96240 =[];
  if(is_array($m4is_j296 )){
foreach ($m4is_j296 as $m4is_k52736 =>$m4is_v586 ){
if(strpos($m4is_k52736, "{$m4is_j86631
}_membership_levels" )!== false &&$m4is_v586 === 'on' ){
$m4is_m96240[]=(int) str_replace("{$m4is_j86631
}_membership_levels-", '', $m4is_k52736 );
 
}
}
}$m4is_u897 =['memberships' =>implode(',', array_filter($m4is_m96240 )), 'any_membership' =>isset($m4is_j296["{$m4is_j86631
}_anymembership"])&&$m4is_j296["{$m4is_j86631
}_anymembership"]=== 'on' ? 1 : 0, 'logged_in_only' =>isset($m4is_j296["{$m4is_j86631
}_loggedin"])&&$m4is_j296["{$m4is_j86631
}_loggedin"]=== 'on' ? 1 : 0, 'logged_out_only' =>isset($m4is_j296["{$m4is_j86631
}_anonymous_only"])&&$m4is_j296["{$m4is_j86631
}_anonymous_only"]=== 'on' ? 1 : 0, 'invert_results' =>isset($m4is_j296["{$m4is_j86631
}_invert_results"])&&$m4is_j296["{$m4is_j86631
}_invert_results"]=== 'on' ? 1 : 0, 'contact_ids' =>empty($m4is_j296["{$m4is_j86631
}_contact_ids"])? '' : sanitize_text_field($m4is_j296["{$m4is_j86631
}_contact_ids"]), 'tags1' =>empty($m4is_j296["{$m4is_j86631
}_access_tags"])? '' : $m4is_j296["{$m4is_j86631
}_access_tags"], 'tags2' =>empty($m4is_j296["{$m4is_j86631
}_access_tags2"])? '' : $m4is_j296["{$m4is_j86631
}_access_tags2"], 'eval' =>empty($m4is_j296["{$m4is_j86631
}_eval"])? '' : trim($m4is_j296["{$m4is_j86631
}_eval"]), 'asset_id' =>empty($m4is_j296["{$m4is_j86631
}_asset_id"])? '' : sanitize_text_field($m4is_j296["{$m4is_j86631
}_asset_id"]), ];
 if(!empty($m4is_u897['eval'])){
$m4is_a642 =['%91' =>'[', '%93' =>']' ];
 $m4is_u897['eval']=strtr($m4is_u897['eval'], $m4is_a642 );
 
}return m4is_v679::m4is_c26()->m4is_b679()->m4is_h86($m4is_u897, 'divi' );
 
}    
function m4is_j48(){
global $shortcode_tags;
 $m4is_t82560 =[];
 foreach ($shortcode_tags as $m4is_p786 =>$m4is_v2366 ){
if(is_array($m4is_v2366 )){
if($m4is_v2366[0]instanceof ET_Builder_Element ||$m4is_v2366[0]instanceof ET_Builder_Module ){
if($this->m4is_j67896($m4is_p786 )){
$m4is_t82560[$m4is_p786]=$m4is_v2366;
 remove_shortcode($m4is_p786, $m4is_v2366 );
 
}
}
}
}if(!empty($m4is_t82560 )){
foreach ($m4is_t82560 as $m4is_p786 =>$m4is_v2366){
$m4is_r64160 =$m4is_v2366[0];
 $m4is_x26467 =$m4is_v2366[1];
 add_shortcode($m4is_p786, function($m4is_l62046, $m4is_t09761, $m4is_c5764 )use ($m4is_r64160, $m4is_x26467 ){
$m4is_u6591 ='';
 if($this->m4is_e19($m4is_l62046 )){
$m4is_u6591 =$m4is_r64160->$m4is_x26467($m4is_l62046, $m4is_t09761, $m4is_c5764);
 
}return $m4is_u6591;
  
});
 
}
}
} 
function m4is_w38265($m4is_p786, $m4is_e70413 ){
if($this->m4is_j67896($m4is_p786 )){
remove_shortcode($m4is_p786, $m4is_e70413 );
 add_shortcode($m4is_p786, function($m4is_l62046, $m4is_t09761, $m4is_c5764 )use ($m4is_e70413 ){
$m4is_u6591 ='';
 if($this->m4is_e19($m4is_l62046 )){
$m4is_u6591 =$m4is_e70413['instance']->_render($m4is_l62046, $m4is_t09761, $m4is_c5764 );
 
}return $m4is_u6591;
 
});
 
}
}
function m4is_j67896($m4is_p786 ){
if(!empty($this->pre_render_tags)&&is_array($this->pre_render_tags)){
return in_array($m4is_p786, $this->pre_render_tags);
 
}else{
return false;
 
}
}
}

