<?php

/**
 * Copyright (c) 2017-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_q96705 {
private static bool $m4is_r02639;
 private static int $m4is_h21895;
 private static object $m4is_r1546;
 private static object $m4is_f683;
 private static string $m4is_f4218;
  static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_f683 =m4is_f58::m4is_c26();
 self::$m4is_r02639 =!m4is_s52::m4is_w74();
 self::$m4is_h21895 =(int) self::$m4is_r1546->m4is_z56();
 self::$m4is_f4218 ='memberium';
 
} static 
function m4is_y2487($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'fields' =>'', 'htmlattr' =>'', 'separator' =>' ', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_n0216 =array_filter(explode(',', trim($m4is_l62046['fields'])));
 $m4is_o498 ='';
 if(empty($m4is_n0216 )){
return '';
 
}foreach ($m4is_n0216 as $m4is_r637 ){
$m4is_r637 =strtolower(trim($m4is_r637 ));
 $m4is_o498 .= htmlspecialchars(self::$m4is_r1546->m4is_d842($m4is_r637 ));
 if(count($m4is_n0216 )> 1 ){
$m4is_o498 .= $m4is_l62046['separator'];
 
}
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} static 
function m4is_k6589($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['includepassword' =>false, 'name' =>'membsession', 'omit' =>'',  ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_c54072 =m4is_f61::m4is_d8195($m4is_l62046['includepassword'], false );
 $m4is_j108 =array_filter(explode(',', $m4is_l62046['omit']));
 $session =m4is_q82::m4is_d59(self::$m4is_r1546->m4is_x66());
 if(!$m4is_c54072 ){
$m4is_r6234 =strtolower(self::$m4is_r1546->m4is_j498('settings', 'password_field' ));
 unset($session['keap']['contact'][$m4is_r6234]);
 
}foreach ($m4is_j108 as $m4is_q09176){
unset($session['memb_user'][$m4is_q09176], $session['keap']['contact'][$m4is_q09176], $session['keap']['affiliate'][$m4is_q09176]);
 
}$m4is_o498 ='<script type="text/javascript">' . "\n";
 $m4is_o498 .= 'var ' . $m4is_l62046['name']. ' = ' . json_encode($session, JSON_PRETTY_PRINT). ";\n";
 $m4is_o498 .= '</script>' . "\n";
 return $m4is_o498;
 
} static 
function m4is_k80462($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_l073 ='';
 static $m4is_m92735 =0;
 $m4is_m92735++;
 $m4is_y642 =['actionset_id' =>0, 'buttontext' =>'Change Email', 'email1label' =>'Email Address:', 'email2label' =>'Repeat Email Address:', 'failure_url' =>'', 'form_name' =>'change_email_' . $m4is_m92735, 'goal' =>'', 'success_url' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_f087 =(int) self::$m4is_r1546->m4is_x66();
 $m4is_f4930 =m4is_q82::m4is_k660($m4is_f087, 'contact', 'email', '' );
 if(empty($m4is_f4930 )||empty($m4is_f087 )||empty(self::$m4is_h21895 )){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_o048 ='memb_email_change_' . $m4is_m92735;
 $m4is_a01 ='';
 $m4is_u076 =base64_encode(serialize(['actionset_id' =>$m4is_l62046['actionset_id'], 'goal' =>$m4is_l62046['goal'], 'failure_url' =>$m4is_l62046['failure_url'], 'success_url' =>$m4is_l62046['success_url'], ]));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u076 );
 if(!empty($_SESSION['memb_flash']['email_change_message'])){
$m4is_a01 =$_SESSION['memb_flash']['email_change_message'];
 unset($_SESSION['memb_flash']['email_change_message'], $_SESSION['memb_flash']['email_change_result']);
 
}$m4is_l91805 =new stdClass;
 $m4is_l91805->form_name =$m4is_l62046['form_name'];
 $m4is_l91805->nonce =wp_nonce_field('memb_email_change_' . $m4is_m92735, '_wpnonce', true, false );
 $m4is_l91805->signature =self::$m4is_r1546->m4is_r626($m4is_u076 );
 $m4is_l91805->parameters =$m4is_u076;
 $m4is_l91805->form_id =$m4is_m92735;
 $m4is_l91805->email =$m4is_f4930;
 $m4is_l91805->email1_label =$m4is_l62046['email1label'];
 $m4is_l91805->email2_label =$m4is_l62046['email2label'];
 $m4is_l91805->button_text =$m4is_l62046['buttontext'];
 $m4is_l91805->message =$m4is_a01;
 return m4is_f61::m4is_l0659($m4is_v3458, $m4is_l62046, $m4is_t09761, $m4is_v3458, $m4is_l91805 );
 
} static 
function m4is_t02($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_m92735 =0;
 $m4is_m92735++;
 $m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field');
 $m4is_y802 =($m4is_r6234 == 'Password')? 20 : 256;
 $m4is_y642 =['actionset_id' =>0, 'buttontext' =>'Change Password', 'confirm_template_id' =>0, 'goal' =>'', 'maxlength' =>$m4is_y802, 'password1label' =>'New Password:', 'password2label' =>'Repeat Password:', 'redirect_url' =>'',  'success_message' =>_x('Password Changed Successfully.', 'memb_change_password', 'memberium'), 'success_url' =>'', 'tagids' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(self::$m4is_r1546->m4is_v461()||is_archive()||is_feed()||is_search()){
return '';
 
}if(!self::$m4is_h21895 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['maxlength']=abs(intval($m4is_l62046['maxlength']));
 $m4is_u076 =base64_encode(serialize(['confirm_template_id' =>(int) trim($m4is_l62046['confirm_template_id']), 'goal' =>trim($m4is_l62046['goal']), 'actionset_id' =>trim($m4is_l62046['actionset_id']), 'redirect_url' =>trim($m4is_l62046['redirect_url']), 'tagids' =>trim($m4is_l62046['tagids']), 'successurl' =>trim($m4is_l62046['success_url']), 'contact_id' =>self::$m4is_h21895, ]));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u076);
 $m4is_x29143 ='memb_password_change-' . $m4is_m92735;
 $m4is_i796 ='password_change_' . $m4is_m92735;
 $m4is_i46 ='';
 $m4is_r54196 =remove_query_arg(['passwordchange'], $_SERVER['REQUEST_URI']);
 $m4is_v215 =self::$m4is_f683->m4is_t73689();
 $m4is_l91805 =new stdClass;
 $m4is_l91805->form_name =$m4is_x29143;
 $m4is_l91805->form_action =$m4is_r54196;
 $m4is_l91805->form_id =$m4is_m92735;
 $m4is_l91805->nonce =wp_nonce_field($m4is_i796. $m4is_u076, '_wpnonce', true, false);
;
 $m4is_l91805->parameters =$m4is_u076;
 $m4is_l91805->signature =$m4is_o31859;
 $m4is_l91805->max_length =$m4is_l62046['maxlength'];
 $m4is_l91805->min_length =self::$m4is_r1546->m4is_j498('settings', 'min_password_length');
 $m4is_l91805->password1_label =$m4is_l62046['password1label'];
 $m4is_l91805->password2_label =$m4is_l62046['password2label'];
 $m4is_l91805->button_text =$m4is_l62046['buttontext'];
 $m4is_l91805->messages ='';
 if(!empty($m4is_v215)){
$m4is_l91805->messages =$m4is_v215;
 self::$m4is_f683->m4is_h185('');
 
}else{
if(!empty($_GET['passwordchange'])&&$_GET['passwordchange']== 'success' ){
$m4is_l91805->messages =$m4is_l62046['success_message'];
 unset($_GET['passwordchange']);
 
}
}return m4is_f61::m4is_l0659($m4is_v3458, $m4is_l62046, $m4is_t09761, $m4is_v3458, $m4is_l91805);
 
} static 
function m4is_y794($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_m92735 =1;
 $m4is_y642 =['buttontext' =>'Send Password', 'emailtext' =>'Email Address', 'successurl' =>'',  'failureurl' =>'',  'template_id' =>0, 'tag_id' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!empty(self::$m4is_r1546->m4is_j498('settings', 'local_auth_only'))){
if(self::$m4is_r1546->m4is_v461()){
return '<p style="color:red;font-weight:bold;">Send Password Form disabled by secure password storage.</p>';
 
}return '';
 
}if(is_user_logged_in()){
if(self::$m4is_r1546->m4is_v461()){
return '<p style="color:red;font-weight:bold;">Send Password Form not visible to logged in users.</p>';
 
}return '';
 
}if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_x29143 ='memb_password_send-' . $m4is_m92735;
 $m4is_i46 ='';
 $m4is_l62046['template_id']=(int) $m4is_l62046['template_id'];
 $m4is_l62046['tag_id']=abs(intval($m4is_l62046['tag_id']));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_l62046['template_id']. $m4is_l62046['tag_id']);
 if(!empty(self::$m4is_f683->m4is_t73689())){
$m4is_i46 .= '<div class="password_send_message">' . self::$m4is_f683->m4is_t73689(). '</div>';
 
}$m4is_i46 .= "<form name=\"{$m4is_x29143
}\" id=\"{$m4is_x29143
}\" method=\"post\">";
 $m4is_i46 .= "<input type=\"hidden\" name=\"form_id\" value=\"{$m4is_m92735
}\">";
 $m4is_i46 .= "<input type=\"hidden\" name=\"template_id\" value=\"{$m4is_l62046['template_id']
}\">";
 $m4is_i46 .= "<input type=\"hidden\" name=\"signature\" value=\"{$m4is_o31859
}\">";
 $m4is_i46 .= '<input type="hidden" name="successurl" value="' . base64_encode($m4is_l62046['successurl']). '">';
 $m4is_i46 .= '<input type="hidden" name="failureurl" value="' . base64_encode($m4is_l62046['failureurl']). '">';
 $m4is_i46 .= '<input type="hidden" name="tag_id" value="' . base64_encode($m4is_l62046['tag_id']). '">';
 $m4is_i46 .= '<input type="hidden" name="memb_form_type" value="memb_send_password">';
 $m4is_i46 .= '<div id="' . $m4is_x29143 . '-block1">';
 $m4is_i46 .= '<label id="' . $m4is_x29143 . '-email-label">' . $m4is_l62046['emailtext']. ':</label>';
 $m4is_i46 .= '<input id="' . $m4is_x29143 . '-email-input" name="email" type="email" required value="">';
 $m4is_i46 .= '</div>';
 $m4is_i46 .= '<div id="' . $m4is_x29143 . '-block2">';
 $m4is_i46 .= '<input type="submit" value="' . $m4is_l62046['buttontext']. '" name="submit">';
 $m4is_i46 .= '</div>';
 $m4is_i46 .= "</form>";
 self::$m4is_f683->m4is_h185('');
 $m4is_m92735++;
 return $m4is_i46;
 
} static 
function m4is_p09846($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_b87 =1;
 $m4is_y642 =['date_fields' =>'', 'failure_url' =>'', 'form_id' =>'registration_form_' . $m4is_b87, 'goal' =>'', 'pass_fields' =>true, 'pass_password' =>false, 'remove_accents' =>'y', 'required_fields' =>'', 'success_url' =>'', 'tagids' =>'',  ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(empty(self::$m4is_h21895 )){
return '';
 
}m4is_j586::m4is_x7134();
        $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_a73190 =base64_encode(serialize(['contact_id' =>self::$m4is_h21895, 'date_fields' =>$m4is_l62046['date_fields'], 'failure_url' =>$m4is_l62046['failure_url'], 'form_id' =>$m4is_l62046['form_id'], 'goal' =>$m4is_l62046['goal'], 'pass_fields' =>$m4is_l62046['pass_fields'], 'pass_password' =>$m4is_l62046['pass_password'], 'remove_accents' =>$m4is_l62046['remove_accents'], 'required_fields' =>$m4is_l62046['required_fields'], 'success_url' =>$m4is_l62046['success_url'], 'tagids' =>$m4is_l62046['tagids'],  ]));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_a73190);
 $m4is_i46 ='';
 if(isset($_SESSION['error_message'])&&is_array($_SESSION['error_message'])){
foreach ($_SESSION['error_message']as $m4is_l073){
$m4is_i46 .= '<p class="memb_registration_error"> ' . $m4is_l073 . '</p>';
 
}unset($_SESSION['error_message']);
 
}$m4is_i46 .= '<form method="post" action="" id="' . $m4is_l62046['form_id']. '" name="">';
 $m4is_i46 .= wp_nonce_field($m4is_a73190, '_wpnonce', true, false);
 $m4is_i46 .= "<input type=\"hidden\" name=\"memb_form_type\" value=\"memb_update_contact_form\">";
 $m4is_i46 .= '<input type="hidden" name="params" value="' . $m4is_a73190 . '">';
 $m4is_i46 .= '<input type="hidden" name="signature" value="' . $m4is_o31859 . '">';
 $m4is_i46 .= do_shortcode($m4is_t09761);
 $m4is_i46 .= '</form>';
 $m4is_b87++;
 return $m4is_i46;
 
} static 
function m4is_p4863($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'cache' =>HOUR_IN_SECONDS, 'capture' =>'', 'mode' =>'text', 'htmlattr' =>'', 'txtfmt' =>'', ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_o498 ='';
 $m4is_f4930 =m4is_q82::m4is_d6758(self::$m4is_r1546->m4is_x66(), 'memb_user', 'email', '' );
 if(!empty($m4is_f4930 )){
$m4is_c31 =self::$m4is_f683->m4is_l86($m4is_f4930 );
 if($m4is_l62046['mode']== 'text'){
switch ($m4is_c31){
case 1: $m4is_o498 =_x('Single Opted In', 'memb_optin_status', 'memberium');
 break;
 case 2: $m4is_o498 =_x('Double Opted In', 'memb_optin_status', 'memberium');
 break;
 default: $m4is_o498 =_x('Not Opted In', 'memb_optin_status', 'memberium');
 break;
 
}
}else{
$m4is_o498 =$m4is_c31;
 
}
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_p78($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['capture' =>'', 'contact_id' =>self::$m4is_h21895, 'date_format' =>'', 'default' =>'', 'fields' =>'', 'htmlattr' =>'', 'owner_id' =>0, 'separator' =>' ', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['fields']=trim($m4is_l62046['fields']);
 $m4is_l62046['contact_id']=(int) $m4is_l62046['contact_id'];
 $m4is_l62046['fields']=array_filter(explode(',', $m4is_l62046['fields']));
 if(empty($m4is_l62046['fields'])){
return '';
 
} if($m4is_l62046['owner_id']> 0){
$m4is_l62046['owner_id']=(int) $m4is_l62046['owner_id'];
 
}else{
if($m4is_l62046['contact_id']<> self::$m4is_h21895 ){
$m4is_j90523 =m4is_p40::m4is_p67($m4is_l62046['contact_id'], true);
 $m4is_l62046['owner_id']=(int) $m4is_j90523['ownerid'];
 unset($m4is_j90523);
 
}else{
$m4is_l62046['owner_id']=m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', 'ownerid', 0 );
 
}
}$m4is_w597 =m4is_u36::m4is_b6056($m4is_l62046['owner_id'], true );
 $m4is_n0216 =$m4is_l62046['fields'];
 $m4is_s12460 =count($m4is_n0216);
 $m4is_v81756 =0;
 $m4is_v4821 =0;
 $m4is_o498 ='';
 foreach($m4is_l62046['fields']as $m4is_r637){
$m4is_v4821++;
 $m4is_r637 =strtolower(trim($m4is_r637));
 if(isset($m4is_w597[$m4is_r637])){
$m4is_o076 =strtotime($m4is_w597[$m4is_r637]);
 if($m4is_l62046['date_format']== '' ||$m4is_o076 == 0){
$m4is_w86 =$m4is_w597[$m4is_r637];
 
}else{
$m4is_w86 =date($m4is_l62046['date_format'], strtotime($m4is_w597[$m4is_r637]));
 
}
}else{
$m4is_w86 =$m4is_l62046['default'];
 
}$m4is_o498 .= $m4is_w86;
 if($m4is_v4821 < $m4is_s12460){
$m4is_o498 .= $m4is_l62046['separator'];
 
}
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr']);
 
}static 
function m4is_b30647($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['actionset_id' =>'', 'contact_id' =>self::$m4is_h21895, 'date_format' =>'', 'fields' =>'', 'goal' =>'', 'tag_id' =>'', 'txtfmt' =>'', 'value' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['contact_id']=(int) $m4is_l62046['contact_id'];
 $m4is_l62046['fields']=array_filter(explode(',', $m4is_l62046['fields']));
 $m4is_l62046['value']=m4is_f61::m4is_n05($m4is_l62046['value'], $m4is_l62046['txtfmt']);
 if(empty($m4is_l62046['contact_id'])){
return '';
 
}if(empty($m4is_l62046['fields'])){
return '';
 
}if($m4is_l62046['date_format']> '' ){
if(strcasecmp($m4is_l62046['date_format'], 'infusionsoft' )== 0 ){
$m4is_l62046['date_format']='Ymd\Th:i:s';
 
}$m4is_l62046['value']=date($m4is_l62046['date_format'], strtotime($m4is_l62046['value']));
 
}$m4is_l62046['value']=trim($m4is_l62046['value']);
 $m4is_t27891 =m4is_c69807::m4is_f5248('Contact' );
 $m4is_d962 =['CreatedBy', 'DateCreated', 'Groups', 'Id', 'LastUpdated', 'LastUpdatedBy', 'Validated', ];
  if(is_array($m4is_t27891 )){
foreach ($m4is_t27891 as $valid_field ){
$m4is_l62046['fields']=str_ireplace($valid_field, $valid_field, $m4is_l62046['fields']);
 
}
}foreach ($m4is_l62046['fields']as $m4is_l9671 =>$m4is_q523 ){
if(!in_array($m4is_q523, $m4is_t27891)||in_array($m4is_q523, $m4is_d962)){
unset($m4is_l62046['fields'][$m4is_l9671]);
 
}else{
self::$m4is_r1546->m4is_s56($m4is_q523, $m4is_l62046['value'], $m4is_l62046['contact_id']);
 
}
}if($m4is_l62046['contact_id']== self::$m4is_h21895 ){
m4is_q82::m4is_u687(self::$m4is_r1546->m4is_x66());
 
}return '';
 
}static 
function m4is_f9305($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_b87 =1;
 if(is_feed()){
return '';
 
}$m4is_y642 =['action_id' =>'', 'autologin' =>false, 'date_fields' =>'', 'encoded' =>true, 'failure_url' =>'', 'form_id' =>'', 'goal' =>'', 'inf_fields' =>false, 'membership_tags' =>'',  'pass_fields' =>false, 'pass_password' =>false, 'recaptcha' =>false, 'remove_accents' =>'n', 'required_fields' =>'FirstName,Email', 'secure' =>true, 'success_url' =>'', 'tagids' =>'', 'unfriendly' =>false, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}if(is_user_logged_in()){
if(current_user_can('manage_options' )){
if(empty($m4is_t09761 )){
return '<p style="color:red;"><strong>ERROR:</strong> No Form Specified</pre>';
 
}if(stripos($m4is_t09761, '<input' )=== false ||stripos($m4is_t09761, 'FirstName' )=== false ||stripos($m4is_t09761, 'Email' )=== false ){
return '<p style="color:red;"><strong>ERROR:</strong>  Your form must include both the <strong>FirstName</strong>, and <strong>Email</strong> input fields in order to register a new contact.</p>';
 
}return '<p style="color:red;"><strong>NOTE:</strong>  The registration form is only displayed when not logged in.</p>';
 
}return '';
 
}m4is_j586::m4is_x7134();
     $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['autologin']=m4is_f61::m4is_d8195($m4is_l62046['autologin'], false );
 $m4is_l62046['encoded']=m4is_f61::m4is_d8195($m4is_l62046['encoded'], false );
 $m4is_l62046['inf_fields']=m4is_f61::m4is_d8195($m4is_l62046['inf_fields'], false );
 $m4is_l62046['pass_fields']=m4is_f61::m4is_d8195($m4is_l62046['pass_fields'], false );
 $m4is_l62046['pass_password']=m4is_f61::m4is_d8195($m4is_l62046['pass_password'], false );
 $m4is_l62046['recaptcha']=m4is_f61::m4is_d8195($m4is_l62046['recaptcha'], false );
 $m4is_l62046['secure']=m4is_f61::m4is_d8195($m4is_l62046['secure'], false );
 $m4is_l62046['unfriendly']=m4is_f61::m4is_d8195($m4is_l62046['unfriendly'], false );
  if($m4is_l62046['recaptcha']){
$m4is_l62046['recaptcha_v2_secret_key']=self::$m4is_r1546->m4is_j498('settings', 'recaptcha_v2_secret_key', '' );
 $m4is_l62046['recaptcha_v2_site_key']=self::$m4is_r1546->m4is_j498('settings', 'recaptcha_v2_site_key', '' );
 if(empty($m4is_l62046['recaptcha_v2_secret_key'])||empty($m4is_l62046['recaptcha_v2_site_key'])){
$m4is_l62046['recaptcha']=false;
 $m4is_l62046['recaptcha_v2_secret_key']='';
 $m4is_l62046['recaptcha_v2_site_key']='';
 
}
}if(empty(trim($m4is_l62046['form_id']))){
$m4is_l62046['form_id']='registration_form_' . $m4is_b87;
 
}$m4is_a73190 =base64_encode(serialize(['action_id' =>$m4is_l62046['action_id'], 'autologin' =>$m4is_l62046['autologin'], 'date_fields' =>$m4is_l62046['date_fields'], 'failure_url' =>$m4is_l62046['failure_url'], 'form_id' =>$m4is_l62046['form_id'], 'goal' =>$m4is_l62046['goal'], 'membership_tags' =>$m4is_l62046['membership_tags'], 'pass_fields' =>$m4is_l62046['pass_fields'], 'pass_password' =>$m4is_l62046['pass_password'], 'recaptcha' =>$m4is_l62046['recaptcha'], 'remove_accents' =>$m4is_l62046['remove_accents'], 'required_fields' =>$m4is_l62046['required_fields'], 'success_url' =>$m4is_l62046['success_url'], 'tagids' =>$m4is_l62046['tagids'], 'unfriendly' =>$m4is_l62046['unfriendly'], ]));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_a73190 );
 $m4is_i46 ='';
 if(!empty($_SESSION['error_message'])){
foreach ($_SESSION['error_message']as $m4is_l073 ){
$m4is_i46 .= '<p class="memb_registration_error"> ' . $m4is_l073 . '</p>';
 
}
}$m4is_l91805 =new stdClass;
 $m4is_l91805->recaptcha_html =$m4is_l62046['recaptcha']? '<div class="g-recaptcha" data-sitekey="' . $m4is_l62046['recaptcha_v2_site_key']. '"></div>' : '';
 if($m4is_l62046['recaptcha']){
wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true );
 
}$m4is_i46 .= '<form method="post" action="" id="' . $m4is_l62046['form_id']. '" name="">' . "\n";
 $m4is_i46 .= "<input type=\"hidden\" name=\"memb_form_type\" value=\"memb_registration\">" . "\n";
 $m4is_i46 .= wp_nonce_field($m4is_a73190, '_wpnonce', true, false ). "\n";
 $m4is_i46 .= '<input type="hidden" name="params" value="' . $m4is_a73190 . '">' . "\n";
 $m4is_i46 .= '<input type="hidden" name="signature" value="' . $m4is_o31859 . '">' . "\n";
 $m4is_i46 .= $m4is_l91805->recaptcha_html . "\n";
 $m4is_i46 .= do_shortcode($m4is_t09761 ). "\n";
 $m4is_i46 .= '</form>' . "\n\n";
 $m4is_i46 =do_shortcode($m4is_i46 );
 if($m4is_l62046['encoded']||$m4is_l62046['secure']){
$m4is_i46 =m4is_f61::m4is_b07361($m4is_i46 );
 
}$m4is_b87++;
 $_SESSION['error_message']=null;
 return $m4is_i46;
 
}static 
function m4is_x7438($m4is_l62046, $m4is_t09761 =NULL, $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'default' =>'', 'fieldname' =>'', 'htmlattr' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 if(empty($m4is_l62046['fieldname'])){
return '';
 
}$m4is_l17096 =wp_get_current_user();
 $m4is_r637 =$m4is_l62046['fieldname'];
 $m4is_o498 =$m4is_l17096->$m4is_r637;
 $m4is_o498 =empty($m4is_l17096->$m4is_r637)? $m4is_l62046['default']: $m4is_l17096->$m4is_r637;
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_p01($m4is_l62046, $m4is_t09761 =NULL, $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_m92735 =0;
 m4is_j586::m4is_x7134();
 $m4is_y642 =['send_button_text' =>'Reset Password', 'template_id' =>0, 'update_button_text' =>'Update Password', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}  if(is_user_logged_in()){
return '';
 
}if(is_feed()){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['template_id']=(int) $m4is_l62046['template_id'];
 $m4is_m92735 =$m4is_m92735 + 1;
 $m4is_i46 ='';
 if(isset($_GET['action'])&&isset($_GET['key'])&&isset($_GET['login'])){
$m4is_l17096 =check_password_reset_key($_GET['key'], $_GET['login']);
 if('WP_User' == get_class($m4is_l17096)){
$m4is_i46 .= '<form method="post" action="./">';
 $m4is_i46 .= wp_nonce_field('memb_reset_password_' . $m4is_m92735, '_wpnonce', true, false);
 $m4is_i46 .= '<input type="hidden" name="form_id" value="' . $m4is_m92735 . '">';
 $m4is_i46 .= '<input type="hidden" name="memb_form_type" value="memb_reset_password">';
 $m4is_i46 .= '<input type="hidden" name="key" value="' . $_GET['key']. '">';
 $m4is_i46 .= '<input type="hidden" name="login" value="' . $_GET['login']. '">';
 $m4is_i46 .= '<label>Enter Your New Password:</label><input type="password" name="password1" placeholder="New Password" size="30"><br />';
 $m4is_i46 .= '<label>Confirm Your New Password:</label><input type="password" name="password2" placeholder="New Password" size="30"><br />';
 $m4is_i46 .= '<input type="submit" value="' . $m4is_l62046['update_button_text']. '">';
 $m4is_i46 .= '</form>';
 
}else{
$m4is_i46 .= '<p>Invalid / Expired Link</p>';
 
}
}else{
$m4is_u897 =base64_encode(serialize(['template_id' =>$m4is_l62046['template_id']]));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u897);
 $m4is_i46 .= '<form method="post">';
 $m4is_i46 .= wp_nonce_field('memb_reset_password_' . $m4is_m92735, '_wpnonce', true, false);
 $m4is_i46 .= '<input type="hidden" name="form_id" value="' . $m4is_m92735 . '">';
 $m4is_i46 .= '<input type="hidden" name="memb_form_type" value="memb_reset_password">';
 $m4is_i46 .= '<input type="hidden" name="params" value="' . $m4is_u897 . '">';
 $m4is_i46 .= '<input type="hidden" name="signature" value="' . $m4is_o31859 . '">';
 $m4is_i46 .= '<label>Your Login Email:</label><input type="email" name="email" placeholder="Your Email Address" size="30"><br />';
 $m4is_i46 .= '<input type="submit" value="' . $m4is_l62046['send_button_text']. '">';
 $m4is_i46 .= '</form>';
 
}return $m4is_i46;
 
}static 
function m4is_n09($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['alt' =>'', 'capture' =>'', 'class' =>'memberium-gravatar', 'default' =>'', 'email' =>m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', 'email', '' ), 'rating' =>'g', 'size' =>32, 'title' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['size']=((int) $m4is_l62046['size']< 1)? 32 : (int) $m4is_l62046['size'];
 if($m4is_l62046['size']> 2048){
$m4is_l62046['size']=2048;
 
}if(empty($m4is_l62046['email'])&&is_user_logged_in()){
$m4is_l17096 =wp_get_current_user();
 $m4is_l62046['email']=$m4is_l17096->user_email;
 
}$m4is_l62046['email']=strtolower(trim($m4is_l62046['email']));
 $m4is_b5890 ='//www.gravatar.com/avatar/' . md5($m4is_l62046['email']). '.jpg?s=' . (int) $m4is_l62046['size'];
 $m4is_b5890 .= ($m4is_l62046['default']> '')? '&d=' . urlencode($m4is_l62046['default']): '';
 $m4is_b5890 .= ($m4is_l62046['rating']> '')? '&r=' . urlencode($m4is_l62046['rating']): '';
 $m4is_t09761 ='<img src="' . $m4is_b5890 . '" border="0" alt="' . esc_attr($m4is_l62046['alt']). '" title="' . esc_attr($m4is_l62046['title']). '" height="' . esc_attr($m4is_l62046['size']). '" width="' . esc_attr($m4is_l62046['size']). '" class="' . $m4is_l62046['class']. '"/>';
 return m4is_f61::m4is_u150(false, $m4is_t09761, '', $m4is_l62046['capture'], '', '', '');
 
}static 
function m4is_r06($m4is_l62046, $m4is_t09761 =NULL, $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'htmlattr' =>'', 'name' =>false, 'op' =>'show', 'txtfmt' =>'', 'val' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['name']=strtolower(trim($m4is_l62046['name']));
 if(empty($m4is_l62046['name'])){
return '';
 
}$m4is_f087 =self::$m4is_r1546->m4is_x66();
 if(!$m4is_f087){
return '';
 
} $m4is_o498 ='';
 $m4is_v4821 =(int) self::$m4is_f683->m4is_v9648($m4is_l62046['name']);
 switch (strtolower($m4is_l62046['op'])){
case 'decr': if((int) $m4is_l62046['val']== 0){
$m4is_l62046['val']=1;
 
}$m4is_v4821 =$m4is_v4821 - (int) $m4is_l62046['val'];
 break;
 case 'incr': if((int) $m4is_l62046['val']== 0){
$m4is_l62046['val']=1;
 
}$m4is_v4821 =$m4is_v4821 + (int) $m4is_l62046['val'];
 break;
 case 'set': $m4is_v4821 =(int) $m4is_l62046['val'];
 break;
 case 'show': case 'get': $m4is_o498 =$m4is_v4821;
 break;
 
}self::$m4is_f683->m4is_c5104($m4is_l62046['name'], $m4is_v4821);
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_g3142($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'alt' =>'No membership levels found.', 'before' =>'', 'capture' =>'', 'format' =>'<li>%%membershipname%% (%%tagid%%)</li>', 'htmlattr' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_k824 =m4is_q82::m4is_d59(self::$m4is_r1546->m4is_x66());
 if(!empty($m4is_k824['memb_user']['membership_tags'])){
$m4is_l5726 =explode(',', $m4is_k824['memb_user']['membership_tags']);
 $m4is_o498 ='';
 $m4is_l9321 =m4is_k865::m4is_z2906();
 $m4is_m96240 =self::$m4is_r1546->m4is_j498('memberships' );
 foreach ($m4is_l5726 as $m4is_x083){
$m4is_o498 .= str_ireplace('%%tagid%%', $m4is_x083, $m4is_l62046['format']);
 $m4is_o498 =str_ireplace('%%tagname%%', $m4is_l9321['mc'][$m4is_x083], $m4is_o498);
 $m4is_o498 =str_ireplace('%%membershiplevel%%', $m4is_m96240[$m4is_x083]['level'], $m4is_o498);
 $m4is_o498 =str_ireplace('%%membershipname%%', $m4is_m96240[$m4is_x083]['name'], $m4is_o498);
 
}
}else{
$m4is_o498 =$m4is_l62046['alt'];
 
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_f66081($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['mode' =>'plain', 'style' =>'color:#000;', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_g415 =m4is_a391::m4is_g29870($m4is_f087 );
 if($m4is_g415 ){
$m4is_k5680 =add_query_arg('rss_user', $m4is_g415, get_feed_link());
 
}else{
$m4is_k5680 =get_feed_link();
 
}if($m4is_l62046['mode']== 'input'){
$m4is_b3785 =strlen($m4is_k5680);
 $m4is_k5680 ='<input class="memberium_feed_url" style="' . $m4is_l62046['style']. '" value="' . $m4is_k5680 . '" disabled="disabled" size="' . $m4is_b3785 * 0.9 . '">';
 
}elseif($m4is_l62046['mode']== 'key'){
$m4is_k5680 =$m4is_g415;
 
}return $m4is_k5680;
 
}static 
function m4is_z671($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
 static $m4is_m92735 =0;
 $m4is_y642 =['css_class' =>'', 'style' =>'', 'button_text' =>'Get New RSS URL', 'button_url' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_m92735++;
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_x29143 ='memb_resetfeedurl_button-' . $m4is_m92735;
 $m4is_c8625 ='memb_resetfeedurl_button_' . $m4is_m92735;
 $m4is_i46 ='<form method="post" action="">';
 $m4is_i46 .= "<input type='hidden' name='memb_form_type' value='memb_resetfeedurl_button'>";
 $m4is_i46 .= "<input type='hidden' name='form_id' value='{$m4is_m92735
}'>";
 $m4is_i46 .= wp_nonce_field("memb_resetfeedurl_{$m4is_m92735
}", '_wpnonce', true, false);
 if(empty($m4is_l62046['button_url'])){
$m4is_i46 .= "<input type=\"submit\" class=\"{$m4is_l62046['css_class']
}\" id=\"{$m4is_c8625
}\" value=\"{$m4is_l62046['button_text']
}\">";
 
}else{
$m4is_i46 .= "<input type=\"image\" src=\"{$m4is_l62046['button_url']
}\" class=\"{$m4is_l62046['css_class']
}\" id=\"{$m4is_c8625
}\" >";
 
}$m4is_i46 .= '</form>';
 return $m4is_i46;
 
}static 
function m4is_e20845($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
} $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'contact_id' =>self::$m4is_h21895, 'date_format' =>'', 'default' =>'', 'fields' =>'', 'field_filter' =>'', 'htmlattr' =>'', 'separator' =>' ', 'txtfmt' =>'', ];
  if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_h21895 =(int) $m4is_l62046['contact_id'];
  if(empty($m4is_l62046['fields'])){
return '';
 
}m4is_j586::m4is_x7134();
 if($m4is_h21895 <> self::$m4is_h21895 ){
$m4is_j90523 =m4is_p40::m4is_p67($m4is_h21895, true );
 
}else{
$m4is_j90523 =m4is_q82::m4is_d6758(self::$m4is_r1546->m4is_x66(), 'keap', 'contact', []);
 
}$m4is_n0216 =array_filter(explode(',', $m4is_l62046['fields']));
 $m4is_s12460 =count($m4is_n0216 );
 $m4is_v81756 =0;
 $m4is_o498 ='';
 $m4is_e66310 =$m4is_h21895 ? false : current_user_can('manage_options' );
  foreach ($m4is_n0216 as $m4is_r637 ){
if($m4is_e66310 ){
$m4is_u6820 ='Admin';
 
}else{
$m4is_r637 =strtolower(trim($m4is_r637 ));
 if(isset($m4is_j90523[$m4is_r637])){
$m4is_o076 =strtotime($m4is_j90523[$m4is_r637]);
 if($m4is_l62046['date_format']== '' ||$m4is_o076 == 0 ){
$m4is_u6820 =isset($m4is_j90523[$m4is_r637])? htmlspecialchars($m4is_j90523[$m4is_r637]): '';
 
}else{
$m4is_u6820 =date($m4is_l62046['date_format'], $m4is_o076 );
 
}
}else{
$m4is_u6820 =$m4is_l62046['default'];
 
}
}if($m4is_l62046['field_filter']){
$m4is_u6820 =apply_filters("memberium/shortcodes/memb_contact/{$m4is_l62046['field_filter']
}", $m4is_u6820, $m4is_r637 );
 
}$m4is_o498 .= $m4is_u6820;
 if($m4is_s12460 > 1){
$m4is_o498 .= $m4is_l62046['separator'];
 
}
}if($m4is_s12460 > 1 &&strlen($m4is_l62046['separator'])> 0 ){
$m4is_o498 =substr($m4is_o498, 0, -strlen($m4is_l62046['separator']));
 
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_c59043($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}global $wpdb;
 $m4is_y642 =['count' =>10, 'date_format' =>'F d, Y', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!is_user_logged_in()){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['count']=empty($m4is_l62046['count'])? 10 : (int) $m4is_l62046['count'];
 $m4is_l17096 =wp_get_current_user();
 $m4is_y193 =$m4is_l17096->user_login;
 $m4is_r9613 =self::$m4is_r1546->m4is_i76('appname');
 $m4is_o498 ='';
 if(empty($m4is_t09761)){
$m4is_t09761 ='%%logintime%% - %%ipaddress%%<br />';
 
}$m4is_v2613 ="SELECT `logintime`, `ipaddress` FROM `" . m4is_l5841::m4is_h37(). "` WHERE `username` = '{$m4is_y193
}' AND `appname` = '{$m4is_r9613
}' ORDER BY `logintime` DESC ";
 if($m4is_l62046['count']){
$m4is_v2613 .= ' LIMIT '. $m4is_l62046['count'];
 
}$m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 if(is_array($m4is_m615)){
foreach($m4is_m615 as $m4is_g91703){
$m4is_t42917 =$m4is_t09761;
 $m4is_t42917 =str_ireplace('%%logintime%%', date($m4is_l62046['date_format'], $m4is_g91703['logintime']), $m4is_t42917);
 $m4is_t42917 =str_ireplace('%%ipaddress%%', $m4is_g91703['ipaddress'], $m4is_t42917);
 $m4is_o498 .= $m4is_t42917;
 
}
}m4is_j586::m4is_x7134();
 return $m4is_o498;
 
} static 
function m4is_q83467($m4is_l62046 =[], $m4is_t09761 =null, $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
} $m4is_y642 =['count' =>10, 'id' =>'', 'offset' =>0,  'order_by' =>'display_name', 'order' =>'ASC', 'roles' =>'subscriber', 'tag_id' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_o498 ='';
 $m4is_l62046['order']=strtoupper(trim($m4is_l62046['order']));
 $m4is_l62046['order_by']=strtolower(trim($m4is_l62046['order_by']));
 $m4is_l62046['id']=empty($m4is_l62046['id'])? 0 : m4is_p40::m4is_i6158($m4is_l62046['id']);
 $m4is_l62046['roles']=trim($m4is_l62046['roles']);
 if(empty($m4is_t09761 )){

}if(!empty($m4is_l62046['roles'])){
$m4is_l62046['roles']=explode(',', $m4is_l62046['roles']);
 $m4is_l62046['roles']=array_map('trim', $m4is_l62046['roles']);
 $m4is_l62046['roles']=array_map('strtolower', $m4is_l62046['roles']);
 $m4is_l62046['roles']=array_filter($m4is_l62046['roles']);
 
}else{
$m4is_l62046['roles']=[];
 
}$m4is_l9321 =trim($m4is_l62046['tag_id']);
 if(!empty($m4is_l9321 )){
$m4is_l9321 =explode(',', $m4is_l9321 );
 $m4is_l9321 =array_map('trim', $m4is_l9321 );
 $m4is_l9321 =array_map('strtolower', $m4is_l9321 );
 $m4is_l9321 =array_filter($m4is_l9321 );
 $m4is_o140 =implode('|', $m4is_l9321 );
 
}else{
$m4is_o140 ='';
 
}$m4is_y66291 =['fields' =>'all', 'number' =>(int) $m4is_l62046['count'], 'offset' =>(int) $m4is_l62046['offset'],  'orderby' =>'meta_value', 'order' =>$m4is_l62046['order'],   ];
  if(!empty($m4is_l9321 )){
$m4is_y66291['meta_query'][]=['key' =>'memb_Groups', 'compare' =>'REGEXP', 'value' =>'(^|,)' . $m4is_o140 . '(,|$)',    ];
 
}if($m4is_l62046['id']){
$m4is_y66291['search']=$m4is_l62046['id'];
 $m4is_y66291['search_columns']=['ID' ];
 
}if(!empty($m4is_l62046['roles'])){
$m4is_y66291['role__in']=$m4is_l62046['roles'];
 
}$m4is_r403 =get_users($m4is_y66291 );
 $m4is_z96 =count($m4is_r403 );
 $m4is_t69 =[];
 foreach($m4is_r403 as $m4is_d07693 =>$m4is_l17096){
$m4is_f087 =$m4is_l17096->data->ID;
 $m4is_h21895 =m4is_p40::m4is_w58096($m4is_l17096->data->ID );
 if($m4is_h21895 ){
$m4is_t69[$m4is_d07693]=$m4is_l17096;
 $m4is_t69[$m4is_d07693]->contact_id =$m4is_h21895;
  
}
}$m4is_r403 =$m4is_t69;
 $m4is_o60745 =false !== stripos($m4is_t09761, '%%contact.' );
 $m4is_w85027 =false !== stripos($m4is_t09761, '%%role.' );
 $m4is_p1675 =false !== stripos($m4is_t09761, '%%user.' );
 $m4is_a569 =false !== stripos($m4is_t09761, '%%meta.' );
 $m4is_s83 =false !== stripos($m4is_t09761, '%%capability.' );
 $m4is_p65406 =['ID', 'user_nicename', 'user_url', 'user_registered', 'user_status', 'display_name' ];
 if(isset($m4is_r403[0]->data->contact_fields[$m4is_l62046['order_by']])){
usort($m4is_r403, function($u1, $u2)use ($m4is_l62046){
$v1 =$u1->data->contact_fields[$m4is_l62046['order_by']];
 $v2 =$u2->data->contact_fields[$m4is_l62046['order_by']];
 return ($m4is_l62046['order']== 'ASC')? strnatcasecmp($v1, $v2 ): strnatcasecmp($v2, $v1 );
 
});
 
}if(is_array($m4is_r403 )){
$m4is_o280 =0;
 foreach($m4is_r403 as $m4is_l17096 ){
$m4is_p6925 =$m4is_t09761;
 $m4is_h21895 =$m4is_l17096->contact_id;
 $m4is_i935 =m4is_p40::m4is_p67($m4is_h21895 );
 if(!empty($m4is_i935['Email'])){
$m4is_o280++;
 if($m4is_o60745 ){
foreach($m4is_i935 as $m4is_l9671 =>$m4is_v586 ){
$m4is_p6925 =str_ireplace('%%contact.' . $m4is_l9671 . '%%', $m4is_v586, $m4is_p6925 );
 
}
}if($m4is_w85027 ){
foreach($m4is_l17096->roles as $m4is_l9671 =>$m4is_v586 ){
$m4is_p6925 =str_ireplace('%%role.' . $m4is_l9671 . '%%', $m4is_v586, $m4is_p6925 );
 
}
}if($m4is_a569 ){
$m4is_p6925 =str_ireplace('%%meta.ipaddress%%', $m4is_l17096->login_ip_address, $m4is_p6925 );
 $m4is_p6925 =str_ireplace('%%meta.login_count%%', (int) $m4is_l17096->login_count, $m4is_p6925 );
 
}if($m4is_p1675 ){
foreach($m4is_p65406 as $m4is_l9671 ){
$m4is_p6925 =str_ireplace('%%user.' . $m4is_l9671 . '%%', $m4is_l17096->data->$m4is_l9671, $m4is_p6925 );
 
}
}$m4is_o498 =preg_replace('/(%%\S+%%)/', '', $m4is_o498 );
 $m4is_o498 .= $m4is_p6925;
 
}
}
} $m4is_o498 =do_shortcode($m4is_o498);
 return $m4is_o498;
 
} static 
function m4is_x248($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['tokens' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642 ));
 
}$user_id =self::$m4is_r1546->m4is_x66();
 if(!$user_id){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 self::$m4is_r1546->m4is_s85($m4is_l62046['tokens']);
 return '';
 
}static 
function m4is_p9656($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_m92735 =0;
 m4is_j586::m4is_x7134();
 $m4is_y642 =['actionset' =>'', 'button_text' =>'Get New Password', 'email_label' =>'Username or Email', 'form_name' =>'lostpasswordform', 'goal' =>'', 'redirect' =>'', 'redirect' =>'', 'tag_id' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(self::$m4is_r1546->m4is_j498('settings', 'disable_lost_password')){
if(self::$m4is_r1546->m4is_v461()){
return '<p><strong style="color:red;">Admin Notice:</strong>  Lost password feature disabled in Site Security settings.</p>';
 
}return '';
 
}if(is_user_logged_in()){
if(current_user_can('manage_options' )){
return '<p><strong style="color:red;">Admin Notice:</strong>  You are already logged in.</p>';
 
}return '';
 
}if(is_feed()){
return '';
 
}$m4is_m92735++;
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['form_name']='_' . $m4is_m92735;
 $m4is_l62046['redirect']=empty($m4is_l62046['redirect'])? get_site_url(): $m4is_l62046['redirect'];
 $m4is_l91805 =['actionset' =>$m4is_l62046['actionset'], 'goal' =>$m4is_l62046['goal'], 'redirect' =>$m4is_l62046['redirect'], 'tag_id' =>$m4is_l62046['tag_id'], ];
 $m4is_u076 =base64_encode(serialize($m4is_l91805));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u076);
 $m4is_i46 ='';
 $m4is_l91805 =new stdclass;
 $m4is_l91805->goal =$m4is_l62046['goal'];
 $m4is_l91805->actionset =$m4is_l62046['actionset'];
 $m4is_l91805->button_text =$m4is_l62046['button_text'];
 $m4is_l91805->email_label =$m4is_l62046['email_label'];
 $m4is_l91805->form_id =$m4is_m92735;
 $m4is_l91805->form_name =$m4is_l62046['form_name'];
 $m4is_l91805->message ='';
 $m4is_l91805->parameters =$m4is_u076;
 $m4is_l91805->redirect =$m4is_l62046['redirect'];
 $m4is_l91805->signature =$m4is_o31859;
 $m4is_l91805->tag_id =$m4is_l62046['tag_id'];
 if(isset($_SESSION['flash']['lost_password_message'])){
$m4is_l91805->message =$_SESSION['flash']['lost_password_message'];
 unset($_SESSION['flash']['lost_password_message']);
 
}return m4is_f61::m4is_l0659($m4is_v3458, $m4is_l62046, $m4is_t09761, $m4is_v3458, $m4is_l91805);
 
}static 
function m4is_l6860($m4is_l62046 =[], $m4is_t09761 =null, $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_m676 ='';
 $m4is_y642 =['length' =>self::$m4is_r1546->m4is_j498('settings', 'min_password_length'), 'strength' =>self::$m4is_r1546->m4is_j498('settings', 'password_strength'), 'repeat' =>true, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['repeat']=m4is_f61::m4is_d8195($m4is_l62046['repeat'], true);
 if(empty($m4is_m676)||$m4is_l62046['repeat']=== false ){
$m4is_m676 =self::$m4is_r1546->m4is_a601($m4is_l62046['length'], $m4is_l62046['strength']);
 
}return $m4is_m676;
 
}    static 
function m4is_e70($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_q82::m4is_u687(self::$m4is_r1546->m4is_x66());
 return '';
 
}static 
function m4is_g02($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_u450 =$m4is_l62046['keys']?? '';
 $m4is_k824 =m4is_q82::m4is_d59(self::$m4is_r1546->m4is_x66());
 ksort($m4is_k824 );
 return '<pre>' . print_r($m4is_k824, true ). '</pre>';
 
}static 
function m4is_f25($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['contact_id' =>self::$m4is_r1546->m4is_z56(), 'tag_ids' =>'', ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_l62046['tag_ids']=array_map('trim', array_filter(explode(',', $m4is_l62046['tag_ids'])));
 $m4is_f087 =m4is_p40::m4is_i6158($m4is_l62046['contact_id']);
 if(empty($m4is_f087 )||empty($m4is_l62046['tag_ids'])){
return '';
 
}$m4is_l7639 =m4is_k865::m4is_z2906(false, false );
 $m4is_k824 =m4is_q82::m4is_d59($m4is_f087 );
 $m4is_f406 =empty($m4is_k824['memb_user']['tags'])? []: array_filter(explode(',', $m4is_k824['memb_user']['tags']));
 $m4is_g61295 =array_intersect($m4is_f406, $m4is_l62046['tag_ids']);
 $m4is_o498 ='';
 if(empty($m4is_t09761 )){
foreach ($m4is_g61295 as $m4is_d913 ){
if(array_key_exists($m4is_d913, $m4is_l7639['mc'])){
$m4is_o498 .= sprintf('<p class="tag_list_%d">%s</p>', $m4is_d913, $m4is_l7639['mc'][$m4is_d913]);
 
}
}
}else{
foreach ($m4is_g61295 as $m4is_d913 ){
if(array_key_exists($m4is_d913, $m4is_l7639['mc'])){
$m4is_t42917 =$m4is_t09761;
 $m4is_t42917 =str_ireplace('{{tag_id}}', $m4is_d913, $m4is_t42917 );
 $m4is_t42917 =str_ireplace('{{tag_name}}', $m4is_l7639['mc'][$m4is_d913], $m4is_t09761 );
 $m4is_o498 .= $m4is_t42917;
 
}
}
}return $m4is_o498;
 
}
}

