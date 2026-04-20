<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 
/**
 * Handles Memberium's WooCommerce-related frontend functionality.
 *
 * This class integrates Memberium's custom logic with WooCommerce, providing shortcodes,
 * frontend actions, and filters that extend or modify the default WooCommerce behavior
 * for logged-in users, product visibility, and purchase handling.
 *
 * It implements a singleton pattern to ensure only one instance of the class is active during execution.
 *
 * @copyright  2012-2024 David J Bullock
 * @package    Memberium\WooCommerce
 * @subpackage Frontend
 * @since      1.0.0
 * @final
 */


 final 
class m4is_v46135 {
 private m4is_r83 $m4is_r1546;
  public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_i702();
 $this->m4is_d4861();
 
}    private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
} private 
function m4is_d4861(): void {
add_action('init', [$this, 'm4is_s2469'], 1 );
 add_action('woocommerce_login_form', [$this, 'm4is_n466']);
 add_action('template_redirect', [$this, 'm4is_y57'], 9, 0 );
 add_action('wp_loaded', [$this, 'm4is_u5823'], 9, 0 );
 add_filter('woocommerce_related_products', [$this, 'm4is_j15827'], 20, 3 );
 add_filter('woocommerce_checkout_fields', [$this, 'm4is_w549'], 100, 1 );
 
}    
function m4is_s2469(){
$m4is_z2136 =m4is_r83::m4is_c26()->m4is_l9657();
 $m4is_l9321['nested']=['memb_has_in_cart' =>[$m4is_z2136, 'm4is_e501'], 'memb_has_purchased_product' =>[$m4is_z2136, 'm4is_u497'], 'memb_is_cart_empty' =>[$m4is_z2136, 'm4is_u1632'], ];
  if(false ){
$m4is_l9321['standard']=[];
 foreach($m4is_l9321['standard']as $m4is_p786 =>$m4is_j86631 ){
add_shortcode($m4is_p786, [$this, $m4is_j86631]);
 
}
}foreach($m4is_l9321['nested']as $m4is_p786 =>$m4is_j86631 ){
add_shortcode($m4is_p786, [$this, $m4is_j86631[1]]);
  for ($i =1;
 $i < (int) $m4is_j86631[0];
 $i++ ){
add_shortcode($m4is_p786 . $i, [$this, $m4is_j86631[1]]);
 
}
}
} public 
function m4is_u5823(): void {
if(empty($_POST['woocommerce-login-nonce'])||empty($_POST['password'])||empty($_POST['username'])){
return;
 
}$m4is_f36 =(bool) m4is_r83::m4is_c26()->m4is_j498('settings', 'persistent_login', 0 );
 if($m4is_f36 ){
$_POST['rememberme']='forever';
 
}
} public 
function m4is_w549($m4is_a89 ){
if(!is_user_logged_in()){
return $m4is_a89;
 
}if(isset($m4is_a89['billing']['billing_email'])){
$m4is_a89['billing']['billing_email']['required']=0;
 
}if(wp_doing_ajax()){
return $m4is_a89;
 
}if($this->m4is_r1546->m4is_x29046()){
return $m4is_a89;
 
}echo <<<HTMLBLOCK
			<style>
				#billing_email_field {visibility:hidden;}
				#billing_email_field label span {visibility:hidden;}
			</style>
		HTMLBLOCK;
 return $m4is_a89;
 
} public 
function m4is_u497($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
m4is_j586::m4is_x7134();
 $m4is_y642 =['product_id' =>'', 'txtfmt' =>'', 'capture' =>'', ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_y4862 =array_filter(explode(',', $m4is_l62046['product_id']));
 $m4is_f087 =$this->m4is_r1546->m4is_x66();
 $m4is_t265 =false;
 foreach($m4is_y4862 as $m4is_o6480 ){
$m4is_t265 =$m4is_t265 ||wc_customer_bought_product(null, $m4is_f087, $m4is_o6480);
 
}$m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_t265);
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
} public 
function m4is_e501($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
m4is_j586::m4is_x7134();
 $m4is_y642 =['product_id' =>'', 'txtfmt' =>'', 'capture' =>'', ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_t265 =false;
 $m4is_y4862 =explode(',', $m4is_l62046['product_id']);
 $m4is_x82 =WC()->cart->get_cart();
 foreach($m4is_x82 as $m4is_o015 =>$m4is_k72 ){
$m4is_t265 =$m4is_t265 ||in_array($m4is_k72['product_id'], $m4is_y4862 );
 
}$m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_t265);
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
} public 
function m4is_u1632($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
m4is_j586::m4is_x7134();
 $m4is_x82 =WC()->cart->get_cart();
 $m4is_t265 =empty($m4is_x82 );
 $m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_t265 );
 return m4is_f61::m4is_u150(false, $m4is_o498, '', '' );
 
} public 
function m4is_j15827(array $m4is_h59, int $m4is_o6480, array $args ): array {
$m4is_f683 =m4is_f58::m4is_c26();
 foreach($m4is_h59 as $m4is_l9671 =>$m4is_b4068 ){
if(!is_int($m4is_b4068 )){
continue;
 
}if(!$m4is_f683->m4is_x72168($m4is_b4068 )){
unset($m4is_h59[$m4is_l9671]);
 
}
}return array_values($m4is_h59 );
 
}    public 
function m4is_n466(){
echo m4is_l5841::m4is_u06('', ['display' =>true]);
 
}    public 
function m4is_y57(): void {
if(!is_wc_endpoint_url('order-received' )){
return;
 
}$m4is_c6016 =absint(get_query_var('order-received' ));
 if(!$m4is_c6016 ){
return;
 
}$m4is_y5760 =wc_get_order($m4is_c6016 );
 if(!$m4is_y5760 ){
return;
 
}$m4is_a3165 =$m4is_y5760->get_status();
 $valid_states =apply_filters('memberium/woocommerce/checkout/redirect/status', ['completed', 'processing', 'on-hold']);
 if(!in_array($m4is_a3165, $valid_states, true )){
return;
 
}foreach ($m4is_y5760->get_items()as $m4is_c4069 ){
$m4is_o6480 =$m4is_c4069->get_product_id();
 $m4is_f56 =get_post_meta($m4is_o6480, m4is_d12540::M4IS_X267, true );
 if(!empty($m4is_f56 )&&is_string($m4is_f56 )){
wp_safe_redirect(esc_url_raw($m4is_f56 ), 302, 'Memberium WooCommerce Purchase Redirect' );
 exit;
 
}
}
} 
}

