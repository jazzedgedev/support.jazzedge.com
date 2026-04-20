<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_f05398::m4is_z95();
 final 
class m4is_f05398 {
private $m4is_r1546;
  static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->process_updates();
 $this->m4is_d628();
 
}private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
} private 
function process_updates(){
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}$this->m4is_d68315();
 
}private 
function m4is_d68315(){
if(!isset($_POST['is4wp_ignore_tag_categories'])||!is_array($_POST['is4wp_ignore_tag_categories'])){
return;
 
}$m4is_x379 =$_POST['is4wp_ignore_tag_categories'];
 $m4is_v6720 =m4is_z6894::m4is_u43(false, true );
 $m4is_j4692 =implode(',', array_diff($m4is_v6720, $m4is_x379 ));
 $this->m4is_r1546->m4is_d64918($m4is_j4692, 'settings', 'ignore_tag_categories' );
 m4is_k865::m4is_l26();
 m4is_h65::m4is_z896('Tag Categories Ignore List Updated.' );
 
} 
function m4is_d628(){
$this->m4is_n0239();
 echo '<p>Select which tag categories <strong>are</strong> synchronized.</p>';
 echo '<p><strong>For best performance, sync either ALL categories, or only ONE category.</strong></p>';
 echo '<p>Tags in the <strong style="color:red;">BOLD RED</strong> categories are not synced.</p>';
 $this->m4is_m57380();
 echo '<p><input type="submit" name="save" value="Save Category Sync" class="button-primary" /></p>';
 
} private 
function m4is_m57380(){
$m4is_j4692 =m4is_z6894::m4is_j6724();
 $m4is_n764 =m4is_z6894::m4is_a38740(false );
 echo '<div class="indented">';
 foreach($m4is_n764 as $m4is_h6528 ){
$m4is_p6356 ='';
 $m4is_z12867 =' field_selected ';
 if(!in_array($m4is_h6528['id'], $m4is_j4692 )){
$m4is_p6356 =' checked="checked" ';
 $m4is_z12867 ='';
 
}$m4is_p6356 =!in_array($m4is_h6528['id'], $m4is_j4692 )? ' checked="checked" ' : '';
 $m4is_p576 ='is4wp_category_' . $m4is_h6528['id'];
 printf('<p class="checkbox"><input value=%s %s type="checkbox" id="%s" name="is4wp_ignore_tag_categories[]">', $m4is_h6528['id'], $m4is_p6356, $m4is_p576 );
 printf('<label class="%s" for="%s" >%s</label></p>', $m4is_z12867, $m4is_p576, $m4is_h6528['name']);
 
}echo '</div>';
 
}private 
function m4is_n0239(){
echo <<<CSSBLOCK
		<style>
			p.checkbox { margin-bottom: 6px; display: inline-block; width: 250px; white-space: nowrap; overflow:hidden; }
			div.indented {margin-left: 15px;}
			label.field_selected { font-weight:bold; color:red; }
		</style>
		CSSBLOCK;
 
}
}

