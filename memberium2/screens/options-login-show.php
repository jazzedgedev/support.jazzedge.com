<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 new m4is_m4506();
 final 
class m4is_m4506 {
private $m4is_r1546, $m4is_r9613, $m4is_e0213;
 
function __construct(){
$this->m4is_h269();
 $this->m4is_o719();
 
}private 
function m4is_h269(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 $this->m4is_e0213 ='settings';
 
} private 
function m4is_h246(): void {
$m4is_a89 =$this->m4is_q7486();
 $m4is_r18 ='';
 $m4is_n96 ='Password';
 $m4is_r6234 =$this->m4is_r1546->m4is_j498($this->m4is_e0213, 'password_field', $m4is_n96 );
  $m4is_v45136 =$this->m4is_r1546->m4is_j498('settings', 'ignore_contact_fields' );
 $m4is_v45136 =is_string($m4is_v45136 )? array_filter(explode(',', $m4is_v45136 )): [];
 if(!empty($m4is_v45136 )){
foreach ($m4is_a89 as $m4is_l9671 =>$m4is_q523 ){
if(in_array($m4is_q523, $m4is_v45136 )){
unset($m4is_a89[$m4is_l9671]);
 
}
}
} foreach ($m4is_a89 as $m4is_g91703 ){
$m4is_a437 =$m4is_g91703 == $m4is_r6234 ? 'selected="selected"' : '';
 $m4is_r18 =$m4is_r18 . sprintf('<option value="%s" %s >%s</option>', $m4is_g91703, $m4is_a437, $m4is_g91703 );
 
}echo '<li><label>CRM Password Field</label>';
 echo '<select id="password_field" name="password_field" class="basic-single" style="width:250px;">';
 echo $m4is_r18;
 echo '</select>', m4is_h65::m4is_o64(1171 );
 echo '</li>';
 if($m4is_n96 == $m4is_r6234 ){
echo '<li><label>Create New Password Field</label>';
 echo '<input type="text" autocomplete="off" name="new_crm_field" size="20" value="" />';
 echo m4is_h65::m4is_o64(5733 ), '</li>';
 
}
}private 
function m4is_o719(){
echo '<form method="POST" action="">';
 wp_nonce_field($this->m4is_r1546->m4is_j541(), 'memberium_options_nonce' );
 $this->m4is_y046();
 $this->m4is_c4175();
 $this->m4is_n9616();
 $this->m4is_b2138();
 $this->m4is_o48();
 $this->m4is_w630();
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 echo '</form>';
 
}private 
function m4is_y046(){
$m4is_z692 =$this->m4is_a16389();
 $m4is_w938 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'min_password_length', 6 );
 $m4is_i0548 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'password_strength', 0 );
 $m4is_o10537 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'password_reset_tag', 0 );
 $m4is_r2369 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'local_auth_only', 0 );
 $m4is_t56607 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'disable_password_reset', 0 );
 $m4is_f786 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'disable_lost_password', 0 );
 echo '<h3>Password Settings</h3>';
 echo '<ul>';
 $this->m4is_z74();
 $this->m4is_y8641();
 $this->m4is_h246();
 $m4is_y66291 =['help_id' =>1185, 'min' =>6, 'max' =>64, 'size' =>6 ];
 m4is_h65::m4is_w6712('Minimum Password Length', 'min_password_length', $m4is_w938, $m4is_y66291 );
 m4is_h65::m4is_z30162('Password Strength', 'password_strength', $m4is_i0548, $m4is_z692, ['style' =>'width:250px;', 'help_id' =>21852]);
 m4is_h65::m4is_y648('Secure Passwords / Local Auth Only', 'local_auth_only', 6818, $m4is_r2369 );
 m4is_h65::m4is_y648('Disable Lost Password', 'disable_lost_password', 4422, $m4is_f786 );
 m4is_h65::m4is_y648('Disable WordPress Password Reset', 'disable_password_reset', 4421, $m4is_t56607 );
 m4is_h65::m4is_h70259('Password Reset Tag', 'password_reset_tag', $m4is_o10537, 'taglistdropdown', ['help_id' =>1183 ]);
 echo '</ul>';
 
}private 
function m4is_c4175(){
$m4is_q7686 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'site_ban_tag', 0 );
 $m4is_p8623 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'maximum_login_ips', 0 );
 $m4is_l1875 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'maximum_login_timeframe', 0 );
 $m4is_s138 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'bruteforce_check', 0 );
 $m4is_k6751 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'require_membership', 0 );
 $m4is_f72 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'known_logins_only', 0 );
 $m4is_r64096 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'allow_local_logins', 0 );
 $m4is_o027 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'simultaneous_logins', 0 );
 echo '<h3>Login Restrictions</h3>';
 echo '<ul>';
 m4is_h65::m4is_y648('Require Membership', 'require_membership', 1202, $m4is_k6751 );
 m4is_h65::m4is_y648('Known Logins Only', 'known_logins_only', 6773, $m4is_f72 );
 m4is_h65::m4is_y648('Allow Local Logins', 'allow_local_logins', 6367, $m4is_r64096 );
 m4is_h65::m4is_y648('Prevent Simultaneous Logins', 'simultaneous_logins', 1197, $m4is_o027 );
 m4is_h65::m4is_h70259('Site Ban Tag', 'site_ban_tag', $m4is_q7686, 'taglistdropdown', ['help_id' =>1195 ]);
 m4is_h65::m4is_w6712('Maximum Login IPs', 'maximum_login_ips', $m4is_p8623, ['min' =>0, 'max' =>100, 'help_id' =>1204]);
 m4is_h65::m4is_w6712('Maximum Login Window (Hours)', 'maximum_login_timeframe', $m4is_l1875, ['min' =>0, 'max' =>672, 'help_id' =>1204]);
 m4is_h65::m4is_z30162('Bot Login Protection', 'bruteforce_check', $m4is_s138, $this->m4is_e81479(), ['style' =>'width:250px;', 'help_id' =>0 ]);
 echo '</ul>';
 
} private 
function m4is_n9616(){
$m4is_n723 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'login_log', 0 );
 $m4is_v06 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'login_log_length', 0 );
 $m4is_k825 =(string) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'last_login_field', '' );
 $m4is_w82 =$this->m4is_a4263();
 echo <<<HTMLBLOCK
			<hr>
			<h3>Login Logging</h3>
			<ul>
		HTMLBLOCK;
 m4is_h65::m4is_y648('Login Log', 'login_log', 1187, $m4is_n723 );
 m4is_h65::m4is_w6712('Login Log Retention (Days)', 'login_log_length', $m4is_v06, ['help_id' =>0000]);
 m4is_h65::m4is_z30162('Last Login Date Field', 'last_login_field', $m4is_k825, $m4is_w82, ['help_id' =>1173 ]);
 echo <<<HTMLBLOCK
			</ul>
		HTMLBLOCK;
 
} private 
function m4is_b2138(){
$m4is_d36 =m4is_j4156::m4is_c289();
 $m4is_s8523 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'login_url', 0 );
 $m4is_w2956 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'login_actionset', 0 );
 $m4is_d2317 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'login_tag', 0 );
 $m4is_t271 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'disable_displayname_update', 0 );
 $m4is_f02748 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'enable_slug_update', 0 );
 $m4is_z82357 =(string) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'displayname_format', '' );
 $m4is_w0428 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'disable_login_sync', 0 );
 echo '<hr>';
 echo '<h3>Login Actions</h3>';
 echo '<ul>';
 m4is_h65::m4is_h70259('Login Page <strong style="color:red;">(Caution)</strong>', 'login_url', $m4is_s8523, 'pagelistdropdown', ['help_id' =>1208 ]);
 m4is_h65::m4is_y648('Disable Display Name Update', 'disable_displayname_update', 9634, $m4is_t271 );
 m4is_h65::m4is_i014('Display Name Format', 'displayname_format', $m4is_z82357, ['help_id' =>5731]);
 m4is_h65::m4is_y648('Enable User URL Slug Update', 'enable_slug_update', 21857, $m4is_f02748 );
  m4is_h65::m4is_h70259('Login Tag', 'login_tag', $m4is_d2317, 'taglistdropdown', ['help_id' =>000 ]);
 if($m4is_d36 ){
m4is_h65::m4is_h70259('Login Actionset', 'login_actionset', $m4is_w2956, 'actionsetdropdown', ['help_id' =>1175 ]);
 
}if(true ){
m4is_h65::m4is_y648('Disable Login Sync/Actions', 'disable_login_sync', 0, $m4is_w0428 );
 
}echo '</ul>';
 
} private 
function m4is_o48(){
$m4is_d36 =m4is_j4156::m4is_c289();
 $m4is_b628 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'autologout_time', 0 );
 $m4is_c65 =(int) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'logout_actionset', 0 );
 echo '<hr>';
 echo '<h3>Logout Actions</h3>';
 echo '<ul>';
 m4is_h65::m4is_w6712('Autologout/Inactivity Timer (Seconds)', 'autologout_time', $m4is_b628, ['help_id' =>7688, 'max' =>86400, 'min' =>0, 'size' =>3, 'style' =>'text-align:right;width:80px;', ]);
 if($m4is_d36 ){
m4is_h65::m4is_h70259('Logout Actionset', 'logout_actionset', $m4is_c65, 'actionsetdropdown', ['help_id' =>1178]);
 
}echo '</ul>';
 echo '<hr>';
 
} private 
function m4is_w630(){
 $m4is_n316 =(bool) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'allow_autologin', 0 );
 $m4is_t16867 =(string) $this->m4is_r1546->m4is_j498($this->m4is_e0213, 'autologin_authkeys', '' );
 echo '<h3>Auto-Login Settings</h3>';
 echo '<ul>';
 m4is_h65::m4is_y648('Allow Autologin', 'allow_autologin', 4398, $m4is_n316 );
 if(!$m4is_n316 ){
return;
 
} m4is_h65::m4is_i014('Autologin Auth Keys', 'autologin_authkeys', $m4is_t16867, ['help_id' =>2571, 'style' =>'text-align:left;width:350px;' ]);
 echo '<hr>';
 echo '</ul>';
 
} private 
function m4is_a4263(){
$m4is_z8669 =m4is_s695::m4is_e654(m4is_s695::CONTACT_FIELDS, m4is_s695::DATE_TYPE );
 $m4is_e9307 =m4is_s695::m4is_e654(m4is_s695::CONTACT_FIELDS, m4is_s695::DATETIME_TYPE );
 $m4is_m615 =array_merge($m4is_z8669, $m4is_e9307 );
 $m4is_r37596 =[];
 $m4is_r37596['']='(None)';
 foreach($m4is_m615 as $m4is_g91703 ){
$m4is_r37596[$m4is_g91703]=$m4is_g91703;
 
}return $m4is_r37596;
 
} private 
function m4is_e81479(){
return ['0' =>'Disabled', '1' =>'Basic (Cache Friendly)', '2' =>'Maximum' ];
 
} private 
function m4is_z74(){
$m4is_r2369 =$this->m4is_r1546->m4is_j498($this->m4is_e0213, 'min_password_length', 6 );
 $m4is_r6234 =$this->m4is_r1546->m4is_j498($this->m4is_e0213, 'password_field', 'Password' );
 if(!$m4is_r2369 ){
echo '<p"><strong style="color:darkred;">RECOMMENDATION:</strong> Passwords are stored insecurely in Keap.  Turn on "Secure Passwords" below to only use encrypted passwords.</p>';
 
}if($m4is_r6234 == 'Password'){
echo '<p"><strong style="color:darkred;">RECOMMENDATION:</strong>  Using the default "Password" field may increase your API requirements.  We recommend using a custom field instead.</p>';
 echo '<p"><strong style="color:darkred;">WARNING:</strong>  Passwords are limited to 20 characters due to using the built-in "Password" field.</p>';
 
}
} private 
function m4is_y8641(){
$m4is_p4935 =$this->m4is_r1546->m4is_j498($this->m4is_e0213, 'username_field', 'Email' );
 $m4is_x6495 ='';
 $m4is_w9356 =1;
 $m4is_i67806 =['Email', ];
 if($m4is_p4935 <> 'Email' ){
$m4is_i67806[]=$m4is_p4935;
 
}foreach($m4is_i67806 as $m4is_k72 ){
$m4is_x6495 .= "<option value='{$m4is_k72
}' " . (($m4is_k72 == $m4is_p4935 )? ' selected="selected" ' : '' ). ">{$m4is_k72
}</option>";
 
}$m4is_w9356 =count($m4is_i67806);
 if($m4is_w9356 > 1 ){
echo '<li><label>Keap Username Field</label>';
 echo '<select id="username_field" name="username_field" class="basic-single" style="width:250px;">';
 echo $m4is_x6495;
 echo '</select>', m4is_h65::m4is_o64(1169 ), '</li>';
 
}
} private 
function m4is_q7486(): array {
$m4is_a89 =m4is_s695::m4is_e654(m4is_s695::CONTACT_FIELDS, m4is_s695::TEXT_TYPE );
 $m4is_a89[]='MiddleName';
 $m4is_a89[]='SpouseName';
 if($this->m4is_r1546->m4is_j498($this->m4is_e0213, 'password_field', '' )== 'Password' ){
$m4is_a89[]='Password';
 
};
 return $m4is_a89;
 
} private 
function m4is_a16389(){
$m4is_r37596 =[0 =>'Simple Lowercase (Level 0)', 1 =>'+ Uppercase Consonants (Level 1)', 2 =>'+ Uppercase Vowels (Level 2)', 3 =>'+ Numbers (Level 3)', 4 =>'+ Symbols (Level 4)', ];
 if(m4is_r83::m4is_c26()->m4is_j498('settings', 'password_field' )!== 'Password' ){
$m4is_r37596[5]='4 Word Passphrase';
 $m4is_r37596[6]='5 Word Passphrase';
 
}return $m4is_r37596;
 
}
}

