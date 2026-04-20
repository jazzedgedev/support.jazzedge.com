<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_b3281::m4is_c961();
 final 
class m4is_b3281 {
private $m4is_r1546;
 private $m4is_r9613;
 private $m4is_j45;
 private $m4is_c92430;
 private $m4is_q7962;
 private $m4is_y193;
 static 
function m4is_c961(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}
function __construct(){
$this->m4is_i702();
 $this->m4is_m01874();
 $this->m4is_g831();
 $this->m4is_g162();
 
}private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 $this->m4is_j45 ='';
 $this->m4is_c92430 =15;
 $this->m4is_q7962 =0;
 $this->m4is_y193 ='';
 
}private 
function m4is_m01874(){
$this->m4is_j45 =empty($_GET['ip'])? '' : trim($_GET['ip']);
 $this->m4is_c92430 =empty($_GET['limit'])? 15 : (int) trim($_GET['limit']);
 $this->m4is_q7962 =empty($_GET['start'])? 0 : (int) trim($_GET['start']);
 $this->m4is_y193 =empty($_GET['name'])? '' : strtolower(trim ($_GET['name']));
 
}private 
function m4is_f7108(){
global $wpdb;
 $m4is_v2613 ="SELECT `logintime`, `ipaddress`, `username` FROM %i WHERE `appname` = %s ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_l5841::m4is_h37(), $this->m4is_r9613 );
 if(!empty($this->m4is_y193 )){
$m4is_v2613 .= " AND `username` LIKE '%" . $wpdb->esc_like($this->m4is_y193 ). "%' ";
;
 
}if(!empty($this->m4is_j45 )){
$m4is_v2613 .= " AND `ipaddress` LIKE '%" . $wpdb->esc_like($this->m4is_j45 ). "%' ";
 
}$m4is_v2613 .= $wpdb->prepare(" ORDER BY `id` DESC LIMIT %d, %d", $this->m4is_q7962, $this->m4is_c92430 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 return $m4is_m615;
 
}private 
function m4is_g831(){
$m4is_m615 =$this->m4is_f7108();
 if(is_array($m4is_m615 )&&!empty($m4is_m615 )){
$m4is_a9317 =[];
 $m4is_m125 =get_option('timezone_string');
 $m4is_n5840 =date_default_timezone_get();
 if(!empty($m4is_m125)){
date_default_timezone_set($m4is_m125 );
 
}echo <<<HTMLBLOCK
				<table class="widefat">
					<tr>
						<td width="150">Login Time</td>
						<td width="125">IP Address</td>
						<td>Username</td>
						<td>Location</td>
					</tr>
			HTMLBLOCK;
 foreach($m4is_m615 as $m4is_g91703 ){
$m4is_n9130 =$m4is_g91703['ipaddress'];
 if(!isset($m4is_a9317[$m4is_n9130])){
$m4is_a9317[$m4is_n9130]=m4is_q6082::m4is_a67($m4is_n9130 );
 
}$m4is_t082 =date('Y-m-d H:i:s', $m4is_g91703['logintime']);
 $m4is_n9130 =$m4is_g91703['ipaddress'];
 $m4is_y193 =$m4is_g91703['username'];
 echo <<<HTMLBLOCK
					<tr>
						<td>{$m4is_t082
}</td>
						<td><a href="https://geoiptool.com/en/?ip={$m4is_n9130
}" target="geoip">{$m4is_n9130
}</a></td>
						<td>{$m4is_y193
}</td>
						<td>
				HTMLBLOCK;
 if(!empty($m4is_a9317[$m4is_n9130]['latitude'])){
echo <<<HTMLBLOCK
						<a target="map" href="https://www.google.com/maps/@{$m4is_a9317[$m4is_n9130]['latitude']
},{$m4is_a9317[$m4is_n9130]['longitude']
},13z">
							<em class="fa fa-map-marker"></em>
						</a>&nbsp;
					HTMLBLOCK;
 
}if(isset($m4is_a9317[$m4is_n9130])&&is_array($m4is_a9317[$m4is_n9130])){
if(isset($m4is_a9317[$m4is_n9130]['city'])){
echo $m4is_a9317[$m4is_n9130]['city'], ', ', $m4is_a9317[$m4is_n9130]['region_name'], ' ', $m4is_a9317[$m4is_n9130]['country_name'];
 
}
}else{
echo '<em>Unknown</em>';
 
}echo <<<HTMLBLOCK
						</td>
					</tr>
				HTMLBLOCK;
 
}date_default_timezone_set($m4is_n5840 );
 echo '</table>';
 
}
}private 
function m4is_g162(){
$m4is_a9058 =m4is_l5841::m4is_e3216();
 echo <<<HTMLBLOCK
			<p>
				{$m4is_a9058
} login entries found.
			</p>
			<form method="get" style="margin-top:12px;display:inline-block;">
				<input type="hidden" name="page" value="memberium-logs">
				Username: <input type='text' name='name' value='{$this->m4is_y193
}' placeholder='Username'>"
				IP Address: <input type='text' name='ip' value='{$this->m4is_j45
}' placeholder='IP Address'>
				Limit: <input type='text' name='limit' value='{$this->m4is_c92430
}' placeholder='# Results'>
				<input type="submit" value="Search" class="button-primary" style="margin-left:15px;">
			</form>

			<form method="post" style="margin-top:20px;">
				<input type="hidden" name="page" value="memberium-logs">
				<input type="hidden" name="tab" value="login">
				<input type="submit" name="trim_login_log"  value="Trim Log to Last 30 Days" class="submitdelete button" onClick="return confirmDelete()">
				<input type="submit" name="delete_login_log" value="Delete Log" class="submitdelete button" onClick="return confirmDelete()">
			</form>
			<style>
				.submitdelete {
					background-color: red  !important;
					color: white  !important;
				}
			</style>
			<script>
				function confirmDelete() {
					return confirm( 'Are you sure you want to delete entries in this log?  Your deleted log data cannnot be recovered.' );
				}
			</script>
		HTMLBLOCK;
 
}
}

