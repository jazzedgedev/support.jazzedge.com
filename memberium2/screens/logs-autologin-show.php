<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 new m4is_s66();
 final 
class m4is_s66 {
private $m4is_r1546;
 private $m4is_r9613;
 private $m4is_h21895;
 private $m4is_l07426;
 private $m4is_c92430;
 private $m4is_n95268;
 private $m4is_q7962;
 
function __construct(){
$this->m4is_i702();
 $this->m4is_k56();
 $this->m4is_g162();
 
} private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 $this->m4is_l07426 =m4is_q62395::m4is_t66507();
 $this->m4is_h21895 =empty($_GET['contact_id'])? 0 : (int) $_GET['contact_id'];
 $this->m4is_c92430 =empty($_GET['limit'])? 10 : (int) $_GET['limit'];
 $this->m4is_n95268 =empty($_GET['search'])? '' : trim($_GET['search']);
 $this->m4is_q7962 =empty($_GET['start'])? 0 : (int) $_GET['start'];
 
}private 
function m4is_k56(){
$m4is_m615 =$this->m4is_f7108();
 if(!is_array($m4is_m615 )||empty($m4is_m615 )){
echo '<p>The Autologin log is empty.</p>';
 
}else{
$m4is_m125 =get_option('timezone_string' );
 $m4is_m125 =empty($m4is_m125 )? 'UTC' : $m4is_m125;
 $m4is_n5840 =date_default_timezone_get();
 date_default_timezone_set($m4is_m125 );
 echo <<<HTMLBLOCK
				<table class="widefat" style="table-layout:fixed">
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
 echo '<td style="word-wrap: break-word;">', nl2br($m4is_g91703['log']), '</td>';
 echo '</tr>';
 
}echo '</table>';
 date_default_timezone_set($m4is_n5840 );
 
}
} private 
function m4is_f7108(){
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT `id`, UNIX_TIMESTAMP(`time`) AS `time`, `contactid`, `log` FROM %i WHERE `type` = 'autologin' AND `appname` = %s ", $this->m4is_l07426, $this->m4is_r9613 );
 if($this->m4is_h21895 ){
$m4is_v2613 .= $wpdb->prepare(" AND `contactid` = %d ", $this->m4is_h21895 );
 
}if($this->m4is_n95268 ){
$m4is_v2613 .= " AND `log` LIKE '%" . $wpdb->esc_like($this->m4is_n95268 ). "%' ";
 
}$m4is_v2613 .= $wpdb->prepare(" ORDER BY `id` DESC LIMIT %d", $this->m4is_c92430 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 return $m4is_m615;
 
}private 
function m4is_g162(){
echo <<<HTMLBLOCK
			<form method="get" style="margin-top:12px;">
			<input type="hidden" name="page" value="memberium-logs">
			<input type="hidden" name="tab" value="autologin">
			Contact ID: <input type='text' name='contact_id' value='{$this->m4is_h21895
}' placeholder='Contact ID'>
			Search: <input type='text' name='search' value='{$this->m4is_n95268
}' placeholder='Search'>
			Limit: <input type='text' name='limit' value='{$this->m4is_c92430
}' placeholder='# Results'>
			<input type="submit" value="Search" class="button-primary" style="margin-left:15px;">
			</form>

			<form method="post">
			<input type="hidden" name="page" value="memberium-logs">
			<input type="hidden" name="tab" value="autologin">
			<p><input type="submit" name="delete_autologin" value="Delete Log" class="button delete"></p>
			</form>
		HTMLBLOCK;
 
}
}

