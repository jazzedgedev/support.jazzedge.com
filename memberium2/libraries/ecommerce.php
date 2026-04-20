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
class m4is_l4568 {
 private static 
function m4is_m8569(): array {
return ['American Express' =>['34', '37'], 'China UnionPay' =>['62', '88'], 'Diners Club Carte Blanche' =>['300', '305'], 'Diners Club International' =>['300', '305', '309', '36', '38,39'], 'Diners Club' =>['54', '55'], 'Discover Card' =>['6011', '622126', '622925', '644,649', '65'], 'JCB' =>['3528', '3589'], 'Laser' =>['6304', '6706', '6771', '6709'], 'Maestro' =>['5018', '5020', '5038', '5612', '5893', '6304', '6759', '6761', '6762', '6763', '0604', '6390'], 'Dankort' =>['5019'], 'MasterCard' =>['50', '55'], 'Visa' =>['4'], 'Visa Electron' =>['4026', '417500', '4405', '4508', '4844', '4913', '4917'], ];
 
} public static 
function m4is_g128(string $m4is_j694 ): string {
$m4is_h9754 ='Unknown';
 $m4is_o65 =self::m4is_m8569();
 foreach($m4is_o65 as $m4is_k52736 =>$m4is_s967 ){
foreach($m4is_s967 as $m4is_j67631 ){
if(strpos($m4is_j67631, ',' )){
$m4is_m73045 =array_filter(explode(',', $m4is_j67631 ));
 $m4is_y4853 =substr($m4is_j694, 0, strlen($m4is_m73045[0]));
 if($m4is_y4853 >= $m4is_m73045[0]&&$m4is_y4853 <= $m4is_m73045[1]){
$m4is_h9754 =$m4is_k52736;
 
}
}else{
 if(strncmp($m4is_j694, $m4is_j67631, strlen($m4is_j67631 ))=== 0 ){
$m4is_h9754 =$m4is_k52736;
 
}
}
}
}return $m4is_h9754;
 
} public static 
function m4is_f3172(string $m4is_y4853 ): bool {
$m4is_y4853 =preg_replace('/\D/', '', $m4is_y4853 );
  if(empty($m4is_y4853 )){
return false;
 
}$m4is_t340 =strlen($m4is_y4853 );
 $m4is_v815 =$m4is_t340 % 2;
 $m4is_t4853 =0;
 for ($m4is_b3785 =0;
 $m4is_b3785 < $m4is_t340;
 $m4is_b3785++ ){
$m4is_g95614 =(int) $m4is_y4853[$m4is_b3785];
  if($m4is_b3785 % 2 == $m4is_v815 ){
$m4is_g95614 *= 2;
  if($m4is_g95614 > 9 ){
$m4is_g95614 -= 9;
 
}
} $m4is_t4853 += $m4is_g95614;
 
} return ($m4is_t4853 % 10 == 0 );
 
}
}

