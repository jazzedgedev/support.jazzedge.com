<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_s10863 {
private 
function __construct(){
 
} public static 
function m4is_a18(string $m4is_y29766 ='ISO-8859-1', string $m4is_h671 ='UTF-8', string $m4is_l91805 ='' ): string {
if(function_exists('iconv' )){
return iconv($m4is_y29766, $m4is_h671, $m4is_l91805 );
 
}elseif(function_exists('mb_convert_encoding' )){
return mb_convert_encoding($m4is_l91805, $m4is_h671, $m4is_y29766 );
 
}error_log('Memberium:  [Error] Neither iconv nor mbstring are available for encoding conversion.' );
 return $m4is_l91805;
 
}
}

