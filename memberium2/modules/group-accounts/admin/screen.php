<?php
 class_exists('m4is_r83' )||die();
 final 
class m4is_q913 {
private object $m4is_r1546;
 private object $m4is_b3516;
 private object $m4is_o27056;
 public 
function __construct(){
$this->m4is_v026();
 $this->m4is_i702();
 $_SERVER['REQUEST_METHOD']== 'POST' &&$this->m4is_g6642();
 $this->m4is_t85();
 
}private 
function m4is_v026(): void {
current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 
}private 
function m4is_i702(): void {
$this->m4is_r1546 =m4is_r83::m4is_c26();
 $this->m4is_b3516 =m4is_s6729::m4is_c26();
 $this->m4is_o27056 =m4is_u7102::m4is_c26();
 
}private 
function m4is_t85(){
$m4is_e0213 =$this->m4is_o27056->m4is_f27408();
  $m4is_y642 =['child_added_goal' =>'',  'child_cancel_goal' =>'',  'parent_added_goal' =>'',  'child_added_actionset' =>0,  'child_cancel_actionset' =>0,  'parent_added_actionset' =>0,  'whitelist_memberships' =>false,  'tag_whitelist' =>'',  ];
 $m4is_e0213 =wp_parse_args($m4is_e0213, $m4is_y642 );
  $m4is_m7964 =['general' =>'<i class="fa fa-umbrella"></i> General', 'tags' =>'<i class="fa fa-tags"></i> Tags', 'subscriptions' =>'<i class="fa fa-shopping-cart"></i> Subscriptions', 'teams' =>'<i class="fa fa-users"></i> Teams', ];
 $_GET['tab']=isset($_GET['tab'])&&array_key_exists($_GET['tab'], $m4is_m7964 )? $_GET['tab']: array_key_first($m4is_m7964);
 $m4is_i78603 =$_GET['tab'];
 m4is_h65::m4is_n25();
 echo '<div class="wrap">';
 echo '<h2>', __('Group Account Settings' ), '</h2>';
 echo '<h2 class="nav-tab-wrapper">';
  foreach ($m4is_m7964 as $m4is_b51936 =>$m4is_k52736){
 $class =$m4is_b51936 == $m4is_i78603 ? ' nav-tab-active' : '';
  if($m4is_b51936 == $m4is_i78603){
echo "<span class='nav-tab{$class
}'>{$m4is_k52736
}</span>";
 
} else{
echo "<a class='nav-tab{$class
}' href='?page=", $_GET['page'], "&tab={$m4is_b51936
}'>{$m4is_k52736
}</a>";
 
}
} echo '</h2>';
   echo '<div class="memberium_tabcontent" style="margin-top:10px;">';
 if($m4is_i78603 == 'general' ){
 $m4is_x7062 =$this->m4is_d49632();
 $m4is_b6092 =(bool) m4is_j4156::m4is_c289();
 $m4is_l23748 =!empty($m4is_e0213['child_added_actionset'])||!empty($m4is_e0213['child_cancel_actionset'])||!empty($m4is_e0213['parent_added_actionset']);
 echo '<form method="POST" action="">';
 wp_nonce_field(m4is_r83::m4is_c26()->m4is_j541(), 'memberium_umbrella_account_nonce' );
 echo '<ul>';
 echo '<h3>Management Automations</h3>';
 if( $m4is_l23748){
echo '<h4>Legacy Actionsets</h4>';
 if(empty($m4is_e0213['child_added_goal'])){
echo '<li><label>Child Added Actionset</label>';
 echo '<input value="', $m4is_e0213['child_added_actionset'], '"  name="child_added_actionset" id="child_added_actionset" type="text" class="dropdown actionsetdropdown">';
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 
}if(empty($m4is_e0213['child_cancel_goal'])){
echo '<li><label>Child Cancel Actionset</label>';
 echo '<input value="', $m4is_e0213['child_cancel_actionset'], '"  name="child_cancel_actionset" id="child_cancel_actionset" type="text" class="dropdown actionsetdropdown">';
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 
}if(empty($m4is_e0213['parent_added_goal'])){
echo '<li><label>Parent Added Actionset</label>';
 echo '<input value="', $m4is_e0213['parent_added_actionset'], '"  name="parent_added_actionset" id="parent_added_actionset" type="text" class="dropdown actionsetdropdown">';
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 
}echo '<h4>Campaign Builder API Goals <em>(New / Replaces Actionsets)</em></h4>';
 
}echo '<li><label>Child Added Goal</label>';
 echo '<input value="', $m4is_e0213['child_added_goal']?? '', '" name="child_added_goal" id="child_added_goal" type="text" style="width:400px;" maxlength="32">';
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 echo '<li><label>Child Cancel Goal</label>';
 echo '<input value="', $m4is_e0213['child_cancel_goal']?? '', '" name="child_cancel_goal" id="child_cancel_goal" type="text" style="width:400px;" maxlength="32">';
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 echo '<li><label>Parent Added Goal</label>';
 echo '<input value="', $m4is_e0213['parent_added_goal']?? '', '" name="parent_added_goal" id="parent_added_goal" type="text" style="width:400px;" maxlength="32">';
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 echo '<hr>';
 echo '<h3>Inheritance</h3>';
 $m4is_y66291 =['id' =>'whitelist_memberships', 'label' =>'Automatically innher membership tags', ];
 echo '<li><label>Automatically Inherit Membership Tags</label>';
 m4is_h65::m4is_v6739('whitelist_memberships', $m4is_e0213['whitelist_memberships'], $m4is_y66291 );
 echo '<li><label>Inherit Individual Tags</label>';
 echo '<input value="', $m4is_e0213['tag_whitelist'], '" type="text" class="multitaglist" id="tag_whitelist" name="tag_whitelist[]" style="width:500px;">';
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 $m4is_a89 =$this->m4is_c647();
 $m4is_y66291 =['class' =>'basic-single', 'id' =>'inherited_fields', 'multiple' =>true, 'size' =>2, 'style' =>'width:400px;', ];
 echo '<li><label>Inherited Fields</label>';
 echo $this->m4is_b3516->m4is_i725('inherited_fields[]', $m4is_a89, $m4is_e0213['inherited_fields'], $m4is_y66291 );
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 echo '<hr>';
 echo '<h3>Child Accounts</h3>';
 $m4is_y66291 =['id' =>'child_count_add', 'class' =>'basic-single', 'style' =>'width:400px;', ];
 $this_fields =array_merge(['' =>'(None)'], $m4is_a89);
 $m4is_e0213['child_count_add']=isset($m4is_e0213['child_count_add'])? $m4is_e0213['child_count_add']: '';
 echo '<li><label>Active Child Tag</label>';
 echo '<input value="', $m4is_e0213['active_child_tag']?? 0, '" type="text" class="taglistdropdown" id="active_child_tag" name="active_child_tag" style="width:500px;">';
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 echo '<hr>';
 echo '<h3>Parent Accounts</h3>';
 echo '<li><label>Parent Tags</label>';
 echo '<input value="', $m4is_e0213['parent_tags'], '" type="text" class="multitaglist" id="parent_tags" name="parent_tags[]" style="width:500px;">';
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 echo '<li><label>Minimum Child Accounts</label>';
 echo '<input value="', $m4is_e0213['max_child_accounts'], '"  name="max_child_accounts" id="max_child_accounts" type="number" min="-1" max="999999" class="">';
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 echo '<li><label>Additional Children Field</label>';
 echo $this->m4is_b3516->m4is_i725('child_count_add', $this_fields, $m4is_e0213['child_count_add'], $m4is_y66291 );
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 $m4is_v586 =floor($m4is_e0213['parent_cache_ttl']/ 3600 );
 $m4is_z6698 =m4is_h65::m4is_o64(0000 );
 echo <<<HTMLBLOCK
				<li>
					<label>Parent Cache TTL</label>
					<input value="{$m4is_v586
}"  name="parent_cache_ttl" id="parent_cache_ttl" type="number" min="4" max="744" class=""> hours
					{$m4is_z6698
}
				</li>
			HTMLBLOCK;
 $m4is_y66291 =['class' =>'basic-single', 'disabled' =>!isset($_GET['safety-override']), 'id' =>'parent_field', 'style' =>'width:400px;', ];
 echo '<li><label>Parent/Child Field Match Field:</label>';
 echo $this->m4is_b3516->m4is_i725('parent_field', $m4is_x7062, $m4is_e0213['parent_field'], $m4is_y66291);
 echo m4is_h65::m4is_o64(0000 );
 echo '</ul>';
 echo '<p><input type="submit" value="Update" class="button-primary"></p>';
 echo '</form>';
 
}elseif($m4is_i78603 == 'tags' ){
$this->m4is_a76638();
 
}elseif($m4is_i78603 == 'about' ){
$this->m4is_o018();
 
}elseif($m4is_i78603 == 'subscriptions' ){
$this->m4is_g7315();
 
}elseif($m4is_i78603 == 'teams' ){
$this->m4is_x0694();
 
}$this->m4is_d916();
 
}   private 
function m4is_g6642(): void {
$m4is_e0213 =$this->m4is_o27056->m4is_f27408();
 $_POST['child_added_actionset']=empty($_POST['child_added_goal'])? $_POST['child_added_actionset']?? '' : '';
 $_POST['child_cancel_actionset']=empty($_POST['child_cancel_goal'])? $_POST['child_cancel_actionset']?? '' : '';
 $_POST['parent_added_actionset']=empty($_POST['parent_added_goal'])? $_POST['parent_added_actionset']?? '' : '';
  $m4is_u450 =['whitelist_memberships', ];
 foreach($m4is_u450 as $m4is_l9671 ){
$m4is_e0213[$m4is_l9671]=isset($_POST[$m4is_l9671])? (int) (bool) $_POST[$m4is_l9671]: $m4is_e0213[$m4is_l9671];
 
}isset($_POST['parent_cache_ttl'])&&$_POST['parent_cache_ttl']=$_POST['parent_cache_ttl']* 3600;
 $m4is_u450 =['active_child_tag', 'max_child_accounts', 'parent_cache_ttl', ];
 foreach($m4is_u450 as $m4is_l9671 ){
$m4is_e0213[$m4is_l9671]=isset($_POST[$m4is_l9671])? (int) $_POST[$m4is_l9671]: $m4is_e0213[$m4is_l9671];
 
}$m4is_u450 =['child_count_add', 'parent_field', 'child_added_goal', 'child_cancel_goal', 'parent_added_goal', ];
 foreach($m4is_u450 as $m4is_l9671 ){
$m4is_e0213[$m4is_l9671]=isset($_POST[$m4is_l9671])? trim($_POST[$m4is_l9671]): $m4is_e0213[$m4is_l9671];
 
}$m4is_u450 =['inherited_fields', 'parent_tags', 'tag_whitelist', ];
 foreach($m4is_u450 as $m4is_l9671 ){
$m4is_e0213[$m4is_l9671]=isset($_POST[$m4is_l9671])? trim(implode(',', $_POST[$m4is_l9671])): $m4is_e0213[$m4is_l9671];
 
}$m4is_u450 =['child_added_actionset', 'child_cancel_actionset', 'parent_added_actionset', ];
 foreach($m4is_u450 as $m4is_l9671 ){
$m4is_e0213[$m4is_l9671]=isset($_POST[$m4is_l9671])? trim($_POST[$m4is_l9671]): $m4is_e0213[$m4is_l9671];
 
}unset($m4is_e0213['child_field']);
 if(!empty($_POST['subscription'])){
unset($m4is_e0213['subscriptions']);
 $m4is_e0213['subscriptions']=array_map('intval', $_POST['subscription']);
 $m4is_e0213['ecommerce']=(int) (array_sum($m4is_e0213['subscriptions'])> 0 );
 
}if(isset($_POST['add-tag-grant'])){
  $m4is_e0213['tag_grants'][$_POST['tag']]=(int) $_POST['seats'];
 
}if(isset($_POST['update-tag-grants'])){
 $m4is_e0213['tag_grants']=$_POST['tag_grants'];
  foreach($_POST['tag_grants']as $m4is_p786 =>$m4is_o26 ){
 if($m4is_o26 == 0 ){
unset($m4is_e0213['tag_grants'][$m4is_p786]);
 
}
}
}if(isset($_POST['add-tag-translation'])){
 if($_POST['oldtag']<> $_POST['newtag']){
  $_POST['oldtag']=(int) $_POST['oldtag'];
 $_POST['newtag']=(int) $_POST['newtag'];
 $m4is_e0213['tag_translation'][$_POST['oldtag']]=$_POST['newtag'];
 
} if(($_POST['oldtag']== $_POST['newtag'])||$_POST['newtag']== 0 ){
unset($m4is_e0213['tag_translation'][$_POST['oldtag']]);
 
}
}if(isset($_POST['update-tag-translation'])){
  
}if(isset($_POST['form_name'])&&$_POST['form_name']='memberium/teams/delete' ){
if(!empty($_POST['delete'])){
foreach($_POST['delete']as $m4is_d07693 =>$m4is_v586 ){
if($m4is_v586 ){
$m4is_r637 =$m4is_e0213['teams'][$m4is_d07693]['field_name'];
 m4is_l870::m4is_v07($m4is_d07693 );
 unset($m4is_e0213['teams'][$m4is_d07693]);
 
}
}
}
}if(isset($_POST['form_name'])&&$_POST['form_name']='memberium/teams/add' ){
if(!empty($_POST['team_team_name'])&&!empty($_POST['field_name'])&&!empty($_POST['tags'])){
$m4is_e0213['teams'][$_POST['field_name']]=['team_name' =>$_POST['team_team_name']?? '', 'field_name' =>$_POST['field_name'], 'tags' =>$_POST['tags']?? '', ];
 
}
}$this->m4is_o27056->m4is_g6642($m4is_e0213 );
 $this->m4is_r1546->m4is_v26184();
 
} private 
function m4is_c647(): array {
 $m4is_j108 =['AccountId', 'CreatedBy', 'DateCreated', 'Email', 'FirstName', 'Groups', 'Id', 'LastName', 'LastUpdated', 'LastUpdatedBy', 'Password', 'Validated', $this->m4is_r1546->m4is_j498('settings', 'password_field'), $this->m4is_o27056->m4is_s54627()];
  $m4is_a89 =[];
  $m4is_i5248 =m4is_c69807::m4is_f5248('Contact', true );
  foreach($m4is_i5248 as $m4is_q523){
 if(!in_array($m4is_q523, $m4is_j108)){
$m4is_a89[$m4is_q523]=$m4is_q523;
 
}
} return $m4is_a89;
 
} private 
function m4is_d49632(){
 $m4is_m4936 =['Address3Street1', 'Address3Street2', 'Address3Type', 'AssistantName', 'City2', 'City3', 'Fax1Type', 'Fax2Type', 'Phone3Type', 'Phone4Type', 'Phone5Type', 'ReferralCode', 'SpouseName', $this->m4is_r1546->m4is_j498('settings', 'password_field' ), $this->m4is_o27056->m4is_s54627()];
  $m4is_a89 =[];
  $m4is_i5248 =m4is_c69807::m4is_f5248('Contact', true );
  foreach($m4is_i5248 as $m4is_q523 ){
 if(in_array($m4is_q523, $m4is_m4936)||substr($m4is_q523, 0, 1)== '_'){
$m4is_a89[$m4is_q523]=$m4is_q523;
 
}
} return $m4is_a89;
 
} private 
function m4is_g7315(){
$m4is_j024 =$this->m4is_d20();
 $m4is_e0213 =$this->m4is_o27056->m4is_f27408();
 $m4is_o287 =$this->m4is_r1546->m4is_i916();
 $m4is_j098 =m4is_v87365::m4is_d96640();
 if(!empty($m4is_o287 )){
echo '<form method="post">';
 echo '<table class="widefat">';
 echo '<tr style="font-weight:bold;">';
 echo '<td style="width:150px;">Children/Active Sub</td>';
 echo '<td>Subscription Plan Name</td>';
 echo '<td>Charge</td>';
 echo '<td>Frequency</td>';
 echo '<td>Cycles</td>';
 echo '</tr>';
 foreach($m4is_o287 as $m4is_y078 ){
if($m4is_y078['Active']){
$m4is_y078['name']=$m4is_j098[$m4is_y078['ProductId']]['ProductName'];
 $m4is_y078['period']=$m4is_j024[$m4is_y078['Cycle']];
 $m4is_y078['NumberOfCycles']=isset($m4is_y078['NumberOfCycles'])? $m4is_y078['NumberOfCycles']: 0;
 $m4is_v586 =(empty($m4is_e0213['subscriptions'][$m4is_y078['Id']])? 0 : $m4is_e0213['subscriptions'][$m4is_y078['Id']]);
 echo '<tr>';
 echo '<td><input min=0 step=1 type="number" name="subscription[', $m4is_y078['Id'], ']" value="', $m4is_v586, '" style="width:70px; !important"></td>';
 echo '<td>', $m4is_y078['name'], '</td>';
 echo '<td>$', $m4is_y078['PlanPrice'], '</td>';
 echo '<td>', $m4is_y078['Frequency'], ' ', $m4is_y078['period'], '</td>';
 echo '<td>', $m4is_y078['NumberOfCycles']> 0 ? $m4is_y078['NumberOfCycles']: 'Unlimited', '</td>';
 echo '</tr>';
 
}
}echo '</table>';
 echo '<p><input type="submit" class="button-primary"></p>';
 echo '</form>';
 
}
}private 
function m4is_n82(): array {
$m4is_e0213 =$this->m4is_o27056->m4is_f27408();
 $m4is_e0213['teams']??= [];
 $m4is_a89 =m4is_c69807::m4is_f5248('contact', true );
 $m4is_j108 =['Email', 'FirstName', 'LastName', $this->m4is_r1546->m4is_j498('settings', 'password_field' ), $this->m4is_o27056->m4is_s54627()];
 foreach($m4is_a89 as $m4is_d07693 =>$m4is_q523 ){
if(substr($m4is_q523, 0, 1 )<> '_' ||in_array($m4is_q523, $m4is_j108 )||array_key_exists($m4is_q523, $m4is_e0213['teams'])){
unset($m4is_a89[$m4is_d07693]);
 
}
}return $m4is_a89;
 
}private 
function m4is_x0694(): void {
$m4is_e0213 =$this->m4is_o27056->m4is_f27408();
 $m4is_a89 =$this->m4is_n82();
 $m4is_j8396 ='';
 foreach($m4is_a89 as $m4is_q523 ){
$m4is_j8396 .= '<option value="' . $m4is_q523 . '">' . $m4is_q523 . '</option>';
 
}echo <<<HTMLBLOCK
			<form method="post">
				<input type="hidden" name="form_name" value="memberium/teams/add">
				<table class="widefat">
					<thead>
						<th>Template Team Name</th>
						<th>Field Name</th>
						<th>Tags Given</th>
					</thead>
					<tr>
						<td><input name="team_team_name" type="text" required="required"></td>
						<td><select name="field_name">{$m4is_j8396
}</select></td>
						<td><input name="tags" type="text" size="40" class="multitaglist" required="required"></td>
					</tr>
					<tr>
						<td>
							<input type="submit" class="button-primary" value="Create Team">
						</td>
					</tr>
				</table>
			</form>
		HTMLBLOCK;
 echo <<<HTMLBLOCK
			<table class="widefat" style="margin-top:20px;">
				<form method="post">
					<input type="hidden" name="form_name" value="memberium/teams/delete">
					<thead>
						<th>Delete</th>
						<th>Template Team Name</th>
						<th>Field Name</th>
						<th>Tags Given</th>
					</thead>
		HTMLBLOCK;
 if(!empty($m4is_e0213['teams'])){
$m4is_m09142 ='<input type="submit" class="button-primary" value="Delete">';
 foreach($m4is_e0213['teams']as $m4is_q2643 ){
$m4is_d07693 =$m4is_q2643['field_name'];
 $m4is_l9321 =array_filter(explode(',', $m4is_q2643['tags']));
 $m4is_l9321 =m4is_k865::m4is_k29064($m4is_l9321, true );
 $m4is_l9321 =implode(', ', $m4is_l9321 );
 echo <<<HTMLBLOCK
					<tr>
						<td style="width:50px;"><input type="checkbox" name="delete[{$m4is_d07693
}]" value="1"></td>
						<td>{$m4is_q2643['team_name']
}</td>
						<td>{$m4is_q2643['field_name']
}</td>
						<td>{$m4is_l9321
}</td>
					</tr>
				HTMLBLOCK;
 
}
}else{
echo '<tr><td colspan="4">No Teams Defined</td></tr>';
 $m4is_m09142 ='';
 
}echo <<<HTMLBLOCK
					</tr>
					<tr>
						<td>
							{$m4is_m09142
}
						</td>
					</tr>
				</form>
			</table>
		HTMLBLOCK;
 
} 
function build_json_field_list(): string {
$m4is_i5248 =m4is_c69807::m4is_f5248('Contact', true );
 $m4is_z87 =[['id' =>'', 'text' =>'(None)' ]];
 foreach ($m4is_i5248 as $m4is_q523 ){
 $m4is_z87[]=['id' =>$m4is_q523, 'text' =>$m4is_q523 ];
 
}return json_encode($m4is_z87 );
 
} 
function build_json_tag_list(): string {
  $m4is_l9321 =m4is_k865::m4is_z2906(true );
   $m4is_l9321 =(array)$m4is_l9321['mc'];
  $m4is_z470 =[['id' =>0, 'text' =>'(None)' ]];
  foreach ($m4is_l9321 as $m4is_d07693 =>$m4is_p786 ){
 $m4is_z470[]=['id' =>$m4is_d07693, 'text' =>"{$m4is_p786
} ({$m4is_d07693
})", ];
 
} return json_encode($m4is_z470 );
 
} 
function m4is_d20(): array {
return [6 =>'Days', 3 =>'Weeks', 2 =>'Months', 1 =>'Years', ];
 
} 
function m4is_d916(){
$m4is_z470 =$this->build_json_tag_list();
 $m4is_z87 =$this->build_json_field_list();
 $m4is_y66503 =m4is_j4156::m4is_s6612();
 echo <<<HTMLBLOCK
					<script>
						var actionsetlist = {$m4is_y66503
};
						var fieldlist     = {$m4is_z87
};
						var taglist       = {$m4is_z470
};
					</script>
				</div>
			</div>

		HTMLBLOCK;
 
}
/**
 * Displays the about tab.
 *
 * This function retrieves the version of the Memberium Umbrella Account Extension for Keap,
 * then outputs an HTML block that displays the title of the extension, the version, the copyright notice,
 * and a link to the documentation.
 *
 * @return void
 */


 
