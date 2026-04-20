<?php
/**
 * Plugin Name: Jazzedge AI Emails
 * Plugin URI: https://jazzedge.com
 * Description: Generate FluentCRM-ready marketing emails with Katahdin AI.
 * Version: 1.1.0
 * Author: Jazzedge
 * License: GPL v2 or later
 * Text Domain: jazzedge-ai-emails
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('JAZZEDGE_AI_EMAILS_VERSION', '1.1.0');
define('JAZZEDGE_AI_EMAILS_PLUGIN_FILE', __FILE__);
define('JAZZEDGE_AI_EMAILS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JAZZEDGE_AI_EMAILS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JAZZEDGE_AI_EMAILS_PLUGIN_ID', 'jazzedge-ai-emails');

/**
 * Main plugin class.
 */
class Jazzedge_AI_Emails {

    private static $instance = null;

    public $admin;
    public $database;
    public $ai_handler;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->init_components();
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, 'jazzedge_ai_emails_activate');
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        add_action('init', array($this, 'init'), 5);
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        }
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    private function init_components() {
        $this->load_dependencies();
        $this->database = new JE_Emails_Database();
        $this->ai_handler = new JE_Emails_AI_Handler();
        $this->admin = new JE_Emails_Admin();
    }

    private function load_dependencies() {
        require_once JAZZEDGE_AI_EMAILS_PLUGIN_DIR . 'includes/class-database.php';
        require_once JAZZEDGE_AI_EMAILS_PLUGIN_DIR . 'includes/class-ai-handler.php';
        require_once JAZZEDGE_AI_EMAILS_PLUGIN_DIR . 'includes/class-admin.php';
    }

    public function init() {
        $this->database->maybe_add_columns();
        $this->register_with_hub();
        load_plugin_textdomain('jazzedge-ai-emails', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    private function register_with_hub() {
        if (!function_exists('katahdin_ai_hub')) {
            return;
        }
        $hub = katahdin_ai_hub();
        if (!$hub || !isset($hub->plugin_registry) || !method_exists($hub->plugin_registry, 'register')) {
            return;
        }
        $config = array(
            'name' => 'Jazzedge AI Emails',
            'version' => JAZZEDGE_AI_EMAILS_VERSION,
            'features' => array(
                'email_generation',
            ),
            'quota_limit' => 5000,
        );
        $result = $hub->plugin_registry->register(JAZZEDGE_AI_EMAILS_PLUGIN_ID, $config);
        if (is_wp_error($result)) {
            error_log('Jazzedge AI Emails: Hub registration failed: ' . $result->get_error_message());
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            esc_html__('AI Emails', 'jazzedge-ai-emails'),
            esc_html__('AI Emails', 'jazzedge-ai-emails'),
            'manage_options',
            'jazzedge-ai-emails',
            array($this->admin, 'render_page'),
            self::admin_menu_icon_sparkles(),
            25
        );
    }

    /**
     * Sparkles menu icon (Heroicons outline), tuned to match default admin menu grays.
     */
    private static function admin_menu_icon_sparkles() {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#a7aaad" stroke-width="1.5">'
            . '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 003.089 3.09L15.75 12l-2.847.813a4.5 4.5 0 00-3.089 3.09zM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.898 20.198 15.75 21l-1.148-.802a3.75 3.75 0 00-2.633-1.038l-1.147-.062 1.147-.062a3.75 3.75 0 002.633-1.038L15.75 15l1.148.802a3.75 3.75 0 002.633 1.038l1.147.062-1.147.062a3.75 3.75 0 00-2.633 1.038z"/>'
            . '</svg>';

        return 'data:image/svg+xml;charset=utf-8,' . rawurlencode( $svg );
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_jazzedge-ai-emails') {
            return;
        }
        wp_enqueue_script(
            'jazzedge-ai-emails-admin',
            JAZZEDGE_AI_EMAILS_PLUGIN_URL . 'assets/js/admin.js',
            array(),
            JAZZEDGE_AI_EMAILS_VERSION,
            true
        );
        wp_enqueue_style(
            'jazzedge-ai-emails-admin',
            JAZZEDGE_AI_EMAILS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            JAZZEDGE_AI_EMAILS_VERSION
        );
        // `jeAiEmails.nonce` must be wp_create_nonce( 'wp_rest' ) for authenticated REST requests in admin.
        wp_localize_script(
            'jazzedge-ai-emails-admin',
            'jeAiEmails',
            array(
                'nonce' => wp_create_nonce('wp_rest'),
                'restUrl' => esc_url_raw(rest_url('jazzedge-ai-emails/v1/')),
            )
        );
    }

    public function rest_permission() {
        return current_user_can('manage_options');
    }

    public function register_rest_routes() {
        register_rest_route(
            'jazzedge-ai-emails/v1',
            '/generate',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'rest_generate'),
                'permission_callback' => array($this, 'rest_permission'),
            )
        );
        register_rest_route(
            'jazzedge-ai-emails/v1',
            '/revise-subject',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'rest_revise_subject'),
                'permission_callback' => array($this, 'rest_permission'),
            )
        );
        register_rest_route(
            'jazzedge-ai-emails/v1',
            '/revise-body',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'rest_revise_body'),
                'permission_callback' => array($this, 'rest_permission'),
            )
        );
        register_rest_route(
            'jazzedge-ai-emails/v1',
            '/approve',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'rest_approve'),
                'permission_callback' => array($this, 'rest_permission'),
            )
        );
        register_rest_route(
            'jazzedge-ai-emails/v1',
            '/save',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'rest_save'),
                'permission_callback' => array($this, 'rest_permission'),
            )
        );
        register_rest_route(
            'jazzedge-ai-emails/v1',
            '/emails',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'rest_list_emails'),
                'permission_callback' => array($this, 'rest_permission'),
            )
        );
        register_rest_route(
            'jazzedge-ai-emails/v1',
            '/email/(?P<id>\d+)',
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'rest_delete_email'),
                'permission_callback' => array($this, 'rest_permission'),
            )
        );
        register_rest_route(
            'jazzedge-ai-emails/v1',
            '/test',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'rest_test'),
                'permission_callback' => array($this, 'rest_permission'),
            )
        );
        register_rest_route(
            'jazzedge-ai-emails/v1',
            '/settings',
            array(
                array(
                    'methods' => 'GET',
                    'callback' => array($this, 'rest_get_settings'),
                    'permission_callback' => array($this, 'rest_permission'),
                ),
                array(
                    'methods' => 'POST',
                    'callback' => array($this, 'rest_post_settings'),
                    'permission_callback' => array($this, 'rest_permission'),
                ),
            )
        );
        register_rest_route(
            'jazzedge-ai-emails/v1',
            '/draft-state',
            array(
                array(
                    'methods' => 'GET',
                    'callback' => array($this, 'rest_get_draft_state'),
                    'permission_callback' => array($this, 'rest_permission'),
                ),
                array(
                    'methods' => 'POST',
                    'callback' => array($this, 'rest_post_draft_state'),
                    'permission_callback' => array($this, 'rest_permission'),
                ),
            )
        );
    }

    public function rest_generate(WP_REST_Request $request) {
        try {
            $this->database->maybe_create_tables();
            $this->database->maybe_add_columns();

            $prompt = $request->get_param('prompt');
            if (empty($prompt) || !is_string($prompt)) {
                return new WP_Error('invalid_prompt', __('Prompt is required.', 'jazzedge-ai-emails'), array('status' => 400));
            }

            $points_param = $request->get_param('points');
            $about_param = $request->get_param('about');
            $base_email_param = $request->get_param('baseEmail');
            $design_param = $request->get_param('design');

            $prompt_points_json = '';
            if (is_array($points_param)) {
                $clean_points = array();
                foreach ($points_param as $p) {
                    $clean_points[] = is_string($p) ? sanitize_text_field(wp_unslash($p)) : '';
                }
                $prompt_points_json = wp_json_encode($clean_points);
            }

            $urls_param = $request->get_param('urls');
            $draft_urls = $this->sanitize_draft_urls($urls_param);
            $prompt_urls_json = wp_json_encode($draft_urls);

            $title = '';
            $custom = JE_Emails_AI_Handler::get_custom_system_prompt();

            $subject_result = $this->ai_handler->generate_subject($prompt, array(), $custom);
            if (is_wp_error($subject_result)) {
                return $subject_result;
            }

            $body_result = $this->ai_handler->generate_body($prompt, array(), $custom, $subject_result['content']);
            if (is_wp_error($body_result)) {
                return $body_result;
            }

            $id = $this->database->save_email(
                array(
                    'title' => $title,
                    'subject' => $subject_result['content'],
                    'body' => $body_result['content'],
                    'status' => 'draft',
                    'subject_thread' => wp_json_encode($subject_result['thread']),
                    'body_thread' => wp_json_encode($body_result['thread']),
                    'subject_approved' => 0,
                    'body_approved' => 0,
                    'prompt_points' => $prompt_points_json,
                    'prompt_about' => is_string($about_param) ? sanitize_textarea_field(wp_unslash($about_param)) : '',
                    'prompt_base_email' => is_string($base_email_param) ? wp_unslash($base_email_param) : '',
                    'prompt_design' => is_string($design_param) ? sanitize_textarea_field(wp_unslash($design_param)) : '',
                    'prompt_urls' => $prompt_urls_json,
                )
            );

            if (!$id) {
                return new WP_Error('db_error', __('Could not save email.', 'jazzedge-ai-emails'), array('status' => 500));
            }

            return rest_ensure_response(
                array(
                    'id' => (int) $id,
                    'subject' => $subject_result['content'],
                    'body' => $body_result['content'],
                    'subject_thread' => $subject_result['thread'],
                    'body_thread' => $body_result['thread'],
                    'debug' => array(
                        'subject_messages' => $subject_result['messages_sent'],
                        'body_messages' => $body_result['messages_sent'],
                    ),
                )
            );
        } catch (Exception $e) {
            error_log('Jazzedge AI Emails rest_generate error: ' . $e->getMessage());
            return new WP_Error('server_error', $e->getMessage(), array('status' => 500));
        }
    }

    public function rest_revise_subject(WP_REST_Request $request) {
        $id = absint($request->get_param('id'));
        $feedback = $request->get_param('feedback');
        if (!$id || empty($feedback) || !is_string($feedback)) {
            return new WP_Error('invalid_params', __('Email id and feedback are required.', 'jazzedge-ai-emails'), array('status' => 400));
        }
        $row = $this->database->get_email($id);
        if (!$row) {
            return new WP_Error('not_found', __('Email not found.', 'jazzedge-ai-emails'), array('status' => 404));
        }
        $thread = JE_Emails_AI_Handler::decode_thread($row['subject_thread']);
        $custom = JE_Emails_AI_Handler::get_custom_system_prompt();
        $result = $this->ai_handler->generate_subject($feedback, $thread, $custom, true);
        if (is_wp_error($result)) {
            return $result;
        }
        $this->database->update_email(
            $id,
            array(
                'subject' => $result['content'],
                'subject_thread' => wp_json_encode($result['thread']),
                'subject_approved' => 0,
            )
        );
        return rest_ensure_response(
            array(
                'subject' => $result['content'],
                'subject_thread' => $result['thread'],
                'debug' => array(
                    'messages_sent' => $result['messages_sent'],
                ),
            )
        );
    }

    public function rest_revise_body(WP_REST_Request $request) {
        $id = absint($request->get_param('id'));
        $feedback = $request->get_param('feedback');
        if (!$id || empty($feedback) || !is_string($feedback)) {
            return new WP_Error('invalid_params', __('Email id and feedback are required.', 'jazzedge-ai-emails'), array('status' => 400));
        }
        $row = $this->database->get_email($id);
        if (!$row) {
            return new WP_Error('not_found', __('Email not found.', 'jazzedge-ai-emails'), array('status' => 404));
        }
        $thread = JE_Emails_AI_Handler::decode_thread($row['body_thread']);
        $custom = JE_Emails_AI_Handler::get_custom_system_prompt();
        $result = $this->ai_handler->generate_body($feedback, $thread, $custom, $row['subject'], true);
        if (is_wp_error($result)) {
            return $result;
        }
        $this->database->update_email(
            $id,
            array(
                'body' => $result['content'],
                'body_thread' => wp_json_encode($result['thread']),
                'body_approved' => 0,
            )
        );
        return rest_ensure_response(
            array(
                'body' => $result['content'],
                'body_thread' => $result['thread'],
                'debug' => array(
                    'messages_sent' => $result['messages_sent'],
                ),
            )
        );
    }

    public function rest_approve(WP_REST_Request $request) {
        $id = absint($request->get_param('id'));
        $field = $request->get_param('field');
        if (!$id || !in_array($field, array('subject', 'body'), true)) {
            return new WP_Error('invalid_params', __('Valid id and field (subject|body) required.', 'jazzedge-ai-emails'), array('status' => 400));
        }
        if (!$this->database->get_email($id)) {
            return new WP_Error('not_found', __('Email not found.', 'jazzedge-ai-emails'), array('status' => 404));
        }
        $value = $request->get_param('value');
        if ($field === 'subject' && is_string($value)) {
            $this->database->update_email($id, array('subject' => sanitize_text_field($value)));
        }
        if ($field === 'body' && is_string($value)) {
            // Preserve Gutenberg block comments (wp_kses_post strips HTML comments).
            $this->database->update_email($id, array('body' => $value));
        }
        $ok = $this->database->approve_field($id, $field);
        if (!$ok) {
            return new WP_Error('db_error', __('Could not update approval.', 'jazzedge-ai-emails'), array('status' => 500));
        }
        $row = $this->database->get_email($id);
        return rest_ensure_response(
            array(
                'success' => true,
                'subject_approved' => (bool) (int) $row['subject_approved'],
                'body_approved' => (bool) (int) $row['body_approved'],
            )
        );
    }

    public function rest_save(WP_REST_Request $request) {
        $id = absint($request->get_param('id'));
        if (!$id) {
            return new WP_Error('invalid_params', __('Email id is required.', 'jazzedge-ai-emails'), array('status' => 400));
        }
        $row = $this->database->get_email($id);
        if (!$row) {
            return new WP_Error('not_found', __('Email not found.', 'jazzedge-ai-emails'), array('status' => 404));
        }
        if (!(int) $row['subject_approved'] || !(int) $row['body_approved']) {
            return new WP_Error('not_ready', __('Both subject and body must be approved before saving.', 'jazzedge-ai-emails'), array('status' => 400));
        }
        $subject = $request->get_param('subject');
        $body = $request->get_param('body');
        $current_subject = is_string($subject) ? sanitize_text_field($subject) : sanitize_text_field((string) $row['subject']);
        if (function_exists('mb_substr')) {
            $current_subject = mb_substr($current_subject, 0, 255);
        } else {
            $current_subject = substr($current_subject, 0, 255);
        }
        $sync = array(
            'title' => $current_subject,
        );
        if (is_string($subject)) {
            $sync['subject'] = sanitize_text_field($subject);
        }
        if (is_string($body)) {
            $sync['body'] = $body;
        }
        $this->database->update_email($id, $sync);
        $this->database->approve_email($id);
        return rest_ensure_response(array('success' => true));
    }

    public function rest_list_emails() {
        $this->database->maybe_add_columns();
        return rest_ensure_response($this->database->get_all_emails());
    }

    public function rest_delete_email(WP_REST_Request $request) {
        $id = absint($request->get_param('id'));
        if (!$this->database->get_email($id)) {
            return new WP_Error('not_found', __('Email not found.', 'jazzedge-ai-emails'), array('status' => 404));
        }
        $this->database->delete_email($id);
        return rest_ensure_response(array('success' => true));
    }

    public function rest_test() {
        $result = $this->ai_handler->test_connection();
        return rest_ensure_response($result);
    }

    public function rest_get_settings() {
        return rest_ensure_response(
            array(
                'custom_system_prompt' => JE_Emails_AI_Handler::get_custom_system_prompt(),
            )
        );
    }

    public function rest_post_settings(WP_REST_Request $request) {
        $prompt = $request->get_param('custom_system_prompt');
        if (!is_string($prompt)) {
            $prompt = '';
        }
        update_option('je_emails_system_prompt', wp_kses_post($prompt), false);
        return rest_ensure_response(
            array(
                'success' => true,
                'custom_system_prompt' => JE_Emails_AI_Handler::get_custom_system_prompt(),
            )
        );
    }

    public function rest_get_draft_state() {
        $raw = get_option('je_emails_draft_state', '');
        $state = array(
            'points' => array(),
            'baseEmail' => '',
            'designNotes' => '',
            'emailAbout' => '',
            'urls' => array(
                array('url' => '', 'text' => ''),
                array('url' => '', 'text' => ''),
            ),
        );
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $state = wp_parse_args($decoded, $state);
            }
        }
        $state['urls'] = $this->sanitize_draft_urls(isset($state['urls']) ? $state['urls'] : null);
        return rest_ensure_response($state);
    }

    public function rest_post_draft_state(WP_REST_Request $request) {
        $points = $request->get_param('points');
        $base_email = $request->get_param('baseEmail');
        $design_notes = $request->get_param('designNotes');
        $email_about = $request->get_param('emailAbout');
        $urls = $request->get_param('urls');

        $state = array(
            'points' => is_array($points) ? array_map('sanitize_text_field', $points) : array(),
            'baseEmail' => is_string($base_email) ? $base_email : '',
            'designNotes' => is_string($design_notes) ? sanitize_textarea_field($design_notes) : '',
            'emailAbout' => is_string($email_about) ? sanitize_textarea_field($email_about) : '',
            'urls' => $this->sanitize_draft_urls($urls),
        );

        update_option('je_emails_draft_state', wp_json_encode($state), false);

        return rest_ensure_response(array('success' => true));
    }

    /**
     * Normalize up to two URL rows for draft storage / generate payload.
     *
     * @param mixed $urls_param
     * @return array<int, array{url: string, text: string}>
     */
    private function sanitize_draft_urls($urls_param) {
        $out = array(
            array('url' => '', 'text' => ''),
            array('url' => '', 'text' => ''),
        );
        if (!is_array($urls_param)) {
            return $out;
        }
        for ($i = 0; $i < 2; $i++) {
            if (!isset($urls_param[$i]) || !is_array($urls_param[$i])) {
                continue;
            }
            $item = $urls_param[$i];
            $u = isset($item['url']) && is_string($item['url']) ? esc_url_raw(wp_unslash($item['url'])) : '';
            $t = isset($item['text']) && is_string($item['text']) ? sanitize_text_field(wp_unslash($item['text'])) : '';
            $out[$i] = array(
                'url' => $u,
                'text' => $t,
            );
        }
        return $out;
    }

    public function deactivate() {
        flush_rewrite_rules();
    }
}

/**
 * Plugin activation (runs before instance may exist).
 */
function jazzedge_ai_emails_activate() {
    if (!defined('JAZZEDGE_AI_EMAILS_PLUGIN_DIR')) {
        return;
    }
    require_once JAZZEDGE_AI_EMAILS_PLUGIN_DIR . 'includes/class-database.php';
    $db = new JE_Emails_Database();
    $db->create_tables();
    $db->maybe_add_columns();
    flush_rewrite_rules();
}

/**
 * Bootstrap.
 */
function jazzedge_ai_emails() {
    return Jazzedge_AI_Emails::instance();
}

add_action('plugins_loaded', 'jazzedge_ai_emails');
