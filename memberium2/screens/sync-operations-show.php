<?php

/**
 * Copyright (c) 2017-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 m4is_j692::m4is_z95();
 final 
class m4is_j692 {
private $m4is_r1546;
 private $makepass_scheduled;
 static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_v026();
 $this->m4is_i702();
 $this->m4is_s3572();
 $this->m4is_k56();
 
}private 
function m4is_v026(){
current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 
}private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->makepass_scheduled =false;
 
}private 
function m4is_s3572(){
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}$m4is_l2669 =isset($_POST['makepass_scan_size'])? (int) $_POST['makepass_scan_size']: 0;
 $m4is_l38476 =isset($_POST['makepass_scan_tag'])? (int) $_POST['makepass_scan_tag']: 0;
 $m4is_a9354 =isset($_POST['makepass_success_actionset'])? (int) $_POST['makepass_success_actionset']: 0;
 $m4is_m7520 =isset($_POST['makepass_success_tag'])? (int) $_POST['makepass_success_tag']: 0;
 if($m4is_l2669 < 0 ||$m4is_l2669 > 5 ){
$m4is_l2669 =0;
 
}$this->m4is_r1546->m4is_d64918($m4is_l2669, 'settings', 'makepass_scan_size' );
 $this->m4is_r1546->m4is_d64918($m4is_l38476, 'settings', 'makepass_scan_tag' );
 $this->m4is_r1546->m4is_d64918($m4is_a9354, 'settings', 'makepass_success_actionset', );
 $this->m4is_r1546->m4is_d64918($m4is_m7520, 'settings', 'makepass_success_tag', );
 m4is_h65::m4is_z896('MakePass Scanner Options Updated.' );
 
}
function m4is_n074(): array {
$m4is_k98670 =function($m4is_k72, $m4is_o015 ){
if($m4is_k72 > time()- 3600 ){
foreach($m4is_k72 as $k2 =>$v2 ){
if($k2 == 'memberium/contacts/makepass_scan' ){
return true;
 
}
}
}return false;
 
};
 $m4is_y025 =get_option('cron', []);
 return array_filter($m4is_y025, $m4is_k98670, ARRAY_FILTER_USE_BOTH );
 
}
function m4is_k56(){
$m4is_c1637 ='m4is_h65';
 $m4is_y025 =$this->m4is_n074();
 if(empty($m4is_y025 )){
echo '<p><strong>Warning:</strong>  The MakePass Scanner process (WP CRON) is not running.  This service will reinstall each hour.</p>';
 
}$m4is_l2669 =$this->m4is_r1546->m4is_j498('settings', 'makepass_scan_size' );
 $m4is_l38476 =$this->m4is_r1546->m4is_j498('settings', 'makepass_scan_tag' );
 $m4is_a9354 =$this->m4is_r1546->m4is_j498('settings', 'makepass_success_actionset' );
 $m4is_m7520 =$this->m4is_r1546->m4is_j498('settings', 'makepass_success_tag' );
 echo '<h3>Password Generation Scanner</h3>';
 echo '<p><strong style="color:red;">This tool is only recommended if you cannot reliably use HTTP POST to create users.</strong></p>';
 echo '<p>This tool is slower, and uses a large number of API calls to operate.</p>';
 echo '<ul>';
 $m4is_c1637::m4is_h70259('Makepass Start Tag', 'makepass_scan_tag', $m4is_l38476, 'taglistdropdown', ['help_id' =>12526]);
 $m4is_c1637::m4is_h70259('Makepass Complete Tag', 'makepass_success_tag', $m4is_m7520, 'taglistdropdown', ['help_id' =>12526]);
 $m4is_c1637::m4is_h70259('Makepass Complete Actionset', 'makepass_success_actionset', $m4is_a9354, 'actionsetdropdown', ['help_id' =>12526]);
 $m4is_c1637::m4is_w6712('Contacts Per Scan', 'makepass_scan_size', $m4is_l2669, ['min' =>1, 'max' =>5, 'help_id' =>12526, 'style' =>'text-align:right;width:80px;']);
 if($m4is_l38476 &&$m4is_l2669 ){
m4is_f642::m4is_t2678();
 
}echo '</ul>';
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 
}
}

