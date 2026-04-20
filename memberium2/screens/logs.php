<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 new m4is_i36();
 
class m4is_i36 {
private $m4is_r1546;
 private $m4is_r9613;
 private $m4is_i78603;
 private $m4is_b766;
 private $m4is_b1723;
 private $m4is_m7964;
 
function __construct(){
$this->m4is_h269();
 $this->m4is_v60();
 m4is_h65::m4is_n25();
 $this->m4is_w689();
 $this->m4is_o9356();
 $this->m4is_e16();
 
}private 
function m4is_h269(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 $this->m4is_b1723 ='memberium/log_count';
 $this->m4is_b766 ='login';
 $this->m4is_m7964 =['login' =>'<i class="fa fa-history"></i> Logins', 'loginfail' =>'<i class="fa fa-ban"></i> Login Error', 'httppost' =>'<i class="fa fa-paper-plane"></i> HTTP POST', 'autologin' =>'<i class="fa fa-magic"></i> Autologin', 'cron' =>'<i class="fa fa-clock"></i> Cron', 'phperror' =>'<i class="fa fa-bug"></i> PHP Errors',  ];
 $this->m4is_i78603 =$this->m4is_o08();
 
}private 
function m4is_o08(){
$m4is_b51936 =isset($_GET['tab'])? strtolower($_GET['tab']): '';
 $m4is_b51936 =array_key_exists($m4is_b51936, $this->m4is_m7964 )? $m4is_b51936 : $this->m4is_b766;
 return $m4is_b51936;
 
}private 
function m4is_v60(){
global $wpdb;
 if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}if($this->m4is_i78603 == 'login' ){
$m4is_e80 =m4is_l5841::m4is_h37();
 if(!empty($_POST['delete_login_log'])){
$m4is_v2613 ="DELETE FROM %i WHERE `appname` = %s";
 $m4is_v76912 =$wpdb->prepare($m4is_v2613, $m4is_e80, $this->m4is_r9613 );
 $wpdb->query($m4is_v76912 );
 delete_transient($this->m4is_b1723 );
 
}if(!empty($_POST['trim_login_log'])){
$m4is_v2613 ="DELETE FROM %i WHERE `appname` = %s AND `logintime` < UNIX_TIMESTAMP(DATE_SUB( NOW(), INTERVAL 30 DAY ) )";
 $m4is_v76912 =$wpdb->prepare($m4is_v2613, $m4is_e80, $this->m4is_r9613 );
 $wpdb->query($m4is_v76912 );
 delete_transient($this->m4is_b1723 );
 
}
}elseif($this->m4is_i78603 == 'httppost' ){
if(!empty($_POST['delete_httppost'])){
m4is_q62395::m4is_n32950();
 
}
}elseif($this->m4is_i78603 == 'autologin' ){
if(!empty($_POST['delete_autologin'])){
m4is_q62395::m4is_g03();
 
}
}elseif($this->m4is_i78603 == 'loginfail' ){
if(!empty($_POST['delete_loginerror_log'])){
m4is_q62395::m4is_r8051();
 
}
}
}private 
function m4is_c016(): void {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("TRUNCATE TABLE %i", m4is_l5841::m4is_h37());
 $wpdb->query($m4is_v2613 );
 delete_transient($this->m4is_b1723 );
 
}private 
function m4is_w689(){
m4is_s6729::m4is_c26()->m4is_a4215();
 echo '<div class="wrap">';
  echo '<h2 class="nav-tab-wrapper">';
 foreach ($this->m4is_m7964 as $m4is_b51936 =>$m4is_k52736 ){
$m4is_s6347 =($m4is_b51936 == $this->m4is_i78603 )? ' nav-tab-active' : '';
 if($m4is_b51936 == $this->m4is_i78603 ){
echo "<span class='nav-tab{$m4is_s6347
}'>{$m4is_k52736
}</span>";
 
}else{
echo "<a class='nav-tab{$m4is_s6347
}' href='?page=", $_GET['page'], "&tab={$m4is_b51936
}'>{$m4is_k52736
}</a>";
 
}
}echo '</h2>';
 
}private 
function m4is_o9356(){
$m4is_d04266 =$this->m4is_r1546->m4is_x63587("logs-{$this->m4is_i78603
}-show.php" );
 echo '<div class="memberium_tabcontent" style="margin-top:10px;">';
 if(file_exists($m4is_d04266 )){
require_once $m4is_d04266;
 
}else{
echo '<p>Screen Missing</p>';
 
}echo '</div>';
 
}private 
function m4is_e16(){
echo '</div>';
 
}
}

