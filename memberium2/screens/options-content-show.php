<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_g20::m4is_h682();
 final 
class m4is_g20 {
private $m4is_r1546;
 public static 
function m4is_h682(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_o719();
 
}private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
}private 
function m4is_o719(){
$m4is_e0213 =$this->m4is_r1546->m4is_j498('settings' );
 $m4is_g56124 =$this->m4is_r1546->m4is_j498('settings', 'site_lock_enabled' );
 $m4is_f261 =$this->m4is_r1546->m4is_j498('settings', 'page_inheritance' );
 $m4is_m6796 =$this->m4is_r1546->m4is_j498('settings', 'force_learndash_inheritance' );
 $m4is_l68 =$this->m4is_r1546->m4is_j498('settings', 'default_prohibited_action' );
 $m4is_q649 =$this->m4is_h54986();
 $m4is_k67 =$this->m4is_l40();
 $m4is_a27648 =wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memberium_options_nonce', true, false );
 echo <<<HTMLBLOCK
			<p>You can set your content protection options below but even though you can do some fun stuff, none of them are required to launch your site.</p>
			<form method="POST" action="">
				{$m4is_a27648
}
				<h3>General Protection Options</h3>
				<ul>
		HTMLBLOCK;
 m4is_h65::m4is_y648('Site Lock', 'site_lock_enabled', 8354, $m4is_g56124 );
 m4is_h65::m4is_y648('Page Security Inheritance', 'page_inheritance', 6374, $m4is_f261 );
 m4is_h65::m4is_y648('Force LearnDash Security Inheritance', 'force_learndash_inheritance', 21594, $m4is_m6796 );
 m4is_h65::m4is_z30162('Default Prohibited Action', 'default_prohibited_action', $m4is_e0213['default_prohibited_action'], $m4is_q649, ['help_id' =>1217]);
 m4is_h65::m4is_i014('Default Page Redirect', 'default_page_redirect', $m4is_e0213['default_page_redirect'], ['help_id' =>1220, 'style' =>'text-align:left;width:250px;', 'placeholder' =>'Absolute or Relative URL.  Leave blank for login page.']);
  echo <<<HTMLBLOCK
			</ul>
			<hr>
			<h3>Excerpts/Teasers</h3>
			<ul>
		HTMLBLOCK;
 m4is_h65::m4is_y648('Always Generate Excerpts', 'autogenerate_excerpts', 6808, $m4is_e0213['autogenerate_excerpts']);
 m4is_h65::m4is_z30162('Auto Include Default Excerpt', 'include_default_excerpt', $m4is_e0213['include_default_excerpt'], $m4is_k67, ['help_id' =>6808]);
 m4is_h65::m4is_w6712('Auto Excerpt Length', 'excerpt_length', $m4is_e0213['excerpt_length'], ['help_id' =>6811, 'min' =>0, 'max' =>999, 'size' =>5]);
 $this->m4is_y81($m4is_e0213 );
 echo m4is_h65::m4is_o64(1224);
 echo <<<HTMLBLOCK
			</ul>
			<p><input type="submit" value="Update" class="button-primary"></p>
			</form>
		HTMLBLOCK;
 
}private 
function m4is_h54986(){
 return ['redirect' =>'Redirect', 'hide' =>'Hide Completely', 'excerpt' =>'Show Excerpt Only', '' =>'None', ];
 
}private 
function m4is_l40(){
return ['' =>'None', 'prepend' =>'Prepend Default Excerpt', 'append' =>'Append Default Excerpt', 'embed' =>'Embed Excerpt', ];
 
}private 
function m4is_y81($m4is_e0213 ){
$m4is_f75 =defined('MEMBERIUM_NOWYSIWYG' )&&constant('MEMBERIUM_NOWYSIWYG' );
 echo '<table style="width:100%;">';
 echo '<tr><td width="300" valign="top">';
 echo '<p>Default Excerpt', m4is_h65::m4is_o64(1224), '</p>';
 echo '</td><td style="padding-right:25px;">';
 if($m4is_f75 ){
echo '<textarea name="global_excerpt" cols="40" rows="5" placeholder="Enter your teaser content HTML here">', $m4is_e0213['global_excerpt'], '</textarea>';
 
}else{
wp_editor($m4is_e0213['global_excerpt'], 'global_excerpt' );
 
}echo '</td></tr></table>';
 
}
}

