<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_t36568 {
 private static m4is_r83 $m4is_r1546;
 private static string $m4is_r9613;
 private static object $m4is_f683;
 private static object $m4is_z59682;
 private static bool $m4is_n1028;
 private static string $m4is_f4218;
  public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_f683 =m4is_f58::m4is_c26();
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_n1028 =!m4is_s52::m4is_w74();
 self::$m4is_f4218 ='memberium';
 m4is_j586::m4is_k751();
 m4is_j586::m4is_x7134();
 
} public static 
function m4is_c43859(){
if(self::$m4is_n1028){
return;
 
}if(!wp_verify_nonce($_POST['_wpnonce'], 'place_order_button-' . $_POST['form_id'])){
wp_die('Security Check Failed - Nonce Validation Error' );
 
}if(!self::$m4is_r1546->m4is_f1072($_POST['digital_signature'], $_POST['order_settings'])){
wp_die('Security Check Failed - Signature Validation Error' );
 
}$m4is_h21895 =(int) self::$m4is_r1546->m4is_z56();
 $m4is_m92735 =(int) $_POST['form_id'];
 $m4is_n418 =unserialize(base64_decode($_POST['order_settings']));
 $m4is_l97604 =self::$m4is_r1546->m4is_c38461($m4is_n418 );
 if($m4is_l97604 ){
if($m4is_n418['success_url']> '' ){
$m4is_f56 =$m4is_n418['success_url'];
 
}
}else{
if($m4is_n418['fail_url']> '' ){
$m4is_f56 =$m4is_n418['fail_url'];
 
}
}self::$m4is_r1546->m4is_x4831($m4is_h21895 );
 self::$m4is_r1546->m4is_i12($m4is_h21895 );
 if($m4is_f56 > '' ){
wp_redirect($m4is_f56, 302, 'Memberium - Order Catcher' );
 exit;
 
}
} public static 
function m4is_q605(){
if(self::$m4is_n1028){
return;
 
}if(!self::$m4is_r1546->m4is_z56()){
return;
 
}$m4is_v80635 ='memb_actionset_button';
 m4is_j586::m4is_x7134();
 if(!wp_verify_nonce($_POST['_wpnonce'], 'memb_actionset_' . $_POST['form_id'])){
wp_die(_x('Security Check Failed - Nonce Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 
}if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['action'])){
wp_die(_x('Security Check Failed - Signature Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 
}$m4is_c05328 =unserialize(base64_decode($_POST['action']));
 $m4is_h21895 =isset($m4is_c05328['contact_id'])? $m4is_c05328['contact_id']: self::$m4is_r1546->m4is_z56();
 $m4is_m92735 =isset($_POST['form_id'])? (int) $_POST['form_id']: 0;
 $m4is_c6016 =isset($_POST['order_id'])? (int) $_POST['order_id']: 0;
 $m4is_f56 =isset($m4is_c05328['redirect'])? $m4is_c05328['redirect']: '';
 if(empty($m4is_f56 )){
$m4is_f56 =isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: $_SERVER['REQUEST_URI'];
 
}if($m4is_h21895 > 0 ){
self::$m4is_r1546->m4is_s85($m4is_c05328['tokens']);
 self::$m4is_r1546->m4is_k59073($m4is_c05328['fus'], $m4is_h21895 );
 self::$m4is_r1546->m4is_t64038($m4is_c05328['goals'], $m4is_h21895 );
 self::$m4is_r1546->m4is_k98($m4is_c05328['tags'], $m4is_h21895 );
 self::$m4is_r1546->m4is_u71903($m4is_c05328['action_id'], $m4is_h21895);
 
}$m4is_y66291 =['action_id' =>$m4is_c05328['action_id'], 'contact_id' =>$m4is_c05328['contact_id'], 'fus' =>$m4is_c05328['fus'], 'goals' =>$m4is_c05328['goals'], 'tags' =>$m4is_c05328['tags'], 'redirect' =>$m4is_f56, ];
 do_action('memberium/actionset_button/clicked', $m4is_y66291 );
 if($m4is_c05328['debug']){
echo '<pre>';
 echo __LINE__, " - Set ActionSet = {$m4is_c05328['action_id']
}<br />";
 echo __LINE__, " - Set ContactId = {$m4is_c05328['contact_id']
}<br />";
 echo __LINE__, " - Set FUS       = {$m4is_c05328['fus']
}<br />";
 echo __LINE__, " - Set Goals     = {$m4is_c05328['goals']
}<br />";
 echo __LINE__, " - Set Tags      = {$m4is_c05328['tags']
}<br />";
 echo __LINE__, " - Set Tokens    = {$m4is_c05328['tokens']
}<br />";
 echo __LINE__, " - Set Redirect  = {$m4is_f56
}<br />";
 echo '</pre>';
 echo '<a href="', $m4is_f56, '">', _x('Continue...', $m4is_v80635, self::$m4is_f4218 ), '</a>';
 exit;
 
}if($m4is_f56 > '' ){
wp_redirect($m4is_f56, 302, 'Memberium Actionset Button' );
 exit;
 
}
} public static 
function m4is_s9062(){
if(empty($_FILES['uploadedfiles'])){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_v80635 ='memb_filebox_upload';
 $m4is_r1546 =m4is_r83::m4is_c26();
 if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['params'])){
wp_die(_x('Security Check Failed - Signature Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 exit;
 
}$m4is_u897 =unserialize(base64_decode($_POST['params']));
 $m4is_h21895 =(int) $m4is_u897['contact_id'];
 $m4is_j8657 =$m4is_u897['rename'];
 $m4is_y5630 =count($_FILES['uploadedfiles']['name']);
 $m4is_x78542 =0;
 $m4is_p36840 ='';
 $m4is_i61 =[];
 for ($m4is_b3785 =0;
 $m4is_b3785 < $m4is_y5630;
 $m4is_b3785++ ){
$m4is_k52736 =$_FILES['uploadedfiles']['name'][$m4is_b3785];
 $m4is_j0361 =$_FILES['uploadedfiles']['type'][$m4is_b3785];
 $m4is_x149 =$_FILES['uploadedfiles']['tmp_name'][$m4is_b3785];
 $m4is_q847 =$_FILES['uploadedfiles']['error'][$m4is_b3785];
 $m4is_f3156 =$_FILES['uploadedfiles']['size'][$m4is_b3785];
 if($m4is_f3156 > $m4is_u897['maxsize']||$m4is_q847 == 2 ){
$m4is_x78542++;
 $m4is_p36840 .= sprintf('<p class="filebox_upload_error">File size of "%s" exceeded the %s limit.</p>', $m4is_k52736, size_format($m4is_u897['maxsize']));
 continue;
 
}if(!empty($m4is_q847)){
$m4is_x78542++;
 $m4is_p36840 .= sprintf('<p class="filebox_upload_error">Upload of %s failed (%s).</p>', $m4is_k52736, $m4is_q847 );
 continue;
 
}$m4is_i61[]=['name' =>$m4is_k52736, 'type' =>$m4is_j0361, 'size' =>$m4is_f3156, 'error' =>$m4is_q847, 'tmp_name' =>$m4is_x149, ];
 
}if($m4is_u897['no_errors']&&$m4is_x78542){
$m4is_p36840 .= '<p class="filebox_upload_error">Some uploads failed.  No files saved.</p>';
 $m4is_x78542++;
 
}else{
foreach($m4is_i61 as $m4is_k86914){
 if(!empty($m4is_j8657)){
$m4is_k86914['name']=$m4is_j8657 . '.' . pathinfo($m4is_k86914['name'], PATHINFO_EXTENSION );
 
} $m4is_r98 =base64_encode(file_get_contents($m4is_k86914['tmp_name']));
 $m4is_u6591 =self::$m4is_z59682->uploadFile($m4is_k86914['name'], $m4is_r98, $m4is_h21895 );
 if($m4is_u6591 < 1 ){
$m4is_p36840 .= substr($m4is_u6591, strpos($m4is_u6591, ']')+ 1, -1 ). ' for ' . $m4is_k86914['name'];
 
}unset($m4is_r98);
 unlink($m4is_k86914['tmp_name']);
 if(!(int) $m4is_u6591){
$m4is_x78542++;
 
}
}m4is_t21664::m4is_m57($m4is_h21895, true );
 
}$m4is_l9671 ='memberium::file_upload_msg::' . self::$m4is_r1546->m4is_x66();
 if($m4is_x78542 ){
  $m4is_u6591 =set_transient($m4is_l9671, $m4is_p36840, 3600 );
 self::$m4is_f683->m4is_o63($m4is_p36840 );
 self::$m4is_r1546->m4is_t64038($m4is_u897['failure_goals'], $m4is_h21895 );
 self::$m4is_r1546->m4is_u71903($m4is_u897['failure_actionsets'], $m4is_h21895 );
 self::$m4is_r1546->m4is_k98($m4is_u897['failure_tags'], $m4is_h21895 );
 if(!empty($m4is_u897['failure_url'])){
wp_redirect($m4is_u897['failure_url'], 302 );
 die();
 
}
}else{
  $m4is_u6591 =set_transient($m4is_l9671, $m4is_u897['success_msg'], 3600 );
 self::$m4is_f683->m4is_o63($m4is_u897['success_msg']);
 self::$m4is_r1546->m4is_t64038($m4is_u897['success_goals'], $m4is_h21895 );
 self::$m4is_r1546->m4is_u71903($m4is_u897['success_actionsets'], $m4is_h21895 );
 self::$m4is_r1546->m4is_k98($m4is_u897['success_tags'], $m4is_h21895 );
 if(!empty($m4is_u897['success_url'])){
wp_redirect($m4is_u897['success_url'], 302 );
 die();
 
}
}
} public static 
function m4is_p780(){
m4is_j586::m4is_x7134();
 $m4is_m92735 =$_POST['form_id'];
 if(!wp_verify_nonce($_POST['_wpnonce'], 'memb_placeorder_' . $m4is_m92735)){
wp_die('Security Check Failed - Nonce Validation Error' );
 
}if(!self::$m4is_r1546->m4is_f1072($_POST['digital_signature'], $_POST['params'])){
wp_die('Security Check Failed - Signature Validation Error' );
 
}$m4is_j86631 =unserialize(base64_decode($_POST['params']));
 if(empty($m4is_j86631['lead_affiliate_id'])||empty($m4is_j86631['sale_affiliate_id'])){
 $m4is_e80 ='Invoice';
 $m4is_a89 =['AffiliateId', 'LeadAffiliateId' ];
 $m4is_v76912 =['ContactId' =>(int) $m4is_j86631['contact_id']];
 $m4is_t042 ='Id';
 $m4is_k236 =false;
 $m4is_d01 =m4is_c69807::m4is_i84($m4is_e80, 1, 0, $m4is_v76912, $m4is_a89, $m4is_t042, $m4is_k236 );
 if(count($m4is_d01 )){
if(empty($m4is_j86631['lead_affiliate_id'])){
$m4is_j86631['lead_affiliate_id']=isset($invoice[0]['LeadAffiliateId'])? $invoice[0]['LeadAffiliateId']: 0;
 
}if(empty($m4is_j86631['sale_affiliate_id'])){
$m4is_j86631['sale_affiliate_id']=isset($invoice[0]['AffiliateId'])? $invoice[0]['AffiliateId']: 0;
 
}
}
}$m4is_y5760 =self::$m4is_z59682->placeOrder((int) $m4is_j86631['contact_id'], (int) $m4is_j86631['creditcard_id'], (int) $m4is_j86631['payplan_id'], (array)$m4is_j86631['product_ids'], (array)$m4is_j86631['subscription_ids'], (bool) $m4is_j86631['process_specials'], (array)$m4is_j86631['promo_codes'], (int) $m4is_j86631['lead_affiliate_id'], (int) $m4is_j86631['sale_affiliate_id']);
  $m4is_l97604 =(int) $m4is_y5760['InvoiceId'];
 $m4is_o064 =(int) $m4is_y5760['OrderId'];
 if($m4is_y5760['Successful']== 'true' ){
$m4is_e768 =$m4is_j86631['success_actionset'];
 $m4is_v8723 =$m4is_j86631['success_goal'];
 $m4is_p786 =$m4is_j86631['success_tag'];
 $m4is_n6062 =$m4is_j86631['success_url'];
 if(!empty($m4is_j86631['order_title'])){
m4is_c69807::m4is_z64('Job', $m4is_o064, ['JobTitle' =>$m4is_j86631['order_title']]);
 
}
}else{
$m4is_e768 =$m4is_j86631['failure_actionset'];
 $m4is_v8723 =$m4is_j86631['failure_goal'];
 $m4is_p786 =$m4is_j86631['failure_tag'];
 $m4is_n6062 =$m4is_j86631['failure_url'];
 if($m4is_j86631['delete_failed']){
self::$m4is_z59682->deleteInvoice($m4is_l97604 );
 if(!empty($m4is_j86631['subscription_ids'])){
m4is_v87365::m4is_e60495($m4is_o064 );
 
}
}
}self::$m4is_r1546->m4is_k79($m4is_j86631['contact_id'], $m4is_p786, $m4is_e768, $m4is_v8723 );
 if(!empty($m4is_n6062 )){
$m4is_u897 =['orderId' =>rawurlencode($m4is_o064 ), 'invoiceId' =>rawurlencode($m4is_l97604 ), ];
 $m4is_n6062 =add_query_arg($m4is_u897, $m4is_n6062 );
 wp_redirect($m4is_n6062, 302 );
 exit;
 
}
} public static 
function m4is_k3167(){
 $m4is_v80635 ='memb_resetfeedurl_button';
 m4is_j586::m4is_x7134();
 if(!wp_verify_nonce($_POST['_wpnonce'], 'memb_resetfeedurl_' . $_POST['form_id'])){
wp_die(_x('Security Check Failed - Nonce Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 exit;
 
}m4is_a391::m4is_v5960();
 return;
 
} public static 
function m4is_q97(){
if(self::$m4is_n1028){
return;
 
}if(!self::$m4is_r1546->m4is_z56()){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_v80635 ='memb_list_subscriptions';
 $m4is_u076 =unserialize(base64_decode($_POST['parameters']));
 if(!wp_verify_nonce($_POST['_wpnonce'], 'memb_cancelsubscription_' . $m4is_u076['recurringorder_id'])){
wp_die(_x('Security Check Failed - Nonce Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 exit;
 
}if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['parameters'])){
wp_die(_x('Security Check Failed - Signature Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 exit;
 
}$m4is_h21895 =self::$m4is_r1546->m4is_z56();
 $m4is_r37596 =self::$m4is_r1546->m4is_j498();
 $m4is_u275 =(int) $m4is_u076['recurringorder_id'];
 $m4is_s07 =(int) $m4is_u076['subscriptionplan_id'];
 $m4is_w6501 =(bool) $m4is_u076['immediate'];
 $m4is_t5382 =$m4is_u076['cancel_text'];
 $m4is_j15 =isset($m4is_r37596['ecommerce']['actions'][$m4is_s07])? $m4is_r37596['ecommerce']['actions'][$m4is_s07]: [];
 $m4is_k6670 =isset($m4is_j15['cancel_action'])? $m4is_j15['cancel_action']: 0;
 $m4is_y256 =isset($m4is_j15['cancel_goal'])? $m4is_j15['cancel_goal']: '';
 $m4is_l6512 =isset($m4is_j15['end_action'])? $m4is_j15['end_action']: 0;
 $m4is_q3861 =isset($m4is_j15['end_goal'])? $m4is_j15['end_goal']: '';
 $m4is_z81 =date('Ymd' );
 $m4is_n56 =date('Ymd', strtotime($m4is_u076['next_bill_date']. ' - 1 day' ));
 $m4is_p63619 =$m4is_w6501 ? $m4is_z81 : $m4is_n56;
  $m4is_e32607 =['AutoCharge' =>0, 'ReasonStopped' =>sprintf(_x('Cancelled through Memberium on %s.  Old End Date: %s, New End Date: %s', $m4is_v80635, self::$m4is_f4218 ), date('Y-m-d' ), $m4is_n56, $m4is_p63619 ), 'EndDate' =>$m4is_p63619 . 'T23:59:59', ];
 if($m4is_p63619 <= $m4is_z81 ){
$m4is_e32607['Status']='Inactive';
 
}$m4is_u6591 =m4is_c69807::m4is_z64('RecurringOrder', $m4is_u275, $m4is_e32607 );
  if($m4is_k6670 > 0 ){
m4is_j4156::m4is_w4805($m4is_h21895, (int) $m4is_k6670 );
 
}if($m4is_y256 > '' ){
m4is_c69807::m4is_z3902($m4is_h21895, $m4is_y256, self::$m4is_r9613 );
 
} if($m4is_l6512 > 0 ||$m4is_q3861 > '' ){
$m4is_l91805 =['recurringorder_id' =>$m4is_u275, 'end_action' =>$m4is_l6512, 'end_goal' =>$m4is_q3861, ];
 if($m4is_l6512 > 0 ){
self::$m4is_r1546->m4is_q65('actionset', $m4is_n56, $m4is_l91805 );
 
}if($m4is_q3861 > '' ){
self::$m4is_r1546->m4is_q65('achievegoal', $m4is_n56, $m4is_l91805 );
 
}
} self::$m4is_r1546->m4is_v31259($m4is_h21895 );
 $m4is_d74 =self::$m4is_r1546->m4is_c686($m4is_h21895, true );
 $m4is_n215 =['action' =>'Cancel', 'subscription_id' =>$m4is_u275, 'end_date' =>date('Y-m-d', strtotime($m4is_n56 )), 'future_expiry' =>date('Ymd' )<= $m4is_p63619 ? 0 : 1, ];
 $m4is_n6062 =add_query_arg($m4is_n215, $_SERVER['REQUEST_URI']);
 wp_redirect($m4is_n6062 );
 exit;
 
} public static 
function m4is_j6451(){
if(self::$m4is_n1028){
return;
 
}if(!self::$m4is_r1546->m4is_z56()){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_v80635 ='memb_add_creditcard';
  if(!wp_verify_nonce($_POST['_wpnonce'], 'creditcard_add_' . $_POST['form_id'])){
wp_die(_x('Security Check Failed - Nonce Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 
}if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['parameters'])){
wp_die(_x('Security Check Failed - Signature Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 
}$m4is_r6249 =true;
 $m4is_y628 =false;
 $m4is_m92735 =(int) $_POST['form_id'];
 $m4is_u076 =unserialize(base64_decode($_POST['parameters']));
 $m4is_i462 =(bool) $m4is_u076['backcharge'];
 $m4is_u807 =(int) $m4is_u076['creditcard_id'];
 $m4is_l366 =array_filter(explode(',', $m4is_u076['plan_ids']));
 $m4is_q10366 =(bool) $m4is_u076['set_default'];
 $m4is_h21895 =(int) $m4is_u076['contact_id'];
  $m4is_g14087 =trim($m4is_u076['successurl']);
 $m4is_t14956 =trim($m4is_u076['failureurl']);
 $m4is_f56 =$m4is_t14956;
 $m4is_i8169 =(bool) $m4is_u076['debug'];
 $m4is_x36 =$m4is_u076['debug_ip']=== m4is_a01587::m4is_y342();
 if($m4is_h21895 < 1 ){
$m4is_r6249 =false;
 $_SESSION['flash']['add_creditcard']=_x('Invalid User ID', $m4is_v80635, self::$m4is_f4218 );
 
}$_SESSION['flash']['add_creditcard']=$m4is_u076['success_msg'];
  if(empty($_POST['cardtype'])&&!empty($_POST['cc-number'])){
$_POST['cardtype']=m4is_l4568::m4is_g128($_POST['cc-number']);
 
} $m4is_a89 =['city', 'country', 'firstname', 'lastname', 'nameoncard', 'state', 'streetaddress1', 'streetaddress2', ];
 foreach($_POST as $m4is_l9671 =>$m4is_v586){
$_POST[$m4is_l9671]=stripslashes($m4is_v586 );
 
}  if(!$m4is_u807 ){
$m4is_g7928 =['cardtype', 'cc-number', 'city', 'country', 'cvv2', 'expirationmonth', 'expirationyear', 'nameoncard', 'phonenumber', 'streetaddress1', ];
 
}else{
$m4is_g7928 =['city', 'country', 'cvv2', 'expirationmonth', 'expirationyear', 'nameoncard', 'phonenumber', 'streetaddress1', ];
 
}if(is_array($m4is_g7928 )){
foreach ($m4is_g7928 as $m4is_q523){
if(empty($_POST[$m4is_q523])||trim($_POST[$m4is_q523])== ''){
$_SESSION['flash']['add_creditcard']=_x('You need to fill out all required fields.', $m4is_v80635, self::$m4is_f4218 );
 $m4is_r6249 =false;
 break;
 
}
}
} if($m4is_r6249 ){
if((date('Ym' )> ($_POST['expirationyear']. $_POST['expirationmonth']))){
$_SESSION['flash']['add_creditcard']=_x('The credit card is expired.', $m4is_v80635, self::$m4is_f4218 );
 $m4is_r6249 =false;
 
}
} if($m4is_u807 == 0 ){
if($m4is_r6249 ){
if(!m4is_l4568::m4is_f3172($_POST['cc-number'])){
$_SESSION['flash']['add_creditcard']=_x('The credit card number is invalid.', 'memb_add_creditcard', self::$m4is_f4218 );
 $m4is_r6249 =false;
 
}
}
} if(isset($_POST['cc-number'])&&$m4is_r6249 ){
$m4is_s4966 =substr($_POST['cc-number'], -4 );
 $alt_card_id =self::$m4is_z59682->locateCard($m4is_h21895, $m4is_s4966 );
 if($alt_card_id > 0 ){
$m4is_m09 =m4is_c01675::m4is_w68604($m4is_h21895 );
 if(isset($m4is_m09[$alt_card_id])){
$m4is_u807 =(int) $alt_card_id;
 
}
}
}if($m4is_r6249 ){
$m4is_y368 =m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact' );
 if($m4is_u807 == 0 ){
$m4is_r68 =['ContactId' =>$m4is_h21895, 'BillName' =>wp_strip_all_tags($_POST['nameoncard']?? ''), 'BillAddress1' =>wp_strip_all_tags($_POST['streetaddress1']?? ''), 'BillAddress2' =>wp_strip_all_tags($_POST['streetaddress2']?? ''), 'BillCity' =>wp_strip_all_tags($_POST['city']?? ''), 'BillCountry' =>wp_strip_all_tags($_POST['country']?? ''), 'BillState' =>wp_strip_all_tags($_POST['state']?? '' ), 'BillZip' =>wp_strip_all_tags($_POST['postalcode']?? '' ), 'CardNumber' =>wp_strip_all_tags($_POST['cc-number']?? '' ), 'CardType' =>wp_strip_all_tags($_POST['cardtype']?? '' ), 'CVV2' =>(string) $_POST['cvv2']?? '',  'FirstName' =>$m4is_y368['firstname']?? '', 'LastName' =>$m4is_y368['lastname']?? '', 'ExpirationMonth' =>wp_strip_all_tags($_POST['expirationmonth']), 'ExpirationYear' =>wp_strip_all_tags($_POST['expirationyear']), 'NameOnCard' =>wp_strip_all_tags($_POST['nameoncard']), 'Email' =>strtolower($m4is_y368['email']?? '' ), 'PhoneNumber' =>wp_strip_all_tags($_POST['phonenumber']?? '' ), 'ShipAddress1' =>$m4is_y368['address2street1']?? '', 'ShipAddress2' =>$m4is_y368['address2street2']?? '', 'ShipCity' =>$m4is_y368['city2']?? '', 'ShipCompanyName' =>$m4is_y368['company']?? '', 'ShipCountry' =>$m4is_y368['country2']?? '', 'ShipFirstName' =>$m4is_y368['firstname']?? '', 'ShipLastName' =>$m4is_y368['lastname']?? '', 'ShipMiddleName' =>'', 'ShipName' =>wp_strip_all_tags($_POST['nameoncard']?? '' ), 'ShipPhoneNumber' =>wp_strip_all_tags($_POST['phonenumber']?? '' ), 'ShipState' =>$m4is_y368['state2']?? '', 'ShipZip' =>$m4is_y368['postalcode2']?? '', ];
 $m4is_u807 =m4is_c69807::m4is_y7501('CreditCard', $m4is_r68 );
 if($m4is_u807 > 0 ){
$m4is_y628 =true;
 
}
}else{
$m4is_o30 =['BillName' =>wp_strip_all_tags($_POST['nameoncard']), 'BillAddress1' =>wp_strip_all_tags($_POST['streetaddress1']), 'BillAddress2' =>wp_strip_all_tags($_POST['streetaddress2']), 'BillCity' =>wp_strip_all_tags($_POST['city']), 'BillCountry' =>wp_strip_all_tags($_POST['country']), 'BillState' =>wp_strip_all_tags($_POST['state']), 'BillZip' =>wp_strip_all_tags($_POST['postalcode']), 'ExpirationMonth' =>wp_strip_all_tags($_POST['expirationmonth']), 'ExpirationYear' =>wp_strip_all_tags($_POST['expirationyear']), 'NameOnCard' =>wp_strip_all_tags($_POST['nameoncard']), 'PhoneNumber' =>wp_strip_all_tags($_POST['phonenumber']), 'ShipName' =>wp_strip_all_tags($_POST['nameoncard']), 'ShipPhoneNumber' =>wp_strip_all_tags($_POST['phonenumber']), 'CVV2' =>(string) trim($_POST['cvv2']), 'Email' =>strtolower($m4is_y368['email']?? '' ), 'FirstName' =>$m4is_y368['firstname']?? '', 'LastName' =>$m4is_y368['lastname']?? '', 'ShipAddress1' =>$m4is_y368['address2street1']?? '', 'ShipAddress2' =>$m4is_y368['address2street2']?? '', 'ShipCity' =>$m4is_y368['city2']?? '', 'ShipCompanyName' =>$m4is_y368['company']?? '', 'ShipCountry' =>$m4is_y368['country2']?? '', 'ShipFirstName' =>$m4is_y368['firstname']?? '', 'ShipLastName' =>$m4is_y368['lastname']?? '', 'ShipState' =>$m4is_y368['state2']?? '', 'ShipZip' =>$m4is_y368['postalcode2']?? '', 'MaestroIssueNumber' =>'', 'ShipMiddleName' =>'', ];
 $m4is_u807 =(int) m4is_c69807::m4is_z64('CreditCard', (int) $m4is_u807, $m4is_o30 );
 $m4is_y628 =(bool) ($m4is_u807 > 0 );
 
}if($m4is_u807 > 0 ){
self::$m4is_z59682->validateCard((int) $m4is_u807);
 
}delete_transient(m4is_c01675::m4is_c6341($m4is_h21895 ));
 if($m4is_u807 ){
 if($m4is_q10366 ){
$m4is_h3647 =['AutoCharge', 'CC1', 'ContactId', 'Id', 'MerchantAccountId', 'OriginatingOrderId', 'Status', 'SubscriptionPlanId', ];
 $m4is_v76912 =['ContactId' =>$m4is_h21895,   ];
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_m615 =m4is_c69807::m4is_i84('RecurringOrder', $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', false );
 $m4is_r03 =self::$m4is_r1546->m4is_j498('settings', 'merchant_account_id' );
 foreach($m4is_m615 as $m4is_g91703 ){
if(empty($m4is_l366 )||in_array($m4is_g91703['SubscriptionPlanId'], $m4is_l366 )){
$m4is_h023[$m4is_g91703['Id']]=['CC1' =>isset($m4is_g91703['CC1'])? $m4is_g91703['CC1']: 0, 'Id' =>isset($m4is_g91703['Id'])? $m4is_g91703['Id']: 0, 'MerchantAccountId' =>empty($m4is_g91703['MerchantAccountId'])? $m4is_r03 : $m4is_g91703['MerchantAccountId'], 'OriginatingOrderId' =>isset($m4is_g91703['OriginatingOrderId'])? $m4is_g91703['OriginatingOrderId']: 0, 'Status' =>isset($m4is_g91703['Status'])? $m4is_g91703['Status']: 0, 'ContactId' =>isset($m4is_g91703['ContactId'])? $m4is_g91703['ContactId']: 0, 'AutoCharge' =>isset($m4is_g91703['AutoCharge'])? $m4is_g91703['AutoCharge']: 0, ];
 
}
}unset($m4is_m615, $m4is_g91703);
  if(is_array($m4is_h023 )){
foreach ($m4is_h023 as $m4is_l9671 =>$m4is_y362 ){
$m4is_r08743 =false;
 $m4is_r08743 =$m4is_r08743 ||$m4is_y362['Status']!== 'Active';
 $m4is_r08743 =$m4is_r08743 ||$m4is_y362['CC1']== $m4is_u807;
 if(!$m4is_r08743 ){
$m4is_d07693 =(int) $m4is_y362['Id'];
 $m4is_e32607 =['CC1' =>(int) $m4is_u807, 'MerchantAccountId' =>(int) $m4is_y362['MerchantAccountId'],  'AutoCharge' =>1, ];
 m4is_c69807::m4is_z64('RecurringOrder', $m4is_d07693, $m4is_e32607 );
 
}
}
}unset($m4is_y362, $m4is_e32607, $m4is_h3647, $m4is_v76912, $m4is_l9671);
 
}  if($m4is_i462 ){
if($m4is_x36 )echo 'Starting Backcharge<br>';
 $m4is_r03 =self::$m4is_r1546->m4is_j498('settings', 'merchant_account_id' );
 $m4is_d01 =self::$m4is_r1546->m4is_v31259($m4is_h21895, false, true, true );
  $m4is_c76 =is_array($m4is_d01 )? count($m4is_d01 ): 0;
  $m4is_h3647 =['Id', 'JobRecurringId', ];
 $m4is_v76912 =['ContactId' =>(int) $m4is_h21895, ];
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_m615 =m4is_c69807::m4is_o986('Job', $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647 );
 $m4is_u4290 =[];
 if($m4is_i8169 ){
error_log('Memberium: [info] Credit Card Update - found ' . count($m4is_d01 ). ' orders' );
 
}foreach($m4is_m615 as $m4is_g91703 ){
$m4is_u4290[$m4is_g91703['Id']]=isset($m4is_g91703['JobRecurringId'])? $m4is_g91703['JobRecurringId']: 0;
 
}unset($m4is_m615, $m4is_g91703);
 $m4is_w89066 =[];
 $m4is_x38729 =count($m4is_u4290 );
 if($m4is_x36 ){
echo "Parameters: <pre>", print_r($m4is_u076, true ), "</pre>";
 echo "Default Merchant ID: {$m4is_r03
}<br>";
 echo "Unpaid Invoices Found: {$m4is_c76
}<br>";
 echo "Unpaid Jobs Found: {$m4is_x38729
}<br>";
 echo "Invoices: <pre>", print_r($m4is_d01, true ), "</pre>";
 echo "Jobs: <pre>", print_r($m4is_u4290, true ), "</pre>";
 
}if($m4is_c76 ){
foreach($m4is_d01 as $m4is_t06897 ){
if($m4is_i8169 ){

}if(($m4is_t06897['TotalPaid']< $m4is_t06897['TotalDue'])and ($m4is_t06897['PayStatus']== '0' )and ($m4is_t06897['TotalDue']> 0 )){
$m4is_k76462 =$m4is_t06897['Id'];
 $m4is_w269 =$m4is_t06897['JobId'];
 if(isset($m4is_u4290[$m4is_w269])){
$m4is_i53127 =$m4is_u4290[$m4is_w269];
 if(!empty($m4is_h023[$m4is_i53127])&&$m4is_h023[$m4is_i53127]['Status']== 'Active' ){
$m4is_w89066[$m4is_k76462]=['invoice_id' =>(int) $m4is_k76462, 'recurringorder_id' =>(int) $m4is_i53127, 'merchant_id' =>$m4is_h023[$m4is_i53127]['MerchantAccountId']? (int) $m4is_h023[$m4is_i53127]['MerchantAccountId']: 0, 'total_due' =>$m4is_t06897['TotalDue'], ];
 
}elseif(isset($m4is_u4290[$m4is_w269])&&$m4is_u4290[$m4is_w269]== 0){
$m4is_w89066[$m4is_k76462]=['invoice_id' =>(int) $m4is_k76462, 'recurringorder_id' =>0, 'merchant_id' =>$m4is_r03, 'total_due' =>$m4is_t06897['TotalDue'], ];
 
}
}
}
}
} usort($m4is_w89066, function($a, $b){
if($a['total_due']> $b['total_due'])return -1;
 if($a['total_due']< $b['total_due'])return 1;
 if($a['invoice_id']< $b['invoice_id'])return -1;
 if($a['invoice_id']> $b['invoice_id'])return 1;
 return 0;
 
});
 if($m4is_x36 ){
echo "Invoices: <pre>", print_r($m4is_d01, true ), "</pre>";
 echo "Items: <pre>", print_r($m4is_w89066, true ), "</pre>";
 
} foreach($m4is_w89066 as $m4is_c4069 ){
if($m4is_c4069['merchant_id']){
$m4is_u6591 =self::$m4is_z59682->chargeInvoice($m4is_c4069['invoice_id'], 'Updated Credit Card Back Charge', (int) $m4is_u807, $m4is_c4069['merchant_id'], false );
 if(is_string($m4is_u6591 )){
error_log("Memberium (" . __LINE__ . ' ' . __FUNCTION__ . "): [Warning] Error Charging Invoice ID {$m4is_c4069['invoice_id']
} - {$m4is_u6591
}" );
 
}
}else{
error_log("Memberium (" . __LINE__ . ' ' . __FUNCTION__ . "): [Warning] Error Charging Invoice ID {$m4is_c4069['invoice_id']
} - No Merchant ID" );
 
}
}
} 
}
}if($m4is_y628){
self::$m4is_r1546->m4is_u71903($m4is_u076['action_ids'], $m4is_h21895 );
 self::$m4is_r1546->m4is_t64038($m4is_u076['goals'], $m4is_h21895 );
 self::$m4is_r1546->m4is_k98($m4is_u076['tag_ids'], $m4is_h21895 );
 self::$m4is_r1546->m4is_i12($m4is_h21895 );
 self::$m4is_r1546->m4is_x4831($m4is_h21895 );
 m4is_c01675::m4is_a20694($m4is_h21895 );
 $m4is_f56 =$m4is_g14087;
 
}else{
$m4is_f56 =$m4is_t14956;
 
}if(!empty($m4is_f56 )){
wp_redirect($m4is_f56 );
 exit;
 
}
} public static 
function m4is_q36(){
if(!m4is_s52::m4is_w74()){
return;
 
}$m4is_r1546 =m4is_r83::m4is_c26();
 $m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 $m4is_f683 =m4is_f58::m4is_c26();
 $m4is_d913 =base64_decode($_POST['tag_id']);
 m4is_j586::m4is_x7134();
 if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['template_id']. $m4is_d913 )){
wp_die(_x('Security Check Failed - Signature Validation Error', 'memb_send_password', self::$m4is_f4218 ));
 exit;
 
}self::$m4is_f683->m4is_h185('There was an error Looking up that account.' );
 $m4is_r6249 =true;
 $m4is_m92735 =(int) $_POST['form_id'];
 $m4is_o809 =(int) $_POST['template_id'];
 $m4is_f4930 =strtolower(trim($_POST['email']));
 $m4is_i64253 =base64_decode($_POST['successurl']);
 $m4is_y36 =base64_decode($_POST['failureurl']);
 $m4is_d913 =base64_decode($_POST['tag_id']);
 $m4is_m60 =false;
 $m4is_d9204 =false;
 $m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field' );
 $m4is_r2369 =self::$m4is_r1546->m4is_j498('settings', 'local_auth_only' );
 if(trim($_POST['email'])== '' ){
self::$m4is_f683->m4is_h185('You must submit an email address.');
 return;
 
}$m4is_l17096 =get_user_by('email', $m4is_f4930);
 if($m4is_l17096){
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_l17096->ID);
 if(!$m4is_h21895 ){
$m4is_h21895 =m4is_p40::m4is_r804($m4is_f4930 );
 
}if($m4is_h21895){
$m4is_i935 =m4is_p40::m4is_p67($m4is_h21895 );
 
}
}if(empty($m4is_i935 )){
$m4is_h21895 =m4is_p40::m4is_r804($m4is_f4930);
 
}if(empty($m4is_r2369 )){
if($m4is_h21895 > 0){
if(isset($m4is_i935[$m4is_r6234])&&$m4is_i935[$m4is_r6234]== 'PASSWORD_PLACEHOLDER'){
$m4is_q6570 =self::$m4is_r1546->m4is_a601();
 $m4is_i935[$m4is_r6234]=$m4is_q6570;
 self::$m4is_r1546->m4is_v1694($m4is_i935);
 m4is_p40::m4is_x6560($m4is_h21895, [$m4is_r6234 =>$m4is_q6570]);
 self::$m4is_r1546->m4is_f605($m4is_h21895 );
 
}
}
}if($m4is_h21895 > 0 ){
if($m4is_d913 > 0 ){
self::$m4is_r1546->m4is_k98($m4is_d913, $m4is_h21895 );
 $m4is_m60 =true;
 $m4is_d9204 =true;
 
}
}if($m4is_o809){
if($m4is_h21895 > 0){
$m4is_t2657 =[$m4is_h21895];
 
}if(!empty($m4is_t2657)){
$m4is_c31 =$m4is_z59682->sendTemplate($m4is_t2657, $m4is_o809 );
 $m4is_m60 =true;
 
}
}else{
if(!$m4is_d9204 ){
if(!empty($m4is_i935 )){
$m4is_m676 =empty($m4is_i935[$m4is_r6234])? '' : $m4is_i935[$m4is_r6234];
 $m4is_p80796 =empty($m4is_i935['FirstName'])? '' : $m4is_i935['FirstName'];
 $m4is_q7924 =get_bloginfo('admin_email' );
 $m4is_v10 =$m4is_f4930;
 $m4is_j125 =sprintf(_x('Your Password for %s', 'memb_send_password', self::$m4is_f4218 ), get_bloginfo('name' ));
 $m4is_a173 =sprintf(_x("<html><body><p>Dear %s</p>\n\n<p>Your password is: %s\n\n</p><p>- %s\n</p></body></html>", 'memb_send_password', self::$m4is_f4218 ), $m4is_p80796, $m4is_m676, get_bloginfo('name'));
 $m4is_j125 =apply_filters('memberium/email/send_password/subject', $m4is_j125, $m4is_i935, $m4is_l17096 );
 $m4is_a173 =apply_filters('memberium/email/send_password/message', $m4is_a173, $m4is_i935, $m4is_m676, $m4is_l17096 );
 $m4is_v10 =apply_filters('memberium/email/send_password/to', $m4is_v10 );
 $m4is_q7924 =apply_filters('memberium/email/send_password/from', $m4is_q7924 );
 $m4is_m60 =true;
 $m4is_a97 =function(){
return 'text/html';
 
};
 add_filter('wp_mail_content_type', $m4is_a97 );
 wp_mail($m4is_v10, $m4is_j125, $m4is_a173 );
 remove_filter('wp_mail_content_type', $m4is_a97 );
 
}else{
self::$m4is_f683->m4is_h185('Account not found.');
 
}
}
}if($m4is_m60){
$m4is_f683->m4is_h185(_x('Your password has been emailed to you.  Please be sure to check your spam folder.', 'memb_send_password', self::$m4is_f4218 ));
 if(!empty($m4is_i64253)){
wp_redirect($m4is_i64253);
 exit;
 
}
}if(!$m4is_m60 ){
if(!empty($m4is_y36 )){
wp_redirect($m4is_y36 );
 exit;
 
}
}self::$m4is_r1546->m4is_i12();
 
} public static 
function m4is_h83(): void {
global $wpdb;
 $m4is_v80635 ='memb_update_form';
 if(!wp_verify_nonce($_POST['_wpnonce'], $_POST['params'])){
wp_die('Invalid Update Form Submission' );
 
}if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['params'])){
wp_die('Security Check Failed - Signature Validation Error' );
 exit;
 
}m4is_j586::m4is_x7134();
  $m4is_m5907 =$_POST;
 $m4is_l073 =[];
 $m4is_q847 =false;
 $m4is_b26 =false;
 $m4is_y4625 =true;
 $m4is_m60 =false;
 $m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field' );
 $m4is_p4935 =self::$m4is_r1546->m4is_j498('settings', 'username_field' );
 $m4is_u897 =unserialize(base64_decode($m4is_m5907['params']));
 $m4is_h21895 =(int) $m4is_u897['contact_id'];
 $m4is_x0645 =m4is_c69807::m4is_n5367(-1 );
 if(self::$m4is_r1546->m4is_z56()<> $m4is_h21895 ){
wp_die('Security Check Failed - Signature Validation Error' );
 exit;
 
} foreach($m4is_x0645 as $m4is_s36520 =>$m4is_u7420 ){
if(isset($m4is_m5907[$m4is_s36520])){
$m4is_v586 =$m4is_m5907[$m4is_s36520];
 if($m4is_u7420 == 5 ){
 if(strlen($m4is_v586 )< 4 ){
$m4is_v586 =strtoupper($m4is_v586 );
 
}
}elseif($m4is_u7420 == 13 ||$m4is_u7420 == 14 ){
 $m4is_q4296 =strtotime($m4is_v586 );
 if($m4is_q4296 > 0 ){
$m4is_v586 =date('Ymd\TH:i:s', strtotime($m4is_v586 ));
 
}else{
unset($m4is_m5907[$m4is_s36520]);
 
}
}elseif($m4is_u7420 == 17 ||$m4is_u7420 == 20 ||$m4is_u7420 == 23 ){
 if(is_array($m4is_v586)){
$m4is_v586 =implode(',', $m4is_v586 );
 
}
}elseif($m4is_u7420 == 19 ){
 if(strpos($m4is_v586, '@' )!== false ){
$m4is_v586 =strtolower(trim($m4is_v586 ));
 
}else{
$m4is_v586 ='';
 
}
}$m4is_m5907[$m4is_s36520]=$m4is_v586;
 
}
} if(!empty($m4is_u897['required_fields'])){
$m4is_g7928 =array_filter(explode(',', $m4is_u897['required_fields']));
 if(is_array($m4is_g7928 )){
foreach ($m4is_g7928 as $m4is_v273 ){
if(!isset($m4is_m5907[$m4is_v273])){
$m4is_y4625 =false;
 $m4is_q847 =true;
 $m4is_l073[]='You are missing required fields.';
 
}
}
}
} foreach ($m4is_m5907 as $m4is_l9671 =>$m4is_v586){
if(isset($m4is_m5907[$m4is_l9671 . '_confirmation'])&&$m4is_m5907[$m4is_l9671]<> $m4is_m5907[$m4is_l9671 . '_confirmation']){
$m4is_q847 =true;
 $m4is_l073[]='Your confirmed fields do not match.';
 
}
} if(!empty($m4is_u897['date_fields'])){
$m4is_w82 =explode(',', $m4is_u897['date_fields']);
 if(is_array($m4is_w82 )){
foreach($m4is_w82 as $m4is_x036 ){
if(isset($m4is_m5907[$m4is_x036])){
$m4is_q4296 =strtotime($m4is_m5907[$m4is_x036]);
 $m4is_m5907[$m4is_x036]=date('Ymd\TH:i:s', $m4is_q4296);
 
}
}
}
} if(!$m4is_q847){
$m4is_t27891 =m4is_c69807::m4is_f5248('Contact');
 $m4is_i935 =[];
 foreach($m4is_m5907 as $m4is_l9671 =>$m4is_v586 ){
if(in_array($m4is_l9671, $m4is_t27891 )){
$m4is_i935[$m4is_l9671]=stripslashes($m4is_v586 );
 
}
}if(!empty($m4is_i935)){
m4is_p40::m4is_x6560($m4is_h21895, $m4is_i935 );
  if($m4is_h21895 > 0){
if(!empty($m4is_u897['goal'])){
self::$m4is_r1546->m4is_t64038($m4is_u897['goal'], $m4is_h21895);
 
}if(!empty($m4is_u897['tagids'])){
self::$m4is_r1546->m4is_k98($m4is_u897['tagids'], $m4is_h21895);
 
}self::$m4is_r1546->m4is_x4831($m4is_h21895);
 m4is_q82::m4is_u687(self::$m4is_r1546->m4is_x66());
   $m4is_f6029 =m4is_s695::m4is_e654(m4is_s695::CONTACT_FIELDS, m4is_s695::EMAIL_TYPE);
 $m4is_f6029[]='Email';
 $m4is_f6029[]='EmailAddress2';
 $m4is_f6029[]='EmailAddress3';
 $m4is_x95 =[];
 foreach($m4is_i935 as $m4is_r637 =>$m4is_w86){
if(in_array($m4is_r637, $m4is_f6029)){
if(!empty($m4is_w86)){
$m4is_x95[]=strtolower(trim($m4is_w86));
 
}
}
}$m4is_x95 =array_unique($m4is_x95);
 foreach($m4is_x95 as $m4is_f4930){
m4is_p40::m4is_y935($m4is_f4930, 'Memberium Update Form');
 
}
}else{
$m4is_q847 =true;
 
}
}
} if($m4is_q847 ||$m4is_b26){
$m4is_f56 =$m4is_u897['failure_url'];
 
}else{
$m4is_f56 =$m4is_u897['success_url'];
 
}  if(!empty($m4is_i935)&&$m4is_u897['pass_fields']){
$m4is_z903 =(strpos($m4is_f56, '?')=== false)? '?' : '&';
 if($m4is_h21895 > 0){
$m4is_z903 .= 'id=' . $m4is_h21895 . '&';
 
}foreach ($m4is_i935 as $m4is_l9671 =>$m4is_v586){
if($m4is_l9671 <> $m4is_r6234 ||$m4is_u897['pass_password']== true){
$m4is_z903 .= $m4is_l9671 . '=' . urlencode($m4is_v586). '&';
 
}
}
}$m4is_f56 =trim($m4is_f56 . $m4is_z903, '&');
  if(!empty($m4is_l073)){
$_SESSION['error_message']=$m4is_l073;
 
}if(!empty($m4is_f56)){
session_write_close();
 wp_redirect($m4is_f56, 302, 'Memberium Update Form Redirect' );
 exit;
 
}
} public static 
function m4is_v08392(){
$m4is_r1546 =m4is_r83::m4is_c26();
 $m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 if(!m4is_s52::m4is_w74()){
return;
 
}if(!self::$m4is_r1546->m4is_z56()){
return;
 
}m4is_j586::m4is_x7134();
 if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['parameters'])){
wp_die(_x('Security Check Failed - Signature Validation Error', 'memb_list_invoices', self::$m4is_f4218 ));
 exit;
 
}$m4is_u076 =unserialize(base64_decode($_POST['parameters']));
 $m4is_l97604 =(int) $m4is_u076['invoice_id'];
 if(!wp_verify_nonce($_POST['_wpnonce'], 'memb_payinvoice_' . $m4is_l97604)){
wp_die(_x('Security Check Failed - Nonce Validation Error', 'memb_list_invoices', self::$m4is_f4218 ));
 exit;
 
}$m4is_h21895 =(int) self::$m4is_r1546->m4is_z56();
 $m4is_u807 =(int) $_POST['creditcard_id'];
 $m4is_l97604 =(int) $m4is_u076['invoice_id'];
 $m4is_e806 =(int) $m4is_u076['merchant_id'];
 $m4is_d913 =$m4is_u076['tag_id'];
 $m4is_v8723 =$m4is_u076['goal'];
 $m4is_i7193 ='';
 $m4is_d01 =self::$m4is_r1546->m4is_v31259($m4is_h21895, false, true);
 $m4is_t06897 =isset($m4is_d01[$m4is_l97604])? $m4is_d01[$m4is_l97604]: [];
 $m4is_g56631 =['pay_action' =>'', 'pay_goal' =>'', ];
 $m4is_u6591 =$m4is_z59682->chargeInvoice($m4is_l97604, _x('Memberium Customer Care', 'memb_list_invoices', self::$m4is_f4218 ), $m4is_u807, $m4is_e806, false);
 if(isset($m4is_u6591['Successful'])&&$m4is_u6591['Successful']){
$m4is_k17856 =self::$m4is_r1546->m4is_f66295((int) $m4is_t06897['JobId']);
 $m4is_l4691 =self::$m4is_r1546->m4is_j498('ecommerce', 'actions');
 foreach($m4is_k17856 as $m4is_d07693 =>$m4is_l13){
$m4is_j15 =isset($m4is_l4691[$m4is_l13['SubscriptionPlanId']])? $m4is_l4691[$m4is_l13['SubscriptionPlanId']]: false;
 if($m4is_j15){
$m4is_g56631['pay_action'].= empty($m4is_j15['pay_action'])? '' : ',' . $m4is_j15['pay_action'];
 $m4is_g56631['pay_goal'].= empty($m4is_j15['pay_goal'])? '' : ',' . $m4is_j15['pay_goal'];
 
}
}foreach($m4is_g56631 as $m4is_o015 =>$m4is_k72){
$m4is_g56631[$m4is_o015]=trim($m4is_g56631[$m4is_o015], ',');
 
}$m4is_i7193 =trim($m4is_i7193 . ',' . $m4is_g56631['pay_action'], ',');
 $m4is_v8723 =trim($m4is_v8723 . ',' . $m4is_g56631['pay_goal'], ',');
 self::$m4is_r1546->m4is_k98($m4is_d913, $m4is_h21895);
 self::$m4is_r1546->m4is_u71903($m4is_i7193, $m4is_h21895);
 self::$m4is_r1546->m4is_t64038($m4is_v8723, $m4is_h21895);
  $_SESSION['flash']['invoice_payment']=$m4is_u076['success_msg'];
  delete_transient('Memberium::' . self::$m4is_r9613 . '::Invoices::' . $m4is_h21895);
 delete_transient('Memberium::' . self::$m4is_r9613 . '::PayPlans::' . $m4is_h21895);
 delete_transient('Memberium::' . self::$m4is_r9613 . '::PayPlanItems::' . $m4is_h21895);
 wp_redirect($m4is_u076['redirect_url']);
 exit;
 
}else{
$_SESSION['flash']['invoice_payment']=_x('Payment failed.', 'memb_list_invoices', self::$m4is_f4218 );
 
}
} public static 
function m4is_u041(){
if(!m4is_s52::m4is_w74()){
return;
 
}if(!wp_verify_nonce($_POST['_wpnonce'], 'oneclick_sale_' . $_POST['form_id'])){
wp_die(_x('Security Check Failed - Nonce Validation Error', 'memb_one_click_sale', self::$m4is_f4218 ));
 exit;
 
}if(!self::$m4is_r1546->m4is_f1072($_POST['digital_signature'], $_POST['parameters'])){
wp_die(_x('Security Check Failed - Signature Validation Error', 'memb_one_click_sale', self::$m4is_f4218 ));
 exit;
 
}m4is_j586::m4is_x7134();
 $m4is_u076 =unserialize(base64_decode($_POST['parameters']));
 $m4is_n86039 =$m4is_u076['action_id'];
 $m4is_h21895 =self::$m4is_r1546->m4is_z56();
 $m4is_c6016 =(int) $m4is_u076['order_id'];
 if($m4is_n86039 > ''){
self::$m4is_r1546->m4is_u71903($m4is_n86039, $m4is_h21895);
 
}
} public static 
function m4is_s82596(){
$m4is_v80635 ='memb_change_email';
 $m4is_f087 =(int) self::$m4is_r1546->m4is_x66();
 if(!wp_verify_nonce($_POST['_wpnonce'], 'memb_email_change_' . $_POST['form_id'])){
wp_die(_x('Security Check Failed - Nonce Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 exit;
 
}if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['actions'])){
wp_die(_x('Security Check Failed - Signature Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 exit;
 
}m4is_j586::m4is_x7134();
 if($_POST['email1']<> $_POST['email2']){
$_SESSION['memb_flash']['email_change_message']='<p>' . _x("Your new email addresses don't match.", 'memb_change_email', self::$m4is_f4218 ). '</p>';
 return;
 
}if($_POST['email1']== m4is_q82::m4is_k660($m4is_f087, 'contact', 'email' )){
$_SESSION['memb_flash']['email_change_message']='<p>' . _x('Your email address is already ', 'memb_change_email', self::$m4is_f4218 ). $_POST['email1']. '</p>';
 return;
 
}$_POST['email1']=stripslashes($_POST['email1']);
 $m4is_j15 =unserialize(base64_decode($_POST['actions']));
 $m4is_q03616 =get_user_by('email', $_POST['email1']);
 if($m4is_q03616){
$_SESSION['memb_flash']['email_change_message']='<p>' . _x('That email address is already used on another member.', 'memb_change_email', self::$m4is_f4218 ). '</p>';
 return;
 
} $m4is_h21895 =m4is_p40::m4is_r804($_POST['email1']);
 if(!empty($m4is_h21895 )){
$_SESSION['memb_flash']['email_change_message']='<p>' . _x('That email address is already on our list.', 'memb_change_email', self::$m4is_f4218 ). '</p>';
 return;
 
}$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 $m4is_u6591 =false;
 if($m4is_h21895 ){
$m4is_u6591 =self::$m4is_r1546->m4is_x6461($m4is_f087, $_POST['email1'], $m4is_h21895 );
  
}if($m4is_u6591){
$_SESSION['memb_flash']['email_change_message']='<p>' . _x('Your email address has been changed to', 'memb_change_email', self::$m4is_f4218 ). ' ' . $_POST['email1']. '</p>';
  if(!empty($m4is_j15['actionset_id'])){
self::$m4is_r1546->m4is_u71903($m4is_j15['actionset_id']);
 
}if(!empty($m4is_j15['goal'])){
self::$m4is_r1546->m4is_t64038($m4is_j15['goal']);
 
}self::$m4is_r1546->m4is_i80956();
 wp_clear_auth_cookie();
 m4is_q82::m4is_u687($m4is_f087 );
 wp_set_current_user($m4is_f087 );
 wp_set_auth_cookie($m4is_f087, true, false);
 if(!empty($m4is_j15['success_url'])){
wp_redirect($m4is_j15['success_url']);
 exit;
 
}if(!empty($_SERVER['REQUEST_URI'])){
wp_redirect($_SERVER['REQUEST_URI']);
 exit;
 
}return;
 
}else{
$_SESSION['flash']['email_change_message']='<p>Your email address could not be changed to ' . $_POST['email1']. '</p>';
 if(!empty($m4is_j15['failure_url'])){
wp_redirect($m4is_j15['failure_url']);
 exit;
 
}
}
} public static 
function m4is_j98462(){
if(self::$m4is_n1028){
return;
 
}if(!is_user_logged_in()){
return;
 
}if(!wp_verify_nonce($_POST['_wpnonce'], 'password_change_' . $_POST['form_id']. $_POST['parameters'])){
wp_die(_x('Security Check Failed - Nonce Validation Error', 'memb_change_password', self::$m4is_f4218 ));
 exit;
 
}if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['parameters'])){
wp_die(_x('Security Check Failed - Signature Validation Error', 'memb_change_password', self::$m4is_f4218 ));
 exit;
 
}m4is_j586::m4is_x7134();
 $m4is_s259 ='memb_change_password';
 $m4is_r6249 =true;
 $m4is_f683 =m4is_f58::m4is_c26();
 $m4is_f4863 =self::$m4is_r1546->m4is_j498('settings', 'min_password_length' );
 $m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_u076 =unserialize(base64_decode($_POST['parameters']));
 $m4is_m676 =stripslashes($_POST['password1']);
 $m4is_m92735 =isset($_POST['form_id'])? (int) $_POST['form_id']: 0;
 $m4is_v8723 =isset($m4is_u076['goal'])? $m4is_u076['goal']: '';
 $m4is_f2937 =isset($m4is_u076['tagids'])? $m4is_u076['tagids']: '';
 $m4is_w64602 =isset($m4is_u076['actionset_id'])? $m4is_u076['actionset_id']: '';
 $m4is_f56 =isset($m4is_u076['redirect_url'])? $m4is_u076['redirect_url']: '';
 $m4is_u724 =isset($m4is_u076['confirm_template_id'])? (int) $m4is_u076['confirm_template_id']: 0;
 $m4is_i64253 =isset($m4is_u076['successurl'])? $m4is_u076['successurl']: '';
 $m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if(!$m4is_u076['contact_id']){
$m4is_f683->m4is_h185(_x('You are not an Keap User.', $m4is_s259, self::$m4is_f4218 ));
 return;
 
}if(empty($m4is_m676 )){
$m4is_f683->m4is_h185(_x('You cannot set your password to an Empty Password.', $m4is_s259, self::$m4is_f4218 ));
 return;
 
}if(strpos($m4is_m676, '\\' )!== false ){
$m4is_f683->m4is_h185(_x('The backslash character may not be used in passwords.', $m4is_s259, self::$m4is_f4218 ));
 return;
 
}if(strlen($m4is_m676)< $m4is_f4863){
$m4is_f683->m4is_h185(sprintf(_x('Your password must be at least %d characters long.', $m4is_s259, self::$m4is_f4218 ), $m4is_f4863));
 return;
 
}if(!empty($m4is_m676 )&&$m4is_m676 != $_POST['password2']){
$m4is_f683->m4is_h185(_x('The passwords entered do not match.', $m4is_s259, self::$m4is_f4218 ));
 return;
 
}$m4is_f56 =empty($m4is_f56 )? $_SERVER['REQUEST_URI']: $m4is_f56;
 $m4is_m60 =self::$m4is_r1546->m4is_a58($m4is_m676 );
 if($m4is_m60 ){
$m4is_f683->m4is_h185(_x('Password Changed Successfully.', $m4is_s259, self::$m4is_f4218 ));
 self::$m4is_r1546->m4is_k98($m4is_f2937 );
 self::$m4is_r1546->m4is_u71903($m4is_w64602 );
 self::$m4is_r1546->m4is_t64038($m4is_v8723 );
 $m4is_f56 =empty($m4is_i64253 )? $m4is_f56 : $m4is_i64253;
 $m4is_f56 =remove_query_arg(['passwordchange'], $m4is_f56 );
 $m4is_f56 =add_query_arg(['passwordchange' =>'success'], $m4is_f56 );
 if($m4is_u724 > 0 ){
self::$m4is_z59682->sendTemplate([$m4is_h21895], $m4is_u724 );
 
}if(!empty($m4is_f56 )){
session_write_close();
 wp_redirect($m4is_f56, 302, 'Memberium Password Change Redirect' );
 exit;
 
}
}else{
self::$m4is_f683->m4is_h185(_x('Password Change Failed.', $m4is_s259, self::$m4is_f4218 ));
 $m4is_f56 =add_query_arg(['passwordchange' =>'failed'], $m4is_f56 );
 
}session_write_close();
 nocache_headers();
 if(!empty($m4is_f56 )){
wp_redirect($m4is_f56, 302, 'Memberium Password Change Redirect' );
 exit;
 
}
} public static 
function m4is_j5928(){
if(self::$m4is_n1028 ){
return;
 
}m4is_j586::m4is_x7134();
 if(is_user_logged_in()){
return;
 
}$m4is_f4930 =sanitize_email(trim(wp_unslash($_POST['user_login'])));
 if(empty($m4is_f4930 )){
return;
 
}$m4is_l17096 =self::$m4is_r1546->m4is_q93($m4is_f4930 );
 if(!is_a($m4is_l17096, 'WP_User' )){
return;
 
}$m4is_l9671 =get_password_reset_key($m4is_l17096 );
 $m4is_g928 =$m4is_l17096->user_login;
 $m4is_n6062 =site_url('wp-login.php?action=rp&key=' . rawurlencode($m4is_l9671 ). '&login=' . rawurlencode($m4is_g928 ));
 $m4is_n62638 =is_multisite()? get_network()->site_name : wp_specialchars_decode(get_option('blogname' ), ENT_QUOTES );
 $m4is_w915 =sprintf(__('Password Reset for %s' ), $m4is_n62638 );
 $m4is_a173 =__('Someone has requested a password reset for the following account:' ). "\r\n\r\n";
 $m4is_a173 .= sprintf(__('Site Name: %s' ), $m4is_n62638 ). "\r\n\r\n";
 $m4is_a173 .= sprintf(__('Username: %s' ), $m4is_f4930 ). "\r\n\r\n";
 $m4is_a173 .= __('If you did not request a new password, just ignore this email and nothing will happen.' ). "\r\n\r\n";
 $m4is_a173 .= __('To reset your password, please click the link below:'). "\r\n\r\n";
 $m4is_a173 .= $m4is_n6062 . "\r\n";
 $m4is_w915 =apply_filters('retrieve_password_title', $m4is_w915, $m4is_l17096->user_login, $m4is_l17096 );
 $m4is_a173 =apply_filters('retrieve_password_message', $m4is_a173, $m4is_l9671, $m4is_l17096->user_login, $m4is_l17096 );
 if($m4is_a173 &&!wp_mail($m4is_f4930, wp_specialchars_decode($m4is_w915), $m4is_a173 )){
wp_die(__('The lost password email could not be sent.' ). "<br />\n" );
 
}
} public static 
function m4is_j48796(): void {
$m4is_v80635 ='memb_registration_form';
 if(!wp_verify_nonce($_POST['_wpnonce'], $_POST['params'])){
wp_die(_x('Invalid Registration Submission', $m4is_v80635, self::$m4is_f4218 ));
 
}if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['params'])){
wp_die(_x('Security Check Failed - Signature Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 
}m4is_j586::m4is_x7134();
 global $wpdb;
  $m4is_j108 =['_wp_http_referer', '_wpnonce', 'memb_form_type', 'params', 'signature' ];
 $m4is_q847 =false;
 $m4is_l073 =[];
 $m4is_b26 =false;
 $m4is_y4625 =true;
 $m4is_m60 =false;
 $m4is_j4768 =[];
 $m4is_y660 =false;
 $m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field' );
 $m4is_f4863 =self::$m4is_r1546->m4is_j498('settings', 'min_password_length' );
 $m4is_p4935 =self::$m4is_r1546->m4is_j498('settings', 'username_field' );
 $m4is_r2369 =self::$m4is_r1546->m4is_j498('settings', 'local_auth_only' );
 $m4is_l34968 =self::$m4is_r1546->m4is_j498('settings', 'recaptcha_v2' );
 $m4is_e86209 =self::$m4is_r1546->m4is_j498('settings', 'recaptcha_v2_secret_key', '' );
 $m4is_m5907 =apply_filters('memberium/registration_form/post/pre', $_POST );
 $m4is_u897 =unserialize(base64_decode($m4is_m5907['params']));
 $m4is_w82 =array_filter(explode(',', $m4is_u897['date_fields']));
 $m4is_g7928 =array_filter(explode(',', $m4is_u897['required_fields']));
 $m4is_v90148 =!$m4is_r2369;
 $m4is_y34107 =$m4is_r6234 . '_confirmation';
  if($m4is_u897['remove_accents']){
foreach($m4is_m5907 as $m4is_o015=>$m4is_k72 ){
$m4is_m5907[$m4is_o015]=trim(remove_accents($m4is_k72 ));
 
}
} if($m4is_u897['recaptcha']&&isset($m4is_m5907['g-recaptcha-response'])&&$m4is_l34968 ){
$m4is_d67910 =$m4is_m5907['g-recaptcha-response'];
 $m4is_n6062 =sprintf('https://www.google.com/recaptcha/api/siteverify?secret=%s&response=%s', $m4is_e86209, $m4is_d67910 );
 $m4is_n548 =wp_remote_get($m4is_n6062 );
 $m4is_n548 =wp_remote_retrieve_body($m4is_n548 );
 $m4is_t52173 =json_decode($m4is_n548 );
 if(!$m4is_t52173->success ){
$m4is_q847 =true;
 $m4is_l073[]=_x('You need to successfully pass the CAPTCHA Test to register.', 'memb_registration_form', self::$m4is_f4218 );
 
}
} if(!isset($m4is_m5907[$m4is_r6234])&&!isset($m4is_m5907[$m4is_y34107])){
$m4is_m5907[$m4is_y34107]=$m4is_m5907[$m4is_r6234]=self::$m4is_r1546->m4is_a601();
 $m4is_v90148 =true;
 $m4is_y660 =true;
 
} if(is_array($m4is_g7928 )){
foreach ($m4is_g7928 as $m4is_v273 ){
if(!isset($m4is_m5907[$m4is_v273])){
$m4is_y4625 =false;
 $m4is_q847 =true;
 $m4is_l073[]=_x('You are missing required fields.', 'memb_registration_form', self::$m4is_f4218 );
 
}
}
} if(!empty($m4is_m5907[$m4is_r6234])&&strlen($m4is_m5907[$m4is_r6234])< $m4is_f4863 ){
$m4is_q847 =true;
 $m4is_l073[]=sprintf(_x('Your password must be at least %d characters long.', 'memb_registration_form', self::$m4is_f4218 ), $m4is_f4863 );
 
} if(strpos($m4is_m5907[$m4is_r6234], '\\' )!== false ){
$m4is_q847 =true;
 $m4is_l073[]=_x('You may not use the backslash (\\) character in passwords.', 'memb_registration_form', self::$m4is_f4218 );
 
} if(is_array($m4is_m5907 )){
foreach ($m4is_m5907 as $m4is_l9671 =>$m4is_v586 ){
$m4is_h42516 =$m4is_l9671 . '_confirmation';
 if(isset($m4is_m5907[$m4is_h42516])&&$m4is_m5907[$m4is_l9671]<> $m4is_m5907[$m4is_h42516]){
$m4is_q847 =true;
 $m4is_l073[]=_x('Your confirmed fields do not match.', 'memb_registration_form', self::$m4is_f4218 );
 
}
}unset($m4is_l9671, $m4is_v586, $m4is_u263 );
 
}$m4is_m5907[$m4is_p4935]=stripslashes($m4is_m5907[$m4is_p4935]);
 $m4is_b26 =self::m4is_r08($m4is_m5907[$m4is_p4935]);
  if(!$m4is_b26 ){
$m4is_h3647 =[$m4is_r6234, 'Groups' ];
 $m4is_v76912 =[$m4is_p4935 =>$m4is_m5907[$m4is_p4935]];
 $m4is_z640 =m4is_c69807::m4is_o986('Contact', 1000, 0, $m4is_v76912, $m4is_h3647 );
 if(is_array($m4is_z640 )){
foreach ($m4is_z640 as $m4is_i935 ){
if(!empty($m4is_i935[$m4is_r6234])){
$m4is_b26 =true;
 
}
}
}
} if(!$m4is_b26 &&$m4is_p4935 <> 'Email' &&isset($m4is_m5907['Email'])){
$m4is_h3647 =[$m4is_r6234, 'Groups', 'Id', 'Email' ];
 $m4is_v76912 =['Email' =>$m4is_m5907[$m4is_p4935]];
 $m4is_z640 =m4is_c69807::m4is_o986('Contact', 1000, 0, $m4is_v76912, $m4is_h3647 );
 if(is_array($m4is_z640 )){
foreach ($m4is_z640 as $m4is_i935 ){
if($m4is_i935[$m4is_r6234]> '' ){
$m4is_b26 =true;
 
}
} 
}
} if($m4is_b26 ){
$m4is_q847 =true;
 $m4is_l073[]=_x('That username is not available.', 'memb_registration_form', self::$m4is_f4218 );
 
} if(!$m4is_q847 &&!$m4is_b26 ){
if(empty($m4is_m5907[$m4is_r6234])){
$m4is_m5907[$m4is_r6234]=self::$m4is_r1546->m4is_a601();
 $m4is_y660 =true;
 $m4is_v90148 =true;
 
}$m4is_t27891 =m4is_c69807::m4is_f5248('Contact', true);
 $m4is_i935 =[];
 foreach ($m4is_m5907 as $m4is_l9671 =>$m4is_v586 ){
if(in_array($m4is_l9671, $m4is_w82 )){
$m4is_v586 =date('Ymd\Th:i:s', strtotime($m4is_v586 ));
 
}if(in_array($m4is_l9671, $m4is_t27891 )){
$m4is_i935[$m4is_l9671]=$m4is_v586;
 
}
} if($m4is_r2369 == true &&$m4is_v90148 == false ){
unset($m4is_i935[$m4is_r6234]);
 
}if($m4is_u897['pass_password']== false ){
$m4is_j108[]=$m4is_r6234;
 $m4is_j108[]=$m4is_y34107;
 
} if(is_array($m4is_i935 )&&!empty($m4is_i935 )&&$m4is_u897['pass_fields']){
foreach ($m4is_i935 as $m4is_l9671 =>$m4is_v586 ){
if(!in_array($m4is_l9671, $m4is_j108 )){
$m4is_j67631 ='';
 if($m4is_u897['inf_fields']){
$m4is_l9671 =substr($m4is_l9671, 0, 1 )== '_' ? 'inf_custom' . $m4is_l9671 : 'inf_field_' . $m4is_l9671;
 
}$m4is_j4768[$m4is_l9671]=urlencode($m4is_v586 );
 
}
}foreach ($m4is_m5907 as $m4is_l9671 =>$m4is_v586 ){
if(!isset($m4is_i935[$m4is_l9671])){
if(!in_array($m4is_l9671, $m4is_j108 )){
$m4is_j4768[$m4is_l9671]=urlencode($m4is_v586 );
 
}
}
}
} if(!empty($m4is_i935 )){
 $m4is_h21895 =m4is_p40::m4is_k82670($m4is_i935 );
 $m4is_e70689 =_x('Membership Signup New User', 'memb_registration_form', self::$m4is_f4218 );
 m4is_p40::m4is_x6560($m4is_h21895, $m4is_i935 );
 $m4is_w65083 =['Email', 'EmailAddress2', 'EmailAddress3'];
 foreach($m4is_w65083 as $m4is_m23 ){
if(!empty($m4is_i935[$m4is_m23])){
$m4is_i935[$m4is_m23]=sanitize_email($m4is_i935[$m4is_m23]);
 m4is_p40::m4is_y935($m4is_i935[$m4is_m23], $m4is_e70689 );
 
}
}if($m4is_h21895 ){
self::$m4is_r1546->m4is_t64038($m4is_u897['goal'], $m4is_h21895 );
 self::$m4is_r1546->m4is_k98($m4is_u897['tagids'], $m4is_h21895 );
 self::$m4is_r1546->m4is_u71903($m4is_u897['action_id'], $m4is_h21895 );
 $rows =self::$m4is_r1546->m4is_x4831($m4is_h21895 );
 $user_id =self::$m4is_r1546->m4is_f605($m4is_h21895, $m4is_m5907[$m4is_r6234]);
  if($m4is_u897['autologin']){
$m4is_l17096 =get_user_by('id', $user_id );
 $m4is_v64 =$m4is_i935[$m4is_p4935];
 if(!empty($m4is_u897['success_url'])){
self::$m4is_f683->m4is_w6289(add_query_arg($m4is_j4768, $m4is_u897['success_url']));
 
}wp_set_auth_cookie($user_id );
 wp_set_current_user($user_id );
 m4is_l5841::m4is_e40($m4is_v64 );
 do_action('wp_login', $m4is_v64, $m4is_l17096 );
 self::$m4is_f683->m4is_g38965($m4is_i935, $user_id );
 m4is_q82::m4is_d59($user_id );
 
}
}else{
$m4is_q847 =true;
 
}
}
} if($m4is_q847 ||$m4is_b26 ){
$m4is_f56 =$m4is_u897['failure_url'];
 
}else{
$m4is_f56 =$m4is_u897['success_url'];
 
} if(!empty($m4is_l073 )){
$_SESSION['error_message']=$m4is_l073;
 
} if(!empty($m4is_f56 )){
$m4is_f56 =add_query_arg($m4is_j4768, $m4is_f56 );
 wp_redirect($m4is_f56 );
 exit;
 
}
} public static 
function m4is_x366(): void {
if(self::$m4is_n1028){
return;
 
}if(!self::$m4is_r1546->m4is_z56()){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_u076 =unserialize(base64_decode($_POST['parameters']));
 $m4is_h21895 =(int) $m4is_u076['contact_id']?? 0;
 $m4is_v80635 ='memb_add_paymentmethod_' . $m4is_h21895;
  if(!wp_verify_nonce($_POST['_wpnonce'], 'add_paymentmethod_' . $m4is_h21895 )){
wp_die(_x('Security Check Failed - Nonce Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 
}if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['parameters'])){
wp_die(_x('Security Check Failed - Signature Validation Error', $m4is_v80635, self::$m4is_f4218 ));
 
}$m4is_j668 =(int) $_POST['payment_method_id']?? 0;
 $m4is_c537 =(int) $_POST['creditcard_id']?? 0;
 $m4is_i462 =(bool) $m4is_u076['backcharge'];
 $m4is_n548 =json_decode(stripslashes(trim($_POST['payment_response']?? '' )));
 $m4is_d47 =(bool) $m4is_u076['logging'];
 $m4is_i8169 =(bool) $m4is_u076['debug'];
 $m4is_n246 =(bool) $m4is_u076['default'];
 $m4is_f0326 =sanitize_text_field($_POST['payment_message']?? '' );
 $m4is_d913 =(int) $m4is_u076['tag_id'];
 $m4is_l366 =array_filter(explode(',', $m4is_u076['plan_ids']));
 $m4is_q37596 =trim($m4is_u076['redirect']?? '' );
 $m4is_z47 =trim($m4is_u076['failure']?? '' );
 $m4is_q37596 =$m4is_q37596 ?: $_SERVER['HTTP_REFERER'];
 $m4is_z47 =$m4is_z47 ?: $_SERVER['HTTP_REFERER'];
  if($m4is_i8169 ){
echo '<p>Add Payment Method Debug</p>';
 echo '<p><strong>Shortcode Settings:</strong></p>';
 echo '<p>Backcharge:  ', (int) $m4is_i462, '</p>';
 echo '<p>Debug:  ', (int) $m4is_i8169, '</p>';
 echo '<p>Default:  ', (int) $m4is_n246, '</p>';
 echo '<p>Tag ID:  ', esc_html($m4is_d913 ), '</p>';
 echo '<p>Plan IDs:  ', esc_html(implode(', ', $m4is_l366 )), '</p>';
 echo '<p>Success Redirect:  ', esc_html($m4is_q37596 ), '</p>';
 echo '<p>Failure Redirect:  ', esc_html($m4is_z47 ), '</p>';
 echo '<p><strong>New Card Results from Keap:</strong></p>';
 echo '<p>Payment Method ID:  ', (int) $m4is_j668, '</p>';
 echo '<p>New Card ID:  ', (int) $m4is_c537, '</p>';
 echo '<p>New Card Message:  ', esc_html($m4is_f0326 ), '</p>';
 echo '<p>Success:  ', print_r(esc_html($m4is_n548->success ?? 0 ), true ), '</p>';
 echo '<p><strong>Raw Data:</strong></p>';
 echo '<pre>Form Response:  ', print_r($_POST, true ), '</pre>';
 echo '<p>Raw Keap Response:  ', print_r($m4is_n548, true ), '</p>';
 
}if(!$m4is_c537 ){
if($m4is_i8169 ){
echo '<p>No Credit Card Added.</p>';
 die();
 
}if(!headers_sent()){
$m4is_z47 =add_query_arg(['error' =>'CARD_NOT_ADDED'], $m4is_z47 );
 wp_redirect($m4is_z47, 302, 'Add Payment Method failed.  Card not added.' );
 exit;
 
}return;
 
} m4is_c01675::m4is_j6029($m4is_h21895 );
  if($m4is_c537 ){
$m4is_n451 =self::m4is_z14836($m4is_h21895, $m4is_l366 );
 self::m4is_g56($m4is_n451, $m4is_c537 );
 if($m4is_i462 ){
$m4is_n451 =self::m4is_z14836($m4is_h21895, $m4is_l366 );
 self::m4is_s02938($m4is_h21895, $m4is_c537, $m4is_n451, $m4is_d47, $m4is_i8169 );
 
}
} if($m4is_d913 ){
self::$m4is_r1546->m4is_k98($m4is_d913, $m4is_h21895 );
 
}self::$m4is_r1546->m4is_i12($m4is_h21895 );
 self::$m4is_r1546->m4is_x4831($m4is_h21895 );
 if($m4is_i8169 ){
echo '<p>Finished processing.</p>';
 die();
 
}if(!headers_sent()){
wp_redirect($m4is_q37596, 302, 'Add Payment Method Redirect' );
 exit;
 
}
}    private static 
function m4is_r08(string $m4is_f4930 ): bool {
global $wpdb;
 $m4is_f4930 =sanitize_email($m4is_f4930 );
 $m4is_v2613 ="SELECT COUNT(*) FROM `{$wpdb->users
}` WHERE `user_login` = %s OR `user_email` = %s";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_f4930, $m4is_f4930 );
 $m4is_h973 =$wpdb->get_col($m4is_v2613, 0 );
 return (boolean) array_shift($m4is_h973 );
 
} private static 
function m4is_z14836(int $m4is_h21895, array $m4is_l366 =[]): array {
$m4is_h3647 =['AutoCharge', 'CC1', 'ContactId', 'Id', 'MerchantAccountId', 'OriginatingOrderId', 'Status', 'SubscriptionPlanId', ];
 $m4is_v76912 =['ContactId' =>$m4is_h21895, 'AutoCharge' =>'1', 'Status' =>'%',  ];
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_h023 =[];
 $m4is_m615 =m4is_c69807::m4is_i84('RecurringOrder', $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', false );
 $m4is_r03 =self::$m4is_r1546->m4is_j498('settings', 'merchant_account_id' );
 foreach($m4is_m615 as $m4is_g91703 ){
if(empty($m4is_l366 )||in_array($m4is_g91703['SubscriptionPlanId'], $m4is_l366 )){
$m4is_h023[$m4is_g91703['Id']]=['CC1' =>isset($m4is_g91703['CC1'])? $m4is_g91703['CC1']: 0, 'Id' =>isset($m4is_g91703['Id'])? $m4is_g91703['Id']: 0, 'MerchantAccountId' =>empty($m4is_g91703['MerchantAccountId'])? $m4is_r03 : $m4is_g91703['MerchantAccountId'], 'OriginatingOrderId' =>isset($m4is_g91703['OriginatingOrderId'])? $m4is_g91703['OriginatingOrderId']: 0, 'Status' =>isset($m4is_g91703['Status'])? $m4is_g91703['Status']: 0, 'ContactId' =>isset($m4is_g91703['ContactId'])? $m4is_g91703['ContactId']: 0, 'AutoCharge' =>isset($m4is_g91703['AutoCharge'])? $m4is_g91703['AutoCharge']: 0, ];
 
}
}return $m4is_h023;
 
} private static 
function m4is_g56(array $m4is_h023, int $m4is_u807 ){
if(empty($m4is_h023 )){
return;
 
}foreach ($m4is_h023 as $m4is_l9671 =>$m4is_y362 ){
if($m4is_y362['Status']!== 'Active' ){
continue;
 
}if($m4is_y362['CC1']== $m4is_u807 ){
continue;
 
}$m4is_d07693 =(int) $m4is_y362['Id'];
 $m4is_e32607 =['CC1' =>(int) $m4is_u807, 'MerchantAccountId' =>(int) $m4is_y362['MerchantAccountId'],  'AutoCharge' =>1, ];
 m4is_c69807::m4is_z64('RecurringOrder', $m4is_d07693, $m4is_e32607 );
 
}
} private static 
function m4is_s02938(int $m4is_h21895, int $m4is_u807, array $m4is_h023, bool $m4is_d47 =false, bool $m4is_x36 =false ): void {
if($m4is_x36 )echo '<pre>Starting Backcharge</pre>';
 $m4is_r03 =self::$m4is_r1546->m4is_j498('settings', 'merchant_account_id' );
 if(!$m4is_r03 ){
if($m4is_x36 ){
echo '<pre>[Warning] Default merchant ID not set.  Invoice backcharge aborted.</pre>';
 
}error_log("Memberium (" . __LINE__ . ' ' . __FUNCTION__ . "): [Warning] Default merchant ID not set.  Invoice backcharge aborted." );
 return;
 
}$m4is_d01 =self::$m4is_r1546->m4is_v31259($m4is_h21895, false, true, true );
  $m4is_c76 =is_array($m4is_d01 )? count($m4is_d01 ): 0;
 if($m4is_d47 ){

}if($m4is_c76 === 0 ){
if($m4is_x36 ){
echo '<pre>No invoices found to backcharge.</pre>';
 
}return;
 
} $m4is_h3647 =['Id', 'JobRecurringId', ];
 $m4is_v76912 =['ContactId' =>$m4is_h21895, ];
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_u4290 =[];
 $m4is_m615 =m4is_c69807::m4is_o986('Job', $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647 );
 foreach($m4is_m615 as $m4is_g91703 ){
$m4is_u4290[$m4is_g91703['Id']]=isset($m4is_g91703['JobRecurringId'])? $m4is_g91703['JobRecurringId']: 0;
 
}unset($m4is_m615, $m4is_g91703);
 $m4is_w89066 =[];
 $m4is_x38729 =count($m4is_u4290 );
 if($m4is_x36 ){
echo "Default Merchant ID: {$m4is_r03
}<br>";
 echo "Unpaid Invoices Found: {$m4is_c76
}<br>";
 echo "Unpaid Jobs Found: {$m4is_x38729
}<br>";
 echo "Invoices: <pre>", print_r($m4is_d01, true ), "</pre>";
 echo "Jobs: <pre>", print_r($m4is_u4290, true ), "</pre>";
 
}if($m4is_c76 ){
foreach($m4is_d01 as $m4is_t06897 ){
if($m4is_d47 ){

}if(($m4is_t06897['TotalPaid']< $m4is_t06897['TotalDue'])and ($m4is_t06897['PayStatus']== '0' )and ($m4is_t06897['TotalDue']> 0 )){
$m4is_k76462 =$m4is_t06897['Id'];
 $m4is_w269 =$m4is_t06897['JobId'];
 if(isset($m4is_u4290[$m4is_w269])){
$m4is_i53127 =$m4is_u4290[$m4is_w269];
 if(!empty($m4is_h023[$m4is_i53127])&&$m4is_h023[$m4is_i53127]['Status']== 'Active' ){
$m4is_w89066[$m4is_k76462]=['invoice_id' =>(int) $m4is_k76462, 'recurringorder_id' =>(int) $m4is_i53127, 'merchant_id' =>$m4is_h023[$m4is_i53127]['MerchantAccountId']? (int) $m4is_h023[$m4is_i53127]['MerchantAccountId']: 0, 'total_due' =>$m4is_t06897['TotalDue'], ];
 
}elseif(isset($m4is_u4290[$m4is_w269])&&$m4is_u4290[$m4is_w269]== 0){
$m4is_w89066[$m4is_k76462]=['invoice_id' =>(int) $m4is_k76462, 'recurringorder_id' =>0, 'merchant_id' =>$m4is_r03, 'total_due' =>$m4is_t06897['TotalDue'], ];
 
}
}
}
}
}if($m4is_x36 ){
echo "Invoices: <pre>", print_r($m4is_d01, true ), "</pre>";
 echo "Invoices to Backcharge: <pre>", print_r($m4is_w89066, true ), "</pre>";
 
} usort($m4is_w89066, function($a, $b ){
if($a['total_due']> $b['total_due'])return -1;
 if($a['total_due']< $b['total_due'])return 1;
 if($a['invoice_id']< $b['invoice_id'])return -1;
 if($a['invoice_id']> $b['invoice_id'])return 1;
 return 0;
 
});
  foreach($m4is_w89066 as $m4is_c4069 ){
if($m4is_c4069['merchant_id']){
$m4is_u6591 =self::$m4is_z59682->chargeInvoice($m4is_c4069['invoice_id'], 'Updated Credit Card Back Charge', (int) $m4is_u807, $m4is_c4069['merchant_id'], false );
 if($m4is_x36 ){
echo '<pre>Contact ID:  ', $m4is_h21895, '</pre>';
 echo '<pre>Credit Card ID:  ', $m4is_u807, '</pre>';
 echo '<pre>Merchant ID:  ', $m4is_c4069['merchant_id'], '</pre>';
 echo '<pre>Invoice ID:  ', $m4is_c4069['invoice_id'], '</pre>';
 if(!empty($m4is_u6591['Message']))echo '<pre>Keap Response:  ', $m4is_u6591['Message'], '</pre>';
 
}if($m4is_d47 ){
error_log('[Memberium] Backcharging - Contact ID:     ' . $m4is_h21895 );
 error_log('[Memberium] Backcharging - Credit Card ID: ' . $m4is_u807 );
 error_log('[Memberium] Backcharging - Merchant ID:    ' . $m4is_c4069['merchant_id']);
 error_log('[Memberium] Backcharging - Invoice ID:     ' . $m4is_c4069['invoice_id']);
 error_log('[Memberium] Backcharging - API Result:     ' . $m4is_u6591['Message']);
 
}if(is_string($m4is_u6591 )){
error_log("Memberium (" . __LINE__ . ' ' . __FUNCTION__ . "): [Warning] Error Charging Invoice ID {$m4is_c4069['invoice_id']
} - {$m4is_u6591
}" );
 
}
}else{
error_log("Memberium (" . __LINE__ . ' ' . __FUNCTION__ . "): [Warning] Error Charging Invoice ID {$m4is_c4069['invoice_id']
} - No Merchant ID" );
 
}
}
}
}

