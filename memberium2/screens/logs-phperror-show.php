<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 new m4is_f735();
 final 
class m4is_f735 {
private $m4is_o47268 =[];
 private $m4is_j17093 =[];
 private $m4is_o698 =[];
 
function __construct(){
$this->m4is_o719();
 
}private 
function m4is_o719(): void {
$this->m4is_k73();
 echo '<h3>PHP Error Logs</h3>';
 if(empty($this->m4is_o47268 )){
echo '<p>No PHP Error logs found.</p>';
 return;
 
}$m4is_p3275 =32 * KB_IN_BYTES;
 foreach($this->m4is_o47268 as $m4is_g26713 ){
if(file_exists($m4is_g26713 )){
$m4is_i506 =filesize($m4is_g26713 );
 $m4is_t57696 =ceil($m4is_i506 / MB_IN_BYTES );
 $m4is_e53706 =$m4is_p3275 > $m4is_i506 ? 0 : -$m4is_p3275;
 $m4is_o765 =human_time_diff(time(), filemtime($m4is_g26713 ));
 $m4is_y97648 =size_format($m4is_i506 );
 $m4is_m80 =size_format($m4is_p3275 );
 $m4is_b56860 =esc_html(file_get_contents($m4is_g26713, false, null, $m4is_e53706, $m4is_p3275 ));
 echo <<<HTMLBLOCK
					<div style="margin-bottom:20px">
						Location: {$m4is_g26713
}<br>
						Last Updated: {$m4is_o765
} ago<br>
						Total Error Log Length:  {$m4is_y97648
}<br />
						Displaying last {$m4is_m80
}<br>';
						<textarea style="width:80%" rows="20">{$m4is_b56860
}</textarea><br />
					</div>
				HTMLBLOCK;
 
}
}
}public 
function m4is_k73(string $m4is_p68792 =ABSPATH ): array {
$m4is_z984 ='memberium/logs/php/errors';
 $m4is_q436 =3 * MINUTE_IN_SECONDS;
 $m4is_u719 =get_transient($m4is_z984 );
 if(is_array($m4is_u719 )&&!empty($m4is_u719 )){
$this->m4is_o47268 =$m4is_u719;
 return $m4is_u719;
 
}$this->m4is_o47268 =[];
 $this->m4is_j17093[]=trailingslashit(trailingslashit(WP_CONTENT_DIR ). 'uploads' );
 $this->m4is_j17093[]=trailingslashit(wp_get_upload_dir()['basedir']);
 $this->m4is_j17093[]=trailingslashit(WP_PLUGIN_DIR );
 $m4is_y37 =ini_get('error_log' );
 if(defined('UPLOADS' )){
$this->m4is_j17093[]=str_replace(trailingslashit(WP_CONTENT_DIR ), '', untrailingslashit(UPLOADS ));
 
}if(!empty($m4is_y37 )){
$this->m4is_o47268[]=$m4is_y37;
 
}$this->m4is_s396($m4is_p68792 );
 set_transient($m4is_z984, $this->m4is_o47268, $m4is_q436 );
 return $this->m4is_o47268;
 
}private 
function m4is_g79(string $m4is_k86914 ): bool {
if(is_dir($m4is_k86914 )){
return false;
 
}$m4is_j46 =false;
 $m4is_q32659 =null;
 $m4is_e53706 =0;
 $m4is_x1486 =4 * KB_IN_BYTES;
 $m4is_g650 =file_get_contents($m4is_k86914, $m4is_j46, $m4is_q32659, $m4is_e53706, $m4is_x1486 );
 if(strpos($m4is_g650, 'PHP Fatal error' )!== false ){
return true;
 
}if(strpos($m4is_g650, 'PHP Parse error' )!== false ){
return true;
 
}if(strpos($m4is_g650, 'PHP Warning' )!== false ){
return true;
 
}if(strpos($m4is_g650, 'PHP Notice' )!== false ){
return true;
 
}return false;
 
}private 
function m4is_s396(string $m4is_p68792 ){
if(!is_dir($m4is_p68792 )){
return;
 
}$m4is_u39687 =scandir($m4is_p68792 );
 foreach($m4is_u39687 as $m4is_e03627 ){
if($m4is_e03627 == '.' ||$m4is_e03627 == '..' ){
continue;
 
}if(stripos($m4is_e03627, 'php_errorlog')!== false ||stripos($m4is_e03627, 'debug.log')!== false ){
$this->m4is_o47268[]=$m4is_p68792 . $m4is_e03627;
 
}elseif(stripos($m4is_e03627, '.php')=== false ){
if(stripos($m4is_e03627, 'log')!== false ){
if(strpos($m4is_e03627, 'error')!== false ||strpos($m4is_e03627, 'debug')!== false ){
$m4is_n63421 =$m4is_p68792 . DIRECTORY_SEPARATOR . $m4is_e03627;
 if($this->m4is_g79($m4is_p68792 . $m4is_e03627 )){
$this->m4is_o47268[]=$m4is_p68792 . $m4is_e03627;
 
}
}
}
} $m4is_c93761 =$m4is_p68792 . $m4is_e03627 . DIRECTORY_SEPARATOR;
 if(is_dir($m4is_c93761 )){
 $this->m4is_s396($m4is_c93761 );
  
}
}return;
 
}
}

