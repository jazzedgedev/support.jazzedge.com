<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 global $wpdb;
  $m4is_m6862 =5;
 $m4is_x39508 =m4is_r83::m4is_c26()->get_i2sdk_options();
 $m4is_m26193 =(array)m4is_r83::m4is_c26()->m4is_j498('ga_customvars');
  $m4is_p295 =['' =>'[Select the Variable]', '!system.membership_level' =>'Membership Level', '!system.membership_name' =>'Membership Name', ];
 $m4is_a89 =m4is_c69807::m4is_f5248('Contact', TRUE );
 $m4is_j108 =[''];
 foreach ($m4is_a89 as $m4is_f3968 =>$m4is_q523 ){
$m4is_p295['!contact.' . strtolower($m4is_q523 )]='Contact ' . $m4is_q523;
 
}$m4is_a89 =m4is_c69807::m4is_f5248('Affiliate', TRUE);
 foreach ($m4is_a89 as $m4is_f3968 =>$m4is_q523 ){
$m4is_p295['!affiliate.' . strtolower($m4is_q523 )]='Affiliate ' . $m4is_q523;
 
}if($_SERVER['REQUEST_METHOD']== 'POST' ){
 if(isset($_POST['add-variable'])){
$m4is_m26193[$_POST['slot_id']]=['name' =>$_POST['slot_name'], 'variable' =>$_POST['slot_variable'], 'label' =>$m4is_p295[$_POST['slot_variable']], ];
 m4is_h65::m4is_z896('Custom Variable Added' );
 
} if(!empty($_POST['delete'])){
foreach ($_POST['delete']as $m4is_l9671 =>$m4is_v586 ){
if($m4is_v586 == 'on' ){
unset($m4is_m26193[$m4is_l9671]);
 m4is_h65::m4is_z896('Custom Variable Deleted' );
 
}
}
} m4is_r83::m4is_c26()->m4is_d64918($m4is_m26193, 'ga_customvars');
 
}$m4is_f3960 =[];
 for ($i =1;
 $i <= $m4is_m6862;
 $i++ ){
if(!isset($m4is_m26193[$i])){
$m4is_f3960[]=$i;
 
}
}m4is_h65::m4is_n25();
 ?>
<div class="wrap">
	<h1>Memberium Google Analytics Settings</h1>
	<?php
 if(count($m4is_m26193 )> $m4is_m6862 ){
echo '<tr><td colspan="6">', _e('All custom variable slots are assigned.' ), '</td></tr>';
 
}else{
$m4is_m8706 ='';
 foreach ($m4is_p295 as $m4is_v586 =>$m4is_w42 ){
$m4is_m8706.= '<option value="' . $m4is_v586 . '">' . $m4is_w42 . '</option>';
 
}$m4is_e56 ='';
 foreach ($m4is_f3960 as $m4is_w42 ){
$m4is_e56.= '<option value="' . $m4is_w42 . '">' . $m4is_w42 . '</option>';
 
}?>
		<h3>Add New Custom Variable</h3>
		<div style="width:800px;">
			<form method="POST" action="">
				<table class="widefat">
					<tr>
						<th>Custom Variable Label</th>
						<th>Order</th>
						<th>Value</th>
					</tr>
					<tr>
						<td><input name="slot_name" type="text" size="25" required="required" placeholder="Your name for this variable"/></td>
						<td><select name="slot_id" required="required"><?php echo $m4is_e56;
 ?></select></td>
						<td><select name="slot_variable" required="required"><?php echo $m4is_m8706;
 ?></select></td>
					</tr>
				</table>
				&nbsp;<br />
				<input type="submit" name="add-variable" value="Add Custom Variable" class="button-primary" />
				<hr />
			</form>
		</div>
		<?php
 
}?>
	<h3>Current Custom Variables</h3>
	<div style="width:800px;">
		<form method="POST" action="">
			<hr />
			<table class="widefat" style="white-space:nowrap;">
				<tr>
					<th>Custom Variable Label</th>
					<th>Order</th>
					<th>Value</th>
					<th>Delete?</th>
				</tr>
				<?php
 if(count($m4is_m26193 )== 0 ){
echo '<td colspan="99">You have no custom variables defined.</td>';
 
}else{
foreach ((array)$m4is_m26193 as $m4is_q963 =>$m4is_c2340 ){
echo '<tr>';
 echo '<td>';
 echo $m4is_c2340['name'];
 echo '</td>';
 echo '<td>';
 echo $m4is_q963;
 echo '</td>';
 echo '<td>';
 echo $m4is_c2340['label'];
 echo '</td>';
 echo '<td>';
 echo '<input type="checkbox" name="delete[' . $m4is_q963 . ']">';
 echo '</td>';
 echo '</tr>';
 
}
}?>
			</table>
			&nbsp;<br />
			<input type="submit" name="delete-variables" value="Delete Custom Variables" class="button-secondary" />
		</form>
	</div>
</div>
<hr />

