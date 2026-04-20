<?php

/**
 * The Template for displaying the [memb_coursegrid] shortcode grid item
 *
 * This template can be overridden by copying it to yourtheme/memberium/coursegrid_item.php
 *
 * Copyright (c) 2022 David J Bullock
 * Web Power and Light
 *
 * @param array $item_data  Current Item Course Data
 * @param array $atts       Contains shortcode attributes
 * @param array $data       Contains all shortcode and query data
 */
 defined('ABSPATH') || die();
 // Grid Item Data
 $status = $item_data['status'];
 $progress = $item_data['progress'];
 $access = $item_data['access'];
 $url = !empty($item_data['url']) ? $item_data['url'] : false;
 echo "<div class=\"memberium-course-grid-item\" data-item-id=\"{$item_id
}\" data-status=\"{$status
}\" data-progress=\"{$progress
}\" data-access=\"{$access
}\">";
 // Status Icon
 echo "<i class=\"memberium-course-grid-icon banner {$status
}\">";
 echo "<span class=\"memberium-course-grid-icon-text\">" . $item_data['status_text'] . "</span>";
 echo "</i>";
 echo ($url) ? "<a href=\"{$url
}\">" : "";
 // Course Image
 echo "<figure class=\"memberium-course-grid-figure\">";
 echo "<img src=\"{$item_data['thumbnail']['src']
}\" alt=\"\">";
 echo "</figure>";
 echo ($url) ? "</a>" : "";
//End Course Link;
 // Content
 echo "<div class=\"memberium-course-grid-content\">";
 // Title
 echo "<h3>{$item_data['title']
}</h3>";
 // Excerpt
 if( !empty($item_data['excerpt']) ){
 echo "<div class=\"memberium-course-grid-excerpt\">";
 echo do_shortcode(wpautop($item_data['excerpt']));
 echo "</div>";
 
} echo "<div class=\"memberium-course-grid-excerpt\">{$excerpt
}</div>";
 echo "</div>";
//End .memberium-course-grid-content
 echo "<div class=\"memberium-course-grid-footer\">";
 // Action Button
 if( $url ){
 echo "<a class=\"memberium-course-grid-button\" href=\"{$url
}\">";
 
} else{
 echo "<div class=\"memberium-course-grid-button\">";
 
} echo apply_filters("memberium/lms/course/item/button_text",  $item_data['button_text'],  $item_data,  $atts);
 echo ( $url ) ? "</a>" : '</div>';
 // Course Progress
 if( $course_grid_show_progress ){
 echo "<div class=\"memberium-course-grid-progress\">";
 echo "<span class=\"memberium-course-grid-progress-bar\" style=\"width:{$progress
}%\">";
 echo "<span class=\"screen-reader-text\">{$progress
}%</span>";
 echo "</span>";
 echo "</div>";
 echo "<div class=\"memberium-course-grid-progress-text\">{$item_data['progress_text']
}</div>";
 
} echo "</div>";
//End .memberium-course-grid-footer
 echo "</div>";
//End .memberium-course-grid-item

