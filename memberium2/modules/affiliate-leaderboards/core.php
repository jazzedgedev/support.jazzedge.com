<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_v967 {
const VERSION ='1.0';
 private $m4is_r1546;
 private $m4is_x25 =false;
 private $m4is_b50618 =false;
 private $m4is_f683 =false;
 private $m4is_m63284 =[];
  public static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
if(!m4is_s52::m4is_f27()){
return;
 
}$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_h269();
 
} private 
function m4is_h269(): void {
$this->m4is_r1546->m4is_p39(['m4is_h09' =>__DIR__ . '/cron', 'm4is_q03964' =>__DIR__ . '/shortcodes', ]);
 $this->m4is_n89();
 if(is_admin()){
require_once __DIR__ . '/admin.php';
 m4is_w28::m4is_c26();
 
}else{
$this->m4is_s2469();
 
}
} private 
function m4is_n89(): void {
$m4is_m512 =time();
 $m4is_k104 =['memberium/affiliates/scan_stale_leaderboards' =>['i' =>'twicedaily', 'o' =>0 ], ];
 foreach($m4is_k104 as $m4is_y3576 =>$m4is_l91805 ){
$m4is_j724 =$m4is_l91805['o']== 0 ? $m4is_m512 : $m4is_m512 + rand(0, 30 );
 wp_next_scheduled($m4is_y3576 )||wp_schedule_event($m4is_j724, $m4is_l91805['i'], $m4is_y3576 );
 
}add_action('memberium/affiliates/scan_stale_leaderboards', ['m4is_h09', 'm4is_l12']);
 
} public 
function m4is_w45(): string {
return self::VERSION;
 
} public 
function m4is_l18(): array {
$m4is_m63284 =get_option('memberium_leaderboard_profiles', []);
 $m4is_m63284 =is_array($m4is_m63284 )? $m4is_m63284 : [];
 return $m4is_m63284;
 
} public 
function m4is_j3129(array $m4is_m63284 ): void {
update_option('memberium_leaderboard_profiles', $m4is_m63284 );
 $this->m4is_m63284 =$m4is_m63284;
 
} 
function m4is_x4830($m4is_d07693 ): array {
$this->m4is_m63284 =$this->m4is_l18();
 foreach($this->m4is_m63284 as $m4is_l9671 =>$m4is_y50 ){
if($m4is_d07693 === $m4is_l9671 ||0 === strcasecmp($m4is_d07693, $m4is_y50['name'])){
return $m4is_y50;
 
}
}return [];
 
} 
function m4is_s2469(): void {
add_action('memberium/shortcodes/add', [$this, 'm4is_s2469']);
 add_shortcode('memb_show_leaderboard', ['m4is_q03964', 'm4is_c046']);
 
} 
}

