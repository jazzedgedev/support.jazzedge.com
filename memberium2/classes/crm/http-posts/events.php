<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_o239 {

function add($m4is_c05328, $m4is_j613, $m4is_m5907){
if($m4is_c05328 == ''){
m4is_j586::m4is_x7134();
 $m4is_c3749 =isset($m4is_j613['debug']);
 if($m4is_c3749)echo __LINE__, " - Debug Mode Enabled\n";
 if($m4is_c3749)echo __LINE__, " - POST: ", print_r($m4is_m5907, true), "\n";
 $m4is_k52736 =empty($m4is_j613['name'])? '' : trim($m4is_j613['name']);
 $m4is_a873 =time();
 m4is_r83::m4is_c26()->m4is_u3540($m4is_k52736);
 echo 'Operation Completed';
 
}
}
}

