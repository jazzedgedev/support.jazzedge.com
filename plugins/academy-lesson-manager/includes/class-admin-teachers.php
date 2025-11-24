<?php
/**
 * ALM Admin Teachers Class
 * 
 * Handles teacher CRUD operations for Academy Lesson Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Teachers {
    
    private $wpdb;
    private $table_name;
    private $database;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();
        $this->table_name = $this->database->get_table_name('teachers');
    }
    
    /**
     * Render the teachers admin page
     */
    public function render_page() {
        // Handle form submissions first
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['save_teacher'])) {
                $this->save_teacher();
                return;
            } elseif (isset($_POST['delete_teacher'])) {
                $this->delete_teacher();
                return;
            }
        }
        
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        echo '<div class="wrap">';
        $this->render_navigation_buttons('teachers');
        echo '<h1>' . __('Teachers', 'academy-lesson-manager') . ' <a href="?page=academy-manager-teachers&action=add" class="page-title-action">' . __('Add New', 'academy-lesson-manager') . '</a></h1>';
        
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
        echo '<a href="?page=academy-manager-settings" class="button ' . ($current_page === 'settings' ? 'button-primary' : '') . '" style="margin-left: 10px;">' . __('Settings', 'academy-lesson-manager') . '</a>';
        echo '</div>';
    }
    
    /**
     * Show success/error messages
     */
    private function show_message($message) {
        $class = 'notice-success';
        $text = '';
        
        switch ($message) {
            case 'created':
                $text = __('Teacher created successfully.', 'academy-lesson-manager');
                break;
            case 'updated':
                $text = __('Teacher updated successfully.', 'academy-lesson-manager');
                break;
            case 'deleted':
                $text = __('Teacher deleted successfully.', 'academy-lesson-manager');
                break;
            case 'error':
                $class = 'notice-error';
                $text = __('An error occurred.', 'academy-lesson-manager');
                break;
        }
        
        if ($text) {
            echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($text) . '</p></div>';
        }
    }
    
    /**
     * Render list page
     */
    private function render_list_page() {
        
        $teachers = $this->wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY teacher_name ASC"
        );
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column column-name">' . __('Name', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-picture">' . __('Picture', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-short-bio">' . __('Short Bio', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-website">' . __('Website', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" class="manage-column column-actions">' . __('Actions', 'academy-lesson-manager') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($teachers)) {
            echo '<tr><td colspan="5" class="no-items">' . __('No teachers found.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            foreach ($teachers as $teacher) {
                $this->render_teacher_row($teacher);
            }
        }
        
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Render a single teacher row
     */
    private function render_teacher_row($teacher) {
        echo '<tr>';
        
        // Name
        echo '<td class="column-name"><strong>' . esc_html($teacher->teacher_name) . '</strong></td>';
        
        // Picture
        echo '<td class="column-picture">';
        if ($teacher->picture_id > 0) {
            $image_url = wp_get_attachment_image_url($teacher->picture_id, 'thumbnail');
            if ($image_url) {
                echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($teacher->teacher_name) . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" />';
            } else {
                echo '<span style="color: #999;">—</span>';
            }
        } else {
            echo '<span style="color: #999;">—</span>';
        }
        echo '</td>';
        
        // Short Bio
        echo '<td class="column-short-bio">';
        if (!empty($teacher->short_bio)) {
            $short_bio = wp_strip_all_tags($teacher->short_bio);
            echo esc_html(wp_trim_words($short_bio, 20));
        } else {
            echo '<span style="color: #999;">—</span>';
        }
        echo '</td>';
        
        // Website
        echo '<td class="column-website">';
        if (!empty($teacher->website_url)) {
            echo '<a href="' . esc_url($teacher->website_url) . '" target="_blank">' . esc_html($teacher->website_url) . '</a>';
        } else {
            echo '<span style="color: #999;">—</span>';
        }
        echo '</td>';
        
        // Actions
        echo '<td class="column-actions">';
        echo '<a href="?page=academy-manager-teachers&action=edit&id=' . $teacher->ID . '" class="button button-small">' . __('Edit', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-teachers&action=delete&id=' . $teacher->ID . '" class="button button-small" onclick="return confirm(\'' . __('Are you sure you want to delete this teacher?', 'academy-lesson-manager') . '\')" style="color: #dc3232;">' . __('Delete', 'academy-lesson-manager') . '</a>';
        echo '</td>';
        
        echo '</tr>';
    }
    
    /**
     * Render add page
     */
    private function render_add_page() {
        echo '<p><a href="?page=academy-manager-teachers" class="button">&larr; ' . __('Back to Teachers', 'academy-lesson-manager') . '</a></p>';
        $this->render_teacher_form();
    }
    
    /**
     * Render edit page
     */
    private function render_edit_page($id) {
        if (empty($id)) {
            echo '<div class="notice notice-error"><p>' . __('Invalid teacher ID.', 'academy-lesson-manager') . '</p></div>';
            return;
        }
        
        $teacher = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE ID = %d",
            $id
        ));
        
        if (!$teacher) {
            echo '<div class="notice notice-error"><p>' . __('Teacher not found.', 'academy-lesson-manager') . '</p></div>';
            return;
        }
        
        echo '<p><a href="?page=academy-manager-teachers" class="button">&larr; ' . __('Back to Teachers', 'academy-lesson-manager') . '</a></p>';
        $this->render_teacher_form($teacher);
    }
    
    /**
     * Render teacher form
     */
    private function render_teacher_form($teacher = null) {
        $is_edit = !empty($teacher);
        $action_url = $is_edit 
            ? admin_url('admin.php?page=academy-manager-teachers&action=edit&id=' . $teacher->ID)
            : admin_url('admin.php?page=academy-manager-teachers&action=add');
        
        echo '<form method="post" action="' . esc_url($action_url) . '" enctype="multipart/form-data">';
        wp_nonce_field('alm_teacher_action', 'alm_teacher_nonce');
        
        if ($is_edit) {
            echo '<input type="hidden" name="teacher_id" value="' . esc_attr($teacher->ID) . '" />';
        }
        
        echo '<table class="form-table">';
        echo '<tbody>';
        
        // Teacher Name
        echo '<tr>';
        echo '<th scope="row"><label for="teacher_name">' . __('Teacher Name', 'academy-lesson-manager') . ' <span class="required">*</span></label></th>';
        echo '<td>';
        
        // Get ACF teacher choices
        $acf_teachers = $this->get_acf_teacher_choices();
        
        if (!empty($acf_teachers)) {
            // Use dropdown from ACF choices
            echo '<select id="teacher_name" name="teacher_name" class="regular-text" required>';
            echo '<option value="">' . __('— Select Teacher —', 'academy-lesson-manager') . '</option>';
            foreach ($acf_teachers as $acf_teacher) {
                $selected = ($is_edit && $teacher->teacher_name === $acf_teacher) ? 'selected' : '';
                echo '<option value="' . esc_attr($acf_teacher) . '" ' . $selected . '>' . esc_html($acf_teacher) . '</option>';
            }
            echo '</select>';
            echo '<p class="description">' . __('Select from ACF field "lesson_teacher" choices. This ensures consistency with existing lessons.', 'academy-lesson-manager') . '</p>';
        } else {
            // Fallback to text input if ACF choices not available
            echo '<input type="text" id="teacher_name" name="teacher_name" value="' . ($is_edit ? esc_attr($teacher->teacher_name) : '') . '" class="regular-text" required />';
            echo '<p class="description">' . __('This name will be used in the lesson_teacher ACF field.', 'academy-lesson-manager') . '</p>';
        }
        
        echo '</td>';
        echo '</tr>';
        
        // Picture
        echo '<tr>';
        echo '<th scope="row"><label for="teacher_picture">' . __('Picture', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        $picture_id = $is_edit ? intval($teacher->picture_id) : 0;
        $picture_url = $picture_id > 0 ? wp_get_attachment_image_url($picture_id, 'medium') : '';
        
        echo '<div id="teacher-picture-preview" style="margin-bottom: 10px;">';
        if ($picture_url) {
            echo '<img src="' . esc_url($picture_url) . '" style="max-width: 300px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px;" />';
        }
        echo '</div>';
        
        echo '<input type="hidden" id="teacher_picture_id" name="teacher_picture_id" value="' . esc_attr($picture_id) . '" />';
        echo '<button type="button" class="button" id="teacher-picture-upload-btn">' . __('Choose Picture', 'academy-lesson-manager') . '</button> ';
        echo '<button type="button" class="button" id="teacher-picture-remove-btn" ' . ($picture_id > 0 ? '' : 'style="display:none;"') . '>' . __('Remove Picture', 'academy-lesson-manager') . '</button>';
        echo '<p class="description">' . __('Upload a picture for this teacher.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Short Bio
        echo '<tr>';
        echo '<th scope="row"><label for="teacher_short_bio">' . __('Short Bio', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        $short_bio = $is_edit ? $teacher->short_bio : '';
        echo '<textarea id="teacher_short_bio" name="teacher_short_bio" rows="4" cols="50" class="large-text">' . esc_textarea($short_bio) . '</textarea>';
        echo '<p class="description">' . __('A brief bio that will be displayed on lesson pages.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Long Bio
        echo '<tr>';
        echo '<th scope="row"><label for="teacher_long_bio">' . __('Long Bio', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        $long_bio = $is_edit ? $teacher->long_bio : '';
        wp_editor($long_bio, 'teacher_long_bio', array(
            'textarea_name' => 'teacher_long_bio',
            'textarea_rows' => 10,
            'media_buttons' => false,
            'teeny' => false
        ));
        echo '<p class="description">' . __('A detailed bio for teacher profile pages.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Website URL
        echo '<tr>';
        echo '<th scope="row"><label for="teacher_website_url">' . __('Website URL', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="url" id="teacher_website_url" name="teacher_website_url" value="' . ($is_edit ? esc_attr($teacher->website_url) : '') . '" class="regular-text" placeholder="https://..." />';
        echo '<p class="description">' . __('Optional link to the teacher\'s website.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Social Media URLs
        echo '<tr>';
        echo '<th scope="row">' . __('Social Media', 'academy-lesson-manager') . '</th>';
        echo '<td>';
        echo '<fieldset>';
        
        // Instagram
        echo '<p>';
        echo '<label for="teacher_instagram_url" style="display: inline-block; width: 120px;">' . __('Instagram:', 'academy-lesson-manager') . '</label>';
        $instagram_url = $is_edit && isset($teacher->instagram_url) ? $teacher->instagram_url : '';
        echo '<input type="url" id="teacher_instagram_url" name="teacher_instagram_url" value="' . esc_attr($instagram_url) . '" class="regular-text" placeholder="https://instagram.com/..." style="width: 400px;" />';
        echo '</p>';
        
        // TikTok
        echo '<p>';
        echo '<label for="teacher_tiktok_url" style="display: inline-block; width: 120px;">' . __('TikTok:', 'academy-lesson-manager') . '</label>';
        $tiktok_url = $is_edit && isset($teacher->tiktok_url) ? $teacher->tiktok_url : '';
        echo '<input type="url" id="teacher_tiktok_url" name="teacher_tiktok_url" value="' . esc_attr($tiktok_url) . '" class="regular-text" placeholder="https://tiktok.com/@..." style="width: 400px;" />';
        echo '</p>';
        
        // Facebook
        echo '<p>';
        echo '<label for="teacher_facebook_url" style="display: inline-block; width: 120px;">' . __('Facebook:', 'academy-lesson-manager') . '</label>';
        $facebook_url = $is_edit && isset($teacher->facebook_url) ? $teacher->facebook_url : '';
        echo '<input type="url" id="teacher_facebook_url" name="teacher_facebook_url" value="' . esc_attr($facebook_url) . '" class="regular-text" placeholder="https://facebook.com/..." style="width: 400px;" />';
        echo '</p>';
        
        // YouTube
        echo '<p>';
        echo '<label for="teacher_youtube_url" style="display: inline-block; width: 120px;">' . __('YouTube:', 'academy-lesson-manager') . '</label>';
        $youtube_url = $is_edit && isset($teacher->youtube_url) ? $teacher->youtube_url : '';
        echo '<input type="url" id="teacher_youtube_url" name="teacher_youtube_url" value="' . esc_attr($youtube_url) . '" class="regular-text" placeholder="https://youtube.com/..." style="width: 400px;" />';
        echo '</p>';
        
        // LinkedIn
        echo '<p>';
        echo '<label for="teacher_linkedin_url" style="display: inline-block; width: 120px;">' . __('LinkedIn:', 'academy-lesson-manager') . '</label>';
        $linkedin_url = $is_edit && isset($teacher->linkedin_url) ? $teacher->linkedin_url : '';
        echo '<input type="url" id="teacher_linkedin_url" name="teacher_linkedin_url" value="' . esc_attr($linkedin_url) . '" class="regular-text" placeholder="https://linkedin.com/in/..." style="width: 400px;" />';
        echo '</p>';
        
        echo '</fieldset>';
        echo '<p class="description">' . __('Optional social media links for the teacher.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="save_teacher" value="1" />';
        echo '<input type="submit" class="button button-primary" value="' . ($is_edit ? esc_attr__('Update Teacher', 'academy-lesson-manager') : esc_attr__('Add Teacher', 'academy-lesson-manager')) . '" />';
        if ($is_edit) {
            echo ' <a href="?page=academy-manager-teachers" class="button">' . __('Cancel', 'academy-lesson-manager') . '</a>';
        }
        echo '</p>';
        echo '</form>';
        
        // Media uploader script
        $this->enqueue_media_uploader_script();
    }
    
    /**
     * Enqueue media uploader script
     */
    private function enqueue_media_uploader_script() {
        wp_enqueue_media();
        ?>
        <script>
        jQuery(document).ready(function($) {
            var mediaUploader;
            
            $('#teacher-picture-upload-btn').on('click', function(e) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: '<?php _e('Choose Teacher Picture', 'academy-lesson-manager'); ?>',
                    button: {
                        text: '<?php _e('Use this picture', 'academy-lesson-manager'); ?>'
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#teacher_picture_id').val(attachment.id);
                    $('#teacher-picture-preview').html('<img src="' + attachment.url + '" style="max-width: 300px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px;" />');
                    $('#teacher-picture-remove-btn').show();
                });
                
                mediaUploader.open();
            });
            
            $('#teacher-picture-remove-btn').on('click', function(e) {
                e.preventDefault();
                $('#teacher_picture_id').val('0');
                $('#teacher-picture-preview').html('');
                $(this).hide();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Save teacher
     */
    private function save_teacher() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        check_admin_referer('alm_teacher_action', 'alm_teacher_nonce');
        
        $teacher_id = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
        $teacher_name = isset($_POST['teacher_name']) ? sanitize_text_field($_POST['teacher_name']) : '';
        $picture_id = isset($_POST['teacher_picture_id']) ? intval($_POST['teacher_picture_id']) : 0;
        $short_bio = isset($_POST['teacher_short_bio']) ? wp_kses_post($_POST['teacher_short_bio']) : '';
        $long_bio = isset($_POST['teacher_long_bio']) ? wp_kses_post($_POST['teacher_long_bio']) : '';
        $website_url = isset($_POST['teacher_website_url']) ? esc_url_raw($_POST['teacher_website_url']) : '';
        $instagram_url = isset($_POST['teacher_instagram_url']) ? esc_url_raw($_POST['teacher_instagram_url']) : '';
        $tiktok_url = isset($_POST['teacher_tiktok_url']) ? esc_url_raw($_POST['teacher_tiktok_url']) : '';
        $facebook_url = isset($_POST['teacher_facebook_url']) ? esc_url_raw($_POST['teacher_facebook_url']) : '';
        $youtube_url = isset($_POST['teacher_youtube_url']) ? esc_url_raw($_POST['teacher_youtube_url']) : '';
        $linkedin_url = isset($_POST['teacher_linkedin_url']) ? esc_url_raw($_POST['teacher_linkedin_url']) : '';
        
        if (empty($teacher_name)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-teachers&action=' . ($teacher_id > 0 ? 'edit&id=' . $teacher_id : 'add'))));
            exit;
        }
        
        // Generate slug from name
        $teacher_slug = sanitize_title($teacher_name);
        
        // Check if slug already exists (for new teachers or if name changed)
        if ($teacher_id > 0) {
            $existing = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT ID FROM {$this->table_name} WHERE teacher_slug = %s AND ID != %d",
                $teacher_slug,
                $teacher_id
            ));
        } else {
            $existing = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT ID FROM {$this->table_name} WHERE teacher_slug = %s",
                $teacher_slug
            ));
        }
        
        if ($existing) {
            // Append number if slug exists
            $counter = 1;
            $original_slug = $teacher_slug;
            while ($existing) {
                $teacher_slug = $original_slug . '-' . $counter;
                if ($teacher_id > 0) {
                    $existing = $this->wpdb->get_row($this->wpdb->prepare(
                        "SELECT ID FROM {$this->table_name} WHERE teacher_slug = %s AND ID != %d",
                        $teacher_slug,
                        $teacher_id
                    ));
                } else {
                    $existing = $this->wpdb->get_row($this->wpdb->prepare(
                        "SELECT ID FROM {$this->table_name} WHERE teacher_slug = %s",
                        $teacher_slug
                    ));
                }
                $counter++;
            }
        }
        
        $data = array(
            'teacher_name' => $teacher_name,
            'teacher_slug' => $teacher_slug,
            'picture_id' => $picture_id,
            'short_bio' => $short_bio,
            'long_bio' => $long_bio,
            'website_url' => $website_url,
            'instagram_url' => $instagram_url,
            'tiktok_url' => $tiktok_url,
            'facebook_url' => $facebook_url,
            'youtube_url' => $youtube_url,
            'linkedin_url' => $linkedin_url
        );
        
        if ($teacher_id > 0) {
            // Update
            $result = $this->wpdb->update(
                $this->table_name,
                $data,
                array('ID' => $teacher_id),
                array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );
            
            if ($result !== false) {
                wp_redirect(add_query_arg('message', 'updated', admin_url('admin.php?page=academy-manager-teachers&action=edit&id=' . $teacher_id)));
            } else {
                wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-teachers&action=edit&id=' . $teacher_id)));
            }
        } else {
            // Insert
            $result = $this->wpdb->insert($this->table_name, $data, array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));
            
            if ($result) {
                $new_id = $this->wpdb->insert_id;
                wp_redirect(add_query_arg('message', 'created', admin_url('admin.php?page=academy-manager-teachers&action=edit&id=' . $new_id)));
            } else {
                wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-teachers&action=add')));
            }
        }
        
        exit;
    }
    
    /**
     * Handle delete
     */
    private function handle_delete($id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        if (empty($id)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-teachers')));
            exit;
        }
        
        $result = $this->wpdb->delete($this->table_name, array('ID' => $id), array('%d'));
        
        if ($result) {
            wp_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=academy-manager-teachers')));
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-teachers')));
        }
        
        exit;
    }
    
    /**
     * Delete teacher (from form)
     */
    private function delete_teacher() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        check_admin_referer('alm_teacher_action', 'alm_teacher_nonce');
        
        $teacher_id = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
        
        if (empty($teacher_id)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-teachers')));
            exit;
        }
        
        $this->handle_delete($teacher_id);
    }
    
    /**
     * Get teacher by name (for use in other classes)
     */
    public function get_teacher_by_name($name) {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE teacher_name = %s",
            $name
        ));
    }
    
    /**
     * Get all teachers (for use in other classes)
     */
    public function get_all_teachers() {
        return $this->wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY teacher_name ASC"
        );
    }
    
    /**
     * Get ACF teacher choices from the lesson_teacher field
     */
    private function get_acf_teacher_choices() {
        if (!function_exists('get_field_object')) {
            return array();
        }
        
        // Method 1: Try to get from ACF field groups (most reliable)
        if (function_exists('acf_get_field_groups')) {
            $field_groups = acf_get_field_groups();
            foreach ($field_groups as $group) {
                $fields = acf_get_fields($group['key']);
                if ($fields) {
                    foreach ($fields as $field) {
                        if (isset($field['name']) && $field['name'] === 'lesson_teacher') {
                            if (isset($field['choices'])) {
                                // Handle both array and string formats
                                if (is_array($field['choices'])) {
                                    return array_keys($field['choices']);
                                } elseif (is_string($field['choices'])) {
                                    return $this->parse_acf_choices_string($field['choices']);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Method 2: Try to get field object directly (needs a post context)
        // Get a lesson post to get the field object
        $lessons_table = $this->database->get_table_name('lessons');
        $lesson_post_id = $this->wpdb->get_var(
            "SELECT post_id FROM {$lessons_table} WHERE post_id > 0 LIMIT 1"
        );
        
        if ($lesson_post_id) {
            $field_object = get_field_object('lesson_teacher', $lesson_post_id);
            if ($field_object && isset($field_object['choices'])) {
                if (is_array($field_object['choices'])) {
                    return array_keys($field_object['choices']);
                } elseif (is_string($field_object['choices'])) {
                    return $this->parse_acf_choices_string($field_object['choices']);
                }
            }
        }
        
        // Method 3: Get from ACF field post type (direct database query)
        // ACF stores fields as posts with post_type = 'acf-field'
        $acf_fields = $this->wpdb->get_results(
            "SELECT post_excerpt, post_content FROM {$this->wpdb->posts} 
             WHERE post_type = 'acf-field' 
             AND post_excerpt = 'lesson_teacher' 
             LIMIT 1"
        );
        
        if (!empty($acf_fields)) {
            $field_data = maybe_unserialize($acf_fields[0]->post_content);
            
            if (isset($field_data['choices'])) {
                if (is_array($field_data['choices'])) {
                    return array_keys($field_data['choices']);
                } elseif (is_string($field_data['choices'])) {
                    return $this->parse_acf_choices_string($field_data['choices']);
                }
            }
            
            // Also check if choices are stored directly in post_content as a string
            // ACF might store it as a raw string in the post_content
            if (is_string($acf_fields[0]->post_content) && strpos($acf_fields[0]->post_content, "\n") !== false) {
                // Try parsing the raw post_content as choices
                $parsed = $this->parse_acf_choices_string($acf_fields[0]->post_content);
                if (!empty($parsed)) {
                    return $parsed;
                }
            }
        }
        
        // Method 4: Try getting field key and use get_field_object
        $field_key = $this->wpdb->get_var(
            "SELECT post_name FROM {$this->wpdb->posts} 
             WHERE post_type = 'acf-field' 
             AND post_excerpt = 'lesson_teacher' 
             LIMIT 1"
        );
        
        if ($field_key) {
            $field_settings = get_field_object($field_key);
            if ($field_settings && isset($field_settings['choices'])) {
                if (is_array($field_settings['choices'])) {
                    return array_keys($field_settings['choices']);
                } elseif (is_string($field_settings['choices'])) {
                    return $this->parse_acf_choices_string($field_settings['choices']);
                }
            }
        }
        
        // Method 5: Try getting from postmeta directly (ACF stores field settings in postmeta with field key)
        $field_post = $this->wpdb->get_row(
            "SELECT ID, post_name FROM {$this->wpdb->posts} 
             WHERE post_type = 'acf-field' 
             AND post_excerpt = 'lesson_teacher' 
             LIMIT 1"
        );
        
        if ($field_post) {
            // Get field settings from postmeta
            $field_meta = get_post_meta($field_post->ID, '', true);
            if (is_array($field_meta)) {
                foreach ($field_meta as $key => $value) {
                    if ($key === 'choices' || strpos($key, 'choices') !== false) {
                        $choices_value = is_array($value) ? $value[0] : $value;
                        if (is_string($choices_value)) {
                            $parsed = $this->parse_acf_choices_string($choices_value);
                            if (!empty($parsed)) {
                                return $parsed;
                            }
                        }
                    }
                }
            }
            
            // Try getting choices from serialized post_content
            $post_content = get_post_field('post_content', $field_post->ID);
            if ($post_content) {
                $unserialized = maybe_unserialize($post_content);
                if (is_array($unserialized) && isset($unserialized['choices'])) {
                    if (is_array($unserialized['choices'])) {
                        return array_keys($unserialized['choices']);
                    } elseif (is_string($unserialized['choices'])) {
                        return $this->parse_acf_choices_string($unserialized['choices']);
                    }
                }
            }
        }
        
        return array();
    }
    
    /**
     * Parse ACF choices string (one per line format)
     */
    private function parse_acf_choices_string($choices_string) {
        $choices_array = array();
        $lines = explode("\n", $choices_string);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            // Handle format: "value : label" or just "value"
            if (strpos($line, ' : ') !== false) {
                $parts = explode(' : ', $line, 2);
                $key = trim($parts[0]);
                $choices_array[$key] = isset($parts[1]) ? trim($parts[1]) : $key;
            } else {
                // Just the value (label same as value)
                $choices_array[$line] = $line;
            }
        }
        
        return array_keys($choices_array);
    }
    
}

