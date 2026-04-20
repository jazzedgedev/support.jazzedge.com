<?php
 class_exists('m4is_q1089')||die();
 final 
class m4is_f356 extends BP_Group_Extension {

function __construct(){
add_filter('memberium/enhanced_admin_scripts', [$this, 'm4is_g3175'], 10, 1);
 $m4is_y66291 =['access' =>'noone', 'name' =>'BuddyPress Groups for Memberium', 'slug' =>'bp-groups-for-memberium', ];
 parent::init($m4is_y66291 );
 
}
function m4is_g3175($m4is_b19){
$m4is_b19[]='toplevel_page_bp-groups';
  $m4is_b19[]='buddyboss_page_bp-groups';
  return $m4is_b19;
 
}
function display($m4is_j4976 =null ){
$m4is_j4976 =bp_get_group_id();
 
}
function settings_screen($m4is_j4976 =null){
if(empty($m4is_j4976)){
return;
 
}$m4is_d87521 =groups_get_groupmeta($m4is_j4976, '_is4wp_autojoin');
 $m4is_d87521 =empty($m4is_d87521 )? []: $m4is_d87521;
 $m4is_d87521['autojoin_admin']=isset($m4is_d87521['autojoin_admin'])? $m4is_d87521['autojoin_admin']: '';
 $m4is_d87521['autojoin_moderator']=isset($m4is_d87521['autojoin_moderator'])? $m4is_d87521['autojoin_moderator']: '';
 $m4is_d87521['autojoin_member']=isset($m4is_d87521['autojoin_member'])? $m4is_d87521['autojoin_member']: '';
 $m4is_d87521['autoban']=isset($m4is_d87521['autoban'])? $m4is_d87521['autoban']: '';
 echo '<style>';
 echo ' .memberium_label { display:inline-block; width:175px; margin-right: 20px; }';
 echo ' .multitaglist .tag-selector { margin-bottom:6px; }';
 echo '</style>';
 echo '<label class="memberium_label">Autojoin as Admin:</label>';
 echo '<input type="text" id="is4wp_autojoin_admin" name="is4wp_autojoin_admin" value="', $m4is_d87521['autojoin_admin'], '" class="multitaglist" style="width:500px;">';
 echo '<br>';
 echo '<label class="memberium_label">Autojoin as Moderator:</label>';
 echo '<input type="text" id="is4wp_autojoin_moderator"  name="is4wp_autojoin_moderator" value="', $m4is_d87521['autojoin_moderator'], '" class="multitaglist" style="width:500px;">';
 echo '<br>';
 echo '<label class="memberium_label">Autojoin as Member:</label>';
 echo '<input type="text" name="is4wp_autojoin_member" value="', ($m4is_d87521['autojoin_member']> '' ? $m4is_d87521['autojoin_member']: '' ), '" class="multitaglist" style="width:500px;">';
 echo '<br>';
 echo '<label class="memberium_label">Auto-Ban:</label>';
 echo '<input type="text" name="is4wp_autoban" value="', ($m4is_d87521['autoban']> '' ? $m4is_d87521['autoban']: '' ), '" class="multitaglist" style="width:500px;">';
 echo '<br>';
 return;
 $m4is_l9321 =m4is_k865::m4is_z2906(true );
 $m4is_l9321 =$m4is_l9321['mc'];
 $m4is_z470 =[];
 foreach ((array)$m4is_l9321 as $m4is_d07693 =>$m4is_p786 ){
$m4is_z470[]=['id' =>$m4is_d07693, 'text' =>$m4is_p786 . ' (' . $m4is_d07693 . ')' ];
 
}$m4is_z470 =json_encode($m4is_z470 );
 unset($m4is_l9321, $m4is_d07693, $m4is_p786 );
 echo '<script>';
 echo '	var taglist = ', $m4is_z470, ';';
 echo '</script>';
 unset($m4is_l9321, $m4is_z470 );
 echo '<script> ';
 echo '	jQuery(document).ready( function() { ';
 echo '		jQuery(".multitaglist").wpalSelect2({ ';
 echo '			placeholder: "Select the tags for this role.", ';
 echo '			tags: taglist ';
 echo '		}); ';
 echo '}); ';
 echo '</script>';
 
}
function settings_screen_save($m4is_j4976 =NULL ){
$m4is_d87521 =[];
 $m4is_d87521['autojoin_admin']=isset($_POST['is4wp_autojoin_admin'])? $_POST['is4wp_autojoin_admin']: '';
 $m4is_d87521['autojoin_moderator']=isset($_POST['is4wp_autojoin_moderator'])? $_POST['is4wp_autojoin_moderator']: '';
 $m4is_d87521['autojoin_member']=isset($_POST['is4wp_autojoin_member'])? $_POST['is4wp_autojoin_member']: '';
 $m4is_d87521['autoban']=isset($_POST['is4wp_autoban'])? $_POST['is4wp_autoban']: '';
 groups_update_groupmeta($m4is_j4976, '_is4wp_autojoin', $m4is_d87521 );
 
}
}final 
class memberium_buddypress_groups_class {
private $m4is_f087 =0;
 public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
if((!class_exists('BP_Groups_Group' ))||(!class_exists('BP_Group_Extension' ))){
return;
 
}if(function_exists('bp_register_group_extension' )){
if(is_admin()){
bp_register_group_extension('m4is_f356' );
 
}
}$this->m4is_d4861();
 
}private 
function m4is_d4861(){
add_action('memberium/session/updated', [$this, 'm4is_g762'], 20, 2 );
 
}private 
function m4is_g3186($m4is_t87 ){
$m4is_p65 =[];
 if(is_array($m4is_t87 )){
foreach($m4is_t87 as $m4is_d10 ){
$m4is_p65[]=$m4is_d10->id;
 
}
}return $m4is_p65;
 
}
function m4is_j639($m4is_f087 ){
$this->m4is_f087 =$m4is_f087;
 
}
function m4is_h239($m4is_d07693 ){
return empty($this->m4is_f087 )? $m4is_d07693 : $this->m4is_f087;
 
}  public 
function m4is_g762($m4is_f087, $m4is_k824 ): void {
if(apply_filters('memberium/buddypress/groups/autojoin', false, $m4is_f087 )){
return;
 
}if(!function_exists('groups_get_groups' )||!class_exists('bp_groups_group' )){
return;
 
}if(!defined('BP_GROUPS_SLUG' )){
return;
 
}if(defined('WPAL_DISABLE_BUDDYPRESS_AUTOJOIN' )&&constant('WPAL_DISABLE_BUDDYPRESS_AUTOJOIN' )){
return;
 
}if(user_can($m4is_f087, 'manage_options' )){
return;
 
}$m4is_y66291 =['show_hidden' =>true, 'per_page' =>0, 'page' =>0, ];
 $m4is_t87 =groups_get_groups($m4is_y66291 );
 $m4is_t87 =isset($m4is_t87['groups'])? $m4is_t87['groups']: [];
 $m4is_l9321 =isset($m4is_k824['memb_user']['tags'])? array_filter(explode(',', $m4is_k824['memb_user']['tags'])): [];
 if(is_array($m4is_t87 )){
$this->m4is_j639($m4is_f087 );
 add_filter('bp_loggedin_user_id', [$this, 'm4is_h239'], PHP_INT_MAX, 1 );
 $m4is_e1265 =BP_Groups_Member::get_group_ids($m4is_f087 )['groups'];
 $m4is_j297 =$this->m4is_g3186(BP_Groups_Member::get_is_banned_of($m4is_f087 )['groups']);
 $m4is_f0263 =$this->m4is_g3186(BP_Groups_Member::get_is_admin_of($m4is_f087 )['groups']);
 $m4is_a1066 =$this->m4is_g3186(BP_Groups_Member::get_is_mod_of($m4is_f087 )['groups']);
 remove_filter('bp_loggedin_user_id', [$this, 'm4is_h239']);
 $this->m4is_j639(0 );
 foreach ($m4is_t87 as $m4is_d10 ){
$m4is_b31024 =new BP_Groups_Member($m4is_f087, $m4is_d10->id );
 $m4is_d87521 =groups_get_groupmeta($m4is_d10->id, '_is4wp_autojoin' );
 $m4is_d87521 =is_array($m4is_d87521 )? $m4is_d87521 : [];
 if(!empty($m4is_d87521 )){
$m4is_d87521['autoban']=isset($m4is_d87521['autoban'])? array_filter(explode(',', $m4is_d87521['autoban'])): [];
 $m4is_d87521['autojoin_admin']=isset($m4is_d87521['autojoin_admin'])? array_filter(explode(',', $m4is_d87521['autojoin_admin'])): [];
 $m4is_d87521['autojoin_moderator']=isset($m4is_d87521['autojoin_moderator'])? array_filter(explode(',', $m4is_d87521['autojoin_moderator'])): [];
 $m4is_d87521['autojoin_member']=isset($m4is_d87521['autojoin_member'])? array_filter(explode(',', $m4is_d87521['autojoin_member'])): [];
 $m4is_t053 =!empty($m4is_d87521['autoban']);
 $m4is_v51746 =!empty($m4is_d87521['autojoin_moderator']);
 $m4is_m32 =!empty($m4is_d87521['autojoin_admin']);
 $m4is_a90416 =$m4is_m32 ||$m4is_v51746 ||(!empty($m4is_d87521['autojoin_member']));
 $m4is_p09468 =in_array($m4is_d10->id, $m4is_j297 );
 $m4is_b31 =in_array($m4is_d10->id, $m4is_a1066 );
 $m4is_e66310 =in_array($m4is_d10->id, $m4is_f0263 );
 $m4is_e43802 =$m4is_e66310 ||$m4is_b31 ||in_array($m4is_d10->id, $m4is_e1265 );
 $m4is_o586 =!empty(array_intersect($m4is_d87521['autoban'], $m4is_l9321 ));
 $m4is_b354 =!empty(array_intersect($m4is_d87521['autojoin_moderator'], $m4is_l9321 ));
 $m4is_v610 =!empty(array_intersect($m4is_d87521['autojoin_admin'], $m4is_l9321 ));
 $m4is_e682 =$m4is_b354 ||$m4is_v610 ||(!empty(array_intersect($m4is_d87521['autojoin_member'], $m4is_l9321 )));
  if($m4is_t053 ){
if($m4is_o586 &&!$m4is_p09468 ){
$m4is_b31024->demote();
 $m4is_b31024->ban();
 $m4is_p09468 =true;
 
}if((!$m4is_o586 )&&$m4is_p09468 ){
$m4is_b31024->unban();
 $m4is_p09468 =false;
 
}
}if(!$m4is_p09468 ){
if($m4is_a90416 ){
if((!$m4is_e43802 )&&$m4is_e682 ){
groups_join_group($m4is_d10->id, $m4is_f087 );
 $m4is_e43802 =true;
 
}elseif($m4is_e43802 &&(!$m4is_e682 )){
$m4is_b31024->remove();
 $m4is_e43802 =false;
 
}
}
}if((!$m4is_p09468)&&$m4is_a90416){
if($m4is_v51746){
if($m4is_b354 &&(!$m4is_v610)){
$m4is_b31024->promote('mod');
 
}elseif($m4is_b31 &&!$m4is_b354 &&!$m4is_v610){
$m4is_b31024->demote('mod');
 
}
}if($m4is_m32){
if(!$m4is_e66310 &&$m4is_v610){
$m4is_b31024->promote('admin');
 
}elseif($m4is_e66310 &&!$m4is_v610){
$m4is_b31024->demote('admin');
 
}
}
}
}
}
}
} 
}

