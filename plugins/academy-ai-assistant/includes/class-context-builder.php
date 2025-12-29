<?php
/**
 * Context Builder Class for Academy AI Assistant
 * 
 * Gathers relevant context from existing plugins to provide personalized AI assistance
 * Security: Read-only access, validates user_id, only accesses current user's data
 * Performance: Caches context data for 5 minutes
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Context_Builder {
    
    private $cache_group = 'aaa_context';
    private $cache_duration = 300; // 5 minutes
    
    /**
     * Build context for AI based on user and query
     * 
     * @param int $user_id User ID
     * @param string $query User's query/question
     * @param array $additional_context Additional context (e.g., current lesson, page context)
     * @return array Context data array
     */
    public function build_context($user_id, $query = '', $additional_context = array()) {
        // Security: Validate user_id
        $user_id = absint($user_id);
        if (!$user_id || !get_userdata($user_id)) {
            return array();
        }
        
        // Security: Only access data for specified user
        if ($user_id !== get_current_user_id()) {
            return array();
        }
        
        // Performance: Check cache first
        $cache_key = 'user_' . $user_id . '_' . md5($query);
        $cached = wp_cache_get($cache_key, $this->cache_group);
        if ($cached !== false) {
            return $cached;
        }
        
        $context = array(
            'user_id' => $user_id,
            'query' => sanitize_text_field($query),
            'timestamp' => current_time('mysql'),
            'lesson_data' => $this->get_lesson_data($user_id),
            'practice_data' => $this->get_practice_data($user_id),
            'favorites' => $this->get_user_favorites($user_id),
            'membership_level' => $this->get_user_membership_level($user_id),
            'current_page' => $additional_context
        );
        
        // Performance: Cache the context
        wp_cache_set($cache_key, $context, $this->cache_group, $this->cache_duration);
        
        return $context;
    }
    
    /**
     * Get lesson data for user
     * Safety: Read-only, checks class existence
     * 
     * @param int $user_id User ID
     * @return array Lesson data
     */
    private function get_lesson_data($user_id) {
        $data = array(
            'completed_lessons' => array(),
            'favorite_lessons' => array(),
            'library_lessons' => array(),
            'recent_lessons' => array()
        );
        
        // Safety: Check if ALM_Database class exists
        if (!class_exists('ALM_Database')) {
            return $data;
        }
        
        global $wpdb;
        
        // Get user's favorite lessons (from jph_lesson_favorites table)
        $favorites_table = $wpdb->prefix . 'jph_lesson_favorites';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$favorites_table}'") == $favorites_table) {
            $favorites = $wpdb->get_results($wpdb->prepare(
                "SELECT title, url FROM {$favorites_table} 
                 WHERE user_id = %d 
                 ORDER BY updated_at DESC 
                 LIMIT 20",
                $user_id
            ), ARRAY_A);
            
            $data['favorite_lessons'] = $favorites ? $favorites : array();
        }
        
        // Get user's library lessons (Essentials selections)
        if (class_exists('ALM_Essentials_Library')) {
            $library = new ALM_Essentials_Library();
            $library_lessons = $library->get_user_library($user_id);
            
            if ($library_lessons) {
                $data['library_lessons'] = array_map(function($lesson) {
                    return array(
                        'id' => $lesson->ID,
                        'title' => $lesson->lesson_title,
                        'description' => $lesson->lesson_description,
                        'selected_at' => $lesson->selected_at
                    );
                }, $library_lessons);
            }
        }
        
        return $data;
    }
    
    /**
     * Get practice data for user
     * Safety: Read-only, checks class existence
     * 
     * @param int $user_id User ID
     * @return array Practice data
     */
    private function get_practice_data($user_id) {
        $data = array(
            'stats' => array(),
            'recent_sessions' => array(),
            'current_assignment' => null,
            'progress' => array()
        );
        
        // Safety: Check if JPH_Database class exists
        if (!class_exists('JPH_Database')) {
            return $data;
        }
        
        $database = new JPH_Database();
        
        // Get user stats
        $stats = $database->get_user_stats($user_id);
        if ($stats) {
            $data['stats'] = array(
                'total_xp' => $stats['total_xp'],
                'current_level' => $stats['current_level'],
                'total_sessions' => $stats['total_sessions'],
                'total_minutes' => $stats['total_minutes'],
                'current_streak' => $stats['current_streak'],
                'last_practice_date' => $stats['last_practice_date']
            );
        }
        
        // Get recent practice sessions
        $sessions = $database->get_practice_sessions($user_id, 10, 0);
        if ($sessions) {
            $data['recent_sessions'] = array_slice($sessions, 0, 5); // Last 5 sessions
        }
        
        // Get current JPC assignment
        if (class_exists('JPH_JPC_Handler')) {
            $assignment = JPH_JPC_Handler::get_user_current_assignment($user_id);
            if ($assignment) {
                $data['current_assignment'] = array(
                    'focus_title' => $assignment['focus_title'],
                    'focus_order' => $assignment['focus_order'],
                    'key_sig' => $assignment['key_sig']
                );
            }
        }
        
        return $data;
    }
    
    /**
     * Get user's favorite lessons/resources
     * 
     * @param int $user_id User ID
     * @return array Favorites
     */
    private function get_user_favorites($user_id) {
        global $wpdb;
        
        $favorites_table = $wpdb->prefix . 'jph_lesson_favorites';
        
        // Safety: Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$favorites_table}'") != $favorites_table) {
            return array();
        }
        
        $favorites = $wpdb->get_results($wpdb->prepare(
            "SELECT title, url, category, resource_type 
             FROM {$favorites_table} 
             WHERE user_id = %d 
             ORDER BY updated_at DESC 
             LIMIT 10",
            $user_id
        ), ARRAY_A);
        
        return $favorites ? $favorites : array();
    }
    
    /**
     * Get user's membership level
     * 
     * @param int $user_id User ID
     * @return int Membership level (0=Free, 1=Essentials, 2=Studio, 3=Premier)
     */
    private function get_user_membership_level($user_id) {
        // Use filter if available (from academy-lesson-manager)
        if (has_filter('alm_get_current_user_membership_level')) {
            return apply_filters('alm_get_current_user_membership_level', 0);
        }
        
        // Default to 0 (Free) if no filter available
        return 0;
    }
    
    /**
     * Get current lesson/chapter context if user is on a lesson page
     * 
     * @return array Current page context
     */
    public function get_current_page_context() {
        global $post;
        
        $context = array(
            'is_lesson_page' => false,
            'lesson_id' => null,
            'chapter_id' => null,
            'lesson_title' => null
        );
        
        // Check if we're on a lesson page
        // This would need to be customized based on your lesson page structure
        if (is_singular() && $post) {
            // Check if post has lesson meta or is a lesson post type
            $lesson_id = get_post_meta($post->ID, 'lesson_id', true);
            if ($lesson_id) {
                $context['is_lesson_page'] = true;
                $context['lesson_id'] = absint($lesson_id);
                $context['lesson_title'] = get_the_title($post->ID);
                
                // Try to get chapter ID from URL or meta
                $chapter_id = get_post_meta($post->ID, 'chapter_id', true);
                if ($chapter_id) {
                    $context['chapter_id'] = absint($chapter_id);
                }
            }
        }
        
        return $context;
    }
    
    /**
     * Format context for AI prompt
     * Limits context size to prevent token overflow
     * 
     * @param array $context Context data
     * @param int $max_tokens Maximum tokens for context (default 2000)
     * @return string Formatted context string
     */
    public function format_context_for_ai($context, $max_tokens = 2000) {
        $formatted = array();
        
        // User info
        if (!empty($context['user_id'])) {
            $formatted[] = "User ID: " . $context['user_id'];
        }
        
        // Membership level
        if (isset($context['membership_level'])) {
            $levels = array(0 => 'Free', 1 => 'Essentials', 2 => 'Studio', 3 => 'Premier');
            $level_name = isset($levels[$context['membership_level']]) ? $levels[$context['membership_level']] : 'Unknown';
            $formatted[] = "Membership: " . $level_name;
        }
        
        // Practice stats
        if (!empty($context['practice_data']['stats'])) {
            $stats = $context['practice_data']['stats'];
            $formatted[] = "Practice Stats: Level " . $stats['current_level'] . ", " . $stats['total_sessions'] . " sessions, " . $stats['total_minutes'] . " minutes practiced";
            if ($stats['current_streak'] > 0) {
                $formatted[] = "Current streak: " . $stats['current_streak'] . " days";
            }
        }
        
        // Current assignment
        if (!empty($context['practice_data']['current_assignment'])) {
            $assign = $context['practice_data']['current_assignment'];
            $formatted[] = "Current Practice Focus: " . $assign['focus_title'] . " in key of " . $assign['key_sig'];
        }
        
        // Favorite lessons (limit to 5)
        if (!empty($context['favorites'])) {
            $fav_titles = array_slice(array_column($context['favorites'], 'title'), 0, 5);
            if (!empty($fav_titles)) {
                $formatted[] = "Favorite lessons: " . implode(', ', $fav_titles);
            }
        }
        
        // Library lessons (limit to 3)
        if (!empty($context['lesson_data']['library_lessons'])) {
            $lib_titles = array_slice(array_column($context['lesson_data']['library_lessons'], 'title'), 0, 3);
            if (!empty($lib_titles)) {
                $formatted[] = "Library lessons: " . implode(', ', $lib_titles);
            }
        }
        
        // Current page context
        if (!empty($context['current_page']['is_lesson_page'])) {
            $formatted[] = "Currently viewing: " . $context['current_page']['lesson_title'];
        }
        
        $context_string = implode("\n", $formatted);
        
        // Rough token estimation (1 token ≈ 4 characters)
        $estimated_tokens = strlen($context_string) / 4;
        
        if ($estimated_tokens > $max_tokens) {
            // Truncate if too long
            $max_chars = $max_tokens * 4;
            $context_string = substr($context_string, 0, $max_chars) . '...';
        }
        
        return $context_string;
    }
}

