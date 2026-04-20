<?php

/**
* Copyright (c) 2018-2022 David J Bullock
* Web Power and Light
*/


 class_exists('m4is_r83' )||die();
  
class m4is_s45 extends \Elementor\Widget_Shortcode {
 protected 
function render(){
$m4is_t09761 =$this->get_settings_for_display('shortcode' );
 $m4is_t09761 =apply_filters('memberium/elementor/widget/shortcode/render', $m4is_t09761, $this->get_settings_for_display());
 if(!empty($m4is_t09761 )){
global $wp_embed;
 $m4is_t09761 =do_shortcode(shortcode_unautop($wp_embed->run_shortcode($m4is_t09761 )));
  echo '<div class="elementor-shortcode">', $m4is_t09761, '</div>';
 
}
}
}

