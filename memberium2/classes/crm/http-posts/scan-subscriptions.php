<?php
 class_exists('m4is_r83' )||die();
  final 
class m4is_g93124 {
 private $m4is_r9613 ='';
 private $m4is_i935 =[];
 private $m4is_m06 =[];
 private $m4is_y368 =[];
 private $m4is_h21895 =0;
 private $m4is_i8169 =false;
 private $m4is_i215 =0;
 private $m4is_j8236 =[];
 private $m4is_j613 =[];
 private $m4is_a9058 =[];
 private $m4is_o0379 ='';
 private $m4is_c53 =0;
 private $m4is_e32607 =[];
 private $m4is_m5907 =[];
  private $m4is_l251 =0;
 private $m4is_n283 ='';
 private $m4is_j124 =0;
 private $m4is_s07366 ='';
 private $m4is_i169 =0;
 private $m4is_t342 ='';
 private $m4is_r9312 =0;
 private $m4is_h023 =[];
 private $m4is_l543 =[];
 private $xyzzy =null;
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
 $this->m4is_c0461();
 $this->m4is_w37();
 
}
function __destruct(){
m4is_r83::m4is_c26()->m4is_s965('send_http_post');
 $m4is_z8419 =microtime(true )- $this->m4is_c53;
 $m4is_a173 =sprintf("Completed Scan Subscriptions HTTP POST.  Processing time: %0.3f seconds\n", $m4is_z8419 );
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
 $this->m4is_y368 =m4is_c69807::m4is_f5248('Contact', true );
 $this->m4is_r9613 =m4is_r83::m4is_c26()->m4is_i76('appname' );
 ksort($this->m4is_m5907 );
 
}private 
function m4is_i082(){
$this->m4is_i935 =$this->m4is_m5907;
 $this->m4is_h21895 =isset($this->m4is_i935['Id'])? (int) $this->m4is_i935['Id']: $this->m4is_h21895;
 $this->m4is_i8169 =isset($this->m4is_j613['debug'])? $this->m4is_s1570($this->m4is_j613['debug'], false ): false;
 $this->m4is_l251 =isset($this->m4is_j613['aactionset'])? (int) $this->m4is_j613['aactionset']: 0;
 $this->m4is_n283 =isset($this->m4is_j613['agoal'])? trim($this->m4is_j613['agoal']): '';
 $this->m4is_j124 =isset($this->m4is_j613['atagid'])? (int) $this->m4is_j613['atagid']: 0;
 $this->m4is_i169 =isset($this->m4is_j613['iactionset'])? (int) $this->m4is_j613['iactionset']: 0;
 $this->m4is_t342 =isset($this->m4is_j613['igoal'])? trim($this->m4is_j613['igoal']): '';
 $this->m4is_r9312 =isset($this->m4is_j613['itagid'])? (int) $this->m4is_j613['itagid']: 0;
 $this->m4is_l543 =isset($this->m4is_j613['subscriptionplans'])? array_filter(explode(',', $this->m4is_j613['subscriptionplans'])): [];
 $this->m4is_m06 =empty($this->m4is_j613['destfield'])? []: array_filter(explode(',', $this->m4is_j613['destfield']));
 $this->m4is_o0379 =m4is_q62395::m4is_v729($this->m4is_h21895, 'httppost', 'Scan Subscriptions' );
 $this->m4is_a9058[]=sprintf('Subscription Types = ', implode(',', $this->m4is_l543 ));
 if($this->m4is_i8169 ){
$m4is_p6925 ="%s - %s = %s\n";
 echo "Begin Scan Subscriptions\n";
 printf($m4is_p6925, __LINE__, 'Contact ID', $this->m4is_h21895 );
 printf($m4is_p6925, __LINE__, 'Subscription Types', implode(', ', $this->m4is_l543 ));
 printf($m4is_p6925, __LINE__, 'Active Actionset', $this->m4is_l251 );
 printf($m4is_p6925, __LINE__, 'Active Goal', $this->m4is_n283 );
 printf($m4is_p6925, __LINE__, 'Active Tag', $this->m4is_j124 );
 printf($m4is_p6925, __LINE__, 'Inactive Actionset', $this->m4is_i169 );
 printf($m4is_p6925, __LINE__, 'Inactive Goal', $this->m4is_t342 );
 printf($m4is_p6925, __LINE__, 'Inactive Tag', $this->m4is_r9312 );
 
}
}private 
function m4is_h067(){
$m4is_e0234 =[];
 $m4is_m02983 =[];
 if(!$this->m4is_h21895 ){
$m4is_e0234[]='Error:  Invalid Keap contact ID';
 
}foreach($this->m4is_m06 as $m4is_l9671 =>$m4is_r637 ){
if(!in_array($m4is_r637, $this->m4is_y368 )){
$m4is_m02983[]=sprintf("Warning: %s is not a valid target field.", $m4is_r637 );
 unset($this->m4is_m06[$m4is_l9671]);
 
}
}if(empty($this->m4is_m06 )){
$m4is_e0234[]="Error: No valid target fields provided.";
 
}if(!empty($m4is_e0234 )){
if($this->m4is_i8169 ){
echo implode("\n", array_merge($m4is_e0234, $m4is_m02983 )), "\n";
 printf("%d - Finished MakePass at %s\n", __LINE__, microtime(true ));
 
}$this->m4is_a9058 =array_merge($this->m4is_a9058, $m4is_e0234 );
 exit;
 
}
}private 
function m4is_c0461(){
$m4is_h3647 =['BillingCycle', 'EndDate', 'Frequency', 'Id', 'LastBillDate', 'NextBillDate', 'PaidThruDate', 'StartDate', 'Status', 'SubscriptionPlanId' ];
 $m4is_v76912 =['contactId' =>$this->m4is_h21895 ];
 $this->m4is_h023 =m4is_c69807::m4is_o986('RecurringOrder', 1000, 0, $m4is_v76912, $m4is_h3647 );
 if(!is_array($this->m4is_h023 )){
$this->m4is_a9058[]=sprintf('Error:  Unable to retrieve recurring orders for contact %d', $this->m4is_h21895 );
 
}if(empty($this->m4is_h023 )){
$this->m4is_a9058[]=sprintf('Notice:  No recurring orders found' );
 exit;
 
}
}private 
function m4is_w37(){
$m4is_f69781 =['EndDate', 'LastBillDate', 'PaidThruDate', 'StartDate', 'NextBillDate' ];
  foreach ($this->m4is_h023 as $m4is_y362 ){
if($m4is_y362['Status']== 'Inactive' ){
if(empty($this->m4is_l543 )||in_array($m4is_y362['SubscriptionPlanId'], $this->m4is_l543 )){
if($this->m4is_i8169 ){
printf("%d - Found Matching Inactive Subscription ID: %d, Type: %d\n", __LINE__, $m4is_y362['Id'], $m4is_y362['SubscriptionPlanId']);
 
}foreach($m4is_f69781 as $m4is_r637 ){
$m4is_y362[$m4is_r637 ]=empty($m4is_y362[$m4is_r637 ])? '' : $m4is_y362[$m4is_r637 ];
 
}$this->m4is_s07366 =max($m4is_y362['EndDate'], $m4is_y362['LastBillDate'], $m4is_y362['PaidThruDate'], $m4is_y362['StartDate'], $m4is_y362['NextBillDate'], $this->m4is_s07366 );
 
}
}else{
if($this->m4is_i8169 ){
printf("%d - Found Inactive Non-Matching Subscription ID: %d, Type: %d\n", __LINE__, $m4is_y362['Id'], $m4is_y362['SubscriptionPlanId']);
 
}
}
} foreach ($this->m4is_h023 as $m4is_y362 ){
if($m4is_y362['Status']== 'Active' ){
if(empty($this->m4is_l543 )||in_array($m4is_y362['SubscriptionPlanId'], $this->m4is_l543 )){
$this->m4is_s07366 ='';
 if($this->m4is_i8169 ){
printf("%d - Found Matching Active Subscription ID: %d, Type: %d \n", __LINE__, $m4is_y362['Id'], $m4is_y362['SubscriptionPlanId']);
 
}
}else{
if($this->m4is_i8169 ){
printf("%d - Found Active Non-Matching Subscription ID: %d\n", __LINE__, $m4is_y362['Id'], $m4is_y362['SubscriptionPlanId']);
 
}
}
}
}foreach($this->m4is_m06 as $m4is_r637 ){
$m4is_e32607[$m4is_r637 ]=$this->m4is_s07366;
 
}if($this->m4is_i8169 ){
printf("%d - Expiration Date: %s", __LINE__, $this->m4is_s07366 );
 
}m4is_p40::m4is_x6560($this->m4is_h21895, $m4is_e32607 );
 
}private 
function m4is_o6048(){
 if($this->m4is_s07366 == '' ){
if($this->m4is_j124 <> 0 ){
m4is_r83::m4is_c26()->m4is_k98([$this->m4is_j124 ], $this->m4is_h21895);
 if($this->m4is_i8169 ){
printf("%d - Add/Remove Tag %d\n", __LINE__, $this->m4is_j124 );
 
}
}if(!empty($this->m4is_l251 )){
$m4is_u6591 =m4is_j4156::m4is_w4805($this->m4is_h21895, $this->m4is_l251 );
 if($this->m4is_i8169 ){
printf("%d - Running Actionset %d\n", __LINE__, $this->m4is_l251 );
 echo __LINE__, ' - ', print_r($m4is_u6591, true), "\n";
 
}
}if(!empty($this->m4is_n283 )){
m4is_c69807::m4is_z3902($this->m4is_h21895, $this->m4is_n283 );
 if($this->m4is_i8169 ){
printf("%d - Achieving Goal %s\n", __LINE__, $this->m4is_n283 );
 
}
}
} else{
if($this->m4is_r9312 <> 0 ){
m4is_r83::m4is_c26()->m4is_k98([$this->m4is_r9312 ], $this->m4is_h21895);
 if($this->m4is_i8169 ){
printf("%d - Add/Remove Tag %d\n", __LINE__, $this->m4is_r9312 );
 
}
}if(!empty($this->m4is_i169 )){
$m4is_u6591 =m4is_j4156::m4is_w4805($this->m4is_h21895, $this->m4is_i169 );
 if($this->m4is_i8169 ){
printf("%d - Running Actionset %d\n", __LINE__, $this->m4is_i169 );
 echo __LINE__, ' - ', print_r($m4is_u6591, true), "\n";
 
}
}if(!empty($this->m4is_t342 )){
m4is_c69807::m4is_z3902($this->m4is_h21895, $this->m4is_t342 );
 if($this->m4is_i8169 ){
printf("%d - Achieving Goal %s\n", __LINE__, $this->m4is_t342 );
 
}
}
}
}private 
function m4is_o8570($m4is_v586 ){
return $m4is_v586 ? 'Yes' : 'No';
 
}private 
function m4is_s1570($m4is_v586, bool $m4is_n246 =false ): bool {
$m4is_v586 =strtolower(substr(trim($m4is_v586 ), 0, 1 ));
 return in_array($m4is_v586, ['y', 't', '1' ])? true : (in_array($m4is_v586, ['n', 'f', '0' ])? false : $m4is_n246 );
 
} 
}

