<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_c58016 {
static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_d4861();
 
}private 
function m4is_d4861(){
add_action('admin_init', [$this, 'm4is_m34']);
 
} private 
function m4is_q8056(){
return ['course', 'lesson', 'llms_quiz', ];
 
} private 
function m4is_n436(){
return ['_is4wp_learndash_achievement', '_is4wp_learndash_actions', '_is4wp_learndash_autoenroll', '_is4wp_learndash_autojoin', '_is4wp_learndash_goals', '_is4wp_learndash_redirect', '_is4wp_learndash_shortcodes', '_is4wp_learndash_tags', '_is4wp_lms_complete_percent', '_is4wp_lms_completed', '_is4wp_lms_grade', '_is4wp_lms_start_date', ];
 
} private 
function m4is_c4768(int $m4is_a31606 ): string {
$m4is_i7193 =m4is_j4156::m4is_z8359();
 $m4is_r37596 ='';
 if(!empty($m4is_i7193)){
$m4is_r37596 ='<option value="0">(No Actions)</option>';
;
 foreach ($m4is_i7193 as $m4is_w64602 =>$m4is_t0631 ){
$m4is_a437 =$m4is_a31606 == $m4is_w64602 ? ' selected="selected" ' : '';
 $m4is_r37596 .= '<option value="' . $m4is_w64602 . '" ' . $m4is_a437 . '>' . $m4is_t0631 . '</option>';
 
}
}return $m4is_r37596;
 
} public 
function m4is_m34(){
$m4is_q485 =m4is_s6729::m4is_c26()->m4is_m6180();
 $m4is_q6396 =$this->m4is_q8056();
 if(in_array($m4is_q485, $m4is_q6396 )){
add_meta_box('is4wp-liferlms-actions', 'LifterLMS Memberium Integration', [$this, 'm4is_c7231'], $m4is_q485, 'side' );
 
}add_action('save_post', [$this, 'm4is_k34756'], 10, 3 );
 
} public 
function m4is_c7231($m4is_m5907, $m4is_i5630 ): void {
if(!is_a($m4is_m5907, 'WP_Post' )){
return;
 
}$m4is_v46257 =$this->m4is_q8056();
 if(!in_array($m4is_m5907->post_type, $m4is_v46257 )){
return;
 
}$m4is_b4068 =$m4is_m5907->ID;
 $m4is_q485 =$m4is_m5907->post_type;
 $m4is_l271 =get_post_meta($m4is_b4068 );
 $m4is_r1692 =[];
 $m4is_f69781 =$this->m4is_n436();
 foreach($m4is_f69781 as $m4is_l9671){
$m4is_r1692[$m4is_l9671]=isset($m4is_l271[$m4is_l9671][0])? $m4is_l271[$m4is_l9671][0]: '';
 
}wp_nonce_field(__FILE__, "memberium_lifterlms_actions_nonce_{$m4is_b4068
}");
 $m4is_w49826 =empty($m4is_r1692['_is4wp_learndash_goals'])? '' : $m4is_r1692['_is4wp_learndash_goals'];
 $m4is_h40297 =empty($m4is_r1692['_is4wp_learndash_tags'])? '' : $m4is_r1692['_is4wp_learndash_tags'];
 $m4is_m23915 =empty($m4is_r1692['_is4wp_learndash_autoenroll'])? '' : $m4is_r1692['_is4wp_learndash_autoenroll'];
 $m4is_e86 =$this->m4is_c4768((int) $m4is_r1692['_is4wp_learndash_actions']);
 if(in_array($m4is_q485, ['course' ])){
echo '<label for="_is4wp_learndash_autoenroll">' . _e("AutoEnroll Tags", 'memberium'). ':</label> ';
 echo '<input value= "', $m4is_m23915, '" name="_is4wp_learndash_autoenroll" class="multitaglist" style="width:100%; max-width:100%"><br /><br />';
 
}echo '<p>On completion of this section, execute the following actions:</p>';
 echo '<label for="_is4wp_learndash_goals">' . _e("Achieve this Goal", 'memberium'). ':</label> ';
 echo '<input value="', $m4is_w49826, '" name="_is4wp_learndash_goals" style="width:100%; max-width:100%"><br /><br />';
 echo '<label for="_is4wp_learndash_tags">' . _e("Apply these Tags", 'memberium'). ':</label> ';
 echo '<input value="', $m4is_h40297, '" name="_is4wp_learndash_tags" class="multitaglist" style="width:100%; max-width:100%"><br /><br />';
 if(!empty($m4is_e86 )){
echo '<label for="_is4wp_learndash_actions">' . _e("Run this Actionset", 'memberium'). ':</label> ';
 echo '<select class="actionset-selector" name="_is4wp_learndash_actions" style="width:100%; max-width:100%">';
 echo $m4is_e86;
 echo '</select>';
 
}
}public 
function m4is_k34756(int $m4is_b4068, $m4is_m5907, $m4is_a686 ): void {
 if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE){
return;
 
} $m4is_i65 ='memberium_lifterlms_actions_nonce_' . $m4is_b4068;
 if(empty($_POST[$m4is_i65])||!wp_verify_nonce($_POST[$m4is_i65], __FILE__)){
return;
 
}if(!current_user_can('edit_posts', $m4is_b4068)){
return;
 
}$m4is_u450 =$this->m4is_n436();
 foreach($m4is_u450 as $m4is_l9671 ){
if(isset($_POST[$m4is_l9671])){
if(empty($_POST[$m4is_l9671])){
delete_post_meta($m4is_b4068, $m4is_l9671 );
 
}else{
update_post_meta($m4is_b4068, $m4is_l9671, $_POST[$m4is_l9671]);
 
}
}
}
}
}

