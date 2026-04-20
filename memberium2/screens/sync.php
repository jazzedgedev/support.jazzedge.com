<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_s6729' )||die();
 m4is_o68::m4is_c26();
 final 
class m4is_o68 {
private $m4is_r1546;
 static public 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_v026();
 $this->m4is_i702();
 $this->m4is_s5801();
   $m4is_m7964 =$this->m4is_u14();
 $m4is_i78603 =$this->m4is_o08($m4is_m7964 );
 $this->m4is_t46960($m4is_m7964, $m4is_i78603 );
 $this->m4is_p676($m4is_i78603 );
 $this->m4is_d65();
 
}private 
function m4is_v026(): void {
current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 
}private 
function m4is_i702(): void {
$this->m4is_r1546 =memberium_service_class::m4is_a40('app' );
 
}private 
function m4is_u14(): array {
$m4is_n569 =['operations' =>'sync-operations-show.php', 'categories' =>'sync-categories-show.php', 'contactfields' =>'sync-contactfields-show.php', 'buddypress' =>'sync-buddypress-show.php', 'affiliatefields' =>'sync-affiliatefields-show.php', 'wipe' =>'sync-wipe-show.php', ];
 $m4is_m7964 =['operations' =>'<i class="fa fa-plug"></i> Operations', 'categories' =>'<i class="fa fa-tags"></i> Tag Categories', 'contactfields' =>'<i class="fa fa-user"></i> Contact Fields', ];
 if(!isset($_GET['override'])){
$m4is_l38476 =$this->m4is_r1546->m4is_j498('settings', 'makepass_scan_tag' );
 $m4is_a9354 =$this->m4is_r1546->m4is_j498('settings', 'makepass_success_actionset' );
 $m4is_m7520 =$this->m4is_r1546->m4is_j498('settings', 'makepass_success_tag' );
 if(!$m4is_l38476 ||!$m4is_a9354 ||!$m4is_m7520 ){
unset($m4is_m7964['operations']);
 
}
}$m4is_x506 =$this->m4is_r1546->m4is_j498('settings', 'sync_affiliate', 0 );
 if($m4is_x506 ){
$m4is_m7964['affiliatefields']='<i class="fa fa-user-plus"></i> Affiliate Fields';
 
}if(function_exists('bp_is_active' )&&bp_is_active('xprofile' )){
$m4is_m7964['buddypress']='<i class="fa fa-user-plus"></i> BuddyPress Fields';
 
}return $m4is_m7964;
 
}private 
function m4is_s5801(): void {
m4is_z6894::m4is_d7492();
 m4is_s695::m4is_r2903();
 m4is_h65::m4is_n25();
 
}private 
function m4is_o08(array $m4is_m7964 ): string {
$m4is_b51936 =empty($_GET['tab'])? array_key_first($m4is_m7964 ): strtolower($_GET['tab']);
 $m4is_i78603 =array_key_exists($m4is_b51936, $m4is_m7964 )? $m4is_b51936 : array_key_first($m4is_m7964 );
 return $m4is_i78603;
 
}private 
function m4is_t46960(array $m4is_m7964, string $m4is_i78603 ): void {
$m4is_d3012 ='memberium-sync-options';
 m4is_s6729::m4is_c26()->m4is_a4215();
 echo '<div class="wrap">';
  echo '<h3 class="nav-tab-wrapper">';
 foreach ($m4is_m7964 as $m4is_b51936 =>$m4is_k52736 ){
$class =($m4is_b51936 == $m4is_i78603 )? ' nav-tab-active' : '';
 if($m4is_b51936 == $m4is_i78603 ){
echo "<span class='nav-tab{$class
}'>{$m4is_k52736
}</span>";
 
}else{
echo "<a class='nav-tab{$class
}' href='?page={$m4is_d3012
}&tab={$m4is_b51936
}'>{$m4is_k52736
}</a>";
 
}
}
}private 
function m4is_p676($m4is_i78603 ){
$m4is_a6465 ='memberium_sync_api';
 echo '</h3>';
 echo '<div class="memberium_tabcontent" style="margin-top:10px;">';
 echo '<div class="wrap">';
 echo '<form method="POST" action="">';
 echo '<input type="hidden" name="formtype" value="', $m4is_i78603, '">';
 wp_nonce_field($this->m4is_r1546->m4is_j541(), $m4is_a6465 );
 $m4is_n569 =['operations' =>'sync-operations-show.php', 'categories' =>'sync-categories-show.php', 'contactfields' =>'sync-contactfields-show.php', 'buddypress' =>'sync-buddypress-show.php', 'affiliatefields' =>'sync-affiliatefields-show.php', 'wipe' =>'sync-wipe-show.php', ];
 if(array_key_exists($m4is_i78603, $m4is_n569 )){
require_once $this->m4is_r1546->m4is_x63587($m4is_n569[$m4is_i78603]);
 
}else{
$this->m4is_r1546->m4is_s965('easter_egg' );
 echo wp_oembed_get('https://www.youtube.com/watch?v=zgvXtexdgAM', ['autoplay' =>'1']);
 echo '<p>Klaatu Barada N... Necktie... Neckturn... Nickel...</p><p>It\'s an "N" word, it\'s definitely an "N" word!</p><p>Klaatu... Barada... N...</p>';
 
}echo '</form>';
 echo '</div>';
 echo '</div>';
 echo '</div>';
 
}private 
function m4is_d65(): void {
$m4is_l9321 =m4is_k865::m4is_z2906(true );
 $m4is_l9321 =$m4is_l9321['mc'];
 $m4is_z470 =[];
 $m4is_z470[]=['id' =>0, 'text' =>'(None)' ];
 foreach ((array)$m4is_l9321 as $m4is_d07693 =>$m4is_p786 ){
$m4is_z470[]=['id' =>$m4is_d07693, 'text' =>$m4is_p786 . ' (' . $m4is_d07693 . ')' ];
 
}$m4is_z470 =json_encode($m4is_z470 );
 unset($m4is_l9321, $m4is_d07693, $m4is_p786 );
 echo '<script>';
 echo 'var actionsetlist = ', m4is_j4156::m4is_s6612(), ";\n";
 echo 'var taglist = ', $m4is_z470, ";\n";
 echo '</script>';
 unset($m4is_z470 );
 
}
}

