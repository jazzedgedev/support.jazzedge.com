<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 new m4is_p34();
 
class m4is_p34 {
private $m4is_r1546, $m4is_r9613, $m4is_j6305, $m4is_c92430, $m4is_x9366, $m4is_n95268, $m4is_q7962;
 public static 
function m4is_c961(){
return new m4is_p34();
 
}
function __construct(){
$this->m4is_v026();
 $this->m4is_h269();
 $this->m4is_c816();
 $this->m4is_c86321();
 $this->m4is_u78316();
 
}private 
function m4is_h269(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_r9613 =$this->m4is_r1546->m4is_i76('appname');
 
}private 
function m4is_c816(): void {
$this->m4is_c92430 =empty($_GET['limit'])? 5 : (int) trim($_GET['limit']);
 $this->m4is_q7962 =empty($_GET['start'])? 0 : (int) trim($_GET['start']);
 $this->m4is_n95268 =empty($_GET['search'])? '' : trim($_GET['search']);
 $this->m4is_j6305 =empty($_GET['ip'])? '' : trim($_GET['ip']);
 $this->m4is_x9366 =$this->m4is_y63();
 
}private 
function m4is_v026(): void {
current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 
}private 
function m4is_y63(){
global $wpdb;
 $m4is_e80 =m4is_q62395::m4is_t66507();
 $m4is_j563 =[];
 $m4is_n27093 ='';
 if(!empty($this->m4is_j6305 )){
$m4is_j563[]=" `ipaddress` LIKE '%" . $wpdb->esc_like($this->m4is_j6305 ). "%' ";
 
}if(!empty($this->m4is_n95268 )){
if(is_numeric($this->m4is_n95268 )){
$m4is_j563[]=" `contactid` = '" . $wpdb->esc_like((int) $this->m4is_n95268 ). "' OR `log` LIKE '%" . $wpdb->esc_like($this->m4is_n95268 ). "%' ";
 
}else{
$m4is_j563[]=" `log` LIKE '%" . $wpdb->esc_like($this->m4is_n95268 ). "%' ";
 
}
}if(!empty($m4is_j563 )){
$m4is_n27093 .= ' AND (' . implode(' AND ', $m4is_j563 ). ' )';
 
}$m4is_v2613 ="SELECT UNIX_TIMESTAMP(`time`) as `time`, `ipaddress`, `contactid`, `log` FROM `{$m4is_e80
}` WHERE `type` = 'loginfail' AND `appname` = '{$this->m4is_r9613
}' {$m4is_n27093
} ORDER BY `id` DESC LIMIT {$this->m4is_c92430
} ;";
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 return $m4is_m615;
 
}private 
function m4is_c86321(){
$m4is_m615 =$this->m4is_x9366;
 if(!is_array($this->m4is_x9366 )||empty($this->m4is_x9366 )){
if(empty($this->m4is_n95268 )&&empty($this->m4is_j6305 )){
echo '<p>The Login Failure log is empty.</p>';
 
}else{
echo '<p>No login failure records were found matching your search.</p>';
 
}
}else{
$m4is_m125 =get_option('timezone_string' );
 $m4is_m125 =empty($m4is_m125 )? 'UTC' : $m4is_m125;
 $m4is_n5840 =date_default_timezone_get();
 date_default_timezone_set($m4is_m125 );
 echo '<table class="widefat">';
 echo '<tr>';
 echo '<td width="175">Login Time</td>';
 echo '<td width="125">IP Address</td>';
 echo '<td width="100">Contact Id</td>';
 echo '<td>Log</td>';
 echo '</tr>';
 foreach ($this->m4is_x9366 as $m4is_g91703 ){
echo '<tr>';
 printf('<td>%s</td>', date('Y-m-d H:i:s', $m4is_g91703['time']));
 printf('<td>%s</td>', $m4is_g91703['ipaddress']);
 printf('<td>%s</td>', $m4is_g91703['contactid']);
 printf('<td>%s</td>', esc_html($m4is_g91703['log']));
 echo '</tr>';
 
}echo '</table>';
 date_default_timezone_set($m4is_n5840 );
 
}
}private 
function m4is_u78316(){
echo <<<HTMLBLOCK
			<form method="get" style="margin-top:12px;display:inline-block;">
				<input type="hidden" name="page" value="memberium-logs">
				<input type="hidden" name="tab" value="loginfail">
				Search: <input type='text' name='search' value='{$this->m4is_n95268
}' placeholder='Search'>
				IP Address: <input type='text' name='ip' value='{$this->m4is_j6305
}' placeholder='IP Address'>
				Limit: <input type='text' name='limit' value='{$this->m4is_c92430
}' placeholder='# Results'>
				<input type="submit" value="Search" class="button-primary" style="margin-left:15px;">
			</form>

			<form method="post" style="margin-top:20px;">
				<input type="hidden" name="page" value="memberium-loginerrors">
				<input type="hidden" name="tab" value="loginerrors">
				<input type="submit" name="delete_loginerror_log" value="Delete Log" class="submitdelete button" onClick="return confirmDelete()">
			</form>

			<style>
				input[name="delete_loginerror_log"]{
					background-color: red  !important;
					color: white  !important;
				}
			</style>

			<script>
				function confirmDelete() {
					return confirm( 'Are you sure you want to delete all the entries in this log?  Your data cannnot be recovered.' );
				}
			</script>
		HTMLBLOCK;
 
}
}

