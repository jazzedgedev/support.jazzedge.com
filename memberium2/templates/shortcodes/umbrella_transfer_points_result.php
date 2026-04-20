<?php

/**
 * Copyright (c) 2022-2024 David J Bullock
 * Web Power and Light
 */
 defined('ABSPATH') || die();
 if ($data->error_type) {
 if ($data->error_type == 'no_points_transferred') {
 echo "<p>No points were transferred.</p>";
 
} elseif ($data->error_type == 'invalid_points') {
 echo "<p>An invalid number of points was specified.</p>";
 
} elseif ($data->error_type == 'invalid_users') {
 echo "<p>The user specified to recieve the points is not a valid user.</p>";
 
} 
} else {
 echo "<p>You successfully transferred {$data->points
} points.</p>";
 
}

