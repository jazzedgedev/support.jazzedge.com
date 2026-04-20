<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83')||die();
 
class m4is_g75 {
private 
function __construct(){

}static 
function init(){
$m4is_x8341 =defined('MEMBERIUM_BETA' )&&constant('MEMBERIUM_BETA' );
 if($m4is_x8341 ){
add_action('admin_footer-users.php', [__CLASS__, 'm4is_w283']);
 add_action('admin_notices', [__CLASS__, 'm4is_e53']);
 add_action('admin_print_styles-users.php', [__CLASS__, 'm4is_h061']);
 add_filter('bulk_actions-users', [__CLASS__, 'm4is_u69651']);
 add_filter('handle_bulk_actions-users', [__CLASS__, 'm4is_d358'], 10, 3 );
 add_action('restrict_manage_users', [__CLASS__, 'm4is_d65419']);
 
}
}static 
function m4is_u69651($m4is_r3298 ){
$m4is_r3298['memb_bulk_tag']=__('Bulk Add/Remove CRM Tag', 'memberium' );
 return $m4is_r3298;
 
}static 
function m4is_d358($m4is_q37596, $m4is_c05328, $m4is_m38 ){
$m4is_y66291 =['memb_bulk_tag_error', 'memb_bulk_tag_no_contact', 'memb_bulk_tag_success', ];
 $m4is_q37596 =remove_query_arg($m4is_y66291, $m4is_q37596 );
  $m4is_m38 =array_filter($m4is_m38 );
 $m4is_d913 =isset($_GET['memb_bulk_update_tag'])? (int) $_GET['memb_bulk_update_tag']: 0;
 if($m4is_c05328 == 'memb_bulk_tag' ){
$m4is_d913 =empty($_REQUEST['memb_bulk_update_tag'])? 0 : (int) $_REQUEST['memb_bulk_update_tag'];
  if(empty($m4is_d913 )){
$m4is_q37596 =add_query_arg('memb_bulk_tag_error', 'tag', $m4is_q37596 );
 return $m4is_q37596;
 
} $m4is_y673 =$m4is_d913 < 0 ? 'remove' : 'add';
 $m4is_v30712 =[];
 $m4is_r09178 =0;
 foreach ($m4is_m38 as $m4is_f087){
$m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087);
 if(!empty($m4is_h21895)){
$m4is_v30712[]=$m4is_h21895;
 
}else{
++$m4is_r09178;
 
}
} if(empty($m4is_v30712)){
$m4is_q37596 =add_query_arg('memb_bulk_tag_no_contact', 'all', $m4is_q37596 );
 return $m4is_q37596;
 
}$m4is_n548 =m4is_r83::m4is_c26()->m4is_p98460($m4is_v30712, $m4is_d913);
 $m4is_y66291 =[];
 if(is_array($m4is_n548)){
$m4is_m60 =!empty($m4is_n548['SUCCESS'])? count($m4is_n548['SUCCESS']): false;
 $m4is_z7810 =!empty($m4is_n548['FAILURE'])? count($m4is_n548['FAILURE']): false;
 if($m4is_m60 ){
$m4is_y66291['memb_bulk_tag_success']=$m4is_m60;
 
}if($m4is_z7810){
$m4is_y66291['memb_bulk_tag_error']=$m4is_z7810;
 
}
}else{
$m4is_y66291['memb_bulk_tag_error']=count($m4is_v30712);
 
}if(!empty($m4is_r09178)){
$m4is_y66291['memb_bulk_tag_no_contact']=(int) $m4is_r09178;
 
}if(!empty($m4is_y66291)){
$m4is_q37596 =add_query_arg($m4is_y66291, $m4is_q37596);
 
}
}return $m4is_q37596;
 
}static 
function m4is_d65419(){
$m4is_u0837 =__('Select CRM Tag', 'memberium' );
  echo "<div class='memb_bulk_update_tag_wrap'>", "\n";
  echo '<select id="memb_bulk_update_tag" name="memb_bulk_update_tag" class="memb_bulk_update_tag" placeholder="' . $m4is_u0837 . '">', "\n";
 echo '<option value="0">none</option>', "\n";
 echo '<option value="1">foo</option>', "\n";
 echo '<option value="2">bar</option>', "\n";
 echo '<option value="3">baz</option>', "\n";
 echo '</select>', "\n";
 echo "</div>", "\n";
 
}static 
function m4is_e53(){
$m4is_d786 ='memb_bulk_tag_';
 $m4is_c34857 =$m4is_d786 . 'success';
 $m4is_s656 =$m4is_d786 . 'error';
 $m4is_z12 =$m4is_d786 . 'no_contact';
 $m4is_a173 ='';
 $m4is_j0361 ='';
 if(empty($_REQUEST[$m4is_c34857])&&empty($_REQUEST[$m4is_s656])&&empty($_REQUEST[$m4is_z12])){
return;
 
}$m4is_m60 =empty($_REQUEST[$m4is_c34857])? false : $_REQUEST[$m4is_c34857];
  $m4is_q847 =empty($_REQUEST[$m4is_s656])? false : $_REQUEST[$m4is_s656];
 $m4is_r09178 =empty($_REQUEST[$m4is_z12])? false : $_REQUEST[$m4is_z12];
 if($m4is_m60 ){
$m4is_j0361 ='success';
 if((int) $m4is_m60 > 1 ){
$m4is_a173 .= sprintf(__('%s contacts have been updated.', 'memberium' ), $m4is_m60 );
 
}else{
$m4is_a173 .= __('1 contact has been updated.', 'memberium' );
 
}
}if($m4is_q847){
$m4is_j0361 =empty($m4is_j0361)? 'error' : $m4is_j0361;
 $m4is_a173 .= !empty($m4is_a173)? "<br>" : "";
 if($m4is_q847 === 'tag' ){
$m4is_a173 .= __('No Tag selected.', 'memberium' );
 
}else{
if((int)$m4is_q847 > 1 ){
$m4is_a173 .= sprintf(__('%s contacts not updated.', 'memberium' ), $m4is_q847);
 
}else{
$m4is_a173 .= __('1 contact not updated.', 'memberium' );
 
}
}
} if($m4is_r09178 ){
$m4is_j0361 =empty($m4is_j0361 )? 'error' : $m4is_j0361;
 $m4is_a173 .= empty($m4is_a173 )? '' : '<br>';
 if($m4is_r09178 === 'all' ){
$m4is_a173 .= __('None of the selected users have an Infusionst Contact ID.', 'memberium' );
 
}elseif((int) $m4is_r09178 > 1 ){
$m4is_a173 .= sprintf(__('%s selected users do not have a contact ID.', 'memberium' ), $m4is_r09178 );
 
}else{
$m4is_a173 .= __('1 selected User does not have a contact ID.', 'memberium' );
 
}
}if(!empty($m4is_a173 )){
echo "<div class=\"notice notice-{$m4is_j0361
} is-dismissible\">";
 echo "<h2>Memberium " . __('Bulk Tag Contacts', 'memberium' ). "</h2>";
 echo "<p>{$m4is_a173
}</p>";
 echo "</div>";
 
}return;
 
}static 
function m4is_h061(){
?>
		<style id="memb_bulk_contact_tags_style">
			/*
			.memb_hidden { display:none; }
			*/
			.memb_bulk_update_tag { width:200px; }
			.memb_bulk_update_tag_wrap { float:left; margin-right:6px; max-width:12.5rem; };
		</style>
		<?php
 
}static 
function m4is_w283(){
$m4is_z470 =[];
 $m4is_l9321 =m4is_k865::m4is_z2906(true )['mc'];
 foreach ($m4is_l9321 as $m4is_d07693 =>$m4is_p786 ){
$m4is_z470[]=['id' =>$m4is_d07693, 'text' =>'Add ' . $m4is_p786 . ' (' . $m4is_d07693 . ')' ];
 $m4is_z470[]=['id' =>'-' . $m4is_d07693, 'text' =>'Remove ' . $m4is_p786 . ' (-' . $m4is_d07693 . ')' ];
 
}?>
		<script id="memb_bulk_contact_tags_script">
		(function( $ ) {
			$( document ).ready( function() {
				var bulktaglist = <?php echo json_encode($m4is_z470)?>,
					$changedMembSel = null;
				// Move Inputs Position
				$('.memb_bulk_update_tag_wrap').each(function(i, $wrap) {
					var $parent  = $($wrap).closest(".tablenav"),
						$input   = $('input', $wrap),
						selector = $parent.hasClass('top') ? '#bulk-action-selector-top' : '#bulk-action-selector-bottom';
						$($wrap).insertAfter( $(selector) );
						$($input).wpalSelect2({
						data        : bulktaglist,
						placeholder : $($input).attr("placeholder")
					}).on('change', function(e) {
						if( $changedMembSel !== e.target ){
							membBulkUpdateTagChange(e.target, e.val);
						}
					});
				});

				// Action Select Changes
				$('select[name="action"], select[name="action2"]').on('change', function(e) {
					if( this.value === 'memb_bulk_tag' ){
						$('.memb_bulk_update_tag_wrap').removeClass('memb_hidden');
					}
					else{
						$('.memb_bulk_update_tag_wrap').addClass('memb_hidden');
					}
				});

				var membBulkUpdateTagChange = function ( $el, val ){
					$('.memb_bulk_update_tag').each(function(i, $input) {
						if( $input !== $el ){
							if( $input.value !== undefined && val !== $input.value ){
								$input.value = val;
								$($input).trigger('change');
							}
						}
						else {
							$changedMembSel = $el;
						}
					});
				};
			});
		})( jQuery );
		</script>
		<?php
 
}
}

