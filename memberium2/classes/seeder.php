<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_b351 {
static 
function m4is_y76241(){
$m4is_p786 =get_option('m4is/seeder/tag', 0 );
 $m4is_d3012 =get_option('m4is/seeder/page', 0 );
 $m4is_t6421 =get_option('m4is/seeder/last_run', 0 );
 if(!$m4is_p786 ){
return;
 
}if(time()- $m4is_t6421 < 5 ){
return;
 
}
} private static 
function m4is_f367($m4is_d3012, $m4is_p786 ){
if(empty($m4is_p786 )){
return;
 
}$m4is_r1546 =m4is_r83::m4is_c26();
 $m4is_c92430 =1000;
 $m4is_e80 ='Contact';
 $m4is_d3012 =(int) $m4is_d3012;
 $m4is_h3647 =m4is_c69807::m4is_f5248($m4is_e80, false );
 $m4is_e69637 =0;
 $m4is_r9613 =$m4is_r1546->m4is_i76('appname' );
 $m4is_v76912 =['Groups' =>$m4is_p786 ];
 $m4is_m615 =m4is_c69807::m4is_o986($m4is_e80, $m4is_c92430, $m4is_d3012, $m4is_v76912, $m4is_h3647 );
 foreach($m4is_m615 as $row){
$m4is_r1546->m4is_v1694($row, false );
 
}if(count($m4is_m615)< $m4is_c92430){
update_option('m4is/seeder/page', 0, false);
 
}else{
update_option('m4is/seeder/page', ($m4is_d3012 + 1), false);
 
}
}
}

