<?php
/**
 * Plugin Name: Academy Practice Hub with Dashboard
 * Description: Complete practice tracking and gamification system with leaderboards, badges, and progress analytics for JazzEdge Academy students.
 * Version: 1.3
 * Author: JazzEdge
 * Text Domain: academy-practice-hub
 */
if (!defined('ABSPATH')) { exit; }

// Intentionally minimal by default. Enable wire‑through to test parity without moving code.

// Define a toggle constant in wp-config.php or here to enable wire-through mode.
if (!defined('APH_WIRE_THROUGH')) {
    define('APH_WIRE_THROUGH', false); // Disabled - Academy plugin is now self-contained
}

// Load required classes (we'll instantiate conditionally)
require_once __DIR__ . '/includes/class-je-crm-sender.php';
require_once __DIR__ . '/includes/database-schema.php';
require_once __DIR__ . '/includes/class-database.php';
require_once __DIR__ . '/includes/class-gamification.php';
require_once __DIR__ . '/includes/class-logger.php';
require_once __DIR__ . '/includes/class-rate-limiter.php';
require_once __DIR__ . '/includes/class-cache.php';
require_once __DIR__ . '/includes/class-validator.php';
require_once __DIR__ . '/includes/class-audit-logger.php';
require_once __DIR__ . '/includes/class-rest-api.php';
require_once __DIR__ . '/includes/class-admin-pages.php';
require_once __DIR__ . '/includes/class-frontend.php';
require_once __DIR__ . '/includes/class-jpc-handler.php';

// Initialize database schema on activation
register_activation_hook(__FILE__, 'aph_activate');

function aph_activate() {
    // Create tables
    APH_Database_Schema::create_tables();
    
    // Add leaderboard columns to existing tables
    APH_Database_Schema::add_leaderboard_columns();

    // Add timezone columns to practice sessions table
    APH_Database_Schema::add_practice_session_timezone_columns();
    
    // Add email tracking column
    APH_Database_Schema::add_email_tracking_column();
    
    // Update badges schema to use image_url instead of icon
    APH_Database_Schema::update_badges_schema();
    
    // Add additional performance indexes
    APH_Database_Schema::add_additional_indexes();
    
    // Create milestone submissions table if it doesn't exist
    aph_create_milestone_submissions_table();
}

/**
 * Create milestone submissions table
 */
function aph_create_milestone_submissions_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'jph_jpc_milestone_submissions';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            curriculum_id int(11) NOT NULL,
            video_url text NOT NULL,
            submission_date datetime NOT NULL,
            grade varchar(10) DEFAULT NULL,
            graded_on date DEFAULT NULL,
            teacher_notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY curriculum_id (curriculum_id),
            KEY submission_date (submission_date),
            KEY grade (grade),
            KEY graded_on (graded_on)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        error_log("APH: Created milestone submissions table: $table_name");
    }
}

// Initialize REST API
new JPH_REST_API();

// Initialize Admin Pages
new JPH_Admin_Pages();

// Ensure timezone columns exist for practice sessions
add_action('init', 'jph_ensure_practice_session_timezone_columns');
function jph_ensure_practice_session_timezone_columns() {
    APH_Database_Schema::add_practice_session_timezone_columns();
}

// Ensure sje_tag_id column exists for badges
add_action('init', 'aph_ensure_badges_sje_tag_id_column');
function aph_ensure_badges_sje_tag_id_column() {
    APH_Database_Schema::update_badges_schema();
}

// Initialize Practice Reminder System
add_action('init', 'jph_init_practice_reminders');
function jph_init_practice_reminders() {
    // Schedule daily reminder check if not already scheduled
    if (!wp_next_scheduled('jph_daily_practice_reminder_check')) {
        // Calculate next 9 AM - if it's already past 9 AM today, schedule for tomorrow
        $now = current_time('timestamp');
        $today_9am = strtotime('today 9:00');
        
        // If it's already past 9 AM today, schedule for tomorrow at 9 AM
        // Otherwise, schedule for today at 9 AM
        $first_run = ($now >= $today_9am) ? strtotime('tomorrow 9:00') : $today_9am;
        
        wp_schedule_event($first_run, 'daily', 'jph_daily_practice_reminder_check');
    }
}

