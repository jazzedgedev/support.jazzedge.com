<?php
/**
 * OpenAI Whisper API Client
 * 
 * Handles transcription using OpenAI Whisper API
 */

if (!defined('ABSPATH')) {
    exit;
}

class ALM_Whisper_Client {
    
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
     * @param string $file_path Path to MP3/audio file
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
     * Transcribe an audio file
     * 
     * @param string $file_path Path to MP3/audio file
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
                'message' => 'File too large: ' . number_format($file_size_mb, 2) . ' MB. OpenAI Whisper API limit is 25 MB.'
            );
        }
        
        // Detect MIME type based on file extension
        $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime_type = 'audio/mpeg'; // default for MP3
        if ($file_ext === 'mp3') {
            $mime_type = 'audio/mpeg';
        } elseif ($file_ext === 'm4a') {
            $mime_type = 'audio/mp4';
        } elseif ($file_ext === 'wav') {
            $mime_type = 'audio/wav';
        } elseif ($file_ext === 'mp4') {
            $mime_type = 'video/mp4';
        }
        
        // Verify file is readable
        if (!is_readable($file_path)) {
            return array(
                'success' => false,
                'message' => 'File is not readable: ' . $file_path
            );
        }
        
        // Ensure we have an absolute path
        $absolute_file_path = realpath($file_path);
        if ($absolute_file_path === false) {
            return array(
                'success' => false,
                'message' => 'Could not resolve absolute path for file: ' . $file_path
            );
        }
        
        // Verify file has content
        $file_size = filesize($absolute_file_path);
        if ($file_size === false || $file_size === 0) {
            return array(
                'success' => false,
                'message' => 'File is empty or unreadable: ' . $absolute_file_path
            );
        }
        
        // Retry logic for transient errors
        $attempt = 0;
        $last_error = null;
        $http_code = 0;
        $curl_error = '';
        $response = '';
        $upload_start_time = time();
        
        // Read file content once
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
        
            // Verify file still exists
            if (!file_exists($absolute_file_path) || !is_readable($absolute_file_path)) {
                return array(
                    'success' => false,
                    'message' => 'File does not exist or is not readable: ' . $absolute_file_path
                );
            }
            
            // Manually construct multipart/form-data body
            $boundary = 'WebKitFormBoundary' . uniqid();
            $eol = "\r\n";
            
            $body = '';
            $safe_filename = addslashes($upload_filename);
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="file"; filename="' . $safe_filename . '"' . $eol;
            $body .= 'Content-Type: ' . $mime_type . $eol;
            $body .= $eol;
            $body .= $file_content;
            $body .= $eol;
            
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="model"' . $eol;
            $body .= $eol;
            $body .= 'whisper-1';
            $body .= $eol;
            
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="language"' . $eol;
            $body .= $eol;
            $body .= 'en';
            $body .= $eol;
            
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="response_format"' . $eol;
            $body .= $eol;
            $body .= 'verbose_json';
            $body .= $eol;
            
            $body .= '--' . $boundary . '--' . $eol;
            
            // Make API request
            $ch = curl_init($this->api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            
            $headers = array(
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: multipart/form-data; boundary=' . $boundary
            );
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1800); // 30 minutes total timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60); // 1 minute to connect
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            
            // Enable verbose output for debugging (only if WP_DEBUG is on)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $verbose_file = sys_get_temp_dir() . '/alm_curl_verbose_' . $chapter_id . '.log';
                $verbose_handle = fopen($verbose_file, 'w');
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_STDERR, $verbose_handle);
            }
            
            $request_start = time();
            if ($progress_callback) {
                call_user_func($progress_callback, 'processing', 'Uploading file and waiting for response... (This may take 5-15 minutes)');
            }
            
            // Log that we're starting the API call
            error_log(sprintf(
                'ALM Whisper API [Chapter %d]: Starting API call - File: %s (%.2f MB), Timeout: 1800s, Body size: %d bytes',
                $chapter_id,
                basename($file_path),
                $file_size_mb,
                strlen($body)
            ));
            
            // Store start time in a file so we can check if it's stuck
            $status_file = sys_get_temp_dir() . '/alm_transcription_' . $chapter_id . '.tmp';
            file_put_contents($status_file, json_encode(array(
                'start_time' => $request_start,
                'chapter_id' => $chapter_id,
                'file' => basename($file_path)
            )));
            
            // Log right before curl_exec
            error_log(sprintf(
                'ALM Whisper API [Chapter %d]: About to call curl_exec() - Time: %s',
                $chapter_id,
                date('Y-m-d H:i:s')
            ));
        
            $response = curl_exec($ch);
            
            // Log immediately after curl_exec returns
            $request_duration = time() - $request_start;
            error_log(sprintf(
                'ALM Whisper API [Chapter %d]: curl_exec() returned - Duration: %ds, Time: %s',
                $chapter_id,
                $request_duration,
                date('Y-m-d H:i:s')
            ));
            
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            $curl_errno = curl_errno($ch);
            
            // Clean up status file
            if (file_exists($status_file)) {
                @unlink($status_file);
            }
            
            // Clean up verbose log if it exists
            if (defined('WP_DEBUG') && WP_DEBUG && isset($verbose_handle)) {
                fclose($verbose_handle);
                $verbose_file = sys_get_temp_dir() . '/alm_curl_verbose_' . $chapter_id . '.log';
                if (file_exists($verbose_file) && filesize($verbose_file) > 0) {
                    error_log(sprintf(
                        'ALM Whisper API [Chapter %d]: cURL verbose log available at: %s',
                        $chapter_id,
                        $verbose_file
                    ));
                }
            }
            
            // Log the result
            error_log(sprintf(
                'ALM Whisper API [Chapter %d]: API call completed - HTTP %d, cURL errno: %d, Duration: %ds, Error: %s, Response length: %d',
                $chapter_id,
                $http_code,
                $curl_errno,
                $request_duration,
                $curl_error ? $curl_error : 'None',
                strlen($response)
            ));
            
            // If curl_exec returned false, log more details
            if ($response === false) {
                error_log(sprintf(
                    'ALM Whisper API [Chapter %d]: curl_exec() returned FALSE - cURL errno: %d, Error: %s, Info: %s',
                    $chapter_id,
                    $curl_errno,
                    $curl_error,
                    print_r(curl_getinfo($ch), true)
                ));
            }
        
            curl_close($ch);
            
            if ($http_code !== 200 || $attempt > 1) {
                error_log(sprintf(
                    'ALM Whisper API [Chapter %d]: Attempt %d/%d - HTTP %d, File: %s (%.2f MB), Duration: %ds',
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
                break;
            }
            
            if ($http_code === 200) {
                break;
            }
            
            // Check if it's a transient error
            $is_transient_error = in_array($http_code, array(502, 503, 504, 429));
            
            $error_data = json_decode($response, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'HTTP ' . $http_code;
            $last_error = 'API error: ' . $error_message;
            
            if (!$is_transient_error) {
                break;
            }
            
            // Wait and retry
            if ($attempt < $max_retries) {
                $wait_time = pow(2, $attempt);
                if ($progress_callback) {
                    call_user_func($progress_callback, 'processing', sprintf('Transient error (HTTP %d). Retrying in %d seconds...', $http_code, $wait_time));
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
                'message' => $error_msg
            );
        }
        
        if ($http_code !== 200) {
            $error_msg = $last_error . ' (after ' . $attempt . ' attempt(s), total time: ' . (time() - $upload_start_time) . 's)';
            if ($progress_callback) {
                call_user_func($progress_callback, 'failed', $error_msg);
            }
            return array(
                'success' => false,
                'message' => $error_msg
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
        
        return array(
            'success' => true,
            'text' => $data['text'],
            'segments' => isset($data['segments']) ? $data['segments'] : null,
            'duration_seconds' => $duration_seconds,
            'duration_minutes' => $duration_minutes,
            'cost' => $estimated_cost
        );
    }
    
    /**
     * Transcribe an uploaded audio file (standalone, not linked to lesson/chapter)
     * Supports text or vtt output format.
     *
     * @param string $file_path Path to audio file
     * @param string $output_format 'text' or 'vtt'
     * @return array Result with success, content, message
     */
    public function transcribe_upload($file_path, $output_format = 'text') {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('OpenAI API key not configured. Please set it in Katahdin AI Hub or Fluent Support AI settings.', 'academy-lesson-manager')
            );
        }

        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'message' => __('File not found.', 'academy-lesson-manager')
            );
        }

        $file_size_mb = filesize($file_path) / 1024 / 1024;
        if ($file_size_mb > 25) {
            return array(
                'success' => false,
                'message' => sprintf(__('File too large: %s MB. OpenAI Whisper API limit is 25 MB.', 'academy-lesson-manager'), number_format($file_size_mb, 2))
            );
        }

        $format = in_array($output_format, array('text', 'vtt')) ? $output_format : 'text';

        $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime_type = 'audio/mpeg';
        if ($file_ext === 'mp3') {
            $mime_type = 'audio/mpeg';
        } elseif ($file_ext === 'm4a') {
            $mime_type = 'audio/mp4';
        } elseif ($file_ext === 'wav') {
            $mime_type = 'audio/wav';
        } elseif ($file_ext === 'mp4') {
            $mime_type = 'video/mp4';
        } elseif ($file_ext === 'webm') {
            $mime_type = 'audio/webm';
        }

        $absolute_file_path = realpath($file_path);
        if ($absolute_file_path === false) {
            return array('success' => false, 'message' => __('Could not resolve file path.', 'academy-lesson-manager'));
        }

        $file_content = file_get_contents($absolute_file_path);
        if ($file_content === false) {
            return array('success' => false, 'message' => __('Failed to read file.', 'academy-lesson-manager'));
        }

        $upload_filename = basename($absolute_file_path);
        $boundary = 'WebKitFormBoundary' . uniqid();
        $eol = "\r\n";

        $body = '';
        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="file"; filename="' . addslashes($upload_filename) . '"' . $eol;
        $body .= 'Content-Type: ' . $mime_type . $eol . $eol;
        $body .= $file_content . $eol;
        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="model"' . $eol . $eol . 'whisper-1' . $eol;
        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="language"' . $eol . $eol . 'en' . $eol;
        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="response_format"' . $eol . $eol . $format . $eol;
        $body .= '--' . $boundary . '--' . $eol;

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: multipart/form-data; boundary=' . $boundary
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 1800);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            return array('success' => false, 'message' => 'cURL error: ' . $curl_error);
        }

        if ($http_code !== 200) {
            $error_data = json_decode($response, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'HTTP ' . $http_code;
            return array('success' => false, 'message' => 'API error: ' . $error_message);
        }

        return array(
            'success' => true,
            'content' => $response,
            'format' => $format
        );
    }

    /**
     * Get duration of audio file using ffprobe (if available) or estimate
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
            // Fallback: estimate based on file size (rough approximation for MP3)
            $file_size_mb = filesize($file_path) / 1024 / 1024;
            // Rough estimate: ~1MB per minute for 128kbps MP3
            return $file_size_mb * 60;
        }
        
        // Get duration using ffprobe
        $cmd = escapeshellarg($ffprobe) . " -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($file_path) . " 2>&1";
        $output = shell_exec($cmd);
        $duration = floatval(trim($output));
        
        return $duration > 0 ? $duration : 0;
    }
}

