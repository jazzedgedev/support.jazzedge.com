<?php

/**
 * Copyright (c) 2017-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_s6729' )||die();
 ?>
<style>
	.columns {
		float:left;
		width:30%;
		display:inline-block;
		text-align:left;
		margin-right:25px;
		min-width:300px;
	}
</style>
<?php
 m4is_c1835::m4is_o719();
 final 
class m4is_c1835 {
private $m4is_c71685;
 static 
function m4is_o719(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_c71685 =m4is_s6729::m4is_c26()->m4is_r86();
 m4is_r83::m4is_c26()->m4is_s965('view_integrations' );
 $this->m4is_k186();
 
}private 
function m4is_k186(){
echo '<div style="width:100%;border-color:#000;">';
 echo '<div class="columns">';
 echo '<h3>Active Modules</h3>';
 echo '<p class="indented">';
 $this->m4is_t61736();
 echo '</p>';
 echo '<h3>Activated Integrations</h3>';
 echo '<p class="indented">';
 $this->m4is_n61074();
 echo '</p>';
 echo '</div>';
 echo '<div class="columns">';
 echo '<h3>Potential conflicts</h3>';
 echo '<p class="indented">';
 $this->m4is_m5093();
 echo '</p>';
  echo '</div>';
 echo '</div>';
 echo '<p></p>';
 
}private 
function m4is_t61736(){
$m4is_y634 =apply_filters('memberium/modules/active/names', []);
 if(!empty($m4is_y634 )){
sort($m4is_y634 );
 foreach($m4is_y634 as $m4is_k52736 ){
printf('<strong class="goodplugin">%s</strong><br>', $m4is_k52736 );
 
}
}
}private 
function m4is_n61074(){
$m4is_t265 =!empty($this->m4is_c71685['detected'])&&is_array($this->m4is_c71685['detected']);
 if($m4is_t265 ){
foreach ($this->m4is_c71685['detected']as $m4is_s63 ){
$m4is_k036 =isset($m4is_s63['help'])? m4is_h65::m4is_o64($m4is_s63['help']): '';
 printf('<span class="%splugin">%s</span> %s<br />', $m4is_s63['class'], $m4is_s63['name'], $m4is_k036 );
 
}
}else{
echo '<span>None</span><br />';
 
}
}private 
function m4is_m5093(){
$m4is_t265 =!empty($this->m4is_c71685['problem'])&&is_array($this->m4is_c71685['problem']);
 if($m4is_t265 ){
foreach ($this->m4is_c71685['problem']as $m4is_s63 ){
$m4is_k036 =empty($m4is_s63['help'])? '' : m4is_h65::m4is_o64($m4is_s63['help']);
 printf('<span class="badplugin %splugin">%s</span> %s<br />', $m4is_s63['class'], $m4is_s63['name'], $m4is_k036 );
 
}
}else{
echo 'No known conflicts detected.<br />';
 
}
}private 
function m4is_i1965(){
$m4is_t265 =!empty($this->m4is_c71685['available'])&&is_array($this->m4is_c71685['available']);
 if($m4is_t265 ){
foreach ($this->m4is_c71685['available']as $m4is_s63 ){
$m4is_k036 =empty($m4is_s63['help'])? '' : m4is_h65::m4is_o64($m4is_s63['help']);
 printf('%s %s<br />', $m4is_s63['name'], $m4is_k036 );
 
}
}else{
echo 'No additional available integrations.<br>';
 
}
}
}

