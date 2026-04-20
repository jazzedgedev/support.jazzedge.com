<?php
 class_exists('m4is_r83' )||die();
  final 
class m4is_v94 {
static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_d4861();
 
}private 
function m4is_d4861(){
add_filter('memberium/modules/active/names', function(array $m4is_y634 ){
return array_merge($m4is_y634, ['GravityForms Support']);
 
});
 
}
}

