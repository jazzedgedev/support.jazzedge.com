<?php
exit;

/*
Plugin Name:	Oxygen Functions Plugin (COPY)
Plugin URI:		https://example.com
Description:	My custom functions.
Version:		1.0.0
Author:			Willie
Author URI:		https://example.com
*/

$wpe_path = '/nas/content/live/jazzacademy';
require_once( '/nas/content/live/jazzacademystg/willie/ja-admin/vimeo.php-3.0.2/src/Vimeo/Vimeo.php' );
require_once( '/nas/content/live/jazzacademystg/wp-load.php' );
use Vimeo\Vimeo;

function ja_enqueue_custom_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-quiz-script', get_stylesheet_directory_uri() . '/js/custom-quiz.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'ja_enqueue_custom_scripts');

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
    $reveal_time = intval($_POST['reveal_time']);
    $difficulty_level = sanitize_text_field($_POST['difficulty_level']);

    update_user_meta($user_id, 'reveal_time', $reveal_time);
    update_user_meta($user_id, 'difficulty_level', $difficulty_level);

    wp_send_json_success();
}
add_action('wp_ajax_ja_save_user_settings', 'ja_save_user_settings');

/*
function ja_enqueue_custom_scripts() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'ja_enqueue_custom_scripts');

function ja_get_user_settings() {
    $user_id = get_current_user_id();
    $reveal_time = get_user_meta($user_id, 'reveal_time', true) ?: 5;
    $difficulty_level = get_user_meta($user_id, 'difficulty_level', true) ?: 'easy';

    wp_send_json_success(array(
        'reveal_time' => $reveal_time,
        'difficulty_level' => $difficulty_level
    ));
}
add_action('wp_ajax_get_user_settings', 'ja_get_user_settings');

function ja_save_user_settings() {
    $user_id = get_current_user_id();
    $reveal_time = intval($_POST['reveal_time']);
    $difficulty_level = sanitize_text_field($_POST['difficulty_level']);

    update_user_meta($user_id, 'reveal_time', $reveal_time);
    update_user_meta($user_id, 'difficulty_level', $difficulty_level);

    wp_send_json_success();
}
add_action('wp_ajax_save_user_settings', 'ja_save_user_settings');
*/

/*
function enqueue_custom_scripts() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function get_user_settings() {
    $user_id = get_current_user_id();
    $reveal_time = get_user_meta($user_id, 'reveal_time', true) ?: 5;
    $difficulty_level = get_user_meta($user_id, 'difficulty_level', true) ?: 'easy';

    wp_send_json_success(array(
        'reveal_time' => $reveal_time,
        'difficulty_level' => $difficulty_level
    ));
}
add_action('wp_ajax_get_user_settings', 'get_user_settings');

function save_user_settings() {
    $user_id = get_current_user_id();
    $reveal_time = intval($_POST['reveal_time']);
    $difficulty_level = sanitize_text_field($_POST['difficulty_level']);

    update_user_meta($user_id, 'reveal_time', $reveal_time);
    update_user_meta($user_id, 'difficulty_level', $difficulty_level);

    wp_send_json_success();
}
add_action('wp_ajax_save_user_settings', 'save_user_settings');
*/

function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(https://jazzedge.academy/wp-content/uploads/2023/04/jazzedge-academy-logo-500px.png);
		height:65px;
		width:320px;
		background-size: 320px 65px;
		background-repeat: no-repeat;
        	padding-bottom: 30px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );


add_action('init', 'start_session', 1);
function start_session() {
	if(!session_id()) {
	session_start();
	}
	global $wpdb;
	$user_id = get_current_user_id();
	$prefs = $wpdb->get_var( "SELECT prefs FROM academy_user_prefs WHERE user_id = $user_id " );
	$prefs_array = unserialize($prefs);
	if (!empty($prefs_array)) {
		foreach ($prefs_array AS $k => $v) {
			$_SESSION[$k] = $v;
		}
	}
	$timezone = $wpdb->get_var( "SELECT timezone FROM academy_user_prefs WHERE user_id = $user_id " );
	$_SESSION['user-timezone'] = $timezone;
}
add_action('wp_logout','end_session');
add_action('wp_login','end_session');
add_action('end_session_action','end_session');

function end_session() {
	session_destroy();
}

function session_pref($pref) {
	if (!empty($pref)) { return $_SESSION[$pref]; }
}

function return_post_id() { // for dynamic classes
	$post_id = get_the_ID();
	return 'class-content-div postid-' . $post_id;
}

function return_featured_image() { // for dynamic classes
	$post_id = get_the_ID();
	return get_the_post_thumbnail_url($post_id);
}

function format_timezone($time, $user_timezone = 'America/New_York', $format = 'mdy') {
    if (isset($_SESSION['user-timezone'])) {
    	$user_timezone = $_SESSION['user-timezone'];
    } else {
    	$user_timezone = 'America/New_York';
    }
	$changetime = new DateTime($time, new DateTimeZone('UTC'));
	$hours = 4; // change to UTC
	$utctime = (clone $changetime)->add(new DateInterval("PT{$hours}H")); // use clone to avoid modification of $now object
	$utctime->setTimezone(new DateTimeZone($user_timezone));
	if ($format === 'mdy') {
		$user_date_format = return_user_pref_setting('dateformat');
		// $dateformat = session_pref('dateformat');
		if ($user_date_format && $user_date_format != 'us') {
			//return $utctime->format('d-m-Y \a\t h:ia');
			return $utctime->format('F jS \a\t h:ia');
		} else {
			//return $utctime->format('m-d-Y \a\t h:ia');
			return $utctime->format('F jS \a\t h:ia');
		}
	} elseif ($format === 'string') {
			return $utctime->format('M. jS, Y \a\t h:ia');
	}
}

add_filter( 'nav_menu_link_attributes', function($atts) {
        $atts['class'] = "academy_menu_link";
        return $atts;
}, 100, 1 );

add_filter( 'rest_endpoints', function( $endpoints ){
    if ( isset( $endpoints['/wp/v2/users'] ) ) {
        unset( $endpoints['/wp/v2/users'] );
    }
    if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
});


add_action('fluentform_before_insert_submission', function ($insertData, $data, $form) {
   
 if($form->id != 18) { // your form id. Change the 17 with your own login for ID
        return;
    }
    $redirectUrl = home_url(); // You can change the redirect url after successful login

    if (get_current_user_id()) { // user already registered
        wp_send_json_success([
            'result' => [
                'redirectUrl' => $redirectUrl,
                'message' => 'Your are already logged in. Redirecting now...'
            ]
        ]);
    }

    $email = \FluentForm\Framework\Helpers\ArrayHelper::get($data, 'email'); // your form should have email field
   
    if(!$email) {
        wp_send_json_error([
            'errors' => ['Please provide email']
        ], 423);
    }

    $user = get_user_by_email($email);
	if($user ) {
       
      	
		$name = $user->user_login;
		$email = $user->user_email;
		$adt_rp_key = get_password_reset_key( $user );
		$user_login = $user->user_login;
		$rp_link = '<a href="' . wp_login_url()."?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login) . '">Reset Link</a>';

		if ($name == "") $name = "There";
		$message = "Hello ".$name.",<br>";
		$message .= "Click here to resset the password for your account: <br>";
		$message .= $rp_link.'<br>';

		
	    $subject = __("Your account on ".get_bloginfo( 'name'));
	    $headers = array();

	   add_filter( 'wp_mail_content_type', function( $content_type ) {return 'text/html';});
	   $headers[] = 'From: Jazzedge Academy <`support@jazzedge.com`>'."\r\n";
	   wp_mail( $email, $subject, $message, $headers);

	   // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
	   remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
        wp_send_json_success([
            'result' => [
              
                'message' => 'Your password reset link has been sent your email.'
            ]
        ]);
    } else {
        // password or user don't match
        wp_send_json_error([
            'errors' => ['Email / password is not correct']
        ], 423);
    }
}, 10, 3);

add_action('fluentform/submission_note_stored', function ($insertId, $added_note)
{
	//wp_mail( 'wmyette@jazzedge.com', 'fluent sub', 'got here'.$insertId );
	/*
	Note is saved in this database: 'wp_fluentform_submission_meta'
	Use the response_id from table 'wp_fluentform_submission_meta' 
	then query 'id' from 'wp_fluentform_submissions' to find user_id
	*/
	
  global $wpdb;
  $data = array(	
	'data' => $insertId .'-----'.$added_note,
	);
	$format = array('%s');
	$wpdb->insert('aaa_test',$data,$format);
  
}, 10, 2);

// see https://facetwp.com/help-center/using-facetwp-with/searchwp/
add_filter( 'searchwp\swp_query\args', function( $args ) {
  if ( isset( $args['facetwp'] ) ) {
    $args['posts_per_page'] = -1;
  }
  return $args;
} );

/*
function megamenu_add_fontawesome_pro_icons($icons) {
	$icons["fa-f8d4"] = "fa-sharp fa-light fa-piano";

	return $icons;
}
add_filter("megamenu_fontawesome_6_icons", "megamenu_add_fontawesome_pro_icons");
*/

add_action( 'academy_update_active_memb_dbase_cron', 'academy_update_active_memb_dbase' );
function academy_update_active_memb_dbase() {
	global $wpdb;
	include( '/nas/content/live/jazzacademy/willie/infusion_connect.php' );
	
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
	
	$data = array(	'date' => date('Y-m-d'),
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
				);
	$format = array('%s','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d');
	$wpdb->insert('academy_active_memb',$data,$format);
}

