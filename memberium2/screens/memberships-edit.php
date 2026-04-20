<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 $m4is_n73190 =empty($_GET['id'])? 0 : (int) $_GET['id'];
 if(!array_key_exists($m4is_n73190, $this->m4is_m96240 )||!is_array($this->m4is_m96240[$m4is_n73190])){
echo '<p>Invalid Membership Id</p>';
 return;
 
}$m4is_w64 =$this->m4is_m96240[$m4is_n73190];
 $m4is_f81662 =$this->m4is_r1546->m4is_j498('settings', 'dynamic_menus' );
 if(empty($m4is_w64['main_id'])){
$m4is_w64['main_id']=$m4is_n73190;
 
}if(empty($m4is_w64['addltag_ids'])){
$m4is_w64['addltag_ids']='';
 
}if(empty($m4is_w64['roles'])){
$m4is_w64['roles']=[];
 
}$m4is_w0937 ='';
 if(is_array($this->m4is_e863 )){
foreach ($this->m4is_e863 as $m4is_m7560 ){
$m4is_a437 =in_array($m4is_m7560['id'], $m4is_w64['roles'])? ' selected="selected" ' : '';
 $m4is_w0937 .= "<option value='{$m4is_m7560['id']
}' {$m4is_a437
}>{$m4is_m7560['name']
}</option>";
 
}
}$m4is_w79 =m4is_h65::m4is_o64(0000 );
 $m4is_x726 =m4is_h65::m4is_o64(0000 );
 $m4is_f63 =m4is_h65::m4is_o64(0000 );
 $m4is_u1294 =m4is_h65::m4is_o64(0000 );
 $m4is_l517 =m4is_h65::m4is_o64(0000 );
 $m4is_k7096 =m4is_h65::m4is_o64(0000 );
 echo <<<HTMLBLOCK

	<div class="wrap memberium">
		<p>Return to <a href="?page=memberium-memberships">Membership Screen</a></p>
		<form method="post">
			<input name="action" value="edit" type="hidden">
			<h1 style="margin-bottom:10px;">Membership Level Editor</h1><br />
			<label style="margin-left:0px;">Membership Name:</label>
			<input type="text" name="name" value="{$m4is_w64['name']
}" required="required" style="font-size:180%; margin-top:-10px;">
			<ul>
				<h3>Tags</h3>
				<li>
					<label style="color:green;"><strong>Access Tag</strong></label>
					<input disabled="disabled" value="{$m4is_n73190
}" type="text" class="requiredtaglistdropdown" required="required" style="width:300px;"> {$m4is_w79
}
				</li>
				<li>
					<label style="color:green;"><strong>Add\'l Access Tags</strong></label>
					<input value="{$m4is_w64['addltag_ids']
}" type="text" class="multitaglist" name="addltag_ids" style="width:500px;"> {$m4is_x726
}
				</li>
				<li>
					<label style="color:red;"><strong>Payment Failure</strong></label>
					<input value="{$m4is_w64['payf_id']
}" name="payf_id" type="text" class="taglistdropdown" style="width:350px;"> {$m4is_f63
}
				</li>
				<li>
					<label style="color:red;"><strong>Cancellation</strong></label>
					<input value="{$m4is_w64['cancel_id']
}" name="cancel_id" type="text" class="taglistdropdown" style="width:350px;"> {$m4is_u1294
}
				</li>
				<li>
					<label style="color:red;"><strong>Suspension Tag</strong></label>
					<input value="{$m4is_w64['suspend_id']
}" name="suspend_id" type="text" class="taglistdropdown" style="width:350px;">' {$m4is_l517
}
				</li>
				<hr>
				<h3>Level</h3>
				<li>
					<label>Level</label>
					<input type="number" value="{$m4is_w64['level']
}" name="level" min="0" max="999999" required="required" style="text-align:right; width: 80px;"> {$m4is_k7096
}
				</li>
				<hr>
				<h3>Special Pages</h3>
				<li>
					<label>Home Page Priority</label>
					<input type="number" value="{$m4is_w64['login_redirect_priority']
}" name="login_redirect_priority" min="0" max="999999" required="required" style="text-align:right; width: 80px;">
				</li>
				<li>
					<label>First Login Page</label>
					<input value="{$m4is_w64['first_login_page']
}" name="first_login_page" type="text" class="pagelistdropdown" style="width:500px;">
				</li>
				<li>
					<label>Membership Home Page</label>
					<input value="{$m4is_w64['login_page']
}" name="login_page" type="text" class="pagelistdropdown" style="width:500px;">
				</li>
				<li>
					<label>Membership Logout Page</label>
					<input value="{$m4is_w64['logout_page']
}" name="logout_page" type="text" class="pagelistdropdown" style="width:500px;">
				</li>
				<li>
					<label>PAYF Home Page</label>
					<input value="{$m4is_w64['payf_homepage']
}" name="payf_homepage" type="text" class="pagelistdropdown" style="width:500px;">
				</li>
				<li>
				<li>
					<label>SUSP Home Page</label>
					<input value="{$m4is_w64['susp_homepage']
}" name="susp_homepage" type="text" class="pagelistdropdown" style="width:500px;">
				</li>
				<li>
				<li>
					<label>CANC Home Page</label>
					<input value="{$m4is_w64['canc_homepage']
}" name="canc_homepage" type="text" class="pagelistdropdown" style="width:500px;">
				</li>
				<li>
				<hr>
				<h3>Theme</h3>
				<li>
					<label>Theme</label>
					<input value="{$m4is_w64['theme']
}" name="theme" type="text" class="themelistdropdown" style="width:250px;">
				</li>
