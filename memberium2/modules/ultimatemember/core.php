<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_g67182 {
 public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
  $this->m4is_d4861();
 $this->m4is_e92();
 
} private 
function m4is_d4861(){
add_filter('memberium/posts/unenhanced', [$this, 'm4is_v2664'], 10, 1 );
 add_filter('um_login_allow_nonce_verification', [$this, 'm4is_f4066'], 1, 1 );
 add_filter('init', [$this, 'm4is_e92'], 1, 0 );
  
}public 
function m4is_e92(){
remove_filter('init', 'um_login_allow_nonce_verification', 'um_login_nonce_safety', 99 );
 
} public 
function m4is_v2664(array $m4is_l15 =[]): array {
$m4is_l15[]='um_directory';
 $m4is_l15[]='um_form';
 return $m4is_l15;
 
} public 
function m4is_f4066($m4is_t25641 ){
wp_set_current_user(0 );
 return $m4is_t25641;
 
} 
}

