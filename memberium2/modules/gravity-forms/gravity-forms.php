<?php
 class_exists('m4is_r83' )||die();
 if(is_admin()){
add_filter('memberium/modules/active/names', function($m4is_y634){
return array_merge($m4is_y634, ['GravityForms Support']);
 
});
 
}else{
require_once __DIR__ . '/core.php';
 m4is_x2469::m4is_c26();
 
}