// Initialize daily streak recalculation
add_action('init', 'jph_init_daily_streak_recalc');
function jph_init_daily_streak_recalc() {
    if (!wp_next_scheduled('jph_daily_streak_recalc')) {
        $now = current_time('timestamp');
        $wp_timezone = wp_timezone();
        $today_date = wp_date('Y-m-d', $now, $wp_timezone);
        $today_2am_dt = new DateTime($today_date . ' 02:00:00', $wp_timezone);
        $today_2am = $today_2am_dt->getTimestamp();
        $first_run = ($now >= $today_2am)
            ? $today_2am_dt->modify('+1 day')->getTimestamp()
            : $today_2am;
        wp_schedule_event($first_run, 'daily', 'jph_daily_streak_recalc');
    }
}

// Hook into the scheduled event
add_action('jph_daily_streak_recalc', 'jph_run_daily_streak_recalc');

/**
 * Recalculate streaks in daily batches to avoid timeouts.
 */
function jph_run_daily_streak_recalc() {
    global $wpdb;

    $stats_table = $wpdb->prefix . 'jph_user_stats';
    $batch_size = 200;
    $offset = (int) get_option('aph_streak_recalc_offset', 0);

    $user_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT user_id FROM {$stats_table} ORDER BY user_id ASC LIMIT %d OFFSET %d",
        $batch_size,
        $offset
    ));

    if (empty($user_ids)) {
        update_option('aph_streak_recalc_offset', 0);
        return;
    }

    $gamification = new APH_Gamification();
    foreach ($user_ids as $user_id) {
        $gamification->update_streak((int) $user_id, false);
    }

    update_option('aph_streak_recalc_offset', $offset + $batch_size);
}

// Initialize practice session timezone backfill
add_action('init', 'jph_init_practice_session_timezone_backfill');
function jph_init_practice_session_timezone_backfill() {
    if (get_option('jph_practice_session_timezone_backfill_complete')) {
        return;
    }

    if (!wp_next_scheduled('jph_backfill_practice_session_timezones')) {
        wp_schedule_single_event(time() + 60, 'jph_backfill_practice_session_timezones');
    }
}

add_action('jph_backfill_practice_session_timezones', 'jph_run_practice_session_timezone_backfill');
function jph_run_practice_session_timezone_backfill() {
    global $wpdb;

    $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
    $batch_size = 500;
    $last_id = (int) get_option('jph_practice_session_backfill_last_id', 0);
    $wp_timezone = wp_timezone();
    $utc_timezone = new DateTimeZone('UTC');

    $sessions = $wpdb->get_results($wpdb->prepare(
        "SELECT id, user_id, created_at, created_at_utc, user_timezone_at_session
         FROM {$sessions_table}
         WHERE id > %d
         ORDER BY id ASC
         LIMIT %d",
        $last_id,
        $batch_size
    ), ARRAY_A);

    if (empty($sessions)) {
        update_option('jph_practice_session_timezone_backfill_complete', 1);
        delete_option('jph_practice_session_backfill_last_id');
        return;
    }

    foreach ($sessions as $session) {
        $session_id = (int) $session['id'];
        $user_id = (int) $session['user_id'];

        $updates = array();
        $formats = array();

        if (empty($session['created_at_utc']) && !empty($session['created_at'])) {
            $session_datetime = new DateTime($session['created_at'], $wp_timezone);
            $session_datetime->setTimezone($utc_timezone);
            $updates['created_at_utc'] = $session_datetime->format('Y-m-d H:i:s');
            $formats[] = '%s';
        }

        if (empty($session['user_timezone_at_session'])) {
            $timezone_string = get_user_meta($user_id, 'aph_user_timezone', true);
            if (!empty($timezone_string)) {
                try {
                    new DateTimeZone($timezone_string);
                } catch (Exception $e) {
                    $timezone_string = '';
                }
            }
            if (empty($timezone_string)) {
                $timezone_string = wp_timezone_string();
            }
            $updates['user_timezone_at_session'] = $timezone_string;
            $formats[] = '%s';
        }

        if (!empty($updates)) {
            $wpdb->update(
                $sessions_table,
                $updates,
                array('id' => $session_id),
                $formats,
                array('%d')
            );
        }

        $last_id = $session_id;
    }

    update_option('jph_practice_session_backfill_last_id', $last_id);
    wp_schedule_single_event(time() + 60, 'jph_backfill_practice_session_timezones');
}

