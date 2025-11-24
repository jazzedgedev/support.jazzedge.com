<?php
/**
 * Video Downloader Class
 * 
 * Handles downloading and converting Bunny CDN videos to MP4
 */

class Transcription_Video_Downloader {
    
    private $ffmpeg_path;
    private $output_dir;
    
    /**
     * Get output directory path
     */
    public function get_output_directory() {
        return $this->output_dir;
    }
    
    /**
     * Check if MP4 file exists for a chapter
     * 
     * @param int $lesson_id Lesson ID
     * @param int $chapter_id Chapter ID
     * @param string $chapter_title Chapter title
     * @return string|false Path to MP4 file if exists, false otherwise
     */
    public function get_mp4_path($lesson_id, $chapter_id, $chapter_title) {
        $filename = $this->generate_filename($lesson_id, $chapter_id, $chapter_title, 'mp4');
        $file_path = $this->output_dir . $filename;
        
        if (file_exists($file_path)) {
            return $file_path;
        }
        
        return false;
    }
    
    /**
     * Generate filename with format: {lesson_id}-{chapter_id}-{title}.{ext}
     * 
     * @param int $lesson_id Lesson ID
     * @param int $chapter_id Chapter ID
     * @param string $title Chapter title
     * @param string $ext File extension (mp4, vtt, etc.)
     * @return string Generated filename
     */
    public function generate_filename($lesson_id, $chapter_id, $title, $ext = 'mp4') {
        $safe_title = sanitize_file_name($title);
        $safe_title = preg_replace('/[^a-zA-Z0-9_-]/', '-', $safe_title);
        return $lesson_id . '-' . $chapter_id . '-' . $safe_title . '.' . $ext;
    }
    
    public function __construct() {
        // Find ffmpeg
        $this->ffmpeg_path = $this->find_ffmpeg();
        
        // Set output directory (alm_chapters folder in WordPress root/public folder)
        // ABSPATH is the WordPress root (where wp-config.php is)
        // For Local by Flywheel: /Users/williemyette/Local Sites/jazzedge/app/public/
        $this->output_dir = ABSPATH . 'alm_chapters/';
        if (!file_exists($this->output_dir)) {
            wp_mkdir_p($this->output_dir);
        }
    }
    
    /**
     * Get VTT/transcript output directory (alm_transcripts folder)
     * 
     * @return string Path to alm_transcripts directory
     */
    public function get_transcripts_directory() {
        $transcripts_dir = ABSPATH . 'alm_transcripts/';
        if (!file_exists($transcripts_dir)) {
            wp_mkdir_p($transcripts_dir);
        }
        return $transcripts_dir;
    }
    
    /**
     * Find ffmpeg executable
     */
    private function find_ffmpeg() {
        $ffmpeg_paths = array(
            '/opt/homebrew/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/usr/bin/ffmpeg',
            'ffmpeg'
        );
        
        foreach ($ffmpeg_paths as $path) {
            if (strpos($path, '/') === 0) {
                if (file_exists($path)) {
                    $test_result = @shell_exec(escapeshellarg($path) . " -version 2>&1");
                    if (!empty($test_result) && (strpos($test_result, 'ffmpeg') !== false || strpos($test_result, 'version') !== false)) {
                        return $path;
                    }
                }
            } else {
                $which_result = @shell_exec("which " . escapeshellarg($path) . " 2>&1");
                if (!empty($which_result) && strpos($which_result, '/') !== false) {
                    return trim($which_result);
                }
            }
        }
        
        // Last resort
        $known_path = '/opt/homebrew/bin/ffmpeg';
        if (file_exists($known_path)) {
            return $known_path;
        }
        
        return null;
    }
    
    /**
     * Download and convert video to MP4 (Bunny or Vimeo)
     * 
     * @param string|int $video_url The Bunny CDN m3u8 URL or Vimeo ID
     * @param int $lesson_id Lesson ID
     * @param int $chapter_id Chapter ID
     * @param string $chapter_title Chapter title
     * @param string $source Video source: 'bunny' or 'vimeo'
     * @return string|false Path to MP4 file or false on failure
     */
    public function download_and_convert($video_url, $lesson_id, $chapter_id, $chapter_title, $source = 'bunny') {
        if (!$this->ffmpeg_path) {
            error_log('Transcription: ffmpeg not found');
            return false;
        }
        
        // Generate filename with new format: {lesson_id}-{chapter_id}-{title}.mp4
        $filename = $this->generate_filename($lesson_id, $chapter_id, $chapter_title, 'mp4');
        $output_path = $this->output_dir . $filename;
        
        // If file already exists, return it
        if (file_exists($output_path)) {
            return $output_path;
        }
        
        // Handle different video sources
        if ($source === 'vimeo') {
            return $this->download_vimeo_video($video_url, $output_path);
        } else {
            return $this->download_bunny_video($video_url, $output_path);
        }
    }
    
