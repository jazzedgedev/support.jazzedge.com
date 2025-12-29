<?php
/*
Plugin Name: Wmyette Shortcodes
Plugin URI: http://jazzedge.com
Description: Shortcodes for Jazzedge/PWW sites
Version: 1.0
Author: Myette
Author URI: http://www.pianowithwillie.com
*/

define('INSTALL', 'jazzacademy');

class Wmyette_Shortcodes {

    function __construct() {
        add_shortcode('TEMPLATE', array($this, 'TEMPLATE'));
        add_shortcode('max_menu_data', array($this, 'max_menu_data'));
        add_shortcode('max_class_replays', array($this, 'max_class_replays'));
        add_shortcode('je_return_facet_listing_template', array($this, 'je_return_facet_listing_template'));
        add_shortcode('facet_truncate_description', array($this, 'facet_truncate_description'));
        add_shortcode('facet_format_skill_level', array($this, 'facet_format_skill_level'));
        add_shortcode('facet_return_replay_thumbnail', array($this, 'facet_return_replay_thumbnail'));
        add_shortcode('je_return_lesson_data', array($this, 'je_return_lesson_data'));
        add_shortcode('je_return_lesson_chapters', array($this, 'je_return_lesson_chapters'));
        add_shortcode('je_return_lesson_resources', array($this, 'je_return_lesson_resources'));
        add_shortcode('je_return_lesson_resources_new', array($this, 'je_return_lesson_resources_new'));
        add_shortcode('je_return_lesson_progress', array($this, 'je_return_lesson_progress'));
        add_shortcode('je_return_chapter_data', array($this, 'je_return_chapter_data'));
        add_shortcode('je_return_course_data', array($this, 'je_return_course_data'));
        add_shortcode('je_return_course_lessons', array($this, 'je_return_course_lessons'));
        add_shortcode('je_return_course_progress', array($this, 'je_return_course_progress'));
        add_shortcode('je_return_path_progress', array($this, 'je_return_path_progress'));
        add_shortcode('je_free_video_player', array($this, 'je_free_video_player'));
        add_shortcode('je_video_player', array($this, 'je_video_player'));
        add_shortcode('je_video_player_new', array($this, 'je_video_player_new'));
        add_shortcode('je_list_active_subscriptions', array($this, 'je_list_active_subscriptions'));
        add_shortcode('ja_support_ticket', array($this, 'ja_support_ticket'));
        add_shortcode('je_cancel_academy_subscription', array($this, 'je_cancel_academy_subscription'));
        add_shortcode('je_list_payments', array($this, 'je_list_payments'));
        add_shortcode('ja_return_user_data', array($this, 'ja_return_user_data'));
    }

    function TEMPLATE($atts, $content = NULL) {
        $atts = shortcode_atts(array('link' => ''), $atts, 'TEMPLATE');
        return $return;
    }

    function max_class_replays($atts, $content = NULL) {
        $atts = shortcode_atts(array('link' => ''), $atts, 'max_class_replays');
        $html = '
        <ul>
            <li><a href="#" class="hover-black">Jazz Piano Class with Nina</a></li>
            <li>Coaching with Paul</li>
            <li>Creative Pianist Toolkit with Willie</li>
        </ul>';
        return $html;
    }

    function max_menu_data($atts, $content = NULL) {
        global $wpdb;
        $atts = shortcode_atts(array('return' => 'user_id', 'limit' => 0, 'charlimit' => 0, 'member_level' => null, 'event_type' => null), $atts, 'max_menu_data');
        $return = $atts['return'];
        $limit = intval($atts['limit']);
        $charlimit = intval($atts['charlimit']);
        $member_level = $atts['member_level'];
        $event_type = $atts['event_type'];

        if ($return === 'user_id') {
            $user_id = get_current_user_id();
            return $user_id;
        } elseif ($return === 'changelog') {
            $q = $wpdb->get_row("SELECT * FROM academy_changelog ORDER BY date DESC LIMIT 1");
            $date = date("F jS, Y", strtotime($q->date));
            return "<div id='max_changelog'><p class='max_changelog_date'>$date</p><p>" . stripslashes($q->log_entry) . '</p></div>';
        } elseif ($return === 'lesson_collections') {
            $courses = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_type = 'course' ORDER BY post_title ASC");
            $html = '<div style="margin-top: 3px"><select id="dynamic_select_lesson_collection2" class="classic"> 
              <option value="" selected>Choose a lesson collection</option>
              <option value="/courses">View All Lesson Collections...</option>';
            foreach ($courses as $course) {
                $permalink = get_permalink($course->ID);
                if ($charlimit > 0) {
                    $html .= '<option value="' . $permalink . '">' . mb_strimwidth($course->post_title, 0, $charlimit, "...") . '</option>';
                } else {
                    $html .= '<option value="' . $permalink . '">' . $course->post_title . '</option>';
                }
            }
            $html .= '</select></div>';
            return $html;
        } elseif ($return === 'lesson_collections_trimmed') {
            $courses = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_type = 'course' ORDER BY post_title ASC");
            $html = '<div style="margin-top: 6px"><select id="dynamic_select_lesson_collection" class="classic"> 
              <option value="" selected>Choose a lesson collection</option>
              <option value="/courses">View All Lesson Collections...</option>';
            foreach ($courses as $course) {
                $permalink = get_permalink($course->ID);
                $html .= '<option value="' . $permalink . '">' . mb_strimwidth($course->post_title, 0, 36, "...") . '</option>';
            }
            $html .= '</select></div>';
            return $html;
        } elseif ($return === 'recent_class_replays') {
            $events = $wpdb->get_results("SELECT * FROM `wp_postmeta` WHERE meta_key = 'event_date' AND meta_value < CURDATE() ORDER BY meta_value DESC LIMIT 5");
            $html = '<div class="pc_container">';
            if ($events) {
                foreach ($events as $e) {
                    $teacher = ucfirst(get_field("teacher", $e->post_id));
                    $date = date("M. jS, Y", strtotime($e->meta_value));
                    $permalink = get_permalink($e->post_id);
                    $event_title = mb_strimwidth(get_the_title($e->post_id), 0, 28, "...");
                    $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot($teacher) . '</div>
                    <div class="pc_description-container-80" style="padding-top: 4px;">
                        <h2><a href="' . $permalink . '">' . $event_title . '</a></h2>
                        <p class="pc_event_subtitle">(' . $date . ') with ' . ucfirst($teacher) . '</p>
                    </div>';
                }
            }
            $html .= '</div>';
            return $html;
        } elseif ($return === 'browse_by') {
            $html .= '
            <li><a href="https://jazzedge.academy/replays/?_search=class&view=grid">View All Class Replays <i class="fa-sharp fa-solid fa-circle-arrow-right"></i></a></li>
            </ul>';
            return $html;
        } elseif ($return === 'upcoming_classes') {
            $meta = $wpdb->get_results("SELECT * FROM `wp_postmeta` WHERE meta_key = 'event_date' AND meta_value >= CURDATE() ORDER BY meta_value ASC LIMIT 8");
            if (empty($meta)) { return; }
            if ($meta) {
                $html = '<div class="pc_container">';
                $x = 1;
                foreach ($meta as $m) {
                    $post_id = $m->post_id;
                    $post_status = get_post_status($post_id);
                    $event_type = get_field("event_type", $post_id);
                    if ($event_type === 'class' && $x <= 5 && $post_status === 'publish') {
                        $event_title = mb_strimwidth(get_the_title($post_id), 0, 28, "...");
                        $teacher = get_field("teacher", $post_id);
                        $event_date = get_field("event_date", $post_id);
                        $permalink = get_the_permalink($post_id);
                        $user_timezone = $_SESSION['user-timezone'];
                        $event_date_utz = format_timezone($event_date, $user_timezone, 'string');
                        $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot($teacher) . '</div>
                        <div class="pc_description-container-80" style="padding-top: 4px;">
                            <h2><a href="' . $permalink . '">' . $event_title . '</a></h2>
                            <p class="pc_event_subtitle">(' . $event_date_utz . ') with ' . ucfirst($teacher) . '</p>
                        </div>';
                        $x++;                    
                        }
                }
                $html .= '</div>';
                return $html;
            }
        } elseif ($return === 'studio_classes') {
            $html = '<div class="pc_container">';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('willie') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="https://jazzedge.academy/replays/?_event_teacher=willie">Willie\'s Classes</a></h2>
                    <p class="pc_event_subtitle">Classes: TCI, CPT, PMTR, etc.</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('paul') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="https://jazzedge.academy/replays/?_event_teacher=paul">Paul\'s Classes</a></h2>
                    <p class="pc_event_subtitle">Classes: Transcription, Pop/Rock, etc.</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('nina') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="https://jazzedge.academy/replays/?_event_teacher=nina">Nina\'s Classes</a></h2>
                    <p class="pc_event_subtitle">Classes: Jazz Piano, Latin Jazz, etc.</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('anna') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="https://jazzedge.academy/replays/?_event_teacher=anna">Anna\'s Classes</a></h2>
                    <p class="pc_event_subtitle">Classes: Vocal Training</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('mike') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="https://jazzedge.academy/replays/?_event_teacher=mike">Mike\'s Classes</a></h2>
                    <p class="pc_event_subtitle">Classes: Rhythm Training</p>
                </div>';
          /*
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('john') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="https://jazzedge.academy/replays/?_event_teacher=john">John\'s Classes</a></h2>
                    <p class="pc_event_subtitle">Classes: Improvisation</p>
                </div>';
        */
            $html .= '</div>';
            return $html;
        } elseif ($return === 'meet_your_teachers') {
            $html = '<div class="pc_container">';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('willie') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/jazzedge-teachers">Willie Myette</a></h2>
                    <p class="pc_event_subtitle">Teaching online since 2000</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('paul') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/jazzedge-teachers">Paul Buono</a></h2>
                    <p class="pc_event_subtitle">Teaching with Jazzedge Since 2013</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('nina') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/jazzedge-teachers">Nina Ott</a></h2>
                    <p class="pc_event_subtitle">Teaching with Jazzedge Since 2022</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('anna') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/jazzedge-teachers">Anna Rizzo</a> (Voice)</h2>
                    <p class="pc_event_subtitle">Teaching with Jazzedge Since 2022</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('mike') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/jazzedge-teachers">Mike Marble</a> (Rhythm)</h2>
                    <p class="pc_event_subtitle">Teaching with Jazzedge Since 2018</p>
                </div>';
            /*
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('mike') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/replays/?_class_type=rhythm-training">Rhythm Class</a></h2>
                    <p class="pc_event_subtitle">Teacher: Mike</p>
                </div>';
            */
            $html .= '</div>';
            return $html;
        } elseif ($return === 'popular_classes') {
            $html = '<div class="pc_container">';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('paul') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/replays/?_class_type=music-notation">Music Notation Class</a></h2>
                    <p class="pc_event_subtitle">Teacher: Paul</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('Willie') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/replays/?_class_type=practical-theory">Practical Music Theory Class</a></h2>
                    <p class="pc_event_subtitle">Teacher: Willie</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('Paul') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/replays/?_class_type=music-history">Music History Class</a></h2>
                    <p class="pc_event_subtitle">Teacher: Paul</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('Paul') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/replays/?_class_type=transcription">Transcription Class</a></h2>
                    <p class="pc_event_subtitle">Teacher: Paul</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('john') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/replays/?_class_type=improvisation">Improvisation Class</a></h2>
                    <p class="pc_event_subtitle">Teacher: John</p>
                </div>';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('mike') . '</div>
                <div class="pc_description-container-80" style="padding-top: 4px;">
                    <h2><a href="/replays/?_class_type=rhythm-training">Rhythm Class</a></h2>
                    <p class="pc_event_subtitle">Teacher: Mike</p>
                </div>';
            $html .= '</div>';
            return $html;
        } elseif ($return === 'courses_running_this_semester') {
            $courses = get_field("running_premier_courses", "option");
            $html = '<div class="pc_container">';
            foreach ($courses as $course) {
                $permalink = get_permalink($course->ID);
                $premier_course_teacher = get_field("premier_course_teacher", $course->ID);
                $premier_course_short_description = get_field("premier_course_short_description", $course->ID);
                $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot($premier_course_teacher) . '</div>
                    <div class="pc_description-container-80" style="padding-top: 4px;">
                        <h2><a href="' . $permalink . '">' . $course->post_title . '</a></h2>
                        <p class="pc_event_subtitle">' . $premier_course_short_description . '<br />Teacher: ' . ucfirst($premier_course_teacher) . '</p>
                    </div>';
            }
            $html .= '</div>';
            return $html;
        } elseif ($return === 'previous_premier_courses') {
            $courses = get_field("previous_premier_courses", "option");
            $html = '<div class="pc_container">';
            foreach ($courses as $course) {
                $permalink = get_permalink($course->ID);
                $premier_course_teacher = get_field("premier_course_teacher", $course->ID);
                $premier_course_short_description = get_field("premier_course_short_description", $course->ID);
                $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot($premier_course_teacher) . '</div>
                    <div class="pc_description-container-80" style="padding-top: 4px;">
                        <h2><a href="' . $permalink . '">' . $course->post_title . '</a></h2>
                        <p class="pc_event_subtitle">' . $premier_course_short_description . '<br />Teacher: ' . ucfirst($premier_course_teacher) . '</p>
                    </div>';
            }
            $html .= '</div>';
            return $html;
        } elseif ($return === 'learning_paths') {
            $rand = generateRandomString(5);
            $html = '<div class="pc_container">';
            $html .= ' <div class="pc_image-container-15"><i class="fa-sharp fa-solid fa-signal" style="color: #00aa00; font-size: 22px;"></i></div>
                <div class="pc_description-container-85" style="padding-top: 4px;">
                    <span class="pc_large_text"><a href="//jazzedge.academy/paths/?reload=' . $rand . '#beginner" class="link-tabs" onclick="window.location.reload()" title="beginner">Level 1 (Beginner)</a></span>
                </div>
                <div class="pc_image-container-15"><i class="fa-sharp fa-solid fa-signal" style="color: #e5d600; font-size: 22px;"></i></div>
                <div class="pc_description-container-85" style="padding-top: 4px;">
                    <span class="pc_large_text"><a href="//jazzedge.academy/paths/?reload=' . $rand . '#intermediate" class="link-tabs" onclick="window.location.reload()" title="intermediate">Level 2 (Intermediate)</a></span>
                </div>
                <div class="pc_image-container-15"><i class="fa-sharp fa-solid fa-signal" style="color: #f04e23; font-size: 22px;"></i></div>
                <div class="pc_description-container-85" style="padding-top: 4px;">
                    <span class="pc_large_text"><a href="//jazzedge.academy/paths/?reload=' . $rand . '#advanced" class="link-tabs" onclick="window.location.reload()" title="advanced">Level 3 (Advanced)</a></span>
                </div>';
            $html .= '</div>';
            return $html;
        } elseif ($return === 'popular_styles') {
            $html = '<div class="pc_container">';
            $html .= '<a href="//jazzedge.academy/?s=&_lesson_style=jazz" class="pc_container-50">
                    <i class="fa-sharp fa-solid fa-music"></i> Jazz
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_style=ballad" class="pc_container-50">
                    <i class="fa-sharp fa-solid fa-music"></i> Slow Ballads
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_style=blues" class="pc_container-50">
                    <i class="fa-sharp fa-solid fa-music"></i> Blues
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_style=rock" class="pc_container-50">
                    <i class="fa-sharp fa-solid fa-music"></i> Rock
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_style=cocktail" class="pc_container-50">
                    <i class="fa-sharp fa-solid fa-music"></i> Cocktail Piano
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_style=funk" class="pc_container-50">
                    <i class="fa-sharp fa-solid fa-music"></i> Funk
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_style=latin" class="pc_container-50">
                    <i class="fa-sharp fa-solid fa-music"></i> Latin 
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_style=gospel" class="pc_container-50">
                    <i class="fa-sharp fa-solid fa-music"></i> Gospel
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_style=organ" class="pc_container-50">
                    <i class="fa-sharp fa-solid fa-music"></i> Organ
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_style=classical" class="pc_container-50">
                    <i class="fa-sharp fa-solid fa-music"></i> Classical
                </a>';
                        $html .= '</div>';
            return $html;
        } elseif ($return === 'popular_tags') {
            $html = '<div class="pc_container">';
            $html .= '<a href="//jazzedge.academy/?s=&_lesson_tags=song-lesson" class="pc_container-50">
                    <i class="fa-sharp fa-regular fa-tag"></i> Song Lessons
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_tags=2-5-1" class="pc_container-50">
                    <i class="fa-sharp fa-regular fa-tag"></i> 2-5-1
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_tags=arpeggios" class="pc_container-50">
                    <i class="fa-sharp fa-regular fa-tag"></i> Arpeggios
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_tags=fills" class="pc_container-50">
                    <i class="fa-sharp fa-regular fa-tag"></i> Fills
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_tags=blues-scale" class="pc_container-50">
                    <i class="fa-sharp fa-regular fa-tag"></i> Blues Scale
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_tags=endings" class="pc_container-50">
                    <i class="fa-sharp fa-regular fa-tag"></i> Endings
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_tags=analysis" class="pc_container-50">
                    <i class="fa-sharp fa-regular fa-tag"></i> Analysis
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_tags=enclosures" class="pc_container-50">
                    <i class="fa-sharp fa-regular fa-tag"></i> Enclosures
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_tags=rootless-chords" class="pc_container-50">
                    <i class="fa-sharp fa-regular fa-tag"></i> Rootless Chords
                </a>
                <a href="//jazzedge.academy/?s=&_lesson_tags=tritone" class="pc_container-50">
                    <i class="fa-sharp fa-regular fa-tag"></i> Tritone
                </a>';
            $html .= '</div>';
            return $html;
        } elseif ($return === 'recently_viewed_lessons') {
    global $wpdb, $user_id;
    $return_limit = ($limit > 0) ? $limit : 5; // Variable to change the number of records displayed
    // Fetch recently viewed items from the new table
    $q = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM academy_recently_viewed WHERE user_id = %d  AND deleted_at IS NULL ORDER BY datetime DESC LIMIT %d",
        $user_id, $return_limit
    ));

