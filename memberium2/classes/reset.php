<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_y18042 {
static $m4is_e394 =false;
 public static 
function reset_app(){
self::m4is_s0238();
 self::m4is_v42();
 self::m4is_e46327();
 self::m4is_g69483();
 self::m4is_i432();
 
} private static 
function m4is_s0238(){
echo '<strong>Clearing i2SDK</strong><br />';
 if(self::$m4is_e394 ){
self::m4is_s1874('i2sdk' );
 
}else{
echo 'Deleting i2sdk<br />';
 
}
}   private static 
function m4is_v42(){
echo '<strong>Clearing User Meta</strong><br />';
 $m4is_u6184 =['infusionsoft_user_id', 'm4is/%', 'memb\_%', 'memberium%', ];
 foreach ($m4is_u6184 as $m4is_c8657 ){
$m4is_l79562 =self::m4is_h52470($m4is_c8657 );
 foreach($m4is_l79562 as $m4is_r1692 ){
if(self::$m4is_e394 ){
delete_user_meta($m4is_r1692['user_id'], $m4is_r1692['meta_key']);
 
}else{
printf('User Meta - %d = %s<br />', $m4is_r1692['user_id'], $m4is_r1692['meta_key']);
 
}
}
}
}private static 
function m4is_h52470($m4is_c8657 ): array {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare('SELECT `user_id`, `meta_key` FROM %i WHERE `meta_key` LIKE %s', $wpdb->usermeta, $m4is_c8657 );
 $m4is_l79562 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 return $m4is_l79562;
 
}   private static 
function m4is_e46327(){
echo '<strong>Clearing Postmeta</strong><br />';
 $m4is_u6184 =['_is4wp_%', '_memberium_%', ];
 foreach ($m4is_u6184 as $m4is_c8657 ){
$m4is_l79562 =self::m4is_j90($m4is_c8657 );
 foreach($m4is_l79562 as $m4is_r1692 ){
if(self::$m4is_e394 ){
delete_post_meta($m4is_r1692['post_id'], $m4is_r1692['meta_key']);
 
}else{
printf('Post Meta - %d = %s<br />', $m4is_r1692['post_id'], $m4is_r1692['meta_key']);
 
}
}
}
}private static 
function m4is_j90($m4is_c8657 ): array {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare('SELECT `post_id`, `meta_key` FROM %i WHERE `meta_key` LIKE %s', $wpdb->postmeta, $m4is_c8657 );
 $m4is_l79562 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 return $m4is_l79562;
 
}    private static 
function m4is_g69483(){
echo '<strong>Clearing Options</strong><br />';
 $m4is_u6184 =['i2sdk', 'memberium%', ];
 foreach ($m4is_u6184 as $m4is_c8657 ){
$m4is_r37596 =self::m4is_x014($m4is_c8657 );
 foreach($m4is_r37596 as $m4is_q1470 ){
if(self::$m4is_e394 ){
self::m4is_s1874($m4is_q1470 );
 
}else{
printf('Deleting Option - %s<br />', $m4is_q1470 );
 
}
}
}
}private static 
function m4is_x014($m4is_c8657 ): array {
global $wpdb;
 $m4is_v2613 =$wpdb->prepare('SELECT `option_name` FROM %i WHERE `option_name` LIKE %s', $wpdb->options, $m4is_c8657 );
 $m4is_r37596 =$wpdb->get_col($m4is_v2613 );
 return $m4is_r37596;
 
}private static 
function m4is_s1874($m4is_j56697 ){
update_option($m4is_j56697, '', false );
 delete_option($m4is_j56697 );
 
} private static 
function m4is_q24(): array {
$m4is_w516 =get_option('memberium_tables', []);
 $m4is_w516 =is_array($m4is_w516 )? $m4is_w516 : [];
 return $m4is_w516;
 
}    private static 
function m4is_i432(){
global $wpdb;
 $m4is_w516 =self::m4is_q24();
 foreach($m4is_w516 as $m4is_m7426 ){
if(self::$m4is_e394 ){
$wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i", $m4is_m7426 ));
 
}else{
printf('Dropping Table - %s<p>', $m4is_m7426 );
 
}
}
}
}

