<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_c34 {
private $metas;
 private m4is_r83 $m4is_r1546;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_d4861();
 
}private 
function m4is_d4861(): void {
add_action('admin_init', [$this, 'm4is_t1732']);
 add_filter('memberium/modules/active/names', [$this, 'm4is_f809'], 10, 1 );
 add_filter('woocommerce_helper_suppress_admin_notices', '__return_true' );
 
}private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->metas =['_memberium_canc_tag', '_memberium_main_tag', '_memberium_payf_tag', '_memberium_susp_tag', m4is_d12540::M4IS_X267, ];
 
} private 
function m4is_x80245(): array {
return $this->metas;
 
} public 
function m4is_f809(array $m4is_y634 ): array {
return array_merge($m4is_y634, ['WooCommerce for Memberium Support' ]);
 
} public 
function m4is_t1732(): void {
add_meta_box('memberium\woocommerce\actions', 'Memberium WooCommerce', [$this, 'm4is_q92603'], 'product', 'side' );
 add_action('save_post_product', [$this, 'm4is_x9136']);
 
} public 
function m4is_q92603(): void {
global $post;
 $m4is_i38 =(int) get_post_meta($post->ID, '_memberium_main_tag', true );
 $m4is_t6204 =(int) get_post_meta($post->ID, '_memberium_canc_tag', true );
 $m4is_x1632 =(int) get_post_meta($post->ID, '_memberium_payf_tag', true );
 $m4is_e3854 =(int) get_post_meta($post->ID, '_memberium_susp_tag', true );
 $m4is_q37596 =trim((string) get_post_meta($post->ID, '_memberium_checkout_redirect', true ));
 $m4is_c36941 =__('Checkout Redirect URL', 'memberium');
 $m4is_j1825 ='Leave blank for WooCommerce checkout';
 $m4is_j1825 =($m4is_q37596 > '' ? 'Default Redirect to ' . $m4is_q37596 : $m4is_j1825);
 $m4is_w63647 =__("Access Tag", 'memberium' );
 $m4is_k686 =__("Cancel Tag", 'memberium' );
 $m4is_o019 =__("Payment Failure Tag", 'memberium' );
 $m4is_s59036 =__("Suspend/On-Hold Tag", 'memberium' );
 echo <<<HTMLBLOCK
			<label for="_memberium_main_tag">{$m4is_w63647
}:</label>
			<input name="_memberium_main_tag" value="{$m4is_i38
}" class="taglistdropdown" style="width:100%; max-width:100%"><br />
			<br />

			<label for="_memberium_canc_tag">{$m4is_k686
}:</label>
			<input name="_memberium_canc_tag" value="{$m4is_t6204
}" class="taglistdropdown" style="width:100%; max-width:100%"><br />
			<br />

			<label for="_memberium_payf_tag">{$m4is_o019
}:</label>
			<input name="_memberium_payf_tag" value="{$m4is_x1632
}" class="taglistdropdown" style="width:100%; max-width:100%"><br />
			<br />

			<label for="_memberium_susp_tag">{$m4is_s59036
}:</label>
			<input name="_memberium_susp_tag" value="{$m4is_e3854
}" class="taglistdropdown" style="width:100%; max-width:100%"><br />
			<br />

			<label for="_memberium_checkout_redirect">{$m4is_c36941
}</label>
			<input type="text" id="_memberium_checkout_redirect" name="_memberium_checkout_redirect" style="width:100%; max-width:100%" rows="1" value="{$m4is_q37596
}" placeholder="{$m4is_j1825
}">
			<br />
		HTMLBLOCK;
 
} public 
function m4is_x9136(int $m4is_b4068 ): void {
 if(defined('DOING_AUTOSAVE' )&&DOING_AUTOSAVE ){
return;
 
}if(!current_user_can('edit_posts', $m4is_b4068 )){
return;
 
}if(!$m4is_b4068 ){
return;
 
}$m4is_x07 =$this->m4is_x80245();
 foreach($m4is_x07 as $m4is_h35 ){
if(isset($_POST[$m4is_h35])){
$m4is_v586 =trim($_POST[$m4is_h35], ',' );
 empty($m4is_v586 )? delete_post_meta($m4is_b4068, $m4is_h35 ): update_post_meta($m4is_b4068, $m4is_h35, $m4is_v586 );
 
}
}
}
}