HTMLBLOCK;
 if($m4is_f81662 ){
m4is_h65::m4is_y648('Exclude from Personal Menus', 'dynamic_menus', 0, $m4is_w64['dynamic_menus']);
 
}else{
echo '<input type="hidden" value="' . $m4is_w64['dynamic_menus']. '" name="dynamic_menus">';
 
}echo <<<HTMLBLOCK
	<hr>
		<h3>Roles</h3>
		<li>
			<label>WordPress Roles</label>
			<input value="" name="roles[]" type="hidden">
			<select style="width:400px; height:1.6em;" class="roles-selector" name="roles[]" multiple="multiple" placeholder="Select WordPress roles to apply on login">
				<option value="">(None)</option>
				{$m4is_w0937
}
			</select>
		</li>
		<br />
HTMLBLOCK;
 do_action('memberium/memberships/edit', $m4is_w64);
 echo <<<HTMLBLOCK
		<hr>
	</ul>
	<input type="submit" class="button-primary" value="Update Membership Level">
	</form>
</div>

HTMLBLOCK;
  $m4is_w9764 =wp_get_themes();
 $m4is_r15 =[];
 $m4is_r15[]=['id' =>'', 'text' =>'(Default)' ];
 foreach ($m4is_w9764 as $m4is_k52736 =>$m4is_c67896 ){
$m4is_r15[]=['id' =>$m4is_k52736, 'text' =>$m4is_c67896->Name ];
 
}$m4is_r15 =json_encode($m4is_r15 );
 unset($m4is_k52736, $m4is_c67896, $m4is_w9764 );
  $m4is_l9321 =m4is_k865::m4is_z2906(true );
 $m4is_l9321 =$m4is_l9321['mc'];
 $m4is_d94821 =[];
 $m4is_d94821[]=['id' =>0, 'text' =>'(None)' ];
  $m4is_b89 =[];
 $m4is_m96240 =(array)m4is_r83::m4is_c26()->m4is_j498('memberships');
 foreach ($m4is_m96240 as $m4is_l9671 =>$m4is_v586 ){
if($m4is_l9671 <> $m4is_n73190){
$m4is_b89[]=$m4is_l9671;
 $m4is_b89[]=$m4is_v586['payf_id'];
 $m4is_b89[]=$m4is_v586['cancel_id'];
 $m4is_b89[]=$m4is_v586['suspend_id'];
 
}
}$m4is_b89 =array_unique(array_filter($m4is_b89 ));
 foreach ((array)$m4is_l9321 as $m4is_d07693 =>$m4is_p786 ){
if(!in_array($m4is_d07693, $m4is_b89 )){
$m4is_d94821[]=['id' =>$m4is_d07693, 'text' =>"{$m4is_p786
} ({$m4is_d07693
})" ];
 $m4is_y34619[]=['id' =>$m4is_d07693, 'text' =>"{$m4is_p786
} ({$m4is_d07693
})", ];
 
}
}$m4is_d94821 =json_encode($m4is_d94821 );
 $m4is_y34619 =json_encode($m4is_y34619 );
 unset($m4is_l9321, $m4is_d07693, $m4is_p786 );
 $m4is_k0941 =m4is_h65::m4is_z53();
  echo '<script>';
 echo 'var themelist       = ', $m4is_r15, ';';
 echo 'var taglist         = ', $m4is_d94821, ';';
 echo 'var requiredtaglist = ', $m4is_y34619, ';';
 echo 'var pagelist        = ', $m4is_k0941, ';';
 echo '</script>';

