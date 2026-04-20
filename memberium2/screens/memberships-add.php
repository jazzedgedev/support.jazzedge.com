<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 $m4is_o4786 =m4is_h65::m4is_o64(1222, 'Click Here' );
 $m4is_h6148 =m4is_h65::m4is_o64(0000, 'Click Here' );
 $m4is_s16390 =m4is_h65::m4is_y648('Exclude from Personal Menus', 'dynamic_menus', 0, 0, false );
 $m4is_w0937 ='';
 foreach ($this->m4is_e863 as $m4is_m7560 ){
$m4is_w0937 .= sprintf('<option value="%d">%s</option>', $m4is_m7560['id'], $m4is_m7560['name']);
 
}echo <<<HTMLBLOCK
	<div class="wrap memberium">
		<p>Return to <a href="?page=memberium-memberships">Membership Screen</a></p>
		<form method="post" action="?page=memberium-memberships">
			<input name="action" value="add" type="hidden">

			<h1 style="margin-bottom:20px;">Create Membership Tags and Level</h1>
			<p style="margin-bottom:20px;">
				Looking for help to understand how to create a membership level or questions about what to input where? {$m4is_o4786
}
			</p>
				<label style="margin-left:0px;">Membership Name:</label>
				<input type="text" name="name" value="" placeholder="Enter Membership Name" required="required" size="25" tabindex="1" style="font-size:150%; margin-top:-10px;">
			<ul>
			<h3>Tags</h3>
			<li>
				<label><strong style="color:green;">Access Tag</strong> <strong>*</strong></label>
				<input value="" name="main_id" type="text" placeholder="This setting is required" class="requiredtaglistdropdown" required="required" style="width:350px;">
			</li>
			<li>
				<label><strong style="color:red;">Payment Failure (PAYF)</strong></label>
				<input value="0" name="payf_id" type="text" class="taglistdropdown" style="width:350px;">
			</li>
			<li>
				<label><strong style="color:red;">Cancellation (CANC)</strong></label>
				<input value="0" name="cancel_id" type="text" class="taglistdropdown" style="width:350px;">
			</li>
			<li>
				<label><strong style="color:red;">Suspension Tag (SUSP)</strong></label>
				<input value="0" name="suspend_id" type="text" class="taglistdropdown" style="width:350px;">
			</li>
			<hr>
			<h3>Level</h3>
			<li>
				<label>Level</label>
				<input type="number" value="0" name="level" min="0" max="999999" style="text-align:right; width: 80px;">
			</li>
			<hr>
			<h3>Special Pages</h3>
			<li>
				<label>First Login Page</label>
				<input value="0" name="first_login_page" type="text" class="pagelistdropdown" style="width:500px;">
			</li>
			<li>
				<label>Membership Home Page</label>
				<input value="0" name="login_page" type="text" class="pagelistdropdown" style="width:500px;">
			</li>
			<li>
				<label>Membership Logout Page</label>
				<input value="0" name="logout_page" type="text" class="pagelistdropdown" style="width:500px;">
			</li>
			<li>
				<label>PAYF Home Page</label>
				<input value="0" name="payf_homepage" type="text" class="pagelistdropdown" style="width:500px;">
			</li>
			<li>
				<label>SUSP Home Page</label>
				<input value="0" name="susp_homepage" type="text" class="pagelistdropdown" style="width:500px;">
			</li>
			<li>
				<label>CANC Home Page</label>
				<input value="0" name="canc_homepage" type="text" class="pagelistdropdown" style="width:500px;">
			</li>
			<li>
			<hr>
			<h3>Theme</h3>
			<li>
				<label>Theme</label>
				<input value="" name="theme" type="text" class="themelistdropdown" style="width:250px;">
			</li>
			{$m4is_s16390
}
			<hr>
			<h3>Roles</h3>
			<li style="margin-bottom:10px;">
				<label>WordPress Roles</label>
				<input value="" name="roles[]" type="hidden">
				<select style="width:400px; height:1.6em;" class="roles-selector" name="roles[]" multiple="multiple" placeholder="Select WordPress roles to apply on login">
					<option value="">(None)</option>
					{$m4is_w0937
}
				</select>
			</li>
			<hr>
HTMLBLOCK;
 do_action('memberium/memberships/edit', []);
 echo <<<HTMLBLOCK
			</ul>
			<input type="submit" class="button-primary" value="Create Membership Level">
		</form>
		<p style="margin-bottom:20px;">
			Looking for help to understand how to create a membership level or questions about what to input where? {$m4is_h6148
}
		</p>
	</div>
HTMLBLOCK;
 $m4is_r15 =m4is_h65::m4is_w01954();
 $m4is_l9321 =m4is_k865::m4is_z2906(true )['mc'];
  $m4is_y34619 =[];
 $m4is_d94821 =[];
 $m4is_d94821[]=['id' =>0, 'text' =>'(None)' ];
  $m4is_b89 =[];
 $m4is_m96240 =$this->m4is_r1546->m4is_j498('memberships');
 foreach ($m4is_m96240 as $m4is_l9671 =>$m4is_v586 ){
$m4is_b89[]=$m4is_l9671;
 $m4is_b89[]=$m4is_v586['payf_id'];
 $m4is_b89[]=$m4is_v586['cancel_id'];
 $m4is_b89[]=$m4is_v586['suspend_id'];
 
}$m4is_b89 =array_unique(array_filter($m4is_b89 ));
 foreach ((array)$m4is_l9321 as $m4is_d07693 =>$m4is_p786 ){
if(!in_array($m4is_d07693, $m4is_b89 )){
$m4is_d94821[]=['id' =>$m4is_d07693, 'text' =>"{$m4is_p786
} ({$m4is_d07693
})" ];
 $m4is_y34619[]=['id' =>$m4is_d07693, 'text' =>"{$m4is_p786
} ({$m4is_d07693
})" ];
 
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