    $html = '<div class="pc_container">';
    $empty = TRUE;

			foreach ($q as $recent) {
				$empty = FALSE;
				$type = $recent->type;
				$post_id = $recent->post_id;
				$lesson_permalink = get_the_permalink($post_id);
				$title = stripslashes($recent->title);
		
				// Determine the icon based on the type
				switch ($type) {
					case 'lesson':
						$icon_class = 'fa-video';
						break;
					case 'class':
						$icon_class = 'fa-chalkboard-user';
						break;
					case 'event':
						$icon_class = 'fa-calendar';
						break;
					case 'premier-course':
						$icon_class = 'fa-book';
						break;
					case 'premier-class':
						$icon_class = 'fa-star';
						break;
					case 'mini-lesson':
						$icon_class = 'fa-play-circle';
						break;
					case 'blueprint':
						$icon_class = 'fa-map';
						break;
					case 'tip':
						$icon_class = 'fa-lightbulb';
						break;
					default:
						$icon_class = 'fa-file';
						break;
				}
		
				$html .= '<a href="' . esc_url($lesson_permalink) . '" class="pc_container-recents">
							<i class="fa-sharp fa-solid ' . $icon_class . '"></i> ' . mb_strimwidth($title, 0, 40, "...")
						   . '</a>';
			}

