<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 
class m4is_h50968 {
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_s2469();
 $this->m4is_p660();
 
}
function m4is_s2469(): void {
$m4is_s6347 ='m4is_p635';
 $m4is_o9361 =['umbrella_child_count' =>'m4is_s683', 'umbrella_download_csv' =>'m4is_n86472', 'umbrella_enroll_child' =>'m4is_i6127', 'umbrella_ld_course_info' =>'m4is_w6320', 'umbrella_list_children_nav' =>'m4is_s6532', 'umbrella_list_children' =>'m4is_x10', 'umbrella_lms_dashboard' =>'m4is_e42356', 'umbrella_transfer_points' =>'m4is_o35', 'umbrella_update_team_membership' =>'m4is_e81', ];
 foreach($m4is_o9361 as $m4is_k52736 =>$m4is_s7349 ){
add_shortcode($m4is_k52736, [$m4is_s6347, $m4is_s7349]);
 
}
}
function m4is_p660(): void {
if(empty($_POST['memb_form_type'])){
return;
 
}$m4is_y46186 =['m4is_i18563' =>'m4is_i18563', ];
 
}
}

