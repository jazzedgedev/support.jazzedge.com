<?php
 class_exists('m4is_r83')||die();
  final 
class m4is_c239 {
private $m4is_r1546;
 private $m4is_h21895 =0;
 private $m4is_d836 =true;
 private $m4is_i8169 =false;
 private $m4is_i215 =0;
 private $m4is_f4930 ='';
 private $m4is_h9061 =false;
 private $m4is_a618 =false;
 private $m4is_j613 =[];
 private $m4is_v8723 ='';
 private $m4is_a9058 =[];
 private $m4is_o0379 =0;
 private $m4is_b65 =false;
 private $m4is_m676 ='';
 private $m4is_r6234 ='';
 private $m4is_w938 =0;
 private $m4is_m5907 =[];
 private $m4is_l17096 =null;
 private $m4is_y193 ='';
 private $m4is_d696 =false;
 static 
function m4is_z95(array $m4is_j613 =[], array $m4is_m5907 =[]): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self($m4is_j613, $m4is_m5907 );
 
}private 
function __construct(array $m4is_j613 =[], array $m4is_m5907 =[]){
ini_set('display_errors', 1 );
 $m4is_j613 =empty($m4is_j613 )? $_GET : $m4is_j613;
 $m4is_m5907 =empty($m4is_m5907 )? $_POST : $m4is_m5907;
 m4is_j586::m4is_x7134();
 $this->m4is_q97523($m4is_j613, $m4is_m5907 );
 $this->m4is_n90186();
 $this->m4is_l52763();
 $this->m4is_h13524();
 $this->m4is_o96();
 $this->m4is_d04275();
 $this->m4is_n66();
 $this->m4is_w245();
 $this->m4is_h067();
 $this->m4is_i5364();
    $this->m4is_r17894();
 $this->m4is_z68();
 $this->m4is_a601();
 $this->m4is_g31();
 $this->m4is_l196();
 
}
function __destruct(){
$this->close_log();
  
} private 
function m4is_q97523(array $m4is_j613, array $m4is_m5907 ): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_j613 =$m4is_j613;
 $this->m4is_m5907 =$m4is_m5907;
 $this->m4is_r6234 =$this->m4is_r1546->m4is_j498('settings', 'password_field', 'Password' );
 $this->m4is_w938 =$this->m4is_r1546->m4is_j498('settings', 'min_password_length' );
 
}private 
function m4is_n90186(): void {
$this->m4is_i935 =$this->m4is_m5907;
 $this->m4is_h21895 =isset($this->m4is_i935['Id'])? (int) $this->m4is_i935['Id']: 0;
 $this->m4is_f4930 =isset($this->m4is_i935['Email'])? strtolower(trim($this->m4is_i935['Email'])): '';
 $this->m4is_m676 =isset($this->m4is_i935[$this->m4is_r6234 ])? trim($this->m4is_i935[$this->m4is_r6234 ]): '';
 
}private 
function m4is_l52763(): void {
$this->m4is_d836 =isset($this->m4is_j613['adduser'])? $this->m4is_s1570($this->m4is_j613['adduser'], true ): true;
 $this->m4is_i8169 =isset($this->m4is_j613['debug'])? $this->m4is_s1570($this->m4is_j613['debug'], false ): false;
 $this->m4is_i215 =empty($this->m4is_j613['delay'])? 0 : (int) $this->m4is_j613['delay'];
 $this->elv_exists_tag =isset($this->m4is_j613['exists_tag'])? (int) $this->m4is_j613['exists_tag']: 0;
 $this->m4is_v8723 =empty($this->m4is_j613['goal'])? '' : $this->m4is_j613['goal'];
 $this->m4is_b65 =isset($this->m4is_j613['overwrite'])? $this->m4is_s1570($this->m4is_j613['overwrite'], false ): false;
 $this->m4is_k40 =empty($this->m4is_j613['sync'])? []: array_filter(explode(',', strtolower(trim($this->m4is_j613['sync']))));
 $this->m4is_f2937 =empty($this->m4is_j613['tagids'])? []: array_filter(explode(',', $this->m4is_j613['tagids']));
 $this->m4is_d696 =isset($this->m4is_j613['verbose'])? $this->m4is_s1570($this->m4is_j613['verbose'], false ): false;
 
}private 
function m4is_h13524(){
$this->m4is_o0379 =m4is_q62395::m4is_v729($this->m4is_h21895, 'httppost', 'MakePass' );
 $this->m4is_a9058[]=sprintf("Contact ID = %d, Email = %s", $this->m4is_h21895, $this->m4is_f4930 );
 $this->m4is_a9058[]=sprintf("AddUser = %s, OverWrite = %s, Sync = %s, Tags = %s", $this->m4is_o8570($this->m4is_d836 ), $this->m4is_o8570($this->m4is_b65 ), implode(',', $this->m4is_k40 ), implode(',', $this->m4is_f2937 ));
 $this->m4is_k3269([__LINE__ . " - Begin MakePass at " . microtime(true), __LINE__ . " - Debug Mode Enabled", __LINE__ . " - Contact ID = " . $this->m4is_h21895, __LINE__ . " - Email Address = " . $this->m4is_f4930, __LINE__ . " - Password Field = " . $this->m4is_r6234, __LINE__ . " - Password Value = " . empty($this->m4is_i935[$this->m4is_r6234 ])? 'Empty' : 'Populated', __LINE__ . " - AddUser = " . $this->m4is_o8570($this->m4is_d836 ), __LINE__ . " - OverWrite = " . $this->m4is_o8570($this->m4is_b65 ), __LINE__ . " - Delay = " . $this->m4is_i215, __LINE__ . " - Goal = " . $this->m4is_v8723, __LINE__ . " - Sync Flags = " . implode(', ', $this->m4is_k40 ), __LINE__ . " - Tag IDs = " . implode(', ', $this->m4is_f2937 ), __LINE__ . " - Exists Tag = " . $this->elv_exists_tag, ]);
 if($this->m4is_i8169 ){
if($this->m4is_d696 ){
foreach($this->m4is_j613 as $m4is_l9671 =>$m4is_v586 ){
echo __LINE__, " - GET[{$m4is_l9671
}] = ", $m4is_v586, "\n";
 
}
}
}
} private 
function m4is_o96(): void {
$this->m4is_l17096 =get_user_by('email', $this->m4is_f4930 );
 if(!$this->m4is_l17096 ){
$this->m4is_k3269(__LINE__ . " - No user found with email '{$this->m4is_f4930
}'" );
 return;
 
}$m4is_a173 =sprintf("User '%s' found in WordPress database.", $this->m4is_l17096->user_email );
 $this->m4is_a9058[]=$m4is_a173;
 $this->m4is_k3269(__LINE__ . " - {$m4is_a173
}" );
 
} private 
function m4is_d04275(){
if(!$this->m4is_l17096 ){
return;
 
}if(user_can($this->m4is_l17096, 'manage_options' )){
$m4is_e0234[]='Error:  Contact email matches an admin account.';
 
}if(!empty($m4is_e0234 )){
$m4is_e0234[]='Aborting account creation.';
 if($this->m4is_i8169 ){
echo implode("\n", $m4is_e0234 ), "\n";
 printf("%d - Finished MakePass at %s\n", __LINE__, microtime(true ));
 
}$this->m4is_a9058 =array_merge($this->m4is_a9058, $m4is_e0234 );
 $this->close_log();
 die();
 
}
} private 
function m4is_n66(){
if($this->m4is_b65 == true &&$this->m4is_l17096 == false ){
return;
 
}if($this->m4is_r6234 !== 'Password' ){
return;
 
}if(!empty($this->m4is_i935[$this->m4is_r6234])){
return;
 
}$m4is_a89 =['Password' ];
 $m4is_i935 =m4is_p40::m4is_t609($this->m4is_h21895, $m4is_a89 );
 $this->m4is_i935['Password']=empty($m4is_i935['Password'])? '' : $m4is_i935['Password'];
 ksort($this->m4is_i935 );
 if($this->m4is_i8169)echo __LINE__, " - Retrieved Built-in Password Field from API\n";
 
} private 
function m4is_w245(){
$m4is_c64158 =true;
 $m4is_e0234 =[];
 if($this->m4is_b65 == false &&$this->m4is_l17096 ){
$m4is_e0234[]=__LINE__ . " - User '{$this->m4is_l17096->user_login
}' found in WordPress database.  Password generation skipped.";
 $m4is_c64158 =false;
 
}if($this->m4is_i935[$this->m4is_r6234]&&strlen($this->m4is_i935[$this->m4is_r6234])>= $this->m4is_w938 ){
$m4is_e0234[]=__LINE__ . " - Using supplied password.  Password passes length test.";
 $m4is_c64158 =false;
 
}if($this->m4is_b65 == true &&empty($this->m4is_i935[$this->m4is_r6234])){
$m4is_e0234[]=__LINE__ . " - Generating new password due to empty Password Field.";
 $m4is_c64158 =true;
 
}if($this->m4is_b65 == true &&$this->m4is_i935[$this->m4is_r6234]=== 'PASSWORD_PLACEHOLDER' ){
$m4is_e0234[]=__LINE__ . " - Generating new password due to PASSWORD_PLACEHOLDER.";
 $m4is_c64158 =true;
 
}if($this->m4is_i8169 ){
foreach($m4is_e0234 as $m4is_q847 ){
echo $m4is_q847 . "\n";
 
}
}$this->m4is_a618 =$m4is_c64158;
 
} private 
function m4is_h067(){
$m4is_e0234 =[];
 if(!$this->m4is_h21895 ){
$m4is_e0234[]='Error:  Invalid Keap contact ID';
 
}if(empty($this->m4is_f4930 )){
$m4is_e0234[]='Error:  Email address missing or invalid';
 
}if(!empty($this->m4is_i935[$this->m4is_r6234 ])){
if(strlen($this->m4is_i935[$this->m4is_r6234 ])< $this->m4is_w938 ){
$m4is_e0234[]='Error:  Supplied Password is too short';
 
}
}if(empty($this->m4is_i935['FirstName'])){
$m4is_e0234[]='Error:  First Name Missing.  The First Name field is required.';
 
}if(!empty($m4is_e0234 )){
$m4is_e0234[]='Aborting account creation.';
 if($this->m4is_i8169 ){
echo implode("\n", $m4is_e0234 ), "\n";
 printf("%d - Finished MakePass at %s\n", __LINE__, microtime(true ));
 
}$this->m4is_a9058 =array_merge($this->m4is_a9058, $m4is_e0234 );
 $this->close_log();
 die();
 
}
} private 
function m4is_i5364(): void {
$this->m4is_r1546->m4is_v1694($this->m4is_i935 );
 if($this->m4is_i8169 ){
echo __LINE__, " - Updating Local Contact Cache\n";
 if($this->m4is_d696 ){
foreach($this->m4is_i935 as $m4is_l9671 =>$m4is_v586 ){
echo __LINE__, " - Contact[{$m4is_l9671
}] = ", $m4is_v586, "\n";
 
}
}
}
}   private 
function m4is_r17894(){
$m4is_d83614 =true;
 if(!$this->m4is_l17096 ){
return;
 
}$m4is_d83614 =false;
 $this->m4is_a618 =false;
 if($this->m4is_b65 ){
$m4is_y09662 =sprintf("Warning: User Email %s found in WordPress database. Overwriting Password.", $this->m4is_l17096->user_email );
 $m4is_d83614 =true;
 $this->m4is_a618 =true;
 
}else{
$m4is_y09662 =sprintf('Warning: User Email %s found in WordPress database. Password creation skipped.', $this->m4is_l17096->user_email );
 $this->m4is_a618 =false;
 if($this->elv_exists_tag ){
m4is_k865::m4is_i28($this->m4is_h21895, [$this->elv_exists_tag ]);
 
}
}if($this->m4is_i8169 ){
echo __LINE__, ' - ', $m4is_y09662, "\n";
 
}$this->m4is_a9058[]=$m4is_y09662;
 
}private 
function m4is_z68(){
$m4is_c8216 =false;
 $m4is_m676 =empty($this->m4is_i935[$this->m4is_r6234 ])? '' : $this->m4is_i935[$this->m4is_r6234 ];
  if(!empty($m4is_m676 )){
$m4is_c8216 =true;
 $this->m4is_m676 =$m4is_m676;
 $this->m4is_a9058[]='Using Password from Keap Contact Record';
 if($this->m4is_i8169 )echo __LINE__, " - Using Password from Keap contact record\n";
 
}if(isset($m4is_m676 )&&$m4is_m676 == 'PASSWORD_PLACEHOLDER' ){
$m4is_c8216 =false;
 $this->m4is_b65 =true;
 $this->m4is_m676 ='';
 
}if($this->m4is_a618 ){
if($m4is_c8216 &&!$this->m4is_b65 ){
$this->m4is_a9058[]='Password Generation Disabled';
 $this->m4is_a618 =false;
 
}else{
$this->m4is_a618 =true;
 
}
}if($this->m4is_i8169 ){
printf("%d - Password Exists    = %s\n", __LINE__, $this->m4is_o8570($m4is_c8216 ));
 printf("%d - Password Overwrite = %s\n", __LINE__, $this->m4is_o8570($this->m4is_b65 ));
 printf("%d - Generate Password  = %s\n", __LINE__, $this->m4is_o8570($this->m4is_a618 ));
 printf("%d - Password Length    = %d\n", __LINE__, $this->m4is_w938 );
 
}
}private 
function m4is_a601(){
if(!$this->m4is_a618 ){
return;
 
} $this->m4is_m676 =$this->m4is_r1546->m4is_a601();
 if($this->m4is_i8169 ){
echo __LINE__, " - Password Generated: {$this->m4is_m676
}\n";
 
}$this->m4is_a9058[]="New password generated.";
 $this->m4is_i935[$this->m4is_r6234 ]=$this->m4is_m676;
 $updated_fields =[$this->m4is_r6234 =>$this->m4is_m676, ];
 $m4is_u6591 =m4is_p40::m4is_x6560($this->m4is_h21895, $updated_fields );
  $this->m4is_r1546->m4is_w05782($this->m4is_h21895, $this->m4is_i935 );
 if($this->m4is_i8169 ){
echo __LINE__, " - password_field = ", $this->m4is_r6234, "\n";
 echo __LINE__, " - Updated Password to {$this->m4is_m676
} \n";
 foreach($m4is_e32607 as $m4is_l9671 =>$m4is_v586 ){
echo __LINE__, " - Updated Field: {$m4is_l9671
} = {$m4is_v586
}\n";
 
}
}
}private 
function m4is_g31(){
$m4is_f087 =$this->m4is_r1546->m4is_f605($this->m4is_h21895, $this->m4is_m676 );
 if($this->m4is_i8169 ){
echo __LINE__, " - Contact ID #", $this->m4is_h21895, "\n";
 echo __LINE__, " - Password:  '", $this->m4is_m676, "'\n";
 if($m4is_f087 ){
echo __LINE__, " - User ID (", $m4is_f087, ") Added / Updated\n";
 
}else{
echo __LINE__, " - User Not Added / Updated\n";
 
}
}if($m4is_f087 ){
if(is_multisite()&&!is_user_member_of_blog($m4is_f087 )){
$m4is_s740 =get_current_blog_id();
 $m4is_y1662 =get_blog_option($m4is_s740, 'default_role', 'subscriber' );
 add_user_to_blog($m4is_s740, $m4is_f087, $m4is_y1662 );
 
}m4is_q82::m4is_u687($m4is_f087 );
 
}
}private 
function m4is_l196(){
if(!empty($this->m4is_f2937 )){
$this->m4is_r1546->m4is_k98($this->m4is_f2937, $this->m4is_h21895 );
 if($m4is_c3749){
echo __LINE__, " - Added Tags: ", implode(', ', $this->m4is_f2937 ), "\n";
 
}$this->m4is_a9058[]=sprintf('Added Tags ', implode(', ', $this->m4is_f2937 ));
 
}if(!empty($this->m4is_v8723 )){
 $m4is_d5472 =m4is_c69807::m4is_z3902($this->m4is_h21895, $this->m4is_v8723 );
 if($this->m4is_i8169 ){
printf("%d - Goal Achieved with %s\n", __LINE__, $this->m4is_v8723 );
 print_r($m4is_d5472 );
 
}
}if($this->m4is_i8169 ){
echo __LINE__, " - Updated Contact\n";
 echo __LINE__, " - Sleeping: {$this->m4is_i215
} seconds\n";
 
}$this->m4is_r1546->m4is_i12($this->m4is_h21895 );
 $this->m4is_r1546->m4is_s965('send_http_post');
 sleep($this->m4is_i215 );
 
}private 
function m4is_o8570($m4is_v586 ){
return $m4is_v586 ? 'Yes' : 'No';
 
}private 
function m4is_s1570($m4is_v586, bool $m4is_n246 =false ): bool {
$m4is_v586 =strtolower(substr(trim($m4is_v586 ), 0, 1 ));
 return in_array($m4is_v586, ['y', 't', '1' ])? true : (in_array($m4is_v586, ['n', 'f', '0' ])? false : $m4is_n246 );
 
} private 
function close_log(){
if($this->m4is_o0379 &&!empty($this->m4is_a9058 )){
m4is_q62395::m4is_x6835($this->m4is_o0379, implode("\n", $this->m4is_a9058 ));
 
}if($this->m4is_i8169 ){
printf("Exiting MakePass HTTP POST Process at %s\n", microtime(true ));
 
}
}private 
function m4is_k3269($m4is_a173 ): void {
if($this->m4is_i8169 ){
if(is_array($m4is_a173 )){
foreach($m4is_a173 as $m4is_t42917 ){
echo $m4is_t42917, "\n";
 
}return;
 
}echo $m4is_a173, "\n";
 
}
}
}

