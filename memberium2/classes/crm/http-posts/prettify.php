<?php
 class_exists('m4is_r83' )||die();
  final 
class m4is_d69852 {
private $m4is_a9058 =[], $m4is_t57 =[], $m4is_i935 =[], $m4is_h21895 =0, $m4is_f69781 =[], $m4is_j613 =[], $m4is_v8723 ='', $m4is_i8169 =false, $m4is_o0379 =null, $m4is_m5907 =[], $m4is_u398 =false, $m4is_c53 =0, $m4is_f2937 =[], $m4is_o66198 =[];
 static 
function m4is_z95(array $m4is_j613 =[], array $m4is_m5907 =[]): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self($m4is_j613, $m4is_m5907 );
 
}private 
function __construct(array $m4is_j613, array $m4is_m5907 ){
ini_set('display_errors', 1 );
 $m4is_j613 =empty($m4is_j613 )? $_GET : $m4is_j613;
 $m4is_m5907 =empty($m4is_m5907 )? $_POST : $m4is_m5907;
 $this->m4is_c53 =isset($_SERVER['REQUEST_TIME_FLOAT'])? $_SERVER['REQUEST_TIME_FLOAT']: microtime(true );
 $this->m4is_j613 =$m4is_j613;
 $this->m4is_m5907 =$m4is_m5907;
 $this->m4is_q97523();
 $this->m4is_i082();
 $this->m4is_a975();
 $this->m4is_s580();
 $this->m4is_n52761();
 $this->m4is_x6583();
 $this->m4is_f389();
 $this->m4is_m4863();
 $this->m4is_r8639();
 $this->m4is_x6560();
 $this->m4is_s49();
 
}
function __destruct(){
$m4is_z8419 =microtime(true )- $this->m4is_c53;
 $m4is_a173 =sprintf("Completed Prettify Contact HTTP POST.  Processing time: %0.3f seconds\n", $m4is_z8419 );
 $this->m4is_a9058[]=$m4is_a173;
 $this->m4is_a9058 =array_filter($this->m4is_a9058 );
 if($this->m4is_o0379 &&!empty($this->m4is_a9058 )){
m4is_q62395::m4is_x6835($this->m4is_o0379, implode("\n", $this->m4is_a9058 ));
 
}if($this->m4is_i8169 ){
echo $m4is_a173;
 
}
}private 
function m4is_q97523(){
$this->m4is_f69781 =m4is_c69807::m4is_f5248('Contact', false );
 $this->m4is_t57 =$this->m4is_t4267();
 
}private 
function m4is_i082(){
ksort($this->m4is_m5907 );
 $this->m4is_i935 =$this->m4is_m5907;
 $this->m4is_h21895 =isset($this->m4is_i935['Id'])? (int) $this->m4is_i935['Id']: $this->m4is_h21895;
 $this->m4is_i8169 =isset($this->m4is_j613['debug']);
 $this->m4is_u398 =isset($this->m4is_j613['noaccents']);
 $this->m4is_f2937 =isset($this->m4is_j613['tagids'])&&!empty($this->m4is_j613['tagids'])? array_filter(explode(',', $this->m4is_j613['tagids'])): $this->m4is_f2937;
 
} private 
function m4is_a975(){
foreach ($this->m4is_i935 as $m4is_r637 =>$m4is_v586 ){
$m4is_v586 =trim($m4is_v586 );
 if($m4is_v586 > '' &&in_array($m4is_r637, $this->m4is_t57 )){
$this->m4is_o66198[$m4is_r637 ]=$m4is_v586;
 
}else{
unset($this->m4is_i935[$m4is_r637 ]);
 
}
}if($this->m4is_i8169 ){
echo __LINE__, "\n";
 echo print_r($this->m4is_i935, true ), "\n";
 
}
}private 
function m4is_s580(){
if(!$this->m4is_u398 ){
return;
 
}foreach ($this->m4is_o66198 as $m4is_r637 =>$m4is_v586 ){
$this->m4is_o66198[$m4is_r637 ]=remove_accents($m4is_v586 );
 
}
}private 
function m4is_n52761(){
$m4is_f69781 =$this->m4is_x5636();
 foreach ($m4is_f69781 as $m4is_r637 ){
if(isset($this->m4is_o66198[$m4is_r637 ])){
$this->m4is_o66198[$m4is_r637 ]=strtolower($this->m4is_o66198[$m4is_r637 ]);
 
}
}
}private 
function m4is_x6583(){
$m4is_f69781 =$this->m4is_e71266();
 foreach ($m4is_f69781 as $m4is_r637 ){
if(!empty($this->m4is_o66198[$m4is_r637 ])){
$this->m4is_o66198[$m4is_r637 ]=ucwords(strtolower($this->m4is_o66198[$m4is_r637 ]));
 
}
}
}private 
function m4is_f389(){
$m4is_f69781 =$this->m4is_c66();
 foreach ($m4is_f69781 as $m4is_r637 ){
if(!empty($this->m4is_o66198[$m4is_r637 ])){
$this->m4is_o66198[$m4is_r637 ]=strtoupper($this->m4is_o66198[$m4is_r637 ]);
 
}
}
}private 
function m4is_m4863(){
$m4is_f69781 =$this->m4is_n5246();
 foreach ($m4is_f69781 as $m4is_r637){
if(!empty($this->m4is_o66198[$m4is_r637 ])){
if(strlen($this->m4is_o66198[$m4is_r637 ])< 4){
$this->m4is_o66198[$m4is_r637 ]=strtoupper(strtolower(remove_accents($this->m4is_o66198[$m4is_r637 ])));
 
}else{
$this->m4is_o66198[$m4is_r637 ]=ucwords(strtolower($this->m4is_o66198[$m4is_r637 ]));
 
}
}
}
}private 
function m4is_r8639(){
foreach($this->m4is_o66198 as $m4is_r637 =>$m4is_v586 ){
if($this->m4is_i935[$m4is_r637 ]== $m4is_v586 ){
unset($this->m4is_o66198[$m4is_r637 ]);
 
}
}print_r($this->m4is_o66198 );
 
}private 
function m4is_x6560(){
if(empty($this->m4is_o66198 )){
return;
 
}m4is_p40::m4is_x6560($this->m4is_h21895, $this->m4is_o66198, true );
  
}private 
function m4is_s49(){
if(!empty($this->m4is_f2937 )){
m4is_r83::m4is_c26()->m4is_k98($this->m4is_f2937, $this->m4is_h21895 );
 if($this->m4is_i8169 ){
printf("%d - Tagged contact with %s\n", __LINE__, implode(', ', $this->m4is_f2937 ));
 
}
}if(!empty($this->m4is_v8723 )){
$m4is_e46082 =explode(':', $this->m4is_v8723 );
 $m4is_s63 =$m4is_e46082[0];
 $m4is_v8723 =$m4is_e46082[1];
 m4is_c69807::m4is_z3902($this->m4is_h21895, $m4is_v8723, $m4is_s63 );
 if($this->m4is_i8169 ){
printf("%d - Goal Achieved with %s\n", __LINE__, $this->m4is_v8723 );
 
}
}m4is_r83::m4is_c26()->m4is_s965('send_http_post');
 
}  private 
function m4is_t4267(): array {
static $m4is_f69781 =[];
 if(empty($m4is_f69781 )){
$m4is_f69781 =array_merge($this->m4is_x5636(), $this->m4is_e71266(), $this->m4is_n5246(), $this->m4is_c66());
 
}return $m4is_f69781;
 
} private 
function m4is_e71266(): array {
static $m4is_f69781 =[];
 if(empty($m4is_f69781 )){
$m4is_f69781 =apply_filters('memberium/httppost/prettify/fields/propercase', ['Address2Street1', 'Address2Street2', 'Address3Street1', 'Address3Street2', 'AssistantName', 'City', 'City2', 'City3', 'Company', 'Country', 'Country2', 'Country3', 'FirstName', 'JobTitle', 'LastName', 'MiddleName', 'Nickname', 'SpouseName', 'StreetAddress1', 'StreetAddress2', ]);
 
}return $m4is_f69781;
 
} private 
function m4is_c66(): array {
static $m4is_f69781 =[];
 if(empty($m4is_f69781 )){
$m4is_f69781 =apply_filters('memberium/httppost/prettify/fields/uppercase', ['PostalCode', 'PostalCode2', 'PostalCode3', ]);
 
}return $m4is_f69781;
 
} private 
function m4is_x5636(): array {
static $m4is_f69781 =[];
 if(empty($m4is_f69781 )){
$m4is_f69781 =apply_filters('memberium/httppost/prettify/fields/lowercase', ['Email', 'EmailAddress2', 'EmailAddress3', ]);
 
}return $m4is_f69781;
 
} private 
function m4is_n5246(): array {
static $m4is_f69781 =[];
 if(empty($m4is_f69781 )){
$m4is_f69781 =apply_filters('memberium/httppost/prettify/fields/states', ['State', 'State2', 'State3', ]);
 
}return $m4is_f69781;
 
} 
}

