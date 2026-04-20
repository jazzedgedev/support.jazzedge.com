<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_p635 {
private static $m4is_r1546;
 private static $m4is_f4218;
 private static $m4is_r8695;
 private static $m4is_o27056;
  private 
function __construct(){
 
} public static 
function m4is_c961(): void {
self::$m4is_f4218 =m4is_r83::NAMESPACE;
 self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_o27056 =m4is_u7102::m4is_c26();
 self::$m4is_r8695 =self::$m4is_o27056->m4is_d326('parent_tags');
 
} public static 
function m4is_s683($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
$m4is_y642 =['text' =>'<p>%d of %d</p>', ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_f087 =self::$m4is_r1546->m4is_x66();
  if(self::$m4is_r1546->m4is_v461()){
return '[Admin] No Child Accounts';
 
}if(!m4is_q82::m4is_d6758($m4is_f087, 'umbrella', 'is_parent', 0 )){
return '';
 
}$m4is_r32 =m4is_q82::m4is_d6758($m4is_f087, 'umbrella', 'max_children', 0 );
 $m4is_v9046 =self::$m4is_o27056->m4is_u107($m4is_f087 );
 $m4is_o498 =sprintf(_x($m4is_l62046['text'], self::$m4is_f4218 ), $m4is_v9046, $m4is_r32 );
 $m4is_o498 =apply_filters('memberium/shortcodes/' . $m4is_v3458, $m4is_o498 );
  return $m4is_o498;
 
} public static 
function m4is_i6127($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 global $memb_messages;
  $m4is_y642 =['button_text' =>'Add Account',  'form_id' =>'',  'overwrite' =>false, ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
  foreach($m4is_l62046 as $m4is_o015 =>$m4is_k72 ){
  if(!array_key_exists($m4is_o015, $m4is_y642 )){
$m4is_y642[$m4is_o015]=$m4is_k72;
 
}
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['overwrite']=m4is_f61::m4is_d8195($m4is_l62046['overwrite'], false );
  if(self::$m4is_r1546->m4is_v461()){
return '<p>[Admin] Cannot enroll child accounts</p>';
 
}$m4is_f087 =self::$m4is_r1546->m4is_x66();
 if(!m4is_q82::m4is_d6758($m4is_f087, 'umbrella', 'is_parent', 0 )){
return '';
 
}$m4is_o498 ='';
 $m4is_r32 =m4is_q82::m4is_d6758($m4is_f087, 'umbrella', 'max_children', 0 );
 $m4is_v9046 =self::$m4is_o27056->m4is_u107($m4is_f087 );
   if($m4is_v9046 >= $m4is_r32 ){
return sprintf('<p style="subacct_maxed">%s</p>', _x('All child seats are assigned.', 'umbrella_enroll_child', self::$m4is_f4218 ));
 
}$m4is_a510 =[];
 $m4is_r18 ='<select name="actions">';
 $m4is_j15 =[];
  if(is_array($m4is_l62046 )){
foreach($m4is_l62046 as $m4is_l9671 =>$m4is_v586 ){
if(substr($m4is_l9671, 0, 1 )<> 'a' ){
continue;
 
}$m4is_l9671 =intval(substr($m4is_l9671, 1 ));
 if($m4is_l9671 > 0 ){
$m4is_r18 .= sprintf('<option value="%s">%s</option>', $m4is_l9671, $m4is_v586 );
 $m4is_j15[]=$m4is_l9671;
 
}elseif($m4is_l9671 === 0 ){
$m4is_r18 .= sprintf('<option value="">%s</option>', $m4is_v586 );
 
}
}
} $m4is_r18 .= '</select>';
  $m4is_a510 =is_array($m4is_j15 )? implode(',', $m4is_j15 ): '';
  if(empty($m4is_j15 )){
$m4is_r18 .= '';
 
} if(!empty($m4is_t09761 )){
$m4is_t09761 =str_ireplace('%%action.dropdown%%', $m4is_r18, $m4is_t09761 );
 
} $m4is_u897 =base64_encode(serialize(['allowed_actions' =>$m4is_a510, 'custom' =>!empty($m4is_t09761 ), 'overwrite' =>$m4is_l62046['overwrite'], 'contact_id' =>self::$m4is_r1546->m4is_z56(), ]));
  $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u897 );
  if(!empty($memb_messages['flash']['create_child_result'])){
$m4is_o498 .= $memb_messages['flash']['create_child_result'];
 
} $m4is_o498 .= '<form method="POST" action="" id="' . $m4is_l62046['form_id']. '">';
  $m4is_o498 .= '<input type="hidden" name="formtype" value="childenroll">';
 $m4is_o498 .= "<input type=\"hidden\" name=\"params\" value=\"{$m4is_u897
}\">";
 $m4is_o498 .= "<input type=\"hidden\" name=\"digital_signature\" value=\"{$m4is_o31859
}\">";
  $m4is_o498 .= wp_nonce_field('memb_childenroll', '_wpnonce', true, false );
  if(empty($m4is_t09761 )){
$m4is_o498 .= sprintf('<label>%s</label><input name="FirstName" type="text" value="" required="required"> *<br />', _x('First Name:', 'umbrella_enroll_child', self::$m4is_f4218 ));
 $m4is_o498 .= sprintf('<label>%s</label><input name="LastName" type="text" value="" required="required"> *<br />', _x('Last Name:', 'umbrella_enroll_child', self::$m4is_f4218 ));
 $m4is_o498 .= sprintf('<label>%s</label><input type="email" value="" name="Email" required="required"> *<br />', _x('Email Address:', 'umbrella_enroll_child', self::$m4is_f4218 ));
 $m4is_o498 .= sprintf('<label>%s</label><input type="tel" value="" name="Phone3"><br />', _x('Mobile Number:', 'umbrella_enroll_child', self::$m4is_f4218 ));
 
} else{
$m4is_o498 .= do_shortcode($m4is_t09761 );
 
} $m4is_o498 .= sprintf('<label></label><input type="submit" value="%s" name=""><br />', $m4is_l62046['button_text']);
  $m4is_o498 .= '</form>';
  $memb_messages['flash']['create_child_result']='';
  return $m4is_o498;
 
}      public static 
function m4is_w6320($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(!class_exists('SFWD_LMS' )||!method_exists('SFWD_LMS', 'get_course_info' )){
if(current_user_can('manage_options' )){
return '<p>LearnDash SFWD_LMS::get_course_info() method is missing.</p>';
 
}return '<p>Course information is not available.</p>';
 
}$m4is_z71834 =self::$m4is_r1546->m4is_x66();
 if(!$m4is_z71834 ){
return '';
 
}$m4is_y642 =['user_id' =>isset($_GET['user_id'])? (int) $_GET['user_id']: 0, 'contact_id' =>isset($_GET['contact_id'])? (int) $_GET['contact_id']: 0, ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
  if(!$m4is_z71834 ){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_r205 =get_current_user_id();
 $m4is_a62514 =$m4is_l62046['user_id']? $m4is_l62046['user_id']: m4is_p40::m4is_i6158($m4is_l62046['contact_id']);
 if(!$m4is_a62514 ){
return '<p>No valid user to display.</p>';
 
}$m4is_c02793 =self::$m4is_r1546->m4is_v461();
 $m4is_c02793 =$m4is_c02793 ||self::$m4is_o27056->m4is_t58904($m4is_r205, $m4is_a62514 );
  if(!$m4is_c02793 ){
$m4is_q95421 =self::$m4is_o27056->m4is_n681();
 $m4is_c02793 =in_array($m4is_l62046['contact_id'], $m4is_q95421 );
 
} if(!$m4is_c02793 ){
$m4is_c02793 =($m4is_l62046['contact_id']== m4is_q82::m4is_d6758($m4is_z71834, 'memb_user', 'crm_id', 0 ));
 
} if($m4is_c02793 ){
$m4is_f087 =m4is_p40::m4is_i6158($m4is_l62046['contact_id']);
  if($m4is_f087 ){
$m4is_l62046 =['registered_show_thumbnail' =>'false', 'user_id' =>$m4is_f087 ];
 return SFWD_LMS::get_course_info($m4is_f087, $m4is_l62046 );
 
}else{
$m4is_c02793 =false;
 
}
} if(!$m4is_c02793 ){
return "<p>Access not permitted to this user's course records</p>";
 
}return '';
 
} public static 
function m4is_s6532($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 $m4is_y642 =['limit' =>isset($_GET['limit'])? (int) $_GET['limit']: 0,  'page' =>isset($_GET['pg'])? (int) $_GET['pg']: 1,  'parent_id' =>self::$m4is_r1546->m4is_z56(),  ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
  if(!self::$m4is_r1546->m4is_m2480(self::$m4is_r8695 )){
return '';
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  if($m4is_l62046['limit']== 0 ){
return '';
 
} $m4is_l62046['count']=self::$m4is_o27056->m4is_z04271();
  $m4is_l62046['page']=$m4is_l62046['page']< 1 ? 1 : $m4is_l62046['page'];
  $m4is_f602 =$m4is_l62046['count']> 0 ? (int) ceil($m4is_l62046['count']/ $m4is_l62046['limit']): 1;
  if($m4is_f602 < 2 ){
return '';
 
} $m4is_i0795 =add_query_arg('pg', ($m4is_l62046['page']- 1));
 $m4is_f13965 =add_query_arg('pg', ($m4is_l62046['page']+ 1 ));
  $m4is_l62046['page']=($m4is_l62046['page']< 1 )? 1 : $m4is_l62046['page'];
  $m4is_o498 ='<div class="memberium_umbrella_nav" style="width:100%;">';
  if($m4is_l62046['page']> 1 ){
$m4is_o498 .= '<span class="memberium_umbrella_nav_previous" style="margin-left:30px;margin-right:30px;"><a href="' . $m4is_i0795 . '">&lt;Previous&gt;</a></span>';
 
} $m4is_o498 .= '<span class="">Page ' . ($m4is_l62046['page']). ' of ' . ($m4is_f602 ). '</span>';
  if($m4is_l62046['page']< $m4is_f602 ){
$m4is_o498 .= '<span class="memberium_umbrella_nav_next" style="margin-left:30px;margin-right:30px;"><a href="' . $m4is_f13965 . '">&lt;Next&gt;</a></span>';
 
} $m4is_o498 .= '</div>';
  return $m4is_o498;
 
} public static 
function m4is_x10($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 $m4is_y642 =['can_disconnect' =>'y',  'limit' =>isset($_GET['limit'])? (int) $_GET['limit']: 0,  'page' =>isset($_GET['pg'])? (int) $_GET['pg']: 1,  'parent_id' =>self::$m4is_r1546->m4is_z56(),  ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
  if(!self::$m4is_r1546->m4is_m2480(self::$m4is_r8695 )){
return '';
 
} m4is_j586::m4is_x7134();
  $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  $m4is_l62046['can_disconnect']=m4is_f61::m4is_d8195($m4is_l62046['can_disconnect'], true );
 $m4is_l62046['page']=$m4is_l62046['page']> 0 ? $m4is_l62046['page']: 1;
 $m4is_l62046['page']=$m4is_l62046['page']< 1 ? 1 : $m4is_l62046['page'];
 $m4is_l62046['parent_id']=(int) $m4is_l62046['parent_id'];
 $m4is_e53706 =($m4is_l62046['page']- 1 )* $m4is_l62046['limit'];
 $m4is_o498 ='';
 $m4is_h850 =m4is_p40::m4is_i6158($m4is_l62046['parent_id']);
 if(empty($m4is_l62046['parent_id'])){
return '';
 
}m4is_u81::m4is_d38602($m4is_h850 );
  if($m4is_l62046['limit']){
 $m4is_y66291 =['contact_id' =>$m4is_l62046['parent_id'],  'limit' =>$m4is_l62046['limit'],  'offset' =>$m4is_e53706  ];
  $m4is_v30712 =self::$m4is_o27056->m4is_n681($m4is_y66291 );
 
}else{
 $m4is_v30712 =self::$m4is_o27056->m4is_n681();
 
} if(!empty($m4is_t09761 )){
 foreach($m4is_v30712 as $m4is_h21895 ){
 $m4is_p6925 =$m4is_t09761;
  $m4is_v2396 ='<form method="post">';
 $m4is_v2396 .= '<input type="hidden" name="formtype" value="childdisconnect">';
  $m4is_v2396 .= '<input type="hidden" name="contact_id" value="' . $m4is_h21895 . '">';
  $m4is_v2396 .= '<input type="hidden" name="signature" value="' . md5(wp_salt('nonce' ). $m4is_h21895). '">';
  $m4is_v2396 .= '<input type="submit" value="Disconnect">';
  $m4is_v2396 .= '</form>';
  $m4is_p6925 =str_ireplace('%%contact.id%%', $m4is_h21895, $m4is_p6925 );
  $m4is_p6925 =str_ireplace('{{contact.id}}', $m4is_h21895, $m4is_p6925 );
  $m4is_p6925 =str_ireplace('%%disconnect.button%%', $m4is_v2396, $m4is_p6925 );
  $m4is_o498 .= $m4is_p6925;
 
} $m4is_o498 =do_shortcode($m4is_o498 );
 
} else{
 $m4is_o498 ="<table style='width:100%;'>";
 $m4is_o498 .= "<tr>";
  $m4is_o498 .= '<td>First Name</td>';
 $m4is_o498 .= '<td>Last Name</td>';
 $m4is_o498 .= '<td>Email</td>';
 $m4is_o498 .= '<td>Mobile Phone</td>';
 $m4is_o498 .= '<td></td>';
  $m4is_o498 .= "</tr>";
  if(count($m4is_v30712 )){
 foreach($m4is_v30712 as $m4is_h21895 ){
 $m4is_i935 =m4is_p40::m4is_p67($m4is_h21895 );
  $m4is_o498 .= '<tr>';
  $m4is_o498 .= sprintf('<td>%s</td>', $m4is_i935['FirstName']?? '' );
 $m4is_o498 .= sprintf('<td>%s</td>', $m4is_i935['LastName']?? '' );
 $m4is_o498 .= sprintf('<td><a href="mailto:%s">%s</a></td>', $m4is_i935['Email'], $m4is_i935['Email']);
 $m4is_o498 .= sprintf('<td>%s</td>', $m4is_i935['Phone3']?? '' );
 $m4is_o498 .= '<td>';
  if($m4is_l62046['can_disconnect']){
 $m4is_o498 .= '<form method="post">';
 $m4is_o498 .= '<input type="hidden" name="formtype" value="childdisconnect">';
  $m4is_o498 .= '<input type="hidden" name="contact_id" value="' . $m4is_i935['Id']. '">';
  $m4is_o498 .= '<input type="hidden" name="signature" value="' . md5(wp_salt('nonce' ). $m4is_i935['Id']). '">';
  $m4is_o498 .= '<input type="submit" value="Disconnect">';
  $m4is_o498 .= '</form>';
 
} $m4is_o498 .= '</td>';
 $m4is_o498 .= '</tr>';
 
}
} $m4is_o498 .= "</table>";
 
} return $m4is_o498;
 
} public static 
function m4is_e42356($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 m4is_j586::m4is_x7134();
  $m4is_y642 =['course_class' =>'umbrella_lms_dashboard_course',  'course_style' =>'margin-bottom:6px;padding:20px 10px 20px 10px;background-color:#eee;',  'date_format' =>'F j, Y',  'template' =>$m4is_v3458,  'user_class' =>'umbrella_lms_dashboard_user',  'user_style' =>'margin-bottom:12px;padding:20px 10px 20px 10px;background-color:#eee;',  ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
  if(!self::$m4is_r1546->m4is_m2480(self::$m4is_r8695 )){
return '';
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_r403 =[];
  $m4is_w8256 =[];
  $m4is_q95421 =self::$m4is_o27056->m4is_z45812();
   if(count($m4is_q95421 )){
 $m4is_y66291 =['fields' =>['ID',  'display_name',  'user_email'  ], 'include' =>$m4is_q95421,  'order' =>'ASC',  'orderby' =>'display_name',  ];
  $m4is_j69 =new WP_User_Query($m4is_y66291 );
  $m4is_r403 =$m4is_j69->results;
 
} foreach($m4is_r403 as $m4is_l9671 =>$m4is_l17096 ){
 $m4is_t6056 =$m4is_l17096->ID;
  if(function_exists('learndash_user_get_enrolled_courses' )){
 $m4is_y563 =learndash_user_get_enrolled_courses($m4is_t6056 );
  sort($m4is_y563 );
  $m4is_c6320 =get_user_meta($m4is_t6056, '_sfwd-course_progress', true );
  $m4is_r403[$m4is_l9671]->courses =[];
  foreach($m4is_y563 as $m4is_e95021 ){
 if(get_post_status($m4is_e95021 )== 'publish' &&get_post_type($m4is_e95021 )== 'sfwd-courses' ){
 $m4is_m5907 =get_post($m4is_e95021 );
  $m4is_d4686 =learndash_get_course_steps_count($m4is_e95021 );
  $m4is_m18537 =isset($m4is_c6320[$m4is_e95021]['last_id'])? $m4is_c6320[$m4is_e95021]['last_id']: 0;
  $m4is_d56407 =learndash_course_get_completed_steps($m4is_t6056, $m4is_e95021 );
  $m4is_m83520 =empty($m4is_d4686 )? 0 : (int) ($m4is_d56407 / $m4is_d4686 * 100 );
  $m4is_q4296 =function_exists('learndash_user_get_course_completed_date' )? (int) learndash_user_get_course_completed_date($m4is_t6056, $m4is_e95021 ): 0;
  $m4is_h07523 =empty($m4is_m18537 )? 'None' : get_the_title($m4is_m18537 );
  $m4is_y06417 =new stdclass;
 $m4is_y06417->course_title =$m4is_m5907->post_title;
 $m4is_y06417->completed_timestamp =$m4is_q4296;
 $m4is_y06417->course_steps =$m4is_d4686;
 $m4is_y06417->completed_steps =$m4is_d56407;
 $m4is_y06417->step_title =$m4is_h07523;
 $m4is_y06417->percentage =$m4is_m83520;
 $m4is_y06417->access_expired =(int) ld_course_access_expired($m4is_e95021, $m4is_t6056 );
 $m4is_y06417->expiration_timestamp =(int) ld_course_access_expires_on($m4is_e95021, $m4is_t6056 );
  $m4is_r403[$m4is_l9671]->courses[$m4is_e95021]=$m4is_y06417;
 
}
}
} 
}  return m4is_f61::m4is_l0659($m4is_v3458, $m4is_l62046, $m4is_t09761, $m4is_v3458, $m4is_r403 );
 
} public static 
function m4is_o35($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 m4is_j586::m4is_x7134();
  $m4is_y642 =['button_class' =>'', 'button_text' =>'Transfer Points', 'field' =>'', 'max' =>0, 'min' =>1, 'redirect' =>'', ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
 $m4is_h21895 =self::$m4is_r1546->m4is_z56();
  if($m4is_h21895 == 0 ){
return '';
 
}if(!self::$m4is_r1546->m4is_m2480(self::$m4is_r8695 )){
return '';
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['field']=strtolower(trim($m4is_l62046['field']));
  if(empty($m4is_l62046['field'])){
return '';
 
} $m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_f95063 =(float) m4is_q82::m4is_k660($m4is_f087, 'contact', $m4is_l62046['field'], 0 );
 $child_ids =self::$m4is_o27056->m4is_z45812();
  $m4is_y89162 =[];
 $params =['field' =>$m4is_l62046['field'], 'max_points' =>$m4is_f95063, 'pcid' =>$m4is_h21895, 'redirect' =>$m4is_l62046['redirect'], ];
  if(count($child_ids )){
$m4is_y66291 =['fields' =>['ID', 'display_name', 'user_email' ], 'include' =>$child_ids, 'order' =>'ASC', 'orderby' =>'display_name', ];
 $m4is_j69 =new WP_User_Query($m4is_y66291 );
 $m4is_y89162 =$m4is_j69->results;
 
} $m4is_l91805 =new stdclass;
 $m4is_l91805->params =base64_encode(serialize($params ));
 $m4is_l91805->users =$m4is_y89162;
 $m4is_l91805->points =$m4is_f95063;
 $m4is_l91805->headers =wp_nonce_field($m4is_l91805->params, 'umbrella_transfer_points_wpnonce', true, false );
 $m4is_l91805->headers .= "<input type='hidden' name='signature' value='{$m4is_l91805->params
}'>";
 $m4is_l91805->headers .= "<input type='hidden' name='formtype' value='umbrella_points_transfer'>";
 $m4is_l91805->redirect =$m4is_l62046['redirect'];
  return m4is_f61::m4is_l0659($m4is_v3458, $m4is_l62046, $m4is_t09761, $m4is_v3458, $m4is_l91805 );
 
}    public static 
function m4is_n86472($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
 m4is_j586::m4is_x7134();
  $m4is_y642 =['button_text' =>'Download CSV', 'button_class' =>'', 'filter_name' =>'', ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
  if(($m4is_h21895 =self::$m4is_r1546->m4is_z56())== 0){
return '';
 
} if(!self::$m4is_r1546->m4is_m2480(self::$m4is_r8695 )){
return '';
 
} $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
  $m4is_u897 =['filter_name' =>$m4is_l62046['filter_name'], ];
  $m4is_u897 =base64_encode(serialize($m4is_u897 ));
  $m4is_o498 ='';
  $m4is_o498 .= '<form method=post>';
  $m4is_o498 .= wp_nonce_field(-1, 'umbrella_download_csv_wpnonce', true, false );
  $m4is_o498 .= "<input type=hidden name='params' value='{$m4is_u897
}'>";
 $m4is_o498 .= "<input type=hidden name='formtype' value='umbrella_csv_download'>";
  $m4is_o498 .= "<input type=submit class='' name='' value='{$m4is_l62046['button_text']
}'>";
  $m4is_o498 .= '</form>';
  return $m4is_o498;
 
}    public static 
function m4is_e81($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
static $m4is_n9814 =false;
 $m4is_y642 =['team_id' =>'', ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_z30692 =array_filter(array_map('trim', explode(',', $m4is_l62046['team_id'])));
 $m4is_i341 =get_current_user_id();
 if(!self::$m4is_r1546->m4is_m2480(self::$m4is_r8695 )){
return '';
 
}$m4is_r66 =m4is_l870::m4is_h5626($m4is_i341 );
 if(!empty($m4is_l62046['team_id'])){
$m4is_r66 =array_intersect($m4is_r66, $m4is_z30692 );
 
}if(empty($m4is_r66 )){
return '<p>No matching teams.</p>';
 
}$m4is_b6438 =self::$m4is_o27056->m4is_y9164($m4is_i341 );
 foreach($m4is_r66 as $m4is_z30692 ){
$m4is_j31 =m4is_l870::m4is_k2074($m4is_z30692 );
 $m4is_r32 =m4is_l870::m4is_d078($m4is_z30692 );
 $m4is_v9046 =m4is_l870::m4is_p610($m4is_z30692 );
 $m4is_f087 =get_current_user_id();
 $m4is_k6739 =get_the_title($m4is_z30692 );
 $m4is_o31859 =sha1(wp_salt('nonce' ). $m4is_z30692 );
 echo <<<HTMLBLOCK
				<form method="post">
					<input type="hidden" name="formtype" value="memberium/group-accounts/team/member/update">
					<input type="hidden" name="team_id" value="{$m4is_z30692
}">
					<input type="hidden" name="user_id" value="{$m4is_f087
}">
					<input type="hidden" name="signature" value="{$m4is_o31859
}">
					<div>
						<p>
							Team {$m4is_k6739
} ({$m4is_v9046
}/{$m4is_r32
})
						</p>
							<select name="child_uids[]" class="js-example-basic-single" multiple="multiple" style="width:90%;">
			HTMLBLOCK;
 if(!empty($m4is_b6438 )){
foreach($m4is_b6438 as $m4is_x09186 ){
$m4is_a437 =in_array($m4is_x09186, $m4is_j31 )? ' selected="selected" ' : '';
 $m4is_l17096 =get_userdata($m4is_x09186 );
 $m4is_k52736 =sprintf('%s %s (%s)', get_user_meta($m4is_x09186, 'first_name', true ), get_user_meta($m4is_x09186, 'last_name', true ), $m4is_l17096->user_email );
 printf('<option value="%d" %s > %s </option>', $m4is_x09186, $m4is_a437, $m4is_k52736 );
 
}
}echo <<<HTMLBLOCK

							</select>
						<p>
						<input type="submit" style="margin-top:12px;" value="Save">
					</div>
				</form>
			HTMLBLOCK;
 
}echo <<<HTMLBLOCK
			<script>
				jQuery(document).ready(function() {
					jQuery('.js-example-basic-single').select2();
				});
			</script>


		HTMLBLOCK;
 if(!$m4is_n9814 ){
echo <<<HTMLBLOCK
				<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
				<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
			HTMLBLOCK;
 
}return '';
 
}private static 
function m4is_f62680(){

}
}

