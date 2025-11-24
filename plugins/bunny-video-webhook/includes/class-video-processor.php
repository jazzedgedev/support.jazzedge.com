<?php
/**
 * Video Processor Class for Bunny Video Webhook
 * 
 * Handles parsing video filenames and updating the lesson database
 * 
 * @package Bunny_Video_Webhook
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Bunny_Video_Webhook_Processor {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Logger instance
     */
    private $logger;
    
    /**
     * Lessons table name
     */
    private $lessons_table;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->logger = new Bunny_Video_Webhook_Logger();
        $this->lessons_table = $wpdb->prefix . 'alm_lessons';
    }
    
    /**
     * Process video upload from webhook
     * 
     * @param array $webhook_data Webhook payload data
     * @return array|WP_Error Processing result
     */
    public function process_video($webhook_data) {
        // Extract video information from webhook
        // Bunny.net webhook sends VideoGuid, VideoLibraryId, and Status
        $video_id = isset($webhook_data['VideoGuid']) ? $webhook_data['VideoGuid'] : 
                   (isset($webhook_data['VideoId']) ? $webhook_data['VideoId'] : 
                   (isset($webhook_data['videoId']) ? $webhook_data['videoId'] : ''));
        
        $video_library_id = isset($webhook_data['VideoLibraryId']) ? $webhook_data['VideoLibraryId'] : '';
        
        // Try to get title/filename from webhook first
        $video_title = isset($webhook_data['Title']) ? $webhook_data['Title'] : (isset($webhook_data['title']) ? $webhook_data['title'] : '');
        $video_filename = isset($webhook_data['FileName']) ? $webhook_data['FileName'] : (isset($webhook_data['fileName']) ? $webhook_data['fileName'] : '');
        
        // If filename is not in webhook, try to get it from title
        if (empty($video_filename) && !empty($video_title)) {
            $video_filename = $video_title;
        }
        
        // If we still don't have filename and we have video_id, fetch from Bunny.net API
        if (empty($video_filename) && !empty($video_id)) {
            $video_metadata = $this->fetch_video_metadata($video_id, $video_library_id);
            if ($video_metadata && isset($video_metadata['title'])) {
                $video_filename = $video_metadata['title'];
                // Only log if we haven't logged this recently
                if (!$this->logger->has_recent_info_log($video_id, 'Fetched video title from Bunny.net API')) {
                    $this->logger->log_info('Fetched video title from Bunny.net API', array(
                        'video_id' => $video_id,
                        'video_filename' => $video_filename
                    ));
                }
            }
        }
        
        // If we still don't have a filename, log error (only if not logged recently)
        if (empty($video_filename)) {
            $error_msg = 'No filename found in webhook or API response';
            if (!$this->logger->has_recent_error_log($video_id, $error_msg)) {
                $this->logger->log_error($error_msg, array(
                    'video_id' => $video_id,
                    'webhook_data' => $webhook_data
                ));
            }
            return new WP_Error(
                'missing_filename',
                'Could not determine video filename from webhook or API',
                array('video_id' => $video_id, 'webhook_data' => $webhook_data)
            );
        }
        
        // Check if filename contains -sample
        if (strpos($video_filename, '-sample') === false) {
            // Only log if not logged recently
            if (!$this->logger->has_recent_info_log($video_id, 'Video skipped - does not contain -sample')) {
                $this->logger->log_info('Video skipped - does not contain -sample', array(
                    'video_id' => $video_id,
                    'video_filename' => $video_filename
                ));
            }
            return array(
                'success' => false,
                'message' => 'Video does not contain -sample in filename, skipping',
                'skipped' => true
            );
        }
        
        // Parse filename to extract lesson ID and chapter ID
        $parsed = $this->parse_filename($video_filename);
        
        if (is_wp_error($parsed)) {
            $error_msg = 'Failed to parse filename';
            // Only log if not logged recently
            if (!$this->logger->has_recent_error_log($video_id, $error_msg)) {
                $this->logger->log_error($error_msg, array(
                    'video_id' => $video_id,
                    'video_filename' => $video_filename,
                    'error_details' => $parsed->get_error_message(),
                    'webhook_data' => $webhook_data
                ));
            }
            return $parsed;
        }
        
        $lesson_id = $parsed['lesson_id'];
        $chapter_id = $parsed['chapter_id'];
        
        // Check if lesson already has this video URL set (prevent duplicate processing)
        $existing_lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT sample_video_url, sample_chapter_id FROM {$this->lessons_table} WHERE ID = %d",
            $lesson_id
        ));
        
        // Build HLS Playlist URL to compare
        $bunny_url = $this->build_hls_url($video_id, $webhook_data);
        
        if (is_wp_error($bunny_url)) {
            $error_msg = 'Failed to build HLS URL';
            if (!$this->logger->has_recent_error_log($video_id, $error_msg)) {
                $this->logger->log_error($error_msg, array(
                    'video_id' => $video_id,
                    'video_filename' => $video_filename,
                    'lesson_id' => $lesson_id,
                    'chapter_id' => $chapter_id,
                    'error_details' => $bunny_url->get_error_message(),
                    'webhook_data' => $webhook_data
                ));
            }
            return $bunny_url;
        }
        
        // If lesson already has this URL set, skip processing (already processed)
        if ($existing_lesson && !empty($existing_lesson->sample_video_url) && $existing_lesson->sample_video_url === $bunny_url) {
            // Check if we've already logged success for this video
            if (!$this->logger->has_success_log($video_id)) {
                // Log once that we're skipping because already processed
                $this->logger->log_info('Video already processed - lesson already has this URL', array(
                    'video_id' => $video_id,
                    'video_filename' => $video_filename,
                    'lesson_id' => $lesson_id,
                    'chapter_id' => $chapter_id,
                    'bunny_url' => $bunny_url
                ));
            }
            return array(
                'success' => true,
                'message' => 'Video already processed - lesson already has this URL',
                'already_processed' => true,
                'lesson_id' => $lesson_id,
                'chapter_id' => $chapter_id,
                'bunny_url' => $bunny_url
            );
        }
        
        // Update lesson database
        $result = $this->update_lesson($lesson_id, $chapter_id, $bunny_url, $video_id, $video_filename);
        
        if (is_wp_error($result)) {
            $error_msg = 'Failed to update lesson';
            if (!$this->logger->has_recent_error_log($video_id, $error_msg)) {
                $this->logger->log_error($error_msg, array(
                    'video_id' => $video_id,
                    'video_filename' => $video_filename,
                    'lesson_id' => $lesson_id,
                    'chapter_id' => $chapter_id,
                    'bunny_url' => $bunny_url,
                    'error_details' => $result->get_error_message(),
                    'webhook_data' => $webhook_data
                ));
            }
            return $result;
        }
        
        // Log success (only once per video_id)
        if (!$this->logger->has_success_log($video_id)) {
            $this->logger->log_success('Video processed and lesson updated successfully', array(
                'video_id' => $video_id,
                'video_filename' => $video_filename,
                'lesson_id' => $lesson_id,
                'chapter_id' => $chapter_id,
                'bunny_url' => $bunny_url
            ));
        }
        
        return array(
            'success' => true,
            'message' => 'Video processed successfully',
            'lesson_id' => $lesson_id,
            'chapter_id' => $chapter_id,
            'bunny_url' => $bunny_url
        );
    }
    
    /**
     * Parse filename to extract lesson ID and chapter ID
     * 
     * Format: 78-797-id797-Finding-Scale-Secrets-sample.mp4
     * First number (78) = lesson ID
     * Second number (797) = chapter ID
     * 
     * @param string $filename Video filename
     * @return array|WP_Error Parsed data with lesson_id and chapter_id
     */
    private function parse_filename($filename) {
        // Remove file extension
        $filename = preg_replace('/\.[^.]+$/', '', $filename);
        
        // Extract first two numbers from filename
        // Pattern: number-number-...
        if (preg_match('/^(\d+)-(\d+)/', $filename, $matches)) {
            $lesson_id = intval($matches[1]);
            $chapter_id = intval($matches[2]);
            
            if ($lesson_id > 0 && $chapter_id > 0) {
                return array(
                    'lesson_id' => $lesson_id,
                    'chapter_id' => $chapter_id
                );
            }
        }
        
        return new WP_Error(
            'invalid_filename',
            sprintf('Could not parse lesson ID and chapter ID from filename: %s', $filename),
            array('filename' => $filename)
        );
    }
    
    /**
     * Build HLS Playlist URL from video ID
     * 
     * @param string $video_id Bunny.net video ID
     * @param array $webhook_data Optional webhook data to extract CDN hostname from
     * @return string|WP_Error HLS URL or error
     */
    private function build_hls_url($video_id, $webhook_data = array()) {
        if (empty($video_id)) {
            return new WP_Error(
                'missing_video_id',
                'Video ID is required to build HLS URL'
            );
        }
        
        $cdn_hostname = '';
        
        // Try to extract CDN hostname from webhook data if URL is provided
        if (!empty($webhook_data)) {
            // Check for various URL fields in webhook
            $url_fields = array('VideoUrl', 'videoUrl', 'Url', 'url', 'HlsUrl', 'hlsUrl', 'PlaylistUrl', 'playlistUrl');
            foreach ($url_fields as $field) {
                if (isset($webhook_data[$field]) && !empty($webhook_data[$field])) {
                    $url = $webhook_data[$field];
                    // Extract hostname from URL
                    $parsed = parse_url($url);
                    if (isset($parsed['host'])) {
                        $cdn_hostname = $parsed['host'];
                        break;
                    }
                }
            }
        }
        
        // If not found in webhook, get from settings
        if (empty($cdn_hostname)) {
            $cdn_hostname = get_option('bunny_video_webhook_cdn_hostname', '');
        }
        
        // If still empty, use default
        if (empty($cdn_hostname)) {
            // Default: vz-0696d3da-4b7.b-cdn.net (this should be configured in settings)
            $cdn_hostname = 'vz-0696d3da-4b7.b-cdn.net';
        }
        
        // Build HLS URL
        $hls_url = sprintf('https://%s/%s/playlist.m3u8', $cdn_hostname, $video_id);
        
        return $hls_url;
    }
    
    /**
     * Fetch video metadata from Bunny.net API
     * 
     * @param string $video_id Video GUID
     * @param string|int $library_id Video Library ID (optional, will use from settings if not provided)
     * @return array|false Video metadata or false on error
     */
    private function fetch_video_metadata($video_id, $library_id = '') {
        // Get library ID and API key from settings
        // Check plugin-specific settings first, then fall back to ALM settings
        if (empty($library_id)) {
            $library_id = get_option('bunny_video_webhook_library_id', '');
            if (empty($library_id)) {
                $library_id = get_option('alm_bunny_library_id', '');
            }
        }
        
        $api_key = get_option('bunny_video_webhook_api_key', '');
        if (empty($api_key)) {
            $api_key = get_option('alm_bunny_api_key', '');
        }
        
        if (empty($library_id) || empty($api_key)) {
            $this->logger->log_error('Bunny.net API not configured - missing Library ID or API Key', array(
                'video_id' => $video_id,
                'library_id_set' => !empty($library_id),
                'api_key_set' => !empty($api_key)
            ));
            return false;
        }
        
        $api_base_url = 'https://video.bunnycdn.com';
        $url = $api_base_url . '/library/' . $library_id . '/videos/' . $video_id;
        
        $args = array(
            'headers' => array(
                'AccessKey' => $api_key,
                'accept' => 'application/json'
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            $this->logger->log_error('Error fetching video metadata from Bunny.net API', array(
                'video_id' => $video_id,
                'error' => $response->get_error_message()
            ));
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            $this->logger->log_error('Bunny.net API returned error status', array(
                'video_id' => $video_id,
                'status_code' => $status_code,
                'response_body' => substr($body, 0, 500)
            ));
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->log_error('Failed to parse JSON response from Bunny.net API', array(
                'video_id' => $video_id,
                'json_error' => json_last_error_msg()
            ));
            return false;
        }
        
        return $data;
    }
    
    /**
     * Update lesson in database
     * 
     * @param int $lesson_id Lesson ID
     * @param int $chapter_id Chapter ID
     * @param string $bunny_url HLS Playlist URL
     * @param string $video_id Video ID for logging
     * @param string $video_filename Video filename for logging
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    private function update_lesson($lesson_id, $chapter_id, $bunny_url, $video_id, $video_filename) {
        // Check if lesson exists
        $lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID FROM {$this->lessons_table} WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            return new WP_Error(
                'lesson_not_found',
                sprintf('Lesson with ID %d not found in database', $lesson_id),
                array('lesson_id' => $lesson_id)
            );
        }
        
        // Update lesson with sample video URL and chapter ID
        $result = $this->wpdb->update(
            $this->lessons_table,
            array(
                'sample_video_url' => $bunny_url,
                'sample_chapter_id' => $chapter_id
            ),
            array('ID' => $lesson_id),
            array('%s', '%d'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error(
                'database_error',
                sprintf('Failed to update lesson %d in database', $lesson_id),
                array('lesson_id' => $lesson_id)
            );
        }
        
        return true;
    }
}

