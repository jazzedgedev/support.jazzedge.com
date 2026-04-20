<?php

/**
 * Copyright (c) 2021-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83')||die();
 final 
class m4is_t6065 {
private 
function m4is_d4861(){
if(is_admin()){
require_once __DIR__ . '/admin.php';
 m4is_s06874::m4is_c26();
 
}
}private 
function __construct(){
$this->m4is_d4861();
 
}static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}
}