// Hook into the scheduled event
add_action('jph_daily_practice_reminder_check', 'jph_check_and_send_practice_reminders');

// Allow manual trigger via URL for external cron services
// Usage: https://yoursite.com/wp-json/aph/v1/reminders/trigger-cron?key=YOUR_SECRET_KEY
add_action('rest_api_init', function() {
    register_rest_route('aph/v1', '/reminders/trigger-cron', array(
        'methods' => 'GET',
        'callback' => 'jph_trigger_reminder_check_via_api',
        'permission_callback' => '__return_true' // We'll check the key instead
    ));
});

/**
 * Trigger reminder check via REST API (for external cron services)
 */
function jph_trigger_reminder_check_via_api($request) {
    // Get secret key from settings or use a default
    $secret_key = get_option('jph_reminder_cron_secret_key', '');
    
    // If no key is set, generate one and save it
    if (empty($secret_key)) {
        $secret_key = wp_generate_password(32, false);
        update_option('jph_reminder_cron_secret_key', $secret_key);
    }
    
    $provided_key = $request->get_param('key');
    
    // Verify the key
    if (empty($provided_key) || $provided_key !== $secret_key) {
        return new WP_Error('unauthorized', 'Invalid or missing secret key', array('status' => 401));
    }
    
    // Run the reminder check
    jph_check_and_send_practice_reminders();
    
    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Reminder check completed',
        'timestamp' => current_time('mysql')
    ));
}

/**
 * Check and send practice reminders
 */