    $html .= '</div>';
    if ($empty) {
        $html = '<p>No recents found. As you watch lessons they will show up here.</p>';
    }
    return $html;
}  elseif ($return === 'recently_viewed_lessons_old') {
            global $wpdb, $user_id;
            $return_limit = ($limit > 0) ? $limit : 5;
            $q = $wpdb->get_results("SELECT a.* FROM academy_video_tracking a INNER JOIN ( SELECT post_id, MAX(datetime) as max_datetime FROM academy_video_tracking WHERE user_id = $user_id GROUP BY post_id ORDER BY max_datetime DESC LIMIT $return_limit ) b ON a.post_id = b.post_id AND a.datetime = b.max_datetime WHERE a.user_id = $user_id ORDER BY a.datetime DESC;");
            $html = '<div class="pc_container">';
            $empty = TRUE;
            foreach ($q as $recent) {
                $empty = FALSE;
                $type = $recent->type;
                $chapter_id = $recent->chapter_id;
                $post_id = $recent->post_id;
                $last_viewed = date("m-d-Y", strtotime($recent->datetime));
                $lesson_permalink = get_the_permalink($post_id);
                $lesson_id = get_post_meta($post_id, 'lesson_id', true);
                $event_id = ($type === 'class') ? $recent->lesson_id : 0;
                $title = ($type === 'lesson') ? get_the_title($post_id) : get_the_title($event_id);
                $icon_class = ($lesson_id > 0) ? 'fa-video' : 'fa-chalkboard-user';
                $event_url = ($event_id > 0) ? "?id=$event_id" : '';
                $html .= '<a href="' . $lesson_permalink . $event_url . '" class="pc_container-recents">
                            <i class="fa-sharp fa-solid ' . $icon_class . '"></i> ' . mb_strimwidth($title, 0, 40, "...") . '
                        </a>';
            }
            $html .= '</div>';
            if ($empty) {
                $html = '<p>No recents found. As you watch lessons they will show up here.</p>';
            }
            return $html;
        } elseif ($return === 'my_favorites') {
            global $wpdb, $user_id;
            $q = $wpdb->get_results("SELECT * FROM academy_favorites WHERE user_id = $user_id AND type != 'resource' AND deleted_at IS NULL ORDER BY datetime DESC LIMIT 5");
            $html = '<div class="pc_container">';
            $empty = TRUE;
            foreach ($q as $fav) {
                $empty = FALSE;
                $type = $fav->type;
                $course_or_lesson_id = $fav->course_or_lesson_id;
                $added = date('M. jS, Y', strtotime($fav->datetime));
                if ($type === 'lesson') {
                    $title = je_return_lesson_title($course_or_lesson_id);
                    $icon = '<i class="fa-sharp fa-solid fa-video"></i>';
                    $permalink = je_get_permalink($course_or_lesson_id, 'lesson');
                    $percent_complete = je_return_lesson_progress_percentage($course_or_lesson_id);
                    $is_event = '';
                } elseif ($type === 'course') {
                    $title = je_return_course_title($course_or_lesson_id);
                    $icon = '<i class="fa-sharp fa-solid fa-book"></i>';
                    $permalink = je_get_permalink($course_or_lesson_id, 'course');
                    $percent_complete = je_return_course_progress_percentage($course_or_lesson_id);
                    $is_event = '';
                } elseif ($type === 'path') {
                    $title = get_the_title($course_or_lesson_id);
                    $icon = '<i class="fa-sharp fa-solid fa-book-open-cover"></i>';
                    $permalink = get_the_permalink($course_or_lesson_id);
                    $percent_complete = je_return_path_progress_percentage($course_or_lesson_id);
                    $is_event = '';
                } elseif ($type === 'coaching' || $type === 'class' || $type === 'event') {
                    $title = get_the_title($course_or_lesson_id);
                    $icon = '<i class="fa-sharp fa-solid fa-chalkboard-user"></i>';
                    $permalink = 'https://jazzedge.academy/event-replay/?id=' . $course_or_lesson_id;
                    $is_event = '&is_event=y';
                }
                $title = ($type != 'event') ? $title : get_the_title($course_or_lesson_id);
                $html .= '<a href="' . $permalink . '" class="pc_container-recents">' . $icon . ' ' . $title . '</a>';
            }
            $html .= '</div>';
            if ($empty) {
                $html = '<div class="center"><p>No favorite lessons found.<br /><a href="/docs/how-to-favorite-lessons/">Learn how to favorite lessons</a>.</p></div>';
            }
            return $html;
        } elseif ($return === 'my_favorite_resources') {
            global $wpdb, $user_id;
            $q = $wpdb->get_results("SELECT * FROM academy_favorites WHERE user_id = $user_id AND type = 'resource' AND link_type != '' AND course_or_lesson_id > 0  AND deleted_at IS NULL ORDER BY datetime DESC LIMIT 5");
            $html = '<div class="pc_container">';
            $empty = TRUE;
            foreach ($q as $fav) {
                $empty = FALSE;
                $course_or_lesson_id = $fav->course_or_lesson_id;
                $added = date('m-d-Y', strtotime($fav->datetime));
                $lesson_title = $wpdb->get_var("SELECT lesson_title FROM academy_lessons WHERE ID = $course_or_lesson_id");
                $permalink = je_get_permalink($course_or_lesson_id, 'lesson');
                $font_awesome_class = je_return_resource_type($fav->link_type, 'font_awesome_class');
                $resource = $fav->link;
                $resource_type = je_return_resource_type($fav->link_type);
                $resource_link = 'https://s3.amazonaws.com/jazzedge-resources/' . $resource;
                $html .= '<a href="' . $resource_link . '" aria-label="Resource Type: ' . $resource_type . '" data-microtip-position="top" role="tooltip" class="pc_container-recents"><i class="' . $font_awesome_class . '"></i>' . $lesson_title . '</a>';
            }
            $html .= '</div>';
            if ($empty) {
                $html = '<div class="center"><p>No favorite resources found.<br /><a href="/docs/how-to-add-resources-to-your-favorites/">Learn how to favorite resources</a>.</p></div>';
            }
            return $html;
        } elseif ($return === 'get_started') {
            global $keap_id, $user_email;
           	            $html = '<div class="pc_container">';
            $forum_link = "//myjazzedge.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$keap_id&Email=$user_email&redir=/forums/forum/getting-started/introduce-yourself/";
            $html .= '
                <div class="pc_image-container-20"><i class="fa-solid fa-circle-1 max_menu_numbers"></i></div>
                <div class="pc_description-container-80 padding-top-5">
                    <h2><a href="/course/30-day-piano-playbook/" target="">30-Day Piano Playbook&trade;</a></h2>
                    <p class="pc_event_subtitle">Start here if new to the Academy</p>
                </div>
                <div class="pc_image-container-20"><i class="fa-solid fa-circle-2 max_menu_numbers"></i></div>
                <div class="pc_description-container-80 padding-top-5">
                    <h2><a href="/jazzedge-practice-curriculum/" target="">JPC Curriculum</a></h2>
                    <p class="pc_event_subtitle">Master all of your foundations</p>
                </div>
                <div class="pc_image-container-20"><i class="fa-solid fa-circle-3 max_menu_numbers"></i></div>
                <div class="pc_description-container-80 padding-top-5">
                    <h2><a href="/blueprints/" target="_blank">Academy Blueprints</a></h2>
                    <p class="pc_event_subtitle">Structured learning on a specific topic</p>
                </div>
                <div class="pc_image-container-20"><i class="fa-solid fa-circle-4 max_menu_numbers"></i></div>
                <div class="pc_description-container-80 padding-top-4">
                    <h2><a href="https://www.facebook.com/groups/pianowithwillie" target="_blank">Join Facebook Group</a></h2>
                    <p class="pc_event_subtitle">Meet other students and get tips</p>
                </div>
                <div class="pc_image-container-20"><i class="fa-solid fa-circle-5 max_menu_numbers"></i></div>
                <div class="pc_description-container-80 padding-top-5">
                    <h2><a href="/help" target="_blank">Read the Documentation</a></h2>
                    <p class="pc_event_subtitle">Need help? Read our docs.</p>
                </div>';
            $html .= '</div>';
            return $html;
        } elseif ($return === 'latest_news_post') {
            $return_limit = ($limit > 0) ? $limit : 5;
            $args = array(
				'post_type' => 'news',
				'post_status' => 'publish',
				'orderby' => 'date',
				'order' => 'DESC',
				'posts_per_page' => 1, // To get only the most recent post
			);
            $post = get_posts($args);
            if ($post) {
				$news_title = $post[0]->post_title;
				$news_content = $post[0]->post_content;
				$html = "<div class='news-box'>";
				$html .= "<h3 class='news-title'>$news_title</h3>";
				$html .= "<p class='news-content'>$news_content</p>";
				$html .= "</div>";
			}
			return $html;
        } elseif ($return === 'office_hours') {
            $return_limit = ($limit > 0) ? $limit : 5;
            $args = array(
                'post_type' => 'office-hours',
                'post_status' => 'publish',
                'meta_key' => 'office_hours_date',
                'meta_value' => date('Y-m-d'),
                'meta_compare' => '>=',
                'orderby' => 'meta_value',
                'posts_per_page' => $return_limit,
                'order' => 'ASC',
            );
            $events = get_posts($args);
            if ($events) {
                $html = '<div class="pc_container">';
                foreach ($events as $event) {
                    $event_date = get_field("office_hours_date",$event->ID);
                    $permalink = get_the_permalink($event->ID);
                    $user_timezone = $_SESSION['user-timezone'];
                    $event_date_formatted = format_timezone($event_date, $user_timezone);
                    $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('willie') . '</div>
                        <div class="pc_description-container-80" style="padding-top: 4px;">
                            <h2><a href="' . $permalink . '">' . $event->post_title . '</a> (all)</h2>
                            <p class="pc_event_subtitle">' . $event_date_formatted . ' with Willie</p>
                        </div>';
                }
                $html .= '</div>';
            }
            return $html;
        } elseif ($return === 'ec_events') {
				$return_limit = ($limit > 0) ? (int) $limit : 5;
			
				// --- Normalize shortcode filters: accept single or comma-separated lists ---
				$norm_terms = function($val) {
					if (empty($val)) return array();
					if (is_array($val)) return array_filter(array_map('sanitize_title', $val));
					// string: allow comma-separated
					$parts = array_map('trim', explode(',', (string)$val));
					return array_filter(array_map('sanitize_title', $parts));
				};
				$member_terms = $norm_terms($member_level); // e.g. ['premier']
				$etype_terms  = $norm_terms($event_type);   // e.g. ['coaching','class']
			
				// --- Build query for future events, ordered by start datetime ---
				$args = array(
					'post_type'      => 'je_event',
					'post_status'    => 'publish',
					'posts_per_page' => $return_limit,
					'meta_key'       => 'je_event_start',
					'orderby'        => 'meta_value',
					'meta_type'      => 'DATETIME',                 // ensure proper ordering
					'order'          => 'ASC',
					'meta_query'     => array(
						array(
							'key'     => 'je_event_start',
							'value'   => current_time('mysql'),     // site TZ "now"
							'compare' => '>=',
							'type'    => 'DATETIME',
						),
					),
				);
			
				// --- Optional tax filters from shortcode ---
				$tax_query = array('relation' => 'AND');
				if (!empty($etype_terms)) {
					$tax_query[] = array(
						'taxonomy' => 'event-type',
						'field'    => 'slug',
						'terms'    => $etype_terms,
					);
				}
				if (!empty($member_terms)) {
					$tax_query[] = array(
						'taxonomy' => 'membership-level',
						'field'    => 'slug',
						'terms'    => $member_terms,
					);
				}
				if (count($tax_query) > 1) {
					$args['tax_query'] = $tax_query;
				}
			
				// --- Fetch events ---
				$events = get_posts($args);
			
				// --- Render ---
				if ($events) {
					$html = '<div class="pc_container">';
			
					foreach ($events as $event) {
						$event_id  = $event->ID;
			
						// Start datetime string (ACF). Use WordPress timezone handling like other parts of the code
						$start_raw = get_post_meta($event_id, 'je_event_start', true);
						if ($start_raw && function_exists('je_ev_local_dt')) {
							$dt = je_ev_local_dt($start_raw);
							$date_str = $dt ? wp_date('D., M. jS, Y \a\t g:ia', $dt->getTimestamp()) : '';
						} else {
							// Fallback to basic formatting
							$ts = $start_raw ? strtotime($start_raw) : 0;
							$date_str = $ts ? date_i18n('D., M. jS, Y \a\t g:ia', $ts) : '';
						}
			
						// First teacher term (if any)
						$teacher_names = wp_get_post_terms($event_id, 'teacher', array('fields' => 'names'));
						$teacher_name  = (!is_wp_error($teacher_names) && !empty($teacher_names)) ? $teacher_names[0] : '';
						$teacher_name_uc = $teacher_name ? ucfirst(strtolower($teacher_name)) : '';
			
						// Permalink + headshot
						$permalink     = get_permalink($event_id);
						$headshot_html = function_exists('ja_teacher_headshot') ? ja_teacher_headshot($teacher_name) : '';
			
						// Optional chip for level if you filtered by it
						$level_chip = '';
						if (!empty($member_terms)) {
							// show first requested level for context
							$level_chip = ' <span class="pc_level_chip">(' . esc_html(ucfirst($member_terms[0])) . ')</span>';
						}
			
						$html .= '
						<div class="pc_image-container-20">'.$headshot_html.'</div>
						<div class="pc_description-container-80" style="padding-top:4px;">
							<h2><a href="'.esc_url($permalink).'">'.esc_html(get_the_title($event_id)).'</a>'.$level_chip.'</h2>
							<p class="pc_event_subtitle">'.esc_html($date_str).($teacher_name_uc ? ' with '.esc_html($teacher_name_uc) : '').'</p>
						</div>';
					}
			
					$html .= '</div>';
				} else {
					$html = '<p>No upcoming events found.</p>';
				}
			
				return $html;
    } elseif ($return === 'studio_coaching') {
				$return_limit = ($limit > 0) ? (int) $limit : 5;
				
				// Build the query for upcoming Studio Coaching with Willie
				$args = array(
					'post_type'      => 'je_event',
					'post_status'    => 'publish',
					'posts_per_page' => $return_limit,
					'meta_key'       => 'je_event_start',
					'orderby'        => 'meta_value',
					'order'          => 'ASC',
					'meta_query'     => array(
						array(
							'key'     => 'je_event_start',
							'value'   => current_time('mysql'), // site tz “now”
							'compare' => '>=',
							'type'    => 'DATETIME',
						),
					),
					'tax_query'      => array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'event-type',         // event type = coaching
							'field'    => 'slug',
							'terms'    => array('coaching'),
						),
						array(
							'taxonomy' => 'membership-level',   // membership level = studio
							'field'    => 'slug',
							'terms'    => array('studio'),
						),
						array(
							'taxonomy' => 'teacher',            // teacher = Willie
							'field'    => 'name',               // stored as “Willie” (name)
							'terms'    => array('Willie'),
						),
					),
				);
				
				$events = get_posts($args);
				
				if ($events) {
					$html = '<div class="pc_container">';
				
					foreach ($events as $event) {
						$event_id = $event->ID;
				
						// Start datetime (string stored by ACF)
						$start_raw = get_post_meta($event_id, 'je_event_start', true);
				
						// If you track a per-user tz in session, format with your helper
						$user_timezone = isset($_SESSION['user-timezone']) ? $_SESSION['user-timezone'] : '';
						if (function_exists('format_timezone') && !empty($user_timezone)) {
							$event_date_formatted = format_timezone($start_raw, $user_timezone);
						} else {
							// Fallback to site timezone formatting
							$ts = $start_raw ? strtotime($start_raw) : 0;
							$event_date_formatted = $ts ? date_i18n('D., M. jS, Y \a\t g:ia', $ts) : '';
						}
				
						// Teacher term (first)
						$teacher_terms = wp_get_post_terms($event_id, 'teacher', array('fields' => 'names'));
						$teacher_name  = (!is_wp_error($teacher_terms) && !empty($teacher_terms)) ? $teacher_terms[0] : 'Willie';
						$teacher_name_uc = ucfirst(strtolower($teacher_name));
				
						// Permalink & headshot
						$permalink     = get_permalink($event_id);
						$headshot_html = function_exists('ja_teacher_headshot') ? ja_teacher_headshot($teacher_name) : '';
				
						$html .= '
						<div class="pc_image-container-20">'.$headshot_html.'</div>
						<div class="pc_description-container-80" style="padding-top: 4px;">
							<h2><a href="'.esc_url($permalink).'">'.esc_html(get_the_title($event_id)).'</a></h2>
							<p class="pc_event_subtitle">'.esc_html($event_date_formatted).' with '.esc_html($teacher_name_uc).'</p>
						</div>';
					}
				
					$html .= '</div>';
				} else {
					$html = '<p>No upcoming Studio Coaching found.</p>';
				}
				
				return $html;
			} elseif ($return === 'premier_coaching') {
				$return_limit = ($limit > 0) ? (int) $limit : 5;
			
				// Upcoming Premier Coaching (any teacher) from new JE Events calendar
				$args = array(
					'post_type'      => 'je_event',
					'post_status'    => 'publish',
					'posts_per_page' => $return_limit,
					'meta_key'       => 'je_event_start',
					'orderby'        => 'meta_value',
					'order'          => 'ASC',
					'meta_query'     => array(
						array(
							'key'     => 'je_event_start',
							'value'   => current_time('mysql'), // site TZ "now"
							'compare' => '>=',
							'type'    => 'DATETIME',
						),
					),
					'tax_query'      => array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'event-type',        // coaching
							'field'    => 'slug',
							'terms'    => array('coaching'),
						),
						array(
							'taxonomy' => 'membership-level',  // premier
							'field'    => 'slug',
							'terms'    => array('premier'),
						),
					),
				);
			
				$events = get_posts($args);
			
				if ($events) {
					$html = '<div class="pc_container">';
			
					foreach ($events as $event) {
						$event_id = $event->ID;
			
						// Start datetime from ACF field (string)
						$start_raw = get_post_meta($event_id, 'je_event_start', true);
			
						// Prefer your per-user timezone helper if available
						$user_timezone = isset($_SESSION['user-timezone']) ? $_SESSION['user-timezone'] : '';
						if (function_exists('format_timezone') && !empty($user_timezone)) {
							$event_date_formatted = format_timezone($start_raw, $user_timezone);
						} else {
							// Fallback to site timezone formatting
							$ts = $start_raw ? strtotime($start_raw) : 0;
							$event_date_formatted = $ts ? date_i18n('D., M. jS, Y \a\t g:ia', $ts) : '';
						}
			
						// Teacher term (first if multiple)
						$teacher_terms = wp_get_post_terms($event_id, 'teacher', array('fields' => 'names'));
						$teacher_name  = (!is_wp_error($teacher_terms) && !empty($teacher_terms)) ? $teacher_terms[0] : '';
						$teacher_name_uc = $teacher_name ? ucfirst(strtolower($teacher_name)) : '';
			
						// Permalink & headshot
						$permalink     = get_permalink($event_id);
						$headshot_html = function_exists('ja_teacher_headshot') ? ja_teacher_headshot($teacher_name) : '';
			
						$html .= '
						<div class="pc_image-container-20">'.$headshot_html.'</div>
						<div class="pc_description-container-80" style="padding-top: 4px;">
							<h2><a href="'.esc_url($permalink).'">'.esc_html(get_the_title($event_id)).'</a> (premier)</h2>
							<p class="pc_event_subtitle">'.esc_html($event_date_formatted).($teacher_name_uc ? ' with '.esc_html($teacher_name_uc) : '').'</p>
						</div>';
					}
			
					$html .= '</div>';
				} else {
					$html = '<p>No upcoming Premier Coaching found.</p>';
				}
			
				return $html;
            } elseif ($return === 'coaching') {
            $return_limit = ($limit > 0) ? $limit : 5;
            $args = array(
                'post_type' => 'coaching',
                'post_status' => 'publish',
                'meta_key' => 'coaching_date',
                'meta_value' => date('Y-m-d'),
                'meta_compare' => '>=',
                'orderby' => 'meta_value',
                'posts_per_page' => $return_limit,
                'order' => 'ASC',
            );
            $events = get_posts($args);
            if ($events) {
                $html = '<div class="pc_container">';
                foreach ($events as $event) {
                    $event_date = get_field("coaching_date",$event->ID);
                    $coaching_teacher = get_field("coaching_teacher",$event->ID);
                    $coaching_membership_level = get_field("coaching_membership_level",$event->ID);
                    $permalink = get_the_permalink($event->ID);
                    $user_timezone = $_SESSION['user-timezone'];
                    $event_date_formatted = format_timezone($event_date, $user_timezone);
                    $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot($coaching_teacher) . '</div>
                        <div class="pc_description-container-80" style="padding-top: 4px;">
                            <h2><a href="' . $permalink . '">' . $event->post_title . '</a> ('.$coaching_membership_level.')</h2>
                            <p class="pc_event_subtitle">' . $event_date_formatted . ' with '.ucfirst($coaching_teacher).'</p>
                        </div>';
                }
                $html .= '</div>';
            }
            return $html;
        } elseif ($return === 'upcoming_classes_premier') {
            $return_limit = ($limit > 0) ? $limit : 5;
            $args = array(
                'post_type' => 'classes',
                'post_status' => 'publish',
                'meta_key' => 'class_date',
                'meta_value' => date('Y-m-d'),
                'meta_compare' => '>=',
                'orderby' => 'meta_value',
                'posts_per_page' => $return_limit,
                'order' => 'ASC',
            );
            $events = get_posts($args);
            if ($events) {
                $html = '<div class="pc_container">';
                foreach ($events as $event) {
                    $event_date = get_field("class_date",$event->ID);
                    $class_teacher = get_field('class_teacher',$event->ID);
                    $permalink = get_the_permalink($event->ID);
                    $user_timezone = $_SESSION['user-timezone'];
                    $event_date_formatted = format_timezone($event_date, $user_timezone);
                    $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot($class_teacher) . '</div>
                        <div class="pc_description-container-80" style="padding-top: 4px;">
                            <h2><a href="' . $permalink . '">' . $event->post_title . '</a> (premier)</h2>
                            <p class="pc_event_subtitle">' . $event_date_formatted . ' with '.ucfirst($class_teacher).'</p>
                        </div>';
                }
                $html .= '</div>';
            }
            return $html;
        } elseif ($return === 'webinars') {
            $return_limit = ($limit > 0) ? $limit : 5;
            $args = array(
                'post_type' => 'webinar-single',
                'post_status' => 'publish',
                'meta_key' => 'webinar_date',
                'meta_value' => date('Y-m-d'),
                'meta_compare' => '>=',
                'orderby' => 'meta_value',
                'posts_per_page' => $return_limit,
                'order' => 'ASC',
            );
            $events = get_posts($args);
            if ($events) {
                $html = '<div class="pc_container">';
                foreach ($events as $event) {
                    $event_date = get_field("webinar_date",$event->ID);
                    $user_timezone = $_SESSION['user-timezone'];
                    $event_date_utz = format_timezone($event_date, $user_timezone);
                    $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('willie') . '</div>
                        <div class="pc_description-container-80" style="padding-top: 4px;">
                            <h2><a href="/webinars">' . $event->post_title . '</a></h2>
                            <p class="pc_event_subtitle">' . $event_date_utz . ' with Willie</p>
                        </div>';
                }
                $html .= '</div>';
            } else {
                $html = '<div style="width:100%; text-align: center; font-size: 12pt; padding: 10px;">More webinars added soon.<br />For now, check out the <a href="/webinars">replays</a>.</div>';
            }
            return $html;
        } elseif ($return === 'mini-lessons') {
            $return_limit = ($limit > 0) ? $limit : 5;
            $args = array(
                'post_type' => 'mini-lessons',
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'posts_per_page' => $return_limit,
            );
            $minis = get_posts($args);
            if ($minis) {
                $html = '<div class="pc_container">';
                foreach ($minis as $mini) {
                    $permalink = get_the_permalink($mini->ID);
                    $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('willie') . '</div>
                    <div class="pc_description-container-80">
                        <h2><a href="' . $permalink . '">' . $mini->post_title . '</a></h2>
                    </div>';
                }
                $html .= '</div>';
            }
            return $html;
        } elseif ($return === 'tips') {
            $return_limit = ($limit > 0) ? $limit : 5;
            $args = array(
                'post_type' => 'tips',
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'posts_per_page' => $return_limit,
            );
            $tips = get_posts($args);
            if ($tips) {
                $html = '<div class="pc_container">';
                foreach ($tips as $tip) {
                    $permalink = get_the_permalink($tip->ID);
                    $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('willie') . '</div>
                    <div class="pc_description-container-80">
                        <h2><a href="' . $permalink . '">' . $tip->post_title . '</a></h2>
                        <p class="pc_event_subtitle">' . get_field("blueprint_short_description", $tip->ID) . '</p>
                    </div>';
                }
                $html .= '</div>';
            }
            return $html;
        } elseif ($return === 'mini') {
            $return_limit = ($limit > 0) ? $limit : 5;
            $args = array(
                'post_type' => 'mini-lessons',
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'posts_per_page' => $return_limit,
            );
            $tips = get_posts($args);
            if ($tips) {
                $html = '<div class="pc_container">';
                foreach ($tips as $tip) {
                    $permalink = get_the_permalink($tip->ID);
                    $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot('willie') . '</div>
                    <div class="pc_description-container-80">
                        <h2><a href="' . $permalink . '">' . $tip->post_title . '</a></h2>
                        <p class="pc_event_subtitle">' . get_field("blueprint_short_description", $tip->ID) . '</p>
                    </div>';
                }
                $html .= '</div>';
            }
            return $html;
        } elseif ($return === 'blueprints') {
            $return_limit = ($limit > 0) ? $limit : 6;
            $args = array(
                'post_type' => 'blueprint',
                'post_status' => 'publish',
                'meta_key' => 'blueprint_order',
                                'orderby' => 'meta_value',
                'posts_per_page' => $return_limit,
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => 'blueprint_featured',
                        'value' => 'y',
                        'compare' => '='
                    )
                ),
            );
            $blueprints = get_posts($args);
            if ($blueprints) {
                $html = '<div class="pc_container">';
                foreach ($blueprints as $blueprint) {
                    $thumbnail = get_the_post_thumbnail_url($blueprint->ID);
                    $blueprint_short_description = get_field("blueprint_short_description", $blueprint->ID);
                    $permalink = get_the_permalink($blueprint->ID);
                    $html .= ' <div class="pc_image-container-20">' . ja_circle_image($thumbnail) . '</div>
                    <div class="pc_description-container-80">
                        <h2><a href="' . $permalink . '">' . $blueprint->post_title . '</a></h2>
                        <p class="pc_event_subtitle">' . $blueprint_short_description . '</p>
                    </div>';
                }
                $html .= '</div>';
            }
            return $html;
        } elseif ($return === 'upcoming_premier_classes') {
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
                    )
                ),
            );
            $pc_events = get_posts($args);
            if ($pc_events) {
                $html = '<div class="pc_container">';
                foreach ($pc_events as $pc_event) {
                    //$pc_event_date = strtotime(get_post_meta($pc_event->ID, 'premier_class_release_date', true));
                    $pc_event_date = get_field('premier_class_release_date', $pc_event->ID);
                   // $premier_live_class_date = strtotime(get_post_meta($pc_event->ID, 'premier_live_class_date', true));
                    $premier_live_class_date = get_field('premier_live_class_date', $pc_event->ID);
                    $premier_course_id = get_post_meta($pc_event->ID, 'premier_course_id', true);
                    $premier_course_teacher = get_post_meta($premier_course_id, 'premier_course_teacher', true);
                    $user_timezone = $_SESSION['user-timezone'];
                    if (!empty($premier_live_class_date)) {
                        $permalink = get_the_permalink($premier_course_id) . '#live-' . $pc_event->ID;
                        $pc_event_date_utz = format_timezone($premier_live_class_date, $user_timezone);
                    } else {
                        $permalink = get_the_permalink($pc_event->ID);
                        $pc_event_date_utz = format_timezone($pc_event_date, $user_timezone);
                    }
                    $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot($premier_course_teacher) . '</div>
                    <div class="pc_description-container-80" style="padding-top: 4px;">
                        <h2><a href="' . $permalink . '">' . $pc_event->post_title . '</a> (premier)</h2>
                        <p class="pc_event_subtitle">' . $pc_event_date_utz . ' with ' . ucfirst($premier_course_teacher) . '</p>
                    </div>';
                }
                $html .= '</div>';
            } else {
                $html = '<div style="width:100%; text-align: center; font-size: 12pt; padding: 10px;">More Premier Classes Added Soon.<br /><a href="/premier-courses/">Check out the upcoming Premier Courses</a>.</div>';
            }
            return $html;
        } elseif ($return === 'template') {
            $html = '<div class="pc_container">';
            $html .= ' <div class="pc_image-container-20">' . ja_teacher_headshot($premier_course_teacher) . '</div>
                    <div class="pc_description-container-80" style="padding-top: 4px;">
                        <h2><a href="' . $permalink . '">' . $course->post_title . '</a></h2>
                        <p class="pc_event_subtitle">' . $premier_course_short_description . '<br />Teacher: ' . ucfirst($premier_course_teacher) . '</p>
                    </div>';
            $html .= '</div>';
            return $html;
        }
    }

    function je_return_facet_listing_template($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'template' => 'lessons',
            ),
            $atts,
            'je_return_facet_listing_template'
        );
        if (wp_is_mobile() != TRUE && $atts['template'] == 'lessons') {
            // return do_shortcode('[facetwp template="lessons"]');
            //return do_shortcode('[facetwp template="lessons_table"]');
        } else {
            return do_shortcode('[facetwp template="lessons_mobile"]');
        }
    }

    function facet_truncate_description($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'text' => '',
            ),
            $atts,
            'facet_truncate_description'
        );
        $text = $atts['text'];
        return mb_strimwidth($text, 0, 18, "...");
    }

    function facet_format_skill_level($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'level' => '',
            ),
            $atts,
            'facet_format_skill_level'
        );
        $level = $atts['level'];
        return '<div class="lesson-grid-style lesson-grid-level-' . $level . '"><span class="black-text">Skill Level:</span> ' . $level . '</div>';
    }

    function facet_return_replay_thumbnail()
    {
        $post_id = get_the_ID();
        $class_type = get_field("class_type", $post_id);
        if (empty($class_type) && get_field("event_type", $post_id) == 'coaching') {
            $teacher = get_field("teacher", $post_id);
            if ($teacher == 'willie') {
                return 'https://jazzedge.academy/wp-content/uploads/2023/03/coaching-willie.jpg';
            } elseif ($teacher == 'paul') {
                return 'https://jazzedge.academy/wp-content/uploads/2023/03/coaching-paul.jpg';
            } else {
                return 'https://jazzedge.academy/wp-content/uploads/2023/03/event-thumbnail-1.jpg';
            }
        } elseif ($class_type == 'music-history') {
            return 'https://jazzedge.academy/wp-content/uploads/2023/03/jazz-history-class.jpg';
        } elseif ($class_type == 'music-notation') {
            return 'https://jazzedge.academy/wp-content/uploads/2023/03/sibelius-class.jpg';
        } elseif ($class_type == 'improvisation') {
            return 'https://jazzedge.academy/wp-content/uploads/2023/03/improvisation-class.jpg';
        } elseif ($class_type == 'jazz-piano') {
            return 'https://jazzedge.academy/wp-content/uploads/2023/03/jazz-piano-class.jpg';
        } elseif ($class_type == 'pop-rock') {
            return 'https://jazzedge.academy/wp-content/uploads/2023/03/pop-rock-class.jpg';
        } elseif ($class_type == 'transcription') {
            return 'https://jazzedge.academy/wp-content/uploads/2023/03/transcription-class.jpg';
        } elseif ($class_type == 'rhythm-training') {
            return 'https://jazzedge.academy/wp-content/uploads/2023/03/rhythm-class.jpg';
        } elseif ($class_type == 'vocal-training') {
            return 'https://jazzedge.academy/wp-content/uploads/2023/03/vocal-class.jpg';
        } else {
            return 'https://jazzedge.academy/wp-content/uploads/2023/03/event-thumbnail-1.jpg';
        }
    }

    function je_return_lesson_data($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'return' => 'lesson_title',
                'lesson_id' => 0,
            ),
            $atts,
            'je_return_lesson_data'
        );
        if ($atts['lesson_id'] > 0) {
            $lesson_id = intval($atts['lesson_id']);
        } else {
            $lesson_id = get_post_meta(get_the_ID(), 'lesson_id', true);
        }
        if ($lesson_id == 0) {
            return '<div style="text-align: center; width:100%;">No lesson found.</div>';
        }
        global $wpdb;
        $q = $wpdb->get_row("SELECT * FROM academy_lessons WHERE ID = $lesson_id ");
		$course_id = isset($q) && isset($q->course_id) ? intval($q->course_id) : 0;
		if ($atts['return'] == 'lesson_title') {
			$return = isset($q) && isset($q->lesson_title) ? stripslashes(htmlspecialchars($q->lesson_title)) : '';
		}
                if ($atts['return'] == 'course_id') {
            $return = $course_id;
        }
		if ($atts['return'] == 'lesson_description') {
			$return = isset($q) && isset($q->lesson_description) ? stripslashes(htmlspecialchars($q->lesson_description)) : '';
		}
		if ($atts['return'] == 'course_title') {
			$course_id = intval($course_id); // Ensures $course_id is an integer
			$return = stripslashes(htmlspecialchars($wpdb->get_var($wpdb->prepare("SELECT course_title FROM academy_courses WHERE ID = %d", $course_id))));
		}
        return $return;
    }

    function je_return_lesson_chapters($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'format' => 'well',
            ),
            $atts,
            'je_return_lesson_chapters'
        );
        global $wpdb;
        $lesson_id = get_post_meta(get_the_ID(), 'lesson_id', true);
        $chapter_slug = (isset($_GET['c'])) ? sanitize_text_field($_GET['c']) : '';
        $total_lesson_duration = 0;
        if ($lesson_id == 0) {
            return 'No lesson found.';
        }
        $chapters = $wpdb->get_results("SELECT * FROM academy_chapters WHERE lesson_id = $lesson_id ORDER BY menu_order ASC");
        if (empty($chapters)) {
            return '<div style="text-align: center; width:100%; ">No chapters found.</div>';
        }
        // ********** SHOW IN WELL
        if ($atts['format'] == 'well') {
            $return = '<div class="well">';
            if (count($chapters) < 2) {
                $return .= '<div style="text-align: center; width:100%;"><span style="font-size:10pt;">(this lesson has only 1 chapter)</span></div>';
            }
            $return .= '<ul class="list-items">';
            $counter = 1;
            $lesson_title = je_return_lesson_title($lesson_id);
            $lesson_title = str_replace('"', "", $lesson_title);
            $lesson_title = str_replace("'", "", $lesson_title);
            $has_download_access = je_check_download_access($lesson_id);
            foreach ($chapters as $chapter) {
                $chapter_link = '?c=' . $chapter->slug;
                $chapter_id = $chapter->ID;
                $activity_completed = (je_is_chapter_complete($chapter->ID) == TRUE) ? 'activity-completed' : '';
                $is_active_chapter = ($chapter->slug == $chapter_slug) ? 'active' : '';
                $return .= '
                <li class="' . $is_active_chapter . '" data-lesson="lesson-' . $counter . '">
                    <a href="' . $chapter_link . '">
                        <span class="item-number ' . $activity_completed . '">' . $counter . '</span>
                        <span class="item-title">' . stripslashes($chapter->chapter_title) . '</span>
                        <span class="item-label">Duration: ' . format_time($chapter->duration) . '</span>
                    </a>';
                if ($has_download_access == 'true') {
                    $return .= '<a href="/willie/download_chapter.php?id=' . $chapter_id . '&title=' . $lesson_title . '&num=' . $counter . '" class="download_chapter_button"><i class="fa-solid fa-cloud-arrow-down"></i> Download Video</a>';
                }
                $return .= '</li>
                <!--/.lesson-->';
                $counter++;
                $total_lesson_duration += $chapter->duration;
            }
            $return .= '<li style="padding:30px 0px; font-size:10pt; text-align:center;">Total Lesson Duration = ' . format_time($total_lesson_duration) . '</li>
            </ul>
            <!-- /.list-items -->
            </div>
            <!-- /.well -->';
        }
        // ********** SHOW AS LIST
        if ($atts['format'] == 'list') {
            $return = '<ul>';
            foreach ($chapters as $chapter) {
                $return .= '<li><a href="/chapter/?c=' . $chapter->slug . '">' . $chapter->chapter_title . '</a></li>';
            }
            $return .= '</ul>';
        }
        return $return;
    }

    function je_return_lesson_resources($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(),
            $atts,
            'je_return_lesson_resources'
        );
        $access = FALSE;
        $post_id = get_the_ID();
        $lesson_id = get_post_meta($post_id, 'lesson_id', true);
        $rental_status = je_check_rental_access();
        $user_id = get_current_user_id();
        $user_membership_level = je_return_membership_level();
        $tags = get_post_meta($post_id, 'lesson_tags', true);
        $no_access_message = '<h4 style="color:#f04e23;">Looking for sheet music and backing tracks?</h4><p><a href="/signup" class="hover-black ">Upgrade</a> your membership to gain access to the sheet music and resources or use <a href="#" class="use-academy-credit hover-black">Academy Credits</a> to access this lesson.</p><p><a href="#" class="show-modal-sheet-music-sample hover-black">View a sheet music sample</a></p><p style="font-size: 10pt;">If you are seeing this message, and you are a member, <strong>make sure you are logged in</strong> to your account.</p>';
        $song_level_access = FALSE;
        if (is_array($tags)) {
            if (in_array('Song Lesson', $tags)) {
                $song_level_access = TRUE;
            }
            if (in_array('Academy Success', $tags)) {
                $song_level_access = TRUE;
            }
        }
        if ($user_membership_level == 'ACADEMY_ACADEMY_NC' || $user_membership_level == 'ACADEMY_ACADEMY' || $user_membership_level == 'ACADEMY_PREMIER') {
            $access = TRUE;
        } elseif ($post_id == 587 || $post_id == 547 || $post_id == 548) {
            $access = TRUE;
        } elseif ($user_membership_level == 'ACADEMY_SONG' && $song_level_access == TRUE) {
            $access = TRUE;
        } elseif (je_check_rental_access($post_id) == 'active') {
            $access = TRUE;
        }
        if ($user_membership_level == FALSE || $access == FALSE) {
            return $no_access_message;
        }
        if (je_return_membership_expired() == 'true') {
            return $no_access_message;
        }
        global $wpdb;
        $domain = 'jazzedge.academy';
        $resources = unserialize($wpdb->get_var("SELECT resources FROM academy_lessons WHERE ID = $lesson_id "));
        $return = '<div style="text-align: left; "><ul style="list-style: none; padding:10px 0px; margin:0px;">';
        foreach ($resources as $k => $v) {
            if (!empty($v)) {
                $found = TRUE;
            }
            $favorite_this_resource = '';
            $is_resource_favorited = $wpdb->get_var("SELECT user_id FROM academy_favorites WHERE course_or_lesson_id = $lesson_id AND type = 'resource' AND user_id = $user_id AND link = '$v' ");
            if (!empty($is_resource_favorited)) {
                $favorite_this_resource .= ' <span class="unfavorite_resource"><a href="/willie/favorite_resource.php?action=unfavorite&link_type=' . $k . '&lesson_id=' . $lesson_id . '"><i class="fa-solid fa-folder-minus"></i></a></span>';
            } else {
                $favorite_this_resource .= ' <span class="favorite_resource"><a href="/willie/favorite_resource.php?action=favorite&link_type=' . $k . '&lesson_id=' . $lesson_id . '&v=' . $v . '"><i class="fa-solid fa-folder-plus"></i></a></span>';
            }
            switch (substr($k, 0, 3)) {
                case 'mp3':
                    $icon = '<i class="fa-sharp fa-solid fa-volume"></i>';
                    $resource_name = 'Lesson Audio Only';
                    break;
                case 'mid':
                    $icon = '<i class="fa-sharp fa-solid fa-sliders-h"></i>';
                    $resource_name = 'MIDI Files';
                    break;
                case 'ire':
                    $icon = '<i class="fa-sharp fa-solid fa-sliders-h"></i>';
                    $resource_name = 'iRealPro';
                    break;
                case 'pdf':
                    $icon = '<i class="fa-sharp fa-solid fa-music"></i>';
                    $resource_name = 'Sheet Music';
                    break;
                case 'jam':
                    $icon = '<i class="fa-sharp fa-solid fa-drum"></i>';
                    $resource_name = 'Backing Track';
                    break;
                case 'cal':
                    $icon = '<i class="fa-sharp fa-solid fa-people-arrows"></i>';
                    $resource_name = 'Call & Response';
                    break;
                case 'zip':
                    $icon = '<i class="fa-sharp fa-solid fa-file-zipper"></i>';
                    $resource_name = 'Zip File';
                    break;
            }
                        $return .= (!empty($v) && $k != 'note') ? '<li class="resource-list-item">' . $icon . ' <a href="https://' . $domain . '/je_link.php?id=' . $lesson_id . '&link=' . $v . '" class="hover-black" target="_blank">' . $resource_name . '</a> <span class="resource_type">' . $k . '</span>' . $favorite_this_resource . '</li>' : '';
            $note_return = ($k == 'note' && !empty($v)) ? '<li class="resource-list-item"><i class="fa fa-sticky-note"></i> ' . $v . '</li>' : '';
        }
        $return .= $note_return;
        $return .= '</ul></div>';
        $return = (je_return_registered_member() == 'true') ? $return : '<span class="error">Sorry, you must be a <a href="/register">registered user</a> to view these resources.</span>';
        return ($found == TRUE) ? $return : '<p>No resources for this lesson.</p>';
    }

    function je_return_lesson_resources_new($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(),
            $atts,
            'je_return_lesson_resources_new'
        );
        global $user_membership_level_num;
        $access = FALSE;
        $user_id = get_current_user_id();
        $post_id = get_the_ID();
        $lesson_id = get_post_meta($post_id, 'lesson_id', true);
        $je_has_lesson_access = je_has_lesson_access();
        $academy_credit_access = je_check_academy_credit_access();
        $no_access_message = '<h4 style="color:#f04e23; text-align: center; width: 100%;">Looking for sheet music and backing tracks?</h4><p><a href="/signup" class="hover-black ">Upgrade</a> your membership to gain access to the sheet music and resources.</p><p><a href="#" class="show-modal-sheet-music-sample hover-black">View a sheet music sample</a></p><p style="font-size: 10pt;">If you are seeing this message, and you are a member, <strong>make sure you are logged in</strong>.</p>';

		$payment_failed = do_shortcode('[memb_has_any_tag tagid=7772]');
		if ($payment_failed === 'Yes') { return $no_access_message; }
	
		if ($user_membership_level_num >= 1)  { 
			$access = TRUE;
		}
		
        if ($post_id == 587 || $post_id == 547 || $post_id == 548) { // 30-Day Playbook
            $access = TRUE;
        }
        if ($je_has_lesson_access === 'true' && $user_membership_level_num > 1) {
            $access = TRUE;
        }
        if ($academy_credit_access === 'true') {
            $access = TRUE;
        }
        if ($access === FALSE) {
            return $no_access_message;
        }

        if (je_return_membership_expired() == 'true') {
            return $no_access_message;
        }

        global $wpdb;
        $domain = 'jazzedge.academy';
        $resource_name = '';
        $note_return = '';
        $resources = unserialize($wpdb->get_var("SELECT resources FROM academy_lessons WHERE ID = $lesson_id "));
        $return = '<div style="text-align: left; "><ul style="list-style: none; padding:10px 0px; margin:0px;">';
        foreach ($resources as $k => $v) {
            if (!empty($v)) {
                $found = TRUE;
            }
            $favorite_this_resource = '';
            $is_resource_favorited = $wpdb->get_var("SELECT user_id FROM academy_favorites WHERE course_or_lesson_id = $lesson_id AND type = 'resource' AND user_id = $user_id AND link = '$v' ");
            if (!empty($is_resource_favorited)) {
                $favorite_this_resource .= ' <span class="unfavorite_resource"><a href="/willie/favorite_resource.php?action=unfavorite&link_type=' . $k . '&lesson_id=' . $lesson_id . '"><i class="fa-solid fa-folder-minus"></i></a></span>';
            } else {
                $favorite_this_resource .= ' <span class="favorite_resource"><a href="/willie/favorite_resource.php?action=favorite&link_type=' . $k . '&lesson_id=' . $lesson_id . '&v=' . $v . '"><i class="fa-solid fa-folder-plus"></i></a></span>';
            }
            switch (substr($k, 0, 3)) {
                case 'mp3':
                    $icon = '<i class="fa-sharp fa-solid fa-volume"></i>';
                    $resource_name = 'Lesson Audio Only';
                    break;
                case 'mid':
                    $icon = '<i class="fa-sharp fa-solid fa-sliders-h"></i>';
                    $resource_name = 'MIDI Files';
                    break;
                case 'ire':
                    $icon = '<i class="fa-sharp fa-solid fa-sliders-h"></i>';
                    $resource_name = 'iRealPro';
                    break;
                case 'pdf':
                    $icon = '<i class="fa-sharp fa-solid fa-music"></i>';
                    $resource_name = 'Sheet Music';
                    break;
                case 'jam':
                    $icon = '<i class="fa-sharp fa-solid fa-drum"></i>';
                    $resource_name = 'Backing Track';
                    break;
                case 'cal':
                    $icon = '<i class="fa-sharp fa-solid fa-people-arrows"></i>';
                    $resource_name = 'Call & Response';
                    break;
                case 'zip':
                    $icon = '<i class="fa-sharp fa-solid fa-file-zipper"></i>';
                    $resource_name = 'Zip File';
                    break;
            }
            if ($resource_name != 'Zip File') { // hiding zip files
                $return .= (!empty($v) && $k != 'note') ? '<li class="resource-list-item">' . $icon . ' <a href="https://' . $domain . '/je_link.php?id=' . $lesson_id . '&link=' . $v . '" class="hover-black" target="_blank">' . $resource_name . '</a> <span class="resource_type">' . $k . '</span>' . $favorite_this_resource . '</li>' : '';
            }
            $note_return = ($k === 'note' && !empty($v)) ? '<li class="resource-list-item"><i class="fa fa-sticky-note"></i> ' . $v . '</li>' : '';
        }
        $return .= $note_return;
        $return .= '</ul></div>';
        return (!empty($return)) ? $return : '<p>There are no resources for this lesson.</p>';
    }

    function je_return_lesson_progress($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'format' => 'percent',
                'lesson_id' => 0,
            ),
            $atts,
            'je_return_lesson_progress'
        );
        $format = $atts['format'];
        $post_id = get_the_ID();
        $lesson_id = get_post_meta($post_id, 'lesson_id', true);
        $lesson_id = ($atts['lesson_id'] > 0) ? $atts['lesson_id'] : $lesson_id;
        if ($format == 'percent') {
            return je_return_lesson_progress_percentage($lesson_id, 'percent');
        } elseif ($format == 'full') {
            return je_return_lesson_progress_percentage($lesson_id, 'full');
        }
    }

    function je_return_chapter_data($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'return' => 'chapter_title',
            ),
            $atts,
            'je_return_chapter_data'
        );
        $chapter_slug = (isset($_GET['c'])) ? sanitize_text_field($_GET['c']) : '';
        $atts_return = (isset($atts['return'])) ? $atts['return'] : '';
        global $wpdb;
        $q = $wpdb->get_row("SELECT * FROM academy_chapters WHERE slug = '$chapter_slug'");
        if ($atts_return == 'chapter_title') {
            $return = (isset($q->chapter_title)) ? stripslashes($q->chapter_title) : '';
        }
        if ($atts_return == 'vimeo_id') {
            $lesson_post_id = get_the_ID();
            $lesson_id = get_post_meta($lesson_post_id, 'lesson_id', true);
            $chapter_count = $wpdb->get_var("SELECT COUNT(ID) FROM academy_chapters WHERE lesson_id = $lesson_id");
            if ($chapter_count == 1) {
                $vimeo_id = $wpdb->get_var("SELECT vimeo_id FROM academy_chapters WHERE lesson_id = $lesson_id");
            } else {
                $vimeo_id = $q->vimeo_id;
            }
            $return = $vimeo_id;
        }
        if ($atts_return == 'youtube_id') {
            $lesson_post_id = get_the_ID();
            $lesson_id = get_post_meta($lesson_post_id, 'lesson_id', true);
            $chapter_count = $wpdb->get_var("SELECT COUNT(ID) FROM academy_chapters WHERE lesson_id = $lesson_id");
            if ($chapter_count == 1) {
                $youtube_id = $wpdb->get_var("SELECT youtube_id FROM academy_chapters WHERE lesson_id = $lesson_id");
            } else {
                $youtube_id = $q->youtube_id;
            }
            $return = $youtube_id;
        }
        if ($atts['return'] == 'duration') {
            $return = $q->duration;
        }
        return $return;
    }

    function je_return_course_data($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'return' => 'course_title',
                'course_id' => 0,
            ),
            $atts,
            'je_return_course_data'
        );
        $chapter_slug = (isset($_GET['c'])) ? sanitize_text_field($_GET['c']) : '';
                if ($atts['course_id'] > 0) {
            $course_id = intval($atts['course_id']);
        } else {
            $course_id = je_return_id_from_slug($chapter_slug, 'course');
        }

        if ($course_id == 0) {
            return 'No course found.';
        }
        global $wpdb;
        $q = $wpdb->get_row("SELECT * FROM academy_courses WHERE ID = $course_id ");
        if ($atts['return'] == 'course_id') {
            $return = $q->ID;
        }
        if ($atts['return'] == 'course_description') {
            $return = $q->course_description;
        }
        if ($atts['return'] == 'course_title') {
            $return = $q->course_title;
        }
        return $return;
    }

    function je_return_course_lessons($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'format' => 'well',
                'course_id' => 0,
            ),
            $atts,
            'je_return_course_lessons'
        );
        global $wpdb;
        $course_id = intval($atts['course_id']);
        $lessons = $wpdb->get_results("SELECT * FROM academy_lessons WHERE course_id = $course_id ORDER BY course_sort_order ASC");
        if (empty($lessons)) {
            return '<div style="text-align: center; width:100%; ">No lessons found.</div>';
        }
        // ********** SHOW IN WELL
        if ($atts['format'] == 'well') {
            $return = '<div class="well">';
            if (count($lessons) < 2) {
                $return .= '<div style="text-align: center; width:100%;"><span style="font-size:10pt;">(this collection has only 1 lesson)</span></div>';
            }
            $return .= '<ul class="list-items">';
            $counter = 1;
            $total_lesson_duration = 0;
            foreach ($lessons as $lesson) {
                $lesson_id = $lesson->ID;
                $lesson_link = je_get_permalink($lesson_id);
                $activity_completed = (je_is_lesson_complete($lesson_id) == TRUE) ? 'activity-completed' : '';
                $percent_complete = je_return_lesson_progress_percentage($lesson_id);
                $is_active_chapter = (isset($chapter) && $chapter->slug == $chapter_slug) ? 'active' : '';
                $return .= '
                <li class="' . $is_active_chapter . '" data-lesson="lesson-' . $counter . '">
                    <a href="' . $lesson_link . '">
                        <span class="item-number ' . $activity_completed . '">' . $counter . '</span>
                        <span class="item-title">' . stripslashes(htmlspecialchars($lesson->lesson_title)) . '</span>
                        <span class="item-label"><div style="width:95%;">
                            <div class="w3-light-grey">
                            <div class="w3-container w3-green" style="width:' . $percent_complete . '% "><span style="padding:0.01em 16px;">' . $percent_complete . '%</span></div>
                            </div>
                            </div>
                        </span>
                        <span class="item-label">' . stripslashes(htmlspecialchars($lesson->lesson_description)) . '</span>
                        <span class="item-label">Duration: ' . format_time($lesson->duration) . '</span>
                    </a>
                </li>
                <!--/.lesson-->';
                $counter++;
                $total_lesson_duration += $lesson->duration;
            }
            $return .= '<li style="padding:30px 0px; font-size:10pt; text-align:center;">Total Collection Duration = ' . format_time($total_lesson_duration) . '</li>
            </ul>
            <!-- /.list-items -->
            </div>
            <!-- /.well -->';
        }
        // ********** SHOW AS LIST
        if ($atts['format'] == 'list') {
            $return = '<ul>';
            foreach ($lessons as $lesson) {
                $return .= '<li><a href="' . je_get_permalink($lesson->ID) . '">' . stripslashes(htmlspecialchars($lesson->lesson_title)) . '</a></li>';
            }
            $return .= '</ul>';
        }
        return $return;
    }

    function je_return_course_progress($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'format' => 'percent',
                'course_id' => 0,
            ),
            $atts,
            'je_return_course_progress'
        );
        $format = $atts['format'];
        $post_id = get_the_ID();
        $course_id = get_post_meta($post_id, 'course_id', true);
        $course_id = ($atts['course_id'] > 0) ? $atts['course_id'] : $course_id;
        if ($format == 'percent') {
            return je_return_course_progress_percentage($course_id, 'percent');
        } elseif ($format == 'full') {
            return je_return_course_progress_percentage($course_id, 'full');
        }
    }

    function je_return_path_progress($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'format' => 'percent',
                'path_id' => 0,
            ),
            $atts,
            'je_return_path_progress'
        );
        $format = $atts['format'];
        $path_id = (isset($atts['path_id'])) ? $atts['path_id'] : get_the_ID();
        if (je_return_number_of_lessons_in_path($path_id) == 0) {
            return;
        }
        if ($format == 'percent') {
            return je_return_path_progress_percentage($path_id, 'percent');
        } elseif ($format == 'full') {
            return je_return_path_progress_percentage($path_id, 'full');
        }
    }

    function je_free_video_player($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'vimeo_id' => 362984500,
                'lesson_id' => 0,
            ),
            $atts,
            'je_video_player'
        );
        $vimeo_id = (isset($atts['vimeo_id'])) ? $atts['vimeo_id'] : 0;
        $lesson_id = (isset($atts['lesson_id'])) ? $atts['lesson_id'] : 0;
        $return = do_shortcode("[fvplayer src='https://vimeo.com/$vimeo_id']");
        return $return;
    }

    function je_video_player($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'vimeo_id' => 362984500,
                'lesson_id' => 0,
            ),
            $atts,
            'je_video_player'
        );
        $vimeo_id = (isset($atts['vimeo_id'])) ? $atts['vimeo_id'] : 0;
        $lesson_id = (isset($atts['lesson_id'])) ? $atts['lesson_id'] : 0;
        $post_id = get_the_ID();
        $user_membership_level = je_return_membership_level();
        $tags = get_post_meta($post_id, 'lesson_tags', true);
        $song_level_access = FALSE;
        if (is_array($tags)) {
            if (in_array('Song Lesson', $tags)) {
                $song_level_access = TRUE;
            }
            if (in_array('Academy Success', $tags)) {
                $song_level_access = TRUE;
            }
        }
        $rental_status = je_check_rental_access();
        if ($user_membership_level == 'ACADEMY_ACADEMY' || $user_membership_level == 'ACADEMY_PREMIER') {
            return do_shortcode("[fvplayer src='https://vimeo.com/$vimeo_id']");
        } elseif ($user_membership_level == 'ACADEMY_SONG' && $song_level_access == TRUE) {
            return do_shortcode("[fvplayer src='https://vimeo.com/$vimeo_id']");
        } else {
            return '<div class="video_no_access"><p>You do not have access to this lesson with your current membership level. <a href="/signup" class="hover-black ">Click here for upgrade options</a>.</p></div>';
        }
    }

    function je_video_player_new($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'vimeo_id' => 362984500,
                'lesson_id' => 0,
                'splash' => 'https://jazzedge.academy/wp-content/uploads/2023/12/splash-play-video.jpg',
                'splash_text' => ''
            ),
            $atts,
            'je_video_player_new'
        );
        $vimeo_id = (isset($atts['vimeo_id'])) ? $atts['vimeo_id'] : 0;
        $lesson_id = (isset($atts['lesson_id'])) ? $atts['lesson_id'] : 0;
        return do_shortcode("[fvplayer src='https://vimeo.com/$vimeo_id' splash='" . $atts['splash'] . "' splash_text='" . $atts['splash_text'] . "']");
    }

    function __ACCOUNT_FUNCTIONS__($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'link' => '',
            ),
            $atts,
            'TEMPLATE'
        );
                return $return;
    }

    function ja_support_ticket($atts, $content = NULL)
    {
        $atts = shortcode_atts(
            array(
                'foo' => '',
            ),
            $atts,
            'ja_support_ticket'
        );
        $user_id = get_current_user_id();

        $return = '<input type="hidden" name="user_data" value="' . $user_id . '" data-name="user_data" />';

        return $return;
    }

    function je_list_active_subscriptions($atts, $content = NULL)
    {
        if (!empty($_GET['step'])) {
            return;
        }
        //include('/nas/content/live/'.INSTALL.'/willie/infusion_connect.php');
        global $install, $app;
		// include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
        $returnFields = array('ContactId', 'Id', 'AutoCharge', 'BillingAmt', 'BillingCycle', 'LastBillDate', 'PaidThruDate', 'ProductId', 'StartDate', 'Status', 'BillingCycle', 'MerchantAccountId', 'MaxRetry', 'NumDaysBetweenRetry', 'PaymentGatewayId', 'ReasonStopped', 'SubscriptionPlanId', 'OriginatingOrderId', 'EndDate', 'NextBillDate');
        $contact_id = memb_getContactId();
        $ecd = keap_get_contact_fields($contact_id,array('_AcademyEligibleCancelDate'));
		$eligible_cancel_date = convert_infusionsoft_date($ecd['_AcademyEligibleCancelDate']);
		//echo '***'.$eligible_cancel_date;
		
        if (!empty($_GET['id'])) {
            $query = array('ContactId' => $contact_id, 'Status' => 'Active', 'Id' => intval($_GET['id']));
        } else {
            $query = array('ContactId' => $contact_id);
        }
        $subscriptions = $app->dsQuery("RecurringOrder", 100, 0, $query, $returnFields);

        $has_1_year = memb_hasAnyTags(array(9813, 9815, 9817, 9819));
        $academy_expiration_date = memb_getContactField('_AcademyExpirationDate');
        //$expiration_message = (!empty($academy_expiration_date)) ? '<div style="background:#fff4cc; text-align: center; padding: 15px;"><p>You have a non-recurring membership with access to Jazzedge Academy until: <strong>' . convert_infusionsoft_date($academy_expiration_date) . '</strong></p></div>' : '';

        if (!empty($subscriptions)) {
            $return = "
            <div class='rg-container hover-black'>
        <table class='rg-table zebra' summary='Hed'>
            <caption class='rg-header'>
                <span class='rg-dek'>$expiration_message<p>You can scroll within this box to see all of your memberships.</p></span>
            </caption>
            <thead>
                <tr>
                    <th class='text'>Status</th>
                    <th class='text'>Membership</th>
                    <th class='text '>Amount</th>
                    <th class='text'>Start Date</th>
                    <th class='text'>Next Billing</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>";
            krsort($subscriptions);
            $contact_id = memb_getContactId();
            foreach ($subscriptions as $subscription) {
                $id = $subscription['Id'];
                $returnFields = array('ProductName', 'ProductPrice', 'Sku');
                $pid = $subscription['ProductId'];
                $query = array('Id' => $pid);
                $next_bill_date = ($subscription['Status'] == 'Active' && $subscription['AutoCharge'] == 1) ? convert_infusionsoft_date($subscription['NextBillDate']) : 'Cancelled';
                $next_bill_date_raw = $subscription['NextBillDate'];
                $mysql_friendly_date = date('Y-m-d', strtotime($next_bill_date_raw));
                $start_date = convert_infusionsoft_date($subscription['StartDate']);
                $product = $app->dsQuery("Product", 1, 0, $query, $returnFields);
				$product_name = $product[0]['ProductName'];
				$product_name = ($product_name == 'JA_MONTHLY_STUDIO_DMP') ? 'Annual Studio Membership' : $product_name;
                $product_sku = $product[0]['Sku'];
                $billing_amount = number_format($subscription['BillingAmt'], 2, '.', ',');
                $billing_cycle = je_return_billing_cycle($subscription['BillingCycle']);
                $payment_gateway = $subscription['PaymentGatewayId'];
                $billing = ($subscription['AutoCharge'] == 1 && $subscription['Status'] == 'Active') ? '<strong style="color:green">Active</strong>' : '<strong style="color:red">Cancelled*</strong>';
                $return .= "<tr>
                <td>$billing</td>
                <td>$product_name ($pid)</td>
                <td>$$billing_amount/$billing_cycle</td>
                <td>$start_date</td>
                <td>$next_bill_date</td>";
                $current_user = wp_get_current_user();
                $user_email = $current_user->user_email;
                
                // cancellable PIDS:
                $academy_pids = array(62332, 62334, 62285, 62293, 62323, 62321, 62319, 62317, 62315, 62313, 62291, 62289, 62287, 62283, 62281, 62279, 62259, 62257, 62251, 62249, 62243, 62241, 62239, 62237);

                if ($subscription['Status'] == 'Active' && $payment_gateway != 5 && $subscription['AutoCharge'] == 1 && empty($_GET['step'])) {
                    $return .= "<td>
                <form method='post' target='' action='/upgrade-downgrade-membership'>
                <input type='hidden' name='action' value='save_upgrade_downgrade_data' />
                <input type='hidden' name='next_billing_date' value='$mysql_friendly_date' />
                <input type='hidden' name='current_product_name' value='$product_name' />
                <input type='hidden' name='current_product_id' value='$pid' />
                <input type='hidden' name='current_product_sku' value='$product_sku' />
                <!-- <button type='submit' class='je-button-square'>Change</button> -->
                </form></td>";
                    if (in_array($pid, $academy_pids)) {
                        $return .= "<td><a href='?step=1&contact=$contact_id&id=$id&pid=$pid&nbd=$mysql_friendly_date'>Cancel</a></td>";
                    } elseif ($pid === 62336) {
                        $return .= "<td><a href='https://jazzpianolessons.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$contact_id&Email=$user_email&redir=/cancel-subscription/' target='_blank'>Cancel</a></td>";
                    } elseif ($pid === 62171) {
                        $return .= "<td><a href='https://homeschoolpiano.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$contact_id&Email=$user_email&redir=/cancel-subscription/' target='_blank'>Cancel</a></td>";
                    } elseif ($pid === 62350 || $pid === 62352) {
                        $return .= "<td>Can cancel after<br />$eligible_cancel_date</td>";
                    } else {
                        $return .= "<td><a href='https://myjazzedge.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id=$contact_id&Email=$user_email&redir=/billing' target='_blank'>Cancel</a></td>";
                    }
                } elseif ($subscription['Status'] == 'Active' && $payment_gateway === 5 && $subscription['AutoCharge'] == 1 && empty($_GET['step'])) {
                    $has_card = keap_has_card();
                    if ($has_card == 'false') {
                        $return .= "<td>
                <a href=\"#\" onclick=\"return confirm('You paid using Paypal so you need to add a credit card in order to use the upgrade/downgrade system. Then come back to this page and click on the upgrade/downgrade button again. Or, you can cancel your membership and signup for a new membership using Paypal. This is the way Paypal works. They will not allow us to change or modify a subscription once started.')\"><button type='link' class='je-button-square'>Upgrade/Downgrade</button></a>
                </td>";
                    } else {
                        $return .= "<td>
                    <form method='post' target='' action='/upgrade-downgrade-membership'>
                    <input type='hidden' name='action' value='save_upgrade_downgrade_data' />
                    <input type='hidden' name='next_billing_date' value='$mysql_friendly_date' />
                    <input type='hidden' name='current_product_name' value='$product_name' />
                    <input type='hidden' name='current_product_id' value='$pid' />
                    <input type='hidden' name='current_product_sku' value='$product_sku' />
                    <button type='submit' class='je-button-square'>Upgrade/Downgrade</button>
                    </form></td>";
                    }
                    if (in_array($pid, $academy_pids)) {
                        $return .= "<td><a href='?step=1&contact=$contact_id&id=$id&pid=$pid'>Cancel Membership</a></td>";
                    } elseif ($pid === 62350 || $pid === 62352) {
                        $return .= "<td>Can cancel after<br />$eligible_cancel_date</td>";
                    } else {
                        $return .= "<td>Contact us to cancel.</td>";
                    }
                } elseif ($pid == 62293) {
                    echo "<tr>
                        <td>1-Year</td>
                        <td>Jazzedge Academy Premier</td>
                        <td></td>
                        <td></td>
                    </tr>";
                } else {
                    $return .= '<td></td><td></td>';
                }

                $return .= '</tr>';
            }
            $return .= '</tbody></table></div>';
        } else {
            if (memb_hasAnyTags(array(7754))) {
                return '<p class="center bold_red">You are part of a HomeSchoolPiano membership. Billing is handled through the master account.</p>';
            }
            if (memb_hasAnyTags(array(7746))) {
                return '<p class="center bold_red">You purchased through the HomeSchool Buyers Co-op. Please visit their site for your invoice and payment info.</p>';
            }
            if (memb_hasAnyTags(array(9661)) && empty($academy_expiration_date)) {
                return '<p class="center bold_red" style="font-size: 18pt;">You currently have a free trial to Jazzedge Academy. <br /><a href="/signup" class="hover-black ">Click here to upgrade your membership</a>.</p>';
            }
            if (!empty($academy_expiration_date)) {
                return $expiration_message;
            }

            $return .= '<p class="center">You do not have any <u>active</u>, recurring, memberships in the system. Check your invoices for 1x payments. Please contact us if this is in error.</p><p class="center" style="font-size: 14pt; color: red;" >If you have access to the site, this likely means that you purchased a 1x, non-recurring membership.</p>';
        }
        return $return;
    }

    function je_cancel_academy_subscription($atts, $content = NULL)
    {
        $step = $_GET['step'];
        if (empty($step)) {
            return;
        }
        $contact_id = memb_getContactId();
        $user_id = get_current_user_id();
        $pid = intval($_GET['pid']);
        $subscription_id = intval($_GET['id']);
        $security = generateRandomString(7);

        if ($step == 1) {
        	global $install, $app;
			include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
//            include('/nas/content/live/'.INSTALL.'/willie/infusion_connect.php');
            $returnFields = array('ContactId', 'Id', 'ProductId', 'SubscriptionPlanId', 'NextBillDate');
            $query = array('ContactId' => $contact_id, 'Status' => 'Active', 'Id' => intval($_GET['id']));
            $subscriptions = $app->dsQuery("RecurringOrder", 1, 0, $query, $returnFields);
            $next_bill_date = $subscriptions[0]['NextBillDate'];
            $u = $app->updateCon($contact_id, array("_AcademyUpgradeDowngradeDate" => $next_bill_date));
        }
        if ($step == 2) {
            return do_shortcode("<style>#memb_actionset_button_1 {border-radius: 8px; background-color: red; color: white;}#memb_actionset_button_1:hover {background-color: black;}</style><div style='text-align:center;'>[memb_actionset_button button_text='Yes. Cancel & Delete My Membership' tag_ids=9745 redirect_url='?step=3&contact=$contact_id&action=delete&pid=$pid&id=$subscription_id&security=$security$user_id']<p style='font-size: 10pt;'>(You'll still keep the time you paid for)</p></div>");
        }
        if ($step == 3) {
            $security_user_id = substr($_GET['security'], 7);
            if ($security_user_id != $user_id) {
                wp_redirect('https://jazzedge.academy/');
                exit;
            }
            global $wpdb;
        	global $install, $app;
			include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
            
           //include('/nas/content/live/'.INSTALL.'/willie/infusion_connect.php');
            $returnFields = array('ContactId', 'Id', 'ProductId', 'SubscriptionPlanId', 'NextBillDate');
            $query = array('ContactId' => $contact_id, 'Status' => 'Active', 'Id' => intval($_GET['id']));
            $subscriptions = $app->dsQuery("RecurringOrder", 1, 0, $query, $returnFields);
            $current_user = wp_get_current_user();
            $username = $current_user->user_login;
            $user_email = $current_user->user_email;
            $user_fname = $current_user->user_firstname;
            $user_lname = $current_user->user_lastname;
            $student_name = $user_fname . ' ' . $user_lname;
            $user_id = $current_user->ID;
            $next_bill_date = $subscriptions[0]['NextBillDate'];
            $lose_access_on = convert_infusionsoft_date($next_bill_date);

            $cancel_tag_id = 0;
            if ($pid == 62334) {
                $cancel_tag_id = 9962;
            }
            if ($pid == 62332) {
                $cancel_tag_id = 9960;
            }
            if ($pid == 62285) {
                $cancel_tag_id = 9837;
            }
            if ($pid == 62293) {
                $cancel_tag_id = 9964;
            }
            if ($pid == 62241) {
                $cancel_tag_id = 9739;
            }
            if ($pid == 62239) {
                $cancel_tag_id = 9737;
            }
            if ($pid == 62257) {
                $cancel_tag_id = 9811;
            }
            if ($pid == 62237) {
                $cancel_tag_id = 9735;
            }
            if ($pid == 62283) {
                $cancel_tag_id = 9831;
            }
            if ($pid == 62291) {
                $cancel_tag_id = 9980; // 9980	CANCEL_JA_YEAR_LSN_CLASSES
            }
            if ($pid == 62281) {
                $cancel_tag_id = 9833;
            }
            if ($pid == 62279) {
                $cancel_tag_id = 9835;
            }
            
            if ($pid == 62350) { // ja_monthly_studio_dmp
                //$cancel_tag_id = 0000;
            }
            
             if ($pid == 62352) { // ja_monthly_premier_dmp
                //$cancel_tag_id = 0000;
            }
            
			if ($cancel_tag_id > 0) {
	            memb_setTags(array($cancel_tag_id));
	        }
            $u = $app->updateCon($contact_id, array("_AcademyExpirationDate" => $next_bill_date));

            $today = date('Y-m-d');
            $found = $wpdb->get_var("SELECT ID FROM academy_cancellations WHERE date = '$today' && contact_id = $contact_id && product_id = $pid");
            $data = array(
                'username' => $username,
                'student_name' => $student_name,
                'user_id' => $user_id,
                'contact_id' => $contact_id,
                'date' => date('Y-m-d'),
                'subscription_id' => $subscription_id,
                'product_id' => $subscriptions[0]['ProductId'],
                'expiration_date' => date('Y-m-d', strtotime($subscriptions[0]['NextBillDate'])),
            );
            $format = array('%s', '%s', '%d', '%d', '%s', '%d', '%d', '%s');
            if (empty($found)) {
                $wpdb->insert('academy_cancellations', $data, $format);
            }
        }
        return;
    }

    function je_list_payments($atts, $content = null)
    {
        $atts = shortcode_atts(
            array(
                'view' => 'd',
            ),
            $atts,
            'je_list_payments'
        );
        $return_array = array();
		global $install, $app;
		// include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
        
       // include('/nas/content/live/'.INSTALL.'/willie/infusion_connect.php');
        $contact_id = memb_getContactId();
        $payments_query = array('ContactId' => $contact_id);
        $payments_returnFields = array('ContactId', 'Id', 'PayAmt', 'PayDate', 'PayType', 'InvoiceId');
        $payments = $app->dsQuery("Payment", 200, 0, $payments_query, $payments_returnFields);

        if (memb_hasAnyTags(array(9757))) {
            $return .= '<p class="center bold_red">You have received a free month of access to Jazzedge Academy.</p>';
        }

        $return .= "<div class='rg-container hover-black' style='border:1px solid red;'>
    <table class='rg-table zebra' summary='Hed'>
        <caption class='rg-header'>
            <span class='rg-dek'>The following invoices are unpaid. Click the Pay Now button to pay your invoice. If you need to update your credit card, click the credit card button above.</span>
        </caption>
        <thead>
        <tr>
                <th class='text'>Invoice #</th>
                <th class='text'>Product</th>
                <th class='text'>Date Due</th>
                <th class='text'>Amount Due</th>
                <th class='text'>Choose Card</th>
                <th class='text'></th>
            </tr>
        </thead>
        <tbody>
        " . do_shortcode("[memb_list_invoices paid=0 unpaid=1]
 <tr>
 <td>%%invoice.id%%</td>
 <td>%%description%%</td>
 <td>%%date.due%%</td>
 <td>$%%amount.due%%</td>
 <td>%%creditcard.dropdown%%</td>
 <td>%%submit%%</span></td>
 </tr>
 [/memb_list_invoices]") . "</thead>
        </tbody></table></div>";

        if (!empty($payments)) {
            $return .= "
            <div class='rg-container hover-black'>
        <table class='rg-table zebra' summary='Hed'>
            <caption class='rg-header'>
                <span class='rg-dek'>You can scroll within this box to see all invoices</span>
            </caption>
            <thead>
                <tr>
                    <th class='text'>Invoice #</th>
                    <th class='text'>Payment Date</th>
                    <th class='text'>Type</th>
                    <th class='text'>Amount</th>
                    <th class='text'></th>
                </tr>
            </thead>
            <tbody>";
            $total_amount = 0;

            krsort($payments);
            foreach ($payments as $payment) {
                $amount = number_format($payment['PayAmt'], 2, '.', ',');
                $total_amount += $amount;
                if ($payment['PayType'] != 'Credit') {
                    $pay_type = ($payment['PayType'] == 'Refund') ? '<span class="bold_red">Refund</span>' : $payment['PayType'];
                    $return .= "<tr>
                                <td><a href='https://jazzedge.academy/receipt/?invoice_id=" . $payment['InvoiceId'] . "' target='_blank'>" . $payment['InvoiceId'] . "</a></td>
                                <td>" . convert_infusionsoft_date($payment['PayDate']) . "</td>
                                <td>$pay_type</td>
                                <td>$$amount</td>
                                <td><a href='https://jazzedge.academy/receipt/?invoice_id=" . $payment['InvoiceId'] . "' target='_blank' class=''><i class='fa-sharp fa-solid fa-file-invoice-dollar'></i> View Invoice</a>&nbsp;";

                    if (strtotime($payment['PayDate']) > strtotime('-30 days') && $payment['PayType'] != 'Refund') {
                        //$return .= "<a href='https://jazzedge.academy/refund-request/?invoice_id=".$payment['InvoiceId']."&contact_id=$contact_id&amount=$amount' target='_blank' class='button' style='background-color: red;'>Request Refund</a>";
                    }
                    $return .= "</td></tr>";
                }
            }
            $return .= '</tbody></table></div>';
        } else {
            $return .= '<p class="center">You do not have any invoices in the system. Please contact us if this is in error.</p>';
        }
        return $return;
    }
    
