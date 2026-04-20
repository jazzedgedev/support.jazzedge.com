<?php

/**
 * Copyright (c) 2018-2022 David J Bullock
 * Web Power and Light
*/


 class_exists('m4is_r83' )||die();
  final 
class m4is_d02 {
public $slug ='elementor';
  public $container_els =['section', 'column'];
  public $container_visibility =[];
  public $widget_visibility =[];
  public $ns ='';
  private $m4is_r1546;
 public static 
function m4is_c26(): self {
static $m4is_b30146;
 if(is_null($m4is_b30146 )){
$m4is_b30146 =new self;
 $m4is_b30146->ns =m4is_v679::NS;
 $m4is_b30146->m4is_h269();
 
}return $m4is_b30146;
 
}private 
function __construct(){
$this->m4is_i702();
 
}private 
function m4is_i702(): void {
$this->slug ='elementor';
 $this->container_els =['section', 'column'];
 $this->container_visibility =[];
 $this->widget_visibility =[];
 $this->ns =m4is_v679::NS;
 $this->m4is_r1546 =m4is_r83::m4is_c26();
 
}public 
function m4is_h269(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->container_els =apply_filters('memberium/elementor/editor/container_slugs', $this->container_els );
  $this->m4is_d4861();
 
}private 
function m4is_d4861(): void {
add_action('elementor/widgets/widgets_registered', [$this, 'm4is_y26138'], 20 );
  add_action('elementor/frontend/before_render', [$this, 'm4is_e54061'], 10, 1 );
  add_action('elementor/frontend/after_render', [$this, 'm4is_t10954'], 10, 1 );
  add_filter('elementor/frontend/section/should_render', [$this, 'm4is_h805'], 1, 2 );
  add_filter('elementor/frontend/column/should_render', [$this, 'm4is_h805'], 1, 2 );
 add_filter('elementor/frontend/widget/should_render', [$this, 'm4is_h805'], 1, 2 );
 add_filter('elementor/frontend/container/should_render', [$this, 'm4is_r972'], 10, 2 );
 add_filter('elementor/frontend/column/should_render', [$this, 'm4is_r972'], 10, 2 );
 
} public 
function m4is_r972($m4is_u3807, $m4is_i3410 ): bool {
if(!$m4is_u3807 ){
return $m4is_u3807;
 
}if(empty($m4is_i3410 )||\Elementor\Plugin::$instance->editor->is_edit_mode()){
return $m4is_u3807;
 
}if(current_user_can('manage_options')){
return $m4is_u3807;
 
}$m4is_e0213 =$m4is_i3410->get_settings_for_display();
 $m4is_q30624 =explode(',', m4is_q82::m4is_d6758($this->m4is_r1546->m4is_x66(), 'memb_user', 'membership_tags', '' ));
    $m4is_q1470 =isset($m4is_e0213['memberium_login_status'])? $m4is_e0213['memberium_login_status']: '';
 if($m4is_q1470 <> '' &&$m4is_q1470 <> 'both' ){
if($m4is_q1470 == 'loggedin' &&!is_user_logged_in()){
return false;
 
}if($m4is_q1470 == 'anonymous' &&is_user_logged_in()){
return false;
 
}
}$m4is_q1470 =isset($m4is_e0213['memberium_any_membership'])? $m4is_e0213['memberium_any_membership']: '';
 if($m4is_q1470 == 'yes' ){
if(empty($m4is_q30624 )){
return false;
 
}
}else{
$m4is_r37596 =isset($m4is_e0213['memberium_memberships_section'])? $m4is_e0213['memberium_memberships_section']: [];
 if(!empty($m4is_r37596 )){
if(!array_intersect($m4is_r37596, $m4is_q30624 )){
return false;
 
}
}
}$m4is_q1470 =isset($m4is_e0213['memberium_tags1_section'])? implode(',', $m4is_e0213['memberium_tags1_section']): '';
 if(!empty($m4is_q1470 )){
if(!$this->m4is_r1546->m4is_m2480($m4is_q1470 )){
return false;
 
}
}$m4is_q1470 =isset($m4is_e0213['memberium_tags2_section'])? implode(',', $m4is_e0213['memberium_tags2_section']): '';
 if(!empty($m4is_q1470 )){
if(!$this->m4is_r1546->m4is_m2480($m4is_q1470 )){
return false;
 
}
}$m4is_q1470 =isset($m4is_e0213['memberium_all_tags_section'])? $m4is_e0213['memberium_all_tags_section']: [];
 if(!empty($m4is_q1470 )){
if(!$this->m4is_r1546->m4is_x13466($m4is_q1470 )){
return false;
 
}
}return $m4is_u3807;
 
} 
function m4is_y26138($m4is_m78126 ){
require_once __DIR__ . '/widget-shortcode.php';
 $m4is_m78126->unregister_widget_type('shortcode' );
 $m4is_m78126->register_widget_type(new m4is_s45 );
 
} 
function m4is_e54061($m4is_k6892){
 if(\Elementor\Plugin::$instance->editor->is_edit_mode()||empty($m4is_k6892)){
return;
 
}$m4is_p705 =$m4is_k6892->get_type();
 $m4is_b87 =$m4is_k6892->get_id();
 $m4is_e0213 =$m4is_k6892->get_settings_for_display();
 $m4is_a32861 =$this->m4is_d273($m4is_e0213);
 $m4is_f725 =in_array($m4is_p705, $this->container_els);
  if($m4is_f725 ){
 if(!$m4is_a32861){
$this->container_visibility[$m4is_b87]=$this->m4is_o08763($m4is_k6892);
 
}
} else{
$m4is_u37 =$m4is_k6892->get_name();
  if($m4is_a32861 ){
 $m4is_a32861 =$this->m4is_h14($m4is_b87, $m4is_a32861 );
  if(!$m4is_a32861 ){
$this->widget_visibility[]=$m4is_b87;
 
}
} else{
$this->widget_visibility[]=$m4is_b87;
 
} if(!$m4is_a32861 ){
if($m4is_u37 === 'text-editor'){
add_filter('widget_text', [$this, 'm4is_d5682'], 1, 2 );
 
}if($m4is_u37 === 'shortcode'){
add_filter("{$this->ns
}/{$this->slug
}/widget/shortcode/render", [$this, 'm4is_d5682'], 1, 2 );
 
}
}
}
} 
function m4is_t10954($m4is_k6892){
 if(\Elementor\Plugin::$instance->editor->is_edit_mode()||empty($m4is_k6892 )){
return;
 
}$m4is_p705 =$m4is_k6892->get_type();
 $m4is_b87 =$m4is_k6892->get_id();
 $m4is_f725 =in_array($m4is_p705, $this->container_els);
  if($m4is_f725){

} else{
$m4is_u37 =$m4is_k6892->get_name();
  if(in_array($m4is_b87, $this->widget_visibility )){
if($m4is_u37 === 'text-editor' ){
remove_filter('widget_text', [$this, 'm4is_d5682'], 1 );
 
}if($m4is_u37 === 'shortcode' ){
remove_filter("{$this->ns
}/{$this->slug
}/widget/shortcode/render", [$this, 'm4is_d5682'], 1, 2 );
 
}
}
}
} 
function m4is_o08763($m4is_k6892){
$m4is_z731 =$m4is_k6892->get_data('elements');
 $m4is_j083 =[];
 if(is_array($m4is_z731 )&&!empty($m4is_z731 )){
foreach ($m4is_z731 as $key =>$m4is_r7519){
$m4is_j083 =$this->m4is_y602($m4is_j083, $m4is_r7519 );
 $m4is_q06462 =(isset($m4is_r7519['elements'])&&!empty($m4is_r7519['elements']))? $m4is_r7519['elements']: false;
 if($m4is_q06462 ){
foreach ($m4is_q06462 as $m4is_k6892 =>$m4is_i3410){
$m4is_j083 =$this->m4is_y602($m4is_j083, $m4is_i3410 );
 $m4is_l856 =isset($m4is_i3410['elements'])? $m4is_i3410['elements']: false;
 if($m4is_l856 ){
foreach ($m4is_l856 as $m4is_u6860 =>$m4is_v586){
$m4is_j083 =$this->m4is_y602($m4is_j083, $m4is_v586 );
 
}
}
}
}
}
}return $m4is_j083;
 
} 
function m4is_y602($m4is_j083, $m4is_l91805){
$m4is_j0361 =!empty($m4is_l91805['elType'])? $m4is_l91805['elType']: false;
 $m4is_d07693 =!empty($m4is_l91805['id'])? $m4is_l91805['id']: false;
 if($m4is_j0361 === 'widget' &&$m4is_d07693){
$m4is_j083[]=$m4is_d07693;
 
}return $m4is_j083;
 
} 
function m4is_h14($m4is_b87, $m4is_a32861){
 $m4is_o27 =false;
 if(!empty($this->container_visibility)){
foreach ($this->container_visibility as $m4is_u2616 =>$m4is_p65){
if(is_array($m4is_p65 )){
foreach ($m4is_p65 as $m4is_d07693){
if($m4is_b87 === $m4is_d07693 ){
$m4is_a32861 =false;
 
}
}
}
}
}return $m4is_a32861;
 
} 
function m4is_h805($m4is_a62319, $m4is_k6892){
 if(\Elementor\Plugin::$instance->editor->is_edit_mode()||empty($m4is_k6892 )){
return $m4is_a62319;
 
}$m4is_p705 =$m4is_k6892->get_type();
 $m4is_b87 =$m4is_k6892->get_id();
 $m4is_f725 =in_array($m4is_p705, $this->container_els );
  if($m4is_f725 ){
if(array_key_exists($m4is_b87, $this->container_visibility )){
if($m4is_p705 === 'section' ){
$m4is_a62319 =false;
 
}
}
} else{
if(in_array($m4is_b87, $this->widget_visibility)){
$m4is_a62319 =false;
 
}
}return $m4is_a62319;
 
} 
function m4is_d5682($m4is_t09761, $m4is_e0213 ){
return ' ';
 
} private 
function m4is_d273(array $m4is_j296 =[]): bool {
$m4is_m96240 =[];
  $m4is_q62 =m4is_v679::m4is_c26();
 $m4is_j86631 =$m4is_q62::PREFIX;
 foreach($m4is_j296 as $m4is_k52736 =>$m4is_v586 ){
if(strpos($m4is_k52736, "{$m4is_j86631
}_membership_levels" )!== false &&$m4is_v586 === '1' ){
$m4is_m96240[]=(int) str_replace("{$m4is_j86631
}_membership_levels-", '', $m4is_k52736);
 
}
}$m4is_g5846 =isset($m4is_j296["{$m4is_j86631
}_access_tags"])? $m4is_j296["{$m4is_j86631
}_access_tags"]: '';
 $m4is_g5846 =!empty($m4is_g5846)&&is_array($m4is_g5846)? implode(',', $m4is_g5846): $m4is_g5846;
 $m4is_x91 =isset($m4is_j296["{$m4is_j86631
}_access_tags2"])? $m4is_j296["{$m4is_j86631
}_access_tags2"]: '';
 $m4is_x91 =!empty($m4is_x91)&&is_array($m4is_x91)? implode(',', $m4is_x91): $m4is_x91;
 $m4is_u897 =['memberships' =>implode(',', $m4is_m96240), 'any_membership' =>isset($m4is_j296["{$m4is_j86631
}_anymembership"])&&$m4is_j296["{$m4is_j86631
}_anymembership"]=== '1' ? 1 : 0, 'logged_in_only' =>isset($m4is_j296["{$m4is_j86631
}_loggedin"])&&$m4is_j296["{$m4is_j86631
}_loggedin"]=== '1' ? 1 : 0, 'logged_out_only' =>isset($m4is_j296["{$m4is_j86631
}_anonymous_only"])&&$m4is_j296["{$m4is_j86631
}_anonymous_only"]=== '1' ? 1 : 0, 'invert_results' =>isset($m4is_j296["{$m4is_j86631
}_invert_results"])&&$m4is_j296["{$m4is_j86631
}_invert_results"]=== '1' ? 1 : 0, 'contact_ids' =>isset($m4is_j296["{$m4is_j86631
}_contact_ids"])? sanitize_text_field($m4is_j296["{$m4is_j86631
}_contact_ids"]): '', 'eval' =>isset($m4is_j296["{$m4is_j86631
}_eval"])? trim($m4is_j296["{$m4is_j86631
}_eval"]): '', 'asset_id' =>isset($m4is_j296["{$m4is_j86631
}_asset_id"])? sanitize_text_field($m4is_j296["{$m4is_j86631
}_asset_id"]): '', 'tags1' =>!empty($m4is_g5846)? trim($m4is_g5846, ','): '', 'tags2' =>!empty($m4is_x91)? trim($m4is_x91, ','): '' ];
 return $m4is_q62->m4is_b679()->m4is_h86($m4is_u897, 'elementor' );
 
}
}

