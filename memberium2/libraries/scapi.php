<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_f61 {
private static $m4is_r1546;
 private static $m4is_b7682;
  static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 
}    private static 
function m4is_y12978(): array {
return ['abs', 'addslashes', 'bin2hex', 'ceil', 'convert_uudecode', 'convert_uuencode', 'crc32', 'crypt', 'floor', 'html_entity_decode', 'htmlentities', 'htmlspecialchars_decode', 'htmlspecialchars', 'intval', 'lcfirst', 'ltrim', 'md5', 'nl2br', 'rawurlencode', 'remove_accents', 'rtrim', 'sha1', 'str_rot13', 'strip_tags', 'stripslashes', 'strlen', 'strrev', 'strtolower', 'strtoupper', 'trim', 'ucfirst', 'ucwords', 'wptexturize', ];
 
} static 
function m4is_d8195($m4is_v586, $m4is_n246 =false ): bool {
$m4is_v586 =strtolower(trim($m4is_v586 ));
 if($m4is_v586 == 'on' ){
return true;
 
}elseif($m4is_v586 == 'off' ){
return false;
 
}$m4is_v586 =substr($m4is_v586, 0, 1 );
 switch($m4is_v586){
case 'y': case 't': case '1': return true;
 break;
 case 'n': case 'f': case '0': return false;
 break;
 
}return (binary) $m4is_n246;
 
} static 
function m4is_b02($m4is_f16846, $m4is_q160, $m4is_c269, $m4is_p966 =true ){
$m4is_q160 =strtolower(trim($m4is_q160 ));
 if(self::m4is_d8195($m4is_p966, true )){
$m4is_f16846 =strtolower(trim($m4is_f16846 ));
 $m4is_c269 =strtolower(trim($m4is_c269 ));
 
}$m4is_m60 =false;
 if(in_array($m4is_q160, ['=', '==', '===', 'eq' ])){
$m4is_m60 =($m4is_f16846 == $m4is_c269);
 
}elseif(in_array($m4is_q160, ['gt', '>' ])){
$m4is_m60 =($m4is_f16846 > $m4is_c269);
 
}elseif(in_array($m4is_q160, ['lt', '<' ])){
$m4is_m60 =($m4is_f16846 < $m4is_c269);
 
}elseif(in_array($m4is_q160, ['le', '<=' ])){
$m4is_m60 =($m4is_f16846 <= $m4is_c269);
 
}elseif(in_array($m4is_q160, ['ge', '>=', '=>' ])){
$m4is_m60 =($m4is_f16846 >= $m4is_c269);
 
}elseif(in_array($m4is_q160, ['ne', '!=', '!==' ])){
$m4is_m60 =($m4is_f16846 <> $m4is_c269);
 
}elseif(in_array($m4is_q160, ['bw', '~=' ])){
 $m4is_m60 =(strpos($m4is_f16846, $m4is_c269 )=== 0 );
 
}elseif(in_array($m4is_q160, ['ew', '=~' ])){
 if($m4is_f16846 == $m4is_c269){
$m4is_m60 =true;
 
}else{
if(strlen($m4is_f16846 )> strlen($m4is_c269 )){
$m4is_m60 =@substr_compare($m4is_f16846, $m4is_c269, -strlen($m4is_c269 ), strlen($m4is_c269 ))=== 0;
 
}else{
$m4is_m60 =false;
 
}
}
}elseif(in_array($m4is_q160, ['contains', '~~' ])){
$m4is_m60 =strpos($m4is_f16846, $m4is_c269 )!== false;
 
}elseif($m4is_q160 == 'in' ){
$m4is_c269 =array_filter(array_map('trim', explode(',', $m4is_c269 )));
 $m4is_m60 =in_array($m4is_f16846, $m4is_c269 );
 
}elseif($m4is_q160 == '!in' ){
$m4is_c269 =array_filter(array_map('trim', explode(',', $m4is_c269 )));
 $m4is_m60 =!in_array($m4is_f16846, $m4is_c269 );
 
}elseif($m4is_q160 == 'range' ){
$m4is_y64308 =explode(',', $m4is_c269 );
 $m4is_m60 =$m4is_f16846 >= $m4is_y64308[0]&&$m4is_f16846 <= $m4is_y64308[1];
 
}elseif($m4is_q160 == '!range' ){
$m4is_y64308 =explode(',', $m4is_c269 );
 $m4is_m60 =$m4is_f16846 < $m4is_y64308[0]||$m4is_f16846 > $m4is_y64308[1];
 
}elseif($m4is_q160 == 'datebefore' ){
$m4is_t539 =(int) strtotime($m4is_c269 . ' - ' . $m4is_f16846);
 $m4is_p64263 =(int) strtotime($m4is_c269);
 $m4is_m60 =(time()>= $m4is_t539 &&time()<= $m4is_p64263 );
 
}elseif($m4is_q160 == 'dateafter' ){
$m4is_f96 =strtotime($m4is_c269 . ' + ' . $m4is_f16846);
 $m4is_p64263 =strtotime($m4is_c269);
 $m4is_m60 =(time()<= $m4is_f96 &&time()>= $m4is_p64263);
 
}return (bool) $m4is_m60;
 
} static 
function m4is_n05(string $m4is_k4693, string $m4is_n63071 ): string {
static $m4is_t1524 =[];
 $m4is_t1524 =empty($m4is_t1524)? self::m4is_y12978(): $m4is_t1524;
 $m4is_i9605 =array_filter(explode(',', $m4is_n63071 ));
 foreach ($m4is_i9605 as $m4is_n63071 ){
if(in_array(strtolower($m4is_n63071 ), $m4is_t1524 )){
$m4is_k4693 =$m4is_n63071($m4is_k4693);
 
}else{
if($m4is_n63071 == 'sanitize_title' ){
$m4is_k4693 =sanitize_title($m4is_k4693, $m4is_k4693 );
 
}
}
}return (string) $m4is_k4693;
 
} static 
function m4is_f6513($m4is_t09761, $m4is_b61495 ='' ): string {
$m4is_v05 =false;
 if($m4is_b61495 == '' ){
$m4is_b61495 ='display';
 
}if(!is_array($m4is_b61495 )){
$m4is_b61495 =explode(',', $m4is_b61495);
 
}$contact_update =[];
 if(is_array($m4is_b61495 )){
foreach ($m4is_b61495 as $m4is_o498 ){
$m4is_o498 =strtolower(trim($m4is_o498 ));
 if($m4is_o498 == 'display' ){
$m4is_v05 =true;
 
}if(strtolower(substr($m4is_o498, 0, 6 ))== 'field:' ){
$m4is_s36520 =substr($m4is_o498, 6 );
 if(!is_user_logged_in()){
self::$m4is_b7682[$m4is_s36520]=$m4is_t09761;
 
}else{
self::$m4is_r1546->m4is_y165($m4is_s36520, $m4is_t09761 );
 
}
}if(substr($m4is_o498, 0, 4 )== 'var:' ){
$m4is_x64872 =substr($m4is_o498, 4 );
 
} 
}
} if($m4is_v05 ){
return $m4is_t09761;
 
}return '';
 
} static 
function m4is_d6851($m4is_t09761 ='', $m4is_v8646 ='', $m4is_o28 =true, $m4is_m60 =false ): string {
if($m4is_t09761 == '' ){
if($m4is_m60 ){
return __('Yes', 'memberium' );
 
}else{
return __('No', 'memberium' );
 
}
}$m4is_n9786 =strtolower('[else_' . $m4is_v8646 . ']' );
 $m4is_u628 =strtolower('[else_' . substr($m4is_v8646, 5 ). ']' );
 $m4is_t09761 =str_ireplace($m4is_u628, $m4is_n9786, $m4is_t09761 );
 $m4is_f1247 =false;
 if(stripos($m4is_t09761, $m4is_n9786 )!== false ){
$m4is_f1247 =true;
 
}if($m4is_f1247 === false ){
$m4is_h502 =['success' =>$m4is_t09761, 'failure' =>'' ];
 
}else{
$m4is_n9786 =str_replace(['[', ']'], ['\[', '\]'], $m4is_n9786 );
 $m4is_u6591 =preg_split('/' . $m4is_n9786 . '/i', $m4is_t09761 );
 $m4is_h502 =['success' =>$m4is_u6591[0], 'failure' =>$m4is_u6591[1]];
 
}if($m4is_o28 ){
if($m4is_m60 == true){
return do_shortcode($m4is_h502['success']);
 
}else{
return do_shortcode($m4is_h502['failure']);
 
}
}return '';
  
} static 
function m4is_u150($m4is_e6683 =true, $m4is_t09761 ='', $m4is_i1590 ='', $m4is_j02786 ='', $m4is_g658 ='', $m4is_z8214 ='', $m4is_t67 ='' ): string {
if($m4is_e6683 ){
$m4is_t09761 =do_shortcode($m4is_t09761 );
 
}if(!empty($m4is_i1590 )){
$m4is_t09761 =m4is_f61::m4is_n05($m4is_t09761, $m4is_i1590);
 
}$m4is_t09761 =$m4is_z8214 . $m4is_t09761 . $m4is_t67;
 if(!empty($m4is_j02786 )){
$m4is_t09761 =m4is_f61::m4is_f6513($m4is_t09761, $m4is_j02786 );
 
}if(!empty($m4is_g658 )){
$m4is_t09761 =$m4is_g658 . '="' . $m4is_t09761 . '"';
 
}return $m4is_t09761;
 
} static 
function m4is_i3627(string $m4is_t09761 ='', $m4is_e85349 ='', string $m4is_v3458 =''): string {
if(empty($m4is_t09761 )){
return '';
 
}$m4is_g327 =[];
 $m4is_v05 =false;
 $m4is_m86264 =false;
 $m4is_n38675 ='default';
 $m4is_o498 ='';
 $m4is_c8657 ='/(\[lang.*\])/U';
 $m4is_e85349 =array_filter(explode(',', $m4is_e85349 ));
 $m4is_s496 =preg_split($m4is_c8657, $m4is_t09761, 0, PREG_SPLIT_DELIM_CAPTURE );
  foreach($m4is_s496 as $m4is_l9671 =>$m4is_v586){
$m4is_v586 =trim($m4is_v586);
 if(substr($m4is_v586, 0, 1)== '['){
$m4is_u897 =shortcode_parse_atts(substr($m4is_v586, 1, -1));
 if(isset($m4is_u897['lang'])){
$m4is_n38675 =$m4is_u897['lang'];
 
}
}else{
$m4is_g327[$m4is_n38675]=$m4is_v586;
 
}
}unset($m4is_s496, $m4is_l9671, $m4is_v586, $m4is_u897);
  $m4is_j03 =0;
 if(isset($m4is_g327['default'])){
$m4is_t09761 =$m4is_g327['default'];
 
}else{
$m4is_t09761 =reset($m4is_g327);
 
}foreach($m4is_e85349 as $m4is_r93654 =>$m4is_d81702 ){
if(isset($m4is_g327[$m4is_r93654])&&$m4is_d81702 > $m4is_j03){
$m4is_t09761 =$m4is_g327[$m4is_r93654];
 $m4is_j03 =$m4is_d81702;
 
}
}return $m4is_t09761;
 
} static 
function m4is_g4037($m4is_t47286, $m4is_s496, $m4is_n246 =''){
 $m4is_s496 =explode(',', $m4is_s496);
 foreach ($m4is_s496 as $m4is_a43256){
if(isset($m4is_t47286[$m4is_a43256])){
$m4is_t47286 =$m4is_t47286[$m4is_a43256];
 
}else{
$m4is_t47286 =$m4is_n246;
 break;
 
}
}return $m4is_t47286;
 
} static 
function m4is_q7642($m4is_k52736, $m4is_l67501 ='shortcodes' ){
$m4is_w46 =get_stylesheet_directory();
 $m4is_g34786 ='/' . $m4is_l67501 . '/' . $m4is_k52736 . '.php';
 $m4is_u415 =[ $m4is_w46 . '/memberium' . $m4is_g34786, self::$m4is_r1546->m4is_g316(). '/templates' . $m4is_g34786, ];
 $m4is_u415 =apply_filters('memberium/shortcodes/template-paths', $m4is_u415 );
 foreach($m4is_u415 as $m4is_d04266 ){
if(file_exists($m4is_d04266 )){
return $m4is_d04266;
 
}
}error_log("Memberium: [error] Shortcode template for {$m4is_k52736
} not found." );
 return false;
 
} static 
function m4is_l0659(string $name, array $atts =[], string $content ='', string $code ='', object $data ): string {
$m4is_d39827 =self::m4is_q7642($name );
 ob_start();
 if($m4is_d39827 ){
include $m4is_d39827;
 
}else{
if(self::$m4is_r1546->m4is_v461()){
echo "<P>Template Missing {$name
}</P>";
  
}
}$m4is_o498 =preg_replace('/\s+/', ' ', ob_get_clean());
  return $m4is_o498;
 
} public static 
function m4is_b07361(string $m4is_t09761 ): string {
if(!empty($m4is_t09761 )){
$m4is_x50 =mb_substr(base64_encode(mb_convert_encoding($m4is_t09761, 'UTF-8', 'auto' )), 0, -2 );
 $m4is_t09761 ='<script type="text/javascript"> document.write(atob("' . $m4is_x50 . '" + "==") ); </script>';
 
}return $m4is_t09761;
 
} public static 
function m4is_a478(array $m4is_l62046 ){
$m4is_l62046 =(is_array($m4is_l62046 ))? $m4is_l62046 : (array)$m4is_l62046;
 foreach ($m4is_l62046 as $m4is_l9671 =>$m4is_g096 ){
$m4is_l62046[$m4is_l9671]=self::m4is_p7451($m4is_g096 );
 
}return $m4is_l62046;
 
}public static 
function m4is_p7451($m4is_d659 ='' ){
$m4is_f087 =self::$m4is_r1546->m4is_x66();
 if(stripos($m4is_d659, '{{')!== false){
 $m4is_d659 =str_ireplace('{{ip_address}}', m4is_a01587::m4is_y342(), $m4is_d659);
 $m4is_d659 =str_ireplace('{{system_link.home}}', get_home_url(), $m4is_d659);
 $m4is_d659 =str_ireplace('{{system_link.site}}', get_site_url(), $m4is_d659);
 $m4is_d659 =str_ireplace('{{current.url}}', $_SERVER['REQUEST_URI'], $m4is_d659);
 $m4is_d659 =str_ireplace('{{contact_id}}', m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'crm_id', 0 ), $m4is_d659);
 $m4is_d659 =str_ireplace('{{post_id}}', get_the_ID(), $m4is_d659);
 $m4is_d659 =str_ireplace('{{author_contact_id}}', m4is_p40::m4is_w58096((int) get_the_author_meta('ID' )), $m4is_d659);
    if(stripos($m4is_d659, '{{registration_date}}')!== FALSE ){
$m4is_l17096 =wp_get_current_user();
 if($m4is_l17096 ){
$m4is_d659 =str_replace('{{registration_date}}', $m4is_l17096->user_registered, $m4is_d659);
 
}else{
$m4is_d659 =str_replace('{{registration_date}}', 'Not Registered', $m4is_d659);
 
}
}if(stripos($m4is_d659, '{{member.homepage}}')!== FALSE){
$m4is_v27639 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'login_page', 0 );
 $m4is_s8523 =$m4is_v27639 ? get_permalink($m4is_v27639 ): site_url();
 $m4is_d659 =str_ireplace('{{member.homepage}}', get_permalink($m4is_v27639 ), $m4is_d659);
 
}if(stripos($m4is_d659, '{{system_link.')!== FALSE){
$m4is_d659 =preg_replace_callback('|({{system_link\.(.*)}})|U', function($m4is_a157){
$m4is_f602 =get_option('memberium_pages');
 $m4is_a157[2]=isset($m4is_a157[2])? strtolower($m4is_a157[2]): 'none';
 if(isset($m4is_f602[$m4is_a157[2]])){
return get_permalink($m4is_f602[$m4is_a157[2]]);
 
}return;
 
}, $m4is_d659);
 
}$m4is_d659 =self::m4is_u02($m4is_d659 );
 $m4is_d659 =self::m4is_l6758($m4is_d659 );
 $m4is_d659 =self::m4is_u68($m4is_d659 );
 $m4is_d659 =self::m4is_j520($m4is_d659 );
 $m4is_d659 =self::m4is_z5086($m4is_d659 );
 $m4is_d659 =self::m4is_u069($m4is_d659 );
 $m4is_d659 =self::m4is_s26($m4is_d659 );
 $m4is_d659 =self::m4is_s3521($m4is_d659 );
 $m4is_d659 =self::m4is_c069($m4is_d659 );
 $m4is_d659 =self::m4is_f982($m4is_d659 );
 $m4is_d659 =self::m4is_q57($m4is_d659 );
 $m4is_d659 =self::m4is_h90($m4is_d659 );
  
}$m4is_d659 =self::m4is_o957($m4is_d659 );
 return $m4is_d659;
 
} private static 
function m4is_o29(string $m4is_o498 ): void {
if(strtolower(substr($m4is_o498, 0, 6 ))=== 'field:' ){
$m4is_s36520 =substr($m4is_o498, 6 );
 if(is_user_logged_in()){
self::$m4is_r1546->m4is_y165($m4is_s36520, $m4is_o498 );
 
}else{
self::$m4is_b7682[$m4is_s36520]=$m4is_o498;
 
}
}
}   private static 
function m4is_u02(string $m4is_u32416 ): string {
if(stripos($m4is_u32416, '{{author_contact_id}}' )=== false ){
return $m4is_u32416;
 
}global $authordata;
 $m4is_f087 =isset($authordata->ID )? $authordata->ID : 0;
 $m4is_e95021 =m4is_p40::m4is_w58096($m4is_f087 );
 $m4is_u32416 =str_ireplace('{{author_contact_id}}', $m4is_e95021, $m4is_u32416 );
  return $m4is_u32416;
 
}private static 
function m4is_l6758(string $m4is_u32416 ): string {
if(stripos($m4is_u32416, '{{permalink.' )=== false ){
return $m4is_u32416;
 
}$m4is_u32416 =preg_replace_callback('|({{permalink\.(.*)}})|U', function($m4is_a157 ){
return get_permalink($m4is_a157[2]);
 
}, $m4is_u32416 );
 return $m4is_u32416;
 
}private static 
function m4is_u68(string $m4is_u32416 ): string {
$m4is_f087 =self::$m4is_r1546->m4is_x66();
 if(stripos($m4is_u32416, '{{affiliate.' )=== false ){
return $m4is_u32416;
 
}$m4is_u32416 =preg_replace_callback('|({{affiliate\.(.*)}})|U', function($m4is_a157 ){
$m4is_l9671 =$m4is_a157[2];
 $m4is_f087 =get_current_user_id();
 $m4is_u6591 =m4is_q82::m4is_k660($m4is_f087, 'affiliate', $m4is_l9671, '' );
 return $m4is_u6591;
 
}, $m4is_u32416 );
 return $m4is_u32416;
 
}private static 
function m4is_j520(string $m4is_u32416 ): string {
if(stripos($m4is_u32416, '{{contact.' )=== false ){
return $m4is_u32416;
 
}$m4is_u32416 =preg_replace_callback('|({{contact\.(.*)}})|U', function($m4is_a157 ){
$m4is_l9671 =$m4is_a157[2];
 $m4is_u6591 =m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', $m4is_l9671, '' );
 return $m4is_u6591;
 
}, $m4is_u32416 );
 return $m4is_u32416;
 
}private static 
function m4is_u069(string $m4is_u32416 ): string {
if(stripos($m4is_u32416, '{{cookie.' )=== false ){
return $m4is_u32416;
 
}$m4is_u32416 =preg_replace_callback('|({{cookie\.(.*)}})|U', function($m4is_a157 ){
$m4is_l9671 =$m4is_a157[2];
 $m4is_u6591 =self::m4is_g4037($_COOKIE, $m4is_l9671 );
 return $m4is_u6591;
 
}, $m4is_u32416 );
 return $m4is_u32416;
 
}private static 
function m4is_s3521(string $m4is_u32416 ): string {
if(stripos($m4is_u32416, '{{date.' )=== false ){
return $m4is_u32416;
 
}$m4is_u32416 =preg_replace_callback('|({{date\.(.*)}})|U', function($m4is_a157 ){
$date_format =$m4is_a157[2];
 return date($date_format );
 
}, $m4is_u32416 );
 return $m4is_u32416;
 
}private static 
function m4is_c069(string $m4is_u32416 ): string {
if(stripos($m4is_u32416, '{{get.')=== false ){
return $m4is_u32416;
 
}$m4is_u32416 =preg_replace_callback('|({{get\.(.*)}})|U', function($m4is_a157 ){
$m4is_l9671 =$m4is_a157[2];
 $m4is_u6591 =self::m4is_g4037($_GET, $m4is_l9671 );
 return $m4is_u6591;
 
}, $m4is_u32416 );
 return $m4is_u32416;
 
} private static 
function m4is_f982(string $m4is_u32416 ): string {
if(stripos($m4is_u32416, '{{post.')=== false ){
return $m4is_u32416;
 
}$m4is_u32416 =preg_replace_callback('|({{post\.(.*)}})|U', function($m4is_a157 ){
$m4is_l9671 =$m4is_a157[2];
 $m4is_u6591 =self::m4is_g4037($_POST, $m4is_l9671 );
 return $m4is_u6591;
 
}, $m4is_u32416 );
 return $m4is_u32416;
 
} private static 
function m4is_o957(string $m4is_d659 ): string {
$m4is_n95268 =['{{::', '::}}', '<:', ':>'];
 $m4is_k59 =['[', ']', '[', ']'];
 $m4is_d659 =str_replace($m4is_n95268, $m4is_k59, $m4is_d659 );
  return $m4is_d659;
 
} private static 
function m4is_n91735(string $m4is_d659 ): string {
return str_ireplace('{{ip_address}}', m4is_a01587::m4is_y342(), $m4is_d659 );
 
} private static 
function m4is_q57(string $m4is_u32416 ): string {
if(stripos($m4is_u32416, '{{random.')=== false ){
return $m4is_u32416;
 
}$m4is_u32416 =preg_replace_callback('|({{random\.(.*)}})|U', function($m4is_a157 ){
$m4is_y64308 =explode(',', $m4is_a157[2]);
 return mt_rand((int) $m4is_y64308[0], (int) $m4is_y64308[1]);
 
}, $m4is_u32416 );
 return $m4is_u32416;
 
} private static 
function m4is_s26(string $m4is_u32416 ): string {
if(stripos($m4is_u32416, '{{field.')=== false ){
return $m4is_u32416;
 
}$m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_u32416 =preg_replace_callback('|({{field\.(.*)}})|U', function($m4is_a157 )use ($m4is_f087 ){
$m4is_l9671 =$m4is_a157[2];
 if($m4is_f087 ){
return htmlspecialchars(self::$m4is_r1546->m4is_d842($m4is_l9671 ));
 
}return empty(self::$m4is_b7682[$m4is_l9671])? '' : self::$m4is_b7682[$m4is_l9671];
 
}, $m4is_u32416 );
 return $m4is_u32416;
 
} private static 
function m4is_z5086(string $m4is_u32416 ): string {
if(stripos($m4is_u32416, '{{usermeta.')=== false ){
return $m4is_u32416;
 
}$m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_u32416 =preg_replace_callback('|({{usermeta\.(.*)}})|U', function($m4is_a157 ){
$m4is_b39640 =!empty($m4is_a157[2])? strtolower($m4is_a157[2]): '';
 $m4is_f087 =get_current_user_id();
 if($m4is_f087 > 0 &&$m4is_b39640 > '' ){
return empty($m4is_b39640 )? '' : get_user_meta($m4is_f087, $m4is_b39640, true );
 
}else{
return '';
 
}
}, $m4is_u32416 );
 return $m4is_u32416;
 
} private static 
function m4is_h90(string $m4is_u32416 ): string {
if(stripos($m4is_u32416, '{{session.')=== false ){
return $m4is_u32416;
 
}$m4is_u32416 =preg_replace_callback('|({{session\.(.*)}})|U', function($m4is_a157 ){
$m4is_l9671 =$m4is_a157[2];
 $m4is_u6591 =self::m4is_g4037($_SESSION, $m4is_l9671 );
 return $m4is_u6591;
 
}, $m4is_u32416 );
 return $m4is_u32416;
 
} static 
function m4is_v1740($m4is_y642 =[], $m4is_l62046 =[], $m4is_q9650 ='' ){
$m4is_l62046 =array_merge((array)$m4is_y642, (array)$m4is_l62046 );
 $m4is_k98670 =empty($m4is_q9650 )? 'shortcode_atts' : "shortcode_atts_{$m4is_q9650
}";
 return apply_filters($m4is_k98670, $m4is_l62046, $m4is_l62046, $m4is_y642, $m4is_q9650 );
 
} public static 
function m4is_b48($m4is_l62046 =[], array $m4is_y642 =[]){
$m4is_o498 ='';
 if(empty($m4is_l62046[0])||$m4is_l62046[0]!== 'showatts' ){
return false;
 
}if(empty($m4is_y642 )){
$m4is_o498 ='N/A';
 
}else{
$m4is_o498 =implode(',', array_keys($m4is_y642 ));
 
}return $m4is_o498;
 
}
}

