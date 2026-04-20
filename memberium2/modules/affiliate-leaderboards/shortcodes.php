<?php
 class_exists('m4is_r83')||die();
 m4is_q03964::m4is_h269();
 final 
class m4is_q03964 {
private static $m4is_r1546;
 private static $m4is_u934;
 private static $m4is_f4218;
 static 
function m4is_h269(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_u934 =m4is_v967::m4is_c26();
 self::$m4is_f4218 ='memberium';
 
}static 
function m4is_c046($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
static $m4is_e52716 =[];
 m4is_j586::m4is_x7134();
 $m4is_y642 =['amount' =>false, 'css_id' =>'', 'except' =>'', 'id' =>0, 'limit' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 if(empty($m4is_l62046['id'])){
return self::$m4is_r1546->m4is_v461()? '<p style="font-weight:bold">Leaderboard ID Missing</p>' : '';
 
}$m4is_l62046['amount']=m4is_f61::m4is_d8195($m4is_l62046['amount'], false );
 $m4is_l62046['except']=array_filter(explode(',', $m4is_l62046['except']), 'trim' );
 $m4is_o498 ='';
 $m4is_q1046 =md5(serialize($m4is_l62046 ));
 $m4is_y50 =self::$m4is_u934->m4is_x4830($m4is_l62046['id']);
 if(empty($m4is_y50 )||empty($m4is_y50['cache'])){
return '';
 
}$m4is_n58 =$m4is_y50['cache'];
 foreach($m4is_l62046['except']as $m4is_l9671 ){
unset($m4is_n58[$m4is_l9671]);
 
}$m4is_h973 =0;
 $m4is_o498 .= '<div class="memberium-leaderboard">';
 foreach($m4is_n58 as $m4is_c74206 =>$m4is_v586 ){
$m4is_a566 =m4is_z13097::m4is_d95($m4is_c74206 );
 $m4is_o498 .= '<div class="leaderboard-row">';
 $m4is_o498 .= '<span class="leaderboard-order">' . (1 + $m4is_h973 ). '</span>';
 $m4is_o498 .= '<span class="leaderboard-name">' . $m4is_a566['AffName'];
 if(self::$m4is_r1546->m4is_v461()){
$m4is_o498 .= " ({$m4is_c74206
})";
 
}$m4is_o498 .= '</span>';
 if($m4is_l62046['amount']){
$m4is_o498 .= '<span class="leaderboard-value">' . $m4is_v586 . '</span>';
 
}$m4is_o498 .= '</div>';
 $m4is_h973++;
 if($m4is_h973 == $m4is_l62046['limit']){
break;
 
}
}$m4is_o498 .= '</div>';
 return $m4is_o498;
 
}
}

