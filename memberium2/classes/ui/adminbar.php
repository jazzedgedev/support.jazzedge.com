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
class m4is_n2043 {
private 
function __construct(){

}static 
function m4is_l01263($m4is_n71685){
if(m4is_r83::m4is_c26()->m4is_v461()){
$m4is_j67631 ='wpal-memberium-';
 $m4is_s092 ='new-content';
 $m4is_w89066 =[['group' =>'', 'href' =>get_admin_url(null, 'admin.php?page=memberium-memberships&action=add' ), 'id' =>$m4is_j67631 . 'membership', 'meta' =>[], 'parent' =>$m4is_s092, 'title' =>'Membership', ], ['group' =>'', 'href' =>get_admin_url(null, 'post-new.php?post_type=partials' ), 'id' =>$m4is_j67631 . 'partial', 'meta' =>[], 'parent' =>$m4is_s092, 'title' =>'Partial', ], ['group' =>'', 'href' =>get_admin_url(null, 'post-new.php?post_type=memb_shortcodeblocks' ), 'id' =>$m4is_j67631 . 'customshortcode', 'meta' =>[], 'parent' =>$m4is_s092, 'title' =>'Custom Shortcode', ], ];
 foreach ($m4is_w89066 as $m4is_c4069 ){
$m4is_n71685->add_node($m4is_c4069 );
 
}$m4is_s092 ='wpal-memberium';
 $m4is_w89066 =[['id' =>$m4is_s092, 'title' =>'Memberium', 'parent' =>'', 'href' =>'', 'group' =>'', 'meta' =>[], ], ['id' =>$m4is_j67631 . 'sync-tags', 'title' =>'Sync Tags', 'parent' =>$m4is_s092, 'href' =>get_admin_url(null, 'admin.php?page=memberium-support&tab=dashboard&action=sync-tags' ), 'group' =>'', 'meta' =>[], ], ['id' =>$m4is_j67631 . 'sync-fields', 'title' =>'Sync Custom Fields', 'parent' =>$m4is_s092, 'href' =>get_admin_url(null, 'admin.php?page=memberium-support&tab=dashboard&action=sync-fields' ), 'group' =>'', 'meta' =>[], ], ['id' =>$m4is_j67631 . 'sync-actionsets', 'title' =>'Sync Actionsets', 'parent' =>$m4is_s092, 'href' =>get_admin_url(null, 'admin.php?page=memberium-support&tab=dashboard&action=sync-actionsets' ), 'group' =>'', 'meta' =>[], ], ['id' =>$m4is_j67631 . 'update-license', 'title' =>'Update License', 'parent' =>$m4is_s092, 'href' =>get_admin_url(null, 'admin.php?page=memberium-support&tab=dashboard&action=update-license' ), 'group' =>'', 'meta' =>[], ], ['id' =>$m4is_j67631 . 'dashboard', 'title' =>'Dashboard', 'parent' =>$m4is_s092, 'href' =>get_admin_url(null, 'admin.php?page=memberium-support&tab=dashboard'), 'group' =>'', 'meta' =>[], ], ['id' =>$m4is_j67631 . 'support', 'title' =>'Support', 'parent' =>$m4is_s092, 'href' =>get_admin_url(null, 'admin.php?page=memberium-support&tab=support' ), 'group' =>'', 'meta' =>[], ], ];
 foreach($m4is_w89066 as $m4is_c4069){
$m4is_n71685->add_node($m4is_c4069);
 
}
}
}static 
function m4is_o0352($m4is_n71685 ){
if(defined('QM_VERSION' )){
return;
 
}if(!current_user_can('manage_options' )){
return;
 
}$m4is_r6684 =m4is_r83::m4is_c26()->m4is_e07(false );
 $m4is_v5669 =wp_using_ext_object_cache();
 $m4is_m87062 =!empty($_SERVER['HTTP_CF_RAY']);
 $m4is_r8492 =!empty($_SERVER['HTTP_X_SUCURI_CLIENTIP']);
 $m4is_i4086 =$m4is_m87062 ||$m4is_r8492;
 $m4is_z52608 =is_admin()? WP_MAX_MEMORY_LIMIT : ini_get('memory_limit' );
 $m4is_f6067 =count(get_option('active_plugins', []));
 $m4is_s092 ='wpal-memberium-performance';
 $m4is_w89066 =[['id' =>$m4is_s092, 'title' =>'Performance', 'parent' =>'', 'href' =>'', 'group' =>'', 'meta' =>[], ], ['id' =>'wpal-memberium-time', 'title' =>'Time: <span style="color:white;" id="wpal-memberium-time">' . number_format_i18n($m4is_r6684['time'], 3 ). 's</span>', 'parent' =>$m4is_s092, 'href' =>'#', 'group' =>'', 'meta' =>[], ], ['id' =>'wpal-memberium-wpmemory', 'title' =>'Total Memory: <span style="color:white;">' . size_format(intval($m4is_z52608 )* 1024 * 1024). '</span>', 'parent' =>$m4is_s092, 'href' =>'#', 'group' =>'', 'meta' =>[], ], ['id' =>'wpal-memberium-memory', 'title' =>'Used Memory: <span style="color:white;" id="wpal-memberium-memory">' . size_format($m4is_r6684['memory'], 2 ). '</span>', 'parent' =>$m4is_s092, 'href' =>'#', 'group' =>'', 'meta' =>[], ], ['id' =>'wpal-memberium-plugins', 'title' =>'Plugins: <span style="color:white;">' . number_format_i18n($m4is_f6067 ). '</span>', 'parent' =>$m4is_s092, 'href' =>'#', 'group' =>'', 'meta' =>[], ], ['id' =>'wpal-memberium-queries', 'title' =>'SQL Queries: <span style="color:white;" id="wpal-memberium-db-queries">' . get_num_queries(). '</span>', 'parent' =>$m4is_s092, 'href' =>'#', 'group' =>'', 'meta' =>[], ], ['id' =>'wpal-memberium-http', 'title' =>'HTTP Calls: <span style="color:white;" id="wpal-memberium-http-calls">' . (int) $m4is_r6684['http_calls']. '</span>', 'parent' =>$m4is_s092, 'href' =>'#', 'group' =>'', 'meta' =>[], ], ['id' =>'wpal-memberium-crm-api', 'title' =>'API Calls: <span style="color:white;" id="wpal-memberium-crm-api">' . (int) $m4is_r6684['api_calls']. '</span>', 'parent' =>$m4is_s092, 'href' =>'#', 'group' =>'', 'meta' =>[], ], ];
 if($m4is_v5669 ){
global $wp_object_cache;
 $m4is_k50 =property_exists($wp_object_cache, 'cache_hits' )? $wp_object_cache->cache_hits : 0;
 $m4is_d38610 =property_exists($wp_object_cache, 'cache_misses' )? $wp_object_cache->cache_misses : 0;
 if(true ){
$m4is_w89066[]=['id' =>'wpal-memberium-cache-divider', 'title' =>'<hr />', 'parent' =>'wpal-memberium-performance', 'href' =>'#', 'group' =>'', 'meta' =>[], ];
 $m4is_w89066[]=['id' =>'wpal-memberium-caching-label', 'title' =>'Object Caching', 'parent' =>'wpal-memberium-performance', 'href' =>'#', 'group' =>'', 'meta' =>[], ];
 $m4is_w89066[]=['id' =>'wpal-memberium-cache-hits', 'title' =>'Cache Hits: <span style="color:white;" id="wpal-memberium-cache-hits">' . (int) $m4is_k50 . '</span>', 'parent' =>'wpal-memberium-performance', 'href' =>'#', 'group' =>'', 'meta' =>[], ];
 $m4is_w89066[]=['id' =>'wpal-memberium-cache-misses', 'title' =>'Cache Misses: <span style="color:white;" id="wpal-memberium-cache-misses">' . (int) $m4is_d38610 . '</span>', 'parent' =>'wpal-memberium-performance', 'href' =>'#', 'group' =>'', 'meta' =>[], ];
 
}
}else{
$m4is_w89066[]=['id' =>'wpal-memberium-caching-label', 'title' =>'Object Cache:  <span style="color:white;">Not Found</span>', 'parent' =>'wpal-memberium-performance', 'href' =>'#', 'group' =>'', 'meta' =>[], ];
 
}if($m4is_i4086 ){
$m4is_s092 ='wpal-memberium-performance';
 $m4is_w89066[]=['id' =>'wpal-memberium-cache-divider', 'title' =>'<hr />', 'parent' =>$m4is_s092, 'href' =>'#', 'group' =>'', 'meta' =>[], ];
 $m4is_w89066[]=['id' =>'wpal-memberium-cloudflare', 'title' =>'Cloudflare: <span style="color:white;">' . ($m4is_m87062 ? 'Yes' : 'No' ). '</span>', 'parent' =>$m4is_s092, 'href' =>'#', 'group' =>'', 'meta' =>[], ];
 $m4is_w89066[]=['id' =>'wpal-memberium-sucuri', 'title' =>'Sucuri: <span style="color:white;">' . ($m4is_r8492 ? 'Yes' : 'No' ). '</span>', 'parent' =>$m4is_s092, 'href' =>'#', 'group' =>'', 'meta' =>[], ];
 
}foreach ($m4is_w89066 as $m4is_c4069 ){
$m4is_n71685->add_node($m4is_c4069 );
 
}
}
}

