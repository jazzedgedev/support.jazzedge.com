<?php
 class_exists('m4is_r83')||die();
 if(!current_user_can('manage_options')){
wp_die(__('You do not have sufficient permissions to access this page.'));
 
} if(!empty($m4is_e1964)){
echo "<style type=\"text/css\">{$m4is_e1964
}</style>";
 
}echo '<div class="wrap marketplace-wrap memberium">';
  if(isset($m4is_f619)&&(!empty($m4is_f619['title'])||!empty($m4is_f619['desc']))){
echo '<div class="marketplace-description marketplace-description-header">';
 echo (!empty($m4is_f619['title']))? '<h3>'.$m4is_f619['title'].'</h3>' : '';
 echo (!empty($m4is_f619['desc']))? '<p>'.$m4is_f619['desc'].'</p>' : '';
 echo '</div>';
 
} echo '<div class="wrap">';
 $m4is_n41266 =false;
  if($m4is_q49051){
echo '<h4 class="nav-tab-wrapper">';
 foreach($m4is_p47 as $m4is_q1852 ){
$m4is_j09 =$m4is_q1852['slug'];
 if($m4is_j09 > '' ){
if($m4is_j09 === $m4is_p10384 ){
$m4is_n41266 =$m4is_q1852;
 echo '<span class="nav-tab nav-tab-active">';
 echo $m4is_q1852['title'];
 echo '</span>';
 
}else{
echo '<a class="nav-tab" href="'.$m4is_f5196.'&amp;tab='.$m4is_j09.'">';
 echo $m4is_q1852['title'];
 echo '</a>';
 
}
}
} echo '</h4>';
 
} if($m4is_n41266 ){
echo '<br class="clear">';
 if(!empty($m4is_n41266['desc'])){
echo '<p class="memberium-marketplace-desc">'.$m4is_n41266['desc'].'</p>';
 
} echo '<div class="wp-list-table widefat">';
 echo '<div id="the-list">';
  $m4is_n4267 ='<div class="memberium-marketplace-card %s %s"%s>';
 $m4is_n4267 .='<figure%s><img src="%s"/></figure>';
 $m4is_n4267 .='<div class="memberium-marketplace-content">';
 $m4is_n4267 .= '<h3> %s </h3>';
 $m4is_n4267 .='<p> %s </p>';
 $m4is_n4267 .='<div class="memberium-marketplace-buttons">';
 $m4is_n4267 .='<a class="button button-primary" href="%s" target="_blank">';
 $m4is_n4267 .= __('Learn More').'</a>';
 $m4is_n4267 .='</div>';
 $m4is_n4267 .='</div>';
 $m4is_n4267 .= '</div>';
  foreach ($m4is_n41266['listings']as $m4is_l9671 =>$m4is_e980){
$m4is_m675 =($m4is_e980['style']> '' )? " style=\"{$m4is_e980['style']
}\"" : "";
  $m4is_h64 ='';
  if($m4is_e980['border']> '' ){
$m4is_r31706 ="border-color:{$m4is_e980['border']
};
";
 if($m4is_e980['border']=== 'transparent' ){
$m4is_r31706 .= ' border:none;';
 
}$m4is_h64 =($m4is_r31706 > '' )? " style=\"{$m4is_r31706
}\"" : "";
 
}echo sprintf($m4is_n4267, sanitize_title_with_dashes($m4is_e980['title']), $m4is_e980['className'], $m4is_m675, $m4is_h64, $m4is_e980['logo'], $m4is_e980['title'], $m4is_e980['desc'], $m4is_e980['link']);
 
} echo '</div>';
  echo '</div>';
 
} if($m4is_g876 &&((!empty($m4is_g876['title']))||(!empty($m4is_g876['desc']))||!empty($m4is_g876['link']))){
echo '<div class="marketplace-description marketplace-description-footer">';
 echo (!empty($m4is_g876['title']))? '<h3>'.$m4is_g876['title'].'</h3>' :'';
 echo (!empty($m4is_g876['desc']))? '<p>'.$m4is_g876['desc'].'</p>' :'';
 if(!empty($m4is_g876['link'])){
if(!empty($m4is_g876['link']['title'])&&!empty($m4is_g876['link']['url'])){
echo sprintf('<a href="%s">%s</a>', $m4is_g876['link']['url'], $m4is_g876['link']['title']);
 
}
}echo '</div>';
 
} echo '</div>';
  echo '</div>';