add_action( 'academy_events_to_jazzedge_cron', 'academy_send_events_to_jazzedge' );
function academy_send_events_to_jazzedge() {
	global $wpdb;
	$events = $wpdb->get_results( "
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
		" );

	$post = array();
	foreach ($events AS $event){
		$post_id = $event->ID;
		$post[$event->event_timestamp]['post_id'] .= $post_id;
		$post[$event->event_timestamp]['event_title'] = $event->post_title;
		$post[$event->event_timestamp]['event_date'] = $event->event_date;
		$post[$event->event_timestamp]['event_type'] = $event->event_type;
		$post[$event->event_timestamp]['class_type'] = $event->class_type;
		$post[$event->event_timestamp]['permalink'] = get_permalink($post_id);
		$post[$event->event_timestamp]['event_timestamp'] = $event->event_timestamp;
	}
	
//*** GET OFFICE HOURS
		$args = array(
		'post_type' => 'office-hours',
		'post_status' => 'publish',
		'meta_key' => 'office_hours_date',
		'meta_value' => date('Y-m-d'),
		'meta_compare' => '>=',
		'orderby' => 'meta_value',
		'posts_per_page' => 2,
		'order' => 'ASC',
		); 
		$events = get_posts( $args );
		if ( $events ) {
			foreach ( $events as $event ) {
				$post_id = $event->ID;
				$event_date = get_post_meta( $event->ID, 'office_hours_date', true );
				$timestamp = strtotime($event_date);
				$permalink = get_the_permalink($event->ID);
				
				$post[$timestamp]['post_id'] .= $post_id;
				$post[$timestamp]['event_title'] = $event->post_title;
				$post[$timestamp]['event_date'] = $event_date;
				$post[$timestamp]['event_type'] = 'office-hours';
				$post[$timestamp]['class_type'] = 'office-hours';
				$post[$timestamp]['permalink'] = $permalink;
				$post[$timestamp]['event_timestamp'] = $timestamp;
			}
		}
//***


//*** GET PREMIER CLASSES
		$return_limit = ($limit > 0) ? $limit : 5;
		$args = array(
            'post_type' => 'premier-class',
            'post_status' => 'publish',
            'meta_key' => 'premier_class_release_date',
            'orderby' => 'meta_value',
            'posts_per_page' => $return_limit,
            'order' => 'ASC',
            'meta_query' => array(
                    array(
                        'key' => 'premier_class_release_date',
                        'value' => date('Y-m-d'),
                        'compare' => '>='
                    ),
                    array(
                        'key' => 'premier_class_type',
                        'value' => 'teacher-checkin',
                        'compare' => '='
                    )                   
                ),
			); 
		$events = get_posts( $args );
		if ( $events ) {
			foreach ( $events as $event ) {
				$post_id = $event->ID;
				$event_date = get_post_meta( $event->ID, 'premier_class_release_date', true );
				$timestamp = strtotime($event_date);
				$permalink = 'https://jazzedge.academy/premier-courses/';
				
				$post[$timestamp]['post_id'] .= $post_id;
				$post[$timestamp]['event_title'] = $event->post_title;
				$post[$timestamp]['event_date'] = $event_date;
				$post[$timestamp]['event_type'] = 'premier-class';
				$post[$timestamp]['class_type'] = 'premier-class';
				$post[$timestamp]['permalink'] = $permalink;
				$post[$timestamp]['event_timestamp'] = $timestamp;
			}
		}
//***


//*** GET WEBINARS
		$return_limit = ($limit > 0) ? $limit : 5;
		$args = array(
            'post_type' => 'webinars',
            'post_status' => 'publish',
            'meta_key' => 'webinar_date',
            'meta_value' => date('Y-m-d'),
            'meta_compare' => '>=',
            'orderby' => 'meta_value',
            'posts_per_page' => $return_limit,
            'order' => 'ASC',
			); 
		$events = get_posts( $args );
		if ( $events ) {
			foreach ( $events as $event ) {
				$post_id = $event->ID;
				$event_date = get_post_meta( $event->ID, 'webinar_date', true );
				$timestamp = strtotime($event_date);
				$permalink = get_the_permalink($event->ID);
				
				$post[$timestamp]['post_id'] .= $post_id;
				$post[$timestamp]['event_title'] = $event->post_title;
				$post[$timestamp]['event_date'] = $event_date;
				$post[$timestamp]['event_type'] = 'webinar';
				$post[$timestamp]['class_type'] = 'webinar';
				$post[$timestamp]['permalink'] = $permalink;
				$post[$timestamp]['event_timestamp'] = $timestamp;
			}
		}
//***


	
	$url = 'https://jazzedgecrm.com/willie/fluent_api/fluent_receive_events_post.php?c=63kfDgjjkl4532d	';
	$response = wp_remote_post( $url, array(
		'body'    => $post,
	) );
	return $response;
}


/***************** FLUENT FORMS ****************/

add_filter('fluentform/editor_shortcodes', function ($smartCodes) {
    $smartCodes[0]['shortcodes']['{pc_course_teacher}'] = 'Premier Course Teacher (fname)';
    return $smartCodes;
});

/*
 * To replace dynamic new smartcode the filter hook will be
 * fluentform/editor_shortcode_callback_{your_smart_code_name}
 */
add_filter('fluentform/editor_shortcode_callback_pc_course_teacher', function ($value, $form) {
	$post_id = get_the_ID();
	$premier_course_id = get_field( "premier_course_id", $post_id );
	$premier_course_teacher = get_field( "premier_course_teacher", $premier_course_id );
    return $premier_course_teacher;
}, 10, 2);

add_filter('fluentform/rendering_field_data_select', function ($data, $form) {

    if ($form->id != 16) {
        return $data;
    }
    $course_id = intval($_GET['course_id']);
    if ($course_id < 1) {
        return $data;
    }

    // check if the name attriibute is 'dynamic_dropdown'
    if (\FluentForm\Framework\Helpers\ArrayHelper::get($data, 'attributes.name') != 'song_selection_dropdown') {
        return $data;
    }
    
    if ($course_id > 0) {
    
    	 $videos = get_field( "premier_course_songs", $course_id );
		  if (!empty($videos)) {
			foreach ($videos AS $video) {
			  $song_title = $video['premier_course_song_title'];
			  $songs[]=
                [
                    "label"      => $song_title,
                    "value"      => $song_title,
                    "calc_value" => ""
                ];
			}
		  }
		// We are merging with existing options here
		$data['settings']['advanced_options'] = array_merge($data['settings']['advanced_options'], $songs);
    }
    return $data;
}, 10, 2);

add_filter('fluentform/validate_input_item_select',function ($error,$field){
    return [];
},10,2);

/***************** FLUENT CRM ****************/

// https://developers.fluentcrm.com/modules/contact-profile-section/
add_action('fluentcrm_loaded',  function () {
    $key = 'ja_customer_details';
    $sectionTitle = 'My Custom Section';
    $callback = function($contentArr, $subscriber) {
        $contentArr['heading'] = 'Content Heading';
        $contentArr['content_html'] = "
                       <div>
                            <h4>My Content</h4>
                            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard 
                            dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled ...</p>
                       </div>
               " .$subscriber->user_id; // https://developers.fluentcrm.com/database/

        return $contentArr;
    };
    FluentCrmApi('extender')->addProfileSection( $key, $sectionTitle, $callback);
});


// https://developers.fluentcrm.com/modules/smart-code/
add_action('fluentcrm_loaded', function () {
    $key = 'your_custom_smartcode_group_key';
    $title = 'Your Custom Smartcode Group Title';
    $shortCodes = [
        'code_4' => 'Code 4 Title',
        'code_5' => 'Code 5 Title',
        // ...
    ];
    $callback = function ($code, $valueKey, $defaultValue, $subscriber) {
        if ($valueKey == 'code_4') {
            return 'Code 4 Value';
        }
        if ($valueKey == 'code_5') {
            return 'Code 5 Value'.$subscriber->email;
        }
        return $defaultValue; // default value works in case of invalid value key
    };

    FluentCrmApi('extender')->addSmartCode($key, $title, $shortCodes, $callback);
});

/***************** FLUENT SUPPORT ****************/

add_filter( 'fluent_support/customer_extra_widgets', 'je_support_widget', 40, 2 );        

function je_support_widget($widgets, $customer)
{
//echo '<pre><h2>$customer</h2>'; print_r($customer); echo '</pre>';

	global $wpdb;
	// https://fluentcrm.com/docs/contact-php-api/

	$contactApi = FluentCrmApi('contacts');
	$customer_email = (isset($customer->title) && strlen($customer->title) > 6 ) ? $customer->title : $customer->email;
	$crm_contact = $contactApi->getContact($customer_email);
	if (!empty($crm_contact)) {
		$customData = $crm_contact->custom_fields();
		$keap_id_in_crm = $customData['keap_id'];	
	}
	
	include_once( '/nas/content/live/jazzacademy/willie/infusion_connect.php' );
	$app = new iSDK;
	$app->cfgCon( 'ft217', 'b58ba9eec87128385289385ecc643607e4b1c69b74b5cb36e4957391e4bd13d2' );

	if (!empty($customer_email)) {
		$user = get_user_by( 'email', $crm_contact->email );
		$email = (!empty($crm_contact->email)) ? $crm_contact->email : $customer_email;
		$keap_id = keap_find_contact_id($email); // get from keap directly rather than saved data
		$user_id = $user->ID;
		// $keap_id = ($keap_id_in_crm <= 0) ? get_user_meta( $user_id, 'infusionsoft_user_id', TRUE ) : $keap_id_in_crm;
		
		if ($keap_id <= 0) { // they are not in keap
			$contact_fields_to_find = array('Id','Email','FirstName','LastName','_AcademyLastLogin','_AcademyExpirationDate','_HSPLastLogin','_SPJLastLogin','_JELastLogin','_Password4Microsites','_JazzedgeExpirationDate','_JEMembershipOfferEndDate','_PurchasedSkus0','_PWWDate2Cancel','_PromoCodesUsed');
			$contact =  $app->findByEmail($email,$contact_fields_to_find);
			$keap_id = $contact[0]['Id'];
		}
		
		// get tags from keap
		if ($keap_id > 0) {
			$contact_fields_to_find = array('Id','Email','FirstName','LastName','_AcademyLastLogin','_AcademyExpirationDate','_HSPLastLogin','_SPJLastLogin','_JELastLogin','_Password4Microsites','_JazzedgeExpirationDate','_JEMembershipOfferEndDate','_PurchasedSkus0','_PWWDate2Cancel','_PromoCodesUsed');
			//$contact =  $app->findByEmail($email,$contact_fields_to_find);
			$contact = $app->loadCon($keap_id,$contact_fields_to_find);
			$academy_last_login = convert_infusionsoft_date($contact['_AcademyLastLogin']);

			$returnFields = array( 'ContactId', 'ContactGroup', 'GroupId', 'DateCreated', 'Contact.Groups','Contact.ContactNotes');
			$query = array('ContactId' => $keap_id);
			$tags = $app->dsQuery("ContactGroupAssign",400,0,$query,$returnFields);
			$academy_exp_date = (isset($contact['_AcademyExpirationDate'])) ? date("m/d/Y",strtotime($contact['_AcademyExpirationDate'])) : '';
			$jazzedge_exp_date = (isset($contact['_JazzedgeExpirationDate'])) ? date("m/d/Y",strtotime($contact['_JazzedgeExpirationDate'])) : '';
			$password_for_microsites = (isset($contact['_Password4Microsites'])) ? $contact['_Password4Microsites'] : '';
			$tag_ids = $tags[0]['Contact.Groups'];
			$contact_notes = $tags[0]['Contact.ContactNotes'];
			$tag_ids_array = explode (',',$tag_ids);
		}
		
		
		$dt = date('Y-m-d');
		$first_day_of_month = strtotime(date("Y-m-01"));
		$last_day_of_month = strtotime(date("Y-m-t"));
		
		$login_count_this_month = $wpdb->get_var( "SELECT COUNT(id) FROM memberium_loginlog WHERE username LIKE '$email' AND logintime BETWEEN $first_day_of_month AND $last_day_of_month" );
				
		$data_transfer_from_academy = file_get_contents('https://jazzedge.academy/willie/crm_transfer.php?code=b3pg8Cd8NEoERmYg&email='.$email);
		$ja_user_data = explode('*',$data_transfer_from_academy);
		$ja_user_login = $ja_user_data[0];
		$ja_user_id = $ja_user_data[1];
		
		$c = $wpdb->get_results( "SELECT * FROM academy_user_credits WHERE user_id = $ja_user_id" );
		$users_credits = $c[0]->class_credits;
		// lookup membership status
		
		$memb_academy = "<a href='https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id=$keap_id&Email=$email&redir=/dashboard/' target='_blank'>";
		$memb_hsp = "<a href='https://homeschoolpiano.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=$email&redir=/dashboard/' target='_blank'>";
		$memb_je = "<a href='https://jazzedge.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=$email&redir=/dashboard/' target='_blank'>";
		$memb_spj = "<a href='https://summerpianojam.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=$email&redir=/dashboard/' target='_blank'>";
		$memb_jcm = "<a href='https://jazzchristmasmusic.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=$email&redir=/dashboard/' target='_blank'>";
		$memb_jpl = "<a href='https://jazzpianolessons.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=$email&redir=/dashboard/' target='_blank'>";

		$access = '';
		$access .= (array_intersect(array(9950), $tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<li>'.$memb_academy.'ACADEMY-TRIAL</a></li>' : '' ;
		$access .= (array_intersect(array(9954,9956,9994), $tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<li>'.$memb_academy.'ACADEMY-STUDIO</a> ('.$academy_last_login.')</li>' : '' ;
		$access .= (array_intersect(array(9661), $tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<li>'.$memb_academy.'ACADEMY-FREE</a></li>' : '' ;
		$access .= (array_intersect(array(9653,9657,9807), $tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<li>'.$memb_academy.'ACADEMY-LEGACY</a></li>' : '' ;
		$access .= (array_intersect(array(9827,9819), $tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<li>'.$memb_academy.'ACADEMY-LSN-ONLY</a></li>' : '' ;
		$access .= (array_intersect(array(9903,9905), $tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<li>'.$memb_academy.'ACADEMY-CLASSES-ONLY</a></li>' : '' ;
		$access .= (array_intersect(array(9909,9911), $tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<li>'.$memb_academy.'ACADEMY-COACHING-ONLY</a></li>' : '' ;
		$access .= (array_intersect(array(9823,9815), $tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<li>'.$memb_academy.'ACADEMY-LSN-CLASSES</a></li>' : '' ;
		$access .= (array_intersect(array(9825,9817), $tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<li>'.$memb_academy.'ACADEMY-LSN-COACHING</a></li>' : '' ;
		$access .= (array_intersect(array(9913,9915), $tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<li>'.$memb_academy.'ACADEMY-CLASSES-COACHING</a></li>' : '' ;
		$access .= (array_intersect(array(9821,9813,9659), $tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<li>'.$memb_academy.'ACADEMY-PREMIER</a> ('.$academy_last_login.')</li>' : '' ;
		$access .= (array_intersect(array(8859,8861), $tag_ids_array) && empty(array_intersect(array(8701,7772),$tag_ids_array))) ? '<li>'.$memb_je.'NBP</a></li>' : '' ;
		$access .= (array_intersect(array(8645,8817,8649), $tag_ids_array) && empty(array_intersect(array(8701,7772),$tag_ids_array))) ? '<li>'.$memb_je.'JAZZEDGE</a></li>' : '' ;
		$access .= (array_intersect(array(9881,9879,9405,9403), $tag_ids_array) && empty(array_intersect(array(9883,7772),$tag_ids_array))) ? '<li>'.$memb_jpl.'JPL</a></li>' : '' ;
		$access .= (array_intersect(array(7548,7574,7578), $tag_ids_array) && empty(array_intersect(array(7552,7772),$tag_ids_array))) ? '<li>'.$memb_hsp.'HSP</a></li>' : '' ;
		$access .= (array_intersect(array(6800), $tag_ids_array) && empty(array_intersect(array(7772),$tag_ids_array))) ? '<li>'.$memb_jcm.'JCM</a></li>' : '' ;
		$access .= (array_intersect(array(8963), $tag_ids_array) && empty(array_intersect(array(7772),$tag_ids_array))) ? '<li>'.$memb_spj.'SPJ</a></li>' : '' ;
		
		// 7772	****PAYMENT FAILED****
		$access .= (array_intersect(array(7772), $tag_ids_array)) ? '<li><strong style="color:red;">PAYMENT FAILED</strong></li>' : '' ;
		$body_html .= "
		Account: $customer_email<br />
		<ul>
		
		<li><a href='https://ft217.infusionsoft.com/Contact/manageContact.jsp?view=edit&ID=$keap_id&lists_sel=orders' target='_blank'>Open in Keap</a> ($keap_id)</li>
		<li><a href='https://ft217.infusionsoft.com/app/searchResults/searchResults?searchTerm=$customer->first_name%20$customer->last_name' target='_blank'>Find By Name</a> (in Keap)</li>

		<li>
		<a href='https://jazzedge.academy/willie/ja-admin/student.php?action=lookup_student&email=$customer->email' target='_blank'>Open in JA Admin</a>
		</li>
		
		<li>
		<a href='https://mypianoaccount.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=$email&redir=/lesson-downloads/' target='_blank'>MPA Downloads</a> &bull; <a href='https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id=$keap_id&Email=$email&redir=/billing/' target='_blank'>Update Card</a> &bull; <a href='https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id=$keap_id&Email=$email&redir=/success/' target='_blank'>Call</a>
		</li>
		
		</ul>
		Academy Exp: $academy_exp_date (<a href='https://jazzedge.academy/willie/ja-admin/actions.php?action=clear-academy-expiration&contact_id=$keap_id' target='_blank'>Clr</a>)<br />
		Jazzedge Exp: $jazzedge_exp_date

		<h2>Membership Access:</h2>
		<form target='_blank' method='post' action='/willie/fluentsupport/fluent_support_update_academy_pass.php'>
			<input type='hidden' name='action' value='update_password' />
			<input type='hidden' name='code' value='Faj6skcHeGJK2jkl452sdgvj24fDa' />
			<input type='hidden' name='user_id' value='".$ja_user_id."' />
			<input type='hidden' name='email' value='".$ja_user_login."' />
			Set Academy Password: <input type='text' name='password' value='' />
			<button type='submit' class='button blue small'>Set Password</button>
		</form><br />
		Password: $password_for_microsites (<a href='https://jazzedge.academy/willie/crm_update_action.php?code=77b3pg8Cd8NEoERmYg&action=update_password&email=$email&keap_id=$keap_id' target='_blank'>update</a>)<br />
		Login Count This Month: <strong>$login_count_this_month</strong><br />
		<ul style='line-height:80%'>$access</ul>
		<h2>Academy Data:</h2>
		<ul style='line-height:80%'>
			<li>ID: $ja_user_id</li>
			<li>Login: $ja_user_login</li>
			<li>Academy Credits: $users_credits</li>
		</ul>
		<h2>Special Pricing:</h2>
		<ul style='line-height:80%'>
			<li><a href='https://ft217.infusionsoft.com/app/orderForms/ja_monthly_studio_special'>Studio for $39/month</a></li>
			<li><a href='https://ft217.infusionsoft.com/app/orderForms/ja_year_studio_special'>Studio for $390/year</a></li>
			<li><a href='https://ft217.infusionsoft.com/app/orderForms/ja_monthly_premier_special'>Premier for $69/month</a></li>
			<li><a href='https://ft217.infusionsoft.com/app/orderForms/ja_year_premier_special'>Premier for $690/year</a></li>
		</ul>
		
		
		 <style>
		 .fs_tk_card.fs_tk_extra_card, .fs_tk_card.fs_tk_extra_card { max-height: 800px; }	
		</style>
		";

		$widgets['ja_support'] = [
			'body_html' => $body_html
		];
		
		
	} else {
		$widgets['ja_support'] = [
			'body_html' => 'no email - '.$customer_email
		];
	
	}
	
	
	
	return $widgets;

}

//****** FLUENT SUPPORT FUNCTIONS START ****** /



function update_fluent_crm_membership_level ( $user_login, $user ) {
	$memb_data = ja_return_user_membership_data() ?? null;
	$user_membership_name = $memb_data['membership_name'] ?? null;
	$keap_id = $memb_data['keap_id'] ?? null;
	$contactApi = FluentCrmApi('contacts');
	$data = [
		'email' => $user_login, // requied
		'custom_values' => [
			'academy_membership_level' => $user_membership_name,
			'keap_id' => $keap_id,
		]
	];
	$contact = $contactApi->createOrUpdate($data);
}
add_action('wp_login', 'update_fluent_crm_membership_level', 99, 2);


function fluent_update_custom_field($custom_field,$custom_value) {

	$current_user = wp_get_current_user();
	$username = $current_user->user_login;
	$user_email = $current_user->user_email;
	$user_fname = $current_user->user_firstname;
	$user_lname = $current_user->user_lastname;
	$user_id = $current_user->ID;

	if ($user_id <= 0) { return; }
	
	$contactApi = FluentCrmApi('contacts');
	
	$data = [
		'email' => $username, // requied
		'custom_values' => [
			$custom_field => $custom_value,
		]
	];

	$contact = $contactApi->createOrUpdate($data);
}

function return_permalink($post_id = 0) {
	if ($post_id < 1) {
		$post_id = get_the_ID();
	}
	return get_the_permalink($post_id);
}



//****** FLUENT BOOKING FUNCTIONS  ****** /

/* NOT USING THE BOOKING SYSTEM BUT SAVE
add_action( 'fluent_booking/booking_details_header', 'fluent_booking_header' );
function fluent_booking_header(){
    echo '<div style="text-align: center; margin-bottom: 25px;"><a href="/dashboard"><img src="https://jazzedge.academy/wp-content/uploads/2023/04/jazzedge-academy-logo-500px.png" /></a><br /><a href="/dashboard">Click Here To Go Back To Your Dashboard</a><p>(the following details have been sent to your email address)</p></div>';
}


function fluent_has_booking() {
	global $wpdb, $user_id;
	$bookings = $wpdb->get_results( "SELECT * FROM wp_fluentform_submissions WHERE user_id = $user_id AND payment_status = 'paid'" );
	echo '<ul>';
	
	foreach ($bookings AS $booking) {
		$details = json_decode($booking->response);
	}	
	echo '</ul>';

}
*/

//****** END FLUENT FUNCTIONS  ****** /



add_filter( 'mwai_chatbot_params', 'override_chatbot_params', 10, 1 );

function je_wp_is_mobile() {
    static $is_mobile;

    if ( isset($is_mobile) )
        return $is_mobile;

    if ( empty($_SERVER['HTTP_USER_AGENT']) ) {
        $is_mobile = false;
    } elseif (
        strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false ) {
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


function override_chatbot_params( $atts ) {
	$current_user = wp_get_current_user();
	$username = $current_user->user_login;
	$user_email = $current_user->user_email;
	$user_fname = $current_user->user_firstname;
	$user_lname = $current_user->user_lastname;
	$user_id = $current_user->ID;
  $atts['context'] = "Converse as if you were Meowy, a futuristic cat who promotes good manners and eco-friendly behavior to save the earth.";
  $atts['ai_name'] = "Jazzedge AI: ";
  $year = date('Y');
  $atts['start_sentence'] = "Hello ".$user_fname.", I am your AI assitant. Ask me any music questions you have!";
  return $atts;
}

add_filter( 'mwai_ai_reply', function ( $reply, $query ) {
	global $wpdb;
	foreach ($query AS $k => $v) {
		$test .= "key=$k value=$v\n\n";
		if ($k == 'messages') {
			foreach ($v AS $d) {
				$t .= $d;
			}
		}
	}
	
	$data = array(	
		'query' => $t, 
		'datetime' => date('Y-m-d h:i:s'),
	);
	$format = array('%s','%s');
	$wpdb->insert('academy_chatgpt_queries',$data,$format);

	  // If not in the mood for a chat...
	  $reply->result = "I don't know-wm";
	  return $reply;
}, 10, 2 );


/****************************************************************************************************************
//****************************************************************************************************************
// 	START USER ACCESS FUNCTIONS
//****************************************************************************************************************
// ****************************************************************************************************************/


function ja_is_14_trial_member(){
	global $wpdb, $user_id, $user_membership_level, $user_membership_level_num;
	if ($user_membership_level === '14daytrial') {
		return 'true';
	} else {
		return 'false';
	}
}

function ja_is_free_member(){
	global $wpdb, $user_id, $user_membership_level, $user_membership_level_num;
	if ($user_membership_level === 'free' && $user_membership_level_num <= 0) {
		return 'true';
	} else {
		return 'false';
	}
}


function ja_return_user_membership_level_num() {
	global $wpdb, $user_id, $user_membership_level, $user_membership_level_num;
	return $user_membership_level_num;
}

function ja_limit_player() {
	$post_id = get_the_ID();
	$limit = 'true';
	if (je_return_active_member() == 'true' || je_check_academy_credit_access() == 'true' || $post_id == 548 || $post_id == 547 || $post_id == 587) { $limit = 'false'; }
	if (ja_is_classes_only_member() === 'true') { $limit = 'true'; }
	return $limit;
}

function je_has_lesson_access() {
	global $wpdb, $user_id, $user_membership_level, $user_membership_level_num, $lesson_post_id;
	if ($lesson_post_id === 587 || $lesson_post_id === 548 || $lesson_post_id === 547) { return 'true'; } // 30-day playbook

	if (empty($user_id)) { return 'false'; }
	$lesson_id = get_the_ID();
	$purchased_lesson = $wpdb->get_var( "SELECT ID FROM academy_credit_purchases WHERE user_id = $user_id AND post_id = $lesson_id" );

	if (empty($purchased_lesson)) { 
		$purchased_lesson = $wpdb->get_var( "SELECT ID FROM academy_user_credit_log WHERE user_id = $user_id AND post_id = $lesson_id" );
	}
	
	if (!empty($purchased_lesson)) { 
		return 'true'; 
	}

	if ($user_membership_level === '14daytrial' || $user_membership_level === 'studio' || $user_membership_level === 'lessons' || $user_membership_level_num >= 3) { return 'true'; }
	
	
	return 'false';
}

function je_has_class_access() {
	global $wpdb, $user_id, $user_membership_level, $user_membership_level_num;
	$class_id = intval($_GET['id']);
	$purchased_lesson = $wpdb->get_var( "SELECT ID FROM academy_credit_purchases WHERE user_id = $user_id AND post_id = $class_id" );

	if (empty($purchased_lesson)) { 
		$purchased_lesson = $wpdb->get_var( "SELECT ID FROM academy_user_credit_log WHERE user_id = $user_id AND post_id = $class_id" );
	}
	
	if (!empty($purchased_lesson)) { 
		return 'true'; 
	}

	if ($user_membership_level === '14daytrial' || $user_membership_level === 'studio' || $user_membership_level === 'classes' || $user_membership_level_num >= 3) { return 'true'; }
		
	return 'false';
}


function ja_return_user_membership_data(){
/* wmyette */
	global $user_id;
	$memb_data = array();
	$keap_data = $_SESSION['keap']['contact'] ?? null;
	if (!empty($keap_data)) {
		$memb_data['fname'] = $keap_data['firstname'];
		$memb_data['lname'] = $keap_data['lastname'];
		$memb_data['email'] = $keap_data['email'];
		$memb_data['tags'] = $keap_data['groups'];
		$memb_data['keap_id'] = $keap_data['id'];
	}
	
	$session = $_SESSION['memb_user']['membership_names'] ?? null;
	if (!empty($session)) {
		$memb_data['user_id'] = $_SESSION['memb_user']['user_id'];
		$memb_data['username'] = $_SESSION['memb_user']['LoginName'];
		$session_memberships = explode(',',$session);
	}
	
	$memberships = array(
		'ACADEMY_FREE' => 'Free Trial,0,free', 
		'ACADEMY_SONG' => 'Song,2,lessons', 
		'ACADEMY_ACADEMY' => 'Academy,2,lessons', 
		'ACADEMY_ACADEMY_NC' => 'Academy NC,2,lessons', 
		'JA_MONTHLY_LSN_CLASSES' => 'Lessons & Classes,3,lessons_classes', 
		'JA_MONTHLY_LSN_COACHING' => 'Lessons & Coaching,3,lessons_classes', 
		'JA_MONTHLY_LSN_ONLY' => 'Lessons Only,2,lessons', 
		'JA_MONTHLY_CLASSES_ONLY' => 'Classes Only,2,classes',
		'JA_MONTHLY_PREMIER' => 'Premier (All Access),4,premier', 
		'JA_YEAR_LSN_CLASSES' => 'Lessons & Classes,3,lessons_classes', 
		'JA_YEAR_LSN_COACHING' => 'Lessons & Coaching,3,lessons_classes', 
		'JA_YEAR_LSN_ONLY' => 'Lessons Only,2,lessons', 
		'JA_YEAR_CLASSES_ONLY' => 'Classes Only,2,classes', 
		'JA_MONTHLY_STUDIO' => 'Studio,2,studio', 
		'JA_YEAR_STUDIO' => 'Studio,2,studio', 
		'JA_YEAR_PREMIER' => 'Premier (All Access),4,premier',
		'ACADEMY_STUDIO' => 'Studio,2,studio', 
		'ACADEMY_PREMIER' => 'Premier (All Access),4,premier', 
	);
	
	if (!empty($session_memberships)) {
		foreach($memberships AS $sku => $data) {
		
			if (in_array($sku,$session_memberships)) {
				$d = explode(',',$data);
				$memb_data['membership_name'] = $d[0];
				$memb_data['membership_product'] = $sku;
				$memb_data['membership_numeric'] = $d[1];
				$memb_data['membership_level'] = $d[2];
			}
		}
	}
	/*
	if ($user_id === 6756) {
		echo '<pre><h2>$session</h2>'; print_r($session); echo '</pre>';
		echo '<pre><h2>keap_data</h2>'; print_r($keap_data); echo '</pre>';
		echo '<pre><h2>$memb_data</h2>'; print_r($memb_data); echo '</pre>';
	}
	*/
	return $memb_data;
}

function je_return_active_member(){
	if (je_return_membership_expired() == 'true') { return 'false'; }
	elseif (memb_hasMembership('ACADEMY_PREMIER') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('ACADEMY_STUDIO') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_MONTHLY_CLASSES_ONLY') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_MONTHLY_LSN_ONLY') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_LESSONS_90DAYS') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('ACADEMY_SONG') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('ACADEMY_ACADEMY') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('ACADEMY_ACADEMY_1YR') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('ACADEMY_ACADEMY_NC') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_MONTHLY_LSN_CLASSES') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_MONTHLY_LSN_COACHING') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_MONTHLY_PREMIER') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_YEAR_LSN_CLASSES') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_YEAR_LSN_COACHING') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_YEAR_LSN_ONLY') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_YEAR_CLASSES_ONLY') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_YEAR_PREMIER') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_YEAR_PREMIER') == TRUE) { return 'true'; }
	else { return 'false'; }
}

function ja_is_premier() {
	if (memb_hasMembership('JA_MONTHLY_PREMIER') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_YEAR_PREMIER') == TRUE) { return 'true'; }
}

function ja_is_jazzedge_member() {
	if (memb_hasAnyTags(array(8817,8649,8645,8859,8861)) == TRUE) { return 'true'; }
	else { return 'false'; }
}

function ja_is_jpl_member() {
	if (memb_hasAnyTags(array(9403,9405)) == TRUE) { return 'true'; }
	else { return 'false'; }
}

function ja_is_hsp_member() {
	if (memb_hasAnyTags(array(7548,7574,7578)) == TRUE) { return 'true'; }
	else { return 'false'; }
}

function ja_is_classes_only_member() {
	if (memb_hasMembership('JA_YEAR_CLASSES_ONLY') == TRUE) { return 'true'; }
	elseif (memb_hasMembership('JA_MONTHLY_CLASSES_ONLY') == TRUE) { return 'true'; }
	else {return 'false';}
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
 
add_action( 'wp_footer', function() {
  ?>
    <script>
      document.addEventListener('facetwp-loaded', function() {
        if (! FWP.loaded) { // initial pageload
          FWP.hooks.addFilter('facetwp/ajax_settings', function(settings) {
            settings.headers = { 'X-WP-Nonce': FWP_JSON.nonce };
            return settings;
          });
        }
      });
    </script>
  <?php
}, 100 );


add_filter( 'facetwp_builder_dynamic_tag_value', function( $tag_value, $tag_name, $params ) {
  if ( 'has_credit_access' == $tag_name ) {
  		global $wpdb;
		$user_id = get_current_user_id();
		$is_free = get_field( "free_event" );
  		$found = $wpdb->get_var( "SELECT post_id FROM academy_user_credit_log WHERE user_id = $user_id AND post_id = ".intval($params['post']->ID)."" );
		if ($found > 0 || $is_free === 'y') {
	   	 $tag_value = '<div style="text-align: center; font-size: 12pt; color:white; width: 100%; background: green; padding: 2px; margin-bottom: 0px;">
		<i class="fa-sharp fa-solid fa-circle-check"></i> You Can Access This Class
		</div>';
	   }
  }
   
  return $tag_value;
}, 10, 3 );

add_filter( 'facetwp_builder_dynamic_tags', function( $tags, $params ) {
	$post_id = $params['post']->ID;
	$post_content = wp_strip_all_tags($params['post']->post_content);
	$lesson_id = get_field( 'lesson_id', $post_id);
	global $wpdb, $user_id, $user_membership_level, $user_membership_level_num, $user_membership_product;
	$lesson_id = get_field( 'lesson_id', $post_id);
	/*
	$has_credit_access = je_check_credit_used_for_class($post_id);
	if ($has_credit_access === 'true') {
		$tags['has_credit_access'] = '<div style="text-align: center; font-size: 12pt; color:yellow; position: absolute; top: 23px; left: 0px; width: 100%; background: #000; padding: 2px; margin-bottom: 0px;">
		<i class="fa-sharp fa-solid fa-circle-check"></i> You Have Access To This Class
		</div>';
	}
	*/
	
	if ($user_id == 5675) {
	//echo '<pre><h2>$params</h2>'; print_r($params); echo '</pre>';
	$student_purchased_classes = $wpdb->get_results( "SELECT post_id FROM academy_user_credit_log WHERE user_id = $user_id" );
		foreach ($student_purchased_classes AS $id) {
			$test .= $id->post_id . ',';
		}
		$tags['test'] = $test;
	//echo '<pre><h2>$students_purchased_classes</h2>'; print_r($test); echo '</pre>';

	}
	/* not needed but keep in case we want to use this code
	if ($user_id > 0) {
		if ($user_membership_product == 'ACADEMY_ACADEMY' || $user_membership_product == 'ACADEMY_ACADEMY_NC') { 
				$required_membership_level = get_field( "required_membership_level" );
				if ($required_membership_level <= 2) {
				$tags['academy_message'] = '<div class="academy-access-message">You have access to this session.</div>'; 
			}	else { $tags['academy_message'] = ''; }
		}
	
		if ($user_membership_product == 'ACADEMY_PREMIER') { 
				$required_membership_level = get_field( "required_membership_level" );
				if ($required_membership_level == 3) {
				$tags['premier_message'] = '<div class="academy-access-message">This is a PREMIER/COACHING-level session.</div>'; 
			}	else { $tags['premier_message'] = ''; }
		}
	}
	*/	
	$teacher = get_field( "teacher", $post_id );
        if ($teacher == 'willie') {
        	$tags['coaching_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/coaching-willie.jpg';
        } elseif ($teacher == 'paul') {
        	$tags['coaching_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/coaching-paul.jpg';
        } else {
        	$tags['coaching_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/event-thumbnail-1.jpg';
		}   
		
	$class_type = get_field( "class_type", $post_id );
        if ($class_type == 'music-history') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/jazz-history-class.jpg';
        } elseif ($class_type === 'music-notation') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/sibelius-class.jpg';
        } elseif ($class_type === 'cpt-theory') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/11/CPT-Theory.png';
        } elseif ($class_type === 'cpt-standards') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/11/CPT-Standards.png';
        } elseif ($class_type === 'cpt-improvisation') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/11/CPT-Improvisation.png';
        } elseif ($class_type === 'standards-beg') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/10/willie-just-standards-new.png';
        } elseif ($class_type === 'standards-adv') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/10/willie-just-standards-new.png';
        } elseif ($class_type === 'improvisation') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/improvisation-class.jpg';
        } elseif ($class_type === 'jazz-piano') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/jazz-piano-class.jpg';
        } elseif ($class_type === 'pop-rock') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/pop-rock-class.jpg';
        } elseif ($class_type === 'transcription') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/transcription-class.jpg';
        } elseif ($class_type === 'rhythm-training') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/rhythm-class.jpg';
        } elseif ($class_type === 'vocal-training') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/vocal-class.jpg';
        } elseif ($class_type === 'music-theory') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/04/music-theory-paul-small.jpg';
        } elseif ($class_type === 'latin-jazz') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/04/latin-jazz-nina-small.jpg';
        } elseif ($class_type === 'willie-coaching') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/coaching-willie.jpg';
        } elseif ($class_type === 'academy-coaching') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/07/paul-coaching.png';
        } elseif ($class_type === 'premier-coaching') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/07/paul-coaching.png';
		} elseif ($class_type === 'tci-beg') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/10/tci-all.png';
		} elseif ($class_type === 'tci-adv') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/10/tci-all.png';
		} elseif ($class_type === 'practical-theory') {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/09/willie-practical-theory-small.png';
		} else {
        	$tags['class_thumbnail'] = 'https://jazzedge.academy/wp-content/uploads/2023/03/event-thumbnail-1.jpg';
        }
       
        
	/* Not using but save in case we need any of this code.....
	if ($user_id > 0 & $show == 1) {
		$favorite = $wpdb->get_var( "SELECT user_id FROM academy_favorites WHERE course_or_lesson_id = $lesson_id AND type = 'lesson' AND user_id = $user_id" ); 
		$q = $wpdb->get_row( "SELECT * FROM academy_lessons WHERE ID = $lesson_id " );
		$course_id = intval($q->course_id);
		$lesson_title = $q->lesson_title;
		$tags['je_lesson_title'] = $lesson_title;
		$tags['je_lesson_skill_level'] = get_post_meta( $post_id, 'lesson_skill_level', true );
		$tags['je_course_id'] = $course_id; 
		$tags['je_course_title'] = je_return_course_title($course_id); // not working on mobile using {{ post:title }}
		$tags['je_course_url'] = je_get_permalink($course_id,'course');
		$tags['je_favorite_lesson'] .=
		'<form method="post">';
		if (!empty($favorite)){
		$tags['je_favorite_lesson'] .= '
		<input type="hidden" name="action" value="unfavorite" />
		<input type="hidden" name="lesson_id" value="'.$lesson_id.'" />
		<button type="submit" class="button" >
			<span class="button-favorite"><i class="fa-solid fa-star"></i></span>
		</button>';
		} else { 
		$tags['je_favorite_lesson'] .= '<input type="hidden" name="action" value="favorite" />
		<input type="hidden" name="lesson_id" value="'.$lesson_id.'" />
		<button type="submit" class="button">
			<span class="button-favorite"><i class="fa-regular fa-star"></i></span>
		</button>';
		} 
		$tags['je_favorite_lesson'] .= '</form>';
	
		$percent_complete = je_return_lesson_progress_percentage($lesson_id);
		$show_percent_complete = ($percent_complete > 0) ? '<span class="percent-complete-circle">'.$percent_complete.'</span>' : '';

		$percent_complete_course = je_return_course_progress_percentage($course_id);
		$show_percent_complete_course = ($percent_complete_course > 0) ? '<span class="percent-complete-circle">'.$percent_complete_course.'</span>' : '';

		$tags['je_percent_complete'] = $show_percent_complete;
		$tags['je_percent_complete_course'] = $show_percent_complete_course;
	}
	*/
	//$excerpt = get_the_excerpt();
	//$truncated_excerpt = mb_strimwidth($excerpt,0,200,"...");
	$event_date = get_field("event_date", $post_id);
	$user_timezone = (isset($_SESSION['user-timezone'])) ? $_SESSION['user-timezone'] : 'America/New_York';
	$event_date_formatted = format_timezone($event_date,$user_timezone);
	$tags['event_date_formatted'] = $event_date_formatted;
	$tags['user_timezone'] = $user_timezone;
	$tags['event_id'] = $post_id;

	$event_subtitle = get_field( "event_subtitle" );
	$event_subtitle_output = (!empty($event_subtitle)) ? '<u>Focus</u>: '.$event_subtitle : '';
	$event_song = get_field( "event_song" );
	$event_song_output = (!empty($event_song)) ? '<br /><u>Song</u>: <strong>'.$event_song .'</strong>' : '';
	// Quiz
	$has_quiz = get_field( "event_has_quiz", $post_id );
	$event_has_quiz = ($has_quiz[0] === 'y') ? '<br /><u>Quiz</u>: <strong style="color:green;">Yes</strong>' : '';

	$prep_lesson_vimeo_id = get_field( "prep_lesson_vimeo_id" );
	$event_has_prep_lesson = ($prep_lesson_vimeo_id > 0) ? ' // <u>Prep Lesson</u>: <strong style="color:green;">Yes</strong>' : '';
	
		
	$truncated_excerpt = mb_strimwidth($post_content,0,120,"...");
	$tags['lesson_excerpt'] = '<div class="lesson-grid-description" style="font-size: 12pt; padding: 0px;">'.$truncated_excerpt;
	if (!empty($event_date) && !empty($event_song) || !empty($event_date) && !empty($event_focus)) {
		$tags['lesson_excerpt'] .= '<div style="margin-top: 10px; font-size: 11pt; padding: 5px; border: 1px solid #ccc;">'.$event_subtitle_output.$event_song_output.$event_has_quiz.$event_has_prep_lesson.'</div>';
	}
	$tags['lesson_excerpt'] .= '</div>';



    return $tags;
}, 10, 2 );

add_filter( 'facetwp_use_search_relevancy', '__return_false' );

add_filter("retrieve_password_message", "jazzedge_custom_password_reset", 99, 4);
function jazzedge_custom_password_reset($message, $key, $user_login, $user_data )    {
  $message = "Someone has requested a password reset for the following account:
" . sprintf(__('%s'), $user_data->user_email) . "

If this was a mistake, you can just ignore this email and nothing will happen to your account.

To reset your password, visit the following address:

" . "https://jazzedge.academy/wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login) . "\r\n" . "

If you have any further issues, please either reply to this email, or email us at support@jazzedge.com

The Jazzedge Academy Team";
  return $message;
}

add_filter( 'retrieve_password_title',
  function( $title )
  {
    $title = __( 'Password reset for Jazzedge Academy' );
    return $title;
  }
);


function get_facet_tag($lesson_tag) {
    global $wpdb;
    $facet_value = $wpdb->get_var( "SELECT facet_value FROM wp_facetwp_index WHERE facet_display_value = '$lesson_tag'" );
    return $facet_value;
  }

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @param boole $img True to return a complete IMG tag False for just the URL
 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
 * @return String containing either just a URL or a complete image tag
 * @source https://gravatar.com/site/implement/images/php/
 */
function get_gravatar( $email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = array() ) {
    $url = 'https://www.gravatar.com/avatar/';
    $url .= md5( strtolower( trim( $email ) ) );
    $url .= "?s=$s&d=$d&r=$r";
    if ( $img ) {
        $url = '<img src="' . $url . '"';
        foreach ( $atts as $key => $val )
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';
    }
    return $url;
}

function returnTheYear(){
	return date('Y');
}
function returnCurrentDate(){
	return date('Y-m-d h:i:s');
}
function returnYMD(){
	return date('Ymd');
}

function time2str($ts)
{
    if(!ctype_digit($ts))
        $ts = strtotime($ts);

    $diff = time() - $ts;
    if($diff == 0)
        return 'now';
    elseif($diff > 0)
    {
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 60) return 'just now';
            if($diff < 120) return '1 minute ago';
            if($diff < 3600) return floor($diff / 60) . ' minutes ago';
            if($diff < 7200) return '1 hour ago';
            if($diff < 86400) return floor($diff / 3600) . ' hours ago';
        }
        if($day_diff == 1) return 'Yesterday';
        if($day_diff < 7) return $day_diff . ' days ago';
        if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
        if($day_diff < 60) return 'last month';
        return date('F Y', $ts);
    }
    else
    {
        $diff = abs($diff);
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 120) return 'in a minute';
            if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
            if($diff < 7200) return 'in an hour';
            if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
        }
        if($day_diff == 1) return 'Tomorrow';
        if($day_diff < 4) return date('l', $ts);
        if($day_diff < 7 + (7 - date('w'))) return 'next week';
        if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
        if(date('n', $ts) == date('n') + 1) return 'next month';
        return date('F Y', $ts);
    }
}


