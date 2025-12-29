<?php
/**
 * VTT Parser Class
 * 
 * Parses WebVTT files to extract timestamped segments
 */

if (!defined('ABSPATH')) {
    exit;
}

class Transcription_VTT_Parser {
    
    /**
     * Parse VTT file and extract segments
     * 
     * @param string $vtt_content VTT file content
     * @return array Array of segments with start, end, and text
     */
    public function parse_vtt($vtt_content) {
        $segments = array();
        
        // Remove WEBVTT header and any metadata
        $lines = explode("\n", $vtt_content);
        $in_cue = false;
        $current_start = null;
        $current_end = null;
        $current_text = array();
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and WEBVTT header
            if (empty($line) || $line === 'WEBVTT') {
                if ($in_cue && $current_start !== null && $current_end !== null && !empty($current_text)) {
                    // Save previous cue
                    $segments[] = array(
                        'start' => $current_start,
                        'end' => $current_end,
                        'text' => implode(' ', $current_text)
                    );
                    $current_text = array();
                    $in_cue = false;
                }
                continue;
            }
            
            // Check if line is a timestamp (format: HH:MM:SS.mmm --> HH:MM:SS.mmm)
            if (preg_match('/^(\d{2}):(\d{2}):(\d{2})\.(\d{3})\s*-->\s*(\d{2}):(\d{2}):(\d{2})\.(\d{3})/', $line, $matches)) {
                // Save previous cue if exists
                if ($in_cue && $current_start !== null && $current_end !== null && !empty($current_text)) {
                    $segments[] = array(
                        'start' => $current_start,
                        'end' => $current_end,
                        'text' => implode(' ', $current_text)
                    );
                    $current_text = array();
                }
                
                // Parse timestamps
                $start_hours = intval($matches[1]);
                $start_minutes = intval($matches[2]);
                $start_seconds = intval($matches[3]);
                $start_millis = intval($matches[4]);
                $current_start = $start_hours * 3600 + $start_minutes * 60 + $start_seconds + ($start_millis / 1000);
                
                $end_hours = intval($matches[5]);
                $end_minutes = intval($matches[6]);
                $end_seconds = intval($matches[7]);
                $end_millis = intval($matches[8]);
                $current_end = $end_hours * 3600 + $end_minutes * 60 + $end_seconds + ($end_millis / 1000);
                
                $in_cue = true;
            } elseif ($in_cue && !empty($line)) {
                // This is text content for the current cue
                $current_text[] = $line;
            }
        }
        
        // Save last cue
        if ($in_cue && $current_start !== null && $current_end !== null && !empty($current_text)) {
            $segments[] = array(
                'start' => $current_start,
                'end' => $current_end,
                'text' => implode(' ', $current_text)
            );
        }
        
        return $segments;
    }
    
    /**
     * Parse VTT file from path
     * 
     * @param string $vtt_path Full path to VTT file
     * @return array|false Array of segments or false on error
     */
    public function parse_vtt_file($vtt_path) {
        if (!file_exists($vtt_path)) {
            return false;
        }
        
        $vtt_content = file_get_contents($vtt_path);
        if ($vtt_content === false) {
            return false;
        }
        
        return $this->parse_vtt($vtt_content);
    }
}

