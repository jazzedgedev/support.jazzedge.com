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
        // Handle bulk actions - check for bulk_action or button names
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['bulk_action']) || isset($_POST['submit_bulk_tag']) || isset($_POST['submit_bulk_style']))) {
            $this->handle_bulk_action();
            return;
        }
        
        // Handle bulk skill level update (separate from other bulk actions)
        // Check for button click OR dropdown value
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
            (isset($_POST['doaction-skill-level']) || (isset($_POST['bulk_action_skill_level']) && $_POST['bulk_action_skill_level'] !== '-1'))) {
            $this->handle_bulk_skill_level_update();
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
            case 'delete-chapter':
                $chapter_id = isset($_GET['chapter_id']) ? intval($_GET['chapter_id']) : 0;
                $lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
                $this->handle_delete_chapter($chapter_id, $lesson_id);
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
                case 'bulk_lesson_level_updated':
                    echo '<div class="notice notice-success"><p>' . __('Selected lessons\' lesson levels updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'invalid_level':
                    echo '<div class="notice notice-warning"><p>' . __('Invalid lesson level selected.', 'academy-lesson-manager') . '</p></div>';
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
                case 'collection_assigned':
                    echo '<div class="notice notice-success"><p>' . __('Lessons assigned to collection successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'no_collection_selected':
                    echo '<div class="notice notice-warning"><p>' . __('Please select a collection.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'bulk_deleted':
                    echo '<div class="notice notice-success"><p>' . __('Selected lessons deleted successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'bulk_tag_added':
                    $tag_name = isset($_GET['tag']) ? sanitize_text_field($_GET['tag']) : '';
                    $message = __('Tag added to selected lessons successfully.', 'academy-lesson-manager');
                    if (!empty($tag_name)) {
                        $message = sprintf(__('Tag "%s" added to selected lessons successfully.', 'academy-lesson-manager'), $tag_name);
                    }
                    echo '<div class="notice notice-success"><p>' . $message . '</p></div>';
                    break;
                case 'bulk_tag_error':
                    echo '<div class="notice notice-error"><p>' . __('An error occurred while adding tags to lessons.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'no_tag_selected':
                    echo '<div class="notice notice-error"><p>' . __('Please select a tag to add.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'tag_not_found':
                    echo '<div class="notice notice-error"><p>' . __('The selected tag was not found in the tags database.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'bulk_style_added':
                    $style_name = isset($_GET['style']) ? sanitize_text_field($_GET['style']) : '';
                    $message = __('Style added to selected lessons successfully.', 'academy-lesson-manager');
                    if (!empty($style_name)) {
                        $message = sprintf(__('Style "%s" added to selected lessons successfully.', 'academy-lesson-manager'), $style_name);
                    }
                    echo '<div class="notice notice-success"><p>' . $message . '</p></div>';
                    break;
                case 'bulk_style_error':
                    echo '<div class="notice notice-error"><p>' . __('An error occurred while adding styles to lessons.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'no_style_selected':
                    echo '<div class="notice notice-error"><p>' . __('Please select a style to add.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'invalid_style':
                    echo '<div class="notice notice-error"><p>' . __('The selected style is not valid.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        // Handle search and filters
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $membership_filter = isset($_GET['membership_level']) ? intval($_GET['membership_level']) : 0;
        $collection_filter = isset($_GET['collection_filter']) ? sanitize_text_field($_GET['collection_filter']) : '';
        $resources_filter = isset($_GET['resources_filter']) ? sanitize_text_field($_GET['resources_filter']) : '';
        $pathway_filter = isset($_GET['pathway_filter']) ? sanitize_key($_GET['pathway_filter']) : '';
        $skill_level_filter = isset($_GET['skill_level_filter']) ? sanitize_key($_GET['skill_level_filter']) : '';
        $tag_filter = isset($_GET['tag']) ? sanitize_text_field($_GET['tag']) : '';
        $style_filter = isset($_GET['style_filter']) ? sanitize_text_field($_GET['style_filter']) : '';
        $order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'ID';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        
        // Pagination
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 50;
        $per_page = max(10, min(200, $per_page)); // Between 10 and 200
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($paged - 1) * $per_page;
        
        // Build query
        $where_conditions = array();
        if (!empty($search)) {
            $where_conditions[] = $this->wpdb->prepare("(lesson_title LIKE %s OR lesson_description LIKE %s)", 
                '%' . $search . '%', '%' . $search . '%');
        }
        if ($membership_filter > 0) {
            $where_conditions[] = $this->wpdb->prepare("membership_level = %d", $membership_filter);
        }
        // Filter for unassigned lessons
        if ($collection_filter === 'unassigned') {
            $where_conditions[] = "(collection_id = 0 OR collection_id IS NULL)";
        } elseif (!empty($collection_filter) && is_numeric($collection_filter)) {
            // Filter by specific collection
            $where_conditions[] = $this->wpdb->prepare("collection_id = %d", intval($collection_filter));
        }
        // Filter for lessons with/without resources
        if ($resources_filter === 'has_resources') {
            $where_conditions[] = "(resources IS NOT NULL AND resources != '' AND resources != 'N;')";
        } elseif ($resources_filter === 'no_resources') {
            $where_conditions[] = "(resources IS NULL OR resources = '' OR resources = 'N;')";
        }
        
        // Filter by skill level
        if (!empty($skill_level_filter) && in_array($skill_level_filter, array('beginner', 'intermediate', 'advanced', 'pro'), true)) {
            $where_conditions[] = $this->wpdb->prepare("l.lesson_level = %s", $skill_level_filter);
        }
        
        // Filter by tag - match exact tag (handles tags at start, middle, or end of comma-separated list)
        if (!empty($tag_filter)) {
            $tag_filter_escaped = $this->wpdb->esc_like(trim($tag_filter));
            // Match: tag at start, tag in middle (preceded by ", "), or tag at end (followed by nothing or end of string)
            $where_conditions[] = $this->wpdb->prepare(
                "(l.lesson_tags = %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s)",
                $tag_filter_escaped,
                $tag_filter_escaped . ',%',
                '%, ' . $tag_filter_escaped . ',%',
                '%, ' . $tag_filter_escaped
            );
        }
        
        // Filter by style - match exact style (handles styles at start, middle, or end of comma-separated list)
        if (!empty($style_filter)) {
            $style_filter_escaped = $this->wpdb->esc_like(trim($style_filter));
            // Match: style at start, style in middle (preceded by ", "), or style at end (followed by nothing or end of string)
            $where_conditions[] = $this->wpdb->prepare(
                "(l.lesson_style = %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s)",
                $style_filter_escaped,
                $style_filter_escaped . ',%',
                '%, ' . $style_filter_escaped . ',%',
                '%, ' . $style_filter_escaped
            );
        }
        
        // Filter by pathway - need to join with lesson_pathways table
        $pathways_table = $this->database->get_table_name('lesson_pathways');
        $join_pathway = '';
        if (!empty($pathway_filter)) {
            $join_pathway = "INNER JOIN {$pathways_table} lp ON l.ID = lp.lesson_id";
            $where_conditions[] = $this->wpdb->prepare("lp.pathway = %s", $pathway_filter);
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
        
        // Get total count for pagination
        $count_sql = "SELECT COUNT(DISTINCT l.ID) FROM {$this->table_name} l {$join_pathway} {$where}";
        $total_items = intval($this->wpdb->get_var($count_sql));
        $total_pages = ceil($total_items / $per_page);
        
        // Use table alias 'l' for lessons table with pagination
        $sql = "SELECT DISTINCT l.* FROM {$this->table_name} l {$join_pathway} {$where} ORDER BY l.{$order_by} {$order} LIMIT %d OFFSET %d";
        $lessons = $this->wpdb->get_results($this->wpdb->prepare($sql, $per_page, $offset));
        
        // Render statistics
        $this->render_statistics();
        
        // Render search form (outside bulk actions form)
        $this->render_search_form($search, $membership_filter, $collection_filter, $resources_filter, $pathway_filter, $skill_level_filter, $tag_filter, $style_filter);
        
        // Open bulk actions form
        echo '<form method="post" action="" id="bulk-actions-form">';
        wp_nonce_field('bulk-lessons');
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
        echo '<input type="submit" class="button" value="' . __('Update Membership', 'academy-lesson-manager') . '" onclick="return confirm(\'' . __('Are you sure you want to update the membership levels for the selected lessons?', 'academy-lesson-manager') . '\')" />';
        echo '</p>';
        echo '<input type="hidden" id="bulk_action" name="bulk_action" value="update_membership" />';
        
        echo '<p style="margin-top: 10px;">';
        echo '<label for="bulk_collection_id">' . __('Assign to collection:', 'academy-lesson-manager') . '</label> ';
        echo '<select id="bulk_collection_id" name="bulk_collection_id" style="margin-left: 10px;">';
        echo '<option value="0">' . __('Select collection...', 'academy-lesson-manager') . '</option>';
        $collections_table = $this->database->get_table_name('collections');
        $collections = $this->wpdb->get_results("SELECT ID, collection_title FROM $collections_table ORDER BY collection_title ASC");
        foreach ($collections as $collection) {
            echo '<option value="' . esc_attr($collection->ID) . '">' . esc_html(stripslashes($collection->collection_title)) . '</option>';
        }
        echo '</select>';
        echo '<input type="submit" class="button" name="submit_collection" value="' . __('Assign to Collection', 'academy-lesson-manager') . '" onclick="document.getElementById(\'bulk_action\').value=\'assign_collection\'; return confirm(\'' . __('Are you sure you want to assign the selected lessons to this collection?', 'academy-lesson-manager') . '\');" />';
        echo '</p>';
        
        echo '<p class="description">' . __('Select lessons from the list below, then choose a membership level and click "Update Selected Lessons".', 'academy-lesson-manager') . '</p>';
        
        // Bulk tag addition
        echo '<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">';
        echo '<label for="bulk_tag_add" style="font-weight: 600; margin-right: 8px;">' . __('Add Tag to Selected Lessons:', 'academy-lesson-manager') . '</label>';
        $tags_table = $this->database->get_table_name('tags');
        $all_tags = $this->wpdb->get_results("SELECT tag_name FROM {$tags_table} ORDER BY tag_name ASC");
        echo '<select id="bulk_tag_add" name="bulk_tag_add" style="margin-right: 8px;">';
        echo '<option value="">' . __('Select tag to add...', 'academy-lesson-manager') . '</option>';
        foreach ($all_tags as $tag) {
            echo '<option value="' . esc_attr($tag->tag_name) . '">' . esc_html($tag->tag_name) . '</option>';
        }
        echo '</select>';
        echo '<input type="submit" class="button" name="submit_bulk_tag" value="' . __('Add Tag to Selected', 'academy-lesson-manager') . '" onclick="document.getElementById(\'bulk_action\').value=\'add_tag\'; var checkboxes = document.querySelectorAll(\'#bulk-actions-form-table input[name=\\\'lesson[]\\\']:checked\'); if (checkboxes.length === 0) { alert(\'' . __('Please select at least one lesson.', 'academy-lesson-manager') . '\'); return false; } var form = document.getElementById(\'bulk-actions-form\'); var existingHidden = document.getElementById(\'bulk_selected_lessons_container\'); if (existingHidden) { existingHidden.remove(); } var container = document.createElement(\'div\'); container.id = \'bulk_selected_lessons_container\'; container.style.display = \'none\'; Array.from(checkboxes).forEach(function(cb) { var hidden = document.createElement(\'input\'); hidden.type = \'hidden\'; hidden.name = \'lesson[]\'; hidden.value = cb.value; container.appendChild(hidden); }); form.appendChild(container); return confirm(\'' . __('Are you sure you want to add this tag to the selected lessons? Existing tags will be preserved.', 'academy-lesson-manager') . '\');" />';
        echo '<p class="description" style="margin-top: 8px;">' . __('This will add the selected tag to all selected lessons without removing existing tags.', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        // Bulk style addition
        echo '<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">';
        echo '<label for="bulk_style_add" style="font-weight: 600; margin-right: 8px;">' . __('Add Style to Selected Lessons:', 'academy-lesson-manager') . '</label>';
        $styles = array('Any', 'Jazz', 'Cocktail', 'Blues', 'Rock', 'Funk', 'Latin', 'Classical', 'Smooth Jazz', 'Holiday', 'Ballad', 'Pop', 'New Age', 'Gospel', 'New Orleans', 'Country', 'Modal', 'Stride', 'Organ', 'Boogie');
        echo '<select id="bulk_style_add" name="bulk_style_add" style="margin-right: 8px;">';
        echo '<option value="">' . __('Select style to add...', 'academy-lesson-manager') . '</option>';
        foreach ($styles as $style) {
            echo '<option value="' . esc_attr($style) . '">' . esc_html($style) . '</option>';
        }
        echo '</select>';
        echo '<input type="submit" class="button" name="submit_bulk_style" value="' . __('Add Style to Selected', 'academy-lesson-manager') . '" onclick="document.getElementById(\'bulk_action\').value=\'add_style\'; var checkboxes = document.querySelectorAll(\'#bulk-actions-form-table input[name=\\\'lesson[]\\\']:checked\'); if (checkboxes.length === 0) { alert(\'' . __('Please select at least one lesson.', 'academy-lesson-manager') . '\'); return false; } var form = document.getElementById(\'bulk-actions-form\'); var existingHidden = document.getElementById(\'bulk_selected_lessons_container\'); if (existingHidden) { existingHidden.remove(); } var container = document.createElement(\'div\'); container.id = \'bulk_selected_lessons_container\'; container.style.display = \'none\'; Array.from(checkboxes).forEach(function(cb) { var hidden = document.createElement(\'input\'); hidden.type = \'hidden\'; hidden.name = \'lesson[]\'; hidden.value = cb.value; container.appendChild(hidden); }); form.appendChild(container); return confirm(\'' . __('Are you sure you want to add this style to the selected lessons? Existing styles will be preserved.', 'academy-lesson-manager') . '\');" />';
        echo '<p class="description" style="margin-top: 8px;">' . __('This will add the selected style to all selected lessons without removing existing styles.', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '<p style="margin-top: 15px; border-top: 1px solid #ddd; padding-top: 15px;">';
        echo '<button type="submit" class="button button-large" name="bulk_delete" style="color: #a00; border-color: #dc3232;" onclick="document.getElementById(\'bulk_action\').value=\'delete\'; return confirm(\'' . __('Are you sure you want to DELETE the selected lessons? This action cannot be undone and will also delete associated WordPress posts.', 'academy-lesson-manager') . '\');">' . __('Delete Selected Lessons', 'academy-lesson-manager') . '</button>';
        echo ' <a href="?page=academy-manager-lessons&action=sync_all" class="button button-large" onclick="return confirm(\'' . __('This will sync all lessons to WordPress posts. Continue?', 'academy-lesson-manager') . '\')">' . __('Sync All Lessons', 'academy-lesson-manager') . '</a>';
        echo '</p>';
        
        echo '</div>';
        
        // Close the first form (bulk actions box)
        echo '</form>';
        
        // Render column visibility controls (outside form)
        $this->render_column_visibility_controls();
        
        // Render pagination info (outside POST form to avoid nested forms)
        $this->render_pagination_info($total_items, $paged, $per_page, $total_pages);
        
        // Open NEW form for table and skill level dropdown (they need to be together)
        echo '<form method="post" action="" id="bulk-actions-form-table">';
        
        // WordPress-style bulk actions dropdown for skill level (above table, INSIDE the table form)
        echo '<div class="tablenav top" style="margin-bottom: 10px;">';
        echo '<div class="alignleft actions bulkactions">';
        echo '<select name="bulk_action_skill_level" id="bulk-action-skill-level-selector-top" style="margin-right: 5px;">';
        echo '<option value="-1">' . __('Set Skill Level...', 'academy-lesson-manager') . '</option>';
        $level_options = array(
            'beginner' => __('Beginner', 'academy-lesson-manager'),
            'intermediate' => __('Intermediate', 'academy-lesson-manager'),
            'advanced' => __('Advanced', 'academy-lesson-manager'),
            'pro' => __('Pro', 'academy-lesson-manager'),
            'clear' => __('Clear Skill Level', 'academy-lesson-manager')
        );
        foreach ($level_options as $value => $label) {
            echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<input type="submit" class="button action" id="doaction-skill-level" name="doaction-skill-level" value="' . __('Apply', 'academy-lesson-manager') . '" />';
        echo '</div>';
        echo '</div>';
        
        // Render lessons table (inside the same form as the skill level dropdown)
        $this->render_lessons_table($lessons, $order_by, $order);
        
        // Close the table form
        echo '</form>';
        
        // Render pagination controls (below table, outside form to avoid nested forms)
        $this->render_pagination_controls($paged, $total_pages, $per_page, $search, $membership_filter, $collection_filter, $resources_filter, $pathway_filter, $skill_level_filter, $order_by, $order);
        
        // JavaScript for quick pathway addition
        $pathways_json = json_encode(ALM_Admin_Settings::get_pathways());
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('alm_quick_add_pathway');
        
        echo '<script>
        jQuery(document).ready(function($) {
            var pathways = ' . $pathways_json . ';
            var ajaxUrl = "' . esc_js($ajax_url) . '";
            var nonce = "' . esc_js($nonce) . '";
            
            $(document).on("click", ".alm-quick-add-pathway", function() {
                var lessonId = $(this).data("lesson-id");
                var button = $(this);
                
                // Create dropdown dialog
                var dialog = $("<div>").css({
                    "position": "fixed",
                    "top": "50%",
                    "left": "50%",
                    "transform": "translate(-50%, -50%)",
                    "background": "#fff",
                    "padding": "20px",
                    "border": "2px solid #239B90",
                    "border-radius": "8px",
                    "box-shadow": "0 4px 12px rgba(0,0,0,0.3)",
                    "z-index": "100000",
                    "min-width": "300px"
                });
                
                var form = $("<form>");
                form.append($("<label>").text("Pathway:").css({"display": "block", "margin-bottom": "8px", "font-weight": "600"}));
                var pathwaySelect = $("<select>").attr("name", "pathway").css({"width": "100%", "padding": "8px", "margin-bottom": "16px"});
                pathwaySelect.append($("<option>").val("").text("Select pathway..."));
                $.each(pathways, function(key, name) {
                    pathwaySelect.append($("<option>").val(key).text(name));
                });
                form.append(pathwaySelect);
                
                form.append($("<label>").text("Rank (1-5):").css({"display": "block", "margin-bottom": "8px", "font-weight": "600"}));
                var rankSelect = $("<select>").attr("name", "rank").css({"width": "100%", "padding": "8px", "margin-bottom": "16px"});
                for (var i = 1; i <= 5; i++) {
                    rankSelect.append($("<option>").val(i).text(i));
                }
                form.append(rankSelect);
                
                var buttonRow = $("<div>").css({"display": "flex", "gap": "8px", "justify-content": "flex-end"});
                var saveBtn = $("<button>").text("Add").attr("type", "button").addClass("button button-primary");
                var cancelBtn = $("<button>").text("Cancel").attr("type", "button").addClass("button").css({"margin-right": "8px"});
                buttonRow.append(cancelBtn).append(saveBtn);
                form.append(buttonRow);
                dialog.append(form);
                
                var overlay = $("<div>").css({
                    "position": "fixed",
                    "top": "0",
                    "left": "0",
                    "width": "100%",
                    "height": "100%",
                    "background": "rgba(0,0,0,0.5)",
                    "z-index": "99999"
                });
                
                $("body").append(overlay).append(dialog);
                
                cancelBtn.on("click", function() {
                    overlay.remove();
                    dialog.remove();
                });
                
                saveBtn.on("click", function() {
                    var pathway = pathwaySelect.val();
                    var rank = parseInt(rankSelect.val());
                    
                    if (!pathway) {
                        alert("Please select a pathway");
                        return;
                    }
                    
                    $.ajax({
                        url: ajaxUrl,
                        type: "POST",
                        data: {
                            action: "alm_quick_add_pathway",
                            nonce: nonce,
                            lesson_id: lessonId,
                            pathway: pathway,
                            rank: rank
                        },
                        success: function(response) {
                            if (response.success) {
                                overlay.remove();
                                dialog.remove();
                                // Reload page to show updated pathways
                                location.reload();
                            } else {
                                alert("Error: " + (response.data || "Failed to add pathway"));
                            }
                        },
                        error: function() {
                            alert("Error: Failed to communicate with server");
                        }
                    });
                });
            });
        });
        </script>';
        
    }
    
    /**
     * Render search form
     */
    private function render_search_form($search, $membership_filter = 0, $collection_filter = '', $resources_filter = '', $pathway_filter = '', $skill_level_filter = '', $tag_filter = '', $style_filter = '') {
        echo '<div class="alm-search-filters" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">';
        echo '<style>
        .alm-search-filters .alm-filter-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .alm-search-filters .alm-filter-row:last-child {
            margin-bottom: 0;
        }
        .alm-search-filters label {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            min-width: 120px;
            flex-shrink: 0;
        }
        .alm-search-filters select,
        .alm-search-filters input[type="search"] {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            min-width: 200px;
            background: #fff;
        }
        .alm-search-filters select:focus,
        .alm-search-filters input[type="search"]:focus {
            outline: none;
            border-color: #239B90;
            box-shadow: 0 0 0 3px rgba(35, 155, 144, 0.1);
        }
        .alm-search-filters .button {
            padding: 8px 16px;
            height: auto;
            border-radius: 6px;
            font-weight: 500;
        }
        .alm-search-filters .button-clear {
            color: #dc3232;
            border-color: #dc3232;
        }
        .alm-search-filters .button-clear:hover {
            background: #dc3232;
            color: #fff;
        }
        </style>';
        
        // Single form for all filters
        echo '<form method="get" action="" class="alm-filter-form">';
        echo '<input type="hidden" name="page" value="academy-manager-lessons" />';
        // Preserve pagination if set
        if (isset($_GET['paged']) && intval($_GET['paged']) > 1) {
            echo '<input type="hidden" name="paged" value="' . esc_attr(intval($_GET['paged'])) . '" />';
        }
        if (isset($_GET['per_page']) && intval($_GET['per_page']) > 0) {
            echo '<input type="hidden" name="per_page" value="' . esc_attr(intval($_GET['per_page'])) . '" />';
        }
        
        // Search box row
        echo '<div class="alm-filter-row">';
        echo '<label for="lesson-search-input">' . __('Search:', 'academy-lesson-manager') . '</label>';
        echo '<input type="search" id="lesson-search-input" name="search" value="' . esc_attr($search) . '" placeholder="' . __('Search lessons...', 'academy-lesson-manager') . '" style="flex: 1; max-width: 400px;" />';
        echo '<input type="submit" class="button button-primary" value="' . __('Search', 'academy-lesson-manager') . '" />';
        echo '</div>';
        
        // Filters row
        echo '<div class="alm-filter-row">';
        
        // Membership level filter
        echo '<label for="membership-filter">' . __('Membership:', 'academy-lesson-manager') . '</label>';
        echo '<select id="membership-filter" name="membership_level" onchange="this.form.submit()">';
        echo '<option value="">' . __('All Levels', 'academy-lesson-manager') . '</option>';
        $membership_levels = ALM_Admin_Settings::get_membership_levels();
        foreach ($membership_levels as $key => $level) {
            $selected = ($membership_filter == $level['numeric']) ? 'selected' : '';
            $lesson_count = $this->get_lesson_count_by_membership($level['numeric']);
            echo '<option value="' . esc_attr($level['numeric']) . '" ' . $selected . '>' . esc_html($level['name']) . ' (' . $lesson_count . ')</option>';
        }
        echo '</select>';
        
        // Collection filter
        echo '<label for="collection-filter" style="margin-left: 8px;">' . __('Collection:', 'academy-lesson-manager') . '</label>';
        echo '<select id="collection-filter" name="collection_filter" onchange="this.form.submit()">';
        echo '<option value="">' . __('All', 'academy-lesson-manager') . '</option>';
        $unassigned_count = $this->get_unassigned_lesson_count();
        echo '<option value="unassigned" ' . selected($collection_filter, 'unassigned', false) . '>' . sprintf(__('Not Assigned (%d)', 'academy-lesson-manager'), $unassigned_count) . '</option>';
        echo '<option disabled>──────────</option>';
        $collections_table = $this->database->get_table_name('collections');
        $collections = $this->wpdb->get_results("SELECT ID, collection_title FROM $collections_table ORDER BY collection_title ASC");
        foreach ($collections as $collection) {
            $collection_id = intval($collection->ID);
            $lesson_count = $this->get_lesson_count_by_collection($collection_id);
            $selected = ($collection_filter == $collection_id) ? 'selected' : '';
            echo '<option value="' . esc_attr($collection_id) . '" ' . $selected . '>' . esc_html(stripslashes($collection->collection_title)) . ' (' . $lesson_count . ')</option>';
        }
        echo '</select>';
        
        // Pathway filter
        echo '<label for="pathway-filter" style="margin-left: 8px;">' . __('Pathway:', 'academy-lesson-manager') . '</label>';
        echo '<select id="pathway-filter" name="pathway_filter" onchange="this.form.submit()">';
        echo '<option value="">' . __('All Pathways', 'academy-lesson-manager') . '</option>';
        $pathways = ALM_Admin_Settings::get_pathways();
        foreach ($pathways as $pathway_key => $pathway_name) {
            $selected = ($pathway_filter == $pathway_key) ? 'selected' : '';
            $lesson_count = $this->get_lesson_count_by_pathway($pathway_key);
            echo '<option value="' . esc_attr($pathway_key) . '" ' . $selected . '>' . esc_html($pathway_name) . ' (' . $lesson_count . ')</option>';
        }
        echo '</select>';
        
        // Skill Level filter
        echo '<label for="skill-level-filter" style="margin-left: 8px;">' . __('Skill Level:', 'academy-lesson-manager') . '</label>';
        echo '<select id="skill-level-filter" name="skill_level_filter" onchange="this.form.submit()">';
        echo '<option value="">' . __('All Levels', 'academy-lesson-manager') . '</option>';
        $skill_levels = array(
            'beginner' => __('Beginner', 'academy-lesson-manager'),
            'intermediate' => __('Intermediate', 'academy-lesson-manager'),
            'advanced' => __('Advanced', 'academy-lesson-manager'),
            'pro' => __('Pro', 'academy-lesson-manager')
        );
        foreach ($skill_levels as $level_key => $level_name) {
            $selected = ($skill_level_filter == $level_key) ? 'selected' : '';
            $lesson_count = $this->get_lesson_count_by_skill_level($level_key);
            echo '<option value="' . esc_attr($level_key) . '" ' . $selected . '>' . esc_html($level_name) . ' (' . $lesson_count . ')</option>';
        }
        echo '</select>';
        
        // Resources filter
        echo '<label for="resources-filter" style="margin-left: 8px;">' . __('Resources:', 'academy-lesson-manager') . '</label>';
        echo '<select id="resources-filter" name="resources_filter" onchange="this.form.submit()">';
        echo '<option value="">' . __('All', 'academy-lesson-manager') . '</option>';
        $has_resources_count = $this->get_lesson_count_with_resources();
        $no_resources_count = $this->get_lesson_count_without_resources();
        echo '<option value="has_resources" ' . selected($resources_filter, 'has_resources', false) . '>' . sprintf(__('Has Resources (%d)', 'academy-lesson-manager'), $has_resources_count) . '</option>';
        echo '<option value="no_resources" ' . selected($resources_filter, 'no_resources', false) . '>' . sprintf(__('No Resources (%d)', 'academy-lesson-manager'), $no_resources_count) . '</option>';
        echo '</select>';
        
        // Style filter
        echo '<label for="style-filter" style="margin-left: 8px;">' . __('Style:', 'academy-lesson-manager') . '</label>';
        echo '<select id="style-filter" name="style_filter" onchange="this.form.submit()">';
        echo '<option value="">' . __('All Styles', 'academy-lesson-manager') . '</option>';
        $styles = array('Any', 'Jazz', 'Cocktail', 'Blues', 'Rock', 'Funk', 'Latin', 'Classical', 'Smooth Jazz', 'Holiday', 'Ballad', 'Pop', 'New Age', 'Gospel', 'New Orleans', 'Country', 'Modal', 'Stride', 'Organ', 'Boogie');
        foreach ($styles as $style) {
            $selected = ($style_filter == $style) ? 'selected' : '';
            $lesson_count = $this->get_lesson_count_by_style($style);
            echo '<option value="' . esc_attr($style) . '" ' . $selected . '>' . esc_html($style) . ' (' . $lesson_count . ')</option>';
        }
        echo '</select>';
        
        echo '</div>';
        
        // Show active tag filter
        if (!empty($tag_filter)) {
            echo '<div class="alm-filter-row">';
            echo '<label>' . __('Active Tag Filter:', 'academy-lesson-manager') . '</label>';
            echo '<span style="padding: 6px 12px; background: #239B90; color: #fff; border-radius: 4px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">';
            echo esc_html($tag_filter);
            echo '<a href="?page=academy-manager-lessons' . (!empty($search) ? '&search=' . urlencode($search) : '') . '" style="color: #fff; text-decoration: none; font-size: 18px; line-height: 1;" title="' . __('Remove tag filter', 'academy-lesson-manager') . '">&times;</a>';
            echo '</span>';
            echo '</div>';
        }
        
        // Show active style filter
        if (!empty($style_filter)) {
            echo '<div class="alm-filter-row">';
            echo '<label>' . __('Active Style Filter:', 'academy-lesson-manager') . '</label>';
            echo '<span style="padding: 6px 12px; background: #239B90; color: #fff; border-radius: 4px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">';
            echo esc_html($style_filter);
            echo '<a href="?page=academy-manager-lessons' . (!empty($search) ? '&search=' . urlencode($search) : '') . '" style="color: #fff; text-decoration: none; font-size: 18px; line-height: 1;" title="' . __('Remove style filter', 'academy-lesson-manager') . '">&times;</a>';
            echo '</span>';
            echo '</div>';
        }
        
        // Clear filters button
        if ($membership_filter > 0 || !empty($collection_filter) || !empty($resources_filter) || !empty($pathway_filter) || !empty($skill_level_filter) || !empty($tag_filter) || !empty($style_filter)) {
            echo '<div class="alm-filter-row">';
            echo '<a href="?page=academy-manager-lessons' . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="button button-clear">' . __('Clear All Filters', 'academy-lesson-manager') . '</a>';
            echo '</div>';
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
        echo '<th scope="col" class="manage-column column-id column-id">' . $this->get_sortable_header('ID', 'ID', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column column-post-id column-post-id">' . __('Post ID', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-date column-date">' . $this->get_sortable_header('Date', 'post_date', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column column-title column-title">' . $this->get_sortable_header('Lesson Title', 'lesson_title', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column column-course column-course">' . __('Collection', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-duration column-duration">' . __('Duration', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-membership column-membership">' . __('Memb. Level', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-skill-level column-skill-level">' . __('Skill Level', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-pathways column-pathways" style="width: 200px;">' . __('Pathways', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-tags column-tags" style="width: 200px;">' . __('Tags', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-lesson-style column-lesson-style" style="width: 150px;">' . __('Lesson Style', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-resources column-resources" style="text-align: center;">' . __('Resources', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-actions column-actions">' . __('Actions', 'academy-lesson-manager') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($lessons)) {
            echo '<tr><td colspan="13" class="no-items">' . __('No lessons found.', 'academy-lesson-manager') . '</td></tr>';
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
        // Check if lesson is assigned to a collection
        $is_unassigned = empty($lesson->collection_id) || $lesson->collection_id == 0;
        
        $collection_title = ALM_Helpers::get_collection_title($lesson->collection_id);
        
        // Add visual indicator for unassigned lessons
        $row_style = $is_unassigned ? 'background-color: #fff4e5;' : '';
        
        echo '<tr style="' . esc_attr($row_style) . '">';
        echo '<th scope="row" class="check-column"><input type="checkbox" name="lesson[]" value="' . $lesson->ID . '" /></th>';
        echo '<td class="column-id">' . $lesson->ID . '</td>';
        echo '<td class="column-post-id">' . ($lesson->post_id ? $lesson->post_id : '—') . '</td>';
        echo '<td class="column-date">' . ALM_Helpers::format_date($lesson->post_date) . '</td>';
        echo '<td class="column-title"><strong>' . esc_html(stripslashes($lesson->lesson_title)) . '</strong>' . ($is_unassigned ? ' <span style="color: #d63638; font-size: 11px;">(' . __('Not Assigned', 'academy-lesson-manager') . ')</span>' : '') . '</td>';
        echo '<td class="column-course">';
        if ($is_unassigned) {
            echo '<span style="color: #d63638; font-style: italic;">— ' . __('Not Assigned', 'academy-lesson-manager') . ' —</span>';
        } else {
            echo '<a href="?page=academy-manager&action=edit&id=' . $lesson->collection_id . '">' . esc_html($collection_title) . '</a>';
        }
        echo '</td>';
        echo '<td class="column-duration">' . ALM_Helpers::format_duration($lesson->duration) . '</td>';
        echo '<td class="column-membership">' . esc_html(ALM_Admin_Settings::get_membership_level_name($lesson->membership_level)) . '</td>';
        
        // Skill Level
        echo '<td class="column-skill-level">';
        $skill_levels = array(
            'beginner' => __('Beginner', 'academy-lesson-manager'),
            'intermediate' => __('Intermediate', 'academy-lesson-manager'),
            'advanced' => __('Advanced', 'academy-lesson-manager'),
            'pro' => __('Pro', 'academy-lesson-manager')
        );
        if (!empty($lesson->lesson_level) && isset($skill_levels[$lesson->lesson_level])) {
            $level_name = $skill_levels[$lesson->lesson_level];
            $level_colors = array(
                'beginner' => '#46b450',
                'intermediate' => '#239B90',
                'advanced' => '#f0ad4e',
                'pro' => '#dc3232'
            );
            $color = isset($level_colors[$lesson->lesson_level]) ? $level_colors[$lesson->lesson_level] : '#666';
            echo '<span style="background: ' . esc_attr($color) . '; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-block;">' . esc_html($level_name) . '</span>';
        } else {
            echo '<span style="color: #999; font-size: 12px;">—</span>';
        }
        echo '</td>';
        
        // Pathway quick edit
        echo '<td class="column-pathways">';
        $pathways_table = $this->database->get_table_name('lesson_pathways');
        $lesson_pathways = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT pathway, pathway_rank FROM {$pathways_table} WHERE lesson_id = %d ORDER BY pathway_rank ASC, pathway ASC LIMIT 3",
            $lesson->ID
        ));
        
        if (!empty($lesson_pathways)) {
            $pathway_names = ALM_Admin_Settings::get_pathways();
            foreach ($lesson_pathways as $lp) {
                $pathway_name = isset($pathway_names[$lp->pathway]) ? $pathway_names[$lp->pathway] : $lp->pathway;
                echo '<div style="margin-bottom: 4px; font-size: 12px;">';
                echo '<span style="background: #239B90; color: white; padding: 2px 6px; border-radius: 3px; display: inline-block; margin-right: 4px;">' . esc_html($pathway_name) . '</span>';
                echo '<span style="color: #666;">Rank: ' . esc_html($lp->pathway_rank) . '</span>';
                echo '</div>';
            }
            $total_pathways = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$pathways_table} WHERE lesson_id = %d",
                $lesson->ID
            ));
            if ($total_pathways > 3) {
                echo '<span style="color: #999; font-size: 11px;">+' . ($total_pathways - 3) . ' more</span>';
            }
        } else {
            echo '<span style="color: #999; font-size: 12px;">—</span>';
        }
        
        // Quick add pathway button
        echo '<div style="margin-top: 4px;">';
        echo '<button type="button" class="button button-small alm-quick-add-pathway" data-lesson-id="' . $lesson->ID . '" style="font-size: 11px; padding: 2px 8px; height: auto; line-height: 1.5;">' . __('+ Add', 'academy-lesson-manager') . '</button>';
        echo '</div>';
        echo '</td>';
        
        // Tags column
        echo '<td class="column-tags">';
        if (!empty($lesson->lesson_tags)) {
            $tags = array_map('trim', explode(',', $lesson->lesson_tags));
            $tags = array_filter($tags);
            if (!empty($tags)) {
                echo '<div style="display: flex; flex-wrap: wrap; gap: 4px;">';
                foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag)) {
                        $filter_url = admin_url('admin.php?page=academy-manager-lessons&tag=' . urlencode($tag));
                        echo '<a href="' . esc_url($filter_url) . '" style="background: #f0f0f0; color: #333; padding: 2px 8px; border-radius: 3px; font-size: 11px; text-decoration: none; display: inline-block; margin-bottom: 2px;" title="' . esc_attr($tag) . '">' . esc_html($tag) . '</a>';
                    }
                }
                echo '</div>';
            } else {
                echo '<span style="color: #999; font-size: 12px;">—</span>';
            }
        } else {
            echo '<span style="color: #999; font-size: 12px;">—</span>';
        }
        echo '</td>';
        
        // Lesson Style column
        echo '<td class="column-lesson-style">';
        if (!empty($lesson->lesson_style)) {
            $styles = array_map('trim', explode(',', $lesson->lesson_style));
            $styles = array_filter($styles);
            if (!empty($styles)) {
                echo '<div style="display: flex; flex-wrap: wrap; gap: 4px;">';
                foreach ($styles as $style) {
                    $style = trim($style);
                    if (!empty($style)) {
                        $filter_url = admin_url('admin.php?page=academy-manager-lessons&style=' . urlencode($style));
                        echo '<a href="' . esc_url($filter_url) . '" style="background: #e3f2fd; color: #1976d2; padding: 2px 8px; border-radius: 3px; font-size: 11px; text-decoration: none; display: inline-block; margin-bottom: 2px;" title="' . esc_attr($style) . '">' . esc_html($style) . '</a>';
                    }
                }
                echo '</div>';
            } else {
                echo '<span style="color: #999; font-size: 12px;">—</span>';
            }
        } else {
            echo '<span style="color: #999; font-size: 12px;">—</span>';
        }
        echo '</td>';
        
        echo '<td class="column-resources" style="text-align: center;">';
        $resources = ALM_Helpers::format_serialized_resources($lesson->resources);
        if (!empty($resources)) {
            echo '<span style="color: #46b450; font-weight: bold;">✓ ' . __('Yes', 'academy-lesson-manager') . '</span>';
        } else {
            echo '<span style="color: #dc3232;">✗ ' . __('No', 'academy-lesson-manager') . '</span>';
        }
        echo '</td>';
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
        
        // Get lesson URL for copy button
        $lesson_url = '';
        if ($lesson->post_id) {
            $lesson_url = get_permalink($lesson->post_id);
        }
        
        // Back button and actions
        echo '<p>';
        echo '<a href="?page=academy-manager-lessons" class="button">&larr; ' . __('Back to Lessons', 'academy-lesson-manager') . '</a> ';
        
        // Add Copy URL button if post exists
        if (!empty($lesson_url)) {
            echo '<button type="button" class="button alm-copy-url-btn" data-url="' . esc_attr($lesson_url) . '" title="' . __('Copy Lesson URL', 'academy-lesson-manager') . '"><span class="dashicons dashicons-admin-page"></span> ' . __('Copy URL', 'academy-lesson-manager') . '</button> ';
        }
        
        // Add WordPress Edit button if post exists
        if ($lesson->post_id) {
            echo '<a href="' . get_edit_post_link($lesson->post_id) . '" class="button" target="_blank" title="' . __('Edit in WordPress', 'academy-lesson-manager') . '"><span class="dashicons dashicons-edit"></span> ' . __('WordPress Edit', 'academy-lesson-manager') . '</a> ';
        }
        
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
                case 'chapter-deleted':
                    echo '<div class="notice notice-success"><p>' . __('Chapter deleted successfully.', 'academy-lesson-manager') . '</p></div>';
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
        
        // Add post status field
        echo '<tr>';
        echo '<th scope="row"><label>' . __('Post Status', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        if ($lesson->post_id) {
            $post = get_post($lesson->post_id);
            if ($post) {
                $status = $post->post_status;
                $status_color = ($status === 'publish') ? '#46b450' : (($status === 'draft') ? '#dc3232' : '#ffb900');
                echo '<span style="color: ' . $status_color . '; font-weight: bold;">' . esc_html(ucfirst($status)) . '</span>';
                if ($status === 'future') {
                    echo '<p class="description">' . __('This post is scheduled for a future date.', 'academy-lesson-manager') . '</p>';
                }
            } else {
                echo '<span style="color: #dc3232;">' . __('No WordPress post found', 'academy-lesson-manager') . '</span>';
            }
        } else {
            echo '<span style="color: #666;">' . __('No post ID set', 'academy-lesson-manager') . '</span>';
        }
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="slug">' . __('Slug', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="text" id="slug" name="slug" value="' . esc_attr($lesson->slug) . '" class="regular-text" /></td>';
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
        
        // Add link to edit collection if lesson belongs to a collection
        if (!empty($lesson->collection_id)) {
            echo ' <a href="?page=academy-manager&action=edit&id=' . $lesson->collection_id . '" class="button button-small" style="margin-left: 10px;" title="' . __('Edit Collection', 'academy-lesson-manager') . '"><span class="dashicons dashicons-external"></span> ' . __('Edit Collection', 'academy-lesson-manager') . '</a>';
        }
        
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
        echo ' <button type="button" class="button button-small" id="alm-update-duration-btn" style="margin-left: 10px;">' . __('Update from Chapters', 'academy-lesson-manager') . '</button>';
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
        echo '<th scope="row"><label for="lesson_level">' . __('Lesson Level', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="lesson_level" name="lesson_level">';
        echo '<option value="">' . __('Select level...', 'academy-lesson-manager') . '</option>';
        $level_options = array(
            'beginner' => __('Beginner', 'academy-lesson-manager'),
            'intermediate' => __('Intermediate', 'academy-lesson-manager'),
            'advanced' => __('Advanced', 'academy-lesson-manager'),
            'pro' => __('Pro', 'academy-lesson-manager')
        );
        foreach ($level_options as $value => $label) {
            $selected = (isset($lesson->lesson_level) && $lesson->lesson_level === $value) ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Skill level required for this lesson.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="lesson_tags">' . __('Lesson Tags', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        
        // Get all tags from database
        $tags_table = $this->database->get_table_name('tags');
        $all_tags = $this->wpdb->get_results("SELECT tag_name FROM {$tags_table} ORDER BY tag_name ASC");
        
        // Get current lesson tags
        $current_tags = array();
        if (!empty($lesson->lesson_tags)) {
            $current_tags = array_map('trim', explode(',', $lesson->lesson_tags));
            $current_tags = array_filter($current_tags);
        }
        
        // Multi-select dropdown
        echo '<select id="lesson_tags" name="lesson_tags[]" multiple="multiple" class="regular-text" style="min-height: 150px; width: 100%; max-width: 500px;">';
        foreach ($all_tags as $tag) {
            $tag_name = $tag->tag_name;
            $selected = in_array($tag_name, $current_tags) ? 'selected' : '';
            echo '<option value="' . esc_attr($tag_name) . '" ' . $selected . '>' . esc_html($tag_name) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Hold Ctrl (Cmd on Mac) to select multiple tags. Tags are normalized from the tags database.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Lesson Style field
        echo '<tr>';
        echo '<th scope="row"><label for="lesson_style">' . __('Lesson Style', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        
        // Get current lesson styles
        $current_styles = array();
        if (!empty($lesson->lesson_style)) {
            $current_styles = array_map('trim', explode(',', $lesson->lesson_style));
            $current_styles = array_filter($current_styles);
        }
        
        $styles = array('Any', 'Jazz', 'Cocktail', 'Blues', 'Rock', 'Funk', 'Latin', 'Classical', 'Smooth Jazz', 'Holiday', 'Ballad', 'Pop', 'New Age', 'Gospel', 'New Orleans', 'Country', 'Modal', 'Stride', 'Organ', 'Boogie');
        
        // Multi-select dropdown
        echo '<select id="lesson_style" name="lesson_style[]" multiple="multiple" class="regular-text" style="min-height: 150px; width: 100%; max-width: 500px;">';
        foreach ($styles as $style) {
            $selected = in_array($style, $current_styles) ? 'selected' : '';
            echo '<option value="' . esc_attr($style) . '" ' . $selected . '>' . esc_html($style) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Hold Ctrl (Cmd on Mac) to select multiple styles.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Pathway assignments (multiple pathways with ranks)
        echo '<tr>';
        echo '<th scope="row"><label>' . __('AI Pathways', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<div id="alm-pathways-container">';
        
        // Get current pathway assignments
        $pathways_table = $this->database->get_table_name('lesson_pathways');
        $current_pathways = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT pathway, pathway_rank FROM {$pathways_table} WHERE lesson_id = %d ORDER BY pathway_rank ASC, pathway ASC",
            $lesson->ID
        ));
        
        // Get available pathway options from settings
        $pathway_options = ALM_Admin_Settings::get_pathways();
        
        // Display existing pathway assignments
        if (!empty($current_pathways)) {
            foreach ($current_pathways as $idx => $pathway_row) {
                $pathway_value = $pathway_row->pathway;
                $pathway_rank = $pathway_row->pathway_rank;
                
                echo '<div class="alm-pathway-row" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">';
                echo '<select name="pathways[' . $idx . '][pathway]" class="regular-text" style="flex: 1;">';
                echo '<option value="">' . __('Select pathway...', 'academy-lesson-manager') . '</option>';
                foreach ($pathway_options as $value => $label) {
                    $selected = ($pathway_value === $value) ? 'selected' : '';
                    echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
                }
                echo '</select>';
                echo '<label style="font-weight: 600; white-space: nowrap;">' . __('Rank:', 'academy-lesson-manager') . '</label>';
                echo '<select name="pathways[' . $idx . '][rank]" style="width: 80px;">';
                for ($i = 1; $i <= 5; $i++) {
                    $selected = ($pathway_rank == $i) ? 'selected' : '';
                    echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
                }
                echo '</select>';
                echo '<button type="button" class="button button-small alm-remove-pathway" style="color: #dc3232;">' . __('Remove', 'academy-lesson-manager') . '</button>';
                echo '</div>';
            }
        }
        
        // Add new pathway button
        echo '<button type="button" id="alm-add-pathway" class="button button-secondary">' . __('Add Pathway', 'academy-lesson-manager') . '</button>';
        echo '<p class="description">' . __('Assign this lesson to AI pathways with ranking (1 = most important to recommend, 5 = least important). A lesson can belong to multiple pathways.', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
        
        // JavaScript for adding/removing pathways dynamically
        $pathway_options_js = array();
        foreach ($pathway_options as $value => $label) {
            $pathway_options_js[] = 'pathwaySelect.append($("<option>").val("' . esc_js($value) . '").text("' . esc_js($label) . '"));';
        }
        
        echo '<script>
        jQuery(document).ready(function($) {
            var pathwayIndex = ' . (empty($current_pathways) ? 0 : count($current_pathways)) . ';
            
            $("#alm-add-pathway").on("click", function() {
                var pathwayRow = $("<div>").addClass("alm-pathway-row").css({"display": "flex", "align-items": "center", "gap": "10px", "margin-bottom": "10px"});
                
                var pathwaySelect = $("<select>")
                    .attr("name", "pathways[" + pathwayIndex + "][pathway]")
                    .addClass("regular-text")
                    .css("flex", "1")
                    .append($("<option>").val("").text("' . __('Select pathway...', 'academy-lesson-manager') . '"));
                ' . implode("\n                ", $pathway_options_js) . '
                
                var rankLabel = $("<label>").css({"font-weight": "600", "white-space": "nowrap"}).text("' . __('Rank:', 'academy-lesson-manager') . '");
                
                var rankSelect = $("<select>")
                    .attr("name", "pathways[" + pathwayIndex + "][rank]")
                    .css("width", "80px");
                for (var i = 1; i <= 5; i++) {
                    rankSelect.append($("<option>").val(i).text(i));
                }
                
                var removeBtn = $("<button>")
                    .attr("type", "button")
                    .addClass("button button-small alm-remove-pathway")
                    .css("color", "#dc3232")
                    .text("' . __('Remove', 'academy-lesson-manager') . '");
                
                pathwayRow.append(pathwaySelect).append(rankLabel).append(rankSelect).append(removeBtn);
                $("#alm-pathways-container").append(pathwayRow);
                pathwayIndex++;
            });
            
            $(document).on("click", ".alm-remove-pathway", function() {
                $(this).closest(".alm-pathway-row").remove();
            });
        });
        </script>';
        
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
        
        // Copy URL JavaScript
        if (!empty($lesson_url)) {
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
        
        echo '<tr>';
        echo '<th scope="row"><label for="lesson_level">' . __('Lesson Level', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="lesson_level" name="lesson_level">';
        echo '<option value="">' . __('Select level...', 'academy-lesson-manager') . '</option>';
        $level_options = array(
            'beginner' => __('Beginner', 'academy-lesson-manager'),
            'intermediate' => __('Intermediate', 'academy-lesson-manager'),
            'advanced' => __('Advanced', 'academy-lesson-manager'),
            'pro' => __('Pro', 'academy-lesson-manager')
        );
        foreach ($level_options as $value => $label) {
            $selected = ($value === 'intermediate') ? 'selected' : ''; // Default to intermediate
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Skill level required for this lesson.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="lesson_tags">' . __('Lesson Tags', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        
        // Get all tags from database
        $tags_table = $this->database->get_table_name('tags');
        $all_tags = $this->wpdb->get_results("SELECT tag_name FROM {$tags_table} ORDER BY tag_name ASC");
        
        // Multi-select dropdown
        echo '<select id="lesson_tags" name="lesson_tags[]" multiple="multiple" class="regular-text" style="min-height: 150px; width: 100%; max-width: 500px;">';
        foreach ($all_tags as $tag) {
            echo '<option value="' . esc_attr($tag->tag_name) . '">' . esc_html($tag->tag_name) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Hold Ctrl (Cmd on Mac) to select multiple tags. Tags are normalized from the tags database.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Lesson Style field
        echo '<tr>';
        echo '<th scope="row"><label for="lesson_style">' . __('Lesson Style', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        
        $styles = array('Any', 'Jazz', 'Cocktail', 'Blues', 'Rock', 'Funk', 'Latin', 'Classical', 'Smooth Jazz', 'Holiday', 'Ballad', 'Pop', 'New Age', 'Gospel', 'New Orleans', 'Country', 'Modal', 'Stride', 'Organ', 'Boogie');
        
        // Multi-select dropdown
        echo '<select id="lesson_style" name="lesson_style[]" multiple="multiple" class="regular-text" style="min-height: 150px; width: 100%; max-width: 500px;">';
        foreach ($styles as $style) {
            echo '<option value="' . esc_attr($style) . '">' . esc_html($style) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Hold Ctrl (Cmd on Mac) to select multiple styles.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Get available pathway options from settings
        $pathway_options = ALM_Admin_Settings::get_pathways();
        
        echo '<tr>';
        echo '<th scope="row"><label>' . __('AI Pathways', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<div id="alm-pathways-container">';
        // Empty container - pathways will be added via JavaScript
        echo '</div>';
        
        $pathway_options_js = array();
        foreach ($pathway_options as $value => $label) {
            $pathway_options_js[] = 'pathwaySelect.append($("<option>").val("' . esc_js($value) . '").text("' . esc_js($label) . '"));';
        }
        
        echo '<button type="button" id="alm-add-pathway" class="button button-secondary">' . __('Add Pathway', 'academy-lesson-manager') . '</button>';
        echo '<p class="description">' . __('Assign this lesson to AI pathways with ranking (1 = most important to recommend, 5 = least important). A lesson can belong to multiple pathways.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // JavaScript for adding/removing pathways dynamically
        echo '<script>
        jQuery(document).ready(function($) {
            var pathwayIndex = 0;
            
            $("#alm-add-pathway").on("click", function() {
                var pathwayRow = $("<div>").addClass("alm-pathway-row").css({"display": "flex", "align-items": "center", "gap": "10px", "margin-bottom": "10px"});
                
                var pathwaySelect = $("<select>")
                    .attr("name", "pathways[" + pathwayIndex + "][pathway]")
                    .addClass("regular-text")
                    .css("flex", "1")
                    .append($("<option>").val("").text("' . __('Select pathway...', 'academy-lesson-manager') . '"));
                ' . implode("\n                ", $pathway_options_js) . '
                
                var rankLabel = $("<label>").css({"font-weight": "600", "white-space": "nowrap"}).text("' . __('Rank:', 'academy-lesson-manager') . '");
                
                var rankSelect = $("<select>")
                    .attr("name", "pathways[" + pathwayIndex + "][rank]")
                    .css("width", "80px");
                for (var i = 1; i <= 5; i++) {
                    rankSelect.append($("<option>").val(i).text(i));
                }
                
                var removeBtn = $("<button>")
                    .attr("type", "button")
                    .addClass("button button-small alm-remove-pathway")
                    .css("color", "#dc3232")
                    .text("' . __('Remove', 'academy-lesson-manager') . '");
                
                pathwayRow.append(pathwaySelect).append(rankLabel).append(rankSelect).append(removeBtn);
                $("#alm-pathways-container").append(pathwayRow);
                pathwayIndex++;
            });
            
            $(document).on("click", ".alm-remove-pathway", function() {
                $(this).closest(".alm-pathway-row").remove();
            });
        });
        </script>';
        
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
            'lesson_description' => ALM_Helpers::clean_html_content($_POST['lesson_description']),
            'post_date' => sanitize_text_field($_POST['post_date']),
            'duration' => intval($_POST['duration']),
            'song_lesson' => sanitize_text_field($_POST['song_lesson']),
            'vtt' => sanitize_text_field($_POST['vtt']),
            'slug' => sanitize_text_field($_POST['slug']),
            'membership_level' => intval($_POST['membership_level']),
            'lesson_level' => sanitize_text_field($_POST['lesson_level']),
            'lesson_tags' => $this->normalize_lesson_tags($_POST['lesson_tags'] ?? array()),
            'lesson_style' => $this->normalize_lesson_styles($_POST['lesson_style'] ?? array()),
        );
        
        $result = $this->wpdb->insert($this->table_name, $data);
        
        if ($result) {
            $lesson_id = $this->wpdb->insert_id;
            
            // Update pathway assignments
            $this->update_lesson_pathways($lesson_id);
            
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
            'lesson_description' => ALM_Helpers::clean_html_content($_POST['lesson_description']),
            'post_date' => sanitize_text_field($_POST['post_date']),
            'duration' => intval($_POST['duration']),
            'song_lesson' => sanitize_text_field($_POST['song_lesson']),
            'vtt' => sanitize_text_field($_POST['vtt']),
            'slug' => sanitize_text_field($_POST['slug']),
            'membership_level' => intval($_POST['membership_level']),
            'lesson_level' => sanitize_text_field($_POST['lesson_level']),
            'lesson_tags' => $this->normalize_lesson_tags($_POST['lesson_tags'] ?? array()),
            'lesson_style' => $this->normalize_lesson_styles($_POST['lesson_style'] ?? array()),
        );
        
        $result = $this->wpdb->update($this->table_name, $data, array('ID' => $lesson_id));
        
        if ($result !== false) {
            // Update pathway assignments
            $this->update_lesson_pathways($lesson_id);
            
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
     * Update lesson pathway assignments
     */
    private function update_lesson_pathways($lesson_id) {
        $pathways_table = $this->database->get_table_name('lesson_pathways');
        
        // Delete all existing pathway assignments for this lesson
        $this->wpdb->delete($pathways_table, array('lesson_id' => $lesson_id));
        
        // Insert new pathway assignments if provided
        if (isset($_POST['pathways']) && is_array($_POST['pathways'])) {
            foreach ($_POST['pathways'] as $pathway_data) {
                $pathway = sanitize_text_field($pathway_data['pathway']);
                $rank = isset($pathway_data['rank']) ? intval($pathway_data['rank']) : null;
                
                // Only insert if pathway is selected and rank is valid (1-5)
                if (!empty($pathway) && $rank >= 1 && $rank <= 5) {
                    $this->wpdb->insert(
                        $pathways_table,
                        array(
                            'lesson_id' => $lesson_id,
                            'pathway' => $pathway,
                            'pathway_rank' => $rank
                        ),
                        array('%d', '%s', '%d')
                    );
                }
            }
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
     * Handle deleting a chapter from a lesson
     */
    private function handle_delete_chapter($chapter_id, $lesson_id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        if (empty($chapter_id) || empty($lesson_id)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . $lesson_id)));
            exit;
        }
        
        $database = new ALM_Database();
        $chapters_table = $database->get_table_name('chapters');
        
        // Delete the chapter
        $result = $this->wpdb->delete($chapters_table, array('ID' => $chapter_id));
        
        if ($result) {
            wp_redirect(add_query_arg('message', 'chapter-deleted', admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . $lesson_id)));
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . $lesson_id)));
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
        
        // Always re-sync to WordPress post (updates existing or creates new)
        $sync = new ALM_Post_Sync();
        $post_id = $sync->sync_lesson_to_post($lesson_id);
        
        if ($post_id) {
            wp_redirect(add_query_arg('message', 'fixed', admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . $lesson_id)));
            exit;
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-lessons&action=edit&id=' . $lesson_id)));
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
                
                // Format the display type with number if present
                $display_type = ucfirst($resource['type']);
                if (preg_match('/^(jam|ireal|sheet_music|zip)(\d+)$/', $resource['type'], $matches)) {
                    $base_type = $matches[1];
                    $number = $matches[2];
                    $type_map = array(
                        'jam' => 'Backing Track',
                        'ireal' => 'iRealPro',
                        'sheet_music' => 'Sheet Music',
                        'zip' => 'Zip File'
                    );
                    $display_type = (isset($type_map[$base_type]) ? $type_map[$base_type] : ucfirst($base_type)) . ' ' . $number;
                } elseif (in_array($resource['type'], array('jam', 'ireal', 'sheet_music', 'zip'))) {
                    // Also handle non-numbered resources
                    $type_map = array(
                        'jam' => 'Backing Track',
                        'ireal' => 'iRealPro',
                        'sheet_music' => 'Sheet Music',
                        'zip' => 'Zip File'
                    );
                    $display_type = isset($type_map[$resource['type']]) ? $type_map[$resource['type']] : ucfirst($resource['type']);
                }
                
                echo '<li>';
                echo '<strong>' . esc_html($display_type) . ':</strong> ';
                echo '<a href="' . esc_url($resource['display_url']) . '" target="_blank">' . esc_html($display_name) . '</a>';
                if (!empty($resource['label'])) {
                    echo ' <span style="color: #666; font-style: italic;">(' . esc_html($resource['label']) . ')</span>';
                }
                echo ' <a href="#" class="alm-edit-resource" data-type="' . esc_attr($resource['type']) . '" data-url="' . esc_attr($resource['url']) . '" data-attachment-id="' . esc_attr($resource['attachment_id'] ?? 0) . '" data-label="' . esc_attr($resource['label'] ?? '') . '" data-lesson-id="' . esc_attr($lesson->ID) . '" style="color: #2271b1; margin-left: 10px;">' . __('Edit', 'academy-lesson-manager') . '</a>';
                echo ' <a href="#" class="alm-delete-resource" data-type="' . esc_attr($resource['type']) . '" data-lesson-id="' . esc_attr($lesson->ID) . '" style="color: #dc3232; margin-left: 10px;">' . __('Delete', 'academy-lesson-manager') . '</a>';
                echo '</li>';
            }
            echo '</ul>';
        }
        
        // Add resource form (hidden by default)
        echo '<div id="alm-add-resource-form" style="display: none; margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">';
        echo '<h4>' . __('Add New Resource', 'academy-lesson-manager') . '</h4>';
        echo '<input type="hidden" id="alm-edit-resource-type" value="" />';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row"><label for="alm-resource-type">' . __('Resource Type', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="alm-resource-type" name="resource_type">';
        echo '<option value="">' . __('Choose...', 'academy-lesson-manager') . '</option>';
        echo '<option value="sheet_music" selected>Sheet Music</option>';
        echo '<option value="ireal">iRealPro</option>';
        echo '<option value="jam">Backing Track (mp3)</option>';
        echo '<option value="zip">Zip File</option>';
        echo '<option value="note">Note</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr class="alm-resource-file-row">';
        echo '<th scope="row"><label for="alm-resource-url">' . __('Resource File', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<div id="alm-resource-file-wrapper">';
        echo '<input type="hidden" id="alm-resource-attachment-id" name="attachment_id" value="" />';
        echo '<input type="text" id="alm-resource-url" name="resource_url" placeholder="Click to select file from media library" readonly style="background-color: #f0f0f1; width: 200px !important; max-width: 200px;" />';
        echo '<button type="button" class="button" id="alm-select-media-btn" style="margin-left: 10px;">' . __('Select from Media Library', 'academy-lesson-manager') . '</button>';
        echo '<button type="button" class="button" id="alm-clear-media-btn" style="margin-left: 5px; display: none;">' . __('Clear', 'academy-lesson-manager') . '</button>';
        echo '</div>';
        echo '<p class="description">' . __('Select a file from the media library or enter a URL manually.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '<tr class="alm-resource-note-row" style="display: none;">';
        echo '<th scope="row"><label for="alm-resource-note">' . __('Note Content', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<textarea id="alm-resource-note" name="resource_note" class="large-text" rows="5" placeholder="Enter your note content here..."></textarea>';
        echo '<p class="description">' . __('Write a note that will be displayed in the resources area.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="alm-resource-label">' . __('Label (optional, 30 chars max)', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="text" id="alm-resource-label" name="resource_label" class="regular-text" maxlength="30" placeholder="e.g., Fast Tempo, Tutorial, etc." />';
        echo '<p class="description">' . __('Add a short note to help students differentiate between similar resources.', 'academy-lesson-manager') . '</p>';
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
        echo '<h3>' . __('Chapters in This Lesson', 'academy-lesson-manager') . ' <a href="?page=academy-manager-chapters&action=add&lesson_id=' . $lesson_id . '" class="button button-small">' . __('Add Chapter', 'academy-lesson-manager') . '</a>';
        echo ' <button type="button" class="button button-small" id="alm-calculate-bunny-durations" data-lesson-id="' . $lesson_id . '">' . __('Calculate All Bunny Durations', 'academy-lesson-manager') . '</button>';
        echo ' <button type="button" class="button button-small" id="alm-calculate-vimeo-durations" data-lesson-id="' . $lesson_id . '">' . __('Calculate All Vimeo Durations', 'academy-lesson-manager') . '</button></h3>';
        
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
            echo '<th scope="col">' . __('Actions', 'academy-lesson-manager') . '</th>';
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
                echo '<td>';
                echo '<a href="?page=academy-manager-chapters&action=edit&id=' . $chapter->ID . '" class="button button-small">' . __('Edit', 'academy-lesson-manager') . '</a> ';
                echo '<a href="?page=academy-manager-lessons&action=delete-chapter&chapter_id=' . $chapter->ID . '&lesson_id=' . $lesson_id . '" class="button button-small" onclick="return confirm(\'' . __('Are you sure you want to delete this chapter?', 'academy-lesson-manager') . '\')" style="color: #dc3232;">' . __('Delete', 'academy-lesson-manager') . '</a>';
                echo '</td>';
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
        
        $action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
        
        // Check for button names as alternative triggers
        if (isset($_POST['submit_bulk_tag'])) {
            $action = 'add_tag';
        } elseif (isset($_POST['submit_bulk_style'])) {
            $action = 'add_style';
        }
        
        if ($action === 'update_membership') {
            $this->handle_bulk_membership_update();
        } elseif ($action === 'assign_collection') {
            $this->handle_bulk_collection_assign();
        } elseif ($action === 'delete') {
            $this->handle_bulk_delete();
        } elseif ($action === 'add_tag') {
            $this->handle_bulk_tag_add();
        } elseif ($action === 'add_style') {
            $this->handle_bulk_style_add();
        }
    }
    
    /**
     * Handle bulk skill level update (new simplified approach)
     */
    private function handle_bulk_skill_level_update() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $lesson_ids = isset($_POST['lesson']) ? array_map('intval', (array) $_POST['lesson']) : array();
        $skill_level_action = isset($_POST['bulk_action_skill_level']) ? sanitize_text_field($_POST['bulk_action_skill_level']) : '';
        
        // Check if lessons were selected
        if (empty($lesson_ids)) {
            wp_redirect(add_query_arg('message', 'no_lessons_selected', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
        
        // Check if action was selected (not default '-1')
        if ($skill_level_action === '' || $skill_level_action === '-1') {
            wp_redirect(add_query_arg('message', 'no_action_selected', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
        
        // Validate skill level action
        $valid_levels = array('beginner', 'intermediate', 'advanced', 'pro', 'clear');
        if (!in_array($skill_level_action, $valid_levels, true)) {
            wp_redirect(add_query_arg('message', 'invalid_level', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
        
        // Set lesson_level value (null for 'clear', otherwise the level)
        $lesson_level = ($skill_level_action === 'clear') ? null : $skill_level_action;
        
        $updated = 0;
        
        foreach ($lesson_ids as $lesson_id) {
            if ($lesson_level === null) {
                // Handle NULL value
                $result = $this->wpdb->query($this->wpdb->prepare(
                    "UPDATE {$this->table_name} SET lesson_level = NULL WHERE ID = %d",
                    $lesson_id
                ));
            } else {
                // Handle string value
                $result = $this->wpdb->update(
                    $this->table_name,
                    array('lesson_level' => $lesson_level),
                    array('ID' => $lesson_id),
                    array('%s'),
                    array('%d')
                );
            }
            
            if ($result !== false) {
                $updated++;
            }
        }
        
        wp_redirect(add_query_arg('message', 'bulk_lesson_level_updated', admin_url('admin.php?page=academy-manager-lessons')));
        exit;
    }
    
    /**
     * Handle bulk membership update
     */
    private function handle_bulk_membership_update() {
        $lesson_ids = isset($_POST['lesson']) ? array_map('intval', $_POST['lesson']) : array();
        $membership_level = isset($_POST['bulk_membership_level']) ? intval($_POST['bulk_membership_level']) : 0;
        
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
     * Handle bulk collection assignment
     */
    private function handle_bulk_collection_assign() {
        $lesson_ids = isset($_POST['lesson']) ? array_map('intval', $_POST['lesson']) : array();
        $collection_id = isset($_POST['bulk_collection_id']) ? intval($_POST['bulk_collection_id']) : 0;
        
        // Check if lessons were selected
        if (empty($lesson_ids)) {
            wp_redirect(add_query_arg('message', 'no_lessons_selected', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
        
        // Check if collection was selected
        if ($collection_id === 0) {
            wp_redirect(add_query_arg('message', 'no_collection_selected', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
        
        $updated = 0;
        
        foreach ($lesson_ids as $lesson_id) {
            $result = $this->wpdb->update(
                $this->table_name,
                array('collection_id' => $collection_id),
                array('ID' => $lesson_id),
                array('%d'),
                array('%d')
            );
            
            if ($result !== false) {
                $updated++;
            }
        }
        
        wp_redirect(add_query_arg('message', 'collection_assigned', admin_url('admin.php?page=academy-manager-lessons')));
        exit;
    }
    
    /**
     * Handle bulk lesson deletion
     */
    private function handle_bulk_delete() {
        $lesson_ids = isset($_POST['lesson']) ? array_map('intval', $_POST['lesson']) : array();
        
        // Check if lessons were selected
        if (empty($lesson_ids)) {
            wp_redirect(add_query_arg('message', 'no_lessons_selected', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }
        
        $sync = new ALM_Post_Sync();
        $deleted = 0;
        
        foreach ($lesson_ids as $lesson_id) {
            // Get post_id before deleting from ALM table
            $lesson = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT post_id FROM {$this->table_name} WHERE ID = %d",
                $lesson_id
            ));
            
            // Delete from ALM table
            $result = $this->wpdb->delete($this->table_name, array('ID' => $lesson_id));
            
            if ($result !== false) {
                $deleted++;
                
                // Delete WordPress post and meta if it exists
                if ($lesson && $lesson->post_id) {
                    $sync->delete_post_and_meta($lesson->post_id);
                }
            }
        }
        
        if ($deleted > 0) {
            wp_redirect(add_query_arg('message', 'bulk_deleted', admin_url('admin.php?page=academy-manager-lessons')));
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-lessons')));
        }
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
    
    /**
     * Get count of lessons not assigned to any collection
     */
    private function get_unassigned_lesson_count() {
        $count = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE collection_id = 0 OR collection_id IS NULL"
        );
        return intval($count);
    }
    
    /**
     * Get count of lessons assigned to a specific collection
     */
    private function get_lesson_count_by_collection($collection_id) {
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE collection_id = %d",
            $collection_id
        ));
        return intval($count);
    }
    
    /**
     * Get count of lessons assigned to a specific pathway
     */
    private function get_lesson_count_by_pathway($pathway) {
        $pathways_table = $this->database->get_table_name('lesson_pathways');
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(DISTINCT lesson_id) FROM {$pathways_table} WHERE pathway = %s",
            $pathway
        ));
        return intval($count);
    }
    
    /**
     * Get count of lessons with a specific skill level
     */
    private function get_lesson_count_by_skill_level($skill_level) {
        // Check if lesson_level column exists
        $level_columns = $this->wpdb->get_results("SHOW COLUMNS FROM {$this->table_name} LIKE 'lesson_level'");
        if (empty($level_columns)) {
            return 0;
        }
        
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE lesson_level = %s",
            $skill_level
        ));
        return intval($count);
    }
    
    /**
     * Get lesson count by style
     */
    private function get_lesson_count_by_style($style) {
        $style_escaped = $this->wpdb->esc_like(trim($style));
        return intval($this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE (lesson_style = %s OR lesson_style LIKE %s OR lesson_style LIKE %s OR lesson_style LIKE %s)",
            $style_escaped,
            $style_escaped . ',%',
            '%, ' . $style_escaped . ',%',
            '%, ' . $style_escaped
        )));
    }
    
    /**
     * Get count of lessons with resources
     */
    private function get_lesson_count_with_resources() {
        $count = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE resources IS NOT NULL AND resources != '' AND resources != 'N;'"
        );
        return intval($count);
    }
    
    /**
     * Get count of lessons without resources
     */
    private function get_lesson_count_without_resources() {
        $count = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE resources IS NULL OR resources = '' OR resources = 'N;'"
        );
        return intval($count);
    }
    
    /**
     * Render column visibility controls
     */
    private function render_column_visibility_controls() {
        $user_id = get_current_user_id();
        $hidden_columns = get_user_meta($user_id, 'alm_lesson_list_hidden_columns', true);
        if (!is_array($hidden_columns)) {
            $hidden_columns = array();
        }
        
        // Define all available columns
        $columns = array(
            'column-id' => __('ID', 'academy-lesson-manager'),
            'column-post-id' => __('Post ID', 'academy-lesson-manager'),
            'column-date' => __('Date', 'academy-lesson-manager'),
            'column-title' => __('Lesson Title', 'academy-lesson-manager'),
            'column-course' => __('Collection', 'academy-lesson-manager'),
            'column-duration' => __('Duration', 'academy-lesson-manager'),
            'column-membership' => __('Memb. Level', 'academy-lesson-manager'),
            'column-skill-level' => __('Skill Level', 'academy-lesson-manager'),
            'column-pathways' => __('Pathways', 'academy-lesson-manager'),
            'column-tags' => __('Tags', 'academy-lesson-manager'),
            'column-lesson-style' => __('Lesson Style', 'academy-lesson-manager'),
            'column-resources' => __('Resources', 'academy-lesson-manager'),
            'column-actions' => __('Actions', 'academy-lesson-manager')
        );
        
        echo '<div class="alm-column-visibility" style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">';
        echo '<button type="button" id="alm-toggle-columns" class="button button-secondary" style="position: relative;">';
        echo '<span class="dashicons dashicons-admin-settings" style="vertical-align: middle; margin-right: 4px;"></span>';
        echo __('Columns', 'academy-lesson-manager');
        echo '</button>';
        echo '<div id="alm-columns-dropdown" style="display: none; position: absolute; background: #fff; border: 1px solid #ccc; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); padding: 10px; z-index: 1000; margin-top: 5px; min-width: 200px;">';
        echo '<div style="font-weight: 600; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #ddd;">' . __('Show/Hide Columns', 'academy-lesson-manager') . '</div>';
        foreach ($columns as $column_id => $column_name) {
            $checked = !in_array($column_id, $hidden_columns) ? 'checked' : '';
            echo '<label style="display: block; padding: 6px 4px; cursor: pointer; user-select: none;">';
            echo '<input type="checkbox" class="alm-column-toggle" data-column="' . esc_attr($column_id) . '" ' . $checked . ' style="margin-right: 8px;" />';
            echo esc_html($column_name);
            echo '</label>';
        }
        echo '</div>';
        echo '</div>';
        
        // JavaScript for column visibility
        $hidden_columns_json = json_encode($hidden_columns);
        echo '<script>
        jQuery(document).ready(function($) {
            var hiddenColumns = ' . $hidden_columns_json . ';
            var ajaxUrl = "' . esc_js(admin_url('admin-ajax.php')) . '";
            var nonce = "' . esc_js(wp_create_nonce('alm_column_visibility')) . '";
            
            // Toggle dropdown
            $("#alm-toggle-columns").on("click", function(e) {
                e.stopPropagation();
                $("#alm-columns-dropdown").toggle();
            });
            
            // Close dropdown when clicking outside
            $(document).on("click", function(e) {
                if (!$(e.target).closest(".alm-column-visibility").length) {
                    $("#alm-columns-dropdown").hide();
                }
            });
            
            // Apply initial visibility
            function applyColumnVisibility() {
                hiddenColumns.forEach(function(columnId) {
                    $("th." + columnId + ", td." + columnId).hide();
                });
            }
            applyColumnVisibility();
            
            // Handle column toggle
            $(".alm-column-toggle").on("change", function() {
                var columnId = $(this).data("column");
                var isHidden = !$(this).is(":checked");
                
                if (isHidden) {
                    if (hiddenColumns.indexOf(columnId) === -1) {
                        hiddenColumns.push(columnId);
                    }
                    $("th." + columnId + ", td." + columnId).hide();
                } else {
                    hiddenColumns = hiddenColumns.filter(function(id) {
                        return id !== columnId;
                    });
                    $("th." + columnId + ", td." + columnId).show();
                }
                
                // Save preferences
                $.ajax({
                    url: ajaxUrl,
                    type: "POST",
                    data: {
                        action: "alm_save_column_preferences",
                        nonce: nonce,
                        hidden_columns: hiddenColumns
                    }
                });
            });
        });
        </script>';
        
        echo '<style>
        .alm-column-visibility {
            position: relative;
        }
        #alm-columns-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 5px;
        }
        </style>';
    }
    
    /**
     * Render pagination info (above table)
     */
    private function render_pagination_info($total_items, $paged, $per_page, $total_pages) {
        $start = ($paged - 1) * $per_page + 1;
        $end = min($paged * $per_page, $total_items);
        
        // Build query args for per page form
        $query_args = $_GET;
        unset($query_args['per_page']);
        unset($query_args['paged']); // Reset to page 1 when changing per page
        
        echo '<div class="alm-pagination-info" style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; padding: 10px 0;">';
        echo '<div style="color: #666; font-size: 14px;">';
        if ($total_items > 0) {
            echo sprintf(__('Showing %d-%d of %d lessons', 'academy-lesson-manager'), $start, $end, $total_items);
        } else {
            echo __('No lessons found', 'academy-lesson-manager');
        }
        echo '</div>';
        
        // Per page selector form
        echo '<form method="get" action="" style="display: inline;">';
        foreach ($query_args as $key => $value) {
            if ($key !== 'per_page' && $key !== 'paged') {
                echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }
        }
        echo '<div style="display: flex; align-items: center; gap: 8px;">';
        echo '<label for="per-page-select" style="font-size: 14px; color: #666;">' . __('Per page:', 'academy-lesson-manager') . '</label>';
        echo '<select id="per-page-select" name="per_page" style="padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px;" onchange="this.form.submit()">';
        $per_page_options = array(25, 50, 100, 200);
        foreach ($per_page_options as $option) {
            $selected = ($per_page == $option) ? 'selected' : '';
            echo '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render pagination controls (below table)
     */
    private function render_pagination_controls($paged, $total_pages, $per_page, $search, $membership_filter, $collection_filter, $resources_filter, $pathway_filter, $skill_level_filter, $order_by, $order) {
        if ($total_pages <= 1) {
            return; // No pagination needed
        }
        
        // Build query string for pagination links
        $query_args = array(
            'page' => 'academy-manager-lessons',
            'per_page' => $per_page,
            'order_by' => $order_by,
            'order' => $order
        );
        if (!empty($search)) {
            $query_args['search'] = $search;
        }
        if ($membership_filter > 0) {
            $query_args['membership_level'] = $membership_filter;
        }
        if (!empty($collection_filter)) {
            $query_args['collection_filter'] = $collection_filter;
        }
        if (!empty($resources_filter)) {
            $query_args['resources_filter'] = $resources_filter;
        }
        if (!empty($pathway_filter)) {
            $query_args['pathway_filter'] = $pathway_filter;
        }
        if (!empty($skill_level_filter)) {
            $query_args['skill_level_filter'] = $skill_level_filter;
        }
        
        echo '<div class="alm-pagination" style="margin-top: 20px; display: flex; justify-content: center; align-items: center; gap: 8px; padding: 20px 0;">';
        
        // First page
        if ($paged > 1) {
            $query_args['paged'] = 1;
            echo '<a href="' . esc_url(add_query_arg($query_args, admin_url('admin.php'))) . '" class="button" title="' . __('First page', 'academy-lesson-manager') . '">«</a>';
            
            // Previous page
            $query_args['paged'] = $paged - 1;
            echo '<a href="' . esc_url(add_query_arg($query_args, admin_url('admin.php'))) . '" class="button" title="' . __('Previous page', 'academy-lesson-manager') . '">‹ ' . __('Previous', 'academy-lesson-manager') . '</a>';
        } else {
            echo '<span class="button disabled" style="opacity: 0.5; cursor: not-allowed;">«</span>';
            echo '<span class="button disabled" style="opacity: 0.5; cursor: not-allowed;">‹ ' . __('Previous', 'academy-lesson-manager') . '</span>';
        }
        
        // Page numbers
        echo '<span style="padding: 0 12px; color: #666;">';
        echo sprintf(__('Page %d of %d', 'academy-lesson-manager'), $paged, $total_pages);
        echo '</span>';
        
        // Next page
        if ($paged < $total_pages) {
            // Next page
            $query_args['paged'] = $paged + 1;
            echo '<a href="' . esc_url(add_query_arg($query_args, admin_url('admin.php'))) . '" class="button" title="' . __('Next page', 'academy-lesson-manager') . '">' . __('Next', 'academy-lesson-manager') . ' ›</a>';
            
            // Last page
            $query_args['paged'] = $total_pages;
            echo '<a href="' . esc_url(add_query_arg($query_args, admin_url('admin.php'))) . '" class="button" title="' . __('Last page', 'academy-lesson-manager') . '">»</a>';
        } else {
            echo '<span class="button disabled" style="opacity: 0.5; cursor: not-allowed;">' . __('Next', 'academy-lesson-manager') . ' ›</span>';
            echo '<span class="button disabled" style="opacity: 0.5; cursor: not-allowed;">»</span>';
        }
        
        echo '</div>';
    }
    
    /**
     * Render database statistics
     */
    private function render_statistics() {
        // Get counts
        $lessons_count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $unassigned_count = $this->get_unassigned_lesson_count();
        
        $collections_table = $this->database->get_table_name('collections');
        $collections_count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$collections_table}");
        
        $chapters_table = $this->database->get_table_name('chapters');
        $chapters_count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$chapters_table}");
        
        echo '<div class="alm-statistics" style="background: #f9f9f9; border: 1px solid #ddd; padding: 15px 20px; margin-bottom: 20px; display: flex; gap: 30px; flex-wrap: wrap;">';
        
        echo '<div style="flex: 1; min-width: 150px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($lessons_count) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Lessons', 'academy-lesson-manager') . '</p>';
        if ($unassigned_count > 0) {
            echo '<p style="margin: 5px 0 0 0; color: #d63638; font-size: 12px;">' . sprintf(__('%d unassigned', 'academy-lesson-manager'), $unassigned_count) . '</p>';
        }
        echo '</div>';
        
        echo '<div style="flex: 1; min-width: 150px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($chapters_count) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Chapters', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '<div style="flex: 1; min-width: 150px;">';
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($collections_count) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Collections', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '<div style="flex: 1; min-width: 150px;">';
        $avg_chapters = $lessons_count > 0 ? round($chapters_count / $lessons_count, 1) : 0;
        echo '<span style="font-size: 24px; font-weight: bold; color: #2271b1;">' . number_format($avg_chapters, 1) . '</span>';
        echo '<p style="margin: 5px 0 0 0; color: #666;">' . __('Avg Chapters/Lesson', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * Handle bulk style addition
     */
    private function handle_bulk_style_add() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $lesson_ids = isset($_POST['lesson']) ? array_map('intval', (array) $_POST['lesson']) : array();
        
        // Handle comma-separated values if submitted as a single string (legacy support)
        if (count($lesson_ids) === 1 && is_string($lesson_ids[0]) && strpos($lesson_ids[0], ',') !== false) {
            $lesson_ids = array_map('intval', explode(',', $lesson_ids[0]));
        }

        if (empty($lesson_ids)) {
            wp_redirect(add_query_arg('message', 'no_lessons_selected', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }

        $style_to_add = isset($_POST['bulk_style_add']) ? trim(sanitize_text_field($_POST['bulk_style_add'])) : '';

        if (empty($style_to_add)) {
            wp_redirect(add_query_arg('message', 'no_style_selected', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }

        // Validate style is in allowed list
        $allowed_styles = array('Any', 'Jazz', 'Cocktail', 'Blues', 'Rock', 'Funk', 'Latin', 'Classical', 'Smooth Jazz', 'Holiday', 'Ballad', 'Pop', 'New Age', 'Gospel', 'New Orleans', 'Country', 'Modal', 'Stride', 'Organ', 'Boogie');
        if (!in_array($style_to_add, $allowed_styles, true)) {
            wp_redirect(add_query_arg('message', 'invalid_style', admin_url('admin.php?page=academy-manager-lessons')));
            exit;
        }

        $updated_count = 0;
        foreach ($lesson_ids as $lesson_id) {
            $current_lesson_styles_str = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT lesson_style FROM {$this->table_name} WHERE ID = %d",
                $lesson_id
            ));

            $current_lesson_styles = array();
            if (!empty($current_lesson_styles_str)) {
                $current_lesson_styles = array_map('trim', explode(',', $current_lesson_styles_str));
                $current_lesson_styles = array_filter($current_lesson_styles);
            }

            if (!in_array($style_to_add, $current_lesson_styles)) {
                $current_lesson_styles[] = $style_to_add;
                sort($current_lesson_styles); // Keep styles sorted
                $new_styles_string = implode(', ', $current_lesson_styles);

                $result = $this->wpdb->update(
                    $this->table_name,
                    array('lesson_style' => $new_styles_string),
                    array('ID' => $lesson_id),
                    array('%s'),
                    array('%d')
                );

                if ($result !== false) {
                    $updated_count++;
                } else {
                    error_log("ALM Bulk Style Add: Failed to update lesson_style for lesson ID: {$lesson_id}");
                }
            }
        }

        if ($updated_count > 0) {
            wp_redirect(add_query_arg(array('message' => 'bulk_style_added', 'style' => $style_to_add), admin_url('admin.php?page=academy-manager-lessons')));
        } else {
            wp_redirect(add_query_arg('message', 'bulk_style_error', admin_url('admin.php?page=academy-manager-lessons')));
        }
        exit;
    }
    
    /**
     * Normalize lesson tags array to comma-separated string
     */
    private function normalize_lesson_tags($tags_array) {
        if (!is_array($tags_array) || empty($tags_array)) {
            return '';
        }
        
        $tags = array_filter(array_map('trim', $tags_array));
        $tags_string = implode(', ', $tags);
        
        // Sanitize and limit to 500 characters (column limit)
        $tags_string = sanitize_text_field($tags_string);
        if (strlen($tags_string) > 500) {
            $tags_string = substr($tags_string, 0, 497) . '...';
        }
        
        return $tags_string;
    }
    
    /**
     * Normalize lesson styles array to comma-separated string
     */
    private function normalize_lesson_styles($styles_array) {
        if (!is_array($styles_array) || empty($styles_array)) {
            return '';
        }
        
        $styles = array_filter(array_map('trim', $styles_array));
        sort($styles); // Keep styles sorted
        $styles_string = implode(', ', $styles);
        
        // Sanitize and limit to 500 characters (column limit)
        $styles_string = sanitize_text_field($styles_string);
        if (strlen($styles_string) > 500) {
            $styles_string = substr($styles_string, 0, 497) . '...';
        }
        
        return $styles_string;
    }
}