function jph_check_and_send_practice_reminders() {
    global $wpdb;
    
    error_log('JPH Practice Reminders: Cron job started at ' . current_time('mysql'));
    
    // Check if FluentCRM is available
    if (!function_exists('fluentCrmApi')) {
        error_log('JPH Practice Reminders: ERROR - FluentCRM not available');
        return;
    }
    
    $table_name = $wpdb->prefix . 'jph_user_plans';
    $now = current_time('mysql');
    $wp_timezone = wp_timezone();
    $now_date = new DateTime($now, $wp_timezone);
    $now_start = clone $now_date;
    $now_start->setTime(0, 0, 0);
    
    error_log('JPH Practice Reminders: Current time: ' . $now . ' (WordPress timezone: ' . $wp_timezone->getName() . ')');
    
    // Get all users with reminders enabled
    $users = $wpdb->get_results($wpdb->prepare(
        "SELECT user_id, reminder_enabled, reminder_threshold_days, last_practiced_date, 
                last_reminder_sent, reminder_cooldown_days
         FROM {$table_name}
         WHERE reminder_enabled = 1
         AND (last_practiced_date IS NOT NULL OR reminder_threshold_days > 0)"
    ));
    
    error_log('JPH Practice Reminders: Found ' . count($users) . ' users with reminders enabled');
    
    if (empty($users)) {
        error_log('JPH Practice Reminders: No users found, exiting');
        return;
    }
    
    $reminders_sent = 0;
    $errors = 0;
    $skipped_threshold = 0;
    $skipped_cooldown = 0;
    $skipped_no_user = 0;
    
    // Get global cooldown from admin settings (default 5)
    $global_cooldown = get_option('jph_reminder_cooldown_days', 5);
    
    foreach ($users as $user_data) {
        $user_id = intval($user_data->user_id);
        $threshold = intval($user_data->reminder_threshold_days);
        // Use user's individual cooldown if set, otherwise use global cooldown
        $cooldown = intval($user_data->reminder_cooldown_days) ?: $global_cooldown;
        $last_practiced = $user_data->last_practiced_date;
        $last_reminder = $user_data->last_reminder_sent;
        
        // Ensure threshold is at least 5 days
        if ($threshold < 5) {
            // Update user's threshold to minimum 5
            $wpdb->update(
                $table_name,
                array('reminder_threshold_days' => 5),
                array('user_id' => $user_id),
                array('%d'),
                array('%d')
            );
            $threshold = 5;
            error_log("JPH Practice Reminders: Updated user {$user_id} threshold from {$user_data->reminder_threshold_days} to 5 (minimum required)");
        }
        
        error_log("JPH Practice Reminders: Checking user {$user_id} - threshold: {$threshold}, cooldown: {$cooldown}, last_practiced: {$last_practiced}, last_reminder: {$last_reminder}");
        
        // Calculate days since last practice using WordPress timezone
        if ($last_practiced) {
            $last_practiced_date = new DateTime($last_practiced, $wp_timezone);
            $last_practiced_start = clone $last_practiced_date;
            $last_practiced_start->setTime(0, 0, 0);
            $days_since = $now_start->diff($last_practiced_start)->days;
        } else {
            // User has never practiced, use a large number
            $days_since = 999;
        }
        
        error_log("JPH Practice Reminders: User {$user_id} - days since practice: {$days_since}, threshold: {$threshold}");
        
        // Check if threshold is met
        if ($days_since < $threshold) {
            error_log("JPH Practice Reminders: User {$user_id} - SKIPPED: Only {$days_since} days since practice (needs {$threshold})");
            $skipped_threshold++;
            continue; // Not enough days, skip
        }
        
        // Check cooldown period
        if ($last_reminder) {
            $last_reminder_date = new DateTime($last_reminder, $wp_timezone);
            $last_reminder_start = clone $last_reminder_date;
            $last_reminder_start->setTime(0, 0, 0);
            $days_since_reminder = $now_start->diff($last_reminder_start)->days;
            
            error_log("JPH Practice Reminders: User {$user_id} - days since last reminder: {$days_since_reminder}, cooldown: {$cooldown}");
            
            if ($days_since_reminder < $cooldown) {
                error_log("JPH Practice Reminders: User {$user_id} - SKIPPED: In cooldown period ({$days_since_reminder}/{$cooldown} days)");
                $skipped_cooldown++;
                continue; // Still in cooldown, skip
            }
        }
        
        // Get user email
        $user = get_user_by('ID', $user_id);
        if (!$user || !$user->user_email) {
            error_log("JPH Practice Reminders: User {$user_id} - SKIPPED: User not found or no email");
            $skipped_no_user++;
            continue;
        }
        
        // Get configured tag from settings
        $reminder_tag = get_option('jph_reminder_fluentcrm_tag', 'Practice Reminder');
        error_log("JPH Practice Reminders: User {$user_id} ({$user->user_email}) - ELIGIBLE: Applying tag '{$reminder_tag}'");
        
        // Apply FluentCRM tag
        $result = jph_apply_fluentcrm_reminder_tag($user_id, $user->user_email, $reminder_tag, $days_since);
        
        if ($result['success']) {
            // Update last_reminder_sent
            $wpdb->update(
                $table_name,
                array('last_reminder_sent' => $now),
                array('user_id' => $user_id),
                array('%s'),
                array('%d')
            );
            
            $reminders_sent++;
            error_log("JPH Practice Reminders: ✓ SUCCESS - Sent reminder to user {$user_id} ({$user->user_email}) - {$days_since} days since last practice");
        } else {
            $errors++;
            error_log("JPH Practice Reminders: ✗ FAILED - Could not send reminder to user {$user_id} ({$user->user_email}): {$result['message']}");
        }
    }
    
    error_log("JPH Practice Reminders: COMPLETE - Sent: {$reminders_sent}, Errors: {$errors}, Skipped (threshold): {$skipped_threshold}, Skipped (cooldown): {$skipped_cooldown}, Skipped (no user): {$skipped_no_user}");
}

/**
 * Apply FluentCRM tag for practice reminder
 */
