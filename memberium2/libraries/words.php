<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_g689 {
private static $m4is_m7426;
 private static $m4is_n6062;
 private static $m4is_t73196;
 static 
function m4is_c961(){
global $wpdb;
 self::$m4is_m7426 =$wpdb->prefix . 'memberium_words';
 self::$m4is_n6062 ='https://membership-system-downloads.webpowerandlight.com/data/eff_large_wordlist.txt';
 self::$m4is_t73196 ='memberium/database/words';
 
}private 
function __construct(){

}     public static 
function m4is_m662(): string {
return self::$m4is_m7426;
 
} public static 
function m4is_t59042(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::$m4is_m7426;
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(10) unsigned NOT NULL AUTO_INCREMENT, \n" . "word varchar(10) NOT NULL, \n" . "PRIMARY KEY (id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
} public static 
function m4is_y46135(){
global $wpdb;
 $m4is_p715 =self::m4is_f86756();
 if($m4is_p715 > 7700 ){
return;
 
}$m4is_n548 =wp_remote_get(self::$m4is_n6062 );
 if(is_a($m4is_n548, 'WP_Error' )){
return;
 
}$m4is_p48691 =0;
 $m4is_a694 =array_filter(explode("\n", wp_remote_retrieve_body($m4is_n548 )));
 $m4is_a694 =array_map(function(string $m4is_v586 ){
return trim(substr($m4is_v586, 6, 9 ));
 
}, $m4is_a694 );
 if(!empty($m4is_a694 )){
foreach($m4is_a694 as $m4is_f95062 ){
$wpdb->insert(self::$m4is_m7426, ['word' =>$m4is_f95062]);
 
}
}self::m4is_r7509();
 
}   private static 
function m4is_r7509(){
global $wpdb;
 $m4is_p48691 =$wpdb->get_var($wpdb->prepare("SELECT max(`id`) FROM %i WHERE 1", self::$m4is_m7426 ));
 update_option(self::$m4is_t73196, $m4is_p48691 );
 
} private static 
function m4is_f86756(): int {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare("SELECT count(`id`) FROM %i WHERE 1", self::$m4is_m7426 );
 $m4is_h973 =(int) $wpdb->get_var($m4is_v2613 );
 return $m4is_h973;
 
}
}

