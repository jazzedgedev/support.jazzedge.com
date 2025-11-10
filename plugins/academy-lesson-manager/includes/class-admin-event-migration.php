<?php
/**
 * Event Migration Admin Class
 * 
 * Handles migration of je_event posts to ALM lessons
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Event_Migration {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Table names
     */
    private $lessons_table;
    private $chapters_table;
    private $collections_table;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();
        $this->lessons_table = $this->database->get_table_name('lessons');
        $this->chapters_table = $this->database->get_table_name('chapters');
        $this->collections_table = $this->database->get_table_name('collections');
    }
    
    /**
     * Render the migration page
     */
    public function render_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['convert_event'])) {
            $this->handle_convert_event();
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_convert_events'])) {
            $this->handle_bulk_convert_events();
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_remove_conversion'])) {
            $this->handle_bulk_remove_conversion();
            return;
        }
        
        echo '<div class="wrap">';
        $this->render_navigation_buttons();
        echo '<h1>' . __('Event Migration', 'academy-lesson-manager') . '</h1>';
        
        switch ($action) {
            case 'convert':
                $this->render_convert_page($event_id);
                break;
            case 'bulk_convert':
                $this->render_bulk_convert_page();
                break;
            default:
                $this->render_list_page();
                break;
        }
        
        echo '</div>';
    }
    
    /**
     * Render navigation buttons
     */
    private function render_navigation_buttons() {
        echo '<div class="alm-navigation-buttons" style="margin-bottom: 20px;">';
        echo '<a href="?page=academy-manager" class="button">' . __('Collections', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-lessons" class="button">' . __('Lessons', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-chapters" class="button">' . __('Chapters', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-event-migration" class="button button-primary">' . __('Event Migration', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings" class="button" style="margin-left: 10px;">' . __('Settings', 'academy-lesson-manager') . '</a>';
        echo '</div>';
    }
    
    /**
     * Render the events list page
     */
    private function render_list_page() {
        // Handle bulk action redirect
        if (isset($_GET['bulk_action'])) {
            $event_ids = isset($_GET['event_ids']) ? array_map('intval', $_GET['event_ids']) : array();
            if (!empty($event_ids)) {
                if ($_GET['bulk_action'] === 'bulk_convert') {
                    wp_redirect(add_query_arg(array('action' => 'bulk_convert', 'event_ids' => implode(',', $event_ids)), admin_url('admin.php?page=academy-manager-event-migration')));
                    exit;
                } elseif ($_GET['bulk_action'] === 'remove_conversion') {
                    // Handle remove conversion immediately (no confirmation page needed)
                    $this->handle_bulk_remove_conversion_get($event_ids);
                    return;
                }
            }
        }
        
        // Show success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            switch ($message) {
                case 'converted':
                    $lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
                    $msg = __('Event converted successfully.', 'academy-lesson-manager');
                    if ($lesson_id) {
                        $msg .= ' <a href="?page=academy-manager-lessons&action=edit&id=' . $lesson_id . '">' . __('View Lesson', 'academy-lesson-manager') . '</a>';
                    }
                    echo '<div class="notice notice-success"><p>' . $msg . '</p></div>';
                    break;
                case 'bulk_converted':
                    $converted_count = isset($_GET['converted_count']) ? intval($_GET['converted_count']) : 0;
                    $total_count = isset($_GET['total_count']) ? intval($_GET['total_count']) : 0;
                    $msg = sprintf(__('Bulk conversion completed: %d of %d events converted successfully.', 'academy-lesson-manager'), $converted_count, $total_count);
                    echo '<div class="notice notice-success"><p>' . $msg . '</p></div>';
                    break;
                case 'removed_conversion':
                    $removed_count = isset($_GET['removed_count']) ? intval($_GET['removed_count']) : 0;
                    $total_count = isset($_GET['total_count']) ? intval($_GET['total_count']) : 0;
                    $msg = sprintf(__('Conversion removed successfully: %d of %d events processed.', 'academy-lesson-manager'), $removed_count, $total_count);
                    echo '<div class="notice notice-success"><p>' . $msg . '</p></div>';
                    break;
                case 'error':
                    echo '<div class="notice notice-error"><p>' . __('Error converting event.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        // Get search term
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        
        // Filter by conversion status
        $filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';
        
        // Get je_event posts with search
        $args = array(
            'post_type' => 'je_event',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        // Add search query if search term exists
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $events = get_posts($args);
        
        // Search and filter controls (outside bulk actions form)
        echo '<div class="tablenav top" style="margin-bottom: 10px;">';
        echo '<div class="alignleft actions">';
        // Search form - separate from bulk actions
        echo '<form method="get" action="" style="display: inline-block; margin-right: 15px;">';
        echo '<input type="hidden" name="page" value="academy-manager-event-migration" />';
        if (!empty($filter) && $filter !== 'all') {
            echo '<input type="hidden" name="filter" value="' . esc_attr($filter) . '" />';
        }
        echo '<input type="search" name="search" value="' . esc_attr($search) . '" placeholder="' . __('Search events...', 'academy-lesson-manager') . '" style="margin-right: 5px; width: 250px;" />';
        echo '<input type="submit" class="button" value="' . __('Search', 'academy-lesson-manager') . '" />';
        if (!empty($search)) {
            $clear_url = '?page=academy-manager-event-migration';
            if (!empty($filter) && $filter !== 'all') {
                $clear_url .= '&filter=' . esc_attr($filter);
            }
            echo ' <a href="' . $clear_url . '" class="button">' . __('Clear', 'academy-lesson-manager') . '</a>';
        }
        echo '</form>';
        
        // Filter buttons with search preserved
        $filter_url_base = '?page=academy-manager-event-migration';
        if (!empty($search)) {
            $filter_url_base .= '&search=' . urlencode($search);
        }
        
        echo '<a href="' . $filter_url_base . '&filter=all" class="button ' . ($filter === 'all' ? 'button-primary' : '') . '">' . __('All', 'academy-lesson-manager') . '</a> ';
        echo '<a href="' . $filter_url_base . '&filter=not_converted" class="button ' . ($filter === 'not_converted' ? 'button-primary' : '') . '">' . __('Not Converted', 'academy-lesson-manager') . '</a> ';
        echo '<a href="' . $filter_url_base . '&filter=converted" class="button ' . ($filter === 'converted' ? 'button-primary' : '') . '">' . __('Converted', 'academy-lesson-manager') . '</a>';
        echo '</div>';
        echo '</div>';
        
        // Bulk actions form - separate, wrapping only the table
        echo '<form method="get" action="" id="alm-events-form">';
        echo '<input type="hidden" name="page" value="academy-manager-event-migration" />';
        if (!empty($search)) {
            echo '<input type="hidden" name="search" value="' . esc_attr($search) . '" />';
        }
        if (!empty($filter) && $filter !== 'all') {
            echo '<input type="hidden" name="filter" value="' . esc_attr($filter) . '" />';
        }
        
        echo '<div class="tablenav top">';
        echo '<div class="alignleft actions bulkactions">';
        echo '<label for="bulk-action-selector-top" class="screen-reader-text">' . __('Select bulk action', 'academy-lesson-manager') . '</label>';
        echo '<select name="bulk_action" id="bulk-action-selector-top">';
        echo '<option value="-1">' . __('Bulk Actions', 'academy-lesson-manager') . '</option>';
        echo '<option value="bulk_convert">' . __('Convert to Lessons', 'academy-lesson-manager') . '</option>';
        echo '<option value="remove_conversion">' . __('Remove Conversion', 'academy-lesson-manager') . '</option>';
        echo '</select>';
        echo '<input type="submit" id="doaction" class="button action" value="' . __('Apply', 'academy-lesson-manager') . '" />';
        echo '</div>';
        echo '</div>';
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox" /></td>';
        echo '<th scope="col">' . __('Post ID', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . __('Event Title', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . __('Date', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . __('Has Replay', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . __('Has Resources', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . __('Publish Status', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . __('Status', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . __('Actions', 'academy-lesson-manager') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($events)) {
            echo '<tr><td colspan="9" style="text-align: center; padding: 20px;">' . __('No events found.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            foreach ($events as $event) {
                $this->render_event_row($event, $filter);
            }
        }
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<div class="tablenav bottom">';
        echo '<div class="alignleft actions bulkactions">';
        echo '<label for="bulk-action-selector-bottom" class="screen-reader-text">' . __('Select bulk action', 'academy-lesson-manager') . '</label>';
        echo '<select name="bulk_action" id="bulk-action-selector-bottom">';
        echo '<option value="-1">' . __('Bulk Actions', 'academy-lesson-manager') . '</option>';
        echo '<option value="bulk_convert">' . __('Convert to Lessons', 'academy-lesson-manager') . '</option>';
        echo '<option value="remove_conversion">' . __('Remove Conversion', 'academy-lesson-manager') . '</option>';
        echo '</select>';
        echo '<input type="submit" id="doaction2" class="button action" value="' . __('Apply', 'academy-lesson-manager') . '" />';
        echo '</div>';
        echo '</div>';
        
        // Show search results count if searching
        if (!empty($search)) {
            $results_count = count($events);
            echo '<div style="margin-top: 10px; padding: 10px; background: #f0f0f1; border-left: 4px solid #2271b1;">';
            echo '<strong>' . sprintf(__('Search results: %d event(s) found for "%s"', 'academy-lesson-manager'), $results_count, esc_html($search)) . '</strong>';
            echo '</div>';
        }
        
        echo '</form>';
        
        // Add JavaScript for bulk actions
        $this->render_bulk_actions_script();
    }
    
    /**
     * Render a single event row
     */
    private function render_event_row($event, $filter) {
        // Check if already converted
        $converted_lesson_id = get_post_meta($event->ID, '_converted_to_alm_lesson_id', true);
        $is_converted = !empty($converted_lesson_id);
        
        // Filter out converted/not converted based on filter
        if ($filter === 'converted' && !$is_converted) {
            return;
        }
        if ($filter === 'not_converted' && $is_converted) {
            return;
        }
        
        // Check for replay Vimeo ID
        $replay_vimeo_id = get_post_meta($event->ID, 'je_event_replay_vimeo_id', true);
        $has_replay = !empty($replay_vimeo_id);
        
        // Check for resources
        $has_resources = $this->event_has_resources($event->ID);
        
        // Get event start date from ACF field
        $event_start = get_field('je_event_start', $event->ID);
        $event_date_display = '';
        if (!empty($event_start)) {
            // Handle both timestamp and date string formats
            if (is_numeric($event_start)) {
                $date_ts = (int)$event_start;
            } else {
                $date_ts = strtotime((string)$event_start);
            }
            if ($date_ts) {
                $event_date_display = ' <span style="color: #666; font-weight: normal;">(' . date_i18n(get_option('date_format'), $date_ts) . ')</span>';
            }
        }
        
        echo '<tr>';
        echo '<th scope="row" class="check-column">';
        // Allow selection of both converted and unconverted events
        echo '<input type="checkbox" name="event_ids[]" value="' . $event->ID . '" class="alm-event-checkbox" />';
        echo '</th>';
        echo '<td>' . $event->ID . '</td>';
        echo '<td><strong>' . esc_html($event->post_title) . '</strong>' . $event_date_display . '</td>';
        echo '<td>' . date_i18n(get_option('date_format'), strtotime($event->post_date)) . '</td>';
        echo '<td>' . ($has_replay ? '<span style="color: #46b450;">✓</span>' : '<span style="color: #dc3232;">✗</span>') . '</td>';
        echo '<td>' . ($has_resources ? '<span style="color: #46b450;">✓</span>' : '<span style="color: #dc3232;">✗</span>') . '</td>';
        // Publish status column
        echo '<td>';
        $pub_status = $event->post_status;
        switch ($pub_status) {
            case 'publish':
                echo '<span style="color: #46b450; font-weight: bold;">' . __('Published', 'academy-lesson-manager') . '</span>';
                break;
            case 'private':
                echo '<span style="color: #6c7781; font-weight: bold;">' . __('Private', 'academy-lesson-manager') . '</span>';
                break;
            case 'draft':
                echo '<span style="color: #ffb900; font-weight: bold;">' . __('Draft', 'academy-lesson-manager') . '</span>';
                break;
            case 'pending':
                echo '<span style="color: #ffb900; font-weight: bold;">' . __('Pending', 'academy-lesson-manager') . '</span>';
                break;
            case 'future':
                echo '<span style="color: #2271b1; font-weight: bold;">' . __('Scheduled', 'academy-lesson-manager') . '</span>';
                break;
            case 'trash':
                echo '<span style="color: #dc3232; font-weight: bold;">' . __('Trash', 'academy-lesson-manager') . '</span>';
                break;
            default:
                echo '<span style="color: #666;">' . esc_html(ucfirst($pub_status)) . '</span>';
        }
        echo '</td>';
        echo '<td>';
        if ($is_converted) {
            echo '<span style="color: #46b450;">' . __('Converted', 'academy-lesson-manager') . '</span>';
            if ($converted_lesson_id) {
                echo ' <a href="?page=academy-manager-lessons&action=edit&id=' . $converted_lesson_id . '">' . __('View Lesson', 'academy-lesson-manager') . '</a>';
            }
        } else {
            echo '<span style="color: #dc3232;">' . __('Not Converted', 'academy-lesson-manager') . '</span>';
        }
        echo '</td>';
        echo '<td>';
        if (!$is_converted) {
            echo '<a href="?page=academy-manager-event-migration&action=convert&event_id=' . $event->ID . '" class="button button-small button-primary">' . __('Convert', 'academy-lesson-manager') . '</a>';
        } else {
            echo '<span class="button button-small" disabled>' . __('Already Converted', 'academy-lesson-manager') . '</span>';
        }
        echo '</td>';
        echo '</tr>';
    }
    
    /**
     * Check if event has resources
     */
    private function event_has_resources($event_id) {
        // Check ACF repeater count
        if (function_exists('get_field')) {
            $resources = get_field('je_event_resource_repeater', $event_id);
            if (!empty($resources) && is_array($resources)) {
                return true;
            }
        }
        
        // Fallback: Check post meta
        $repeater_count = get_post_meta($event_id, 'je_event_resource_repeater', true);
        if (!empty($repeater_count) && intval($repeater_count) > 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Render the convert page
     */
    private function render_convert_page($event_id) {
        $event = get_post($event_id);
        
        if (!$event || $event->post_type !== 'je_event') {
            echo '<div class="notice notice-error"><p>' . __('Event not found.', 'academy-lesson-manager') . '</p></div>';
            echo '<a href="?page=academy-manager-event-migration" class="button">' . __('Back to Events', 'academy-lesson-manager') . '</a>';
            return;
        }
        
        // Check if already converted
        $converted_lesson_id = get_post_meta($event_id, '_converted_to_alm_lesson_id', true);
        if (!empty($converted_lesson_id)) {
            echo '<div class="notice notice-warning"><p>' . __('This event has already been converted. ', 'academy-lesson-manager') . '<a href="?page=academy-manager-lessons&action=edit&id=' . $converted_lesson_id . '">' . __('View Lesson', 'academy-lesson-manager') . '</a></p></div>';
            echo '<a href="?page=academy-manager-event-migration" class="button">' . __('Back to Events', 'academy-lesson-manager') . '</a>';
            return;
        }
        
        // Get event data for preview
        $replay_vimeo_id = get_post_meta($event_id, 'je_event_replay_vimeo_id', true);
        $has_resources = $this->event_has_resources($event_id);
        $resources_count = $this->count_event_resources($event_id);
        
        echo '<div class="alm-event-convert">';
        echo '<h2>' . __('Convert Event to Lesson', 'academy-lesson-manager') . '</h2>';
        
        echo '<div style="background: #f9f9f9; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px;">';
        echo '<h3>' . __('Event Details', 'academy-lesson-manager') . '</h3>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row">' . __('Event Title', 'academy-lesson-manager') . '</th>';
        echo '<td><strong>' . esc_html($event->post_title) . '</strong></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row">' . __('Event ID', 'academy-lesson-manager') . '</th>';
        echo '<td>' . $event_id . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row">' . __('Has Replay Video', 'academy-lesson-manager') . '</th>';
        echo '<td>' . ($replay_vimeo_id ? '<span style="color: #46b450;">✓ Yes (Vimeo ID: ' . esc_html($replay_vimeo_id) . ')</span>' : '<span style="color: #dc3232;">✗ No</span>') . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row">' . __('Has Resources', 'academy-lesson-manager') . '</th>';
        echo '<td>' . ($has_resources ? '<span style="color: #46b450;">✓ Yes (' . $resources_count . ' resource(s))</span>' : '<span style="color: #dc3232;">✗ No</span>') . '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';
        
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="event_id" value="' . $event_id . '" />';
        echo '<input type="hidden" name="convert_event" value="1" />';
        
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row"><label for="collection_id">' . __('Target Collection', 'academy-lesson-manager') . ' <span style="color: #dc3232;">*</span></label></th>';
        echo '<td>';
        echo '<select id="collection_id" name="collection_id" required>';
        echo '<option value="">' . __('Select a collection...', 'academy-lesson-manager') . '</option>';
        $collections = $this->wpdb->get_results("SELECT ID, collection_title FROM {$this->collections_table} ORDER BY collection_title ASC");
        foreach ($collections as $collection) {
            echo '<option value="' . esc_attr($collection->ID) . '">' . esc_html(stripslashes($collection->collection_title)) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Select the collection where this lesson will be created.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="membership_level">' . __('Membership Level', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="membership_level" name="membership_level">';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            $selected = ($level['numeric'] == 2) ? 'selected' : '';
            echo '<option value="' . esc_attr($level['numeric']) . '" ' . $selected . '>' . esc_html($level['name']) . ' (' . $level['numeric'] . ') - ' . esc_html($level['description']) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        
        echo '<div style="background: #e7f5e7; border: 1px solid #46b450; padding: 15px; margin: 20px 0;">';
        echo '<h3>' . __('What will be created:', 'academy-lesson-manager') . '</h3>';
        echo '<ul style="list-style: disc; margin-left: 20px;">';
        echo '<li>' . __('Lesson: ') . esc_html($event->post_title) . '</li>';
        if ($replay_vimeo_id) {
            echo '<li>' . __('Chapter with Vimeo ID: ') . esc_html($replay_vimeo_id) . '</li>';
        }
        if ($has_resources) {
            echo '<li>' . sprintf(__('%d resource(s) will be converted', 'academy-lesson-manager'), $resources_count) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
        
        echo '<p class="submit">';
        echo '<input type="submit" class="button button-primary button-large" value="' . __('Convert Event to Lesson', 'academy-lesson-manager') . '" onclick="return confirm(\'' . __('Are you sure you want to convert this event to a lesson?', 'academy-lesson-manager') . '\');" /> ';
        echo '<a href="?page=academy-manager-event-migration" class="button">' . __('Cancel', 'academy-lesson-manager') . '</a>';
        echo '</p>';
        echo '</form>';
        
        echo '</div>';
    }
    
    /**
     * Count event resources
     */
    private function count_event_resources($event_id) {
        if (function_exists('get_field')) {
            $resources = get_field('je_event_resource_repeater', $event_id);
            if (!empty($resources) && is_array($resources)) {
                return count($resources);
            }
        }
        
        // Fallback: Check post meta
        $repeater_count = get_post_meta($event_id, 'je_event_resource_repeater', true);
        return !empty($repeater_count) ? intval($repeater_count) : 0;
    }
    
    /**
     * Handle event conversion
     */
    private function handle_convert_event() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
        $collection_id = isset($_POST['collection_id']) ? intval($_POST['collection_id']) : 0;
        $membership_level = isset($_POST['membership_level']) ? intval($_POST['membership_level']) : 2;
        
        if (empty($event_id) || empty($collection_id)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-event-migration')));
            exit;
        }
        
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'je_event') {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-event-migration')));
            exit;
        }
        
        // Check if already converted
        $existing_lesson_id = get_post_meta($event_id, '_converted_to_alm_lesson_id', true);
        if (!empty($existing_lesson_id)) {
            wp_redirect(add_query_arg(array('message' => 'error', 'event_id' => $event_id), admin_url('admin.php?page=academy-manager-event-migration&action=convert')));
            exit;
        }
        
        // Get formatted lesson title with date
        $lesson_title = $this->get_formatted_lesson_title($event_id, $event->post_title);
        
        // Create lesson
        $lesson_data = array(
            'collection_id' => $collection_id,
            'lesson_title' => $lesson_title,
            'lesson_description' => ALM_Helpers::clean_html_content($event->post_content),
            'post_date' => date('Y-m-d', strtotime($event->post_date)),
            'duration' => 0, // Will be calculated from chapter
            'song_lesson' => 'n',
            'slug' => sanitize_title($lesson_title),
            'membership_level' => $membership_level,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $result = $this->wpdb->insert($this->lessons_table, $lesson_data);
        
        if ($result === false) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-event-migration')));
            exit;
        }
        
        $lesson_id = $this->wpdb->insert_id;
        
        // Convert resources
        $resources = $this->convert_event_resources($event_id);
        if (!empty($resources)) {
            $this->wpdb->update(
                $this->lessons_table,
                array('resources' => serialize($resources)),
                array('ID' => $lesson_id),
                array('%s'),
                array('%d')
            );
        }
        
        // Get video URLs from event
        $replay_vimeo_id = get_post_meta($event_id, 'je_event_replay_vimeo_id', true);
        $bunny_url = get_post_meta($event_id, 'je_event_bunny_url', true);
        
        // Create chapter if either Vimeo ID or Bunny URL exists
        if (!empty($replay_vimeo_id) || !empty($bunny_url)) {
            $chapter_data = array(
                'lesson_id' => $lesson_id,
                'chapter_title' => $lesson_title,
                'menu_order' => 1,
                'vimeo_id' => !empty($replay_vimeo_id) ? intval($replay_vimeo_id) : 0,
                'youtube_id' => '',
                'bunny_url' => !empty($bunny_url) ? sanitize_text_field($bunny_url) : '',
                'duration' => 0,
                'free' => 'n',
                'slug' => sanitize_title($lesson_title),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );
            
            $this->wpdb->insert($this->chapters_table, $chapter_data);
        }
        
        // Sync lesson to WordPress post
        $sync = new ALM_Post_Sync();
        $post_id = $sync->sync_lesson_to_post($lesson_id);
        
        // Copy featured image if exists
        $thumbnail_id = get_post_thumbnail_id($event_id);
        if ($thumbnail_id && $post_id) {
            set_post_thumbnail($post_id, $thumbnail_id);
        }
        
        // Mark event as converted
        update_post_meta($event_id, '_converted_to_alm_lesson_id', $lesson_id);
        
        // Reorder lessons in collection by title
        $this->reorder_collection_lessons_by_title($collection_id);
        
        wp_redirect(add_query_arg(array('message' => 'converted', 'lesson_id' => $lesson_id), admin_url('admin.php?page=academy-manager-event-migration')));
        exit;
    }
    
    /**
     * Convert event resources to ALM format
     */
    private function convert_event_resources($event_id) {
        $resources = array();
        
        // Try ACF first
        if (function_exists('get_field')) {
            $acf_resources = get_field('je_event_resource_repeater', $event_id);
            if (!empty($acf_resources) && is_array($acf_resources)) {
                foreach ($acf_resources as $index => $resource) {
                    $resource_type = isset($resource['je_event_resource_type']) ? $resource['je_event_resource_type'] : '';
                    $resource_file = isset($resource['je_event_resource_file']) ? $resource['je_event_resource_file'] : '';
                    $resource_youtube = isset($resource['je_event_resource_youtube']) ? $resource['je_event_resource_youtube'] : '';
                    
                    // Map resource type
                    $alm_type = $this->map_resource_type($resource_type);
                    
                    // Get file URL if attachment ID provided
                    // ACF file fields can return an array with 'url', 'id', etc., or just an ID
                    $file_url = '';
                    $attachment_id = 0;
                    if (!empty($resource_file)) {
                        if (is_array($resource_file)) {
                            // ACF array format: ['url' => '...', 'id' => 123]
                            $file_url = isset($resource_file['url']) ? $resource_file['url'] : '';
                            $attachment_id = isset($resource_file['id']) ? intval($resource_file['id']) : (isset($resource_file['ID']) ? intval($resource_file['ID']) : 0);
                        } elseif (is_numeric($resource_file)) {
                            $attachment_id = intval($resource_file);
                            $file_url = wp_get_attachment_url($attachment_id);
                        } elseif (is_string($resource_file)) {
                            $file_url = $resource_file;
                        }
                    }
                    
                    // Only proceed if we have a valid URL string
                    $final_url = '';
                    $final_attachment_id = 0;
                    
                    if (!empty($file_url) && is_string($file_url)) {
                        $final_url = $file_url;
                        $final_attachment_id = $attachment_id;
                    } elseif (!empty($resource_youtube) && is_string($resource_youtube)) {
                        $final_url = esc_url_raw($resource_youtube);
                        $final_attachment_id = 0;
                    }
                    
                    // Only store if we have a valid URL
                    if (!empty($final_url)) {
                        // Determine resource key (support multiple of same type)
                        $resource_key = $this->get_resource_key($resources, $alm_type);
                        
                        $resources[$resource_key] = array(
                            'url' => $final_url,
                            'attachment_id' => $final_attachment_id,
                            'label' => ''
                        );
                    }
                }
            }
        }
        
        // Fallback: Parse post meta directly
        if (empty($resources)) {
            $repeater_count = get_post_meta($event_id, 'je_event_resource_repeater', true);
            if (!empty($repeater_count) && intval($repeater_count) > 0) {
                $count = intval($repeater_count);
                for ($i = 0; $i < $count; $i++) {
                    $resource_type = get_post_meta($event_id, 'je_event_resource_repeater_' . $i . '_je_event_resource_type', true);
                    $resource_file = get_post_meta($event_id, 'je_event_resource_repeater_' . $i . '_je_event_resource_file', true);
                    $resource_youtube = get_post_meta($event_id, 'je_event_resource_repeater_' . $i . '_je_event_resource_youtube', true);
                    
                    if (empty($resource_file) && empty($resource_youtube)) {
                        continue;
                    }
                    
                    // Map resource type
                    $alm_type = $this->map_resource_type($resource_type);
                    
                    // Get file URL if attachment ID provided
                    // Handle both numeric ID and array format from post meta
                    $file_url = '';
                    $attachment_id = 0;
                    if (!empty($resource_file)) {
                        if (is_array($resource_file)) {
                            // Array format from ACF or post meta
                            $file_url = isset($resource_file['url']) ? $resource_file['url'] : '';
                            $attachment_id = isset($resource_file['id']) ? intval($resource_file['id']) : (isset($resource_file['ID']) ? intval($resource_file['ID']) : 0);
                        } elseif (is_numeric($resource_file)) {
                            $attachment_id = intval($resource_file);
                            $file_url = wp_get_attachment_url($attachment_id);
                        } elseif (is_string($resource_file)) {
                            $file_url = $resource_file;
                        }
                    }
                    
                    // Only proceed if we have a valid URL string
                    $final_url = '';
                    $final_attachment_id = 0;
                    
                    if (!empty($file_url) && is_string($file_url)) {
                        $final_url = $file_url;
                        $final_attachment_id = $attachment_id;
                    } elseif (!empty($resource_youtube) && is_string($resource_youtube)) {
                        $final_url = esc_url_raw($resource_youtube);
                        $final_attachment_id = 0;
                    }
                    
                    // Only store if we have a valid URL
                    if (!empty($final_url)) {
                        // Determine resource key
                        $resource_key = $this->get_resource_key($resources, $alm_type);
                        
                        $resources[$resource_key] = array(
                            'url' => $final_url,
                            'attachment_id' => $final_attachment_id,
                            'label' => ''
                        );
                    }
                }
            }
        }
        
        return $resources;
    }
    
    /**
     * Map event resource type to ALM resource type
     */
    private function map_resource_type($event_type) {
        $type_map = array(
            'coaching-sheet' => 'sheet_music',
            'sheet-music' => 'sheet_music',
            'sheet' => 'sheet_music',
            'ireal' => 'ireal',
            'irealpro' => 'ireal',
            'jam' => 'jam',
            'backing-track' => 'jam',
            'mp3' => 'jam',
            'zip' => 'zip'
        );
        
        $event_type = strtolower(trim($event_type));
        return isset($type_map[$event_type]) ? $type_map[$event_type] : 'sheet_music'; // Default to sheet_music
    }
    
    /**
     * Get resource key (support multiple of same type)
     */
    private function get_resource_key($existing_resources, $type) {
        $count = 1;
        foreach ($existing_resources as $key => $value) {
            if (strpos($key, $type) === 0) {
                $count++;
            }
        }
        
        return ($count > 1) ? $type . $count : $type;
    }
    
    /**
     * Render bulk convert page
     */
    private function render_bulk_convert_page() {
        $event_ids_str = isset($_GET['event_ids']) ? sanitize_text_field($_GET['event_ids']) : '';
        $event_ids = !empty($event_ids_str) ? array_map('intval', explode(',', $event_ids_str)) : array();
        
        if (empty($event_ids)) {
            echo '<div class="notice notice-error"><p>' . __('No events selected.', 'academy-lesson-manager') . '</p></div>';
            echo '<a href="?page=academy-manager-event-migration" class="button">' . __('Back to Events', 'academy-lesson-manager') . '</a>';
            return;
        }
        
        // Get event details
        $events = array();
        $total_resources = 0;
        $total_replays = 0;
        foreach ($event_ids as $event_id) {
            $event = get_post($event_id);
            if ($event && $event->post_type === 'je_event') {
                // Check if already converted
                $converted_lesson_id = get_post_meta($event_id, '_converted_to_alm_lesson_id', true);
                if (empty($converted_lesson_id)) {
                    $replay_vimeo_id = get_post_meta($event_id, 'je_event_replay_vimeo_id', true);
                    $resources_count = $this->count_event_resources($event_id);
                    
                    $events[] = array(
                        'ID' => $event_id,
                        'title' => $event->post_title,
                        'replay_vimeo_id' => $replay_vimeo_id,
                        'resources_count' => $resources_count
                    );
                    
                    if (!empty($replay_vimeo_id)) {
                        $total_replays++;
                    }
                    $total_resources += $resources_count;
                }
            }
        }
        
        if (empty($events)) {
            echo '<div class="notice notice-warning"><p>' . __('All selected events have already been converted.', 'academy-lesson-manager') . '</p></div>';
            echo '<a href="?page=academy-manager-event-migration" class="button">' . __('Back to Events', 'academy-lesson-manager') . '</a>';
            return;
        }
        
        echo '<div class="alm-event-bulk-convert">';
        echo '<h2>' . sprintf(__('Bulk Convert Events to Lessons (%d selected)', 'academy-lesson-manager'), count($events)) . '</h2>';
        
        echo '<div style="background: #f9f9f9; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px;">';
        echo '<h3>' . __('Selected Events', 'academy-lesson-manager') . '</h3>';
        echo '<ul style="list-style: disc; margin-left: 20px; max-height: 300px; overflow-y: auto;">';
        foreach ($events as $event_data) {
            echo '<li><strong>' . esc_html($event_data['title']) . '</strong> (ID: ' . $event_data['ID'] . ')';
            if ($event_data['replay_vimeo_id']) {
                echo ' - <span style="color: #46b450;">Replay: ' . esc_html($event_data['replay_vimeo_id']) . '</span>';
            }
            if ($event_data['resources_count'] > 0) {
                echo ' - <span style="color: #46b450;">' . $event_data['resources_count'] . ' resource(s)</span>';
            }
            echo '</li>';
        }
        echo '</ul>';
        echo '<p><strong>' . __('Summary:', 'academy-lesson-manager') . '</strong> ' . count($events) . ' events, ' . $total_replays . ' with replays, ' . $total_resources . ' total resources</p>';
        echo '</div>';
        
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="bulk_convert_events" value="1" />';
        foreach ($event_ids as $event_id) {
            echo '<input type="hidden" name="event_ids[]" value="' . $event_id . '" />';
        }
        
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row"><label for="collection_id">' . __('Target Collection', 'academy-lesson-manager') . ' <span style="color: #dc3232;">*</span></label></th>';
        echo '<td>';
        echo '<select id="collection_id" name="collection_id" required>';
        echo '<option value="">' . __('Select a collection...', 'academy-lesson-manager') . '</option>';
        $collections = $this->wpdb->get_results("SELECT ID, collection_title FROM {$this->collections_table} ORDER BY collection_title ASC");
        foreach ($collections as $collection) {
            echo '<option value="' . esc_attr($collection->ID) . '">' . esc_html(stripslashes($collection->collection_title)) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Select the collection where all lessons will be created.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="membership_level">' . __('Membership Level', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="membership_level" name="membership_level">';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            $selected = ($level['numeric'] == 2) ? 'selected' : '';
            echo '<option value="' . esc_attr($level['numeric']) . '" ' . $selected . '>' . esc_html($level['name']) . ' (' . $level['numeric'] . ') - ' . esc_html($level['description']) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        
        echo '<div style="background: #e7f5e7; border: 1px solid #46b450; padding: 15px; margin: 20px 0;">';
        echo '<h3>' . __('What will be created:', 'academy-lesson-manager') . '</h3>';
        echo '<ul style="list-style: disc; margin-left: 20px;">';
        echo '<li>' . sprintf(__('%d lesson(s)', 'academy-lesson-manager'), count($events)) . '</li>';
        if ($total_replays > 0) {
            echo '<li>' . sprintf(__('%d chapter(s) with Vimeo replays', 'academy-lesson-manager'), $total_replays) . '</li>';
        }
        if ($total_resources > 0) {
            echo '<li>' . sprintf(__('%d total resource(s) will be converted', 'academy-lesson-manager'), $total_resources) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
        
        echo '<p class="submit">';
        echo '<input type="submit" class="button button-primary button-large" value="' . sprintf(__('Convert %d Events to Lessons', 'academy-lesson-manager'), count($events)) . '" onclick="return confirm(\'' . sprintf(__('Are you sure you want to convert %d events to lessons? This action cannot be undone.', 'academy-lesson-manager'), count($events)) . '\');" /> ';
        echo '<a href="?page=academy-manager-event-migration" class="button">' . __('Cancel', 'academy-lesson-manager') . '</a>';
        echo '</p>';
        echo '</form>';
        
        echo '</div>';
    }
    
    /**
     * Handle bulk event conversion
     */
    private function handle_bulk_convert_events() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $event_ids = isset($_POST['event_ids']) ? array_map('intval', $_POST['event_ids']) : array();
        $collection_id = isset($_POST['collection_id']) ? intval($_POST['collection_id']) : 0;
        $membership_level = isset($_POST['membership_level']) ? intval($_POST['membership_level']) : 2;
        
        if (empty($event_ids) || empty($collection_id)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-event-migration')));
            exit;
        }
        
        $converted_count = 0;
        $total_count = count($event_ids);
        $sync = new ALM_Post_Sync();
        
        foreach ($event_ids as $event_id) {
            $event = get_post($event_id);
            if (!$event || $event->post_type !== 'je_event') {
                continue;
            }
            
            // Check if already converted
            $existing_lesson_id = get_post_meta($event_id, '_converted_to_alm_lesson_id', true);
            if (!empty($existing_lesson_id)) {
                continue; // Skip already converted events
            }
            
            // Get formatted lesson title with date
            $lesson_title = $this->get_formatted_lesson_title($event_id, $event->post_title);
            
            // Create lesson
            $lesson_data = array(
                'collection_id' => $collection_id,
                'lesson_title' => $lesson_title,
                'lesson_description' => ALM_Helpers::clean_html_content($event->post_content),
                'post_date' => date('Y-m-d', strtotime($event->post_date)),
                'duration' => 0, // Will be calculated from chapter
                'song_lesson' => 'n',
                'slug' => sanitize_title($lesson_title),
                'membership_level' => $membership_level,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );
            
            $result = $this->wpdb->insert($this->lessons_table, $lesson_data);
            
            if ($result === false) {
                continue; // Skip failed inserts
            }
            
            $lesson_id = $this->wpdb->insert_id;
            
            // Convert resources
            $resources = $this->convert_event_resources($event_id);
            if (!empty($resources)) {
                $this->wpdb->update(
                    $this->lessons_table,
                    array('resources' => serialize($resources)),
                    array('ID' => $lesson_id),
                    array('%s'),
                    array('%d')
                );
            }
            
            // Get video URLs from event
            $replay_vimeo_id = get_post_meta($event_id, 'je_event_replay_vimeo_id', true);
            $bunny_url = get_post_meta($event_id, 'je_event_bunny_url', true);
            
            // Create chapter if either Vimeo ID or Bunny URL exists
            if (!empty($replay_vimeo_id) || !empty($bunny_url)) {
                $chapter_data = array(
                    'lesson_id' => $lesson_id,
                    'chapter_title' => $lesson_title,
                    'menu_order' => 1,
                    'vimeo_id' => !empty($replay_vimeo_id) ? intval($replay_vimeo_id) : 0,
                    'youtube_id' => '',
                    'bunny_url' => !empty($bunny_url) ? sanitize_text_field($bunny_url) : '',
                    'duration' => 0,
                    'free' => 'n',
                    'slug' => sanitize_title($lesson_title),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                );
                
                $this->wpdb->insert($this->chapters_table, $chapter_data);
            }
            
            // Sync lesson to WordPress post
            $post_id = $sync->sync_lesson_to_post($lesson_id);
            
            // Copy featured image if exists
            $thumbnail_id = get_post_thumbnail_id($event_id);
            if ($thumbnail_id && $post_id) {
                set_post_thumbnail($post_id, $thumbnail_id);
            }
            
            // Mark event as converted
            update_post_meta($event_id, '_converted_to_alm_lesson_id', $lesson_id);
            
            $converted_count++;
        }
        
        // Reorder lessons in collection by title after bulk conversion
        $this->reorder_collection_lessons_by_title($collection_id);
        
        wp_redirect(add_query_arg(array(
            'message' => 'bulk_converted',
            'converted_count' => $converted_count,
            'total_count' => $total_count
        ), admin_url('admin.php?page=academy-manager-event-migration')));
        exit;
    }
    
    /**
     * Reorder lessons in a collection by title (alphabetically)
     * 
     * @param int $collection_id Collection ID
     */
    private function reorder_collection_lessons_by_title($collection_id) {
        // Ensure menu_order column exists
        $database = new ALM_Database();
        $database->check_and_add_menu_order_column();
        
        // Get all lessons in the collection, sorted by post_date then title
        $lessons = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT ID, lesson_title, post_date FROM {$this->lessons_table} WHERE collection_id = %d ORDER BY post_date ASC, lesson_title ASC",
            $collection_id
        ));
        
        // Update menu_order sequentially using 0-based order (to match drag-drop behavior)
        $menu_order = 0;
        foreach ($lessons as $lesson) {
            $this->wpdb->update(
                $this->lessons_table,
                array('menu_order' => $menu_order),
                array('ID' => $lesson->ID),
                array('%d'),
                array('%d')
            );
            $menu_order++;
        }
    }
    
    /**
     * Get formatted lesson title with event date
     * 
     * @param int $event_id Event post ID
     * @param string $base_title Base title from event post
     * @return string Formatted title with date appended if available
     */
    private function get_formatted_lesson_title($event_id, $base_title) {
        // Get event start date from ACF field
        $event_start = get_field('je_event_start', $event_id);
        
        if (!empty($event_start)) {
            // Handle both timestamp and date string formats
            if (is_numeric($event_start)) {
                $date_ts = (int)$event_start;
            } else {
                $date_ts = strtotime((string)$event_start);
            }
            
            if ($date_ts) {
                // Format date and append to title
                $formatted_date = date_i18n('F j, Y', $date_ts);
                return $base_title . ' - ' . $formatted_date;
            }
        }
        
        // Fallback to base title if no date found
        return $base_title;
    }
    
    /**
     * Handle bulk remove conversion (from GET request, immediate action)
     */
    private function handle_bulk_remove_conversion_get($event_ids) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $removed_count = 0;
        $total_count = count($event_ids);
        
        foreach ($event_ids as $event_id) {
            if ($this->remove_event_conversion($event_id)) {
                $removed_count++;
            }
        }
        
        // Clear object cache after bulk operation
        wp_cache_flush();
        
        wp_redirect(add_query_arg(array(
            'message' => 'removed_conversion',
            'removed_count' => $removed_count,
            'total_count' => $total_count,
            'filter' => 'not_converted' // Show not converted filter after removal
        ), admin_url('admin.php?page=academy-manager-event-migration')));
        exit;
    }
    
    /**
     * Handle bulk remove conversion (from POST request)
     */
    private function handle_bulk_remove_conversion() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $event_ids = isset($_POST['event_ids']) ? array_map('intval', $_POST['event_ids']) : array();
        
        if (empty($event_ids)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-event-migration')));
            exit;
        }
        
        $removed_count = 0;
        $total_count = count($event_ids);
        
        foreach ($event_ids as $event_id) {
            if ($this->remove_event_conversion($event_id)) {
                $removed_count++;
            }
        }
        
        // Clear object cache after bulk operation
        wp_cache_flush();
        
        wp_redirect(add_query_arg(array(
            'message' => 'removed_conversion',
            'removed_count' => $removed_count,
            'total_count' => $total_count,
            'filter' => 'not_converted' // Show not converted filter after removal
        ), admin_url('admin.php?page=academy-manager-event-migration')));
        exit;
    }
    
    /**
     * Remove conversion for a single event
     * Deletes lesson, chapters, WordPress post, and meta data
     * 
     * @param int $event_id Event post ID
     * @return bool True on success, false on failure
     */
    private function remove_event_conversion($event_id) {
        // Get the lesson ID from event meta
        $lesson_id = get_post_meta($event_id, '_converted_to_alm_lesson_id', true);
        
        if (empty($lesson_id)) {
            return false; // Not converted, nothing to remove
        }
        
        // Get lesson data to find WordPress post ID (check if lesson still exists)
        $lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT post_id FROM {$this->lessons_table} WHERE ID = %d",
            $lesson_id
        ));
        
        // Delete all chapters for this lesson (even if lesson doesn't exist anymore)
        $this->wpdb->delete(
            $this->chapters_table,
            array('lesson_id' => $lesson_id),
            array('%d')
        );
        
        // Delete the lesson from lessons table (only if it exists)
        if ($lesson) {
            $this->wpdb->delete(
                $this->lessons_table,
                array('ID' => $lesson_id),
                array('%d')
            );
            
            // Delete WordPress post if it exists
            if (!empty($lesson->post_id)) {
                $post = get_post($lesson->post_id);
                if ($post) {
                    wp_delete_post($lesson->post_id, true); // true = force delete (skip trash)
                }
            }
        }
        
        // Remove conversion marker from event - use multiple methods to ensure deletion
        delete_post_meta($event_id, '_converted_to_alm_lesson_id');
        
        // Also delete directly via database to bypass any caching
        $this->wpdb->delete(
            $this->wpdb->postmeta,
            array(
                'post_id' => $event_id,
                'meta_key' => '_converted_to_alm_lesson_id'
            ),
            array('%d', '%s')
        );
        
        // Clear WordPress object cache for this post
        clean_post_cache($event_id);
        
        return true;
    }
    
    /**
     * Render JavaScript for bulk actions
     */
    private function render_bulk_actions_script() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Handle select all checkbox
            $('#cb-select-all-1').on('change', function() {
                $('.alm-event-checkbox').prop('checked', $(this).prop('checked'));
            });
            
            // Handle form submission for bulk actions
            $('#alm-events-form').on('submit', function(e) {
                var bulkAction = $('select[name="bulk_action"]').val();
                if (bulkAction === '-1' || bulkAction === '') {
                    e.preventDefault();
                    alert('<?php echo esc_js(__('Please select a bulk action.', 'academy-lesson-manager')); ?>');
                    return false;
                }
                
                var checked = $('.alm-event-checkbox:checked').length;
                if (checked === 0) {
                    e.preventDefault();
                    alert('<?php echo esc_js(__('Please select at least one event.', 'academy-lesson-manager')); ?>');
                    return false;
                }
                
                // Handle remove conversion (confirmation)
                if (bulkAction === 'remove_conversion') {
                    if (!confirm('<?php echo esc_js(__('Are you sure you want to remove the conversion for the selected events? This will permanently delete the lessons, chapters, and WordPress posts that were created. This action cannot be undone.', 'academy-lesson-manager')); ?>')) {
                        e.preventDefault();
                        return false;
                    }
                }
                
                // Change form action for bulk convert
                if (bulkAction === 'bulk_convert') {
                    // Form will submit with GET method and redirect to bulk_convert page
                }
            });
            
            // Update select all checkbox state when individual checkboxes change
            $('.alm-event-checkbox').on('change', function() {
                var total = $('.alm-event-checkbox').length;
                var checked = $('.alm-event-checkbox:checked').length;
                $('#cb-select-all-1').prop('checked', total === checked);
            });
        });
        </script>
        <?php
    }
}

