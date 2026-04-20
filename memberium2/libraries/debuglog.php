<?php

/**
 * Copyright (c) 2018-4 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_g64 {
public const M4IS_Z641 =1;
 public const M4IS_W94572 =2;
 public const M4IS_Z409 =3;
 public const M4IS_U75 =4;
 private static int $m4is_r4637;
 private static array $m4is_i1645;
 private 
function __construct(){
 
} public static 
function m4is_c961(): void {
self::$m4is_r4637 =0;
 self::$m4is_i1645 =[self::ERROR =>'ERROR', self::WARNING =>'WARNING', self::INFO =>'INFO', self::DEBUG =>'DEBUG', ];
 
} public static 
function m4is_e276(int $m4is_c5468 ): void {
self::$m4is_r4637 =$m4is_c5468;
 
} static 
function m4is_n669(string $m4is_c5468, string $m4is_a173 ): void {
if(self::$m4is_r4637 > $m4is_c5468 ){
return;
 
}error_log(sprintf('Memberium: [%s] %s', $m4is_c5468, $m4is_a173 ));
 
} static 
function m4is_f66924(string $m4is_k86914 ='', string $m4is_a1056 ='', int $m4is_t42917 =0, string $m4is_d3069 ='', $m4is_l91805 ='' ): void {
if(isset($_GET['doing_wp_cron'])){
return;
 
}if(is_admin()){
return;
 
}global $user;
 $m4is_g53684 =$_SERVER['REMOTE_ADDR']. '::' . isset($_SERVER['REQUEST_TIME_FLOAT'])? $_SERVER['REQUEST_TIME_FLOAT']: $_SERVER['REQUEST_TIME'];
 $m4is_o498 =$m4is_g53684 . ' :: ' . microtime(true);
 $m4is_o498 .= ' :: ' . (function_exists('get_current_user_id')? get_current_user_id(): 0);
 if(function_exists('current_filter')){
$m4is_o498 .= ' :: ' . current_filter();
 
}$m4is_o498 .= ' :: ';
 $m4is_o498 .= basename($m4is_k86914). ' -> ' . $m4is_a1056 . ' -> ' . $m4is_t42917 . " :: ";
 if(!empty($m4is_l91805)){
$m4is_o498 .= $m4is_d3069 . ' = ';
 if(is_array($m4is_l91805)||is_object($m4is_l91805)){
$m4is_o498 .= print_r($m4is_l91805, true);
 
}elseif(is_bool($m4is_l91805)){
$m4is_o498 .= $m4is_l91805 ? 'True' : 'False';
 
}else{
$m4is_o498 .= $m4is_l91805;
 
}
}else{
$m4is_o498 .= $m4is_d3069;
 
}$m4is_o498 .= "\n";
 if(constant('MEMBERIUM_DEBUGLOG' )== 'error_log:'){
error_log($m4is_o498 );
 
}elseif(constant('MEMBERIUM_DEBUGLOG' )> ''){
file_put_contents(constant('MEMBERIUM_DEBUGLOG' ), $m4is_o498, FILE_APPEND);
 
}else{
echo nl2br($m4is_o498);
 
}
}
}

