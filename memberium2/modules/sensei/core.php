<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_s66047 {
private $m4is_r1546;
 private $m4is_v257 =[];
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
  $this->m4is_d4861();
 if(!is_admin()){
return;
 
} require_once __DIR__ . '/admin.php';
  m4is_v5864::m4is_c26();
 
} private 
function m4is_d4861(){
add_action('memberium/session/updated', [$this, 'm4is_z38760'], 10, 2);
 add_action('init', [$this, 'm4is_r23760'], PHP_INT_MAX);
 add_action('sensei_user_lesson_end', [$this, 'm4is_e59736'], 10, 2);
 add_action('sensei_user_course_end', [$this, 'm4is_p92136'], 10, 2);
 add_action('sensei_user_quiz_grade', [$this, 'm4is_k1862'], 100, 5);
 add_filter('memberium/lms/name', [$this, 'm4is_f02516']);
 add_filter('memberium/lms/module_post_types', [$this, 'm4is_w67']);
 add_filter('memberium/lms/course_type', [$this, 'm4is_y6691']);
 add_filter('memberium/lms/course_category', [$this, 'm4is_e6578']);
 add_filter('memberium/lms/course_tag', [$this, 'm4is_m7663']);
 add_filter('memberium/lms/user/course/progress', [$this, 'm4is_n3614'], 100, 2);
 add_filter('memberium/lms/course/item/data', [$this, 'm4is_b56091'], 10, 2);
 add_filter('memberium/modules/active/names', function($m4is_y634){
return array_merge($m4is_y634, ['WooSensei for Memberium']);
 
});
 
}    public 
function m4is_f02516($m4is_k52736 ): string {
return 'Sensei';
 
} 
function m4is_y6691($m4is_q485 ): string {
return 'course';
 
} 
function m4is_e6578($m4is_q1852 ): string {
return 'course-category';
 
} public 
function m4is_m7663($m4is_p786 ): string {
 return 'module';
 
} public 
function m4is_w67($m4is_x320 =[]){
 return array_merge($m4is_x320, ['course', 'lesson', ]);
 
}    private 
function m4is_j67($m4is_f087, $m4is_b1249 ){
global $wpdb;
  $m4is_b1249 =(int) $m4is_b1249;
 $m4is_f087 =(int) $m4is_f087;
  $m4is_v2613 ="SELECT `comment_ID` FROM `{$wpdb->comments
}` WHERE `comment_post_ID` = {$m4is_b1249
} AND `user_id` = {$m4is_f087
} AND `comment_approved` = 'passed' AND `comment_type` = 'sensei_lesson_status';";
  $m4is_h516 =(int) $wpdb->get_var($m4is_v2613 );
  if($m4is_h516 ){
$m4is_k863 =get_comment_meta($m4is_h516 );
 
} else{
$m4is_k863 =[];
 
} return $m4is_k863;
 
} private 
function m4is_n3614($m4is_f087, $m4is_n3691 ){
 global $wpdb;
  $m4is_n3691 =(int) $m4is_n3691;
 $m4is_f087 =(int) $m4is_f087;
  $m4is_v2613 ="SELECT `comment_ID` FROM `{$wpdb->comments
}` WHERE `comment_post_ID` = {$m4is_n3691
} AND `user_id` = {$m4is_f087
} AND `comment_approved` = 'complete' AND `comment_type` = 'sensei_course_status';";
 $m4is_h516 =(int) $wpdb->get_var($m4is_v2613 );
  if($m4is_h516 ){
$m4is_k863 =get_comment_meta($m4is_h516 );
 
} else{
$m4is_k863 =[];
 
} return $m4is_k863;
 
} private 
function m4is_p54082($m4is_f087, $m4is_j0361, $m4is_y1347 ){
 $m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
  if(!$m4is_h21895 ){
return;
 
} if($m4is_j0361 == 'course'){
$m4is_c256 =$this->m4is_n3614($m4is_f087, $m4is_y1347);
 
}elseif($m4is_j0361 == 'lesson'){
$m4is_c256 =$this->m4is_j67($m4is_f087, $m4is_y1347);
 
}else{
 return;
 
} $m4is_l79562 =get_post_meta($m4is_y1347);
  if(!empty($m4is_l79562['_is4wp_lms_start_date'][0])){
$m4is_r637 =isset($m4is_l79562['_is4wp_lms_start_date'][0])? $m4is_l79562['_is4wp_lms_start_date'][0]: '';
 $m4is_n12 =isset($m4is_c256['start'][0])? $m4is_c256['start'][0]: '';
 m4is_r83::m4is_c26()->m4is_e186($m4is_h21895, $m4is_r637, $m4is_n12);
 
} if(!empty($m4is_l79562['_is4wp_lms_complete_percent'][0])){
$m4is_r637 =isset($m4is_l79562['_is4wp_lms_complete_percent'][0])? $m4is_l79562['_is4wp_lms_complete_percent'][0]: '';
 $m4is_n12 =isset($m4is_c256['percent'][0])? $m4is_c256['percent'][0]: '';
 m4is_r83::m4is_c26()->m4is_e186($m4is_h21895, $m4is_r637, $m4is_n12);
 
} if(!empty($m4is_l79562['_is4wp_learndash_goals'][0])){
m4is_r83::m4is_c26()->m4is_t64038($m4is_l79562['_is4wp_learndash_goals'][0], $m4is_h21895 );
 
} if(!empty($m4is_l79562['_is4wp_learndash_tags'][0])){
m4is_r83::m4is_c26()->m4is_k98($m4is_l79562['_is4wp_learndash_tags'][0], $m4is_h21895 );
 
} if(!empty($m4is_l79562['_is4wp_learndash_actions'][0])){
m4is_r83::m4is_c26()->m4is_u71903($m4is_l79562['_is4wp_learndash_actions'][0], $m4is_h21895 );
 
} do_action('memberium/lms/completion', $m4is_f087, $m4is_y1347);
 
} private 
function m4is_t7361(){
 global $wpdb;
  $m4is_q65869 ='_is4wp_learndash_autoenroll';
  $m4is_c093 =$wpdb->posts;
 $m4is_e0895 =$wpdb->postmeta;
  $m4is_v2613 ="SELECT `ID`, `meta_value` FROM `{$m4is_c093
}`, `{$m4is_e0895
}` WHERE post_status = 'publish' AND post_type = 'course' AND  post_id = ID AND meta_key = '{$m4is_q65869
}' AND meta_value > '' ";
  $m4is_r02674 =$wpdb->get_results($m4is_v2613, ARRAY_A );
  return $m4is_r02674;
 
} private 
function m4is_b83(){
 return (int) sensei()->version;
 
} private 
function m4is_n6381($m4is_f087, $m4is_n3691 ){
 $m4is_a6814 =$this->m4is_b83();
  if($m4is_a6814 == 2 ){
Sensei_Utils::user_start_course($m4is_f087, $m4is_n3691 );
  
} else{
try {
$m4is_d569 =new Sensei_Frontend;
 $m4is_d569->manually_enrol_learner($m4is_f087, $m4is_n3691 );
 
} catch (exception $m4is_t635 ){
$this->m4is_v257[]=['action' =>'add', 'user_id' =>$m4is_f087, 'course_id' =>$m4is_n3691, ];
 
}
}
} private 
function m4is_e273($m4is_f087, $m4is_n3691){
 $m4is_a6814 =$this->m4is_b83();
  if($m4is_a6814 == 2){
sensei_utils::sensei_remove_user_from_course($m4is_n3691, $m4is_f087);
 
} else{
try {
 $m4is_d569 =new Sensei_Frontend;
 $m4is_f236 =Sensei_Course_Manual_Enrolment_Provider::instance();
  sensei_utils::sensei_remove_user_from_course($m4is_n3691, $m4is_f087);
 $m4is_f236->withdraw_learner($m4is_f087, $m4is_n3691);
 
} catch (Exception $m4is_t635){
$this->m4is_v257[]=['action' =>'remove', 'user_id' =>$m4is_f087, 'course_id' =>$m4is_n3691, ];
 
}
}
} public 
function m4is_r23760(){
 if(empty($this->m4is_v257 )){
return;
  
} foreach($this->m4is_v257 as $m4is_l9671 =>$m4is_v586 ){
 if($m4is_v586['action']== 'add' ){
$this->m4is_n6381($m4is_v586['user_id'], $m4is_v586['course_id']);
 unset($this->m4is_v257[$m4is_l9671]);
  
} if($m4is_v586['action']== 'remove' ){
$this->m4is_e273($m4is_v586['user_id'], $m4is_v586['course_id']);
 unset($this->m4is_v257[$m4is_l9671]);
  
}
}
} public 
function m4is_z38760($m4is_f087, $m4is_k824 ){
 if(user_can($m4is_f087, 'manage_options')){
return;
  
} $m4is_r02674 =$this->m4is_t7361();
  if(empty($m4is_r02674)||!is_array($m4is_r02674)){
return;
  
} $m4is_z6689 =$this->m4is_b83();
 $m4is_p6457 =empty($m4is_k824['keap']['contact']['groups'])? '' : $m4is_k824['keap']['contact']['groups'];
  foreach ($m4is_r02674 as $m4is_m5907){
 $m4is_h51646 =$m4is_m5907['meta_value'];
 $m4is_n3691 =$m4is_m5907['ID'];
  $m4is_s248 =m4is_r83::m4is_c26()->m4is_l5918($m4is_h51646, $m4is_p6457);
 $m4is_o51 =sensei_utils::has_started_course($m4is_n3691, $m4is_f087);
  if($m4is_o51 !== $m4is_s248){
if($m4is_s248){
 $this->m4is_n6381($m4is_f087, $m4is_n3691);
 
}else{
 $this->m4is_e273($m4is_f087, $m4is_n3691);
 
}
}
}
} public 
function m4is_k1862($m4is_f087, $m4is_w2075, $m4is_e21, $m4is_u74, $m4is_a87469){
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ){
return;
 
}$m4is_t27638 =wp_get_post_parent_id($m4is_w2075 );
 $m4is_j0361 =get_post_type($m4is_t27638 );
 $m4is_l79562 =get_post_meta($m4is_w2075);
  if(!empty($m4is_l79562['_is4wp_lms_grade'][0])){
$m4is_r637 =isset($m4is_l79562['_is4wp_lms_grade'][0])? $m4is_l79562['_is4wp_lms_grade'][0]: '';
 $m4is_n12 =$m4is_e21;
 m4is_r83::m4is_c26()->m4is_e186($m4is_h21895, $m4is_r637, "{$m4is_n12
}");
 
} if(!empty($m4is_l79562['_is4wp_lms_completed'][0])){
$m4is_r637 =isset($m4is_l79562['_is4wp_lms_completed'][0])? $m4is_l79562['_is4wp_lms_completed'][0]: '';
 $m4is_n12 =(int) ($m4is_e21 >= $m4is_u74);
 m4is_r83::m4is_c26()->m4is_e186($m4is_h21895, $m4is_r637, $m4is_n12);
 
} do_action('memberium/lms/completion', $m4is_f087, $m4is_w2075 );
 
} public 
function m4is_e59736($m4is_f087, $m4is_b1249 ){
 $this->m4is_p54082($m4is_f087, 'lesson', $m4is_b1249 );
 
} public 
function m4is_p92136($m4is_f087, $m4is_n3691 ){
 $this->m4is_p54082($m4is_f087, 'course', $m4is_n3691);
 
} public 
function m4is_b56091(array $m4is_l91805, int $m4is_b4068 ){
if(get_post_type($m4is_b4068 )!== 'course'){
return $m4is_l91805;
 
}$m4is_f087 =$this->m4is_r1546->m4is_x66();
 $m4is_l91805['access']=m4is_f58::m4is_c26()->m4is_x72168($m4is_b4068, $m4is_f087);
  if(!$m4is_l91805['access']){
$m4is_l91805['status']='locked';
 return $m4is_l91805;
 
} $m4is_l91805['access']=Sensei()->course->is_user_enrolled($m4is_b4068, $m4is_f087 );
  if(!$m4is_l91805['access']){
if(!Sensei()->course->can_current_user_manually_enrol($m4is_b4068)){
$m4is_l91805['status']='locked';
 return $m4is_l91805;
 
} else{
$m4is_l91805['access']=1;
 $m4is_l91805['status']='not_enrolled';
 
}
} if(!Sensei()->course->is_prerequisite_complete($m4is_b4068)){
 $m4is_l91805['access']=0;
 $m4is_l91805['status']='locked';
 return $m4is_l91805;
 
} $m4is_l91805['progress']=Sensei()->course->get_completion_percentage($m4is_b4068, $m4is_f087 );
  return $m4is_l91805;
 
} 
}

