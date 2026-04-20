<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 */
 defined('ABSPATH') || die();
 if (empty($data) ) {
 return;
 
} ?>
<form method="GET" action="<?= $data->action ?>">
	<input type="hidden" name="email" value="<?= $data->username ?>">
	<input type="hidden" name="password" value="<?= $data->password ?>">
	<input type="hidden" name="to" value="<?= $data->url ?>">
	<input type="submit" value="<?= $data->button_text ?>" id="<?= $data->css_id ?>" >
</form>

