<?php

/**
 * Copyright (c) 2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83')||die();
  
class m4is_w375 {
private $api_key;
 private $api_secret;
 private $api_base ='https://api.zoom.us/v2/';
 private $api_count =0;
 
function __construct($m4is_l9671, $m4is_l21056 ){
$this->api_key =$m4is_l9671;
 $this->api_secret =$m4is_l21056;
 
} 
function m4is_a40($m4is_s7349, $m4is_y66291 =false){
$m4is_u897 =['headers' =>$this->m4is_t8616()];
 if(is_array($m4is_y66291)){
$m4is_u897 =wp_parse_args($m4is_y66291, $m4is_u897);
 
}$m4is_n548 =wp_remote_get($this->api_base.$m4is_s7349, $m4is_u897);
 $this->api_count++;
 return $this->m4is_c13($m4is_n548);
 
} 
function m4is_e1258($m4is_s7349, $m4is_s68 =false){
return $this->m4is_l3586($this->api_base.$m4is_s7349, 'POST', $m4is_s68 );
 
} 
function m4is_o5978($m4is_s7349, $m4is_s68 =false ){
return $this->m4is_l3586($this->api_base.$m4is_s7349, 'PATCH', $m4is_s68 );
 
} 
function m4is_z65($m4is_s7349, $m4is_s68 =false ){
return $this->m4is_l3586($this->api_base.$m4is_s7349, 'PUT', $m4is_s68 );
 
} 
function m4is_d869($m4is_s7349, $m4is_s68 =false ){
return $this->m4is_l3586($this->api_base.$m4is_s7349, 'DELETE', $m4is_s68 );
 
} 
function m4is_l3586($m4is_n6062, $m4is_s7349, $m4is_s68 =false ){
$m4is_u897 =['method' =>$m4is_s7349, 'headers' =>$this->m4is_t8616()];
 if($m4is_s68){
$m4is_u897['body']=(is_array($m4is_s68))? json_encode($m4is_s68): $m4is_s68;
 
}$m4is_n548 =wp_remote_request($m4is_n6062, $m4is_u897);
 $this->api_count++;
 return $this->m4is_c13($m4is_n548);
 
} 
function m4is_t8616(){
return ['Authorization' =>'Bearer ' . $this->m4is_n5426(), 'Content-Type' =>'application/json', 'Accept' =>'application/json', ];
 
} 
function m4is_n5426(){
$m4is_a873 =time()* 1000 - 30000;
 $m4is_f619 =json_encode(['typ' =>'JWT', 'alg' =>'HS256']);
 $m4is_z761 =$this->m4is_h6167($m4is_f619);
 $m4is_x50 =json_encode(['iss' =>$this->api_key, 'exp' =>$m4is_a873]);
 $m4is_v95 =$this->m4is_h6167($m4is_x50);
 $m4is_o31859 =hash_hmac('sha256', $m4is_z761 . "." . $m4is_v95, $this->api_secret, true);
 $m4is_l95 =$this->m4is_h6167($m4is_o31859);
 $m4is_b84367 =$m4is_z761 . "." . $m4is_v95 . "." . $m4is_l95;
 return $m4is_b84367;
 
}
function m4is_h6167($m4is_l91805){
$m4is_a3250 =base64_encode($m4is_l91805);
 if($m4is_a3250 === false){
return false;
 
}$m4is_n6062 =strtr($m4is_a3250, '+/', '-_');
 return rtrim($m4is_n6062, '=');
 
} 
function m4is_i346($m4is_d07693, $m4is_m7560 ){
$m4is_a873 =time()* 1000 - 30000;
 $m4is_l91805 =base64_encode($this->api_key.$m4is_d07693.$m4is_a873.$m4is_m7560);
 $m4is_j54781 =hash_hmac('sha256', $m4is_l91805, $this->api_secret, true);
 $m4is_j54781 =base64_encode($m4is_j54781);
 $m4is_o31859 ="{$this->api_key
}.{$m4is_d07693
}.{$m4is_a873
}.{$m4is_m7560
}.{$m4is_j54781
}";
 return rtrim(strtr(base64_encode($m4is_o31859), '+/', '-_'), '=');
 
} 
function m4is_c13($m4is_n548 ){
if(is_wp_error($m4is_n548)){
return $m4is_n548;
 
}else{
return json_decode(wp_remote_retrieve_body($m4is_n548));
 
}
}
}

