<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_p60 {
private $m4is_r1546;
 static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_d4861();
 $this->m4is_f3276();
 
}private 
function m4is_d4861(): void {
add_action('wpcomplete_page_completed', [$this, 'elf_mark_post_complete']);
 add_action('wpcomplete_course_completed', [$this, 'm4is_p12']);
 
}private 
function m4is_f3276(): void {
if(is_admin()){
if(include_once(__DIR__ . '/admin.php' )){
m4is_c48235::m4is_c26();
 
}
}
}public 
function m4is_p12($m4is_l91805 =[]): void {
$m4is_f087 =isset($m4is_l91805['user_id'])? $m4is_l91805['user_id']: $this->m4is_r1546->m4is_x66();
 $m4is_b4068 =isset($m4is_l91805['post_id'])? $m4is_l91805['post_id']: 0;
 $m4is_n524 =isset($m4is_l91805['button_id'])? $m4is_l91805['button_id']: 0;
 $m4is_n3691 =isset($m4is_l91805['course'])? $m4is_l91805['course']: 0;
 $m4is_h21895 =(int) m4is_p40::m4is_w58096($m4is_f087 );
 if(empty($m4is_n0423 )){
return;
 
}$m4is_l9321 =get_post_meta($m4is_b4068, '_is4wp_wpcomplete_tags', true);
 if(empty($m4is_l9321)){
return;
 
}$this->m4is_r1546->m4is_k98($m4is_l9321, $m4is_h21895 );
 
}
}

