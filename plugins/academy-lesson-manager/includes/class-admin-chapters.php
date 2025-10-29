<?php
/**
 * Chapters Admin Class
 * 
 * Handles the chapters admin page functionality
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Chapters {
    
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
        $this->table_name = $this->database->get_table_name('chapters');
    }
    
    /**
     * Render the chapters admin page
     */
    public function render_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Handle POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle bulk actions first
            if (isset($_POST['action']) && $_POST['action'] !== '-1') {
                $this->handle_bulk_action();
                return;
            }
            
            // Handle bulk slug update
            if (isset($_POST['bulk_update_slugs'])) {
                $this->handle_bulk_update_slugs();
                return;
            }
            
            // Handle other form submissions (add/edit)
            $this->handle_form_submission();
            return;
        }
        
        echo '<div class="wrap">';
        $this->render_navigation_buttons('chapters');
        echo '<h1>' . __('Chapters', 'academy-lesson-manager') . ' <a href="?page=academy-manager-chapters&action=add" class="page-title-action">' . __('Add New', 'academy-lesson-manager') . '</a></h1>';
        
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
        echo '<a href="?page=academy-manager-chapters" class="button ' . ($current_page === 'chapters' ? 'button-primary' : '') . '">' . __('Chapters', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings" class="button ' . ($current_page === 'settings' ? 'button-primary' : '') . '" style="margin-left: 10px;">' . __('Settings', 'academy-lesson-manager') . '</a>';
        echo '</div>';
    }
    
    /**
     * Render the chapters list page
     */
    private function render_list_page() {
        // Show success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            switch ($message) {
                case 'created':
                    echo '<div class="notice notice-success"><p>' . __('Chapter created successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'updated':
                    echo '<div class="notice notice-success"><p>' . __('Chapter updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'deleted':
                    echo '<div class="notice notice-success"><p>' . __('Chapter deleted successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'slugs_updated':
                    $updated_count = isset($_GET['updated_count']) ? intval($_GET['updated_count']) : 0;
                    echo '<div class="notice notice-success"><p>' . sprintf(__('%d chapter slug(s) updated successfully.', 'academy-lesson-manager'), $updated_count) . '</p></div>';
                    break;
                case 'bulk_deleted':
                    $deleted_count = isset($_GET['deleted_count']) ? intval($_GET['deleted_count']) : 0;
                    echo '<div class="notice notice-success"><p>' . sprintf(__('%d chapter(s) deleted successfully.', 'academy-lesson-manager'), $deleted_count) . '</p></div>';
                    break;
                case 'bulk_slugs_updated':
                    $updated_count = isset($_GET['updated_count']) ? intval($_GET['updated_count']) : 0;
                    echo '<div class="notice notice-success"><p>' . sprintf(__('%d chapter slug(s) updated successfully.', 'academy-lesson-manager'), $updated_count) . '</p></div>';
                    break;
            }
        }
        
        // Handle search
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'ID';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        
		// Build query with lesson join
        $lessons_table = $this->database->get_table_name('lessons');
		$where = '';
		$orphan_filter = isset($_GET['orphans']) && $_GET['orphans'] === '1';
        if (!empty($search)) {
			$where = $this->wpdb->prepare("WHERE (c.chapter_title LIKE %s OR l.lesson_title LIKE %s)", 
				'%' . $search . '%', '%' . $search . '%');
        }

		// Orphan filter (chapters without a valid lesson)
		if ($orphan_filter) {
			$orphan_sql = "(c.lesson_id IS NULL OR c.lesson_id = 0 OR l.ID IS NULL)";
			$where = empty($where) ? "WHERE {$orphan_sql}" : $where . " AND {$orphan_sql}";
		}
        
        // Validate order by
        $allowed_order_by = array('ID', 'chapter_title', 'menu_order', 'vimeo_id', 'duration');
        if (!in_array($order_by, $allowed_order_by)) {
            $order_by = 'ID';
        }
        
        $allowed_order = array('ASC', 'DESC');
        if (!in_array($order, $allowed_order)) {
            $order = 'DESC';
        }
        
        $sql = "SELECT c.*, l.lesson_title, l.collection_id 
                FROM {$this->table_name} c 
                LEFT JOIN {$lessons_table} l ON c.lesson_id = l.ID 
                {$where} 
                ORDER BY c.{$order_by} {$order}";
        
        $chapters = $this->wpdb->get_results($sql);
        
		// Render statistics
		$this->render_statistics();
        
		// Render search form
		$this->render_search_form($search);
        
        // Render chapters table
        $this->render_chapters_table($chapters, $order_by, $order);
    }
    
    /**
     * Render search form
     */
    private function render_search_form($search) {
        echo '<div class="alm-search-form">';
        echo '<form method="post" action="">';
        echo '<p class="search-box">';
        echo '<label class="screen-reader-text" for="chapter-search-input">' . __('Search Chapters:', 'academy-lesson-manager') . '</label>';
        echo '<input type="search" id="chapter-search-input" name="search" value="' . esc_attr($search) . '" placeholder="' . __('Search chapters...', 'academy-lesson-manager') . '" />';
        echo '<input type="submit" id="search-submit" class="button" value="' . __('Search Chapters', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        // Orphan toggle link
        $is_orphans = isset($_GET['orphans']) && $_GET['orphans'] === '1';
        $orphans_on_url = add_query_arg(array('orphans' => '1'));
        $orphans_off_url = remove_query_arg('orphans');
        echo '<div style="margin-top: 8px;">';
        if ($is_orphans) {
            echo '<a class="button" href="' . esc_url($orphans_off_url) . '">' . __('Show All Chapters', 'academy-lesson-manager') . '</a> ';
            echo '<span class="description" style="margin-left: 6px;">' . __('Filtering orphans (no lesson)', 'academy-lesson-manager') . '</span>';
        } else {
            echo '<a class="button" href="' . esc_url($orphans_on_url) . '">' . __('Show Orphaned Chapters', 'academy-lesson-manager') . '</a>';
        }
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Render chapters table with bulk actions
     */
    private function render_chapters_table($chapters, $order_by, $order) {
        echo '<form method="post" action="">';
        echo '<div class="tablenav top">';
        echo '<div class="alignleft actions bulkactions">';
        echo '<select name="action" id="bulk-action-selector-top">';
        echo '<option value="-1">' . __('Bulk Actions', 'academy-lesson-manager') . '</option>';
        echo '<option value="update_slugs">' . __('Update Slugs', 'academy-lesson-manager') . '</option>';
        echo '<option value="delete">' . __('Delete', 'academy-lesson-manager') . '</option>';
        echo '</select>';
        echo '<input type="submit" class="button action" value="' . __('Apply', 'academy-lesson-manager') . '" onclick="return confirmAction();" />';
        echo '<script>
        function confirmAction() {
            var action = document.getElementById("bulk-action-selector-top").value;
            if (action === "delete") {
                return confirm("' . __('Are you sure you want to delete the selected chapters?', 'academy-lesson-manager') . '");
            }
            return true;
        }
        </script>';
        echo '</div>';
        echo '</div>';
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all" /></th>';
        echo '<th scope="col" class="manage-column">' . $this->get_sortable_header('ID', 'ID', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column">' . __('Lesson', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column">' . $this->get_sortable_header('Order', 'menu_order', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column">' . $this->get_sortable_header('Chapter Title', 'chapter_title', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column">' . __('Slug', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column">' . $this->get_sortable_header('Vimeo ID', 'vimeo_id', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column">' . __('YouTube ID', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column">' . $this->get_sortable_header('Duration', 'duration', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column">' . __('Free', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column">' . __('Actions', 'academy-lesson-manager') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($chapters)) {
            echo '<tr><td colspan="11" class="no-items">' . __('No chapters found.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            foreach ($chapters as $chapter) {
                $this->render_chapter_row($chapter);
            }
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</form>';
    }
    
    /**
     * Render the add chapter page
     */
    private function render_add_page() {
        $lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
        
        echo '<div class="alm-chapter-details">';
        echo '<h2>' . __('Add New Chapter', 'academy-lesson-manager') . '</h2>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="lesson_id">' . __('Lesson', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="lesson_id" name="lesson_id" required>';
        echo '<option value="">' . __('Select a lesson...', 'academy-lesson-manager') . '</option>';
        $lessons_table = $this->database->get_table_name('lessons');
        $lessons = $this->wpdb->get_results("SELECT ID, lesson_title FROM $lessons_table ORDER BY lesson_title ASC");
        foreach ($lessons as $lesson) {
            $selected = ($lesson->ID == $lesson_id) ? 'selected' : '';
            echo '<option value="' . esc_attr($lesson->ID) . '" ' . $selected . '>' . esc_html(stripslashes($lesson->lesson_title)) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="chapter_title">' . __('Chapter Title', 'academy-lesson-manager') . ' <span class="description">(required)</span></label></th>';
        echo '<td><input type="text" id="chapter_title" name="chapter_title" value="" class="regular-text" required /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="menu_order">' . __('Order', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="number" id="menu_order" name="menu_order" value="1" class="small-text" min="1" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="vimeo_id">' . __('Vimeo ID', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="number" id="vimeo_id" name="vimeo_id" value="" class="small-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="youtube_id">' . __('YouTube ID', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="text" id="youtube_id" name="youtube_id" value="" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="bunny_url">' . __('Bunny URL', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="url" id="bunny_url" name="bunny_url" value="" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="duration">' . __('Duration (seconds)', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="duration" name="duration" value="0" class="small-text" />';
        echo '<span class="description" style="margin-left: 10px;">(00:00:00)</span>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="free">' . __('Free Chapter', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="free" name="free">';
        $free_options = ALM_Helpers::get_yes_no_options();
        foreach ($free_options as $value => $label) {
            echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="form_action" value="create" />';
        echo '<input type="submit" class="button-primary" value="' . __('Add Chapter', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render a single chapter row
     */
    private function render_chapter_row($chapter) {
        $lesson_title = $chapter->lesson_title ? $chapter->lesson_title : 'Unknown Lesson';
        $background = ($chapter->vimeo_id == 0 && empty($chapter->youtube_id) && empty($chapter->bunny_url)) ? 'background-color: #ffebee;' : '';
        
        // Get chapter permalink (lesson post + chapter slug parameter)
        $chapter_url = '';
        if ($chapter->lesson_id) {
            $lessons_table = $this->database->get_table_name('lessons');
            $lesson = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT post_id FROM {$lessons_table} WHERE ID = %d",
                $chapter->lesson_id
            ));
            
            if ($lesson && $lesson->post_id && get_post($lesson->post_id)) {
                $chapter_url = add_query_arg('c', $chapter->slug, get_permalink($lesson->post_id));
            }
        }
        
        echo '<tr style="' . $background . '">';
        echo '<th scope="row" class="check-column"><input type="checkbox" name="chapter[]" value="' . $chapter->ID . '" /></th>';
        echo '<td class="column-id">' . $chapter->ID . '</td>';
        echo '<td class="column-lesson">';
        if ($chapter->lesson_id) {
            echo '<a href="?page=academy-manager-lessons&action=edit&id=' . $chapter->lesson_id . '">' . esc_html(stripslashes($lesson_title)) . '</a>';
        } else {
            echo esc_html(stripslashes($lesson_title));
        }
        echo '</td>';
        echo '<td class="column-order">' . $chapter->menu_order . '</td>';
        echo '<td class="column-title"><strong>' . esc_html(stripslashes($chapter->chapter_title)) . '</strong></td>';
        echo '<td class="column-slug">';
        if (empty($chapter->slug)) {
            echo '<span style="color: #dc3232; font-weight: bold;">⚠️ Missing</span>';
        } else {
            echo esc_html($chapter->slug);
        }
        echo '</td>';
        echo '<td class="column-vimeo">';
        if ($chapter->vimeo_id) {
            echo '<a href="https://vimeo.com/' . $chapter->vimeo_id . '" target="_blank">' . $chapter->vimeo_id . '</a>';
        } else {
            echo '—';
        }
        echo '</td>';
        echo '<td class="column-youtube">';
        if ($chapter->youtube_id) {
            echo '<a href="https://youtube.com/watch?v=' . $chapter->youtube_id . '" target="_blank">' . esc_html($chapter->youtube_id) . '</a>';
        } else {
            echo '—';
        }
        echo '</td>';
        echo '<td class="column-duration">' . ALM_Helpers::format_duration($chapter->duration) . '</td>';
        echo '<td class="column-free">' . ($chapter->free === 'y' ? __('Yes', 'academy-lesson-manager') : __('No', 'academy-lesson-manager')) . '</td>';
        echo '<td class="column-actions">';
        echo '<div style="display: flex; gap: 5px; flex-wrap: nowrap;">';
        echo '<a href="?page=academy-manager-chapters&action=edit&id=' . $chapter->ID . '" class="button button-small" title="' . __('Edit Chapter', 'academy-lesson-manager') . '"><span class="dashicons dashicons-edit"></span></a>';
        if (!empty($chapter_url)) {
            echo '<a href="' . esc_url($chapter_url) . '" class="button button-small" target="_blank" title="' . __('View Chapter on Frontend', 'academy-lesson-manager') . '"><span class="dashicons dashicons-admin-site"></span></a>';
        }
        echo '<a href="?page=academy-manager-chapters&action=delete&id=' . $chapter->ID . '" class="button button-small" onclick="return confirm(\'' . __('Are you sure you want to delete this chapter?', 'academy-lesson-manager') . '\')" title="' . __('Delete Chapter', 'academy-lesson-manager') . '"><span class="dashicons dashicons-trash"></span></a>';
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
     * Render the edit page (editable form)
     */
    private function render_edit_page($id) {
        if (empty($id)) {
            echo '<div class="notice notice-error"><p>' . __('Invalid chapter ID.', 'academy-lesson-manager') . '</p></div>';
            return;
        }
        
        // Get chapter with lesson info
        $lessons_table = $this->database->get_table_name('lessons');
        $chapter = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT c.*, l.lesson_title, l.collection_id  
             FROM {$this->table_name} c 
             LEFT JOIN {$lessons_table} l ON c.lesson_id = l.ID 
             WHERE c.ID = %d",
            $id
        ));
        
        if (!$chapter) {
            echo '<div class="notice notice-error"><p>' . __('Chapter not found.', 'academy-lesson-manager') . '</p></div>';
            return;
        }
        
        // Show success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            switch ($message) {
                case 'updated':
                    echo '<div class="notice notice-success"><p>' . __('Chapter updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        // Back button and actions
        echo '<p>';
        echo '<a href="?page=academy-manager-chapters" class="button">&larr; ' . __('Back to Chapters', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-chapters&action=delete&id=' . $chapter->ID . '" class="button" onclick="return confirm(\'' . __('Are you sure you want to delete this chapter?', 'academy-lesson-manager') . '\')">' . __('Delete', 'academy-lesson-manager') . '</a>';
        echo '</p>';
        
        // Chapter details
        echo '<div class="alm-chapter-details">';
        echo '<h2>' . __('Edit Chapter', 'academy-lesson-manager') . '</h2>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Chapter ID', 'academy-lesson-manager') . '</th>';
        echo '<td>' . $chapter->ID . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="lesson_id">' . __('Lesson', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="lesson_id" name="lesson_id" required>';
        echo '<option value="">' . __('Select a lesson...', 'academy-lesson-manager') . '</option>';
        $lessons = $this->wpdb->get_results("SELECT ID, lesson_title FROM $lessons_table ORDER BY lesson_title ASC");
        foreach ($lessons as $lesson) {
            $selected = ($lesson->ID == $chapter->lesson_id) ? 'selected' : '';
            echo '<option value="' . esc_attr($lesson->ID) . '" ' . $selected . '>' . esc_html(stripslashes($lesson->lesson_title)) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="chapter_title">' . __('Chapter Title', 'academy-lesson-manager') . ' <span class="description">(required)</span></label></th>';
        echo '<td><input type="text" id="chapter_title" name="chapter_title" value="' . esc_attr(stripslashes($chapter->chapter_title)) . '" class="regular-text" required /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Slug', 'academy-lesson-manager') . '</th>';
        echo '<td>' . ($chapter->slug ? esc_html($chapter->slug) : '<span style="color: #dc3232;">Not set</span>') . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="menu_order">' . __('Order', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="number" id="menu_order" name="menu_order" value="' . esc_attr($chapter->menu_order) . '" class="small-text" min="1" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="vimeo_id">' . __('Vimeo ID', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="number" id="vimeo_id" name="vimeo_id" value="' . esc_attr($chapter->vimeo_id) . '" class="small-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="youtube_id">' . __('YouTube ID', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="text" id="youtube_id" name="youtube_id" value="' . esc_attr($chapter->youtube_id) . '" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="bunny_url">' . __('Bunny URL', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="url" id="bunny_url" name="bunny_url" value="' . esc_attr($chapter->bunny_url) . '" class="regular-text" />';
        echo '<button type="button" class="button fetch-bunny-metadata" style="margin-left: 10px;">' . __('Fetch Metadata', 'academy-lesson-manager') . '</button>';
        echo '<p class="description">' . __('Enter a Bunny.net URL and click "Fetch Metadata" to automatically get video duration and other information.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="duration">' . __('Duration (seconds)', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="duration" name="duration" value="' . esc_attr($chapter->duration) . '" class="small-text" />';
        echo '<span class="description" style="margin-left: 10px;">(' . ALM_Helpers::format_duration($chapter->duration) . ')</span>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="free">' . __('Free Chapter', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="free" name="free">';
        $free_options = ALM_Helpers::get_yes_no_options();
        foreach ($free_options as $value => $label) {
            $selected = ($value === $chapter->free) ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Created', 'academy-lesson-manager') . '</th>';
        echo '<td>' . ALM_Helpers::format_date($chapter->created_at) . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Updated', 'academy-lesson-manager') . '</th>';
        echo '<td>' . ALM_Helpers::format_date($chapter->updated_at) . '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="chapter_id" value="' . $chapter->ID . '" />';
        echo '<input type="hidden" name="form_action" value="update" />';
        echo '<input type="submit" class="button-primary" value="' . __('Update Chapter', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
        
        // Warning if no video source
        if ($chapter->vimeo_id == 0 && empty($chapter->youtube_id) && empty($chapter->bunny_url)) {
            echo '<div class="notice notice-warning"><p><strong>' . __('Warning:', 'academy-lesson-manager') . '</strong> ' . __('This chapter has no video source (Vimeo ID, YouTube ID, or Bunny URL).', 'academy-lesson-manager') . '</p></div>';
        }
    }
    
    /**
     * Handle form submission (create/update)
     */
    private function handle_form_submission() {
        $action = isset($_POST['form_action']) ? sanitize_text_field($_POST['form_action']) : '';
        
        if ($action === 'create') {
            $this->create_chapter();
        } elseif ($action === 'update') {
            $this->update_chapter();
        }
    }
    
    /**
     * Create a new chapter
     */
    private function create_chapter() {
        $chapter_title = sanitize_text_field($_POST['chapter_title']);
        $lesson_id = intval($_POST['lesson_id']);
        
        if (empty($chapter_title) || empty($lesson_id)) {
            wp_die(__('Chapter title and lesson are required.', 'academy-lesson-manager'));
        }
        
        // Generate slug from chapter title
        $slug = sanitize_title($chapter_title);
        
        $data = array(
            'lesson_id' => $lesson_id,
            'chapter_title' => $chapter_title,
            'menu_order' => intval($_POST['menu_order']),
            'vimeo_id' => intval($_POST['vimeo_id']),
            'youtube_id' => sanitize_text_field($_POST['youtube_id']),
            'bunny_url' => esc_url_raw($_POST['bunny_url']),
            'duration' => intval($_POST['duration']),
            'free' => sanitize_text_field($_POST['free']),
            'slug' => $slug,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $result = $this->wpdb->insert($this->table_name, $data);
        
        if ($result) {
            $chapter_id = $this->wpdb->insert_id;
            // Check if we came from a lesson page
            $lesson_id_param = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
            if ($lesson_id_param) {
                wp_redirect(add_query_arg(array('page' => 'academy-manager-lessons', 'action' => 'edit', 'id' => $lesson_id_param, 'message' => 'chapter_added')));
            } else {
                wp_redirect(add_query_arg(array('page' => 'academy-manager-chapters', 'action' => 'edit', 'id' => $chapter_id, 'message' => 'created')));
            }
            exit;
        } else {
            wp_die(__('Error creating chapter.', 'academy-lesson-manager'));
        }
    }
    
    /**
     * Update an existing chapter
     */
    private function update_chapter() {
        $chapter_id = intval($_POST['chapter_id']);
        $chapter_title = sanitize_text_field($_POST['chapter_title']);
        $lesson_id = intval($_POST['lesson_id']);
        
        if (empty($chapter_title) || empty($lesson_id)) {
            wp_die(__('Chapter title and lesson are required.', 'academy-lesson-manager'));
        }
        
        // Generate slug from chapter title
        $slug = sanitize_title($chapter_title);
        
        $data = array(
            'lesson_id' => $lesson_id,
            'chapter_title' => $chapter_title,
            'menu_order' => intval($_POST['menu_order']),
            'vimeo_id' => intval($_POST['vimeo_id']),
            'youtube_id' => sanitize_text_field($_POST['youtube_id']),
            'bunny_url' => esc_url_raw($_POST['bunny_url']),
            'duration' => intval($_POST['duration']),
            'free' => sanitize_text_field($_POST['free']),
            'slug' => $slug,
            'updated_at' => current_time('mysql')
        );
        
        $result = $this->wpdb->update(
            $this->table_name,
            $data,
            array('ID' => $chapter_id),
            array('%d', '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_redirect(add_query_arg(array('page' => 'academy-manager-chapters', 'action' => 'edit', 'id' => $chapter_id, 'message' => 'updated')));
            exit;
        } else {
            wp_die(__('Error updating chapter.', 'academy-lesson-manager'));
        }
    }
    
    /**
     * Handle chapter deletion
     */
    private function handle_delete($chapter_id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $result = $this->wpdb->delete($this->table_name, array('ID' => $chapter_id));
        
        if ($result) {
            wp_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=academy-manager-chapters')));
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-chapters')));
            exit;
        }
    }
    
    /**
     * Render bulk update slugs button
     */
    private function render_bulk_update_slugs_button() {
        echo '<form method="post" action="" style="margin-bottom: 15px;" onsubmit="return confirm(\'' . __('Are you sure you want to update all chapter slugs? This will regenerate slugs for all chapters based on their titles.', 'academy-lesson-manager') . '\');">';
        echo '<input type="hidden" name="bulk_update_slugs" value="1" />';
        echo '<input type="submit" class="button button-secondary" value="' . __('Update All Slugs', 'academy-lesson-manager') . '" />';
        echo '</form>';
    }
    
    /**
     * Handle bulk slug update
     */
    private function handle_bulk_update_slugs() {
        // Get all chapters
        $chapters = $this->wpdb->get_results("SELECT ID, chapter_title FROM {$this->table_name}");
        
        $updated_count = 0;
        
        foreach ($chapters as $chapter) {
            // Generate slug from chapter title
            $slug = sanitize_title($chapter->chapter_title);
            
            // Update the chapter
            $result = $this->wpdb->update(
                $this->table_name,
                array('slug' => $slug, 'updated_at' => current_time('mysql')),
                array('ID' => $chapter->ID),
                array('%s', '%s'),
                array('%d')
            );
            
            if ($result !== false) {
                $updated_count++;
            }
        }
        
        // Redirect back to list page with success message
        wp_redirect(add_query_arg(array('page' => 'academy-manager-chapters', 'message' => 'slugs_updated', 'updated_count' => $updated_count)));
        exit;
    }
    
    /**
     * Handle bulk actions (delete, update slugs)
     */
    private function handle_bulk_action() {
        $action = sanitize_text_field($_POST['action']);
        $chapters = isset($_POST['chapter']) ? array_map('intval', $_POST['chapter']) : array();
        
        if (empty($chapters)) {
            wp_redirect(add_query_arg(array('page' => 'academy-manager-chapters', 'message' => 'no_selection')));
            exit;
        }
        
        if ($action === 'delete') {
            $deleted_count = 0;
            
            foreach ($chapters as $chapter_id) {
                $result = $this->wpdb->delete($this->table_name, array('ID' => $chapter_id), array('%d'));
                
                if ($result) {
                    $deleted_count++;
                }
            }
            
            // Redirect back to list page with success message
            wp_redirect(add_query_arg(array('page' => 'academy-manager-chapters', 'message' => 'bulk_deleted', 'deleted_count' => $deleted_count)));
            exit;
        } elseif ($action === 'update_slugs') {
            $updated_count = 0;
            
            foreach ($chapters as $chapter_id) {
                // Get the chapter
                $chapter = $this->wpdb->get_row($this->wpdb->prepare("SELECT chapter_title FROM {$this->table_name} WHERE ID = %d", $chapter_id));
                
                if ($chapter) {
                    // Generate slug from chapter title
                    $slug = sanitize_title($chapter->chapter_title);
                    
                    // Update the chapter
                    $result = $this->wpdb->update(
                        $this->table_name,
                        array('slug' => $slug, 'updated_at' => current_time('mysql')),
                        array('ID' => $chapter_id),
                        array('%s', '%s'),
                        array('%d')
                    );
                    
                    if ($result !== false) {
                        $updated_count++;
                    }
                }
            }
            
            // Redirect back to list page with success message
            wp_redirect(add_query_arg(array('page' => 'academy-manager-chapters', 'message' => 'bulk_slugs_updated', 'updated_count' => $updated_count)));
            exit;
        }
    }
    
    /**
     * Render database statistics
     */
    private function render_statistics() {
        // Get counts
        $chapters_count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        $lessons_table = $this->database->get_table_name('lessons');
        $lessons_count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$lessons_table}");
        
        $collections_table = $this->database->get_table_name('collections');
        $collections_count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$collections_table}");
        
        // Get average chapters per lesson
        $avg_chapters = $lessons_count > 0 ? round($chapters_count / $lessons_count, 1) : 0;

        // Get total duration in seconds across all chapters
        $total_seconds = intval($this->wpdb->get_var("SELECT COALESCE(SUM(duration),0) FROM {$this->table_name}"));
        $hours = floor($total_seconds / 3600);
        $minutes = floor(($total_seconds % 3600) / 60);
        $seconds = $total_seconds % 60;
        $hhmmss = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        
        echo '<div class="alm-statistics" style="background: #f9f9f9; border: 1px solid #ddd; padding: 15px 20px; margin-bottom: 20px; display: flex; gap: 30px; flex-wrap: wrap;">';
        
        echo '<div style="flex: 1; min-width: 150px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($chapters_count) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Chapters', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '<div style="flex: 1; min-width: 150px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($lessons_count) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Lessons', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '<div style="flex: 1; min-width: 150px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($collections_count) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Collections', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '<div style="flex: 1; min-width: 180px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($hours, 1) . '+</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Hours of Content (approx)', 'academy-lesson-manager') . '</p>';
        echo '<p style="margin: 2px 0 0 0; color: #888; font-size: 12px;">' . esc_html($hhmmss) . ' (' . number_format($total_seconds) . 's)</p>';
        echo '</div>';

        echo '<div style="flex: 1; min-width: 150px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($avg_chapters, 1) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Avg Chapters/Lesson', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '</div>';
    }
}
