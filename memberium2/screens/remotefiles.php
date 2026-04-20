<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83')||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_t49573::m4is_z95();
 
class m4is_t49573 {
private static $m4is_e0213 =[];
 static 
function m4is_z95(){
self::m4is_h269();
 m4is_h65::m4is_n25();
 self::m4is_c66831();
 self::m4is_w689();
 self::m4is_c71890();
 self::m4is_a7526();
 self::m4is_e16();
 
}private static 
function m4is_a7526(){
$m4is_y30866 =m4is_a01587::m4is_g18();
 echo '<h3>Add New S3 Profile</h3>';
 echo '<div style="width:800px;">';
 echo '<form method="POST" action="">';
 echo '<table class="widefat">';
 echo '<input type="hidden" name="type" value="s3">';
 echo '<tr>';
 echo '<th nowrap="nowrap">Profile Name</th>';
 echo '<th nowrap="nowrap">Profile Type</th>';
 echo '<th nowrap="nowrap">Default Host</th>';
 echo '<th nowrap="nowrap">Default Bucket</th>';
 echo '<th nowrap="nowrap">Region</th>';
 echo '<th nowrap="nowrap">Access Key</th>';
 echo '<th nowrap="nowrap">S3 Secret Key</th>';
 echo '<th nowrap="nowrap">Expiration</th>';
 echo '</tr>';
 echo '<tr>';
 echo '<td><input name="profile_name" type="text" size="10" autocomplete="off"/></td>';
 echo '<td><select name="profile_type"><option value="s3">Amazon S3</option></select></td>';
 echo '<td><input name="default_host" type="text" size="20" placeholder="s3.amazonaws.com" autocomplete="off"/></td>';
 echo '<td><input name="default_bucket" type="text" size="20" autocomplete="off"/></td>';
 echo '<td><select name="region"/>';
 foreach($m4is_y30866 as $m4is_o015 =>$m4is_k72){
echo "<option value='{$m4is_k72
}' ".($m4is_k72 == 'us-east-1' ? ' selected ' : ''). ">{$m4is_o015
}</option>";
 
}echo '</select></td>';
 echo '<td><input name="s3_access_key" type="text" size="20" autocomplete="off"/></td>';
 echo '<td><input name="s3_secret_key" type="text" size="20" autocomplete="off"/></td>';
 echo '<td><input name="expiration" type="text" size="4" autocomplete="off"/></td>';
 echo '</tr>';
 echo '</table>';
 echo '&nbsp;<br />';
 echo '<input type="submit" name="add-profile" value="Add Remote Profile" class="button-primary" />';
 echo '<hr />';
 echo '</form>';
 
}private static 
function m4is_c71890(){
$m4is_m54 =!empty(self::$m4is_e0213['remote_files'])? self::$m4is_e0213['remote_files']: [];
 $m4is_y43087 =count($m4is_m54 );
 echo '<form method="POST" action="" autocomplete="off">';
 echo '<table class="widefat" style="white-space:nowrap;">';
 echo '<tr>';
 echo '<th nowrap="nowrap">Profile Name</th>';
 echo '<th nowrap="nowrap">Default Host</th>';
 echo '<th nowrap="nowrap">Default Bucket</th>';
 echo '<th nowrap="nowrap">Region</th>';
 echo '<th nowrap="nowrap">S3 Access Key</th>';
 echo '<th nowrap="nowrap">S3 Secret Key</th>';
 echo '<th nowrap="nowrap">Expiration</th>';
 echo '<th>Delete?</th>';
 echo '</tr>';
 if(empty($m4is_m54)){
echo '<tr><td colspan="99">You have no remote storage defined.</td></tr>';
 
}else{
foreach ($m4is_m54 as $m4is_n73190 =>$m4is_y50 ){
if($m4is_y50['type']== 's3' ){
echo '<tr>';
 echo '<td><i class="fab fa-aws"></i> ', $m4is_y50['name'], '</td>';
 echo '<td>', $m4is_y50['host'], '</td>';
 echo '<td>', $m4is_y50['bucket'], '</td>';
 echo '<td>', isset($m4is_y50['region'])? $m4is_y50['region']: 'Default', '</td>';
 echo '<td>', $m4is_y50['access_key'], '</td>';
 echo '<td>', substr($m4is_y50['secret_key'], 0, 8 ), '********</td>';
 echo '<td>', (int)$m4is_y50['expiration'], '</td>';
 echo '<td>';
 echo '<input type="checkbox" name="delete[' . $m4is_n73190 . ']">';
 echo '</td>';
 echo '</tr>';
 
}
}
}echo '</table>';
 echo '&nbsp;<br />';
 if($m4is_y43087 > 0 ){
echo '<input type="submit" name="delete-profiles" value="Delete Profiles" class="button-secondary" />';
 
}echo '</form>';
 echo '</div>';
 
}private static 
function m4is_w689(){
m4is_s6729::m4is_c26()->m4is_a4215();
  echo '<div class="wrap">';
 echo '<hr />';
 
}private static 
function m4is_e16(){
echo '</div>';
 
}private static 
function m4is_c66831(){
if($_SERVER['REQUEST_METHOD']== 'POST' ){
if(isset($_POST['delete'])){
foreach ($_POST['delete']as $m4is_r69245 =>$m4is_c31 ){
if($m4is_c31 == 'on' ){
unset(self::$m4is_e0213['remote_files'][$m4is_r69245]);
 m4is_h65::m4is_z896('Remote Storage Profile Deleted', 'error' );
 
}
}
}if(isset($_POST['add-profile'])&&trim($_POST['profile_name'])> '' ){
$m4is_r69245 =strtolower(trim($_POST['profile_name']));
 $m4is_p76648 =[];
 $m4is_p76648['name']=trim($_POST['profile_name']);
 $m4is_p76648['type']='s3';
 $m4is_p76648['access_key']=trim($_POST['s3_access_key']);
 $m4is_p76648['expiration']=(int) $_POST['expiration']> 0 ? (int)$_POST['expiration']: 300;
 $m4is_p76648['secret_key']=trim($_POST['s3_secret_key']);
 $m4is_p76648['host']=trim($_POST['default_host'])> '' ? trim($_POST['default_host']): 's3.amazonaws.com';
 $m4is_p76648['bucket']=trim($_POST['default_bucket']);
 $m4is_p76648['region']=isset($_POST['region'])? trim($_POST['region']): 'us-east-1';
 self::$m4is_e0213['remote_files'][$m4is_r69245]=$m4is_p76648;
 
}m4is_r83::m4is_c26()->m4is_n368(self::$m4is_e0213 );
 
}
}private static 
function m4is_h269(){
self::$m4is_e0213 =m4is_r83::m4is_c26()->m4is_j498();
 
}
}

