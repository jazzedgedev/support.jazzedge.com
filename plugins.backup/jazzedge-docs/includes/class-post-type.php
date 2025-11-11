<?php
/**
 * Post Type Class for Jazzedge Docs
 * Handles the custom post type registration
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jazzedge_Docs_Post_Type {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_filter('post_updated_messages', array($this, 'updated_messages'));
        add_action('admin_init', array($this, 'prevent_redirect_loop'));
    }
    
    /**
     * Register the custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Support Docs', 'Post Type General Name', 'jazzedge-docs'),
            'singular_name'         => _x('Support Doc', 'Post Type Singular Name', 'jazzedge-docs'),
            'menu_name'             => __('Support Docs', 'jazzedge-docs'),
            'name_admin_bar'        => __('Support Doc', 'jazzedge-docs'),
            'archives'              => __('Support Doc Archives', 'jazzedge-docs'),
            'attributes'            => __('Support Doc Attributes', 'jazzedge-docs'),
            'parent_item_colon'     => __('Parent Support Doc:', 'jazzedge-docs'),
            'all_items'             => __('All Support Docs', 'jazzedge-docs'),
            'add_new_item'          => __('Add New Support Doc', 'jazzedge-docs'),
            'add_new'               => __('Add New', 'jazzedge-docs'),
            'new_item'              => __('New Support Doc', 'jazzedge-docs'),
            'edit_item'             => __('Edit Support Doc', 'jazzedge-docs'),
            'update_item'           => __('Update Support Doc', 'jazzedge-docs'),
            'view_item'             => __('View Support Doc', 'jazzedge-docs'),
            'view_items'            => __('View Support Docs', 'jazzedge-docs'),
            'search_items'          => __('Search Support Doc', 'jazzedge-docs'),
            'not_found'             => __('Not found', 'jazzedge-docs'),
            'not_found_in_trash'    => __('Not found in Trash', 'jazzedge-docs'),
            'featured_image'        => __('Featured Image', 'jazzedge-docs'),
            'set_featured_image'    => __('Set featured image', 'jazzedge-docs'),
            'remove_featured_image' => __('Remove featured image', 'jazzedge-docs'),
            'use_featured_image'    => __('Use as featured image', 'jazzedge-docs'),
            'insert_into_item'     => __('Insert into support doc', 'jazzedge-docs'),
            'uploaded_to_this_item' => __('Uploaded to this support doc', 'jazzedge-docs'),
            'items_list'            => __('Support Docs list', 'jazzedge-docs'),
            'items_list_navigation' => __('Support Docs list navigation', 'jazzedge-docs'),
            'filter_items_list'     => __('Filter support docs list', 'jazzedge-docs'),
        );
        
        $args = array(
            'label'                 => __('Support Doc', 'jazzedge-docs'),
            'description'           => __('Jazzedge Support Documentation Articles', 'jazzedge-docs'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes'),
            'taxonomies'            => array('jazzedge_doc_category'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 20,
            'menu_icon'             => 'dashicons-book-alt',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'capabilities'          => array(
                'edit_post'          => 'edit_post',
                'read_post'          => 'read_post',
                'delete_post'        => 'delete_post',
                'edit_posts'         => 'edit_posts',
                'edit_others_posts'  => 'edit_others_posts',
                'publish_posts'       => 'publish_posts',
                'read_private_posts' => 'read_private_posts',
                'delete_posts'       => 'delete_posts',
                'delete_private_posts' => 'delete_private_posts',
                'delete_published_posts' => 'delete_published_posts',
                'delete_others_posts' => 'delete_others_posts',
                'edit_private_posts' => 'edit_private_posts',
                'edit_published_posts' => 'edit_published_posts',
            ),
            'map_meta_cap'          => true,
            'show_in_rest'          => true,
            'rewrite'               => array(
                'slug' => 'docs',
                'with_front' => false,
                'feeds' => true,
                'pages' => true
            ),
        );
        
        register_post_type('jazzedge_doc', $args);
        
        // Add custom rewrite rule as backup
        add_rewrite_rule(
            '^docs/([^/]+)/?$',
            'index.php?post_type=jazzedge_doc&name=$matches[1]',
            'top'
        );
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'jazzedge_doc_settings',
            __('Support Doc Settings', 'jazzedge-docs'),
            array($this, 'render_settings_meta_box'),
            'jazzedge_doc',
            'side',
            'default'
        );
        
        add_meta_box(
            'jazzedge_doc_related',
            __('Related Support Docs', 'jazzedge-docs'),
            array($this, 'render_related_meta_box'),
            'jazzedge_doc',
            'normal',
            'default'
        );
        
        add_meta_box(
            'jazzedge_doc_ratings',
            __('Ratings & Feedback', 'jazzedge-docs'),
            array($this, 'render_ratings_meta_box'),
            'jazzedge_doc',
            'normal',
            'default'
        );
    }
    
    /**
     * Render settings meta box
     */
    public function render_settings_meta_box($post) {
        wp_nonce_field('jazzedge_doc_settings', 'jazzedge_doc_settings_nonce');
        
        
        $featured = get_post_meta($post->ID, '_jazzedge_doc_featured', true);
        ?>
        <p>
            <label>
                <input type="checkbox" name="jazzedge_doc_featured" value="yes" <?php checked($featured, 'yes'); ?>>
                <?php _e('Featured Doc', 'jazzedge-docs'); ?>
            </label>
        </p>
        <?php
    }
    
    /**
     * Render related docs meta box
     */
    public function render_related_meta_box($post) {
        wp_nonce_field('jazzedge_doc_related', 'jazzedge_doc_related_nonce');
        
        $db = new Jazzedge_Docs_Database();
        $related_doc_ids = $db->get_related_docs($post->ID, 100);
        
        // Get all docs
        $all_docs = get_posts(array(
            'post_type' => 'jazzedge_doc',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'exclude' => array($post->ID)
        ));
        ?>
        <div id="jazzedge-doc-related-wrapper">
            <select id="jazzedge-doc-related-select" style="width: 100%;">
                <option value=""><?php _e('Select a support doc to add...', 'jazzedge-docs'); ?></option>
                <?php foreach ($all_docs as $doc): ?>
                    <option value="<?php echo esc_attr($doc->ID); ?>"><?php echo esc_html($doc->post_title); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="jazzedge-doc-add-related" class="button" style="margin-top: 10px;">
                <?php _e('Add Related Support Doc', 'jazzedge-docs'); ?>
            </button>
            
            <ul id="jazzedge-doc-related-list" style="margin-top: 15px;">
                <?php foreach ($related_doc_ids as $related_id): ?>
                    <?php $related_post = get_post($related_id); ?>
                    <?php if ($related_post): ?>
                        <li data-doc-id="<?php echo esc_attr($related_id); ?>" style="padding: 8px; background: #f9f9f9; margin-bottom: 5px; display: flex; justify-content: space-between; align-items: center;">
                            <span><?php echo esc_html($related_post->post_title); ?></span>
                            <button type="button" class="jazzedge-doc-remove-related button-link" data-doc-id="<?php echo esc_attr($related_id); ?>">
                                <?php _e('Remove', 'jazzedge-docs'); ?>
                            </button>
                            <input type="hidden" name="jazzedge_doc_related[]" value="<?php echo esc_attr($related_id); ?>">
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
    
    /**
     * Render ratings meta box
     */
    public function render_ratings_meta_box($post) {
        $db = new Jazzedge_Docs_Database();
        $ratings = $db->get_doc_ratings($post->ID);
        $avg_rating = $db->get_average_rating($post->ID);
        $rating_count = $db->get_rating_count($post->ID);
        ?>
        <div id="jazzedge-doc-ratings-wrapper">
            <?php if ($rating_count > 0): ?>
                <p>
                    <strong><?php _e('Average Rating:', 'jazzedge-docs'); ?></strong>
                    <?php echo esc_html(number_format($avg_rating, 1)); ?> / 5.0
                    <span style="color: #ffb900; font-size: 16px; margin-left: 5px;">
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $avg_rating) {
                                echo '★';
                            } elseif ($i - $avg_rating < 0.5 && $i - $avg_rating > 0) {
                                echo '☆';
                            } else {
                                echo '<span style="opacity: 0.3;">☆</span>';
                            }
                        }
                        ?>
                    </span>
                    (<?php echo esc_html($rating_count); ?> <?php echo $rating_count === 1 ? __('rating', 'jazzedge-docs') : __('ratings', 'jazzedge-docs'); ?>)
                </p>
                
                <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 100px;"><?php _e('Rating', 'jazzedge-docs'); ?></th>
                            <th style="width: 150px;"><?php _e('User', 'jazzedge-docs'); ?></th>
                            <th><?php _e('Feedback', 'jazzedge-docs'); ?></th>
                            <th style="width: 150px;"><?php _e('Date', 'jazzedge-docs'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ratings as $rating): ?>
                            <tr>
                                <td>
                                    <span style="color: #ffb900;">
                                        <?php 
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $rating->rating ? '★' : '☆';
                                        }
                                        ?>
                                    </span>
                                    (<?php echo esc_html($rating->rating); ?>/5)
                                </td>
                                <td>
                                    <?php 
                                    if ($rating->user_id && $rating->display_name) {
                                        echo esc_html($rating->display_name);
                                        if ($rating->user_email) {
                                            echo '<br><small style="color: #666;">' . esc_html($rating->user_email) . '</small>';
                                        }
                                    } else {
                                        echo __('Guest', 'jazzedge-docs');
                                        if ($rating->user_ip) {
                                            echo '<br><small style="color: #666;">' . esc_html($rating->user_ip) . '</small>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?php echo $rating->feedback ? esc_html($rating->feedback) : '<em style="color: #999;">' . __('No feedback provided', 'jazzedge-docs') . '</em>'; ?></td>
                                <td><small><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($rating->created_at))); ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No ratings yet. Ratings will appear here once users rate this support doc.', 'jazzedge-docs'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id) {
        // Check nonce
        if (!isset($_POST['jazzedge_doc_settings_nonce']) || 
            !wp_verify_nonce($_POST['jazzedge_doc_settings_nonce'], 'jazzedge_doc_settings')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save settings
        
        $featured = isset($_POST['jazzedge_doc_featured']) ? 'yes' : 'no';
        update_post_meta($post_id, '_jazzedge_doc_featured', $featured);
        
        // Save related docs
        if (isset($_POST['jazzedge_doc_related']) && is_array($_POST['jazzedge_doc_related'])) {
            $db = new Jazzedge_Docs_Database();
            $existing_related = $db->get_related_docs($post_id, 100);
            
            $new_related = array_map('intval', $_POST['jazzedge_doc_related']);
            
            // Remove docs that are no longer related
            foreach ($existing_related as $related_id) {
                if (!in_array($related_id, $new_related)) {
                    $db->remove_related_doc($post_id, $related_id);
                }
            }
            
            // Add new related docs
            foreach ($new_related as $related_id) {
                if (!in_array($related_id, $existing_related)) {
                    $db->add_related_doc($post_id, $related_id);
                }
            }
        }
    }
    
    /**
     * Customize updated messages
     */
    public function updated_messages($messages) {
        $post = get_post();
        
        $messages['jazzedge_doc'] = array(
            0  => '',
            1  => __('Support Doc updated.', 'jazzedge-docs'),
            2  => __('Custom field updated.', 'jazzedge-docs'),
            3  => __('Custom field deleted.', 'jazzedge-docs'),
            4  => __('Support Doc updated.', 'jazzedge-docs'),
            5  => isset($_GET['revision']) ? sprintf(__('Support Doc restored to revision from %s', 'jazzedge-docs'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6  => __('Support Doc published.', 'jazzedge-docs'),
            7  => __('Support Doc saved.', 'jazzedge-docs'),
            8  => __('Support Doc submitted.', 'jazzedge-docs'),
            9  => sprintf(__('Support Doc scheduled for: <strong>%1$s</strong>.', 'jazzedge-docs'), date_i18n(__('M j, Y @ G:i', 'jazzedge-docs'), strtotime($post->post_date))),
            10 => __('Support Doc draft updated.', 'jazzedge-docs'),
        );
        
        return $messages;
    }
    
    /**
     * Prevent redirect loop for edit links
     */
    public function prevent_redirect_loop() {
        // Only run on admin edit pages
        if (!is_admin()) {
            return;
        }
        
        // Check if we're trying to edit a jazzedge_doc
        if (isset($_GET['post']) && isset($_GET['action']) && $_GET['action'] === 'edit') {
            $post_id = intval($_GET['post']);
            $post = get_post($post_id);
            
            if ($post && $post->post_type === 'jazzedge_doc') {
                // Ensure user can edit this post
                if (!current_user_can('edit_post', $post_id)) {
                    wp_die(__('You do not have permission to edit this support doc.', 'jazzedge-docs'));
                }
            }
        }
    }
}

