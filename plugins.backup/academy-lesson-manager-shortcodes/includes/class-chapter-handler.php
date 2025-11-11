<?php
/**
 * Chapter Handler for ALM Shortcodes
 * Handles chapter ID resolution and navigation logic
 */

class ALM_Chapter_Handler {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
     * Get chapter ID based on lesson ID and optional chapter slug
     * 
     * @param int $lesson_id The lesson ID
     * @param string $chapter_slug Optional chapter slug from URL parameter
     * @return array Array with chapter_id, current_chapter_num, next_chapter_slug
     */
    public function get_chapter_data($lesson_id, $chapter_slug = null) {
        // Validate lesson_id
        if (empty($lesson_id) || !is_numeric($lesson_id)) {
            return $this->get_error_response('Invalid lesson ID');
        }
        
        // Get all chapters for this lesson, ordered by menu_order
        $chapters = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT ID, slug, chapter_title, menu_order 
             FROM {$this->wpdb->prefix}alm_chapters 
             WHERE lesson_id = %d 
             ORDER BY menu_order ASC",
            $lesson_id
        ));
        
        if (empty($chapters)) {
            return $this->get_error_response('No chapters found for this lesson');
        }
        
        // If only one chapter, return it
        if (count($chapters) === 1) {
            return array(
                'chapter_id' => $chapters[0]->ID,
                'current_chapter_num' => 1,
                'next_chapter_slug' => null,
                'total_chapters' => 1,
                'current_chapter' => $chapters[0],
                'success' => true
            );
        }
        
        // If chapter slug provided, find specific chapter
        if (!empty($chapter_slug)) {
            return $this->find_chapter_by_slug($chapters, $chapter_slug);
        }
        
        // Default: return first chapter
        return array(
            'chapter_id' => $chapters[0]->ID,
            'current_chapter_num' => 1,
            'next_chapter_slug' => isset($chapters[1]) ? $chapters[1]->slug : null,
            'total_chapters' => count($chapters),
            'current_chapter' => $chapters[0],
            'success' => true
        );
    }
    
    /**
     * Find chapter by slug and return navigation data
     */
    private function find_chapter_by_slug($chapters, $chapter_slug) {
        $current_chapter_num = 1;
        $current_chapter = null;
        $next_chapter_slug = null;
        
        foreach ($chapters as $index => $chapter) {
            if ($chapter->slug === $chapter_slug) {
                $current_chapter = $chapter;
                $current_chapter_num = $index + 1;
                
                // Get next chapter slug
                if (isset($chapters[$index + 1])) {
                    $next_chapter_slug = $chapters[$index + 1]->slug;
                }
                break;
            }
        }
        
        if (!$current_chapter) {
            return $this->get_error_response('Chapter not found');
        }
        
        return array(
            'chapter_id' => $current_chapter->ID,
            'current_chapter_num' => $current_chapter_num,
            'next_chapter_slug' => $next_chapter_slug,
            'total_chapters' => count($chapters),
            'current_chapter' => $current_chapter,
            'success' => true
        );
    }
    
    /**
     * Get chapter navigation (previous/next)
     */
    public function get_chapter_navigation($lesson_id, $current_chapter_id) {
        $chapters = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT ID, slug, chapter_title, menu_order 
             FROM {$this->wpdb->prefix}alm_chapters 
             WHERE lesson_id = %d 
             ORDER BY menu_order ASC",
            $lesson_id
        ));
        
        $navigation = array(
            'previous' => null,
            'next' => null,
            'current_index' => -1
        );
        
        foreach ($chapters as $index => $chapter) {
            if ($chapter->ID == $current_chapter_id) {
                $navigation['current_index'] = $index;
                
                // Previous chapter
                if ($index > 0) {
                    $navigation['previous'] = array(
                        'id' => $chapters[$index - 1]->ID,
                        'slug' => $chapters[$index - 1]->slug,
                        'title' => $chapters[$index - 1]->chapter_title
                    );
                }
                
                // Next chapter
                if ($index < count($chapters) - 1) {
                    $navigation['next'] = array(
                        'id' => $chapters[$index + 1]->ID,
                        'slug' => $chapters[$index + 1]->slug,
                        'title' => $chapters[$index + 1]->chapter_title
                    );
                }
                break;
            }
        }
        
        return $navigation;
    }
    
    /**
     * Get all chapters for a lesson with metadata
     */
    public function get_lesson_chapters($lesson_id) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT ID, slug, chapter_title, menu_order, duration, bunny_url, youtube_id, vimeo_id
             FROM {$this->wpdb->prefix}alm_chapters 
             WHERE lesson_id = %d 
             ORDER BY menu_order ASC",
            $lesson_id
        ));
    }
    
    /**
     * Get error response
     */
    private function get_error_response($message) {
        return array(
            'chapter_id' => null,
            'current_chapter_num' => 0,
            'next_chapter_slug' => null,
            'total_chapters' => 0,
            'current_chapter' => null,
            'error' => $message,
            'success' => false
        );
    }
}
