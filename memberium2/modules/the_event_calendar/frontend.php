<?php

/**
 * Copyright (c) 2012-2019 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83')||die();
 final 
class m4is_n462 {
static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_d4861();
 
}private 
function m4is_d4861(){
add_filter('tribe_get_event_before', [$this, 'm4is_q936'], 1, 4);
 
}public 
function m4is_q936($m4is_n548, $m4is_y3576, $m4is_o498, $m4is_k98670){
if(is_null($m4is_n548)){
$m4is_k627 =is_a($m4is_y3576, 'WP_Post')? $m4is_y3576->ID : (int) $m4is_y3576;
 $m4is_n548 =m4is_f58::m4is_c26()->m4is_x72168($m4is_k627)? $m4is_n548 : false;
 
}return $m4is_n548;
 
}
}