function ja_circle_image($image = '') {
	$return = '<img src="'.$image.'" style="width:100%; height:100%; border-radius: 50%;" />';
	return $return;
}


function ja_teacher_headshot($teacher = 'willie', $show_style = '',  $class = '', $width = '100%', $height = '100%') {

	if ($show_style === 'true' || empty ($show_style)) { $style = " style = 'width:".$width."; height:".$height."'"; }
switch (strtolower($teacher)) {
	case 'willie':
		$teacher_img = '<img '.$style.' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-willie-smaller.png" class="'.$class.'" alt="Teacher - Willie Myette">';
		break;
	case 'paul':
		$teacher_img = '<img '.$style.' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-paul-smaller.png" class="'.$class.'" alt="Teacher - Paul Buono">';
		break;
	case 'nina':
		$teacher_img = '<img '.$style.' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-nina-smaller.png" class="'.$class.'" alt="Teacher - Nina Ott">';
		break;
	case 'anna':
		$teacher_img = '<img '.$style.' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-anna-smaller.png" class="'.$class.'" alt="Teacher - Anna Rizzo">';
		break;
	case 'darby':
		$teacher_img = '<img '.$style.' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-darby-smaller.png" class="'.$class.'" alt="Teacher - Darby Wolf">';
		break;
	case 'mike':
		$teacher_img = '<img '.$style.' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-mike-smaller.png" class="'.$class.'" alt="Teacher - Mike Marble">';
		break;
	case 'john':
		$teacher_img = '<img '.$style.' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-john-smaller.png" class="'.$class.'" alt="Teacher - John McKenna">';
		break;
	default:
		$teacher_img = '<img '.$style.' src="https://jazzedge.academy/wp-content/uploads/2024/01/academy-headshot-willie-smaller.png" class="'.$class.'" alt="Teacher - Willie Myette">';	
		break;
}	
return $teacher_img;

}
	
	
function date_future_or_past($date_to_check) {
	$date_to_check_timestamp = strtotime($date_to_check) + (60 * 60 * 5);
	$diff = $date_to_check_timestamp - time();
	/*
	echo "date_to_check_timestamp = $date_to_check_timestamp<br />";
	echo 'current timestamp = '.time().'<br />';
	echo 'diff = '.$diff.'<br />';
	*/
	return ($diff > 0) ? 'future' : 'past';
}

