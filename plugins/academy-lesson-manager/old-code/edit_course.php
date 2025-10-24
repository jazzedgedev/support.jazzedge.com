<?php  


/*
Update the Vimeo download links later by calling https://developer.vimeo.com/api/reference/videos#get_video

From this call you can get the duration of the video and the download links
*/

/*
https://jazzedge.com/willie/academy_courses_dbase.php?key=AjDNiUHkhb42ksG35jqsYL32fCsaG4
*/

// check for access

$page_title = 'Academy Admin';
include_once('includes/head.php'); 
include_once('includes/header.php');
$active_tab = 'courses';
include_once('includes/sidebar.php'); 

$action = ($_GET['action']) ? $_GET['action'] : $_POST['action'];
$view = ($_GET['view']) ? $_GET['view'] : $_POST['view'];
$resources = array();
global $wpdb;

if ($action == 'add' ) {
} elseif ($action == 'update' ) {
	$post_id = ($_POST['post_id'] > 0) ? $_POST['post_id'] : 0;
	if ($post_id == 0) {
		$post_id = wp_insert_post(
			array(
				'post_date' 		=>	date('Y-m-d H:i:s'),
				'comment_status'	=>	'closed',
				'ping_status'		=>	'closed',
				'post_author'		=>	1972,
				'post_name'			=>	sanitize_title($_POST['course_title']),
				'post_title'		=>	sanitize_text_field($_POST['course_title']),
				'post_status'		=>	'publish',
				'post_type'			=>	'course',
				'post_content' 		=> 	stripslashes_deep($_POST['course_description']),
				'meta_input' => array(
					'course_id' => intval($_POST['id']),
					'course_site' => strtoupper(sanitize_title($_POST['course_site'])),
				   )
			)
		);
	} else {
		$update_post_data = array(
			  	'ID'           		=> 	$post_id,
			  	'post_date' 		=>	date('Y-m-d H:i:s'),
				'comment_status'	=>	'closed',
				'ping_status'		=>	'closed',
				'post_author'		=>	1972,
				'post_name'			=>	sanitize_title($_POST['course_title']),
				'post_title'		=>	sanitize_text_field($_POST['course_title']),
				'post_status'		=>	'publish',
				'post_type'			=>	'course',
				'post_content' 		=> 	stripslashes_deep($_POST['course_description']),
				'meta_input' => array(
					'course_id' => intval($_POST['id']),
					'course_site' => strtoupper(sanitize_title($_POST['course_site'])),
				   )
		  );
		// Update the post into the database
		wp_update_post( $update_post_data );
	}

	$wpdb->update('academy_courses', array(
			'post_id' => $_POST['post_id'],
			'course_title' => sanitize_text_field($_POST['course_title']),
			'course_description' => stripslashes_deep($_POST['course_description']),
			'site' => strtoupper(sanitize_title($_POST['course_site'])),

	),array ('ID' => $_POST['id']));
	$url = je_return_full_url() .'&msg=Course%20Updated';
	wp_redirect($url);
	exit;

} elseif ($action == 'delete_resource') {
} elseif ($action == 'update_chapter') {
} elseif ($action == 'add_chapter') {
} elseif ($action == 'delete_chapter') {
} elseif ($action == 'delete_course') {
	$course_id = intval($_GET['course_id']);
	$wpdb->delete( 'academy_courses', array( 'ID' => $course_id) );
	$url = 'courses.php?msg=Course%20Deleted';
	wp_redirect($url);
	exit;
} elseif ($action == 'add_to_suggested') {
} elseif ($action == 'clone') {
	$id = intval($_GET['id']);
	$r = $wpdb->get_row( "SELECT * FROM academy_courses WHERE ID = $id" );
	$data = array(	'course_title' => 'CLONE-'.$r->course_title,
					'course_description' => $r->course_description,
				);
	$format = array('%s','%s');
	$wpdb->insert('academy_courses',$data,$format);
	$insert_id = $wpdb->insert_id;
	$url = 'edit_course.php?id='.$insert_id.'&msg=Course%20Cloned%20(ID:%20'.$insert_id.')';
	wp_redirect($url);
	exit;
}
?>


<div id="content">		

<h1>Edit Course</h1>
<?php
$x = 1;
$r = $wpdb->get_row( "SELECT * FROM academy_courses WHERE ID = ".intval($_GET['id']) );
?>

<script language="Javascript" type="text/javascript">
	function ConfirmPrompt(sMessage, sUrl)  {
	  var ok = null;              
	  ok = confirm(sMessage);
			
	  if (ok)
			window.open(sUrl, '_self');
	}
	</script>
	