function jph_apply_fluentcrm_reminder_tag($user_id, $user_email, $tag_name, $days_since) {
    try {
        // Check if FluentCRM is available
        if (!function_exists('fluentCrmApi')) {
            return array(
                'success' => false,
                'message' => 'FluentCRM not available'
            );
        }
        
        // Get or create contact
        $contact = fluentCrmApi('contacts')->getContact($user_email);
        
        if (!$contact) {
            // Create contact if doesn't exist
            $contact = fluentCrmApi('contacts')->createOrUpdate(array(
                'email' => $user_email,
                'first_name' => get_user_meta($user_id, 'first_name', true) ?: '',
                'last_name' => get_user_meta($user_id, 'last_name', true) ?: '',
                'user_id' => $user_id
            ));
        }
        
        if (!$contact) {
            return array(
                'success' => false,
                'message' => 'Could not create or retrieve FluentCRM contact'
            );
        }
        
        // Get tag ID by looking up the tag by name
        $tag_id = null;
        
        // First, try to find existing tag by name
        $all_tags = fluentCrmApi('tags')->all();
        if ($all_tags && is_array($all_tags)) {
            foreach ($all_tags as $t) {
                $t_title = is_object($t) ? $t->title : (is_array($t) ? ($t['title'] ?? '') : '');
                if ($t_title === $tag_name) {
                    $tag_id = is_object($t) ? $t->id : (is_array($t) ? ($t['id'] ?? null) : null);
                    break;
                }
            }
        }
        
        // If tag doesn't exist, create it
        if (!$tag_id) {
            $tag = fluentCrmApi('tags')->getOrCreate($tag_name);
            if ($tag) {
                $tag_id = is_object($tag) ? $tag->id : (is_array($tag) ? ($tag['id'] ?? null) : null);
            }
        }
        
        // Fallback: query database directly if API fails
        if (!$tag_id) {
            global $wpdb;
            $tags_table = $wpdb->prefix . 'fc_tags';
            $db_tag = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM {$tags_table} WHERE title = %s LIMIT 1",
                $tag_name
            ));
            
            if ($db_tag) {
                $tag_id = $db_tag->id;
            } else {
                $wpdb->insert(
                    $tags_table,
                    array(
                        'title' => $tag_name,
                        'slug' => sanitize_title($tag_name),
                        'created_at' => current_time('mysql')
                    ),
                    array('%s', '%s', '%s')
                );
                $tag_id = $wpdb->insert_id;
            }
        }
        
        if (!$tag_id) {
            return array(
                'success' => false,
                'message' => 'Could not get or create FluentCRM tag'
            );
        }
        
        // Apply tag to contact
        $contact->attachTags(array($tag_id));
        
        // Log the reminder tag application
        $user = get_user_by('ID', $user_id);
        $user_name = '';
        if ($user) {
            $first_name = get_user_meta($user_id, 'first_name', true);
            $last_name = get_user_meta($user_id, 'last_name', true);
            if ($first_name || $last_name) {
                $user_name = trim($first_name . ' ' . $last_name);
            } else {
                $user_name = $user->display_name ?: $user->user_login;
            }
        }
        
        // Get database instance and log
        require_once plugin_dir_path(__FILE__) . 'includes/class-database.php';
        $database = new JPH_Database();
        $database->log_reminder_tag($user_id, $user_email, $user_name, $tag_name, $days_since);
        
        return array(
            'success' => true,
            'message' => 'Tag applied successfully'
        );
        
    } catch (Exception $e) {
        return array(
            'success' => false,
            'message' => $e->getMessage()
        );
    }
}

/**
 * Remove practice reminder tags when user practices
 */
function jph_remove_practice_reminder_tags($user_id) {
    try {
        // Check if FluentCRM is available
        if (!function_exists('fluentCrmApi')) {
            return;
        }
        
        $user = get_user_by('ID', $user_id);
        if (!$user || !$user->user_email) {
            return;
        }
        
        $contact = fluentCrmApi('contacts')->getContact($user->user_email);
        if (!$contact) {
            return;
        }
        
        // Get all practice reminder tags
        $tags = fluentCrmApi('tags')->all();
        $reminder_tag_ids = array();
        
        foreach ($tags as $tag) {
            if (strpos($tag->title, 'Practice Reminder') === 0) {
                $reminder_tag_ids[] = $tag->id;
            }
        }
        
        if (!empty($reminder_tag_ids)) {
            $contact->detachTags($reminder_tag_ids);
        }
        
    } catch (Exception $e) {
        error_log("JPH Practice Reminders: Error removing tags for user {$user_id}: " . $e->getMessage());
    }
}

// Hook into practice session logging to remove reminder tags
add_action('jph_practice_session_logged', 'jph_remove_practice_reminder_tags_on_practice', 10, 1);
function jph_remove_practice_reminder_tags_on_practice($user_id) {
    jph_remove_practice_reminder_tags($user_id);
}

// Initialize Frontend (conditionally based on wire-through setting)
if (!defined('APH_FRONTEND_SEPARATED')) {
    define('APH_FRONTEND_SEPARATED', true); // Enable our frontend class
}
if (APH_FRONTEND_SEPARATED) {
    new JPH_Frontend();
}