function sale_percentage_saved($retail_price = 0, $sale_price = 0, $return = 'decrease') {
	$decrease = ($retail_price - $sale_price) / $retail_price * 100;
	$increase = ($retail_price - $sale_price) / $sale_price * 100;
	return ($return === 'decrease') ? ceil(round($decrease,2)) : ceil(round($increase,2));
}

function pc_return_sale_percentage_saved() {
	$post_id = get_the_ID();
	$premier_course_retail_price = get_field( "premier_course_retail_price", $post_id );
	$premier_course_sale_price = get_field( "premier_course_sale_price", $post_id );
	$savings = sale_percentage_saved($premier_course_retail_price,$premier_course_sale_price);
	return ($savings > 0) ? "You Save: $savings%" : '';
}

function pc_return_course_intro_video() {
	$post_id = get_the_ID();
	$premier_course_promo_video_vimeo_id = get_field( "premier_course_promo_video_vimeo_id", $post_id );
	return 'https://vimeo.com/' . $premier_course_promo_video_vimeo_id;
}

function pc_return_sale_active() {
	$post_id = get_the_ID();
	$premier_course_sale_end_date = get_field( "premier_course_sale_end_date", $post_id );
	$sale_active = (date_future_or_past($premier_course_sale_end_date) === 'future') ? 'active' : 'inactive';
	return $sale_active;
}


