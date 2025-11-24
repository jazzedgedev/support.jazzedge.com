<?php
/*
Plugin Name:	Oxygen Functions Plugin
Plugin URI:		https://example.com
Description:	My custom functions.
Version:		1.0.0
Author:			Willie
Author URI:		https://example.com
*/

$install = 'jazzacademy';
require_once('/nas/content/live/'.$install.'/willie/ja-admin/vimeo.php-3.0.2/src/Vimeo/Vimeo.php');
require_once('/nas/content/live/'.$install.'/wp-load.php');
use Vimeo\Vimeo;

/*
function ja_enqueue_custom_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-quiz-script', get_stylesheet_directory_uri() . '/js/custom-quiz.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'ja_enqueue_custom_scripts');
*/

//function kmb_user_meets_level() { return; }
//function kmb_user_has_any_tag() { return; }
//function kmb_user_has_all_tags() { return; }



function custom_logout_redirect() {
    // Check if the user is logged in and if they are on the specific page
    $user_id = get_current_user_id();
    if (is_user_logged_in() && is_page('not-opt-in')) {
        // Log the user out and redirect to the desired page
        $logout_url = wp_logout_url(site_url('/not-opt-in'));
        wp_redirect($logout_url);
        exit();
    }
}
add_action('template_redirect', 'custom_logout_redirect');

function clear_autologin_cookies() {
    // Clear cookies by setting their values to empty and expiring them in the past
    setcookie('academy_username', '', time() - 3600, "/"); // clear cookie
    setcookie('keap_id', '', time() - 3600, "/"); // clear cookie
}

// Hook into the wp_logout action to clear the cookies on logout
add_action('wp_logout', 'clear_autologin_cookies');


function ja_get_user_settings() {
    $user_id = get_current_user_id();
    $reveal_time = get_user_meta($user_id, 'reveal_time', true) ?: 5;
    $difficulty_level = get_user_meta($user_id, 'difficulty_level', true) ?: 'easy';

    wp_send_json_success(array(
        'reveal_time' => $reveal_time,
        'difficulty_level' => $difficulty_level
    ));
}
add_action('wp_ajax_ja_get_user_settings', 'ja_get_user_settings');

function ja_save_user_settings() {
    $user_id = get_current_user_id();
    $reveal_time = isset($_POST['reveal_time']) ? intval($_POST['reveal_time']) : 0;
    $difficulty_level = isset($_POST['difficulty_level']) ? sanitize_text_field($_POST['difficulty_level']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

    if ($reveal_time && $difficulty_level && $category) {
        update_user_meta($user_id, 'reveal_time', $reveal_time);
        update_user_meta($user_id, 'difficulty_level', $difficulty_level);
        update_user_meta($user_id, 'category', $category);
        wp_send_json_success();
    } else {
        wp_send_json_error('Invalid input');
    }
}
add_action('wp_ajax_ja_save_user_settings', 'ja_save_user_settings');

// AJAX handler for marking video as watched
add_action('wp_ajax_mark_watched', 'mark_video_watched');
function mark_video_watched() {
    $user_id = intval($_POST['user_id']);
    $post_id = intval($_POST['post_id']);

    // Log the user ID and post ID for debugging
    error_log("wm-Mark Watched called for User ID: $user_id, Post ID: $post_id"); 

    // Try updating the user meta
    $updated = update_user_meta($user_id, 'has_watched_' . $post_id, true);

    // Log the result of the update
    if ($updated) {
        error_log("wm-User meta updated successfully.");
    } else {
        error_log("wm-Failed to update user meta.");
    }

    wp_die();
}

// AJAX handler for marking video as unwatched
add_action('wp_ajax_mark_unwatched', 'mark_video_unwatched');
function mark_video_unwatched() {
    $user_id = intval($_POST['user_id']);
    $post_id = intval($_POST['post_id']);

    // Log the user ID and post ID for debugging
    error_log("wm-Mark Unwatched called for User ID: $user_id, Post ID: $post_id");

    // Try deleting the user meta
    $deleted = delete_user_meta($user_id, 'has_watched_' . $post_id);

    // Log the result of the delete
    if ($deleted) {
        error_log("wm-User meta deleted successfully.");
    } else {
        error_log("wm-Failed to delete user meta.");
    }

    wp_die();
}


// Custom login logo
function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(https://jazzedge.academy/wp-content/uploads/2023/04/jazzedge-academy-logo-500px.png);
            height: 65px;
            width: 320px;
            background-size: 320px 65px;
            background-repeat: no-repeat;
            padding-bottom: 30px;
        }
    </style>
<?php }
add_action('login_enqueue_scripts', 'my_login_logo');

// Start session and set user preferences
function start_session() {
    if (!session_id()) {
        session_start();
    }
    global $wpdb;
    $user_id = get_current_user_id();
    
    // Get user preferences
    $prefs = $wpdb->get_var($wpdb->prepare("SELECT prefs FROM academy_user_prefs WHERE user_id = %d", $user_id));
    $prefs_array = unserialize($prefs);
    if (!empty($prefs_array)) {
        foreach ($prefs_array as $k => $v) {
            $_SESSION[$k] = $v;
        }
    }

    // Get user timezone
    $timezone = $wpdb->get_var($wpdb->prepare("SELECT timezone FROM academy_user_prefs WHERE user_id = %d", $user_id));
    $_SESSION['user-timezone'] = $timezone;
}
// add_action('init', 'start_session', 1);

// End session on logout and login
function end_session() {
    session_destroy();
}
add_action('wp_logout', 'end_session');
add_action('wp_login', 'end_session');
add_action('end_session_action', 'end_session');

// Retrieve a session preference
function session_pref($pref) {
    return !empty($pref) && isset($_SESSION[$pref]) ? $_SESSION[$pref] : null;
}

// Return post ID with dynamic class
function return_post_id() {
    $post_id = get_the_ID();
    return 'class-content-div postid-' . $post_id;
}

// Return featured image URL
function return_featured_image() {
    $post_id = get_the_ID();
    return get_the_post_thumbnail_url($post_id);
}


function ja_recently_viewed($type, $post_id = null, $user_id = null, $title = null, $event_id = null) {
    global $wpdb;

    // If event_id is provided, use it as post_id
    if (!is_null($event_id)) {
        $post_id = intval($event_id);
    }

    // Get post_id and user_id if not provided
    if (is_null($post_id)) {
        $post_id = get_the_ID();
    }
    if (is_null($user_id)) {
        $user_id = get_current_user_id();
    }

    // Check if post_id and user_id are valid
    if (empty($post_id) || empty($user_id) || !is_int($post_id) || !is_int($user_id) || $user_id < 2000) {
        return;
    }

    // Ensure the post_id and user_id are valid integers
    $post_id = intval($post_id);
    $user_id = intval($user_id);

    // Get the page title if not provided
    if (is_null($title)) {
        $title = get_the_title($post_id);
    }

    // Prepare data to insert
    $data = [
        'post_id' => $post_id,
        'user_id' => $user_id,
        'type' => $type,
        'title' => $title,
        'datetime' => current_time('mysql')
    ];

    // Check if the record already exists
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM academy_recently_viewed WHERE post_id = %d AND user_id = %d",
        $post_id,
        $user_id
    ));

    // If the record exists, update the datetime field
    if ($existing) {
        $wpdb->update(
            'academy_recently_viewed',
            ['datetime' => current_time('mysql')],
            ['post_id' => $post_id, 'user_id' => $user_id],
            ['%s'],
            ['%d', '%d']
        );
    } else {
        // Otherwise, insert a new record
        $wpdb->insert(
            'academy_recently_viewed',
            $data,
            ['%d', '%d', '%s', '%s', '%s']
        );
    }
}

function format_timezone($time, $user_timezone = 'America/New_York') {
    global $user_id;

    // Ensure $user_timezone is a valid string and fallback to default if invalid
    $valid_timezones = DateTimeZone::listIdentifiers();
    if (!in_array($user_timezone, $valid_timezones)) {
        $user_timezone = 'America/New_York';
    }

    // Define the original timezone
    $original_timezone = 'America/New_York';

    // Check if the input time is in the format 'm/d/Y h:i a'
    $datetime = DateTime::createFromFormat('m/d/Y h:i a', $time, new DateTimeZone($original_timezone));
    if (!$datetime) {
        // If not, assume the time is in a different format and try to convert it
        try {
            $datetime = new DateTime($time, new DateTimeZone('UTC'));
        } catch (Exception $e) {
            return "Invalid time format";
        }
    }

    // Convert to UTC
    $datetime->setTimezone(new DateTimeZone('UTC'));

    // Convert to user's timezone
    $datetime->setTimezone(new DateTimeZone($user_timezone));

    // Debugging for specific user
    if ($user_id === 2003) {
       // echo "<br />Original time: $time";
      //  echo "<br />Converted to UTC: " . $datetime->format('Y-m-d H:i:s T');
      //  echo "<br />Converted to User's Timezone ($user_timezone): " . $datetime->format('Y-m-d H:i:s T');
    }

    // Return the converted time in the desired format
    //return $datetime->format('F jS, Y \a\t h:ia');
    return $datetime->format('D., M. jS, Y \a\t g:ia');
}


function no_format_timezone($time, $user_timezone = 'America/New_York') {
    global $user_id;

    // Ensure $user_timezone is a valid string and fallback to default if invalid
    $valid_timezones = DateTimeZone::listIdentifiers();
    if (!in_array($user_timezone, $valid_timezones)) {
        $user_timezone = 'America/New_York';
    }

    // Check if the input time is a Unix timestamp
    if (is_numeric($time) && (int)$time == $time) {
        $changetime = (new DateTime())->setTimestamp((int)$time)->setTimezone(new DateTimeZone('UTC'));
    } else {
        // Define the original timezone
        $original_timezone = 'America/New_York';

        // Create DateTime object from the input time in Eastern Time
        $changetime = DateTime::createFromFormat('m/d/Y h:i a', $time, new DateTimeZone($original_timezone));

        if ($changetime === false) {
            return "Invalid time format";
        }

        // Convert to UTC
        $changetime->setTimezone(new DateTimeZone('UTC'));
    }

    // Convert to user's timezone
    $changetime->setTimezone(new DateTimeZone($user_timezone));

    // Debugging for specific user
    if ($user_id === 2003) {
        //echo "<br />Original time: $time";
        //echo "<br />Converted to UTC: " . $changetime->format('Y-m-d H:i:s T');
        //echo "<br />Converted to User's Timezone ($user_timezone): " . $changetime->format('Y-m-d H:i:s T');
    }

    // Return the converted time in the desired format
    return $changetime->format('F jS, Y \a\t h:ia');
}


function display_recently_viewed() {
    global $wpdb, $user_id;
    $user_id = get_current_user_id();

    if ($user_id < 2000) {
        return;
    }

    $recently_viewed = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM academy_recently_viewed WHERE user_id = %d  AND deleted_at IS NULL ORDER BY datetime DESC LIMIT 5",
        $user_id
    ));

    if (!empty($recently_viewed)) {
        echo '<div class="rg-container hover-black">';
        echo '<table class="rg-table zebra" summary="Recently Viewed">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="text">Date</th>';
        echo '<th class="text">Title</th>';
        echo '<th class="text" style="text-align: center;">Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($recently_viewed as $item) {
            $view_link = get_permalink($item->post_id);
            $datetime = format_timezone($item->datetime, 'America/New_York'); // Change 'America/New_York' to the desired user timezone
            $is_favorite = is_favorite($item->post_id, $user_id); // Function to check if the item is a favorite
            $favorite_class = $is_favorite ? 'fa-solid' : 'fa-regular';
            echo '<tr id="row-' . $item->ID . '">';
            echo '<td>' . $datetime . '</td>';
            echo '<td>' . get_icon_by_type($item->type) . ' ' . stripslashes($item->title) . '</td>';
            echo '<td class="text center">';
            echo '<a href="' . esc_url($view_link) . '" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i> View</a>';
            echo ' | ';
            echo '<a href="#" class="delete-recently-viewed" data-id="' . $item->ID . '"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a>';
            echo ' | ';
       //     echo '<a href="#" class="favorite-toggle" id="favorite-' . $item->post_id . '" data-id="' . $item->post_id . '"><i class="fa ' . $favorite_class . ' fa-star" aria-hidden="true"></i></a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p>No recently viewed items found.</p>';
    }
}

// Function to check if the item is a favorite
function is_favorite($post_id, $user_id) {
    global $wpdb;
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM academy_favorites WHERE post_id = %d AND user_id = %d",
        $post_id, $user_id
    ));
    return $result > 0;
}


function get_icon_by_type($type) {
    switch ($type) {
        case 'lesson':
            return '<i class="fa-sharp fa-solid fa-video" aria-hidden="true"></i>';
        case 'class':
            return '<i class="fa-sharp fa-solid fa-chalkboard-user" aria-hidden="true"></i>';
        case 'event':
            return '<i class="fa-sharp fa-solid fa-calendar" aria-hidden="true"></i>';
        case 'mini-lesson':
            return '<i class="fa-sharp fa-solid fa-book-open" aria-hidden="true"></i>';
        case 'premier-class':
            return '<i class="fa-sharp fa-solid fa-award" aria-hidden="true"></i>';
        case 'premier-course':
            return '<i class="fa-sharp fa-solid fa-graduation-cap" aria-hidden="true"></i>';
        case 'tip':
            return '<i class="fa-sharp fa-solid fa-lightbulb" aria-hidden="true"></i>';
        case 'blueprint':
            return '<i class="fa-sharp fa-solid fa-map" aria-hidden="true"></i>';
        default:
            return '<i class="fa-sharp fa-solid fa-file" aria-hidden="true"></i>';
    }
}


// Handle the AJAX request to delete a recently viewed item
function delete_recently_viewed() {
    global $wpdb;
    check_ajax_referer('recently-viewed-nonce', '_ajax_nonce');
    $user_id = get_current_user_id();
    $id = intval($_POST['id']);

    if ($user_id < 2000) {
        wp_send_json_error('Invalid user');
    }

    $result = $wpdb->delete('academy_recently_viewed', ['ID' => $id, 'user_id' => $user_id], ['%d', '%d']);

    if ($result) {
        wp_send_json_success('Deleted successfully');
    } else {
        wp_send_json_error('Delete failed');
    }
}
add_action('wp_ajax_delete_recently_viewed', 'delete_recently_viewed');

