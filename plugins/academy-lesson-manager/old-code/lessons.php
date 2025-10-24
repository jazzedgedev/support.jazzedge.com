<?php  


/*
Update the Vimeo download links later by calling https://developer.vimeo.com/api/reference/videos#get_video

From this call you can get the duration of the video and the download links
*/

/*
https://jazzedge.com/willie/academy_lessons_dbase.php?key=AjDNiUHkhb42ksG35jqsYL32fCsaG4
*/

// check for access

$page_title = 'Academy Admin';
include_once('includes/head.php'); 
include_once('includes/header.php');
$active_tab = 'lessons';
include_once('includes/sidebar.php'); 

$action = ($_GET['action']) ? $_GET['action'] : $_POST['action'];
$view = ($_GET['view']) ? $_GET['view'] : $_POST['view'];
global $wpdb;

if ($action == 'clone') {
	$id = intval($_GET['id']);
	$r = $wpdb->get_row( "SELECT * FROM academy_lessons WHERE ID = $id" );
	$data = array(	'lesson_title' => 'CLONE-'.$r->lesson_title,
					'post_date' => date('Y-m-d'),
					'lesson_description' => $r->lesson_description,
					'assets' => $r->assets,
					'course_id' => $r->course_id,
				);
	$format = array('%s','%s','%s','%s','%d');
	$wpdb->insert('academy_lessons',$data,$format);
	$insert_id = $wpdb->insert_id;
	$url = je_return_full_url() .'?msg=Lesson%20Cloned%20(ID:%20'.$insert_id.')';
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
		
<h1>Lessons:</h1>
<div style="width:50%; margin:10px 0px;">
<form method="post">
	Search: 
	<input type="text"  name="search"  class="form" value="<?php echo $_POST['search']; ?>"> 
	<button type="submit" class="button small blue">Search</button>
	

	<input type="hidden" name="action" value="action" />
	
</form>
</div>

<?php
switch ($_GET['order_by']) {
	case 'date_asc':
		$order_by = 'ORDER BY post_date ASC';
		break;
	case 'date_desc':
		$order_by = 'ORDER BY post_date DESC';
		break;
	case 'title_asc':
		$order_by = 'ORDER BY lesson_title ASC';
		break;
	case 'title_desc':
		$order_by = 'ORDER BY lesson_title DESC';
		break;
	case 'postid_asc':
		$order_by = 'ORDER BY post_id ASC';
		break;
	case 'postid_desc':
		$order_by = 'ORDER BY post_id DESC';
		break;
	case 'id_asc':
		$order_by = 'ORDER BY ID ASC';
		break;
	case 'id_desc':
		$order_by = 'ORDER BY ID DESC';
		break;
	case 'success':
		$order_by = 'ORDER BY success_lesson DESC';
		break;
	case 'sku':
		$order_by = 'ORDER BY sku DESC';
		break;
	default:
		$order_by = 'ORDER BY post_date DESC';
		break;
}

$where = '';
if (isset($_POST['search'])) {
	$search = sanitize_text_field($_POST['search']);
	$where = " WHERE lesson_title LIKE '%$search%' OR lesson_description LIKE '%$search%' ";
} 

$results = $wpdb->get_results( "SELECT * FROM academy_lessons $where $order_by", OBJECT );
$x = 1;
?>


<table cellpadding="0" cellspacing="0" width="100%" class="sortable">
			
				<thead>
					<tr>
						<th></th>
						<th>Post ID <a href="?order_by=postid_asc"><i class="fa-solid fa-arrow-up-short-wide"></i></a> <a href="?order_by=postid_desc"><i class="fa-solid fa-arrow-down-wide-short"></i></a></th>
						<th>ID <a href="?order_by=id_asc"><i class="fa-solid fa-arrow-up-short-wide"></i></a> <a href="?order_by=id_desc"><i class="fa-solid fa-arrow-down-wide-short"></i></a></th>
						<th>Date <a href="?order_by=date_asc"><i class="fa-solid fa-arrow-up-short-wide"></i></a> <a href="?order_by=date_desc"><i class="fa-solid fa-arrow-down-wide-short"></i></a></th>
						<th>Site<a href="?order_by=site_asc"><i class="fa-solid fa-arrow-up-short-wide"></i></a> <a href="?order_by=site_desc"><i class="fa-solid fa-arrow-down-wide-short"></i></a></th>
						<th>SKU<a href="?order_by=sku"><i class="fa-solid fa-arrow-up-short-wide"></i></a></th>
						<th>Lesson <a href="?order_by=title_asc"><i class="fa-solid fa-arrow-up-short-wide"></i></a> <a href="?order_by=title_desc"><i class="fa-solid fa-arrow-down-wide-short"></i></a></th>
						<th>Success <a href="?order_by=success"><i class="fa-solid fa-arrow-up-short-wide"></i></a></th>
						<th>Song</th>
						<th>Skill Level</th>
						<th>Course</th>
						<th>Duration</th>
						<th>Clone</th>
					</tr>
				</thead>
				
				<tbody>

<?php

foreach ($results as $r  ) {
	$course_title = je_return_course_title($r->course_id);
	$background = ($r->jami_done === 'y') ? 'yellow' : 'white';
	echo "<tr style='background-color:$background'>
			<td>$x)</td>
			<td>$r->post_id <a href='https://jazzedge.academy/wp-admin/post.php?post=$r->post_id&action=edit' target='_blank'><i class='fa-solid fa-up-right-from-square'></i></a></td>
			<td>$r->ID</td>
			<td>$r->post_date</td>
			<td>$r->site</td>
			<td>$r->sku</td>
			<td><a href='edit_lesson.php?id=$r->ID'>".stripslashes($r->lesson_title)."</a> <a href='edit_lesson.php?id=$r->ID&show_chapters=y'><i class=\"fa-solid fa-video\"></i></a></td>
			<td>".$r->success_lesson." (".$r->success_order.") ".$r->success_style."</td>
			<td>".$r->song_lesson."</td>
			<td>".get_post_meta( $r->post_id, 'lesson_skill_level', true )."</td>
			<td><a href='edit_course.php?id=$r->course_id'>".stripslashes($course_title)."</a></td>
			<td>".convertToHoursMins($r->duration)."</td>
			<td><a href='?action=clone&id=$r->ID'>Clone</a></td>
		</tr>";
$x++;
}
?>
</table>
</div>

<?php include_once('includes/scripts.php'); ?>
<?php include_once('includes/footer.php'); ?>


