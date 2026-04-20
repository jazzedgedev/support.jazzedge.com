<?php
 class_exists('m4is_r83')||die();
 final 
class m4is_u6435 {
private $app_id ='';
 private 
function __construct(){

}static private 
function m4is_k657($m4is_v586, $m4is_r37596, $m4is_n246 ){
$m4is_v586 =strtolower(trim($m4is_v586 ));
 $m4is_v586 =in_array($m4is_v586, $m4is_r37596 )? $m4is_v586 : $m4is_n246;
 return $m4is_v586;
 
} static 
function m4is_r879($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
$m4is_y642 =['color' =>'light',  'mobile' =>'', 'order' =>'social', 'posts' =>10, 'url' =>get_permalink(),  'width' =>550, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_o498 ='<div class="fb-comments" data-href="' . $m4is_l62046['url']. '" data-numposts="' . $m4is_l62046['posts']. '" data-colorscheme="' . $m4is_l62046['color']. '" data-order-by="' . $m4is_l62046['order']. '" data-width="' . $m4is_l62046['width']. '"></div>';
 return $m4is_o498;
 
} static 
function m4is_b86316($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
$m4is_y642 =['parent' =>'false', 'url' =>'',  'width' =>560, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_o498 ='<div class="fb-comment-embed" data-href="' . $m4is_l62046['url']. '" data-width="' . $m4is_l62046['width']. '" data-include-parent="' . $m4is_l62046['parent']. '"></div>';
 return $m4is_o498;
 
} static 
function m4is_s061($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
$m4is_y642 =['color' =>'light', 'coppa' =>'false', 'faces' =>'false', 'layout' =>'standard', 'size' =>'small', 'url' =>'https://www.facebook.com/memberium/', 'width' =>-1, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['color']=self::m4is_k657($m4is_l62046['color'], ['light', 'dark', ], 'light' );
 $m4is_l62046['coppa']=self::m4is_k657($m4is_l62046['coppa'], ['true', 'false', ], 'true' );
 $m4is_l62046['layout']=self::m4is_k657($m4is_l62046['layout'], ['standard', 'button_count', 'box_count'], 'standard' );
 $m4is_l62046['faces']=self::m4is_k657($m4is_l62046['faces'], ['true', 'false'], 'false' );
 $m4is_l62046['size']=self::m4is_k657($m4is_l62046['size'], ['large', 'small'], 'small' );
 if($m4is_l62046['layout']== 'standard' ){
if($m4is_l62046['width']== -1 ){
$m4is_l62046['width']=450;
 
}$m4is_s01 =225;
 $m4is_d2371 =35;
 if($m4is_l62046['faces']== 'true' ){
$m4is_d2371 =80;
 
}$m4is_l62046['width']=max($m4is_l62046['width'], $m4is_s01 );
 
}elseif($m4is_l62046['layout']== 'box_count' ){
$m4is_d2371 =65;
 $m4is_l62046['width']=55;
 
}elseif($m4is_l62046['layout']== 'button_count' ){
$m4is_l62046['width']=90;
 $m4is_d2371 =20;
 
}$m4is_u897 =['data-colorscheme' =>$m4is_l62046['color'], 'data-href' =>$m4is_l62046['url'], 'data-kid-directed-site' =>$m4is_l62046['coppa'], 'data-layout' =>$m4is_l62046['layout'], 'data-show-faces' =>$m4is_l62046['faces'], 'data-size' =>$m4is_l62046['size'], 'data-width' =>$m4is_l62046['width'], 'data-height' =>$m4is_d2371, ];
 $m4is_w367 ='';
 foreach($m4is_u897 as $m4is_o015 =>$m4is_k72 ){
if(!empty($m4is_k72 )){
$m4is_w367 .= $m4is_o015 . '="' . $m4is_k72 .'"';
 
}
}$m4is_o498 ='<div class="fb-follow" ' . $m4is_w367 . ' ></div>';
 return $m4is_o498;
 
} static 
function m4is_n30465($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 ='' ){
$m4is_y642 =['action' =>'like', 'color' =>'light', 'coppa' =>'false', 'faces' =>'false', 'layout' =>'standard', 'referral' =>'', 'share' =>'false', 'size' =>'small', 'url' =>get_permalink(), 'width' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['action']=self::m4is_k657($m4is_l62046['action'], ['like', 'recommend'], 'like' );
 $m4is_l62046['color']=self::m4is_k657($m4is_l62046['color'], ['light', 'dark'], 'light' );
 $m4is_l62046['coppa']=self::m4is_k657($m4is_l62046['coppa'], ['true', 'false'], 'false' );
 $m4is_l62046['layout']=self::m4is_k657($m4is_l62046['layout'], ['standard', 'button_count', 'button', 'box_count'], 'standard' );
 $m4is_l62046['share']=self::m4is_k657($m4is_l62046['layout'], ['true', 'false'], 'false');
 $m4is_l62046['faces']=self::m4is_k657($m4is_l62046['faces'], ['true', 'false'], 'false' );
 $m4is_l62046['size']=self::m4is_k657($m4is_l62046['size'], ['small', 'large'], 'small' );
 if($m4is_l62046['layout']== 'standard' ){
$m4is_l62046['width']=min($m4is_l62046['width'], 225 );
 
}elseif($m4is_l62046['layout']== 'box_count' ){
$m4is_l62046['width']=min($m4is_l62046['width'], 55 );
 
}elseif($m4is_l62046['layout']== 'button_count' ){
$m4is_l62046['width']=min($m4is_l62046['width'], 90 );
 
}elseif($m4is_l62046['layout']== 'button' ){
$m4is_l62046['width']=min($m4is_l62046['width'], 47 );
 
}$m4is_u897 =['data-action' =>$m4is_l62046['action'], 'data-colorscheme' =>$m4is_l62046['color'], 'data-href' =>$m4is_l62046['url'], 'data-kid-directed-site' =>$m4is_l62046['coppa'], 'data-layout' =>$m4is_l62046['layout'], 'data-ref' =>$m4is_l62046['referral'], 'data-share' =>$m4is_l62046['share'], 'data-show-faces' =>$m4is_l62046['faces'], 'data-size' =>$m4is_l62046['size'], 'data-width' =>$m4is_l62046['width'], ];
 $m4is_w367 ='';
 foreach($m4is_u897 as $m4is_o015 =>$m4is_k72 ){
if(!empty($m4is_k72 )){
$m4is_w367 .= $m4is_o015 . '="' . $m4is_k72 .'"';
 
}
}return '<div class="fb-like" ' . $m4is_w367 . ' ></div>';
 
} static 
function m4is_y36815($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 ='' ){
$m4is_y642 =['adapt' =>'true', 'faces' =>'true', 'height' =>500, 'hide_cover' =>'false', 'hide_cta' =>'false', 'small_header' =>'false', 'tabs' =>'timeline', 'url' =>'https://www.facebook.com/memberium/', 'width' =>340, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['width']=max($m4is_l62046['width'], 180 );
 $m4is_l62046['width']=min($m4is_l62046['width'], 500 );
 $m4is_l62046['height']=max($m4is_l62046['height'], 70 );
 $m4is_l62046['hide_cover']=self::m4is_k657($m4is_l62046['hide_cover'], ['true', 'false'], 'false' );
 $m4is_l62046['faces']=self::m4is_k657($m4is_l62046['faces'], ['true', 'false'], 'true' );
 $m4is_l62046['hide_cta']=self::m4is_k657($m4is_l62046['hide_cta'], ['true', 'false'], 'false' );
 $m4is_l62046['small_header']=self::m4is_k657($m4is_l62046['small_header'], ['true', 'false'], 'false' );
 $m4is_l62046['adapt']=self::m4is_k657($m4is_l62046['adapt'], ['true', 'false'], 'true' );
 $m4is_u897 =['data-href' =>$m4is_l62046['url'], 'data-width' =>$m4is_l62046['width'], 'data-height' =>$m4is_l62046['height'], 'data-tabs' =>$m4is_l62046['tabs'], 'data-hide-cover' =>$m4is_l62046['hide_cover'], 'datadata-show-facepile' =>$m4is_l62046['faces'], 'data-hide-cta' =>$m4is_l62046['hide_cta'], 'data-small-header' =>$m4is_l62046['small_header'], 'data-adapt-container-width' =>$m4is_l62046['adapt'], ];
 $m4is_w367 ='';
 foreach($m4is_u897 as $m4is_o015 =>$m4is_k72 ){
if(!empty($m4is_k72 )){
$m4is_w367 .= $m4is_o015 . '="' . $m4is_k72 .'"';
 
}
}return '<div class="fb-like" ' . $m4is_w367 . ' ></div>';
 
} static 
function m4is_w50($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 ='' ){
$m4is_y642 =['size' =>'large', 'url' =>get_permalink(), ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['size']=self::m4is_k657($m4is_l62046['size'], ['small', 'large'], 'large' );
 $m4is_u897 =['data-size' =>$m4is_l62046['size'], 'data-uri' =>$m4is_l62046['url'], ];
 $m4is_w367 ='';
 foreach($m4is_u897 as $m4is_o015 =>$m4is_k72 ){
if(!empty($m4is_k72 )){
$m4is_w367 .= $m4is_o015 . '="' . $m4is_k72 .'"';
 
}
}$m4is_o498 ='<div class="fb-save" ' . $m4is_w367 . '></div>';
 return $m4is_o498;
 
} static 
function m4is_i15($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 ='' ){
$m4is_y642 =['color' =>'light', 'coppa' =>'false', 'referral' =>'', 'url' =>get_permalink(), ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['color']=self::m4is_k657($m4is_l62046['color'], ['light', 'dark'], 'light' );
 $m4is_l62046['coppa']=self::m4is_k657($m4is_l62046['coppa'], ['true', 'false'], 'false' );
 $m4is_l62046['size']='small';
 $m4is_u897 =['data-colorscheme' =>$m4is_l62046['color'], 'data-href' =>$m4is_l62046['url'], 'data-kid-directed-site' =>$m4is_l62046['coppa'], 'data-ref' =>$m4is_l62046['referral'], 'data-size' =>$m4is_l62046['size'], ];
 $m4is_w367 ='';
 foreach($m4is_u897 as $m4is_o015 =>$m4is_k72 ){
if(!empty($m4is_k72 )){
$m4is_w367 .= $m4is_o015 . '="' . $m4is_k72 .'"';
 
}
}$m4is_o498 ='<div class="fb-send" ' . $m4is_w367 . ' ></div>';
 return $m4is_o498;
 
} static 
function m4is_g584($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 ='' ){
$m4is_y642 =['layout' =>'icon_link', 'mobile' =>'false', 'size' =>'small', 'url' =>get_permalink(), ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['layout']=self::m4is_k657($m4is_l62046['layout'], ['box_count', 'button_count', 'button', 'icon_link'], 'icon_link' );
 $m4is_l62046['mobile']=self::m4is_k657($m4is_l62046['mobile'], ['true', 'false'], 'false' );
 $m4is_l62046['size']=self::m4is_k657($m4is_l62046['size'], ['small', 'large'], 'small' );
 $m4is_u897 =['data-href' =>$m4is_l62046['url'], 'data-layout' =>$m4is_l62046['layout'], 'data-mobile_iframe' =>$m4is_l62046['mobile'], 'data-size' =>$m4is_l62046['size'], ];
 $m4is_w367 ='';
 foreach($m4is_u897 as $m4is_o015 =>$m4is_k72 ){
if(!empty($m4is_k72 )){
$m4is_w367 .= $m4is_o015 . '="' . $m4is_k72 .'"';
 
}
}$m4is_o498 ='<div class="fb-share-button" ' . $m4is_w367 . ' >';
 $m4is_o498 .= '<a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=' . urlencode($m4is_l62046['url']). '">Share</a></div>';
 return $m4is_o498;
 
} static 
function m4is_x126($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 ='' ){
$m4is_y642 =['post' =>'false', 'url' =>'',  'width' =>500, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['width']=max($m4is_l62046['width'], 220 );
 $m4is_l62046['post']=self::m4is_k657($m4is_l62046['post'], ['true', 'false'], 'false');
 $m4is_u897 =['data-href' =>$m4is_l62046['url'], 'data-width' =>$m4is_l62046['width'], 'data-show-text' =>$m4is_l62046['post'], ];
 $m4is_w367 ='';
 foreach($m4is_u897 as $m4is_o015 =>$m4is_k72 ){
if(!empty($m4is_k72 )){
$m4is_w367 .= $m4is_o015 . '="' . $m4is_k72 .'"';
 
}
}$m4is_o498 ='<div class="fb-video" ' . $m4is_w367 . ' ><div class="fb-xfbml-parse-ignore"></div></div>';
 return $m4is_o498;
 
}
}

