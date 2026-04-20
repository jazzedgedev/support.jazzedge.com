<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_j01762 {
private $m4is_r1546;
 private $m4is_r9613;
 private $m4is_o27056;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_q7148();
 
}private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_r9613 =$this->m4is_r1546->m4is_i76('appname' );
 $this->m4is_o27056 =m4is_u7102::m4is_c26();
 
}private 
function m4is_k807(){
$m4is_i05347 =$this->m4is_q17536();
 foreach($m4is_i05347 as $m4is_i341 ){
$m4is_b76 =$this->m4is_o27056->m4is_h43206($m4is_i341 );
 if(empty($m4is_b76 )){
$m4is_q95421 =$this->m4is_o27056->m4is_u073($m4is_i341 );
 $this->m4is_o27056->m4is_h263($m4is_i341, $m4is_q95421 );
 
}
}
}private 
function m4is_x13(){

}private 
function m4is_q17536(): array {
global $wpdb;
 $m4is_v61358 =$this->m4is_o27056->m4is_s54627();
 $m4is_e80 =m4is_p40::m4is_o1723();
 $m4is_v2613 ="SELECT `id` FROM `{$m4is_e80
}` WHERE `fieldname` = %s AND `value` LIKE 'prnt-%' AND `appname` = %s ORDER BY `id` ASC";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_v61358, $this->m4is_r9613 );
 $m4is_v30712 =$wpdb->get_col($m4is_v2613 );
 $m4is_v30712 =is_array($m4is_v30712 )? $m4is_v30712 : [];
 $m4is_c57106 =[];
 foreach($m4is_v30712 as $m4is_h21895 ){
$m4is_c57106[]=m4is_p40::m4is_i6158($m4is_h21895 );
 
}return $m4is_c57106;
 
} public 
function m4is_q7148(): void {
global $wpdb;
 $m4is_z765 =$this->m4is_o27056->m4is_o136();
 $m4is_e80 =m4is_u81::m4is_a6804();
 if(!$m4is_z765 ){
return;
 
} $m4is_v2613 ="SELECT `child_uid` FROM `{$m4is_e80
}` WHERE `active` = 1 AND `sync` = 0";
 $m4is_b6438 =$wpdb->get_col($m4is_v2613 );
 $m4is_b6438 =is_array($m4is_b6438 )? $m4is_b6438 : [];
 if(!empty($m4is_d38714 )){
$m4is_l8931 =[];
 foreach($m4is_b6438 as $m4is_x09186 ){
$m4is_l8931[]=m4is_p40::m4is_w58096($m4is_x09186 );
 
}$m4is_l8931 =array_filter($m4is_l8931 );
 if($m4is_l8931 ){
$this->m4is_r1546->m4is_p98460($m4is_l8931, $m4is_z765 );
 
}$m4is_a652 =implode(',', $m4is_b6438 );
 $m4is_v2613 ="UPDATE %i SET `sync` = 1 WHERE `child_uid` IN ( {$m4is_a652
} )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80 );
 $m4is_v2613 =$wpdb->query($m4is_v2613 );
 
} $m4is_v2613 ="SELECT `child_uid` FROM `{$m4is_e80
}` WHERE `active` = 0 AND `sync` = 0";
 $m4is_b6438 =$wpdb->get_col($m4is_v2613 );
 $m4is_b6438 =is_array($m4is_b6438 )? $m4is_b6438 : [];
 if(!empty($m4is_d38714 )){
$m4is_l8931 =[];
 foreach($m4is_b6438 as $m4is_x09186 ){
$m4is_l8931[]=m4is_p40::m4is_w58096($m4is_x09186 );
 
}$m4is_l8931 =array_filter($m4is_l8931 );
 if($m4is_l8931 ){
$this->m4is_r1546->m4is_p98460($m4is_l8931, $m4is_z765 );
 
}$m4is_a652 =implode(',', $m4is_b6438 );
 $m4is_v2613 ="UPDATE %i SET `sync` = 1 WHERE `child_uid` IN ( {$m4is_a652
} )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80 );
 $m4is_v2613 =$wpdb->query($m4is_v2613 );
 
}$m4is_v2613 ="DELETE FROM %i WHERE `sync` = 1 AND `active` = 0";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80 );
 $m4is_v2613 =$wpdb->query($m4is_v2613 );
 
}
}