function premier_course_ready($timestamp = 0){
	if ($timestamp < 1) {
	$post_id = get_the_ID();
	$premier_course_release_date = get_field( "premier_course_release_date", $post_id );
  	return (strtotime($premier_course_release_date) + (60 * 60 * 5) <= time()) ? 'true' : 'false';
	}
	return ($timestamp <= time()) ? 'true' : 'false';
}

function premier_course_ready_permalink(){
	$post_id = get_the_ID();
	if (premier_course_ready() != 'true') { return; }
	$permalink = get_permalink($post_id);
	return $permalink;
}

function premier_class_ready($timestamp = 0){
	if ($timestamp < 1) {
	$post_id = get_the_ID();
	$premier_class_release_date = get_field( "premier_class_release_date", $post_id );
  	return (strtotime($premier_class_release_date) + (60 * 60 * 5) <= time()) ? 'true' : 'false';
	}
	return ($timestamp <= time()) ? 'true' : 'false';
}

function premier_recital_ready($timestamp = 0){
	if ($timestamp < 1) {
	$post_id = get_the_ID();
	$premier_course_recital_date = get_field( "premier_course_recital_date", $post_id );
  	return (strtotime($premier_course_recital_date) + (60 * 60 * 5) <= time()) ? 'true' : 'false';
	}
	return ($timestamp <= time()) ? 'true' : 'false';
}

