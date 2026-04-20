<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 */
 defined('ABSPATH') || die();
 if (empty($data->users) ) {
 echo '<p>You have no users to transfer points to.</p>';
 return;
 
} if (empty($data->points) ) {
 echo '<p>You have no points to transfer to users.</p>';
 return;
 
} ?>
<p>You have <?php echo $data->points;
 ?> points available.</p>
<form method="post">
	<?php echo $data->headers;
 ?>
	<p>Transfer <input name="points" value="0" type="number" min="0" max="<?php echo $data->points;
 ?>"> points to:
	<select name="recipient">
	<?php
 foreach($data->users as $user) {
 echo "<option value='{$user->user_email
}'>{$user->display_name
} ({$user->user_email
})</option>";
 
} ?>
	</select></p>
	<input type="submit" class="<?php echo $atts['button_class'];
 ?>" value="<?php echo $atts['button_text'];
 ?>">
</form>