// Register with Katahdin AI Hub if available
add_action('katahdin_ai_hub_init', function($hub) {
    $hub->register_plugin('academy-practice-hub', array(
        'name' => 'Academy Practice Hub',
        'version' => '4.0',
        'features' => array('chat', 'completions'),
        'quota_limit' => 5000 // tokens per month
    ));
});

// Also try to register on init in case the hook was already fired
add_action('init', function() {
    if (class_exists('Katahdin_AI_Hub') && function_exists('katahdin_ai_hub')) {
        $hub = katahdin_ai_hub();
        if ($hub && method_exists($hub, 'register_plugin')) {
            $hub->register_plugin('academy-practice-hub', array(
                'name' => 'Academy Practice Hub',
                'version' => '4.0',
                'features' => array('chat', 'completions'),
                'quota_limit' => 5000 // tokens per month
            ));
        }
    }
});

// Register plugin on admin init (when admin permissions are available)
add_action('admin_init', function() {
    if (class_exists('Katahdin_AI_Hub') && function_exists('katahdin_ai_hub')) {
        $hub = katahdin_ai_hub();
        if ($hub && method_exists($hub, 'register_plugin')) {
            $hub->register_plugin('academy-practice-hub', array(
                'name' => 'Academy Practice Hub',
                'version' => '4.0',
                'features' => array('chat', 'completions'),
                'quota_limit' => 5000 // tokens per month
            ));
        }
    }
});

// Add asset enqueuing hooks to match original plugin
add_action('wp_enqueue_scripts', 'aph_enqueue_frontend_assets');
add_action('admin_enqueue_scripts', 'aph_enqueue_admin_assets');

// Add JPC modal functionality
add_action('wp_footer', 'aph_add_jpc_modal_scripts');

/**
 * Enqueue frontend assets
 */
function aph_enqueue_frontend_assets() {
    // Only enqueue on pages that might have our shortcode
    if (is_singular() || is_home() || is_front_page()) {
        wp_enqueue_script('jquery');
    }
}

/**
 * Enqueue admin assets
 */
function aph_enqueue_admin_assets() {
    // Enqueue jQuery for admin pages
    wp_enqueue_script('jquery');
}

/**
 * Add JPC modal scripts and styles
 */
