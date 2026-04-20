<?php

/**
 * Copyright (c) 2017-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 m4is_g95873::m4is_h269();
 final 
class m4is_g95873 {
private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_h21895;
 private static $m4is_e66310;
 private static $m4is_z59682;
 private static $m4is_r02639;
 private static $m4is_f4218;
  static 
function m4is_h269(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_h21895 =self::$m4is_r1546->m4is_z56();
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_r02639 =!m4is_s52::m4is_w74();
 self::$m4is_f4218 ='memberium';
 
} static 
function m4is_o6159($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'htmlattr' =>'', 'txtfmt' =>'', ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_o498 =htmlspecialchars(self::$m4is_r9613 );
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} static 
function m4is_w3614($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}static $form_id =1;
 m4is_j586::m4is_x7134();
 $m4is_y642 =['action_id' =>'', 'button_text' =>'Submit', 'button_url' =>'', 'contact_id' =>self::$m4is_h21895, 'css_class' =>'', 'debug' =>'no', 'form_action' =>'', 'fus_ids' =>'', 'goals' =>'', 'redirect_url' =>'', 'tag_ids' =>'', 'tagids' =>'', 'target' =>'_self', 'tokens' =>'', ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['debug']=m4is_f61::m4is_d8195($m4is_l62046['debug'], false);
 $m4is_c05328 =[];
 $m4is_c05328['action_id']=$m4is_l62046['action_id'];
 $m4is_c05328['contact_id']=$m4is_l62046['contact_id'];
 $m4is_c05328['debug']=$m4is_l62046['debug'];
 $m4is_c05328['fus']=$m4is_l62046['fus_ids'];
 $m4is_c05328['goals']=$m4is_l62046['goals'];
 $m4is_c05328['redirect']=$m4is_l62046['redirect_url'];
 $m4is_c05328['tags']=trim($m4is_l62046['tagids']. ',' . $m4is_l62046['tag_ids'], ',');
 $m4is_c05328['tokens']=trim($m4is_l62046['tokens']);
 $m4is_u076 =base64_encode(serialize($m4is_c05328 ));
 $m4is_x29143 ='memb_actionset_button-' . $form_id;
 $m4is_c8625 ='memb_actionset_button_' . $form_id;
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u076 );
 $form_id++;
 if(current_user_can('manage_options' )){
$m4is_i46 ="<input type='submit' class='{$m4is_l62046['css_class']
}' id='{$m4is_c8625
}' value='Actionset Button disabled for Admin'>";
 return $m4is_i46;
 
}if($m4is_c05328['contact_id']< 1 ){
return;
 
}$m4is_i46 ='';
 $m4is_i46 .= "<form name='{$m4is_x29143
}' id='{$m4is_x29143
}' method='post' target='{$m4is_l62046['target']
}' action='{$m4is_l62046['form_action']
}'>";
 $m4is_i46 .= "<input type='hidden' name='memb_form_type' value='memb_actionset_button'>";
 $m4is_i46 .= "<input type='hidden' name='form_id' value='{$form_id
}'>";
 $m4is_i46 .= "<input type='hidden' name='action' value='{$m4is_u076
}'>";
 $m4is_i46 .= "<input type='hidden' name='signature' value='{$m4is_o31859
}'>";
 $m4is_i46 .= wp_nonce_field('memb_actionset_' . $form_id, '_wpnonce', true, false);
 if($m4is_l62046['button_url']== ''){
$m4is_i46 .= "<input type='submit' class='{$m4is_l62046['css_class']
}' id='{$m4is_c8625
}' value='{$m4is_l62046['button_text']
}'>";
 
}else{
$m4is_i46 .= "<input type='image' src='{$m4is_l62046['button_url']
}' class='{$m4is_l62046['css_class']
}' id='{$m4is_c8625
}' >";
 
}$m4is_i46 .= "</form>";
 return $m4is_i46;
 
}static 
function m4is_p453($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}if(is_feed()){
return;
 
}$m4is_y642 =['contact_id' =>self::$m4is_h21895, 'debug' =>'no', 'force' =>'no', 'tag_id' =>'', 'tag_ids' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(', ', array_keys($m4is_y642));
 
}if(empty(self::$m4is_h21895 )){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['debug']=m4is_f61::m4is_d8195($m4is_l62046['debug'], false);
 $m4is_l62046['force']=m4is_f61::m4is_d8195($m4is_l62046['force'], false);
 $m4is_l62046['tag_id']=trim($m4is_l62046['tag_id']. ',' . $m4is_l62046['tag_ids'], ',');
 m4is_r83::m4is_c26()->m4is_k98($m4is_l62046['tag_id'], $m4is_l62046['contact_id'], (boolean) $m4is_l62046['force'], $m4is_l62046['debug']);
 
}static 
function m4is_h06274($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['contact_id' =>self::$m4is_h21895, 'date_format' =>'F j, Y g:i a', 'debug' =>'no', 'force' =>'no', 'limit' =>1, 'tag_id' =>'', 'tag_ids' =>'', 'upcomingonly' =>'yes', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(self::$m4is_h21895 < 1){
return;
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['upcomingonly']=m4is_f61::m4is_d8195($m4is_l62046['upcomingonly'], true);
 $m4is_l62046['limit']=(int) $m4is_l62046['limit'];
 $m4is_l62046['limit']=$m4is_l62046['limit']< 0 ? 1 : $m4is_l62046['limit'];
 $m4is_l62046['limit']=$m4is_l62046['limit']> 1000 ? 1000 : $m4is_l62046['limit'];
  $m4is_v76912 =['IsAppointment' =>1, 'ContactId' =>self::$m4is_h21895, ];
  if($m4is_l62046['upcomingonly']){
$m4is_v76912['ActionDate']='~>~' . date('Y-m-d');
 
}$m4is_e80 ='ContactAction';
 $m4is_a89 =m4is_c69807::m4is_f5248($m4is_e80);
 $m4is_f927 =m4is_c69807::m4is_o986($m4is_e80, $m4is_l62046['limit'], 0, $m4is_v76912, $m4is_a89);
 $m4is_r658 =0;
 $m4is_o498 ='';
 $m4is_f086 =0;
 $m4is_w82 =['ActionDate', 'CompletionDate', 'CreationDate', 'EndDate', 'LastUpdated', 'PopupDate', ];
 if(is_array($m4is_f927)){
if(!empty($m4is_f927)){
$m4is_r658 =count($m4is_f927);
 foreach($m4is_f927 as $m4is_o43762){
$m4is_f086++;
 $m4is_p6925 =$m4is_t09761;
 $m4is_g73641 =($m4is_f086 % 2)? $m4is_g73641 ='odd': $m4is_g73641 ='even';
 $m4is_p6925 =str_ireplace('%%cycler%%', $m4is_g73641, $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%line%%', $m4is_f086, $m4is_p6925);
 foreach($m4is_w82 as $m4is_x036){
$m4is_c8657 ='/(%%'.strtolower($m4is_x036). '(\|?)(.*)%%)/U';
 if(stripos($m4is_p6925, '%%' . $m4is_x036)!== false){
$m4is_p6925 =preg_replace_callback($m4is_c8657, function($m4is_a157)use ($m4is_o43762, $m4is_x036, $m4is_l62046){
$m4is_a157[3]=substr($m4is_a157[3], 1);
 if($m4is_a157[3]> ''){
$m4is_f16985 =$m4is_a157[3];
 
}else{
$m4is_f16985 =$m4is_l62046['date_format'];
 
}return date($m4is_f16985, strtotime($m4is_o43762[$m4is_x036]));
 
}, $m4is_p6925);
 
}
}foreach($m4is_a89 as $m4is_q523){
if(!in_array($m4is_q523, $m4is_w82)){
if(!isset($m4is_o43762[$m4is_q523])){
$m4is_o43762[$m4is_q523 ]='';
 
}$m4is_p6925 =str_ireplace('%%' . $m4is_q523 . '%%', $m4is_o43762[$m4is_q523], $m4is_p6925);
 
}
}$m4is_o498 .= $m4is_p6925;
 
}
}
}$m4is_o498 =str_ireplace('%%appt.count%%', $m4is_r658, $m4is_p6925);
 return do_shortcode($m4is_o498);
 
}static 
function m4is_v16967($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['contact_id' =>self::$m4is_h21895, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_c507 =self::$m4is_z59682->listLinkedContacts($m4is_l62046['contact_id']);
 return '<pre>' . print_r($m4is_c507, true). '</pre>';
 
}static 
function m4is_v357($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['fus_id' =>0, 'fus_ids' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['fus_ids']=trim($m4is_l62046['fus_id']. ',' . $m4is_l62046['fus_ids'], ',');
 m4is_r83::m4is_c26()->m4is_f7350($m4is_l62046['fus_ids'], m4is_r83::m4is_c26()->m4is_z56());
 
}static 
function m4is_g679($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['fus_id' =>0, 'fus_ids' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!self::$m4is_h21895 ){
return;
 
}if(is_feed()){
return;
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['fus_ids']=trim($m4is_l62046['fus_id']. ',' . $m4is_l62046['fus_ids'], ', ');
 m4is_r83::m4is_c26()->m4is_w17($m4is_l62046['fus_ids'], self::$m4is_h21895 );
 
}static 
function m4is_h96($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}if(is_feed()){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['tag_id' =>0, 'contact_id' =>self::$m4is_h21895, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 if($m4is_l62046['tag_id']> ''){
m4is_r83::m4is_c26()->m4is_k98($m4is_l62046['tag_id'], $m4is_l62046['contact_id']);
 
}
}static 
function m4is_l409($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 if(is_feed()){
return;
 
}$m4is_y642 =['action_id' =>0, 'force_sync' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 m4is_r83::m4is_c26()->m4is_u71903($m4is_l62046['action_id'], self::$m4is_h21895 );
 
}static 
function m4is_s0695($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}if(is_feed()){
return;
 
}$m4is_y642 =['tag_id' =>0, 'contact_id' =>self::$m4is_h21895, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!self::$m4is_h21895 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 m4is_r83::m4is_c26()->m4is_k98($m4is_l62046['tag_id'], $m4is_l62046['contact_id']);
 
} public static 
function m4is_u32($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
static $m4is_a0413 =0;
 if(self::$m4is_r02639 ){
return '';
 
}if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, [])){
return $m4is_b32676;
 
};
 if(!self::$m4is_h21895 ){
return '';
 
}$m4is_f465 =isset($_GET )&&count($_GET );
 if($m4is_a0413 ){
error_log(sprintf('Memberium: [error]: [%s], API throttled.  Excess usage of [%s] will cause API stability issues.', $m4is_v3458, $m4is_v3458 ));
 return '';
 
}if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
 self::$m4is_r1546->m4is_x4831(self::$m4is_h21895 );
 $m4is_a0413++;
 sleep($m4is_a0413 );
 $m4is_l17096 =wp_get_current_user();
 m4is_q82::m4is_u687($m4is_l17096->ID );
 return '';
 
}static 
function m4is_t2863($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['contact_id1' =>self::$m4is_h21895, 'contact_id2' =>0, 'link_type' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_u6591 =self::$m4is_z59682->unlinkContacts($m4is_l62046['contact_id1'], $m4is_l62046['contact_id2'], $m4is_l62046['link_type']);
 
}static 
function m4is_p6621($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 =''){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['capture' =>'', 'exact' =>false, 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_m60 =false;
 $m4is_b87316 =0;
 $m4is_f416 =0;
 $m4is_r32698 =m4is_q82::m4is_d6758(self::$m4is_r1546->m4is_x66(), 'memb_user', 'email', '' );
 switch ($m4is_v3458){
case 'memb_is_no_optin': $m4is_b87316 =0;
 break;
 case 'memb_is_1x_optin': $m4is_b87316 =1;
 break;
 case 'memb_is_2x_optin': $m4is_b87316 =2;
 break;
 
}$m4is_f416 =m4is_f58::m4is_c26()->m4is_l86($m4is_r32698);
 $m4is_e66310 =self::$m4is_r1546->m4is_v461();
 if($m4is_l62046['exact']||$m4is_b87316 == 0){
$m4is_m60 =($m4is_e66310 ||$m4is_f416 == $m4is_b87316);
 
}else{
$m4is_m60 =($m4is_e66310 ||$m4is_f416 >= $m4is_b87316);
 
}$m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_m60);
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
}static 
function m4is_i52671($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}if(is_feed()){
return;
 
}$m4is_y642 =['call_name' =>'', 'debug' =>false, 'integration' =>self::$m4is_r9613, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!self::$m4is_h21895 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['call_name']=trim($m4is_l62046['call_name']);
 $m4is_l62046['integration']=trim($m4is_l62046['integration']);
 if(empty($m4is_l62046['call_name'])||empty($m4is_l62046['integration'])){
return;
 
}$m4is_l62046['debug']=m4is_f61::m4is_d8195($m4is_l62046['debug'], false);
 if($m4is_l62046['debug']){
echo "Achieving Goal {$m4is_l62046['integration']
}:{$m4is_l62046['call_name']
}<br>";
 
}self::$m4is_r1546->m4is_t64038($m4is_l62046['call_name'], self::$m4is_h21895, $m4is_l62046['integration']);
 
}static 
function m4is_x2976($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'delimiter' =>',', 'htmlattr' =>'', 'separator' =>',', 'tag_ids' =>'', 'tagids' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_o498 ='';
 $m4is_l62046['tagids']=trim($m4is_l62046['tagids']. ',' . $m4is_l62046['tag_ids'], ', ');
 if($m4is_l62046['tagids']> ''){
$m4is_i29 =m4is_k865::m4is_z2906();
 $m4is_l9321 =explode($m4is_l62046['delimiter'], $m4is_l62046['tagids']);
 foreach ($m4is_l9321 as $m4is_p786){
if(isset($m4is_i29['mc'][$m4is_p786])){
$m4is_o498 .= htmlspecialchars($m4is_i29['mc'][$m4is_p786]). $m4is_l62046['separator'];
 
}
}$m4is_o498 =substr($m4is_o498, 0, strlen($m4is_o498)- strlen($m4is_l62046['separator']));
 
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_u397($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
global $wpdb;
 if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'date_format' =>'l, F dS, Y, g:sA', 'default' =>'', 'htmlattr' =>'', 'mode' =>'newest',  'tag_ids' =>'', 'tagids' =>'', 'txtfmt' =>'', 'tz' =>'America/New_York', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['tagids']=trim($m4is_l62046['tagids']. ',' . $m4is_l62046['tag_ids'], ', ' );
 $m4is_l62046['mode']=strtolower(trim($m4is_l62046['mode']));
 $m4is_o498 =$m4is_l62046['default'];
 $m4is_l9321 =m4is_k865::m4is_c743(explode(',', $m4is_l62046['tagids']));
 $m4is_s156 =implode(',', $m4is_l9321 );
 $m4is_f087 =self::$m4is_r1546->m4is_x66();
 if(empty($m4is_l9321 )){
return '';
 
}if(!self::$m4is_h21895 ){
return '';
 
}if(!self::$m4is_r1546->m4is_j498('settings', 'sync_tag_details' )){
return '';
 
}$m4is_y174 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tag_detail_count', 0 );
 if($m4is_y174 == 0 ){
m4is_k865::m4is_h894(self::$m4is_h21895 );
 m4is_q82::m4is_u687($m4is_f087 );
 $m4is_y174 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tag_detail_count', 0 );
 
}if(!$m4is_y174 ){
return '';
 
}$m4is_z68613 =$m4is_l62046['mode']== 'newest' ? 'DESC' : 'ASC';
 $m4is_d38091 =date_default_timezone_get();
 $m4is_y66291 =[m4is_k865::m4is_o39864(), self::$m4is_h21895, self::$m4is_r9613, ];
 $m4is_v2613 ="SELECT `created` FROM %i WHERE `contactid` = %d AND `tagid` IN ( {$m4is_s156
} ) AND `appname` = %s ORDER BY `created` {$m4is_z68613
}  LIMIT 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_y66291 );
 $m4is_n2059 =$wpdb->get_var($m4is_v2613 );
 date_default_timezone_set($m4is_l62046['tz']);
 $m4is_o498 =date($m4is_l62046['date_format'], strtotime($m4is_n2059));
 date_default_timezone_set($m4is_d38091 );
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_z16764($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['capture' =>'', 'format' =>'', 'htmlattr' =>'', 'tags' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['tags']=explode(',', $m4is_l62046['tags']);
 $m4is_t87 =m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', 'groups', '' );
 $m4is_c079 =empty($m4is_t87)? []: explode(',', $m4is_t87);
 $m4is_a86549 =count($m4is_l62046['tags']);
 $m4is_v6715 =0;
 foreach($m4is_c079 as $m4is_q01842){
if(in_array($m4is_q01842, $m4is_l62046['tags'])){
$m4is_v6715++;
 
}
}$m4is_o498 ='';
 if($m4is_l62046['format']== '%'){
$m4is_o498 =(($m4is_v6715 / $m4is_a86549)* 100);
 
}else{
$m4is_o498 =$m4is_v6715;
 
}return m4is_f61::m4is_u150(true, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr']);
 
}static 
function m4is_s7845($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['cachetime' =>900, 'capture' =>'', 'tag_id' =>0, 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['tag_id']=(int) m4is_k865::m4is_y6189($m4is_l62046['tag_id']);
 $m4is_l62046['cachetime']=$m4is_l62046['cachetime']< 450 ? $m4is_l62046['cachetime']=450 : (int) $m4is_l62046['cachetime'];
 if($m4is_l62046['tag_id']< 1){
return;
 
}$m4is_z984 ='memberium::tag_count::' . $m4is_l62046['tag_id'];
 $m4is_h973 =get_transient($m4is_z984);
 if($m4is_h973 === false){
if(method_exists(self::$m4is_z59682, 'dscount')){
$m4is_e80 ='ContactGroupAssign';
 $m4is_h973 =self::$m4is_z59682->dscount($m4is_e80, ['GroupId' =>$m4is_l62046['tag_id']]);
 set_transient($m4is_z984, $m4is_h973, $m4is_l62046['cachetime']);
 
}else{
$m4is_h973 ='Count Unavailable';
 
}
}return m4is_f61::m4is_u150(true, $m4is_h973, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], '');
 
}static 
function m4is_k763($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}if(is_feed()){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['contact_id1' =>self::$m4is_h21895, 'contact_id2' =>0, 'link_type' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $result =self::$m4is_z59682->linkContacts($m4is_l62046['contact_id1'], $m4is_l62046['contact_id2'], $m4is_l62046['link_type']);
 
}static 
function m4is_n1986($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'htmlattr' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_o498 =self::$m4is_r1546->m4is_z56();
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], '', $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_q9354($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}if(is_feed()){
return;
 
}$m4is_y642 =['fus_id' =>0, 'fus_ids' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}if(self::$m4is_h21895 < 1 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_s90816 =trim($m4is_l62046['fus_id']. ',' . $m4is_l62046['fus_ids'], ',' );
 self::$m4is_r1546->m4is_k59073($m4is_s90816, self::$m4is_h21895 );
 
}static 
function m4is_x867($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}if(is_feed()){
return;
 
}$m4is_y642 =['action_ids' =>'', 'debug' =>'no', 'fus_ids' =>'', 'goals' =>'', 'redirect' =>'', 'tag_ids' =>'', 'tagids' =>'', 'tokens' =>'', 'url' =>get_site_url(), ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}m4is_j586::m4is_x7134();
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['debug']=m4is_f61::m4is_d8195($m4is_l62046['debug'], false );
 if(self::$m4is_r1546->m4is_v461()){
return $m4is_l62046['url'];
 
}if(!self::$m4is_h21895 ){
return $m4is_l62046['url'];
 
}$m4is_c05328 =['actionset' =>$m4is_l62046['action_ids'], 'contact_id' =>self::$m4is_h21895, 'debug' =>$m4is_l62046['debug'], 'fus' =>$m4is_l62046['fus_ids'], 'goals' =>$m4is_l62046['goals'], 'redirect' =>$m4is_l62046['redirect'], 'tags' =>trim("{$m4is_l62046['tagids']
},{$m4is_l62046['tag_ids']
}", ',' ), 'tokens' =>trim($m4is_l62046['tokens']), ];
 $m4is_u076 =base64_encode(serialize($m4is_c05328 ));
 $m4is_n6062 =sprintf('?memb_actionlink=%s', $m4is_u076 );
 $m4is_k52736 ='verification';
 $m4is_c05328 =$m4is_u076;
 $m4is_x62017 =wp_nonce_url($m4is_n6062, $m4is_c05328, $m4is_k52736 );
 return $m4is_x62017;
 
}static 
function m4is_b450($m4is_l62046, $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['capture' =>'', 'txtfmt' =>'', ];
 if($m4is_b32676 =m4is_f61::m4is_b48($m4is_l62046, $m4is_y642 )){
return $m4is_b32676;
 
};
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_u6591 =m4is_c69807::m4is_j4681();
 $m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_u6591 );
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], '', '', '' );
 
}
}

