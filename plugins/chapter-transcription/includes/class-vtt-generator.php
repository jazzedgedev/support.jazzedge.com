<?php
/**
 * VTT Generator Class
 * 
 * Generates WebVTT files from transcripts
 */

class Transcription_VTT_Generator {
    
    /**
     * Generate VTT file from transcript
     * 
     * @param string $transcript_text The transcript text
     * @param array $segments Whisper API segments with timestamps (optional)
     * @param float $video_duration Total video duration in seconds
     * @param string $output_path Full path where VTT file should be saved
     * @return bool Success
     */
    public function generate_vtt($transcript_text, $segments, $video_duration, $output_path) {
        $vtt_content = "WEBVTT\n\n";
        
        // If we have segments with timestamps from Whisper API, use them
        if (!empty($segments) && is_array($segments)) {
            foreach ($segments as $segment) {
                if (isset($segment['start'], $segment['end'], $segment['text'])) {
                    $start_time = $this->format_timestamp($segment['start']);
                    $end_time = $this->format_timestamp($segment['end']);
                    $text = trim($segment['text']);
                    
                    if (!empty($text)) {
                        $vtt_content .= $start_time . " --> " . $end_time . "\n";
                        $vtt_content .= $text . "\n\n";
                    }
                }
            }
        } else {
            // Fallback: estimate timestamps based on text length and duration
            $vtt_content .= $this->generate_estimated_vtt($transcript_text, $video_duration);
        }
        
        // Save VTT file
        return file_put_contents($output_path, $vtt_content) !== false;
    }
    
    /**
     * Generate VTT with estimated timestamps
     * Splits text into chunks and estimates timing based on average reading speed
     */
    private function generate_estimated_vtt($text, $duration) {
        $vtt_content = '';
        
        // Average reading speed: ~150 words per minute = 2.5 words per second
        // Average speaking speed: ~150 words per minute = 2.5 words per second
        $words_per_second = 2.5;
        
        // Split text into sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $current_time = 0;
        $chunk_duration = 5; // Show each subtitle for ~5 seconds
        
        $chunk = '';
        $chunk_start = 0;
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) continue;
            
            $word_count = str_word_count($sentence);
            $sentence_duration = $word_count / $words_per_second;
            
            // If adding this sentence would exceed chunk duration, save current chunk
            if (!empty($chunk) && ($current_time - $chunk_start + $sentence_duration) > $chunk_duration) {
                $chunk_end = min($chunk_start + $chunk_duration, $duration);
                $vtt_content .= $this->format_timestamp($chunk_start) . " --> " . $this->format_timestamp($chunk_end) . "\n";
                $vtt_content .= trim($chunk) . "\n\n";
                
                $chunk = $sentence . ' ';
                $chunk_start = $current_time;
            } else {
                $chunk .= $sentence . ' ';
            }
            
            $current_time += $sentence_duration;
            
            // Don't exceed video duration
            if ($current_time >= $duration) {
                break;
            }
        }
        
        // Add remaining chunk
        if (!empty($chunk)) {
            $chunk_end = min($chunk_start + $chunk_duration, $duration);
            $vtt_content .= $this->format_timestamp($chunk_start) . " --> " . $this->format_timestamp($chunk_end) . "\n";
            $vtt_content .= trim($chunk) . "\n\n";
        }
        
        return $vtt_content;
    }
    
    /**
     * Format timestamp for VTT (HH:MM:SS.mmm)
     */
    private function format_timestamp($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = (float)$seconds % 60;
        $milliseconds = floor(($secs - floor($secs)) * 1000);
        $secs = floor($secs);
        
        return sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, $secs, $milliseconds);
    }
}

