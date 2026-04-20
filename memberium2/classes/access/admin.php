<?php

/**
 * Copyright (c) 2017-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
 final 
class m4is_e37682 {
static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){

}
function m4is_a3466(){
$m4is_s90 ='gutenberg';
 $m4is_l6970 =$this->m4is_p43571($m4is_s90 );
 if(empty($m4is_l6970 )){
return;
 
} $m4is_x59 =$this->m4is_p6406();
 $m4is_j67631 =m4is_v679::PREFIX;
 $m4is_f4218 =m4is_v679::NS;
 $m4is_l91805 =['WPAL_BLOCKS_PREFIX' =>$m4is_j67631, 'WPAL_BLOCKS_SETTINGS_TITLE' =>$m4is_x59['settings_title'], 'WPAL_BLOCKS_KEYS_REMOVED_TEXT' =>$m4is_x59['keys_removed_text'], 'debug' =>defined('WPAL_BLOCKS_DEBUG')? constant('WPAL_BLOCKS_DEBUG' ): 0,  'notices' =>[]];
  $m4is_l9321 =$this->m4is_z2906();
 if(!empty($m4is_l9321 )){
$m4is_l91805['tags']=array_map(function($m4is_k72){
return ['value' =>$m4is_k72['id'], 'label' =>$m4is_k72['text']];
 
}, $m4is_l9321 );
 
} $m4is_a394 =['type' =>'string', 'default' =>'' ];
 $m4is_l91805['attributes']=["{$m4is_j67631
}_memberships" =>$m4is_a394 ];
 foreach ($m4is_l6970 as $m4is_o015 =>$m4is_k72){
$m4is_j0361 =isset($m4is_k72['type'])&&$m4is_k72['type']> '' ? $m4is_k72['type']: false;
 if(isset($m4is_k72['level'])){
$m4is_l6970[$m4is_o015]['label']=str_replace("&nbsp;", " ", $m4is_k72['label']);
 
}else{
$m4is_l91805['attributes'][$m4is_k72['name']]=$m4is_a394;
 
}
}$m4is_l91805['controls']=$m4is_l6970;
  $m4is_l91805['omitted_blocks']=apply_filters("{$m4is_f4218
}/{$m4is_s90
}/settings/omitted_blocks", ['core/freeform', 'divi/placeholder', 'fl-builder/layout' ]);
  $m4is_b691 ='wpal-blocks-gutenberg-editor';
 $m4is_n6062 =plugin_dir_url(MEMBERIUM_HOME);
 $m4is_l66875 ="{$m4is_n6062
}js/gutenberg-editor-access.js";
 $m4is_q068 ="{$m4is_n6062
}css/gutenberg-editor-access.css";
 $m4is_s861 =['react', 'react-dom', 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-data', 'wp-element', 'wp-hooks', 'wp-i18n', 'wp-polyfill' ];
 wp_enqueue_script($m4is_b691, $m4is_l66875, $m4is_s861, '1.2.0', false);
 wp_enqueue_style("{$m4is_b691
}-css", $m4is_q068, [], '1.2.0', 'all');
 wp_localize_script($m4is_b691, 'wpal_blocks_params', $m4is_l91805);
 
}
function m4is_d7803($m4is_e2365 =false, string $m4is_b691 ='' ): string {
$m4is_n6062 =plugin_dir_url(MEMBERIUM_HOME);
 $m4is_a6814 =m4is_r83::m4is_c26()->m4is_w45();
 wp_register_style("{$m4is_b691
}_s2css", "{$m4is_n6062
}css/wpal-select2.min.css", false, '4.0.3', 'all');
 wp_register_script("{$m4is_b691
}_s2js", "{$m4is_n6062
}js/wpal-select2.full.min.js", ['jquery'], '4.0.3', true);
 if($m4is_e2365 ){
wp_enqueue_style("{$m4is_b691
}_s2css");
 wp_enqueue_script("{$m4is_b691
}_s2js");
 
}return 'wpalSelect2';
 
} 
function m4is_p956(string $m4is_s90 ): array {
$m4is_y79 =['asset_id' =>0, 'invert_results' =>0 ];
 $m4is_f4218 =m4is_v679::NS;
 $m4is_y79 =apply_filters("{$m4is_f4218
}/{$m4is_s90
}/disabled/controls", $m4is_y79);
 return is_array($m4is_y79)? $m4is_y79 : [];
 
} 
function m4is_p43571(string $m4is_s90 ): array {
$m4is_f4218 =m4is_v679::NS;
 $m4is_j67631 =m4is_v679::PREFIX;
 $m4is_x59 =$this->m4is_p6406();
 $m4is_b3785 =0;
 $m4is_l6970 =[];
 $m4is_d81702 =0;
 $m4is_j17 =10;
 $m4is_y79 =$this->m4is_p956($m4is_s90);
 $m4is_d0238 =['type' =>'checkbox', 'default' =>'0', 'label_on' =>_x('On', "{$m4is_f4218
}/access/checkbox", $m4is_f4218), 'label_off' =>_x('Off', "{$m4is_f4218
}/access/checkbox", $m4is_f4218), 'return_value' =>'1' ];
  if(!array_key_exists('any_membership', $m4is_y79 )){
$m4is_l6970[$m4is_b3785]=$m4is_d0238;
 $m4is_l6970[$m4is_b3785]['name']="{$m4is_j67631
}_anymembership";
 $m4is_l6970[$m4is_b3785]['label']=sprintf(_x('Any %s', "{$m4is_f4218
}/access/membership/level", $m4is_f4218), $m4is_x59['membership_levels']);
 $m4is_l6970[$m4is_b3785]['priority']=$m4is_d81702 + $m4is_j17;
 $m4is_l6970[$m4is_b3785]['toggles']=['off' =>false, 'on' =>["{$m4is_j67631
}_membership_levels" =>true, "{$m4is_j67631
}_anonymous_only" =>false, "{$m4is_j67631
}_loggedin" =>false, ]];
 $m4is_b3785 ++;
 
} $m4is_m96240 =$this->m4is_i5610();
 $m4is_m96240 =isset($m4is_m96240 )&&is_array($m4is_m96240 )? $m4is_m96240 : false;
 $m4is_d75326 =!array_key_exists('memberships', $m4is_y79 );
 if($m4is_m96240 &&$m4is_d75326 ){
foreach ($m4is_m96240 as $m4is_d07693 =>$m4is_w64 ){
$m4is_l6970[$m4is_b3785]=$m4is_d0238;
 $m4is_l6970[$m4is_b3785]['name']="{$m4is_j67631
}_membership_levels-{$m4is_d07693
}";
 $m4is_l6970[$m4is_b3785]['label']=stripslashes($m4is_w64['name']). "&nbsp;({$m4is_w64['level']
})";
 $m4is_l6970[$m4is_b3785]['priority']=$m4is_d81702 + $m4is_j17;
 $m4is_l6970[$m4is_b3785]['level']=$m4is_d07693;
 $m4is_l6970[$m4is_b3785]['toggles']=['on' =>["{$m4is_j67631
}_anonymous_only" =>false, "{$m4is_j67631
}_loggedin" =>false ], 'off' =>["{$m4is_j67631
}_anymembership" =>false ]];
 $m4is_b3785 ++;
 
}
} if(!array_key_exists('logged_in_only', $m4is_y79 )){
$m4is_l6970[$m4is_b3785]=$m4is_d0238;
 $m4is_l6970[$m4is_b3785]['name']="{$m4is_j67631
}_loggedin";
 $m4is_l6970[$m4is_b3785]['label']=$m4is_x59['any_logged_in'];
 $m4is_l6970[$m4is_b3785]['priority']=$m4is_d81702 + $m4is_j17;
 $m4is_l6970[$m4is_b3785]['toggles']=['on' =>["{$m4is_j67631
}_membership_levels" =>false, "{$m4is_j67631
}_anymembership" =>false, "{$m4is_j67631
}_anonymous_only" =>false ], 'off' =>false, ];
 $m4is_b3785 ++;
 
} if(!array_key_exists('logged_out_only', $m4is_y79 )){
$m4is_l6970[$m4is_b3785]=$m4is_d0238;
 $m4is_l6970[$m4is_b3785]['name']="{$m4is_j67631
}_anonymous_only";
 $m4is_l6970[$m4is_b3785]['label']=$m4is_x59['logged_out_only'];
 $m4is_l6970[$m4is_b3785]['priority']=$m4is_d81702 + $m4is_j17;
 $m4is_l6970[$m4is_b3785]['toggles']=['off' =>false, 'on' =>["{$m4is_j67631
}_membership_levels" =>false, "{$m4is_j67631
}_anymembership" =>false, "{$m4is_j67631
}_loggedin" =>false ]];
 $m4is_b3785 ++;
 
} if(!array_key_exists('invert_results', $m4is_y79)){
$m4is_l6970[$m4is_b3785]=$m4is_d0238;
 $m4is_l6970[$m4is_b3785]['name']="{$m4is_j67631
}_invert_results";
 $m4is_l6970[$m4is_b3785]['label']=$m4is_x59['invert'];
 $m4is_l6970[$m4is_b3785]['priority']=$m4is_d81702 + $m4is_j17;
 $m4is_l6970[$m4is_b3785]['description']=$m4is_x59['invert_desc'];
 $m4is_b3785 ++;
 
} $m4is_f652 =[];
 if(!array_key_exists('tags1', $m4is_y79 )){
$m4is_f652["{$m4is_j67631
}_access_tags"]=$m4is_x59['require_key'];
 
}if(!array_key_exists('tags2', $m4is_y79 )){
$m4is_f652["{$m4is_j67631
}_access_tags2"]=$m4is_x59['and_require_key'];
 
}if(!empty($m4is_f652)){
foreach ($m4is_f652 as $name =>$label){
$m4is_l6970[$m4is_b3785]=['name' =>$name, 'type' =>'SELECT2', 'label' =>$label, 'priority' =>$m4is_d81702 + $m4is_j17 ];
 $m4is_b3785 ++;
 
}
} if(!array_key_exists('contact_ids', $m4is_y79)){
$m4is_l6970[$m4is_b3785]=['name' =>"{$m4is_j67631
}_contact_ids", 'type' =>'textarea', 'label' =>$m4is_x59['user_ids'], 'priority' =>$m4is_d81702 + $m4is_j17, 'rows' =>1, 'description' =>$m4is_x59['user_ids_desc'], 'sanitize' =>'number-csv' ];
 $m4is_b3785 ++;
 
} if(!array_key_exists('eval', $m4is_y79)){
$m4is_l6970[$m4is_b3785]=['name' =>"{$m4is_j67631
}_eval", 'type' =>'textarea', 'label' =>$m4is_x59['eval'], 'priority' =>$m4is_d81702 + $m4is_j17, 'rows' =>1, 'description' =>$m4is_x59['eval_desc']];
 $m4is_b3785 ++;
 
} if(!array_key_exists('asset_id', $m4is_y79)){
$m4is_l6970[$m4is_b3785]=['name' =>"{$m4is_j67631
}_asset_id", 'type' =>'text', 'label' =>$m4is_x59['asset_id'], 'priority' =>$m4is_d81702 + $m4is_j17, 'description' =>$m4is_x59['asset_id_desc'], 'sanitize' =>'slugify' ];
 
}return apply_filters("{$m4is_f4218
}/{$m4is_s90
}/control/config", $m4is_l6970 );
 
} 
function m4is_i5610(){
$m4is_m96240 =m4is_r83::m4is_c26()->m4is_j498('memberships');
 return is_array($m4is_m96240)? $m4is_m96240 : [];
 
} 
function m4is_p6406($m4is_l9671 =false, $m4is_s90 ='' ){
$m4is_f4218 =m4is_v679::NS;
 $m4is_s90 =!empty($m4is_s90 )? "{$m4is_f4218
}/access" : '';
  $m4is_x59 =['all_visitors' =>'All Visitors', 'and_key_desc' =>'The contact must have both the 1st Tag ID(s) AND this Tag ID(s) in order to view this item and these settings are only available for logged in users.', 'and_key_name' =>'AND Tag ID(s)', 'and_require_key' =>'AND Require Tag ID(s)', 'any_logged_in' =>'Any Logged In User', 'any_membership' =>'Any Membership Level', 'asset_id_desc' =>'Enter an admin ID to be used for filters. Non-leading _ and alpha / numerical characters only.', 'asset_id' =>'Asset ID', 'eval_desc' =>'Enter a boolean expression which evaluates to true to show or false to hide this element.', 'eval' =>'PHP Boolean Expression', 'hide' =>'Hide Completely', 'key_name' =>'Tag ID(s)', 'keys_removed_text' =>'Notice : The following Tag ID(s) have been removed', 'logged_in_msg' =>'settings are only available for logged in users.', 'logged_in_only' =>'Logged In Users Only', 'logged_out_only' =>'Logged Out Only', 'logged_out_user' =>'Logged Out Visitors Only', 'membership_levels' =>'Membership Levels', 'memberships' =>'Memberships', 'prohibited_action' =>'When Prohibited :', 'redirect_url' =>'Redirect URL', 'redirect' =>'Redirect', 'require_key' =>'Require Tag ID(s)', 'settings_title' =>'Memberium', 'user_ids_desc' =>'Comma Seperated values. Example : 123,456,789', 'user_ids' =>'Require User IDs', 'user_status' =>'User Status', ];
 if(is_string($m4is_l9671 )){
return !empty($m4is_x59[$m4is_l9671])? _x($m4is_x59[$m4is_l9671], $m4is_s90, $m4is_f4218 ): '';
 
}else{
return $m4is_x59;
 
}
}   
function m4is_l6016(string $m4is_j0361 ): array {
$m4is_x59 =$this->m4is_p6406();
 $m4is_l6970 =[['name' =>'status', 'label' =>$m4is_x59['user_status'], 'type' =>'select2', 'data' =>'status', 'disable-search' =>true, 'change' =>"statusToggle", 'priority' =>100 ], ['name' =>'memberships', 'label' =>$m4is_x59['memberships'], 'info' =>"{$m4is_x59['memberships']
} {$m4is_x59['logged_in_msg']
}", 'type' =>'select2', 'data' =>'levels', 'change' =>"contactToggle", 'multiple' =>1, 'priority' =>200 ], ['name' =>'tags1', 'label' =>$m4is_x59['key_name'], 'info' =>"{$m4is_x59['key_name']
} {$m4is_x59['logged_in_msg']
}", 'type' =>'select2', 'data' =>'keys', 'change' =>"contactToggle", 'multiple' =>1, 'priority' =>300 ], ['name' =>'tags2', 'label' =>$m4is_x59['and_key_name'], 'info' =>$m4is_x59['and_key_desc'], 'type' =>'select2', 'data' =>'keys', 'change' =>"contactToggle", 'multiple' =>1, 'priority' =>400 ], ['name' =>'eval', 'label' =>$m4is_x59['eval'], 'type' =>'textarea', 'desc' =>$m4is_x59['eval_desc'], 'priority' =>500 ], ];
 if($m4is_j0361 === 'taxonomy' ){
$m4is_l6970[]=['name' =>'prohibited_action', 'label' =>$m4is_x59['prohibited_action'], 'type' =>'select2', 'data' =>'prohibited_actions', 'change' =>'prohibitedActionToggle', 'disable-search' =>true, 'priority' =>600 ];
 $m4is_l6970[]=['name' =>'redirect_url', 'label' =>$m4is_x59['redirect_url'], 'type' =>'text', 'priority' =>700 ];
 
}return $m4is_l6970;
 
}
function m4is_i60238(){
return apply_filters('memberium/access/logged_in_only/fields', ['memberships', 'tags1', 'tags2']);
 
}
function m4is_c0667(array $m4is_l91805 ): array {
$m4is_a234 =apply_filters('memberium/access/logged_in_settings', ['any_membership' =>1, 'memberships' =>1, 'tags1' =>1, 'tags2' =>1 ]);
 if(is_array($m4is_a234 )&&!empty($m4is_a234 )){
foreach ($m4is_a234 as $m4is_l9671 =>$m4is_v586 ){
if(array_key_exists($m4is_l9671, $m4is_l91805 )){
unset($m4is_l91805[$m4is_l9671]);
 
}
}
}return $m4is_l91805;
 
}
function m4is_y91($m4is_d579 ){
$m4is_m96240 =empty($m4is_d579)? []: array_filter(explode(',', $m4is_d579));
 return in_array('any_membership', $m4is_m96240)? 'any_membership' : $m4is_d579;
 
}
function m4is_a91(string $type ){
$m4is_p95312 =plugin_dir_url(MEMBERIUM_HOME);
 $m4is_l66875 ="{$m4is_p95312
}js/core-wp-asset-access.js";
 $m4is_a6814 =m4is_r83::m4is_c26()->m4is_w45();
 $m4is_b691 ="memb-core-wp-asset-access-js";
  $this->m4is_d7803(true );
 wp_register_style('memberium_admin_css', $m4is_p95312 . 'css/admin.css', false, $m4is_a6814, 'all');
 wp_enqueue_style('memberium_admin_css');
  $m4is_l91805 =['type' =>$type, 'select2Data' =>['levels' =>$this->m4is_s365(), 'keys' =>$this->m4is_z2906(), 'status' =>$this->m4is_j2396(), 'prohibited_actions' =>$this->m4is_v6624()], 'I18n' =>['ids_removed' =>$this->m4is_p6406('keys_removed_text')], 'loggedInOnlyKeys' =>$this->m4is_i60238()];
  wp_enqueue_script($m4is_b691, $m4is_l66875, ['jquery'], $m4is_a6814, true);
 wp_localize_script($m4is_b691, "membCoreAssetsAccessData", $m4is_l91805);
 unset($m4is_p95312, $m4is_l66875, $m4is_a6814, $m4is_b691, $m4is_l91805);
 
}   
function m4is_s365(): array {
static $m4is_j1907 =[];
 if(empty($m4is_j1907 )){
$m4is_m96240 =$this->m4is_i5610();
 $m4is_j1907 =[['id' =>'any_membership', 'text' =>$this->m4is_p6406('any_membership' )]];
 if(!empty($m4is_m96240 )){
foreach ($m4is_m96240 as $m4is_d07693 =>$m4is_c5468 ){
$m4is_j1907[]=['id' =>$m4is_d07693, 'text' =>stripslashes($m4is_c5468['name']). " ({$m4is_c5468['level']
})" ];
 
}
}
}return $m4is_j1907;
 
}
function m4is_z2906(): array {
static $m4is_u450 =[];
 if(empty($m4is_u450 )){
$m4is_j3627 =m4is_k865::m4is_b94([], '' );
 if(!empty($m4is_j3627 )){
$m4is_u450 =array_map(function($m4is_o015, $m4is_k72 ){
return ['id' =>$m4is_o015, 'text' =>$m4is_k72 ];
 
}, array_keys($m4is_j3627 ), $m4is_j3627 );
 
}
}return $m4is_u450;
 
}
function m4is_j2396(): array {
$m4is_x59 =$this->m4is_p6406();
 return [['id' =>'', 'text' =>$m4is_x59['all_visitors']], ['id' =>'1', 'text' =>$m4is_x59['logged_in_only']], ['id' =>'2', 'text' =>$m4is_x59['logged_out_user']]];
 
}
function m4is_v6624(): array {
$m4is_x59 =$this->m4is_p6406();
 return [['id' =>'', 'text' =>$m4is_x59['hide']], ['id' =>'1', 'text' =>$m4is_x59['redirect']]];
 
}
}

