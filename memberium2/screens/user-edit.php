<?php

/**
 * Copyright (c) 2017-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_s6729' )||die();
 
class m4is_e41 {
private static $m4is_r1546;
 private static $m4is_h21895;
 private static $m4is_k824;
 private static $m4is_l17096;
 private 
function __construct(){

} static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 
} static 
function m4is_o719(WP_User $m4is_l17096 ){
if(!self::m4is_v026()){
return;
 
}if(self::m4is_w206($m4is_l17096 )){
return;
 
}self::$m4is_h21895 =m4is_p40::m4is_r804($m4is_l17096->user_email );
 m4is_p40::m4is_m684($m4is_l17096->ID, self::$m4is_h21895 );
 self::$m4is_l17096 =$m4is_l17096;
 self::$m4is_k824 =m4is_q82::m4is_d59($m4is_l17096->ID );
 echo <<<HTMLBLOCK
			<hr>
			<h3>Memberium Membership System</h3>
		HTMLBLOCK;
 if(self::m4is_o5803()){
echo '<table class="form-table">';
 self::m4is_l3762();
 self::m4is_d72465();
 self::m4is_i17690();
 self::m4is_r07();
 self::m4is_v69();
 self::m4is_f67095();
 self::m4is_l372();
 self::m4is_u14056();
 do_action('memberium/admin/user_editor', $m4is_l17096 );
 echo '</table>';
 
}$m4is_o3451 =self::m4is_d290();
 echo <<<HTMLBLOCK
			<table class="form-table">
				{$m4is_o3451
}
			</table>
		HTMLBLOCK;
 
} static 
function m4is_w206(WP_User $m4is_l17096 ): bool {
if(!user_can($m4is_l17096, 'manage_options' )){
return false;
 
}echo <<<HTMLBLOCK
			<tr>
				<th>WARNING:</th>
				<td>
					<p style="font-weight:bold;color:red;">
						This user has the Admin / "manage_options" capability.
					</p>
					<p>
						Admin users cannnot be linked to Keap contacts.
					</p>
				</td>
			</tr>
		HTMLBLOCK;
 return true;
 
} static 
function m4is_v026(){
return current_user_can('manage_options' );
 
}private static 
function m4is_o5803(){
return (bool) self::$m4is_h21895;
 
}private static 
function m4is_l3762(){
self::m4is_k160();
 self::m4is_h245();
 self::m4is_n32();
 self::m4is_z032();
 self::m4is_y1547();
 self::m4is_d67583();
 
}private static 
function m4is_k160(){
$m4is_h21895 =self::$m4is_h21895;
 echo <<<HTMLBLOCK
			<tr>
				<th>
					<label for="infusionsoft_id">Keap Contact ID</label>
				</th>
				<td>
					<input name="infusionsoft_id" value="{$m4is_h21895
}" disabled="disabled" size="10" style="text-align:right;">
				</td>
			</tr>
		HTMLBLOCK;
 
}private static 
function m4is_h245(){
$m4is_t082 =date('l, F j, Y @ g:i A', strtotime(self::$m4is_l17096->user_registered ));
 echo <<<HTMLBLOCK
			<tr>
				<th><label>User Creation Date</label></th>
				<td>{$m4is_t082
}</td>
			</tr>
		HTMLBLOCK;
 
}private static 
function m4is_n32(){
$m4is_u9705 =(int) get_user_meta(self::$m4is_l17096->ID, 'memberium_private_comments', true );
 $m4is_p6356 =$m4is_u9705 == 1 ? ' checked="checked" ' : ' ';
 echo <<<HTMLBLOCK
			<tr>
			<th><label for="memberium_private_comments">Private Comments</label></th>
			<td>
			<input name="memberium_private_comments" type="hidden" value="0">
			<input name="memberium_private_comments" id="memberium_private_comments" {$m4is_p6356
} type="checkbox" value="1"> Enable Private Comments<br />
			</td>
			</tr>
		HTMLBLOCK;
 
}private static 
function m4is_z032(){
$m4is_f4930 =strtolower(trim(self::$m4is_l17096->user_email ));
 $m4is_y193 =strtolower(trim(self::$m4is_l17096->user_login ));
 $m4is_i830 =$m4is_f4930 === $m4is_y193 ? ' checked=checked ' : ' ';
  echo '<tr>';
 echo '<th><label for="new_emailaddress">Update Username</label></th>';
 echo '<td>';
 echo '<input name="memb_update_email_confirm" id="memb_update_email_confirm" ', $m4is_i830, ' type="checkbox" value="1"> Keep Username synced to email address<br />';
 echo '</td>';
 echo '</tr>';
 
} private static 
function m4is_y1547(): void {
$m4is_c67896 =empty(self::$m4is_k824['memb_user']['theme'])? '' : self::$m4is_k824['memb_user']['theme'];
 if($m4is_c67896 ){
echo <<<HTMLBLOCK
				<tr>
					<th>WARNING:</th>
					<td>
						<p style='font-weight:bold;color:red;'>
							This user's membership level is set to use the "$m4is_c67896" theme.
						</p>
					</td>
				</tr>
			HTMLBLOCK;
 
}
}private static 
function m4is_d67583(): void {
$m4is_v8646 =m4is_a391::m4is_g29870(self::$m4is_l17096->ID );
 $m4is_v4312 =add_query_arg('rss_user', $m4is_v8646, get_feed_link());
 echo '<tr>';
 echo '<th valign="top"><label for="rss_user_id">RSS User ID</label></th>';
 echo '<td>';
 echo '<input disabled=disabled size="30" value="', $m4is_v8646, '"><br />';
 echo '<input disabled=disabled size="60" value="', $m4is_v4312, '"><br />';
 echo '</td>';
 echo '</tr>';
 
}private static 
function m4is_d72465(): void {
$m4is_h973 =m4is_p40::m4is_a56278(self::$m4is_l17096->user_email );
 if($m4is_h973 === 1 ){
return;
 
}if($m4is_h973 > 1 ){
echo <<<HTMLBLOCK
				<tr>
					<th>WARNING:</th>
					<td>
						<p style="font-weight:bold;color:red;">MULTIPLE CONTACT RECORDS FOUND WITH THE SAME EMAIL ADDRESS</p>
					</td>
				</tr>
			HTMLBLOCK;
 
}if($m4is_h973 === 0 ){
echo <<<HTMLBLOCK
				<tr>
					<th>WARNING:</th>
					<td>
						<p style="font-weight:bold;color:red;">NO CONTACT RECORD FOUND WITH THIS EMAIL ADDRESS</p>
					</td>
				</tr>
			HTMLBLOCK;
 
}
} private static 
function m4is_i17690(){
$m4is_r6234 =self::$m4is_r1546->m4is_j498('settings', 'password_field' );
 $m4is_i935 =m4is_p40::m4is_p67(self::$m4is_h21895 );
 echo '<tr>';
 echo '<th><label for="infusionsoft_fields">Keap Fields</label></th>';
 echo '<td>';
 if(!empty($m4is_i935 )){
if(!empty($m4is_i935[$m4is_r6234])){
echo "\n\n<!--\nToken: ", substr(base64_encode($m4is_i935[$m4is_r6234]), 0, -1), "\n -->\n\n";
 
}foreach ($m4is_i935 as $m4is_l9671 =>$m4is_v586 ){
if($m4is_l9671 != 'Groups' &&$m4is_v586 <> 'null' &&$m4is_v586 > '' &&$m4is_l9671 != $m4is_r6234 ){
echo "\n", '<input style="color:#000;width:200px;margin-right:25px;" disabled value="', $m4is_l9671, '"> <input name="', $m4is_l9671 , '" disabled style="color:#000;width:300px;" value="', $m4is_v586, '"><br>', "\n";
 
}
}
}echo '</td>';
 echo '</tr>';
 
}private static 
function m4is_v69(){
$m4is_m96240 =empty(self::$m4is_k824['memb_user']['membership_names'])? []: explode(',', self::$m4is_k824['memb_user']['membership_names']);
 $m4is_c5468 =empty(self::$m4is_k824['memb_user']['membership_level'])? 0 : self::$m4is_k824['memb_user']['membership_level'];
 echo '<tr>';
 echo '<th valign="top"><label for="infusionsoft_memberships">Memberships</label></th>';
 echo '<td>';
 if(empty($m4is_m96240 )){
echo '<em>(None)</em>';
 
}else{
foreach($m4is_m96240 as $m4is_w64 ){
echo '<input type=text disabled value="', $m4is_w64, '" size="', strlen($m4is_w64 ), '"> ';
  
} 
}echo '<br>';
 echo 'Level: ', $m4is_c5468, '<br />';
 echo '<br />';
 echo '</td>';
 echo '</tr>';
 
}private static 
function m4is_f67095(){
if(isset(self::$m4is_k824['memb_user']['login_page'])){
$m4is_m1672 =self::$m4is_k824['memb_user']['login_page'];
 $m4is_y469 =$m4is_m1672 > 0 ? get_the_title($m4is_m1672): '';
 $m4is_y469 =$m4is_m1672 == -1 ? 'Profile Page' : $m4is_y469;
 $m4is_n69648 =self::$m4is_r1546->m4is_y3476(self::$m4is_l17096->ID );
 echo '<tr>';
 echo '<th><label for="membership_homepage">Homepage</label></th>';
 echo '<td>';
 echo "<a target='_blank' href='{$m4is_n69648
}'>{$m4is_y469
}</a> ({$m4is_m1672
})";
 echo '</td>';
 echo '</tr>';
 
}
}private static 
function m4is_l372(){
remove_action('admin_footer', [m4is_s6729::m4is_c26(), 'm4is_n68517']);
 echo '<tr>';
 echo '<th><label for="infusionsoft_tags">Keap Tags</label></th>';
 echo '<td>';
 $m4is_f406 =isset(self::$m4is_k824['keap']['contact']['groups'])? array_filter(explode(',', self::$m4is_k824['keap']['contact']['groups'])): [];
 $m4is_l9321 =m4is_k865::m4is_z2906(false, false )['mc'];
 $m4is_r37596 =[];
 foreach ($m4is_f406 as $m4is_z765 ){
if(isset($m4is_l9321[$m4is_z765])){
$m4is_r37596[$m4is_z765]=$m4is_l9321[$m4is_z765];
 
}
}uasort($m4is_r37596, function($m4is_o30724, $m4is_v6697 ){
if($m4is_o30724 == $m4is_v6697 ){
return 0;
 
}return ($m4is_o30724 < $m4is_v6697 )? -1 : 1;
 
});
 echo "<select id='current_tags' multiple='multiple' disabled='disabled' class='disabledmultitaglist' style='width:600px !important;'>";
 foreach ($m4is_r37596 as $m4is_l9671 =>$m4is_v586 ){
echo '<option value="', $m4is_l9671, '" selected=selected>', $m4is_v586, ' (' . $m4is_l9671 . ')</option>';
 
}echo '</select><br />';
 $m4is_z470 =[];
 $m4is_l9321 =m4is_k865::m4is_z2906(true )['mc'];
 foreach ($m4is_l9321 as $m4is_d07693 =>$m4is_p786 ){
if(in_array($m4is_d07693, $m4is_f406 )){
$m4is_z470[]=['id' =>'-' . $m4is_d07693, 'text' =>'Remove ' . $m4is_p786 . ' (-' . $m4is_d07693 . ')' ];
 
}else{
$m4is_z470[]=['id' =>$m4is_d07693, 'text' =>$m4is_p786 . ' (' . $m4is_d07693 . ')' ];
 
}
}echo '<script>';
 echo 'var taglist = ', json_encode($m4is_z470), ';';
 echo '</script>';
 echo '<br />';
 echo '<span class="description">Add/Remove Tags:</span><br />';
 echo '<input type="text" value="" id="updated_tags" name="updated_tags" class="multitaglist" style="width:600px !important;"><br>';
 echo '</td>';
 echo '</tr>';
 
}private static 
function m4is_d290(): string {
return <<<HTMLBLOCK
			<table class="form-table">
				<tr>
					<th>
						<label for="infusionsoft_tags">Synchronize Keap Contact</label>
					</th>
					<td>
						<input type="submit" name="memberium_sync" value="Re-Synchronize Contact" class="button-secondary"><br />
						<span class="description">Synchronizing will delete any local cached contact data, and resync from Keap.</span>
					</td>
				</tr>
			</table>
		HTMLBLOCK;
 
}private static 
function m4is_u14056(): void {
$m4is_m125 =get_option('timezone_string' );
 $m4is_n5840 =date_default_timezone_get();
 if(!empty($m4is_m125 )){
date_default_timezone_set($m4is_m125 );
 
}$m4is_q09 =get_user_meta(self::$m4is_l17096->ID, 'last_login_time', true );
 $m4is_l56974 =get_user_meta(self::$m4is_l17096->ID, 'login_ip_address', true );
 $m4is_g6164 =(int) get_user_meta(self::$m4is_l17096->ID, 'login_count', true );
 $m4is_q09 =$m4is_q09 > 1 ? date('Y-m-d h:i:s', $m4is_q09 ): 'None';
 $m4is_l56974 =$m4is_l56974 > '' ? $m4is_l56974 : 'None';
 date_default_timezone_set($m4is_n5840 );
 echo <<<HTMLBLOCK
			<table class="form-table">
				<tr>
					<th>
						<label>Last Login:</label>
					</th>
					<td>
						Date: {$m4is_q09
}<br />
						IP Address: {$m4is_l56974
}<br>
						Total Logins: {$m4is_g6164
}<br>
					</td>
				</tr>
			</table>
		HTMLBLOCK;
 
}private static 
function m4is_r07(){
$m4is_l17096 =self::$m4is_l17096;
 if(!self::$m4is_r1546->m4is_j498('settings', 'allow_autologin' )){
return;
 
}if(user_can($m4is_l17096, 'edit_others_posts' )){
return;
 
}$m4is_p4935 =self::$m4is_r1546->m4is_j498('settings', 'username_field' );
 $m4is_t16867 =array_filter(explode(',', self::$m4is_r1546->m4is_j498('settings', 'autologin_authkeys' )));
 $m4is_i16 =isset($m4is_t16867[0])? $m4is_t16867[0]: '';
 if(empty($m4is_i16 )){
return;
 
}$m4is_i16 =urlencode($m4is_i16 );
 $m4is_h21895 =self::$m4is_h21895;
 $m4is_f4930 =urlencode(self::$m4is_l17096->user_email );
 $m4is_x169 =get_site_url();
 $m4is_n6062 =$m4is_x169 . "/?memb_autologin=yes&auth_key=" . $m4is_i16 . "&Id=" . $m4is_h21895 . "&Email=" . $m4is_f4930;
 echo '<tr>';
 echo '<th valign="top"><label for="memberium_autologin_link">Autologin Link</label></th>';
 echo '<td>';
 echo "<input id='memberium_autologin_link' disabled='disabled' size='90' value='", $m4is_n6062, "'>";
 echo '&nbsp; <span class="memberium-copy-button">Copy</span>';
 echo '</td>';
 echo '</tr>';
 echo '<style>
				.memberium-copy-button {
                padding: 5px 10px;
                background-color: #007bff;
                color: #fff;
                border: none;
                cursor: pointer;
            }
	        </style>';
 
}
}

