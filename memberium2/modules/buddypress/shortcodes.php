<?php

/**
* Copyright (c) 2018-2022 David J Bullock
* Web Power and Light
*/


  class_exists('m4is_r83')||die();
 m4is_s5403::m4is_h269();
 final 
class m4is_s5403 {
static private $m4is_f4218;
 static private $m4is_r1546;
 static 
function m4is_h269(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_f4218 ='memberium';
 
}static 
function m4is_p917($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 =''): string {
$m4is_m60 =current_user_can('manage_options' );
 $m4is_y642 =['capture' =>'', 'not' =>false, 'txtfmt' =>'', 'type' =>'', ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_f087 =self::$m4is_r1546->m4is_x66();
 $m4is_l62046['type']=strtolower(trim($m4is_l62046['type']));
 $m4is_l62046['not']=!empty($m4is_l62046['not']);
 if($m4is_f087){
if(!$m4is_m60){
if(function_exists('bp_get_member_type')){
$m4is_h765 ='';
 $m4is_m63284 =bp_get_member_type($m4is_f087, false );
 if(in_array($m4is_l62046['type'], $m4is_m63284 )){
$m4is_m60 =true;
 
};
 
}
}
}if($m4is_l62046['not']== true ){
$m4is_m60 =!$m4is_m60;
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, TRUE, $m4is_m60 );
 return m4is_f61::m4is_u150(false, $m4is_t09761, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
}static 
function m4is_z68967($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
$m4is_y642 =['img_size' =>'120', ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_y66291 =['type' =>'alphabetical', 'per_page' =>999 ];
 $m4is_b42 =$m4is_t87 =BP_Groups_Group::get($m4is_y66291 );
 echo '<pre>', print_r($m4is_b42, true ), '</pre>';
 
}
}

