<?php

/**
* Copyright (c) 2018-2024 David J Bullock
* Web Power and Light
*/


 class_exists('m4is_s6729' )||die();
 final 
class m4is_a98361 {
private m4is_r83 $m4is_r1546;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_d4861();
 
}private 
function m4is_d4861(){
add_action('init', [$this, 'm4is_r4760'], 10 );
 add_action('admin_print_scripts-edit.php', [$this, 'm4is_v653']);
 add_action('wp_ajax_memberium_save_bulk_edit', [$this, 'm4is_o4185']);
 add_action('quick_edit_custom_box', [$this, 'm4is_u98260'], 10, 2 );
 add_action('bulk_edit_custom_box', [$this, 'm4is_b8710'], 10, 2 );
 add_filter('bulk_actions-edit-post', [$this, 'm4is_f53']);
 add_filter('bulk_actions-edit-page', [$this, 'm4is_f53']);
 add_filter('handle_bulk_actions-edit-post', [$this, 'm4is_u648'], 10, 3 );
 add_filter('handle_bulk_actions-edit-page', [$this, 'm4is_u648'], 10, 3 );
 
}   
function m4is_r4760(){
if(!isset($_GET['memb_accesscontrol_bulkedit_nonce'])){
return;
 
}if(!isset($_GET['screen'])||$_GET['screen']!= 'edit-post' ){
return;
 
}if(!isset($_GET['post'])||!is_array($_GET['post'])){
return;
 
}if(!current_user_can('edit_posts')){
return;
 
}$this->m4is_o4185();
 
}
function m4is_b8710($m4is_w93, $m4is_q485 ){
static $m4is_v5618 =true;
 if($m4is_v5618){
$m4is_v5618 =false;
 wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memb_accesscontrol_bulkedit_nonce');
 
}echo '<fieldset class="inline-edit-col-right inline-edit-', $m4is_w93, '">';
 echo '<div class="inline-edit-col column-', $m4is_w93 , '">';
 echo '<label class="inline-edit-group">';
 switch ($m4is_w93){
case 'memberships': $m4is_m96240 =$this->m4is_r1546->m4is_j498('memberships' );
 $m4is_g52 =[];
 foreach ($m4is_m96240 as $m4is_l9671 =>$m4is_j90523){
$m4is_g52[]=$m4is_l9671;
 
}$m4is_g52 =implode(',', $m4is_g52);
 echo '<input type="hidden" name="bulkedit-membershiplist" id="bulkedit-membershiplist" value="', $m4is_g52, '">';
  if(count($m4is_m96240)> 0){
echo '<ul class="cat-checklist category-checklist">';
 echo '<em>Membership Access</em>';
 echo '<label for="memb_useexisting"><input type="checkbox" name="memb_useexisting" value="1" id="memb_useexisting"> <em>Use Existing Memberships</em></label>';
 foreach ($m4is_m96240 as $m4is_d07693 =>$m4is_w64){
echo "<label for='memb_membership_level_{$m4is_d07693
}'><input type='checkbox' name='memb_membership_level_{$m4is_d07693
}' value='{$m4is_d07693
}' id='memb_membership_level_{$m4is_d07693
}'> " . stripslashes($m4is_w64['name']). '</label>';
 
}
}echo '<label for="memb_anonymousonly"><input type="checkbox" name="memb_anonymousonly" value="1" id="memb_anonymousonly"> Logged Out Only</label>';
 echo '<label for="memb_anyloggedinuser"><input type="checkbox" name="memb_anyloggedinuser" value="1" id="memb_anyloggedinuser"> Any Logged In User</label>';
 echo '<label for="memb_google1stclick"><input type="checkbox" name="memb_google1stclick" value="1" id="memb_google1stclick"> Google 1st Click Free</label>';
 echo '<label for="memb_facebookcrawler"><input type="checkbox" name="memb_facebookcrawler" value="1" id="memb_facebookcrawler"> Facebook Crawler Access</label>';
 echo '</ul>';
 echo '<hr />';
 echo '<label for="memb_prohibitedaction"><span class="title" style="width:75px;">Prohibited</span><select name="memb_prohibitedaction" id="memb_prohibitedaction">';
 echo '<option value="default">Default</option>';
 echo '<option value="redirect">Redirect</option>';
 echo '<option value="hide">Hide Completely</option>';
 echo '<option value="excerpt">Show Excerpt Only</option>';
 echo '</select></label>';
 echo '<label><span class="title" style="width:75px;">Redirect URL  </span><input type="text" name="memb_redirecturl" value="" placeholder="" id="memb_redirecturl"></label>';
 break;
 
}echo '</label></div></fieldset>';
 
}
function m4is_v653(){
$m4is_p95312 =plugins_url('memberium-ac');
 wp_enqueue_script('memberium-admin-edit', $m4is_p95312 . '/js/quickedit.js', ['jquery', 'inline-edit-post'], '', true);
 
}
function m4is_r8063($m4is_w93, $m4is_q485){
static $m4is_c306 =true;
 if($m4is_w93 <> 'memberships'){
return;
 
}if($m4is_c306){
$m4is_c306 =false;
 wp_nonce_field(constant('MEMBERIUM_MODULES_DIR' ), 'memb_accesscontrol_quickedit_nonce');
 
}echo '<div style="clear:both;"></div>';
 switch ($m4is_w93){
case 'memberships': $m4is_m96240 =$this->m4is_r1546->m4is_m6617();
 echo '<fieldset class="inline-edit-col-left inline-edit-', $m4is_w93, '">';
 echo '<div class="inline-edit-col column-', $m4is_w93, '">';
 echo '<label class="inline-edit-group">';
 if(count($m4is_m96240)> 0){
echo '<em><strong>Membership Access</strong></em><br>';
 echo '<ul>';
 foreach ($m4is_m96240 as $id =>$membership){
echo '<label for="memb_membership_' . $id . '" style="float:left;margin-right:25px;"><input type="checkbox" name="memb_membership_levels[' . $id . ']" value="' . $id . '" id="memb_membership_' . $id . '"> ' . stripslashes($membership['name']). '</label>';
 
}unset($m4is_m96240, $id, $membership);
 echo '</ul>';
 
}echo '</label></div></fieldset>';
 echo '<fieldset class="inline-edit-col-right inline-edit-', $m4is_w93, '">';
 echo '<div class="inline-edit-col column-', $m4is_w93, '">';
 echo '<label class="inline-edit-group">';
 echo '<em><strong>Other Access Controls</strong></em><br>';
 echo '<label for="memb_anyloggedinuser"><input type="checkbox" name="memb_anyloggedinuser" value="1" id="memb_anyloggedinuser">Any Logged In User</label>';
 echo '<label for="memb_anonymousonly"><input type="checkbox" name="memb_anonymousonly" value="1" id="memb_anonymousonly">Logged Out Only</label>';
 echo '<label for="memb_google1stclick"><input type="checkbox" name="memb_google1stclick" value="1" id="memb_google1stclick">Google 1st Click Free</label>';
 echo '<label><span class="title" style="width:75px;">Prohibited</span><select name="memb_prohibitedaction" id="memb_prohibitedaction">';
 echo '<option value="default">Default</option>';
 echo '<option value="redirect">Redirect</option>';
 echo '<option value="hide">Hide Completely</option>';
 echo '<option value="excerpt">Show Excerpt Only</option>';
 echo '</select></label>';
 echo '<label><span class="title" style="width:75px;">Redirect URL  </span><input type="text" name="memb_redirecturl" value="" placeholder="" id="memb_redirecturl"></label>';
 echo '</label></div></fieldset>';
 
}
}
function m4is_c125(int $m4is_b4068, WP_Post $m4is_m5907, bool $m4is_a686 ){
$m4is_a27648 ='memb_accesscontrol_quickedit_nonce';
 if(!isset($_POST[$m4is_a27648 ])||!wp_verify_nonce($_POST[$m4is_a27648 ], constant('MEMBERIUM_MODULES_DIR' ))){
return;
 
}if(!current_user_can('edit_post', $m4is_b4068 )){
return;
 
} $_POST['memb_anonymousonly']=isset($_POST['memb_anonymousonly'])? $_POST['memb_anonymousonly']: 0;
 $_POST['memb_google1stclick']=isset($_POST['memb_google1stclick'])? $_POST['memb_google1stclick']: 0;
 $_POST['memb_loggedin']=isset($_POST['memb_loggedin'])? $_POST['memb_loggedin']: '';
 $_POST['memb_membership_levels']=isset($_POST['memb_membership_levels'])? $_POST['memb_membership_levels']: '';
 $_POST['memb_redirecturl']=isset($_POST['memb_redirecturl'])? trim($_POST['memb_redirecturl']): '';
  add_post_meta($m4is_b4068, '_memberium_google_1stclick', (int) $_POST['memb_google1stclick'], true)or update_post_meta($m4is_b4068, '_memberium_google_1stclick', $_POST['memb_google1stclick']);
 add_post_meta($m4is_b4068, '_memberium_anonymous_only', (int) $_POST['memb_anonymousonly'], true)or update_post_meta($m4is_b4068, '_memberium_anonymous_only', $_POST['memb_anonymousonly']);
 add_post_meta($m4is_b4068, '_memberium_loggedin', (int) $_POST['memb_loggedin'], true)or update_post_meta($m4is_b4068, '_memberium_loggedin', $_POST['memb_loggedin']);
  if((int) $_POST['memb_anonymousonly']== 1){
$_POST['memb_membership_levels']='';
 
}$m4is_i629 =implode(',', (array)$_POST['memb_membership_levels']);
 add_post_meta($m4is_b4068, '_memberium_membership_levels', $m4is_i629, true)or update_post_meta($m4is_b4068, '_memberium_membership_levels', $m4is_i629);
  add_post_meta($m4is_b4068, '_memberium_prohibited_action', $_POST['memb_prohibitedaction'], true)or update_post_meta($m4is_b4068, '_memberium_prohibited_action', $_POST['memb_prohibitedaction']);
 delete_post_meta($m4is_b4068, '_memberium_hide_completely');
 if($_POST['memb_prohibitedaction']!= 'redirect'){
$_POST['memb_redirecturl']='';
 
} add_post_meta($m4is_b4068, '_memberium_redirect_url', $_POST['memb_redirecturl'], true)or update_post_meta($m4is_b4068, '_memberium_redirect_url', $_POST['memb_redirecturl']);
 
} 
function m4is_o4185(){
if(!current_user_can('edit_posts')&&!current_user_can('edit_others_posts')){
return;
 
}echo __LINE__, ' @ ', __METHOD__;
 print_r(func_get_args(), true );
 die();
   $m4is_v16 =empty($_POST['memb_anonymousonly'])? 0 : (int) $_POST['memb_anonymousonly'];
 $m4is_s3826 =empty($_POST['memb_anyloggedinuser'])? 0 : (int) $_POST['memb_anyloggedinuser'];
 $m4is_m5686 =empty($_POST['memb_facebookcrawler'])? 0 : (int) $_POST['memb_facebookcrawler'];
 $m4is_l61 =empty($_POST['memb_google1stclick'])? 0 : (int) $_POST['memb_google1stclick'];
 $m4is_k26 =empty($_POST['post_ids'])? []: $_POST['post_ids'];
 $m4is_p1084 =empty($_POST['memb_prohibitedaction'])? 'default' : $_POST['memb_prohibitedaction'];
 $m4is_f56 =empty($_POST['memb_redirecturl'])? '' : trim($_POST['memb_redirecturl']);
 $m4is_a469 =empty($_POST['memb_useexisting'])? 0 : 1;
 $m4is_i629 =isset($_POST['memb_memberships'])&&!empty($_POST['memb_memberships'])? $_POST['memb_memberships']: '';
 if($m4is_v16 == 1){
$_POST['memb_memberships']='';
 
}if(!empty($_POST['memb_memberships'])){

}foreach ($m4is_k26 as $m4is_b4068){
if(current_user_can('edit_post', $m4is_b4068)){
$m4is_b9263 =['prohibited_action' =>$m4is_p1084, 'redirect_url' =>$m4is_f56, 'facebook_crawler' =>$m4is_m5686, 'google_1st_click' =>$m4is_l61, ];
 m4is_w831::m4is_f0691($m4is_b4068, $m4is_b9263);
  if(!$m4is_a469){
m4is_w831::m4is_f0691($m4is_b4068, 'memberships', $m4is_i629);
  if(!empty($_POST['memb_memberships'])){
$m4is_b9263 =['anonymous_only' =>$m4is_v16, 'any_loggedin_user' =>$m4is_s3826, 'any_membership' =>'', 'memberships' =>$m4is_i629, ];
 m4is_w831::m4is_f0691($m4is_b4068, $m4is_b9263);
 
}else{
$m4is_b9263 =['anonymous_only' =>$m4is_v16, 'any_loggedin_user' =>$m4is_s3826, 'any_membership' =>'', ];
 m4is_w831::m4is_f0691($m4is_b4068, $m4is_b9263);
 
}
}else{

}
}else{

}
}
}
function m4is_u98260($m4is_w93, $m4is_q485){
static $m4is_c306 =TRUE;
 if($m4is_c306){
$m4is_c306 =FALSE;
 wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memb_accesscontrol_quickedit_nonce');
 
}echo '<fieldset class="inline-edit-col-right inline-edit-', $m4is_w93, '">';
 echo '<div class="inline-edit-col column-', $m4is_w93, '">';
 echo '<label class="inline-edit-group">';
 switch ($m4is_w93){
case 'memberships': $m4is_m96240 =$this->m4is_r1546->m4is_j498('memberships');
 if(count($m4is_m96240)> 0){
 echo '<ul>';
 echo '<em>Membership Access</em>';
 foreach ($m4is_m96240 as $m4is_d07693 =>$m4is_w64){
echo '<label for="memb_membership_' . $m4is_d07693 . '"><input type="checkbox" name="memb_membership_levels[' . $m4is_d07693 . ']" value="' . $m4is_d07693 . '" id="memb_membership_' . $m4is_d07693 . '"> ' . stripslashes($m4is_w64['name']). '</label>';
 
}echo '<label for="memb_anonymousonly"><input type="checkbox" name="memb_anonymousonly" value="1" id="memb_anonymousonly">Logged Out Only</label>';
 echo '<label for="memb_google1stclick"><input type="checkbox" name="memb_google1stclick" value="1" id="memb_google1stclick">Google 1st Click Free</label>';
 echo '<label for="memb_facebookcrawler"><input type="checkbox" name="memb_facebookcrawler" value="1" id="memb_facebookcrawler">Facebook Crawler Access</label>';
 echo '</ul>';
 echo '<hr />';
 echo '<label><span class="title" style="width:75px;">Prohibited</span><select name="memb_prohibitedaction" id="memb_prohibitedaction">';
 echo '<option value="default">Default</option>';
 echo '<option value="redirect">Redirect</option>';
 echo '<option value="hide">Hide Completely</option>';
 echo '<option value="excerpt">Show Excerpt Only</option>';
 echo '</select></label>';
 echo '<label><span class="title" style="width:75px;">Redirect URL  </span><input type="text" name="memb_redirecturl" value="" placeholder="" id="memb_redirecturl"></label>';
 
}break;
 
}echo '</label></div></fieldset>';
 
} 
}

