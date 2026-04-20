<?php

/**
 * Copyright (c) 2023-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_u4397 {
private $m4is_r1546;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_d4861();
  
}private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
}private 
function m4is_d4861(){
add_action('switch_to_user', [$this, 'm4is_b0287'], 10, 4 );
 
}public 
function m4is_b0287(int $m4is_z078, int $m4is_e50, $m4is_n21, $m4is_d61936 ){
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_z078 );
 if(!$m4is_h21895 ){
return;
 
}$this->m4is_r1546->m4is_x4831($m4is_h21895 );
 m4is_q82::m4is_u687($m4is_z078 );
 
}
}

