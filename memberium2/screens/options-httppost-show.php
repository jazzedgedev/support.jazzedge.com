<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 
class m4is_d164 {
static 
function m4is_h689(){
$m4is_j75 =m4is_r83::m4is_c26()->m4is_j498('settings', 'default_reglink_tag');
 echo '<hr>';
 echo '<h3>Registration Link</h3>';
 m4is_h65::m4is_h70259('Default Registration Link Tag', 'default_reglink_tag', $m4is_j75, 'taglistdropdown', ['help_id' =>9289]);
 
}static 
function m4is_o92(){
$m4is_x39508 =stripslashes_deep(m4is_r83::m4is_c26()->get_i2sdk_options());
 $m4is_j75 =m4is_r83::m4is_c26()->m4is_j498('settings', 'httppost_log');
 $m4is_g62 =empty($m4is_x39508['http_post_key'])? '' : $m4is_x39508['http_post_key'];
 $m4is_c6356 =array_filter(explode(',', $m4is_g62));
 $m4is_s4062 =count($m4is_c6356);
 $m4is_w07426 =$m4is_c6356[mt_rand(0, $m4is_s4062 -1)];
 $m4is_l86726 =get_site_url();
 echo '<hr>';
 echo '<h3>HTTP POST URLs</h3>';
 m4is_h65::m4is_y648('HTTP POST Log', 'httppost_log', 21923, $m4is_j75);
 m4is_h65::m4is_i014('HTTP POST Auth Keys', 'http_post_key', $m4is_g62, ['help_id' =>3699, 'disabled' =>true, 'style' =>'text-align:left;width:350px;']);
 echo '<p style="margin-left:25px;">For all provided links below, please verify the http/https in your links match the actual URL of your site.</p>';
 if($m4is_s4062 > 0){
$m4is_n6062 =$m4is_l86726 . '/?operation=contact-update&auth_key=' . urlencode($m4is_w07426 );
 echo '<p style="margin-left:25px;"><b>Update Contact Example:</b> <input type="text" value="' . $m4is_n6062 . '" size="100">';
 echo m4is_h65::m4is_o64(0617);
 $m4is_n6062 =$m4is_l86726 . '/?operation=makepass&auth_key=' . urlencode($m4is_w07426 );
 echo '<p style="margin-left:25px;"><b>Password Generator Example:</b> <input type="text" value="' . $m4is_n6062 . '" size="100">';
 echo m4is_h65::m4is_o64(0613);
 $m4is_n6062 =$m4is_l86726 . '/?operation=contact-delete&auth_key=' . urlencode($m4is_w07426 );
 echo '<p style="margin-left:25px;"><b style="color:red;">Delete User Example:</b> <input type="text" value="' . $m4is_n6062 . '" size="100">';
 
}else{
echo '<p><b>Please create your Auth key in the i2SDK to enable functionality.</b></p>';
 
}
}static 
function m4is_o46(){
$m4is_n316 =m4is_r83::m4is_c26()->m4is_j498('settings', 'allow_autologin');
 $m4is_p4935 =m4is_r83::m4is_c26()->m4is_j498('settings', 'username_field');
 echo '<hr>', '<h3>Autologin URLs</h3>', '<div style="margin-left:25px">';
 if($m4is_n316){
$m4is_c6356 =array_filter(explode(',', m4is_r83::m4is_c26()->m4is_j498('settings', 'autologin_authkeys')));
 $m4is_s4062 =count($m4is_c6356);
 if($m4is_s4062){
$m4is_y96480 =m4is_h65::m4is_o64(363);
 $m4is_l86726 =get_site_url();
 $m4is_w07426 =urlencode($m4is_c6356[mt_rand(0, $m4is_s4062 - 1)]);
 $m4is_y06243 ="{$m4is_l86726
}/?memb_autologin=yes&auth_key={$m4is_w07426
}&Id=~Contact.Id~&{$m4is_p4935
}=~Contact.Email~&redir=/your-page/";
 $m4is_p166 ="{$m4is_l86726
}/?memb_autologin=yes&auth_key={$m4is_w07426
}&forcelogin=1&redir=/your-page/";
 echo "<p>For all provided links below, please verify the http/https in your links match the actual URL of your site. {$m4is_y96480
}</p>";
 echo '<p><b>Email Autologin Example:</b> <input type="text" value="' . $m4is_y06243 . '" size="80"></p>';
 echo '<p><b>Order Form Autologin Example:</b> <input type="text" value="' . $m4is_p166 . '" size="80"></p>';
 
}else{
echo '<p><b>Please create your Auth keys to enable functionality.</b></p>';
 
}
}else{
echo '<p>Autologin Disabled</p>';
 
}echo '</div>';
 
}static 
function m4is_c3905(){
$m4is_c6356 =array_filter(explode(',', m4is_r83::m4is_c26()->m4is_j498('settings', 'autologin_authkeys')));
 $m4is_l86726 =get_site_url();
 $m4is_y96480 =m4is_h65::m4is_o64(16681, $m4is_e63195 ='What\'s this?');
 echo '<hr />';
 echo '<h3>New User Confirmation Links</h3>';
 echo '<p style="margin-left:25px;">For all provided links below, please verify the http/https in your links match the actual URL of your site. ', $m4is_y96480, '</p>';
 foreach ($m4is_c6356 as $m4is_w07426 ){
$m4is_n6062 ="{$m4is_l86726
}/?action=confirmregistration&authkey={$m4is_w07426
}&email=~Contact.Email~&cid=~Contact.Id~";
 echo '<div style="margin-left:25px">';
 echo '<p><strong>Confirmation Link Example:</strong> <input type="text" value="' . $m4is_n6062 . '" size="80"></p>';
 echo '</div>';
 
}
}static 
function m4is_o943(){
$m4is_r6234 =m4is_r83::m4is_c26()->m4is_j498('settings', 'password_field');
 $m4is_e63195 ="Password:  <input id='Contact0{$m4is_r6234
}' name='Contact0{$m4is_r6234
}' type='password' class='regula-validation' data-constraints='@Required(label=&quot;Your Password&quot;, groups=[customer])' /><br /><br />";
 $m4is_y96480 =m4is_h65::m4is_o64(21931);
 if($m4is_r6234 <> 'Password' ){
echo '<hr />';
 echo '<h3>Order Form Password HTML</h3>', '<div style="margin-left:25px">', $m4is_y96480, '<p><strong>Order Form Password Field:</strong>', '<input type="text" name="" id="" size="80" value="', htmlentities($m4is_e63195 ), '"></p>', '</div>';
 
}
}static 
function m4is_o719(){
echo '<ul>';
 echo '<form method="POST" action="">';
 wp_nonce_field(m4is_r83::m4is_c26()->m4is_j541(), 'memberium_options_nonce' );
 m4is_d164::m4is_h689();
 m4is_d164::m4is_o92();
 m4is_d164::m4is_o46();
 m4is_d164::m4is_c3905();
 m4is_d164::m4is_o943();
 echo '</ul>';
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 echo '</form>';
 m4is_r83::m4is_c26()->m4is_s965('view_http_post' );
 
}private 
function __construct(){

}
}m4is_d164::m4is_o719();

