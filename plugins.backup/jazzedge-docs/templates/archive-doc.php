<?php
/**
 * Archive Template for Jazzedge Docs
 * 
 * This template is used when viewing the docs archive page
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$archive_title = get_option('jazzedge_docs_archive_title', 'Documentation');
$archive_description = get_option('jazzedge_docs_archive_description', '');
?>

<div class="jazzedge-docs-archive">
    <div class="jazzedge-docs-archive-header">
        <h1 class="jazzedge-docs-archive-title"><?php echo esc_html($archive_title); ?></h1>
        <?php if (!empty($archive_description)): ?>
            <div class="jazzedge-docs-archive-description">
                <?php echo wp_kses_post($archive_description); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php echo do_shortcode('[jazzedge_docs_search]'); ?>
    
    <?php echo do_shortcode('[jazzedge_docs_categories]'); ?>
    
    <?php
    if (have_posts()):
        ?>
        <div class="jazzedge-docs-archive-list">
            <h2><?php _e('All Support Docs', 'jazzedge-docs'); ?></h2>
            <?php echo do_shortcode('[jazzedge_docs_list]'); ?>
        </div>
        <?php
    endif;
    ?>
</div>

<?php
get_footer();

