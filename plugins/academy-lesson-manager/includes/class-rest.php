<?php
/**
 * REST API endpoints for ALM
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Rest_API {
    /** @var wpdb */
    private $wpdb;

    /** @var ALM_Database */
    private $database;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();

        add_action('rest_api_init', array($this, 'register_routes'));
        
        // Ensure REST API can authenticate users via cookies
        add_filter('determine_current_user', array($this, 'rest_determine_current_user'), 20);
    }
    
    /**
     * Help REST API determine current user from cookies
     * This ensures is_user_logged_in() works in REST API context
     */
    public function rest_determine_current_user($user_id) {
        // If user is already determined, return it
        if ($user_id) {
            return $user_id;
        }
        
        // Only apply to our REST API endpoints
        if (empty($_SERVER['REQUEST_URI']) || strpos($_SERVER['REQUEST_URI'], '/wp-json/alm/v1/') === false) {
            return $user_id;
        }
        
        // Try to get user from cookies
        if (!empty($_COOKIE)) {
            foreach ($_COOKIE as $name => $value) {
                if (strpos($name, 'wordpress_logged_in_') === 0) {
                    $cookie_parts = explode('|', $value);
                    if (!empty($cookie_parts[0]) && is_numeric($cookie_parts[0])) {
                        $potential_user_id = intval($cookie_parts[0]);
                        $user = get_user_by('id', $potential_user_id);
                        if ($user) {
                            // Verify cookie expiration
                            if (!empty($cookie_parts[1]) && intval($cookie_parts[1]) > time()) {
                                return $potential_user_id;
                            }
                        }
                    }
                    break;
                }
            }
        }
        
        return $user_id;
    }

    public function register_routes() {
        register_rest_route('alm/v1', '/search-lessons', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_search_lessons'),
            'permission_callback' => '__return_true',
            'args' => array(
                'q' => array('type' => 'string', 'required' => false),
                'page' => array('type' => 'integer', 'required' => false, 'default' => 1),
                'per_page' => array('type' => 'integer', 'required' => false, 'default' => 20),
                'membership_level' => array('type' => 'integer', 'required' => false),
                'lesson_level' => array('type' => 'string', 'required' => false, 'enum' => array('', 'beginner', 'intermediate', 'advanced', 'pro'), 'default' => ''),
                'tag' => array('type' => 'string', 'required' => false),
                'lesson_style' => array('type' => 'string', 'required' => false),
                'collection_id' => array('type' => 'integer', 'required' => false),
                'has_resources' => array('type' => 'string', 'required' => false, 'enum' => array('', 'has', 'none'), 'default' => ''),
                'song_lesson' => array('type' => 'string', 'required' => false, 'enum' => array('', 'y', 'n'), 'default' => ''),
                'teacher' => array('type' => 'string', 'required' => false),
                'key' => array('type' => 'string', 'required' => false),
                'has_sample' => array('type' => 'string', 'required' => false, 'enum' => array('', 'y', 'n'), 'default' => ''),
                'min_duration' => array('type' => 'integer', 'required' => false),
                'max_duration' => array('type' => 'integer', 'required' => false),
                'date_from' => array('type' => 'string', 'required' => false),
                'date_to' => array('type' => 'string', 'required' => false),
                'order_by' => array('type' => 'string', 'required' => false, 'enum' => array('relevance', 'date'), 'default' => 'relevance'),
                'search_source' => array('type' => 'string', 'required' => false, 'enum' => array('dashboard', 'shortcode'), 'default' => 'dashboard'),
            ),
        ));
        
        register_rest_route('alm/v1', '/search-lessons/ai-recommend', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_ai_recommendations'),
            'permission_callback' => array($this, 'check_ai_permission'),
            'args' => array(
                'q' => array('type' => 'string', 'required' => true),
                'results' => array('type' => 'array', 'required' => false, 'default' => array()),
            ),
        ));
        
        register_rest_route('alm/v1', '/ai-paths', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_ai_paths'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
        
        register_rest_route('alm/v1', '/ai-paths/(?P<id>\d+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_ai_path'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
        
        register_rest_route('alm/v1', '/user/lesson-level-preference', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_save_lesson_level_preference'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'lesson_level' => array('type' => 'string', 'required' => true, 'enum' => array('beginner', 'intermediate', 'advanced', 'pro')),
            ),
        ));
        
        register_rest_route('alm/v1', '/user/lesson-level-preference', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_lesson_level_preference'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
        
        register_rest_route('alm/v1', '/tags', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_tags'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('alm/v1', '/teachers', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_teachers'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('alm/v1', '/keys', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_keys'),
            'permission_callback' => '__return_true',
        ));
        
        // Essentials Library endpoints
        register_rest_route('alm/v1', '/library/select', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_library_select'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'lesson_id' => array('type' => 'integer', 'required' => true),
            ),
        ));
        
        register_rest_route('alm/v1', '/library/available', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_library_available'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
        
        register_rest_route('alm/v1', '/library/lessons', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_library_lessons'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
        
        register_rest_route('alm/v1', '/notifications/expand', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_expand_notification'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'text' => array('type' => 'string', 'required' => true),
            ),
        ));

        register_rest_route('alm/v1', '/lesson-analytics/students', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_lesson_analytics_students'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'lesson_post_id' => array('type' => 'integer', 'required' => true),
            ),
        ));

        register_rest_route('alm/v1', '/lesson-analytics/send-webhook', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_lesson_analytics_webhook'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'lesson_post_id' => array('type' => 'integer', 'required' => true),
                'webhook_url' => array('type' => 'string', 'required' => true),
            ),
        ));

        register_rest_route('alm/v1', '/lesson-analytics/send-fluentcrm', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_lesson_analytics_fluentcrm'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'lesson_post_id' => array('type' => 'integer',               'required' => true),
                'status'         => array('type' => 'string',                 'required' => false),
                'tag_id'         => array('type' => 'integer',               'required' => false, 'default' => 0),
                'cf_key'         => array('type' => 'string',                'required' => false, 'default' => ''),
                'cf_value'       => array('type' => 'string',                'required' => false, 'default' => ''),
                'emails'         => array(
                    'type'     => 'array',
                    'required' => false,
                    'default'  => array(),
                    'items'    => array('type' => 'string'),
                ),
            ),
        ));

        register_rest_route('alm/v1', '/lesson-analytics/fluentcrm-settings', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_lesson_analytics_fluentcrm_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));

        register_rest_route('alm/v1', '/lesson-analytics/fluentcrm-settings', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_save_lesson_analytics_fluentcrm_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'status'   => array('type' => 'string',  'required' => false),
                'tag_id'   => array('type' => 'integer', 'required' => false, 'default' => 0),
                'cf_key'   => array('type' => 'string',  'required' => false, 'default' => ''),
                'cf_value' => array('type' => 'string',  'required' => false, 'default' => ''),
            ),
        ));
        
        // Zoom webhook endpoint (unauthenticated, validates via shared secret)
        register_rest_route('alm/v1', '/zoom-recording', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_zoom_webhook'),
            'permission_callback' => '__return_true', // Public endpoint, validates secret internally
        ));
    }
    
    /**
     * Check if user has permission for AI recommendations
     */
    public function check_ai_permission() {
        // Allow logged-in users only
        return is_user_logged_in();
    }
    
    /**
     * Check if user has permission for user preferences
     */
    public function check_user_permission() {
        // Allow logged-in users
        return is_user_logged_in();
    }
    
    /**
     * Check if user has admin permission
     */
    public function check_admin_permission() {
        // Allow only users with manage_options capability
        return current_user_can('manage_options');
    }

    /**
     * Get students who viewed a lesson (admin only)
     */
    public function handle_lesson_analytics_students(WP_REST_Request $request) {
        $lesson_post_id = intval($request->get_param('lesson_post_id'));
        if ($lesson_post_id <= 0) {
            return new WP_Error('invalid_lesson', 'Invalid lesson_post_id', array('status' => 400));
        }

        $table_name = 'academy_recently_viewed';
        $users_table = $this->wpdb->users;
        $usermeta_table = $this->wpdb->usermeta;

        $rows = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT arv.user_id,
                    u.display_name,
                    u.user_email,
                    fn.meta_value AS first_name,
                    ln.meta_value AS last_name,
                    COUNT(*) as views,
                    MAX(arv.datetime) as last_viewed
             FROM {$table_name} arv
             LEFT JOIN {$users_table} u ON u.ID = arv.user_id
             LEFT JOIN {$usermeta_table} fn ON fn.user_id = arv.user_id AND fn.meta_key = 'first_name'
             LEFT JOIN {$usermeta_table} ln ON ln.user_id = arv.user_id AND ln.meta_key = 'last_name'
             WHERE arv.post_id = %d
               AND arv.deleted_at IS NULL
             GROUP BY arv.user_id, u.display_name, u.user_email, fn.meta_value, ln.meta_value
             ORDER BY last_viewed DESC",
            $lesson_post_id
        ), ARRAY_A);

        $students = array_map(function($student) {
            if (isset($student['user_email'])) {
                $student['email'] = $student['user_email'];
                unset($student['user_email']);
            }
            return $student;
        }, $rows);

        return rest_ensure_response(array(
            'success' => true,
            'students' => $students,
            'count' => count($students),
        ));
    }

    /**
     * Send lesson student list to webhook (admin only)
     */
    public function handle_lesson_analytics_webhook(WP_REST_Request $request) {
        $lesson_post_id = intval($request->get_param('lesson_post_id'));
        $webhook_url = esc_url_raw($request->get_param('webhook_url'));

        if ($lesson_post_id <= 0 || empty($webhook_url)) {
            return new WP_Error('invalid_request', 'lesson_post_id and webhook_url are required', array('status' => 400));
        }
        if (!preg_match('#^https?://#i', $webhook_url)) {
            return new WP_Error('invalid_url', 'Webhook URL must be http or https', array('status' => 400));
        }

        $table_name = 'academy_recently_viewed';
        $users_table = $this->wpdb->users;
        $usermeta_table = $this->wpdb->usermeta;

        $students = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT arv.user_id,
                    u.display_name,
                    u.user_email,
                    fn.meta_value AS first_name,
                    ln.meta_value AS last_name,
                    COUNT(*) as views,
                    MAX(arv.datetime) as last_viewed
             FROM {$table_name} arv
             LEFT JOIN {$users_table} u ON u.ID = arv.user_id
             LEFT JOIN {$usermeta_table} fn ON fn.user_id = arv.user_id AND fn.meta_key = 'first_name'
             LEFT JOIN {$usermeta_table} ln ON ln.user_id = arv.user_id AND ln.meta_key = 'last_name'
             WHERE arv.post_id = %d
               AND arv.deleted_at IS NULL
             GROUP BY arv.user_id, u.display_name, u.user_email, fn.meta_value, ln.meta_value
             ORDER BY last_viewed DESC",
            $lesson_post_id
        ), ARRAY_A);

        $students = array_map(function($student) {
            if (isset($student['user_email'])) {
                $student['email'] = $student['user_email'];
                unset($student['user_email']);
            }
            return $student;
        }, $students);

        $lesson_title = get_the_title($lesson_post_id);
        $base_payload = array(
            'lesson_post_id' => $lesson_post_id,
            'lesson_title' => $lesson_title ?: '',
        );

        $attempted = 0;
        $success_count = 0;
        $failures = array();
        $last_payload = null;
        $last_response_body = '';
        $last_status_code = 0;

        foreach ($students as $student) {
            $email = isset($student['email']) ? trim($student['email']) : '';
            if (empty($email)) {
                $failures[] = array(
                    'user_id' => $student['user_id'] ?? '',
                    'email' => '',
                    'error' => 'Missing email',
                );
                continue;
            }

            $payload = array_merge($base_payload, array(
                'email' => $email,
                'first_name' => $student['first_name'] ?? '',
                'last_name' => $student['last_name'] ?? '',
                'full_name' => $student['display_name'] ?? '',
                'views' => $student['views'] ?? 0,
                'last_viewed' => $student['last_viewed'] ?? '',
            ));

            $attempted++;
            $last_payload = $payload;

            $response = wp_remote_post($webhook_url, array(
                'headers' => array('Content-Type' => 'application/json'),
                'body' => wp_json_encode($payload),
                'timeout' => 20,
            ));

            if (is_wp_error($response)) {
                $failures[] = array(
                    'email' => $email,
                    'error' => $response->get_error_message(),
                );
                $last_status_code = 0;
                $last_response_body = '';
                continue;
            }

            $last_status_code = wp_remote_retrieve_response_code($response);
            $last_response_body = wp_remote_retrieve_body($response);
            if (strlen($last_response_body) > 2000) {
                $last_response_body = substr($last_response_body, 0, 2000) . '...';
            }

            if ($last_status_code < 200 || $last_status_code >= 300) {
                $failures[] = array(
                    'email' => $email,
                    'error' => 'Webhook returned HTTP ' . $last_status_code,
                    'response_body' => $last_response_body,
                );
                continue;
            }

            $success_count++;
        }

        $success = empty($failures);
        $message = $success
            ? 'Webhook delivered successfully.'
            : sprintf('Webhook completed with %d failures.', count($failures));

        return rest_ensure_response(array(
            'success' => $success,
            'message' => $message,
            'attempted' => $attempted,
            'successful' => $success_count,
            'failures' => array_slice($failures, 0, 10),
            'status_code' => $last_status_code,
            'response_body' => $last_response_body,
            'payload' => $last_payload ?: $base_payload,
        ));
    }

    /**
     * Send lesson students to support site CRM via JE_CRM_Sender (admin only).
     */
    public function handle_lesson_analytics_fluentcrm(WP_REST_Request $request) {
        $lesson_post_id = intval($request->get_param('lesson_post_id'));
        $saved = get_option('alm_lesson_analytics_fluentcrm_settings', array());
        if (!is_array($saved)) {
            $saved = array();
        }
        $saved_status = isset($saved['status']) ? sanitize_text_field($saved['status']) : 'subscribed';

        $status = sanitize_text_field($request->get_param('status'));
        if ($status === '' || $status === null) {
            $status = $saved_status;
        }
        if ($status === '') {
            $status = 'subscribed';
        }

        $tag_id   = intval($request->get_param('tag_id'));
        $cf_key   = sanitize_key((string) $request->get_param('cf_key'));
        $cf_value = sanitize_text_field((string) $request->get_param('cf_value'));

        $requested_emails = $request->get_param('emails');
        $filter_emails    = array();
        if (is_array($requested_emails) && count($requested_emails) > 0) {
            $filter_emails = array_map('sanitize_email', $requested_emails);
            $filter_emails = array_values(array_unique(array_filter(array_map('strtolower', $filter_emails))));
        }

        if ($lesson_post_id <= 0) {
            return new WP_Error('invalid_request', 'lesson_post_id is required', array('status' => 400));
        }

        update_option(
            'alm_lesson_analytics_fluentcrm_settings',
            array(
                'status'   => $status,
                'tag_id'   => $tag_id,
                'cf_key'   => $cf_key,
                'cf_value' => $cf_value,
            ),
            false
        );

        $table_name = 'academy_recently_viewed';
        $users_table = $this->wpdb->users;
        $usermeta_table = $this->wpdb->usermeta;

        $students = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT arv.user_id,
                    u.display_name,
                    u.user_email,
                    fn.meta_value AS first_name,
                    ln.meta_value AS last_name,
                    COUNT(*) as views,
                    MAX(arv.datetime) as last_viewed
             FROM {$table_name} arv
             LEFT JOIN {$users_table} u ON u.ID = arv.user_id
             LEFT JOIN {$usermeta_table} fn ON fn.user_id = arv.user_id AND fn.meta_key = 'first_name'
             LEFT JOIN {$usermeta_table} ln ON ln.user_id = arv.user_id AND ln.meta_key = 'last_name'
             WHERE arv.post_id = %d
               AND arv.deleted_at IS NULL
             GROUP BY arv.user_id, u.display_name, u.user_email, fn.meta_value, ln.meta_value
             ORDER BY last_viewed DESC",
            $lesson_post_id
        ), ARRAY_A);

        $students = array_map(function($student) {
            if (isset($student['user_email'])) {
                $student['email'] = $student['user_email'];
                unset($student['user_email']);
            }
            return $student;
        }, $students);

        if (count($filter_emails) > 0) {
            $allowed = array_flip($filter_emails);
            $students = array_values(array_filter($students, function ($student) use ($allowed) {
                $email = isset($student['email']) ? strtolower(trim($student['email'])) : '';
                return $email !== '' && isset($allowed[$email]);
            }));
        }

        $lesson_title = get_the_title($lesson_post_id);

        $attempted = 0;
        $success_count = 0;
        $failures = array();
        $last_payload = null;
        $last_response_body = '';
        $last_status_code = 0;

        foreach ($students as $student) {
            $email = isset($student['email']) ? trim($student['email']) : '';
            if (empty($email)) {
                $failures[] = array(
                    'user_id' => $student['user_id'] ?? '',
                    'email' => '',
                    'error' => 'Missing email',
                );
                continue;
            }

            $attempted++;
            $send_payload = array(
                'email'  => $email,
                'status' => $status,
            );
            if ($tag_id > 0) {
                $send_payload['add_tags'] = array($tag_id);
            }
            if ($cf_key !== '') {
                $send_payload['custom_fields'] = array($cf_key => $cf_value);
            }
            $last_payload = array_merge($send_payload, array(
                'lesson_post_id' => $lesson_post_id,
                'lesson_title'   => $lesson_title ?: '',
            ));

            $result = JE_CRM_Sender::send($send_payload);

            if (empty($result['success'])) {
                $err = isset($result['message']) ? $result['message'] : 'Unknown error';
                $failures[] = array(
                    'email' => $email,
                    'error' => $err,
                );
                $last_status_code = 0;
                $last_response_body = is_string($err) ? $err : wp_json_encode($result);
                continue;
            }

            $success_count++;
            $last_status_code = 200;
            $last_response_body = isset($result['message']) ? $result['message'] : wp_json_encode($result);
        }

        $failure_count = count($failures);
        $success = ($failure_count === 0);
        $message = $success
            ? 'CRM import completed successfully.'
            : sprintf('CRM import completed with %d failures.', $failure_count);

        return rest_ensure_response(array(
            'success' => $success,
            'message' => $message,
            'attempted' => $attempted,
            'successful' => $success_count,
            'failures' => array_slice($failures, 0, 10),
            'status_code' => $last_status_code,
            'response_body' => $last_response_body,
            'payload' => $last_payload ?: array(
                'lesson_post_id' => $lesson_post_id,
                'lesson_title'   => $lesson_title ?: '',
                'email'          => '',
                'status'         => $status,
                'tag_id'         => $tag_id,
                'cf_key'         => $cf_key,
                'cf_value'       => $cf_value,
            ),
        ));
    }

    /**
     * Get saved CRM (JE_CRM_Sender) settings for lesson analytics (admin only).
     */
    public function handle_get_lesson_analytics_fluentcrm_settings() {
        $settings = get_option('alm_lesson_analytics_fluentcrm_settings', array());
        if (!is_array($settings)) {
            $settings = array();
        }
        $status = isset($settings['status']) ? sanitize_text_field($settings['status']) : 'subscribed';
        if ($status === '') {
            $status = 'subscribed';
        }
        $tag_id   = isset($settings['tag_id']) ? intval($settings['tag_id']) : 0;
        $cf_key   = isset($settings['cf_key']) ? sanitize_key($settings['cf_key']) : '';
        $cf_value = isset($settings['cf_value']) ? sanitize_text_field($settings['cf_value']) : '';

        return rest_ensure_response(array(
            'success'  => true,
            'settings' => array(
                'status'   => $status,
                'tag_id'   => $tag_id,
                'cf_key'   => $cf_key,
                'cf_value' => $cf_value,
            ),
        ));
    }

    /**
     * Save CRM settings for lesson analytics (admin only).
     */
    public function handle_save_lesson_analytics_fluentcrm_settings(WP_REST_Request $request) {
        $status = sanitize_text_field($request->get_param('status'));
        if ($status === '') {
            $status = 'subscribed';
        }
        $tag_id   = intval($request->get_param('tag_id'));
        $cf_key   = sanitize_key($request->get_param('cf_key'));
        $cf_value = sanitize_text_field($request->get_param('cf_value'));

        $saved = array(
            'status'   => $status,
            'tag_id'   => $tag_id,
            'cf_key'   => $cf_key,
            'cf_value' => $cf_value,
        );

        update_option('alm_lesson_analytics_fluentcrm_settings', $saved, false);

        return rest_ensure_response(array(
            'success'  => true,
            'message'  => 'CRM settings saved.',
            'settings' => $saved,
        ));
    }
    
    /**
     * Handle notification text expansion with AI
     */
    public function handle_expand_notification(WP_REST_Request $request) {
        $text = sanitize_textarea_field($request->get_param('text'));
        
        if (empty($text)) {
            return new WP_Error('empty_text', 'No text provided to expand', array('status' => 400));
        }
        
        // Get system message from saved option, or use default
        $default_prompt = "You are an AI assistant helping to expand and improve notification messages for a music education platform (Jazzedge Academy). 

Your task is to take a brief notification message and expand it into a more detailed, engaging, and informative message.

Guidelines:
- Keep the friendly, encouraging tone appropriate for students
- Expand on key points naturally
- Make it more engaging and informative
- Maintain professional but warm communication style
- Don't add information that wasn't implied in the original
- Keep it concise but comprehensive
- Use clear, accessible language

IMPORTANT: Start your response with a compelling title in square brackets [Title Here], followed by the expanded description. The title should be 3-8 words, catchy and attention-grabbing.

Example format:
[New Jazz Standards Course Available] We're excited to announce our new Jazz Standards course that will help you master classic jazz tunes. This comprehensive course includes video lessons, sheet music, and backing tracks to practice with...";
        
        $system_message = get_option('alm_notification_ai_prompt', $default_prompt);
        
        // Ensure we have a valid prompt
        if (empty(trim($system_message))) {
            $system_message = $default_prompt;
        }

        // Call Katahdin AI Hub
        $expanded_response = $this->call_katahdin_ai($text, $system_message, 'gpt-4', 1000, 0.7);
        
        if (is_wp_error($expanded_response)) {
            return new WP_Error('ai_error', $expanded_response->get_error_message(), array('status' => 500));
        }
        
        // Extract title from brackets at the start of the response
        $title = '';
        $description = trim($expanded_response);
        
        // Look for title in brackets at the beginning: [Title Here] description...
        if (preg_match('/^\[([^\]]+)\]\s*(.+)$/s', $description, $matches)) {
            $title = trim($matches[1]);
            $description = trim($matches[2]);
        }
        
        // Return response with title in brackets at the start for easy copy-paste
        return new WP_REST_Response(array(
            'expanded_text' => $description ? $description : $expanded_response,
            'expanded_title' => $title,
            'original_text' => $text
        ), 200);
    }
    
    /**
     * Call Katahdin AI Hub for notification expansion
     */
    private function call_katahdin_ai($prompt, $system_message, $model = 'gpt-4', $max_tokens = 1000, $temperature = 0.7) {
        // Check if Katahdin AI Hub is available
        if (!class_exists('Katahdin_AI_Hub_REST_API')) {
            return new WP_Error('ai_hub_unavailable', 'Katahdin AI Hub is not available', array('status' => 503));
        }
        
        // Ensure plugin is registered with Katahdin AI Hub
        if (class_exists('Katahdin_AI_Hub') && function_exists('katahdin_ai_hub')) {
            $hub = katahdin_ai_hub();
            if ($hub && method_exists($hub, 'register_plugin')) {
                $quota_limit = get_option('alm_ai_quota_limit', 10000);
                $hub->register_plugin('academy-lesson-manager', array(
                    'name' => 'Academy Lesson Manager',
                    'version' => '1.0',
                    'features' => array('chat', 'completions'),
                    'quota_limit' => $quota_limit
                ));
            }
        }
        
        // Prepare the request
        $request_data = array(
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $system_message
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'model' => $model,
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
            'plugin_id' => 'academy-lesson-manager'
        );
        
        // Make the request to Katahdin AI Hub using internal WordPress REST API
        $rest_server = rest_get_server();
        $request = new WP_REST_Request('POST', '/katahdin-ai-hub/v1/chat/completions');
        $request->set_header('Content-Type', 'application/json');
        $request->set_header('X-Plugin-ID', 'academy-lesson-manager');
        $request->set_body(json_encode($request_data));
        
        // Add the request data as parameters
        foreach ($request_data as $key => $value) {
            $request->set_param($key, $value);
        }
        
        $response = $rest_server->dispatch($request);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_data = $response->get_data();
        
        if (isset($response_data['choices'][0]['message']['content'])) {
            return trim($response_data['choices'][0]['message']['content']);
        }
        
        return new WP_Error('ai_response_error', 'Unexpected response format from AI service', array('status' => 500));
    }
    
    /**
     * Handle AI path recommendations
     */
    public function handle_ai_recommendations(WP_REST_Request $request) {
        $query = trim((string) $request->get_param('q'));
        $search_results = $request->get_param('results') ?: array();
        
        if (empty($query)) {
            return new WP_Error('missing_query', 'Search query is required', array('status' => 400));
        }
        
        // Get user's membership level
        $user_id = get_current_user_id();
        $user_membership_level = apply_filters('alm_get_current_user_membership_level', 3);
        
        // Get existing AI recommender instance or create new one
        if (isset($GLOBALS['alm_ai_recommender']) && $GLOBALS['alm_ai_recommender'] instanceof ALM_AI_Recommender) {
            $recommender = $GLOBALS['alm_ai_recommender'];
        } else {
            require_once ALM_PLUGIN_DIR . 'includes/class-ai-recommender.php';
            $recommender = new ALM_AI_Recommender();
        }
        $recommendations = $recommender->generate_path_recommendations($query, $search_results, $user_membership_level);
        
        if (is_wp_error($recommendations)) {
            return $recommendations;
        }
        
        // Get lesson permalinks for recommended lessons
        if (!empty($recommendations['recommended_path'])) {
            foreach ($recommendations['recommended_path'] as &$item) {
                $item['permalink'] = $this->get_lesson_permalink($item['lesson_id']);
            }
        }
        
        if (!empty($recommendations['alternative_lessons'])) {
            foreach ($recommendations['alternative_lessons'] as &$item) {
                $item['permalink'] = $this->get_lesson_permalink($item['lesson_id']);
            }
        }
        
        // Save path to database
        $user_id = get_current_user_id();
        $ai_paths_table = $this->database->get_table_name('ai_paths');
        
        $path_data = array(
            'user_id' => $user_id,
            'search_query' => $query,
            'summary' => isset($recommendations['summary']) ? $recommendations['summary'] : '',
            'recommended_path' => isset($recommendations['recommended_path']) ? json_encode($recommendations['recommended_path']) : '',
            'alternative_lessons' => isset($recommendations['alternative_lessons']) ? json_encode($recommendations['alternative_lessons']) : ''
        );
        
        $path_id = $this->wpdb->insert($ai_paths_table, $path_data);
        
        if ($path_id) {
            $recommendations['path_id'] = $this->wpdb->insert_id;
        }
        
        // Add debug info including the prompt and SQL queries (for debugging)
        $catalog = $recommender->get_last_catalog();
        $catalog_count = count($catalog);
        $lessons_count = $recommender->get_last_catalog_lessons_count();
        
        $recommendations['debug'] = array(
            'prompt_sent' => $recommender->get_last_prompt(),
            'system_message' => $recommender->get_system_message(),
            'catalog_count' => $catalog_count,
            'catalog_lessons_count' => $lessons_count,
            'sql_query' => $recommender->get_last_sql_query(),
            'sql_params' => $recommender->get_last_sql_params()
        );
        
        return rest_ensure_response($recommendations);
    }
    
    /**
     * Get lesson permalink by lesson ID
     */
    private function get_lesson_permalink($lesson_id) {
        $lessons_table = $this->database->get_table_name('lessons');
        $post_id = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT post_id FROM {$lessons_table} WHERE ID = %d",
            $lesson_id
        ));
        
        if ($post_id) {
            return get_permalink($post_id);
        }
        
        return '';
    }
    
    /**
     * Get AI paths for current user
     */
    public function handle_get_ai_paths(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('unauthorized', 'User must be logged in', array('status' => 401));
        }
        
        $ai_paths_table = $this->database->get_table_name('ai_paths');
        
        $paths = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT ID, search_query, path_name, summary, created_at 
            FROM {$ai_paths_table} 
            WHERE user_id = %d 
            ORDER BY created_at DESC 
            LIMIT 50",
            $user_id
        ), ARRAY_A);
        
        return rest_ensure_response($paths);
    }
    
    /**
     * Get single AI path by ID
     */
    public function handle_get_ai_path(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('unauthorized', 'User must be logged in', array('status' => 401));
        }
        
        $path_id = (int) $request->get_param('id');
        $ai_paths_table = $this->database->get_table_name('ai_paths');
        
        $path = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$ai_paths_table} 
            WHERE ID = %d AND user_id = %d",
            $path_id,
            $user_id
        ), ARRAY_A);
        
        if (!$path) {
            return new WP_Error('not_found', 'Path not found', array('status' => 404));
        }
        
        // Decode JSON fields
        if (!empty($path['recommended_path'])) {
            $path['recommended_path'] = json_decode($path['recommended_path'], true);
        }
        if (!empty($path['alternative_lessons'])) {
            $path['alternative_lessons'] = json_decode($path['alternative_lessons'], true);
        }
        
        return rest_ensure_response($path);
    }
    
    /**
     * Save lesson level preference for current user
     */
    public function handle_save_lesson_level_preference(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('unauthorized', 'User must be logged in', array('status' => 401));
        }
        
        $lesson_level = (string) $request->get_param('lesson_level');
        if (!in_array($lesson_level, array('beginner', 'intermediate', 'advanced', 'pro'), true)) {
            return new WP_Error('invalid_level', 'Invalid lesson level', array('status' => 400));
        }
        
        update_user_meta($user_id, 'alm_lesson_level_preference', $lesson_level);
        
        return rest_ensure_response(array(
            'success' => true,
            'lesson_level' => $lesson_level
        ));
    }
    
    /**
     * Get lesson level preference for current user
     */
    public function handle_get_lesson_level_preference(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return rest_ensure_response(array('lesson_level' => 'intermediate'));
        }
        
        $lesson_level = get_user_meta($user_id, 'alm_lesson_level_preference', true);
        if (empty($lesson_level) || !in_array($lesson_level, array('beginner', 'intermediate', 'advanced', 'pro'), true)) {
            $lesson_level = 'intermediate'; // Default
        }
        
        return rest_ensure_response(array('lesson_level' => $lesson_level));
    }
    
    /**
     * Get all tags for frontend dropdown
     */
    public function handle_get_tags(WP_REST_Request $request) {
        $tags_table = $this->database->get_table_name('tags');
        
        $tags = $this->wpdb->get_results("
            SELECT tag_name, tag_slug
            FROM {$tags_table}
            ORDER BY tag_name ASC
        ");
        
        $tags_array = array();
        foreach ($tags as $tag) {
            $tags_array[] = array(
                'name' => $tag->tag_name,
                'slug' => $tag->tag_slug
            );
        }
        
        return rest_ensure_response($tags_array);
    }
    
    /**
     * Get all teachers for frontend dropdown
     */
    public function handle_get_teachers(WP_REST_Request $request) {
        $teachers_table = $this->database->get_table_name('teachers');
        $lessons_table = $this->database->get_table_name('lessons');
        
        $teachers = array();
        
        // Get teachers from database table
        $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$teachers_table}'") === $teachers_table;
        if ($table_exists) {
            $teachers_from_db = $this->wpdb->get_results(
                "SELECT DISTINCT teacher_name FROM {$teachers_table} ORDER BY teacher_name ASC"
            );
            foreach ($teachers_from_db as $teacher) {
                $teachers[] = $teacher->teacher_name;
            }
        }
        
        // Also get teachers from postmeta (ACF fields) for backward compatibility
        if (function_exists('get_field')) {
            $lessons_with_posts = $this->wpdb->get_results(
                "SELECT DISTINCT post_id FROM {$lessons_table} WHERE post_id > 0"
            );
            
            foreach ($lessons_with_posts as $lesson) {
                $teacher = get_field('lesson_teacher', $lesson->post_id);
                if (!empty($teacher)) {
                    $teacher = sanitize_text_field($teacher);
                    if (!in_array($teacher, $teachers)) {
                        $teachers[] = $teacher;
                    }
                }
            }
        }
        
        // Also get from postmeta directly (more efficient)
        $teachers_from_meta = $this->wpdb->get_results(
            "SELECT DISTINCT meta_value as teacher_name 
             FROM {$this->wpdb->postmeta} 
             WHERE meta_key = 'lesson_teacher' 
             AND meta_value IS NOT NULL 
             AND meta_value != ''
             ORDER BY meta_value ASC"
        );
        
        foreach ($teachers_from_meta as $teacher) {
            $teacher_name = sanitize_text_field($teacher->teacher_name);
            if (!in_array($teacher_name, $teachers)) {
                $teachers[] = $teacher_name;
            }
        }
        
        sort($teachers);
        
        $teachers_array = array();
        foreach ($teachers as $teacher) {
            $teachers_array[] = array(
                'name' => $teacher
            );
        }
        
        return rest_ensure_response($teachers_array);
    }
    
    /**
     * Get all keys for frontend dropdown
     */
    public function handle_get_keys(WP_REST_Request $request) {
        $lessons_table = $this->database->get_table_name('lessons');
        
        $keys = array();
        
        // Get keys from postmeta (ACF fields)
        if (function_exists('get_field')) {
            $lessons_with_posts = $this->wpdb->get_results(
                "SELECT DISTINCT post_id FROM {$lessons_table} WHERE post_id > 0"
            );
            
            foreach ($lessons_with_posts as $lesson) {
                $key = get_field('lesson_key', $lesson->post_id);
                if (!empty($key)) {
                    $key = sanitize_text_field($key);
                    if (!in_array($key, $keys)) {
                        $keys[] = $key;
                    }
                }
            }
        }
        
        // Also get from postmeta directly (more efficient)
        $keys_from_meta = $this->wpdb->get_results(
            "SELECT DISTINCT meta_value as key_name 
             FROM {$this->wpdb->postmeta} 
             WHERE meta_key = 'lesson_key' 
             AND meta_value IS NOT NULL 
             AND meta_value != ''
             ORDER BY meta_value ASC"
        );
        
        foreach ($keys_from_meta as $key) {
            $key_name = sanitize_text_field($key->key_name);
            if (!in_array($key_name, $keys)) {
                $keys[] = $key_name;
            }
        }
        
        sort($keys);
        
        $keys_array = array();
        foreach ($keys as $key) {
            $keys_array[] = array(
                'name' => $key
            );
        }
        
        return rest_ensure_response($keys_array);
    }
    
    /**
     * Check if user is Essentials member
     * 
     * @param int $user_id User ID
     * @return bool True if Essentials member
     */
    private function is_essentials_member($user_id) {
        // Check if user has Essentials membership via Keap/Infusionsoft
        // Essentials members should have membership level 1
        // They should NOT have Studio (2) or Premier (3) access
        
        // First check if they have Studio or Premier (if so, they're not Essentials)
        $studio_access = false;
        $premier_access = false;
        
        if (function_exists('memb_hasAnyTags')) {
            $studio_access = memb_hasAnyTags([9954,10136,9807,9827,9819,9956,10136]);
            $premier_access = memb_hasAnyTags([9821,9813,10142]);
        }
        
        // If they have Studio or Premier, they're not Essentials
        if ($studio_access || $premier_access) {
            return false;
        }
        
        // Check for Essentials membership SKU
        $essentials_skus = array('JA_YEAR_ESSENTIALS', 'ACADEMY_ESSENTIALS');
        foreach ($essentials_skus as $sku) {
            if (function_exists('memb_hasMembership') && memb_hasMembership($sku) === true) {
                return true;
            }
        }
        
        // Fallback: Check if they have active membership but not Studio/Premier
        // This assumes Essentials is the base paid membership
        if (function_exists('je_return_active_member') && je_return_active_member() == 'true') {
            // If they're an active member but don't have Studio/Premier, assume Essentials
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle library select endpoint
     */
    public function handle_library_select(WP_REST_Request $request) {
        if (!class_exists('ALM_Essentials_Library')) {
            return new WP_Error('library_unavailable', 'Library system not available', array('status' => 500));
        }
        
        $user_id = get_current_user_id();
        $lesson_id = $request->get_param('lesson_id');
        
        // Check if user is Essentials member
        if (!$this->is_essentials_member($user_id)) {
            return new WP_Error('unauthorized', 'This feature is available for Essentials members only', array('status' => 403));
        }
        
        $library = new ALM_Essentials_Library();
        $result = $library->add_lesson_to_library($user_id, $lesson_id);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $available = $library->get_available_selections($user_id);
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Lesson added to your library!',
            'available_count' => $available
        ));
    }
    
    /**
     * Handle library available endpoint
     */
    public function handle_library_available(WP_REST_Request $request) {
        if (!class_exists('ALM_Essentials_Library')) {
            return new WP_Error('library_unavailable', 'Library system not available', array('status' => 500));
        }
        
        $user_id = get_current_user_id();
        $library = new ALM_Essentials_Library();
        $available = $library->get_available_selections($user_id);
        $next_grant = $library->get_next_grant_date($user_id);
        
        return rest_ensure_response(array(
            'available_count' => $available,
            'next_grant_date' => $next_grant
        ));
    }
    
    /**
     * Handle library lessons endpoint
     */
    public function handle_library_lessons(WP_REST_Request $request) {
        if (!class_exists('ALM_Essentials_Library')) {
            return new WP_Error('library_unavailable', 'Library system not available', array('status' => 500));
        }
        
        $user_id = get_current_user_id();
        $library = new ALM_Essentials_Library();
        $lessons = $library->get_user_library($user_id);
        
        $lessons_array = array();
        foreach ($lessons as $lesson) {
            $lesson_url = '';
            if ($lesson->post_id) {
                $lesson_url = get_permalink($lesson->post_id);
            } elseif ($lesson->slug) {
                $lesson_url = home_url('/lesson/' . $lesson->slug . '/');
            }
            
            $lessons_array[] = array(
                'id' => $lesson->ID,
                'title' => stripslashes($lesson->lesson_title),
                'description' => stripslashes($lesson->lesson_description),
                'url' => $lesson_url,
                'selected_at' => $lesson->selected_at,
                'duration' => $lesson->duration
            );
        }
        
        return rest_ensure_response($lessons_array);
    }
    

    /**
     * Search lessons with filters and pagination
     */
    public function handle_search_lessons(WP_REST_Request $request) {
        $lessons_table = $this->database->get_table_name('lessons');

        $q = trim((string) $request->get_param('q'));
        $search_source = (string) $request->get_param('search_source');
        if (!in_array($search_source, array('dashboard', 'shortcode'), true)) {
            $search_source = 'shortcode'; // Default to shortcode if not specified
        }
        
        // Debug logging
        error_log('ALM Search: q=' . $q . ', search_source_param=' . $request->get_param('search_source') . ', final_source=' . $search_source);
        // AI: extract filters from natural language
        $ai_filters = ALM_AI::extract_filters($q);
        $page = max(1, intval($request->get_param('page')));
        $per_page = min(100, max(1, intval($request->get_param('per_page')))); // Allow up to 100 per page
        $offset = ($page - 1) * $per_page;
        
        // Check for debug parameter
        $debug_mode = isset($_GET['debug']) && $_GET['debug'] == '1';

        $membership_param_raw = isset($ai_filters['membership_level']) ? $ai_filters['membership_level'] : $request->get_param('membership_level');
        $membership_param = $membership_param_raw !== null ? intval($membership_param_raw) : null;
        // Allow theme/plugins to define current user's accessible level
        $current_user_level = apply_filters('alm_get_current_user_membership_level', 0);
        $enforce_membership = apply_filters('alm_enforce_membership_in_search', false, $request);
        // Check if membership_param was explicitly provided (including 0 for Free level)
        $membership_param_provided = $membership_param_raw !== null;
        // Only enforce if explicitly enabled or if a membership param is provided
        // BUT: Don't apply membership filter when there's a search query - show all results
        $apply_membership_filter = (!empty($q) ? false : ($enforce_membership || $membership_param_provided));
        $max_level = $apply_membership_filter
            ? ($membership_param_provided && !isset($ai_filters['membership_level']) ? $membership_param : ($membership_param !== null ? min($membership_param, intval($current_user_level)) : intval($current_user_level)))
            : 0; // 0 disables the filter

        $collection_id = isset($ai_filters['collection_id']) ? intval($ai_filters['collection_id']) : intval($request->get_param('collection_id'));
        $has_resources = isset($ai_filters['has_resources']) ? (string) $ai_filters['has_resources'] : (string) $request->get_param('has_resources');
        $song_lesson = isset($ai_filters['song_lesson']) ? (string) $ai_filters['song_lesson'] : (string) $request->get_param('song_lesson');
        $min_duration = isset($ai_filters['min_duration']) ? intval($ai_filters['min_duration']) : intval($request->get_param('min_duration'));
        $max_duration = isset($ai_filters['max_duration']) ? intval($ai_filters['max_duration']) : intval($request->get_param('max_duration'));
        $date_from = isset($ai_filters['date_from']) ? (string) $ai_filters['date_from'] : (string) $request->get_param('date_from');
        $date_to = isset($ai_filters['date_to']) ? (string) $ai_filters['date_to'] : (string) $request->get_param('date_to');
        $lesson_level = isset($ai_filters['lesson_level']) ? (string) $ai_filters['lesson_level'] : (string) $request->get_param('lesson_level');
        $tag = trim((string) $request->get_param('tag'));
        $lesson_style = trim((string) $request->get_param('lesson_style'));
        $teacher = trim((string) $request->get_param('teacher'));
        $key = trim((string) $request->get_param('key'));
        $has_sample = isset($ai_filters['has_sample']) ? (string) $ai_filters['has_sample'] : (string) $request->get_param('has_sample');
        $order_by = (string) $request->get_param('order_by');

        $where = array('1=1');
        $where_params = array(); // Parameters for WHERE clause only
        $select_params = array(); // Parameters for SELECT clause (relevance scoring)
        $select_relevance = '';
        $order_clause = '';
        $join_teacher = '';
        $join_key = '';

        // For now, skip FULLTEXT and always use LIKE for search queries (more reliable)
        // FULLTEXT requires indexes and may not work in all environments
        $use_fulltext = false; // Disabled - always use LIKE
        
        // If there's a search query, use LIKE search
        if (!empty($q)) {
            // Extract meaningful keywords (remove common stop words and short words)
            $stop_words = array('i', 'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'has', 'he', 'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the', 'to', 'was', 'will', 'with', 'have', 'my', 'me', 'we', 'they', 'them', 'their', 'this', 'these', 'what', 'which', 'who');
            $tokens = preg_split('/\s+/', strtolower($q));
            $keywords = array();
            foreach ($tokens as $token) {
                $token = trim($token);
                if (empty($token)) continue;
                // Keep words that are 2+ characters and not stop words
                // Changed from 3 to 2 to catch short queries like "TCI"
                if (mb_strlen($token) >= 2 && !in_array($token, $stop_words, true)) {
                    $keywords[] = $token;
                }
            }
            
            // If no keywords after filtering, use original query (might be short like "TCI" or single character)
            if (empty($keywords)) {
                $keywords = array(strtolower(trim($q)));
            }
            
            // Build WHERE condition: ANY keyword can match (OR logic)
            // Use case-insensitive matching - MySQL LIKE is case-insensitive with utf8mb4_general_ci collation
            // But we'll use BINARY comparison with LOWER() to ensure it works regardless of collation
            $or_conditions = array();
            foreach ($keywords as $keyword) {
                $escaped_keyword = '%' . $this->wpdb->esc_like($keyword) . '%';
                // Use LOWER() on columns and convert keyword to lowercase for reliable case-insensitive matching
                $lower_keyword = strtolower($escaped_keyword);
                $or_conditions[] = "(LOWER(l.lesson_title) LIKE %s OR LOWER(l.lesson_description) LIKE %s OR LOWER(c.collection_title) LIKE %s)";
                $where_params[] = $lower_keyword;
                $where_params[] = $lower_keyword;
                $where_params[] = $lower_keyword;
            }
            
            // Use OR logic - find lessons with ANY keyword match
            $where[] = '(' . implode(' OR ', $or_conditions) . ')';
            
            // Build relevance scoring - heavily prioritize title matches
            $select_relevance = ', (';
            $relevance_parts = array();
            foreach ($keywords as $keyword) {
                $escaped_keyword = '%' . $this->wpdb->esc_like($keyword) . '%';
                $lower_keyword = strtolower($escaped_keyword);
                // Very high weight for title matches (100), description (3), collection (1)
                // Use LOWER() for case-insensitive matching
                $relevance_parts[] = "(CASE WHEN LOWER(l.lesson_title) LIKE %s THEN 100 ELSE 0 END)";
                $select_params[] = $lower_keyword;
                $relevance_parts[] = "(CASE WHEN LOWER(l.lesson_description) LIKE %s THEN 3 ELSE 0 END)";
                $select_params[] = $lower_keyword;
                $relevance_parts[] = "(CASE WHEN LOWER(c.collection_title) LIKE %s THEN 1 ELSE 0 END)";
                $select_params[] = $lower_keyword;
            }
            $select_relevance .= implode(' + ', $relevance_parts) . ') AS relevance';
            
            // Order by relevance if we have keywords
            $order_clause = "ORDER BY relevance DESC, l.post_date DESC";
        }

        if ($apply_membership_filter && $membership_param_provided) {
            // Get free trial lesson IDs
            $free_trial_lesson_ids = get_option('alm_free_trial_lesson_ids', array());
            $free_trial_lesson_ids = array_map('intval', $free_trial_lesson_ids);
            $free_trial_lesson_ids = array_filter($free_trial_lesson_ids);
            
            // If membership_param was provided directly (from frontend filter), use exact match
            // Otherwise, use <= logic for accessibility (user can see content at their level or below)
            if ($membership_param_provided && !isset($ai_filters['membership_level'])) {
                // Direct frontend filter - exact match (including 0 for Free)
                if ($membership_param == 0 && !empty($free_trial_lesson_ids)) {
                    // Free trial users can access: lessons with membership_level = 0 OR lessons in free trial whitelist
                    $placeholders = implode(',', array_fill(0, count($free_trial_lesson_ids), '%d'));
                    $where[] = "(l.membership_level = %d OR l.post_id IN ($placeholders))";
                    $where_params[] = $membership_param;
                    $where_params = array_merge($where_params, $free_trial_lesson_ids);
                } else {
                    $where[] = "l.membership_level = %d";
                    $where_params[] = $membership_param;
                }
            } else {
                // AI filter or enforcement - use <= logic for accessibility
                if ($max_level == 0 && !empty($free_trial_lesson_ids)) {
                    // Free trial users can access: lessons with membership_level = 0 OR lessons in free trial whitelist
                    $placeholders = implode(',', array_fill(0, count($free_trial_lesson_ids), '%d'));
                    $where[] = "(l.membership_level <= %d OR l.post_id IN ($placeholders))";
                    $where_params[] = $max_level;
                    $where_params = array_merge($where_params, $free_trial_lesson_ids);
                } else {
                    $where[] = "l.membership_level <= %d";
                    $where_params[] = $max_level;
                }
            }
        }
        if ($collection_id > 0) {
            $where[] = "l.collection_id = %d";
            $where_params[] = $collection_id;
        }
        if ($song_lesson === 'y' || $song_lesson === 'n') {
            $where[] = "l.song_lesson = %s";
            $where_params[] = $song_lesson;
        }
        if ($has_resources === 'has') {
            $where[] = "(l.resources IS NOT NULL AND l.resources != '' AND l.resources != 'N;')";
        } elseif ($has_resources === 'none') {
            $where[] = "(l.resources IS NULL OR l.resources = '' OR l.resources = 'N;')";
        }
        if ($min_duration > 0) {
            $where[] = "l.duration >= %d";
            $where_params[] = $min_duration;
        }
        if ($max_duration > 0) {
            $where[] = "l.duration <= %d";
            $where_params[] = $max_duration;
        }
        if (!empty($date_from)) {
            $where[] = "l.post_date >= %s";
            $where_params[] = $date_from;
        }
        if (!empty($date_to)) {
            $where[] = "l.post_date <= %s";
            $where_params[] = $date_to;
        }
        
        // Filter out future-dated lessons unless user is admin
        if (!current_user_can('manage_options')) {
            // Get current date in ET timezone for comparison
            $now_et = new DateTime('now', new DateTimeZone('America/New_York'));
            $today_et = $now_et->format('Y-m-d');
            // Note: Removed l.post_date = '' check as MySQL DATE columns can't be compared to empty strings
            $where[] = "(l.post_date IS NULL OR l.post_date = '0000-00-00' OR l.post_date <= %s)";
            $where_params[] = $today_et;
        }
        
        if (!empty($lesson_level) && in_array($lesson_level, array('beginner', 'intermediate', 'advanced', 'pro'), true)) {
            $where[] = "l.lesson_level = %s";
            $where_params[] = $lesson_level;
        }
        
        // Filter by tag - match exact tag (handles tags at start, middle, or end of comma-separated list)
        if (!empty($tag)) {
            $tag_trimmed = trim($tag);
            // Match: tag at start, tag in middle (preceded by ", "), or tag at end (followed by nothing or end of string)
            $where[] = "(l.lesson_tags = %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s)";
            $where_params[] = $tag_trimmed;
            $where_params[] = $tag_trimmed . ',%';
            $where_params[] = '%, ' . $tag_trimmed . ',%';
            $where_params[] = '%, ' . $tag_trimmed;
        }
        
        // Filter by lesson style - match exact style (handles styles at start, middle, or end of comma-separated list)
        if (!empty($lesson_style)) {
            $style_trimmed = trim($lesson_style);
            // Match: style at start, style in middle (preceded by ", "), or style at end (followed by nothing or end of string)
            $where[] = "(l.lesson_style = %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s)";
            $where_params[] = $style_trimmed;
            $where_params[] = $style_trimmed . ',%';
            $where_params[] = '%, ' . $style_trimmed . ',%';
            $where_params[] = '%, ' . $style_trimmed;
        }
        
        // Filter by teacher - join with postmeta
        if (!empty($teacher)) {
            if ($teacher === '__no_teacher__') {
                // Filter for lessons with no teacher
                $join_teacher = "LEFT JOIN {$this->wpdb->postmeta} pm_teacher ON l.post_id = pm_teacher.post_id AND pm_teacher.meta_key = 'lesson_teacher'";
                $where[] = "(l.post_id = 0 OR pm_teacher.meta_value IS NULL OR pm_teacher.meta_value = '')";
            } else {
                // Filter for specific teacher
                $join_teacher = "INNER JOIN {$this->wpdb->postmeta} pm_teacher ON l.post_id = pm_teacher.post_id AND l.post_id > 0 AND pm_teacher.meta_key = 'lesson_teacher'";
                $where[] = "pm_teacher.meta_value = %s";
                $where_params[] = $teacher;
            }
        }
        
        // Filter by key - join with postmeta
        if (!empty($key)) {
            if ($key === '__no_key__') {
                // Filter for lessons with no key
                $join_key = "LEFT JOIN {$this->wpdb->postmeta} pm_key ON l.post_id = pm_key.post_id AND pm_key.meta_key = 'lesson_key'";
                $where[] = "(l.post_id = 0 OR pm_key.meta_value IS NULL OR pm_key.meta_value = '')";
            } else {
                // Filter for specific key
                $join_key = "INNER JOIN {$this->wpdb->postmeta} pm_key ON l.post_id = pm_key.post_id AND l.post_id > 0 AND pm_key.meta_key = 'lesson_key'";
                $where[] = "pm_key.meta_value = %s";
                $where_params[] = $key;
            }
        }
        
        // Filter by has sample video
        if ($has_sample === 'y') {
            $where[] = "(l.sample_video_url IS NOT NULL AND l.sample_video_url != '')";
        } elseif ($has_sample === 'n') {
            $where[] = "(l.sample_video_url IS NULL OR l.sample_video_url = '')";
        }

        // Set order clause if not already set by keyword search above
        if (empty($order_clause)) {
            if ($order_by === 'date') {
                $order_clause = "ORDER BY l.post_date DESC";
            } else {
                // Default order if no keywords and no specific order_by
                $order_clause = "ORDER BY l.post_date DESC";
            }
        }

        $where_sql = 'WHERE ' . implode(' AND ', $where);

        // Count - always include JOIN for collection_title searches, plus teacher/key joins if needed
        $collections_table = $this->database->get_table_name('collections');
        $count_from = "FROM {$lessons_table} l LEFT JOIN {$collections_table} c ON c.ID = l.collection_id {$join_teacher} {$join_key}";
        $count_sql = $this->wpdb->prepare("SELECT COUNT(*) {$count_from} {$where_sql}", $where_params);
        $total = intval($this->wpdb->get_var($count_sql));

        // Query with collection title join (always include JOIN since we need collection_title)
        // Calculate duration from chapters instead of using stored value
        $chapters_table = $this->database->get_table_name('chapters');
        $sql = "SELECT l.ID, l.post_id, l.collection_id, l.lesson_title, l.lesson_description, l.post_date, COALESCE((SELECT SUM(duration) FROM {$chapters_table} WHERE lesson_id = l.ID), 0) AS duration, l.song_lesson, l.membership_level, l.lesson_level, l.lesson_tags, l.lesson_style, l.slug, l.vtt, l.resources, l.sample_video_url, c.collection_title{$select_relevance}
                FROM {$lessons_table} l
                LEFT JOIN {$collections_table} c ON c.ID = l.collection_id
                {$join_teacher}
                {$join_key}
                {$where_sql}
                {$order_clause}
                LIMIT %d OFFSET %d";

        // Combine params: select_params (relevance) first, then where_params, then limit/offset
        $query_params = array_merge($select_params, $where_params);
        $query_params[] = $per_page;
        $query_params[] = $offset;

        $prepared = $this->wpdb->prepare($sql, $query_params);
        
        // Execute main query - get results as objects
        $rows = $this->wpdb->get_results($prepared, OBJECT);
        
        // Log actual executed query and any errors for debugging
        if (!empty($q) && (empty($rows) || $total === 0)) {
            $actual_query = $this->wpdb->last_query;
            $db_error = $this->wpdb->last_error;
            if ($db_error) {
                error_log('ALM Search: Database error - ' . $db_error);
                error_log('ALM Search: Last query - ' . $actual_query);
            }
        }

        // Fuzzy fallback when no results
        if (!empty($q) && (empty($rows) || $total === 0)) {
            $tokens = preg_split('/\s+/', $q);
            $like_parts = array();
            $score_parts = array();
            $f_params = array();

            foreach ($tokens as $tok) {
                $tok = trim($tok);
                if ($tok === '') continue;
                // Build LIKEs for tokens of 2+ characters (changed from 3 to catch "TCI")
                if (mb_strlen($tok) >= 2) {
                    $like = '%' . $this->wpdb->esc_like($tok) . '%';
                    $like_parts[] = "l.lesson_title LIKE %s"; $f_params[] = $like;
                    $like_parts[] = "l.lesson_description LIKE %s"; $f_params[] = $like;
                    $like_parts[] = "c.collection_title LIKE %s"; $f_params[] = $like; // Fixed: use collection_title, not chapter_title
                    $like_parts[] = "c2.chapter_title LIKE %s"; $f_params[] = $like; // Use c2 for chapters table
                    $like_parts[] = "t.content LIKE %s"; $f_params[] = $like;
                    $score_parts[] = "(l.lesson_title LIKE %s)"; $f_params[] = $like;
                    $score_parts[] = "(c.collection_title LIKE %s)"; $f_params[] = $like; // Fixed: use collection_title
                    $score_parts[] = "(c2.chapter_title LIKE %s)"; $f_params[] = $like; // Use c2 for chapters table
                }
                // SOUNDEX for longer tokens
                if (mb_strlen($tok) >= 4) {
                    $like_parts[] = "SOUNDEX(l.lesson_title) = SOUNDEX(%s)"; $f_params[] = $tok;
                }
            }

            // If no token conditions built, use SOUNDEX of whole query
            if (empty($like_parts)) {
                $like_parts[] = "SOUNDEX(l.lesson_title) = SOUNDEX(%s)"; $f_params[] = $q;
            }

            $f_where = array('1=1');
            $f_params_full = array();
            // Apply the same membership/filters if enabled
            if ($apply_membership_filter) {
                // Get free trial lesson IDs
                $free_trial_lesson_ids = get_option('alm_free_trial_lesson_ids', array());
                $free_trial_lesson_ids = array_map('intval', $free_trial_lesson_ids);
                $free_trial_lesson_ids = array_filter($free_trial_lesson_ids);
                
                if ($max_level == 0 && !empty($free_trial_lesson_ids)) {
                    // Free trial users can access: lessons with membership_level = 0 OR lessons in free trial whitelist
                    $placeholders = implode(',', array_fill(0, count($free_trial_lesson_ids), '%d'));
                    $f_where[] = "(l.membership_level <= %d OR l.post_id IN ($placeholders))";
                    $f_params_full[] = $max_level;
                    $f_params_full = array_merge($f_params_full, $free_trial_lesson_ids);
                } elseif ($max_level > 0) {
                    $f_where[] = "l.membership_level <= %d";
                    $f_params_full[] = $max_level;
                }
            }
            if ($collection_id > 0) { $f_where[] = "l.collection_id = %d"; $f_params_full[] = $collection_id; }
            if ($song_lesson === 'y' || $song_lesson === 'n') { $f_where[] = "l.song_lesson = %s"; $f_params_full[] = $song_lesson; }
            if (!empty($lesson_level) && in_array($lesson_level, array('beginner', 'intermediate', 'advanced', 'pro'), true)) {
                $f_where[] = "l.lesson_level = %s";
                $f_params_full[] = $lesson_level;
            }
            if ($has_resources === 'has') { $f_where[] = "(l.resources IS NOT NULL AND l.resources != '' AND l.resources != 'N;')"; }
            elseif ($has_resources === 'none') { $f_where[] = "(l.resources IS NULL OR l.resources = '' OR l.resources = 'N;')"; }
            if ($min_duration > 0) { $f_where[] = "l.duration >= %d"; $f_params_full[] = $min_duration; }
            if ($max_duration > 0) { $f_where[] = "l.duration <= %d"; $f_params_full[] = $max_duration; }
            if (!empty($date_from)) { $f_where[] = "l.post_date >= %s"; $f_params_full[] = $date_from; }
            if (!empty($date_to)) { $f_where[] = "l.post_date <= %s"; $f_params_full[] = $date_to; }
            
            // Filter out future-dated lessons unless user is admin (for fuzzy fallback)
            if (!current_user_can('manage_options')) {
                $now_et = new DateTime('now', new DateTimeZone('America/New_York'));
                $today_et = $now_et->format('Y-m-d');
                // Note: Removed l.post_date = '' check as MySQL DATE columns can't be compared to empty strings
                $f_where[] = "(l.post_date IS NULL OR l.post_date = '0000-00-00' OR l.post_date <= %s)";
                $f_params_full[] = $today_et;
            }
            
            if (!empty($tag)) {
                $tag_trimmed = trim($tag);
                $f_where[] = "(l.lesson_tags = %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s)";
                $f_params_full[] = $tag_trimmed;
                $f_params_full[] = $tag_trimmed . ',%';
                $f_params_full[] = '%, ' . $tag_trimmed . ',%';
                $f_params_full[] = '%, ' . $tag_trimmed;
            }
            if (!empty($lesson_style)) {
                $style_trimmed = trim($lesson_style);
                $f_where[] = "(l.lesson_style = %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s)";
                $f_params_full[] = $style_trimmed;
                $f_params_full[] = $style_trimmed . ',%';
                $f_params_full[] = '%, ' . $style_trimmed . ',%';
                $f_params_full[] = '%, ' . $style_trimmed;
            }
            if (!empty($teacher)) {
                if ($teacher === '__no_teacher__') {
                    $f_where[] = "(l.post_id = 0 OR NOT EXISTS (SELECT 1 FROM {$this->wpdb->postmeta} WHERE post_id = l.post_id AND meta_key = 'lesson_teacher' AND meta_value IS NOT NULL AND meta_value != ''))";
                } else {
                    $f_where[] = "EXISTS (SELECT 1 FROM {$this->wpdb->postmeta} WHERE post_id = l.post_id AND meta_key = 'lesson_teacher' AND meta_value = %s)";
                    $f_params_full[] = $teacher;
                }
            }
            if (!empty($key)) {
                if ($key === '__no_key__') {
                    $f_where[] = "(l.post_id = 0 OR NOT EXISTS (SELECT 1 FROM {$this->wpdb->postmeta} WHERE post_id = l.post_id AND meta_key = 'lesson_key' AND meta_value IS NOT NULL AND meta_value != ''))";
                } else {
                    $f_where[] = "EXISTS (SELECT 1 FROM {$this->wpdb->postmeta} WHERE post_id = l.post_id AND meta_key = 'lesson_key' AND meta_value = %s)";
                    $f_params_full[] = $key;
                }
            }
            if ($has_sample === 'y') {
                $f_where[] = "(l.sample_video_url IS NOT NULL AND l.sample_video_url != '')";
            } elseif ($has_sample === 'n') {
                $f_where[] = "(l.sample_video_url IS NULL OR l.sample_video_url = '')";
            }

            $like_sql = '(' . implode(' OR ', $like_parts) . ')';
            $f_where[] = $like_sql;
            
            // Build WHERE clause with placeholders - we need to track which placeholders are for filters vs LIKE
            $where_sql2 = 'WHERE ' . implode(' AND ', $f_where);

            $score_sql = (empty($score_parts) ? '0' : implode(' + ', $score_parts));

            // IMPORTANT: The SQL structure has score_sql first (uses $f_params), then WHERE clause (uses $f_params_full then $f_params)
            // But wpdb->prepare() processes placeholders left-to-right, so we need to bind in the correct order:
            // 1. First, $f_params for the score calculation (in score_sql)
            // 2. Then, $f_params_full for the WHERE filters
            // 3. Then, $f_params again for the WHERE LIKE conditions
            // 4. Finally, $per_page and $offset
            
            // Calculate duration from chapters instead of using stored value
            $chapters_table = $this->database->get_table_name('chapters');
            $sql2 = "SELECT l.ID, l.post_id, l.collection_id, l.lesson_title, l.lesson_description, l.post_date, COALESCE((SELECT SUM(duration) FROM {$chapters_table} WHERE lesson_id = l.ID), 0) AS duration, l.song_lesson, l.membership_level, l.lesson_level, l.lesson_tags, l.lesson_style, l.slug, l.vtt, l.resources, l.sample_video_url,
                            c.collection_title, (" . $score_sql . ") AS score
                     FROM {$lessons_table} l
                     LEFT JOIN {$chapters_table} c2 ON c2.lesson_id = l.ID
                     LEFT JOIN " . $this->database->get_table_name('transcripts') . " t ON t.lesson_id = l.ID
                     LEFT JOIN {$collections_table} c ON c.ID = l.collection_id
                     {$where_sql2}
                     GROUP BY l.ID
                     ORDER BY score DESC, l.post_date DESC
                     LIMIT %d OFFSET %d";

            // CRITICAL: $f_params is built with LIKE params first (5 per token), then score params (3 per token)
            // SQL needs: score params first (last 3 of $f_params), then $f_params_full, then LIKE params (first 5 of $f_params)
            // For one token "tci": $f_params has 8 values [LIKE_title, LIKE_desc, LIKE_coll, LIKE_chap, LIKE_trans, score_title, score_coll, score_chap]
            // score_sql needs: [score_title, score_coll, score_chap] = last 3
            // WHERE LIKE needs: [LIKE_title, LIKE_desc, LIKE_coll, LIKE_chap, LIKE_trans] = first 5
            
            $like_param_count = count($like_parts); // 5 per token
            $score_param_count = count($score_parts); // 3 per token
            $score_params = array_slice($f_params, $like_param_count); // Last 3 params (score)
            $where_like_params = array_slice($f_params, 0, $like_param_count); // First 5 params (LIKE)
            
            // Bind in order: score params, then filters, then WHERE LIKE params, then LIMIT
            $f_bind = array_merge($score_params, $f_params_full, $where_like_params);
            $f_bind[] = $per_page; $f_bind[] = $offset;
            $prepared2 = $this->wpdb->prepare($sql2, $f_bind);
            
            // Execute the query
            $rows = $this->wpdb->get_results($prepared2, OBJECT);
            
            // Recalculate total for fuzzy fallback results
            // We need to count the results from the fuzzy query to get accurate pagination
            if (!empty($rows) && is_array($rows)) {
                // Count total matching rows for the fuzzy query (without LIMIT)
                // Must match the same JOINs as the main query to get accurate count
                $transcripts_table = $this->database->get_table_name('transcripts');
                $count_from2 = "FROM {$lessons_table} l
                     LEFT JOIN {$chapters_table} c2 ON c2.lesson_id = l.ID
                     LEFT JOIN {$transcripts_table} t ON t.lesson_id = l.ID
                     LEFT JOIN {$collections_table} c ON c.ID = l.collection_id";
                $count_sql2 = "SELECT COUNT(DISTINCT l.ID) {$count_from2} {$where_sql2}";
                $count_params2 = array_merge($f_params_full, $where_like_params);
                $total = intval($this->wpdb->get_var($this->wpdb->prepare($count_sql2, $count_params2)));
            } else {
                // If still no results, keep total at 0
                $total = 0;
            }
        }

        // Get membership level names (cache to avoid repeated lookups)
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-settings.php';
        $membership_level_cache = array();
        
        // Shape response
        $items = array();
        
        foreach ($rows as $r) {
            $membership_level = intval($r->membership_level);
            // Cache membership level names to avoid repeated lookups
            if (!isset($membership_level_cache[$membership_level])) {
                $membership_level_cache[$membership_level] = ALM_Admin_Settings::get_membership_level_name($membership_level);
            }
            $membership_level_name = $membership_level_cache[$membership_level];
            
            $items[] = array(
                'id' => intval($r->ID),
                'post_id' => intval($r->post_id),
                'collection_id' => intval($r->collection_id),
                'collection_title' => !empty($r->collection_title) ? stripslashes($r->collection_title) : '',
                'title' => stripslashes($r->lesson_title),
                'description' => stripslashes($r->lesson_description),
                'date' => $r->post_date,
                'duration' => intval($r->duration),
                'song' => $r->song_lesson === 'y',
                'membership_level' => $membership_level,
                'membership_level_name' => $membership_level_name,
                'lesson_level' => !empty($r->lesson_level) ? $r->lesson_level : null,
                'lesson_tags' => !empty($r->lesson_tags) ? stripslashes($r->lesson_tags) : '',
                'lesson_style' => !empty($r->lesson_style) ? stripslashes($r->lesson_style) : '',
                'slug' => $r->slug,
                'permalink' => ($r->post_id ? get_permalink($r->post_id) : ''),
                'sample_video_url' => !empty($r->sample_video_url) ? $r->sample_video_url : '',
                'relevance' => isset($r->relevance) ? floatval($r->relevance) : null,
                'has_resources' => (!empty($r->resources) && $r->resources !== 'N;')
            );
        }

        // AI re-ranking hook (no-op by default)
        $items = ALM_AI::rerank_items($items, $q);
        
        $response = array(
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $per_page > 0 ? (int) ceil($total / $per_page) : 1
        );
        
        // Always include SQL query in response for debugging (especially when no results)
        // Build the actual SQL with values substituted for debugging
        $debug_sql = isset($prepared) ? $prepared : '';
        $debug_count_sql = isset($count_sql) ? $count_sql : '';
        $debug_fuzzy_sql = '';
        $debug_fuzzy_count_sql = '';
        
        // If fuzzy fallback was used, also show that SQL
        if (!empty($q) && (empty($rows) || $total === 0) && isset($prepared2)) {
            $debug_fuzzy_sql = $prepared2;
            if (isset($count_sql2) && isset($count_params2)) {
                $debug_fuzzy_count_sql = $this->wpdb->prepare($count_sql2, $count_params2);
            }
        }
        
        // Log search query (log all searches, even from guests)
        // Only log queries with 2+ characters to avoid logging every keystroke
        // Don't log HEAD requests (those are just for debounced logging)
        $is_head_request = $request->get_method() === 'HEAD';
        $should_log = !empty($q) && strlen($q) >= 2 && !$is_head_request;
        
        if ($should_log) {
            // Get user ID - WPEngine strips cookies, so we rely on REST API nonce authentication
            // WordPress REST API should have authenticated the user if nonce is valid
            $user_id = 0;
            
            // Try to get current user (WordPress REST API should set this if nonce is valid)
            $current_user = wp_get_current_user();
            if ($current_user && $current_user->ID > 0) {
                $user_id = $current_user->ID;
            }
            
            // Fallback: Try standard WordPress function
            if (!$user_id && is_user_logged_in()) {
                $user_id = get_current_user_id();
            }
            
            // Note: If cookies are stripped by WPEngine, we may not be able to identify the user
            // In that case, user_id will be 0 (guest), which is acceptable
            
            // Note: user_id can be 0 for guest users - we'll log it anyway
            
            $search_logs_table = $this->database->get_table_name('search_logs');
            
            // Ensure table exists before trying to insert
            $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$search_logs_table}'") == $search_logs_table;
            if (!$table_exists) {
                // Try to create the table
                $database = new ALM_Database();
                $database->create_tables();
                $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$search_logs_table}'") == $search_logs_table;
            }
            
            if ($table_exists) {
                $result = $this->wpdb->insert(
                    $search_logs_table,
                    array(
                        'user_id' => $user_id, // Can be 0 for guests
                        'search_query' => $q,
                        'search_source' => $search_source,
                        'results_count' => $total,
                        'created_at' => current_time('mysql')
                    ),
                    array('%d', '%s', '%s', '%d', '%s')
                );
                
                // Log error if insert failed (for debugging)
                if ($result === false && !empty($this->wpdb->last_error)) {
                    error_log('ALM Search Log: Failed to insert search log - ' . $this->wpdb->last_error);
                }
            }
        }
        
        // Always include debug info (not just when debug_mode is enabled)
        $response['_debug'] = array(
            'sql' => $debug_sql,
            'count_sql' => $debug_count_sql,
            'fuzzy_sql' => $debug_fuzzy_sql,
            'fuzzy_count_sql' => $debug_fuzzy_count_sql,
            'actual_executed_query' => !empty($q) && (empty($rows) || $total === 0) ? $this->wpdb->last_query : '',
            'db_error' => $this->wpdb->last_error ?: '',
            'filters' => array(
                'query' => $q,
                'lesson_style' => $lesson_style,
                'lesson_level' => $lesson_level,
                'tag' => $tag,
                'collection_id' => $collection_id,
                'membership_level' => $membership_param,
                'page' => $page,
                'per_page' => $per_page
            ),
            'total_results' => $total,
            'returned_results' => count($items),
            'used_fuzzy_fallback' => !empty($debug_fuzzy_sql),
            'where_params_count' => count($where_params),
            'select_params_count' => count($select_params)
        );

        return new WP_REST_Response($response, 200);
    }
    
    /**
     * Handle Zoom webhook from Zapier
     * 
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function handle_zoom_webhook(WP_REST_Request $request) {
        // Load webhook processor
        require_once ALM_PLUGIN_DIR . 'includes/class-zoom-webhook.php';
        $webhook = new ALM_Zoom_Webhook();
        
        // Get payload - Zapier sends form data
        $payload = array();
        
        // WordPress REST API parses form data into get_body_params()
        $body_params = $request->get_body_params();
        if (!empty($body_params)) {
            $payload = $body_params;
        } else {
            // Fallback to JSON body
            $json_params = $request->get_json_params();
            if (!empty($json_params)) {
                $payload = $json_params;
            } else {
                // Last resort: try $_POST directly (for form-encoded data)
                $payload = $_POST;
            }
        }
        
        // If still empty, try parsing raw body as form data
        if (empty($payload)) {
            $raw_body = $request->get_body();
            if (!empty($raw_body)) {
                parse_str($raw_body, $payload);
            }
        }
        
        // Log all incoming requests, even if payload is empty
        // This ensures we capture all webhook attempts for debugging
        if (empty($payload)) {
            // Log empty/malformed request
            $debug = array(
                'timestamp' => current_time('mysql'),
                'payload' => array(),
                'error' => 'Empty or unparseable payload received',
                'request_method' => $request->get_method(),
                'request_headers' => $request->get_headers(),
                'raw_body' => $request->get_body(),
                'steps' => array(
                    array('step' => 'payload_extraction', 'status' => 'failed', 'message' => 'Could not extract payload from request')
                )
            );
            $webhook->add_debug_log($debug);
            
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Empty or unparseable payload received',
                'debug' => $debug
            ), 400);
        }
        
        // Process webhook (this will also log the request)
        $result = $webhook->process_webhook($payload, false);
        
        if ($result['success']) {
            return new WP_REST_Response(array(
                'success' => true,
                'event_id' => $result['event_id'],
                'vimeo_id' => $result['vimeo_id'],
                'collection_id' => $result['collection_id'],
                'migration' => $result['migration'],
                'debug' => $result['debug']
            ), 200);
        } else {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => $result['error'],
                'debug' => $result['debug']
            ), 400);
        }
    }
}

// Bootstrap
new ALM_Rest_API();


