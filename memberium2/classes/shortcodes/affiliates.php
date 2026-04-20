<?php

/**
 * Copyright (c) 2017-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 m4is_j06::m4is_h269();
 final 
class m4is_j06 {
static private $m4is_r1546;
 static private $m4is_r9613;
 static private $m4is_h21895;
 static private $m4is_r02639;
  static 
function m4is_h269(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname');
 self::$m4is_h21895 =(int) self::$m4is_r1546->m4is_z56();
 self::$m4is_r02639 =!m4is_s52::m4is_w74();
 
} static 
function m4is_k257($m4is_l62046, string $m4is_t09761 ='', string $m4is_v3458 =''): string {
static $m4is_f950 =0;
 if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()||!is_singular()){
return '';
 
}$m4is_f950++;
 $m4is_y642 =['class' =>'', 'label' =>'Open Affiliate Dashboard', 'style' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642));
 
}$m4is_h21895 =self::$m4is_h21895;
 if(empty(self::$m4is_h21895 )){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_t6601 =m4is_z13097::m4is_y876(self::$m4is_h21895 );
 $m4is_a566 =m4is_z13097::m4is_d95($m4is_t6601);
 $m4is_u6527 =isset($m4is_a566['Status'])? $m4is_a566['Status']: 0;
 $m4is_d9528 =isset($m4is_a566['AffCode'])? $m4is_a566['AffCode']: '';
 $m4is_z18 =isset($m4is_a566['Password'])? $m4is_a566['Password']: '';
 if(empty($m4is_u6527)||empty($m4is_d9528)||empty($m4is_z18)){
return '';
 
}$m4is_r9613 =self::$m4is_r9613;
 $m4is_l91805 =new stdClass;
 $m4is_l91805->form_name ="memb_affiliate_login_{$m4is_f950
}";
 $m4is_l91805->appname =self::$m4is_r9613;
 $m4is_l91805->action =sprintf('https://%s.infusionsoft.com/j_spring_security_check', self::$m4is_r9613 );
 $m4is_l91805->affiliate_code =$m4is_d9528;
 $m4is_l91805->affiliate_password =$m4is_z18;
 $m4is_l91805->button_label =$m4is_l62046['label'];
 $m4is_l91805->button_style =$m4is_l62046['style'];
 $m4is_l91805->button_class =$m4is_l62046['class'];
 m4is_j586::m4is_x7134();
 return m4is_f61::m4is_l0659($m4is_v3458, $m4is_l62046, $m4is_t09761, $m4is_v3458, $m4is_l91805);
 
} static 
function m4is_u80($m4is_l62046, string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'fields' =>'', 'htmlattr' =>'', 'separator' =>' ', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 if(empty($m4is_l62046['fields'])){
return '';
 
}$m4is_h21895 =self::$m4is_h21895;
 if(empty(self::$m4is_h21895 )){
return '';
 
}if(!m4is_s52::m4is_w74()){
return '';
 
}$m4is_o498 ='';
 $m4is_t6601 =m4is_z13097::m4is_y876($m4is_h21895);
 if($m4is_t6601){
$m4is_a566 =array_change_key_case(m4is_z13097::m4is_d95($m4is_t6601));
 if(!empty($m4is_a566)){
$m4is_n0216 =array_filter(explode(',', $m4is_l62046['fields']));
 foreach ($m4is_n0216 as $m4is_r637){
$m4is_r637 =strtolower(trim($m4is_r637));
 $m4is_w86 =!empty($m4is_a566[$m4is_r637])? $m4is_a566[$m4is_r637]: '';
 $m4is_o498 .= $m4is_w86;
 if(count($m4is_n0216 )> 1){
$m4is_o498 .= $m4is_l62046['separator'];
 
}
}
}
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} static 
function m4is_q5489($m4is_l62046, string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_j90523 =[];
 m4is_j586::m4is_x7134();
      $m4is_h21895 =self::$m4is_r1546->m4is_z56();
 $m4is_t6601 =m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'affiliate', 'id', 0 );
  $m4is_y642 =['affiliate_id' =>$m4is_t6601, 'after' =>'', 'before' =>'', 'capture' =>'', 'default' =>'0.00', 'fields' =>'running_balance', 'format' =>'USD', 'htmlattr' =>'', 'separator' =>'', 'txtfmt' =>'',  ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 if(empty($m4is_l62046['affiliate_id'])){
return '';
 
}$m4is_s9162 =[$m4is_l62046['affiliate_id']];
 $m4is_l62046['fields']=array_filter(explode(',', strtolower($m4is_l62046['fields'])));
 $m4is_o498 ='';
 $m4is_x3066 =['amountearned' =>'amount_earned', 'runningbalance' =>'running_balance', ];
 foreach($m4is_l62046['fields']as $m4is_l9671 =>$m4is_v586 ){
if(array_key_exists($m4is_v586, $m4is_x3066 )){
$m4is_l62046['fields'][$m4is_l9671]=$m4is_x3066[$m4is_v586];
 
}
}$m4is_b53866 =m4is_z13097::m4is_x4673($m4is_l62046['affiliate_id']);
 $m4is_b53866 =array_change_key_case($m4is_b53866, CASE_LOWER);
 $m4is_j90523[$m4is_l62046['affiliate_id']]=$m4is_b53866;
 if(empty($m4is_j90523[$m4is_l62046['affiliate_id']])){
if(!is_array($m4is_b53866 )){
return '';
 
}
}else{
$m4is_b53866 =$m4is_j90523[$m4is_l62046['affiliate_id']];
 
}if(class_exists('NumberFormatter' )){
$m4is_m538 =new NumberFormatter('en_US', NumberFormatter::CURRENCY);
 
}if(is_array($m4is_l62046['fields'])){
foreach($m4is_l62046['fields']as $field){
if(class_exists('NumberFormatter')){
$m4is_o26 =$m4is_m538->formatCurrency($m4is_b53866[$field], $m4is_l62046['format']);
 
}else{
$m4is_o26 =$m4is_b53866[$field];
 
}$m4is_o498 .= (isset($m4is_b53866[$field])? $m4is_o26 : $m4is_l62046['default']). $m4is_l62046['separator'];
 
}
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} static 
function m4is_w91($m4is_l62046, string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'txtfmt' =>'', 'not' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_c31 =m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'affiliate', 'status', '' );
 if(!empty($m4is_c31)){
$m4is_u6591 =(boolean) $m4is_c31 ||self::$m4is_r1546->m4is_v461();
 
}else{
$m4is_u6591 =0;
 
}$m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, TRUE, $m4is_u6591);
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
} static 
function m4is_w9850($m4is_l62046, string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
}if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return 'n/a';
 
}m4is_j586::m4is_x7134();
 self::$m4is_r1546->m4is_b068();
 return '';
 
} static 
function m4is_n7631($m4is_l62046, string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['capture' =>'', 'affiliate_id' =>false, 'not' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['not']=!empty($m4is_l62046['not']);
 $m4is_w32604 =empty($_COOKIE['affiliate_id'])? 0 : (int) $_COOKIE['affiliate_id'];
 $m4is_w32604 or $m4is_w32604 =(int) get_user_meta(self::$m4is_r1546->m4is_x66(), 'memberium/keap/affiliate/id', true );
 if(self::$m4is_r1546->m4is_v461()){
$m4is_t265 =true;
 
}else{
if(stripos($m4is_v3458, '_not_')){
$m4is_l62046['not']=true;
 
}if($m4is_l62046['affiliate_id']&&$m4is_l62046['affiliate_id']== $m4is_w32604){
$m4is_t265 =true;
 
}else{
$m4is_t265 =false;
 
}
}if($m4is_l62046['not']){
$m4is_t265 =!$m4is_t265;
 
}$m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_t265);
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
} static 
function m4is_h76628($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'date_format' =>'', 'default' =>'', 'fields' =>'', 'htmlattr' =>'', 'separator' =>' ', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
  if(empty($m4is_l62046['fields'])){
return '';
 
}$m4is_h21895 =self::$m4is_r1546->m4is_z56();
  $m4is_t6601 =self::$m4is_r1546->m4is_g72069($m4is_h21895 );
 if($m4is_t6601 ){
$m4is_h63 =m4is_z13097::m4is_d95($m4is_t6601 );
 $m4is_h21895 =isset($m4is_h63['ContactId'])? (int) $m4is_h63['ContactId']: 0;
 
}else{
$m4is_h21895 =0;
 
}if(!$m4is_h21895 ){
return '';
 
}$m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 $m4is_j90523 =m4is_q82::m4is_d6758($m4is_f087, 'keap', 'contact' );
 $m4is_n0216 =explode(',', $m4is_l62046['fields']);
 $m4is_s12460 =count($m4is_n0216);
 $m4is_v81756 =0;
  $m4is_o498 ='';
 foreach ($m4is_n0216 as $m4is_r637){
$m4is_r637 =strtolower(trim($m4is_r637));
 if(isset($m4is_j90523[$m4is_r637])){
$m4is_o076 =strtotime($m4is_j90523[$m4is_r637]);
 if($m4is_l62046['date_format']== '' ||$m4is_o076 == 0){
$m4is_w86 =isset($m4is_j90523[$m4is_r637])? $m4is_j90523[$m4is_r637]: '';
 
}else{
$m4is_w86 =date($m4is_l62046['date_format'], strtotime($m4is_j90523[$m4is_r637]));
 
}
}else{
$m4is_w86 =$m4is_l62046['default'];
 
}$m4is_o498 .= $m4is_w86;
 if($m4is_s12460 > 1){
$m4is_o498 .= $m4is_l62046['separator'];
 
}
}if($m4is_s12460 > 1 &&strlen($m4is_l62046['separator'])> 0){
$m4is_o498 =substr($m4is_o498, 0, -strlen($m4is_l62046['separator']));
 
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}
}

