<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83')||die();
 m4is_d56423::m4is_h269();
 final 
class m4is_d56423 {
static private $m4is_r1546;
 static private $m4is_e6426;
 static 
function m4is_h269(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_e6426 =self::$m4is_r1546->m4is_b32198();
 
}static 
function m4is_e62($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ){
if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return;
 
}static $m4is_v4821 =0;
 $output ='';
 $m4is_r08743 =['memb_list_shortcodes', 'memb_debug', ];
 $m4is_o9361 =$GLOBALS['shortcode_tags'];
 ksort($m4is_o9361 );
 foreach ($m4is_o9361 as $m4is_c39 =>$m4is_a1056 ){
$m4is_y1302 =stripos($m4is_c39, 'memb_')!== false ||$m4is_y1302 =stripos($m4is_c39, 'umbrella_')!== false;
 if($m4is_y1302 ){
$m4is_v4821++;
 echo "<strong>[{$m4is_c39
}]</strong><br />";
 echo do_shortcode("[{$m4is_c39
} showatts]"), '<br /><br />';
 
}
}return $output;
 
}static 
function m4is_y3061($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ){
$m4is_l91805 =self::$m4is_r1546->m4is_e07(false );
 self::$m4is_r1546->m4is_q98(true );
 return '<pre>' . print_r($m4is_l91805, true ). '</pre>';
 
}static 
function m4is_q35($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
$m4is_l62046 =(array)$m4is_l62046;
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return '';
 
}if($_SERVER['REMOTE_ADDR']<> '127.0.0.1' ){
return '';
 
}   ini_set('display_errors', 0 );
 return '';
 
}
}

