<?php
/**
 * Admin Class for Jazzedge Docs
 * Handles admin interface functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jazzedge_Docs_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_footer', array($this, 'admin_footer_scripts'));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'jazzedge') !== false || strpos($hook, 'doc') !== false) {
            wp_enqueue_style(
                'jazzedge-docs-admin',
                JAZZEDGE_DOCS_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                JAZZEDGE_DOCS_VERSION
            );
            
            wp_enqueue_script(
                'jazzedge-docs-admin',
                JAZZEDGE_DOCS_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                JAZZEDGE_DOCS_VERSION,
                true
            );
            
            wp_localize_script('jazzedge-docs-admin', 'jazzedgeDocsAdmin', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('jazzedge_docs_admin_nonce')
            ));
        }
    }
    
    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=jazzedge_doc',
            __('Analytics', 'jazzedge-docs'),
            __('Analytics', 'jazzedge-docs'),
            'manage_options',
            'jazzedge-docs-analytics',
            array($this, 'render_analytics_page')
        );
        
        add_submenu_page(
            'edit.php?post_type=jazzedge_doc',
            __('Settings', 'jazzedge-docs'),
            __('Settings', 'jazzedge-docs'),
            'manage_options',
            'jazzedge-docs-settings',
            array($this, 'render_settings_page')
        );
        
        // Handle flush rewrite rules
        add_action('admin_init', array($this, 'handle_flush_rewrite_rules'));
    }
    
    /**
     * Handle flush rewrite rules
     */
    public function handle_flush_rewrite_rules() {
        if (isset($_GET['jazzedge_docs_flush_rules']) && isset($_GET['_wpnonce'])) {
            if (wp_verify_nonce($_GET['_wpnonce'], 'jazzedge_docs_flush_rules') && current_user_can('manage_options')) {
                // Force hard flush
                flush_rewrite_rules(true);
                delete_option('jazzedge_docs_flush_rewrite_rules');
                
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Rewrite rules flushed successfully! You may need to refresh the page.', 'jazzedge-docs') . '</p></div>';
                });
            }
        }
    }
    
    /**
     * Render analytics page
     */
    public function render_analytics_page() {
        $db = new Jazzedge_Docs_Database();
        
        // Get popular docs
        $popular_docs = $db->get_popular_docs(10);
        
        // Get top-rated docs
        $top_rated_docs = $db->get_top_rated_docs(10);
        
        // Get all docs with ratings
        $all_docs = get_posts(array(
            'post_type' => 'jazzedge_doc',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        $docs_with_ratings = array();
        foreach ($all_docs as $doc) {
            $avg_rating = $db->get_average_rating($doc->ID);
            $rating_count = $db->get_rating_count($doc->ID);
            $feedback_count = $db->get_feedback_count($doc->ID);
            if ($rating_count > 0) {
                $docs_with_ratings[] = array(
                    'doc' => $doc,
                    'avg_rating' => $avg_rating,
                    'rating_count' => $rating_count,
                    'feedback_count' => $feedback_count
                );
            }
        }
        
        // Sort by average rating descending
        usort($docs_with_ratings, function($a, $b) {
            if ($a['avg_rating'] == $b['avg_rating']) {
                return $b['rating_count'] - $a['rating_count'];
            }
            return $b['avg_rating'] > $a['avg_rating'] ? 1 : -1;
        });
        
        ?>
        <div class="wrap">
            <h1><?php _e('Support Docs Analytics', 'jazzedge-docs'); ?></h1>
            
            <div class="jazzedge-docs-analytics">
                <div class="analytics-section">
                    <h2><?php _e('Popular Support Docs', 'jazzedge-docs'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Support Doc', 'jazzedge-docs'); ?></th>
                                <th><?php _e('Views', 'jazzedge-docs'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($popular_docs): ?>
                                <?php foreach ($popular_docs as $doc_data): ?>
                                    <?php $doc = get_post($doc_data->doc_id); ?>
                                    <?php if ($doc): ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo get_edit_post_link($doc->ID); ?>">
                                                    <?php echo esc_html($doc->post_title); ?>
                                                </a>
                                            </td>
                                            <td><?php echo esc_html($doc_data->view_count); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2"><?php _e('No views yet.', 'jazzedge-docs'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="analytics-section" style="margin-top: 30px;">
                    <h2><?php _e('Support Docs Ratings', 'jazzedge-docs'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Support Doc', 'jazzedge-docs'); ?></th>
                                <th><?php _e('Average Rating', 'jazzedge-docs'); ?></th>
                                <th><?php _e('Rating Count', 'jazzedge-docs'); ?></th>
                                <th><?php _e('Comments', 'jazzedge-docs'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($docs_with_ratings)): ?>
                                <?php foreach ($docs_with_ratings as $doc_data): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo get_edit_post_link($doc_data['doc']->ID); ?>">
                                                <?php echo esc_html($doc_data['doc']->post_title); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php 
                                            $avg_rating = $doc_data['avg_rating'];
                                            echo esc_html(number_format($avg_rating, 1));
                                            // Display stars
                                            echo ' <span style="color: #ffb900;">';
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $avg_rating) {
                                                    echo '★';
                                                } elseif ($i - $avg_rating < 0.5 && $i - $avg_rating > 0) {
                                                    echo '☆';
                                                } else {
                                                    echo '<span style="opacity: 0.3;">☆</span>';
                                                }
                                            }
                                            echo '</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo esc_html($doc_data['rating_count']); ?>
                                            <?php if ($doc_data['rating_count'] > 0): ?>
                                                <a href="<?php echo esc_url(add_query_arg(array('page' => 'jazzedge-docs-analytics', 'view_ratings' => $doc_data['doc']->ID), admin_url('edit.php?post_type=jazzedge_doc'))); ?>" 
                                                   style="margin-left: 10px;">
                                                    <?php _e('View Ratings', 'jazzedge-docs'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($doc_data['feedback_count'] > 0): ?>
                                                <strong style="color: #2271b1;"><?php echo esc_html($doc_data['feedback_count']); ?></strong>
                                                <?php echo $doc_data['feedback_count'] === 1 ? __('comment', 'jazzedge-docs') : __('comments', 'jazzedge-docs'); ?>
                                            <?php else: ?>
                                                <span style="color: #999;"><?php _e('No comments', 'jazzedge-docs'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4"><?php _e('No ratings yet.', 'jazzedge-docs'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php
                // Show individual ratings if viewing a specific doc
                if (isset($_GET['view_ratings']) && intval($_GET['view_ratings']) > 0):
                    $view_doc_id = intval($_GET['view_ratings']);
                    $view_doc = get_post($view_doc_id);
                    $ratings = $db->get_doc_ratings($view_doc_id);
                    ?>
                    <div class="analytics-section" style="margin-top: 30px;">
                        <h2>
                            <?php printf(__('Ratings & Feedback: %s', 'jazzedge-docs'), esc_html($view_doc->post_title)); ?>
                            <a href="<?php echo esc_url(remove_query_arg('view_ratings')); ?>" class="button" style="margin-left: 10px;">
                                <?php _e('Back to Analytics', 'jazzedge-docs'); ?>
                            </a>
                        </h2>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Rating', 'jazzedge-docs'); ?></th>
                                    <th><?php _e('User', 'jazzedge-docs'); ?></th>
                                    <th><?php _e('Feedback', 'jazzedge-docs'); ?></th>
                                    <th><?php _e('Date', 'jazzedge-docs'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ratings)): ?>
                                    <?php foreach ($ratings as $rating): ?>
                                        <tr>
                                            <td>
                                                <span style="color: #ffb900; font-size: 18px;">
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
                                                        echo '<br><small>' . esc_html($rating->user_email) . '</small>';
                                                    }
                                                } else {
                                                    echo __('Guest', 'jazzedge-docs');
                                                    if ($rating->user_ip) {
                                                        echo '<br><small>' . esc_html($rating->user_ip) . '</small>';
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo $rating->feedback ? esc_html($rating->feedback) : '<em>' . __('No feedback provided', 'jazzedge-docs') . '</em>'; ?></td>
                                            <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($rating->created_at))); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4"><?php _e('No ratings found.', 'jazzedge-docs'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (isset($_POST['jazzedge_docs_settings_submit'])) {
            check_admin_referer('jazzedge_docs_settings');
            
            update_option('jazzedge_docs_archive_title', sanitize_text_field($_POST['archive_title']));
            update_option('jazzedge_docs_archive_description', wp_kses_post($_POST['archive_description']));
            update_option('jazzedge_docs_search_placeholder', sanitize_text_field($_POST['search_placeholder']));
            update_option('jazzedge_docs_per_page', intval($_POST['per_page']));
            
            echo '<div class="notice notice-success"><p>' . __('Settings saved.', 'jazzedge-docs') . '</p></div>';
        }
        
        $archive_title = get_option('jazzedge_docs_archive_title', 'Support Documentation');
        $archive_description = get_option('jazzedge_docs_archive_description', '');
        $search_placeholder = get_option('jazzedge_docs_search_placeholder', 'Search support documentation...');
        $per_page = get_option('jazzedge_docs_per_page', 12);
        
        ?>
        <div class="wrap">
            <h1><?php _e('Support Docs Settings', 'jazzedge-docs'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('jazzedge_docs_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="archive_title"><?php _e('Archive Title', 'jazzedge-docs'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="archive_title" id="archive_title" value="<?php echo esc_attr($archive_title); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="archive_description"><?php _e('Archive Description', 'jazzedge-docs'); ?></label>
                        </th>
                        <td>
                            <?php wp_editor($archive_description, 'archive_description', array('textarea_rows' => 5)); ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="search_placeholder"><?php _e('Search Placeholder', 'jazzedge-docs'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="search_placeholder" id="search_placeholder" value="<?php echo esc_attr($search_placeholder); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="per_page"><?php _e('Docs Per Page', 'jazzedge-docs'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="per_page" id="per_page" value="<?php echo esc_attr($per_page); ?>" min="1" max="100">
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Save Settings', 'jazzedge-docs'), 'primary', 'jazzedge_docs_settings_submit'); ?>
            </form>
            
            <hr style="margin: 30px 0;">
            
            <h2><?php _e('Tools', 'jazzedge-docs'); ?></h2>
            <p><?php _e('If you are experiencing 404 errors with your support docs, try flushing the rewrite rules:', 'jazzedge-docs'); ?></p>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('edit.php?post_type=jazzedge_doc&page=jazzedge-docs-settings&jazzedge_docs_flush_rules=1'), 'jazzedge_docs_flush_rules')); ?>" 
               class="button button-secondary">
                <?php _e('Flush Rewrite Rules', 'jazzedge-docs'); ?>
            </a>
            
            <hr style="margin: 30px 0;">
            
            <h2><?php _e('Debug Information', 'jazzedge-docs'); ?></h2>
            <?php
            // Check if post type is registered
            $post_type_obj = get_post_type_object('jazzedge_doc');
            if ($post_type_obj) {
                echo '<p><strong>' . __('Post Type Registered:', 'jazzedge-docs') . '</strong> Yes</p>';
                echo '<p><strong>' . __('Rewrite Slug:', 'jazzedge-docs') . '</strong> ' . esc_html($post_type_obj->rewrite['slug']) . '</p>';
            } else {
                echo '<p><strong style="color: red;">' . __('Post Type Registered:', 'jazzedge-docs') . '</strong> No</p>';
            }
            
            // Check rewrite rules
            $rules = get_option('rewrite_rules');
            $has_docs_rules = false;
            if ($rules && is_array($rules)) {
                foreach ($rules as $pattern => $rule) {
                    if (strpos($pattern, 'docs') !== false || strpos($rule, 'jazzedge_doc') !== false) {
                        $has_docs_rules = true;
                        echo '<p><strong>' . __('Rewrite Rule Found:', 'jazzedge-docs') . '</strong> ' . esc_html($pattern) . ' => ' . esc_html($rule) . '</p>';
                    }
                }
            }
            if (!$has_docs_rules) {
                echo '<p><strong style="color: red;">' . __('Rewrite Rules:', 'jazzedge-docs') . '</strong> ' . __('No docs rewrite rules found. Please flush rewrite rules.', 'jazzedge-docs') . '</p>';
            }
            
            // Check for test doc
            $test_doc = get_page_by_path('test', OBJECT, 'jazzedge_doc');
            if ($test_doc) {
                echo '<p><strong>' . __('Test Support Doc Found:', 'jazzedge-docs') . '</strong> Yes (ID: ' . $test_doc->ID . ')</p>';
                echo '<p><strong>' . __('Doc Status:', 'jazzedge-docs') . '</strong> ' . esc_html($test_doc->post_status) . '</p>';
                echo '<p><strong>' . __('Expected URL:', 'jazzedge-docs') . '</strong> <a href="' . esc_url(get_permalink($test_doc->ID)) . '" target="_blank">' . esc_url(get_permalink($test_doc->ID)) . '</a></p>';
            } else {
                echo '<p><strong style="color: orange;">' . __('Test Support Doc Found:', 'jazzedge-docs') . '</strong> ' . __('No support doc with slug "test" found. Make sure the doc exists and is published.', 'jazzedge-docs') . '</p>';
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Admin footer scripts
     */
    public function admin_footer_scripts() {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'jazzedge_doc' && $screen->base === 'post') {
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Handle related docs
                $('#jazzedge-doc-add-related').on('click', function() {
                    var select = $('#jazzedge-doc-related-select');
                    var docId = select.val();
                    var docTitle = select.find('option:selected').text();
                    
                    if (!docId) {
                        alert('<?php echo esc_js(__('Please select a support doc first.', 'jazzedge-docs')); ?>');
                        return;
                    }
                    
                    // Check if already added
                    if ($('#jazzedge-doc-related-list').find('[data-doc-id="' + docId + '"]').length > 0) {
                        alert('<?php echo esc_js(__('This support doc is already in the related list.', 'jazzedge-docs')); ?>');
                        return;
                    }
                    
                    // Add to list
                    var li = $('<li>')
                        .attr('data-doc-id', docId)
                        .css({
                            'padding': '8px',
                            'background': '#f9f9f9',
                            'margin-bottom': '5px',
                            'display': 'flex',
                            'justify-content': 'space-between',
                            'align-items': 'center'
                        })
                        .html(
                            '<span>' + docTitle + '</span>' +
                            '<button type="button" class="jazzedge-doc-remove-related button-link" data-doc-id="' + docId + '"><?php echo esc_js(__('Remove', 'jazzedge-docs')); ?></button>' +
                            '<input type="hidden" name="jazzedge_doc_related[]" value="' + docId + '">'
                        );
                    
                    $('#jazzedge-doc-related-list').append(li);
                    select.val('');
                });
                
                // Handle remove related doc
                $(document).on('click', '.jazzedge-doc-remove-related', function() {
                    $(this).closest('li').remove();
                });
            });
            </script>
            <?php
        }
    }
}

