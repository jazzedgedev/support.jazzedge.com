<?php
/**
 * REST API Class for Jazzedge Docs
 * Handles REST API endpoints
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jazzedge_Docs_REST_API {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST routes
     */
    public function register_routes() {
        register_rest_route('jazzedge-docs/v1', '/search', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_search'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jazzedge-docs/v1', '/rating', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_submit_rating'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jazzedge-docs/v1', '/docs', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_docs'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jazzedge-docs/v1', '/categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_categories'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * REST search endpoint
     */
    public function rest_search($request) {
        $query = $request->get_param('q');
        $limit = $request->get_param('limit') ? intval($request->get_param('limit')) : 10;
        
        if (empty($query)) {
            return new WP_Error('no_query', __('Search query is required.', 'jazzedge-docs'), array('status' => 400));
        }
        
        $args = array(
            'post_type' => 'jazzedge_doc',
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            's' => $query,
            'orderby' => 'relevance'
        );
        
        $search_query = new WP_Query($args);
        
        $results = array();
        
        if ($search_query->have_posts()) {
            while ($search_query->have_posts()) {
                $search_query->the_post();
                
                $post_id = get_the_ID();
                $categories = get_the_terms($post_id, 'jazzedge_doc_category');
                
                $results[] = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'excerpt' => wp_trim_words(get_the_excerpt(), 30),
                    'url' => get_permalink(),
                    'date' => get_the_date(),
                    'categories' => $categories && !is_wp_error($categories) ? array_map(function($cat) {
                        return array(
                            'id' => $cat->term_id,
                            'name' => $cat->name,
                            'slug' => $cat->slug
                        );
                    }, $categories) : array()
                );
            }
            wp_reset_postdata();
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'results' => $results,
            'count' => count($results),
            'query' => $query
        ));
    }
    
    /**
     * REST submit rating endpoint
     */
    public function rest_submit_rating($request) {
        $doc_id = $request->get_param('doc_id');
        $rating = $request->get_param('rating');
        $feedback = $request->get_param('feedback');
        
        if (empty($doc_id) || empty($rating)) {
            return new WP_Error('missing_params', __('Doc ID and rating are required.', 'jazzedge-docs'), array('status' => 400));
        }
        
        $doc_id = intval($doc_id);
        $rating = intval($rating);
        
        if ($rating < 1 || $rating > 5) {
            return new WP_Error('invalid_rating', __('Rating must be between 1 and 5.', 'jazzedge-docs'), array('status' => 400));
        }
        
        $db = new Jazzedge_Docs_Database();
        $result = $db->record_rating($doc_id, $rating, sanitize_textarea_field($feedback));
        
        if ($result) {
            $average = $db->get_average_rating($doc_id);
            $count = $db->get_rating_count($doc_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => __('Rating submitted successfully.', 'jazzedge-docs'),
                'average_rating' => $average,
                'rating_count' => $count
            ));
        }
        
        return new WP_Error('rating_failed', __('Failed to submit rating.', 'jazzedge-docs'), array('status' => 500));
    }
    
    /**
     * REST get docs endpoint
     */
    public function rest_get_docs($request) {
        $category = $request->get_param('category');
        $limit = $request->get_param('limit') ? intval($request->get_param('limit')) : 10;
        $offset = $request->get_param('offset') ? intval($request->get_param('offset')) : 0;
        
        $args = array(
            'post_type' => 'jazzedge_doc',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        if (!empty($category)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'jazzedge_doc_category',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($category)
                )
            );
        }
        
        $query = new WP_Query($args);
        
        $docs = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $post_id = get_the_ID();
                $categories = get_the_terms($post_id, 'jazzedge_doc_category');
                
                $docs[] = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'url' => get_permalink(),
                    'date' => get_the_date(),
                    'categories' => $categories && !is_wp_error($categories) ? array_map(function($cat) {
                        return array(
                            'id' => $cat->term_id,
                            'name' => $cat->name,
                            'slug' => $cat->slug
                        );
                    }, $categories) : array()
                );
            }
            wp_reset_postdata();
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'docs' => $docs,
            'total' => $query->found_posts
        ));
    }
    
    /**
     * REST get categories endpoint
     */
    public function rest_get_categories($request) {
        $parent = $request->get_param('parent');
        
        $args = array(
            'taxonomy' => 'jazzedge_doc_category',
            'hide_empty' => false,
            'orderby' => 'meta_value_num',
            'meta_key' => 'category_order',
            'order' => 'ASC'
        );
        
        if ($parent !== null) {
            $args['parent'] = intval($parent);
        } else {
            $args['parent'] = 0;
        }
        
        $categories = get_terms($args);
        
        $formatted_categories = array();
        
        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $category) {
                $icon = get_term_meta($category->term_id, 'category_icon', true);
                
                $formatted_categories[] = array(
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'count' => $category->count,
                    'icon' => $icon,
                    'url' => get_term_link($category)
                );
            }
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'categories' => $formatted_categories
        ));
    }
}