function enqueue_recently_viewed_scripts() {
    wp_enqueue_script('jquery'); // Ensure jQuery is enqueued
    wp_localize_script('jquery', 'recentlyViewedAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('recently-viewed-nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_recently_viewed_scripts');

// Add class to navigation menu links
add_filter('nav_menu_link_attributes', function($atts) {
    $atts['class'] = "academy_menu_link";
    return $atts;
}, 100, 1);

// Remove REST API user endpoints for security
add_filter('rest_endpoints', function($endpoints) {
    if (isset($endpoints['/wp/v2/users'])) {
        unset($endpoints['/wp/v2/users']);
    }
    if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});

// Handle form submission before inserting data
add_action('fluentform_before_insert_submission', function($insertData, $data, $form) {
    if ($form->id != 18) { // your form id. Change the 18 with your own form ID
        return;
    }

    $redirectUrl = home_url(); // You can change the redirect URL after successful login

    // Check if user is already logged in
    if (get_current_user_id()) {
        wp_send_json_success([
            'result' => [
                'redirectUrl' => $redirectUrl,
                'message' => 'You are already logged in. Redirecting now...'
            ]
        ]);
    }

    $email = \FluentForm\Framework\Helpers\ArrayHelper::get($data, 'email'); // your form should have email field

    if (!$email) {
        wp_send_json_error([
            'errors' => ['Please provide email']
        ], 423);
    }

    $user = get_user_by('email', $email);
    if ($user) {
        $name = $user->user_login;
        $email = $user->user_email;
        $adt_rp_key = get_password_reset_key($user);
        $user_login = $user->user_login;
        $rp_link = '<a href="' . wp_login_url() . "?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login) . '">Reset Link</a>';

        if ($name == "") $name = "There";
        $message = "Hello $name,<br>";
        $message .= "Click here to reset the password for your account: <br>";
        $message .= $rp_link . '<br>';

        $subject = __("Your account on " . get_bloginfo('name'));
        $headers = [];

        add_filter('wp_mail_content_type', function($content_type) { return 'text/html'; });
        $headers[] = 'From: Jazzedge Academy <support@jazzedge.com>' . "\r\n";
        wp_mail($email, $subject, $message, $headers);

        // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
        remove_filter('wp_mail_content_type', 'set_html_content_type');

        wp_send_json_success([
            'result' => [
                'message' => 'Your password reset link has been sent to your email.'
            ]
        ]);
    } else {
        // User not found
        wp_send_json_error([
            'errors' => ['Email not found']
        ], 423);
    }
}, 10, 3);

// Fluent Forms action hook
add_action('fluentform/submission_note_stored', function ($insertId, $added_note) {
    global $wpdb;
    $data = array(
        'data' => $insertId . '-----' . $added_note,
    );
    $format = array('%s');
    $wpdb->insert('aaa_test', $data, $format);
}, 10, 2);

// SearchWP integration with FacetWP
add_filter('searchwp\swp_query\args', function($args) {
    if (isset($args['facetwp'])) {
        $args['posts_per_page'] = -1;
    }
    return $args;
});

// Update active membership database cron job

add_action( 'academy_update_active_memb_dbase_cron', 'academy_update_active_memb_dbase' );
function academy_update_active_memb_dbase() {
//	include( '/nas/content/live/jazzacademy/willie/infusion_connect.php' );
	global $install, $app, $wpdb;
	include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');

	$studio_monthly = $app->savedSearchAllFields(1938, 1, $x);
	foreach ($studio_monthly AS $sm) {
		$studio_monthly_revenue += $sm['BillingAmt'];
		$studio_monthly_count++;
	}

	$studio_yearly = $app->savedSearchAllFields(1944, 1, $x);
	foreach ($studio_yearly AS $sy) {
		$studio_yearly_revenue += $sy['BillingAmt'];
		$studio_yearly_count++;
	}

	$premier_monthly = $app->savedSearchAllFields(1940, 1, $x);
	foreach ($premier_monthly AS $pm) {
		$premier_monthly_revenue += $pm['BillingAmt'];
		$premier_monthly_count++;
	}

	$premier_yearly = $app->savedSearchAllFields(1946, 1, $x);
	foreach ($premier_yearly AS $py) {
		$premier_yearly_revenue += $py['BillingAmt'];
		$premier_yearly_count++;
	}
	
	$report_id = 1980; // hsp_monthly
	$members = $app->savedSearchAllFields($report_id, 1, $x);
	foreach ($members AS $m) {
		$hsp_monthly_revenue += $m['BillingAmt'];
		$hsp_monthly_count++;
	}
	
	$report_id = 1984; // hsp_monthly_family
	$members = $app->savedSearchAllFields($report_id, 1, $x);
	foreach ($members AS $m) {
		$hsp_monthly_family_revenue += $m['BillingAmt'];
		$hsp_monthly_family_count++;
	}
	
	$report_id = 1982; // hsp_yearly
	$members = $app->savedSearchAllFields($report_id, 1, $x);
	foreach ($members AS $m) {
		$hsp_yearly_revenue += $m['BillingAmt'];
		$hsp_yearly_count++;
	}
	
	$report_id = 1986; // hsp_yearly_family
	$members = $app->savedSearchAllFields($report_id, 1, $x);
	foreach ($members AS $m) {
		$hsp_yearly_family_revenue += $m['BillingAmt'];
		$hsp_yearly_family_count++;
	}
	
	$report_id = 1988; // je_monthly
	$members = $app->savedSearchAllFields($report_id, 1, $x);
	foreach ($members AS $m) {
		$je_monthly_revenue += $m['BillingAmt'];
		$je_monthly_count++;
	}
	
	$report_id = 1990; // je_yearly
	$members = $app->savedSearchAllFields($report_id, 1, $x);
	foreach ($members AS $m) {
		$je_yearly_revenue += $m['BillingAmt'];
		$je_yearly_count++;
	}
	
	$report_id = 1992; // jpl_monthly
	$members = $app->savedSearchAllFields($report_id, 1, $x);
	foreach ($members AS $m) {
		$jpl_monthly_revenue += $m['BillingAmt'];
		$jpl_monthly_count++;
	}
	
	$report_id = 1994; // jpl_yearly
	$members = $app->savedSearchAllFields($report_id, 1, $x);
	foreach ($members AS $m) {
		$jpl_yearly_revenue += $m['BillingAmt'];
		$jpl_yearly_count++;
	}
	
	$report_id = 2002; // studio_monthly_dmp
	$members = $app->savedSearchAllFields($report_id, 1, $x);
	foreach ($members AS $m) {
		$studio_monthly_dmp_revenue += $m['BillingAmt'];
		$studio_monthly_dmp_count++;
	}
	
	$report_id = 2006; // premier_monthly_dmp
	$members = $app->savedSearchAllFields($report_id, 1, $x);
	foreach ($members AS $m) {
		$premier_monthly_dmp_revenue += $m['BillingAmt'];
		$premier_monthly_dmp_count++;
	}
	
	$data = array(	'date' => date('Y-m-d'),
					'studio_monthly_dmp' => $studio_monthly_dmp_count,
					'studio_monthly_dmp_revenue' => $studio_monthly_dmp_revenue,
					'studio_monthly' => $studio_monthly_count,
					'studio_yearly' => $studio_yearly_count,
					'premier_monthly' => $premier_monthly_count,
					'premier_yearly' => $premier_yearly_count,
					'studio_monthly_revenue' => $studio_monthly_revenue,
					'studio_yearly_revenue' => $studio_yearly_revenue,
					'premier_monthly_revenue' => $premier_monthly_revenue,
					'premier_yearly_revenue' => $premier_yearly_revenue,
					'hsp_monthly' => $hsp_monthly_count,
					'hsp_monthly_revenue' => $hsp_monthly_revenue,
					'hsp_monthly_family' => $hsp_monthly_family_count,
					'hsp_monthly_family_revenue' => $hsp_monthly_family_revenue,
					'hsp_yearly' => $hsp_yearly_count,
					'hsp_yearly_revenue' => $hsp_yearly_revenue,
					'hsp_yearly_family' => $hsp_yearly_family_count,
					'hsp_yearly_family_revenue' => $hsp_yearly_family_revenue,
					'je_monthly' => $je_monthly_count,				
					'je_monthly_revenue' => $je_monthly_revenue,				
					'je_yearly' => $je_yearly_count,				
					'je_yearly_revenue' => $je_yearly_revenue,				
					'jpl_monthly' => $jpl_monthly_count,				
					'jpl_monthly_revenue' => $jpl_monthly_revenue,				
					'jpl_yearly' => $jpl_yearly_count,				
					'jpl_yearly_revenue' => $jpl_yearly_revenue,	
					'premier_monthly_dmp' => $premier_monthly_dmp_count,				
					'premier_monthly_dmp_revenue' => $premier_monthly_dmp_revenue,				
				);
	$format = array('%s','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d');
	$wpdb->insert('academy_active_memb',$data,$format);
}


// Send events to Jazzedge CRM
add_action('academy_events_to_jazzedge_cron', 'academy_send_events_to_jazzedge');
function academy_send_events_to_jazzedge() {
    global $wpdb;
    $events_query = "
        SELECT p.ID, p.post_title, p.post_content, m1.meta_value event_type, m2.meta_value event_date, m3.meta_value class_type, m4.meta_value event_timestamp 
        FROM wp_posts p 
        LEFT JOIN wp_postmeta m1 ON p.ID = m1.post_id 
        LEFT JOIN wp_postmeta m2 ON p.ID = m2.post_id 
        LEFT JOIN wp_postmeta m3 ON p.ID = m3.post_id 
        LEFT JOIN wp_postmeta m4 ON p.ID = m4.post_id 
        WHERE m1.meta_key = 'event_type' AND m1.meta_value = 'class' 
        AND m2.meta_key = 'event_date' AND m2.meta_value >= CURDATE() 
        AND m3.meta_key = 'class_type' 
        AND m4.meta_key = 'event_timestamp'
        AND p.post_status = 'publish' 
        ORDER BY m4.meta_value ASC 
        LIMIT 5;
    ";

    $events = $wpdb->get_results($events_query);

    $post = [];
    foreach ($events as $event) {
        $post_id = $event->ID;
        $post[$event->event_timestamp]['post_id'] .= $post_id;
        $post[$event->event_timestamp]['event_title'] = $event->post_title;
        $post[$event->event_timestamp]['event_date'] = $event->event_date;
        $post[$event->event_timestamp]['event_type'] = $event->event_type;
        $post[$event->event_timestamp]['class_type'] = $event->class_type;
        $post[$event->event_timestamp]['permalink'] = get_permalink($post_id);
        $post[$event->event_timestamp]['event_timestamp'] = $event->event_timestamp;
    }

    // Additional event types
    $event_types = [
        'office-hours' => ['office_hours_date', 2],
        'premier-class' => ['premier_class_release_date', 5, 'teacher-checkin'],
        'webinars' => ['webinar_date', 5]
    ];

foreach ($event_types as $type => $type_values) {
    $meta_key = $type_values[0];
    $limit = $type_values[1];
    $meta_value = isset($type_values[2]) ? $type_values[2] : null;
        $args = [
            'post_type' => $type,
            'post_status' => 'publish',
            'meta_key' => $meta_key,
            'meta_value' => date('Y-m-d'),
            'meta_compare' => '>=',
            'orderby' => 'meta_value',
            'posts_per_page' => $limit,
            'order' => 'ASC'
        ];

        if ($meta_value) {
            $args['meta_query'][] = [
                'key' => $meta_key,
                'value' => $meta_value,
                'compare' => '='
            ];
        }

        $events = get_posts($args);
        foreach ($events as $event) {
            $post_id = $event->ID;
            $event_date = get_post_meta($event->ID, $meta_key, true);
            $timestamp = strtotime($event_date);
            $permalink = $type == 'premier-class' ? 'https://jazzedge.academy/premier-courses/' : get_permalink($event->ID);

            $post[$timestamp]['post_id'] .= $post_id;
            $post[$timestamp]['event_title'] = $event->post_title;
            $post[$timestamp]['event_date'] = $event_date;
            $post[$timestamp]['event_type'] = $type;
            $post[$timestamp]['class_type'] = $type;
            $post[$timestamp]['permalink'] = $permalink;
            $post[$timestamp]['event_timestamp'] = $timestamp;
        }
    }

    $url = 'https://jazzedgecrm.com/willie/fluent_api/fluent_receive_events_post.php?c=63kfDgjjkl4532d';
    $response = wp_remote_post($url, [
        'body' => $post,
    ]);
    return $response;
}

/***************** FLUENT FORMS ****************/

add_filter('fluentform/editor_shortcodes', function($smartCodes) {
    $smartCodes[0]['shortcodes']['{pc_course_teacher}'] = 'Premier Course Teacher (fname)';
    return $smartCodes;
});

add_filter('fluentform/editor_shortcode_callback_pc_course_teacher', function($value, $form) {
    $post_id = get_the_ID();
    $premier_course_id = get_field("premier_course_id", $post_id);
    $premier_course_teacher = get_field("premier_course_teacher", $premier_course_id);
    return $premier_course_teacher;
}, 10, 2);

add_filter('fluentform/rendering_field_data_select', function($data, $form) {
    if ($form->id != 16) {
        return $data;
    }
    $course_id = intval($_GET['course_id']);
    if ($course_id < 1) {
        return $data;
    }

    if (\FluentForm\Framework\Helpers\ArrayHelper::get($data, 'attributes.name') != 'song_selection_dropdown') {
        return $data;
    }

    if ($course_id > 0) {
        $videos = get_field("premier_course_songs", $course_id);
        if (!empty($videos)) {
            foreach ($videos as $video) {
                $song_title = $video['premier_course_song_title'];
                $songs[] = [
                    "label" => $song_title,
                    "value" => $song_title,
                    "calc_value" => ""
                ];
            }
        }
        $data['settings']['advanced_options'] = array_merge($data['settings']['advanced_options'], $songs);
    }
    return $data;
}, 10, 2);

add_filter('fluentform/validate_input_item_select', function($error, $field) {
    return [];
}, 10, 2);



/***************** FLUENT CRM ****************/

/**
 * Enhanced JazzEdge Support Widget for FluentSupport
 * Includes data from JazzEdge.com membership API
 */

// Configuration constants
define('JAZZEDGE_MAIN_SITE_URL', 'https://jazzedge.com');
define('JAZZEDGE_API_KEY', 'je_api_2024_K9m7nQ8vL3xR6tY2wE9rP5sA1dF4hJ7k');

add_filter('fluent_support/customer_extra_widgets', 'je_enhanced_support_widget', 40, 2);

function je_enhanced_support_widget($widgets, $customer) {
    global $wpdb;
    
    // Validate customer object
    if (!is_object($customer) || (!isset($customer->title) && !isset($customer->email))) {
        return $widgets;
    }

    $customer_email = (isset($customer->title) && strlen($customer->title) > 6) ? $customer->title : $customer->email;
    
    if (empty($customer_email)) {
        $widgets['ja_support'] = ['body_html' => '<div class="je-error">No email found</div>'];
        return $widgets;
    }

    // Handle cache clearing
    if (isset($_POST['clear_jazzedge_cache']) && $_POST['clear_jazzedge_cache'] === $customer_email) {
        $cache_key = 'jazzedge_membership_' . md5($customer_email);
        delete_transient($cache_key);
        // Add a success message that will show in the widget
        $cache_cleared_message = '<div style="background: #d4edda; color: #155724; padding: 8px; margin: 5px 0; border-radius: 3px; font-size: 11px;">✅ JazzEdge.com membership cache cleared!</div>';
    } else {
        $cache_cleared_message = '';
    }

    // Initialize data collections
    $keap_data = je_get_keap_data($customer_email);
    $academy_data = je_get_academy_data($customer_email, $keap_data['keap_id'] ?? 0);
    $jazzedge_membership = je_get_jazzedge_membership($customer_email);
    $booking_data = je_get_booking_data($customer);

    // Build the widget HTML
    $widget_html = je_build_widget_html($customer_email, $customer, $keap_data, $academy_data, $jazzedge_membership, $booking_data, $cache_cleared_message);
    
    $widgets['ja_support'] = ['body_html' => $widget_html];
    return $widgets;
}

/**
 * Get Keap/InfusionSoft data
 */
function je_get_keap_data($customer_email) {
    global $wpdb, $install, $app;
    
    $data = [
        'keap_id' => 0,
        'contact' => [],
        'tags' => [],
        'tag_ids_array' => [],
        'login_count' => 0
    ];

    // Get Keap ID from FluentCRM
    if (function_exists('FluentCrmApi')) {
        $contactApi = FluentCrmApi('contacts');
        $crm_contact = $contactApi->getContact($customer_email);
        
        if (!empty($crm_contact)) {
            $customData = $crm_contact->custom_fields();
            $data['keap_id'] = isset($customData['keap_id']) ? (int)$customData['keap_id'] : 0;
        }
    }

    // Connect to Keap if available
    include_once('/nas/content/live/' . $install . '/keap_isdk/infusion_connect.php');
    
    if ($data['keap_id'] <= 0) {
        $data['keap_id'] = keap_find_contact_id($customer_email);
    }

    if ($data['keap_id'] > 0) {
        $contact_fields = [
            'Id', 'Email', 'FirstName', 'LastName', '_AcademyLastLogin', '_AcademyExpirationDate',
            '_HSPLastLogin', '_SPJLastLogin', '_JELastLogin', '_Password4Microsites', '_JazzedgeExpirationDate',
            '_JEMembershipOfferEndDate', '_PurchasedSkus0', '_PWWDate2Cancel', '_PromoCodesUsed'
        ];
        
        $data['contact'] = $app->loadCon($data['keap_id'], $contact_fields);
        
        // Get tags
        $returnFields = ['ContactId', 'ContactGroup', 'GroupId', 'DateCreated', 'Contact.Groups'];
        $query = ['ContactId' => $data['keap_id']];
        $tags = $app->dsQuery("ContactGroupAssign", 400, 0, $query, $returnFields);
        
        $data['tags'] = $tags;
        $tag_ids = $tags[0]['Contact.Groups'] ?? '';
        $data['tag_ids_array'] = !empty($tag_ids) ? explode(',', $tag_ids) : [];

        // Get login count for this month
        $first_day = strtotime(date("Y-m-01"));
        $last_day = strtotime(date("Y-m-t"));
        
        $data['login_count'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM memberium_loginlog WHERE username LIKE %s AND logintime BETWEEN %d AND %d",
            $customer_email, $first_day, $last_day
        ));
    }

    return $data;
}

/**
 * Get Academy data
 */
function je_get_academy_data($customer_email, $keap_id) {
    global $wpdb;
    
    $data = [
        'user_login' => '',
        'user_id' => 0,
        'credits' => 0,
        'eligible_cancel_date' => ''
    ];

    if ($keap_id > 0) {
        // Get Academy user data
        $academy_response = file_get_contents('https://jazzedge.academy/willie/crm_transfer.php?code=b3pg8Cd8NEoERmYg&email=' . urlencode($customer_email));
        $ja_user_data = explode('*', $academy_response);
        $data['user_login'] = $ja_user_data[0] ?? '';
        $data['user_id'] = (int)($ja_user_data[1] ?? 0);

        // Get user credits
        if ($data['user_id'] > 0) {
            $c = $wpdb->get_results($wpdb->prepare("SELECT * FROM academy_user_credits WHERE user_id = %d", $data['user_id']));
            $data['credits'] = $c[0]->class_credits ?? 0;
        }

        // Get eligible cancel date
        $ecd = keap_get_contact_fields($keap_id, ['_AcademyEligibleCancelDate']);
        $data['eligible_cancel_date'] = convert_infusionsoft_date($ecd['_AcademyEligibleCancelDate'] ?? '');
    }

    return $data;
}

/**
 * Get JazzEdge.com membership data
 */
function je_get_jazzedge_membership($customer_email) {
    if (!function_exists('get_jazzedge_membership_data')) {
        return ['error' => 'Membership API not available'];
    }

    $membership_data = get_jazzedge_membership_data($customer_email);
    
    if (!$membership_data) {
        return ['error' => 'No membership data found'];
    }

    return $membership_data;
}

/**
 * Get booking data
 */
function je_get_booking_data($customer) {
    global $wpdb;
    
    if (!isset($customer->user_id)) {
        return null;
    }

    $query = $wpdb->prepare(
        "SELECT * FROM wp_fcal_bookings WHERE person_user_id = %d AND status = 'scheduled'", 
        $customer->user_id
    );
    
    $booking = $wpdb->get_row($query);
    
    if (!empty($booking)) {
        $date = new DateTime($booking->start_time, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone('America/New_York'));
        
        return [
            'id' => $booking->id,
            'start_time' => $date->format('m-d-Y h:ia')
        ];
    }
    
    return null;
}

/**
 * Build the complete widget HTML
 */
function je_build_widget_html($customer_email, $customer, $keap_data, $academy_data, $jazzedge_membership, $booking_data, $cache_cleared_message = '') {
    $keap_id = $keap_data['keap_id'];
    $contact = $keap_data['contact'];
    $tag_ids_array = $keap_data['tag_ids_array'];
    
    // Format dates
    $academy_exp_date = isset($contact['_AcademyExpirationDate']) ? date("m/d/Y", strtotime($contact['_AcademyExpirationDate'])) : '';
    $jazzedge_exp_date = isset($contact['_JazzedgeExpirationDate']) ? date("m/d/Y", strtotime($contact['_JazzedgeExpirationDate'])) : '';
    $password_for_microsites = $contact['_Password4Microsites'] ?? '';
    $academy_last_login = convert_infusionsoft_date($contact['_AcademyLastLogin'] ?? '');
    
    ob_start();
    ?>
    
    <div class="je-support-widget">
        <style>
        .je-support-widget {
            font-size: 12px;
            line-height: 1.4;
        }
        .je-section {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .je-section:last-child {
            border-bottom: none;
        }
        .je-section h3 {
            margin: 0 0 8px 0;
            font-size: 13px;
            color: #333;
            font-weight: bold;
        }
        .je-links {
            list-style: none;
            padding: 0;
            margin: 5px 0;
        }
        .je-links li {
            margin-bottom: 3px;
            line-height: 1.3;
        }
        .je-links a {
            color: #0073aa;
            text-decoration: none;
            font-size: 11px;
        }
        .je-links a:hover {
            text-decoration: underline;
        }
        .je-membership-item {
            background: #f9f9f9;
            padding: 5px 8px;
            margin: 3px 0;
            border-radius: 3px;
            font-size: 11px;
        }
        .je-membership-item.active {
            background: #e8f5e9;
            border-left: 3px solid #4caf50;
        }
        .je-membership-item.expired {
            background: #ffebee;
            border-left: 3px solid #f44336;
        }
        .je-error {
            color: #d63638;
            background: #fcf0f1;
            padding: 5px;
            border-radius: 3px;
            font-size: 11px;
        }
        .je-warning {
            background: #fff3cd;
            color: #856404;
            padding: 5px;
            border: 1px solid #ffeaa7;
            border-radius: 3px;
            margin: 5px 0;
            font-size: 11px;
        }
        .je-info-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 5px 10px;
            font-size: 11px;
        }
        .je-info-grid strong {
            color: #333;
        }
        .je-password-form {
            margin: 5px 0;
        }
        .je-password-form input[type="text"] {
            width: 80px;
            padding: 2px 4px;
            font-size: 11px;
        }
        .je-password-form button {
            padding: 2px 6px;
            font-size: 10px;
            background: #0073aa;
            color: white;
            border: none;
            border-radius: 2px;
            cursor: pointer;
        }
        .fs_tk_card.fs_tk_extra_card {
            max-height: 900px;
            overflow-y: auto;
        }
        </style>

        <!-- Account Info -->
        <div class="je-section">
            <h3>Account: <?php echo esc_html($customer_email); ?></h3>
            <ul class="je-links">
                <li><a href="https://ft217.infusionsoft.com/Contact/manageContact.jsp?view=edit&ID=<?php echo $keap_id; ?>&lists_sel=orders" target="_blank">Open in Keap</a> (<?php echo $keap_id; ?>)</li>
                <li><a href="https://ft217.infusionsoft.com/app/searchResults/searchResults?searchTerm=<?php echo urlencode($customer->first_name . ' ' . $customer->last_name); ?>" target="_blank">Find By Name in Keap</a></li>
                <li><a href="https://jazzedge.academy/willie/ja-admin/student.php?action=lookup_student&email=<?php echo urlencode($customer->email); ?>" target="_blank">Open in JA Admin</a></li>
            </ul>
        </div>

        <!-- Quick Actions -->
        <div class="je-section">
            <h3>Quick Actions</h3>
            <ul class="je-links">
                <li><a href="https://mypianoaccount.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=<?php echo $keap_id; ?>&Email=<?php echo urlencode($customer_email); ?>&redir=/lesson-downloads/" target="_blank">MPA Downloads</a></li>
                <li><a href="https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id=<?php echo $keap_id; ?>&Email=<?php echo urlencode($customer_email); ?>&redir=/billing/" target="_blank">Update Card</a></li>
                <li><a href="https://pianowithwillie.com/wp-admin/admin.php?page=wc-orders&s=<?php echo urlencode($customer_email); ?>" target="_blank">PWW Orders</a></li>
                <li><a href="https://jazzedge.com/wp-admin/users.php?s=<?php echo urlencode($customer_email); ?>" target="_blank">Find in Jazzedge.com</a></li>
            </ul>
        </div>

        <!-- Booking Info -->
        <?php if ($booking_data): ?>
        <div class="je-section">
            <h3>Scheduled Booking</h3>
            <a href="https://jazzedge.academy/wp-admin/admin.php?page=fluent-booking#/scheduled-events?period=upcoming&booking_id=<?php echo $booking_data['id']; ?>" target="_blank">
                OSOP: <?php echo $booking_data['start_time']; ?>
            </a>
        </div>
        <?php endif; ?>

        <!-- Account Details -->
        <div class="je-section">
            <h3>Account Details</h3>
            <div class="je-info-grid">
                <strong>Academy Exp:</strong>
                <span><?php echo $academy_exp_date; ?> 
                    <?php if ($academy_exp_date): ?>
                    <a href="https://jazzedge.academy/willie/ja-admin/actions.php?action=clear-academy-expiration&contact_id=<?php echo $keap_id; ?>" target="_blank">Clear</a>
                    <?php endif; ?>
                </span>
                
                <strong>JazzEdge Exp:</strong>
                <span><?php echo $jazzedge_exp_date; ?></span>
                
                <strong>Login Count:</strong>
                <span><strong><?php echo $keap_data['login_count']; ?></strong> (this month)</span>
            </div>

            <!-- DMP Warning -->
            <?php if (!empty($academy_data['eligible_cancel_date']) && $academy_data['eligible_cancel_date'] != '01-01-1970'): ?>
            <div class="je-warning">
                <strong>DMP Member - Eligible Cancel: <?php echo $academy_data['eligible_cancel_date']; ?></strong>
            </div>
            <?php endif; ?>

            <!-- Password Management -->
            <?php if (!empty($academy_data['user_login'])): ?>
            <form class="je-password-form" target="_blank" method="post" action="/willie/fluentsupport/fluent_support_update_academy_pass.php">
                <input type="hidden" name="action" value="update_password" />
                <input type="hidden" name="code" value="Faj6skcHeGJK2jkl452sdgvj24fDa" />
                <input type="hidden" name="user_id" value="<?php echo $academy_data['user_id']; ?>" />
                <input type="hidden" name="email" value="<?php echo $academy_data['user_login']; ?>" />
                <input type="text" name="password" placeholder="New password" />
                <button type="submit">Set Password</button>
            </form>
            <?php endif; ?>
            
            <div class="je-info-grid">
                <strong>Password:</strong>
                <span><?php echo $password_for_microsites; ?> 
                    <a href="https://jazzedge.academy/willie/crm_update_action.php?code=77b3pg8Cd8NEoERmYg&action=update_password&email=<?php echo urlencode($customer_email); ?>&keap_id=<?php echo $keap_id; ?>" target="_blank">update</a>
                </span>
            </div>
        </div>

        <!-- JazzEdge.com Membership -->
        <div class="je-section">
            <h3>JazzEdge.com Membership</h3>
            
            <!-- Cache cleared message -->
            <?php echo $cache_cleared_message; ?>
            
            <!-- Clear cache button -->
            <form method="post" style="margin-bottom: 10px;">
                <input type="hidden" name="clear_jazzedge_cache" value="<?php echo esc_attr($customer_email); ?>">
                <button type="submit" style="background: #6c757d; color: white; border: none; padding: 3px 8px; border-radius: 3px; font-size: 10px; cursor: pointer;">
                    🔄 Refresh Data
                </button>
            </form>
            
            <?php if (isset($jazzedge_membership['error'])): ?>
                <div class="je-error"><?php echo esc_html($jazzedge_membership['error']); ?></div>
            <?php else: ?>
                <?php 
                $active_memberships = array_filter($jazzedge_membership['memberships'] ?? [], function($m) {
                    return $m['status'] === 'active';
                });
                
                if (!empty($active_memberships)): ?>
                    <?php foreach ($active_memberships as $membership): ?>
                    <div class="je-membership-item active">
                        <strong><?php echo esc_html($membership['product_name']); ?></strong><br>
                        <?php if (!empty($membership['expires_at']) && $membership['expires_at'] !== '0000-00-00 00:00:00' && $membership['expires_at'] !== 'lifetime'): ?>
                            Expires: <?php echo date('m/d/Y', strtotime($membership['expires_at'])); ?>
                        <?php elseif ($membership['expires_at'] === 'lifetime'): ?>
                            <span style="color: #4caf50; font-weight: bold;">Lifetime Access</span>
                        <?php else: ?>
                            Active Subscription
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="je-info-grid">
                        <strong>Member Since:</strong>
                        <span><?php echo date('m/d/Y', strtotime($jazzedge_membership['registration_date'])); ?></span>
                        
                        <?php if (isset($jazzedge_membership['woocommerce']['total_spent'])): ?>
                        <strong>Total Spent:</strong>
                        <span>$<?php echo number_format($jazzedge_membership['woocommerce']['total_spent'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="je-error">No active JazzEdge.com memberships found</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Legacy Membership Access -->
        <div class="je-section">
            <h3>Legacy Site Access</h3>
            <ul class="je-links">
                <?php echo je_build_membership_access_links($tag_ids_array, $keap_id, $customer_email, $academy_last_login); ?>
            </ul>
        </div>

        <!-- Academy Data -->
        <?php if (!empty($academy_data['user_login'])): ?>
        <div class="je-section">
            <h3>Academy Data</h3>
            <div class="je-info-grid">
                <strong>ID:</strong> <span><?php echo $academy_data['user_id']; ?></span>
                <strong>Login:</strong> <span><?php echo $academy_data['user_login']; ?></span>
                <strong>Credits:</strong> <span><?php echo $academy_data['credits']; ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php
    return ob_get_clean();
}

/**
 * Build membership access links (legacy sites)
 */
function je_build_membership_access_links($tag_ids_array, $keap_id, $email, $academy_last_login) {
    $base_urls = [
        'academy' => "https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id=$keap_id&Email=" . urlencode($email) . "&redir=/dashboard/",
        'hsp' => "https://homeschoolpiano.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=" . urlencode($email) . "&redir=/dashboard/",
        'je' => "https://jazzedge.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=" . urlencode($email) . "&redir=/dashboard/",
        'spj' => "https://summerpianojam.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=" . urlencode($email) . "&redir=/dashboard/",
        'jcm' => "https://jazzchristmasmusic.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=" . urlencode($email) . "&redir=/dashboard/",
        'jpl' => "https://jazzpianolessons.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=" . urlencode($email) . "&redir=/dashboard/"
    ];

    $access_items = [];

    // Check for payment failure first
    if (array_intersect([7772], $tag_ids_array)) {
        $access_items[] = '<li><strong style="color:red;">PAYMENT FAILED</strong></li>';
    } else {
        // Academy memberships
        $academy_blocked = array_intersect([9717, 7772], $tag_ids_array);
        if (!$academy_blocked) {
            if (array_intersect([9950], $tag_ids_array)) {
                $access_items[] = "<li><a href='{$base_urls['academy']}' target='_blank'>ACADEMY-TRIAL</a></li>";
            }
            if (array_intersect([10136], $tag_ids_array)) {
                $access_items[] = "<li><a href='{$base_urls['academy']}' target='_blank'>ACADEMY-STUDIO-DMP</a> ($academy_last_login)</li>";
            }
            if (array_intersect([9954, 9956, 9994], $tag_ids_array)) {
                $access_items[] = "<li><a href='{$base_urls['academy']}' target='_blank'>ACADEMY-STUDIO</a> ($academy_last_login)</li>";
            }
            if (array_intersect([9821, 9813, 9659], $tag_ids_array)) {
                $access_items[] = "<li><a href='{$base_urls['academy']}' target='_blank'>ACADEMY-PREMIER</a> ($academy_last_login)</li>";
            }
            if (array_intersect([10142], $tag_ids_array)) {
                $access_items[] = "<li><a href='{$base_urls['academy']}' target='_blank'>ACADEMY-PREMIER-DMP</a> ($academy_last_login)</li>";
            }
            // Add other academy membership types...
        }

        // Other site memberships
        if (array_intersect([8859, 8861], $tag_ids_array) && !array_intersect([8701, 7772], $tag_ids_array)) {
            $access_items[] = "<li><a href='{$base_urls['je']}' target='_blank'>NBP</a></li>";
        }
        if (array_intersect([8645, 8817, 8649], $tag_ids_array) && !array_intersect([8701, 7772], $tag_ids_array)) {
            $access_items[] = "<li><a href='{$base_urls['je']}' target='_blank'>JAZZEDGE</a></li>";
        }
        if (array_intersect([7548, 7574, 7578], $tag_ids_array) && !array_intersect([7552, 7772], $tag_ids_array)) {
            $access_items[] = "<li><a href='{$base_urls['hsp']}' target='_blank'>HSP</a></li>";
        }
        if (array_intersect([9881, 9879, 9405, 9403], $tag_ids_array) && !array_intersect([9883, 7772], $tag_ids_array)) {
            $access_items[] = "<li><a href='{$base_urls['jpl']}' target='_blank'>JPL</a></li>";
        }
    }

    return implode("\n", $access_items);
}

/***************** FLUENT FUNCTIONS ****************/

// Update FluentCRM membership level on login
add_action('wp_login', 'update_fluent_crm_membership_level', 99, 2);
function update_fluent_crm_membership_level($user_login, $user) {
    $memb_data = ja_return_user_membership_data() ?? null;
    $user_membership_name = $memb_data['membership_name'] ?? null;
    $keap_id = $memb_data['keap_id'] ?? null;
    $contactApi = FluentCrmApi('contacts');
    $data = [
        'email' => $user_login,
        'custom_values' => [
            'academy_membership_level' => $user_membership_name,
            'keap_id' => $keap_id,
        ]
    ];
    $contactApi->createOrUpdate($data);
}

// Update FluentCRM custom field
function fluent_update_custom_field($custom_field, $custom_value) {
    $current_user = wp_get_current_user();
    $username = $current_user->user_login;
    $user_email = $current_user->user_email;
    $user_id = $current_user->ID;

    if ($user_id <= 0) {
        return;
    }

    $contactApi = FluentCrmApi('contacts');
    $data = [
        'email' => $username,
        'custom_values' => [
            $custom_field => $custom_value,
        ]
    ];
    $contactApi->createOrUpdate($data);
}

// Return permalink
function return_permalink($post_id = 0) {
    if ($post_id < 1) {
        $post_id = get_the_ID();
    }
    return get_the_permalink($post_id);
}


/***************** OTHER FUNCTIONS ****************/

// Custom function to check if the user is on a mobile device
function je_wp_is_mobile() {
    static $is_mobile;

    if (isset($is_mobile)) {
        return $is_mobile;
    }

    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $is_mobile = false;
    } elseif (
        strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false ||
        strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false ||
        strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false ||
        strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false ||
        strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
    ) {
        $is_mobile = true;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') == false) {
        $is_mobile = true;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false) {
        $is_mobile = false;
    } else {
        $is_mobile = false;
    }

    return $is_mobile;
}
    
/****************************************************************************************************************
//****************************************************************************************************************
// 	START USER ACCESS FUNCTIONS
//****************************************************************************************************************
// ****************************************************************************************************************/

function _____START_USER_ACCESS_FUNCTIONS_____() { return null; }

function ja_is_14_trial_member() {
    global $user_membership_level;
    return ($user_membership_level === '14daytrial') ? 'true' : 'false';
}

function ja_is_free_member() {
    global $user_membership_level, $user_membership_level_num;
    return ($user_membership_level === 'free' && $user_membership_level_num <= 0) ? 'true' : 'false';
}

function ja_return_user_membership_level_num() {
    global $user_membership_level_num;
    return $user_membership_level_num;
}

function ja_limit_player() {
    // Check for blocking tags first - if user has blocking tags, always limit
    if (je_has_blocking_tags()) {
        return 'true';
    }
    
    $post_id = get_the_ID();
    $limit = 'true';
    if (je_return_active_member() == 'true' || je_check_academy_credit_access() == 'true' || in_array($post_id, [548, 547, 587])) { 
        $limit = 'false'; 
    }
    if (ja_is_classes_only_member() === 'true') { 
        $limit = 'true'; 
    }
    return $limit;
}

function je_has_lesson_access() {
    global $wpdb, $user_id, $user_membership_level, $user_membership_level_num, $lesson_post_id;
    
    // Check for blocking tags first - if user has blocking tags, deny access
    if (je_has_blocking_tags()) {
        return 'false';
    }
    
    if (in_array($lesson_post_id, [587, 548, 547])) { 
        return 'true'; 
    }
    if (empty($user_id)) { 
        return 'false'; 
    }
    $lesson_id = get_the_ID();
    $purchased_lesson = $wpdb->get_var($wpdb->prepare("SELECT ID FROM academy_credit_purchases WHERE user_id = %d AND post_id = %d", $user_id, $lesson_id));
    
    //esentials membership
    /*
    if ($user_membership_level_num >= 1 && in_array($lesson_post_id, [263, 279, 271, 278, 282, 276, 280, 275, 281, 264, 266, 265, 307, 327, 333, 318, 328, 332, 325, 330, 324, 319, 331, 323])) { 
        return 'true'; 
    }
    */
    
    // Check Essentials library access for Studio-level lessons
	if ($user_membership_level_num == 1) {
		// Get ALM lesson_id from post
		$alm_lesson_id = null;
		if (function_exists('get_field')) {
			$alm_lesson_id = get_field('alm_lesson_id', $lesson_id);
		}
		if (empty($alm_lesson_id)) {
			$alm_lesson_id = get_post_meta($lesson_id, 'alm_lesson_id', true);
		}
		
		if ($alm_lesson_id) {
			// Get lesson membership level
			$lesson_level = $wpdb->get_var($wpdb->prepare(
				"SELECT membership_level FROM {$wpdb->prefix}alm_lessons WHERE ID = %d",
				intval($alm_lesson_id)
			));
			
			// If lesson is Studio level (2), check if in library
			if (intval($lesson_level) == 2) {
				if (class_exists('ALM_Essentials_Library')) {
					$library = new ALM_Essentials_Library();
					if ($library->has_lesson_in_library($user_id, intval($alm_lesson_id))) {
						return 'true';
					}
				}
			}
		}
	}
    //echo "**$user_membership_level";
    if (empty($purchased_lesson)) { 
        $purchased_lesson = $wpdb->get_var($wpdb->prepare("SELECT ID FROM academy_user_credit_log WHERE user_id = %d AND post_id = %d", $user_id, $lesson_id));
    }

    if (!empty($purchased_lesson)) { 
        return 'true'; 
    }

    if (in_array($user_membership_level, ['14daytrial', 'studio', 'lessons']) || $user_membership_level_num >= 2) { 
        return 'true'; 
    }

    return 'false';
}

function je_has_class_access() {
    global $wpdb, $user_id, $user_membership_level, $user_membership_level_num;
    
    // Check for blocking tags first - if user has blocking tags, deny access
    if (je_has_blocking_tags()) {
        return 'false';
    }
    
    //$class_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $class_id = get_the_ID();
    $purchased_lesson = $wpdb->get_var($wpdb->prepare("SELECT ID FROM academy_credit_purchases WHERE user_id = %d AND post_id = %d", $user_id, $class_id));

    if (empty($purchased_lesson)) { 
        $purchased_lesson = $wpdb->get_var($wpdb->prepare("SELECT ID FROM academy_user_credit_log WHERE user_id = %d AND post_id = %d", $user_id, $class_id));
    }

    if (!empty($purchased_lesson)) { 
        return 'true'; 
    }

    if (in_array($user_membership_level, ['14daytrial', 'studio', 'classes']) || $user_membership_level_num >= 3) { 
        return 'true'; 
    }

    return 'false';
}

function ja_return_user_membership_data() {
	// Check for blocking tags first - if user has blocking tags, return empty/free membership
	if (je_has_blocking_tags()) {
		$memb_data = array();
		$memb_data['membership_name'] = 'Free';
		$memb_data['membership_product'] = 'Free';
		$memb_data['membership_numeric'] = 0;
		$memb_data['membership_level'] = 'free';
		$memb_data['fname'] = do_shortcode('[memb_contact fields=FirstName]');
		$memb_data['lname'] = do_shortcode('[memb_contact fields=LastName]');
		$memb_data['email'] = do_shortcode('[memb_contact fields=Email]');
		$memb_data['tags'] = '';
		$memb_data['keap_id'] = do_shortcode('[memb_contact fields=Id]');
		return $memb_data;
	}

	$essentials_access = memb_hasAnyTags([10290,10288]);
	$premier_access = memb_hasAnyTags([9821,9813,10142]);
	$studio_access = memb_hasAnyTags([9954,10136,9807,9827,9819,9956,10136]);
	//$studio_access = do_shortcode('[memb_has_any_tag tagid=9954,10136,9807,9827,9819,9956,10136]');
	if ($essentials_access || $essentials_access === TRUE || $essentials_access ==='Yes' || $essentials_access === 'true') {
		$memb_data['membership_name'] = 'Essentials';
		$memb_data['membership_product'] = 'Essentials';
		$memb_data['membership_numeric'] = 1;
		$memb_data['membership_level'] = 'essentials';
	} 
	if ($studio_access || $studio_access === TRUE || $studio_access ==='Yes' || $studio_access === 'true') {
		$memb_data['membership_name'] = 'Studio';
		$memb_data['membership_product'] = 'Studio';
		$memb_data['membership_numeric'] = 2;
		$memb_data['membership_level'] = 'studio';
	} 
	if ($premier_access || $premier_access === TRUE || $premier_access === 'Yes' || $premier_access === 'true')  {
		$memb_data['membership_name'] = 'Premier';
		$memb_data['membership_product'] = 'Premier';
		$memb_data['membership_numeric'] = 3;
		$memb_data['membership_level'] = 'premier';
	}
	
	$memb_data['fname'] = do_shortcode('[memb_contact fields=FirstName]');
	$memb_data['lname'] = do_shortcode('[memb_contact fields=LastName]');
	$memb_data['email'] = do_shortcode('[memb_contact fields=Email]');
	$memb_data['tags'] = '';
	$memb_data['keap_id'] = do_shortcode('[memb_contact fields=Id]');
	return $memb_data;
/*
    global $user_id;
    $memb_data = [];
    $keap_data = $_SESSION['keap']['contact'] ?? null;
    if (!empty($keap_data)) {
        $memb_data['fname'] = $keap_data['firstname'] ?? '';
        $memb_data['lname'] = $keap_data['lastname'] ?? '';
        $memb_data['email'] = $keap_data['email'] ?? '';
        $memb_data['tags'] = $keap_data['groups'] ?? '';
        $memb_data['keap_id'] = $keap_data['id'] ?? 0;
    }

    $session = $_SESSION['memb_user']['membership_names'] ?? null;
    if (!empty($session)) {
        $memb_data['user_id'] = $_SESSION['memb_user']['user_id'];
        $memb_data['username'] = $_SESSION['memb_user']['LoginName'];
        $session_memberships = explode(',', $session);
    }

    $memberships = [
        'ACADEMY_FREE' => 'Free Account,0,free',
        'ACADEMY_SONG' => 'Studio,2,studio',
        'ACADEMY_ACADEMY' => 'Studio,2,studio',
        'ACADEMY_ACADEMY_NC' => 'Studio,2,studio',
        'JA_MONTHLY_LSN_CLASSES' => 'Studio,3,studio',
        'JA_MONTHLY_LSN_COACHING' => 'Studio,3,studio',
        'JA_MONTHLY_LSN_ONLY' => 'Studio,2,studio',
        'JA_MONTHLY_CLASSES_ONLY' => 'Studio,2,studio',
        'JA_MONTHLY_PREMIER' => 'Premier (All Access),4,premier',
        'JA_MONTHLY_PREMIER_DMP' => 'Premier (All Access),4,premier',
        'JA_YEAR_LSN_CLASSES' => 'Studio,3,studio',
        'JA_YEAR_LSN_COACHING' => 'Studio,3,studio',
        'JA_YEAR_LSN_ONLY' => 'Studio,2,studio',
        'JA_YEAR_CLASSES_ONLY' => 'Classes Only,2,studio',
        'JA_MONTHLY_STUDIO' => 'Studio,2,studio',
        'JA_MONTHLY_STUDIO_DMP' => 'Studio,2,studio',
        'JA_YEAR_STUDIO' => 'Studio,2,studio',
        'JA_YEAR_PREMIER' => 'Premier (All Access),4,premier',
        'ACADEMY_STUDIO' => 'Studio,2,studio',
        'ACADEMY_PREMIER' => 'Premier (All Access),4,premier',
    ];

    if (!empty($session_memberships)) {
        foreach ($memberships as $sku => $data) {
            if (in_array($sku, $session_memberships)) {
                $d = explode(',', $data);
                $memb_data['membership_name'] = $d[0];
                $memb_data['membership_product'] = $sku;
                $memb_data['membership_numeric'] = $d[1];
                $memb_data['membership_level'] = $d[2];
            }
        }
    }

    return $memb_data;
    */
}

function je_return_membership_level($return = 'nicename') {
    // Retrieve user membership data from session or Keap
    $membership_data = ja_return_user_membership_data();

    // Default to Free membership if no membership data is available
    if (empty($membership_data['membership_level'])) {
        if ($return == 'product') { return 'Free'; }
        elseif ($return == 'nicename') { return 'Free'; }
        elseif ($return == 'numeric') { return 0; }
    }

    // Determine membership level based on the retrieved data
    switch ($membership_data['membership_level']) {
        case 'premier':
            if ($return == 'product') { return 'Premier'; }
            elseif ($return == 'nicename') { return 'Premier'; }
            elseif ($return == 'numeric') { return 99; }
            break;

        case 'studio':
            if ($return == 'product') { return 'Studio'; }
            elseif ($return == 'nicename') { return 'Studio'; }
            elseif ($return == 'numeric') { return 20; }
            break;

        case 'free':
        default:
            if ($return == 'product') { return 'Free'; }
            elseif ($return == 'nicename') { return 'Free'; }
            elseif ($return == 'numeric') { return 0; }
            break;
    }

    // Fallback if no condition matched
    return $return == 'numeric' ? 0 : 'Free';
}


function je_return_membership_level_old($return = 'product') {
    $academy_level = '';
    
    if (memb_hasMembership('ACADEMY_PREMIER') == TRUE) { 
        if ($return == 'product') { return 'ACADEMY_PREMIER'; }
        elseif ($return == 'nicename') { return 'Premier'; }
        elseif ($return == 'numeric') { return 99; }		
    } elseif (memb_hasMembership('JA_MONTHLY_PREMIER') == TRUE) { 
        if ($return == 'product') { return 'JA_MONTHLY_PREMIER'; }
        elseif ($return == 'nicename') { return 'Premier'; }
        elseif ($return == 'numeric') { return 99; }		
    } elseif (memb_hasMembership('JA_MONTHLY_PREMIER_DMP') == TRUE) { 
        if ($return == 'product') { return 'JA_MONTHLY_PREMIER_DMP'; }
        elseif ($return == 'nicename') { return 'Premier (DMP)'; }
        elseif ($return == 'numeric') { return 99; }		
    } elseif (memb_hasMembership('JA_YEAR_PREMIER') == TRUE) { 
        if ($return == 'product') { return 'JA_YEAR_PREMIER'; }
        elseif ($return == 'nicename') { return 'Premier (Year)'; }
        elseif ($return == 'numeric') { return 99; }		
    } elseif (memb_hasMembership('ACADEMY_ACADEMY') == TRUE) {
        if ($return == 'product') { return 'ACADEMY_ACADEMY'; }
        elseif ($return == 'nicename') { return 'Academy'; }
        elseif ($return == 'numeric') { return 20; }	
    } elseif (memb_hasMembership('ACADEMY_ACADEMY_NC') == TRUE) {
        if ($return == 'product') { return 'ACADEMY_ACADEMY_NC'; }
        elseif ($return == 'nicename') { return 'Academy (No Coaching)'; }
        elseif ($return == 'numeric') { return 20; }	
    } elseif (memb_hasMembership('JA_MONTHLY_LSN_CLASSES') == TRUE) {
        if ($return == 'product') { return 'JA_MONTHLY_LSN_CLASSES'; }
        elseif ($return == 'nicename') { return 'Lessons & Classes'; }
        elseif ($return == 'numeric') { return 20; }	
    } elseif (memb_hasMembership('JA_MONTHLY_LSN_COACHING') == TRUE) {
        if ($return == 'product') { return 'JA_MONTHLY_LSN_COACHING'; }
        elseif ($return == 'nicename') { return 'Lessons & Coaching'; }
        elseif ($return == 'numeric') { return 20; }	
    } elseif (memb_hasMembership('JA_MONTHLY_LSN_ONLY') == TRUE) {
        if ($return == 'product') { return 'JA_MONTHLY_LSN_ONLY'; }
        elseif ($return == 'nicename') { return 'Lessons Only'; }
        elseif ($return == 'numeric') { return 20; }	
    } elseif (memb_hasMembership('JA_MONTHLY_CLASSES_ONLY') == TRUE) {
        if ($return == 'product') { return 'JA_MONTHLY_CLASSES_ONLY'; }
        elseif ($return == 'nicename') { return 'Classes Only'; }
        elseif ($return == 'numeric') { return 20; }	
    } elseif (memb_hasMembership('JA_YEAR_LSN_CLASSES') == TRUE) {
        if ($return == 'product') { return 'JA_YEAR_LSN_CLASSES'; }
        elseif ($return == 'nicename') { return 'Lessons & Classes (Year)'; }
        elseif ($return == 'numeric') { return 21; }	
    } elseif (memb_hasMembership('JA_YEAR_LSN_COACHING') == TRUE) {
        if ($return == 'product') { return 'JA_YEAR_LSN_COACHING'; }
        elseif ($return == 'nicename') { return 'Lessons & Coaching (Year)'; }
        elseif ($return == 'numeric') { return 21; }	
    } elseif (memb_hasMembership('JA_YEAR_LSN_ONLY') == TRUE) {
        if ($return == 'product') { return 'JA_YEAR_LSN_ONLY'; }
        elseif ($return == 'nicename') { return 'Lessons Only (Year)'; }
        elseif ($return == 'numeric') { return 21; }	
    } elseif (memb_hasMembership('ACADEMY_FREE') == TRUE) {
        if ($return == 'product') { return 'ACADEMY_FREE'; }
        elseif ($return == 'nicename') { return 'Free'; }
        elseif ($return == 'numeric') { return 0; }	
    } else {
        return 0; // Default to no membership or unknown
    }
}


/****************************************************************************************************************
//****************************************************************************************************************
// 	START USER ACCESS FUNCTIONS - 2
//****************************************************************************************************************
// ****************************************************************************************************************/

/**
 * Check if user has any blocking tags that should deny access
 * @return bool True if user has blocking tags, false otherwise
 */
function je_has_blocking_tags() {
    if (!function_exists('memb_hasAnyTags')) {
        return false;
    }
    
    $blocking_tags_str = get_option('alm_keap_blocking_tags', '');
    if (empty($blocking_tags_str)) {
        return false;
    }
    
    // Parse comma-separated tag IDs
    $blocking_tags = array_map('trim', explode(',', $blocking_tags_str));
    $blocking_tags = array_filter($blocking_tags, 'is_numeric');
    $blocking_tags = array_map('intval', $blocking_tags);
    
    if (empty($blocking_tags)) {
        return false;
    }
    
    // Check if user has any blocking tags
    return memb_hasAnyTags($blocking_tags) === true;
}

function je_return_active_member() {
    // Check for blocking tags first - if user has blocking tags, deny access
    if (je_has_blocking_tags()) {
        return 'false';
    }
    
    if (je_return_membership_expired() == 'true') { 
        return 'false'; 
    }

    $memberships = [
        'ACADEMY_PREMIER', 'ACADEMY_STUDIO', 'JA_MONTHLY_CLASSES_ONLY', 'JA_MONTHLY_LSN_ONLY',
        'JA_LESSONS_90DAYS', 'ACADEMY_SONG', 'ACADEMY_ACADEMY', 'ACADEMY_ACADEMY_1YR',
        'ACADEMY_ACADEMY_NC', 'JA_MONTHLY_LSN_CLASSES', 'JA_MONTHLY_LSN_COACHING', 'JA_MONTHLY_STUDIO', 'JA_YEAR_STUDIO',
        'JA_MONTHLY_PREMIER', 'JA_MONTHLY_PREMIER_DMP', 'JA_YEAR_LSN_CLASSES', 'JA_YEAR_LSN_COACHING',
        'JA_YEAR_LSN_ONLY', 'JA_YEAR_CLASSES_ONLY', 'JA_YEAR_PREMIER', 'JA_MONTHLY_STUDIO_DMP', 'JA_MONTHLY_ESSENTIALS', 'JA_YEAR_ESSENTIALS'
    ];
    
    $payment_failed = do_shortcode('[memb_has_any_tag tagid=7772]');
	if ($payment_failed === 'Yes') { return 'false'; }
    foreach ($memberships as $membership) {
        if (memb_hasMembership($membership) === true) {
            return 'true';
        }
    }
    return 'false';
}

function ja_is_premier() {
	$premier_access = do_shortcode('[memb_has_any_tag tagid=9821,9813,10142]');
	return ($premier_access === 'Yes') ? 'true' : 'false';
}

function ja_is_jazzedge_member() {
    return (memb_hasAnyTags([8817, 8649, 8645, 8859, 8861]) === true) ? 'true' : 'false';
}

function ja_is_jpl_member() {
    return (memb_hasAnyTags([9403, 9405]) === true) ? 'true' : 'false';
}

function ja_is_hsp_member() {
    return (memb_hasAnyTags([7548, 7574, 7578]) === true) ? 'true' : 'false';
}

function ja_is_classes_only_member() {
    return (memb_hasMembership('JA_YEAR_CLASSES_ONLY') === true || memb_hasMembership('JA_MONTHLY_CLASSES_ONLY') === true) ? 'true' : 'false';
}

/****************************************************************************************************************
//****************************************************************************************************************
// 	END USER ACCESS FUNCTIONS
//****************************************************************************************************************
// ****************************************************************************************************************/

/*
  Please note that caching may interfere with the NONCE,
  causing ajax requests to fail. Please DISABLE CACHING for facet pages,
  or set the cache expiration to < 12 hours!
*/

add_action('wp_footer', function() {
    ?>
    <script>
      document.addEventListener('facetwp-loaded', function() {
        if (!FWP.loaded) { // initial pageload
          FWP.hooks.addFilter('facetwp/ajax_settings', function(settings) {
            settings.headers = { 'X-WP-Nonce': FWP_JSON.nonce };
            return settings;
          });
        }
      });
    </script>
    <?php
}, 100);

add_filter('facetwp_builder_dynamic_tag_value', function($tag_value, $tag_name, $params) {
    if ($tag_name === 'has_credit_access') {
        global $wpdb;
        $user_id = get_current_user_id();
        $is_free = get_field("free_event");
        $found = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM academy_user_credit_log WHERE user_id = %d AND post_id = %d", $user_id, intval($params['post']->ID)));
        if ($found > 0 || $is_free === 'y') {
            $tag_value = '<div style="text-align: center; font-size: 12pt; color:white; width: 100%; background: green; padding: 2px; margin-bottom: 0px;">
                            <i class="fa-sharp fa-solid fa-circle-check"></i> You Can Access This Class
                          </div>';
        }
    }
    return $tag_value;
}, 10, 3);

add_filter('facetwp_builder_dynamic_tags', function($tags, $params) {
    $post_id = $params['post']->ID;
    $post_content = wp_strip_all_tags($params['post']->post_content);
    $teacher = get_field("teacher", $post_id);

    switch ($teacher) {
        case 'willie':
            $tags['coaching_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/coaching-willie.jpg';
            break;
        case 'paul':
            $tags['coaching_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/coaching-paul.jpg';
            break;
        default:
            $tags['coaching_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/event-thumbnail-1.jpg';
            break;
    }

    $class_type = get_field("class_type", $post_id);
    $class_thumbnails = [
        'music-history' => 'https://jazzedge.academy/wp-content/uploads/2023/03/jazz-history-class.jpg',
        'music-notation' => 'https://jazzedge.academy/wp-content/uploads/2023/03/sibelius-class.jpg',
        'cpt-theory' => 'https://jazzedge.academy/wp-content/uploads/2023/11/CPT-Theory.png',
        'cpt-standards' => 'https://jazzedge.academy/wp-content/uploads/2023/11/CPT-Standards.png',
        'cpt-improvisation' => 'https://jazzedge.academy/wp-content/uploads/2023/11/CPT-Improvisation.png',
        'standards-beg' => 'https://jazzedge.academy/wp-content/uploads/2023/10/willie-just-standards-new.png',
        'standards-adv' => 'https://jazzedge.academy/wp-content/uploads/2023/10/willie-just-standards-new.png',
        'improvisation' => 'https://jazzedge.academy/wp-content/uploads/2023/03/improvisation-class.jpg',
        'jazz-piano' => 'https://jazzedge.academy/wp-content/uploads/2023/03/jazz-piano-class.jpg',
        'pop-rock' => 'https://jazzedge.academy/wp-content/uploads/2023/03/pop-rock-class.jpg',
        'transcription' => 'https://jazzedge.academy/wp-content/uploads/2023/03/transcription-class.jpg',
        'rhythm-training' => 'https://jazzedge.academy/wp-content/uploads/2023/03/rhythm-class.jpg',
        'vocal-training' => 'https://jazzedge.academy/wp-content/uploads/2023/03/vocal-class.jpg',
        'music-theory' => 'https://jazzedge.academy/wp-content/uploads/2023/04/music-theory-paul-small.jpg',
        'latin-jazz' => 'https://jazzedge.academy/wp-content/uploads/2023/04/latin-jazz-nina-small.jpg',
        'willie-coaching' => 'https://jazzedge.academy/wp-content/uploads/2023/03/coaching-willie.jpg',
        'academy-coaching' => 'https://jazzedge.academy/wp-content/uploads/2023/07/paul-coaching.png',
        'premier-coaching' => 'https://jazzedge.academy/wp-content/uploads/2023/07/paul-coaching.png',
        'tci-beg' => 'https://jazzedge.academy/wp-content/uploads/2023/10/tci-all.png',
        'tci-adv' => 'https://jazzedge.academy/wp-content/uploads/2023/10/tci-all.png',
        'practical-theory' => 'https://jazzedge.academy/wp-content/uploads/2023/09/willie-practical-theory-small.png',
        'default' => 'https://jazzedge.academy/wp-content/uploads/2023/03/event-thumbnail-1.jpg',
    ];

    $tags['class_thumbnail'] = $class_thumbnails[$class_type] ?? $class_thumbnails['default'];
    $event_date = get_field("event_date", $post_id);
    $ec_event_date = get_field("_EventStartDate", $post_id);
    $tags['ec_event_date'] = date('F jS, Y',strtotime($ec_event_date));
    
    $ec_focus = esc_html( get_field('ec_focus', $post_id) );
	if ( !empty($ec_focus) ) {
    	$tags['ec_focus'] = '<div class="ec_membership_level_div">' . ucfirst(esc_html( $ec_focus )) . '</div>';
	} else {
		$tags['ec_focus'] = '<div class="ec_focus_div_empty"></div>';
	}

    
    $user_timezone = $_SESSION['user-timezone'] ?? 'America/New_York';
    $event_date_formatted = format_timezone($event_date, $user_timezone);
    $tags['event_date_formatted'] = $event_date_formatted;
    $tags['user_timezone'] = $user_timezone;
    $tags['event_id'] = $post_id;
	$tags['coaching_date'] = format_timezone(get_field("coaching_date", $post_id),$user_timezone);
	$tags['premier_class_date'] = format_timezone(get_field("class_date", $post_id),$user_timezone);
	
	
    $event_subtitle = get_field("event_subtitle");
    $event_subtitle_output = !empty($event_subtitle) ? '<u>Focus</u>: ' . $event_subtitle : '';
    $event_song = get_field("event_song");
    $event_song_output = !empty($event_song) ? '<br /><u>Song</u>: <strong>' . $event_song . '</strong>' : '';

$has_quiz = get_field("event_has_quiz", $post_id);
$event_has_quiz = (is_array($has_quiz) && isset($has_quiz[0]) && $has_quiz[0] === 'y') ? '<br /><u>Quiz</u>: <strong style="color:green;">Yes</strong>' : '';
    $prep_lesson_vimeo_id = get_field("prep_lesson_vimeo_id");
    $event_has_prep_lesson = ($prep_lesson_vimeo_id > 0) ? ' // <u>Prep Lesson</u>: <strong style="color:green;">Yes</strong>' : '';

    $truncated_excerpt = mb_strimwidth($post_content, 0, 120, "...");
    $tags['lesson_excerpt'] = '<div class="lesson-grid-description" style="font-size: 12pt; padding: 0px;">' . $truncated_excerpt;
    if (!empty($event_date) && (!empty($event_song) || !empty($event_subtitle))) {
        $tags['lesson_excerpt'] .= '<div style="margin-top: 10px; font-size: 11pt; padding: 5px; border: 1px solid #ccc;">' . $event_subtitle_output . $event_song_output . $event_has_quiz . $event_has_prep_lesson . '</div>';
    }
    $tags['lesson_excerpt'] .= '</div>';

    return $tags;
}, 10, 2);

add_filter('facetwp_use_search_relevancy', '__return_false');

add_filter("retrieve_password_message", "jazzedge_custom_password_reset", 99, 4);
function jazzedge_custom_password_reset($message, $key, $user_login, $user_data) {
    $message = "Someone has requested a password reset for the following account:
" . sprintf(__('%s'), $user_data->user_email) . "

If this was a mistake, you can just ignore this email and nothing will happen to your account.

To reset your password, visit the following address:

" . "https://jazzedge.academy/wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login) . "\r\n" . "

If you have any further issues, please either reply to this email, or email us at support@jazzedge.com

The Jazzedge Academy Team";
    return $message;
}

add_filter('retrieve_password_title', function($title) {
    $title = __('Password reset for Jazzedge Academy');
    return $title;
});

function get_facet_tag($lesson_tag) {
    global $wpdb;
    $facet_value = $wpdb->get_var($wpdb->prepare("SELECT facet_value FROM wp_facetwp_index WHERE facet_display_value = %s", $lesson_tag));
    return $facet_value;
}

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @param bool $img True to return a complete IMG tag False for just the URL
 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
 * @return string containing either just a URL or a complete image tag
 * @source https://gravatar.com/site/implement/images/php/
 */
function get_gravatar($email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = array()) {
    $url = 'https://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= "?s=$s&d=$d&r=$r";
    if ($img) {
        $url = '<img src="' . $url . '"';
        foreach ($atts as $key => $val) {
            $url .= ' ' . $key . '="' . $val . '"';
        }
        $url .= ' />';
    }
    return $url;
}

function returnTheYear() {
    return date('Y');
}

function returnCurrentDate() {
    return date('Y-m-d h:i:s');
}

function returnYMD() {
    return date('Ymd');
}

function time2str($ts) {
    if (!ctype_digit($ts)) {
        $ts = strtotime($ts);
    }

    $diff = time() - $ts;
    if ($diff == 0) {
        return 'now';
    } elseif ($diff > 0) {
        $day_diff = floor($diff / 86400);
        if ($day_diff == 0) {
            if ($diff < 60) return 'just now';
            if ($diff < 120) return '1 minute ago';
            if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
            if ($diff < 7200) return '1 hour ago';
            if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        }
        if ($day_diff == 1) return 'Yesterday';
        if ($day_diff < 7) return $day_diff . ' days ago';
        if ($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
        if ($day_diff < 60) return 'last month';
        return date('F Y', $ts);
    } else {
        $diff = abs($diff);
        $day_diff = floor($diff / 86400);
        if ($day_diff == 0) {
            if ($diff < 120) return 'in a minute';
            if ($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
            if ($diff < 7200) return 'in an hour';
            if ($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
        }
        if ($day_diff == 1) return 'Tomorrow';
        if ($day_diff < 4) return date('l', $ts);
        if ($day_diff < 7 + (7 - date('w'))) return 'next week';
        if (ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
        if (date('n', $ts) == date('n') + 1) return 'next month';
        return date('F Y', $ts);
    }
}

function ja_circle_image($image = '') {
    return '<img src="' . $image . '" style="width:100%; height:100%; border-radius: 50%;" />';
}

function ja_teacher_headshot($teacher = 'willie', $show_style = '', $class = '', $width = '100%', $height = '100%') {
    if ($show_style === 'true' || empty($show_style)) { 
        $style = "style='width:{$width}; height:{$height}'"; 
    }
    switch (strtolower($teacher)) {
        case 'willie':
            $teacher_img = '<img ' . $style . ' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-willie-smaller.png" class="' . $class . '" alt="Teacher - Willie Myette">';
            break;
        case 'paul':
            $teacher_img = '<img ' . $style . ' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-paul-smaller.png" class="' . $class . '" alt="Teacher - Paul Buono">';
            break;
        case 'nina':
            $teacher_img = '<img ' . $style . ' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-nina-smaller.png" class="' . $class . '" alt="Teacher - Nina Ott">';
            break;
        case 'anna':
            $teacher_img = '<img ' . $style . ' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-anna-smaller.png" class="' . $class . '" alt="Teacher - Anna Rizzo">';
            break;
        case 'darby':
            $teacher_img = '<img ' . $style . ' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-darby-smaller.png" class="' . $class . '" alt="Teacher - Darby Wolf">';
            break;
        case 'mike':
            $teacher_img = '<img ' . $style . ' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-mike-smaller.png" class="' . $class . '" alt="Teacher - Mike Marble">';
            break;
        case 'john':
            $teacher_img = '<img ' . $style . ' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-john-smaller.png" class="' . $class . '" alt="Teacher - John McKenna">';
            break;
        default:
            $teacher_img = '<img ' . $style . ' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-willie-smaller.png" class="' . $class . '" alt="Teacher - Willie Myette">';
            break;
    }
    return $teacher_img;
}

function date_future_or_past($date_to_check) {
    $date_to_check_timestamp = strtotime($date_to_check) + (60 * 60 * 5);
    $diff = $date_to_check_timestamp - time();
    return ($diff > 0) ? 'future' : 'past';
}

function sale_percentage_saved($retail_price = 0, $sale_price = 0, $return = 'decrease') {
    if ($retail_price == 0) {
        return 0;
    }
    $decrease = (($retail_price - $sale_price) / $retail_price) * 100;
    $increase = (($retail_price - $sale_price) / $sale_price) * 100;
    return ($return === 'decrease') ? ceil(round($decrease, 2)) : ceil(round($increase, 2));
}

function pc_return_sale_percentage_saved() {
    $post_id = get_the_ID();
    $premier_course_retail_price = get_field("premier_course_retail_price", $post_id);
    $premier_course_sale_price = get_field("premier_course_sale_price", $post_id);
    $savings = sale_percentage_saved($premier_course_retail_price, $premier_course_sale_price);
    return ($savings > 0) ? "You Save: $savings%" : '';
}

function pc_return_course_intro_video() {
    $post_id = get_the_ID();
    $premier_course_promo_video_vimeo_id = get_field("premier_course_promo_video_vimeo_id", $post_id);
    return 'https://vimeo.com/' . $premier_course_promo_video_vimeo_id;
}

function pc_return_sale_active() {
    $post_id = get_the_ID();
    $premier_course_sale_end_date = get_field("premier_course_sale_end_date", $post_id);
    $sale_active = (date_future_or_past($premier_course_sale_end_date) === 'future') ? 'active' : 'inactive';
    return $sale_active;
}

function premier_course_ready($timestamp = 0) {
    if ($timestamp < 1) {
        $post_id = get_the_ID();
        $premier_course_release_date = get_field("premier_course_release_date", $post_id);
        return (strtotime($premier_course_release_date) + (60 * 60 * 5) <= time()) ? 'true' : 'false';
    }
    return ($timestamp <= time()) ? 'true' : 'false';
}

function premier_course_ready_permalink() {
    $post_id = get_the_ID();
    if (premier_course_ready() !== 'true') {
        return '';
    }
    $permalink = get_permalink($post_id);
    return $permalink;
}

function premier_class_ready($timestamp = 0) {
    if ($timestamp < 1) {
        $post_id = get_the_ID();
        $premier_class_release_date = get_field("premier_class_release_date", $post_id);
        return (strtotime($premier_class_release_date) + (60 * 60 * 5) <= time()) ? 'true' : 'false';
    }
    return ($timestamp <= time()) ? 'true' : 'false';
}

function premier_recital_ready($timestamp = 0) {
    if ($timestamp < 1) {
        $post_id = get_the_ID();
        $premier_course_recital_date = get_field("premier_course_recital_date", $post_id);
        return (strtotime($premier_course_recital_date) + (60 * 60 * 5) <= time()) ? 'true' : 'false';
    }
    return ($timestamp <= time()) ? 'true' : 'false';
}

function tip_ready($timestamp = 0) {
    // Set the time zone to Eastern Time Zone
    $eastern_timezone = new DateTimeZone('America/New_York');

    // Get the current time in Eastern Time
    $current_time = new DateTime('now', $eastern_timezone);

    // If no timestamp is provided, get the tip release date for the current post
    if ($timestamp < 1) {
        $post_id = get_the_ID();
        $tip_release_date = get_field("tip_release_date", $post_id);

        // Create a DateTime object for the tip release date in UTC
        $tip_release_datetime_utc = new DateTime($tip_release_date, new DateTimeZone('UTC'));

        // Convert the tip release date to Eastern Time
        $tip_release_datetime_utc->setTimezone($eastern_timezone);

        // Add 4 hours to the release time to match your requirement
        $tip_release_datetime_utc->modify('+4 hours');

        // Compare the release date and time to the current time
        return ($tip_release_datetime_utc <= $current_time) ? 'true' : 'false';
    }

    // If a timestamp is provided, compare it directly to the current time
    $timestamp_datetime = (new DateTime('@' . $timestamp))->setTimezone($eastern_timezone);

    return ($timestamp_datetime <= $current_time) ? 'true' : 'false';
}

function tip_readyold($timestamp = 0) {
    if ($timestamp < 1) {
        $post_id = get_the_ID();
        $tip_release_date = get_field("tip_release_date", $post_id);
        return (strtotime($tip_release_date) + (60 * 60 * 4) <= time()) ? 'true' : 'false';
    }
    return ($timestamp <= time()) ? 'true' : 'false';
}
    //wm up to here done

// chatgpt edits:
function isDateInPastOrFuture($dateString): string {
    try {
        $date = new DateTime($dateString);
        $now = new DateTime();
        
        // Normalize time to avoid time comparison if only date comparison is intended
        $date->setTime(0, 0, 0);
        $now->setTime(0, 0, 0);

        if ($date < $now) {
            return 'past';
        } elseif ($date > $now) {
            return 'future';
        } else {
            return 'present';
        }
    } catch (Exception $e) {
        // Handle exception for invalid date formats
        return 'invalid date format';
    }
}


function premier_class_ready_permalink(){
	$post_id = get_the_ID();
	if (premier_class_ready() != 'true') { return; }
	$permalink = get_permalink($post_id);
	return $permalink;
}


function wpdb_print_error(){
    global $wpdb;
    if($wpdb->last_error !== '') {
        $str   = htmlspecialchars( $wpdb->last_result, ENT_QUOTES );
        $query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );
        print "<div id='error'>
        <p class='wpdberror'><strong>WordPress database error:</strong> [$str]<br />
        <code>$query</code></p>
        </div>";
   }
}

function ja_goal_keywords($string, $min_word_length = 3, $min_word_occurrence = 2, $as_array = false, $max_words = 8, $restrict = false) {
   function keyword_count_sort($first, $sec) {
     return $sec[1] - $first[1];
   }

   $string = preg_replace('/[^\p{L}0-9 ]/', ' ', $string);
   $string = trim(preg_replace('/\s+/', ' ', $string));
   $words = explode(' ', $string);

   if ($restrict === false) {
      $commonWords = array('piano','like','able','learn','want','play','more','with','proper','jazz','over');
      $words = array_udiff($words, $commonWords,'strcasecmp');
   }

   if ($restrict !== false) {
      $allowedWords = array(/* Array of allowed words */);
      $words = array_uintersect($words, $allowedWords,'strcasecmp');
   }

   $keywords = array();

   while(($c_word = array_shift($words)) !== null) {
     if (strlen($c_word) < $min_word_length) continue;
     $c_word = strtolower($c_word);

     if (array_key_exists($c_word, $keywords)) $keywords[$c_word][1]++;
     else $keywords[$c_word] = array($c_word, 1);
   }

   usort($keywords, 'keyword_count_sort');
   $final_keywords = array();

   foreach ($keywords as $keyword_det) {
     if ($keyword_det[1] < $min_word_occurrence) break;
     array_push($final_keywords, $keyword_det[0]);
   }

  $final_keywords = array_slice($final_keywords, 0, $max_words);

  return $as_array ? $final_keywords : implode('|', $final_keywords);
}

function return_timestamp( ) {
	return time();
}

function dateThreeMonthsAgo() {
    return date('Y-m-d', strtotime('-3 months'));
}


function is_event_free(){
	$event_id = intval($_GET['id']);
	$value = get_field( "free_event", $event_id );
	return $value;
}

// Function to change default wordpress@domain.com email address
function wp_sender_email( $original_email_address ) {
return 'support@jazzedge.com';
}

// Function to change sender name
function wp_sender_name( $original_email_from ) {
return 'Jazzedge Academy';
}

// Add our functions to WordPress filters 
add_filter( 'wp_mail_from', 'wp_sender_email' );
add_filter( 'wp_mail_from_name', 'wp_sender_name' );

function extract_youtube_id($url){
    $n = preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user|shorts)\/))([^\?&\"'>]+)/", $url, $matches);
    if ($n) {
        return $matches[1];
    }
    return false;
}


function is_youtube_url_valid($url) {

    // Let's check the host first
    $parse = parse_url($url);
    $host = $parse['host'];
    if (!in_array($host, array('youtube.com', 'www.youtube.com', 'youtu.be'))) {
        return false;
    }

    $ch = curl_init();
    $oembedURL = 'www.youtube.com/oembed?url=' . urlencode($url).'&format=json';
    curl_setopt($ch, CURLOPT_URL, $oembedURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Silent CURL execution
    $output = curl_exec($ch);
    unset($output);

    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($info['http_code'] !== 404)
        return true;
    else 
        return false;
}

function is_youtube_url_private( $video_id ){
	//$video_id = 'HvT1NxZmOLc'; // pub
	//$video_id = 's9szZJvZa2M'; // priv
	//$video_id = 'lTTIpx6FrVA'; // unlisted

    $url = "https://www.googleapis.com/youtube/v3/videos?part=status&id=$video_id&key=AIzaSyDpFSwqke8Ytyq9gQThaQh1JCbm86BXR8w";
    $results = json_decode(file_get_contents($url));

    if ($results->items[0]->status->privacyStatus == 'public' || $results->items[0]->status->privacyStatus == 'unlisted') { 
    	return FALSE;
    } else {
    	return TRUE;
    }
}

function format_money($num) {
	return '$'.number_format($num,2);
}

function keap_add_new_user($email,$fname,$lname) {
    global $install;
    //include('/nas/content/live/'.$install.'/willie/infusion_connect.php');
    if (empty($email)) { return; }
    $contact_id = keap_find_contact_id($email);
    if ($contact_id > 0) { return $contact_id; }
   	$contact_data = array('FirstName' => sanitize_text_field($fname),
                'LastName'  => sanitize_text_field($lname),
                'Email'     => sanitize_email($email)
                );
	$contact_id = $app->addCon($contact_data);
	return $contact_id;
}

function keap_opt_status($email = '') {
    if (empty($email)) { return ; }
	global $app;
//	include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
 	$opt_status = $app->optStatus($email);
	return $opt_status;
}

function keap_has_card() {
    $keap_id = memb_getContactId();
    if ($keap_id < 1) { return ; }
	global $app;
    if (!$app) {
    	global $install, $app;
		include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
    }
	$returnFields = array('Id','last4','CardType','ExpirationMonth','NameOnCard');
 	$query = array('ContactId' => $keap_id);
 	$card = $app->dsQuery("CreditCard", 1, 0, $query, $returnFields);
	$has_card = (!empty($card[0]['last4'])) ? 'true' : 'false';
	return $has_card;
}


function keap_tag_contact($contact_id,$tag_id) {
	if ($contact_id < 1) { return ; }
    global $app;
    if (!$app) {
    	global $install, $app;
		include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
    }
	$tagged = $app->grpAssign($contact_id,$tag_id);
	return $tagged;
}

function keap_check_contact_tag($contact_id, $tag_id) {
    if ($contact_id < 1) { 
        return false; 
    }
    
    global $app;
    if (!$app) {
        global $install, $app;
        include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
    }
    
    // Define the return fields and query for the contact tags
    $returnFields = array('ContactId', 'ContactGroup', 'GroupId', 'DateCreated', 'Contact.Groups', 'Contact.ContactNotes');
    $query = array('ContactId' => $contact_id);
    
    // Fetch the contact's tags using dsQuery
    $tags = $app->dsQuery("ContactGroupAssign", 400, 0, $query, $returnFields);
    
    if (!empty($tags)) {
        // Extract tag ids from the result
        foreach ($tags as $tag) {
            if (isset($tag['GroupId']) && $tag['GroupId'] == $tag_id) {
                return true;  // The specific tag exists
            }
        }
    }
    
    return false;  // The tag ID does not exist
}


function keap_removetag_contact($contact_id,$tag_id) {
	error_log('TGA REMOVED for free trial user - oxygen functions.');
	if ($contact_id < 1) { return ; }
    global $app;
    if (!$app) {
    	global $install, $app;
		include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
    }
	$tagged = $app->grpRemove($contact_id,$tag_id);
	return $tagged;
}


function keap_goal_contact($contact_id,$keap_api_goal) {
	if ($contact_id < 1) { return ; }
    global $app;
    if (!$app) {
    	global $install, $app;
		include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
    }
	$goal = $app->achieveGoal('ft217', $keap_api_goal, $contact_id); 
	return $goal;
}

function keap_find_contact_id($email_address) {
	if (empty($email_address)) { return ; }
	$contact_id = null;
    global $app;
    if (!$app) {
    	global $install, $app;
		include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
    }
	$contact =  $app->findByEmail($email_address,array('Id'));
	$contact_id = $contact[0]['Id'];
	return $contact_id;
}

function keap_update_contact($contact_id,$update_array) {
	if ($contact_id < 1) { return ; }
 	global $app;
    if (!$app) {
    	global $install, $app;
		include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
    }
	$u = $app->updateCon($contact_id,$update_array);
	return $u;
}

function keap_get_contact_fields($contact_id,$field_array) {
	if ($contact_id < 1) { return ; }
    global $app;
    if (!$app) {
    	global $install, $app;
		include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
    }
	$u = $app->loadCon($contact_id,$field_array);
	return $u;
}

function keap_has_lesson_suggestions($data = '') {
	$keap_id = memb_getContactId();
	if ($keap_id < 1) { return ; }
    global $app;
    if (!$app) {
    	global $install, $app;
		include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
    }
    $field_array = array('_MostInterestedInLearning','_SkillLevel');
	$u = $app->loadCon($keap_id,$field_array);
	if ($data === 'aboutme') {
		return $u;
	} else {
		return (empty($u)) ? 'false' : 'true';
	}
}



function generateRandomString($length = 10) {
    $characters = '123456789abcdefghjkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function can_access_jpc(){
	// no  longer needed since everyone can view JPC. Keep this code.
	return 'true'; // wmyette
	$membership_level = je_return_membership_level();
	if ($membership_level == 'ACADEMY_PREMIER' OR  $membership_level == 'ACADEMY_ACADEMY') {
		return 'true';
	} else {
		return 'false';
	}
}

function convert_infusionsoft_date($date){
	return date('m-d-Y', strtotime($date));
}

function convert_to_easy_date($date){
	return date('M. jS, Y', strtotime($date));
}


function convertToHoursMins($time, $format = '%02d:%02d') {
    if ($time < 1) {
        return;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
    // echo convertToHoursMins(250, '%02d hours %02d minutes'); // should output 4 hours 17 minutes
}

function convertToDaysYears($sum, $return = 'dm') {
    $years = floor($sum / 365);
    $months = floor(($sum - ($years * 365))/30.5);
    $days = round(($sum - ($years * 365) - ($months * 30.5)));
    if ($return == 'dm') {
    	return $months . ' months, ' . $days . ' days';
    } else {
    	return $years . ' years, ' . $months . ' months, ' . $days . ' days';
    }
}

function calc_date_diff($date1,$date2,$return = 'full') {
	
	// Declare and define two dates
	$date1 = strtotime($date1);
	$date2 = strtotime($date2);

	// Formulate the Difference between two dates
	$diff = abs($date2 - $date1);

	// To get the year divide the resultant date into
	// total seconds in a year (365*60*60*24)
	$years = floor($diff / (365*60*60*24));

	// To get the month, subtract it with years and
	// divide the resultant date into
	// total seconds in a month (30*60*60*24)
	$months = floor(($diff - $years * 365*60*60*24)
								 / (30*60*60*24));

	// To get the day, subtract it with years and
	// months and divide the resultant date into
	// total seconds in a days (60*60*24)
	$days = floor(($diff - $years * 365*60*60*24 -
			   $months*30*60*60*24)/ (60*60*24));

	// To get the hour, subtract it with years,
	// months & seconds and divide the resultant
	// date into total seconds in a hours (60*60)
	$hours = floor(($diff - $years * 365*60*60*24
		 - $months*30*60*60*24 - $days*60*60*24)
									 / (60*60));

	// To get the minutes, subtract it with years,
	// months, seconds and hours and divide the
	// resultant date into total seconds i.e. 60
	$minutes = floor(($diff - $years * 365*60*60*24
		   - $months*30*60*60*24 - $days*60*60*24
							- $hours*60*60)/ 60);

	// To get the minutes, subtract it with years,
	// months, seconds, hours and minutes
	$seconds = floor(($diff - $years * 365*60*60*24
		   - $months*30*60*60*24 - $days*60*60*24
				  - $hours*60*60 - $minutes*60));

	// Print the result
	
	if ($return == 'y') {
		return $years;
	} elseif ($return == 'mon') {
		return $months;
	} elseif ($return == 'd') {
		return $days;
	} elseif ($return == 'h') {
		return $hours;
	} elseif ($return == 'm') {
		return $minutes;
	} elseif ($return == 's') {
		return $seconds;
	} else {
		return printf("%d years, %d months, %d days, %d hours, "
	   . "%d minutes, %d seconds", $years, $months,
			   $days, $hours, $minutes, $seconds);
	}
}

function ft_evergreen_date($days_to_add = 7) {
	global $wpdb, $user_id;
	if ($user_id > 0) {
		$signup_date = $wpdb->get_var( "SELECT signup_date FROM academy_free_trial_signups WHERE user_id = $user_id " );
	} else { $signup_date = date('m-d-Y'); }
	$evergreen_date = date('Y-m-d h:i:s', strtotime($signup_date . "+$days_to_add DAYS"));
	// for testing...
	//$evergreen_date = date('Y-m-d h:i:s', strtotime("+5 SECONDS") - (3600 * 4));
	return $evergreen_date;
}

function delete_all_user_jpc_progress(){
	global $wpdb, $user_id;
	$wpdb->delete( 'je_practice_curriculum_assignments', array( 'user_id' => $user_id ) );
	$wpdb->delete( 'je_practice_curriculum_progress', array( 'user_id' => $user_id ) );
	$wpdb->delete( 'je_practice_milestone_submissions', array( 'user_id' => $user_id ) );
	$wpdb->delete( 'je_practice_goals', array( 'user_id' => $user_id ) );
	$wpdb->delete( 'je_practice_curriculum_fundational', array( 'user_id' => $user_id ) );
}

function delete_all_user_repertoire(){
	global $wpdb, $user_id;
	$wpdb->delete( 'academy_user_repertoire', array( 'user_id' => $user_id ) );
}


function infusionsoft_redirect(){
	$infusion_id = intval($_GET['id']);
	$email = sanitize_email($_GET['email']);
	$dir = sanitize_text_field($_GET['dir']);
	if (!empty($infusion_id) && !empty($email)) {	
		return "https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id=$infusion_id&Email=$email&redir=/$dir";
	} else {
		return "https://jazzedge.academy/";
	}
}

function blueprint_user_has_access() {
	global $wpdb, $user_id;
	$is_active_member = je_return_active_member();
	if ($is_active_member === 'true') { return 'true'; }
	$blueprint_id = get_the_ID();
	$blueprint_free_user = $wpdb->get_var( "SELECT ID FROM academy_blueprint_freeuser WHERE user_id = $user_id AND blueprint_id = $blueprint_id" );
	if (!empty($blueprint_free_user) && $blueprint_free_user > 0) { return 'true'; }
	return 'false';
}

function blueprint_has_resources() {
	global $wpdb;
	$blueprint_step_id = get_the_ID();
	$blueprint_backing_track = get_post_meta( $blueprint_step_id, 'blueprint_backing_track', true );
	$blueprint_sheet_music = get_post_meta( $blueprint_step_id, 'blueprint_sheet_music', true );
	return (!empty($blueprint_sheet_music) || !empty($blueprint_backing_track)) ? 'true' : 'false';
}

function blueprint_lesson_complete() {
	global $wpdb, $user_id;
	$blueprint_step_id = get_the_ID();
	$blueprint_lesson_complete = ($wpdb->get_var( "SELECT blueprint_step_id FROM academy_completed_blueprint_lessons WHERE user_id = $user_id AND blueprint_step_id = $blueprint_step_id " ) > 1) ? 'true' : 'false';
	return $blueprint_lesson_complete;
}

function blueprint_progress($blueprint_id = '') { 
	global $wpdb;
	$blueprint_id = (empty($blueprint_id)) ? get_the_ID() : $blueprint_id;
	$args = array(
		'posts_per_page'   => -1,
		'post_type'        => 'blueprint-step',
		'post_status'      => 'publish',
		'meta_query' => array(
			array(
				'key'     => 'blueprint_id',
				'value'   => $blueprint_id,
				'compare' => 'LIKE',
			),
		),
	);
	$blueprint_steps = count(get_posts( $args ));
	$user_id = get_current_user_id();
	$completed_blueprint_steps = $wpdb->get_var( "SELECT COUNT(*) FROM academy_completed_blueprint_lessons WHERE user_id = $user_id AND blueprint_id = '$blueprint_id'" );

	return percent_complete($completed_blueprint_steps,$blueprint_steps);
}	

function je_return_user_id(){
	$user_id = get_current_user_id();
	return $user_id;
}

function je_return_user_email( $atts , $content = NULL ) { 
		$atts = shortcode_atts(
			array(
			), $atts, 'je_return_user_email'
		);
		$current_user = wp_get_current_user();
		$username = $current_user->user_login;
		$user_email = $current_user->user_email;
		return $username;
	}

function je_return_opt_status() {
	$opt = do_shortcode('[memb_optin_status]');
	if ($opt == 'Double Opted In') { return TRUE; } else { return FALSE; }
}

function return_user_ip() {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function return_dashboard_tutorial_video() {
	$tutorial = intval($_GET['tutorial']);
	$membership_level = je_return_membership_level();
	switch ($membership_level) {
		case 'ACADEMY_PREMIER':
			$vimeo_id = 751456686;
			break;
		case 'ACADEMY_ACADEMY':
			$vimeo_id = 751456711;
			break;
		case 'ACADEMY_ACADEMY_NC':
			$vimeo_id = 751456711;
			break;
		case 'ACADEMY_SONG':
			$vimeo_id = 751456724;
			break;
		case 'ACADEMY_FREE':
			$vimeo_id = 763598394;
			break;
		default:
			$vimeo_id = 751443191;
			break;
	}
	if (empty($tutorial)) {
		return 'https://vimeo.com/'.$vimeo_id;
	} elseif ($tutorial == 1) { return 'https://vimeo.com/751458213'; 
	} elseif ($tutorial == 2) { return 'https://vimeo.com/750472776'; 
	} elseif ($tutorial == 3) { return 'https://vimeo.com/750375804'; 
	} elseif ($tutorial == 4) { return 'https://vimeo.com/750463009'; 
	} elseif ($tutorial == 5) { return 'https://vimeo.com/750475828'; 
	} elseif ($tutorial == 6) { return 'https://vimeo.com/750477576'; 
	} elseif ($tutorial == 7) { return 'https://vimeo.com/750479403'; 
	} elseif ($tutorial == 8) { return 'https://vimeo.com/750469626'; 
	} elseif ($tutorial == 9) { return 'https://vimeo.com/752975978'; }
	return;
}

function log_user_ip($ip_address = '') {
	global $wpdb, $user_id;
	if (empty($user_id)) {
		$user_id = get_current_user_id();
	}
	if ( empty( $ip_address ) ) {
		$ip_address = return_user_ip();
	} 
	$q = $wpdb->get_row( "SELECT * FROM academy_students WHERE user_id = $user_id " );
	$user_ip_addresses = $q->user_ip_addresses;
	$infusion_id = $q->infusion_id;
	if ( !empty($infusion_id) ) {
		$ips = unserialize($user_ip_addresses);
		$write_ips = array();
		if ( !empty($ips) ) {
			foreach ($ips AS $ip) {
				$write_ips[] .= $ip;
				if ($ip != $ip_address) { $write_ips[] .= $ip_address; } 
			}
			$wpdb->update('academy_students', array(
					'user_ip_addresses' => serialize($write_ips)
			),array ('user_id' => $user_id));
		} else {
			$write_ips[] .= $ip_address;
			$wpdb->update('academy_students', array(
					'user_ip_addresses' => serialize($write_ips)
			),array ('user_id' => $user_id));
		}	
	}
}

//setcookie($cookie_name, $cookie_value, strtotime("+1 year"));

function event_has_replay($event_id){
	global $wpdb;
	$found = $wpdb->get_var( 'SELECT ID FROM academy_event_recordings WHERE event_id = '.intval($event_id) );
	return ($found > 0) ? 'true' : 'false';
}


function vimeo_return_download_link($vimeo_id = 0, $return = 'array') {
	$client = new Vimeo("4b8aa7cfcc3ca72c070d952629bfc5061c459f37", "dUbGlWUkDSZoyPce8ehdnfoxDpAwGeoU5uuxEg0ecrESqGLh7taUehQOZqk8bL3a22xqA2vxt3cvekLIxe39/AhNpokavpKsmDJlr671roBVGCTPG5aDnoBEauCCJDIH", "7d303a30c260a569f0ea69cb01265f9b");
    $response = $client->request('/videos/' . $vimeo_id, [], 'GET');

    if ($response['status'] !== 200 || !isset($response['body']['files'])) {
        return null; // Handle the error appropriately
    }

    $videoDetails = $response['body'];
    $name = $videoDetails['name'] ?? 'Unknown';
    $remaining_api_calls = $response['headers']['x-ratelimit-remaining'] ?? 0;

    $downloadUrls = [];
    foreach ($videoDetails['files'] as $file) {
        if (isset($file['quality'], $file['link']) && $file['quality'] !== 'hls' && $file['quality'] !== 'dash') {
            $downloadUrls[$file['quality'] . $file['rendition']] = $file['link'];
        }
    }

    $preferredQualityOrder = ['sourcesource', 'hd1080p', 'hd720p', 'sd540p', 'sd360p', 'sd240p'];
    $download_url = '';
    foreach ($preferredQualityOrder as $quality) {
        if (isset($downloadUrls[$quality])) {
            $download_url = $downloadUrls[$quality];
            break;
        }
    }

    if ($return == 'array') {
        return [
            'url' => $download_url,
            'name' => $name,
            'remaining_api_calls' => $remaining_api_calls
        ];
    } elseif ($return == 'link') {
        return $download_url;
    }
}



function return_user_pref($prefs,$pref_to_return) {
	$prefs_array = unserialize($prefs);
	if (!empty($prefs_array)) {
		foreach ($prefs_array AS $k => $v) {
			if ($pref_to_return == $k) { return $v; }
		}
	} else { return ; }
}

function return_cancel_step_2_link() {
	return "?step=2&contact=$_GET[contact]&id=$_GET[id]&pid=$_GET[pid]";
}

function return_get_param($get = '') {
	if (!empty($get)) {
		return $_GET[$get];
	} else { return ; }
}

function return_user_pref_setting($pref_to_return) {
	global $wpdb, $user_id;
	if ($user_id < 1) { return; }
	$user_prefs = $wpdb->get_var( "SELECT prefs FROM academy_user_prefs WHERE user_id = $user_id " );
	$user_prefs = unserialize($user_prefs);
	if (!empty($user_prefs)) {
	foreach ($user_prefs AS $user_pref => $user_setting) {
		if ($user_pref == $pref_to_return) { return $user_setting; }
	}
	}
	return NULL;
}

function je_write_user_prefs($pref_to_update,$updated_setting,$redirect = '',$user_id = 0) {
	global $wpdb;
	$found = 0;
	$user_id = get_current_user_id();
	if ($user_id < 1) { return; }
	$user_prefs = $wpdb->get_var( "SELECT prefs FROM academy_user_prefs WHERE user_id = $user_id " );
	$user_prefs = unserialize($user_prefs);
	foreach ($user_prefs AS $user_pref => $user_setting) {
		if ($debug == 'y_4kEdm2a7w' ){  echo "<br>pref = $user_pref, current setting = $user_setting<br>"; }
		if ($user_pref == $pref_to_update) { $found = 1; }
		if ($user_pref == $pref_to_update) {
			$user_prefs[$user_pref] = $updated_setting;
			$found = 1;
		} elseif ($user_pref != $pref_to_update) {
			$user_prefs[$user_pref] = $user_setting;
		}
	}
	if ($found == 0) { $user_prefs[$pref_to_update] = $updated_setting; } // add
	$wpdb->update('academy_user_prefs', array(
			'prefs' => serialize($user_prefs)
	),array ('user_id' => $user_id));
	if ($pref_to_update == 'freetome') {
		$url = 'https://jazzedge.academy/search/';
	} elseif (!empty($redirect)) {
		$url = $redirect;
	} else {
		$url = je_return_full_url();
	}
	wp_redirect($url);
	exit;
}


function je_return_path_type() {
	return $_GET['type'];
}

function je_return_full_url($url = ''){
	$url = (empty($url)) ? $_SERVER['HTTP_REFERER'] : $url;
	$parts = parse_url($url);
	parse_str($parts['query'], $query);
	if (!empty($query)) {$build_query = '?';}
	foreach ($query AS $k => $v) {
		$build_query .= $k . '=' . $v . '&';
	}
 	return $parts['scheme'].'://'.$parts['host'].$parts['path'].substr($build_query,0,-1);
}

function je_return_billing_cycle($cycle) {
	switch ($cycle) {
    case 1:
        return 'year';
        break;
    case 2:
        return 'month';
        break;
    case 3:
        return 'week';
        break;
	}
}

function return_path_step_practice_time($int = 3){
	$int = intval($int);
	switch ($int) {
		case 3:
		return '1-3 Days';
		break;
		case 7:
		return 'One Week';
		break;
		case 14:
		return '1-2 Weeks';
		break;
		case 30:
		return '3-4 Weeks';
		break;
		case 60:
		return 'Ongoing - Keep Coming Back To This Lesson';
		break;
		case 75:
		return '1-3 Months';
		break;
		case 90:
		return '3+ Months';
		break;
		case 120:
		return '6 Months';
		break;
		case 365:
		return '1 Year';
		break;
	}
	return;
}

function return_step_resources($key_sig){
	$key_sig = intval($key_sig);
	$resource = array();
	switch ($key_sig) {
		case 1:
			$resource['ESSENTIALS-C'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-c/';
			break;
		case 2:
			$resource['ESSENTIALS-Dflat'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-d-flat/';
			break;
		case 3:
			$resource['ESSENTIALS-D'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-d/';
			break;
		case 4:
			$resource['ESSENTIALS-Eflat'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-e-flat/';
			break;
		case 5:
			$resource['ESSENTIALS-E'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-e/';
			break;
		case 6:
			$resource['ESSENTIALS-F'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-f/';
			break;
		case 7:
			$resource['ESSENTIALS-Fsharp'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-f-sharp/';
			break;
		case 8:
			$resource['ESSENTIALS-G'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-g/';
			break;
		case 9:
			$resource['ESSENTIALS-Aflat'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-a-flat/';
			break;
		case 10:
			$resource['ESSENTIALS-A'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-a/';
			break;
		case 11:
			$resource['ESSENTIALS-Bflat'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-b-flat/';
			break;
		case 12:
			$resource['ESSENTIALS-B'] = 'https://jazzedge.academy/lesson/piano-essentials-key-of-b/';
			break;
		
	}
	return $resource;
}

function je_return_class_sample() {
	if (empty($_GET['id'])) {
		$post_id = get_the_ID();
	} else {
		$post_id = intval($_GET['id']);
	}
	$class_type = get_field( "class_type", $post_id );
	if ($class_type == 'jazz-piano') { return 'https://vimeo.com/795335652'; }
	elseif ($class_type == 'music-history') { return 'https://vimeo.com/812459724'; }
	elseif ($class_type == 'improvisation') { return 'https://vimeo.com/805582931'; }
	elseif ($class_type == 'vocal-training') { return 'https://vimeo.com/795341042'; }
	elseif ($class_type == 'rhythm-training') { return 'https://vimeo.com/795342442'; }
	elseif ($class_type == 'pop-rock') { return 'https://vimeo.com/795339822'; }
	elseif ($class_type == 'transcription') { return 'https://vimeo.com/795337002'; }
	elseif ($class_type == 'music-notation') { return 'https://vimeo.com/795338342'; }
	elseif ($class_type == 'academy-coaching') { return 'https://vimeo.com/812850655'; }
	elseif ($class_type == 'premier-coaching') { return 'https://vimeo.com/812850655'; }
	elseif ($class_type == 'willie-coaching') { return 'https://vimeo.com/812853354'; }
	elseif ($class_type == 'standards-beg') { return 'https://vimeo.com/838680385'; }
	elseif ($class_type == 'standards-adv') { return 'https://vimeo.com/838682672'; }
	elseif ($class_type == 'practical-theory') { return 'https://vimeo.com/867292738'; }
	else { return 'https://vimeo.com/751443191'; }
}

function je_return_class_sample_vimeo_id() {
	$post_id = get_the_ID();
	$class_type = get_field( "class_type", $post_id );
	if ($class_type == 'jazz-piano') { return '795335652'; }
	elseif ($class_type == 'music-history') { return '812459724'; }
	elseif ($class_type == 'improvisation') { return '805582931'; }
	elseif ($class_type == 'vocal-training') { return '795341042'; }
	elseif ($class_type == 'rhythm-training') { return '795342442'; }
	elseif ($class_type == 'pop-rock') { return '795339822'; }
	elseif ($class_type == 'transcription') { return '795337002'; }
	elseif ($class_type == 'music-notation') { return '795338342'; }
	elseif ($class_type == 'academy-coaching') { return '812850655'; }
	elseif ($class_type == 'premier-coaching') { return '812850655'; }
	elseif ($class_type == 'willie-coaching') { return '812853354'; }
	elseif ($class_type == 'standards-beg') { return '838680385'; }
	elseif ($class_type == 'standards-adv') { return '838682672'; }
	elseif ($class_type == 'practical-theory') { return '867292738'; }
	else { return '751443191'; }
}


function ja_does_event_recording_exist($event_id) {
	global $wpdb;
	$found = $wpdb->get_var( "SELECT event_id FROM academy_event_recordings WHERE event_id = $event_id " );
	return $found;
}

function url_param_key_equals_value($key, $value)
{
	foreach($_GET as $k => $v)
		{  
			if ($k == '_lesson_search' AND !empty($v)) { return 'true'; }
			if ($k == $key && $v == $value) {
					return 'true';
				}
		}
    return 'false';
}

// get their memberium data and write to our dbase for quicker access (not needed)
/*
function je_write_memb_login( $user_login, $user ) {
	global $wpdb;
	$found = $wpdb->get_var( "SELECT ID FROM academy_students WHERE user_login = '$user_login' " );
	if (empty($found)) {
		$data = array(	
					'user_login' => $user_login, 
					);
		$format = array('%s');
		$wpdb->insert('academy_students',$data,$format);
		}
	}
*/
//add_action( 'wp_login', je_write_memb_login, 99, 2 );

function mje_return_autologin_link($redirect = 'dashboard'){
	$infusion_id = memb_getContactId();
	$infusion_email = memb_getContactField('Email');
	$link = 'https://myjazzedge.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G';
	$link .= "&Id=$infusion_id&Email=$infusion_email";
	$link .= "&redir=/$redirect";
	return $link;
}

function je_write_memb_data() {
	global $wpdb;
	$current_user = wp_get_current_user();
	$user_login = $current_user->user_login;
	$found = $wpdb->get_var( "SELECT ID FROM academy_students WHERE user_login = '$user_login' " );
	$infusion_id = memb_getContactId();
	$membership_level = je_return_membership_level();
	if (!empty($found) && $current_user->ID > 0) {
		$wpdb->update(
			'academy_students', array(
			'user_id' => $current_user->ID,
			'membership' => $academy_level,
			'infusion_id' => $infusion_id,
		),array ('user_login' => $user_login));
	} else {
		$data = array(	
			'user_id' => $current_user->ID, 
			'membership' => $membership_level,
			'infusion_id' => $infusion_id,
			'user_login' => $user_login
		);
	$format = array('%d','%s','%d','%s');
	$wpdb->insert('academy_students',$data,$format);
	}
}

function je_top_5_lessons_viewed($interval = 7) {
	// SELECT COUNT(*) AS `Rows`, `lesson_id` FROM `je_video_tracking` WHERE datetime BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE() GROUP BY `lesson_id` ORDER BY `Rows` DESC LIMIT 5;

}

function je_return_membership_expired() {
	$academy_expiration_date = strtotime(memb_getContactField('_AcademyExpirationDate'));
	if (empty($academy_expiration_date)) {
		return 'false';
	}
	$now = time();
	if ($now <= $academy_expiration_date) {
		return 'false';
	} else {
		return 'true';
	}
	return;
}


function ja_is_legacy_lessons_classes() {
	//$post_id = get_the_ID();
	$access = 'false';
	if (je_return_membership_expired() === 'true') {$here = 1;  $access = 'false'; }
	if (memb_hasMembership('JA_YEAR_LSN_CLASSES') == TRUE) { $access = 'true'; }
	return $access;
}

function ja_has_class_coaching_access($type = 'coaching',$location = 0) {
	//$post_id = get_the_ID();
	$access = 'false';
	if (je_return_membership_expired() == 'true') {$here = 1;  $access = 'false'; }
	if ($type == 'coaching') {
		$required_membership_level = get_field( "required_membership_level" );
		if (empty($required_membership_level) && isset($_GET['id']) && $_GET['id'] > 0) { $required_membership_level = get_field( "required_membership_level", intval($_GET['id']) ); }
		if (memb_hasMembership('ACADEMY_PREMIER') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_MONTHLY_PREMIER') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_YEAR_PREMIER') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_MONTHLY_STUDIO') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_YEAR_STUDIO') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('ACADEMY_ACADEMY') == TRUE && $required_membership_level <= 2) { $access = 'true'; }
		elseif (memb_hasMembership('JA_MONTHLY_LSN_COACHING') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_YEAR_LSN_COACHING') == TRUE) { $access = 'true'; }
	} elseif ($type == 'class') {
		$class_type = get_field( "class_type" );
		if (empty($class_type) && isset($_GET['id']) && $_GET['id'] > 0) { $class_type = get_field( "class_type", intval($_GET['id']) ); }
		
		$is_academy_member = (memb_hasMembership('ACADEMY_ACADEMY') == TRUE || memb_hasMembership('ACADEMY_ACADEMY_NC') == TRUE) ? 'true' : 'false';
		if (memb_hasMembership('ACADEMY_PREMIER') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_MONTHLY_PREMIER') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_YEAR_PREMIER') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_MONTHLY_STUDIO') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_YEAR_STUDIO') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_MONTHLY_LSN_CLASSES') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_YEAR_CLASSES_ONLY') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_MONTHLY_CLASSES_ONLY') == TRUE) { $access = 'true'; }
		elseif (memb_hasMembership('JA_YEAR_LSN_CLASSES') == TRUE) { $access = 'true'; }
		elseif ($is_academy_member == 'true' && $class_type == 'music-notation' || $is_academy_member == 'true' && $class_type == 'music-history') { $access = 'true'; }
		
	}
	global $user_id;
	/*
	if ($user_id == 5729) {
	 echo "<br><br>access=$access // here = $here // type = $type // location = $location ";
	}
	*/
	
	return $access;
}



function je_can_user_view_event($event_id = 0){
	global $wpdb, $user_id, $user_membership_level_num, $user_membership_product;
	if ($event_id == 0) { $event_id = intval($_GET['id']); }
	$access = 'false';
	if (ja_is_premier() == 'true') { return 'true'; }
	$has_access = je_check_credit_used_for_event($event_id);
	$has_credit_access = je_check_credit_used_for_class($event_id);
	$event_sku = get_field('event_sku',$event_id);
	$event_type = get_field('event_type',$event_id);
	
	if ($event_type == 'coaching') { 
		$type = 'coaching'; 
	} else { 
		$type == 'class'; 
	}
	$event_purchased = je_check_event_purchased($event_sku);
	$required_membership_level = get_post_meta( $event_id, 'required_membership_level', true );
	
	if ($event_id < 1) { return 'false'; }

	if ($has_access == 'true' || $has_credit_access == 'true') { $access = 'true'; }

	if ($event_purchased == 'true') { $access = 'true'; }
	if ($user_membership_product == 'ACADEMY_ACADEMY' || $user_membership_product == 'ACADEMY_PREMIER') {
		if ($user_membership_level_num >= $required_membership_level) { $access = 'true'; }
	}
	$has_class_coaching_access = ja_has_class_coaching_access($type);
	if ($has_class_coaching_access == 'true') {
		$access = 'true';
	}
	
	if ($type != 'coaching') {
		//$has_access = je_check_credit_used_for_event($event_id);
		$has_class_access = ja_has_class_coaching_access('class');
		$has_credit_access = je_check_academy_credit_access($event_id);
		if ($has_class_access === 'true' || $has_credit_access === 'true') {
			$access = 'true';
		}
	}
	
	// testing
	/*
	if ($user_id == 3567) {
		echo "<h1>TESTING.....</h1><br>uid = $user_id<br>";
		echo "post_id = $post_id<br>";
		echo "has_class_coaching_access = $has_class_coaching_access<br>";
		echo "user_membership_level_num = $user_membership_level_num<br>";
		echo "has_credit_access = $has_credit_access<br />";
		echo "has_access = $has_access<br>";
		echo "has_class_access = $has_class_access<br>";
		echo "event_sku = $event_sku<br>";
		echo "event_purchased = $event_purchased<br>";
		echo "required_membership_level = $required_membership_level<br> ";
		echo "user_membership_product = $user_membership_product<br> ";
		echo "<hr />ACCESS = $access";
	}
	*/
	
	return $access;
}

/*
function je_return_active_member(){
	$user_id = get_current_user_id();
	if ($user_id < 1) { return 'false'; }
	$membership_level = je_return_membership_level();
	if ($membership_level == 'ACADEMY_PREMIER' OR  $membership_level == 'ACADEMY_ACADEMY' OR  $membership_level == 'ACADEMY_ACADEMY_NC' OR $membership_level == 'ACADEMY_SONG' ) {
		// check for expired access
		if (je_return_membership_expired() == 'true') {
			return 'false';
		} else {
			return 'true';
		}
	} else {
		return 'false';
	}
}
*/



function je_return_membership_level_name_from_num($num){ // wmyette delete?
	if ($num == 3) { return 'Premier'; }
	elseif ($num == 2) { return 'Academy'; }
	elseif ($num == 1) { return 'Song'; }
	else { return 'Any'; }
}


function je_return_registered_member(){
	$membership_level = je_return_membership_level();
	if ($membership_level == 'ACADEMY_FREE' OR $membership_level == 'ACADEMY_PREMIER' OR  $membership_level == 'ACADEMY_ACADEMY' OR  $membership_level == 'ACADEMY_ACADEMY_NC' OR $membership_level == 'ACADEMY_SONG' ) {
		return 'true';
	} else {
		return 'false';
	}
}

/* CHATGPT FUNCTIONS .... */

function checkForSpacesAfterCommas($string) {
    // Regular expression to search for a comma followed directly by a space
    $pattern = '/,\s/';

    // Perform the search
    if (preg_match($pattern, $string)) {
        return true; // Indicates a comma is followed by a space
    } else {
        return false; // No commas are followed by a space
    }
}

function removeSpacesAfterCommas($string) {
    // Regular expression to find a comma followed by one or more spaces
    $pattern = '/,\s+/';

    // Replace each comma followed by one or more spaces with a comma without spaces
    $result = preg_replace($pattern, ',', $string);

    return $result;
}

function containsAsterisk($string) {
    return strpos($string, '*') !== false;
}

function areDatesOnTheSameDay($date1, $date2) {
    // Create DateTime objects from the input dates
    $dateTime1 = new DateTime($date1);
    $dateTime2 = new DateTime($date2);

    // Compare the year, month, and day components
    return $dateTime1->format('Y-m-d') === $dateTime2->format('Y-m-d');
}




function je_check_free_access($name='chapters'){
	$membership_level = je_return_membership_level();
	if ($membership_level != '' AND $membership_level != 'ACADEMY_FREE') { return; } // they are a member or must register
	$chapters_viewed = je_return_chapters_viewed('norental');
	$resources_viewed = je_return_links_viewed();
	$max_free_chapters = 25;
	$max_free_resources = 3;
	if ($name == 'chapters') {
		if ($membership_level == 'ACADEMY_FREE' AND $chapters_viewed < $max_free_chapters) { return; }
		elseif ($membership_level == 'ACADEMY_FREE' AND $chapters_viewed >= $max_free_chapters) { 
			return 'maxed';
		}
	} elseif ($name == 'resources') {
		if ($membership_level == 'ACADEMY_FREE' AND $resources_viewed < $max_free_resources) { return; }
		elseif ($membership_level == 'ACADEMY_FREE' AND $resources_viewed >= $max_free_resources) { 
			return 'maxed';
		}
	}
}

function ja_skill_level($skill_level) {
	if (empty($skill_level)) { return; }
	switch ($skill_level) {
		case 0 :
			$level = 'Beginner';
			break;
		case 1 :
			$level = 'Beginner';
			break;	
		case 2: 
			$level = 'Intermediate';
			break;
		case 3: 
			$level = 'Advanced';
			break;
		case 4: 
			$level = 'Professional';
			break;
		default :
			$level = 'Intermediate';
			break;
	}
	return $level;
}

function je_return_skill_level_image($post_id = 0) { 
	$post_id = get_the_ID();
	$lesson_skill_level = get_field( "lesson_skill_level", $post_id);
	$post_type = get_post_type($post_id);
	if ($post_type != 'lesson') { return; }
	switch ($lesson_skill_level) {
		case 1:
			$image = 'https://jazzedge.academy/wp-content/uploads/2024/01/path-beginner-squashed.jpg';
			break;
		case 2:
			$image = 'https://jazzedge.academy/wp-content/uploads/2024/01/path-intermediate-squashed.jpg';
			break;
		case 3:
			$image = 'https://jazzedge.academy/wp-content/uploads/2024/01/path-advanced-squashed.jpg';
			break;
		case 4:
			$image = 'https://jazzedge.academy/wp-content/uploads/2024/01/path-advanced-squashed.jpg';
			break;
		default:
			$image = 'https://jazzedge.academy/wp-content/uploads/2024/01/path-intermediate-squashed.jpg';
	}
	return $image;
}

function je_return_skill_level($post_id = 0) { // wmyette need to update this since skill level is now numeric in postmeta
	if ($post_id < 1) { return; }
	$lesson_level = get_post_meta( $post_id, 'lesson_skill_level', true );
	switch ($lesson_level) {
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
	return $level;
}


function je_return_user_registered_for_event($event_id) {
	global $wpdb;
	$post_id = (!empty($event_id)) ? $event_id : get_the_ID();
	$user_id = get_current_user_id();
	$registered = $wpdb->get_var( "SELECT ID FROM academy_event_registration WHERE user_id = $user_id AND event_id = $post_id " );
	if (!empty($registered)) { return 'true'; } else { return 'false'; }
}

function je_return_user_credits($type = 'rental'){
	global $wpdb, $user_id;
	switch ($type) {
		case 'rental':
			$select = 'rental_credits';
			break;
		case 'download':
			$select = 'download_credits';
			break;
		case 'class':
			$select = 'class_credits';
			break;
	}
	$count = $wpdb->get_var( "SELECT $select FROM academy_user_credits WHERE user_id = $user_id" );
	return ($count > 0) ? $count : 0;
}

function je_check_credit_used_for_event($event_id = 0){ // deprecated
	global $wpdb;
	if ($event_id == 0) { return; }
	$user_id = get_current_user_id();
	$found = $wpdb->get_var( "SELECT event_id FROM academy_event_access WHERE user_id = $user_id AND event_id = ".intval($event_id)."" );
	return (!empty($found)) ? 'true' : 'false';
}

function je_check_credit_used_for_class($event_id = 0){ // deprecated
	global $wpdb, $user_id;
	if ($event_id == 0) { return; }
	$found = $wpdb->get_var( "SELECT post_id FROM academy_user_credit_log WHERE user_id = $user_id AND post_id = ".intval($event_id)."" );
	return (!empty($found)) ? 'true' : 'false';
}


function je_check_event_purchased($event_sku = ''){ // deprecated
	global $wpdb, $user_id;
	$purchase_date = $wpdb->get_var( "SELECT date_purchased FROM academy_event_purchased WHERE user_id = $user_id AND event_sku = '".sanitize_text_field($event_sku)."'" );
	return (!empty($purchase_date)) ? 'true' : 'false';
}


function je_check_rental_access($lesson_id = 0, $user_id = 0){
	global $wpdb;
	$lesson_id = ($lesson_id == 0) ? get_the_ID() : $lesson_id;
	$user_id = ($check_user_id > 0) ? $check_user_id : get_current_user_id();
	$expiration_date = $wpdb->get_var( "SELECT expiration_date FROM academy_credit_purchases WHERE user_id = $user_id AND (lesson_id = $lesson_id OR post_id = $lesson_id) ORDER BY expiration_date DESC" );
	$expiration_date = strtotime($expiration_date);
	return ($expiration_date >= strtotime("NOW - 4 HOURS")) ? 'active' : 'expired';
}

function je_check_academy_credit_access($id = 0){
	global $wpdb;
	$id = ($id == 0) ? get_the_ID() : $id;
	$user_id = get_current_user_id();
	$found_1 = $wpdb->get_var( "SELECT user_id FROM academy_user_credit_log WHERE user_id = $user_id AND post_id = $id" );
	$found_2 = $wpdb->get_var( "SELECT event_id FROM academy_event_access WHERE user_id = $user_id AND event_id = $id" );
	return ($found_1 || $found_2) ? 'true' : 'false';
}

function je_has_purchased_class(){
	global $wpdb;
	$user_id = get_current_user_id();
	$found_1 = $wpdb->get_var( "SELECT user_id FROM academy_user_credit_log WHERE user_id = $user_id AND type = 'class_event'" );
	return ($found_1) ? 'true' : 'false';
}


function je_check_download_access($lesson_id = 0, $user_id = 0){
	global $wpdb;
	$lesson_id = ($lesson_id == 0) ? get_the_ID() : $lesson_id;
	$user_id = (isset($check_user_id) && $check_user_id > 0) ? $check_user_id : get_current_user_id();
	$q = $wpdb->get_var( "SELECT ID FROM academy_downloads WHERE user_id = $user_id AND (lesson_id = $lesson_id OR post_id = $lesson_id)" );
	return (!empty($q)) ? 'true' : 'false';
}

function je_return_active_rentals($user_id = 0){
	global $wpdb;
	$active_rentals = array();
	$user_id = ($check_user_id > 0) ? $check_user_id : get_current_user_id();
	$rentals = $wpdb->get_results( "SELECT * FROM academy_credit_purchases WHERE user_id = $user_id" );
	foreach ($rentals AS $rental) {
		$expiration_date = strtotime($rental->expiration_date);
		if ($expiration_date >= strtotime("NOW - 4 HOURS")) {
			$active_rentals[$rental->lesson_id] .= $expiration_date;
		}
	}
	return $active_rentals;
}


function has_active_rentals($user_id = 0){
	$rentals = je_return_active_rentals();
	return (!empty($rentals)) ? 'true' : 'false';
}


function je_return_rental_details($return = 'expiration_date', $lesson_id = 0, $check_user_id = 0){
	global $wpdb, $user_id;
	$lesson_id = ($lesson_id == 0) ? get_the_ID() : $lesson_id;
	if ($return == 'expiration_date') {
		$datetime = $wpdb->get_var( "SELECT expiration_date FROM academy_credit_purchases WHERE user_id = $user_id AND lesson_id = $lesson_id ORDER BY expiration_date DESC" );
		$human_date = date('l, m-d-Y \a\t\ h:ia',strtotime($datetime));
		return $human_date;
	} elseif ($return == 'start_date') {
		$datetime = $wpdb->get_var( "SELECT start_date FROM academy_credit_purchases WHERE user_id = $user_id AND lesson_id = $lesson_id ORDER BY expiration_date DESC" );
		$human_date = date('l, m-d-Y \a\t\ h:ia',strtotime($datetime));
		return $human_date;
	} elseif ($return == 'lesson_rented') {
		$found = $wpdb->get_results( "SELECT ID FROM academy_credit_purchases WHERE user_id = $user_id AND (lesson_id = $lesson_id OR post_id = $lesson_id) ORDER BY expiration_date DESC" );
		//echo "SELECT ID FROM academy_credit_purchases WHERE user_id = $user_id AND (lesson_id = $lesson_id OR post_id = $lesson_id)";
		$return = (!empty($found)) ? 'true' : 'false';
		return $return;
	} 
}



function show_lesson_progress_div() {
	$user_id = get_current_user_id();
	if ($user_id < 1) { return 'false'; }
	$lesson_rental_status = je_check_rental_access();
	// $active_member = je_return_active_member();
	$active_member = je_has_lesson_access();
	if ($lesson_rental_status == 'active' || $active_member == 'true') {
		return 'true';
	} else {
		return 'false';
	}
}

function je_return_has_jpc_sheet(){
	global $wpdb, $user_id;
	$jpc = $wpdb->get_var( "SELECT ID FROM je_practice_curriculum_assignments WHERE user_id = $user_id ORDER BY ID DESC" );	
	if (!empty($jpc)) { return TRUE; } else { return FALSE; }
}

function je_return_has_fundational(){
	global $wpdb, $user_id;
	$jpc = $wpdb->get_row( "SELECT * FROM je_practice_curriculum_fundational WHERE user_id = $user_id" );	
	if (!empty($jpc)) { return TRUE; } else { return FALSE; }
}

function je_return_has_repertoire(){
	global $wpdb, $user_id;
	$jpc = $wpdb->get_row( "SELECT * FROM academy_user_repertoire WHERE user_id = $user_id" );	
	if (!empty($jpc)) { return TRUE; } else { return FALSE; }
}

function je_return_min_practiced_today(){
	global $wpdb, $user_id;
	$sum = $wpdb->get_var( "SELECT SUM(practice_time) FROM academy_practice_log WHERE user_id = $user_id AND DATE(datetime) = CURDATE()" );
	return $sum;
}

function je_return_min_practiced_today_formatted($minutes){
	return convertToHoursMins(intval($minutes), '%02d:%02d');
}


function je_return_chapters_viewed($include_rental = ''){
	global $wpdb, $user_id;
	if ($include_rental == 'norental') {
		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM je_video_tracking WHERE user_id = $user_id AND rental_view != 1" );
	} else {
		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM je_video_tracking WHERE user_id = $user_id " );
	}
	return $count;
}

function je_return_links_viewed($range = 999){
	global $wpdb;
	$current_user = wp_get_current_user();
	$user_login = $current_user->user_login;
	if ($range == 999) {
		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM je_link_views WHERE username = '$user_login' " );
	} else {
		$start_date = date('Y-m-d', strtotime("- $range DAYS"));
		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM je_link_views WHERE username = '$user_login' AND (date BETWEEN '$start_date' AND CURRENT_DATE) " );
	}
	if (empty($count)) { return 0; } else {	return $count; }
}


function wpe_date($date = '',$calc = '-',$duration = '7 DAYS') {
	if (empty($date)) { return; }
	if ($date == 'now' OR $date == 'today') { return date('Y-m-d H:i:s', strtotime("-4 HOURS")); }	
	$new_date = date('Y-m-d H:i:s', strtotime("$calc $duration, -4 HOURS"));
	return $new_date;
}



function je_return_viewing_data($return = 'chapter_views', $start_date = '', $end_date = ''){
	global $wpdb;
//	$today = date('Y-m-d H:i:s', strtotime("-4 HOURS")); 
	$user_id = get_current_user_id();
	$today = date('Y-m-d'); 
	$start_date = (!empty($start_date)) ? $start_date : $today . ' 00:00:00';
	$end_date = (!empty($end_date)) ? $end_date : $today . ' 23:59:59';	
	
	if ($return == 'chapter_views') {
	$chapter_views = $wpdb->get_var( "SELECT COUNT(ID) FROM je_video_tracking WHERE user_id = $user_id AND (datetime BETWEEN '$start_date' AND '$end_date') " );
	return $chapter_views;
	}
	
	if ($return == 'last_lesson') {
		$r = $wpdb->get_row( "SELECT * FROM je_video_tracking WHERE user_id = $user_id ORDER BY datetime DESC LIMIT 1 " );
		$return = array();
		$return["date"] = date('m-d-Y',strtotime($r->datetime));
		$return["time"] = date('h:ia',strtotime($r->datetime));
		$return["post_id"] = $r->post_id;
		$return["lesson_id"] = $r->lesson_id;
		$return["chapter_id"] .= $r->chapter_id;
		$return["skill_level"] .= $r->skill_level;
		$return["element"] .= $r->element;
		$return["pillar"] .= $r->pillar;
		$return["lesson_title"] .= je_return_lesson_title($r->lesson_id);	
		return $return;
	}
	/*
	if ($return == 'last_lesson_title') {
		$lesson_id = $wpdb->get_var( "SELECT lesson_id FROM je_video_tracking WHERE user_id = $user_id ORDER BY datetime DESC LIMIT 1 " );
		return je_return_lesson_title($lesson_id);
	}	
	*/
	if ($return == 'pillars') {
		$pillars = $wpdb->get_results( "SELECT pillar, COUNT(*) AS count FROM je_video_tracking WHERE user_id = $user_id AND (datetime BETWEEN '$start_date' AND '$end_date') GROUP BY pillar" );
		if (!empty($pillars)) {
			foreach ($pillars AS $r) {
				$pillar_count[$r->pillar] .= $r->count;
			}
			return $pillar_count;
		} else { return; }
	}
	if ($return == 'elements') {
		$elements = $wpdb->get_results( "SELECT element, COUNT(*) AS count FROM je_video_tracking WHERE user_id = $user_id AND (datetime BETWEEN '$start_date' AND '$end_date') GROUP BY element" );
		return "SELECT element, COUNT(*) AS count FROM je_video_tracking WHERE user_id = $user_id AND (datetime BETWEEN '$start_date' AND '$end_date') GROUP BY element";
		if (!empty($elements)) {
			foreach ($elements AS $r) {
				$element_count[$r->element] .= $r->count;
			}
			return $element_count;
		} else { return; }
	}
	
}


function je_link($url = '', $download = 0)
{
	global $wpdb;
	$post_id = intval($_GET['id']);
	$current_user = wp_get_current_user();
	$username = $current_user->user_login;
	$user_id = $current_user->ID;
	$email = $current_user->user_email;

	$data = array(
		'user_id' => $user_id, 
		'date' => date('Y-m-d'),
		'url' => $url,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'username' => $username,
		'email' => $email,
	);

	$format = array('%d','%s','%s','%s','%s','%s');
	$found = $wpdb->get_var("SELECT ID FROM je_link_views WHERE url = '$url' AND username = '$username'");

	// Insert the record if not already found
	if (empty($found)) {
		$wpdb->insert('je_link_views', $data, $format);
	}

	// Check if the URL starts with 'https://jazzedge.academy/'
	if (strpos($url, 'https://jazzedge.academy/') !== 0) {
		// If not, modify the URL for S3
		$url = 'https://s3.amazonaws.com/jazzedge-resources/' . $url;
	}

	// Handle download or redirect
	if ($download == 1) {
		force_download($url);
	} else {
		wp_redirect($url);
	}
	exit;
}


function download_chapter($url,$filename='chapter') {
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-Disposition: attachment; filename=".$filename.".mp4"); 
	readfile($url); 
}

function console_log($output, $with_script_tags = false) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

/*
function format_time($t,$f=':') // t = seconds, f = separator 
{
  	$t = intval($t);
	return sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
}
*/

function format_time($t,$f=':') // t = seconds, f = separator 
{
  	$t = intval($t);
  	$hours = (floor($t/3600) > 0) ? floor($t/3600) : 0;
	if ($hours > 0) {
		return sprintf("%02d%s%02d%s%02d", $hours, $f, ($t/60)%60, $f, $t%60);
	} else {
		return sprintf("%02d%s%02d", ($t/60)%60, $f, $t%60);
	}
}


function ja_delete_user_data($user_id) {
	global $wpdb;
	if ($user_id < 0) {
		$user_id = get_current_user_id();
	}
	if ($user_id < 0) {
		return;
	}
	$wpdb->query( $wpdb->prepare( "UPDATE academy_user_repertoire SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE je_video_tracking SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE je_link_views SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE je_practice_curriculum_progress SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE academy_completed_chapters SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE je_practice_curriculum_assignments SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE academy_favorites SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE academy_practice_log SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE academy_completed_lessons SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE practice_actions SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE academy_premier_quiz_results SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE academy_user_notes SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE academy_pc_graded SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE academy_completed_chapters SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE academy_recently_viewed SET deleted_at = NOW() WHERE user_id = %d", $user_id ) );
}


function ja_return_lesson_duration($post_id){
	global $wpdb;
	$lesson_id = $wpdb->get_var( "SELECT ID FROM academy_lessons WHERE post_id = ".intval($post_id)."" );	
	$total_lesson_duration = $wpdb->get_var( "SELECT SUM(duration) FROM academy_chapters WHERE lesson_id = $lesson_id" );
 	return format_time($total_lesson_duration);
}

function ja_return_lesson_duration_in_seconds($post_id) {
	global $wpdb;
	$lesson_id = $wpdb->get_var("SELECT ID FROM academy_lessons WHERE post_id = ".intval($post_id));
	$total_lesson_duration = $wpdb->get_var("SELECT SUM(duration) FROM academy_chapters WHERE lesson_id = $lesson_id");
	return intval($total_lesson_duration); // Return duration in seconds as an integer
}

function ja_return_class_duration_in_seconds($post_id) {
	global $wpdb;
	$total_class_duration = $wpdb->get_var("SELECT duration FROM academy_event_recordings WHERE event_id = $post_id");
	return intval($total_class_duration); // Return duration in seconds as an integer
}

function ja_return_lesson_duration_formatted($post_id) {
	$duration_in_seconds = ja_return_lesson_duration_in_seconds($post_id);
	return format_time($duration_in_seconds); // Format the duration for display
}



function ja_return_class_duration($post_id){
	global $wpdb;
	$class_duration = $wpdb->get_var( "SELECT duration FROM academy_event_recordings WHERE event_id = ".intval($post_id)."" );	
 	return format_time($class_duration);
}



function percent_complete($completed, $total) {
  $count1 = $completed /  max($total, 1);
  $count2 = $count1 * 100;
  $percent = number_format($count2, 0);
  if ($percent == 0) { 
  	return $percent; 
  } else  {
  	$percent = sprintf('%02d', $percent);
  	return $percent;
  }
}

function je_return_short_course_description($course_id = 0) {
	global $wpdb;
	$post_id = get_the_ID();
	$content = get_post_field('post_content',$post_id);
	return mb_strimwidth($content,0,150,"...");
}


function je_get_permalink($course_lesson_id,$type = 'lesson') {
	$meta_key = null;
	if ($course_lesson_id < 1) { return; }
	if ($type === 'class') { return 'https://jazzedge.academy/event-replay/?id='.$course_lesson_id; }
	if ($type === 'path') { return get_the_permalink($course_lesson_id); }
	switch ($type) {
	case 'course':
		$meta_key = "course_id";
		break;
	case 'lesson':
		$meta_key = "lesson_id";
		break;
	}
	global $wpdb;
	$post_id = $wpdb->get_var( "select post_id, meta_key from $wpdb->postmeta where meta_key = '$meta_key' AND meta_value = $course_lesson_id" );
	$permalink = get_permalink($post_id);
	return $permalink;
}

function je_get_postid($course_lesson_id,$name = 'lesson') {
	if ($course_lesson_id < 1) { return; }
	switch ($name) {
	case 'course':
		$meta_key = "course_id";
		break;
	case 'lesson':
		$meta_key = "lesson_id";
		break;
	}
	global $wpdb;
	$post_id = $wpdb->get_var( "select post_id from $wpdb->postmeta where meta_key = '$meta_key' AND meta_value = $course_lesson_id" );
	return $post_id;
}

function je_return_id_from_slug($slug,$name){
	global $wpdb;
	switch ($name) {
	case 'course':
		$dbase = "academy_courses";
		break;
	case 'lesson':
		$dbase = "academy_lessons";
		break;
	case 'chapter':
		$dbase = "academy_chapters";
		break;
	}
	$slug = sanitize_text_field($slug);
	$id = $wpdb->get_var( "SELECT ID FROM $dbase WHERE slug = '$slug'" ); 
	return $id;
}

function je_return_slug_from_id($id,$name){
	global $wpdb;
	switch ($name) {
	case 'course':
		$dbase = "academy_courses";
		break;
	case 'lesson':
		$dbase = "academy_lessons";
		break;
	case 'chapter':
		$dbase = "academy_chapters";
		break;
	}
	$id = intval($id);
	$slug = $wpdb->get_var( "SELECT slug FROM $dbase WHERE ID = $id" ); 
	return $slug;
}

function je_return_course_id($lesson_id = 0) {
		global $wpdb;
		$lesson_id = intval($lesson_id);
		$course_id = $wpdb->get_var( "SELECT course_id FROM academy_lessons WHERE ID = $lesson_id" );
		return $course_id;
}


function je_return_next_chapter_link($chapter_id = 0, $lesson_id = 0) {
	global $wpdb;
	if ($lesson_id == 0 || $chapter_id == 0) { return; }
	$chapters = $wpdb->get_results( "SELECT ID,menu_order FROM academy_chapters WHERE lesson_id = $lesson_id ORDER BY menu_order ASC" );
	$chapters_array = array();
	$order = 1;
	foreach ($chapters AS $chapter){
		if ($chapter_id == $chapter->ID) { 
			$chapters_array[] = array('order' => $order, 'ID' => $chapter->ID, 'current' => TRUE);
			$chapter_menu_order = $chapter->menu_order; 
		} else {
			$chapters_array[] = array('order' => $order, 'ID' => $chapter->ID, 'current' => FALSE);
		}
		$order++;
	}
	$current_order = NULL;
	$next_chapter_link = NULL;
	foreach ($chapters_array AS $c){
		if ($c['current'] == TRUE) { $current_order = $c['order']; }
		$next_order = $current_order + 1;
	}
	foreach ($chapters_array AS $c){
		if ($c['order'] == $next_order) { 
			$next_chapter_link = je_return_slug_from_id($c['ID'],'chapter'); 
		}
	}
	return $next_chapter_link;
}

function je_return_chapter_title($chapter_id = 0) {
    global $wpdb;
    $chapter_id = intval($chapter_id);
    return $wpdb->get_var($wpdb->prepare("SELECT chapter_title FROM academy_chapters WHERE ID = %d", $chapter_id));
}

function je_return_lesson_title($lesson_id = 0) {
    global $wpdb;
    $lesson_id = intval($lesson_id);
    $lesson_title = $wpdb->get_var($wpdb->prepare("SELECT lesson_title FROM academy_lessons WHERE ID = %d", $lesson_id));
    return stripslashes($lesson_title);
}

function je_return_class_title($event_id = 0) {
    global $wpdb;
    $event_id = intval($event_id);
    $q = $wpdb->get_row($wpdb->prepare("SELECT * FROM academy_event_recordings WHERE event_id = %d", $event_id));
    if ($q) {
        return stripslashes($q->title . ' (' . date('F jS, Y', strtotime($q->date)) . ')');
    }
    return '';
}

function je_return_course_title($course_id = 0) {
    global $wpdb;
    $course_id = intval($course_id);
    return $wpdb->get_var($wpdb->prepare("SELECT course_title FROM academy_courses WHERE ID = %d", $course_id));
}

function je_return_path_title($path_id = 0) {
    return get_the_title(intval($path_id));
}

function je_chapter_not_selected($lesson_id = 0) {
    global $wpdb;
    $user_id = get_current_user_id();
    if ($user_id == 1972) {
        echo 'Viewing as admin';
        return;
    }
    $lesson_id = intval($lesson_id);
    $slug = $wpdb->get_var($wpdb->prepare(
        "SELECT slug FROM academy_chapters WHERE lesson_id = %d ORDER BY menu_order ASC", 
        $lesson_id
    ));
    if ($slug) {
        wp_redirect('?c=' . $slug);
        exit;
    }
}

function je_return_free_chapter($lesson_id = 0) {
    global $wpdb;
    if ($lesson_id < 1) {
        $lesson_id = get_field('lesson_id', get_the_ID());
    }
    $vimeo_id = $wpdb->get_var($wpdb->prepare(
        "SELECT vimeo_id FROM academy_chapters WHERE lesson_id = %d AND free = 'y' LIMIT 1",
        $lesson_id
    ));
    return $vimeo_id;
}

function je_is_chapter() {
    if (je_return_id_from_slug($_GET['c'], 'chapter') > 0) {
        return true;
    } else {
        global $wpdb;
        $slug = sanitize_text_field($_GET['c']);
        $post_id = get_the_ID();
        $lesson_id = get_post_meta($post_id, 'lesson_id', true);
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(ID) FROM academy_chapters WHERE lesson_id = %d",
            intval($lesson_id)
        ));
        return $count == 1;
    }
}


function je_return_number_of_lesson_chapters($lesson_id){
	global $wpdb;
	$count = $wpdb->get_var( "SELECT COUNT(ID) FROM academy_chapters WHERE lesson_id = ".intval($lesson_id)."" ); 
	return $count;
}

function je_return_number_of_lessons_in_course($course_id = 0){ 
	global $wpdb;
	if ($course_id > 0) { $course_id = intval($course_id); }
	else { 
		$course_id = get_the_ID();
 	}
 	$total_number_of_lessons = $wpdb->get_var( "SELECT COUNT(ID) FROM academy_lessons WHERE course_id = $course_id " );
	return $total_number_of_lessons;
}

function je_return_number_of_lessons_in_path($path_id = 0){ 
	global $wpdb, $user_id;
    $total_number_of_lessons = 0; // Initialize the variable outside of the if block
	if ($path_id > 0) { $path_id = intval($path_id); }
	else { 
		$path_id = get_the_ID();
 	}
 	$steps = get_field('path_steps',$path_id); 
	if ( $steps ) {
		$total_number_of_lessons = 0;
		foreach ($steps AS $step) {
			$total_number_of_lessons ++;
		}	
	}
	return $total_number_of_lessons;
}

function je_event_watched($event_id){
	global $wpdb, $user_id;
	$q = $wpdb->get_var( "SELECT user_id FROM academy_event_watched WHERE user_id = $user_id AND event_id = $event_id" ); 
	if (!empty($q)){ return 'true'; } else { return 'false'; }
}

function je_is_chapter_complete($chapter_id){
	global $wpdb, $user_id;
	$q = $wpdb->get_var( "SELECT user_id FROM academy_completed_chapters WHERE chapter_id = $chapter_id AND user_id = $user_id" ); 
	if (!empty($q)){ return TRUE; } else { return FALSE; }
}

function je_is_lesson_complete($lesson_id = 0){ // was je_are_all_lesson_chapters_complete
	global $wpdb, $user_id;
	if ($lesson_id > 0) { $lesson_id = intval($lesson_id); }
	else { 
		$post_id = get_the_ID();
		$lesson_id = get_post_meta( $post_id, 'lesson_id', true );
 	}
	$lesson_chapter_count = $wpdb->get_var( "SELECT COUNT(*) FROM academy_chapters WHERE lesson_id = $lesson_id" );
	$completed_chapters_count = $wpdb->get_var( "SELECT COUNT(DISTINCT chapter_id) FROM academy_completed_chapters WHERE lesson_id = $lesson_id AND user_id = $user_id" ); 

	if ($lesson_chapter_count == $completed_chapters_count){ return TRUE; } else { return FALSE; }
}

function je_is_course_complete($course_id = 0){ 
	global $wpdb, $user_id;
	if ($course_id > 0) { $course_id = intval($course_id); }
	else { 
		$post_id = get_the_ID();
		$course_id = get_post_meta( $post_id, 'course_id', true );
 	}
	$course_lesson_count = $wpdb->get_var( "SELECT COUNT(*) FROM academy_lessons WHERE course_id = $course_id" );
	$completed_lessons_count = $wpdb->get_var( "SELECT COUNT(*) FROM academy_completed_lessons WHERE course_id = $course_id AND user_id = $user_id" ); 
	if ($course_lesson_count == $completed_lessons_count){ return TRUE; } else { return FALSE; }
}

	
function je_is_path_complete($path_id = 0){ 
	global $wpdb, $user_id;
	if ($path_id > 0) { $path_id = intval($path_id); }
	else { 
		$path_id = get_the_ID();
 	}
 	/*
 	get_field($selector, [$post_id], [$format_value]);
	$selector (string) (Required) The field name or field key.
	$post_id (mixed) (Optional) The post ID where the value is saved. Defaults to the current post.
	$format_value (bool) (Optional) Whether to apply formatting logic. Defaults to true.
	*/
 	$steps = get_field('path_steps',$path_id); 
	if ( $steps ) {
		$total_number_of_lessons = 0;
		$total_completed_lessons = 0;
		foreach ($steps AS $step) {
			$lesson_id = get_post_meta( $step['lesson_post_id'], 'lesson_id', true );
			if (je_is_lesson_complete($lesson_id) == TRUE) {
				$total_completed_lessons ++;
			}
			$total_number_of_lessons ++;
		}	
	}
	if ($total_number_of_lessons == $total_completed_lessons){ return TRUE; } else { return FALSE; }
}

function je_date_path_completed($path_id = 0,$format = 'full'){ 
	global $wpdb, $user_id;
	if ($path_id > 0) { $path_id = intval($path_id); }
	else { 
		$path_id = get_the_ID();
 	}
 	$date = $wpdb->get_var( "SELECT datetime FROM academy_completed_paths WHERE user_id = $user_id AND path_id = $path_id " );
 	if ($format == 'full') {
 		return (empty($date)) ? '' : '<span style="color:#f04e23;">Completed on: <strong>' .date('m-d-Y',strtotime($date)).'</strong></span>';
	} else {
 		return (empty($date)) ? '' : '<span style="color:#f04e23;"><strong>' .date('m-d-Y',strtotime($date)).'</strong></span>';
	}	
}

 
function je_is_lesson_marked_complete($lesson_id = 0){
	global $wpdb, $user_id;
	if ($lesson_id > 0) { $lesson_id = intval($lesson_id); }
	else { 
		$post_id = get_the_ID();
		$lesson_id = get_post_meta( $post_id, 'lesson_id', true );
 	}
	$q = $wpdb->get_var( "SELECT lesson_id FROM academy_completed_lessons WHERE lesson_id = $lesson_id AND user_id = $user_id " );
	if (!empty($q)){ return TRUE; } else { return FALSE; }
}

function je_is_course_marked_complete($course_id = 0){
	global $wpdb, $user_id;
	if ($course_id > 0) { $course_id = intval($course_id); }
	else { 
		$post_id = get_the_ID();
		$course_id = get_post_meta( $post_id, 'course_id', true );
 	}
	$q = $wpdb->get_var( "SELECT course_id FROM academy_completed_courses WHERE course_id = $course_id AND user_id = $user_id " );
	if (!empty($q)){ return TRUE; } else { return FALSE; }
}

function je_is_path_marked_complete($path_id = 0){
	global $wpdb, $user_id;
	if ($path_id > 0) { $path_id = intval($path_id); }
	else { 
		$path_id = get_the_ID();
 	}
	$q = $wpdb->get_var( "SELECT ID FROM academy_completed_paths WHERE path_id = $path_id AND user_id = $user_id " );
	if (!empty($q)){ return TRUE; } else { return FALSE; }
}


// Pass the lesson or course id from our dbase, not the wordpress post_id
function je_return_lesson_progress_percentage($lesson_id,$format = 'percent') {
	global $wpdb;
	if ($lesson_id == 'ACF_lesson_id') {
		$post_id = get_the_ID();
		$lesson_id = get_field('lesson_id',$post_id);
	}
	$user_id = get_current_user_id();
	$chapters = $wpdb->get_results( "SELECT * FROM academy_chapters WHERE lesson_id = ".intval($lesson_id)."" );
	$total_number_of_chapters = 0;
	$total_completed_chapters = 0;
	foreach ($chapters AS $chapter){
		if (je_is_chapter_complete($chapter->ID) == TRUE) {
			$total_completed_chapters ++;
		}
		$total_number_of_chapters ++;
	}
	if ($format == 'percent') {
		return percent_complete($total_completed_chapters,$total_number_of_chapters);
	} else {
		return "You've Completed: $total_completed_chapters / $total_number_of_chapters chapters";
	}

}

// Pass the lesson or course id from our dbase, not the wordpress post_id
function je_return_course_progress_percentage($course_id,$format = 'percent') {
	global $wpdb, $user_id;
	$lessons = $wpdb->get_results( "SELECT * FROM academy_lessons WHERE course_id = ".intval($course_id)."" );
	$total_number_of_lessons = 0;
	$total_completed_lessons = 0;
	foreach ($lessons AS $lesson){
		if (je_is_lesson_complete($lesson->ID) == TRUE) {
			$total_completed_lessons ++;
		}
		$total_number_of_lessons ++;
	}
	if ($format == 'percent') {
		return percent_complete($total_completed_lessons,$total_number_of_lessons);
	} else {
		return "You've Completed: $total_completed_lessons / $total_number_of_lessons lessons";
	}
}

// Pass the lesson or course id from our dbase, not the wordpress post_id
function je_return_path_progress_percentage($path_id,$format = 'percent') {
	global $wpdb, $user_id;
	$steps = get_field('path_steps',$path_id); 
	if ( $steps ) {
		$total_number_of_lessons = 0;
		$total_completed_lessons = 0;
		foreach ($steps AS $step) {
			$lesson_id = get_post_meta( $step['lesson_post_id'], 'lesson_id', true );
			if (je_is_lesson_complete($lesson_id) == TRUE) {
				$total_completed_lessons ++;
			}
			$total_number_of_lessons ++;
		}	
	}
	if ($format == 'percent') {
		return percent_complete($total_completed_lessons,$total_number_of_lessons);
	} else {
		return "You've Completed: $total_completed_lessons / $total_number_of_lessons lessons";
	}
}

function je_return_resource_type($resource,$return='name'){
	$font_awesome_class = '';
	$image_name = '';
	$name = '';
	switch ($resource) {
	case 'pdf':
		$name = "Sheet Music 1";
		$image_name = "sheetmusic";
		$font_awesome_class = 'fa fa-music';
		break;
	case 'pdf2':
		$name = "Sheet Music 2";
		$image_name = "sheetmusic";
		$font_awesome_class = 'fa fa-music';
		break;
	case 'pdf3':
		$name = "Sheet Music 3";
		$image_name = "sheetmusic";
		$font_awesome_class = 'fa fa-music';
		break;
	case 'pdf4':
		$name = "Sheet Music 4";
		$image_name = "sheetmusic";
		$font_awesome_class = 'fa fa-music';
		break;
	case 'pdf5':
		$name = "Sheet Music 5";
		$image_name = "sheetmusic";
		$font_awesome_class = 'fa fa-music';
		break;
	case 'jam':
		$name = "Backing Track 1";
		$image_name = "backing-track";
		$font_awesome_class = 'fa-drum';
		break;
	case 'jam2':
		$name = "Backing Track 2";
		$image_name = "backing-track";
		$font_awesome_class = 'fa-drum';
		break;
	case 'jam3':
		$name = "Backing Track 3";
		$image_name = "backing-track";
		$font_awesome_class = 'fa-drum';
		break;
	case 'ireal':
		$name = "iRealPro 1";
		$image_name = "irealpro";
		$font_awesome_class = 'fa-solid fa-trumpet';
		break;
	case 'ireal2':
		$name = "iRealPro 2";
		$image_name = "irealpro";
		$font_awesome_class = 'fa-solid fa-trumpet';
		break;
	case 'ireal3':
		$name = "iRealPro 3";
		$image_name = "irealpro";
		$font_awesome_class = 'fa-solid fa-trumpet';
		break;
	case 'callresponse1':
		$name = "Call & Response 1";
		$image_name = "callresponse";
		$font_awesome_class = 'fa-solid fa-megaphone';
		break;
	case 'callresponse2':
		$name = "Call & Response 2";
		$image_name = "callresponse";
		$font_awesome_class = 'fa-solid fa-megaphone';
		break;
	case 'callresponse3':
		$name = "Call & Response 3";
		$image_name = "callresponse";
		$font_awesome_class = 'fa-solid fa-megaphone';
		break;
	case 'mp3':
		$name = "Lesson Audio";
		$image_name = "audio";
		$font_awesome_class = 'fa fa-volume';
		break;
	case 'midi':
		$name = "Midi Files";
		$image_name = "midi";
		$font_awesome_class = 'fa fa-sliders-h';
		break;
	default:
		$name = "Resource";
		$image_name = "sheetmusic";
		$font_awesome_class = 'fa fa-music';
	}
	if ($return == 'name') { return $name; }
	elseif ($return == 'image_name') { return $image_name; }
	elseif ($return == 'font_awesome_class') { return $font_awesome_class; }
}

/* START JPC FUNCTIONS */

function tip_practice_action_id() {
	global $tip_practice_action_id;
	return $tip_practice_action_id;
	//$post_id = get_the_ID();
	//$tip_practice_action_id = get_sub_field('tip_practice_action_id');
}


function je_return_jpc_step_data($step_id,$data='title'){  
	global $wpdb;
	$step_id = intval($step_id);
	if ($step_id < 1 || empty($step_id)) { return; }
	if ($data == 'key_sig') {
			$key_sig = $wpdb->get_var( "SELECT key_sig FROM je_practice_curriculum_steps WHERE step_id = $step_id" );
			return $key_sig;
	}
	$curriculum_id = $wpdb->get_var( "SELECT curriculum_id FROM je_practice_curriculum_steps WHERE step_id = $step_id" );
	$q = $wpdb->get_row( "SELECT * FROM je_practice_curriculum WHERE ID = $curriculum_id " );
	if ($data == 'title') {	return stripslashes($q->focus_title); }
	elseif ($data == 'element') {	return $q->focus_element; }
	elseif ($data == 'type') {	return $q->focus_pillar; }
	elseif ($data == 'tempo') {	return $q->tempo; }
	elseif ($data == 'focus_1_set') { if ($q->focus_1_id > 1) { return TRUE; }  else { return FALSE; } 	}
	elseif ($data == 'focus_2_set') { if ($q->focus_2_id > 1) { return TRUE; }  else { return FALSE; } 	}
	elseif ($data == 'focus_3_set') { if ($q->focus_3_id > 1) { return TRUE; }  else { return FALSE; } 	}
}

function pc_return_class_percentage_complete($return = 'json', $post_id = 0) {
    $post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
    $practice_actions = get_field('practice_actions', $post_id);
    $completed_practice_actions = 0;
    $total_practice_actions = 0;
    
    if (is_array($practice_actions)) {
        $total_practice_actions = count($practice_actions);
    }

    $action_id = 1;
    if ($practice_actions) {
        foreach ($practice_actions as $row) {
            $completed = pc_is_practice_action_complete($post_id, $action_id);
            if ($completed) {
                $completed_practice_actions++;
            }
            $action_id++;
        }
    }

    if ($return === 'truefalse') {
    	if ($total_practice_actions > 0) {
        return ($completed_practice_actions === $total_practice_actions) ? 'true' : 'false';
        } else { return 'false'; }
    }

    if ($return === 'cssclass') {
        return ($completed_practice_actions === $total_practice_actions) ? 'hide-completed-class' : '';
    }

    if ($return === 'buttonclass') {
        return ($completed_practice_actions === $total_practice_actions) ? 'collapsed' : '';
    }

    $json_array = array($completed_practice_actions, $total_practice_actions);
    return json_encode($json_array);
}

function pc_return_has_premier_course_access($post_id = 0) {
	$user_id = get_current_user_id();
	if ($user_id < 1) { return 'false'; }
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$post_type = get_post_type($post_id);
	$purchased = pc_return_premier_course_purchased($post_id);
	//	$premier_access = do_shortcode('[memb_has_any_tag tagid=9821,9813,10142]');
	//	$studio_access = do_shortcode('[memb_has_any_tag tagid=9954,10136,9807,9827,9819,9956,10136]');

	if (memb_hasAnyTags([9821,9813,10142])) {
		return 'true';
	}
	
	if ($purchased === 'true') {
		return 'true';
 	}
	return 'false';
}

function pc_return_premier_course_purchased($post_id = 0) {
	global $wpdb;
	$user_id = get_current_user_id();
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$post_type = get_post_type($post_id);
	if ($post_type === 'premier-class') {
		$premier_course_id = get_field('premier_course_id', $post_id);
		$premier_course_sku = get_field('premier_course_sku', $premier_course_id);
	} else {
		$premier_course_sku = get_field('premier_course_sku', $post_id);
	}
	//echo "SELECT ID FROM academy_event_purchased WHERE event_sku = '$premier_course_sku' AND user_id = $user_id ";
	$found = $wpdb->get_var( "SELECT ID FROM academy_event_purchased WHERE event_sku = '$premier_course_sku' AND user_id = $user_id " );
	return (!empty($found)) ? 'true' : 'false';
}

function pc_return_premier_course_promo_video_vimeo_id($post_id = 0) {
	global $wpdb;
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$vimeo_id = get_field('premier_course_promo_video_vimeo_id', $post_id);
	return ($vimeo_id > 0) ? 'https://vimeo.com/'.$vimeo_id : 'https://vimeo.com/751443191';
}

function pc_return_has_quiz($post_id = 0) {
	global $wpdb;
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$has_quiz = get_field('premier_class_has_quiz', $post_id);
	return ($has_quiz === 'y') ? '<span style="color: #4caf50;">Yes</span>' : 'No';
}



function pc_return_course_classs_array($post_id = 0) {
	global $wpdb;
	$pc_classes_array = array();
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$args = array(
		'post_type' => 'premier-class',
		'post_status' => 'publish',
		'orderby' => 'ID',
		'posts_per_page' => -1,
		'order' => 'ASC',
		'meta_query' => array(
				array(
					'key' => 'premier_course_id',
					'value' => $post_id,
					'compare' => '='
				),
				array(
					'key' => 'premier_class_type',
					'value' => 'recorded-class',
					'compare' => '='
				),                
			),
		); 
	$pc_classes = get_posts( $args );
	foreach ($pc_classes AS $pc_class) {
		$pc_classes_array[] = $pc_class->ID;
	}
	return $pc_classes_array;
}

function pc_return_assignments_completed($return = 'status', $post_id = 0) {
    global $wpdb, $user_id;
    $classes_array = array();
    $user_id = ($user_id > 0) ? $user_id : get_current_user_id();
    $post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
    $classes_array = pc_return_course_classs_array($post_id);
    $class_ids = '';

    foreach ($classes_array as $class_id) {
        $class_ids .= $class_id . ',';
    }
    $class_ids = rtrim($class_ids, ',');

    if (empty($class_ids)) {
        return 0;
    }

    $count = $wpdb->get_var(
        "SELECT COUNT(*) FROM academy_pc_graded WHERE user_id = $user_id  AND deleted_at IS NULL AND class_id IN ($class_ids) AND grade = 'pass';"
    );

    if ($count < 1) {
        return 0;
    } else {
        return $count;
    }
}

function pc_return_quizzes_completed($return = 'status', $post_id = 0) {
    global $wpdb, $user_id;
    $classes_array = array();
    $user_id = ($user_id > 0) ? $user_id : get_current_user_id();
    $post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
    $classes_array = pc_return_course_classs_array($post_id);
    $class_ids = '';

    foreach ($classes_array as $class_id) {
        $class_ids .= $class_id . ',';
    }
    $class_ids = rtrim($class_ids, ',');

    if (empty($class_ids)) {
        return 0;
    }

    $count = $wpdb->get_var(
        "SELECT COUNT(*) FROM academy_premier_quiz_results WHERE user_id = $user_id  AND deleted_at IS NULL AND premier_class_id IN ($class_ids) AND score >= 70;"
    );

    if ($count < 1) {
        return 0;
    } else {
        return $count;
    }
    /*
    if ($count === 6 && $return === 'status') { return 'true'; } 
    elseif ($return === 'count') { return $count; }
    else { return; }
    */
}

function pc_return_submitted_recital_piece($post_id = 0) {
	global $wpdb, $user_id;
	$found = 0;
	$user_id = ($user_id > 0) ? $user_id : get_current_user_id();
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
    $premier_course_semester = get_field("premier_course_semester");
	$found = $wpdb->get_var( "SELECT id FROM wp_fluentform_submissions WHERE form_id = 16 AND user_id = $user_id AND status LIKE '%$post_id%' AND status LIKE '%$premier_course_semester%' " );    
	if ($found > 0) { return 'true'; } else { return 'false'; }
}


function pc_return_course_live_dates($post_id = 0) {
	global $wpdb;
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$live_classes = $wpdb->get_results("SELECT post.post_title, 
       metaDate.meta_value AS live_date, 
       metaId.meta_value AS course_id 
FROM wp_posts AS post 
LEFT JOIN wp_postmeta AS metaDate ON post.ID = metaDate.post_id AND metaDate.meta_key = 'premier_live_class_date'
LEFT JOIN wp_postmeta AS metaId ON post.ID = metaId.post_id AND metaId.meta_key = 'premier_course_id'
WHERE post.post_type = 'premier-class' 
  AND post.post_status = 'publish'
  AND metaId.meta_value = '$post_id';
  ");
	/*
	 "
		SELECT
		  post.post_title,
		  metaDate.meta_value AS live_date,
		  metaId.meta_value AS course_id
		FROM wp_posts AS post
		LEFT JOIN (
		  SELECT
			*
		  FROM wp_postmeta
		  WHERE meta_key = 'premier_live_class_date'
		) AS metaDate
		  ON post.ID = metaDate.post_id
		LEFT JOIN (
		  SELECT
			*
		  FROM wp_postmeta
		  WHERE meta_key = 'premier_course_id'
		) AS metaId
		  ON post.ID = metaId.post_id
		WHERE post.post_type = 'premier-class' AND post.post_status = 'publish';
	" 
	*/
	
	$html = '<ul class="live_class_date_list">';
	$user_timezone = (!empty($_SESSION['user-timezone'])) ? $_SESSION['user-timezone'] : 'America/New_York';
	
   	foreach ($live_classes AS $live_class) {
		if (!empty($live_class->live_date) && $live_class->course_id = $post_id) {
			$live_class_date_utz = format_timezone($live_class->live_date,$user_timezone);
			$html .= '<li>'.$live_class_date_utz.'</li>';
		}
   	}
   	$html .= '</ul>';
    return $html;
}

function pc_return_course_title() {
	global $premier_course_title, $post_id;
	if (!empty($premier_course_title)) { return $premier_course_title; }
	$post_id = ($post_id > 0) ? $post_id : get_the_ID();
	$premier_course_id = get_field('premier_course_id', $post_id);
	$premier_course_title = get_the_title($premier_course_id);
	return $premier_course_title;
}


function pc_return_course_promo_video_link() {
	global $premier_course_promo_video_vimeo_id, $post_id;
	return "https://vimeo.com/$premier_course_promo_video_vimeo_id";
}


function pc_return_submitted_assignment($post_id = 0, $return = 'text') {
    global $wpdb;
    $user_id = get_current_user_id();
    if ($user_id < 1) {
        return;
    }

    // Ensure $post_id is properly set
    $post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();

    // Prepare the SQL query with placeholders for safety
    $forms = $wpdb->get_results($wpdb->prepare(
        "SELECT id, response FROM wp_fluentform_submissions WHERE form_id IN (10,23) AND user_id = %d AND status != 'redo'",
        $user_id
    ));

    $found = false;
    foreach ($forms as $form) {
        $response = (string) $form->response;
        $r = json_decode($response, true);

        // Check if 'class_id' exists in the response array
        if (isset($r['class_id'])) {
            $class_id = $r['class_id'];
            if ($class_id == $post_id) {
                $found = true;
                break;
            }
        }
    }

    if ($return === 'text') {
        return $found ? '<span style="color: #4caf50;">Yes</span>' : 'No';
    } elseif ($return === 'icon') {
        return $found ? '<span style="color: #4caf50;"><i class="fa-sharp fa-regular fa-list-check"></i></span> Assignment Complete' : '<i class="fa-sharp fa-regular fa-list-check"></i> Assignment Incomplete';
    }

    // Return null if $return parameter is neither 'text' nor 'icon'
    return null;
}


function pc_return_num_assignments($post_id = 0, $return = 'text') {
	global $wpdb;
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$assignments = $wpdb->get_results("SELECT post.post_title, metaDate.meta_value AS vimeo_id, metaId.meta_value AS course_id FROM wp_posts AS post LEFT JOIN wp_postmeta AS metaDate ON post.ID = metaDate.post_id AND metaDate.meta_key = 'premier_class_assignment_vimeo_id' LEFT JOIN wp_postmeta AS metaId ON post.ID = metaId.post_id AND metaId.meta_key = 'premier_course_id' WHERE post.post_type = 'premier-class' AND post.post_status = 'publish' AND metaId.meta_value = '$post_id' AND metaDate.meta_value > 0 AND metaDate.meta_value > 751443191;");
	return count($assignments);
}

function pc_return_num_quizzes($post_id = 0, $return = 'text') {
	global $wpdb;
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$quizzes = $wpdb->get_results("SELECT post.post_title, metaDate.meta_value AS has_quiz, metaId.meta_value AS course_id FROM wp_posts AS post LEFT JOIN wp_postmeta AS metaDate ON post.ID = metaDate.post_id AND metaDate.meta_key = 'premier_class_has_quiz' LEFT JOIN wp_postmeta AS metaId ON post.ID = metaId.post_id AND metaId.meta_key = 'premier_course_id' WHERE post.post_type = 'premier-class' AND post.post_status = 'publish' AND metaId.meta_value = '$post_id' AND metaDate.meta_value = 'y';
");
	return count($quizzes);
}


function pc_return_took_quiz($post_id = 0, $return = 'text') {
	global $wpdb;
	$user_id = get_current_user_id();
	if ($user_id < 1) { return; }
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$date = $wpdb->get_var( "SELECT date FROM academy_premier_quiz_results WHERE user_id = $user_id AND premier_class_id = $post_id " );
	if ($return === 'text') {
		return (!empty($date)) ? '<span style="color: #4caf50;">Yes</span>' : 'No';
	}
	if ($return === 'icon') {
		return (!empty($date)) ? '<span style="color: #4caf50;"><i class="fa-sharp fa-regular fa-question-circle"></i></span> Quiz Complete' : '<i class="fa-sharp fa-regular fa-question-circle"></i> Quiz Incomplete';
	}
}

function pc_return_quiz_score($post_id = 0, $format = 'score') {
	global $wpdb;
	$user_id = get_current_user_id();
	if ($user_id < 1) { return; }
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$score = $wpdb->get_var( "SELECT score FROM academy_premier_quiz_results WHERE user_id = $user_id  AND deleted_at IS NULL AND premier_class_id = $post_id " );

	if ($format != 'score') {
		return (empty($score)) ? '' : "(Your Score: $score%)";
	} else {
		return $score;
	}
}

function pc_return_assignment_grade($post_id = 0, $format = 'score') {
	global $wpdb;
	$user_id = get_current_user_id();
	if ($user_id < 1) { return; }
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$score = $wpdb->get_var( "SELECT grade FROM academy_pc_graded WHERE user_id = $user_id AND class_id = $post_id AND hide IS NULL" );

	if ($format != 'score') {
		return (empty($score)) ? '' : "(".strtoupper($score).")";
	} else {
		return $score;
	}
}

function pc_return_level_graphic($level){
	if ($level < 1) { return; }
	if ($level == 1) { return 'https://jazzedge.academy/wp-content/uploads/2024/05/level-1.png'; }
	if ($level == 2) { return 'https://jazzedge.academy/wp-content/uploads/2024/05/level-2.png'; }
	if ($level == 3) { return 'https://jazzedge.academy/wp-content/uploads/2024/05/level-3.png'; }
}

function pc_is_class_assignment_graded($post_id = 0) {
	global $wpdb;
	$user_id = get_current_user_id();
	if ($user_id < 1) { return; }
	$post_id = ($post_id < 1) ? get_the_ID() : intval($post_id);
	$graded = $wpdb->get_var( "SELECT ID FROM academy_pc_graded WHERE class_id = $post_id AND grade NOT IN ('','pending') AND user_id = $user_id AND hide IS NULL" );
	return ($graded) ? 'true' : 'false';
}

function pc_is_practice_action_complete($post_id = 0, $action_id = 0) {
	global $wpdb;
	$user_id = get_current_user_id();
	if ($user_id < 1) { return; }
	$action_id = intval($action_id);
	$post_id = intval($post_id);
	$completed = $wpdb->get_var( "SELECT completed FROM practice_actions WHERE user_id = $user_id  AND deleted_at IS NULL AND post_id = $post_id AND action_id = $action_id " );
	return $completed;
}

function pc_mark_practice_action_complete($post_id = 0, $action_id = 0) {
	global $wpdb;
	$user_id = get_current_user_id();
	if ($user_id < 1) { return; }
	$action_id = intval($action_id);
	$post_id = intval($post_id);
	$data = array(	
			'user_id' => $user_id, 
			'action_id' => $action_id,
			'post_id' => $post_id,
			'completed' => time(),
		);
	$format = array('%d','%d','%d','%d');
	$wpdb->insert('practice_actions',$data,$format);
	return;
}

function pc_mark_practice_action_reset($post_id = 0, $action_id = 0) {
	global $wpdb;
	$user_id = get_current_user_id();
	if ($user_id < 1) { return; }
	$action_id = intval($action_id);
	$post_id = intval($post_id);
	$wpdb->delete( 'practice_actions', array('post_id' => $post_id, 'user_id' => $user_id, 'action_id' => $action_id));
	return;
}


/* wmyette new function is below jpc_mark_step_complete delete once JPC is updated */
function je_mark_step_complete($step_id = 0) {
	global $wpdb, $user_id;
	$step_id = intval($step_id);
	$today = date('Y-m-d H:i:s', strtotime("-4 HOURS")); 
	if ($step_id < 1 || empty($step_id)) { return; }
	$id = $wpdb->get_var( "SELECT ID FROM je_practice_curriculum_progress WHERE step_id = $step_id AND user_id = $user_id " );
	$curriculum_id = $wpdb->get_var( "SELECT curriculum_id FROM je_practice_curriculum_steps WHERE step_id = $step_id" );
	if (!empty($id)) {
		$wpdb->update('je_practice_curriculum_progress', array( 'curriculum_id' => $curriculum_id, 'date_completed' => $today),array ('ID' => $id));
	} else {
			$data = array(	
					'user_id' => $user_id, 
					'step_id' => $step_id,
					'curriculum_id' => $curriculum_id,
					'date_completed' => $today,
				);
			$format = array('%d','%d','%d','%s');
			$wpdb->insert('je_practice_curriculum_progress',$data,$format);
	}
	return;
}

function jpc_mark_step_complete($user_id, $step_id, $curriculum_id) {
    global $wpdb;

    // Check if the user ID is valid
    if ($user_id < 2000) {
        return new WP_Error('invalid_user', 'Invalid user ID.');
    }

    // Set $curriculum_id to 1 if not set. Helpful for initial setup of JPC
    $curriculum_id = ($curriculum_id > 0) ? $curriculum_id : 1;

    // Define the column name based on the step_id
    $adjusted_step_id = $step_id % 12;
    $adjusted_step_id = $adjusted_step_id == 0 ? 12 : $adjusted_step_id;
    $column_name = 'step_' . $adjusted_step_id;

    // Check if a record exists for the user in jpc_student_progress for the given curriculum_id
    $existing_record = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM jpc_student_progress WHERE user_id = %d AND curriculum_id = %d",
        $user_id, $curriculum_id
    ));
    if ($existing_record == 0) {
        // Insert a new record if none exists
        $insert_result = $wpdb->insert(
            'jpc_student_progress',
            [
                'user_id' => $user_id,
                'curriculum_id' => $curriculum_id,
                $column_name => $step_id
            ],
            [
                '%d',
                '%d',
                '%d'
            ]
        );

        if ($insert_result === false) {
            return new WP_Error('db_insert_error', 'Failed to insert a new record.');
        }
        
    } else {
        // Update the corresponding step column with the step_id
        $result = $wpdb->update(
            'jpc_student_progress',
            [
                $column_name => $step_id
            ], // Data to update
            ['user_id' => $user_id, 'curriculum_id' => $curriculum_id], // Where clause
            ['%d'],                    // Data format
            ['%d', '%d']               // Where format
        );

        if ($result === false) {
            return new WP_Error('db_update_error', 'Failed to update the step.');
        }
    }

    // Update the completed_on column for the step_id in jpc_student_assignments
    $wpdb->update(
        'jpc_student_assignments',
        ['completed_on' => current_time('mysql')],
        ['user_id' => $user_id, 'step_id' => $step_id],
        ['%s'],
        ['%d', '%d']
    );

    // Get the most recent step_id from jpc_student_assignments
    $last_step_id = $wpdb->get_var($wpdb->prepare(
        "SELECT step_id FROM jpc_student_assignments WHERE user_id = %d AND deleted_at IS NULL ORDER BY ID DESC LIMIT 1",
        $user_id
    ));

    // Calculate the next step_id
    $next_step_id = $last_step_id + 1;

    // Insert the next step assignment in jpc_student_assignments
    $insert_next_step = $wpdb->insert(
        'jpc_student_assignments',
        [
            'user_id' => $user_id,
            'date' => current_time('mysql'),
            'step_id' => $next_step_id,
            'curriculum_id' => $curriculum_id,
            'deleted_at' => NULL
        ],
        [
            '%d',
            '%s',
            '%d',
            '%d',
            '%s'
        ]
    );

    if ($insert_next_step === false) {
        return new WP_Error('db_insert_error', 'Failed to insert the next step assignment.');
    }

    return true;
}

function jpc_mark_step_complete_old($user_id, $step_id, $curriculum_id) {
    global $wpdb;

    // Check if the user ID is valid
    if ($user_id < 2000) {
        return new WP_Error('invalid_user', 'Invalid user ID.');
    }

	// set $curriculum_id to 1 if not set. Helpful for initial setup of JPC
	$curriculum_id = ($curriculum_id > 0) ? $curriculum_id : 1;

    // Define the column name based on the step_id
    $adjusted_step_id = $step_id % 12;
    $adjusted_step_id = $adjusted_step_id == 0 ? 12 : $adjusted_step_id;
    $column_name = 'step_' . $adjusted_step_id;

    // Check if a record exists for the user in jpc_student_progress for the given curriculum_id
    $existing_record = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM jpc_student_progress WHERE user_id = %d AND curriculum_id = %d",
        $user_id, $curriculum_id
    ));
    if ($existing_record == 0) {
        // Insert a new record if none exists
        $insert_result = $wpdb->insert(
            'jpc_student_progress',
            [
                'user_id' => $user_id,
                'curriculum_id' => $curriculum_id,
                $column_name => $step_id
            ],
            [
                '%d',
                '%d',
                '%s'
            ]
        );

        if ($insert_result === false) {
            return new WP_Error('db_insert_error', 'Failed to insert a new record.');
        }
        
    } else {
        // Update the corresponding step column with the step_id
        $result = $wpdb->update(
            'jpc_student_progress',
            [
                $column_name => $step_id
            ], // Data to update
            ['user_id' => $user_id, 'curriculum_id' => $curriculum_id], // Where clause
            ['%s'],                    // Data format
            ['%d', '%d']               // Where format
        );

        if ($result === false) {
            return new WP_Error('db_update_error', 'Failed to update the step.');
        }
    }
        // Get the most recent step_id from jpc_student_assignments
        $last_step_id = $wpdb->get_var($wpdb->prepare(
            "SELECT step_id FROM jpc_student_assignments WHERE user_id = %d AND deleted_at IS NULL ORDER BY ID DESC LIMIT 1",
            $user_id
        ));

        // Calculate the next step_id
        $next_step_id = $last_step_id + 1;
        // Insert the next step assignment in jpc_student_assignments
        $insert_next_step = $wpdb->insert(
            'jpc_student_assignments',
            [
                'user_id' => $user_id,
                'date' => current_time('mysql'),
                'step_id' => $next_step_id,
                'curriculum_id' => $curriculum_id,
                'deleted_at' => NULL
            ],
            [
                '%d',
                '%s',
                '%d',
                '%d',
                '%s'
            ]
        );

        if ($insert_next_step === false) {
            return new WP_Error('db_insert_error', 'Failed to insert the next step assignment.');
        }
    

    return true;
}

function jpc_delete_curriculum_data($user_id, $curriculum_id) {
    global $wpdb;

    // Prepare and execute the delete query for jpc_student_assignments
    $query_assignments = $wpdb->prepare("DELETE FROM jpc_student_assignments WHERE user_id = %d AND curriculum_id >= %d", $user_id, $curriculum_id);
    $result_assignments = $wpdb->query($query_assignments);

    // Prepare and execute the delete query for jpc_student_progress
    $query_progress = $wpdb->prepare("DELETE FROM jpc_student_progress WHERE user_id = %d AND curriculum_id >= %d", $user_id, $curriculum_id);
    $result_progress = $wpdb->query($query_progress);

    // Check if both queries were successful
    if ($result_assignments === false || $result_progress === false) {
        return new WP_Error('db_delete_error', 'Failed to delete the curriculum data.');
    }

    return true;
}

function jpc_delete_all_user_data($user_id) {
    global $wpdb;

    // Prepare and execute the delete query for jpc_student_assignments
    $query_assignments = $wpdb->prepare("DELETE FROM jpc_student_assignments WHERE user_id = %d", $user_id);
    $result_assignments = $wpdb->query($query_assignments);

    // Prepare and execute the delete query for jpc_student_progress
    $query_progress = $wpdb->prepare("DELETE FROM jpc_student_progress WHERE user_id = %d", $user_id);
    $result_progress = $wpdb->query($query_progress);

    // Check if both queries were successful
    if ($result_assignments === false || $result_progress === false) {
        return new WP_Error('db_delete_error', 'Failed to delete the user data.');
    }

    return true;
}


function jpc_check_all_steps_complete($user_id = null, $curriculum_id = null) {
    global $wpdb, $user_id;

    // Get the user ID if not set
    if (is_null($user_id)) {
        $user_id = get_current_user_id();
        if ($user_id === 0) {
            return new WP_Error('invalid_user', 'User is not logged in.');
        }
    }

    // Get the curriculum ID from GET parameters if not set
    if (is_null($curriculum_id)) {
        $curriculum_id = isset($_GET['curriculum_id']) ? intval($_GET['curriculum_id']) : null;
        if (is_null($curriculum_id)) {
            return new WP_Error('invalid_curriculum', 'Curriculum ID is not set.');
        }
    }

    // SQL query to check if all steps are not null
    $query = $wpdb->prepare("
        SELECT *
        FROM jpc_student_progress
        WHERE user_id = %d
        AND curriculum_id = %d
        AND step_1 IS NOT NULL
        AND step_2 IS NOT NULL
        AND step_3 IS NOT NULL
        AND step_4 IS NOT NULL
        AND step_5 IS NOT NULL
        AND step_6 IS NOT NULL
        AND step_7 IS NOT NULL
        AND step_8 IS NOT NULL
        AND step_9 IS NOT NULL
        AND step_10 IS NOT NULL
        AND step_11 IS NOT NULL
        AND step_12 IS NOT NULL
    ", $user_id, $curriculum_id);

    $result = $wpdb->get_row($query);
    // If result is not empty, all steps are complete
    return $result !== null;
}

function je_reset_step($step_id = 0) {
	global $wpdb, $user_id;
	$step_id = intval($step_id);
	if ($step_id < 1 || empty($step_id)) { return; }
	$wpdb->delete( 'je_practice_curriculum_progress', array( 'step_id' => $step_id, 'user_id' => $user_id) );
	return;
}

function je_return_step_date_completed($step_id = 0) {
	global $wpdb, $user_id;
	$step_id = intval($step_id);
	if ($step_id < 1) { return; }
	$date_completed = $wpdb->get_var( "SELECT date_completed FROM je_practice_curriculum_progress WHERE step_id = $step_id AND user_id = $user_id " );
	return (empty($date_completed)) ? "" : $date_completed ;
}

function je_return_free_focuses_complete() {
	global $wpdb, $user_id;
	$complete = FALSE;
	$jpc = $wpdb->get_var( "SELECT step_id_3 FROM je_practice_curriculum_assignments WHERE user_id = $user_id ORDER BY ID DESC" );	
	return ($jpc >= 60) ? TRUE : FALSE;
}

function je_return_three_focuses_complete() {
	global $wpdb, $user_id;
	$complete = FALSE;
	$jpc = $wpdb->get_row( "SELECT * FROM je_practice_curriculum_assignments WHERE user_id = $user_id ORDER BY ID DESC" );	
	if (!empty(je_return_step_date_completed($jpc->step_id_1))) { $complete_1 = TRUE; }
	if (!empty(je_return_step_date_completed($jpc->step_id_2))) { $complete_2 = TRUE; }
	if (!empty(je_return_step_date_completed($jpc->step_id_3))) { $complete_3 = TRUE; }
	/* taking this out to allow all students to go as far as they want
	if (je_return_membership_level('numeric') < 2 && je_return_free_focuses_complete() == 60) {
		if (je_return_membership_level() == 'ACADEMY_ACADEMY_NC') { return TRUE; }
		return FALSE;
	}
	*/
	return ($complete_1 == TRUE && $complete_2 == TRUE && $complete_3 == TRUE) ? TRUE : FALSE;
}

function jpc_return_foundational_lesson_ids($return = 'test') {
	global $wpdb, $user_id;
	$foundational_lesson_ids = $wpdb->get_results( "SELECT lesson_id_1,lesson_id_2 FROM je_practice_curriculum_fundational WHERE user_id = $user_id " );
	if ($return == 'test') {
		return (empty($foundational_lesson_ids)) ? FALSE : TRUE;
	} elseif ($return == 'fundational_1') {
		foreach($foundational_lesson_ids AS $r) {
			if ($r->lesson_id_1 > 0) { return TRUE; }
		}
		return FALSE;
	} elseif ($return == 'fundational_2') {
		foreach($foundational_lesson_ids AS $r) {
			if ($r->lesson_id_2 > 0) { return TRUE; }
		}
		return FALSE;
	} else {
		return $foundational_lesson_ids;
	}
}

function jpc_return_fundational_ids($return = 'test') {
	global $wpdb, $user_id;
	$q = $wpdb->get_row( "SELECT * FROM je_practice_curriculum_fundational WHERE user_id = $user_id " );
	if ($return == 'has_fundational_1') {
		if ($q->lesson_id_1 > 0 || !empty($q->custom_lesson_title_1)) { return TRUE; }
		else { return FALSE; }
	} elseif ($return == 'fundational_1') {
		foreach($foundational_lesson_ids AS $r) {
			if ($r->lesson_id_1 > 0) { return TRUE; }
		}
		return FALSE;
	} elseif ($return == 'fundational_2') {
		foreach($foundational_lesson_ids AS $r) {
			if ($r->lesson_id_2 > 0) { return TRUE; }
		}
		return FALSE;
	} else {
		return $foundational_lesson_ids;
	}
}

function jpc_return_focuses_complete($curriculum_id = 0) {
	global $wpdb, $user_id;
	$curriculum_id = ($curriculum_id > 0) ? $curriculum_id : intval($_GET['curriculum_id']);
	$focuses_completed = $wpdb->get_var( "SELECT COUNT(ID) FROM je_practice_curriculum_progress WHERE user_id = $user_id AND curriculum_id = $curriculum_id " );
	return ($focuses_completed == 12) ? TRUE : FALSE;
}

function jep_return_lesson_favorites(){
	global $wpdb, $user_id;
	$favorites = $wpdb->get_results( "SELECT course_or_lesson_id FROM academy_favorites WHERE user_id = $user_id AND type = 'lesson' ORDER BY datetime DESC" ); 
	$fav_array = array();
	foreach ($favorites AS $favorite) {
		$fav_array[] .= $favorite->course_or_lesson_id;
	}
	return $fav_array;
}

function jep_return_course_favorites(){
	global $wpdb, $user_id;
	$favorites = $wpdb->get_results( "SELECT course_or_lesson_id FROM academy_favorites WHERE user_id = $user_id AND type = 'course' ORDER BY datetime DESC" ); 
	$fav_array = array();
	foreach ($favorites AS $favorite) {
		$fav_array[] .= $favorite->course_or_lesson_id;
	}
	return $fav_array;
}

function jep_return_path_favorites(){
	global $wpdb, $user_id;
	$favorites = $wpdb->get_results( "SELECT course_or_lesson_id FROM academy_favorites WHERE user_id = $user_id AND type = 'path' ORDER BY datetime DESC" ); 
	$fav_array = array();
	foreach ($favorites AS $favorite) {
		$fav_array[] .= $favorite->course_or_lesson_id;
	}
	return $fav_array;
}



function key_sig($key) {
	switch ($key) {
    case 1:
        return 'C';
        break;
    case 2:
        return 'D flat';
        break;
    case 3:
        return 'D';
        break;
    case 4:
        return 'E flat';
        break;
    case 5:
        return 'E';
        break;
    case 6:
        return 'F';
        break;
    case 7:
        return 'F sharp';
        break;
    case 8:
        return 'G';
        break;
    case 9:
        return 'A flat';
        break;
    case 10:
        return 'A';
        break;
    case 11:
        return 'B flat';
        break;
    case 12:
        return 'B';
        break;                                            
	case 13:
        return 'ALL 12';
        break; 
    case 14:
        return 'N/A';
        break;   
	}
}

function ja_return_bundle_sample_vimeo_id() {
	$post_id = get_the_ID();
	return $post_id;
}

function key_sig_select($k,$num,$focus_id,$user_id) {
	$focus_id = intval($focus_id);
	$user_id = intval($user_id);
	$keys_completed = je_return_keys_complete_for_focus_id($user_id,$focus_id);
	$return = '<select name="focus_'.$num.'_key">';
	$keys = array (
		1 => 'C',
		2 => 'D flat',
		3 => 'D',
		4 => 'E flat',
		5 => 'E',
		6 => 'F',
		7 => 'F sharp',
		8 => 'G',
		9 => 'A flat',
		10 => 'A',
		11 => 'B flat',
		12 => 'B',
		13 => 'ALL',
		14 => 'N/A'
	);
	foreach ($keys AS $key => $key_name) {
		$complete = (in_array($key,$keys_completed)) ? '**' : '';
		if ($key == $k) { 
			$return .= "<option value='$key' selected='selected'>$complete $key_name</option>";
		} else {
			$return .= "<option value='$key'>$complete $key_name</option>";
		}
	}
	$return .= '</select>';
	return $return;

}



function ja_jpc_status($step_id) { 
	global $wpdb, $user_id;
	$complete = $wpdb->get_var( "SELECT ID FROM je_practice_curriculum_progress WHERE step_id = ".intval($step_id) ." AND user_id = $user_id " );
		if (!empty($complete)) {
			return 'complete';
		} else {
			return 'incomplete'; 
		}
}

function ja_jpc_step_id($curriculum_id,$key_sig) { 
	global $wpdb;
	$step_id = $wpdb->get_var( "SELECT step_id FROM je_practice_curriculum_steps WHERE curriculum_id = ".intval($curriculum_id) ." AND key_sig = ".intval($key_sig)." " );
	return $step_id;
}

function ja_milestone_submitted($curriculum_id = 0){
	global $wpdb, $user_id;
	if ($curriculum_id == 0) { return; }
	$submission_date = $wpdb->get_var( "SELECT submission_date FROM je_practice_milestone_submissions WHERE user_id = $user_id AND curriculum_id = $curriculum_id ORDER BY submission_date DESC" ); 
	return (!empty($submission_date)) ? '<strong>'.date('M. dS, \'y',strtotime($submission_date)).'</strong>': '';
}

function ja_milestone_graded($curriculum_id = 0) {
    global $wpdb, $user_id, $pass_or_redo;

    if ($curriculum_id == 0) {
        return;
    }

    $q = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM je_practice_milestone_submissions WHERE user_id = %d AND curriculum_id = %d AND grade IS NOT NULL ORDER BY submission_date DESC",
        $user_id, $curriculum_id
    ));

    if ($q) {
        $pass_or_redo = $q->grade;
        $grade_color = ($q->grade == 'pass') ? 'green' : 'red';
        $grade = "<p>Grade: <strong style='color:$grade_color;'>$q->grade</strong>";
        if (empty($q->teacher_notes)) {
            $grade .= '</p>';
        } else {
            $grade .= "<div class='teacher_note'><strong>Teacher note</strong>: " . stripslashes($q->teacher_notes) . "</div></p>";
        }
        return $grade;
    }

    return '';
}

function ja_milestone_submission_video_url($curriculum_id = 0){
	global $wpdb, $user_id;
	if ($curriculum_id == 0) { return; }
	$video_url = $wpdb->get_var( "SELECT video_url FROM je_practice_milestone_submissions WHERE user_id = $user_id AND curriculum_id = $curriculum_id" );
	return (!empty($video_url)) ? "<a href='$video_url' target='_blank'><i class='fa-sharp fa-solid fa-arrow-up-right-from-square'></i></a>" : '';
}

function ja_get_class_permalink($string) {
    // Extract numbers from the string
    $numbers = preg_replace('/\D/', '', $string); // Remove all non-digit characters
    $is_only_numbers = ($string === $numbers);

    // Determine the permalink based on the string content
    if ($is_only_numbers) {
        return "https://jazzedge.academy/event-replay/?id=" . $numbers;
    } else {
        $post_id = intval($numbers);
        return get_the_permalink($post_id);
    }
}

function ja_is_new_class($event_id) {
    // Check if $event_id has only numbers
    if (ctype_digit($event_id)) {
        return false; // $event_id has only numbers
    } else {
        return true; // $event_id has non-numeric characters
    }
}

function ja_class_id($string) {
    // Check if the string is in the format 'newclass_1234'
    if (preg_match('/^newclass_(\d+)$/', $string, $matches)) {
        return $matches[1]; // Return the extracted number
    }
    return null; // Return null if the string is not in the correct format
}

/* END JEP FUNCTIONS */

function leaderboard_blueprint_quiz() {

/*
SELECT user_id, MAX(score) AS highest_score, COUNT(*) AS quiz_count
FROM academy_blueprint_quiz_results
GROUP BY user_id
HAVING highest_score = (SELECT MAX(score) FROM academy_blueprint_quiz_results)
ORDER BY quiz_count DESC
LIMIT 1;

*/
}


function check_popup_preference($user_id, $popup_name) {
    global $wpdb;
    $table_name = 'academy_popup_prefs';  // No prefix

    // Log the table name and query for debugging
    //error_log('Table Name: ' . $table_name);
    $query = $wpdb->prepare(
        "SELECT preference FROM $table_name WHERE user_id = %d AND popup_name = %s",
        $user_id, $popup_name
    );
    //error_log('Query: ' . $query);

    $preference = $wpdb->get_var($query);

    // Log the raw result from the database
    //error_log('Raw Preference from DB: ' . var_export($preference, true));

    return $preference === '1';
}




function update_popup_preference($user_id, $popup_name, $preference) {
    global $wpdb;
    $table_name = 'academy_popup_prefs';
    
    $existing_preference = $wpdb->get_var($wpdb->prepare(
        "SELECT preference FROM $table_name WHERE user_id = %d AND popup_name = %s",
        $user_id, $popup_name
    ));
    
    if ($existing_preference !== null) {
        $wpdb->update(
            $table_name,
            ['preference' => $preference],
            ['user_id' => $user_id, 'popup_name' => $popup_name],
            ['%d'],
            ['%d', '%s']
        );
    } else {
        $wpdb->insert(
            $table_name,
            ['user_id' => $user_id, 'popup_name' => $popup_name, 'preference' => $preference],
            ['%d', '%s', '%d']
        );
    }
}

add_action('wp_ajax_check_popup_preference', 'check_popup_preference_ajax');
add_action('wp_ajax_nopriv_check_popup_preference', 'check_popup_preference_ajax');

function check_popup_preference_ajax() {
    $user_id = intval($_POST['user_id']);
    $popup_name = sanitize_text_field($_POST['popup_name']);

    $show_popup = !check_popup_preference($user_id, $popup_name);

    // Log details for debugging
    //error_log('User ID: ' . $user_id);
    //error_log('Popup Name: ' . $popup_name);
    //error_log('Show Popup: ' . ($show_popup ? 'true' : 'false'));

    // Send a JSON response with the value of $show_popup
    wp_send_json(['show_popup' => $show_popup, 'debug' => 'Value of $show_popup: ' . ($show_popup ? 'true' : 'false')]);
}



add_action('wp_ajax_update_popup_preference', 'update_popup_preference_ajax');
add_action('wp_ajax_nopriv_update_popup_preference', 'update_popup_preference_ajax');

function update_popup_preference_ajax() {
    $user_id = intval($_POST['user_id']);
    $popup_name = sanitize_text_field($_POST['popup_name']);
    $preference = intval($_POST['preference']);

    update_popup_preference($user_id, $popup_name, $preference);
    wp_send_json_success();
}

//************** THE EVENTS CALENDAR - TRIBE EVENTS ***************//
function check_ec_has_replay() { return; }
/*
function check_ec_has_replay($post_id = null) {
    // If no post ID is passed, use the global post object
    if (empty($post_id)) {
        global $post;
        if (isset($post->ID)) {
            $post_id = $post->ID;
        } else {
            return false; // Return false if no post ID is available
        }
    }

    // Get the values of the updated meta fields
    $vimeo_id = get_post_meta($post_id, 'ec_replay_vimeo_id', true);
    $youtube_url = get_post_meta($post_id, 'ec_youtube_url', true);
    $bunny_url = get_post_meta($post_id, 'ec_bunny_url', true);

    // Check if any of the fields have a value
    if (!empty($vimeo_id) || !empty($youtube_url) || !empty($bunny_url)) {
        return true; // At least one field has data
    }

    return false; // None of the fields have data
}


function get_event_categories( $post_id = null ) {
    // Get the current post ID if not provided
    $post_id = is_null( $post_id ) ? get_the_ID() : $post_id;

    // Get the categories for the event
    $categories = get_the_terms( $post_id, 'tribe_events_cat' );

    // Check if categories are found
    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
        // Return the categories as an array of names
        $category_names = wp_list_pluck( $categories, 'name' );
        return $category_names;
    }

    // Return empty array if no categories are found
    return array();
}

function has_event_category( $category_to_check, $post_id = null ) {
    // Use the function to get event categories
    $categories = get_event_categories( $post_id );

    // Make the search case-insensitive
    $category_to_check = strtolower( $category_to_check );

    // Loop through categories and check if 'studio' or 'premier' exists
    foreach ( $categories as $category ) {
        if ( strtolower( $category ) === $category_to_check ) {
            return true; // Return true if the category is found
        }
    }

    return false; // Return false if the category is not found
}

add_filter( 'facetwp_facet_display_value', function( $value, $params ) {
    if ( $params['facet_name'] == 'ec_membership_level' ) {
        return ucfirst( $value ); // Capitalize the first letter of the membership level
    }
    return $value;
}, 10, 2 );

add_filter( 'tribe_ical_feed_calname', function ( $name ) { 
  return 'Jazzedge Academy Events'; 
} );
*/


function display_keap_response() {
    global $wpdb;

    // Get the 'code' parameter from the URL
    $code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : null;

    // Get the 'fn' (first name) parameter from the URL
    $first_name = isset($_GET['fn']) ? sanitize_text_field($_GET['fn']) : null;

    // If there's no code in the URL, return early
    if (!$code) {
        return '<p>No response found. Please check your link.</p>';
    }

    // Query the database for the response with the given code
    $response = $wpdb->get_var($wpdb->prepare(
        "SELECT response FROM keap_email_responses WHERE code = %s",
        $code
    ));

    // If no response was found, return an error message
    if (!$response) {
        return '<p>There was an error. Please try your link again.</p>';
    }

    // Replace {{name}} with the first name, if provided, and capitalize the first letter
    if ($first_name) {
        $response = str_replace('{{fname}}', esc_html(ucfirst($first_name)), $response);
    } else {
        // Remove {{name}} if no first name is provided
        $response = str_replace('{{fname}}', '', $response);
    }

    // Return the final response
    $response = wp_kses($response, wp_kses_allowed_html('post'));
    return $response;
}

function display_fluent_form_based_on_fid() {
    global $wpdb;

    $output = null;

    // Define the allowed list of form IDs
    $allowed_ids = array(35,36); // Add the allowed Fluent Form IDs here

    // Get the 'code' parameter from the URL
    $code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : null;

    // Query the database to get the response and fluentform_id based on the 'code'
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT fluentform_id FROM keap_email_responses WHERE code = %s",
        $code
    ));

    // If there's no 'code' parameter in the URL or the form ID is not in the allowed list, return
    if (!$result || !in_array(intval($result->fluentform_id), $allowed_ids)) {
        return;
    }

    // Display the Fluent Form using the shortcode if fluentform_id is in the allowed list
    $output .= do_shortcode('[fluentform id="' . intval($result->fluentform_id) . '"]');

    return $output;
}


function _____METRICS_____() {return; }
//************** METRICS ***************//
function update_ja_metrics_users_on_login($user_login, $user) {
    global $wpdb;

    // Get the user ID
    $user_id = $user->ID;
    
    // Get the user's email and display name
    $email = $user->user_email;
    $name = $user->display_name;

    // Get the current date for last login
    $last_login_date = current_time('mysql');

    // Get the membership level using the je_return_membership_level function with 'nicename' return
    $membership_level = je_return_membership_level('nicename');

    // Check if the user already exists in the ja_metrics_users table
    $user_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM jam_users WHERE user_id = %d", $user_id));

    if ($user_exists) {
        // Update the user's last login date and membership level
        $wpdb->update(
            'jam_users',
            array(
                'name'             => $name,
                'email'            => $email,
                'membership_level' => $membership_level,
                'last_login_date'  => $last_login_date,
                'updated_at'       => current_time('mysql')
            ),
            array('user_id' => $user_id),
            array('%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
    } else {
        // Insert a new record for the user
        $wpdb->insert(
            'jam_users',
            array(
                'user_id'          => $user_id,
                'name'             => $name,
                'email'            => $email,
                'membership_level' => $membership_level,
                'last_login_date'  => $last_login_date,
                'created_at'       => current_time('mysql'),
                'updated_at'       => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
}

// Hook the function to wp_login action
add_action('wp_login', 'update_ja_metrics_users_on_login', 10, 2);

function populate_ja_metrics_session_records($user_id, $session_type, $session_title) {
    global $wpdb;

    // Get the current date
    $session_date = current_time('mysql');

    // Insert session data into the ja_metrics_session_records table
    $wpdb->insert(
        'ja_metrics_session_records',
        array(
            'user_id'       => $user_id,
            'session_type'  => $session_type,
            'session_title' => $session_title,
            'session_date'  => $session_date,
            'created_at'    => $session_date,
            'updated_at'    => $session_date
        ),
        array('%d', '%s', '%s', '%s', '%s', '%s')
    );
}

function update_ja_metrics_student_metrics($user_id, $metric_data) {
    global $wpdb;

    // Check if the student metrics already exist for the user
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM ja_metrics_student_metrics WHERE user_id = %d",
        $user_id
    ));

    // Prepare data for updating
    $data = array(
        'last_login_date' => $metric_data['last_login_date'] ?? NULL,
        'membership_level' => $metric_data['membership_level'] ?? NULL,
        'last_live_class_attended_title' => $metric_data['last_live_class_attended_title'] ?? NULL,
        'last_live_class_attended_date' => $metric_data['last_live_class_attended_date'] ?? NULL,
        'last_coaching_session_attended_title' => $metric_data['last_coaching_session_attended_title'] ?? NULL,
        'last_coaching_session_attended_date' => $metric_data['last_coaching_session_attended_date'] ?? NULL,
        'last_lesson_viewed_title' => $metric_data['last_lesson_viewed_title'] ?? NULL,
        'last_lesson_date_viewed' => $metric_data['last_lesson_date_viewed'] ?? NULL,
        'last_lesson_id_viewed' => $metric_data['last_lesson_id_viewed'] ?? NULL,
        'last_premier_course_viewed_title' => $metric_data['last_premier_course_viewed_title'] ?? NULL,
        'last_premier_course_viewed_date' => $metric_data['last_premier_course_viewed_date'] ?? NULL,
        'last_jpc_focus_completed' => $metric_data['last_jpc_focus_completed'] ?? NULL,
        'last_jpc_focus_completed_date' => $metric_data['last_jpc_focus_completed_date'] ?? NULL,
        'last_blueprint_viewed_title' => $metric_data['last_blueprint_viewed_title'] ?? NULL,
        'last_blueprint_viewed_date' => $metric_data['last_blueprint_viewed_date'] ?? NULL,
        'last_practice_logged' => $metric_data['last_practice_logged'] ?? NULL,
        'minutes_practiced_last_7_days' => $metric_data['minutes_practiced_last_7_days'] ?? NULL,
        'minutes_practiced_last_30_days' => $metric_data['minutes_practiced_last_30_days'] ?? NULL
    );

    if ($exists) {
        // Update existing metrics
        $wpdb->update(
            'ja_metrics_student_metrics',
            $data,
            array('user_id' => $user_id),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%d', '%d'),
            array('%d')
        );
    } else {
        // Insert new metrics
        $data['user_id'] = $user_id; // Add user_id for insert
        $wpdb->insert(
            'ja_metrics_student_metrics',
            $data,
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%d', '%d')
        );
    }
}
/*
function update_last_login_date_and_membership_level($user_login, $user) {
    global $wpdb;

    // Get the user ID and email
    $user_id = $user->ID;
    $user_email = !empty($user->user_email) ? $user->user_email : get_userdata($user_id)->user_email;

    // Ensure user email is fetched correctly
    if (empty($user_email)) {
        error_log("update_last_login_date_and_membership_level - User email is missing for user ID: $user_id");
        $user_email = 'unknown@example.com'; // Default if email is not available
    }

    // Get the current date in Y-m-d format and verify its proper MySQL format
    $last_login_date = date('Y-m-d', current_time('timestamp'));

    // Get the user's membership level
    $membership_level = je_return_membership_level('nicename'); 

    // Check if membership level is correctly fetched
    if (empty($membership_level)) {
        error_log("update_last_login_date_and_membership_level - Membership level is missing for user ID: $user_id");
        $membership_level = 'Free'; // Set default if membership level is not found
    }

    // Check if the user already has a record in ja_analytics_user_data
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM ja_analytics_user_data WHERE user_id = %d",
        $user_id
    ));

    // Prepare data to update the last login date, email, and membership level
    $data = array(
        'last_login_date' => $last_login_date,
        'membership_level' => $membership_level,
        'user_email' => $user_email,
        'updated_at' => current_time('mysql')  // Track when the record is updated
    );

    if ($exists) {
        if (empty($last_login_date) || $last_login_date == '0000-00-00') {
            $last_login_date = date('Y-m-d', current_time('timestamp'));  // Get the correct current date
            $data['last_login_date'] = $last_login_date;
        }

        // Update the existing record with last_login_date, membership_level, and user_email
        $wpdb->update(
            'ja_analytics_user_data',
            $data,
            array('user_id' => $user_id),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );
    } else {
        // Insert new row for the user if they don't already have a record in ja_analytics_user_data
        $data['user_id'] = $user_id;
        $wpdb->insert(
            'ja_analytics_user_data',
            $data,
            array('%d', '%s', '%s', '%s', '%s')
        );
    }
}

// Hook the function into the wp_login action
add_action('wp_login', 'update_last_login_date_and_membership_level', 10, 2);
*/

//**************** V2 ANALYTICS

function ja_analytics_track_video_view($user_id, $post_id, $video_name, $type) {
    global $wpdb;

    // If the user is not logged in, track with IP address in the free table
    if (empty($user_id) || $user_id < 1) {
        $ip_address = return_user_ip();
        $data = array(    
            'video_name' => $video_name, 
            'post_id' => $post_id,
            'type' => $type,
            'datetime' => date('Y-m-d H:i:s', strtotime('-4 HOURS')),
            'ip' => $ip_address,
        );
        $format = array('%s','%d','%s','%s','%s');
        $wpdb->insert('ja_analytics_video_tracking_free', $data, $format);
        return;
    }

    // Check if video tracking already exists for today
    $found = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM jam_video_tracking WHERE date(datetime) = CURDATE() AND user_id = %d AND video_name = %s",
        $user_id, $video_name
    ));

    // If no record found, insert a new tracking record
    if (empty($found)) {
        $data = array(    
            'video_name' => $video_name, 
            'user_id' => $user_id,
            'post_id' => $post_id,
            'type' => $type,
            'datetime' => date('Y-m-d H:i:s', strtotime('-4 HOURS')),
        );
        $format = array('%s','%d','%d','%s','%s');
        $wpdb->insert('jam_video_tracking', $data, $format);
    }

    // Insert session data into ja_analytics_session_records
	$session_data = array(
		'user_id'       => $user_id,
		'session_type'  => $type,  // Directly use the video type as the session type
		'session_title' => sanitize_text_field($video_name),
		'session_date'  => current_time('mysql'),  // Uses WordPress timezone settings
		'created_at'    => current_time('mysql'),
		'updated_at'    => current_time('mysql')
	);
	
	$session_format = array('%d', '%s', '%s', '%s', '%s', '%s');
	$wpdb->insert('jam_session_records', $session_data, $session_format);

    // Update user analytics based on the video type
    ja_analytics_update_user_data($user_id, $type, $post_id, $video_name);
    echo "// called ja_analytics_update_user_data ($user_id, $type, $post_id, $video_name)";
}


function update_last_jpc_focus_completed($user_id) {
    global $wpdb;

    // Prepare the data (only the last completed date)
    $data = array(
        'last_jpc_completed_date' => date('Y-m-d')  // Use current date in Y-m-d format
    );

    // Check if the user already exists in jam_student_metrics
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM jam_student_metrics WHERE user_id = %d",
        $user_id
    ));

    // Update or insert the data
    if ($exists) {
        // Update the existing record
        $wpdb->update(
            'jam_student_metrics',
            $data,
            array('user_id' => $user_id),
            array('%s'),
            array('%d')
        );
    } else {
        // Insert a new record if it doesn't exist
        $data['user_id'] = $user_id;
        $wpdb->insert(
            'jam_student_metrics',
            $data,
            array('%d', '%s')
        );
    }
}


function ja_analytics_update_user_data($user_id, $type, $post_id, $video_name) {
    global $wpdb;

    $data = array();
    $date_viewed = date('Y-m-d', current_time('timestamp')); // Current date in Y-m-d format

    // Switch case for type to handle different cases more effectively
    switch ($type) {
        case 'lesson':
            $data = array(
                'last_lesson_id' => $post_id,
                'last_lesson_title' => $video_name,
                'last_lesson_date' => $date_viewed
            );
            break;

        case 'studio_class_replay':
            $data = array(
                'last_studio_class_id' => $post_id,
                'last_studio_class_title' => $video_name,
                'last_studio_class_date' => $date_viewed
            );
            break;
        
        case 'premier_course_class_replay':
			$data = array(
				'last_premier_course_id' => $post_id, 
				'last_premier_course_title' => $video_name, 
				'last_premier_course_date' => $date_viewed 
			);
			break;


        case 'jpc_replay':
            $data = array(
                'last_jpc_step_id' => $post_id,
                'last_jpc_curriculum_id' => $video_name,
                'last_jpc_date' => $date_viewed
            );
            break;

        case 'blueprint':
            $data = array(
                'last_blueprint_id' => $post_id,
                'last_blueprint_title' => $video_name,
                'last_blueprint_date' => $date_viewed
            );
            break;

        case 'live_event_checkin':
            $data = array(
                'last_live_event_id' => $post_id,
                'last_live_event_title' => $video_name,
                'last_live_event_date' => $date_viewed
            );
            break;

		case 'premier_class':
			$data = array(
				'last_premier_class_id' => $post_id,
				'last_premier_class_title' => $video_name,
				'last_premier_class_date' => $date_viewed
			);
			break;

        case 'coaching_checkin':
            $data = array(
                'last_coaching_checkin_id' => $post_id,
                'last_coaching_checkin_title' => $video_name,
                'last_coaching_checkin_date' => $date_viewed
            );
            break;

        case 'coaching_replay':
            $data = array(
                'last_coaching_replay_id' => $post_id,
                'last_coaching_replay_title' => $video_name,
                'last_coaching_replay_date' => $date_viewed
            );
            break;

        case 'quick_tip':
            $data = array(
                'last_quick_tip_id' => $post_id,
                'last_quick_tip_title' => $video_name,
                'last_quick_tip_date' => $date_viewed
            );
            break;

        case 'mini_lesson':
            $data = array(
                'last_mini_lesson_id' => $post_id,
                'last_mini_lesson_title' => $video_name,
                'last_mini_lesson_date' => $date_viewed
            );
            break;

       default:
			error_log("Unexpected type '$type' passed to jam_student_metrics function in oxygen-functions.php");
			return;

    }

    // Update or insert data in the ja_analytics_user_data table
    if (!empty($data)) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM jam_student_metrics WHERE user_id = %d",
            $user_id
        ));

        if ($exists) {
            $wpdb->update(
                'jam_student_metrics',
                $data,
                array('user_id' => $user_id),
                array_map(function($v) { return is_int($v) ? '%d' : '%s'; }, $data),
                array('%d')
            );
        } else {
            $data['user_id'] = $user_id;
            $wpdb->insert(
                'jam_student_metrics',
                $data,
                array_merge(array('%d'), array_map(function($v) { return is_int($v) ? '%d' : '%s'; }, $data))
            );
        }
    }
}

function mark_osop_emailed_now_book($email) {
    // Find the user by email
    $user = get_user_by('email', $email);

    // Check if user exists
    if ($user) {
        $user_id = $user->ID;

        // Update the osop_emailed_need_to_book meta to "yes"
        update_user_meta($user_id, 'osop_emailed_need_to_book', 'yes');

        return "User OSOP EMAILED meta updated successfully.";
    } else {
        return "User not found.";
    }
}

function check_user_osop_status() {
    global $wpdb, $user_id;

    // If $user_id is not set, get it from WordPress
    if (empty($user_id)) {
        $user_id = get_current_user_id();
        
        // If no user is logged in, return 'none'
        if (empty($user_id)) {
            return 'none';
        }
    }

    // Check if the user exists in the academy_osop_emailed table
    $user_osop_emailed = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM academy_osop_emailed WHERE user_id = %d", 
            $user_id
        )
    );


    // Check user meta for osop_committed and osop_ready
    $osop_committed = get_user_meta($user_id, 'osop_committed', true);
    $osop_booked = get_user_meta($user_id, 'osop_booked', true);
    $osop_ready = get_user_meta($user_id, 'osop_ready', true);
    $osop_emailed_need_to_book = get_user_meta($user_id, 'osop_emailed_need_to_book', true);

    // If osop_ready is 'yes', return 'ready'
    if ($osop_committed === 'yes' && $osop_booked === 'yes' && $osop_ready === 'yes') {
        return 'ready';
    }
    
    // If user is not in the academy_osop_emailed table, return 'none'
    if ($osop_emailed_need_to_book === 'yes' && $osop_booked != 'yes') {
        return 'must_commit';
    }
    
    if ($osop_booked === 'yes' && $osop_ready != 'yes') {
        return 'booked';
    }
    
    // If osop_committed is 'yes', return 'committed'
    if ($osop_committed === 'yes' && $osop_ready != 'yes') {
        return 'committed';
    }


    // If neither osop_committed nor osop_ready is set, return 'none'
    return 'none';
}

function osop_add_academy_credit($user_id) {
    global $wpdb;

    // Validate the passed parameter
    if (empty($user_id)) {
        return;
    }

    // Define the table name (no prefix)
    $table_name = 'academy_user_credits';

    // Check if the user exists in the table
    $user = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", intval($user_id))
    );

    if (!$user) {
        return;
    }

    // Increment class_credits by 1
    $updated = $wpdb->query(
        $wpdb->prepare(
            "UPDATE $table_name SET class_credits = class_credits + 1 WHERE user_id = %d",
            intval($user_id)
        )
    );

    if ($updated === false) {
        return;
    }
}
function pc_return_class_ready($release_date) {
    // Convert the input release date to a timestamp
    $release_timestamp = strtotime($release_date) + (5 * 60 * 60);

    // Ensure the date is valid
    if ($release_timestamp === false) {
        return false; // Invalid date format
    }

    // Get the current timestamp
    $current_timestamp = time();

    // Compare the release date with the current date
    if ($release_timestamp <= $current_timestamp) {
    	return TRUE;
    } else { return FALSE; }
}


function search_lessons_api( WP_REST_Request $request ) {
    $search_query = sanitize_text_field( $request->get_param('query') );

    $args = array(
        'post_type'      => 'lesson', // Change this if your lessons are stored differently
        'posts_per_page' => 10,
        's'              => $search_query,
    );

    $query = new WP_Query( $args );

    $results = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $results[] = array(
                'id'    => get_the_ID(),
                'title' => get_the_title(),
                'url'   => get_permalink(),
                'excerpt' => get_the_excerpt(),
            );
        }
        wp_reset_postdata();
    }

    return rest_ensure_response( $results );
}

function register_search_lessons_route() {
    register_rest_route( 'custom/v1', '/search-lessons', array(
        'methods'  => 'GET',
        'callback' => 'search_lessons_api',
        'args'     => array(
            'query' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
        'permission_callback' => '__return_true',
    ));
}

add_action( 'rest_api_init', 'register_search_lessons_route' );

?>
<?php
/**
 * Clean Piano Lesson Meta Box - No Oxygen Conflicts
 * 
 * Simplified version that won't interfere with Oxygen Builder
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Piano Lesson meta box - Clean version
 */
add_action('add_meta_boxes', 'piano_add_simple_meta_box');
function piano_add_simple_meta_box() {
    add_meta_box(
        'piano_lesson_simple',
        '🎹 Piano Lesson Database ID',
        'piano_simple_meta_box_callback',
        'lessons',
        'side',
        'high'
    );
}

/**
 * Simple meta box callback - No complex JS/CSS
 */
function piano_simple_meta_box_callback($post) {
    wp_nonce_field('piano_lesson_meta_box', 'piano_lesson_meta_nonce');
    
    $piano_lesson_id = get_post_meta($post->ID, 'piano_lesson_id', true);
    $lesson_data = null;
    
    if ($piano_lesson_id) {
        global $wpdb;
        $lesson_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}piano_lessons WHERE id = %d",
            $piano_lesson_id
        ));
    }
    
    echo '<table class="form-table"><tbody>';
    echo '<tr>';
    echo '<th><label for="piano_lesson_id">Database Lesson ID:</label></th>';
    echo '<td>';
    echo '<input type="number" id="piano_lesson_id" name="piano_lesson_id" value="' . esc_attr($piano_lesson_id) . '" class="small-text" min="1" />';
    echo '<p class="description">Enter the ID from the piano_lessons database table.</p>';
    echo '</td>';
    echo '</tr>';
    
    if ($lesson_data) {
        echo '<tr>';
        echo '<th>Status:</th>';
        echo '<td>';
        echo '<p style="color: #0073aa;"><strong>✅ Connected</strong></p>';
        echo '<p><strong>Title:</strong> ' . esc_html($lesson_data->title) . '</p>';
        echo '<p><strong>Access:</strong> ' . esc_html(ucfirst($lesson_data->access_level)) . '</p>';
        echo '</td>';
        echo '</tr>';
    } elseif ($piano_lesson_id) {
        echo '<tr>';
        echo '<th>Status:</th>';
        echo '<td>';
        echo '<p style="color: #d63384;"><strong>⚠️ Not Found</strong></p>';
        echo '<p>Lesson ID ' . esc_html($piano_lesson_id) . ' not found in database.</p>';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
}

/**
 * Save meta box data - Simple version
 */
add_action('save_post', 'piano_save_simple_meta_box');
function piano_save_simple_meta_box($post_id) {
    if (!isset($_POST['piano_lesson_meta_nonce']) || 
        !wp_verify_nonce($_POST['piano_lesson_meta_nonce'], 'piano_lesson_meta_box')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (get_post_type($post_id) !== 'lessons') {
        return;
    }
    
    if (isset($_POST['piano_lesson_id'])) {
        $lesson_id = sanitize_text_field($_POST['piano_lesson_id']);
        
        if (empty($lesson_id)) {
            delete_post_meta($post_id, 'piano_lesson_id');
        } else {
            update_post_meta($post_id, 'piano_lesson_id', $lesson_id);
        }
    }
}

/**
 * Add simple admin column
 */
add_filter('manage_lessons_posts_columns', 'piano_add_simple_column');
function piano_add_simple_column($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['piano_lesson_id'] = 'DB ID';
        }
    }
    
    return $new_columns;
}

