<?php
/**
 * Helpers Class for Jazzedge Docs
 * Utility functions and template helpers
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jazzedge_Docs_Helpers {
    
    /**
     * Render breadcrumbs
     */
    public function render_breadcrumbs($post_id) {
        $post = get_post($post_id);
        $categories = get_the_terms($post_id, 'jazzedge_doc_category');
        
        ?>
        <nav class="jazzedge-docs-breadcrumbs">
            <a href="<?php echo esc_url(get_post_type_archive_link('jazzedge_doc')); ?>">
                <?php _e('Support Docs', 'jazzedge-docs'); ?>
            </a>
            <?php if ($categories && !is_wp_error($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <span class="separator">/</span>
                    <a href="<?php echo esc_url(get_term_link($category)); ?>">
                        <?php echo esc_html($category->name); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            <span class="separator">/</span>
            <span class="current"><?php echo esc_html($post->post_title); ?></span>
        </nav>
        <?php
    }
    
    /**
     * Render table of contents
     */
    public function render_table_of_contents($content) {
        // Extract headings from content - use same method as add_heading_ids
        preg_match_all('/<h([2-6])[^>]*>(.*?)<\/h[2-6]>/i', $content, $matches);
        
        if (empty($matches[0])) {
            return;
        }
        
        $headings = array();
        $counter = 0;
        foreach ($matches[0] as $index => $heading) {
            $level = intval($matches[1][$index]);
            $text = strip_tags($matches[2][$index]);
            // Use same ID generation as add_heading_ids
            $id = sanitize_title($text) . '-' . $counter++;
            
            $headings[] = array(
                'level' => $level,
                'text' => $text,
                'id' => $id
            );
        }
        
        if (empty($headings)) {
            return;
        }
        
        ?>
        <div class="jazzedge-docs-toc">
            <h3 class="jazzedge-docs-toc-title"><?php _e('Table of Contents', 'jazzedge-docs'); ?></h3>
            <ul class="jazzedge-docs-toc-list">
                <?php foreach ($headings as $heading): ?>
                    <li class="jazzedge-docs-toc-item jazzedge-docs-toc-level-<?php echo esc_attr($heading['level']); ?>">
                        <a href="#<?php echo esc_attr($heading['id']); ?>">
                            <?php echo esc_html($heading['text']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
    
    /**
     * Render ratings
     */
    public function render_ratings($post_id) {
        $db = new Jazzedge_Docs_Database();
        $average_rating = $db->get_average_rating($post_id);
        $rating_count = $db->get_rating_count($post_id);
        $user_rating = $db->get_user_rating($post_id);
        $user_rating_value = $user_rating ? $user_rating->rating : 0;
        
        ?>
        <div class="jazzedge-docs-ratings">
            <h3><?php _e('Was this helpful?', 'jazzedge-docs'); ?></h3>
            <div class="jazzedge-docs-rating-stars" data-doc-id="<?php echo esc_attr($post_id); ?>">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="jazzedge-docs-star <?php echo $i <= $user_rating_value ? 'active' : ''; ?>" 
                          data-rating="<?php echo esc_attr($i); ?>">
                        ★
                    </span>
                <?php endfor; ?>
            </div>
            <?php if ($average_rating > 0): ?>
                <div class="jazzedge-docs-rating-average">
                    <?php printf(__('Average rating: %s (%d ratings)', 'jazzedge-docs'), 
                        number_format($average_rating, 1), 
                        $rating_count); ?>
                </div>
            <?php endif; ?>
            <div class="jazzedge-docs-rating-feedback" style="display: none;">
                <textarea placeholder="<?php esc_attr_e('Optional feedback...', 'jazzedge-docs'); ?>" 
                          class="jazzedge-docs-feedback-text"></textarea>
                <button class="jazzedge-docs-submit-feedback"><?php _e('Submit', 'jazzedge-docs'); ?></button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render related docs
     */
    public function render_related_docs($related_doc_ids) {
        if (empty($related_doc_ids)) {
            return;
        }
        
        ?>
        <div class="jazzedge-docs-related">
            <h3><?php _e('Related Support Docs', 'jazzedge-docs'); ?></h3>
            <ul class="jazzedge-docs-related-list">
                <?php foreach ($related_doc_ids as $related_id): ?>
                    <?php $related_post = get_post($related_id); ?>
                    <?php if ($related_post): ?>
                        <li>
                            <a href="<?php echo esc_url(get_permalink($related_id)); ?>">
                                <?php echo esc_html($related_post->post_title); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
    
    /**
     * Add IDs to headings in content
     */
    public function add_heading_ids($content) {
        // Use a fresh counter for each call to match TOC generation
        $counter = 0;
        
        // Check if heading already has an ID to avoid duplicates
        $content = preg_replace_callback('/<h([2-6])([^>]*)>(.*?)<\/h[2-6]>/i', function($matches) use (&$counter) {
            $level = intval($matches[1]);
            $attrs = $matches[2];
            $text = strip_tags($matches[3]);
            
            // Check if ID already exists
            if (preg_match('/id=["\']([^"\']+)["\']/', $attrs, $id_match)) {
                return $matches[0]; // Return unchanged if ID already exists
            }
            
            $id = sanitize_title($text) . '-' . $counter++;
            return '<h' . $level . ' id="' . esc_attr($id) . '"' . $attrs . '>' . $matches[3] . '</h' . $level . '>';
        }, $content);
        
        return $content;
    }
}

