<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 m4is_n63956::m4is_h269();
 final 
class m4is_n63956 {
static private $m4is_r1546;
 static private $m4is_f4218;
 static private $m4is_o27056;
  private 
function __construct(){

} static 
function m4is_h269(): void {
m4is_j586::m4is_x7134();
 self::$m4is_f4218 ='memberium';
 self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_o27056 =m4is_u7102::m4is_c26();
 
} static 
function m4is_g05834(){
global $memb_messages;
  $m4is_l61385 =(int) $_POST['contact_id'];
   if(md5(wp_salt('nonce' ). $m4is_l61385 )<> $_POST['signature']){
return;
 
} $m4is_a89 =[self::$m4is_o27056->m4is_s54627()=>'', ];
  $m4is_v20641 =(int) self::$m4is_o27056->m4is_h1820();
 $m4is_y256 =self::$m4is_o27056->m4is_i669();
 $m4is_h850 =get_current_user_id();
 $m4is_x09186 =m4is_p40::m4is_i6158($m4is_l61385 );
 m4is_p40::m4is_x6560($m4is_l61385, $m4is_a89 );
 m4is_c69807::m4is_z3902($m4is_l61385, $m4is_y256 );
 m4is_j4156::m4is_w4805($m4is_l61385, $m4is_v20641 );
 self::$m4is_r1546->m4is_x4831($m4is_l61385);
 self::$m4is_o27056->m4is_y7324($m4is_x09186, $m4is_h850 );
  $memb_messages['flash']['create_child_result']='<p style="color:green;font-weight:bold;">' . _x('Child Account removed.', 'umbrella_list_children', self::$m4is_f4218 ). '</p>';
 
} static 
function m4is_m0786(){
global $memb_messages;
  if(!wp_verify_nonce($_POST['_wpnonce'], 'memb_childenroll' )){
wp_die('Security Check Failed - Nonce Validation Error' );
 exit;
 
} if(!self::$m4is_r1546->m4is_f1072($_POST['digital_signature'], $_POST['params'])){
wp_die('Security Check Failed - Signature Validation Error');
 exit;
 
} $m4is_v61358 =self::$m4is_o27056->m4is_s54627();
 $m4is_k937 =self::$m4is_o27056->m4is_s54627();
 $m4is_f28 =(int) self::$m4is_o27056->m4is_d326('child_added_actionset');
 $m4is_q251 =(int) self::$m4is_o27056->m4is_d326('parent_added_actionset');
 $m4is_r96106 =(string) self::$m4is_o27056->m4is_d326('child_added_goal');
 $m4is_m924 =(string) self::$m4is_o27056->m4is_d326('parent_added_goal');
 $m4is_u897 =unserialize(base64_decode($_POST['params']));
 $m4is_u52964 =strtolower($m4is_v61358 );
 $m4is_d27 =(int) $m4is_u897['contact_id'];
 $m4is_l61385 =0;
 $m4is_a510 =array_filter(explode(',', $m4is_u897['allowed_actions']));
 $m4is_s2308 =isset($_POST['actions'])? (int) $_POST['actions']: 0;
 $m4is_n5746 =isset($_POST['goal'])? trim($_POST['goal']): '';
 $m4is_f4930 =strtolower(trim($_POST['Email']));
 $m4is_h850 =self::$m4is_r1546->m4is_x66();
  if(!in_array($m4is_s2308, $m4is_a510 )){
$m4is_s2308 =0;
 
} if(empty($m4is_f4930 )){
$memb_messages['flash']['create_child_result']='<p style="color:red;font-weight:bold;">' . _x('Email is Required', 'umbrella_enroll_child', self::$m4is_f4218 ). '</p>';
 return;
 
} if(empty($_POST['FirstName'])){
$memb_messages['flash']['create_child_result']='<p style="color:red;font-weight:bold;">' . _x('First Name is Required', 'umbrella_enroll_child', self::$m4is_f4218 ). '</p>';
 return;
 
} $m4is_l0649 =get_user_by('email', $m4is_f4930 );
  if($m4is_l0649 &&user_can($m4is_l0649, 'edit_others_posts' )){
$memb_messages['flash']['create_child_result']='<p style="color:red;font-weight:bold;">' . _x('You cannot add site staff members.', 'umbrella_enroll_child', self::$m4is_f4218 ). '</p>';
 return;
 
} $m4is_x675 =strtolower(trim(m4is_q82::m4is_d6758(self::$m4is_r1546->m4is_x66(), 'memb_user', 'email', '' )));
 if($m4is_f4930 == $m4is_x675 ){
 $memb_messages['flash']['create_child_result']='<p style="color:red;font-weight:bold;">' . _x("You can't add yourself.", 'umbrella_enroll_child', self::$m4is_f4218 ). '</p>';
 return;
 
} $m4is_h3647 =['Id',  $m4is_v61358,  $m4is_k937  ];
   $m4is_r4085 =m4is_p40::m4is_o104($m4is_f4930, $m4is_h3647 );
  foreach($m4is_r4085 as $m4is_d07693 =>$child_contact ){
 if(!empty($child_contact[$m4is_k937])||!empty($child_contact[$m4is_v61358])){
 $memb_messages['flash']['create_child_result']='<p style="color:red;font-weight:bold;">' . _x("You can't add this account.", 'umbrella_enroll_child', self::$m4is_f4218 ). '</p>';
 return;
 
}$m4is_b66932 =m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', $m4is_u52964, '' );
 if(isset($child_contact[$m4is_k937])&&$child_contact[$m4is_k937]== $m4is_b66932 ){
$m4is_l61385 =$child_contact['Id'];
 
}if(empty($child_contact[$m4is_k937])){
$m4is_l61385 =$child_contact['Id'];
 
}
} $m4is_t27891 =m4is_c69807::m4is_f5248('Contact', false );
 $m4is_a89 =[];
 if($m4is_l61385 == 0 ||$m4is_u897['overwrite']== true ){
$m4is_a89 =self::m4is_y20693($_POST );
 
}$m4is_b66932 =m4is_q82::m4is_k660($m4is_h850, 'contact', $m4is_u52964, '' );
 $m4is_a89[$m4is_k937]=self::$m4is_o27056->m4is_q31028($m4is_b66932, 'child' );
  if($m4is_l61385 == 0 ){
$m4is_l61385 =m4is_p40::m4is_k82670($m4is_a89 );
 if($m4is_l61385 > 0 ){
$m4is_x09186 =m4is_p40::m4is_i6158($m4is_l61385 );
 m4is_p40::m4is_x6560($m4is_l61385, $m4is_a89 );
 self::$m4is_o27056->m4is_b69781($m4is_x09186, $m4is_h850 );
 m4is_p40::m4is_y935($m4is_f4930, 'Added by Memberium Umbrella Account System' );
 $memb_messages['flash']['create_child_result']='<p style="color:green;font-weight:bold;">' . _x('Child Account added.', 'umbrella_enroll_child', self::$m4is_f4218 ). '</p>';
 self::m4is_g4687($m4is_d27, $m4is_l61385, $m4is_s2308, $m4is_n5746 );
 
}else{
$memb_messages['flash']['create_child_result']='<p style="color:red;font-weight:bold;">' . _x('Error creating child account.', 'umbrella_enroll_child', self::$m4is_f4218 ). '</p>';
 
}
}else{
  if(empty($child_contact[$m4is_k937])){
$m4is_x09186 =m4is_p40::m4is_i6158($m4is_l61385 );
 $m4is_b66932 =m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', $m4is_u52964, '' );
 $m4is_a89[$m4is_k937]=self::$m4is_o27056->m4is_q31028($m4is_b66932, 'child' );
 self::$m4is_o27056->m4is_b69781($m4is_x09186, $m4is_h850 );
 m4is_p40::m4is_x6560($m4is_l61385, $m4is_a89 );
  self::m4is_g4687($m4is_d27, $m4is_l61385, $m4is_s2308, $m4is_n5746 );
 $memb_messages['flash']['create_child_result']='<p style="color:green;font-weight:bold;">' . _x('Child Account assigned.', 'umbrella_enroll_child', self::$m4is_f4218 ). '</p>';
 
}else{
 $memb_messages['flash']['create_child_result']='<p style="color:red;font-weight:bold;">' . _x('That account already assigned to a user.', 'umbrella_enroll_child', self::$m4is_f4218 ). '</p>';
 
}
} if($m4is_l61385 ){
 sleep(1 );
  self::$m4is_r1546->m4is_x4831($m4is_l61385 );
 
}do_action('memberium_umbrella_add_child', $m4is_l61385, $m4is_d27 );
 do_action('memberium/umbrella/add_child', $m4is_l61385, $m4is_d27 );
 return;
 
} private static 
function m4is_y20693(array $m4is_w0738 ): array {
$m4is_t27891 =m4is_c69807::m4is_f5248('Contact', false );
 $m4is_a89 =[];
 foreach ($m4is_w0738 as $m4is_r637 =>$m4is_v586 ){
if(in_array($m4is_r637, $m4is_t27891 )){
$m4is_a89[$m4is_r637]=stripslashes($m4is_v586 );
 
}
}return $m4is_a89;
 
} private static 
function m4is_g4687(int $m4is_d27, int $m4is_l61385, $m4is_s2308, $m4is_n5746 ): void {
$m4is_r96106 =(string) self::$m4is_o27056->m4is_d326('child_added_goal' );
 $m4is_m924 =(string) self::$m4is_o27056->m4is_d326('parent_added_goal' );
 $m4is_f28 =(int) self::$m4is_o27056->m4is_d326('child_added_actionset' );
 $m4is_q251 =(int) self::$m4is_o27056->m4is_d326('parent_added_actionset' );
 m4is_c69807::m4is_z3902($m4is_l61385, $m4is_r96106 );
 m4is_c69807::m4is_z3902($m4is_l61385, $m4is_n5746 );
 m4is_c69807::m4is_z3902($m4is_d27, $m4is_m924 );
 self::$m4is_r1546->m4is_u71903($m4is_f28, $m4is_l61385 );
 self::$m4is_r1546->m4is_u71903($m4is_q251, $m4is_d27 );
 self::$m4is_r1546->m4is_u71903($m4is_s2308, $m4is_l61385 );
 
} public static 
function m4is_v608(): void {
 m4is_j586::m4is_x7134();
  if(!wp_verify_nonce($_POST['umbrella_transfer_points_wpnonce'], $_POST['signature'])){
 wp_die('Security Check Failed - Nonce Validation Error' );
 exit;
 
} $m4is_q847 =false;
  $m4is_u897 =unserialize(base64_decode($_POST['signature']));
  $m4is_f95063 =(int) $_POST['points'];
  $m4is_z36802 =sanitize_email($_POST['recipient']);
  $m4is_q523 =$m4is_u897['field'];
  $m4is_n548 =new stdclass;
  $m4is_n548->max_points =$m4is_u897['max_points'];
  $m4is_n548->points =$m4is_f95063;
  $parent_contact_id =$m4is_u897['pcid'];
  $child_contact_id =self::$m4is_r1546->m4is_o70($m4is_z36802);
  if($m4is_f95063 == 0){
$m4is_q847 =true;
 $m4is_n548->error_type ='no_points_transferred';
 
} if($m4is_f95063 > $m4is_u897['max_points']||$m4is_f95063 < 1){
$m4is_q847 =true;
 $m4is_n548->error_type ='invalid_points';
 
} if(!$parent_contact_id ||!$child_contact_id){
$m4is_q847 =true;
 $m4is_n548->error_type ='invalid_users';
 
} if(!$m4is_q847){
 $m4is_v02578 =self::$m4is_r1546->m4is_v921($child_contact_id, $m4is_q523);
  $m4is_i90174 =$m4is_n548->max_points - $m4is_f95063;
  $m4is_v48271 =$m4is_v02578 + $m4is_f95063;
  self::$m4is_r1546->m4is_s56($m4is_q523, $m4is_i90174, $parent_contact_id );
  self::$m4is_r1546->m4is_s56($m4is_q523, $m4is_v48271, $child_contact_id );
  $m4is_n548->error_type =false;
  $m4is_n548->points =$m4is_f95063;
 
} $_SESSION['flash']['umbrella_transfer_points']=m4is_f61::m4is_l0659('umbrella_transfer_points_result', null, null, null, $m4is_n548);
 
} public static 
function m4is_g2684(): void {
 m4is_j586::m4is_x7134();
  $m4is_r403 =[];
 $m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_q95421 =self::$m4is_o27056->m4is_z45812();
 $m4is_u897 =isset($_POST['params'])? unserialize(base64_decode($_POST['params'])):[];
 $m4is_q95421 =apply_filters('memberium/umbrella/download_csv', $m4is_q95421);
 $m4is_q95421 =apply_filters("memberium/umbrella/download_csv/{$m4is_u897['filter_name']
}", $m4is_q95421);
  foreach($m4is_q95421 as $k =>$v){
 
}
} public static 
function m4is_j94(): void {
 $m4is_z30692 =(int) $_POST['team_id']?? 0;
 $m4is_b6438 =array_filter(array_map('trim', $_POST['child_uids']?? []));
 $m4is_j31 =m4is_l870::m4is_k2074($m4is_z30692 );
 $m4is_z87032 =array_diff($m4is_b6438, $m4is_j31 );
 $m4is_v58463 =array_diff($m4is_j31, $m4is_b6438 );
 foreach($m4is_v58463 as $m4is_x09186 ){
m4is_l870::m4is_c4061($m4is_x09186, $m4is_z30692 );
 
}foreach($m4is_z87032 as $m4is_x09186 ){
m4is_l870::m4is_o26496($m4is_x09186, $m4is_z30692 );
 
}
}
}

