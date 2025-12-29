<?php
/**
 * ALM Admin Credit Log Class
 * 
 * Handles credit log CRUD operations for Academy Lesson Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Credit_Log {
    
    private $wpdb;
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = 'academy_user_credit_log'; // No prefix
    }
    
    /**
     * Render the credit log admin page
     */
    public function render_page() {
        // Handle form submissions first
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['save_credit_log'])) {
                $this->save_credit_log();
                return;
            } elseif (isset($_POST['delete_credit_log'])) {
                $this->delete_credit_log();
                return;
            } elseif (isset($_POST['bulk_delete_credit_log'])) {
                $this->bulk_delete_credit_log();
                return;
            }
        }
        
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        echo '<div class="wrap">';
        $this->render_navigation_buttons('credit-log');
        echo '<h1>' . __('Credit Log', 'academy-lesson-manager') . ' <a href="?page=academy-manager-credit-log&action=add" class="page-title-action">' . __('Add New', 'academy-lesson-manager') . '</a></h1>';
        
        // Show messages
        if (isset($_GET['message'])) {
            $this->show_message($_GET['message']);
        }
        
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
        echo '<a href="?page=academy-manager-chapters" class="button ' . ($current_page === 'chapters' ? 'button-primary' : '') . '">' . __('Chapters', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-teachers" class="button ' . ($current_page === 'teachers' ? 'button-primary' : '') . '">' . __('Teachers', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-credit-log" class="button ' . ($current_page === 'credit-log' ? 'button-primary' : '') . '">' . __('Credit Log', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-settings" class="button ' . ($current_page === 'settings' ? 'button-primary' : '') . '" style="margin-left: 10px;">' . __('Settings', 'academy-lesson-manager') . '</a>';
        echo '</div>';
    }
    
    /**
     * Show success/error messages
     */
    private function show_message($message) {
        $class = 'notice-success';
        $text = '';
        
        $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
        
        switch ($message) {
            case 'created':
                $text = __('Credit log entry created successfully. You can add another lesson for this user below.', 'academy-lesson-manager');
                break;
            case 'created_multiple':
                $text = sprintf(__('%d credit log entries created successfully. You can add more lessons for this user below.', 'academy-lesson-manager'), $count);
                break;
            case 'updated':
                $text = __('Credit log entry updated successfully.', 'academy-lesson-manager');
                break;
            case 'deleted':
                $text = __('Credit log entry deleted successfully.', 'academy-lesson-manager');
                break;
            case 'bulk_deleted':
                $text = sprintf(__('%d credit log entries deleted successfully.', 'academy-lesson-manager'), $count);
                break;
            case 'error':
                $class = 'notice-error';
                $text = isset($_GET['message_text']) ? urldecode($_GET['message_text']) : __('An error occurred. Please try again.', 'academy-lesson-manager');
                break;
        }
        
        if ($text) {
            echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($text) . '</p></div>';
        }
    }
    
    /**
     * Render list page with search and pagination
     */
    private function render_list_page() {
        // Get search/filter parameters
        $search_user = isset($_GET['search_user']) ? sanitize_text_field($_GET['search_user']) : '';
        $search_lesson = isset($_GET['search_lesson']) ? sanitize_text_field($_GET['search_lesson']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($paged - 1) * $per_page;
        
        // Build WHERE clause
        $where = array('1=1');
        $where_values = array();
        
        if (!empty($search_user)) {
            $where[] = "(u.user_login LIKE %s OR u.user_email LIKE %s OR u.display_name LIKE %s)";
            $search_term = '%' . $this->wpdb->esc_like($search_user) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if (!empty($search_lesson)) {
            $where[] = "(COALESCE(p.post_title, p_old.post_title) LIKE %s)";
            $where_values[] = '%' . $this->wpdb->esc_like($search_lesson) . '%';
        }
        
        if (!empty($date_from)) {
            $where[] = "cl.timestamp >= %d";
            $where_values[] = strtotime($date_from);
        }
        
        if (!empty($date_to)) {
            $where[] = "cl.timestamp <= %d";
            $where_values[] = strtotime($date_to . ' 23:59:59');
        }
        
        $where_sql = implode(' AND ', $where);
        
        // Get total count - use backticks to prevent prefix addition
        // Check both wp_posts and wp_posts_old for old system post_ids
        $posts_old_table = $this->wpdb->prefix . 'posts_old';
        $count_query = "SELECT COUNT(*) 
                        FROM `{$this->table_name}` cl
                        LEFT JOIN {$this->wpdb->users} u ON cl.user_id = u.ID
                        LEFT JOIN {$this->wpdb->posts} p ON cl.post_id = p.ID
                        LEFT JOIN `{$posts_old_table}` p_old ON cl.post_id = p_old.ID
                        WHERE {$where_sql}";
        
        if (!empty($where_values)) {
            $count_query = $this->wpdb->prepare($count_query, $where_values);
        }
        
        $total_items = $this->wpdb->get_var($count_query);
        $total_pages = ceil($total_items / $per_page);
        
        // Get items - use backticks to prevent prefix addition
        // Check both wp_posts and wp_posts_old for old system post_ids
        $posts_old_table = $this->wpdb->prefix . 'posts_old';
        $query = "SELECT cl.*, 
                         u.user_login, u.user_email, u.display_name,
                         COALESCE(p.post_title, p_old.post_title) as post_title,
                         CASE WHEN p.ID IS NULL AND p_old.ID IS NOT NULL THEN 1 ELSE 0 END as is_old_system
                  FROM `{$this->table_name}` cl
                  LEFT JOIN {$this->wpdb->users} u ON cl.user_id = u.ID
                  LEFT JOIN {$this->wpdb->posts} p ON cl.post_id = p.ID
                  LEFT JOIN `{$posts_old_table}` p_old ON cl.post_id = p_old.ID
                  WHERE {$where_sql}
                  ORDER BY cl.timestamp DESC
                  LIMIT %d OFFSET %d";
        
        $query_values = array_merge($where_values, array($per_page, $offset));
        $query = $this->wpdb->prepare($query, $query_values);
        
        $items = $this->wpdb->get_results($query);
        
        // Render search form
        $this->render_search_form($search_user, $search_lesson, $date_from, $date_to);
        
        // Render bulk actions
        $this->render_bulk_actions();
        
        // Render table
        echo '<form method="post" action="" id="bulk-actions-form">';
        wp_nonce_field('alm_bulk_credit_log_action', 'alm_bulk_credit_log_nonce');
        
        // Preserve search/filter values in hidden fields
        if (!empty($search_user)) {
            echo '<input type="hidden" name="preserve_search_user" value="' . esc_attr($search_user) . '" />';
        }
        if (!empty($search_lesson)) {
            echo '<input type="hidden" name="preserve_search_lesson" value="' . esc_attr($search_lesson) . '" />';
        }
        if (!empty($date_from)) {
            echo '<input type="hidden" name="preserve_date_from" value="' . esc_attr($date_from) . '" />';
        }
        if (!empty($date_to)) {
            echo '<input type="hidden" name="preserve_date_to" value="' . esc_attr($date_to) . '" />';
        }
        if ($paged > 1) {
            echo '<input type="hidden" name="preserve_paged" value="' . esc_attr($paged) . '" />';
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>';
        echo '<th scope="col" class="manage-column column-id">' . __('ID', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-user">' . __('User', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-post">' . __('Lesson', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-type">' . __('Type', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-date">' . __('Date', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-actions">' . __('Actions', 'academy-lesson-manager') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($items)) {
            echo '<tr><td colspan="7" class="no-items">' . __('No credit log entries found.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            foreach ($items as $item) {
                $this->render_item_row($item);
            }
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</form>';
        
        // Add JavaScript for select all functionality
        $this->render_bulk_actions_script();
        
        // Render pagination
        if ($total_pages > 1) {
            $page_links = paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total_pages,
                'current' => $paged
            ));
            
            if ($page_links) {
                echo '<div class="tablenav"><div class="tablenav-pages">' . $page_links . '</div></div>';
            }
        }
    }
    
    /**
     * Render search form
     */
    private function render_search_form($search_user, $search_lesson, $date_from, $date_to) {
        echo '<div class="alm-search-form" style="background: #fff; padding: 15px; margin-bottom: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
        echo '<form method="get" action="">';
        echo '<input type="hidden" name="page" value="academy-manager-credit-log" />';
        
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">';
        
        // User search
        echo '<div>';
        echo '<label for="search_user" style="display: block; margin-bottom: 5px; font-weight: 600;">' . __('Search User', 'academy-lesson-manager') . '</label>';
        echo '<input type="text" id="search_user" name="search_user" value="' . esc_attr($search_user) . '" class="regular-text" placeholder="' . __('Name or email', 'academy-lesson-manager') . '" />';
        echo '</div>';
        
        // Lesson search
        echo '<div>';
        echo '<label for="search_lesson" style="display: block; margin-bottom: 5px; font-weight: 600;">' . __('Search Lesson', 'academy-lesson-manager') . '</label>';
        echo '<input type="text" id="search_lesson" name="search_lesson" value="' . esc_attr($search_lesson) . '" class="regular-text" placeholder="' . __('Lesson title', 'academy-lesson-manager') . '" />';
        echo '</div>';
        
        // Date from
        echo '<div>';
        echo '<label for="date_from" style="display: block; margin-bottom: 5px; font-weight: 600;">' . __('Date From', 'academy-lesson-manager') . '</label>';
        echo '<input type="date" id="date_from" name="date_from" value="' . esc_attr($date_from) . '" class="regular-text" />';
        echo '</div>';
        
        // Date to
        echo '<div>';
        echo '<label for="date_to" style="display: block; margin-bottom: 5px; font-weight: 600;">' . __('Date To', 'academy-lesson-manager') . '</label>';
        echo '<input type="date" id="date_to" name="date_to" value="' . esc_attr($date_to) . '" class="regular-text" />';
        echo '</div>';
        
        // Buttons
        echo '<div>';
        echo '<input type="submit" class="button button-primary" value="' . __('Search', 'academy-lesson-manager') . '" />';
        if ($search_user || $search_lesson || $date_from || $date_to) {
            echo ' <a href="?page=academy-manager-credit-log" class="button">' . __('Reset', 'academy-lesson-manager') . '</a>';
        }
        echo '</div>';
        
        echo '</div>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render bulk actions section
     */
    private function render_bulk_actions() {
        echo '<div class="alm-bulk-actions" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">';
        echo '<h3>' . __('Bulk Actions', 'academy-lesson-manager') . '</h3>';
        echo '<p>';
        echo '<button type="submit" form="bulk-actions-form" class="button button-large" name="bulk_delete_credit_log" style="color: #a00; border-color: #dc3232;" onclick="return confirm(\'' . esc_js(__('Are you sure you want to DELETE the selected credit log entries? This action cannot be undone.', 'academy-lesson-manager')) . '\');">' . __('Delete Selected Entries', 'academy-lesson-manager') . '</button>';
        echo '</p>';
        echo '<p class="description">' . __('Select one or more entries from the list below, then click "Delete Selected Entries" to remove them.', 'academy-lesson-manager') . '</p>';
        echo '</div>';
    }
    
    /**
     * Render a single item row
     */
    private function render_item_row($item) {
        $user_name = !empty($item->display_name) ? $item->display_name : $item->user_login;
        $user_email = $item->user_email;
        $lesson_title = !empty($item->post_title) ? $item->post_title : __('Post ID: ', 'academy-lesson-manager') . $item->post_id;
        $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $item->timestamp);
        $is_old_system = isset($item->is_old_system) && $item->is_old_system == 1;
        
        echo '<tr>';
        echo '<th scope="row" class="check-column"><input type="checkbox" name="credit_log_ids[]" value="' . esc_attr($item->ID) . '" class="alm-credit-log-checkbox" /></th>';
        echo '<td>' . esc_html($item->ID) . '</td>';
        echo '<td>';
        echo '<strong>' . esc_html($user_name) . '</strong><br>';
        echo '<small style="color: #666;">' . esc_html($user_email) . '</small><br>';
        echo '<small style="color: #999;">ID: ' . esc_html($item->user_id) . '</small>';
        echo '</td>';
        echo '<td>';
        if ($item->post_id) {
            if ($is_old_system) {
                // Old system post - no edit link available
                echo '<span style="color: #666;">' . esc_html($lesson_title) . '</span>';
                echo ' <span style="color: #999; font-size: 11px;">(' . __('Old System', 'academy-lesson-manager') . ')</span><br>';
            } else {
                // Current system post - show edit link
                echo '<a href="' . get_edit_post_link($item->post_id) . '" target="_blank">' . esc_html($lesson_title) . '</a><br>';
            }
            echo '<small style="color: #999;">ID: ' . esc_html($item->post_id) . '</small>';
        } else {
            echo '<span style="color: #999;">' . __('N/A', 'academy-lesson-manager') . '</span>';
        }
        echo '</td>';
        echo '<td>' . esc_html($item->type ?: 'lesson') . '</td>';
        echo '<td>' . esc_html($date) . '</td>';
        echo '<td>';
        echo '<a href="?page=academy-manager-credit-log&action=edit&id=' . $item->ID . '" class="button button-small">' . __('Edit', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-credit-log&action=add&user_id=' . $item->user_id . '" class="button button-small">' . __('Add', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-credit-log&action=delete&id=' . $item->ID . '" class="button button-small" onclick="return confirm(\'' . esc_js(__('Are you sure you want to delete this entry?', 'academy-lesson-manager')) . '\');">' . __('Delete', 'academy-lesson-manager') . '</a>';
        echo '</td>';
        echo '</tr>';
    }
    
    /**
     * Render add page
     */
    private function render_add_page() {
        echo '<p><a href="?page=academy-manager-credit-log" class="button">&larr; ' . __('Back to Credit Log', 'academy-lesson-manager') . '</a></p>';
        $this->render_credit_log_form();
    }
    
    /**
     * Render edit page
     */
    private function render_edit_page($id) {
        if (empty($id)) {
            echo '<div class="notice notice-error"><p>' . __('Invalid entry ID.', 'academy-lesson-manager') . '</p></div>';
            return;
        }
        
        $item = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM `{$this->table_name}` WHERE ID = %d",
            $id
        ));
        
        if (!$item) {
            echo '<div class="notice notice-error"><p>' . __('Entry not found.', 'academy-lesson-manager') . '</p></div>';
            return;
        }
        
        echo '<p><a href="?page=academy-manager-credit-log" class="button">&larr; ' . __('Back to Credit Log', 'academy-lesson-manager') . '</a></p>';
        $this->render_credit_log_form($item);
    }
    
    /**
     * Render credit log form
     */
    private function render_credit_log_form($item = null) {
        $is_edit = !empty($item);
        $action_url = $is_edit 
            ? admin_url('admin.php?page=academy-manager-credit-log&action=edit&id=' . $item->ID)
            : admin_url('admin.php?page=academy-manager-credit-log&action=add');
        
        echo '<form method="post" action="' . esc_url($action_url) . '" id="credit_log_form">';
        wp_nonce_field('alm_credit_log_action', 'alm_credit_log_nonce');
        
        // Always include hidden input to ensure save action is detected
        echo '<input type="hidden" name="save_credit_log" value="1" />';
        
        if ($is_edit) {
            echo '<input type="hidden" name="credit_log_id" value="' . esc_attr($item->ID) . '" />';
        }
        
        echo '<table class="form-table">';
        echo '<tbody>';
        
        // User selector
        echo '<tr>';
        echo '<th scope="row"><label for="user_id">' . __('User', 'academy-lesson-manager') . ' <span class="required">*</span></label></th>';
        echo '<td>';
        $prefill_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : ($is_edit ? $item->user_id : 0);
        echo '<input type="text" id="user_search" name="user_search" value="" class="regular-text" placeholder="' . __('Search by name or email...', 'academy-lesson-manager') . '" autocomplete="off" />';
        echo '<input type="hidden" id="user_id" name="user_id" value="' . esc_attr($prefill_user_id) . '" required />';
        echo '<div id="user_search_results" style="margin-top: 10px; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; display: none;"></div>';
        echo '<div id="selected_user" style="margin-top: 10px; padding: 10px; background: #f0f0f1; border-radius: 4px; display: none;"></div>';
        if ($prefill_user_id) {
            $user = get_user_by('ID', $prefill_user_id);
            if ($user) {
                echo '<script type="text/javascript">';
                echo 'jQuery(document).ready(function($) {';
                echo 'selectUser(' . $user->ID . ', ' . json_encode($user->display_name) . ', ' . json_encode($user->user_email) . ');';
                echo '});';
                echo '</script>';
            }
        }
        echo '<p class="description">' . __('Search for a user by name or email address.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Post/Lesson selector - multiple selection for add, single for edit
        echo '<tr>';
        echo '<th scope="row"><label for="post_id">' . __('Lesson/Post', 'academy-lesson-manager') . ' <span class="required">*</span></label></th>';
        echo '<td>';
        if ($is_edit) {
            // Edit mode: single selection
            $prefill_post_id = $item->post_id;
            echo '<input type="text" id="post_search" name="post_search" value="" class="regular-text" placeholder="' . __('Search by lesson title...', 'academy-lesson-manager') . '" autocomplete="off" />';
            echo '<input type="hidden" id="post_id" name="post_id" value="' . esc_attr($prefill_post_id) . '" required />';
            echo '<div id="post_search_results" style="margin-top: 10px; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; display: none;"></div>';
            echo '<div id="selected_post" style="margin-top: 10px; padding: 10px; background: #f0f0f1; border-radius: 4px; display: none;"></div>';
            if ($prefill_post_id) {
                $post = get_post($prefill_post_id);
                if ($post) {
                    echo '<script type="text/javascript">';
                    echo 'jQuery(document).ready(function($) {';
                    echo 'selectPost(' . $post->ID . ', ' . json_encode($post->post_title) . ');';
                    echo '});';
                    echo '</script>';
                }
            }
            echo '<p class="description">' . __('Search for a lesson or post by title.', 'academy-lesson-manager') . '</p>';
        } else {
            // Add mode: multiple selection with collection search
            echo '<div style="margin-bottom: 10px;">';
            echo '<label style="margin-right: 15px;"><input type="radio" name="search_type" value="lesson" checked /> ' . __('Search Lessons', 'academy-lesson-manager') . '</label>';
            echo '<label><input type="radio" name="search_type" value="collection" /> ' . __('Search Collections', 'academy-lesson-manager') . '</label>';
            echo '</div>';
            echo '<input type="text" id="post_search" name="post_search" value="" class="regular-text" placeholder="' . __('Search by lesson or collection title...', 'academy-lesson-manager') . '" autocomplete="off" />';
            echo '<input type="hidden" id="post_ids" name="post_ids" value="" />';
            echo '<div id="post_search_results" style="margin-top: 10px; max-height: 300px; overflow-y: auto; border: 1px solid #ddd; display: none;"></div>';
            echo '<div id="selected_posts" style="margin-top: 10px; min-height: 50px; padding: 10px; background: #f0f0f1; border-radius: 4px; display: none;"></div>';
            echo '<p class="description">' . __('Search for lessons or collections. Click a lesson to add it, or click a collection to add all lessons in that collection.', 'academy-lesson-manager') . '</p>';
        }
        echo '</td>';
        echo '</tr>';
        
        // Type
        echo '<tr>';
        echo '<th scope="row"><label for="type">' . __('Type', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="type" name="type" class="regular-text">';
        $types = array('lesson' => __('Lesson', 'academy-lesson-manager'), 'class_event' => __('Class Event', 'academy-lesson-manager'));
        $current_type = $is_edit ? ($item->type ?: 'lesson') : 'lesson';
        foreach ($types as $value => $label) {
            $selected = ($current_type === $value) ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Type of purchase.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Timestamp
        echo '<tr>';
        echo '<th scope="row"><label for="timestamp">' . __('Date & Time', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        $timestamp = $is_edit ? $item->timestamp : current_time('timestamp');
        $date_value = date('Y-m-d', $timestamp);
        $time_value = date('H:i:s', $timestamp);
        echo '<input type="date" id="timestamp_date" name="timestamp_date" value="' . esc_attr($date_value) . '" class="regular-text" style="width: 150px;" /> ';
        echo '<input type="time" id="timestamp_time" name="timestamp_time" value="' . esc_attr($time_value) . '" class="regular-text" style="width: 150px;" />';
        echo '<p class="description">' . __('Date and time of the purchase. Defaults to current time.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="submit" class="button button-primary" value="' . ($is_edit ? __('Update Entry', 'academy-lesson-manager') : __('Add Entry', 'academy-lesson-manager')) . '" id="credit_log_submit_btn" />';
        echo ' <a href="?page=academy-manager-credit-log" class="button">' . __('Cancel', 'academy-lesson-manager') . '</a>';
        echo '</p>';
        
        echo '</form>';
        
        
        // Add JavaScript for search functionality
        $this->render_search_scripts();
    }
    
    /**
     * Render JavaScript for user and post search
     */
    private function render_search_scripts() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var userSearchTimeout, postSearchTimeout;
            
            // Helper function to escape HTML for use in attributes
            function escapeHtmlAttr(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }
            
            // Helper function to escape HTML for display
            function escapeHtml(str) {
                if (!str) return '';
                var div = $('<div>').text(str);
                return div.html();
            }
            
            // User search
            $('#user_search').on('input', function() {
                var query = $(this).val();
                if (query.length < 2) {
                    $('#user_search_results').hide().empty();
                    return;
                }
                
                clearTimeout(userSearchTimeout);
                userSearchTimeout = setTimeout(function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'alm_search_users',
                            query: query,
                            nonce: '<?php echo wp_create_nonce('alm_search_users'); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data.length > 0) {
                                var html = '<ul style="list-style: none; margin: 0; padding: 0;">';
                                response.data.forEach(function(user) {
                                    html += '<li style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;" class="alm-user-result" data-user-id="' + user.ID + '" data-user-name="' + escapeHtmlAttr(user.display_name) + '" data-user-email="' + escapeHtmlAttr(user.user_email) + '">';
                                    html += '<strong>' + escapeHtml(user.display_name) + '</strong> (' + escapeHtml(user.user_email) + ')';
                                    html += '</li>';
                                });
                                html += '</ul>';
                                $('#user_search_results').html(html).show();
                            } else {
                                $('#user_search_results').html('<p style="padding: 10px;">No users found.</p>').show();
                            }
                        }
                    });
                }, 300);
            });
            
            // Post search
            $('#post_search').on('input', function() {
                var query = $(this).val();
                if (query.length < 2) {
                    $('#post_search_results').hide().empty();
                    return;
                }
                
                clearTimeout(postSearchTimeout);
                postSearchTimeout = setTimeout(function() {
                    var searchType = $('input[name="search_type"]:checked').val() || 'lesson';
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'alm_search_posts',
                            query: query,
                            search_type: searchType,
                            nonce: '<?php echo wp_create_nonce('alm_search_posts'); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data.length > 0) {
                                var html = '<ul style="list-style: none; margin: 0; padding: 0;">';
                                response.data.forEach(function(post) {
                                    // Check if already selected (for multiple mode)
                                    var isEditMode = $('#post_id').length > 0;
                                    var displayTitle = post.display_title || post.post_title;
                                    var postId = post.ID || 0;
                                    
                                    if (isEditMode) {
                                        html += '<li style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;" class="alm-post-result" data-post-id="' + postId + '" data-post-title="' + escapeHtmlAttr(displayTitle) + '">';
                                        html += '<strong>' + escapeHtml(displayTitle) + '</strong>';
                                        if (post.collection_title) {
                                            html += ' <small style="color: #666;">[' + escapeHtml(post.collection_title) + ']</small>';
                                        }
                                        if (post.lesson_id) {
                                            html += ' <small style="color: #666;">(Lesson ID: ' + post.lesson_id + ')</small>';
                                        }
                                        html += '</li>';
                                    } else {
                                        // Multiple selection mode
                                        if (post.is_collection && post.post_ids && post.post_ids.length > 0) {
                                            // Collection - add all lessons
                                            // Store collection data in a data attribute to avoid escaping issues
                                            var collectionData = {
                                                post_ids: post.post_ids,
                                                collection_title: post.collection_title,
                                                lesson_count: post.lesson_count,
                                                lessons_data: post.lessons_data || []
                                            };
                                            html += '<li style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee; background: #e7f3ff; border-left: 4px solid #0073aa;" class="alm-collection-result" data-collection="' + escapeHtmlAttr(encodeURIComponent(JSON.stringify(collectionData))) + '">';
                                            html += '<strong style="color: #0073aa;">📁 ' + escapeHtml(post.collection_title) + '</strong>';
                                            html += ' <span style="color: #0073aa; font-size: 12px;">(' + post.lesson_count + ' lessons - Click to add all)</span>';
                                            html += '</li>';
                                        } else {
                                            // Single lesson
                                            html += '<li style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee; background: #fff;" class="alm-post-result" data-post-id="' + postId + '" data-post-title="' + escapeHtmlAttr(displayTitle) + '">';
                                            html += '<strong>' + escapeHtml(displayTitle) + '</strong>';
                                            if (post.collection_title) {
                                                html += ' <small style="color: #666;">[' + escapeHtml(post.collection_title) + ']</small>';
                                            }
                                            if (post.lesson_id) {
                                                html += ' <small style="color: #666;">(Lesson ID: ' + post.lesson_id + ')</small>';
                                            }
                                            html += ' <span style="color: #0073aa; font-size: 12px;">[Click to add]</span>';
                                            html += '</li>';
                                        }
                                    }
                                });
                                html += '</ul>';
                                $('#post_search_results').html(html).show();
                            } else {
                                $('#post_search_results').html('<p style="padding: 10px;">No ' + searchType + 's found.</p>').show();
                            }
                        }
                    });
                }, 300);
            });
            
            // Update search when search type changes
            $('input[name="search_type"]').on('change', function() {
                var query = $('#post_search').val();
                if (query.length >= 2) {
                    $('#post_search').trigger('input');
                }
            });
            
            // Handle user result clicks using event delegation
            $(document).on('click', '.alm-user-result', function() {
                var userId = $(this).data('user-id');
                var userName = $(this).data('user-name');
                var userEmail = $(this).data('user-email');
                selectUser(userId, userName, userEmail);
            });
            
            // Handle post result clicks using event delegation (edit mode)
            $(document).on('click', '.alm-post-result', function() {
                var isEditMode = $('#post_id').length > 0;
                var postId = $(this).data('post-id');
                var postTitle = $(this).data('post-title');
                
                if (isEditMode) {
                    selectPost(postId, postTitle);
                } else {
                    addPost(postId, postTitle);
                }
            });
            
            // Handle collection result clicks using event delegation
            $(document).on('click', '.alm-collection-result', function() {
                addCollectionFromData(this);
            });
            
            // Clear user
            $('#clear_user').on('click', function(e) {
                e.preventDefault();
                $('#user_id').val('');
                $('#user_search').val('');
                $('#selected_user').hide();
                $('#user_search').show();
            });
            
            // Clear post
            $('#clear_post').on('click', function(e) {
                e.preventDefault();
                $('#post_id').val('');
                $('#post_search').val('');
                $('#selected_post').hide();
                $('#post_search').show();
            });
        });
        
        function selectUser(userId, displayName, email) {
            jQuery('#user_id').val(userId);
            jQuery('#user_search').val('');
            jQuery('#user_search_results').hide().empty();
            var html = '<strong>' + jQuery('<div>').text(displayName).html() + '</strong> (' + jQuery('<div>').text(email).html() + ')';
            html += ' <a href="#" id="clear_user" style="color: #d63638; margin-left: 10px;"><?php echo esc_js(__('Change', 'academy-lesson-manager')); ?></a>';
            jQuery('#selected_user').html(html).show();
            jQuery('#user_search').hide();
            
            // Re-bind clear button
            jQuery('#clear_user').off('click').on('click', function(e) {
                e.preventDefault();
                jQuery('#user_id').val('');
                jQuery('#user_search').val('');
                jQuery('#selected_user').hide().empty();
                jQuery('#user_search').show();
            });
        }
        
        // Single selection (edit mode)
        function selectPost(postId, postTitle) {
            jQuery('#post_id').val(postId);
            jQuery('#post_search').val('');
            jQuery('#post_search_results').hide().empty();
            var html = '<strong>' + jQuery('<div>').text(postTitle).html() + '</strong> (ID: ' + postId + ')';
            html += ' <a href="#" id="clear_post" style="color: #d63638; margin-left: 10px;"><?php echo esc_js(__('Change', 'academy-lesson-manager')); ?></a>';
            jQuery('#selected_post').html(html).show();
            jQuery('#post_search').hide();
            
            // Re-bind clear button
            jQuery('#clear_post').off('click').on('click', function(e) {
                e.preventDefault();
                jQuery('#post_id').val('');
                jQuery('#post_search').val('');
                jQuery('#selected_post').hide().empty();
                jQuery('#post_search').show();
            });
        }
        
        // Multiple selection (add mode)
        var selectedPosts = [];
        
        function addPost(postId, postTitle) {
            if (!postId || postId == 0) {
                return;
            }
            
            // Check if already added
            if (selectedPosts.find(function(p) { return p.id == postId; })) {
                return;
            }
            
            // Add to array
            selectedPosts.push({id: postId, title: postTitle});
            updateSelectedPosts();
            
            // Clear search
            jQuery('#post_search').val('');
            jQuery('#post_search_results').hide().empty();
        }
        
        // New function to handle collection data from data attribute
        function addCollectionFromData(element) {
            var collectionDataStr = jQuery(element).attr('data-collection');
            if (!collectionDataStr) {
                return;
            }
            
            try {
                var collectionData = JSON.parse(decodeURIComponent(collectionDataStr));
                addCollection(collectionData);
            } catch(e) {
                console.error('Error parsing collection data:', e);
                return;
            }
        }
        
        function addCollection(collectionData) {
            if (!collectionData || !collectionData.post_ids || collectionData.post_ids.length === 0) {
                return;
            }
            
            var postIds = collectionData.post_ids.map(function(id) { return parseInt(id); }).filter(function(id) { return id > 0; });
            var collectionTitle = collectionData.collection_title || 'Collection';
            var lessonsData = collectionData.lessons_data || [];
            
            // Create a map of post_id to lesson title for quick lookup
            var lessonsMap = {};
            lessonsData.forEach(function(lesson) {
                if (lesson.post_id) {
                    lessonsMap[lesson.post_id] = {
                        lesson_title: lesson.lesson_title || '',
                        post_title: lesson.post_title || lesson.lesson_title || ''
                    };
                }
            });
            
            // Add all lessons from collection
            var added = 0;
            postIds.forEach(function(postId) {
                if (postId > 0 && !selectedPosts.find(function(p) { return p.id == postId; })) {
                    // Try to get the actual lesson title from lessons data
                    var lessonTitle = collectionTitle + ' - Lesson ' + postId;
                    if (lessonsMap[postId]) {
                        lessonTitle = lessonsMap[postId].post_title || lessonsMap[postId].lesson_title || lessonTitle;
                    }
                    selectedPosts.push({id: postId, title: lessonTitle});
                    added++;
                }
            });
            
            if (added > 0) {
                updateSelectedPosts();
                // Clear search
                jQuery('#post_search').val('');
                jQuery('#post_search_results').hide().empty();
            }
        }
        
        function removePost(postId) {
            selectedPosts = selectedPosts.filter(function(p) { return p.id != postId; });
            updateSelectedPosts();
        }
        
        function updateSelectedPosts() {
            if (selectedPosts.length === 0) {
                jQuery('#selected_posts').hide().empty();
                jQuery('#post_ids').val('');
                return;
            }
            
            var html = '<strong>Selected Lessons (' + selectedPosts.length + '):</strong><br><ul style="list-style: none; margin: 10px 0 0 0; padding: 0;">';
            var postIds = [];
            
            selectedPosts.forEach(function(post) {
                postIds.push(post.id);
                html += '<li style="padding: 8px; margin: 5px 0; background: #fff; border: 1px solid #ddd; border-radius: 4px;">';
                html += '<strong>' + jQuery('<div>').text(post.title).html() + '</strong> (ID: ' + post.id + ')';
                html += ' <a href="#" onclick="removePost(' + post.id + '); return false;" style="color: #d63638; margin-left: 10px;"><?php echo esc_js(__('Remove', 'academy-lesson-manager')); ?></a>';
                html += '</li>';
            });
            
            html += '</ul>';
            jQuery('#selected_posts').html(html).show();
            jQuery('#post_ids').val(postIds.join(','));
        }
        </script>
        <?php
    }
    
    /**
     * Save credit log entry
     */
    private function save_credit_log() {
        if (!isset($_POST['alm_credit_log_nonce']) || !wp_verify_nonce($_POST['alm_credit_log_nonce'], 'alm_credit_log_action')) {
            wp_die(__('Security check failed.', 'academy-lesson-manager'));
        }
        
        $id = isset($_POST['credit_log_id']) ? intval($_POST['credit_log_id']) : 0;
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        
        // Handle single post_id (edit mode) or multiple post_ids (add mode)
        $post_ids = array();
        if ($id > 0) {
            // Edit mode: single post_id
            $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
            if (empty($post_id)) {
                wp_redirect(add_query_arg(array('page' => 'academy-manager-credit-log', 'action' => 'edit&id=' . $id, 'message' => 'error'), admin_url('admin.php')));
                exit;
            }
            $post_ids = array($post_id);
        } else {
            // Add mode: multiple post_ids
            $post_ids_str = isset($_POST['post_ids']) ? sanitize_text_field($_POST['post_ids']) : '';
            if (!empty($post_ids_str)) {
                $post_ids = array_map('intval', explode(',', $post_ids_str));
                $post_ids = array_filter($post_ids); // Remove empty values
            }
        }
        
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'lesson';
        $timestamp_date = isset($_POST['timestamp_date']) ? sanitize_text_field($_POST['timestamp_date']) : '';
        $timestamp_time = isset($_POST['timestamp_time']) ? sanitize_text_field($_POST['timestamp_time']) : '';
        
        // Validate required fields
        if (empty($user_id) || empty($post_ids)) {
            wp_redirect(add_query_arg(array('page' => 'academy-manager-credit-log', 'action' => $id ? 'edit&id=' . $id : 'add', 'user_id' => $user_id, 'message' => 'error'), admin_url('admin.php')));
            exit;
        }
        
        // Calculate timestamp
        if (!empty($timestamp_date)) {
            $timestamp = strtotime($timestamp_date . ' ' . ($timestamp_time ?: '00:00:00'));
        } else {
            $timestamp = current_time('timestamp');
        }
        
        if ($id > 0) {
            // Edit mode: Update single entry
            $post_id = $post_ids[0]; // Get the single post_id from array
            $sql = $this->wpdb->prepare(
                "UPDATE `{$this->table_name}` SET user_id = %d, post_id = %d, type = %s, timestamp = %d WHERE ID = %d",
                $user_id,
                $post_id,
                $type,
                $timestamp,
                $id
            );
            $result = $this->wpdb->query($sql);
            
            if ($result !== false) {
                wp_redirect(add_query_arg(array('page' => 'academy-manager-credit-log', 'message' => 'updated'), admin_url('admin.php')));
            } else {
                wp_redirect(add_query_arg(array('page' => 'academy-manager-credit-log', 'action' => 'edit&id=' . $id, 'message' => 'error'), admin_url('admin.php')));
            }
        } else {
            // Add mode: Insert multiple entries (one for each selected lesson)
            $inserted_count = 0;
            $errors = array();
            
            foreach ($post_ids as $post_id) {
                $sql = $this->wpdb->prepare(
                    "INSERT INTO `{$this->table_name}` (user_id, post_id, type, timestamp) VALUES (%d, %d, %s, %d)",
                    $user_id,
                    $post_id,
                    $type,
                    $timestamp
                );
                
                $result = $this->wpdb->query($sql);
                if ($result !== false) {
                    $inserted_count++;
                } else {
                    $errors[] = 'Post ID ' . $post_id . ': ' . $this->wpdb->last_error;
                }
            }
            
            if ($inserted_count > 0) {
                // Redirect back to add page with user_id preserved and success message
                $message = count($post_ids) > 1 
                    ? 'created_multiple' 
                    : 'created';
                wp_redirect(add_query_arg(array(
                    'page' => 'academy-manager-credit-log',
                    'action' => 'add',
                    'user_id' => $user_id,
                    'message' => $message,
                    'count' => $inserted_count
                ), admin_url('admin.php')));
            } else {
                wp_redirect(add_query_arg(array(
                    'page' => 'academy-manager-credit-log',
                    'action' => 'add',
                    'user_id' => $user_id,
                    'message' => 'error'
                ), admin_url('admin.php')));
            }
        }
        
        exit;
    }
    
    /**
     * Delete credit log entry
     */
    private function delete_credit_log() {
        if (!isset($_POST['alm_credit_log_nonce']) || !wp_verify_nonce($_POST['alm_credit_log_nonce'], 'alm_credit_log_action')) {
            wp_die(__('Security check failed.', 'academy-lesson-manager'));
        }
        
        $id = isset($_POST['credit_log_id']) ? intval($_POST['credit_log_id']) : 0;
        
        if (empty($id)) {
            wp_redirect(add_query_arg(array('page' => 'academy-manager-credit-log', 'message' => 'error'), admin_url('admin.php')));
            exit;
        }
        
        // Use direct SQL to avoid prefix issues
        $sql = $this->wpdb->prepare("DELETE FROM `{$this->table_name}` WHERE ID = %d", $id);
        $result = $this->wpdb->query($sql);
        
        if ($result !== false) {
            wp_redirect(add_query_arg(array('page' => 'academy-manager-credit-log', 'message' => 'deleted'), admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg(array('page' => 'academy-manager-credit-log', 'message' => 'error'), admin_url('admin.php')));
        }
        
        exit;
    }
    
    /**
     * Handle delete action
     */
    private function handle_delete($id) {
        if (empty($id)) {
            wp_redirect(add_query_arg(array('page' => 'academy-manager-credit-log', 'message' => 'error'), admin_url('admin.php')));
            exit;
        }
        
        // Use direct SQL to avoid prefix issues
        $sql = $this->wpdb->prepare("DELETE FROM `{$this->table_name}` WHERE ID = %d", $id);
        $result = $this->wpdb->query($sql);
        
        if ($result !== false) {
            wp_redirect(add_query_arg(array('page' => 'academy-manager-credit-log', 'message' => 'deleted'), admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg(array('page' => 'academy-manager-credit-log', 'message' => 'error'), admin_url('admin.php')));
        }
        
        exit;
    }
    
    /**
     * Handle bulk delete action
     */
    private function bulk_delete_credit_log() {
        if (!isset($_POST['alm_bulk_credit_log_nonce']) || !wp_verify_nonce($_POST['alm_bulk_credit_log_nonce'], 'alm_bulk_credit_log_action')) {
            wp_die(__('Security check failed.', 'academy-lesson-manager'));
        }
        
        $ids = isset($_POST['credit_log_ids']) ? array_map('intval', $_POST['credit_log_ids']) : array();
        
        if (empty($ids)) {
            $this->redirect_with_message('error', __('Please select at least one entry to delete.', 'academy-lesson-manager'));
            exit;
        }
        
        // Build redirect URL with preserved filters
        $redirect_args = array('page' => 'academy-manager-credit-log');
        
        if (isset($_POST['preserve_search_user']) && !empty($_POST['preserve_search_user'])) {
            $redirect_args['search_user'] = sanitize_text_field($_POST['preserve_search_user']);
        }
        if (isset($_POST['preserve_search_lesson']) && !empty($_POST['preserve_search_lesson'])) {
            $redirect_args['search_lesson'] = sanitize_text_field($_POST['preserve_search_lesson']);
        }
        if (isset($_POST['preserve_date_from']) && !empty($_POST['preserve_date_from'])) {
            $redirect_args['date_from'] = sanitize_text_field($_POST['preserve_date_from']);
        }
        if (isset($_POST['preserve_date_to']) && !empty($_POST['preserve_date_to'])) {
            $redirect_args['date_to'] = sanitize_text_field($_POST['preserve_date_to']);
        }
        if (isset($_POST['preserve_paged']) && !empty($_POST['preserve_paged'])) {
            $redirect_args['paged'] = intval($_POST['preserve_paged']);
        }
        
        // Delete entries
        $deleted_count = 0;
        // Sanitize IDs and build safe query
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids); // Remove any invalid IDs
        if (empty($ids)) {
            $redirect_args['message'] = 'error';
            wp_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
            exit;
        }
        
        // Build safe IN clause
        $ids_string = implode(',', $ids);
        $sql = "DELETE FROM `{$this->table_name}` WHERE ID IN ($ids_string)";
        $result = $this->wpdb->query($sql);
        
        if ($result !== false) {
            $deleted_count = $result;
            $redirect_args['message'] = 'bulk_deleted';
            $redirect_args['count'] = $deleted_count;
        } else {
            $redirect_args['message'] = 'error';
        }
        
        wp_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
        exit;
    }
    
    /**
     * Redirect with message
     */
    private function redirect_with_message($message, $text = '') {
        $args = array('page' => 'academy-manager-credit-log', 'message' => $message);
        if (!empty($text)) {
            $args['message_text'] = urlencode($text);
        }
        wp_redirect(add_query_arg($args, admin_url('admin.php')));
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
                $('.alm-credit-log-checkbox').prop('checked', $(this).prop('checked'));
            });
            
            // Update select all checkbox when individual checkboxes change
            $('.alm-credit-log-checkbox').on('change', function() {
                var totalCheckboxes = $('.alm-credit-log-checkbox').length;
                var checkedCheckboxes = $('.alm-credit-log-checkbox:checked').length;
                
                if (checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0) {
                    $('#cb-select-all-1').prop('checked', true);
                } else {
                    $('#cb-select-all-1').prop('checked', false);
                }
            });
        });
        </script>
        <?php
    }
}

