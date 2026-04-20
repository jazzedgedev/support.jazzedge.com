<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 if(!function_exists('wp_new_user_notification')){
 
function wp_new_user_notification(int $m4is_f087, $m4is_f56619 ='' ){
$m4is_r1546 =m4is_r83::m4is_c26();
 $m4is_s680 =0;
 $m4is_h21895 =0;
 $m4is_r37596 =$m4is_r1546->m4is_j498();
 $m4is_l17096 =get_userdata($m4is_f087 );
 $m4is_p9540 =(bool) $m4is_r1546->m4is_j498('settings', 'sync_new_wp_users' );
 $m4is_r6234 =(string) $m4is_r1546->m4is_j498('settings', 'password_field' );
 $m4is_r2369 =(bool) $m4is_r1546->m4is_j498('settings', 'local_auth_only', false );
 $m4is_r09164 =(int) $m4is_r1546->m4is_j498('settings', 'new_user_registration_tag', 0 );
 $m4is_o10537 =(int) $m4is_r1546->m4is_j498('settings', 'password_reset_tag', 0 );
 $m4is_i935 =['Email' =>$m4is_l17096->user_email, $m4is_r6234 =>$m4is_f56619, ];
 if($m4is_r2369 ){
unset($m4is_i935[$m4is_r6234]);
 
}if(!empty($m4is_p9540 )){
$m4is_h21895 =m4is_p40::m4is_k82670($m4is_i935 );
 if($m4is_r09164 ){
$m4is_r1546->m4is_r1476()->grpAssign($m4is_h21895, $m4is_r09164 );
 
}
}  $m4is_n42 =wp_specialchars_decode(get_option('blogname' ), ENT_QUOTES );
 $m4is_a173 =sprintf(__('New user registration on your site %s:' ), $m4is_n42 ). "\r\n\r\n";
 $m4is_a173 .= sprintf(__('Username: %s'), $m4is_l17096->user_login ). "\r\n\r\n";
 $m4is_a173 .= sprintf(__('E-mail: %s'), $m4is_l17096->user_email ). "\r\n";
 @wp_mail(get_option('admin_email' ), sprintf(__('[%s] New User Registration' ), $m4is_n42 ), $m4is_a173 );
 if(empty($m4is_f56619 )){
return;
 
}$m4is_a173 =sprintf(__('Username: %s' ), $m4is_l17096->user_login ). "\r\n";
 $m4is_a173 .= sprintf(__('Password: %s' ), $m4is_f56619 ). "\r\n";
 $m4is_a173 .= wp_login_url(). "\r\n";
 wp_mail($m4is_l17096->user_email, sprintf(__('[%s] Your username and password' ), $m4is_n42 ), $m4is_a173 );
 
}
}

