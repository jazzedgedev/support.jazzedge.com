<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 */
 defined('ABSPATH') || die();
 if (empty($data) ) {
 return;
 
} ?>
<form name="<?= $data->form_name ?>" id="<?= $data->form_name ?>" action="<?= $data->action ?>" target="_blank" method="POST">
	<input type="hidden" name="Login" value="Login" />
	<input type="hidden" name="j_username" value="<?= $data->affiliate_code ?>" />
	<input type="hidden" name="j_password" value="<?= $data->affiliate_password ?>" />
	<input type="hidden" name="_spring_security_remember_me" value="1" />
	<input type="submit" name="submit" value="<?= $data->button_label ?>" style="<?= $data->button_style ?>" class="<?= $data->button_class ?>" />
</form>
<script>
	document.forms['<?= $data->form_name ?>'].submit();
</script>

