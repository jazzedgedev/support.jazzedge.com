<?php
/**
 * Vimeo API Helper Class
 * 
 * Handles video metadata fetching from Vimeo API using Vimeo PHP library
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Vimeo_API {
    
    /**
     * Vimeo API base URL
     */
    private $api_base_url = 'https://api.vimeo.com';
    
    /**
     * Vimeo client (if library is available)
     */
    private $vimeo_client = null;
    
    /**
     * Whether Vimeo PHP library is available
     */
    private $library_available = false;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Try to load Vimeo PHP library (same path as in oxygen-functions.php)
        // Try multiple possible paths
        $possible_paths = array(
            ABSPATH . '../willie/ja-admin/vimeo.php-3.0.2/src/Vimeo/Vimeo.php',
            '/nas/content/live/jazzacademy/willie/ja-admin/vimeo.php-3.0.2/src/Vimeo/Vimeo.php',
            dirname(ABSPATH) . '/willie/ja-admin/vimeo.php-3.0.2/src/Vimeo/Vimeo.php'
        );
        
        $vimeo_library_path = null;
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                $vimeo_library_path = $path;
                break;
            }
        }
        
        if ($vimeo_library_path) {
            require_once($vimeo_library_path);
            
            if (class_exists('Vimeo\Vimeo')) {
                $this->library_available = true;
                
                // Use same credentials as in oxygen-functions.php
                $client_id = "4b8aa7cfcc3ca72c070d952629bfc5061c459f37";
                $client_secret = "dUbGlWUkDSZoyPce8ehdnfoxDpAwGeoU5uuxEg0ecrESqGLh7taUehQOZqk8bL3a22xqA2vxt3cvekLIxe39/AhNpokavpKsmDJlr671roBVGCTPG5aDnoBEauCCJDIH";
                $access_token = "7d303a30c260a569f0ea69cb01265f9b";
                
                try {
                    $this->vimeo_client = new \Vimeo\Vimeo($client_id, $client_secret, $access_token);
                    error_log("ALM Vimeo API: Successfully initialized Vimeo client using library");
                } catch (Exception $e) {
                    error_log("ALM Vimeo API: Failed to initialize Vimeo client - " . $e->getMessage());
                    $this->library_available = false;
                }
            }
        }
        
        if (!$this->library_available) {
            error_log("ALM Vimeo API: Vimeo PHP library not found, falling back to HTTP API. Tried paths: " . implode(', ', $possible_paths));
        }
    }
    
    /**
     * Extract video ID from Vimeo URL or ID
     * 
     * @param string|int $vimeo_input Vimeo URL or video ID
     * @return int|false Video ID or false if not found
     */
    public function extract_video_id($vimeo_input) {
        if (empty($vimeo_input)) {
            return false;
        }
        
        // If it's already a numeric ID
        if (is_numeric($vimeo_input)) {
            return intval($vimeo_input);
        }
        
        // If it's a string, try to extract ID from URL
        if (is_string($vimeo_input)) {
            $patterns = [
                // Standard Vimeo URLs: https://vimeo.com/123456789
                '/vimeo\.com\/(\d+)/',
                // Player URLs: https://player.vimeo.com/video/123456789
                '/player\.vimeo\.com\/video\/(\d+)/',
                // Just the ID itself
                '/^(\d+)$/'
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $vimeo_input, $matches)) {
                    return intval($matches[1]);
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get video metadata from Vimeo API
     * 
     * @param int|string $vimeo_id Vimeo video ID or URL
     * @return array|false Video metadata body or false on error
     */
    public function get_video_metadata($vimeo_id) {
        $video_id = $this->extract_video_id($vimeo_id);
        
        if (!$video_id) {
            error_log("ALM Vimeo API: Could not extract video ID from: " . print_r($vimeo_id, true));
            return false;
        }
        
        // Use Vimeo PHP library if available (preferred method)
        if ($this->library_available && $this->vimeo_client) {
            try {
                $response = $this->vimeo_client->request('/videos/' . $video_id, array(), 'GET');
                
                error_log("ALM Vimeo API (Library): Video ID {$video_id}, Status: " . (isset($response['status']) ? $response['status'] : 'unknown'));
                
                if (isset($response['status']) && $response['status'] === 200 && isset($response['body'])) {
                    $body = $response['body'];
                    
                    // Log if duration is missing or 0
                    if (isset($body['duration']) && $body['duration'] == 0) {
                        error_log("ALM Vimeo API: Duration is 0 for video ID {$video_id}");
                    } elseif (!isset($body['duration'])) {
                        error_log("ALM Vimeo API: Duration field missing for video ID {$video_id}. Available fields: " . implode(', ', array_keys($body)));
                    }
                    
                    return $body;
                } else {
                    error_log("ALM Vimeo API (Library): Request failed. Status: " . (isset($response['status']) ? $response['status'] : 'unknown') . ", Body keys: " . (isset($response['body']) ? implode(', ', array_keys($response['body'])) : 'none'));
                    return false;
                }
            } catch (Exception $e) {
                error_log("ALM Vimeo API (Library): Exception - " . $e->getMessage());
                return false;
            }
        }
        
        // Fallback to HTTP API
        $url = $this->api_base_url . '/videos/' . $video_id;
        
        $args = array(
            'headers' => array(
                'Accept' => 'application/vnd.vimeo.*+json;version=3.4'
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            error_log("ALM Vimeo API (HTTP): Error - " . $response->get_error_message());
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        error_log("ALM Vimeo API (HTTP): Video ID {$video_id}, Status: {$status_code}");
        
        if ($status_code !== 200) {
            error_log("ALM Vimeo API (HTTP): Non-200 response. Body: " . substr($body, 0, 500));
            return false;
        }
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("ALM Vimeo API (HTTP): JSON decode error - " . json_last_error_msg());
            return false;
        }
        
        // Log if duration is missing or 0
        if (isset($data['duration']) && $data['duration'] == 0) {
            error_log("ALM Vimeo API (HTTP): Duration is 0 for video ID {$video_id}");
        } elseif (!isset($data['duration'])) {
            error_log("ALM Vimeo API (HTTP): Duration field missing for video ID {$video_id}. Available fields: " . implode(', ', array_keys($data)));
        }
        
        return $data;
    }
    
    /**
     * Get video duration from Vimeo ID
     * 
     * @param int|string $vimeo_id Vimeo video ID or URL
     * @return int|false Duration in seconds or false on error
     */
    public function get_video_duration($vimeo_id) {
        $metadata = $this->get_video_metadata($vimeo_id);
        
        if (!$metadata || !isset($metadata['duration'])) {
            return false;
        }
        
        return intval($metadata['duration']);
    }
    
    /**
     * Get comprehensive video info from Vimeo ID
     * 
     * @param int|string $vimeo_id Vimeo video ID or URL
     * @return array|false Video info or false on error
     */
    public function get_video_info($vimeo_id) {
        $metadata = $this->get_video_metadata($vimeo_id);
        
        if (!$metadata) {
            return false;
        }
        
        return array(
            'duration' => isset($metadata['duration']) ? intval($metadata['duration']) : 0,
            'title' => isset($metadata['name']) ? $metadata['name'] : '',
            'description' => isset($metadata['description']) ? $metadata['description'] : '',
            'thumbnail' => isset($metadata['pictures']['sizes']) && !empty($metadata['pictures']['sizes']) 
                ? $metadata['pictures']['sizes'][count($metadata['pictures']['sizes']) - 1]['link'] 
                : ''
        );
    }
}

