<?php
/**
 * REST API Class for Chapter Transcription Manager
 * 
 * Handles REST API endpoints for embeddings data export (for migration)
 * Security: All endpoints require administrator authentication
 */

if (!defined('ABSPATH')) {
    exit;
}

class CT_REST_API {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        $namespace = 'chapter-transcription/v1';
        
        // Export embeddings data (for migration from local server)
        register_rest_route($namespace, '/embeddings/export', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_export_embeddings'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'batch_size' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 100,
                    'minimum' => 1,
                    'maximum' => 1000
                ),
                'offset' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 0,
                    'minimum' => 0
                ),
                'transcript_id' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 0
                )
            )
        ));
        
        // Get total count of embeddings (for migration progress tracking)
        register_rest_route($namespace, '/embeddings/count', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_embeddings_count'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
    }
    
    /**
     * Check if user has admin permission (for data migration endpoints)
     * Requires manage_options capability
     */
    public function check_admin_permission($request = null) {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'insufficient_permissions',
                'Administrator access required.',
                array('status' => 403)
            );
        }
        return true;
    }
    
    /**
     * Export embeddings data in batches
     * GET /wp-json/chapter-transcription/v1/embeddings/export?batch_size=100&offset=0
     */
    public function handle_export_embeddings($request) {
        global $wpdb;
        
        $embeddings_table = $wpdb->prefix . 'alm_transcript_embeddings';
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $embeddings_table
        ));
        
        if (!$table_exists) {
            return new WP_Error(
                'table_not_found',
                'Embeddings table does not exist.',
                array('status' => 404)
            );
        }
        
        $batch_size = absint($request->get_param('batch_size'));
        $offset = absint($request->get_param('offset'));
        $transcript_id = absint($request->get_param('transcript_id'));
        
        // Build query
        $where_clause = '';
        if ($transcript_id > 0) {
            $where_clause = $wpdb->prepare(" WHERE transcript_id = %d", $transcript_id);
        }
        
        // Get total count
        $total_count = $wpdb->get_var("SELECT COUNT(*) FROM {$embeddings_table}" . $where_clause);
        
        // Get batch of embeddings
        $query = "SELECT 
                    transcript_id,
                    segment_index,
                    embedding,
                    segment_text,
                    start_time,
                    end_time,
                    created_at
                  FROM {$embeddings_table}
                  {$where_clause}
                  ORDER BY transcript_id, segment_index
                  LIMIT %d OFFSET %d";
        
        $embeddings = $wpdb->get_results($wpdb->prepare($query, $batch_size, $offset), ARRAY_A);
        
        if ($wpdb->last_error) {
            return new WP_Error(
                'database_error',
                'Database error: ' . $wpdb->last_error,
                array('status' => 500)
            );
        }
        
        // Calculate if there are more batches
        $has_more = ($offset + count($embeddings)) < $total_count;
        
        return rest_ensure_response(array(
            'success' => true,
            'data' => $embeddings,
            'pagination' => array(
                'total' => (int) $total_count,
                'offset' => $offset,
                'batch_size' => $batch_size,
                'returned' => count($embeddings),
                'has_more' => $has_more
            ),
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Get total count of embeddings
     * GET /wp-json/chapter-transcription/v1/embeddings/count
     */
    public function handle_get_embeddings_count($request) {
        global $wpdb;
        
        $embeddings_table = $wpdb->prefix . 'alm_transcript_embeddings';
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $embeddings_table
        ));
        
        if (!$table_exists) {
            return new WP_Error(
                'table_not_found',
                'Embeddings table does not exist.',
                array('status' => 404)
            );
        }
        
        $total_count = $wpdb->get_var("SELECT COUNT(*) FROM {$embeddings_table}");
        
        // Get count by transcript_id for more detailed info
        $transcript_counts = $wpdb->get_results(
            "SELECT transcript_id, COUNT(*) as segment_count 
             FROM {$embeddings_table} 
             GROUP BY transcript_id 
             ORDER BY transcript_id",
            ARRAY_A
        );
        
        return rest_ensure_response(array(
            'success' => true,
            'total_count' => (int) $total_count,
            'unique_transcripts' => count($transcript_counts),
            'transcript_counts' => $transcript_counts,
            'timestamp' => current_time('mysql')
        ));
    }
}