<?php 
			$course_id = $r->ID;
			$post_id = $r->post_id;
			if ($post_id == 0) {
				$post_id = $wpdb->get_var( "SELECT post_id FROM wp_postmeta WHERE meta_key = 'course_id' AND meta_value = $course_id " );
				$wpdb->update('academy_courses', array( 'post_id' => $post_id ),array ('ID' => $course_id));
			}
		 ?>

<?php if (!empty($_GET['msg'])) { ?>		 
	<div style="background: yellow; padding: 20px;">
	<?php echo $_GET['msg']; ?>
	</div>
<?php } ?>
		
		<form action="" method="post" id="update_course">
			<p>
			Course ID: <?php echo $course_id; ?> (<a href="javascript:ConfirmPrompt('Are you sure you want to delete this course?', '?action=delete_course&course_id=<?php echo $course_id; ?>');">delete course</a>) - <a href="?action=clone&id=<?php echo $_GET['id'];?>">clone course</a> | <a href="https://jazzedge.academy/wp-admin/post.php?post=<?php echo $post_id;?>&action=edit" target="_blank">edit in wordpress</a>
			</p>
			
			<p>
				<label>Post ID (wordpress):</label><br />
				<input type="text" disabled size="30" class="text" name="post_id" value="<?php echo $post_id; ?>" /> 
			</p>
			
			
			<p>
				<label>Course Site:</label><br />
				<select name="course_site">
					<option value="JE" <?php if ($r->site == "JE") { echo 'selected'; }?>>JE</option>
					<option value="JPD" <?php if ($r->site == "JPD") { echo 'selected'; }?>>JPD</option>
					<option value="SPJ" <?php if ($r->site == "SPJ") { echo 'selected'; }?>>SPJ</option>
					<option value="JCM" <?php if ($r->site == "JCM") { echo 'selected'; }?>>JCM</option>
					<option value="TTM" <?php if ($r->site == "TTM") { echo 'selected'; }?>>TTM</option>
					<option value="CPL" <?php if ($r->site == "CPL") { echo 'selected'; }?>>CPL</option>
					<option value="FPL" <?php if ($r->site == "FPL") { echo 'selected'; }?>>FPL</option>
					<option value="RPL" <?php if ($r->site == "RPL") { echo 'selected'; }?>>RPL</option>
					<option value="PBP" <?php if ($r->site == "PBP") { echo 'selected'; }?>>PBP</option>
					<option value="JPT" <?php if ($r->site == "JPT") { echo 'selected'; }?>>JPT</option>
					<option value="MTO" <?php if ($r->site == "MTO") { echo 'selected'; }?>>MTO</option>
				</select>
			</p>

			
			<p>
				<label>Course Title:</label><br />
				<input type="text" size="100" class="text" name="course_title"  value="<?php echo stripslashes(htmlspecialchars($r->course_title)); ?>" />
			</p>
			
			<p>
				<label>Course Description:</label><br />
				<textarea rows="7" cols="103" name="course_description"><?php echo $r->course_description; ?></textarea>
			</p>
					
	<input type="hidden" name="post_id" value="<?php echo $post_id;?>">
	<input type="hidden" name="id" value="<?php echo $r->ID;?>">
	<input type="hidden" name="action" value="update">
	<button type="submit" class="button large red">Update</button>
</form>
<h2>Lessons in This Course:</h2>
<div class='rg-container hover-black'>
	<table class='rg-table zebra' summary='Hed'>
		<thead>
			<tr>
				<th class='text' width='20%'>Title</th>
				<th class='text'>SKU</th>
				<th class='text'>Jami Done?</th>
				<th class='text'>Description</th>
				<th class='text'>Duration</th>
			</tr>
		</thead>
		<tbody>
		<?php 

$lessons = $wpdb->get_results( "SELECT * FROM academy_lessons WHERE course_id = $course_id " );
foreach ($lessons AS $lesson) {
	$background = ($lesson->jami_done === 'y') ? 'yellow' : 'white';
	echo "<tr><td><a href='https://jazzedge.academy/willie/ja-admin/edit_lesson.php?show_chapters=y&id=$lesson->ID' target='_blank'>".stripslashes($lesson->lesson_title)."</a></td>
	<td>".stripslashes($lesson->sku)."</td>
	<td style='background-color: {$background}'>".$lesson->jami_done."</td>
	<td>".stripslashes($lesson->lesson_description)."</td>
	<td>".convertToHoursMins($lesson->duration)."</td></tr>";
}
 ?>	
		</tbody>
	</table>
</div>
</div>
<br><br>

<?php include_once('includes/scripts.php'); ?>
<?php include_once('includes/footer.php'); ?>


