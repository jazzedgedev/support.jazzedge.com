<?php
 class_exists('m4is_r83' )||die();
 if(!defined('PROFILE_BUILDER_VERSION' )){
return;
 
} add_action('wppb_password_reset', 'm4is_l362', 10, 2 );
  
function m4is_l362($m4is_f087, $m4is_m676 ){
 $m4is_f087 =abs(intval($m4is_f087 ));
  $m4is_h21895 =m4is_p40::m4is_w58096($m4is_f087 );
  $m4is_s36520 =m4is_r83::m4is_c26()->m4is_j498('settings', 'password_field' );
  if($m4is_h21895 < 1 ||empty($m4is_m676 )||empty($m4is_s36520 )){
return;
 
} m4is_r83::m4is_c26()->m4is_s56($m4is_s36520, $m4is_m676, $m4is_h21895 );
 
}add_filter('memberium/modules/active/names', function($m4is_y634 ){
return array_merge($m4is_y634, ['Profile Builder for Memberium']);
 
});

