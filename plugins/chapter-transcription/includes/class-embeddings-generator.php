<?php
/**
 * Embeddings Generator Class
 * 
 * Generates vector embeddings for transcript segments using Katahdin AI Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

class Transcription_Embeddings_Generator {
    
    private $plugin_id = 'chapter-transcription';
    private $model = 'text-embedding-3-small'; // or 'text-embedding-3-large' for better quality
    
    /**
     * Generate embedding for a single text segment
     * 
     * @param string $text Text to embed
     * @param int $max_retries Maximum number of retries
     * @return array|WP_Error Embedding vector or error
     */
    public function generate_embedding($text, $max_retries = 3) {
        // Check if Katahdin AI Hub is available
        if (!function_exists('katahdin_ai_hub')) {
            return new WP_Error('katahdin_not_available', 'Katahdin AI Hub is not available');
        }
        
        // Ensure plugin is registered
        $this->ensure_plugin_registered();
        
        // Truncate text if too long (OpenAI has token limits)
        // text-embedding-3-small supports up to 8191 tokens (~32,000 characters)
        // We'll use a safe limit of 30,000 characters
        $max_length = 30000;
        if (strlen($text) > $max_length) {
            $text = substr($text, 0, $max_length);
            error_log("Embedding: Text truncated to {$max_length} characters");
        }
        
        // Retry logic with exponential backoff
        $last_error = null;
        for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
            // Make API call through Katahdin AI Hub
            $result = katahdin_ai_hub()->make_api_call(
                $this->plugin_id,
                'embeddings',
                array(
                    'input' => $text
                ),
                array(
                    'model' => $this->model
                )
            );
            
            if (is_wp_error($result)) {
                $last_error = $result;
                $error_code = $result->get_error_code();
                $error_message = $result->get_error_message();
                
                // Check if it's a rate limit error
                if (strpos($error_message, 'rate limit') !== false || 
                    strpos($error_code, 'rate_limit') !== false ||
                    strpos($error_message, '429') !== false) {
                    // Exponential backoff for rate limits
                    $wait_time = pow(2, $attempt) * 2; // 4s, 8s, 16s
                    error_log("Embedding: Rate limit hit, waiting {$wait_time}s before retry {$attempt}/{$max_retries}");
                    sleep($wait_time);
                    continue;
                }
                
                // Check if it's a transient error (5xx)
                if (strpos($error_message, '502') !== false || 
                    strpos($error_message, '503') !== false || 
                    strpos($error_message, '504') !== false) {
                    $wait_time = pow(2, $attempt); // 2s, 4s, 8s
                    error_log("Embedding: Transient error, waiting {$wait_time}s before retry {$attempt}/{$max_retries}");
                    sleep($wait_time);
                    continue;
                }
                
                // For other errors, don't retry
                return $result;
            }
            
            // Extract embedding vector from response
            if (isset($result['data'][0]['embedding'])) {
                return $result['data'][0]['embedding'];
            }
            
            // Invalid response - retry
            $last_error = new WP_Error('invalid_response', 'Invalid response from embeddings API');
            if ($attempt < $max_retries) {
                sleep(1);
                continue;
            }
        }
        
        // All retries failed
        return $last_error ? $last_error : new WP_Error('max_retries_exceeded', 'Max retries exceeded');
    }
    
    /**
     * Generate embeddings for all segments in a transcript
     * 
     * @param int $transcript_id Transcript ID
     * @param callable $progress_callback Optional callback for progress updates (segment_index, total_segments, status)
     * @return array Result with success count and errors
     */
    public function generate_embeddings_for_transcript($transcript_id, $progress_callback = null) {
        global $wpdb;
        
        $transcripts_table = $wpdb->prefix . 'alm_transcripts';
        $embeddings_table = $wpdb->prefix . 'alm_transcript_embeddings';
        
        // Get transcript with segments
        $transcript = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, chapter_id, vtt_segments FROM {$transcripts_table} WHERE ID = %d",
            $transcript_id
        ));
        
        if (!$transcript) {
            return array(
                'success' => false,
                'message' => 'Transcript not found',
                'processed' => 0,
                'errors' => array()
            );
        }
        
        if (empty($transcript->vtt_segments)) {
            return array(
                'success' => false,
                'message' => 'No segments found in transcript',
                'processed' => 0,
                'errors' => array()
            );
        }
        
        $segments = json_decode($transcript->vtt_segments, true);
        if (!is_array($segments) || empty($segments)) {
            return array(
                'success' => false,
                'message' => 'Invalid segments data',
                'processed' => 0,
                'errors' => array()
            );
        }
        
        // Check if embeddings already exist
        $existing_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$embeddings_table} WHERE transcript_id = %d",
            $transcript_id
        ));
        
        if ($existing_count > 0) {
            // Skip if embeddings already exist (can add option to regenerate later)
            return array(
                'success' => true,
                'message' => 'Embeddings already exist for this transcript',
                'processed' => $existing_count,
                'skipped' => true
            );
        }
        
        $total_segments = count($segments);
        $processed = 0;
        $success = 0;
        $failed = 0;
        $errors = array();
        
        // Call progress callback at start
        if ($progress_callback) {
            call_user_func($progress_callback, 0, $total_segments, 'Starting...');
        }
        
        foreach ($segments as $index => $segment) {
            if (!isset($segment['text']) || empty(trim($segment['text']))) {
                continue; // Skip empty segments
            }
            
            $text = trim($segment['text']);
            $start_time = isset($segment['start']) ? floatval($segment['start']) : null;
            $end_time = isset($segment['end']) ? floatval($segment['end']) : null;
            
            // Update progress: generating embedding
            if ($progress_callback) {
                call_user_func($progress_callback, $index + 1, $total_segments, "Generating embedding for segment " . ($index + 1) . "/{$total_segments}...");
            }
            
            // Generate embedding
            $embedding = $this->generate_embedding($text);
            
            if (is_wp_error($embedding)) {
                $failed++;
                $error_msg = $embedding->get_error_message();
                $error_code = $embedding->get_error_code();
                
                // Log detailed error
                error_log("Embedding failed for transcript {$transcript_id}, segment {$index}: [{$error_code}] {$error_msg}");
                
                $errors[] = "Segment {$index}: " . $error_msg . " (code: {$error_code})";
                
                // Update progress: error
                if ($progress_callback) {
                    call_user_func($progress_callback, $index + 1, $total_segments, "Error: {$error_msg}");
                }
                
                // If rate limited, add longer delay before next segment
                if (strpos($error_msg, 'rate limit') !== false || strpos($error_code, 'rate_limit') !== false) {
                    if ($progress_callback) {
                        call_user_func($progress_callback, $index + 1, $total_segments, "Rate limited, waiting 5 seconds...");
                    }
                    sleep(5); // Wait 5 seconds before continuing
                }
                
                continue;
            }
            
            // Update progress: storing embedding
            if ($progress_callback) {
                call_user_func($progress_callback, $index + 1, $total_segments, "Storing embedding...");
            }
            
            // Store embedding
            $embedding_json = json_encode($embedding);
            
            $result = $wpdb->insert(
                $embeddings_table,
                array(
                    'transcript_id' => $transcript_id,
                    'segment_index' => $index,
                    'embedding' => $embedding_json,
                    'segment_text' => $text,
                    'start_time' => $start_time,
                    'end_time' => $end_time
                ),
                array('%d', '%d', '%s', '%s', '%f', '%f')
            );
            
            if ($result !== false) {
                $success++;
            } else {
                $failed++;
                $errors[] = "Segment {$index}: Database insert failed - " . $wpdb->last_error;
            }
            
            $processed++;
            
            // Update progress: completed segment
            if ($progress_callback) {
                call_user_func($progress_callback, $index + 1, $total_segments, "Segment " . ($index + 1) . " complete ({$success} success, {$failed} failed)");
            }
            
            // Delay to avoid rate limiting (increased for safety)
            usleep(200000); // 0.2 second delay between API calls
        }
        
        // Update progress: complete
        if ($progress_callback) {
            call_user_func($progress_callback, $total_segments, $total_segments, "Complete: {$success} success, {$failed} failed");
        }
        
        // If we had failures, log summary
        if ($failed > 0) {
            error_log("Embeddings generation for transcript {$transcript_id}: {$success} success, {$failed} failed out of {$processed} processed");
        }
        
        return array(
            'success' => $failed === 0,
            'message' => "Processed {$processed} segments: {$success} success, {$failed} failed",
            'processed' => $processed,
            'success_count' => $success,
            'failed_count' => $failed,
            'errors' => array_slice($errors, 0, 10) // Limit errors
        );
    }
    
    /**
     * Ensure plugin is registered with Katahdin AI Hub
     */
    private function ensure_plugin_registered() {
        if (!function_exists('katahdin_ai_hub')) {
            return;
        }
        
        $registry = katahdin_ai_hub()->plugin_registry;
        
        if (!$registry->is_registered($this->plugin_id)) {
            // Register the plugin
            $registry->register($this->plugin_id, array(
                'name' => 'Chapter Transcription',
                'description' => 'Generates embeddings for transcript segments',
                'features' => array('embeddings'),
                'quota_limit' => 0, // Unlimited (or set a limit)
                'quota_period' => 'monthly'
            ));
        }
    }
    
    /**
     * Get statistics about embeddings
     * 
     * @return array Statistics
     */
    public function get_statistics() {
        global $wpdb;
        
        $transcripts_table = $wpdb->prefix . 'alm_transcripts';
        $embeddings_table = $wpdb->prefix . 'alm_transcript_embeddings';
        
        $total_transcripts = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$transcripts_table} 
             WHERE vtt_segments IS NOT NULL AND vtt_segments != ''"
        );
        
        $transcripts_with_embeddings = $wpdb->get_var(
            "SELECT COUNT(DISTINCT transcript_id) FROM {$embeddings_table}"
        );
        
        $total_embeddings = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$embeddings_table}"
        );
        
        $total_segments = $wpdb->get_var(
            "SELECT SUM(JSON_LENGTH(vtt_segments)) FROM {$transcripts_table} 
             WHERE vtt_segments IS NOT NULL AND vtt_segments != ''"
        );
        
        // Count transcripts with partial embeddings (some segments failed)
        $transcripts_with_partial = $wpdb->get_var(
            "SELECT COUNT(DISTINCT t.ID) 
             FROM {$transcripts_table} t
             WHERE t.vtt_segments IS NOT NULL 
             AND t.vtt_segments != ''
             AND EXISTS (SELECT 1 FROM {$embeddings_table} e WHERE e.transcript_id = t.ID)
             AND (SELECT COUNT(*) FROM {$embeddings_table} e2 WHERE e2.transcript_id = t.ID) < 
                 JSON_LENGTH(t.vtt_segments)"
        );
        
        return array(
            'total_transcripts' => intval($total_transcripts),
            'transcripts_with_embeddings' => intval($transcripts_with_embeddings),
            'transcripts_with_partial' => intval($transcripts_with_partial),
            'total_embeddings' => intval($total_embeddings),
            'total_segments' => intval($total_segments),
            'needs_embeddings' => intval($total_transcripts) - intval($transcripts_with_embeddings)
        );
    }
    
    /**
     * Retry failed segments for a transcript (regenerate only missing embeddings)
     * 
     * @param int $transcript_id Transcript ID
     * @return array Result
     */
    public function retry_failed_segments($transcript_id) {
        global $wpdb;
        
        $transcripts_table = $wpdb->prefix . 'alm_transcripts';
        $embeddings_table = $wpdb->prefix . 'alm_transcript_embeddings';
        
        // Get transcript with segments
        $transcript = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, chapter_id, vtt_segments FROM {$transcripts_table} WHERE ID = %d",
            $transcript_id
        ));
        
        if (!$transcript || empty($transcript->vtt_segments)) {
            return array(
                'success' => false,
                'message' => 'Transcript or segments not found'
            );
        }
        
        $segments = json_decode($transcript->vtt_segments, true);
        if (!is_array($segments) || empty($segments)) {
            return array(
                'success' => false,
                'message' => 'Invalid segments data'
            );
        }
        
        // Get existing embeddings
        $existing = $wpdb->get_results($wpdb->prepare(
            "SELECT segment_index FROM {$embeddings_table} WHERE transcript_id = %d",
            $transcript_id
        ), ARRAY_A);
        
        $existing_indices = array();
        foreach ($existing as $row) {
            $existing_indices[] = intval($row['segment_index']);
        }
        
        $processed = 0;
        $success = 0;
        $failed = 0;
        $errors = array();
        
        // Only process segments that don't have embeddings
        foreach ($segments as $index => $segment) {
            if (in_array($index, $existing_indices)) {
                continue; // Skip if embedding already exists
            }
            
            if (!isset($segment['text']) || empty(trim($segment['text']))) {
                continue;
            }
            
            $text = trim($segment['text']);
            $start_time = isset($segment['start']) ? floatval($segment['start']) : null;
            $end_time = isset($segment['end']) ? floatval($segment['end']) : null;
            
            // Generate embedding
            $embedding = $this->generate_embedding($text);
            
            if (is_wp_error($embedding)) {
                $failed++;
                $error_msg = $embedding->get_error_message();
                $error_code = $embedding->get_error_code();
                $errors[] = "Segment {$index}: " . $error_msg . " (code: {$error_code})";
                
                if (strpos($error_msg, 'rate limit') !== false) {
                    sleep(5);
                }
                continue;
            }
            
            // Store embedding
            $embedding_json = json_encode($embedding);
            
            $result = $wpdb->insert(
                $embeddings_table,
                array(
                    'transcript_id' => $transcript_id,
                    'segment_index' => $index,
                    'embedding' => $embedding_json,
                    'segment_text' => $text,
                    'start_time' => $start_time,
                    'end_time' => $end_time
                ),
                array('%d', '%d', '%s', '%s', '%f', '%f')
            );
            
            if ($result !== false) {
                $success++;
            } else {
                $failed++;
                $errors[] = "Segment {$index}: Database insert failed";
            }
            
            $processed++;
            usleep(200000); // 0.2 second delay
        }
        
        return array(
            'success' => $failed === 0,
            'message' => "Retried {$processed} segments: {$success} success, {$failed} failed",
            'processed' => $processed,
            'success_count' => $success,
            'failed_count' => $failed,
            'errors' => array_slice($errors, 0, 10)
        );
    }
}

