<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 */
 defined('ABSPATH') || die();
 if (empty($data)) {
 return;
 
} wp_enqueue_script('jquery',  null,  null,  null,  true);
 ?><style>
	#<?= $data->form_name ?> div {
		margin-bottom:12px;
	}
	#<?= $data->form_name ?> label {
		display:inline-block;
		width:150px; 
	}
</style>
<?php
 if (! empty($data->messages)) {
 echo "<div class='password_change_message'><p>{$data->messages
}</p></div>";
 
} do_action('memberium/shortcodes/memb_change_password/before_form',  $data);
 ?>
<form name="<?= $data->form_name ?>" id="<?= $data->form_name ?>" method="post" action="<?= $data->form_action ?>">
	<?= $data->nonce ?>
	<input type="hidden" name="memb_form_type" value="memb_change_password">
	<input type="hidden" name="form_id" value="<?= $data->form_id ?>">
	<input type="hidden" name="parameters" value="<?= $data->parameters ?>">
	<input type="hidden" name="signature" value="<?= $data->signature ?>">
	<div>
		<label><?= $data->password1_label ?></label>
		<input name="password1" id="<?= $data->form_name ?>-password1" minlength="<?= $data->min_length ?>" maxlength="<?= $data->max_length ?>" type="password" size="30" required value="" autocomplete="new-password" autocapitalize="off">
	</div>
	<div>
		<label><?= $data->password2_label ?></label>
		<input name="password2" id="<?= $data->form_name ?>-password2" minlength="<?= $data->min_length ?>" maxlength="<?= $data->max_length ?>" type="password" size="30" required value="" autocompelte="new-password" autocapitalize="off">
	</div>
	<div>
		<label></label>
		<input type="submit" value="<?= $data->button_text ?>">
	</div>
</form>
<?php
 do_action('memberium/shortcodes/memb_change_password/after_form',  $data);
 ?>
<script>
	document.addEventListener("DOMContentLoaded", (event) => {
		jQuery("input[type=password]").hover(
			function() {
				jQuery(this).attr('type', 'text');
			}, function() {
				jQuery(this).attr('type', 'password');
			}
		);
	});
</script>

