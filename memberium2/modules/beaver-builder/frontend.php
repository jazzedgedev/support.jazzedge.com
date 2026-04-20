<?php

/**
 * Proprietary Software - All Rights Reserved
 *
 * This file is part of the Memberium plugin, which is proprietary software developed by Web Power and Light.
 * Unauthorized copying, distribution, or modification of this file, via any medium, is strictly prohibited.
 *
 * Copyright (c) 2012-2025 David J Bullock
 * Web Power and Light
 *
 * For licensing information, please contact Web Power and Light.
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_l56149 {
public $slug ='beaver_builder';
  public $container_els =['section', 'column'];
  public $module_visibility =[];
  public $container_visibility =[];
  public $widget_visibility =[];
   public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_h269();
 
} private 
function m4is_h269(): void {
 add_action('fl_builder_before_render_row', [$this, 'm4is_a606'], 10, 2 );
 add_action('fl_builder_after_render_row', [$this, 'm4is_x07586'], 10, 2 );
  add_action('fl_builder_before_render_column_group', [$this, 'm4is_z04629'], 10, 2 );
 add_action('fl_builder_after_render_column_group', [$this, 'm4is_z93'], 10, 2 );
  add_action('fl_builder_before_render_module', [$this, 'm4is_d64926'], 10, 1 );
 add_action('fl_builder_after_render_module', [$this, 'm4is_n7260'], 10, 1 );
 
} public 
function m4is_a606(object $m4is_g91703 ): void {
if(!$this->m4is_l2075($m4is_g91703->settings )){
$this->container_visibility[$m4is_g91703->node ]=$m4is_g91703->node;
 add_filter('fl_builder_template_path', [$this, 'm4is_y6451'], 10, 3 );
 
}
} public 
function m4is_x07586(object $m4is_g91703 ): void {
if(isset($this->container_visibility[$m4is_g91703->node ])){
remove_filter('fl_builder_template_path', [$this, 'm4is_y6451'], 10 );
 unset($this->container_visibility[$m4is_g91703->node ]);
 
}
} public 
function m4is_y6451(): bool {
return false;
 
} public 
function m4is_z6273(): bool {
return false;
 
} public 
function m4is_z04629(object $m4is_d10, array $m4is_v35 ){
$m4is_n189 =true;
 $m4is_z0294 =[];
 if($m4is_n189 ){
if(is_array($m4is_v35 )){
$m4is_h72 =count($m4is_v35);
 foreach ($m4is_v35 as $m4is_r243 =>$m4is_a37){
if($m4is_a37->type === 'column' &&$m4is_a37->settings > '' ){
if(!$this->m4is_l2075($m4is_a37->settings )){
$m4is_h72 --;
 if(!isset($m4is_z0294[$m4is_d10->node ])){
$m4is_z0294[$m4is_d10->node ]=[];
 
}$m4is_z0294[$m4is_d10->node][$m4is_a37->node]=$m4is_a37->node;
 
}
}
} if($m4is_h72 > 0 ){
if(!empty($m4is_z0294)){
$this->container_visibility[$m4is_d10->parent]=$m4is_z0294;
 
}
} else{
if(!isset($this->container_visibility[$m4is_d10->parent])){
$this->container_visibility[$m4is_d10->parent ]=[];
 
}$this->container_visibility[$m4is_d10->parent ][$m4is_d10->node ]=true;
 add_filter('fl_builder_template_path', [$this, 'm4is_z6273'], 2 );
 
}
}
}
} public 
function m4is_z93(object $m4is_d10, array $m4is_v35 ){
$m4is_s092 =$m4is_d10->parent;
 $m4is_a43256 =$m4is_d10->node;
  if(isset($this->container_visibility[$m4is_s092])){
 if(isset($this->container_visibility[$m4is_s092 ][$m4is_a43256 ])){
 if(is_array($this->container_visibility[$m4is_s092 ][$m4is_a43256 ])){
 
}else{
 remove_filter('fl_builder_template_path', [$this, 'm4is_z6273'], 10 );
 unset($this->container_visibility[$m4is_s092 ][$m4is_a43256 ]);
 
}
} if(empty($this->container_visibility[$m4is_s092 ])){
unset($this->container_visibility[$m4is_s092 ]);
 
}
}
} public 
function m4is_d64926(object $m4is_e70413 ): void {
$m4is_v2869 =$this->m4is_l2075($m4is_e70413->settings );
 if($m4is_v2869 ){
 if(is_array($this->container_visibility )&&!empty($this->container_visibility )){
foreach ($this->container_visibility as $m4is_m95 =>$m4is_g91703 ){
 if(is_array($m4is_g91703 )&&!empty($m4is_g91703 )){
foreach ($m4is_g91703 as $m4is_x89640 =>$m4is_p65680 ){
 if(is_array($m4is_p65680 )&&!empty($m4is_p65680 )){
foreach ($m4is_p65680 as $m4is_a60 =>$m4is_g617){
if($m4is_a60 === $m4is_e70413->parent ){
$m4is_v2869 =false;
 
}
}
}
}
}
}
}
} if(!$m4is_v2869 ){
$this->module_visibility[]=$m4is_e70413->node;
 add_filter('fl_builder_template_path', [$this, 'm4is_z23'], 10, 3 );
 
}
} public 
function m4is_n7260(object $m4is_e70413 ){
$m4is_o74013 =array_search($m4is_e70413->node, $this->module_visibility );
 if($m4is_o74013 !== false ){
unset($this->module_visibility[$m4is_o74013]);
  remove_filter('fl_builder_template_path', [$this, 'm4is_z23'], 10 );
 
}
} public 
function m4is_z23($m4is_d04266, $m4is_w43796, $m4is_j09 ): bool {
return false;
 
} public 
function m4is_l2075(object $m4is_j296 ): bool {
$m4is_j86631 =m4is_v679::PREFIX;
 $m4is_m96240 =[];
  foreach($m4is_j296 as $m4is_k52736 =>$m4is_v586 ){
if(strpos($m4is_k52736, "{$m4is_j86631
}_membership_levels" )!== false &&$m4is_v586 === '1' ){
$m4is_m96240[]=(int) str_replace("{$m4is_j86631
}_membership_levels-", '', $m4is_k52736 );
 
}
}$m4is_g5846 =isset($m4is_j296->{
"{$m4is_j86631
}_access_tags"
})? $m4is_j296->{
"{$m4is_j86631
}_access_tags"
}: '';
 $m4is_g5846 =!empty($m4is_g5846)&&is_array($m4is_g5846)? implode(',', $m4is_g5846): $m4is_g5846;
 $m4is_x91 =isset($m4is_j296->{
"{$m4is_j86631
}_access_tags2"
})? $m4is_j296->{
"{$m4is_j86631
}_access_tags2"
}: '';
 $m4is_x91 =!empty($m4is_x91)&&is_array($m4is_x91)? implode(',', $m4is_x91): $m4is_x91;
 $m4is_u897 =['memberships' =>implode(',', $m4is_m96240 ), 'any_membership' =>isset($m4is_j296->{
"{$m4is_j86631
}_anymembership"
})&&$m4is_j296->{
"{$m4is_j86631
}_anymembership"
}=== '1' ? 1 : 0, 'logged_in_only' =>isset($m4is_j296->{
"{$m4is_j86631
}_loggedin"
})&&$m4is_j296->{
"{$m4is_j86631
}_loggedin"
}=== '1' ? 1 : 0, 'logged_out_only' =>isset($m4is_j296->{
"{$m4is_j86631
}_anonymous_only"
})&&$m4is_j296->{
"{$m4is_j86631
}_anonymous_only"
}=== '1' ? 1 : 0, 'invert_results' =>isset($m4is_j296->{
"{$m4is_j86631
}_invert_results"
})&&$m4is_j296->{
"{$m4is_j86631
}_invert_results"
}=== '1' ? 1 : 0, 'contact_ids' =>!empty($m4is_j296->{
"{$m4is_j86631
}_contact_ids"
})? sanitize_text_field($m4is_j296->{
"{$m4is_j86631
}_contact_ids"
}): '', 'tags1' =>!empty($m4is_g5846)? trim($m4is_g5846, ','): '', 'tags2' =>!empty($m4is_x91)? trim($m4is_x91, ','): '', 'eval' =>!empty($m4is_j296->{
"{$m4is_j86631
}_eval"
})? trim($m4is_j296->{
"{$m4is_j86631
}_eval"
}): '', 'asset_id' =>!empty($m4is_j296->{
"{$m4is_j86631
}_asset_id"
})? sanitize_text_field($m4is_j296->{
"{$m4is_j86631
}_asset_id"
}): '' ];
 return m4is_v679::m4is_c26()->m4is_b679()->m4is_h86($m4is_u897, $this->slug );
 
}
}

