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
    }
    
    /**
     * Render the collections admin page
     */
    public function render_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['bulk_action'])) {
                $this->handle_bulk_action();
                return;
            }
            $this->handle_form_submission();
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
        
        // Back button and actions
        echo '<p>';
        echo '<a href="?page=academy-manager" class="button">&larr; ' . __('Back to Collections', 'academy-lesson-manager') . '</a> ';
        
        // Add Copy URL button if post exists
        if (!empty($collection_url)) {
            echo '<button type="button" class="button alm-copy-url-btn" data-url="' . esc_attr($collection_url) . '" title="' . __('Copy Collection URL', 'academy-lesson-manager') . '"><span class="dashicons dashicons-admin-page"></span> ' . __('Copy URL', 'academy-lesson-manager') . '</button> ';
        }
        
        echo '<a href="?page=academy-manager&action=delete&id=' . $collection->ID . '" class="button" onclick="return confirm(\'' . __('Are you sure you want to delete this collection?', 'academy-lesson-manager') . '\')" title="' . __('Delete Collection', 'academy-lesson-manager') . '"><span class="dashicons dashicons-trash"></span> ' . __('Delete', 'academy-lesson-manager') . '</a>';
        echo '</p>';
        
        // Collection details
        echo '<div class="alm-collection-details">';
        echo '<h2>' . __('Edit Collection', 'academy-lesson-manager') . '</h2>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Collection ID', 'academy-lesson-manager') . '</th>';
        echo '<td>' . $collection->ID . '</td>';
        echo '</tr>';
        
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
        echo '<h3>' . __('Lessons in This Collection', 'academy-lesson-manager') . ' <a href="?page=academy-manager-lessons&action=add&collection_id=' . $collection_id . '" class="button button-small">' . __('Add Lesson', 'academy-lesson-manager') . '</a>';
        echo ' <button type="button" class="button button-small" id="alm-calculate-collection-bunny-durations" data-collection-id="' . $collection_id . '">' . __('Calculate All Bunny Durations', 'academy-lesson-manager') . '</button>';
        echo ' <button type="button" class="button button-small" id="alm-calculate-collection-vimeo-durations" data-collection-id="' . $collection_id . '">' . __('Calculate All Vimeo Durations', 'academy-lesson-manager') . '</button>';
        echo ' <button type="button" class="button button-small" id="alm-sync-collection-lessons" data-collection-id="' . $collection_id . '">' . __('Sync All Lessons', 'academy-lesson-manager') . '</button>';
        echo '</h3>';
        
        if (empty($lessons)) {
            echo '<p>' . __('No lessons found in this collection.', 'academy-lesson-manager') . '</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col" style="width: 40px;">' . __('', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col" style="width: 60px;">' . __('ID', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col" style="width: 40%;">' . __('Title', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col" style="width: 100px;">' . __('Duration', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col" style="width: 150px;">' . __('Synced Status', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('Actions', 'academy-lesson-manager') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody class="ui-sortable">';
            
            foreach ($lessons as $lesson) {
                $sync_status = $this->check_lesson_sync_status($lesson);
                
                echo '<tr data-lesson-id="' . $lesson->ID . '">';
                echo '<td><span class="dashicons dashicons-menu" style="cursor: move; color: #999;"></span></td>';
                echo '<td>' . $lesson->ID . '</td>';
                echo '<td><a href="?page=academy-manager-lessons&action=edit&id=' . $lesson->ID . '">' . esc_html(stripslashes($lesson->lesson_title)) . '</a></td>';
                echo '<td>' . ALM_Helpers::format_duration($lesson->duration) . '</td>';
                echo '<td>';
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
                echo '<td>';
                echo '<a href="?page=academy-manager-lessons&action=edit&id=' . $lesson->ID . '" class="button button-small">' . __('Edit', 'academy-lesson-manager') . '</a> ';
                echo '<a href="?page=academy-manager-lessons&action=remove_from_collection&id=' . $lesson->ID . '&collection_id=' . $collection_id . '" class="button button-small" onclick="return confirm(\'' . __('Are you sure you want to remove this lesson from the collection?', 'academy-lesson-manager') . '\')">' . __('Remove', 'academy-lesson-manager') . '</a>';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
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