add_action('manage_lessons_posts_custom_column', 'piano_show_simple_column', 10, 2);
function piano_show_simple_column($column, $post_id) {
    if ($column === 'piano_lesson_id') {
        $lesson_id = get_post_meta($post_id, 'piano_lesson_id', true);
        
        if ($lesson_id) {
            global $wpdb;
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}piano_lessons WHERE id = %d",
                $lesson_id
            ));
            
            if ($exists) {
                echo '<strong style="color: #0073aa;">' . esc_html($lesson_id) . '</strong> ✅';
            } else {
                echo '<strong style="color: #d63384;">' . esc_html($lesson_id) . '</strong> ❌';
            }
        } else {
            echo '—';
        }
    }
}

/**
 * JazzEdge Academy Membership Parser Functions
 * Add these functions to your JazzEdge Academy functions.php file
 */

/**
 * Configuration - Update these values
 */
define('JAZZEDGE_MAIN_SITE_URL', 'https://jazzedge.com');
define('JAZZEDGE_API_KEY', 'je_api_2024_K9m7nQ8vL3xR6tY2wE9rP5sA1dF4hJ7k'); // Updated API key

/**
 * Get membership data from main JazzEdge site
 * 
 * @param string $email The user's email address
 * @return array|false Returns membership data array or false on failure
 */
