<?php
/**
 * OpenAI Whisper API Client
 * 
 * Handles transcription using OpenAI Whisper API
 */

class Transcription_Whisper_Client {
    
    private $api_key;
    private $api_url = 'https://api.openai.com/v1/audio/transcriptions';
    private $cost_per_minute = 0.006; // $0.006 per minute
    
    public function __construct() {
        // Try to get API key from existing options
        $this->api_key = get_option('katahdin_ai_hub_openai_key', '');
        if (empty($this->api_key)) {
            $this->api_key = get_option('fluent_support_ai_openai_key', '');
        }
    }
    
    /**
     * Validate file before transcription (without calling API)
     * 
     * @param string $file_path Path to MP4/audio file
     * @return array Validation result with success, message, and file info
     */
    public function validate_file($file_path) {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => 'OpenAI API key not configured.'
            );
        }
        
        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'message' => 'File not found: ' . $file_path
            );
        }
        
        // Check file size
        $file_size_mb = filesize($file_path) / 1024 / 1024;
        if ($file_size_mb > 25) {
            return array(
                'success' => false,
                'message' => 'File too large: ' . number_format($file_size_mb, 2) . ' MB. OpenAI Whisper API limit is 25 MB.',
                'file_size_mb' => $file_size_mb
            );
        }
        
        // Get file duration
        $duration_seconds = $this->get_file_duration($file_path);
        $duration_minutes = $duration_seconds / 60;
        $estimated_cost = $duration_minutes * $this->cost_per_minute;
        
        return array(
            'success' => true,
            'file_size_mb' => $file_size_mb,
            'duration_seconds' => $duration_seconds,
            'duration_minutes' => $duration_minutes,
            'estimated_cost' => $estimated_cost,
            'message' => 'File is valid for transcription'
        );
    }
    
    /**
     * Transcribe an audio/video file
     * 
     * @param string $file_path Path to MP4/audio file
     * @param int $chapter_id Chapter ID for logging
     * @param int $max_retries Maximum number of retries for transient errors (default: 3)
     * @param callable $progress_callback Optional callback function(status, message) for progress updates
     * @return array Result with success, text, duration, and cost
     */
    public function transcribe_file($file_path, $chapter_id, $max_retries = 3, $progress_callback = null) {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => 'OpenAI API key not configured. Please set it in Katahdin AI Hub or Fluent Support AI settings.'
            );
        }
        
        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'message' => 'File not found: ' . $file_path
            );
        }
        
        // Get file duration (for cost calculation)
        $duration_seconds = $this->get_file_duration($file_path);
        $duration_minutes = $duration_seconds / 60;
        $estimated_cost = $duration_minutes * $this->cost_per_minute;
        
        // Check file size - OpenAI Whisper API has a 25MB limit
        $file_size_mb = filesize($file_path) / 1024 / 1024;
        if ($file_size_mb > 25) {
            return array(
                'success' => false,
                'message' => 'File too large: ' . number_format($file_size_mb, 2) . ' MB. OpenAI Whisper API limit is 25 MB. Consider compressing the video or splitting it.'
            );
        }
        
        // Increase PHP execution time for this operation
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        
        // Detect MIME type based on file extension
        $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime_type = 'video/mp4'; // default
        if ($file_ext === 'mp3') {
            $mime_type = 'audio/mpeg';
        } elseif ($file_ext === 'm4a') {
            $mime_type = 'audio/mp4';
        } elseif ($file_ext === 'wav') {
            $mime_type = 'audio/wav';
        } elseif ($file_ext === 'mp4') {
            $mime_type = 'video/mp4';
        }
        
        // Verify file is readable before attempting upload
        if (!is_readable($file_path)) {
            return array(
                'success' => false,
                'message' => 'File is not readable: ' . $file_path
            );
        }
        
        // Ensure we have an absolute path for CURLFile
        $absolute_file_path = realpath($file_path);
        if ($absolute_file_path === false) {
            return array(
                'success' => false,
                'message' => 'Could not resolve absolute path for file: ' . $file_path
            );
        }
        
        // Verify file is actually readable and has content
        $file_size = filesize($absolute_file_path);
        if ($file_size === false || $file_size === 0) {
            return array(
                'success' => false,
                'message' => 'File is empty or unreadable: ' . $absolute_file_path . ' (size: ' . $file_size . ')'
            );
        }
        
        // Verify it's actually an audio file by checking file signature (magic bytes)
        $file_handle = fopen($absolute_file_path, 'rb');
        if ($file_handle) {
            $header = fread($file_handle, 4);
            fclose($file_handle);
            
            // MP3 files start with ID3 tag (49 44 33) or frame sync (FF FB/FF F3/FF F2)
            $is_valid_audio = false;
            if (substr($header, 0, 3) === 'ID3') {
                $is_valid_audio = true; // ID3 tag (MP3)
            } elseif (substr($header, 0, 2) === "\xFF\xFB" || substr($header, 0, 2) === "\xFF\xF3" || substr($header, 0, 2) === "\xFF\xF2") {
                $is_valid_audio = true; // MP3 frame sync
            } elseif ($file_ext === 'mp3' || $file_ext === 'mp4' || $file_ext === 'm4a' || $file_ext === 'wav') {
                // If extension matches, assume it's valid (some files might not have standard headers)
                $is_valid_audio = true;
            }
            
            if (!$is_valid_audio && $file_ext === 'mp3') {
                error_log('Whisper API: WARNING - MP3 file may be corrupted. Header: ' . bin2hex($header));
            }
        }
        
        // Retry logic for transient errors (502, 503, 504, 429)
        $attempt = 0;
        $last_error = null;
        $http_code = 0;
        $curl_error = '';
        $response = '';
        $upload_start_time = time();
        
        // Read file content once (before retry loop)
        $file_content = file_get_contents($absolute_file_path);
        if ($file_content === false) {
            return array(
                'success' => false,
                'message' => 'Failed to read file: ' . $absolute_file_path
            );
        }
        
        $upload_filename = basename($absolute_file_path);
        
        while ($attempt < $max_retries) {
            $attempt++;
            
            if ($progress_callback && $attempt > 1) {
                call_user_func($progress_callback, 'processing', sprintf('Retry attempt %d/%d...', $attempt, $max_retries));
            } elseif ($progress_callback) {
                call_user_func($progress_callback, 'processing', 'Sending file to OpenAI API...');
            }
        
            // Verify file still exists and is readable right before upload
            if (!file_exists($absolute_file_path) || !is_readable($absolute_file_path)) {
                $error_msg = 'File does not exist or is not readable: ' . $absolute_file_path;
                error_log('Whisper API: ' . $error_msg);
                return array(
                    'success' => false,
                    'message' => $error_msg
                );
            }
            
            // Manually construct multipart/form-data body for this attempt
            // This gives us full control over the encoding
            // Boundary should NOT include -- prefix (that's added when constructing the body)
            $boundary = 'WebKitFormBoundary' . uniqid();
            $eol = "\r\n";
            
            // Build multipart body manually
            $body = '';
            
            // File field - escape filename for safety
            $safe_filename = addslashes($upload_filename);
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="file"; filename="' . $safe_filename . '"' . $eol;
            $body .= 'Content-Type: ' . $mime_type . $eol;
            $body .= $eol;
            $body .= $file_content;
            $body .= $eol;
            
            // Model field
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="model"' . $eol;
            $body .= $eol;
            $body .= 'whisper-1';
            $body .= $eol;
            
            // Language field
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="language"' . $eol;
            $body .= $eol;
            $body .= 'en';
            $body .= $eol;
            
            // Response format field
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="response_format"' . $eol;
            $body .= $eol;
            $body .= 'verbose_json';
            $body .= $eol;
            
            // Closing boundary
            $body .= '--' . $boundary . '--' . $eol;
            
            // Only log on retry attempts
            if ($attempt > 1) {
                error_log(sprintf(
                    'Whisper API: Retry attempt %d/%d - File: %s (%.2f MB)',
                    $attempt,
                    $max_retries,
                    $upload_filename,
                    filesize($absolute_file_path) / 1024 / 1024
                ));
            }
            
            // Make API request using manually constructed multipart body
            $ch = curl_init($this->api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            
            // Set the manually constructed multipart body
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            
            // Set Content-Type header with boundary
            $headers = array(
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: multipart/form-data; boundary=' . $boundary
            );
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            // Force HTTP/1.1
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        
        // Increase timeout for large files (30 minutes)
        curl_setopt($ch, CURLOPT_TIMEOUT, 1800);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        
        // Enable verbose output only for errors (not logged unless there's an error)
        $verbose_log = null;
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $verbose_log = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_STDERR, $verbose_log);
            curl_setopt($ch, CURLOPT_HEADER, true);
        }
            
            $request_start = time();
            if ($progress_callback) {
                call_user_func($progress_callback, 'processing', 'Uploading file and waiting for response... (This may take 5-15 minutes)');
            }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $curl_info = curl_getinfo($ch);
        $request_duration = time() - $request_start;
        
        // If CURLOPT_HEADER was enabled, separate headers from body
        if (isset($verbose_log) && $response) {
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            if ($header_size > 0) {
                $response_headers = substr($response, 0, $header_size);
                $response_body = substr($response, $header_size);
                $response = $response_body; // Use body only for parsing
            }
        }
        
        // Only log verbose output on errors
        if (isset($verbose_log)) {
            rewind($verbose_log);
            $verbose_output = stream_get_contents($verbose_log);
            fclose($verbose_log);
            // Store verbose output for error logging only
            if ($http_code !== 200 && !empty($verbose_output)) {
                $verbose_output_stored = $verbose_output;
            }
        }
        
        curl_close($ch);
        
            // Only log errors or retries
            if ($http_code !== 200 || $attempt > 1) {
                error_log(sprintf(
                    'Whisper API [Chapter %d]: Attempt %d/%d - HTTP %d, File: %s (%.2f MB), Duration: %ds',
                    $chapter_id,
                    $attempt,
                    $max_retries,
                    $http_code,
                    basename($file_path),
                    $file_size_mb,
                    $request_duration
                ));
            }
            
            if ($curl_error) {
                $last_error = 'cURL error: ' . $curl_error;
                // Don't retry on cURL errors (network issues)
                break;
            }
            
            // Success!
            if ($http_code === 200) {
                break;
            }
            
            // Check if it's a transient error that we should retry
            $is_transient_error = in_array($http_code, array(502, 503, 504, 429));
            
            $error_data = json_decode($response, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'HTTP ' . $http_code;
            $last_error = 'API error: ' . $error_message;
            
            if (!$is_transient_error) {
                // Permanent error, don't retry
                break;
            }
            
            // Transient error - wait and retry
            if ($attempt < $max_retries) {
                $wait_time = pow(2, $attempt); // Exponential backoff: 2s, 4s, 8s
                $error_msg = sprintf(
                    'Transient error (HTTP %d) on attempt %d. Retrying in %d seconds...',
                    $http_code,
                    $attempt,
                    $wait_time
                );
                error_log('Whisper API: ' . $error_msg);
                
                if ($progress_callback) {
                    call_user_func($progress_callback, 'processing', $error_msg);
                }
                
                sleep($wait_time);
            }
        }
        
        // Check final result
        if ($curl_error) {
            // Enhanced error logging for cURL errors
            $error_details = array(
                'chapter_id' => $chapter_id,
                'file_path' => basename($file_path),
                'file_size_mb' => round($file_size_mb, 2),
                'duration_minutes' => round($duration_minutes, 2),
                'curl_error' => $curl_error,
                'attempts' => $attempt,
                'total_time_seconds' => time() - $upload_start_time
            );
            
            $debug_log = "=== WHISPER API cURL ERROR ===\n";
            $debug_log .= "Chapter ID: " . $error_details['chapter_id'] . "\n";
            $debug_log .= "File: " . $error_details['file_path'] . "\n";
            $debug_log .= "Size: " . $error_details['file_size_mb'] . " MB\n";
            $debug_log .= "Duration: " . $error_details['duration_minutes'] . " min\n";
            $debug_log .= "cURL Error: " . $error_details['curl_error'] . "\n";
            $debug_log .= "Attempts: " . $error_details['attempts'] . "\n";
            $debug_log .= "Total Time: " . $error_details['total_time_seconds'] . "s\n";
            $debug_log .= "============================\n";
            
            error_log($debug_log);
            set_transient('ct_whisper_error_' . $chapter_id, $error_details, 3600);
            
            $error_msg = $last_error . ' (after ' . $attempt . ' attempt(s), total time: ' . (time() - $upload_start_time) . 's)';
            if ($progress_callback) {
                call_user_func($progress_callback, 'failed', $error_msg);
            }
            return array(
                'success' => false,
                'message' => $error_msg,
                'attempts' => $attempt,
                'debug_info' => $error_details
            );
        }
        
        if ($http_code !== 200) {
            // Enhanced error logging for debugging
            $error_data = json_decode($response, true);
            $error_details = array(
                'chapter_id' => $chapter_id,
                'file_path' => basename($file_path),
                'file_size_mb' => round($file_size_mb, 2),
                'duration_minutes' => round($duration_minutes, 2),
                'http_code' => $http_code,
                'attempts' => $attempt,
                'total_time_seconds' => time() - $upload_start_time,
                'error_message' => isset($error_data['error']['message']) ? $error_data['error']['message'] : 'Unknown error',
                'error_type' => isset($error_data['error']['type']) ? $error_data['error']['type'] : 'Unknown',
                'error_code' => isset($error_data['error']['code']) ? $error_data['error']['code'] : null,
                'api_response' => substr($response, 0, 500) // First 500 chars of response
            );
            
            // Create a copyable debug log entry
            $debug_log = "=== WHISPER API ERROR ===\n";
            $debug_log .= "Chapter ID: " . $error_details['chapter_id'] . "\n";
            $debug_log .= "File: " . $error_details['file_path'] . "\n";
            $debug_log .= "Size: " . $error_details['file_size_mb'] . " MB\n";
            $debug_log .= "Duration: " . $error_details['duration_minutes'] . " min\n";
            $debug_log .= "HTTP Code: " . $error_details['http_code'] . "\n";
            $debug_log .= "Attempts: " . $error_details['attempts'] . "\n";
            $debug_log .= "Total Time: " . $error_details['total_time_seconds'] . "s\n";
            $debug_log .= "Error Type: " . $error_details['error_type'] . "\n";
            $debug_log .= "Error Code: " . ($error_details['error_code'] ?: 'N/A') . "\n";
            $debug_log .= "Error Message: " . $error_details['error_message'] . "\n";
            $debug_log .= "API Response: " . $error_details['api_response'] . "\n";
            $debug_log .= "========================\n";
            
            error_log($debug_log);
            
            // Store debug info in transient for easy retrieval
            set_transient('ct_whisper_error_' . $chapter_id, $error_details, 3600); // 1 hour
            
            $error_msg = $last_error . ' (after ' . $attempt . ' attempt(s), total time: ' . (time() - $upload_start_time) . 's)';
            if ($progress_callback) {
                call_user_func($progress_callback, 'failed', $error_msg);
            }
            return array(
                'success' => false,
                'message' => $error_msg,
                'http_code' => $http_code,
                'attempts' => $attempt,
                'debug_info' => $error_details
            );
        }
        
        if ($progress_callback) {
            call_user_func($progress_callback, 'processing', 'API response received! Parsing transcription data...');
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['text'])) {
            $error_msg = 'Unexpected API response format. Response: ' . substr($response, 0, 200);
            if ($progress_callback) {
                call_user_func($progress_callback, 'failed', $error_msg);
            }
            return array(
                'success' => false,
                'message' => $error_msg
            );
        }
        
        if ($progress_callback) {
            call_user_func($progress_callback, 'processing', 'Transcription received! (' . strlen($data['text']) . ' characters)');
        }
        
        // Log cost
        $cost_logger = new Transcription_Cost_Logger();
        $cost_logger->log_transcription($chapter_id, $duration_minutes, $estimated_cost);
        
        return array(
            'success' => true,
            'text' => $data['text'],
            'segments' => isset($data['segments']) ? $data['segments'] : null, // Word-level timestamps
            'duration_seconds' => $duration_seconds,
            'duration_minutes' => $duration_minutes,
            'cost' => $estimated_cost
        );
    }
    
    /**
     * Get duration of video/audio file using ffprobe
     */
    private function get_file_duration($file_path) {
        // Try to find ffprobe
        $ffprobe_paths = array(
            '/opt/homebrew/bin/ffprobe',
            '/usr/local/bin/ffprobe',
            '/usr/bin/ffprobe',
            'ffprobe'
        );
        
        $ffprobe = null;
        foreach ($ffprobe_paths as $path) {
            if (strpos($path, '/') === 0 && file_exists($path)) {
                $ffprobe = $path;
                break;
            } else {
                $which_result = @shell_exec("which " . escapeshellarg($path) . " 2>/dev/null");
                if (!empty($which_result) && strpos($which_result, '/') !== false) {
                    $ffprobe = trim($which_result);
                    break;
                }
            }
        }
        
        if (!$ffprobe) {
            // Fallback: estimate based on file size (rough approximation)
            $file_size_mb = filesize($file_path) / 1024 / 1024;
            // Rough estimate: 1MB per minute for 1080p video
            return $file_size_mb * 60;
        }
        
        // Get duration using ffprobe
        $cmd = escapeshellarg($ffprobe) . " -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($file_path) . " 2>&1";
        $output = shell_exec($cmd);
        $duration = floatval(trim($output));
        
        return $duration > 0 ? $duration : 0;
    }
}

