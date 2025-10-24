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
                case 'fixed':
                    echo '<div class="notice notice-success"><p>' . __('WordPress post created successfully for this collection.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'already_exists':
                    echo '<div class="notice notice-warning"><p>' . __('WordPress post already exists for this collection.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        // Handle search
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'ID';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        
        // Build query
        $where = '';
        if (!empty($search)) {
            $where = $this->wpdb->prepare("WHERE collection_title LIKE %s OR collection_description LIKE %s", 
                '%' . $search . '%', '%' . $search . '%');
        }
        
        $order_clause = "ORDER BY $order_by $order";
        
        $collections = $this->wpdb->get_results(
            "SELECT * FROM {$this->table_name} $where $order_clause"
        );
        
        echo '<div class="tablenav top">';
        echo '<div class="alignleft actions">';
        echo '<form method="post" style="display: inline-block;">';
        echo '<input type="text" name="search" value="' . esc_attr($search) . '" placeholder="' . __('Search collections...', 'academy-lesson-manager') . '" />';
        echo '<input type="submit" class="button" value="' . __('Search', 'academy-lesson-manager') . '" />';
        if (!empty($search)) {
            echo '<a href="?page=academy-manager" class="button">' . __('Clear', 'academy-lesson-manager') . '</a>';
        }
        echo '</form>';
        echo '</div>';
        echo '</div>';
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>';
        echo '<th scope="col" class="manage-column column-id">' . __('ID', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-post-id">' . __('Post ID', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-title">' . __('Collection Title', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-membership">' . __('Membership Level', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-lessons">' . __('Lessons', 'academy-lesson-manager') . '</th>';
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
    }
    
    /**
     * Render a collection row
     */
    private function render_collection_row($collection) {
        $lesson_count = ALM_Helpers::count_collection_lessons($collection->ID);
        echo '<tr>';
        echo '<th scope="row" class="check-column"><input type="checkbox" name="collection[]" value="' . $collection->ID . '" /></th>';
        echo '<td class="column-id">' . $collection->ID . '</td>';
        echo '<td class="column-post-id">' . ($collection->post_id ? $collection->post_id : '—') . '</td>';
        echo '<td class="column-title"><strong>' . esc_html(stripslashes($collection->collection_title)) . '</strong></td>';
        echo '<td class="column-membership">' . esc_html(ALM_Admin_Settings::get_membership_level_name($collection->membership_level)) . '</td>';
        echo '<td class="column-lessons">' . $lesson_count . '</td>';
        echo '<td class="column-actions">';
        echo '<div style="display: flex; gap: 5px; flex-wrap: wrap;">';
        echo '<a href="?page=academy-manager&action=edit&id=' . $collection->ID . '" class="button button-small" title="' . __('Edit Collection', 'academy-lesson-manager') . '"><span class="dashicons dashicons-edit"></span></a>';
        
        // Add View Post button if post exists
        if ($collection->post_id && get_post($collection->post_id)) {
            echo '<a href="' . get_edit_post_link($collection->post_id) . '" class="button button-small" target="_blank" title="' . __('View WordPress Post', 'academy-lesson-manager') . '"><span class="dashicons dashicons-external"></span></a>';
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
            }
        }
        
        // Back button and actions
        echo '<p>';
        echo '<a href="?page=academy-manager" class="button">&larr; ' . __('Back to Collections', 'academy-lesson-manager') . '</a> ';
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
        
        echo '<tr>';
        echo '<th scope="row"><label for="membership_level">' . __('Membership Level', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="membership_level" name="membership_level" required>';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            $selected = ($level['numeric'] == $collection->membership_level) ? 'selected' : '';
            echo '<option value="' . esc_attr($level['numeric']) . '" ' . $selected . '>' . esc_html($level['name']) . ' (' . $level['numeric'] . ') - ' . esc_html($level['description']) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Higher levels can access lower level content.', 'academy-lesson-manager') . '</p>';
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
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="collection_id" value="' . $collection->ID . '" />';
        echo '<input type="hidden" name="form_action" value="update" />';
        echo '<input type="submit" class="button-primary" value="' . __('Update Collection', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
        
        // Lessons in this collection
        $this->render_collection_lessons($id);
    }
    
    /**
     * Render lessons in a collection
     */
    private function render_collection_lessons($collection_id) {
        $lessons_table = $this->database->get_table_name('lessons');
        
        $lessons = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$lessons_table} WHERE collection_id = %d ORDER BY lesson_title ASC",
            $collection_id
        ));
        
        echo '<div class="alm-collection-lessons">';
        echo '<h3>' . __('Lessons in This Collection', 'academy-lesson-manager') . ' <a href="?page=academy-manager-lessons&action=add&collection_id=' . $collection_id . '" class="button button-small">' . __('Add Lesson', 'academy-lesson-manager') . '</a></h3>';
        
        if (empty($lessons)) {
            echo '<p>' . __('No lessons found in this collection.', 'academy-lesson-manager') . '</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">' . __('ID', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('Title', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('Duration', 'academy-lesson-manager') . '</th>';
            echo '<th scope="col">' . __('Actions', 'academy-lesson-manager') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($lessons as $lesson) {
                echo '<tr>';
                echo '<td>' . $lesson->ID . '</td>';
                echo '<td><a href="?page=academy-manager-lessons&action=edit&id=' . $lesson->ID . '">' . esc_html(stripslashes($lesson->lesson_title)) . '</a></td>';
                echo '<td>' . ALM_Helpers::format_duration($lesson->duration) . '</td>';
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
        
        // Sync to WordPress post
        $sync = new ALM_Post_Sync();
        $sync->sync_collection_to_post($collection_id);
        
        wp_redirect(add_query_arg(array('page' => 'academy-manager', 'action' => 'edit', 'id' => $collection_id, 'message' => 'updated')));
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
}
