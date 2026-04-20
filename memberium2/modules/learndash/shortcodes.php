<?php
 class_exists('m4is_h68' )||die();
 final 
class m4is_v43 {
private static object $m4is_r1546;
 private static object $m4is_f683;
 public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_f683 =m4is_f58::m4is_c26();
 
} public static 
function m4is_b2590($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(!is_user_logged_in()){
return '';
 
}$m4is_y642 =['id' =>0, 'not' =>'', ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 if(empty($m4is_l62046['id'])){
return '';
 
}$m4is_l62046['not']=m4is_f61::m4is_d8195($m4is_l62046['not'], false );
 $m4is_f087 =get_current_user_id();
 $m4is_c6320 =get_user_meta($m4is_f087, '_sfwd-course_progress', true );
 $m4is_p124 =false;
 if(is_array($m4is_c6320 )){
foreach($m4is_c6320 as $m4is_e95021 =>$m4is_x981 ){
if($m4is_l62046['id']== $m4is_e95021 &&$m4is_x981['total']== $m4is_x981['completed']){
$m4is_p124 =true;
 break;
 
}if(!empty($m4is_x981['lessons'][$m4is_l62046['id']])){
$m4is_p124 =true;
 break;
 
}if(!empty($m4is_x981['topics'][$m4is_l62046['id']])){
$m4is_p124 =true;
 break;
 
}
}
}if($m4is_l62046['not']){
$m4is_p124 =!$m4is_p124;
 
}$m4is_i1590 ='';
 $m4is_j02786 ='';
 $m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_p124 );
 return m4is_f61::m4is_u150(true, $m4is_o498, $m4is_i1590, $m4is_j02786 );
 
} public static 
function m4is_y635($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(!is_user_logged_in()){
return '';
 
}$m4is_y642 =['id' =>0, 'not' =>false, 'txtfmt' =>'', 'capture' =>'', ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_s6672 =ld_get_mycourses(get_current_user_id());
 $m4is_m60 =($m4is_s6672 )&&in_array($m4is_l62046['id'], $m4is_s6672 );
 if($m4is_l62046['not']){
$m4is_m60 =!$m4is_m60;
 
}$m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_m60 );
 return m4is_f61::m4is_u150(true, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
} public static 
function m4is_f13($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(!is_singular()){
return '';
 
}if(!is_user_logged_in()){
return '';
 
}if(!function_exists('ld_update_course_access' )){
return 'LearnDash not installed<br />';
 
}$m4is_y642 =['course_id' =>0, 'user_id' =>get_current_user_id(), ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['course_id']=(int) $m4is_l62046['course_id'];
 if(empty($m4is_l62046['course_id'])||empty($m4is_l62046['user_id'])){
return '';
 
}if(!get_post_status($m4is_l62046['course_id'])){
return 'Invalid Course ID<br />';
 
}$m4is_j0669 =(bool) (strtolower(trim($m4is_v3458 ))== 'memb_learndash_course_unenroll' );
 ld_update_course_access($m4is_l62046['user_id'], $m4is_l62046['course_id'], $m4is_j0669 );
 
} public static 
function m4is_i7581($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(!is_singular()){
return '';
 
}if(!is_user_logged_in()){
return '';
 
}$m4is_y642 =['admin_id' =>0, 'tag_id' =>0, 'button_text' =>'Clear Course History', 'warning' =>'Are you sure you want to clear your course history?', 'completion' =>'Your course history has been cleared.', ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_w365 =(int) $m4is_l62046['admin_id'];
 $m4is_d913 =(int) $m4is_l62046['tag_id'];
 $m4is_x27613 =get_current_user_id();
 $m4is_d9476 =trim($m4is_l62046['button_text']);
 $m4is_y09662 =trim($m4is_l62046['warning']);
 $m4is_e478 =trim($m4is_l62046['completion']);
 if(self::$m4is_r1546->m4is_v461()){
if(!$m4is_w365 ){
return '<p><strong>Error:</strong> No admin ID provided.</p>';
 
}if(!user_can($m4is_w365, 'edit_users' )){
return '<p><strong>Error:</strong> Admin user provided does not have sufficient permissions to clear LearnDash course data.</p>';
 
}return sprintf('<p>The %s shortcode may cannot be used by administrators.</p>', $m4is_v3458 );
 
}$m4is_u076 =base64_encode(serialize(['admin_id' =>$m4is_w365, 'completion' =>$m4is_e478, 'tag_id' =>$m4is_d913, 'user_id' =>$m4is_x27613, 'warning' =>$m4is_l62046['warning'], ]));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u076 );
 $m4is_a27648 =wp_nonce_field('memberium/learndash/delete_history/' . $m4is_x27613, '_wpnonce', true, false);
 $m4is_i46 ='';
 $m4is_v215 =self::$m4is_f683->m4is_t73689();
 if(!empty($m4is_v215 )){
$m4is_i46 .= sprintf('<p class="memberium_learndash_clear_history_message">%s</p>', $m4is_v215 );
 
}$m4is_i46 .= <<<HTMLBLOCK
			<form method="post" class="memberium-learndash-delete-history" action="">
				{$m4is_a27648
}
				<input type="hidden" name="memb_form_type" value="memberium/learndash/delete_history">
				<input type="hidden" name="user_id" value="{$m4is_x27613
}">
				<input type="hidden" name="parameters" value="{$m4is_u076
}">
				<input type="hidden" name="signature" value="{$m4is_o31859
}">
				<input type="submit" class="memberium_learndash_clear_history_button" value="{$m4is_d9476
}"  onClick="return confirmDelete()">
			</form>
			<script>
				function confirmDelete() {
					return confirm( '{$m4is_y09662
}' );
				}
			</script>
		HTMLBLOCK;
 return $m4is_i46;
 
}
}

