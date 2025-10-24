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
global $wpdb;
if ($action == 'clone') {
	$id = intval($_GET['id']);
	$r = $wpdb->get_row( "SELECT * FROM academy_courses WHERE ID = $id" );
	$data = array(	'course_title' => 'CLONE-'.$r->course_title,
					'course_description' => $r->course_description,
				);
	$format = array('%s','%s');
	$wpdb->insert('academy_courses',$data,$format);
	$insert_id = $wpdb->insert_id;
	$url = je_return_full_url() .'?msg=Course%20Cloned%20(ID:%20'.$insert_id.')';
	wp_redirect($url);
	exit;
}
?>
<div id="content">		
<?php if (!empty($_GET['msg'])) { ?>		 
	<div style="margin: 10px 10px; background: yellow; padding: 20px;">
	<?php echo $_GET['msg']; ?>
	</div>
<?php } ?>
		
<h1>Courses:</h1>
<?php
switch ($_GET['order_by']) {
	case 'title_asc':
		$order_by = 'ORDER BY course_title ASC';
		break;
	case 'title_desc':
		$order_by = 'ORDER BY course_title DESC';
		break;
	case 'site_asc':
		$order_by = 'ORDER BY site ASC';
		break;
	case 'site_desc':
		$order_by = 'ORDER BY site DESC';
		break;
	case 'id_asc':
		$order_by = 'ORDER BY ID ASC';
		break;
	case 'id_desc':
		$order_by = 'ORDER BY ID DESC';
		break;
	case 'postid_asc':
		$order_by = 'ORDER BY post_id ASC';
		break;
	case 'postid_desc':
		$order_by = 'ORDER BY post_id DESC';
		break;
	default:
		$order_by = 'ORDER BY ID DESC';
		break;
}
$results = $wpdb->get_results( "SELECT * FROM academy_courses $order_by", OBJECT );
$x = 1;
?>
<table cellpadding="0" cellspacing="0" width="100%" class="sortable">
			
				<thead>
					<tr>
						<th></th>
						<th>Post ID <a href="?order_by=postid_asc"><i class="fa-solid fa-arrow-up-short-wide"></i></a> <a href="?order_by=postid_desc"><i class="fa-solid fa-arrow-down-wide-short"></i></a></th>
						<th>ID <a href="?order_by=id_asc"><i class="fa-solid fa-arrow-up-short-wide"></i></a> <a href="?order_by=id_desc"><i class="fa-solid fa-arrow-down-wide-short"></i></a></th>
						<th>Site <a href="?order_by=site_asc"><i class="fa-solid fa-arrow-up-short-wide"></i></a> <a href="?order_by=site_desc"><i class="fa-solid fa-arrow-down-wide-short"></i></a></th>
						<th>Course <a href="?order_by=title_asc"><i class="fa-solid fa-arrow-up-short-wide"></i></a> <a href="?order_by=title_desc"><i class="fa-solid fa-arrow-down-wide-short"></i></a></th>
						<th>Jami Done</th>
						<th>Clone</th>
					</tr>
				</thead>
				
				<tbody>
<?php
foreach ($results as $r  ) {
	// Check if all lessons in this course have jami_done = 'y'
	$lessons = $wpdb->get_results( "SELECT jami_done FROM academy_lessons WHERE course_id = $r->ID" );
	$total_lessons = count($lessons);
	$completed_lessons = 0;
	
	foreach ($lessons as $lesson) {
		if ($lesson->jami_done === 'y') {
			$completed_lessons++;
		}
	}
	
	// Determine status and background color
	if ($total_lessons == 0) {
		$jami_status = 'No Lessons';
		$background = '';
	} elseif ($completed_lessons == $total_lessons && $total_lessons > 0) {
		$jami_status = 'Complete';
		$background = 'style="background-color: lightgreen"';
	} elseif ($completed_lessons > 0) {
		$jami_status = "$completed_lessons/$total_lessons";
		$background = 'style="background-color: lightgray"';
	} else {
		$jami_status = "$completed_lessons/$total_lessons";
		$background = '';
	}
	
	echo "<tr $background>
			<td>$x)</td>
			<td>$r->post_id <a href='https://jazzedge.academy/wp-admin/post.php?post=$r->post_id&action=edit' target='_blank'><i class='fa-solid fa-up-right-from-square'></i></a></td>
			<td>$r->ID</td>
			<td>$r->site</td>
			<td><a href='edit_course.php?id=$r->ID' target='_blank'>$r->course_title</a></td>
			<td>$jami_status</td>
			<td><a href='?action=clone&id=$r->ID'>Clone</a></td>
		</tr>";
$x++;
}
?>
</table>
</div>
<?php include_once('includes/scripts.php'); ?>
<?php include_once('includes/footer.php'); ?>