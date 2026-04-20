<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 */
 defined('ABSPATH') || die();
 if (! empty($data->message)) {
 echo "<div class='lost_password_message'>{$data->message
}</div>";
 
} if (isset($_POST['memb_form_type']) && $_POST['memb_form_type'] == 'memb_lost_password') {
 echo '<p class="memb_lost_password_submitted">',  _x('Your lost password request has been submitted.  Please check your email.',  'memb_lost_password',  'memberium'),  '</p>';
 
} ?><form name="<?php echo $data->form_name;
 ?>" id="<?php echo $data->form_name;
 ?>" method="post">
	<input type="hidden" name="form_id" value="<?php echo $data->form_id;
 ?>"">
	<input type="hidden" name="actions" value="<?php echo $data->parameters;
 ?>">
	<input type="hidden" name="signature" value="<?php echo $data->signature;
 ?>">
	<input type="hidden" name="memb_form_type" value="memb_lost_password">
	<p>
		<label for="user_login"><?php echo $data->email_label;
 ?><br />
			<input type="text" name="user_login" id="user_login" class="input" value="" size="30" required="required" placeholder="Enter your email address here." autocomplete="username"/>
		</label>
	</p>
	<input type="hidden" name="redirect_to" value="<?php echo $data->redirect;
 ?>"/>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php echo $data->button_text;
 ?>"/>
	</p>
</form>

