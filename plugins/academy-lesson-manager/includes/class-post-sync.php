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
    
    private function alm_debug_log( $msg ) {
        $log_file = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR . '/alm-debug.log' : ABSPATH . 'wp-content/alm-debug.log';
        @file_put_contents( $log_file, '[' . date( 'Y-m-d H:i:s' ) . '] ' . $msg . "\n", FILE_APPEND | LOCK_EX );
    }
    
    /**
     * Sync lesson to WordPress post
     */
    public function sync_lesson_to_post($alm_lesson_id) {
        $this->alm_debug_log( 'sync_lesson_to_post START alm_lesson_id=' . $alm_lesson_id );
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
        
        $post_date = $lesson->post_date ? $lesson->post_date . ' 00:00:00' : current_time('mysql');
        // Ensure WP post stays published even if lesson release date is in the future
        if (strtotime($post_date) > current_time('timestamp')) {
            $post_date = current_time('mysql');
        }
        $post_status = 'publish';
        $post_author = (int) get_current_user_id();
        if ( ! $post_author ) {
            $post_author = 1;
        }
        $post_content = stripslashes( $lesson->lesson_description );
        if ( $post_content === '' || $post_content === null ) {
            $post_content = ' ';
        }
        $post_data = array(
            'post_title'   => stripslashes( $lesson->lesson_title ),
            'post_content' => $post_content,
            'post_status'  => $post_status,
            'post_type'    => 'lesson',
            'post_author'  => $post_author,
            'post_date'    => $post_date,
            'post_date_gmt' => get_gmt_from_date( $post_date ),
            'post_modified' => current_time( 'mysql' ),
            'post_name'    => $lesson->slug ?: sanitize_title( $lesson->lesson_title ),
        );
        
        // Determine if we're updating an existing post or creating new
        $is_update = false;
        if ( ! empty( $lesson->post_id ) ) {
            $existing = get_post( (int) $lesson->post_id );
            if ( $existing instanceof WP_Post ) {
                $post_data['ID'] = (int) $lesson->post_id;
                $is_update = true;
            }
        }
        
        // Critical: Never pass ID=0 or invalid post_id to wp_update_post.
        // That fires save_post with null post → Memberium fatal (expects WP_Post).
        // Corrupted post_id=0 can occur after server migration (AUTO_INCREMENT issues).
        if ( isset( $post_data['ID'] ) ) {
            $existing = get_post( (int) $post_data['ID'] );
            if ( empty( $post_data['ID'] ) || ! $existing instanceof WP_Post ) {
                unset( $post_data['ID'] );
                $is_update = false;
            }
        }
        
        $this->alm_debug_log( 'sync_lesson_to_post before wp_insert/wp_update is_update=' . ( $is_update ? '1' : '0' )
            . ' post_type_exists=' . ( post_type_exists( 'lesson' ) ? '1' : '0' )
            . ' post_author=' . $post_author );
        // Workaround: save_post hooks cause memory exhaustion; wp_insert_post_data/pre_insert_post can block insert.
        // Temporarily remove them so we can create the post; we'll set ACF fields ourselves right after.
        global $wp_filter;
        $saved = array();
        foreach ( array( 'save_post', 'save_post_lesson', 'wp_insert_post_data', 'wp_insert_post_empty_content', 'pre_insert_post' ) as $hook ) {
            if ( isset( $wp_filter[ $hook ] ) ) {
                $saved[ $hook ] = $wp_filter[ $hook ];
                remove_all_filters( $hook );
            }
        }
        if ( $is_update ) {
            $post_id = wp_update_post( $post_data, true );
        } else {
            $post_id = wp_insert_post( $post_data, true );
        }
        foreach ( $saved as $hook => $val ) {
            $wp_filter[ $hook ] = $val;
        }
        $this->alm_debug_log( 'sync_lesson_to_post after wp post_id=' . ( is_wp_error( $post_id ) ? 'WP_Error' : ( $post_id ?: '0' ) ) );

        // Fallback: if wp_insert_post returned 0, insert directly via wpdb to bypass any remaining WP filters.
        if ( ! $is_update && ( is_wp_error( $post_id ) || empty( $post_id ) ) ) {
            $this->alm_debug_log( 'sync_lesson_to_post trying direct wpdb insert fallback' );
            $post_modified_gmt = get_gmt_from_date( $post_data['post_modified'] );
            $insert_result = $this->wpdb->insert(
                $this->wpdb->posts,
                array(
                    'post_author'       => $post_data['post_author'],
                    'post_date'         => $post_data['post_date'],
                    'post_date_gmt'     => $post_data['post_date_gmt'],
                    'post_content'      => $post_data['post_content'],
                    'post_title'        => $post_data['post_title'],
                    'post_status'       => $post_data['post_status'],
                    'post_name'         => $post_data['post_name'],
                    'post_modified'     => $post_data['post_modified'],
                    'post_modified_gmt' => $post_modified_gmt,
                    'post_type'         => $post_data['post_type'],
                    'post_parent'       => 0,
                    'comment_status'    => 'closed',
                    'ping_status'       => 'closed',
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
            );
            if ( $insert_result ) {
                $post_id = (int) $this->wpdb->insert_id;
                if ( ! $post_id ) {
                    // insert_id=0 despite success: wp_posts likely has corrupted primary key / missing AUTO_INCREMENT
                    $this->alm_debug_log( 'sync_lesson_to_post fallback insert_id=0 - wp_posts table may have corrupted PRIMARY KEY or missing AUTO_INCREMENT on ID column. Run in phpMyAdmin: SHOW INDEX FROM ' . $this->wpdb->posts . '; to verify.' );
                    $post_id = $this->wpdb->get_var( $this->wpdb->prepare(
                        "SELECT ID FROM {$this->wpdb->posts} WHERE post_title = %s AND post_type = 'lesson' AND post_author = %d AND post_date = %s ORDER BY ID DESC LIMIT 1",
                        $post_data['post_title'],
                        $post_data['post_author'],
                        $post_data['post_date']
                    ) );
                    if ( $post_id ) {
                        $post_id = (int) $post_id;
                        $this->alm_debug_log( 'sync_lesson_to_post recovered post_id via lookup: ' . $post_id );
                    }
                } else {
                    $this->alm_debug_log( 'sync_lesson_to_post fallback succeeded post_id=' . $post_id );
                }
            } else {
                $this->alm_debug_log( 'sync_lesson_to_post fallback failed wpdb_error=' . $this->wpdb->last_error );
            }
        }
        
        // Validate before proceeding - prevents null post propagating to save_post hooks
        if ( is_wp_error( $post_id ) || empty( $post_id ) ) {
            error_log( 'ALM Post Sync failed: ' . ( is_wp_error( $post_id ) ? $post_id->get_error_message() : 'Empty post ID returned' ) );
            return false;
        }
        
        // Update ALM table with new post_id when we inserted (including fallback from corrupted post_id)
        if ( ! $is_update ) {
            $this->wpdb->update(
                $lessons_table,
                array('post_id' => $post_id),
                array('ID' => $alm_lesson_id),
                array('%d'),
                array('%d')
            );
        }
        
        $this->alm_debug_log( 'sync_lesson_to_post before update_lesson_acf_fields' );
        // Update ACF fields
        $this->update_lesson_acf_fields($post_id, $lesson, $collection);
        $this->alm_debug_log( 'sync_lesson_to_post DONE' );
        
        // Skip flush_rewrite_rules on every save — it's expensive and can cause memory exhaustion on large sites.
        // Permalinks for lesson/course post types don't require flushing per-post. Run manually if needed.
        
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
            'post_type' => 'course',
            'post_date' => $collection->created_at,
            'post_modified' => current_time('mysql'),
            'post_name' => sanitize_title($collection->collection_title)
        );
        
        // Check if post_id exists and is valid (never use post_id=0 — causes save_post with null → fatal)
        $post_id = null;
        if ( ! empty( $collection->post_id ) ) {
            $existing = get_post( (int) $collection->post_id );
            if ( $existing instanceof WP_Post ) {
                $post_data['ID'] = (int) $collection->post_id;
            }
        }
        if ( isset( $post_data['ID'] ) ) {
            $existing = get_post( (int) $post_data['ID'] );
            if ( empty( $post_data['ID'] ) || ! $existing instanceof WP_Post ) {
                unset( $post_data['ID'] );
            }
        }
        if ( isset( $post_data['ID'] ) ) {
            $post_id = wp_update_post( $post_data, true );
        } else {
            // Check if a course post with this exact title already exists
            $existing_post = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT ID FROM {$this->wpdb->posts} WHERE post_title = %s AND post_type = 'course' LIMIT 1",
                stripslashes($collection->collection_title)
            ));
            
            if ($existing_post) {
                // Update the existing course post (validate before use)
                $existing = get_post( (int) $existing_post->ID );
                if ( $existing instanceof WP_Post ) {
                    $post_data['ID'] = (int) $existing_post->ID;
                    $post_id = wp_update_post( $post_data, true );
                } else {
                    $post_id = wp_insert_post( $post_data, true );
                }
            } else {
                // Create new post
                $post_id = wp_insert_post( $post_data, true );
            }
            
            if ( is_wp_error( $post_id ) || empty( $post_id ) ) {
                error_log( 'ALM Collection Post Sync failed: ' . ( is_wp_error( $post_id ) ? $post_id->get_error_message() : 'Empty post ID returned' ) );
                return false;
            }
            
            // Update ALM table with post_id
            $this->wpdb->update(
                $collections_table,
                array('post_id' => $post_id),
                array('ID' => $alm_collection_id),
                array('%d'),
                array('%d')
            );
        }
        
        if ( is_wp_error( $post_id ) || empty( $post_id ) ) {
            error_log( 'ALM Collection Post Sync failed: ' . ( is_wp_error( $post_id ) ? $post_id->get_error_message() : 'Empty post ID returned' ) );
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
        if (!empty($lesson->ID)) {
            update_post_meta($post_id, 'alm_lesson_id', $lesson->ID);
        } else {
            delete_post_meta($post_id, 'alm_lesson_id');
        }
        if (!empty($lesson->collection_id)) {
            update_post_meta($post_id, 'alm_collection_id', $lesson->collection_id);
        } else {
            delete_post_meta($post_id, 'alm_collection_id');
        }
        
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
        if (!empty($collection->ID)) {
            update_post_meta($post_id, 'alm_collection_id', $collection->ID);
        } else {
            delete_post_meta($post_id, 'alm_collection_id');
        }
        
        // Save course_id to meta (for backwards compatibility)
        if (!empty($collection->ID)) {
            update_post_meta($post_id, 'course_id', $collection->ID);
        } else {
            delete_post_meta($post_id, 'course_id');
        }
        
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
        
        if (!$post || $post->post_type !== 'course') {
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
