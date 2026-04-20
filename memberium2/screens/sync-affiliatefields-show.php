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
 m4is_j85::m4is_z95();
 
class m4is_j85 {
private $m4is_r1546, $m4is_a89 =[], $m4is_v45136 =[], $m4is_g7928 =[];
 static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_s3572();
 $this->m4is_h269();
 $this->m4is_k56();
 
}private 
function m4is_h269(){
$this->m4is_a89 =m4is_c69807::m4is_f5248('Affiliate', false );
  $this->m4is_v45136 =$this->m4is_d69();
 $this->m4is_g7928 =$this->m4is_m4296();
 
}private 
function m4is_e286(){
global $wpdb;
 if(!empty($m4is_d05921 )){
$m4is_r9613 =$this->m4is_r1546->m4is_i76('appname');
 $m4is_v2613 ='DELETE FROM `' . m4is_z13097::m4is_o71(). '` WHERE fieldname in (\'' . $m4is_d05921 . '\') AND `appname` = "' . $m4is_r9613 . '" ';
 $wpdb->query($m4is_v2613 );
 
}
}private 
function m4is_s3572(){
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}$m4is_v45136 =isset($_POST['ignore_affiliate_fields'])&&is_array($_POST['ignore_affiliate_fields'])? implode(',', $_POST['ignore_affiliate_fields']): '';
 $this->m4is_r1546->m4is_d64918($m4is_v45136, 'settings', 'ignore_affiliate_fields');
 m4is_s695::m4is_r2903();
 m4is_h65::m4is_z896('Affiliate Fields Ignore List Updated.');
 
}private 
function m4is_k56(){
echo '<p>Please read our online help BEFORE changing these options.</p>';
  echo '<p>Affiliate fields marked in <strong style="color:red;">BOLD RED</strong> are not synced. ';
 echo 'We recommend blocking as many fields as possible to speed up performance, and reduce database usage. ';
 echo 'Be careful not to block fields you use.</p>';
 echo '<p>Please contact support@memberium.com if you have questions about this function.</p>';
  $this->m4is_z18357();
 echo '<p><input type="submit" name="save" value="Save Affiliate Field Sync" class="button-primary" /></p>';
 
}private 
function m4is_z18357(){
$m4is_x562 =m4is_c69807::m4is_f5248('Affiliate', false );
 $m4is_v45136 =$this->m4is_d69();
 $m4is_g7928 =$this->m4is_m4296();
 $m4is_g7928 =array_flip($m4is_g7928 );
 sort($m4is_x562 );
 echo '<div class="indented">';
 foreach ($m4is_x562 as $m4is_r637 ){
$m4is_p6356 ='';
 $m4is_s6347 ='';
 $m4is_z12867 ='';
 $m4is_p576 ='ignore_affiliate_fields_' . $m4is_r637;
 if(in_array($m4is_r637, $m4is_v45136 )){
$m4is_p6356 =' checked="checked" ';
 $m4is_z12867 =' field_selected ';
 
}if(!in_array($m4is_r637, $m4is_g7928 )){
printf('<p class="checkbox"><input value="%s" %s type="checkbox" id="%s" name="ignore_affiliate_fields[]">', $m4is_r637, $m4is_p6356, $m4is_p576 );
 printf('<label for="%s" class="%s">%s</label></p>', $m4is_p576, $m4is_z12867, $m4is_r637 );
 
}
}echo '</div>';
 
}private 
function m4is_n2656(){
echo '<select multiple="multiple" id="ignore_affiliate_fields" name="ignore_affiliate_fields[]" size="20" style="width:200px;">';
 foreach ($this->m4is_a89 as $m4is_q523){
if(!array_key_exists($m4is_q523, $this->m4is_g7928 )){
$m4is_a437 =in_array($m4is_q523, $this->m4is_v45136 )? ' selected="selected" ' : '';
 printf('<option value="%s" %s>%s</option>', $m4is_q523, $m4is_a437, $m4is_q523 );
 
}
}echo '</select>';
 
}private 
function m4is_d69(): array {
return array_filter(explode(',', $this->m4is_r1546->m4is_j498('settings', 'ignore_affiliate_fields', '' )));
 
}private 
function m4is_m4296(): array {
$m4is_g7928 =[];
 $m4is_g7928[]='AffCode';
 $m4is_g7928[]='AffName';
 $m4is_g7928[]='ContactId';
 $m4is_g7928[]='Id';
 $m4is_g7928[]='ParentId';
 $m4is_g7928[]='Password';
 $m4is_g7928[]='Status';
 return array_flip($m4is_g7928 );
 
}
}