function ja_return_user_data($atts, $content = NULL) {
    global $wpdb, $user_id;
    
    // Ensure $user_id is set correctly
    $user_id = get_current_user_id();

    // Define the default attributes
    $atts = shortcode_atts(array('data' => ''), $atts, 'ja_return_user_data');

    // Map the attribute values to their respective table names
    $tables = array(
        'video_views' => 'je_video_tracking',
        'link_views' => 'je_link_views',
        'practice_curriculum_progress' => 'je_practice_curriculum_progress',
        'completed_chapters' => 'academy_completed_chapters',
        'practice_curriculum_assignments' => 'je_practice_curriculum_assignments',
        'favorites' => 'academy_favorites',
        'practice_log' => 'academy_practice_log',
        'completed_lessons' => 'academy_completed_lessons',
        'practice_actions' => 'practice_actions',
        'premier_quiz_results' => 'academy_premier_quiz_results',
        'user_notes' => 'academy_user_notes',
        'pc_graded' => 'academy_pc_graded',
        'recently_viewed' => 'academy_recently_viewed',
        'user_repertoire' => 'academy_user_repertoire'
    );

    // Get the table name based on the attribute
    $table = isset($tables[$atts['data']]) ? $tables[$atts['data']] : '';

    if ($table) {
        // Prepare and execute the query to count the records
        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE user_id = %d AND deleted_at IS NULL", $user_id);
        $count = $wpdb->get_var($query);

        return $count;
    } else {
        return 'Invalid data attribute';
    }
}
    
}

$wmyette_shortcodes = new Wmyette_Shortcodes();