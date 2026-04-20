<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83')||die();
 final 
class m4is_z59 {

function process_path_protect_rules(){
$m4is_q78 =$_SERVER['REQUEST_URI'];
 $m4is_c05328 ='';
 $m4is_f56 =get_site_url();
 $m4is_e0213 =$this->m4is_i674();
 $m4is_x51 =function_exists('is_user_logged_in' )? is_user_logged_in(): false;
 if(!empty($m4is_e0213['rules'])&&is_array($m4is_e0213['rules'])){
foreach ($m4is_e0213['rules']as $m4is_x806 ){
$m4is_x806['urls']=isset($m4is_x806['urls'])? $m4is_x806['urls']: '';
 $m4is_q6802 =array_filter(array_map('trim', array_filter(explode("\n", $m4is_x806['urls']))));
 if(is_array($m4is_q6802)){
foreach($m4is_q6802 as $m4is_n6062 ){
if(strpos($m4is_q78, $m4is_n6062 )=== 0 ){
$m4is_d271 =true;
 if($m4is_x806['logged_in']== 1 &&!$m4is_x51 ){
$m4is_d271 =false;
 
}if($m4is_x806['anonymous_only']== 1 &&$m4is_x51 ){
$m4is_d271 =false;
 
}if(!$m4is_d271 ){
$m4is_c05328 =$m4is_x806['prohibited_action'];
 $m4is_f56 =$m4is_x806['redirect_url'];
 break;
 
}
}
}
}
}
}if($m4is_c05328 == 'hide' ){
include(get_query_template('404' ));
 exit;
 
}elseif($m4is_c05328 == 'redirect' ){
m4is_j586::m4is_x7134();
 nocache_headers();
 wp_redirect($m4is_f56);
 exit;
 
}
}private 
function m4is_i674(){
$m4is_l9671 ='WPAL/pathprotect/settings';
 $m4is_z2019 ='MemberiumPathProtect';
 $m4is_e0213 =get_option($m4is_l9671, false);
 if($m4is_e0213 === false){
$m4is_e0213 =get_option($m4is_z2019, '');
 if(is_array($m4is_e0213)){
update_option($m4is_l9671, $m4is_e0213);
 
}
}if(!is_array($m4is_e0213)){
$m4is_e0213 =[];
 
}return $m4is_e0213;
 
}
function __construct(){
global $pagenow;
 if(!in_array($pagenow, ['wp-login.php', 'wp-register.php'])){
add_action('init', [$this, 'process_path_protect_rules']);
 
}
}
}

