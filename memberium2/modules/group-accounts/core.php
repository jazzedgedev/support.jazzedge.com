<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_u7102 {
private const M4IS_C4372 ='4.0';
 private const M4IS_Z25 ='memberium/groups/max_children';
 private const M4IS_K66479 ='memberium/groups/source';
 private $m4is_r1546;
 private $m4is_e6426;
 private $m4is_r9613;
 private $m4is_o36902;
 private $m4is_q25;
 private $m4is_h54;
 private $m4is_e0213;
 private $m4is_c672;
     static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_i702();
 if(!$this->m4is_h54 ){
return;
 
}$this->m4is_y1648();
 $this->m4is_d4861();
 add_action('memberium/modules/loaded', [$this, 'm4is_f3276'], 10 );
 
} private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_e6426 =$this->m4is_r1546->m4is_b32198();
 $this->m4is_e0213 =$this->m4is_f27408();
 $this->m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 $this->m4is_h54 =true ||m4is_s52::m4is_b12067(['unlimited', 'umbrella']);
 $this->m4is_q25 ='';
 $this->m4is_e0213 =$this->m4is_f27408();
 $this->m4is_o36902 =false;
 $this->m4is_c672 =[];
 
} private 
function m4is_y1648(): void {
$this->m4is_r1546->m4is_p39(['m4is_s4820' =>__DIR__ . '/cpt', 'm4is_u81' =>__DIR__ . '/database', 'm4is_r82473' =>__DIR__ . '/session', 'm4is_l870' =>__DIR__ . '/team', 'm4is_n63' =>__DIR__ . '/admin/admin', 'm4is_n63956' =>__DIR__ . '/catchers', 'm4is_q913' =>__DIR__ . '/admin/screen', 'm4is_j01762' =>__DIR__ . '/cron', 'm4is_h50968' =>__DIR__ . '/frontend', 'm4is_p635' =>__DIR__ . '/shortcodes', ]);
 
} public 
function m4is_f3276(){
if(is_admin()){
m4is_n63::m4is_c26();
 
}else{
m4is_h50968::m4is_c26();
 
}if(wp_doing_cron()){
m4is_j01762::m4is_c26();
 
}include_once __DIR__ . '/api.php';
 
} private 
function m4is_d4861(){
add_action('init', ['m4is_s4820', 'm4is_s8624'], 1 );
  add_action('init', [$this, 'm4is_z417']);
  add_action('delete_post', ['m4is_l870', 'm4is_o725'], 10, 2 );
 add_action('delete_user', [$this, 'm4is_m87'], 10, 1 );
 add_action('delete_user', ['m4is_l870', 'm4is_f196'], 10, 1 );
 add_action('memberium_email_change', [$this, 'm4is_f8293'], 10, 4 );
  add_filter('memberium_contact_load', ['m4is_r82473', 'm4is_j43'], 10, 1 );
  add_filter('memberium_contact_load', ['m4is_r82473', 'm4is_q53702'], 5, 1 );
 add_filter('memberium_contact_load', ['m4is_r82473', 'm4is_y14'], 15, 1 );
 add_action('memberium/session/updated', ['m4is_l870', 'm4is_s893'], 10, 2 );
 add_action('memberium/session/updated', ['m4is_r82473', 'm4is_p8901'], 20, 2 );
 add_filter('memberium/session/filter', ['m4is_r82473', 'm4is_z67842'], 10, 2 );
  
}      public 
function m4is_d326($m4is_l9671 ='' ){
 if(empty($m4is_l9671 )){
return $this->m4is_e0213;
 
} return isset($this->m4is_e0213[$m4is_l9671])? $this->m4is_e0213[$m4is_l9671]: null;
 
} private 
function m4is_x8463(): array {
return ['add_child_actionset' =>0, 'child_added_goal' =>'', 'child_cancel_goal' =>'', 'parent_added_goal' =>'', 'child_cancel_actionset' =>0, 'ecommerce' =>1, 'inherited_fields' =>'', 'ld_quiz_notify_parent' =>0, 'max_child_accounts' =>10, 'parent_added_actionset' =>0, 'parent_cache_ttl' =>86400, 'parent_field' =>'ReferralCode', 'parent_tags' =>'', 'tag_mask' =>'', 'tag_mask_field' =>'', 'tag_translation' =>[], ];
 
} public 
function m4is_f27408(){
$this->m4is_e0213 =get_option('memberium_umbrella_settings', []);
 $m4is_y642 =$this->m4is_x8463();
 $this->m4is_e0213 =wp_parse_args($this->m4is_e0213, $m4is_y642);
  return $this->m4is_e0213;
 
} public 
function m4is_g6642($m4is_e0213 =false ){
 if($m4is_e0213 === false ){
$m4is_e0213 =$this->m4is_e0213;
 
} update_option('memberium_umbrella_settings', $m4is_e0213, 'yes' );
 
}      public 
function m4is_w45(){
return self::M4IS_C4372;
 
} public 
function m4is_d412(): string {
return empty($this->m4is_e0213['parent_tags'])? '' : $this->m4is_e0213['parent_tags'];
 
}public 
function m4is_o136(): int {
return (int) $this->m4is_e0213['active_child_tag'];
 
} public 
function m4is_p6046(){
return $this->m4is_e0213['tag_mask'];
 
} public 
function m4is_l2370(){
return $this->m4is_e0213['parent_cache_ttl'];
 
} public 
function m4is_h106(){
return $this->m4is_e0213['tag_mask_field'];
 
} public 
function m4is_s54627(){
return empty($this->m4is_e0213['parent_field'])? '' : $this->m4is_e0213['parent_field'];
 
} public 
function m4is_h1820(){
return $this->m4is_e0213['child_cancel_actionset'];
 
} public 
function m4is_i669(){
return $this->m4is_e0213['child_cancel_goal'];
 
} public 
function m4is_j6392(){
  return $this->m4is_e0213['add_child_actionset'];
 
}public 
function m4is_p782(){
return $this->m4is_e0213['inherited_fields'];
 
} private 
function m4is_b369(){
  return $this->m4is_e0213['parent_added_actionset'];
 
}    public 
function m4is_k87($m4is_l9321 ='' ){
$this->m4is_e0213['tag_mask']=trim($m4is_l9321 );
 
} public 
function m4is_a02364(int $m4is_w46279 =86400 ){
$this->m4is_e0213['parent_cache_ttl']=$m4is_w46279;
 
} public 
function m4is_g96286($m4is_x374 =0, $m4is_c2061 =0 ){
 if(!$m4is_x374 &&!$m4is_c2061 ){
return;
 
} if($m4is_c2061 == 0 ){
unset($this->m4is_c672[$m4is_x374]);
 
} if($m4is_c2061 > 0 ){
$this->m4is_c672[$m4is_x374]=$m4is_c2061;
 
} $this->m4is_e0213['tag_translation']=$this->m4is_c672;
 
} public 
function m4is_g9076($m4is_v61358 ='Email3' ){
$this->m4is_e0213['parent_field']=$m4is_v61358;
 
} public 
function m4is_u1627(int $m4is_n86039 ){
$this->m4is_e0213['add_child_actionset']=$m4is_n86039;
 
} public 
function m4is_n861(int $m4is_n86039 ){
$this->m4is_e0213['parent_added_actionset']=$m4is_n86039;
 
} public 
function m4is_w42175(int $m4is_n86039 ){
$this->m4is_e0213['child_cancel_actionset']=$m4is_n86039;
 
} public 
function m4is_b62($m4is_r637, $m4is_y5697 =true ){
 if($m4is_y5697 === null ){
unset($this->m4is_q25[$m4is_r637]);
 
} else{
$this->m4is_q25[$m4is_r637]=(boolean) $m4is_y5697;
 
} $this->m4is_e0213['inherited_fields']=$this->m4is_q25;
 
} public 
function m4is_z250($m4is_g360 ='' ){
$this->m4is_e0213['tag_mask_field']=$m4is_g360;
 
}public 
function m4is_y8765(): bool {
return $this->m4is_o36902;
 
}    public 
function m4is_z417(): void {
if($_SERVER['REQUEST_METHOD']<> 'POST' ||empty($_POST['formtype'])){
return;
 
}$m4is_w205 ='m4is_n63956';
 $m4is_o369 =['childdisconnect' =>[$m4is_w205, 'm4is_g05834' ], 'childenroll' =>[$m4is_w205, 'm4is_m0786' ], 'umbrella_points_transfer' =>[$m4is_w205, 'm4is_v608' ], 'umbrella_csv_download' =>[$m4is_w205, 'm4is_g2684' ], 'memberium/group-accounts/team/member/update' =>[$m4is_w205, 'm4is_j94' ], ];
 if(!array_key_exists($_POST['formtype'], $m4is_o369 )){
return;
 
}$m4is_o369[$_POST['formtype']]();
 return;
 if($_POST['formtype']== 'childdisconnect'){
$m4is_w205::m4is_g05834();
 
}elseif($_POST['formtype']== 'childenroll'){
$m4is_w205::m4is_m0786();
 
}elseif($_POST['formtype']== 'umbrella_points_transfer'){
$m4is_w205::m4is_v608();
 
}elseif($_POST['formtype']== 'umbrella_csv_download'){
$m4is_w205::m4is_g2684();
 
}elseif($_POST['formtype']== 'umbrella_csv_download'){
$m4is_w205::m4is_g2684();
 
}
}          public 
function m4is_d6406(){
return !in_array($this->m4is_s54627(), ['Email', 'EmailAddress2', 'EmailAddress3']);
 
} public 
function m4is_n94($m4is_t816 ){
 if(!current_user_can('manage_options' )){
 $m4is_q95421 =$this->m4is_z45812();
  $m4is_t816 =array_merge($m4is_t816, $m4is_q95421 );
 
} return $m4is_t816;
 
}public 
function m4is_r195($m4is_f087 =null ): bool {
static $m4is_r8695;
 $m4is_r8695 ??= array_filter(explode(',', $this->m4is_d412()));
 $m4is_f087 ??= self::$m4is_r1546->m4is_x66();
 $m4is_f406 =array_filter(explode(',', m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tags', '' )));
 return (bool) array_intersect($m4is_r8695, $m4is_f406 );
 
} public 
function m4is_l274($m4is_l9321 ): bool {
$m4is_r8695 =$this->m4is_d412();
 if(empty($m4is_r8695 )){
return false;
 
}$m4is_r8695 =array_filter(explode(',', $this->m4is_d412()));
 $m4is_l9321 =is_array($m4is_l9321 )? array_filter($m4is_l9321 ): array_filter(explode(',', $m4is_l9321 ));
 return (bool) array_intersect($m4is_r8695, $m4is_l9321 );
 
} public 
function m4is_f8293($m4is_h21895 =0, $m4is_f087 =0, $m4is_m158 ='', $m4is_f4930 ='' ){
$m4is_k937 =$this->m4is_s54627();
 $lparent_field =strtolower($this->m4is_s54627());
 if(!in_array($lparent_field, ['email', 'emailaddress2', 'emailaddress3'])){
return;
 
}if(empty($m4is_m158 )||empty($m4is_f4930 )){
return;
 
}$m4is_y66291 =[$m4is_k937 =>$m4is_m158 ];
 $m4is_v30712 =$this->m4is_n681($m4is_y66291 );
 if(is_array($m4is_v30712 )&&!empty($m4is_v30712 )){
foreach($m4is_v30712 as $m4is_h21895 ){
$m4is_a89 =[$m4is_k937 =>$m4is_f4930, ];
 m4is_p40::m4is_x6560($m4is_h21895, $m4is_a89);
  
}
}
}   public 
function m4is_h43206(int $m4is_i341 ): string {
$m4is_b76 =get_user_meta($m4is_i341, self::M4IS_K66479, true );
 return $m4is_b76 ? $m4is_b76 : 'contact_table';
 
} public 
function m4is_u073(int $m4is_i341 ): array {
$m4is_i341 =$m4is_i341 ? $m4is_i341 : self::$m4is_r1546->m4is_x66();
 $m4is_z918 =m4is_p40::m4is_w58096($m4is_i341 );
 $m4is_b76 =get_user_meta($m4is_i341, self::M4IS_K66479, true );
 $m4is_q95421 =[];
 if($m4is_b76 == 'relationship_table' ){
$m4is_q95421 =$this->m4is_g712($m4is_i341 );
 
}else{
$m4is_q95421 =$this->m4is_q18342($m4is_i341 );
 $this->m4is_h263($m4is_i341, $m4is_q95421 );
 
}return $m4is_q95421;
 
} private 
function m4is_h263(int $m4is_i341, array $m4is_q95421 ): void {
global $wpdb;
 $m4is_e80 =m4is_u81::m4is_a6804();
 foreach($m4is_q95421 as $m4is_m617 ){
$m4is_l91805 =['parent_uid' =>$m4is_i341, 'child_uid' =>$m4is_m617 ];
 $wpdb->insert($m4is_e80, $m4is_l91805 );
 
}update_user_meta($m4is_i341, self::M4IS_K66479, 'relationship_table' );
 
} private 
function m4is_g712(int $m4is_i341 ): array {
global $wpdb;
 $m4is_o80491 =$this->m4is_y86($m4is_i341 );
 $m4is_e80 =m4is_u81::m4is_a6804();
 $m4is_v2613 ="SELECT `child_uid` FROM %i WHERE `parent_uid` = %d AND `child_uid` > 0 AND `child_uid` <> %d AND `active` = 1  ORDER BY `id` ASC";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_i341, $m4is_i341 );
 if($m4is_o80491 ){
$m4is_v2613 .= $wpdb->prepare(" LIMIT %d", $m4is_o80491 );
 
}$m4is_q95421 =$wpdb->get_col($m4is_v2613 );
 return $m4is_q95421;
 
} private 
function m4is_q18342(int $m4is_i341 ): array {
global $wpdb;
 $m4is_h21895 =m4is_p40::m4is_w58096($m4is_i341 );
 $m4is_i01365 =m4is_p40::m4is_p67($m4is_h21895, false, true );
 $m4is_v61358 =$this->m4is_s54627();
 $m4is_b6187 =empty($m4is_i01365[$m4is_v61358])? '' : $m4is_i01365[$m4is_v61358];
 if(substr($m4is_b6187, 0, 5 )!= 'prnt-' ){
return [];
 
}$m4is_x10362 =$this->m4is_q31028($m4is_b6187, 'child' );
 $m4is_o80491 =$this->m4is_y86($m4is_i341 );
 if(empty($m4is_b6187 )){
return [];
 
}$m4is_e80 =m4is_p40::m4is_o1723();
 $m4is_v2613 ="SELECT `id` FROM `{$m4is_e80
}` WHERE `fieldname` = %s AND `value` = %s AND `appname` = %s ORDER BY `id` ASC";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_v61358, $m4is_x10362, $this->m4is_r9613 );
 $m4is_v2613 =$m4is_o80491 ? $m4is_v2613 . " LIMIT {$m4is_o80491
}" : $m4is_v2613;
 $m4is_l8931 =$wpdb->get_col($m4is_v2613 );
 $m4is_q95421 =is_array($m4is_l8931 )? array_filter($m4is_l8931 ): [];
 $m4is_b6438 =[];
  foreach($m4is_l8931 as $m4is_h21895 ){
$m4is_b6438[]=m4is_p40::m4is_i6158($m4is_h21895 );
 
}$m4is_b6438 =array_filter($m4is_b6438 );
 return $m4is_b6438;
 
} public 
function m4is_l7085(int $m4is_i341 =0 ): int {
global $wpdb;
 $m4is_i341 =$m4is_i341 ? $m4is_i341 : self::$m4is_r1546->m4is_x66();
 $m4is_b76 =get_user_meta($m4is_i341, self::M4IS_K66479, true );
 if($m4is_b76 <> 'relationship_table' ){
$m4is_q95421 =$this->m4is_q18342($m4is_i341 );
 $this->m4is_h263($m4is_i341, $m4is_q95421 );
 return count($m4is_q95421 );
 
}$m4is_e80 =m4is_u81::m4is_a6804();
 $m4is_v2613 ="SELECT COUNT(*) FROM %i WHERE `parent_uid` = %d AND `child_uid` > 0 AND `child_uid` <> %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_i341, $m4is_i341);
 $m4is_h973 =(int) $wpdb->get_var($m4is_v2613 );
 return $m4is_h973;
 
}    public 
function m4is_a6938($m4is_y66291 =[]){
 global $wpdb;
  if(empty($m4is_y66291['contact_id'])){
return false;
 
} $m4is_i935 =empty($m4is_y66291['contact'])? []: $m4is_y66291['contact'];
 $m4is_h21895 =empty($m4is_y66291['contact_id'])? 0 : $m4is_y66291['contact_id'];
 $m4is_i341 =empty($m4is_y66291['parent_id'])? 0 : $m4is_y66291['parent_id'];
 $m4is_j57239 =$this->m4is_e0213['parent_field'];
 $m4is_e1385 =array_filter(explode(',', $this->m4is_e0213['parent_tags']));
 $m4is_d27 =false;
  if(empty($m4is_i935[$m4is_j57239])){
return false;
 
} $m4is_y642 =['appname' =>$this->m4is_r1546->m4is_i76('appname'), 'contact_id' =>isset($m4is_i935['Id'])? $m4is_i935['Id']: 0, 'parent_field' =>$this->m4is_e0213['parent_field'], 'parent_id' =>isset($m4is_i935[$m4is_j57239])? $m4is_i935[$m4is_j57239]: '', ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642);
 $m4is_y66291['parent_id']=$this->m4is_q31028($m4is_y66291['parent_id'], 'parent' );
  $m4is_e80 =m4is_p40::m4is_o1723();
 $m4is_v2613 ="SELECT `c1`.`id`, `c2`.`value` as `tags` FROM `{$m4is_e80
}` AS `c1`, `{$m4is_e80
}` AS `c2` WHERE `c1`.`appname` = %s AND `c1`.`fieldname` = %s AND `c1`.`value` = %s AND `c1`.`id` <> %d AND `c2`.`id` = `c1`.`id` AND `c2`.`fieldname` = 'Groups' ORDER BY `c1`.`id`; ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_y66291['appname'], $m4is_y66291['parent_field'], $m4is_y66291['parent_id'], $m4is_y66291['contact_id']);
 $m4is_i01365 =$wpdb->get_row($m4is_v2613 );
  $m4is_l9321 =isset($m4is_i01365->tags)? explode(',', $m4is_i01365->tags): [];
  if(array_intersect($m4is_e1385, $m4is_l9321 )){
$m4is_d27 =$m4is_i01365->id;
 
} return $m4is_d27;
 
} public 
function m4is_z04271($m4is_y66291 =[]){
 global $wpdb;
  $m4is_r9613 =$this->m4is_r1546->m4is_i76('appname');
 $m4is_v61358 =$this->m4is_e0213['parent_field'];
 $m4is_z06985 =$this->m4is_r1546->m4is_z56();
 $m4is_i64730 =strtolower($this->m4is_e0213['parent_field']);
 $m4is_h21895 =isset($m4is_y66291['contact_id'])? $m4is_y66291['contact_id']: $m4is_z06985;
 $m4is_i341 =$this->m4is_r1546->m4is_v921($m4is_h21895, $m4is_v61358 );
 $m4is_p0872 =m4is_p40::m4is_o1723();
  $m4is_y642 =['appname' =>$this->m4is_r9613, 'parent_field' =>$m4is_v61358, 'contact_id' =>$m4is_h21895, 'limit' =>0, 'offset' =>0, 'parent_id' =>$m4is_i341, ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642 );
  if($m4is_y66291['parent_id']<> $this->m4is_q31028($m4is_y66291['parent_id'], 'parent' )){
return 0;
 
} $m4is_y66291['parent_id']=$this->m4is_q31028($m4is_y66291['parent_id'], 'child' );
 $m4is_v2613 ="SELECT count(*) FROM `{$m4is_p0872
}` WHERE `appname` = %s AND `fieldname` = %s AND `value` = %s AND `id` <> %d ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_y66291['appname'], $m4is_y66291['parent_field'], $m4is_y66291['parent_id'], $m4is_y66291['contact_id']);
 $m4is_h973 =$wpdb->get_var($m4is_v2613 );
  return $m4is_h973;
 
} public 
function m4is_n756($m4is_y66291 =[]){
  $m4is_y642 =['user_id' =>self::$m4is_r1546->m4is_x66(), ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642 );
  if($this->m4is_r1546->m4is_g6860($m4is_y66291['user_id'])){
return m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', '!parent_id', 0 );
 
}
} public 
function m4is_n681($m4is_y66291 =[]){
 global $wpdb;
  $m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 $m4is_v61358 =$this->m4is_e0213['parent_field'];
 $m4is_z06985 =$this->m4is_r1546->m4is_z56();
 $m4is_i64730 =strtolower($this->m4is_e0213['parent_field']);
 $m4is_h21895 =isset($m4is_y66291['contact_id'])? $m4is_y66291['contact_id']: $m4is_z06985;
 $m4is_i341 =$this->m4is_r1546->m4is_v921($m4is_h21895, $m4is_v61358 );
 $m4is_p0872 =m4is_p40::m4is_o1723();
  $m4is_y642 =['appname' =>$this->m4is_r9613, 'parent_field' =>$m4is_v61358, 'contact_id' =>$m4is_h21895, 'limit' =>0, 'offset' =>0, 'parent_id' =>$m4is_i341, ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642 );
  if(empty($m4is_y66291['parent_id'])||$m4is_y66291['parent_id']<> $this->m4is_q31028($m4is_y66291['parent_id'], 'parent' )){
return [];
 
} $m4is_y66291['parent_id']=$this->m4is_q31028($m4is_y66291['parent_id'], 'child' );
 $m4is_c578 =($m4is_y66291['limit']> 0 )? " LIMIT {$m4is_y66291['limit']
} OFFSET {$m4is_y66291['offset']
} " : '';
  $m4is_v2613 ="
			SELECT `c1`.`id`
			FROM `{$m4is_p0872
}` AS `c1`
			LEFT JOIN `{$m4is_p0872
}` AS `c2`
			ON ( `c2`.`id` = `c1`.`id` AND `c2`.`appname` = %s AND `c2`.`fieldname` = 'LastName' )
			LEFT JOIN `{$m4is_p0872
}` AS `c3`
			ON ( `c3`.`id` = `c1`.`id` AND `c3`.`appname` = %s AND `c3`.`fieldname` = 'FirstName' )
			WHERE `c1`.`appname` = %s
			AND `c1`.`fieldname` = %s
			AND `c1`.`value` = %s
			AND `c1`.`id` <> %d
			ORDER BY `c2`.`value`, `c3`.`value`, `c1`.`id` ASC
			{$m4is_c578
}
		";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_y66291['appname'], $m4is_y66291['appname'], $m4is_y66291['appname'], $m4is_y66291['parent_field'], $m4is_y66291['parent_id'], $m4is_y66291['contact_id']);
 $m4is_v30712 =$wpdb->get_col($m4is_v2613 );
  return $m4is_v30712;
 
} public 
function m4is_z45812($m4is_y66291 =[]){
 $m4is_v30712 =$this->m4is_n681($m4is_y66291 );
  if(empty($m4is_v30712 )){
return [];
 
} $wp_user_ids =[];
  foreach($m4is_v30712 as $m4is_h21895 ){
$m4is_f087 =m4is_p40::m4is_i6158($m4is_h21895 );
 if($m4is_f087 ){
$wp_user_ids[]=$m4is_f087;
 
}
} return $wp_user_ids;
 
} public 
function m4is_y817(): int {
 $m4is_v30712 =$this->m4is_n681();
  return (int) count($m4is_v30712 );
 
} public 
function m4is_x46653($m4is_y66291 =[]){
 global $wpdb;
  $m4is_y642 =['child_contact' =>[], 'generate_password' =>true, 'optin' =>true, 'parent_contact_id' =>0, 'parent_contact' =>[], ];
 $m4is_y66291 =wp_parse_args($m4is_y66291, $m4is_y642 );
   if($m4is_y66291['parent_contact_id']){
$m4is_y66291['parent_contact']=m4is_p40::m4is_p67($m4is_y66291['parent_contact_id']);
 
} if($m4is_y66291['child_contact_id']){
$m4is_y66291['child_contact']=m4is_p40::m4is_p67($m4is_y66291['child_contact_id']);
 
} if(empty($m4is_y66291['child_contact'])||empty($m4is_y66291['parent_contact'])){
return -1;
 
} $m4is_v61358 =$this->m4is_s54627();
 $m4is_b6187 =isset($m4is_y66291['parent_contact'][$m4is_v61358])? $m4is_y66291['parent_contact'][$m4is_v61358]: '';
  if(empty($m4is_b6187 )){
return -2;
 
} $m4is_k937 =$this->m4is_s54627();
 if(!empty($m4is_y66291['child_contact'][$m4is_k937])){
return -3;
 
} $m4is_r37596 =$this->m4is_r1546->m4is_j498();
 $m4is_r6234 =isset($m4is_r37596['settings']['password_field'])? $m4is_r37596['settings']['password_field']: '';
  if($m4is_y66291['m4is_a601']&&empty($m4is_y66291['child_contact'][$m4is_r6234])){
$m4is_y66291['child_contact'][$m4is_r6234]=$this->m4is_r1546->m4is_a601();
 
} $m4is_l61385 =(int) $m4is_y66291['child_contact']['Id'];
 $m4is_d27 =(int) $m4is_y66291['parent_contact_id']['Id'];
  if(empty($m4is_y66291['child_contact']['Id'])&&!empty($m4is_y66291['child_contact_id']['Email'])){
 $m4is_a89 =$m4is_y66291['child_contact'];
 $m4is_a89[$m4is_k937]=$this->m4is_q31028($m4is_b6187, 'child' );
  $m4is_l61385 =(int) m4is_p40::m4is_k82670($m4is_a89);
  if($m4is_l61385 ){
 $m4is_y66291 =['contact_id' =>$m4is_l61385, 'cache_ttl' =>0 ];
 $this->m4is_r1546->m4is_n361($m4is_y66291 );
  $m4is_y66291['child_contact_id']=$m4is_l61385;
 $m4is_y66291['child_contact']['Id']=$m4is_l61385;
 $m4is_y66291['parent_contact_id']=(int) $m4is_y66291['parent_contact_id'];
  if($m4is_y66291['optin']){
m4is_p40::m4is_y935($m4is_y66291['child_contact']['Email'], 'Added by Memberium Umbrella Account System' );
 
} m4is_c69807::m4is_z3902($m4is_l61385, $this->m4is_e0213['child_added_goal']);
 m4is_c69807::m4is_z3902($m4is_d27, $this->m4is_e0213['parent_added_goal']);
 m4is_j4156::m4is_w4805($m4is_l61385, (int) $this->m4is_e0213['add_child_actionset']);
 m4is_j4156::m4is_w4805($m4is_d27, (int) $this->m4is_e0213['parent_added_actionset']);
 
}
} return $m4is_l61385;
 
} public 
function m4is_q31028($m4is_v3458 ='', $m4is_z75923 ='raw' ){
 if(!in_array(substr($m4is_v3458, 0, 5 ), ['chld-', 'prnt-'])){
$m4is_z75923 ='raw';
 
} $m4is_z75923 =strtolower(trim($m4is_z75923 ));
  if($m4is_z75923 == 'parent' ){
return 'prnt-' . substr($m4is_v3458, 5 );
 
} elseif($m4is_z75923 == 'child' ){
return 'chld-' . substr($m4is_v3458, 5 );
 
} else{
return $m4is_v3458;
 
}
} public 
function m4is_m8657($data ){
return;
 $quiz['id']=$data['quiz']->ID;
 $quiz['name']=$data['quiz']->post_title;
 $quiz['score']=$data['score'];
 $quiz['count']=$data['count'];
 $quiz['pass']=$data['pass'];
 $quiz['points']=$data['points'];
 $quiz['percentage']=$data['percentage'];
 $quiz['time']=(int) $data['timespent'];
 $clientlist[0]=$this->m4is_r1546->m4is_z56();
 $email_template_id =get_post_meta($quiz['id'], 'parent_email_notification', 0 );
 $m4is_a790 =m4is_q82::m4is_d6758($this->m4is_r1546->m4is_x66(), 'keap', 'contact' );
  $full_name =($m4is_a790['firstname']?? '' ). ' ' . ($m4is_a790['lastname']?? '' );
 $mail['subject']='MMMastery Update: '. $full_name . '\'s Quiz Results for ' . $quiz['name'];
 $mail['to']=$m4is_a790['email']?? '';
 $mail['body']="\n";
 $mail['body'].= "Congratulations!\n";
 $mail['body']="\n";
 $mail['body'].= "You've Just Passed the Quiz for {$quiz['name']
} \n";
 $mail['body']="\n";
 $mail['body']="Your Percentage Correct:  {$quiz['percentage']
}\n";
 $mail['body']="Your Score:  {$quiz['score']
}\n";
 $mail['body']="Your Points:  {$quiz['points']
}\n";
 $mail['body']="Your Time:  {$quiz['time']
}\n";
 $mail['body']="\n";
 $mail['body']="Please keep in mind that this isn't a certification but a retention assessment. Keep up the good work!\n";
 $mail['body']="\n";
 $mail['body']="Thanks again,\n";
 $mail['body']="Micah Mitchell\n";
 $mail['body']="MMMastery.com\n";
  $mail['body']=do_shortcode($mail['body']);
 if(!empty($m4is_a790['!parent_id'])){
$clientlist[1]=$m4is_a790['!parent_id'];
 $mail['cc']=$m4is_a790['_ParentUsername']?? '';
 
}$result =m4is_r83::m4is_c26()->m4is_r1476()->sendEmail($clientlist, 'noreply@fierceconnection.com', '~Contact.Email~', $mail['cc'], '', 'Text', $mail['subject'], '', $mail['body']);
 
}  public 
function m4is_y86(int $m4is_f087 =0 ): int {
$m4is_f087 =$m4is_f087 ?: (int) self::$m4is_r1546->m4is_x66();
 $m4is_k824 =m4is_q82::m4is_d59($m4is_f087 );
 $m4is_r8695 =$this->m4is_d412();
 if(!array_intersect(explode(',', $m4is_k824['memb_user']['tags']), explode(',', $m4is_r8695 ))){
return 0;
  
}if(isset($m4is_k824['umbrella']['max_child_accounts'])){
return $m4is_k824['umbrella']['max_child_accounts'];
 
}if($m4is_o80491 =get_user_meta($m4is_f087, self::M4IS_Z25, true )){
return $m4is_o80491;
 
}$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 if($m4is_h21895 == 0 ){
return 0;
 
}$m4is_i935 =m4is_p40::m4is_p67($m4is_h21895, true, true );
 $m4is_r8695 =empty($m4is_i935['groups'])? []: explode(',', $m4is_i935['groups']);
 if(!$this->m4is_l274($m4is_r8695 )){
return 0;
 
}$m4is_h973 =isset($this->m4is_e0213['max_child_accounts'])? $this->m4is_e0213['max_child_accounts']: 0;
  $m4is_j860 =isset($this->m4is_e0213['tag_grants'])? $this->m4is_e0213['tag_grants']: [];
 $m4is_q523 =empty($this->m4is_e0213['child_count_add'])? '' : strtolower($this->m4is_e0213['child_count_add']);
 $m4is_e62501 =$this->m4is_e0213['ecommerce'];
  if(!empty($m4is_r637 )){
$m4is_v586 =isset($m4is_i935[$m4is_r637])? (int) $m4is_i935[$m4is_r637]: 0;
 $m4is_h973 =$m4is_h973 + $m4is_v586;
 
} if($m4is_j860 ){
foreach($m4is_j860 as $m4is_d913 =>$m4is_v586 ){
if(in_array($m4is_d913, $m4is_r8695 )){
$m4is_h973 =$m4is_h973 + $m4is_v586;
 
}
}
} if($m4is_e62501 ){
$m4is_d74 =$this->m4is_r1546->m4is_c686($m4is_h21895 );
 if(!empty($m4is_d74 )){
$m4is_i2065 =date('Ymd' ). 'T23:59:59';
 foreach($m4is_d74 as $m4is_c12 ){
$m4is_c12['EndDate']=isset($m4is_c12['EndDate'])? $m4is_c12['EndDate']: '';
 $m4is_c12['Active']=isset($m4is_c12['Active'])? $m4is_c12['Active']: '';
 if($m4is_c12['Status']== 'Active' ||$m4is_c12['EndDate']> $m4is_i2065 ){
$m4is_u6501 =$m4is_c12['SubscriptionPlanId'];
 $m4is_h973 =$m4is_h973 + (empty($this->m4is_e0213['subscriptions'][$m4is_u6501])? 0 : ($this->m4is_e0213['subscriptions'][$m4is_u6501]* $m4is_c12['Qty']));
 
}
}
}
}$m4is_h973 =apply_filters('memberium/groupaccount/childcount/override', $m4is_h973, $m4is_f087 );
 update_user_meta($m4is_f087, self::M4IS_Z25, $m4is_h973 );
 return $m4is_h973;
 
} public 
function m4is_y8645(int $m4is_f087, array $m4is_i935 ): int {
$m4is_i297 =0;
 if(empty($m4is_i935['id'])){
return $m4is_i297;
 
}$m4is_r8695 =explode(',', $this->m4is_d412());
 $m4is_p6457 =explode(',', $m4is_i935['groups']?? '' );
 if(!array_intersect($m4is_p6457, $m4is_r8695)){
return $m4is_i297;
 
} $m4is_i297 =(int) $this->m4is_e0213['max_child_accounts'];
   $m4is_i297 += $this->m4is_w3896($m4is_i935 );
 $m4is_i297 += $this->m4is_u20($m4is_p6457 );
 $m4is_i297 += $this->m4is_u2739($m4is_i935['id']);
 $m4is_i297 =apply_filters('memberium/groupaccount/childcount/override', $m4is_i297, $m4is_f087 );
 $m4is_u60 =get_user_meta($m4is_f087, self::M4IS_Z25, true );
 if($m4is_u60 !== $m4is_i297 ){
update_user_meta($m4is_f087, self::M4IS_Z25, $m4is_i297 );
 
}return $m4is_i297;
 
} private 
function m4is_u20(array $m4is_p6457 ): int {
$m4is_i297 =0;
 foreach($this->m4is_e0213['tag_grants']?? []as $m4is_d913 =>$m4is_w180 ){
if(in_array($m4is_d913, $m4is_p6457 )){
$m4is_i297 += $m4is_w180;
 
}
}return $m4is_i297;
 
} private 
function m4is_w3896(array $m4is_i935 ): int {
$m4is_w180 =0;
 $m4is_z89466 =strtolower($this->m4is_e0213['child_count_add']?? '' );
 if(empty($m4is_z89466 )||!array_key_exists($m4is_z89466, $m4is_i935 )){
return $m4is_w180;
 
}$m4is_w180 =(int) ($m4is_i935[$m4is_z89466]?? 0 );
 return $m4is_w180;
 
} private 
function m4is_u2739(int $m4is_h21895 ): int {
$m4is_i297 =0;
 if($m4is_h21895 < 1 ){
return $m4is_i297;
 
}$m4is_e62501 =$this->m4is_e0213['subscriptions']?? [];
 if(empty($m4is_e62501 )){
return $m4is_i297;
 
}$m4is_d74 =m4is_r83::m4is_c26()->m4is_c686($m4is_h21895 );
 if(empty($m4is_d74 )||!is_array($m4is_d74 )){
return $m4is_i297;
 
}$m4is_i2065 =date('Ymd' ). 'T23:59:59';
 $m4is_s719 ='2099-12-31T23:59:59';
 foreach($m4is_d74 as $m4is_c12 ){
$m4is_c31 =$m4is_c12['Status']?? 'Active';
 if($m4is_c31 !== 'Active' ){
continue;
 
}$m4is_y81309 =$m4is_c12['EndDate']?? $m4is_s719;
 if(!empty($m4is_y81309 )&&$m4is_y81309 < $m4is_i2065 ){
continue;
 
}$m4is_u6501 =$m4is_c12['SubscriptionPlanId'];
 $m4is_a692 =empty($m4is_e62501[$m4is_u6501])? 0 : $m4is_e62501[$m4is_u6501];
 $m4is_o3760 =$m4is_c12['Qty']?? 1;
 $m4is_i297 += $m4is_a692 * $m4is_o3760;
 
}return $m4is_i297;
 
} public 
function m4is_u107(int $m4is_f087, int $m4is_m617 =0 ): int {
global $wpdb;
 $m4is_v2613 ="SELECT count(*) FROM %i WHERE `parent_uid` = %d AND `active` = 1 AND `child_uid` > 0";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_u81::m4is_a6804(), $m4is_f087 );
 if($m4is_m617 ){
$m4is_v2613 .= " AND `child_uid` <> %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_m617 );
 
}$m4is_h973 =$wpdb->get_var($m4is_v2613 );
   return (int) $m4is_h973;
 
}public 
function m4is_t58904(int $m4is_r205, int $m4is_a62514 ): bool {
global $wpdb;
 $m4is_v2613 ="SELECT count(*) FROM %i WHERE `parent_uid` = %d and `child_uid` = %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_u81::m4is_a6804(), $m4is_r205, $m4is_a62514 );
 $is_child =$wpdb->get_col($m4is_v2613 );
 return (bool) $is_child;
 
} public 
function m4is_y9164(int $m4is_h850 ): array {
global $wpdb;
 $m4is_e80 =m4is_u81::m4is_a6804();
 $m4is_o80491 =$this->m4is_y86($m4is_h850 );
 $m4is_v2613 ="SELECT `child_uid` FROM %i WHERE `parent_uid` = %d AND `child_uid` > 0 AND `active` = 1 ORDER BY `id` ASC LIMIT %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_h850, $m4is_o80491 );
 $m4is_b6438 =$wpdb->get_col($m4is_v2613 );
 if(count($m4is_b6438 )<= $m4is_o80491 ){
return $m4is_b6438;
 
}if(count($m4is_b6438 )== $m4is_o80491 ){
$m4is_m950 =implode(',', $m4is_b6438 );
 $m4is_v2613 ="SELECT `child_uid` FROM %i WHERE `parent_uid` = %d AND `child_uid` NOT IN ( {$m4is_m950
} )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_h850 );
 $m4is_i47961 =$wpdb->get_col($m4is_v2613 );
 if(is_array($m4is_i47961 )){
foreach($m4is_i47961 as $m4is_i6302 ){
$this->m4is_y7324($m4is_i6302, $m4is_h850 );
 
}
}
}return $m4is_b6438;
 
} public 
function m4is_b201(int $m4is_a62514 ){
global $wpdb;
 $m4is_v2613 ="SELECT `parent_uid` FROM %i WHERE `child_uid` = %d ORDER BY `id` ASC LIMIT 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_u81::m4is_a6804(), $m4is_a62514 );
 $m4is_r205 =$wpdb->get_var($m4is_v2613 );
 return (int) $m4is_r205;
 
} public 
function m4is_b69781(int $m4is_x09186, int $m4is_h850 ): bool {
global $wpdb;
 $m4is_v6508 =$this->m4is_u107($m4is_h850, $m4is_x09186 );
 $m4is_r32 =m4is_q82::m4is_d6758($m4is_h850, 'umbrella', 'max_children', 0 );
 if($m4is_r32 <= $m4is_v6508 ){
return false;
 
}$m4is_m9654 =$this->m4is_y12895($m4is_h850 );
 m4is_u81::m4is_r43($m4is_x09186, $m4is_h850, $m4is_m9654 );
 update_user_meta($m4is_x09186, self::M4IS_K66479, 'relationship_table' );
 m4is_q82::m4is_q57064($m4is_x09186 );
  m4is_r82473::m4is_k6873();
 return true;
 
} private 
function m4is_y12895($m4is_h850 ): bool {
$m4is_m9654 =!empty(m4is_q82::m4is_d6758($m4is_h850, 'memb_user', 'membership_tags', '' ));
 return $m4is_m9654;
 
} public 
function m4is_m87(int $m4is_f087 ): void {
global $wpdb;
 m4is_l870::m4is_f196($m4is_f087 );
 $m4is_e80 =m4is_u81::m4is_a6804();
 $m4is_v2613 ="DELETE FROM %i WHERE parent_uid = %d OR `child_uid` > %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_f087, $m4is_f087 );
 $wpdb->query($m4is_v2613 );
 
} public 
function m4is_y7324(int $m4is_x09186, int $m4is_h850 ): bool {
global $wpdb;
 m4is_u81::m4is_r43($m4is_x09186, $m4is_h850, false );
 m4is_q82::m4is_q57064($m4is_x09186 );
 m4is_r82473::m4is_k6873();
 m4is_l870::m4is_f196($m4is_x09186 );
 return true;
 
}        
}

