<?php

/**
 * Copyright (c) 2012-2024 by David Bullock / Web Power and Light
 * All rights reserved.
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_h68 {
private const AUTOENROLL_CACHE_KEY ='memberium/learndash/autoenroll';
 private const AUTOJOIN_CACHE_KEY ='memberium/learndash/autojoin';
 private const GROUP_ADD =false;
 private const GROUP_REMOVE =true;
 private const COURSE_ENROLL =false;
 private const COURSE_UNENROLL =true;
 private $m4is_r1546;
     public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_i702();
 $this->m4is_y1648();
 $this->m4is_d4861();
 $this->m4is_f3276();
 
} private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
} private 
function m4is_y1648(): void {
$m4is_l61035 =['m4is_l10524' =>__DIR__ . '/admin', 'm4is_v076' =>__DIR__ . '/catchers', 'm4is_c05' =>__DIR__ . '/frontend', 'm4is_v43' =>__DIR__ . '/shortcodes', ];
 $this->m4is_r1546->m4is_p39($m4is_l61035 );
 
}private 
function m4is_f3276(){
if(is_admin()){
 m4is_l10524::m4is_c26();
 
}else{
 m4is_c05::m4is_c26();
 
}
} private 
function m4is_d4861(){
add_action('learndash_assignment_approved', [$this, 'm4is_d426'], 10, 1 );
 add_action('learndash_assignment_uploaded', [$this, 'm4is_o461'], 10, 2 );
 add_action('learndash_course_completed', [$this, 'm4is_t86591'], 5, 1 );
 add_action('learndash_lesson_completed', [$this, 'm4is_j26837'], 5, 1 );
 add_action('learndash_quiz_submitted', [$this, 'm4is_m37209'], 5, 2 );
 add_action('learndash_topic_completed', [$this, 'm4is_e02856'], 5, 1 );
 add_action('template_redirect', [$this, 'm4is_x28366'], 901 );
  add_filter('learndash_certificate_pdf_page_formats', [$this, 'm4is_r682'], 10 );
 add_filter('memberium/lms/course_category', [$this, 'm4is_e6578']);
 add_filter('memberium/lms/course_tag', [$this, 'm4is_m7663']);
 add_filter('memberium/lms/course_type', [$this, 'm4is_y6691']);
 add_filter('memberium/lms/module_post_types', [$this, 'm4is_w67']);
 add_filter('memberium/lms/name', [$this, 'm4is_f02516']);
 add_filter('memberium/posts/unenhanced', [$this, 'm4is_v2664'], 10, 1);
  remove_action('wp_login_failed', 'learndash_login_failed' );
 add_action('wp_login_failed', 'learndash_login_failed', 100, 1 );
 if(!defined('DISABLE_LEARNDASH_ENROLL' )){
add_action('memberium/session/updated', [$this, 'm4is_g762'], 10, 2 );
 add_action('memberium/session/updated', [$this, 'm4is_q739'], 11, 2 );
 
}
}       
function m4is_v2664($m4is_l15 =[]){
$m4is_l15[]='sfwd-essays';
 return $m4is_l15;
 
} 
function m4is_y6691($m4is_q485){
return 'sfwd-courses';
 
} 
function m4is_e6578($m4is_q1852){
return 'ld_course_category';
 
} 
function m4is_m7663($m4is_p786){
return 'ld_course_tag';
 
} 
function m4is_f02516($m4is_k52736){
return 'LearnDash';
 
}
function m4is_w67($m4is_x320 =[]){
return array_merge($m4is_x320, ['sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', ]);
 
}    public 
function m4is_g762($m4is_f087 =0, ?array $m4is_k824 =[]){
if(!function_exists('ld_update_group_access' )){
error_log('Memberium: [error] Learndash function ld_update_group_access() not found.' );
 return;
 
}if(user_can($m4is_f087, 'manage_options' )){
return;
 
}$m4is_b4669 =$this->m4is_o01438();
 if(empty($m4is_b4669 )){
return;
 
}$m4is_a3698 =learndash_get_users_group_ids($m4is_f087 );
 $m4is_f406 =isset($m4is_k824['memb_user']['tags'])? array_filter(explode(',', $m4is_k824['memb_user']['tags'])): [];
 $m4is_r781 =[];
  foreach ($m4is_a3698 as $m4is_j4976 ){
 if(!array_key_exists($m4is_j4976, $m4is_b4669 )){
continue;
 
}$m4is_o78 =$m4is_b4669[$m4is_j4976];
  if((bool) array_intersect($m4is_o78, $m4is_f406 )){
continue;
 
}ld_update_group_access($m4is_f087, $m4is_j4976, self::GROUP_REMOVE );
 unset($m4is_a3698[$m4is_j4976]);
 unset($m4is_b4669[$m4is_j4976]);
 $m4is_r781[$m4is_j4976]=$m4is_j4976;
 
} foreach($m4is_b4669 as $m4is_j4976 =>$m4is_l9321 ){
 if(in_array($m4is_j4976, $m4is_a3698 )){
continue;
 
} if(in_array($m4is_j4976, $m4is_r781 )){
continue;
 
}if(!(bool) array_intersect($m4is_b4669[$m4is_j4976], $m4is_f406 )){
continue;
 
}ld_update_group_access($m4is_f087, $m4is_j4976, self::GROUP_ADD );
 
}
} public 
function m4is_q739(int $m4is_f087 =0, ?array $m4is_k824 =[]): void {
if(!function_exists('ld_update_course_access' )){
error_log('Memberium: [error] Learndash function ld_update_course_access not found.' );
 return;
 
}if(!function_exists('learndash_user_get_enrolled_courses' )){
error_log('Memberium: [error] Learndash function learndash_user_get_enrolled_courses not found.' );
 return;
 
}if(user_can($m4is_f087, 'manage_options' )){
return;
 
} $m4is_y984 =$this->m4is_v40781();
 if(empty($m4is_y984 )){
return;
 
}$m4is_p6457 =isset($m4is_k824['memb_user']['tags'])? array_filter(explode(',', $m4is_k824['memb_user']['tags'])): [];
 $m4is_s6672 =learndash_user_get_enrolled_courses($m4is_f087 );
 $m4is_m251 =[];
  foreach($m4is_s6672 as $m4is_n3691 ){
 if(!array_key_exists($m4is_n3691, $m4is_y984 )){
continue;
 
} if((bool) array_intersect($m4is_y984[$m4is_n3691], $m4is_p6457 )){
continue;
 
}ld_update_course_access($m4is_f087, $m4is_n3691, self::COURSE_UNENROLL );
  $m4is_m251[$m4is_n3691]=$m4is_n3691;
 unset($m4is_s6672[$m4is_n3691], $m4is_y984[$m4is_n3691]);
 
} foreach($m4is_y984 as $m4is_d07693 =>$m4is_f79416 ){
 if(in_array($m4is_d07693, $m4is_s6672 )){
continue;
 
} if(array_key_exists($m4is_d07693, $m4is_m251 )){
continue;
 
} if((bool) array_intersect($m4is_f79416, $m4is_p6457 )){
ld_update_course_access($m4is_f087, $m4is_d07693, self::COURSE_ENROLL );
  
}
}
}   public 
function m4is_t86591($m4is_l91805 ){
$m4is_f087 =isset($m4is_l91805['user']->ID)? (int) $m4is_l91805['user']->ID : $this->m4is_r1546->m4is_x66();
 $m4is_b4068 =isset($m4is_l91805['course']->ID )? (int) $m4is_l91805['course']->ID : 0;
 $m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_b4068 ){
return;
 
}if(!$m4is_h21895 ){
return;
 
}$m4is_l79562 =get_post_meta($m4is_b4068 );
 $m4is_j661 =empty($m4is_l79562['_is4wp_learndash_goals'][0])? '' : $m4is_l79562['_is4wp_learndash_goals'][0];
 $m4is_l9321 =empty($m4is_l79562['_is4wp_learndash_tags'][0])? '' : $m4is_l79562['_is4wp_learndash_tags'][0];
 $m4is_j15 =empty($m4is_l79562['_is4wp_learndash_actions'][0])? '' : $m4is_l79562['_is4wp_learndash_actions'][0];
 $this->m4is_r1546->m4is_t64038($m4is_j661, $m4is_h21895 );
 $this->m4is_r1546->m4is_k98($m4is_l9321, $m4is_h21895 );
 $this->m4is_r1546->m4is_u71903($m4is_j15, $m4is_h21895 );
 do_action('memberium/lms/completion', $m4is_f087, $m4is_b4068 );
 
}public 
function m4is_j26837($m4is_l91805 ){
$m4is_f087 =isset($m4is_l91805['user']->ID )? (int) $m4is_l91805['user']->ID : $this->m4is_r1546->m4is_x66();
 $m4is_b4068 =isset($m4is_l91805['lesson']->ID )? (int) $m4is_l91805['lesson']->ID : 0;
 $m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_b4068 ){
return;
 
}if(!$m4is_h21895 ){
return;
 
}$m4is_l79562 =get_post_meta($m4is_b4068 );
 $m4is_j661 =empty($m4is_l79562['_is4wp_learndash_goals'][0])? '' : $m4is_l79562['_is4wp_learndash_goals'][0];
 $m4is_l9321 =empty($m4is_l79562['_is4wp_learndash_tags'][0])? '' : $m4is_l79562['_is4wp_learndash_tags'][0];
 $m4is_j15 =empty($m4is_l79562['_is4wp_learndash_actions'][0])? '' : $m4is_l79562['_is4wp_learndash_actions'][0];
 $this->m4is_r1546->m4is_t64038($m4is_j661, $m4is_h21895 );
 $this->m4is_r1546->m4is_k98($m4is_l9321, $m4is_h21895 );
 $this->m4is_r1546->m4is_u71903($m4is_j15, $m4is_h21895 );
 
}public 
function m4is_e02856($m4is_l91805 ){
$m4is_f087 =isset($m4is_l91805['user']->ID )? (int) $m4is_l91805['user']->ID : $this->m4is_r1546->m4is_x66();
 $m4is_b4068 =isset($m4is_l91805['topic']->ID )? (int) $m4is_l91805['topic']->ID : 0;
 $m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087);
 if(!$m4is_b4068 ){
return;
 
}if(!$m4is_h21895 ){
return;
 
}$m4is_l79562 =get_post_meta($m4is_b4068 );
 $m4is_j661 =empty($m4is_l79562['_is4wp_learndash_goals'][0])? '' : $m4is_l79562['_is4wp_learndash_goals'][0];
 $m4is_l9321 =empty($m4is_l79562['_is4wp_learndash_tags'][0])? '' : $m4is_l79562['_is4wp_learndash_tags'][0];
 $m4is_j15 =empty($m4is_l79562['_is4wp_learndash_actions'][0])? '' : $m4is_l79562['_is4wp_learndash_actions'][0];
 $this->m4is_r1546->m4is_t64038($m4is_j661, $m4is_h21895 );
 $this->m4is_r1546->m4is_k98($m4is_l9321, $m4is_h21895 );
 $this->m4is_r1546->m4is_u71903($m4is_j15, $m4is_h21895 );
 do_action('memberium/lms/completion', $m4is_f087, $m4is_b4068 );
 
} public 
function m4is_m37209($m4is_l91805, $m4is_l17096 ){
$m4is_f087 =isset($m4is_l17096->ID )? $m4is_l17096->ID : $this->m4is_r1546->m4is_x66();
 $m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
return;
 
}$m4is_b4068 =isset($m4is_l91805['quiz'])? (int) $m4is_l91805['quiz']: 0;
 if(!$m4is_b4068 ){
error_log('Memberium: [error] No Post ID found for LearnDash Quiz Completion Data ' . print_r($m4is_l91805['quiz'], true ));
 return;
 
}$m4is_l79562 =get_post_meta($m4is_b4068 );
 $m4is_j661 ='';
 $m4is_l9321 ='';
 $m4is_j15 ='';
 if($m4is_l91805['pass']){
$m4is_j661 =empty($m4is_l79562['_is4wp_learndash_goals'][0])? '' : $m4is_l79562['_is4wp_learndash_goals'][0];
 $m4is_l9321 =empty($m4is_l79562['_is4wp_learndash_tags'][0])? '' : $m4is_l79562['_is4wp_learndash_tags'][0];
 $m4is_j15 =empty($m4is_l79562['_is4wp_learndash_actions'][0])? '' : $m4is_l79562['_is4wp_learndash_actions'][0];
 
}else{
$m4is_j661 =empty($m4is_l79562['_is4wp_learndash_fail_goals'][0])? '' : $m4is_l79562['_is4wp_learndash_fail_goals'][0];
 $m4is_l9321 =empty($m4is_l79562['_is4wp_learndash_fail_tags'][0])? '' : $m4is_l79562['_is4wp_learndash_fail_tags'][0];
 $m4is_j15 =empty($m4is_l79562['_is4wp_learndash_fail_actions'][0])? '' : $m4is_l79562['_is4wp_learndash_fail_actions'][0];
 
}$this->m4is_r1546->m4is_t64038($m4is_j661, $m4is_h21895 );
 $this->m4is_r1546->m4is_k98($m4is_l9321, $m4is_h21895 );
 $this->m4is_r1546->m4is_u71903($m4is_j15, $m4is_h21895 );
 do_action('memberium/lms/completion', $m4is_f087, $m4is_b4068 );
 
}public 
function m4is_z89($m4is_k05679, $m4is_m5907, $m4is_l17096 ){
if(!$m4is_k05679 ){
return;
 
}if(!is_a($m4is_m5907, 'WP_Post' )){
return;
 
}$m4is_f087 =(int) $m4is_l17096->ID;
 $m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
return;
 
}$m4is_b4068 =(int) $m4is_m5907->ID;
 $m4is_b8206 =get_post($m4is_b4068, 'ARRAY_A' );
 $m4is_k05676 =get_user_meta($m4is_f087, '_sfwd-quizzes', true );
 foreach($m4is_k05676 as $m4is_t7401 ){
if($m4is_t7401['quiz']== $m4is_b4068 ){
$m4is_d72631 =false;
 
}
}do_action('memberium/lms/completion', $m4is_f087, $m4is_b4068 );
  
}    public 
function m4is_o461(int $m4is_u3216 =0, $m4is_u7403 =[]): void {
if(!$m4is_u3216 ){
return;
 
}$m4is_f087 =isset($m4is_u7403['user_id'])? $m4is_u7403['user_id']: 0;
 $m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
return;
 
}$m4is_b4068 =isset($m4is_u7403['lesson_id'])? $m4is_u7403['lesson_id']: 0;
 if(!$m4is_b4068 ){
return;
 
}$m4is_r1692 =get_post_meta($m4is_b4068 );
 if(!empty($m4is_r1692['_memberium_lms_assignment_upload_tag'][0])){
m4is_r83::m4is_c26()->m4is_k98($m4is_r1692['_memberium_lms_assignment_upload_tag'][0], $m4is_h21895 );
 
}
}public 
function m4is_d426(int $m4is_h7352 =0 ): void {
$m4is_h7352 =(int) $m4is_h7352;
 if(!$m4is_h7352 ){
return;
 
}$m4is_u7403 =get_post_meta($m4is_h7352 );
 $m4is_b4068 =isset($m4is_u7403['lesson_id'][0])? $m4is_u7403['lesson_id'][0]: 0;
 $m4is_f087 =isset($m4is_u7403['user_id'][0])? $m4is_u7403['user_id'][0]: 0;
 $m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
return;
 
}$m4is_r1692 =get_post_meta($m4is_b4068 );
 if(!empty($m4is_r1692['_memberium_lms_assignment_approval_tag'][0])){
m4is_r83::m4is_c26()->m4is_k98($m4is_r1692['_memberium_lms_assignment_approval_tag'][0], $m4is_h21895 );
 
}
}   public 
function m4is_x28366(){
if(m4is_f58::m4is_c26()->post_type == 'sfwd-quiz' ){
  
}
} public 
function m4is_r682($m4is_r37596 ){
$m4is_r37596['A4_EXTRA']='A4 Extra';
 $m4is_r37596['A4_LONG']='A4 Long';
 $m4is_r37596['A4_SUPER']='A4 Super';
 $m4is_r37596['COMPACT']='Compact';
 $m4is_r37596['FOOLSCAP']='Foolscap';
 $m4is_r37596['GLETTER']='Government Letter';
 $m4is_r37596['GOVERNMENTLEGAL']='Government Legal';
 $m4is_r37596['JLEGAL']='Junior Legal';
 $m4is_r37596['LEDGER']='Ledger';
 $m4is_r37596['LEGAL']='Legal';
 $m4is_r37596['TABLOID']='Tabloid';
 return $m4is_r37596;
 
}    public 
function m4is_z14($m4is_s86, $m4is_k52736 ='', $m4is_y66291 =[]){
$m4is_s86 =empty($m4is_s86 )? $this->m4is_r1546->m4is_x66(): $m4is_s86;
 $m4is_y642 =['description' =>'', ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642 );
 $m4is_i0669 =['post_author' =>$m4is_s86, 'post_title' =>$m4is_k52736, 'post_content' =>$m4is_y66291['description'], ];
 $m4is_b4068 =wp_insert_post($m4is_i0669 );
 if(!is_a($m4is_b4068, 'WP_Error' )){
learndash_set_groups_administrators($m4is_b4068, [$m4is_s86]);
 
}
}    public 
function m4is_q147(): array {
$m4is_j3627 =[];
 $m4is_w8256 =$this->m4is_r768();
 foreach($m4is_w8256 as $m4is_d07693 =>$m4is_x981 ){
$m4is_l9321 =array_filter(explode(',', $m4is_x981->meta_value ));
 if(!empty($m4is_l9321 )){
$m4is_j3627[$m4is_d07693]=$m4is_l9321;
 
}
}update_option(self::AUTOENROLL_CACHE_KEY, $m4is_j3627, false );
 return $m4is_j3627;
 
} public 
function m4is_h63170(): array {
$m4is_j3627 =[];
 $m4is_t87 =$this->m4is_r561();
 foreach($m4is_t87 as $m4is_d07693 =>$m4is_d10 ){
$m4is_l9321 =array_filter(explode(',', $m4is_d10->meta_value ));
 if(!empty($m4is_l9321 )){
$m4is_j3627[$m4is_d07693]=$m4is_l9321;
 
}
}update_option(self::AUTOJOIN_CACHE_KEY, $m4is_j3627, false );
 return $m4is_j3627;
 
} public 
function m4is_v40781(): array {
$m4is_j3627 =get_option(self::AUTOENROLL_CACHE_KEY, false );
 if(!is_array($m4is_j3627 )){
$m4is_j3627 =$this->m4is_q147();
 
}return $m4is_j3627;
 
} public 
function m4is_o01438(): array {
$m4is_j3627 =get_option(self::AUTOJOIN_CACHE_KEY, false );
 if(!is_array($m4is_j3627 )){
$m4is_j3627 =$this->m4is_h63170();
 
}return $m4is_j3627;
 
}    private 
function m4is_r768(): array {
static $m4is_y563;
 if(is_null($m4is_y563 )){
global $wpdb;
 $m4is_w07 =learndash_get_open_courses();
 $m4is_c093 =$wpdb->posts;
 $m4is_e0895 =$wpdb->postmeta;
 $m4is_v2613 ="SELECT `ID`, `meta_value` FROM `{$m4is_c093
}`, `{$m4is_e0895
}` WHERE `post_status` = 'publish' AND `post_type` = 'sfwd-courses' AND `post_id` = `ID` AND `meta_key` = '_is4wp_learndash_autoenroll' AND `meta_value` > '' ";
 if(!empty($m4is_w07 )){
$m4is_w07 =implode(',', $m4is_w07 );
 $m4is_v2613 .= " AND `ID` NOT IN ( {$m4is_w07
} ) ";
 
}$m4is_y563 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 $m4is_y563 =is_array($m4is_y563 )? $m4is_y563 : [];
 
}return apply_filters('memberium/lms/courses/autoenroll', $m4is_y563 );
 
} private 
function m4is_r561(): array {
static $m4is_s196;
 if(is_null($m4is_s196 )){
global $wpdb;
  $m4is_v2613 =<<<SQLBLOCK
                SELECT
                    ID,
                    meta_value
                FROM
                    {$wpdb->posts
},
                    {$wpdb->postmeta
}
                WHERE post_status = 'publish'
                    AND post_type = 'groups'
                    AND post_id = ID
                    AND meta_key = '_is4wp_learndash_autojoin'
                    AND meta_value > ''
            SQLBLOCK;
 $m4is_s196 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 $m4is_s196 =is_array($m4is_s196 )? $m4is_s196 : [];
 
}return apply_filters('memberium/lms/groups/autojoin', $m4is_s196 );
 
} 
}

