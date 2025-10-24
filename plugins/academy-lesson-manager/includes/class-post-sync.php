<?php
/**
 * Post Sync Class
 * 
 * Handles synchronization between ALM tables and WordPress posts
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Post_Sync {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
     * Sync lesson to WordPress post
     */
    public function sync_lesson_to_post($alm_lesson_id) {
        $database = new ALM_Database();
        $lessons_table = $database->get_table_name('lessons');
        
        // Get lesson data
        $lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$lessons_table} WHERE ID = %d",
            $alm_lesson_id
        ));
        
        if (!$lesson) {
            return false;
        }
        
        // Get collection data for context
        $collections_table = $database->get_table_name('collections');
        $collection = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$collections_table} WHERE ID = %d",
            $lesson->collection_id
        ));
        
        $post_data = array(
            'post_title' => stripslashes($lesson->lesson_title),
            'post_content' => stripslashes($lesson->lesson_description),
            'post_status' => 'publish',
            'post_type' => 'lesson',
            'post_date' => $lesson->post_date ? $lesson->post_date . ' 00:00:00' : current_time('mysql'),
            'post_modified' => current_time('mysql'),
            'post_name' => $lesson->slug ?: sanitize_title($lesson->lesson_title)
        );
        
        // Update existing post or create new one
        if ($lesson->post_id && get_post($lesson->post_id)) {
            $post_data['ID'] = $lesson->post_id;
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
            
            // Update ALM table with new post_id
            $this->wpdb->update(
                $lessons_table,
                array('post_id' => $post_id),
                array('ID' => $alm_lesson_id),
                array('%d'),
                array('%d')
            );
        }
        
        if (is_wp_error($post_id)) {
            return false;
        }
        
        // Update ACF fields
        $this->update_lesson_acf_fields($post_id, $lesson, $collection);
        
        return $post_id;
    }
    
    /**
     * Sync collection to WordPress post
     */
    public function sync_collection_to_post($alm_collection_id) {
        $database = new ALM_Database();
        $collections_table = $database->get_table_name('collections');
        
        // Get collection data
        $collection = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$collections_table} WHERE ID = %d",
            $alm_collection_id
        ));
        
        if (!$collection) {
            return false;
        }
        
        $post_data = array(
            'post_title' => stripslashes($collection->collection_title),
            'post_content' => stripslashes($collection->collection_description),
            'post_status' => 'publish',
            'post_type' => 'lesson-collection',
            'post_date' => $collection->created_at,
            'post_modified' => current_time('mysql'),
            'post_name' => sanitize_title($collection->collection_title)
        );
        
        // Update existing post or create new one
        if ($collection->post_id && get_post($collection->post_id)) {
            $post_data['ID'] = $collection->post_id;
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
            
            // Update ALM table with new post_id
            $this->wpdb->update(
                $collections_table,
                array('post_id' => $post_id),
                array('ID' => $alm_collection_id),
                array('%d'),
                array('%d')
            );
        }
        
        if (is_wp_error($post_id)) {
            return false;
        }
        
        // Update ACF fields
        $this->update_collection_acf_fields($post_id, $collection);
        
        return $post_id;
    }
    
    /**
     * Update ACF fields for lesson
     */
    private function update_lesson_acf_fields($post_id, $lesson, $collection = null) {
        if (!function_exists('update_field')) {
            return;
        }
        
        // Basic lesson fields
        update_field('alm_lesson_id', $lesson->ID, $post_id);
        update_field('alm_collection_id', $lesson->collection_id, $post_id);
        update_field('lesson_duration', $lesson->duration, $post_id);
        update_field('lesson_song_lesson', $lesson->song_lesson === 'y', $post_id);
        update_field('lesson_vtt', $lesson->vtt, $post_id);
        update_field('lesson_slug', $lesson->slug, $post_id);
        update_field('lesson_membership_level', $lesson->membership_level, $post_id);
        
        // Also store as regular post meta for compatibility
        update_post_meta($post_id, 'alm_lesson_id', $lesson->ID);
        update_post_meta($post_id, 'alm_collection_id', $lesson->collection_id);
        
        // Resources and assets
        if ($lesson->resources) {
            $resources = maybe_unserialize($lesson->resources);
            update_field('lesson_resources', $resources, $post_id);
        }
        
        if ($lesson->assets) {
            $assets = maybe_unserialize($lesson->assets);
            update_field('lesson_assets', $assets, $post_id);
        }
        
        // Collection data
        if ($collection) {
            update_field('lesson_collection_title', stripslashes($collection->collection_title), $post_id);
            update_field('lesson_collection_membership_level', $collection->membership_level, $post_id);
        }
        
        // Membership level name
        $membership_name = ALM_Admin_Settings::get_membership_level_name($lesson->membership_level);
        update_field('lesson_membership_level_name', $membership_name, $post_id);
    }
    
    /**
     * Update ACF fields for collection
     */
    private function update_collection_acf_fields($post_id, $collection) {
        if (!function_exists('update_field')) {
            return;
        }
        
        // Basic collection fields
        update_field('alm_collection_id', $collection->ID, $post_id);
        update_field('collection_membership_level', $collection->membership_level, $post_id);
        
        // Also store as regular post meta for compatibility
        update_post_meta($post_id, 'alm_collection_id', $collection->ID);
        
        // Membership level name
        $membership_name = ALM_Admin_Settings::get_membership_level_name($collection->membership_level);
        update_field('collection_membership_level_name', $membership_name, $post_id);
        
        // Count lessons in collection
        $lesson_count = ALM_Helpers::count_collection_lessons($collection->ID);
        update_field('collection_lesson_count', $lesson_count, $post_id);
    }
    
    /**
     * Delete WordPress post and related data
     */
    public function delete_post_and_meta($post_id) {
        if (!$post_id) {
            return false;
        }
        
        // Delete post meta
        delete_post_meta($post_id, 'alm_lesson_id');
        delete_post_meta($post_id, 'alm_collection_id');
        delete_post_meta($post_id, 'lesson_duration');
        delete_post_meta($post_id, 'lesson_song_lesson');
        delete_post_meta($post_id, 'lesson_vtt');
        delete_post_meta($post_id, 'lesson_slug');
        delete_post_meta($post_id, 'lesson_membership_level');
        delete_post_meta($post_id, 'lesson_resources');
        delete_post_meta($post_id, 'lesson_assets');
        delete_post_meta($post_id, 'lesson_collection_title');
        delete_post_meta($post_id, 'lesson_collection_membership_level');
        delete_post_meta($post_id, 'lesson_membership_level_name');
        delete_post_meta($post_id, 'collection_membership_level');
        delete_post_meta($post_id, 'collection_membership_level_name');
        delete_post_meta($post_id, 'collection_lesson_count');
        
        // Delete ACF fields if function exists
        if (function_exists('delete_field')) {
            $fields_to_delete = array(
                'alm_lesson_id', 'alm_collection_id', 'lesson_duration', 'lesson_song_lesson',
                'lesson_vtt', 'lesson_slug', 'lesson_membership_level', 'lesson_resources',
                'lesson_assets', 'lesson_collection_title', 'lesson_collection_membership_level',
                'lesson_membership_level_name', 'collection_membership_level',
                'collection_membership_level_name', 'collection_lesson_count'
            );
            
            foreach ($fields_to_delete as $field) {
                delete_field($field, $post_id);
            }
        }
        
        // Delete the post
        wp_delete_post($post_id, true);
        
        return true;
    }
    
    /**
     * Sync lesson from WordPress post to ALM table
     */
    public function sync_post_to_lesson($post_id) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'lesson') {
            return false;
        }
        
        $database = new ALM_Database();
        $lessons_table = $database->get_table_name('lessons');
        
        // Get ALM lesson ID from ACF
        $alm_lesson_id = get_field('alm_lesson_id', $post_id);
        
        if (!$alm_lesson_id) {
            return false;
        }
        
        // Update ALM table with post data
        $data = array(
            'lesson_title' => $post->post_title,
            'lesson_description' => $post->post_content,
            'slug' => $post->post_name,
            'post_date' => date('Y-m-d', strtotime($post->post_date))
        );
        
        $result = $this->wpdb->update(
            $lessons_table,
            $data,
            array('ID' => $alm_lesson_id),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Sync collection from WordPress post to ALM table
     */
    public function sync_post_to_collection($post_id) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'lesson-collection') {
            return false;
        }
        
        $database = new ALM_Database();
        $collections_table = $database->get_table_name('collections');
        
        // Get ALM collection ID from ACF
        $alm_collection_id = get_field('alm_collection_id', $post_id);
        
        if (!$alm_collection_id) {
            return false;
        }
        
        // Update ALM table with post data
        $data = array(
            'collection_title' => $post->post_title,
            'collection_description' => $post->post_content
        );
        
        $result = $this->wpdb->update(
            $collections_table,
            $data,
            array('ID' => $alm_collection_id),
            array('%s', '%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get post ID for ALM lesson
     */
    public function get_lesson_post_id($alm_lesson_id) {
        $database = new ALM_Database();
        $lessons_table = $database->get_table_name('lessons');
        
        $lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT post_id FROM {$lessons_table} WHERE ID = %d",
            $alm_lesson_id
        ));
        
        return $lesson ? $lesson->post_id : 0;
    }
    
    /**
     * Get post ID for ALM collection
     */
    public function get_collection_post_id($alm_collection_id) {
        $database = new ALM_Database();
        $collections_table = $database->get_table_name('collections');
        
        $collection = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT post_id FROM {$collections_table} WHERE ID = %d",
            $alm_collection_id
        ));
        
        return $collection ? $collection->post_id : 0;
    }
}
