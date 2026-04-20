<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_u4569 {
private static $m4is_r1546;
 public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 
}   public static 
function m4is_v926(): void {
self::m4is_a03();
 self::m4is_s6437();
 self::m4is_o275();
 self::m4is_x048();
 self::m4is_q66894();
 self::m4is_y831();
 self::m4is_p0653();
 self::m4is_a3827();
 self::m4is_s08693();
 self::m4is_f08();
 self::m4is_c74910();
 self::m4is_e42178();
 self::m4is_v92176();
 self::m4is_f02();
 self::m4is_s72865();
 self::m4is_z8736();
 self::m4is_n25963();
 self::m4is_e58();
 self::m4is_m162();
 
} public static 
function m4is_y6087(): void {
global $wpdb;
 $m4is_v1568 =get_option(m4is_s52::m4is_n34());
 $m4is_h56893 =m4is_s52::m4is_v91686($m4is_v1568 );
 $m4is_m9654 =(boolean) $m4is_h56893['active'];
 $m4is_r6249 =(boolean) $m4is_h56893['valid'];
 $m4is_f14893 =(boolean) $m4is_h56893['trial_mode'];
 $m4is_f166 ='<strong style="color:green;">Yes</strong>';
 $m4is_a760 ='<strong style="color:red;">No</strong>';
 echo '<h3>License Status</h3>';
 echo '<p class="indented">';
 echo '<label>Valid</label>';
 echo '<span>', ($m4is_r6249 ? $m4is_f166 : $m4is_a760 ), '</span><br />';
 echo '<label>Active</label>';
 echo '<span>', ($m4is_m9654 ? $m4is_f166 : $m4is_a760 ), '</span><br />';
 if($m4is_f14893 ){
echo '<label>Test Mode</label>';
 echo '<span><strong style="color:red;">TEST LICENSE MODE</strong></span><br />';
 
}echo '<label>Next Check</label>';
 echo '<span>', date('F jS, Y @ h:i:s', $m4is_h56893['next_check']), '</span><br />';
 echo '<label>Renewal Date</label>';
 echo '<span>', date('F jS, Y @ h:i:s', $m4is_h56893['renewal_date']), '</span><br />';
 echo '</p>';
 echo '<hr />';
 echo '<h3>License Detail</h3>';
 echo '<textarea cols="100" rows="20">';
 foreach ($m4is_h56893 as $m4is_o015 =>$m4is_k72 ){
echo ucwords(str_replace('_', ' ', $m4is_o015 )), ':  ', $m4is_k72, "\n";
 
}echo '</textarea>';
 echo '<p></p>';
 
} public static 
function m4is_s6125(): void {
$m4is_m96240 =self::$m4is_r1546->m4is_j498('memberships' );
 $m4is_f263 =json_encode($m4is_m96240, JSON_PRETTY_PRINT );
 echo <<<HTMLBLOCK
			<h3>Memberships</h3>
			<textarea cols="100" rows="20">{$m4is_f263
}</textarea>
		HTMLBLOCK;
 
} public static 
function m4is_o71664(){
$m4is_i406 =m4is_s52::m4is_f27();
 echo '<label>License Status</label>';
 echo '<span>', (int) $m4is_i406, '</span><br />';
 self::m4is_x139();
 
}    private static 
function m4is_x048(){
echo '<h3>Object Caching</h3>';
 echo '<div class="indented">';
 if(wp_using_ext_object_cache()){
$m4is_e0258 =$GLOBALS['wp_object_cache'];
 $m4is_u83742 =[];
 $m4is_n5398 =get_object_vars($m4is_e0258 );
 $m4is_u83742['misses']=empty($m4is_n5398['cache_misses'])? 0 : $m4is_n5398['cache_misses'];
 $m4is_u83742['hits']=empty($m4is_n5398['cache_hits'])? 0 : $m4is_n5398['cache_hits'];
 $m4is_u83742['client']=empty($m4is_n5398['redis_client'])? 'Unknown' : $m4is_n5398['redis_client'];
 if(function_exists('wp_cache_get_stats' )){
$m4is_n5398 =wp_cache_get_stats();
 echo '<p>Alternate Object Cache Detected (wp_cache_get_stats)</p>';
  
}elseif(method_exists($m4is_e0258, 'getstats' )){
$m4is_n5398 =$GLOBALS['wp_object_cache']->getStats();
 echo '<p>Alternate Object Cache Detected (getStats)</p>';
  
}echo '<p>';
 echo "<strong>Object Cache Client:</strong>  {$m4is_u83742['client']
}<br />";
 echo "<strong>Object Cache Hits:</strong>  {$m4is_u83742['hits']
}<br />";
 echo "<strong>Object Cache Misses:</strong>  {$m4is_u83742['misses']
}</p>";
 echo '</p>';
 
}else{
echo '<p style="color:red;font-weight:bold;">Disabled</p>';
 
}echo '</div>';
 
} private static 
function m4is_s6437(){
$m4is_q87563 =['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR', 'CF-CONNECTING-IP', ];
 echo '<h3>Remote IP Address</h3>';
 echo '<div class="indented">';
 foreach($m4is_q87563 as $m4is_i3410){
if(!empty($_SERVER[$m4is_i3410])){
echo $m4is_i3410, ' - ', $_SERVER[$m4is_i3410], '<br />';
 
}
}echo '</div>';
 
} private static 
function m4is_p0653(){
global $wpdb;
 $m4is_k48 =get_option('memberium_tables', []);
 $m4is_p21 =[];
 if(is_array($m4is_k48)){
foreach($m4is_k48 as $m4is_v379){
  $m4is_v2613 ="SELECT 1 FROM `{$m4is_v379
}` LIMIT 1;";
 $wpdb->get_var($m4is_v2613);
 $m4is_c31 =$wpdb->last_error;
 if($m4is_c31){
$m4is_p21[]="Table '{$m4is_v379
}' : <span style='font-weight:bold;color:red;'>{$m4is_c31
}</span><br />";
 
}
}
}echo '<h3>Missing Database Tables</h3>';
 echo '<div class="indented">';
 if(!empty($m4is_p21)){
foreach($m4is_p21 as $m4is_a291){
echo $m4is_a291, '<br>';
 
}
}else{
echo '<p>None</p>';
 
}echo '</div>';
 
}private static 
function m4is_f02(): void {
echo '<h3>wp-admin php.ini</h3>';
 if(!file_exists(get_home_path(). '/wp-admin/php.ini' )){
echo '<p>Not Found</p>';
 return;
 
}echo '<textarea readonly=readonly cols="100" rows="10">', file_get_contents(get_home_path(). '/wp-admin/php.ini'), '</textarea>';
 
}private static 
function m4is_n25963(): void {
$m4is_e81794 =get_defined_constants(true );
 if(!is_array($m4is_e81794)||empty($m4is_e81794['user'])){
return;
 
}echo '<h3>Constants</h3>';
 echo '<table>';
 ksort($m4is_e81794['user']);
 foreach($m4is_e81794['user']as $m4is_o015 =>$m4is_k72 ){
$m4is_k72 =is_string($m4is_k72 )? $m4is_k72 : serialize($m4is_k72 );
 echo '<tr><td>', $m4is_o015, '</td><td>', $m4is_k72, '</td></tr>';
 
}echo '</table>';
 
}private static 
function m4is_p4286(): string {
$m4is_t0518 ='<strong style="color:red;">Unknown</strong>';
 $m4is_m26 ='/proc/cpuinfo';
 if(!@is_readable($m4is_m26 )){
return $m4is_t0518;
 
}$m4is_n893 =@file($m4is_m26 );
 if(!is_array($m4is_n893 )){
return $m4is_t0518;
 
}foreach($m4is_n893 as $m4is_t42917 ){
if(substr($m4is_t42917, 0, 9 )== 'cpu cores' ){
$m4is_t0518 =intval($m4is_t0518 )+ (int) substr($m4is_t42917, strpos($m4is_t42917, ':' )+ 1 );
 
}
}return $m4is_t0518;
 
}private static 
function m4is_z8736(): void {
echo '<h3>Cron Jobs</h3>';
 echo '<div class="indented">';
 $m4is_h51268 =[];
 $m4is_y025 =get_option('cron', []);
 if(count($m4is_y025)){
foreach ($m4is_y025 as $m4is_q380 =>$m4is_u4290){
if(is_array($m4is_u4290)){
foreach ($m4is_u4290 as $jobname =>$details){
if(stripos($jobname, 'memberium')=== 0){
$m4is_h51268[$jobname]=$m4is_q380;
 
}
}
}
}
}echo '<strong style="margin-left:20px;display:inline-block;width:250px;">Cron Job Name</strong><strong>Next Run Time</strong><br>';
 if(!empty($m4is_h51268)){
foreach ($m4is_h51268 as $m4is_k52736 =>$m4is_a873){
echo '<span style="margin-left:20px;display:inline-block;width:250px;">', $m4is_k52736, '</span>', date("Y-m-d h:i:s", $m4is_a873), '<br>';
 
}
}echo '</div>';
 
}private static 
function m4is_a3827(): void {
echo '<h3>CURL Library Support</h3>';
 echo '<div class="indented">';
 if(function_exists('curl_version')){
$m4is_y76 =curl_version();
 echo '<strong>Version</strong>: ', $m4is_y76['version'], '<br />';
 echo '<strong>Version Number</strong>: ', $m4is_y76['version_number'], '<br />';
 echo '<strong>SSL Version</strong>: ', $m4is_y76['ssl_version'], '<br />';
 echo '<strong>LibZ Version</strong>: ', $m4is_y76['libz_version'], '<br />';
 echo '<strong>Age</strong>: ', $m4is_y76['age'], '<br />';
 
}else{
echo '<p style="color:red;font-weight:bold;">CURL Library Support Missing</p>';
 
}echo '</div>';
 
}private static 
function m4is_y831(): void {
global $wpdb;
 echo '<h3>MySQL Configuration</h3>';
 echo '<div class="indented">';
 echo '<h3>Database Storage Engines:</h3>';
 $m4is_v2613 ='SHOW STORAGE ENGINES;';
 $m4is_d5472 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 echo '<table>';
 echo '<tr>';
 echo '<td>Engine Name</td>';
 echo '<td>Support</td>';
 echo '<td>Comments / Description</td>';
 echo '</tr>';
 foreach($m4is_d5472 as $m4is_u6591){
echo '<tr>';
 echo '<td>', $m4is_u6591['Engine'], '</td>';
 echo '<td>', $m4is_u6591['Support'], '</td>';
 echo '<td>', $m4is_u6591['Comment'], '</td>';
 echo '</tr>';
 
}echo '</table>';
 echo '<h3>Cache Configuration</h3>';
 $m4is_v2613 ="show variables like 'have_query_cache';";
 $m4is_u6591 =$wpdb->get_results($m4is_v2613);
 echo '<strong>', $m4is_u6591[0]->Variable_name, '</strong> = ', $m4is_u6591[0]->Value, '<br>';
 $m4is_v2613 ="show variables like '%query%';";
 $m4is_d5472 =$wpdb->get_results($m4is_v2613);
 foreach($m4is_d5472 as $m4is_u6591){
echo '<strong>', $m4is_u6591->Variable_name, '</strong> = ', $m4is_u6591->Value, '<br>';
 
}echo '</div>';
 
}private static 
function m4is_e58(){
$m4is_h7658 =defined('MEMBERIUM_DEBUGLOG' )? constant('MEMBERIUM_DEBUGLOG' ): '';
 echo '<h3>Debug Log:</h3>';
 if(file_exists($m4is_h7658 )){
$m4is_e53706 =(0 - min(filesize($m4is_h7658), MB_IN_BYTES));
 $m4is_e53706 =$m4is_e53706 < 0 ? 0 : $m4is_e53706;
 echo '<form method="post" action=""><input type="submit" name="delete-debug" value="Delete Debug Log"></form>';
 echo '<textarea readonly=readonly cols="100" rows="20">', file_get_contents($m4is_h7658, false, null, $m4is_e53706), '</textarea>';
 
}else{
echo '<p style="color:red;font-weight:bold;">No debug log found.</p>';
 
}
}private static 
function m4is_m162(){
$m4is_g26713 =ini_get('error_log' );
 if(!empty($m4is_g26713)&&file_exists($m4is_g26713)){
$m4is_i506 =(int) filesize($m4is_g26713);
 $m4is_p3275 =10 * KB_IN_BYTES;
  $m4is_e53706 =0 - $m4is_p3275;
 $m4is_e53706 =$m4is_e53706 < 0 ? 0 : $m4is_e53706;
 echo '<h3>PHP Error Log:</h3>';
 if($m4is_g26713 ){
echo '<textarea readonly=readonly cols="100" rows="20">', file_get_contents($m4is_g26713, false, null, $m4is_e53706, $m4is_p3275 + 1024 ), '</textarea>';
 
}else{
echo '<p style="color:red;font-weight:bold;">No error log found.</p>';
 
}
}
}private static 
function m4is_c74910(){
echo '<h3>.htaccess</h3>';
 if(file_exists(get_home_path(). '.htaccess')){
echo '<textarea readonly=readonly cols="100" rows="10">', file_get_contents(get_home_path(). '.htaccess'), '</textarea>';
 
}
}private static 
function m4is_v92176(){
echo '<h3>Main php.ini</h3>';
 if(file_exists(get_home_path(). 'php.ini')){
echo '<textarea readonly=readonly cols="100" rows="10">', file_get_contents(get_home_path(). 'php.ini'), '</textarea>';
 
}else{
echo '<p>Not Found</p>';
 
}
}private static 
function m4is_s08693(){
$m4is_k87416 =['licenseserver.webpowerandlight.com', ];
 $m4is_z47 =false;
 echo '<h3>Network</h3>';
 echo '<div class="indented">';
 echo '<h3>License Servers</h3>';
 echo '<div class="indented">';
 foreach ($m4is_k87416 as $m4is_h540){
$m4is_j45 =gethostbyname($m4is_h540);
 if($m4is_h540 == $m4is_j45){
$m4is_j45 ='<span style="color:red;">Failed To Resolve</span>';
 $m4is_z47 =true;
 
}else{
$m4is_j45 ='<span style="color:green;">' . $m4is_j45 . '</span>';
 
}echo '<p>', $m4is_h540, ' resolves to <strong>', $m4is_j45, '</strong></p>';
 
}if($m4is_z47){
echo '<p style="color:red;">One or more hostnames failed to resolve due to DNS issues.</p>';
 
}$m4is_z47 =false;
 $m4is_q6802 =['https://licenseserver.webpowerandlight.com/getlicense.php', 'https://licenseserver.webpowerandlight.com/memberium-is/current-version.php', ];
 foreach ($m4is_q6802 as $m4is_n6062){
$m4is_u6591 =wp_remote_get($m4is_n6062 );
 if(is_array($m4is_u6591)){

}else{
echo '<p>', $m4is_u6591->errors['http_request_failed'][0], '</pre>';
 $m4is_z47 =true;
 
}
}if(!$m4is_z47){
echo '<p><strong style="color:green;">Connection Successful</strong></p>';
 
}echo '</div>';
 
}static 
function m4is_e42178(){
$m4is_t798 =ini_get_all();
 if(!empty($m4is_t798 )){
ksort($m4is_t798 );
 echo '<h3>PHP .ini Settings</h3>';
 echo '<table>';
 foreach($m4is_t798 as $m4is_o015 =>$m4is_k72 ){
$m4is_d87521 =isset($m4is_k72['local_value'])? $m4is_k72['local_value']: $m4is_k72['global_value'];
 $m4is_d87521 =isset($m4is_d87521 )? $m4is_d87521 : '';
 if(strpos($m4is_d87521, ',')!== false ){
$m4is_d87521 =implode(', ', array_filter(explode(',', $m4is_d87521 )));
 
}if(!empty($m4is_k72['local_value'])||!empty($m4is_k72['global_value'])){
echo '<tr><td>', $m4is_o015, '</td><td>', $m4is_d87521, '</td></tr>';
 
}
}echo '</table>';
 
}
}private static 
function m4is_x139(){
$m4is_q863 =get_option('memberium/activation_log', '' );
 echo '<h3>Activation Log</h3>';
 echo '<textarea readonly=readonly cols="100" rows="10">', $m4is_q863, '</textarea>';
 
}static 
function m4is_o275(){
echo '<h3>PHP Modules Loaded</h3>';
 echo '<div class="indented">';
 echo '<div style="width:600px;">', implode(', ', get_loaded_extensions()), '</div>';
 echo '</div>';
 
}static 
function m4is_x87646(){
$m4is_e0213 =self::$m4is_r1546->m4is_j498('settings');
 echo '<h3>Settings</h3>';
 echo '<textarea cols="100" rows="20">', json_encode($m4is_e0213, JSON_PRETTY_PRINT), '</textarea>';
 
}static 
function m4is_q66894(){
if(function_exists('sys_getloadavg')){
$m4is_t89405 =sys_getloadavg();
 if(is_array($m4is_t89405)&&!empty($m4is_t89405)){
echo '<h3>System Load</h3>';
 echo '<div class="indented">';
 echo '<strong>Last Minute</strong>:  ', $m4is_t89405[0], '<br>';
 echo '<strong>Last 5 Minutes</strong>:  ', $m4is_t89405[1], '<br>';
 echo '<strong>Last 15 Minutes</strong>:  ', $m4is_t89405[2], '<br>';
 
}else{
echo '<p style="font-weight:bold;color:red;">Operating System Load Average Report Empty.</p>';
 
}
}else{
echo '<p style="font-weight:bold;color:red;">Operating System missing Load Average Reports.</p>';
 
}echo '</div>';
 echo '<p></p>';
 
}static 
function m4is_a03(){
global $wpdb;
 $m4is_w1960 =self::$m4is_r1546->m4is_g316(). 'build_id.dat';
 $m4is_q413 =file_exists($m4is_w1960 )? file_get_contents($m4is_w1960 ): 'Unknown';
 $m4is_t0518 =self::m4is_p4286();
 $m4is_g645 =php_uname('m');
 $m4is_w03569 =$wpdb->db_version();
 $m4is_v32 =php_uname('n');
 $m4is_z697 =php_uname('s');
 $m4is_o526 =php_uname('v');
 $m4is_t24096 =phpversion();
 $m4is_w16 =php_sapi_name();
 $m4is_i1656 =isset($_SERVER['SERVER_SOFTWARE'])? $_SERVER['SERVER_SOFTWARE']: '<strong style="color:red;">Unknown</strong>';
 $m4is_l17096 =function_exists('get_current_user' )? get_current_user(): 'Unknown';
 require ABSPATH . WPINC . '/version.php';
 $m4is_k610 =$wp_version;
 echo '<h3>Build Info</h3>';
 echo '<div class="indented">';
  echo "<strong>" . nl2br($m4is_q413 ). "</strong><br>";
 echo '</div>';
 echo '<h3>System Info</h3>';
 echo '<div class="indented">';
 echo "<strong>Hostname</strong>:  {$m4is_v32
}<br>";
 echo "<strong>CPU Type</strong>:  {$m4is_g645
}<br>";
 echo "<strong>CPU Cores</strong>:  {$m4is_t0518
}<br>";
 echo "<strong>Operating System</strong>:  {$m4is_z697
}<br>";
 echo "<strong>Web Server Type</strong>:  {$m4is_i1656
}<br>";
 echo "<strong>Username</strong>:  {$m4is_l17096
}<br>";
 echo "<strong>SAPI</strong>:  {$m4is_w16
}<br>";
 echo "<strong>PHP Version</strong>:  {$m4is_t24096
}<br>";
 echo "<strong>WordPress Version</strong>:  {$m4is_k610
}<br>";
 echo "<strong>Database Version</strong>:  {$m4is_w03569
}<br>";
 echo '<p></p>';
 echo '<strong>Home Path</strong>:  ', get_home_path(), '<br>';
 echo '<strong>Install Point</strong>: ', dirname(MEMBERIUM_HOME), '<br>';
 echo '<p></p>';
 echo '<strong>Remote Address</strong>:  ', $_SERVER['REMOTE_ADDR'], '<br>';
 if(isset($_SERVER['HTTP_X_REAL_IP'])){
echo '<strong>Real IP</strong>:  ', $_SERVER['HTTP_X_REAL_IP'], '<br>';
 
}if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
echo '<strong>Forwarded For</strong>:  ', $_SERVER['HTTP_X_FORWARDED_FOR'], '<br>';
 
}if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])){
echo '<strong>Cloudflre Connecting IP</strong>:  ', $_SERVER['HTTP_CF_CONNECTING_IP'], '<br>';
 
}if(isset($_SERVER['HTTP_X_SUCURI_CLIENTIP'])){
echo '<strong>Sucuri Client IP</strong>:  ', $_SERVER['HTTP_X_SUCURI_CLIENTIP'], '<br>';
 
}echo '<p></p>';
 echo '</div>';
 
}static 
function m4is_f08(){
echo '</div>';
  return;
 echo '<h3>TLS Support</h3>';
 echo '<div class="indented">';
 echo '<h3>CURL SSL TLS 1.2 Test</h3>';
 $m4is_s615 =curl_init('https://tlstest.paypal.com/');
 curl_setopt($m4is_s615, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($m4is_s615, CURLOPT_CONNECTTIMEOUT, 2);
 $m4is_s615 =self::$m4is_r1546->m4is_u76968($m4is_s615);
 $m4is_u6591 =curl_exec($m4is_s615);
  echo '<strong>PayPal Test</strong><br />';
 echo $m4is_u6591, '<br /><br />';
 $m4is_s615 =curl_init('https://www.howsmyssl.com/a/check');
 curl_setopt($m4is_s615, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($m4is_s615, CURLOPT_CONNECTTIMEOUT, 2);
 $m4is_s615 =m4is_r83::m4is_c26()->m4is_u76968($m4is_s615);
 $m4is_u6591 =curl_exec($m4is_s615);
  $m4is_u6591 =json_decode($m4is_u6591);
 if(is_object($m4is_u6591)){
echo '<strong>SSL Protocol Test</strong><br />';
 echo '<strong>TLS Version</strong>: ', $m4is_u6591->tls_version, '<br />';
 echo '<strong>TLS Compression</strong>: ', $m4is_u6591->tls_compression_supported ? 'Yes' : 'No', '<br />';
 if(is_array($m4is_u6591->given_cipher_suites)){
echo '<strong>Cipher Suites</strong>: ', implode(', ', $m4is_u6591->given_cipher_suites), '<br>';
 
}
}else{
echo '<p style="color:red;font-weight:bold;">SSL Protocol Test Failed</p>';
 
}echo '</div>';
 echo '</div>';
 
}static 
function m4is_s72865(){
echo '<h3>wp-config.php:</h3>';
 if(file_exists(get_home_path(). 'wp-config.php')){
echo '<textarea readonly=readonly cols="100" rows="10">', file_get_contents(get_home_path(). 'wp-config.php'), '</textarea>';
 
}
} 
}

