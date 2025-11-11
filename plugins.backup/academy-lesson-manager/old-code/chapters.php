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
$active_tab = 'chapters';
include_once('includes/sidebar.php'); 

$action = ($_GET['action']) ? $_GET['action'] : $_POST['action'];
$view = ($_GET['view']) ? $_GET['view'] : $_POST['view'];
global $wpdb;

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
	default:
		$order_by = 'ORDER BY post_date DESC';
		break;
}

$where = '';
if (isset($_POST['search'])) {
	$search = sanitize_text_field($_POST['search']);
	$where = " WHERE chapter_title LIKE '%$search%'  ";
} 

$results = $wpdb->get_results( "SELECT * FROM academy_chapters $where $order_by", OBJECT );
$x = 1;
?>


<table cellpadding="0" cellspacing="0" width="100%" class="sortable">
			
				<thead>
					<tr>
						<th></th>
						<th>ID <a href="?order_by=id_asc"><i class="fa-solid fa-arrow-up-short-wide"></i></a> <a href="?order_by=id_desc"><i class="fa-solid fa-arrow-down-wide-short"></i></a></th>
						<th>Lesson <a href="?order_by=title_asc"><i class="fa-solid fa-arrow-up-short-wide"></i></a> <a href="?order_by=title_desc"><i class="fa-solid fa-arrow-down-wide-short"></i></a></th>
						<th>Title <a href="?order_by=success"><i class="fa-solid fa-arrow-up-short-wide"></i></a></th>
						<th>Vimeo ID</th>
						<th>Duration</th>
					</tr>
				</thead>
				
				<tbody>

<?php

foreach ($results as $r  ) {
	$course_title = je_return_course_title($r->course_id);
	$lesson_permalink = get_permalink($r->lesson_id);
	echo "<tr $background>
			<td>$r->ID</td>
			<td><a href='https://jazzedge.academy/willie/ja-admin/edit_lesson.php?id=$r->lesson_id' target='_blank'>".je_return_lesson_title($r->lesson_id)."</a></td>
			<td>$r->chapter_title</td>
			<td>$r->vimeo_id</td>
			<td>$r->duration</td>
		</tr>";
$x++;
}
?>
</table>
</div>

<?php include_once('includes/scripts.php'); ?>
<?php include_once('includes/footer.php'); ?>


