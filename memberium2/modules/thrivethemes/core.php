<?php
 class_exists('m4is_r83')||die();
 final 
class m4is_k487 {
private $m4is_r1546;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_q97523();
 
}private 
function m4is_q97523(){
$m4is_l61035 =['m4is_g7961' =>__DIR__ . '/entities/memberium', 'm4is_u30' =>__DIR__ . '/fields/tags', 'm4is_i65086' =>__DIR__ . '/fields/memberships', ];
 $this->m4is_r1546->m4is_p39($m4is_l61035 );
 add_action('init', [$this, 'm4is_p39' ]);
 add_filter('memberium/modules/active/names', [$this, 'm4is_f809'], 10, 1 );
 $this->m4is_m6617();
 
}
function m4is_f809(array $m4is_y634 ): array {
return array_merge($m4is_y634, ['ThriveThemes Integration' ]);
 
}
function m4is_p39(){
tve_register_condition_entity('m4is_g7961' );
 tve_register_condition_field('m4is_u30' );
 tve_register_condition_field('m4is_i65086' );
 
}
function m4is_m6617(?string $m4is_o18692 =''): array {
$m4is_w89066 =(array)$this->m4is_r1546->m4is_j498('memberships' );
 $m4is_m96240 =[];
 foreach ($m4is_w89066 as $m4is_l9671 =>$m4is_w64 ){
if(empty($m4is_o18692 )||stripos($m4is_w64['name'], $m4is_o18692 )!== false ){
$m4is_m96240[$m4is_l9671]=$m4is_w64['name'];
 
}
}return $m4is_m96240;
 
}
}

