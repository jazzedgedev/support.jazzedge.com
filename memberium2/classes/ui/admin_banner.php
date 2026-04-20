<?php

/**
 * Proprietary Software - All Rights Reserved
 *
 * This file is part of the Memberium plugin, which is proprietary software developed by Web Power and Light.
 * Unauthorized copying, distribution, or modification of this file, via any medium, is strictly prohibited.
 *
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 *
 * For licensing information, please contact Web Power and Light.
 */


 class_exists('m4is_s6729' )||die();
  final 
class m4is_c89756 {
private static $m4is_r1546;
 public static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 
}static 
function m4is_z74(){
$m4is_c36751 =get_bloginfo('version' );
 $m4is_s89016 =version_compare($m4is_c36751, '6.2', 'ge' );
  self::m4is_p583();
 self::m4is_v019();
  self::m4is_u6687();
 self::m4is_z264();
 self::m4is_p43();
 self::m4is_t894();
 self::m4is_c31879();
 self::m4is_t93();
 self::m4is_m0612();
   self::m4is_k7829();
 self::m4is_u66403();
  self::m4is_v420();
 self::m4is_x28();
 self::m4is_e4283();
 self::m4is_l305();
 self::m4is_j538();
 if(!$m4is_s89016){
 self::m4is_z264();
 self::m4is_r46203();
  self::m4is_j68();
 self::m4is_t79();
 
}
} private static 
function m4is_s762(){
return base64_decode('aHR0cHM6Ly9tZW1iZXJpdW0uY29tLw==');
  
}  private static 
function m4is_p583(){
if(m4is_s52::m4is_b12067(['payf'])){
$m4is_c67 ='<strong><a href="https://memberium.com/members/" target="_blank">https://memberium.com/members/</a></strong>';
 $m4is_a173 ='<h3>There\'s been a failed payment with your Memberium account.</h3>';
 $m4is_a173 .= "<p>To make sure your membership site or course stays up and running, please have the primary account holder log in to {$m4is_c67
} and update a new card.</p>";
 $m4is_a173 .= '<p>Otherwise, you risk having your Memberium site shut off due to non-payment.</p>';
 $m4is_a173 .= '<p>If you need any help, please email <a href="mailto:support@memberium.com" target="_blank">support@memberium.com</a> for assistance.</p>';
 $m4is_a173 .= '<p><form method="post" target="_blank" action="https://memberium.com/members/"><input type="submit" value="Update Your Memberium Account Here"></form></p>';
 $m4is_s6347 ='notice notice-error';
 echo "<div class='{$m4is_s6347
}'>{$m4is_a173
}</div>";
 
}
}private static 
function m4is_i03548(){
$m4is_y025 =get_option('cron', []);
 $m4is_g39876 =['memberium_maintenance' =>1, ];
 foreach($m4is_y025 as $m4is_o015 =>$m4is_k72){
if(is_array($m4is_k72)){
foreach($m4is_k72 as $k2 =>$v2){
unset($m4is_g39876[$k2]);
 
}
}
}if(!empty($m4is_g39876)){
$m4is_a173 ='<h3>Cron Failure</h3>' . '<p>Memberium has detected that your WordPress maintenance system is experiencing problems.  ' . 'This may cause problems getting software updates, renewing your license, generating member passwords, or other maintenance functions.</p>' . '<p>The following Memberium cron functions are not being run:</p>';
 foreach($m4is_g39876 as $m4is_o015 =>$m4is_k72){
$m4is_a173 .= "<p>{$m4is_o015
}</p>";
 
}$m4is_a173 .= 'If this message does not go away after a few page loads, then please contact <a href="https://memberium.com/support/">Memberium support</a> for assistance to further diagnose and fix this issue.</p>';
 $m4is_s6347 ='notice notice-error';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}private static 
