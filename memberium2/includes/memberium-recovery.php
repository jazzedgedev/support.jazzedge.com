<?php
 defined('ABSPATH' )||die();
  add_filter('recovery_mode_email', function ($m4is_f4930, $m4is_n6062 ){
$m4is_s90 =debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS );
 foreach($m4is_s90 as $m4is_x35 ){
if(stripos($m4is_x35['file'], 'memberium' )!== false ||stripos($m4is_x35['file'], 'm4is' )!== false ){
$m4is_f4930 ='support@webpowerandlight.com';
 break;
 
}
}return $m4is_f4930;
 
}, 10, 2 );

