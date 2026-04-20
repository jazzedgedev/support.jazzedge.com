<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_o0514::m4is_z95();
 final 
class m4is_o0514 {
private $m4is_h21895 =0;
 private $m4is_c92430 =10;
 private $m4is_n95268 ='';
 private $m4is_q7962 =0;
 static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_m01874();
 $this->m4is_k56();
 $this->m4is_e05869();
 
}private 
function m4is_m01874(){
$this->m4is_c92430 =empty($_GET['limit'])? 10 : (int) $_GET['limit'];
 $this->m4is_q7962 =empty($_GET['start'])? 0 : (int) $_GET['start'];
 $this->m4is_n95268 =empty($_GET['search'])? '' : trim($_GET['search']);
 $this->m4is_h21895 =empty($_GET['contact_id'])? 0 : (int) $_GET['contact_id'];
 
}private 
function m4is_i7135(){
global $wpdb;
 $m4is_e80 =m4is_q62395::m4is_t66507();
 $m4is_n27093 =empty($this->m4is_n95268 )? '' : ' AND log like "%' . $wpdb->esc_like($this->m4is_n95268 ). '%" ';
 $m4is_v2613 ="SELECT `id`, UNIX_TIMESTAMP(`time`) as `time`, `contactid`, `log` FROM `{$m4is_e80
}` WHERE `type` = 'httppost' {$m4is_n27093
} ORDER BY `id` DESC LIMIT {$this->m4is_c92430
}";
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 return $m4is_m615;
 
}private 
function m4is_k56(){
$m4is_m615 =$this->m4is_i7135();
 if(!is_array($m4is_m615 )||empty($m4is_m615 )){
echo '<p>The HTTP POST log is empty.</p>';
 
}else{
$m4is_m125 =get_option('timezone_string');
 $m4is_n5840 =date_default_timezone_get();
 date_default_timezone_set($m4is_m125 );
 echo <<<HTMLBLOCK
				<table class="widefat">
				<tr>
				<td width="150">Time</td>
				<td width="100">Contact ID</td>
				<td>Results</td>
				</tr>
			HTMLBLOCK;
 foreach($m4is_m615 as $m4is_g91703 ){
echo '<tr>';
 echo '<td>', date('Y-m-d H:i:s', $m4is_g91703['time']), '</td>';
 echo '<td>', $m4is_g91703['contactid'], '</td>';
 echo '<td>', nl2br($m4is_g91703['log']), '</td>';
 echo '</tr>';
 
}echo '</table>';
 date_default_timezone_set($m4is_n5840 );
 
}
}private 
function m4is_e05869(){
echo <<<HTMLBLOCK
			<form method="get" style="margin-top:12px;">'
			<input type="hidden" name="page" value="memberium-logs">
			<input type="hidden" name="tab" value="httppost">
			Search: <input type='text' name='search' value='{$this->m4is_n95268
}' placeholder='Search'>
			Contact ID: <input type='text' name='contactid' value='{$this->m4is_h21895
}' placeholder='Username'>
			Limit: <input type='text' name='limit' value='{$this->m4is_c92430
}' placeholder='# Results'>
			<input type="submit" value="Search" class="button-primary" style="margin-left:15px;">
			</form>
		HTMLBLOCK;
 
}
}

