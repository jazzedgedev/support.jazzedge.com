<?php

/**
 * Proprietary Software - All Rights Reserved
 *
 * This file is part of the Memberium plugin, which is proprietary software developed by Web Power and Light.
 * Unauthorized copying, distribution, or modification of this file, via any medium, is strictly prohibited.
 *
 * Copyright (c) 2012-2024 David J Bullock
 * Web Power and Light
 *
 * For licensing information, please contact Web Power and Light.
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_h30 {
     private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_h21895;
 private static $m4is_z59682;
 private static $m4is_r02639;
 private static $m4is_f4218;
  public static 
function m4is_c961(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname');
 self::$m4is_h21895 =self::$m4is_r1546->m4is_z56();
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_r02639 =!m4is_s52::m4is_w74();
 self::$m4is_f4218 ='memberium';
 m4is_j586::m4is_k751();
 
} public static 
function m4is_r927($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_b836 =false;
 $m4is_y642 =['backcharge' =>'yes', 'button_id' =>'memberium-add-payment-method-submit', 'button_style' =>'', 'button_text' =>'Add Card', 'debug' =>'no', 'default' =>'yes', 'logging' =>'no', 'plan_ids' =>'', 'redirect' =>$_SERVER['REQUEST_URI'], 'failure' =>$_SERVER['REQUEST_URI'], 'tag_id' =>0, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_i8169 =m4is_f61::m4is_d8195($m4is_l62046['debug'], false );
 if($m4is_b836 ){
if($m4is_i8169 ){
return '<p><strong style="color:red">Debug Notice:</strong>  This shortcode can only be used once per page.</p>';
 
}return '';
 
}$m4is_b836 =true;
 if(!self::$m4is_h21895 ){
if(current_user_can('manage_options' )){
return '<p><strong style="color:red">Admin Notice:</strong>  This shortcode does not work for admins.  Please login as a site member.</p>';
 
}if($m4is_i8169 ){
return '<p><strong style="color:red">Debug Notice:</strong>  This shortcode requires the current user to be linked to a Keap contact.</p>';
 
}return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_d9476 =trim($m4is_l62046['button_text']);
 $m4is_n524 =$m4is_l62046['button_id'];
 $m4is_p36 =$m4is_l62046['button_style'];
 $m4is_h21895 =self::$m4is_h21895;
 $m4is_f087 =get_current_user_id();
 $m4is_u897 =base64_encode(serialize(['contact_id' =>$m4is_h21895, 'backcharge' =>m4is_f61::m4is_d8195($m4is_l62046['backcharge'], true ), 'default' =>m4is_f61::m4is_d8195($m4is_l62046['default'], true ), 'logging' =>m4is_f61::m4is_d8195($m4is_l62046['logging'], false ), 'debug' =>m4is_f61::m4is_d8195($m4is_l62046['debug'], false ), 'plan_ids' =>$m4is_l62046['plan_ids'], 'redirect' =>$m4is_l62046['redirect'], 'failure' =>$m4is_l62046['failure'], 'tag_id' =>(int) $m4is_l62046['tag_id'], ]));
 $m4is_z61825 =m4is_c01675::m4is_q76215($m4is_f087 );
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u897 );
 $m4is_a27648 =wp_nonce_field('add_paymentmethod_' . $m4is_h21895, '_wpnonce', true, false);
 $m4is_x26675 =esc_attr($m4is_n524 );
 $m4is_z654 =esc_html($m4is_d9476 );
 $m4is_o498 =<<<HTMLCODE
			<div>
				<keap-payment-method id="keap-payment-method" key="{$m4is_z61825
}"></keap-payment-method>
			</div>
			<form id="memb_update_creditcard" name="memb_update_creditcard" action="" method="POST">
				<input type="hidden" name="payment_method_id" id="paymentMethodId" value="0">
				<input type="hidden" name="creditcard_id" id="creditCardId" value="0">
				<input type="hidden" name="payment_message" id="paymentMessage" value="">
				<input type="hidden" name="payment_response" id="paymentResponse" value="">
				<input type="hidden" name="memb_form_type" value="memb_add_payment_method">
				<input type="hidden" name="parameters" value="{$m4is_u897
}">
				<input type="hidden" name="signature" value="{$m4is_o31859
}">
				{$m4is_a27648
}
			</form>
			<button id="{$m4is_n524
}" style="{$m4is_p36
}" onclick="submitKeapForm()">{$m4is_d9476
}</button>

			<script src="https://payments.keap.page/lib/payment-method-embed.js"></script>
			<script>
				function submitKeapForm() {
					document.querySelector("#keap-payment-method").submit();
				}

				window.addEventListener('message', ({ data }) => {
					if (data.success) {
						if (typeof data.paymentMethodId !== 'undefined') {
							document.getElementById('paymentMethodId').value = data.paymentMethodId;
						}
						if (typeof data.creditCardId !== 'undefined') {
							document.getElementById('creditCardId').value = data.creditCardId;
						}
						if (typeof data.message !== 'undefined') {
							document.getElementById('paymentMessage').value = data.message;
						}

						document.getElementById('paymentResponse').value = JSON.stringify(data, null, 2);
						document.getElementById('memb_update_creditcard').submit();


					} else {
						// Handle error
					}
				});
			</script>
		HTMLCODE;
 return $m4is_o498;
 
}public static 
function m4is_r60796($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}if(!self::$m4is_h21895 ){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 $m4is_p1054 =m4is_c01675::m4is_w68604(self::$m4is_h21895 );
 $m4is_d895 =m4is_c01675::m4is_s04($m4is_p1054 );
 $m4is_l06283 =0;
 if(is_array($m4is_d895 )&&count($m4is_d895 )> 0){
$m4is_z81 =strtotime(date('Y-m-d' ));
 $m4is_r6654 =strtotime(date($m4is_d895['ExpirationYear']. '-' . ($m4is_d895['ExpirationMonth']+ 1). '-01' ));
 $m4is_l06283 =(int) (($m4is_r6654 - $m4is_z81 )/ 86400 );
 $m4is_l06283 =$m4is_l06283 < 0 ? 0 : $m4is_l06283;
 
}$m4is_o498 =$m4is_l06283;
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
} public static 
function m4is_i926($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'txtfmt' =>'', 'not' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_t265 =false;
 $m4is_l62046['not']=!empty($m4is_l62046['not']);
 if(stripos($m4is_v3458, '_no_')){
$m4is_l62046['not']=true;
 
}$m4is_t265 =(bool) count(m4is_c01675::m4is_w68604(self::$m4is_h21895 ));
 if($m4is_l62046['not']){
$m4is_t265 =!$m4is_t265;
 
}$m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_t265);
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
}public static 
function m4is_o93256($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'date_format' =>'', 'default' =>'', 'fields' =>'', 'txtfmt' =>'', 'separator' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!self::$m4is_h21895 ){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
  $m4is_p1054 =m4is_c01675::m4is_w68604(self::$m4is_h21895 );
 $m4is_d895 =m4is_c01675::m4is_s04($m4is_p1054 );
 $m4is_d895 =array_change_key_case($m4is_d895, CASE_LOWER);
 $m4is_n0216 =array_filter(array_map('strtolower', explode(',', $m4is_l62046['fields'])));
 $m4is_s12460 =is_array($m4is_n0216 )? count($m4is_n0216 ): 0;
 $m4is_v81756 =0;
 $m4is_o498 ='';
 unset($m4is_p1054);
  foreach ($m4is_n0216 as $m4is_r637){
$m4is_r637 =strtolower(trim($m4is_r637));
 if(isset($m4is_d895[$m4is_r637])){
$m4is_o076 =strtotime($m4is_d895[$m4is_r637]);
 if($m4is_l62046['date_format']== '' ||$m4is_o076 == 0){
$m4is_w86 =$m4is_d895[$m4is_r637];
 
}else{
$m4is_w86 =date($m4is_l62046['date_format'], strtotime($m4is_d895[$m4is_r637]));
 
}
}else{
$m4is_w86 =$m4is_l62046['default'];
 
}$m4is_o498 .= $m4is_w86;
 if($m4is_s12460 > 1){
$m4is_o498 .= $m4is_l62046['separator'];
 
}
}if($m4is_s12460 > 1 &&strlen($m4is_l62046['separator'])> 0){
$m4is_o498 =substr($m4is_o498, 0, -strlen($m4is_l62046['separator']));
 
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
}public static 
function m4is_b05($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'days' =>0, 'debug' =>false, 'subscription_ids' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!self::$m4is_h21895 ){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['debug']=m4is_f61::m4is_d8195($m4is_l62046['debug'], false );
 $m4is_l62046['days']=(int) $m4is_l62046['days'];
 $m4is_p1054 =m4is_c01675::m4is_w68604(self::$m4is_h21895 );
 $m4is_d895 =m4is_c01675::m4is_s04($m4is_p1054 );
 if(!empty($m4is_l62046['subscription_ids'])){

}else{
unset($m4is_p1054);
 $m4is_l06283 =0;
 if(is_array($m4is_d895 )&&count($m4is_d895 )> 0 ){
$m4is_z81 =strtotime(date('Y-m-d'));
 $m4is_r6654 =strtotime(date($m4is_d895['ExpirationYear']. '-' . $m4is_d895['ExpirationMonth']. '-28'));
 $m4is_l06283 =(int) (($m4is_r6654 - $m4is_z81)/ 86400);
 if($m4is_l06283 < 0){
$m4is_l06283 =0;
 
}
}$m4is_m60 =(boolean) ($m4is_l06283 < $m4is_l62046['days']);
 
}$m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_m60);
 if($m4is_l62046['txtfmt']> ''){
$m4is_o498 =m4is_f61::m4is_n05($m4is_o498, $m4is_l62046['txtfmt']);
 
}if($m4is_l62046['capture']> ''){
$m4is_o498 =m4is_f61::m4is_f6513($m4is_o498, $m4is_l62046['capture']);
 
} foreach($m4is_d895 as $m4is_l9671 =>$m4is_v586){
$m4is_o498 =str_ireplace('%%' . $m4is_l9671 . '%%', $m4is_v586, $m4is_o498);
 
}$m4is_o498 =str_ireplace('%%days_left%%', $m4is_l06283, $m4is_o498);
 return do_shortcode($m4is_o498);
 
}public static 
function m4is_x37($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['button_text' =>'Pay Now', 'date_format' =>'M d, Y', 'active' =>TRUE, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!self::$m4is_h21895 ){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_p1054 =m4is_c01675::m4is_w68604(self::$m4is_h21895);
 $m4is_f086 =0;
 $m4is_o498 ='';
 $m4is_i46 ='';
 if(trim($m4is_t09761)== ''){
$m4is_t09761 .= '<tr>';
 $m4is_t09761 .= '<td>%%card.type%%</td>';
 $m4is_t09761 .= '<td>xxxxxxxxxxxx%%card.last4%%</td>';
 $m4is_t09761 .= '<td>%%card.month.dropdown%% / ';
 $m4is_t09761 .= '%%card.year.dropdown%%</td>';
  $m4is_t09761 .= '</tr>';
 
}if(is_array($m4is_p1054)){
foreach ($m4is_p1054 as $m4is_d895){
$m4is_u076 =base64_encode(serialize(['creditcard_id' =>(int) $m4is_d895['Id'], ]));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u076);
 $m4is_f086++;
 $m4is_o498 .= '<form method="post" action="">';
 $m4is_i46 .= "<input type=\"hidden\" name=\"memb_form_type\" value=\"memb_list_creditcards_form\">";
 $m4is_o498 .= '<input type="hidden" name="parameters" value="' . $m4is_u076 . '">';
 $m4is_o498 .= '<input type="hidden" name="signature" value="' . $m4is_o31859 . '">';
 $m4is_o498 .= '<input type="hidden" name="Id" value="' . $m4is_d895['Id']. '">';
 if($m4is_f086 % 2){
$m4is_g73641 ='odd';
 
}else{
$m4is_g73641 ='even';
 
}$m4is_u58 ='<select name="ExpirationMonth">';
 for ($m4is_b3785 =1;
 $m4is_b3785 < 13;
 $m4is_b3785++ ){
$m4is_p893 =str_pad($m4is_b3785, 2, '0', STR_PAD_LEFT);
 $m4is_u58 .= '<option value ="' . $m4is_p893 . '" ' . ($m4is_p893 == $m4is_d895['ExpirationMonth']? ' selected="selected" ' : ''). '>' . $m4is_p893 . '</option>';
 
}$m4is_u58 .= '</select>';
 $m4is_f25891 ='<select name="ExpirationYear">';
 for ($m4is_b3785 =date('Y');
 $m4is_b3785 < (date('Y')+ 10);
 $m4is_b3785++ ){
$m4is_f25891 .= '<option value ="' . $m4is_b3785 . '" ' . ($m4is_b3785 == $m4is_d895['ExpirationYear']? ' selected="selected" ' : ''). '>' . $m4is_b3785 . '</option>';
 
}$m4is_f25891 .= '</select>';
 $m4is_k61785 ='<input type="submit" value="Delete" class="deletebutton">';
 $m4is_p6925 =str_ireplace('%%card.id%%', $m4is_d895['Id'], $m4is_t09761);
 $m4is_p6925 =str_ireplace('%%card.type%%', $m4is_d895['CardType'], $m4is_t09761);
 $m4is_p6925 =str_ireplace('%%card.last4%%', $m4is_d895['Last4'], $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%card.year%%', $m4is_d895['ExpirationYear'], $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%card.month%%', $m4is_d895['ExpirationMonth'], $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%card.month.dropdown%%', $m4is_u58, $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%card.year.dropdown%%', $m4is_f25891, $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%card.id%%', $m4is_d895['Id'], $m4is_p6925);
  $m4is_o498 .= $m4is_p6925;
 $m4is_o498 .= '</form>';
 
}
}return $m4is_o498;
 
} public static 
function m4is_j43185($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_m92735 =0;
 $m4is_m92735++;
 $m4is_y642 =['action_ids' =>'', 'addonly' =>'no', 'backcharge' =>'no', 'cardtypes' =>'American Express,Discover,MasterCard,Visa', 'debug' =>false, 'debug_ip' =>'', 'failureurl' =>$_SERVER['REQUEST_URI'], 'goals' =>'', 'ignoressl' =>'no', 'plan_ids' =>'', 'set_default' =>'yes', 'success_msg' =>'Your credit card was successfully updated.', 'successurl' =>$_SERVER['REQUEST_URI'], 'tag_ids' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!is_ssl()){
if(self::$m4is_r1546->m4is_v461()){
return '<p>Credit Card Update Form Disabled Due to Missing SSL.</p>';
 
}return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['addonly']=m4is_f61::m4is_d8195($m4is_l62046['addonly'], false);
 $m4is_l62046['backcharge']=m4is_f61::m4is_d8195($m4is_l62046['backcharge'], true);
 $m4is_l62046['debug']=m4is_f61::m4is_d8195($m4is_l62046['debug'], false);
 $m4is_l62046['ignoressl']=m4is_f61::m4is_d8195($m4is_l62046['ignoressl'], false);
 $m4is_l62046['set_default']=m4is_f61::m4is_d8195($m4is_l62046['set_default'], true);
 if(!self::$m4is_r1546->m4is_v461()){
if(self::$m4is_h21895 == 0){
return '';
 
}if((!is_ssl())&&(!$m4is_l62046['ignoressl'])){
return '';
 
}
}$m4is_x29143 ='memb_addupdate_creditcard-' . $m4is_m92735;
 $m4is_d9476 ='Add Card';
 $m4is_l62046['cardtypes']=array_filter(explode(',', $m4is_l62046['cardtypes']));
 $m4is_d895['Id']=0;
 if($m4is_l62046['addonly']== false){
 $m4is_p1054 =m4is_c01675::m4is_w68604(self::$m4is_h21895 );
 $m4is_d895 =end($m4is_p1054 );
 $m4is_d895 =is_array($m4is_d895 )? $m4is_d895 : [];
 $m4is_g7928 =['BillAddress1', 'BillAddress2', 'BillCity', 'BillCountry', 'BillState', 'BillZip', 'CardType', 'ExpirationMonth', 'ExpirationYear', 'FirstName', 'Id', 'Last4', 'LastName', 'NameOnCard', 'PhoneNumber', ];
 foreach ($m4is_g7928 as $m4is_l9671 ){
if(empty($m4is_d895[$m4is_l9671])){
$m4is_d895[$m4is_l9671]='';
 
}
}
}$m4is_a790 =m4is_q82::m4is_d6758(self::$m4is_r1546->m4is_x66(), 'keap', 'contact' );
 if($m4is_d895['Id']> 0){
 $m4is_u807 =$m4is_d895['Id'];
 $m4is_p80796 =$m4is_d895['FirstName'];
 $m4is_r27 =$m4is_d895['LastName'];
 $m4is_j694 ='************' . $m4is_d895['Last4'];
 $m4is_h2674 =$m4is_d895['CardType'];
 $m4is_i7468 =$m4is_d895['ExpirationMonth'];
 $m4is_b631 =$m4is_d895['ExpirationYear'];
 $m4is_x0719 =trim($m4is_d895['NameOnCard']);
 $m4is_u9528 =$m4is_d895['PhoneNumber'];
 $m4is_x965 =$m4is_d895['BillAddress1'];
 $m4is_e20 =$m4is_d895['BillAddress2'];
 $m4is_f16 =$m4is_d895['BillCity'];
 $m4is_a3165 =$m4is_d895['BillState'];
 $m4is_m79 =$m4is_d895['BillCountry'];
 $m4is_z05 =$m4is_d895['BillZip'];
 $m4is_c501 =isset($m4is_d895['CVV2'])? (string) $m4is_d895['CVV2']: '';
 $m4is_z75923 ='update';
 $m4is_y79 =' disabled ';
 
}else{
 $m4is_u807 =0;
 $m4is_p80796 =$m4is_a790['firstname']?? '';
 $m4is_r27 =$m4is_a790['lastname']?? '';
 $m4is_j694 ='';
 $m4is_h2674 ='';
 $m4is_i7468 ='';
 $m4is_b631 ='';
 $m4is_x0719 =trim($m4is_p80796 . ' ' . $m4is_r27 );
 $m4is_u9528 =$m4is_a790['phone1']?? '';
 $m4is_x965 =$m4is_a790['streetaddress1']?? '';
 $m4is_e20 =$m4is_a790['streetaddress2']?? '';
 $m4is_f16 =$m4is_a790['city']?? '';
 $m4is_a3165 =$m4is_a790['state']?? '';
 $m4is_m79 =$m4is_a790['country']?? '';
 $m4is_z05 =$m4is_a790['postalcode']?? '';
 $m4is_c501 ='';
 $m4is_z75923 ='add';
 $m4is_y79 =' ';
 
}if(empty($m4is_d895['NameOnCard'])){
$m4is_d895['NameOnCard']=$m4is_p80796 . ' ' . $m4is_r27;
 $m4is_x0719 =trim($m4is_d895['NameOnCard' ]);
 
}$m4is_u076 =base64_encode(serialize(['action_ids' =>$m4is_l62046['action_ids'], 'backcharge' =>$m4is_l62046['backcharge'], 'contact_id' =>self::$m4is_h21895, 'creditcard_id' =>$m4is_u807, 'debug' =>$m4is_l62046['debug'], 'debug_ip' =>$m4is_l62046['debug_ip'], 'failureurl' =>$m4is_l62046['failureurl'], 'goals' =>$m4is_l62046['goals'], 'plan_ids' =>$m4is_l62046['plan_ids'], 'set_default' =>$m4is_l62046['set_default'], 'success_msg' =>$m4is_l62046['success_msg'], 'successurl' =>$m4is_l62046['successurl'], 'tag_ids' =>$m4is_l62046['tag_ids'], ]));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u076);
 $m4is_l91805 =new stdClass;
 $m4is_l91805->form_name =$m4is_x29143;
 $m4is_l91805->form_id =$m4is_m92735;
 $m4is_l91805->parameters =$m4is_u076;
 $m4is_l91805->signature =self::$m4is_r1546->m4is_r626($m4is_u076);
 $m4is_l91805->nonce =wp_nonce_field('creditcard_add_' . $m4is_m92735, '_wpnonce', true, false);
 $m4is_l91805->card_types =$m4is_l62046['cardtypes'];
 $m4is_l91805->creditcard_type =$m4is_h2674;
 $m4is_l91805->card_number =$m4is_j694;
 $m4is_l91805->disabled =$m4is_y79;
 $m4is_l91805->expiration_month =$m4is_i7468;
 $m4is_l91805->expiration_year =$m4is_b631;
 $m4is_l91805->name_on_card =$m4is_x0719;
 $m4is_l91805->phone1 =$m4is_u9528;
 $m4is_l91805->address1 =$m4is_x965;
 $m4is_l91805->address2 =$m4is_e20;
 $m4is_l91805->city =$m4is_f16;
 $m4is_l91805->postalcode =$m4is_z05;
 $m4is_l91805->state =$m4is_a3165;
 $m4is_l91805->country =$m4is_m79;
 $m4is_l91805->country_options_html =self::$m4is_r1546->m4is_z40()->getCountryOptions($m4is_l91805->country );
 $m4is_l91805->country_options =[];
 return m4is_f61::m4is_l0659($m4is_v3458, $m4is_l62046, $m4is_t09761, $m4is_v3458, $m4is_l91805 );
 
}   public static 
function m4is_p327($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'default' =>'', 'fields' =>'', 'htmlattr' =>'', 'id' =>0, 'separator' =>' ', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['id']=(int) $m4is_l62046['id'];
 if(empty($m4is_l62046['fields'])||$m4is_l62046['id']< 1){
return '';
 
}$m4is_n0216 =array_filter(explode(',', strtolower($m4is_l62046['fields'])));
 $m4is_s12460 =is_array($m4is_n0216 )? count($m4is_n0216 ): 0;
 $m4is_h1438 =m4is_v87365::m4is_g864($m4is_l62046['id'], true);
 $m4is_o498 ='';
 foreach ($m4is_n0216 as $m4is_r637){
$m4is_r637 =trim($m4is_r637);
 $fieldvalue =isset($m4is_h1438[$m4is_r637])? $m4is_h1438[$m4is_r637]: $m4is_l62046['default'];
 if($m4is_r637 == 'productprice'){
$fieldvalue =number_format((double) $fieldvalue, 2);
 
}elseif($m4is_r637 == 'image.url'){
$fieldvalue ='https://' . self::$m4is_r9613 . '.infusionsoft.com/cart/pimg.jsp?i=5&t=p&s=x&date=' . time();
 
}$m4is_o498 .= $fieldvalue;
 if($m4is_s12460 > 1){
$m4is_o498 .= $m4is_l62046['separator'];
 
}
}if($m4is_s12460 > 1 &&$m4is_l62046['separator']){
$m4is_o498 =substr($m4is_o498, 0, -strlen($m4is_l62046['separator']));
 
}return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}public static 
function m4is_j94208($m4is_l62046 =[], $m4is_t09761 ='', $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'default' =>'', 'fields' =>'', 'id' =>0, 'separator' =>' ', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['id']=(int) $m4is_l62046['id'];
 if($m4is_l62046['fields']='' ||$m4is_l62046['id']< 1){
return '';
 
}$m4is_n0216 =array_filter(explode(',', $m4is_l62046['fields']));
 $m4is_s12460 =is_array($m4is_n0216 )? count($m4is_n0216): 0;
 $subscriptionplan =self::$m4is_r1546->m4is_i916($m4is_l62046['id'], TRUE);
 $m4is_o498 ='';
 foreach ($m4is_n0216 as $m4is_r637){
$m4is_r637 =strtolower(trim($m4is_r637));
 $fieldvalue =isset($subscriptionplan[$m4is_r637])? $subscriptionplan[$m4is_r637]: $m4is_l62046['default'];
 if(in_array($m4is_r637, ['planprice', 'preauthorizeamount'])){
$fieldvalue =number_format((double) $fieldvalue, 2);
 
}$m4is_o498 .= $fieldvalue;
 if($m4is_s12460 > 1){
$m4is_o498 .= $m4is_l62046['separator'];
 
}
}$m4is_o498 =substr($m4is_o498, 0, -strlen($m4is_l62046['separator']));
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
}   public static 
function m4is_r26($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
if(self::$m4is_r02639 ){
return '';
 
}   $m4is_y642 =['invoice_id' =>isset($_GET['invoice_id'])? (int) $_GET['invoice_id']: 0, 'contact_id' =>self::$m4is_r1546->m4is_z56(), 'date_format' =>'F j, Y', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_o498 ='';
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_y66291 =['invoice_id' =>$m4is_l62046['invoice_id'], 'contact_id' =>$m4is_l62046['contact_id'], ];
 $m4is_o17460 =self::$m4is_r1546->m4is_r86521($m4is_y66291 );
 if(is_array($m4is_o17460)){
$m4is_s84520 =get_option('memberium_invoice_template', false);
 $m4is_o498 .= '<div class="memberium-order-receipt">';
 if(!empty($m4is_s84520['header'])){
$m4is_o498 .= $m4is_s84520['header'];
 
}if(isset($m4is_o17460['orderitems'])&&is_array($m4is_o17460['orderitems'])){
foreach($m4is_o17460['orderitems']as $m4is_c4069){
$m4is_p6925 =$m4is_s84520['items'];
 $m4is_p6925 =str_ireplace('%%item.cpu%%', number_format($m4is_c4069['cpu'], 2), $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%item.ppu%%', number_format($m4is_c4069['ppu'], 2), $m4is_p6925);
 foreach($m4is_c4069 as $m4is_l9671 =>$m4is_v586){
$m4is_p6925 =str_ireplace('%%item.' . $m4is_l9671 . '%%', $m4is_v586, $m4is_p6925);
 
}$m4is_p6925 =str_ireplace('%%receipt.subtotal%%', number_format($m4is_c4069['ppu']* $m4is_c4069['qty'], 2), $m4is_p6925);
 $m4is_o498 .= $m4is_p6925;
 
}
}$m4is_o498 .= $m4is_s84520['pre_payments'];
 foreach($m4is_o17460['payments']as $m4is_f40){
$m4is_p6925 =$m4is_s84520['payments'];
 $m4is_p6925 =str_ireplace('%%payment.paydate%%', date($m4is_l62046['date_format'], strtotime($m4is_f40['paydate'])), $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%payment.payamt%%', number_format($m4is_f40['payamt'], 2), $m4is_p6925);
 foreach($m4is_f40 as $m4is_l9671 =>$m4is_v586){
$m4is_p6925 =str_ireplace('%%payment.' . $m4is_l9671 . '%%', $m4is_v586, $m4is_p6925);
 
}$m4is_o498 .= $m4is_p6925;
 
}$m4is_o498 .= $m4is_s84520['pre_scheduled'];
  foreach($m4is_o17460['paymentplanitems']as $m4is_f40){
$m4is_p6925 =$m4is_s84520['scheduled'];
 $m4is_p6925 =str_ireplace('%%scheduled.datedue%%', date($m4is_l62046['date_format'], strtotime($m4is_f40['datedue'])), $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%scheduled.amtdue%%', number_format($m4is_f40['amtdue'], 2), $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%scheduled.amtpaid%%', number_format($m4is_f40['amtpaid'], 2), $m4is_p6925);
 foreach($m4is_f40 as $m4is_l9671 =>$m4is_v586){
$m4is_p6925 =str_ireplace('%%scheduled.' . $m4is_l9671 . '%%', $m4is_v586, $m4is_p6925);
 
}$m4is_o498 .= $m4is_p6925;
 
}$m4is_o498 .= $m4is_s84520['footer'];
 $m4is_o498 .= '</div>';
 if(!empty($m4is_o17460['job'])){
$m4is_o498 =str_ireplace('%%order.datecreated%%', date($m4is_l62046['date_format'], strtotime($m4is_o17460['job']['datecreated'])), $m4is_o498);
 $m4is_o498 =str_ireplace('%%order.duedate%%', date($m4is_l62046['date_format'], strtotime($m4is_o17460['job']['duedate'])), $m4is_o498);
 
}if(!empty($m4is_o17460['invoice'])){
$m4is_o498 =str_ireplace('%%invoice.datecreated%%', date($m4is_l62046['date_format'], strtotime($m4is_o17460['invoice']['datecreated'])), $m4is_o498);
 $m4is_o498 =str_ireplace('%%invoice.overdue%%', number_format($m4is_o17460['invoice']['totaldue']- $m4is_o17460['invoice']['totalpaid'], 2), $m4is_o498);
 $m4is_o498 =str_ireplace('%%invoice.invoicetotal%%', number_format($m4is_o17460['invoice']['invoicetotal'], 2), $m4is_o498);
 $m4is_o498 =str_ireplace('%%invoice.totalpaid%%', number_format($m4is_o17460['invoice']['totalpaid'], 2), $m4is_o498);
 
}if(is_array($m4is_o17460['invoice'])){
foreach($m4is_o17460['invoice']as $m4is_l9671 =>$m4is_v586){
$m4is_o498 =str_ireplace('%%invoice.' . $m4is_l9671 . '%%', $m4is_v586, $m4is_o498);
 
}
}if(is_array($m4is_o17460['contact'])){
foreach($m4is_o17460['contact']as $m4is_l9671 =>$m4is_v586){
$m4is_o498 =str_ireplace('%%contact.' . $m4is_l9671 . '%%', $m4is_v586, $m4is_o498);
 
}
}if(isset($m4is_o17460['job'])&&is_array($m4is_o17460['job'])){
foreach($m4is_o17460['job']as $m4is_l9671 =>$m4is_v586){
$m4is_o498 =str_ireplace('%%order.' . $m4is_l9671 . '%%', $m4is_v586, $m4is_o498);
 
}
}$m4is_o498 =apply_filters('memberium_show_receipt', $m4is_o498, $m4is_o17460);
  $m4is_o498 =preg_replace('/(%%\S+%%)/', '', $m4is_o498);
 
}return do_shortcode($m4is_o498);
 
}   public static 
function m4is_x61024($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 ='' ){
if(self::$m4is_r02639 ){
return '';
 
}global $wpdb;
 $m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>false, 'contact_id' =>self::$m4is_h21895, 'days' =>0, 'htmlattr' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_d61597 ='0.00';
 if(!empty($m4is_l62046['contact_id'])){
$m4is_v2613 ='SELECT sum(`totalpaid`) FROM %i WHERE `appname` = %s `contactid` = %d AND `payplanstatus` = 1 AND `paystatus` = 1; ';
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, m4is_v87365::m4is_p63285(), self::$m4is_r1546->m4is_i76('appname'), $m4is_l62046['contact_id']);
 $m4is_d61597 =$wpdb->get_var($m4is_v2613);
 
}return m4is_f61::m4is_u150(false, $m4is_d61597, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
} public static 
function m4is_o0569($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['button_text' =>'Pay Now', 'contact_id' =>self::$m4is_h21895, 'date_format' =>'M d, Y', 'form_class' =>'invoiceline', 'goal' =>'', 'limit' =>0, 'merchant_id' =>self::$m4is_r1546->m4is_j498('settings', 'merchant_account_id' ), 'nocost' =>true, 'only_prodids' =>'', 'paid' =>true, 'post' =>'', 'pre' =>'', 'redirect_url' =>$_SERVER['REQUEST_URI'], 'reverse' =>false, 'success_msg' =>'Your payment was successfully processed.', 'tag_id' =>'', 'unpaid' =>true,  ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(', ', array_keys($m4is_y642 ));
 
}if(is_feed()||!is_singular()){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, self::$m4is_f4218 );
 if(!$m4is_l62046['contact_id']){
return '';
 
}$m4is_l62046['nocost']=m4is_f61::m4is_d8195($m4is_l62046['nocost'], false );
 $m4is_l62046['paid']=m4is_f61::m4is_d8195($m4is_l62046['paid'], false );
 $m4is_l62046['reverse']=m4is_f61::m4is_d8195($m4is_l62046['reverse'], false );
 $m4is_l62046['unpaid']=m4is_f61::m4is_d8195($m4is_l62046['unpaid'], false );
 if(trim($m4is_t09761)== ''){
$m4is_t09761 .= '<div><span>%%invoice.id%%</span>';
 $m4is_t09761 .= '<span>%%description%%</span>';
 $m4is_t09761 .= '<span>$%%invoice.total%%</span>';
 $m4is_t09761 .= '<span>$%%total.paid%%</span>';
 $m4is_t09761 .= '<span>$%%refund.status%%</span>';
 $m4is_t09761 .= '<span>$%%amount.due%%</span>';
 $m4is_t09761 .= '<span>%%date.due%%</span>';
 $m4is_t09761 .= '<span>%%creditcard.dropdown%%</span>';
 $m4is_t09761 .= '<span>%%submit%%</span></div>';
 
}$m4is_r9613 =self::$m4is_r9613;
 $m4is_j098 =m4is_v87365::m4is_d96640();
 $m4is_d01 =self::$m4is_r1546->m4is_v31259($m4is_l62046['contact_id'], $m4is_l62046['paid'], $m4is_l62046['unpaid']);
 $m4is_d01 =apply_filters('memberium/shortcodes/list_invoices/invoices', $m4is_d01 );
 if(is_array($m4is_d01)&&!$m4is_l62046['nocost']){
foreach($m4is_d01 as $m4is_l9671 =>$m4is_t06897){
if($m4is_t06897['InvoiceTotal']== 0){
unset($m4is_d01[$m4is_l9671]);
 
}
}
}if($m4is_l62046['reverse']){
$m4is_d01 =array_reverse($m4is_d01, true);
 
}$m4is_p1054 =[];
 $jobs =[];
 if(!empty($m4is_d01)){
$m4is_p1054 =m4is_c01675::m4is_w68604($m4is_l62046['contact_id']);
 
} if(empty($m4is_p1054)){
$m4is_u836 ='';
 
}else{
$m4is_u836 ='<select name="creditcard_id">';
 if(is_array($m4is_p1054)){
foreach ($m4is_p1054 as $m4is_d895){
$m4is_c31 =isset($m4is_d895['Status'])? $m4is_d895['Status']: 0;
 if(in_array($m4is_d895['Status'], [1, 3, 4])){
$m4is_q46023 =isset($m4is_d895['CardType'])? $m4is_d895['CardType']: 'Unknown';
 $m4is_s4966 =isset($m4is_d895['Last4'])? $m4is_d895['Last4']: '';
 $m4is_u836 .= '<option value="'. $m4is_d895['Id']. '">' . $m4is_q46023 . ' ' . $m4is_s4966 . '</option>';
 
}
}
}$m4is_u836 .= '</select>';
 
}$m4is_n468 =[0 =>_x('No Credit', 'memb_list_invoices', self::$m4is_f4218 ), 1 =>_x('Partial Credit Applied', 'memb_list_invoices', self::$m4is_f4218 ), 2 =>_x('Full Credit Applied', 'memb_list_invoices', self::$m4is_f4218 ), ];
 $m4is_e8263 =[0 =>_x('Not Paid', 'memb_list_invoices', self::$m4is_f4218 ), 1 =>_x('Paid', 'memb_list_invoices', self::$m4is_f4218 ), ];
 $m4is_p634 =[0 =>_x('No Refund', 'memb_list_invoices', self::$m4is_f4218 ), 1 =>_x('Partial Refund', 'memb_list_invoices', self::$m4is_f4218 ), 2 =>_x('Full Refund', 'memb_list_invoices', self::$m4is_f4218 ), 3 =>_x('Full Write-Off', 'memb_list_invoices', self::$m4is_f4218 ), 4 =>_x('Partial Write-Off', 'memb_list_invoices', self::$m4is_f4218 ), ];
 $m4is_f086 =0;
 $m4is_o498 =$m4is_l62046['pre'];
 if(is_array($m4is_d01 )){
if(count($m4is_d01 )> $m4is_l62046['limit']){

}foreach ($m4is_d01 as $m4is_t06897 ){
 if($m4is_l62046['only_prodids']> '' ){

}$m4is_w89066 =array_filter(explode(',', $m4is_t06897['ProductSold']));
 $m4is_t68059 =is_array($m4is_w89066)? count($m4is_w89066 ): 0;
 $m4is_y04791 =array_shift($m4is_w89066 );
 $m4is_v7460 =$m4is_n468[$m4is_t06897['CreditStatus']];
 $m4is_q9658 =$m4is_e8263[$m4is_t06897['PayStatus']];
 $m4is_q032 =$m4is_p634[$m4is_t06897['RefundStatus']];
 $m4is_y642 =['goal' =>$m4is_l62046['goal'], 'invoice_id' =>(int) $m4is_t06897['Id'], 'merchant_id' =>(int) $m4is_l62046['merchant_id'], 'redirect_url' =>$m4is_l62046['redirect_url'], 'success_msg' =>$m4is_l62046['success_msg'], 'tag_id' =>$m4is_l62046['tag_id'], ];
 $m4is_u076 =base64_encode(serialize($m4is_y642 ));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u076 );
  $m4is_f086++;
 if($m4is_f086 % 2 ){
$m4is_g73641 ='odd';
 
}else{
$m4is_g73641 ='even';
 
} if(in_array(trim($m4is_t06897['Description']), ['One-time:', 'Order Form', 'API Order'])){
if(isset($m4is_j098[$m4is_y04791]['ProductName'])){
$m4is_t06897['Description']=$m4is_j098[$m4is_y04791]['ProductName'];
 if($m4is_t68059 > 1 ){
$m4is_t06897['Description'].= sprintf(_x(', and %d other items.', 'memb_list_invoices', self::$m4is_f4218 ), ($m4is_t68059 - 1 ));
 
}
}
}$m4is_p6925 ='<form method="post" action="">';
 $m4is_p6925 .= wp_nonce_field('memb_payinvoice_' . $m4is_t06897['Id'], '_wpnonce', true, false );
 $m4is_p6925 .= '<input type="hidden" name="memb_form_type" value="memb_pay_invoice">';
 $m4is_p6925 .= '<input type="hidden" name="parameters" value="' . $m4is_u076 . '">';
 $m4is_p6925 .= '<input type="hidden" name="signature" value="' . $m4is_o31859 . '">';
 $m4is_p6925 .= $m4is_t09761;
 $m4is_p6925 .= '</form>';
 $m4is_p6925 =str_ireplace('%%credit.status%%', $m4is_v7460, $m4is_p6925 );
 $m4is_p6925 =str_ireplace('%%cycler%%', $m4is_g73641, $m4is_p6925 );
 $m4is_p6925 =str_ireplace('%%description%%', $m4is_t06897['Description'], $m4is_p6925 );
 $m4is_p6925 =str_ireplace('%%invoice.id%%', $m4is_t06897['Id'], $m4is_p6925 );
 $m4is_p6925 =str_ireplace('%%invoice.total%%', sprintf('%0.2f', $m4is_t06897['InvoiceTotal']), $m4is_p6925 );
 $m4is_p6925 =str_ireplace('%%line%%', $m4is_f086, $m4is_p6925 );
 $m4is_p6925 =str_ireplace('%%pay.status%%', $m4is_q9658, $m4is_p6925 );
 $m4is_p6925 =str_ireplace('%%refund.status%%', $m4is_q032, $m4is_p6925 );
 $m4is_p6925 =str_ireplace('%%total.paid%%', sprintf('%0.2f', $m4is_t06897['TotalPaid']), $m4is_p6925 );
 $m4is_p6925 =str_ireplace('%%date.created%%', date($m4is_l62046['date_format'], strtotime($m4is_t06897['DateCreated'])), $m4is_p6925 );
 if(!empty($m4is_t06897['!payment_due'])){
$m4is_p6925 =str_ireplace('%%amount.due%%', sprintf('%0.2f', $m4is_t06897['!payment_due']), $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%date.due%%', 'Past Due', $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%creditcard.dropdown%%', $m4is_u836, $m4is_p6925);
 if($m4is_l62046['merchant_id']> 0 &&(!empty($m4is_p1054 ))){
$m4is_p6925 =str_ireplace('%%submit%%', '<input type="submit" class="memb_invoice_payment_button" value="' . $m4is_l62046['button_text']. '">', $m4is_p6925);
 
}else{
$m4is_p6925 =str_ireplace('%%submit%%', '', $m4is_p6925);
 
}
}else{
$m4is_p6925 =str_ireplace('%%amount.due%%', '0.00', $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%date.due%%', date($m4is_l62046['date_format'], strtotime($m4is_t06897['DateCreated'])), $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%creditcard.dropdown%%', '', $m4is_p6925);
 $m4is_p6925 =str_ireplace('%%submit%%', '', $m4is_p6925);
 
}$m4is_o498 .= $m4is_p6925;
 
}
}$m4is_o498 .= $m4is_l62046['post'];
 return do_shortcode($m4is_o498 );
 
}public static 
function m4is_v1905($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''){
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_p1054 =false;
 static $m4is_d74 =false;
 $m4is_y642 =['cancel_text' =>'Cancel', 'confirm_button' =>'Yes, Stop Subscription', 'confirm_cancel' =>'Keep Subscription', 'confirm_title' =>'Cancel Subscription?', 'confirm' =>0, 'date_format' =>'M d, Y', 'immediate' =>false,  'jqueryui' =>1, 'onlyids' =>'', 'orderby' =>'StartDate', 'sort' =>'a',  'status' =>'all',  ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!self::$m4is_h21895 ){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['confirm']=m4is_f61::m4is_d8195($m4is_l62046['confirm'], true);
 $m4is_l62046['immediate']=m4is_f61::m4is_d8195($m4is_l62046['immediate'], false);
 $m4is_l62046['jqueryui']=m4is_f61::m4is_d8195($m4is_l62046['jqueryui'], true);
 $m4is_l62046['status']=ucwords(trim($m4is_l62046['status']));
 $m4is_l62046['sort']=substr(strtolower(trim($m4is_l62046['sort'])), 0, 1);
 $m4is_l62046['orderby']=trim($m4is_l62046['orderby']);
 $m4is_k236 =($m4is_l62046['sort']== 'a');
 $m4is_p1054 =m4is_c01675::m4is_w68604(self::$m4is_h21895 );
 $m4is_j098 =m4is_v87365::m4is_e98671();
 $m4is_s5867 =m4is_v87365::m4is_x096();
 $m4is_d74 =self::$m4is_r1546->m4is_c686(self::$m4is_h21895 );
 if(is_array($m4is_d74)){
$m4is_l62046['onlyids']=array_filter(explode(',', $m4is_l62046['onlyids']));
 foreach ($m4is_d74 as $m4is_l9671=>$m4is_c12){
if($m4is_l62046['status']<> 'All'){
if(strtolower($m4is_l62046['status'])<> strtolower($m4is_c12['Status'])){
unset($m4is_d74[$m4is_l9671]);
 
}
}
}if(!empty($m4is_l62046['onlyids'])){
foreach($m4is_d74 as $m4is_l9671 =>$m4is_j90523){
if(!in_array($m4is_j90523['ProgramId'], $m4is_l62046['onlyids'])){
if(!in_array($m4is_j90523['SubscriptionPlanId'], $m4is_l62046['onlyids'])){
unset($m4is_d74[$m4is_l9671]);
 
}
}
}
}usort($m4is_d74, function($a, $b)use ($m4is_l62046, $m4is_k236){
$orderby =$m4is_l62046['orderby'];
 if($a[$orderby]== $b[$orderby]){
$order =0;
 
}if($m4is_k236){
$order =$a[$orderby]< $b[$orderby]? -1 : 1;
 
}else{
$order =$a[$orderby]> $b[$orderby]? -1 : 1;
 
}return $order;
 
});
 
}$m4is_x5841 =(is_array($m4is_d74 )&&!empty($m4is_d74 ));
 $m4is_f086 =0;
 $m4is_o498 ='';
 if($m4is_x5841 &&trim($m4is_t09761 )== '' ){
$m4is_t09761 ='<p>
			%%line%% - %%cycler%% -
			Subscription Name: %%subscription.name%%<br />
			Subscription Status:  %%subscription.status%%<br />
			Subscription Price: %%subscription.price%%<br />
			Subscription Billing Cycle:  %%subscription.billingcycle%%<br />
			Credit Card:  %%creditcard.last4%%<br />
			Card Type: %%creditcard.type%%<br />
			Card Expiration: %%creditcard.expmonth%% / %%creditcard.expyear%%<br />
			Subscription Start Date: %%subscription.startdate%%<br />
			Subscription Paid Through Date: %%subscription.paidthrough%%<br />
			Subscription Next Billing Date: %%subscription.nextbilling%%<br />
			Subscription Status: %%subscription.status%%<br />
			Keywords: %%subscription._keywords%%<br />
			%%cancel.button%%
			</p>';
 
}$m4is_t09761 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, true, $m4is_x5841 );
 if($m4is_x5841){
$m4is_a89 =m4is_c69807::m4is_f5248('RecurringOrder');
 foreach ($m4is_d74 as $m4is_l9671=>$m4is_c12){
$m4is_f086++;
 $m4is_i768 =false;
 $m4is_c31 =_x($m4is_c12['Status'], 'memb_list_subscriptions', 'memberium' );
 switch ($m4is_c12['BillingCycle']){
case 6: $m4is_c12['BillingCycleWord1']=_x('Daily', 'memb_list_subscriptions', 'memberium');
 $m4is_c12['BillingCycleWord2']=_x('Days', 'memb_list_subscriptions', 'memberium');
 break;
 case 3: $m4is_c12['BillingCycleWord1']=_x('Weekly', 'memb_list_subscriptions', 'memberium');
 $m4is_c12['BillingCycleWord2']=_x('Weeks', 'memb_list_subscriptions', 'memberium');
 break;
 case 2: $m4is_c12['BillingCycleWord1']=_x('Monthly', 'memb_list_subscriptions', 'memberium');
 $m4is_c12['BillingCycleWord2']=_x('Months', 'memb_list_subscriptions', 'memberium');
 break;
 case 1: $m4is_c12['BillingCycleWord1']=_x('Annually', 'memb_list_subscriptions', 'memberium');
 $m4is_c12['BillingCycleWord2']=_x('Years', 'memb_list_subscriptions', 'memberium');
 break;
 
}if(empty($m4is_c12['PaidThruDate'])){
$m4is_c12['PaidThruDate']=$m4is_c12['StartDate'];
 
}$m4is_u076 =base64_encode(serialize(['cancel_text' =>$m4is_l62046['cancel_text'], 'immediate' =>$m4is_l62046['immediate'], 'next_bill_date' =>$m4is_c12['NextBillDate'], 'paid_through_date' =>$m4is_c12['PaidThruDate'], 'recurringorder_id' =>(int) $m4is_c12['Id'], 'subscriptionplan_id' =>(int) $m4is_c12['SubscriptionPlanId'], ]));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u076);
 $m4is_u90135 ='<form method="post" class="memberium-subscription-list" action="">';
 $m4is_u90135 .= wp_nonce_field('memb_cancelsubscription_' . $m4is_c12['Id'], '_wpnonce', true, false);
 $m4is_u90135 .= '<input type="hidden" name="memb_form_type" value="memb_cancel_subscription">';
 $m4is_u90135 .= '<input type="hidden" name="parameters" value="' . $m4is_u076 . '">';
 $m4is_u90135 .= '<input type="hidden" name="signature" value="' . $m4is_o31859 . '">';
 $m4is_u90135 .= $m4is_t09761;
 if($m4is_c12['Frequency']> 1){
$m4is_d637 ='Every ' . $m4is_c12['Frequency']. ' ' . $m4is_c12['BillingCycleWord2'];
 
}else{
$m4is_d637 =$m4is_c12['BillingCycleWord1'];
 
}if($m4is_f086 % 2){
$m4is_g73641 ='odd';
 
}else{
$m4is_g73641 ='even';
 
}$m4is_o78296 =empty($m4is_c12['StartDate'])? __('None'): date($m4is_l62046['date_format'], strtotime($m4is_c12['StartDate']));
 $m4is_r08692 =empty($m4is_c12['NextBillDate'])? __('None'): date($m4is_l62046['date_format'], strtotime($m4is_c12['NextBillDate']));
 $m4is_u90135 =str_ireplace('%%creditcard.expmonth%%', isset($m4is_p1054[$m4is_c12['CC1']]['ExpirationMonth'])? $m4is_p1054[$m4is_c12['CC1']]['ExpirationMonth']: '', $m4is_u90135);
 $m4is_u90135 =str_ireplace('%%creditcard.expyear%%', isset($m4is_p1054[$m4is_c12['CC1']]['ExpirationYear'])? $m4is_p1054[$m4is_c12['CC1']]['ExpirationYear']: '', $m4is_u90135);
 $m4is_u90135 =str_ireplace('%%creditcard.id%%', (isset($m4is_p1054['Id'])? $m4is_p1054['Id']: ''), $m4is_u90135);
 $m4is_u90135 =str_ireplace('%%creditcard.last4%%', isset($m4is_p1054[$m4is_c12['CC1']]['Last4'])? 'XXXX-' . $m4is_p1054[$m4is_c12['CC1']]['Last4']: '', $m4is_u90135);
 $m4is_u90135 =str_ireplace('%%creditcard.type%%', isset($m4is_p1054[$m4is_c12['CC1']]['CardType'])? $m4is_p1054[$m4is_c12['CC1']]['CardType']: '', $m4is_u90135);
 $m4is_u90135 =str_ireplace('%%cycler%%', $m4is_g73641, $m4is_u90135);
 $m4is_u90135 =str_ireplace('%%line%%', $m4is_f086, $m4is_u90135);
 $m4is_u90135 =str_ireplace('%%subscription.billingcycle%%', $m4is_d637, $m4is_u90135);
 $m4is_u90135 =str_ireplace('%%subscription.id%%', $m4is_c12['Id'], $m4is_u90135);
 $m4is_u90135 =str_ireplace('%%subscription.name%%', $m4is_j098[$m4is_s5867[$m4is_c12['SubscriptionPlanId']]['ProductId']]['ProductName'], $m4is_u90135);
 $m4is_u90135 =str_ireplace('%%subscription.nextbilling%%', $m4is_r08692, $m4is_u90135);
 $m4is_u90135 =str_ireplace('%%subscription.paidthrough%%', date($m4is_l62046['date_format'], strtotime($m4is_c12['PaidThruDate'])), $m4is_u90135 );
 $m4is_u90135 =str_ireplace('%%subscription.price%%', number_format($m4is_c12['BillingAmt'], 2 ), $m4is_u90135 );
 $m4is_u90135 =str_ireplace('%%subscription.startdate%%', $m4is_o78296, $m4is_u90135 );
 $m4is_u90135 =str_ireplace('%%subscription.status%%', $m4is_c31, $m4is_u90135 );
 foreach($m4is_a89 as $m4is_q523){
if($m4is_q523[0]== '_' &&isset($m4is_c12[$m4is_q523])){
;
 $m4is_u90135 =str_ireplace('%%subscription.' . $m4is_q523 . '%%', $m4is_c12[$m4is_q523], $m4is_u90135);
 
}
}if(!empty($m4is_c12['EndDate'])){
$m4is_u90135 =str_ireplace('%%subscription.enddate%%', date($m4is_l62046['date_format'], strtotime($m4is_c12['EndDate'])), $m4is_u90135);
 
}else{
$m4is_u90135 =str_ireplace('%%subscription.enddate%%', '', $m4is_u90135);
 
}if(strtolower($m4is_c12['Status'])== 'active' &&empty($m4is_c12['EndDate'])){
$m4is_u90135 =str_ireplace('%%cancel.button%%', '<input type="submit" class="memb_subscription_cancel_button" value="' . $m4is_l62046['cancel_text']. '">', $m4is_u90135);
 
}elseif(!empty($m4is_c12['EndDate'])){
$m4is_u90135 =str_ireplace('%%cancel.button%%', __('Cancelled', 'memberium'), $m4is_u90135);
 
}else{
$m4is_u90135 =str_ireplace('%%cancel.button%%', '', $m4is_u90135);
 
}$m4is_u90135 .= '</form>';
 $m4is_o498 .= do_shortcode($m4is_u90135);
 
}unset($m4is_d637);
 
}else{
$m4is_o498 .= $m4is_t09761;
 
}if($m4is_l62046['confirm']){
$params =['confirmTitle' =>$m4is_l62046['confirm_title'], 'confirmButton' =>$m4is_l62046['confirm_button'], 'confirmCancel' =>$m4is_l62046['confirm_cancel'], ];
 if($m4is_l62046['jqueryui']){
$m4is_c36751 =get_bloginfo('version' );
 if(version_compare($m4is_c36751, '5.6', 'ge' )){
wp_enqueue_script('jqueryui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js' );
 wp_enqueue_style('jqueryuitheme', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' );
 
}else{
wp_enqueue_script('jqueryui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js' );
 wp_enqueue_style('jqueryuitheme', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/themes/smoothness/jquery-ui.css' );
 
}
}wp_enqueue_script('memberium-cancellation-confirmation', plugins_url('js/cancellation-confirm.js', MEMBERIUM_HOME ));
 wp_localize_script('memberium-cancellation-confirmation', 'subscriptionCancelText', $params );
 
} $m4is_o498 =preg_replace('/(%%\S+%%)/', '', $m4is_o498);
 return do_shortcode($m4is_o498);
 
}public static 
function m4is_j467($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'contact_id' =>0, 'invoice_id' =>isset($_GET['invoice_id'])? (int) $_GET['invoice_id']: 0, 'product_ids' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(is_feed()||!is_singular()){
return '';
 
}$m4is_o498 ='';
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['contact_id']=$m4is_l62046['contact_id']? $m4is_l62046['contact_id']: self::$m4is_r1546->m4is_z56();
 $m4is_l62046['product_ids']=array_filter(explode(',', $m4is_l62046['product_ids']));
 if(empty($m4is_l62046['contact_id'])||empty($m4is_l62046['product_ids'])){
return '';
 
}$m4is_d01 =self::$m4is_r1546->m4is_v31259($m4is_l62046['contact_id']);
 if($m4is_l62046['invoice_id']){
$m4is_l97604 =$m4is_l62046['invoice_id'];
 $m4is_d01 =isset($m4is_d01[$m4is_l97604])? [$m4is_l97604 =>$m4is_d01[$m4is_l97604]]: [];
 
}foreach($m4is_d01 as $m4is_t06897){
$m4is_j098 =isset($m4is_t06897['ProductSold'])? explode(',', $m4is_t06897['ProductSold']): [];
 if(!empty(array_intersect($m4is_j098, $m4is_l62046['product_ids']))){
$m4is_p46 =true;
 break;
 
}
}$m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, TRUE, $m4is_p46);
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
}public static 
function m4is_j614($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['capture' =>'', 'contact_id' =>0, 'invoice_id' =>isset($_GET['invoice_id'])? (int) $_GET['invoice_id']: 0, 'status' =>'Active', 'subscription_ids' =>'', 'txtfmt' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(is_feed()||!is_singular()){
return '';
 
}$m4is_o498 ='';
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['contact_id']=$m4is_l62046['contact_id']? $m4is_l62046['contact_id']: self::$m4is_r1546->m4is_z56();
 $m4is_l62046['subscription_ids']=array_filter(explode(',', $m4is_l62046['subscription_ids']));
 $m4is_l62046['status']=strtolower($m4is_l62046['status']);
 if(empty($m4is_l62046['contact_id'])||empty($m4is_l62046['subscription_ids'])){
return '';
 
}$m4is_d74 =self::$m4is_r1546->m4is_c686($m4is_l62046['contact_id']);
 if($m4is_l62046['invoice_id']){
$m4is_l97604 =$m4is_l62046['invoice_id'];
 $m4is_d74 =isset($m4is_d74[$m4is_l97604])? [$m4is_l97604 =>$m4is_d74[$m4is_l97604]]: [];
 
}if(!empty($m4is_l62046['status'])){
foreach($m4is_d74 as $m4is_d07693 =>$m4is_c12){
if(strtolower($m4is_c12['Status'])<> $m4is_l62046['status']){
unset($m4is_d74[$m4is_d07693]);
 
}
}
}foreach($m4is_d74 as $m4is_c12){
if(in_array($m4is_c12['SubscriptionPlanId'], $m4is_l62046['subscription_ids'])){
$m4is_j05367 =true;
 break;
 
}
}$m4is_o498 =m4is_f61::m4is_d6851($m4is_t09761, $m4is_v3458, TRUE, $m4is_j05367);
 return m4is_f61::m4is_u150(false, $m4is_o498, $m4is_l62046['txtfmt'], $m4is_l62046['capture']);
 
}    public static 
function m4is_t38($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
}if(empty($_SERVER['HTTPS'])){
if(self::$m4is_r1546->m4is_v461()){
return '<p>Order Form Disabled Due to Missing SSL</p>';
 
}return '';
 
}$m4is_y642 =['autofill' =>'n', 'branding' =>'n', 'button_url' =>'', 'cache' =>HOUR_IN_SECONDS * 24, 'capture' =>'', 'form_id' =>'', 'form_url' =>'', 'remove_styles' =>'y', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 if(false !== strpos($m4is_l62046['form_url'], '.infusionsoft.com/saleform/')){
$m4is_l62046['form_url']=str_ireplace('.infusionsoft.com/', '.infusionsoft.app/', $m4is_l62046['form_url']);
 
}if(false === strpos($m4is_l62046['form_url'], '.infusionsoft.app/saleform/')){
return _x('Invalid Form Embed', 'memb_orderform', 'memberium');
 
}if(false === stripos($m4is_l62046['form_url'], '?cookieSearchStarted=true&cookieStopSearch=true')){
$m4is_l62046['form_url'].= '?cookieSearchStarted=true&cookieStopSearch=true';
 
}$m4is_p5174 =$m4is_l62046['cache'];
 $m4is_v6695 =$m4is_l62046['form_url'];
 $m4is_l62046['branding']=strtolower(substr($m4is_l62046['branding'], 0, 1));
 $m4is_l62046['autofill']=strtolower(substr($m4is_l62046['autofill'], 0, 1));
 $m4is_l62046['remove_styles']=strtolower(substr($m4is_l62046['remove_styles'], 0, 1));
 $m4is_j316 =false;
 $m4is_s951 ='memberium_orderform_' . md5($m4is_v6695);
 if($m4is_p5174 > 0){
$m4is_j316 =get_transient($m4is_s951);
 
} if($m4is_j316 == false){
$m4is_m14 =wp_remote_get($m4is_v6695);
  if(is_array($m4is_m14)){
preg_match('/<body>(.*)<\/body>/sm', $m4is_m14['body'], $m4is_u6591);
 if(isset($m4is_u6591[1])){
$m4is_j316 =$m4is_u6591[1];
 set_transient($m4is_s951, $m4is_j316, $m4is_p5174);
 
}
}else{
return $m4is_m14->errors['http_request_failed'][0];
 
}
}if($m4is_l62046['remove_styles']== 'y'){
$m4is_j316 =preg_replace('/<style>(.*)<\/style>/imU', '', $m4is_j316);
 
}if($m4is_l62046['form_id']> ''){
$m4is_j316 =str_replace('id="orderForm"', 'id="' . $m4is_l62046['form_id']. '"  ', $m4is_j316);
 
}if($m4is_l62046['autofill']== 'y'){
foreach ($_GET as $m4is_l9671 =>$m4is_v586){
$m4is_j316 =str_replace('name=\'' . $m4is_l9671 . '\' value=\'\'', 'name="' . $m4is_l9671 . '" value="' . $m4is_v586 . '"', $m4is_j316);
 
}
}if($m4is_l62046['button_url']> ''){
$m4is_o3451 ='<input type="image" value="Order" id="Order" class="default-input sale-orderbutton np inf-button" name="Order" src="' . $m4is_l62046['button_url']. '" style="width:auto;overflow:visible;"/>';
 $m4is_j316 =preg_replace('/<input(.*)id="Order"(.*)>/iU', $m4is_o3451, $m4is_j316);
 
}if($m4is_l62046['branding']== 'n'){
$m4is_j316 =str_replace('Powered By Keap', '', $m4is_j316);
 
}if($m4is_l62046['capture']> ''){
$m4is_j316 =m4is_f61::m4is_f6513($m4is_j316, $m4is_l62046['capture']);
 
}return $m4is_j316;
 
}public static 
function m4is_k8723($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
if(self::$m4is_r02639 ){
return '';
 
}$m4is_y642 =['autorun' =>'no', 'button_name' =>'order_button', 'button_text' =>'', 'bypass_commissions' =>'no', 'css_class' =>'', 'delete_failed' =>'yes', 'fail_action' =>'', 'fail_goals' =>'', 'fail_tags' =>'', 'fail_url' =>'', 'lead_affiliate_id' =>0, 'merchant_id' =>(int) self::$m4is_r1546->m4is_j498('settings', 'merchant_account_id' ), 'prod_description' =>'', 'prod_id' =>0, 'prod_name' =>'', 'prod_price' =>'', 'prod_type' =>4, 'qty' =>1, 'sales_affiliate_id' =>0, 'success_action' =>'', 'success_goals' =>'', 'success_tags' =>'', 'success_url' =>'', 'taxable' =>'no', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!self::$m4is_h21895 ){
return '';
 
}static $m4is_m92735 =0;
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['autorun']=m4is_f61::m4is_d8195($m4is_l62046['autorun'], false);
 $m4is_l62046['bypass_commissions']=m4is_f61::m4is_d8195($m4is_l62046['bypass_commissions'], false);
 $m4is_l62046['delete_failed']=m4is_f61::m4is_d8195($m4is_l62046['delete_failed'], true);
 $m4is_l62046['taxable']=m4is_f61::m4is_d8195($m4is_l62046['taxable'], false);
 $m4is_l62046['button_text']=$m4is_l62046['button_text']> '' ? $m4is_l62046['button_text']: 'Buy Now ' . $m4is_l62046['prod_price'];
  $m4is_p1054 =m4is_c01675::m4is_w68604(self::$m4is_h21895 );
 if(is_array($m4is_p1054)&&count($m4is_p1054)> 0){
$m4is_d895 =end($m4is_p1054);
 $m4is_u807 =(int) $m4is_d895['Id'];
 unset($m4is_d895, $m4is_p1054);
 
}else{
return '';
 
}foreach($m4is_y642 as $m4is_l9671 =>$m4is_v586){
$m4is_l62046[$m4is_l9671]=trim($m4is_l62046[$m4is_l9671]);
 
}$m4is_n418 =['has_payment_plan' =>false, 'autorun' =>(bool) $m4is_l62046['autorun'], 'bypass_commissions' =>(bool) $m4is_l62046['bypass_commissions'], 'delete_failed' =>(bool) $m4is_l62046['delete_failed'], 'taxable' =>(bool) $m4is_l62046['taxable'], 'product_price' =>(float) $m4is_l62046['prod_price'], 'creditcard_id' =>(int) $m4is_u807, 'lead_affiliate_id' =>(int) $m4is_l62046['lead_affiliate_id'], 'merchant_id' =>(int) $m4is_l62046['merchant_id'], 'product_id' =>(int) $m4is_l62046['prod_id'], 'product_type' =>(int) $m4is_l62046['prod_type'], 'quantity' =>(int) $m4is_l62046['qty'], 'sales_affiliate_id' =>(int) $m4is_l62046['sales_affiliate_id'], 'fail_action' =>$m4is_l62046['fail_action'], 'fail_goals' =>$m4is_l62046['fail_goals'], 'fail_tags' =>$m4is_l62046['fail_tags'], 'fail_url' =>$m4is_l62046['fail_url'], 'product_description' =>$m4is_l62046['prod_description'], 'product_name' =>$m4is_l62046['prod_name'], 'success_action' =>$m4is_l62046['success_action'], 'success_goals' =>$m4is_l62046['success_goals'], 'success_tags' =>$m4is_l62046['success_tags'], 'success_url' =>$m4is_l62046['success_url'], ];
 if($m4is_l62046['autorun']){
$m4is_l97604 =(int) self::$m4is_r1546->m4is_c38461($m4is_n418 );
 if($m4is_l97604){
if($m4is_l62046['success_url']> ''){
$m4is_f56 =$m4is_l62046['success_url'];
 
}
}else{
if($m4is_l62046['fail_url']> ''){
$m4is_f56 =$m4is_l62046['fail_url'];
 
}
}self::$m4is_r1546->m4is_i12(self::$m4is_h21895 );
 wp_redirect($m4is_f56, 302);
 exit;
 
}else{
$m4is_m92735++;
 $m4is_n418 =base64_encode(serialize($m4is_n418));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_n418);
 $form_name ='place_order_button-' . $m4is_m92735;
 $m4is_l62046['button_name']='place_order_button_' . $m4is_m92735;
 $m4is_l60634 =$m4is_l62046['button_url'];
 $m4is_z12867 =$m4is_l62046['css_class'];
 $m4is_i46 ='';
 $m4is_i46 .= "<form name=\"{$form_name
}\" id=\"{$form_name
}\" method=\"post\">";
 $m4is_i46 .= "<input type=\"hidden\" name=\"memb_form_type\" value=\"memb_place_order\">";
 $m4is_i46 .= "<input type=\"hidden\" name=\"form_id\" value=\"{$m4is_m92735
}\">";
 $m4is_i46 .= "<input type=\"hidden\" name=\"order_settings\" value=\"{$m4is_n418
}\">";
 $m4is_i46 .= "<input type=\"hidden\" name=\"digital_signature\" value=\"{$m4is_o31859
}\">";
 $m4is_i46 .= wp_nonce_field('place_order_button-' . $m4is_m92735, '_wpnonce', true, false);
 if($m4is_l60634 == ''){
$m4is_i46 .= "<input type=\"submit\" class=\"{$m4is_z12867
}\" id=\"{$m4is_l62046['button_name']
}\" value=\"{$m4is_l62046['button_text']
}\">";
 
}else{
$m4is_i46 .= "<input type=\"image\" src=\"{$m4is_l60634
}\" class=\"{$m4is_z12867
}\" id=\"{$m4is_l62046['button_name']
}\" >";
 
}$m4is_i46 .= "</form>";
 return $m4is_i46;
 
}
}  public static 
function m4is_p72($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
  if(self::$m4is_r02639 ){
return '';
 
}global $wpdb;
 $m4is_y642 =['action_id' =>0, 'button_url' =>0, 'css_class' =>'', 'error_url' =>'', 'success_url' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}if(!self::$m4is_h21895 ){
return '';
 
}static $m4is_m92735 =1001;
 $m4is_d9476 =_x('Submit', 'memb_one_click_sale', 'memberium');
 if(isset($m4is_l62046['unavailable_text'])){
$m4is_e1928 =$m4is_l62046['unavailable_text'];
 
}else{
$m4is_e1928 =_x('This offer is only available with a qualifying purchase.', 'memb_one_click_sale', 'memberium');
 
}if(isset($_SESSION['is4wp_purchase_session']['contactId'])){
$m4is_h21895 =$_SESSION['is4wp_purchase_session']['contactId'];
 
}else{
return $m4is_e1928;
 
}if(isset($_SESSION['is4wp_purchase_session']['orderId'])){
$m4is_c6016 =$_SESSION['is4wp_purchase_session']['orderId'];
 
}else{
return $m4is_e1928;
 
} if($m4is_m92735 == 0){
$m4is_m92735 =1001;
 
}$m4is_n86039 =$m4is_l62046['action_id'];
 $m4is_l60634 =$m4is_l62046['button_url'];
 $m4is_f56 =$m4is_l62046['success_url'];
 $m4is_h4389 =$m4is_l62046['error_url'];
 $m4is_z12867 =$m4is_l62046['css_class'];
 $m4is_e1928 ='<span class="is4wp_client_error">' . $m4is_e1928 . '</span>';
 if(isset($m4is_l62046['button_text'])&&$m4is_l62046['button_text' > ''])$m4is_d9476 =$m4is_l62046['button_text'];
 if(self::$m4is_h21895 == 0 ||$m4is_n86039 == 0 ||$m4is_c6016 == 0 ||!isset($_GET['infusion_xid'])||!isset($_GET['infusion_type'])||!($_GET['infusion_type']== 'CustomFormSale')){
return $m4is_e1928;
 
} $m4is_a89 =['Id' ];
 $m4is_v76912 =['Id' =>$m4is_c6016, 'ContactId' =>self::$m4is_h21895, ];
 $m4is_b9687 =self::$m4is_z59682->dsLoad('Job', intval($m4is_c6016), $m4is_a89);
 if($m4is_b9687['Id']<> $m4is_c6016){
return $m4is_e1928;
 
}$m4is_a89 =['Id', 'Last4', 'CardType', 'Status', 'ContactId' ];
 $m4is_v76912 =['ContactId' =>$m4is_h21895, ];
 $m4is_v76912 =['ContactId' =>$m4is_h21895, 'CardType' =>$_GET['CreditCard0CardType'], 'ExpirationYear' =>$_GET['CreditCard0ExpirationYear'], 'ExpirationMonth' =>$_GET['CreditCard0ExpirationMonth'], ];
 $m4is_m2196 =m4is_c69807::m4is_o986('CreditCard', 1, 0, $m4is_v76912, $m4is_a89);
 if(is_array($m4is_m2196 )&&count($m4is_m2196 )== 0){
return $m4is_e1928;
 
} $m4is_m92735++;
 $m4is_x29143 ='is4wp_oneclick_sale_form_' . $m4is_m92735;
 $m4is_c8625 ='is4wp_oneclick_sale_button_' . $m4is_m92735;
 $m4is_u076 =base64_encode(serialize(['action_id' =>$m4is_n86039, 'Contact0Email' =>$_GET['Contact0Email'], 'contact_id' =>$m4is_h21895, 'order_id' =>$m4is_c6016, 'redirect_url' =>$m4is_f56, ]));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u076);
 $m4is_i46 ='';
 $m4is_i46 .= "<form name=\"{$m4is_x29143
}\" id=\"{$m4is_x29143
}\" method=\"post\">";
 $m4is_i46 .= "<input type=\"hidden\" name=\"is4wp_form_type\" value=\"is4wp_oneclick_sale\">";
 $m4is_i46 .= "<input type=\"hidden\" name=\"form_id\" value=\"{$m4is_m92735
}\">";
 $m4is_i46 .= "<input type=\"hidden\" name=\"parameters\" value=\"{$m4is_u076
}\">";
 $m4is_i46 .= "<input type=\"hidden\" name=\"digital_signature\" value=\"{$m4is_o31859
}\">";
 $m4is_i46 .= wp_nonce_field('oneclick_sale_' . $m4is_m92735, '_wpnonce', true, false);
 if($m4is_l60634 == ''){
$m4is_i46 .= "<input type=\"submit\" class=\"{$m4is_z12867
}\" id=\"{$m4is_c8625
}\" value=\"{$m4is_d9476
}\">";
 
}else{
$m4is_i46 .= "<input type=\"image\" src=\"{$m4is_l60634
}\" class=\"{$m4is_z12867
}\" id=\"{$m4is_c8625
}\" >";
 
}$m4is_i46 .= "</form>";
 return $m4is_i46;
 
}public static 
function m4is_n67($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
if(self::$m4is_r02639 ){
return '';
 
}$m4is_h21895 =self::$m4is_h21895;
 $m4is_t6601 =(int) get_user_meta(self::$m4is_r1546->m4is_x66(), 'infusionsoft_affiliate', true );
  $m4is_t6601 =$m4is_t6601 or m4is_z13097::m4is_y876($m4is_h21895 );
 $m4is_y642 =['autorun' =>'y',  'bypass_commissions' =>'n', 'debug' =>'n',  'description' =>'',  'has_all_tags' =>'',  'has_any_tag' =>'',  'failure_action' =>'', 'failure_goal' =>'', 'failure_tag' =>'', 'failure_url' =>'', 'id' =>0,  'keep_failed' =>'n',  'lead_affiliate_id' =>$m4is_t6601, 'merchant_id' =>self::$m4is_r1546->m4is_j498('settings', 'merchant_account_id'),  'name' =>'',  'order_title' =>'', 'payment_description' =>'Memberium Online Purchase', 'plan_autocharge' =>'n',  'plan_initial_payment' =>-1,  'plan_max_retries' =>3,  'plan_payment_count' =>1,  'plan_payment_interval' =>30,  'plan_retry_days' =>2,  'plan_start_date' =>'now',  'price' =>-1,  'quantity' =>1, 'sale_affiliate_id' =>$m4is_t6601, 'success_action' =>'', 'success_goal' =>'', 'success_tag' =>'', 'success_url' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['autorun']=m4is_f61::m4is_d8195($m4is_l62046['autorun'], true);
 $m4is_l62046['bypass_commissions']=m4is_f61::m4is_d8195($m4is_l62046['bypass_commissions'], false);
 $m4is_l62046['id']=(int) $m4is_l62046['id'];
 $m4is_l62046['keep_failed']=m4is_f61::m4is_d8195($m4is_l62046['keep_failed'], false);
 $m4is_l62046['lead_affiliate_id']=(int) $m4is_l62046['lead_affiliate_id'];
 $m4is_l62046['merchant_id']=(int) $m4is_l62046['merchant_id'];
 $m4is_l62046['name']=trim($m4is_l62046['name']);
 $m4is_l62046['price']=(double) $m4is_l62046['price'];
 $m4is_l62046['quantity']=(int) $m4is_l62046['quantity'];
 $m4is_l62046['sale_affiliate_id']=(int) $m4is_l62046['sale_affiliate_id'];
 if(!empty($m4is_l62046['m4is_l5918'])){
if(!self::$m4is_r1546->m4is_m2480($m4is_l62046['has_any_tag'])){
return '';
 
}
}if(!empty($m4is_l62046['m4is_x13466'])){
if(!self::$m4is_r1546->m4is_x13466($m4is_l62046['has_all_tags'])){
return '';
 
}
}$m4is_h21895 =(int) self::$m4is_h21895;
 $m4is_u807 =0;
 $m4is_c6016 =0;
 $m4is_j098 =m4is_v87365::m4is_d96640();
 $m4is_r6249 =true;
  $m4is_c05328 =$m4is_l62046['failure_action'];
 $m4is_v8723 =$m4is_l62046['failure_goal'];
 $m4is_p786 =$m4is_l62046['failure_tag'];
 $m4is_n6062 =$m4is_l62046['failure_url'];
 if(isset($m4is_j098[$m4is_l62046['id']])){
$m4is_h1438 =$m4is_j098[$m4is_l62046['id']];
 
}elseif($m4is_l62046['name']> ''){
$m4is_l62046['id']=0;
 foreach($m4is_j098 as $m4is_h1438){
if(0 === strcasecmp ($m4is_h1438['ProductName'], $m4is_l62046['name'])&&$m4is_h1438['Status']== 1){
$m4is_l62046['id']=(int) $m4is_h1438['Id'];
 break;
 
}
}
}unset($m4is_j098);
  if($m4is_l62046['id']< 1){
$m4is_r6249 =false;
 
}if($m4is_l62046['quantity']< 1){
$m4is_r6249 =false;
 
}if($m4is_r6249 &&$m4is_l62046['merchant_id']< 0){
$m4is_r6249 =false;
 
}if($m4is_r6249 &&$m4is_l62046['price']< 0){
$m4is_l62046['price']=(double) $m4is_h1438['ProductPrice'];
 
}  if($m4is_r6249){
if(!((int) $m4is_u807)){
$m4is_p1054 =m4is_c01675::m4is_w68604($m4is_h21895 );
 $m4is_p1054 =m4is_c01675::m4is_s04($m4is_p1054 );
 $m4is_u807 =(int) $m4is_p1054['Id'];
 
}
} if($m4is_r6249){
$m4is_l62046['order_title']=empty($m4is_l62046['order_title'])? $m4is_h1438['ProductName']: $m4is_l62046['order_title'];
 $m4is_j2450 =date_default_timezone_get();
 date_default_timezone_set ('America/New_York');
 $m4is_z81 =date('Ymd\TH:i:s');
 $m4is_l97604 =self::$m4is_z59682->blankOrder($m4is_h21895, $m4is_l62046['order_title'], $m4is_z81, $m4is_l62046['lead_affiliate_id'], $m4is_l62046['sale_affiliate_id']);
 if($m4is_l97604 > 0){
$m4is_l97604 =(int) $m4is_l97604;
 $m4is_u6591 =self::$m4is_z59682->addOrderItem($m4is_l97604, $m4is_l62046['id'], 4, $m4is_l62046['price'], $m4is_l62046['quantity'], $m4is_h1438['ProductName'], $m4is_l62046['description']);
 if($m4is_h1438['Taxable']||$m4is_h1438['CountryTaxable']||$m4is_h1438['StateTaxable']||$m4is_h1438['CityTaxable']){
self::$m4is_z59682->recalculateTax($m4is_l97604);
 
}$m4is_x96 =self::$m4is_z59682->chargeInvoice($m4is_l97604, $m4is_l62046['payment_description'], $m4is_u807, $m4is_l62046['merchant_id'], (bool) $m4is_l62046['bypass_commissions']);
 if(!in_array(strtolower($m4is_x96['Code']), ['declined', 'error'])){
 $m4is_c05328 =$m4is_l62046['success_action'];
 $m4is_v8723 =$m4is_l62046['success_goal'];
 $m4is_p786 =$m4is_l62046['success_tag'];
 $m4is_n6062 =$m4is_l62046['success_url'];
 
}else{
 if(!$m4is_l62046['keep_failed']){
self::$m4is_z59682->deleteInvoice($m4is_l97604);
 
}
}
}date_default_timezone_set ($m4is_j2450);
 
}if(!empty($m4is_c05328)){
self::$m4is_r1546->m4is_u71903($m4is_c05328, $m4is_h21895);
 
}if(!empty($m4is_v8723)){
self::$m4is_r1546->m4is_t64038($m4is_v8723, $m4is_h21895);
 
}if(!empty($m4is_p786)){
self::$m4is_r1546->m4is_k98($m4is_p786, $m4is_h21895);
 
}self::$m4is_r1546->m4is_x4831($m4is_h21895);
 if(!empty($m4is_n6062)){
 echo '<script type="text/javascript"> window.location.replace("', $m4is_n6062, '"); </script>';
 exit;
 
}
}public static 
function m4is_n6290($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_b348 =false;
 $m4is_y642 =['affiliate_id' =>0, 'allow_dupe' =>'n', 'autorun' =>'y',  'debug' =>'n', 'delay_days' =>0, 'dupe_action' =>'', 'dupe_goal' =>'', 'dupe_tag' =>'', 'dupe_url' =>'', 'failure_action' =>'', 'failure_goal' =>'', 'failure_tag' =>'', 'failure_url' =>'', 'first_only' =>'y', 'm4is_x13466' =>'', 'm4is_l5918' =>'', 'id' =>0, 'keep_failed' =>'n', 'merchant_id' =>self::$m4is_r1546->m4is_j498('settings', 'merchant_account_id' ), 'price' =>-1, 'quantity' =>1, 'success_action' =>'', 'success_goal' =>'', 'success_tag' =>'', 'success_url' =>'', 'taxable' =>'n', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['m4is_l5918']=trim($m4is_l62046['m4is_l5918']);
 $m4is_l62046['m4is_x13466']=trim($m4is_l62046['m4is_x13466']);
 if(!empty($m4is_l62046['m4is_l5918'])){
if(!self::$m4is_r1546->m4is_m2480($m4is_l62046['m4is_l5918'])){
return '';
 
}
}if(!empty($m4is_l62046['m4is_x13466'])){
if(!self::$m4is_r1546->m4is_x13466($m4is_l62046['m4is_x13466'])){
return '';
 
}
}$m4is_u807 =0;
 $m4is_u66 =0;
 $m4is_r6249 =true;
 $m4is_h21895 =self::$m4is_h21895;
 $m4is_d74 =self::$m4is_r1546->m4is_i916();
 $m4is_l62046['allow_dupe']=m4is_f61::m4is_d8195($m4is_l62046['allow_dupe'], false );
 $m4is_l62046['autorun']=m4is_f61::m4is_d8195($m4is_l62046['autorun'], true );
 $m4is_l62046['debug']=m4is_f61::m4is_d8195($m4is_l62046['debug'], true );
 $m4is_l62046['first_only']=m4is_f61::m4is_d8195($m4is_l62046['first_only'], true );
 $m4is_l62046['keep_failed']=m4is_f61::m4is_d8195($m4is_l62046['keep_failed'], false );
 $m4is_l62046['taxable']=m4is_f61::m4is_d8195($m4is_l62046['taxable'], false );
 $m4is_l62046['delay_days']=(int) $m4is_l62046['delay_days'];
 $m4is_l62046['id']=(int) $m4is_l62046['id'];
 $m4is_l62046['merchant_id']=(int) $m4is_l62046['merchant_id'];
 $m4is_l62046['quantity']=(int) $m4is_l62046['quantity'];
 if(empty($m4is_l62046['affiliate_id'])){
$m4is_l62046['affiliate_id']=self::$m4is_r1546->m4is_g72069($m4is_h21895 );
 
}if($m4is_l62046['first_only']&&$m4is_b348 ){
if($m4is_l62046['debug'])echo 'Already Executed.  Stopping.<br>';
 return '';
 
}$m4is_b348 =true;
  if(!isset($m4is_d74[$m4is_l62046['id']])||$m4is_d74[$m4is_l62046['id']]['Active']== 0){
$m4is_r6249 =false;
 
}if($m4is_l62046['delay_days']< 0){
$m4is_r6249 =false;
 
}if($m4is_l62046['id']< 0){
$m4is_r6249 =false;
 
}if($m4is_l62046['merchant_id']< 0){
$m4is_r6249 =false;
 
}if($m4is_l62046['price']< 0){
if(isset($m4is_d74[$m4is_l62046['id']]['PlanPrice'])){
$m4is_l62046['price']=(double) $m4is_d74[$m4is_l62046['id']]['PlanPrice'];
 
}else{
$m4is_r6249 =false;
 
}
}$m4is_l62046['price']=(double) $m4is_l62046['price'];
 if($m4is_l62046['quantity']< 1){
$m4is_r6249 =false;
 
}if($m4is_r6249){
 if(!((int) $m4is_u807)){
$m4is_p1054 =m4is_c01675::m4is_w68604($m4is_h21895 );
 $m4is_p1054 =m4is_c01675::m4is_s04($m4is_p1054 );
 $m4is_u807 =(int) $m4is_p1054['Id'];
 if($m4is_l62046['debug'])echo 'Credit Card ID = ', $m4is_u807, '<br>';
 
}
}if(!$m4is_u807 ){
$m4is_r6249 =false;
 
}$m4is_c05328 =$m4is_l62046['failure_action'];
 $m4is_v8723 =$m4is_l62046['failure_goal'];
 $m4is_p786 =$m4is_l62046['failure_tag'];
 $m4is_n6062 =$m4is_l62046['failure_url'];
 if($m4is_r6249){
$m4is_b348 =true;
 $m4is_u66 =self::$m4is_z59682->addRecurringAdv($m4is_h21895, $m4is_l62046['allow_dupe'], $m4is_l62046['id'], $m4is_l62046['quantity'], $m4is_l62046['price'], $m4is_l62046['taxable'], $m4is_l62046['merchant_id'], $m4is_u807, $m4is_l62046['affiliate_id'], $m4is_l62046['delay_days']);
 if($m4is_l62046['debug'])echo 'Subscription ID  = ', $m4is_u66, '<br>';
 if((int) $m4is_u66 > 0){
$m4is_l97604 =self::$m4is_z59682->recurringInvoice($m4is_u66);
 $m4is_u6591 =self::$m4is_z59682->chargeInvoice($m4is_l97604, _x('Memberium Subscription Purchase', 'memb_order_subscription', 'memberium'), $m4is_u807, $m4is_l62046['merchant_id'], false);
 if(!in_array($m4is_u6591['Code'], ['Declined', 'Error'])){
$m4is_c05328 =$m4is_l62046['success_action'];
 $m4is_v8723 =$m4is_l62046['success_goal'];
 $m4is_p786 =$m4is_l62046['success_tag'];
 $m4is_n6062 =$m4is_l62046['success_url'];
 
}else{
if(!$m4is_l62046['keep_failed']){
self::$m4is_z59682->deleteSubscription($m4is_u66);
 
}
}
}else{
if(stripos($m4is_u66, 'Duplicate order')){
if($m4is_l62046['debug'])echo 'Duplicate Order Detected<br>';
 $m4is_c05328 =$m4is_l62046['dupe_action'];
 $m4is_v8723 =$m4is_l62046['dupe_goal'];
 $m4is_p786 =$m4is_l62046['dupe_tag'];
 $m4is_n6062 =$m4is_l62046['dupe_url'];
 
}
}
}if(!empty($m4is_c05328)){
if($m4is_l62046['debug'])echo 'Running Actionsets = ', $m4is_c05328, '<br>';
 self::$m4is_r1546->m4is_u71903($m4is_c05328, $m4is_h21895);
 
}if(!empty($m4is_v8723)){
if($m4is_l62046['debug'])echo 'Running Goals = ', $m4is_v8723, '<br>';
 self::$m4is_r1546->m4is_t64038($m4is_v8723, $m4is_h21895);
 
}if(!empty($m4is_p786)){
if($m4is_l62046['debug'])echo 'Adding/Removing Tags = ', $m4is_p786, '<br>';
 self::$m4is_r1546->m4is_k98($m4is_p786, $m4is_h21895);
 
}if($m4is_l62046['debug'])echo 'Syncing Contact = ', $m4is_p786, '<br>';
 self::$m4is_r1546->m4is_x4831($m4is_h21895);
 if($m4is_l62046['debug'])echo 'Redirect URL = ', $m4is_n6062, '<br>';
 if($m4is_l62046['debug'])return '';
 if($m4is_l62046['first_only']&&!empty($m4is_n6062)){
echo '<script type="text/javascript"> window.location.replace("', $m4is_n6062, '"); </script>';
 exit;
 
}
} public static 
function m4is_b658($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}static $m4is_m92735 =1;
 m4is_j586::m4is_x7134();
 $m4is_y642 =['autorun' =>0, 'button_text' =>'Buy Now', 'button_url' =>'', 'clicked_button_text' =>'Processing Order', 'contact_id' =>self::$m4is_h21895, 'creditcard_id' =>0, 'css_class' =>'', 'debug' =>0, 'delete_failed' =>1, 'failure_actionset' =>0, 'failure_goal' =>'', 'failure_tag' =>0, 'failure_url' =>'', 'lead_affiliate_id' =>0, 'nojs' =>0, 'order_title' =>'Memberium 1 Click Order', 'plan_id' =>0, 'process_specials' =>true, 'product_ids' =>'', 'promo_codes' =>'', 'sale_affiliate_id' =>0, 'subscription_ids' =>'', 'success_actionset' =>0, 'success_goal' =>'', 'success_tag' =>0, 'success_url' =>'', ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['nojs']=m4is_f61::m4is_d8195($m4is_l62046['nojs'], false );
 $m4is_l62046['debug']=m4is_f61::m4is_d8195($m4is_l62046['debug'], false );
 if($m4is_l62046['contact_id']< 1){
if($m4is_l62046['debug']){
echo '<p style="font-weight:bold;color:red">No contact specified.</p>';
 
}return '';
 
}if(empty($m4is_l62046['product_ids'])){
$m4is_j6089 =0;
 
}if(empty($m4is_l62046['product_ids'])&&empty($m4is_l62046['subscription_ids'])){
return '';
 
}if($m4is_l62046['creditcard_id']== 0){
$m4is_p1054 =m4is_c01675::m4is_w68604($m4is_l62046['contact_id']);
 if(is_array($m4is_p1054)&&!empty($m4is_p1054)){
$m4is_d895 =m4is_c01675::m4is_s04($m4is_p1054 );
 $m4is_l62046['creditcard_id']=$m4is_d895['Id'];
 
}else{
return '';
 
}
}if(!empty($_COOKIE['infusionsoft_affiliate'])){
$m4is_l62046['sale_affiliate_id']=empty($m4is_l62046['sale_affiliate_id'])? (int) $_COOKIE['infusionsoft_affiliate']: $m4is_l62046['sale_affiliate_id'];
 $m4is_l62046['lead_affiliate_id']=empty($m4is_l62046['lead_affiliate_id'])? (int) $_COOKIE['infusionsoft_affiliate']: $m4is_l62046['lead_affiliate_id'];
 
}$m4is_j6089 =(int) $m4is_l62046['plan_id'];
 $m4is_l62046['process_specials']=(boolean) $m4is_l62046['process_specials'];
 $m4is_l62046['product_ids']=array_filter(array_map('trim', explode(',', $m4is_l62046['product_ids'])));
 $m4is_l62046['product_ids']=array_filter(array_map('intval', $m4is_l62046['product_ids']));
 $m4is_l62046['promo_codes']=array_filter(array_map('trim', explode(',', $m4is_l62046['promo_codes'])));
 $m4is_l62046['subscription_ids']=array_filter(array_map('trim', explode(',', $m4is_l62046['subscription_ids'])));
 $m4is_l62046['subscription_ids']=array_filter(array_map('intval', $m4is_l62046['subscription_ids']));
 $m4is_u897 =['contact_id' =>$m4is_l62046['contact_id'], 'creditcard_id' =>$m4is_l62046['creditcard_id'], 'debug' =>$m4is_l62046['debug'], 'delete_failed' =>$m4is_l62046['delete_failed'], 'failure_actionset' =>$m4is_l62046['failure_actionset'], 'failure_goal' =>$m4is_l62046['failure_goal'], 'failure_tag' =>$m4is_l62046['failure_tag'], 'failure_url' =>$m4is_l62046['failure_url'], 'lead_affiliate_id' =>$m4is_l62046['lead_affiliate_id'], 'order_title' =>$m4is_l62046['order_title'], 'payplan_id' =>$m4is_j6089, 'process_specials' =>$m4is_l62046['process_specials'], 'product_ids' =>$m4is_l62046['product_ids'], 'promo_codes' =>$m4is_l62046['promo_codes'], 'sale_affiliate_id' =>$m4is_l62046['sale_affiliate_id'], 'subscription_ids' =>$m4is_l62046['subscription_ids'], 'success_actionset' =>$m4is_l62046['success_actionset'], 'success_goal' =>$m4is_l62046['success_goal'], 'success_tag' =>$m4is_l62046['success_tag'], 'success_url' =>$m4is_l62046['success_url'], ];
 $m4is_u897 =base64_encode(serialize($m4is_u897));
 $m4is_o31859 =self::$m4is_r1546->m4is_r626($m4is_u897);
 $m4is_x29143 ='memb_placeorder_form-' . $m4is_m92735;
 $m4is_c8625 ='memb_placeorder_button_' . $m4is_m92735;
  $m4is_i46 ='';
 $m4is_i46 .= "<form name='{$m4is_x29143
}' id='{$m4is_x29143
}' method='post'>";
 $m4is_i46 .= "<input type='hidden' name='memb_form_type' value='memb_placeorder_button'>";
 $m4is_i46 .= "<input type='hidden' name='params' value='{$m4is_u897
}'>";
 $m4is_i46 .= "<input type='hidden' name='form_id' value='{$m4is_m92735
}'>";
 $m4is_i46 .= "<input type='hidden' name='digital_signature' value='{$m4is_o31859
}'>";
 $m4is_i46 .= wp_nonce_field('memb_placeorder_' . $m4is_m92735, '_wpnonce', true, false);
 if(empty($m4is_l62046['button_url'])){
$m4is_i46 .= "<input type='submit' class='{$m4is_l62046['css_class']
}' id='{$m4is_c8625
}' value='{$m4is_l62046['button_text']
}'>";
 
}else{
$m4is_i46 .= "<input type='image' src='{$m4is_l62046['button_url']
}' class='{$m4is_l62046['css_class']
}' id='{$m4is_c8625
}'>";
 
}$m4is_i46 .= "</form>";
 if(!$m4is_l62046['nojs']){
$m4is_i46 .= "
			<script>
			jQuery(document).ready(function() {
				jQuery('#{$m4is_c8625
}').on('click', function() {
					jQuery('#{$m4is_c8625
}').prop('value', '{$m4is_l62046['clicked_button_text']
}');
					jQuery('#{$m4is_c8625
}').attr('disabled', 'disabled');
					jQuery('#{$m4is_x29143
}').submit();
					return true;
				});
			});
			</script>
			";
 
}$m4is_m92735++;
 return $m4is_i46;
 
} public static 
function m4is_u6925($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 =''): string {
if(self::$m4is_r02639 ){
return '';
 
}if(is_feed()){
return '';
 
} $m4is_y642 =['field' =>'invoicetotal', 'order_id' =>isset($_GET['orderId'])? $_GET['orderId']: 0, 'refresh' =>false, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts'){
return implode(',', array_keys($m4is_y642));
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['refresh']=m4is_f61::m4is_d8195($m4is_l62046['refresh'], false);
 $m4is_l62046['field']=strtolower(trim($m4is_l62046['field']));
  if(!$m4is_l62046['refresh']){
if(empty($m4is_l62046['order_id'])){
return '';
 
}
} $m4is_f6029 =['Contact0Email', 'inf_field_Email', ];
 foreach($m4is_f6029 as $m4is_r637){
if(empty($m4is_f4930)){
$m4is_f4930 =empty($_GET[$m4is_r637])? '' : $_GET[$m4is_r637];
 
}
}if(empty($m4is_f4930 )){
$m4is_f4930 =m4is_q82::m4is_d6758(self::$m4is_r1546->m4is_x66(), 'memb_user', 'email', '' );
 
}if(empty($m4is_f4930 )){
return '';
 
}$m4is_h21895 =self::$m4is_r1546->m4is_o70($m4is_f4930);
 if(empty($m4is_h21895)){
return '';
 
}if($m4is_l62046['refresh']){
if(!$m4is_h21895){
return '';
 
}
}if($m4is_l62046['refresh']||(self::$m4is_r1546->m4is_c835()< $m4is_l62046['order_id'])){
m4is_v87365::m4is_l318();
 
}if($m4is_l62046['refresh']){
$m4is_l62046['order_id']=self::$m4is_r1546->m4is_h37260($m4is_h21895);
 
}$m4is_d01 =self::$m4is_r1546->m4is_v31259($m4is_h21895);
 $m4is_t4853 =['invoicetotal' =>0, 'totaldue' =>0, 'totalpaid' =>0, ];
 if(is_array($m4is_d01)){
foreach($m4is_d01 as $m4is_t06897){
if($m4is_t06897['JobId']== $m4is_l62046['order_id']){
$m4is_t4853['invoicetotal']=$m4is_t4853['invoicetotal']+ $m4is_t06897['InvoiceTotal'];
 $m4is_t4853['totaldue']=$m4is_t4853['totaldue']+ $m4is_t06897['TotalDue'];
 $m4is_t4853['totalpaid']=$m4is_t4853['totalpaid']+ $m4is_t06897['TotalPaid'];
 
}
}
}return isset($m4is_t4853[$m4is_l62046['field']])? $m4is_t4853[$m4is_l62046['field']]: 0;
 
} public static 
function m4is_c36($m4is_l62046 =[], string $m4is_t09761 ='', string $m4is_v3458 ='' ): string {
if(self::$m4is_r02639 ){
return '';
 
}if(!is_user_logged_in()){
return '';
 
}if(is_feed()){
return '';
 
}$m4is_f4930 =m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', 'email', '' );
 $m4is_m676 =m4is_q82::m4is_k660(self::$m4is_r1546->m4is_x66(), 'contact', 'password', '' );
 $m4is_y642 =['css_id' =>'', 'height' =>720, 'mode' =>'url',  'width' =>540, ];
 if(isset($m4is_l62046[0])&&$m4is_l62046[0]== 'showatts' ){
return implode(',', array_keys($m4is_y642 ));
 
}if(empty($m4is_f4930)||empty($m4is_m676)){
return '';
 
}$m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium');
 $m4is_l62046['mode']=strtolower(trim($m4is_l62046['mode']));
  $m4is_r9613 =self::$m4is_r9613;
 $m4is_d2371 =(int) $m4is_l62046['height'];
 $m4is_i63785 =(int) $m4is_l62046['width'];
 $m4is_f4930 =urlencode($m4is_f4930);
 $m4is_m676 =urlencode($m4is_m676);
 $m4is_o498 ='';
 if($m4is_l62046['mode']== 'iframe'){
$m4is_o498 ='<iframe src="https://' . $m4is_r9613 . '.infusionsoft.com/ClientLogin/loginProcess.jsp?email=' . $m4is_f4930 . '&amp;password=' . $m4is_m676 . '&amp;Login=Login" width="720" height="540" frameborder="0" scrolling="auto"></iframe>';
 
}elseif($m4is_l62046['mode']== 'url'){
$m4is_o498 ='https://' . $m4is_r9613 . '.infusionsoft.com/ClientLogin/loginProcess.jsp?email=' . $m4is_f4930 . '&password=' . $m4is_m676 . '&Login=Login';
 
}elseif($m4is_l62046['mode']== 'link'){
$m4is_o498 ='<a target="clientlogin" href="https://' . $m4is_r9613 . '.infusionsoft.com/ClientLogin/loginProcess.jsp?email=' . $m4is_f4930 . '&password=' . $m4is_m676 . '&Login=Login">Click Me</a>';
 
}elseif($m4is_l62046['mode']== 'redirect'){

}return $m4is_o498;
 
}   
}

