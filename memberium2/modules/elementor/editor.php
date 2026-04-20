<?php

/**
 * Copyright (c) 2018-2022 David J Bullock
 * Web Power and Light
*/


 class_exists('m4is_r83' )||die();
  final 
class m4is_e4173 {
public $slug ='elementor';
 public $version ='1.1.0';
  public $to_json =[];
  public $omitted_blocks =[];
  public $ns ='';
  public $prefix ='';
  public $I18n ='';
  public $access_class;
  private 
function __construct(){
$this->m4is_i702();
 
}private 
function m4is_i702(): void {
$this->slug ='elementor';
 $this->version ='1.1.0';
  $this->to_json =[];
  $this->omitted_blocks =[];
  $this->ns ='';
  $this->prefix ='';
  $this->I18n ='';
  
}public static 
function m4is_c26(): self {
static $m4is_b30146;
 if(is_null($m4is_b30146 )){
$m4is_b30146 =new self;
 $m4is_b30146->access_class =m4is_v679::m4is_c26();
 $m4is_b30146->prefix =$m4is_b30146->access_class::PREFIX;
 $m4is_b30146->ns =$m4is_b30146->access_class::NS;
 $m4is_b30146->m4is_h269();
 
}return $m4is_b30146;
 
}public 
function m4is_h269(){
$this->m4is_o31();
 $m4is_a95428 =$this->access_class->m4is_j16();
 $this->I18n =$m4is_a95428->m4is_p6406(false, $this->slug );
 $this->to_json['WPAL_BLOCKS_PREFIX']=$this->prefix;
 $this->to_json['WPAL_BLOCKS_KEYS_REMOVED_TEXT']=$this->I18n['keys_removed_text'];
 $this->to_json['controls']=$m4is_a95428->m4is_p43571($this->slug );
  $this->to_json['tags']=$m4is_a95428->m4is_z2906();
  $this->omitted_blocks =apply_filters('memberium/elementor/settings/omitted_blocks', ['column']);
 
}private 
function m4is_o31(){
add_filter('memberium/elementor/editor/control/args', [$this, 'm4is_r66417'], 10, 5 );
  add_action('elementor/element/container/section_effects/after_section_end', [$this, 'm4is_e156'], 10, 2 );
 add_action('elementor/element/column/section_effects/after_section_end', [$this, 'm4is_e156'], 10, 2 );
 
} public 
function m4is_u69(){
$m4is_p95312 =plugin_dir_url(__FILE__ );
 wp_enqueue_style('wpal-blocks-elementor-editor', "{$m4is_p95312
}/editor.css", [], $this->version, 'all');
 wp_enqueue_script('wpal-blocks-elementor-editor', "{$m4is_p95312
}/editor.js", ['jquery'], $this->version, true);
 wp_localize_script('wpal-blocks-elementor-editor', 'wpale_params', $this->to_json);
 
} public 
function m4is_a8614($m4is_x95460, $m4is_x71 ){
if(in_array($m4is_x95460->get_type(), $this->omitted_blocks)){
return;
 
}$m4is_w4309 =$this->to_json['controls'];
 if(!$m4is_w4309 ||empty($m4is_w4309)){
return;
 
} $m4is_x95460->start_controls_section('wpal-blocks', ['label' =>$this->I18n['settings_title'], 'tab' =>\Elementor\Controls_Manager::TAB_ADVANCED ]);
 foreach ($m4is_w4309 as $m4is_r243 =>$m4is_s53067 ){
$m4is_j0361 =isset($m4is_s53067['type'])? $this->m4is_b61364($m4is_s53067['type']): false;
 $m4is_k52736 =isset($m4is_s53067['name'])? $m4is_s53067['name']: false;
 if($m4is_k52736 &&$m4is_j0361 ){
$m4is_z60 =['label' =>isset($m4is_s53067['label'])? $m4is_s53067['label']: false, 'type' =>$m4is_j0361 ];
  $m4is_i392 =['default', 'description', 'options', 'label_on', 'label_off', 'return_value', 'multiple', 'rows', 'separator', 'placeholder'];
 foreach ($m4is_i392 as $m4is_m4052 =>$m4is_d87521){
if(isset($m4is_s53067[$m4is_d87521])){
$m4is_z60[$m4is_d87521]=$m4is_s53067[$m4is_d87521];
 
}
}$m4is_z60 =apply_filters('memberium/elementor/editor/control/args', $m4is_z60, $m4is_k52736, $m4is_x95460, $m4is_x71 );
 $m4is_x95460->add_control($m4is_k52736, $m4is_z60 );
 
}
} $m4is_x95460->end_controls_section();
 
}  private 
function m4is_b61364(string $m4is_j0361 ='' ){
$m4is_j0361 =!empty($m4is_j0361)? strtolower($m4is_j0361 ): $m4is_j0361;
 if($m4is_j0361 === 'checkbox' ){
return \Elementor\Controls_Manager::SWITCHER;
 
}elseif($m4is_j0361 === 'select2' ||$m4is_j0361 === 'text' ){
return \Elementor\Controls_Manager::TEXT;
 
}elseif($m4is_j0361 === 'textarea' ){
return \Elementor\Controls_Manager::TEXTAREA;
 
}return false;
 
} public 
function m4is_r66417($m4is_z60, $m4is_k52736, $m4is_x95460, $m4is_x71 ){
if($m4is_k52736 === "{$this->prefix
}_loggedin" ){
$m4is_z60['separator']='before';
 
}if($m4is_k52736 === "{$this->prefix
}_access_tags" ){
$m4is_z60['separator']='before';
 
}if($m4is_k52736 === "{$this->prefix
}_access_tags" ||$m4is_k52736 === "{$this->prefix
}_access_tags2" ){
$m4is_z60['label_block']=true;
 $m4is_z60['default']='';
 
}if($m4is_k52736 === "{$this->prefix
}_invert_results" ){
$m4is_z60['separator']='before';
 
}return $m4is_z60;
 
} public 
function m4is_e156($m4is_x95460, $m4is_y66291 ){
$m4is_x95460->start_controls_section('memberium-section-visibility', ['label' =>__('Memberium' ), 'tab' =>\Elementor\Controls_Manager::TAB_ADVANCED ]);
    $m4is_x95460->add_control('memberium_login_status', ['label' =>__('Logged In Visibility' ), 'type' =>\Elementor\Controls_Manager::SELECT, 'label_block' =>false, 'options' =>['loggedin' =>'Logged-In Only', 'anonymous' =>'Logged-Out Only', 'both' =>'Both', ], 'description' =>__('' )]);
    $m4is_x95460->add_control('hr', ['type' =>\Elementor\Controls_Manager::DIVIDER, 'condition' =>['memberium_login_status' =>'loggedin', ], ]);
    $m4is_x95460->add_control('memberium_any_membership', ['label' =>__('Any Membership' ), 'type' =>\Elementor\Controls_Manager::SWITCHER, 'label_block' =>false, 'label_on' =>'Yes', 'label_off' =>'No', 'description' =>__('' ), 'condition' =>['memberium_login_status' =>'loggedin', ], ]);
    $m4is_x95460->add_control('hr2', ['type' =>\Elementor\Controls_Manager::DIVIDER, 'condition' =>['memberium_login_status' =>'loggedin', ], ]);
    $m4is_m96240 =m4is_r83::m4is_c26()->m4is_b96654([], 'elementor' );
 foreach ($m4is_m96240 as $m4is_d07693 =>$m4is_w64 ){
$m4is_r37596[$m4is_d07693]=$m4is_w64['name'];
 
}$m4is_x95460->add_control('memberium_memberships_section', ['label' =>__('Require Membership' ), 'type' =>\Elementor\Controls_Manager::SELECT2, 'label_block' =>true, 'multiple' =>true, 'options' =>$m4is_r37596, 'description' =>__('The member must have one or more of the selected memberships.' ), 'condition' =>['memberium_login_status' =>'loggedin', 'memberium_any_membership' =>'', ]]);
    $m4is_x95460->add_control('hr2', ['type' =>\Elementor\Controls_Manager::DIVIDER, 'condition' =>['memberium_login_status' =>'loggedin', ], ]);
 $m4is_x95460->add_control('memberium_tags1_section', ['label' =>__('Require Any Tag ID' ), 'type' =>\Elementor\Controls_Manager::SELECT2, 'label_block' =>true, 'multiple' =>true, 'options' =>m4is_k865::m4is_b94([], 'elementor' ),  'description' =>__('The member must have one or more of the selected tags.' ), 'condition' =>['memberium_login_status' =>'loggedin', ]]);
  $m4is_x95460->add_control('memberium_all_tags_section', ['label' =>__('Require All Tags' ), 'type' =>\Elementor\Controls_Manager::SELECT2, 'label_block' =>true, 'multiple' =>true, 'options' =>m4is_k865::m4is_b94([], 'elementor' ),  'description' =>__('The member must have all the selected tags.' ), 'condition' =>['memberium_login_status' =>'loggedin', ]]);
 $m4is_x95460->end_controls_section();
 
}
}

