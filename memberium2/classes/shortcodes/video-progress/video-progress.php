<?php
 class_exists('m4is_r83')||die();
  
function m4is_f580(){
return m4is_i63417::m4is_c26();
 
} 
class m4is_i63417 {
 protected $to_json =[];
  protected $print_scripts =false;
  private 
function __construct(){

} static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} 
function m4is_b61493($m4is_l62046, $m4is_t09761 =null, $m4is_v3458){
m4is_j586::m4is_x7134();
 $m4is_y642 =['src' =>'', 'time_tags' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_r46793 =[];
 $m4is_c4062 =($m4is_l62046['time_tags']> '' )? array_filter(explode(',', $m4is_l62046['time_tags'])): [];
 if(!empty($m4is_c4062)&&!empty($m4is_l62046['src'])){
foreach ($m4is_c4062 as $m4is_u408 =>$m4is_k61){
$time_tag_array =array_filter(explode('|', $m4is_k61 ));
 $m4is_a873 =(isset($time_tag_array[0]))? $time_tag_array[0]: false;
 $m4is_p786 =(isset($time_tag_array[1]))? $time_tag_array[1]: false;
 if($m4is_a873 &&$m4is_p786 ){
$m4is_r46793[]=['time' =>$this->m4is_l528($m4is_a873), 'tag_id' =>$m4is_p786 ];
 
}
}$m4is_o36126 =$this->m4is_z907($m4is_l62046['src']);
 $this->to_json[]=['src' =>$m4is_l62046['src'], 'time_tags' =>$m4is_r46793, 'type' =>$this->m4is_z907($m4is_l62046['src']), ];
 if(!$this->print_scripts){
wp_register_script('memberium-video-progress', plugin_dir_url(__FILE__ ). 'js/video-progress.js', ['jquery'], m4is_r83::m4is_c26()->m4is_w45(), true );
 wp_enqueue_script('memberium-video-progress' );
 add_action('wp_print_footer_scripts', [$this, 'm4is_s02696'], 9999 );
 $this->print_scripts =true;
 
}
}
} 
function m4is_z907($m4is_x680){
$m4is_i65847 =$m4is_m860 =$is_wistia =$is_video =false;
 $m4is_y4856 ='#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#';
 $m4is_j61608 ='#^https?://(.+\.)?vimeo\.com/.*#';
 $m4is_i65847 =(preg_match($m4is_j61608, $m4is_x680 ));
 $m4is_m860 =(preg_match($m4is_y4856, $m4is_x680 ));
 $m4is_j0361 =($m4is_m860)? 'youtube' : false;
 $m4is_j0361 =($m4is_i65847)? 'vimeo' : $m4is_j0361;
 return $m4is_j0361;
 
} 
function m4is_s02696(){
m4is_f58::m4is_c26()->m4is_h6904('mvp_params', $this->to_json);
 
} 
function m4is_l528($m4is_a873){
$m4is_i41506 =preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $m4is_a873);
 sscanf($m4is_i41506, "%d:%d:%d", $m4is_q746, $m4is_q2836, $m4is_y7052);
 return ($m4is_q746 * 3600 + $m4is_q2836 * 60 + $m4is_y7052 );
 
}
}

