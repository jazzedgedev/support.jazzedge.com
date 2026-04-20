<?php
  
class m4is_u30 extends TCB\ConditionalDisplay\Field {
 public static 
function get_entity(){
return 'memberium';
 
} public static 
function get_key(){
return 'memberium_tag';
 
} public static 
function get_label(){
return 'Has Any Tags';
 
} static 
function get_conditions(){
return ['autocomplete' ];
 
}static 
function is_boolean(){
return false;
 
} public 
function get_value($m4is_k824 ){
return isset($m4is_k824['memb_user']['tags'])? array_filter(explode(',', $m4is_k824['memb_user']['tags'])): [];
 
} public static 
function get_options($m4is_p9640 =[], $m4is_k259 ='' ){
$m4is_l9321 =m4is_k865::m4is_o172($m4is_k259 );
 if(!empty($m4is_p9640 )){
$m4is_p3152 =array_filter($m4is_l9321, function($m4is_p786 )use ($m4is_p9640 ){
return in_array($m4is_p786->id, $m4is_p9640 );
 
});
 $m4is_l9321 =$m4is_p3152;
 
}$m4is_w89066 =[];
 foreach($m4is_l9321 as $m4is_p786 ){
$m4is_w89066[]=['value' =>(string) $m4is_p786->id, 'label' =>sprintf("%s (%s)", $m4is_p786->name, $m4is_p786->id ), ];
 
}return $m4is_w89066;
 
} public static 
function get_autocomplete_placeholder(){
return 'Search Tags';
 
} public static 
function get_display_order(){
return 10;
 
}
}

