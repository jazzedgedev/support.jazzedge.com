<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_h6530::m4is_z95();
  final 
class m4is_h6530 {
private m4is_r83 $m4is_r1546;
 private array $m4is_m96240;
 private array $m4is_e863;
  static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_i702();
 $this->m4is_o719();
 
} private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_m96240 =(array)$this->m4is_r1546->m4is_j498('memberships' );
 $this->m4is_e863 =m4is_h65::m4is_h1426();
 
} private 
function m4is_s3572(){
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}if(!empty($_POST['main_action'])){
$this->m4is_m27();
 
}if(!empty($_POST['action'])){
if($_POST['action']=== 'edit' ){
$this->m4is_w2608();
 
}elseif($_POST['action']=== 'add' ){
$this->m4is_v84652();
 
}elseif($_POST['action']=== 'delete' ){
$this->m4is_e17();
 
}
}if(!empty($_POST['create-category'])){
$this->m4is_q6014();
 
}if(!empty($_POST['create-tag'])){
$this->m4is_f348();
 
}if(!empty($_POST['create-tags'])){
$this->m4is_f66523();
 
}if(!empty($_POST['create-membership'])){
$this->m4is_a0896();
 
}$this->m4is_r1546->m4is_v26184();
 $this->m4is_a64368();
 m4is_h65::m4is_n25();
 
} private 
function m4is_o719(){
$this->m4is_s3572();
 m4is_s6729::m4is_c26()->m4is_a4215();
 $m4is_c05328 =isset($_GET['action'])? trim($_GET['action']): '';
 $m4is_d07693 =isset($_GET['id'])? (int) $_GET['id']: 0;
 if($m4is_c05328 == 'edit' &&$m4is_d07693 > 0 ){
require_once $this->m4is_r1546->m4is_x63587('/memberships-edit.php' );
 
}elseif($m4is_c05328 == 'add' ){
require_once $this->m4is_r1546->m4is_x63587('/memberships-add.php' );
 
}else{
require_once $this->m4is_r1546->m4is_x63587('/memberships-list.php' );
 require_once $this->m4is_r1546->m4is_x63587('/memberships-tagbuilder.php' );
 
}$this->m4is_r1546->m4is_s965('view_memberships' );
 
} private 
function m4is_a64368(){
$this->m4is_m96240 =array_filter($this->m4is_m96240, fn($m4is_v586, $m4is_l9671 )=>$m4is_l9671 !== '' &&$m4is_l9671 !== null &&$m4is_v586['main_id']> 0, ARRAY_FILTER_USE_BOTH );
 uasort($this->m4is_m96240, function ($m4is_r45, $m4is_q5980 ){
if($m4is_r45['level']== $m4is_q5980['level']){
if($m4is_r45['name']== $m4is_q5980['name']){
return 0;
 
}return ($m4is_r45['name']< $m4is_q5980['name'])? -1 : 1;
 
}return ($m4is_r45['level']< $m4is_q5980['level'])? -1 : 1;
 
});
 $this->m4is_r1546->m4is_d64918($this->m4is_m96240, 'memberships' );
 
} private 
function m4is_m27(){
$_POST['main_action']=$_POST['main_action']?? [];
 $_POST['level']=$_POST['level']?? [];
 $_POST['login_redirect_priority']=$_POST['login_redirect_priority']?? [];
 if(!empty($_POST['level'])&&is_array($_POST['level'])){
foreach($_POST['level']as $m4is_l9671 =>$m4is_v586 ){
$this->m4is_m96240[$m4is_l9671]['level']=$m4is_v586;
 
}
}if(!empty($_POST['login_redirect_priority'])&&is_array($_POST['login_redirect_priority'])){
foreach($_POST['login_redirect_priority']as $m4is_l9671 =>$m4is_v586 ){
$this->m4is_m96240[$m4is_l9671]['login_redirect_priority']=$m4is_v586;
 
}
}if(!empty($_POST['main_action'])&&is_array($_POST['main_action'])){
$deleted_memberships =[];
 foreach($_POST['main_action']as $m4is_l9671 =>$m4is_v586 ){
if($m4is_v586 == 'Delete' ){
m4is_h65::m4is_z896('Your membership level &ldquo;<strong>' . $this->m4is_m96240[$m4is_l9671]['name']. '</strong>&rdquo; has been deleted.' );
  $m4is_x40 ='Memberium ' . $this->m4is_m96240[$m4is_l9671]['name'];
 $m4is_e05248 =sanitize_key('memberium_' . $this->m4is_m96240[$m4is_l9671]['name']);
 remove_role($m4is_e05248 );
 unset($this->m4is_m96240[$m4is_l9671]);
 $deleted_memberships[]=$m4is_l9671;
 
}
}$this->m4is_p31472($deleted_memberships );
 
}
}private 
function m4is_v84652(){
$m4is_d07693 =isset($_POST['main_id'])? (int) $_POST['main_id']: 0;
 if(empty($_POST['name'])||empty($m4is_d07693 )){
m4is_h65::m4is_z896('Failed to add Membership Level.  Missing Fields' );
 return;
 
}$m4is_p125 =[];
 $m4is_p125['name']=ucwords(trim(stripslashes($_POST['name'])));
 $m4is_p125['main_id']=(int) $_POST['main_id'];
 $m4is_p125['addltag_ids']=empty($_POST['addltag_ids'])? '' : $_POST['addltag_ids'];
 $m4is_p125['payf_id']=(int) $_POST['payf_id'];
 $m4is_p125['cancel_id']=(int) $_POST['cancel_id'];
 $m4is_p125['suspend_id']=(int) $_POST['suspend_id'];
 $m4is_p125['level']=(int) $_POST['level'];
 $m4is_p125['roles']=array_filter((array)$_POST['roles']);
 $m4is_p125['login_page']=(int) $_POST['login_page'];
 $m4is_p125['first_login_page']=(int) $_POST['first_login_page'];
 $m4is_p125['logout_page']=(int) $_POST['logout_page'];
 $m4is_p125['theme']=$_POST['theme'];
 $m4is_p125['payf_homepage']=(int) $_POST['payf_homepage'];
 $m4is_p125['susp_homepage']=(int) $_POST['susp_homepage'];
 $m4is_p125['canc_homepage']=(int) $_POST['canc_homepage'];
 $m4is_p125['dynamic_menus']=isset($_POST['dynamic_menus'])? (int) $_POST['dynamic_menus']: 0;
  $m4is_x40 ='Memberium ' . $m4is_p125['name'];
 $m4is_e05248 =sanitize_key('memberium_' . $m4is_p125['name']);
 $m4is_m7560 =get_role($m4is_e05248 );
 if(!$m4is_m7560 ){
$m4is_m7560 =add_role($m4is_e05248, $m4is_x40 );
 
}$m4is_m7560->add_cap('read');
 $this->m4is_m96240[$m4is_d07693]=$m4is_p125;
 $this->m4is_m96240 =apply_filters('memberium/memberships/save', $this->m4is_m96240, $m4is_d07693, $_POST );
 
}private 
function m4is_w2608(){
$m4is_d07693 =(int) $_GET['id']?? (int) $_GET['id'];
 if(empty($_GET['id'])||!array_key_exists($m4is_d07693, $this->m4is_m96240 )){
return;
 
}$m4is_y06417 =$this->m4is_m96240[$m4is_d07693];
 $m4is_p960 =$this->m4is_m96240[$m4is_d07693];
 $m4is_p960['addltag_ids']=isset($_POST['addltag_ids'])? $_POST['addltag_ids']: $m4is_y06417['addltag_ids'];
 $m4is_p960['canc_homepage']=isset($_POST['canc_homepage'])? (int) $_POST['canc_homepage']: $m4is_y06417['canc_homepage'];
 $m4is_p960['cancel_id']=isset($_POST['cancel_id'])? (int) $_POST['cancel_id']: $m4is_y06417['cancel_id'];
 $m4is_p960['dynamic_menus']=isset($_POST['dynamic_menus'])? (int) $_POST['dynamic_menus']: $m4is_y06417['dynamic_menus'];
 $m4is_p960['first_login_page']=isset($_POST['first_login_page'])? (int) $_POST['first_login_page']: $m4is_y06417['first_login_page'];
 $m4is_p960['level']=isset($_POST['level'])? (int) $_POST['level']: $m4is_y06417['level'];
 $m4is_p960['login_page']=isset($_POST['login_page'])? (int) $_POST['login_page']: $m4is_y06417['login_page'];
 $m4is_p960['login_redirect_priority']=isset($_POST['login_redirect_priority'])? (int) $_POST['login_redirect_priority']: $m4is_y06417['login_redirect_priority'];
 $m4is_p960['logout_page']=isset($_POST['logout_page'])? (int) $_POST['logout_page']: $m4is_y06417['logout_page'];
 $m4is_p960['main_id']=$m4is_d07693;
 $m4is_p960['name']=isset($_POST['name'])? trim(stripslashes($_POST['name'])): $m4is_y06417['name'];
 $m4is_p960['payf_homepage']=isset($_POST['payf_homepage'])? (int) $_POST['payf_homepage']: $m4is_y06417['payf_homepage'];
 $m4is_p960['payf_id']=isset($_POST['payf_id'])? (int) $_POST['payf_id']: $m4is_y06417['payf_id'];
 $m4is_p960['roles']=array_filter(isset($_POST['roles'])? $_POST['roles']: $m4is_y06417['roles']);
$m4is_p960;
 $m4is_p960['susp_homepage']=isset($_POST['susp_homepage'])? (int) $_POST['susp_homepage']: $m4is_y06417['payf_homepage'];
 $m4is_p960['suspend_id']=isset($_POST['suspend_id'])? (int) $_POST['suspend_id']: $m4is_y06417['suspend_id'];
 $m4is_p960['theme']=isset($_POST['theme'])? $_POST['theme']: $m4is_y06417['theme'];
 $this->m4is_m96240[$m4is_d07693]=$m4is_p960;
 $this->m4is_m96240 =apply_filters('memberium/memberships/save', $this->m4is_m96240, $m4is_d07693, $_POST );
  m4is_h65::m4is_z896('Your membership level &ldquo;<strong>' . trim($_POST['name']). '</strong>&rdquo; has been updated.' );
 
}private 
function m4is_f66523(){
$m4is_l73269 =trim($_POST['tag_name']);
 $m4is_q7962 =(int) $_POST['start'];
 $m4is_s91 =(int) $_POST['end'];
 $m4is_w66 =(int) abs(trim($_POST['category_id']));
 $m4is_m60 =false;
 if(false === strpos($m4is_l73269, '%d' )){
$m4is_l73269 .= ' %d';
 
}for ($m4is_b3785 =$m4is_q7962;
 $m4is_b3785 <= $m4is_s91;
 $m4is_b3785++ ){
$m4is_n23617 =sprintf($m4is_l73269, $m4is_b3785 );
 if(!m4is_k865::m4is_q05762($m4is_n23617 )){
m4is_k865::m4is_f348($m4is_n23617, $m4is_w66 );
 $m4is_m60 =true;
 
}
}if($m4is_m60 ){
m4is_k865::m4is_l26();
 m4is_h65::m4is_z896('Your Drip Tags for &ldquo;<strong>' . $m4is_n23617 . '</strong>&rdquo; have been created.' );
 
}else{
m4is_h65::m4is_z896('Your Drip Tags for &ldquo;<strong>' . $m4is_n23617 . '</strong>&rdquo; already exist.' );
 
}
} private 
function m4is_e17(): void {
$m4is_d07693 =isset($_POST['membership_id'])? (int) $_POST['membership_id']: 0;
 if(array_key_exists($m4is_d07693, $this->m4is_m96240 )){
$m4is_k52736 =$this->m4is_m96240[$_POST[$m4is_d07693]]['name'];
 unset($m4is_m96240[$m4is_d07693]);
 m4is_h65::m4is_z896(sprintf('Your membership level &ldquo;<strong>%s</strong>&rdquo; has been deleted.', $m4is_k52736 ));
 
}
}private 
function m4is_q6014(){
$m4is_k52736 =isset($_POST['category_name'])? $_POST['category_name']: '';
 $m4is_d07693 =m4is_z6894::m4is_f37($m4is_k52736 );
 if($m4is_d07693 ){
m4is_h65::m4is_z896('Your Category &ldquo;<strong>' . $m4is_k52736 . '</strong>&rdquo; has been created.' );
 
}else{
m4is_h65::m4is_z896('Your Category &ldquo;<strong>' . $m4is_k52736 . '</strong>&rdquo; already exists.' );
 
}return $m4is_d07693;
 
} private 
function m4is_p31472(array $m4is_d6498 ): bool {
if(empty($m4is_d6498 )){
return false;
 
}global $wpdb;
 $m4is_v2613 ="SELECT `post_id`, `meta_value` FROM `{$wpdb->postmeta
}` WHERE `meta_key` = '_is4wp_membership_levels' AND `meta_value` > '' ";
 $m4is_r02674 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 foreach ($m4is_r02674 as $m4is_m5907 ){
$m4is_v5613 =array_filter(explode(',', $m4is_m5907['meta_value']));
 $m4is_t6842 =implode(',', array_diff($m4is_v5613, $m4is_d6498 ));
 m4is_w831::m4is_f0691($m4is_m5907['post_id'], 'memberships', $m4is_t6842 );
 
}$this->m4is_r1546->m4is_v26184();
 return true;
 
}private 
function m4is_f348(){
 $m4is_n23617 =isset($_POST['tag_name'])? trim($_POST['tag_name']): '';
 $m4is_w66 =isset($_POST['category_id'])? (int) abs(trim($_POST['category_id'])): 0;
 if(empty($m4is_n23617 )){
m4is_h65::m4is_z896('Tag name missing.' );
 return;
 
}$m4is_n684 =m4is_k865::m4is_q05762($m4is_n23617 );
 if(!$m4is_n684 ){
m4is_k865::m4is_f348($m4is_n23617, $m4is_w66 );
 m4is_h65::m4is_z896('Your Tag &ldquo;<strong>' . $m4is_n23617 . '</strong>&rdquo; has been created.' );
 
}else{
m4is_h65::m4is_z896('Your Tag &ldquo;<strong>' . $m4is_n23617 . '</strong>&rdquo; already exists.' );
 
}m4is_k865::m4is_l26();
 
}private 
function m4is_a0896(){
 $m4is_n23617 =trim($_POST['tag_name']);
 $m4is_w66 =(int) abs(trim($_POST['category_id']));
 $m4is_i92416 =!empty($_POST['create_set']);
 $m4is_b6348 =$m4is_i92416 ? ['', 'PAYF', 'CANC', 'SUSP']: ['', 'PAYF'];
 $m4is_m60 =false;
 $m4is_a5187 =[];
 foreach($m4is_b6348 as $m4is_s6681 ){
$m4is_e491 =$m4is_n23617 . $m4is_s6681;
 $m4is_h973 =m4is_k865::m4is_q05762($m4is_e491 );
 if(!$m4is_h973 ){
$m4is_a5187['Tag' . $m4is_s6681]=m4is_k865::m4is_f348($m4is_n23617 . $m4is_s6681, $m4is_w66 );
 $m4is_m60 =true;
 
}
}if($m4is_m60 ){
$m4is_h58 =['name' =>$m4is_n23617, 'main_id' =>$m4is_a5187['Tag'], 'payf_id' =>isset($m4is_a5187['TagPAYF'])? $m4is_a5187['TagPAYF']: 0, 'cancel_id' =>isset($m4is_a5187['TagCANC'])? $m4is_a5187['TagCANC']: 0, 'suspend_id' =>isset($m4is_a5187['TagSUSP'])? $m4is_a5187['TagSUSP']: 0, 'level' =>0, 'roles' =>[], 'login_page' =>0, 'first_login_page' =>0, 'logout_page' =>0, 'theme' =>'', 'login_redirect_priority' =>0, 'addltag_ids' =>'', 'payf_homepage' =>0, 'susp_homepage' =>0, 'canc_homepage' =>0, 'dynamic_menus' =>0, ];
 $this->m4is_m96240[$m4is_a5187['Tag']]=$m4is_h58;
  $m4is_x40 ='Memberium ' . $m4is_n23617;
 $m4is_e05248 =sanitize_key('memberium_' . $m4is_n23617 );
 $m4is_m7560 =get_role($m4is_e05248 );
 if(!$m4is_m7560 ){
$m4is_m7560 =add_role($m4is_e05248, $m4is_x40 );
 
}$m4is_m7560->add_cap('read' );
 m4is_k865::m4is_l26();
 m4is_h65::m4is_z896('Your Membership Tags for &ldquo;<strong>' . $m4is_n23617 . '</strong>&rdquo; and Level have been created.' );
 
}else{
m4is_h65::m4is_z896('Your Membership Tags for &ldquo;<strong>' . $m4is_n23617 . '</strong>&rdquo; already exist.' );
 
}
}
}

