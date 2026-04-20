<?php

/**
 * Copyright (c) 2017-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_u62::m4is_z95();
 final 
class m4is_u62 {
private $m4is_r1546;
 private $m4is_x25;
 private $m4is_i6308;
 public static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self();
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_o719();
 
}private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_x25 =m4is_s6729::m4is_c26();
 $this->m4is_i6308 =m4is_s52::m4is_f27();
 
}private 
function m4is_o719(){
$m4is_e0213 =$this->m4is_r1546->m4is_j498('settings' );
 $m4is_q30 =$this->m4is_r1546->m4is_j498('settings', 'autoupdate' );
 $m4is_z626 =m4is_l9685::m4is_b45381();
 $m4is_i796 =wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memberium_options_nonce', true, false );
 $m4is_u3029 =$this->m4is_z708();
 $m4is_y96480 =m4is_h65::m4is_o64(9891);
 echo <<<HTMLBLOCK
			<form method="POST" action="">
				{$m4is_i796
}
				<h2>Memberium Updates</h2>
				<ul>
		HTMLBLOCK;
 if(!$m4is_z626 ){
echo <<<HTMLBLOCK
				<h3 style="color:red;">Warning</h3>
					<p>
						System updates disabled because the plugin folder cannot be written to.
					</p>
			HTMLBLOCK;
 
}elseif($this->m4is_i6308 ){
m4is_h65::m4is_y648('Auto-Update Plugin', 'autoupdate', 12523, $m4is_e0213['autoupdate']);
 
}echo <<<HTMLBLOCK
					<li>
						<label>Memberium Manual Updater</label>
						<div style="display:inline-block;">
						{$m4is_u3029
}<br>
						{$m4is_y96480
}
						</div>
					</li>
				</ul>
				<p>
					<input type="submit" value="Update" class="button-primary">
				</p>
			</form>
		HTMLBLOCK;
 
} 
function m4is_z708($m4is_c71 ='memberium2' ): string {
$m4is_z9157 =m4is_l9685::m4is_f358();
 $m4is_w390 =$this->m4is_r1546->m4is_w45();
 $m4is_n0716 =m4is_l9685::m4is_b45381();
 $m4is_u626 ='';
 $m4is_p691 ='';
 if(is_array($m4is_z9157 )){
foreach ($m4is_z9157 as $m4is_d07693=>$m4is_a686 ){
$m4is_a437 =$m4is_w390 == $m4is_a686['version']? ' selected="selected" ' : '';
 $m4is_u626 .= sprintf('<option value="%d" %s>%s %s</option>', $m4is_d07693, $m4is_a437, $m4is_a686['name'], $m4is_a686['comments']);
 
}
}if($m4is_n0716 ){
$m4is_p691 =<<<HTMLBLOCK
				<option value="">Choose your option</option>
				<option value="download">Download</option>
				<option value="install">Install</option>
			HTMLBLOCK;
 
}else{
$m4is_p691 =<<<HTMLBLOCK
				<option value="">Choose your option</option>
				<option value="download">Download</option>
			HTMLBLOCK;
 
}$output =<<<HTMLBLOCK
			<select name="manual_upgrade" style="width:500px !important; margin-bottom:6px;">
				{$m4is_u626
}
			</select><br>
			<select name="manual_upgrade_confirm" style="width:250px !important; margin-bottom:6px;">
				{$m4is_p691
}
			</select>
		HTMLBLOCK;
 return $output;
 
} 
}

