<?php
/**
 * Search Class for Jazzedge Docs
 * Handles search functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jazzedge_Docs_Search {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_jazzedge_docs_search', array($this, 'ajax_search'));
        add_action('wp_ajax_nopriv_jazzedge_docs_search', array($this, 'ajax_search'));
    }
    
    /**
     * AJAX search handler
     */
    public function ajax_search() {
        check_ajax_referer('jazzedge_docs_nonce', 'nonce');
        
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        
        if (empty($query)) {
            wp_send_json_error(array('message' => __('Please enter a search term.', 'jazzedge-docs')));
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
                $excerpt = get_the_excerpt();
                $permalink = get_permalink();
                
                // Highlight search term in title and excerpt
                $title = $this->highlight_search_term(get_the_title(), $query);
                $excerpt = $this->highlight_search_term(wp_trim_words($excerpt, 30), $query);
                
                $results[] = array(
                    'id' => $post_id,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'url' => $permalink,
                    'date' => get_the_date()
                );
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success(array(
            'results' => $results,
            'count' => count($results),
            'query' => $query
        ));
    }
    
    /**
     * Highlight search term in text
     */
    private function highlight_search_term($text, $term) {
        return preg_replace('/(' . preg_quote($term, '/') . ')/i', '<mark>$1</mark>', $text);
    }
    
    /**
     * Get search suggestions
     */
    public function get_suggestions($query, $limit = 5) {
        $args = array(
            'post_type' => 'jazzedge_doc',
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            's' => $query,
            'orderby' => 'relevance'
        );
        
        $query = new WP_Query($args);
        $suggestions = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $suggestions[] = array(
                    'title' => get_the_title(),
                    'url' => get_permalink()
                );
            }
            wp_reset_postdata();
        }
        
        return $suggestions;
    }
}

