<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_e35 {
 static 
function m4is_k2851($m4is_f087, $m4is_l17096){
$m4is_f087 =(int) $m4is_f087;
 if($m4is_f087){
$m4is_h21895 =memb_getContactIdByUserId($m4is_f087);
 if($m4is_h21895){
$m4is_l17096 =m4is_l5841::m4is_c09($m4is_l17096);
 if(is_a($m4is_l17096, 'WP_Error')){
wp_safe_redirect(home_url());
 exit;
 
}
}
}
}
}

