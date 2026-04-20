<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 
class m4is_v628 {
private $conditions;
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
 if(is_admin()){
 add_filter('memberium/modules/active/names', [$this, 'm4is_f6528'], 10, 1 );
 
}else{
 $this->m4is_d4861();
 
} if(function_exists('oxygen_vsb_register_condition' )){
 $this->m4is_n64();
 
}
} private 
function m4is_d4861(){
add_action('wp', [$this, 'm4is_j6970'], 20 );
 add_filter('do_shortcode_tag', [$this, 'm4is_m35840'], PHP_INT_MAX, 4 );
 add_filter('memberium/modules/active/names', [$this, 'm4is_f6528'], 10, 1 );
 
} 
function m4is_f6528(array $m4is_y634 =[]){
 return array_merge($m4is_y634, ['Oxygen Block Builder for Memberium']);
 
}
function m4is_j6970(){
$this->safe_tags =apply_filters('memberium/oxygen/tags', $this->safe_tags );
 
}
function m4is_m35840($m4is_o498, $m4is_p786, $m4is_a394, $m4is_p125 ){
$m4is_h719 =(strpos($m4is_p786, 'ct_section' )=== 0 );
 if(!$m4is_h719 &&in_array($m4is_p786, $this->safe_tags )){
$m4is_h719 =true;
 
}return $m4is_h719 ? do_shortcode($m4is_o498 ): $m4is_o498;
 
}
function m4is_n64(){
$m4is_j09 ='oxygen';
 $m4is_q62 =m4is_v679::m4is_c26();
 $m4is_j67631 =$m4is_q62::PREFIX;
 $m4is_x59 =$m4is_q62->m4is_j16()->m4is_p6406(false, $m4is_j09 );
 $m4is_y79 =$m4is_q62->m4is_j16()->m4is_p956($m4is_j09 );
 $m4is_q1852 =$m4is_x59['settings_title' ];
 $m4is_w71698 =['includes', "doesn't include", '==', '!=' ];
 $m4is_q61487 =['includes' ];
  if(!array_key_exists('memberships', $m4is_y79 )){
$m4is_m96240 =$m4is_q62->m4is_j16()->m4is_i5610();
 $m4is_m96240 =isset($m4is_m96240 )&&is_array($m4is_m96240 )? $m4is_m96240 : false;
 if($m4is_m96240 ){
$this->any_membership_text =sprintf(__("Any %s" ), $m4is_x59['membership_levels'], 'memberium' );
 $m4is_v586 =['options' =>[$this->any_membership_text]];
 foreach ($m4is_m96240 as $m4is_d07693 =>$m4is_w64 ){
$m4is_v586['options'][$m4is_d07693]=stripslashes($m4is_w64['name']). "({$m4is_d07693
})";
 
}$m4is_k52736 =$m4is_x59['membership_levels'];
 $m4is_b28679 ='m4is_c382';
 oxygen_vsb_register_condition($m4is_k52736, $m4is_v586, $m4is_q61487, $m4is_b28679, $m4is_q1852 );
 
}
} if(!array_key_exists('tags1', $m4is_y79 )){
$m4is_a3261 =m4is_k865::m4is_b94([], $m4is_j09 );
 if(!empty($m4is_a3261 )){
$m4is_k52736 =sprintf(__("Require %s", 'memberium' ), $m4is_x59['key_name']);
 $m4is_v586 =['options' =>$m4is_a3261 ];
 $m4is_b28679 ='m4is_d66208';
 oxygen_vsb_register_condition($m4is_k52736, $m4is_v586, $m4is_w71698, $m4is_b28679, $m4is_q1852 );
 
}
} if(!array_key_exists('asset_id', $m4is_y79 )){
$m4is_k52736 =$m4is_x59['asset_id'];
 $m4is_b28679 ='m4is_i9075';
 $this->conditions[$m4is_k52736]="{$m4is_j67631
}_asset_id";
 $m4is_v586 =['custom' =>true];
 oxygen_vsb_register_condition($m4is_k52736, $m4is_v586, $m4is_q61487, $m4is_b28679, $m4is_q1852);
 
}
} 
function m4is_w0842($m4is_x39 ){
$m4is_y642 =['any_membership' =>0, 'asset_id' =>'', 'contact_ids' =>'', 'eval' =>'', 'invert_results' =>0, 'logged_in_only' =>0, 'logged_out_only' =>0, 'memberships' =>'', 'tags1' =>'', 'tags2' =>'' ];
 $m4is_e05491 =wp_parse_args($m4is_x39, $m4is_y642 );
 return m4is_v679::m4is_c26()->m4is_b679()->m4is_h86($m4is_e05491, 'oxygen' );
 
} 
function m4is_d86390($m4is_v586 ){
$m4is_x39 =array_filter(explode('(', $m4is_v586 ));
 return (int) end($m4is_x39 );
 
}private $safe_tags =['ct_code_block', 'ct_headlines', 'ct_link_button', 'ct_link', 'ct_text_block', 'oxy_rich_text', ];
 public $any_membership_text;
  
}    
function m4is_c382($m4is_v586, $m4is_q61487){
$m4is_u96 =m4is_v628::m4is_c26();
 $m4is_e0213 =[];
 $m4is_g6471 =$m4is_u96->any_membership_text;
 if($m4is_v586 === $m4is_g6471 ){
$m4is_e0213['any_membership']=1;
 
}else{
$m4is_e0213['memberships']=$m4is_u96->m4is_d86390($m4is_v586);
 
}return $m4is_u96->m4is_w0842($m4is_e0213);
 
} 
function m4is_d66208($m4is_v586, $m4is_q61487){
$m4is_u96 =m4is_v628::m4is_c26();
 $m4is_r86041 =in_array($m4is_q61487, ['!=', "doesn't include"])? '-' : '';
 $m4is_d07693 =$m4is_u96->m4is_d86390($m4is_v586);
 $m4is_e0213 =['tags1' =>$m4is_r86041 . $m4is_d07693];
 return $m4is_u96->m4is_w0842($m4is_e0213);
 
} 
function m4is_i9075($m4is_v586, $m4is_q61487){
 if(!empty($m4is_v586)){
$m4is_v586 =str_replace([" ", "-", "\n", "\r", "\t"], '', $m4is_v586);
 
} if(!empty($m4is_v586)){
$settings =['asset_id' =>$m4is_v586];
 return m4is_v628::m4is_c26()->m4is_w0842($settings);
 
}else{
return true;
 
}
}

