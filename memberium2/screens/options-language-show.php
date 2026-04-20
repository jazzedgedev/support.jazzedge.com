<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 new m4is_w2351();
 final 
class m4is_w2351 {
private $m4is_r1546;
 private $m4is_l07426;
 
function __construct(){
$this->m4is_i702();
 $this->m4is_s3572();
 $this->m4is_o719();
 
} private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_l07426 =m4is_d86::m4is_n61();
 
} private 
function m4is_s3572(): void {
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}global $wpdb;
 if(!empty($_POST['add_translation'])){
$m4is_s90 =strtolower(trim($_POST['context']?? '' ));
 $m4is_k52736 =strtolower(trim($_POST['name']?? '' ));
 $m4is_r93654 =strtolower(trim($_POST['language']?? '' ));
 $m4is_b5246 =trim(stripslashes($_POST['origtext']?? '' ));
 $m4is_v586 =trim(stripslashes($_POST['value']?? '' ));
 $m4is_v2613 ='DELETE FROM %i WHERE `context` = %s AND `name` = %s AND origtext = %s';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $this->m4is_l07426, $m4is_s90, $m4is_k52736, $m4is_b5246 );
 $wpdb->query($m4is_v2613 );
 if(!empty($_POST['value'])){
$m4is_v2613 ='INSERT INTO %i (`context`, `language`, `name`, `origtext`, `value` ) VALUES (%s, %s, %s, %s, %s )';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $this->m4is_l07426, $m4is_s90, $m4is_r93654, $m4is_k52736, $m4is_b5246, $m4is_v586 );
 $wpdb->query($m4is_v2613 );
 
}
}if(!empty($_POST['update_translations'])){
$m4is_x39 =$_POST['value']?? [];
 foreach($m4is_x39 as $m4is_d07693 =>$m4is_v586 ){
$m4is_d07693 =intval($m4is_d07693 );
 $m4is_v586 =trim(stripslashes($m4is_v586 ));
 $m4is_v2613 =$wpdb->prepare('UPDATE %i SET `value` = %s WHERE `id` = %d', $this->m4is_l07426, $m4is_v586, $m4is_d07693 );
 $wpdb->query($m4is_v2613 );
 
}if(!empty($_POST['id'])&&is_array($_POST['id'])){
$m4is_p65 =array_filter(array_map('intval', $_POST['id']));
 $m4is_p65 =implode(',', $m4is_p65 );
 $m4is_v2613 =$wpdb->prepare("DELETE FROM %i WHERE `id` IN ( {$m4is_p65
} )", $this->m4is_l07426 );
 $wpdb->query($m4is_v2613 );
 
}
}
}private 
function m4is_o719(){
global $wpdb;
 $m4is_y96480 =m4is_h65::m4is_o64(14684, 'Click to Learn More' );
 $m4is_i796 =wp_nonce_field(__FILE__, 'memberium_language_translations_nonce', true, false );
 $m4is_h718 =trim($_GET['search']?? '' );
 $m4is_c7845 =esc_html($m4is_h718 );
 $m4is_i63 =$this->m4is_u64012();
 $m4is_p250 =$this->m4is_v7156($m4is_h718 );
  echo <<<HTMLBLOCK
			<h3>Multi-Language Support</h3>
			<p>
				Enter the name of the shortcode (without the brackets) in “Context”.<br />
				Leave Name and Language fields to the default.<br />
				Enter “Original text” (from which you want to translate) and then the “New text" you want displayed instead.<br />
			<p>
				{$m4is_y96480
}
			</p>
			<hr />
		HTMLBLOCK;
  echo <<<HTMLBLOCK
			<form method=post style="margin-bottom:24px;">
				{$m4is_i796
}
				<ul>
					<label>Context:</label>
					<input required="required" type="text" name="context" value="" size="30">
				</ul>
				<ul>
					<label>Name:</label>
					<select name="name">
						<option value="memberium">Memberium</option>
						<option value="">Generic</option>
					</select>
				</ul>
				<ul>
					<label>Language:</label>
					{$m4is_i63
}
				</ul>
				<ul>
					<label>Original Text:</label>
					<input required="required" type="text" name="origtext" value="" size="80">
				</ul>
				<ul>
					<label>New Text:</label>
					<input type="text" name="value" value="" size="80">
				</ul>

				<input required="required" type="submit" name="add_translation" value="Add/Update" class="button-primary">
			</form>
			<hr />
		HTMLBLOCK;
  echo <<<HTMLBLOCK
			<form method="get">
				{$m4is_i796
}
				<input type="hidden" name="page" value="memberium-options">
				<input type="hidden" name="tab" value="language">
				<p>
					<input type="text" name="search" value="{$m4is_c7845
}" placeholder="Search for translations" style="width:250px;"> &nbsp;
					<input type="submit" name="submit" value="Search" class="button-primary">
				</p>
			</form>
		HTMLBLOCK;
 $m4is_o12907 =$this->m4is_o89713($m4is_p250 );
 if(empty($m4is_o12907 )){
echo '<p>No translations found.</p>';
 return;
 
} echo <<<HTMLBLOCK
			<style>
				.memberium_search_results {

				}
				.memberium_search_results td {
					padding: 5px;
				}
				.memberium_search_results tr:nth-child(odd) {
					background-color: #f9f9f9;
				}
				.memberium_search_results td.memberium_delete {
					width: 50px;
				}
				.memberium_search_results td.memberium_id {
					width: 50px;
				}
				.memberium_search_results td.memberium_language {
					width: 125px;
				}
				.memberium_search_results td.memberium_context {
					width: 125px;
				}
				.memberium_search_results td.memberium_original {
					word-wrap: break-word;
					word-break: break-all;
					width: 350px;
				}
				.memberium_search_results td.memberium_new {
					word-wrap: break-word;
					word-break: break-all;
				}
				.memberium_search_results td textarea {
					height: 90%;
					overflow-y: hidden;
					width: 90%;
				}
			</style>
			<form method="post">
				<table class="widefat memberium_search_results">
					<tr>
						<td class="memberium_delete">Delete</td>
						<td class="memberium_id">ID</td>
						<td class="memberium_language">Language</td>
						<td class="memberium_context">Context</td>
						<td class="memberium_original">Original Text</td>
						<td class="memberium_new">Translated Text</td>
					</tr>
					{$m4is_o12907
}
				</table>
				<p>
				<input type="submit" name="update_translations" value="Update">
				</p>
			</form>
			<script>
				function wpal_resize_all_textareas() {
					jQuery('textarea').each(function () {
						this.style.height = 'auto';
						this.style.height = (this.scrollHeight) + 'px';
					});
				}

				jQuery(window).resize( function() { wpal_resize_all_textareas(); } );
				jQuery(document).ready( function() { wpal_resize_all_textareas(); } );
				jQuery('textarea').on( 'input', function () { wpal_resize_all_textareas(); } );
			</script>
		HTMLBLOCK;
 
} private 
function m4is_u64012(): string {
$m4is_e85349 =$this->m4is_o2410();
 $m4is_i46 ='<select name="language">';
 foreach($m4is_e85349 as $m4is_v3458 =>$m4is_k52736 ){
$m4is_i46 .= sprintf('<option value="%s">%s</option>', $m4is_v3458, $m4is_k52736 );
 
}$m4is_i46 .= '</select>';
 return $m4is_i46;
 
} private 
function m4is_o2410(): array {
return ['' =>'Default', 'en' =>'English', 'ar' =>'Arabic', 'cy' =>'Welsh', 'de' =>'German', 'es' =>'Spanish', 'fr' =>'French', 'he' =>'Hebrew', 'hi' =>'Hindu', 'it' =>'Italian', 'ja' =>'Japanese', 'ji' =>'Yiddish', 'ko' =>'Korean', 'po' =>'Polish', 'pt' =>'Portuguese', 'ru' =>'Russian', 'sv' =>'Swedish', 'vi' =>'Vietnamese', 'zh' =>'Chinese', ];
 
} private 
function m4is_v7156($m4is_h718 ): array {
global $wpdb;
 $m4is_m608 =5;
 $m4is_c92430 =empty($m4is_h718 )? "LIMIT {$m4is_m608
}" : '';
 $m4is_x2915 ='%' . $wpdb->esc_like($m4is_h718 ). '%';
 $m4is_v2613 ="SELECT * FROM %i WHERE `context` LIKE %s OR `origtext` LIKE %s OR `value` LIKE %s ORDER BY id DESC {$m4is_c92430
}";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $this->m4is_l07426, $m4is_x2915, $m4is_x2915, $m4is_x2915 );
 $m4is_d5472 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 return is_array($m4is_d5472 )? $m4is_d5472 : [];
 
} private 
function m4is_o89713(array $m4is_p250 ): string {
$m4is_i46 ='';
 foreach($m4is_p250 as $m4is_g91703 ){
$m4is_d07693 =esc_html($m4is_g91703['id']);
 $m4is_s90 =esc_html($m4is_g91703['context']);
 $m4is_b5246 =esc_html($m4is_g91703['origtext']);
 $m4is_v586 =esc_html($m4is_g91703['value']);
 $m4is_r93654 =empty($m4is_g91703['language'])? '(Unknown)' : $m4is_g91703['language'];
 $m4is_i46 .= <<<HTMLBLOCK
				<tr>
					<td class="memberium_delete">
						<input type="checkbox" name="id[]" value="{$m4is_d07693
}">
					</td>
					<td class="memberium_id">
						{$m4is_d07693
}
					</td>
					<td class="memberium_language">
						{$m4is_r93654
}
					</td>
					<td class="memberium_context">
						{$m4is_s90
}
					</td>
					<td class="memberium_original">
						{$m4is_b5246
}
					</td>
					<td class="memberium_new">
						<textarea name="value[{$m4is_d07693
}]">{$m4is_v586
}</textarea>
					</td>
				</tr>
			HTMLBLOCK;
 
}return $m4is_i46;
 
} 
}

