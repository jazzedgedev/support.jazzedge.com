<?php
 class_exists('m4is_r83')||die();
 final 
class m4is_i79 {
private $m4is_r1546;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_d4861();
 $this->m4is_f3276();
 
}private 
function m4is_f3276(): void {
if(is_admin()){
include __DIR__ . '/admin.php';
 m4is_c58016::m4is_c26();
 
}
}private 
function m4is_d4861(): void {
add_action('lifterlms_course_completed', [$this, 'm4is_t86591'], 5, 2 );
 add_action('lifterlms_quiz_completed', [$this, 'm4is_t901'], 5, 2 );
 add_action('llms_trigger_lesson_completion', [$this, 'm4is_j26837'], 5, 2 );
 add_action('llms_user_added_to_membership_level', [$this, 'm4is_r1670'], 5, 2 );
 add_action('llms_user_removed_from_membership_level', [$this, 'm4is_i59084'], 5, 2 );
 add_action('memberium/session/updated', [$this, 'm4is_d79165'], 10, 2);
 add_filter('memberium/lms/course_category', [$this, 'm4is_e6578']);
 add_filter('memberium/lms/course_tag', [$this, 'm4is_m7663']);
 add_filter('memberium/lms/course_type', [$this, 'm4is_y6691']);
 add_filter('memberium/lms/course/item/data', [$this, 'm4is_b56091'], 10, 2);
 
} 
function m4is_r1670($m4is_f087, $m4is_o6480 ): void {
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if($m4is_h21895 ){
$m4is_r1692 =get_post_meta($m4is_o6480 );
 
}
} 
function m4is_i59084($m4is_f087, $m4is_o6480 ): void {
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if($m4is_h21895 ){
$m4is_r1692 =get_post_meta($m4is_o6480 );
 
}
}
function m4is_t86591($m4is_f087, $m4is_n3691){
$m4is_r1692 =get_post_meta($m4is_n3691 );
 $m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if($m4is_h21895 ){
if(!empty($m4is_r1692['_is4wp_learndash_goals'][0])){
m4is_r83::m4is_c26()->m4is_t64038($m4is_r1692['_is4wp_learndash_goals'][0], $m4is_h21895 );
 
}if(!empty($m4is_r1692['_is4wp_learndash_tags'][0])){
m4is_r83::m4is_c26()->m4is_k98($m4is_r1692['_is4wp_learndash_tags'][0], $m4is_h21895 );
 
}if(!empty($m4is_r1692['_is4wp_learndash_actions'][0])){
m4is_r83::m4is_c26()->m4is_u71903($m4is_r1692['_is4wp_learndash_actions'][0], $m4is_h21895 );
 
}if(!empty($m4is_r1692['_is4wp_learndash_shortcodes'][0])){
$m4is_u6591 =do_shortcode($m4is_r1692['_is4wp_learndash_shortcodes'][0]);
 
}
}do_action('memberium/lms/completion', $m4is_f087, $m4is_n3691);
 
}
function m4is_j26837($m4is_f087, $m4is_b1249 ){
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 $m4is_u3646 =get_post_meta($m4is_b1249);
 if($m4is_h21895){
if(!empty($m4is_u3646['_is4wp_learndash_goals'][0])){
m4is_r83::m4is_c26()->m4is_t64038($m4is_u3646['_is4wp_learndash_goals'][0], $m4is_h21895 );
 
}if(!empty($m4is_u3646['_is4wp_learndash_tags'][0])){
m4is_r83::m4is_c26()->m4is_k98($m4is_u3646['_is4wp_learndash_tags'][0], $m4is_h21895 );
 
}if(!empty($m4is_u3646['_is4wp_learndash_actions'][0])){
m4is_r83::m4is_c26()->m4is_u71903($m4is_u3646['_is4wp_learndash_actions'][0], $m4is_h21895 );
 
}if(!empty($m4is_u3646['_is4wp_learndash_shortcodes'][0])){
do_shortcode($m4is_u3646['_is4wp_learndash_shortcodes'][0]);
 
}
}do_action('memberium/lms/completion', $m4is_f087, $m4is_b1249);
 
}
function m4is_t901($m4is_f087, $m4is_s15 ){
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 $m4is_w2075 =$m4is_s15['id'];
 $m4is_u3646 =get_post_meta($m4is_w2075 );
 if(!$m4is_s15['passed']){
return;
 
}if(!$m4is_h21895){
if(!empty($m4is_u3646['_is4wp_learndash_goals'][0])){
m4is_r83::m4is_c26()->m4is_t64038($m4is_u3646['_is4wp_learndash_goals'][0], $m4is_h21895 );
 
}if(!empty($m4is_u3646['_is4wp_learndash_tags'][0])){
m4is_r83::m4is_c26()->m4is_k98($m4is_u3646['_is4wp_learndash_tags'][0], $m4is_h21895 );
 
}if(!empty($m4is_u3646['_is4wp_learndash_actions'][0])){
m4is_r83::m4is_c26()->m4is_u71903($m4is_u3646['_is4wp_learndash_actions'][0], $m4is_h21895 );
 
}if(!empty($m4is_u3646['_is4wp_learndash_shortcodes'][0])){
$result =do_shortcode($m4is_u3646['_is4wp_learndash_shortcodes'][0]);
 
}
}do_action('memberium/lms/completion', $m4is_f087, $m4is_w2075);
 
}
function m4is_d79165($m4is_f087, $m4is_k824){
global $wpdb;
  $m4is_c093 =$wpdb->posts;
 $m4is_e0895 =$wpdb->postmeta;
 $m4is_v2613 ="SELECT ID, meta_value FROM {$m4is_c093
}, {$m4is_e0895
} WHERE post_status = 'publish' AND post_type = 'course' AND  post_id = ID AND meta_key = '_is4wp_learndash_autoenroll' AND meta_value > '' ";
 $m4is_r02674 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 if(is_array($m4is_r02674 )&&!empty($m4is_r02674 )){
$m4is_v665 =new LLMS_Student($m4is_f087 );
 foreach ($m4is_r02674 as $m4is_m5907 ){
$m4is_o51 =llms_is_user_enrolled($m4is_f087, $m4is_m5907['ID']);
 $m4is_s248 =m4is_r83::m4is_c26()->m4is_m2480($m4is_m5907['meta_value'], $m4is_k824 );
 if($m4is_o51 ){
if(!$m4is_s248 ){
$m4is_v665->unenroll($m4is_m5907['ID']);
 
}
}else{
if($m4is_s248 ){
$m4is_v665->enroll($m4is_m5907['ID']);
 
}
}
}
}
} 
function m4is_b56091(array $m4is_l91805, int $m4is_b4068){
if(get_post_type($m4is_b4068)!== 'course'){
return $m4is_l91805;
 
}$m4is_f087 =$this->m4is_r1546->m4is_x66();
  $m4is_l91805['access']=m4is_f58::m4is_c26()->m4is_x72168($m4is_b4068, $m4is_f087);
 if(!$m4is_l91805['access']){
$m4is_l91805['status']='locked';
 return $m4is_l91805;
 
}$m4is_x981 =new LLMS_Course($m4is_b4068 );
  $m4is_o51 =llms_is_user_enrolled($m4is_f087, $m4is_b4068 );
 if(!$m4is_o51 ){
if(!$m4is_x981->is_enrollment_open()||!$m4is_x981->has_capacity()){
$m4is_l91805['access']=0;
 $m4is_l91805['status']='locked';
 return $m4is_l91805;
 
}else{
$m4is_l91805['status']='not_enrolled';
 
}$m4is_l91805['url']=$m4is_x981->get_sales_page_url();
 
} if($m4is_x981->has_prerequisite('course' )&&!$m4is_x981->is_prerequisite_complete('course' )){
$m4is_l91805['access']=0;
  $m4is_l91805['status']='locked';
 return $m4is_l91805;
 
} if($m4is_x981->has_prerequisite('course_track' )&&!$m4is_x981->is_prerequisite_complete('course_track' )){
$m4is_l91805['access']=0;
  $m4is_l91805['status']='locked';
 return $m4is_l91805;
 
}$m4is_l91805['progress']=$m4is_x981->get_percent_complete($m4is_f087 );
 return $m4is_l91805;
 
}
function m4is_y6691($m4is_q485){
return 'course';
 
}
function m4is_e6578($m4is_q1852){
return 'course_cat';
 
}
function m4is_m7663($m4is_p786){
return 'course_tag';
 
} 
}

