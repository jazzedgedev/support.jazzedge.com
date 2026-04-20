<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 
class m4is_n63 {
private $m4is_r1546;
 private $m4is_v845;
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self();
 
} private 
function __construct(){
$this->m4is_i702();
   add_action('init', [$this, 'm4is_t1732'], 9 );
 
}private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_v845 =m4is_u7102::m4is_c26();
 
}      public 
function m4is_t1732(){
if(!$this->m4is_r1546->m4is_v461()){
return;
 
}add_action('memberium_admin_menu_addons', [$this, 'm4is_p2651']);
 add_action('memberium/admin/user_editor', [$this, 'm4is_l76'], 2010 );
    m4is_u81::m4is_u4372();
 
} public 
function m4is_p2651(string $m4is_x160 ){
if($m4is_x160 ){
      add_submenu_page($m4is_x160, 'Group Accounts', 'Group Accounts', 'manage_options', 'memberium-group-accounts', [$this, 'm4is_g63']);
 
}
} public 
function m4is_l76($m4is_l17096 ): void {
 if(!$this->m4is_r1546->m4is_v461()){
return;
 
}$m4is_k824 =m4is_q82::m4is_d59($m4is_l17096->ID );
 $m4is_h21895 =$m4is_k824['keap']['contact']['id']??= 0;
 m4is_u81::m4is_d38602($m4is_l17096->ID );
 if(!$m4is_h21895 ){
return;
 
}$m4is_q6302 =m4is_u7102::m4is_c26();
 $m4is_v61358 =strtolower($m4is_q6302->m4is_s54627());
 $m4is_e0213 =[['label' =>'Group Account Code:', 'value' =>empty($m4is_k824['keap']['contact'][$m4is_v61358])? '<strong style="color:red;">None</strong>' : $m4is_k824['keap']['contact'][$m4is_v61358]]];
 if($m4is_q6302->m4is_r195($m4is_l17096->ID )){
$m4is_q95421 =$m4is_q6302->m4is_u073($m4is_l17096->ID );
 $m4is_b24783 =[];
 foreach($m4is_q95421 as $m4is_m617 ){
$m4is_g6275 =get_user_by('ID', $m4is_m617 );
 if(!is_a($m4is_g6275, 'WP_User' )){
$m4is_q6302->m4is_y7324($m4is_m617, $m4is_l17096->ID );
 continue;
 
}$m4is_b24783[]=sprintf('<a href="%s" target="child_user">%s (%s)</a>', get_edit_user_link($m4is_m617 ), $m4is_g6275->display_name, $m4is_g6275->user_email );
 
}$m4is_e0213 =array_merge($m4is_e0213, [['label' =>'Maximum Children:', 'value' =>$m4is_k824['umbrella']['max_children']], ['label' =>'Children Found:', 'value' =>count($m4is_b24783 ),  ], ['label' =>'Child Accounts:', 'value' =>implode(', ', $m4is_b24783 ), ], ]);
 
}else{
$m4is_i341 =$m4is_q6302->m4is_b201($m4is_l17096->ID );
 if($m4is_i341 ){
$m4is_s092 =get_user_by('ID', $m4is_i341 );
 $m4is_e0213 =array_merge($m4is_e0213, [['label' =>'Parent User:', 'value' =>sprintf('<a href="%s">%s (%s)</a>', get_edit_user_link($m4is_i341 ), $m4is_s092->display_name, $m4is_s092->user_email )], ]);
 
}
} echo '<table class="form-table">';
 echo '<tr>';
 echo '<th valign="top"><label for="infusionsoft_umbrella">Group Accounts</label></th>';
 foreach($m4is_e0213 as $m4is_d87521 ){
echo '<tr>';
 echo '<td>', $m4is_d87521['label'], '</td><td>', $m4is_d87521['value'], '</td>';
 echo '</tr>';
 
}echo '</table>';
 
} public 
function m4is_g63(): void {
m4is_s6729::m4is_c26()->m4is_a4215();
 new m4is_q913();
 require_once __DIR__ . '/screen.php';
 
} 
}

