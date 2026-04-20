<?php

/**
 * The Template for displaying the [memb_coursegrid] shortcode query loop
 *
 * This template can be overridden by copying it to yourtheme/memberium/coursegrid_container.php.
 *
 * Copyright (c) 2022 David J Bullock
 * Web Power and Light
 *
 * @param array $atts Contains shortcode attributes
 * @param array $data Contains all shortcode and query data
 *
 */
 defined('ABSPATH') || die();
 // Breakpoint Column Style CSS
 echo $data['wrapper-col-styles'];
 // Wrapper Div For Grid Column Items
 echo "<div class=\"{$data['wrapper-class']
}\" data-memb-coursegrid-num=\"{$data['grid-number']
}\">";
 if ( $data['grid-items'] ) {
 $course_grid_show_progress = ( !empty($atts['progress_bar']) && (int)$atts['progress_bar'] > 0 );
 foreach ( $data['grid-items'] as $item_data ) {
 include $data['grid-item-template'];
 
} 
} echo "</div>";
//End Wrapper Div For Grid Column Items