function tip_ready($timestamp = 0){
	if ($timestamp < 1) {
	$post_id = get_the_ID();
	$tip_release_date = get_field( "tip_release_date", $post_id );
  	return (strtotime($tip_release_date) + (60 * 60 * 5) <= time()) ? 'true' : 'false';
	}
	return ($timestamp <= time()) ? 'true' : 'false';
}

/*
function isDateInPastOrFuture($dateString): string {
    $date = new DateTime($dateString);
    $now = new DateTime();

    if ($date < $now) {
        return 'past';
    } elseif ($date > $now) {
        return 'future';
    } else {
        return 'present';
    }
}
*/
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
    include( '/nas/content/live/jazzacademy/willie/infusion_connect.php' );
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
	include( '/nas/content/live/jazzacademy/willie/infusion_connect.php' );
 	$opt_status = $app->optStatus($email);
	return $opt_status;
}

function keap_has_card() {
    $keap_id = memb_getContactId();
    if ($keap_id < 1) { return ; }
	include( '/nas/content/live/jazzacademy/willie/infusion_connect.php' );
	$returnFields = array('Id','last4','CardType','ExpirationMonth','NameOnCard');
 	$query = array('ContactId' => $keap_id);
 	$card = $app->dsQuery("CreditCard", 1, 0, $query, $returnFields);
	$has_card = (!empty($card[0]['last4'])) ? 'true' : 'false';
	return $has_card;
}


