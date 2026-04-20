<?php
/**
 * Collections Admin Class
 * 
 * Handles the collections admin page functionality
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Collections {
    

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
        $this->table_name = $this->database->get_table_name('collections');
        add_action('admin_init', array($this, 'maybe_process_form_early'), 1);
    }

    /**
     * Process collection create/update at admin_init (before output) so wp_redirect works.
     */
    public function maybe_process_form_early() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        if (!isset($_POST['form_action'])) {
            return;
        }
        $form_action = sanitize_text_field($_POST['form_action']);
        if (!in_array($form_action, array('create', 'update'), true)) {
            return;
        }
        if (!isset($_GET['page']) || $_GET['page'] !== 'academy-manager') {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }
        $this->handle_form_submission();
        exit;
    }
    
    /**
     * Render the collections admin page
     */
    public function render_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
            $this->handle_bulk_action();
            return;
        }
        
        echo '<div class="wrap">';
        $this->render_navigation_buttons('collections');
        echo '<h1>' . __('Collections', 'academy-lesson-manager') . ' <a href="?page=academy-manager&action=add" class="page-title-action">' . __('Add New', 'academy-lesson-manager') . '</a></h1>';
        
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
            case 'sync':
                $this->handle_sync($id);
                break;
            case 'find_post':
                $this->handle_find_post($id);
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
     * Render the collections list page
     */
    private function render_list_page() {
        // Show success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            switch ($message) {
                case 'created':
                    echo '<div class="notice notice-success"><p>' . __('Collection created successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'updated':
                    echo '<div class="notice notice-success"><p>' . __('Collection updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'deleted':
                    echo '<div class="notice notice-success"><p>' . __('Collection deleted successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'bulk-deleted':
                    echo '<div class="notice notice-success"><p>' . __('Selected collections deleted successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'fixed':
                    echo '<div class="notice notice-success"><p>' . __('WordPress post created successfully for this collection.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'already_exists':
                    echo '<div class="notice notice-warning"><p>' . __('WordPress post already exists for this collection.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'synced':
                    echo '<div class="notice notice-success"><p>' . __('All collections synced successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'synced_with_errors':
                    echo '<div class="notice notice-warning"><p>' . __('Collections synced with some errors. Please check the error log (ALMD prefix) for details.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }

        // Handle search and filters
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $filter_level = isset($_GET['filter_level']) ? intval($_GET['filter_level']) : '';
        $order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'ID';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        
        // Build query
        $where_conditions = array();
        if (!empty($search)) {
            $where_conditions[] = $this->wpdb->prepare("(collection_title LIKE %s OR collection_description LIKE %s)", 
                '%' . $search . '%', '%' . $search . '%');
        }
        if (!empty($filter_level)) {
            $where_conditions[] = $this->wpdb->prepare("membership_level = %d", $filter_level);
        }
        
        $where = '';
        if (!empty($where_conditions)) {
            $where = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $order_clause = "ORDER BY $order_by $order";
        
        $collections = $this->wpdb->get_results(
            "SELECT * FROM {$this->table_name} $where $order_clause"
        );
        
        // Render statistics
        $this->render_statistics();
        
        echo '<div class="tablenav top">';
        echo '<div class="alignleft actions">';
        echo '<form method="get" action="" style="display: inline-block;">';
        echo '<input type="hidden" name="page" value="academy-manager" />';
        echo '<input type="text" name="search" value="' . esc_attr($search) . '" placeholder="' . __('Search collections...', 'academy-lesson-manager') . '" style="margin-right: 10px;" />';
        echo '<select name="filter_level" style="margin-right: 10px;">';
        echo '<option value="">' . __('All Levels', 'academy-lesson-manager') . '</option>';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            $selected = $filter_level == $level['numeric'] ? 'selected' : '';
            echo '<option value="' . esc_attr($level['numeric']) . '" ' . $selected . '>' . esc_html($level['name']) . '</option>';
        }
        echo '</select>';
        echo '<input type="submit" class="button" value="' . __('Filter', 'academy-lesson-manager') . '" />';
        if (!empty($search) || !empty($filter_level)) {
            echo '<a href="?page=academy-manager" class="button">' . __('Clear', 'academy-lesson-manager') . '</a>';
        }
        echo '</form>';
        echo '</div>';
        echo '</div>';
        
        // Add Sync All Collections button
        echo '<div class="tablenav" style="margin-bottom: 20px;">';
        echo '<div class="alignleft">';
        echo '<a href="?page=academy-manager&action=sync_all" class="button button-primary" onclick="return confirm(\'' . __('This will sync all collections to WordPress course posts. Existing course posts will be reused. Continue?', 'academy-lesson-manager') . '\')">' . __('Sync All Collections', 'academy-lesson-manager') . '</a>';
        echo '<p class="description" style="margin-top: 10px;">' . __('This will link collections to existing course posts by matching titles, or create new posts if needed. Existing posts will not be duplicated.', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        echo '</div>';
        
        // Bulk actions form
        echo '<form method="post" action="" id="collections-bulk-actions">';
        echo '<div class="alm-bulk-actions" style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border: 1px solid #ddd;">';
        echo '<h3>' . __('Bulk Actions', 'academy-lesson-manager') . '</h3>';
        echo '<p>';
        echo '<label for="bulk_membership_level">' . __('Set membership level to:', 'academy-lesson-manager') . '</label> ';
        echo '<select id="bulk_membership_level" name="bulk_membership_level">';
        echo '<option value="">' . __('Select level...', 'academy-lesson-manager') . '</option>';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            echo '<option value="' . esc_attr($level['numeric']) . '">' . esc_html($level['name']) . ' (' . $level['numeric'] . ') - ' . esc_html($level['description']) . '</option>';
        }
        echo '</select> ';
        echo '<input type="hidden" id="bulk_action" name="bulk_action" value="" />';
        echo '<input type="submit" class="button" value="' . __('Update Membership', 'academy-lesson-manager') . '" onclick="document.getElementById(\'bulk_action\').value=\'update_membership\'; return true;" />';
        echo ' ';
        echo '<input type="submit" class="button button-secondary" value="' . __('Delete Selected', 'academy-lesson-manager') . '" onclick="document.getElementById(\'bulk_action\').value=\'delete\'; return confirm(\'' . __('Are you sure you want to delete the selected collections? This action cannot be undone.', 'academy-lesson-manager') . '\');" />';
        echo '</p>';
        echo '<p class="description">' . __('Select collections in the table below, then choose an action and click the button.', 'academy-lesson-manager') . '</p>';
        echo '</div>';

        echo '<table class="wp-list-table widefat fixed striped alm-collections-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>';
        echo '<th scope="col" class="manage-column column-id">' . __('ID', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-post-id">' . __('Post ID', 'academy-lesson-manager') . '</th>';
        
        // Collection Title column with sorting
        $title_sort = ($order_by === 'collection_title') ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC';
        $title_order_icon = '';
        if ($order_by === 'collection_title') {
            $title_order_icon = $order === 'ASC' ? '▲' : '▼';
        }
        echo '<th scope="col" class="manage-column column-title sortable ' . ($order_by === 'collection_title' ? strtolower($order) : 'asc') . '">';
        echo '<a href="?page=academy-manager&order_by=collection_title&order=' . $title_sort . '&search=' . esc_attr($search) . '&filter_level=' . esc_attr($filter_level) . '">' . __('Collection Title', 'academy-lesson-manager') . ' ' . $title_order_icon . '</a>';
        echo '</th>';
        
        // Membership Level column with sorting
        $level_sort = ($order_by === 'membership_level') ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC';
        $level_order_icon = '';
        if ($order_by === 'membership_level') {
            $level_order_icon = $order === 'ASC' ? '▲' : '▼';
        }
        echo '<th scope="col" class="manage-column column-membership sortable ' . ($order_by === 'membership_level' ? strtolower($order) : 'asc') . '">';
        echo '<a href="?page=academy-manager&order_by=membership_level&order=' . $level_sort . '&search=' . esc_attr($search) . '&filter_level=' . esc_attr($filter_level) . '">' . __('Level', 'academy-lesson-manager') . ' ' . $level_order_icon . '</a>';
        echo '</th>';
        echo '<th scope="col" class="manage-column column-lessons" style="text-align: center;">' . __('Lessons', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-actions">' . __('Actions', 'academy-lesson-manager') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($collections)) {
            echo '<tr><td colspan="7" style="text-align: center; padding: 20px;">' . __('No collections found.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            foreach ($collections as $collection) {
                $this->render_collection_row($collection);
            }
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</form>';
    }
    
    /**
     * Render a collection row
     */
    private function render_collection_row($collection) {
        $lesson_count = ALM_Helpers::count_collection_lessons($collection->ID);
        $level_name = ALM_Admin_Settings::get_membership_level_name(intval($collection->membership_level));
        echo '<tr>';
        echo '<th scope="row" class="check-column"><input type="checkbox" name="collection[]" value="' . $collection->ID . '" form="collections-bulk-actions" /></th>';
        echo '<td class="column-id">' . $collection->ID . '</td>';
        echo '<td class="column-post-id">';
        if ($collection->post_id) {
            echo '<a href="' . get_edit_post_link($collection->post_id) . '" target="_blank">' . $collection->post_id . '</a>';
        } else {
            echo '—';
        }
        echo '</td>';
        echo '<td class="column-title"><strong>' . esc_html(stripslashes($collection->collection_title)) . '</strong></td>';
        echo '<td class="column-membership">' . esc_html($level_name) . '</td>';
        echo '<td class="column-lessons" style="text-align: center;">' . $lesson_count . '</td>';
        echo '<td class="column-actions">';
        echo '<div style="display: flex; gap: 5px; flex-wrap: wrap;">';
        echo '<a href="?page=academy-manager&action=edit&id=' . $collection->ID . '" class="button button-small" title="' . __('Edit Collection', 'academy-lesson-manager') . '"><span class="dashicons dashicons-edit"></span></a>';
        
        // Add View Front End button if post exists
        if ($collection->post_id && get_post($collection->post_id)) {
            $frontend_url = get_permalink($collection->post_id);
            echo '<a href="' . esc_url($frontend_url) . '" class="button button-small" target="_blank" title="' . __('View on Front End', 'academy-lesson-manager') . '"><span class="dashicons dashicons-admin-site"></span></a>';
            echo '<a href="' . get_edit_post_link($collection->post_id) . '" class="button button-small" target="_blank" title="' . __('Edit WordPress Post', 'academy-lesson-manager') . '"><span class="dashicons dashicons-external"></span></a>';
        } else {
            // Add Fix button if no post exists
            echo '<a href="?page=academy-manager&action=fix&id=' . $collection->ID . '" class="button button-small" onclick="return confirm(\'' . __('Create WordPress post for this collection?', 'academy-lesson-manager') . '\')" title="' . __('Create WordPress Post', 'academy-lesson-manager') . '"><span class="dashicons dashicons-admin-tools"></span></a>';
        }
        
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    
    /**
     * Render the add collection page
     */
    private function render_add_page() {
        echo '<div class="alm-collection-details">';
        echo '<h2>' . __('Add New Collection', 'academy-lesson-manager') . '</h2>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="post_id">' . __('Post ID', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="number" id="post_id" name="post_id" value="" class="small-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="collection_title">' . __('Collection Title', 'academy-lesson-manager') . ' <span class="description">(required)</span></label></th>';
        echo '<td><input type="text" id="collection_title" name="collection_title" value="" class="regular-text" required /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="collection_description">' . __('Description', 'academy-lesson-manager') . '</label></th>';
        echo '<td><textarea id="collection_description" name="collection_description" rows="5" cols="50" class="large-text"></textarea></td>';
        echo '</tr>';

        // Membership level
        echo '<tr>';
        echo '<th scope="row"><label for="membership_level">' . __('Membership Level', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="membership_level" name="membership_level" required>';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            $selected = ($level['numeric'] == 2) ? 'selected' : '';
            echo '<option value="' . esc_attr($level['numeric']) . '" ' . $selected . '>' . esc_html($level['name']) . ' (' . $level['numeric'] . ') - ' . esc_html($level['description']) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="form_action" value="create" />';
        echo '<input type="submit" class="button-primary" value="' . __('Add Collection', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render the edit collection page
     */
    private function render_edit_page($id) {
        $collection = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE ID = %d",
            $id
        ));
        
        if (!$collection) {
            echo '<div class="notice notice-error"><p>' . __('Collection not found.', 'academy-lesson-manager') . '</p></div>';
            return;
        }
        
        // Show success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            switch ($message) {
                case 'created':
                    echo '<div class="notice notice-success"><p>' . __('Collection created successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'updated':
                    echo '<div class="notice notice-success"><p>' . __('Collection updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'deleted':
                    echo '<div class="notice notice-success"><p>' . __('Collection deleted successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'lesson_added':
                    echo '<div class="notice notice-success"><p>' . __('Lesson added to collection successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'lesson_removed':
                    echo '<div class="notice notice-success"><p>' . __('Lesson removed from collection successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'synced_single':
                    echo '<div class="notice notice-success"><p>' . __('Collection synced to WordPress post successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'post_found':
                    echo '<div class="notice notice-success"><p>' . __('Course post found and linked successfully!', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'post_not_found':
                    echo '<div class="notice notice-warning"><p>' . __('No matching course post found. You may need to create one.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        // Get collection URL for copy button
        $collection_url = '';
        if ($collection->post_id) {
            $collection_url = get_permalink($collection->post_id);
        }
        
        $btn_base         = 'display:inline-flex;align-items:center;gap:5px;padding:0 12px;height:28px;font-size:12px;font-weight:500;line-height:1;border-radius:5px;border:1px solid #c3c4c7;background:#fff;color:#2271b1;text-decoration:none;cursor:pointer;margin:0 4px 0 0;vertical-align:middle;white-space:nowrap;';
        $btn_hover        = 'onmouseover="this.style.background=\'#f0f6ff\';this.style.borderColor=\'#2271b1\'" onmouseout="this.style.background=\'#fff\';this.style.borderColor=\'#c3c4c7\'"';
        $btn_danger       = 'display:inline-flex;align-items:center;gap:5px;padding:0 12px;height:28px;font-size:12px;font-weight:500;line-height:1;border-radius:5px;border:1px solid #c3c4c7;background:#fff;color:#dc3232;text-decoration:none;cursor:pointer;margin:0 4px 0 0;vertical-align:middle;white-space:nowrap;';
        $btn_danger_hover = 'onmouseover="this.style.background=\'#fff5f5\';this.style.borderColor=\'#dc3232\'" onmouseout="this.style.background=\'#fff\';this.style.borderColor=\'#c3c4c7\'"';

        $ico_back     = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>';
        $ico_copy_url = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>';
        $ico_trash    = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>';

        echo '<div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-bottom:16px;padding:10px 12px;background:#f6f7f7;border:1px solid #e0e0e0;border-radius:6px;">';
        echo '<a href="?page=academy-manager" style="' . $btn_base . '" ' . $btn_hover . '>' . $ico_back . ' ' . esc_html__('Back', 'academy-lesson-manager') . '</a>';
        if (!empty($collection_url)) {
            echo '<button type="button" class="alm-copy-url-btn" data-url="' . esc_attr($collection_url) . '" style="' . $btn_base . '" ' . $btn_hover . '>' . $ico_copy_url . ' ' . esc_html__('Copy URL', 'academy-lesson-manager') . '</button>';
        }
        echo '<a href="?page=academy-manager&action=delete&id=' . intval($collection->ID) . '" onclick="return confirm(\'' . esc_js(__('Are you sure you want to delete this collection?', 'academy-lesson-manager')) . '\')" style="' . $btn_danger . '" ' . $btn_danger_hover . '>' . $ico_trash . ' ' . esc_html__('Delete', 'academy-lesson-manager') . '</a>';
        echo '</div>';

        // Collection details
        echo '<div class="alm-collection-details">';
        echo '<h2>' . esc_html__('Edit Collection', 'academy-lesson-manager') . ' <span style="font-weight:400;color:#666;font-size:16px;">(id: ' . intval($collection->ID) . ')</span></h2>';

        $lessons_table_for_jump = $this->database->get_table_name('lessons');
        $jump_lessons           = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT ID, lesson_title, menu_order FROM {$lessons_table_for_jump} WHERE collection_id = %d ORDER BY menu_order ASC, ID ASC",
            $collection->ID
        ));
        if (!empty($jump_lessons)) {
            echo '<div style="margin-bottom:12px;display:flex;align-items:center;gap:10px;">';
            echo '<label for="alm-lesson-jump" style="font-weight:600;white-space:nowrap;">' . esc_html__('Jump to Lesson:', 'academy-lesson-manager') . '</label>';
            echo '<select id="alm-lesson-jump" style="max-width:480px;">';
            echo '<option value="">' . esc_html__('— Select lesson —', 'academy-lesson-manager') . '</option>';
            foreach ($jump_lessons as $jl) {
                $jl_order = isset($jl->menu_order) ? intval($jl->menu_order) : 0;
                $jl_title = $jl->lesson_title ? stripslashes($jl->lesson_title) : '';
                $label    = '#' . $jl_order . ' — ' . ($jl_title ? esc_html($jl_title) : esc_html__('(untitled)', 'academy-lesson-manager')) . ' (ID ' . intval($jl->ID) . ')';
                echo '<option value="' . intval($jl->ID) . '">' . $label . '</option>';
            }
            echo '</select>';
            echo '</div>';
            echo '<script>document.getElementById("alm-lesson-jump").addEventListener("change",function(){ if(this.value) window.location.href="?page=academy-manager-lessons&action=edit&id="+this.value; });</script>';
        }

        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';

        echo '<tr>';
        echo '<th scope="row"><label for="post_id">' . __('Post ID', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="number" id="post_id" name="post_id" value="' . esc_attr($collection->post_id) . '" class="small-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="collection_title">' . __('Collection Title', 'academy-lesson-manager') . ' <span class="description">(required)</span></label></th>';
        echo '<td><input type="text" id="collection_title" name="collection_title" value="' . esc_attr(stripslashes($collection->collection_title)) . '" class="regular-text" required /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="collection_description">' . __('Description', 'academy-lesson-manager') . '</label></th>';
        echo '<td><textarea id="collection_description" name="collection_description" rows="5" cols="50" class="large-text">' . esc_textarea(stripslashes($collection->collection_description)) . '</textarea></td>';
        echo '</tr>';

        // Membership level
        echo '<tr>';
        echo '<th scope="row"><label for="membership_level">' . __('Membership Level', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="membership_level" name="membership_level" required>';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            // Skip Free (0) level - starter program access is handled via whitelist
            if ($level['numeric'] == 0) {
                continue;
            }
            $selected = ($level['numeric'] == intval($collection->membership_level)) ? 'selected' : '';
            echo '<option value="' . esc_attr($level['numeric']) . '" ' . $selected . '>' . esc_html($level['name']) . ' (' . $level['numeric'] . ') - ' . esc_html($level['description']) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Created', 'academy-lesson-manager') . '</th>';
        echo '<td>' . ALM_Helpers::format_date($collection->created_at) . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Updated', 'academy-lesson-manager') . '</th>';
        echo '<td>' . ALM_Helpers::format_date($collection->updated_at) . '</td>';
        echo '</tr>';
        
        // Show course_id from post meta if post exists
        if ($collection->post_id && get_post($collection->post_id)) {
            $course_id = get_post_meta($collection->post_id, 'course_id', true);
            $alm_collection_id = get_post_meta($collection->post_id, 'alm_collection_id', true);
            
            echo '<tr>';
            echo '<th scope="row">' . __('Post Meta Info', 'academy-lesson-manager') . '</th>';
            echo '<td>';
            echo '<strong>course_id:</strong> ' . ($course_id ? $course_id : 'Not set') . '<br>';
            echo '<strong>alm_collection_id:</strong> ' . ($alm_collection_id ? $alm_collection_id : 'Not set');
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="collection_id" value="' . $collection->ID . '" />';
        echo '<input type="hidden" name="form_action" value="update" />';
        echo '<input type="submit" class="button-primary" value="' . __('Update Collection', 'academy-lesson-manager') . '" /> ';
        echo '<button type="button" id="alm-fix-lesson-order" class="button" data-collection-id="' . intval($collection->ID) . '" data-nonce="' . esc_attr(wp_create_nonce('alm_admin_nonce')) . '">' . esc_html__('Fix Order', 'academy-lesson-manager') . '</button> ';
        echo '<a href="?page=academy-manager&action=find_post&id=' . $collection->ID . '" class="button" title="' . __('Find and link to existing course post', 'academy-lesson-manager') . '">' . __('Find Post', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager&action=sync&id=' . $collection->ID . '" class="button" title="' . __('Sync Collection to WordPress Post', 'academy-lesson-manager') . '">' . __('Sync to Post', 'academy-lesson-manager') . '</a>';
        echo '</p>';
        echo '</form>';
        echo '</div>';
        
        // Lessons in this collection
        $this->render_collection_lessons($id);
        
        // Copy URL JavaScript
        if (!empty($collection_url)) {
            echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const copyBtn = document.querySelector(".alm-copy-url-btn");
                if (copyBtn) {
                    copyBtn.addEventListener("click", function() {
                        const url = this.getAttribute("data-url");
                        navigator.clipboard.writeText(url).then(function() {
                            const originalHtml = copyBtn.innerHTML;
                            copyBtn.innerHTML = "<span class=\"dashicons dashicons-yes\"></span> Copied!";
                            copyBtn.style.background = "#46b450";
                            setTimeout(function() {
                                copyBtn.innerHTML = originalHtml;
                                copyBtn.style.background = "";
                            }, 2000);
                        }).catch(function(err) {
                            // Fallback for older browsers
                            const textArea = document.createElement("textarea");
                            textArea.value = url;
                            document.body.appendChild(textArea);
                            textArea.select();
                            document.execCommand("copy");
                            document.body.removeChild(textArea);
                            const originalHtml = copyBtn.innerHTML;
                            copyBtn.innerHTML = "<span class=\"dashicons dashicons-yes\"></span> Copied!";
                            copyBtn.style.background = "#46b450";
                            setTimeout(function() {
                                copyBtn.innerHTML = originalHtml;
                                copyBtn.style.background = "";
                            }, 2000);
                        });
                    });
                }
            });
            </script>';
        }
    }
    
    /**
     * Render lessons in a collection
     */
    private function render_collection_lessons($collection_id) {
        // Ensure menu_order column exists
        $this->database->check_and_add_menu_order_column();
        
        $lessons_table = $this->database->get_table_name('lessons');
        
        $lessons = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$lessons_table} WHERE collection_id = %d ORDER BY menu_order ASC, lesson_title ASC",
            $collection_id
        ));
        
        echo '<div class="alm-collection-lessons">';
        $cid_int = intval($collection_id);
        echo '<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:10px;">';
        echo '<h3 style="margin:0;">' . esc_html__('Lessons in This Collection', 'academy-lesson-manager') . '</h3>';
        echo '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">';
        echo '<a href="?page=academy-manager-lessons&action=add&collection_id=' . $cid_int . '" class="button button-small">' . esc_html__('Add Lesson', 'academy-lesson-manager') . '</a>';
        echo '<button type="button" class="button button-small" id="alm-calculate-collection-bunny-durations" data-collection-id="' . $cid_int . '">' . esc_html__('Bunny Durations', 'academy-lesson-manager') . '</button>';
        echo '<button type="button" class="button button-small" id="alm-calculate-collection-vimeo-durations" data-collection-id="' . $cid_int . '">' . esc_html__('Vimeo Durations', 'academy-lesson-manager') . '</button>';
        echo '<button type="button" class="button button-small" id="alm-sync-collection-lessons" data-collection-id="' . $cid_int . '">' . esc_html__('Sync All', 'academy-lesson-manager') . '</button>';
        echo '<div style="position:relative;display:inline-block;">';
        echo '<button type="button" id="alm-col-coll-btn" class="button button-small" style="display:inline-flex;align-items:center;gap:4px;">';
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z"/></svg>';
        echo ' ' . esc_html__('Columns', 'academy-lesson-manager') . ' <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg></button>';
        echo '<div id="alm-col-coll-menu" style="display:none;position:absolute;right:0;top:100%;margin-top:4px;background:#fff;border:1px solid #c3c4c7;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.12);padding:8px 0;z-index:9999;min-width:150px;">';
        $coll_cols             = array(
            'ccol-id'       => __('ID', 'academy-lesson-manager'),
            'ccol-order'    => __('Order', 'academy-lesson-manager'),
            'ccol-duration' => __('Duration', 'academy-lesson-manager'),
            'ccol-sample'   => __('Has Sample', 'academy-lesson-manager'),
            'ccol-release'  => __('Release Date', 'academy-lesson-manager'),
            'ccol-sync'     => __('Synced Status', 'academy-lesson-manager'),
        );
        $coll_hidden_defaults  = array('ccol-id');
        foreach ($coll_cols as $k => $lbl) {
            $chk = !in_array($k, $coll_hidden_defaults, true) ? 'checked' : '';
            echo '<label style="display:flex;align-items:center;gap:8px;padding:5px 14px;cursor:pointer;font-size:13px;white-space:nowrap;" onmouseover="this.style.background=\'#f0f6ff\'" onmouseout="this.style.background=\'transparent\'"><input type="checkbox" class="alm-col-coll-toggle" data-col="' . esc_attr($k) . '" ' . $chk . ' style="margin:0"> ' . esc_html($lbl) . '</label>';
        }
        echo '</div></div>';
        echo '</div></div>';

        if (empty($lessons)) {
            echo '<p>' . __('No lessons found in this collection.', 'academy-lesson-manager') . '</p>';
        } else {
            $ico_edit = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>';
            $ico_dupe = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>';
            $ico_rem  = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>';
            $r_btn    = 'display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border:1px solid #c3c4c7;border-radius:4px;background:#fff;text-decoration:none;cursor:pointer;margin:1px;';

            echo '<div style="overflow-x:auto;width:100%;">';
            echo '<table class="wp-list-table widefat striped alm-lesson-reorder" style="table-layout:auto;">';
            echo '<thead><tr>';
            echo '<th scope="col" style="width:30px;"></th>';
            echo '<th scope="col" class="ccol-id" style="width:45px;">' . esc_html__('ID', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col" class="ccol-order" style="width:55px;">' . esc_html__('Order', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col" style="min-width:160px;">' . esc_html__('Title', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col" class="ccol-duration" style="width:80px;">' . esc_html__('Duration', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col" class="ccol-sample" style="width:90px;">' . esc_html__('Has Sample', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col" class="ccol-release" style="width:100px;">' . esc_html__('Release Date', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col" class="ccol-sync" style="width:120px;">' . esc_html__('Synced Status', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col" style="width:100px;">' . esc_html__('Actions', 'academy-lesson-manager') . '</th>';
            echo '</tr></thead>';
            echo '<tbody class="ui-sortable">';

            foreach ($lessons as $lesson) {
                $sync_status = $this->check_lesson_sync_status($lesson);

                $has_sample         = false;
                $sample_video_url   = isset($lesson->sample_video_url) ? $lesson->sample_video_url : '';
                $sample_chapter_id  = isset($lesson->sample_chapter_id) ? intval($lesson->sample_chapter_id) : 0;

                if ((!empty($sample_video_url) && $sample_video_url !== '0') || $sample_chapter_id > 0) {
                    $has_sample = true;
                }

                echo '<tr data-lesson-id="' . intval($lesson->ID) . '">';
                echo '<td><span class="dashicons dashicons-menu" style="cursor: move; color: #999;"></span></td>';
                echo '<td class="ccol-id">' . intval($lesson->ID) . '</td>';
                echo '<td class="lesson-menu-order ccol-order" style="text-align:center;font-variant-numeric:tabular-nums;">' . intval(isset($lesson->menu_order) ? $lesson->menu_order : 0) . '</td>';
                echo '<td><a href="?page=academy-manager-lessons&action=edit&id=' . intval($lesson->ID) . '">' . esc_html(stripslashes($lesson->lesson_title)) . '</a></td>';
                echo '<td class="ccol-duration">' . ALM_Helpers::format_duration($lesson->duration) . '</td>';
                echo '<td class="ccol-sample" style="text-align: center;">';
                if ($has_sample) {
                    echo '<span style="color: #46b450; font-weight: bold;">' . __('Yes', 'academy-lesson-manager') . '</span>';
                } else {
                    echo '<span style="color: #999;">' . __('No', 'academy-lesson-manager') . '</span>';
                }
                echo '</td>';
                $release_date_display = (!empty($lesson->post_date) && $lesson->post_date !== '0000-00-00') ? esc_html(substr($lesson->post_date, 0, 10)) : '—';
                echo '<td class="ccol-release">' . $release_date_display . '</td>';
                echo '<td class="ccol-sync">';
                if ($sync_status['status'] === 'synced') {
                    echo '<span style="color: #46b450; font-weight: bold;">✓ ' . __('Synced', 'academy-lesson-manager') . '</span>';
                } elseif ($sync_status['status'] === 'partial') {
                    echo '<span style="color: #ffb900; font-weight: bold;">⚠ ' . __('Partially Synced', 'academy-lesson-manager') . '</span>';
                } elseif ($sync_status['status'] === 'not_synced') {
                    echo '<span style="color: #dc3232; font-weight: bold;">✗ ' . __('Not Synced', 'academy-lesson-manager') . '</span>';
                } else {
                    echo '<span style="color: #666; font-weight: bold;">— ' . __('No WordPress Post', 'academy-lesson-manager') . '</span>';
                }
                echo '</td>';
                echo '<td style="white-space:nowrap;">';
                echo '<a href="?page=academy-manager-lessons&action=edit&id=' . intval($lesson->ID) . '" target="_blank" title="' . esc_attr__('Edit', 'academy-lesson-manager') . '" style="' . $r_btn . 'color:#2271b1;" onmouseover="this.style.background=\'#f0f6ff\';this.style.borderColor=\'#2271b1\'" onmouseout="this.style.background=\'#fff\';this.style.borderColor=\'#c3c4c7\'">' . $ico_edit . '</a>';
                echo '<a href="?page=academy-manager-lessons&action=duplicate&id=' . intval($lesson->ID) . '" target="_blank" title="' . esc_attr__('Duplicate', 'academy-lesson-manager') . '" style="' . $r_btn . 'color:#2271b1;" onmouseover="this.style.background=\'#f0f6ff\';this.style.borderColor=\'#2271b1\'" onmouseout="this.style.background=\'#fff\';this.style.borderColor=\'#c3c4c7\'">' . $ico_dupe . '</a>';
                echo '<a href="?page=academy-manager-lessons&action=remove_from_collection&id=' . intval($lesson->ID) . '&collection_id=' . $cid_int . '" onclick="return confirm(\'' . esc_js(__('Remove this lesson from the collection?', 'academy-lesson-manager')) . '\')" title="' . esc_attr__('Remove', 'academy-lesson-manager') . '" style="' . $r_btn . 'color:#dc3232;" onmouseover="this.style.background=\'#fff5f5\';this.style.borderColor=\'#dc3232\'" onmouseout="this.style.background=\'#fff\';this.style.borderColor=\'#c3c4c7\'">' . $ico_rem . '</a>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            echo '<script>
(function(){
    var key = "alm_hidden_ccols";
    var defs = ' . wp_json_encode($coll_hidden_defaults) . ';
    var saved = localStorage.getItem(key);
    var hidden = defs;
    if (saved) {
        try {
            var p = JSON.parse(saved);
            hidden = Array.isArray(p) ? p : defs;
        } catch (e) {
            hidden = defs;
        }
    }
    function apply() {
        document.querySelectorAll(".alm-col-coll-toggle").forEach(function(cb) {
            var show = cb.checked;
            document.querySelectorAll("." + cb.dataset.col).forEach(function(el) {
                el.style.display = show ? "" : "none";
            });
        });
        localStorage.setItem(key, JSON.stringify(
            Array.from(document.querySelectorAll(".alm-col-coll-toggle")).filter(function(c) { return !c.checked; }).map(function(c) { return c.dataset.col; })
        ));
    }
    document.querySelectorAll(".alm-col-coll-toggle").forEach(function(cb) {
        cb.checked = !hidden.includes(cb.dataset.col);
    });
    apply();
    document.querySelectorAll(".alm-col-coll-toggle").forEach(function(cb) {
        cb.addEventListener("change", apply);
    });
    var btn = document.getElementById("alm-col-coll-btn");
    var menu = document.getElementById("alm-col-coll-menu");
    if (btn && menu) {
        btn.addEventListener("click", function(e) {
            e.stopPropagation();
            menu.style.display = menu.style.display === "none" ? "block" : "none";
        });
        document.addEventListener("click", function() {
            menu.style.display = "none";
        });
        menu.addEventListener("click", function(e) { e.stopPropagation(); });
    }
})();
</script>';
        }
        
        echo '</div>';
    }
    
    /**
     * Check sync status of lesson with WordPress post
     */
    private function check_lesson_sync_status($lesson) {
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
     * Handle form submission (create/update)
     */
    private function handle_form_submission() {
        $action = isset($_POST['form_action']) ? sanitize_text_field($_POST['form_action']) : '';
        
        if ($action === 'create') {
            $this->create_collection();
        } elseif ($action === 'update') {
            $this->update_collection();
        }
    }
    
    /**
     * Create a new collection
     */
    private function create_collection() {
        $collection_title = sanitize_text_field($_POST['collection_title']);
        $collection_description = sanitize_textarea_field($_POST['collection_description']);
        $post_id = intval($_POST['post_id']);
        
        if (empty($collection_title)) {
            wp_die(__('Collection title is required.', 'academy-lesson-manager'));
        }
        
        $result = $this->wpdb->insert(
            $this->table_name,
            array(
                'post_id' => $post_id,
                'collection_title' => $collection_title,
                'collection_description' => $collection_description,
                'membership_level' => intval($_POST['membership_level']),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%d', '%s', '%s')
        );
        
        if ($result === false) {
            wp_die(__('Error creating collection.', 'academy-lesson-manager'));
        }
        
        $collection_id = $this->wpdb->insert_id;
        
        // Sync to WordPress post
        $sync = new ALM_Post_Sync();
        $post_id = $sync->sync_collection_to_post($collection_id);
        
        wp_redirect(add_query_arg(array('page' => 'academy-manager', 'action' => 'edit', 'id' => $collection_id, 'message' => 'created')));
        exit;
    }
    
    /**
     * Update an existing collection
     */
    private function update_collection() {
        $collection_id = intval($_POST['collection_id']);
        $collection_title = sanitize_text_field($_POST['collection_title']);
        $collection_description = sanitize_textarea_field($_POST['collection_description']);
        $post_id = intval($_POST['post_id']);
        
        if (empty($collection_title)) {
            wp_die(__('Collection title is required.', 'academy-lesson-manager'));
        }
        
        $result = $this->wpdb->update(
            $this->table_name,
            array(
                'post_id' => $post_id,
                'collection_title' => $collection_title,
                'collection_description' => $collection_description,
                'membership_level' => intval($_POST['membership_level']),
                'updated_at' => current_time('mysql')
            ),
            array('ID' => $collection_id),
            array('%d', '%s', '%s', '%d', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_die(__('Error updating collection.', 'academy-lesson-manager'));
        }
        
        // Also update all lessons in this collection
        $lessons_table = $this->database->get_table_name('lessons');
        $new_membership_level = intval($_POST['membership_level']);
        $this->wpdb->update(
            $lessons_table,
            array('membership_level' => $new_membership_level),
            array('collection_id' => $collection_id),
            array('%d'),
            array('%d')
        );
        
        // Sync to WordPress post
        $sync = new ALM_Post_Sync();
        $sync->sync_collection_to_post($collection_id);
        
        wp_redirect(add_query_arg(array('page' => 'academy-manager', 'action' => 'edit', 'id' => $collection_id, 'message' => 'updated')));
        exit;
    }

    /**
     * Handle bulk actions for collections
     */
    private function handle_bulk_action() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $action = sanitize_text_field($_POST['bulk_action']);
        if ($action === 'update_membership') {
            $this->handle_bulk_membership_update();
        } elseif ($action === 'delete') {
            $this->handle_bulk_delete();
        }
    }
    
    /**
     * Handle bulk membership update for collections
     */
    private function handle_bulk_membership_update() {
        $collection_ids = isset($_POST['collection']) ? array_map('intval', (array) $_POST['collection']) : array();
        $membership_level = isset($_POST['bulk_membership_level']) ? intval($_POST['bulk_membership_level']) : 0;
        
        if (empty($collection_ids)) {
            wp_redirect(admin_url('admin.php?page=academy-manager'));
            exit;
        }
        
        if ($membership_level === 0 && $membership_level !== 0) {
            wp_redirect(admin_url('admin.php?page=academy-manager'));
            exit;
        }
        
        $lessons_table = $this->database->get_table_name('lessons');
        $updated_collections = 0;
        $updated_lessons = 0;
        
        foreach ($collection_ids as $collection_id) {
            // Update collection membership level
            $result = $this->wpdb->update(
                $this->table_name,
                array('membership_level' => $membership_level),
                array('ID' => $collection_id),
                array('%d'),
                array('%d')
            );
            
            if ($result !== false) {
                $updated_collections++;
                
                // Also update all lessons in this collection
                $lessons_updated = $this->wpdb->update(
                    $lessons_table,
                    array('membership_level' => $membership_level),
                    array('collection_id' => $collection_id),
                    array('%d'),
                    array('%d')
                );
                
                if ($lessons_updated !== false) {
                    $updated_lessons += $lessons_updated;
                }
            }
        }
        
        wp_redirect(admin_url('admin.php?page=academy-manager'));
        exit;
    }
    
    /**
     * Handle collection deletion
     */
    private function handle_delete($collection_id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Get post_id before deleting from ALM table
        $collection = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT post_id FROM {$this->table_name} WHERE ID = %d",
            $collection_id
        ));
        
        $result = $this->wpdb->delete($this->table_name, array('ID' => $collection_id));
        
        if ($result) {
            // Delete WordPress post and meta if it exists
            if ($collection && $collection->post_id) {
                $sync = new ALM_Post_Sync();
                $sync->delete_post_and_meta($collection->post_id);
            }
            
            wp_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=academy-manager')));
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager')));
            exit;
        }
    }
    
    /**
     * Handle bulk delete for collections
     */
    private function handle_bulk_delete() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $collection_ids = isset($_POST['collection']) ? array_map('intval', (array) $_POST['collection']) : array();
        
        if (empty($collection_ids)) {
            wp_redirect(admin_url('admin.php?page=academy-manager'));
            exit;
        }
        
        $deleted_count = 0;
        $sync = new ALM_Post_Sync();
        
        foreach ($collection_ids as $collection_id) {
            // Get post_id before deleting
            $collection = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT post_id FROM {$this->table_name} WHERE ID = %d",
                $collection_id
            ));
            
            $result = $this->wpdb->delete($this->table_name, array('ID' => $collection_id));
            
            if ($result !== false) {
                $deleted_count++;
                
                // Delete WordPress post and meta if it exists
                if ($collection && $collection->post_id) {
                    $sync->delete_post_and_meta($collection->post_id);
                }
            }
        }
        
        wp_redirect(add_query_arg('message', 'bulk-deleted', admin_url('admin.php?page=academy-manager')));
        exit;
    }
    
    /**
     * Handle fixing collection (create WordPress post)
     */
    private function handle_fix($collection_id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Check if collection exists
        $collection = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE ID = %d",
            $collection_id
        ));
        
        if (!$collection) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager')));
            exit;
        }
        
        // Check if post already exists
        if ($collection->post_id && get_post($collection->post_id)) {
            wp_redirect(add_query_arg('message', 'already_exists', admin_url('admin.php?page=academy-manager')));
            exit;
        }
        
        // Create WordPress post
        $sync = new ALM_Post_Sync();
        $post_id = $sync->sync_collection_to_post($collection_id);
        
        if ($post_id) {
            wp_redirect(add_query_arg('message', 'fixed', admin_url('admin.php?page=academy-manager')));
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager')));
            exit;
        }
    }
    
    /**
     * Handle finding and linking to existing course post
     */
    private function handle_find_post($id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Get collection
        $collection = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE ID = %d",
            $id
        ));
        
        if (!$collection) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager&action=edit&id=' . $id)));
            exit;
        }
        
        // Find course post with matching title
        $existing_post = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID FROM {$this->wpdb->posts} WHERE post_title = %s AND post_type = 'course' LIMIT 1",
            stripslashes($collection->collection_title)
        ));
        
        if ($existing_post) {
            // Update collection with found post_id
            $this->wpdb->update(
                $this->table_name,
                array('post_id' => $existing_post->ID),
                array('ID' => $id),
                array('%d'),
                array('%d')
            );
            
            wp_redirect(add_query_arg('message', 'post_found', admin_url('admin.php?page=academy-manager&action=edit&id=' . $id)));
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'post_not_found', admin_url('admin.php?page=academy-manager&action=edit&id=' . $id)));
            exit;
        }
    }
    
    /**
     * Handle syncing a single collection
     */
    private function handle_sync($id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $sync = new ALM_Post_Sync();
        $result = $sync->sync_collection_to_post($id);
        
        if ($result) {
            wp_redirect(add_query_arg('message', 'synced_single', admin_url('admin.php?page=academy-manager&action=edit&id=' . $id)));
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager&action=edit&id=' . $id)));
            exit;
        }
    }
    
    /**
     * Handle syncing all collections
     */
    private function handle_sync_all() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $sync = new ALM_Post_Sync();
        
        // Get all collections
        $collections = $this->wpdb->get_results("SELECT ID FROM {$this->table_name}");
        
        $synced = 0;
        $errors = 0;
        
        foreach ($collections as $collection) {
            $result = $sync->sync_collection_to_post($collection->ID);
            if ($result) {
                $synced++;
            } else {
                $errors++;
                error_log("ALMD Failed to sync collection ID: {$collection->ID}");
            }
        }
        
        // Show result message
        if ($errors > 0) {
            wp_redirect(add_query_arg('message', 'synced_with_errors', admin_url('admin.php?page=academy-manager')));
        } else {
            wp_redirect(add_query_arg('message', 'synced', admin_url('admin.php?page=academy-manager')));
        }
        exit;
    }
    
    /**
     * Render database statistics
     */
    private function render_statistics() {
        // Get counts
        $collections_count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        $lessons_table = $this->database->get_table_name('lessons');
        $lessons_count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$lessons_table}");
        
        $chapters_table = $this->database->get_table_name('chapters');
        $chapters_count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$chapters_table}");
        
        // Get average lessons per collection
        $avg_lessons = $collections_count > 0 ? round($lessons_count / $collections_count, 1) : 0;
        
        echo '<div class="alm-statistics" style="background: #f9f9f9; border: 1px solid #ddd; padding: 15px 20px; margin-bottom: 20px; display: flex; gap: 30px; flex-wrap: wrap;">';
        
        echo '<div style="flex: 1; min-width: 150px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($collections_count) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Collections', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '<div style="flex: 1; min-width: 150px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($lessons_count) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Lessons', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '<div style="flex: 1; min-width: 150px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($chapters_count) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Chapters', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '<div style="flex: 1; min-width: 150px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($avg_lessons, 1) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Avg Lessons/Collection', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '</div>';
    }
}
