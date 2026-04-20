<?php

/**
 * Copyright (C) 2022 David Bullock
 * Web Power and Light, LLC
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_g59 {
static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_d4861();
 
}private 
function m4is_d4861(){
add_action('memberium/session/updated', [$this, 'm4is_j71654'], 10, 2 );
 if(is_admin()){
add_filter('memberium/modules/active/names', [$this, 'm4is_f6528'], 10, 1 );
 
}
}
function m4is_f6528($m4is_y634 =[]){
return array_merge($m4is_y634, ['Peepso Groups for Memberium']);
 
}
function m4is_j71654($m4is_f087, $m4is_k824){
global $wpdb;
 if(!$m4is_f087 ){
return;
 
}$m4is_l9321 =isset($m4is_k824['memb_user']['tags'])? explode(',', $m4is_k824['memb_user']['tags']): [];
 if(empty($m4is_l9321 )){
return;
 
} $m4is_v2613 ="SELECT `gm_group_id` FROM `{$wpdb->prefix
}peepso_group_members` WHERE `gm_user_id` = {$m4is_f087
}";
 $m4is_t87 =implode(',', array_keys($wpdb->get_results($m4is_v2613, OBJECT_K )));
 if(!empty($m4is_t87)){
$m4is_v2613 ="SELECT `post_id`, `meta_value` FROM `{$wpdb->postmeta
}` WHERE `post_id` IN ( {$m4is_t87
} ) AND `meta_key` = 'autojoin' AND `meta_value` > ''";
 $m4is_g35162 =$wpdb->get_results($m4is_v2613, OBJECT_K);
 if(!empty($m4is_g35162)){
foreach($m4is_g35162 as $m4is_d439){
if(!in_array($m4is_d439->meta_value, $m4is_l9321)){
 $peepso =new PeepSoGroupUser($m4is_d439->post_id, $m4is_f087);
 $peepso->member_leave();
 
}
}
}
} $m4is_v2613 ="SELECT distinct(`gm_group_id`) FROM `{$wpdb->prefix
}peepso_group_members` WHERE `gm_user_id` <> {$m4is_f087
}";
 $m4is_t87 =implode(',', array_keys($wpdb->get_results($m4is_v2613, OBJECT_K)));
 if(!empty($m4is_t87)){
$m4is_v2613 ="SELECT `post_id`, `meta_value` FROM `{$wpdb->postmeta
}` WHERE `post_id` IN ( {$m4is_t87
} ) AND `meta_key` = 'autojoin' AND `meta_value` > ''";
 $m4is_g35162 =$wpdb->get_results($m4is_v2613, OBJECT_K);
 if(!empty($m4is_g35162)){
foreach($m4is_g35162 as $m4is_d439){
if(in_array($m4is_d439->meta_value, $m4is_l9321)){
 $peepso =new \PeepSoGroupUser($m4is_d439->post_id, $m4is_f087 );
 $peepso->member_join();
 
}
}
}
}
}
}

