<?php

/**
 * Copyright (c) 2017-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_l032 {
   private static 
function m4is_e7810(){
return apply_filters('memberium/lms/course_type', 'page');
 
}private static 
function m4is_r10(){
return apply_filters('memberium/lms/course_category', 'category');
 
}private static 
function m4is_i8649(){
return apply_filters('memberium/lms/course_tag', 'post_tag');
 
}   static 
function m4is_d674($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 =''){
self::$m4is_j83564 =++self::$m4is_j83564;
 $m4is_c57 =self::m4is_e7810();
 $m4is_x07466 =['container_template' =>'coursegrid_container', 'grid_template' =>'coursegrid_item' ];
 $m4is_y642 =['container_template' =>$m4is_x07466['container_template'],  'css_class' =>'',  'css_id' =>'',  'grid_template' =>$m4is_x07466['grid_template'],  'order' =>'asc',  'sort' =>'course_order',  'post_ids' =>'',  'post_type' =>$m4is_c57,  'posts_per_page' =>-1,  'categories' =>'',  'tags' =>'',  'taxonomy_compare' =>'AND',  'columns' =>3,  'mobile_cols' =>1,  'tablet_cols' =>2,  'widescreen_cols' =>3,  'no_breakpoints' =>0,  'progress_bar' =>1,  'no_css' =>0  ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =wp_parse_args($m4is_l62046, $m4is_y642);
 $m4is_q485 =$m4is_l62046['post_type']=empty($m4is_l62046['post_type'])? $m4is_c57 : $m4is_l62046['post_type'];
 add_filter('memberium/lms/course/item/data', [__CLASS__, 'course_grid_item_data'], PHP_INT_MAX - 1, 2);
  foreach ($m4is_x07466 as $m4is_r526 =>$m4is_n246){
$m4is_i6235 =empty($m4is_l62046[$m4is_r526])? false : m4is_f61::m4is_q7642($m4is_l62046[$m4is_r526]);
  if(!$m4is_i6235){
if(m4is_r83::m4is_c26()->m4is_v461()){
$m4is_q847 =__('Error : template missing for %s', 'memberium');
 return "<p>".sprintf($m4is_q847, $m4is_r526 )."</p>";
 
}return '';
 
}else{
if($m4is_r526 === 'grid_template'){
$m4is_p28409 =$m4is_i6235;
 
}
}
} $m4is_g2361 =['post_type' =>$m4is_q485, 'posts_per_page' =>$m4is_l62046['posts_per_page'], ];
  $m4is_f65 =self::m4is_m72460('post_ids', $m4is_l62046);
 if($m4is_f65){
$m4is_g2361['post__in']=$m4is_f65;
 
} $m4is_u0926 =self::m4is_l2983($m4is_l62046);
 if($m4is_u0926){
$m4is_g2361['tax_query']=$m4is_u0926;
 
}  elseif($m4is_q485 == 'post' ||$m4is_q485 === 'page'){
if(m4is_r83::m4is_c26()->m4is_v461()){
$m4is_q847 =__('Error : memb_coursegrid shortcode for %ss requires a category or tag filter to display.', 'memberium');
 return "<p>" . sprintf($m4is_q847, $m4is_q485). "</p>";
 
}return '';
 
} $m4is_o36 =!empty($m4is_l62046['order'])? strtolower($m4is_l62046['order']): 'asc';
 $m4is_o36 =!in_array($m4is_o36, ['asc', 'desc'])? 'asc' : $m4is_o36;
 $m4is_z68613 =!empty($m4is_l62046['sort'])? strtolower($m4is_l62046['sort']): 'course_order';
 $m4is_w93062 =in_array($m4is_z68613, ['title', 'date']);
 if($m4is_w93062){
$m4is_g2361['orderby']=$m4is_z68613;
 $m4is_g2361['order']=$m4is_o36;
 
} $m4is_v76912 =self::m4is_w64019($m4is_g2361, $m4is_l62046);
 $m4is_h546 =$m4is_v76912 ? $m4is_v76912->get_posts(): false;
 $m4is_w8256 =[];
 if($m4is_h546){
foreach ($m4is_h546 as $m4is_b4068){
$m4is_w8256[]=apply_filters("memberium/lms/course/item/data", [], $m4is_b4068);
 
} if(!$m4is_w93062){
if($m4is_z68613 === 'course_order'){
add_filter('memberium/course/grid/sort', [__CLASS__, 'course_grid_course_order_sort'], PHP_INT_MAX - 1, 2);
 
}$m4is_w8256 =apply_filters("memberium/course/grid/sort", $m4is_w8256, $m4is_z68613);
 if(!empty($m4is_w8256)&&$m4is_o36 === 'desc'){
$m4is_w8256 =array_reverse($m4is_w8256);
 
}
}
}$m4is_l74 =['css_class' =>'', 'css_id' =>''];
  $m4is_e8013 ='memberium-course-grid';
  foreach ($m4is_l74 as $m4is_l28 =>$m4is_p62690){
if(!empty(esc_attr($m4is_l62046[$m4is_l28]))){
$m4is_l74[$m4is_l28]=esc_attr($m4is_l62046[$m4is_l28]);
 
}
}if((int)$m4is_l62046['no_breakpoints']> 0){
$m4is_m3804 ='';
 
}else{
$m4is_m3804 =self::m4is_u2183($m4is_l62046, $m4is_e8013);
 
}$m4is_e8013 .= empty($m4is_l74['css_class'])? "" : " {$m4is_l74['css_class']
}";
 $m4is_y365 =empty($m4is_l74['css_id'])? "" : " {$m4is_l74['css_id']
}";
 $m4is_q698 =['grid-number' =>self::$m4is_j83564, 'grid-items' =>$m4is_w8256, 'grid-item-template' =>$m4is_p28409, 'query_args' =>$m4is_g2361, 'wrapper-id' =>$m4is_y365, 'wrapper-class' =>$m4is_e8013, 'wrapper-col-styles' =>$m4is_m3804 ];
  if((int) $m4is_l62046['no_css']< 1){
wp_enqueue_style('memb_coursegrid_css');
 
} return m4is_f61::m4is_l0659($m4is_x07466['container_template'], $m4is_l62046, $m4is_t09761, $m4is_v3458, $m4is_q698);
 
} static 
function m4is_w64019($m4is_y66291, $m4is_l62046){
$m4is_y642 =['post_type' =>self::m4is_e7810(), 'post_status' =>'publish', 'posts_per_page' =>-1, 'orderby' =>'date', 'order' =>'ASC', 'fields' =>'ids' ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642);
 $m4is_q485 =$m4is_y66291['post_type'];
 $m4is_y66291 =apply_filters('memberium/lms/query/args', $m4is_y66291, $m4is_l62046);
 if(empty($m4is_y66291)){
return false;
 
}$m4is_v76912 =new WP_Query($m4is_y66291 );
 wp_reset_postdata();
 return ($m4is_v76912->have_posts())? $m4is_v76912 : false;
 
} static 
function m4is_l2983($m4is_u897){
$m4is_u0926 =[];
 $m4is_q485 =$m4is_u897['post_type'];
 $m4is_g285 =($m4is_q485 === self::m4is_e7810());
  $m4is_x69523 =self::m4is_m72460('categories', $m4is_u897);
 if($m4is_x69523){
$m4is_u0926[]=['taxonomy' =>$m4is_g285 ? self::m4is_r10(): 'category', 'field' =>'term_id', 'terms' =>$m4is_x69523, 'operator' =>'IN', 'include_children' =>false, ];
 
}$m4is_l9321 =self::m4is_m72460('tags', $m4is_u897);
 if($m4is_l9321){
$m4is_u0926[]=['taxonomy' =>($m4is_g285)? self::m4is_i8649(): 'post_tag', 'field' =>'term_id', 'terms' =>$m4is_l9321, 'operator' =>'IN' ];
 
}if(!empty($m4is_u0926)&&count($m4is_u0926)> 1){
$m4is_p14 ='AND';
 if(!empty($m4is_u897['tax_compare'])){
$m4is_p14 =strtoupper(trim($m4is_u897['tax_compare']));
 if($m4is_p14 !== 'OR' ||$m4is_p14 !== 'AND'){
$m4is_p14 ='AND';
 
}
}$m4is_u0926[]=['relation' =>$m4is_p14 ];
 
}return empty($m4is_u0926)? false : $m4is_u0926;
 
} static 
function course_grid_item_data(array $m4is_l91805, int $m4is_d07693){
$m4is_r1692 =get_post_meta($m4is_d07693, '_memberium/coursegrid/config', true);
 $m4is_r1692 =$m4is_r1692 ? $m4is_r1692 : [];
 $m4is_i3208 =empty($m4is_r1692['excerpt'])? '' : html_entity_decode(stripslashes($m4is_r1692['excerpt']));
  $m4is_x66480 =['ID' =>$m4is_d07693, 'title' =>get_the_title($m4is_d07693), 'excerpt' =>$m4is_i3208, 'url' =>get_the_permalink($m4is_d07693), 'status' =>'unlocked', 'progress' =>0, 'access' =>1, 'order' =>empty($m4is_r1692['order'])? 0 : (int)$m4is_r1692['order']];
 $m4is_l91805 =wp_parse_args($m4is_l91805, $m4is_x66480);
 $m4is_l91805['thumbnails']=self::m4is_j6432($m4is_d07693, $m4is_l91805, $m4is_r1692);
  if(!(int)$m4is_l91805['access']> 0){
$m4is_l91805['status_text']=_x('Not Enrolled', 'course_grid_status', 'memberium');
 $m4is_l91805['progress_text']=_x('Locked', 'course_grid_progress', 'memberium');
 $m4is_l91805['button_text']=_x('Locked', 'course_grid_button', 'memberium');
 $m4is_l91805['thumbnail']=$m4is_l91805['thumbnails']['locked'];
 $m4is_l91805['url']=!empty($m4is_r1692['locked_url'])? $m4is_r1692['locked_url']: '';
 
}else{
$m4is_c6320 =(int)$m4is_l91805['progress'];
 $m4is_l91805['thumbnail']=$m4is_l91805['thumbnails']['unlocked'];
 $m4is_l91805['status_text']=_x('Enrolled', 'course_grid_status', 'memberium');
  if($m4is_c6320 > 0 &&$m4is_c6320 < 100){
$m4is_l91805['progress_text']=sprintf(_x('%d%% Completed', 'course_grid_progress', 'memberium'), $m4is_c6320);
 $m4is_l91805['button_text']=_x("Continue", 'course_grid_button', 'memberium');
 
} elseif($m4is_c6320 >= 100){
$m4is_l91805['status']='completed';
 $m4is_l91805['status_text']=_x('Completed', 'course_grid_status', 'memberium');
 $m4is_l91805['progress_text']=_x('Completed', 'course_grid_progress', 'memberium');
 $m4is_l91805['button_text']=_x('Completed', 'course_grid_button', 'memberium');
 
} else{
if($m4is_l91805['status']=== 'not_enrolled' ){
$m4is_l91805['status']='unlocked';
 $m4is_l91805['status_text']=_x('Not Enrolled', 'course_grid_status', 'memberium');
 
}$m4is_l91805['progress_text']=_x('Not Started', 'course_grid_progress', 'memberium');
 $m4is_l91805['button_text']=_x('Start', 'course_grid_button', 'memberium');
 
}
}return $m4is_l91805;
 
}static 
function m4is_j6432($m4is_d07693, $m4is_l91805, $m4is_r1692){
$m4is_t68725 =get_post_thumbnail_id($m4is_d07693);
 $m4is_z0261 =get_the_post_thumbnail_url($m4is_d07693, 'medium');
 $m4is_x265 =[];
 $m4is_c31 =['unlocked', 'locked' ];
 if(empty($m4is_z0261)){
$m4is_z0261 =plugin_dir_url(MEMBERIUM_HOME). "css/memberium-default-course.svg";
 
}foreach ($m4is_c31 as $m4is_k72){
$m4is_e456 =!empty($m4is_r1692[$m4is_k72])? $m4is_r1692[$m4is_k72]: [];
 $m4is_e98510 =!empty($m4is_e456['url'])? esc_url($m4is_e456['url']): '';
 $m4is_t64375 =!empty($m4is_e456['id'])? (int)$m4is_e456['id']: 0;
 $m4is_n66231 =!empty($m4is_t64375)? wp_get_attachment_image_srcset($m4is_t64375): '';
 if(empty($m4is_e98510)){
$m4is_e98510 =$m4is_z0261;
 $m4is_t64375 =$m4is_t68725;
 $m4is_n66231 =$m4is_z0261;
 
} $m4is_x265[$m4is_k72]=['src' =>$m4is_e98510, 'id' =>$m4is_t64375, 'srcset' =>$m4is_n66231 ];
 
}return $m4is_x265;
 
}static 
function m4is_u2183($m4is_u897, $m4is_e8013){
$m4is_m3804 ="<style>";
 $m4is_p56147 =['mobile_cols' =>['col' =>1 ], 'tablet_cols' =>['col' =>2, 'break' =>768 ], 'columns' =>['col' =>3, 'break' =>992 ], 'widescreen_cols' =>['col' =>3, 'break' =>1312 ]];
      foreach ($m4is_p56147 as $m4is_p56147 =>$m4is_y642){
$m4is_a37 =(int)esc_attr($m4is_u897[$m4is_p56147]);
 $m4is_a37 =empty($m4is_a37)? $m4is_y642['col']: $m4is_a37;
  if($m4is_p56147 != 'mobile_cols'){
$m4is_l046 =(int)esc_attr(apply_filters("memberium/lms/breakpoint/pixel/{$m4is_p56147
}", $m4is_y642['break']));
 $m4is_l046 =empty($m4is_l046)? $m4is_y642['break']: $m4is_l046;
 $m4is_m3804 .= "@media (min-width: {$m4is_l046
}px) {";
 
} $m4is_m3804 .= ".{$m4is_e8013
} {";
 if($m4is_p56147 === 'mobile_cols'){
$m4is_m3804 .= "margin:0 auto;display:grid;grid-gap:2em;";
 
}$m4is_m3804 .= "grid-template-columns: repeat({$m4is_a37
}, 1fr);";
 $m4is_m3804 .= "}";
  $m4is_m3804 .= ($m4is_p56147 != 'mobile_cols' )? "}" : "";
  
}$m4is_m3804 .= "</style>";
 return $m4is_m3804;
 
} static 
function m4is_m72460($m4is_l9671, $m4is_u897){
if(is_array($m4is_u897)&&is_string($m4is_l9671)&&!empty($m4is_u897[$m4is_l9671])){
$m4is_k4693 =$m4is_u897[$m4is_l9671];
 $m4is_e09 =array_unique(explode(',', trim($m4is_k4693, ',')));
 $m4is_e09 =array_values(array_filter($m4is_e09, 'is_numeric' ));
 return (!empty($m4is_e09))? $m4is_e09 : false;
 
}return false;
 
} static 
function course_grid_course_order_sort($m4is_w8256, $m4is_z68613){
if($m4is_z68613 !== 'course_order' ||empty($m4is_w8256)){
return $m4is_w8256;
 
}$m4is_n13 =['unlocked' =>1, 'completed' =>2, 'locked' =>3 ];
 usort($m4is_w8256, function ($m4is_l1940, $m4is_h6256)use($m4is_n13){
 if($m4is_l1940['order']=== $m4is_h6256['order']){
 if($m4is_l1940['status']=== $m4is_h6256['status']){
 if($m4is_l1940['status']=== 'unlocked'){
return $m4is_l1940['progress']< $m4is_h6256['progress']? 1 : -1;
 
}else{
return 1;
 
}
} else{
return $m4is_n13[$m4is_l1940['status']]> $m4is_n13[$m4is_h6256['status']]? 1 : -1;
 
}
} return $m4is_l1940['order']> $m4is_h6256['order']? 1 : -1;
 
});
 return $m4is_w8256;
 
}static $m4is_j83564 =0;
 private 
function __construct(){

}
}

