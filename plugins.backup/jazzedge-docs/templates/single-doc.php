<?php
/**
 * Single Template for Jazzedge Docs
 * 
 * This template is used when viewing a single doc
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$helpers = new Jazzedge_Docs_Helpers();
$db = new Jazzedge_Docs_Database();

while (have_posts()):
    the_post();
    $post_id = get_the_ID();
    ?>
    
    <article id="post-<?php the_ID(); ?>" <?php post_class('jazzedge-doc-single'); ?>>
        <div class="jazzedge-doc-container">
            <?php $helpers->render_breadcrumbs($post_id); ?>
            
            <h1 class="jazzedge-doc-title"><?php the_title(); ?></h1>
            
            <div class="jazzedge-doc-meta">
                <?php
                $categories = get_the_terms($post_id, 'jazzedge_doc_category');
                if ($categories && !is_wp_error($categories)):
                    ?>
                    <span class="jazzedge-doc-categories">
                        <?php foreach ($categories as $category): ?>
                            <a href="<?php echo esc_url(get_term_link($category)); ?>">
                                <?php echo esc_html($category->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </span>
                <?php endif; ?>
                <span class="jazzedge-doc-date"><?php echo get_the_date(); ?></span>
                <?php
                $view_count = $db->get_view_count($post_id);
                if ($view_count > 0):
                    ?>
                    <span class="jazzedge-doc-views"><?php echo esc_html($view_count); ?> <?php _e('views', 'jazzedge-docs'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="jazzedge-doc-content">
                <?php
                the_content();
                ?>
            </div>
            
            <?php $helpers->render_ratings($post_id); ?>
            
            <?php
            // Related docs
            $related_doc_ids = $db->get_related_docs($post_id, 5);
            if (!empty($related_doc_ids)):
                $helpers->render_related_docs($related_doc_ids);
            endif;
            ?>
            
            <div class="jazzedge-doc-print">
                <button onclick="window.print()" class="jazzedge-doc-print-btn">
                    <?php _e('Print', 'jazzedge-docs'); ?>
                </button>
            </div>
        </div>
    </article>
    
    <?php
endwhile;

get_footer();

