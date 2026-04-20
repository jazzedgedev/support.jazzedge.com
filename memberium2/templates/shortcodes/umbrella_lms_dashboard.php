<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 */
 defined('ABSPATH') || die();
 foreach($data as $item) {
 echo "<div class='{$atts['user_class']
}' style='{$atts['user_style']
}'>";
 echo "<strong>Name:</strong>  {$item->display_name
}<br>";
 echo "<strong>Email:</strong>  <a href='mailto:{$item->user_email
}'>{$item->user_email
}</a><br>";
 echo "<strong>Enrolled Courses:</strong>  ",  count($item->courses),  "<br>";
 if (count($item->courses) ) {
 foreach($item->courses as $course) {
 echo "<div class='{$atts['course_class']
}' style='{$atts['course_style']
}'>";
 echo "<strong>Course:</strong>  {$course->course_title
}<br>";
 echo "<strong>Percent Completed:</strong>  {$course->percentage
}%<br>";
 if ($course->step_title) {
 echo "<strong>Last Completed Lesson:</strong>  {$course->step_title
}<br>";
 
} if ($course->completed_timestamp) {
 echo "<strong>Date Completed:</strong>  ",  date($atts['date_format'],  $course->completed_timestamp),  "<br>";
 
} echo '</div>';
 
} 
} else {
 echo '<p>No courses enrolled.</p>';
 
} echo '</div>';
 
}

