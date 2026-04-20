<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 m4is_c01675::m4is_h269();
 final 
class m4is_c01675 {
private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_z59682;
 private const CREDITCARD_CACHE_TTL =900;
 private const PAYMENT_SESSION_TTL =(45 * MINUTE_IN_SECONDS );
 private const PAYMENT_TOKEN_KEY ='memberium/payment/token';
  static 
function m4is_h269(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 
} static 
function m4is_c6341(int $m4is_h21895 ){
return sprintf('memberium/creditcards/%s/%d', self::$m4is_r9613, $m4is_h21895 );
 
} public static 
function m4is_j6029(int $m4is_h21895 ): bool {
if($m4is_h21895 ){
$m4is_z984 =self::m4is_c6341($m4is_h21895 );
 delete_transient($m4is_z984 );
 return true;
 
}return false;
 
} public static 
function m4is_s04(array $m4is_p1054 ){
if(empty($m4is_p1054 )){
return [];
 
}return end($m4is_p1054 );
 
} public static 
function m4is_f2190(int $m4is_a3165 ): string {
$m4is_w04 =[0 =>'Unknown',  1 =>'Error',  2 =>'Deleted',  3 =>'OK',  4 =>'Inactive',  ];
 return key_exists($m4is_a3165, $m4is_w04 )? $m4is_w04[$m4is_a3165]: 'Unknown';
 
} public static 
function m4is_w68604(int $m4is_h21895, int $m4is_c31 =3 ): array {
$m4is_p1054 =[];
 if(!$m4is_h21895 ){
return $m4is_p1054;
 
}$m4is_z984 =self::m4is_c6341($m4is_h21895 );
 $m4is_p1054 =get_transient($m4is_z984 );
 $m4is_g53684 =self::$m4is_r1546->m4is_z56();
 if($m4is_p1054 === false ){
$m4is_p1054 =self::m4is_a20694($m4is_h21895 );
 
}if($m4is_c31 >= 0 ){
foreach($m4is_p1054 as $m4is_l9671 =>$m4is_d895 ){
$m4is_i696 =(int) $m4is_d895['Status'];
 if($m4is_i696 <> $m4is_c31 ){
unset($m4is_p1054[$m4is_l9671]);
 
}
}
}if($m4is_h21895 === $m4is_g53684 ){
$_SESSION['memb_user']['has_credit_card']=is_array($m4is_p1054 )? count($m4is_p1054 ): 0;
 
}return $m4is_p1054;
 
} public static 
function m4is_a20694(int $m4is_h21895 ){
$m4is_z984 =self::m4is_c6341($m4is_h21895 );
 $m4is_q436 =300;
 $m4is_p1054 =get_transient($m4is_z984 );
 if($m4is_p1054 === false ){
$m4is_p1054 =[];
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_e69637 =0;
 $m4is_e80 ='CreditCard';
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false );
 $m4is_v76912 =['ContactId' =>$m4is_h21895,  ];
 do {
$m4is_m615 =self::$m4is_z59682->dsQueryOrderBy($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', true );
 if(is_string($m4is_m615 )){
error_log('Memberium: [error] Credit Card Sync API Error - ' . $m4is_m615 );
 break;
 
}$m4is_h973 =is_array($m4is_m615 )? count($m4is_m615 ): 0;
 if(is_array($m4is_m615 )){
foreach ($m4is_m615 as $m4is_g91703 ){
$m4is_p1054[$m4is_g91703['Id']]=$m4is_g91703;
 
}$m4is_d3012++;
 $m4is_e69637 =$m4is_e69637 + $m4is_h973;
 
}
}while ($m4is_h973 == $m4is_c92430 );
 if(is_array($m4is_p1054 )){
set_transient($m4is_z984, $m4is_p1054, self::CREDITCARD_CACHE_TTL );
 
}
}if(self::$m4is_r1546->m4is_z56()=== $m4is_h21895 ){
$_SESSION['memb_user']['has_credit_card']=is_array($m4is_p1054 )? count($m4is_p1054 ): 0;
 
}return $m4is_p1054;
 
}private static 
function m4is_i06(int $m4is_h21895 ): string {
$m4is_n5049 =self::$m4is_r1546->m4is_b32198();
 $m4is_s6492 =$m4is_n5049->m4is_i06($m4is_h21895 );
 return (string ) $m4is_s6492;
 
}private static 
function m4is_t74632(int $m4is_f087, string $m4is_v8646 ): void {
if(empty($m4is_v8646 )||empty($m4is_f087 )){
return;
 
}$m4is_q58 =['expiration' =>time()+ self::PAYMENT_SESSION_TTL, 'token' =>$m4is_v8646 ];
 update_user_meta($m4is_f087, self::PAYMENT_TOKEN_KEY, $m4is_q58 );
 
} public static 
function m4is_q76215(int $m4is_f087 ): string {
if(empty($m4is_f087 )){
return '';
 
}$m4is_e52716 =get_user_meta($m4is_f087, self::PAYMENT_TOKEN_KEY, true );
 $m4is_v8646 ='';
 if(is_array($m4is_e52716 )){
$m4is_r6654 =$m4is_e52716['expiration']?? 0;
 if($m4is_r6654 > time()){
$m4is_v8646 =$m4is_e52716['token']?? '';
 
}
}if(empty($m4is_v8646 )){
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 $m4is_v8646 =self::m4is_i06($m4is_h21895 );
 self::m4is_t74632($m4is_f087, $m4is_v8646 );
 
}return (string ) $m4is_v8646;
 
} public static 
function m4is_j46573(int $m4is_h21895, int $m4is_j668 ): int {
if(empty($m4is_j668 )||empty($m4is_h21895 )){
return 0;
 
}$m4is_n5049 =self::$m4is_r1546->m4is_b32198();
 $m4is_p1054 =$m4is_n5049->elf_retrieve_creditcards($m4is_h21895 );
 if(!is_array($m4is_p1054 )||empty($m4is_p1054 )){
return 0;
 
}foreach ($m4is_p1054 as $m4is_d895 ){
if(($m4is_d895->payment_method_id ?? 0 )== $m4is_j668 ){
return (int) $m4is_d895->id ?? 0;
 
}
}return 0;
 
}
}

