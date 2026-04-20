<?php
 class_exists('m4is_r83' )||die();
  final 
class m4is_g7460 {
private $m4is_i935 =[];
 private $m4is_v586 ='';
 private $m4is_y368 =[];
 private $m4is_h21895 =0;
 private $m4is_i8169 =false;
 private $m4is_i215 =0;
 private $m4is_j8236 =[];
 private $m4is_j613 =[];
 private $m4is_v8723 ='';
 private $m4is_a9058 =[];
 private $m4is_o0379 =0;
 private $m4is_b65 =true;
 private $m4is_m5907 =[];
 private $m4is_b76 ='';
 private $m4is_c53 =0;
 private $m4is_f2937 =[];
 private $m4is_m06 =[];
 private $m4is_e32607 =[];
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
 $this->m4is_h067();
 $this->m4is_e4608();
 $this->m4is_x6560();
 $this->m4is_l196();
 
}
function __destruct(){
$m4is_z8419 =microtime(true )- $this->m4is_c53;
 $m4is_a173 =sprintf("Completed Copy Fields HTTP POST.  Processing time: %0.3f seconds\n", $m4is_z8419 );
 $this->m4is_a9058[]=$m4is_a173;
 $this->m4is_a9058 =array_filter($this->m4is_a9058 );
 if($this->m4is_o0379 &&!empty($this->m4is_a9058 )){
m4is_q62395::m4is_x6835($this->m4is_o0379, implode("\n", $this->m4is_a9058 ));
 
}if($this->m4is_i8169 ){
echo $m4is_a173;
 
}
}private 
function m4is_q97523(array $m4is_j613, array $m4is_m5907 ){
$this->m4is_y368 =m4is_c69807::m4is_f5248('Contact', true );
 $this->m4is_c53 =isset($_SERVER['REQUEST_TIME_FLOAT'])? $_SERVER['REQUEST_TIME_FLOAT']: microtime(true );
 $this->m4is_j613 =$m4is_j613;
 $this->m4is_m5907 =$m4is_m5907;
 ksort($this->m4is_m5907 );
 
}private 
function m4is_i082(){
$this->m4is_i935 =$this->m4is_m5907;
 $this->m4is_h21895 =isset($this->m4is_i935['Id'])? (int) $this->m4is_i935['Id']: $this->m4is_h21895;
 $this->m4is_i8169 =isset($this->m4is_j613['debug'])? $this->m4is_s1570($this->m4is_j613['debug'], false ): false;
 $this->m4is_b76 =isset($this->m4is_j613['source'])? $this->m4is_j613['source']: '';
 $this->m4is_m06 =array_filter(explode(',', $this->m4is_j613['target']?? '' ));
 $this->m4is_b65 =isset($this->m4is_j613['overwrite'])? $this->m4is_s1570($this->m4is_j613['overwrite'], false ): false;
 $this->m4is_v8723 =empty($this->m4is_j613['goal'])? '' : $this->m4is_j613['goal'];
 $this->m4is_f2937 =empty($this->m4is_j613['tagids'])? []: array_filter(explode(',', $this->m4is_j613['tagids']));
 $this->m4is_i215 =empty($this->m4is_j613['delay'])? 0 : (int) $this->m4is_j613['delay'];
 $this->m4is_o0379 =m4is_q62395::m4is_v729($this->m4is_h21895, 'httppost', "Copy Fields\n");
 
}private 
function m4is_h067(){
$m4is_e0234 =[];
 $m4is_m02983 =[];
 if(!$this->m4is_h21895 ){
$m4is_e0234[]="Error: No Contact ID provided";
 
}if(empty($this->m4is_b76 )||!in_array($this->m4is_b76, $this->m4is_y368 )){
$m4is_e0234[]="Error: No valid source field provided.";
 
}foreach($this->m4is_m06 as $m4is_l9671 =>$m4is_r637 ){
if($m4is_r637 == $this->m4is_b76 ){
$m4is_m02983[]='Warning: You cannot set your source field as one of your target fields.';
 unset($this->m4is_m06[$m4is_l9671]);
 
}if(!$this->m4is_b65 ){
if(!empty($this->m4is_i935[$m4is_r637 ])){
$m4is_m02983[]=sprintf('Warning: Overwrite is Off, Skipping populated field %s.', $m4is_r637 );
 unset($this->m4is_m06[$m4is_l9671]);
 
}
}if(!in_array($m4is_r637, $this->m4is_y368 )){
$m4is_m02983[]=sprintf("Warning: %s is not a valid target field.", $m4is_r637 );
 unset($this->m4is_m06[$m4is_l9671]);
 
}
}if(empty($this->m4is_m06 )){
$m4is_e0234[]="Error: No valid target fields provided.";
 
}$this->m4is_a9058 =array_merge($this->m4is_a9058, $m4is_m02983, $m4is_e0234 );
 $this->m4is_v586 =isset($this->m4is_i935[$this->m4is_b76 ])? $this->m4is_i935[$this->m4is_b76 ]: '';
 if($this->m4is_i8169 ){
printf("%s = %s = %s\n", __LINE__, 'Contact ID', $this->m4is_h21895 );
 printf("%s = %s = %s\n", __LINE__, 'Source Field', $this->m4is_b76 );
 printf("%s = %s = %s\n", __LINE__, 'Source Value', $this->m4is_v586 );
 printf("%s = %s = %s\n", __LINE__, 'Target Fields', implode(', ', $this->m4is_m06 ));
 printf("%s = %s = %s\n", __LINE__, 'Overwrite', $this->m4is_o8570($this->m4is_b65 ));
 echo implode("\n", $m4is_m02983 ), "\n";
 echo implode("\n", $m4is_e0234 );
 
}if(count($m4is_e0234 )){
die();
 
}
}private 
function m4is_e4608(){
if(!empty($this->m4is_j8236 )){
$this->m4is_v586 =m4is_f61::m4is_n05($this->m4is_v586, implode(',', $this->m4is_j8236 ));
 if($this->m4is_i8169 ){
printf("New Source Value = %s\n", __LINE__, $this->m4is_v586 );
 
}
}else{
if($this->m4is_i8169 ){
echo "No text formatting applied.\n";
 
}
}
}private 
function m4is_x6560(){
foreach($this->m4is_m06 as $m4is_r637 ){
$this->m4is_e32607[$m4is_r637 ]=$this->m4is_v586;
 $this->m4is_i935[$m4is_r637 ]=$this->m4is_v586;
 
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
$m4is_e46082 =array_filter(explode(':', $this->m4is_v8723 ));
 $m4is_s63 =$m4is_e46082[0];
 $m4is_v8723 =$m4is_e46082[1];
 m4is_c69807::m4is_z3902($this->m4is_h21895, $m4is_v8723, $m4is_s63 );
 if($this->m4is_i8169 ){
printf("%d - Goal Achieved with %s\n", __LINE__, $this->m4is_v8723 );
 
}
}m4is_r83::m4is_c26()->m4is_i12($this->m4is_h21895 );
 if($this->m4is_i8169 ){
echo __LINE__, " - Updated Contact\n";
 echo __LINE__, " - Sleeping: {$this->m4is_i215
} seconds\n";
 
}m4is_r83::m4is_c26()->m4is_s965('send_http_post');
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

