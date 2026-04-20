<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 */
 defined('ABSPATH') || die();
 wp_enqueue_script('jquery',  null,  null,  null,  true);
 $domain = 'memberium';
 $label_card_number = _x('Card Number:',  $name,  $domain);
 $label_card_type = _x('Card Type:',  $name,  $domain);
 $label_city = _x('City:',  $name,  $domain);
 $label_country = _x('Country:',  $name,  $domain);
 $label_cvv = _x('CVV Code:',  $name,  $domain);
 $label_expiration_month = _x('Expiration Month:',  $name,  $domain);
 $label_expiration_year = _x('Expiration Year:',  $name,  $domain);
 $label_name_on_card = _x('Name on Card:',  $name,  $domain);
 $label_phone_number = _x('Phone Number:',  $name,  $domain);
 $label_postalcode = _x('Postal Code:',  $name,  $domain);
 $label_state = _x('State/Province:',  $name,  $domain);
 $label_street_address = _x('Street Address:',  $name,  $domain);
 $label_submit = _x('Submit',  $name,  $domain);
 $placeholder_address1 = _x('Address Line 1',  $name,  $domain);
 $placeholder_address2 = _x('Optional Address Line 2',  $name,  $domain);
 $placeholder_card_number = _x('Credit Card Number',  $name,  $domain);
 $placeholder_city = _x('City',  $name,  $domain);
 $placeholder_name_on_card = _x('Full Name on Card',  $name,  $domain);
 $placeholder_phone_number = _x('Best Phone Number',  $name,  $domain);
 $placeholder_postalcode = _x('Zip or Postal Code',  $name,  $domain);
 $placeholder_state = _x('N/A if none',  $name,  $domain);
 ?><style>
	#<?= $data->form_name ?> div { 
		margin-bottom:12px;
	}
	#<?= $data->form_name ?> label { 
		display:inline-block;
		width:150px;
	}
	::-webkit-input-placeholder {
		font-style: italic;
	}
	:-moz-placeholder {
		font-style: italic;  
	}
	::-moz-placeholder {
		font-style: italic;  
	}
	:-ms-input-placeholder {  
		font-style: italic; 
	}
</style>
<form name="<?= $data->form_name ?>" id="<?= $data->form_name ?>" method="post">
	<?= $data->nonce ?>
	<input type="hidden" name="memb_form_type" value="memb_add_creditcard_button">
	<input type="hidden" name="form_id" value="<?= $data->form_id ?>">
	<input type="hidden" name="parameters" value="<?= $data->parameters ?>">
	<input type="hidden" name="signature" value="<?= $data->signature ?>">
	<div>
		<label><?= $label_card_number ?></label>
		<input name="cc-number" value="<?= $data->card_number ?>" <?= $data->disabled ?>  placeholder="<?= $placeholder_card_number ?>" type="text" size="20" maxlength="20" required="required" pattern="[0-9]{13,16}" x-autocompletetype="cc-number" autocomplete="cc-number">
	</div>
	<div>
		<label><?= $label_card_type ?></label>
		<select name="cardtype" <?= $data->disabled ?> required="required" autocompelete="cc-type">
			<option value=""></option>
			<?php
 foreach ($data->card_types as $card_type) {
 $card_selected = $card_type == $data->creditcard_type ? ' selected="selected" ' : ' ';
 echo "<option value='{$card_type
}' {$card_selected
}>{$card_type
}</option>";
 
} ?>
		</select>
	</div>
	<div>
		<label><?= $label_expiration_month ?></label>
		<select name="expirationmonth" required="required" autocomplete="cc-exp-month">
			<?php
 for ($i = 1;
 $i < 13;
 $i++) {
 $month_name = date('F',  mktime(1,  1,  1,  $i,  1,  2000) );
 $month_name = _x($month_name,  'memb_add_creditcard',  'memberium');
 $month_number = sprintf('%02d',  $i);
 $selected = $data->expiration_month == $i ? ' selected="selected" ' : '';
 echo "<option {$selected
} value='{$month_number
}'>{$month_number
} - {$month_name
}</option>";
 
} ?>
		</select>
	</div>
	<div>
		<label><?= $label_expiration_year ?></label>
		<select name="expirationyear" required="required" autocomplete="cc-exp-year">
		<?php
 for ($i = date('Y');
 $i < date('Y') + 15;
 $i++) {
 $selected = $data->expiration_year == $i ? ' selected="selected" ' : '';
 echo "<option {$selected
} value='{$i
}'>{$i
}</option>";
 
} ?>
		</select>
	</div>
	<div>
		<label><?= $label_cvv ?></label>
		<input name="cvv2" type="text" size="4" maxlength="4" required placeholder="" value="" autocomplete="cc-csc">
	</div>
	<div>
		<label><?= $label_name_on_card ?></label>
		<input name="nameoncard" value="<?= $data->name_on_card ?>" placeholder="<?= $placeholder_name_on_card ?>" type="text" size="30" required="required" autocomplete="cc-name">
	</div>
	<div>
		<label><?= $label_phone_number ?></label>
		<input name="phonenumber" value="<?= $data->phone1 ?>" placeholder="<?= $placeholder_phone_number ?>" type="tel" size="20" required="required" autocomplete="tel">
	</div>
	<div>
		<label><?= $label_street_address ?></label>
		<input name="streetaddress1" value="<?= $data->address1 ?>" placeholder="<?= $placeholder_address1 ?>" size="30" type="text" required="required" autocomplete="address-line1">
	</div>
	<div>
		<label><?= $label_street_address ?></label>
		<input name="streetaddress2" value="<?= $data->address2 ?>" placeholder="<?= $placeholder_address2 ?>" size="30" type="text" autocomplete="address-line2">
	</div>
	<div>
		<label><?= $label_city ?></label>
		<input name="city" value="<?= $data->city ?>" placeholder="<?= $placeholder_city ?>" type="text" required="required" autocomplete="address-level2">
	</div>
	<div>
		<label><?= $label_state ?></label>
		<input name="state" value="<?= $data->state ?>" placeholder="<?= $placeholder_state ?>" type="text" size="30" required="required" autocomplete="address-level1">
	</div>
	<div>
		<label><?= $label_postalcode ?></label>
		<input name="postalcode" value="<?= $data->postalcode ?>" placeholder="<?= $placeholder_postalcode ?>" type="text" size="15" required="required" autocmoplete="">
	</div>
	<div>
		<label><?= $label_country ?></label>
		<select name="country" required="required">
			<?= $data->country_options_html ?>
		</select>
	</div>
	<div>
		<label></label>
		<input name="defaultcard" value="<?= $label_submit ?>" type="submit">
	</div>
</form>

<script>
	document.addEventListener("DOMContentLoaded", (event) => {
		jQuery("input[name=cc-number]").hover(
			function() {
				jQuery(this).attr('type', 'text');
			}, function() {
				jQuery(this).attr('type', 'password');
			}
		);

		jQuery("input[name=cc-number],input[name=cvv2]").on('keypress', function(e) {
			return e.metaKey || // cmd/ctrl
			e.which <= 0 || // arrow keys
			e.which == 8 || // delete key
			/[0-9]/.test(String.fromCharCode(e.which)); // numbers
		});

	});
</script>

