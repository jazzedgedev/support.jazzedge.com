<?php

/**
 * Copyright (c) 2012-2022 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 */


 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.' ));
 m4is_v568::m4is_z95();
 final 
class m4is_v568 {
static 
function m4is_z95(){
 $m4is_n764 =m4is_z6894::m4is_a38740(true );
 $m4is_v61753 ='';
 foreach ($m4is_n764 as $tag_category ){
$m4is_v61753 .= '<option value="' . $tag_category['id']. '">' . $tag_category['name']. '</option>';
 
}$create_membership_help =m4is_h65::m4is_o64(8853 );
 $create_tag_help =m4is_h65::m4is_o64(8852 );
 $create_drip_help =m4is_h65::m4is_o64(8856 );
 $create_category_help =m4is_h65::m4is_o64(8858 );
 echo <<<HTMLBLOCK
			<h3>Tag Builder Pro</h3>
			<table class="widefat">
				<form method="POST" action="">
					<tr>
						<td>Create Membership Level:</td>
						<td>
							<input name="tag_name" type="text" size="20" /> &nbsp;
							<select name="category_id" class="basic-single" style="width:200px;">{$m4is_v61753
}</select> &nbsp;
							<input type=checkbox name="create_set" value=all /> Include SUSP/CANC &nbsp;
							<input type="submit" name="create-membership" value="Create" class="button-primary" /> &nbsp; {$create_membership_help
}
						</td>
					</tr>
				</form>

				<form method="POST" action="">
					<tr>
						<td>Create New Tag:</td>
						<td>
						<input name=tag_name type=text size=20 required=required /> &nbsp;
						<select name=category_id class=basic-single required=required style="width:200px;">{$m4is_v61753
}</select> &nbsp;
						<input type="submit" name="create-tag" value="Create" class="button-primary" /> &nbsp; {$create_tag_help
}
					</td>
				</tr>
				</form>

				<form method="POST" action="">
					<tr>
						<td>Create Drip Tags:</td>
						<td>
							<input name="tag_name" type="text" size="20" required=required /> &nbsp;
							Start: <input name=start type=number min=1 value=1 max=300 size=3 required=required /> &nbsp;
							End: <input name=end type=number min=1 value=1 max=300 size=3 required=required/> &nbsp;
							<select name="category_id" class="basic-single" style="width:200px;">{$m4is_v61753
}</select> &nbsp;
							<input type="submit" name="create-tags" value="Create All" class="button-primary" /> &nbsp;
							{$create_drip_help
}
						'</td>
					</tr>
				</form>

				<form method="POST" action="">
					<tr>
						<td>Create New Category:</td>
						<td>
							<input name=category_name type=text size=20 required=required /> &nbsp;
							<input type="submit" name="create-category" value="Create" class="button-primary" /> &nbsp;
							{$create_category_help
}
						</td>
					</tr>
				</form>
			</table>
			&nbsp;<br />
		HTMLBLOCK;
 
}
}

