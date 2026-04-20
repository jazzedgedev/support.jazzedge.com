<?php

/**
 * Copyright (c) 2021-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_w6715' )||die();
  final 
class m4is_x73604 {
private m4is_s6729 $m4is_x25;
 private m4is_r83 $m4is_r1546;
       public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_i702();
 $this->m4is_d4861();
 
}      private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_x25 =m4is_s6729::m4is_c26();
 
} private 
function m4is_d4861(): void {
add_action('admin_init', [$this, 'm4is_p783']);
 add_filter('memberium/modules/active/names', [$this, 'm4is_f809'], 10, 1 );
 
} private 
function m4is_p6359(): array {
return ['_memberium_gamipress_badge_add', '_memberium_gamipress_rank_add', '_memberium_gamipress_tag_add', ];
 
} private 
function m4is_c9572(): array {
global $wpdb;
 $m4is_e594 =[];
  $m4is_v2613 ="SELECT `ID` as `id`, `post_title` as `name` FROM %i WHERE `post_status` = 'publish' AND `post_type` IN (SELECT `post_name` FROM %i WHERE `post_type` = 'achievement-type' AND `post_status` = 'publish'); ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $wpdb->posts, $wpdb->posts );
 $rows =$wpdb->get_results($m4is_v2613, ARRAY_A );
 foreach ($rows as $row ){
$m4is_e594[$row['id']]=$row['name'];
 
}return $m4is_e594;
 
} private 
function m4is_e268(): array {
global $wpdb;
 $m4is_s62034 =[];
  $m4is_v2613 ="SELECT `ID` as `id`, `post_title` as `name` FROM %i WHERE `post_status` = 'publish' AND `post_type` IN (SELECT `post_name` FROM %i WHERE `post_type` = 'rank-type' AND `post_status` = 'publish'); ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $wpdb->posts, $wpdb->posts);
 $rows =$wpdb->get_results($m4is_v2613, ARRAY_A);
 foreach ($rows as $row ){
$m4is_s62034[$row['id']]=$row['name'];
 
}return $m4is_s62034;
 
} private 
function m4is_o8529(): void {
$m4is_y66291 =['fields' =>'ids', 'numberposts' =>-1, 'order' =>'ASC', 'orderby' =>'ID', 'post_status' =>'publish', 'post_type' =>gamipress_get_achievement_types_slugs(),  ];
 $m4is_k26 =get_posts($m4is_y66291 );
 $m4is_g65047 ='_memberium_gamipress_badge_add';
 $m4is_y205 ='memberium/gamipress/assign_by_tag';
 $m4is_o86564 =[];
 foreach($m4is_k26 as $m4is_d07693 ){
$m4is_p786 =get_post_meta($m4is_d07693, $m4is_g65047, true );
 if($m4is_p786 ){
$m4is_o86564[$m4is_d07693]=$m4is_p786;
 
}
}update_option($m4is_y205, $m4is_o86564, false );
 $m4is_g65047 ='_memberium_gamipress_tag_add';
 $m4is_y205 ='memberium/gamipress/tag_by_badge';
 $m4is_o86564 =[];
 foreach($m4is_k26 as $m4is_d07693 ){
$m4is_p786 =get_post_meta($m4is_d07693, $m4is_g65047, true );
 if($m4is_p786 ){
$m4is_o86564[$m4is_d07693]=$m4is_p786;
 
}
}update_option($m4is_y205, $m4is_o86564, false );
 $m4is_y66291 =['fields' =>'ids', 'numberposts' =>-1, 'order' =>'ASC', 'orderby' =>'ID', 'post_status' =>'publish', 'post_type' =>gamipress_get_rank_types_slugs(),  ];
 $m4is_g65047 ='_memberium_gamipress_tag_add';
 $m4is_y205 ='memberium/gamipress/rank/tag_by_rank';
 $m4is_o86564 =[];
 foreach($m4is_k26 as $m4is_d07693 ){
$m4is_p786 =get_post_meta($m4is_d07693, $m4is_g65047, true );
 if($m4is_p786 ){
$m4is_o86564[$m4is_d07693]=$m4is_p786;
 
}
}update_option($m4is_y205, $m4is_o86564, false );
 $m4is_g65047 ='_memberium_gamipress_rank_add';
 $m4is_y205 ='memberium/gamipress/rank/rank_by_tag';
 $m4is_o86564 =[];
 foreach($m4is_k26 as $m4is_d07693 ){
$m4is_p786 =get_post_meta($m4is_d07693, $m4is_g65047, true );
 if($m4is_p786 ){
$m4is_o86564[$m4is_d07693]=$m4is_p786;
 
}
}update_option($m4is_y205, $m4is_o86564, false );
 
} private 
function m4is_b45(): void {
$m4is_y66291 =['fields' =>'ids', 'numberposts' =>-1, 'order' =>'ASC', 'orderby' =>'ID', 'post_status' =>'publish', 'post_type' =>gamipress_get_rank_types_slugs(),  ];
 $m4is_k26 =get_posts($m4is_y66291 );
 $m4is_g65047 ='_memberium_gamipress_tag_add';
 $m4is_y205 ='memberium/gamipress/rank/tag_by_rank';
 $m4is_o86564 =[];
 foreach($m4is_k26 as $m4is_d07693 ){
$m4is_p786 =get_post_meta($m4is_d07693, $m4is_g65047, true );
 if($m4is_p786 ){
$m4is_o86564[$m4is_d07693]=$m4is_p786;
 
}
}update_option($m4is_y205, $m4is_o86564 );
 $m4is_g65047 ='_memberium_gamipress_rank_add';
 $m4is_y205 ='memberium/gamipress/rank/rank_by_tag';
 $m4is_o86564 =[];
 foreach($m4is_k26 as $m4is_d07693 ){
$m4is_p786 =get_post_meta($m4is_d07693, $m4is_g65047, true );
 if($m4is_p786 ){
$m4is_o86564[$m4is_d07693]=$m4is_p786;
 
}
}update_option($m4is_y205, $m4is_o86564 );
 
}     public 
function m4is_f809(array $m4is_y634 ): array {
return array_merge($m4is_y634, ['GamiPress for Keap']);
 
}     public 
function m4is_p783(): void {
$m4is_m421 =['gamipress_get_achievement_types_slugs', 'gamipress_get_rank_types_slugs', ];
 foreach($m4is_m421 as $m4is_a1056 ){
if(!function_exists($m4is_a1056 )){
return;
 
}
}$m4is_q485 =$this->m4is_x25->m4is_m6180();
 $m4is_a673 =gamipress_get_achievement_types_slugs();
 $m4is_n7063 =gamipress_get_rank_types_slugs();
 $m4is_y23 =apply_filters('memberium/lms/module_post_types', []);
 $m4is_v0361 =in_array($m4is_q485, $m4is_a673 );
 $m4is_l023 =in_array($m4is_q485, $m4is_n7063 );
 $m4is_j80972 =in_array($m4is_q485, $m4is_y23 );
 if($m4is_v0361 ){
add_meta_box('memberium-gamipress-achievements', 'Memberium for GamiPress', [$this, 'm4is_s697'], $m4is_q485, 'side' );
 add_action('save_post', [$this, 'm4is_h68593'], 10, 3 );
 
}if($m4is_l023 ){
add_meta_box('memberium-gamipress-ranks', 'Memberium for GamiPress', [$this, 'm4is_u4856'], $m4is_q485, 'side' );
 add_action('save_post', [$this, 'm4is_g7216'], 10, 3 );
 
}if($m4is_j80972 ){
$m4is_c892 =apply_filters('memberium/lms/name', 'LMS' );
 add_meta_box('memberium-lms-achievements', "GamiPress for {$m4is_c892
}", [$this, 'm4is_y64'], $m4is_q485, 'side' );
 add_action('save_post', [$this, 'm4is_v2850'], 10, 3 );
 
}
}    public 
function m4is_u4856(WP_Post $m4is_m5907 ): void {
$m4is_b4068 =$m4is_m5907->ID;
 $m4is_i796 =wp_nonce_field('memberium/gamipress/rank/save', 'memberium_gamipress_rank_nonce', true, false );
 $m4is_o70261 =get_post_meta($m4is_b4068, '_memberium_gamipress_tag_add', true );
 $m4is_o70261 =empty($m4is_o70261 )? 0 : $m4is_o70261;
 $m4is_y68 =__('Add Tag if Member has Rank', 'memberium' );
 $m4is_v48 =get_post_meta($m4is_b4068, '_memberium_gamipress_rank_add', true );
 $m4is_v48 =empty($m4is_v48 )? 0 : $m4is_v48;
 $m4is_c16 =__('Add Rank if Member has Tag', 'memberium' );
 echo <<<HTMLBLOCK
			{$m4is_i796
}
			<label for="_memberium_gamipress_tag_add">
				{$m4is_y68
}:
			</label>
			<input name="_memberium_gamipress_tag_add" class="taglistdropdown" style="width:100%; max-width:100%" value="{$m4is_o70261
}">
			<br /><br />

			<label for="_memberium_gamipress_rank_add">
				{$m4is_c16
}:
			</label>
			<input name="_memberium_gamipress_rank_add" class="taglistdropdown" style="width:100%; max-width:100%" value="{$m4is_v48
}">
			<br />
			<br />
		HTMLBLOCK;
 do_action('memberium/gamipress/rank_metabox' );
 
} public 
function m4is_g7216(int $m4is_b4068, WP_Post $m4is_m5907, bool $m4is_a686 ): void {
if(!$this->m4is_x25->m4is_w54728($m4is_b4068, 'memberium_gamipress_rank_nonce', 'memberium/gamipress/rank/save' )){
return;
 
}$m4is_u450 =$this->m4is_p6359();
 $m4is_n7063 =gamipress_get_rank_types_slugs();
 $this->m4is_x25->m4is_q72456($m4is_b4068, $m4is_u450, $_POST );
 $this->m4is_b45();
 
}      public 
function m4is_s697(WP_Post $m4is_m5907 ): void {
$m4is_b4068 =$m4is_m5907->ID;
 $m4is_i796 =wp_nonce_field('memberium/gamipress/achievement/save', "memberium_gamipress_nonce", true, false );
 $m4is_u86739 =__('Add Badge if Member has Tag', 'memberium' );
 $m4is_p73145 =get_post_meta($m4is_b4068, '_memberium_gamipress_badge_add', true );
 $m4is_p73145 =empty($m4is_p73145 )? 0 : $m4is_p73145;
 $m4is_y68 =__('Add Tag if Member has Badge', 'memberium' );
 $m4is_o70261 =get_post_meta($m4is_b4068, '_memberium_gamipress_tag_add', true );
 $m4is_o70261 =empty($m4is_o70261 )? 0 : $m4is_o70261;
 echo <<<HTMLBLOCK
			{$m4is_i796
}
			<label for="_memberium_gamipress_badge_add">
				{$m4is_u86739
}:
			</label>
			<input name="_memberium_gamipress_badge_add" class="taglistdropdown" style="width:100%; max-width:100%" value="{$m4is_p73145
}">
			<br /><br />

			<label for="_memberium_gamipress_tag_add">
				{$m4is_y68
}:
			</label>
			<input name="_memberium_gamipress_tag_add" class="taglistdropdown" style="width:100%; max-width:100%" value="{$m4is_o70261
}">
			<br /><br />
		HTMLBLOCK;
 do_action('memberium/gamipress/achievement_metabox');
 
} public 
function m4is_h68593(int $m4is_b4068, WP_Post $m4is_m5907, bool $m4is_a686 ): void {
if(!$this->m4is_x25->m4is_w54728($m4is_b4068, 'memberium_gamipress_nonce', 'memberium/gamipress/achievement/save' )){
return;
 
}$m4is_u450 =$this->m4is_p6359();
 $m4is_a673 =gamipress_get_achievement_types_slugs();
 foreach ($m4is_u450 as $m4is_l9671 ){
if(isset($_POST[$m4is_l9671])){
if(empty($_POST[$m4is_l9671])){
delete_post_meta($m4is_b4068, $m4is_l9671 );
 
}else{
update_post_meta($m4is_b4068, $m4is_l9671, $_POST[$m4is_l9671]);
 
}
}
} $this->m4is_o8529();
 
}    public 
function m4is_y64(WP_Post $m4is_m5907 ){
$m4is_l9671 ='_is4wp_learndash_achievement';
 $m4is_r1692 =get_post_meta($m4is_m5907->ID, $m4is_l9671, true );
 $m4is_r1692 =empty($m4is_r1692 )? 0 : $m4is_r1692;
 $m4is_e594 =$this->m4is_c9572();
 $m4is_a496 =get_post_type_object($m4is_m5907->post_type )->labels->singular_name;
 $m4is_i796 =wp_nonce_field('memberium/gamipress/lms/options/save', 'memberium_gamipress_lms_nonce', true, false );
 $m4is_r063 ='';
 $m4is_u86739 =__('Add Badge on Completion of', 'memberium' );
 foreach ($m4is_e594 as $m4is_d07693 =>$m4is_k52736){
$m4is_a437 =($m4is_r1692 == $m4is_d07693)? ' selected="selected" ' : '';
 $m4is_r063 ="<option value='{$m4is_d07693
}' {$m4is_a437
}>{$m4is_k52736
}</option>";
 
}echo <<<HTMLBLOCK
			{$m4is_i796
}
			<label for="{$m4is_l9671
}">
				{$m4is_u86739
} {$m4is_a496
}:
			</label>
			<select class="actionset-selector" name="{$m4is_l9671
}" style="width:100%; max-width:100%">
				<option value="0">(No Achievement)</option>
				{$m4is_r063
}
			</select>
			<br /><br />
		HTMLBLOCK;
 do_action('memberium/gamipress/achievement_metabox' );
 
} public 
function m4is_v2850(int $m4is_b4068, WP_Post $m4is_m5907, bool $m4is_a686 ): void {
if(!$this->m4is_x25->m4is_w54728($m4is_b4068, "memberium_gamipress_lms_nonce", 'memberium/gamipress/lms/options/save' )){
return;
 
}$m4is_u450 =['_is4wp_learndash_achievement', ];
 foreach ($m4is_u450 as $m4is_l9671 ){
if(isset($_POST[$m4is_l9671])){
if(empty($_POST[$m4is_l9671])){
delete_post_meta($m4is_b4068, $m4is_l9671 );
 
}else{
update_post_meta($m4is_b4068, $m4is_l9671, $_POST[$m4is_l9671]);
 
}
}
}
}
}

