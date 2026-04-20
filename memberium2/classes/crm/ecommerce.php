<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 m4is_v87365::m4is_h269();
 final 
class m4is_v87365 {
private static object $m4is_r1546;
 private static string $m4is_r9613;
 private static string $m4is_e09382;
 private static object $m4is_z59682;
 private static object $m4is_e6426;
  static 
function m4is_h269(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_e09382 ='memberium_products';
 self::$m4is_e6426 =self::$m4is_r1546->m4is_z40()->elf_rest();
 
}    static 
function m4is_p63285(): string {
return 'memberium_invoices';
 
} static 
function m4is_a36489(): string {
return self::$m4is_e09382;
 
}static 
function m4is_z32(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_p63285();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(11) NOT NULL, \n" . "appname varchar(32), \n" . "affiliateid int(11) NOT NULL, \n" . "contactid int(11) NOT NULL, \n" . "creditstatus int(11) NOT NULL, \n" . "datecreated datetime NOT NULL, \n" . "description varchar(255) NOT NULL, \n" . "invoicetotal double NOT NULL, \n" . "invoicetype varchar(32) NOT NULL, \n" . "jobid int(11) NOT NULL, \n" . "lastupdated datetime NOT NULL, \n" . "leadaffiliateid int(11) NOT NULL, \n" . "payplanstatus int(11) NOT NULL, \n" . "paystatus int(11) NOT NULL, \n" . "productsold varchar(255) NOT NULL, \n" . "promocode varchar(32) NOT NULL, \n" . "refundstatus int(11) NOT NULL, \n" . "totaldue double NOT NULL, \n" . "totalpaid double NOT NULL, \n" . "KEY job_id (jobid), \n" . "KEY contactid (contactid), \n" . "KEY affiliateid (affiliateid), \n" . "KEY datecreated (datecreated), \n" . "PRIMARY KEY  (id,appname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}static 
function m4is_d45621(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_a36489();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) NOT NULL, \n" . "appname varchar(32) NOT NULL, \n" . "fieldname varchar(64) NOT NULL default '', \n" . "value longtext, \n" . "KEY id (id), \n" . "PRIMARY KEY  (id,appname,fieldname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}   public static 
function m4is_p68(){
$m4is_n548 =self::$m4is_e6426->m4is_a40(1, 'merchants' );
 $m4is_n548 =self::$m4is_e6426->elf_handle_faults($m4is_n548 );
 return (array)$m4is_n548;
 
}    private static 
function m4is_k654(int $m4is_l97604 ): string {
return sprintf('memberium/%s/payplans/%d', self::$m4is_r9613, $m4is_l97604 );
 
} private static 
function m4is_l59612(int $m4is_j6089 ): string {
return sprintf('memberium/%s/payplanitems/%d', self::$m4is_r9613, $m4is_j6089 );
 
}private static 
function m4is_o57391(): int {
return MINUTE_IN_SECONDS * 30;
 
} static 
function m4is_w06321(int $m4is_l97604 ){
$m4is_x170 =false;
 $m4is_w46279 =self::m4is_o57391();
 if($m4is_l97604){
$m4is_z984 =self::m4is_k654($m4is_l97604 );
 $m4is_x170 =get_transient($m4is_z984 );
 if($m4is_x170 === false ){
$m4is_e80 ='PayPlan';
 $m4is_v76912 =['InvoiceId' =>$m4is_l97604 ];
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80 );
 $m4is_x170 =m4is_c69807::m4is_o986($m4is_e80, 1, 0, $m4is_v76912, $m4is_h3647 );
 
}if(is_array($m4is_x170 )&&!empty($m4is_x170 )){
set_transient($m4is_z984, $m4is_x170[0], $m4is_w46279 );
 $m4is_x170 =$m4is_x170[0];
 
}
}return $m4is_x170;
 
}    static 
function m4is_k6691(int $m4is_j6089 ){
$m4is_i386 =false;
 if($m4is_j6089){
$m4is_z984 =self::m4is_l59612($m4is_j6089 );
 $m4is_i386 =get_transient($m4is_z984 );
 if($m4is_i386 === false){
$m4is_e80 ='PayPlanItem';
 $m4is_v76912 =['PayPlanId' =>$m4is_j6089 ];
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80 );
 $m4is_i386 =m4is_c69807::m4is_i84($m4is_e80, 1000, 0, $m4is_v76912, $m4is_h3647, 'DateDue', true );
 if(is_array($m4is_i386 )&&count($m4is_i386 )> 0 ){
set_transient($m4is_z984, $m4is_i386, 1800 );
 
}
}
}return $m4is_i386;
 
}    private static 
function m4is_g823(array $m4is_j098 ){
global $wpdb;
 $m4is_e80 =self::m4is_a36489();
 $m4is_r9613 =self::$m4is_r9613;
 $m4is_a89 =m4is_c69807::m4is_f5248('product' );
 $m4is_v45136 =[];
 $m4is_p65 =[];
 $m4is_i760 =[];
 $m4is_u450 =[];
 foreach($m4is_j098 as $m4is_h1438){
$m4is_p65[]=$m4is_h1438['Id'];
 
}if(!empty($m4is_p65)){
$m4is_d579 =implode(',', $m4is_p65);
 $m4is_v2613 ="DELETE FROM `{$m4is_e80
}` WHERE `id` NOT IN ({$m4is_d579
})";
 $wpdb->query($m4is_v2613);
 
}foreach($m4is_j098 as $m4is_d07693 =>$m4is_h1438){
$m4is_h1432 =self::m4is_x8325((int) $m4is_h1438['Id']);
  foreach($m4is_h1432 as $m4is_r637 =>$m4is_v586){
if(!array_key_exists($m4is_r637, $m4is_h1438)){
$m4is_v2613 ="DELETE FROM `{$m4is_e80
}` WHERE `appname` = '{$m4is_r9613
}' AND `id` = {$m4is_h1438['Id']
} AND `fieldname` = '{$m4is_r637
}' ";
 $wpdb->query($m4is_v2613);
 
}
} foreach($m4is_h1438 as $m4is_o015 =>$m4is_k72){
if(!in_array($m4is_o015, $m4is_v45136)){
if((!isset($m4is_h1432[$m4is_o015])||($m4is_h1432[$m4is_o015]<> $m4is_k72))){
$m4is_i760[]=$wpdb->prepare('(%d, %s, %s, %s)', $m4is_d07693, $m4is_r9613, $m4is_o015, $m4is_k72);
 $m4is_u450[].= $wpdb->prepare("%s", $m4is_o015);
 
}
}
}if(!empty($m4is_i760)){
$m4is_v2613 ="INSERT INTO {$m4is_e80
} (id, appname, fieldname, value) VALUES " . implode(',', $m4is_i760). " ON DUPLICATE KEY UPDATE id=VALUES(id), appname=VALUES(appname), fieldname=VALUES(fieldname), value=VALUES(value);";
 $m4is_u6591 =$wpdb->query($m4is_v2613);
 
}
}
} static 
function m4is_i194(): int {
global $wpdb;
 $m4is_v2613 ="SELECT count(DISTINCT `id`) FROM %i WHERE `appname` = %s";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_e09382, self::$m4is_r9613 );
 return (int) $wpdb->get_var($m4is_v2613);
 
}static 
function m4is_a0846(){
global $wpdb;
 $m4is_s82753 =3 * HOUR_IN_SECONDS;
 $m4is_c1869 =get_option('memberium_tables_updated', []);
 $m4is_c92430 =defined('MEMBERIUM_PRODUCT_LIMIT')? constant('MEMBERIUM_PRODUCT_LIMIT' ): 1000;
 $m4is_e80 ='Product';
 $m4is_d3012 =0;
 $m4is_z051 ='Id';
 $m4is_e207 ='%';
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false, ['LargeImage']);
 $m4is_e69637 =0;
 $m4is_r9613 =self::$m4is_r9613;
 $m4is_z984 ="Memberium_{$m4is_r9613
}_Product";
 $m4is_v76912 =['Id' =>$m4is_e207];
 $m4is_j098 =[];
 $m4is_z59682 =self::$m4is_z59682;
 $m4is_q1046 ='all';
 $m4is_o14 ='memberium2/products';
 do {
$m4is_m615 =$m4is_z59682->dsQuery($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647 );
 if(!is_array($m4is_m615 )){
error_log(sprintf('Memberium: [error] Product Sync API Error:  Limit = %d,  Error = "%s"', $m4is_c92430, $m4is_m615 ));
 return;
 
}$m4is_d3012++;
 if(is_array($m4is_m615)){
foreach ($m4is_m615 as $m4is_g91703){
$m4is_j098[$m4is_g91703['Id']]=$m4is_g91703;
 
}
}
}while (is_array($m4is_m615 )&&count($m4is_m615 )== $m4is_c92430 );
 unset($m4is_m615, $m4is_g91703);
 if(is_array($m4is_j098)){
set_transient($m4is_z984, $m4is_j098, $m4is_s82753 );
 set_transient('memberium_products_updated', time());
 self::m4is_g823($m4is_j098);
 
}else{
$m4is_j098 =false;
 
}$m4is_c1869['products']=time();
 update_option('memberium_tables_updated', $m4is_c1869, false );
 return $m4is_j098;
 
}static 
function m4is_x8325(int $m4is_o6480 ){
global $wpdb;
 $m4is_r9613 =self::$m4is_r9613;
 $m4is_e80 =self::m4is_a36489();
 $m4is_h1438 =[];
 $m4is_v2613 ="SELECT `fieldname`, `value` FROM `{$m4is_e80
}` WHERE `appname` = '{$m4is_r9613
}' AND `id` = {$m4is_o6480
}";
 $m4is_d5472 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 if(is_array($m4is_d5472)){
foreach($m4is_d5472 as $m4is_u6591){
$m4is_h1438[$m4is_u6591['fieldname']]=$m4is_u6591['value'];
 
}
}return $m4is_h1438;
 
}static 
function m4is_e98671(){
global $wpdb;
 $m4is_v2613 ="SELECT `id`, `fieldname`, `value` FROM %i WHERE `appname` = %s";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_e09382, self::$m4is_r9613 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 $m4is_j098 =[];
 foreach($m4is_m615 as $m4is_g91703){
$m4is_j098[$m4is_g91703['id']][$m4is_g91703['fieldname']]=$m4is_g91703['value'];
 
}return $m4is_j098;
 
} static 
function m4is_g864(int $m4is_d07693, bool $m4is_w01 =false ){
global $wpdb;
 $m4is_q1046 =$m4is_d07693;
 $m4is_o14 ='memberium2/product';
 $m4is_s82753 =900;
 $m4is_t265 =false;
 $m4is_h1438 =wp_cache_get($m4is_q1046, $m4is_o14, false, $m4is_t265 );
 if($m4is_t265 === false ){
$m4is_e80 =self::m4is_a36489();
 $m4is_r9613 =m4is_r83::m4is_c26()->m4is_i76('appname');
 $m4is_v2613 ="SELECT `fieldname`, `value` FROM `{$m4is_e80
}` WHERE `appname` = '{$m4is_r9613
}' AND `id` = {$m4is_d07693
}";
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 $m4is_h1438 =[];
 if(is_array($m4is_m615)){
foreach($m4is_m615 as $m4is_g91703){
$m4is_r637 =$m4is_w01 ? strtolower($m4is_g91703['fieldname']): $m4is_g91703['fieldname'];
 $m4is_h1438[$m4is_r637]=$m4is_g91703['value'];
 
}
}if(!empty($m4is_h1438)){
wp_cache_set($m4is_q1046, $m4is_h1438, $m4is_o14, $m4is_s82753);
 
}
}return $m4is_h1438;
 
} static 
function m4is_d96640($m4is_w01 =false ){
global $wpdb;
 $m4is_q1046 ='all';
 $m4is_o14 ='memberium2/products';
 $m4is_s82753 =900;
 $m4is_t265 =false;
 $m4is_j098 =wp_cache_get($m4is_q1046, $m4is_o14, false, $m4is_t265 );
 if($m4is_t265 === false ){
$m4is_e80 =self::m4is_a36489();
 $m4is_r9613 =m4is_r83::m4is_c26()->m4is_i76('appname' );
 $m4is_v2613 ="SELECT `id`, `fieldname`, `value` FROM `{$m4is_e80
}` WHERE `appname` = '{$m4is_r9613
}'";
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 $m4is_j098 =[];
 foreach($m4is_m615 as $m4is_d07693 =>$m4is_g91703 ){
$m4is_r637 =$m4is_w01 ? strtolower($m4is_g91703['fieldname']): $m4is_g91703['fieldname'];
 $m4is_j098[$m4is_g91703['id']][$m4is_r637]=$m4is_g91703['value'];
 unset($m4is_m615[$m4is_d07693]);
 
}if(!empty($m4is_j098 )){
wp_cache_set($m4is_q1046, $m4is_j098, $m4is_o14, $m4is_s82753 );
 
}
}return $m4is_j098;
 
}   private static 
function m4is_t571(): string {
return 'memberium/ecommerce/subscriptionplans';
 
}private static 
function m4is_y61692(): int {
return HOUR_IN_SECONDS * 12;
 
} static 
function m4is_x096(){
$m4is_z984 =self::m4is_t571();
 $m4is_q436 =self::m4is_y61692();
 $m4is_s5867 =get_transient($m4is_z984 );
 if($m4is_s5867 === false ){
$m4is_e80 ='SubscriptionPlan';
 $m4is_h85 =1000;
 $m4is_d3012 =0;
 $m4is_s5867 =[];
 $m4is_v76912 =['Id' =>'%' ];
 do {
$m4is_m615 =m4is_c69807::m4is_o986($m4is_e80, $m4is_h85, $m4is_d3012, $m4is_v76912 );
 $m4is_e665 =is_array($m4is_m615 )? count($m4is_m615 ): 0;
 $m4is_d3012 =$m4is_d3012 + 1;
 if($m4is_e665 ){
foreach ($m4is_m615 as $m4is_g91703 ){
$m4is_d07693 =$m4is_g91703['Id'];
 $m4is_f7034 =(int) $m4is_g91703['Cycle'];
 $m4is_d637 =(int) $m4is_g91703['Frequency'];
 $m4is_s5867[$m4is_d07693]=$m4is_g91703;
 if($m4is_f7034 == 6 ){
$m4is_s5867[$m4is_d07693]['FrequencyWord']=($m4is_d637 == 1 )? 'Day' : $m4is_d637 . ' Days';
 
}elseif($m4is_f7034 == 3 ){
$m4is_s5867[$m4is_d07693]['FrequencyWord']=($m4is_d637 == 1 )? 'Week' : $m4is_d637 . ' Weeks';
 
}elseif($m4is_f7034 == 2 ){
$m4is_s5867[$m4is_d07693]['FrequencyWord']=($m4is_d637 == 1 )? 'Month' : $m4is_d637 . ' Months';
 
}elseif($m4is_f7034 == 1 ){
$m4is_s5867[$m4is_d07693]['FrequencyWord']=($m4is_d637 == 1 )? 'Year' : $m4is_d637 . ' Years';
 
}
}
}
}while ($m4is_e665 === $m4is_h85 );
 if(is_array($m4is_s5867 )){
set_transient($m4is_z984, $m4is_s5867, $m4is_q436);
 
}
}return $m4is_s5867;
 
}    public static 
function m4is_l318(): int {
global $wpdb;
 if(!self::$m4is_r1546->m4is_j498('settings', 'sync_ecommerce', false )){
return 0;
 
}$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 $m4is_r359 =get_option('memberium_tables_updated', []);
 $m4is_l07426 =self::m4is_p63285();
  $m4is_v2613 ="SELECT MAX(`lastupdated`) FROM `{$m4is_l07426
}` WHERE `appname` = '{$m4is_r9613
}';";
 $m4is_i3206 =$wpdb->get_var($m4is_v2613);
 if(!$m4is_i3206 ){
$m4is_i3206 ='1969-01-20T00:00:00';
 
} $m4is_e80 ='Invoice';
 $m4is_c92430 =400;
 $m4is_d3012 =0;
 $m4is_e69637 =0;
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false);
 $m4is_v76912 =['LastUpdated' =>"~>=~ {$m4is_i3206
}" ];
 $m4is_y276 =['CreditStatus' =>0, 'Description' =>'', 'InvoiceTotal' =>0, 'InvoiceType' =>'', 'JobId' =>0, 'LeadAffiliateId' =>0, 'PayPlanStatus' =>0, 'PayStatus' =>0, 'ProductSold' =>'', 'PromoCode' =>'', 'RefundStatus' =>0, 'Synced' =>0, 'TotalDue' =>0, 'TotalPaid' =>0, ];
 $m4is_n870 =['appname' =>'%s', 'contactid' =>'%d', 'creditstatus' =>'%s', 'datecreated' =>'%s', 'description' =>'%s', 'id' =>'%d', 'invoicetotal' =>'%d', 'invoicetype' =>'%s', 'jobid' =>'%d', 'lastupdated' =>'%s', 'leadaffiliateid' =>'%s', 'payplanstatus' =>'%s', 'paystatus' =>'%s', 'productsold' =>'%s', 'promocode' =>'%s', 'refundstatus' =>'%s', 'totaldue' =>'%s', 'totalpaid' =>'%s', ];
 $m4is_m84519 ='`' . implode('`, `', array_keys($m4is_n870 )). '`';
 $m4is_y1260 ="'" . implode("', '", $m4is_n870 ). "'";
 $m4is_v2786 ="INSERT INTO `{$m4is_l07426
}` ( {$m4is_m84519
} ) VALUES ( {$m4is_y1260
} )";
 $m4is_a91035 ="DELETE FROM %i WHERE `appname` = %s AND `id` = %d";
 do {
$m4is_m615 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'LastUpdated', true);
 $m4is_e665 =is_array($m4is_m615)? count($m4is_m615 ): 0;
 if($m4is_e665 ){
foreach ($m4is_m615 as $m4is_g91703){
$m4is_g91703 =array_merge($m4is_y276, $m4is_g91703 );
 $m4is_d07693 =(int) $m4is_g91703['Id'];
 $m4is_q64510 =isset($m4is_g91703['LastUpdated'])? $m4is_g91703['LastUpdated']: $m4is_g91703['DateCreated'];
 $m4is_s947 =['appname' =>$m4is_r9613, 'contactid' =>$m4is_g91703['ContactId'], 'creditstatus' =>$m4is_g91703['CreditStatus'], 'datecreated' =>date('Y-m-d h:i:s', strtotime($m4is_g91703['DateCreated'])), 'description' =>$m4is_g91703['Description'], 'id' =>$m4is_d07693, 'invoicetotal' =>$m4is_g91703['InvoiceTotal'], 'invoicetype' =>$m4is_g91703['InvoiceType'], 'jobid' =>$m4is_g91703['JobId'], 'lastupdated' =>date('Y-m-d h:i:s', strtotime($m4is_q64510)), 'leadaffiliateid' =>$m4is_g91703['LeadAffiliateId'], 'payplanstatus' =>$m4is_g91703['PayPlanStatus'], 'paystatus' =>$m4is_g91703['PayStatus'], 'productsold' =>$m4is_g91703['ProductSold'], 'promocode' =>isset($m4is_g91703['PromoCode'])? $m4is_g91703['PromoCode']: '', 'refundstatus' =>$m4is_g91703['RefundStatus'], 'totaldue' =>$m4is_g91703['TotalDue'], 'totalpaid' =>$m4is_g91703['TotalPaid'], ];
 $m4is_v2613 =$wpdb->prepare($m4is_a91035, $m4is_l07426, $m4is_r9613, $m4is_d07693 );
 $wpdb->query($m4is_v2613 );
 $m4is_v2613 =$wpdb->prepare($m4is_v2786, $m4is_s947 );
 $wpdb->query($m4is_v2613 );
 
}
}$m4is_d3012++;
 $m4is_e69637 =$m4is_e69637 + $m4is_e665;
 
}while ($m4is_e665 == $m4is_c92430);
 $m4is_r359['invoices']=time();
 update_option('memberium_tables_updated', $m4is_r359, false);
 return (int) $m4is_e69637;
 
}   public static 
function m4is_e60495(int $m4is_o064 ){
if(!empty($m4is_o064)){
$m4is_v76912 =['OriginatingOrderId' =>$m4is_o064 ];
 $m4is_a89 =['Id' ];
 $m4is_d74 =m4is_c69807::m4is_o986('RecurringOrder', 998, 0, $m4is_v76912, $m4is_a89);
  if(is_array($m4is_d74)){
foreach($m4is_d74 as $m4is_c12){
$m4is_u6591 =self::$m4is_r1546->dsDelete('RecurringOrder', (int) $m4is_c12['Id']);
 
}
}
}
} public static 
function m4is_a20916(){
$m4is_c92430 =999;
 $m4is_e80 ='RecurringOrder';
 $m4is_d3012 =0;
 $m4is_z051 ='Id';
 $m4is_e207 ='%';
 $m4is_e69637 =0;
 $m4is_r9613 =self::$m4is_r9613;
 $m4is_o287 =[];
 $m4is_h3647 =['ContactId', 'EndDate', 'Id', 'ReasonStopped', 'Status', ];
 $m4is_v76912 =['Status' =>'Active', 'EndDate' =>'~<=~' . date('Y-m-d'), ];
 do {
$m4is_m615 =m4is_c69807::m4is_o986($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647);
 if(!empty($m4is_m615)){
foreach ($m4is_m615 as $m4is_g91703){
$m4is_g91703['EndDate']=(isset($m4is_g91703['EndDate']))? $m4is_g91703['EndDate']: '';
 $m4is_g91703['ReasonStopped']=(isset($m4is_g91703['ReasonStopped']))? trim($m4is_g91703['ReasonStopped']): '';
 if($m4is_g91703['EndDate']> '' &&$m4is_g91703['EndDate']< date('Ymd\T00:00:00')){
$m4is_z82169 =[];
 if($m4is_g91703['ReasonStopped']== ''){
$m4is_z82169['ReasonStopped']="End Date Passed.\nAutoEnded by Memberium";
 
}$m4is_z82169['EndDate']=date('Ymd\Th:i:s');
 $m4is_z82169['Status']='Inactive';
 m4is_c69807::m4is_z64('RecurringOrder', (int) $m4is_g91703['Id'], $m4is_z82169);
 
}
}
}$m4is_d3012++;
 
}while (count($m4is_m615)== $m4is_c92430);
 unset($m4is_m615, $m4is_g91703);
 return $m4is_o287;
 
}
}

