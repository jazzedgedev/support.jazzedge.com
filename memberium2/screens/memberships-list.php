<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 $m4is_a5794 =count($this->m4is_m96240 );
 $m4is_v43790 ='';
 $membership_help =m4is_h65::m4is_o64(1222 );
 $m4is_l5726 =m4is_k865::m4is_z2906(false );
 $m4is_l5726 =is_array($m4is_l5726['mc'])? $m4is_l5726['mc']: array();
 if($m4is_a5794 == 0 ){
$m4is_v43790 ='<tr><td colspan="99">You have no membership levels created.</td></tr>';
 $m4is_n1806 ='';
 
}else{
$m4is_n1806 ='<input type="submit" name="main_action" value="Update Membership Levels" class="button-primary" />';
 foreach ($this->m4is_m96240 as $m4is_d07693 =>$m4is_w64 ){
$m4is_c67896 =wp_get_theme($m4is_w64['theme']);
 $m4is_n23617 =!empty($m4is_l5726[$m4is_d07693]['name'])? $m4is_l5726[$m4is_d07693]['name']. ' (' . $m4is_d07693 . ')' : '<em>Tag Missing</em>';
 $m4is_n23617 =!empty($m4is_l5726[$m4is_d07693])? $m4is_l5726[$m4is_d07693]. ' (' . $m4is_d07693 . ')' : '<em>Tag Missing</em>';
 $m4is_w64['level']=isset($m4is_w64['level'])? (int) $m4is_w64['level']: 0;
 $m4is_w64['login_redirect_priority']=isset($m4is_w64['login_redirect_priority'])? (int) $m4is_w64['login_redirect_priority']: 0;
 $m4is_g7623 =empty($_GET['page'])? '' : (int) $_GET['page'];
 $m4is_n73190 =empty($m4is_d07693 )? '' : (int) $m4is_d07693;
 $m4is_k61785 =get_submit_button('Delete', 'delete', 'main_action[' . $m4is_d07693 . ']', false );
 $m4is_m5907 =get_post($m4is_w64['login_page']);
 if(is_a($m4is_m5907, 'WP_Post' )){
$m4is_b021 =empty($m4is_m5907->post_title )? '(Default)' : $m4is_m5907->post_title;
 $m4is_j647 =sprintf('<a href="%s">%s (%d)</a>', get_permalink($m4is_w64['login_page']), $m4is_b021, $m4is_w64['login_page']);
 
}else{
$m4is_j647 ='(Default)';
 
}$m4is_v43790 .= <<<HTMLBLOCK
			<tr>
				<td>
					<a class="button-secondary" href="?page=memberium-memberships&action=edit&id={$m4is_n73190
}">Edit</a>
				</td>
				<td>
					<strong><a href="?page=memberium-memberships&action=edit&id={$m4is_n73190
}">{$m4is_w64['name']
}</a></strong>
				</td>
				<td>
					{$m4is_n23617
}
				</td>
				<td>
					<input type=number min=0 max=99999 maxlength=6 name="level[{$m4is_n73190
}]" value="{$m4is_w64['level']
}" style="width:80px;">
				</td>
				<td>
					<input type=number min=0 max=99999 maxlength=6 name="login_redirect_priority[{$m4is_n73190
}]" value="{$m4is_w64['login_redirect_priority']
}" style="width:80px;">
				</td>
				<td>
					{$m4is_j647
}
				</td>
				<td>
					{$m4is_k61785
}
				</td>
			</tr>
		HTMLBLOCK;
 
}
}echo <<<HTMLBLOCK
<div class="wrap">
	<!-- h1>Memberium Membership Settings< -->
	<h3>Current Membership Levels {$membership_help
}</h3>
	<div style="width:90%;">
		<p>
			These are the membership levels you have already set up, click the name of a membership level below to edit it or click the "Create New Membership Level" button to add a new membership level.
		</p>
		<hr />
		<form method="POST" action="">
			<input type="submit" name="main_action" value="Update Levels" style="position:absolute;left:-100%;" />
			<table class="widefat" style="white-space:nowrap;">
				<tr style="background-color:#eee">
				<th style="width:50px;"></th>
					<th style="width:250px;"><strong>Level&nbsp;Name</strong></th>
					<th><strong>Tag</strong></th>
					<th style="width:75px;"><strong>Level</strong></th>
					<th style="width:75px;"><strong>Login Priority</strong></th>
					<th><strong>Homepage</strong></th>
					<th></th>
				</tr>
				{$m4is_v43790
}
			</table>

		<p>
		<a href="?page=memberium-memberships&action=add" class="button-primary">Create New Membership Level</a> &nbsp;
		{$m4is_n1806
}
	</form>
	<hr />
</div>
HTMLBLOCK;

