<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_s6729')||die();
 final 
class m4is_h65 {
static $m4is_l8963;
 static $m4is_p73;
 static 
function m4is_c961(){
self::$m4is_p73 ='https://keap.memberium.com/';
 self::$m4is_l8963 =[];
 
} static 
function m4is_o64(int $m4is_d07693, string $m4is_e63195 ='Read More...' ): string {
$m4is_c67 ='';
 if(empty($m4is_d07693 )){
return '';
 
}return sprintf(' (<strong><a href="%s?page_id=%d" target="_blank">%s</a></strong>) ', self::$m4is_p73, $m4is_d07693, $m4is_e63195 );
 
}    static 
function m4is_z896(string $m4is_a173, string $m4is_j0361 ='update' ){
self::$m4is_l8963[]=['type' =>$m4is_j0361, 'message' =>$m4is_a173 ];
 
} static 
function m4is_n25(){
if(!empty(self::$m4is_l8963 )){
foreach (self::$m4is_l8963 as $m4is_o015 =>$m4is_w743 ){
if($m4is_w743['type']== 'update' ){
$m4is_k97 ='updated';
 
}elseif($m4is_w743['type']== 'error' ){
$m4is_k97 ='error';
 
}printf('<div class="%s"><p>%s</p></div>', $m4is_k97, $m4is_w743['message']);
 unset(self::$m4is_l8963[$m4is_o015]);
 
}
}
}    static 
function m4is_k58(): array {
$m4is_q76 =[];
 $m4is_x320 =get_post_types(['public' =>true]);
 if(is_array($m4is_x320)){
foreach($m4is_x320 as $m4is_q485){
$m4is_q76[]=$m4is_q485;
 
}
}return $m4is_q76;
 
}    static 
function m4is_w6712(string $m4is_w42 ='', string $m4is_k52736 ='', int $m4is_v586 =0, array $m4is_y66291 =[]){
$m4is_y642 =['class' =>'', 'disabled' =>'', 'help_id' =>0, 'id' =>$m4is_k52736, 'max' =>999999, 'min' =>0, 'size' =>8, 'step' =>1, 'style' =>'', 'units' =>'', ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642);
 $m4is_y66291['size']-= (int) $m4is_y66291['size'];
 $m4is_y66291['step']-= (int) $m4is_y66291['step'];
 $m4is_i46 ='';
 $m4is_i46 .= empty($m4is_y66291['disabled'])? '' : ' disabled="disabled" ';
 $m4is_y66291 =[$m4is_w42, $m4is_i46, $m4is_y66291['id'], $m4is_k52736, $m4is_y66291['min'], $m4is_y66291['max'], $m4is_y66291['size'], $m4is_y66291['step'], $m4is_v586, $m4is_y66291['class'], $m4is_y66291['style'], $m4is_y66291['units'], self::m4is_o64($m4is_y66291['help_id']), ];
 vprintf("<li><label>%s</label><input type=number %s id='%s' name='%s' min=%s max=%s size=%s step=%s value='%s' class='%s' style='%s' > %s %s</li>\n\n", $m4is_y66291 );
 
} static 
function m4is_y648(string $m4is_w42, string $m4is_k52736, int $m4is_y52413 =0, bool $m4is_d87521 =false, $m4is_o498 =true ){
$m4is_p6356 =$m4is_d87521 ? 'checked=checked' : '';
 $m4is_y66291 =[$m4is_w42, $m4is_k52736, $m4is_k52736, $m4is_p6356, self::m4is_o64($m4is_y52413 ), ];
 $m4is_i46 =vsprintf("<li><label>%s</label><input type=hidden value=0 name='%s'><label style='width:75px;'><input type=checkbox value=1 class=ios-switch name='%s' %s /><div class=switch></div></label>%s</li>\n\n", $m4is_y66291 );
 if($m4is_o498 ){
echo $m4is_i46;
 
}return $m4is_i46;
 
} static 
function m4is_v6739(string $m4is_k52736 ='', bool $m4is_v586 =false, array $m4is_y66291 =[]){
if(empty($m4is_k52736)){
return;
 
}$m4is_y642 =['label' =>'', 'echo' =>true, 'id' =>$m4is_k52736, 'helpid' =>0, 'autofocus' =>false, 'class' =>'', 'disabled' =>false, 'form' =>'', 'required' =>false, 'style' =>'', ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642);
 $output ='';
 $output .= '<input type="hidden" value="0" name="' . $m4is_k52736 . '">';
 $output .= '<label style="width:75px;"><input type="checkbox" value="1" class="ios-switch" name="' . $m4is_k52736 . '" ' . ($m4is_v586 == 1 ? ' checked="checked" ' : ''). ' ';
 $output .= '/><div class="switch"></div></label>';
 $output .= m4is_h65::m4is_o64($m4is_y66291['helpid']). "</li>\n\n";
 if($m4is_y66291['echo']){
echo $output;
 
}return $output;
 
} static 
function m4is_f7205(string $m4is_k52736 ='', array $m4is_r37596 =[], $m4is_x39 ='', array $m4is_y66291 =[]){
if(empty($m4is_r37596)||empty($m4is_k52736)){
return;
 
}if(!is_array($m4is_x39)){
$m4is_x39 =explode(',', $m4is_x39);
 
}$m4is_y642 =['autofocus' =>false, 'class' =>'', 'disabled' =>false, 'case_sensitive' =>false, 'echo' =>true, 'form' =>'', 'id' =>$m4is_k52736, 'multiple' =>false, 'required' =>false, 'size' =>1, 'style' =>'', ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642);
 $m4is_o498 ='<select name="' . $m4is_k52736 . '" ';
 if($m4is_y66291['autofocus']){
$m4is_o498 .= ' autofocus="autofocus"';
 
}if($m4is_y66291['disabled']){
$m4is_o498 .= ' disabled="disabled"';
 
}if($m4is_y66291['multiple']){
$m4is_o498 .= ' multiple="multiple"';
 
}if($m4is_y66291['required']){
$m4is_o498 .= ' required="required"';
 
}if($m4is_y66291['size']){
$m4is_o498 .= ' size="' . (int) $m4is_y66291['size']. '"';
 
}if(!empty($m4is_y66291['class'])){
$m4is_o498 .= ' class="' . $m4is_y66291['class']. '"';
 
}if(!empty($m4is_y66291['style'])){
$m4is_o498 .= ' style="' . $m4is_y66291['style']. '"';
 
}if(!empty($m4is_y66291['form'])){
$m4is_o498 .= ' form="' . $m4is_y66291['form']. '"';
 
}if(!empty($m4is_y66291['id'])){
$m4is_o498 .= ' id="' . $m4is_y66291['id']. '"';
 
}$m4is_o498 .= ' size="' . $m4is_y66291['size']. '">';
 foreach($m4is_r37596 as $m4is_v586 =>$m4is_w42){
$m4is_a437 =false;
 foreach($m4is_x39 as $m4is_n246){
if($m4is_y66291['case_sensitive']){
$m4is_a437 =$m4is_a437 ||(bool) ($m4is_v586 == $m4is_n246);
 
}else{
$m4is_a437 =$m4is_a437 ||(bool) (0 === strcasecmp($m4is_v586, $m4is_n246));
 
}
}$m4is_o498 .= '<option value="' . $m4is_v586 . '" ' . ($m4is_a437 ? ' selected="selected" ' : ''). '>' . $m4is_w42 . '</option>';
 
}$m4is_o498 .= '</select>';
 if($m4is_y66291['disabled']){
foreach($m4is_x39 as $m4is_v586){
$m4is_o498 ="<input type='hidden' name='{$m4is_k52736
}[]' value='{$m4is_v586
}' />";
 
}
}if($m4is_y66291['echo']){
echo "\n\n", $m4is_o498, "\n\n";
 
}else{
return "\n\n" . $m4is_o498 . "\n\n";
 
}
} static 
function m4is_a9861(string $m4is_k52736, array $m4is_j86631 =[]){
$m4is_j86631['help_text']=isset($m4is_j86631['help_text'])? $m4is_j86631['help_text']: false;
 $m4is_j86631['help_id']=isset($m4is_j86631['help_id'])? $m4is_j86631['help_id']: 0;
 $m4is_j86631['type']=!empty($m4is_j86631['type'])? $m4is_j86631['type']: 'text';
 $m4is_j86631['id']=!empty($m4is_j86631['id'])? $m4is_j86631['id']: $m4is_k52736;
 $m4is_j86631['name']=!empty($m4is_j86631['name'])? $m4is_j86631['name']: $m4is_k52736;
 $m4is_j86631['required']=!empty($m4is_j86631['required'])? true : false;
 $m4is_u450 =['placeholder', 'size', 'style', 'class', 'value', 'label', 'type', 'name', 'wrapper_class', 'help_id', 'min', 'max', 'step' ];
 foreach($m4is_u450 as $m4is_l9671){
$m4is_j86631[$m4is_l9671]=isset($m4is_j86631[$m4is_l9671])? $m4is_j86631[$m4is_l9671]: '';
 
}$m4is_u450 =['custom'];
 foreach($m4is_u450 as $m4is_l9671){
$m4is_j86631[$m4is_l9671]=isset($m4is_j86631[$m4is_l9671])? $m4is_j86631[$m4is_l9671]: [];
 
}if(!empty($m4is_j86631['custom'])){
foreach ($m4is_j86631['custom']as $m4is_o015 =>$m4is_k72){
$m4is_j86631['custom'][$m4is_o015]=esc_attr($m4is_o015). '="' . esc_attr($m4is_k72). '" ';
 
}
}if($m4is_j86631['label']){
echo '<p class="', $m4is_j86631['wrapper_class'], '">';
 echo '<label for="', esc_attr($m4is_j86631['id']), '">', wp_kses_post($m4is_j86631['label']), '</label>', "\n";
 
}echo '<input ';
 $m4is_u450 =['placeholder', 'size', 'style', 'class', 'value', 'label', 'type', 'name', 'min', 'max', 'step'];
 echo $m4is_j86631['required']? ' required=required ' : '';
 foreach($m4is_u450 as $m4is_l9671){
echo (($m4is_j86631[$m4is_l9671]<> '')? $m4is_l9671 . '="'. esc_attr($m4is_j86631[$m4is_l9671]). '" ' : '');
 
}foreach($m4is_j86631['custom']as $m4is_l9671){
echo $m4is_l9671;
 
}echo '/>', "\n";
 echo m4is_h65::m4is_o64($m4is_j86631['help_id'], $m4is_j86631['help_text']);
 if($m4is_j86631['label']){
echo '</p>';
 
}
} static 
function m4is_h70259(string $m4is_w42 ='', string $m4is_k52736 ='', string $m4is_v586 ='', string $m4is_j927 ='', array $m4is_y66291 =[]){
$m4is_y642 =['help_id' =>0, 'style' =>'', 'class' =>'', 'naked' =>false, 'id' =>$m4is_k52736, 'multiple' =>'', 'units' =>'', 'disabled' =>'', ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642);
 $m4is_y66291['multiple']=empty($m4is_y66291['multiple'])? '' : 'multiple';
 $m4is_i46 ='';
 $m4is_i46 .= empty($m4is_y66291['disabled'])? '' : ' disabled="disabled" ';
 if(!$m4is_y66291['naked']){
echo '<li>';
 
}echo "<label for='{$m4is_k52736
}'>{$m4is_w42
}</label>", "<input {$m4is_i46
} value='{$m4is_v586
}' type=hidden id='{$m4is_y66291['id']
}' name='{$m4is_k52736
}' {$m4is_y66291['multiple']
} class='dropdown {$m4is_j927
} {$m4is_y66291['class']
}' style='{$m4is_y66291['style']
}' /> {$m4is_y66291['units']
} ", m4is_h65::m4is_o64($m4is_y66291['help_id']);
 if(!$m4is_y66291['naked']){
echo '</li>';
 
}
} static 
function m4is_z30162(string $m4is_w42 ='', string $m4is_k52736 ='', string $m4is_v586 ='', array $m4is_r37596 =[], array $m4is_y66291 =[]){
$m4is_y642 =['class' =>'basic-single', 'help_id' =>0, 'id' =>$m4is_k52736, 'style' =>'', ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642);
 echo "<li><label>{$m4is_w42
}</label>", "<select id='{$m4is_y66291['id']
}' class='basic-single {$m4is_y66291['class']
}' name='{$m4is_k52736
}' style='width:250px;'>";
 foreach ($m4is_r37596 as $m4is_d07693 =>$m4is_q1470){
$selected =($m4is_d07693 == $m4is_v586)? 'selected=selected' : '';
 echo "<option value='{$m4is_d07693
}' {$selected
}>{$m4is_q1470
}</option>";
 
}echo '</select>', m4is_h65::m4is_o64($m4is_y66291['help_id']), "</li>\n\n";
 
} static 
function m4is_i014(string $m4is_w42 ='', string $m4is_k52736 ='', string $m4is_v586 ='', array $m4is_y66291 =[]){
$m4is_y642 =['class' =>'', 'disabled' =>false, 'help_id' =>0, 'id' =>$m4is_k52736, 'pattern' =>'', 'placeholder' =>'', 'size' =>40, 'style' =>'', 'type' =>'text', ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642);
 $m4is_y66291['size']=(int) $m4is_y66291['size'];
 $m4is_y66291['disabled']=$m4is_y66291['disabled']? ' disabled=disabled ' : '';
 $m4is_y66291['pattern']=$m4is_y66291['pattern']? " pattern='{$m4is_y66291['pattern']
}' " : '';
 echo "<li><label>{$m4is_w42
}</label>", "<input type='{$m4is_y66291['type']
}' id='{$m4is_y66291['id']
}' {$m4is_y66291['pattern']
} name='{$m4is_k52736
}' placeholder='{$m4is_y66291['placeholder']
}' size='{$m4is_y66291['size']
}' value='{$m4is_v586
}' {$m4is_y66291['disabled']
}>", self::m4is_o64($m4is_y66291['help_id']), "</li>\n\n";
 
} static 
function m4is_m39(int $m4is_v586 =0, int $m4is_p48691 =0, int $m4is_x74 =0, array $m4is_y66291 =[]): string {
$m4is_y642 =['good' =>'font-weight:bold;color:green;', 'ok' =>'font-weight:bold;color:gold;', 'bad' =>'font-weight:bold;color:red;' ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642);
 $m4is_a3165 ='good';
 if($m4is_v586 < $m4is_x74){
$m4is_a3165 ='ok';
 
}elseif($m4is_v586 > $m4is_p48691){
$m4is_a3165 ='bad';
 
}return "<span style='{$m4is_y66291[$m4is_a3165]
}'>{$m4is_v586
}</span>";
 
} static 
function m4is_q62087(string $m4is_d07693): string {
if(strpos($_SERVER['REQUEST_URI'], '?')=== false){
$m4is_o9861 ='?';
 
}else{
$m4is_o9861 ='&';
 
}return $_SERVER['REQUEST_URI']. $m4is_o9861 . 'memberium_ignore_notice=' . urlencode($m4is_d07693);
 
}  static 
function m4is_q267(string $m4is_l9671, string $m4is_n6062 =''): string {
$m4is_l9671 =strtolower(trim($m4is_l9671));
 $m4is_z984 ='memberium::welcomecontent::' . $m4is_l9671;
 if(defined('MEMBERIUM_BETA' )&&constant('MEMBERIUM_BETA' )){
delete_transient($m4is_z984);
 
}$m4is_t09761 =get_transient($m4is_l9671);
 if(!$m4is_t09761){
$m4is_d67825 =urlencode($m4is_l9671);
 $m4is_a6814 =m4is_r83::m4is_c26()->m4is_w45();
 if(empty($m4is_n6062)){
$m4is_n6062 ="https://licenseserver.webpowerandlight.com/welcome/index.php?tab={$m4is_d67825
}&version={$m4is_a6814
}";
 
}$m4is_n548 =wp_remote_get($m4is_n6062);
 if(is_a($m4is_n548, 'WP_Error')){
if(isset($m4is_n548->errors['http_request_failed'][0])){
$m4is_t09761 ="<p>Loading Remote Page Content Failed:  {$m4is_n548->errors['http_request_failed'][0]
}</p>";
 
}else{
$m4is_t09761 ='<p>Loading Remote Page Content Failed</p>';
 
}
}else{
$m4is_t09761 =isset($m4is_n548['body'])? $m4is_n548['body']: '<p>No Content Available</p>';
 if($m4is_t09761 > ''){
set_transient($m4is_z984, $m4is_t09761, 3600);
 
}else{
$m4is_t09761 ='<p>Page content temporarily unavailable.</p>';
 
}
}
}return $m4is_t09761;
 
} static 
function m4is_w01954(){
 $m4is_w9764 =wp_get_themes();
 $m4is_r15 =[];
 $m4is_r15[]=['id' =>'', 'text' =>'(Default)' ];
 foreach ($m4is_w9764 as $m4is_k52736 =>$m4is_c67896 ){
$m4is_r15[]=['id' =>$m4is_k52736, 'text' =>$m4is_c67896->Name ];
 
}return json_encode($m4is_r15 );
 
} public static 
function m4is_h1426(): array {
global $wp_roles;
 $m4is_g36 =$wp_roles->roles;
 $m4is_e863 =[];
 $m4is_d35066 =['activate_plugins', 'create_user', 'delete_plugins', 'delete_themes', 'delete_users', 'edit_plugins', 'edit_themes', 'edit_users', 'install_plugins', 'install_themes', 'manage_options', 'switch_themes', 'update_core', 'update_plugins', 'update_themes', ];
 foreach ($m4is_g36 as $m4is_x40 =>$m4is_y6983 ){
$m4is_k52736 =$m4is_y6983['name'];
 $m4is_m3605 =$m4is_y6983['capabilities'];
 foreach($m4is_d35066 as $m4is_l9671 ){
if(array_key_exists($m4is_l9671, $m4is_m3605 )){
continue 2;
 
}
}$m4is_e863[]=['id' =>$m4is_x40, 'name' =>$m4is_y6983['name']];
 
}return $m4is_e863;
 
} static 
function m4is_z53(){
global $wpdb;
 $m4is_d579 =implode("','", m4is_h65::m4is_k58());
 $m4is_v2613 ="SELECT `ID`, `post_title` FROM `{$wpdb->posts
}` WHERE `post_status` = 'publish' AND `post_type` IN ( '" . $m4is_d579 . "' ) ORDER BY `id` ASC;";
 $m4is_f602 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 $m4is_k0941[]=['id' =>0, 'text' =>'(Default)' ];
 $m4is_k0941[]=['id' =>-1, 'text' =>'(User Profile Page)' ];
 foreach ($m4is_f602 as $m4is_d07693=>$page ){
$m4is_k0941[]=['id' =>$page['ID'], 'text' =>"{$page['post_title']
} ({$page['ID']
})" ];
 
}return json_encode($m4is_k0941 );
 
} 
}