function get_jazzedge_membership_data($email) {
    if (!is_email($email)) {
        error_log('Invalid email provided to get_jazzedge_membership_data: ' . $email);
        return false;
    }
    
    // Check cache first (cache for 5 minutes)
    $cache_key = 'jazzedge_membership_' . md5($email);
    $cached_data = get_transient($cache_key);
    
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // Build API URL
    $api_url = JAZZEDGE_MAIN_SITE_URL . '/wp-json/jazzedge/v1/membership';
    $api_url = add_query_arg(array(
        'email' => $email,
        'api_key' => JAZZEDGE_API_KEY
    ), $api_url);
    
    // Make API request
    $response = wp_remote_get($api_url, array(
        'timeout' => 15,
        'user-agent' => 'JazzEdge Academy/' . get_bloginfo('version'),
        'headers' => array(
            'Accept' => 'application/json'
        )
    ));
    
    // Check for errors
    if (is_wp_error($response)) {
        error_log('JazzEdge API Error: ' . $response->get_error_message());
        return false;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    if ($response_code !== 200) {
        error_log('JazzEdge API HTTP Error: ' . $response_code . ' - ' . $response_body);
        return false;
    }
    
    $data = json_decode($response_body, true);
    
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JazzEdge API JSON Error: ' . json_last_error_msg());
        return false;
    }
    
    if (!$data['success']) {
        error_log('JazzEdge API Response Error: ' . $data['message']);
        return false;
    }
    
    // Cache the result for 5 minutes
    set_transient($cache_key, $data['data'], 5 * MINUTE_IN_SECONDS);
    
    return $data['data'];
}