function m4is_o018(){
$m4is_a6814 =$this->m4is_o27056->m4is_w45();
 echo <<<HTMLBLOCK
		<h2>
			Memberium Team Account Extension for Keap</h2>
			<p>
				Version {$m4is_a6814
}
			</p>
			<p>
				Copyright &copy; 2015-2024 David Bullock
			</p>
		<p>
		For documentation on this extension, please <a href="https://memberium.com/?p=12000" target="_blank">view our help page</a>.
		</p>
	HTMLBLOCK;
 
} 
function m4is_w97548($m4is_b3516, array $m4is_l9321 ){
 $m4is_y66291 =['class' =>'basic-single',  'echo' =>false, 'required' =>true,  'style' =>'width:250px;margin-right:30px;'  ];
 $m4is_r6583 =['0' =>'(None)']+ $m4is_l9321;
 $m4is_a54 =$this->m4is_b3516->m4is_i725('oldtag', $m4is_l9321, '', $m4is_y66291 );
 $m4is_f576 =$this->m4is_b3516->m4is_i725('newtag', $m4is_r6583, '', $m4is_y66291 );
 $m4is_w42 =__('Set Tag Translation', 'memberium' );
  echo <<<HTMLBLOCK
		<form method="post">
			<p>
				<label>Create New Tag Translation</label>
				{$m4is_a54
}
				{$m4is_f576
}
				<input type="submit" class="button-primary" name="add-tag-translation" value="{$m4is_w42
}">
			</p>
		</form>
	HTMLBLOCK;
 
} 
function m4is_a76638(){
 $m4is_r1546 =m4is_r83::m4is_c26();
  $m4is_l9321 =m4is_k865::m4is_z2906(true )['mc'];
  $m4is_e0213 =$this->m4is_o27056->m4is_f27408();
  $m4is_a58624 =$m4is_e0213['tag_grants']?? [];
  $m4is_g80327 =count($m4is_a58624 );
  echo '<h3>Tag Grants</h3>';
  if($m4is_g80327 > 0 ){
$this->m4is_t2943($m4is_a58624, $m4is_l9321 );
 
}else{
 echo '<p>You have no tags assigned to give child account slots.  Create a new tag grant below:</p>';
 
} $this->m4is_u36085($this->m4is_b3516, $m4is_l9321 );
  echo '<hr>';
 echo '<h3>Tag Translations</h3>';
  if(!empty($m4is_e0213['tag_translation'])){
$this->m4is_r90235($m4is_e0213['tag_translation'], $m4is_l9321);
 
}else{
 echo '<p>You have no tag translations created.  Create a new tag translation below:</p>';
 
} $this->m4is_w97548($this->m4is_b3516, $m4is_l9321 );
 
} 
function m4is_t2943(array $m4is_a58624, array $m4is_l9321){
 $output ='<form method="post">
				<table class="widefat">
				<tr style="font-weight:bold;">
				<td style="width:150px;">Children/Tag</td>
				<td style="width:100px;">Tag Id</td>
				<td>Tag Name</td>
				</tr>';
  foreach($m4is_a58624 as $m4is_l9671 =>$m4is_v586 ){
$output .= '<tr>
						<td><input min=0 step=1 type="number" name="tag_grants[' . $m4is_l9671 . ']" value="' . $m4is_v586 . '" style="width:70px; !important"></td>
						<td>' . $m4is_l9671 . '</td>
						<td>' . $m4is_l9321[$m4is_l9671]. '</td>
						</tr>';
 
} $output .= '</table>
					<p><input type="submit" class="button-primary" name="update-tag-grants" value="Update"></p>
					</form>';
 echo $output;
 
} 
function m4is_u36085($m4is_b3516, array $m4is_l9321){
 $m4is_y66291 =['class' =>'basic-single', 'echo' =>false, 'required' =>true, 'style' =>'width:250px;margin-right:30px;', ];
  $m4is_r18 =$m4is_b3516->m4is_i725('tag', $m4is_l9321, '', $m4is_y66291);
  $output =<<<HTMLBLOCK
			<form method="post">
				<p>
					<label>Create New Tag Grant</label>
					$m4is_r18
					Seats: <input name="seats" type="number" min="1" value="1" style="width:60px;margin-right:30px;">
					<input type="submit" class="button-primary" name="add-tag-grant" value="Add Tag Grant">
				</p>
			</form>
			HTMLBLOCK;
  echo $output;
 
} 
function m4is_r90235(array $m4is_x491, array $m4is_l9321 ){
 $output =<<<HTMLBLOCK
			<form method="post">
				<table class="widefat">
					<tr style="font-weight:bold;">
						<td style="width:350px;">Original Tag</td>
						<td style="width:350px;">New Tag</td>
						<td></td>
					</tr>
		HTMLBLOCK;
  foreach($m4is_x491 as $m4is_y58 =>$m4is_a5187 ){
$output .= <<<HTMLBLOCK
				<tr>
					<td>{$m4is_l9321[$m4is_y58]
} ({$m4is_y58
})</td>
					<td>{$m4is_l9321[$m4is_a5187]
} ({$m4is_a5187
})</td>
				</tr>
			HTMLBLOCK;
 
} $output .= <<<HTMLBLOCK
				</table>
			</form>
		HTMLBLOCK;
  echo $output;
 
}
}

