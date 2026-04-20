<?php

/**
 * Copyright (c) 2012-2024 by David Bullock / Web Power and Light
 * All rights reserved.
 */


 class_exists('m4is_h68' )||die();
 final 
class m4is_c05 {
private $m4is_r1546;
 private $m4is_f683;
 private static $m4is_k2769 =[];
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_f683 =m4is_f58::m4is_c26();
 $this->m4is_d4861();
 
} private 
function m4is_d4861(){
add_filter('learndash_shortcode_atts', [$this, 'm4is_a329'], 10, 2 );
 add_action('memberium/shortcodes/add', [$this, 'm4is_u5410']);
 add_action('memberium/shortcodes/remove', [$this, 'm4is_u54']);
 add_filter('learndash_get_lesson_list_args', [$this, 'm4is_i43'], 10, 3 );
 add_filter('learndash_course_completion_url', [$this, 'm4is_p17466'], 5, 2 );
 add_filter('memberium/lms/course/item/data', [$this, 'm4is_b56091'], 10, 2 );
 add_filter('memberium/registration_fields/custom', [$this, 'm4is_k185'], 10, 1 );
 add_filter('ld_lesson_access_from', [$this, 'm4is_a843'], PHP_INT_MAX, 3);
 add_filter('sfwd_lms_has_access', [$this, 'm4is_b663'], PHP_INT_MAX, 3 );
 add_filter('learndash_can_user_read_step', [$this, 'm4is_a6803'], 10, 3 );
 add_filter('learndash_content_access', [$this, 'm4is_p52'], 5, 2 );
 add_action('init', [$this, 'm4is_j458']);
 $this->m4is_u5410();
 
} 
function m4is_j458(): void {
if(empty($_POST['memb_form_type'])){
return;
 
}$m4is_y46186 =['memberium/learndash/delete_history' =>['m4is_v076', 'm4is_i18563'], ];
 if(!array_key_exists($_POST['memb_form_type'], $m4is_y46186 )){
return;
 
}call_user_func($m4is_y46186[$_POST['memb_form_type']]);
 
} public 
function m4is_p17466($m4is_c67, $m4is_b4068 ){
$m4is_f56 =get_post_meta($m4is_b4068, '_is4wp_learndash_redirect', TRUE );
 if($m4is_f56 ){
return do_shortcode($m4is_f56 );
 
}else{
return $m4is_c67;
 
}
} public 
function m4is_b663($m4is_v586, $m4is_b4068, $m4is_f087 ){
$m4is_b4068 =(int) $m4is_b4068;
 $m4is_v586 =(bool) $m4is_v586;
  if(!$m4is_v586 ){
return $m4is_v586;
 
} if(get_post_type($m4is_b4068 )!== 'sfwd-courses' ){
return $m4is_v586;
 
} $m4is_f087 =$m4is_f087 ? $m4is_f087 : $this->m4is_r1546->m4is_x66();
  if($m4is_f087 ){
if(user_can($m4is_f087, 'edit_others_posts' )||user_can($m4is_f087, 'edit_post', $m4is_b4068 )){
return true;
 
}
}return $this->m4is_f683->m4is_x72168((int) $m4is_b4068, (int) $m4is_f087 );
 
} public 
function m4is_a329($m4is_a394, $m4is_j09 ){
if(empty($m4is_a394['post__in'])){
return $m4is_a394;
 
}if(!is_array($m4is_a394['post__in'])){
return $m4is_a394;
 
}foreach($m4is_a394['post__in']as $m4is_l9671 =>$m4is_b4068 ){
if(!$this->m4is_f683->m4is_x72168($m4is_b4068 )){
unset($m4is_a394['post__in'][$m4is_l9671]);
 
}
}return $m4is_a394;
 
} public 
function m4is_i43($m4is_y66291, $m4is_d07693, $m4is_n3691 ){
if(empty($m4is_y66291['post__in'])||!is_array($m4is_y66291['post__in'])){
return $m4is_y66291;
 
}foreach($m4is_y66291['post__in']as $m4is_l9671 =>$m4is_b4068 ){
if(!$this->m4is_f683->m4is_x72168($m4is_b4068 )){
unset($m4is_y66291['post__in'][$m4is_l9671]);
 
}
}return $m4is_y66291;
 
} public 
function m4is_k185($m4is_r08743 ){
$m4is_n68 =debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
 foreach($m4is_n68 as $m4is_x35){
if($m4is_x35['function']== 'learndash_login_shortcode'){
return true;
 
}
}return $m4is_r08743;
 
}  public 
function m4is_b56091(array $m4is_l91805, int $m4is_b4068 ){
if(get_post_type($m4is_b4068)!== 'sfwd-courses'){
return $m4is_l91805;
 
}$m4is_f087 =$this->m4is_r1546->m4is_x66();
 $m4is_r1692 =get_post_meta($m4is_b4068, '_sfwd-courses', true);
 $m4is_l91805['access']=sfwd_lms_has_access($m4is_b4068, $m4is_f087);
   if(!$m4is_l91805['access']){
$m4is_l91805['status']='locked';
 return $m4is_l91805;
 
} $m4is_c6320 =learndash_course_progress(['course_id' =>$m4is_b4068, 'user_id' =>$m4is_f087, 'array' =>true, ]);
 $m4is_l91805['progress']=($m4is_c6320 &&!empty($m4is_c6320['percentage']))? $m4is_c6320['percentage']: 0;
 $m4is_l91805['status']='locked';
  $m4is_x66985 =isset($m4is_r1692['sfwd-courses_course_price_type'])? $m4is_r1692['sfwd-courses_course_price_type']: 'open';
 if(in_array($m4is_x66985, ['open', 'free'])){
$m4is_l91805['status']='unlocked';
 
}elseif(in_array($m4is_x66985, ['paynow', 'closed', 'subscribe'])){
if($m4is_l91805['access']){
$m4is_l91805['status']='unlocked';
 
}
}if($m4is_l91805['status']!== 'locked' ){
if(!learndash_is_course_prerequities_completed($m4is_b4068 )){
$m4is_l91805['status']='locked';
  
}
}return $m4is_l91805;
 
} public 
function m4is_a6803($m4is_f0174, $m4is_e43671, $m4is_n3691 ){
if($m4is_f0174 ){
$m4is_f0174 =m4is_f58::m4is_c26()->m4is_x72168($m4is_e43671 );
 
}return $m4is_f0174;
 
} public 
function m4is_p52($m4is_z06174, $m4is_m5907 ){
if(method_exists(m4is_f58::m4is_c26(), 'm4is_x72168')){
$m4is_m60 =m4is_f58::m4is_c26()->m4is_x72168($m4is_m5907->ID );
 if(!$m4is_m60 ){
return $m4is_m5907->excerpt;
 
}
}return null;
 
} public 
function m4is_u5410(): void {
$m4is_x75360 ='m4is_v43';
 add_shortcode('memb_learndash_is_completed', [$m4is_x75360, 'm4is_b2590']);
 add_shortcode('memb_learndash_is_enrolled', [$m4is_x75360, 'm4is_y635']);
 add_shortcode('memb_learndash_course_enroll', [$m4is_x75360, 'm4is_f13']);
 add_shortcode('memb_learndash_course_unenroll', [$m4is_x75360, 'm4is_f13']);
 add_shortcode('memb_learndash_course_reset', [$m4is_x75360, 'm4is_i7581']);
 
} public 
function m4is_u54(): void {
remove_shortcode('memb_learndash_course_enroll' );
 remove_shortcode('memb_learndash_course_unenroll' );
 remove_shortcode('memb_learndash_is_completed' );
 remove_shortcode('memb_learndash_is_enrolled' );
 
} public 
function m4is_a843($m4is_n639, $m4is_b1249, $m4is_f087 ){
$m4is_h1945 =time();
 if($m4is_n639 < $m4is_h1945 ){
return $m4is_n639;
 
}static $m4is_l91805 =[];
 $m4is_n3691 =learndash_get_course_id($m4is_b1249 );
  if(!isset($m4is_l91805[$m4is_n3691])){
$m4is_l91805[$m4is_n3691]=['tags' =>get_post_meta($m4is_n3691, '_is4wp_learndash_drip_feed_override', true ), 'users' =>[]];
 
} if(empty($m4is_l91805[$m4is_n3691]['users'][$m4is_f087])){
if(!empty($m4is_l91805[$m4is_n3691]['tags'])){
$m4is_l91805[$m4is_n3691]['users'][$m4is_f087]=$this->m4is_f683->m4is_w0842(false, ['tags1' =>$m4is_l91805[$m4is_n3691]['tags']]);
 
}else{
$m4is_l91805[$m4is_n3691]['users'][$m4is_f087]=false;
 
}
}return $m4is_l91805[$m4is_n3691]['users'][$m4is_f087]? null : $m4is_n639;
 
}
}

