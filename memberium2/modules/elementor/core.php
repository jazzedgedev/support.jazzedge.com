<?php

/**
* Copyright (c) 2018-2022 David J Bullock
* Web Power and Light
*/


 class_exists('m4is_r83' )||die();
  final 
class m4is_e1047 {
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){

} public 
function m4is_p147(): void {
add_action('wpal/block/access/init', [$this, 'm4is_h269']);
 
}
function m4is_h269(){
add_action('elementor/element/after_section_end', [$this, 'm4is_e831'], 10, 2 );
  add_action('elementor/editor/before_enqueue_scripts', [$this, 'm4is_t21']);
   if(is_admin()&&!wp_doing_ajax()){
return;
 
}add_action('template_redirect', [$this, 'm4is_f620' ], PHP_INT_MAX );
 
}   public 
function m4is_b679(): m4is_d02 {
static $m4is_f683;
 if(!isset($m4is_f683 )){
include_once __DIR__ . '/frontend.php';
 $m4is_f683 =m4is_d02::m4is_c26();
 
}return $m4is_f683;
 
} public 
function m4is_f620(){
$m4is_w468 =\Elementor\Plugin::instance();
 if($m4is_w468->editor->is_edit_mode()){
return;
 
}if($m4is_w468->preview->is_preview_mode()){
return;
 
}if(!empty($_GET['action'])&&$_GET['action']=== 'elementor' ){
return;
 
} remove_action('elementor/element/after_section_end', [$this, 'm4is_e831'], 10 );
  $this->m4is_b679();
 
}      public 
function m4is_o3890(){
static $m4is_i461;
 if(!isset($m4is_i461 )){
include_once __DIR__ . '/editor.php';
 $m4is_i461 =m4is_e4173::m4is_c26();
 
}return $m4is_i461;
 
} public 
function m4is_e831($m4is_x95460, $m4is_x71 ): void {
if('section_advanced' === $m4is_x71 ||'_section_style' === $m4is_x71 ){
$this->m4is_o3890()->m4is_a8614($m4is_x95460, $m4is_x71 );
 
}
} public 
function m4is_t21(): void {
$this->m4is_o3890()->m4is_u69();
 
}
}

