<?php
/**
 * Admin menu, UI, and AJAX handlers.
 */

if (!defined('ABSPATH')) {
    exit;
}

class FC_Helper_Admin_Page {

    const NONCE_ACTION = 'fc_helper_nonce';

    /** @var string */
    const OPTION_SAVED_PRODUCTS = 'fc_helper_saved_products';

    /** @var string Default sample video splash when none chosen (shown in form + AI prompts). */
    const DEFAULT_SAMPLE_SPLASH_URL = 'https://shop.jazzedge.com/wp-content/uploads/2026/04/splash-play-video.jpg.webp';

    /**
     * @var self|null
     */
    private static $instance = null;

    /**
     * @return self
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_fc_helper_generate', array($this, 'ajax_generate'));
        add_action('wp_ajax_fc_helper_revise', array($this, 'ajax_revise'));
        add_action('wp_ajax_fc_helper_save_product', array($this, 'ajax_save_product'));
        add_action('wp_ajax_fc_helper_load_products', array($this, 'ajax_load_products'));
        add_action('wp_ajax_fc_helper_delete_product', array($this, 'ajax_delete_product'));
        add_action('wp_ajax_fc_helper_search_lessons', array($this, 'ajax_search_lessons'));
        add_action('wp_ajax_fc_helper_get_lesson', array($this, 'ajax_get_lesson'));
        add_action('wp_ajax_fc_helper_save_theme', array($this, 'ajax_save_theme'));
        add_action('wp_ajax_fc_helper_delete_theme', array($this, 'ajax_delete_theme'));
        add_action('wp_ajax_fc_helper_reset_themes', array($this, 'ajax_reset_themes'));
        add_action('wp_ajax_fc_helper_suggest_theme_colors', array($this, 'ajax_suggest_theme_colors'));
    }

    public function register_menu() {
        add_menu_page(
            __('FC Helper', 'fc-helper'),
            __('FC Helper', 'fc-helper'),
            'manage_options',
            'fc-helper',
            array($this, 'render_page'),
            'dashicons-media-code',
            58
        );
    }

    /**
     * @param string $hook
     */
    public function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_fc-helper') {
            return;
        }
        wp_enqueue_style(
            'fc-helper-fonts',
            'https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap',
            array(),
            null
        );
        wp_enqueue_style(
            'fc-helper-admin',
            FC_HELPER_PLUGIN_URL . 'assets/css/admin.css',
            array('fc-helper-fonts'),
            FC_HELPER_VERSION
        );
        wp_enqueue_media();
        wp_enqueue_script(
            'fc-helper-admin',
            FC_HELPER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'media-views'),
            FC_HELPER_VERSION,
            true
        );

        $themes = FC_Helper_AI_Generator::get_themes();
        $themes_full  = array();
        foreach ($themes as $slug => $t ) {
            $themes_full[] = array_merge(
                array( 'slug' => $slug ),
                $t
            );
        }

        wp_localize_script(
            'fc-helper-admin',
            'fcHelper',
            array(
                'ajaxUrl'               => admin_url('admin-ajax.php'),
                'nonce'                 => wp_create_nonce(self::NONCE_ACTION),
                'themes'                => $themes_full,
                'builtinThemeSlugs'     => array_keys(FC_Helper_AI_Generator::get_default_themes()),
                'savedProducts'         => $this->get_saved_products_for_js(),
                'defaultSampleSplashUrl' => self::DEFAULT_SAMPLE_SPLASH_URL,
                'i18n'           => array(
                    'copied'         => __('Copied!', 'fc-helper'),
                    'error'          => __('Something went wrong.', 'fc-helper'),
                    'generating'     => __('Generating…', 'fc-helper'),
                    'characters'     => __('characters', 'fc-helper'),
                    'output'         => __('Output', 'fc-helper'),
                    'copyClipboard'  => __('Copy to Clipboard', 'fc-helper'),
                    'generateHtml'   => __('Generate HTML', 'fc-helper'),
                    'tabOutput'      => __('Output HTML', 'fc-helper'),
                    'tabPrompt'      => __('Prompt Sent', 'fc-helper'),
                    'tabDebug'       => __('Debug Log', 'fc-helper'),
                    'promptLabel'    => __('This is the exact prompt sent to the AI.', 'fc-helper'),
                    'clearDebug'     => __('Clear Debug Log', 'fc-helper'),
                    'chooseSplash'   => __('Choose image', 'fc-helper'),
                    'removeSplash'   => __('Remove', 'fc-helper'),
                    'splashFrameTit'  => __('Select splash image', 'fc-helper'),
                    'splashFrameBtn'  => __('Use this image', 'fc-helper'),
                    'selectSaved'     => __('— Select a saved product —', 'fc-helper'),
                    'saveProductPrompt' => __('Name this saved product', 'fc-helper'),
                    'nameRequired'    => __('Please enter a product name.', 'fc-helper'),
                    'productSaved'    => __('Product saved.', 'fc-helper'),
                    'productUpdated'  => __('Product updated.', 'fc-helper'),
                    'saveProduct'     => __('Save Product', 'fc-helper'),
                    'updateProduct'   => __('Update Product', 'fc-helper'),
                    'productUpdatedHtml' => __('HTML saved to product.', 'fc-helper'),
                    'productDeleted'  => __('Saved product removed.', 'fc-helper'),
                    'themeManagerTitle' => __('Theme Manager', 'fc-helper'),
                    'themeResetConfirm' => __('Restore all themes to the original defaults? Custom themes will be removed.', 'fc-helper'),
                    'themeDeleteConfirm' => __('Delete this theme?', 'fc-helper'),
                    'themeInvalidData' => __('Invalid theme data.', 'fc-helper'),
                    'themeSlugRequired' => __('Theme slug is required.', 'fc-helper'),
                    'themeSaved'        => __('Theme saved.', 'fc-helper'),
                    'primaryLabel'      => __('Primary', 'fc-helper'),
                    'primaryLesson'     => __('Primary lesson', 'fc-helper'),
                    'viewLesson'        => __('View', 'fc-helper'),
                    'editLesson'        => __('Edit', 'fc-helper'),
                    'makePrimary'       => __('Make primary', 'fc-helper'),
                    'makePrimaryBtn'    => __('Make Primary', 'fc-helper'),
                    'removeLesson'      => __('Remove lesson', 'fc-helper'),
                    'maxLessons'        => __('Maximum 10 lessons.', 'fc-helper'),
                    'lessonsSelected'   => __('selected', 'fc-helper'),
                    'alreadyAdded'      => __('Already added', 'fc-helper'),
                    'productNameRequired' => __('Please enter a product name.', 'fc-helper'),
                ),
            )
        );
    }

    /**
     * Themes as a list of objects for JS / AJAX (slug + all fields).
     *
     * @return array<int, array<string, string>>
     */
    private function themes_for_ajax_response() {
        $list = array();
        foreach (FC_Helper_AI_Generator::get_themes() as $slug => $t ) {
            $list[] = array_merge( array( 'slug' => $slug ), $t );
        }
        return $list;
    }

    public function ajax_save_theme() {
        $this->verify_ajax_basic();
        $slug = sanitize_key(wp_unslash($_POST['theme_slug'] ?? ''));
        $raw  = wp_unslash($_POST['theme_data'] ?? '');
        if (!is_string($raw) || '' === $raw) {
            wp_send_json_error(array('message' => __('Invalid theme data.', 'fc-helper')));
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            wp_send_json_error(array('message' => __('Invalid theme data.', 'fc-helper')));
        }
        if ('' === $slug) {
            wp_send_json_error(array('message' => __('Theme slug is required.', 'fc-helper')));
        }
        $defaults = FC_Helper_AI_Generator::get_default_themes();
        $fb       = isset($defaults[ $slug ]) ? $defaults[ $slug ] : $defaults['dark_gold'];
        $row      = array(
            'label' => isset($decoded['label']) ? (string) $decoded['label'] : '',
        );
        $color_keys = array(
            'primary',
            'accent',
            'bg_dark',
            'bg_mid',
            'bg_gradient_end',
            'gold',
            'gold_dark',
            'text_light',
            'text_muted',
            'bg_cream',
            'bg_warm',
            'border_warm',
            'text_on_light',
        );
        foreach ($color_keys as $ck ) {
            $row[ $ck ] = isset($decoded[ $ck ]) ? (string) $decoded[ $ck ] : '';
        }
        $san               = FC_Helper_AI_Generator::normalize_theme_row($row, $fb);
        $themes            = FC_Helper_AI_Generator::get_themes();
        $themes[ $slug ] = $san;
        FC_Helper_AI_Generator::save_themes($themes);
        wp_send_json_success(array('themes' => $this->themes_for_ajax_response()));
    }

    public function ajax_delete_theme() {
        $this->verify_ajax_basic();
        $slug = sanitize_key(wp_unslash($_POST['theme_slug'] ?? ''));
        if ('' === $slug) {
            wp_send_json_error(array('message' => __('Theme slug is required.', 'fc-helper')));
        }
        $themes = FC_Helper_AI_Generator::get_themes();
        if (!isset($themes[ $slug ])) {
            wp_send_json_error(array('message' => __('Theme not found.', 'fc-helper')));
        }
        unset($themes[ $slug ]);
        FC_Helper_AI_Generator::save_themes($themes);
        wp_send_json_success(array('themes' => $this->themes_for_ajax_response()));
    }

    public function ajax_reset_themes() {
        $this->verify_ajax_basic();
        FC_Helper_AI_Generator::save_themes(FC_Helper_AI_Generator::get_default_themes());
        wp_send_json_success(array('themes' => $this->themes_for_ajax_response()));
    }

    public function ajax_suggest_theme_colors() {
        $this->verify_ajax();
        $raw_mc = wp_unslash($_POST['master_color'] ?? '');
        $master = is_string($raw_mc) ? sanitize_hex_color($raw_mc) : '';
        if (!$master) {
            wp_send_json_error(array('message' => __('Invalid master color.', 'fc-helper')));
        }
        $theme_name = sanitize_text_field(wp_unslash($_POST['theme_name'] ?? ''));
        $result     = FC_Helper_AI_Generator::suggest_colors($master, $theme_name);
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        wp_send_json_success(array('colors' => $result));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function get_saved_products_for_js() {
        $list = $this->get_saved_products();
        foreach ($list as $i => $p ) {
            if (isset($p['data']) && is_array($p['data'])) {
                $list[ $i ]['data'] = $this->attach_lesson_display_meta($p['data']);
            }
        }
        usort(
            $list,
            function ( $a, $b ) {
                return strcasecmp($a['name'], $b['name']);
            }
        );
        return $list;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function get_saved_products() {
        $raw = get_option(self::OPTION_SAVED_PRODUCTS, array());
        if (!is_array($raw)) {
            return array();
        }
        $out = array();
        foreach ($raw as $row ) {
            $n = $this->normalize_saved_product_row($row);
            if (null !== $n) {
                $out[] = $n;
            }
        }
        return $out;
    }

    /**
     * @param mixed $row
     * @return array<string, mixed>|null
     */
    private function normalize_saved_product_row($row) {
        if (!is_array($row)) {
            return null;
        }
        $id   = isset($row['id']) ? sanitize_text_field((string) $row['id']) : '';
        $name = isset($row['name']) ? sanitize_text_field((string) $row['name']) : '';
        if ('' === $id || '' === $name) {
            return null;
        }
        $created = isset($row['created']) ? absint($row['created']) : 0;
        if ($created <= 0) {
            $created = time();
        }
        $data = isset($row['data']) && is_array($row['data']) ? $this->normalize_saved_product_data($row['data']) : array();
        return array(
            'id'      => $id,
            'name'    => $name,
            'created' => $created,
            'data'    => $data,
        );
    }

    /**
     * @param array<mixed, mixed> $data
     * @return list<array{id:int, primary:bool}>
     */
    private function parse_stored_lesson_ids($data) {
        if (!isset($data['lesson_ids'])) {
            return array();
        }
        $v = $data['lesson_ids'];
        if (is_string($v) && $v !== '') {
            $decoded = json_decode($v, true);
            $v       = is_array($decoded) ? $decoded : array();
        }
        if (!is_array($v)) {
            return array();
        }
        $out = array();
        foreach ($v as $row ) {
            if (!is_array($row)) {
                continue;
            }
            $id = isset($row['id']) ? absint($row['id']) : 0;
            if ($id <= 0) {
                continue;
            }
            $out[] = array(
                'id'      => $id,
                'primary' => !empty($row['primary']),
            );
        }
        return $out;
    }

    /**
     * @param array<mixed, mixed> $data
     * @return list<array{lesson: array<string, mixed>, chapters: array<int, array<string, mixed>>}>|null
     */
    private function parse_stored_lesson_data_list($data) {
        if (!isset($data['lesson_data_list'])) {
            return null;
        }
        $v = $data['lesson_data_list'];
        if (is_string($v) && $v !== '') {
            $decoded = json_decode($v, true);
            $v       = is_array($decoded) ? $decoded : null;
        }
        if (!is_array($v)) {
            return null;
        }
        $out = array();
        foreach ($v as $item ) {
            if (!is_array($item) || empty($item['lesson']) || !is_array($item['lesson'])) {
                continue;
            }
            $out[] = $this->sanitize_lesson_payload($item);
        }
        return $out === array() ? null : $out;
    }

    /**
     * Dedupe by id (first wins), cap at 10, enforce exactly one primary.
     *
     * @param list<array{id:int, primary:bool}> $rows
     * @return list<array{id:int, primary:bool}>
     */
    private function normalize_lesson_ids_rows(array $rows) {
        $seen = array();
        $out  = array();
        foreach ($rows as $row ) {
            if (!is_array($row)) {
                continue;
            }
            $id = isset($row['id']) ? absint($row['id']) : 0;
            if ($id <= 0 || isset($seen[ $id ])) {
                continue;
            }
            $seen[ $id ] = true;
            $out[]       = array(
                'id'      => $id,
                'primary' => !empty($row['primary']),
            );
            if (count($out) >= 10) {
                break;
            }
        }
        if (array() === $out) {
            return array();
        }
        $primary_count = 0;
        foreach ($out as $r ) {
            if (!empty($r['primary'])) {
                ++$primary_count;
            }
        }
        if (0 === $primary_count) {
            $out[0]['primary'] = true;
        } elseif ($primary_count > 1 ) {
            $found = false;
            foreach ($out as $i => $r ) {
                if (!empty($r['primary'])) {
                    if (!$found) {
                        $found = true;
                    } else {
                        $out[ $i ]['primary'] = false;
                    }
                }
            }
        }
        return $out;
    }

    /**
     * @param list<array{id:int, primary:bool}> $ids_rows
     * @return int
     */
    private function primary_lesson_id_from_rows(array $ids_rows) {
        foreach ($ids_rows as $r ) {
            if (!empty($r['primary'])) {
                return (int) $r['id'];
            }
        }
        return isset($ids_rows[0]['id']) ? (int) $ids_rows[0]['id'] : 0;
    }

    /**
     * @param list<array{id:int, primary:bool}> $ids_rows
     * @param list<array{lesson: array<string, mixed>, chapters: array<int, array<string, mixed>>> $payloads
     * @return list<array{lesson: array<string, mixed>, chapters: array<int, array<string, mixed>>}>
     */
    private function realign_lesson_data_list(array $ids_rows, array $payloads) {
        $by_id = array();
        foreach ($payloads as $pl ) {
            if (!is_array($pl) || empty($pl['lesson']) || !is_array($pl['lesson'])) {
                continue;
            }
            $id = isset($pl['lesson']['id']) ? absint($pl['lesson']['id']) : 0;
            if ($id > 0) {
                $by_id[ $id ] = $pl;
            }
        }
        $out = array();
        foreach ($ids_rows as $row ) {
            $id = (int) $row['id'];
            if (isset($by_id[ $id ])) {
                $out[] = $by_id[ $id ];
            }
        }
        return $out;
    }

    /**
     * @param array<mixed, mixed> $data
     * @return array<string, mixed>
     */
    private function normalize_saved_product_data($data) {
        $theme = isset($data['theme']) ? sanitize_key((string) $data['theme']) : 'dark_gold';
        $themes = FC_Helper_AI_Generator::get_themes();
        if (!isset($themes[ $theme ])) {
            if (isset($themes['dark_gold'])) {
                $theme = 'dark_gold';
            } else {
                $first = array_key_first($themes);
                $theme = $first ? $first : 'dark_gold';
            }
        }
        $lesson_id   = isset($data['lesson_id']) ? absint($data['lesson_id']) : 0;
        $lesson_data = null;
        if (isset($data['lesson_data'])) {
            if (is_array($data['lesson_data'])) {
                $lesson_data = $data['lesson_data'];
            } elseif (is_string($data['lesson_data']) && $data['lesson_data'] !== '') {
                $decoded = json_decode($data['lesson_data'], true);
                $lesson_data = is_array($decoded) ? $decoded : null;
            }
        }
        $lesson_ids_rows   = $this->parse_stored_lesson_ids($data);
        $lesson_data_list  = $this->parse_stored_lesson_data_list($data);

        if (array() === $lesson_ids_rows && $lesson_id > 0) {
            $lesson_ids_rows = array(
                array(
                    'id'      => $lesson_id,
                    'primary' => true,
                ),
            );
        }

        if (array() === $lesson_ids_rows && null !== $lesson_data && isset($lesson_data['lesson']['id'])) {
            $lid = absint($lesson_data['lesson']['id']);
            if ($lid > 0) {
                $lesson_ids_rows = array(
                    array(
                        'id'      => $lid,
                        'primary' => true,
                    ),
                );
                if (null === $lesson_data_list) {
                    $lesson_data_list = array( $lesson_data );
                }
            }
        }

        $lesson_ids_rows = $this->normalize_lesson_ids_rows($lesson_ids_rows);

        if (array() === $lesson_ids_rows) {
            $lesson_id = 0;
        } else {
            $lesson_id = $this->primary_lesson_id_from_rows($lesson_ids_rows);
            if (is_array($lesson_data_list) && count($lesson_data_list) > 0) {
                $lesson_data_list = $this->realign_lesson_data_list($lesson_ids_rows, $lesson_data_list);
            } elseif (null !== $lesson_data && 1 === count($lesson_ids_rows)) {
                $only = (int) $lesson_ids_rows[0]['id'];
                $lid  = isset($lesson_data['lesson']['id']) ? absint($lesson_data['lesson']['id']) : 0;
                if ($lid === $only) {
                    $lesson_data_list = array( $lesson_data );
                }
            }
            if (is_array($lesson_data_list)) {
                foreach ($lesson_data_list as $pl ) {
                    if (!is_array($pl) || empty($pl['lesson'])) {
                        continue;
                    }
                    $plid = isset($pl['lesson']['id']) ? absint($pl['lesson']['id']) : 0;
                    if ($plid === $lesson_id) {
                        $lesson_data = $pl;
                        break;
                    }
                }
            }
        }

        return array(
            'product_title'           => sanitize_text_field(isset($data['product_title']) ? (string) $data['product_title'] : ''),
            'description'             => isset($data['description']) ? sanitize_textarea_field((string) $data['description']) : '',
            'sample_video_url'        => esc_url_raw(isset($data['sample_video_url']) ? (string) $data['sample_video_url'] : ''),
            'sample_video_splash_url' => esc_url_raw(isset($data['sample_video_splash_url']) ? (string) $data['sample_video_splash_url'] : ''),
            'additional_notes'        => isset($data['additional_notes']) ? sanitize_textarea_field((string) $data['additional_notes']) : '',
            'theme'                   => $theme,
            'lesson_id'               => $lesson_id,
            'lesson_data'             => $lesson_data,
            'lesson_ids'              => $lesson_ids_rows,
            'lesson_data_list'        => $lesson_data_list,
            'html_output'             => isset($data['html_output'])
                ? (
                    current_user_can('unfiltered_html')
                        ? (string) $data['html_output']
                        : wp_kses_post((string) $data['html_output'])
                )
                : '',
        );
    }

    /**
     * Enriched lesson rows for admin JS (not persisted).
     *
     * @param list<array{id:int, primary:bool}> $rows
     * @return list<array<string, mixed>>
     */
    private function batch_fetch_lesson_display_meta(array $rows) {
        global $wpdb;
        $ids = array();
        foreach ($rows as $r ) {
            if (!empty($r['id'])) {
                $ids[] = (int) $r['id'];
            }
        }
        $ids = array_values(array_unique(array_filter(array_map('absint', $ids))));
        if (array() === $ids) {
            return array();
        }
        $lessons_t = $wpdb->prefix . 'alm_lessons';
        $coll_t    = $wpdb->prefix . 'alm_collections';
        if (!$this->alm_table_exists($lessons_t)) {
            return array();
        }
        $in_list = implode(',', $ids);
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if ($this->alm_table_exists($coll_t)) {
            $db_rows = $wpdb->get_results(
                "SELECT l.ID, l.lesson_title, l.collection_id, COALESCE(c.collection_title, '') AS collection_title, l.post_id
                FROM {$lessons_t} l
                LEFT JOIN {$coll_t} c ON c.ID = l.collection_id
                WHERE l.ID IN ({$in_list})",
                ARRAY_A
            );
        } else {
            $db_rows = $wpdb->get_results(
                "SELECT ID, lesson_title, collection_id, '' AS collection_title, post_id FROM {$lessons_t} WHERE ID IN ({$in_list})",
                ARRAY_A
            );
        }
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if (!is_array($db_rows)) {
            $db_rows = array();
        }
        $map = array();
        foreach ($db_rows as $dbrow ) {
            if (!is_array($dbrow) || empty($dbrow['ID'])) {
                continue;
            }
            $map[ (int) $dbrow['ID'] ] = $dbrow;
        }
        $chapters_by = $this->batch_fetch_chapters_for_lesson_ids($ids);
        $out         = array();
        foreach ($rows as $r ) {
            $id = (int) $r['id'];
            if (!isset($map[ $id ])) {
                continue;
            }
            $dbrow    = $map[ $id ];
            $post_id  = isset($dbrow['post_id']) ? (int) $dbrow['post_id'] : 0;
            $view_raw = ($post_id > 0) ? get_permalink($post_id) : '';
            $out[]    = array(
                'id'               => $id,
                'title'            => isset($dbrow['lesson_title']) ? sanitize_text_field((string) $dbrow['lesson_title']) : '',
                'collection_id'    => isset($dbrow['collection_id']) ? (int) $dbrow['collection_id'] : 0,
                'collection_title' => isset($dbrow['collection_title']) ? sanitize_text_field((string) $dbrow['collection_title']) : '',
                'edit_url'         => admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . $id),
                'view_url'         => $view_raw ? esc_url_raw((string) $view_raw) : '',
                'primary'          => !empty($r['primary']),
                'chapters'         => isset($chapters_by[ $id ]) ? $chapters_by[ $id ] : array(),
            );
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function attach_lesson_display_meta(array $data) {
        $rows = isset($data['lesson_ids']) && is_array($data['lesson_ids']) ? $data['lesson_ids'] : array();
        if (array() === $rows && !empty($data['lesson_id'])) {
            $rows = array(
                array(
                    'id'      => (int) $data['lesson_id'],
                    'primary' => true,
                ),
            );
        }
        if (array() === $rows) {
            return $data;
        }
        $data['lesson_ids_display'] = $this->batch_fetch_lesson_display_meta($rows);
        return $data;
    }

    /**
     * @return string
     */
    private function new_saved_product_id() {
        if (function_exists('wp_generate_uuid4')) {
            return wp_generate_uuid4();
        }
        return 'fc_' . uniqid('', true);
    }

    /**
     * Ajax: capability + nonce only (no hub required).
     */
    private function verify_ajax_basic() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'fc-helper')), 403);
        }
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
    }

    /**
     * @param string $table Full table name including prefix.
     */
    private function alm_table_exists($table) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
    }

    /**
     * Format non-negative seconds as HH:MM:SS (hours may exceed two digits if needed).
     *
     * @param int $sec
     * @return string
     */
    private function format_seconds_hhmmss($sec) {
        $sec = max(0, (int) $sec);
        $h   = (int) floor($sec / 3600);
        $m   = (int) floor(($sec % 3600) / 60);
        $s   = (int) ($sec % 60);
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    /**
     * Short duration for labels, e.g. 2:34 or 1:05:02.
     *
     * @param int $sec
     * @return string
     */
    private function format_duration_human($sec) {
        $sec = max(0, (int) $sec);
        $h   = (int) floor($sec / 3600);
        $m   = (int) floor(($sec % 3600) / 60);
        $s   = (int) ($sec % 60);
        if ($h > 0) {
            return sprintf('%d:%02d:%02d', $h, $m, $s);
        }
        return sprintf('%d:%02d', $m, $s);
    }

    /**
     * Chapter rows for one lesson (ALM wp_*alm_chapters). Optionally loads transcripts.
     *
     * @param int         $lesson_id
     * @param string      $chapters_t
     * @param string|null $transcripts_t Pass null to skip transcript queries.
     * @return list<array<string, mixed>>
     */
    private function build_chapters_payload_for_lesson_id($lesson_id, $chapters_t, $transcripts_t = null) {
        global $wpdb;
        $lesson_id = (int) $lesson_id;
        if ($lesson_id <= 0 || !$this->alm_table_exists($chapters_t)) {
            return array();
        }
        $load_transcripts = null !== $transcripts_t && $this->alm_table_exists($transcripts_t );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $chapter_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, chapter_title, menu_order, duration FROM {$chapters_t} WHERE lesson_id = %d ORDER BY menu_order ASC, ID ASC",
                $lesson_id
            ),
            ARRAY_A
        );
        if (!is_array($chapter_rows)) {
            $chapter_rows = array();
        }
        $chapters_out = array();
        $cumulative     = 0;
        foreach ($chapter_rows as $crow ) {
            $chid     = isset($crow['ID']) ? (int) $crow['ID'] : 0;
            $duration = isset($crow['duration']) ? max(0, (int) $crow['duration']) : 0;
            $trans    = null;
            if ($load_transcripts) {
                $raw_content = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT content FROM {$transcripts_t} WHERE lesson_id = %d AND chapter_id = %d ORDER BY ID DESC LIMIT 1",
                        $lesson_id,
                        $chid
                    )
                );
                if (is_string($raw_content) && $raw_content !== '') {
                    $trans = wp_strip_all_tags($raw_content);
                    if ($trans === '') {
                        $trans = null;
                    }
                }
            }
            $start_ts = $this->format_seconds_hhmmss($cumulative);
            $chapters_out[] = array(
                'id'               => $chid,
                'title'            => isset($crow['chapter_title']) ? sanitize_text_field((string) $crow['chapter_title']) : '',
                'menu_order'       => isset($crow['menu_order']) ? (int) $crow['menu_order'] : 0,
                'duration_seconds' => $duration,
                'start_time'       => $start_ts,
                'timestamp'        => $start_ts,
                'duration'         => $this->format_duration_human($duration),
                'transcript'       => $trans,
            );
            $cumulative += $duration;
        }
        return $chapters_out;
    }

    /**
     * Batch-load chapter rows for many lessons (single query). No transcripts (use ajax_get_lesson for full data).
     *
     * @param list<int> $lesson_ids
     * @return array<int, list<array<string, mixed>>>
     */
    private function batch_fetch_chapters_for_lesson_ids(array $lesson_ids) {
        global $wpdb;
        $lesson_ids = array_values(array_unique(array_filter(array_map('absint', $lesson_ids))));
        if (array() === $lesson_ids) {
            return array();
        }
        $chapters_t = $wpdb->prefix . 'alm_chapters';
        if (!$this->alm_table_exists($chapters_t)) {
            return array();
        }
        $in_list = implode(',', $lesson_ids);
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results(
            "SELECT ID, lesson_id, chapter_title, menu_order, duration FROM {$chapters_t} WHERE lesson_id IN ({$in_list}) ORDER BY lesson_id ASC, menu_order ASC, ID ASC",
            ARRAY_A
        );
        if (!is_array($rows)) {
            $rows = array();
        }
        $grouped = array();
        foreach ($rows as $crow ) {
            $lid = isset($crow['lesson_id']) ? (int) $crow['lesson_id'] : 0;
            if ($lid <= 0) {
                continue;
            }
            if (!isset($grouped[ $lid ])) {
                $grouped[ $lid ] = array();
            }
            $grouped[ $lid ][] = $crow;
        }
        $out = array();
        foreach ($lesson_ids as $lid ) {
            $lid = (int) $lid;
            $chapter_rows = isset($grouped[ $lid ]) ? $grouped[ $lid ] : array();
            $chapters_out = array();
            $cumulative   = 0;
            foreach ($chapter_rows as $crow ) {
                $chid     = isset($crow['ID']) ? (int) $crow['ID'] : 0;
                $duration = isset($crow['duration']) ? max(0, (int) $crow['duration']) : 0;
                $start_ts = $this->format_seconds_hhmmss($cumulative);
                $chapters_out[] = array(
                    'id'               => $chid,
                    'title'            => isset($crow['chapter_title']) ? sanitize_text_field((string) $crow['chapter_title']) : '',
                    'menu_order'       => isset($crow['menu_order']) ? (int) $crow['menu_order'] : 0,
                    'duration_seconds' => $duration,
                    'start_time'       => $start_ts,
                    'timestamp'        => $start_ts,
                    'duration'         => $this->format_duration_human($duration),
                );
                $cumulative += $duration;
            }
            $out[ $lid ] = $chapters_out;
        }
        return $out;
    }

    public function ajax_search_lessons() {
        $this->verify_ajax_basic();
        global $wpdb;
        $s = sanitize_text_field(wp_unslash($_POST['s'] ?? ''));
        if (strlen($s) < 2) {
            wp_send_json_success(array('lessons' => array()));
        }
        $table         = $wpdb->prefix . 'alm_lessons';
        $coll_table    = $wpdb->prefix . 'alm_collections';
        if (!$this->alm_table_exists($table)) {
            wp_send_json_success(array('lessons' => array()));
        }
        $like = '%' . $wpdb->esc_like($s) . '%';
        if ($this->alm_table_exists($coll_table)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names trusted (prefixed).
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT l.ID, l.lesson_title, l.collection_id, COALESCE(c.collection_title, '') AS collection_title
                    FROM {$table} l
                    LEFT JOIN {$coll_table} c ON c.ID = l.collection_id
                    WHERE l.lesson_title LIKE %s
                    ORDER BY l.lesson_title ASC
                    LIMIT 10",
                    $like
                ),
                ARRAY_A
            );
        } else {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name trusted (prefixed).
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT ID, lesson_title, collection_id, '' AS collection_title FROM {$table} WHERE lesson_title LIKE %s ORDER BY lesson_title ASC LIMIT 10",
                    $like
                ),
                ARRAY_A
            );
        }
        if (!is_array($rows)) {
            $rows = array();
        }
        $out = array();
        foreach ($rows as $row ) {
            $cid   = isset($row['collection_id']) ? (int) $row['collection_id'] : 0;
            $ctit  = isset($row['collection_title']) ? sanitize_text_field((string) $row['collection_title']) : '';
            $out[] = array(
                'id'               => isset($row['ID']) ? (int) $row['ID'] : 0,
                'title'            => isset($row['lesson_title']) ? sanitize_text_field((string) $row['lesson_title']) : '',
                'collection_id'    => $cid,
                'collection_title' => $ctit,
            );
        }
        wp_send_json_success(array('lessons' => $out));
    }

    public function ajax_get_lesson() {
        $this->verify_ajax_basic();
        global $wpdb;
        $lesson_id = isset($_POST['lesson_id']) ? absint(wp_unslash($_POST['lesson_id'])) : 0;
        if ($lesson_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid lesson.', 'fc-helper')));
        }
        $lessons_t     = $wpdb->prefix . 'alm_lessons';
        $chapters_t    = $wpdb->prefix . 'alm_chapters';
        $transcripts_t = $wpdb->prefix . 'alm_transcripts';
        $coll_table    = $wpdb->prefix . 'alm_collections';
        if (!$this->alm_table_exists($lessons_t) || !$this->alm_table_exists($chapters_t)) {
            wp_send_json_error(array('message' => __('Lesson tables not found.', 'fc-helper')));
        }
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- prefixed table names.
        if ($this->alm_table_exists($coll_table)) {
            $lesson_row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT l.ID, l.lesson_title, l.lesson_description, l.sample_video_url, l.sample_chapter_id, l.collection_id, l.post_id, COALESCE(c.collection_title, '') AS collection_title
                    FROM {$lessons_t} l
                    LEFT JOIN {$coll_table} c ON c.ID = l.collection_id
                    WHERE l.ID = %d",
                    $lesson_id
                ),
                ARRAY_A
            );
        } else {
            $lesson_row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT ID, lesson_title, lesson_description, sample_video_url, sample_chapter_id, collection_id, post_id, '' AS collection_title FROM {$lessons_t} WHERE ID = %d",
                    $lesson_id
                ),
                ARRAY_A
            );
        }
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if (!is_array($lesson_row) || empty($lesson_row['ID'])) {
            wp_send_json_error(array('message' => __('Lesson not found.', 'fc-helper')));
        }
        $resolved_video_url = $this->resolve_alm_lesson_sample_video_url($lesson_row, $chapters_t);
        $transcripts_use    = $this->alm_table_exists($transcripts_t) ? $transcripts_t : null;
        $chapters_out       = $this->build_chapters_payload_for_lesson_id($lesson_id, $chapters_t, $transcripts_use);
        $coll_id   = isset($lesson_row['collection_id']) ? (int) $lesson_row['collection_id'] : 0;
        $coll_name = isset($lesson_row['collection_title']) ? sanitize_text_field((string) $lesson_row['collection_title']) : '';
        $post_id   = isset($lesson_row['post_id']) ? (int) $lesson_row['post_id'] : 0;
        $view_raw  = ($post_id > 0) ? get_permalink($post_id) : '';
        wp_send_json_success(
            array(
                'lesson'   => array(
                    'id'                 => (int) $lesson_row['ID'],
                    'title'              => sanitize_text_field((string) ($lesson_row['lesson_title'] ?? '')),
                    'description'        => isset($lesson_row['lesson_description']) ? sanitize_textarea_field((string) $lesson_row['lesson_description']) : '',
                    'resolved_video_url' => $resolved_video_url,
                    'collection_id'      => $coll_id,
                    'collection_title'   => $coll_name,
                    'post_id'            => $post_id,
                    'edit_url'           => admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . (int) $lesson_row['ID']),
                    'view_url'           => $view_raw ? esc_url_raw((string) $view_raw) : '',
                ),
                'chapters' => $chapters_out,
            )
        );
    }

    /**
     * Resolve sample video URL from ALM lesson row (sample_video_url or sample_chapter_id).
     *
     * @param array<string, mixed> $lesson_row Row from alm_lessons.
     * @param string               $chapters_t Full chapters table name.
     * @return string Sanitized URL or empty string.
     */
    private function resolve_alm_lesson_sample_video_url(array $lesson_row, $chapters_t) {
        global $wpdb;
        $resolved = '';
        if (isset($lesson_row['sample_video_url']) && is_string($lesson_row['sample_video_url'])) {
            $resolved = trim($lesson_row['sample_video_url']);
        }
        $sample_chapter_id = isset($lesson_row['sample_chapter_id']) ? (int) $lesson_row['sample_chapter_id'] : 0;
        if ('' !== $resolved || $sample_chapter_id <= 0) {
            return '' === $resolved ? '' : esc_url_raw($resolved);
        }
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $ch_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT bunny_url, vimeo_id, youtube_id FROM {$chapters_t} WHERE ID = %d",
                $sample_chapter_id
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if (!is_array($ch_row)) {
            return '';
        }
        $bunny = isset($ch_row['bunny_url']) ? trim((string) $ch_row['bunny_url']) : '';
        if ('' !== $bunny) {
            return esc_url_raw($bunny);
        }
        $vimeo = isset($ch_row['vimeo_id']) ? (int) $ch_row['vimeo_id'] : 0;
        if ($vimeo > 0) {
            return esc_url_raw('https://vimeo.com/' . $vimeo);
        }
        $youtube = isset($ch_row['youtube_id']) ? trim((string) $ch_row['youtube_id']) : '';
        if ('' !== $youtube) {
            return esc_url_raw('https://www.youtube.com/watch?v=' . rawurlencode($youtube));
        }
        return '';
    }

    /**
     * @return list<array{id:int, primary:bool}>|null
     */
    private function parse_lesson_ids_from_post() {
        $raw = wp_unslash($_POST['lesson_ids'] ?? '');
        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }
        $out = array();
        foreach ($decoded as $item ) {
            if (!is_array($item)) {
                continue;
            }
            $id = isset($item['id']) ? absint($item['id']) : 0;
            if ($id <= 0) {
                continue;
            }
            $out[] = array(
                'id'      => $id,
                'primary' => !empty($item['primary']),
            );
        }
        return $out === array() ? null : $out;
    }

    /**
     * @return list<array{lesson: array<string, mixed>, chapters: array<int, array<string, mixed>>>|null
     */
    private function parse_lesson_data_list_from_request() {
        $raw_json = wp_unslash($_POST['lesson_data_list'] ?? '');
        if (!is_string($raw_json) || trim($raw_json) === '') {
            return null;
        }
        $decoded = json_decode($raw_json, true);
        if (!is_array($decoded)) {
            return null;
        }
        $out = array();
        foreach ($decoded as $item ) {
            if (!is_array($item) || empty($item['lesson']) || !is_array($item['lesson']) || !isset($item['chapters']) || !is_array($item['chapters'])) {
                continue;
            }
            $out[] = $this->sanitize_lesson_payload($item);
        }
        return $out === array() ? null : $out;
    }

    /**
     * @return array{lesson: array<string, mixed>, chapters: array<int, array<string, mixed>>}|null
     */
    private function parse_lesson_data_from_request() {
        $raw_json = wp_unslash($_POST['lesson_data'] ?? '');
        if (!is_string($raw_json) || trim($raw_json) === '') {
            return null;
        }
        $decoded = json_decode($raw_json, true);
        if (!is_array($decoded) || empty($decoded['lesson']) || !is_array($decoded['lesson']) || !isset($decoded['chapters']) || !is_array($decoded['chapters'])) {
            return null;
        }
        return $this->sanitize_lesson_payload($decoded);
    }

    /**
     * @param array<string, mixed> $decoded
     * @return array{lesson: array<string, mixed>, chapters: array<int, array<string, mixed>>}
     */
    private function sanitize_lesson_payload(array $decoded) {
        $lesson = $decoded['lesson'];
        $out_l  = array(
            'id'                 => isset($lesson['id']) ? absint($lesson['id']) : 0,
            'title'              => sanitize_text_field(isset($lesson['title']) ? (string) $lesson['title'] : ''),
            'description'        => isset($lesson['description']) ? sanitize_textarea_field((string) $lesson['description']) : '',
            'resolved_video_url' => isset($lesson['resolved_video_url']) ? esc_url_raw((string) $lesson['resolved_video_url']) : '',
            'collection_id'      => isset($lesson['collection_id']) ? absint($lesson['collection_id']) : 0,
            'collection_title'   => sanitize_text_field(isset($lesson['collection_title']) ? (string) $lesson['collection_title'] : ''),
        );
        $out_c = array();
        foreach ($decoded['chapters'] as $ch ) {
            if (!is_array($ch)) {
                continue;
            }
            $trans = $ch['transcript'] ?? null;
            if (is_string($trans)) {
                $trans = wp_strip_all_tags($trans);
                if (strlen($trans) > 80000) {
                    $trans = substr($trans, 0, 80000);
                }
                if ($trans === '') {
                    $trans = null;
                }
            } else {
                $trans = null;
            }
            $out_c[] = array(
                'id'               => isset($ch['id']) ? absint($ch['id']) : 0,
                'title'            => sanitize_text_field(isset($ch['title']) ? (string) $ch['title'] : ''),
                'menu_order'       => isset($ch['menu_order']) ? (int) $ch['menu_order'] : 0,
                'duration_seconds' => isset($ch['duration_seconds']) ? max(0, (int) $ch['duration_seconds']) : 0,
                'start_time'       => sanitize_text_field(isset($ch['start_time']) ? (string) $ch['start_time'] : '00:00:00'),
                'transcript'       => $trans,
            );
        }
        return array(
            'lesson'   => $out_l,
            'chapters' => $out_c,
        );
    }

    public function ajax_save_product() {
        $this->verify_ajax_basic();
        $product_id = sanitize_text_field(wp_unslash($_POST['product_id'] ?? ''));
        $name       = sanitize_text_field(wp_unslash($_POST['product_name'] ?? ''));
        $data       = $this->sanitize_form_payload();
        if (trim((string) ($data['product_title'] ?? '')) === '') {
            wp_send_json_error(array('message' => __('Please enter a product name.', 'fc-helper')));
        }
        if (current_user_can('unfiltered_html')) {
            $data['html_output'] = wp_unslash($_POST['html_output'] ?? '');
        } else {
            $data['html_output'] = wp_kses_post(wp_unslash($_POST['html_output'] ?? ''));
        }
        $products   = $this->get_saved_products();

        if ('' !== $product_id) {
            $updated = false;
            foreach ($products as $i => $p ) {
                if ($p['id'] === $product_id) {
                    $products[ $i ]['data'] = $data;
                    $updated                = true;
                    break;
                }
            }
            if (!$updated) {
                wp_send_json_error(array('message' => __('Product not found.', 'fc-helper')));
            }
            update_option(self::OPTION_SAVED_PRODUCTS, $products, false);
            wp_send_json_success(
                array(
                    'products' => $this->get_saved_products_for_js(),
                )
            );
            return;
        }

        if ('' === trim($name)) {
            wp_send_json_error(array('message' => __('Please enter a product name.', 'fc-helper')));
        }
        $updated = false;
        foreach ($products as $i => $p ) {
            if ($p['name'] === $name) {
                $products[ $i ]['data'] = $data;
                $updated                = true;
                break;
            }
        }
        if (!$updated) {
            $products[] = array(
                'id'      => $this->new_saved_product_id(),
                'name'    => $name,
                'created' => time(),
                'data'    => $data,
            );
        }
        update_option(self::OPTION_SAVED_PRODUCTS, $products, false);
        wp_send_json_success(
            array(
                'products' => $this->get_saved_products_for_js(),
            )
        );
    }

    public function ajax_load_products() {
        $this->verify_ajax_basic();
        wp_send_json_success(array('products' => $this->get_saved_products_for_js()));
    }

    public function ajax_delete_product() {
        $this->verify_ajax_basic();
        $id       = sanitize_text_field(wp_unslash($_POST['product_id'] ?? ''));
        $products = $this->get_saved_products();
        $before   = count($products);
        $products = array_values(
            array_filter(
                $products,
                function ( $p ) use ( $id ) {
                    return $p['id'] !== $id;
                }
            )
        );
        if (count($products) === $before) {
            wp_send_json_error(array('message' => __('Product not found.', 'fc-helper')));
        }
        update_option(self::OPTION_SAVED_PRODUCTS, $products, false);
        wp_send_json_success(
            array(
                'products' => $this->get_saved_products_for_js(),
            )
        );
    }

    /**
     * Accessible label color (#1c1917 vs #ffffff) for text on a solid hex background.
     *
     * @param string $bg_hex Background color.
     * @return string
     */
    private function contrast_label_for_bg($bg_hex) {
        $hex = ltrim($bg_hex, '#');
        if (strlen($hex) !== 6) {
            return '#ffffff';
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $l = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
        return $l > 0.55 ? '#1c1917' : '#ffffff';
    }

    /**
     * Comma-separated R,G,B for use in rgba(var(--fc-accent-rgb), a).
     *
     * @param string $hex #RRGGBB or RRGGBB.
     * @return string e.g. "201,168,70"
     */
    private function hex_to_rgb_csv($hex) {
        $hex = ltrim((string) $hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return '201,168,70';
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return sprintf('%d,%d,%d', $r, $g, $b);
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $themes       = FC_Helper_AI_Generator::get_themes();
        $default_slug = 'dark_gold';
        if (!isset($themes[ $default_slug ])) {
            $kf = array_key_first($themes);
            $default_slug = $kf ? $kf : 'dark_gold';
        }
        $d = isset($themes[ $default_slug ]) ? $themes[ $default_slug ] : FC_Helper_AI_Generator::get_default_themes()['dark_gold'];
        ?>
        <div class="wrap fc-helper-wrap">
            <h1 class="screen-reader-text"><?php echo esc_html__('FC Helper', 'fc-helper'); ?></h1>

            <?php if (!fc_helper_is_hub_active()) : ?>
                <div class="notice notice-error"><p><?php echo esc_html__('FC Helper requires the Katahdin AI Hub plugin to be active.', 'fc-helper'); ?></p></div>
            <?php else : ?>
            <style id="fc-helper-theme-vars">
                :root {
                    --fc-primary: <?php echo esc_attr($d['primary']); ?>;
                    --fc-accent: <?php echo esc_attr($d['accent']); ?>;
                    --fc-accent-rgb: <?php echo esc_attr($this->hex_to_rgb_csv($d['accent'])); ?>;
                    --fc-generate-label: <?php echo esc_attr($this->contrast_label_for_bg($d['accent'])); ?>;
                    --fc-on-primary: <?php echo esc_attr($this->contrast_label_for_bg($d['primary'])); ?>;
                }
            </style>

            <div class="fc-helper-app">
                <aside class="fc-helper-panel fc-helper-panel--form">
                    <form id="fc-helper-form" class="fc-helper-form" autocomplete="off" onsubmit="return false;">
                        <input type="hidden" name="theme" id="fc_theme_input" value="<?php echo esc_attr($default_slug); ?>" />

                        <header class="fc-helper-form__header">
                            <div class="fc-helper-form__header-row">
                                <div class="fc-helper-form__header-text">
                                    <span class="fc-helper-form__title"><?php echo esc_html__('FC Helper', 'fc-helper'); ?></span>
                                </div>
                                <div class="fc-helper-form__header-actions">
                                    <button type="button" class="fc-helper-btn fc-helper-btn--primary fc-helper-btn--sm" id="fc-new-session">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18" aria-hidden="true" style="vertical-align:middle; margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        <?php echo esc_html__('New', 'fc-helper'); ?>
                                    </button>
                                    <a href="#" target="_blank" rel="noopener" class="fc-helper-btn fc-helper-btn--primary fc-helper-btn--sm" id="fc-edit-product-link" hidden>
                                        <?php echo esc_html__('Edit Product ↗', 'fc-helper'); ?>
                                    </a>
                                    <button type="button" class="fc-helper-btn fc-helper-btn--primary fc-helper-btn--sm" id="fc-helper-save-product"><?php echo esc_html__('Save Product', 'fc-helper'); ?></button>
                                </div>
                            </div>
                        </header>

                        <div class="fc-helper-saved-toolbar" role="region" aria-label="<?php echo esc_attr__('Saved products', 'fc-helper'); ?>">
                            <label class="fc-helper-saved-toolbar__label" for="fc-helper-saved-select"><?php echo esc_html__('Load Product', 'fc-helper'); ?></label>
                            <div class="fc-helper-saved-toolbar__row">
                                <select id="fc-helper-saved-select" class="fc-helper-saved-toolbar__select" autocomplete="off">
                                    <option value=""><?php echo esc_html__('— Select a saved product —', 'fc-helper'); ?></option>
                                </select>
                                <button type="button" class="fc-helper-btn fc-helper-btn--secondary fc-helper-btn--sm" id="fc-helper-saved-delete" disabled><?php echo esc_html__('Delete', 'fc-helper'); ?></button>
                            </div>
                        </div>

                        <div class="fc-helper-form__sections">
                            <section class="fc-helper-section fc-helper-section--lesson-data">
                                <h2 class="fc-helper-section__title"><?php echo fc_helper_icon('magnifying-glass'); ?> <span><?php echo esc_html__('Lesson Data', 'fc-helper'); ?></span></h2>
                                <div class="fc-helper-field fc-helper-field--product-name">
                                    <label for="fc_product_title"><?php echo esc_html__('Product Name', 'fc-helper'); ?></label>
                                    <input name="product_title" id="fc_product_title" type="text" class="fc-helper-input--product-name" placeholder="<?php echo esc_attr__( 'e.g. "Blues Fundamentals Bundle" or leave blank to use lesson title', 'fc-helper' ); ?>" autocomplete="off" />
                                </div>
                                <div class="fc-helper-field fc-helper-field--lesson-search">
                                    <label for="fc-lesson-search"><?php echo esc_html__('Search Lesson', 'fc-helper'); ?></label>
                                    <div class="fc-helper-lesson-search-wrap" id="fc-lesson-search-wrap">
                                        <input type="text" id="fc-lesson-search" autocomplete="off" placeholder="<?php echo esc_attr__('Type lesson name…', 'fc-helper'); ?>" />
                                        <ul id="fc-lesson-results" class="fc-helper-lesson-results" role="listbox" hidden></ul>
                                    </div>
                                </div>
                                <div id="fc-lesson-selected" class="fc-helper-lesson-selected" hidden>
                                    <p id="fc-lesson-selected-empty" class="fc-lesson-selected-empty"> <?php echo esc_html__('No lessons selected yet — search below to add one.', 'fc-helper'); ?></p>
                                    <ul id="fc-selected-lessons" class="fc-selected-lessons" aria-label="<?php echo esc_attr__('Selected lessons', 'fc-helper'); ?>"></ul>
                                    <div class="fc-lesson-selected__footer">
                                        <button type="button" class="fc-helper-btn fc-helper-btn--outline fc-helper-btn--sm" id="fc-add-lesson-btn"><?php echo esc_html__( '+ Add another lesson', 'fc-helper'); ?></button>
                                        <span id="fc-lesson-count" class="fc-lesson-selected__count" aria-live="polite"></span>
                                        <span id="fc-lesson-max-msg" class="fc-lesson-selected__max-msg" hidden><?php echo esc_html__('Maximum 10 lessons.', 'fc-helper'); ?></span>
                                    </div>
                                    <div id="fc-lesson-selected-chapters" class="fc-helper-lesson-selected__chapters" aria-live="polite"></div>
                                </div>
                                <input type="hidden" name="lesson_id" id="fc-lesson-id" value="" />
                                <input type="hidden" name="lesson_ids" id="fc-lesson-ids-json" value="" />
                                <textarea id="fc-lesson-data" name="lesson_data" class="fc-helper-lesson-data-hidden" hidden></textarea>
                                <textarea id="fc-lesson-data-list" name="lesson_data_list" class="fc-helper-lesson-data-hidden" hidden></textarea>
                            </section>

                            <section class="fc-helper-section">
                                <h2 class="fc-helper-section__title"><?php echo fc_helper_icon('tag'); ?> <span><?php echo esc_html__('Product', 'fc-helper'); ?></span></h2>
                                <div class="fc-helper-field">
                                    <label for="fc_description"><?php echo esc_html__('Description', 'fc-helper'); ?></label>
                                    <textarea name="description" id="fc_description" rows="4"></textarea>
                                </div>
                            </section>

                            <section class="fc-helper-section">
                                <h2 class="fc-helper-section__title"><?php echo fc_helper_icon('play-circle'); ?> <span><?php echo esc_html__('Sample Video', 'fc-helper'); ?></span></h2>
                                <div class="fc-helper-field">
                                    <label for="fc_sample_video_url"><?php echo esc_html__('Sample Video URL', 'fc-helper'); ?></label>
                                    <input name="sample_video_url" id="fc_sample_video_url" type="text" placeholder="https://..." />
                                </div>
                                <div class="fc-helper-field fc-helper-field--media">
                                    <span class="fc-helper-field__label-text" id="fc-helper-splash-label"><?php echo esc_html__('Sample Video Splash Image', 'fc-helper'); ?></span>
                                    <input type="hidden" name="sample_video_splash_url" id="fc_sample_video_splash_url" value="<?php echo esc_attr(self::DEFAULT_SAMPLE_SPLASH_URL); ?>" />
                                    <div class="fc-helper-media-picker" id="fc-helper-splash-picker">
                                        <div class="fc-helper-media-picker__preview" id="fc-helper-splash-preview">
                                            <img src="<?php echo esc_url(self::DEFAULT_SAMPLE_SPLASH_URL); ?>" alt="" id="fc-helper-splash-preview-img" width="160" height="90" loading="lazy" decoding="async" />
                                            <div class="fc-helper-media-picker__actions">
                                                <button type="button" class="fc-helper-btn fc-helper-btn--secondary fc-helper-btn--sm" id="fc-helper-splash-remove"><?php echo esc_html__('Remove', 'fc-helper'); ?></button>
                                            </div>
                                        </div>
                                        <button type="button" class="fc-helper-btn fc-helper-btn--outline fc-helper-btn--sm" id="fc-helper-splash-select" hidden><?php echo esc_html__('Choose image', 'fc-helper'); ?></button>
                                    </div>
                                </div>
                            </section>

                            <section class="fc-helper-section">
                                <h2 class="fc-helper-section__title"><?php echo fc_helper_icon('pencil-square'); ?> <span><?php echo esc_html__('Notes', 'fc-helper'); ?></span></h2>
                                <div class="fc-helper-field">
                                    <label for="fc_additional_notes"><?php echo esc_html__('Additional Notes / AI Instructions', 'fc-helper'); ?></label>
                                    <textarea name="additional_notes" id="fc_additional_notes" rows="4"></textarea>
                                </div>
                            </section>

                            <section class="fc-helper-section">
                                <h2 class="fc-helper-section__title"><?php echo fc_helper_icon('swatch'); ?> <span><?php echo esc_html__('Style Theme', 'fc-helper'); ?></span></h2>
                                <div class="fc-helper-swatches" role="list">
                                    <?php
                                    $i = 0;
                                    foreach ($themes as $slug => $info) :
                                        $selected = (0 === $i);
                                        ?>
                                        <button type="button" class="fc-helper-swatch<?php echo $selected ? ' is-selected' : ''; ?>"
                                            role="listitem"
                                            style="--swatch-ring: <?php echo esc_attr($info['primary']); ?>;"
                                            data-theme="<?php echo esc_attr($slug); ?>"
                                            data-primary="<?php echo esc_attr($info['primary']); ?>"
                                            data-accent="<?php echo esc_attr($info['accent']); ?>"
                                            data-btn-on-accent="<?php echo esc_attr($this->contrast_label_for_bg($info['accent'])); ?>"
                                            data-on-primary="<?php echo esc_attr($this->contrast_label_for_bg($info['primary'])); ?>"
                                            aria-pressed="<?php echo $selected ? 'true' : 'false'; ?>"
                                            aria-label="<?php echo esc_attr($info['label']); ?>">
                                            <span class="fc-helper-swatch__colors" aria-hidden="true">
                                                <span class="fc-helper-swatch__chip" style="background-color: <?php echo esc_attr($info['primary']); ?>"></span>
                                                <span class="fc-helper-swatch__chip" style="background-color: <?php echo esc_attr($info['accent']); ?>"></span>
                                            </span>
                                            <span class="fc-helper-swatch__label"><?php echo esc_html($info['label']); ?></span>
                                        </button>
                                        <?php
                                        ++$i;
                                    endforeach;
                                    ?>
                                </div>
                                <p class="fc-helper-theme-manage-wrap">
                                    <button type="button" class="fc-helper-btn fc-helper-btn--outline fc-helper-btn--sm" id="fc-theme-manager-btn"><?php echo esc_html__('Manage Themes', 'fc-helper'); ?></button>
                                </p>
                            </section>
                        </div>

                        <button type="button" class="fc-helper-generate" id="fc-helper-generate">
                            <span class="fc-helper-generate__icon" aria-hidden="true"><?php echo fc_helper_icon('sparkles'); ?></span>
                            <span class="fc-helper-generate__spin" hidden aria-hidden="true">
                                <svg class="fc-helper-ring" width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10" fill="none" stroke-width="3" stroke="currentColor" stroke-dasharray="48" stroke-dashoffset="12" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="fc-helper-generate__label"><?php echo esc_html__('Generate HTML', 'fc-helper'); ?></span>
                        </button>

                        <div class="fc-helper-revision" id="fc-helper-revision" hidden>
                            <div class="fc-helper-field">
                                <label for="fc_revision_request" class="screen-reader-text"><?php echo esc_html__('Request a revision', 'fc-helper'); ?></label>
                                <textarea id="fc_revision_request" rows="3" placeholder="<?php echo esc_attr__('Request a revision…', 'fc-helper'); ?>"></textarea>
                            </div>
                            <div class="fc-helper-revision__actions">
                                <button type="button" class="fc-helper-btn fc-helper-btn--secondary" id="fc-helper-revise">
                                    <span class="fc-helper-btn__icon" aria-hidden="true"><?php echo fc_helper_icon('pencil-square', array('width' => '20', 'height' => '20')); ?></span>
                                    <span class="fc-helper-btn__text"><?php echo esc_html__('Revise', 'fc-helper'); ?></span>
                                </button>
                                <button type="button" class="fc-helper-btn fc-helper-btn--primary" id="fc-helper-copy-inline">
                                    <span class="fc-helper-btn__icon fc-helper-btn__icon--copy" aria-hidden="true"><?php echo fc_helper_icon('document-duplicate', array('width' => '20', 'height' => '20')); ?></span>
                                    <span class="fc-helper-btn__icon fc-helper-btn__icon--check" aria-hidden="true" hidden><?php echo fc_helper_icon('check'); ?></span>
                                    <span class="fc-helper-btn__text"><?php echo esc_html__('Copy HTML', 'fc-helper'); ?></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </aside>

                <div class="fc-helper-panel fc-helper-panel--output">
                    <div class="fc-helper-output-toolbar">
                        <span class="fc-helper-output-toolbar__title"><?php echo esc_html__('Output', 'fc-helper'); ?></span>
                        <span class="fc-helper-output-toolbar__count" id="fc-helper-count">0 <?php echo esc_html__('characters', 'fc-helper'); ?></span>
                        <button type="button" class="fc-helper-btn fc-helper-btn--accent fc-helper-btn--sm" id="fc-helper-update-product" hidden><?php echo esc_html__('Update Product', 'fc-helper'); ?></button>
                        <button type="button" class="fc-helper-btn fc-helper-btn--outline fc-helper-btn--sm" id="fc-helper-copy-top">
                            <span class="fc-helper-btn__icon" aria-hidden="true"><?php echo fc_helper_icon('clipboard', array('width' => '20', 'height' => '20')); ?></span>
                            <span class="fc-helper-btn__text"><?php echo esc_html__('Copy to Clipboard', 'fc-helper'); ?></span>
                        </button>
                    </div>

                    <div class="fc-helper-output-main" id="fc-helper-output-main">
                        <div class="fc-helper-output-loading" id="fc-helper-spinner" hidden>
                            <div class="fc-helper-output-loading__inner">
                                <div class="fc-helper-output-loading__ring" aria-hidden="true"></div>
                                <p class="fc-helper-output-loading__text"><?php echo esc_html__('Generating…', 'fc-helper'); ?></p>
                            </div>
                        </div>

                        <div class="fc-helper-output-empty" id="fc-helper-empty">
                            <span class="fc-helper-output-empty__icon" aria-hidden="true"><?php echo fc_helper_icon('document-text', array('width' => '48', 'height' => '48', 'class' => 'fc-helper-svg-icon fc-helper-svg-icon--lg')); ?></span>
                            <p class="fc-helper-output-empty__title"><?php echo esc_html__('Fill in the form and click Generate HTML', 'fc-helper'); ?></p>
                            <p class="fc-helper-output-empty__sub"><?php echo esc_html__('Your product description will appear here, ready to copy into FluentCart.', 'fc-helper'); ?></p>
                        </div>

                        <div class="fc-helper-output-tabs" id="fc-helper-tabs" role="tablist" hidden>
                            <button type="button" class="fc-helper-tab is-active" id="fc-tab-output" role="tab" aria-selected="true" aria-controls="fc-panel-output" data-tab="output">
                                <?php echo esc_html__('Output HTML', 'fc-helper'); ?> <span class="fc-helper-tab__meta" id="fc-tab-count" aria-live="polite"></span>
                            </button>
                            <button type="button" class="fc-helper-tab" id="fc-tab-prompt" role="tab" aria-selected="false" aria-controls="fc-panel-prompt" data-tab="prompt">
                                <?php echo esc_html__('Prompt Sent', 'fc-helper'); ?>
                            </button>
                            <button type="button" class="fc-helper-tab" id="fc-tab-debug" role="tab" aria-selected="false" aria-controls="fc-panel-debug" data-tab="debug">
                                <?php echo esc_html__('Debug Log', 'fc-helper'); ?>
                            </button>
                        </div>

                        <div class="fc-helper-tab-panels" id="fc-helper-tab-panels" hidden>
                            <div class="fc-helper-tab-panel is-active" id="fc-panel-output" role="tabpanel" aria-labelledby="fc-tab-output" data-panel="output">
                                <label for="fc_helper_output" class="screen-reader-text"><?php echo esc_html__('Generated HTML', 'fc-helper'); ?></label>
                                <textarea id="fc_helper_output" class="fc-helper-code-area" name="fc_helper_output" spellcheck="false" placeholder="<?php echo esc_attr__('Your generated HTML will appear here...', 'fc-helper'); ?>"></textarea>
                            </div>
                            <div class="fc-helper-tab-panel" id="fc-panel-prompt" role="tabpanel" aria-labelledby="fc-tab-prompt" data-panel="prompt">
                                <p class="fc-helper-prompt-label"><?php echo esc_html__('This is the exact prompt sent to the AI.', 'fc-helper'); ?></p>
                                <label for="fc_helper_prompt_json" class="screen-reader-text"><?php echo esc_html__('Prompt JSON', 'fc-helper'); ?></label>
                                <textarea id="fc_helper_prompt_json" class="fc-helper-code-area fc-helper-code-area--tall" readonly spellcheck="false" placeholder=""></textarea>
                            </div>
                            <div class="fc-helper-tab-panel" id="fc-panel-debug" role="tabpanel" aria-labelledby="fc-tab-debug" data-panel="debug">
                                <div class="fc-helper-debug-head">
                                    <button type="button" class="fc-helper-btn fc-helper-btn--outline fc-helper-btn--sm" id="fc-helper-clear-debug"><?php echo esc_html__('Clear Debug Log', 'fc-helper'); ?></button>
                                </div>
                                <label for="fc_helper_debug" class="screen-reader-text"><?php echo esc_html__('Debug log', 'fc-helper'); ?></label>
                                <textarea id="fc_helper_debug" class="fc-helper-code-area fc-helper-code-area--tall" readonly spellcheck="false" placeholder=""></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $fc_theme_color_fields = array(
                'primary'         => __('Primary Background', 'fc-helper'),
                'bg_mid'          => __('Mid Background', 'fc-helper'),
                'bg_gradient_end' => __('Gradient End', 'fc-helper'),
                'accent'          => __('Accent Color', 'fc-helper'),
                'gold'            => __('Accent (Fill)', 'fc-helper'),
                'gold_dark'       => __('Accent Dark', 'fc-helper'),
                'text_light'      => __('Text Light', 'fc-helper'),
                'text_muted'      => __('Text Muted', 'fc-helper'),
                'bg_cream'        => __('Cream Background', 'fc-helper'),
                'bg_warm'         => __('Warm Background', 'fc-helper'),
                'border_warm'     => __('Warm Border', 'fc-helper'),
                'text_on_light'   => __('Text on Light Backgrounds', 'fc-helper'),
                'bg_dark'         => __('Background Dark', 'fc-helper'),
            );
            ?>
            <div id="fc-theme-manager-modal" class="fc-theme-manager-overlay" hidden aria-hidden="true">
                <div class="fc-theme-manager-modal" role="dialog" aria-modal="true" aria-labelledby="fc-theme-manager-title" tabindex="-1">
                    <div class="fc-theme-manager-modal__header">
                        <h2 id="fc-theme-manager-title" class="fc-theme-manager-modal__title"><?php echo esc_html__('Theme Manager', 'fc-helper'); ?></h2>
                        <div class="fc-theme-manager-modal__header-actions">
                            <button type="button" class="fc-helper-btn fc-helper-btn--secondary fc-helper-btn--sm" id="fc-theme-reset-btn"><?php echo esc_html__('Reset to Defaults', 'fc-helper'); ?></button>
                            <button type="button" class="fc-helper-btn fc-helper-btn--outline fc-helper-btn--sm" id="fc-theme-manager-close"><?php echo esc_html__('× Close', 'fc-helper'); ?></button>
                        </div>
                    </div>
                    <div class="fc-theme-manager-modal__body">
                        <div class="fc-theme-list-wrap">
                            <ul id="fc-theme-list" class="fc-theme-list" role="list"></ul>
                            <button type="button" class="fc-helper-btn fc-helper-btn--outline fc-helper-btn--sm fc-theme-list__new" id="fc-theme-new-btn"><?php echo esc_html__( '＋ New Theme', 'fc-helper' ); ?></button>
                        </div>
                        <div id="fc-theme-editor" class="fc-theme-editor" hidden>
                            <div id="fc-theme-preview" class="fc-theme-preview" aria-hidden="true">
                                <div class="fc-theme-preview__inner">
                                    <span class="fc-theme-preview__badge"><?php echo esc_html__('New', 'fc-helper'); ?></span>
                                    <h1 class="fc-theme-preview__title"><?php echo esc_html__('Preview', 'fc-helper'); ?></h1>
                                </div>
                            </div>
                            <div class="fc-helper-field">
                                <label for="fc-theme-name"><?php echo esc_html__('Theme Name', 'fc-helper'); ?></label>
                                <input type="text" id="fc-theme-name" autocomplete="off" />
                            </div>
                            <div class="fc-helper-field">
                                <label for="fc-theme-slug"><?php echo esc_html__('Slug', 'fc-helper'); ?></label>
                                <input type="text" id="fc-theme-slug" class="fc-theme-slug-input" autocomplete="off" />
                            </div>
                            <div class="fc-theme-generate-row">
                                <label class="fc-theme-generate-row__label" for="fc-theme-master-color"><?php echo esc_html__('Generate from Color', 'fc-helper'); ?></label>
                                <div class="fc-theme-generate-row__controls">
                                    <input type="color" id="fc-theme-master-color" value="#1c1917" />
                                    <span class="fc-theme-color-hex fc-theme-master-hex" aria-hidden="true">#1c1917</span>
                                    <button type="button" class="fc-helper-btn fc-helper-btn--outline fc-helper-btn--sm" id="fc-theme-generate-btn"><?php echo esc_html__( "\u{2726} Generate Colors", 'fc-helper' ); ?></button>
                                </div>
                                <p class="fc-theme-generate-row__hint"><?php echo esc_html__('Pick one color, AI will suggest the full palette.', 'fc-helper'); ?></p>
                                <p class="fc-theme-generate-row__error" id="fc-theme-generate-error" hidden role="alert"></p>
                            </div>
                            <div class="fc-theme-color-grid">
                                <?php foreach ($fc_theme_color_fields as $ckey => $clabel ) : ?>
                                    <div class="fc-theme-color-row" data-color-key="<?php echo esc_attr($ckey); ?>">
                                        <label class="fc-theme-color-row__label" for="fc-theme-c-<?php echo esc_attr($ckey); ?>"><?php echo esc_html($clabel); ?></label>
                                        <input type="color" id="fc-theme-c-<?php echo esc_attr($ckey); ?>" class="fc-theme-color-input" data-color-key="<?php echo esc_attr($ckey); ?>" value="#000000" />
                                        <input type="text" class="fc-theme-color-hex" readonly value="#000000" tabindex="-1" aria-hidden="true" />
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="fc-theme-editor__actions">
                                <button type="button" class="fc-helper-btn fc-helper-btn--primary" id="fc-theme-save-btn"><?php echo esc_html__('Save Theme', 'fc-helper'); ?></button>
                                <button type="button" class="fc-helper-btn fc-btn--danger" id="fc-theme-delete-btn"><?php echo esc_html__('Delete Theme', 'fc-helper'); ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="fc-theme-manager-modal__footer">
                        <button type="button" class="fc-helper-btn fc-helper-btn--primary" id="fc-theme-done-btn"><?php echo esc_html__('Done', 'fc-helper'); ?></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }


    private function verify_ajax() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'fc-helper')), 403);
        }
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        if (!fc_helper_is_hub_active()) {
            wp_send_json_error(array('message' => __('Katahdin AI Hub is not active.', 'fc-helper')));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function sanitize_form_payload() {
        $lesson_data       = $this->parse_lesson_data_from_request();
        $lesson_data_list  = $this->parse_lesson_data_list_from_request();
        $lesson_ids_rows   = $this->parse_lesson_ids_from_post();
        $lesson_id_post    = isset($_POST['lesson_id']) ? absint(wp_unslash($_POST['lesson_id'])) : 0;

        if ((null === $lesson_ids_rows || array() === $lesson_ids_rows) && $lesson_id_post > 0) {
            $lesson_ids_rows = array(
                array(
                    'id'      => $lesson_id_post,
                    'primary' => true,
                ),
            );
        }
        if ((null === $lesson_ids_rows || array() === $lesson_ids_rows) && null !== $lesson_data && isset($lesson_data['lesson']['id'])) {
            $lid = absint($lesson_data['lesson']['id']);
            if ($lid > 0) {
                $lesson_ids_rows = array(
                    array(
                        'id'      => $lid,
                        'primary' => true,
                    ),
                );
            }
        }

        $lesson_ids_rows = null === $lesson_ids_rows ? array() : $this->normalize_lesson_ids_rows($lesson_ids_rows);

        if (array() === $lesson_ids_rows) {
            $lesson_id = 0;
        } else {
            $lesson_id = $this->primary_lesson_id_from_rows($lesson_ids_rows);
        }

        if (array() !== $lesson_ids_rows && is_array($lesson_data_list) && count($lesson_data_list) > 0) {
            $lesson_data_list = $this->realign_lesson_data_list($lesson_ids_rows, $lesson_data_list);
        } elseif (array() !== $lesson_ids_rows && 1 === count($lesson_ids_rows) && null !== $lesson_data) {
            $lesson_data_list = array( $lesson_data );
        }

        if (array() !== $lesson_ids_rows && is_array($lesson_data_list)) {
            foreach ($lesson_data_list as $pl ) {
                if (!is_array($pl) || empty($pl['lesson'])) {
                    continue;
                }
                $plid = isset($pl['lesson']['id']) ? absint($pl['lesson']['id']) : 0;
                if ($plid === $lesson_id) {
                    $lesson_data = $pl;
                    break;
                }
            }
        }

        return array(
            'product_title'           => sanitize_text_field(wp_unslash($_POST['product_title'] ?? '')),
            'description'             => sanitize_textarea_field(wp_unslash($_POST['description'] ?? '')),
            'sample_video_url'        => esc_url_raw(wp_unslash($_POST['sample_video_url'] ?? '')),
            'sample_video_splash_url' => esc_url_raw(wp_unslash($_POST['sample_video_splash_url'] ?? '')),
            'additional_notes'        => sanitize_textarea_field(wp_unslash($_POST['additional_notes'] ?? '')),
            'theme'                   => $this->sanitize_theme(wp_unslash($_POST['theme'] ?? 'dark_gold')),
            'lesson_id'               => $lesson_id,
            'lesson_data'             => $lesson_data,
            'lesson_ids'              => $lesson_ids_rows,
            'lesson_data_list'        => $lesson_data_list,
        );
    }

    /**
     * @param string $theme
     * @return string
     */
    private function sanitize_theme($theme) {
        $theme  = sanitize_key($theme);
        $themes = FC_Helper_AI_Generator::get_themes();
        if (isset($themes[ $theme ])) {
            return $theme;
        }
        if (isset($themes['dark_gold'])) {
            return 'dark_gold';
        }
        $first = array_key_first($themes);
        return $first ? $first : 'dark_gold';
    }

    public function ajax_generate() {
        $this->verify_ajax();
        $payload = $this->sanitize_form_payload();
        if (trim((string) ($payload['product_title'] ?? '')) === '') {
            wp_send_json_error(array('message' => __('Please enter a product name.', 'fc-helper')));
        }

        $result = FC_Helper_AI_Generator::generate($payload);
        if (is_wp_error($result)) {
            $err = $result->get_error_data();
            if (!is_array($err)) {
                $err = array();
            }
            wp_send_json_error(
                array(
                    'message'       => $result->get_error_message(),
                    'prompt_sent'   => $err['prompt_sent'] ?? array(),
                    'response_time' => $err['response_time'] ?? null,
                    'http_status'   => $err['http_status'] ?? null,
                    'model'         => $err['model'] ?? FC_Helper_AI_Generator::MODEL,
                    'usage'         => $err['usage'] ?? array(),
                )
            );
        }
        wp_send_json_success(
            array(
                'html'          => $result['html'],
                'prompt_sent'   => $result['messages'],
                'model'         => $result['model'],
                'usage'         => $result['usage'],
                'response_time' => $result['response_time_ms'],
                'http_status'   => $result['http_status'],
            )
        );
    }

    public function ajax_revise() {
        $this->verify_ajax();

        $html             = wp_unslash($_POST['html'] ?? '');
        $revision_request = sanitize_textarea_field(wp_unslash($_POST['revision_request'] ?? ''));
        $theme_key        = $this->sanitize_theme(wp_unslash($_POST['theme'] ?? 'dark_gold'));

        if (!is_string($html) || trim($html) === '') {
            wp_send_json_error(
                array(
                    'message'       => __('No HTML to revise.', 'fc-helper'),
                    'prompt_sent'   => array(),
                    'response_time' => null,
                    'http_status'   => null,
                )
            );
        }

        $theme  = FC_Helper_AI_Generator::get_theme($theme_key);
        $result = FC_Helper_AI_Generator::revise($html, $revision_request, $theme);
        if (is_wp_error($result)) {
            $err = $result->get_error_data();
            if (!is_array($err)) {
                $err = array();
            }
            wp_send_json_error(
                array(
                    'message'       => $result->get_error_message(),
                    'prompt_sent'   => $err['prompt_sent'] ?? array(),
                    'response_time' => $err['response_time'] ?? null,
                    'http_status'   => $err['http_status'] ?? null,
                    'model'         => $err['model'] ?? FC_Helper_AI_Generator::MODEL,
                    'usage'         => $err['usage'] ?? array(),
                )
            );
        }
        wp_send_json_success(
            array(
                'html'          => $result['html'],
                'prompt_sent'   => $result['messages'],
                'model'         => $result['model'],
                'usage'         => $result['usage'],
                'response_time' => $result['response_time_ms'],
                'http_status'   => $result['http_status'],
            )
        );
    }
}
