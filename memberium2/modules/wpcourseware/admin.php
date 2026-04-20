<?php
 class_exists('m4is_r83')||die();
 final 
class m4is_j62058 {
private $m4is_x25;
 private $m4is_r1546;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_d4861();
 
}private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_x25 =m4is_s6729::m4is_c26();
 
}private 
function m4is_d4861(){
add_action('admin_init', [$this, 'm4is_m34']);
 add_action('memberium_admin_menu_addons', [$this, 'm4is_p2651']);
 add_filter('memberium/modules/active/names', [$this, 'm4is_f809'], 10, 1 );
 
}   public 
function m4is_p2651(){
$m4is_q20175 ='manage_options';
 if(!current_user_can($m4is_q20175 )){
return;
 
}$m4is_x160 ='memberium';
 $m4is_b9263 =$m4is_q20175;
 add_submenu_page($m4is_x160, 'Memberium WP Courseware', 'WP Courseware', $m4is_q20175, 'memberium-wpcw', [$this, 'm4is_a66493']);
 
}
function m4is_a66493(){
require_once __DIR__ . '/screens/wpcourseware.php';
 
}
function m4is_f809($m4is_y634 ){
return array_merge($m4is_y634, ['WP-Courseware for Memberium Support' ]);
 
}    public 
function m4is_m34(){
$m4is_q485 =$this->m4is_x25->m4is_m6180();
 $m4is_n9768 =['course_unit', ];
 if(in_array($m4is_q485, $m4is_n9768 )){
add_meta_box('is4wp-wpcw-actions', 'WP Courseware Memberium Integration', [$this, 'm4is_w36528'], $m4is_q485, 'side' );
 
}add_action('admin_footer', [$this->m4is_x25, 'm4is_n68517']);
 add_action('save_post', [$this, 'm4is_r46067']);
 
} public 
function m4is_w36528(){
global $post;
  $m4is_l79562 =get_post_meta($post->ID );
 $m4is_r1692 =[];
 $m4is_f69781 =['_is4wp_wpcw_completion_tag', ];
 foreach($m4is_f69781 as $m4is_l9671){
$m4is_r1692[$m4is_l9671]=isset($m4is_l79562[$m4is_l9671][0])? $m4is_l79562[$m4is_l9671][0]: '';
 
}wp_nonce_field($this->m4is_r1546->m4is_j541(), "memberium_membershipaccess_nonce_{$post->ID
}");
 if(in_array($post->post_type, ['course_unit'])){
$m4is_w42 =_e('Unit Completion Tag', 'memberium' );
 $m4is_v586 =empty($m4is_r1692['_is4wp_wpcw_completion_tag'])? '' : $m4is_r1692['_is4wp_wpcw_completion_tag'];
 echo <<<HTMLBLOCK
				<label for="_is4wp_wpcw_completion_tag">{$m4is_w42
}:</label>
				<input name="_is4wp_wpcw_completion_tag" class="multitaglist" style="width:100%; max-width:100%" value="{$m4is_v586
}"><br /><br />';
			HTMLBLOCK;
 
}
}public 
function m4is_r46067($m4is_b4068 ){
if(defined('DOING_AUTOSAVE' )&&DOING_AUTOSAVE ){
return;
 
}$m4is_t475 =$_POST;
 if(empty($m4is_t475["memberium_membershipaccess_nonce_{$m4is_b4068
}"])||!wp_verify_nonce($m4is_t475["memberium_membershipaccess_nonce_{$m4is_b4068
}"], $this->m4is_r1546->m4is_j541())){
return;
 
}if(!current_user_can('edit_posts', $m4is_b4068 )){
return;
 
}$fieldnames =['_is4wp_wpcw_completion_tag', ];
 foreach($fieldnames as $key ){
$m4is_t475[$key]=isset($m4is_t475[$key])? $m4is_t475[$key]: '';
 add_post_meta($m4is_b4068, $key, $m4is_t475[$key], true )or update_post_meta($m4is_b4068, $key, $m4is_t475[$key]);
 
}
}
}