function keap_tag_contact($contact_id,$tag_id) {
	if ($contact_id < 1) { return ; }
    include( '/nas/content/live/jazzacademy/willie/infusion_connect.php' );
	$tagged = $app->grpAssign($contact_id,$tag_id);
	return $tagged;
}

function keap_goal_contact($contact_id,$keap_api_goal) {
	if ($contact_id < 1) { return ; }
    include( '/nas/content/live/jazzacademy/willie/infusion_connect.php' );
	$goal = $app->achieveGoal('ft217', $keap_api_goal, $contact_id); 
	return $goal;
}

function keap_find_contact_id($email_address) {
	if (empty($email_address)) { return ; }
    include( '/nas/content/live/jazzacademy/willie/infusion_connect.php' );
	$contact =  $app->findByEmail($email_address,array('Id'));
	$contact_id = $contact[0]['Id'];
	return $contact_id;
}

function keap_update_contact($contact_id,$update_array) {
	if ($contact_id < 1) { return ; }
    include( '/nas/content/live/jazzacademy/willie/infusion_connect.php' );
	$u = $app->updateCon($contact_id,$update_array);
	return $u;
}

function keap_get_contact_fields($contact_id,$field_array) {
	if ($contact_id < 1) { return ; }
    include( '/nas/content/live/jazzacademy/willie/infusion_connect.php' );
	$u = $app->loadCon($contact_id,$field_array);
	return $u;
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

function vimeo_return_download_link($vimeo_id = 0,$return = 'array'){
	$client = new Vimeo("4b8aa7cfcc3ca72c070d952629bfc5061c459f37", "dUbGlWUkDSZoyPce8ehdnfoxDpAwGeoU5uuxEg0ecrESqGLh7taUehQOZqk8bL3a22xqA2vxt3cvekLIxe39/AhNpokavpKsmDJlr671roBVGCTPG5aDnoBEauCCJDIH", "7d303a30c260a569f0ea69cb01265f9b");
	global $wpdb;
	$response = $client->request('/videos/'.$vimeo_id, array(), 'GET');
	//echo '<pre><h2>$response</h2>'; print_r($response); echo '</pre>';

	$name = $response['body']['name'];
	$remaining_api_calls = $response['headers']['x-ratelimit-remaining'];
	foreach ($response['body']['files'] AS $file) {
		if ($file['quality'] == 'source') {
			$source_download_url = $file['link'];
			//echo 'Source: ' . $source_download_url . '<br>';
		} elseif ($file['quality'] == 'hd' && $file['rendition'] == '1080p') {
			$hd_download_1080_url = $file['link'];
			//echo 'HD 1080: ' . $hd_download_1080_url . '<br>';
		} elseif ($file['quality'] == 'hd' && $file['rendition'] == '720p') {
			$hd_download_720_url = $file['link'];
			//echo 'HD 720: ' . $hd_download_720_url . '<br>';
		} elseif ($file['quality'] == 'sd' && $file['rendition'] == '540p') {
			$sd_download_url = $file['link'];
			//echo 'SD 540: ' . $sd_download_url . '<br>';
		}
	}
	if (!empty($source_download_url)) { $download_url = $source_download_url; }
	elseif (!empty($hd_download_1080_url)) { $download_url = $hd_download_1080_url; }
	elseif (!empty($hd_download_720_url)) { $download_url = $hd_download_720_url; }
	elseif (!empty($sd_download_url)) { $download_url = $sd_download_url; }
	//echo 'RETURN: ' . $download_url . '<br>';
	
	if ($return == 'array') {
		return array('url' => $download_url, 'name' => $name, 'remaining_api_calls' => $remaining_api_calls);
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


function je_return_membership_level($return = 'product'){
	$academy_level = '';
	if (memb_hasMembership('ACADEMY_PREMIER') == TRUE) { 
		if ($return == 'product') { return 'ACADEMY_PREMIER'; }
		elseif ($return == 'nicename') { return 'Premier'; }
		elseif ($return == 'numeric') { return 3; }		
	} elseif (memb_hasMembership('JA_MONTHLY_PREMIER') == TRUE) { 
		if ($return == 'product') { return 'ACADEMY_PREMIER'; }
		elseif ($return == 'nicename') { return 'Premier'; }
		elseif ($return == 'numeric') { return 3; }		
	} elseif (memb_hasMembership('ACADEMY_ACADEMY') == TRUE) {
		if ($return == 'product') { return 'ACADEMY_ACADEMY'; }
		elseif ($return == 'nicename') { return 'Academy'; }
		elseif ($return == 'numeric') { return 2; }	
	} elseif (memb_hasMembership('ACADEMY_ACADEMY_NC') == TRUE) {
		if ($return == 'product') { return 'ACADEMY_ACADEMY_NC'; }
		elseif ($return == 'nicename') { return 'Academy (No Coaching)'; }
		elseif ($return == 'numeric') { return 0; }	
	} elseif (memb_hasMembership('ACADEMY_SONG') == TRUE) {
		if ($return == 'product') { return 'ACADEMY_SONG'; }
		elseif ($return == 'nicename') { return 'Song'; }
		elseif ($return == 'numeric') { return 1; }	
	} elseif (memb_hasMembership('JA_MONTHLY_LSN_CLASSES') == TRUE) {
		if ($return == 'product') { return 'JA_MONTHLY_LSN_CLASSES'; }
		elseif ($return == 'nicename') { return 'Lessons & Classes'; }
		elseif ($return == 'numeric') { return 2; }	
	} elseif (memb_hasMembership('JA_MONTHLY_LSN_COACHING') == TRUE) {
		if ($return == 'product') { return 'JA_MONTHLY_LSN_COACHING'; }
		elseif ($return == 'nicename') { return 'Lessons & Coaching'; }
		elseif ($return == 'numeric') { return 2; }	
	} elseif (memb_hasMembership('JA_MONTHLY_LSN_ONLY') == TRUE) {
		if ($return == 'product') { return 'JA_MONTHLY_LSN_ONLY'; }
		elseif ($return == 'nicename') { return 'Lessons Only'; }
		elseif ($return == 'numeric') { return 2; }	
	} elseif (memb_hasMembership('JA_MONTHLY_CLASSES_ONLY') == TRUE) {
		if ($return == 'product') { return 'JA_MONTHLY_CLASSES_ONLY'; }
		elseif ($return == 'nicename') { return 'Classes Only'; }
		elseif ($return == 'numeric') { return 2; }	
	} elseif (memb_hasMembership('JA_YEAR_LSN_CLASSES') == TRUE) {
		if ($return == 'product') { return 'JA_YEAR_LSN_CLASSES'; }
		elseif ($return == 'nicename') { return 'Lessons & Classes'; }
		elseif ($return == 'numeric') { return 2; }	
	} elseif (memb_hasMembership('JA_YEAR_LSN_COACHING') == TRUE) {
		if ($return == 'product') { return 'JA_YEAR_LSN_COACHING'; }
		elseif ($return == 'nicename') { return 'Lessons & Coaching'; }
		elseif ($return == 'numeric') { return 2; }	
	} elseif (memb_hasMembership('JA_YEAR_LSN_ONLY') == TRUE) {
		if ($return == 'product') { return 'JA_YEAR_LSN_ONLY'; }
		elseif ($return == 'nicename') { return 'Lessons Only'; }
		elseif ($return == 'numeric') { return 2; }	
	} elseif (memb_hasMembership('JA_YEAR_CLASSES_ONLY') == TRUE) {
		if ($return == 'product') { return 'JA_YEAR_CLASSES_ONLY'; }
		elseif ($return == 'nicename') { return 'Classes Only'; }
		elseif ($return == 'numeric') { return 2; }	
	} elseif (memb_hasMembership('ACADEMY_FREE') == TRUE) {
		if ($return == 'product') { return 'ACADEMY_FREE'; }
		elseif ($return == 'nicename') { return 'Free'; }
		elseif ($return == 'numeric') { return 0; }	
	} else { return 0; }
}

function je_return_membership_level_name_from_num($num){
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


/* delete - not using
function encrypt_decrypt($string,$function = 'encrypt') {
	// https://www.geeksforgeeks.org/how-to-encrypt-and-decrypt-a-php-string/
	// Store the cipher method
	$ciphering = "AES-128-CTR";

	// Use OpenSSl Encryption method
	$iv_length = openssl_cipher_iv_length($ciphering);
	$options = 0;

	// Non-NULL Initialization Vector for encryption
	$encryption_iv = '1234567891011121137';
  
	// Store the encryption key
	$encryption_key = "GeeksforGeeks";

	if ($function == 'encrypt') {
		// Use openssl_encrypt() function to encrypt the data
		$encryption = openssl_encrypt($string, $ciphering,
					$encryption_key, $options, $encryption_iv);

		return $encryption;
	} else {
		// Use openssl_decrypt() function to decrypt the data
		$decryption=openssl_decrypt ($string, $ciphering, 
			$encryption_key, $options, $encryption_iv);
		return $decryption;
	}
	
}
*/

function je_link($url = '',$download = 0)
{
	global $wpdb;
	$post_id = intval($_GET['id']);
	$current_user = wp_get_current_user();
	$username = $current_user->user_login;
	$user_id = $current_user->ID;
    $email = $current_user->user_email;
    
	$data = array(	'user_id' => $user_id, 
					'date' => date('Y-m-d'),
					'url' => $url ,
					'ip' => $_SERVER['REMOTE_ADDR'],
					'username' => $username,
					'email' => $email,
				);
	$format = array('%d','%s','%s','%s','%s','%s');
	$found = $wpdb->get_var( "SELECT ID FROM je_link_views WHERE url = '$url' AND username = '$username'" );
	if (empty($found) AND je_check_rental_access($post_id) != 'active') {
		$wpdb->insert('je_link_views',$data,$format);
	}
	$url = 'https://s3.amazonaws.com/jazzedge-resources/'.$url;
	if ($download == 1) {
		force_download($url);
	} else {
    	wp_redirect( $url );
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

function ja_return_lesson_duration($post_id){
	global $wpdb;
	$lesson_id = $wpdb->get_var( "SELECT ID FROM academy_lessons WHERE post_id = ".intval($post_id)."" );	
	$total_lesson_duration = $wpdb->get_var( "SELECT SUM(duration) FROM academy_chapters WHERE lesson_id = $lesson_id" );
 	return format_time($total_lesson_duration);
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
		$chapter_title = $wpdb->get_var( "SELECT chapter_title FROM academy_chapters WHERE ID = $chapter_id" );
		return $chapter_title;
}

function je_return_lesson_title($lesson_id = 0) {
		global $wpdb;
		$lesson_id = intval($lesson_id);
		$lesson_title = $wpdb->get_var( "SELECT lesson_title FROM academy_lessons WHERE ID = $lesson_id" );
		return stripslashes($lesson_title);
}

function je_return_class_title($event_id = 0) {
		global $wpdb;
		$event_id = intval($event_id);
		$q = $wpdb->get_row( "SELECT * FROM academy_event_recordings WHERE event_id = $event_id" );
		return stripslashes($q->title . ' (' . date('F jS, Y',strtotime($q->date)) . ')');
}

function je_return_course_title($course_id = 0) {
		global $wpdb;
		$course_id = intval($course_id);
		$course_title = $wpdb->get_var( "SELECT course_title FROM academy_courses WHERE ID = $course_id" );
		return $course_title;
}

function je_return_path_title($path_id = 0) {
		return get_the_title(intval($path_id));
}

function je_chapter_not_selected($lesson_id = 0) {
		global $wpdb;
		$user_id = get_current_user_id();
		if ($user_id == 1972) { echo 'Viewing as admin'; return; }
		$lesson_id = intval($lesson_id);
		$slug = $wpdb->get_var( "SELECT slug FROM academy_chapters WHERE lesson_id = $lesson_id ORDER BY menu_order ASC" );
		wp_redirect('?c='.$slug);
		exit;
}

function je_return_free_chapter($lesson_id = 0) {
		global $wpdb;
		if ($lesson_id < 1) {
			$lesson_id = get_field( 'lesson_id', get_the_ID());
		}
		$vimeo_id = $wpdb->get_var( "SELECT vimeo_id FROM academy_chapters WHERE lesson_id = $lesson_id AND free= 'y' LIMIT 1" );
		return $vimeo_id;
}


function je_is_chapter() {
	if (je_return_id_from_slug($_GET['c'],'chapter') > 0) {
		return TRUE; 
	} else {
		global $wpdb;
		$slug = sanitize_text_field($_GET['c']);
		$post_id = get_the_ID();
		$lesson_id = get_post_meta( $post_id, 'lesson_id', true );
		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM academy_chapters WHERE lesson_id = ".intval($lesson_id)."" ); 
		if ($count == 1) { return TRUE; } else { return FALSE; }
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

function pc_return_class_percentage_complete($return = 'json', $post_id = 0){
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$practice_actions = array();
	$practice_actions = get_field('practice_actions', $post_id);
	$completed_practice_actions = 0;
	if (is_array($practice_actions)) {
		$total_practice_actions = count($practice_actions);
	}
	$action_id = 1;
	if( $practice_actions ) {
		foreach( $practice_actions as $row ) {
		  $completed = pc_is_practice_action_complete($post_id,$action_id);
		  if ($completed) {
			  $completed_practice_actions++;
		  } 
		$action_id++;
		}
	}
	if ($return === 'truefalse') {
		return ($completed_practice_actions === $total_practice_actions) ? 'true' : 'false';
	}
	if ($return === 'cssclass') {
		//return ($completed_practice_actions === $total_practice_actions) ? 'hide-completed-class' : '';
	}
	if ($return === 'buttonclass') {
		return ($completed_practice_actions === $total_practice_actions) ? 'collapsed' : '';
	}
	$json_array = array($completed_practice_actions,$total_practice_actions);
	return json_encode($json_array);
}

function pc_return_has_premier_course_access($post_id = 0) {
	$user_id = get_current_user_id();
	if ($user_id < 1) { return 'false'; }
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$post_type = get_post_type($post_id);
	$purchased = pc_return_premier_course_purchased($post_id);
	$membership_level = $_SESSION['memb_user']['membership_level'];
	$premier_course_expiration_date = get_field( "premier_course_expiration_date", $post_id, 'false' );
	$course_expired = (isDateInPastOrFuture($premier_course_expiration_date) === 'past') ? 'y' : 'n';
	// has premier access, check if course expired
	if ($membership_level > 21 && $course_expired != 'y') { 
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
	foreach ($classes_array AS $class_id) {
		$class_ids .= $class_id . ',';
	}
	$class_ids = substr($class_ids,0,-1);
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM academy_pc_graded WHERE user_id = $user_id AND class_id IN ($class_ids) AND grade = 'pass';
	" );
	if ($count < 1) { return 0; } else { return $count; }
}


function pc_return_quizzes_completed($return = 'status', $post_id = 0) {
	global $wpdb, $user_id;
	$classes_array = array();
	$user_id = ($user_id > 0) ? $user_id : get_current_user_id();
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	$classes_array = pc_return_course_classs_array($post_id);
	foreach ($classes_array AS $class_id) {
		$class_ids .= $class_id . ',';
	}
	$class_ids = substr($class_ids,0,-1);
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM academy_premier_quiz_results WHERE user_id = $user_id AND premier_class_id IN ($class_ids) AND score >= 70;
	" );
	if ($count < 1) { return 0; } else { return $count; }
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
	if ($user_id < 1) { return; }
	$post_id = ($post_id > 0) ? intval($post_id) : get_the_ID();
	
	$forms = $wpdb->get_results( "SELECT id, response FROM wp_fluentform_submissions WHERE form_id IN (10,23) AND user_id = $user_id AND status != 'redo'" );    
    
	foreach ($forms AS $form){
		$response = (string) $form->response;
		$r =  json_decode($response, true);
		$class_id = $r['class_id'];
		$found = ($class_id == $post_id) ? TRUE : FALSE;
		if ($found) { break; }
	}

	if ($return === 'text') {
		return ($found) ? '<span style="color: #4caf50;">Yes</span>' : 'No';
	}
	if ($return === 'icon') {
		return ($found) ? '<span style="color: #4caf50;"><i class="fa-sharp fa-regular fa-list-check"></i></span> Assignment Complete' : '<i class="fa-sharp fa-regular fa-list-check"></i> Assignment Incomplete';
	}
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
	$score = $wpdb->get_var( "SELECT score FROM academy_premier_quiz_results WHERE user_id = $user_id AND premier_class_id = $post_id " );

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
	$completed = $wpdb->get_var( "SELECT completed FROM practice_actions WHERE user_id = $user_id AND post_id = $post_id AND action_id = $action_id " );
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

function ja_milestone_graded($curriculum_id = 0){
	global $wpdb, $user_id, $pass_or_redo;
	if ($curriculum_id == 0) { return; }
	$q = $wpdb->get_row( "SELECT * FROM je_practice_milestone_submissions WHERE user_id = $user_id AND curriculum_id = $curriculum_id AND grade IS NOT NULL ORDER BY submission_date DESC" );
	$pass_or_redo = $q->grade;
	$grade_color = ($q->grade == 'pass') ? 'green' : 'red';
	$grade = "<p>Grade: <strong style='color:$grade_color;'>$q->grade</strong>";
	if (empty($q->teacher_notes)) { $grade .= '</p>'; }
	else { $grade .= "<div class='teacher_note'><strong>Teacher note</strong>: ".stripslashes($q->teacher_notes)."</div></p>"; }
	return (!empty($q)) ? $grade : '';
}

function ja_milestone_submission_video_url($curriculum_id = 0){
	global $wpdb, $user_id;
	if ($curriculum_id == 0) { return; }
	$video_url = $wpdb->get_var( "SELECT video_url FROM je_practice_milestone_submissions WHERE user_id = $user_id AND curriculum_id = $curriculum_id" );
	return (!empty($video_url)) ? "<a href='$video_url' target='_blank'><i class='fa-sharp fa-solid fa-arrow-up-right-from-square'></i></a>" : '';
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




















