<?php
/**
 * ALM Admin FAQs Class
 * 
 * Handles FAQ CRUD operations as a tab in Academy Lesson Manager Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_FAQs {
    
    private $wpdb;
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'alm_faqs';
    }
    
    /**
     * Render FAQs tab content
     */
    public function render_tab() {
        // Handle form submissions - must check before any output
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_faq'])) {
            $this->save_faq();
            return;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_faq'])) {
            $this->delete_faq();
            return; // Exit early after delete to prevent form display
        }
        
        // Get current FAQ if editing
        $edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
        $current_faq = null;
        if ($edit_id) {
            $current_faq = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $edit_id
            ));
        }
        
        // Get all FAQs grouped by category
        $faqs = $this->wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY category ASC, display_order ASC, id ASC"
        );
        
        // Get all categories
        $categories = $this->wpdb->get_col(
            "SELECT DISTINCT category FROM {$this->table_name} ORDER BY category ASC"
        );
        if (empty($categories)) {
            $categories = array('membership');
        }
        
        ?>
        <div class="alm-settings-section">
            <h2><?php _e('FAQ Management', 'academy-lesson-manager'); ?></h2>
            <p class="description"><?php _e('Manage frequently asked questions for different pages and promotions.', 'academy-lesson-manager'); ?></p>
            
            <?php if (isset($_GET['message'])): ?>
                <?php
                $message = sanitize_text_field($_GET['message']);
                $class = 'notice-success';
                $text = '';
                switch ($message) {
                    case 'faq_saved':
                        $text = __('FAQ saved successfully.', 'academy-lesson-manager');
                        break;
                    case 'faq_deleted':
                        $text = __('FAQ deleted successfully.', 'academy-lesson-manager');
                        break;
                    case 'error':
                        $class = 'notice-error';
                        $text = __('An error occurred.', 'academy-lesson-manager');
                        break;
                }
                if ($text):
                ?>
                <div class="notice <?php echo esc_attr($class); ?> is-dismissible">
                    <p><?php echo esc_html($text); ?></p>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="alm-faq-admin">
                <!-- Add/Edit Form -->
                <div class="alm-faq-form">
                    <h3><?php echo $current_faq ? __('Edit FAQ', 'academy-lesson-manager') : __('Add New FAQ', 'academy-lesson-manager'); ?></h3>
                    
                    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=academy-manager-settings&tab=faqs' . ($current_faq ? '&edit=' . $current_faq->id : ''))); ?>">
                        <?php wp_nonce_field('alm_faq_action', 'alm_faq_nonce'); ?>
                        
                        <?php if ($current_faq): ?>
                            <input type="hidden" name="faq_id" value="<?php echo esc_attr($current_faq->id); ?>" />
                        <?php endif; ?>
                        
                        <table class="form-table">
                            <tbody>
                                <tr>
                                    <th scope="row">
                                        <label for="faq_question"><?php _e('Question', 'academy-lesson-manager'); ?> <span class="required">*</span></label>
                                    </th>
                                    <td>
                                        <input type="text" id="faq_question" name="faq_question" value="<?php echo $current_faq ? esc_attr($current_faq->question) : ''; ?>" class="regular-text" required />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="faq_answer"><?php _e('Answer', 'academy-lesson-manager'); ?> <span class="required">*</span></label>
                                    </th>
                                    <td>
                                        <?php
                                        $answer_content = $current_faq ? $current_faq->answer : '';
                                        wp_editor($answer_content, 'faq_answer', array(
                                            'textarea_name' => 'faq_answer',
                                            'textarea_rows' => 10,
                                            'media_buttons' => false,
                                            'teeny' => true
                                        ));
                                        ?>
                                        <p class="description"><?php _e('You can use HTML formatting. Links will automatically open in a new tab.', 'academy-lesson-manager'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="faq_category"><?php _e('Category', 'academy-lesson-manager'); ?> <span class="required">*</span></label>
                                    </th>
                                    <td>
                                        <select id="faq_category" name="faq_category" class="regular-text" required>
                                            <option value=""><?php _e('-- Select or Enter New Category --', 'academy-lesson-manager'); ?></option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo esc_attr($cat); ?>" <?php selected($current_faq && $current_faq->category === $cat); ?>>
                                                    <?php echo esc_html(ucfirst($cat)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" id="faq_category_new" name="faq_category_new" placeholder="<?php esc_attr_e('Or enter new category name', 'academy-lesson-manager'); ?>" class="regular-text" style="margin-top: 5px; display: none;" />
                                        <p class="description"><?php _e('Select an existing category or enter a new one (e.g., membership, black-friday).', 'academy-lesson-manager'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="faq_display_order"><?php _e('Display Order', 'academy-lesson-manager'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="faq_display_order" name="faq_display_order" value="<?php echo $current_faq ? esc_attr($current_faq->display_order) : '0'; ?>" class="small-text" min="0" />
                                        <p class="description"><?php _e('Lower numbers appear first. FAQs with the same order are sorted by ID.', 'academy-lesson-manager'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="faq_is_active"><?php _e('Status', 'academy-lesson-manager'); ?></label>
                                    </th>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="faq_is_active" name="faq_is_active" value="1" <?php checked(!$current_faq || $current_faq->is_active); ?> />
                                            <?php _e('Active (visible on frontend)', 'academy-lesson-manager'); ?>
                                        </label>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <p class="submit">
                            <input type="hidden" name="save_faq" value="1" />
                            <input type="submit" class="button button-primary" value="<?php echo $current_faq ? esc_attr__('Update FAQ', 'academy-lesson-manager') : esc_attr__('Add FAQ', 'academy-lesson-manager'); ?>" />
                            <?php if ($current_faq): ?>
                                <a href="?page=academy-manager-settings&tab=faqs" class="button"><?php _e('Cancel', 'academy-lesson-manager'); ?></a>
                            <?php endif; ?>
                        </p>
                    </form>
                </div>
                
                <!-- FAQs List -->
                <div class="alm-faq-list" style="margin-top: 40px;">
                    <h3><?php _e('Existing FAQs', 'academy-lesson-manager'); ?></h3>
                    
                    <?php if (empty($faqs)): ?>
                        <p><?php _e('No FAQs found. Add your first FAQ above.', 'academy-lesson-manager'); ?></p>
                    <?php else: ?>
                        <?php
                        $current_category = '';
                        foreach ($faqs as $faq):
                            if ($current_category !== $faq->category):
                                if ($current_category !== ''):
                                    echo '</table></div>';
                                endif;
                                $current_category = $faq->category;
                                ?>
                                <div class="alm-faq-category-section" style="margin-bottom: 30px;">
                                    <h4 style="margin-bottom: 10px; color: #239B90; font-size: 18px;">
                                        <?php echo esc_html(ucfirst($faq->category)); ?> FAQs
                                    </h4>
                                    <table class="wp-list-table widefat fixed striped">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%;"><?php _e('Order', 'academy-lesson-manager'); ?></th>
                                                <th style="width: 12%;"><?php _e('Category', 'academy-lesson-manager'); ?></th>
                                                <th style="width: 28%;"><?php _e('Question', 'academy-lesson-manager'); ?></th>
                                                <th style="width: 35%;"><?php _e('Answer Preview', 'academy-lesson-manager'); ?></th>
                                                <th style="width: 8%;"><?php _e('Status', 'academy-lesson-manager'); ?></th>
                                                <th style="width: 12%;"><?php _e('Actions', 'academy-lesson-manager'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            <?php endif; ?>
                            
                            <tr>
                                <td><?php echo esc_html($faq->display_order); ?></td>
                                <td><strong><?php echo esc_html($faq->category); ?></strong></td>
                                <td><strong><?php echo esc_html($faq->question); ?></strong></td>
                                <td><?php echo wp_trim_words(strip_tags($faq->answer), 20); ?></td>
                                <td>
                                    <?php if ($faq->is_active): ?>
                                        <span style="color: green;"><?php _e('Active', 'academy-lesson-manager'); ?></span>
                                    <?php else: ?>
                                        <span style="color: #999;"><?php _e('Inactive', 'academy-lesson-manager'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="white-space: nowrap;">
                                    <a href="?page=academy-manager-settings&tab=faqs&edit=<?php echo esc_attr($faq->id); ?>" class="button button-small" style="margin-right: 5px;"><?php _e('Edit', 'academy-lesson-manager'); ?></a>
                                    <form method="post" action="" style="display: inline;" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to delete this FAQ?', 'academy-lesson-manager'); ?>');">
                                        <?php wp_nonce_field('alm_faq_action', 'alm_faq_nonce'); ?>
                                        <input type="hidden" name="faq_id" value="<?php echo esc_attr($faq->id); ?>" />
                                        <input type="submit" name="delete_faq" class="button button-small" value="<?php esc_attr_e('Delete', 'academy-lesson-manager'); ?>" style="color: #a00;" />
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($current_category !== ''): ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <style>
        .alm-faq-admin {
            max-width: 1200px;
        }
        .alm-faq-form {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin-bottom: 20px;
        }
        .alm-faq-form h3 {
            margin-top: 0;
        }
        .alm-faq-category-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 15px;
        }
        .required {
            color: #dc3232;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Show/hide new category input based on select
            $('#faq_category').on('change', function() {
                if ($(this).val() === '') {
                    $('#faq_category_new').show().focus();
                } else {
                    $('#faq_category_new').hide().val('');
                }
            });
            
            // If new category input has value, clear select
            $('#faq_category_new').on('input', function() {
                if ($(this).val() !== '') {
                    $('#faq_category').val('');
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Save FAQ
     */
    private function save_faq() {
        check_admin_referer('alm_faq_action', 'alm_faq_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        $faq_id = isset($_POST['faq_id']) ? intval($_POST['faq_id']) : 0;
        $question = isset($_POST['faq_question']) ? sanitize_text_field($_POST['faq_question']) : '';
        
        // Handle wp_editor content - it comes through as raw POST data
        $answer = '';
        if (isset($_POST['faq_answer'])) {
            // WordPress editor content is already unslashed by WordPress, but we need to handle it properly
            $answer = wp_kses_post(stripslashes_deep($_POST['faq_answer']));
        }
        
        $category_new = isset($_POST['faq_category_new']) ? trim(sanitize_text_field($_POST['faq_category_new'])) : '';
        $category = isset($_POST['faq_category']) ? trim(sanitize_text_field($_POST['faq_category'])) : '';
        $display_order = isset($_POST['faq_display_order']) ? intval($_POST['faq_display_order']) : 0;
        $is_active = isset($_POST['faq_is_active']) ? 1 : 0;
        
        // Use new category if provided, otherwise use selected category
        if (!empty($category_new)) {
            $category = strtolower($category_new);
        }
        
        // If editing and category is empty, get it from the existing FAQ
        if (empty($category) && $faq_id) {
            $existing_faq = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT category FROM {$this->table_name} WHERE id = %d",
                $faq_id
            ));
            if ($existing_faq) {
                $category = $existing_faq->category;
            }
        }
        
        if (empty($question) || empty($answer) || empty($category)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs' . ($faq_id ? '&edit=' . $faq_id : ''))));
            exit;
        }
        
        // Normalize category (lowercase, no spaces, use hyphens)
        $category = strtolower(trim($category));
        $category = preg_replace('/[^a-z0-9-]/', '-', $category);
        $category = preg_replace('/-+/', '-', $category);
        $category = trim($category, '-');
        
        $data = array(
            'question' => $question,
            'answer' => $answer,
            'category' => $category,
            'display_order' => $display_order,
            'is_active' => $is_active,
            'updated_at' => current_time('mysql')
        );
        
        if ($faq_id) {
            // Update existing FAQ
            $where = array('id' => $faq_id);
            $result = $this->wpdb->update(
                $this->table_name,
                $data,
                $where,
                array('%s', '%s', '%s', '%d', '%d', '%s'),
                array('%d')
            );
            
            // wpdb->update returns false on error, or number of rows affected (0 if no change, but that's OK)
            if ($result === false) {
                // Log error for debugging
                error_log('FAQ Update Error: ' . $this->wpdb->last_error);
                wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs&edit=' . $faq_id)));
                exit;
            }
        } else {
            // Insert new FAQ
            $data['created_at'] = current_time('mysql');
            $result = $this->wpdb->insert(
                $this->table_name,
                $data,
                array('%s', '%s', '%s', '%d', '%d', '%s', '%s')
            );
            
            if ($result === false) {
                // Log error for debugging
                error_log('FAQ Insert Error: ' . $this->wpdb->last_error);
                wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
                exit;
            }
        }
        
        // Success - redirect without edit parameter to show the list
        wp_redirect(add_query_arg('message', 'faq_saved', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
        exit;
    }
    
    /**
     * Delete FAQ
     */
    private function delete_faq() {
        check_admin_referer('alm_faq_action', 'alm_faq_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        $faq_id = isset($_POST['faq_id']) ? intval($_POST['faq_id']) : 0;
        
        if (!$faq_id) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        $result = $this->wpdb->delete(
            $this->table_name,
            array('id' => $faq_id),
            array('%d')
        );
        
        if ($result !== false) {
            wp_redirect(add_query_arg('message', 'faq_deleted', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
        }
        exit;
    }
}

