<?php
 class_exists('m4is_h68' )||die();
 final 
class m4is_v076 {
private static object $m4is_r1546;
 private static object $m4is_f683;
 private static string $m4is_f4218;
 private 
function __construct(){
 
}public static 
function m4is_c961(): void {
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_f683 =m4is_f58::m4is_c26();
 self::$m4is_f4218 ='memberium';
 
}    public static 
function m4is_i18563(): void {
if(!self::$m4is_r1546->m4is_f1072($_POST['signature'], $_POST['parameters'])){
wp_die(_x('Security Check Failed - Signature Validation Error', self::$m4is_f4218, self::$m4is_f4218 ));
 exit;
 
}if(!wp_verify_nonce($_POST['_wpnonce'], 'memberium/learndash/delete_history/' . (int) $_POST['user_id'])){
wp_die('Security Check Failed - Nonce Validation Error' );
 
}$m4is_u076 =unserialize(base64_decode($_POST['parameters']));
 wp_set_current_user($m4is_u076['admin_id']);
 learndash_delete_user_data($m4is_u076['user_id']);
 wp_set_current_user($m4is_u076['user_id']);
 self::$m4is_f683->m4is_h185($m4is_u076['completion']);
 
}
}

