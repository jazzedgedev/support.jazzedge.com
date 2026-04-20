<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_v5864 {
private $m4is_f4218;
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$m4is_f4218 ='memberium';
  $this->m4is_d4861();
 
} private 
function m4is_d4861(){
add_action('admin_init', [$this, 'm4is_m34']);
 
} private 
function m4is_n436(){
return ['_is4wp_learndash_actions', '_is4wp_learndash_autoenroll', '_is4wp_learndash_autojoin', '_is4wp_learndash_goals', '_is4wp_learndash_redirect', '_is4wp_learndash_shortcodes', '_is4wp_learndash_tags', '_is4wp_lms_complete_percent', '_is4wp_lms_completed', '_is4wp_lms_grade', '_is4wp_lms_start_date', ];
 
} private 
function m4is_q8056(){
return ['course', 'lesson', ];
 
} 
function m4is_m34(){
 $m4is_q485 =m4is_s6729::m4is_c26()->m4is_m6180();
  if(in_array($m4is_q485, $this->m4is_q8056())){
add_meta_box('is4wp-sensei-actions', 'Sensei Memberium Integration', [$this, 'm4is_f0946'], $m4is_q485, 'side' );
 
} add_action('save_post', [$this, 'm4is_j36'], 10, 3 );
 
} 
function m4is_f0946(){
global $post;
 $m4is_v46257 =$this->m4is_q8056();
 if(in_array($post->post_type, $m4is_v46257 )){
$m4is_i65 ="memberium_sensei_actions_nonce_{$post->ID
}";
 $m4is_k698 =constant('MEMBERIUM_SKU' );
 wp_nonce_field($m4is_k698, $m4is_i65 );
 $m4is_l271 =get_post_meta($post->ID );
 $m4is_r1692 =[];
 $m4is_f69781 =$this->m4is_n436();
 $m4is_i7193 =m4is_j4156::m4is_z8359();
 foreach($m4is_f69781 as $m4is_l9671 ){
$m4is_r1692[$m4is_l9671]=isset($m4is_l271[$m4is_l9671][0])? $m4is_l271[$m4is_l9671][0]: '';
 
}if($post->post_type == 'course' ){
$m4is_w42 =__("AutoEnroll Tags", 'memberium' );
 $m4is_v586 =$m4is_r1692['_is4wp_learndash_autoenroll']> '' ? $m4is_r1692['_is4wp_learndash_autoenroll']: '';
 echo <<<HTMLBLOCK
					<label for="_is4wp_learndash_autoenroll">{$m4is_w42
}:</label>
					<input name="_is4wp_learndash_autoenroll" class="multitaglist" style="width:100%; max-width:100%" value="{$m4is_v586
}"><br /><br />
				HTMLBLOCK;
 
}$m4is_j28 =$m4is_r1692['_is4wp_learndash_tags']> '' ? $m4is_r1692['_is4wp_learndash_tags']: '';
 $m4is_z50 =$m4is_r1692['_is4wp_learndash_goals']> '' ? $m4is_r1692['_is4wp_learndash_goals']: '';
 $m4is_o61 =__("Apply these Tags", $this->m4is_f4218 );
 $m4is_c64832 =__("Run this Actionsets", $this->m4is_f4218 );
 $m4is_s4368 =__("Achieve these Goals", $this->m4is_f4218 );
 $m4is_m3162 ='';
 foreach ($m4is_i7193 as $m4is_w64602=>$m4is_t0631){
$selected =$m4is_r1692['_is4wp_learndash_actions']== $m4is_w64602 ? ' selected="selected" ' : '';
 $m4is_m3162 .= "<option value='{$m4is_w64602
}'{$selected
}>{$m4is_t0631
}</option>";
 
}echo <<<HTMLBLOCK
				<p>On completion of this section, execute the following actions:</p>

				<label for="_is4wp_learndash_tags">{$m4is_o61
}:</label>
				<input name="_is4wp_learndash_tags" class="multitaglist" style="width:100%; max-width:100%" value="{$m4is_j28
}"><br /><br />

				<label for="_is4wp_learndash_actions">{$m4is_c64832
}:</label>
				<select class="actionset-selector" name="_is4wp_learndash_actions" style="width:100%; max-width:100%">
				<option value="0">(No Actions)</option>
				{$m4is_m3162
}
				</select>

				<label for="_is4wp_learndash_goals">{$m4is_s4368
}:</label>
				<input name="_is4wp_learndash_goals" style="width:100%; max-width:100%" value="{$m4is_z50
}"><br /><br />

				<hr />
			HTMLBLOCK;
 $m4is_f69781 =['' =>'(None)'];
 $m4is_a89 =m4is_c69807::m4is_f5248('contact', true );
 foreach($m4is_a89 as $m4is_q523 ){
$m4is_f69781[$m4is_q523]=$m4is_q523;
 
}$m4is_y66291 =['class' =>'actionset-selected', 'style' =>'width:250px;', 'echo' =>false, ];
 if($post->post_type == 'course'){
$m4is_t098 =isset($m4is_r1692['_is4wp_lms_start_date'])? $m4is_r1692['_is4wp_lms_start_date']: '';
 $m4is_p473 =isset($m4is_r1692['_is4wp_lms_complete_percent'])? $m4is_r1692['_is4wp_lms_complete_percent']: '';
 $start_date_label =__('Start Date Field', $this->m4is_f4218 );
 $percent_complete_label =__("Percent Complete", $this->m4is_f4218 );
 $m4is_t76436 =m4is_h65::m4is_f7205('_is4wp_lms_start_date', $m4is_f69781, $m4is_t098, $m4is_y66291 );
 $m4is_r186 =m4is_h65::m4is_f7205('_is4wp_lms_complete_percent', $m4is_f69781, $m4is_p473, $m4is_y66291 );
 echo <<<HTMLBLOCK
					<label for="_is4wp_lms_start_date">{$start_date_label
}:</label><br />
					{$m4is_t76436
}<br />
					<label for="_is4wp_lms_complete_percent">{$percent_complete_label
}:</label><br />
					{$m4is_r186
}<br />
				HTMLBLOCK;
 
}if($post->post_type == 'lesson'){
$m4is_m17 =isset($m4is_r1692['_is4wp_lms_grade'])? $m4is_r1692['_is4wp_lms_grade']: '';
 $m4is_f42 =isset($m4is_r1692['_is4wp_lms_completed'])? $m4is_r1692['_is4wp_lms_completed']: '';
 $m4is_g063 =__(" Grade", $this->m4is_f4218 );
 $m4is_d663 =__("Quiz Passed", $this->m4is_f4218 );
 $m4is_s14 =m4is_h65::m4is_f7205('_is4wp_lms_grade', $m4is_f69781, $m4is_m17, $m4is_y66291 );
 $m4is_b871 =m4is_h65::m4is_f7205('_is4wp_lms_completed', $m4is_f69781, $m4is_f42, $m4is_y66291 );
 $m4is_i46 =<<<HTMLBLOCK
				<label for="_is4wp_lms_grade">{$m4is_g063
}:</label><br />
				{$m4is_s14
}
				<br>
				<label for="_is4wp_lms_completed">{$m4is_d663
}:</label><br />
				{$m4is_b871
}
				<br>
				HTMLBLOCK;
 echo $m4is_i46;
 
}echo '<hr />';
 
}
} 
function m4is_j36($m4is_b4068, $m4is_m5907, $m4is_a686 ){
if(defined('DOING_AUTOSAVE' )&&DOING_AUTOSAVE ){
return;
 
}$m4is_i65 ="memberium_sensei_actions_nonce_{$m4is_b4068
}";
 $m4is_k698 =constant('MEMBERIUM_SKU' );
 if(empty($_POST[$m4is_i65])||!wp_verify_nonce($_POST[$m4is_i65], $m4is_k698 )){
return;
 
} if(!current_user_can('edit_posts', $m4is_b4068 )){
return;
 
} $m4is_u450 =$this->m4is_n436();
  foreach($m4is_u450 as $m4is_l9671 ){
 if(isset($_POST[$m4is_l9671])){
 if(empty($_POST[$m4is_l9671])){
delete_post_meta($m4is_b4068, $m4is_l9671);
 
} else{
update_post_meta($m4is_b4068, $m4is_l9671, $_POST[$m4is_l9671]);
 
}
}
}
} 
}

