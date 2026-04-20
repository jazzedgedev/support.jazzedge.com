<?php

/**
 * Proprietary Software - All Rights Reserved
 *
 * This file is part of the Memberium plugin, which is proprietary software developed by Web Power and Light.
 * Unauthorized copying, distribution, or modification of this file, via any medium, is strictly prohibited.
 *
 * Copyright (c) 2017-2024 David J Bullock
 * Web Power and Light
 *
 * For licensing information, please contact Web Power and Light.
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_r5369 {
private static $m4is_r1546;
 private static $m4is_f683;
 private static $m4is_r02639;
 private static $m4is_f4218;
 private 
function __construct(){

} static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_f683 =m4is_f58::m4is_c26();
 self::$m4is_r02639 =!m4is_s52::m4is_w74();
 self::$m4is_f4218 ='memberium';
 
} static 
function m4is_u23($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'except_memberships' =>'', 'not' =>'', 'output' =>'', 'membership' =>'', 'memberships' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_r789 =!empty($m4is_l62046['not']);
 $m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_y65809 =$m4is_l62046['membership']. ',' . $m4is_l62046['memberships'];
 $m4is_y65809 =array_filter(explode(',', strtolower(trim($m4is_y65809 ))));
 $m4is_i9762 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'membership_names', '' );
 $m4is_j8940 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'membership_tags', '' );
 $m4is_c430 =array_filter(explode(',', strtolower($m4is_j8940 . ',' . $m4is_i9762 )));
 if(empty($m4is_y65809 )){
$m4is_m60 =false;
 
}elseif(!is_user_logged_in()){
$m4is_m60 =false;
 
}elseif(self::$m4is_r1546->m4is_v461()){
$m4is_m60 =true;
 
}else{
$m4is_m60 =(bool) count(array_intersect($m4is_c430, $m4is_y65809 ));
 
}$m4is_m60 =$m4is_r789 ? !$m4is_m60 : $m4is_m60;
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_d69835($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return 'n/a';
 
}$m4is_d039 =!is_feed();
 return m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_d039 );
 
} static 
function m4is_l74836($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'not' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_r789 =!empty($m4is_l62046['not']);
 $m4is_r789 =$m4is_v3458 == 'memb_is_not_admin' ? true : $m4is_r789;
 $m4is_e66310 =self::$m4is_r1546->m4is_v461();
 $m4is_e66310 =$m4is_r789 ? !$m4is_e66310 : $m4is_e66310;
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_e66310 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], '', '', '' );
 return $m4is_t09761;
 
} static 
function m4is_j87($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_m60 =is_single();
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_n64829($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'fields' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_m60 =false;
 $m4is_a89 =array_filter(explode(',', $m4is_l62046['fields']));
 if(empty($m4is_a89 )){
return false;
 
}foreach ($m4is_a89 as $m4is_q523 ){
if(isset($_GET[$m4is_q523])){
$m4is_m60 =true;
 break;
 
}
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_d24196($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
  
}m4is_j586::m4is_x7134();
 $m4is_y642 =['capture' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_t09761 =m4is_f58::m4is_c26()->m4is_z29675($m4is_t09761, $m4is_v3458 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_h821($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'not' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_u6591 =is_user_logged_in();
 $m4is_r789 =!empty($m4is_l62046['not']);
 $m4is_u6591 =$m4is_r789 ? !$m4is_u6591 : $m4is_u6591;
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_u6591 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], '', '', '' );
 return $m4is_t09761;
 
} static 
function m4is_k75216($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'debug' =>false, 'not' =>'', 'txtfmt' =>'', 'user_id' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_f087 =empty($m4is_l62046['user_id'])? self::$m4is_r1546->m4is_x66(): $m4is_l62046['user_id'];
 $m4is_l62046['debug']=m4is_f61::m4is_d8195($m4is_l62046['debug']);
 if(empty($m4is_f087 )){
return '';
 
}$m4is_r789 =!empty($m4is_l62046['not']);
 $m4is_k9075 =(int) get_user_meta($m4is_f087, 'login_count', true);
 $m4is_n1698 =2 > $m4is_k9075;
 $m4is_n1698 =$m4is_r789 ? !$m4is_n1698 : $m4is_n1698;
 if($m4is_l62046['debug']){
echo 'Login Count: ' . $m4is_k9075 . '<br>';
 return '';
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_n1698 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_z66789($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}global $post;
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_c05328 =(bool) $post->excerpt_only == 1;
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_c05328 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_q675($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'htmlattr' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $languages =m4is_d86::m4is_e93706();
 $m4is_t09761 =m4is_f61::m4is_i3627($m4is_t09761, $languages, $m4is_v3458 );
 $m4is_t09761 =m4is_f61::m4is_u150(true, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr']);
 return $m4is_t09761;
 
} static 
function m4is_h12($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'htmlattr' =>'', 'txtfmt' =>'', 'until' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 if(empty($m4is_l62046['until'])){
return do_shortcode($m4is_t09761 );
 
}if(strtotime($m4is_l62046['until'])> time()){
$m4is_t09761 =do_shortcode($m4is_t09761);
 
}$m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 return $m4is_t09761;
 
} static 
function m4is_i95364($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_p576 =0;
 $m4is_p576++;
 $m4is_y642 =['class' =>'', 'delayhide_function' =>'', 'delayhide' =>0, 'delayshow_function' =>'', 'delayshow' =>0, 'fadehide' =>0, 'fadeout_function' =>'', 'fadeout' =>0, 'fadeshow' =>0, 'id' =>'memberium_fader_' . $m4is_p576, 'style' =>'', 'target_class' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_i46 ='';
 $m4is_n66257 ='';
 $m4is_v6340 ='';
 $m4is_m06 ='#' . $m4is_l62046['id'];
 $m4is_t09761 =do_shortcode($m4is_t09761 );
 $m4is_l62046['delayshow']=(int) ($m4is_l62046['delayshow']* 1000 );
 $m4is_l62046['delayhide']=(int) ($m4is_l62046['delayhide']* 1000 );
 $m4is_l62046['fadeout']=(int) ($m4is_l62046['fadeout']* 1000 );
 $m4is_l62046['fadeshow']=(int) ($m4is_l62046['fadeshow']* 1000 );
 $m4is_l62046['fadehide']=(int) ($m4is_l62046['fadehide']* 1000 );
 $m4is_l62046['delayhide_function']=!empty($m4is_l62046['delayhide_function'])? ', ' . $m4is_l62046['delayhide_function']: '';
 $m4is_l62046['delayshow_function']=!empty($m4is_l62046['delayshow_function'])? ', ' . $m4is_l62046['delayshow_function']: '';
 $m4is_l62046['fadeout_function']=!empty($m4is_l62046['fadeout_function'])? ', ' . $m4is_l62046['fadeout_function']: '';
 if($m4is_l62046['fadeout']< 1 ){
$m4is_v6340 ='display:none;';
 
}if($m4is_l62046['style']> '' ){
$m4is_v6340 .= $m4is_l62046['style'];
 
}if($m4is_l62046['target_class']> '' ){
$m4is_m06 =$m4is_l62046['target_class'];
 
}if(trim($m4is_t09761 )> '' ){
$m4is_i46 =sprintf('<div id="%s" class="%s" style="%s">%s</div>', $m4is_l62046['id'], $m4is_l62046['class'], $m4is_v6340, $m4is_t09761 );
  
}$m4is_n66257 .= '<script type="text/javascript">';
 $m4is_n66257 .= 'jQuery(document).ready(function() { ';
 $m4is_n66257 .= '   jQuery("' . $m4is_m06 . '")';
 if($m4is_l62046['fadeout']> 0){
$m4is_n66257 .= '.delay(' . $m4is_l62046['fadeout'].')';
 $m4is_n66257 .= '.fadeOut(' . $m4is_l62046['fadehide']. $m4is_l62046['fadeout_function']. ')';
 
}if($m4is_l62046['delayshow']> 0){
$m4is_n66257 .= '.delay(' . $m4is_l62046['delayshow']. ')';
 $m4is_n66257 .= '.fadeIn(' . $m4is_l62046['fadeshow']. $m4is_l62046['delayshow_function']. ')' ;
 
}if($m4is_l62046['delayhide']> 0){
$m4is_n66257 .= '.delay(' . $m4is_l62046['delayhide']. ')';
 $m4is_n66257 .= '.fadeOut(' . $m4is_l62046['fadehide']. $m4is_l62046['delayhide_function']. ')' ;
 
}$m4is_n66257 .= ";\n";
 $m4is_n66257 .= '});';
 $m4is_n66257 .= '</script>';
  return $m4is_n66257 . $m4is_i46;
 
} static 
function m4is_x62485($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return 'n/a';
 
}return '';
 
} static 
function m4is_y89($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return 'n/a';
 
}$m4is_c03875 =empty($_COOKIE['memberium_autologin_session'])? false : (bool) $_COOKIE['memberium_autologin_session'];
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_c03875 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761 );
 return $m4is_t09761;
 
} static 
function m4is_z97($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'txtfmt' =>'', 'types' =>'', 'not' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_b4068 =get_the_id();
 $m4is_g486 =array_filter(explode(',', strtolower($m4is_l62046['types'])));
 $m4is_q485 =strtolower(get_post_type($m4is_b4068 ));
 if(empty($m4is_g486 )){
return $m4is_q485;
 
}$m4is_u6591 =in_array($m4is_q485, $m4is_g486 );
 $m4is_u6591 =empty($m4is_l62046['not'])? $m4is_u6591 : !$m4is_u6591;
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_u6591 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], '', '', '' );
 return $m4is_t09761;
 
} static 
function m4is_o643($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'htmlattr' =>'', 'not' =>'', 'post_id' =>0, 'postid' =>0, 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_o6715 =false;
 if($m4is_l62046['post_id']> 0 ){
$m4is_l62046['postid']=$m4is_l62046['post_id'];
 
}$m4is_l62046['postid']=empty($m4is_l62046['postid'])? (int) get_the_ID(): (int) $m4is_l62046['postid'];
 $m4is_l62046['not']=!empty($m4is_l62046['not']);
 if(stripos($m4is_v3458, '_not_' )){
$m4is_l62046['not']=true;
 
}if(self::$m4is_r1546->m4is_v461()){
$m4is_o6715 =true;
 
}else{
if($m4is_l62046['postid']){
$m4is_o6715 =apply_filters('memberium_has_post_access', null, $m4is_l62046['postid']);
 
}
}if($m4is_l62046['not']){
$m4is_o6715 =!$m4is_o6715;
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_o6715 );
 $m4is_t09761 =m4is_f61::m4is_u150(true, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 return $m4is_t09761;
 
} static 
function m4is_t1669($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'contact_id' =>0, 'days' =>'', 'debug' =>false, 'hours' =>'', 'htmlattr' =>'', 'interval' =>'', 'months' =>'', 'tag_id' =>'', 'txtfmt' =>'', 'weeks' =>'', 'years' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_q26407 =self::$m4is_r1546->m4is_j498('settings', 'sync_tag_details' );
 if(empty($m4is_q26407 )){
return '';
 
}global $wpdb;
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_m60 =false;
 $m4is_l62046['interval']=empty($m4is_l62046['hours'])? $m4is_l62046['interval']: $m4is_l62046['interval']. " {$m4is_l62046['hours']
} hours ";
 $m4is_l62046['interval']=empty($m4is_l62046['days'])? $m4is_l62046['interval']: $m4is_l62046['interval']. " {$m4is_l62046['days']
} days ";
 $m4is_l62046['interval']=empty($m4is_l62046['weeks'])? $m4is_l62046['interval']: $m4is_l62046['interval']. " {$m4is_l62046['weeks']
} weeks ";
 $m4is_l62046['interval']=empty($m4is_l62046['months'])? $m4is_l62046['interval']: $m4is_l62046['interval']. " {$m4is_l62046['months']
} months ";
 $m4is_l62046['interval']=empty($m4is_l62046['years'])? $m4is_l62046['interval']: $m4is_l62046['interval']. " {$m4is_l62046['years']
} years ";
 if(empty($m4is_l62046['contact_id'])||empty($m4is_l62046['tag_id'])||empty($m4is_l62046['interval'])){
return '';
 
} $m4is_h21895 =empty($m4is_l62046['contact_id'])? (int) self::$m4is_r1546->m4is_z56(): (int) $m4is_l62046['contact_id'];
 $m4is_o076 =time();
 $m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 $m4is_v2613 ='SELECT `created` FROM %s WHERE `appname` = %s AND `contactid` = %d AND `tagid` = %d ;';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_k865::m4is_o39864(), $m4is_r9613, $m4is_h21895, $m4is_l62046['tag_id']);
 $m4is_c06971 =strtotime($wpdb->get_var($m4is_v2613 ). ' + ' . $m4is_l62046['interval']);
 if(empty($m4is_c06971 )){
return '';
 
}$m4is_m60 =$m4is_o076 >= $m4is_c06971;
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(true, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 return $m4is_t09761;
 
} static 
function m4is_g4671($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'except_roles' =>'', 'not' =>'', 'roles' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['not']=!empty($m4is_l62046['not']);
 if(stripos($m4is_v3458, '_not_' )){
$m4is_l62046['not']=true;
 
}$m4is_l62046['except_roles']=array_filter(explode(',', strtolower(trim($m4is_l62046['except_roles']))));
 $m4is_l62046['roles']=array_filter(explode(',', strtolower(trim($m4is_l62046['roles']))));
 $m4is_l17096 =get_userdata(self::$m4is_r1546->m4is_x66());
 $m4is_l182 =is_a($m4is_l17096, 'WP_User' )? $m4is_l17096->roles : [];
 $m4is_t265 =false;
 if(empty($m4is_l62046['roles'])&&empty($m4is_l62046['except_roles'])){
return '';
 
}if(is_user_logged_in()){
if(self::$m4is_r1546->m4is_v461()){
$m4is_t265 =true;
 
}else{
$m4is_t265 =(boolean) (count(array_intersect($m4is_l62046['roles'], $m4is_l182 ))== count($m4is_l62046['roles']));
 if($m4is_t265 ){
$m4is_t265 =!(boolean) (count(array_intersect($m4is_l62046['except_roles'], $m4is_l182 ))> 0 );
 
}if($m4is_l62046['not']){
$m4is_t265 =!$m4is_t265;
 
}
}
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_t265 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_c3194($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'except_roles' =>'', 'not' =>'', 'roles' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['not']=!empty($m4is_l62046['not']);
 $m4is_l62046['not']=stripos($m4is_v3458, '_not_')? true : $m4is_l62046['not'];
 $m4is_l62046['except_roles']=array_filter(explode(',', strtolower(trim($m4is_l62046['except_roles']))));
 $m4is_l62046['roles']=array_filter(explode(',', strtolower(trim($m4is_l62046['roles']))));
 $m4is_l17096 =wp_get_current_user();
 $m4is_l182 =(is_a($m4is_l17096, 'WP_User' ))? $m4is_l17096->roles : [];
 $m4is_t265 =false;
 if(empty($m4is_l62046['roles'])&&empty($m4is_l62046['except_roles'])){
return '';
 
}if(is_user_logged_in()){
if(self::$m4is_r1546->m4is_v461()){
$m4is_t265 =true;
 
}else{
$m4is_t265 =(boolean) (count(array_intersect($m4is_l62046['roles'], $m4is_l182 ))> 0 );
 if($m4is_t265 ){
$m4is_t265 =!(boolean) (count(array_intersect($m4is_l62046['except_roles'], $m4is_l182 ))> 0 );
 
}if($m4is_l62046['not']){
$m4is_t265 =!$m4is_t265;
 
}
}
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_t265 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_q8415($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'contact_id' =>0, 'except_contact_ids' =>'', 'not' =>'', 'output' =>'', 'tagid' =>'', 'tagids' =>'', 'tag_ids' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_h21895 =empty($m4is_l62046['contact_id'])? self::$m4is_r1546->m4is_z56(): $m4is_l62046['contact_id'];
 $m4is_l9321 =array_filter(explode(',', trim($m4is_l62046['tagid']. ',' . $m4is_l62046['tagids']. ',' . $m4is_l62046['tag_ids'], ',' )));
 $m4is_r789 =!empty($m4is_l62046['not']);
 $m4is_r789 =stripos($m4is_v3458, '_not_' )? !$m4is_r789 : $m4is_r789;
 $m4is_w26561 =array_filter(explode(',', $m4is_l62046['except_contact_ids']));
 $m4is_t265 =false;
 $m4is_e66310 =self::$m4is_r1546->m4is_v461();
 if(!$m4is_e66310 ){
if(!count($m4is_l9321 )){
return '';
 
}if(!in_array($m4is_h21895, $m4is_w26561 )){
$m4is_t265 =$m4is_h21895 > 0 ? self::$m4is_r1546->m4is_x13466($m4is_l9321, $m4is_h21895 ): false;
 
}
}$m4is_t265 =$m4is_e66310 ? true : $m4is_t265;
 $m4is_t265 =$m4is_r789 ? !$m4is_t265 : $m4is_t265;
 $m4is_t09761 =empty($m4is_l62046['output'])? $m4is_t09761 : $m4is_l62046['output'];
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_t265 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_e63401($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'category_id' =>'', 'contact_id' =>self::$m4is_r1546->m4is_z56(), 'debug' =>false, 'except_contact_ids' =>'', 'except_contactid' =>'', 'except_tag_ids' =>'', 'except_tagid' =>'', 'min' =>1, 'not' =>'', 'output' =>'', 'tag_id' =>'', 'tag_ids' =>'', 'tagid' =>'', 'tagids' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_e66310 =self::$m4is_r1546->m4is_v461();
 $m4is_d73218 =self::$m4is_r1546->m4is_z56();
 $m4is_r789 =!empty($m4is_l62046['not']);
 $m4is_r789 =stripos($m4is_v3458, '_not_' )? !$m4is_r789 : $m4is_r789;
 $m4is_s15437 =trim(trim($m4is_l62046['tag_id']. ',' . $m4is_l62046['tag_ids']. ',' . $m4is_l62046['tagid']. ',' . $m4is_l62046['tagids'], ',' ));
 $m4is_x718 =trim($m4is_l62046['except_tagid']. ',' . $m4is_l62046['except_tag_ids']);
 $m4is_w26561 =trim($m4is_l62046['except_contactid']. ',' . $m4is_l62046['except_contact_ids']);
 $m4is_h21895 =empty($m4is_l62046['contact_id'])? $m4is_d73218 : $m4is_l62046['contact_id'];
 $m4is_t265 =$m4is_e66310;
 $m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 $m4is_k824 =m4is_q82::m4is_d59($m4is_f087 );
 $m4is_f406 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tags', '' );
 if(!$m4is_e66310 ){
$m4is_k152 =true;
 $m4is_t319 =true;
 $m4is_y26847 =true;
 $m4is_k05826 =true;
 if(!empty($m4is_s15437 )){
$m4is_k152 =self::$m4is_r1546->m4is_m2480($m4is_s15437, $m4is_k824 );
 
}if(!empty($m4is_x718 )){
$m4is_t319 =!self::$m4is_r1546->m4is_m2480($m4is_x718, $m4is_k824 );
 
}if(!empty($m4is_w26561 )){
$m4is_l62046['except_contactids']=array_filter(explode(',', $m4is_w26561));
 if(count($m4is_l62046['except_contactids'])&&in_array($m4is_l62046['contact_id'], $m4is_l62046['except_contactids'])){
$m4is_y26847 =false;
 
}
}if(!empty($m4is_l62046['category_id'])){
$m4is_k05826 =self::$m4is_r1546->m4is_d49860((int) $m4is_l62046['category_id'], $m4is_k824 );
 
}$m4is_t265 =($m4is_k152 === true )&&($m4is_t319 === true )&&($m4is_y26847 === true )&&($m4is_k05826 === true );
 if($m4is_t265 &&$m4is_l62046['min']> 1 ){
$m4is_i29 =array_filter(explode(',', $m4is_s15437 ));
 $m4is_c079 =array_filter(explode(',', $m4is_f406 ));
 $m4is_v6715 =0;
 foreach($m4is_c079 as $m4is_q01842 ){
if(in_array($m4is_q01842, $m4is_i29 )){
$m4is_v6715++;
 
}
}if($m4is_v6715 < $m4is_l62046['min']){
$m4is_t265 =false;
 
}
}$m4is_t265 =$m4is_r789 ? !$m4is_t265 : $m4is_t265;
 
}$m4is_t09761 =empty($m4is_l62046['output'])? $m4is_t09761 : $m4is_l62046['output'];
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_t265 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_l96($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'caseinsensitive' =>'yes', 'not' =>'', 'test' =>'=', 'output' =>'', 'txtfmt' =>'', 'value1' =>'', 'value2' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_r789 =m4is_f61::m4is_d8195($m4is_l62046['not'], false );
 $m4is_e66310 =self::$m4is_r1546->m4is_v461();
 $m4is_n93 =m4is_f61::m4is_d8195($m4is_l62046['caseinsensitive'], true );
 $m4is_t09761 =empty($m4is_l62046['output'])? $m4is_t09761 : $m4is_l62046['output'];
 $m4is_m60 =m4is_f61::m4is_b02($m4is_l62046['value1'], $m4is_l62046['test'], $m4is_l62046['value2'], $m4is_n93 );
 $m4is_m60 =$m4is_e66310 ? true : $m4is_m60;
 $m4is_m60 =$m4is_r789 ? !$m4is_m60 : $m4is_m60;
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(true, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_w28453($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'not' =>'', 'output' =>'', 'tokens' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_r789 =m4is_f61::m4is_d8195($m4is_l62046['not'], false );
 $m4is_m20 =trim($m4is_l62046['tokens'], ',' );
 $m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_e66310 =self::$m4is_r1546->m4is_v461();
 $m4is_r789 =stripos($m4is_v3458, '_not_' )? !$m4is_r789 : $m4is_r789;
 $m4is_t265 =false;
 if($m4is_e66310 ){
$m4is_t265 =true;
 
}elseif($m4is_f087 < 1){
$m4is_t265 =false;
 
}elseif(empty($m4is_m20 )){
$m4is_t265 =false;
 
}else{
$m4is_t265 =self::$m4is_r1546->m4is_d4936($m4is_m20, $m4is_f087 );
 
}$m4is_t265 =$m4is_r789 ? !$m4is_t265 : $m4is_t265;
 $m4is_t09761 =empty($m4is_l62046['output'])? $m4is_t09761 : $m4is_l62046['output'];
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_t265 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_m02($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'levels' =>'', 'strict' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_t265 =false;
 $m4is_v068 =m4is_f61::m4is_d8195($m4is_l62046['strict'], false );
 $m4is_h21895 =self::$m4is_r1546->m4is_z56();
 $m4is_e66310 =self::$m4is_r1546->m4is_v461();
 if($m4is_e66310 ){
$m4is_t265 =true;
 
}if($m4is_h21895 ){
$m4is_t265 =self::$m4is_f683->m4is_d40658($m4is_l62046['strict'], $m4is_l62046['levels']);
 
}$m4is_t265 =$m4is_e66310 ? true : $m4is_t265;
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_t265 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_x976($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'except_contact_ids' =>'', 'except_contactid' =>'', 'except_tag_ids' =>'', 'except_tagid' =>'', 'tagid' =>'', 'tag_ids' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_h21895 =self::$m4is_r1546->m4is_z56();
 $m4is_e66310 =self::$m4is_r1546->m4is_v461();
 $m4is_x718 =$m4is_l62046['except_tagid']=trim($m4is_l62046['except_tagid']. ',' . $m4is_l62046['except_tag_ids'], ', ' );
 $m4is_w26561 =$m4is_l62046['except_contactid']=trim($m4is_l62046['except_contactid']. ',' . $m4is_l62046['except_contact_ids'], ', ' );
 $m4is_s15437 =$m4is_l62046['tagid']=trim($m4is_l62046['tagid']. ',' . $m4is_l62046['tag_ids'], ', ' );
 if(empty($m4is_s15437 )){
return '';
 
}$m4is_t265 =$m4is_h21895 ? self::$m4is_r1546->m4is_m2480($m4is_s15437 ): false;
 if($m4is_t265 ){
$m4is_w26561 =array_filter(explode(',', $m4is_w26561 ));
 if(count($m4is_w26561 )&&in_array($m4is_h21895, $m4is_w26561 )){
$m4is_t265 =false;
 
}
}if($m4is_t265 ){
if($m4is_x718 > '' &&self::$m4is_r1546->m4is_m2480($m4is_x718 )){
$m4is_t265 =false;
 
}
}$m4is_t265 =$m4is_e66310 ? true : $m4is_t265;
 $m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, !$m4is_t265 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_u665($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'caseinsensitive' =>true, 'key' =>'', 'test' =>'=', 'txtfmt' =>'', 'value' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_n93 =m4is_f61::m4is_d8195($m4is_l62046['caseinsensitive'], false );
 $m4is_l9671 =trim($m4is_l62046['key']);
 $m4is_q160 =trim($m4is_l62046['test']);
 $m4is_v586 =trim($m4is_l62046['value']);
 if(empty($m4is_l9671 )){
return '';
 
}if(empty($m4is_v586 )&&$m4is_l62046['test']== '=' ){
$m4is_m60 =isset($_COOKIE[$m4is_l9671]);
 
}else{
$m4is_d853 =isset($_COOKIE[$m4is_l9671])? trim($_COOKIE[$m4is_l9671]): '';
 $m4is_m60 =m4is_f61::m4is_b02($m4is_d853, $m4is_q160, $m4is_v586, $m4is_n93 );
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_e23146($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'caseinsensitive' =>true, 'key' =>'', 'test' =>'=', 'txtfmt' =>'', 'value' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_n93 =m4is_f61::m4is_d8195($m4is_l62046['caseinsensitive'], false );
 $m4is_l9671 =trim($m4is_l62046['key']);
 $m4is_q160 =trim($m4is_l62046['test']);
 $m4is_v586 =trim($m4is_l62046['value']);
 if(empty($m4is_l9671 )){
return '';
 
}if(empty($m4is_v586 )&&$m4is_q160 == '='){
$m4is_m60 =isset($_GET[$m4is_l9671]);
 
}else{
$m4is_o8491 =isset($_GET[$m4is_l9671])? trim($_GET[$m4is_l9671]): '';
 $m4is_m60 =m4is_f61::m4is_b02($m4is_o8491, $m4is_q160, $m4is_v586, $m4is_n93 );
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, TRUE, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_i078($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'caseinsensitive' =>true, 'key' =>'', 'test' =>'=', 'txtfmt' =>'', 'value' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_n93 =m4is_f61::m4is_d8195($m4is_l62046['caseinsensitive'], false );
 $m4is_l9671 =trim($m4is_l62046['key']);
 $m4is_q160 =trim($m4is_l62046['test']);
 $m4is_v586 =trim($m4is_l62046['value']);
 if(empty($m4is_l9671 )){
return '';
 
}if(empty($m4is_v586 )&&$m4is_q160 == '=' ){
$m4is_m60 =isset($_POST[$m4is_l9671]);
 
}else{
$m4is_x2971 =isset($_POST[$m4is_l9671])? trim($_POST[$m4is_l9671]): '';
 $m4is_m60 =m4is_f61::m4is_b02($m4is_x2971, $m4is_q160, $m4is_v586, $m4is_n93 );
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, TRUE, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_d68($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'caseinsensitive' =>true, 'key' =>'', 'test' =>'=', 'txtfmt' =>'', 'value' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_n93 =m4is_f61::m4is_d8195($m4is_l62046['caseinsensitive'], false );
 $m4is_l9671 =trim($m4is_l62046['key']);
 $m4is_q160 =trim($m4is_l62046['test']);
 $m4is_v586 =trim($m4is_l62046['value']);
 if(empty($m4is_l9671 )){
return '';
 
}if(empty($m4is_v586 )&&$m4is_q160 == '=' ){
$m4is_m60 =isset($_REQUEST[$m4is_l9671]);
 
}else{
$m4is_x56067 =isset($_REQUEST[$m4is_l9671])? $_REQUEST[$m4is_l9671]: '';
 $m4is_m60 =m4is_f61::m4is_b02($m4is_x56067, $m4is_q160, $m4is_v586, $m4is_n93 );
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, TRUE, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_w647($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'name' =>'', 'test' =>'show', 'txtfmt' =>'', 'val' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_f087 =self::$m4is_r1546->m4is_x66();
 if(empty($m4is_f087 )){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_k52736 =strtolower(trim($m4is_l62046['name']));
 $m4is_q160 =strtolower(trim($m4is_l62046['test']));
 $m4is_v586 =(float) $m4is_l62046['val'];
 $m4is_v4821 =(float) self::$m4is_f683->m4is_v9648($m4is_k52736 );
 $m4is_m60 =FALSE;
 if(in_array($m4is_q160, ['eq', '=', '==', '==='])){
$m4is_m60 =($m4is_v4821 === $m4is_v586 );
 
}elseif($m4is_q160 == 'gt' ){
$m4is_m60 =$m4is_v4821 > $m4is_v586;
 
}elseif($m4is_q160 == 'lt'){
$m4is_m60 =$m4is_v4821 < $m4is_v586;
 
}elseif($m4is_q160 == 'le'){
$m4is_m60 =$m4is_v4821 <= $m4is_v586;
 
}elseif($m4is_q160 == 'ge'){
$m4is_m60 =$m4is_v4821 >= $m4is_v586;
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, TRUE, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_t206($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['browser' =>'', 'capture' =>'', 'os' =>'', 'txtfmt' =>'', 'type' =>'', 'user' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_d039 =true;
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_e8216 =m4is_f58::m4is_c26()->m4is_c710($_SERVER['HTTP_USER_AGENT']);
 $m4is_h5106 =$m4is_l62046['browser']=strtolower(trim($m4is_l62046['browser']));
 $m4is_j0481 =$m4is_l62046['os']=strtolower(trim($m4is_l62046['os']));
 $m4is_j0361 =$m4is_l62046['type']=strtolower(trim($m4is_l62046['type']));
 $m4is_l17096 =$m4is_l62046['user']=strtolower(trim($m4is_l62046['user']));
 if(!empty($m4is_h5106 )&&$m4is_h5106 !== $m4is_e8216['browser']){
$m4is_d039 =false;
 
}if(!empty($m4is_j0481 )&&$m4is_j0481 !== $m4is_e8216['os' ]){
$m4is_d039 =false;
 
}if(!empty($m4is_j0361 )&&$m4is_j0361 != $m4is_e8216['type']){
$m4is_d039 =false;
 
}if(!empty($m4is_l17096 )&&$m4is_l62046['user']!= $m4is_e8216['user']){
$m4is_d039 =false;
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_d039 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_a756($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}$m4is_y642 =['capture' =>'', 'date_format' =>'F jS, Y', 'date' =>'', 'days' =>0, 'months' =>0, 'tagids' =>'', 'txtfmt' =>'', 'weeks' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_m60 =false;
 $m4is_e66310 =self::$m4is_r1546->m4is_v461();
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l9321 =array_filter(explode(',', trim($m4is_l62046['tagids'])));
 $m4is_t082 =$m4is_l62046['date'];
 $m4is_t21468 =(int) $m4is_l62046['days'];
 $m4is_j38 =(int) $m4is_l62046['weeks'];
 $m4is_a78951 =(int) $m4is_l62046['months'];
 $m4is_t21468 =$m4is_t21468 + ($m4is_j38 * 7 )+ ($m4is_a78951 * 30 );
 if($m4is_t21468 < 1 &&empty($m4is_l62046['date'])){
return;
 
}if($m4is_e66310 ){
$m4is_m60 =true;
 
}else{
if(empty($m4is_t082 )){
$m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_t082 =get_userdata($m4is_f087 )->user_registered;
 
}if($m4is_t21468 ){
$m4is_k3165 =strtotime(sprintf('%s + %s days', $m4is_t082, $m4is_t21468 ));
 
}else{
$m4is_k3165 =strtotime($m4is_t082 );
 
}if($m4is_k3165 < time()){
$m4is_m60 =true;
 if(!empty($m4is_l9321 )){
if(!self::$m4is_r1546->m4is_m2480($m4is_l9321 )){
$m4is_m60 =false;
 
}
}
}else{
$m4is_m60 =false;
 
}
}if($m4is_m60 ){
$m4is_t09761 =str_ireplace('{{:date:}}', date($m4is_l62046['date_format'], $m4is_k3165 ), $m4is_t09761 );
 $m4is_x0356 =(int) (($m4is_k3165 - time())/ 86400 );
 $m4is_x0356 .= $m4is_x0356 > 1 ? ' days' : ' day';
 $m4is_t09761 =str_ireplace('{{:days:}}', $m4is_x0356, $m4is_t09761 );
 $m4is_b814 =(int) (($m4is_k3165 - time())/ (7 * 86400 ));
 $m4is_b814 .= $m4is_b814 > 1 ? ' weeks' : ' week';
 $m4is_t09761 =str_ireplace('{{:weeks:}}', $m4is_b814, $m4is_t09761 );
 $m4is_s50782 =(int) (($m4is_k3165 - time())/ (30 * 86400 ));
 $m4is_s50782 .= $m4is_s50782 > 1 ? ' months' : ' month';
 $m4is_t09761 =str_ireplace('{{:months:}}', $m4is_s50782, $m4is_t09761 );
 $m4is_x0356 =intval(($m4is_k3165 - time()- (7 * 86400 * $m4is_b814 ))/ 86400 );
 $m4is_x0356 .= $m4is_x0356 > 1 ? ' days' : ' day';
 $m4is_t09761 =str_ireplace('{{:compoundtime:}}', $m4is_b814 . ' and ' . $m4is_x0356, $m4is_t09761);
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, TRUE, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_m73($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'date_format' =>'F jS, Y', 'end' =>'', 'start' =>'', 'tagids' =>'', 'txtfmt' =>'', 'tz' =>get_option('timezone_string' ), ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_q7962 =$m4is_l62046['start'];
 $m4is_s91 =$m4is_l62046['end'];
 $m4is_l9321 =array_filter(explode(',', trim($m4is_l62046['tagids'])));
 $m4is_t26387 =in_array($m4is_l62046['tz'], timezone_identifiers_list())? $m4is_l62046['tz']: get_option('timezone_string' );
 $m4is_s20349 =date_default_timezone_get();
 $m4is_o076 =time();
 $m4is_m60 =false;
 if(!empty($m4is_t26387 )){
date_default_timezone_set($m4is_t26387 );
 
}$m4is_c53 =(int) strtotime($m4is_q7962 );
 $m4is_j53276 =strtotime($m4is_s91 )? (int) strtotime($m4is_s91 ): $m4is_o076 + 1;
 date_default_timezone_set($m4is_s20349 );
 $m4is_m60 =($m4is_o076 > $m4is_c53 &&$m4is_o076 < $m4is_j53276 );
  if(!empty($m4is_l9321 )){
$m4is_m60 =$m4is_m60 &&self::$m4is_r1546->m4is_m2480($m4is_l9321 );
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, TRUE, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
} static 
function m4is_b978($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'date_format' =>'F jS, Y', 'date' =>'', 'days' =>0, 'months' =>0, 'tagids' =>'', 'txtfmt' =>'', 'weeks' =>0, 'years' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_m60 =false;
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_t082 =trim($m4is_l62046['date']);
 $m4is_l9321 =array_filter(explode(',', trim($m4is_l62046['tagids'])));
 $m4is_t21468 =(int) $m4is_l62046['days'];
 $m4is_a78951 =(int) $m4is_l62046['months'];
 $m4is_j38 =(int) $m4is_l62046['weeks'];
 $m4is_l10 =(int) $m4is_l62046['years'];
 $m4is_t21468 =$m4is_t21468 + ($m4is_j38 * 7 )+ ($m4is_a78951 * 30)+ ($m4is_l10 * 365 );
 if(empty($m4is_t082 )){
$m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_t082 =get_userdata($m4is_f087 )->user_registered;
 $m4is_k3165 =strtotime(sprintf('% + %s days', $m4is_t082, $m4is_t21468 ));
 
}else{
$m4is_k3165 =strtotime($m4is_t082 );
 
}$m4is_m60 =($m4is_k3165 > time());
 if(!empty($m4is_l9321 )){
$m4is_m60 =$m4is_m60 &&self::$m4is_r1546->m4is_m2480($m4is_l9321 );
 
}if($m4is_m60 ){
$m4is_t09761 =str_ireplace('{{:date:}}', date($m4is_l62046['date_format'], $m4is_k3165 ), $m4is_t09761 );
 $m4is_x0356 =(int) (($m4is_k3165 - time())/ DAY_IN_SECONDS );
 $m4is_x0356 .= $m4is_x0356 > 1 ? ' days' : ' day';
 $m4is_t09761 =str_ireplace('{{:days:}}', $m4is_x0356, $m4is_t09761 );
 $m4is_b814 =(int) (($m4is_k3165 - time())/ (WEEK_IN_SECONDS ));
 $m4is_b814 .= $m4is_b814 > 1 ? ' weeks' : ' week';
 $m4is_t09761 =str_ireplace('{{:weeks:}}', $m4is_b814, $m4is_t09761 );
 $m4is_s50782 =(int) (($m4is_k3165 - time())/ (30 * DAY_IN_SECONDS ));
 $m4is_s50782 .= $m4is_s50782 > 1 ? ' months' : ' month';
 $m4is_t09761 =str_ireplace('{{:months:}}', $m4is_s50782, $m4is_t09761);
 $m4is_b814 =intval(($m4is_k3165 - time()- (7 * DAY_IN_SECONDS * $m4is_b814))/ 86400 );
 $m4is_b814 .= $m4is_b814 > 1 ? ' days' : ' day';
 $m4is_t09761 =str_ireplace('{{:compoundtime:}}', $m4is_b814 . ' and ' . $m4is_x0356, $m4is_t09761 );
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_m60 );
 $m4is_t09761 =m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 return $m4is_t09761;
 
}
}

