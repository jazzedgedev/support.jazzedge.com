<?php
$page_title = 'JAZZEDGE Admin';

include_once('includes/head.php'); 
include_once('includes/header.php');
$active_tab = 'milestone_submission';
include_once('includes/sidebar.php'); 

global $app;
    if (!$app) {
    	global $install, $app;
		include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
    }


$action = (isset($_POST['action'])) ? $_POST['action'] : $_GET['action'];

if ($action == 'show_graded') {
	$q = $wpdb->get_results( "SELECT * FROM je_practice_milestone_submissions WHERE grade IS NOT NULL ORDER BY graded_on DESC" );
} else { 
	$q = $wpdb->get_results( "SELECT * FROM je_practice_milestone_submissions WHERE grade IS NULL && video_url != '' " );
}

if ($action == 'show_redo') {
	$q = $wpdb->get_results( "SELECT * FROM je_practice_milestone_submissions WHERE grade = 'redo' ORDER BY submission_date DESC" );
} 

if ($action == 'error') {
	$id = intval($_POST['id']);
	$wpdb->delete( 'je_practice_milestone_submissions', array( 'ID' => $id ) );
	$url = '?msg=Error Message Sent to Student';
	$infusion_id = intval($_POST['infusion_id']);
	$app->grpAssign($infusion_id,9751);
	wp_redirect($url);
	exit;
}

if ($action == 'grade') {
	$id = intval($_POST['id']);
	if ($_POST['grade'] != '') {
		$wpdb->update('je_practice_milestone_submissions', array(
				'grade' => sanitize_text_field($_POST['grade']),
				'graded_on' => date('Y-m-d'),
				'teacher_notes' => sanitize_text_field($_POST['teacher_notes']),
		),array ('ID' => $id));
		$url = '?msg=Graded';
		$infusion_id = intval($_POST['infusion_id']);
		$app->grpAssign($infusion_id,9637);
		wp_redirect($url);
		exit;
	} else {
		$url = '?msg=EMPTY-GRADE';
		wp_redirect($url);
		exit;
	}
}

if ($action == 'hide') {
	$id = intval($_GET['id']);
	$wpdb->update('je_practice_milestone_submissions', array(
			'grade' => 'HIDE'
	),array ('ID' => $id));
	$url = '?msg=Hidden';
	wp_redirect($url);
	exit;
}


function return_milestone($curriculum_id){
	global $wpdb;
	$curriculum_id = intval($curriculum_id);
	$q = $wpdb->get_row( "SELECT * FROM je_practice_curriculum WHERE ID = $curriculum_id " );
	return $q->focus_title . '<br>(tempo: '.$q->tempo.')';
}


?>	


<div id="content">
	<h2>Milestone Submissions:</h2>
		<?php if ($_GET['msg'] !='') { echo '<p style="background: yellow; padding: 6px;">Message: '.$_GET['msg'].'</p>'; } ?>
		<a href="?">Show To Be Graded </a> &bull; 
		<a href="?action=show_graded">Show Last 100 Graded</a> &bull;
		<a href="?action=show_redo">Show Redo</a>
		
		<br>
		<table cellpadding="0" cellspacing="0" width="100%" class="sortable">
				<thead>
					<tr>
						<th>ID</th>
						<th>User</th>
						<th>Submission Date</th>
						<th>Grade (On Date)</th>
						<th width="200">Curriculum ID / Milestone</th>
						<th>Video URL</th>
						<th>Grade / Note</th>
						
					</tr>
				</thead>
				
				<tbody>	

<?php	
	$x = 1;
	foreach ($q AS $r) {
			$user_id = $r->user_id;
			$user_info = get_userdata($user_id);
			$email = $user_info->user_email;
			$first_name = $user_info->first_name;
			$last_name = $user_info->last_name;
			$infusion_id = $r->infusion_id;
			$video_url = (empty($r->video_url)) ? $r->video_url_graded : $r->video_url;
			$grade = (empty($r->grade)) ? 'TBG' : $r->grade;
			echo '<tr>';
			echo '<td>'.$r->ID.'</td>';
			echo '<td><a id="hide_milestone" href="?action=hide&id='.$r->ID.'"><i class="fa-solid fa-circle-xmark"></i></a> '.$first_name.' ' . $last_name. ' ('.$user_id.') <a id="login_as_student" href="https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id='.$infusion_id.'&Email='.$email.'&redir=/dashboard" target="_blank"><i class="fa-solid fa-square-arrow-up-right"></i></a></td>';
			echo "<td>$r->submission_date</td>";
			echo "<td>$grade ($r->graded_on)</td>";
			echo '<td>#'.$r->curriculum_id.') '.return_milestone($r->curriculum_id).'</td>';
			echo "<td><a href='{$video_url}' target='_blank'><i class='fa-solid fa-circle-play' style='font-size:20pt;'></i>video</a></td>";
			$grade_pass = ($r->grade == 'pass') ? 'selected = "selected"' : '';
			$grade_redo = ($r->grade == 'redo') ? 'selected = "selected"' : '';
			echo '<td><form method="post">
					<p><select name="grade">
						<option value="">Grade...</option>
						<option value="pass" '.$grade_pass.'>PASS</option>
						<option value="redo" '.$grade_redo.'>REDO</option>
					</select><br /> ';
			echo '<textarea onClick="this.select()" name="teacher_notes" rows="4" cols="50" maxlength="255">'.stripslashes($r->teacher_notes).'</textarea></p>';
			echo '<input type="hidden" name="action" value="grade" />
			<input type="hidden" name="infusion_id" value="'.$r->infusion_id.'" />
			<input type="hidden" name="video_url" value="'.$r->video_url.'" />
			<input type="hidden" name="id" value="'.$r->ID.'" />
			<input type="submit" class="submit" value="Submit Grade" /></form>&nbsp;
			
			<form method="post">
			<input type="hidden" name="action" value="error" />
			<input type="hidden" name="infusion_id" value="'.$r->infusion_id.'" />
			<input type="hidden" name="id" value="'.$r->ID.'" />
			<input type="submit" id="milestone_error" class="submit" style="background: red; border: white;" value="Delete & Inform Student" onclick="return confirm(\'Are you sure?\')" /></form>
			
			</td>';
			echo '</tr>';
			$x++;
		}


	
	
	?>
	
</div>	


<?php include_once('includes/scripts.php'); ?>
<?php include_once('includes/footer.php'); ?>

