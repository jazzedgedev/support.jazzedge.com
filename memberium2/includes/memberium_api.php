<?php

/**
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
     
function memb_getContactId(): int {
return (int) m4is_r83::m4is_c26()->m4is_z56();
 
} 
function memb_getContactIdByUserId(int $user_id ): int {
return (int) m4is_p40::m4is_w58096($user_id );
 
} 
function memb_getUserIdByContactId(int $contact_id ): int {
return m4is_p40::m4is_i6158($contact_id);
 
}     
function memb_hasAnyTags($tags, $contact_id =0 ): bool {
$m4is_r1546 =m4is_r83::m4is_c26();
 $m4is_f087 =$contact_id ? m4is_p40::m4is_i6158($contact_id ): $m4is_r1546->m4is_x66();
 if(empty($tags )){
return true;
 
}if(!$m4is_f087 ){
return false;
 
}if(user_can($m4is_f087, 'manage_options' )){
return true;
 
}$m4is_q06138 =m4is_q82::m4is_d6758($m4is_f087, 'memb_user', 'tags', '' );
 if(!empty($m4is_q06138)){
return $m4is_r1546->m4is_l5918($tags, $m4is_q06138 );
 
}return false;
 
} 
function memb_hasAllTags($tags, $contact_id =false): bool {
return m4is_r83::m4is_c26()->m4is_x13466($tags, $contact_id );
 
}    
function memb_hasAnyMembership(): bool {
return m4is_f58::m4is_c26()->m4is_e4731();
 
} 
function memb_hasMembership(string $level_name ): bool {
return m4is_f58::m4is_c26()->m4is_u70($level_name );
 
} 
function memb_hasMembershipLevel(int $level): bool {
return m4is_f58::m4is_c26()->m4is_z4619($level );
 
}    
function memb_overrideProhibitedAction(string $action ='default' ){
return m4is_f58::m4is_c26()->m4is_f4206($action );
 
} 
function memb_isPostProtected($post ): bool {
return m4is_f58::m4is_c26()->m4is_t749($post );
 
} 
function memb_hasPostAccess(int $post_id, int $user_id =0 ): bool {
return m4is_f58::m4is_c26()->m4is_x72168($post_id, $user_id );
 
} 
function memb_hasTermAccess(int $term_id, $taxonomy): bool {
return m4is_w5132::m4is_c26()->m4is_w09($term_id, $taxonomy );
 
}    
function memb_changeContactEmail(string $email, int $user_id, bool $force_username =null ): bool {
return (bool) m4is_r83::m4is_c26()->m4is_x6461($user_id, $email, 0, $force_username );
 
} 
function memb_changeContactPassword(string $new_password, int $contact_id =0 ): bool {
return (bool) m4is_r83::m4is_c26()->m4is_a58($new_password, $contact_id );
 
}    
function memb_setContactField(string $key, $value, int $contact_id =0): bool {
if(empty($key )){
return false;
 
}return (bool) m4is_r83::m4is_c26()->m4is_s56($key, $value, $contact_id );
 
} 
function memb_getContactField(string $fieldname, bool $sanitize =false ){
return (string) m4is_f58::m4is_c26()->m4is_v921($fieldname, $sanitize );
 
} 
function memb_loadContactById(int $contact_id ): array {
return (array)m4is_p40::m4is_p67($contact_id );
 
} 
function memb_syncContact(int $contact_id, bool $cascade =false ): bool {
return (bool) m4is_r83::m4is_c26()->m4is_x4831($contact_id, $cascade );
 
} 
function memb_setTags($tags, int $contact_id =0, bool $force =false): bool {
return (bool) m4is_r83::m4is_c26()->m4is_k98($tags, $contact_id, $force );
 
} 
function memb_getSession(int $user_id ): array {
return m4is_q82::m4is_d59($user_id );
 
}   
function memb_getAffiliateField(string $fieldname, bool $sanitize =false ): string {
return m4is_f58::m4is_c26()->m4is_o0764($fieldname, $sanitize );
 
}    
function memb_runActionset($actionset_ids ='', int $contact_id =0 ): bool {
return (bool) m4is_r83::m4is_c26()->m4is_u71903($actionset_ids, $contact_id );
 
}    
function memb_getReceipt(array $args =[]): array {
return m4is_r83::m4is_c26()->m4is_r86521($args );
 
}    
function memb_getUserFields(string $field_name, int $user_id =0){
return m4is_r83::m4is_c26()->m4is_d842($field_name, $user_id);
 
} 
function memb_setUserField(string $field_name, $value, int $user_id =0){
return m4is_r83::m4is_c26()->m4is_y165($field_name, $value, $user_id);
 
}   
function memb_getMembershipMap(): array {
return m4is_r83::m4is_c26()->m4is_j498('memberships');
 
}
function memb_getTagMap(bool $cache_bust =false, bool $negatives =false ): array {
return m4is_k865::m4is_z2906($cache_bust , $negatives );
 
}
function memb_getContactFieldsMap(): array {
return m4is_c69807::m4is_f5248('Contact', false );
  
}
function memb_createTag($tag_name, $category_id =0, $description ='Created by Memberium PHP API' ){
$tag_name =trim($tag_name );
 $category_id =(int) $category_id;
 $description =trim($description );
 return m4is_k865::m4is_f348($tag_name, $category_id, $description );
 
}
function memb_savePostPermissions(int $post_id, $permissions, $value =null ){
return m4is_w831::m4is_f0691($post_id, $permissions, $value );
 
}    
function memb_get_keap_api(){
if(!is_object($GLOBALS['i2sdk'])){
return false;
 
}return $GLOBALS['i2sdk']->isdk;
  
}
function memb_getAppName(): string {
return m4is_r83::m4is_c26()->m4is_i76('appname' );
 
}   
function memb_loadPostPermissions(int $post_id ){
return m4is_w831::m4is_e513($post_id );
 
}
function memb_getPostSettings(int $post_id ){
return m4is_w831::m4is_e513($post_id);
 
}
function memb_get_license_status(): string {
return m4is_s52::m4is_w74();
 
}
function memb_has_license_tags($tags ): bool {
$tags =is_array($tags)? $tags : explode(',', $tags);
 return m4is_s52::m4is_b12067($tags);
 
}
function memb_is_license_trial(): bool {
return m4is_s52::m4is_e24();
 
}    
function memb_getLoggedIn(): bool {
error_log('Memberium: [error]:  ' . __FUNCTION__ . '() is deprecated and will be removed in a future release.  Use is_user_logged_in() instead.' );
 $m4is_y9268 =debug_backtrace();
 if(!empty($m4is_y9268 )){
error_log('Location: ' . print_r($m4is_y9268, true ));
 
}return is_user_logged_in();
 
} 
function memb_is_loggedin(): bool {
error_log('Memberium: [warning]:  ' . __FUNCTION__ . '() is deprecated and will be removed in a future release.  Use is_user_logged_in() instead.' );
 $m4is_y9268 =debug_backtrace();
 if(!empty($m4is_y9268 )){
error_log('Location: ' . print_r($m4is_y9268, true ));
 
}return is_user_logged_in();
 
} 
function memb_doShortcode(string $content, bool $do_regular_shortcodes =true ): string {
error_log('Memberium: [warning]:  ' . __FUNCTION__ . '() is deprecated and will be removed in a future release.  Use do_shortcode() instead.' );
 $m4is_y9268 =debug_backtrace();
 if(!empty($m4is_y9268 )){
error_log('Location: ' . print_r($m4is_y9268, true ));
 
}return do_shortcode($content );
 
}
function doMemberiumLogin(string $username, string $password ='', bool $idempotent =false ){
error_log('Memberium: [warning]:  ' . __FUNCTION__ . '() is deprecated.' );
 $m4is_y9268 =debug_backtrace();
 if(!empty($m4is_y9268 )){
error_log('Location: ' . print_r($m4is_y9268, true ));
 
}return m4is_l5841::m4is_v61($username, $password, $idempotent);
 
}
function memb_setSSOMode(bool $mode =true ){
error_log('Memberium: [warning]:  ' . __FUNCTION__ . '() is deprecated.' );
 $m4is_y9268 =debug_backtrace();
 if(!empty($m4is_y9268 )){
error_log('Location: ' . print_r($m4is_y9268, true ));
 
}return m4is_r83::m4is_c26()->m4is_h61($mode);
 
}

