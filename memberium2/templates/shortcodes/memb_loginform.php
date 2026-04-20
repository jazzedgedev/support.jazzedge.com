<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 */
 defined('ABSPATH') || die();
 $args = [ 'echo' => true,  'form_id' => $data->form_id,  'label_log_in' => $data->button_label,  'label_password' => $data->password_label,  'label_remember' => $data->remember_label,  'label_username' => $data->username_label,  'redirect' => $data->redirect,  'remember' => $data->remember,  ];
 wp_login_form($args);
 ?>
<script>
	jQuery('#user_login, #user_pass').prop('required', true);

	jQuery('#user_pass').hover( function() {
	    jQuery('#user_pass').attr('type', 'text');
	}, function() {
    	jQuery('#user_pass').attr('type', 'password');
	});
</script>

