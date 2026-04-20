<?php
 class_exists('m4is_r83' )||die();
  final 
class m4is_a561 {
private $m4is_a9058 =[];
 private $m4is_r1546;
 private $m4is_i935 =[];
 private $m4is_h21895 =0;
 private $m4is_i8169 =false;
 private $m4is_f4930 ='';
 private $m4is_j613 =[];
 private $m4is_o0379 =0;
 private $m4is_m5907 =[];
 private $m4is_c53 =0;
 private $m4is_k40 =[];
 private $m4is_l17096 =null;
 private $m4is_f087 =0;
  static 
function m4is_z95(array $m4is_j613 =[], array $m4is_m5907 =[]): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self($m4is_j613, $m4is_m5907 );
 
}private 
function __construct(array $m4is_j613, array $m4is_m5907 ){
ini_set('display_errors', 1 );
 $this->m4is_r1546 =m4is_r83::m4is_c26();
 $m4is_j613 =empty($m4is_j613 )? $_GET : $m4is_j613;
 $m4is_m5907 =empty($m4is_m5907 )? $_POST : $m4is_m5907;
 m4is_j586::m4is_x7134();
 $this->m4is_q97523($m4is_j613, $m4is_m5907 );
 $this->m4is_i082();
 $this->m4is_h067();
 $this->m4is_c80();
 $this->m4is_v1694();
 $this->m4is_r68417();
 $this->m4is_e476();
 $this->m4is_r8746();
 $this->m4is_m72();
 m4is_q82::m4is_d59($this->m4is_f087 );
 
}
function __destruct(){
$m4is_z8419 =microtime(true )- $this->m4is_c53;
 $m4is_a173 =sprintf("Completed Contact Update HTTP POST.  Processing time: %0.3f seconds\n", $m4is_z8419 );
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
 
}private 
function m4is_i082(){
ksort($this->m4is_m5907 );
 $this->m4is_i935 =$this->m4is_m5907;
 $this->m4is_h21895 =isset($this->m4is_i935['Id'])? (int) $this->m4is_i935['Id']: $this->m4is_h21895;
 $this->m4is_i8169 =isset($this->m4is_j613['debug'])? $this->m4is_s1570($this->m4is_j613['debug'], false ): false;
 $this->m4is_f4930 =isset($this->m4is_m5907['Email'])? $this->m4is_m5907['Email']: '';
 $this->m4is_k40 =empty($this->m4is_j613['sync'])? []: array_filter(explode(',', strtolower(trim($this->m4is_j613['sync']))));
 $this->m4is_o0379 =m4is_q62395::m4is_v729($this->m4is_h21895, 'httppost', "Update Contact\n");
 if($this->m4is_i8169 ){
printf("Contact ID = %d\n", $this->m4is_h21895 );
 
}
} private 
function m4is_h067(){
$m4is_e0234 =[];
 if(!$this->m4is_h21895 ){
$m4is_e0234[]="Error: No Contact ID provided";
 
}if(empty($this->m4is_f4930 )){
$m4is_e0234[]="Error: No Email address provided";
 
}if(count($m4is_e0234 )){
$this->m4is_a9058 =array_merge($this->m4is_a9058, $m4is_e0234 );
 die();
 
}
}private 
function m4is_c80(){
$this->m4is_l17096 =m4is_r83::m4is_c26()->m4is_q93($this->m4is_f4930 );
 if(!$this->m4is_l17096 ){
$this->m4is_f087 =m4is_p40::m4is_i6158($this->m4is_h21895 );
 
}else{
$this->m4is_f087 =$this->m4is_l17096->ID;
 
}if($this->m4is_i8169 ){
printf("Matched Contact ID = %d to User ID = %d\n", $this->m4is_h21895, $this->m4is_f087 );
 $this->m4is_a9058[]=sprintf("Matched Contact ID = %d to User ID = %d", $this->m4is_h21895, $this->m4is_f087 );
 
}if($this->m4is_f087 == 0){
$this->m4is_a9058[]=sprintf("No user found for email %s or Contact ID %d", $this->m4is_f4930, $this->m4is_h21895 );
 exit;
 
}
}private 
function m4is_v1694(){
$this->m4is_r1546->m4is_v1694($this->m4is_i935 );
 m4is_q82::m4is_u687($this->m4is_f087 );
 $this->m4is_a9058[]='Contact Saved';
 
} private 
function m4is_r68417(){
if(!$this->m4is_h21895 ){
return;
 
}if(!$this->m4is_r1546->m4is_j498('settings', 'sync_affiliate', false )){
return;
 
}if(!in_array('affiliate', $this->m4is_k40 )){
return;
 
}$this->m4is_r1546->m4is_x17402($this->m4is_h21895 );
 m4is_q62395::m4is_x6835($this->m4is_o0379, sprintf("Syncing Affiliate Record for Contact %s\n", $this->m4is_f4930 ));
 if($this->m4is_i8169 ){
printf("Synced Affiliate record for %s\n", $this->m4is_f4930 );
 $m4is_a566 =m4is_z13097::m4is_n19($this->m4is_h21895 );
 if(is_array($m4is_a566 )){
foreach($m4is_a566 as $m4is_q523 =>$m4is_v586 ){
echo sprintf("%s = '%s'\n", $m4is_q523, $m4is_v586 );
 
}
}else{
echo 'No Affiliate Records Found';
 
}
}
}   private 
function m4is_e476(){
if(!$this->m4is_h21895 ){
return;
 
}if(!$this->m4is_r1546->m4is_j498('settings', 'sync_ecommerce', false )){
return;
 
}if(!in_array('invoices', $this->m4is_k40 )){
return;
 
}
} private 
function m4is_r8746(){
if(!$this->m4is_h21895 ){
return;
 
}if(!$this->m4is_r1546->m4is_j498('settings', 'sync_ecommerce', false )){
return;
 
}if(!in_array('creditcards', $this->m4is_k40 )){
return;
 
}
} private 
function m4is_m72(): void {
if(!$this->m4is_h21895 ){
return;
 
}if(!$this->m4is_r1546->m4is_j498('settings', 'sync_ecommerce', false )){
return;
 
}if(!in_array('creditcards', $this->m4is_k40 )){
return;
 
}
} private 
function m4is_s1570($m4is_v586, bool $m4is_n246 =false ): bool {
$m4is_v586 =strtolower(substr(trim($m4is_v586 ), 0, 1 ));
 return in_array($m4is_v586, ['y', 't', '1' ])? true : (in_array($m4is_v586, ['n', 'f', '0' ])? false : $m4is_n246 );
 
}
}