/**
 * Check if user has active membership
 * 
 * @param string $email The user's email address
 * @return bool True if user has active membership
 */
function user_has_active_jazzedge_membership($email) {
    $membership_data = get_jazzedge_membership_data($email);
    
    if (!$membership_data) {
        return false;
    }
    
    // Check for active memberships
    if (!empty($membership_data['memberships'])) {
        foreach ($membership_data['memberships'] as $membership) {
            if ($membership['status'] === 'active') {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Get user's active membership products
 * 
 * @param string $email The user's email address
 * @return array Array of active membership products
 */
function get_user_active_memberships($email) {
    $membership_data = get_jazzedge_membership_data($email);
    $active_memberships = array();
    
    if (!$membership_data) {
        return $active_memberships;
    }
    
    if (!empty($membership_data['memberships'])) {
        foreach ($membership_data['memberships'] as $membership) {
            if ($membership['status'] === 'active') {
                $active_memberships[] = $membership;
            }
        }
    }
    
    return $active_memberships;
}

/**
 * Get user's total spending on main site
 * 
 * @param string $email The user's email address
 * @return float Total amount spent
 */
function get_user_total_spent($email) {
    $membership_data = get_jazzedge_membership_data($email);
    
    if (!$membership_data) {
        return 0.00;
    }
    
    $total_spent = 0.00;
    
    // From WooCommerce data
    if (isset($membership_data['woocommerce']['total_spent'])) {
        $total_spent += floatval($membership_data['woocommerce']['total_spent']);
    }
    
    // From MemberPress transactions
    if (!empty($membership_data['transactions'])) {
        foreach ($membership_data['transactions'] as $transaction) {
            if ($transaction['status'] === 'complete') {
                $total_spent += floatval($transaction['amount']);
            }
        }
    }
    
    return $total_spent;
}

/**
 * Check if user has specific membership product
 * 
 * @param string $email The user's email address
 * @param string|int $product_id_or_name Product ID or name to check
 * @return bool True if user has the specified membership
 */
function user_has_membership_product($email, $product_id_or_name) {
    $membership_data = get_jazzedge_membership_data($email);
    
    if (!$membership_data) {
        return false;
    }
    
    if (!empty($membership_data['memberships'])) {
        foreach ($membership_data['memberships'] as $membership) {
            if ($membership['status'] === 'active') {
                // Check by product ID
                if (is_numeric($product_id_or_name) && $membership['product_id'] == $product_id_or_name) {
                    return true;
                }
                // Check by product name (case insensitive)
                if (stripos($membership['product_name'], $product_id_or_name) !== false) {
                    return true;
                }
            }
        }
    }
    
    return false;
}

/**
 * Display membership status widget for Fluent Support integration
 * 
 * @param string $email The user's email address
 * @return string HTML output for membership status
 */
function display_membership_status_widget($email) {
    $membership_data = get_jazzedge_membership_data($email);
    
    if (!$membership_data) {
        return '<div class="membership-status error">Unable to retrieve membership data</div>';
    }
    
    $output = '<div class="jazzedge-membership-status">';
    $output .= '<h4>JazzEdge.com Membership Status</h4>';
    
    // User info
    $output .= '<div class="user-info">';
    $output .= '<p><strong>Name:</strong> ' . esc_html($membership_data['first_name'] . ' ' . $membership_data['last_name']) . '</p>';
    $output .= '<p><strong>Member since:</strong> ' . date('M j, Y', strtotime($membership_data['registration_date'])) . '</p>';
    
    if (!empty($membership_data['last_login'])) {
        $output .= '<p><strong>Last login:</strong> ' . date('M j, Y g:i A', strtotime($membership_data['last_login'])) . '</p>';
    }
    $output .= '</div>';
    
    // Active memberships
    if (!empty($membership_data['memberships'])) {
        $active_memberships = array_filter($membership_data['memberships'], function($m) {
            return $m['status'] === 'active';
        });
        
        if (!empty($active_memberships)) {
            $output .= '<div class="active-memberships">';
            $output .= '<h5>Active Memberships:</h5>';
            $output .= '<ul>';
            foreach ($active_memberships as $membership) {
                $output .= '<li>';
                $output .= '<strong>' . esc_html($membership['product_name']) . '</strong>';
                if (!empty($membership['expires_at']) && $membership['expires_at'] !== '0000-00-00 00:00:00') {
                    $output .= ' (expires: ' . date('M j, Y', strtotime($membership['expires_at'])) . ')';
                }
                $output .= '</li>';
            }
            $output .= '</ul>';
            $output .= '</div>';
        }
    }
    
    // Recent transactions
    if (!empty($membership_data['transactions'])) {
        $recent_transactions = array_slice($membership_data['transactions'], 0, 3);
        $output .= '<div class="recent-transactions">';
        $output .= '<h5>Recent Transactions:</h5>';
        $output .= '<ul>';
        foreach ($recent_transactions as $transaction) {
            $output .= '<li>';
            $output .= esc_html($transaction['product_name']) . ' - $' . number_format($transaction['amount'], 2);
            $output .= ' (' . ucfirst($transaction['status']) . ')';
            $output .= ' - ' . date('M j, Y', strtotime($transaction['created_at']));
            $output .= '</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';
    }
    
    // Total spent
    $total_spent = get_user_total_spent($email);
    if ($total_spent > 0) {
        $output .= '<div class="total-spent">';
        $output .= '<p><strong>Total spent:</strong> $' . number_format($total_spent, 2) . '</p>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    // Add some basic styling
    $output .= '<style>
        .jazzedge-membership-status {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
            font-size: 14px;
        }
        .jazzedge-membership-status h4 {
            margin-top: 0;
            color: #004555;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }
        .jazzedge-membership-status h5 {
            color: #333;
            margin: 15px 0 8px 0;
        }
        .jazzedge-membership-status ul {
            margin: 0;
            padding-left: 20px;
        }
        .jazzedge-membership-status li {
            margin-bottom: 5px;
        }
        .membership-status.error {
            color: #d63638;
            background: #fcf0f1;
            border-color: #d63638;
            padding: 10px;
            border-radius: 3px;
        }
    </style>';
    
    return $output;
}

/**
 * Shortcode to display membership status
 * Usage: [jazzedge_membership_status email="user@example.com"]
 */
add_shortcode('jazzedge_membership_status', function($atts) {
    $atts = shortcode_atts(array(
        'email' => ''
    ), $atts);
    
    if (empty($atts['email']) || !is_email($atts['email'])) {
        return '<p>Invalid email address provided.</p>';
    }
    
    return display_membership_status_widget($atts['email']);
});

/**
 * AJAX handler for getting membership data (for admin use)
 */
add_action('wp_ajax_get_jazzedge_membership', function() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $email = sanitize_email($_POST['email']);
    
    if (!is_email($email)) {
        wp_send_json_error('Invalid email address');
        return;
    }
    
    $membership_data = get_jazzedge_membership_data($email);
    
    if ($membership_data) {
        wp_send_json_success($membership_data);
    } else {
        wp_send_json_error('Could not retrieve membership data');
    }
});

/**
 * Add admin menu for testing the API
 */
add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        'JazzEdge Membership Checker',
        'JazzEdge Membership',
        'manage_options',
        'jazzedge-membership-checker',
        'render_membership_checker_page'
    );
});

function render_membership_checker_page() {
    ?>
    <div class="wrap">
        <h1>JazzEdge Membership Checker</h1>
        <p>Test the connection to the main JazzEdge.com site and retrieve membership data.</p>
        
        <form id="membership-checker-form" method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">Email Address</th>
                    <td>
                        <input type="email" name="test_email" id="test_email" class="regular-text" required>
                        <p class="description">Enter an email address to check their JazzEdge.com membership status.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button-primary">Check Membership</button>
            </p>
        </form>
        
        <div id="membership-results" style="display: none;">
            <h2>Membership Data</h2>
            <div id="membership-content"></div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#membership-checker-form').on('submit', function(e) {
            e.preventDefault();
            
            var email = $('#test_email').val();
            $('#membership-results').hide();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_jazzedge_membership',
                    email: email
                },
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true).text('Checking...');
                },
                success: function(response) {
                    if (response.success) {
                        var html = '<pre>' + JSON.stringify(response.data, null, 2) + '</pre>';
                        $('#membership-content').html(html);
                        $('#membership-results').show();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Connection error. Please check your API configuration.');
                },
                complete: function() {
                    $('button[type="submit"]').prop('disabled', false).text('Check Membership');
                }
            });
        });
    });
    </script>
    <?php
}

