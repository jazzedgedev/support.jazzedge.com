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
class m4is_j71492 {
static private $m4is_r1546;
 static private $m4is_r9613;
 static private $m4is_h21895;
 static private $m4is_r02639;
 static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname');
 self::$m4is_r02639 =!m4is_s52::m4is_w74();
 self::$m4is_h21895 =(int) self::$m4is_r1546->m4is_z56();
 
} static 
function m4is_h93($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
 if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['url' =>'', 'background' =>0, 'resync' =>0, 'display' =>false, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!self::$m4is_h21895 ){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 if(!empty($m4is_l62046['url'])){
$m4is_u076 =['contactId' =>self::$m4is_h21895, 'Email' =>m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', 'email', '' ), ];
 $m4is_u6591 =wp_remote_post($m4is_l62046['url'], ['body' =>$m4is_u076]);
 
}if($m4is_l62046['display']){
return isset($m4is_u6591['body'])? $m4is_u6591['body']: '';
 
}return '';
 
} static 
function m4is_o37($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_b87 =0;
 if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_b87++;
 $m4is_y642 =['style' =>'link', 'appname' =>self::$m4is_r9613, 'url' =>'/', 'css_id' =>$m4is_b87, 'button_text' =>'Login', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_o498 ='';
 $m4is_y193 =m4is_q82::m4is_k660($m4is_f087, 'contact', 'email', '' );
 $m4is_m676 =m4is_q82::m4is_k660($m4is_f087, 'contact', 'password', '' );
 $m4is_l91805 =new stdClass;
 if($m4is_l62046['style']== 'button' ){
$m4is_l91805->action ="https://{$m4is_l62046['appname']
}.customerhub.net/web_services/auto_login";
 $m4is_l91805->appname =$m4is_l62046['appname'];
 $m4is_l91805->username =$m4is_y193;
 $m4is_l91805->password =$m4is_m676;
 $m4is_l91805->button_text =$m4is_l62046['button_text'];
 $m4is_l91805->css_id =$m4is_l62046['css_id'];
 $m4is_l91805->url =$m4is_l62046['url'];
 $m4is_o498 =m4is_f61::m4is_l0659($m4is_v3458, $m4is_l62046, $m4is_t09761, $m4is_v3458, $m4is_l91805 );
 
}else{
$m4is_o498 ='https://' . $m4is_l62046['appname']. '.customerhub.net/web_services/auto_login?email='. urlencode($m4is_y193). '&password='. urlencode($m4is_m676). '&to=' . urlencode($m4is_l62046['url']);
 
}return $m4is_o498;
 
}static 
function m4is_g82($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'htmlattr' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_x8341 =defined('MEMBERIUM_BETA' )? (bool) constant('MEMBERIUM_BETA' ): false;
 $m4is_o498 =self::$m4is_r1546->m4is_w45(). ($m4is_x8341 ? 'beta' : '' );
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_f76($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'htmlattr' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_o498 =m4is_s52::m4is_w74()? _x('Valid', 'memb_license_status', 'memberium'): _x('Invalid', 'memb_license_status', 'memberium');
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_d218($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
    static $m4is_t6056 =0;
 $m4is_y642 =['id' =>'', 'size' =>256, 'data' =>trim($m4is_t09761), 'style' =>'', 'class' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 if(empty($m4is_l62046['data'])){
return '';
 
}$m4is_t6056++;
 $m4is_p576 =empty($m4is_l62046['id'])? 'qrcode_' . $m4is_t6056 : $m4is_l62046['id'];
 $m4is_l62046['style']="width:{$m4is_l62046['size']
}px;height:{$m4is_l62046['size']
}px; display:block; {$m4is_l62046['style']
}";
 wp_enqueue_script('jquery-qrcode', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js', null, self::$m4is_r1546->m4is_w45(), true);
 $m4is_o498 ="<p><div id=\"{$m4is_p576
}\" style=\"{$m4is_l62046['style']
}\" class=\"{$m4is_l62046['class']
}\"></div></p>";
 $m4is_o498 .= '
			<script>
			jQuery(document).ready(function() {
				jQuery("#' . $m4is_p576 . '").qrcode({
					text : "' . $m4is_l62046['data']. '",
					height : "' . $m4is_l62046['size']. '",
					width : "' . $m4is_l62046['size']. '"
				});
			});
			</script>';
 return $m4is_o498;
 
} static 
function m4is_d28716($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
    $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'feedurl' =>'http://feeds.feedburner.com/brainyquote/QUOTEBR', 'htmlattr' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_o498 ='';
 if($m4is_l62046['feedurl']> ''){
 $m4is_e15248 =fetch_feed($m4is_l62046['feedurl']);
 if(!is_a($m4is_e15248, 'WP_Error')){
 $m4is_r1602 =$m4is_e15248->get_item_quantity(1);
 $m4is_k1067 =$m4is_e15248->get_items(0, $m4is_r1602);
 $m4is_o498 ='<span class="memberium_quotd_quote">' . $m4is_k1067[0]->get_description(). '<span> ' . '<span class="memberium_quotd_attribution">' . $m4is_k1067[0]->get_title(). '<span>';
 
}
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_s664($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['capture' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}ob_start();
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_t09761 =trim($m4is_t09761 );
 $m4is_y28534 =self::$m4is_r1546->m4is_g316(). 'libraries/eval.php';
 if(!class_exists('m4is_b54' )){
error_log("Memberium: [error] Eval Function Class file missing {$m4is_y28534
}");
 if(self::$m4is_r1546->m4is_v461()){
return "<p>ERROR: Eval Function Class file missing {$m4is_y28534
}</p>";
 
}
}else{
$m4is_u6591 =m4is_b54::m4is_i3025($m4is_t09761, function($m4is_u6860, $m4is_v3458 ){
if(self::$m4is_r1546->m4is_v461()){
global $post;
 $m4is_l073 =$m4is_u6860->getMessage();
 $m4is_s30 =$m4is_u6860->getLine();
 $m4is_b4068 =$post->ID;
 $m4is_a173 ="Error in shortcode contents: {$m4is_l073
} at line {$m4is_s30
} in post ID {$m4is_b4068
}";
 $m4is_v3458 =htmlspecialchars($m4is_v3458);
 error_log($m4is_a173);
 echo "<p><span style='color:red;font-weight:bold;'>Script Error:  </span>{$m4is_a173
} / {$m4is_v3458
}</p>";
 
}
});
 
}unset($m4is_t09761);
 $m4is_o498 =ob_get_clean();
  return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], '', '', '');
 
}static 
function m4is_d076($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
}$m4is_y642 =['field' =>'language', 'default' =>'', 'only' =>'', 'except' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['field']=strtolower($m4is_l62046['field']);
 $m4is_l62046['only']=array_map('strtolower', array_filter(array_map('trim', explode(',', $m4is_l62046['only']))));
 $m4is_l62046['except']=array_map('strtolower', array_filter(array_map('trim', explode(',', $m4is_l62046['except']))));
 $m4is_s53927 =m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', $m4is_l62046['field'], $m4is_l62046['default']);
 $m4is_e85349 =m4is_d86::m4is_w635();
 $m4is_q16492 =(boolean) count($m4is_l62046['only']);
 $m4is_f87 =(boolean) count($m4is_l62046['except']);
 $m4is_e85349 =apply_filters('memberium_language_class_list', $m4is_e85349);
  if(empty($m4is_s53927)){
$m4is_l62046['default']=m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', 'languages', $m4is_l62046['default']);
 
}if($m4is_q16492 ||$m4is_f87){
foreach($m4is_e85349 as $m4is_l9671 =>$m4is_r93654){
$m4is_r93654 =strtolower($m4is_r93654);
 if($m4is_s53927 <> $m4is_r93654){
if($m4is_q16492 &&!in_array($m4is_r93654, $m4is_l62046['only'])){
unset($m4is_e85349[$m4is_l9671]);
 
}if($m4is_r93654 <> $m4is_f87 &&in_array($m4is_r93654, $m4is_l62046['except'])){
unset($m4is_e85349[$m4is_l9671]);
 
}
}
}
}unset($m4is_r93654, $m4is_l62046['except'], $m4is_l62046['only'], $m4is_f87, $m4is_q16492);
 $m4is_o498 ='';
 foreach ($m4is_e85349 as $m4is_p59 =>$m4is_b84573){
$m4is_o498 .= '<option value="' . $m4is_p59 . '" ' . (($m4is_s53927 == strtolower($m4is_p59))? ' selected ' : ' '). '>' . $m4is_b84573 . '</option>';
 
}unset($m4is_p59, $m4is_b84573);
 m4is_j586::m4is_x7134();
 return $m4is_o498;
 
}static 
function m4is_b0163($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ):string {
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['default' =>'America/Los_Angeles', 'except' =>'', 'field' =>'timezone', 'only' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['field']=strtolower($m4is_l62046['field']);
 $m4is_l62046['only']=array_map('strtolower', array_filter(array_map('trim', explode(',', $m4is_l62046['only']))));
 $m4is_l62046['except']=array_map('strtolower', array_filter(array_map('trim', explode(',', $m4is_l62046['except']))));
 $m4is_c296 =strtolower(trim(m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', $m4is_l62046['field'], $m4is_l62046['default'])));
 if(empty($m4is_c296)){
  
}$m4is_u83215 =m4is_q05::m4is_l05674();
 $m4is_q16492 =(boolean) count($m4is_l62046['only']);
 $m4is_f87 =(boolean) count($m4is_l62046['except']);
 $m4is_u83215 =apply_filters('memberium_time_classzone_list', $m4is_u83215);
 $m4is_o498 ='';
 if($m4is_q16492 ||$m4is_f87){
foreach($m4is_u83215 as $m4is_l9671 =>$m4is_j2450){
$m4is_j2450 =strtolower($m4is_j2450);
 if($m4is_c296 <> $m4is_j2450){
if($m4is_q16492 &&!in_array($m4is_j2450, $m4is_l62046['only'])){
unset($m4is_u83215[$m4is_l9671]);
 
}if($m4is_j2450 <> $m4is_f87 &&in_array($m4is_j2450, $m4is_l62046['except'])){
unset($m4is_u83215[$m4is_l9671]);
 
}
}
}
}unset($m4is_j2450, $m4is_l62046['except'], $m4is_l62046['only'], $m4is_f87, $m4is_q16492);
 foreach ($m4is_u83215 as $m4is_m8437 =>$m4is_o61095){
$m4is_o498 .= '<option value="' . $m4is_m8437 . '" ' . (($m4is_c296 == strtolower($m4is_m8437))? ' selected ' : ' '). '>' . $m4is_o61095 . '</option>';
 
}unset($m4is_m8437, $m4is_o61095);
 return $m4is_o498;
 
}static 
function m4is_m68691($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
   $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'default' =>'', 'field' =>'country_name', 'htmlattr' =>'', 'ip_address' =>m4is_a01587::m4is_y342(), 'provider' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['provider']=strtolower(trim($m4is_l62046['provider']));
 $m4is_l62046['field']=strtolower(trim($m4is_l62046['field']));
 $m4is_l62046['ip_address']=empty($m4is_l62046['ip_address'])? m4is_a01587::m4is_y342(): $m4is_l62046['ip_address'];
 $m4is_a9317 =m4is_q6082::m4is_a67($m4is_l62046['ip_address'], $m4is_l62046['provider']);
 if($m4is_l62046['field']== 'country' )$m4is_l62046['field']='country_name';
 if($m4is_l62046['field']== 'province')$m4is_l62046['field']='region_name';
 if($m4is_l62046['field']== 'region' )$m4is_l62046['field']='region_name';
 if($m4is_l62046['field']== 'state' )$m4is_l62046['field']='region_name';
 $m4is_v586 =isset($m4is_a9317[$m4is_l62046['field']])? $m4is_a9317[$m4is_l62046['field']]: $m4is_l62046['default'];
 $m4is_o498 =htmlspecialchars($m4is_v586);
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_c0267($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return 'n/a';
 
}global $wp_embed;
 return do_shortcode($wp_embed->run_shortcode($m4is_t09761 ));
 
}static 
function m4is_r51978($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}if(!m4is_s52::m4is_w74()){
return '';
 
}if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return 'n/a';
 
}m4is_j586::m4is_x7134();
 global $wp_embed;
 $m4is_t09761 =do_shortcode($wp_embed->run_shortcode($m4is_t09761 ));
 return m4is_f61::m4is_b07361($m4is_t09761 );
 
} static 
function m4is_k4508($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['capture' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_o498 =do_shortcode(m4is_f61::m4is_p7451($m4is_t09761 ));
 if(!empty($m4is_l62046['txtfmt'])){
$m4is_o498 =m4is_f61::m4is_n05($m4is_o498, $m4is_l62046['txtfmt']);
 
}if(!empty($m4is_l62046['capture'])){
$m4is_o498 =m4is_f61::m4is_f6513($m4is_o498, $m4is_l62046['capture']);
 
}return $m4is_o498;
 
}static 
function m4is_c62387($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['default' =>'United States', 'except' =>'', 'field' =>'country', 'only' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['field']=strtolower($m4is_l62046['field']);
 $m4is_l62046['only']=array_map('strtolower', array_filter(array_map('trim', explode(',', $m4is_l62046['only']))));
 $m4is_l62046['except']=array_map('strtolower', array_filter(array_map('trim', explode(',', $m4is_l62046['except']))));
 $m4is_d6046 =strtolower(trim(m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', $m4is_l62046['field'], $m4is_l62046['default'])));
 $m4is_q16492 =(boolean) count($m4is_l62046['only']);
 $m4is_f87 =(boolean) count($m4is_l62046['except']);
 $m4is_x18 =apply_filters('memberium/country_list', self::$m4is_r1546->m4is_z40()->getCountries());
 if($m4is_q16492 ||$m4is_f87){
foreach($m4is_x18 as $m4is_l9671 =>$m4is_m79){
$m4is_m79 =strtolower($m4is_m79);
 if($m4is_d6046 <> $m4is_m79){
if($m4is_q16492 &&!in_array($m4is_m79, $m4is_l62046['only'])){
unset($m4is_x18[$m4is_l9671]);
 
}if($m4is_m79 <> $m4is_f87 &&in_array($m4is_m79, $m4is_l62046['except'])){
unset($m4is_x18[$m4is_l9671]);
 
}
}
}
}unset($m4is_m79, $m4is_f87, $m4is_q16492);
 $m4is_o498 ='';
 foreach ($m4is_x18 as $m4is_m79){
$m4is_o498 .= '<option value="' . $m4is_m79 . '" ' . (($m4is_d6046 == strtolower($m4is_m79))? ' selected ' : ' '). '>' . $m4is_m79 . '</option>';
 
}return $m4is_o498;
 
}static 
function m4is_r26038($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'date' =>'', 'htmlattr' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_o498 ='';
 $m4is_t21468 =0;
 if($m4is_l62046['date']> ''){
$m4is_t21468 =(int) m4is_q05::m4is_n706($m4is_l62046['date']);
 
}if($m4is_t21468 < 0){
$m4is_o498 =abs($m4is_t21468 ). $m4is_l62046['before'];
 
}elseif($m4is_t21468 > 0){
$m4is_o498 =abs($m4is_t21468 ). $m4is_l62046['after'];
 
}else{
$m4is_o498 =0;
 
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], '', '');
 
}static 
function m4is_t4932($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_p576 =0;
 if(!m4is_s52::m4is_w74()){
return '';
 
}if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'class' =>'memberium_message', 'names' =>'', 'style' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_p576++;
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['names']=array_filter(array_map('trim', explode(',', $m4is_l62046['names'])));
 $m4is_d7369 =[];
 if(!empty($_SESSION['flash'])){
if(empty($m4is_l62046['names'])){
foreach($_SESSION['flash']as $m4is_l9671 =>$m4is_a173){
$m4is_d7369[]=$m4is_a173;
 unset($_SESSION['flash'][$m4is_l9671]);
 
}
}else{
foreach($m4is_l62046['names']as $name){
if(!empty($_SESSION['flash'][$name])){
$m4is_d7369[]=$name;
 unset($_SESSION['flash'][$name]);
 
}
}
}
}$m4is_o498 ='';
 foreach($m4is_d7369 as $m4is_a173){
if(empty($m4is_l62046['before'])&&empty($m4is_l62046['after'])){
$m4is_o498 .= '<div class="' . $m4is_l62046['class']. '" style="' . $m4is_l62046['style']. '">' . $m4is_a173 . '</div>';
 
}else{
$m4is_o498 .= $m4is_l62046['before']. $m4is_a173 . $m4is_l62046['after'];
 
}
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
}static 
function m4is_n819($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return 'n/a';
 
}return do_shortcode(do_shortcode(m4is_f61::m4is_p7451($m4is_t09761)));
 
}static 
function m4is_l2946($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'htmlattr' =>'', 'value' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_o498 =$m4is_l62046['value'];
 return m4is_f61::m4is_u150(false, $m4is_o498, '', '', $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}static 
function m4is_f3549($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['end' =>1, 'start' =>0, 'step' =>1, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_o498 ='';
  return $m4is_o498;
 
}
}

