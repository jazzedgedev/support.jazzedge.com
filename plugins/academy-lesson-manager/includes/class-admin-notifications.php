<?php
/**
 * Notifications Admin Class
 *
 * Provides UI/CRUD for Practice Hub notifications.
 *
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Notifications {

    /**
     * Notifications manager
     *
     * @var ALM_Notifications_Manager|null
     */
    private $notifications_manager;

    /**
     * Constructor
     */
    public function __construct() {
        if (class_exists('ALM_Notifications_Manager')) {
            $this->notifications_manager = new ALM_Notifications_Manager();
        }

        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'handle_post_actions'));
    }

    /**
     * Register admin submenu
     */
    public function register_menu() {
        add_submenu_page(
            'academy-manager',
            __('Notifications', 'academy-lesson-manager'),
            __('Notifications', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-notifications',
            array($this, 'render_page')
        );
    }

    /**
     * Handle form submissions
     */
    public function handle_post_actions() {
        if (
            !is_admin() ||
            !isset($_POST['alm_notification_action']) ||
            !current_user_can('manage_options')
        ) {
            return;
        }

        check_admin_referer('alm_notification_action', 'alm_notification_nonce');

        if (!$this->notifications_manager) {
            wp_die(__('Notifications manager is unavailable.', 'academy-lesson-manager'));
        }

        $action = sanitize_text_field($_POST['alm_notification_action']);
        $message = 'notification_error';

        switch ($action) {
            case 'save':
                $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
                $category_palette = ALM_Notifications_Manager::get_category_palette();
                $data = array(
                    'ID' => $notification_id,
                    'title' => sanitize_text_field(wp_unslash($_POST['notification_title'] ?? '')),
                    'content' => wp_kses_post(wp_unslash($_POST['notification_content'] ?? '')),
                    'link_label' => sanitize_text_field(wp_unslash($_POST['notification_link_label'] ?? '')),
                    'link_url' => esc_url_raw(wp_unslash($_POST['notification_link_url'] ?? '')),
                    'publish_at' => sanitize_text_field(wp_unslash($_POST['notification_publish_at'] ?? current_time('mysql'))),
                    'is_active' => isset($_POST['notification_is_active']) ? 1 : 0,
                    'category' => sanitize_key($_POST['notification_category'] ?? ALM_Notifications_Manager::DEFAULT_CATEGORY),
                );
                if (empty($data['category']) || !isset($category_palette[$data['category']])) {
                    $data['category'] = ALM_Notifications_Manager::DEFAULT_CATEGORY;
                }
                $result = $this->notifications_manager->save_notification($data);
                if (!is_wp_error($result)) {
                    $message = $notification_id ? 'notification_updated' : 'notification_created';
                }
                break;

            case 'toggle':
                $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
                $new_status = isset($_POST['notification_new_status']) ? intval($_POST['notification_new_status']) : 0;
                if ($notification_id && $this->notifications_manager->set_notification_status($notification_id, $new_status)) {
                    $message = $new_status ? 'notification_activated' : 'notification_deactivated';
                }
                break;

            case 'delete':
                $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
                if ($notification_id && $this->notifications_manager->delete_notification($notification_id)) {
                    $message = 'notification_deleted';
                }
                break;

            case 'save_ai_prompt':
                $ai_prompt = wp_unslash($_POST['alm_notification_ai_prompt'] ?? '');
                update_option('alm_notification_ai_prompt', $ai_prompt);
                $message = 'ai_prompt_saved';
                break;
        }

        wp_safe_redirect(add_query_arg(
            array(
                'page' => 'academy-manager-notifications',
                'notification_message' => $message,
            ),
            admin_url('admin.php')
        ));
        exit;
    }

    /**
     * Render notifications page
     */
    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'academy-lesson-manager'));
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Practice Hub Notifications', 'academy-lesson-manager') . '</h1>';
        echo '<p>' . esc_html__('Send announcements, updates, and resource links directly to students inside the Practice Hub dashboard and the [jph_notifications] shortcode.', 'academy-lesson-manager') . '</p>';
        
        echo '<style>
        .button {
            background-color: #fff;
            border: 0;
        }
        </style>';

        $this->render_notices();
        $this->render_form();

        echo '</div>';
    }

    /**
     * Render admin notices
     */
    private function render_notices() {
        if (!isset($_GET['notification_message'])) {
            return;
        }

        $notification_message = sanitize_text_field($_GET['notification_message']);
        $notice_map = array(
            'notification_created' => array('type' => 'success', 'text' => __('Notification published.', 'academy-lesson-manager')),
            'notification_updated' => array('type' => 'success', 'text' => __('Notification updated.', 'academy-lesson-manager')),
            'notification_deleted' => array('type' => 'success', 'text' => __('Notification deleted.', 'academy-lesson-manager')),
            'notification_activated' => array('type' => 'success', 'text' => __('Notification activated.', 'academy-lesson-manager')),
            'notification_deactivated' => array('type' => 'success', 'text' => __('Notification hidden.', 'academy-lesson-manager')),
            'ai_prompt_saved' => array('type' => 'success', 'text' => __('AI prompt saved successfully.', 'academy-lesson-manager')),
            'notification_error' => array('type' => 'error', 'text' => __('Unable to save notification. Please try again.', 'academy-lesson-manager')),
        );

        if (isset($notice_map[$notification_message])) {
            $notice = $notice_map[$notification_message];
            printf('<div class="notice notice-%1$s"><p>%2$s</p></div>', esc_attr($notice['type']), esc_html($notice['text']));
        }
    }

    /**
     * Render notifications form & list
     */
    private function render_form() {
        if (!$this->notifications_manager) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Notifications manager is unavailable.', 'academy-lesson-manager') . '</p></div>';
            return;
        }

        $editing_id = isset($_GET['edit_notification']) ? intval($_GET['edit_notification']) : 0;
        $editing_notification = $editing_id ? $this->notifications_manager->get_notification($editing_id) : null;
        $notifications = $this->notifications_manager->get_notifications(array(
            'status' => 'all',
            'limit' => 20,
            'order' => 'DESC',
        ));

        $publish_timestamp = current_time('timestamp');
        if (!empty($editing_notification['publish_at'])) {
            $publish_timestamp = strtotime($editing_notification['publish_at']);
        }
        $publish_value = date('Y-m-d\TH:i', $publish_timestamp);

        echo '<div id="alm-notifications-panel" class="postbox" style="margin-top:20px; padding:24px;">';
        echo '<h2>' . esc_html($editing_notification ? __('Edit Notification', 'academy-lesson-manager') : __('Create Notification', 'academy-lesson-manager')) . '</h2>';

        echo '<form method="post" action="" style="margin-top: 16px;">';
        wp_nonce_field('alm_notification_action', 'alm_notification_nonce');
        echo '<input type="hidden" name="alm_notification_action" value="save" />';
        echo '<input type="hidden" name="notification_id" value="' . esc_attr($editing_notification['ID'] ?? 0) . '" />';

        $category_palette = ALM_Notifications_Manager::get_category_palette();
        $selected_category = sanitize_key($editing_notification['category'] ?? ALM_Notifications_Manager::DEFAULT_CATEGORY);
        if (!isset($category_palette[$selected_category])) {
            $selected_category = ALM_Notifications_Manager::DEFAULT_CATEGORY;
        }

        echo '<table class="form-table"><tbody>';

        echo '<tr>';
        echo '<th scope="row"><label for="notification_title">' . esc_html__('Title', 'academy-lesson-manager') . '</label></th>';
        $title_value = !empty($editing_notification['title']) ? wp_unslash($editing_notification['title']) : '';
        echo '<td><input type="text" class="regular-text" id="notification_title" name="notification_title" value="' . esc_attr($title_value) . '" required /></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row"><label for="notification_category">' . esc_html__('Notification Type', 'academy-lesson-manager') . '</label></th>';
        echo '<td><select id="notification_category" name="notification_category">';
        foreach ($category_palette as $slug => $data) {
            printf(
                '<option value="%1$s"%3$s>%2$s</option>',
                esc_attr($slug),
                esc_html($data['label']),
                selected($selected_category, $slug, false)
            );
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Choose how this notification should be categorized for filtering and color coding.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row"><label for="notification_content">' . esc_html__('Message', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        $content_value = !empty($editing_notification['content']) ? wp_unslash($editing_notification['content']) : '';
        wp_editor($content_value, 'alm_notification_content', array(
            'textarea_name' => 'notification_content',
            'textarea_rows' => 6,
            'media_buttons' => false,
            'teeny' => true,
        ));
        echo '<p class="description" style="margin-bottom: 8px;">' . esc_html__('Use the editor to add formatted text, links, or highlights.', 'academy-lesson-manager') . '</p>';
        echo '<button type="button" id="alm-expand-notification-btn" class="button button-secondary" style="margin-top: 8px;">';
        echo '<span class="dashicons dashicons-star-filled" style="vertical-align: middle; margin-top: 3px;"></span> ';
        echo esc_html__('Expand with AI', 'academy-lesson-manager');
        echo '</button>';
        echo '<span id="alm-expand-notification-spinner" class="spinner" style="float: none; margin-left: 8px; visibility: hidden;"></span>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row"><label for="notification_link_label">' . esc_html__('Button Label (optional)', 'academy-lesson-manager') . '</label></th>';
        $link_label_value = !empty($editing_notification['link_label']) ? wp_unslash($editing_notification['link_label']) : '';
        echo '<td><input type="text" id="notification_link_label" name="notification_link_label" class="regular-text" value="' . esc_attr($link_label_value) . '" placeholder="' . esc_attr__('View Resource', 'academy-lesson-manager') . '" /></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row"><label for="notification_link_url">' . esc_html__('Button URL (optional)', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="url" id="notification_link_url" name="notification_link_url" class="regular-text" value="' . esc_attr($editing_notification['link_url'] ?? '') . '" placeholder="https://example.com/lesson" /></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row"><label for="notification_publish_at">' . esc_html__('Publish Date', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="datetime-local" id="notification_publish_at" name="notification_publish_at" value="' . esc_attr($publish_value) . '" /></td>';
        echo '</tr>';

        $is_active = isset($editing_notification['is_active']) ? intval($editing_notification['is_active']) : 1;
        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Status', 'academy-lesson-manager') . '</th>';
        echo '<td><label><input type="checkbox" name="notification_is_active" value="1" ' . checked(1, $is_active, false) . ' /> ' . esc_html__('Visible to students', 'academy-lesson-manager') . '</label></td>';
        echo '</tr>';

        echo '</tbody></table>';

        echo '<p class="submit">';
        echo '<button type="submit" class="button button-primary">' . esc_html($editing_notification ? __('Update Notification', 'academy-lesson-manager') : __('Publish Notification', 'academy-lesson-manager')) . '</button> ';
        if ($editing_notification) {
            echo '<a href="' . esc_url(remove_query_arg('edit_notification')) . '#alm-notifications-panel" class="button button-secondary">' . esc_html__('Cancel Editing', 'academy-lesson-manager') . '</a>';
        }
        echo '</p>';
        echo '</form>';

        echo '<hr />';

        // AI Prompt Editor Section
        $default_prompt = "You are an AI assistant helping to expand and improve notification messages for a music education platform (Jazzedge Academy). 

Your task is to take a brief notification message and expand it into a more detailed, engaging, and informative message while maintaining the original intent and tone.

Guidelines:
- Keep the friendly, encouraging tone appropriate for students
- Expand on key points naturally
- Make it more engaging and informative
- Maintain professional but warm communication style
- Don't add information that wasn't implied in the original
- Keep it concise but comprehensive
- Use clear, accessible language

Return only the expanded text, no explanations or additional commentary.";
        
        $saved_prompt = get_option('alm_notification_ai_prompt', $default_prompt);
        
        echo '<div id="alm-ai-prompt-panel" class="postbox" style="margin-top:20px; padding:24px;">';
        echo '<h2>' . esc_html__('AI Expansion Prompt', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . esc_html__('Customize the system prompt used when expanding notification text with AI. This prompt instructs the AI on how to expand your notification messages.', 'academy-lesson-manager') . '</p>';
        
        echo '<form method="post" action="" style="margin-top: 16px;">';
        wp_nonce_field('alm_notification_action', 'alm_notification_nonce');
        echo '<input type="hidden" name="alm_notification_action" value="save_ai_prompt" />';
        
        echo '<table class="form-table"><tbody>';
        echo '<tr>';
        echo '<th scope="row"><label for="alm_notification_ai_prompt">' . esc_html__('System Prompt', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<textarea id="alm_notification_ai_prompt" name="alm_notification_ai_prompt" rows="15" class="large-text code" style="font-family: monospace; font-size: 13px;">' . esc_textarea($saved_prompt) . '</textarea>';
        echo '<p class="description">' . esc_html__('This prompt is sent to the AI along with your notification text. Modify it to change how the AI expands your messages.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '</tbody></table>';
        
        echo '<p class="submit">';
        echo '<button type="submit" class="button button-primary">' . esc_html__('Save AI Prompt', 'academy-lesson-manager') . '</button> ';
        echo '<button type="button" id="alm-reset-ai-prompt" class="button button-secondary">' . esc_html__('Reset to Default', 'academy-lesson-manager') . '</button>';
        echo '</p>';
        echo '</form>';
        echo '</div>';
        
        // Add JavaScript for reset button
        ?>
        <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                var $resetBtn = $('#alm-reset-ai-prompt');
                var $promptTextarea = $('#alm_notification_ai_prompt');
                var defaultPrompt = <?php echo json_encode($default_prompt); ?>;
                
                $resetBtn.on('click', function() {
                    if (confirm('<?php echo esc_js(__('Reset the prompt to the default? This will overwrite your current prompt.', 'academy-lesson-manager')); ?>')) {
                        $promptTextarea.val(defaultPrompt);
                    }
                });
            });
        })(jQuery);
        </script>
        <?php

        echo '<hr />';

        echo '<h2>' . esc_html__('Recent Notifications', 'academy-lesson-manager') . '</h2>';

        if (empty($notifications)) {
            echo '<p>' . esc_html__('No notifications yet.', 'academy-lesson-manager') . '</p>';
        } else {
            echo '<table class="widefat striped">';
            echo '<thead><tr>';
            echo '<th>' . esc_html__('Title', 'academy-lesson-manager') . '</th>';
            echo '<th>' . esc_html__('Type', 'academy-lesson-manager') . '</th>';
            echo '<th>' . esc_html__('Status', 'academy-lesson-manager') . '</th>';
            echo '<th>' . esc_html__('Publish Date', 'academy-lesson-manager') . '</th>';
            echo '<th>' . esc_html__('Link', 'academy-lesson-manager') . '</th>';
            echo '<th>' . esc_html__('Actions', 'academy-lesson-manager') . '</th>';
            echo '</tr></thead><tbody>';
            foreach ($notifications as $notification) {
                $active = intval($notification['is_active']);
                $status_label = $active ? __('Active', 'academy-lesson-manager') : __('Hidden', 'academy-lesson-manager');
                $toggle_status = $active ? 0 : 1;
                $toggle_label = $active ? __('Hide', 'academy-lesson-manager') : __('Activate', 'academy-lesson-manager');
                echo '<tr>';
                echo '<td><strong>' . esc_html(wp_unslash($notification['title'])) . '</strong></td>';
                $type_slug = sanitize_key($notification['category'] ?? ALM_Notifications_Manager::DEFAULT_CATEGORY);
                $type_label = $category_palette[$type_slug]['label'] ?? $category_palette[ALM_Notifications_Manager::DEFAULT_CATEGORY]['label'];
                echo '<td>' . esc_html($type_label) . '</td>';
                echo '<td>' . esc_html($status_label) . '</td>';
                echo '<td>' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($notification['publish_at']))) . '</td>';
                if (!empty($notification['link_url'])) {
                    echo '<td><a href="' . esc_url($notification['link_url']) . '" target="_blank" rel="noopener">' . esc_html($notification['link_label'] ?: __('View Resource', 'academy-lesson-manager')) . '</a></td>';
                } else {
                    echo '<td>—</td>';
                }
                echo '<td style="display:flex; gap:6px; flex-wrap:wrap;">';
                echo '<a href="' . esc_url(add_query_arg('edit_notification', intval($notification['ID']))) . '" class="button button-small">' . esc_html__('Edit', 'academy-lesson-manager') . '</a>';

                echo '<form method="post" action="" style="display:inline-block;">';
                wp_nonce_field('alm_notification_action', 'alm_notification_nonce');
                echo '<input type="hidden" name="alm_notification_action" value="toggle" />';
                echo '<input type="hidden" name="notification_id" value="' . esc_attr($notification['ID']) . '" />';
                echo '<input type="hidden" name="notification_new_status" value="' . esc_attr($toggle_status) . '" />';
                echo '<button type="submit" class="button button-small">' . esc_html($toggle_label) . '</button>';
                echo '</form>';

                echo '<form method="post" action="" style="display:inline-block;" onsubmit="return confirm(\'' . esc_js(__('Delete this notification?', 'academy-lesson-manager')) . '\');">';
                wp_nonce_field('alm_notification_action', 'alm_notification_nonce');
                echo '<input type="hidden" name="alm_notification_action" value="delete" />';
                echo '<input type="hidden" name="notification_id" value="' . esc_attr($notification['ID']) . '" />';
                echo '<button type="submit" class="button button-small button-link-delete">' . esc_html__('Delete', 'academy-lesson-manager') . '</button>';
                echo '</form>';

                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }

        echo '</div>';
        
        // Add JavaScript for AI expansion
        ?>
        <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                var $expandBtn = $('#alm-expand-notification-btn');
                var $spinner = $('#alm-expand-notification-spinner');
                
                $expandBtn.on('click', function() {
                    // Get the current content from the editor
                    var editor = tinyMCE.get('alm_notification_content');
                    var currentText = '';
                    
                    if (editor && !editor.isHidden()) {
                        // Get text content from visual editor
                        currentText = editor.getContent({format: 'text'});
                    } else {
                        // Get text from textarea
                        currentText = $('#alm_notification_content').val();
                    }
                    
                    // Strip HTML tags if any
                    currentText = currentText.replace(/<[^>]*>/g, '').trim();
                    
                    if (!currentText) {
                        alert('<?php echo esc_js(__('Please enter some text to expand.', 'academy-lesson-manager')); ?>');
                        return;
                    }
                    
                    // Disable button and show spinner
                    $expandBtn.prop('disabled', true);
                    $spinner.css('visibility', 'visible');
                    
                    // Make API request
                    $.ajax({
                        url: '<?php echo esc_url(rest_url('alm/v1/notifications/expand')); ?>',
                        method: 'POST',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                        },
                        data: JSON.stringify({
                            text: currentText
                        }),
                        contentType: 'application/json',
                        dataType: 'json',
                        success: function(response) {
                            if (response.expanded_text) {
                                // Update the editor with expanded text
                                if (editor && !editor.isHidden()) {
                                    // Visual editor is active
                                    editor.setContent(response.expanded_text);
                                } else {
                                    // Text editor is active
                                    $('#alm_notification_content').val(response.expanded_text);
                                }
                                
                                // Show success message
                                alert('<?php echo esc_js(__('Notification text expanded successfully!', 'academy-lesson-manager')); ?>');
                            } else {
                                alert('<?php echo esc_js(__('No expanded text received from AI.', 'academy-lesson-manager')); ?>');
                            }
                        },
                        error: function(xhr) {
                            var errorMsg = '<?php echo esc_js(__('Error expanding notification text.', 'academy-lesson-manager')); ?>';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg += ' ' + xhr.responseJSON.message;
                            }
                            alert(errorMsg);
                        },
                        complete: function() {
                            // Re-enable button and hide spinner
                            $expandBtn.prop('disabled', false);
                            $spinner.css('visibility', 'hidden');
                        }
                    });
                });
            });
        })(jQuery);
        </script>
        <?php
    }
}

