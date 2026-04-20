<?php
 class_exists('m4is_r83' )||die();
  final 
class m4is_z352 {
private $m4is_r1546;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}
function __construct(){
$this->m4is_i702();
 $this->m4is_d4861();
 if(is_admin()){
require __DIR__ . '/admin.php';
 m4is_j96::m4is_c26();
 
}
}private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
}private 
function m4is_d4861(){
add_action('learn-press/user-completed-lesson', [$this, 'm4is_k06'], 10, 3 );
  add_action('learn-press/user-course-finished', [$this, 'm4is_m4057'], 10, 3 );
  add_action('learn-press/user/quiz-finished', [$this, 'm4is_r625'], 10, 3 );
  add_action('memberium/session/created', [$this, 'm4is_m74960'], 10, 1 );
 add_filter('learn-press/has-enrolled-course', [$this, 'm4is_b968'], 10, 3 );
 add_filter('memberium/posts/unenhanced', [$this, 'm4is_j63'], 10, 1 );
 add_filter('memberium/lms/module_post_types', [$this, 'm4is_w67']);
 add_filter('memberium/lms/name', [$this, 'm4is_f02516']);
 
}private 
function m4is_y7945(): array {
static $m4is_r02674 =[];
 if(empty($m4is_r02674 )){
global $wpdb;
 $m4is_v2613 ="SELECT `ID`, `meta_value` FROM %i, %i WHERE `post_status` = 'publish' AND `post_type` = 'lp_course' AND `post_id` = `ID` AND `meta_key` = '_is4wp_learndash_autoenroll' AND `meta_value` > '' ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $wpdb->posts, $wpdb->postmeta );
 $m4is_r02674 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 
}return $m4is_r02674;
 
} private 
function m4is_u98(int $m4is_f087 ){
if(empty($m4is_f087 )){
return [];
 
}if(!method_exists('lp_user', 'get_orders')){
error_log('Memberium: [error] LearnPress  LP_User get_orders() method missing.');
 return [];
 
}$m4is_z2416 =new lp_user($m4is_f087 );
  $m4is_t1829 =$m4is_z2416->get_orders();
 $m4is_w8256 =[];
 foreach ($m4is_t1829 as $m4is_n3691 =>$m4is_c6016 ){
$m4is_w8256[]=$m4is_n3691;
 
}return $m4is_w8256;
 
} public 
function m4is_j63(array $m4is_l15 ): array {
$m4is_l15[]='lp_order';
 return $m4is_l15;
 
}public 
function m4is_m74960($m4is_k824 =false ): void {
if(empty($m4is_k824['keap']['contact']['groups'])){
return;
 
}global $wpdb;
 static $m4is_d890 =[];
 $m4is_f087 =isset($m4is_k824['memb_user']['user_id'])? $m4is_k824['memb_user']['user_id']: 0;
 $m4is_r02674 =$this->m4is_y7945();
 if(empty($m4is_r02674)){
return;
 
}$m4is_s6672 =$this->m4is_u98($m4is_f087 );
 foreach ($m4is_r02674 as $m4is_m5907 ){
$m4is_b4068 =$m4is_m5907['ID'];
 $m4is_x7419 =in_array($m4is_b4068, $m4is_s6672)||in_array($m4is_b4068, $m4is_d890);
 $m4is_c345 =trim($m4is_m5907['meta_value'], ',');
 if(!$m4is_x7419 ){
if(m4is_r83::m4is_c26()->m4is_m2480($m4is_c345, $m4is_k824)){
 if(class_exists('LearnPress\Models\UserItems\UserCourseModel' )){
$m4is_a05 =new LearnPress\Models\UserItems\UserCourseModel;
 $m4is_a05->user_id =$m4is_f087;
 $m4is_a05->item_id =$m4is_b4068;
 $m4is_a05->item_type =LP_COURSE_CPT;
 $m4is_a05->ref_type ='';
 $m4is_a05->status =LP_COURSE_ENROLLED;
 $m4is_a05->graduation =LP_COURSE_GRADUATION_IN_PROGRESS;
 $m4is_a05->start_time =gmdate(LP_Datetime::$format, time());
 $m4is_a05->save();
 $m4is_d890[]=$m4is_b4068;
 
}else{
error_log('Memberium: [error] LearnPress has broken their own API for a third time.  LearnPress\Models\UserItems\UserCourseModel not found.' );
 
}
}
}
}
} public 
function m4is_b968($m4is_v9507, int $m4is_f087, int $m4is_n3691 ){
$m4is_c345 =get_post_meta($m4is_n3691, '_is4wp_learndash_autoenroll', true );
 if(empty($m4is_c345 )){
return $m4is_v9507;
 
}$m4is_i935 =m4is_q82::m4is_d6758($m4is_f087, 'keap', 'contact', []);
 if(!empty($m4is_i935['id'])){
$m4is_v9507 =$this->m4is_r1546->m4is_m2480($m4is_c345, $m4is_i935 );
 
}return $m4is_v9507;
 
} public 
function m4is_k06(int $m4is_b1249, $m4is_u6591, int $m4is_f087 ){
$m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ||!$m4is_f087 ){
return;
 
}$m4is_r1692 =get_post_meta($m4is_b1249 );
 if(!empty($m4is_r1692['_is4wp_learndash_goals'][0])){
m4is_r83::m4is_c26()->m4is_t64038($m4is_r1692['_is4wp_learndash_goals'][0], $m4is_h21895 );
 
}if(!empty($m4is_r1692['_is4wp_learndash_tags'][0])){
m4is_r83::m4is_c26()->m4is_k98($m4is_r1692['_is4wp_learndash_tags'][0], $m4is_h21895 );
 
}if(!empty($m4is_r1692['_is4wp_learndash_actions'][0])){
m4is_r83::m4is_c26()->m4is_u71903($m4is_r1692['_is4wp_learndash_actions'][0], $m4is_h21895 );
 
}do_action('memberium/lms/completion', (int) $m4is_f087, (int) $m4is_b1249 );
 
} public 
function m4is_r625(int $m4is_w2075, int $m4is_n3691, int $m4is_f087 ){
$m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if($m4is_h21895 &&$m4is_w2075 ){
$m4is_b53824 =get_post_meta($m4is_w2075, '_lp_passing_grade', true );
 if($m4is_b53824 !== false ){
 $m4is_b53824 =(int) $m4is_b53824;
 $m4is_z2416 =new LP_User($m4is_f087 );
 $m4is_i51 =$m4is_z2416->get_quiz_results($m4is_w2075, $m4is_n3691 );
 $m4is_z3170 =(int) $m4is_i51;
 $m4is_q60 =($m4is_z3170 >= $m4is_b53824 );
 $m4is_r1692 =get_post_meta($m4is_w2075 );
 if($m4is_q60 ){
if(!empty($m4is_r1692['_is4wp_learndash_goals'][0])){
$this->m4is_r1546->m4is_t64038($m4is_r1692['_is4wp_learndash_goals'][0], $m4is_h21895 );
 
}if(!empty($m4is_r1692['_is4wp_learndash_tags'][0])){
$this->m4is_r1546->m4is_k98($m4is_r1692['_is4wp_learndash_tags'][0], $m4is_h21895 );
 
}if(!empty($m4is_r1692['_is4wp_learndash_actions'][0])){
$this->m4is_r1546->m4is_u71903($m4is_r1692['_is4wp_learndash_actions'][0], $m4is_h21895 );
 
}do_action('memberium/lms/completion', (int) $m4is_f087, (int) $m4is_w2075 );
 
}else{
if(!empty($m4is_r1692['_is4wp_learndash_fail_goals'][0])){
$this->m4is_r1546->m4is_t64038($m4is_r1692['_is4wp_learndash_fail_goals'][0], $m4is_h21895 );
 
}if(!empty($m4is_r1692['_is4wp_learndash_fail_tags'][0])){
$this->m4is_r1546->m4is_k98($m4is_r1692['_is4wp_learndash_fail_tags'][0], $m4is_h21895 );
 
}if(!empty($m4is_r1692['_is4wp_learndash_fail_actions'][0])){
$this->m4is_r1546->m4is_u71903($m4is_r1692['_is4wp_learndash_fail_actions'][0], $m4is_h21895 );
 
}
}
}
} 
} public 
function m4is_m4057(int $m4is_n3691, int $m4is_f087, int $m4is_s66470 ){
$m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_h21895 ||!$m4is_f087 ){
return;
 
}$m4is_r1692 =get_post_meta($m4is_n3691 );
 if(!empty($m4is_r1692['_is4wp_learndash_goals'][0])){
$this->m4is_r1546->m4is_t64038($m4is_r1692['_is4wp_learndash_goals'][0], $m4is_h21895 );
 
}if(!empty($m4is_r1692['_is4wp_learndash_tags'][0])){
$this->m4is_r1546->m4is_k98($m4is_r1692['_is4wp_learndash_tags'][0], $m4is_h21895 );
 
}if(!empty($m4is_r1692['_is4wp_learndash_actions'][0])){
$this->m4is_r1546->m4is_u71903($m4is_r1692['_is4wp_learndash_actions'][0], $m4is_h21895 );
 
}do_action('memberium/lms/completion', (int) $m4is_f087, (int) $m4is_n3691 );
 
}public 
function m4is_w67($m4is_x320 =[]){
return array_merge($m4is_x320, ['lp_course', 'lp_lesson', 'lp_quiz', ]);
 
}public 
function m4is_f02516($m4is_k52736 ): string {
return 'LearnPress';
 
}   private 
function m4is_n60($m4is_f087, $m4is_n3691 ){
if(empty($m4is_n3691)||empty($m4is_f087)){
return false;
 
}$m4is_m421 =['learn_press_create_order', 'learn_press_add_order_item', 'learn_press_update_order_status', ];
 foreach ($m4is_m421 as $m4is_a1056 ){
if(!function_exists($m4is_a1056 )){
error_log(sprintf('Memberium: [error] LearnPress %s function missing.', $m4is_a1056 ));
 return false;
 
}
}if(!class_exists('lp_user' )){
error_log('Memberium: [error] LearnPress lp_user class missing.');
 return false;
 
}$m4is_z2416 =new lp_user($m4is_f087);
  $m4is_y5760 =learn_press_create_order(null );
 $m4is_c6016 =$m4is_y5760->get_id();
 $m4is_y5760->set_user_id($m4is_f087);
 $m4is_y5760->save();
  learn_press_add_order_item($m4is_c6016, $m4is_n3691 );
 learn_press_update_order_status($m4is_c6016, 'lp-completed' );
  return $m4is_c6016;
 
} 
}

