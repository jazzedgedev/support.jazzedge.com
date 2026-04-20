<?php

/**
 * Copyright (c) 2021-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_o046 {
private $m4is_x25;
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_x25 =m4is_s6729::m4is_c26();
 $this->m4is_d4861();
 
} private 
function m4is_d4861(): void {
add_action('admin_init', [$this, 'm4is_p783']);
 add_filter('memberium/modules/active/names', [$this, 'm4is_f809'], 10, 1 );
 
}   public 
function m4is_f809(array $m4is_y634 ): array {
return array_merge($m4is_y634, ['BadgeOS for Memberium Integration' ]);
 
}   public 
function m4is_p783(): void {
$m4is_m421 =['badgeos_get_achievement_types_slugs', 'badgeos_get_rank_types_slugs', ];
 foreach($m4is_m421 as $m4is_a1056 ){
if(!function_exists($m4is_a1056 )){
return;
 
}
}$m4is_q485 =m4is_s6729::m4is_c26()->m4is_m6180();
 $m4is_a673 =badgeos_get_achievement_types_slugs();
 $m4is_n7063 =badgeos_get_rank_types_slugs();
 $m4is_y23 =apply_filters('memberium/lms/module_post_types', []);
 $m4is_c892 =apply_filters('memberium/lms/name', 'LMS' );
 if(in_array($m4is_q485, $m4is_a673 )){
add_meta_box('memberium-badgeos-achievements', 'BadgeOS for Memberium', [$this, 'm4is_s697'], $m4is_q485, 'side' );
 
}if(in_array($m4is_q485, $m4is_n7063 )){
add_meta_box('memberium-badgeos-ranks', 'BadgeOS for Memberium', [$this, 'm4is_u4856'], $m4is_q485, 'side' );
 
}if(in_array($m4is_q485, $m4is_y23 )){
add_meta_box('memberium-lms-achievements', "BadgeOS for {$m4is_c892
}", [$this, 'm4is_y64'], $m4is_q485, 'side' );
 
}if($_SERVER['REQUEST_METHOD']== 'POST' ){
add_action('save_post', [$this, 'm4is_v2850'], 10, 3 );
 if(isset($_POST['post_type'])&&in_array($_POST['post_type'], $m4is_a673 )){
add_action('save_post', [$this, 'm4is_h68593'], 10, 3 );
 
}if(isset($_POST['post_type'])&&in_array($_POST['post_type'], $m4is_n7063 )){
add_action('save_post', [$this, 'm4is_g7216'], 10, 3 );
 
}
}
}    public 
function m4is_u4856(WP_Post $m4is_m5907 ): void {
$m4is_k698 =defined('MEMBERIUM_SKU' )? constant('MEMBERIUM_SKU' ): '';
 $m4is_i796 =wp_nonce_field($m4is_k698, "memberium/badgeos/tag_map/{$m4is_m5907->ID
}", true, false );
 $m4is_o70261 =get_post_meta($m4is_m5907->ID, '_memberium_badgeos_tag_add', true );
 $m4is_o70261 =empty($m4is_o70261 )? 0 : $m4is_o70261;
 $m4is_l380 =__('Add Tag if member has Badge', 'memberium' );
  echo <<<HTMLBLOCK
			{$m4is_i796
}
			<label for  = "_memberium_badgeos_tag_add">{$m4is_l380
}:</label>
			<input name = "_memberium_badgeos_tag_add" class = "taglistdropdown" style = "width:100%; max-width:100%" value = "{$m4is_o70261
}"><br /><br />';
		HTMLBLOCK;
 do_action('memberium/badgeos/rank_metabox' );
 
} public 
function m4is_g7216(int $m4is_b4068, $m4is_m5907, $m4is_a686 ): void {
$m4is_k698 =defined('MEMBERIUM_SKU' )? constant('MEMBERIUM_SKU' ): '';
 if(!$this->m4is_x25->m4is_w54728($m4is_b4068, "memberium/badgeos/tag_map/{$m4is_b4068
}", $m4is_k698 )){
return;
 
}$m4is_u450 =['_memberium_badgeos_rank_add', '_memberium_badgeos_tag_add', ];
 $this->m4is_x25->m4is_q72456($m4is_b4068, $m4is_u450, $_POST );
  $this->m4is_b45();
 
}   public 
function m4is_s697(WP_Post $m4is_m5907 ){
global $post;
 $m4is_k698 =defined('MEMBERIUM_SKU' )? constant('MEMBERIUM_SKU' ): '';
 $m4is_a27648 =wp_nonce_field($m4is_k698, "memberium/badgeos/tag_map/{$m4is_m5907->ID
}", true, false );
 $m4is_p73145 =get_post_meta($m4is_m5907->ID, '_memberium_badgeos_badge_add', true );
 $m4is_p73145 =empty($m4is_p73145 )? 0 : $m4is_p73145;
 $m4is_l7916 =__('Add Badge if Member has Tag', 'memberium' );
 $m4is_o70261 =get_post_meta($m4is_m5907->ID, '_memberium_badgeos_tag_add', true );
 $m4is_o70261 =empty($m4is_o70261 )? 0 : $m4is_o70261;
 $m4is_l380 =__('Add Tag if Member has Badge', 'memberium' );
 echo <<<HTMLDOCK
			{$m4is_a27648
}
			<label for="_memberium_badgeos_badge_add">{$m4is_l7916
}:</label>
			<input name="_memberium_badgeos_badge_add" class="taglistdropdown" style="width:100%; max-width:100%" value="{$m4is_p73145
}"><br /><br />

			<label for="_memberium_badgeos_tag_add">{$m4is_l380
}:</label>
			<input name="_memberium_badgeos_tag_add" class="taglistdropdown" style="width:100%; max-width:100%" value="{$m4is_o70261
}"><br /><br />
		HTMLDOCK;
 do_action('memberium/badgeos/achievement_metabox');
 
} public 
function m4is_h68593(int $m4is_b4068 , $m4is_m5907, $m4is_a686): void {
$m4is_k698 =defined('MEMBERIUM_SKU' )? constant('MEMBERIUM_SKU' ): '';
 if(!$this->m4is_x25->m4is_w54728($m4is_b4068, "memberium/badgeos/tag_map/{$m4is_b4068
}", $m4is_k698 )){
return;
 
}$m4is_u450 =['_memberium_badgeos_badge_add', '_memberium_badgeos_tag_add', ];
 $this->m4is_x25->m4is_q72456($m4is_b4068, $m4is_u450, $_POST );
  $this->m4is_o8529();
 
}    public 
function m4is_y64(WP_Post $m4is_m5907 ): void {
global $post;
 $m4is_l9671 ='_is4wp_learndash_achievement';
 $m4is_k698 =defined('MEMBERIUM_SKU' )? constant('MEMBERIUM_SKU' ): '';
 $m4is_i796 =wp_nonce_field($m4is_k698, "memberium_badgeos_lms_nonce_{$m4is_m5907->ID
}", true, false );
 $m4is_r1692 =get_post_meta($m4is_m5907->ID, $m4is_l9671, true );
 $m4is_r1692 =empty($m4is_r1692 )? 0 : $m4is_r1692;
 $m4is_a496 =get_post_type_object($m4is_m5907->post_type )->labels->singular_name;
 $m4is_l7916 =__('Add Badge on Completion of ', 'memberium');
 $m4is_e594 =$this->m4is_c201();
 $m4is_r063 ='';
 if(is_array($m4is_e594 )){
foreach ($m4is_e594 as $m4is_d07693 =>$m4is_k52736 ){
$m4is_a437 =$m4is_r1692 == $m4is_d07693 ? ' selected="selected" ' : '';
 $m4is_r063 .= "<option value='{$m4is_d07693
}' {$m4is_a437
}>{$m4is_k52736
}</option>";
 
}
}echo <<<HTMLBLOCK
			<label for='{$m4is_l9671
}'>
				{$m4is_l7916
} {$m4is_a496
}:
			</label>
			<select class="actionset-selector" name="{$m4is_l9671
}" style="width:100%; max-width:100%">
				<option value="0">(No Achievement)</option>
				{$m4is_r063
}
			</select>
			<br />
			<br />
		HTMLBLOCK;
 do_action('memberium/badgeos/achievement_metabox' );
 
} public 
function m4is_v2850(int $m4is_b4068, $m4is_m5907, $m4is_a686 ){
$m4is_k698 =defined('MEMBERIUM_SKU' )? constant('MEMBERIUM_SKU' ): '';
 if(!$this->m4is_x25->m4is_w54728($m4is_b4068, "memberium_badgeos_lms_nonce_{$m4is_b4068
}", $m4is_k698 )){
return;
 
}$m4is_u450 =['_is4wp_learndash_achievement', ];
 $this->m4is_x25->m4is_q72456($m4is_b4068, $m4is_u450, $_POST );
 
}    private 
function m4is_c201(): array {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT `ID` as `id`, `post_title` as `name` FROM %i WHERE `post_status` = 'publish' AND `post_type` IN ( SELECT `post_name` FROM %i WHERE `post_type` = 'achievement-type' AND `post_status` = 'publish' ) )", $wpdb->posts, $wpdb->posts );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 $m4is_e594 =[];
 foreach ($m4is_m615 as $m4is_g91703 ){
$m4is_e594[$m4is_g91703['id']]=$m4is_g91703['name'];
 
}return $m4is_e594;
 
} private 
function m4is_o8529(){
$m4is_g65047 ='_memberium_badgeos_badge_add';
 $m4is_y205 ='memberium/badgeos/assign_by_tag';
 $m4is_f19205 ='memberium/badgeos/tag_by_badge';
 $m4is_o86564 =[];
 $m4is_y66291 =['numberposts' =>-1, 'order' =>'ASC', 'orderby' =>'ID', 'post_status' =>'publish', 'post_type' =>badgeos_get_achievement_types_slugs(), 'fields' =>'ids', ];
 $m4is_k26 =get_posts($m4is_y66291 );
 foreach ($m4is_k26 as $m4is_d07693 ){
$m4is_p786 =get_post_meta($m4is_d07693, $m4is_g65047, true );
 if($m4is_p786 ){
$m4is_o86564[$m4is_d07693]=$m4is_p786;
 
}
}update_option($m4is_y205, $m4is_o86564 );
 $m4is_o86564 =[];
 foreach($m4is_k26 as $m4is_d07693 ){
$m4is_p786 =get_post_meta($m4is_d07693, $m4is_g65047, true );
 if($m4is_p786 ){
$m4is_o86564[$m4is_d07693]=$m4is_p786;
 
}
}update_option($m4is_f19205, $m4is_o86564 );
 
} private 
function m4is_b45(){
$m4is_g65047 ='_memberium_badgeos_rank_add';
  $m4is_y205 ='memberium/badgeos/tag_by_rank';
 $m4is_o86564 =[];
 $m4is_y66291 =['numberposts' =>-1, 'order' =>'ASC', 'orderby' =>'ID', 'post_status' =>'publish', 'post_type' =>badgeos_get_rank_types_slugs(), 'fields' =>'ids', ];
 $m4is_k26 =get_posts($m4is_y66291 );
 $m4is_o86564 =[];
 foreach($m4is_k26 as $m4is_d07693 ){
$m4is_p786 =get_post_meta($m4is_d07693, $m4is_g65047, true );
 if($m4is_p786 ){
$m4is_o86564[$m4is_d07693]=$m4is_p786;
 
}
}update_option($m4is_y205, $m4is_o86564 );
  
} 
}

