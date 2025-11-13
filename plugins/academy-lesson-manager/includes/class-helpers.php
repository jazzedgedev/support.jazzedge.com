<?php
/**
 * Helper Functions Class
 * 
 * Utility functions for Academy Lesson Manager
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Helpers {
    
    /**
     * Format duration in seconds to HH:MM:SS format
     * 
     * @param int $seconds Duration in seconds
     * @return string Formatted duration
     */
    public static function format_duration($seconds) {
        if (empty($seconds) || $seconds <= 0) {
            return '00:00:00';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    
    /**
     * Format duration in seconds to shorter format (MM:SS or H:MM:SS)
     * 
     * @param int $seconds Duration in seconds
     * @return string Formatted duration (e.g., "5:30" or "1:05:30")
     */
    public static function format_duration_short($seconds) {
        if (empty($seconds) || $seconds <= 0) {
            return '0:00';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        } else {
            return sprintf('%d:%02d', $minutes, $secs);
        }
    }
    
    /**
     * Get collection title by collection ID
     * 
     * @param int $collection_id Collection ID
     * @return string Collection title
     */
    public static function get_collection_title($collection_id) {
        if (empty($collection_id)) {
            return 'No Collection';
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('collections');
        
        $title = $wpdb->get_var($wpdb->prepare(
            "SELECT collection_title FROM $table_name WHERE ID = %d",
            $collection_id
        ));
        
        return $title ? stripslashes($title) : 'Unknown Collection';
    }
    
    /**
     * Get course title by course ID (deprecated - use get_collection_title)
     * 
     * @param int $course_id Course ID
     * @return string Course title
     */
    public static function get_course_title($course_id) {
        return self::get_collection_title($course_id);
    }
    
    /**
     * Get lesson title by lesson ID
     * 
     * @param int $lesson_id Lesson ID
     * @return string Lesson title
     */
    public static function get_lesson_title($lesson_id) {
        if (empty($lesson_id)) {
            return 'No Lesson';
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('lessons');
        
        $title = $wpdb->get_var($wpdb->prepare(
            "SELECT lesson_title FROM $table_name WHERE ID = %d",
            $lesson_id
        ));
        
        return $title ? stripslashes($title) : 'Unknown Lesson';
    }
    
    /**
     * Format serialized resources for display
     * 
     * @param string $resources Serialized resources string
     * @return array Formatted resources array
     */
    public static function format_serialized_resources($resources) {
        if (empty($resources)) {
            return array();
        }
        
        $unserialized = maybe_unserialize($resources);
        
        if (!is_array($unserialized)) {
            return array();
        }
        
        $formatted = array();
        foreach ($unserialized as $type => $value) {
            if (!empty($value)) {
                // Handle both old format (string URL) and new format (array with url, attachment_id, label)
                if (is_array($value)) {
                    $url = isset($value['url']) ? $value['url'] : '';
                    $attachment_id = isset($value['attachment_id']) ? $value['attachment_id'] : 0;
                    $label = isset($value['label']) ? $value['label'] : '';
                    
                    // Safety check: if url is still an array, try to extract from it
                    if (is_array($url)) {
                        $url = isset($url['url']) ? $url['url'] : (isset($url[0]) ? $url[0] : '');
                    }
                } else {
                    $url = $value;
                    $attachment_id = 0;
                    $label = '';
                }
                
                // Ensure url is a string before proceeding
                if (!empty($url) && is_string($url)) {
                    $formatted[] = array(
                        'type' => $type,
                        'url' => $url,
                        'attachment_id' => $attachment_id,
                        'label' => $label,
                        'display_url' => self::format_resource_url($url, $type)
                    );
                }
            }
        }
        
        return $formatted;
    }
    
    /**
     * Format resource URL for display
     * 
     * @param string $url Resource URL
     * @param string $type Resource type
     * @return string Formatted URL
     */
    private static function format_resource_url($url, $type) {
        // Safety check: ensure we have a string
        if (!is_string($url)) {
            // If it's an array, try to extract URL
            if (is_array($url)) {
                $url = isset($url['url']) ? $url['url'] : (isset($url[0]) ? $url[0] : '');
            } else {
                $url = (string) $url;
            }
        }
        
        // If still not a string or empty, return empty string
        if (!is_string($url) || empty($url)) {
            return '';
        }
        
        // Handle different URL formats based on type
        if (strpos($url, 'https://s3.amazonaws.com/jazzedge-resources/') === 0) {
            return $url;
        } elseif (strpos($url, '/jazzedge') === 0) {
            return 'https://jazzedge.com' . $url;
        } else {
            return $url;
        }
    }
    
    /**
     * Sanitize text field
     * 
     * @param string $text Text to sanitize
     * @return string Sanitized text
     */
    public static function sanitize_text($text) {
        return sanitize_text_field($text);
    }
    
    /**
     * Sanitize textarea content
     * 
     * @param string $content Content to sanitize
     * @return string Sanitized content
     */
    public static function sanitize_textarea($content) {
        return sanitize_textarea_field($content);
    }
    
    /**
     * Clean HTML and WordPress block comments from content
     * Removes WordPress Gutenberg block comments and HTML tags, keeping plain text
     * 
     * @param string $content Content to clean
     * @return string Cleaned plain text content
     */
    public static function clean_html_content($content) {
        if (empty($content)) {
            return '';
        }
        
        // Remove WordPress block comments (e.g., <!-- wp:paragraph -->, <!-- /wp:paragraph -->)
        $content = preg_replace('/<!--\s*wp:[^>]*-->/i', '', $content);
        $content = preg_replace('/<!--\s*\/wp:[^>]*-->/i', '', $content);
        
        // Remove all HTML tags but keep the text content
        $content = strip_tags($content);
        
        // Decode HTML entities
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Clean up extra whitespace (multiple spaces, newlines, etc.)
        $content = preg_replace('/[ \t]+/', ' ', $content); // Multiple spaces to single space
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content); // Multiple newlines to double newline
        $content = trim($content);
        
        return $content;
    }
    
    /**
     * Get site options for dropdown
     * 
     * @return array Site options
     */
    public static function get_site_options() {
        return array(
            'JE' => 'JazzEdge',
            'JPD' => 'Jazz Piano Department',
            'SPJ' => 'Smooth Piano Jazz',
            'JCM' => 'Jazz Chord Mastery',
            'TTM' => 'The Theory Master',
            'CPL' => 'Contemporary Piano Lessons',
            'FPL' => 'Free Piano Lessons',
            'RPL' => 'Rock Piano Lessons',
            'PBP' => 'Piano By Pattern',
            'JPT' => 'Jazz Piano Techniques',
            'MTO' => 'Music Theory Online'
        );
    }
    
    /**
     * Get success style options
     * 
     * @return array Success style options
     */
    public static function get_success_style_options() {
        return array(
            '' => 'N/A',
            'basics' => 'Basics',
            'rock' => 'Rock',
            'standards' => 'Standards',
            'improvisation' => 'Improvisation',
            'blues' => 'Blues'
        );
    }
    
    /**
     * Get skill level options
     * 
     * @return array Skill level options
     */
    public static function get_skill_level_options() {
        return array(
            '' => 'Choose Skill Level...',
            'N/A' => 'N/A',
            'Beginner' => 'Beginner',
            'Intermediate' => 'Intermediate',
            'Advanced' => 'Advanced',
            'Professional' => 'Professional'
        );
    }
    
    /**
     * Format date for display
     * 
     * @param string $date Date string
     * @return string Formatted date
     */
    public static function format_date($date) {
        if (empty($date) || $date === '0000-00-00') {
            return 'Not set';
        }
        
        return date('M j, Y', strtotime($date));
    }
    
    /**
     * Get yes/no options
     * 
     * @return array Yes/No options
     */
    public static function get_yes_no_options() {
        return array(
            '' => 'Choose...',
            'y' => 'Yes',
            'n' => 'No'
        );
    }
    
    /**
     * Count lessons in a collection
     * 
     * @param int $collection_id Collection ID
     * @return int Number of lessons
     */
    public static function count_collection_lessons($collection_id) {
        if (empty($collection_id)) {
            return 0;
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('lessons');
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE collection_id = %d",
            $collection_id
        ));
    }
    
    /**
     * Count lessons in a course (deprecated - use count_collection_lessons)
     * 
     * @param int $course_id Course ID
     * @return int Number of lessons
     */
    public static function count_course_lessons($course_id) {
        return self::count_collection_lessons($course_id);
    }
    
    /**
     * Count chapters in a lesson
     * 
     * @param int $lesson_id Lesson ID
     * @return int Number of chapters
     */
    public static function count_lesson_chapters($lesson_id) {
        if (empty($lesson_id)) {
            return 0;
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('chapters');
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE lesson_id = %d",
            $lesson_id
        ));
    }
}
