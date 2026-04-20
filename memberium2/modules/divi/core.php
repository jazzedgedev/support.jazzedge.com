<?php

/**
 * Proprietary Software - All Rights Reserved
 *
 * This file is part of the Memberium plugin, which is proprietary software developed by Web Power and Light.
 * Unauthorized copying, distribution, or modification of this file, via any medium, is strictly prohibited.
 *
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 *
 * For licensing information, please contact Web Power and Light.
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_d69560 {
static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){

}public 
function m4is_p147(){
add_action('wpal/block/access/init', [$this, 'm4is_h269']);
 
}public 
function m4is_h269(){
add_action('et_builder_modules_loaded', [$this, 'm4is_s8154'], PHP_INT_MAX );
 add_action('admin_enqueue_scripts', [$this, 'm4is_h92']);
 
}
function m4is_s8154(){
if(is_admin()||isset($_GET['et_fb'])){
$m4is_s681 =isset($_GET['page'])&&$_GET['page']== 'et_theme_builder';
 $m4is_s681 =$m4is_s681 ||isset($_GET['et_tb']);
 if($m4is_s681 <> 'et_theme_builder' &&$m4is_s681 == false ){
$this->m4is_o3890()->m4is_h269();
 
}
}else{
$this->m4is_b679()->m4is_h269();
 
}
} 
function m4is_h92($m4is_e81053 ){
$m4is_b19 =['edit.php', 'post-new.php', 'post.php'];
 if(in_array($m4is_e81053, $m4is_b19 )){
wp_enqueue_style('select2css_divi', plugin_dir_url(__FILE__). 'select2_divi.css', false, '1.0.5', 'all' );
 
}
}
function m4is_o3890(){
static $m4is_i461 =null;
 if(is_null($m4is_i461 )){
include_once __DIR__ . '/' . 'editor.php';
 $m4is_i461 =m4is_t26::m4is_c26();
 
}return $m4is_i461;
 
}
function m4is_b679(){
static $m4is_f683 =null;
 if(is_null($m4is_f683 )){
include_once __DIR__ . '/' . 'frontend.php';
 $m4is_f683 =m4is_i651::m4is_c26();
 
}return $m4is_f683;
 
}
}

