<?php
 class_exists('m4is_p60' )||die();
 final 
class m4is_c48235 {
static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_c7460();
 $this->m4is_d4861();
 
}private 
function m4is_d4861(){
add_action('admin_init', [$this, 'm4is_e067']);
 
}private 
function m4is_c7460(){
if(!defined('WPCOMPLETE_IS_ACTIVATED' )){
return;
 
}$m4is_w03678 =constant('WPCOMPLETE_IS_ACTIVATED' );
 if($m4is_w03678 == false ){
$m4is_t634 =array_filter(explode(',', get_option('wpcomplete_post_type', '')));
 $m4is_t14608 =get_post_types();
 $m4is_d35918 =['attachment'];
 foreach($m4is_t14608 as $m4is_g035){
if(!in_array($m4is_g035, $m4is_d35918)){
if(!in_array($m4is_g035, $m4is_t634)){
$m4is_r0175 =get_post_type_object($m4is_g035);
 if($m4is_r0175->public){
$m4is_t634[]=$m4is_g035;
 
}
}
}
}if(!empty($m4is_t634)){
$m4is_t634 =implode(',', $m4is_t634);
 update_option('wpcomplete_post_type', $m4is_t634, false);
 
}
}
}
function m4is_b247(){
global $post;
 $m4is_r1692 =[];
 $m4is_l271 =get_post_meta($post->ID);
 $m4is_f69781 =['_is4wp_wpcomplete_tags', '_is4wp_wpcomplete_badges', ];
 foreach($m4is_f69781 as $m4is_l9671 ){
if(isset($m4is_l271[$m4is_l9671][0])){
$m4is_r1692[$m4is_l9671]=$m4is_l271[$m4is_l9671][0];
 
}else{
$m4is_r1692[$m4is_l9671]='';
 
}
}unset($metas, $m4is_f69781, $m4is_l9671);
 wp_nonce_field(m4is_r83::m4is_c26()->m4is_j541(), "memberium_wpcomplete_nonce_{$post->ID
}");
 echo '<label for="_is4wp_wpcomplete_tags">' . _e("Apply these Tags", 'memberium' ). ':</label> ';
 echo '<input name="_is4wp_wpcomplete_tags" class="multitaglist" style="width:100%; max-width:100%" value="', ($m4is_r1692['_is4wp_wpcomplete_tags']> '' ? $m4is_r1692['_is4wp_wpcomplete_tags']: '' ), '"><br /><br />';
 
}
function m4is_t14670($m4is_b4068){
 if(defined('DOING_AUTOSAVE' )&&DOING_AUTOSAVE ){
return;
 
} if(empty($_POST["memberium_wpcomplete_nonce_{$m4is_b4068
}"])||!wp_verify_nonce($_POST["memberium_wpcomplete_nonce_{$m4is_b4068
}"], m4is_r83::m4is_c26()->m4is_j541())){
return;
 
}if(!current_user_can('edit_posts', $m4is_b4068)){
return;
 
}$m4is_f69781 =['_is4wp_wpcomplete_tags', ];
 foreach($m4is_f69781 as $m4is_l9671 ){
$_POST[$m4is_l9671]=isset($_POST[$m4is_l9671])? $_POST[$m4is_l9671]: '';
 if(empty($_POST[$m4is_l9671])){
delete_post_meta($m4is_b4068, $m4is_l9671);
 
}else{
add_post_meta($m4is_b4068, $m4is_l9671, $_POST[$m4is_l9671], true )or update_post_meta($m4is_b4068, $m4is_l9671, $_POST[$m4is_l9671]);
 
}
}
}
function m4is_l70439($m4is_b4068){
return !empty(get_post_meta($m4is_b4068, 'wpcomplete', true));
 
}
function m4is_e067(){
$m4is_q485 =m4is_s6729::m4is_c26()->m4is_m6180();
 add_meta_box('is4wp-wpcomplete-actions', 'WPComplete Memberium Integration', [$this, 'm4is_b247'], $m4is_q485, 'side');
 add_action('save_post', [$this, 'm4is_t14670']);
 
}
}

