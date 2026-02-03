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
    private $categories_table_name;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'alm_faqs';
        $this->categories_table_name = $wpdb->prefix . 'alm_faq_categories';
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
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_faqs'])) {
            $this->import_faqs();
            return; // Exit early after import to prevent form display
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
            $this->save_category();
            return;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
            $this->delete_category();
            return;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_category'])) {
            $this->rename_category();
            return;
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
        
        // Get all categories from categories table with FAQ counts
        $categories_with_counts = $this->wpdb->get_results(
            "SELECT c.category_name as category, 
                    COALESCE(COUNT(f.id), 0) as count 
             FROM {$this->categories_table_name} c
             LEFT JOIN {$this->table_name} f ON f.category = c.category_name
             GROUP BY c.id, c.category_name
             ORDER BY c.display_order ASC, c.category_name ASC"
        );
        
        // Ensure count is an integer
        foreach ($categories_with_counts as $cat) {
            $cat->count = intval($cat->count);
        }
        
        // Get all categories as array (from categories table)
        $categories = $this->wpdb->get_col(
            "SELECT category_name FROM {$this->categories_table_name} ORDER BY display_order ASC, category_name ASC"
        );
        if (empty($categories)) {
            // Fallback: get from FAQs table if categories table is empty
            $categories = $this->wpdb->get_col(
                "SELECT DISTINCT category FROM {$this->table_name} WHERE category IS NOT NULL AND category != '' ORDER BY category ASC"
            );
            if (empty($categories)) {
                $categories = array('membership');
            }
        }
        
        // Get category being edited
        $edit_category = isset($_GET['edit_category']) ? sanitize_text_field($_GET['edit_category']) : '';
        
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
                    case 'faqs_imported':
                        $imported_count = isset($_GET['imported']) ? intval($_GET['imported']) : 0;
                        $text = sprintf(__('%d FAQ(s) imported successfully.', 'academy-lesson-manager'), $imported_count);
                        break;
                    case 'import_error':
                        $class = 'notice-error';
                        $error_msg = isset($_GET['error_msg']) ? sanitize_text_field(urldecode($_GET['error_msg'])) : '';
                        $text = __('Import failed.', 'academy-lesson-manager') . ($error_msg ? ' ' . esc_html($error_msg) : '');
                        break;
                    case 'category_saved':
                        $text = __('Category saved successfully.', 'academy-lesson-manager');
                        break;
                    case 'category_deleted':
                        $text = __('Category deleted successfully.', 'academy-lesson-manager');
                        break;
                    case 'category_renamed':
                        $text = __('Category renamed successfully.', 'academy-lesson-manager');
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
                <!-- Import Section -->
                <div class="alm-faq-import" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-bottom: 20px;">
                    <h3><?php _e('Import FAQs', 'academy-lesson-manager'); ?></h3>
                    <p class="description">
                        <?php _e('Import FAQs from a CSV file. The CSV should have the following columns:', 'academy-lesson-manager'); ?>
                    </p>
                    <ul style="list-style: disc; margin-left: 20px; margin-bottom: 15px;">
                        <li><strong>question</strong> - <?php _e('The FAQ question (required)', 'academy-lesson-manager'); ?></li>
                        <li><strong>answer</strong> - <?php _e('The FAQ answer (required)', 'academy-lesson-manager'); ?></li>
                        <li><strong>category</strong> - <?php _e('The FAQ category (required, default: membership)', 'academy-lesson-manager'); ?></li>
                        <li><strong>display_order</strong> - <?php _e('Display order number (optional, default: 0)', 'academy-lesson-manager'); ?></li>
                        <li><strong>is_active</strong> - <?php _e('Active status: 1 for active, 0 for inactive (optional, default: 1)', 'academy-lesson-manager'); ?></li>
                    </ul>
                    <p class="description">
                        <strong><?php _e('Note:', 'academy-lesson-manager'); ?></strong> <?php _e('The first row can be a header row with column names. CSV files should be UTF-8 encoded.', 'academy-lesson-manager'); ?>
                    </p>
                    <form method="post" action="" enctype="multipart/form-data" style="margin-top: 15px;">
                        <?php wp_nonce_field('alm_faq_import', 'alm_faq_import_nonce'); ?>
                        <table class="form-table">
                            <tbody>
                                <tr>
                                    <th scope="row">
                                        <label for="faq_import_file"><?php _e('CSV File', 'academy-lesson-manager'); ?></label>
                                    </th>
                                    <td>
                                        <input type="file" id="faq_import_file" name="faq_import_file" accept=".csv" required />
                                        <p class="description"><?php _e('Select a CSV file to import.', 'academy-lesson-manager'); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="submit">
                            <input type="hidden" name="import_faqs" value="1" />
                            <input type="submit" class="button button-primary" value="<?php esc_attr_e('Import FAQs', 'academy-lesson-manager'); ?>" />
                        </p>
                    </form>
                </div>
                
                <!-- Category Management Section -->
                <div class="alm-faq-categories" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-bottom: 20px;">
                    <h3><?php _e('Manage Categories', 'academy-lesson-manager'); ?></h3>
                    <p class="description">
                        <?php _e('Manage FAQ categories. You can add new categories, rename existing ones, or delete unused categories.', 'academy-lesson-manager'); ?>
                    </p>
                    
                    <!-- Add/Edit Category Form -->
                    <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                        <h4><?php echo $edit_category ? __('Edit Category', 'academy-lesson-manager') : __('Add New Category', 'academy-lesson-manager'); ?></h4>
                        <form method="post" action="" style="margin-top: 10px;">
                            <?php wp_nonce_field('alm_faq_category_action', 'alm_faq_category_nonce'); ?>
                            <?php if ($edit_category): ?>
                                <input type="hidden" name="old_category" value="<?php echo esc_attr($edit_category); ?>" />
                            <?php endif; ?>
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="category_name"><?php _e('Category Name', 'academy-lesson-manager'); ?> <span class="required">*</span></label>
                                        </th>
                                        <td>
                                            <input type="text" id="category_name" name="category_name" 
                                                   value="<?php echo $edit_category ? esc_attr($edit_category) : ''; ?>" 
                                                   class="regular-text" required 
                                                   placeholder="<?php esc_attr_e('e.g., membership, black-friday', 'academy-lesson-manager'); ?>" />
                                            <p class="description">
                                                <?php _e('Category names will be converted to lowercase with hyphens. Spaces and special characters will be replaced.', 'academy-lesson-manager'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="submit">
                                <input type="hidden" name="save_category" value="1" />
                                <input type="submit" class="button button-primary" 
                                       value="<?php echo $edit_category ? esc_attr__('Update Category', 'academy-lesson-manager') : esc_attr__('Add Category', 'academy-lesson-manager'); ?>" />
                                <?php if ($edit_category): ?>
                                    <a href="?page=academy-manager-settings&tab=faqs" class="button"><?php _e('Cancel', 'academy-lesson-manager'); ?></a>
                                <?php endif; ?>
                            </p>
                        </form>
                    </div>
                    
                    <!-- Categories List -->
                    <div>
                        <h4><?php _e('Existing Categories', 'academy-lesson-manager'); ?></h4>
                        <?php if (empty($categories_with_counts)): ?>
                            <p><?php _e('No categories found. Add your first category above.', 'academy-lesson-manager'); ?></p>
                        <?php else: ?>
                            <table class="wp-list-table widefat fixed striped" style="margin-top: 10px;">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;"><?php _e('Category Name', 'academy-lesson-manager'); ?></th>
                                        <th style="width: 15%;"><?php _e('FAQ Count', 'academy-lesson-manager'); ?></th>
                                        <th style="width: 55%;"><?php _e('Actions', 'academy-lesson-manager'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories_with_counts as $cat_data): ?>
                                        <tr>
                                            <td><strong><?php echo esc_html(ucfirst($cat_data->category)); ?></strong></td>
                                            <td><?php echo esc_html($cat_data->count); ?> <?php echo $cat_data->count == 1 ? __('FAQ', 'academy-lesson-manager') : __('FAQs', 'academy-lesson-manager'); ?></td>
                                            <td style="white-space: nowrap;">
                                                <a href="?page=academy-manager-settings&tab=faqs&edit_category=<?php echo esc_attr(urlencode($cat_data->category)); ?>" 
                                                   class="button button-small" style="margin-right: 5px;">
                                                    <?php _e('Rename', 'academy-lesson-manager'); ?>
                                                </a>
                                                <?php if ($cat_data->count > 0): ?>
                                                    <button type="button" class="button button-small" 
                                                            onclick="alert('<?php esc_attr_e('Cannot delete category with FAQs. Please move or delete all FAQs in this category first, or use the Rename function to change the category name.', 'academy-lesson-manager'); ?>');" 
                                                            style="color: #999;" disabled>
                                                        <?php _e('Delete', 'academy-lesson-manager'); ?>
                                                    </button>
                                                <?php else: ?>
                                                    <form method="post" action="" style="display: inline-block;" 
                                                          onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to delete this category?', 'academy-lesson-manager'); ?>');">
                                                        <?php wp_nonce_field('alm_faq_category_action', 'alm_faq_category_nonce'); ?>
                                                        <input type="hidden" name="category_name" value="<?php echo esc_attr($cat_data->category); ?>" />
                                                        <input type="hidden" name="delete_category" value="1" />
                                                        <button type="submit" class="button button-small" 
                                                                style="color: #a00;">
                                                            <?php _e('Delete', 'academy-lesson-manager'); ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <!-- Bulk Rename Form -->
                            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                                <h4><?php _e('Bulk Rename Category', 'academy-lesson-manager'); ?></h4>
                                <p class="description">
                                    <?php _e('Rename a category and update all FAQs that use it. This is useful for fixing typos or standardizing category names.', 'academy-lesson-manager'); ?>
                                </p>
                                <form method="post" action="" style="margin-top: 10px;">
                                    <?php wp_nonce_field('alm_faq_category_action', 'alm_faq_category_nonce'); ?>
                                    <table class="form-table">
                                        <tbody>
                                            <tr>
                                                <th scope="row">
                                                    <label for="rename_old_category"><?php _e('Old Category', 'academy-lesson-manager'); ?></label>
                                                </th>
                                                <td>
                                                    <select id="rename_old_category" name="rename_old_category" class="regular-text" required>
                                                        <option value=""><?php _e('-- Select Category --', 'academy-lesson-manager'); ?></option>
                                                        <?php foreach ($categories as $cat): ?>
                                                            <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html(ucfirst($cat)); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">
                                                    <label for="rename_new_category"><?php _e('New Category Name', 'academy-lesson-manager'); ?></label>
                                                </th>
                                                <td>
                                                    <input type="text" id="rename_new_category" name="rename_new_category" 
                                                           class="regular-text" required 
                                                           placeholder="<?php esc_attr_e('Enter new category name', 'academy-lesson-manager'); ?>" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <p class="submit">
                                        <input type="hidden" name="rename_category" value="1" />
                                        <input type="submit" class="button button-secondary" 
                                               value="<?php esc_attr_e('Rename Category', 'academy-lesson-manager'); ?>" />
                                    </p>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
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
        $question = isset($_POST['faq_question']) ? sanitize_text_field(stripslashes($_POST['faq_question'])) : '';
        
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
        
        // Ensure category exists in categories table
        $category_exists = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->categories_table_name} WHERE category_name = %s",
            $category
        ));
        
        if ($category_exists == 0) {
            // Add category to categories table
            $this->wpdb->insert(
                $this->categories_table_name,
                array('category_name' => $category),
                array('%s')
            );
        }
        
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
    
    /**
     * Import FAQs from CSV file
     */
    private function import_faqs() {
        check_admin_referer('alm_faq_import', 'alm_faq_import_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['faq_import_file']) || $_FILES['faq_import_file']['error'] !== UPLOAD_ERR_OK) {
            $error_msg = __('No file uploaded or upload error occurred.', 'academy-lesson-manager');
            wp_redirect(add_query_arg(array('message' => 'import_error', 'error_msg' => urlencode($error_msg)), admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        $file = $_FILES['faq_import_file'];
        
        // Validate file type
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
            $error_msg = __('Invalid file type. Please upload a CSV file.', 'academy-lesson-manager');
            wp_redirect(add_query_arg(array('message' => 'import_error', 'error_msg' => urlencode($error_msg)), admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        // Open and read CSV file
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            $error_msg = __('Could not open uploaded file.', 'academy-lesson-manager');
            wp_redirect(add_query_arg(array('message' => 'import_error', 'error_msg' => urlencode($error_msg)), admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        $imported_count = 0;
        $errors = array();
        $row_number = 0;
        $header_row = null;
        $header_map = array();
        
        // Read CSV line by line
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row_number++;
            
            // Skip empty rows
            if (empty(array_filter($data))) {
                continue;
            }
            
            // First non-empty row might be header
            if ($header_row === null) {
                $header_row = $data;
                // Check if first row looks like headers (contains common column names)
                $possible_headers = array('question', 'answer', 'category', 'display_order', 'is_active');
                $is_header = false;
                foreach ($data as $cell) {
                    $cell_lower = strtolower(trim($cell));
                    if (in_array($cell_lower, $possible_headers)) {
                        $is_header = true;
                        break;
                    }
                }
                
                if ($is_header) {
                    // Map header names to indices
                    foreach ($header_row as $index => $header) {
                        $header_lower = strtolower(trim($header));
                        $header_map[$header_lower] = $index;
                    }
                    continue; // Skip header row
                } else {
                    // No header row, use positional mapping
                    $header_map = array(
                        'question' => 0,
                        'answer' => 1,
                        'category' => 2,
                        'display_order' => 3,
                        'is_active' => 4
                    );
                }
            }
            
            // Extract data based on header map - ensure we have enough columns
            $question = '';
            $answer = '';
            $category = 'membership';
            $display_order = 0;
            $is_active = 1;
            
            if (isset($header_map['question']) && isset($data[$header_map['question']])) {
                $question = trim($data[$header_map['question']]);
            }
            if (isset($header_map['answer']) && isset($data[$header_map['answer']])) {
                $answer = trim($data[$header_map['answer']]);
            }
            if (isset($header_map['category']) && isset($data[$header_map['category']])) {
                $category = trim($data[$header_map['category']]);
            }
            if (isset($header_map['display_order']) && isset($data[$header_map['display_order']])) {
                $display_order = intval($data[$header_map['display_order']]);
            }
            if (isset($header_map['is_active']) && isset($data[$header_map['is_active']])) {
                $is_active = intval($data[$header_map['is_active']]);
            }
            
            // Validate required fields
            if (empty($question) || empty($answer)) {
                $errors[] = sprintf(__('Row %d: Missing required fields (question or answer).', 'academy-lesson-manager'), $row_number);
                continue;
            }
            
            // Validate category is not empty and is reasonable length (not accidentally using answer text)
            if (empty($category) || strlen($category) > 100) {
                $category = 'membership';
            }
            
            // Normalize category
            if (empty($category)) {
                $category = 'membership';
            }
            $category = strtolower(trim($category));
            $category = preg_replace('/[^a-z0-9-]/', '-', $category);
            $category = preg_replace('/-+/', '-', $category);
            $category = trim($category, '-');
            if (empty($category)) {
                $category = 'membership';
            }
            
            // Sanitize and prepare data
            $question = sanitize_text_field($question);
            $answer = wp_kses_post($answer);
            $display_order = max(0, intval($display_order));
            $is_active = ($is_active == 1) ? 1 : 0;
            
            // Ensure category exists in categories table
            $category_exists = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->categories_table_name} WHERE category_name = %s",
                $category
            ));
            
            if ($category_exists == 0) {
                // Add category to categories table
                $this->wpdb->insert(
                    $this->categories_table_name,
                    array('category_name' => $category),
                    array('%s')
                );
            }
            
            // Insert FAQ
            $insert_data = array(
                'question' => $question,
                'answer' => $answer,
                'category' => $category,
                'display_order' => $display_order,
                'is_active' => $is_active,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );
            
            $result = $this->wpdb->insert(
                $this->table_name,
                $insert_data,
                array('%s', '%s', '%s', '%d', '%d', '%s', '%s')
            );
            
            if ($result !== false) {
                $imported_count++;
            } else {
                $errors[] = sprintf(__('Row %d: Failed to import - %s', 'academy-lesson-manager'), $row_number, $this->wpdb->last_error);
            }
        }
        
        fclose($handle);
        
        // Redirect with results
        if ($imported_count > 0) {
            $redirect_url = add_query_arg(array('message' => 'faqs_imported', 'imported' => $imported_count), admin_url('admin.php?page=academy-manager-settings&tab=faqs'));
            if (!empty($errors)) {
                // Add errors as query parameter (will be shown in notice)
                $error_msg = sprintf(__('%d imported, %d errors', 'academy-lesson-manager'), $imported_count, count($errors));
                $redirect_url = add_query_arg('error_msg', urlencode($error_msg), $redirect_url);
            }
            wp_redirect($redirect_url);
        } else {
            $error_msg = !empty($errors) ? implode(' ', array_slice($errors, 0, 3)) : __('No FAQs were imported. Please check your CSV format.', 'academy-lesson-manager');
            wp_redirect(add_query_arg(array('message' => 'import_error', 'error_msg' => urlencode($error_msg)), admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
        }
        exit;
    }
    
    /**
     * Save/Add new category
     */
    private function save_category() {
        check_admin_referer('alm_faq_category_action', 'alm_faq_category_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        $category_name = isset($_POST['category_name']) ? trim(sanitize_text_field($_POST['category_name'])) : '';
        $old_category = isset($_POST['old_category']) ? trim(sanitize_text_field($_POST['old_category'])) : '';
        
        if (empty($category_name)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        // Normalize category name
        $category_name = strtolower(trim($category_name));
        $category_name = preg_replace('/[^a-z0-9-]/', '-', $category_name);
        $category_name = preg_replace('/-+/', '-', $category_name);
        $category_name = trim($category_name, '-');
        
        if (empty($category_name)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        // Check if category already exists in categories table (unless we're editing)
        if (empty($old_category)) {
            $existing = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->categories_table_name} WHERE category_name = %s LIMIT 1",
                $category_name
            ));
            
            if ($existing > 0) {
                // Category already exists, just redirect
                wp_redirect(add_query_arg('message', 'category_saved', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
                exit;
            }
            
            // Insert new category into categories table
            $result = $this->wpdb->insert(
                $this->categories_table_name,
                array('category_name' => $category_name),
                array('%s')
            );
            
            if ($result === false) {
                error_log('FAQ Category Insert Error: ' . $this->wpdb->last_error);
                wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
                exit;
            }
        } else {
            // Editing - update category name in categories table and all FAQs
            if ($old_category !== $category_name) {
                // Check if new category name already exists in categories table
                $existing = $this->wpdb->get_var($this->wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->categories_table_name} WHERE category_name = %s LIMIT 1",
                    $category_name
                ));
                
                if ($existing > 0) {
                    // New name exists in categories table, merge by updating old category to new
                    // Update all FAQs with old category to new category
                    $this->wpdb->update(
                        $this->table_name,
                        array('category' => $category_name, 'updated_at' => current_time('mysql')),
                        array('category' => $old_category),
                        array('%s', '%s'),
                        array('%s')
                    );
                    // Delete old category from categories table
                    $this->wpdb->delete(
                        $this->categories_table_name,
                        array('category_name' => $old_category),
                        array('%s')
                    );
                } else {
                    // Update category name in categories table
                    $this->wpdb->update(
                        $this->categories_table_name,
                        array('category_name' => $category_name, 'updated_at' => current_time('mysql')),
                        array('category_name' => $old_category),
                        array('%s', '%s'),
                        array('%s')
                    );
                    // Update all FAQs with old category to new category
                    $this->wpdb->update(
                        $this->table_name,
                        array('category' => $category_name, 'updated_at' => current_time('mysql')),
                        array('category' => $old_category),
                        array('%s', '%s'),
                        array('%s')
                    );
                }
            }
        }
        
        wp_redirect(add_query_arg('message', 'category_saved', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
        exit;
    }
    
    /**
     * Delete category (only if no FAQs use it)
     */
    private function delete_category() {
        check_admin_referer('alm_faq_category_action', 'alm_faq_category_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        $category_name = isset($_POST['category_name']) ? trim(sanitize_text_field($_POST['category_name'])) : '';
        
        if (empty($category_name)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        // Check if category exists in categories table
        $category_exists = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->categories_table_name} WHERE category_name = %s",
            $category_name
        ));
        
        if ($category_exists == 0) {
            // Category doesn't exist
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        // Check if category has any FAQs
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE category = %s",
            $category_name
        ));
        
        if ($count > 0) {
            // Can't delete category with FAQs
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        // Delete category from categories table
        $result = $this->wpdb->delete(
            $this->categories_table_name,
            array('category_name' => $category_name),
            array('%s')
        );
        
        if ($result === false) {
            // Log error for debugging
            error_log('FAQ Category Delete Error: ' . $this->wpdb->last_error);
            error_log('Category name attempted: ' . $category_name);
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
        } else {
            wp_redirect(add_query_arg('message', 'category_deleted', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
        }
        exit;
    }
    
    /**
     * Rename category (bulk update all FAQs)
     */
    private function rename_category() {
        check_admin_referer('alm_faq_category_action', 'alm_faq_category_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        $old_category = isset($_POST['rename_old_category']) ? trim(sanitize_text_field($_POST['rename_old_category'])) : '';
        $new_category = isset($_POST['rename_new_category']) ? trim(sanitize_text_field($_POST['rename_new_category'])) : '';
        
        if (empty($old_category) || empty($new_category)) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        // Normalize new category name
        $new_category = strtolower(trim($new_category));
        $new_category = preg_replace('/[^a-z0-9-]/', '-', $new_category);
        $new_category = preg_replace('/-+/', '-', $new_category);
        $new_category = trim($new_category, '-');
        
        if (empty($new_category) || $old_category === $new_category) {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
            exit;
        }
        
        // Check if new category name already exists in categories table
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->categories_table_name} WHERE category_name = %s LIMIT 1",
            $new_category
        ));
        
        if ($existing > 0) {
            // New name exists, merge by updating old category to new in FAQs table
            $this->wpdb->update(
                $this->table_name,
                array('category' => $new_category, 'updated_at' => current_time('mysql')),
                array('category' => $old_category),
                array('%s', '%s'),
                array('%s')
            );
            // Delete old category from categories table
            $this->wpdb->delete(
                $this->categories_table_name,
                array('category_name' => $old_category),
                array('%s')
            );
        } else {
            // Update category name in categories table
            $this->wpdb->update(
                $this->categories_table_name,
                array('category_name' => $new_category, 'updated_at' => current_time('mysql')),
                array('category_name' => $old_category),
                array('%s', '%s'),
                array('%s')
            );
            // Update all FAQs with old category to new category
            $this->wpdb->update(
                $this->table_name,
                array('category' => $new_category, 'updated_at' => current_time('mysql')),
                array('category' => $old_category),
                array('%s', '%s'),
                array('%s')
            );
        }
        
        wp_redirect(add_query_arg('message', 'category_renamed', admin_url('admin.php?page=academy-manager-settings&tab=faqs')));
        exit;
    }
}

