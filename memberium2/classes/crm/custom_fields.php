<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_s695 {
private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_z59682;
 private static $m4is_z98;
 private static $m4is_m7426;
 private static $m4is_e6426;
 public const CONTACT_FIELDS =-1;
 public const AFFILIATE_FIELDS =-3;
 public const OPPORTUNITY_FIELDS =-4;
 public const COMPANY_FIELDS =-6;
 public const TASK_FIELDS =-5;
 public const ORDER_FIELDS =-9;
 public const SUBSCRIPTION_FIELDS =-10;
 public const PHONE_TYPE =1;
 public const SSN_TYPE =2;
 public const CURRENCY_TYPE =3;
 public const PERCENT_TYPE =4;
 public const STATE_TYPE =5;
 public const YESNO_TYPE =6;
 public const YEAR_TYPE =7;
 public const MONTH_TYPE =8;
 public const DOW_TYPE =9;
 public const NAME_TYPE =10;
 public const DECIMAL_TYPE =11;
 public const WHOLE_TYPE =12;
 public const DATE_TYPE =13;
 public const DATETIME_TYPE =14;
 public const TEXT_TYPE =15;
 public const TEXTAREA_TYPE =16;
 public const LISTBOX_TYPE =17;
 public const WEBSITE_TYPE =18;
 public const EMAIL_TYPE =19;
 public const RADIO_TYPE =20;
 public const DROPDOWN_TYPE =21;
 public const USER_TYPE =22;
 public const DRILLDOWN_TYPE =23;
  public static 
function m4is_i702(){
global $wpdb;
 self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_m7426 =$wpdb->prefix . 'memberium_customfields';
 self::$m4is_z98 =1000;
 self::$m4is_e6426 =self::$m4is_r1546->m4is_b32198();
 
}private 
function __construct(){
 
}      public static 
function m4is_w52(): string {
return self::$m4is_m7426;
  
} public static 
function m4is_r7665(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_w52();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(11) NOT NULL, \n" . "appname varchar(32) NOT NULL, \n" . "name varchar(64) NOT NULL, \n" . "label varchar(64) NOT NULL, \n" . "datatype smallint(6) NOT NULL, \n" . "formid smallint(6) NOT NULL, \n" . "PRIMARY KEY  (id,appname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}      public static 
function m4is_c63758(string $m4is_e80 ): array {
global $wpdb;
 static $m4is_e52716 =[];
 $m4is_j3627 =['contact' =>self::CONTACT_FIELDS, 'affiliate' =>self::AFFILIATE_FIELDS, 'opportunity' =>self::OPPORTUNITY_FIELDS, 'company' =>self::COMPANY_FIELDS, 'task' =>self::TASK_FIELDS, 'job' =>self::ORDER_FIELDS, 'recurringorder' =>self::SUBSCRIPTION_FIELDS, ];
 $m4is_j0361 =$m4is_j3627[$m4is_e80]?? 0;
 if(!$m4is_j0361 ){
return [];
 
}if(array_key_exists($m4is_j0361, $m4is_e52716 )){
return $m4is_e52716[$m4is_j0361];
 
}if($m4is_j0361 ){
$m4is_v2613 ="SELECT `id`, `name`, `label`, `datatype` FROM %i WHERE `appname` = %s AND `formid` = %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_w52(), self::$m4is_r9613, $m4is_j0361 );
 $m4is_a89 =(array)$wpdb->get_results($m4is_v2613, OBJECT_K );
 
}$m4is_e52716[$m4is_j0361]=$m4is_a89;
 return $m4is_a89;
 
}      private static 
function m4is_v274(): array {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT `id` FROM %i WHERE `appname` = %s", self::m4is_w52(), self::$m4is_r9613 );
 $m4is_p65 =$wpdb->get_col($m4is_v2613 );
 return $m4is_p65;
 
} public static 
function m4is_r56126(int $m4is_m92735 =0, int $m4is_y64165 =0 ): int {
global $wpdb;
 $m4is_u9381 =$m4is_m92735 < 0 ? $wpdb->prepare("AND `formid` = %d ", $m4is_m92735 ): '';
 $m4is_u9381 .= $m4is_y64165 > 0 ? $wpdb->prepare("AND `datatype` = %d ", $m4is_y64165 ): '';
 $m4is_v2613 =$wpdb->prepare("SELECT COUNT(`id`) FROM %i WHERE `appname` = %s {$m4is_u9381
}", self::m4is_w52(), self::$m4is_r9613 );
 $m4is_h973 =$wpdb->get_var($m4is_v2613 );
 return (int) $m4is_h973;
 
} public static 
function m4is_e654(int $m4is_m92735 =0, int $m4is_y64165 =0 ): array {
global $wpdb;
 static $m4is_e52716 =[];
 $m4is_u9381 =$m4is_m92735 < 0 ? $wpdb->prepare("AND `formid` = %d ", $m4is_m92735 ): '';
 $m4is_u9381 .= $m4is_y64165 > 0 ? $wpdb->prepare("AND `datatype` = %d ", $m4is_y64165 ): '';
 $m4is_v2613 =$wpdb->prepare("SELECT concat('_', `name`) FROM %i WHERE `appname` = %s {$m4is_u9381
}", self::m4is_w52(), self::$m4is_r9613 );
 $m4is_j54781 =sha1($m4is_v2613 );
 if(isset($m4is_e52716[$m4is_j54781])){
return $m4is_e52716[$m4is_j54781];
 
}$m4is_d965 =$wpdb->get_col($m4is_v2613 );
 $m4is_e52716[$m4is_j54781]=$m4is_d965;
 return $m4is_d965;
 
} public static 
function m4is_r16938(int $m4is_m92735 =0, int $m4is_y64165 =0 ): array {
global $wpdb;
 $m4is_u9381 =$m4is_m92735 < 0 ? $wpdb->prepare("AND `formid` = %d ", $m4is_m92735 ): '';
 $m4is_u9381 .= $m4is_y64165 > 0 ? $wpdb->prepare("AND `datatype` = %d ", $m4is_y64165 ): '';
 $m4is_v2613 =$wpdb->prepare("SELECT * FROM %i WHERE `appname` = %s {$m4is_u9381
}", self::m4is_w52(), self::$m4is_r9613 );
 $m4is_a89 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 return $m4is_a89;
 
}      public static 
function m4is_r2903(): int {
$m4is_m615 =(int) self::m4is_g8705();
 $m4is_e80 ='DataFormField';
  do_action('memberium/custom_fields/sync' );
 set_transient('i2sdk_customfields_updated', time());
 wp_cache_delete($m4is_e80, 'i2sdk/tables' );
 return $m4is_m615;
  
} public static 
function m4is_g8705(): int {
$m4is_k206 =0;
  $m4is_k206 += self::m4is_z1590();
 $m4is_k206 += self::m4is_b72();
 $m4is_k206 += self::m4is_h7163();
 $m4is_k206 += self::m4is_s19();
  $m4is_k206 += self::m4is_b675();
 $m4is_k206 += self::m4is_z658();
 $m4is_k206 += self::m4is_d566();
 $m4is_k206 += self::m4is_s03647();
  return $m4is_k206;
 
} private static 
function m4is_b72(): int {
$m4is_k206 =0;
 $m4is_n548 =self::$m4is_e6426->elf_retrieve_affiliate_model();
 if(!is_wp_error($m4is_n548 )){
$m4is_k206 += self::m4is_m854($m4is_n548 );
 
}m4is_z13097::m4is_o8403();
 return $m4is_k206;
 
} private static 
function m4is_d566(): int {
$m4is_k206 =0;
 $m4is_n548 =self::$m4is_e6426->elf_retrieve_appointment_model();
 if(!is_wp_error($m4is_n548 )){
$m4is_k206 += self::m4is_m854($m4is_n548 );
 
}return $m4is_k206;
 
} private static 
function m4is_z1590(): int {
$m4is_k206 =0;
 $m4is_n548 =self::$m4is_e6426->elf_retrieve_contact_model();
 if(!is_wp_error($m4is_n548 )){
$m4is_k206 += self::m4is_m854($m4is_n548 );
 
}m4is_p40::m4is_o8403();
 return $m4is_k206;
 
} private static 
function m4is_z658(): int {
$m4is_k206 =0;
 $m4is_n548 =self::$m4is_e6426->elf_retrieve_company_model();
 if(!is_wp_error($m4is_n548 )){
$m4is_k206 += self::m4is_m854($m4is_n548 );
 
}return $m4is_k206;
 
} private static 
function m4is_b675(): int {
$m4is_k206 =0;
 $m4is_n548 =self::$m4is_e6426->elf_retrieve_notes_model();
 if(!is_wp_error($m4is_n548 )){
$m4is_k206 += self::m4is_m854($m4is_n548 );
 
}return $m4is_k206;
 
} private static 
function m4is_s03647(): int {
$m4is_k206 =0;
 $m4is_n548 =self::$m4is_e6426->elf_retrieve_opportunity_model();
 if(!is_wp_error($m4is_n548 )){
$m4is_k206 += self::m4is_m854($m4is_n548 );
 
}return $m4is_k206;
 
} private static 
function m4is_h7163(): int {
$m4is_k206 =0;
 $m4is_n548 =self::$m4is_e6426->elf_retrieve_order_model();
 if(!is_wp_error($m4is_n548 )){
$m4is_k206 += self::m4is_m854($m4is_n548 );
 
}return $m4is_k206;
 
} private static 
function m4is_s19(): int {
$m4is_k206 =0;
 $m4is_n548 =self::$m4is_e6426->elf_retrieve_subscription_model();
 if(!is_wp_error($m4is_n548 )){
$m4is_k206 += self::m4is_m854($m4is_n548 );
 
}return $m4is_k206;
 
}      public static 
function m4is_w6257(string $m4is_e80, string $m4is_s36520, string $m4is_g89163, int $m4is_u6183 ){
$m4is_s36520 =trim($m4is_s36520 );
 return self::$m4is_z59682->addCustomField($m4is_e80, $m4is_s36520, $m4is_g89163, $m4is_u6183 );
 
} public static 
function m4is_j2847(string $m4is_s36520, string $m4is_g89163 ='Text', int $m4is_u6183 =0 ){
if(empty($m4is_s36520 )){
return;
 
}$m4is_s36520 =trim($m4is_s36520 );
 $m4is_g89163 =trim($m4is_g89163 );
 $m4is_u6183 =(int) $m4is_u6183;
 if($m4is_u6183 == 0){
$m4is_h3647 =['Id', 'Name' ];
 $m4is_v76912 =['Id' =>'%' ];
 $m4is_d976 =m4is_c69807::m4is_o986('DataFormGroup', self::$m4is_z98, 0, $m4is_v76912, $m4is_h3647 );
 $m4is_u6183 =(int) $m4is_d976[0]['Id'];
 
}if($m4is_u6183 > 0 ){
m4is_s695::m4is_w6257('Contact', $_POST['new_crm_field'], $m4is_g89163, $m4is_u6183 );
 m4is_s695::m4is_z1590();
 
}else{
m4is_h65::m4is_z896('Unable to add new custom field.  Please create a custom tab and custom group in Keap first.' );
 return;
 
}
}      private static 
function m4is_m854(object $m4is_n548 ): int {
global $wpdb;
 $m4is_a89 =$m4is_n548->custom_fields ?? [];
 $m4is_p65 =[];
 $m4is_i760 =[];
 $m4is_v2613 ='';
 foreach($m4is_a89 as $m4is_l9671 =>$m4is_q523 ){
$m4is_e75 =(int) ($m4is_q523->id ?? 0 );
 $m4is_d93825 =$m4is_q523->label ?? '';
 $m4is_r150 =$m4is_q523->field_name ?? '';
 $m4is_l24 =self::m4is_c208($m4is_q523->record_type ?? '' );
 $m4is_n67508 =self::m4is_g034($m4is_q523->field_type ?? '' );
 $m4is_p65[]=$m4is_e75;
 $m4is_i760[]=$wpdb->prepare(" ( %d, %s, %s, %s, %d, %d ) ", $m4is_e75, self::$m4is_r9613, $m4is_r150, $m4is_d93825, $m4is_n67508, $m4is_l24 );
 
} if(!empty($m4is_i760 )){
$m4is_v2613 ="INSERT INTO %i ( `id`, `appname`, `name`, `label`, `datatype`, `formid` ) VALUES " . implode(",\n", $m4is_i760 ). " ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `label` = VALUES(`label`), `datatype` = VALUES(`datatype`), `formid` = VALUES(`formid`)";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_w52());
 $m4is_m615 =$wpdb->query($m4is_v2613 );
 
} if(!empty($m4is_p65 )){
$m4is_h5648 =implode(',', $m4is_p65 );
 $m4is_v2613 ="DELETE FROM %i WHERE `appname` = %s AND `formid` = %d AND `id` NOT IN ( {$m4is_h5648
} )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_w52(), self::$m4is_r9613, $m4is_l24 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 
}return count($m4is_p65 );
 
} private static 
function m4is_c208(string $m4is_e80 ): int {
static $m4is_j3627 =['company' =>self::COMPANY_FIELDS, 'contact' =>self::CONTACT_FIELDS, 'opportunity' =>self::OPPORTUNITY_FIELDS, 'order' =>self::ORDER_FIELDS, 'referral_partner' =>self::AFFILIATE_FIELDS, 'subscription' =>self::SUBSCRIPTION_FIELDS, 'task_note_appointment' =>self::TASK_FIELDS, ];
 $m4is_e80 =strtolower($m4is_e80 );
 if(array_key_exists($m4is_e80, $m4is_j3627 )){
return $m4is_j3627[$m4is_e80];
 
}error_log('Memberium: [error] Custom Field Table ID not found for table: ' . $m4is_e80 );
 return -9999;
 
} private static 
function m4is_g034(string $m4is_g89163 ): int {
static $m4is_j3627 =['currency' =>self::CURRENCY_TYPE, 'date' =>self::DATE_TYPE, 'datetime' =>self::DATETIME_TYPE, 'decimal' =>self::DECIMAL_TYPE, 'dow' =>self::DOW_TYPE, 'drilldown' =>self::DRILLDOWN_TYPE, 'dropdown' =>self::DROPDOWN_TYPE, 'email' =>self::EMAIL_TYPE, 'listbox' =>self::LISTBOX_TYPE, 'month' =>self::MONTH_TYPE, 'name' =>self::NAME_TYPE, 'percent' =>self::PERCENT_TYPE, 'phone' =>self::PHONE_TYPE, 'radio' =>self::RADIO_TYPE, 'ssn' =>self::SSN_TYPE, 'state' =>self::STATE_TYPE, 'text' =>self::TEXT_TYPE, 'textarea' =>self::TEXTAREA_TYPE, 'user' =>self::USER_TYPE, 'website' =>self::WEBSITE_TYPE, 'wholenumber' =>self::WHOLE_TYPE, 'year' =>self::YEAR_TYPE, 'yesno' =>self::YESNO_TYPE, ];
 $m4is_g89163 =strtolower($m4is_g89163 );
 if(!array_key_exists($m4is_g89163, $m4is_j3627 )){
error_log('Memberium: [error] Custom Field Type ID not found for type: ' . $m4is_g89163 );
 return 0;
 
}return array_key_exists($m4is_g89163, $m4is_j3627 )? $m4is_j3627[$m4is_g89163]: 0;
 
} 
}m4is_s695::m4is_i702();

