<?php

/**
* Copyright (c) 2018-2025 David J Bullock
* Web Power and Light
*/


 class_exists('m4is_q1089' )||die();
  final 
class m4is_z6342 {
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_d4861();
 
} private 
function m4is_d4861(){
add_action('admin_head', [$this, 'm4is_r57684' ]);
    $this->m4is_o31785();
 
}public 
function m4is_r57684(): void {
echo <<<HTMLBLOCK
		<style>
			#bb_redirection,
			#bp-member-type-redirection {
			display: none !important;
			}
			</style>
		HTMLBLOCK;
 
} private 
function m4is_o31785(){
if(!class_exists('BP_Core_Members_Switching' )){
return;
 
}return;
  $m4is_k536 =BP_Core_Members_Switching::get_instance();
 remove_action('personal_options', [$m4is_k536, 'action_personal_options']);
 remove_filter('ms_user_row_actions', [$m4is_k536, 'filter_user_row_actions']);
 remove_filter('user_row_actions', [$m4is_k536, 'filter_user_row_actions']);
 
}  
function m4is_k481(array $m4is_w64 ): void {
if(!function_exists('bp_get_member_types' )){
return;
 
}$m4is_m63284 =bp_get_member_types([], '' );
 if(empty($m4is_m63284 )||!is_array($m4is_m63284 )){
return;
 
}echo <<<HTMLBLOCK
			<h3>BuddyPress</h3>
			<li>
			<label>Profile Type</label>
			<input value="" name="buddypress_profile_type" type="hidden">
			<select style="width:400px; height:1.6em;" class="roles-selector" name="buddypress_profile_type" placeholder="Select the BuddyPress profile type to apply on login">
			<option value="">(None)</option>
		HTMLBLOCK;
 $m4is_w64['buddypress_profile_type']=empty($m4is_w64['buddypress_profile_type'])? '' : $m4is_w64['buddypress_profile_type'];
 foreach($m4is_m63284 as $m4is_l9671 =>$m4is_y50 ){
$m4is_w42 =empty($m4is_y50->labels['name'])? ucwords($m4is_l9671 ): $m4is_y50->labels['name'];
 if(!empty($m4is_w42 )){
$m4is_a437 =$m4is_w64['buddypress_profile_type']== $m4is_l9671 ? ' selected ' : '';
 printf('<option value="%s" %s>%s</option>', $m4is_l9671, $m4is_a437, $m4is_w42 );
 
}
}$m4is_y96480 =m4is_h65::m4is_o64(0000 );
 echo <<<HTMLBLOCK
			</select>{$m4is_y96480
}
			</li><br />
		HTMLBLOCK;
 
} 
function m4is_f2730(array $m4is_m96240, int $m4is_d07693, array $m4is_m5907 ): array {
$m4is_l9671 ='buddypress_profile_type';
 $m4is_m96240[$m4is_d07693][$m4is_l9671]=empty($m4is_m96240[$m4is_d07693][$m4is_l9671])? '' : $m4is_m96240[$m4is_d07693][$m4is_l9671];
 if(isset($m4is_m5907['buddypress_profile_type'])){
$m4is_m96240[$m4is_d07693][$m4is_l9671]=$m4is_m5907[$m4is_l9671];
 
}return $m4is_m96240;
 
}
}

