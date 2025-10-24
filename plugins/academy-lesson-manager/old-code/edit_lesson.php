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
$resources = array();
global $wpdb;





if ($action == 'add' ) {
	$assets = array();
	$assets['pdf'] = (substr($_POST['sheet_music'],0,9) == '/jazzedge') ? substr($_POST['sheet_music'],20) : $_POST['sheet_music'];
	$assets['pdf2'] = (substr($_POST['sheet_music2'],0,9) == '/jazzedge') ? substr($_POST['sheet_music2'],20) : $_POST['sheet_music2'];
	$assets['pdf3'] = (substr($_POST['sheet_music3'],0,9) == '/jazzedge') ? substr($_POST['sheet_music3'],20) : $_POST['sheet_music3'];
	$assets['pdf4'] = (substr($_POST['sheet_music4'],0,9) == '/jazzedge') ? substr($_POST['sheet_music4'],20) : $_POST['sheet_music4'];
	$assets['pdf5'] = (substr($_POST['sheet_music5'],0,9) == '/jazzedge') ? substr($_POST['sheet_music5'],20) : $_POST['sheet_music5'];
	$assets['mp3'] = (substr($_POST['mp3'],0,10) == 'https://s3') ? substr($_POST['mp3'],44) : $_POST['mp3'];
	$assets['zip'] = (substr($_POST['zip'],0,10) == 'https://s3') ? substr($_POST['zip'],44) : $_POST['zip'];
	$assets['midi'] = (substr($_POST['midi'],0,10) == 'https://s3') ? substr($_POST['midi'],44) : $_POST['midi'];
	$assets['jam'] = (substr($_POST['jam'],0,10) == 'https://s3') ? substr($_POST['jam'],44) : $_POST['jam'];
	$assets['jam2'] = (substr($_POST['jam2'],0,10) == 'https://s3') ? substr($_POST['jam2'],44) : $_POST['jam2'];
	$assets['jam3'] = (substr($_POST['jam3'],0,10) == 'https://s3') ? substr($_POST['jam3'],44) : $_POST['jam3'];
	$assets['note'] = (substr($_POST['note'],0,10) == 'https://s3') ? substr($_POST['note'],44) : $_POST['note'];
	$assets['ireal'] = (substr($_POST['ireal'],0,9) == '/jazzedge') ? substr($_POST['ireal'],20) : $_POST['ireal'];
	$assets['ireal2'] = (substr($_POST['ireal2'],0,9) == '/jazzedge') ? substr($_POST['ireal2'],20) : $_POST['ireal2'];
	$assets['ireal3'] = (substr($_POST['ireal3'],0,9) == '/jazzedge') ? substr($_POST['ireal3'],20) : $_POST['ireal3'];
	$assets['callresponse1'] = (substr($_POST['callresponse1'],0,9) == '/jazzedge') ? substr($_POST['callresponse1'],20) : $_POST['callresponse1'];
	$assets['callresponse2'] = (substr($_POST['callresponse2'],0,9) == '/jazzedge') ? substr($_POST['callresponse2'],20) : $_POST['callresponse2'];
	$assets['callresponse3'] = (substr($_POST['callresponse3'],0,9) == '/jazzedge') ? substr($_POST['callresponse3'],20) : $_POST['callresponse3'];

	//vtt
	$vtt = (substr($_POST['vtt'],0,10) == '/subtitles') ? substr($_POST['vtt'],11) : $_POST['vtt'];
	
	$wpdb->insert('academy_lessons', array(
			'site' => $_POST['site'],
			'post_title' => stripslashes($_POST['post_title']),
			'post_date' => date('Y-m-d', strtotime($_POST['post_date'])),
			'vimeo_id' => $_POST['vimeo_id'],
			'assets' => serialize($assets),
			'virt_kybd' => $_POST['virt_kybd'],
			'slug' => sanitize_title($_POST['lesson_title']),
			'vtt' => $vtt,
			'post_content' => $_POST['post_content'],

	));
	$insert_id = $wpdb->insert_id;
	$url = je_return_full_url() .'&msg=Lesson%20Added%20(ID:%20'.$insert_id.')';
	wp_redirect($url);
	exit;

} elseif ($action == 'update' ) {
	$post_id = ($_POST['post_id'] > 0) ? intval($_POST['post_id']) : 0;
	$course_id = intval($_POST['course_id']);
	
	if ($post_id == 0) {
		$post_id = wp_insert_post(
			array(
				'post_date' 		=>	date('Y-m-d H:i:s'),
				'comment_status'	=>	'closed',
				'ping_status'		=>	'closed',
				'post_author'		=>	1972,
				'post_name'			=>	sanitize_title($_POST['lesson_title']),
				'post_title'		=>	sanitize_text_field($_POST['lesson_title']),
				'post_status'		=>	'publish',
				'post_type'			=>	'lesson',
				'post_content' 		=> 	stripslashes_deep($_POST['lesson_description']),
				'meta_input' => array(
					'lesson_id' => intval($_POST['id']),
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
				'post_name'			=>	sanitize_title($_POST['lesson_title']),
				'post_title'		=>	sanitize_text_field($_POST['lesson_title']),
				'post_status'		=>	'publish',
				'post_type'			=>	'lesson',
				'post_content' 		=> 	stripslashes_deep($_POST['lesson_description']),
				'meta_input' => array(
					'lesson_id' => intval($_POST['id']),
				   )
		  );
		// Update the post into the database
		wp_update_post( $update_post_data );
	}
	
	
	// find current assets to transfer to resources
	$db_resources = unserialize($wpdb->get_var( "SELECT resources FROM academy_lessons WHERE post_id = $post_id  " ));
	foreach ($db_resources AS $k => $v) {
		if (!empty($v)) { $resources[$k] .= $v; }
	}	

	// build resources
	if (!empty($_POST['resource_url'])) {
		$resource_type = $_POST['resource_type'];
		for ($i = 1; $i <= 10; $i++) {
			if (empty($resources[$resource_type.$i])) {
				$resources[$resource_type.$i] .= substr($_POST['resource_url'],20);
				break;
			}
		}
	}

	$vtt = (substr($_POST['vtt'],0,10) == '/subtitles') ? substr($_POST['vtt'],11) : $_POST['vtt'];

	update_post_meta( $post_id, 'lesson_skill_level',sanitize_text_field($_POST['lesson_skill_level']));

	// get chapter duration
	$total_duration = 0;
	$chapters = $wpdb->get_results( "SELECT * FROM academy_chapters WHERE lesson_id = $post_id ORDER BY menu_order ASC", OBJECT );
	foreach ($chapters as $c  ) {
		$total_duration = $total_duration + $c->duration;
	}	
	$wpdb->update('academy_lessons', array(
			'post_id' => $post_id,
			'course_id' => $course_id,
			'lesson_title' => sanitize_text_field($_POST['lesson_title']),
			'post_date' => date('Y-m-d', strtotime($_POST['post_date'])),
			'vtt' => $vtt,
			'resources' => serialize($resources),
			'lesson_description' => stripslashes_deep($_POST['lesson_description']),
			'slug' => sanitize_title($_POST['lesson_title']),
			'song_lesson' => sanitize_text_field($_POST['song_lesson']),
			'success_lesson' => sanitize_text_field($_POST['success_lesson']),
			'success_style' => sanitize_text_field($_POST['success_style']),
			'sku' => sanitize_text_field($_POST['sku']),
			'duration' => $total_duration,
			'success_order' => intval($_POST['success_order']),
	),array ('ID' => $_POST['id']));
	$url = je_return_full_url() .'&msg=Lesson%20Updated';
	wp_redirect($url);
	exit;

} elseif ($action == 'delete_resource') {
	// find current assets to transfer to resources
	$lesson_id = intval($_GET['lesson_id']);
	$db_assets = unserialize($wpdb->get_var( "SELECT resources FROM academy_lessons WHERE ID = $lesson_id  " ));
	foreach ($db_assets AS $k => $v) {
		if ($k != $_GET['type']) { $resources[$k] .= $v; }
	}	
	$wpdb->update('academy_lessons', array(
			'resources' => serialize($resources),
	),array ('ID' => $lesson_id));
	$url = je_return_full_url() .'&msg=Resource%20Deleted';
	wp_redirect($url);
	exit;
		
} elseif ($action == 'jami_done') {
	$wpdb->update('academy_lessons', array(
			'jami_done' => 'y',
	),array ('post_id' => intval($_GET['id'])));
	$url = je_return_full_url() .'&msg=Jami%20Done';
	wp_redirect($url);
	exit;
} elseif ($action == 'update_chapter') {
	$chapter_id = intval($_POST['chapter_id']);
	
	// Example usage:
	$youtube_url = sanitize_url($_POST['youtube_id']);
	$youtube_id = extract_youtube_id($youtube_url);
	

	$wpdb->update('academy_chapters', array(
			'chapter_title' => sanitize_text_field($_POST['chapter_title']),
			'menu_order' => intval($_POST['menu_order']),
			'vimeo_id' => intval($_POST['vimeo_id']),
			'bunny_url' => $_POST['bunny_url'],
			'youtube_id' => $youtube_id,
			'free' => sanitize_text_field($_POST['free']),
	),array ('ID' => $chapter_id));
	$url = je_return_full_url() .'&msg=Chapter%20Updated';
	wp_redirect($url);
	exit;
} elseif ($action == 'add_chapter') {
	$highest_menu_order = $wpdb->get_var( "SELECT menu_order FROM academy_chapters WHERE lesson_id = ".intval($_POST['lesson_id'])." ORDER BY menu_order DESC" );		
	$menu_order = ($_POST['menu_order'] > 0) ? intval($_POST['menu_order']) : $highest_menu_order + 1;
	$chapter_vimeo_id = (substr($_POST['vimeo_id'],0,18) == 'https://vimeo.com/') ? substr($_POST['vimeo_id'],18) : $_POST['vimeo_id'];
	$chapter_youtube_id = (substr($_POST['youtube_id'],0,17) == 'https://youtu.be/') ? substr($_POST['youtube_id'],17) : $_POST['youtube_id'];
	$chapter_slug = sanitize_title($_POST['chapter_title']);
	$q = $wpdb->get_var( "SELECT slug FROM academy_chapters WHERE slug = '$chapter_slug' " );
	if (!empty($q)) {
		$random = rand(2,987643);
		$chapter_slug = $chapter_slug . '_' . $random;
	}
	$data = array(	
			'post_date' => date('Y-m-d'),
			'chapter_title' => sanitize_text_field($_POST['chapter_title']),
			'menu_order' => $menu_order,
			'vimeo_id' => intval($chapter_vimeo_id),
			'youtube_id' => sanitize_text_field($chapter_youtube_id),
			'lesson_id' => intval($_POST['lesson_id']),
			'slug' => $chapter_slug,
			);
	$format = array('%s','%s','%d','%d','%s','%d','%s');
	$wpdb->insert('academy_chapters',$data,$format);
	$insert_id = $wpdb->insert_id;
	$url = je_return_full_url() .'&msg=Chapter%20Added%20(ID:%20'.$insert_id.')';
	wp_redirect($url);
	exit;
} elseif ($action == 'delete_chapter') {
	$chapter_id = intval($_GET['chapter_id']);
	$wpdb->delete( 'academy_chapters', array( 'ID' => $chapter_id) );
	$url = je_return_full_url() .'&msg=Chapter%20Deleted';
	wp_redirect($url);
	exit;
} elseif ($action == 'delete_lesson') {
	$lesson_id = intval($_GET['lesson_id']);
	$wpdb->delete( 'academy_lessons', array( 'ID' => $lesson_id) );
	$url = 'lessons.php?msg=Lesson%20Deleted';
	wp_redirect($url);
	exit;
} elseif ($action == 'add_to_suggested') {
	$lesson_id = intval($_POST['lesson_id']);
	$post_id = intval($_POST['post_id']);
	$lesson_skill_level = $_POST['lesson_skill_level'];
	$found = $wpdb->get_var( "SELECT ID FROM academy_suggested_lessons WHERE lesson_id = $lesson_id " );
	switch ($lesson_skill_level) {
		case 'Beginner':
			$level = 1;
			break;
		case 'Intermediate':
			$level = 2;
			break;
		case 'Advanced':
			$level = 3;
			break;
		case 'Professional':
			$level = 4;
			break;
		case 'N/A':
			$level = 2;
			break;
	}
	if (empty($found)) {
		$data = array(	'lesson_id' => $lesson_id, 
						'post_id' => $post_id,
						'category' => $_POST['category'],
						'lesson_skill_level' => $level,
					);
				
		$format = array('%d','%d','%d','%d');
		$wpdb->insert('academy_suggested_lessons',$data,$format);
		$url = 'edit_lesson.php?id='.$lesson_id.'&msg=Lesson%20Added%20To%20Suggested';
	} else {
		$wpdb->update('academy_suggested_lessons', array(
			'lesson_id' => $lesson_id, 
			'post_id' => $post_id,
			'category' => $_POST['category'],
			'lesson_skill_level' => $level,
		),array ('lesson_id' => $lesson_id));
	$url = 'edit_lesson.php?id='.$lesson_id.'&show_chapters=y&msg=Lesson%20Updated%20To%20Suggested';
	}
	wp_redirect($url);
	exit;
} elseif ($action == 'clone') {
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
	$url = 'edit_lesson.php?id='.$insert_id.'&msg=Lesson%20Cloned%20(ID:%20'.$insert_id.')';
	wp_redirect($url);
	exit;
}
?>


<div id="content">		

<h1>Edit Lesson</h1>
<?php
//$results = $wpdb->get_results( "SELECT * FROM academy_lessons", OBJECT );
$x = 1;
$r = $wpdb->get_row( "SELECT * FROM academy_lessons WHERE ID = ".intval($_GET['id']) );
$chapters = $wpdb->get_results( "SELECT * FROM academy_chapters WHERE lesson_id = $r->ID ORDER BY menu_order ASC", OBJECT );
if ($r->jami_done === 'y') { echo '<div style="background-color: red; color:white; padding: 10px;">Jami Done</div>'; }
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
			$lesson_id = $r->ID;
			$course_id = $r->course_id;
			$post_id = $r->post_id;
			$lesson_skill_level = get_field('lesson_skill_level',$post_id);
			$lesson_duration = $r->duration;
			$suggested_lesson_category_id = $wpdb->get_var( "SELECT category FROM academy_suggested_lessons WHERE lesson_id = $lesson_id " );

			
			if ($post_id == 0) {
				$post_id = $wpdb->get_var( "SELECT post_id FROM wp_postmeta WHERE  meta_key = 'lesson_id' AND meta_value = $lesson_id " );
				$wpdb->update('academy_lessons', array( 'post_id' => $post_id ),array ('ID' => $lesson_id));
			}
			
			$lesson_teacher = get_post_meta($post_id,'lesson_teacher',TRUE);
			$lesson_style = get_post_meta($post_id,'lesson_style',TRUE);
			$lesson_pillar = get_post_meta($post_id,'lesson_pillar',TRUE);
			$lesson_element = get_post_meta($post_id,'lesson_element',TRUE);
			$lesson_key = get_post_meta($post_id,'lesson_key',TRUE);
			$lesson_tags = get_post_meta($post_id,'lesson_tags',TRUE);
			$lesson_tonality = get_post_meta($post_id,'lesson_tonality',TRUE);
			
			// $song_lesson = (in_array('Song Lesson',$lesson_tags)) ? 'y' : 'n';
			$song_lesson = $r->song_lesson;
		 ?>

<?php if (!empty($_GET['msg'])) { ?>		 
	<div style="background: yellow; padding: 20px;">
	<?php echo $_GET['msg']; ?>
	</div>
<?php } ?>


<form method="post">
	<input type="hidden" name="action" value="add_to_suggested" />
	<input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>" />
	<input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
	<input type="hidden" name="lesson_skill_level" value="<?php echo $lesson_skill_level; ?>" />
	<select name="category">
		<option value="">Choose Category...</option>
		<?php 
		$categories = $wpdb->get_results( "SELECT * FROM academy_suggested_categories ORDER BY category ASC " );
		foreach ($categories AS $category){
			$selected = ($category->ID == $suggested_lesson_category_id) ? 'selected="selected"' : '';
			echo '<option '.$selected.' value="'.$category->ID.'">'.$category->category.'</option>';
		}
		 ?>
		
	</select>
	<button type="submit" class="button small blue">Add To Suggested Lesson List</button>
</form>
		
		
		<a href="?action=jami_done&id=<?php echo $post_id; ?> "><button type="link" class="je-button-square">Mark Jami Done</button></a>
		<form action="" method="post" id="update_lesson">
			
			<p>
			Lesson ID: <?php echo $lesson_id; ?>, Post ID: <?php echo $post_id; ?> (<a href="javascript:ConfirmPrompt('Are you sure you want to delete this lesson?', '?action=delete_lesson&lesson_id=<?php echo $lesson_id; ?>');">delete lesson</a>) - <a href="?action=clone&id=<?php echo $_GET['id'];?>">clone lesson</a> | <a href="https://jazzedge.academy/wp-admin/post.php?post=<?php echo $post_id;?>&action=edit" target="_blank">edit in wordpress</a> | 
			<a href="<?php echo get_the_permalink($post_id); ?>" target="_blank">view in Academy</a>
			<br>
			Duration: <?php echo $lesson_duration; ?>
			<br>
			Is Song Lesson: 
			<select name="song_lesson">
				<option value="">..</option>
				<option value="y" <?php if ($song_lesson == 'y') { echo 'selected'; } ?>>Yes</option>
				<option value="n" <?php if ($song_lesson == 'n') { echo 'selected'; } ?>>No</option>
			</select>
			
			</p>

			<p>
				<label>Lesson Skill Level:</label><br />
			<select name="lesson_skill_level">
				<option value="">Choose Skill Level...</option>
				<option value="N/A" <?php if ($lesson_skill_level == 'N/A') { echo 'selected'; } ?>>N/A</option>
				<option value="Beginner" <?php if ($lesson_skill_level == 'Beginner') { echo 'selected'; } ?>>Beginner</option>
				<option value="Intermediate" <?php if ($lesson_skill_level == 'Intermediate') { echo 'selected'; } ?>>Intermediate</option>
				<option value="Advanced" <?php if ($lesson_skill_level == 'Advanced') { echo 'selected'; } ?>>Advanced</option>
				<option value="Professional" <?php if ($lesson_skill_level == 'Professional') { echo 'selected'; } ?>>Professional</option>
			</select>	
			</p>
			
			<p>
			<strong>SKU:</strong> (new Jazzedge with AI SKU)<br />
				<input type="text" size="100" class="text" name="sku"  value="<?php echo stripslashes(htmlspecialchars($r->sku)); ?>" />
			</p>
			
			<div style="padding: 20px; border:1px solid #ccc; width: 500px; margin: 20px 0px;">
			<p>Academy Success lessons are found in the main menu as a starting point for students.</p>
			Success Lesson? <input type="checkbox" name="success_lesson" value="y" <?php if ($r->success_lesson == 'y') { echo 'checked'; } ?> /> Yes<br />
			Style: <select name="success_style">
				<option value="">Choose Style...</option>
				<option value="" <?php if (empty($r->success_style)) { echo 'selected'; } ?>>N/A</option>
				<option value="basics" <?php if ($r->success_style == 'basics') { echo 'selected'; } ?>>Basics</option>
				<option value="rock" <?php if ($r->success_style == 'rock') { echo 'selected'; } ?>>Rock</option>
				<option value="standards" <?php if ($r->success_style == 'standards') { echo 'selected'; } ?>>Standards</option>
				<option value="improvisation" <?php if ($r->success_style == 'improvisation') { echo 'selected'; } ?>>Improvisation</option>
				<option value="blues" <?php if ($r->success_style == 'blues') { echo 'selected'; } ?>>Blues</option>
			</select>	<br />
			Order <input type="input" name="success_order" value="<?php echo $r->success_order; ?>" />
			</div>
			






			<p>
				<label>Lesson Title:</label><br />
				<input type="text" size="100" class="text" name="lesson_title"  value="<?php echo stripslashes(htmlspecialchars($r->lesson_title)); ?>" />
			</p>
			
			<p>
				<label>Lesson Description:</label><br />
				<textarea rows="7" cols="103" name="lesson_description"><?php echo $r->lesson_description; ?></textarea>
			</p>


			<p>
				<label>Release Date:</label><br />
				<input type="text" size="20" class="text" name="post_date" id="datepicker"  onclick=""  value="<?php echo $r->post_date; ?>" />
			</p>



<label>Assets:</label><br />
<?php
$assets_li = '';
$resources = unserialize($r->resources);

foreach ($resources as $asset_type => $asset_link) {
    if ($asset_link != '') {
        $encoded_asset_link = urlencode($asset_link);
        $assets_li .= "<li>
            <a href='https://s3.amazonaws.com/jazzedge-resources/$asset_link' target='_blank'>$asset_link</a> ($asset_type) 
            <a href='download_asset.php?file=$encoded_asset_link' style='margin-left: 10px;'>
                <i class='fa-solid fa-download'></i>
            </a> 
            <a href='javascript:void(0);' onclick=\"if(confirm('Are you sure you want to delete this asset?')) { window.location='?action=delete_resource&type=$asset_type&lesson_id=$_GET[id]'; }\" style='margin-left: 15px;'>
                <i class='fa-sharp fa-solid fa-trash'></i>
            </a>
        </li>";
    }
}
echo $assets_li;
?>

<h2>Add Resource:</h2>
<p>
				<label>Resource Link:</label><br />
				<input type="text" size="60" class="text" name="resource_url" value="" />
				Type:
				<select name="resource_type">
				<option value="">Choose...</option>
				<option value="pdf">PDF</option>
				<option value="ireal">iRealPro</option>
				<option value="jam">Jam Track (mp3)</option>
				</select>
			</p>
			<p>
				<label>*Resources Note* added to the lesson resources widget</label><br />
				<input type="text" size="60" class="text" name="resource_note" value="<?php echo $asset['note']; ?>" /> 
			</p>
			
			<p>
				<label>*Assets Note* added to the lesson resources widget</label><br />
				<input type="text" size="60" class="text" name="note" value="<?php echo $asset['note']; ?>" /> 
			</p>
			
			<p>
				<label>VTT File: (most lesson will not have vtt)</label><br />
				<input type="text" size="30"  onclick="select()" class="text" name="vtt" value="<?php echo $r->vtt; ?>" />
			</p>

			
			<h2>Assign To Course:</h2><br />
			<select name="course_id">
			<option value="">choose</option>
			<?php 
			$courses = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_type = 'course' AND post_status = 'publish' ORDER BY post_title ASC", OBJECT );
			foreach ($courses as $course) {
			$course_id_meta = get_post_meta( $course->ID, 'course_id', true );
			?>
			<option value='<?php echo $course_id_meta; ?>' <?php if ($course_id_meta == $course_id) { echo 'selected';} ?>><?php echo $course->post_title; ?></option>
			
			<?php			
			}
			?>
			</select>
<p></p>
	<input type="hidden" name="post_id" value="<?php echo $post_id;?>">
	<input type="hidden" name="id" value="<?php echo $r->ID;?>">
	<input type="hidden" name="action" value="update">
	<button type="submit" class="button large red">Update</button>
</form>
	<div style="border: 1px solid ccc; padding: 20px; background:#f0f1f0; margin-top: 30px; ">
			<h2>Add Chapter to Lesson:</h2>
			<div class="table">
			 <div class="tr">
				<span class="td">Order</span>
				<span class="td">Title</span>
				<span class="td">Vimeo ID</span>
				<span class="td">Youtube ID</span>
				<span class="td">Action</span>
			</div>

			<form id="add_chapter" class="tr" method="post" action="">
					<span class="td"><input type="text" name="menu_order" size="4" onClick="select();" /></span>
					<span class="td"><input type="text" name="chapter_title" size="40" /></span>
					<span class="td"><input type="text" name="vimeo_id" /></span>
					<span class="td"><input type="text" name="youtube_id" /></span>
					<span class="td">
						<input type="hidden" name="action" value="add_chapter" />
						<input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>" />
						<button type="submit" class="button small blue">Add</button>
					</span>
				</form>

			</div>
			
			
			<h2>Lesson Chapters:</h2>
			<div class="table">
			 <div class="tr">
				<span class="td">ID</span>
				<span class="td">Order</span>
				<span class="td">Title</span>
				<span class="td">Vimeo ID</span>
				<span class="td">Bunny URL</span>
				<span class="td">Download URL</span>
				<span class="td">Youtube ID</span>
				<span class="td">FREE?</span>
				<span class="td">Duration</span>
				<span class="td">Action</span>
			</div>
			<?php
if ($_GET['show_chapters'] == 'y') {
			$total_duration = 0;
			foreach ($chapters as $c  ) {
				$background = ($c->vimeo_id == 0 && $c->youtube_id == '') ? 'red' : 'white';
				$total_duration = $total_duration + $c->duration;
				$vimeo_data = vimeo_return_download_link($c->vimeo_id);
				$vimeo_download_link = $vimeo_data['url'];
				$vimeo_name = $vimeo_data['name'];
				$vimeo_api_calls_left = $vimeo_data['remaining_api_calls'];

			?>

<form id="form3" class="tr" method="post" action="">
    <span class="td"><?php echo $c->ID; ?></span>
    <span class="td"><input type="text" name="menu_order" value="<?php echo $c->menu_order; ?>" size="4" onclick="select();" /></span>
    <span class="td"><input type="text" name="chapter_title" value="<?php echo stripslashes($c->chapter_title); ?>" size="40" /></span>
    <span class="td"><input type="text" name="vimeo_id" value="<?php echo $c->vimeo_id; ?>" size="11" /> <a href="https://vimeo.com/<?php echo $c->vimeo_id; ?>" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></span>
    <span class="td"><input type="text" name="bunny_url" value="<?php echo $c->bunny_url; ?>" size="11" /></span>
    <span class="td">
        <input type="text" name="vimeo_download" value="<?php echo $vimeo_name; ?>" size="20" onclick="select()" />
        <a href="download.php?url=<?php echo urlencode($vimeo_download_link); ?>&name=<?php echo urlencode($vimeo_name); ?>">
            <i class="fa-solid fa-download"></i>
        </a>
        <a href="<?php echo $vimeo_download_link; ?>" target="_blank">
            <i class="fa-solid fa-eye"></i>
        </a>
    </span>
    <span class="td"><input type="text" name="youtube_id" value="<?php echo $c->youtube_id; ?>" size="11" /> <a href="https://youtube.com/<?php echo $c->youtube_id; ?>" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></span>
    <span class="td"><input type="text" name="free" value="<?php echo $c->free; ?>" size="2" /></span>
    <span class="td"><?php echo convertToHoursMins($c->duration); ?></span>
    <span class="td">
        <input type="hidden" name="action" value="update_chapter" />
        <input type="hidden" name="chapter_id" value="<?php echo $c->ID; ?>" />
        <button type="submit" class="button small blue">Update</button>
        <a href="javascript:ConfirmPrompt('Are you sure you want to delete this chapter?', '?action=delete_chapter&chapter_id=<?php echo $c->ID; ?>');" style="color:#fff;">
            <button type="button" class="button small red">Delete</button>
        </a>
    </span>
</form>

			<?php } ?>
			</div>
			<p>
			Total Duration: <?php echo convertToHoursMins($total_duration); ?> <br>
			Vimeo API Calls Remaining: <?php echo $vimeo_api_calls_left; ?>
			</p>
			<a href="update_vimeo_titles.php?c=3jk5lkwe6hf4FwdsHrt4&lesson_id=<?php echo $lesson_id; ?>"><button type="button" class="button large red">Update Vimeo Titles</button></a>
			<a href="update_vimeo_durations.php?c=3jk5lkwe6hf4FwdsHrt4&lesson_id=<?php echo $lesson_id; ?>"><button type="button" class="button large red">Update Vimeo Durations</button></a>
<?php } else { ?>
			<a href="?show_chapters=y&id=<?php echo $lesson_id; ?>"><button type="button" class="button large red">Show Chapters</button></a>
<?php } ?>
	</div>
</div>
<br><br>

<?php include_once('includes/scripts.php'); ?>
<?php include_once('includes/footer.php'); ?>


