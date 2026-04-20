<?php
 class_exists('m4is_r83' )||die();
  final 
class m4is_x2469 {
private $m4is_r1546;
  static 
function m4is_c26(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
}private 
function __construct(){
$this->m4is_i702();
 $this->m4is_f3276();
 $this->m4is_d4861();
 $this->m4is_s2469();
 
}private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 
}private 
function m4is_f3276(): void {
if(is_admin()&&include_once __DIR__ . '/admin.php' ){
m4is_v94::m4is_c26();
 
} 
}private 
function m4is_d4861(){
add_filter('gform_pre_render', [$this, 'm4is_f19'], 1, 3);
 add_filter('gform_after_submission', [$this, 'm4is_g7210'], 10, 2);
 add_filter('gform_field_value_memb_city', [$this, 'm4is_y061']);
 add_filter('gform_field_value_memb_country', [$this, 'm4is_a721']);
 add_filter('gform_field_value_memb_email', [$this, 'm4is_y6695']);
 add_filter('gform_field_value_memb_firstname', [$this, 'm4is_a4031']);
 add_filter('gform_field_value_memb_firstname', [$this, 'm4is_d49651']);
 add_filter('gform_field_value_memb_lastname', [$this, 'm4is_m92']);
 add_filter('gform_field_value_memb_phone', [$this, 'm4is_m63']);
 add_filter('gform_field_value_memb_postalcode', [$this, 'm4is_o23']);
 add_filter('gform_field_value_memb_state', [$this, 'm4is_p90']);
 add_filter('gform_field_value_memb_streetaddress1', [$this, 'm4is_s14306']);
 add_filter('gform_field_value_memb_streetaddress2', [$this, 'm4is_b3066']);
 add_action('gform_editor_js', [$this, 'm4is_g1625']);
 add_action('gform_field_advanced_settings', [$this, 'm4is_l952'], 10, 2 );
 add_action('gform_post_payment', [$this, 'm4is_e6210'], 10, 2 );
 add_filter('gform_form_settings', [$this, 'm4is_w62637'], 10, 2 );
 add_filter('gform_pre_form_settings_save', [$this, 'm4is_a7208'], 10, 2 );
 
}
function m4is_a7208($m4is_m14 ){
$m4is_m14['memberiumformtag']=rgpost('memberiumformtag' );
 return $m4is_m14;
 
} 
function m4is_e6210($m4is_e03627, $m4is_c05328 ){
 
}
function m4is_l952($m4is_u75934, $m4is_m92735 ){
static $seen =[];
 if(!empty($seen[$m4is_m92735][$m4is_u75934])){
return;
 
}if($m4is_u75934 == 50 ){
$m4is_a89 =m4is_c69807::m4is_f5248('Contact');
 $m4is_n8957 =[$this->m4is_r1546->m4is_j498('settings', 'password_field'), ];
 foreach($m4is_a89 as $m4is_l9671 =>$m4is_r637){
if(in_array($m4is_r637, $m4is_n8957 )){
unset($m4is_a89[$m4is_l9671]);
 
}
}unset($m4is_l9671, $m4is_n8957 );
 ?>
			<!-- li class="default_value_setting admin_label_setting field_setting" -->
			<li class="admin_label_setting field_setting">
			<label for="field_admin_label" class="section_label">
			Memberium Sync
			<?php ?>

			</label>
			<select id="memberiumfieldsync" onchange="SetFieldProperty('memberiumfieldsync', this.value);" >
			<option value="">(None)</option>
			<?php
 if(is_array($m4is_a89 )){
foreach($m4is_a89 as $m4is_q523 ){
printf('<option value="%s">%s</option>', $m4is_q523, $m4is_q523 );
 
}
}?>
			</select>
			</li>
			<?php
 
}$seen[$m4is_m92735][$m4is_u75934]=1;
 
}
function m4is_w62637($m4is_e0213, $m4is_m14 ){
$m4is_l9321 =m4is_k865::m4is_z2906();
 $m4is_v586 =rgar($m4is_m14, 'memberiumformtag' );
  $m4is_r18 ='<option value="">(None)</option>';
 foreach($m4is_l9321['mc']as $m4is_o015 =>$m4is_k72 ){
$m4is_a437 =$m4is_v586== $m4is_o015 ? ' selected=selected ' : '';
 $m4is_r18 .= sprintf('<option value="%s" %s >%s</option>', $m4is_o015, $m4is_a437, $m4is_k72 );
 
}unset($m4is_l9321, $m4is_v586 );
 $m4is_e0213['Form Options']['memberiumformtag']='
		<tr>
		<th><label for="memberiumformtag">Memberium Form Tag</label></th>
		<td><select name="memberiumformtag">' . $m4is_r18 . '</select></td>
		</tr>';
 return $m4is_e0213;
 
}
function m4is_g1625(){
?>
		<script type='text/javascript'>
		//adding setting to fields of type "text"
		fieldSettings.text += ", .memberiumfieldsync";

		//binding to the load field settings event to initialize the checkbox
		jQuery(document).bind("gform_load_field_settings", function(event, field, form){
			jQuery("#memberiumfieldsync").val(field["memberiumfieldsync"]);
		});
		</script>
		<?php
 
}
function m4is_s2469(){
add_shortcode('memb_gravityform_field', [$this, 'm4is_q4609']);
 
}
function m4is_x321($m4is_w5867 ='', $m4is_n246 =''){
$m4is_v586 ='';
 if(!empty($m4is_w5867 )){
if(substr($m4is_w5867, 0, 13 )== 'memb.contact.' ){
$m4is_r637 =strtolower(substr($m4is_w5867, 13 ));
 $m4is_v586 =m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', $m4is_r637, '' );
 
}
}if(empty($m4is_v586 )&&!empty($m4is_n246 )){
$m4is_v586 =$m4is_n246;
 
}return do_shortcode($m4is_v586 );
 
}
function m4is_g7210($m4is_e03627, $m4is_m14 ){
if(is_array($m4is_m14['fields'])){
$m4is_i641 =[];
 foreach($m4is_m14['fields']as $m4is_m661 =>$m4is_q523 ){
$m4is_j379 =empty($m4is_q523->memberiumfieldsync)? '' : $m4is_q523->memberiumfieldsync;
 if(!empty($m4is_j379)){
if($m4is_q523->type == 'address'){
$m4is_d07693 =$m4is_q523->id;
 $m4is_f69781 =[];
 if($m4is_j379 == 'StreetAddress1'){
$m4is_f69781 =['street1' =>'StreetAddress1', 'street2' =>'StreetAddress2', 'city' =>'City', 'state' =>'State', 'postalcode' =>'PostalCode', 'country' =>'Country', ];
 
}elseif($m4is_j379 == 'Address2Street1'){
$m4is_f69781 =['street1' =>'Address2Street1', 'street2' =>'Address2Street2', 'city' =>'City2', 'state' =>'State2', 'postalcode' =>'PostalCode2', 'country' =>'Country2', ];
 
}elseif($m4is_j379 == 'Address3Street1'){
$m4is_f69781 =['street1' =>'Address3Street1', 'street2' =>'Address3Street2', 'city' =>'City3', 'state' =>'State3', 'postalcode' =>'PostalCode3', 'country' =>'Country3', ];
 
}if(!empty($m4is_f69781)){
$m4is_l9671 ="{$m4is_d07693
}.1";
 if(isset($m4is_e03627[$m4is_l9671])){
$m4is_r637 =$m4is_f69781['street1'];
 if(empty($m4is_i641[$m4is_r637])){
$m4is_i641[$m4is_r637]=trim($m4is_e03627[$m4is_l9671]);
 
}
}$m4is_l9671 ="{$m4is_d07693
}.2";
 if(isset($m4is_e03627[$m4is_l9671])){
$m4is_r637 =$m4is_f69781['street2'];
 if(empty($m4is_i641[$m4is_r637])){
$m4is_i641[$m4is_r637]=trim($m4is_e03627[$m4is_l9671]);
 
}
}$m4is_l9671 ="{$m4is_d07693
}.3";
 if(isset($m4is_e03627[$m4is_l9671])){
$m4is_r637 =$m4is_f69781['street2'];
 if(empty($m4is_i641[$m4is_r637])){
$m4is_i641[$m4is_r637]=trim($m4is_e03627[$m4is_l9671]);
 
}
}$m4is_l9671 ="{$m4is_d07693
}.4";
 if(isset($m4is_e03627[$m4is_l9671])){
$m4is_r637 =$m4is_f69781['state'];
 if(empty($m4is_i641[$m4is_r637])){
$m4is_i641[$m4is_r637]=trim($m4is_e03627[$m4is_l9671]);
 
}
}$m4is_l9671 ="{$m4is_d07693
}.5";
 if(isset($m4is_e03627[$m4is_l9671])){
$m4is_r637 =$m4is_f69781['postalcode'];
 if(empty($m4is_i641[$m4is_r637])){
$m4is_i641[$m4is_r637]=trim($m4is_e03627[$m4is_l9671]);
 
}
}$m4is_l9671 ="{$m4is_d07693
}.6";
 if(isset($m4is_e03627[$m4is_l9671])){
$m4is_r637 =$m4is_f69781['country'];
 if(empty($m4is_i641[$m4is_r637])){
$m4is_i641[$m4is_r637]=trim($m4is_e03627[$m4is_l9671]);
 
}
}
}
}elseif($m4is_q523->type == 'name'){
$m4is_d07693 =$m4is_q523->id;
 if($m4is_j379 == 'FirstName'){
$m4is_l9671 ="{$m4is_d07693
}.2";
 if(isset($m4is_e03627[$m4is_l9671])){
if(empty($m4is_i641['Title'])){
$m4is_i641['Title']=trim($m4is_e03627[$m4is_l9671]);
 
}
}$m4is_l9671 ="{$m4is_d07693
}.3";
 if(isset($m4is_e03627[$m4is_l9671])){
if(empty($m4is_i641['FirstName'])){
$m4is_i641['FirstName']=trim($m4is_e03627[$m4is_l9671]);
 
}
}$m4is_l9671 ="{$m4is_d07693
}.4";
 if(isset($m4is_e03627[$m4is_l9671])){
if(empty($m4is_i641['MiddleName'])){
$m4is_i641['MiddleName']=trim($m4is_e03627[$m4is_d07693.'.4']);
 
}
}$m4is_l9671 ="{$m4is_d07693
}.6";
 if(isset($m4is_e03627[$m4is_l9671])){
if(empty($m4is_i641['LastName'])){
$m4is_i641['LastName']=trim($m4is_e03627[$m4is_l9671]);
 
}
}$m4is_l9671 ="{$m4is_d07693
}.8";
 if(isset($m4is_e03627[$m4is_l9671])){
if(empty($m4is_i641['Suffix'])){
$m4is_i641['Suffix']=trim($m4is_e03627[$m4is_l9671]);
 
}
}
}else{
$m4is_x39 =[];
 $m4is_l9671 ="{$m4is_d07693
}.2";
 if(!empty($m4is_e03627[$m4is_l9671])){
$m4is_x39[]=$m4is_e03627[$m4is_l9671];
 
}$m4is_l9671 ="{$m4is_d07693
}.3";
 if(!empty($m4is_e03627[$m4is_l9671])){
$m4is_x39[]=$m4is_e03627[$m4is_l9671];
 
}$m4is_l9671 ="{$m4is_d07693
}.4";
 if(!empty($m4is_e03627[$m4is_l9671])){
$m4is_x39[]=$m4is_e03627[$m4is_l9671];
 
}$m4is_l9671 ="{$m4is_d07693
}.6";
 if(!empty($m4is_e03627[$m4is_l9671])){
$m4is_x39[]=$m4is_e03627[$m4is_l9671];
 
}$m4is_l9671 ="{$m4is_d07693
}.8";
 if(!empty($m4is_e03627[$m4is_l9671])){
$m4is_x39[]=$m4is_e03627[$m4is_l9671];
 
}if(empty($m4is_i641[$m4is_j379])){
$m4is_i641[$m4is_j379]=trim(implode(' ', $m4is_x39));
 
}
}
}elseif($m4is_q523->type == 'list'){
   
}elseif($m4is_q523->type == 'date' ){
$m4is_v586 =trim($m4is_e03627[$m4is_q523->id]);
 if(!empty($m4is_v586)){
if(empty($m4is_i641[$m4is_j379])){
$m4is_i641[$m4is_j379]=date('Ymd\TH:i:s', strtotime($m4is_v586));
 
}
}
}elseif($m4is_q523->type == 'number'){
$m4is_i641[$m4is_j379]=(string) trim($m4is_e03627[$m4is_q523->id]);
 
}elseif(in_array($m4is_q523->type, ['checkbox'])){
$m4is_x39 =[];
 foreach ($m4is_q523->inputs as $input_id =>$input){
$m4is_n20 =$input['id'];
 if(!empty($m4is_e03627[$m4is_n20])){
$m4is_x39[]=$m4is_e03627[$m4is_n20];
 
}
}if(substr($m4is_x39, 0, 2 )== '["' ){
$m4is_x39 =json_decode($m4is_x39 );
 
}else{
$m4is_x39 =implode(',', $m4is_x39 );
 
}if(empty($m4is_i641[$m4is_j379])){
$m4is_i641[$m4is_j379]=$m4is_x39;
 
}
}elseif(in_array($m4is_q523->type, ['multiselect'])){
$m4is_x39 =$m4is_e03627[$m4is_q523->id];
 if(substr($m4is_x39, 0, 2)== '["'){
$m4is_x39 =implode(',', json_decode($m4is_x39));
 
}if(empty($m4is_i641[$m4is_j379])){
$m4is_i641[$m4is_j379]=$m4is_x39;
 
}
}else{
if(empty($m4is_i641[$m4is_j379])){
$m4is_i641[$m4is_j379]=trim($m4is_e03627[$m4is_q523->id]);
 
}
}
}
}if(!empty($m4is_i641)){
$m4is_f087 =$this->m4is_r1546->m4is_x66();
 $m4is_p4935 =m4is_r83::m4is_c26()->m4is_j498('settings', 'username_field');
 $m4is_r6234 =m4is_r83::m4is_c26()->m4is_j498('settings', 'password_field');
 $m4is_x51 =is_user_logged_in();
 $m4is_d0664 =strtolower(m4is_q82::m4is_k660($m4is_f087, 'contact', 'email', '' ));
 $m4is_q309 =isset($m4is_i641['Email'])? strtolower(trim($m4is_i641['Email'])): '';
 $m4is_h21895 =0;
 $m4is_d913 =isset($m4is_m14['memberiumformtag'])? (int) $m4is_m14['memberiumformtag']: 0;
 if(empty($m4is_q309)&&is_user_logged_in()){
$m4is_l17096 =wp_get_current_user();
 $m4is_q309 =is_a($m4is_l17096, 'WP_User' )? $m4is_l17096->user_email : '';
 
} unset($m4is_i641[$m4is_r6234]);
  if(is_user_logged_in()){
if(empty($m4is_q309 )||$m4is_q309 == $m4is_d0664 ){
$m4is_h21895 =$this->m4is_r1546->m4is_z56();
 
}
}if($m4is_h21895 ){
unset($m4is_i641[$m4is_p4935]);
 m4is_r83::m4is_c26()->m4is_k98($m4is_d913, $m4is_h21895 );
 m4is_p40::m4is_x6560($m4is_h21895, $m4is_i641);
 m4is_r83::m4is_c26()->m4is_x4831($m4is_h21895 );
 m4is_r83::m4is_c26()->m4is_f605($m4is_h21895 );
 m4is_q82::m4is_d59($m4is_f087 );
 
}else{
$m4is_h21895 =m4is_p40::m4is_k82670($m4is_i641);
 m4is_p40::m4is_y935($m4is_i641['Email'], "GravityForms Enrollment - Form '{$m4is_m14['title']
}'\nIP Address ' . {$m4is_e03627['ip']
}" );
 m4is_r83::m4is_c26()->m4is_k98($m4is_d913, $m4is_h21895 );
 
} if($m4is_h21895 ){
 $m4is_y66291 =['table' =>-1, 'type' =>19, ];
 $m4is_f6029 =m4is_s695::m4is_e654(m4is_s695::CONTACT_FIELDS, m4is_s695::EMAIL_TYPE);
 $m4is_f6029[]='Email';
 $m4is_f6029[]='EmailAddress2';
 $m4is_f6029[]='EmailAddress3';
 $m4is_x95 =[];
 foreach($m4is_i641 as $m4is_r637 =>$m4is_w86 ){
if(in_array($m4is_r637, $m4is_f6029 )){
if(!empty($m4is_w86 )){
$m4is_x95[]=strtolower(trim($m4is_w86 ));
 
}
}
}$m4is_x95 =array_unique($m4is_x95 );
 foreach($m4is_x95 as $m4is_f4930 ){
if(!empty($m4is_f4930 )){
m4is_p40::m4is_y935($m4is_f4930, 'Memberium Gravity Form Submission' );
 
}
}
}
}
}
} 
function m4is_f19($m4is_m14, $m4is_q25107, $m4is_g496){
if(is_array($m4is_m14 )){
foreach ($m4is_m14['fields']as &$m4is_q523 ){
$m4is_g89163 =property_exists($m4is_q523, 'type' )? $m4is_q523->type : '';
 $m4is_v586 ='';
 $m4is_w5867 =property_exists($m4is_q523, 'inputName' )? $m4is_q523->inputName : '';
 $m4is_n246 =property_exists($m4is_q523, 'defaultValue' )? $m4is_q523->defaultValue : '';
 $m4is_v586 =$this->m4is_x321($m4is_w5867, $m4is_n246 );
 if(property_exists($m4is_q523, 'defaultValue' )){
$m4is_q523->defaultValue =$m4is_v586;
 
}if($m4is_g89163 == 'date' ){
if(!empty($m4is_v586 )){
$m4is_i691 =strtotime($m4is_v586 );
 if($m4is_q523->dateType == 'datepicker'){
$m4is_q523->defaultValue =date('m\/d\/Y', $m4is_i691 );
 
}elseif($m4is_q523->dateType == 'datefield' ){
$m4is_q523->inputs[0]['defaultValue']=date('n', $m4is_i691 );
 $m4is_q523->inputs[1]['defaultValue']=date('j', $m4is_i691 );
 $m4is_q523->inputs[2]['defaultValue']=date('Y', $m4is_i691 );
 
}elseif($m4is_q523->dateType == 'datedropdown' ){
$m4is_q523->inputs[0]['defaultValue']=date('n', $m4is_i691 );
 $m4is_q523->inputs[1]['defaultValue']=date('j', $m4is_i691 );
 $m4is_q523->inputs[2]['defaultValue']=date('Y', $m4is_i691 );
 
}
}
}elseif($m4is_g89163 == 'name' ){
foreach($m4is_q523->inputs as $m4is_l9671 =>$m4is_c945 ){
$m4is_w5867 =isset($m4is_c945['name'])? $m4is_c945['name']: '';
 $m4is_n246 =isset($m4is_c945['defaultValue'])? $m4is_c945['defaultValue']: '';
 $m4is_q523->inputs[$m4is_l9671]['defaultValue']=$this->m4is_x321($m4is_w5867, $m4is_n246 );
 
}
}elseif($m4is_g89163 == 'address' ){
foreach($m4is_q523->inputs as $m4is_l9671 =>$m4is_c945 ){
$m4is_w5867 =isset($m4is_c945['name'])? $m4is_c945['name']: '';
 $m4is_n246 =isset($m4is_c945['defaultValue'])? $m4is_c945['defaultValue']: '';
 $m4is_q523->inputs[$m4is_l9671]['defaultValue']=$this->m4is_x321($m4is_w5867, $m4is_n246 );
 
}
}elseif($m4is_g89163 == 'checkbox' ){
$selections =[];
 if(property_exists($m4is_q523, 'defaultValue' )){
$selections =strtolower($m4is_q523->defaultValue );
 $selections =array_filter(explode(',', $selections ));
  
}foreach($m4is_q523->choices as $choice_id =>$choice ){
if(in_array(strtolower($choice['value']), $selections )){
$m4is_q523->choices[$choice_id]['isSelected']=1;
 
}
}
}else{
if(is_array($m4is_q523->inputs )){
foreach($m4is_q523->inputs as $m4is_l9671 =>$m4is_c945 ){
$m4is_w5867 =isset($m4is_c945['name'])? $m4is_c945['name']: '';
 $m4is_n246 =isset($m4is_c945['defaultValue'])? $m4is_c945['defaultValue']: '';
 $m4is_q523->inputs[$m4is_l9671]['defaultValue']=$this->m4is_x321($m4is_w5867, $m4is_n246 );
 
}
}
}
}
}return $m4is_m14;
 
}
function m4is_z826($m4is_m14, $m4is_q25107, $m4is_g496){

}
function m4is_q4609($m4is_l62046, $m4is_t09761 =null, $m4is_v3458 =''){
$m4is_y642 =['after' =>'', 'before' =>'', 'capture' =>'', 'debug' =>0, 'default' =>'', 'field' =>'', 'form_id' =>0, 'htmlattr' =>'', 'input' =>'', 'offset' =>0, 'order' =>'DESC', 'page_size' =>1, 'status' =>'active', 'txtfmt' =>'', 'user_id' =>$this->m4is_r1546->m4is_x66(), ];
 $m4is_l62046 =shortcode_atts($m4is_y642, $m4is_l62046, 'memberium' );
 $m4is_l62046['field']=strtolower(trim($m4is_l62046['field']));
 $m4is_l62046['input']=strtolower(trim($m4is_l62046['input']));
 $m4is_l62046['form_id']=(int) $m4is_l62046['form_id'];
 $m4is_l62046['page_size']=(int) $m4is_l62046['page_size'];
 $m4is_l62046['offset']=(int) $m4is_l62046['offset'];
 $m4is_m14 =GFAPI::get_form($m4is_l62046['form_id']);
 $m4is_m661 =0;
 if(empty($m4is_l62046['field'])){
if(m4is_r83::m4is_c26()->m4is_v461())return '<p>Error:  No Fieldname Provided.</pre>';
 return;
 
}if(empty($m4is_m14 )){
if(m4is_r83::m4is_c26()->m4is_v461())return '<p>Error:  Invalid Form Id.</p>';
 return;
 
}if(empty($m4is_m14['fields'])){
if(m4is_r83::m4is_c26()->m4is_v461())return '<p>Error:  This form has no fields.</p>';
 return;
 
}$m4is_s59381 =[];
 $m4is_s59381['status']='active';
 $m4is_l184 =1;
 $m4is_u6827 =['direction' =>$m4is_l62046['order'], 'is_numeric' =>true ];
 $m4is_r693 =['offset' =>$m4is_l62046['offset'], 'page_size' =>$m4is_l62046['page_size']];
 $m4is_s59381['field_filters'][]=['key' =>'created_by', 'value' =>$m4is_l62046['user_id']];
 $m4is_e03627 =GFAPI::get_entries($m4is_l62046['form_id'], $m4is_s59381, $m4is_u6827, $m4is_r693, $m4is_l184);
 if($m4is_l62046['debug']){
return print_r($m4is_e03627, true );
 
} foreach($m4is_m14['fields']as $m4is_a89 ){
$m4is_d039 =($m4is_l62046['field']== strtolower($m4is_a89->memberiumfieldsync ));
 $m4is_d039 =$m4is_d039 ||($m4is_l62046['field']== strtolower($m4is_a89->adminLabel ));
 $m4is_d039 =$m4is_d039 ||($m4is_l62046['field']== strtolower($m4is_a89->label ));
 if($m4is_d039 ){
if(!is_array($m4is_a89->inputs )){
$m4is_m661 =$m4is_a89->id;
 
}else{
foreach ($m4is_a89->inputs as $m4is_y867 ){
if($m4is_l62046['input']== strtolower($m4is_y867['label'])||$m4is_l62046['input']== strtolower($m4is_y867['name'])){
$m4is_m661 =$m4is_y867['id'];
 
}
}
}
}
}unset($m4is_u6827, $m4is_s59381, $m4is_r693, $m4is_l184, $m4is_l16849 );
 $m4is_m661 =empty($m4is_m661 )? $m4is_l62046['field']: $m4is_m661;
 if($m4is_m661 ){
$m4is_v586 =isset($m4is_e03627[0][$m4is_m661])? $m4is_e03627[0][$m4is_m661]: $m4is_l62046['default'];
 $m4is_l16849 =json_decode($m4is_v586 );
 $m4is_v586 =empty($m4is_l16849 )? $m4is_v586 : $m4is_l16849;
 
}return m4is_f61::m4is_u150(false, $m4is_v586, $m4is_l62046['txtfmt'], $m4is_l62046['capture'], $m4is_l62046['htmlattr'], $m4is_l62046['before'], $m4is_l62046['after']);
 
}   
function m4is_a4031($m4is_v586 ){
return m4is_q82::m4is_d6758($this->m4is_r1546->m4is_x66(), 'memb_user', 'crm_id', 0 );
 
}
function m4is_d49651($m4is_v586){
return m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', 'firstname', '' );
 
}
function m4is_m92($m4is_v586){
return m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', 'lastname', '' );
 
}
function m4is_y6695($m4is_v586){
return m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', 'email', '' );
 
}
function m4is_m63($m4is_v586){
return m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', 'phone1', '' );
 
}
function m4is_s14306($m4is_v586){
return m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', 'streetaddress1', '' );
 
}
function m4is_b3066($m4is_v586){
return m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', 'streetaddress2', '' );
 
}
function m4is_y061($m4is_v586){
return m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', 'city', '' );
 
}
function m4is_p90($m4is_v586){
return m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', 'state', '' );
 
}
function m4is_o23($m4is_v586){
return m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', 'postalcode', '' );
 
}
function m4is_a721($m4is_v586){
return m4is_q82::m4is_k660($this->m4is_r1546->m4is_x66(), 'contact', 'country', '' );
 
}
function __call($m4is_s7349, $m4is_y66291){

}
}