function aph_add_jpc_modal_scripts() {
    // Only add on pages that might have the JPC table
    if (is_singular() || is_home() || is_front_page()) {
        ?>
        <style>
        body.jpc-modal-open {
            overflow: hidden;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // JPC Video Modal functionality
            const modal = $('#jpc-video-modal');
            const modalTitle = $('#jpc-modal-title');
            const modalFocus = $('#jpc-modal-focus');
            const modalKey = $('#jpc-modal-key');
            const videoContainer = $('#jpc-video-container');
            const markCompleteBtn = $('#jpc-mark-complete');
            
            // JPC Submission Modal functionality
            const submissionModal = $('#jpc-submission-modal');
            const submissionTitle = $('#jpc-submission-title');
            const submissionFocus = $('#jpc-submission-focus');
            const submissionForm = $('#jpc-submission-form');
            const submissionSuccess = $('#jpc-submission-success');
            const youtubeUrlInput = $('#jpc-youtube-url');
            const curriculumIdInput = $('#jpc-curriculum-id');
            const submitMilestoneBtn = $('#jpc-submit-milestone');
            
            // JPC Help Modal functionality
            const helpModal = $('#jpc-help-modal');
            
            let currentStepData = null;
            let currentSubmissionData = null;
            
            // Open modal when clicking on completed key
            $(document).on('click', '.jpc-video-modal-trigger', function(e) {
                e.preventDefault();
                
                const stepId = $(this).data('step-id');
                const curriculumId = $(this).data('curriculum-id');
                const keyName = $(this).data('key-name');
                const focusTitle = $(this).data('focus-title');
                
                currentStepData = {
                    stepId: stepId,
                    curriculumId: curriculumId,
                    keyName: keyName,
                    focusTitle: focusTitle
                };
                
                // Update modal content
                modalTitle.text('JPC Lesson Video');
                modalFocus.text(focusTitle);
                modalKey.text(keyName);
                
                // Show loading
                videoContainer.html('<div class="jpc-loading">Loading video...</div>');
                
                // Show modal
                modal.show();
                $('body').addClass('jpc-modal-open');
                
                // Load video
                loadVideo(stepId, curriculumId);
            });
            
            // Open help modal when clicking "Help with JPC" link
            $(document).on('click', '.jpc-help-link', function(e) {
                e.preventDefault();
                // Ensure video src is set (in case it was cleared when closing)
                const iframe = $('#jpc-help-video-container iframe');
                if (iframe.length && !iframe.attr('src')) {
                    iframe.attr('src', 'https://player.vimeo.com/video/1145644391');
                }
                helpModal.show();
                $('body').addClass('jpc-modal-open');
            });
            
            // Close modal
            $(document).on('click', '.jpc-modal-close', function(e) {
                e.preventDefault();
                if (modal.is(':visible')) {
                    closeModal();
                } else if (submissionModal.is(':visible')) {
                    closeSubmissionModal();
                } else if (helpModal.is(':visible')) {
                    closeHelpModal();
                }
            });
            
            $(document).on('click', '.jpc-modal-overlay', function(e) {
                if (e.target === this) {
                    if (modal.is(':visible')) {
                        closeModal();
                    } else if (submissionModal.is(':visible')) {
                        closeSubmissionModal();
                    } else if (helpModal.is(':visible')) {
                        closeHelpModal();
                    }
                }
            });
            
            // Close modal on Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (modal.is(':visible')) {
                        closeModal();
                    } else if (submissionModal.is(':visible')) {
                        closeSubmissionModal();
                    } else if (helpModal.is(':visible')) {
                        closeHelpModal();
                    }
                }
            });
            
            // Mark as complete functionality
            markCompleteBtn.on('click', function() {
                if (currentStepData) {
                    markStepComplete(currentStepData.stepId, currentStepData.curriculumId);
                }
            });
            
            // Open submission modal when clicking "Get Graded"
            $(document).on('click', '.jpc-submission-modal-trigger', function(e) {
                e.preventDefault();
                
                const curriculumId = $(this).data('curriculum-id');
                const focusTitle = $(this).data('focus-title');
                
                currentSubmissionData = {
                    curriculumId: curriculumId,
                    focusTitle: focusTitle
                };
                
                // Update modal content
                submissionFocus.text(focusTitle);
                curriculumIdInput.val(curriculumId);
                youtubeUrlInput.val('');
                
                // Show form, hide success message
                submissionForm.show();
                submissionSuccess.hide();
                
                // Show modal
                submissionModal.show();
                $('body').addClass('jpc-modal-open');
            });
            
            // Submit milestone functionality
            submitMilestoneBtn.on('click', function() {
                const youtubeUrl = youtubeUrlInput.val().trim();
                const curriculumId = curriculumIdInput.val();
                
                if (!youtubeUrl) {
                    alert('Please enter a YouTube URL');
                    return;
                }
                
                if (!curriculumId) {
                    alert('Error: Curriculum ID missing');
                    return;
                }
                
                // Disable button and show loading
                submitMilestoneBtn.prop('disabled', true).text('Submitting...');
                
                // Submit via AJAX
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'jpc_submit_milestone',
                        curriculum_id: curriculumId,
                        youtube_url: youtubeUrl,
                        nonce: '<?php echo wp_create_nonce('jpc_submit_milestone'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            submissionForm.hide();
                            submissionSuccess.show();
                            
                            // Hide submit/cancel buttons and show only close button
                            $('.jpc-modal-footer').html(`
                                <button type="button" class="jpc-btn jpc-btn-primary jpc-modal-close">Close</button>
                            `);
                            
                            // Auto-close modal after 5 seconds
                            setTimeout(function() {
                                closeSubmissionModal();
                            }, 5000);
                        } else {
                            alert('Error submitting: ' + (response.data || 'Unknown error'));
                            submitMilestoneBtn.prop('disabled', false).text('Submit for Grading');
                        }
                    },
                    error: function() {
                        alert('Error submitting. Please try again.');
                        submitMilestoneBtn.prop('disabled', false).text('Submit for Grading');
                    }
                });
            });
            
            function closeModal() {
                modal.hide();
                $('body').removeClass('jpc-modal-open');
                videoContainer.html('<div class="jpc-loading">Loading video...</div>');
                markCompleteBtn.hide();
                currentStepData = null;
            }
            
            function closeSubmissionModal() {
                submissionModal.hide();
                $('body').removeClass('jpc-modal-open');
                submissionForm.show();
                submissionSuccess.hide();
                youtubeUrlInput.val('');
                submitMilestoneBtn.prop('disabled', false).text('Submit for Grading');
                
                // Restore original footer
                $('.jpc-modal-footer').html(`
                    <button type="button" class="jpc-btn jpc-btn-secondary jpc-modal-close">Cancel</button>
                    <button type="button" class="jpc-btn jpc-btn-primary" id="jpc-submit-milestone">Submit for Grading</button>
                `);
                
                currentSubmissionData = null;
            }
            
            function closeHelpModal() {
                // Stop the video by clearing the src
                const iframe = $('#jpc-help-video-container iframe');
                if (iframe.length) {
                    iframe.attr('src', '');
                }
                helpModal.hide();
                $('body').removeClass('jpc-modal-open');
            }
            
            function loadVideo(stepId, curriculumId) {
                // First verify the user has completed this step
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'jpc_verify_step_completion',
                        step_id: stepId,
                        curriculum_id: curriculumId,
                        nonce: '<?php echo wp_create_nonce('jpc_verify_step'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data.completed) {
                            // Get the video URL from the step data
                            if (response.data.vimeo_id) {
                                // Load the Vimeo video directly
                                videoContainer.html(`
                                    <iframe src="https://player.vimeo.com/video/${response.data.vimeo_id}" 
                                            width="100%" 
                                            height="100%" 
                                            frameborder="0" 
                                            allow="autoplay; fullscreen; picture-in-picture" 
                                            allowfullscreen>
                                    </iframe>
                                `);
                            } else {
                                // Fallback to lesson page if no Vimeo ID
                                const videoUrl = `/jpc-lesson/?step_id=${stepId}&cid=${curriculumId}`;
                                videoContainer.html(`
                                    <iframe src="${videoUrl}" 
                                            allowfullscreen 
                                            webkitallowfullscreen 
                                            mozallowfullscreen>
                                    </iframe>
                                `);
                            }
                            
                            // Show mark complete button if this is the current step
                            if (response.data.is_current_step) {
                                markCompleteBtn.show();
                            }
                        } else {
                            // User hasn't completed this step - show error
                            videoContainer.html(`
                                <div style="text-align: center; padding: 40px; color: #dc3545;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; margin-bottom: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    <h4>Access Denied</h4>
                                    <p>You must complete this step before you can view the video.</p>
                                </div>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        videoContainer.html(`
                            <div style="text-align: center; padding: 40px; color: #dc3545;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; margin-bottom: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                                <h4>Error Loading Video</h4>
                                <p>There was an error verifying your progress. Please try again.</p>
                            </div>
                        `);
                    }
                });
            }
            
            function markStepComplete(stepId, curriculumId) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'jpc_mark_step_complete',
                        step_id: stepId,
                        curriculum_id: curriculumId,
                        nonce: '<?php echo wp_create_nonce('jpc_mark_complete'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update the UI
                            markCompleteBtn.hide();
                            
                            // Show success message
                            videoContainer.html(`
                                <div style="text-align: center; padding: 40px; color: #10b981;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; margin-bottom: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                    <h4>Step Completed!</h4>
                                    <p>Great job! You can now proceed to the next step.</p>
                                </div>
                            `);
                            
                            // Refresh the page after a short delay to update the table
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            alert('Error marking step as complete: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('Error marking step as complete. Please try again.');
                    }
                });
            }
        });
        </script>
        <?php
    }
}


// Load WP-CLI commands if available
if (defined('WP_CLI') && WP_CLI) {
    // WP-CLI commands can be added here if needed
}

// When enabled, bootstrap the existing JazzEdge Practice Hub code paths for parity testing.
if (APH_WIRE_THROUGH) {
    // Only run if the original plugin is not active to avoid double-loading.
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    $original_plugin_slug = 'jazzedge-practice-hub/jazzedge-practice-hub.php';
    if (!is_plugin_active($original_plugin_slug)) {
        // Load the original plugin main file to restore admin menus, assets, and shortcode rendering
        $original_main = WP_PLUGIN_DIR . '/jazzedge-practice-hub/jazzedge-practice-hub.php';
        if (file_exists($original_main)) {
            require_once $original_main;
        }
    }
}
