<?php
  
class m4is_i65086 extends TCB\ConditionalDisplay\Field {
 public static 
function get_entity(){
return 'memberium';
 
} public static 
function get_key(){
return 'memberium_membership';
 
} public static 
function get_label(){
return 'Has Any Memberships';
 
} static 
function get_conditions(){
return ['autocomplete' ];
 
} public 
function get_value($m4is_k824 ){
return isset($m4is_k824['memb_user']['membership_tags'])? array_filter(explode(',', $m4is_k824['memb_user']['membership_tags'])): [];
 
} public static 
function get_options($m4is_p9640 =[], $m4is_k259 ='' ){
$m4is_m96240 =m4is_k487::m4is_c26()->m4is_m6617($m4is_k259 );
 if(!empty($m4is_p9640 )){
$m4is_t6842 =array_filter($m4is_m96240, function($m4is_d07693 )use ($m4is_p9640 ){
return in_array($m4is_d07693, $m4is_p9640 );
 
}, ARRAY_FILTER_USE_KEY );
 $m4is_m96240 =$m4is_t6842;
 
}$m4is_w89066 =[];
 foreach($m4is_m96240 as $m4is_d07693 =>$m4is_w64 ){
$m4is_w89066[]=['value' =>(string) $m4is_d07693, 'label' =>sprintf("%s (%s)", $m4is_w64, $m4is_d07693 ), ];
 
}return $m4is_w89066;
 
} public static 
function get_autocomplete_placeholder(){
return 'Search Tags';
 
} public static 
function get_display_order(){
return 10;
 
}
}

