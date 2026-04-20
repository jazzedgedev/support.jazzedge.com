<?php

/**
 * Memberium Session Class
 * Copyright 2024 by David Bullock / Web Power and Light
 */


  final 
class m4is_q82 {
private const CACHE_GROUP ='memberium/sessions';
 private const CACHE_TTL =3600;
 private const RING_CACHE_SIZE =5;
 private const SESSION_TTL =HOUR_IN_SECONDS * 12;
 private const USERMETA_KEY ='memberium/session';
 private const M4IS_C4372 =202407251615;
 private const M4IS_M87219 =2 * MINUTE_IN_SECONDS;
 private static $m4is_r1546;
 private static $m4is_y9160;
 private static $m4is_n69048;
 private static $m4is_f016;
 private static $m4is_a71;
   private 
function __construct(){
 
} public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
  self::$m4is_y9160 =null;
 self::$m4is_n69048 =[];
 self::$m4is_a71 =[];
 
}  public static 
function m4is_d59(int $m4is_f087 ): array {
$m4is_k824 =self::m4is_v61605($m4is_f087 );
 if(self::m4is_g29186($m4is_k824 )){
self::m4is_b9626($m4is_f087 );
 $m4is_k824 =self::m4is_u687($m4is_f087 );
 
}return $m4is_k824;
 
} public static 
function m4is_u687(int $m4is_f087 ): array {
global $wpdb;
 if(!m4is_s52::m4is_f27()){
self::m4is_b9626($m4is_f087 );
 return [];
 
}if(($m4is_l17096 =get_user_by('id', $m4is_f087 ))=== false ){
self::m4is_b9626($m4is_f087 );
 return [];
 
}$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 delete_option('um_cache_userdata_' . $m4is_f087 );
  $m4is_k824 =[];
 $m4is_k824 =self::m4is_l66190($m4is_k824, $m4is_l17096 );
 if(user_can($m4is_l17096, 'manage_options' )){
self::m4is_n57($m4is_f087, $m4is_k824 );
 return $m4is_k824;
 
}if(!$m4is_h21895){
$m4is_h21895 =(int) self::$m4is_r1546->m4is_n871($m4is_l17096->user_email );
 if(!$m4is_h21895 ){
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_l17096->ID );
 
}
}if(!$m4is_h21895 ){
self::m4is_n57($m4is_f087, $m4is_k824 );
 return $m4is_k824;
 
}self::$m4is_r1546->m4is_f7064(true );
 self::$m4is_r1546->m4is_d1796($m4is_h21895, $m4is_f087 );
 self::$m4is_r1546->m4is_i12($m4is_h21895);
 $m4is_i935 =m4is_p40::m4is_p67($m4is_h21895, false );
 $m4is_l9321 =empty($m4is_i935['Groups'])? []: array_filter(explode(',', $m4is_i935['Groups']));
 $m4is_k824 =self::m4is_i695($m4is_k824, $m4is_i935 );
 $m4is_k824 =self::m4is_k86($m4is_k824, $m4is_i935 );
 $m4is_k824 =self::m4is_e50732($m4is_k824, $m4is_l9321 );
 $m4is_k824 =self::m4is_s65026($m4is_k824, $m4is_l9321 );
 $m4is_k824 =self::$m4is_r1546->m4is_f968($m4is_k824 );
 $m4is_k824 =self::$m4is_r1546->m4is_z324($m4is_k824 );
 $m4is_k824 =apply_filters('memberium_session_filter', $m4is_k824 );
 $m4is_k824 =apply_filters('memberium/session/filter', $m4is_k824 );
 self::m4is_h37192($m4is_f087, $m4is_i935 );
 if(false ){
 $m4is_k824 =self::$m4is_r1546->m4is_r28340($m4is_f087, $m4is_k824 );
 
}if(did_action('init' )){
self::$m4is_r1546->m4is_f605($m4is_h21895 );
 
}else{

}self::$m4is_r1546->m4is_f7064(false );
 ksort($m4is_k824 );
 self::m4is_n57($m4is_f087, $m4is_k824 );
 do_action('memberium/session/updated', $m4is_f087, $m4is_k824);
    if(!empty($m4is_k824['memb_user']['roles'])){
self::$m4is_r1546->m4is_i66($m4is_k824['memb_user']['roles'], $m4is_f087 );
 
}do_action('memberium/session/created', $m4is_k824 );
 return $m4is_k824;
 
} public static 
function m4is_d6758(int $m4is_f087, string $m4is_x95460 ='', string $m4is_l9671 ='', $m4is_n246 ='' ){
$m4is_k824 =self::m4is_d59($m4is_f087 );
 $m4is_u6591 =isset($m4is_k824[$m4is_x95460])? $m4is_k824[$m4is_x95460]: [];
 if(empty($m4is_l9671 )){
return empty($m4is_u6591 )? []: $m4is_u6591;
 
}return empty($m4is_u6591[$m4is_l9671])? $m4is_n246 : $m4is_u6591[$m4is_l9671];
 
} public static 
function m4is_k660(int $m4is_f087, string $m4is_x95460 ='', string $m4is_l9671 ='', $m4is_n246 ='' ){
$m4is_x95460 =strtolower($m4is_x95460 );
 $m4is_l9671 =strtolower($m4is_l9671 );
 $m4is_k824 =self::m4is_d59($m4is_f087 );
 $m4is_u6591 =isset($m4is_k824['keap'][$m4is_x95460])? $m4is_k824['keap'][$m4is_x95460]: [];
 if(empty($m4is_l9671 )){
return empty($m4is_u6591 )? []: $m4is_u6591;
 
}return empty($m4is_u6591[$m4is_l9671])? $m4is_n246 : $m4is_u6591[$m4is_l9671];
 
} public static 
function m4is_g14(int $m4is_f087, string $m4is_x95460, string $m4is_l9671, $m4is_v586 ){
$m4is_x95460 =strtolower($m4is_x95460 );
 $m4is_l9671 =strtolower($m4is_l9671 );
 $m4is_k824 =self::m4is_d59($m4is_f087 );
 $m4is_k824['keap'][$m4is_x95460][$m4is_l9671]=$m4is_v586;
 self::m4is_n57($m4is_f087, $m4is_k824 );
 
} public static 
function m4is_t660(int $m4is_f087 ): int {
return self::m4is_d6758($m4is_f087, 'memb_user', 'crm_id', 0 );
 
}  private static 
function m4is_g29186(array $m4is_k824 ): bool {
if(empty($m4is_k824 )){
return true;
 
}if(isset($m4is_k824['keap']['contact'])&&empty($m4is_k824['keap']['contact']['id'])){
return true;
 
}if(empty($m4is_k824['memb_user']['version'])||$m4is_k824['memb_user']['version']!== self::M4IS_C4372 ){
return true;
 
}if(isset($m4is_k824['memb_user']['nextupdate'])&&$m4is_k824['memb_user']['nextupdate']< time()){
return true;
 
}if(isset($m4is_k824['memb_user']['revision'])&&$m4is_k824['memb_user']['revision']< self::$m4is_r1546->m4is_w7426()){
return true;
 
}if(isset($m4is_k824['memb_user']['user_revision'])&&$m4is_k824['memb_user']['user_revision']< self::m4is_k340($m4is_k824['memb_user']['user_id'])){
return true;
 
}$m4is_h21895 =(int) $m4is_k824['memb_user']['crm_id'];
 $m4is_w784 =self::$m4is_r1546->m4is_b6486($m4is_h21895 );
 $m4is_v1573 =isset($m4is_k824['keap']['meta']['contact'])? $m4is_k824['keap']['meta']['contact']: 0;
 if($m4is_w784 > $m4is_v1573 ){
return true;
 
}return false;
 
} public static 
function m4is_q57064(int $m4is_f087 ): float {
$m4is_a873 =microtime(true );
 update_user_meta($m4is_f087, 'memberium/revision', $m4is_a873 );
 return $m4is_a873;
 
} public static 
function m4is_k340(int $m4is_f087 ): float {
$m4is_a6814 =(float) get_user_meta($m4is_f087, 'memberium/revision', true )OR $m4is_a6814 =self::m4is_q57064($m4is_f087 );
 return $m4is_a6814;
 
}  private static 
function m4is_v61605(int $m4is_f087 ): array {
$m4is_k824 =self::m4is_d50($m4is_f087 );
 $m4is_k824 =$m4is_k824 or self::m4is_z3248($m4is_f087 );
 $m4is_k824 =$m4is_k824 or get_user_meta($m4is_f087, self::USERMETA_KEY, true );
 return is_array($m4is_k824 )? $m4is_k824 : [];
 
} private static 
function m4is_n57(int $m4is_f087, array $m4is_k824 ): void {
self::m4is_n98($m4is_f087, $m4is_k824 );
 self::m4is_w62($m4is_f087, $m4is_k824 );
 update_user_meta($m4is_f087, self::USERMETA_KEY, $m4is_k824 );
 
} private static 
function m4is_b9626(int $m4is_f087 ): void {
wp_cache_set(self::m4is_b816($m4is_f087 ), null, self::CACHE_GROUP, 1 );
 delete_user_meta($m4is_f087, self::USERMETA_KEY );
 wp_cache_delete(self::m4is_b816($m4is_f087 ), self::CACHE_GROUP );
 self::m4is_a1476($m4is_f087 );
 
}  private static 
function m4is_l66190(array $m4is_k824, WP_User $m4is_l17096 ): array {
$m4is_k824['memb_user']['user_id']=$m4is_l17096->ID;
 $m4is_k824['memb_user']['email']=strtolower($m4is_l17096->user_email );
 $m4is_k824['memb_user']['loginname']=strtolower($m4is_l17096->user_login );
 $m4is_k824['memb_user']['crm_id']=0;
 $m4is_k824['memb_user']['languages']=m4is_d86::m4is_e93706();
 $m4is_k824['memb_user']['revision']=self::$m4is_r1546->m4is_w7426();
 $m4is_k824['memb_user']['source']='local';
 $m4is_k824['memb_user']['nextupdate']=(time()+ self::SESSION_TTL );
 $m4is_k824['memb_user']['version']=self::M4IS_C4372;
 $m4is_k824['memb_user']['user_revision']=self::m4is_k340($m4is_l17096->ID );
 return $m4is_k824;
 
} private static 
function m4is_i695(array $m4is_k824, array $m4is_i935 ): array {
 $m4is_k824['keap']['contact']=array_change_key_case($m4is_i935, CASE_LOWER );
 $m4is_k824['keap']['meta']['contact']=isset($m4is_i935['!LastUpdated'])? (int) $m4is_i935['!LastUpdated']: time();
 $m4is_k824['memb_user']['crm_id']=$m4is_i935['Id']?? 0;
 $m4is_k824['memb_user']['source']='keap';
 return $m4is_k824;
 
} private static 
function m4is_k86(array $m4is_k824, array $m4is_i935 ): array {
$m4is_k824['memb_user']['canc_homepage']=0;
 $m4is_k824['memb_user']['login_page']=0;
 $m4is_k824['memb_user']['membership_id']=0;
 $m4is_k824['memb_user']['membership_level']=0;
 $m4is_k824['memb_user']['membership_names']='';
 $m4is_k824['memb_user']['membership_tags']='';
 $m4is_k824['memb_user']['payf_homepage']=0;
 $m4is_k824['memb_user']['roles']='';
 $m4is_k824['memb_user']['susp_homepage']=0;
 $m4is_k824['memb_user']['tags']=isset($m4is_k824['keap']['contact']['groups'])? $m4is_k824['keap']['contact']['groups']: '';
 $m4is_k824['memb_user']['theme']='';
 return $m4is_k824;
 
} private static 
function m4is_e50732(array $m4is_k824, array $m4is_l9321 ): array {
if(empty($m4is_k824['keap']['contact']['groups'])){
return $m4is_k824;
 
}$m4is_k824['memb_user']['tags']=$m4is_k824['keap']['contact']['groups'];
 $m4is_x84 =m4is_k865::m4is_z2906();
 $m4is_x84 =isset($m4is_x84['mc'])? $m4is_x84['mc']: [];
 $m4is_p47016 =[];
 foreach ($m4is_l9321 as $m4is_d07693=>$tag){
if(isset($m4is_x84[$tag])){
$m4is_p47016[]=$m4is_x84[$tag];
 
}
}$m4is_k824['memb_user']['tag_names']=empty($m4is_p47016 )? '' : implode(',', $m4is_p47016 );
 return $m4is_k824;
 
} private static 
function m4is_s65026(array $m4is_k824, array $m4is_l9321 ): array {
static $m4is_m96240;
 if(empty($m4is_k824['keap']['contact']['groups'])){
return $m4is_k824;
 
}$m4is_m96240 ??= self::$m4is_r1546->m4is_j498('memberships' );
 if(empty($m4is_m96240 )||!is_array($m4is_m96240 )){
return $m4is_k824;
 
}$m4is_f087 =$m4is_k824['memb_user']['user_id'];
 $m4is_k9075 =get_user_meta($m4is_f087, 'login_count', true );
  $m4is_b2785 =[];
 $m4is_f75194 =[];
 $m4is_e863 =[];
 $m4is_n680 =-1;
 $m4is_q076 =-1;
 foreach ($m4is_m96240 as $m4is_d07693 =>$m4is_w64 ){
$m4is_w64['susp_id']=isset($m4is_w64['susp_id'])? $m4is_w64['susp_id']: 0;
 $m4is_w64['canc_id']=isset($m4is_w64['canc_id'])? $m4is_w64['canc_id']: 0;
 $m4is_w64['payf_id']=isset($m4is_w64['payf_id'])? $m4is_w64['payf_id']: 0;
 $m4is_w64['addltag_ids']=isset($m4is_w64['addltag_ids'])? $m4is_w64['addltag_ids']: '';
 $has_primary_tag =in_array($m4is_d07693, $m4is_l9321 );
 $has_alt_tags =(bool) count(array_intersect($m4is_l9321, array_filter(explode(',', $m4is_w64['addltag_ids']))));
 $has_payf_tag =(bool) in_array($m4is_w64['payf_id'], $m4is_l9321 );
 $has_susp_tag =(bool) in_array($m4is_w64['suspend_id'], $m4is_l9321 );
 $has_canc_tag =(bool) in_array($m4is_w64['cancel_id'], $m4is_l9321 );
  if($has_primary_tag ||$has_alt_tags){
if($has_payf_tag ||$has_canc_tag ||$has_susp_tag){
if(intval($m4is_w64['login_redirect_priority'])>= $m4is_n680){
if(empty($m4is_k824['memb_user']['login_page'])){
$m4is_k824['memb_user']['login_page']=$has_payf_tag ? $m4is_w64['payf_homepage']: $m4is_k824['memb_user']['login_page'];
 $m4is_k824['memb_user']['login_page']=$has_susp_tag ? $m4is_w64['susp_homepage']: $m4is_k824['memb_user']['login_page'];
 $m4is_k824['memb_user']['login_page']=$has_canc_tag ? $m4is_w64['canc_homepage']: $m4is_k824['memb_user']['login_page'];
 
}$m4is_n680 =isset($m4is_w64['login_redirect_priority'])? $m4is_w64['login_redirect_priority']: 0;
 
}
}else{
if($m4is_w64['level']>= $m4is_k824['memb_user']['membership_level']){
if(is_array($m4is_w64['roles'])&&!empty($m4is_w64['roles'])){
$m4is_e863 =array_merge($m4is_e863, $m4is_w64['roles']);
 
}$m4is_k824['memb_user']['membership_id']=$m4is_d07693;
 $m4is_k824['memb_user']['theme']=isset($m4is_w64['theme'])? $m4is_w64['theme']: '';
 $m4is_k824['memb_user']['membership_level']=$m4is_w64['level'];
 $m4is_b2785[]=$m4is_d07693;
 $m4is_f75194[]=$m4is_w64['name'];
 
}if(intval($m4is_w64['login_redirect_priority'])>= $m4is_n680 ){
$m4is_k824['memb_user']['login_page']=isset($m4is_w64['login_page'])? $m4is_w64['login_page']: 0;
 $m4is_k824['memb_user']['first_login_page']=isset($m4is_w64['first_login_page'])? $m4is_w64['first_login_page']: 0;
 $m4is_k824['memb_user']['logout_page']=isset($m4is_w64['logout_page'])? $m4is_w64['logout_page']: 0;
 $m4is_n680 =isset($m4is_w64['login_redirect_priority'])? $m4is_w64['login_redirect_priority']: 0;
 if($m4is_k9075 < 1 ){
$m4is_k824['memb_user']['login_page']=$m4is_k824['memb_user']['first_login_page'];
 
}
}
}
}if(is_array($m4is_b2785)&&count($m4is_b2785)> 0){
$m4is_k824['memb_user']['membership_tags']=implode(',', $m4is_b2785 );
 $m4is_k824['memb_user']['membership_names']=implode(',', $m4is_f75194 );
 
}
}$m4is_k824['memb_user']['roles']=is_array($m4is_e863 )? implode(',', array_unique($m4is_e863 )): '';
 return $m4is_k824;
 
} private static 
function m4is_h37192(int $m4is_f087, array $m4is_i935 ): void {
if(!self::$m4is_r1546->m4is_j498('settings', 'sync_meta_updates', false )){
return;
 
}global $wpdb;
 $m4is_e80 =$wpdb->usermeta;
 $m4is_c8657 ='memb\_%';
 $m4is_v2613 ="SELECT `meta_key`, `meta_value` FROM {$m4is_e80
} WHERE `user_id` = {$m4is_f087
} AND `meta_key` LIKE '{$m4is_c8657
}' ";
 $m4is_i61069 =$wpdb->get_results($m4is_v2613, OBJECT_K );
 foreach ($m4is_i935 as $m4is_l9671 =>$m4is_v586 ){
$m4is_b39640 ="memb_{$m4is_l9671
}";
 if(!isset($m4is_i61069[$m4is_b39640]->meta_value )||$m4is_i61069[$m4is_b39640]->meta_value <> $m4is_v586 ){
update_user_meta($m4is_f087, $m4is_b39640, $m4is_v586 );
 
}
}
}  private static 
function m4is_n98(int $m4is_f087, array $m4is_k824 ): void {
wp_cache_set(self::m4is_b816($m4is_f087 ), $m4is_k824, self::CACHE_GROUP, self::CACHE_TTL );
 
} private static 
function m4is_d50(int $m4is_f087 ): array {
$m4is_t265 =false;
 $m4is_k824 =wp_cache_get(self::m4is_b816($m4is_f087 ), self::CACHE_GROUP, false, $m4is_t265 );
 if($m4is_t265 === false ||!is_array($m4is_k824 )){
return [];
 
}return $m4is_k824;
 
} private static 
function m4is_b816(int $m4is_f087 ): string {
return 'session/' . $m4is_f087;
 
}  private static 
function m4is_z3248(int $m4is_f087 ): array {
$m4is_l9671 ="id:{$m4is_f087
}";
 if(!array_key_exists($m4is_l9671, self::$m4is_a71 )){
return [];
 
}$m4is_k824 =self::$m4is_a71[$m4is_l9671];
   unset(self::$m4is_a71[$m4is_l9671]);
 self::$m4is_a71[$m4is_l9671]=$m4is_k824;
 return $m4is_k824;
 
} private static 
function m4is_w62(int $m4is_f087, array $m4is_k824 ): void {
unset(self::$m4is_a71["id:{$m4is_f087
}"]);
 self::$m4is_a71["id:{$m4is_f087
}"]=$m4is_k824;
 if(count(self::$m4is_a71 )> self::RING_CACHE_SIZE ){
array_shift(self::$m4is_a71 );
 
}
} private static 
function m4is_a1476(int $m4is_f087 ): void {
unset(self::$m4is_a71["id:{$m4is_f087
}"]);
 
}  public static 
function m4is_v2031(string $m4is_w42, string $m4is_a173 ): void {
$m4is_b6306 =self::m4is_x37529();
 self::$m4is_f016 =self::m4is_o521();
 self::$m4is_n69048 =self::m4is_f26($m4is_w42 );
 self::$m4is_n69048[$m4is_w42][]=['message' =>$m4is_a173, 'expires' =>time()+ self::SESSION_TTL, ];
 $_COOKIE[$m4is_b6306]=self::$m4is_f016;
 
} public static 
function m4is_f26(string $m4is_w42 ): array {
$m4is_b6306 =self::m4is_x37529();
 $m4is_n69048 =[];
 if(isset($_COOKIE[$m4is_b6306])){
$m4is_t6056 =$_COOKIE[$m4is_b6306];
 $m4is_z984 =self::m4is_o380($m4is_t6056 );
 $m4is_n69048 =get_transient($m4is_z984 );
 $m4is_n69048 =is_array($m4is_n69048 )? $m4is_n69048 : [];
 delete_transient($m4is_z984 );
  
}return $m4is_n69048;
 
} public static 
function m4is_j97318(): void {
if(empty(self::$m4is_n69048 )){
return;
 
}$m4is_b6306 =self::m4is_x37529();
 $m4is_t6056 =$_COOKIE[$m4is_b6306]?? self::m4is_o521();
 $m4is_k731 =self::m4is_o380($m4is_t6056 );
 setcookie($m4is_b6306, $m4is_t6056, time()+ MINUTE_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
 set_transient($m4is_k731, self::$m4is_n69048, M4IS_M87219 );
 
}  private static 
function m4is_o521(): string {
return sha1(time(). '.' . m4is_a01587::m4is_y342(). '.' . ($_SERVER['REMOTE_PORT']?? mt_rand(0, 65536 )). '.' . bin2hex(random_bytes(8 )));
 
} private static 
function m4is_x37529(): string {
return 'wp_memberium_messages';
 
} private static 
function m4is_o380(string $m4is_t6056 ): string {
return 'memberium/messages/' . $m4is_t6056;
 
}
}

