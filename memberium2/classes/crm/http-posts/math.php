<?php
 class_exists('m4is_r83' )||die();
  final 
class m4is_p75 {
private $m4is_a9058 =[];
 private $m4is_i935 =[];
 private $m4is_h21895 =0;
 private $m4is_i8169 =false;
 private $m4is_p4219 ='';
 private $m4is_f69781 =[];
 private $m4is_f16985 ='';
 private $m4is_a1056 ='';
 private $m4is_c53 =0;
 private $m4is_j613 =[], $m4is_v8723 ='', $m4is_e81053 ='', $m4is_o0379 =null, $m4is_p48691 =null, $m4is_x74 =null, $m4is_z75923 ='pure',  $m4is_m5907 =[], $m4is_b96 ='', $m4is_u6591 =null, $m4is_n2397 =0, $m4is_f2937 =[], $m4is_f16846 =0, $m4is_c269 =0;
 static 
function m4is_z95(array $m4is_j613 =[], array $m4is_m5907 =[]): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self($m4is_j613, $m4is_m5907 );
 
}private 
function __construct(array $m4is_j613, array $m4is_m5907 ){
$m4is_j613 =empty($m4is_j613 )? $_GET : $m4is_j613;
 $m4is_m5907 =empty($m4is_m5907 )? $_POST : $m4is_m5907;
 $this->m4is_q97523($m4is_j613, $m4is_m5907 );
 $this->m4is_i082();
 $this->m4is_b61493();
 $this->m4is_u17026();
 $this->m4is_h58661();
 $this->m4is_s490();
 $this->m4is_s49();
 $this->m4is_o5679();
 
}
function __destruct(){
$m4is_z8419 =microtime(true )- $this->m4is_c53;
 $m4is_a173 =sprintf("Completed Math HTTP POST.  Processing time: %0.3f seconds\n", $m4is_z8419 );
 $this->m4is_a9058[]=$m4is_a173;
 $this->m4is_a9058 =array_filter($this->m4is_a9058 );
 if($this->m4is_o0379 &&!empty($this->m4is_a9058 )){
m4is_q62395::m4is_x6835($this->m4is_o0379, implode("\n", $this->m4is_a9058 ));
 
}if($this->m4is_i8169 ){
echo $m4is_a173;
 
}
}private 
function m4is_q97523(array $m4is_j613, array $m4is_m5907 ){
ini_set('display_errors', 1 );
 $this->m4is_c53 =isset($_SERVER['REQUEST_TIME_FLOAT'])? $_SERVER['REQUEST_TIME_FLOAT']: microtime(true );
 $this->m4is_j613 =$m4is_j613;
 $this->m4is_m5907 =$m4is_m5907;
 $this->m4is_f69781 =m4is_c69807::m4is_f5248('Contact', false );
 
} private 
function m4is_i082(){
ksort($this->m4is_m5907 );
 $this->m4is_i8169 =isset($this->m4is_j613['debug']);
 $this->m4is_i935 =$this->m4is_m5907;
 $this->m4is_h21895 =isset($this->m4is_i935['Id'])? (int) $this->m4is_i935['Id']: $this->m4is_h21895;
 $this->m4is_p4219 =isset($this->m4is_j613['destination'])? trim($this->m4is_j613['destination']): $this->m4is_p4219;
 $this->m4is_f16985 =isset($this->m4is_j613['format'])? strtolower(trim($this->m4is_j613['format'])): $this->m4is_f16985;
 $this->m4is_a1056 =isset($this->m4is_j613['function'])? strtolower(trim($this->m4is_j613['function'])): $this->m4is_a1056;
 $this->m4is_z75923 =isset($this->m4is_j613['mode'])? strtolower(trim($this->m4is_j613['mode'])): $this->m4is_z75923;
 $this->m4is_b96 =isset($this->m4is_j613['round'])? (int) $this->m4is_j613['round']: $this->m4is_b96;
 $this->m4is_f2937 =isset($this->m4is_j613['tagids'])&&!empty($this->m4is_j613['tagids'])? array_filter(explode(',', $this->m4is_j613['tagids'])): $this->m4is_f2937;
 $this->m4is_f16846 =isset($this->m4is_j613['value1'])? trim($this->m4is_j613['value1']): $this->m4is_f16846;
 $this->m4is_c269 =isset($this->m4is_j613['value2'])? trim($this->m4is_j613['value2']): $this->m4is_c269;
 $this->m4is_b96 =(float) $this->m4is_x3092($this->m4is_b96 );
 $this->m4is_f16846 =(float) $this->m4is_x3092($this->m4is_f16846 );
 $this->m4is_c269 =(float) $this->m4is_x3092($this->m4is_c269 );
 
}private 
function m4is_h067(){
$m4is_e0234 =[];
 if(!$this->m4is_h21895 ){
$m4is_e0234 ='Error:  No valid contact ID provided.';
 
}if(!is_numeric($this->m4is_f16846 )){
$m4is_e0234 =sprintf('Error:  No valid value provided for Value1 (%s).', $this->m4is_f16846 );
 
}if(!is_numeric($this->m4is_c269 )){
$m4is_e0234 =sprintf('Error:  No valid value provided for Value2 (%s).', $this->m4is_c269 );
 
}if(!in_array($this->m4is_p4219, $this->m4is_f69781 )){
$m4is_e0234 ='Error:  No valid destination contact field name provided.';
 
}if(!in_array($this->m4is_a1056, ['subtract', 'add', 'max', 'min', 'multiply', 'divide', 'random' ])){
$m4is_e0234 =sprintf('Error:  Invalid operation/function provided: %s', $this->m4is_a1056 );
 
}if(!in_array($this->m4is_z75923, ['pure', 'credit' ])){
$m4is_e0234 =sprintf('Error:  Invalid mode provided: %s', $this->m4is_z75923 );
 
}if($this->m4is_a1056 == 'divide' &&$this->m4is_c269 == 0 ){
$m4is_e0234 ='Error:  Divide by zero.  Value2 cannot be zero.';
 
}$m4is_e0234 =array_filter($m4is_e0234 );
 if(count($m4is_e0234 )){
if($this->m4is_i8169 ){
foreach($m4is_e0234 as $m4is_q847 ){
echo sprintf("%s\n", $m4is_q847 );
 
}
}$this->m4is_a9058 =array_merge($this->m4is_a9058, $m4is_e0234 );
 header('HTTP/1.0 400 Bad Request');
 die();
 
}
}private 
function m4is_b61493(){
m4is_j586::m4is_x7134();
 if($this->m4is_i8169 ){
echo __LINE__, ' - Debug Mode Enabled<br>';
 printf('%d - %s: %s<br />', __LINE__, 'Contact ID', $this->m4is_h21895 );
 printf('%d - %s: %s<br />', __LINE__, 'Value 1', $this->m4is_f16846 );
 printf('%d - %s: %s<br />', __LINE__, 'Function', $this->m4is_a1056 );
 printf('%d - %s: %s<br />', __LINE__, 'Value 2', $this->m4is_c269 );
 printf('%d - %s: %s<br />', __LINE__, 'Destination', $this->m4is_p4219 );
 printf('%d - %s: %s<br />', __LINE__, 'Precision', $this->m4is_b96 );
 printf('%d - %s: %s<br />', __LINE__, 'Formatting', $this->m4is_f16985 );
 printf('%d - %s: %s<br />', __LINE__, 'Mode', $this->m4is_z75923 );
 
}
}private 
function m4is_s490(){
if(!empty($this->m4is_p4219 )){
 m4is_p40::m4is_x6560($this->m4is_h21895, [$this->m4is_p4219 =>$this->m4is_u6591], true );
  if($this->m4is_i8169 ){
printf('%d - Contact ID: %d --  %s: %s<br />', __LINE__, $this->m4is_h21895, $this->m4is_p4219, $this->m4is_u6591 );
 
}if($this->m4is_i8169 )echo __LINE__, " - Queued update to contact record\n";
 $m4is_c28 ="Value1 = {$this->m4is_f16846
} {$this->m4is_a1056
} {$this->m4is_c269
} = {$this->m4is_u6591
}\n" . "Destination = {$this->m4is_p4219
}";
 $this->m4is_o0379 =m4is_q62395::m4is_v729($this->m4is_h21895, 'httppost', "Math\n" . $m4is_c28 );
 
}
} private 
function m4is_u17026(){
if($this->m4is_a1056 == 'add' ){
$this->m4is_u6591 =$this->m4is_f16846 + $this->m4is_c269;
 
}elseif($this->m4is_a1056 == 'divide' &&$this->m4is_c269 != 0 ){
$this->m4is_u6591 =$this->m4is_f16846 / $this->m4is_c269;
 
}elseif($this->m4is_a1056 == 'max' ){
$this->m4is_u6591 =max($this->m4is_f16846, $this->m4is_c269 );
 
}elseif($this->m4is_a1056 == 'min' ){
$this->m4is_u6591 =min($this->m4is_f16846, $this->m4is_c269 );
 
}elseif($this->m4is_a1056 == 'multiply' ){
$this->m4is_u6591 =$this->m4is_f16846 * $this->m4is_c269;
 
}elseif($this->m4is_a1056 == 'random' ){
$this->m4is_u6591 =mt_rand($this->m4is_f16846, $this->m4is_c269 );
 
}elseif($this->m4is_a1056 == 'subtract' ){
$this->m4is_u6591 =$this->m4is_f16846 - $this->m4is_c269;
 
}if($this->m4is_i8169 )printf("%d - Calculating: %s %s %s = %s\n", __LINE__, $this->m4is_f16846, $this->m4is_a1056, $this->m4is_c269, $this->m4is_u6591 );
 if($this->m4is_b96 ){
$this->m4is_u6591 =round($this->m4is_u6591, $this->m4is_b96 );
 if($this->m4is_i8169 )printf("%d - Rounded Result to (%s)\n", __LINE__, $this->m4is_u6591 );
 
}if($this->m4is_u6591 == (int) $this->m4is_u6591){
$this->m4is_u6591 =(int) $this->m4is_u6591;
 
}else{
$this->m4is_u6591 =(float) $this->m4is_u6591;
 
}if(!empty($this->m4is_f16985 )){
$this->m4is_u6591 =sprintf($this->m4is_f16985, $this->m4is_u6591 );
 if($this->m4is_i8169 )echo __LINE__, " - Formatted Result to {$this->m4is_u6591
}\n";
 
}return $this->m4is_u6591;
 
} private 
function m4is_h58661(){
if(isset($_GET['min'])){
$this->m4is_x74 =isset($this->m4is_j613['min'])? $this->m4is_j613['min']: $this->m4is_x74;
 $this->m4is_x74 =(float) $this->m4is_x3092($this->m4is_x74 );
 if($this->m4is_u6591 < $this->m4is_x74 ){
if($this->m4is_i8169 )printf("%d - Result (%s) is less than min (%s)\n", __LINE__, $this->m4is_u6591, $this->m4is_x74 );
 if($this->m4is_z75923 == 'credit' ){
if($this->m4is_i8169 )printf("%d - Insufficient credit left.  Aborting transaction\n", __LINE__ );
 die();
 
}else{
$this->m4is_u6591 =$this->m4is_x74;
 
}
}
}if(isset($_GET['max'])){
$this->m4is_p48691 =isset($this->m4is_j613['max'])? $this->m4is_j613['max']: $this->m4is_p48691;
 $this->m4is_p48691 =(float) $this->m4is_x3092($this->m4is_p48691 );
 if($this->m4is_u6591 > $this->m4is_p48691 ){
if($this->m4is_i8169 )printf("%d - Result (%s) is greater than max (%s)\n", __LINE__, $this->m4is_u6591, $this->m4is_p48691 );
 if($this->m4is_z75923 == 'credit' ){
if($this->m4is_i8169 )printf("%d - Credit ceiling exceeded.  Aborting transaction\n", __LINE__ );
 die();
 
}else{
$this->m4is_u6591 =$this->m4is_p48691;
 
}
}
}
}private 
function m4is_s49(){
if(!empty($this->m4is_f2937 )){
m4is_r83::m4is_c26()->m4is_k98($this->m4is_f2937, $this->m4is_h21895 );
 if($this->m4is_i8169 ){
printf("%d - Tagged contact with %s\n", __LINE__, implode(', ', $this->m4is_f2937 ));
 
}
}if(!empty($this->m4is_v8723 )){
$m4is_e46082 =array_filter(explode(':', $this->m4is_v8723 ));
 $m4is_s63 =$m4is_e46082[0];
 $m4is_v8723 =$m4is_e46082[1];
 m4is_c69807::m4is_z3902($this->m4is_h21895, $m4is_v8723, $m4is_s63 );
 if($this->m4is_i8169 ){
printf("%d - Goal Achieved with %s\n", __LINE__, $this->m4is_v8723 );
 
}
}
}private 
function m4is_o5679(){
m4is_r83::m4is_c26()->m4is_s965('send_http_post');
 echo "Operation Completed\n";
 
} private 
function m4is_x3092($m4is_v586 ){
if(!is_null($m4is_v586 )&&in_array($m4is_v586, $this->m4is_f69781 )&&array_key_exists($m4is_v586, $this->m4is_i935 )){
$m4is_v586 =$this->m4is_i935[$m4is_v586 ];
 
}return $m4is_v586;
 
} 
}

