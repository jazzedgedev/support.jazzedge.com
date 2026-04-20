<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 ?>
<style>
	p.checkbox { margin-bottom: 6px; display: inline-block; width: 200px; white-space: nowrap; overflow:hidden; }
	div.indented {margin-left: 15px;}
	label.field_selected { font-weight:bold; color:red; }
</style>
<?php
 m4is_l7256::m4is_z95();
 final 
class m4is_l7256 {
private $m4is_r1546;
 static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_s3572();
 $this->m4is_d628();
 
}private 
function m4is_s3572(){
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}if(!isset($_POST['formtype'])||$_POST['formtype']!== 'contactfields' ){
return;
 
}$m4is_j108 =isset($_POST['is4wp_ignore_contact_fields'])? array_filter($_POST['is4wp_ignore_contact_fields']): [];
 $m4is_d19720 =is_array($m4is_j108 )? implode(',', $m4is_j108 ): '';
 $this->m4is_r1546->m4is_d64918($m4is_d19720, 'settings', 'ignore_contact_fields' );
 m4is_s695::m4is_r2903();
 m4is_p40::m4is_u09816();
 m4is_h65::m4is_z896('Contact Fields Ignore List Updated.' );
 
}
function m4is_d628(){
echo '<p>Please read our online help BEFORE changing these options.</p>';
  echo '<p>Contact fields marked in <strong style="color:red;">BOLD RED</strong> are not synced. ';
 echo 'We recommend blocking as many fields as possible to speed up performance, and reduce database usage. ';
 echo 'Be careful not to block fields you use.</p>';
 echo '<p>Please contact support@memberium.com if you have questions about this function.</p>';
  $this->m4is_p71();
 echo '<p><input type="submit" name="save" value="Save Contact Field Sync" class="button-primary" /></p>';
 
}private 
function m4is_p71(){
$m4is_y368 =m4is_c69807::m4is_f5248('Contact', false );
 $m4is_g7928 =array_filter(explode(',', $this->m4is_r1546->m4is_j498('sync', 'required_fields')['Contact']));
 $m4is_n72966 =array_filter(explode(',', $this->m4is_r1546->m4is_j498('settings', 'ignore_contact_fields' )));
 $m4is_g7928[]=$this->m4is_r1546->m4is_j498('settings', 'username_field');
 $m4is_g7928[]=$this->m4is_r1546->m4is_j498('settings', 'password_field');
 array_flip($m4is_g7928 );
 sort($m4is_y368);
 echo '<div class="indented">';
 foreach ($m4is_y368 as $m4is_f7628 ){
$m4is_p6356 ='';
 $m4is_s6347 ='';
 $m4is_z12867 ='';
 $m4is_p576 ='is4wp_ignore_contact_fields_' . $m4is_f7628;
 if(in_array($m4is_f7628, $m4is_n72966 )){
$m4is_p6356 =' checked="checked" ';
 $m4is_z12867 =' field_selected ';
 
}if(!in_array($m4is_f7628, $m4is_g7928 )){
printf('<p class="checkbox"><input value="%s" %s type="checkbox" id="%s" name="is4wp_ignore_contact_fields[]">', $m4is_f7628, $m4is_p6356, $m4is_p576 );
 printf('<label for="%s" class="%s">%s</label></p>', $m4is_p576, $m4is_z12867, $m4is_f7628 );
 
}
}echo '</div>';
 
}private 
function m4is_d6946(){
$m4is_e827 ='';
 $m4is_y368 =m4is_c69807::m4is_f5248('Contact', false );
 $m4is_g7928 =array_filter(explode(',', $this->m4is_r1546->m4is_j498('sync', 'required_fields')['Contact']));
 $m4is_s86934 =array_filter(explode(',', $this->m4is_r1546->m4is_j498('settings', 'ignore_contact_fields' )));
 $m4is_g7928[]=$this->m4is_r1546->m4is_j498('settings', 'username_field' );
 $m4is_g7928[]=$this->m4is_r1546->m4is_j498('settings', 'password_field' );
 array_flip((array)$m4is_g7928 );
 echo '<select multiple="multiple" id="is4wp_ignore_contact_fields" name="is4wp_ignore_contact_fields[]" size="20" style="width:200px;">>';
 foreach ($m4is_y368 as $m4is_f7628 ){
$m4is_a437 =in_array($m4is_f7628, $m4is_s86934 )? ' selected="selected" ' : '' ;
 if(!in_array($m4is_f7628, $m4is_g7928 )){
printf('<option value="%s" %s>%s</option>', $m4is_f7628, $m4is_a437, $m4is_f7628 );
 
}
}echo '</select>';
 
}
}

