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
class m4is_d47529 {
private static $m4is_p43682 ='memberium/cron';
  public static 
function m4is_g6231(array $m4is_h693 ): array {
return array_merge($m4is_h693, ['1min' =>['interval' =>60, 'display' =>__('Every minute' )], '3min' =>['interval' =>3 * 60, 'display' =>__('Every 3 minutes' )], '5min' =>['interval' =>5 * 60, 'display' =>__('Every 5 minutes' )], '20min' =>['interval' =>20 * 60, 'display' =>__('Every 20 minutes' )], '30min' =>['interval' =>30 * 60, 'display' =>__('Every 30 minutes' )], ]);
 return $m4is_h693;
 
} private static 
function m4is_u60841(): array {
return ['memberium/actionsets/sync' =>['i' =>'twicedaily', 'o' =>0 ], 'memberium/affiliates/running_totals' =>['i' =>'twicedaily', 'o' =>0 ], 'memberium/contacts/sync_custom_fields' =>['i' =>'twicedaily', 'o' =>0 ], 'memberium/contacts/async' =>['i' =>'3min', 'o' =>0 ], 'memberium/contacts/makepass_scan' =>['i' =>'3min', 'o' =>0 ], 'memberium/invoices/sync' =>['i' =>'hourly', 'o' =>0 ], 'memberium/maintenance/logs/trim' =>['i' =>'twicedaily', 'o' =>0 ], 'memberium/maintenance/updater' =>['i' =>'twicedaily', 'o' =>0 ], 'memberium/products/sync' =>['i' =>'twicedaily', 'o' =>0 ], 'memberium/subscriptions/scan_expired' =>['i' =>'twicedaily', 'o' =>0 ], 'memberium/subscriptions/sync' =>['i' =>'twicedaily', 'o' =>0 ], 'memberium/tags/categories/sync' =>['i' =>'twicedaily', 'o' =>0 ], 'memberium/tags/sync' =>['i' =>'twicedaily', 'o' =>0 ], 'memberium/maintenance/daily' =>['i' =>'daily', 'o' =>0 ], 'memberium_maintenance' =>['i' =>'hourly', 'o' =>0 ], 'memberium/maintenance/daily' =>['i' =>'daily', 'o' =>0 ],  ];
 
} static 
function m4is_x0769(){
$m4is_m512 =time();
 $m4is_k104 =self::m4is_u60841();
 foreach($m4is_k104 as $m4is_y3576 =>$m4is_l91805 ){
$m4is_j724 =$m4is_l91805['o']== 0 ? $m4is_m512 : $m4is_m512 + rand(0, 180 );
 wp_next_scheduled($m4is_y3576 )||wp_schedule_event($m4is_j724, $m4is_l91805['i'], $m4is_y3576 );
 
}update_option(self::$m4is_p43682, $m4is_m512 );
 
} static 
function m4is_q845(){
$m4is_k104 =self::m4is_u60841();
 foreach($m4is_k104 as $m4is_y3576 =>$m4is_l91805 ){
wp_clear_scheduled_hook($m4is_y3576 );
 
}delete_option(self::$m4is_p43682 );
 
} static 
function m4is_g62481(){

}
}

