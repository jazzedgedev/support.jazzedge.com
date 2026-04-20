<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_n2916::m4is_z95();
 
class m4is_n2916 {
private $m4is_m7513 =[];
 private $m4is_y368 =[];
 static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_h269();
 $this->m4is_s3572();
 $this->m4is_d628();
 $this->m4is_b61308();
 
}private 
function m4is_h269(){
$this->m4is_y368 =m4is_c69807::m4is_f5248('Contact', true );
 $this->m4is_m7513 =$this->m4is_m41(get_option('memberium_xprofile_map', []));
 
} private 
function m4is_m41($m4is_j3627 ){
 $m4is_j3627 =array_filter($m4is_j3627 );
  foreach($m4is_j3627 as $m4is_v70218 =>$m4is_j0641 ){
if(!array_key_exists($m4is_v70218, $m4is_j3627 )){
unset($m4is_j3627[$m4is_v70218 ]);
 
}
} foreach($m4is_j3627 as $m4is_v70218 =>$m4is_j0641 ){
if(!in_array($m4is_j0641, $m4is_j3627 )){
unset($m4is_j3627[$m4is_v70218 ]);
 
}
}foreach($m4is_j3627 as $m4is_v70218 =>$m4is_j0641 ){
if(empty($m4is_v70218 )||empty ($m4is_j0641 )){
unset($m4is_j3627[$m4is_v70218]);
 
}
}return $m4is_j3627;
 
}private 
function m4is_s3572(){
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}$m4is_f2349 =isset($_POST['xprofile_map_delete'])&&is_array($_POST['xprofile_map_delete'])? $_POST['xprofile_map_delete']: [];
 $m4is_a8053 =isset($_POST['new_keap_map'])? $_POST['new_keap_map']: '';
 $m4is_g92 =(int) isset($_POST['new_xprofile_map'])? $_POST['new_xprofile_map']: '';
 foreach ($m4is_f2349 as $m4is_v43967 =>$m4is_j0641 ){
unset($this->m4is_m7513[$m4is_v43967]);
 
}if($m4is_a8053 &&$m4is_g92 ){
$this->m4is_m7513[$m4is_a8053]=$m4is_g92;
 
}update_option('memberium_xprofile_map', $this->m4is_m7513 );
 
}private 
function m4is_k2136(){
global $wpdb;
 static $m4is_m719 =null;
 if(is_null($m4is_m719 )){
 $m4is_g60 =buddypress();
 $m4is_m719 =[];
 $m4is_v2613 ="SELECT `id`, `name` FROM `{$m4is_g60->profile->table_name_fields
}` WHERE 1 ";
 $m4is_u6591 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 foreach ($m4is_u6591 as $m4is_g91703 ){
$m4is_m719[$m4is_g91703['id']]=$m4is_g91703['name'];
 
}
}return $m4is_m719;
 
}private 
function m4is_e16(){
echo '</table>';
 echo '<p><input type="submit" name="save" value="Save BuddyPress Field Sync" class="button-primary" /></p>';
 echo '</form>';
 echo '</div>';
 
}private 
function m4is_w689(){
echo '<div class="wrap">';
 echo '<form method="POST" action="">';
 echo '<input type="hidden" name="formtype" value="', $_GET['tab'], '">';
 wp_nonce_field(__FILE__);
 echo '<table class="widefat">';
 echo '<thead><tr></td><th>Keap Field</th><th>BuddyPress XProfile Field</th><th style="width:150px;"></th></tr></thead>';
 
}private 
function m4is_d628(){
$m4is_m719 =$this->m4is_k2136();
 $this->m4is_w689();
 foreach ($this->m4is_y368 as $m4is_f7628 ){
$m4is_v586 =empty($this->m4is_m7513[$m4is_f7628])? 0 : $this->m4is_m7513[$m4is_f7628];
 if($m4is_v586 ){
echo '<tr>';
 echo '<td style="width:300px;">', $m4is_f7628, '</td>';
 echo '<td>';
  echo '<input disabled type="text" value="', $m4is_m719[$m4is_v586 ], '" style="width:300px;">';
 echo '</td>';
 echo '<td><input type=submit name="xprofile_map_delete[', $m4is_f7628, ']" value="Remove" class="submitdelete"></td>';
 echo '</tr>';
 
}
}$this->m4is_s86672();
 $this->m4is_e16();
 
}private 
function m4is_s86672(){
$m4is_p08629 ='';
 foreach($this->m4is_y368 as $m4is_v43967 ){
if(!array_key_exists($m4is_v43967, $this->m4is_m7513 )){
$m4is_p08629 .= '<option value="' . $m4is_v43967 . '">' . $m4is_v43967 . '</option>';
 
}
}echo '<tr style="margin-top:20px;">';
 echo '<td>';
 echo '<select name="new_keap_map" class="basic-single" style="margin-right: 20px">', $m4is_p08629, '</select>';
 echo '</td>';
 echo '<td>';
 echo '<select name="new_xprofile_map" class="requiredtaglistdropdown"></select>';
 echo '</td>';
 echo '<td></td>';
 echo '</tr>';
 
}private 
function m4is_b61308(){
$m4is_m719 =$this->m4is_k2136();
 if(!empty($m4is_m719 )){
 $m4is_y523[]=['id' =>0, 'text' =>'(Don\'t Sync)' ];
 foreach ((array)$m4is_m719 as $m4is_d07693 =>$m4is_r637 ){
if(!in_array($m4is_d07693, $this->m4is_m7513 )){
$m4is_y523[]=['id' =>$m4is_d07693, 'text' =>$m4is_r637 ];
 
}
}$m4is_y523 =json_encode($m4is_y523 );
 unset($m4is_l9321, $m4is_d07693, $m4is_p786 );
 echo '<script>';
 echo 'var requiredtaglist      = ', $m4is_y523, ';';
 echo '</script>';
 unset($m4is_y523 );
 
}
}
}

