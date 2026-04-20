<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 m4is_z13097::m4is_h269();
 final 
class m4is_z13097 {
static private $m4is_r6218;
 static private $m4is_g721;
 static private $m4is_r1546;
 static private $m4is_r9613;
 static private $m4is_z59682;
 static private $m4is_d29436;
 static private $m4is_e6426;
  static 
function m4is_h269(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_r6218 ='memberium_affiliates';
 self::$m4is_g721 ='memberium_affiliates_totals';
 self::$m4is_e6426 =self::$m4is_r1546->m4is_z40()->elf_rest();
 
}    static 
function m4is_o71(): string {
return self::$m4is_r6218;
 
}static 
function m4is_u91(): string {
return self::$m4is_g721;
 
}static 
function m4is_y65892(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_o71();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) NOT NULL, \n" . "appname varchar(32) NOT NULL, \n" . "fieldname varchar(64) NOT NULL DEFAULT '', \n" . "value longtext, \n" . "KEY id (id), \n" . "KEY appname (appname), \n" . "KEY fieldname (fieldname), \n" . "KEY value (value(64) ), \n" . "PRIMARY KEY  (id,appname,fieldname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}static 
function m4is_k169(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::m4is_u91();
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(20) NOT NULL, \n" . "appname varchar(32) NOT NULL, \n" . "time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, \n" . "amount_earned double NOT NULL DEFAULT 0, \n" . "payments double NOT NULL DEFAULT 0, \n" . "clawbacks double NOT NULL DEFAULT 0, \n" . "running_balance double NOT NULL DEFAULT 0, \n" . "KEY id (id), \n" . "KEY appname (appname), \n" . "PRIMARY KEY  (id,appname) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}    static 
function m4is_u961(){
 self::$m4is_d29436 =is_null(self::$m4is_d29436 )  ? self::$m4is_d29436 =self::$m4is_r1546->m4is_j498('settings', 'max_affiliate_age', 0 ) : self::$m4is_d29436;
  return (int) self::$m4is_d29436;
 
} static 
function m4is_l85(int $m4is_w46279 ){
 $m4is_w46279 =$m4is_w46279 < 0 ? 0 : $m4is_w46279;
  self::$m4is_d29436 =$m4is_w46279;
  return $m4is_w46279;
 
} static 
function m4is_d176(){
 static $m4is_y5760;
  if(!isset($m4is_y5760 )){
 $m4is_y5760 =self::$m4is_r1546->m4is_r1476()->dsGetSetting('Affiliate', 'chooseaffiliate' );
 
} return $m4is_y5760;
 
} static 
function m4is_u90(): int {
 global $wpdb;
   $m4is_v2613 ="SELECT COUNT(*) FROM `" . self::$m4is_r6218 . "` WHERE `appname` = %s AND `fieldname` = 'Status' AND `value` = 1;";
  $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_r9613 );
  return (int) $wpdb->get_var($m4is_v2613 );
 
} static 
function m4is_g74(int $m4is_h85 =0, int $m4is_d3012 =0, string $m4is_c31 ='' ): array {
global $wpdb;
   $m4is_v2613 ="SELECT distinct `id` FROM `" . self::$m4is_r6218 . "` WHERE `appname` = %s AND `fieldname` = 'Status' ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_r9613 );
   if(!empty($m4is_c31 )){
$m4is_v2613 .= " AND `value` = {$m4is_c31
} ";
  
} $m4is_v2613 .= "ORDER BY `id` ASC ";
   if($m4is_h85 > 0 ){
$m4is_v2613 .= " LIMIT {$m4is_h85
} OFFSET " . ($m4is_d3012 * $m4is_h85 );
  
} $m4is_p65 =$wpdb->get_col($m4is_v2613, 0 );
   return $m4is_p65;
  
} static 
function m4is_x4673(int $m4is_t6601 ): array {
global $wpdb;
 $m4is_l9671 ='memberium/sync/running_totals/last_update';
 $m4is_k25038 =time()- (int) get_option($m4is_l9671, 0 );
 $m4is_w46279 =HOUR_IN_SECONDS;
 if($m4is_k25038 > $m4is_w46279 ){
self::m4is_h18();
 
}$m4is_v2613 ="SELECT `amount_earned`, `payments`, `clawbacks`, `running_balance`, `time` FROM %i WHERE `appname` = %s AND `id` = %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_u91(), self::$m4is_r9613, $m4is_t6601 );
 $m4is_u6591 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 $m4is_u6591 =is_array($m4is_u6591[0])? $m4is_u6591[0]: [];
 $m4is_a873 =empty($m4is_u6591['time'])? 0 : strtotime($m4is_u6591['time']);
  return $m4is_u6591;
 
} static 
function m4is_h18(){
global $wpdb;
 if(!self::$m4is_r1546->m4is_j498('settings', 'sync_affiliate', 0 )){
return;
 
}$m4is_l9671 ='memberium/sync/running_totals/updated';
 $m4is_t6421 =(int) get_transient($m4is_l9671 );
 if((time()- $m4is_t6421 )< 43200 ){
return;
 
}$m4is_e80 =self::$m4is_r6218;
 $m4is_r9613 =self::$m4is_r9613;
 $m4is_h85 =500;
 $m4is_d3012 =0;
 do {
 $m4is_s9162 =self::m4is_g74($m4is_h85, $m4is_d3012, 1 );
 if(!empty($m4is_s9162 )){
$m4is_b53866 =self::$m4is_z59682->affRunningTotals($m4is_s9162 );
 if(!empty($m4is_b53866)&&is_array($m4is_b53866)){
 foreach($m4is_b53866 as $m4is_p86){
if(is_array($m4is_p86)){
 $m4is_v2613 ="INSERT INTO %i (`id`, `appname`, `amount_earned`, `payments`, `clawbacks`, `running_balance`) ";
 $m4is_v2613 .= "VALUES ( %f, %s, %f, %f, %f, %f ) ";
 $m4is_v2613 .= "ON DUPLICATE KEY UPDATE `id` = %f, `appname` = %s, `amount_earned` = %f, `payments` = %f, `clawbacks` = %f, `running_balance` = %f;";
  $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_u91(), $m4is_p86['AffiliateId'], self::$m4is_r9613, $m4is_p86['AmountEarned'], $m4is_p86['Payments'], $m4is_p86['Clawbacks'], $m4is_p86['RunningBalance'], $m4is_p86['AffiliateId'], self::$m4is_r9613, $m4is_p86['AmountEarned'], $m4is_p86['Payments'], $m4is_p86['Clawbacks'], $m4is_p86['RunningBalance']);
  $wpdb->query($m4is_v2613);
 
}
}
} $m4is_d3012++;
 
}
}while (count($m4is_s9162 )== $m4is_h85 );
  set_transient($m4is_l9671, time(), 3600 );
 
} static 
function m4is_r16(array $m4is_a566, array $m4is_l766 =[]){
global $wpdb;
 if(empty($m4is_a566['Id'])){
return [];
 
} $m4is_e80 ='Affiliate';
 $m4is_m7426 =self::$m4is_r6218;
 $m4is_r1546 =m4is_r83::m4is_c26();
  $m4is_a566 =self::$m4is_r1546->m4is_f9708($m4is_a566 );
  $m4is_t6601 =(int) $m4is_a566['Id'];
 $m4is_a566['!LastUpdated']=time();
  $m4is_l766 =empty($m4is_l766 )? self::m4is_d85476($m4is_t6601 ): $m4is_l766;
  $m4is_w2647 =self::$m4is_r1546->m4is_t8350($m4is_l766, $m4is_a566 );
 $m4is_z19 =self::$m4is_r1546->m4is_f247($m4is_l766, $m4is_a566 );
 $m4is_r06238 =self::$m4is_r1546->m4is_p5672($m4is_l766, $m4is_a566 );
 $m4is_b912 =self::$m4is_r1546->m4is_g861($m4is_w2647, $m4is_r06238 );
  self::m4is_r03286($m4is_t6601, $m4is_r06238 );
  if(!empty($m4is_w2647 )){
foreach ($m4is_w2647 as $m4is_l9671 =>$m4is_v586 ){
 $m4is_v2613 ="UPDATE `" . self::$m4is_r6218 . "` SET `value` = %s WHERE `id` = %d AND `fieldname` = %s AND `appname` = %s";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_v586, $m4is_t6601, $m4is_l9671, self::$m4is_r9613 );
  $wpdb->query($m4is_v2613 );
 
}
} if(!empty($m4is_z19 )){
$m4is_i760 =[];
 foreach ($m4is_z19 as $m4is_l9671 =>$m4is_v586 ){
 $m4is_i760[]=$wpdb->prepare(' (%d, %s, %s, %s) ', $m4is_t6601, self::$m4is_r9613, $m4is_l9671, $m4is_v586 );
 
}if(!empty($m4is_i760 )){
 $m4is_v2613 ="INSERT INTO `" . self::$m4is_r6218 . "` (`id`, `appname`, `fieldname`, `value`) VALUES " . implode(',', $m4is_i760 );
  $wpdb->query($m4is_v2613 );
 
}
}  return $m4is_a566;
 
} static 
function m4is_f664(){
global $wpdb;
  $m4is_c92430 =1000;
  $m4is_e80 ='Affiliate';
  $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, true );
  $m4is_t57364 ='memberium/batchsync/affiliates/page';
  $m4is_w92738 ='memberium/batchsync/affiliates/timestamp';
  $m4is_r9613 =self::$m4is_r9613;
  $m4is_v76912 =['Id' =>'%'];
  $m4is_z59682 =self::$m4is_r1546->m4is_r1476();
  $m4is_d3012 =0;
  $m4is_u450 =[];
  do {
 $m4is_x9366 =$m4is_z59682->dsQueryOrderBy($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', true );
 $m4is_h973 =is_array($m4is_x9366 )? count($m4is_x9366 ): 0;
 if(!is_array($m4is_x9366 )){
error_log('Memberium: [error] Batch Sync Affiliates API Error - ' . $m4is_x9366 );
 break;
 
}if(is_array($m4is_x9366 )){
 foreach($m4is_x9366 as $m4is_j90523){
$m4is_q28[$m4is_j90523['Id']]=$m4is_j90523;
 
}unset($m4is_x9366);
  $m4is_s9162 =[];
 foreach($m4is_q28 as $m4is_a566){
$m4is_s9162[]=$m4is_a566['Id'];
 
} $m4is_s70 =implode(',', $m4is_s9162);
 $m4is_v2613 ="SELECT `id`, `fieldname`, `value` FROM `" . self::$m4is_r6218 . "` WHERE `appname` = '{$m4is_r9613
}' AND `id` IN ({$m4is_s70
}) AND `fieldname` <> '!LastUpdated';";
 $m4is_x9366 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 $m4is_q61 =[];
  if(is_array($m4is_x9366)){
foreach($m4is_x9366 as $m4is_l9671 =>$m4is_j90523){
$m4is_q61[$m4is_j90523['id']][$m4is_j90523['fieldname']]=$m4is_j90523['value'];
 
}
}unset($m4is_x9366);
  foreach($m4is_q28 as $m4is_d07693 =>$m4is_a566 ){
if(!array_key_exists($m4is_d07693, $m4is_q61 )){
self::m4is_r16($m4is_a566 );
 unset($m4is_q28[$m4is_d07693]);
 
}
} $m4is_z9157 =[];
 $m4is_i760 =[];
 $m4is_p65 =[];
 $m4is_a873 =time();
  foreach($m4is_q28 as $m4is_d07693 =>$m4is_a566){
$m4is_c29861 =array_diff($m4is_a566, $m4is_q61[$m4is_d07693]);
 if(!empty($m4is_c29861)){
$m4is_z9157[$m4is_d07693]=$m4is_c29861;
 $m4is_p65[]=$m4is_d07693;
 
}
} foreach($m4is_z9157 as $m4is_d07693 =>$m4is_g91703){
$m4is_r637 =key($m4is_g91703);
 $m4is_v586 =$m4is_g91703[$m4is_r637];
 $m4is_i760[]=$wpdb->prepare('(%d, %s, %s, %s)', $m4is_d07693, $m4is_r9613, $m4is_r637, $m4is_v586);
 
} foreach($m4is_p65 as $m4is_d07693){
$m4is_i760[]=$wpdb->prepare('(%d, %s, %s, %s)', $m4is_d07693, $m4is_r9613, '!LastUpdated', $m4is_a873);
 
} if(!empty($m4is_i760)){
$m4is_v2613 ="INSERT INTO `" . self::$m4is_r6218 . "` (`id`, `appname`, `fieldname`, `value`) VALUES " . implode(',', $m4is_i760 ). " ON DUPLICATE KEY UPDATE `id`=VALUES(`id`), `appname`=VALUES(`appname`), `fieldname`=VALUES(`fieldname`), `value`=VALUES(`value`);";
 $wpdb->query($m4is_v2613);
 
} $m4is_d3012++;
 
} usleep(250000 );
 
}while ($m4is_h973 == $m4is_c92430);
  
} static 
function m4is_d95(int $m4is_d07693 ): array {
global $wpdb;
   $m4is_v2613 ="SELECT `fieldname`, `value` FROM `" . self::$m4is_r6218 . "` WHERE `appname` = %s AND `id` = %d ";
  $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_r9613, $m4is_d07693 );
  $m4is_q28 =$wpdb->get_results($m4is_v2613, ARRAY_A );
  if(empty($m4is_q28 )){
 $m4is_a566 =self::m4is_e859($m4is_d07693 );
  $m4is_a566['!source']='Remote';
 
}else{
 $m4is_a566 =[];
  foreach ($m4is_q28 as $m4is_g91703 ){
 $m4is_a566[$m4is_g91703['fieldname']]=$m4is_g91703['value'];
 
} $m4is_a566['!source']='Local';
 
} return $m4is_a566;
 
} static 
function m4is_d85476(int $m4is_t6601 ): array {
 global $wpdb;
 $m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
  $m4is_v2613 ="SELECT `fieldname`, `value` FROM `" . self::$m4is_r6218 . "` WHERE `appname` = %s AND `id` = %d ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_r9613, $m4is_t6601 );
  $m4is_q28 =(array)$wpdb->get_results($m4is_v2613, ARRAY_A );
  if(!empty($m4is_q28 )){
$m4is_q28 =self::$m4is_r1546->m4is_f0367($m4is_q28, 'fieldname', 'value' );
 $m4is_q28['!source']='Local';
 
} return $m4is_q28;
 
} static 
function m4is_n19(int $m4is_h21895 ): array {
 global $wpdb;
  $m4is_m7426 =self::$m4is_r6218;
 $m4is_r9613 =self::$m4is_r9613;
  $m4is_v2613 =<<<SQLBLOCK
			SELECT
				`{$m4is_m7426
}_2`.`fieldname`,
				`{$m4is_m7426
}_2`.`value`
			FROM
				`{$m4is_m7426
}`,
				`{$m4is_m7426
}` AS `{$m4is_m7426
}_2`
			WHERE   `{$m4is_m7426
}`.`appname`  = %s
			AND     `{$m4is_m7426
}`.`fieldname` = 'ContactId'
			AND     `{$m4is_m7426
}`.`value`     = %d
			AND     `{$m4is_m7426
}_2`.`id`      = {$m4is_m7426
}.id
			AND     `{$m4is_m7426
}_2`.`appname` = %s;
		SQLBLOCK;
  $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_r9613, $m4is_h21895, self::$m4is_r9613 );
  $m4is_a566 =$wpdb->get_results($m4is_v2613, ARRAY_N );
  $m4is_a566 =self::$m4is_r1546->m4is_f0367($m4is_a566, 0, 1 );
  $m4is_a566 =apply_filters('memberium/affiliate/load', $m4is_a566 );
  return $m4is_a566;
 
} static 
function m4is_h026(int $m4is_h21895 ): array {
 $m4is_c92430 =1;
  $m4is_e80 ='Affiliate';
  $m4is_d3012 =0;
  $m4is_v76912 =['ContactId' =>$m4is_h21895 ];
  $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, true );
  $m4is_a566 =m4is_c69807::m4is_i84($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647, 'Id', true );
  if(!is_array($m4is_a566 )){
 
} $m4is_a566 =isset($m4is_a566[0])? $m4is_a566[0]: [];
  $m4is_a566 =is_array($m4is_a566 )? self::$m4is_r1546->m4is_f9708($m4is_a566 ): $m4is_a566;
  return $m4is_a566;
 
} static 
function m4is_m56923(int $m4is_t6601 ): array {
$m4is_a566 =m4is_c69807::m4is_b6614('Affiliate', $m4is_t6601 );
 return (array)$m4is_a566;
 
} static 
function m4is_c90626(int $m4is_t6601 ){
 global $wpdb;
   $m4is_v2613 ="DELETE FROM `" . self::$m4is_r6218 . "` WHERE `id` = %d AND `appname` = %s ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_t6601, self::$m4is_r9613 );
  $wpdb->query($m4is_v2613 );
 
} private static 
function m4is_r03286(int $m4is_t6601, array $m4is_a89 ){
 if(count($m4is_a89 )){
 global $wpdb;
  $m4is_z32461 =implode("','", $m4is_a89 );
  $m4is_v2613 ="DELETE FROM `". self::$m4is_r6218 . "` WHERE `id` = %d AND `appname` = %s AND `fieldname` IN ( '{$m4is_z32461
}' )";
  $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_t6601, self::$m4is_r9613 );
  $wpdb->query($m4is_v2613 );
 
}
} static 
function m4is_e859(int $m4is_t6601 ): array {
 $m4is_u6095 ='Affiliate';
  $m4is_a566 =(array)m4is_c69807::m4is_b6614($m4is_u6095, $m4is_t6601 );
  if(is_array($m4is_a566 )&&!empty($m4is_a566 )){
 self::m4is_r16($m4is_a566 );
  return $m4is_a566;
 
} elseif(m4is_c69807::m4is_g46($m4is_a566 )){
 self::m4is_c90626($m4is_t6601 );
 
} else{
 error_log('Memberium: [error]  Affiliate #' . $m4is_t6601 . ' - ' . $m4is_a566);
 
} return [];
 
} static 
function m4is_z60183(int $m4is_h21895 ): array {
$m4is_a566 =self::m4is_h026($m4is_h21895 );
 if(is_array($m4is_a566 )&&!empty($m4is_a566 )){
self::m4is_r16($m4is_a566 );
 return $m4is_a566;
 
}elseif(m4is_c69807::m4is_g46($m4is_a566 )){
  error_log(sprintf('Memberium: [error] Affiliate for Contact # %d - %s deleted.', $m4is_h21895, $m4is_a566 ));
 
} else{
 error_log(sprintf('Memberium: [error] Affiliate for Contact ID # %d - %s', $m4is_h21895, $m4is_a566 ));
 
} return [];
 
} static 
function m4is_x66347(int $m4is_t6601, int $m4is_w46279 =-1 ): bool {
global $wpdb;
  $m4is_w46279 =$m4is_w46279 < 10 ? self::m4is_u961(): 10;
  $m4is_p82 =true;
  $m4is_v2613 ="SELECT `value` as `age` FROM `" . self::$m4is_r6218 . "` WHERE `appname` = %s AND `id` = %d AND `fieldname` = '!LastUpdated' ";
  $m4is_k25038 =(int) $wpdb->get_var($m4is_v2613, self::$m4is_r9613, $m4is_t6601 );
  return ($m4is_k25038 + $m4is_w46279 )> time();
 
} static 
function m4is_y876(int $m4is_h21895 ): int {
global $wpdb;
  static $m4is_x8270 =[];
  if(array_key_exists($m4is_h21895, $m4is_x8270 )){
return $m4is_x8270[$m4is_h21895];
 
}$m4is_v2613 ="SELECT `id` FROM %i WHERE `appname` = %s AND `fieldname` = 'ContactId' AND `value` = '%d'";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::m4is_o71(), self::$m4is_r9613, $m4is_h21895 );
 $m4is_d07693 =(int) $wpdb->get_var($m4is_v2613 );
 if(!empty($m4is_d07693 )){
$m4is_x8270[$m4is_h21895]=$m4is_d07693;
 
} if(empty($m4is_d07693 )){
error_log(sprintf("Memberium: [info] No affiliate ID found for contact ID: %d", $m4is_h21895 ));
 
}return $m4is_d07693;
 
}    static 
function m4is_o8403(): void {
$m4is_q1470 ='ignore_affiliate_fields';
 $m4is_j0361 ='Affiliate';
 $m4is_w96028 =m4is_c69807::m4is_f5248($m4is_j0361, false );
 $m4is_v45136 =self::$m4is_r1546->m4is_j498('settings', $m4is_q1470, '' );
 $m4is_v45136 =is_string($m4is_v45136 )? $m4is_v45136 : '';
 $m4is_v45136 =array_filter(explode(',', $m4is_v45136 ));
 $m4is_v45136 =array_intersect($m4is_w96028, $m4is_v45136 );
 $m4is_v45136 =implode(',', $m4is_v45136 );
 self::$m4is_r1546->m4is_d64918($m4is_v45136, 'settings', $m4is_q1470 );
 
} 
}

