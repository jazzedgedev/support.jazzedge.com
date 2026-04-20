<?php
if (!defined('ABSPATH')) exit;

class JECM_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_menu', array($this, 'update_menu_badge'), 20);
        add_action('admin_init', array($this, 'save_settings'));
        add_action('admin_init', array($this, 'save_webhook_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_jecm_remove_tag', array($this, 'ajax_remove_tag'));
        add_action('wp_ajax_jecm_remove_tags_bulk', array($this, 'ajax_remove_tags_bulk'));
        add_action('wp_ajax_jecm_get_all_tags', array($this, 'ajax_get_all_tags'));
        add_action('wp_ajax_jecm_add_tag', array($this, 'ajax_add_tag'));
        add_action('wp_ajax_jecm_cancel_sub', array($this, 'ajax_cancel_sub'));
        add_action('wp_ajax_jecm_update_expiry', array($this, 'ajax_update_expiry'));
        add_action('wp_ajax_jecm_add_task', array($this, 'ajax_add_task'));
        add_action('wp_ajax_jecm_reachout', array($this, 'ajax_reachout'));
        add_action('wp_ajax_jecm_add_to_keap', array($this, 'ajax_add_to_keap'));
        add_action('wp_ajax_jecm_dismiss_order', array($this, 'ajax_dismiss_order'));
        add_action('wp_ajax_jecm_delete_order', array($this, 'ajax_delete_order'));
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_je-keap-manager') {
            return;
        }
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css', array(), '1.13.2');
    }

    public function add_menu() {
        add_menu_page(
            'Keap Manage',
            'Keap Manage',
            'manage_options',
            'je-keap-manager',
            array($this, 'render_page'),
            'dashicons-groups',
            55
        );
    }

    /**
     * Open tasks count from jecm_tasks table (status = open).
     */
    private function get_open_task_count() {
        return JECM_Tasks::count_open();
    }

    /**
     * Show open-task count badge on the Keap Manage admin menu item (same pattern as plugin updates).
     */
    public function update_menu_badge() {
        global $menu;

        $task_count = (int) $this->get_open_task_count();
        if ($task_count <= 0 || !is_array($menu)) {
            return;
        }

        foreach ($menu as $key => $item) {
            if (isset($item[2]) && $item[2] === 'je-keap-manager' && isset($menu[$key][0]) && is_string($menu[$key][0])) {
                if (strpos($menu[$key][0], 'jecm-menu-task-badge') !== false) {
                    break;
                }
                $menu[$key][0] .= ' <span class="awaiting-mod update-plugins count-' . esc_attr((string) $task_count) . ' jecm-menu-task-badge">'
                    . '<span class="plugin-count">' . esc_html((string) $task_count) . '</span></span>';
                break;
            }
        }
    }

    private function current_tab() {
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'find';
        return in_array($tab, array('find', 'cancel', 'settings', 'log', 'balance', 'tasks', 'webhook', 'test'), true) ? $tab : 'find';
    }

    private function tab_url($tab) {
        return admin_url('admin.php?page=je-keap-manager&tab=' . $tab);
    }

    /**
     * Transient key for Find Student tab cached record (per logged-in admin).
     */
    private function jecm_find_student_cache_key() {
        return 'jecm_fst_' . get_current_user_id();
    }

    /**
     * Build admin URL for the audit log with optional filters and paging.
     *
     * @param array{filter_action?: string, filter_user?: int, paged?: int} $args
     */
    private function log_tab_url($args = array()) {
        $q = array(
            'page' => 'je-keap-manager',
            'tab'  => 'log',
        );
        if (!empty($args['filter_action'])) {
            $q['filter_action'] = sanitize_key($args['filter_action']);
        }
        if (!empty($args['filter_user'])) {
            $q['filter_user'] = (int) $args['filter_user'];
        }
        if (!empty($args['paged']) && (int) $args['paged'] > 1) {
            $q['paged'] = (int) $args['paged'];
        }

        return add_query_arg($q, admin_url('admin.php'));
    }

    /**
     * @param array{paged?: int} $args
     */
    private function webhook_tab_url($args = array()) {
        $q = array(
            'page' => 'je-keap-manager',
            'tab'  => 'webhook',
        );
        if (!empty($args['paged']) && (int) $args['paged'] > 1) {
            $q['paged'] = (int) $args['paged'];
        }
        return add_query_arg($q, admin_url('admin.php'));
    }

    /**
     * @param array{task_status?: string, paged?: int} $args
     */
    private function tasks_tab_url($args = array()) {
        $q = array(
            'page' => 'je-keap-manager',
            'tab'  => 'tasks',
        );
        if (!empty($args['task_status']) && is_string($args['task_status'])) {
            $ts = sanitize_key($args['task_status']);
            if ($ts === 'completed' || $ts === 'all') {
                $q['task_status'] = $ts;
            }
        }
        if (!empty($args['paged']) && (int) $args['paged'] > 1) {
            $q['paged'] = (int) $args['paged'];
        }

        return add_query_arg($q, admin_url('admin.php'));
    }

    /**
     * Normalize tag ID list from stored destination row (tag_ids array and/or legacy tag_id, comma-separated string allowed).
     *
     * @param array<string, mixed> $row
     * @return list<int>
     */
    private function jecm_autologin_dest_tag_ids_normalize(array $row) {
        $out = array();
        if (!empty($row['tag_ids']) && is_array($row['tag_ids'])) {
            foreach ($row['tag_ids'] as $v) {
                $tid = absint($v);
                if ($tid > 0) {
                    $out[] = $tid;
                }
            }
        }
        if (empty($out) && array_key_exists('tag_id', $row) && (string) $row['tag_id'] !== '') {
            $legacy_raw = $row['tag_id'];
            if (is_string($legacy_raw) && strpos($legacy_raw, ',') !== false) {
                foreach (explode(',', $legacy_raw) as $part) {
                    $tid = absint(trim($part));
                    if ($tid > 0) {
                        $out[] = $tid;
                    }
                }
            } else {
                $tid = absint($legacy_raw);
                if ($tid > 0) {
                    $out[] = $tid;
                }
            }
        }

        return array_values(array_unique(array_map('intval', $out)));
    }

    /**
     * @return array<int, array{title: string, path: string, site_index: int, tag_ids: list<int>}>
     */
    private function jecm_get_autologin_destinations() {
        $raw = get_option('jecm_autologin_destinations', '');
        $out = array();
        if ($raw !== '' && $raw !== false) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $out[] = array(
                        'title'      => isset($row['title']) ? (string) $row['title'] : '',
                        'path'       => isset($row['path']) ? (string) $row['path'] : '',
                        'site_index' => isset($row['site_index']) ? absint($row['site_index']) : 0,
                        'tag_ids'    => $this->jecm_autologin_dest_tag_ids_normalize($row),
                    );
                }
            }
        }
        if (empty($out)) {
            return array(
                array('title' => 'Dashboard', 'path' => '/dashboard/', 'site_index' => 0, 'tag_ids' => array()),
                array('title' => 'Account', 'path' => '/account/', 'site_index' => 0, 'tag_ids' => array()),
            );
        }

        return $out;
    }

    /**
     * MemberMouse autologin sites (base URL + auth key per site).
     *
     * @return list<array{name: string, url: string, auth_key: string}>
     */
    private function jecm_get_autologin_sites() {
        $raw = get_option('jecm_autologin_sites', '');
        $out = array();
        if ($raw !== '' && $raw !== false) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $out[] = array(
                        'name'     => isset($row['name']) ? (string) $row['name'] : '',
                        'url'      => isset($row['url']) ? (string) $row['url'] : '',
                        'auth_key' => isset($row['auth_key']) ? (string) $row['auth_key'] : '',
                    );
                }
            }
        }
        if (empty($out)) {
            return array(
                array(
                    'name'     => 'Jazzedge Academy',
                    'url'      => 'https://jazzedge.academy',
                    'auth_key' => 'K9DqpZpAhvqe',
                ),
                array(
                    'name'     => 'Summer Piano Jam',
                    'url'      => 'https://summerpianojam.com',
                    'auth_key' => '9JeYjtLTtj3G',
                ),
            );
        }

        return $out;
    }

    /**
     * @return array{first_name: string, last_name: string}
     */
    private function fetch_keap_contact_name_parts($contact_id) {
        $api_key = get_option('jecm_keap_api_key');
        if (!$api_key || !$contact_id) {
            return array('first_name' => '', 'last_name' => '');
        }
        $response = wp_remote_get(
            'https://api.infusionsoft.com/crm/rest/v1/contacts/' . intval($contact_id),
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 10,
            )
        );
        if (is_wp_error($response)) {
            return array('first_name' => '', 'last_name' => '');
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($body)) {
            return array('first_name' => '', 'last_name' => '');
        }

        return array(
            'first_name' => trim((string) ($body['given_name'] ?? '')),
            'last_name'  => trim((string) ($body['family_name'] ?? '')),
        );
    }

    private function get_keap_contact_display_name($contact_id) {
        $n = $this->fetch_keap_contact_name_parts($contact_id);

        return trim($n['first_name'] . ' ' . $n['last_name']);
    }

    private function merge_jecm_recent_student($email, $name, $contact_id) {
        $uid = get_current_user_id();
        if (!$uid || !is_email($email)) {
            return;
        }
        $list = get_user_meta($uid, 'jecm_recent_students', true);
        if (!is_array($list)) {
            $list = array();
        }
        $email_l = strtolower($email);
        $keep    = array();
        foreach ($list as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            if (strtolower((string) ($entry['email'] ?? '')) === $email_l) {
                continue;
            }
            $keep[] = $entry;
        }
        array_unshift(
            $keep,
            array(
                'email'       => $email,
                'name'        => $name !== '' ? $name : $email,
                'contact_id'  => (int) $contact_id,
                'viewed_at'   => time(),
            )
        );
        update_user_meta($uid, 'jecm_recent_students', array_slice($keep, 0, 20));
    }

    public function save_settings() {
        if (!isset($_POST['jecm_nonce']) || !wp_verify_nonce($_POST['jecm_nonce'], 'jecm_settings')) return;

        // Test API key
        if (isset($_POST['jecm_test_api_key'])) {
            $api_key = sanitize_text_field($_POST['jecm_keap_api_key']);
            $result  = $this->test_keap_api_key($api_key);
            $type    = $result['success'] ? 'success' : 'error';
            add_settings_error('jecm', 'test', 'Keap API Test: ' . $result['message'], $type);
            return;
        }

        // Save settings
        if (isset($_POST['jecm_save_settings'])) {
            update_option('jecm_keap_api_key',        sanitize_text_field($_POST['jecm_keap_api_key']));
            update_option('jecm_keap_academy_tag_id', absint($_POST['jecm_keap_academy_tag_id']));
            update_option('jecm_keap_hsp_tag_id',     absint($_POST['jecm_keap_hsp_tag_id']));
            update_option('jecm_fc_academy_tag_id',   absint($_POST['jecm_fc_academy_tag_id']));
            update_option('jecm_fc_hsp_tag_id',       absint($_POST['jecm_fc_hsp_tag_id']));
            update_option('jecm_keap_monthly_academy_tags', sanitize_text_field($_POST['jecm_keap_monthly_academy_tags'] ?? ''));
            update_option('jecm_keap_yearly_academy_tags',  sanitize_text_field($_POST['jecm_keap_yearly_academy_tags'] ?? ''));
            update_option('jecm_keap_monthly_hsp_tags',     sanitize_text_field($_POST['jecm_keap_monthly_hsp_tags'] ?? ''));
            update_option('jecm_keap_yearly_hsp_tags',      sanitize_text_field($_POST['jecm_keap_yearly_hsp_tags'] ?? ''));
            update_option('jecm_fc_payment_failed_tag_id', sanitize_text_field($_POST['jecm_fc_payment_failed_tag_id'] ?? ''));
            update_option('jecm_fc_payment_failed_reachout_tag_id', sanitize_text_field($_POST['jecm_fc_payment_failed_reachout_tag_id'] ?? ''));

            // Webhook: update if value present, clear only if explicit clear flag sent
            $wh_val = isset($_POST['jecm_flowmattic_new_user_webhook'])
                ? esc_url_raw(wp_unslash((string) $_POST['jecm_flowmattic_new_user_webhook']))
                : null;
            if ($wh_val !== null) {
                if ($wh_val !== '' || !empty($_POST['jecm_flowmattic_webhook_clear'])) {
                    update_option('jecm_flowmattic_new_user_webhook', $wh_val);
                }
            }

            // Tag IDs: update if value present, clear only if explicit clear flag sent
            $tid_val = isset($_POST['jecm_new_student_tag_ids'])
                ? preg_replace('/[^\d,]/', '', wp_unslash((string) $_POST['jecm_new_student_tag_ids']))
                : null;
            if ($tid_val !== null) {
                if ($tid_val !== '' || !empty($_POST['jecm_new_student_tag_ids_clear'])) {
                    update_option('jecm_new_student_tag_ids', $tid_val);
                }
            }

            if (isset($_POST['jecm_autologin_destinations'])) {
                $json_raw = wp_unslash($_POST['jecm_autologin_destinations']);
                $decoded  = json_decode($json_raw, true);
                $clean    = array();
                if (is_array($decoded)) {
                    foreach ($decoded as $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        $t = isset($row['title']) ? sanitize_text_field((string) $row['title']) : '';
                        $p = isset($row['path']) ? sanitize_text_field((string) $row['path']) : '';
                        $i = isset($row['site_index']) ? absint($row['site_index']) : 0;
                        $tag_ids_clean = $this->jecm_autologin_dest_tag_ids_normalize($row);
                        if ($t === '' && $p === '') {
                            continue;
                        }
                        $clean[] = array(
                            'title'      => $t,
                            'path'       => $p,
                            'site_index' => $i,
                            'tag_ids'    => $tag_ids_clean,
                        );
                    }
                }
                update_option('jecm_autologin_destinations', wp_json_encode($clean));
            }

            if (isset($_POST['jecm_autologin_sites'])) {
                $json_raw = wp_unslash($_POST['jecm_autologin_sites']);
                $decoded  = json_decode($json_raw, true);
                $clean    = array();
                if (is_array($decoded)) {
                    foreach ($decoded as $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        $n = isset($row['name']) ? sanitize_text_field((string) $row['name']) : '';
                        $u = isset($row['url']) ? esc_url_raw((string) $row['url']) : '';
                        $k = isset($row['auth_key']) ? sanitize_text_field((string) $row['auth_key']) : '';
                        if ($n === '' && $u === '' && $k === '') {
                            continue;
                        }
                        $clean[] = array(
                            'name'     => $n,
                            'url'      => $u,
                            'auth_key' => $k,
                        );
                    }
                }
                update_option('jecm_autologin_sites', wp_json_encode($clean));
            }

            add_settings_error('jecm', 'saved', 'Settings saved.', 'success');
        }
    }

    public function save_webhook_settings() {
        if (!isset($_POST['jecm_webhook_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['jecm_webhook_nonce'])), 'jecm_webhook')) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['jecm_regenerate_webhook_secret'])) {
            update_option('jecm_fc_webhook_secret', wp_generate_password(48, false, false));
            add_settings_error('jecm', 'webhook_secret', __('FluentCart webhook secret regenerated. Update the header value on Jazzedge Academy.', 'je-keap-manager'), 'success');
            return;
        }

        if (isset($_POST['jecm_save_webhook'])) {
            $secret = isset($_POST['jecm_fc_webhook_secret']) ? sanitize_text_field(wp_unslash($_POST['jecm_fc_webhook_secret'])) : '';
            update_option('jecm_fc_webhook_secret', $secret);
            add_settings_error('jecm', 'webhook_saved', __('Webhook settings saved.', 'je-keap-manager'), 'success');
            return;
        }

        if (
            isset($_POST['jecm_action']) && $_POST['jecm_action'] === 'purge_webhook_log'
            && isset($_POST['jecm_webhook_purge_nonce'])
            && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['jecm_webhook_purge_nonce'])), 'jecm_purge_webhook_log')
        ) {
            JECM_FluentCart_Webhook::purge_webhook_logs();
            add_settings_error('jecm', 'webhook_purged', __('Incoming webhook log purged.', 'je-keap-manager'), 'success');
        }
    }

    public function ajax_remove_tag() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'jecm_remove_tag')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        $api_key = get_option('jecm_keap_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'No Keap API key configured.'));
        }

        $contact_id = absint($_POST['contact_id'] ?? 0);
        $tag_id     = absint($_POST['tag_id'] ?? 0);
        if (!$contact_id || !$tag_id) {
            wp_send_json_error(array('message' => 'Missing contact or tag ID.'));
        }

        $keap   = new JECM_Keap_API($api_key);
        $result = $keap->remove_tags($contact_id, array($tag_id));

        $email        = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $tag_name     = sanitize_text_field(wp_unslash($_POST['tag_name'] ?? ''));
        $contact_name = sanitize_text_field(wp_unslash($_POST['contact_name'] ?? ''));

        JECM_Logger::log_action(array(
            'action'       => 'remove_tag',
            'email'        => $email,
            'contact_id'   => $contact_id,
            'contact_name' => $contact_name,
            'keap_success' => !empty($result['success']) ? 1 : 0,
            'fc_success'   => null,
            'details'      => array(
                'tag_id'   => $tag_id,
                'tag_name' => $tag_name,
            ),
        ));

        if (!empty($result['success'])) {
            wp_send_json_success();
        }

        wp_send_json_error(array('message' => $result['message'] ?? 'Remove failed.'));
    }

    public function ajax_remove_tags_bulk() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'jecm_remove_tag')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        $api_key = get_option('jecm_keap_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'No Keap API key configured.'));
        }

        $contact_id = absint($_POST['contact_id'] ?? 0);
        if (!$contact_id) {
            wp_send_json_error(array('message' => 'Missing contact ID.'));
        }

        $tag_ids_raw = isset($_POST['tag_ids']) ? wp_unslash($_POST['tag_ids']) : '[]';
        $tag_names_raw = isset($_POST['tag_names']) ? wp_unslash($_POST['tag_names']) : '[]';
        $tag_ids_dec   = json_decode($tag_ids_raw, true);
        $tag_names_dec = json_decode($tag_names_raw, true);
        if (!is_array($tag_ids_dec) || !is_array($tag_names_dec)) {
            wp_send_json_error(array('message' => 'Invalid tag payload.'));
        }

        $tag_ids = array_values(array_unique(array_filter(array_map('absint', $tag_ids_dec))));
        if (empty($tag_ids)) {
            wp_send_json_error(array('message' => 'No tags selected.'));
        }

        $tag_names = array();
        foreach ($tag_ids as $i => $tid) {
            $raw_name = isset($tag_names_dec[ $i ]) ? (string) $tag_names_dec[ $i ] : '';
            $tag_names[] = sanitize_text_field($raw_name);
        }

        $email        = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $contact_name = sanitize_text_field(wp_unslash($_POST['contact_name'] ?? ''));

        $keap   = new JECM_Keap_API($api_key);
        $result = $keap->remove_tags($contact_id, $tag_ids);

        $batch_ok = !empty($result['success']);
        foreach ($tag_ids as $i => $tag_id) {
            JECM_Logger::log_action(array(
                'action'       => 'remove_tag',
                'email'        => $email,
                'contact_id'   => $contact_id,
                'contact_name' => $contact_name,
                'keap_success' => $batch_ok ? 1 : 0,
                'fc_success'   => null,
                'details'      => array(
                    'tag_id'   => $tag_id,
                    'tag_name' => isset($tag_names[ $i ]) ? $tag_names[ $i ] : '',
                ),
            ));
        }

        if ($batch_ok) {
            wp_send_json_success();
        }

        wp_send_json_error(array('message' => $result['message'] ?? 'Remove failed.'));
    }

    public function ajax_get_all_tags() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'jecm_add_tag')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        $api_key = get_option('jecm_keap_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'No Keap API key configured.'));
        }

        $force_refresh = isset($_POST['refresh']) && sanitize_text_field(wp_unslash($_POST['refresh'])) === '1';
        $keap   = new JECM_Keap_API($api_key);
        $result = $keap->get_all_tags($force_refresh);

        if (empty($result['success'])) {
            wp_send_json_error(array('message' => $result['message'] ?? __('Could not load tags.', 'je-keap-manager')));
        }

        wp_send_json_success(array('tags' => $result['tags'] ?? array()));
    }

    public function ajax_add_tag() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'jecm_add_tag')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        $api_key = get_option('jecm_keap_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'No Keap API key configured.'));
        }

        $contact_id = absint($_POST['contact_id'] ?? 0);
        if (!$contact_id) {
            wp_send_json_error(array('message' => __('Missing contact ID.', 'je-keap-manager')));
        }

        $tag_ids_raw = isset($_POST['tag_ids']) ? wp_unslash($_POST['tag_ids']) : '[]';
        $decoded_ids = json_decode($tag_ids_raw, true);
        if (!is_array($decoded_ids) || empty($decoded_ids)) {
            wp_send_json_error(array('message' => __('No tags selected.', 'je-keap-manager')));
        }

        $tag_names_raw = isset($_POST['tag_names']) ? wp_unslash($_POST['tag_names']) : '[]';
        $decoded_names = json_decode($tag_names_raw, true);
        if (!is_array($decoded_names) || count($decoded_names) !== count($decoded_ids)) {
            wp_send_json_error(array('message' => __('Tag names must match tag IDs.', 'je-keap-manager')));
        }

        $tag_pairs = array();
        foreach ($decoded_ids as $i => $raw_id) {
            $tid = absint($raw_id);
            if ($tid <= 0) {
                continue;
            }
            $tname = isset($decoded_names[ $i ]) ? sanitize_text_field((string) $decoded_names[ $i ]) : '';
            $tag_pairs[ $tid ] = $tname;
        }

        $tag_ids = array_keys($tag_pairs);

        if (empty($tag_ids)) {
            wp_send_json_error(array('message' => __('No valid tag IDs.', 'je-keap-manager')));
        }

        $email        = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $contact_name = sanitize_text_field(wp_unslash($_POST['contact_name'] ?? ''));

        $keap   = new JECM_Keap_API($api_key);
        $result = $keap->apply_tags_batch($contact_id, $tag_ids);

        if (empty($result['success'])) {
            wp_send_json_error(array('message' => $result['message'] ?? __('Apply failed.', 'je-keap-manager')));
        }

        foreach ($tag_pairs as $tid => $tname) {
            JECM_Logger::log_action(array(
                'action'       => 'add_tag',
                'email'        => $email,
                'contact_id'   => $contact_id,
                'contact_name' => $contact_name,
                'keap_success' => 1,
                'fc_success'   => null,
                'details'      => array(
                    'tag_id'   => (int) $tid,
                    'tag_name' => $tname,
                ),
            ));
        }

        wp_send_json_success();
    }

    public function ajax_reachout() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'je-keap-manager')));
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'jecm_reachout')) {
            wp_send_json_error(array('message' => __('Invalid nonce', 'je-keap-manager')));
        }

        $contact_id = absint($_POST['contact_id'] ?? 0);
        $email      = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        if ($email === '') {
            wp_send_json_error(array('message' => __('Missing email.', 'je-keap-manager')));
        }

        $tag_raw = get_option('jecm_fc_payment_failed_reachout_tag_id', '');
        $tag_ids = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) $tag_raw)))));
        if (empty($tag_ids)) {
            wp_send_json_error(array('message' => __('Reach Out tag not configured in Settings.', 'je-keap-manager')));
        }

        if (!function_exists('FluentCrmApi')) {
            wp_send_json_error(array('message' => __('FluentCRM is not available.', 'je-keap-manager')));
        }

        $contactApi = FluentCrmApi('contacts');
        $fc         = $contactApi->getContact($email);
        if (!$fc) {
            $fc = $contactApi->createOrUpdate(array('email' => $email));
        }
        if (!$fc) {
            wp_send_json_error(array('message' => __('Could not find or create FluentCRM contact.', 'je-keap-manager')));
        }

        $fc->attachTags($tag_ids);

        $contact_name = '';
        if ($contact_id > 0) {
            $contact_name = $this->get_keap_contact_display_name($contact_id);
        }

        JECM_Logger::log_action(array(
            'action'       => 'apply_tag',
            'email'        => $email,
            'contact_id'   => $contact_id,
            'contact_name' => $contact_name,
            'keap_success' => null,
            'fc_success'   => 1,
            'details'      => array(
                'tag_ids'  => $tag_ids,
                'tag_name' => 'Payment Failed — Reach Out',
            ),
        ));

        $task_note         = __('Payment Failed — follow up with student. Reach Out tag applied. Email sent.', 'je-keap-manager');
        $task_student_name = $contact_name !== '' ? $contact_name : $email;
        $task_id           = JECM_Tasks::insert_open_task($email, $contact_id, $task_student_name, $task_note);

        if ($task_id > 0) {
            wp_send_json_success(array('message' => __('Reach Out tag applied and follow-up task created.', 'je-keap-manager')));
        }

        wp_send_json_success(array('message' => __('Reach Out tag applied. Follow-up task could not be created.', 'je-keap-manager')));
    }

    public function ajax_add_to_keap() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'je-keap-manager')));
        }
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'jecm_add_to_keap')) {
            wp_send_json_error(array('message' => __('Invalid nonce.', 'je-keap-manager')));
        }
        $email     = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $full_name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        if (empty($email)) {
            wp_send_json_error(array('message' => __('Email is required.', 'je-keap-manager')));
        }

        $api_key = get_option('jecm_keap_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('No Keap API key configured.', 'je-keap-manager')));
        }

        // Split display name into first/last
        $parts      = explode(' ', $full_name, 2);
        $first_name = $parts[0] ?? '';
        $last_name  = $parts[1] ?? '';

        $keap   = new JECM_Keap_API($api_key);
        $result = $keap->create_contact($email, $first_name, $last_name);

        if (!empty($result['success'])) {
            $contact_id = $result['contact_id'] ?? 0;

            $flowmattic_url = get_option('jecm_flowmattic_new_user_webhook', '');
            $academy_fired  = false;
            if (!empty($flowmattic_url)) {
                $parts    = explode(' ', $full_name, 2);
                $wh_fname = trim($parts[0] ?? '');
                $wh_lname = trim($parts[1] ?? '');
                wp_remote_post(
                    $flowmattic_url,
                    array(
                        'timeout'  => 15,
                        'blocking' => false,
                        'headers'  => array('Content-Type' => 'application/json'),
                        'body'     => wp_json_encode(
                            array(
                                'email' => $email,
                                'fname' => $wh_fname,
                                'lname' => $wh_lname,
                            )
                        ),
                    )
                );
                $academy_fired = true;
            }

            $tags_applied      = array();
            $raw_tag_setting   = get_option('jecm_new_student_tag_ids', '');
            $contact_id_apply  = absint($contact_id);
            if ($raw_tag_setting !== '' && $contact_id_apply > 0) {
                $default_tag_ids = array_filter(array_map('intval', explode(',', (string) $raw_tag_setting)));
                foreach ($default_tag_ids as $tid) {
                    if ($tid > 0) {
                        $tag_res = $keap->apply_tag($contact_id_apply, $tid);
                        if (!empty($tag_res['success'])) {
                            $tags_applied[] = $tid;
                        }
                    }
                }
            }

            JECM_Logger::log_action(array(
                'action'       => 'add_to_keap',
                'email'        => $email,
                'contact_id'   => $contact_id,
                'contact_name' => $full_name,
                'details'      => 'Created Keap contact for ' . $email
                    . ($academy_fired ? ' · Flowmattic Academy webhook fired' : '')
                    . (!empty($tags_applied) ? ' · Tags applied: ' . implode(', ', $tags_applied) : ''),
                'keap_success' => 1,
            ));
            wp_send_json_success(
                array(
                    'message'       => $academy_fired
                        ? __('Contact created in Keap and sent to Jazzedge Academy.', 'je-keap-manager')
                        : __('Contact created in Keap.', 'je-keap-manager'),
                    'contact_id'    => $contact_id,
                    'academy_fired' => $academy_fired,
                )
            );
        } else {
            wp_send_json_error(array('message' => $result['error'] ?? __('Failed to create contact.', 'je-keap-manager')));
        }
    }

    public function ajax_dismiss_order() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'je-keap-manager')));
        }
        $order_id = isset($_POST['order_id']) ? sanitize_text_field(wp_unslash($_POST['order_id'])) : '';
        if ($order_id === '') {
            wp_send_json_error(array('message' => __('Missing order ID.', 'je-keap-manager')));
        }
        $nonce_in = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce_in, 'jecm_dismiss_order_' . $order_id)) {
            wp_send_json_error(array('message' => __('Invalid nonce.', 'je-keap-manager')));
        }
        $dismissed   = array_map('strval', (array) get_option('jecm_dismissed_balance_orders', array()));
        $dismissed[] = $order_id;
        update_option('jecm_dismissed_balance_orders', array_values(array_unique($dismissed)));
        delete_transient('jecm_balance_due_count_' . get_current_user_id());
        wp_send_json_success(array('message' => __('Order dismissed.', 'je-keap-manager')));
    }

    public function ajax_delete_order() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'je-keap-manager')));
        }
        $order_id = absint($_POST['order_id'] ?? 0);
        if (!$order_id) {
            wp_send_json_error(array('message' => __('Missing order ID.', 'je-keap-manager')));
        }
        if (
            !wp_verify_nonce(
                isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '',
                'jecm_delete_order_' . $order_id
            )
        ) {
            wp_send_json_error(array('message' => __('Invalid nonce.', 'je-keap-manager')));
        }
        $api_key = get_option('jecm_keap_api_key', '');
        if ($api_key === '') {
            wp_send_json_error(array('message' => __('No API key configured.', 'je-keap-manager')));
        }
        $keap   = new JECM_Keap_API($api_key);
        $result = $keap->delete_order($order_id);

        if (!empty($result['success'])) {
            delete_transient($this->jecm_find_student_cache_key());
            $log_email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
            $log_cid   = absint($_POST['contact_id'] ?? 0);
            $log_row   = array(
                'action'       => 'delete_order',
                'email'        => $log_email,
                'details'      => 'Deleted Keap order ID ' . $order_id,
                'keap_success' => 1,
            );
            if ($log_cid > 0) {
                $log_row['contact_id'] = $log_cid;
            }
            JECM_Logger::log_action($log_row);
            wp_send_json_success(array('message' => __('Order deleted.', 'je-keap-manager')));
        }
        wp_send_json_error(array('message' => $result['error'] ?? __('Failed to delete order.', 'je-keap-manager')));
    }

    public function ajax_cancel_sub() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'jecm_cancel_sub')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        $api_key = get_option('jecm_keap_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'No Keap API key configured.'));
        }

        $contact_id      = absint($_POST['contact_id'] ?? 0);
        $subscription_id = absint($_POST['subscription_id'] ?? 0);
        $product_name    = sanitize_text_field(wp_unslash($_POST['product_name'] ?? ''));
        $billing_cycle   = sanitize_text_field(wp_unslash($_POST['billing_cycle'] ?? ''));
        $cancel_reason   = sanitize_text_field(wp_unslash($_POST['cancel_reason'] ?? ''));
        $email           = sanitize_email(wp_unslash($_POST['email'] ?? ''));

        if (!$contact_id || !$subscription_id || !$email) {
            wp_send_json_error(array('message' => 'Missing contact, subscription, or email.'));
        }

        if (!in_array($cancel_reason, array('customer_cancel', 'payment_failed'), true)) {
            wp_send_json_error(array('message' => 'Invalid cancel reason.'));
        }

        $bc_upper = strtoupper(trim((string) $billing_cycle));
        if ($bc_upper === 'MONTH') {
            $is_monthly = true;
        } else {
            $is_monthly = false;
            if ($bc_upper === '' && $product_name !== '') {
                $pn = strtoupper($product_name);
                if (strpos($pn, 'MONTH') !== false || strpos($pn, 'HSP_MO') !== false || strpos($pn, '_MO') !== false) {
                    $is_monthly = true;
                }
                if (strpos($pn, 'YEAR') !== false || strpos($pn, '1YR') !== false || strpos($pn, 'ANNUAL') !== false) {
                    $is_monthly = false;
                }
            }
        }

        $is_hsp = (stripos($product_name, 'HSP') !== false);
        $group  = $is_hsp
            ? ($is_monthly ? 'hsp_monthly' : 'hsp_yearly')
            : ($is_monthly ? 'academy_monthly' : 'academy_yearly');

        $keap = new JECM_Keap_API($api_key);

        $results = $this->process_cancellation(
            $keap,
            array(
                'contact_id'            => $contact_id,
                'email'                 => $email,
                'memberships_to_cancel' => array($group),
                'cancel_reason'         => $cancel_reason,
                'subscription_id'       => $subscription_id,
                'subscription_label'    => $product_name,
            )
        );

        wp_send_json_success(array(
            'results'    => $results,
            'contact_id' => $contact_id,
        ));
    }

    public function ajax_update_expiry() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'jecm_update_expiry')) {
            wp_send_json_error(array('message' => 'Invalid nonce.'));
        }

        $contact_id = absint($_POST['contact_id'] ?? 0);
        $field      = sanitize_text_field(wp_unslash($_POST['field'] ?? ''));
        $date_in    = isset($_POST['date']) ? sanitize_text_field(wp_unslash($_POST['date'])) : '';
        $email      = sanitize_email(wp_unslash($_POST['email'] ?? ''));

        if (!$contact_id) {
            wp_send_json_error(array('message' => 'Missing contact.'));
        }

        $allowed = array('AcademyExpirationDate', 'HSPExpirationDate');
        if (!in_array($field, $allowed, true)) {
            wp_send_json_error(array('message' => 'Invalid field.'));
        }

        $api_key = get_option('jecm_keap_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'No Keap API key configured.'));
        }

        if ($email === '') {
            $c_resp = wp_remote_get(
                'https://api.infusionsoft.com/crm/rest/v1/contacts/' . intval($contact_id),
                array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $api_key,
                        'Accept'        => 'application/json',
                    ),
                    'timeout' => 10,
                )
            );
            if (!is_wp_error($c_resp)) {
                $c_body = json_decode(wp_remote_retrieve_body($c_resp), true);
                if (is_array($c_body) && !empty($c_body['email_addresses'][0]['email'])) {
                    $email = sanitize_email($c_body['email_addresses'][0]['email']);
                }
            }
        }

        if ($date_in === '') {
            $payload_value = '';
            $display       = '—';
        } else {
            $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone('UTC');
            $dt = DateTime::createFromFormat('m/d/Y', $date_in, $tz);
            if ($dt === false) {
                wp_send_json_error(array('message' => 'Invalid date format.'));
            }
            $dt->setTime(0, 0, 0);
            $iso_dt        = clone $dt;
            $iso_dt->setTimezone(new DateTimeZone('UTC'));
            $payload_value = $iso_dt->format('Y-m-d\T00:00:00.000\Z');
            $display       = $dt->format('F j, Y');
        }

        $keap   = new JECM_Keap_API($api_key);
        $result = $keap->update_custom_fields($contact_id, array('_' . $field => $payload_value));

        if (empty($result['success'])) {
            wp_send_json_error(array('message' => $result['message'] ?? 'Update failed.'));
        }

        $contact_name = '';
        try {
            $contact_name = $this->get_keap_contact_display_name($contact_id);
        } catch (\Throwable $e) {
            $contact_name = '';
        }

        JECM_Logger::log_action(array(
            'action'       => 'update_expiry',
            'email'        => $email ?: '',
            'contact_id'   => $contact_id,
            'contact_name' => $contact_name,
            'keap_success' => 1,
            'fc_success'   => null,
            'details'      => array(
                'field'    => $field,
                'new_date' => $date_in,
            ),
        ));

        wp_send_json_success(array('display' => $display));
    }

    public function ajax_add_task() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'je-keap-manager')));
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'jecm_add_task')) {
            wp_send_json_error(array('message' => __('Invalid session. Reload and try again.', 'je-keap-manager')));
        }

        $email        = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $contact_id   = absint($_POST['contact_id'] ?? 0);
        $student_name = sanitize_text_field(wp_unslash($_POST['student_name'] ?? ''));
        $notes        = isset($_POST['notes']) ? wp_unslash($_POST['notes']) : '';

        if ($email === '') {
            wp_send_json_error(array('message' => __('Missing email.', 'je-keap-manager')));
        }

        $id = JECM_Tasks::insert_open_task($email, $contact_id, $student_name, $notes);
        if ($id < 1) {
            wp_send_json_error(array('message' => __('Could not save task. Add a note in the text area.', 'je-keap-manager')));
        }

        wp_send_json_success(array('task_id' => $id));
    }

    public function render_page() {
        $tab = $this->current_tab();

        // Visiting Balance Due forces a fresh badge count (nav is rendered before tab body runs).
        if ($tab === 'balance') {
            delete_transient('jecm_balance_due_count_' . get_current_user_id());
        }

        // Cache balance-due count for badge (5 min TTL, default date range).
        $jecm_balance_badge_key   = 'jecm_balance_due_count_' . get_current_user_id();
        $jecm_balance_badge_count = get_transient($jecm_balance_badge_key);
        if ($jecm_balance_badge_count === false) {
            $bk = get_option('jecm_keap_api_key');
            if ($bk) {
                $bk_api      = new JECM_Keap_API($bk);
                $bk_orders = $bk_api->get_balance_due_orders(gmdate('Y-m-01'), gmdate('Y-12-31'));
                if (is_array($bk_orders)) {
                    $bk_dismissed = (array) get_option('jecm_dismissed_balance_orders', array());
                    $bk_visible   = array_filter(
                        $bk_orders,
                        static function ($o) use ($bk_dismissed) {
                            $oid = (string) ($o['id'] ?? $o['order_id'] ?? '');

                            return $oid === '' || !in_array($oid, $bk_dismissed, true);
                        }
                    );
                    $jecm_balance_badge_count = count($bk_visible);
                } else {
                    $jecm_balance_badge_count = 0;
                }
                set_transient($jecm_balance_badge_key, $jecm_balance_badge_count, 5 * MINUTE_IN_SECONDS);
            } else {
                $jecm_balance_badge_count = 0;
            }
        }

        $jecm_cached_fst_for_nav = get_transient($this->jecm_find_student_cache_key());
        $jecm_find_tab_text      = __('Find Student', 'je-keap-manager');
        if (is_array($jecm_cached_fst_for_nav) && !empty($jecm_cached_fst_for_nav['contact']) && is_array($jecm_cached_fst_for_nav['contact'])) {
            $jecm_fst_gn = trim((string) ($jecm_cached_fst_for_nav['contact']['given_name'] ?? ''));
            $jecm_fst_fn = trim((string) ($jecm_cached_fst_for_nav['contact']['family_name'] ?? ''));
            if ($jecm_fst_gn !== '' || $jecm_fst_fn !== '') {
                $jecm_fst_nm = trim($jecm_fst_gn . ' ' . $jecm_fst_fn);
                $jecm_find_tab_text = $jecm_fst_nm !== '' ? $jecm_fst_nm : (string) ($jecm_cached_fst_for_nav['email'] ?? $jecm_find_tab_text);
            }
        }

        settings_errors('jecm');
        ?>
        <div class="wrap">
            <h1>Keap Manage</h1>

            <nav class="nav-tab-wrapper" style="margin-bottom:20px;">
                <a href="<?php echo esc_url($this->tab_url('find')); ?>"
                   class="nav-tab <?php echo $tab === 'find' ? 'nav-tab-active' : ''; ?>"
                   style="max-width:12em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    🔍 <?php echo esc_html($jecm_find_tab_text); ?>
                </a>
                <a href="<?php echo esc_url($this->tasks_tab_url()); ?>"
                   class="nav-tab <?php echo $tab === 'tasks' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Tasks', 'je-keap-manager'); ?>
                    <?php
                    $jecm_open_n = JECM_Tasks::count_open();
                    if ($jecm_open_n > 0) {
                        echo ' <span class="jecm-tasks-nav-badge" style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 7px;margin-left:6px;font-size:11px;font-weight:700;line-height:1;letter-spacing:-0.02em;color:#fff;background:#d63638;border-radius:999px;box-shadow:0 1px 2px rgba(0,0,0,0.15);vertical-align:2px;" title="' . esc_attr__('Open tasks', 'je-keap-manager') . '">' . esc_html((string) (int) $jecm_open_n) . '</span>';
                    }
                    ?>
                </a>
                <a href="<?php echo esc_url($this->tab_url('settings')); ?>"
                   class="nav-tab <?php echo $tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    ⚙️ Settings
                </a>
                <a href="<?php echo esc_url($this->tab_url('log')); ?>"
                   class="nav-tab <?php echo $tab === 'log' ? 'nav-tab-active' : ''; ?>">
                    📋 Audit Log
                </a>
                <a href="<?php echo esc_url($this->tab_url('balance')); ?>"
                   class="nav-tab <?php echo $tab === 'balance' ? 'nav-tab-active' : ''; ?>">
                    💰 <?php esc_html_e('Balance Due', 'je-keap-manager'); ?>
                    <?php if ((int) $jecm_balance_badge_count > 0) : ?>
                        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 7px;margin-left:6px;font-size:11px;font-weight:700;line-height:1;color:#fff;background:#d63638;border-radius:999px;box-shadow:0 1px 2px rgba(0,0,0,0.15);vertical-align:2px;"
                              title="<?php echo esc_attr__('Orders with balance due (cached)', 'je-keap-manager'); ?>">
                            <?php echo (int) $jecm_balance_badge_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo esc_url($this->tab_url('webhook')); ?>"
                   class="nav-tab <?php echo $tab === 'webhook' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Webhook', 'je-keap-manager'); ?>
                </a>
                <?php /* Test tab hidden from nav; still available at ?tab=test for direct access. */ ?>
            </nav>

            <?php if ($tab === 'find'): ?>
                <?php $this->render_find_tab(); ?>

            <?php elseif ($tab === 'tasks'): ?>
                <?php $this->render_tasks_tab(); ?>

            <?php elseif ($tab === 'cancel'): ?>
                <?php
                $step         = isset($_POST['jecm_step']) ? (int) $_POST['jecm_step'] : 1;
                $lookup_data  = null;
                $result       = null;
                $lookup_error = '';

                $api_key = get_option('jecm_keap_api_key');
                $keap    = new JECM_Keap_API($api_key);

                // Step 1 submitted — look up student
                if ($step === 1 && isset($_POST['jecm_lookup_nonce']) && wp_verify_nonce($_POST['jecm_lookup_nonce'], 'jecm_lookup')) {
                    $email = sanitize_email($_POST['jecm_email'] ?? '');
                    if (!$email) {
                        $lookup_error = 'Please enter a valid email address.';
                    } elseif (!$api_key) {
                        $lookup_error = 'No Keap API key configured — go to Settings tab.';
                    } else {
                        $lookup_data = $this->lookup_student($email, $keap);
                        if (!$lookup_data['success']) {
                            $lookup_error = $lookup_data['message'];
                            $lookup_data  = null;
                        }
                    }
                    if ($lookup_data) {
                        $step = 2;
                    } else {
                        $step = 1;
                    }
                }

                // Step 2 submitted — process cancellation
                if ($step === 2 && isset($_POST['jecm_process_nonce']) && wp_verify_nonce($_POST['jecm_process_nonce'], 'jecm_process')) {
                    $result = $this->process_cancellation($keap);
                    $step   = 3;
                }
                ?>

                <div class="card" style="max-width:700px; padding:20px;">
                    <h2 style="margin-top:0;">Process Cancellation</h2>

                    <?php if (!empty($lookup_error)): ?>
                        <div class="notice notice-error is-dismissible"><p><?php echo esc_html($lookup_error); ?></p></div>
                    <?php endif; ?>

                    <?php if ($step === 1): ?>
                    <!-- STEP 1: Email lookup -->
                    <form method="post">
                        <?php wp_nonce_field('jecm_lookup', 'jecm_lookup_nonce'); ?>
                        <input type="hidden" name="jecm_step" value="1">
                        <table class="form-table">
                            <tr>
                                <th><label for="jecm_email">Student Email</label></th>
                                <td>
                                    <input type="email" id="jecm_email" name="jecm_email" required style="width:300px;"
                                        value="<?php echo esc_attr($_POST['jecm_email'] ?? ''); ?>">
                                </td>
                            </tr>
                        </table>
                        <p><input type="submit" class="button button-primary" value="Look Up Student →"></p>
                    </form>

                    <?php elseif ($step === 2 && $lookup_data): ?>
                    <!-- STEP 2: Show active memberships + cancel checkboxes -->

                    <?php
                    $last_payment = $lookup_data['last_payment'];
                    $last_ts      = $last_payment['success'] ? strtotime($last_payment['raw']) : null;
                    ?>

                    <div style="background:#f6f7f7; border:1px solid #dcdcde; border-radius:4px; padding:14px 16px; margin-bottom:16px;">
                        <strong><?php echo esc_html($lookup_data['first_name'] . ' ' . $lookup_data['last_name']); ?></strong>
                        &nbsp;—&nbsp; <?php echo esc_html($lookup_data['email']); ?>
                        &nbsp;—&nbsp; Keap ID: <?php echo esc_html($lookup_data['contact_id']); ?>
                        <?php if ($last_payment['success']): ?>
                            &nbsp;—&nbsp; Last payment: <strong><?php echo esc_html($last_payment['date']); ?></strong>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($lookup_data['active_memberships'])): ?>
                        <div class="notice notice-warning"><p>No active memberships found based on configured tag IDs.</p></div>
                    <?php else: ?>

                    <form method="post">
                        <?php wp_nonce_field('jecm_process', 'jecm_process_nonce'); ?>
                        <input type="hidden" name="jecm_step"       value="2">
                        <input type="hidden" name="jecm_email"      value="<?php echo esc_attr($lookup_data['email']); ?>">
                        <input type="hidden" name="jecm_contact_id" value="<?php echo esc_attr($lookup_data['contact_id']); ?>">
                        <input type="hidden" name="jecm_cancel_reason" value="<?php echo esc_attr($_POST['jecm_cancel_reason'] ?? 'requested'); ?>">

                        <h3 style="margin-bottom:8px;">Active Memberships</h3>
                        <table class="widefat" style="margin-bottom:16px;">
                            <thead>
                                <tr><th style="width:40px;">Cancel</th><th>Membership</th><th>Type</th><th>Est. End Date</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($lookup_data['active_memberships'] as $i => $mem):
                                $is_monthly = in_array($mem['group'], array('academy_monthly', 'hsp_monthly'), true);
                                $days       = $is_monthly ? 30 : 365;
                                $end_date   = $last_ts ? date('M j, Y', strtotime('+' . $days . ' days', $last_ts)) : '—';
                                $group_type = str_replace('_', ' ', ucwords($mem['group'], '_'));
                            ?>
                                <tr>
                                    <td style="text-align:center;">
                                        <input type="checkbox" name="jecm_cancel_memberships[]"
                                               value="<?php echo esc_attr($mem['group']); ?>"
                                               id="mem_<?php echo (int) $i; ?>" checked>
                                    </td>
                                    <td><label for="mem_<?php echo (int) $i; ?>"><?php echo esc_html($mem['label']); ?></label></td>
                                    <td><?php echo esc_html($group_type); ?></td>
                                    <td><?php echo esc_html($end_date); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                        <table class="form-table">
                            <tr>
                                <th>Cancel Reason</th>
                                <td>
                                    <label style="margin-right:20px;">
                                        <input type="radio" name="jecm_cancel_reason" value="requested"
                                            <?php checked(($_POST['jecm_cancel_reason'] ?? 'requested'), 'requested'); ?>>
                                        They asked to cancel
                                    </label>
                                    <label>
                                        <input type="radio" name="jecm_cancel_reason" value="payment_failed"
                                            <?php checked(($_POST['jecm_cancel_reason'] ?? ''), 'payment_failed'); ?>>
                                        Payment Failed
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th>Other</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="jecm_cancel_other" value="1" id="jecm_other_chk"
                                            <?php checked(!empty($_POST['jecm_cancel_other'])); ?>>
                                        Log additional note
                                    </label>
                                    <div id="jecm_other_note_wrap" style="margin-top:8px; display:<?php echo !empty($_POST['jecm_cancel_other']) ? 'block' : 'none'; ?>;">
                                        <input type="text" name="jecm_other_note" maxlength="50"
                                            placeholder="Short note (50 chars max)"
                                            value="<?php echo esc_attr($_POST['jecm_other_note'] ?? ''); ?>"
                                            style="width:280px;">
                                    </div>
                                    <script>
                                    document.getElementById('jecm_other_chk').addEventListener('change', function(){
                                        document.getElementById('jecm_other_note_wrap').style.display = this.checked ? 'block' : 'none';
                                    });
                                    </script>
                                </td>
                            </tr>
                        </table>

                        <p>
                            <a href="<?php echo esc_url($this->tab_url('cancel')); ?>" class="button">← Start Over</a>
                            &nbsp;
                            <input type="submit" name="jecm_process" class="button button-primary"
                                   style="background:#d63638; border-color:#d63638;" value="Cancel Selected Memberships">
                        </p>
                    </form>
                    <?php endif; ?>

                    <?php elseif ($step === 3 && $result !== null): ?>
                    <!-- STEP 3: Results -->
                    <h3>Results for <?php echo esc_html($_POST['jecm_email'] ?? ''); ?></h3>
                    <table class="widefat" style="margin-bottom:16px;">
                        <thead><tr><th>Action</th><th>Result</th></tr></thead>
                        <tbody>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td><?php echo esc_html($row['label']); ?></td>
                                <td><?php echo $row['success'] ? '✅' : '❌'; ?> <?php echo esc_html($row['message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="button" id="jecm_copy_json" class="button" style="margin-bottom:12px;">
                        📋 Copy Results as JSON
                    </button>
                    <span id="jecm_copy_json_confirm" style="display:none; color:#1a7f37; font-weight:600; margin-left:8px;">Copied!</span>
                    <script>
                    document.getElementById('jecm_copy_json').addEventListener('click', function() {
                        var data = <?php echo wp_json_encode($result, JSON_PRETTY_PRINT); ?>;
                        navigator.clipboard.writeText(JSON.stringify(data, null, 2)).then(function() {
                            var c = document.getElementById('jecm_copy_json_confirm');
                            c.style.display = 'inline';
                            setTimeout(function(){ c.style.display = 'none'; }, 2500);
                        });
                    });
                    </script>
                    <p><a href="<?php echo esc_url($this->tab_url('cancel')); ?>" class="button">← Process Another</a></p>

                    <?php endif; ?>
                </div>

            <?php elseif ($tab === 'settings'): ?>
                <?php
                $settings_autologin_sites = $this->jecm_get_autologin_sites();
                $settings_autologin_dest  = $this->jecm_get_autologin_destinations();
                $jecm_settings_grid       = 'display:grid; grid-template-columns:minmax(160px, 240px) minmax(0, 1fr); gap:16px 20px; align-items:center;';
                $jecm_settings_grid_tight  = 'display:grid; grid-template-columns:minmax(120px, 42%) minmax(0, 1fr); gap:14px 16px; align-items:center;';
                $jecm_pb_header_bg        = 'background:#f6f7f7;border-bottom:1px solid #dcdcde;';
                $jecm_pb_h2                = 'text-align:center;margin:0;padding:12px 16px;font-size:13px;font-weight:600;line-height:1.35;color:#1d2327;border:none;';
                $jecm_pb_inside            = 'padding:20px 22px;margin:0 !important;';
                ?>
                <div class="jecm-settings-wrap" style="max-width:1100px;">
                    <style>
                        .jecm-settings-wrap .postbox .inside input.regular-text,
                        .jecm-settings-wrap .postbox .inside input[type="password"],
                        .jecm-settings-wrap .postbox .inside input[type="number"],
                        .jecm-settings-wrap .postbox .inside input[type="text"] {
                            padding: 8px 12px;
                            min-height: 38px;
                            box-sizing: border-box;
                            vertical-align: middle;
                        }
                        .jecm-settings-wrap .postbox .inside .description {
                            margin-top: 8px;
                            margin-bottom: 0;
                            line-height: 1.45;
                        }
                        .jecm-settings-wrap .postbox .postbox-header .hndle {
                            cursor: default;
                        }
                    </style>
                    <h2 style="margin-top:0;"><?php esc_html_e('Settings', 'je-keap-manager'); ?></h2>
                    <form method="post" id="jecm-settings-form">
                        <?php wp_nonce_field('jecm_settings', 'jecm_nonce'); ?>
                        <input type="hidden" name="jecm_autologin_destinations" id="jecm-autologin-destinations-field" value="">
                        <input type="hidden" name="jecm_autologin_sites" id="jecm-autologin-sites-field" value="">

                        <div class="postbox" style="margin-bottom:20px;">
                            <div class="postbox-header" style="<?php echo esc_attr($jecm_pb_header_bg); ?>">
                                <h2 class="hndle" style="<?php echo esc_attr($jecm_pb_h2); ?>"><?php esc_html_e('Keap API', 'je-keap-manager'); ?></h2>
                            </div>
                            <div class="inside" style="<?php echo esc_attr($jecm_pb_inside); ?>">
                                <div style="<?php echo esc_attr($jecm_settings_grid); ?>">
                                    <label for="jecm-keap-api-key" style="font-weight:600; align-self:start; padding-top:10px;"><?php esc_html_e('Keap API Key (SAK)', 'je-keap-manager'); ?></label>
                                    <div>
                                        <input id="jecm-keap-api-key" type="password" name="jecm_keap_api_key"
                                            value="<?php echo esc_attr(get_option('jecm_keap_api_key')); ?>"
                                            style="width:100%; max-width:100%;" class="regular-text">
                                        <p class="description"><?php esc_html_e('Service Account Key from Keap → Settings → Integrations → Service Account Keys', 'je-keap-manager'); ?></p>
                                    </div>
                                </div>
                                <p style="margin:20px 0 0; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                                    <input type="submit" name="jecm_test_api_key"
                                        class="button button-secondary"
                                        value="<?php esc_attr_e('Test SAK (REST)', 'je-keap-manager'); ?>">
                                </p>
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(2,minmax(280px,1fr));gap:20px;align-items:start;margin-bottom:20px;">
                            <div class="postbox" style="margin:0;">
                                <div class="postbox-header" style="<?php echo esc_attr($jecm_pb_header_bg); ?>">
                                    <h2 class="hndle" style="<?php echo esc_attr($jecm_pb_h2); ?>"><?php esc_html_e('Keap Cancel Tags', 'je-keap-manager'); ?></h2>
                                </div>
                                <div class="inside" style="<?php echo esc_attr($jecm_pb_inside); ?>">
                                    <div style="<?php echo esc_attr($jecm_settings_grid_tight); ?>">
                                        <label for="jecm-keap-academy-tag" style="font-weight:600;"><?php esc_html_e('Academy Cancel Tag ID', 'je-keap-manager'); ?></label>
                                        <div><input id="jecm-keap-academy-tag" type="number" name="jecm_keap_academy_tag_id"
                                            value="<?php echo esc_attr(get_option('jecm_keap_academy_tag_id', '10316')); ?>"
                                            style="width:120px; max-width:100%;"></div>

                                        <label for="jecm-keap-hsp-tag" style="font-weight:600; padding-top:6px;"><?php esc_html_e('HSP Cancel Tag ID', 'je-keap-manager'); ?></label>
                                        <div><input id="jecm-keap-hsp-tag" type="number" name="jecm_keap_hsp_tag_id"
                                            value="<?php echo esc_attr(get_option('jecm_keap_hsp_tag_id', '10318')); ?>"
                                            style="width:120px; max-width:100%;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="postbox" style="margin:0;">
                                <div class="postbox-header" style="<?php echo esc_attr($jecm_pb_header_bg); ?>">
                                    <h2 class="hndle" style="<?php echo esc_attr($jecm_pb_h2); ?>"><?php esc_html_e('FluentCRM Tags', 'je-keap-manager'); ?></h2>
                                </div>
                                <div class="inside" style="<?php echo esc_attr($jecm_pb_inside); ?>">
                                    <div style="<?php echo esc_attr($jecm_settings_grid_tight); ?>">
                                        <label for="jecm-fc-academy-tag" style="font-weight:600;"><?php esc_html_e('Academy Cancel', 'je-keap-manager'); ?></label>
                                        <div><input id="jecm-fc-academy-tag" type="number" name="jecm_fc_academy_tag_id"
                                            value="<?php echo esc_attr(get_option('jecm_fc_academy_tag_id', '153')); ?>"
                                            style="width:120px; max-width:100%;"></div>

                                        <label for="jecm-fc-hsp-tag" style="font-weight:600;"><?php esc_html_e('HSP Cancel', 'je-keap-manager'); ?></label>
                                        <div><input id="jecm-fc-hsp-tag" type="number" name="jecm_fc_hsp_tag_id"
                                            value="<?php echo esc_attr(get_option('jecm_fc_hsp_tag_id', '154')); ?>"
                                            style="width:120px; max-width:100%;"></div>

                                        <label for="jecm-fc-payment-failed-tag" style="font-weight:600;"><?php esc_html_e('Payment Failed — Cancelled (tag 155)', 'je-keap-manager'); ?></label>
                                        <div>
                                            <input id="jecm-fc-payment-failed-tag" type="text" name="jecm_fc_payment_failed_tag_id"
                                                value="<?php echo esc_attr(get_option('jecm_fc_payment_failed_tag_id', '')); ?>"
                                                style="width:200px; max-width:100%;" placeholder="<?php esc_attr_e('e.g. 155', 'je-keap-manager'); ?>" class="regular-text">
                                            <p class="description"><?php esc_html_e('Applied when cancellation reason is Payment Failed. Cancels the membership.', 'je-keap-manager'); ?></p>
                                        </div>

                                        <label for="jecm-fc-payment-failed-reachout-tag" style="font-weight:600;"><?php esc_html_e('Payment Failed — Reach Out (tag 156)', 'je-keap-manager'); ?></label>
                                        <div>
                                            <input id="jecm-fc-payment-failed-reachout-tag" type="text" name="jecm_fc_payment_failed_reachout_tag_id"
                                                value="<?php echo esc_attr(get_option('jecm_fc_payment_failed_reachout_tag_id', '')); ?>"
                                                style="width:200px; max-width:100%;" placeholder="<?php esc_attr_e('e.g. 156', 'je-keap-manager'); ?>" class="regular-text">
                                            <p class="description"><?php esc_html_e('Applied when a student has an unpaid payment but has NOT been cancelled yet. Triggers win-back outreach.', 'je-keap-manager'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="postbox" style="margin-bottom:20px;">
                            <div class="postbox-header" style="<?php echo esc_attr($jecm_pb_header_bg); ?>">
                                <h2 class="hndle" style="<?php echo esc_attr($jecm_pb_h2); ?>"><?php esc_html_e('Membership Tag IDs', 'je-keap-manager'); ?></h2>
                            </div>
                            <div class="inside" style="<?php echo esc_attr($jecm_pb_inside); ?>">
                                <div style="display:grid;grid-template-columns:repeat(2,minmax(240px,1fr));gap:28px 32px;align-items:start;">
                                    <div>
                                        <p class="description" style="margin:0 0 14px;font-weight:600;font-size:13px;color:#1d2327;"><?php esc_html_e('Academy', 'je-keap-manager'); ?></p>
                                        <div style="<?php echo esc_attr($jecm_settings_grid_tight); ?>">
                                            <label for="jecm-monthly-academy-tags" style="font-weight:600;"><?php esc_html_e('Monthly', 'je-keap-manager'); ?></label>
                                            <div>
                                                <input id="jecm-monthly-academy-tags" type="text" name="jecm_keap_monthly_academy_tags"
                                                    value="<?php echo esc_attr(get_option('jecm_keap_monthly_academy_tags', '')); ?>"
                                                    style="width:100%; max-width:100%;" placeholder="<?php esc_attr_e('e.g. 123,456', 'je-keap-manager'); ?>" class="regular-text">
                                                <p class="description"><?php esc_html_e('Comma-separated Keap tag IDs for monthly Academy membership.', 'je-keap-manager'); ?></p>
                                            </div>

                                            <label for="jecm-yearly-academy-tags" style="font-weight:600;"><?php esc_html_e('Yearly', 'je-keap-manager'); ?></label>
                                            <div>
                                                <input id="jecm-yearly-academy-tags" type="text" name="jecm_keap_yearly_academy_tags"
                                                    value="<?php echo esc_attr(get_option('jecm_keap_yearly_academy_tags', '')); ?>"
                                                    style="width:100%; max-width:100%;" placeholder="<?php esc_attr_e('e.g. 789,101', 'je-keap-manager'); ?>" class="regular-text">
                                                <p class="description"><?php esc_html_e('Comma-separated Keap tag IDs for yearly Academy membership.', 'je-keap-manager'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="description" style="margin:0 0 14px;font-weight:600;font-size:13px;color:#1d2327;"><?php esc_html_e('HSP', 'je-keap-manager'); ?></p>
                                        <div style="<?php echo esc_attr($jecm_settings_grid_tight); ?>">
                                            <label for="jecm-monthly-hsp-tags" style="font-weight:600;"><?php esc_html_e('Monthly', 'je-keap-manager'); ?></label>
                                            <div>
                                                <input id="jecm-monthly-hsp-tags" type="text" name="jecm_keap_monthly_hsp_tags"
                                                    value="<?php echo esc_attr(get_option('jecm_keap_monthly_hsp_tags', '')); ?>"
                                                    style="width:100%; max-width:100%;" placeholder="<?php esc_attr_e('e.g. 112,113', 'je-keap-manager'); ?>" class="regular-text">
                                                <p class="description"><?php esc_html_e('Comma-separated Keap tag IDs for monthly HSP membership.', 'je-keap-manager'); ?></p>
                                            </div>

                                            <label for="jecm-yearly-hsp-tags" style="font-weight:600;"><?php esc_html_e('Yearly', 'je-keap-manager'); ?></label>
                                            <div>
                                                <input id="jecm-yearly-hsp-tags" type="text" name="jecm_keap_yearly_hsp_tags"
                                                    value="<?php echo esc_attr(get_option('jecm_keap_yearly_hsp_tags', '')); ?>"
                                                    style="width:100%; max-width:100%;" placeholder="<?php esc_attr_e('e.g. 114,115', 'je-keap-manager'); ?>" class="regular-text">
                                                <p class="description"><?php esc_html_e('Comma-separated Keap tag IDs for yearly HSP membership.', 'je-keap-manager'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="postbox" style="margin-bottom:20px;">
                            <div class="postbox-header" style="<?php echo esc_attr($jecm_pb_header_bg); ?>">
                                <h2 class="hndle" style="<?php echo esc_attr($jecm_pb_h2); ?>"><?php esc_html_e('🔗 Integrations', 'je-keap-manager'); ?></h2>
                            </div>
                            <div class="inside" style="<?php echo esc_attr($jecm_pb_inside); ?>">
                                <table class="form-table" role="presentation" style="margin:0;">
                                    <tr>
                                        <th scope="row" style="width:220px;">
                                            <label for="jecm_flowmattic_new_user_webhook"><?php esc_html_e('Flowmattic New User Webhook', 'je-keap-manager'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text"
                                                   id="jecm_flowmattic_new_user_webhook"
                                                   name="jecm_flowmattic_new_user_webhook"
                                                   class="regular-text"
                                                   style="width:100%;max-width:52rem;box-sizing:border-box;"
                                                   value="<?php echo esc_attr(get_option('jecm_flowmattic_new_user_webhook', '')); ?>"
                                                   placeholder="https://jazzedge.academy/webhook/capture/..." />
                                            <input type="hidden" name="jecm_flowmattic_webhook_clear" value="0" />
                                            <?php if (get_option('jecm_flowmattic_new_user_webhook', '') !== '') : ?>
                                                <a href="#" style="font-size:11px;color:#b32d2e;margin-left:8px;"
                                                   onclick="this.previousElementSibling.value='1';this.closest('tr').querySelector('input[type=text]').value='';return false;">
                                                    <?php esc_html_e('× Clear', 'je-keap-manager'); ?>
                                                </a>
                                            <?php endif; ?>
                                            <p class="description"><?php esc_html_e('Flowmattic webhook URL that creates a new WordPress user on Jazzedge Academy. Receives: email, fname, lname.', 'je-keap-manager'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" style="width:220px;">
                                            <label for="jecm_new_student_tag_ids"><?php esc_html_e('New Student Tag IDs', 'je-keap-manager'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text"
                                                   id="jecm_new_student_tag_ids"
                                                   name="jecm_new_student_tag_ids"
                                                   class="regular-text"
                                                   value="<?php echo esc_attr(get_option('jecm_new_student_tag_ids', '')); ?>"
                                                   placeholder="123, 456, 789" />
                                            <input type="hidden" name="jecm_new_student_tag_ids_clear" value="0" />
                                            <?php if (get_option('jecm_new_student_tag_ids', '') !== '') : ?>
                                                <a href="#" style="font-size:11px;color:#b32d2e;margin-left:8px;"
                                                   onclick="this.previousElementSibling.value='1';this.closest('tr').querySelector('input[type=text]').value='';return false;">
                                                    <?php esc_html_e('× Clear', 'je-keap-manager'); ?>
                                                </a>
                                            <?php endif; ?>
                                            <p class="description"><?php esc_html_e('Comma-separated Keap tag IDs to apply automatically when a new student is added to Keap.', 'je-keap-manager'); ?></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="postbox" style="margin-bottom:20px;">
                            <div class="postbox-header" style="<?php echo esc_attr($jecm_pb_header_bg); ?>">
                                <h2 class="hndle" style="<?php echo esc_attr($jecm_pb_h2); ?>"><?php esc_html_e('🌐 Sites & Auth Keys', 'je-keap-manager'); ?></h2>
                            </div>
                            <div class="inside" style="<?php echo esc_attr($jecm_pb_inside); ?>">
                                <p class="description" style="margin-top:0;"><?php esc_html_e('MemberMouse autologin: site base URL and auth_key per property. Used by Find Student “Auto Login” copy link.', 'je-keap-manager'); ?></p>
                                <table class="widefat" style="max-width:100%;" id="jecm-autologin-sites-table">
                                    <thead>
                                        <tr>
                                            <th style="width:22%;"><?php esc_html_e('Site Name', 'je-keap-manager'); ?></th>
                                            <th style="width:32%;"><?php esc_html_e('Site URL', 'je-keap-manager'); ?></th>
                                            <th><?php esc_html_e('Auth Key', 'je-keap-manager'); ?></th>
                                            <th style="width:90px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="jecm-autologin-sites-tbody">
                                    <?php foreach ($settings_autologin_sites as $ss) : ?>
                                        <tr class="jecm-autologin-site-row">
                                            <td><input type="text" class="jecm-site-name regular-text" value="<?php echo esc_attr($ss['name']); ?>" style="width:100%;"></td>
                                            <td><input type="url" class="jecm-site-url regular-text" value="<?php echo esc_attr($ss['url']); ?>" style="width:100%;"></td>
                                            <td><input type="text" class="jecm-site-auth-key regular-text" value="<?php echo esc_attr($ss['auth_key']); ?>" style="width:100%;" autocomplete="off"></td>
                                            <td><button type="button" class="button jecm-autologin-site-remove"><?php esc_html_e('Remove', 'je-keap-manager'); ?></button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <p>
                                    <button type="button" class="button" id="jecm-autologin-site-add"><?php esc_html_e('Add Site', 'je-keap-manager'); ?></button>
                                </p>
                            </div>
                        </div>

                        <script>
                        (function() {
                            var tbody = document.getElementById('jecm-autologin-sites-tbody');
                            var form = document.getElementById('jecm-settings-form');
                            var hidden = document.getElementById('jecm-autologin-sites-field');
                            function syncSitesHidden() {
                                if (!hidden || !tbody) {
                                    return;
                                }
                                var rows = tbody.querySelectorAll('.jecm-autologin-site-row');
                                var data = [];
                                rows.forEach(function(tr) {
                                    var n = tr.querySelector('.jecm-site-name');
                                    var u = tr.querySelector('.jecm-site-url');
                                    var k = tr.querySelector('.jecm-site-auth-key');
                                    data.push({
                                        name: n ? n.value.trim() : '',
                                        url: u ? u.value.trim() : '',
                                        auth_key: k ? k.value.trim() : ''
                                    });
                                });
                                hidden.value = JSON.stringify(data);
                            }
                            var addBtn = document.getElementById('jecm-autologin-site-add');
                            if (addBtn && tbody) {
                                addBtn.addEventListener('click', function() {
                                    var tr = document.createElement('tr');
                                    tr.className = 'jecm-autologin-site-row';
                                    tr.innerHTML = '<td><input type="text" class="jecm-site-name regular-text" value="" style="width:100%;"></td>' +
                                        '<td><input type="url" class="jecm-site-url regular-text" value="" style="width:100%;"></td>' +
                                        '<td><input type="text" class="jecm-site-auth-key regular-text" value="" style="width:100%;" autocomplete="off"></td>' +
                                        '<td><button type="button" class="button jecm-autologin-site-remove">Remove</button></td>';
                                    tbody.appendChild(tr);
                                });
                            }
                            if (tbody) {
                                tbody.addEventListener('click', function(ev) {
                                    if (!ev.target.closest('.jecm-autologin-site-remove')) {
                                        return;
                                    }
                                    var tr = ev.target.closest('.jecm-autologin-site-row');
                                    if (tr && tr.parentNode) {
                                        tr.parentNode.removeChild(tr);
                                    }
                                });
                            }
                            if (form) {
                                form.addEventListener('submit', function() {
                                    syncSitesHidden();
                                });
                            }
                        })();
                        </script>

                        <div class="postbox" style="margin-bottom:20px;">
                            <div class="postbox-header" style="<?php echo esc_attr($jecm_pb_header_bg); ?>">
                                <h2 class="hndle" style="<?php echo esc_attr($jecm_pb_h2); ?>"><?php esc_html_e('Auto-Login Destinations', 'je-keap-manager'); ?></h2>
                            </div>
                            <div class="inside" style="<?php echo esc_attr($jecm_pb_inside); ?>">
                                <p class="description" style="margin-top:0;"><?php esc_html_e('Titles and paths for Find Student “Auto Login” copy links (MemberMouse redir).', 'je-keap-manager'); ?></p>
                                <table class="widefat" style="max-width:100%;" id="jecm-autologin-dest-table">
                                    <thead>
                                        <tr>
                                            <th style="width:28%;"><?php esc_html_e('Title', 'je-keap-manager'); ?></th>
                                            <th style="width:30%;"><?php esc_html_e('Site', 'je-keap-manager'); ?></th>
                                            <th style="width:100px;"><?php esc_html_e('Tag ID', 'je-keap-manager'); ?></th>
                                            <th><?php esc_html_e('Path', 'je-keap-manager'); ?></th>
                                            <th style="width:90px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="jecm-autologin-dest-tbody">
                                    <?php foreach ($settings_autologin_dest as $sdest) : ?>
                                        <tr class="jecm-autologin-dest-row">
                                            <td><input type="text" class="jecm-autologin-title regular-text" value="<?php echo esc_attr($sdest['title']); ?>" style="width:100%;"></td>
                                            <td>
                                                <select class="jecm-autologin-site-select" style="width:100%;">
                                                    <?php foreach ($settings_autologin_sites as $si => $site) : ?>
                                                        <option value="<?php echo esc_attr((string) $si); ?>" <?php selected((int) ($sdest['site_index'] ?? 0), (int) $si); ?>>
                                                            <?php echo esc_html($site['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="text" class="jecm-autologin-tag-id" value="<?php echo esc_attr(implode(', ', array_map('strval', $sdest['tag_ids'] ?? array()))); ?>" style="width:100%;" placeholder="<?php echo esc_attr__('e.g. 123, 456', 'je-keap-manager'); ?>" autocomplete="off"></td>
                                            <td><input type="text" class="jecm-autologin-path regular-text" value="<?php echo esc_attr($sdest['path']); ?>" style="width:100%;"></td>
                                            <td><button type="button" class="button jecm-autologin-remove"><?php esc_html_e('Remove', 'je-keap-manager'); ?></button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <p>
                                    <button type="button" class="button" id="jecm-autologin-add"><?php esc_html_e('Add Destination', 'je-keap-manager'); ?></button>
                                </p>
                            </div>
                        </div>

                        <?php
                        $jecm_sites_for_dest_js = array();
                        foreach ($settings_autologin_sites as $si => $site_row) {
                            $jecm_sites_for_dest_js[] = array(
                                'index' => (int) $si,
                                'name'  => isset($site_row['name']) ? (string) $site_row['name'] : '',
                            );
                        }
                        ?>
                        <script>var jecmSites = <?php echo wp_json_encode($jecm_sites_for_dest_js); ?>;</script>
                        <script>
                        (function() {
                            var tbody = document.getElementById('jecm-autologin-dest-tbody');
                            var form = document.getElementById('jecm-settings-form');
                            var hidden = document.getElementById('jecm-autologin-destinations-field');
                            function escHtml(v) {
                                var d = document.createElement('div');
                                d.textContent = v == null ? '' : String(v);
                                return d.innerHTML;
                            }
                            function siteSelectHtml(selectedIndex) {
                                var sel = selectedIndex == null || selectedIndex === '' ? 0 : parseInt(String(selectedIndex), 10);
                                if (isNaN(sel)) {
                                    sel = 0;
                                }
                                var html = '<select class="jecm-autologin-site-select" style="width:100%;">';
                                (window.jecmSites || []).forEach(function(s) {
                                    var idx = s && typeof s.index !== 'undefined' ? s.index : 0;
                                    var isSel = parseInt(String(idx), 10) === sel ? ' selected' : '';
                                    html += '<option value="' + String(idx) + '"' + isSel + '>' + escHtml(s ? s.name : '') + '</option>';
                                });
                                html += '</select>';
                                return html;
                            }
                            function syncHidden() {
                                if (!hidden || !tbody) {
                                    return;
                                }
                                var data = [];
                                document.querySelectorAll('#jecm-autologin-dest-tbody .jecm-autologin-dest-row').forEach(function(row) {
                                    var siteSelect = row.querySelector('.jecm-autologin-site-select');
                                    var titleEl = row.querySelector('.jecm-autologin-title');
                                    var pathEl = row.querySelector('.jecm-autologin-path');
                                    var tagEl = row.querySelector('.jecm-autologin-tag-id');
                                    var si = siteSelect ? parseInt(String(siteSelect.value), 10) : 0;
                                    if (isNaN(si)) {
                                        si = 0;
                                    }
                                    var tag_ids = [];
                                    var tagStr = tagEl ? String(tagEl.value || '').trim() : '';
                                    if (tagStr !== '') {
                                        tagStr.split(',').forEach(function(part) {
                                            var n = parseInt(String(part).trim(), 10);
                                            if (!isNaN(n) && n > 0) {
                                                tag_ids.push(n);
                                            }
                                        });
                                        var seen = {};
                                        tag_ids = tag_ids.filter(function(n) {
                                            if (seen[n]) {
                                                return false;
                                            }
                                            seen[n] = true;
                                            return true;
                                        });
                                    }
                                    data.push({
                                        title: titleEl ? titleEl.value.trim() : '',
                                        path: pathEl ? pathEl.value.trim() : '',
                                        site_index: si,
                                        tag_ids: tag_ids
                                    });
                                });
                                hidden.value = JSON.stringify(data);
                            }
                            var destAddBtn = document.getElementById('jecm-autologin-add');
                            if (destAddBtn && tbody) {
                                destAddBtn.addEventListener('click', function() {
                                    var tr = document.createElement('tr');
                                    tr.className = 'jecm-autologin-dest-row';
                                    tr.innerHTML = '<td><input type="text" class="jecm-autologin-title regular-text" value="" style="width:100%;"></td>' +
                                        '<td>' + siteSelectHtml(0) + '</td>' +
                                        '<td><input type="text" class="jecm-autologin-tag-id" value="" style="width:100%;" placeholder="<?php echo esc_js(__('e.g. 123, 456', 'je-keap-manager')); ?>" autocomplete="off"></td>' +
                                        '<td><input type="text" class="jecm-autologin-path regular-text" value="" style="width:100%;"></td>' +
                                        '<td><button type="button" class="button jecm-autologin-remove">Remove</button></td>';
                                    tbody.appendChild(tr);
                                });
                            }
                            if (tbody) {
                                tbody.addEventListener('click', function(ev) {
                                    if (!ev.target.closest('.jecm-autologin-remove')) {
                                        return;
                                    }
                                    var tr = ev.target.closest('.jecm-autologin-dest-row');
                                    if (tr && tr.parentNode) {
                                        tr.parentNode.removeChild(tr);
                                    }
                                });
                                tbody.addEventListener('change', function(ev) {
                                    var t = ev.target;
                                    if (t && t.classList && t.classList.contains('jecm-autologin-site-select')) {
                                        syncHidden();
                                    }
                                });
                            }
                            if (form) {
                                form.addEventListener('submit', function() {
                                    syncHidden();
                                });
                            }
                        })();
                        </script>

                        <p style="margin-top:8px;">
                            <input type="submit" name="jecm_save_settings" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'je-keap-manager'); ?>">
                        </p>
                    </form>
                </div>

            <?php elseif ($tab === 'log'):
                if (
                    isset($_POST['jecm_action']) && $_POST['jecm_action'] === 'purge_log'
                    && isset($_POST['jecm_purge_nonce'])
                    && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['jecm_purge_nonce'])), 'jecm_purge_log')
                    && current_user_can('manage_options')
                ) {
                    global $wpdb;
                    $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'jecm_cancellation_log');
                    echo '<div class="notice notice-success"><p>Audit log purged.</p></div>';
                }
                $filter_action = sanitize_key(wp_unslash($_GET['filter_action'] ?? ''));
                $filter_user   = absint($_GET['filter_user'] ?? 0);
                $per_page      = 25;
                $current_page  = max(1, intval($_GET['paged'] ?? 1));
                $offset        = ($current_page - 1) * $per_page;
                $logs          = JECM_Logger::get_logs($per_page, $offset, $filter_action, $filter_user);
                $total         = JECM_Logger::get_total($filter_action, $filter_user);
                $pages         = $per_page > 0 ? (int) ceil($total / $per_page) : 0;
                $log_users     = JECM_Logger::get_distinct_users();
            ?>
                <div class="card" style="max-width:1100px; padding:20px;">
                    <h2 style="margin-top:0;">Audit Log</h2>
                    <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="margin-bottom:16px;padding:12px;background:#f6f7f7;border:1px solid #c3c4c7;border-radius:4px;display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
                        <input type="hidden" name="page" value="je-keap-manager">
                        <input type="hidden" name="tab" value="log">
                        <label for="jecm-filter-action"><?php esc_html_e('Action', 'je-keap-manager'); ?></label>
                        <select name="filter_action" id="jecm-filter-action">
                            <option value=""><?php esc_html_e('All Actions', 'je-keap-manager'); ?></option>
                            <option value="cancel" <?php selected($filter_action, 'cancel'); ?>><?php esc_html_e('Update Subscription', 'je-keap-manager'); ?></option>
                            <option value="remove_tag" <?php selected($filter_action, 'remove_tag'); ?>><?php esc_html_e('Remove Tag', 'je-keap-manager'); ?></option>
                            <option value="apply_tag" <?php selected($filter_action, 'apply_tag'); ?>><?php esc_html_e('Add Tag', 'je-keap-manager'); ?></option>
                            <option value="update_expiry" <?php selected($filter_action, 'update_expiry'); ?>><?php esc_html_e('Update Expiry', 'je-keap-manager'); ?></option>
                            <option value="task_complete" <?php selected($filter_action, 'task_complete'); ?>><?php esc_html_e('Complete Task', 'je-keap-manager'); ?></option>
                        </select>
                        <label for="jecm-filter-user"><?php esc_html_e('User', 'je-keap-manager'); ?></label>
                        <select name="filter_user" id="jecm-filter-user">
                            <option value="0"><?php esc_html_e('All users', 'je-keap-manager'); ?></option>
                            <?php foreach ($log_users as $lu) :
                                $uid = isset($lu['id']) ? (int) $lu['id'] : 0;
                                if ($uid < 1) {
                                    continue;
                                }
                                $ulabel = !empty($lu['display_name']) ? (string) $lu['display_name'] : (string) ($lu['user_login'] ?? '#' . $uid);
                                ?>
                                <option value="<?php echo (int) $uid; ?>" <?php selected($filter_user, $uid); ?>><?php echo esc_html($ulabel); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button button-primary"><?php esc_html_e('Filter', 'je-keap-manager'); ?></button>
                        <a href="<?php echo esc_url($this->log_tab_url()); ?>" class="button"><?php esc_html_e('Clear', 'je-keap-manager'); ?></a>
                    </form>
                    <div style="margin-bottom:12px;">
                        <p style="display:inline; margin:0;"><?php echo (int) $total; ?> total records.</p>
                        <form method="post" style="display:inline; margin-left:12px;">
                            <?php wp_nonce_field('jecm_purge_log', 'jecm_purge_nonce'); ?>
                            <input type="hidden" name="jecm_action" value="purge_log">
                            <button type="submit" class="button button-secondary"
                                onclick="return confirm('Are you sure you want to permanently delete all audit log entries? This cannot be undone.');"
                                style="color:#b32d2e; border-color:#b32d2e;">
                                Purge Log
                            </button>
                        </form>
                    </div>

                    <?php if (empty($logs)): ?>
                        <p><?php echo ($filter_action !== '' || $filter_user > 0) ? esc_html__('No matching records for this filter.', 'je-keap-manager') : esc_html__('No log entries yet.', 'je-keap-manager'); ?></p>
                    <?php else: ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Contact</th>
                                <th>Summary</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($logs as $row): ?>
                            <?php
                            $det_raw   = isset($row['details']) ? (string) $row['details'] : '';
                            $det_dec   = ($det_raw !== '') ? json_decode($det_raw, true) : null;
                            $row_act   = sanitize_key($row['action'] ?? 'cancel');
                            $row_email = sanitize_email($row['email'] ?? '');
                            $fst_url   = add_query_arg(
                                array('page' => 'je-keap-manager', 'tab' => 'find', 'email' => $row_email),
                                admin_url('admin.php')
                            );
                            ?>
                            <tr>
                                <td><?php
                                    $ts = strtotime($row['processed_at']);
                                    echo esc_html($ts ? date('M j, Y g:i A', $ts) : $row['processed_at']);
                                ?></td>
                                <td><?php echo $this->jecm_audit_action_badge($row_act); ?></td>
                                <td><?php
                                    $cname = trim((string) ($row['contact_name'] ?? ''));
                                    if ($cname !== '') {
                                        echo '<strong><a href="' . esc_url($fst_url) . '">' . esc_html($cname) . '</a></strong>';
                                        echo '<br><span class="description">' . esc_html($row_email) . '</span>';
                                    } else {
                                        echo '<strong><a href="' . esc_url($fst_url) . '">' . esc_html($row_email !== '' ? $row_email : '—') . '</a></strong>';
                                    }
                                ?></td>
                                <td><?php echo esc_html($this->jecm_audit_log_summary($row_act, $det_dec, $row)); ?></td>
                                <td><?php echo esc_html(!empty($row['display_name']) ? $row['display_name'] : ($row['user_login'] ?? 'Unknown')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($pages > 1): ?>
                    <div style="margin-top:12px;">
                        <?php for ($p = 1; $p <= $pages; $p++): ?>
                            <?php if ($p == $current_page): ?>
                                <strong style="margin-right:6px;"><?php echo (int) $p; ?></strong>
                            <?php else: ?>
                                <a href="<?php echo esc_url($this->log_tab_url(array(
                                    'filter_action' => $filter_action,
                                    'filter_user'   => $filter_user,
                                    'paged'         => $p,
                                ))); ?>" style="margin-right:6px;"><?php echo (int) $p; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

            <?php elseif ($tab === 'balance') : ?>
                <?php
                if (!empty($_GET['jecm_reset_dismissed']) && current_user_can('manage_options')) {
                    delete_option('jecm_dismissed_balance_orders');
                    wp_safe_redirect($this->tab_url('balance'));
                    exit;
                }

                $api_key = get_option('jecm_keap_api_key');
                $keap    = !empty($api_key) ? new JECM_Keap_API($api_key) : null;

                $since_raw = isset($_GET['since']) ? sanitize_text_field(wp_unslash($_GET['since'])) : '';
                $until_raw = isset($_GET['until']) ? sanitize_text_field(wp_unslash($_GET['until'])) : '';
                $since     = $since_raw !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $since_raw) ? $since_raw : gmdate('Y-m-01');
                $until     = $until_raw !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $until_raw) ? $until_raw : gmdate('Y-12-31');

                $orders = $keap ? $keap->get_balance_due_orders($since, $until) : array();
                set_transient('jecm_balance_due_count_' . get_current_user_id(), count($orders), 5 * MINUTE_IN_SECONDS);

                $jecm_dismissed_orders = array_map('strval', (array) get_option('jecm_dismissed_balance_orders', array()));
                $orders                = array_filter(
                    $orders,
                    static function ($o) use ($jecm_dismissed_orders) {
                        if (!is_array($o)) {
                            return false;
                        }
                        $oid = (string) ($o['id'] ?? $o['order_id'] ?? '');
                        return $oid === '' || !in_array($oid, $jecm_dismissed_orders, true);
                    }
                );
                $orders = array_values($orders);
                ?>
                <div style="max-width:1100px;">
                    <h2 style="margin-top:0;">💰 <?php esc_html_e('Balance Due', 'je-keap-manager'); ?></h2>

                    <form method="get" class="jecm-balance-filter" style="margin-bottom:20px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <input type="hidden" name="page" value="je-keap-manager">
                        <input type="hidden" name="tab" value="balance">
                        <label>
                            <?php esc_html_e('From:', 'je-keap-manager'); ?>
                            <input type="date" name="since" value="<?php echo esc_attr($since); ?>">
                        </label>
                        <label>
                            <?php esc_html_e('To:', 'je-keap-manager'); ?>
                            <input type="date" name="until" value="<?php echo esc_attr($until); ?>">
                        </label>
                        <input type="submit" class="button" value="<?php echo esc_attr__('Filter', 'je-keap-manager'); ?>">
                        <strong><?php echo (int) count($orders); ?> <?php esc_html_e('orders', 'je-keap-manager'); ?></strong>
                        <?php
                        $jecm_dismissed_count = count($jecm_dismissed_orders);
                        if ($jecm_dismissed_count > 0) :
                            ?>
                            <a href="<?php echo esc_url(add_query_arg(array('page' => 'je-keap-manager', 'tab' => 'balance', 'jecm_reset_dismissed' => '1'), admin_url('admin.php'))); ?>"
                               style="font-size:12px;color:#646970;margin-left:12px;">
                                <?php
                                /* translators: %d: number of dismissed orders */
                                echo esc_html(sprintf(__('↺ Show %d dismissed', 'je-keap-manager'), $jecm_dismissed_count));
                                ?>
                            </a>
                        <?php endif; ?>
                    </form>

                    <?php if (!$keap) : ?>
                        <p class="description"><?php esc_html_e('Configure a Keap API key on the Settings tab.', 'je-keap-manager'); ?></p>
                    <?php elseif (empty($orders)) : ?>
                        <p><?php esc_html_e('No orders with an outstanding balance in this date range.', 'je-keap-manager'); ?></p>
                    <?php else : ?>
                        <table class="wp-list-table widefat fixed striped" style="font-size:13px;">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Date', 'je-keap-manager'); ?></th>
                                    <th><?php esc_html_e('Name', 'je-keap-manager'); ?></th>
                                    <th><?php esc_html_e('Product', 'je-keap-manager'); ?></th>
                                    <th style="text-align:right;"><?php esc_html_e('Inv Total', 'je-keap-manager'); ?></th>
                                    <th style="text-align:right;"><?php esc_html_e('Paid', 'je-keap-manager'); ?></th>
                                    <th style="text-align:right;color:#c0392b;"><?php esc_html_e('Balance', 'je-keap-manager'); ?></th>
                                    <th><?php esc_html_e('Auto Charge', 'je-keap-manager'); ?></th>
                                    <th><?php esc_html_e('Action', 'je-keap-manager'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($orders as $order) :
                                if (!is_array($order)) {
                                    continue;
                                }
                                $contact   = isset($order['contact']) && is_array($order['contact']) ? $order['contact'] : array();
                                $cid       = $contact['id'] ?? ($order['contact_id'] ?? '');
                                $given     = isset($contact['given_name']) ? (string) $contact['given_name'] : (string) ($contact['first_name'] ?? '');
                                $family    = isset($contact['family_name']) ? (string) $contact['family_name'] : (string) ($contact['last_name'] ?? '');
                                $cname     = trim($given . ' ' . $family);
                                $cemail    = (string) ($contact['email'] ?? $contact['email_address'] ?? '');
                                $items     = $order['order_items'] ?? $order['line_items'] ?? array();
                                $first_item = is_array($items) && isset($items[0]) && is_array($items[0]) ? $items[0] : array();
                                $product   = (string) ($first_item['name'] ?? $first_item['description'] ?? '');
                                if ($product === '') {
                                    $product = '—';
                                }
                                $inv_total = number_format((float) ($order['total_due'] ?? 0), 2);
                                $paid      = number_format((float) ($order['total_paid'] ?? 0), 2);
                                $balance_n = (float) ($order['total_due'] ?? 0) - (float) ($order['total_paid'] ?? 0);
                                $balance   = number_format($balance_n, 2);
                                $odate_raw = (string) ($order['order_date'] ?? $order['creation_date'] ?? '');
                                $odate_ts  = $odate_raw !== '' ? strtotime($odate_raw) : false;
                                $odate     = $odate_ts ? gmdate('M j, Y', $odate_ts) : '—';
                                $pay_plan  = isset($order['payment_plan']) && is_array($order['payment_plan']) ? $order['payment_plan'] : array();
                                $auto      = !empty($pay_plan['auto_charge']) ? '✅' : '—';
                                $fst_url = $cemail !== ''
                                    ? add_query_arg(
                                        array(
                                            'page'  => 'je-keap-manager',
                                            'tab'   => 'find',
                                            'email' => $cemail,
                                        ),
                                        admin_url('admin.php')
                                    )
                                    : $this->tab_url('find');
                                $keap_url = 'https://app.infusionsoft.com/core/Contact/manageContact.jsp?view=edit&ID=' . (int) $cid;
                                ?>
                                <tr>
                                    <td><?php echo esc_html($odate); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url($fst_url); ?>"><?php echo esc_html($cname !== '' ? $cname : '—'); ?></a><br>
                                        <span style="color:#646970;font-size:11px;"><?php echo esc_html($cemail !== '' ? $cemail : '—'); ?></span>
                                    </td>
                                    <td><?php echo esc_html($product); ?></td>
                                    <td style="text-align:right;">$<?php echo esc_html($inv_total); ?></td>
                                    <td style="text-align:right;">$<?php echo esc_html($paid); ?></td>
                                    <td style="text-align:right;font-weight:700;color:#c0392b;">$<?php echo esc_html($balance); ?></td>
                                    <td style="text-align:center;"><?php echo esc_html($auto); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url($fst_url); ?>" class="button button-small"><?php esc_html_e('View', 'je-keap-manager'); ?></a>
                                        <?php if ((int) $cid > 0) : ?>
                                            <a href="<?php echo esc_url($keap_url); ?>" class="button button-small" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Keap ↗', 'je-keap-manager'); ?></a>
                                        <?php endif; ?>
                                        <?php
                                        $oid = (string) ($order['id'] ?? $order['order_id'] ?? '');
                                        if ($oid !== '') :
                                            ?>
                                            <button type="button"
                                                    class="button button-small jecm-dismiss-order-btn"
                                                    style="color:#b32d2e;border-color:#b32d2e;"
                                                    data-order-id="<?php echo esc_attr($oid); ?>"
                                                    data-nonce="<?php echo esc_attr(wp_create_nonce('jecm_dismiss_order_' . $oid)); ?>"
                                                    data-name="<?php echo esc_attr($cname !== '' ? $cname : $oid); ?>">
                                                <?php esc_html_e('✕ Dismiss', 'je-keap-manager'); ?>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <script>
                    (function() {
                        var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
                        var msgConfirm = <?php echo wp_json_encode(__('Dismiss "%s" from the Balance Due report? This only hides it here — no changes are made in Keap.', 'je-keap-manager')); ?>;
                        var txtRemoving = <?php echo wp_json_encode(__('Removing…', 'je-keap-manager')); ?>;
                        var txtDismiss = <?php echo wp_json_encode(__('✕ Dismiss', 'je-keap-manager')); ?>;
                        document.addEventListener('click', function(e) {
                            var btn = e.target.closest('.jecm-dismiss-order-btn');
                            if (!btn) {
                                return;
                            }
                            var name = btn.getAttribute('data-name') || <?php echo wp_json_encode(__('this order', 'je-keap-manager')); ?>;
                            if (!window.confirm(msgConfirm.replace('%s', name))) {
                                return;
                            }
                            btn.disabled = true;
                            btn.textContent = txtRemoving;
                            var fd = new FormData();
                            fd.append('action', 'jecm_dismiss_order');
                            fd.append('order_id', btn.getAttribute('data-order-id') || '');
                            fd.append('nonce', btn.getAttribute('data-nonce') || '');
                            fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
                                .then(function(r) { return r.json(); })
                                .then(function(res) {
                                    if (res && res.success) {
                                        var row = btn.closest('tr');
                                        if (row) {
                                            row.style.transition = 'opacity 0.3s';
                                            row.style.opacity = '0';
                                            setTimeout(function() { row.remove(); }, 300);
                                        }
                                    } else {
                                        btn.disabled = false;
                                        btn.textContent = txtDismiss;
                                        var err = (res && res.data && res.data.message) ? res.data.message : <?php echo wp_json_encode(__('Error', 'je-keap-manager')); ?>;
                                        window.alert(err);
                                    }
                                })
                                .catch(function() {
                                    btn.disabled = false;
                                    btn.textContent = txtDismiss;
                                    window.alert(<?php echo wp_json_encode(__('Request failed.', 'je-keap-manager')); ?>);
                                });
                        });
                    })();
                    </script>
                </div>

            <?php elseif ($tab === 'webhook'): ?>
                <?php $this->render_webhook_tab(); ?>

            <?php elseif ($tab === 'test'): ?>
                <?php $this->render_test_tab(); ?>

            <?php endif; ?>
        </div>
        <?php
    }

    private function render_tasks_tab() {
        if (
            !empty($_POST['jecm_task_complete'])
            && isset($_POST['jecm_task_complete_nonce'])
            && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['jecm_task_complete_nonce'])), 'jecm_task_complete')
            && current_user_can('manage_options')
        ) {
            $tid = absint($_POST['task_id'] ?? 0);
            $r   = JECM_Tasks::complete_task($tid);
            if (!empty($r['success'])) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Task marked complete.', 'je-keap-manager') . '</p></div>';
            } else {
                $msg = isset($r['message']) ? (string) $r['message'] : __('Could not complete task.', 'je-keap-manager');
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($msg) . '</p></div>';
            }
        }

        $task_filter = isset($_GET['task_status']) ? sanitize_key(wp_unslash($_GET['task_status'])) : 'open';
        if (!in_array($task_filter, array('open', 'completed', 'all'), true)) {
            $task_filter = 'open';
        }
        $status_for_query = ($task_filter === 'all') ? '' : $task_filter;

        $per_page = 25;
        $paged    = max(1, (int) ($_GET['paged'] ?? 1));
        $offset   = ($paged - 1) * $per_page;
        $tasks    = JECM_Tasks::get_tasks($status_for_query, $per_page, $offset);
        $total    = JECM_Tasks::get_total($status_for_query);
        $pages    = $per_page > 0 ? (int) ceil($total / $per_page) : 0;
        ?>
        <div class="card" style="max-width:1100px; padding:20px;">
            <h2 style="margin-top:0;"><?php esc_html_e('Tasks', 'je-keap-manager'); ?></h2>
            <p class="description">
                <?php esc_html_e('Open tasks are created from Find Student. Completing a task is recorded in the audit log.', 'je-keap-manager'); ?>
            </p>
            <p style="margin:16px 0;">
                <a href="<?php echo esc_url($this->tasks_tab_url()); ?>"
                   class="button <?php echo $task_filter === 'open' ? 'button-primary' : ''; ?>"><?php esc_html_e('Open', 'je-keap-manager'); ?></a>
                <a href="<?php echo esc_url($this->tasks_tab_url(array('task_status' => 'completed'))); ?>"
                   class="button <?php echo $task_filter === 'completed' ? 'button-primary' : ''; ?>"><?php esc_html_e('Completed', 'je-keap-manager'); ?></a>
                <a href="<?php echo esc_url($this->tasks_tab_url(array('task_status' => 'all'))); ?>"
                   class="button <?php echo $task_filter === 'all' ? 'button-primary' : ''; ?>"><?php esc_html_e('All', 'je-keap-manager'); ?></a>
            </p>

            <?php if (empty($tasks)) : ?>
                <p><?php esc_html_e('No tasks in this view.', 'je-keap-manager'); ?></p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Created', 'je-keap-manager'); ?></th>
                            <th><?php esc_html_e('Student', 'je-keap-manager'); ?></th>
                            <th><?php esc_html_e('Notes', 'je-keap-manager'); ?></th>
                            <th><?php esc_html_e('Status', 'je-keap-manager'); ?></th>
                            <th><?php esc_html_e('By', 'je-keap-manager'); ?></th>
                            <th style="width:140px;"><?php esc_html_e('Action', 'je-keap-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tasks as $t) : ?>
                        <?php
                        $tid       = (int) ($t['id'] ?? 0);
                        $st        = (string) ($t['status'] ?? '');
                        $em        = sanitize_email($t['student_email'] ?? '');
                        $find_url  = add_query_arg(array('page' => 'je-keap-manager', 'tab' => 'find', 'email' => $em), admin_url('admin.php'));
                        $sname     = trim((string) ($t['student_name'] ?? ''));
                        $disp      = $sname !== '' ? $sname : $em;
                        $notes_raw = (string) ($t['notes'] ?? '');
                        $notes_one = wp_strip_all_tags($notes_raw);
                        if (function_exists('mb_substr')) {
                            $excerpt = mb_substr($notes_one, 0, 220);
                            if (mb_strlen($notes_one) > 220) {
                                $excerpt .= '…';
                            }
                        } else {
                            $excerpt = strlen($notes_one) > 220 ? substr($notes_one, 0, 220) . '…' : $notes_one;
                        }
                        $creator = !empty($t['creator_name']) ? (string) $t['creator_name'] : (string) ($t['creator_login'] ?? '');
                        $ts      = !empty($t['created_at']) ? strtotime($t['created_at']) : false;
                        $tsshow  = $ts ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $ts) : '';
                        ?>
                        <tr>
                            <td><?php echo esc_html($tsshow); ?></td>
                            <td>
                                <strong><a href="<?php echo esc_url($find_url); ?>"><?php echo esc_html($disp); ?></a></strong>
                                <?php if ($em !== '' && $sname !== '') : ?>
                                    <br><span class="description"><?php echo esc_html($em); ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width:380px;"><?php echo esc_html($excerpt); ?></td>
                            <td><?php echo esc_html($st === 'completed' ? __('Completed', 'je-keap-manager') : __('Open', 'je-keap-manager')); ?></td>
                            <td><?php echo esc_html($creator !== '' ? $creator : '—'); ?></td>
                            <td>
                                <?php if ($st === 'open') : ?>
                                    <form method="post" style="margin:0;">
                                        <?php wp_nonce_field('jecm_task_complete', 'jecm_task_complete_nonce'); ?>
                                        <input type="hidden" name="task_id" value="<?php echo (int) $tid; ?>">
                                        <button type="submit" name="jecm_task_complete" value="1" class="button button-small button-primary"><?php esc_html_e('Complete', 'je-keap-manager'); ?></button>
                                    </form>
                                <?php else : ?>
                                    <?php
                                    $cts = !empty($t['completed_at']) ? strtotime($t['completed_at']) : false;
                                    if ($cts) {
                                        echo esc_html(
                                            sprintf(
                                                /* translators: %s: localized datetime */
                                                __('Done %s', 'je-keap-manager'),
                                                date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $cts)
                                            )
                                        );
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($pages > 1) : ?>
                    <p style="margin-top:12px;">
                        <?php
                        for ($pi = 1; $pi <= $pages; $pi++) {
                            if ($pi === $paged) {
                                echo '<strong style="margin-right:8px;">' . (int) $pi . '</strong>';
                            } else {
                                $pag_args = array('paged' => $pi);
                                if ($task_filter === 'completed' || $task_filter === 'all') {
                                    $pag_args['task_status'] = $task_filter;
                                }
                                echo '<a href="' . esc_url($this->tasks_tab_url($pag_args)) . '" style="margin-right:8px;">' . (int) $pi . '</a>';
                            }
                        }
                        ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Jazzedge Academy wp-admin URL for FluentCart order detail (SPA hash route).
     * $academy_order_id matches wp_fct_orders.id / mirror academy_order_id.
     *
     * @param int $academy_order_id
     * @return string Full URL or empty if invalid.
     */
    private function jecm_fc_academy_fluentcart_order_url($academy_order_id) {
        $id = (int) $academy_order_id;
        if ($id <= 0) {
            return '';
        }
        $base = apply_filters('jecm_fc_academy_site_url', 'https://jazzedge.academy');
        if (!is_string($base) || $base === '') {
            $base = 'https://jazzedge.academy';
        }
        $base  = rtrim($base, '/');
        $admin = $base . '/wp-admin/admin.php?page=fluent-cart';

        return esc_url($admin) . '#/orders/' . $id . '/view';
    }

    /**
     * FluentCRM admin deep link for a subscriber (table {prefix}fc_subscribers, column id).
     *
     * @param string $email Student email (exact match).
     * @return string Full URL or empty if table missing / no row.
     */
    private function jecm_fluentcrm_subscriber_admin_url($email) {
        global $wpdb;
        $email = sanitize_email((string) $email);
        if ($email === '') {
            return '';
        }
        $table  = $wpdb->prefix . 'fc_subscribers';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if (!$exists) {
            return '';
        }
        $sid = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE email = %s ORDER BY id ASC LIMIT 1", $email));
        if ($sid < 1) {
            return '';
        }
        $admin = esc_url(admin_url('admin.php?page=fluentcrm-admin'));
        // Hash matches common FluentCRM deep-link pattern (32-char hex); filter if your install expects a different token.
        $t = md5(wp_salt('nonce') . 'jecm_fc_sub|' . $email . '|' . (string) $sid);

        /**
         * @param string $url   Full subscriber admin URL.
         * @param string $email Subscriber email.
         * @param int    $sid   fc_subscribers.id.
         */
        return apply_filters('jecm_fluentcrm_subscriber_admin_url', $admin . '#/subscribers/' . $sid . '?t=' . $t, $email, $sid);
    }

    /**
     * @param string $url From jecm_fluentcrm_subscriber_admin_url().
     */
    private function jecm_render_fluentcrm_student_link($url) {
        if ($url === '') {
            return;
        }
        ?>
        <a href="<?php echo esc_url($url); ?>"
           class="button button-secondary"
           target="_blank"
           rel="noopener noreferrer"
           style="display:inline-flex;align-items:center;gap:6px;white-space:nowrap;flex-shrink:0;">
            <span class="dashicons dashicons-email-alt" style="width:18px;height:18px;font-size:18px;margin:0;"></span>
            <?php esc_html_e('FluentCRM', 'je-keap-manager'); ?>
            <span class="dashicons dashicons-external" style="width:14px;height:14px;font-size:14px;margin:0;opacity:0.85;"></span>
        </a>
        <?php
    }

    /**
     * Student email with one-click copy (Find Student header lines).
     *
     * @param string $email Student email.
     */
    private function jecm_render_student_email_with_copy($email) {
        $email = sanitize_email((string) $email);
        if ($email === '') {
            return;
        }
        ?>
        <span class="jecm-student-email-wrap" style="display:inline-flex;align-items:center;gap:6px;vertical-align:middle;">
            <button type="button"
                    class="jecm-copy-student-email"
                    style="margin:0;padding:0;border:0;background:none;box-shadow:none;border-radius:0;color:#2271b1;cursor:pointer;line-height:0;vertical-align:middle;display:inline-flex;align-items:center;"
                    data-copy="<?php echo esc_attr($email); ?>"
                    title="<?php esc_attr_e('Copy email', 'je-keap-manager'); ?>"
                    aria-label="<?php esc_attr_e('Copy email address', 'je-keap-manager'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18" aria-hidden="true" focusable="false" style="display:block;flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5A3.375 3.375 0 0 0 6.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0 0 15 2.25h-1.5a2.251 2.251 0 0 0-2.15 1.586m5.8 0c.065.21.1.433.1.664v.75h-6V4.5c0-.231.035-.454.1-.664M6.75 7.5H4.875c-.621 0-1.125.504-1.125 1.125v12c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5a9 9 0 0 0-9-9Z" />
                </svg>
            </button>
            <span><?php echo esc_html($email); ?></span>
        </span>
        <?php
    }

    /**
     * @param list<array<string, mixed>> $rows Rows from JECM_FluentCart_Webhook::get_orders_by_email.
     */
    private function jecm_render_fc_orders_table($rows) {
        if (empty($rows)) {
            return;
        }
        ?>
        <h3 style="margin:24px 0 10px;"><?php esc_html_e('FluentCart orders (Academy sync)', 'je-keap-manager'); ?></h3>
        <table class="widefat striped" style="table-layout:auto;">
            <thead>
                <tr>
                    <th><?php esc_html_e('Order UUID', 'je-keap-manager'); ?></th>
                    <th><?php esc_html_e('Academy ID', 'je-keap-manager'); ?></th>
                    <th><?php esc_html_e('Academy admin', 'je-keap-manager'); ?></th>
                    <th><?php esc_html_e('Status', 'je-keap-manager'); ?></th>
                    <th><?php esc_html_e('Payment', 'je-keap-manager'); ?></th>
                    <th><?php esc_html_e('Total', 'je-keap-manager'); ?></th>
                    <th><?php esc_html_e('Receipt', 'je-keap-manager'); ?></th>
                    <th><?php esc_html_e('Invoice', 'je-keap-manager'); ?></th>
                    <th><?php esc_html_e('Completed', 'je-keap-manager'); ?></th>
                    <th><?php esc_html_e('Updated', 'je-keap-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $orow) : ?>
                <?php
                $uuid = (string) ($orow['order_uuid'] ?? '');
                $aid_n = isset($orow['academy_order_id']) && $orow['academy_order_id'] !== '' && $orow['academy_order_id'] !== null
                    ? (int) $orow['academy_order_id'] : 0;
                $aid = $aid_n > 0 ? (string) $aid_n : '—';
                $academy_order_url = $aid_n > 0 ? $this->jecm_fc_academy_fluentcart_order_url($aid_n) : '';
                $raw_amt = isset($orow['total_amount']) && $orow['total_amount'] !== '' && $orow['total_amount'] !== null
                    ? (int) $orow['total_amount'] : null;
                $cur   = strtoupper(trim((string) ($orow['currency'] ?? '')));
                $total = '—';
                if ($raw_amt !== null) {
                    $total = number_format_i18n($raw_amt / 100, 2) . ($cur !== '' ? ' ' . $cur : '');
                }
                $comp = !empty($orow['completed_at'])
                    ? mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $orow['completed_at'], true)
                    : '—';
                $upd = !empty($orow['updated_at'])
                    ? mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $orow['updated_at'], true)
                    : '—';
                ?>
                <tr>
                    <td><code style="font-size:11px; word-break:break-all;"><?php echo esc_html($uuid); ?></code></td>
                    <td><?php echo esc_html($aid); ?></td>
                    <td>
                        <?php if ($academy_order_url !== '') : ?>
                            <a href="<?php echo esc_url($academy_order_url); ?>" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('View on Academy', 'je-keap-manager'); ?>
                                <span class="dashicons dashicons-external" style="font-size:14px;width:14px;height:14px;text-decoration:none;vertical-align:text-bottom;"></span>
                            </a>
                        <?php else : ?>
                            &mdash;
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html((string) ($orow['status'] ?? '')); ?></td>
                    <td><?php echo esc_html((string) ($orow['payment_status'] ?? '')); ?></td>
                    <td><?php echo esc_html($total); ?></td>
                    <td><?php echo esc_html((string) ($orow['receipt_number'] ?? '')); ?></td>
                    <td><?php echo esc_html((string) ($orow['invoice_no'] ?? '')); ?></td>
                    <td><?php echo esc_html($comp); ?></td>
                    <td><?php echo esc_html($upd); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function render_find_tab() {
        $cache_key = $this->jecm_find_student_cache_key();
        if (!empty($_GET['clear']) && current_user_can('manage_options')) {
            delete_transient($cache_key);
            wp_redirect(admin_url('admin.php?page=je-keap-manager&tab=find'));
            exit;
        }
        if (!empty($_GET['nocache']) && current_user_can('manage_options')) {
            delete_transient($cache_key);
        }

        $from_cache = false;
        $api_key    = get_option('jecm_keap_api_key');
        $keap       = new JECM_Keap_API($api_key);
        $email      = '';
        $error      = '';
        $lookup_ok  = false;
        $contact_id = 0;
        $tags_result = null;
        $subs_result = null;
        $tx_result   = null;
        $member_info_rows = array();
        $lookup_data      = array('first_name' => '', 'last_name' => '');
        $body            = null;
        $local_fc_orders = array();

        if (!empty($_POST['jecm_find_nonce']) && wp_verify_nonce(wp_unslash($_POST['jecm_find_nonce']), 'jecm_find')) {
            delete_transient($cache_key);
            $email = sanitize_email(wp_unslash($_POST['jecm_find_email'] ?? ''));
        } elseif (!empty($_GET['email'])) {
            $email = sanitize_email(wp_unslash($_GET['email']));
        }

        if ($email === '' && current_user_can('manage_options')) {
            $cached = get_transient($cache_key);
            if (is_array($cached) && !empty($cached['email']) && !empty($cached['contact_id'])) {
                $email         = sanitize_email((string) $cached['email']);
                $contact_id    = (int) $cached['contact_id'];
                $lookup_ok     = true;
                $tags_result   = $cached['tags'] ?? null;
                $subs_result   = $cached['subscriptions'] ?? null;
                $tx_result     = $cached['tx_result'] ?? null;
                $member_info_rows = isset($cached['member_info_rows']) && is_array($cached['member_info_rows'])
                    ? $cached['member_info_rows']
                    : array();
                $lookup_data   = isset($cached['lookup_data']) && is_array($cached['lookup_data'])
                    ? $cached['lookup_data']
                    : array('first_name' => '', 'last_name' => '');
                $local_fc_orders = isset($cached['local_fc_orders']) && is_array($cached['local_fc_orders'])
                    ? $cached['local_fc_orders']
                    : array();
                $from_cache    = true;
            }
        }

        if ($email && !$from_cache) {
            if (!$api_key) {
                $error = 'No Keap API key configured — go to Settings tab.';
            } else {
                $found = $keap->find_contact_id($email);
                if (!$found['success']) {
                    $error = $found['message'];
                } else {
                    $contact_id = (int) $found['contact_id'];
                    $full       = $keap->get_contact_full($contact_id);
                    if (!$full['success']) {
                        $error = $full['message'];
                    } else {
                        $lookup_ok = true;
                        $body      = $full['body'];
                        $lookup_data = array(
                            'first_name' => trim((string) ($body['given_name'] ?? '')),
                            'last_name'  => trim((string) ($body['family_name'] ?? '')),
                        );
                        $lookup_display_name = trim($lookup_data['first_name'] . ' ' . $lookup_data['last_name']);
                        $this->merge_jecm_recent_student(
                            $email,
                            $lookup_display_name !== '' ? $lookup_display_name : $email,
                            $contact_id
                        );

                        $tags_result = $keap->get_contact_tags_with_names($contact_id);
                        $subs_result = $keap->get_subscriptions($contact_id);
                        $tx_result   = $keap->get_recent_transactions($contact_id, 10);

                        $cf_labels = array(
                            'Academy Expiration Date' => 'AcademyExpirationDate',
                            'Academy Expiration Note' => 'AcademyExpirationNote',
                            'Academy Last Login'      => 'AcademyLastLogin',
                            'HSP Expiration Date'     => 'HSPExpirationDate',
                            'HSP Last Login'          => 'HSPLastLogin',
                            'Password4Microsites'     => 'Password4Microsites',
                        );
                        foreach ($cf_labels as $cf_label => $field_key) {
                            $cf = $keap->interpret_custom_field_from_body($body, $field_key);
                            $cell = '—';
                            if (!empty($cf['success']) && isset($cf['display'])) {
                                $d = trim((string) $cf['display']);
                                if ($d !== '' && strtolower($d) !== '(empty)') {
                                    $cell = $cf['display'];
                                }
                            }
                            $member_info_rows[] = array(
                                'label'     => $cf_label,
                                'value'     => $cell,
                                'raw'       => (!empty($cf['success']) && isset($cf['raw'])) ? (string) $cf['raw'] : '',
                                'editable'  => in_array($field_key, array('AcademyExpirationDate', 'HSPExpirationDate'), true),
                                'field_key' => $field_key,
                            );
                        }
                    }
                }
            }
        }

        if (!$from_cache) {
            $local_fc_orders = $email ? JECM_FluentCart_Webhook::get_orders_by_email($email, 100) : array();
        }

        if (!$from_cache && $lookup_ok && $contact_id > 0 && $email !== '' && is_array($body)) {
            set_transient(
                $cache_key,
                array(
                    'email'          => $email,
                    'contact_id'     => $contact_id,
                    'lookup_ok'      => true,
                    'contact'        => $body,
                    'lookup_data'    => $lookup_data,
                    'tags'           => $tags_result,
                    'subscriptions'  => $subs_result,
                    'tx_result'      => $tx_result,
                    'member_info_rows' => $member_info_rows,
                    'local_fc_orders'  => $local_fc_orders,
                ),
                8 * HOUR_IN_SECONDS
            );
        }
        $jecm_has_fc_local          = !empty($local_fc_orders);
        $jecm_local_student_panel   = (bool) ($email && $jecm_has_fc_local && !$lookup_ok && $contact_id === 0);
        $jecm_find_hide_search      = (bool) ($email && (($lookup_ok && $contact_id) || ($contact_id && !$lookup_ok) || $jecm_local_student_panel));
        $jecm_fc_local_display_name = '';
        if ($jecm_has_fc_local && $email) {
            $jecm_fc_local_display_name = JECM_FluentCart_Webhook::guess_display_name_from_orders($local_fc_orders, $email);
        }
        if ($jecm_local_student_panel) {
            $d = $jecm_fc_local_display_name !== '' ? $jecm_fc_local_display_name : $email;
            $this->merge_jecm_recent_student($email, $d, 0);
        }

        $jecm_fluentcrm_url = '';
        if ($email !== '' && $jecm_find_hide_search) {
            $jecm_fluentcrm_url = $this->jecm_fluentcrm_subscriber_admin_url($email);
        }
        $jecm_find_cache_refresh_url = '';
        if ($email !== '') {
            $jecm_find_cache_refresh_url = add_query_arg(
                array(
                    'page'    => 'je-keap-manager',
                    'tab'     => 'find',
                    'email'   => $email,
                    'nocache' => '1',
                ),
                admin_url('admin.php')
            );
        }

        $jecm_switch_student_form = '';
        if ($jecm_find_hide_search) {
            ob_start();
            ?>
            <div id="jecm-switch-student-form"
                 style="display:none;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;padding:16px 20px;margin-bottom:20px;">
                <strong style="display:block;margin-bottom:10px;"><?php esc_html_e('🔍 Find a Different Student', 'je-keap-manager'); ?></strong>
                <form method="post" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <?php wp_nonce_field('jecm_find', 'jecm_find_nonce'); ?>
                    <input type="email" name="jecm_find_email" placeholder="<?php echo esc_attr__('student@email.com', 'je-keap-manager'); ?>"
                           style="flex:1 1 280px;padding:6px 10px;border:1px solid #8c8f94;border-radius:3px;font-size:14px;"
                           value="" autocomplete="off" />
                    <button type="submit" class="button button-primary"><?php esc_html_e('Find Student', 'je-keap-manager'); ?></button>
                    <button type="button" id="jecm-switch-cancel-btn" class="button button-secondary"><?php esc_html_e('Cancel', 'je-keap-manager'); ?></button>
                </form>
                <?php
                $jecm_sw_recent = get_user_meta(get_current_user_id(), 'jecm_recent_students', true);
                $jecm_sw_recent = is_array($jecm_sw_recent)
                    ? array_values(
                        array_filter(
                            $jecm_sw_recent,
                            static function ($r) {
                                return is_array($r) && !empty($r['email']);
                            }
                        )
                    )
                    : array();
                $jecm_sw_recent = array_slice($jecm_sw_recent, 0, 20);
                if (!empty($jecm_sw_recent)) :
                    ?>
                    <div style="margin-top:10px;">
                        <select id="jecm-switch-recent-select"
                                style="max-width:100%;width:420px;padding:5px 8px;font-size:13px;border:1px solid #8c8f94;border-radius:3px;">
                            <option value=""><?php esc_html_e('— Jump to recent student —', 'je-keap-manager'); ?></option>
                            <?php foreach ($jecm_sw_recent as $r_entry) : ?>
                                <?php
                                $r_email = sanitize_email($r_entry['email']);
                                $r_name  = isset($r_entry['name']) ? sanitize_text_field($r_entry['name']) : $r_email;
                                $r_url   = add_query_arg(
                                    array(
                                        'page'  => 'je-keap-manager',
                                        'tab'   => 'find',
                                        'email' => $r_email,
                                    ),
                                    admin_url('admin.php')
                                );
                                ?>
                                <option value="<?php echo esc_attr($r_url); ?>">
                                    <?php echo esc_html($r_name . ' — ' . $r_email); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <script>
                    (function() {
                        var sel = document.getElementById('jecm-switch-recent-select');
                        if (!sel) {
                            return;
                        }
                        sel.addEventListener('change', function() {
                            if (this.value) {
                                window.location.href = this.value;
                            }
                        });
                    })();
                    </script>
                <?php endif; ?>
            </div>
            <?php
            $jecm_switch_student_form = (string) ob_get_clean();
        }

        ?>
        <div class="card" style="max-width:960px; padding:20px;">
            <?php if (!$jecm_find_hide_search) : ?>
                <h2 style="margin-top:0;">🔍 Find Student</h2>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="notice notice-<?php echo $jecm_has_fc_local && $email ? 'warning' : 'error'; ?>"
                     style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding-right:16px;">
                    <div>
                        <p style="margin:0;"><?php echo esc_html($error); ?></p>
                        <?php if ($jecm_has_fc_local && $email && !$lookup_ok) : ?>
                            <p style="margin:4px 0 0;"><?php esc_html_e('FluentCart order data from the academy is available below for this email.', 'je-keap-manager'); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($email !== '' && !$lookup_ok && $contact_id === 0) : ?>
                        <button type="button"
                                class="button button-primary jecm-add-to-keap-btn"
                                style="display:inline-flex;align-items:center;gap:6px;white-space:nowrap;flex-shrink:0;"
                                data-email="<?php echo esc_attr($email); ?>"
                                data-name=""
                                data-nonce="<?php echo esc_attr(wp_create_nonce('jecm_add_to_keap')); ?>">
                            <span class="dashicons dashicons-plus-alt" style="width:18px;height:18px;font-size:18px;margin:0;"></span>
                            <?php esc_html_e('Add to Keap + Academy', 'je-keap-manager'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($email !== '' && !$lookup_ok && $contact_id === 0) : ?>
                <script>
                (function() {
                    var addToKeapAjax = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
                    document.addEventListener('click', function(e) {
                        var btn = e.target.closest('.jecm-add-to-keap-btn');
                        if (!btn) {
                            return;
                        }
                        if (!window.confirm(<?php echo wp_json_encode(__('Create a new Keap contact for this email address?', 'je-keap-manager')); ?>)) {
                            return;
                        }
                        btn.disabled = true;
                        var origLabel = btn.textContent;
                        btn.textContent = <?php echo wp_json_encode(__('Creating…', 'je-keap-manager')); ?>;
                        var fd = new FormData();
                        fd.append('action',  'jecm_add_to_keap');
                        fd.append('nonce',   btn.getAttribute('data-nonce') || '');
                        fd.append('email',   btn.getAttribute('data-email') || '');
                        fd.append('name',    btn.getAttribute('data-name') || '');
                        fetch(addToKeapAjax, { method: 'POST', credentials: 'same-origin', body: fd })
                            .then(function(r) { return r.json(); })
                            .then(function(res) {
                                if (res && res.success) {
                                    btn.textContent = <?php echo wp_json_encode(__('✓ Created', 'je-keap-manager')); ?>;
                                    btn.style.background = '#00a32a';
                                    btn.style.borderColor = '#00a32a';
                                    setTimeout(function() { window.location.reload(); }, 1200);
                                } else {
                                    btn.disabled = false;
                                    btn.textContent = origLabel;
                                    var msg = (res && res.data && res.data.message) ? res.data.message : <?php echo wp_json_encode(__('Unknown error', 'je-keap-manager')); ?>;
                                    window.alert(<?php echo wp_json_encode(__('Error: ', 'je-keap-manager')); ?> + msg);
                                }
                            })
                            .catch(function() {
                                btn.disabled = false;
                                btn.textContent = origLabel;
                                window.alert(<?php echo wp_json_encode(__('Request failed.', 'je-keap-manager')); ?>);
                            });
                    });
                })();
                </script>
            <?php endif; ?>

            <?php if (!$jecm_find_hide_search) : ?>
                <form method="post" style="margin-bottom:8px;">
                    <?php wp_nonce_field('jecm_find', 'jecm_find_nonce'); ?>
                    <label for="jecm_find_email" class="screen-reader-text"><?php esc_html_e('Email', 'je-keap-manager'); ?></label>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <input type="email" id="jecm_find_email" name="jecm_find_email"
                               value="<?php echo esc_attr($email); ?>"
                               placeholder="<?php echo esc_attr__('student@email.com', 'je-keap-manager'); ?>"
                               style="min-width:300px;flex:1 1 300px;"
                               autocomplete="off" />
                        <?php submit_button(__('Look Up', 'je-keap-manager'), 'primary', 'jecm_find_submit', false); ?>
                    </div>
                </form>

                <?php
                $jecm_recent_all = get_user_meta(get_current_user_id(), 'jecm_recent_students', true);
                $jecm_recent_all = is_array($jecm_recent_all)
                    ? array_values(
                        array_filter(
                            $jecm_recent_all,
                            static function ($r) {
                                return is_array($r) && !empty($r['email']);
                            }
                        )
                    )
                    : array();
                $jecm_recent_all = array_slice($jecm_recent_all, 0, 20);
                if (!empty($jecm_recent_all)) :
                    ?>
                    <div style="margin-bottom:24px;">
                        <select id="jecm-recent-student-select"
                                style="max-width:460px;width:100%;padding:5px 8px;font-size:13px;border:1px solid #8c8f94;border-radius:3px;">
                            <option value=""><?php esc_html_e('— Jump to recent student —', 'je-keap-manager'); ?></option>
                            <?php foreach ($jecm_recent_all as $r_entry) : ?>
                                <?php
                                $r_email = sanitize_email($r_entry['email']);
                                $r_name  = isset($r_entry['name']) ? sanitize_text_field($r_entry['name']) : $r_email;
                                $r_url   = add_query_arg(
                                    array(
                                        'page'  => 'je-keap-manager',
                                        'tab'   => 'find',
                                        'email' => $r_email,
                                    ),
                                    admin_url('admin.php')
                                );
                                ?>
                                <option value="<?php echo esc_attr($r_url); ?>">
                                    <?php echo esc_html($r_name . ' — ' . $r_email); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <script>
                    (function() {
                        var sel = document.getElementById('jecm-recent-student-select');
                        if (!sel) {
                            return;
                        }
                        sel.addEventListener('change', function() {
                            if (this.value) {
                                window.location.href = this.value;
                            }
                        });
                    })();
                    </script>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($jecm_local_student_panel) : ?>
                <?php
                $fst_local_display = $jecm_fc_local_display_name !== '' ? $jecm_fc_local_display_name : $email;
                ?>
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:16px;row-gap:12px;padding-bottom:16px;border-bottom:2px solid #dcdcde;margin-bottom:20px;">
                    <div style="flex:1 1 220px;min-width:0;max-width:100%;">
                        <h2 style="margin:0;font-size:22px;line-height:1.2;"><?php echo esc_html($fst_local_display); ?></h2>
                        <div style="margin-top:4px;color:#646970;font-size:13px;">
                            <?php $this->jecm_render_student_email_with_copy($email); ?>
                            &nbsp;&mdash;&nbsp;
                            <?php esc_html_e('No Keap contact for this email. Autologin and Keap tools require a Keap record.', 'je-keap-manager'); ?>
                            &nbsp;<button type="button" id="jecm-switch-student-btn"
                                    style="background:none;border:none;padding:0;color:#2271b1;cursor:pointer;font-size:13px;text-decoration:underline;">
                                <?php esc_html_e('🔍 Find Different Student', 'je-keap-manager'); ?>
                            </button>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap;flex:0 1 auto;margin-left:auto;">
                        <?php $this->jecm_render_fluentcrm_student_link($jecm_fluentcrm_url); ?>
                        <button type="button"
                                class="button button-primary jecm-add-to-keap-btn"
                                style="display:inline-flex;align-items:center;gap:6px;white-space:nowrap;"
                                data-email="<?php echo esc_attr($email); ?>"
                                data-name="<?php echo esc_attr($fst_local_display); ?>"
                                data-nonce="<?php echo esc_attr(wp_create_nonce('jecm_add_to_keap')); ?>">
                            <span class="dashicons dashicons-plus-alt" style="width:18px;height:18px;font-size:18px;margin:0;"></span>
                            <?php esc_html_e('Add to Keap + Academy', 'je-keap-manager'); ?>
                        </button>
                        <button type="button"
                                class="button button-secondary jecm-open-task-modal"
                                style="display:inline-flex;align-items:center;gap:6px;white-space:nowrap;"
                                data-email="<?php echo esc_attr($email); ?>"
                                data-contact-id="0"
                                data-student-name="<?php echo esc_attr($fst_local_display); ?>">
                            <span class="dashicons dashicons-clipboard" style="width:18px;height:18px;font-size:18px;margin:0;"></span>
                            <?php esc_html_e('Add Task', 'je-keap-manager'); ?>
                        </button>
                    </div>
                </div>
                <?php echo $jecm_switch_student_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup built in render_find_tab ?>
                <?php $this->jecm_render_fc_orders_table($local_fc_orders); ?>
            <?php endif; ?>

            <?php if ($contact_id && !$lookup_ok) : ?>
                <?php
                $jecm_partial_display = trim(trim((string) ($lookup_data['first_name'] ?? '')) . ' ' . trim((string) ($lookup_data['last_name'] ?? '')));
                if ($jecm_partial_display === '') {
                    $jecm_partial_display = $email;
                }
                $jecm_partial_keap_url = 'https://app.infusionsoft.com/core/Contact/manageContact.jsp?view=edit&ID=' . (int) $contact_id;
                ?>
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:16px;row-gap:12px;padding-bottom:16px;border-bottom:2px solid #dcdcde;margin-bottom:16px;">
                    <div style="flex:1 1 220px;min-width:0;max-width:100%;">
                        <h2 style="margin:0;font-size:22px;line-height:1.2;"><?php echo esc_html($jecm_partial_display); ?></h2>
                        <div style="margin-top:4px;color:#646970;font-size:13px;">
                            <?php $this->jecm_render_student_email_with_copy($email); ?>
                            &nbsp;&mdash;&nbsp;
                            <strong><?php esc_html_e('Keap ID:', 'je-keap-manager'); ?></strong>
                            <a href="<?php echo esc_url($jecm_partial_keap_url); ?>" target="_blank" rel="noopener" style="text-decoration:none;color:#2271b1;">
                                <?php echo esc_html((string) $contact_id); ?>
                                <span class="dashicons dashicons-external" style="font-size:13px;vertical-align:middle;"></span>
                            </a>
                            &nbsp;<button type="button" id="jecm-switch-student-btn"
                                    style="background:none;border:none;padding:0;color:#2271b1;cursor:pointer;font-size:13px;text-decoration:underline;">
                                <?php esc_html_e('🔍 Find Different Student', 'je-keap-manager'); ?>
                            </button>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap;flex:0 1 auto;margin-left:auto;">
                        <?php $this->jecm_render_fluentcrm_student_link($jecm_fluentcrm_url); ?>
                        <button type="button"
                                class="button button-secondary jecm-open-task-modal"
                                style="display:inline-flex;align-items:center;gap:6px;white-space:nowrap;"
                                data-email="<?php echo esc_attr($email); ?>"
                                data-contact-id="<?php echo (int) $contact_id; ?>"
                                data-student-name="<?php echo esc_attr($jecm_partial_display); ?>">
                            <span class="dashicons dashicons-clipboard" style="width:18px;height:18px;font-size:18px;margin:0;"></span>
                            <?php esc_html_e('Add Task', 'je-keap-manager'); ?>
                        </button>
                    </div>
                </div>
                <?php echo $jecm_switch_student_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php if ($jecm_has_fc_local) : ?>
                    <?php $this->jecm_render_fc_orders_table($local_fc_orders); ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($lookup_ok && $contact_id): ?>
                <?php
                $jecm_keap_contact_url = 'https://app.infusionsoft.com/core/Contact/manageContact.jsp?view=edit&ID=' . (int) $contact_id;
                $fst_contact_display         = trim($lookup_data['first_name'] . ' ' . $lookup_data['last_name']);
                if ($fst_contact_display === '') {
                    $fst_contact_display = $email;
                }
                $fst_autologin_destinations = $this->jecm_get_autologin_destinations();
                $fst_autologin_sites        = $this->jecm_get_autologin_sites();
                ?>
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:16px;row-gap:12px;padding-bottom:16px;border-bottom:2px solid #dcdcde;margin-bottom:20px;">
                    <div style="flex:1 1 220px;min-width:0;max-width:100%;">
                        <h2 style="margin:0;font-size:22px;line-height:1.2;display:flex;align-items:center;flex-wrap:wrap;gap:8px;">
                            <span><?php echo esc_html($fst_contact_display); ?></span>
                            <?php if (!empty($from_cache)) : ?>
                                <span style="font-size:11px;background:#e0e0e0;padding:2px 8px;border-radius:10px;color:#646970;font-weight:400;line-height:1.4;">
                                    <?php esc_html_e('📦 cached', 'je-keap-manager'); ?>
                                    &nbsp;·&nbsp;
                                    <a href="<?php echo esc_url($jecm_find_cache_refresh_url); ?>"
                                       style="color:#646970;text-decoration:underline;"><?php esc_html_e('Refresh', 'je-keap-manager'); ?></a>
                                </span>
                            <?php endif; ?>
                        </h2>
                        <div style="margin-top:4px;color:#646970;font-size:13px;">
                            <?php $this->jecm_render_student_email_with_copy($email); ?>
                            &nbsp;&mdash;&nbsp;
                            <strong><?php esc_html_e('Keap ID:', 'je-keap-manager'); ?></strong>
                            <a href="<?php echo esc_url($jecm_keap_contact_url); ?>" target="_blank" rel="noopener" style="text-decoration:none;">
                                <?php echo esc_html((string) $contact_id); ?>
                                <span class="dashicons dashicons-external" style="font-size:13px; vertical-align:middle;"></span>
                            </a>
                            &nbsp;<button type="button" id="jecm-switch-student-btn"
                                    style="background:none;border:none;padding:0;color:#2271b1;cursor:pointer;font-size:13px;text-decoration:underline;">
                                <?php esc_html_e('🔍 Find Different Student', 'je-keap-manager'); ?>
                            </button>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap;flex:0 1 auto;margin-left:auto;">
                        <?php $this->jecm_render_fluentcrm_student_link($jecm_fluentcrm_url); ?>
                        <button type="button"
                                class="button button-secondary jecm-open-task-modal"
                                id="jecm-add-task-btn"
                                data-email="<?php echo esc_attr($email); ?>"
                                data-contact-id="<?php echo (int) $contact_id; ?>"
                                data-student-name="<?php echo esc_attr($fst_contact_display); ?>">
                            🏷️ <?php esc_html_e('Add Task', 'je-keap-manager'); ?>
                        </button>
                        <span style="width:1px;height:28px;background:#dcdcde;display:inline-block;flex-shrink:0;"></span>
                        <div style="display:flex;flex-direction:column;align-items:flex-start;gap:3px;flex-shrink:0;">
                            <span style="font-size:10px;font-weight:700;letter-spacing:0.6px;text-transform:uppercase;color:#646970;line-height:1;"><?php esc_html_e('Auto Logins', 'je-keap-manager'); ?></span>
                            <div class="jecm-autologin-inline" style="display:flex;align-items:center;gap:0;background:#f0f6fc;border:1px solid #c5d9ed;border-radius:4px;padding:3px 6px;">
                                <select id="jecm-autologin-dest" class="jecm-autologin-dest" style="margin:0;border:none;background:transparent;height:26px;padding:0 4px;font-size:13px;max-width:min(240px,45vw);">
                                    <?php foreach ($fst_autologin_destinations as $dest) :
                                        $si = absint($dest['site_index'] ?? 0);
                                        ?>
                                        <option value="<?php echo esc_attr($dest['path']); ?>"
                                                data-site-index="<?php echo esc_attr((string) $si); ?>"><?php echo esc_html($dest['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="button button-primary jecm-autologin-copy-btn" id="jecm-autologin-copy"
                                        data-contact="<?php echo esc_attr((string) $contact_id); ?>"
                                        data-email="<?php echo esc_attr($email); ?>"
                                        style="height:26px;line-height:24px;padding:0 10px;font-size:12px;margin-left:4px;">
                                    <?php esc_html_e('Copy', 'je-keap-manager'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo $jecm_switch_student_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php
                $jecm_keap_http_n = $keap->get_keap_http_request_count();
                $jecm_qs          = $keap->get_keap_quota_snapshot();
                $jecm_q_avail     = isset($jecm_qs['x-keap-product-quota-available']) ? trim((string) $jecm_qs['x-keap-product-quota-available']) : '';
                $jecm_q_limit     = isset($jecm_qs['x-keap-product-quota-limit']) ? trim((string) $jecm_qs['x-keap-product-quota-limit']) : '';
                $jecm_t_avail     = isset($jecm_qs['x-keap-product-throttle-available']) ? trim((string) $jecm_qs['x-keap-product-throttle-available']) : '';
                $jecm_t_limit     = isset($jecm_qs['x-keap-product-throttle-limit']) ? trim((string) $jecm_qs['x-keap-product-throttle-limit']) : '';
                $jecm_tt_avail    = isset($jecm_qs['x-keap-tenant-throttle-available']) ? trim((string) $jecm_qs['x-keap-tenant-throttle-available']) : '';
                $jecm_tt_limit    = isset($jecm_qs['x-keap-tenant-throttle-limit']) ? trim((string) $jecm_qs['x-keap-tenant-throttle-limit']) : '';

                $jecm_quota_parts = array();
                if ($jecm_q_avail !== '') {
                    $jecm_quota_parts[] = sprintf(
                        /* translators: 1: remaining quota, 2: optional " / limit" suffix */
                        __('Daily quota remaining: %1$s%2$s', 'je-keap-manager'),
                        $jecm_q_avail,
                        $jecm_q_limit !== '' ? ' / ' . $jecm_q_limit : ''
                    );
                }
                if ($jecm_t_avail !== '') {
                    $jecm_quota_parts[] = sprintf(
                        /* translators: 1: remaining throttle count, 2: optional " / limit" suffix */
                        __('Per-minute throttle remaining: %1$s%2$s', 'je-keap-manager'),
                        $jecm_t_avail,
                        $jecm_t_limit !== '' ? ' / ' . $jecm_t_limit : ''
                    );
                }
                if ($jecm_tt_avail !== '') {
                    $jecm_quota_parts[] = sprintf(
                        /* translators: 1: remaining tenant throttle, 2: optional " / limit" suffix */
                        __('Tenant throttle remaining: %1$s%2$s', 'je-keap-manager'),
                        $jecm_tt_avail,
                        $jecm_tt_limit !== '' ? ' / ' . $jecm_tt_limit : ''
                    );
                }

                $jecm_rl_color = '#646970';
                if ($jecm_q_avail !== '' && $jecm_q_limit !== '' && is_numeric($jecm_q_avail) && is_numeric($jecm_q_limit) && (int) $jecm_q_limit > 0) {
                    $jecm_q_pct = (int) $jecm_q_avail / (int) $jecm_q_limit;
                    if ($jecm_q_pct < 0.05) {
                        $jecm_rl_color = '#d63638';
                    } elseif ($jecm_q_pct < 0.15) {
                        $jecm_rl_color = '#db6b26';
                    }
                } elseif ($jecm_t_avail !== '' && $jecm_t_limit !== '' && is_numeric($jecm_t_avail) && is_numeric($jecm_t_limit) && (int) $jecm_t_limit > 0) {
                    $jecm_t_pct = (int) $jecm_t_avail / (int) $jecm_t_limit;
                    if ($jecm_t_pct < 0.10) {
                        $jecm_rl_color = '#d63638';
                    } elseif ($jecm_t_pct < 0.25) {
                        $jecm_rl_color = '#db6b26';
                    }
                }

                $jecm_i2sdk_link = admin_url('admin.php?page=i2sdk-admin');
                $jecm_has_i2sdk  = class_exists('i2sdk_class', false)
                    || class_exists('keap_sdk_class', false)
                    || (defined('I2SDK_VERSION') && I2SDK_VERSION);
                ?>
                <p class="description" style="margin:-12px 0 20px; color:<?php echo esc_attr($jecm_rl_color); ?>;">
                    <?php
                    if (!empty($jecm_quota_parts)) {
                        echo esc_html(implode(' · ', $jecm_quota_parts));
                        echo ' ';
                        echo esc_html(
                            sprintf(
                                /* translators: %d: HTTP request count */
                                __('This page: %d Keap requests.', 'je-keap-manager'),
                                (int) $jecm_keap_http_n
                            )
                        );
                    } else {
                        echo esc_html(
                            sprintf(
                                /* translators: %d: HTTP request count this page load */
                                __('No x-keap-product-* quota headers were returned on recent responses (Keap documents these for REST). This page: %d requests.', 'je-keap-manager'),
                                (int) $jecm_keap_http_n
                            )
                        );
                    }
                    ?>
                    <?php if ($jecm_has_i2sdk) : ?>
                        <?php
                        echo ' ';
                        echo wp_kses_post(
                            sprintf(
                                '<a href="%1$s">%2$s</a>',
                                esc_url($jecm_i2sdk_link),
                                esc_html__('Memberium → Keap Connection (API log).', 'je-keap-manager')
                            )
                        );
                        ?>
                    <?php endif; ?>
                </p>

                <?php
                // Show reach-out alert if any order has balance > 0 (same rule as Balance Due: total_due − total_paid).
                $jecm_find_has_unpaid = false;
                $jecm_fc_payload = (is_array($tx_result) && isset($tx_result['keap_orders_api_response']) && is_array($tx_result['keap_orders_api_response']))
                    ? $tx_result['keap_orders_api_response']
                    : array();
                $jecm_raw_orders = (array) ($jecm_fc_payload['orders'] ?? array());
                foreach ($jecm_raw_orders as $jecm_ord) {
                    if (is_array($jecm_ord) && ((float) ($jecm_ord['total_due'] ?? 0) - (float) ($jecm_ord['total_paid'] ?? 0)) > 0) {
                        $jecm_find_has_unpaid = true;
                        break;
                    }
                }
                // Check whether the reach out tag is already applied in FluentCRM.
                $jecm_reachout_done = false;
                if ($jecm_find_has_unpaid && $email !== '' && function_exists('FluentCrmApi')) {
                    $jecm_ro_tag_raw = get_option('jecm_fc_payment_failed_reachout_tag_id', '');
                    $jecm_ro_tag_ids = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) $jecm_ro_tag_raw)))));
                    if (!empty($jecm_ro_tag_ids)) {
                        $jecm_fc_sub = FluentCrmApi('contacts')->getContact($email);
                        if ($jecm_fc_sub) {
                            $jecm_fc_applied = array_map('intval', $jecm_fc_sub->tags->pluck('id')->all());
                            foreach ($jecm_ro_tag_ids as $jecm_ro_tid) {
                                if (in_array($jecm_ro_tid, $jecm_fc_applied, true)) {
                                    $jecm_reachout_done = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                ?>
                <?php if ($jecm_find_has_unpaid) : ?>
                    <?php if ($jecm_reachout_done) : ?>
                    <div style="background:#eaf6ea;border:1px solid #7ed07e;border-left:4px solid #00a32a;border-radius:4px;padding:12px 16px;margin-bottom:16px;">
                        <strong><?php esc_html_e('✅ Reach Out in Progress', 'je-keap-manager'); ?></strong>
                        <span style="margin-left:8px;color:#1d6a1d;"><?php esc_html_e('Reach Out tag applied and follow-up task created for this student.', 'je-keap-manager'); ?></span>
                    </div>
                    <?php else : ?>
                    <div style="background:#fff3cd;border:1px solid #ffc107;border-left:4px solid #e67e00;border-radius:4px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                        <div>
                            <strong><?php esc_html_e('⚠️ Unpaid Payment Detected', 'je-keap-manager'); ?></strong>
                            <span style="margin-left:8px;color:#856404;"><?php esc_html_e('This student has an outstanding balance on their account.', 'je-keap-manager'); ?></span>
                        </div>
                        <button type="button" class="button button-primary jecm-reachout-btn"
                                data-contact-id="<?php echo esc_attr((string) $contact_id); ?>"
                                data-email="<?php echo esc_attr($email); ?>"
                                data-nonce="<?php echo esc_attr(wp_create_nonce('jecm_reachout')); ?>"
                                style="background:#e67e00;border-color:#e67e00;margin-left:16px;white-space:nowrap;">
                            <?php esc_html_e('📞 Reach Out', 'je-keap-manager'); ?>
                        </button>
                    </div>
                    <script>
                    (function() {
                        var reachoutAjax = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
                        document.addEventListener('click', function(e) {
                            var btn = e.target.closest('.jecm-reachout-btn');
                            if (!btn) {
                                return;
                            }
                            if (!window.confirm(<?php echo wp_json_encode(__('Apply "Payment Failed — Reach Out" tag to this contact in FluentCRM?', 'je-keap-manager')); ?>)) {
                                return;
                            }
                            btn.disabled = true;
                            var origLabel = btn.textContent;
                            btn.textContent = <?php echo wp_json_encode(__('Sending…', 'je-keap-manager')); ?>;

                            var fd = new FormData();
                            fd.append('action', 'jecm_reachout');
                            fd.append('nonce', btn.getAttribute('data-nonce') || '');
                            fd.append('contact_id', btn.getAttribute('data-contact-id') || '');
                            fd.append('email', btn.getAttribute('data-email') || '');

                            fetch(reachoutAjax, { method: 'POST', credentials: 'same-origin', body: fd })
                                .then(function(r) { return r.json(); })
                                .then(function(res) {
                                    if (res && res.success) {
                                        var alertDiv = btn.closest('div[style]');
                                        if (alertDiv) {
                                            alertDiv.style.background = '#eaf6ea';
                                            alertDiv.style.borderColor = '#7ed07e';
                                            alertDiv.style.borderLeftColor = '#00a32a';
                                            alertDiv.innerHTML = '<strong><?php echo esc_js(__('✅ Reach Out in Progress', 'je-keap-manager')); ?></strong>'
                                                + '<span style="margin-left:8px;color:#1d6a1d;"><?php echo esc_js(__('Reach Out tag applied and follow-up task created for this student.', 'je-keap-manager')); ?></span>';
                                        }
                                    } else {
                                        btn.disabled = false;
                                        btn.textContent = origLabel;
                                        var msg = (res && res.data && res.data.message) ? res.data.message
                                            : (typeof (res && res.data) === 'string' ? res.data : <?php echo wp_json_encode(__('Unknown error', 'je-keap-manager')); ?>);
                                        window.alert(<?php echo wp_json_encode(__('Error: ', 'je-keap-manager')); ?> + msg);
                                    }
                                })
                                .catch(function() {
                                    btn.disabled = false;
                                    btn.textContent = origLabel;
                                    window.alert(<?php echo wp_json_encode(__('Request failed.', 'je-keap-manager')); ?>);
                                });
                        });
                    })();
                    </script>
                    <?php endif; ?>
                <?php endif; ?>

                <h3>Member Info</h3>
                <table class="widefat striped" style="margin-bottom:28px;">
                    <thead>
                        <tr><th>Label</th><th>Value</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($member_info_rows as $row) : ?>
                        <tr>
                            <td><?php echo esc_html($row['label']); ?></td>
                            <td>
                                <?php if (!empty($row['editable'])) : ?>
                                    <?php
                                    $exp_raw   = isset($row['raw']) ? (string) $row['raw'] : '';
                                    $exp_init  = '';
                                    if ($exp_raw !== '' && ($exp_ts = strtotime($exp_raw))) {
                                        $exp_init = date('m/d/Y', $exp_ts);
                                    }
                                    ?>
                                    <span class="jecm-exp-display"><?php echo esc_html($row['value']); ?></span>
                                    <a href="#" class="jecm-exp-edit-btn" style="margin-left:8px;font-size:11px;">[edit]</a>
                                    <span class="jecm-exp-form" style="display:none;margin-left:8px;">
                                        <input type="text"
                                               class="jecm-exp-datepicker"
                                               data-field="<?php echo esc_attr($row['field_key']); ?>"
                                               data-contact="<?php echo esc_attr((string) $contact_id); ?>"
                                               data-email="<?php echo esc_attr($email); ?>"
                                               data-nonce="<?php echo esc_attr(wp_create_nonce('jecm_update_expiry')); ?>"
                                               value="<?php echo esc_attr($exp_init); ?>"
                                               style="width:130px;">
                                        <button type="button" class="button button-small jecm-exp-save" style="margin-left:4px;">Save</button>
                                        <button type="button" class="button button-small jecm-exp-clear" style="margin-left:4px;">Clear Date</button>
                                        <a href="#" class="jecm-exp-cancel" style="margin-left:6px;font-size:11px;">cancel</a>
                                    </span>
                                <?php elseif (($row['field_key'] ?? '') === 'Password4Microsites') : ?>
                                    <?php echo esc_html($row['value']); ?>
                                    <?php
                                    $jecm_pw_copy = '';
                                    if (!empty($row['raw'])) {
                                        $jecm_pw_copy = trim((string) $row['raw']);
                                    }
                                    if ($jecm_pw_copy === '' && isset($row['value']) && (string) $row['value'] !== '' && (string) $row['value'] !== '—') {
                                        $jecm_pw_copy = trim((string) $row['value']);
                                    }
                                    if (strtolower($jecm_pw_copy) === '(empty)') {
                                        $jecm_pw_copy = '';
                                    }
                                    ?>
                                    <button type="button"
                                            class="button button-small jecm-copy-microsite-pw"
                                            style="margin-left:8px;vertical-align:middle;"
                                            data-copy="<?php echo esc_attr($jecm_pw_copy); ?>"><?php esc_html_e('Copy', 'je-keap-manager'); ?></button>
                                <?php else : ?>
                                    <?php echo esc_html($row['value']); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <script>
                jQuery(function($) {
                    function jecmCopyText(text, btn) {
                        var done = function() {
                            var t = btn.textContent;
                            btn.textContent = <?php echo wp_json_encode(__('Copied!', 'je-keap-manager')); ?>;
                            setTimeout(function() { btn.textContent = t; }, 1500);
                        };
                        if (!text) {
                            window.alert(<?php echo wp_json_encode(__('Nothing to copy.', 'je-keap-manager')); ?>);
                            return;
                        }
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text).then(done).catch(function() {
                                try {
                                    var ta = document.createElement('textarea');
                                    ta.value = text;
                                    document.body.appendChild(ta);
                                    ta.select();
                                    document.execCommand('copy');
                                    document.body.removeChild(ta);
                                    done();
                                } catch (e2) {
                                    window.alert(<?php echo wp_json_encode(__('Copy failed.', 'je-keap-manager')); ?>);
                                }
                            });
                        } else {
                            try {
                                var ta2 = document.createElement('textarea');
                                ta2.value = text;
                                document.body.appendChild(ta2);
                                ta2.select();
                                document.execCommand('copy');
                                document.body.removeChild(ta2);
                                done();
                            } catch (e3) {
                                window.alert(<?php echo wp_json_encode(__('Copy failed.', 'je-keap-manager')); ?>);
                            }
                        }
                    }
                    $(document).on('click', '.jecm-copy-microsite-pw', function() {
                        jecmCopyText(this.getAttribute('data-copy') || '', this);
                    });
                    $('.jecm-exp-datepicker').datepicker({ dateFormat: 'mm/dd/yy' });
                    $(document).on('click', '.jecm-exp-edit-btn', function(e) {
                        e.preventDefault();
                        var $td = $(this).closest('td');
                        $td.find('.jecm-exp-display, .jecm-exp-edit-btn').hide();
                        $td.find('.jecm-exp-form').show();
                    });
                    $(document).on('click', '.jecm-exp-cancel', function(e) {
                        e.preventDefault();
                        var $td = $(this).closest('td');
                        $td.find('.jecm-exp-form').hide();
                        $td.find('.jecm-exp-display, .jecm-exp-edit-btn').show();
                    });
                    $(document).on('click', '.jecm-exp-save', function(e) {
                        e.preventDefault();
                        var $btn    = $(this);
                        var $input  = $btn.siblings('.jecm-exp-datepicker');
                        var $td     = $btn.closest('td');
                        var field   = $input.data('field');
                        var contact = $input.data('contact');
                        var nonce   = $input.data('nonce');
                        var email   = $input.data('email');
                        var dateVal = $input.val();
                        if (!dateVal) {
                            return;
                        }
                        $btn.text('Saving…').prop('disabled', true);
                        $.post(ajaxurl, {
                            action:     'jecm_update_expiry',
                            nonce:      nonce,
                            contact_id: contact,
                            field:      field,
                            date:       dateVal,
                            email:      email
                        }, function(resp) {
                            if (resp.success) {
                                $td.find('.jecm-exp-display').text(resp.data.display);
                                $td.find('.jecm-exp-form').hide();
                                $td.find('.jecm-exp-display, .jecm-exp-edit-btn').show();
                            } else {
                                window.alert('Failed: ' + ((resp.data && resp.data.message) ? resp.data.message : 'Unknown error'));
                            }
                            $btn.text('Save').prop('disabled', false);
                        }).fail(function() {
                            window.alert('Request failed.');
                            $btn.text('Save').prop('disabled', false);
                        });
                    });
                    $(document).on('click', '.jecm-exp-clear', function(e) {
                        e.preventDefault();
                        var $clear = $(this);
                        var $form = $clear.closest('.jecm-exp-form');
                        var $td = $clear.closest('td');
                        var $input = $form.find('.jecm-exp-datepicker');
                        var $save = $form.find('.jecm-exp-save');
                        var field = $input.data('field');
                        var contact = $input.data('contact');
                        var nonce = $input.data('nonce');
                        var email = $input.data('email');
                        $save.add($clear).prop('disabled', true);
                        $clear.text('Clearing…');
                        $.post(ajaxurl, {
                            action: 'jecm_update_expiry',
                            nonce: nonce,
                            contact_id: contact,
                            field: field,
                            date: '',
                            email: email
                        }, function(resp) {
                            if (resp.success) {
                                $td.find('.jecm-exp-display').text(resp.data.display);
                                $input.val('');
                                $form.hide();
                                $td.find('.jecm-exp-display, .jecm-exp-edit-btn').show();
                            } else {
                                window.alert('Failed: ' + ((resp.data && resp.data.message) ? resp.data.message : 'Unknown error'));
                            }
                            $save.prop('disabled', false);
                            $clear.prop('disabled', false).text('Clear Date');
                        }).fail(function() {
                            window.alert('Request failed.');
                            $save.prop('disabled', false);
                            $clear.prop('disabled', false).text('Clear Date');
                        });
                    });
                });
                </script>

                <?php
                $jecm_tag_login_map = array();
                foreach ($fst_autologin_destinations as $jecm_dest_login) {
                    $jecm_tids = isset($jecm_dest_login['tag_ids']) && is_array($jecm_dest_login['tag_ids'])
                        ? $jecm_dest_login['tag_ids'] : array();
                    foreach ($jecm_tids as $jecm_tid) {
                        $jecm_tid = (int) $jecm_tid;
                        if ($jecm_tid > 0) {
                            $jecm_tag_login_map[ $jecm_tid ] = $jecm_dest_login;
                        }
                    }
                }
                ?>
                <script>
                window.jecmTagLoginMap = <?php echo wp_json_encode($jecm_tag_login_map); ?>;
                window.jecmSites = <?php echo wp_json_encode(array_values($fst_autologin_sites)); ?>;
                window.jecmContactId = <?php echo (int) $contact_id; ?>;
                window.jecmEmail = <?php echo wp_json_encode($email); ?>;
                </script>

                <h3>Tags</h3>
                <div id="jecm-tag-removed-notice" class="notice notice-success inline" style="display:none;padding:8px 12px;margin:8px 0 12px;">
                    <p style="margin:0;"></p>
                </div>
                <?php if (!$tags_result || !$tags_result['success']) : ?>
                    <p class="description"><?php echo esc_html($tags_result['message'] ?? 'Could not load tags.'); ?></p>
                <?php else : ?>
                    <?php
                    $jecm_existing_tag_ids = array();
                    foreach ($tags_result['tags'] as $jecm_trow) {
                        $jecm_existing_tag_ids[] = (int) $jecm_trow['id'];
                    }
                    ?>
                    <table class="widefat striped" id="jecm-contact-tags-table" style="margin-bottom:12px;">
                        <thead>
                            <tr>
                                <th style="width:32px;vertical-align:middle;text-align:center;padding:8px 4px 8px 10px;"><input type="checkbox" id="jecm-tags-select-all" title="<?php echo esc_attr__('Select all', 'je-keap-manager'); ?>"></th>
                                <th><?php esc_html_e('Tag ID', 'je-keap-manager'); ?></th>
                                <th><?php esc_html_e('Name', 'je-keap-manager'); ?></th>
                                <th style="width:110px;white-space:nowrap;"><?php esc_html_e('Action', 'je-keap-manager'); ?></th>
                                <th style="min-width:140px;"><?php esc_html_e('Login', 'je-keap-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="jecm-contact-tags-tbody">
                        <?php foreach ($tags_result['tags'] as $t) : ?>
                            <tr>
                                <td style="vertical-align:middle;text-align:center;padding:8px 4px 8px 10px;">
                                    <input type="checkbox" class="jecm-tag-select"
                                           value="<?php echo esc_attr((string) $t['id']); ?>"
                                           data-tag-name="<?php echo esc_attr($t['name']); ?>">
                                </td>
                                <td><?php echo esc_html((string) $t['id']); ?></td>
                                <td><?php echo esc_html($t['name']); ?></td>
                                <td style="vertical-align:middle;">
                                    <button type="button" class="button button-small button-link-delete jecm-remove-tag"
                                            data-tag-id="<?php echo esc_attr((string) $t['id']); ?>"
                                            data-tag-name="<?php echo esc_attr($t['name']); ?>"
                                            data-contact-id="<?php echo esc_attr((string) $contact_id); ?>"
                                            data-email="<?php echo esc_attr($email); ?>"
                                            data-contact-name="<?php echo esc_attr($fst_contact_display); ?>"
                                            data-nonce="<?php echo esc_attr(wp_create_nonce('jecm_remove_tag')); ?>">
                                        <?php esc_html_e('Remove', 'je-keap-manager'); ?>
                                    </button>
                                </td>
                                <td style="vertical-align:middle;">
                                    <span class="jecm-tag-login-btn-wrap" data-tag-id="<?php echo esc_attr((string) $t['id']); ?>"></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <script>
                    (function() {
                        function jecmCopyTagLoginUrl(btn, url) {
                            var done = function() {
                                var orig = btn.textContent;
                                btn.textContent = '✅ <?php echo esc_js(__('Copied!', 'je-keap-manager')); ?>';
                                setTimeout(function() { btn.textContent = orig; }, 1500);
                            };
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                navigator.clipboard.writeText(url).then(done).catch(function() {
                                    jecmCopyTagLoginFallback(url, done);
                                });
                            } else {
                                jecmCopyTagLoginFallback(url, done);
                            }
                        }
                        function jecmCopyTagLoginFallback(url, done) {
                            try {
                                var ta = document.createElement('textarea');
                                ta.value = url;
                                document.body.appendChild(ta);
                                ta.select();
                                document.execCommand('copy');
                                document.body.removeChild(ta);
                                done();
                            } catch (e2) {
                                window.alert(<?php echo wp_json_encode(__('Copy failed.', 'je-keap-manager')); ?>);
                            }
                        }
                        function jecmPopulateTagLoginButtons() {
                            var map = (typeof window.jecmTagLoginMap === 'object' && window.jecmTagLoginMap) ? window.jecmTagLoginMap : {};
                            var sites = typeof window.jecmSites !== 'undefined' ? window.jecmSites : [];
                            var cid = window.jecmContactId;
                            var em = typeof window.jecmEmail !== 'undefined' ? window.jecmEmail : '';
                            document.querySelectorAll('.jecm-tag-login-btn-wrap').forEach(function(wrap) {
                                if (wrap.querySelector('.jecm-tag-copy-login')) {
                                    return;
                                }
                                var tagId = parseInt(wrap.getAttribute('data-tag-id'), 10);
                                if (isNaN(tagId) || !map[tagId]) {
                                    return;
                                }
                                var dest = map[tagId];
                                var si = dest.site_index != null ? parseInt(String(dest.site_index), 10) : 0;
                                if (isNaN(si)) {
                                    si = 0;
                                }
                                var site = sites[si] || sites[0];
                                if (!site || !site.url) {
                                    return;
                                }
                                var url = String(site.url).replace(/\/$/, '')
                                    + '/?memb_autologin=yes&auth_key=' + encodeURIComponent(site.auth_key || '')
                                    + '&Id=' + encodeURIComponent(String(cid))
                                    + '&Email=' + encodeURIComponent(String(em))
                                    + '&redir=' + encodeURIComponent(String(dest.path || ''));
                                var btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'button button-small jecm-tag-copy-login';
                                btn.textContent = '📋 ' + (dest.title || '');
                                btn.setAttribute('data-url', url);
                                btn.addEventListener('click', function() {
                                    jecmCopyTagLoginUrl(btn, url);
                                });
                                wrap.appendChild(btn);
                            });
                        }
                        window.jecmPopulateTagLoginButtons = jecmPopulateTagLoginButtons;
                        jecmPopulateTagLoginButtons();
                    })();
                    </script>
                    <div id="jecm-bulk-tag-actions" style="margin-bottom:10px;display:flex;align-items:center;gap:10px;">
                        <button type="button" class="button button-link-delete" id="jecm-remove-selected-tags" disabled>
                            <?php esc_html_e('Remove Selected', 'je-keap-manager'); ?>
                        </button>
                        <span id="jecm-bulk-tag-count" style="font-size:12px;color:#646970;"></span>
                    </div>
                    <p class="description" id="jecm-tags-empty-hint" style="margin:0 0 12px;<?php echo !empty($tags_result['tags']) ? 'display:none;' : ''; ?>">
                        <?php esc_html_e('No tags on this contact.', 'je-keap-manager'); ?>
                    </p>
                    <button type="button" class="button button-secondary" id="jecm-add-tag-btn"
                            data-contact-id="<?php echo esc_attr((string) $contact_id); ?>"
                            data-email="<?php echo esc_attr($email); ?>"
                            data-contact-name="<?php echo esc_attr($fst_contact_display); ?>"
                            data-nonce="<?php echo esc_attr(wp_create_nonce('jecm_add_tag')); ?>"
                            data-remove-nonce="<?php echo esc_attr(wp_create_nonce('jecm_remove_tag')); ?>"
                            style="margin-bottom:28px;">
                        + <?php esc_html_e('Add Tag', 'je-keap-manager'); ?>
                    </button>

                    <div id="jecm-add-tag-backdrop" style="display:none;position:fixed;inset:0;z-index:100000;align-items:center;justify-content:center;background:rgba(0,0,0,0.35);padding:20px;box-sizing:border-box;"
                         aria-hidden="true">
                        <div id="jecm-add-tag-modal" role="dialog" aria-modal="true" aria-labelledby="jecm-add-tag-modal-title"
                             style="background:#fff;border-radius:4px;box-shadow:0 5px 30px rgba(0,0,0,0.2);max-width:480px;width:100%;max-height:90vh;display:flex;flex-direction:column;"
                             onclick="event.stopPropagation();">
                            <div style="padding:12px 16px;border-bottom:1px solid #dcdcde;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <strong id="jecm-add-tag-modal-title" style="font-size:14px;"><?php esc_html_e('Add Keap Tag', 'je-keap-manager'); ?></strong>
                                <button type="button" id="jecm-add-tag-close" class="button button-link" style="text-decoration:none;font-size:22px;line-height:1;padding:0 6px;min-height:0;" aria-label="<?php echo esc_attr__('Close', 'je-keap-manager'); ?>">&times;</button>
                            </div>
                            <div style="padding:12px 16px;flex:1;min-height:0;display:flex;flex-direction:column;">
                                <label for="jecm-add-tag-search" class="screen-reader-text"><?php esc_html_e('Search tags', 'je-keap-manager'); ?></label>
                                <input type="text" id="jecm-add-tag-search" placeholder="<?php echo esc_attr__('Search tags…', 'je-keap-manager'); ?>" style="width:100%;margin-bottom:10px;">
                                <div id="jecm-tag-list" style="max-height:320px;overflow:auto;border:1px solid #dcdcde;padding:8px;border-radius:4px;background:#fcfcfc;flex:1;min-height:120px;">
                                </div>
                            </div>
                            <div style="padding:12px 16px;border-top:1px solid #dcdcde;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                                <span class="jecm-refresh-tags" role="button" tabindex="0"
                                      style="cursor:pointer;font-size:12px;color:#2271b1;user-select:none;">
                                    <?php esc_html_e('↻ Refresh tag list', 'je-keap-manager'); ?>
                                </span>
                                <button type="button" class="button button-primary" id="jecm-add-tag-apply" disabled>
                                    <?php esc_html_e('Apply', 'je-keap-manager'); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <script>
                    (function() {
                        var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
                        var jecmAllTags = null;
                        var jecmExistingTagIds = <?php echo wp_json_encode(array_values($jecm_existing_tag_ids)); ?>;
                        window.jecmContactExistingTagIds = jecmExistingTagIds;

                        function jecmCloseAddTagModal() {
                            var b = document.getElementById('jecm-add-tag-backdrop');
                            if (b) {
                                b.style.display = 'none';
                                b.setAttribute('aria-hidden', 'true');
                            }
                        }

                        function jecmUpdateApplyEnabled() {
                            var btn = document.getElementById('jecm-add-tag-apply');
                            var list = document.getElementById('jecm-tag-list');
                            if (!btn || !list) {
                                return;
                            }
                            var any = false;
                            list.querySelectorAll('input.jecm-tag-pick').forEach(function(cb) {
                                if (cb.checked && !cb.disabled) {
                                    any = true;
                                }
                            });
                            btn.disabled = !any;
                        }

                        function jecmRenderTagPickList(filterQ) {
                            var list = document.getElementById('jecm-tag-list');
                            if (!list || !jecmAllTags) {
                                return;
                            }
                            var q = (filterQ || '').toLowerCase().trim();
                            var tags = jecmAllTags.filter(function(t) {
                                return !q || (t.name && String(t.name).toLowerCase().indexOf(q) !== -1);
                            });
                            tags.sort(function(a, b) {
                                return String(a.name || '').localeCompare(String(b.name || ''));
                            });
                            list.innerHTML = '';
                            if (!tags.length) {
                                list.innerHTML = '<p class="description" style="margin:8px;"><?php echo esc_js(__('No tags match your search.', 'je-keap-manager')); ?></p>';
                                jecmUpdateApplyEnabled();
                                return;
                            }
                            tags.forEach(function(t) {
                                var id = parseInt(String(t.id), 10);
                                if (isNaN(id)) {
                                    return;
                                }
                                var existing = jecmExistingTagIds.indexOf(id) !== -1;
                                var row = document.createElement('div');
                                row.style.cssText = 'display:flex;align-items:flex-start;gap:8px;padding:6px 4px;border-bottom:1px solid #f0f0f1;';
                                if (existing) {
                                    row.style.opacity = '0.6';
                                }
                                var wrap = document.createElement('label');
                                wrap.style.cssText = 'display:flex;align-items:flex-start;gap:8px;cursor:' + (existing ? 'default' : 'pointer') + ';flex:1;margin:0;font-size:13px;line-height:1.4;';
                                var cb = document.createElement('input');
                                cb.type = 'checkbox';
                                cb.className = 'jecm-tag-pick';
                                cb.value = String(id);
                                cb.setAttribute('data-tag-name', t.name != null ? String(t.name) : '');
                                if (existing) {
                                    cb.checked = true;
                                    cb.disabled = true;
                                }
                                var span = document.createElement('span');
                                span.textContent = (t.name != null && String(t.name) !== '') ? String(t.name) : ('Tag #' + id);
                                if (existing) {
                                    var hint = document.createElement('span');
                                    hint.style.cssText = 'font-size:11px;color:#787c82;margin-left:6px;display:block;';
                                    hint.textContent = '<?php echo esc_js(__('(already on contact)', 'je-keap-manager')); ?>';
                                    span.appendChild(hint);
                                }
                                wrap.appendChild(cb);
                                wrap.appendChild(span);
                                row.appendChild(wrap);
                                list.appendChild(row);
                            });
                            jecmUpdateApplyEnabled();
                        }

                        function jecmFetchAllTags(forceRefresh) {
                            var list = document.getElementById('jecm-tag-list');
                            var openBtn = document.getElementById('jecm-add-tag-btn');
                            if (!list || !openBtn) {
                                return;
                            }

                            list.innerHTML = '<p class="description" style="margin:8px;"><?php echo esc_js(__('Loading…', 'je-keap-manager')); ?></p>';
                            var body = new URLSearchParams();
                            body.set('action', 'jecm_get_all_tags');
                            body.set('nonce', openBtn.getAttribute('data-nonce') || '');
                            if (forceRefresh) {
                                body.set('refresh', '1');
                            }
                            return fetch(ajaxUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                                body: body.toString()
                            }).then(function(r) { return r.json(); }).then(function(data) {
                                if (!data || !data.success || !data.data || !Array.isArray(data.data.tags)) {
                                    var msg = (data && data.data && data.data.message) ? data.data.message
                                        : (typeof (data && data.data) === 'string' ? data.data : '<?php echo esc_js(__('Could not load tags.', 'je-keap-manager')); ?>');
                                    list.innerHTML = '<p class="description" style="margin:8px;color:#b32d2e;">' + msg + '</p>';
                                    return;
                                }
                                jecmAllTags = data.data.tags;
                                var search = document.getElementById('jecm-add-tag-search');
                                jecmRenderTagPickList(search ? search.value : '');
                            }).catch(function() {
                                list.innerHTML = '<p class="description" style="margin:8px;color:#b32d2e;"><?php echo esc_js(__('Request failed.', 'je-keap-manager')); ?></p>';
                            });
                        }

                        function jecmAppendContactTagRow(tagId, tagName) {
                            var tbody = document.getElementById('jecm-contact-tags-tbody');
                            var openBtn = document.getElementById('jecm-add-tag-btn');
                            if (!tbody || !openBtn) {
                                return;
                            }
                            var hint = document.getElementById('jecm-tags-empty-hint');
                            if (hint) {
                                hint.style.display = 'none';
                            }
                            var tr = document.createElement('tr');
                            var tdCb = document.createElement('td');
                            tdCb.style.cssText = 'vertical-align:middle;text-align:center;padding:8px 4px 8px 10px;';
                            var cb = document.createElement('input');
                            cb.type = 'checkbox';
                            cb.className = 'jecm-tag-select';
                            cb.value = String(tagId);
                            cb.setAttribute('data-tag-name', tagName != null ? String(tagName) : '');
                            tdCb.appendChild(cb);
                            var tdId = document.createElement('td');
                            tdId.textContent = String(tagId);
                            var tdName = document.createElement('td');
                            tdName.textContent = tagName;
                            var tdAct = document.createElement('td');
                            tdAct.style.verticalAlign = 'middle';
                            var rm = document.createElement('button');
                            rm.type = 'button';
                            rm.className = 'button button-small button-link-delete jecm-remove-tag';
                            rm.setAttribute('data-tag-id', String(tagId));
                            rm.setAttribute('data-tag-name', tagName);
                            rm.setAttribute('data-contact-id', openBtn.getAttribute('data-contact-id') || '');
                            rm.setAttribute('data-email', openBtn.getAttribute('data-email') || '');
                            rm.setAttribute('data-contact-name', openBtn.getAttribute('data-contact-name') || '');
                            rm.setAttribute('data-nonce', openBtn.getAttribute('data-remove-nonce') || '');
                            rm.textContent = '<?php echo esc_js(__('Remove', 'je-keap-manager')); ?>';
                            tdAct.appendChild(rm);
                            var tdLogin = document.createElement('td');
                            tdLogin.style.verticalAlign = 'middle';
                            var wrapLogin = document.createElement('span');
                            wrapLogin.className = 'jecm-tag-login-btn-wrap';
                            wrapLogin.setAttribute('data-tag-id', String(tagId));
                            tdLogin.appendChild(wrapLogin);
                            tr.appendChild(tdCb);
                            tr.appendChild(tdId);
                            tr.appendChild(tdName);
                            tr.appendChild(tdAct);
                            tr.appendChild(tdLogin);
                            tbody.appendChild(tr);
                            if (typeof window.jecmUpdateBulkTagControls === 'function') {
                                window.jecmUpdateBulkTagControls();
                            }
                            if (typeof window.jecmPopulateTagLoginButtons === 'function') {
                                window.jecmPopulateTagLoginButtons();
                            }
                        }

                        var openBtnEl = document.getElementById('jecm-add-tag-btn');
                        if (openBtnEl) {
                            openBtnEl.addEventListener('click', function() {
                                var backdrop = document.getElementById('jecm-add-tag-backdrop');
                                if (!backdrop) {
                                    return;
                                }
                                backdrop.style.display = 'flex';
                                backdrop.setAttribute('aria-hidden', 'false');
                                if (jecmAllTags) {
                                    var s = document.getElementById('jecm-add-tag-search');
                                    jecmRenderTagPickList(s ? s.value : '');
                                } else {
                                    jecmFetchAllTags(false);
                                }
                            });
                        }

                        var closeBtn = document.getElementById('jecm-add-tag-close');
                        if (closeBtn) {
                            closeBtn.addEventListener('click', jecmCloseAddTagModal);
                        }
                        var backdropEl = document.getElementById('jecm-add-tag-backdrop');
                        if (backdropEl) {
                            backdropEl.addEventListener('click', function(ev) {
                                if (ev.target === backdropEl) {
                                    jecmCloseAddTagModal();
                                }
                            });
                        }

                        var searchInput = document.getElementById('jecm-add-tag-search');
                        if (searchInput) {
                            searchInput.addEventListener('input', function() {
                                if (jecmAllTags) {
                                    jecmRenderTagPickList(searchInput.value);
                                }
                            });
                        }

                        var tagListEl = document.getElementById('jecm-tag-list');
                        if (tagListEl) {
                            tagListEl.addEventListener('change', function(ev) {
                                if (ev.target && ev.target.classList && ev.target.classList.contains('jecm-tag-pick')) {
                                    jecmUpdateApplyEnabled();
                                }
                            });
                        }

                        var refreshEl = document.querySelector('.jecm-refresh-tags');
                        if (refreshEl) {
                            var doRefresh = function(ev) {
                                ev.preventDefault();
                                jecmAllTags = null;
                                jecmFetchAllTags(true);
                            };
                            refreshEl.addEventListener('click', doRefresh);
                            refreshEl.addEventListener('keydown', function(ev) {
                                if (ev.key === 'Enter' || ev.key === ' ') {
                                    doRefresh(ev);
                                }
                            });
                        }

                        var applyBtn = document.getElementById('jecm-add-tag-apply');
                        if (applyBtn && openBtnEl) {
                            applyBtn.addEventListener('click', function() {
                                var list = document.getElementById('jecm-tag-list');
                                if (!list || applyBtn.disabled) {
                                    return;
                                }
                                var ids = [];
                                var names = [];
                                list.querySelectorAll('input.jecm-tag-pick').forEach(function(cb) {
                                    if (!cb.checked || cb.disabled) {
                                        return;
                                    }
                                    var idNum = parseInt(cb.value, 10);
                                    if (isNaN(idNum)) {
                                        return;
                                    }
                                    ids.push(idNum);
                                    names.push(cb.getAttribute('data-tag-name') || '');
                                });
                                if (!ids.length) {
                                    return;
                                }
                                applyBtn.disabled = true;
                                var body = new URLSearchParams();
                                body.set('action', 'jecm_add_tag');
                                body.set('nonce', openBtnEl.getAttribute('data-nonce') || '');
                                body.set('contact_id', openBtnEl.getAttribute('data-contact-id') || '');
                                body.set('email', openBtnEl.getAttribute('data-email') || '');
                                body.set('contact_name', openBtnEl.getAttribute('data-contact-name') || '');
                                body.set('tag_ids', JSON.stringify(ids));
                                body.set('tag_names', JSON.stringify(names));
                                fetch(ajaxUrl, {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                                    body: body.toString()
                                }).then(function(r) { return r.json(); }).then(function(data) {
                                    applyBtn.disabled = false;
                                    if (!data || !data.success) {
                                        var msg = (data && data.data && data.data.message) ? data.data.message
                                            : (typeof (data && data.data) === 'string' ? data.data : '<?php echo esc_js(__('Apply failed.', 'je-keap-manager')); ?>');
                                        window.alert(msg);
                                        return;
                                    }
                                    ids.forEach(function(id, i) {
                                        if (jecmExistingTagIds.indexOf(id) === -1) {
                                            jecmExistingTagIds.push(id);
                                        }
                                        jecmAppendContactTagRow(id, names[i] || '');
                                    });
                                    jecmCloseAddTagModal();
                                    var notice = document.getElementById('jecm-tag-removed-notice');
                                    if (notice) {
                                        var p = notice.querySelector('p');
                                        if (p) {
                                            p.textContent = '<?php echo esc_js(__('Tag(s) added.', 'je-keap-manager')); ?>';
                                        }
                                        notice.style.display = 'block';
                                        window.setTimeout(function() {
                                            notice.style.display = 'none';
                                        }, 3000);
                                    }
                                }).catch(function() {
                                    applyBtn.disabled = false;
                                    window.alert('<?php echo esc_js(__('Request failed.', 'je-keap-manager')); ?>');
                                });
                            });
                        }
                    })();
                    </script>
                    <script>
                    (function() {
                        var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;

                        function jecmGetRemoveTagNonce() {
                            var firstRm = document.querySelector('#jecm-contact-tags-tbody .jecm-remove-tag');
                            if (firstRm) {
                                return firstRm.getAttribute('data-nonce') || '';
                            }
                            var addBtn = document.getElementById('jecm-add-tag-btn');
                            return addBtn ? (addBtn.getAttribute('data-remove-nonce') || '') : '';
                        }

                        function jecmUpdateBulkBtn() {
                            var bulkBtn = document.getElementById('jecm-remove-selected-tags');
                            var countEl = document.getElementById('jecm-bulk-tag-count');
                            var selAll = document.getElementById('jecm-tags-select-all');
                            var boxes = document.querySelectorAll('#jecm-contact-tags-tbody .jecm-tag-select');
                            var n = 0;
                            boxes.forEach(function(cb) {
                                if (cb.checked) {
                                    n++;
                                }
                            });
                            if (bulkBtn) {
                                bulkBtn.disabled = n === 0;
                            }
                            if (countEl) {
                                var selTpl = <?php echo wp_json_encode(__('%d selected', 'je-keap-manager')); ?>;
                                countEl.textContent = n ? selTpl.replace('%d', String(n)) : '';
                                countEl.style.display = n ? '' : 'none';
                            }
                            if (selAll) {
                                var total = boxes.length;
                                if (!total) {
                                    selAll.checked = false;
                                    selAll.indeterminate = false;
                                } else {
                                    selAll.checked = n === total;
                                    selAll.indeterminate = n > 0 && n < total;
                                }
                            }
                        }
                        window.jecmUpdateBulkTagControls = jecmUpdateBulkBtn;

                        document.addEventListener('change', function(e) {
                            var t = e.target;
                            if (t && t.id === 'jecm-tags-select-all') {
                                var on = t.checked;
                                document.querySelectorAll('#jecm-contact-tags-tbody .jecm-tag-select').forEach(function(cb) {
                                    cb.checked = on;
                                });
                                jecmUpdateBulkBtn();
                                return;
                            }
                            if (t && t.classList && t.classList.contains('jecm-tag-select')) {
                                jecmUpdateBulkBtn();
                            }
                        });

                        var bulkRm = document.getElementById('jecm-remove-selected-tags');
                        if (bulkRm) {
                            bulkRm.addEventListener('click', function() {
                                var openBtn = document.getElementById('jecm-add-tag-btn');
                                if (!openBtn || bulkRm.disabled) {
                                    return;
                                }
                                var checked = document.querySelectorAll('#jecm-contact-tags-tbody .jecm-tag-select:checked');
                                var ids = [];
                                var names = [];
                                checked.forEach(function(cb) {
                                    var idNum = parseInt(cb.value, 10);
                                    if (!isNaN(idNum)) {
                                        ids.push(idNum);
                                        names.push(cb.getAttribute('data-tag-name') || '');
                                    }
                                });
                                if (!ids.length) {
                                    return;
                                }
                                var nLab = ids.length;
                                if (!confirm(<?php echo wp_json_encode(__('Remove %d tag(s) from this contact?', 'je-keap-manager')); ?>.replace('%d', String(nLab)))) {
                                    return;
                                }
                                bulkRm.disabled = true;
                                var body = new URLSearchParams();
                                body.set('action', 'jecm_remove_tags_bulk');
                                body.set('nonce', jecmGetRemoveTagNonce());
                                body.set('contact_id', openBtn.getAttribute('data-contact-id') || '');
                                body.set('email', openBtn.getAttribute('data-email') || '');
                                body.set('contact_name', openBtn.getAttribute('data-contact-name') || '');
                                body.set('tag_ids', JSON.stringify(ids));
                                body.set('tag_names', JSON.stringify(names));
                                fetch(ajaxUrl, {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                                    body: body.toString()
                                }).then(function(r) { return r.json(); }).then(function(data) {
                                    if (!data || !data.success) {
                                        var msg = (data && data.data && data.data.message) ? data.data.message
                                            : (typeof (data && data.data) === 'string' ? data.data : 'Remove failed.');
                                        window.alert(msg);
                                        bulkRm.disabled = false;
                                        return;
                                    }
                                    checked.forEach(function(cb) {
                                        var row = cb.closest('tr');
                                        if (row && row.parentNode) {
                                            row.parentNode.removeChild(row);
                                        }
                                    });
                                    var idSet = {};
                                    ids.forEach(function(id) { idSet[id] = true; });
                                    var ext = window.jecmContactExistingTagIds;
                                    if (ext && Array.isArray(ext)) {
                                        for (var i = ext.length - 1; i >= 0; i--) {
                                            if (idSet[ext[i]]) {
                                                ext.splice(i, 1);
                                            }
                                        }
                                    }
                                    jecmUpdateBulkBtn();
                                    var selAll = document.getElementById('jecm-tags-select-all');
                                    if (selAll) {
                                        selAll.checked = false;
                                        selAll.indeterminate = false;
                                    }
                                    var notice = document.getElementById('jecm-tag-removed-notice');
                                    if (notice) {
                                        var p = notice.querySelector('p');
                                        if (p) {
                                            p.textContent = <?php echo wp_json_encode(__('Tag(s) removed.', 'je-keap-manager')); ?>;
                                        }
                                        notice.style.display = 'block';
                                        window.setTimeout(function() {
                                            notice.style.display = 'none';
                                        }, 3000);
                                    }
                                    var tbodyLeft = document.getElementById('jecm-contact-tags-tbody');
                                    if (tbodyLeft && !tbodyLeft.querySelector('tr')) {
                                        var hint = document.getElementById('jecm-tags-empty-hint');
                                        if (hint) {
                                            hint.style.display = '';
                                        }
                                    }
                                    bulkRm.disabled = false;
                                }).catch(function() {
                                    window.alert(<?php echo wp_json_encode(__('Request failed.', 'je-keap-manager')); ?>);
                                    bulkRm.disabled = false;
                                });
                            });
                        }

                        document.addEventListener('click', function(e) {
                            var btn = e.target.closest('.jecm-remove-tag');
                            if (!btn) {
                                return;
                            }
                            var tagName = btn.getAttribute('data-tag-name') || '';
                            if (!confirm(<?php echo wp_json_encode(__('Remove tag "%s" from this contact?', 'je-keap-manager')); ?>.replace('%s', tagName))) {
                                return;
                            }
                            btn.disabled = true;
                            var body = new URLSearchParams();
                            body.set('action', 'jecm_remove_tag');
                            body.set('nonce', btn.getAttribute('data-nonce') || '');
                            body.set('contact_id', btn.getAttribute('data-contact-id') || '');
                            body.set('tag_id', btn.getAttribute('data-tag-id') || '');
                            body.set('email', btn.getAttribute('data-email') || '');
                            body.set('tag_name', btn.getAttribute('data-tag-name') || '');
                            body.set('contact_name', btn.getAttribute('data-contact-name') || '');
                            fetch(ajaxUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                                body: body.toString()
                            }).then(function(r) { return r.json(); }).then(function(data) {
                                if (!data || !data.success) {
                                    var msg = (data && data.data && data.data.message) ? data.data.message
                                        : (typeof (data && data.data) === 'string' ? data.data : 'Remove failed.');
                                    window.alert(msg);
                                    btn.disabled = false;
                                    return;
                                }
                                var row = btn.closest('tr');
                                var rid = row ? row.querySelector('.jecm-tag-select') : null;
                                var rmId = rid ? parseInt(rid.value, 10) : NaN;
                                if (row && row.parentNode) {
                                    row.parentNode.removeChild(row);
                                }
                                var ext = window.jecmContactExistingTagIds;
                                if (ext && Array.isArray(ext) && !isNaN(rmId)) {
                                    var ix = ext.indexOf(rmId);
                                    if (ix !== -1) {
                                        ext.splice(ix, 1);
                                    }
                                }
                                jecmUpdateBulkBtn();
                                var tbodyLeft = document.getElementById('jecm-contact-tags-tbody');
                                if (tbodyLeft && !tbodyLeft.querySelector('tr')) {
                                    var hint = document.getElementById('jecm-tags-empty-hint');
                                    if (hint) {
                                        hint.style.display = '';
                                    }
                                }
                                var notice = document.getElementById('jecm-tag-removed-notice');
                                if (notice) {
                                    var p = notice.querySelector('p');
                                    if (p) {
                                        p.textContent = <?php echo wp_json_encode(__('Tag removed.', 'je-keap-manager')); ?>;
                                    }
                                    notice.style.display = 'block';
                                    window.setTimeout(function() {
                                        notice.style.display = 'none';
                                    }, 3000);
                                }
                            }).catch(function() {
                                window.alert(<?php echo wp_json_encode(__('Request failed.', 'je-keap-manager')); ?>);
                                btn.disabled = false;
                            });
                        });
                    })();
                    </script>
                <?php endif; ?>

                <h3><?php esc_html_e('Subscriptions', 'je-keap-manager'); ?></h3>
                <?php if (!$subs_result || !$subs_result['success']): ?>
                    <p class="description"><?php echo esc_html($subs_result['message'] ?? __('Could not load subscriptions.', 'je-keap-manager')); ?></p>
                <?php elseif (empty($subs_result['subscriptions'])): ?>
                    <p class="description"><?php esc_html_e('No subscriptions for this contact.', 'je-keap-manager'); ?></p>
                <?php else: ?>
                    <table class="widefat striped" style="margin-bottom:28px;">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Product', 'je-keap-manager'); ?></th>
                                <th><?php esc_html_e('Active', 'je-keap-manager'); ?></th>
                                <th><?php esc_html_e('Billing amount', 'je-keap-manager'); ?></th>
                                <th><?php esc_html_e('Next bill date', 'je-keap-manager'); ?></th>
                                <th><?php esc_html_e('Action', 'je-keap-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($subs_result['subscriptions'] as $sub): ?>
                            <?php
                            $name   = $sub['_product_name'] ?? '';
                            $sub_id = isset($sub['id']) ? (int) $sub['id'] : 0;
                            $bcycle = isset($sub['billing_cycle']) ? (string) $sub['billing_cycle'] : '';
                            $next   = $sub['next_bill_date'] ?? '';
                            $next_disp = '';
                            if ($next !== '' && $next !== null) {
                                $nts = strtotime((string) $next);
                                $next_disp = $nts ? date('F j, Y', $nts) : (string) $next;
                            } else {
                                $next_disp = '—';
                            }
                            $bill_amt = '—';
                            if (isset($sub['billing_amount']) && $sub['billing_amount'] !== '' && $sub['billing_amount'] !== null && is_numeric($sub['billing_amount'])) {
                                $bill_amt = '$' . number_format((float) $sub['billing_amount'], 2);
                            }
                            $is_active_sub = !empty($sub['active']);
                            ?>
                            <tr class="<?php echo $is_active_sub ? 'jecm-sub-active' : 'jecm-sub-inactive'; ?>"
                                style="<?php echo $is_active_sub ? 'background:#edfaed; border-left:3px solid #00a32a;' : 'border-left:3px solid transparent; color:#646970;'; ?>">
                                <td><?php echo esc_html($name ?: '—'); ?></td>
                                <td>
                                    <?php if ($is_active_sub) : ?>
                                        <span style="background:#00a32a; color:#fff; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:600;"><?php esc_html_e('Active', 'je-keap-manager'); ?></span>
                                    <?php else : ?>
                                        <span style="background:#dcdcde; color:#50575e; padding:2px 8px; border-radius:10px; font-size:12px;"><?php esc_html_e('Inactive', 'je-keap-manager'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($bill_amt); ?></td>
                                <td><?php echo esc_html($next_disp); ?></td>
                                <td style="vertical-align:middle;">
                                    <?php if ($is_active_sub && $sub_id) : ?>
                                        <button type="button" class="button button-small jecm-cancel-sub"
                                                data-sub-id="<?php echo esc_attr((string) $sub_id); ?>"
                                                data-contact-id="<?php echo esc_attr((string) $contact_id); ?>"
                                                data-product="<?php echo esc_attr($name); ?>"
                                                data-billing-cycle="<?php echo esc_attr($bcycle); ?>"
                                                data-email="<?php echo esc_attr($email); ?>"
                                                data-nonce="<?php echo esc_attr(wp_create_nonce('jecm_cancel_sub')); ?>">
                                            <?php esc_html_e('Cancel', 'je-keap-manager'); ?>
                                        </button>
                                    <?php else : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <h3><?php esc_html_e('Recent Payments', 'je-keap-manager'); ?></h3>
                <?php if (!$tx_result || !$tx_result['success']): ?>
                    <p class="description"><?php echo esc_html($tx_result['message'] ?? __('Could not load transactions.', 'je-keap-manager')); ?></p>
                <?php elseif (empty($tx_result['transactions'])): ?>
                    <p class="description"><?php esc_html_e('No transactions found.', 'je-keap-manager'); ?></p>
                <?php else: ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Date', 'je-keap-manager'); ?></th>
                                <th><?php esc_html_e('Amount', 'je-keap-manager'); ?></th>
                                <th><?php esc_html_e('Title', 'je-keap-manager'); ?></th>
                                <th><?php esc_html_e('Type', 'je-keap-manager'); ?></th>
                                <th><?php esc_html_e('Status', 'je-keap-manager'); ?></th>
                                <th><?php esc_html_e('Action', 'je-keap-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($tx_result['transactions'] as $row) :
                            $tx_status_disp  = $row['status_display'] ?? '—';
                            $tx_status_color = '';
                            if ($tx_status_disp === 'Unpaid' || $tx_status_disp === 'Overdue') {
                                $tx_status_color = '#b32d2e';
                            } elseif ($tx_status_disp === 'Refunded') {
                                $tx_status_color = '#996800';
                            } elseif ($tx_status_disp === 'Cancelled') {
                                $tx_status_color = '#787c82';
                            }
                            ?>
                            <tr>
                                <td><?php echo esc_html($row['date_display']); ?></td>
                                <td><?php echo esc_html($row['amount_display']); ?></td>
                                <td><?php echo esc_html($row['title'] ?? ''); ?></td>
                                <td><?php echo esc_html($row['type'] ?? ''); ?></td>
                                <td<?php echo $tx_status_color !== '' ? ' style="color:' . esc_attr($tx_status_color) . ';"' : ''; ?>><?php echo esc_html($tx_status_disp); ?></td>
                                <td>
                                    <?php
                                    $is_unpaid = in_array(
                                        strtoupper((string) ($row['status_raw'] ?? '')),
                                        array('OUTSTANDING', 'DRAFT', 'UNPAID', 'OVERDUE'),
                                        true
                                    );
                                    $row_oid = (int) ($row['order_id'] ?? 0);
                                    ?>
                                    <?php if ($row_oid > 0) : ?>
                                        <a href="<?php echo esc_url('https://app.infusionsoft.com/core/Job/manageJob.jsp?view=edit&ID=' . $row_oid); ?>"
                                           target="_blank" rel="noopener noreferrer"
                                           class="button button-small"
                                           style="margin-right:6px;"><?php esc_html_e('Keap ↗', 'je-keap-manager'); ?></a>
                                    <?php endif; ?>
                                    <?php if ($is_unpaid && $row_oid > 0) : ?>
                                        <button type="button"
                                                class="button button-small jecm-delete-order-btn"
                                                style="color:#b32d2e;border-color:#b32d2e;"
                                                data-order-id="<?php echo esc_attr((string) $row_oid); ?>"
                                                data-contact-id="<?php echo esc_attr((string) (int) $contact_id); ?>"
                                                data-email="<?php echo esc_attr($email); ?>"
                                                data-nonce="<?php echo esc_attr(wp_create_nonce('jecm_delete_order_' . $row_oid)); ?>"
                                                data-label="<?php echo esc_attr(trim(($row['date_display'] ?? '') . ' ' . ($row['amount_display'] ?? ''))); ?>">
                                            <?php esc_html_e('🗑 Delete', 'je-keap-manager'); ?>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (!$row_oid) : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <script>
                    (function() {
                        var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
                        var txtDelete = <?php echo wp_json_encode(__('🗑 Delete', 'je-keap-manager')); ?>;
                        var txtDeleting = <?php echo wp_json_encode(__('Deleting…', 'je-keap-manager')); ?>;
                        var msgConfirm = <?php echo wp_json_encode(__('Permanently delete %s from Keap? This cannot be undone.', 'je-keap-manager')); ?>;
                        var msgErr = <?php echo wp_json_encode(__('Error deleting order.', 'je-keap-manager')); ?>;
                        var msgFail = <?php echo wp_json_encode(__('Request failed.', 'je-keap-manager')); ?>;
                        document.addEventListener('click', function(e) {
                            var btn = e.target.closest('.jecm-delete-order-btn');
                            if (!btn) {
                                return;
                            }
                            var label = btn.getAttribute('data-label') || <?php echo wp_json_encode(__('this order', 'je-keap-manager')); ?>;
                            if (!window.confirm(msgConfirm.replace('%s', label))) {
                                return;
                            }
                            btn.disabled = true;
                            btn.textContent = txtDeleting;
                            var fd = new FormData();
                            fd.append('action', 'jecm_delete_order');
                            fd.append('order_id', btn.getAttribute('data-order-id') || '');
                            fd.append('nonce', btn.getAttribute('data-nonce') || '');
                            fd.append('contact_id', btn.getAttribute('data-contact-id') || '');
                            fd.append('email', btn.getAttribute('data-email') || '');
                            fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
                                .then(function(r) { return r.json(); })
                                .then(function(res) {
                                    if (res && res.success) {
                                        var row = btn.closest('tr');
                                        if (row) {
                                            row.style.transition = 'opacity 0.3s';
                                            row.style.opacity = '0';
                                            setTimeout(function() { row.remove(); }, 300);
                                        }
                                    } else {
                                        btn.disabled = false;
                                        btn.textContent = txtDelete;
                                        window.alert((res && res.data && res.data.message) ? res.data.message : msgErr);
                                    }
                                })
                                .catch(function() {
                                    btn.disabled = false;
                                    btn.textContent = txtDelete;
                                    window.alert(msgFail);
                                });
                        });
                    })();
                    </script>
                <?php endif; ?>

                <?php if (current_user_can('manage_options') && is_array($tx_result ?? null)) : ?>
                    <details class="jecm-debug-keap-payments" style="margin-top:16px;padding:12px;background:#f6f7f7;border:1px solid #c3c4c7;border-radius:4px;">
                        <summary style="cursor:pointer;font-weight:600;list-style-position:outside;">
                            <?php esc_html_e('Debug: raw Keap /orders API response', 'je-keap-manager'); ?>
                        </summary>
                        <p class="description" style="margin:8px 0 0;"><?php esc_html_e('Full decoded JSON returned for this contact’s orders (same request that powers the table above).', 'je-keap-manager'); ?></p>
                        <pre style="margin:8px 0 0;font-size:11px;line-height:1.45;white-space:pre-wrap;word-break:break-word;background:#fff;border:1px solid #dcdcde;padding:10px;max-height:min(70vh,640px);overflow:auto;"><?php
                        $jecm_tx_dbg = $tx_result['message'] ?? '';
                        if (!empty($tx_result['success']) && array_key_exists('keap_orders_api_response', $tx_result)) {
                            $jecm_tx_payload = $tx_result['keap_orders_api_response'];
                            $jecm_tx_json    = wp_json_encode($jecm_tx_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            echo esc_html(false !== $jecm_tx_json ? $jecm_tx_json : print_r($jecm_tx_payload, true));
                        } else {
                            echo esc_html(
                                (string) ($jecm_tx_dbg !== '' ? $jecm_tx_dbg : __('No payload loaded (request may have failed before a body was parsed).', 'je-keap-manager'))
                            );
                        }
                        ?></pre>
                    </details>
                <?php endif; ?>

                <script>var jecmSites = <?php echo wp_json_encode(array_values($fst_autologin_sites)); ?>;</script>
                <script>
                (function() {
                    var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
                    var jecmFindContactNames = <?php echo wp_json_encode(array(
                        'first_name' => $lookup_data['first_name'],
                        'last_name'  => $lookup_data['last_name'],
                    )); ?>;
                    var jecmAutologinDestinations = <?php echo wp_json_encode($fst_autologin_destinations); ?>;
                    document.addEventListener('click', function(ev) {
                        var acb = ev.target.closest('.jecm-autologin-copy-btn');
                        if (!acb) {
                            return;
                        }
                        ev.preventDefault();
                        var inline = acb.closest('.jecm-autologin-inline');
                        var destSel = inline ? inline.querySelector('select.jecm-autologin-dest') : document.getElementById('jecm-autologin-dest');
                        if (!destSel || !destSel.options || destSel.selectedIndex < 0) {
                            return;
                        }
                        var destOpt = destSel.options[destSel.selectedIndex];
                        var si = parseInt(destOpt.getAttribute('data-site-index') || '0', 10);
                        if (isNaN(si)) {
                            si = 0;
                        }
                        var sites = typeof window.jecmSites !== 'undefined' ? window.jecmSites : [];
                        var site = sites[si] || sites[0];
                        if (!site || !site.url) {
                            return;
                        }
                        var path = destOpt.value || '';
                        var contact = acb.getAttribute('data-contact') || '';
                        var email = acb.getAttribute('data-email') || '';
                        var url = String(site.url).replace(/\/$/, '') + '/?memb_autologin=yes&auth_key=' + encodeURIComponent(site.auth_key || '')
                            + '&Id=' + encodeURIComponent(contact) + '&Email=' + encodeURIComponent(email)
                            + '&redir=' + encodeURIComponent(path);
                        var done = function() {
                            acb.style.transition = 'background-color .15s ease, border-color .15s ease, box-shadow .15s ease';
                            acb.style.backgroundColor = '#00a32a';
                            acb.style.borderColor = '#00a32a';
                            acb.style.boxShadow = 'none';
                            window.setTimeout(function() {
                                acb.style.backgroundColor = '';
                                acb.style.borderColor = '';
                                acb.style.boxShadow = '';
                                acb.style.transition = '';
                            }, 1500);
                        };
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(url).then(done).catch(function() {
                                try {
                                    var ta = document.createElement('textarea');
                                    ta.value = url;
                                    document.body.appendChild(ta);
                                    ta.select();
                                    document.execCommand('copy');
                                    document.body.removeChild(ta);
                                    done();
                                } catch (e2) {}
                            });
                        } else {
                            try {
                                var ta2 = document.createElement('textarea');
                                ta2.value = url;
                                document.body.appendChild(ta2);
                                ta2.select();
                                document.execCommand('copy');
                                document.body.removeChild(ta2);
                                done();
                            } catch (e3) {}
                        }
                    });
                    function closeCancelPanels() {
                        document.querySelectorAll('.jecm-cancel-sub-confirm-row').forEach(function(r) { r.remove(); });
                    }
                    function escHtml(s) {
                        var d = document.createElement('div');
                        d.textContent = s == null ? '' : String(s);
                        return d.innerHTML;
                    }
                    function renderResultsTable(rows) {
                        var h = '<table class="widefat" style="margin-top:8px;"><thead><tr><th>Action</th><th>Result</th></tr></thead><tbody>';
                        rows.forEach(function(row) {
                            h += '<tr><td>' + escHtml(row.label) + '</td><td>' + (row.success ? '✅' : '❌') + ' ' + escHtml(row.message) + '</td></tr>';
                        });
                        h += '</tbody></table>';
                        return h;
                    }
                    function escapeAttr(s) {
                        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
                    }
                    function renderKeapContactLinkHtml(cid, firstName, lastName, studentEmail) {
                        if (!cid) {
                            return '';
                        }
                        firstName = firstName == null ? '' : String(firstName);
                        lastName = lastName == null ? '' : String(lastName);
                        studentEmail = studentEmail == null ? '' : String(studentEmail);
                        var displayName = escHtml((firstName + ' ' + lastName).trim());
                        var u = 'https://app.infusionsoft.com/core/Contact/manageContact.jsp?view=edit&ID=' + encodeURIComponent(String(cid));
                        var optsDest = '';
                        (jecmAutologinDestinations || []).forEach(function(d) {
                            if (!d) return;
                            var dsi = d.site_index != null ? parseInt(String(d.site_index), 10) : 0;
                            if (isNaN(dsi)) {
                                dsi = 0;
                            }
                            optsDest += '<option value="' + escapeAttr(d.path || '') + '" data-site-index="' + escapeAttr(String(dsi)) + '">' + escHtml(d.title || '') + '</option>';
                        });
                        return '<p style="margin-bottom:20px;"><strong>' + displayName + '</strong>' +
                            '&nbsp;&mdash;&nbsp;<strong>Keap contact ID:</strong> ' +
                            '<a href="' + u + '" target="_blank" rel="noopener" style="text-decoration:none;">' + escHtml(cid) +
                            '<span class="dashicons dashicons-external" style="font-size:14px; vertical-align:middle; text-decoration:none;"></span></a>' +
                            '&nbsp;&mdash;&nbsp;<span style="display:inline-flex;flex-direction:column;align-items:flex-start;gap:3px;vertical-align:middle;">' +
                            '<span style="font-size:9px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;color:#646970;line-height:1;">' + escHtml('Auto Logins') + '</span>' +
                            '<span class="jecm-autologin-inline" style="display:inline-flex;align-items:center;gap:0;background:#f0f6fc;border:1px solid #c5d9ed;border-radius:4px;padding:3px 6px;">' +
                            '<select class="jecm-autologin-dest" style="margin:0;border:none;background:transparent;height:26px;padding:0 4px;font-size:13px;">' + optsDest + '</select>' +
                            '<button type="button" class="button button-primary button-small jecm-autologin-copy-btn" data-contact="' + escapeAttr(cid) + '" data-email="' + escapeAttr(studentEmail) + '" style="height:26px;line-height:24px;padding:0 10px;font-size:12px;margin-left:4px;">Copy</button>' +
                            '</span></span></p>';
                    }
                    document.addEventListener('click', function(e) {
                        var nvm = e.target.closest('.jecm-cancel-sub-nevermind');
                        if (nvm) {
                            e.preventDefault();
                            var pr = nvm.closest('.jecm-cancel-sub-confirm-row');
                            if (pr) {
                                pr.remove();
                            }
                            return;
                        }
                        var cgo = e.target.closest('.jecm-cancel-sub-confirm');
                        if (cgo) {
                            e.preventDefault();
                            var panel = cgo.closest('.jecm-cancel-sub-confirm-row');
                            var sel = panel ? panel.querySelector('.jecm-cancel-reason-select') : null;
                            var reason = sel ? sel.value : 'customer_cancel';
                            var spin = panel ? panel.querySelector('.spinner') : null;
                            if (spin) {
                                spin.classList.add('is-active');
                            }
                            cgo.disabled = true;
                            var body = new URLSearchParams();
                            body.set('action', 'jecm_cancel_sub');
                            body.set('nonce', cgo.getAttribute('data-nonce') || '');
                            body.set('contact_id', cgo.getAttribute('data-contact-id') || '');
                            body.set('subscription_id', cgo.getAttribute('data-sub-id') || '');
                            body.set('product_name', cgo.getAttribute('data-product') || '');
                            body.set('billing_cycle', cgo.getAttribute('data-billing-cycle') || '');
                            body.set('email', cgo.getAttribute('data-email') || '');
                            body.set('cancel_reason', reason);
                            fetch(ajaxUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                                body: body.toString()
                            }).then(function(r) { return r.json(); }).then(function(data) {
                                if (spin) {
                                    spin.classList.remove('is-active');
                                }
                                cgo.disabled = false;
                                if (!data || !data.success) {
                                    var msg = (data && data.data && data.data.message) ? data.data.message
                                        : (typeof (data && data.data) === 'string' ? data.data : 'Cancel failed.');
                                    window.alert(msg);
                                    return;
                                }
                                var results = (data.data && data.data.results) ? data.data.results : [];
                                var resCid = (data.data && data.data.contact_id) ? data.data.contact_id : (cgo.getAttribute('data-contact-id') || '');
                                var subRow = panel ? panel.previousElementSibling : null;
                                if (panel) {
                                    panel.remove();
                                }
                                if (subRow && subRow.parentNode) {
                                    var resTr = document.createElement('tr');
                                    resTr.className = 'jecm-cancel-sub-results-row';
                                    var tbl = subRow.closest('table');
                                    var ncol = tbl ? tbl.querySelectorAll('thead th').length : 5;
                                    resTr.innerHTML = '<td colspan="' + ncol + '">' +
                                        renderKeapContactLinkHtml(
                                            resCid,
                                            jecmFindContactNames.first_name,
                                            jecmFindContactNames.last_name,
                                            cgo.getAttribute('data-email') || ''
                                        ) + renderResultsTable(results) + '</td>';
                                    subRow.after(resTr);
                                }
                            }).catch(function() {
                                if (spin) {
                                    spin.classList.remove('is-active');
                                }
                                cgo.disabled = false;
                                window.alert('Request failed.');
                            });
                            return;
                        }
                        var openb = e.target.closest('.jecm-cancel-sub');
                        if (!openb) {
                            return;
                        }
                        closeCancelPanels();
                        var tr = openb.closest('tr');
                        if (!tr || !tr.parentNode) {
                            return;
                        }
                        var table = tr.closest('table');
                        var colspan = table ? table.querySelectorAll('thead th').length : 5;
                        var ntr = document.createElement('tr');
                        ntr.className = 'jecm-cancel-sub-confirm-row';
                        ntr.innerHTML = '<td colspan="' + colspan + '">' +
                            'Cancel reason: ' +
                            '<select class="jecm-cancel-reason-select">' +
                            '<option value="customer_cancel">Customer Requested</option>' +
                            '<option value="payment_failed">Payment Failed</option>' +
                            '</select> ' +
                            '<button type="button" class="button button-small jecm-cancel-sub-confirm" style="background:#d63638;border-color:#c32a2a;color:#fff;">Confirm Cancel</button> ' +
                            '<a href="#" class="jecm-cancel-sub-nevermind">Never mind</a> ' +
                            '<span class="spinner" style="float:none;margin:0 8px;vertical-align:middle;"></span>' +
                            '</td>';
                        tr.after(ntr);
                        var cfm = ntr.querySelector('.jecm-cancel-sub-confirm');
                        if (cfm) {
                            cfm.setAttribute('data-nonce', openb.getAttribute('data-nonce') || '');
                            cfm.setAttribute('data-contact-id', openb.getAttribute('data-contact-id') || '');
                            cfm.setAttribute('data-sub-id', openb.getAttribute('data-sub-id') || '');
                            cfm.setAttribute('data-product', openb.getAttribute('data-product') || '');
                            cfm.setAttribute('data-billing-cycle', openb.getAttribute('data-billing-cycle') || '');
                            cfm.setAttribute('data-email', openb.getAttribute('data-email') || '');
                        }
                    });
                })();
                </script>

                <?php if ($jecm_has_fc_local) : ?>
                    <?php $this->jecm_render_fc_orders_table($local_fc_orders); ?>
                <?php endif; ?>

            <?php endif; ?>

            <?php if ($jecm_find_hide_search) : ?>
                <script>
                (function() {
                    var btn    = document.getElementById('jecm-switch-student-btn');
                    var form   = document.getElementById('jecm-switch-student-form');
                    var cancel = document.getElementById('jecm-switch-cancel-btn');
                    if (!btn || !form) {
                        return;
                    }
                    btn.addEventListener('click', function() {
                        form.style.display = 'block';
                        var inp = form.querySelector('input[type="email"]');
                        if (inp) {
                            inp.focus();
                        }
                        btn.style.display = 'none';
                    });
                    if (cancel) {
                        cancel.addEventListener('click', function() {
                            form.style.display = 'none';
                            btn.style.display  = '';
                        });
                    }
                })();
                </script>
            <?php endif; ?>

            <?php if ($email && $jecm_find_hide_search) : ?>
                <div id="jecm-task-modal" style="display:none;position:fixed;inset:0;z-index:100000;align-items:center;justify-content:center;padding:20px;background:rgba(0,0,0,0.45);"
                     role="dialog" aria-modal="true" aria-labelledby="jecm-task-modal-title">
                    <div style="background:#fff;border-radius:4px;max-width:520px;width:100%;max-height:90vh;overflow:auto;box-shadow:0 4px 24px rgba(0,0,0,0.15);padding:20px 22px;border:1px solid #c3c4c7;">
                        <h2 id="jecm-task-modal-title" style="margin:0 0 8px;"><?php esc_html_e('Add task', 'je-keap-manager'); ?></h2>
                        <p class="description" id="jecm-task-modal-sub" style="margin:0 0 14px;"></p>
                        <label for="jecm-task-notes"><strong><?php esc_html_e('Notes', 'je-keap-manager'); ?></strong></label>
                        <textarea id="jecm-task-notes" class="large-text" rows="7" style="width:100%;margin-top:6px;"></textarea>
                        <p id="jecm-task-modal-error" class="notice notice-error" style="display:none;margin:12px 0 0;padding:8px 12px;"></p>
                        <p style="margin:16px 0 0;display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;">
                            <button type="button" class="button" id="jecm-task-modal-cancel"><?php esc_html_e('Cancel', 'je-keap-manager'); ?></button>
                            <button type="button" class="button button-primary" id="jecm-task-modal-save"><?php esc_html_e('Save task', 'je-keap-manager'); ?></button>
                        </p>
                    </div>
                </div>
                <script>
                (function($) {
                    var modal = $('#jecm-task-modal');
                    var body = $('body');
                    var email = '', contactId = 0, studentName = '';
                    var nonce = <?php echo wp_json_encode(wp_create_nonce('jecm_add_task')); ?>;
                    var tasksUrl = <?php echo wp_json_encode($this->tasks_tab_url()); ?>;

                    function jecmCopyStudentEmail(text, btn) {
                        var origTitle = btn.getAttribute('title') || '';
                        var done = function() {
                            btn.setAttribute('title', <?php echo wp_json_encode(__('Copied!', 'je-keap-manager')); ?>);
                            setTimeout(function() { btn.setAttribute('title', origTitle); }, 1500);
                        };
                        if (!text) {
                            window.alert(<?php echo wp_json_encode(__('Nothing to copy.', 'je-keap-manager')); ?>);
                            return;
                        }
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text).then(done).catch(function() {
                                try {
                                    var ta = document.createElement('textarea');
                                    ta.value = text;
                                    document.body.appendChild(ta);
                                    ta.select();
                                    document.execCommand('copy');
                                    document.body.removeChild(ta);
                                    done();
                                } catch (e2) {
                                    window.alert(<?php echo wp_json_encode(__('Copy failed.', 'je-keap-manager')); ?>);
                                }
                            });
                        } else {
                            try {
                                var ta2 = document.createElement('textarea');
                                ta2.value = text;
                                document.body.appendChild(ta2);
                                ta2.select();
                                document.execCommand('copy');
                                document.body.removeChild(ta2);
                                done();
                            } catch (e3) {
                                window.alert(<?php echo wp_json_encode(__('Copy failed.', 'je-keap-manager')); ?>);
                            }
                        }
                    }

                    $(document).on('click', '.jecm-copy-student-email', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        jecmCopyStudentEmail(this.getAttribute('data-copy') || '', this);
                    });

                    function openModal() {
                        $('#jecm-task-modal-sub').text(studentName ? studentName + ' · ' + email : email);
                        $('#jecm-task-notes').val('').trigger('focus');
                        $('#jecm-task-modal-error').hide().text('');
                        modal.css('display', 'flex');
                        body.css('overflow', 'hidden');
                    }
                    function closeModal() {
                        modal.hide();
                        body.css('overflow', '');
                    }

                    $(document).on('click', '.jecm-open-task-modal', function(e) {
                        e.preventDefault();
                        var b = $(this);
                        email = b.data('email') || '';
                        contactId = parseInt(b.data('contact-id') || 0, 10) || 0;
                        studentName = b.data('student-name') || '';
                        openModal();
                    });
                    $('#jecm-task-modal-cancel').on('click', closeModal);
                    modal.on('click', function(e) {
                        if (e.target === modal[0]) {
                            closeModal();
                        }
                    });
                    $(document).on('keydown', function(e) {
                        if (e.key === 'Escape' && modal.is(':visible')) {
                            closeModal();
                        }
                    });
                    $('#jecm-task-modal-save').on('click', function() {
                        var btn = $(this);
                        var notes = $('#jecm-task-notes').val();
                        var err = $('#jecm-task-modal-error');
                        err.hide().text('');
                        btn.prop('disabled', true);
                        $.post(ajaxurl, {
                            action: 'jecm_add_task',
                            nonce: nonce,
                            email: email,
                            contact_id: contactId,
                            student_name: studentName,
                            notes: notes
                        }).done(function(res) {
                            if (res && res.success) {
                                closeModal();
                                window.location.href = tasksUrl;
                                return;
                            }
                            var msg = (res && res.data && res.data.message) ? res.data.message : <?php echo wp_json_encode(__('Could not save task.', 'je-keap-manager')); ?>;
                            err.text(msg).show();
                        }).fail(function() {
                            err.text(<?php echo wp_json_encode(__('Request failed.', 'je-keap-manager')); ?>).show();
                        }).always(function() {
                            btn.prop('disabled', false);
                        });
                    });
                })(jQuery);
                </script>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_webhook_tab() {
        $secret   = JECM_FluentCart_Webhook::get_secret();
        $endpoint = rest_url('jecm/v1/fluentcart');
        $per_page = 30;
        $paged    = max(1, (int) ($_GET['paged'] ?? 1));
        $offset   = ($paged - 1) * $per_page;
        $rows     = JECM_FluentCart_Webhook::get_webhook_logs($per_page, $offset, array());
        $total    = JECM_FluentCart_Webhook::get_webhook_log_total(array());
        $pages    = $per_page > 0 ? (int) ceil($total / $per_page) : 0;
        ?>
        <div class="card" style="max-width:1100px; padding:20px;">
            <h2 style="margin-top:0;"><?php esc_html_e('FluentCart webhook (Jazzedge Academy → SJE)', 'je-keap-manager'); ?></h2>
            <p class="description">
                <?php esc_html_e('Configure FluentCart Pro on jazzedge.academy to POST JSON order payloads to the endpoint below. Use Request Headers and send the same secret for authentication.', 'je-keap-manager'); ?>
            </p>

            <table class="widefat" style="max-width:900px; margin-bottom:20px;">
                <tbody>
                    <tr>
                        <th scope="row" style="width:160px;"><?php esc_html_e('Webhook URL', 'je-keap-manager'); ?></th>
                        <td><code style="word-break:break-all;"><?php echo esc_html($endpoint); ?></code></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Header name', 'je-keap-manager'); ?></th>
                        <td><code>X-JECM-Webhook-Secret</code> <?php esc_html_e('or', 'je-keap-manager'); ?> <code>Authorization: Bearer …</code></td>
                    </tr>
                </tbody>
            </table>

            <form method="post" style="margin-bottom:24px; max-width:900px;">
                <?php wp_nonce_field('jecm_webhook', 'jecm_webhook_nonce'); ?>
                <h3><?php esc_html_e('Shared secret', 'je-keap-manager'); ?></h3>
                <p>
                    <label for="jecm_fc_webhook_secret" class="screen-reader-text"><?php esc_html_e('Webhook secret', 'je-keap-manager'); ?></label>
                    <input type="text" name="jecm_fc_webhook_secret" id="jecm_fc_webhook_secret" class="large-text code"
                           value="<?php echo esc_attr($secret); ?>" autocomplete="off"
                           placeholder="<?php esc_attr_e('Paste or generate a long random secret', 'je-keap-manager'); ?>">
                </p>
                <p>
                    <input type="submit" name="jecm_save_webhook" class="button button-primary" value="<?php esc_attr_e('Save secret', 'je-keap-manager'); ?>">
                    <input type="submit" name="jecm_regenerate_webhook_secret" class="button button-secondary" value="<?php esc_attr_e('Regenerate secret', 'je-keap-manager'); ?>"
                           onclick="return confirm('<?php echo esc_js(__('Regenerate? You must update Jazzedge Academy immediately.', 'je-keap-manager')); ?>'); ?>">
                </p>
            </form>

            <h3><?php esc_html_e('Incoming webhook log', 'je-keap-manager'); ?></h3>
            <p style="margin-bottom:12px;">
                <?php echo esc_html(sprintf(/* translators: %d: record count */ _n('%d request logged.', '%d requests logged.', (int) $total, 'je-keap-manager'), (int) $total)); ?>
                <form method="post" style="display:inline; margin-left:12px;">
                    <?php wp_nonce_field('jecm_purge_webhook_log', 'jecm_webhook_purge_nonce'); ?>
                    <input type="hidden" name="jecm_action" value="purge_webhook_log">
                    <button type="submit" class="button button-secondary"
                            onclick="return confirm('<?php echo esc_js(__('Delete all webhook log rows? Orders mirror is not removed.', 'je-keap-manager')); ?>');"
                            style="color:#b32d2e; border-color:#b32d2e;">
                        <?php esc_html_e('Purge log', 'je-keap-manager'); ?>
                    </button>
                </form>
            </p>

            <?php if (empty($rows)) : ?>
                <p><?php esc_html_e('No webhook deliveries yet.', 'je-keap-manager'); ?></p>
            <?php else : ?>
                <table class="widefat striped" style="table-layout:fixed;">
                    <thead>
                        <tr>
                            <th style="width:130px;"><?php esc_html_e('Time (site)', 'je-keap-manager'); ?></th>
                            <th style="width:90px;"><?php esc_html_e('Auth', 'je-keap-manager'); ?></th>
                            <th style="width:56px;"><?php esc_html_e('HTTP', 'je-keap-manager'); ?></th>
                            <th style="width:72px;"><?php esc_html_e('Saved', 'je-keap-manager'); ?></th>
                            <th style="width:120px;"><?php esc_html_e('Order UUID', 'je-keap-manager'); ?></th>
                            <th><?php esc_html_e('Email / message', 'je-keap-manager'); ?></th>
                            <th style="width:110px;"><?php esc_html_e('Find student', 'je-keap-manager'); ?></th>
                            <th style="width:90px;"><?php esc_html_e('IP', 'je-keap-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $wr) : ?>
                        <?php
                        $t_display = !empty($wr['created_at'])
                            ? mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $wr['created_at'], true)
                            : '';
                        $body      = isset($wr['request_body']) ? (string) $wr['request_body'] : '';
                        $em        = sanitize_email($wr['customer_email'] ?? '');
                        $find_url  = $em
                            ? add_query_arg(array('page' => 'je-keap-manager', 'tab' => 'find', 'email' => $em), admin_url('admin.php'))
                            : '';
                        ?>
                        <tr>
                            <td><?php echo esc_html($t_display); ?></td>
                            <td><?php echo !empty($wr['auth_ok']) ? '✓' : '✗'; ?></td>
                            <td><?php echo esc_html((string) ($wr['http_status'] ?? '')); ?></td>
                            <td><?php echo !empty($wr['processed_ok']) ? '✓' : '—'; ?></td>
                            <td><code style="font-size:11px; word-break:break-all;"><?php echo esc_html(substr((string) ($wr['order_uuid'] ?? ''), 0, 36)); ?><?php echo strlen((string) ($wr['order_uuid'] ?? '')) > 36 ? '…' : ''; ?></code></td>
                            <td>
                                <?php
                                if ($em) {
                                    echo '<a href="' . esc_url($find_url) . '">' . esc_html($em) . '</a><br>';
                                }
                                echo esc_html((string) ($wr['message'] ?? ''));
                                ?>
                                <?php if ($body !== '') : ?>
                                    <details style="margin-top:6px;">
                                        <summary style="cursor:pointer;"><?php esc_html_e('Request body', 'je-keap-manager'); ?></summary>
                                        <pre style="white-space:pre-wrap;word-break:break-all;font-size:11px;max-height:240px;overflow:auto;background:#f6f7f7;padding:8px;border:1px solid #dcdcde;"><?php echo esc_html($body); ?></pre>
                                    </details>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($em && $find_url) : ?>
                                    <a href="<?php echo esc_url($find_url); ?>"><?php esc_html_e('Find student', 'je-keap-manager'); ?></a>
                                <?php else : ?>
                                    &mdash;
                                <?php endif; ?>
                            </td>
                            <td><code style="font-size:11px;"><?php echo esc_html((string) ($wr['remote_ip'] ?? '')); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($pages > 1) : ?>
                    <p style="margin-top:12px;">
                        <?php
                        for ($pi = 1; $pi <= $pages; $pi++) {
                            if ($pi === $paged) {
                                echo '<strong style="margin-right:8px;">' . (int) $pi . '</strong>';
                            } else {
                                echo '<a href="' . esc_url($this->webhook_tab_url(array('paged' => $pi))) . '" style="margin-right:8px;">' . (int) $pi . '</a>';
                            }
                        }
                        ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_test_tab() {
        $api_key = get_option('jecm_keap_api_key');
        $keap    = new JECM_Keap_API($api_key);
        $data    = null;
        $email   = '';
        $error   = '';

        if (isset($_POST['jecm_test_nonce']) && wp_verify_nonce($_POST['jecm_test_nonce'], 'jecm_test')) {
            $email = sanitize_email($_POST['jecm_test_email'] ?? '');
            if (!$email) {
                $error = 'Please enter a valid email.';
            } elseif (!$api_key) {
                $error = 'No Keap API key configured.';
            } else {
                $lookup = $keap->find_contact_id($email);
                if (!$lookup['success']) {
                    $error = $lookup['message'];
                } else {
                    $contact_id = $lookup['contact_id'];
                    $data       = $keap->get_contact_debug_data($contact_id);
                    $data['_contact_id'] = $contact_id;
                }
            }
        }
        ?>
        <div class="card" style="max-width:900px; padding:20px;">
            <h2 style="margin-top:0;">🔬 Subscription Debug</h2>
            <p>Enter a student email to dump all Keap subscription, order, and payment plan data for that contact.</p>

            <?php if ($error): ?>
                <div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div>
            <?php endif; ?>

            <form method="post" style="margin-bottom:20px;">
                <?php wp_nonce_field('jecm_test', 'jecm_test_nonce'); ?>
                <input type="email" name="jecm_test_email" value="<?php echo esc_attr($email); ?>"
                       placeholder="student@example.com" style="width:300px;" required>
                <input type="submit" class="button button-primary" value="Fetch Debug Data">
            </form>

            <?php if ($data): ?>
                <p><strong>Contact ID:</strong> <?php echo esc_html($data['_contact_id']); ?></p>

                <?php
                $sections = array(
                    'v2_subscriptions'      => 'V2 Subscriptions (active subscriptions)',
                    'v1_subscriptions'      => 'V1 Subscriptions (JobRecurring)',
                    'v1_orders'             => 'V1 Orders + Payment Plans',
                    'v2_subscription_plans' => 'V2 Subscription Plans (full catalogue)',
                );
                foreach ($sections as $key => $label):
                    $json = wp_json_encode($data[$key], JSON_PRETTY_PRINT);
                ?>
                <h3 style="margin-bottom:4px;"><?php echo esc_html($label); ?></h3>
                <pre style="background:#f6f7f7; border:1px solid #dcdcde; padding:12px; overflow:auto; max-height:300px; font-size:11px; border-radius:3px;"><?php echo esc_html($json); ?></pre>
                <?php endforeach; ?>

                <p style="margin-top:16px;">
                    <textarea id="jecm-debug-json" style="position:absolute;left:-9999px;top:-9999px;" readonly><?php echo esc_textarea(wp_json_encode($data, JSON_PRETTY_PRINT)); ?></textarea>
                    <button type="button" class="button" id="jecm-copy-debug">Copy All as JSON</button>
                    <span id="jecm-copy-msg" style="display:none; margin-left:10px; color:#1a7f37; font-weight:600;">Copied!</span>
                    <script>
                    document.getElementById('jecm-copy-debug').addEventListener('click', function() {
                        var el  = document.getElementById('jecm-debug-json');
                        var msg = document.getElementById('jecm-copy-msg');
                        el.select();
                        el.setSelectionRange(0, 99999);
                        navigator.clipboard.writeText(el.value).then(function() {
                            msg.style.display = 'inline';
                            setTimeout(function(){ msg.style.display = 'none'; }, 2000);
                        }).catch(function() {
                            document.execCommand('copy');
                            msg.style.display = 'inline';
                            setTimeout(function(){ msg.style.display = 'none'; }, 2000);
                        });
                    });
                    </script>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    private function test_keap_api_key($api_key) {
        if (empty($api_key)) {
            return array('success' => false, 'message' => 'No API key entered.');
        }

        $response = wp_remote_get(
            'https://api.infusionsoft.com/crm/rest/v1/contacts?limit=1',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code === 200) {
            $count = $body['count'] ?? '?';
            return array('success' => true, 'message' => 'Connected. ' . $count . ' contacts in account.');
        }

        if ($code === 401) {
            return array('success' => false, 'message' => 'Unauthorized — check your API key.');
        }

        return array('success' => false, 'message' => 'Unexpected response: HTTP ' . $code);
    }

    private function get_membership_tag_map() {
        return array(
            // Academy Monthly
            9954  => 'Studio (Monthly)',
            9821  => 'Premier (Monthly)',
            9903  => 'Classes Only (Monthly)',
            9827  => 'Lessons Only (Monthly)',
            10136 => 'Studio DMP (Monthly)',
            10142 => 'Premier DMP (Monthly)',
            // Academy Yearly
            9956  => 'Studio (Yearly)',
            9813  => 'Premier (Yearly)',
            9819  => 'Lessons Only (Yearly)',
            9815  => 'Lessons & Classes (Yearly)',
            10290 => 'Essentials (Yearly)',
            // HSP
            7548  => 'HSP Level 1',
            7574  => 'HSP Level 2',
            7578  => 'HSP Level 3',
            8893  => 'HSP Yearly (Solo)',
        );
    }

    private function lookup_student($email, $keap) {
        $lookup = $keap->find_contact_id($email);
        if (!$lookup['success']) {
            return array('success' => false, 'message' => $lookup['message']);
        }
        $contact_id = (int) $lookup['contact_id'];

        $tags_result = $keap->get_contact_tags_with_names($contact_id);
        if (!$tags_result['success']) {
            return array('success' => false, 'message' => $tags_result['message']);
        }
        $contact_tag_ids = array_column($tags_result['tags'], 'id');

        $last_payment = $keap->get_last_payment_date($contact_id);

        $full = $keap->get_contact_full($contact_id);
        if (empty($full['success']) || !is_array($full['body'])) {
            return array(
                'success' => false,
                'message' => $full['message'] ?? 'Could not load contact.',
            );
        }
        $body       = $full['body'];
        $first_name = $body['given_name'] ?? '';
        $last_name  = $body['family_name'] ?? '';

        $parse_ids = function($option_key) {
            $raw = get_option($option_key, '');
            if (empty(trim($raw))) {
                return array();
            }
            return array_filter(array_map('intval', explode(',', $raw)));
        };
        $monthly_academy_ids = $parse_ids('jecm_keap_monthly_academy_tags');
        $yearly_academy_ids  = $parse_ids('jecm_keap_yearly_academy_tags');
        $monthly_hsp_ids     = $parse_ids('jecm_keap_monthly_hsp_tags');
        $yearly_hsp_ids      = $parse_ids('jecm_keap_yearly_hsp_tags');

        $map                = $this->get_membership_tag_map();
        $active_memberships = array();

        foreach (array_intersect($contact_tag_ids, $monthly_academy_ids) as $tid) {
            $tid = (int) $tid;
            $active_memberships[] = array(
                'group'  => 'academy_monthly',
                'tag_id' => $tid,
                'label'  => isset($map[$tid]) ? $map[$tid] : ('Tag #' . $tid),
            );
        }
        foreach (array_intersect($contact_tag_ids, $yearly_academy_ids) as $tid) {
            $tid = (int) $tid;
            $active_memberships[] = array(
                'group'  => 'academy_yearly',
                'tag_id' => $tid,
                'label'  => isset($map[$tid]) ? $map[$tid] : ('Tag #' . $tid),
            );
        }
        foreach (array_intersect($contact_tag_ids, $monthly_hsp_ids) as $tid) {
            $tid = (int) $tid;
            $active_memberships[] = array(
                'group'  => 'hsp_monthly',
                'tag_id' => $tid,
                'label'  => isset($map[$tid]) ? $map[$tid] : ('Tag #' . $tid),
            );
        }
        foreach (array_intersect($contact_tag_ids, $yearly_hsp_ids) as $tid) {
            $tid = (int) $tid;
            $active_memberships[] = array(
                'group'  => 'hsp_yearly',
                'tag_id' => $tid,
                'label'  => isset($map[$tid]) ? $map[$tid] : ('Tag #' . $tid),
            );
        }

        return array(
            'success'            => true,
            'email'              => $email,
            'contact_id'         => $contact_id,
            'first_name'         => $first_name,
            'last_name'          => $last_name,
            'contact_body'       => $full['body'],
            'contact_tag_ids'    => $contact_tag_ids,
            'last_payment'       => $last_payment,
            'active_memberships' => $active_memberships,
        );
    }

    private function process_cancellation(JECM_Keap_API $keap, ?array $ctx = null) {
        $allowed_groups = array('academy_monthly', 'academy_yearly', 'hsp_monthly', 'hsp_yearly');

        if ($ctx === null) {
            $email            = sanitize_email($_POST['jecm_email'] ?? '');
            $contact_id       = absint($_POST['jecm_contact_id'] ?? 0);
            $selected_groups  = array_map('sanitize_key', wp_unslash($_POST['jecm_cancel_memberships'] ?? array()));
            $selected_groups  = array_values(array_intersect($selected_groups, $allowed_groups));
            $cancel_other     = !empty($_POST['jecm_cancel_other']);
            $other_note       = substr(sanitize_text_field(wp_unslash($_POST['jecm_other_note'] ?? '')), 0, 50);
            $cancel_reason    = sanitize_key($_POST['jecm_cancel_reason'] ?? 'requested');
            $subscription_id_force = null;
            $subscription_label    = '';
        } else {
            $email            = sanitize_email($ctx['email'] ?? '');
            $contact_id       = absint($ctx['contact_id'] ?? 0);
            $selected_groups  = array_values(array_intersect(
                array_map('sanitize_key', $ctx['memberships_to_cancel'] ?? array()),
                $allowed_groups
            ));
            $cancel_other     = !empty($ctx['cancel_other']);
            $other_note       = isset($ctx['other_note']) ? substr(sanitize_text_field($ctx['other_note']), 0, 50) : '';
            $cancel_reason    = sanitize_key($ctx['cancel_reason'] ?? 'customer_cancel');
            $sid              = isset($ctx['subscription_id']) ? absint($ctx['subscription_id']) : 0;
            $subscription_id_force = $sid > 0 ? $sid : null;
            $subscription_label    = sanitize_text_field($ctx['subscription_label'] ?? '');
        }

        $results = array();

        if (!$email || !$contact_id) {
            return array(array('label' => 'Validation', 'success' => false, 'message' => 'Invalid request — missing email or contact ID.'));
        }
        if (empty($selected_groups) && !$cancel_other) {
            return array(array('label' => 'Validation', 'success' => false, 'message' => 'Select at least one membership to cancel or log an additional note.'));
        }

        $cancel_academy = !empty(array_intersect($selected_groups, array('academy_monthly', 'academy_yearly')));
        $cancel_hsp     = !empty(array_intersect($selected_groups, array('hsp_monthly', 'hsp_yearly')));

        $is_monthly_academy = in_array('academy_monthly', $selected_groups, true);
        $is_yearly_academy  = in_array('academy_yearly', $selected_groups, true);
        $is_monthly_hsp     = in_array('hsp_monthly', $selected_groups, true);
        $is_yearly_hsp      = in_array('hsp_yearly', $selected_groups, true);

        $api_key = get_option('jecm_keap_api_key');

        if ($api_key) {
            $lookup = $this->lookup_student($email, $keap);
            if (!$lookup['success']) {
                $results[] = array('label' => 'Keap — Student Lookup', 'success' => false, 'message' => $lookup['message']);
            } elseif ((int) $lookup['contact_id'] !== (int) $contact_id) {
                $results[] = array('label' => 'Keap — Student Lookup', 'success' => false, 'message' => 'Contact ID does not match this email.');
            } else {
                $results[] = array('label' => 'Keap — Contact', 'success' => true, 'message' => 'Contact ID ' . $contact_id);

            // Access tag IDs per group (tags to REMOVE from contact)
            $access_tags_by_group = array(
                'academy_monthly' => array(9954, 9821, 9903, 9827, 10136, 10142),
                'academy_yearly'  => array(9956, 9813, 9819, 9815, 10290),
                'hsp_monthly'     => array(7548, 7574, 7578),
                'hsp_yearly'      => array(7548, 7574, 7578, 8893),
            );

            // Subscription plan name patterns per group (to match Keap subscription names)
            $sub_patterns_by_group = array(
                'academy_monthly' => array('JA_MONTHLY_', 'ACADEMY_STUDIO', 'ACADEMY_PREMIER', 'ACADEMY_ACADEMY'),
                'academy_yearly'  => array('JA_YEAR_', 'ACADEMY_ACADEMY_1YR'),
                'hsp_monthly'     => array('HSP_MONTHLY', 'HSP_MO'),
                'hsp_yearly'      => array('HSP_YEAR', 'HSP_ANNUAL'),
            );

            $last_login = $keap->interpret_custom_field_from_body($lookup['contact_body'], '_AcademyLastLogin');
            $results[] = array(
                'label'   => 'Keap — Academy Last Login',
                'success' => $last_login['success'],
                'message' => $last_login['success'] ? $last_login['display'] : ( $last_login['message'] ?? '' ),
            );

            $last_payment    = $lookup['last_payment'];
            $contact_tag_ids = $lookup['contact_tag_ids'];

            if ($last_payment['success']) {
                $last_ts = strtotime($last_payment['raw']);
                $academy_end_ts = null;
                $hsp_end_ts     = null;
                $end_parts      = array();

                if ($is_monthly_academy) {
                    $academy_end_ts = strtotime('+30 days', $last_ts);
                    $end_parts[] = 'Academy Monthly ends ' . date('F j, Y', $academy_end_ts);
                }
                if ($is_yearly_academy) {
                    $academy_end_ts = strtotime('+365 days', $last_ts);
                    $end_parts[] = 'Academy Yearly ends ' . date('F j, Y', $academy_end_ts);
                }
                if ($is_monthly_hsp) {
                    $hsp_end_ts = strtotime('+30 days', $last_ts);
                    $end_parts[] = 'HSP Monthly ends ' . date('F j, Y', $hsp_end_ts);
                }
                if ($is_yearly_hsp) {
                    $hsp_end_ts = strtotime('+365 days', $last_ts);
                    $end_parts[] = 'HSP Yearly ends ' . date('F j, Y', $hsp_end_ts);
                }

                $end_note = !empty($end_parts) ? ' — ' . implode(', ', $end_parts) : ' — No matching membership tags found on contact';

                $results[] = array(
                    'label'   => 'Keap — Last Payment Date',
                    'success' => true,
                    'message' => $last_payment['date'] . $end_note,
                );

                $keap_custom = array();
                $note_ts     = current_time('Y-m-d H:i:s');
                $note_value  = 'Updated by SJE Cancel Manager on ' . date('F j, Y \a\t g:i A', strtotime($note_ts));

                if ($cancel_academy && $academy_end_ts) {
                    $keap_custom['_AcademyExpirationDate'] = date('Y-m-d\T00:00:00.000\Z', $academy_end_ts);
                    $keap_custom['_AcademyExpirationNote'] = $note_value;
                }
                if ($cancel_hsp && $hsp_end_ts) {
                    $keap_custom['_HSPExpirationDate'] = date('Y-m-d\T00:00:00.000\Z', $hsp_end_ts);
                    $keap_custom['_HSPExpirationNote'] = $note_value;
                }

                if (!empty($keap_custom)) {
                    $cf_result = $keap->update_custom_fields($contact_id, $keap_custom);
                    $results[] = array(
                        'label'   => 'Keap — Update Custom Fields',
                        'success' => $cf_result['success'],
                        'message' => $cf_result['success']
                            ? 'AcademyExpirationDate + AcademyExpirationNote updated.'
                            : $cf_result['message'],
                    );
                }
            } else {
                $results[] = array(
                    'label'   => 'Keap — Last Payment Date',
                    'success' => false,
                    'message' => $last_payment['message'],
                );
            }

            if (!empty($selected_groups)) {
                if ($subscription_id_force) {
                    $cr = $keap->cancel_subscription($subscription_id_force, $contact_id);
                    $results[] = array(
                        'label'   => 'Keap — Cancel Subscription: ' . ($subscription_label ?: $subscription_id_force),
                        'success' => $cr['success'],
                        'message' => $cr['message'],
                    );
                } else {
                    // --- Step A: Get and cancel active subscriptions ---
                    $subs_result = $keap->get_subscriptions($contact_id);
                    if (!$subs_result['success']) {
                        $results[] = array('label' => 'Keap — Subscriptions', 'success' => false, 'message' => $subs_result['message'], '_debug' => null);
                    } else {
                        $cancelled_count = 0;
                        foreach ($subs_result['subscriptions'] as $sub) {
                            $plan_name = $sub['_product_name'] ?? '';
                            $sub_id    = $sub['id'] ?? 0;
                            $is_active = !empty($sub['active']);

                            if (!$is_active || !$sub_id) {
                                continue;
                            }

                            foreach ($selected_groups as $group) {
                                $patterns = $sub_patterns_by_group[$group] ?? array();
                                foreach ($patterns as $pattern) {
                                    if (strpos($plan_name, strtoupper($pattern)) !== false) {
                                        $cr = $keap->cancel_subscription($sub_id, $contact_id);
                                        $results[] = array(
                                            'label'   => 'Keap — Cancel Subscription: ' . ($plan_name ?: $sub_id),
                                            'success' => $cr['success'],
                                            'message' => $cr['message'],
                                        );
                                        $cancelled_count++;
                                        break 2;
                                    }
                                }
                            }
                        }

                        if ($cancelled_count === 0) {
                            $results[] = array(
                                'label'   => 'Keap — Subscriptions',
                                'success' => false,
                                'message' => 'No matching active subscriptions found.',
                                '_debug'  => $subs_result['_raw'], // visible in JSON copy below
                            );
                        }
                    }
                }
            }

            // --- Step C: Apply cancel tags (only if configured and > 0) ---
            $academy_cancel_tag = (int) get_option('jecm_keap_academy_tag_id', 0);
            $hsp_cancel_tag     = (int) get_option('jecm_keap_hsp_tag_id', 0);

            if ($cancel_academy && $academy_cancel_tag > 0) {
                $r = $keap->apply_tag($contact_id, $academy_cancel_tag);
                $results[] = array('label' => 'Keap — Academy Cancel Tag', 'success' => $r['success'], 'message' => $r['message']);
            }
            if ($cancel_hsp && $hsp_cancel_tag > 0) {
                $r = $keap->apply_tag($contact_id, $hsp_cancel_tag);
                $results[] = array('label' => 'Keap — HSP Cancel Tag', 'success' => $r['success'], 'message' => $r['message']);
            }
            }
        } else {
            $results[] = array('label' => 'Keap', 'success' => false, 'message' => 'No API key configured — go to Settings tab.');
        }

        if ($cancel_other) {
            $results[] = array('label' => 'Other — ' . ($other_note ?: 'no note'), 'success' => true, 'message' => 'Logged for record keeping. No tags sent.');
        }

        // FluentCRM
        if (!function_exists('FluentCrmApi')) {
            $results[] = array('label' => 'FluentCRM', 'success' => false, 'message' => 'FluentCRM not available.');
        } else {
            $contactApi = FluentCrmApi('contacts');
            $contact    = $contactApi->getContact($email);
            if (!$contact) {
                $contact = $contactApi->createOrUpdate(array('email' => $email));
            }
            if (!$contact) {
                $results[] = array('label' => 'FluentCRM — Find/Create Contact', 'success' => false, 'message' => 'Could not find or create contact.');
            } else {
                $results[] = array('label' => 'FluentCRM — Find Contact', 'success' => true, 'message' => $email);
                if ($cancel_academy) {
                    $contact->attachTags(array((int)get_option('jecm_fc_academy_tag_id', 153)));
                    $results[] = array('label' => 'FluentCRM — Academy Cancel Tag', 'success' => true, 'message' => 'Tag applied.');
                }
                if ($cancel_hsp) {
                    $contact->attachTags(array((int)get_option('jecm_fc_hsp_tag_id', 154)));
                    $results[] = array('label' => 'FluentCRM — HSP Cancel Tag', 'success' => true, 'message' => 'Tag applied.');
                }

                // Payment failed tag — sent regardless of membership type if reason is payment_failed
                if ($cancel_reason === 'payment_failed') {
                    $pf_tag_ids = array_filter(array_map('intval', explode(',', get_option('jecm_fc_payment_failed_tag_id', ''))));
                    if (!empty($pf_tag_ids)) {
                        $contact->attachTags($pf_tag_ids);
                        $results[] = array(
                            'label'   => 'FluentCRM — Payment Failed Tag',
                            'success' => true,
                            'message' => count($pf_tag_ids) . ' tag(s) applied: ' . implode(', ', $pf_tag_ids),
                        );
                    } else {
                        $results[] = array(
                            'label'   => 'FluentCRM — Payment Failed Tag',
                            'success' => false,
                            'message' => 'No Payment Failed tag configured — go to Settings tab.',
                        );
                    }
                }
            }
        }

        // Determine keap/fc success for log
        $keap_success = null;
        $fc_success   = null;
        foreach ($results as $r) {
            if (strpos($r['label'], 'Keap — Academy') !== false
                || strpos($r['label'], 'Keap — HSP') !== false
                || strpos($r['label'], 'Keap — Cancel Subscription') !== false) {
                $keap_success = ($keap_success === false) ? false : (int) $r['success'];
            }
            if (strpos($r['label'], 'FluentCRM —') !== false && strpos($r['label'], 'Tag') !== false) {
                $fc_success = ($fc_success === false) ? false : (int) $r['success'];
            }
        }

        $log_details = array('results' => $results);
        if ($ctx !== null) {
            $log_details['cancel_reason'] = $cancel_reason;
            if (!empty($ctx['subscription_id'])) {
                $log_details['subscription_id'] = (int) $ctx['subscription_id'];
            }
            if (!empty($ctx['subscription_label'])) {
                $log_details['product_name'] = $ctx['subscription_label'];
            }
        }

        JECM_Logger::log_action(array(
            'action'             => 'cancel',
            'email'              => $email,
            'contact_id'         => $contact_id,
            'contact_name'       => $this->get_keap_contact_display_name($contact_id),
            'cancel_reason'      => $cancel_reason,
            'details'            => $log_details,
            'membership_academy' => $cancel_academy,
            'membership_hsp'     => $cancel_hsp,
            'membership_other'   => $cancel_other,
            'other_note'         => $other_note,
            'keap_success'       => $keap_success,
            'fc_success'         => $fc_success,
        ));

        return $results;
    }

    private function jecm_audit_action_badge($action) {
        $action = sanitize_key($action ?: 'cancel');
        $map    = array(
            'cancel'                => array('label' => 'Update Subscription', 'bg' => '#2271b1'),
            'update_subscription'   => array('label' => 'Update Subscription', 'bg' => '#2271b1'),
            'remove_tag'            => array('label' => 'Remove Tag', 'bg' => '#db6b26'),
            'add_tag'               => array('label' => 'Add Tag', 'bg' => '#00a32a'),
            'apply_tag'             => array('label' => 'Add Tag', 'bg' => '#00a32a'),
            'lookup'                => array('label' => 'Lookup', 'bg' => '#646970'),
            'update_expiry'         => array('label' => 'Update Expiry', 'bg' => '#8c5e9e'),
            'task_complete'         => array('label' => 'Complete Task', 'bg' => '#1d7a3b'),
        );
        $m = $map[ $action ] ?? array('label' => $action, 'bg' => '#646970');

        return '<span style="display:inline-block;background:' . esc_attr($m['bg']) . ';color:#fff;border-radius:3px;padding:2px 8px;font-size:11px;">' . esc_html($m['label']) . '</span>';
    }

    private function jecm_audit_humanize_cancel_reason($reason) {
        $reason = (string) $reason;
        if ($reason === 'payment_failed') {
            return 'Payment failed';
        }
        if ($reason === 'customer_cancel' || $reason === 'requested') {
            return 'Customer requested';
        }
        return $reason !== '' ? $reason : '—';
    }

    private function jecm_audit_log_summary($action, $det_dec, $row) {
        $action = sanitize_key($action ?: 'cancel');

        if ($action === 'cancel' || $action === 'update_subscription') {
            $prod = '';
            if (is_array($det_dec)) {
                if (!empty($det_dec['product_name'])) {
                    $prod = (string) $det_dec['product_name'];
                } elseif (!empty($det_dec['subscription_id'])) {
                    $prod = 'Sub #' . (string) (int) $det_dec['subscription_id'];
                }
            }

            $reason_raw = '';
            if (is_array($det_dec) && isset($det_dec['cancel_reason']) && $det_dec['cancel_reason'] !== '' && $det_dec['cancel_reason'] !== null) {
                $reason_raw = $det_dec['cancel_reason'];
            } elseif (!empty($row['cancel_reason'])) {
                $reason_raw = $row['cancel_reason'];
            }
            $reason = $this->jecm_audit_humanize_cancel_reason($reason_raw);
            if ($reason === '—') {
                $reason = '';
            }

            if ($prod !== '' && $reason !== '') {
                return $prod . ' — ' . $reason;
            }
            if ($prod !== '') {
                return $prod;
            }
            if ($reason !== '') {
                return $reason;
            }

            return '—';
        }

        if ($action === 'remove_tag') {
            if (is_array($det_dec)) {
                $tname = isset($det_dec['tag_name']) ? trim((string) $det_dec['tag_name']) : '';
                if ($tname !== '') {
                    return 'Removed: ' . $tname;
                }
                if (isset($det_dec['tag_id']) && $det_dec['tag_id'] !== '' && $det_dec['tag_id'] !== null) {
                    return 'Tag ID ' . (string) (int) $det_dec['tag_id'];
                }
            }

            return '—';
        }

        if ($action === 'apply_tag' || $action === 'add_tag') {
            if (is_array($det_dec)) {
                $tname = isset($det_dec['tag_name']) ? trim((string) $det_dec['tag_name']) : '';
                if ($tname !== '') {
                    return 'Added: ' . $tname;
                }
                if (isset($det_dec['tag_id']) && $det_dec['tag_id'] !== '' && $det_dec['tag_id'] !== null) {
                    return 'Tag ID ' . (string) (int) $det_dec['tag_id'];
                }
            }

            return '—';
        }

        if ($action === 'lookup') {
            return 'Viewed record';
        }

        if ($action === 'update_expiry') {
            if (is_array($det_dec)) {
                $fl = isset($det_dec['field']) ? (string) $det_dec['field'] : '';
                $nd = isset($det_dec['new_date']) ? (string) $det_dec['new_date'] : '';
                if ($fl !== '') {
                    return $nd !== '' ? $fl . ': ' . $nd : $fl . ': cleared';
                }
            }

            return '—';
        }

        if ($action === 'task_complete') {
            if (is_array($det_dec)) {
                $ex = isset($det_dec['notes_excerpt']) ? trim((string) $det_dec['notes_excerpt']) : '';
                if ($ex !== '') {
                    return $ex;
                }
                if (!empty($det_dec['task_id'])) {
                    return 'Task #' . (string) (int) $det_dec['task_id'];
                }
            }

            return '—';
        }

        return '—';
    }
}
