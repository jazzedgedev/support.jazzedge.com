<?php
 class_exists('m4is_r83')||die();
 if(is_admin()){
require_once(__DIR__ . '/admin.php' );
 add_filter('memberium/modules/active/names', function($m4is_y634 ){
return array_merge($m4is_y634, ['WPAL Path Protect for Memberium']);
 
});
 new m4is_z902;
 
}else{
require_once(__DIR__ . '/frontend.php' );
 new m4is_z59;
 
}

