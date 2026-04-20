<?php

/**
 * Copyright (c) 2021-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_k31562 {
private m4is_n5879 $m4is_i56968;
   static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
  $this->m4is_d4861();
 
} private 
function m4is_d4861(): void {
  add_action('esig_display_right_sidebar', [$this, 'm4is_b247'], 10 );
 add_action('esig_document_after_save', [$this, 'm4is_r95']);
 add_filter('memberium/modules/active/names', [$this, 'm4is_f6528'], 10, 1 );
 add_filter('memberium/enhanced_admin_scripts', [$this, 'm4is_w9064']);
 
} private 
function m4is_i702(): void {
  
}public 
function m4is_f6528($m4is_y634 =[]){
return array_merge($m4is_y634, ['WP E-Signature for Memberium']);
 
}public 
function m4is_l7182(){
return ['esign', ];
 
}public 
function m4is_b247($m4is_l91805 ='' ){
$m4is_i56968 =m4is_n5879::m4is_c26();
 $m4is_t19 =isset($_GET['document_id'])? $_GET['document_id']: 0;
 $m4is_i65 ="memberium_esignature_nonce_{$m4is_t19
}";
 $m4is_s15437 =$m4is_i56968->m4is_m52()->meta->get($m4is_t19, '_is4wp_esignature_tags' );
 $m4is_x25 =m4is_s6729::m4is_c26();
 $m4is_a27648 =wp_nonce_field(__FILE__, $m4is_i65, true, false);
 $m4is_d163 =$m4is_s15437 > '' ? $m4is_s15437 : '';
 $m4is_o498 ='';
 $m4is_o498 .= '<div class="postbox esign-form-panel">';
 $m4is_o498 .= '<h3 class="esig-section-title" style="padding-left:0">Memberium Integration</h3>';
 $m4is_o498 .= '<div class="esig-inside">';
 $m4is_o498 .= $m4is_a27648;
 $m4is_o498 .= '<label for="_is4wp_esignature_tags">Add Tag when signed:</label>';
 $m4is_o498 .= '<input value="' . $m4is_d163 . '" name="_is4wp_esignature_tags" class="multitaglist" style="width:95%; max-width:95%"><br /><br />';
 $m4is_o498 .= '</div>';
 $m4is_o498 .= '</div>';
 add_action('admin_footer', [$m4is_x25, 'm4is_n68517']);
 echo $m4is_o498;
 return $m4is_l91805;
 
} public 
function m4is_r95($m4is_b5028 ){
 if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE ){
return;
 
}$m4is_t19 =isset($m4is_b5028['document']->document_id)? $m4is_b5028['document']->document_id : 0;
 $m4is_i65 ="memberium_esignature_nonce_{$m4is_t19
}";
 if(empty($_POST[$m4is_i65])||!wp_verify_nonce($_POST[$m4is_i65], __FILE__ )){
return;
 
} $m4is_f69781 =['_is4wp_esignature_tags', ];
 $m4is_i56968 =m4is_n5879::m4is_c26();
 $m4is_u57018 =$m4is_i56968->m4is_m52();
 foreach($m4is_f69781 as $m4is_l9671 ){
$_POST[$m4is_l9671]=isset($_POST[$m4is_l9671])? $_POST[$m4is_l9671]: '';
 if(empty($_POST[$m4is_l9671])){
$m4is_u57018->elv_wp_esig->meta->delete($m4is_t19, $m4is_l9671);
 
}else{
$m4is_u57018->meta->add($m4is_t19, $m4is_l9671, $_POST[$m4is_l9671])or $m4is_u57018->meta->update($m4is_t19, $m4is_l9671, $_POST[$m4is_l9671]);
 
}
}
} public 
function m4is_w9064(array $m4is_f602): array {
$m4is_f602[]='admin_page_esign-edit-document';
 return $m4is_f602;
 
} 
}

