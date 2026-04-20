<?php

/**
* Copyright (c) 2018-2022 David J Bullock
* Web Power and Light
*/


  class_exists('m4is_r83')||die();
 final 
class m4is_n3268 {
static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_d4861();
 
}private 
function m4is_d4861(){
add_filter('bbp_get_topic_subscribers', [$this, 'm4is_b46']);
 
}
function m4is_b46($m4is_r403 ){
if(!empty($m4is_r403 )&&is_array($m4is_r403 )){
global $wpdb;
 $m4is_p79534 =implode(',', $m4is_r403 );
 $m4is_v2613 ="SELECT user_id FROM {$wpdb->usermeta
} WHERE user_id IN (" . $m4is_p79534 . ") AND `meta_key` = 'memberium_optout' AND `meta_value` = 1";
 $m4is_j0972 =$wpdb->get_col($m4is_v2613 );
 $m4is_r403 =array_diff($m4is_r403, $m4is_j0972 );
 
}return $m4is_r403;
 
}
}

