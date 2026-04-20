<?php

/**
 * Copyright (c) 2012-2024 by David Bullock / Web Power and Light
 * All rights reserved.
 */


 class_exists('m4is_h68' )||die();
 final 
class m4is_l10524 {
private $m4is_r1546;
 private $m4is_b3516;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_d4861();
 
} private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_b3516 =m4is_s6729::m4is_c26();
 
} private 
function m4is_d4861(){
add_action('admin_init', [$this, 'm4is_e067']);
 add_action('manage_posts_custom_column', [$this, 'm4is_i3918'], 10, 2 );
 add_filter('manage_posts_columns', [$this, 'm4is_k5467']);
 add_filter('memberium/modules/active/names', [$this, 'm4is_f6528'], 10, 1 );
 add_filter('memberium/wpadmin/allow', [$this, 'm4is_t92561']);
  
}    public 
function m4is_e067(){
$m4is_q485 =$this->m4is_b3516->m4is_m6180();
 $m4is_q310 =$this->m4is_l7182();
 if(in_array($m4is_q485, $m4is_q310 )||substr($m4is_q485, 0, 5 )== 'sfwd-' ){
add_meta_box('is4wp-learndash-actions', 'LearnDash Memberium Integration', [$this, 'm4is_z53847'], $m4is_q485, 'side' );
 add_action('save_post', [$this, 'm4is_f01']);
 
}
}    public 
function m4is_z53847(){
global $post;
 $m4is_r1692 =[];
 $m4is_l271 =get_post_meta($post->ID );
 $m4is_f69781 =[ '_is4wp_learndash_actions', '_is4wp_learndash_assignment_approved_actions', '_is4wp_learndash_assignment_approved_tags', '_is4wp_learndash_assignment_upload_actions', '_is4wp_learndash_assignment_upload_tags', '_is4wp_learndash_autoenroll', '_is4wp_learndash_autojoin', '_is4wp_learndash_badges', '_is4wp_learndash_fail_actions', '_is4wp_learndash_fail_goals', '_is4wp_learndash_fail_tags', '_is4wp_learndash_goals', '_is4wp_learndash_orientation', '_is4wp_learndash_pdfformat', '_is4wp_learndash_redirect', '_is4wp_learndash_shortcodes', '_is4wp_learndash_tags', '_is4wp_learndash_drip_feed_override', ];
 foreach($m4is_f69781 as $m4is_l9671 ){
if(isset($m4is_l271[$m4is_l9671][0])){
$m4is_r1692[$m4is_l9671]=$m4is_l271[$m4is_l9671][0];
 
}else{
$m4is_r1692[$m4is_l9671]='';
 
}
}unset($metas, $m4is_f69781, $m4is_l9671 );
 wp_nonce_field($this->m4is_r1546->m4is_j541(), "memberium_membershipaccess_nonce_{$post->ID
}");
 if($post->post_type == 'sfwd-certificates' ){
echo '<p>Certificate PDF Format</p>';
 echo '<label for="_is4wp_learndash_pdfformat">' . _e("Page Size", 'memberium' ). ':</label> ';
 echo '<select name="_is4wp_learndash_pdfformat" id="_is4wp_learndash_pdfformat">';
 $m4is_d15784 =['' =>'Default', 'A4_EXTRA' =>'A4 Extra', 'A4_LONG' =>'A4 Long', 'A4_SUPER' =>'A4 Super', 'A4' =>'A4', 'GOVERNMENTLETTER' =>'Government Letter', 'LETTER' =>'US Letter', 'PA4' =>'PA4', 'RA4' =>'RA4', 'SRA4' =>'SRA4', 'SUPER_A4' =>'Super A4', ];
 foreach($m4is_d15784 as $m4is_g9610 =>$m4is_k52736 ){
echo '<option value="', $m4is_g9610, '" ', ($m4is_r1692['learndash_pdfformat']== $m4is_g9610 ? ' selected="selected" ' : '' ), '>', $m4is_k52736, '</option>';
 
}unset($m4is_g9610, $m4is_k52736 );
 echo '</select><br /><br />';
 echo '<label for="_is4wp_learndash_orientation">' . _e("Page Orientation", 'memberium' ). ':</label> ';
 echo '<select name="_is4wp_learndash_orientation" id="_is4wp_learndash_orientation">';
 $m4is_d15784 =['' =>'Automatic', 'L' =>'Landscape', 'P' =>'Portrait', ];
 foreach($m4is_d15784 as $m4is_g9610 =>$m4is_k52736 ){
echo '<option value="', $m4is_g9610, '" ', ($m4is_r1692['learndash_orientation']== $m4is_g9610 ? ' selected="selected" ' : '' ), '>', $m4is_k52736, '</option>';
 
}echo '</select><br /><br />';
 
}$m4is_v46257 =['sfwd-courses', 'sfwd-lessons', 'sfwd-quiz', 'sfwd-topic', ];
 if(in_array($post->post_type, $m4is_v46257 )){
 if(in_array($post->post_type, ['sfwd-courses'])){
echo '<label for="_is4wp_learndash_autoenroll">', _e("AutoEnroll Tags", 'memberium' ), ':</label> ';
 echo '<input name="_is4wp_learndash_autoenroll" class="multitaglist " style="width:100%; max-width:100%" value="', ($m4is_r1692['_is4wp_learndash_autoenroll']> '' ? $m4is_r1692['_is4wp_learndash_autoenroll']: '' ), '"><br /><br />';
 
} echo '<p>On completion of this section, execute the following actions:</p>';
 echo '<label for="_is4wp_learndash_goals">' . _e("Achieve these Goals", 'memberium' ). ':</label> ';
 echo '<input name="_is4wp_learndash_goals" style="width:100%; max-width:100%" value="', ($m4is_r1692['_is4wp_learndash_goals']> '' ? $m4is_r1692['_is4wp_learndash_goals']: '' ), '"><br /><br />';
 echo '<label for="_is4wp_learndash_tags">' . _e("Apply these Tags", 'memberium' ). ':</label> ';
 echo '<input name="_is4wp_learndash_tags" class="multitaglist " style="width:100%; max-width:100%" value="', ($m4is_r1692['_is4wp_learndash_tags']> '' ? $m4is_r1692['_is4wp_learndash_tags']: '' ), '"><br /><br />';
 echo '<label for="_is4wp_learndash_actions">' . _e("Run this Actionset", 'memberium' ). ':</label> ';
 echo '<select class="actionset-selector" name="_is4wp_learndash_actions" style="width:100%; max-width:100%">';
 echo '<option value="0">(No Actions)</option>';
 $m4is_i7193 =m4is_j4156::m4is_z8359();
 foreach ($m4is_i7193 as $m4is_w64602=>$m4is_t0631 ){
echo '<option value="', $m4is_w64602, '" ', ($m4is_r1692['_is4wp_learndash_actions']== $m4is_w64602 ? ' selected="selected" ' : '' ), '>', $m4is_t0631, '</option>';
 
}unset($m4is_w64602, $m4is_t0631 );
 echo '</select>';
 echo '<br /><br />';
 if($post->post_type == 'sfwd-quiz' ){
echo '<p>If the student <strong style="color:red;">fails</strong> this test, execute the following actions:</p>';
 echo '<label for="_is4wp_learndash_fail_goals">' . _e("Achieve these Goals", 'memberium' ). ':</label> ';
 echo '<input name="_is4wp_learndash_fail_goals" style="width:100%; max-width:100%" value="', ($m4is_r1692['_is4wp_learndash_fail_goals']> '' ? $m4is_r1692['_is4wp_learndash_fail_goals']: '' ), '"><br /><br />';
 echo '<label for="_is4wp_learndash_fail_tags">' . _e("Apply these Tags", 'memberium' ). ':</label> ';
 echo '<input name="_is4wp_learndash_fail_tags" class="multitaglist" style="width:100%; max-width:100%" value="', ($m4is_r1692['_is4wp_learndash_fail_tags']> '' ? $m4is_r1692['_is4wp_learndash_fail_tags']: '' ), '"><br /><br />';
 echo '<label for="_is4wp_learndash_fail_actions">' . _e("Run this Actionset", 'memberium' ). ':</label> ';
 echo '<select class="actionset-selector" name="_is4wp_learndash_fail_actions" style="width:100%; max-width:100%">';
 echo '<option value="0">(No Actions)</option>';
 foreach ($m4is_i7193 as $m4is_w64602=>$m4is_t0631 ){
echo '<option value="', $m4is_w64602, '" ', ($m4is_r1692['_is4wp_learndash_fail_actions']== $m4is_w64602 ? ' selected="selected" ' : '' ), '>', $m4is_t0631, '</option>';
 
}echo '</select>';
 
}unset($m4is_i7193, $m4is_w64602, $m4is_t0631 );
  if(in_array($post->post_type, ['sfwd-courses'])){
echo '<label for="_is4wp_learndash_drip_feed_override">', _e("Drip Feed override", 'memberium' ), ':</label> ';
 echo '<input name="_is4wp_learndash_drip_feed_override" class="have-not-have-tag-selector" style="width:100%; max-width:100%" value="', ($m4is_r1692['_is4wp_learndash_drip_feed_override']> '' ? $m4is_r1692['_is4wp_learndash_drip_feed_override']: '' ), '"><br /><br />';
 echo '<p>Tag access check will override the Lesson Release Schedule settings for Enrollment Days or Specific Date on all Lessons in this course.</p>';
 
} if(in_array($post->post_type, ['sfwd-courses'])){
echo '<label for="_is4wp_learndash_redirect">' . _e("Redirect to", 'memberium' ). ':</label> ';
 echo '<input name="_is4wp_learndash_redirect" style="width:100%; max-width:100%" value="', ($m4is_r1692['_is4wp_learndash_redirect']> '' ? $m4is_r1692['_is4wp_learndash_redirect']: '' ), '"><br /><br />';
 
}
}if($post->post_type == 'groups' ){
$m4is_r78964 =empty($m4is_r1692['_is4wp_learndash_autojoin'])? '' : trim($m4is_r1692['_is4wp_learndash_autojoin'], ', ');
 echo '<label for="_is4wp_learndash_autojoin">' . _e("Group Auto-Join Tags", 'memberium' ). ':</label> ';
 echo '<input name="_is4wp_learndash_autojoin" class="multitaglist" style="width:100%; max-width:100%" value="', $m4is_r78964, '"><br /><br />';
 
}add_action('admin_footer', [m4is_s6729::m4is_c26(), 'm4is_n68517']);
 
} public 
function m4is_f01($m4is_b4068 ){
 if(defined('DOING_AUTOSAVE' )&&DOING_AUTOSAVE ){
return;
 
}if(!in_array(get_post_type($m4is_b4068 ), $this->m4is_l7182())){
return;
 
}$m4is_i796 =$_POST["memberium_membershipaccess_nonce_{$m4is_b4068
}"]?? '';
  if(empty($m4is_i796 )||!wp_verify_nonce($m4is_i796, $this->m4is_r1546->m4is_j541())){
return;
 
}if(!current_user_can('edit_posts', $m4is_b4068 )){
return;
 
}$m4is_f69781 =['_is4wp_learndash_achievement', '_is4wp_learndash_actions', '_is4wp_learndash_assignment_approved_actions', '_is4wp_learndash_assignment_approved_tags', '_is4wp_learndash_assignment_upload_actions', '_is4wp_learndash_assignment_upload_tags', '_is4wp_learndash_autoenroll', '_is4wp_learndash_autojoin', '_is4wp_learndash_badges', '_is4wp_learndash_fail_actions', '_is4wp_learndash_fail_goals', '_is4wp_learndash_fail_tags', '_is4wp_learndash_goals', '_is4wp_learndash_orientation', '_is4wp_learndash_pdfformat', '_is4wp_learndash_redirect', '_is4wp_learndash_shortcodes', '_is4wp_learndash_tags', '_is4wp_learndash_drip_feed_override', '_is4wp_lms_complete_percent', '_is4wp_lms_completed', '_is4wp_lms_grade', '_is4wp_lms_start_date', ];
 $m4is_v845 =m4is_h68::m4is_c26();
 foreach($m4is_f69781 as $m4is_l9671 ){
$_POST[$m4is_l9671]=isset($_POST[$m4is_l9671])? $_POST[$m4is_l9671]: '';
 if(empty($_POST[$m4is_l9671])){
delete_post_meta($m4is_b4068, $m4is_l9671);
 
}else{
add_post_meta($m4is_b4068, $m4is_l9671, $_POST[$m4is_l9671], true )or update_post_meta($m4is_b4068, $m4is_l9671, $_POST[$m4is_l9671]);
 
}
}$m4is_v845->m4is_q147();
 $m4is_v845->m4is_h63170();
 
}   public 
function m4is_k5467($m4is_g617 ){
$m4is_q485 =isset($_GET['post_type'])? $_GET['post_type']: 'post';
 $m4is_j37291 =[];
 $m4is_s0743 =[];
 if($m4is_q485 == 'groups' ){
$m4is_j37291['autojoin']='AutoJoin Tag';
 
}elseif($m4is_q485 == 'sfwd-courses' ){
$m4is_j37291['autoenroll']='AutoEnroll Tag';
 
}elseif($m4is_q485 == 'sfwd-lessons' ){

}elseif($m4is_q485 == 'sfwd-quiz' ){

}elseif($m4is_q485 == 'sfwd-topic' ){

}$m4is_g617 =array_merge($m4is_g617, $m4is_j37291 );
 foreach ($m4is_s0743 as $m4is_l9671 ){
unset($m4is_g617[$m4is_l9671]);
 
}return $m4is_g617;
 
}public 
function m4is_i3918($m4is_a950, $m4is_b4068 ){
if($m4is_a950 == 'autojoin' ){
$m4is_l91805 =array_filter(explode(',', trim(get_post_meta($m4is_b4068, '_is4wp_learndash_autojoin', true ), ', ' )));
 echo implode(', ', $m4is_l91805 );
 
}elseif($m4is_a950 == 'autoenroll' ){
$m4is_l91805 =array_filter(explode(',', trim(get_post_meta($m4is_b4068, '_is4wp_learndash_autoenroll', true ), ', ' )));
 echo implode(', ', $m4is_l91805 );
 
}
} public 
function m4is_f6528($m4is_y634 =[]){
return array_merge($m4is_y634, ['LearnDash for Memberium']);
 
} private 
function m4is_l7182(): array {
return ['groups', 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'sfwd-certificates', ];
 
} public 
function m4is_i6845(){

} public 
function m4is_s24($m4is_m5907 ){
if(empty($m4is_m5907->post_type )||$m4is_m5907->post_type !== 'sfwd-courses' ){
return;
 
}if('open' !== learndash_get_course_meta_setting($m4is_m5907->ID, 'course_price_type')){
return;
 
};
 $m4is_c67 ='<a target="_blank" href="https://memberium.com/?p=7948">Click here</a> for more information.';
 echo '<div style="background-color:white;border-radius:10px;border-style:solid;border-color:darkred;padding:10px;">';
 echo '<p><strong>Warning:</strong></p>';
 echo '<p>Because your course is marked as "open", all site members will be enrolled in it regardless of tags or memberships.</p>';
 echo '<p>We recommend setting your course as "closed".</p>';
 echo "<p>{$m4is_c67
}</p>";
 echo '</div>';
 
}    public 
function m4is_t92561($m4is_r2806 ){
if(!$m4is_r2806){
$m4is_w56894 =['learndash_propanel_template', 'ld-report-download', ];
 foreach($m4is_w56894 as $m4is_l6249){
$m4is_r2806 =array_key_exists($m4is_l6249, $_GET);
 if($m4is_r2806){
break;
 
}
}
}return $m4is_r2806;
 
}
}

