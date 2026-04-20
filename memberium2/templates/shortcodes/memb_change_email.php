<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 */
 defined('ABSPATH') || die();
 if ( empty($data) ) {
 return;
 
} if ( ! empty($data->message) ) {
 echo "<div class='email_change_message'>{$data->message
}</div>";
 
} $button_text = isset($data->button_text) ? $data->button_text : '';
 $email1_label = isset($data->email1_label) ? $data->email1_label : '';
 $email2_label = isset($data->email2_label) ? $data->email2_label : '';
 $form_id = isset($data->form_id) ? $data->form_id : '';
 $form_name = isset($data->form_name) ? $data->form_name : '';
 $parameters = isset($data->parameters) ? $data->parameters : '';
 $signature = isset($data->signature) ? $data->signature : '';
 ?>
<style>
	#<?= $data->form_name ?> div {
		margin-bottom:12px;
	}
	#<?= $data->form_name ?> label {
		display:inline-block;
		width:150px;
	}
</style>
<form name="<?= $data->form_name ?>" id="<?= $data->form_name ?>" method="post">
	<?= $data->nonce ?>
	<input type="hidden" name="memb_form_type" value="memb_change_email">
	<input type="hidden" name="form_id" value="<?= $data->form_id ?>">
	<input type="hidden" name="actions" value="<?= $data->parameters ?>">
	<input type="hidden" name="signature" value="<?= $data->signature ?>">
	<div>
		<label><?= $data->email1_label ?></label>
		<input name="email1" value="<?= $data->email ?>" type="email" size="30" required="required" />
	</div>
	<div>
		<label><?= $data->email2_label ?></label>
		<input name="email2"  value="<?= $data->email ?>" type="email" size="30" required="required" />
	</div>
	<div>
		<label></label>
		<input type="submit" value="<?= $data->button_text ?>" name="submit" />
	</div>
</form>

<script>
	jQuery('#user_login, #user_pass').prop('required', true);

	jQuery('#user_pass').hover( function() {
	    jQuery('#user_pass').attr('type', 'text');
	}, function() {
    	jQuery('#user_pass').attr('type', 'password');
	});
</script>

