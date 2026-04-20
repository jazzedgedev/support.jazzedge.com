<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 m4is_g66::m4is_z95();
 
class m4is_g66 {
static private $m4is_r1546;
 static private $m4is_h16905;
 static private $m4is_s6178;
 static private $m4is_e0213;
 static private array $m4is_u65709;
 static private array $m4is_x910;
 static 
function m4is_z95(){
self::m4is_i702();
 self::m4is_v026();
 self::m4is_o719();
 self::m4is_v265();
 
}private 
function __construct(){

}private static 
function m4is_i702(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_u65709 =self::$m4is_r1546->m4is_t5961();
 self::$m4is_x910 =self::$m4is_r1546->m4is_q6941();
 self::$m4is_e0213 =self::$m4is_r1546->m4is_j498('settings' );
 self::$m4is_h16905 =get_option('memberium_extensions', []);
 self::$m4is_s6178 =[];
  foreach(self::$m4is_h16905 as $m4is_o015 =>$m4is_k72 ){
if(!array_key_exists($m4is_o015, self::$m4is_u65709 )){
unset(self::$m4is_h16905[$m4is_o015]);
 
}
}ksort(self::$m4is_h16905 );
 foreach(self::$m4is_u65709 as $m4is_c4069 =>$m4is_d04266 ){
if(array_key_exists($m4is_c4069, self::$m4is_x910 )){
$m4is_k86914 =dirname(self::$m4is_r1546->m4is_g316(). 'vendor/' . $m4is_d04266 ). '/info.txt';
 
}else{
$m4is_k86914 =dirname(self::$m4is_r1546->m4is_v75($m4is_d04266 )). '/info.txt';
 
}if(file_exists($m4is_k86914 )){
self::$m4is_s6178[]=$m4is_k86914;
 
}
}
}private static 
function m4is_v026(){
current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 
}private static 
function m4is_g326(){
foreach (self::$m4is_s6178 as $m4is_c630 =>$m4is_d04266 ){
$m4is_w3680 =dirname($m4is_d04266 ). '/info.txt';
 $m4is_l91805 =get_plugin_data($m4is_w3680, false, false );
 $m4is_j09 =basename(dirname($m4is_d04266 ));
 $m4is_n021 =isset(self::$m4is_h16905[$m4is_j09])? self::$m4is_h16905[$m4is_j09]: 1;
 if(!empty($m4is_l91805['Name'])){
m4is_h65::m4is_y648($m4is_l91805['Name'], "extensions[{$m4is_j09
}]", $m4is_l91805['AuthorURI'], (bool) $m4is_n021 );
 
}
}
}private static 
function m4is_v265(){
echo <<<HTMLBLOCK


			<script>
		HTMLBLOCK;
 if(empty(self::$m4is_h16905['facebook'])){
echo <<<HTMLBLOCK
				jQuery('#facebook_app_id').attr('disabled', 'disabled');
				jQuery('#facebook_app_id').val('');
				jQuery('#facebook_app_id').attr('placeholder', 'Activate the Facebook extension to use this.');
			HTMLBLOCK;
 
}if(empty(self::$m4is_h16905['spiffy'])){
echo <<<HTMLBLOCK
				jQuery('#spiffy_api_key,#spiffy_subdomain').attr('disabled', 'disabled');
				jQuery('#spiffy_api_key,#spiffy_subdomain').val('');
				jQuery('#spiffy_api_key,#spiffy_subdomain').attr('placeholder', 'Activate the Spiffy extension to use this.');
			HTMLBLOCK;
 
}echo <<<HTMLBLOCK
			</script>


		HTMLBLOCK;
 
}private static 
function m4is_o719(){
echo '<form method="POST" action="">';
 wp_nonce_field(self::$m4is_r1546->m4is_j541(), 'memberium_options_nonce' );
 echo '<ul>';
 echo '<h3>Optional Extensions</h3>';
 self::m4is_g326();
 echo '</ul>';
 echo '<ul>';
 echo '<h3>Optional Settings</h3>';
 m4is_h65::m4is_i014('Facebook App ID', 'facebook_app_id', self::$m4is_e0213['facebook_app_id'], ['help_id' =>2571, 'style' =>'text-align:left;width:100px;']);
 echo '<br />';
 m4is_h65::m4is_i014('Spiffy Subdomain', 'spiffy_subdomain', self::$m4is_e0213['spiffy_subdomain'], ['help_id' =>19699, 'pattern' =>'[A-Za-z0-9][A-Za-z0-9\-]+', 'style' =>'text-align:left;width:100px;', 'placeholder' =>'Enter your Spiffy Subdomain here', ]);
 m4is_h65::m4is_i014('Spiffy API Key', 'spiffy_api_key', self::$m4is_e0213['spiffy_api_key'], ['help_id' =>0, 'pattern' =>'[A-Za-z0-9][A-Za-z0-9\-]+', 'style' =>'text-align:left;width:100px;', 'placeholder' =>'Enter your Spiffy API Key here', ]);
 if(!empty(self::$m4is_h16905['spiffy'])){

}echo '</ul>';
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 echo '</form>';
 
}
}

