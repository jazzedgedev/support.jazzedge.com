<?php

/**
 * Copyright (C) 2018-2024 David Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_s6729' )||die();
 final 
class m4is_s31 {
private $tabs =[];
 private $headers =[];
 private $default ='';
 private $current_tab ='';
 
function __construct(){
$this->tabs =[];
 $this->headers =[];
 $this->default ='';
 $this->current_tab ='';
 
} 
function m4is_h376(string $m4is_d60695 ='', string $m4is_w42 ='', string $m4is_j09 ='', $m4is_s7349 ='', string $m4is_u610 =''){
$this->tabs[$m4is_j09]=['icon' =>$m4is_d60695, 'label' =>$m4is_w42, 'slug' =>strtolower(trim($m4is_j09)), 'method' =>$m4is_s7349, 'post' =>$m4is_u610, ];
 if(count($this->tabs)== 1){
$this->m4is_j698($m4is_j09);
 
}
} 
function m4is_y568(array $m4is_m7964){
$this->tabs =$m4is_m7964;
 
}
function m4is_b5641(): array {
return $this->tabs;
 
}
function m4is_j40293(string $m4is_d926 =''){
$this->headers[]=$m4is_d926;
 
}
function m4is_j698(string $m4is_j09 =''): bool {
$slug =strtolower(trim($m4is_j09));
 if(array_key_exists($m4is_j09, $this->tabs)){
$this->default =$m4is_j09;
 return true;
 
}return false;
 
}
function m4is_s96(){
if(empty($this->tabs )){
return;
 
}$m4is_i78603 =$this->m4is_o08();
 if($this->tabs[$m4is_i78603]['post']){
$this->m4is_e584($this->tabs[$m4is_i78603]['post']);
 
}m4is_h65::m4is_n25();
 echo '<div class="wrap about-wrap memberium">';
 foreach($this->headers as $m4is_f619){
echo $this->m4is_e584($m4is_f619);
 
}echo '</div>';
 echo '<div class="wrap">';
  echo '<h4 class="nav-tab-wrapper">';
 foreach ($this->tabs as $m4is_j09 =>$m4is_b51936){
$m4is_s6347 ='nav-tab';
 $m4is_s6347 .= ($m4is_b51936['slug']== $m4is_i78603)? ' nav-tab-active' : '';
 if($m4is_b51936['slug']== $m4is_i78603){
echo "<span class='{$m4is_s6347
}'><i class='{$m4is_b51936['icon']
}'></i> {$m4is_b51936['label']
}</span>";
 
}else{
echo "<a class='{$m4is_s6347
}' href='?page={$_GET['page']
}&tab={$m4is_b51936['slug']
}'><i class='{$m4is_b51936['icon']
}'></i> {$m4is_b51936['label']
}</a>";
 
}
}echo '</h4>';
 echo '<div class="memberium_tabcontent" style="margin-top:10px;">';
 echo $this->m4is_e584($this->tabs[$m4is_i78603]['method']);
 echo '</div>';
 
}
function m4is_o08(): string {
$this->current_tab =isset($_GET['tab'])? strtolower($_GET['tab']): $this->default;
 if(!array_key_exists($this->current_tab, $this->tabs)){
$this->current_tab =$this->default;
 
}return $this->current_tab;
 
} private 
function m4is_e584($m4is_d926 =false){
if(!empty($m4is_d926 )){
if(is_array($m4is_d926 )){
if(method_exists($m4is_d926[0], $m4is_d926[1])){
return call_user_func_array($m4is_d926, []);
 
}else{
echo '<p><span style="font-weight:bold;color:red;">Error:  </span>  ', $m4is_d926[0], '->', $m4is_d926[1], ' not found</p>';
 
}
}elseif(is_string($m4is_d926)){
if(function_exists($m4is_d926)){
return call_user_func($m4is_d926);
 
}elseif(file_exists($m4is_d926)){
include_once $m4is_d926;
 
}else{
echo $m4is_d926;
 
}
}
}
} 
}

