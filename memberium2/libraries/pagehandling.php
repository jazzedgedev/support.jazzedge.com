<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_j586 {
private static $m4is_s673 =false;
  private 
function __construct(){
 
} public static 
function m4is_k751(): void {
$m4is_y79 =defined('MEMBERIUM_DISABLE_CACHING' )&&constant('MEMBERIUM_DISABLE_CACHING' )== true;
 if(function_exists('is_user_logged_in' )&&!is_user_logged_in()){
return;
 
}self::m4is_n06();
 if(!headers_sent()){
header('X-Cache-Enabled: False');
 header('Cache-Control: no-cache, max-age=0, must-revalidate, no-store');
 header('Pragma: no-cache');
 header('Expires: 0');
 nocache_headers();
 
}
} public static 
function m4is_x7134(): void {
static $m4is_n946 =false;
 if($m4is_n946 ){
return;
 
}if(self::$m4is_s673 ){
return;
 
}self::$m4is_s673 =true;
 $m4is_n946 =true;
 self::m4is_k751();
  if(!empty($_SERVER['HTTP_X_VARNISH'])){
return;
 
} 
} public static 
function m4is_f6018(){
return !self::$m4is_s673;
 
} private static 
function m4is_n06(): void {
static $m4is_z1674 =false;
 if(!$m4is_z1674){
$m4is_z1674 =true;
 if(!defined('LSCACHE_NO_CACHE')){
define('LSCACHE_NO_CACHE', true);
 
}if(!defined('DONOTCACHEPAGE')){
define('DONOTCACHEPAGE', true);
 
}if(!headers_sent()){
header('X-Cache-Enabled: False' );
  
}
}
} 
}

