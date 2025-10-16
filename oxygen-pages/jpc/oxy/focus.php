<div class="focus_div">
<?php
global $user_id, $jpc, $vimeo_id, $key_sig, $key_sig_name, $resource_pdf, $resource_ireal, $resource_mp3, $curriculum_id, $tempo, $instructions, $is_active_member, $step_id, $focus, $user_membership_level;

if ($is_active_member === 'true' || $is_active_member === 'false' && $curriculum_id < 2) {
    //echo '<pre><h2>$jpc</h2>'; print_r($jpc); echo '</pre>';
    echo "<p>
    <span class='focus_label'>Focus:</span> <span class='capitalize'><strong>$focus</strong></span><br />
    <span class='focus_label'>Key Of:</span> <strong>$key_sig_name</strong><br />
    <span class='focus_label'>Suggested Tempo:</span> <strong>$tempo BPM</strong><br />";
    
    if (!empty($resource_pdf)){
      echo "<span class='focus_label'>Sheet Music:</span> <strong><a href='/jpc_resources/$resource_pdf' target='_blank'><i class='fa-sharp fa-solid fa-music'></i> Download</a></strong><br />
    ";
    }
    if (!empty($resource_ireal)){
      echo "<span class='focus_label'>iRealPro:</span> <strong><a href='/jpc_resources/$resource_ireal' target='_blank'><i class='fa-sharp fa-solid fa-music'></i> Download</a></strong><br />
    ";
    }
    if (!empty($resource_mp3)){
      echo "<span class='focus_label'>mp3 Backing Track:</span> <strong><a href='/jpc_resources/$resource_mp3' target='_blank'><i class='fa-sharp fa-solid fa-music'></i> Download</a></strong><br />
    ";
    }
    echo "<span class='focus_label'>Instructions:</span>  
    <div class='focus_instructions'>$instructions</div>
    </p>";
    ?>
    </div>
    <div class="center" style="margin-top: 20px;">
    	<form method="post" action="/jpc-completed">
    	<input type="hidden" name="action" value="complete_focus" />
    	<input type="hidden" name="step_id" value="<?php echo $step_id; ?>" />
    	<input type="hidden" name="curriculum_id" value="<?php echo $curriculum_id; ?>" />
    	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
    	<button type="submit" class="je-button-square jpc-complete" onclick="disableButton(this)">Mark Complete <i class="fa-sharp fa-solid fa-circle-check"></i></button>
    	</form>
<?php } else { ?>
<p>As a free member of the site, you can view the first focus. If you would like to access the full curriculum (and learn all your necessary chords, scales and rhythms to be a piano super-star) please <a href="https://jazzedge.academy/#signup">upgrade your membership</a>.</p>
<?php } ?>
</div>