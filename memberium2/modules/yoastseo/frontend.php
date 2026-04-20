<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_o648 {
private m4is_r83 $m4is_r1546;
 static 
function m4is_c26(){
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
}private 
function m4is_o31(){
if(class_exists('Yoast_GA_Options')){

}add_filter('yoast-ga-custom-vars', [$this, 'm4is_v68065' ], 10, 2 );
 
} 
function m4is_v68065($m4is_d294, $m4is_q753){
m4is_j586::m4is_x7134();
 $m4is_k824 =$this->m4is_r1546->m4is_x66();
 $m4is_m26193 =(array)$this->m4is_r1546->m4is_j498('ga_customvars' );
 if(!is_array($m4is_m26193)){
return;
 
}foreach ($m4is_m26193 as $m4is_c2340){
unset($m4is_z163);
 unset($m4is_r637);
 switch ($m4is_c2340['variable']){
case '!system.membership_level': $m4is_z163 =isset($m4is_k824['memb_user']['membership_level'])? $m4is_k824['memb_user']['membership_level']: '';
 break;
 case '!system.membership_name': $m4is_z163 =isset($m4is_k824['memb_user']['membership_names'])? $m4is_k824['memb_user']['membership_names']: '';
 break;
 case '!system.remote_auth': $m4is_z163 =isset($m4is_k824['memb_user']['remote_auth'])? $m4is_k824['memb_user']['remote_auth']: '';
 break;
 case '!system.source': $m4is_z163 =isset($m4is_k824['memb_user']['source'])? $m4is_k824['memb_user']['source']: '';
 break;
 
}if(substr($m4is_c2340['variable'], 0, 9)== '!contact.'){
$m4is_r637 =strtolower(substr($m4is_c2340['variable'], 9));
 
}if(substr($m4is_c2340['variable'], 0, 11)== '!affiliate.'){
$m4is_r637 ='affiliate.' . strtolower(substr($m4is_c2340['variable'], 11 ));
 
}if(!empty($m4is_r637 )){
$m4is_f087 =m4is_r83::m4is_c26()->m4is_x66();
 $m4is_z163 =m4is_q82::m4is_k660($m4is_f087, 'contact', $m4is_r637, '' );
 
}$m4is_d294[]="'_setCustomVar'," . $m4is_q753 . ",'" . addslashes($m4is_c2340['name']). "','" . addslashes($m4is_z163). "', 3";
 $m4is_q753++;
 
}return $m4is_d294;
 
}
}

