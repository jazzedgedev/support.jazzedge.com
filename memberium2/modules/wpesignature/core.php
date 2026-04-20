<?php

/**
 * Copyright (c) 2021-2025 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_n5879 {
 public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
if(!class_exists('WP_E_Digital_Signature' )){
error_log('Memberium: [error] WP E-Signature WP_E_Digital_Signature is missing. The WP E-Signature integration is disabled.' );
 return;
 
}if(!function_exists('WP_E_Sig' )){
error_log('Memberium: [error] WP E-Signature WP_E_Sig function is missing. The WP E-Signature integration is disabled.' );
 return;
 
}$this->m4is_d4861();
 if(is_admin()){
if(include_once(__DIR__ . '/admin.php' )){
m4is_k31562::m4is_c26();
 
}else{
error_log('Memberium: [error] Failed to include admin functionality for WP E-Signature integration.' );
 
}
}
} private 
function m4is_d4861(): void {
add_action('esig_document_basic_closing', [$this, 'm4is_d86465'], 10, 1 );
 add_action('esig_signature_saved', [$this, 'm4is_d86465'], 10, 1 );
 
} public 
function m4is_m52(){
return WP_E_Sig();
 
} public 
function m4is_d86465(array $m4is_o31859 ): void {
$m4is_t19 =$m4is_o31859['sad_doc_id']?? 0;
 if(!$m4is_t19 ){
return;
  
} $m4is_s15437 =$this->m4is_m52()->meta->get($m4is_t19, '_is4wp_esignature_tags' );
 if(empty($m4is_s15437 )){
return;
 
}$m4is_h21895 =0;
 $m4is_f087 =$m4is_o31859['recipient']->wp_user_id ?? 0;
 if(empty($m4is_f087 )){
$m4is_q12678 =$m4is_o31859['recipient']->user_email ?? '';
 $m4is_h21895 =m4is_r83::m4is_c26()->m4is_o70($m4is_q12678 );
 
}else{
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
 
}m4is_r83::m4is_c26()->m4is_k98($m4is_s15437, $m4is_h21895 );
 
}
}

