<?php

/**
 * Copyright (c) 2017-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83')||die();
 final 
class m4is_b789 {
static private $m4is_r1546;
 static private $m4is_h21895;
 static private $m4is_z59682;
 static private $m4is_r02639;
 static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r02639 =!m4is_s52::m4is_w74();
 self::$m4is_h21895 =self::$m4is_r1546->m4is_z56();
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 
}   static 
function m4is_w746($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'file_id' =>0, 'missing_text' =>'No such file.', 'text' =>'Click here to Download', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_o498 ='';
 $m4is_l62046['file_id']=(int) $m4is_l62046['file_id'];
 if($m4is_l62046['file_id']> 0 ){
$m4is_k98136 =self::$m4is_z59682->getDownloadUrl((int) $m4is_l62046['file_id']);
 
}if($m4is_k98136 > '' &&strpos($m4is_k98136, 'http' )!== false ){
$m4is_o498 ='<a href="' . $m4is_k98136 . '">' . htmlspecialchars($m4is_l62046['text']). '</a>';
 
}else{
$m4is_o498 =$m4is_l62046['missing_text'];
 
}if(!empty($m4is_l62046['capture'])){
$m4is_o498 =m4is_f61::m4is_f6513($m4is_o498, $m4is_l62046['capture']);
 
}return $m4is_o498;
 
}static 
function m4is_d04632($m4is_l62046 =[], $m4is_t09761 =null, $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'htmlattr' =>'', 'file_id' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['file_id']=(int) $m4is_l62046['file_id'];
 if($m4is_l62046['file_id']> 0 ){
$m4is_k98136 =self::$m4is_z59682->getDownloadUrl($m4is_l62046['file_id']);
 
}if($m4is_k98136 > '' &&strpos($m4is_k98136, 'http')!== FALSE){
$m4is_o498 =$m4is_k98136;
 
}else{
$m4is_o498 ='';
 
}return m4is_f61::m4is_u150(false, $m4is_o498, '', $m4is_l62046['capture'], $m4is_l62046['htmlattr'], '', '');
 
}static 
function m4is_j4578($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['contact_id' =>self::$m4is_h21895, 'filter' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['contact_id']=(int) $m4is_l62046['contact_id'];
 if(empty($m4is_l62046['contact_id'])){
return '';
 
}$m4is_i61 =m4is_t21664::m4is_m57($m4is_l62046['contact_id'], false );
 $m4is_o498 ='';
 foreach($m4is_i61 as $m4is_l9671 =>$m4is_e09 ){
$m4is_i61[$m4is_l9671]=array_change_key_case($m4is_e09, CASE_LOWER );
 
}if(!empty($m4is_l62046['filter'])){
if(is_array($m4is_i61)&&!empty($m4is_i61)){
foreach ($m4is_i61 as $m4is_l9671 =>$m4is_k86914){
if(stripos($m4is_k86914['filename'], $m4is_l62046['filter'])=== false){
unset($m4is_i61[$m4is_l9671]);
 
}
}
}
}unset($m4is_k86914);
 $m4is_y26 =count($m4is_i61)> 0;
 if($m4is_y26 &&empty($m4is_t09761)){
$m4is_t09761 ='<p>
			%%line%% - %%cycler%%<br />
			filesize: %%filebox.filesize%%<br />
			filebytes: %%filebox.filebytes%%<br />
			extension: %%filebox.extension%%<br />
			filename: %%filebox.filename%%<br />
			id: %%filebox.id%%<br/>
			public: %%filebox.public%%<br/>
			<a href="%%filebox.openlink%%">open</a><br/>
			<a href="%%filebox.downloadlink%%">Download</a><br/>
			</p>';
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_y26);
 $m4is_f086 =0;
 if($m4is_y26){
foreach ($m4is_i61 as $m4is_l9671 =>$m4is_k86914){
$m4is_f086++;
 $m4is_k60 =$m4is_t09761;
 if($m4is_f086 % 2){
$m4is_g73641 ='odd';
 
}else{
$m4is_g73641 ='even';
 
}$m4is_n860 =wp_nonce_url('?filebox_id=' . urlencode($m4is_k86914['id']). '&filename=' . urlencode($m4is_k86914['filename']), 'filebox_download::' . $m4is_k86914['id'], 'signature');
 $m4is_v7852 =wp_nonce_url('?filebox_id=' . urlencode($m4is_k86914['id']). '&filename=' . urlencode($m4is_k86914['filename']). '&viewable=1', 'filebox_download::' . $m4is_k86914['id'], 'signature');
 $m4is_k60 =str_ireplace('%%line%%', $m4is_f086, $m4is_k60);
 $m4is_k60 =str_ireplace('%%cycler%%', $m4is_g73641, $m4is_k60);
 $m4is_k60 =str_ireplace('%%filebox.filesize%%', self::$m4is_r1546->m4is_t61($m4is_k86914['filesize'], 0), $m4is_k60);
 $m4is_k60 =str_ireplace('%%filebox.filebytes%%', $m4is_k86914['filesize'], $m4is_k60);
 $m4is_k60 =str_ireplace('%%filebox.extension%%', $m4is_k86914['extension'], $m4is_k60);
 $m4is_k60 =str_ireplace('%%filebox.filename%%', $m4is_k86914['filename'], $m4is_k60);
 $m4is_k60 =str_ireplace('%%filebox.id%%', $m4is_k86914['id'], $m4is_k60);
 $m4is_k60 =str_ireplace('%%filebox.public%%', $m4is_k86914['public'], $m4is_k60);
 $m4is_k60 =str_ireplace('%%filebox.link%%', '?filebox_id=' . urlencode($m4is_k86914['id']). '&filename=' . urlencode($m4is_k86914['filename']), $m4is_k60);
 $m4is_k60 =str_ireplace('%%filebox.openlink%%', $m4is_v7852, $m4is_k60);
 $m4is_k60 =str_ireplace('%%filebox.downloadlink%%', $m4is_n860, $m4is_k60);
 $m4is_o498 .= do_shortcode($m4is_k60);
 
}
}else{
$m4is_o498 .= do_shortcode($m4is_t09761 );
 
}return $m4is_o498;
 
}static 
function m4is_v65($m4is_l62046 =[], $m4is_t09761 =null, $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return;
 
}if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return 'n/a';
 
}m4is_j586::m4is_x7134();
 $m4is_a173 ='';
 if(is_user_logged_in()){
$m4is_l9671 ='memberium::file_upload_msg::' . self::$m4is_r1546->m4is_x66();
 $m4is_a173 =trim(get_transient($m4is_l9671));
 delete_transient($m4is_l9671);
 
}if(empty($m4is_a173)){
$m4is_a173 =m4is_f58::m4is_c26()->m4is_q60356();
 
}return $m4is_a173;
 
} static 
function m4is_c796($m4is_l62046 =[], $m4is_t09761 =null, $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_v4821 =1;
 if(is_feed()){
return;
 
}m4is_j586::m4is_x7134();
 $m4is_y642 =['button_text' =>'Upload', 'contact_id' =>self::$m4is_h21895, 'error_msg' =>'Your file upload failed.', 'failure_actionsets' =>'', 'failure_goals' =>'', 'failure_tags' =>'', 'failure_url' =>'', 'maxsize' =>10000000, 'multiple' =>false, 'no_errors' =>false, 'rename' =>'', 'success_actionsets' =>'', 'success_goals' =>'', 'success_msg' =>'Your file upload completed.', 'success_tags' =>'', 'success_url' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['multiple']=m4is_f61::m4is_d8195($m4is_l62046['multiple'], false);
 $m4is_l62046['no_errors']=m4is_f61::m4is_d8195($m4is_l62046['no_errors'], false);
 $m4is_l62046['maxsize']=(int) wp_convert_hr_to_bytes($m4is_l62046['maxsize']);
 $m4is_g7103 ='';
 if(!$m4is_l62046['contact_id']){
return;
 
}if($m4is_l62046['maxsize']> 10000000 ||$m4is_l62046['maxsize']< 1){
$m4is_l62046['maxsize']=10000000;
 
}if($m4is_l62046['multiple']){
$m4is_g7103 =' multiple="multiple" ';
 
}$m4is_s019 =base64_encode(serialize(['contact_id' =>$m4is_l62046['contact_id'], 'error_msg' =>$m4is_l62046['error_msg'], 'failure_actionsets' =>$m4is_l62046['failure_actionsets'], 'failure_goals' =>$m4is_l62046['failure_goals'], 'failure_tags' =>$m4is_l62046['failure_tags'], 'failure_url' =>$m4is_l62046['failure_url'], 'maxsize' =>$m4is_l62046['maxsize'], 'no_errors' =>$m4is_l62046['no_errors'], 'rename' =>$m4is_l62046['rename'], 'success_actionsets' =>$m4is_l62046['success_actionsets'], 'success_goals' =>$m4is_l62046['success_goals'], 'success_msg' =>$m4is_l62046['success_msg'], 'success_tags' =>$m4is_l62046['success_tags'], 'success_url' =>$m4is_l62046['success_url'], ]));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_s019);
 $m4is_o498 ='<form id="upload_' . $m4is_v4821 . '" action="" method="POST" enctype="multipart/form-data">' . '<input type="hidden" name="memb_form_type" value="memb_filebox_upload">' . '<input type="hidden" name="params" value="' . $m4is_s019 . '">' . '<input type="hidden" name="signature" value="' . $m4is_o31859 . '">' . '<fieldset>' . '<input type="hidden" id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" value="' . $m4is_l62046['maxsize']. '" />' . '<div>' . '<label for="fileselect">Files to upload:</label>' . '<input type="file" id="fileselect_' . $m4is_v4821 . '" name="uploadedfiles[]" ' . $m4is_g7103 . ' required="required"/>' . '</div>' . '<div id="submitbutton_' . $m4is_v4821 . '">' . '<button type="submit">' . $m4is_l62046['button_text']. '</button>' . '</div>' . '</fieldset>'. '</form>';
 $m4is_v4821++;
 return $m4is_o498;
 
}
}

