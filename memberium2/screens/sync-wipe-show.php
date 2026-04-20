<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_s6729' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_w12::m4is_z95();
 final 
class m4is_w12 {
static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_c82165();
 $this->m4is_i71346();
 
}private 
function m4is_i71346(){
$m4is_i796 =wp_nonce_field('memberium_sync_wipe', 'memberium_sync_wipe_nonce', true, false );
 echo <<<HTMLBLOCK
		<style>
			.red-warning {
				margin:50px;
				padding:50px;
				background-color:red;
				color:white;
				border-radius:25px;
				font-size:16px !important;
			}
			.warning-text {
				font-size:16px !important;
				font-weight: bold;
			}
		</style>
		<form method="post">
			{$m4is_i796
}
			<div class="red-warning">
			<p class="warning-text">
				Click the button below to delete all Memberium data from the database.
				This will remove all Memberium data from the database, including all settings, custom fields, and tags.
			</p>
			<p>
				<input type="text" placeholder="Type 'ETERNAL PAIN' to confirm" name="wipe_confirm" class="regular-text" />
			</p>
			<p class="warning-text">
				This action cannot be undone.
			</p>
			<p class="warning-text">
				<input type="submit" name="wipe" value="Wipe Memberium Data" class="button-primary" />
			</p>
			</div>
		</form>
		HTMLBLOCK;
 
}private 
function m4is_c82165(){
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}$m4is_u0789 =defined('MEMBERIUM_HOME' )? constant('MEMBERIUM_HOME' ): '';
 deactivate_plugins(plugin_basename($m4is_u0789 ));
 
}private 
function m4is_p415(){
global $wpdb;
 $m4is_f087 =get_current_user_id();
 file_put_contents(ABSPATH . '.maintenance', '<?php $upgrading = time();' );
 $m4is_x7953 =['i2sdk%', 'memberium%', ];
 foreach($m4is_x7953 as $m4is_j56697 ){
$m4is_q78234 =$wpdb->esc_like($m4is_j56697 );
 $m4is_v2613 =$wpdb->prepare("SELECT `option_name` FROM %i WHERE `option_name` LIKE %s", $wpdb->options, $m4is_q78234 );
 $m4is_n46 =$wpdb->get_col($m4is_v2613 );
 foreach($m4is_n46 as $m4is_j56697 ){
  
}
}$m4is_z46 =['infusionsoft_user_id', 'memberium/contact_id/%', 'm4is%', 'memberium%', ];
 foreach($m4is_z46 as $m4is_h35 ){
$m4is_q78234 =$wpdb->esc_like($m4is_h35 );
 $m4is_v2613 =$wpdb->prepare("SELECT `user_id`, `meta_key` FROM %i WHERE `meta_key` LIKE %s", $wpdb->usermeta, $m4is_q78234 );
 $m4is_g9638 =$wpdb->query($m4is_v2613 );
 $m4is_g9638 =is_array($m4is_g9638 )? $m4is_g9638 : [];
 foreach($m4is_g9638 as $m4is_f087 =>$m4is_h35 ){
update_user_meta($m4is_f087, $m4is_h35, '' );
 delete_user_meta($m4is_f087, $m4is_h35 );
 
}
}$m4is_k64806 =['%is4wp%', ];
 foreach($m4is_k64806 as $m4is_h35 ){
$m4is_q78234 =$wpdb->esc_like($m4is_h35 );
 $m4is_v2613 =$wpdb->prepare("SELECT `post_id`, `meta_key` FROM %i WHERE `meta_key` LIKE %s", $wpdb->postmeta, $m4is_q78234 );
 $m4is_g9638 =$wpdb->query($m4is_v2613 );
 $m4is_g9638 =is_array($m4is_g9638 )? $m4is_g9638 : [];
 foreach($m4is_g9638 as $m4is_f087 =>$m4is_h35 ){
update_post_meta($m4is_f087, $m4is_h35, '' );
 delete_post_meta($m4is_f087, $m4is_h35 );
 
}
}$m4is_k48 =get_option('memberium_tables', []);
 foreach($m4is_k48 as $m4is_e80 ){
$wpdb->query("TRUNCATE TABLE {$m4is_e80
}" );
 
} if(file_exists(ABSPATH . '.maintenance' )){
unlink(ABSPATH . '.maintenance' );
 
}
}
}

