<?php
/**
 * Keyword Manager Class for Academy AI Assistant
 * 
 * Manages keyword-to-lesson mappings for prioritized recommendations
 */

if (!defined('ABSPATH')) {
    exit;
}

class AAA_Keyword_Manager {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'aaa_keyword_lessons';
    }
    
    /**
     * Get lessons for a keyword
     * 
     * @param string $keyword Keyword to search for
     * @return array Array of lesson IDs with priority
     */
    public function get_lessons_for_keyword($keyword) {
        global $wpdb;
        
        if (empty($keyword)) {
            return array();
        }
        
        // Normalize keyword (lowercase, trim)
        $keyword = strtolower(trim($keyword));
        
        // Check for exact match first
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT lesson_id, priority 
             FROM {$this->table_name} 
             WHERE keyword = %s 
             ORDER BY priority ASC, id ASC",
            $keyword
        ), ARRAY_A);
        
        if (!empty($results)) {
            return array_map(function($row) {
                return array(
                    'lesson_id' => absint($row['lesson_id']),
                    'priority' => absint($row['priority'])
                );
            }, $results);
        }
        
        // Check for partial match (keyword contains or is contained)
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT lesson_id, priority 
             FROM {$this->table_name} 
             WHERE keyword LIKE %s OR %s LIKE CONCAT('%%', keyword, '%%')
             ORDER BY priority ASC, id ASC
             LIMIT 10",
            '%' . $wpdb->esc_like($keyword) . '%',
            $keyword
        ), ARRAY_A);
        
        return array_map(function($row) {
            return array(
                'lesson_id' => absint($row['lesson_id']),
                'priority' => absint($row['priority'])
            );
        }, $results);
    }
    
    /**
     * Get all keywords for a lesson
     * 
     * @param int $lesson_id Lesson ID
     * @return array Array of keywords
     */
    public function get_keywords_for_lesson($lesson_id) {
        global $wpdb;
        
        $lesson_id = absint($lesson_id);
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT keyword, priority 
             FROM {$this->table_name} 
             WHERE lesson_id = %d 
             ORDER BY priority ASC, keyword ASC",
            $lesson_id
        ), ARRAY_A);
        
        return array_map(function($row) {
            return array(
                'keyword' => $row['keyword'],
                'priority' => absint($row['priority'])
            );
        }, $results);
    }
    
    /**
     * Add keyword mapping
     * 
     * @param string $keyword Keyword
     * @param int $lesson_id Lesson ID
     * @param int $priority Priority (lower = higher priority, default 10)
     * @return int|WP_Error Mapping ID or error
     */
    public function add_keyword($keyword, $lesson_id, $priority = 10) {
        global $wpdb;
        
        $keyword = strtolower(trim($keyword));
        $lesson_id = absint($lesson_id);
        $priority = absint($priority);
        
        if (empty($keyword)) {
            return new WP_Error('invalid_keyword', 'Keyword cannot be empty');
        }
        
        if ($lesson_id === 0) {
            return new WP_Error('invalid_lesson', 'Invalid lesson ID');
        }
        
        // Check if mapping already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE keyword = %s AND lesson_id = %d",
            $keyword,
            $lesson_id
        ));
        
        if ($existing) {
            // Update existing
            $updated = $wpdb->update(
                $this->table_name,
                array('priority' => $priority),
                array('id' => $existing),
                array('%d'),
                array('%d')
            );
            
            return $updated !== false ? $existing : new WP_Error('update_failed', 'Failed to update keyword mapping');
        }
        
        // Insert new
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'keyword' => $keyword,
                'lesson_id' => $lesson_id,
                'priority' => $priority
            ),
            array('%s', '%d', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to add keyword mapping');
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Delete keyword mapping
     * 
     * @param int $mapping_id Mapping ID
     * @return bool Success
     */
    public function delete_keyword($mapping_id) {
        global $wpdb;
        
        $mapping_id = absint($mapping_id);
        
        $deleted = $wpdb->delete(
            $this->table_name,
            array('id' => $mapping_id),
            array('%d')
        );
        
        return $deleted !== false;
    }
    
    /**
     * Delete keyword mapping by keyword and lesson
     * 
     * @param string $keyword Keyword
     * @param int $lesson_id Lesson ID
     * @return bool Success
     */
    public function delete_keyword_by_lesson($keyword, $lesson_id) {
        global $wpdb;
        
        $keyword = strtolower(trim($keyword));
        $lesson_id = absint($lesson_id);
        
        $deleted = $wpdb->delete(
            $this->table_name,
            array(
                'keyword' => $keyword,
                'lesson_id' => $lesson_id
            ),
            array('%s', '%d')
        );
        
        return $deleted !== false;
    }
    
    /**
     * Get all keyword mappings (for admin)
     * 
     * @param int $page Page number
     * @param int $per_page Results per page
     * @return array Mappings with lesson info
     */
    public function get_all_mappings($page = 1, $per_page = 50) {
        global $wpdb;
        
        $page = absint($page);
        $per_page = min(absint($per_page), 100);
        $offset = ($page - 1) * $per_page;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT k.id, k.keyword, k.lesson_id, k.priority, k.created_at, k.updated_at,
                    l.lesson_title, l.post_id, l.slug
             FROM {$this->table_name} k
             LEFT JOIN {$wpdb->prefix}alm_lessons l ON k.lesson_id = l.ID
             ORDER BY k.priority ASC, k.keyword ASC, k.id ASC
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ), ARRAY_A);
        
        return $results ? $results : array();
    }
    
    /**
     * Get total count of mappings
     * 
     * @return int Count
     */
    public function get_total_count() {
        global $wpdb;
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        return absint($count);
    }
    
    /**
     * Search keywords in user query
     * Extracts potential keywords from query
     * 
     * @param string $query User query
     * @return array Array of potential keywords found
     */
    public function find_keywords_in_query($query) {
        global $wpdb;
        
        if (empty($query)) {
            return array();
        }
        
        // Get all keywords from database
        $all_keywords = $wpdb->get_col("SELECT DISTINCT keyword FROM {$this->table_name}");
        
        if (empty($all_keywords)) {
            return array();
        }
        
        $query_lower = strtolower($query);
        $found_keywords = array();
        
        // Check each keyword
        foreach ($all_keywords as $keyword) {
            // Check if keyword appears in query (word boundary match)
            if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $query_lower)) {
                $found_keywords[] = $keyword;
            }
        }
        
        return $found_keywords;
    }
}

