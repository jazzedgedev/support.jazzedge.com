<?php

/**
* Copyright (c) 2018-2022 David J Bullock
* Web Power and Light
*/


 class_exists('m4is_r83')||die();
  final 
class m4is_l69 {
static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){

}public 
function m4is_p147(): void {
add_action('memberium/cpt/is_public', [$this, 'm4is_a048'], 10, 2 );
 add_action('wpal/block/access/init', [$this, 'm4is_h269']);
 
}public 
function m4is_h269(){
if(is_admin()){
return;
 
}if(isset($_GET['fl_builder'])){
$this->m4is_o3890();
 
}else{
$this->m4is_b679();
 
}
}public 
function m4is_a048(bool $m4is_f0174, string $m4is_q485 ): bool {
if(current_user_can('edit_posts' )&&isset($_GET['fl_builder'])){
return true;
 
}return $m4is_f0174;
 
}
function m4is_o3890(){
static $m4is_i461;
 if(!isset($m4is_i461 )){
include_once __DIR__ . '/' . 'editor.php';
 $m4is_i461 =m4is_o50786::m4is_c26();
 
}return $m4is_i461;
 
}
function m4is_b679(){
static $m4is_f683;
 if(!isset($m4is_f683 )){
include_once __DIR__ . '/' . 'frontend.php';
 $m4is_f683 =m4is_l56149::m4is_c26();
 
}return $m4is_f683;
 
}
}

