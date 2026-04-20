<?php
 class_exists('m4is_r83' )||die();
 current_user_can('manage_options' )||wp_die(__('You do not have sufficient permissions to access this page.'));
 final 
class m4is_s58429 {
public static 
function m4is_z95(): self {
static $m4is_b30146;
 return $m4is_b30146 ??= new self;
 
} private 
function __construct(){
$this->m4is_i702();
 $this->m4is_s3572();
 $this->m4is_o719();
 $this->m4is_l694();
 
} private 
function m4is_i702(): void {

} private 
function m4is_s3572(): void {
if($_SERVER['REQUEST_METHOD']!== 'POST' ){
return;
 
}$m4is_w73 =get_option('memberium_wpcw', []);
 $m4is_q485 =isset($_POST['type'])? $_POST['type']: '';
 if($m4is_q485 == 'courses' ){
$m4is_w73['courses']['access_tags']=$_POST['access_tags'];
 $m4is_w73['courses']['completion_tag']=$_POST['completion_tag'];
 
}elseif($m4is_q485 == 'modules'){
$m4is_w73['modules']['completion_tag']=$_POST['completion_tag'];
 
}update_option('memberium_wpcw', $m4is_w73 );
 
}private 
function m4is_o719(): void {
$m4is_w73 =get_option('memberium_wpcw', []);
 $m4is_w8256 =$this->m4is_c71643();
 $m4is_y634 =$this->m4is_c0684();
 if(!empty($m4is_w8256 )){
echo <<<HTMLBLOCK
				<h2>WP Courseware Courses</h2>
			HTMLBLOCK;
 $this->m4is_z495($m4is_w8256, $m4is_w73 );
 if(!empty($m4is_y634 )){
echo <<<HTMLBLOCK
					<h2>WP Courseware Modules</h2>
				HTMLBLOCK;
 $this->m4is_j9726($m4is_y634, $m4is_w73 );
 
}else{
echo '<p>No modules found.</p>';
 
}
}else{
echo '<p>No courses found.</p>';
 
}
}private 
function m4is_l694(): void {
$m4is_b89 =[];
 $m4is_l9321 =m4is_k865::m4is_z2906(true );
 $m4is_l9321 =$m4is_l9321['mc'];
 $m4is_d94821 =[];
 $m4is_d94821[]=['id' =>0, 'text' =>'(None)' ];
 if(is_array($m4is_l9321 )){
foreach ((array)$m4is_l9321 as $m4is_d07693 =>$m4is_p786){
if(!in_array($m4is_d07693, $m4is_b89)){
$m4is_d94821[]=['id' =>$m4is_d07693, 'text' =>$m4is_p786 . ' (' . $m4is_d07693 . ')' ];
 
}
}
}$m4is_d94821 =json_encode($m4is_d94821);
 echo '<script>';
 echo 'var taglist = ', $m4is_d94821, ';';
 echo '</script>';
 
}private 
function m4is_c71643(): array {
global $wpdb;
 global $wpcwdb;
 $m4is_v2613 =$wpdb->prepare("SELECT `course_id`, `course_title`, `course_desc`, `course_opt_user_access` FROM %i WHERE 1 ORDER BY `course_opt_user_access` ASC, `course_id` DESC", $wpcwdb->courses );
 $m4is_w8256 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 return $m4is_w8256;
 
}private 
function m4is_c0684(): array {
global $wpdb;
 global $wpcwdb;
 $m4is_v2613 =$wpdb->prepare("SELECT * FROM %i WHERE 1 ORDER BY parent_course_id ASC, module_order ASC", $wpcwdb->modules );
 $m4is_y634 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 return $m4is_y634;
 
}private 
function m4is_z495(array $m4is_w8256, array $m4is_w73 ): void {
echo <<<HTMLBLOCK
			<form method="post" action="">
				<input type="hidden" name="type" value="courses">
				<table class="widefat" style="white-space:nowrap;">
					<tr>
						<td>Title</td>
						<td>Mode</td>
						<td>Access Tag</td>
						<td>Completion Tag</td>
					</tr>
		HTMLBLOCK;
 foreach($m4is_w8256 as $m4is_x981 ){
$m4is_d07693 =$m4is_x981['course_id'];
 $m4is_w915 =$m4is_x981['course_title'];
 $m4is_d3069 =$m4is_x981['course_desc'];
 $m4is_t5649 =$m4is_x981['course_opt_user_access']== 'default_hide';
 $m4is_o6715 =empty($m4is_w73['courses']['access_tags'][$m4is_d07693])? '' : $m4is_w73['courses']['access_tags'][$m4is_d07693];
 $m4is_e478 =empty($m4is_w73['courses']['completion_tag'][$m4is_d07693])? '' : $m4is_w73['courses']['completion_tag'][$m4is_d07693];
 $m4is_i37 =$m4is_t5649 ? '<strong style="color:red;">Protected</strong>' : '<strong style="color:green;">Public</strong>';
 if($m4is_t5649 ){
$m4is_w36167 =<<<HTMLBLOCK
					<input style="width:300px;" type="text" class="multitaglist" value="{$m4is_o6715
}" name="access_tags[{$m4is_d07693
}]" />
				HTMLBLOCK;
 
}else{
$m4is_w36167 =<<<HTMLBLOCK
					<em>Automatic</em>
					<input type="hidden" value="0" name="access_tags[{$m4is_d07693
}">
				HTMLBLOCK;
 
}echo <<<HTMLBLOCK
						<tr>
							<td>
								<strong>{$m4is_w915
}</strong><br>
								{$m4is_d3069
}
							</td>
							<td>
								{$m4is_i37
}
							</td>
							<td>
								{$m4is_w36167
}
							</td>
							<td>
								<input type="text" style="width:300px;" class="multitaglist" value="{$m4is_e478
}" name="completion_tag[{$m4is_d07693
}]">
							</td>
						</tr>
			HTMLBLOCK;
 
}echo <<<HTMLBLOCK
				</table>
				<p>
					<input type="submit" class="button-primary"value="Update" />
				</p>
			</form>
		HTMLBLOCK;
 
}private 
function m4is_j9726(array $m4is_y634, array $m4is_w73 ): void {
echo <<<HTMLBLOCK
			<form method="post" action="">
				<input type="hidden" name="type" value="modules" />
				<table class="widefat" style="white-space:nowrap;">
					<tr>
						<td>Title</td>
						<td>Completion Tag</td>
					</tr>
		HTMLBLOCK;
 foreach($m4is_y634 as $m4is_e70413 ){
$m4is_d07693 =$m4is_e70413['module_id'];
 $m4is_w915 =$m4is_e70413['module_title'];
 $m4is_d3069 =$m4is_e70413['module_desc'];
 $m4is_e478 =isset($m4is_w73['modules']['completion_tag'][$m4is_d07693])? $m4is_w73['modules']['completion_tag'][$m4is_d07693]: '';
 echo <<<HTMLBLOCK
					<tr>
						<td>
							<strong>{$m4is_w915
}</strong><br>
							{$m4is_d3069
}
						</td>
						<td>
							<input type="text" style="width:300px;" class="multitaglist" value="{$m4is_e478
}" name="completion_tag[{$m4is_d07693
}]">
						</td>
					</tr>
			HTMLBLOCK;
 
}echo <<<HTMLBLOCK
				</table>
				<p>
					<input type="submit" class="button-primary" value="Update" />
				</p>
			</form>
		HTMLBLOCK;
 
} 
}

