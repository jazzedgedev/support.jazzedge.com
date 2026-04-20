<?php

/**
* Copyright (c) 2021-2024 David J Bullock
* Web Power and Light, LLC
* https://webpowerandlight.com
* support@webpowerandlight.com
*
*/


 class_exists('m4is_r83' )||die();
 
/**
 * Handles HTTP POST operations for Keap CRM integration within Memberium.
 *
 * This class provides routing and processing for HTTP POST requests, including
 * contact updates and country/region lookups based on postal codes.
 *
 * @package Memberium
 * @copyright 2021-2024 David J Bullock
 * @author David J Bullock
 * @license Proprietary
 */


 final 
class m4is_c86031 {
private $m4is_r1546;
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_i702();
 $this->m4is_m9748();
 
} private 
function m4is_i702(){
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
} private 
function m4is_m9748(){
$i2sdk_options =$this->m4is_r1546->get_i2sdk_options();
  if(isset($_GET['i4w_sync_user'])&&$_GET['i4w_sync_user']== substr($i2sdk_options['api_key'], 0, 6)){
$_GET['operation']='update-contact';
 
}$m4is_w69578 =trim(strtolower($_GET['operation']));
 $m4is_a73 =[    ];
 $m4is_a73 =apply_filters('memberium/httpppost_services/register', $m4is_a73);
 if(array_key_exists($m4is_w69578, $m4is_a73)){
$this->m4is_r1546->m4is_a6832(true);
 m4is_j586::m4is_x7134();
 add_action('i2sdk_http_post', $m4is_a73[$m4is_w69578], 10, 2 );
 
}
}  private 
function m4is_m5091(){
$m4is_c3749 =isset($_GET['debug'])? TRUE : FALSE;
 if($m4is_c3749)echo __LINE__, " - Debug Mode Enabled\n";
 $m4is_h21895 =isset($_POST['Id'])? (int) $_POST['Id']: 0;
 if(!$m4is_h21895){
return;
 
}$m4is_e32607 =[];
 $m4is_f26570 =['Country' =>'PostalCode', 'Country2' =>'PostalCode2', 'Country3' =>'PostalCode3', ];
 $m4is_o83705 =['United States' =>'/^\d{5}(-\d{4})?$/', 'Canada' =>'/^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$/', 'United Kingdom' =>'/^([A-PR-UWYZ0-9][A-HK-Y0-9][AEHMNPRTVXY0-9]?[ABEHMNPRVWXY0-9]? {1,2}[0-9][ABD-HJLN-UW-Z]{2}|GIR 0AA)$/', ];
 if($m4is_c3749){
echo __LINE__, " - Set Contact ID: {$m4is_h21895
}\n";
 echo __LINE__, " - Set Country: {$_POST['Country']
}\n";
 echo __LINE__, " - Set Country: {$_POST['Country2']
}\n";
 echo __LINE__, " - Set Country: {$_POST['Country3']
}\n";
 
} foreach ($m4is_f26570 as $m4is_l169=>$m4is_n6835){
if($_POST[$m4is_l169]== '' &&$_POST[$m4is_n6835]> ''){
foreach ($m4is_o83705 as $m4is_j58 =>$country_regex){
if(preg_match($country_regex, $_POST[$m4is_n6835])){
$_POST[$m4is_l169]=$m4is_j58;
 $m4is_e32607[$m4is_l169]=$m4is_j58;
 if($m4is_c3749)echo __LINE__, " - Set {$_POST[$m4is_l169]
} to {$m4is_j58
}\n";
 
}
}
}
}if($m4is_c3749)echo __LINE__, " - Updated Fields: ", print_r($m4is_e32607, TRUE), "\n";
 if(count($m4is_e32607)){
m4is_p40::m4is_x6560($m4is_h21895, $m4is_e32607, true);
  if($m4is_c3749)echo __LINE__, " - Updated Contact Id: ", print_r($m4is_h21895, TRUE), "\n";
 if($m4is_c3749)echo __LINE__, " - Synced Contact Id: ", print_r($m4is_h21895, TRUE), "\n";
 $this->m4is_r1546->m4is_i12($m4is_h21895);
 if($m4is_c3749){
echo __LINE__, " - Cleared Cache Namespace\n";
 echo __LINE__, " - Contact Updated\n";
 
}
}$this->m4is_r1546->m4is_s965('send_http_post');
 echo 'Operation Completed';
 
} private 
function m4is_a5926(){
$m4is_c3749 =isset($_GET['debug'])? true : false;
 if($m4is_c3749)echo __LINE__, " - Debug Mode Enabled\n";
 $m4is_h21895 =(int) $_POST['Id'];
 $m4is_e32607 =[];
 $m4is_u66435 =NULL;
 if($m4is_c3749)echo __LINE__, " - Set Contact ID: {$m4is_h21895
}\n";
 if($_POST['PostalCode']> ''){
switch ($_POST['Country']){
case 'Canada': switch (strtolower($_POST['PostalCode'], 0, 1)){
case 'a': $m4is_u66435 ='NL';
 break;
 case 'b': $m4is_u66435 ='NS';
 break;
 case 'c': $m4is_u66435 ='PE';
 break;
 case 'g': case 'h': case 'j': $m4is_u66435 ='QC';
 break;
 case 'k': case 'l': case 'm': case 'n': case 'p': $m4is_u66435 ='ON';
 break;
 case 's': $m4is_u66435 ='SK';
 break;
 case 't': $m4is_u66435 ='AB';
 break;
 case 'v': $m4is_u66435 ='BC';
 break;
 case 'x': $m4is_u66435 ='NT';
 break;
 case 'y': $m4is_u66435 ='YT';
 break;
 
}switch (strtolower($_POST['PostalCode'], 0, 3)){
case 'x0a': case 'x0b': case 'x0c': $m4is_u66435 ='NU';
 break;
 
}break;
 case 'United States': $m4is_a9072 =[['region'=>'MA', 'min'=>'01001', 'max'=>'05544'], ['region'=>'RI', 'min'=>'02801', 'max'=>'02940'], ['region'=>'NH', 'min'=>'00210', 'max'=>'00215'], ['region'=>'NH', 'min'=>'03031', 'max'=>'03897'], ['region'=>'ME', 'min'=>'03901', 'max'=>'04992'], ['region'=>'VT', 'min'=>'05001', 'max'=>'05907'], ['region'=>'CT', 'min'=>'06001', 'max'=>'06928'], ['region'=>'NJ', 'min'=>'07001', 'max'=>'08989'], ['region'=>'NY', 'min'=>'00501', 'max'=>'00544'], ['region'=>'NY', 'min'=>'06390', 'max'=>'06390'], ['region'=>'NY', 'min'=>'10001', 'max'=>'14925'], ['region'=>'PA', 'min'=>'15001', 'max'=>'19640'], ['region'=>'DE', 'min'=>'19701', 'max'=>'19980'], ['region'=>'DC', 'min'=>'20001', 'max'=>'20599'], ['region'=>'VA', 'min'=>'20101', 'max'=>'24658'], ['region'=>'MD', 'min'=>'20601', 'max'=>'21930'], ['region'=>'WV', 'min'=>'24701', 'max'=>'26886'], ['region'=>'NC', 'min'=>'27006', 'max'=>'28909'], ['region'=>'SC', 'min'=>'29001', 'max'=>'29945'], ['region'=>'GA', 'min'=>'30002', 'max'=>'39901'], ['region'=>'FL', 'min'=>'32004', 'max'=>'34997'], ['region'=>'AL', 'min'=>'35004', 'max'=>'36925'], ['region'=>'TN', 'min'=>'37010', 'max'=>'38589'], ['region'=>'MS', 'min'=>'38601', 'max'=>'39776'], ['region'=>'KY', 'min'=>'40003', 'max'=>'42788'], ['region'=>'OH', 'min'=>'43001', 'max'=>'45999'], ['region'=>'IN', 'min'=>'46001', 'max'=>'47997'], ['region'=>'MI', 'min'=>'48001', 'max'=>'49971'], ['region'=>'IA', 'min'=>'50001', 'max'=>'52809'], ['region'=>'WI', 'min'=>'53001', 'max'=>'54990'], ['region'=>'MN', 'min'=>'55001', 'max'=>'56763'], ['region'=>'SD', 'min'=>'57001', 'max'=>'57799'], ['region'=>'ND', 'min'=>'58001', 'max'=>'58856'], ['region'=>'MT', 'min'=>'59001', 'max'=>'59937'], ['region'=>'IL', 'min'=>'60001', 'max'=>'62999'], ['region'=>'MS', 'min'=>'63001', 'max'=>'65899'], ['region'=>'KS', 'min'=>'66002', 'max'=>'67954'], ['region'=>'NE', 'min'=>'68001', 'max'=>'69367'], ['region'=>'NE', 'min'=>'68001', 'max'=>'69367'], ['region'=>'LA', 'min'=>'70001', 'max'=>'71497'], ['region'=>'AR', 'min'=>'71601', 'max'=>'72959'], ['region'=>'OK', 'min'=>'73001', 'max'=>'74966'], ['region'=>'TX', 'min'=>'73301', 'max'=>'88595'], ['region'=>'CO', 'min'=>'80001', 'max'=>'81658'], ['region'=>'WY', 'min'=>'82001', 'max'=>'83128'], ['region'=>'ID', 'min'=>'83201', 'max'=>'83888'], ['region'=>'UT', 'min'=>'84001', 'max'=>'84791'], ['region'=>'AZ', 'min'=>'85001', 'max'=>'86556'], ['region'=>'NM', 'min'=>'87001', 'max'=>'88441'], ['region'=>'NV', 'min'=>'88901', 'max'=>'89883'], ['region'=>'HI', 'min'=>'96701', 'max'=>'96898'], ['region'=>'OR', 'min'=>'97001', 'max'=>'97920'], ['region'=>'AK', 'min'=>'99501', 'max'=>'99950'], ['region'=>'WA', 'min'=>'98001', 'max'=>'99403'], ];
  break;
 
}
}if($m4is_c3749)echo __LINE__, " - Set Contact ID: {$m4is_h21895
}\n";
 if(count($m4is_e32607)){
$m4is_h21895 =m4is_p40::m4is_x6560($m4is_h21895, $m4is_e32607);
  $this->m4is_r1546->m4is_x4831($m4is_h21895);
 $this->m4is_r1546->m4is_i12($m4is_h21895);
  
}$this->m4is_r1546->m4is_s965('send_http_post');
 echo 'Operation Completed';
 
}
}

