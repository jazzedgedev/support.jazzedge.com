<?php

/**
 * Copyright (c) 2017-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_t73 {
private static int $m4is_a625;
 private static object $m4is_r1546;
 private static $m4is_m63284;
  static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_a625 =4;
 m4is_j586::m4is_k751();
 
}   public static 
function m4is_i16236($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(!m4is_s52::m4is_w74()){
return '';
 
} $m4is_j89606 =$m4is_l62046;
 $m4is_l62046['profile']=isset($m4is_l62046['profile'])? strtolower(trim($m4is_l62046['profile'])): 'us-east-1';
 $m4is_x06 =self::m4is_g567($m4is_l62046['profile']);
  if($m4is_x06){
$m4is_y50 =$m4is_l62046['profile'];
 $m4is_w35268 =$m4is_x06['bucket'];
 $m4is_y43917 =(int) $m4is_x06['expiration'];
 $m4is_h540 =$m4is_x06['host'];
 $m4is_u66435 =empty($m4is_x06['region'])? '' : $m4is_x06['region'];
 $m4is_j31547 =$m4is_x06['access_key'];
 $m4is_h3865 =$m4is_x06['secret_key'];
 
}else{
$m4is_y50 ='';
 $m4is_w35268 ='';
 $m4is_y43917 =5;
 $m4is_u66435 ='us-east-1';
 $m4is_h540 ='';
 $m4is_j31547 ='';
 $m4is_h3865 ='';
 
}$m4is_y642 =['action_ids' =>'',  'after' =>'', 'before' =>'', 'bucket' =>$m4is_w35268, 'debug' =>0, 'direct_link' =>'no',  'expiring' =>$m4is_y43917, 'fus_ids' =>'',  'goals' =>'', 'htmlattr' =>'', 'object' =>'', 'profile' =>$m4is_y50, 'protocol' =>'https', 'region' =>$m4is_u66435, 'require_login' =>'no',  'tag_ids' =>'', 'tokens' =>'', 'version' =>self::$m4is_a625,  'class' =>'', 'download' =>'', 'id' =>'', 'rel' =>'', 'style' =>'', 'target' =>'', 'type' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_o498 ='';
  if(strtolower(trim($m4is_l62046['direct_link']))== 'no' ){
$m4is_b627 =empty($_SERVER['REQUEST_URI'])? '' : $_SERVER['REQUEST_URI'];
 $m4is_c05328 =[];
 $m4is_c05328['actionset']=$m4is_l62046['action_ids'];
 $m4is_c05328['debug']=$m4is_l62046['debug'];
 $m4is_c05328['expiring']=$m4is_l62046['expiring'];
 $m4is_c05328['fus']=$m4is_l62046['fus_ids'];
 $m4is_c05328['goals']=$m4is_l62046['goals'];
 $m4is_c05328['object']=$m4is_l62046['object'];
 $m4is_c05328['profile']=$m4is_l62046['profile'];
 $m4is_c05328['protocol']=$m4is_l62046['protocol'];
 $m4is_c05328['region']=$m4is_l62046['region'];
 $m4is_c05328['require_login']=$m4is_l62046['require_login'];
 $m4is_c05328['tags']=$m4is_l62046['tag_ids'];
 $m4is_c05328['tokens']=$m4is_l62046['tokens'];
 $m4is_c05328['version']=$m4is_l62046['version'];
 if(strpos($m4is_b627, '?')=== false){
$m4is_b627 .= '?';
 
}else{
$m4is_b627 .= '&';
 
}$m4is_u076 =base64_encode(serialize($m4is_c05328 ));
 $m4is_n6062 ='memb_s3link=' . $m4is_u076;
  $m4is_k52736 ='verification';
 $m4is_c05328 =$m4is_u076;
 $action_url =wp_nonce_url($m4is_n6062, $m4is_c05328, $m4is_k52736);
   $m4is_c67 =$m4is_b627 . $action_url;
 
}else{
if($m4is_l62046['version']== 3){
$m4is_c67 =self::m4is_i62413($m4is_j31547, $m4is_h3865, $m4is_l62046['protocol'], $m4is_h540, $m4is_l62046['bucket'], $m4is_l62046['object'], $m4is_l62046['expiring']);
 
}elseif($m4is_l62046['version']== 4){
 $m4is_c67 =self::m4is_k678($m4is_j31547, $m4is_h3865, $m4is_l62046['bucket'], $m4is_l62046['object'], $m4is_l62046['expiring'], $m4is_l62046['region']);
 
}
}if(strtolower($m4is_l62046['htmlattr'])== 'all'){
$m4is_b23 =['action_ids', 'after', 'before', 'bucket', 'debug', 'direct_link', 'expiring', 'fus_ids', 'goals', 'htmlattr', 'object', 'profile', 'protocol', 'require_login', 'tag_ids', 'tokens', ];
 $m4is_o498 ='<a href="' . $m4is_c67 . '"';
 foreach($m4is_j89606 as $m4is_o015 =>$m4is_k72){
if(!empty($m4is_j89606[$m4is_o015])&&!in_array($m4is_o015, $m4is_b23)){
$m4is_o498 .= ' ' . $m4is_o015 . '="' . $m4is_k72 . '" ';
 
}
}$m4is_o498 .= '>' . $m4is_t09761 . '</a>';
 $m4is_l62046['htmlattr']='';
 
}else{
$m4is_o498 =$m4is_c67;
 
}return m4is_f61::m4is_u150(false, $m4is_o498, '', '', $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}public static 
function m4is_w1932($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(!m4is_s52::m4is_w74()){
return '';
 
}m4is_j586::m4is_x7134();
 if(is_feed()){
return '';
 
}$m4is_j89606 =$m4is_l62046;
 $m4is_l62046['profile']=isset($m4is_l62046['profile'])? strtolower(trim($m4is_l62046['profile'])): 'us-east-1';
 $m4is_x06 =self::m4is_g567($m4is_l62046['profile']);
  if($m4is_x06){
$m4is_y50 =$m4is_l62046['profile'];
 $m4is_w35268 =$m4is_x06['bucket'];
 $m4is_y43917 =(int) $m4is_x06['expiration'];
 $m4is_h540 =$m4is_x06['host'];
 $m4is_u66435 =$m4is_x06['region'];
 $m4is_j31547 =$m4is_x06['access_key'];
 $m4is_h3865 =$m4is_x06['secret_key'];
 
}else{
$m4is_y50 ='';
 $m4is_w35268 ='';
 $m4is_y43917 =5;
 $m4is_u66435 ='us-east-1';
 $m4is_h540 ='';
 $m4is_j31547 ='';
 $m4is_h3865 ='';
 
}static $m4is_p576 =0;
 $m4is_p576++;
 $m4is_y642 =['autoplay' =>'', 'bucket' =>$m4is_w35268, 'controls' =>'', 'direct_link' =>'no',  'download' =>0, 'expiring' =>$m4is_y43917, 'height' =>0, 'loop' =>'', 'muted' =>'', 'object' =>'', 'poster' =>'', 'preload' =>'', 'profile' =>$m4is_y50, 'protocol' =>'https', 'region' =>$m4is_u66435, 'require_login' =>'no',  'version' =>3,  'src' =>'', 'width' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_c39 ='';
 $m4is_i46 ='';
 $m4is_r65 =[];
 if($m4is_l62046['version']== 3){
$m4is_n6062 =self::m4is_i62413($m4is_j31547, $m4is_h3865, $m4is_l62046['protocol'], $m4is_h540, $m4is_l62046['bucket'], $m4is_l62046['object'], $m4is_l62046['expiring']);
 
}elseif($m4is_l62046['version']== 4){
 $m4is_n6062 =self::m4is_k678($m4is_j31547, $m4is_h3865, $m4is_l62046['bucket'], $m4is_l62046['object'], $m4is_l62046['expires'], $m4is_l62046['region'], $m4is_r65 );
 
}$m4is_w367 =($m4is_l62046['width']> 0)? ' style="width:' . $m4is_l62046['width']. 'px;" ': '';
 $m4is_i46 .= '<div '. $m4is_w367 .' class="wp-video">';
 $m4is_i46 .= '<audio class="wp-audio-shortcode" id="secureaudio-' . $m4is_p576 . '" ';
 if($m4is_l62046['width']> 0){
$m4is_i46 .= ' width="' . $m4is_l62046['width']. '" ';
 
}if($m4is_l62046['height']> 0){
$m4is_i46 .= ' height="' . $m4is_l62046['height']. '" ';
 
}if($m4is_l62046['preload']> ''){
$m4is_i46 .= ' autobuffer="autobuffer" preload="' . $m4is_l62046['preload']. '" ';
 
}if($m4is_l62046['controls']> ''){
$m4is_i46 .= ' controls="controls" ';
 
}if($m4is_l62046['autoplay']> ''){
$m4is_i46 .= ' autoplay="autoplay" ';
 
}if($m4is_l62046['muted']> ''){
$m4is_i46 .= ' muted="muted" ';
 
}if($m4is_l62046['poster']> ''){
$m4is_i46 .= ' poster="' . $m4is_l62046['poster']. '" ';
 
}if(!empty($m4is_l62046['loop'])){
$m4is_i46 .= ' loop="' . $m4is_l62046['loop']. '" ';
 
}if(!$m4is_l62046['download']){
$m4is_i46 .= ' controlsList="nodownload" ';
 
}$m4is_i46 .= '>';
 $m4is_i46 .= '<source src="' . $m4is_n6062 .'" />';
 $m4is_i46 .= '</audio></div>';
 return $m4is_i46;
 
}public static 
function m4is_v09315($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(!m4is_s52::m4is_w74()){
return '';
 
}if(is_feed()){
return '';
 
}m4is_j586::m4is_x7134();
 static $m4is_p576 =0;
 $m4is_p576++;
 $m4is_l62046['profile']=isset($m4is_l62046['profile'])? strtolower(trim($m4is_l62046['profile'])): 'us-east-1';
 $m4is_x06 =self::m4is_g567($m4is_l62046['profile']);
 $m4is_w367 ='';
 if($m4is_x06){
$m4is_y50 =$m4is_l62046['profile'];
 $m4is_w35268 =$m4is_x06['bucket'];
 $m4is_y43917 =(int) $m4is_x06['expiration'];
 $m4is_h540 =$m4is_x06['host'];
 $m4is_u66435 =$m4is_x06['region'];
 $m4is_j31547 =$m4is_x06['access_key'];
 $m4is_h3865 =$m4is_x06['secret_key'];
 
}else{
$m4is_y50 ='';
 $m4is_w35268 ='';
 $m4is_y43917 =5;
 $m4is_u66435 ='us-east-1';
 $m4is_h540 ='';
 $m4is_j31547 ='';
 $m4is_h3865 ='';
 
}$m4is_y642 =['autoplay' =>'', 'bucket' =>$m4is_w35268, 'controls' =>'', 'direct_link' =>'no',  'expiring' =>$m4is_y43917, 'height' =>0, 'loop' =>'', 'muted' =>'', 'object' =>'', 'poster' =>'', 'preload' =>'', 'profile' =>$m4is_y50, 'protocol' =>'https', 'require_login' =>'no',  'src' =>'', 'version' =>3,  'width' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
  $m4is_c39 ='';
 $m4is_i46 ='';
  $m4is_r65 =[];
  if(false &&strtolower(trim($m4is_l62046['direct_link']))== 'no'){
 
}else{
if($m4is_l62046['version']== 3){
$m4is_n6062 =self::m4is_i62413($m4is_j31547, $m4is_h3865, $m4is_l62046['protocol'], $m4is_h540, $m4is_l62046['bucket'], $m4is_l62046['object'], $m4is_l62046['expiring']);
 
}elseif($m4is_l62046['version']== 4){
 $m4is_n6062 =self::m4is_k678($m4is_j31547, $m4is_h3865, $m4is_l62046['bucket'], $m4is_l62046['object'], $m4is_l62046['expiring'], $m4is_l62046['region'], $m4is_r65 );
 
}
}if($m4is_l62046['width']> 0){
$m4is_w367 =' style="width:' . $m4is_l62046['width']. 'px;" ';
 
}$m4is_i46 .= '<div '. $m4is_w367 .' class="wp-video">';
 $m4is_i46 .= '<video class="wp-video-shortcode" id="securevideo-' . $m4is_p576 . '" ';
 if($m4is_l62046['width']> 0){
$m4is_i46 .= ' width="' . $m4is_l62046['width']. '" ';
 
}if($m4is_l62046['height']> 0){
$m4is_i46 .= ' height="' . $m4is_l62046['height']. '" ';
 
}if($m4is_l62046['preload']> ''){
$m4is_i46 .= ' autobuffer="autobuffer" preload="' . $m4is_l62046['preload']. '" ';
 
}if($m4is_l62046['controls']> ''){
$m4is_i46 .= ' controls="controls" ';
 
}if($m4is_l62046['autoplay']> ''){
$m4is_i46 .= ' autoplay="autoplay" ';
 
}if($m4is_l62046['muted']> ''){
$m4is_i46 .= ' muted="muted" ';
 
}if($m4is_l62046['poster']> ''){
$m4is_i46 .= ' poster="' . $m4is_l62046['poster']. '" ';
 
}if($m4is_l62046['loop']> ''){
$m4is_i46 .= ' loop="' . $m4is_l62046['loop']. '" ';
 
}$m4is_i46 .= '>';
 $m4is_i46 .= '<source src="' . $m4is_n6062 .'" />';
 $m4is_i46 .= '</video></div>';
 return $m4is_i46;
 
}   public static 
function m4is_v31(): void {
m4is_j586::m4is_x7134();
 if(!wp_verify_nonce($_GET['verification'], $_GET['memb_s3link'])){
return;
 
}if(is_feed()){
return;
 
}$m4is_x51 =function_exists('is_user_logged_in' )&&is_user_logged_in();
 $m4is_f087 =$m4is_x51 ? self::$m4is_r1546->m4is_x66(): 0;
 $m4is_c05328 =unserialize(base64_decode($_GET['memb_s3link']));
 $m4is_i8169 =(boolean) $m4is_c05328['debug']== 1;
 if($m4is_i8169 ){
self::m4is_x681($m4is_c05328 );
 
}if($m4is_c05328['require_login']== 'yes' &&!$m4is_x51 ){
if($m4is_i8169 ){
echo __LINE__, " - Login Required, returning to referring page";
 echo '<a href="', $_SERVER['HTTP_REFERER'], '">Continue</a>';
 exit;
 
}wp_redirect($_SERVER['HTTP_REFERER']);
 exit;
 
}$m4is_h21895 =self::$m4is_r1546->m4is_z56();
 if($m4is_h21895 ){
self::$m4is_r1546->m4is_u71903($m4is_c05328['actionset'], $m4is_h21895 );
 self::$m4is_r1546->m4is_k59073($m4is_c05328['fus'], $m4is_h21895 );
 self::$m4is_r1546->m4is_t64038($m4is_c05328['goals'], $m4is_h21895 );
 self::$m4is_r1546->m4is_k98($m4is_c05328['tags'], $m4is_h21895 );
 self::$m4is_r1546->m4is_s85($m4is_c05328['tokens']);
 
}$m4is_v08 =self::$m4is_r1546->m4is_j498('remote_files' );
 $m4is_y50 =strtolower(trim($m4is_c05328['profile']));
 if(isset($m4is_v08[$m4is_y50])){
$m4is_w35268 =$m4is_v08[$m4is_y50]['bucket'];
 $m4is_h540 =$m4is_v08[$m4is_y50]['host'];
 $m4is_j31547 =$m4is_v08[$m4is_y50]['access_key'];
 $m4is_h3865 =$m4is_v08[$m4is_y50]['secret_key'];
 if($m4is_i8169){
echo __LINE__, ' - Host: ', $m4is_h540, '<br />';
 echo __LINE__, ' - Bucket: ', $m4is_w35268, '<br />';
 
}
}$m4is_y43917 =$m4is_c05328['expiring'];
 $m4is_o4867 =$m4is_c05328['protocol'];
 $m4is_r0175 =$m4is_c05328['object'];
 $m4is_u66435 =$m4is_c05328['region'];
 if($m4is_c05328['version']== 3 ){
$m4is_n6062 =self::m4is_i62413($m4is_j31547, $m4is_h3865, $m4is_o4867, $m4is_h540, $m4is_w35268, $m4is_r0175, $m4is_y43917 );
 
}elseif($m4is_c05328['version']== 4){
 $m4is_n6062 =self::m4is_k678($m4is_j31547, $m4is_h3865, $m4is_w35268, $m4is_r0175, $m4is_y43917, $m4is_u66435 );
 
}do_action('memberium/s3/link/viewed', $m4is_w35268, $m4is_r0175, $m4is_f087, $m4is_h21895 );
 if($m4is_i8169){
echo __LINE__, ' - URL: <a href="', $m4is_n6062, '">', $m4is_n6062, '</a><br />';
 exit;
 
}else{
wp_redirect($m4is_n6062, 302, 'Memberium Secure S3 Link' );
 exit;
 
}
}    private static 
function m4is_x681(array $m4is_c05328 ): void {
echo __LINE__, ' - Region:                 ', $m4is_c05328['region'], '<br />';
 echo __LINE__, ' - Version:                ', $m4is_c05328['version'], '<br />';
 echo __LINE__, ' - Protocol:               ', $m4is_c05328['protocol'], '<br />';
 echo __LINE__, ' - Require Login?:         ', $m4is_c05328['require_login'], '<br />';
 echo __LINE__, ' - Set Actionset:          ', $m4is_c05328['actionset'], '<br />';
 echo __LINE__, ' - Set Debug:              ', $m4is_c05328['debug'], '<br />';
 echo __LINE__, ' - Set Expiration:         ', $m4is_c05328['expiring'], ' seconds<br />';
 echo __LINE__, ' - Set Follow Up Sequence: ', $m4is_c05328['fus'], '<br />';
 echo __LINE__, ' - Set Goals:              ', $m4is_c05328['goals'], '<br />';
 echo __LINE__, ' - Set Object:             ', $m4is_c05328['object'], '<br />';
 echo __LINE__, ' - Set Profile:            ', $m4is_c05328['profile'], '<br />';
 echo __LINE__, ' - Set Tags:               ', $m4is_c05328['tags'], '<br />';
 echo __LINE__, ' - Tokens:                 ', $m4is_c05328['tokens'], '<br />';
 
} private static 
function m4is_g567(string $m4is_k52736 ){
$m4is_m63284 =m4is_r83::m4is_c26()->m4is_j498('remote_files' );
 return isset($m4is_m63284[$m4is_k52736])? $m4is_m63284[$m4is_k52736]: false;
 
} private static 
function m4is_b8057($m4is_l9671, $m4is_l91805, $m4is_s4625 =64 ){
if(strlen($m4is_l9671)> $m4is_s4625){
$m4is_l9671 =pack('H*', sha1($m4is_l9671));
 
}$m4is_l9671 =str_pad($m4is_l9671, $m4is_s4625, chr(0x00));
 $m4is_o60859 =str_repeat(chr(0x36), $m4is_s4625);
 $m4is_r569 =str_repeat(chr(0x5c), $m4is_s4625);
 $m4is_s96762 =pack('H*', sha1(($m4is_l9671 ^ $m4is_r569). pack('H*', sha1(($m4is_l9671 ^ $m4is_o60859). $m4is_l91805))));
 return base64_encode($m4is_s96762);
 
} private static 
function m4is_i62413($m4is_j31547, $m4is_h3865, $m4is_o4867, $m4is_h540, $m4is_w35268, $m4is_d04266, $m4is_r6654 =30 ){
$m4is_x91754 =time()+ (int) $m4is_r6654;
 $m4is_d04266 =str_replace('%2F', '/', rawurlencode($m4is_d04266 =ltrim($m4is_d04266, '/')));
 $m4is_k148 ='/'. $m4is_w35268 .'/'. $m4is_d04266;
 $m4is_t06146 =implode("\n", $pieces =['GET', null, null, $m4is_x91754, $m4is_k148]);
 $m4is_o31859 =self::m4is_b8057($m4is_h3865, $m4is_t06146 );
  $url =sprintf('%s://%s.%s/%s', $m4is_o4867, $m4is_w35268, $m4is_h540, $m4is_d04266);
 $qs =http_build_query($pieces =['AWSAccessKeyId' =>$m4is_j31547, 'Expires' =>$m4is_x91754, 'Signature' =>$m4is_o31859, ]);
 return $url . '?' . $qs;
 
} private static 
function m4is_e85709(string $m4is_w35268 ){
$m4is_m63284 =self::$m4is_r1546->m4is_j498('remote_files' );
 $m4is_u66435 ='';
 $m4is_h540 ='';
 foreach($m4is_m63284 as $m4is_y50){
if($m4is_y50['bucket']== $m4is_w35268){
$m4is_u66435 =empty($m4is_y50['region'])? '' : $m4is_y50['region'];
 $m4is_h540 =empty($m4is_y50['host'])? '' : $m4is_y50['host'];
 if($m4is_h540 == 's3.amazonaws.com' &&empty($m4is_u66435)){
$m4is_u66435 ='us-east-1';
 
}break;
 
}
}if(empty($m4is_u66435)){
if(substr($m4is_h540, 0, 3)=== 's3-'){
$m4is_u66435 =substr($m4is_h540, 3, strpos($m4is_h540, '.', 3)- 3);
 
}if(empty($m4is_u66435)){
$m4is_u66435 ='us-east-1';
 
}
}return $m4is_u66435;
 
} private static 
function m4is_k678($m4is_j31547, $m4is_h3865, $m4is_w35268, $m4is_l63156, $m4is_x91754 =0, $m4is_u66435 ='us-east-1', $m4is_r65 =[]){
if(substr($m4is_l63156, 0, 1 )!== '/' ){
$m4is_l63156 ='/' . $m4is_l63156;
 
}if(empty($m4is_u66435)){
$m4is_u66435 =self::m4is_e85709($m4is_w35268 );
 
}$m4is_i1903 =str_replace('%2F', '/', rawurlencode($m4is_l63156 ));
 $m4is_c617 =[];
 foreach ($m4is_r65 as $m4is_l9671 =>$m4is_v586 ){
$m4is_l9671 =strtolower($m4is_l9671 );
 $m4is_c617[$m4is_l9671]=$m4is_v586;
 
}if(!array_key_exists('host', $m4is_c617 )){
$m4is_u66435 =empty($m4is_u66435)? '' : $m4is_u66435;
 $m4is_c617['host']=($m4is_u66435 == 'us-east-1')? "{$m4is_w35268
}.s3.amazonaws.com" : "{$m4is_w35268
}.s3-{$m4is_u66435
}.amazonaws.com";
 
}ksort($m4is_c617);
 $m4is_i7241 ='';
 foreach ($m4is_c617 as $m4is_l9671 =>$m4is_v586){
$m4is_i7241 .= $m4is_l9671 . ':' . trim($m4is_v586). "\n";
 
}$m4is_u8071 =implode(';', array_keys($m4is_c617));
 $m4is_q4296 =time();
 $m4is_x7625 =gmdate('Ymd', $m4is_q4296);
 $m4is_o963 =$m4is_x7625 . 'T000000Z';
 $m4is_o963 =$m4is_x7625 . 'T' . gmdate('His', $m4is_q4296). 'Z';
 $m4is_z84626 ='AWS4-HMAC-SHA256';
 $m4is_f64176 ="$m4is_x7625/$m4is_u66435/s3/aws4_request";
 $m4is_c45 =['X-Amz-Algorithm' =>$m4is_z84626, 'X-Amz-Credential' =>$m4is_j31547 . '/' . $m4is_f64176, 'X-Amz-Date' =>$m4is_o963, 'X-Amz-SignedHeaders' =>$m4is_u8071 ];
 if($m4is_x91754 > 0){
$m4is_c45['X-Amz-Expires']=$m4is_x91754;
 
}ksort($m4is_c45);
 $m4is_l1668 =[];
 foreach ($m4is_c45 as $m4is_l9671 =>$m4is_v586){
$m4is_l1668[]=rawurlencode($m4is_l9671). '=' . rawurlencode($m4is_v586);
 
}$m4is_n16263 =implode('&', $m4is_l1668);
 $m4is_e41693 ="GET\n$m4is_i1903\n$m4is_n16263\n$m4is_i7241\n$m4is_u8071\nUNSIGNED-PAYLOAD";
 $m4is_w2601 ="$m4is_z84626\n$m4is_o963\n$m4is_f64176\n" . hash('sha256', $m4is_e41693, false);
 $m4is_u659 =hash_hmac('sha256', 'aws4_request', hash_hmac('sha256', 's3', hash_hmac('sha256', $m4is_u66435, hash_hmac('sha256', $m4is_x7625, 'AWS4' . $m4is_h3865, true), true), true), true);
 $m4is_o31859 =hash_hmac('sha256', $m4is_w2601, $m4is_u659);
 $m4is_n6062 ='https://' . $m4is_c617['host']. $m4is_i1903 . '?' . $m4is_n16263 . '&X-Amz-Signature=' . $m4is_o31859;
 return $m4is_n6062;
 
}
}

