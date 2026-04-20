<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_b43 {
static 
function m4is_n581(string $m4is_g34786 ='', string $m4is_z62680 =''){
$m4is_z62680 =empty($m4is_z62680)? get_temp_dir(): $m4is_z62680;
 $m4is_g34786 =empty($m4is_g34786)? uniqid(): $m4is_g34786;
 $m4is_d04266 =$m4is_z62680 . $m4is_g34786;
 mkdir($m4is_d04266 );
 $m4is_d04266 =realpath($m4is_d04266 );
 return $m4is_d04266 . '/';
 
}static 
function m4is_u82(WP_Post $m4is_m5907 ){
$m4is_b4068 =$m4is_m5907->ID;
 $m4is_l79562 =get_post_meta($m4is_b4068 );
 $m4is_u450 =['_elementor_css', '_elementor_data', '_elementor_edit_mode', '_elementor_page_settings', '_elementor_template_type', '_elementor_version', '_et_builder_version', '_et_pb_custom_css', '_et_pb_enable_shortcode_tracking', '_et_pb_old_content', '_et_pb_page_layout', '_et_pb_post_hide_nav', '_et_pb_side_nav', '_et_pb_use_builder', '_fl_builder_data_settings', '_fl_builder_data', '_fl_builder_draft_settings', '_fl_builder_draft', '_fl_builder_enabled', '_is4wp_anonymous_only', '_is4wp_any_loggedin_user', '_is4wp_any_membership', '_is4wp_can_comment', '_is4wp_discourage_cache', '_is4wp_force_public', '_is4wp_hide_from_menu', '_is4wp_private_comments', '_is4wp_prohibited_action', '_is4wp_redirect_url', '_iswp_custom_code', '_optimizepress_color_scheme_advanced', '_optimizepress_color_scheme_template', '_optimizepress_exit_redirect', '_optimizepress_fb_share', '_optimizepress_feature_area', '_optimizepress_feature_title', '_optimizepress_footer_area', '_optimizepress_header_layout', '_optimizepress_launch_funnel', '_optimizepress_launch_gateway', '_optimizepress_lightbox', '_optimizepress_membership', '_optimizepress_mobile_redirect', '_optimizepress_page_thumbnail_preset', '_optimizepress_page_thumbnail', '_optimizepress_pagebuilder', '_optimizepress_scripts', '_optimizepress_seo', '_optimizepress_theme', '_optimizepress_typography', '_wp_page_template', ];
 $m4is_m8367 =['signature' =>'Web Power and Light Post Sherpa', 'version' =>1, 'title' =>isset($m4is_l79562['_template_title'][0])? $m4is_l79562['_template_title'][0]: $m4is_m5907->post_title, 'post' =>['post_content' =>$m4is_m5907->post_content, 'post_title' =>$m4is_m5907->post_title, 'post_excerpt' =>$m4is_m5907->post_excerpt, 'comment_status' =>$m4is_m5907->comment_status, 'ping_status' =>$m4is_m5907->ping_status, 'post_type' =>$m4is_m5907->post_type, ], 'meta' =>[], ];
 foreach($m4is_u450 as $m4is_o015){
if(isset($m4is_l79562[$m4is_o015][0])){
$m4is_m8367['meta'][$m4is_o015]=base64_encode($m4is_l79562[$m4is_o015][0]);
 
}
}$m4is_m8367 =json_encode($m4is_m8367);
 return $m4is_m8367;
 
}static 
function m4is_j76(array $m4is_k26 =[]){
if(!count($m4is_k26 )){
return;
 
}require_once ABSPATH .'/wp-admin/includes/file.php';
 $m4is_y41 =wp_upload_dir();
 $m4is_w78135 =$m4is_y41['path']. '/';
 $m4is_p38 =$m4is_w78135 . 'page-export-' . time(). '-' . count($m4is_k26). '.zip';
 $m4is_z6401 ="\n\nPost Sherpa Export\n" . 'Copyright (c) ' . date('Y'). " Web, Power and Light LLC\n\n";
 $m4is_j19870 =new ziparchive;
 $m4is_j19870->open($m4is_p38, ziparchive::CREATE ||ziparchive::OVERWRITE );
 $m4is_j19870->setArchiveComment($m4is_z6401 );
  $m4is_i031 =[];
 foreach($m4is_k26 as $m4is_d07693){
$m4is_m5907 =get_post($m4is_d07693 );
 $m4is_l91805 =self::m4is_u82($m4is_m5907 );
 $m4is_g34786 ='memberium-page-export-' . $m4is_m5907->post_name . '.json';
 $m4is_j19870->addfromstring($m4is_g34786, $m4is_l91805);
 
}$m4is_j19870->close();
  if(!function_exists('media_handle_upload')){
require_once(ABSPATH . 'wp-admin/includes/image.php');
 require_once(ABSPATH . 'wp-admin/includes/file.php');
 require_once(ABSPATH . 'wp-admin/includes/media.php');
 
}$m4is_r8741 =[];
 $m4is_r8741['name']=basename($m4is_p38);
 $m4is_r8741['tmp_name']=$m4is_p38;
 $m4is_s8491 =media_handle_sideload($m4is_r8741, 0, 'Memberium Page Export Created on ' . date('Y-m-d'));
 return $m4is_s8491;
 
}static 
function m4is_l68013(string $m4is_g34786){
if(!class_exists('ZipArchive' )){
return false;
 
}$m4is_f3956 =new ZipArchive;
 
}static 
function m4is_j70(string $m4is_q38914 ='', int $m4is_b4068 =0){
$m4is_q38914 =json_decode($m4is_q38914, true);
 if($m4is_q38914['signature']== 'Web Power and Light Post Sherpa'){
$m4is_m5907 =['ID' =>$m4is_b4068, 'post_title' =>$m4is_q38914['post']['post_title'], 'post_content' =>$m4is_q38914['post']['post_content'], 'post_excerpt' =>$m4is_q38914['post']['post_excerpt'], 'post_author' =>m4is_r83::m4is_c26()->m4is_x66(), 'comment_status' =>$m4is_q38914['post']['comment_status'], 'ping_status' =>$m4is_q38914['post']['ping_status'], 'post_type' =>$m4is_q38914['post']['post_type'], ];
 $m4is_b4068 =wp_insert_post($m4is_m5907, false);
 foreach ($m4is_q38914['meta']as $m4is_o015 =>$m4is_k72 ){
$m4is_k72 =base64_decode($m4is_k72);
 $m4is_k72 =maybe_unserialize($m4is_k72);
 if(is_string($m4is_k72)){
$m4is_k72 =addslashes($m4is_k72);
 
}$m4is_d07693 =add_post_meta($m4is_b4068, $m4is_o015, $m4is_k72, false);
 
}echo 'post id = ', $m4is_b4068, "<br>";
 $m4is_q160 =get_post_meta($m4is_b4068, '_elementor_data', true);
 echo "\n\n\n", $m4is_q160, "\n\n\n";
 die();
 
}die();
 
}
}

