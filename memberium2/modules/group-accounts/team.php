<?php
 class_exists('m4is_r83' )||die();
  final 
class m4is_l870 {
private static object $m4is_a186;
 private static object $m4is_r1546;
 private static array $m4is_e0213;
 private const M4IS_M5218 ='memberium/teams';
 private const M4IS_N45986 ='_memberium/user/max_count';
 private const M4IS_M31546 ='_memberium/user/field_name';
 private 
function __construct(){
 
} public static 
function m4is_c961(): void {
self::$m4is_a186 =m4is_u7102::m4is_c26();
 self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_e0213 =self::$m4is_a186->m4is_f27408();
 
}    public static 
function m4is_o725(int $m4is_z30692, WP_Post $m4is_q2643 ): void {
if(wp_is_post_revision($m4is_z30692 )){
return;
 
}$m4is_h4806 =$m4is_q2643->post_author;
 $m4is_r637 =get_post_meta($m4is_z30692, self::M4IS_M31546, true );
 $m4is_p4712 =get_user_meta($m4is_h4806, self::M4IS_M5218, true );
 $m4is_p4712 =is_array($m4is_p4712 )? $m4is_p4712 : [];
 unset($m4is_p4712[$m4is_r637]);
 update_user_meta($m4is_h4806, self::M4IS_M5218, $m4is_p4712 );
 global $wpdb;
 $m4is_e80 =m4is_u81::m4is_t135();
 $m4is_v2613 ="SELECT child_uid FROM %i WHERE `team_id` = %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_z30692 );
 $m4is_y89162 =$wpdb->get_col($m4is_v2613 );
 foreach($m4is_y89162 as $m4is_m617 ){
self::m4is_c4061($m4is_m617, $m4is_z30692 );
 
}
} public static 
function m4is_v07(string $m4is_r637 ): void {
$m4is_y66291 =['post_type' =>'memb_team', 'meta_key' =>self::M4IS_M31546, 'meta_value' =>$m4is_r637, 'posts_per_page' =>-1, ];
 $m4is_v76912 =new WP_Query($m4is_y66291 );
 if(!$m4is_v76912->have_posts()){
return;
 
}while($m4is_v76912->have_posts()){
$m4is_v76912->the_post();
 $m4is_z30692 =get_the_ID();
 $m4is_u845 =get_post_field('post_author', $m4is_z30692 );
 wp_delete_post($m4is_z30692, true );
 
}
} private static 
function m4is_e782(array $m4is_r17206, int $m4is_f087, array $m4is_k824 ): array {
$m4is_b527 =false;
 $m4is_y90451 =self::m4is_h5626($m4is_f087 );
 foreach($m4is_y90451 as $m4is_r637 =>$m4is_z30692 ){
if(get_post_status($m4is_z30692 )== 'publish' ){
continue;
 
}else{
unset($m4is_y90451[$m4is_r637]);
 
}
}foreach($m4is_r17206 as $m4is_q523 =>$m4is_q2643 ){
if(array_key_exists($m4is_q523, $m4is_y90451 )){
if(get_post_status($m4is_y90451[$m4is_q523])){
continue;
 
}else{
unset($m4is_y90451[$m4is_q523]);
 
}
}$m4is_r637 =strtolower($m4is_q523 );
 $m4is_w180 =empty($m4is_k824['keap']['contact'][$m4is_r637])? 0 : (int) $m4is_k824['keap']['contact'][$m4is_r637];
 $m4is_k6739 =apply_filters('memberium/teams/team/name', $m4is_q2643['team_name'], $m4is_f087, $m4is_k824 );
 $m4is_b4068 =wp_insert_post(['post_title' =>$m4is_k6739, 'post_content' =>'', 'post_status' =>'publish', 'post_author' =>$m4is_f087, 'post_type' =>'memb_team', 'meta_input' =>[self::M4IS_M31546 =>$m4is_q2643['field_name'], self::M4IS_N45986 =>$m4is_w180, ]]);
 if($m4is_b4068 ){
$m4is_y90451[$m4is_q523]=$m4is_b4068;
 $m4is_b527 =true;
 
}
}if($m4is_b527 ){
update_user_meta($m4is_f087, self::M4IS_M5218, $m4is_y90451 );
 
}return $m4is_y90451;
 
}      public static 
function m4is_d078(int $m4is_z30692 ): int {
return (int) get_post_meta($m4is_z30692, self::M4IS_N45986, true );
 
} public static 
function m4is_s893(int $m4is_f087, array $m4is_k824 ): void {
if(empty($m4is_k824['umbrella']['is_parent'])){
return;
 
}$m4is_r17206 =self::$m4is_e0213['teams']?? [];
 if(empty($m4is_r17206 )){
return;
 
}$m4is_u956 =self::m4is_e782($m4is_r17206, $m4is_f087, $m4is_k824 );
 $m4is_u956 =is_array($m4is_u956 )? $m4is_u956 : [];
 self::m4is_e206($m4is_u956, $m4is_k824 );
 
} private static 
function m4is_e206(array $m4is_u956, $m4is_k824 ){
if(!is_array($m4is_u956 )){
return;
 
}foreach($m4is_u956 as $m4is_r637 =>$m4is_b4068 ){
$m4is_l9671 =strtolower($m4is_r637 );
 $m4is_w180 =empty($m4is_k824['keap']['contact'][$m4is_l9671])? 0 : (int) $m4is_k824['keap']['contact'][$m4is_l9671];
 $m4is_s6438 =self::m4is_d078($m4is_b4068 );
 if($m4is_s6438 <> $m4is_w180 ){
update_post_meta($m4is_b4068, self::M4IS_N45986, $m4is_w180 );
 
}self::m4is_i21($m4is_b4068 );
 
}
}      public static 
function m4is_h5626(int $m4is_i341 ): array {
$m4is_r17206 =get_user_meta($m4is_i341, self::M4IS_M5218, true );
 $m4is_r17206 =is_array($m4is_r17206 )? $m4is_r17206 : [];
 return $m4is_r17206;
 
} public static 
function m4is_p610(int $m4is_z30692 ): int {
global $wpdb;
 $m4is_e80 =m4is_u81::m4is_t135();
 $m4is_v2613 ="SELECT count(*) FROM %i WHERE `team_id` = %d AND `active` = 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_z30692 );
 $m4is_h973 =$wpdb->get_var($m4is_v2613 );
 return (int) $m4is_h973;
 
} public static 
function m4is_k2074(int $m4is_z30692 ): array {
global $wpdb;
 $m4is_e80 =m4is_u81::m4is_t135();
 $m4is_v2613 ="SELECT `child_uid` FROM %i WHERE `team_id` = %d AND `active` = 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_z30692 );
 $m4is_u6591 =$wpdb->get_col($m4is_v2613 );
 return is_array($m4is_u6591 )? array_filter($m4is_u6591 ): [];
 
} public static 
function m4is_e38(int $m4is_f087 ): array {
global $wpdb;
 $m4is_e80 =m4is_u81::m4is_t135();
 $m4is_v2613 ="SELECT `team_id` FROM %i WHERE `child_uid` = %d AND `active` = 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_f087 );
 $m4is_u6591 =$wpdb->get_col($m4is_v2613 );
 return is_array($m4is_u6591 )? $m4is_u6591 : [];
 
}    public static 
function m4is_o26496(int $m4is_f087, int $m4is_z30692 ): void {
global $wpdb;
 $m4is_r32 =self::m4is_d078($m4is_z30692 );
 $m4is_v6508 =self::m4is_p610($m4is_z30692 );
 if($m4is_v6508 >= $m4is_r32 ){
return;
 
}$m4is_e80 =m4is_u81::m4is_t135();
 $m4is_v2613 ="INSERT INTO %i (`team_id`, `child_uid`, `active` ) VALUES (%d, %d, 1 )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_z30692, $m4is_f087 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 m4is_q82::m4is_q57064($m4is_f087 );
 
} public static 
function m4is_f196(int $m4is_f087 ): void {
global $wpdb;
 $m4is_e80 =m4is_u81::m4is_t135();
 $m4is_v2613 ="DELETE FROM %i WHERE `child_uid` = %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_f087 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 
} public static 
function m4is_c4061(int $m4is_f087, int $m4is_z30692 ): void {
global $wpdb;
 $m4is_e80 =m4is_u81::m4is_t135();
 $m4is_v2613 ="DELETE FROM %i WHERE `team_id` = %d AND `child_uid` = %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_z30692, $m4is_f087 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 m4is_q82::m4is_q57064($m4is_f087 );
 
} public static 
function m4is_i21(int $m4is_z30692 ): void {
global $wpdb;
 $m4is_r32 =self::m4is_d078($m4is_z30692 );
 $m4is_e80 =m4is_u81::m4is_t135();
 $m4is_v2613 ="SELECT `child_uid` FROM %i WHERE `team_id` = %d AND `active` = 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_z30692 );
 $m4is_r403 =$wpdb->get_col($m4is_v2613 );
 if(count($m4is_r403 )<= $m4is_r32 ){
return;
 
}$m4is_m38 =array_slice($m4is_r403, $m4is_r32 );
 $m4is_v2613 ="UPDATE %i SET `active` = 0 WHERE `team_id` = %d AND `child_uid` NOT IN ( " . implode(',', $m4is_m38 ). " )";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, $m4is_e80, $m4is_z30692 );
 $m4is_u6591 =$wpdb->query($m4is_v2613 );
 foreach($m4is_m38 as $m4is_f087 ){
m4is_q82::m4is_q57064($m4is_f087 );
 
}
} public static 
function m4is_t8025(int $m4is_b4068 ): array {
if(empty(self::$m4is_e0213['teams'])){
return [];
 
}$m4is_q523 =get_post_meta($m4is_b4068, self::M4IS_M31546, true );
 if(empty($m4is_q523 )||!array_key_exists($m4is_q523, self::$m4is_e0213['teams'])){
return [];
 
}$m4is_l9321 =array_filter(explode(',', self::$m4is_e0213['teams'][$m4is_q523]['tags']));
 return $m4is_l9321;
 
}   
}

