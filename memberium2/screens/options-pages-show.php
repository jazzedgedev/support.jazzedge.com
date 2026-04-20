<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 $m4is_e0213 =m4is_r83::m4is_c26()->m4is_j498('settings');
 $m4is_e5906 =get_option('memberium_pages', []);
 echo '<form method="POST" action="">';
 wp_nonce_field(m4is_r83::m4is_c26()->m4is_j541(), 'memberium_options_nonce' );
 echo '<ul>';
 echo '<h3>System Pages</h3>';
 $m4is_s926 ='';
 if(!empty($m4is_e0213['login_url'])){
$m4is_s926 =' <button formtarget="_blank" formaction="' . admin_url(). 'post.php?post=' . $m4is_e0213['login_url']. '&action=edit">Edit</button> <button formtarget="_blank" formaction="' . get_permalink($m4is_e0213['login_url']). '">View</button> ';
 
}m4is_h65::m4is_h70259('Login Page <strong style="color:red;">(Caution)</strong>', 'login_url', (int) $m4is_e0213['login_url'], 'pagelistdropdown', ['help_id' =>1206, 'units' =>$m4is_s926]);
 $m4is_f602 =[];
 $m4is_f602[]=['n' =>'Membership Registration Page', 'k' =>'registration_page', 'h' =>0000];
 $m4is_f602[]=['n' =>'My Account Page', 'k' =>'my_account', 'h' =>0000];
 $m4is_f602[]=['n' =>'New Account Page', 'k' =>'new_account', 'h' =>0000];
 $m4is_f602[]=['n' =>'Member Profile Page', 'k' =>'profile_page', 'h' =>0000];
 foreach($m4is_f602 as $m4is_d3012 ){
$m4is_o015 =$m4is_d3012['k'];
 $m4is_b4068 =isset($m4is_e5906[$m4is_o015])? (int) $m4is_e5906[$m4is_o015]: 0;
 echo '<li><label for="', $m4is_o015, '">', $m4is_d3012['n'], '</label>';
 echo '<input value="', $m4is_b4068, '" type="hidden" id="', $m4is_o015, '" name="pages[', $m4is_o015, ']" class="dropdown pagelistdropdown">';
 m4is_h65::m4is_o64($m4is_d3012['h']);
 if($m4is_b4068 ){
echo ' <button formtarget="_blank" formaction="', admin_url(). 'post.php?post=', $m4is_b4068, '&action=edit">Edit</button> ';
 echo ' <button formtarget="_blank" formaction="', get_permalink($m4is_b4068 ), '">View</button> ';
 
}m4is_h65::m4is_o64(1208 );
 echo '</li>';
 
}echo '</ul>';
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 echo '&nbsp;<br>';
 echo '<h2>Templates</h2>';
 echo 'Load Page Templates from <select name="template_id" id="">';
 $m4is_y65 =m4is_s6729::m4is_c26()->m4is_n6203();
 foreach($m4is_y65 as $m4is_l9671 =>$m4is_v586 ){
echo '<option value="', ($m4is_l9671 + 1 ), '">', $m4is_v586['name'], '</option>';
 
}echo '</select> to page ';
 echo '<input value="0" type="hidden" id="" name="target_post_id" class="dropdown pagelistdropdown">';
 echo '&nbsp;<br>';
 echo '<p>';
 echo '<input type="submit" name="page_load" value="Load Single Template" class="button-primary">';
 echo '&nbsp;&nbsp;';
 echo '<input type="submit" name="page_load" value="Load All Templates" class="button-primary">';
 echo '</p>';
 echo '<p>';
 echo '<input type="submit" name="page_load" value="Install Email Templates" class="button-primary">';
 echo '</p>';
 if(isset($m4is_y758 )){
echo '<p>Added ', $m4is_y758, ' Email templates</p>';
 
}echo '</form>';