// Remove from unchosen lists + add chosen (only for lists this form manages)

add_action('fluentform/submission_inserted', function($entryId, $formData, $form){
    if ((int)$form->id !== 42 || !function_exists('FluentCrmApi')) return;

    $email      = $formData['email'] ?? '';
    $first_name = $formData['names']['first_name'] ?? '';
    $last_name  = $formData['names']['last_name'] ?? '';
    if (!$email) return;

    $contact = FluentCrmApi('contacts')->createOrUpdate([
        'email'                => $email,
        'first_name'           => $first_name,
        'last_name'            => $last_name,
        'current_piano_status' => $formData['current_piano_status'] ?? '',
        'motivation'           => $formData['motivation'] ?? '',
        'time_commitment'      => $formData['time_commitment'] ?? '',
        'obstacles'            => $formData['obstacles'] ?? ''
    ]);
    if (!$contact) return;

    // Lists controlled by this form (FluentCRM list IDs)
    $managed  = [2,3,24,23,5];

    // Selected lists from checkboxes
    $selected = array_values(array_intersect(
        array_map('intval', (array)($formData['email_list'] ?? [])),
        $managed
    ));

    // Remove from unselected managed lists
    $toDetach = array_values(array_diff($managed, $selected));
    if ($toDetach) $contact->detachLists($toDetach);

    // Add to selected managed lists
    if ($selected) $contact->attachLists($selected);

}, 20, 3);

