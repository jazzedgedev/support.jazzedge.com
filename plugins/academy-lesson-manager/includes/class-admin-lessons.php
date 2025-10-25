<?php
/**
 * Lessons Admin Class
 * 
 * Handles the lessons admin page functionality
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Lessons {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Table name
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();
        $this->table_name = $this->database->get_table_name('lessons');
    }
    
    /**
     * Render the lessons admin page
     */
    public function render_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Handle bulk actions first
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
            $this->handle_bulk_action();
            return;
        }
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handle_form_submission();
            return;
        }
        
        echo '<div class="wrap">';
        $this->render_navigation_buttons('lessons');
        echo '<h1>' . __('Lessons', 'academy-lesson-manager') . ' <a href="?page=academy-manager-lessons&action=add" class="page-title-action">' . __('Add New', 'academy-lesson-manager') . '</a></h1>';
        
        switch ($action) {
            case 'add':
                $this->render_add_page();
                break;
            case 'edit':
                $this->render_edit_page($id);
                break;
            case 'delete':
                $this->handle_delete($id);
                break;
            case 'fix':
                $this->handle_fix($id);
                break;
            case 'sync_all':
                $this->handle_sync_all();
                break;
            case 'remove_from_course':
                $this->handle_remove_from_course($id);
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
    private function render_navigation_buttons($current_page) {
        echo '<div class="alm-navigation-buttons" style="margin-bottom: 20px;">';
        echo '<a href="?page=academy-manager" class="button ' . ($current_page === 'collections' ? 'button-primary' : '') . '">' . __('Collections', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-lessons" class="button ' . ($current_page === 'lessons' ? 'button-primary' : '') . '">' . __('Lessons', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-chapters" class="button ' . ($current_page === 'chapters' ? 'button-primary' : '') . '">' . __('Chapters', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings" class="button ' . ($current_page === 'settings' ? 'button-primary' : '') . '" style="margin-left: 10px;">' . __('Settings', 'academy-lesson-manager') . '</a>';
        echo '</div>';
    }
    
    /**
     * Render the lessons list page
     */
    private function render_list_page() {
        // Show success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            switch ($message) {
                case 'created':
                    echo '<div class="notice notice-success"><p>' . __('Lesson created successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'updated':
                    echo '<div class="notice notice-success"><p>' . __('Lesson updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'deleted':
                    echo '<div class="notice notice-success"><p>' . __('Lesson deleted successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'fixed':
                    echo '<div class="notice notice-success"><p>' . __('WordPress post created successfully for this lesson.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'already_exists':
                    echo '<div class="notice notice-warning"><p>' . __('WordPress post already exists for this lesson.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'synced':
                    echo '<div class="notice notice-success"><p>' . __('All lessons synced successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'bulk_updated':
                    echo '<div class="notice notice-success"><p>' . __('Membership levels updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'error':
                    echo '<div class="notice notice-error"><p>' . __('An error occurred. Please try again.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'no_lessons_selected':
                    echo '<div class="notice notice-warning"><p>' . __('Please select at least one lesson.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'no_level_selected':
                    echo '<div class="notice notice-warning"><p>' . __('Please select a membership level.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        // Handle search and filters
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $membership_filter = isset($_GET['membership_level']) ? intval($_GET['membership_level']) : 0;
        $order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'ID';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        
        // Build query
        $where_conditions = array();
        if (!empty($search)) {
            $where_conditions[] = $this->wpdb->prepare("(lesson_title LIKE %s OR lesson_description LIKE %s)", 
                '%' . $search . '%', '%' . $search . '%');
        }
        if ($membership_filter > 0) {
            $where_conditions[] = $this->wpdb->prepare("membership_level = %d", $membership_filter);
        }
        
        $where = '';
        if (!empty($where_conditions)) {
            $where = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        // Validate order by
        $allowed_order_by = array('ID', 'lesson_title', 'post_date', 'sku', 'site');
        if (!in_array($order_by, $allowed_order_by)) {
            $order_by = 'ID';
        }
        
        $allowed_order = array('ASC', 'DESC');
        if (!in_array($order, $allowed_order)) {
            $order = 'DESC';
        }
        
        $sql = "SELECT * FROM {$this->table_name} {$where} ORDER BY {$order_by} {$order}";
        $lessons = $this->wpdb->get_results($sql);
        
        // Render search form (outside bulk actions form)
        $this->render_search_form($search, $membership_filter);
        
        // Open bulk actions form
        echo '<!-- DEBUG: Opening bulk actions form -->';
        echo '<form method="post" action="" id="bulk-actions-form">';
        echo '<div class="alm-bulk-actions" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">';
        echo '<h3>' . __('Bulk Actions', 'academy-lesson-manager') . '</h3>';
        
        echo '<p>';
        echo '<label for="bulk_membership_level">' . __('Set membership level to:', 'academy-lesson-manager') . '</label> ';
        echo '<select id="bulk_membership_level" name="bulk_membership_level">';
        echo '<option value="">' . __('Select level...', 'academy-lesson-manager') . '</option>';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            echo '<option value="' . esc_attr($level['numeric']) . '">' . esc_html($level['name']) . ' (' . $level['numeric'] . ') - ' . esc_html($level['description']) . '</option>';
        }
        echo '</select>';
        echo '<input type="submit" class="button" value="' . __('Update Selected Lessons', 'academy-lesson-manager') . '" onclick="return confirm(\'' . __('Are you sure you want to update the membership levels for the selected lessons?', 'academy-lesson-manager') . '\')" />';
        echo '</p>';
        echo '<input type="hidden" name="bulk_action" value="update_membership" />';
        
        echo '<p class="description">' . __('Select lessons from the list below, then choose a membership level and click "Update Selected Lessons".', 'academy-lesson-manager') . '</p>';
        
        echo '<p style="margin-top: 15px;">';
        echo '<a href="?page=academy-manager-lessons&action=sync_all" class="button" onclick="return confirm(\'' . __('This will sync all lessons to WordPress posts. Continue?', 'academy-lesson-manager') . '\')">' . __('Sync All Lessons', 'academy-lesson-manager') . '</a>';
        echo '</p>';
        
        echo '</div>';
        
        // Render lessons table (inside bulk actions form)
        $this->render_lessons_table($lessons, $order_by, $order);
        
        // Debug: Check if we're still inside the form
        echo '<!-- DEBUG: About to close bulk actions form -->';
        
        // Close bulk actions form
        echo '</form>';
        
        echo '<!-- DEBUG: Bulk actions form closed -->';
    }
    
    /**
     * Render search form
     */
    private function render_search_form($search, $membership_filter = 0) {
        echo '<div class="alm-search-form">';
        
        // Search box
        echo '<form method="get" action="" class="search-box">';
        echo '<input type="hidden" name="page" value="academy-manager-lessons" />';
        if ($membership_filter > 0) {
            echo '<input type="hidden" name="membership_level" value="' . esc_attr($membership_filter) . '" />';
        }
        echo '<label class="screen-reader-text" for="lesson-search-input">' . __('Search Lessons:', 'academy-lesson-manager') . '</label>';
        echo '<input type="search" id="lesson-search-input" name="search" value="' . esc_attr($search) . '" placeholder="' . __('Search lessons...', 'academy-lesson-manager') . '" />';
        echo '<input type="submit" id="search-submit" class="button" value="' . __('Search Lessons', 'academy-lesson-manager') . '" />';
        echo '</form>';
        
        // Membership level filter
        echo '<form method="get" action="" class="alm-filter-form">';
        echo '<input type="hidden" name="page" value="academy-manager-lessons" />';
        if (!empty($search)) {
            echo '<input type="hidden" name="search" value="' . esc_attr($search) . '" />';
        }
        echo '<label for="membership-filter">' . __('Filter by Membership Level:', 'academy-lesson-manager') . '</label> ';
        echo '<select id="membership-filter" name="membership_level" onchange="this.form.submit()">';
        echo '<option value="">' . __('All Levels', 'academy-lesson-manager') . '</option>';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            $selected = ($membership_filter == $level['numeric']) ? 'selected' : '';
            $lesson_count = $this->get_lesson_count_by_membership($level['numeric']);
            echo '<option value="' . esc_attr($level['numeric']) . '" ' . $selected . '>' . esc_html($level['name']) . ' (' . $lesson_count . ' lessons)</option>';
        }
        echo '</select>';
        if ($membership_filter > 0) {
            echo ' <a href="?page=academy-manager-lessons' . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="button">' . __('Clear Filter', 'academy-lesson-manager') . '</a>';
        }
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render lessons table
     */
    private function render_lessons_table($lessons, $order_by, $order) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>';
        echo '<th scope="col" class="manage-column column-id">' . $this->get_sortable_header('ID', 'ID', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column column-post-id">' . __('Post ID', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-date">' . $this->get_sortable_header('Date', 'post_date', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column column-title">' . $this->get_sortable_header('Lesson Title', 'lesson_title', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column column-song">' . __('Song', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-course">' . __('Collection', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-duration">' . __('Duration', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-membership">' . __('Level', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-actions">' . __('Actions', 'academy-lesson-manager') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($lessons)) {
            echo '<tr><td colspan="9" class="no-items">' . __('No lessons found.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            foreach ($lessons as $lesson) {
                $this->render_lesson_row($lesson);
            }
        }
        
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Render a single lesson row
     */
    private function render_lesson_row($lesson) {
        $collection_title = ALM_Helpers::get_collection_title($lesson->collection_id);
        
        echo '<tr>';
        echo '<th scope="row" class="check-column"><input type="checkbox" name="lesson[]" value="' . $lesson->ID . '" /></th>';
        echo '<td class="column-id">' . $lesson->ID . '</td>';
        echo '<td class="column-post-id">' . ($lesson->post_id ? $lesson->post_id : '—') . '</td>';
        echo '<td class="column-date">' . ALM_Helpers::format_date($lesson->post_date) . '</td>';
        echo '<td class="column-title"><strong>' . esc_html(stripslashes($lesson->lesson_title)) . '</strong></td>';
        echo '<td class="column-song">' . ($lesson->song_lesson === 'y' ? __('Yes', 'academy-lesson-manager') : __('No', 'academy-lesson-manager')) . '</td>';
        echo '<td class="column-course"><a href="?page=academy-manager&action=edit&id=' . $lesson->collection_id . '">' . esc_html($collection_title) . '</a></td>';
        echo '<td class="column-duration">' . ALM_Helpers::format_duration($lesson->duration) . '</td>';
        echo '<td class="column-membership">' . esc_html(ALM_Admin_Settings::get_membership_level_name($lesson->membership_level)) . '</td>';
        echo '<td class="column-actions">';
        echo '<div style="display: flex; gap: 5px; flex-wrap: nowrap;">';
        echo '<a href="?page=academy-manager-lessons&action=edit&id=' . $lesson->ID . '" class="button button-small" title="' . __('Edit Lesson', 'academy-lesson-manager') . '"><span class="dashicons dashicons-edit"></span></a>';
        
        // Add View Post button if post exists
        if ($lesson->post_id && get_post($lesson->post_id)) {
            echo '<a href="' . get_edit_post_link($lesson->post_id) . '" class="button button-small" target="_blank" title="' . __('View WordPress Post', 'academy-lesson-manager') . '"><span class="dashicons dashicons-external"></span></a>';
        } else {
            // Add Fix button if no post exists
            echo '<a href="?page=academy-manager-lessons&action=fix&id=' . $lesson->ID . '" class="button button-small" onclick="return confirm(\'' . __('Create WordPress post for this lesson?', 'academy-lesson-manager') . '\')" title="' . __('Create WordPress Post', 'academy-lesson-manager') . '"><span class="dashicons dashicons-admin-tools"></span></a>';
        }
        
        echo '<a href="?page=academy-manager-lessons&action=delete&id=' . $lesson->ID . '" class="button button-small" onclick="return confirm(\'' . __('Are you sure you want to delete this lesson?', 'academy-lesson-manager') . '\')" title="' . __('Delete Lesson', 'academy-lesson-manager') . '"><span class="dashicons dashicons-trash"></span></a>';
        
        // Add View Lesson Permalink button if post exists
        if ($lesson->post_id && get_post($lesson->post_id)) {
            echo '<a href="' . get_permalink($lesson->post_id) . '" class="button button-small" target="_blank" title="' . __('View Lesson Permalink', 'academy-lesson-manager') . '"><span class="dashicons dashicons-admin-site"></span></a>';
        }
        
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    
    /**
     * Get sortable header HTML
     */
    private function get_sortable_header($title, $column, $current_order_by, $current_order) {
        $new_order = ($current_order_by === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';
        $url = add_query_arg(array('order_by' => $column, 'order' => $new_order));
        
        $class = '';
        if ($current_order_by === $column) {
            $class = ' sorted ' . strtolower($current_order);
        }
        
        return '<a href="' . esc_url($url) . '" class="sortable' . $class . '">' . $title . '</a>';
    }
    
    /**
     * Render the edit page
     */
    private function render_edit_page($id) {
        if (empty($id)) {
            echo '<div class="notice notice-error"><p>' . __('Invalid lesson ID.', 'academy-lesson-manager') . '</p></div>';
            return;
        }
        
        $lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE ID = %d",
            $id
        ));
        
        if (!$lesson) {
            echo '<div class="notice notice-error"><p>' . __('Lesson not found.', 'academy-lesson-manager') . '</p></div>';
            return;
        }
        
        // Back button and actions
        echo '<p>';
        echo '<a href="?page=academy-manager-lessons" class="button">&larr; ' . __('Back to Lessons', 'academy-lesson-manager') . '</a> ';
        
        // Add Fix button to re-sync lesson data
        echo '<a href="?page=academy-manager-lessons&action=fix&id=' . $lesson->ID . '" class="button" onclick="return confirm(\'' . __('Re-sync this lesson to WordPress post?', 'academy-lesson-manager') . '\')" title="' . __('Re-sync Lesson Data', 'academy-lesson-manager') . '"><span class="dashicons dashicons-admin-tools"></span> ' . __('Re-sync', 'academy-lesson-manager') . '</a> ';
        
        echo '<a href="?page=academy-manager-lessons&action=delete&id=' . $lesson->ID . '" class="button" onclick="return confirm(\'' . __('Are you sure you want to delete this lesson?', 'academy-lesson-manager') . '\')" title="' . __('Delete Lesson', 'academy-lesson-manager') . '"><span class="dashicons dashicons-trash"></span> ' . __('Delete', 'academy-lesson-manager') . '</a>';
        echo '</p>';
        
        // Show success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            switch ($message) {
                case 'created':
                    echo '<div class="notice notice-success"><p>' . __('Lesson created successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'updated':
                    echo '<div class="notice notice-success"><p>' . __('Lesson updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'deleted':
                    echo '<div class="notice notice-success"><p>' . __('Lesson deleted successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'fixed':
                    echo '<div class="notice notice-success"><p>' . __('Lesson data re-synced successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        // Lesson details
        echo '<div class="alm-lesson-details">';
        echo '<h2>' . __('Edit Lesson', 'academy-lesson-manager') . '</h2>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Lesson ID', 'academy-lesson-manager') . '</th>';
        echo '<td>' . $lesson->ID . '</td>';
        echo '</tr>';
        
        // Add sync status indicator
        echo '<tr>';
        echo '<th scope="row">' . __('Sync Status', 'academy-lesson-manager') . '</th>';
        echo '<td>';
        $sync_status = $this->check_sync_status($lesson);
        if ($sync_status['status'] === 'synced') {
            echo '<span style="color: #46b450; font-weight: bold;">✓ ' . __('Synced', 'academy-lesson-manager') . '</span>';
            echo '<p class="description">' . __('WordPress post and ACF fields are up to date.', 'academy-lesson-manager') . '</p>';
        } elseif ($sync_status['status'] === 'partial') {
            echo '<span style="color: #ffb900; font-weight: bold;">⚠ ' . __('Partially Synced', 'academy-lesson-manager') . '</span>';
            echo '<p class="description">' . __('Some ACF fields may be missing or outdated.', 'academy-lesson-manager') . '</p>';
        } elseif ($sync_status['status'] === 'not_synced') {
            echo '<span style="color: #dc3232; font-weight: bold;">✗ ' . __('Not Synced', 'academy-lesson-manager') . '</span>';
            echo '<p class="description">' . __('WordPress post exists but ACF fields are missing.', 'academy-lesson-manager') . '</p>';
        } else {
            echo '<span style="color: #666; font-weight: bold;">— ' . __('No WordPress Post', 'academy-lesson-manager') . '</span>';
            echo '<p class="description">' . __('No WordPress post associated with this lesson.', 'academy-lesson-manager') . '</p>';
        }
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="post_id">' . __('Post ID', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="number" id="post_id" name="post_id" value="' . esc_attr($lesson->post_id) . '" class="small-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="collection_id">' . __('Collection', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="collection_id" name="collection_id">';
        $collections_table = $this->database->get_table_name('collections');
        $collections = $this->wpdb->get_results("SELECT ID, collection_title FROM $collections_table ORDER BY collection_title ASC");
        foreach ($collections as $collection) {
            $selected = ($collection->ID == $lesson->collection_id) ? 'selected' : '';
            echo '<option value="' . esc_attr($collection->ID) . '" ' . $selected . '>' . esc_html(stripslashes($collection->collection_title)) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="lesson_title">' . __('Lesson Title', 'academy-lesson-manager') . ' <span class="description">(required)</span></label></th>';
        echo '<td><input type="text" id="lesson_title" name="lesson_title" value="' . esc_attr(stripslashes($lesson->lesson_title)) . '" class="regular-text" required /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="lesson_description">' . __('Description', 'academy-lesson-manager') . '</label></th>';
        echo '<td><textarea id="lesson_description" name="lesson_description" rows="5" cols="50" class="large-text">' . esc_textarea(stripslashes($lesson->lesson_description)) . '</textarea></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="post_date">' . __('Release Date', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="date" id="post_date" name="post_date" value="' . esc_attr($lesson->post_date) . '" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="duration">' . __('Duration (seconds)', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="duration" name="duration" value="' . esc_attr($lesson->duration) . '" class="small-text" />';
        echo '<span class="description" style="margin-left: 10px;">(' . ALM_Helpers::format_duration($lesson->duration) . ')</span>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="song_lesson">' . __('Song Lesson', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="song_lesson" name="song_lesson">';
        $song_options = ALM_Helpers::get_yes_no_options();
        foreach ($song_options as $value => $label) {
            $selected = ($value === $lesson->song_lesson) ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="vtt">' . __('VTT File', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="text" id="vtt" name="vtt" value="' . esc_attr($lesson->vtt) . '" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="slug">' . __('Slug', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="text" id="slug" name="slug" value="' . esc_attr($lesson->slug) . '" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="membership_level">' . __('Membership Level', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="membership_level" name="membership_level" required>';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            $selected = ($level['numeric'] == $lesson->membership_level) ? 'selected' : '';
            echo '<option value="' . esc_attr($level['numeric']) . '" ' . $selected . '>' . esc_html($level['name']) . ' (' . $level['numeric'] . ') - ' . esc_html($level['description']) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Higher levels can access lower level content.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Created', 'academy-lesson-manager') . '</th>';
        echo '<td>' . ALM_Helpers::format_date($lesson->created_at) . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Updated', 'academy-lesson-manager') . '</th>';
        echo '<td>' . ALM_Helpers::format_date($lesson->updated_at) . '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="lesson_id" value="' . $lesson->ID . '" />';
        echo '<input type="hidden" name="form_action" value="update" />';
        echo '<input type="submit" class="button-primary" value="' . __('Update Lesson', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
        
        // Resources
        $this->render_lesson_resources($lesson);
        
        // Chapters in this lesson
        $this->render_lesson_chapters($id);
    }
    
    /**
     * Render the add lesson page
     */
    private function render_add_page() {
        $collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;
        
        echo '<div class="alm-lesson-details">';
        echo '<h2>' . __('Add New Lesson', 'academy-lesson-manager') . '</h2>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="post_id">' . __('Post ID', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="number" id="post_id" name="post_id" value="" class="small-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="collection_id">' . __('Collection', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="collection_id" name="collection_id" required>';
        echo '<option value="">' . __('Select a collection...', 'academy-lesson-manager') . '</option>';
        $collections_table = $this->database->get_table_name('collections');
        $collections = $this->wpdb->get_results("SELECT ID, collection_title FROM $collections_table ORDER BY collection_title ASC");
        foreach ($collections as $collection) {
            $selected = ($collection->ID == $collection_id) ? 'selected' : '';
            echo '<option value="' . esc_attr($collection->ID) . '" ' . $selected . '>' . esc_html(stripslashes($collection->collection_title)) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="lesson_title">' . __('Lesson Title', 'academy-lesson-manager') . ' <span class="description">(required)</span></label></th>';
        echo '<td><input type="text" id="lesson_title" name="lesson_title" value="" class="regular-text" required /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="lesson_description">' . __('Description', 'academy-lesson-manager') . '</label></th>';
        echo '<td><textarea id="lesson_description" name="lesson_description" rows="5" cols="50" class="large-text"></textarea></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="post_date">' . __('Release Date', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="date" id="post_date" name="post_date" value="" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="duration">' . __('Duration (seconds)', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="duration" name="duration" value="0" class="small-text" />';
        echo '<span class="description" style="margin-left: 10px;">(00:00:00)</span>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="song_lesson">' . __('Song Lesson', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="song_lesson" name="song_lesson">';
        $song_options = ALM_Helpers::get_yes_no_options();
        foreach ($song_options as $value => $label) {
            echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="vtt">' . __('VTT File', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="text" id="vtt" name="vtt" value="" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="slug">' . __('Slug', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="text" id="slug" name="slug" value="" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="membership_level">' . __('Membership Level', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="membership_level" name="membership_level" required>';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            $selected = ($level['numeric'] == 2) ? 'selected' : ''; // Default to Studio
            echo '<option value="' . esc_attr($level['numeric']) . '" ' . $selected . '>' . esc_html($level['name']) . ' (' . $level['numeric'] . ') - ' . esc_html($level['description']) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Higher levels can access lower level content.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="form_action" value="create" />';
        echo '<input type="submit" class="button-primary" value="' . __('Add Lesson', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Handle form submission
     */
    private function handle_form_submission() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $form_action = sanitize_text_field($_POST['form_action']);
        
        switch ($form_action) {
            case 'create':
                $this->create_lesson();
                break;
            case 'update':
                $this->update_lesson();
                break;
        }
    }
    
    /**
     * Create a new lesson
     */
    private function create_lesson() {
        $lesson_title = sanitize_text_field($_POST['lesson_title']);
        $collection_id = intval($_POST['collection_id']);
        
        if (empty($lesson_title) || empty($collection_id)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-lessons&action=add')));
            exit;
        }
        
        $data = array(
            'post_id' => intval($_POST['post_id']),
            'collection_id' => $collection_id,
            'lesson_title' => $lesson_title,
            'lesson_description' => sanitize_textarea_field($_POST['lesson_description']),
            'post_date' => sanitize_text_field($_POST['post_date']),
            'duration' => intval($_POST['duration']),
            'song_lesson' => sanitize_text_field($_POST['song_lesson']),
            'vtt' => sanitize_text_field($_POST['vtt']),
            'slug' => sanitize_text_field($_POST['slug']),
            'membership_level' => intval($_POST['membership_level']),
        );
        
        $result = $this->wpdb->insert($this->table_name, $data);
        
        if ($result) {
            $lesson_id = $this->wpdb->insert_id;
            
            // Sync to WordPress post
            $sync = new ALM_Post_Sync();
            $post_id = $sync->sync_lesson_to_post($lesson_id);
            
            // Check if we came from a collection page
            $collection_id_param = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;
            if ($collection_id_param) {
                wp_redirect(add_query_arg('message', 'lesson_added', admin_url('admin.php?page=academy-manager&action=edit&id=' . $collection_id_param)));
            } else {
                wp_redirect(add_query_arg('message', 'created', admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . $lesson_id)));
            }
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-lessons&action=add')));
            exit;
        }
    }
    
    /**
     * Update an existing lesson
     */
    private function update_lesson() {
        $lesson_id = intval($_POST['lesson_id']);
        $lesson_title = sanitize_text_field($_POST['lesson_title']);
        $collection_id = intval($_POST['collection_id']);
        
        if (empty($lesson_title) || empty($collection_id)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . $lesson_id)));
            exit;
        }
        
        $data = array(
            'post_id' => intval($_POST['post_id']),
            'collection_id' => $collection_id,
            'lesson_title' => $lesson_title,
            'lesson_description' => sanitize_textarea_field($_POST['lesson_description']),
            'post_date' => sanitize_text_field($_POST['post_date']),
            'duration' => intval($_POST['duration']),
            'song_lesson' => sanitize_text_field($_POST['song_lesson']),
            'vtt' => sanitize_text_field($_POST['vtt']),
            'slug' => sanitize_text_field($_POST['slug']),
            'membership_level' => intval($_POST['membership_level']),
        );
        
        $result = $this->wpdb->update($this->table_name, $data, array('ID' => $lesson_id));
        
        if ($result !== false) {
            // Sync to WordPress post
            $sync = new ALM_Post_Sync();
            $sync->sync_lesson_to_post($lesson_id);
            
            wp_redirect(add_query_arg('message', 'updated', admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . $lesson_id)));
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . $lesson_id)));
            exit;
        }
    }
    
    /**
     * Handle lesson deletion
     */
    private function handle_delete($lesson_id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Get post_id before deleting from ALM table
        $lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT post_id FROM {$this->table_name} WHERE ID = %d",
            $lesson_id
        ));
        
        $result = $this->wpdb->delete($this->table_name, array('ID' => $lesson_id));
        
        if ($result) {
            // Delete WordPress post and meta if it exists
            if ($lesson && $lesson->post_id) {
                $sync = new ALM_Post_Sync();
                $sync->delete_post_and_meta($lesson->post_id);
            }
            
            wp_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
    }
    
    /**
     * Handle fixing lesson (create WordPress post)
     */
    private function handle_fix($lesson_id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Check if lesson exists
        $lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
        
        // Check if post already exists
        if ($lesson->post_id && get_post($lesson->post_id)) {
            wp_redirect(add_query_arg('message', 'already_exists', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
        
        // Create WordPress post
        $sync = new ALM_Post_Sync();
        $post_id = $sync->sync_lesson_to_post($lesson_id);
        
        if ($post_id) {
            wp_redirect(add_query_arg('message', 'fixed', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
    }
    
    /**
     * Handle removing lesson from collection (set collection_id to 0)
     */
    private function handle_remove_from_course($lesson_id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;
        
        $result = $this->wpdb->update(
            $this->table_name, 
            array('collection_id' => 0), 
            array('ID' => $lesson_id)
        );
        
        if ($result !== false) {
            wp_redirect(add_query_arg('message', 'lesson_removed', admin_url('admin.php?page=academy-manager&action=edit&id=' . $collection_id)));
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager&action=edit&id=' . $collection_id)));
            exit;
        }
    }
    
    /**
     * Render lesson resources
     */
    private function render_lesson_resources($lesson) {
        $resources = ALM_Helpers::format_serialized_resources($lesson->resources);
        
        echo '<div class="alm-lesson-resources">';
        echo '<h3>' . __('Resources', 'academy-lesson-manager') . ' <button type="button" class="button button-small" id="alm-add-resource-btn">' . __('Add Resource', 'academy-lesson-manager') . '</button></h3>';
        
        if (empty($resources)) {
            echo '<p>' . __('No resources found for this lesson.', 'academy-lesson-manager') . '</p>';
        } else {
            echo '<ul class="alm-resources-list">';
            foreach ($resources as $resource) {
                // Get filename from attachment if available
                $display_name = $resource['url'];
                if (!empty($resource['attachment_id'])) {
                    $attachment = get_post($resource['attachment_id']);
                    if ($attachment) {
                        $display_name = $attachment->post_title ?: basename($resource['url']);
                    }
                }
                
                echo '<li>';
                echo '<strong>' . esc_html(ucfirst($resource['type'])) . ':</strong> ';
                echo '<a href="' . esc_url($resource['display_url']) . '" target="_blank">' . esc_html($display_name) . '</a>';
                echo ' <a href="#" class="alm-delete-resource" data-type="' . esc_attr($resource['type']) . '" data-lesson-id="' . esc_attr($lesson->ID) . '" style="color: #dc3232; margin-left: 10px;">' . __('Delete', 'academy-lesson-manager') . '</a>';
                echo '</li>';
            }
            echo '</ul>';
        }
        
        // Add resource form (hidden by default)
        echo '<div id="alm-add-resource-form" style="display: none; margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">';
        echo '<h4>' . __('Add New Resource', 'academy-lesson-manager') . '</h4>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row"><label for="alm-resource-type">' . __('Resource Type', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="alm-resource-type" name="resource_type">';
        echo '<option value="">' . __('Choose...', 'academy-lesson-manager') . '</option>';
        echo '<option value="sheet_music">Sheet Music</option>';
        echo '<option value="ireal">iRealPro</option>';
        echo '<option value="jam">Backing Track (mp3)</option>';
        echo '<option value="zip">Zip File</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="alm-resource-url">' . __('Resource File', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<div id="alm-resource-file-wrapper">';
        echo '<input type="hidden" id="alm-resource-attachment-id" name="attachment_id" value="" />';
        echo '<input type="text" id="alm-resource-url" name="resource_url" class="regular-text" placeholder="Click to select file from media library" readonly style="background-color: #f0f0f1;" />';
        echo '<button type="button" class="button" id="alm-select-media-btn" style="margin-left: 10px;">' . __('Select from Media Library', 'academy-lesson-manager') . '</button>';
        echo '<button type="button" class="button" id="alm-clear-media-btn" style="margin-left: 5px; display: none;">' . __('Clear', 'academy-lesson-manager') . '</button>';
        echo '</div>';
        echo '<p class="description">' . __('Select a file from the media library or enter a URL manually.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '<p class="submit">';
        echo '<button type="button" class="button button-primary" id="alm-save-resource-btn">' . __('Add Resource', 'academy-lesson-manager') . '</button> ';
        echo '<button type="button" class="button" id="alm-cancel-resource-btn">' . __('Cancel', 'academy-lesson-manager') . '</button>';
        echo '</p>';
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * Render chapters in a lesson
     */
    private function render_lesson_chapters($lesson_id) {
        $chapters_table = $this->database->get_table_name('chapters');
        
        $chapters = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$chapters_table} WHERE lesson_id = %d ORDER BY menu_order ASC",
            $lesson_id
        ));
        
        echo '<div class="alm-lesson-chapters">';
        echo '<h3>' . __('Chapters in This Lesson', 'academy-lesson-manager') . ' <a href="?page=academy-manager-chapters&action=add&lesson_id=' . $lesson_id . '" class="button button-small">' . __('Add Chapter', 'academy-lesson-manager') . '</a></h3>';
        
        if (empty($chapters)) {
            echo '<p>' . __('No chapters found in this lesson.', 'academy-lesson-manager') . '</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped alm-chapter-reorder">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">' . __('Drag', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('ID', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('Order', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('Title', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('Vimeo', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('YouTube', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('Bunny', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('Duration', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('Free', 'academy-lesson-manager') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($chapters as $chapter) {
                $background = ($chapter->vimeo_id == 0 && empty($chapter->youtube_id) && empty($chapter->bunny_url)) ? 'background-color: #ffebee;' : '';
                echo '<tr data-chapter-id="' . $chapter->ID . '" style="' . $background . '">';
                echo '<td class="chapter-drag-handle" style="cursor: move; text-align: center;">⋮⋮</td>';
                echo '<td>' . $chapter->ID . '</td>';
                echo '<td class="chapter-order">' . $chapter->menu_order . '</td>';
                echo '<td><a href="?page=academy-manager-chapters&action=edit&id=' . $chapter->ID . '">' . esc_html(stripslashes($chapter->chapter_title)) . '</a></td>';
                echo '<td>' . ($chapter->vimeo_id && $chapter->vimeo_id > 0 ? '<span style="color: #46b450; font-weight: bold;">' . __('Yes', 'academy-lesson-manager') . '</span>' : '<span style="color: #dc3232;">' . __('No', 'academy-lesson-manager') . '</span>') . '</td>';
                echo '<td>' . (!empty($chapter->youtube_id) ? '<span style="color: #46b450; font-weight: bold;">' . __('Yes', 'academy-lesson-manager') . '</span>' : '<span style="color: #dc3232;">' . __('No', 'academy-lesson-manager') . '</span>') . '</td>';
                echo '<td>' . (!empty($chapter->bunny_url) ? '<span style="color: #46b450; font-weight: bold;">' . __('Yes', 'academy-lesson-manager') . '</span>' : '<span style="color: #dc3232;">' . __('No', 'academy-lesson-manager') . '</span>') . '</td>';
                echo '<td>' . ALM_Helpers::format_duration($chapter->duration) . '</td>';
                echo '<td>' . ($chapter->free === 'y' ? __('Yes', 'academy-lesson-manager') : __('No', 'academy-lesson-manager')) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            
            echo '<p class="description">' . __('Drag chapters by the ⋮⋮ handle to reorder them.', 'academy-lesson-manager') . '</p>';
        }
        
        echo '</div>';
    }
    
    
    /**
     * Handle bulk actions
     */
    private function handle_bulk_action() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $action = sanitize_text_field($_POST['bulk_action']);
        
        if ($action === 'update_membership') {
            $this->handle_bulk_membership_update();
        }
    }
    
    /**
     * Handle bulk membership update
     */
    private function handle_bulk_membership_update() {
        $lesson_ids = isset($_POST['lesson']) ? array_map('intval', $_POST['lesson']) : array();
        $membership_level = isset($_POST['bulk_membership_level']) ? intval($_POST['bulk_membership_level']) : 0;
        
        // Debug: Log what we received
        error_log('ALMD Bulk Action Debug:');
        error_log('ALMD POST data: ' . print_r($_POST, true));
        error_log('ALMD Lesson IDs: ' . print_r($lesson_ids, true));
        error_log('ALMD Membership Level: ' . $membership_level);
        
        // Check if lessons were selected
        if (empty($lesson_ids)) {
            wp_redirect(add_query_arg('message', 'no_lessons_selected', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
        
        // Check if membership level was selected
        if ($membership_level === 0) {
            wp_redirect(add_query_arg('message', 'no_level_selected', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
        
        $updated = 0;
        
        foreach ($lesson_ids as $lesson_id) {
            $result = $this->wpdb->update(
                $this->table_name,
                array('membership_level' => $membership_level),
                array('ID' => $lesson_id),
                array('%d'),
                array('%d')
            );
            
            if ($result !== false) {
                $updated++;
            }
        }
        
        wp_redirect(add_query_arg('message', 'bulk_updated', admin_url('admin.php?page=academy-manager-lessons')));
        exit;
    }
    
    /**
     * Handle syncing all lessons
     */
    private function handle_sync_all() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $sync = new ALM_Post_Sync();
        
        // Get all lessons
        $lessons = $this->wpdb->get_results("SELECT ID FROM {$this->table_name}");
        
        $synced = 0;
        foreach ($lessons as $lesson) {
            $result = $sync->sync_lesson_to_post($lesson->ID);
            if ($result) {
                $synced++;
            }
        }
        
        wp_redirect(add_query_arg('message', 'synced', admin_url('admin.php?page=academy-manager-lessons')));
        exit;
    }
    
    /**
     * Check sync status of lesson with WordPress post
     */
    private function check_sync_status($lesson) {
        if (!$lesson->post_id) {
            return array('status' => 'no_post');
        }
        
        $post = get_post($lesson->post_id);
        if (!$post) {
            return array('status' => 'no_post');
        }
        
        // Check if ACF function exists
        if (!function_exists('get_field')) {
            return array('status' => 'no_acf');
        }
        
        // Check critical ACF fields
        $alm_lesson_id = get_field('alm_lesson_id', $lesson->post_id);
        $alm_collection_id = get_field('alm_collection_id', $lesson->post_id);
        $lesson_duration = get_field('lesson_duration', $lesson->post_id);
        $lesson_membership_level = get_field('lesson_membership_level', $lesson->post_id);
        
        // Count missing fields
        $missing_fields = 0;
        if ($alm_lesson_id != $lesson->ID) $missing_fields++;
        if ($alm_collection_id != $lesson->collection_id) $missing_fields++;
        if ($lesson_duration != $lesson->duration) $missing_fields++;
        if ($lesson_membership_level != $lesson->membership_level) $missing_fields++;
        
        if ($missing_fields === 0) {
            return array('status' => 'synced');
        } elseif ($missing_fields <= 2) {
            return array('status' => 'partial');
        } else {
            return array('status' => 'not_synced');
        }
    }
    
    /**
     * Get lesson count by membership level
     */
    private function get_lesson_count_by_membership($membership_level) {
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE membership_level = %d",
            $membership_level
        ));
        return intval($count);
    }
}
