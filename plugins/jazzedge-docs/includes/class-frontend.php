<?php
/**
 * Frontend Class for Jazzedge Docs
 * Handles frontend display functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jazzedge_Docs_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_head', array($this, 'add_meta_tags'));
        add_filter('template_include', array($this, 'template_include'));
        add_filter('single_template', array($this, 'single_template'));
        add_filter('archive_template', array($this, 'archive_template'));
        add_action('wp', array($this, 'track_view'));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style(
            'jazzedge-docs-frontend',
            JAZZEDGE_DOCS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            JAZZEDGE_DOCS_VERSION
        );
        
        wp_enqueue_script(
            'jazzedge-docs-frontend',
            JAZZEDGE_DOCS_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            JAZZEDGE_DOCS_VERSION,
            true
        );
        
        wp_localize_script('jazzedge-docs-frontend', 'jazzedgeDocs', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('jazzedge-docs/v1/'),
            'restNonce' => wp_create_nonce('wp_rest'),
            'nonce' => wp_create_nonce('jazzedge_docs_nonce')
        ));
    }
    
    /**
     * Add meta tags
     */
    public function add_meta_tags() {
        if (is_singular('jazzedge_doc')) {
            global $post;
            echo '<meta name="description" content="' . esc_attr(wp_strip_all_tags(get_the_excerpt())) . '">' . "\n";
        }
    }
    
    /**
     * Template include
     */
    public function template_include($template) {
        if (is_post_type_archive('jazzedge_doc')) {
            $custom_template = locate_template(array('archive-jazzedge_doc.php'));
            if ($custom_template) {
                return $custom_template;
            }
            return JAZZEDGE_DOCS_PLUGIN_DIR . 'templates/archive-doc.php';
        }
        
        return $template;
    }
    
    /**
     * Single template
     */
    public function single_template($template) {
        if (is_singular('jazzedge_doc')) {
            $custom_template = locate_template(array('single-jazzedge_doc.php'));
            if ($custom_template) {
                return $custom_template;
            }
            return JAZZEDGE_DOCS_PLUGIN_DIR . 'templates/single-doc.php';
        }
        
        return $template;
    }
    
    /**
     * Archive template
     */
    public function archive_template($template) {
        if (is_post_type_archive('jazzedge_doc')) {
            $custom_template = locate_template(array('archive-jazzedge_doc.php'));
            if ($custom_template) {
                return $custom_template;
            }
            return JAZZEDGE_DOCS_PLUGIN_DIR . 'templates/archive-doc.php';
        }
        
        return $template;
    }
    
    /**
     * Track view
     */
    public function track_view() {
        if (is_singular('jazzedge_doc')) {
            global $post;
            $db = new Jazzedge_Docs_Database();
            $db->record_view($post->ID);
        }
    }
    
}

