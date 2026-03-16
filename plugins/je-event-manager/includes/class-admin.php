<?php
/**
 * Admin Class for JE Event Manager
 * Handles bulk copy functionality for je_event post type
 */

if (!defined('ABSPATH')) {
    exit;
}

class JE_Event_Manager_Admin {
    
    /**
     * Option key for teacher payment tracking
     */
    const OPTION_TEACHER_PAYMENTS = 'jeem_teacher_report_payments';

    /**
     * ACF field keys for events
     */
    private $acf_fields = array(
        'je_event_zoom_link' => 'Zoom Link',
        'je_event_start' => 'Event Start',
        'je_event_end' => 'Event End',
        'je_event_resource_repeater' => 'Resource Repeater',
        'je_event_replay_vimeo_id' => 'Replay Vimeo ID',
        'je_event_bunny_url' => 'Bunny URL',
        'je_event_registration' => 'Registration',
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add bulk copy button to posts list page
        add_action('restrict_manage_posts', array($this, 'add_bulk_copy_button'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_menu', array($this, 'add_teacher_classes_submenu'));
        add_action('wp_ajax_jeem_bulk_copy_event', array($this, 'ajax_bulk_copy_event'));
        add_action('wp_ajax_jeem_get_events_list', array($this, 'ajax_get_events_list'));
    }
    
    /**
     * Add Teacher Report submenu under JE Events
     */
    public function add_teacher_classes_submenu() {
        $hook = add_submenu_page(
            'edit.php?post_type=je_event',
            __('Teacher Report', 'je-event-manager'),
            __('Teacher Report', 'je-event-manager'),
            'edit_posts',
            'jeem-teacher-classes-report',
            array($this, 'render_teacher_classes_report')
        );
        if ($hook) {
            add_action('load-' . $hook, array($this, 'maybe_save_teacher_payments'));
        }
    }

    /**
     * Process teacher payment form before any output (so redirect works)
     */
    public function maybe_save_teacher_payments() {
        if (empty($_POST['jeem_save_payments']) || !current_user_can('edit_posts')) {
            return;
        }
        check_admin_referer('jeem_teacher_payments', 'jeem_teacher_payments_nonce');

        $current_month_post = isset($_POST['jeem_month']) ? sanitize_text_field($_POST['jeem_month']) : '';
        $teachers_post = isset($_POST['jeem_teachers']) && is_array($_POST['jeem_teachers']) ? $_POST['jeem_teachers'] : array();
        if (!preg_match('/^\d{4}-\d{2}$/', $current_month_post) || empty($teachers_post)) {
            return;
        }

        $payments = get_option(self::OPTION_TEACHER_PAYMENTS, array());
        if (!is_array($payments)) {
            $payments = array();
        }
        $payments[$current_month_post] = array();
        $teacher_paid = isset($_POST['jeem_paid']) && is_array($_POST['jeem_paid']) ? $_POST['jeem_paid'] : array();
        $teacher_note = isset($_POST['jeem_note']) && is_array($_POST['jeem_note']) ? $_POST['jeem_note'] : array();
        foreach ($teachers_post as $name) {
            $name_safe = sanitize_text_field($name);
            if (empty($name_safe)) {
                continue;
            }
            $payments[$current_month_post][$name_safe] = array(
                'paid' => !empty($teacher_paid[$name_safe]),
                'note' => isset($teacher_note[$name_safe]) ? substr(sanitize_text_field($teacher_note[$name_safe]), 0, 30) : '',
            );
        }
        update_option(self::OPTION_TEACHER_PAYMENTS, $payments);

        $table_year = isset($_POST['jeem_year']) ? max(2000, min(2100, (int) $_POST['jeem_year'])) : (int) date('Y');
        $redirect_url = add_query_arg(array(
            'post_type' => 'je_event',
            'page' => 'jeem-teacher-classes-report',
            'month' => $current_month_post,
            'year' => $table_year,
            'jeem_saved' => '1',
        ), admin_url('edit.php'));
        wp_safe_redirect($redirect_url);
        exit;
    }
    
    /**
     * Parse je_event_start to Unix timestamp
     */
    private function get_event_start_timestamp($event_id) {
        if (function_exists('get_field')) {
            $start_value = get_field('je_event_start', $event_id);
        } else {
            $start_value = get_post_meta($event_id, 'je_event_start', true);
        }
        if (!$start_value) {
            return 0;
        }
        if ($start_value instanceof DateTime) {
            return $start_value->getTimestamp();
        }
        if (is_numeric($start_value)) {
            return intval($start_value);
        }
        if (is_string($start_value)) {
            $ts = strtotime($start_value);
            return $ts ? $ts : 0;
        }
        return 0;
    }
    
    /**
     * Render Teacher Report page for accounting
     */
    public function render_teacher_classes_report() {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'je-event-manager'));
        }

