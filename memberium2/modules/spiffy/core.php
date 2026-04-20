<?php

/**
 * Copyright (c) 2023-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_y3186 {
static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_h269();
 
}
function m4is_h269(){
if(!is_admin()){
require_once __DIR__ . '/frontend.php';
 new m4is_j766();
 if($_SERVER['REQUEST_METHOD']=== 'POST' ){
require_once __DIR__ . '/webhooks.php';
 m4is_h20536::m4is_c26();
 
}
}else{
add_filter('memberium/modules/active/names', function($m4is_y634 ){
return array_merge($m4is_y634, ['Spiffy for Memberium']);
 
});
 
}
}
}

