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
        
        // Prepare file for upload (only need to do this once)
        $file_data = array(
            'file' => new CURLFile($file_path, 'video/mp4', basename($file_path))
        );
        
        $post_data = array(
            'model' => 'whisper-1',
            'language' => 'en',
            'response_format' => 'verbose_json' // Get timestamps for VTT generation
        );
        
        // Retry logic for transient errors (502, 503, 504, 429)
        $attempt = 0;
        $last_error = null;
        $http_code = 0;
        $curl_error = '';
        $response = '';
        $upload_start_time = time();
        
        while ($attempt < $max_retries) {
            $attempt++;
            
            if ($progress_callback && $attempt > 1) {
                call_user_func($progress_callback, 'processing', sprintf('Retry attempt %d/%d...', $attempt, $max_retries));
            } elseif ($progress_callback) {
                call_user_func($progress_callback, 'processing', 'Sending file to OpenAI API...');
            }
            
            // Make API request
            $ch = curl_init($this->api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($file_data, $post_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->api_key
            ));
            // Increase timeout for large files
            // Whisper API can take a while for long videos - allow up to 30 minutes
            // File upload + processing can be slow for large files
            curl_setopt($ch, CURLOPT_TIMEOUT, 1800); // 30 minutes
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60); // 60 seconds to connect
            
            $request_start = time();
            if ($progress_callback) {
                call_user_func($progress_callback, 'processing', 'Uploading file and waiting for response... (This may take 5-15 minutes)');
            }
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            $curl_info = curl_getinfo($ch);
            $request_duration = time() - $request_start;
            curl_close($ch);
            
            // Log attempt with detailed info
            error_log(sprintf(
                'Transcription attempt %d/%d for chapter %d: HTTP %d, Duration: %ds, File: %s (%.2f MB, %.1f min), Total time: %ds',
                $attempt,
                $max_retries,
                $chapter_id,
                $http_code,
                $request_duration,
                basename($file_path),
                $file_size_mb,
                $duration_minutes,
                time() - $upload_start_time
            ));
            
            if ($progress_callback) {
                call_user_func($progress_callback, 'processing', sprintf('API responded (HTTP %d) after %d seconds. Processing...', $http_code, $request_duration));
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
                error_log('Transcription: ' . $error_msg);
                
                if ($progress_callback) {
                    call_user_func($progress_callback, 'processing', $error_msg);
                }
                
                sleep($wait_time);
            }
        }
        
        // Check final result
        if ($curl_error) {
            $error_msg = $last_error . ' (after ' . $attempt . ' attempt(s), total time: ' . (time() - $upload_start_time) . 's)';
            if ($progress_callback) {
                call_user_func($progress_callback, 'failed', $error_msg);
            }
            return array(
                'success' => false,
                'message' => $error_msg,
                'attempts' => $attempt
            );
        }
        
        if ($http_code !== 200) {
            $error_msg = $last_error . ' (after ' . $attempt . ' attempt(s), total time: ' . (time() - $upload_start_time) . 's)';
            if ($progress_callback) {
                call_user_func($progress_callback, 'failed', $error_msg);
            }
            return array(
                'success' => false,
                'message' => $error_msg,
                'http_code' => $http_code,
                'attempts' => $attempt
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

