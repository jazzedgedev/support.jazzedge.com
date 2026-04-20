<?php
 class_exists('m4is_r83' )||die();
  final 
class m4is_w3605 {
private  $m4is_i935 =[], $m4is_h21895 =0, $m4is_y368 =[], $m4is_i8169 =false, $m4is_i215 =0, $m4is_j613 =[], $m4is_v8723 ='', $m4is_a9058 =[], $m4is_o0379 ='', $m4is_m5907 =[], $m4is_c53 =0, $m4is_f2937 =[], $m4is_v586 ='', $m4is_e32607 =[], $m4is_f16985 ='', $m4is_a1056 ='', $m4is_w54 ='', $m4is_b76 ='', $m4is_m06 =[];
 static 
function m4is_z95(array $m4is_j613 =[], array $m4is_m5907 =[]): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self($m4is_j613, $m4is_m5907 );
 
}private 
function __construct(array $m4is_j613, array $m4is_m5907 ){
ini_set('display_errors', 1 );
 $m4is_j613 =empty($m4is_j613 )? $_GET : $m4is_j613;
 $m4is_m5907 =empty($m4is_m5907 )? $_POST : $m4is_m5907;
 $this->m4is_q97523($m4is_j613, $m4is_m5907 );
 $this->m4is_i082();
 $this->m4is_h067();
 $this->m4is_h6796();
 $this->m4is_x6560();
 $this->m4is_l196();
 
}
function __destruct(){
$m4is_z8419 =microtime(true )- $this->m4is_c53;
 $m4is_a173 =sprintf("Completed Set Date HTTP POST.  Processing time: %0.3f seconds\n", $m4is_z8419 );
 $this->m4is_a9058[]=$m4is_a173;
 $this->m4is_a9058 =array_filter($this->m4is_a9058 );
 if($this->m4is_o0379 &&!empty($this->m4is_a9058 )){
m4is_q62395::m4is_x6835($this->m4is_o0379, implode("\n", $this->m4is_a9058 ));
 
}if($this->m4is_i8169 ){
echo $m4is_a173;
 
}
}private 
function m4is_q97523(array $m4is_j613, array $m4is_m5907 ){
$this->m4is_c53 =isset($_SERVER['REQUEST_TIME_FLOAT'])? $_SERVER['REQUEST_TIME_FLOAT']: microtime(true );
 $this->m4is_j613 =$m4is_j613;
 $this->m4is_m5907 =$m4is_m5907;
 $this->m4is_y368 =m4is_c69807::m4is_f5248('Contact', false );
 ksort($this->m4is_m5907 );
 
}private 
function m4is_i082(){
$this->m4is_i935 =$this->m4is_m5907;
 $this->m4is_h21895 =isset($this->m4is_i935['Id'])? (int) $this->m4is_i935['Id']: $this->m4is_h21895;
 $this->m4is_i8169 =isset($this->m4is_j613['debug'])? $this->m4is_s1570($this->m4is_j613['debug'], false ): false;
 $this->m4is_i215 =empty($this->m4is_j613['delay'])? 0 : (int) $this->m4is_j613['delay'];
 $this->m4is_f16985 =empty($this->m4is_j613['format'])? 'Y-m-d\TH:i:s' : $this->m4is_j613['format'];
 $this->m4is_a1056 =empty($this->m4is_j613['function'])? '' : str_replace('plus', '+', $this->m4is_j613['function']);
 $this->m4is_v8723 =empty($this->m4is_j613['goal'])? '' : $this->m4is_j613['goal'];
 $this->m4is_b76 =isset($this->m4is_j613['source'])? $this->m4is_j613['source']: 'now';
 $this->m4is_f2937 =empty($this->m4is_j613['tagids'])? []: array_filter(explode(',', $this->m4is_j613['tagids']));
 $this->m4is_m06 =isset($this->m4is_j613['target'])? array_filter(explode(',', $this->m4is_j613['target'])): [];
 $this->m4is_o0379 =m4is_q62395::m4is_v729($this->m4is_h21895, 'httppost', "Set Date\n");
 
}private 
function m4is_h067(){
$m4is_e0234 =[];
 $m4is_m02983 =[];
 if(!$this->m4is_h21895 ){
$m4is_e0234[]="Error: No Contact ID provided";
 
}if(empty($this->m4is_b76 )&&!in_array($this->m4is_b76, $this->m4is_y368 )){
$m4is_e0234[]="Error: No valid source field provided.";
 
}foreach($this->m4is_m06 as $m4is_l9671 =>$m4is_r637 ){
if(!in_array($m4is_r637, $this->m4is_y368 )){
$m4is_m02983[]=sprintf("Warning: %s is not a valid target field.", $m4is_r637 );
 unset($this->m4is_m06[$m4is_l9671]);
 
}
}if(empty($this->m4is_m06 )){
$m4is_e0234[]="Error: No valid target fields provided.";
 
}if(in_array($this->m4is_b76, $this->m4is_y368 )&&!empty($this->m4is_i935[$this->m4is_b76 ])){
$this->m4is_v586 =$this->m4is_i935[$this->m4is_b76 ];
 
}$this->m4is_a9058 =array_merge($this->m4is_a9058, $m4is_m02983, $m4is_e0234 );
 if($this->m4is_i8169 ){
printf("%s = %s = %s\n", __LINE__, 'Contact ID', $this->m4is_h21895 );
 printf("%s = %s = %s\n", __LINE__, 'Source Field', $this->m4is_b76 );
 printf("%s = %s = %s\n", __LINE__, 'Source Value', $this->m4is_v586 );
 printf("%s = %s = %s\n", __LINE__, 'Function/Operation', $this->m4is_a1056 );
 printf("%s = %s = %s\n", __LINE__, 'Date Format', $this->m4is_f16985 );
 printf("%s = %s = %s\n", __LINE__, 'Target Fields', implode(', ', $this->m4is_m06 ));
 echo empty($m4is_m02983 )? '' : implode("\n", $m4is_m02983 ). "\n";
 echo empty($m4is_e0234 )? '' : implode("\n", $m4is_e0234 ). "\n";
 
}if(count($m4is_e0234 )){
die();
 
}
}private 
function m4is_h6796(){
$this->m4is_w54 =date($this->m4is_f16985, strtotime($this->m4is_v586 . $this->m4is_a1056 ));
 $this->m4is_a9058[]=sprintf("Source = %s, Value = %s, Function = %s", $this->m4is_b76, $this->m4is_v586, $this->m4is_a1056 );
 $this->m4is_a9058[]=sprintf("Format = %s, New Date = %s, Target Fields = %s", $this->m4is_f16985, $this->m4is_w54, implode(', ', $this->m4is_m06 ));
 if($this->m4is_i8169 ){
printf("%s = %s = %s\n", __LINE__, 'Destination Value', $this->m4is_w54 );
 
}
}private 
function m4is_x6560(){
foreach ($this->m4is_m06 as $m4is_r637 ){
$this->m4is_e32607[$m4is_r637 ]=$this->m4is_w54;
 $this->m4is_i935[$m4is_r637 ]=$this->m4is_w54;
 
}if(count($this->m4is_e32607 )){
m4is_p40::m4is_x6560($this->m4is_h21895, $this->m4is_e32607 );
  m4is_r83::m4is_c26()->m4is_v1694($this->m4is_i935 );
 
}
}private 
function m4is_l196(){
if(!empty($this->m4is_f2937 )){
m4is_r83::m4is_c26()->m4is_k98($this->m4is_f2937, $this->m4is_h21895 );
 if($this->m4is_i8169 ){
echo __LINE__, " - Added Tags: ", implode(', ', $this->m4is_f2937 ), "\n";
 
}$this->m4is_a9058[]=sprintf('Added Tags ', implode(', ', $this->m4is_f2937 ));
 
}if(!empty($this->m4is_v8723 )){
$m4is_e46082 =explode(':', $this->m4is_v8723 );
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
 
}m4is_r83::m4is_c26()->m4is_i12($this->m4is_h21895 );
 m4is_r83::m4is_c26()->m4is_s965('send_http_post');
 sleep($this->m4is_i215 );
 
}private 
function m4is_o8570($m4is_v586 ){
return $m4is_v586 ? 'Yes' : 'No';
 
}private 
function m4is_s1570($m4is_v586, bool $m4is_n246 =false ): bool {
$m4is_v586 =strtolower(substr(trim($m4is_v586 ), 0, 1 ));
 return in_array($m4is_v586, ['y', 't', '1' ])? true : (in_array($m4is_v586, ['n', 'f', '0' ])? false : $m4is_n246 );
 
}
}