        $current_month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $current_month)) {
            $current_month = date('Y-m');
        }
        $month_start = strtotime($current_month . '-01 00:00:00');
        $month_end = strtotime(date('Y-m-t', $month_start) . ' 23:59:59');
        
        $table_year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
        $table_year = max(2000, min(2100, $table_year));
        
        $args = array(
            'post_type' => 'je_event',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        $events = get_posts($args);
        
        $teacher_counts = array();
        $teacher_counts_year = array();
        $no_teacher_count = 0;
        $no_teacher_events = array();
        
        $year_start = strtotime($table_year . '-01-01 00:00:00');
        $year_end = strtotime($table_year . '-12-31 23:59:59');
        
        foreach ($events as $event) {
            $ts = $this->get_event_start_timestamp($event->ID);
            if ($ts >= $year_start && $ts <= $year_end) {
                $teachers = wp_get_post_terms($event->ID, 'teacher');
                if (!is_wp_error($teachers) && !empty($teachers)) {
                    $month_num = date('n', $ts);
                    foreach ($teachers as $teacher) {
                        $name = $teacher->name;
                        if (!isset($teacher_counts_year[$name])) {
                            $teacher_counts_year[$name] = array_fill(1, 12, 0);
                        }
                        $teacher_counts_year[$name][$month_num]++;
                    }
                }
            }
            if ($ts < $month_start || $ts > $month_end) {
                continue;
            }
            
            $teachers = wp_get_post_terms($event->ID, 'teacher');
            if (is_wp_error($teachers) || empty($teachers)) {
                $no_teacher_count++;
                $no_teacher_events[] = array(
                    'title' => get_the_title($event->ID),
                    'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $ts),
                    'edit_link' => get_edit_post_link($event->ID, 'raw'),
                );
                continue;
            }
            
            foreach ($teachers as $teacher) {
                $name = $teacher->name;
                if (!isset($teacher_counts[$name])) {
                    $teacher_counts[$name] = 0;
                }
                $teacher_counts[$name]++;
            }
        }
        
        ksort($teacher_counts);
        ksort($teacher_counts_year);

        $payments_data = get_option(self::OPTION_TEACHER_PAYMENTS, array());
        $month_payments = (is_array($payments_data) && isset($payments_data[$current_month])) ? $payments_data[$current_month] : array();

        $month_display = date_i18n('F Y', $month_start);
        $month_names = array();
        for ($m = 1; $m <= 12; $m++) {
            $month_names[$m] = date_i18n('M', strtotime($table_year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT) . '-01'));
        }
        if (!empty($_GET['jeem_saved'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Payment status saved.', 'je-event-manager') . '</p></div>';
        }
        ?>
        <div class="wrap jeem-teacher-report">
            <h1><?php echo esc_html(__('Teacher Report', 'je-event-manager')); ?></h1>
            <p class="description"><?php echo esc_html(__('View how many classes each teacher taught in a given month for accounting purposes.', 'je-event-manager')); ?></p>
            
            <form method="get" action="" style="margin: 20px 0;">
                <input type="hidden" name="post_type" value="je_event">
                <input type="hidden" name="page" value="jeem-teacher-classes-report">
                <label for="jeem-year"><?php _e('Year (for table):', 'je-event-manager'); ?></label>
                <select id="jeem-year" name="year" style="margin-right: 16px;">
                    <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 10; $y--) : ?>
                        <option value="<?php echo esc_attr($y); ?>" <?php selected($table_year, $y); ?>><?php echo esc_html($y); ?></option>
                    <?php endfor; ?>
                </select>
                <label for="jeem-month"><?php _e('Month:', 'je-event-manager'); ?></label>
                <input type="month" id="jeem-month" name="month" value="<?php echo esc_attr($current_month); ?>" />
                <input type="submit" class="button button-primary" value="<?php esc_attr_e('Show Report', 'je-event-manager'); ?>">
            </form>
            
            <h2><?php printf(esc_html__('Year %d', 'je-event-manager'), esc_html($table_year)); ?></h2>
            <table class="widefat striped" style="margin-bottom: 24px;">
                <thead>
                    <tr>
                        <th><?php _e('Teacher', 'je-event-manager'); ?></th>
                        <?php for ($m = 1; $m <= 12; $m++) : ?>
                            <th><?php echo esc_html($month_names[$m]); ?></th>
                        <?php endfor; ?>
                        <th><?php _e('Total', 'je-event-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($teacher_counts_year)) : ?>
                        <?php foreach ($teacher_counts_year as $teacher_name => $months) : ?>
                            <?php $total = array_sum($months); ?>
                            <tr>
                                <td><?php echo esc_html($teacher_name); ?></td>
                                <?php for ($m = 1; $m <= 12; $m++) : ?>
                                    <?php
                                    $month_key = $table_year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                                    $is_paid = !empty($payments_data[$month_key][$teacher_name]['paid']);
                                    ?>
                                    <td>
                                        <?php echo intval($months[$m]); ?>
                                        <?php if ($is_paid) : ?>
                                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a; font-size: 16px; width: 16px; height: 16px; vertical-align: middle;" title="<?php esc_attr_e('Paid', 'je-event-manager'); ?>"></span>
                                        <?php endif; ?>
                                    </td>
                                <?php endfor; ?>
                                <td><strong><?php echo intval($total); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="14"><?php _e('No classes with teachers found for this year.', 'je-event-manager'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <h2><?php printf(esc_html__('Report for %s', 'je-event-manager'), esc_html($month_display)); ?></h2>
            
            <?php if (!empty($teacher_counts)) : ?>
            <form method="post" action="">
                <?php wp_nonce_field('jeem_teacher_payments', 'jeem_teacher_payments_nonce'); ?>
                <input type="hidden" name="jeem_save_payments" value="1">
                <input type="hidden" name="jeem_month" value="<?php echo esc_attr($current_month); ?>">
                <input type="hidden" name="jeem_year" value="<?php echo esc_attr($table_year); ?>">
                <?php foreach (array_keys($teacher_counts) as $tn) : ?>
                    <input type="hidden" name="jeem_teachers[]" value="<?php echo esc_attr($tn); ?>">
                <?php endforeach; ?>
                <table class="widefat striped" style="max-width: 700px;">
                    <thead>
                        <tr>
                            <th><?php _e('Teacher', 'je-event-manager'); ?></th>
                            <th><?php _e('Classes', 'je-event-manager'); ?></th>
                            <th><?php _e('Paid', 'je-event-manager'); ?></th>
                            <th><?php _e('Note', 'je-event-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teacher_counts as $teacher_name => $count) : ?>
                            <?php
                            $paid = isset($month_payments[$teacher_name]['paid']) ? (bool) $month_payments[$teacher_name]['paid'] : false;
                            $note = isset($month_payments[$teacher_name]['note']) ? $month_payments[$teacher_name]['note'] : '';
                            ?>
                            <tr>
                                <td><?php echo esc_html($teacher_name); ?></td>
                                <td><?php echo intval($count); ?></td>
                                <td>
                                    <label class="screen-reader-text" for="jeem_paid_<?php echo esc_attr(sanitize_title($teacher_name)); ?>"><?php printf(esc_html__('Mark %s as paid', 'je-event-manager'), esc_html($teacher_name)); ?></label>
                                    <input type="checkbox" id="jeem_paid_<?php echo esc_attr(sanitize_title($teacher_name)); ?>" name="jeem_paid[<?php echo esc_attr($teacher_name); ?>]" value="1" <?php checked($paid); ?>>
                                </td>
                                <td>
                                    <input type="text" name="jeem_note[<?php echo esc_attr($teacher_name); ?>]" value="<?php echo esc_attr($note); ?>" maxlength="30" size="30" placeholder="<?php esc_attr_e('Add note...', 'je-event-manager'); ?>" style="max-width: 100%;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p style="margin-top: 12px;">
                    <input type="submit" class="button button-primary" value="<?php esc_attr_e('Save payment status', 'je-event-manager'); ?>">
                </p>
            </form>
            <?php else : ?>
            <table class="widefat striped" style="max-width: 500px;">
                <thead>
                    <tr>
                        <th><?php _e('Teacher', 'je-event-manager'); ?></th>
                        <th><?php _e('Classes', 'je-event-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2"><?php _e('No classes with teachers found for this month.', 'je-event-manager'); ?></td>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>
            
            <?php if ($no_teacher_count > 0) : ?>
                <h3 style="margin-top: 24px;"><?php _e('Events without teacher assigned', 'je-event-manager'); ?> (<?php echo intval($no_teacher_count); ?>)</h3>
                <p class="description"><?php _e('These events occurred in the selected month but have no teacher taxonomy assigned. You may want to assign teachers for accurate accounting.', 'je-event-manager'); ?></p>
                <ul>
                    <?php foreach ($no_teacher_events as $ev) : ?>
                        <li>
                            <?php if (!empty($ev['edit_link'])) : ?>
                                <a href="<?php echo esc_url($ev['edit_link']); ?>"><?php echo esc_html($ev['title']); ?></a>
                            <?php else : ?>
                                <?php echo esc_html($ev['title']); ?>
                            <?php endif; ?>
                            — <?php echo esc_html($ev['date']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Add bulk copy button above the posts table
     */
    public function add_bulk_copy_button($post_type) {
        if ($post_type !== 'je_event') {
            return;
        }
        ?>
        <div class="jeem-bulk-copy-wrapper" style="margin: 10px 0;">
            <button type="button" class="button button-primary" id="jeem-open-bulk-copy-modal">
                <span class="dashicons dashicons-calendar-alt" style="vertical-align: middle; margin-right: 5px;"></span>
                <?php _e('Bulk Copy Event', 'je-event-manager'); ?>
            </button>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only enqueue on je_event post list page
        if ($hook !== 'edit.php' || !isset($_GET['post_type']) || $_GET['post_type'] !== 'je_event') {
            return;
        }
        
        // Include modals on the posts list page
        add_action('admin_footer', array($this, 'include_modals'));
        
        wp_enqueue_style(
            'je-event-manager-admin',
            JE_EVENT_MANAGER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            JE_EVENT_MANAGER_VERSION
        );
        
        wp_enqueue_script(
            'je-event-manager-admin',
            JE_EVENT_MANAGER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-datepicker'),
            JE_EVENT_MANAGER_VERSION,
            true
        );
        
        // Enqueue jQuery UI datepicker styles
        wp_enqueue_style('jquery-ui-datepicker', 'https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css', array(), '1.13.2');
        
        wp_localize_script('je-event-manager-admin', 'jeEventManager', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('je_event_manager_nonce'),
        ));
    }
    
    /**
     * AJAX: Get events list for dropdown
     */
    public function ajax_get_events_list() {
        check_ajax_referer('je_event_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $args = array(
            'post_type' => 'je_event',
            'post_status' => 'any',
            'posts_per_page' => 100, // Get more to ensure we have 50 with dates
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $events = get_posts($args);
        $events_with_dates = array();
        
        foreach ($events as $event) {
            // Get start date for sorting
            $start_timestamp = 0;
            if (function_exists('get_field')) {
                $start_value = get_field('je_event_start', $event->ID);
            } else {
                $start_value = get_post_meta($event->ID, 'je_event_start', true);
            }
            
            // Convert to timestamp for sorting
            if ($start_value) {
                if ($start_value instanceof DateTime) {
                    $start_timestamp = $start_value->getTimestamp();
                } elseif (is_numeric($start_value)) {
                    $start_timestamp = intval($start_value);
                } elseif (is_string($start_value)) {
                    $ts = strtotime($start_value);
                    if ($ts) {
                        $start_timestamp = $ts;
                    }
                }
            }
            
            // Only include events with valid start dates
            if ($start_timestamp > 0) {
                // Format start date for display
                $start_date = date('Y-m-d H:i', $start_timestamp);
                
                // Get membership level
                $membership_levels = wp_get_post_terms($event->ID, 'membership-level', array('fields' => 'names'));
                $membership_text = '';
                if (!is_wp_error($membership_levels) && !empty($membership_levels)) {
                    $membership_text = ' - ' . implode(', ', $membership_levels);
                }
                
                $display_title = $event->post_title;
                if ($start_date) {
                    $display_title .= ' (' . $start_date . ')';
                }
                if ($membership_text) {
                    $display_title .= $membership_text;
                }
                
                $events_with_dates[] = array(
                    'id' => $event->ID,
                    'title' => $display_title,
                    'post_title' => $event->post_title,
                    'start_timestamp' => $start_timestamp,
                );
            }
        }
        
        // Sort by start date timestamp (newest first)
        usort($events_with_dates, function($a, $b) {
            return $b['start_timestamp'] - $a['start_timestamp'];
        });
        
        // Limit to 50
        $events_list = array_slice($events_with_dates, 0, 50);
        
        // Remove timestamp from final output
        foreach ($events_list as &$event) {
            unset($event['start_timestamp']);
        }
        
        wp_send_json_success($events_list);
    }
    
    /**
     * Include modals in admin footer
     */
    public function include_modals() {
        include JE_EVENT_MANAGER_PLUGIN_DIR . 'templates/modals.php';
    }
    
    /**
     * AJAX: Bulk copy event
     */
    public function ajax_bulk_copy_event() {
        check_ajax_referer('je_event_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $interval = isset($_POST['interval']) ? intval($_POST['interval']) : 0;
        $interval_unit = isset($_POST['interval_unit']) ? sanitize_text_field($_POST['interval_unit']) : 'weeks';
        $count = isset($_POST['count']) ? intval($_POST['count']) : 1;
        $copy_multiple = isset($_POST['copy_multiple']) && $_POST['copy_multiple'] === 'true';
        $source_ids = isset($_POST['source_ids']) ? array_map('intval', explode(',', $_POST['source_ids'])) : array();
        
        // If copying multiple events, copy each selected event 'count' times
        if ($copy_multiple && !empty($source_ids)) {
            $created_ids = array();
            
            // Parse the start date to ensure proper format
            try {
                $date_obj = new DateTime($start_date);
                $base_date = $date_obj->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $base_date = $start_date;
            }
            
            // For each selected event, create 'count' copies
            foreach ($source_ids as $source_id) {
                $current_date = $base_date;
                
                // Create 'count' copies of this event
                for ($i = 0; $i < $count; $i++) {
                    $new_id = $this->copy_event($source_id, $current_date);
                    
                    if (!is_wp_error($new_id)) {
                        $created_ids[] = $new_id;
                    }
                    
                    // Calculate next date for next copy (only if not the last copy)
                    if ($i < $count - 1) {
                        try {
                            $date_obj = new DateTime($current_date);
                            if ($interval_unit === 'weeks') {
                                $date_obj->modify('+' . $interval . ' weeks');
                            } elseif ($interval_unit === 'days') {
                                $date_obj->modify('+' . $interval . ' days');
                            } elseif ($interval_unit === 'months') {
                                $date_obj->modify('+' . $interval . ' months');
                            }
                            $current_date = $date_obj->format('Y-m-d H:i:s');
                        } catch (Exception $e) {
                            break;
                        }
                    }
                }
            }
            
            wp_send_json_success(array(
                'message' => sprintf('%d events created', count($created_ids)),
                'created_ids' => $created_ids
            ));
            return;
        }
        
        // Original single event bulk copy logic
        if (!$source_id || !$start_date || !$interval || !$count) {
            wp_send_json_error(array('message' => 'All fields are required'));
        }
        
        $created_ids = array();
        $current_date = $start_date;
        
        // Parse the start date to ensure proper format
        try {
            $date_obj = new DateTime($start_date);
            $current_date = $date_obj->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $current_date = $start_date;
        }
        
        for ($i = 0; $i < $count; $i++) {
            $new_id = $this->copy_event($source_id, $current_date);
            
            if (!is_wp_error($new_id)) {
                $created_ids[] = $new_id;
            }
            
            // Calculate next date (only if not the last iteration)
            if ($i < $count - 1) {
                try {
                    $date_obj = new DateTime($current_date);
                    if ($interval_unit === 'weeks') {
                        $date_obj->modify('+' . $interval . ' weeks');
                    } elseif ($interval_unit === 'days') {
                        $date_obj->modify('+' . $interval . ' days');
                    } elseif ($interval_unit === 'months') {
                        $date_obj->modify('+' . $interval . ' months');
                    }
                    $current_date = $date_obj->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    break;
                }
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf('%d events created', count($created_ids)),
            'created_ids' => $created_ids
        ));
    }
    
    /**
     * Copy an event
     */
    private function copy_event($source_id, $new_start_date = '') {
        $source_post = get_post($source_id);
        
        if (!$source_post || $source_post->post_type !== 'je_event') {
            return new WP_Error('invalid_event', 'Invalid source event');
        }
        
        // Create new post
        $new_post_data = array(
            'post_title' => $source_post->post_title,
            'post_content' => $source_post->post_content,
            'post_excerpt' => $source_post->post_excerpt,
            'post_type' => 'je_event',
            'post_status' => $source_post->post_status,
        );
        
        $new_id = wp_insert_post($new_post_data);
        
        if (is_wp_error($new_id)) {
            return $new_id;
        }
        
        // Copy all ACF fields
        foreach ($this->acf_fields as $key => $label) {
            // Skip bunny_url and replay_vimeo_id - do not copy these fields
            if ($key === 'je_event_bunny_url' || $key === 'je_event_replay_vimeo_id') {
                continue;
            }
            
            // Check if ACF function exists, otherwise use get_post_meta
            if (function_exists('get_field')) {
                $value = get_field($key, $source_id);
            } else {
                $value = get_post_meta($source_id, $key, true);
            }
            
            if ($key === 'je_event_start') {
                if ($new_start_date) {
                    // Update start date with new date
                    $start_value = $new_start_date;
                } else {
                    // Copy original start date
                    $start_value = $value;
                }
                
                // Try to use ACF update_field, otherwise use update_post_meta
                if (function_exists('update_field')) {
                    update_field($key, $start_value, $new_id);
                } else {
                    update_post_meta($new_id, $key, $start_value);
                }
                
                // Always set end date to 1 hour after start date (whether new or copied)
                try {
                    $timezone = wp_timezone();
                    $start_dt = new DateTime($start_value, $timezone);
                    $new_end_dt = clone $start_dt;
                    $new_end_dt->modify('+1 hour');
                    $new_end_value = $new_end_dt->format('Y-m-d H:i:s');
                    
                    if (function_exists('update_field')) {
                        update_field('je_event_end', $new_end_value, $new_id);
                    } else {
                        update_post_meta($new_id, 'je_event_end', $new_end_value);
                    }
                } catch (Exception $e) {
                    // If date parsing fails, try to set end date anyway
                    try {
                        $start_dt = new DateTime($start_value, wp_timezone());
                        $new_end_dt = clone $start_dt;
                        $new_end_dt->modify('+1 hour');
                        $new_end_value = $new_end_dt->format('Y-m-d H:i:s');
                        
                        if (function_exists('update_field')) {
                            update_field('je_event_end', $new_end_value, $new_id);
                        } else {
                            update_post_meta($new_id, 'je_event_end', $new_end_value);
                        }
                    } catch (Exception $e2) {
                        // Log error but continue
                        error_log('JE Event Manager: Failed to set end date: ' . $e2->getMessage());
                    }
                }
            } elseif ($key === 'je_event_end') {
                // Skip copying je_event_end - it will be set to 1 hour after start date
                // This is handled when we process je_event_start
                continue;
            } elseif ($key === 'je_event_resource_repeater' && $value) {
                // Handle repeater field
                if (function_exists('update_field')) {
                    update_field($key, $value, $new_id);
                } else {
                    // For non-ACF, copy the meta value
                    $meta_value = get_post_meta($source_id, $key, true);
                    if ($meta_value) {
                        update_post_meta($new_id, $key, $meta_value);
                    }
                }
            } else {
                if ($value !== false && $value !== null) {
                    if (function_exists('update_field')) {
                        update_field($key, $value, $new_id);
                    } else {
                        update_post_meta($new_id, $key, $value);
                    }
                }
            }
        }
        
        // Copy taxonomies
        $taxonomies = array('membership-level', 'event-type', 'teacher');
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_post_terms($source_id, $taxonomy, array('fields' => 'ids'));
            if (!is_wp_error($terms) && !empty($terms)) {
                wp_set_post_terms($new_id, $terms, $taxonomy);
            }
        }
        
        // Copy post meta (for any additional fields)
        $meta_keys = get_post_meta($source_id);
        foreach ($meta_keys as $key => $values) {
            // Skip ACF fields (they're already handled)
            if (strpos($key, 'je_event_') === 0) {
                continue;
            }
            // Skip bunny_url and replay_vimeo_id explicitly (in case they're stored differently)
            if ($key === 'je_event_bunny_url' || $key === 'je_event_replay_vimeo_id') {
                continue;
            }
            // Skip internal WordPress meta
            if (strpos($key, '_') === 0 && $key !== '_thumbnail_id') {
                continue;
            }
            foreach ($values as $value) {
                add_post_meta($new_id, $key, maybe_unserialize($value));
            }
        }
        
        return $new_id;
    }
}
