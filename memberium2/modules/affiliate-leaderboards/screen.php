<?php
 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_t92::m4is_o719();
 
class m4is_t92 {
private $m4is_i78603 ='';
 private $m4is_m7964 =[];
 private $m4is_c852;
 static 
function m4is_o719(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self();
 
}private 
function __construct(){
$this->m4is_c852 =m4is_v967::m4is_c26();
 $this->m4is_m7964 =$this->m4is_o608();
 $this->m4is_i78603 =$this->m4is_o08($_GET );
  $this->m4is_s3572($_POST );
 $this->m4is_t85();
 
}
function m4is_o608(){
$m4is_m7964 =['leaderboards' =>'<i class="fa fa-list-ol"></i> Create Leaderboards', 'about' =>'<i class="fa fa-users"></i> About',  ];
 return $m4is_m7964;
 
}
function m4is_o08($m4is_j613 ){
return isset($_GET['tab'])? $_GET['tab']: 'leaderboards';
 
}
function m4is_s3572($m4is_m5907 ){
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}if(isset($m4is_m5907['create-leaderboard'])){
$this->m4is_z743($m4is_m5907 );
 
}elseif(isset($m4is_m5907['update-leaderboard'])){
$this->m4is_r56($m4is_m5907 );
 
}elseif(isset($m4is_m5907['refresh'])){
$this->m4is_v661($m4is_m5907 );
 
}
}private 
function m4is_v661($m4is_m5907 ){
$m4is_d07693 =(int) $_POST['refresh'];
 $m4is_m63284 =$this->m4is_c852->m4is_l18();
 $m4is_m63284[$m4is_d07693]=m4is_h09::m4is_l84536($m4is_m63284[$m4is_d07693]);
 $m4is_e1534 =require_once __DIR__ . '/cron.php';
 $this->m4is_c852->m4is_j3129($m4is_m63284 );
 m4is_h09::m4is_l84536($m4is_m63284[$m4is_d07693]);
 
}private 
function m4is_r56($m4is_m5907 ){
$m4is_m63284 =$this->m4is_c852->m4is_l18();
 foreach($m4is_m5907['profile']as $m4is_l9671 =>$m4is_w96 ){
$m4is_m63284[$m4is_l9671]['slots']=$m4is_w96['slots'];
 $m4is_m63284[$m4is_l9671]['start_date']=$m4is_w96['start_date'];
 $m4is_m63284[$m4is_l9671]['end_date']=$m4is_w96['end_date'];
 $m4is_m63284[$m4is_l9671]['last_updated']=0;
  if(!empty($m4is_w96['delete'])){
unset($m4is_m63284[$m4is_l9671]);
 
}
}$this->m4is_c852->m4is_j3129($m4is_m63284 );
 
}private 
function m4is_z743($m4is_m5907 ){
$m4is_m63284 =$this->m4is_c852->m4is_l18();
 $m4is_p76648 =[];
 $m4is_p76648['name']=empty($m4is_m5907['name'])? '' : substr(trim($m4is_m5907['name']), 0, 40 );
 $m4is_p76648['type']=empty($m4is_m5907['type'])? 'leads' : $m4is_m5907['type'];
 $m4is_p76648['slots']=empty($m4is_m5907['slots'])? 0 : $m4is_m5907['slots'];
 $m4is_p76648['start_date']=empty($m4is_m5907['start_date'])? '' : $m4is_m5907['start_date'];
 $m4is_p76648['end_date']=empty($m4is_m5907['end_date'])? '' : $m4is_m5907['end_date'];
 $m4is_p76648['products']=empty($m4is_m5907['products'])? '' : trim($m4is_m5907['products'], ',' );
 $m4is_p76648['last_updated']=0;
 $m4is_p76648['cache']=[];
 if(empty($m4is_p76648['name'])){
return;
 
}$m4is_m63284[]=$m4is_p76648;
 $this->m4is_c852->m4is_j3129($m4is_m63284 );
 
}
function m4is_t85(){
m4is_s6729::m4is_c26()->m4is_a4215();
 m4is_h65::m4is_n25();
 $this->m4is_w689();
 if($this->m4is_i78603 == 'about' ){
$this->m4is_i69748();
 
}elseif($this->m4is_i78603 == 'leaderboards' ){
$this->m4is_f4591();
 
}$this->m4is_b61308();
 
}private 
function m4is_w689(){
echo '</h2>';
 echo '<div class="memberium_tabcontent" style="margin-top:10px;">';
 echo '<div class="wrap">';
  echo '<h2 class="nav-tab-wrapper">';
 foreach ($this->m4is_m7964 as $m4is_b51936 =>$m4is_k52736 ){
$m4is_s6347 =($m4is_b51936 == $this->m4is_i78603 )? ' nav-tab-active' : '';
 if($m4is_b51936 == $this->m4is_i78603 ){
echo "<span class='nav-tab$m4is_s6347'>$m4is_k52736</span>";
 
}else{
echo "<a class='nav-tab{$m4is_s6347
}' href='?page=", $_GET['page'], "&tab={$m4is_b51936
}'>{$m4is_k52736
}</a>";
 
}
}echo '</h2>';
 echo '<div class="memberium_tabcontent" style="margin-top:10px;">';
 
}
function m4is_i69748(){
$m4is_a6814 =__('Version' ). ' ' . $this->m4is_c852->m4is_w45();
 echo <<<HTMLBLOCK
			<h2>
				Memberium Affiliate Leaderboard Extension for Keap
			</h2>
			<p>{$m4is_a6814
}</p>
			<p>Copyright &copy; 2017-2025 David Bullock, Web Power and Light</p>
		HTMLBLOCK;
 
}
function m4is_f4591(){
$m4is_m63284 =$this->m4is_c852->m4is_l18();
 $m4is_e1534 =m4is_h09::m4is_c26();
 $m4is_j098 =m4is_v87365::m4is_d96640();
 if(!empty($m4is_m63284 )){
echo '<h3>Live Leaderboards</h3>';
 echo '<form method="post">';
 echo '<table class="widefat">';
 echo '<tr>';
 echo '<td style="width:50px;">Delete</td>';
 echo '<td>Name</td>';
 echo '<td style="width:50px;">Type</td>';
 echo '<td style="width:50px;">Slots</td>';
 echo '<td style="width:75px;">Start</td>';
 echo '<td style="width:75px;">End</td>';
 echo '<td>Products</td>';
 echo '<td style="width:75px;">&nbsp;</td>';
 echo '</tr>';
 foreach($m4is_m63284 as $m4is_l9671 =>$m4is_y50 ){
echo '<tr>';
 echo '<td><input type="checkbox" name="profile[', $m4is_l9671, '][delete]" value="1"></td>';
 echo '<td>', $m4is_y50['name'], '</td>';
 echo '<td>', ucwords($m4is_y50['type']), '</td>';
 echo '<td><input type="number" style="width:50px;" value="', $m4is_y50['slots'], '" step="1" min="1" name="profile[', $m4is_l9671, '][slots]"></td>';
 echo '<td><input type="date" value="', $m4is_y50['start_date'], '" name="profile[', $m4is_l9671, '][start_date]"></td>';
 echo '<td><input type="date" value="', $m4is_y50['end_date'], '" name="profile[', $m4is_l9671, '][end_date]"></td>';
 echo '<td>';
 $m4is_j81570 =empty($m4is_y50['products'])? []: array_filter(explode(',', $m4is_y50['products']));
 $m4is_o498 ='';
 if(!empty($m4is_j81570 )){
foreach($m4is_j81570 as $m4is_d07693 ){
$m4is_o498 .= $m4is_j098[$m4is_d07693]['ProductName']. ', ';
 
}unset($m4is_j81570, $m4is_d07693 );
 
}echo trim($m4is_o498, ', ' );
 echo '</td>';
 echo '<td><button name="refresh" class="button button-primary" value="', $m4is_l9671, '">Refresh</button></td>';
  echo '</tr>';
 
}echo '</table>';
 echo '<p><input type="submit" name="update-leaderboard" value="Update Leaderboards" class="button-primary"></p>';
 echo '</form>';
 
}else{
echo '<p>You have no affiliate leaderboards created.  Create one now!</p>';
 
}echo <<<HTMLBLOCK
			<form method="post" id="newprofile">
				<ul>
					<h3>Create New Leaderboard</h3>
		HTMLBLOCK;
 $m4is_y66291 =['id' =>'newprofile_name', 'label' =>'Profile Name', 'placeholder' =>'Enter a name for your Leaderboard here', 'required' =>true, 'size' =>40, ];
 echo m4is_h65::m4is_a9861('name', $m4is_y66291);
 $m4is_r37596 =['leads' =>'Most Leads', 'dollars' =>'Most Sales by Dollar Amount', 'invoices' =>'Most Sales by Invoice Count', ];
 $m4is_y66291 =['id' =>'newprofile_type', 'class' =>'basic-single', 'style' =>'width:250px;', ];
 $m4is_d87521 =null;
 echo '<li><label>Leaderboard Type</label>';
 echo m4is_s6729::m4is_c26()->m4is_i725('type', $m4is_r37596, $m4is_d87521, $m4is_y66291);
 echo m4is_h65::m4is_o64(0000 ), '</li>';
 $m4is_y66291 =['id' =>'newprofile_slots', 'label' =>'Slots', 'placeholder' =>'', 'type' =>'number', 'min' =>1, 'step' =>1, 'value' =>10, 'style' =>'width:50px;', 'required' =>true, ];
 echo m4is_h65::m4is_a9861('slots', $m4is_y66291 );
 $m4is_y66291 =['id' =>'newprofile_start_date', 'label' =>'Start Date', 'placeholder' =>'mm/dd/yyyy', 'size' =>10, 'type' =>'date', 'required' =>true, ];
 echo m4is_h65::m4is_a9861('start_date', $m4is_y66291 );
 $m4is_y66291 =['id' =>'newprofile_end_date', 'label' =>'End Date', 'placeholder' =>'mm/dd/yyyy', 'size' =>10, 'type' =>'date', 'required' =>true, ];
 echo m4is_h65::m4is_a9861('end_date', $m4is_y66291 );
 $m4is_y66291 =['id' =>'newprofile_products', 'label' =>'Products', 'placeholder' =>'Select the products for your leaderboard, or leave blank for all', 'type' =>'text', 'class' =>'multiproductlistdropdown', 'style' =>'width:500px;', 'required' =>false, ];
 echo m4is_h65::m4is_a9861('products', $m4is_y66291 );
 echo '</ul>';
 echo '<p><input type="submit" name="create-leaderboard" value="Create Leaderboard" class="button-primary"></p>';
 echo '</form>';
 echo '<hr>';
 echo '</div>';
 echo '</div>';
 
}
function m4is_b61308(){
$m4is_j098 =m4is_v87365::m4is_d96640();
 $m4is_j63286 =[];
 if(empty($m4is_j098)||!is_array($m4is_j098 )){
return;
 
} foreach($m4is_j098 as $m4is_h1438 ){
$m4is_j63286[]=['id' =>$m4is_h1438['Id'], 'text' =>$m4is_h1438['ProductName']. ' (' . $m4is_h1438['Id']. ')' ];
 
}$m4is_j63286 =json_encode($m4is_j63286 );
 echo '<script>';
 echo 'var productlist = ', $m4is_j63286, ';';
 echo '</script>';
 
}
}?>
<script>
	jQuery( '#newprofile').change( function() {
		var profile_type = jQuery( '#newprofile_type' ).val();
		if ( profile_type == 'leads' ) {
			jQuery(".filteroptions").prop( 'disabled', true );
			jQuery(".filteroptions").hide();
		}
		else {
			jQuery(".filteroptions").prop( 'disabled', false );
			jQuery(".filteroptions").show();
		}
	});

</script>

