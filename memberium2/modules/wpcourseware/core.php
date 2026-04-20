<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_d36915 {
private $m4is_r1546;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_i702();
 $this->m4is_d4861();
 $this->m4is_f3276();
 
} private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
} private 
function m4is_d4861(): void {
 add_action('wpcw_user_completed_unit', [$this, 'm4is_g850'], 10, 3);
 add_action('wpcw_user_completed_module', [$this, 'm4is_w29143'], 10, 3);
 add_action('wpcw_user_completed_course', [$this, 'm4is_v52698'], 10, 3);
  add_action('memberium/session/updated', [$this, 'm4is_k89736'], 10, 2 );
  add_filter('memberium/lms/module_post_types', [$this, 'm4is_w67']);
  add_filter('memberium/lms/name', [$this, 'm4is_f02516']);
 
} private 
function m4is_f3276(): void {
if(is_admin()){
include __DIR__ . '/admin.php';
 m4is_j62058::m4is_c26();
 
}
}    
function m4is_f02516($m4is_k52736 ): string {
return 'WP-Courseware';
 
} 
function m4is_w67($m4is_x320 =[]): array {
return array_merge($m4is_x320, ['course_unit', ]);
 
}    
function m4is_w29143($m4is_f087, $m4is_j567, $m4is_g24863 ): void {
$m4is_y1347 =$m4is_g24863->module_id;
 $m4is_i035 =get_option('memberium_wpcw', []);
 if(isset($m4is_i035['modules']['completion_tag'][$m4is_y1347])){
$m4is_l9321 =$m4is_i035['modules']['completion_tag'][$m4is_y1347];
 if(!empty($m4is_l9321 )){
$this->m4is_r1546->m4is_k98($m4is_l9321);
 
}
}do_action('memberium/lms/completion', $m4is_f087, $m4is_j567 );
 
} 
function m4is_g850($m4is_f087, $m4is_j567, $m4is_g24863 ){
$m4is_l9321 =get_post_meta($m4is_j567, '_is4wp_wpcw_completion_tag', true );
 if($m4is_l9321 ){
$this->m4is_r1546->m4is_k98($m4is_l9321 );
 
}do_action('memberium/lms/completion', $m4is_f087, $m4is_j567 );
 
} 
function m4is_v52698($m4is_f087, $m4is_j567, $m4is_g24863 ){
$m4is_n3691 =$m4is_g24863->course_id;
 $m4is_i035 =get_option('memberium_wpcw', []);
 if(isset($m4is_i035['courses']['completion_tag'][$m4is_n3691])){
$m4is_l9321 =$m4is_i035['courses']['completion_tag'][$m4is_n3691];
 if(!empty($m4is_l9321 )){
$this->m4is_r1546->m4is_k98($m4is_l9321 );
 
}
}do_action('memberium/lms/completion', $m4is_f087, $m4is_j567 );
 
} 
function m4is_k89736($m4is_f087, $m4is_k824 ){
if(!function_exists('WPCW_courses_syncUserAccess' )){
return;
 
}$m4is_z91 =get_option('memberium_wpcw', []);
 $m4is_z91 =isset($m4is_z91['courses']['access_tags'])? $m4is_z91['courses']['access_tags']: [];
 $m4is_w675 =WPCW_users_getUserCourseList($m4is_f087 );
 $m4is_k69 =[];
  foreach($m4is_w675 as $m4is_x981 ){
$m4is_n3691 =$m4is_x981->course_id;
 $m4is_h4706 =isset($m4is_z91[$m4is_n3691])? $m4is_z91[$m4is_n3691]: '';
 if($m4is_x981->course_opt_user_access == 'default_show' ){
$m4is_k69[]=$m4is_n3691;
 
}elseif(!empty($m4is_h4706 )){
if($this->m4is_r1546->m4is_m2480($m4is_h4706, $m4is_k824 )){
$m4is_k69[]=$m4is_n3691;
 
}
}
}foreach($m4is_z91 as $m4is_n3691 =>$m4is_h4706 ){
if($this->m4is_r1546->m4is_m2480($m4is_h4706, $m4is_k824 )){
$m4is_k69[]=$m4is_n3691;
 
}
} $m4is_k69 =array_unique($m4is_k69 );
 WPCW_courses_syncUserAccess($m4is_f087, $m4is_k69, 'sync' );
 
} 
}

