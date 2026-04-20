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
class m4is_w5132 {
private $m4is_j083 =[];
  private $m4is_c87061 =[];
  private $m4is_k260 =[];
  private $m4is_g079 =[];
  private $m4is_h609 =['any_membership', 'contact_ids', 'eval', 'invert_results', 'logged_in_only', 'logged_out_only', 'memberships', 'tags1', 'tags2' ];
 private $m4is_o04 =['any_membership' =>0, 'asset_id' =>'', 'contact_ids' =>'', 'eval' =>'', 'invert_results' =>0, 'logged_in_only' =>0, 'logged_out_only' =>0, 'memberships' =>'', 'tags1' =>'', 'tags2' =>'' ];
 static 
function m4is_c26(){
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){

} public 
function m4is_h86(array $m4is_j296, string $m4is_j0361 ): bool {
 static $m4is_p6356 =[];
 $m4is_y642 =['any_membership' =>0, 'asset_id' =>'', 'contact_ids' =>'', 'eval' =>'', 'invert_results' =>0, 'logged_in_only' =>0, 'logged_out_only' =>0, 'memberships' =>'', 'tags1' =>'', 'tags2' =>'' ];
 $m4is_e05491 =wp_parse_args($m4is_j296, $m4is_y642 );
 if(!empty($m4is_e05491['contact_ids'])){
$m4is_e05491['contact_ids']=$this->m4is_c7066((string) $m4is_e05491['contact_ids']);
 
}if(!empty($m4is_e05491['asset_id'])){
$m4is_e05491['asset_id']=$this->m4is_c7066((string) $m4is_e05491['asset_id']);
 
} $m4is_q1046 =md5(serialize($m4is_e05491 ));
 if(!array_key_exists($m4is_q1046, $m4is_p6356 )){
$m4is_p6356[$m4is_q1046]=m4is_f58::m4is_c26()->m4is_w0842(true, $m4is_e05491, $m4is_j0361, $m4is_e05491['asset_id']);
 
}return $m4is_p6356[$m4is_q1046];
 
}
function m4is_c7066(string $m4is_p65 ): string {
return trim(str_replace([" ", "-", "\n", "\r", "\t"], '', $m4is_p65 ), ',' );
 
}    
function m4is_w56($m4is_b40835, $m4is_g3678 ){
if(empty($m4is_b40835 )){
return $m4is_b40835;
  
}$m4is_j296 =$m4is_g3678['attrs'];
  if(empty($m4is_j296 )){
 if(empty($m4is_g3678['blockName'])){
 $m4is_o9361 =$this->m4is_q0782($m4is_b40835 );
 if($m4is_o9361){
foreach ($m4is_o9361 as $m4is_c39){
$m4is_b40835 =str_replace($m4is_c39, '', $m4is_b40835);
 
}return $m4is_b40835;
 
} else{
return $m4is_b40835;
 
}
} else{
return $m4is_b40835;
 
}
} else{
$m4is_f80654 =$this->m4is_a948($m4is_j296 );
 return $m4is_f80654 ? $m4is_b40835 : '';
 
}
} 
function m4is_q0782($m4is_b40835 ){
$m4is_o9361 =[];
 preg_match_all('/' . get_shortcode_regex(). '/', $m4is_b40835, $m4is_a157, PREG_SET_ORDER );
 foreach ($m4is_a157 as $m4is_c39 ){
$m4is_j296 =empty($m4is_c39[3])? false : shortcode_parse_atts($m4is_c39[3]);
 if($m4is_j296 ){
 $m4is_z2649 =$this->m4is_a948($m4is_j296 );
  if(!$m4is_z2649){
$m4is_o9361[]=$m4is_c39[0];
 
}
}
}return empty($m4is_o9361 )? false : $m4is_o9361;
 
} 
function m4is_a948(array $m4is_j296 ): bool {
$m4is_j86631 =m4is_v679::PREFIX;
 $m4is_u897 =['memberships' =>empty($m4is_j296["{$m4is_j86631
}_memberships"])? '' : $m4is_j296["{$m4is_j86631
}_memberships"], 'any_membership' =>isset($m4is_j296["{$m4is_j86631
}_anymembership"])&&$m4is_j296["{$m4is_j86631
}_anymembership"]=== 'on' ? 1 : 0, 'logged_in_only' =>isset($m4is_j296["{$m4is_j86631
}_loggedin"])&&$m4is_j296["{$m4is_j86631
}_loggedin"]=== 'on' ? 1 : 0, 'logged_out_only' =>isset($m4is_j296["{$m4is_j86631
}_anonymous_only"])&&$m4is_j296["{$m4is_j86631
}_anonymous_only"]=== 'on' ? 1 : 0, 'invert_results' =>isset($m4is_j296["{$m4is_j86631
}_invert_results"])&&$m4is_j296["{$m4is_j86631
}_invert_results"]=== 'on' ? 1 : 0, 'contact_ids' =>empty($m4is_j296["{$m4is_j86631
}_contact_ids"])? '' : sanitize_text_field($m4is_j296["{$m4is_j86631
}_contact_ids"]), 'tags1' =>empty($m4is_j296["{$m4is_j86631
}_access_tags"])? '' : $m4is_j296["{$m4is_j86631
}_access_tags"], 'tags2' =>empty($m4is_j296["{$m4is_j86631
}_access_tags2"])? '' : $m4is_j296["{$m4is_j86631
}_access_tags2"], 'eval' =>empty($m4is_j296["{$m4is_j86631
}_eval"])? '' : trim($m4is_j296["{$m4is_j86631
}_eval"]), 'asset_id' =>empty($m4is_j296["{$m4is_j86631
}_asset_id"])? '' : sanitize_text_field($m4is_j296["{$m4is_j86631
}_asset_id"]), ];
 return $this->m4is_h86($m4is_u897, 'gutenberg' );
 
}    
function m4is_v671($m4is_w89066, $m4is_t8566, $m4is_y66291 ){
$m4is_v15639 =[];
 $m4is_x160 =is_object($m4is_t8566 )&&isset($m4is_t8566->slug )? $m4is_t8566->slug : 'default';
 if(is_array($m4is_w89066 )&&!empty($m4is_w89066 )){
foreach ($m4is_w89066 as $m4is_b3785 =>$m4is_c4069 ){
if($this->m4is_c91502($m4is_c4069, $m4is_w89066, $m4is_x160)){
$m4is_v15639[]=$m4is_c4069;
 
}
}$m4is_w89066 =$m4is_v15639;
 
}return $m4is_w89066;
 
} 
function m4is_c91502($m4is_c4069, $m4is_w89066, $m4is_x160 ){
$m4is_x160 =empty($m4is_x160 )? 'default' : $m4is_x160;
 $m4is_d07693 =$m4is_c4069->ID;
 $m4is_i341 =$m4is_c4069->menu_item_parent;
 if(!isset($this->m4is_c87061[$m4is_x160])){
$this->m4is_c87061[$m4is_x160]=[];
 
}$m4is_c87061 =$this->m4is_c87061[$m4is_x160];
  if(isset($m4is_c87061[$m4is_d07693])){
return $m4is_c87061[$m4is_d07693];
 
} $m4is_r1692 =m4is_v679::m4is_c26()->m4is_v0631($m4is_d07693 );
  if(empty($m4is_r1692)){
$m4is_c87061 =$this->m4is_t743($m4is_c4069, $m4is_w89066, $m4is_x160);
 return $m4is_c87061[$m4is_d07693];
 
}$m4is_t72 =apply_filters("wpal/menu/{$m4is_x160
}/item/visibility", $m4is_r1692, $m4is_d07693);
 $m4is_e05491 =wp_parse_args($m4is_t72, $this->m4is_o04 );
 if(m4is_j586::m4is_f6018()){
$m4is_u450 =$this->m4is_h609;
 foreach($this->m4is_h609 as $m4is_l9671){
if(!empty($m4is_e05491[$m4is_l9671])){
m4is_j586::m4is_x7134();
 break;
 
}
}
}$m4is_a32861 =$this->m4is_h86($m4is_e05491, $m4is_x160 );
 if($m4is_a32861 ){
 $m4is_c87061 =$this->m4is_t743($m4is_c4069, $m4is_w89066, $m4is_x160);
 $m4is_a32861 =$m4is_c87061[$m4is_d07693];
 
}return $m4is_a32861;
 
} 
function m4is_t743($m4is_c4069, $m4is_w89066, $m4is_x160 ){
 $m4is_i341 =$m4is_c4069->menu_item_parent;
 $m4is_c87061 =$this->m4is_c87061[$m4is_x160];
 $m4is_k09 =$m4is_c4069->ID;
 $m4is_c87061[$m4is_k09]=true;
 if($m4is_i341 > 0 ){
 if(!isset($m4is_c87061[$m4is_i341])){
$m4is_s092 =$this->m4is_l63($m4is_c4069, $m4is_w89066);
 $m4is_c87061[$m4is_i341]=$this->m4is_c91502($m4is_s092, $m4is_w89066, $m4is_x160);
 
}$m4is_c87061[$m4is_k09]=$m4is_c87061[$m4is_i341];
 
} $this->m4is_c87061[$m4is_x160]=$m4is_c87061;
 return $m4is_c87061;
 
} 
function m4is_l63($m4is_c4069, $m4is_w89066){
$m4is_s092 =false;
 $m4is_i341 =$m4is_c4069->menu_item_parent;
 if($m4is_i341 > 0){
$m4is_c38921 =array_search($m4is_i341, array_column($m4is_w89066, 'ID'));
 if($m4is_c38921 !== false ){
$m4is_s092 =$m4is_w89066[$m4is_c38921];
 
}
}return $m4is_s092;
 
}   
function m4is_y621($m4is_t961){
if(is_customize_preview()){
return $m4is_t961;
 
}global $wp_registered_widgets;
 if(empty($wp_registered_widgets)){
return $m4is_t961;
 
}foreach($m4is_t961 as $m4is_m85026 =>$m4is_j083){
if($m4is_m85026 == 'wp_inactive_widgets' ||empty($m4is_j083)){
continue;
 
}foreach($m4is_j083 as $m4is_b3785 =>$m4is_d07693){
$m4is_a32861 =$this->m4is_w120($m4is_d07693, $m4is_m85026);
 if(is_null($m4is_a32861)){
$m4is_j56697 =isset($wp_registered_widgets[$m4is_d07693]['callback'][0]->option_name)? $wp_registered_widgets[$m4is_d07693]['callback'][0]->option_name : '';
 $m4is_l9671 =isset($wp_registered_widgets[$m4is_d07693]['params'][0]['number'])? $wp_registered_widgets[$m4is_d07693]['params'][0]['number']: '';
 $m4is_v31486 =get_option($m4is_j56697);
 $m4is_v31486 =is_array($m4is_v31486)&&isset($m4is_v31486[$m4is_l9671])? $m4is_v31486[$m4is_l9671]: null;
 if(is_array($m4is_v31486)&&!empty($m4is_v31486['content'])){
$m4is_g3678 =parse_blocks($m4is_v31486['content']);
 $m4is_j296 =isset($m4is_g3678[0])? $m4is_g3678[0]['attrs']: [];
 $m4is_a32861 =$this->m4is_o67412($m4is_d07693, $m4is_j296, $m4is_m85026);
 
}else{
$m4is_a32861 =$this->m4is_a63($m4is_d07693, $m4is_m85026);
 
}
}$this->m4is_j083[$m4is_m85026][$m4is_d07693]=$m4is_a32861;
 if(!$m4is_a32861 ){
unset($m4is_t961[$m4is_m85026][$m4is_b3785]);
 
}
}
}return $m4is_t961;
 
} 
function m4is_o635($m4is_b30146, $m4is_m348, $m4is_y66291){
$m4is_x435 =empty($m4is_y66291['id'])? 'custom' : $m4is_y66291['id'];
 $m4is_a32861 =$this->m4is_w120($m4is_m348->id, $m4is_x435);
 if(is_null($m4is_a32861)){
 if(is_a($m4is_m348, 'WP_Widget_Block')){
$m4is_g3678 =empty($m4is_b30146['content'])? []: parse_blocks($m4is_b30146['content']);
 $m4is_j296 =isset($m4is_g3678[0])? $m4is_g3678[0]['attrs']: [];
 $m4is_a32861 =$this->m4is_o67412($m4is_m348->id, $m4is_j296, $m4is_x435);
 
}else{
$m4is_a32861 =$this->m4is_a63($m4is_m348->id, $m4is_x435);
 
}
}return $m4is_a32861 ? $m4is_b30146 : false;
 
}
function m4is_o67412($m4is_d07693, $m4is_j296, $m4is_x435 ){
$m4is_j296 =empty($m4is_j296)? $m4is_j296 : $this->m4is_a948($m4is_j296);
 $this->m4is_j083[$m4is_x435][$m4is_d07693]=$this->m4is_h68056($m4is_d07693, $m4is_j296, $m4is_x435 );
 return $this->m4is_j083[$m4is_x435][$m4is_d07693];
 
} 
function m4is_a63($m4is_d07693, $m4is_x435){
$m4is_a32861 =$this->m4is_w120($m4is_d07693, $m4is_x435);
 if(is_null($m4is_a32861)){
if(preg_match('/^(.+)-(\d+)$/', $m4is_d07693, $m4is_p125)){
$m4is_w43796 =$m4is_p125[1];
 $m4is_b3785 =$m4is_p125[2];
 $m4is_e0213 =get_option("widget_{$m4is_w43796
}");
 $m4is_e0213 =empty($m4is_e0213[$m4is_b3785])? []: $m4is_e0213[$m4is_b3785];
 $m4is_a32861 =$this->m4is_h68056($m4is_d07693, $m4is_e0213, $m4is_x435 );
 $this->m4is_j083[$m4is_x435][$m4is_d07693]=$m4is_a32861;
 
}else{
$this->m4is_j083[$m4is_x435][$m4is_d07693]=true;
 
}
}return $this->m4is_j083[$m4is_x435][$m4is_d07693];
 
}
function m4is_w120($m4is_d07693, $m4is_x435 ){
if(!isset($this->m4is_j083[$m4is_x435])){
$this->m4is_j083[$m4is_x435]=[];
 
}return isset($this->m4is_j083[$m4is_x435][$m4is_d07693])? $this->m4is_j083[$m4is_x435][$m4is_d07693]: null;
 
}
function m4is_h68056($m4is_d07693, $m4is_e0213, $m4is_x435 ){
$m4is_v15639 =apply_filters("memberium/widget/visibility", $m4is_e0213, $m4is_d07693, $m4is_x435);
 $m4is_e05491 =wp_parse_args($m4is_v15639, $this->m4is_o04 );
 if(m4is_j586::m4is_f6018()){
foreach ($this->m4is_h609 as $m4is_l9671){
if(!empty($m4is_e05491[$m4is_l9671])){
m4is_j586::m4is_x7134();
 break;
 
}
}
}return $this->m4is_h86($m4is_e05491, 'widget-area' );
 
}    
function m4is_h162($m4is_v76912){
if(!is_admin()&&$m4is_v76912->is_main_query()){
 if(is_archive()){
$m4is_l87469 =m4is_v679::m4is_c26()->m4is_t68274();
 $m4is_e31 =get_queried_object();
 $m4is_f015 =isset($m4is_e31->taxonomy)? $m4is_e31->taxonomy : false;
 if($m4is_f015 &&in_array($m4is_f015, $m4is_l87469)){
$this->m4is_p92($m4is_v76912);
 
}
}
}
} 
function m4is_o17590($m4is_g902, $m4is_f015, $m4is_a2618, $m4is_n2568){
foreach($m4is_g902 as $m4is_d07693 =>$m4is_w90){
if(is_a($m4is_w90, 'WP_Term')&&$m4is_w90->taxonomy == 'category' ){
if(!$this->m4is_w09($m4is_w90->term_id, $m4is_w90->taxonomy )){
unset($m4is_g902[$m4is_d07693]);
 
}
}
}return $m4is_g902;
 
}
function m4is_p92($m4is_v76912 ){
$m4is_v1872 =isset($m4is_v76912->queried_object)? $m4is_v76912->queried_object : false;
 if(!$m4is_v1872 ){
return;
 
} $m4is_f015 =$m4is_v1872->taxonomy;
 $m4is_r538 =$m4is_v1872->term_taxonomy_id;
 $m4is_a32861 =$this->m4is_w09($m4is_r538, $m4is_f015);
  if($m4is_a32861 ){
$m4is_e0165 =$this->m4is_t2348($m4is_r538, $m4is_f015);
 if((int) $m4is_e0165 > 0 ){
$m4is_a32861 =false;
 
}
} if(!$m4is_a32861 ){
$m4is_p1084 ='hide';
 $m4is_g079 =$this->m4is_g079[$m4is_r538];
 $m4is_p1084 =isset($m4is_g079['prohibited_action'])? (int)$m4is_g079['prohibited_action']: 0;
  if($m4is_p1084 === 1 ){
$m4is_q37596 =isset($m4is_g079['redirect_url'])? $m4is_g079['redirect_url']: false;
 if($m4is_q37596 ){
wp_safe_redirect($m4is_q37596 );
 exit;
 
}else{
$m4is_p1084 =0;
 
}
} if($m4is_p1084 === 0 ){
if(method_exists($m4is_v76912, 'set_404')){
$m4is_v76912->set_404();
 
}
}
}
} 
function m4is_w09($m4is_r538, $m4is_f015 ): bool {
 if(isset($this->m4is_k260[$m4is_r538])){
return $this->m4is_k260[$m4is_r538];
 
} $m4is_r1692 =m4is_v679::m4is_c26()->m4is_r89563($m4is_r538);
  if(empty($m4is_r1692)){
$this->m4is_g079[$m4is_r538]=[];
 return true;
 
}$this->m4is_g079[$m4is_r538]=$m4is_r1692;
 $m4is_t72 =apply_filters('wpal/taxonomy/access/visibility/settings', $m4is_r1692, $m4is_r538);
 $m4is_e05491 =wp_parse_args($m4is_t72, $this->m4is_o04);
 if(m4is_j586::m4is_f6018()){
$m4is_u450 =$this->m4is_h609;
 foreach($this->m4is_h609 as $m4is_l9671){
if(!empty($m4is_e05491[$m4is_l9671])){
m4is_j586::m4is_x7134();
 break;
 
}
}
}return $this->m4is_h86($m4is_e05491, $m4is_f015 );
 
} 
function m4is_t2348($m4is_r538, $m4is_f015 ){
$m4is_j9678 =get_ancestors($m4is_r538, $m4is_f015, 'taxonomy');
 if(is_array($m4is_j9678)&&!empty($m4is_j9678)){
foreach ($m4is_j9678 as $m4is_e0165){
 if(!$this->m4is_w09($m4is_e0165, $m4is_f015)){
return $m4is_e0165;
 
}
}
}return false;
 
}
}

