<?php

/**
 * Copyright (c) 2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83')||die();
  
class m4is_q631 {
private $version;
 private $to_json =[];
  
function __construct($m4is_a6814){
$this->version =$m4is_a6814;
 
} 
function frontend_scripts(){
$m4is_k72 =$this->version;
 $m4is_w43796 =WPAL_ZOOM_URL . 'assets/';
 wp_register_script("wpal-zoom-js", "{$m4is_w43796
}wpal-zoom.js", [], $m4is_k72);
 wp_register_style("wpal-zoom-css", "{$m4is_w43796
}wpal-zoom.css", [], $m4is_k72, 'all');
 add_action("wp_footer", [$this, "frontend_footer"]);
 
} 
function zoom_event_func($m4is_l62046, $m4is_t09761, $m4is_p786 ){
m4is_j586::m4is_x7134();
 $m4is_l62046 =shortcode_atts(['className' =>'', 'id' =>0, 'password' =>'', 'host' =>0 ], $m4is_l62046 );
  $m4is_r91662 =$m4is_l62046['className']> "" ? esc_attr($m4is_l62046['className']): "";
 $m4is_r91662 =$m4is_r91662 > '' ? " {$m4is_r91662
}" : "";
 $m4is_p576 ='wpal-zoom-meetings';
 $m4is_r91662 .= $m4is_r91662 > '' ? " {$m4is_p576
}" : $m4is_p576;
  $m4is_d07693 =esc_attr($m4is_l62046['id']);
 $m4is_d07693 =$m4is_d07693 > '' ? (int)str_replace(' ', '', $m4is_d07693): 0;
 $m4is_m676 =esc_attr($m4is_l62046['password']);
 $m4is_h540 =(int)esc_attr($m4is_l62046['host']);
 $m4is_m7560 =0;
  $m4is_e36 =m4is_i01()->m4is_j498('api_key');
 $m4is_f087 =get_current_user_id();
 if($m4is_d07693 < 1 ){
return $this->display_error(__('Zoom event ID is required.', 'wpal-zoom'));
 
}elseif(empty($m4is_m676)){
return $this->display_error(__('Zoom event password is required.', 'wpal-zoom'));
 
}elseif(empty($m4is_e36)){
return $this->display_error(__('Zoom api key is missing.', 'wpal-zoom'));
 
} if((int)$m4is_f087 < 1 ){
return $this->display_error(__('You must be logged in to join this event.', 'wpal-zoom'));
 
}else{
$m4is_w9618 =get_userdata($m4is_f087 );
 $m4is_n47039 =trim("{$m4is_w9618->first_name
} {$m4is_w9618->last_name
}");
 $m4is_q12678 =strtolower($m4is_w9618->user_email);
 $m4is_m7560 =($m4is_h540 > 0 &&$m4is_h540 === $m4is_f087 )? 1 : 0;
 
} $m4is_i521 ='wpal-zoom-iframe';
 $m4is_o66974 =WPAL_ZOOM_URL . 'templates/zoom-frame.html';
  $m4is_i46 ="<div id=\"{$m4is_p576
}\" class=\"{$m4is_r91662
}\">";
 $m4is_i46 .= "<iframe id=\"{$m4is_i521
}\" data-src=\"{$m4is_o66974
}\" allowfullscreen></iframe>";
 $m4is_i46 .= "</div>";
  $this->set_to_json('zoom_meeting', ['frameId' =>$m4is_i521, 'buttonId' =>'wpal-start-event', 'eventID' =>$m4is_d07693, 'leaveUrl' =>$m4is_o66974, 'passWord' =>$m4is_m676, 'apiKey' =>$m4is_e36, 'userName' =>$m4is_n47039, 'userEmail' =>$m4is_q12678, 'role' =>$m4is_m7560, 'signature' =>m4is_i01()->api()->m4is_i346($m4is_d07693, $m4is_m7560)]);
 return $m4is_i46;
 
} 
function display_error($m4is_q847){
$m4is_i46 ="<div class=\"wpal-zoom-error\">";
 $m4is_i46 .= "<p>{$m4is_q847
}</p>";
 $m4is_i46 .= "</div>";
 return $m4is_i46;
 
} 
function frontend_footer(){
$m4is_u51876 =$this->get_to_json();
 if(!empty($m4is_u51876)){
$m4is_f087 =get_current_user_id();
 $m4is_u51876['user_id']=$m4is_f087;
 if(isset($m4is_u51876['zoom_meeting'])){
wp_enqueue_style('wpal-zoom-css');
 wp_enqueue_script('wpal-zoom-js');
 wp_localize_script('wpal-zoom-js', 'wpal_zoom_data', $m4is_u51876);
 
}
}
} 
function set_to_json($m4is_l9671, $m4is_v586 =false){
if($m4is_v586){
$this->to_json[$m4is_l9671]=$m4is_v586;
 
}else{
unset($this->to_json[$m4is_l9671]);
 
}
} 
function get_to_json($m4is_l9671 =false){
if($m4is_l9671){
return (isset($this->to_json[$m4is_l9671]))? $this->to_json[$m4is_l9671]: null;
 
}else{
return $this->to_json;
 
}
}
}

