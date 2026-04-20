<?php
 final 
class m4is_k86940 {
static private $m4is_r1546;
 static private $m4is_n5049;
 public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_n5049 =self::$m4is_r1546->m4is_b32198();
 
}private static 
function m4is_q496(): void {
global $wpdb;
 $m4is_u87692 =(bool) self::$m4is_r1546->m4is_j498('settings', 'sync_ecommerce', 0 );
 if(!$m4is_u87692 ){
return;
 
}$m4is_v2613 ="SELECT `id`, `contact_id` `date_created` `job_id` FROM %i WHERE ( `paystatus` = 0 OR `totaldue` > `totalpaid` ) ";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_v87365::m4is_p63285());
 
}
}

