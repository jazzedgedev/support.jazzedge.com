<?php
/**
 * Frontend Display for Academy Lesson Manager
 * 
 * Handles frontend display of lessons with basic functionality
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_alm_track_progress', array($this, 'track_progress'));
        add_action('wp_ajax_nopriv_alm_track_progress', array($this, 'track_progress'));
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        if (is_singular('lesson')) {
            wp_enqueue_style(
                'alm-frontend',
                ALM_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                ALM_VERSION
            );
            
            wp_enqueue_script(
                'alm-frontend',
                ALM_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                ALM_VERSION,
                true
            );
            
            wp_localize_script('alm-frontend', 'almAjax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('alm_frontend_nonce')
            ));
        }
    }
    
    /**
     * Track lesson progress
     */
    public function track_progress() {
        check_ajax_referer('alm_frontend_nonce', 'nonce');
        
        $lesson_id = intval($_POST['lesson_id']);
        $progress = intval($_POST['progress']);
        $completed = isset($_POST['completed']) ? (bool) $_POST['completed'] : false;
        
        if (!$lesson_id || !is_user_logged_in()) {
            wp_die('Invalid request');
        }
        
        $user_id = get_current_user_id();
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ja_user_progress';
        
        // Insert or update progress
        $wpdb->replace(
            $table_name,
            array(
                'user_id' => $user_id,
                'lesson_id' => $lesson_id,
                'progress_percent' => $progress,
                'completed' => $completed ? 1 : 0,
                'last_accessed' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%d', '%s')
        );
        
        wp_send_json(array('success' => true));
    }
}
