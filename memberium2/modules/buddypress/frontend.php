<?php

/**
* Copyright (c) 2018-2022 David J Bullock
* Web Power and Light
*/


  class_exists('m4is_r83')||die();
 final 
class m4is_q02 {
private $m4is_r1546;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_r1546->m4is_w879('m4is_s5403', __DIR__ . '/shortcodes' );
 add_action('memberium/shortcodes/add', [$this, 'm4is_s2469']);
 $this->m4is_s2469();
 $this->m4is_d4861();
 
}private 
function m4is_d4861(){
add_action('template_redirect', [$this, 'm4is_y862'], 11 );
 
}public 
function m4is_y862(){
if(!is_buddypress()){
return;
 
}$m4is_o66412 =bp_current_component();
 if(!$m4is_o66412 ){
return;
 
}$m4is_o66412 =$m4is_o66412 == 'profile' ? 'members' : $m4is_o66412;
 $m4is_b4068 =bp_core_get_directory_page_id($m4is_o66412 );
 if(!$m4is_b4068 ){
return;
 
}$m4is_f683 =m4is_f58::m4is_c26();
 $m4is_f0174 =$m4is_f683->m4is_x72168($m4is_b4068 );
 if($m4is_f0174 ){
return;
 
}$m4is_c05328 =$m4is_f683->m4is_a26($m4is_b4068 );
 if($m4is_c05328 == 'hide' ){
global $wp_query;
 $wp_query->set_404();
 status_header(404 );
 return;
 
}elseif($m4is_c05328 == 'redirect' ){
$m4is_f683->m4is_m6637($m4is_b4068 );
 
}
}public 
function m4is_s2469(){
$m4is_s6347 ='m4is_s5403';
 add_shortcode('memb_buddypressgroup_grid', [$m4is_s6347, 'm4is_z68967']);
 add_shortcode('memb_has_profile_type', [$m4is_s6347, 'm4is_p917']);
 
}
}

