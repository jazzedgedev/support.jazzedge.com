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
class m4is_a01587 {
 public static 
function m4is_y342(): string {
static $m4is_p0645 ='';
 if(!empty($m4is_p0645 )){
return $m4is_p0645;
 
}$m4is_g6152 =$m4is_g6152 ?? $_SERVER;
 $m4is_p0645 =$_SERVER['REMOTE_ADDR']?? '';
 $m4is_f5368 =['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_X_SUCURI_CLIENTIP', 'HTTP_X_REAL_IP', ];
 foreach ($m4is_f5368 as $m4is_l9671 ){
if(array_key_exists($m4is_l9671, $_SERVER)=== true){
foreach (explode(',', $_SERVER[$m4is_l9671])as $m4is_n9130){
$m4is_n9130 =trim($m4is_n9130);
 if(self::m4is_v639($m4is_n9130)){
$m4is_p0645 =$m4is_n9130;
 
}
}
}
}return (string) $m4is_p0645;
 
} private static 
function m4is_v639(string $m4is_n9130 ): bool {
if(filter_var($m4is_n9130, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)=== false){
return false;
 
}return true;
 
} public static 
function m4is_f46823(string $m4is_n6062 ='', bool $m4is_c63024 =false, array $m4is_y66291 =[]): string {
$m4is_y642 =['timeout' =>10, ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642 );
 $m4is_n548 =wp_remote_get($m4is_n6062, $m4is_y66291 );
 if(is_array($m4is_n548 )){
if(empty($m4is_n548['body'])){
$m4is_n548['body']='';
 
}if(!$m4is_c63024 ){
return $m4is_n548['body'];
 
}
}elseif(is_string($m4is_n548 )){
return $m4is_n548;
 
}return '';
 
} public static 
function m4is_q354(string $m4is_n9130, string $m4is_c68720 ): bool {
list($subnet, $m4is_i62895 )=array_filter(explode('/', $m4is_c68720 ));
 if((ip2long($m4is_n9130 )& ~((1 << (32 - $m4is_i62895 ))- 1 ))== ip2long($subnet )){
return true;
 
}return false;
 
} public static 
function m4is_d29(): array {
$m4is_z984 ='memberium/aws_subnets';
 $m4is_y4659 =get_transient($m4is_z984 );
 if($m4is_y4659 === false ){
 $m4is_n548 =wp_remote_get('https://ip-ranges.amazonaws.com/ip-ranges.json');
  $m4is_n548 =json_decode($m4is_n548['body']);
 $m4is_y4659 =$m4is_n548->prefixes;
 set_transient($m4is_z984, $m4is_y4659, 24 * HOUR_IN_SECONDS);
 unset($m4is_n548);
 
}return $m4is_y4659;
 
} public static 
function m4is_g18(): array {
$m4is_y4659 =m4is_a01587::m4is_d29();
 $m4is_y30866 =[];
 foreach($m4is_y4659 as $m4is_n80626 ){
if('S3' == $m4is_n80626->service ){
$m4is_y30866[$m4is_n80626->region ]=$m4is_n80626->region;
 
}
}ksort($m4is_y30866 );
 return $m4is_y30866;
 
} public static 
function m4is_j301(?string $m4is_b56726 =null ): bool {
$m4is_u93 =m4is_r83::m4is_c26()->m4is_j498('settings', 'debug_ip', '' );
 if(empty($m4is_u93 )){
return false;
 
}$m4is_u93 =array_map('trim', array_filter(explode(',', $m4is_u93 )));
 if(empty($m4is_u93 )){
return false;
 
}$m4is_b56726 =$m4is_b56726 ?? m4is_a01587::m4is_y342();
 if(in_array($m4is_b56726, $m4is_u93 )){
return true;
 
}return false;
 
}
}