/* Helpers */
function je_dt_local($v){ if(!$v) return null; if(is_numeric($v)){$d=new DateTime('@'.intval($v));$d->setTimezone(wp_timezone());return $d;} try{ return new DateTime($v, wp_timezone()); }catch(Exception){ return null; } }
function je_dt_utc(DateTime $d){ $u=clone $d; return $u->setTimezone(new DateTimeZone('UTC')); }
function je_gcal_range($s,$e){ $a=je_dt_utc($s)->format('Ymd\THis\Z'); if(!$e){ $e=(clone $s)->modify('+1 hour'); } $b=je_dt_utc($e)->format('Ymd\THis\Z'); return "$a/$b"; }

// [event_details id=""] (formerly [je_event_details])
// COMMENTED OUT - Moved to academy-lesson-manager-shortcodes plugin
// Uncomment the line below to re-enable this shortcode
// add_shortcode('event_details', function($atts){
if (false) { // Disabled - shortcode moved to academy-lesson-manager-shortcodes plugin
add_shortcode('event_details', function($atts){
  $id = absint($atts['id'] ?? get_the_ID());
  $s  = je_dt_local(get_post_meta($id,'je_event_start',true)); if(!$s) return '';
  $e  = je_dt_local(get_post_meta($id,'je_event_end',true));
  $tz = wp_timezone_string();

  // Date/Time strings
  $day  = wp_date('D', $s->getTimestamp()); // Thu, Fri, etc.
  $date = wp_date('F j, Y', $s->getTimestamp());
  $time = wp_date('g:i a',  $s->getTimestamp()) . ($e ? ' – ' . wp_date('g:i a', $e->getTimestamp()) : '');

  // Helper to get term names (handles membership-level vs membership_level)
  $get_terms_uc = function($post_id, $tax_slugs){
    $tax_slugs = (array) $tax_slugs;
    foreach ($tax_slugs as $tx) {
      $names = wp_get_post_terms($post_id, $tx, ['fields'=>'names']);
      if (!is_wp_error($names) && !empty($names)) {
        return implode(', ', array_map(function($n){ return ucfirst(strtolower($n)); }, $names));
      }
    }
    return '';
  };

  $teacher = $get_terms_uc($id, 'teacher');
  $level   = $get_terms_uc($id, 'membership-level'); 

  // Calendar links
  $title = wp_strip_all_tags(get_the_title($id));
  $desc  = wp_strip_all_tags(get_the_excerpt($id) ?: get_post_field('post_content',$id));
  $gcal  = add_query_arg([
    'action'  => 'TEMPLATE',
    'text'    => $title,
    'dates'   => je_gcal_range($s,$e),
    'details' => $desc."\n".get_permalink($id),
    'ctz'     => $tz,
  ], 'https://www.google.com/calendar/render');
  $ics   = add_query_arg(['action'=>'je_ics','id'=>$id], admin_url('admin-ajax.php'));

  // Join / Registration
  $join     = trim((string) get_post_meta($id,'je_event_zoom_link',true));
  $reg_url  = trim((string) get_post_meta($id,'je_event_registration',true)); // NEW
  $has_reg  = !empty($reg_url);

  // Buffer (hide cal links if event start is >12h in the past)
  $now = new DateTime('now', wp_timezone());
  $cutoff = (clone $now)->modify('-12 hours');
  $show_cal_links = ($s >= $cutoff);

  ob_start(); ?>
  <div class="je-session-card">
    <div class="je-session-dt">
      <div class="je-session-date"><?php echo $day. '. ' . esc_html($date); ?></div>
      <div class="je-session-time"><?php echo esc_html($time); ?> <span class="je-session-tz"><?php echo esc_html($tz); ?></span></div>
    </div>

    <?php if ($teacher || $level): ?>
      <div class="je-session-tags">
        <?php if ($teacher): ?>
          <span class="je-chip"><strong>Teacher:</strong> <?php echo esc_html($teacher); ?></span>
        <?php endif; ?>
        <?php if ($level): ?>
          <span class="je-chip je-chip--level"><strong>Level:</strong> <?php echo esc_html($level); ?></span>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="je-cal je-cal--center je-cal--tight">
      <?php if ($show_cal_links): ?>
        <div class="je-ical-row">
          <a class="je-btn je-btn--sm je-gcal" target="_blank" rel="noopener" href="<?php echo esc_url($gcal); ?>">
            <i class="fa-regular fa-calendar-plus" aria-hidden="true"></i><span>Add to Google</span>
          </a>
          <a class="je-btn je-btn--sm je-ics" href="<?php echo esc_url($ics); ?>">
            <i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i><span>Add to iCal</span>
          </a>
        </div>
      <?php endif; ?>

      <?php
      // If a Registration URL is set → show REGISTER (styled like Join) and do NOT show Join
      if ($has_reg) {
        echo '<a class="je-btn je-join je-join--full je-register" href="'.esc_url($reg_url).'" rel="noopener" target="_blank">
                <i class="fa-solid fa-ticket" aria-hidden="true"></i><span>Register</span>
              </a>';
        echo '<p class="je-register-note">After registering, your join link will be sent via email before the session.</p>';
      } else {
        // Otherwise, show Join with access check
        if ($join){
          $acc = je_event_access_check($id);
          if (!empty($acc['ok'])) {
            echo '<a class="je-btn je-join je-join--full" href="'.esc_url($join).'">
                    <i class="fa-solid fa-video" aria-hidden="true"></i><span>Join Session</span>
                  </a>';
          } else {
            echo '<a class="je-btn je-join je-join--full je-btn--disabled" href="#" aria-disabled="true" onclick="return false;">
                    <i class="fa-solid fa-video" aria-hidden="true"></i><span>Join Session</span>
                  </a>';
            if (!empty($acc['msg'])) {
              echo '<p class="je-access-msg">'.$acc['msg'].'</p>';
            }
          }
        }
      }
      ?>
    </div>
  </div>

  <style>
    /* Small, 2-col calendar buttons */
    .je-ical-row {
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:10px;
      width:100%;
      max-width:420px;
      margin:0 auto 10px;
    }
    .je-btn.je-btn--sm {
      padding:10px 12px;
      font-size:14px;
      line-height:1.2;
      border-radius:10px;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      font-weight:700;
      text-decoration:none;
    }
    /* Keep your existing Join button look */
    .je-btn.je-join {
      display:flex;
      align-items:center;
      justify-content:center;
      gap:10px;
      padding:14px 18px;
      border-radius:14px;
      font-weight:700;
      text-decoration:none;
      background:#0b4c56;
      color:#fff;
    }
    .je-btn.je-join:hover { background:#083b43; color:#fff; }
    .je-btn--disabled { opacity:.55; pointer-events:none; }

    /* Register styled same as join */
    .je-btn.je-register { background:#0b4c56; color:#fff; }
    .je-btn.je-register:hover { background:#083b43; color:#fff; }

    .je-register-note { text-align:center; margin:8px 0 0; font-size:13px; opacity:.8; }
  </style>
  <?php
  return trim(ob_get_clean());
});
} // End of disabled shortcode

/* ICS download via admin-ajax.php */
add_action('wp_ajax_je_ics','je_ics_download');
add_action('wp_ajax_nopriv_je_ics','je_ics_download');
function je_ics_download(){
  $id = absint($_GET['id'] ?? 0); if(!$id){ status_header(404); exit; }
  $s = je_dt_local(get_post_meta($id,'je_event_start',true)); if(!$s){ status_header(404); exit; }
  $e = je_dt_local(get_post_meta($id,'je_event_end',true));

  $title = wp_strip_all_tags(get_the_title($id));
  $desc  = wp_strip_all_tags(get_the_excerpt($id) ?: get_post_field('post_content',$id));
  $loc   = ''; // add ACF field later if needed
  $perma = get_permalink($id);
  $uid   = $id.'@'.parse_url(home_url(),PHP_URL_HOST);

  $dtstart = je_dt_utc($s)->format('Ymd\THis\Z');
  $dtend   = $e ? je_dt_utc($e)->format('Ymd\THis\Z') : je_dt_utc((clone $s)->modify('+1 hour'))->format('Ymd\THis\Z');

  $ics  = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//JE//Events//EN\r\nBEGIN:VEVENT\r\nUID:$uid\r\nDTSTAMP:".gmdate('Ymd\THis\Z')."\r\nDTSTART:$dtstart\r\nDTEND:$dtend\r\nSUMMARY:".addcslashes($title,",;")."\r\n";
  if($loc) $ics .= "LOCATION:".addcslashes($loc,",;")."\r\n";
  $ics .= "DESCRIPTION:".addcslashes($desc."\n".$perma,",;")."\r\nURL:$perma\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";

  header('Content-Type: text/calendar; charset=utf-8');
  header('Content-Disposition: attachment; filename="'.sanitize_title($title).'.ics"');
  echo $ics; exit;
}

/* Shortcode: [je_add_to_calendar] */
add_shortcode('je_add_to_calendar', function($atts){
  $id = absint($atts['id'] ?? get_the_ID());
  $s = je_dt_local(get_post_meta($id,'je_event_start',true)); if(!$s) return '';
  $e = je_dt_local(get_post_meta($id,'je_event_end',true));
  $title = wp_strip_all_tags(get_the_title($id));
  $desc  = wp_strip_all_tags(get_the_excerpt($id) ?: get_post_field('post_content',$id));
  $gcal = add_query_arg([
    'action'=>'TEMPLATE',
    'text'=>$title,
    'dates'=>je_gcal_range($s,$e),
    'details'=>$desc."\n".get_permalink($id),
    'ctz'=>wp_timezone_string(),
  ], 'https://www.google.com/calendar/render');
  $ics  = add_query_arg(['action'=>'je_ics','id'=>$id], admin_url('admin-ajax.php'));
  $join = get_post_meta($id,'je_event_zoom_link',true);

  $o  = '<div class="je-cal">';
  $o .= '<a class="je-btn je-gcal" target="_blank" rel="noopener" href="'.esc_url($gcal).'">Add to Google</a> ';
  $o .= '<a class="je-btn je-ics" href="'.esc_url($ics).'">Download .ics</a>';
  if($join) $o .= ' <a class="je-btn je-join" href="'.esc_url($join).'">Join Session</a>';
  return $o.'</div>';
});

// ---------- helpers (updated + messages) ----------
function je_user_level(){
  global $user_membership_level;
  $lvl = $user_membership_level ?: get_user_meta(get_current_user_id(), 'je_membership_level', true);
  return strtolower(trim((string)$lvl));
}

function je_event_required_level($post_id){
  $slugs = wp_get_post_terms($post_id, 'membership-level', ['fields'=>'slugs']);
  if (is_wp_error($slugs) || empty($slugs)) {
    $slugs = wp_get_post_terms($post_id, 'membership_level', ['fields'=>'slugs']);
  }
  if (is_wp_error($slugs) || empty($slugs)) return 'free';
  $slug = reset($slugs);
  return $slug ?: 'free';
}

function je_event_access_check($post_id){
  $req = je_event_required_level($post_id);          // free|studio|premier

  if ($req === 'free'){
    if (!is_user_logged_in()){
      return ['ok'=>false,'msg'=>'You must be <a href="/login">logged in</a> to join or download resources.'];
    }
    return ['ok'=>true,'msg'=>''];
  }

  if (!is_user_logged_in()){
    return ['ok'=>false,'msg'=>'This session is for members. Please <a href="/login">log in</a> to continue.'];
  }

  $usr = je_user_level();                            // studio|premier|''

  if ($req === 'studio'){
    if ($usr === 'studio' || $usr === 'premier') return ['ok'=>true,'msg'=>''];
    return ['ok'=>false,'msg'=>'This session is for Studio or <a href="/premier">Premier members</a>.'];
  }

  if ($req === 'premier'){
    if ($usr === 'premier') return ['ok'=>true,'msg'=>''];
    return ['ok'=>false,'msg'=>'This session is for <a href="/premier">Premier members</a>.'];
  }

  return ['ok'=>false,'msg'=>''];
}

// Boolean wrapper (back-compat)
function je_event_user_can_access($post_id){
  $r = je_event_access_check($post_id);
  return !empty($r['ok']);
}

function je_icon_for_type($type){
  switch ($type){
    case 'sheet-music':   return "<i class='fa-sharp fa-solid fa-music'></i>";
    case 'backing-track': return "<i class='fa-sharp fa-solid fa-drum'></i>";
    case 'irealpro':      return "<i class='fa-sharp fa-solid fa-sliders-h'></i>";
    case 'youtube':       return "<i class='fa-sharp fa-solid fa-play'></i>";
    default:              return "<i class='fa-sharp fa-solid fa-download'></i>";
  }
}

function je_resolve_attachment_id($file){
  if (!$file) return 0;
  if (is_numeric($file)) return (int)$file;
  if (is_array($file) && !empty($file['ID'])) return (int)$file['ID'];
  if (is_string($file)) return (int) attachment_url_to_postid($file);
  return 0;
}

/* ---------- secure downloader (AJAX) ---------- */
add_action('wp_ajax_je_download_res', 'je_download_res');
add_action('wp_ajax_nopriv_je_download_res', 'je_download_res');
// Replace the existing je_download_res() with this version
/* ---------- secure downloader (AJAX) with diagnostics ---------- */
function je_download_res(){
  // never cache ajax responses
  if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
  nocache_headers();

  $dbg = isset($_GET['dbg']); // && current_user_can('manage_options'); // debug only for admins
	$dbg = 1;
  $post_id = absint($_GET['post'] ?? 0);
  $att_id  = absint($_GET['att']  ?? 0);
  $nonce   = $_GET['_wpnonce'] ?? '';

  $diag = [
    'post_id' => $post_id,
    'att_id'  => $att_id,
    'nonce_rx'=> (bool)$nonce,
    'nonce_ok'=> false,
    'access'  => 'unknown',
    'file_path' => null,
    'file_readable' => null,
    'file_url'  => null,
    'phase'     => 'start'
  ];

  // quick helper to emit JSON and die (only when dbg=1 and admin)
  $emit = function($extra = []) use (&$diag, $dbg){
    if ($dbg) {
      $diag = array_merge($diag, $extra);
      if (function_exists('ob_get_level')) {
        while (ob_get_level() > 0) { @ob_end_clean(); }
      }
      header('Content-Type: application/json; charset=utf-8');
      echo wp_json_encode($diag, JSON_PRETTY_PRINT);
      exit;
    }
    // normal path: just exit silently with appropriate status set earlier
    exit;
  };

  // Basic validation (+ clearer failures in debug)
  if (!$post_id || !$att_id){
    status_header(400);
    $emit(['phase'=>'fail:missing-ids']);
  }

  $diag['nonce_ok'] = wp_verify_nonce($nonce, 'je_res_'.$post_id.'_'.$att_id) ? true : false;
  if (!$diag['nonce_ok']){
    status_header(403);
    $emit(['phase'=>'fail:nonce']);
  }

  // Access check
  try {
    $allowed = function_exists('je_event_user_can_access') ? je_event_user_can_access($post_id) : false;
  } catch (Throwable $t) {
    status_header(500);
    $emit(['phase'=>'fail:access-throw', 'err'=>$t->getMessage()]);
  }
  $allowed = true;
  $diag['access'] = $allowed ? 'ok' : 'denied';
  if (!$allowed){
    status_header(401);
    $emit(['phase'=>'fail:access-denied']);
  }

  // Attachment info
  $file_path = get_attached_file($att_id);     // local path (might be empty if offloaded)
  $file_url  = wp_get_attachment_url($att_id); // public URL (CDN/offload-safe)
  $diag['file_path']    = (string)$file_path;
  $diag['file_readable']= ($file_path && is_readable($file_path));
  $diag['file_url']     = (string)$file_url;

  // If no local file or not readable → redirect to URL (best for offloaded media)
  if (empty($file_path) || !$diag['file_readable']){
    if ($file_url){
      // clear buffers before redirect
      if (function_exists('ob_get_level')) {
        while (ob_get_level() > 0) { @ob_end_clean(); }
      }
      // Use wp_safe_redirect if same host, else normal redirect (many CDNs are different hostnames)
      // If you MUST enforce same-host only, uncomment the host check below.
      // $same_host = parse_url($file_url, PHP_URL_HOST) === parse_url(home_url(), PHP_URL_HOST);
      // if (!$same_host) { status_header(404); $emit(['phase'=>'fail:cdn-host-mismatch']); }
      wp_redirect($file_url, 302);
      exit;
    }
    status_header(404);
    $emit(['phase'=>'fail:no-path-and-no-url']);
  }

  // STREAM LOCAL FILE
  // Clear buffers (prevents “headers already sent”)
  if (function_exists('ob_get_level')) {
    while (ob_get_level() > 0) { @ob_end_clean(); }
  }

  @set_time_limit(0);

  $filename = basename($file_path);
  $mime     = get_post_mime_type($att_id) ?: 'application/octet-stream';
  $length   = @filesize($file_path);

  // Headers
  header('Content-Description: File Transfer');
  header('Content-Type: ' . $mime);
  header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
  header('Content-Transfer-Encoding: binary');
  header('Cache-Control: private, no-cache, no-store, must-revalidate');
  header('Pragma: no-cache');
  header('Expires: 0');
  if ($length !== false) header('Content-Length: ' . $length);
  header('X-Accel-Buffering: no');

  // Output
  $fp = @fopen($file_path, 'rb');
  if ($fp === false){
    status_header(500);
    $emit(['phase'=>'fail:fopen']);
  }
  while (!feof($fp)) {
    echo fread($fp, 8192);
    @flush();
    @ob_flush();
  }
  fclose($fp);
  exit;
}
/* ---------- shortcode ---------- */
add_shortcode('je_event_resources', function($atts){
  $post_id = absint($atts['id'] ?? get_the_ID());
  $user_id = get_current_user_id();

  if (!is_user_logged_in()){
    return '<p>You must be <a href="/login">logged in</a> to download resources.</p>';
  }
  if (!je_event_user_can_access($post_id)){
    return '<p>Please upgrade your membership to a <a href="/premier">Premier-level</a> to download resources.</p>';
  }

  $rows = get_field('je_event_resource_repeater', $post_id);
  if (empty($rows)){
    return '<ul><li>No resources found. Resources might be added after the live session—check back soon.</li></ul>';
  }

  $out = '<ul class="no-bullets">';
  $found = false;

  foreach ($rows as $row){
    $type = strtolower(trim($row['je_event_resource_type'] ?? ''));
    $icon = je_icon_for_type($type);

    // YouTube
    if ($type === 'youtube' && !empty($row['je_event_resource_youtube_link'])){
      $url = esc_url($row['je_event_resource_youtube_link']);
      $out .= "<li class='space'>{$icon} <a href='{$url}' target='_blank' class='hover-black'>Watch on YouTube</a></li>";
      $found = true;
      continue;
    }

    // File
    $att_id = je_resolve_attachment_id($row['je_event_resource_file'] ?? '');
    if ($att_id){
      $nonce = wp_create_nonce('je_res_'.$post_id.'_'.$att_id);
      $dl = add_query_arg([
        'action'   => 'je_download_res',
        'post'     => $post_id,
        'att'      => $att_id,
        '_wpnonce' => $nonce,
      ], admin_url('admin-ajax.php'));

      $title = get_the_title($att_id) ?: basename(get_attached_file($att_id));
      $out .= "<li class='space'>{$icon} <a href='".esc_url($dl)."' class='hover-black'>{$title}</a></li>";
      $found = true;
      continue;
    }
  }

  if (!$found){
    $out .= '<li>No resources found. Resources might be added after the live session—check back soon.</li>';
  }

  $out .= '</ul>';
  return $out;
});

/* ========= JE MONTH CALENDAR (shortcode) =========
 * Uses CPT: je_event
 * Meta: je_event_start (UNIX ts or strtotime()-parsable)
 * Tax: teacher, membership-level (optional query filters via ?teacher=, ?level=)
 * Usage: [je_events_calendar] or [je_events_calendar month="2025-08"]
 */
/* ========= FIX 404: use ?jem=YYYY-MM instead of WP's reserved ?m= ========= */
// --- Month math & query ---
function je_ev_local_dt($v){
  if(!$v) return null;
  if(is_numeric($v)){ $d=new DateTime('@'.intval($v)); $d->setTimezone(wp_timezone()); return $d; }
  try { return new DateTime($v, wp_timezone()); } catch (Exception) { return null; }
}
function je_month_bounds($ym){
  try{ $s=new DateTime($ym.'-01 00:00:00', wp_timezone()); }
  catch(Exception){ $s=new DateTime('first day of this month 00:00:00', wp_timezone()); }
  $e=(clone $s)->modify('last day of this month 23:59:59');
  return [$s,$e];
}

/*
function je_get_events_between($from,$to,$tax_filters=[]){
  // ACF returns Y-m-d H:i:s -> use DATETIME comparison (lexicographic-safe)
  $start = $from->format('Y-m-d H:i:s');
  $end   = $to->format('Y-m-d H:i:s');

  $tax_query = [];
  foreach ($tax_filters as $tax=>$term){
    if ($term) $tax_query[] = ['taxonomy'=>$tax,'field'=>'slug','terms'=>$term];
  }

  // Exclude events that have been converted to ALM lessons
  $meta_query = [[
    'key'     => 'je_event_start',
    'value'   => [$start, $end],
    'compare' => 'BETWEEN',
    'type'    => 'DATETIME',
  ], [
    'key'     => '_converted_to_alm_lesson_id',
    'compare' => 'NOT EXISTS',
  ]];

  return get_posts([
    'post_type'      => 'je_event',
    'posts_per_page' => -1,
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_key'       => 'je_event_start',
    'meta_type'      => 'DATETIME',
    'meta_query'     => $meta_query,
    'tax_query'      => $tax_query ?: [],
    'no_found_rows'  => true,
  ]);
}
*/


function je_get_events_between($from,$to,$tax_filters=[]){
  // ACF returns Y-m-d H:i:s -> use DATETIME comparison (lexicographic-safe)
  $start = $from->format('Y-m-d H:i:s');
  $end   = $to->format('Y-m-d H:i:s');

  $tax_query = [];
  foreach ($tax_filters as $tax=>$term){
    if ($term) $tax_query[] = ['taxonomy'=>$tax,'field'=>'slug','terms'=>$term];
  }

  return get_posts([
    'post_type'      => 'je_event',
    'posts_per_page' => -1,
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_key'       => 'je_event_start',
    'meta_type'      => 'DATETIME',
    'meta_query'     => [[
      'key'     => 'je_event_start',
      'value'   => [$start, $end],
      'compare' => 'BETWEEN',
      'type'    => 'DATETIME',
    ]],
    'tax_query'      => $tax_query ?: [],
    'no_found_rows'  => true,
  ]);
} 

// [je_events_calendar month="YYYY-MM"]
// [je_events_calendar month="YYYY-MM"]

add_shortcode('je_events_calendar', function ($atts) {
  if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
  nocache_headers();

  // cache-buster
  $cb_bucket = floor(time() / 300) * 300;
  $cb_user   = is_user_logged_in() ? ('u' . get_current_user_id()) : 'g';
  $je_cb     = $cb_user . '-' . gmdate('YmdHi', $cb_bucket);

  // month
  $a  = shortcode_atts(['month' => ''], $atts, 'je_events_calendar');
  $ym = $a['month'] ?: (isset($_GET['jem']) ? sanitize_text_field($_GET['jem']) : wp_date('Y-m'));
  [$mStart, $mEnd] = je_month_bounds($ym);

  // prev/next
  $prevObj = (clone $mStart)->modify('-1 month');
  $nextObj = (clone $mStart)->modify('+1 month');
  $prevM   = $prevObj->format('Y-m');
  $nextM   = $nextObj->format('Y-m');

  // filters
  $teacher = isset($_GET['teacher']) ? sanitize_title($_GET['teacher']) : '';
  $level   = isset($_GET['level'])   ? sanitize_title($_GET['level'])   : '';
  $etype   = isset($_GET['etype'])   ? sanitize_title($_GET['etype'])   : '';

  // events
  $events = je_get_events_between($mStart, $mEnd, [
    'teacher'          => $teacher,
    'membership-level' => $level ?: null,
  ]);

  // filter by event type
  if ($etype) {
    $events = array_values(array_filter($events, function($ev) use ($etype) {
      $slugs = wp_get_post_terms($ev->ID, 'event-type', ['fields'=>'slugs']);
      if (is_wp_error($slugs) || empty($slugs)) $slugs = wp_get_post_terms($ev->ID, 'event_type', ['fields'=>'slugs']);
      $slugs = !is_wp_error($slugs) ? array_map('sanitize_title', (array)$slugs) : [];
      return in_array($etype, $slugs, true);
    }));
  }

  // map by day
  $byDay = [];
  foreach ($events as $e) {
    $ts = get_post_meta($e->ID, 'je_event_start', true);
    $dt = je_ev_local_dt($ts);
    if (!$dt) continue;
    $byDay[$dt->format('Y-m-d')][] = $e;
  }
  
  // Helper function to get lesson permalink for converted events
	$get_event_permalink = function($event_id) {
	  global $wpdb;
	  
	  // Check if event is converted to a lesson
	  $alm_lesson_id = get_post_meta($event_id, '_converted_to_alm_lesson_id', true);
	  
	  if (!empty($alm_lesson_id)) {
		// Get the lesson's WordPress post_id from ALM table
		$lessons_table = $wpdb->prefix . 'alm_lessons';
		$lesson_post_id = $wpdb->get_var($wpdb->prepare(
		  "SELECT post_id FROM {$lessons_table} WHERE ID = %d",
		  intval($alm_lesson_id)
		));
		
		// If lesson post exists, use its permalink
		if ($lesson_post_id && get_post($lesson_post_id)) {
		  return get_permalink($lesson_post_id);
		}
	  }
	  
	  // Fallback to event permalink
	  return get_permalink($event_id);
	};

  // urls
  $base    = add_query_arg(['je_cb'=>$je_cb], get_permalink());
  $prevUrl = add_query_arg(array_filter(['jem'=>$prevM,'teacher'=>$teacher,'level'=>$level,'etype'=>$etype,'je_cb'=>$je_cb]), get_permalink());
  $nextUrl = add_query_arg(array_filter(['jem'=>$nextM,'teacher'=>$teacher,'level'=>$level,'etype'=>$etype,'je_cb'=>$je_cb]), get_permalink());

  // terms (light cache)
  $teacher_terms = get_transient('je_cal_teacher_terms');
  if ($teacher_terms === false) {
    $teacher_terms = get_terms(['taxonomy'=>'teacher','hide_empty'=>true]);
    set_transient('je_cal_teacher_terms', $teacher_terms, 10 * MINUTE_IN_SECONDS);
  }
  $level_tax = taxonomy_exists('membership-level') ? 'membership-level' : (taxonomy_exists('membership_level') ? 'membership_level' : '');
  $level_terms = [];
  if ($level_tax) {
    $level_terms = get_transient('je_cal_level_terms');
    if ($level_terms === false) {
      $level_terms = get_terms(['taxonomy'=>$level_tax,'hide_empty'=>true]);
      set_transient('je_cal_level_terms', $level_terms, 10 * MINUTE_IN_SECONDS);
    }
  }
  $etype_tax = taxonomy_exists('event-type') ? 'event-type' : (taxonomy_exists('event_type') ? 'event_type' : '');
  $etype_terms = [];
  if ($etype_tax) {
    $etype_terms = get_transient('je_cal_etype_terms');
    if ($etype_terms === false) {
      $etype_terms = get_terms(['taxonomy'=>$etype_tax,'hide_empty'=>true]);
      set_transient('je_cal_etype_terms', $etype_terms, 10 * MINUTE_IN_SECONDS);
    }
  }

  $todayKey = wp_date('Y-m-d');

  // helpers
  $get_level_slug = function($post_id) {
    $terms = wp_get_post_terms($post_id, 'membership-level', ['fields'=>'slugs']);
    if (is_wp_error($terms) || empty($terms)) $terms = wp_get_post_terms($post_id, 'membership_level', ['fields'=>'slugs']);
    if (!is_wp_error($terms) && !empty($terms)) {
      $slug = strtolower(reset($terms));
      if (in_array($slug, ['studio','premier','free'], true)) return $slug;
    }
    return 'free';
  };
  $get_event_type_slug = function($post_id) {
    $slugs = wp_get_post_terms($post_id, 'event-type', ['fields'=>'slugs']);
    if (is_wp_error($slugs) || empty($slugs)) $slugs = wp_get_post_terms($post_id, 'event_type', ['fields'=>'slugs']);
    return (!is_wp_error($slugs) && !empty($slugs)) ? sanitize_title(reset($slugs)) : '';
  };
  $is_special_tax = function($post_id){
    $has = wp_get_post_terms($post_id, 'special', ['fields'=>'ids']);
    return !is_wp_error($has) && !empty($has);
  };

  ob_start();

  // CSS (once)
  static $je_cal_css_printed = false;
  if (!$je_cal_css_printed) { $je_cal_css_printed = true; ?>
    <style id="je-cal-inline-css">
      #je-cal{ margin-top:10px }
      #je-cal .je-cal-filters{
        --je-bg:#ffffff; --je-br:#e8ecef; --je-text:#3a3a3a; --je-shadow:0 6px 18px rgba(0,0,0,.06);
        display:grid; grid-template-columns:1fr 1fr 1fr auto; align-items:end; gap:18px 22px;
        width:100%; margin:12px 0 22px; padding:16px 20px; background:var(--je-bg);
        border:1px solid var(--je-br); border-radius:18px; box-shadow:var(--je-shadow); color:var(--je-text);
      }
      #je-cal .je-cal-filters label{ display:flex; flex-direction:column; gap:8px; font-weight:700 }
      #je-cal .je-cal-filters select{
        height:52px; border-radius:999px; border:1.5px solid #dfe6ea; padding:0 52px 0 16px; background:#fff;
        background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23004555' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>");
        background-repeat:no-repeat; background-position:right 16px center; background-size:18px 18px;
        font-weight:600; color:#1f2a2e;
      }
      #je-cal .je-cal-reset{ justify-self:end; height:52px; line-height:52px; padding:0 16px; border-radius:999px; background:#f5f8f9; color:#0a5d6c; border:1px solid #dfe7ea; text-decoration:none; font-weight:800 }

      #je-cal .je-cal-bar{ display:flex; align-items:center; justify-content:space-between; gap:16px; margin:10px 0 16px }
      #je-cal .je-cal-title{ flex:1; text-align:center; font-weight:800; font-size:28px; color:#3a3a3a }
      #je-cal .je-cal-nav{ padding:10px 16px; border-radius:999px; font-weight:800; background:#004555; color:#fff !important; text-decoration:none }
      #je-cal .je-cal-nav:hover{ background:#f04e23 }

      #je-cal .je-cal-grid{ display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:8px }
      #je-cal .je-cal-hd{ font-weight:800; text-align:center; padding:8px 0; color:#465057 }
      #je-cal .je-cal-cell{ position:relative; min-height:110px; padding:10px; background:#fff; border:1px solid #e6e6e6; border-radius:12px }
      #je-cal .je-cal-pad{ background:#fafafa }
      #je-cal .je-cal-cell.has-events{ border-color:#cfe1e7; box-shadow:0 1px 2px rgba(0,0,0,.04) }
      #je-cal .je-cal-daynum{ position:absolute; top:8px; right:10px; font-weight:800; opacity:.82; color:#6b767b }
      #je-cal .je-cal-list{ list-style:none; margin:18px 0 0; padding:0 }
      #je-cal .je-cal-list li{ margin:6px 0 0; line-height:1.25 }
      #je-cal .je-cal-list .t{ font-weight:800; margin-right:6px; font-size:.92em; opacity:.9; color:#2f3a3d }
      #je-cal .je-cal-list a{ text-decoration:none; color:#0a5d6c }
      #je-cal .je-cal-list a:hover{ text-decoration:underline; color:#f04e23 }

      /* membership level accents (fallback if no event-type palette) */
      #je-cal .je-cal-list li.studio  a{ border-left:4px solid #004555; padding-left:6px }
      #je-cal .je-cal-list li.premier a{ border-left:4px solid #f04e23; padding-left:6px }

      /* event-type palettes (auto via et-{slug}) */
      #je-cal .je-cal-list li[class*="et-"] a{
        display:inline-block; padding:6px 8px; border-radius:8px;
        background:var(--et-bg,#f5f7fa);
        border-left:4px solid var(--et-accent,#6b767b) !important;
      }
      #je-cal .je-cal-list li[class*="et-"] a .t{ color:var(--et-accent,#6b767b) }
      /* common types */
      #je-cal .je-cal-list li.et-community-call{ --et-bg:#e8f7ee; --et-accent:#1e7f41 }
      #je-cal .je-cal-list li.et-special       { --et-bg:#efeaff; --et-accent:#6b4fd8 }
      #je-cal .je-cal-list li.et-coaching      { --et-bg:#e9f3ff; --et-accent:#004555 }
      #je-cal .je-cal-list li.et-class         { --et-bg:#fff1ec; --et-accent:#f04e23 }
      #je-cal .je-cal-list li.et-workshop      { --et-bg:#fffbe6; --et-accent:#c58a00 }
      #je-cal .je-cal-list li.et-webinar       { --et-bg:#eef6ff; --et-accent:#0a5d6c }
      #je-cal .je-cal-list li.et-office-hours  { --et-bg:#f0f9ff; --et-accent:#0b7285 }

      /* treat separate "special" taxonomy as special as well */
      #je-cal .je-cal-list li.is-special a{ background:#efeaff; border-left-color:#6b4fd8 !important }
      #je-cal .je-cal-list li.is-special a .t{ color:#6b4fd8 }

      /* today */
      #je-cal .je-cal-cell.today{ background:#fff8e6; border:2px solid #f04e23 }
      #je-cal .je-cal-cell.today .je-cal-daynum{ background:#f04e23; color:#fff; padding:2px 6px; border-radius:6px }

      @media (max-width:1000px){ #je-cal .je-cal-filters{ grid-template-columns:1fr 1fr 1fr } }
      @media (max-width:700px){
        #je-cal .je-cal-grid{ grid-template-columns:repeat(2,minmax(0,1fr)) }
        #je-cal .je-cal-filters{ grid-template-columns:1fr; gap:14px }
        #je-cal .je-cal-reset{ width:100%; text-align:center }
      }
    </style>
  <?php } ?>

  <div id="je-cal" class="je-cal-wrap">
    <form class="je-cal-filters" method="get" action="<?php echo esc_url($base); ?>">
      <input type="hidden" name="jem" value="<?php echo esc_attr($mStart->format('Y-m')); ?>">
      <input type="hidden" name="je_cb" value="<?php echo esc_attr($je_cb); ?>">

      <label>
        <span>Teacher</span>
        <select name="teacher" onchange="this.form.submit()">
          <option value="">All</option>
          <?php if (!is_wp_error($teacher_terms)) foreach ($teacher_terms as $t): ?>
            <option value="<?php echo esc_attr($t->slug); ?>" <?php selected($teacher, $t->slug); ?>>
              <?php echo esc_html($t->name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <?php if ($level_tax): ?>
      <label>
        <span>Level</span>
        <select name="level" onchange="this.form.submit()">
          <option value="">All</option>
          <?php if (!is_wp_error($level_terms)) foreach ($level_terms as $lt): ?>
            <option value="<?php echo esc_attr($lt->slug); ?>" <?php selected($level, $lt->slug); ?>>
              <?php echo esc_html(ucfirst(strtolower($lt->name))); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <?php endif; ?>

      <?php if ($etype_tax): ?>
      <label>
        <span>Event Type</span>
        <select name="etype" onchange="this.form.submit()">
          <option value="">All</option>
          <?php if (!is_wp_error($etype_terms)) foreach ($etype_terms as $et): ?>
            <option value="<?php echo esc_attr($et->slug); ?>" <?php selected($etype, $et->slug); ?>>
              <?php echo esc_html($et->name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <?php endif; ?>

      <?php $reset_url = add_query_arg(['jem'=>$mStart->format('Y-m'),'je_cb'=>$je_cb], get_permalink()); ?>
      <a class="je-cal-reset" href="<?php echo esc_url($reset_url); ?>">Reset</a>
    </form>

    <div class="je-cal-bar">
      <a class="je-cal-nav je-cal-prev" href="<?php echo esc_url($prevUrl); ?>">&laquo; <?php echo esc_html(date_i18n('M Y', $prevObj->getTimestamp())); ?></a>
      <div class="je-cal-title"><?php echo esc_html(date_i18n('F Y', $mStart->getTimestamp())); ?></div>
      <a class="je-cal-nav je-cal-next" href="<?php echo esc_url($nextUrl); ?>"><?php echo esc_html(date_i18n('M Y', $nextObj->getTimestamp())); ?> &raquo;</a>
    </div>

    <div class="je-cal-grid">
      <?php
        foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d) echo '<div class="je-cal-hd">'.esc_html($d).'</div>';
        for ($i=0, $firstDow=(int)$mStart->format('w'); $i<$firstDow; $i++) echo '<div class="je-cal-cell je-cal-pad"></div>';

        $daysInMonth = (int)$mStart->format('t');
        for ($day=1; $day<=$daysInMonth; $day++) {
          $dkey = $mStart->format('Y-m-').str_pad($day,2,'0',STR_PAD_LEFT);
          $has  = !empty($byDay[$dkey]);

          $cell_classes = 'je-cal-cell';
          if ($has) $cell_classes .= ' has-events';
          if ($dkey === $todayKey) $cell_classes .= ' today';

          echo '<div class="'.esc_attr($cell_classes).'">';
            echo '<div class="je-cal-daynum">'.intval($day).'</div>';
            if ($has) {
              echo '<ul class="je-cal-list">';
              foreach ($byDay[$dkey] as $ev) {
                $ts   = get_post_meta($ev->ID,'je_event_start',true);
                $dt   = je_ev_local_dt($ts);
                $time = $dt ? wp_date('g:i a', $dt->getTimestamp()) : '';
                $lvl  = $get_level_slug($ev->ID);
                $et   = $get_event_type_slug($ev->ID);   // e.g. community-call, class, coaching, special, etc.
                $isS  = $is_special_tax($ev->ID) || ($et === 'special');

                $classes = trim($lvl.' '.($et ? 'et-'.$et : '').($isS ? ' is-special' : ''));
                $hover   = ($lvl==='studio' ? 'Studio' : ($lvl==='premier' ? 'Premier' : 'Free'));
                
                // Use helper function to get correct permalink
			  	$event_url = $get_event_permalink($ev->ID);

				echo '<li class="'.esc_attr($classes).'"><a title="'.esc_attr($hover).'" href="'.esc_url($event_url).'"><span class="t">'.
					esc_html($time).'</span> '.esc_html(get_the_title($ev)).'</a></li>';

              }
              echo '</ul>';
            }
          echo '</div>';
        }
      ?>
    </div>
  </div>
  <?php
  return trim(ob_get_clean());
});

// Turn OFF multiple CPTs everywhere (frontend + admin UI)
/*
add_filter( 'register_post_type_args', function( $args, $post_type ) {

    $turn_off = ['classes', 'event', 'events']; // <-- add slugs here

    if ( in_array( $post_type, $turn_off, true ) ) {
        $args['public']              = false; // master switch
        $args['show_ui']             = false; // hide from admin menu & screens
        $args['show_in_menu']        = false;
        $args['show_in_admin_bar']   = false;
        $args['show_in_nav_menus']   = false;
        $args['exclude_from_search'] = true;
        $args['publicly_queryable']  = false;
        $args['has_archive']         = false;
        $args['rewrite']             = false; // prevent routes
    }

    return $args;
}, 10, 2 );
*/
add_action( 'wp_footer', function() {
    if ( is_singular() ) {
        global $post;
        if ( $post ) {
            echo '<div style="text-align:center; padding:5px; font-size:12px; color:#bbb;">';
            echo 'Post Type: <strong>' . esc_html( get_post_type( $post ) ) . '</strong>';
            echo '</div>';
        }
    }
});
// [je_events_list count="5" teacher="" level="" etype=""]
add_shortcode('je_events_list', function($atts){
  if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);

  $a = shortcode_atts(['count'=>5,'teacher'=>'','level'=>'','etype'=>''], $atts, 'je_events_list');
  $count   = max(1, (int)$a['count']);
  $teacher = $a['teacher'] !== '' ? sanitize_title($a['teacher']) : null;
  $level   = $a['level']   !== '' ? sanitize_title($a['level'])   : null;
  $etype   = $a['etype']   !== '' ? sanitize_title($a['etype'])   : '';

  // Pull a wide window and then use je_ev_local_dt() to normalize times
  $now_ts = current_time('timestamp');
  
  // Add some buffer time to catch events that might be in different timezone
  $buffer_hours = 6; // Look back 6 hours to catch events that might have timezone issues
  $now_with_buffer = $now_ts - ($buffer_hours * 3600);
  
  $start  = new DateTimeImmutable('@'.$now_with_buffer);
  $end    = new DateTimeImmutable('@'.$now_ts);
  $end    = $end->modify('+365 days');

  $events = je_get_events_between($start, $end, [
    'teacher'          => $teacher,
    'membership-level' => $level,
  ]);

  // Optional event-type filter
  if ($etype) {
    $events = array_values(array_filter($events, function($ev) use ($etype){
      $slugs = wp_get_post_terms($ev->ID, 'event-type', ['fields'=>'slugs']);
      if (is_wp_error($slugs) || empty($slugs)) $slugs = wp_get_post_terms($ev->ID, 'event_type', ['fields'=>'slugs']);
      $slugs = is_wp_error($slugs) ? [] : array_map('sanitize_title', (array)$slugs);
      return in_array($etype, $slugs, true);
    }));
  }

  // Normalize start times with je_ev_local_dt() (prevents Dec 31 epoch bug)
  $items = [];
  foreach ($events as $ev) {
    $raw = get_post_meta($ev->ID, 'je_event_start', true);
    $dt  = je_ev_local_dt($raw);
    if (!$dt) continue;
    $ts = $dt->getTimestamp();
    
    // Get event end time, or default to 2 hours after start if no end time
    $raw_end = get_post_meta($ev->ID, 'je_event_end', true);
    $end_dt = $raw_end ? je_ev_local_dt($raw_end) : null;
    $end_ts = $end_dt ? $end_dt->getTimestamp() : ($ts + (2 * 3600)); // Default 2 hours
    
    // Keep events visible for 6 hours after they end (increased buffer for timezone issues)
    $visible_until = $end_ts + (6 * 3600); // 6 hours after end
    
    // More inclusive filtering - show events that are either upcoming or recently ended
    if ($visible_until >= $now_ts) $items[] = ['id'=>$ev->ID,'ts'=>$ts,'end_ts'=>$end_ts];
  }

  usort($items, fn($a,$b)=> $a['ts'] <=> $b['ts']);
  $items = array_slice($items, 0, $count);

  // Helpers (match calendar)
  $lvl_slug = function($post_id){
    $t = wp_get_post_terms($post_id,'membership-level',['fields'=>'slugs']);
    if (is_wp_error($t) || empty($t)) $t = wp_get_post_terms($post_id,'membership_level',['fields'=>'slugs']);
    if (!is_wp_error($t) && !empty($t)) { $s=strtolower(reset($t)); if (in_array($s,['studio','premier','free'],true)) return $s; }
    return 'free';
  };
  $etype_slug = function($post_id){
    $s = wp_get_post_terms($post_id,'event-type',['fields'=>'slugs']);
    if (is_wp_error($s) || empty($s)) $s = wp_get_post_terms($post_id,'event_type',['fields'=>'slugs']);
    return (!is_wp_error($s) && !empty($s)) ? sanitize_title(reset($s)) : '';
  };
  $is_special = function($post_id){
    $h = wp_get_post_terms($post_id,'special',['fields'=>'ids']);
    return !is_wp_error($h) && !empty($h);
  };

  ob_start();

  // minimal styles (print once)
  static $css = false;
  if (!$css){ $css=true; ?>
    <style id="je-events-list-css">
      /* Cache bust: Updated calendar link styling */
      .je-events-list{margin:0;padding:0;list-style:none}
      .je-events-list .je-events-item{margin:10px 0;padding:10px 12px;border:1px solid #e6e6e6;border-radius:12px;background:#fff}
      .je-events-list .je-events-item a{text-decoration:none;color:#0a5d6c}
      .je-events-list .je-events-item a:hover{text-decoration:underline;color:#f04e23}
      .je-events-list .je-time{font-weight:800;margin-right:8px}
      .je-events-list .je-events-item[class*="et-"]{--et-bg:#f5f7fa;--et-accent:#6b767b;background:var(--et-bg)}
      .je-events-list .je-events-item[class*="et-"] .je-time{color:var(--et-accent)}
      .je-events-list .je-events-item.et-community-call{--et-bg:#e8f7ee;--et-accent:#1e7f41}
      .je-events-list .je-events-item.et-special{--et-bg:#efeaff;--et-accent:#6b4fd8}
      .je-events-list .je-events-item.et-coaching{--et-bg:#e9f3ff;--et-accent:#004555}
      .je-events-list .je-events-item.et-class{--et-bg:#fff1ec;--et-accent:#f04e23}
      .je-events-list .je-events-item.et-workshop{--et-bg:#fffbe6;--et-accent:#c58a00}
      .je-events-list .je-events-item.et-webinar{--et-bg:#eef6ff;--et-accent:#0a5d6c}
      .je-events-list .je-events-item.et-office-hours{--et-bg:#f0f9ff;--et-accent:#0b7285}
      .je-events-list .je-events-item.studio{border-left:4px solid #004555}
      .je-events-list .je-events-item.premier{border-left:4px solid #f04e23}
      .je-events-list .je-events-item.is-special{box-shadow:0 0 0 2px #efeaff inset}
      
      /* View Button Layout */
      .je-events-list .je-events-item{display:flex;align-items:center;justify-content:space-between;gap:16px}
      .je-events-list .je-event-content{flex:1;min-width:0}
      .je-events-list .je-event-title{margin-top:4px}
      .je-events-list .je-event-title a{text-decoration:none;color:#0a5d6c;font-weight:600;font-size:15px;line-height:1.3}
      .je-events-list .je-event-title a:hover{color:#f04e23;text-decoration:underline}
      .je-events-list .je-event-action{flex-shrink:0;margin-left:auto}
      .je-events-list .je-view-btn{display:inline-block;background:#0a5d6c;color:#fff !important;padding:8px 16px;border-radius:6px;text-decoration:none;font-weight:600;font-size:13px;transition:all 0.2s ease;border:none;cursor:pointer;white-space:nowrap}
      .je-events-list .je-view-btn:hover{background:#f04e23;color:#fff !important;text-decoration:none;transform:translateY(-1px)}
      
      /* Calendar Link - Fallback CSS (inline CSS handles the styling) */
      .je-events-list .je-events-calendar-link{text-align:center;margin-top:20px;padding-top:16px;border-top:1px solid #e6e6e6;width:100%}
      .je-events-list .je-events-calendar-btn{color:#0a5d6c;text-decoration:none;font-weight:600;font-size:14px;display:inline-block;padding:8px 16px;border-radius:6px;background:#f8f9fa;border:1px solid #e6e6e6;transition:all 0.2s ease}
    </style>
  <?php }

  echo '<ul class="je-events-list">';
  if ($items){
    foreach ($items as $it){
      $pid = $it['id'];
      $lvl = $lvl_slug($pid);
      $et  = $etype_slug($pid);
      $sp  = $is_special($pid) || ($et === 'special');
      $cls = trim("je-events-item $lvl ".($et ? 'et-'.$et : '').($sp ? ' is-special' : ''));
      echo '<li class="'.esc_attr($cls).'">';
      echo '<div class="je-event-content">';
      echo '<span class="je-time">'.esc_html(wp_date('D, M j • g:i a', $it['ts'])).'</span>';
      echo '<div class="je-event-title">';
      echo '<a href="'.esc_url(get_permalink($pid)).'">'.esc_html(get_the_title($pid)).'</a>';
      echo '</div>';
      echo '</div>';
      echo '<div class="je-event-action">';
      echo '<a href="'.esc_url(get_permalink($pid)).'" class="je-view-btn">View</a>';
      echo '</div>';
      echo '</li>';
    }
  } else {
    echo '<li class="je-events-item">No upcoming events found.</li>';
  }
  echo '</ul>';
  
  // Add "View the full calendar" link with inline CSS to ensure styling
  echo '<div class="je-events-calendar-link" style="text-align:center;margin-top:20px;padding-top:16px;border-top:1px solid #e6e6e6;width:100%;">';
  echo '<a href="/calendar" class="je-events-calendar-btn" style="color:#0a5d6c !important;text-decoration:none !important;font-weight:600;font-size:14px;display:inline-block !important;padding:8px 16px !important;border-radius:6px !important;background:#f8f9fa !important;border:1px solid #e6e6e6 !important;transition:all 0.2s ease;box-shadow:0 1px 3px rgba(0,0,0,0.1);" onmouseover="this.style.color=\'#f04e23\';this.style.background=\'#fff\';this.style.borderColor=\'#f04e23\';this.style.transform=\'translateY(-1px)\';this.style.boxShadow=\'0 2px 4px rgba(0,0,0,0.1)\';" onmouseout="this.style.color=\'#0a5d6c\';this.style.background=\'#f8f9fa\';this.style.borderColor=\'#e6e6e6\';this.style.transform=\'translateY(0)\';this.style.boxShadow=\'0 1px 3px rgba(0,0,0,0.1)\';">View the full calendar →</a>';
  echo '</div>';

  return trim(ob_get_clean());
});

// Test function - add to functions.php
add_action('admin_init', function() {
    if (!isset($_GET['test_family_email'])) return;
    if (!current_user_can('manage_options')) return;
    
    $test_email = 'your-test-email@example.com'; // Change to your test email
    
    echo '<pre>';
    echo "Testing Family System for: $test_email\n\n";
    
    // Test directly with FluentCRM
    if (class_exists('FluentCrm\App\Models\Subscriber')) {
        $subscriber = \FluentCrm\App\Models\Subscriber::where('email', $test_email)->first();
        
        if ($subscriber) {
            echo "✓ Subscriber found\n";
            echo "Subscriber ID: " . $subscriber->id . "\n";
            
            // Get all custom fields
            $custom_fields = $subscriber->custom_fields();
            echo "\nAll Custom Fields:\n";
            print_r($custom_fields);
            
            // Check child_accounts specifically
            if (isset($custom_fields['child_accounts'])) {
                echo "\n✓ child_accounts field found: " . $custom_fields['child_accounts'] . "\n";
            } else {
                echo "\n✗ child_accounts field NOT found\n";
            }
            
            // Get tags
            $tags = $subscriber->tags()->pluck('slug')->toArray();
            echo "\nTags: " . implode(', ', $tags) . "\n";
            
        } else {
            echo "✗ No subscriber found for email: $test_email\n";
        }
    } else {
        echo "✗ FluentCRM not available\n";
    }
    
    // Test with new email-based family system
    if (function_exists('test_family_by_email')) {
        echo "\n\nFamily System Test:\n";
        $result = test_family_by_email($test_email);
        print_r($result);
    }
    
    echo '</pre>';
    die();
});
?>