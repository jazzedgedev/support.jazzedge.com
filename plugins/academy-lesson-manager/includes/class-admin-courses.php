<?php
/**
 * Courses Admin Class
 * 
 * Handles the courses admin page functionality
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Courses {
    
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
        $this->table_name = $this->database->get_table_name('courses');
    }
    
    /**
     * Render the courses admin page
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
        $this->render_navigation_buttons('courses');
        echo '<h1>' . __('Courses', 'academy-lesson-manager') . ' <a href="?page=academy-manager&action=add" class="page-title-action">' . __('Add New', 'academy-lesson-manager') . '</a></h1>';
        
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
        echo '<a href="?page=academy-manager" class="button ' . ($current_page === 'courses' ? 'button-primary' : '') . '">' . __('Courses', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-lessons" class="button ' . ($current_page === 'lessons' ? 'button-primary' : '') . '">' . __('Lessons', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-chapters" class="button ' . ($current_page === 'chapters' ? 'button-primary' : '') . '">' . __('Chapters', 'academy-lesson-manager') . '</a>';
        echo '</div>';
    }
    
    /**
     * Render the courses list page
     */
    private function render_list_page() {
        // Show success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            switch ($message) {
                case 'created':
                    echo '<div class="notice notice-success"><p>' . __('Course created successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'updated':
                    echo '<div class="notice notice-success"><p>' . __('Course updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'deleted':
                    echo '<div class="notice notice-success"><p>' . __('Course deleted successfully.', 'academy-lesson-manager') . '</p></div>';
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
            $where = $this->wpdb->prepare("WHERE course_title LIKE %s", '%' . $search . '%');
        }
        
        // Validate order by
        $allowed_order_by = array('ID', 'course_title', 'site', 'created_at');
        if (!in_array($order_by, $allowed_order_by)) {
            $order_by = 'ID';
        }
        
        $allowed_order = array('ASC', 'DESC');
        if (!in_array($order, $allowed_order)) {
            $order = 'DESC';
        }
        
        $sql = "SELECT * FROM {$this->table_name} {$where} ORDER BY {$order_by} {$order}";
        $courses = $this->wpdb->get_results($sql);
        
        // Render search form
        $this->render_search_form($search);
        
        // Render courses table
        $this->render_courses_table($courses, $order_by, $order);
    }
    
    /**
     * Render search form
     */
    private function render_search_form($search) {
        echo '<div class="alm-search-form">';
        echo '<form method="post" action="">';
        echo '<p class="search-box">';
        echo '<label class="screen-reader-text" for="course-search-input">' . __('Search Courses:', 'academy-lesson-manager') . '</label>';
        echo '<input type="search" id="course-search-input" name="search" value="' . esc_attr($search) . '" placeholder="' . __('Search courses...', 'academy-lesson-manager') . '" />';
        echo '<input type="submit" id="search-submit" class="button" value="' . __('Search Courses', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render courses table
     */
    private function render_courses_table($courses, $order_by, $order) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>';
        echo '<th scope="col" class="manage-column">' . $this->get_sortable_header('ID', 'ID', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column">' . __('Post ID', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column">' . $this->get_sortable_header('Course Title', 'course_title', $order_by, $order) . '</th>';
        echo '<th scope="col" class="manage-column">' . __('Lessons', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column">' . __('Actions', 'academy-lesson-manager') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($courses)) {
            echo '<tr><td colspan="6" class="no-items">' . __('No courses found.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            foreach ($courses as $course) {
                $this->render_course_row($course);
            }
        }
        
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Render a single course row
     */
    private function render_course_row($course) {
        $lesson_count = ALM_Helpers::count_course_lessons($course->ID);
        
        echo '<tr>';
        echo '<th scope="row" class="check-column"><input type="checkbox" name="course[]" value="' . $course->ID . '" /></th>';
        echo '<td class="column-id">' . $course->ID . '</td>';
        echo '<td class="column-post-id">' . ($course->post_id ? $course->post_id : '—') . '</td>';
        echo '<td class="column-title"><strong>' . esc_html(stripslashes($course->course_title)) . '</strong></td>';
        echo '<td class="column-lessons">' . $lesson_count . '</td>';
        echo '<td class="column-actions">';
        echo '<a href="?page=academy-manager&action=edit&id=' . $course->ID . '" class="button button-small">' . __('Edit', 'academy-lesson-manager') . '</a>';
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
            echo '<div class="notice notice-error"><p>' . __('Invalid course ID.', 'academy-lesson-manager') . '</p></div>';
            return;
        }
        
        $course = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE ID = %d",
            $id
        ));
        
        if (!$course) {
            echo '<div class="notice notice-error"><p>' . __('Course not found.', 'academy-lesson-manager') . '</p></div>';
            return;
        }
        
        // Show success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            switch ($message) {
                case 'created':
                    echo '<div class="notice notice-success"><p>' . __('Course created successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'updated':
                    echo '<div class="notice notice-success"><p>' . __('Course updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'deleted':
                    echo '<div class="notice notice-success"><p>' . __('Course deleted successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'lesson_added':
                    echo '<div class="notice notice-success"><p>' . __('Lesson added to course successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'lesson_removed':
                    echo '<div class="notice notice-success"><p>' . __('Lesson removed from course successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        // Back button and actions
        echo '<p>';
        echo '<a href="?page=academy-manager" class="button">&larr; ' . __('Back to Courses', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager&action=delete&id=' . $course->ID . '" class="button" onclick="return confirm(\'' . __('Are you sure you want to delete this course?', 'academy-lesson-manager') . '\')">' . __('Delete', 'academy-lesson-manager') . '</a>';
        echo '</p>';
        
        // Course details
        echo '<div class="alm-course-details">';
        echo '<h2>' . __('Edit Course', 'academy-lesson-manager') . '</h2>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Course ID', 'academy-lesson-manager') . '</th>';
        echo '<td>' . $course->ID . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="course_title">' . __('Course Title', 'academy-lesson-manager') . ' <span class="description">(required)</span></label></th>';
        echo '<td><input type="text" id="course_title" name="course_title" value="' . esc_attr(stripslashes($course->course_title)) . '" class="regular-text" required /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="course_description">' . __('Description', 'academy-lesson-manager') . '</label></th>';
        echo '<td><textarea id="course_description" name="course_description" rows="5" cols="50" class="large-text">' . esc_textarea(stripslashes($course->course_description)) . '</textarea></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="post_id">' . __('Post ID', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="number" id="post_id" name="post_id" value="' . esc_attr($course->post_id) . '" class="small-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Created', 'academy-lesson-manager') . '</th>';
        echo '<td>' . ALM_Helpers::format_date($course->created_at) . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Updated', 'academy-lesson-manager') . '</th>';
        echo '<td>' . ALM_Helpers::format_date($course->updated_at) . '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="course_id" value="' . $course->ID . '" />';
        echo '<input type="hidden" name="form_action" value="update" />';
        echo '<input type="submit" class="button-primary" value="' . __('Update Course', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
        
        // Lessons in this course
        $this->render_course_lessons($id);
    }
    
    /**
     * Render lessons in a course
     */
    private function render_course_lessons($course_id) {
        $lessons_table = $this->database->get_table_name('lessons');
        
        $lessons = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$lessons_table} WHERE course_id = %d ORDER BY lesson_title ASC",
            $course_id
        ));
        
        echo '<div class="alm-course-lessons">';
        echo '<h3>' . __('Lessons in This Course', 'academy-lesson-manager') . ' <a href="?page=academy-manager-lessons&action=add&course_id=' . $course_id . '" class="button button-small">' . __('Add Lesson', 'academy-lesson-manager') . '</a></h3>';
        
        if (empty($lessons)) {
            echo '<p>' . __('No lessons found in this course.', 'academy-lesson-manager') . '</p>';
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
                echo '<a href="?page=academy-manager-lessons&action=remove_from_course&id=' . $lesson->ID . '&course_id=' . $course_id . '" class="button button-small" onclick="return confirm(\'' . __('Are you sure you want to remove this lesson from the course?', 'academy-lesson-manager') . '\')">' . __('Remove', 'academy-lesson-manager') . '</a>';
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
            $this->create_course();
        } elseif ($action === 'update') {
            $this->update_course();
        }
    }
    
    /**
     * Create a new course
     */
    private function create_course() {
        $course_title = sanitize_text_field($_POST['course_title']);
        $course_description = sanitize_textarea_field($_POST['course_description']);
        $post_id = intval($_POST['post_id']);
        
        if (empty($course_title)) {
            wp_die(__('Course title is required.', 'academy-lesson-manager'));
        }
        
        $result = $this->wpdb->insert(
            $this->table_name,
            array(
                'post_id' => $post_id,
                'course_title' => $course_title,
                'course_description' => $course_description,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            wp_die(__('Error creating course.', 'academy-lesson-manager'));
        }
        
        $course_id = $this->wpdb->insert_id;
        wp_redirect(add_query_arg(array('page' => 'academy-manager', 'action' => 'edit', 'id' => $course_id, 'message' => 'created')));
        exit;
    }
    
    /**
     * Update an existing course
     */
    private function update_course() {
        $course_id = intval($_POST['course_id']);
        $course_title = sanitize_text_field($_POST['course_title']);
        $course_description = sanitize_textarea_field($_POST['course_description']);
        $post_id = intval($_POST['post_id']);
        
        if (empty($course_title)) {
            wp_die(__('Course title is required.', 'academy-lesson-manager'));
        }
        
        $result = $this->wpdb->update(
            $this->table_name,
            array(
                'post_id' => $post_id,
                'course_title' => $course_title,
                'course_description' => $course_description,
                'updated_at' => current_time('mysql')
            ),
            array('ID' => $course_id),
            array('%d', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_die(__('Error updating course.', 'academy-lesson-manager'));
        }
        
        wp_redirect(add_query_arg(array('page' => 'academy-manager', 'action' => 'edit', 'id' => $course_id, 'message' => 'updated')));
        exit;
    }
    
    /**
     * Handle course deletion
     */
    private function handle_delete($id) {
        if (empty($id)) {
            wp_die(__('Invalid course ID.', 'academy-lesson-manager'));
        }
        
        // Check if course has lessons
        $lessons_table = $this->database->get_table_name('lessons');
        $lesson_count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM $lessons_table WHERE course_id = %d",
            $id
        ));
        
        if ($lesson_count > 0) {
            wp_die(__('Cannot delete course with existing lessons. Please delete or reassign lessons first.', 'academy-lesson-manager'));
        }
        
        $result = $this->wpdb->delete(
            $this->table_name,
            array('ID' => $id),
            array('%d')
        );
        
        if ($result === false) {
            wp_die(__('Error deleting course.', 'academy-lesson-manager'));
        }
        
        wp_redirect(add_query_arg(array('page' => 'academy-manager', 'message' => 'deleted')));
        exit;
    }
    
    /**
     * Render the add course page
     */
    private function render_add_page() {
        echo '<p><a href="?page=academy-manager" class="button">&larr; ' . __('Back to Courses', 'academy-lesson-manager') . '</a></p>';
        
        echo '<div class="alm-course-details">';
        echo '<h2>' . __('Add New Course', 'academy-lesson-manager') . '</h2>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="course_title">' . __('Course Title', 'academy-lesson-manager') . ' <span class="description">(required)</span></label></th>';
        echo '<td><input type="text" id="course_title" name="course_title" value="" class="regular-text" required /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="course_description">' . __('Description', 'academy-lesson-manager') . '</label></th>';
        echo '<td><textarea id="course_description" name="course_description" rows="5" cols="50" class="large-text"></textarea></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="post_id">' . __('Post ID', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="number" id="post_id" name="post_id" value="0" class="small-text" /></td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="form_action" value="create" />';
        echo '<input type="submit" class="button-primary" value="' . __('Create Course', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
    }
}
