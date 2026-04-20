<?php
 class_exists('m4is_v967' )||die();
 final 
class m4is_h09 {
private static m4is_r83 $m4is_r1546;
 private static m4is_v967 $m4is_u934;
 private static string $m4is_r9613;
 private static string $m4is_z81;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){

}static 
function m4is_l12(){
self::$m4is_z81 =date('Y-m-d' );
 self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname');
 self::$m4is_u934 =m4is_v967::m4is_c26();
 $m4is_m63284 =self::$m4is_u934->m4is_l18();
 foreach($m4is_m63284 as $m4is_l9671 =>$m4is_y50 ){
$m4is_n58 =[];
 if(empty($m4is_y50['cache'])||($m4is_y50['start_date']<= self::$m4is_z81 &&$m4is_y50['end_date']>= self::$m4is_z81 )){
$m4is_m63284[$m4is_l9671]=self::m4is_l84536($m4is_y50 );
 
}
}self::$m4is_u934->m4is_j3129($m4is_m63284 );
 
}static 
function m4is_y0235(){
global $wpdb;
 $m4is_v2613 ='SELECT `id` FROM %i WHERE (`appname` = %s AND `fieldname` = "AffName" ) AND ( `value` LIKE "!%%" ) OR ( `value` LIKE "(INTERNAL)%%" );';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_z13097::m4is_o71(), self::$m4is_r9613 );
 $m4is_q28 =$wpdb->get_col($m4is_v2613 );
 return is_array($m4is_q28)? $m4is_q28 : [];
 
}static 
function m4is_l84536($m4is_y50 ){
$m4is_n58 =[];
 if($m4is_y50['type']== 'leads' ){
$m4is_n58 =self::m4is_f66($m4is_y50 );
 
}elseif(in_array($m4is_y50['type'], ['dollars', 'invoices'])){
$m4is_n58 =self::m4is_f975($m4is_y50 );
 
}$m4is_y50['cache']=$m4is_n58;
 $m4is_y50['last_updated']=time();
 return $m4is_y50;
 
}static 
function m4is_f66($m4is_y50 ){
$m4is_y1726 =self::m4is_y0235();
  $m4is_o09623 =date('Ymd\T23:59:59', strtotime($m4is_y50['end_date']));
 $m4is_n58 =[];
 $m4is_e80 ='Referral';
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_e69637 =0;
 $m4is_h3647 =['AffiliateId', 'ContactId', 'DateSet', ];
 $m4is_v76912 =['DateSet' =>'~>=~ ' . $m4is_y50['start_date'], ];
 do {
$m4is_m615 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'DateSet', true );
 $m4is_m615 =is_array($m4is_m615 )? $m4is_m615 : [];
 $m4is_e665 =count($m4is_m615 );
 foreach($m4is_m615 as $m4is_g91703 ){
if($m4is_g91703['DateSet']<= $m4is_o09623 ){
if(!in_array($m4is_g91703['AffiliateId'], $m4is_y1726 )){
$m4is_n58[$m4is_g91703['AffiliateId']]=isset($m4is_n58[$m4is_g91703['AffiliateId']])? $m4is_n58[$m4is_g91703['AffiliateId']]++ : 1;
 
}
}
}$m4is_d3012++;
 $m4is_e69637 =$m4is_e69637 + $m4is_e665;
 
}while ($m4is_e665 == $m4is_c92430 );
 arsort($m4is_n58 );
 unset($m4is_n58[0]);
 return array_slice($m4is_n58, 0, $m4is_y50['slots'], true );
 
}static 
function m4is_f975($m4is_y50 ){
 $m4is_y1726 =self::m4is_y0235();
 $m4is_o09623 =date('Ymd\T23:59:59', strtotime($m4is_y50['end_date']));
 $m4is_n58 =[];
 $m4is_t12830 =[];
 $m4is_e80 ='Invoice';
 $m4is_c92430 =1000;
 $m4is_d3012 =0;
 $m4is_p23 =array_filter(explode(',', $m4is_y50['products']));
 $m4is_e69637 =0;
 $m4is_h3647 =['AffiliateId', 'LeadAffiliateId', 'DateCreated', 'InvoiceTotal', 'ProductSold', ];
 $m4is_v76912 =['DateCreated' =>'~>=~ ' . date('Ymd\This', strtotime($m4is_y50['start_date'])), 'PayStatus' =>1, 'RefundStatus' =>0, ];
 do {
$m4is_m615 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'DateCreated', true );
 $m4is_m615 =is_array($m4is_m615 )? $m4is_m615 : [];
 $m4is_e665 =count($m4is_m615 );
 foreach($m4is_m615 as $m4is_g91703 ){
$m4is_g9163 =array_filter(explode(',', $m4is_g91703['ProductSold']));
 $m4is_t6601 =(int) $m4is_g91703['AffiliateId'];
 $m4is_v724 =(int) $m4is_g91703['LeadAffiliateId'];
 $m4is_r6249 =(bool) ($m4is_t6601 > 0 ||$m4is_v724 > 0 );
 $m4is_r6249 =$m4is_r6249 &&($m4is_g91703['DateCreated']< $m4is_o09623 );
 $m4is_r6249 =$m4is_r6249 &&(!in_array($m4is_t6601, $m4is_y1726 ));
 if($m4is_r6249 &&!empty($m4is_p23 )){
$m4is_r66079 =array_intersect($m4is_p23, $m4is_g9163 );
 $m4is_r6249 =!empty($m4is_r66079 );
 
}if($m4is_r6249 ){
if($m4is_y50['type']== 'dollars' ){
$m4is_n58[$m4is_t6601]+= (double) $m4is_g91703['InvoiceTotal'];
 
}elseif($m4is_y50['type']== 'invoices' ){
$m4is_n58[$m4is_t6601]++;
 
}
}
}$m4is_d3012++;
 $m4is_e69637 =$m4is_e69637 + $m4is_e665;
 
}while ($m4is_e665 == $m4is_c92430);
 unset($m4is_n58[0], $m4is_t12830[0]);
 arsort($m4is_n58 );
 return array_slice($m4is_n58, 0, $m4is_y50['slots'], true );
 
} 
}

