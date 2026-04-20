<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 m4is_e74806::m4is_z95();
 final 
class m4is_e74806 {
private array $m4is_m7964 =[];
 private ?object $m4is_r1546 =null;
 private string $m4is_r9613 ='';
 private string $m4is_b766 ='general';
 private string $m4is_b51936 ='';
 static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_v026();
 $this->m4is_i702();
 $this->m4is_o08();
 $this->m4is_s3572();
 $this->m4is_w5361();
 $this->m4is_i1869();
 $this->m4is_e98316();
 $this->m4is_g27();
 
}private 
function m4is_v026(){
current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 
}private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 
}
function m4is_h6617(){
$this->m4is_m7964 =['general' =>'<i class="fas fa-shopping-cart"></i> General', 'subscriptions' =>'<i class="fas fa-sync-alt fa-spin"></i> Subscriptions', 'invoices' =>'<i class="fas fa-file-invoice-dollar"></i> Invoices', ];
 return $this->m4is_m7964;
 
}
function m4is_o08(){
if(empty($this->m4is_b51936 )){
$this->m4is_b51936 =isset($_GET['tab'])? strtolower(trim($_GET['tab'])): $this->m4is_b766;
 $this->m4is_b51936 =array_key_exists($this->m4is_b51936, $this->m4is_h6617())? $this->m4is_b51936 : $this->m4is_b51936;
 
}return $this->m4is_b51936;
 
}
function m4is_s3572(){
if($_SERVER['REQUEST_METHOD']<> 'POST'){
return;
 
}if(!wp_verify_nonce($_POST['memberium_ecommerce_nonce'], $this->m4is_r1546->m4is_j541())){
wp_die('nonce error' );
 return;
 
}$m4is_i78603 =$this->m4is_o08();
 if($m4is_i78603 == 'general' ){
$this->m4is_w4538();
 
}elseif($m4is_i78603 == 'subscriptions' ){
$this->m4is_s68364();
 
}elseif($m4is_i78603 == 'invoices' ){
$this->m4is_u1606();
 
}
}private 
function m4is_w4538(){
$m4is_u92 =isset($_POST['affiliate_detect'])? (int) $_POST['affiliate_detect']: $this->m4is_r1546->m4is_j498('settings', 'affiliate_detect' );
 $m4is_o91 =isset($_POST['merchant_account_id'])? (int) $_POST['merchant_account_id']: $this->m4is_r1546->m4is_j498('settings', 'merchant_account_id' );
 $this->m4is_r1546->m4is_d64918($m4is_u92, 'settings', 'affiliate_detect' );
 $this->m4is_r1546->m4is_d64918($m4is_o91, 'settings', 'merchant_account_id' );
 m4is_h65::m4is_z896('General eCommerce Options Updated' );
 
}private 
function m4is_s68364(): void {
$m4is_j15 =[];
 foreach($_POST as $m4is_l9671 =>$m4is_v586 ){
if(is_array($m4is_v586 )){
$m4is_j15[$m4is_l9671]=$m4is_v586;
 
}$this->m4is_r1546->m4is_d64918($m4is_j15, 'ecommerce', 'actions' );
 
}m4is_h65::m4is_z896('Subscription Management Options Updated' );
 
}private 
function m4is_u1606(){
$m4is_p6925 =get_option('memberium_invoice_template', false );
 $m4is_p6925['header']=isset($_POST['invoice_header'])? trim(stripslashes($_POST['invoice_header'])): '';
 $m4is_p6925['items']=isset($_POST['invoice_items'])? trim(stripslashes($_POST['invoice_items'])): '';
 $m4is_p6925['pre_payments']=isset($_POST['invoice_pre_payments'])? trim(stripslashes($_POST['invoice_pre_payments'])): '';
 $m4is_p6925['payments']=isset($_POST['invoice_payments'])? trim(stripslashes($_POST['invoice_payments'])): '';
 $m4is_p6925['pre_scheduled']=isset($_POST['invoice_pre_scheduled'])? trim(stripslashes($_POST['invoice_pre_scheduled'])): '';
 $m4is_p6925['scheduled']=isset($_POST['invoice_scheduled'])? trim(stripslashes($_POST['invoice_scheduled'])): '';
 $m4is_p6925['footer']=isset($_POST['invoice_footer'])? trim(stripslashes($_POST['invoice_footer'])): '';
 $m4is_p6925 =update_option('memberium_invoice_template', $m4is_p6925 );
 m4is_h65::m4is_z896('Invoice Display Options Updated' );
 
}
function m4is_w5361(){
m4is_s6729::m4is_c26()->m4is_a4215();
 m4is_h65::m4is_n25();
 echo <<<HTMLBLOCK
			<div class="wrap">
				<!-- h1>eCommerce Settings</!-->
				<h2 class="nav-tab-wrapper">
		HTMLBLOCK;
 foreach ($this->m4is_m7964 as $m4is_b51936 =>$m4is_k52736 ){
$class =($m4is_b51936 == $this->m4is_b51936 )? ' nav-tab-active' : '';
 if($m4is_b51936 == $this->m4is_b51936 ){
echo "<span class='nav-tab$class'>$m4is_k52736</span>";
 
}else{
echo "<a class='nav-tab{$class
}' href='?page=", $_GET['page'], "&tab={$m4is_b51936
}'>{$m4is_k52736
}</a>";
 
}
}echo '</h2>';
 echo '<div class="memberium_tabcontent" style="margin-top:10px;">';
 echo '<form method="POST" action="">';
 
}
function m4is_e98316 (){
echo '</form>';
 echo '</div>';
 echo '</div>';
 
}
function m4is_i1869(){
if($this->m4is_b51936 == 'general' ){
$this->m4is_v6048();
 
}elseif($this->m4is_b51936 == 'subscriptions' ){
$this->m4is_g7315();
 
}elseif($this->m4is_b51936 == 'invoices' ){
$this->m4is_m470();
 
}
}
function m4is_g7315(){
wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memberium_ecommerce_nonce' );
 $m4is_s5867 =$this->m4is_r1546->m4is_i916();
 $m4is_j098 =m4is_v87365::m4is_d96640();
 $m4is_j15 =$this->m4is_r1546->m4is_j498('ecommerce', 'actions');
 $m4is_e362 =isset($_POST['find'])? sanitize_text_field(trim($_POST['find'])): '';
 $m4is_k4352 =[];
 $m4is_i8726 =is_array($m4is_s5867 )? count($m4is_s5867 ): 0;
 if(!empty($m4is_e362 )){
foreach($m4is_j098 as $m4is_d07693 =>$m4is_h1438 ){
if(stripos($m4is_h1438['ProductName'], $m4is_e362 )=== false ){
$m4is_k4352[]=$m4is_d07693;
 unset($m4is_j098[$m4is_d07693]);
 
}
}if(!empty($m4is_k4352 )){
foreach($m4is_s5867 as $m4is_d07693 =>$m4is_a6834 ){
if(in_array($m4is_a6834['ProductId'], $m4is_k4352 )){
unset($m4is_s5867[$m4is_d07693]);
 
}
}
}
}else{
if($m4is_i8726 > 100 ){
$m4is_s5867 =array_slice($m4is_s5867, $m4is_i8726 - 100, 100 );
 
}
}echo '<P>Search: <input name="find" type="text" value="', $m4is_e362, '"></P>';
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 echo '<table class="widefat" style="white-space:nowrap;">';
 echo '<tr>';
 echo '<th>Subscription</th><th>Payment Action</th><th>Payment Goal</th><th>Cancel Action</th><th>Cancel Goal</th><th>End Date Action</th><th>End Date Goal</th>';
 echo '</tr>';
 if(is_array($m4is_s5867 )){
foreach ($m4is_s5867 as $m4is_g91703 ){
$m4is_d07693 =$m4is_g91703['Id'];
 $m4is_o6480 =$m4is_g91703['ProductId'];
 if($m4is_g91703['Active']){
$cancel_action =empty($m4is_j15[$m4is_d07693 ]['cancel_action'])? 0 : $m4is_j15[$m4is_d07693 ]['cancel_action'];
 $cancel_goal =empty($m4is_j15[$m4is_d07693 ]['cancel_goal'])? '' : $m4is_j15[$m4is_d07693 ]['cancel_goal'];
 $end_action =empty($m4is_j15[$m4is_d07693 ]['end_action'])? 0 : $m4is_j15[$m4is_d07693 ]['end_action'];
 $end_goal =empty($m4is_j15[$m4is_d07693 ]['end_goal'])? '' : $m4is_j15[$m4is_d07693 ]['end_goal'];
 $pay_action =empty($m4is_j15[$m4is_d07693 ]['pay_action'])? 0 : $m4is_j15[$m4is_d07693 ]['pay_action'];
 $pay_goal =empty($m4is_j15[$m4is_d07693 ]['pay_goal'])? '' : $m4is_j15[$m4is_d07693 ]['pay_goal'];
 echo '<tr>';
 echo '<td>', $m4is_j098[$m4is_o6480 ]['ProductName'], ' - $', sprintf('%01.2f', $m4is_g91703['PlanPrice']), ' / ', $m4is_g91703['FrequencyWord'], '</td>';
 echo '<td><input class="actionsetdropdown" type="text" value="', $pay_action, '" name="', $m4is_d07693, '[pay_action]"></td>';
 echo '<td><input type="text" value="', $pay_goal, '" name="', $m4is_d07693, '[pay_goal]"></td>';
 echo '<td><input class="actionsetdropdown" type="number" min="0" max="99999" size="3" value="', (int) $cancel_action, '" name="', $m4is_d07693, '[cancel_action]"></td>';
 echo '<td><input type="text" value="', $cancel_goal, '" name="', $m4is_g91703['Id'], '[cancel_goal]"></td>';
 echo '<td><input class="actionsetdropdown" type="number" min="0" max="99999" size="5" value="', (int) $end_action, '" name="', $m4is_d07693, '[end_action]"></td>';
 echo '<td><input type="text" value="', $end_goal, '" name="', $m4is_g91703['Id'], '[end_goal]"></td>';
 echo '</tr>';
 
}
}
}echo '</table>';
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 
}
function m4is_v6048(){
$m4is_s076 =m4is_v87365::m4is_p68();
 $m4is_e7546 =(int) $m4is_s076['default_merchant_account'];
 $m4is_j65 =[];
 foreach($m4is_s076['merchant_accounts']as $m4is_s8617 ){
$m4is_j65[$m4is_s8617->id]=sprintf('%s %s (%d)', $m4is_s8617->account_name, $m4is_s8617->type, $m4is_s8617->id );
 
}$m4is_u92 =$this->m4is_r1546->m4is_j498('settings', 'affiliate_detect', 0 );
 $m4is_o10537 =$this->m4is_r1546->m4is_j498('settings', 'password_reset_tag', 0);
 $m4is_e806 =$this->m4is_r1546->m4is_j498('settings', 'merchant_account_id', 0 );
 $m4is_c1637 ='m4is_h65';
 wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memberium_ecommerce_nonce' );
 echo '<ul>';
 $m4is_c1637::m4is_y648('Affiliate AutoDetect', 'affiliate_detect', 9124, $m4is_u92 );
 m4is_h65::m4is_z30162('Default Merchant Account:', 'merchant_account_id', $m4is_e806, $m4is_j65, ['style' =>'width:250px;', 'help_id' =>21852, ]);
  m4is_h65::m4is_h70259('Password Reset Tag', 'password_reset_tag', $m4is_o10537, 'taglistdropdown', ['help_id' =>1183]);
 echo '</ul>';
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 
}
function m4is_m470(){
wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memberium_ecommerce_nonce' );
 echo '<ul>';
 echo '<h2>Invoice Display Styler</h2>';
 $m4is_s84520 =get_option('memberium_invoice_template', false);
  echo '<style> textarea { background-color: antiquewhite; } </style>';
 echo '<li><label style="vertical-align:top;">Header ', m4is_h65::m4is_o64(0000 ), '</label>';
 echo '<textarea name="invoice_header" cols=80 rows=3>', isset($m4is_s84520['header'])? $m4is_s84520['header']: '', '</textarea>';
 echo '</li>';
  echo '<li><label style="vertical-align:top;">Line Items ', m4is_h65::m4is_o64(0000 ), '</label>';
 echo '<textarea name="invoice_items" cols=80 rows=3>', isset($m4is_s84520['items'])? $m4is_s84520['items']: '', '</textarea>';
 echo '</li>';
  echo '<li><label style="vertical-align:top;">Payments Header', m4is_h65::m4is_o64(0000 ), '</label>';
 echo '<textarea name="invoice_pre_payments" cols=80 rows=3>', isset($m4is_s84520['pre_payments'])? $m4is_s84520['pre_payments']: '', '</textarea>';
 echo '</li>';
  echo '<li><label style="vertical-align:top;">Payments ', m4is_h65::m4is_o64(0 ), '</label>';
 echo '<textarea name="invoice_payments" cols=80 rows=3>', isset($m4is_s84520['payments'])? $m4is_s84520['payments']: '', '</textarea>';
 echo '</li>';
  echo '<li><label style="vertical-align:top;">Scheduled Payments Header ', m4is_h65::m4is_o64(0000 ), '</label>';
 echo '<textarea name="invoice_pre_scheduled" cols=80 rows=3>', isset($m4is_s84520['pre_scheduled'])? $m4is_s84520['pre_scheduled']: '', '</textarea>';
 echo '</li>';
  echo '<li><label style="vertical-align:top;">Scheduled Payments ', m4is_h65::m4is_o64(0000 ), '</label>';
 echo '<textarea name="invoice_scheduled" cols=80 rows=3>', isset($m4is_s84520['scheduled'])? $m4is_s84520['scheduled']: '', '</textarea>';
 echo '</li>';
  echo '<li><label style="vertical-align:top;">Footer ', m4is_h65::m4is_o64(0000 ), '</label>';
 echo '<textarea name="invoice_footer" cols=80 rows=3>', isset($m4is_s84520['footer'])? $m4is_s84520['footer']: '', '</textarea>';
 echo '</li>';
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 echo '</form>';
 echo '<li><label style="vertical-align:top;">%% Codes</label>';
 echo '<div style="display:inline-block;width:600px;">';
 $m4is_k48 =['invoice', ['job', 'order'], 'contact', 'payplan', 'payment', ['payplanitem', 'scheduled'], ['orderitem', 'item'], ];
 foreach ($m4is_k48 as $m4is_e80 ){
if(is_array ($m4is_e80 )){
$prefix =$m4is_e80[1];
 $m4is_e80 =$m4is_e80[0];
 
}else{
$prefix =$m4is_e80;
 
}$m4is_a89 =m4is_c69807::m4is_f5248($m4is_e80, false );
 sort($m4is_a89 );
 echo '<strong>', ucwords($prefix ), '</strong><br />';
 if(is_array($m4is_a89 )){
foreach($m4is_a89 as $m4is_q523 ){
$m4is_v3458 ='%%' . $prefix . '.' . strtolower($m4is_q523 ). '%%';
 echo '<input type="text" size="', strlen($m4is_v3458 )+ 2, '" value="', $m4is_v3458, '" readonly style="text-align:center;"> ';
 
}echo '<br><br>';
 
}
}echo '<strong>Custom Codes</strong><br />';
 $m4is_a89 =['subtotal' ];
 foreach($m4is_a89 as $m4is_q523 ){
$m4is_v3458 ='%%receipt.' . strtolower($m4is_q523 ). '%%';
 echo '<input type="text" size="', strlen($m4is_v3458 )+ 2, '" value="', $m4is_v3458, '" readonly style="text-align:center;"> ';
 
}echo '</div></li>';
 echo '</ul>';
 
}
function m4is_g27(){
global $wpdb;
  $m4is_l9321 =m4is_k865::m4is_z2906(true );
 $m4is_l9321 =$m4is_l9321['mc'];
 $m4is_z470 =[];
 $m4is_z470[]=['id' =>0, 'text' =>'(None)' ];
 foreach ((array)$m4is_l9321 as $m4is_d07693 =>$m4is_p786 ){
$m4is_z470[]=['id' =>$m4is_d07693, 'text' =>"{$m4is_p786
} ({$m4is_d07693
})", ];
 
}$m4is_z470 =json_encode($m4is_z470 );
 unset($m4is_l9321, $m4is_d07693, $m4is_p786 );
 echo '<script>';
 echo 'var actionsetlist = ', m4is_j4156::m4is_s6612(), ';';
 echo 'var taglist       = ', $m4is_z470, ';';
 echo '</script>';
 unset($m4is_y66503, $m4is_z470 );
 
}
}

