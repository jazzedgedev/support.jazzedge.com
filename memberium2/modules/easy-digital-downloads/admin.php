<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r96' )||die();
 final 
class m4is_a10 {
private $m4is_x25;
 private $m4is_m461;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
add_action('admin_init', [$this, 'm4is_i702']);
 add_action('admin_init', [$this, 'm4is_e90']);
 
}
function m4is_i702(){
$this->m4is_m461 =m4is_r96::m4is_c26();
 $this->m4is_x25 =m4is_s6729::m4is_c26();
 
}   
function m4is_e90(){
add_meta_box('memberium\edd\actions', 'Memberium for EDD', [$this, 'm4is_v20317'], 'download', 'side' );
 add_action('save_post_download', [$this, 'm4is_h8236'], 10, 3 );
 
} 
function m4is_v20317(WP_Post $m4is_m5907 ){
$m4is_l9321 =$this->m4is_m461->m4is_x65($m4is_m5907->ID );
 $m4is_b239 =__('Access Tag', 'memberium' );
 $m4is_b78 =__('Cancel Tag', 'memberium' );
 $m4is_k0645 =__('Payment Failure Tag', 'memberium' );
 $m4is_h01 =__('Trial Tag', 'memberium' );
 $m4is_a27648 =wp_nonce_field('edd_download_actions', "memberium_edd_download_nonce_{$m4is_m5907->ID
}", true, false );
 echo <<<HTMLBLOCK
			{$m4is_a27648
}
			<label for="_memberium_access_tag">
				{$m4is_b239
}:
			</label>
			<input name="_memberium_access_tag" class="taglistdropdown" style="width:100%; max-width:100%" value="{$m4is_l9321['main']
}">
			<br /><br />

			<label for="_memberium_payf_tag">
				{$m4is_k0645
}:
			</label>
			<input name="_memberium_payf_tag"  class="taglistdropdown" style="width:100%; max-width:100%" value="{$m4is_l9321['payf']
}">
			<br /><br />

			<label for="_memberium_trial_tag">
				{$m4is_h01
}:
			</label>
			<input name="_memberium_trial_tag" class="taglistdropdown" style="width:100%; max-width:100%" value="{$m4is_l9321['trial']
}">
			<br /><br />

			<label for="_memberium_canc_tag">
				{$m4is_b78
}:
			</label>
			<input name="_memberium_canc_tag" class="taglistdropdown" style="width:100%; max-width:100%" value="{$m4is_l9321['canc']
}">
			<br /><br />
		HTMLBLOCK;
 
}
function m4is_h8236(int $m4is_b4068, WP_Post $m4is_m5907, bool $m4is_a686){
if(!$this->m4is_x25->m4is_w54728($m4is_b4068, "memberium_edd_download_nonce_{$m4is_b4068
}", 'edd_download_actions', 'edit_posts' )){
return;
 
}$m4is_u450 =['_memberium_access_tag', '_memberium_canc_tag', '_memberium_payf_tag', '_memberium_trial_tag', ];
 foreach ($m4is_u450 as $m4is_l9671 ){
if(empty($_POST[$m4is_l9671])){
delete_post_meta($m4is_b4068, $m4is_l9671 );
 
}else{
update_post_meta($m4is_b4068, $m4is_l9671, trim($_POST[$m4is_l9671], ',' ));
 
}
}
} 
}

