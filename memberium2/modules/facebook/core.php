<?php

/**
 * Copyright (c) 2021-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83')||die();
 final 
class m4is_b9348 {
private string $m4is_k57;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_d4861();
 
}private 
function m4is_d4861(){
if(is_admin()){
m4is_e230::m4is_c26();
 
}else{
$this->m4is_s2469();
 add_action('wp_head', [$this, 'm4is_o4532']);
 
}
}private 
function m4is_s2469(){
$m4is_s6347 ='m4is_u6435';
 add_shortcode('memb_fb_comments', [$m4is_s6347, 'm4is_r879']);
 add_shortcode('memb_fb_embed_comment', [$m4is_s6347, 'm4is_b86316']);
 add_shortcode('memb_fb_follow', [$m4is_s6347, 'm4is_s061']);
 add_shortcode('memb_fb_like', [$m4is_s6347, 'm4is_n30465']);
 add_shortcode('memb_fb_page', [$m4is_s6347, 'm4is_y36815']);
 add_shortcode('memb_fb_save_button', [$m4is_s6347, 'm4is_w50']);
 add_shortcode('memb_fb_send', [$m4is_s6347, 'm4is_i15']);
 add_shortcode('memb_fb_share', [$m4is_s6347, 'm4is_g584']);
 add_shortcode('memb_fb_video', [$m4is_s6347, 'm4is_x126']);
 
}private 
function get_app_id(){
if($this->m4is_k57 === false){
$this->m4is_k57 =m4is_r83::m4is_c26()->m4is_j498('settings', 'facebook_app_id');
 
}return $this->m4is_k57;
 
}
function m4is_o4532(){
$this->m4is_k57 =m4is_r83::m4is_c26()->m4is_j498('settings', 'facebook_app_id');
 if(!empty($this->m4is_k57)){
echo '
			<script>
				window.fbAsyncInit = function() {
				FB.init({
					appId            : \'' . $this->m4is_k57 . '\',
					autoLogAppEvents : true,
					xfbml            : true,
					version          : \'v4.0\'
				});
				};
			</script>
			<script async defer src="https://connect.facebook.net/en_US/sdk.js"></script>
			';
 
}
}
}

