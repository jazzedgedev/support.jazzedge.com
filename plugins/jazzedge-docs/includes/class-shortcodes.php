<?php
/**
 * Shortcodes Class for Jazzedge Docs
 * Handles all shortcode functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jazzedge_Docs_Shortcodes {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('jazzedge_docs_search', array($this, 'search_shortcode'));
        add_shortcode('jazzedge_docs_list', array($this, 'list_shortcode'));
        add_shortcode('jazzedge_docs_categories', array($this, 'categories_shortcode'));
        add_shortcode('jazzedge_doc_single', array($this, 'single_shortcode'));
        add_shortcode('jazzedge_doc_render', array($this, 'render_doc_shortcode'));
        add_shortcode('jazzedge_doc_category_render', array($this, 'render_category_shortcode'));
    }
    
    /**
     * Search shortcode
     */
    public function search_shortcode($atts) {
        $atts = shortcode_atts(array(
            'placeholder' => get_option('jazzedge_docs_search_placeholder', 'Search support documentation...'),
            'show_results' => 'yes',
            'results_count' => 10
        ), $atts);
        
        ob_start();
        ?>
        <div class="jazzedge-docs-search-wrapper">
            <div class="jazzedge-docs-search-bar-container">
                <div class="jazzedge-docs-search-bar">
                    <input type="search" 
                           id="jazzedge-docs-search-input" 
                           class="jazzedge-docs-search-input" 
                           placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                           autocomplete="off">
                    <span class="jazzedge-docs-search-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
                <a href="https://support.jazzedge.com/support?site=academy" class="jazzedge-docs-submit-ticket-btn" target="_blank">
                    Submit Support Ticket
                </a>
            </div>
            <?php if ($atts['show_results'] === 'yes'): ?>
                <div id="jazzedge-docs-search-results" class="jazzedge-docs-search-results" style="display: none;"></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * List shortcode
     */
    public function list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'category_id' => '',
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'featured' => '',
            'layout' => 'grid',
            'columns' => 3
        ), $atts);
        
        $args = array(
            'post_type' => 'jazzedge_doc',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => sanitize_text_field($atts['orderby']),
            'order' => sanitize_text_field($atts['order']),
            'post_status' => 'publish'
        );
        
        // Category filter
        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'jazzedge_doc_category',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($atts['category'])
                )
            );
        } elseif (!empty($atts['category_id'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'jazzedge_doc_category',
                    'field' => 'term_id',
                    'terms' => intval($atts['category_id'])
                )
            );
        }
        
        // Featured filter
        if ($atts['featured'] === 'yes') {
            $args['meta_query'] = array(
                array(
                    'key' => '_jazzedge_doc_featured',
                    'value' => 'yes',
                    'compare' => '='
                )
            );
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        ?>
        <div class="jazzedge-docs-list-wrapper jazzedge-docs-layout-<?php echo esc_attr($atts['layout']); ?>">
            <?php if ($query->have_posts()): ?>
                <div class="jazzedge-docs-grid" style="grid-template-columns: repeat(<?php echo esc_attr($atts['columns']); ?>, 1fr);">
                    <?php while ($query->have_posts()): $query->the_post(); ?>
                        <?php $this->render_doc_card(get_the_ID()); ?>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p><?php _e('No support docs found.', 'jazzedge-docs'); ?></p>
            <?php endif; ?>
        </div>
        <?php
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Categories shortcode
     */
    public function categories_shortcode($atts) {
        $atts = shortcode_atts(array(
            'parent' => '',
            'layout' => 'grid',
            'columns' => 3
        ), $atts);
        
        // Ensure taxonomy is registered
        if (!taxonomy_exists('jazzedge_doc_category')) {
            return '<p style="color: red;">Taxonomy not registered. Please refresh the page.</p>';
        }
        
        $args = array(
            'taxonomy' => 'jazzedge_doc_category',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
            'number' => 0 // Get all categories
        );
        
        if (!empty($atts['parent'])) {
            $args['parent'] = intval($atts['parent']);
        } else {
            $args['parent'] = 0;
        }
        
        $categories = get_terms($args);
        
        // If no categories found, try without orderby to see if that's the issue
        if (empty($categories) || is_wp_error($categories)) {
            $simple_args = array(
                'taxonomy' => 'jazzedge_doc_category',
                'hide_empty' => false,
                'parent' => empty($atts['parent']) ? 0 : intval($atts['parent']),
                'number' => 0
            );
            $categories = get_terms($simple_args);
        }
        
        // Filter out any invalid categories (make sure term exists)
        if (!empty($categories) && !is_wp_error($categories)) {
            $categories = array_filter($categories, function($cat) {
                return $cat && isset($cat->term_id) && $cat->term_id > 0;
            });
        }
        
        // Sort by order meta if available, otherwise by name
        if (!empty($categories) && !is_wp_error($categories)) {
            usort($categories, function($a, $b) {
                $order_a = get_term_meta($a->term_id, 'category_order', true);
                $order_b = get_term_meta($b->term_id, 'category_order', true);
                
                // If both have order, sort by order
                if ($order_a !== '' && $order_b !== '') {
                    return intval($order_a) - intval($order_b);
                }
                // If only one has order, prioritize it
                if ($order_a !== '' && $order_b === '') {
                    return -1;
                }
                if ($order_a === '' && $order_b !== '') {
                    return 1;
                }
                // Otherwise sort by name
                return strcmp($a->name, $b->name);
            });
        }
        
        ob_start();
        ?>
        <div class="jazzedge-docs-categories-wrapper jazzedge-docs-layout-<?php echo esc_attr($atts['layout']); ?>">
            <?php if (!empty($categories) && !is_wp_error($categories)): ?>
                <div class="jazzedge-docs-categories-grid" style="grid-template-columns: repeat(<?php echo esc_attr($atts['columns']); ?>, 1fr);">
                    <?php foreach ($categories as $category): ?>
                        <?php $this->render_category_card($category); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p><?php _e('No categories found.', 'jazzedge-docs'); ?></p>
                <?php if (is_wp_error($categories)): ?>
                    <p style="color: red;"><?php echo esc_html($categories->get_error_message()); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Single doc shortcode
     */
    public function single_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'slug' => ''
        ), $atts);
        
        if (empty($atts['id']) && empty($atts['slug'])) {
            return '<p>' . __('Please provide either id or slug parameter.', 'jazzedge-docs') . '</p>';
        }
        
        $args = array(
            'post_type' => 'jazzedge_doc',
            'post_status' => 'publish',
            'posts_per_page' => 1
        );
        
        if (!empty($atts['id'])) {
            $args['p'] = intval($atts['id']);
        } else {
            $args['name'] = sanitize_text_field($atts['slug']);
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        if ($query->have_posts()) {
            $query->the_post();
            $this->render_single_doc();
            wp_reset_postdata();
        } else {
            echo '<p>' . __('Support doc not found.', 'jazzedge-docs') . '</p>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Render doc card
     */
    private function render_doc_card($post_id) {
        $post = get_post($post_id);
        $permalink = get_permalink($post_id);
        $excerpt = get_the_excerpt($post_id);
        $categories = get_the_terms($post_id, 'jazzedge_doc_category');
        $db = new Jazzedge_Docs_Database();
        $view_count = $db->get_view_count($post_id);
        ?>
        <div class="jazzedge-doc-card">
            <a href="<?php echo esc_url($permalink); ?>" class="jazzedge-doc-card-link">
                <?php if (has_post_thumbnail($post_id)): ?>
                    <div class="jazzedge-doc-card-image">
                        <?php echo get_the_post_thumbnail($post_id, 'medium'); ?>
                    </div>
                <?php endif; ?>
                <div class="jazzedge-doc-card-content">
                    <h3 class="jazzedge-doc-card-title"><?php echo esc_html($post->post_title); ?></h3>
                    <?php if (!empty($excerpt)): ?>
                        <p class="jazzedge-doc-card-excerpt"><?php echo esc_html(wp_trim_words($excerpt, 20)); ?></p>
                    <?php endif; ?>
                    <div class="jazzedge-doc-card-meta">
                        <?php if ($categories && !is_wp_error($categories)): ?>
                            <span class="jazzedge-doc-card-category">
                                <?php echo esc_html($categories[0]->name); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($view_count > 0): ?>
                            <span class="jazzedge-doc-card-views">
                                <?php echo esc_html($view_count); ?> <?php _e('views', 'jazzedge-docs'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
        <?php
    }
    
    /**
     * Render category card
     */
    private function render_category_card($category) {
        $term_link = get_term_link($category);
        
        // Get actual count of published docs in this category
        $args = array(
            'post_type' => 'jazzedge_doc',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => 'jazzedge_doc_category',
                    'field' => 'term_id',
                    'terms' => $category->term_id
                )
            )
        );
        $query = new WP_Query($args);
        $actual_count = $query->found_posts;
        wp_reset_postdata();
        
        // Get up to 5 doc titles for display
        $docs_args = array(
            'post_type' => 'jazzedge_doc',
            'post_status' => 'publish',
            'posts_per_page' => 5,
            'tax_query' => array(
                array(
                    'taxonomy' => 'jazzedge_doc_category',
                    'field' => 'term_id',
                    'terms' => $category->term_id
                )
            ),
            'orderby' => 'date',
            'order' => 'DESC'
        );
        $docs_query = new WP_Query($docs_args);
        ?>
        <div class="jazzedge-doc-category-card">
            <div class="jazzedge-doc-category-header">
                <?php echo esc_html(strtoupper($category->name)); ?>
            </div>
            <div class="jazzedge-doc-category-body">
                <?php if ($docs_query->have_posts()): ?>
                    <ul class="jazzedge-doc-category-list">
                        <?php while ($docs_query->have_posts()): $docs_query->the_post(); ?>
                            <li>
                                <a href="<?php echo esc_url(get_permalink()); ?>" class="jazzedge-doc-category-link">
                                    <?php echo esc_html(get_the_title()); ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                    <?php if ($actual_count > 5): ?>
                        <div class="jazzedge-doc-category-more">
                            <?php printf(__('+%d more', 'jazzedge-docs'), $actual_count - 5); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
            </div>
            <div class="jazzedge-doc-category-footer">
                <a href="<?php echo esc_url($term_link); ?>" class="jazzedge-doc-category-footer-link">
                    <?php echo esc_html($actual_count); ?> <?php echo $actual_count === 1 ? __('SUPPORT DOC', 'jazzedge-docs') : __('SUPPORT DOCS', 'jazzedge-docs'); ?>
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render single doc
     */
    private function render_single_doc() {
        $post_id = get_the_ID();
        $db = new Jazzedge_Docs_Database();
        $helpers = new Jazzedge_Docs_Helpers();
        
        // Breadcrumbs
        $helpers->render_breadcrumbs($post_id);
        
        // Title
        echo '<h1 class="jazzedge-doc-title">' . get_the_title() . '</h1>';
        
        // Meta
        echo '<div class="jazzedge-doc-meta">';
        $categories = get_the_terms($post_id, 'jazzedge_doc_category');
        if ($categories && !is_wp_error($categories)) {
            echo '<span class="jazzedge-doc-categories">';
            foreach ($categories as $category) {
                echo '<a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a>';
            }
            echo '</span>';
        }
        echo '<span class="jazzedge-doc-date">' . get_the_date() . '</span>';
        echo '</div>';
        
        // Content
        echo '<div class="jazzedge-doc-content">';
        the_content();
        echo '</div>';
        
        // Ratings
        $helpers->render_ratings($post_id);
        
        // Related docs
        $related_doc_ids = $db->get_related_docs($post_id, 5);
        if (!empty($related_doc_ids)) {
            $helpers->render_related_docs($related_doc_ids);
        }
        
        // Print button
        echo '<div class="jazzedge-doc-print">';
        echo '<button onclick="window.print()" class="jazzedge-doc-print-btn">' . __('Print', 'jazzedge-docs') . '</button>';
        echo '</div>';
    }
    
    /**
     * Render doc shortcode - Enhanced version for Oxygen Builder
     */
    public function render_doc_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'show_breadcrumbs' => 'yes',
            'show_ratings' => 'yes',
            'show_related' => 'yes',
            'show_print' => 'yes'
        ), $atts);
        
        // Get current post if no ID specified
        global $post;
        $post_id = !empty($atts['id']) ? intval($atts['id']) : ($post && $post->post_type === 'jazzedge_doc' ? $post->ID : 0);
        
        if (!$post_id) {
            return '<p>' . __('Support doc not found.', 'jazzedge-docs') . '</p>';
        }
        
        $doc_post = get_post($post_id);
        if (!$doc_post || $doc_post->post_type !== 'jazzedge_doc') {
            return '<p>' . __('Support doc not found.', 'jazzedge-docs') . '</p>';
        }
        
        $db = new Jazzedge_Docs_Database();
        $helpers = new Jazzedge_Docs_Helpers();
        
        ob_start();
        ?>
        <div class="jazzedge-doc-enhanced-wrapper">
            <?php if ($atts['show_breadcrumbs'] === 'yes'): ?>
                <?php $helpers->render_breadcrumbs($post_id); ?>
            <?php endif; ?>
            
            <div class="jazzedge-doc-enhanced-header">
                <h1 class="jazzedge-doc-enhanced-title"><?php echo esc_html($doc_post->post_title); ?></h1>
                
                <div class="jazzedge-doc-enhanced-meta">
                    <?php
                    $categories = get_the_terms($post_id, 'jazzedge_doc_category');
                    if ($categories && !is_wp_error($categories)):
                        ?>
                        <div class="jazzedge-doc-enhanced-categories">
                            <?php foreach ($categories as $category): ?>
                                <a href="<?php echo esc_url(get_term_link($category)); ?>" class="jazzedge-doc-category-tag">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="jazzedge-doc-enhanced-meta-info">
                        <span class="jazzedge-doc-date-badge">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <?php echo get_the_date('F j, Y', $post_id); ?>
                        </span>
                        <?php
                        $view_count = $db->get_view_count($post_id);
                        if ($view_count > 0):
                            ?>
                            <span class="jazzedge-doc-views-badge">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <?php echo esc_html($view_count); ?> <?php echo $view_count === 1 ? __('view', 'jazzedge-docs') : __('views', 'jazzedge-docs'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="jazzedge-doc-enhanced-content">
                <?php
                $content = get_the_content(null, false, $post_id);
                $content = apply_filters('the_content', $content);
                echo $content;
                ?>
            </div>
            
            <?php if ($atts['show_ratings'] === 'yes'): ?>
                <?php $helpers->render_ratings($post_id); ?>
            <?php endif; ?>
            
            <?php
            // Related docs
            if ($atts['show_related'] === 'yes'):
                $related_doc_ids = $db->get_related_docs($post_id, 5);
                if (!empty($related_doc_ids)):
                    $helpers->render_related_docs($related_doc_ids);
                endif;
            endif;
            ?>
            
            <?php if ($atts['show_print'] === 'yes'): ?>
                <div class="jazzedge-doc-enhanced-actions">
                    <button onclick="window.print()" class="jazzedge-doc-print-btn-enhanced">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        <?php _e('Print Article', 'jazzedge-docs'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render category shortcode - Enhanced version for Oxygen Builder
     */
    public function render_category_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'slug' => '',
            'layout' => 'grid',
            'columns' => 3,
            'show_description' => 'yes',
            'show_icon' => 'yes',
            'show_back_link' => 'yes'
        ), $atts);
        
        // Get current category if no ID/slug specified
        $category = null;
        if (!empty($atts['id'])) {
            $category = get_term(intval($atts['id']), 'jazzedge_doc_category');
        } elseif (!empty($atts['slug'])) {
            $category = get_term_by('slug', sanitize_text_field($atts['slug']), 'jazzedge_doc_category');
        } else {
            // Try to get from current query
            $queried_object = get_queried_object();
            if ($queried_object && isset($queried_object->taxonomy) && $queried_object->taxonomy === 'jazzedge_doc_category') {
                $category = $queried_object;
            }
        }
        
        if (!$category || is_wp_error($category)) {
            return '<p>' . __('Category not found.', 'jazzedge-docs') . '</p>';
        }
        
        // Get docs in this category
        $args = array(
            'post_type' => 'jazzedge_doc',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'jazzedge_doc_category',
                    'field' => 'term_id',
                    'terms' => $category->term_id
                )
            ),
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $query = new WP_Query($args);
        $db = new Jazzedge_Docs_Database();
        $actual_count = $query->found_posts;
        
        ob_start();
        ?>
        <div class="jazzedge-doc-category-enhanced-wrapper">
            <?php if ($atts['show_back_link'] === 'yes'): ?>
                <div class="jazzedge-doc-category-back-link">
                    <a href="<?php echo esc_url(get_post_type_archive_link('jazzedge_doc')); ?>" class="jazzedge-doc-back-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        <?php _e('Back To All Support Docs', 'jazzedge-docs'); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="jazzedge-doc-category-enhanced-header">
                <?php if ($atts['show_icon'] === 'yes'): ?>
                    <?php $icon = get_term_meta($category->term_id, 'category_icon', true); ?>
                    <?php if ($icon): ?>
                        <div class="jazzedge-doc-category-enhanced-icon">
                            <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <h1 class="jazzedge-doc-category-enhanced-title"><?php echo esc_html($category->name); ?></h1>
                
                <?php if ($atts['show_description'] === 'yes' && !empty($category->description)): ?>
                    <div class="jazzedge-doc-category-enhanced-description">
                        <?php echo wp_kses_post(wpautop($category->description)); ?>
                    </div>
                <?php endif; ?>
                
                <div class="jazzedge-doc-category-enhanced-meta">
                    <span class="jazzedge-doc-category-count-badge">
                        <?php echo esc_html($actual_count); ?> <?php echo $actual_count === 1 ? __('article', 'jazzedge-docs') : __('articles', 'jazzedge-docs'); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($query->have_posts()): ?>
                <div class="jazzedge-doc-category-enhanced-docs">
                    <div class="jazzedge-docs-grid jazzedge-docs-grid-enhanced" style="grid-template-columns: repeat(<?php echo esc_attr($atts['columns']); ?>, 1fr);">
                        <?php while ($query->have_posts()): $query->the_post(); ?>
                            <?php $this->render_enhanced_doc_card(get_the_ID()); ?>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="jazzedge-doc-category-empty">
                    <p><?php _e('No articles found in this category.', 'jazzedge-docs'); ?></p>
                </div>
            <?php endif; ?>
            
            <?php wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render enhanced doc card for category pages
     */
    private function render_enhanced_doc_card($post_id) {
        $post = get_post($post_id);
        $permalink = get_permalink($post_id);
        $excerpt = get_the_excerpt($post_id);
        $categories = get_the_terms($post_id, 'jazzedge_doc_category');
        $db = new Jazzedge_Docs_Database();
        $view_count = $db->get_view_count($post_id);
        ?>
        <div class="jazzedge-doc-card-enhanced">
            <a href="<?php echo esc_url($permalink); ?>" class="jazzedge-doc-card-link-enhanced">
                <?php if (has_post_thumbnail($post_id)): ?>
                    <div class="jazzedge-doc-card-image-enhanced">
                        <?php echo get_the_post_thumbnail($post_id, 'medium'); ?>
                    </div>
                <?php endif; ?>
                <div class="jazzedge-doc-card-content-enhanced">
                    <h3 class="jazzedge-doc-card-title-enhanced"><?php echo esc_html($post->post_title); ?></h3>
                    <?php if (!empty($excerpt)): ?>
                        <p class="jazzedge-doc-card-excerpt-enhanced"><?php echo esc_html(wp_trim_words($excerpt, 25)); ?></p>
                    <?php endif; ?>
                    <div class="jazzedge-doc-card-meta-enhanced">
                        <?php if ($categories && !is_wp_error($categories)): ?>
                            <span class="jazzedge-doc-card-category-enhanced">
                                <?php echo esc_html($categories[0]->name); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($view_count > 0): ?>
                            <span class="jazzedge-doc-card-views-enhanced">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <?php echo esc_html($view_count); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
        <?php
    }
}