    /**
     * Download and convert Bunny video
     */
    private function download_bunny_video($bunny_url, $output_path) {
        // Get stream URL from master playlist
        $stream_url = $this->get_stream_url($bunny_url);
        if (!$stream_url) {
            error_log('Transcription: Failed to get stream URL from ' . $bunny_url);
            return false;
        }
        
        // Convert using ffmpeg
        $headers = "Referer: https://support.jazzedge.com/\r\nUser-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36";
        $ffmpeg_cmd = $this->ffmpeg_path . " -headers " . escapeshellarg($headers) . " -i " . escapeshellarg($stream_url) . " -c copy -y " . escapeshellarg($output_path) . " 2>&1";
        
        $output = shell_exec($ffmpeg_cmd);
        
        if (file_exists($output_path)) {
            return $output_path;
        }
        
        error_log('Transcription: ffmpeg conversion failed. Output: ' . substr($output, 0, 500));
        return false;
    }
    
    /**
     * Get Vimeo download URL using Vimeo API
     * 
     * @param int $vimeo_id Vimeo video ID
     * @return array|false Array with 'url', 'name', 'remaining_api_calls' or false on error
     */
    private function get_vimeo_download_url($vimeo_id) {
        // First, try to load ALM_Vimeo_API class explicitly
        if (!class_exists('ALM_Vimeo_API')) {
            $alm_vimeo_path = ABSPATH . 'wp-content/plugins/academy-lesson-manager/includes/class-vimeo-api.php';
            if (file_exists($alm_vimeo_path)) {
                require_once($alm_vimeo_path);
            }
        }
        
        // Try ALM_Vimeo_API class first (most reliable)
        if (class_exists('ALM_Vimeo_API')) {
            try {
                $vimeo_api = new ALM_Vimeo_API();
                $metadata = $vimeo_api->get_video_metadata($vimeo_id);
                
                if ($metadata && isset($metadata['files'])) {
                    $downloadUrls = array();
                    foreach ($metadata['files'] as $file) {
                        if (isset($file['quality'], $file['link']) && $file['quality'] !== 'hls' && $file['quality'] !== 'dash') {
                            $key = $file['quality'] . (isset($file['rendition']) ? $file['rendition'] : '');
                            $downloadUrls[$key] = $file['link'];
                        }
                    }
                    
                    $preferredQualityOrder = array('sourcesource', 'hd1080p', 'hd720p', 'sd540p', 'sd360p', 'sd240p');
                    $download_url = '';
                    foreach ($preferredQualityOrder as $quality) {
                        if (isset($downloadUrls[$quality])) {
                            $download_url = $downloadUrls[$quality];
                            break;
                        }
                    }
                    
                    if (!empty($download_url)) {
                        return array(
                            'url' => $download_url,
                            'name' => isset($metadata['name']) ? $metadata['name'] : 'Unknown',
                            'remaining_api_calls' => 0
                        );
                    }
                }
            } catch (Exception $e) {
                error_log('Transcription: ALM_Vimeo_API exception - ' . $e->getMessage());
            }
        }
        
        // Try to use Vimeo PHP library if available
        $vimeo_client = null;
        $library_available = false;
        
        // Try to load Vimeo PHP library
        $possible_paths = array(
            ABSPATH . '../willie/ja-admin/vimeo.php-3.0.2/src/Vimeo/Vimeo.php',
            dirname(ABSPATH) . '/willie/ja-admin/vimeo.php-3.0.2/src/Vimeo/Vimeo.php',
            ABSPATH . 'wp-content/plugins/academy-lesson-manager/vendor/vimeo/vimeo-api/src/Vimeo/Vimeo.php'
        );
        
        $vimeo_library_path = null;
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                $vimeo_library_path = $path;
                break;
            }
        }
        
        if ($vimeo_library_path) {
            require_once(dirname($vimeo_library_path) . '/Vimeo.php');
            if (class_exists('Vimeo\Vimeo')) {
                $library_available = true;
                $client_id = "4b8aa7cfcc3ca72c070d952629bfc5061c459f37";
                $client_secret = "dUbGlWUkDSZoyPce8ehdnfoxDpAwGeoU5uuxEg0ecrESqGLh7taUehQOZqk8bL3a22xqA2vxt3cvekLIxe39/AhNpokavpKsmDJlr671roBVGCTPG5aDnoBEauCCJDIH";
                $access_token = "7d303a30c260a569f0ea69cb01265f9b";
                
                try {
                    $vimeo_client = new \Vimeo\Vimeo($client_id, $client_secret, $access_token);
                } catch (Exception $e) {
                    error_log('Transcription: Failed to initialize Vimeo client - ' . $e->getMessage());
                    $library_available = false;
                }
            }
        }
        
        // Use Vimeo PHP library if available
        if ($library_available && $vimeo_client) {
            try {
                $response = $vimeo_client->request('/videos/' . $vimeo_id, array(), 'GET');
                
                if (isset($response['status']) && $response['status'] === 200 && isset($response['body'])) {
                    $videoDetails = $response['body'];
                    $name = isset($videoDetails['name']) ? $videoDetails['name'] : 'Unknown';
                    $remaining_api_calls = isset($response['headers']['x-ratelimit-remaining']) ? $response['headers']['x-ratelimit-remaining'] : 0;
                    
                    if (!isset($videoDetails['files'])) {
                        error_log('Transcription: Vimeo API response missing files array');
                        return false;
                    }
                    
                    $downloadUrls = array();
                    foreach ($videoDetails['files'] as $file) {
                        if (isset($file['quality'], $file['link']) && $file['quality'] !== 'hls' && $file['quality'] !== 'dash') {
                            $downloadUrls[$file['quality'] . $file['rendition']] = $file['link'];
                        }
                    }
                    
                    $preferredQualityOrder = array('sourcesource', 'hd1080p', 'hd720p', 'sd540p', 'sd360p', 'sd240p');
                    $download_url = '';
                    foreach ($preferredQualityOrder as $quality) {
                        if (isset($downloadUrls[$quality])) {
                            $download_url = $downloadUrls[$quality];
                            break;
                        }
                    }
                    
                    if (empty($download_url)) {
                        error_log('Transcription: No suitable Vimeo download URL found');
                        return false;
                    }
                    
                    return array(
                        'url' => $download_url,
                        'name' => $name,
                        'remaining_api_calls' => $remaining_api_calls
                    );
                } else {
                    error_log('Transcription: Vimeo API request failed. Status: ' . (isset($response['status']) ? $response['status'] : 'unknown'));
                    return false;
                }
            } catch (Exception $e) {
                error_log('Transcription: Vimeo API exception - ' . $e->getMessage());
                return false;
            }
        }
        
        // Final fallback: Direct HTTP API call
        $api_url = 'https://api.vimeo.com/videos/' . $vimeo_id;
        $access_token = "7d303a30c260a569f0ea69cb01265f9b";
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/vnd.vimeo.*+json;version=3.4'
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_get($api_url, $args);
        
        if (is_wp_error($response)) {
            error_log('Transcription: HTTP API error - ' . $response->get_error_message());
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            error_log('Transcription: Vimeo HTTP API error - Status: ' . $status_code);
            return false;
        }
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Transcription: Vimeo JSON decode error - ' . json_last_error_msg());
            return false;
        }
        
        if (!isset($data['files'])) {
            error_log('Transcription: Vimeo API response missing files array');
            return false;
        }
        
        $downloadUrls = array();
        foreach ($data['files'] as $file) {
            if (isset($file['quality'], $file['link']) && $file['quality'] !== 'hls' && $file['quality'] !== 'dash') {
                $key = $file['quality'] . (isset($file['rendition']) ? $file['rendition'] : '');
                $downloadUrls[$key] = $file['link'];
            }
        }
        
        $preferredQualityOrder = array('sourcesource', 'hd1080p', 'hd720p', 'sd540p', 'sd360p', 'sd240p');
        $download_url = '';
        foreach ($preferredQualityOrder as $quality) {
            if (isset($downloadUrls[$quality])) {
                $download_url = $downloadUrls[$quality];
                break;
            }
        }
        
        if (empty($download_url)) {
            error_log('Transcription: No suitable Vimeo download URL found via HTTP API');
            return false;
        }
        
        return array(
            'url' => $download_url,
            'name' => isset($data['name']) ? $data['name'] : 'Unknown',
            'remaining_api_calls' => 0
        );
    }
    
    /**
     * Download and convert Vimeo video
     */
    private function download_vimeo_video($vimeo_id, $output_path) {
        // Get Vimeo download URL
        $vimeo_data = $this->get_vimeo_download_url($vimeo_id);
        
        if (!$vimeo_data || empty($vimeo_data['url'])) {
            error_log('Transcription: Failed to get Vimeo download URL for ID ' . $vimeo_id);
            return false;
        }
        
        $download_url = $vimeo_data['url'];
        
        // Download using ffmpeg
        // Vimeo download URLs are typically direct MP4 links, so we can use ffmpeg directly
        $ffmpeg_cmd = $this->ffmpeg_path . " -i " . escapeshellarg($download_url) . " -c copy -y " . escapeshellarg($output_path) . " 2>&1";
        
        $output = shell_exec($ffmpeg_cmd);
        
        if (file_exists($output_path)) {
            return $output_path;
        }
        
        error_log('Transcription: Vimeo download failed for ID ' . $vimeo_id . '. ffmpeg output: ' . substr($output, 0, 500));
        return false;
    }
    
    /**
     * Get highest quality stream URL from master playlist
     */
    private function get_stream_url($m3u8_url) {
        $result = $this->download_with_headers($m3u8_url);
        
        if ($result['http_code'] !== 200 || empty($result['content'])) {
            return false;
        }
        
        // Parse master playlist
        $lines = explode("\n", $result['content']);
        $streams = array();
        $current_bandwidth = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (preg_match('/#EXT-X-STREAM-INF:.*BANDWIDTH=(\d+)/', $line, $matches)) {
                $current_bandwidth = intval($matches[1]);
            } elseif (!empty($line) && !preg_match('/^#/', $line) && $current_bandwidth > 0) {
                $streams[] = array(
                    'bandwidth' => $current_bandwidth,
                    'url' => $line
                );
                $current_bandwidth = 0;
            }
        }
        
        if (empty($streams)) {
            return false;
        }
        
        // Sort by bandwidth (highest first)
        usort($streams, function($a, $b) {
            return $b['bandwidth'] - $a['bandwidth'];
        });
        
        // Construct full URL
        $base_url = dirname($m3u8_url) . '/';
        return $base_url . $streams[0]['url'];
    }
    
    /**
     * Download with headers (for Bunny CDN)
     */
    private function download_with_headers($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Referer: https://support.jazzedge.com/',
            'Accept: */*',
            'Accept-Language: en-US,en;q=0.9'
        ));
        
        $content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        return array(
            'content' => $content,
            'http_code' => $http_code,
            'error' => $curl_error
        );
    }
    
    /**
     * Convert MP4 to MP3 (audio only) for transcription
     * This reduces file size significantly since Whisper only needs audio
     * 
     * @param string $mp4_path Path to MP4 file
     * @return string|false Path to MP3 file or false on failure
     */
    public function convert_to_audio($mp4_path) {
        if (!$this->ffmpeg_path) {
            error_log('Transcription: ffmpeg not found, cannot convert to audio');
            return false;
        }
        
        if (!file_exists($mp4_path)) {
            error_log('Transcription: MP4 file not found: ' . $mp4_path);
            return false;
        }
        
        // Create MP3 path (same directory, .mp3 extension)
        $mp3_path = preg_replace('/\.mp4$/i', '.mp3', $mp4_path);
        
        // If MP3 already exists and is recent, check if it's under 25MB
        if (file_exists($mp3_path)) {
            $mp4_mtime = filemtime($mp4_path);
            $mp3_mtime = filemtime($mp3_path);
            $mp3_size_mb = filesize($mp3_path) / 1024 / 1024;
            
            // If MP3 is newer or same age as MP4 AND under 25MB, use it
            if ($mp3_mtime >= $mp4_mtime && $mp3_size_mb <= 25) {
                return $mp3_path;
            }
            
            // If MP3 exists but is too large, delete it and recompress
            if ($mp3_size_mb > 25) {
                error_log(sprintf('Transcription: Existing MP3 is %.2f MB (over 25MB limit), will recompress...', $mp3_size_mb));
                @unlink($mp3_path);
            }
        }
        
        // Convert MP4 to MP3 using ffmpeg
        // Start with good quality settings, then compress more if needed
        // Using VBR (Variable Bitrate) mode for better compression
        $max_size_mb = 25; // Whisper API limit
        $compression_levels = array(
            array('quality' => '4', 'samplerate' => '44100', 'channels' => '2', 'name' => 'VBR q4 stereo', 'bitrate' => '~128k'),
            array('quality' => '6', 'samplerate' => '44100', 'channels' => '2', 'name' => 'VBR q6 stereo', 'bitrate' => '~96k'),
            array('quality' => '7', 'samplerate' => '22050', 'channels' => '1', 'name' => 'VBR q7 mono', 'bitrate' => '~64k'),
            array('quality' => '8', 'samplerate' => '16000', 'channels' => '1', 'name' => 'VBR q8 mono', 'bitrate' => '~48k'),
            array('quality' => '9', 'samplerate' => '16000', 'channels' => '1', 'name' => 'VBR q9 mono', 'bitrate' => '~32k'),
            // Last resort: fixed bitrate at very low rate
            array('bitrate' => '24k', 'samplerate' => '16000', 'channels' => '1', 'name' => '24k mono fixed', 'quality' => null)
        );
        
        $last_output = '';
        $last_size_mb = 0;
        foreach ($compression_levels as $level) {
            // Build ffmpeg command with current compression level
            // -i: input file
            // -vn: disable video (audio only)
            // -acodec libmp3lame: use MP3 codec
            // -q:a: VBR quality (0-9, 9 = smallest file, best compression)
            // -b:a: fixed bitrate (only used for last resort level)
            // -ar: sample rate
            // -ac: audio channels (1 = mono, 2 = stereo)
            // -y: overwrite if exists
            
            if (isset($level['quality'])) {
                // Use VBR mode for better compression
                $ffmpeg_cmd = $this->ffmpeg_path . " -i " . escapeshellarg($mp4_path) . 
                              " -vn -acodec libmp3lame -q:a " . $level['quality'] . 
                              " -ar " . $level['samplerate'] . 
                              " -ac " . $level['channels'] . 
                              " -y " . escapeshellarg($mp3_path) . " 2>&1";
            } else {
                // Use fixed bitrate as last resort
                $ffmpeg_cmd = $this->ffmpeg_path . " -i " . escapeshellarg($mp4_path) . 
                              " -vn -acodec libmp3lame -b:a " . $level['bitrate'] . 
                              " -ar " . $level['samplerate'] . 
                              " -ac " . $level['channels'] . 
                              " -y " . escapeshellarg($mp3_path) . " 2>&1";
            }
            
            error_log(sprintf('Transcription: Attempting MP3 conversion with %s...', $level['name']));
            $output = shell_exec($ffmpeg_cmd);
            $last_output = $output;
            
            if (!file_exists($mp3_path)) {
                error_log('Transcription: Audio conversion failed at ' . $level['name'] . '. Output: ' . substr($output, 0, 500));
                continue;
            }
            
            // Check file size
            $file_size_mb = filesize($mp3_path) / 1024 / 1024;
            $last_size_mb = $file_size_mb;
            
            error_log(sprintf('Transcription: MP3 converted at %s: %.2f MB', $level['name'], $file_size_mb));
            
            if ($file_size_mb <= $max_size_mb) {
                // Success! File is under 25MB
                if ($level['name'] !== '128k stereo') {
                    error_log(sprintf('Transcription: MP3 compressed to %s to meet 25MB limit. Final size: %.2f MB', 
                        $level['name'], $file_size_mb));
                }
                return $mp3_path;
            }
            
            // File is still too large, try next compression level
            error_log(sprintf('Transcription: MP3 at %s is %.2f MB (over 25MB limit), trying higher compression...', 
                $level['name'], $file_size_mb));
        }
        
        // If we get here, even the most aggressive compression didn't work
        if (file_exists($mp3_path)) {
            $final_size_mb = filesize($mp3_path) / 1024 / 1024;
            error_log(sprintf('Transcription: ERROR - MP3 file is still %.2f MB after maximum compression (24k mono). File may be too long for Whisper API.', $final_size_mb));
            // Don't return it - it will fail validation anyway
            // Return false so the caller knows compression failed
            return false;
        }
        
        error_log('Transcription: Audio conversion failed at all compression levels. Output: ' . substr($last_output, 0, 500));
        return false;
    }
}

