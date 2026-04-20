<?php
 class_exists('m4is_r83')||die();
  final 
class m4is_r296 {
private $m4is_i935 =[], $m4is_h21895 =0, $m4is_i215 =1, $m4is_i8169 =false, $m4is_f4930 ='', $m4is_j613 =[], $m4is_v8723 ='', $m4is_e0234 =[], $m4is_a9058 =[], $m4is_o0379 =0, $m4is_m5907 =[], $m4is_f2937 =[], $m4is_l17096 =null, $m4is_f087 =0;
 static 
function m4is_z95(array $m4is_j613 =[], array $m4is_m5907 =[]): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self($m4is_j613, $m4is_m5907 );
 
}private 
function __construct(array $m4is_j613, array $m4is_m5907 ){
ini_set('display_errors', 1 );
 $m4is_j613 =empty($m4is_j613 )? $_GET : $m4is_j613;
 $m4is_m5907 =empty($m4is_m5907 )? $_POST : $m4is_m5907;
 m4is_j586::m4is_x7134();
 $this->m4is_q97523($m4is_j613, $m4is_m5907 );
 $this->m4is_i082();
 $this->m4is_r17894();
 $this->m4is_h067();
 $this->m4is_e86129();
 $this->m4is_e6317();
 
}
function __destruct(){
if($this->m4is_o0379 &&!empty($this->m4is_a9058 )){
m4is_q62395::m4is_x6835($this->m4is_o0379, implode("\n", $this->m4is_a9058 ));
 
}if($this->m4is_i8169 ){
printf("Exiting Delete Contact HTTP POST Process at %s\n", microtime(true ));
 
}
}private 
function m4is_q97523(array $m4is_j613, array $m4is_m5907 ){
ksort($this->m4is_m5907 );
 $this->m4is_j613 =$m4is_j613;
 $this->m4is_m5907 =$m4is_m5907;
 
}private 
function m4is_i082(){
$this->m4is_i935 =$this->m4is_m5907;
 $this->m4is_h21895 =isset($this->m4is_i935['Id'])? (int) $this->m4is_i935['Id']: $this->m4is_h21895;
 $this->m4is_f4930 =isset($this->m4is_i935['Email'])? $this->m4is_i935['Email']: '';
 $this->m4is_i8169 =isset($this->m4is_j613['debug'])? $this->m4is_s1570($this->m4is_j613['debug'], false ): false;
 $this->m4is_v8723 =empty($this->m4is_j613['goal'])? '' : $this->m4is_j613['goal'];
 $this->m4is_f2937 =empty($this->m4is_j613['tagids'])? []: array_filter(explode(',', $this->m4is_j613['tagids']));
 $this->m4is_o0379 =m4is_q62395::m4is_v729($this->m4is_h21895, 'httppost', 'Delete User' );
 $this->m4is_a9058[]=sprintf("Contact ID = %d, Email = %s", $this->m4is_h21895, $this->m4is_f4930 );
 if($this->m4is_i8169 ){
printf("%s - %s\n", __LINE__, 'Begin Contact Deletion at ' . microtime(true ));
 printf("%s - %s\n", __LINE__, 'Debug Mode Enabled' );
 printf("%s - %s = %s\n", __LINE__, 'Contact ID', $this->m4is_h21895 );
 printf("%s - %s = %s\n", __LINE__, 'Email Address', $this->m4is_f4930 );
 printf("%s - %s = %s\n", __LINE__, 'API Goal', $this->m4is_v8723 );
 printf("%s - %s = %s\n", __LINE__, 'Tag IDs', implode(', ', $this->m4is_f2937 ));
 
}
}private 
function m4is_r17894(){
$this->m4is_l17096 =get_user_by('email', $this->m4is_f4930 );
 $this->m4is_f087 =$this->m4is_l17096 ? $this->m4is_l17096->ID : 0;
 $m4is_a173 =sprintf("Matched Contact ID = %d to User ID = %d", $this->m4is_h21895, $this->m4is_f087 );
 $this->m4is_a9058[]=$m4is_a173;
 if($this->m4is_i8169 ){
printf("%s - %s\n", __LINE__, $m4is_a173 );
 
}
}private 
function m4is_h067(){
$m4is_e0234 =[];
 if(!$this->m4is_h21895 ){
$m4is_e0234[]='Error:  Invalid Keap contact ID';
 
}if(empty($this->m4is_f4930 )){
$m4is_e0234[]='Error:  Email address missing or invalid';
 
}if($this->m4is_l17096 &&user_can($this->m4is_l17096, 'edit_others_posts' )){
$m4is_e0234[]='Error:  Contact email matches Editor access.';
 
}if(!empty($m4is_e0234 )){
if($this->m4is_i8169 ){
echo implode("\n", $m4is_e0234 ), "\n";
 echo "Aborted User Deletion\n";
 
}$this->m4is_a9058 =array_merge($this->m4is_a9058, $m4is_e0234 );
 exit;
 
}
}private 
function m4is_e86129(){
m4is_p40::m4is_z62($this->m4is_h21895, true );
 $m4is_a173 =sprintf('Purged local cache for contact ID %d', $this->m4is_h21895 );
 $this->m4is_e0234[]=$m4is_a173;
 if($this->m4is_i8169){
printf("%s - %s\n", __LINE__, $m4is_a173 );
 
}
}private 
function m4is_e6317(){
if(!$this->m4is_f087 ){
return;
 
}$m4is_a173 ='';
 require_once ABSPATH . '/wp-admin/includes/user.php';
 if(function_exists('wp_delete_user' )){
if(wp_delete_user($this->m4is_f087, false )){
$m4is_a173 =sprintf('Deleted WordPress User ID %d', $this->m4is_f087 );
 do_action('memberium/httppost/contact/delete', $this->m4is_f087 );
 
}else{
$m4is_a173 ='Error:  WordPress user deletion failed.';
 
}
}if($m4is_a173 ){
if($this->m4is_i8169 ){
echo __LINE__, ' - ', $m4is_a173, "\n";
 
}$this->m4is_a9058[]=$m4is_a173;
 
}
}private 
function m4is_l196(){
if(!empty($this->m4is_f2937 )){
m4is_r83::m4is_c26()->m4is_k98($this->m4is_f2937, $this->m4is_h21895 );
 if($this->m4is_i8169 ){
echo __LINE__, " - Added Tags: ", implode(', ', $this->m4is_f2937 ), "\n";
 
}$this->m4is_a9058[]=sprintf('Added Tags ', implode(', ', $this->m4is_f2937 ));
 
}if(!empty($this->m4is_v8723 )){
$m4is_e46082 =array_filter(explode(':', $this->m4is_v8723 ));
 $m4is_s63 =$m4is_e46082[0];
 $m4is_v8723 =$m4is_e46082[1];
 m4is_c69807::m4is_z3902($this->m4is_h21895, $m4is_v8723, $m4is_s63 );
 if($this->m4is_i8169 ){
printf("%d - Goal Achieved with %s\n", __LINE__, $this->m4is_v8723 );
 
}
}if($this->m4is_i8169 ){
echo __LINE__, " - Updated Contact\n";
 echo __LINE__, " - Sleeping: {$this->m4is_i215
} seconds\n";
 
}sleep($this->m4is_i215 );
 
}private 
function m4is_o8570($m4is_v586 ){
return $m4is_v586 ? 'Yes' : 'No';
 
}private 
function m4is_s1570($m4is_v586, bool $m4is_n246 =false ): bool {
$m4is_v586 =strtolower(substr(trim($m4is_v586 ), 0, 1 ));
 return in_array($m4is_v586, ['y', 't', '1' ])? true : (in_array($m4is_v586, ['n', 'f', '0' ])? false : $m4is_n246 );
 
}private 
function m4is_z90641(){

}
}