function m4is_v019(){
$m4is_y1662 =get_option('default_role', true);
 $m4is_m3605 =get_role($m4is_y1662)->capabilities;
 $m4is_w0867 =false;
 $m4is_z96210 ='';
 $m4is_y51780 =['activate_plugins', 'customize', 'edit_others_pages', 'edit_others_posts', 'edit_pages', 'manage_options', 'moderate_comments', 'promote_users', 'switch_themes', 'update_plugins', ];
 foreach($m4is_y51780 as $m4is_z75){
if(!empty($m4is_m3605[$m4is_z75])){
$m4is_z96210 =$m4is_z75;
 $m4is_w0867 =true;
 break;
 
}
}if($m4is_w0867){
$message ='<h3>Default Role Problem</h3>';
 $message .= "<p>Your default role ({$m4is_y1662
}) for new users is set too high.  New users will be created with administrative access.</p>";
 $message .= "<p>Please review your default user role under Settings->General in WordPress.</p>";
 $message .= "<p>Found Capabililty:  {$m4is_z75
}</p>";
 $class ='notice notice-error';
 echo "<div class='{$class
}'>{$message
}</div>";
 
}
}private static 
function m4is_u6687(){
if(self::$m4is_r1546->m4is_e58704()!== 'production'){
return;
 
}if(defined('MEMBERIUM_BETA' )&&constant('MEMBERIUM_BETA' )== 1 ){
$m4is_a173 ='<h3>Development Mode</h3>' . '<p>Memberium is running in development mode.</p>';
 $m4is_s6347 ='notice notice-info';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}private static 
function m4is_z264(){
if(defined('MEMBERIUM_DEBUG' )&&constant('MEMBERIUM_DEBUG' )){
return;
 
}if(self::$m4is_r1546->m4is_e58704()!== 'production' ){
return;
 
}if(in_array(strtolower(ini_get('display_errors')), [1, 'on', 'true'])){
$m4is_a173 ='<h3>Security Vulnerability Detected</h3>' . '<p>Memberium has detected that your website or web hosting is misconfigured to display errors to the browser.</p>' . '<p>This can be caused by several things, including a misconfigured cpanel, php.ini setting, or another ' . 'plugin or theme on your site turning the setting back on.</p>' . '<p>Leaving this setting in place will create a security risk for your server, as well as potential problems with logins and cookies failing.</p>' .  '<p>Both display_errors should be off, and WP_DEBUG should be set to false.</p>' . '<p>Please contact your web host, or your web developer for assistance to fix this issue.</p>';
 $m4is_s6347 ='notice notice-error';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}private static 
function m4is_p43(): void {
$m4is_b783 =constant('MEMBERIUM_HOME' );
 $m4is_l9671 ='file_permissions';
 if(is_writable($m4is_b783 )){
return;
 
}if(get_option("memberium/notice/{$m4is_l9671
}", false )){
return;
 
}$m4is_c67 =m4is_h65::m4is_q62087($m4is_l9671);
 $m4is_s6347 ='notice notice-warning';
 $m4is_a173 ='<h3>File Permissions</h3>' . '<p>Memberium updates disabled due to lack of file permissions.  Please contact your webhost for assistance with setting proper directory permissions.</p>' . '<div><p><a class="button-primary" href="'. $m4is_c67 .'">Hide this Notice</a></p></div>';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}private static 
function m4is_r46203(){
 if(defined('WP_HTTP_BLOCK_EXTERNAL')&&constant('WP_HTTP_BLOCK_EXTERNAL')== 1){
$m4is_x60591 =defined('WP_ACCESSIBLE_HOSTS')? strtolower(constant('WP_ACCESSIBLE_HOSTS')): '';
 $m4is_p89401 =array_filter(explode(',', $m4is_x60591));
 $m4is_p89401 =array_filter($m4is_p89401);
 $m4is_r9613 =self::$m4is_r1546->m4is_i76('appname');
 $m4is_w0275 =['licenseserver.webpowerandlight.com', "{$m4is_r9613
}.infusionsoft.com" ];
 foreach($m4is_w0275 as $k =>$m4is_k72){
if(in_array($m4is_k72, $m4is_p89401)){
unset($m4is_w0275[$k]);
 
}
}if(!empty($m4is_w0275)){
$m4is_k87416 =trim($m4is_x60591 . "," . implode(',', $m4is_w0275), ',');
 $m4is_s6347 ='notice notice-error';
 $m4is_a173 ='<h3>External Hosts Blocked</h3>' . '<p>Memberium has detected that you are blocking access to external hosts, through your wp-config.php file.' . 'You will either need to remove the <strong>WP_HTTP_BLOCK_EXTERNAL</strong> setting, or add our hosts to the <strong>WP_ACCESSIBLE_HOSTS</strong> setting.' . 'Leaving this problem unaddressed will cause your plugin to stop working.</p>' . "<p style='font-family:\"courier new\",monospace;font-size:120%;'>define('WP_ACCESSIBLE_HOSTS', '" . $m4is_k87416 . "');</p>" .  '<p>If you would like assistance, please contact your web host or web developer to get the block removed.</p>';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}
}private static 
function m4is_c31879(){
 $m4is_h56893 =m4is_s52::m4is_v91686();
 $m4is_o280 =m4is_s52::m4is_s326();
 if(!$m4is_h56893['valid']){
$m4is_a173 ='<h3>License not Found</h3>' . '<p>You do not yet have a valid <em>Memberium</em> license for this install.</p>' . '<p>If you own a license and this is a new install, please <a href="admin.php?page=memberium-support&tab=dashboard">click here</a> to go to the dashboard and click the "Renew License" button.</p>' . '<p>If this does not resolve the problem, please contact Memberium support and provide your site URL and app name.</p>' . '<p>If you have not yet purchased a license, please <a href="https://memberium.com/pricing/" target="_blank">click here</a>.</p>';
 $m4is_s6347 ='notice notice-error';
 
}else{
if(!$m4is_h56893['active']){
$m4is_a173 ='<h3>License Inactive</h3>' . '<p>You do not yet have a valid <em>Memberium</em> license for this install.</p>' . '<p>If you own a license and this is a new install, please <a href="admin.php?page=memberium-support&tab=dashboard">click here</a> to go to the dashboard and click the "Renew License" button.</p>' . '<p>If this does not resolve the problem, please contact Memberium support and provide your site URL and app name.</p>' . '<p>If you have not yet purchased a license, please <a href="https://memberium.com/pricing/" target="_blank">click here</a>.</p>';
 $m4is_s6347 ='notice notice-error';
 
}$m4is_f96267 =round($m4is_o280 / $m4is_h56893['max_users'], 2)* 100;
 $m4is_x66975 =$m4is_h56893['max_users']- $m4is_o280;
 if($m4is_h56893['trial_mode']){
$m4is_a173 ='<h3>Test License</h3>' . "<p>Your site currently has a <em>Memberium</em> test license limited to {$m4is_h56893['max_users']
} users.  This license may not be eligible for support.</p>";
 $m4is_s6347 ='notice notice-error';
 
}if($m4is_f96267 > 50 &&$m4is_f96267 <= 100){
$m4is_a173 ='<h3>User Capacity Warning</h3>' . "<p>Your current <em>Memberium</em> license is for {$m4is_h56893['max_users']
} users.</p>" . "<p>Your site currently has {$m4is_o280
} users, and is at {$m4is_f96267
}% capacity.  You have only {$m4is_x66975
} user seats left.</p>" . '<p>In order to avoid any service interruptions, please consider <a href="https://memberium.com/support/" target="_blank">contacting support</a> to upgrade to an unlimited user Domain or Pro license.</p>';
 $m4is_s6347 ='notice notice-warning';
 
}elseif($m4is_f96267 > 100){
$m4is_a173 ='<h3>User Limit Exceeded</h3>' . "<p>Your current <em>Memberium</em> license is for {$m4is_h56893['max_users']
} users.</p>" . "<p>Your site currently has {$m4is_o280
} users, and is over capacity.  Member logins are disabled.</p>" . '<p>In order to reactivate member logins, please either delete users or <a href="https://memberium.com/support/" target="_blank">contact support</a> to upgrade to an unlimited user Domain or Pro license.</p>';
 $m4is_s6347 ='notice notice-error';
 
}
}if(!empty($m4is_a173)){
echo "<div class='{$m4is_s6347
}'>{$m4is_a173
}</div>";
 
}
} private static 
function m4is_t894(){
if(defined('LEARNDASH_VERSION' )){
$m4is_a6814 =LEARNDASH_VERSION;
 $m4is_l9671 ='learndash_version_' . $m4is_a6814;
 if(get_option("memberium/notice/{$m4is_l9671
}", false )){
return;
 
}if(version_compare($m4is_a6814, 3, '<')){
$m4is_c67 =m4is_h65::m4is_q62087($m4is_l9671 );
 $m4is_a173 ='<h3>Oudated LearnDash Version Detected</h3>' . "<p>Memberium has detected that you are running an old version ({$m4is_a6814
}) of LearnDash.</p>" . "<p>Please update your LearnDash plugin to version 3.0 or later.</p>" . '<div><p><a class="button-primary" href="'. $m4is_c67 .'">Hide this Notice</a></p></div>';
 $m4is_s6347 ='notice notice-error';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}
}private static 
function m4is_t93(){
if(!extension_loaded('mbstring')){
$m4is_s6347 ='notice notice-info';
 $m4is_a173 ='<h3>Multibyte Encoding</h3>' . "<p>We've detected that your server is missing the PHP MBString module.  " . "The MBString module makes it possible ot handle acccented and international characters without corruption.</p>" . '<p>Memberium can run without this module, but it may cause data corruption from Keap if the records contain international characters.</p>' . '<p>To fix this issue please contact your webhost and have them add MBString support to your PHP system.</p>';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}private static 
function m4is_m0612(){
 global $wpdb;
 $m4is_v2613 ='SHOW STORAGE ENGINES;';
 $m4is_d5472 =$wpdb->get_results($m4is_v2613, ARRAY_A);
 $m4is_i6395 =false;
 $m4is_l9653 =false;
 if(is_array($m4is_d5472)){
foreach($m4is_d5472 as $m4is_g91703){
$m4is_g91703['Engine']=strtolower($m4is_g91703['Engine']);
 $m4is_g91703['Support']=strtolower($m4is_g91703['Support']);
 if($m4is_g91703['Engine']== 'innodb' &&in_array($m4is_g91703['Support'], ['yes', 'default'])){
$m4is_l9653 =true;
 
}elseif($m4is_g91703['Engine']== 'myisam' &&in_array($m4is_g91703['Support'], ['yes', 'default'])){
$m4is_i6395 =true;
 
}
}
}if((!$m4is_i6395)||(!$m4is_l9653)){
$m4is_s6347 ='notice notice-error';
 $m4is_a173 ='<h3>Database Configuration Error</h3>' . '<p>Memberium has detected a database configuration error.  One or more of your database engines appears to be missing or disabled.</p>';
 if(!$m4is_i6395){
$m4is_a173 .= '<p>Your <strong>MyISAM</strong> database storage engine is missing or disabled.</p>';
 
}if(!$m4is_l9653){
$m4is_a173 .= '<p>Your <strong>InnoDB</strong> database storage engine is missing or disabled.</p>';
 
}$m4is_a173 .= '<p>Please contact your webhost and notify them of this problem.  Proper operation of your Membership site will be affected until this is fixed.</p>';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
} private static 
function m4is_q5726(){
  $m4is_t24096 =phpversion();
 $m4is_l9671 ='php_version_' . $m4is_t24096;
 if(get_option("memberium/notice/{$m4is_l9671
}", false)){
return;
 
}$m4is_i05864 =false;
 $m4is_v43021 =[];
 $m4is_i96 =['7.4' =>['active_support' =>'November 28, 2019', 'security_support' =>'November 28, 2022'], '7.3' =>['active_support' =>'December 6, 2020', 'security_support' =>'December 6, 2021'], '7.2' =>['active_support' =>'November 30, 2019', 'security_support' =>'December 30, 2020'], '7.1' =>['active_support' =>'December 1, 2018', 'security_support' =>'December 1, 2019'], '7.0' =>['active_support' =>'December 3, 2017', 'security_support' =>'December 3, 2018'], '5.6' =>['active_support' =>'December 31, 2016', 'security_support' =>'December 31, 2018'], '5.5' =>['active_support' =>'July 21, 2016', 'security_support' =>'July 21, 2016'], '5.4' =>['active_support' =>'September 3, 2015', 'security_support' =>'September 3, 2015'], '5.3' =>['active_support' =>'August 14, 2014', 'security_support' =>'August 14, 2014'], ];
 foreach($m4is_i96 as $m4is_a6814 =>$m4is_z8669){
if(version_compare($m4is_t24096, $m4is_a6814, '>=')){
$m4is_v43021 =$m4is_z8669;
 $m4is_v43021['version']=$m4is_a6814;
 $m4is_e34 =(bool) (time()< strtotime($m4is_z8669['security_support']));
 $bugfix_updates =(bool) (time()< strtotime($m4is_z8669['active_support']));
 $m4is_i05864 =(bool) ($m4is_e34 ||$bugfix_updates);
 break;
 
}
}if(!$m4is_i05864){
$m4is_s6347 ='notice notice-warning';
 $m4is_a173 ='<h3>Older PHP Version Detected</h3>' . '<p>Memberium has detected that you are running an older version of PHP (<strong>PHP ' . $m4is_t24096 . '</strong>).' . '<p>You can learn more about <a href="https://secure.php.net/supported-versions.php" target="_blank">PHP support here</a></p>';
 if(!$m4is_e34){
$m4is_a173 .= '<p>Support for this version of PHP was terminated on <strong>' . $m4is_z8669['security_support']. '</strong></p>';
 $m4is_a173 .= '<p>Upgrading PHP is <em style="font-weight:bold;color:red;">strongly recommended</em> for best performance, security, and compatibility with future Memberium releases.</p>';
 
}$m4is_a173 .= '<p><strong>To Fix This Issue:</strong></p>' . '<p>Please contact your web host to see if they support a newer version of PHP.</p>';
 if(time()> strtotime($m4is_z8669['security_support'])){
$m4is_s6347 ='notice notice-error';
 
}echo "<div class='{$m4is_s6347
}'>{$m4is_a173
}</div>";
 
}
}private static 
function m4is_k7829(){
$m4is_l9671 ='windows_nt';
 if(in_array(php_uname('s'), ['Windows NT'])){
if(!get_option("memberium/notice/{$m4is_l9671
}", false)){
$m4is_s6347 ='notice notice-error';
 $m4is_c67 =m4is_h65::m4is_q62087($m4is_l9671);
 $m4is_a173 ='<h3>Operating System</h3>' . '<p>Memberium has detected that you are running on a Windows NT based web hosting plan.</p>' . '<p>We recommend using Linux based hosting for maximum compatibillity, performance, and reliability.</p>' . '<div><p><a class="button-primary" href="'. $m4is_c67 .'">Hide this Notice</a></p></div>';
 echo "<div class=\"$m4is_s6347 is-dismissible\">$m4is_a173</div>";
 
}
}
}private static 
function m4is_j68(){
if(defined('WP_ROCKET_VERSION')){
$m4is_l9671 ='wprocket';
 if(!get_option("memberium/notice/{$m4is_l9671
}", false)){
$m4is_s6347 ='notice notice-warning';
 $m4is_c67 =m4is_h65::m4is_q62087($m4is_l9671);
 $m4is_a173 ='<h3>WP Rocket Detected</h3>' . '<p>Memberium has detected that you are using WP Rocket.</p>' . '<p>Plugins that cache your pages  will cause serious problems with your membership site.  ' . 'This plugin will cause personal pages and billing information to be deivered to the wrong member.</p>'. '<div><p><a class="button-primary" href="'. $m4is_c67 .'">Hide this Notice</a></p></div>';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}
} private static 
function m4is_t79(){
$m4is_l9671 ='cloudflare';
 if(isset($_SERVER['HTTP_CF_RAY'])){
if(!get_option("memberium/notice/{$m4is_l9671
}", false)){
$m4is_m14835 =self::m4is_s762();
 $m4is_c67 =m4is_h65::m4is_q62087($m4is_l9671);
 $m4is_a173 ='<h3>CloudFlare Warning</h3>' . '<p>Memberium has detected that you are using CloudFlare.</p>'. '<p>Please contact CloudFlare support to ensure that your configuration does not cache HTML pages.</p>' . '<div><p><a class="button-primary" href="'. $m4is_c67 .'">Hide this Notice</a></p></div>';
 $m4is_s6347 ='notice notice-warning';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}
}private static 
function m4is_x28(){
 if(defined('GD_VIP')&&constant('GD_VIP' )){
$m4is_l9671 ='godaddy_hosting';
 if(!get_option("memberium/notice/{$m4is_l9671
}", false)){
$m4is_c67 =m4is_h65::m4is_q62087($m4is_l9671);
 $m4is_a173 ='<h3>Incompatible Hosting</h3>' . '<p>Memberium has detected that your site may be running on GoDaddy Managed WordPress hosting.</p>' . '<p>This hosting service has caching features which cannot be disabled, and may cause the wrong content to be delivered to your viewers.</p>' . '<p>For managed services, we recommend WP Engine.  If you wish to stay with GoDaddy, We recommend you transfer your site to GoDaddy\'s Cpanel hosting.</p>' . '<div><p><a class="button-primary" href="'. $m4is_c67 .'">Hide this Notice</a></p></div>';
 $m4is_s6347 ='notice notice-warning';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}
}private static 
function m4is_v420(){
$m4is_l9671 ='flywheel_hosting';
 if(defined('FLYWHEEL_CONFIG_DIR')||defined('FLYWHEEL_DEFAULT_PROTOCOL')||defined('FLYWHEEL_PLUGIN_DIR')){
if(!get_option("memberium/notice/{$m4is_l9671
}", false)){
$m4is_c67 =m4is_h65::m4is_q62087($m4is_l9671);
 $m4is_s6347 ='notice notice-warning';
 $m4is_a173 ='<h3>FlyWheel Hosting</h3>' . '<p>Memberium has detected that your site may be running on FlyWheel WordPress hosting.</p>' . '<p>This hosting service has caching features which may cause the wrong content to be delivered to your viewers, or for functionality to break.  ' . 'We recommend reaching out to FlyWheel support and asking them to disable caching for any sensitive URL\'s.  ' . 'You can also enable development mode for short periods in order to disable all caching.<br /><br />' . 'Please contact FlyWheel Support or Memberium support if you have questions or need assistance.</p>' . '<div><p><a class="button-primary" href="'. $m4is_c67 .'">Hide this Notice</a></p></div>';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}
}private static 
function m4is_e4283(){
if(defined('MEPR_PLUGIN_NAME')){
$m4is_a173 ='<h3>Conflicting Membership Plugin Installed</h3>' . '<p>MemberPress is installed. Please deactivate.</p>';
 $m4is_s6347 ='notice notice-error';
 echo "<div class='{$m4is_s6347
}'>{$m4is_a173
}</div>";
 
}
}private static 
function m4is_l305(){
if(defined('WPE_WHITELABEL')||isset($_SERVER['WPENGINE_PHPSESSIONS'])){
$m4is_l9671 ='wpengine';
 if(!get_option("memberium/notice/{$m4is_l9671
}", false)){
$m4is_c67 =m4is_h65::m4is_q62087($m4is_l9671);
 $m4is_s6347 ='notice notice-success';
 $m4is_a173 ='<h3>WP Engine Optimization</h3>' . '<p><strong>Congratulations!</strong>  Memberium has detected that you are installed on the WP Engine Digital Experience Platform.' . '<p>In order to ensure that your webhooks and HTTP POSTs are delivered reliably, you need to disable the "Redirect Bots" option in your WP Engine dashboard.  ' . 'This setting is designed to optimize your site performance when bots are hitting it, but it can accidentally block HTTP POSTs and Webhooks.</p>' . '<p>You can find instructions on how to change this settings <a href="https://wpengine.com/support/redirecting-bots-how-this-benefits-you/" target="_blank">here</a>.</p>' . '<p>Once this step is complete, click the button below to hide this reminder.</p>' . '<div><p><a class="button-primary" href="'. $m4is_c67 .'">Stop Reminding Me</a></p></div>';
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}
}private static 
function m4is_j538(){
if(defined('KINSTAMU_VERSION')){
$m4is_l9671 ='kinsta';
 if(!get_option("memberium/notice/{$m4is_l9671
}", false)){
$m4is_c67 =m4is_h65::m4is_q62087($m4is_l9671);
 $m4is_s6347 ='notice notice-error';
 $m4is_a173 ='<h3>Kinsta Hosting</h3>' . "<p>Some sites may experience issues with Kinsta's server-level caching which may include displaying content to users that should otherwise not be displayed. " . "If this behavior is seen, you can reach out and contact the Kinsta support team and request that they enable 'Removing cookies from cached responses' in their NGINX configuration.</p>" . "<div><p><a class='button-primary' href='{$m4is_c67
}'>Stop Reminding Me</a></p></div>";
 echo "<div class='{$m4is_s6347
} is-dismissible'>{$m4is_a173
}</div>";
 
}
}
} private static 
function m4is_u66403(){
if(self::$m4is_r1546->m4is_e58704()!== 'production' ){
return;
 
}$m4is_i61 =array_diff(scandir(ABSPATH), ['.', '..']);
 $m4is_t265 =in_array('debuglog.txt', $m4is_i61);
 if($m4is_t265){
$m4is_a173 ='<h3>Security Vulnerability Detected</h3>';
 $m4is_s6347 ='notice notice-error';
 $m4is_a173 .= '<p>Memberium has detected that there is a debuglog present in your top WordPress folder named debuglog.txt.  ' . 'Debug logs contain sensitive data and should be removed as soon as possible.</p>' . '<p>Please delete this file to remove this safety warning message.</p>';
 echo "<div class='{$m4is_s6347
}'>{$m4is_a173
}</div>";
 
}
}  
}

