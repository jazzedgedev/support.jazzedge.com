<?php

/**
 * Copyright (c) 2021-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83')||die();
 final 
class m4is_e230 {
private 
function __construct(){
add_filter('memberium/modules/active/names', function($m4is_y634){
return array_merge($m4is_y634, ['Facebook Shortcodes']);
 
});
 
}static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}
}

