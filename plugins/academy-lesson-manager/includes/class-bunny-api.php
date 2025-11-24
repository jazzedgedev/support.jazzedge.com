<?php
/**
 * Bunny.net API Helper Class
 * 
 * Handles video metadata fetching from Bunny.net Stream API
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Bunny_API {
    
    /**
     * Bunny.net Stream API base URL
     */
    private $api_base_url = 'https://video.bunnycdn.com';
    
    /**
     * Library ID from settings
     */
    private $library_id;
    
    /**
     * API Key from settings
     */
    private $api_key;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Get settings from WordPress options
        $this->library_id = get_option('alm_bunny_library_id', '');
        $this->api_key = get_option('alm_bunny_api_key', '');
    }
    
    /**
     * Extract video ID from Bunny URL
     * 
     * @param string $bunny_url The Bunny.net URL
     * @return string|false Video ID or false if not found
     */
    public function extract_video_id_from_url($bunny_url) {
        // Handle different Bunny URL formats
        $patterns = [
            // Direct video URLs like: https://vz-0696d3da-4b7.b-cdn.net/31a57000-9ead-45b7-a972-943a5881f7f3/playlist.m3u8
            '/https:\/\/[^\/]+\/([a-f0-9\-]{36})\//',
            // Video library URLs
            '/\/videos\/([a-f0-9\-]{36})/',
            // Direct video ID
            '/^([a-f0-9\-]{36})$/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $bunny_url, $matches)) {
                return $matches[1];
            }
        }
        
        return false;
    }
    
    /**
     * Fetch video metadata from Bunny.net API
     * 
     * @param string $video_id The video ID
     * @return array|false Video metadata or false on error. On error, check error_log for details.
     */
    public function get_video_metadata($video_id) {
        if (empty($this->library_id) || empty($this->api_key)) {
            error_log("ALM Bunny API: Library ID or API key not set. Library ID: " . (empty($this->library_id) ? 'empty' : 'set') . ", API Key: " . (empty($this->api_key) ? 'empty' : 'set'));
            return false;
        }
        
        $url = $this->api_base_url . '/library/' . $this->library_id . '/videos/' . $video_id;
        
        $args = array(
            'headers' => array(
                'AccessKey' => $this->api_key,
                'accept' => 'application/json'
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            $error_msg = "ALM Bunny API: Error fetching metadata for video ID {$video_id} - " . $response->get_error_message();
            error_log($error_msg);
            error_log("ALM Bunny API: Request URL: {$url}");
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            $error_msg = "ALM Bunny API: Metadata request returned status {$status_code} for video ID {$video_id}";
            error_log($error_msg);
            error_log("ALM Bunny API: Request URL: {$url}");
            error_log("ALM Bunny API: Response body: " . substr($body, 0, 500));
            
            // Provide more specific error messages
            if ($status_code === 401) {
                error_log("ALM Bunny API: Authentication failed - check API key");
            } elseif ($status_code === 404) {
                error_log("ALM Bunny API: Video not found - check video ID: {$video_id}");
            } elseif ($status_code === 403) {
                error_log("ALM Bunny API: Access forbidden - check API key permissions");
            }
            
            return false;
        }
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("ALM Bunny API: JSON decode error for video ID {$video_id} - " . json_last_error_msg());
            error_log("ALM Bunny API: Response body: " . substr($body, 0, 500));
            return false;
        }
        
        return $data;
    }
    
    /**
     * Get video duration from Bunny URL
     * 
     * @param string $bunny_url The Bunny.net URL
     * @return int|false Duration in seconds or false on error
     */
    public function get_video_duration($bunny_url) {
        $video_id = $this->extract_video_id_from_url($bunny_url);
        
        if (!$video_id) {
            return false;
        }
        
        $metadata = $this->get_video_metadata($video_id);
        
        if (!$metadata || !isset($metadata['length'])) {
            return false;
        }
        
        return intval($metadata['length']);
    }
    
    /**
     * Get comprehensive video metadata from Bunny URL
     * 
     * @param string $bunny_url The Bunny.net URL
     * @return array|false Video metadata or false on error
     */
    public function get_video_info($bunny_url) {
        $video_id = $this->extract_video_id_from_url($bunny_url);
        
        if (!$video_id) {
            return false;
        }
        
        $metadata = $this->get_video_metadata($video_id);
        
        if (!$metadata) {
            return false;
        }
        
        // Return formatted data
        return array(
            'video_id' => $video_id,
            'duration' => isset($metadata['length']) ? intval($metadata['length']) : 0,
            'width' => isset($metadata['width']) ? intval($metadata['width']) : 0,
            'height' => isset($metadata['height']) ? intval($metadata['height']) : 0,
            'title' => isset($metadata['title']) ? $metadata['title'] : '',
            'description' => isset($metadata['description']) ? $metadata['description'] : '',
            'created_at' => isset($metadata['dateCreated']) ? $metadata['dateCreated'] : '',
            'updated_at' => isset($metadata['dateUpdated']) ? $metadata['dateUpdated'] : '',
            'status' => isset($metadata['status']) ? $metadata['status'] : '',
            'size' => isset($metadata['size']) ? intval($metadata['size']) : 0,
            'raw_metadata' => $metadata
        );
    }
    
    /**
     * Check if Bunny.net API is configured
     * 
     * @return bool True if configured, false otherwise
     */
    public function is_configured() {
        return !empty($this->library_id) && !empty($this->api_key);
    }
    
    /**
     * Test API connection
     * 
     * @return array Test result with success status and message
     */
    public function test_connection() {
        if (!$this->is_configured()) {
            return array(
                'success' => false,
                'message' => 'Bunny.net API not configured. Please set Library ID and API Key.'
            );
        }
        
        // Try to fetch library info as a test
        $url = $this->api_base_url . '/library/' . $this->library_id;
        
        $args = array(
            'headers' => array(
                'AccessKey' => $this->api_key,
                'accept' => 'application/json'
            ),
            'timeout' => 10
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Connection failed: ' . $response->get_error_message()
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code === 200) {
            return array(
                'success' => true,
                'message' => 'Connection successful!'
            );
        } else {
            // Enhanced error reporting
            $error_details = '';
            if ($code === 401) {
                $error_details = ' (Authentication failed - check your API key and Library ID)';
            } elseif ($code === 404) {
                $error_details = ' (Library not found - check your Library ID)';
            } elseif ($code === 403) {
                $error_details = ' (Access forbidden - check API key permissions)';
            }
            
            return array(
                'success' => false,
                'message' => 'Connection failed with HTTP code: ' . $code . $error_details . '. Response: ' . substr($body, 0, 200)
            );
        }
    }
    
    /**
     * Get API configuration status
     * 
     * @return array Configuration status
     */
    public function get_config_status() {
        return array(
            'library_id_set' => !empty($this->library_id),
            'api_key_set' => !empty($this->api_key),
            'configured' => $this->is_configured(),
            'library_id_value' => $this->library_id,
            'api_key_length' => strlen($this->api_key)
        );
    }
    
    /**
     * Debug method to check what's being sent to Bunny.net
     * 
     * @return array Debug information
     */
    public function debug_request() {
        if (!$this->is_configured()) {
            return array('error' => 'Not configured');
        }
        
        $url = $this->api_base_url . '/library/' . $this->library_id;
        
        return array(
            'url' => $url,
            'library_id' => $this->library_id,
            'api_key_length' => strlen($this->api_key),
            'api_key_start' => substr($this->api_key, 0, 8) . '...',
            'headers' => array(
                'AccessKey' => substr($this->api_key, 0, 8) . '...',
                'accept' => 'application/json'
            )
        );
    }
}
