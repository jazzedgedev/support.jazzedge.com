<?php
/**
 * Embedding Search Class for Academy AI Assistant
 * 
 * Searches transcript embeddings using cosine similarity for semantic search
 * Security: Read-only access, uses prepared statements
 * Performance: Limits results, uses indexes
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Embedding_Search {
    
    private $embeddings_table;
    private $transcripts_table;
    private $cache_group = 'aaa_embedding_search';
    private $cache_duration = 3600; // 1 hour (embeddings don't change often)
    private $debug_logger;
    
    public function __construct($debug_logger = null) {
        global $wpdb;
        $this->embeddings_table = $wpdb->prefix . 'alm_transcript_embeddings';
        $this->transcripts_table = $wpdb->prefix . 'alm_transcripts';
        $this->debug_logger = $debug_logger;
    }
    
    /**
     * Check if embedding search is available
     * 
     * @return bool True if tables exist
     */
    public function is_available() {
        global $wpdb;
        
        $embeddings_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $this->embeddings_table)) == $this->embeddings_table;
        $transcripts_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $this->transcripts_table)) == $this->transcripts_table;
        
        return $embeddings_exists && $transcripts_exists;
    }
    
    /**
     * Search embeddings using cosine similarity
     * 
     * @param string $query_text Search query
     * @param int $limit Number of results to return (max 20)
     * @param float $min_similarity Minimum similarity score (0.0 to 1.0)
     * @return array Array of matching segments with metadata
     */
    public function search($query_text, $limit = 10, $min_similarity = 0.5) {
        // Safety: Check if tables exist
        if (!$this->is_available()) {
            return array();
        }
        
        // Performance: Check cache first
        $cache_key = 'search_' . md5($query_text . $limit . $min_similarity);
        $cached = wp_cache_get($cache_key, $this->cache_group);
        if ($cached !== false) {
            return $cached;
        }
        
        // Get query embedding from Katahdin AI Hub
        $query_embedding = $this->get_query_embedding($query_text);
        if (!$query_embedding || !is_array($query_embedding)) {
            return array();
        }
        
        // Search embeddings
        $results = $this->search_embeddings($query_embedding, $limit, $min_similarity);
        
        // Performance: Cache results
        wp_cache_set($cache_key, $results, $this->cache_group, $this->cache_duration);
        
        return $results;
    }
    
    /**
     * Get embedding for search query
     * 
     * @param string $text Text to embed
     * @return array|false Embedding vector or false on failure
     */
    private function get_query_embedding($text) {
        // Check if Katahdin AI Hub is available
        if (!function_exists('katahdin_ai_hub')) {
            return false;
        }
        
        $hub = katahdin_ai_hub();
        if (!$hub || !isset($hub->api_manager)) {
            return false;
        }
        
        // Truncate text if too long (OpenAI limit)
        $max_length = 30000;
        if (strlen($text) > $max_length) {
            $text = substr($text, 0, $max_length);
        }
        
        // Make API call through hub
        // Use the same pattern as chapter-transcription plugin
        $result = $hub->make_api_call(
            AAA_PLUGIN_ID,
            'embeddings',
            array(
                'input' => $text
            ),
            array(
                'model' => 'text-embedding-3-small'
            )
        );
        
        if (is_wp_error($result)) {
            error_log('Academy AI Assistant: Embedding search failed: ' . $result->get_error_message());
            return false;
        }
        
        // Extract embedding from response
        if (isset($result['data'][0]['embedding'])) {
            return $result['data'][0]['embedding'];
        }
        
        return false;
    }
    
    /**
     * Search embeddings using cosine similarity
     * 
     * @param array $query_embedding Query embedding vector
     * @param int $limit Number of results
     * @param float $min_similarity Minimum similarity
     * @return array Search results
     */
    private function search_embeddings($query_embedding, $limit = 10, $min_similarity = 0.7) {
        global $wpdb;
        
        // Performance: Limit results
        $limit = min(absint($limit), 20); // Max 20
        
        // Get all embeddings (we'll calculate similarity in PHP)
        // Performance: For large datasets, consider limiting or using a vector database
        // For now, we'll fetch a reasonable number and calculate similarity
        $max_embeddings = 5000; // Limit to prevent memory issues
        $sql = $wpdb->prepare(
            "SELECT transcript_id, segment_index, embedding, segment_text, start_time, end_time 
             FROM {$this->embeddings_table} 
             LIMIT %d",
            $max_embeddings
        );
        
        // Log SQL query for debugging
        if ($this->debug_logger && $this->debug_logger->is_enabled()) {
            // Build actual SQL with values
            $sql_log = "SELECT transcript_id, segment_index, embedding, segment_text, start_time, end_time 
                        FROM {$this->embeddings_table} 
                        LIMIT " . intval($max_embeddings);
            
            $this->debug_logger->log('embedding_search_sql', 'Embedding search SQL query', array(
                'sql' => $sql_log,
                'table' => $this->embeddings_table,
                'max_embeddings' => $max_embeddings,
                'similarity_threshold' => $min_similarity,
                'note' => 'Similarity calculation done in PHP after fetching embeddings'
            ));
        }
        
        $embeddings = $wpdb->get_results($sql, ARRAY_A);
        
        if (empty($embeddings)) {
            return array();
        }
        
        // Calculate cosine similarity for each embedding
        $similarities = array();
        foreach ($embeddings as $embedding_row) {
            $stored_embedding = json_decode($embedding_row['embedding'], true);
            
            if (!is_array($stored_embedding) || count($stored_embedding) !== count($query_embedding)) {
                continue; // Skip invalid embeddings
            }
            
            $similarity = $this->cosine_similarity($query_embedding, $stored_embedding);
            
            if ($similarity >= $min_similarity) {
                $similarities[] = array(
                    'similarity' => $similarity,
                    'transcript_id' => $embedding_row['transcript_id'],
                    'segment_index' => $embedding_row['segment_index'],
                    'segment_text' => $embedding_row['segment_text'],
                    'start_time' => floatval($embedding_row['start_time']),
                    'end_time' => floatval($embedding_row['end_time'])
                );
            }
        }
        
        // Sort by similarity (highest first)
        usort($similarities, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        // Limit results
        $results = array_slice($similarities, 0, $limit);
        
        // Enrich with lesson/chapter information
        return $this->enrich_results($results);
    }
    
    /**
     * Calculate cosine similarity between two vectors
     * 
     * @param array $vector_a First vector
     * @param array $vector_b Second vector
     * @return float Similarity score (0.0 to 1.0)
     */
    private function cosine_similarity($vector_a, $vector_b) {
        if (count($vector_a) !== count($vector_b)) {
            return 0.0;
        }
        
        $dot_product = 0.0;
        $magnitude_a = 0.0;
        $magnitude_b = 0.0;
        
        for ($i = 0; $i < count($vector_a); $i++) {
            $dot_product += $vector_a[$i] * $vector_b[$i];
            $magnitude_a += $vector_a[$i] * $vector_a[$i];
            $magnitude_b += $vector_b[$i] * $vector_b[$i];
        }
        
        $magnitude_a = sqrt($magnitude_a);
        $magnitude_b = sqrt($magnitude_b);
        
        if ($magnitude_a == 0 || $magnitude_b == 0) {
            return 0.0;
        }
        
        return $dot_product / ($magnitude_a * $magnitude_b);
    }
    
    /**
     * Enrich search results with lesson/chapter information
     * 
     * @param array $results Raw search results
     * @return array Enriched results with lesson URLs
     */
    private function enrich_results($results) {
        global $wpdb;
        
        if (empty($results)) {
            return array();
        }
        
        // Get unique transcript IDs
        $transcript_ids = array_unique(array_column($results, 'transcript_id'));
        
        // Get transcript and chapter information (including chapter slug)
        $transcript_ids_sql = implode(',', array_map('absint', $transcript_ids));
        $sql = "SELECT t.ID, t.lesson_id, t.chapter_id, t.vtt_file,
                    c.chapter_title, c.lesson_id as chapter_lesson_id, c.slug as chapter_slug,
                    l.lesson_title, l.slug, l.post_id
                FROM {$this->transcripts_table} t
                LEFT JOIN {$wpdb->prefix}alm_chapters c ON t.chapter_id = c.ID
                LEFT JOIN {$wpdb->prefix}alm_lessons l ON (t.lesson_id = l.ID OR c.lesson_id = l.ID)
                WHERE t.ID IN ({$transcript_ids_sql})";
        
        // Log SQL query for debugging
        if ($this->debug_logger && $this->debug_logger->is_enabled()) {
            // Build actual SQL with values for logging
            $sql_log = "SELECT t.ID, t.lesson_id, t.chapter_id, t.vtt_file,
                    c.chapter_title, c.lesson_id as chapter_lesson_id, c.slug as chapter_slug,
                    l.lesson_title, l.slug, l.post_id
                FROM {$this->transcripts_table} t
                LEFT JOIN {$wpdb->prefix}alm_chapters c ON t.chapter_id = c.ID
                LEFT JOIN {$wpdb->prefix}alm_lessons l ON (t.lesson_id = l.ID OR c.lesson_id = l.ID)
                WHERE t.ID IN ({$transcript_ids_sql})";
            
            $this->debug_logger->log('embedding_enrich_sql', 'Enriching embedding results with lesson/chapter data', array(
                'sql' => $sql_log,
                'transcript_ids' => $transcript_ids,
                'transcripts_table' => $this->transcripts_table,
                'chapters_table' => $wpdb->prefix . 'alm_chapters',
                'lessons_table' => $wpdb->prefix . 'alm_lessons'
            ));
        }
        
        $transcripts = $wpdb->get_results($sql, ARRAY_A);
        
        // Create lookup map
        $transcript_map = array();
        foreach ($transcripts as $transcript) {
            $transcript_map[$transcript['ID']] = $transcript;
        }
        
        // Enrich results
        foreach ($results as &$result) {
            $transcript_id = $result['transcript_id'];
            
            if (isset($transcript_map[$transcript_id])) {
                $transcript_info = $transcript_map[$transcript_id];
                
                $result['lesson_id'] = $transcript_info['lesson_id'] ?: $transcript_info['chapter_lesson_id'];
                $result['chapter_id'] = $transcript_info['chapter_id'];
                $result['lesson_title'] = $transcript_info['lesson_title'];
                $result['chapter_title'] = $transcript_info['chapter_title'];
                $result['chapter_slug'] = !empty($transcript_info['chapter_slug']) ? $transcript_info['chapter_slug'] : '';
                
                // Build lesson URL - ensure it's from jazzedge.academy domain
                // Get lesson_id first (prefer direct lesson_id, fallback to chapter_lesson_id)
                $lesson_id = !empty($transcript_info['lesson_id']) ? absint($transcript_info['lesson_id']) : (!empty($transcript_info['chapter_lesson_id']) ? absint($transcript_info['chapter_lesson_id']) : 0);
                $result['lesson_url'] = '';
                
                // Method 1: Try to get post_id from ALM lessons table directly (most reliable)
                if ($lesson_id && class_exists('ALM_Database')) {
                    $alm_db = new ALM_Database();
                    $lessons_table = $alm_db->get_table_name('lessons');
                    
                    $lesson = $wpdb->get_row($wpdb->prepare(
                        "SELECT post_id, slug FROM {$lessons_table} WHERE ID = %d",
                        $lesson_id
                    ));
                    
                    if ($lesson) {
                        // Try post_id first
                        if (!empty($lesson->post_id)) {
                            $post_id = absint($lesson->post_id);
                            $post = get_post($post_id);
                            
                            // Verify post exists and is published
                            if ($post && in_array($post->post_status, array('publish', 'private'))) {
                                // Use get_permalink which respects custom post type rewrite rules
                                $permalink = get_permalink($post_id);
                                
                                if ($permalink && !is_wp_error($permalink)) {
                                    // Ensure absolute URL
                                    if (strpos($permalink, 'http') !== 0) {
                                        $result['lesson_url'] = home_url($permalink);
                                    } else {
                                        $result['lesson_url'] = $permalink;
                                    }
                                    
                                    // Force jazzedge.academy domain
                                    $result['lesson_url'] = str_replace(
                                        array('www.pianovideolessons.com', 'pianovideolessons.com'),
                                        'jazzedge.academy',
                                        $result['lesson_url']
                                    );
                                    
                                    // Debug: Log the generated URL
                                    if (defined('WP_DEBUG') && WP_DEBUG) {
                                        error_log("Academy AI Assistant: Generated lesson URL for lesson_id {$lesson_id}, post_id {$post_id}: {$result['lesson_url']}");
                                    }
                                } else {
                                    // If get_permalink failed, log it
                                    if (defined('WP_DEBUG') && WP_DEBUG) {
                                        error_log("Academy AI Assistant: get_permalink failed for post_id {$post_id}, lesson_id {$lesson_id}");
                                    }
                                }
                            } else {
                                // Post doesn't exist or isn't published
                                if (defined('WP_DEBUG') && WP_DEBUG) {
                                    $status = $post ? $post->post_status : 'not found';
                                    error_log("Academy AI Assistant: Post {$post_id} for lesson_id {$lesson_id} has status: {$status}");
                                }
                            }
                        }
                        
                        // Fallback to slug if post_id didn't work
                        if (empty($result['lesson_url']) && !empty($lesson->slug)) {
                            // Try different URL structures
                            $slug = sanitize_title($lesson->slug);
                            
                            // Try singular 'lesson' first (most common)
                            $result['lesson_url'] = home_url('/lesson/' . $slug . '/');
                            
                            // If that doesn't work, the actual URL might be different
                            // But we'll let get_permalink handle it if we have post_id
                        }
                    }
                }
                
                // Method 2: Fallback to post_id from transcript join (if method 1 failed)
                if (empty($result['lesson_url']) && !empty($transcript_info['post_id'])) {
                    $post_id = absint($transcript_info['post_id']);
                    $post = get_post($post_id);
                    
                    if ($post && in_array($post->post_status, array('publish', 'private'))) {
                        $permalink = get_permalink($post_id);
                        if ($permalink && !is_wp_error($permalink)) {
                            if (strpos($permalink, 'http') !== 0) {
                                $result['lesson_url'] = home_url($permalink);
                            } else {
                                $result['lesson_url'] = $permalink;
                            }
                            $result['lesson_url'] = str_replace(
                                array('www.pianovideolessons.com', 'pianovideolessons.com'),
                                'jazzedge.academy',
                                $result['lesson_url']
                            );
                        }
                    }
                }
                
                // Method 3: Last resort - use slug from transcript info
                if (empty($result['lesson_url']) && !empty($transcript_info['slug'])) {
                    $slug = sanitize_title($transcript_info['slug']);
                    $result['lesson_url'] = home_url('/lesson/' . $slug . '/');
                }
                
                // Build chapter-specific URL if we have chapter slug
                // Format: lesson_url?c=chapter-slug
                if (!empty($result['chapter_slug']) && !empty($result['lesson_url'])) {
                    // Use chapter parameter format: ?c=chapter-slug
                    $result['timestamp_url'] = add_query_arg('c', $result['chapter_slug'], $result['lesson_url']);
                } else if ($result['start_time'] > 0 && !empty($result['lesson_url'])) {
                    // Fallback to timestamp if no chapter slug
                    $result['timestamp_url'] = $result['lesson_url'] . '#t=' . intval($result['start_time']);
                } else {
                    $result['timestamp_url'] = $result['lesson_url'];
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Get statistics about available embeddings
     * 
     * @return array Statistics
     */
    public function get_statistics() {
        if (!$this->is_available()) {
            return array(
                'available' => false,
                'total_embeddings' => 0,
                'total_transcripts' => 0
            );
        }
        
        global $wpdb;
        
        $total_embeddings = $wpdb->get_var("SELECT COUNT(*) FROM {$this->embeddings_table}");
        $total_transcripts = $wpdb->get_var(
            "SELECT COUNT(DISTINCT transcript_id) FROM {$this->embeddings_table}"
        );
        
        return array(
            'available' => true,
            'total_embeddings' => intval($total_embeddings),
            'total_transcripts' => intval($total_transcripts)
        );
    }
}

