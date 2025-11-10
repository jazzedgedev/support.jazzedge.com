<?php
/**
 * Taxonomy Class for Jazzedge Docs
 * Handles the custom taxonomy registration
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jazzedge_Docs_Taxonomy {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_taxonomy'));
        add_action('jazzedge_doc_category_add_form_fields', array($this, 'add_category_fields'));
        add_action('jazzedge_doc_category_edit_form_fields', array($this, 'edit_category_fields'));
        add_action('created_jazzedge_doc_category', array($this, 'save_category_fields'));
        add_action('edited_jazzedge_doc_category', array($this, 'save_category_fields'));
        add_filter('manage_edit-jazzedge_doc_category_columns', array($this, 'category_columns'));
        add_filter('manage_jazzedge_doc_category_custom_column', array($this, 'category_column_content'), 10, 3);
    }
    
    /**
     * Register the custom taxonomy
     */
    public function register_taxonomy() {
        $labels = array(
            'name'              => _x('Support Docs Categories', 'taxonomy general name', 'jazzedge-docs'),
            'singular_name'     => _x('Support Docs Category', 'taxonomy singular name', 'jazzedge-docs'),
            'search_items'      => __('Search Categories', 'jazzedge-docs'),
            'all_items'         => __('All Categories', 'jazzedge-docs'),
            'parent_item'       => __('Parent Category', 'jazzedge-docs'),
            'parent_item_colon' => __('Parent Category:', 'jazzedge-docs'),
            'edit_item'         => __('Edit Category', 'jazzedge-docs'),
            'update_item'       => __('Update Category', 'jazzedge-docs'),
            'add_new_item'      => __('Add New Category', 'jazzedge-docs'),
            'new_item_name'     => __('New Category Name', 'jazzedge-docs'),
            'menu_name'         => __('Categories', 'jazzedge-docs'),
        );
        
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array('slug' => 'docs-category'),
        );
        
        register_taxonomy('jazzedge_doc_category', array('jazzedge_doc'), $args);
    }
    
    /**
     * Add category fields
     */
    public function add_category_fields() {
        ?>
        <div class="form-field">
            <label for="category_order"><?php _e('Order', 'jazzedge-docs'); ?></label>
            <input type="number" name="category_order" id="category_order" value="0" min="0">
            <p class="description"><?php _e('Lower numbers appear first.', 'jazzedge-docs'); ?></p>
        </div>
        <div class="form-field">
            <label for="category_icon"><?php _e('Icon', 'jazzedge-docs'); ?></label>
            <input type="text" name="category_icon" id="category_icon" value="" placeholder="dashicons-admin-generic">
            <p class="description"><?php _e('WordPress dashicon class name (e.g., dashicons-admin-generic)', 'jazzedge-docs'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Edit category fields
     */
    public function edit_category_fields($term) {
        $order = get_term_meta($term->term_id, 'category_order', true);
        $icon = get_term_meta($term->term_id, 'category_icon', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="category_order"><?php _e('Order', 'jazzedge-docs'); ?></label>
            </th>
            <td>
                <input type="number" name="category_order" id="category_order" value="<?php echo esc_attr($order); ?>" min="0">
                <p class="description"><?php _e('Lower numbers appear first.', 'jazzedge-docs'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="category_icon"><?php _e('Icon', 'jazzedge-docs'); ?></label>
            </th>
            <td>
                <input type="text" name="category_icon" id="category_icon" value="<?php echo esc_attr($icon); ?>" placeholder="dashicons-admin-generic">
                <p class="description"><?php _e('WordPress dashicon class name (e.g., dashicons-admin-generic)', 'jazzedge-docs'); ?></p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save category fields
     */
    public function save_category_fields($term_id) {
        if (isset($_POST['category_order'])) {
            update_term_meta($term_id, 'category_order', intval($_POST['category_order']));
        }
        
        if (isset($_POST['category_icon'])) {
            update_term_meta($term_id, 'category_icon', sanitize_text_field($_POST['category_icon']));
        }
    }
    
    /**
     * Add custom columns
     */
    public function category_columns($columns) {
        $columns['order'] = __('Order', 'jazzedge-docs');
        $columns['icon'] = __('Icon', 'jazzedge-docs');
        return $columns;
    }
    
    /**
     * Category column content
     */
    public function category_column_content($content, $column_name, $term_id) {
        if ($column_name === 'order') {
            $order = get_term_meta($term_id, 'category_order', true);
            $content = $order ? $order : '0';
        } elseif ($column_name === 'icon') {
            $icon = get_term_meta($term_id, 'category_icon', true);
            if ($icon) {
                $content = '<span class="dashicons ' . esc_attr($icon) . '"></span>';
            } else {
                $content = '—';
            }
        }
        
        return $content;
    }
}

