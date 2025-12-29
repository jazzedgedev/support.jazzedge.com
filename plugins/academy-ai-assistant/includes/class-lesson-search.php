<?php
/**
 * Lesson Search Class for Academy AI Assistant
 * 
 * Searches the lesson database directly by title/keywords
 * Complements embedding search with direct database queries
 */

if (!defined('ABSPATH')) {
    exit;
}

class AAA_Lesson_Search {
    
    private $cache_group = 'aaa_lesson_search';
    private $cache_duration = 300; // 5 minutes
    private $keyword_manager;
    private $debug_logger;
    
    public function __construct($debug_logger = null) {
        $this->keyword_manager = new AAA_Keyword_Manager();
        $this->debug_logger = $debug_logger;
    }
    
    /**
     * Search lessons by title/keywords
     * 
     * @param string $query Search query
     * @param int $limit Number of results
     * @param string $lesson_style Optional style filter (e.g., "Blues")
     * @param string $lesson_level Optional level filter (e.g., "beginner")
     * @param string $lesson_tag Optional tag filter
     * @return array Array of lessons with URLs
     */
    public function search($query, $limit = 10, $lesson_style = '', $lesson_level = '', $lesson_tag = '') {
        // Safety: Check if ALM_Database class exists
        if (!class_exists('ALM_Database')) {
            return array();
        }
        
        // Performance: Check cache first
        $cache_key = 'lesson_search_' . md5($query . $limit);
        $cached = wp_cache_get($cache_key, $this->cache_group);
        if ($cached !== false) {
            return $cached;
        }
        
        $results = array();
        
        // STEP 1: Check for keyword mappings first (highest priority)
        $keyword_matches = $this->keyword_manager->find_keywords_in_query($query);
        if (!empty($keyword_matches)) {
            // Log keyword search SQL
            if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                global $wpdb;
                $keyword_table = $wpdb->prefix . 'aaa_keyword_lessons';
                $this->debug_logger->log('keyword_search_sql', 'Keyword search executed', array(
                    'query' => $query,
                    'keywords_found' => $keyword_matches,
                    'sql_example' => "SELECT keyword, lesson_id, priority FROM {$keyword_table} WHERE keyword IN (" . implode(',', array_map(function($k) { return "'" . esc_sql($k) . "'"; }, $keyword_matches)) . ")"
                ));
            }
            
            foreach ($keyword_matches as $keyword) {
                $mapped_lessons = $this->keyword_manager->get_lessons_for_keyword($keyword);
                foreach ($mapped_lessons as $mapping) {
                    $lesson_result = $this->get_lesson_by_id($mapping['lesson_id']);
                    if (!empty($lesson_result)) {
                        // Mark as keyword-matched and set priority
                        $lesson_result['match_type'] = 'keyword_match';
                        $lesson_result['keyword'] = $keyword;
                        $lesson_result['priority'] = $mapping['priority'];
                        $results[] = $lesson_result;
                    }
                }
            }
        }
        
        // STEP 2: Regular database search (if we haven't hit limit)
        global $wpdb;
        $alm_db = new ALM_Database();
        $lessons_table = $alm_db->get_table_name('lessons');
        
        // Extract keywords and phrases from query
        $extracted = $this->extract_keywords($query);
        $phrases = isset($extracted['phrases']) ? $extracted['phrases'] : array();
        $keywords = isset($extracted['keywords']) ? $extracted['keywords'] : array();
        
        // If no keywords or phrases and no results, return empty
        if (empty($phrases) && empty($keywords) && empty($results)) {
            wp_cache_set($cache_key, array(), $this->cache_group, $this->cache_duration);
            return array();
        }
        
        // Only do database search if we need more results
        if (count($results) < $limit) {
            // STEP 2a: Search for phrases first (highest priority)
            if (!empty($phrases)) {
                foreach ($phrases as $phrase) {
                    if (count($results) >= $limit) {
                        break;
                    }
                    
                    $escaped_phrase = '%' . $wpdb->esc_like($phrase) . '%';
                    
                    // Search for exact phrase match in title (highest priority)
                    $phrase_sql = $wpdb->prepare(
                        "SELECT DISTINCT l.ID, l.lesson_title, l.post_id, l.slug, l.lesson_description, l.lesson_level, l.lesson_tags, l.lesson_style
                         FROM {$lessons_table} l
                         WHERE (LOWER(l.lesson_title) LIKE LOWER(%s) OR LOWER(l.lesson_description) LIKE LOWER(%s) OR LOWER(l.lesson_tags) LIKE LOWER(%s) OR LOWER(l.lesson_style) LIKE LOWER(%s))
                         AND l.post_id > 0
                         AND (l.status = 'published' OR l.status IS NULL)
                         ORDER BY 
                            CASE WHEN LOWER(l.lesson_title) LIKE LOWER(%s) THEN 1 ELSE 2 END,
                            l.lesson_title ASC
                         LIMIT 5",
                        $escaped_phrase, $escaped_phrase, $escaped_phrase, $escaped_phrase, $escaped_phrase
                    );
                    
                    $phrase_lessons = $wpdb->get_results($phrase_sql, ARRAY_A);
                    
                    if (!empty($phrase_lessons)) {
                        foreach ($phrase_lessons as $lesson) {
                            // Skip if already in results
                            $already_included = false;
                            foreach ($results as $existing) {
                                if ($existing['lesson_id'] == absint($lesson['ID'])) {
                                    $already_included = true;
                                    break;
                                }
                            }
                            
                            if ($already_included) {
                                continue;
                            }
                            
                            $post_id = absint($lesson['post_id']);
                            $post = get_post($post_id);
                            
                            if ($post && in_array($post->post_status, array('publish', 'private'))) {
                                $permalink = get_permalink($post_id);
                                
                                if ($permalink && !is_wp_error($permalink)) {
                                    if (strpos($permalink, 'http') !== 0) {
                                        $lesson_url = home_url($permalink);
                                    } else {
                                        $lesson_url = $permalink;
                                    }
                                    
                                    $lesson_url = str_replace(
                                        array('www.pianovideolessons.com', 'pianovideolessons.com'),
                                        'jazzedge.academy',
                                        $lesson_url
                                    );
                                    
                                    $results[] = array(
                                        'lesson_id' => absint($lesson['ID']),
                                        'lesson_title' => $lesson['lesson_title'],
                                        'lesson_url' => $lesson_url,
                                        'lesson_description' => !empty($lesson['lesson_description']) ? wp_trim_words($lesson['lesson_description'], 30) : '',
                                        'match_type' => 'phrase_match',
                                        'matched_phrase' => $phrase,
                                        'priority' => 50 // Higher priority than regular keyword matches
                                    );
                                }
                            }
                            
                            if (count($results) >= $limit) {
                                break;
                            }
                        }
                    }
                }
            }
            
            // STEP 2b: Search for individual keywords (if we still need more results)
            if (count($results) < $limit && !empty($keywords)) {
                $where_conditions = array();
                $params = array();
                
                if (count($keywords) > 1) {
                    // For multiple keywords, require at least one keyword to match
                    $keyword_conditions = array();
                    foreach ($keywords as $keyword) {
                        $escaped_keyword = '%' . $wpdb->esc_like($keyword) . '%';
                        $keyword_conditions[] = "(LOWER(l.lesson_title) LIKE LOWER(%s) OR LOWER(l.lesson_description) LIKE LOWER(%s) OR LOWER(l.lesson_tags) LIKE LOWER(%s) OR LOWER(l.lesson_style) LIKE LOWER(%s))";
                        $params[] = $escaped_keyword;
                        $params[] = $escaped_keyword;
                        $params[] = $escaped_keyword;
                        $params[] = $escaped_keyword;
                    }
                    $where_clause = '(' . implode(' OR ', $keyword_conditions) . ')';
                } else {
                    // Single keyword
                    foreach ($keywords as $keyword) {
                        $escaped_keyword = '%' . $wpdb->esc_like($keyword) . '%';
                        $where_conditions[] = "(LOWER(l.lesson_title) LIKE LOWER(%s) OR LOWER(l.lesson_description) LIKE LOWER(%s) OR LOWER(l.lesson_tags) LIKE LOWER(%s) OR LOWER(l.lesson_style) LIKE LOWER(%s))";
                        $params[] = $escaped_keyword;
                        $params[] = $escaped_keyword;
                        $params[] = $escaped_keyword;
                        $params[] = $escaped_keyword;
                    }
                    $where_clause = '(' . implode(' OR ', $where_conditions) . ')';
                }
                
                $db_limit = min(absint($limit - count($results)), 20);
                
                // Check for skill level keywords in both query and extracted keywords
                $skill_levels_map = array(
                    'beginner' => 'beginner',
                    'intermediate' => 'intermediate',
                    'advanced' => 'advanced',
                    'pro' => 'pro',
                    'professional' => 'pro',
                    'expert' => 'pro'
                );
                $skill_level_match = null;
                
                // First check extracted keywords (more reliable)
                foreach ($keywords as $keyword) {
                    $keyword_lower = strtolower($keyword);
                    if (isset($skill_levels_map[$keyword_lower])) {
                        $skill_level_match = $skill_levels_map[$keyword_lower];
                        break;
                    }
                }
                
                // Fallback: check full query if not found in keywords
                if (!$skill_level_match) {
                    foreach ($skill_levels_map as $keyword => $db_value) {
                        if (stripos($query, $keyword) !== false) {
                            $skill_level_match = $db_value;
                            break;
                        }
                    }
                }
                
                // Use explicit level filter if provided, otherwise use detected level
                $final_level_filter = !empty($lesson_level) ? $lesson_level : $skill_level_match;
                if ($final_level_filter) {
                    $where_clause .= " OR l.lesson_level = %s";
                    $params[] = $final_level_filter;
                }
                
                // Filter by lesson style - match exact style (handles styles at start, middle, or end of comma-separated list)
                // This matches how ALM handles style filtering
                if (!empty($lesson_style)) {
                    $style_trimmed = trim($lesson_style);
                    $where_clause .= " AND (l.lesson_style = %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s OR l.lesson_style LIKE %s)";
                    $params[] = $style_trimmed;
                    $params[] = $style_trimmed . ',%';
                    $params[] = '%, ' . $style_trimmed . ',%';
                    $params[] = '%, ' . $style_trimmed;
                }
                
                // Filter by tag - match exact tag (handles tags at start, middle, or end of comma-separated list)
                if (!empty($lesson_tag)) {
                    $tag_trimmed = trim($lesson_tag);
                    $where_clause .= " AND (l.lesson_tags = %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s OR l.lesson_tags LIKE %s)";
                    $params[] = $tag_trimmed;
                    $params[] = $tag_trimmed . ',%';
                    $params[] = '%, ' . $tag_trimmed . ',%';
                    $params[] = '%, ' . $tag_trimmed;
                }
                
                // Build ORDER BY clause - prioritize lessons matching multiple keywords
                $topic_keywords = array();
                foreach ($keywords as $keyword) {
                    $keyword_lower = strtolower($keyword);
                    if (!in_array($keyword_lower, array('beginner', 'intermediate', 'advanced', 'pro', 'professional', 'expert'))) {
                        $topic_keywords[] = $keyword;
                    }
                }
                
                $order_by_clause = "CASE ";
                $order_params = array();
                
                // Highest priority: Lessons matching ALL topic keywords
                if (count($topic_keywords) > 1) {
                    $all_topic_match_conditions = array();
                    foreach ($topic_keywords as $keyword) {
                        $escaped_keyword = '%' . $wpdb->esc_like($keyword) . '%';
                        $all_topic_match_conditions[] = "(LOWER(l.lesson_title) LIKE LOWER(%s) OR LOWER(l.lesson_description) LIKE LOWER(%s) OR LOWER(l.lesson_tags) LIKE LOWER(%s) OR LOWER(l.lesson_style) LIKE LOWER(%s))";
                        $order_params[] = $escaped_keyword;
                        $order_params[] = $escaped_keyword;
                        $order_params[] = $escaped_keyword;
                        $order_params[] = $escaped_keyword;
                    }
                    $order_by_clause .= "WHEN (" . implode(' AND ', $all_topic_match_conditions) . ") THEN 1 ";
                }
                
                // Second priority: Lessons matching topic keywords in title
                if (count($topic_keywords) > 0) {
                    $title_match_conditions = array();
                    foreach ($topic_keywords as $keyword) {
                        $escaped_keyword = '%' . $wpdb->esc_like($keyword) . '%';
                        $title_match_conditions[] = "LOWER(l.lesson_title) LIKE LOWER(%s)";
                        $order_params[] = $escaped_keyword;
                    }
                    if (count($title_match_conditions) > 0) {
                        $order_by_clause .= "WHEN (" . implode(' AND ', $title_match_conditions) . ") THEN 2 ";
                    }
                }
                
                // Skill level match
                if ($skill_level_match) {
                    $order_by_clause .= " WHEN l.lesson_level = %s THEN " . (count($topic_keywords) > 0 ? "3" : "2");
                    $order_params[] = $skill_level_match;
                    $order_by_clause .= " ELSE " . (count($topic_keywords) > 0 ? "4" : "3") . " END";
                } else {
                    $order_by_clause .= " ELSE " . (count($topic_keywords) > 0 ? "3" : "2") . " END";
                }
                
                // Combine all params
                $all_params = array_merge($params, $order_params, array($db_limit));
                
                $sql = $wpdb->prepare(
                    "SELECT DISTINCT l.ID, l.lesson_title, l.post_id, l.slug, l.lesson_description, l.lesson_level, l.lesson_tags, l.lesson_style
                     FROM {$lessons_table} l
                     WHERE ({$where_clause})
                     AND l.post_id > 0
                     AND (l.status = 'published' OR l.status IS NULL)
                     ORDER BY {$order_by_clause},
                        l.lesson_title ASC
                     LIMIT %d",
                    $all_params
                );
                
                // Log SQL query for debugging
                if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                    $where_clause_log = $where_clause;
                    foreach ($params as $param) {
                        if (is_string($param)) {
                            $where_clause_log = preg_replace('/%s/', "'" . esc_sql($param) . "'", $where_clause_log, 1);
                        } else {
                            $where_clause_log = preg_replace('/%d/', intval($param), $where_clause_log, 1);
                        }
                    }
                    
                    $order_by_log = $order_by_clause;
                    foreach ($order_params as $param) {
                        if (is_string($param)) {
                            $order_by_log = preg_replace('/%s/', "'" . esc_sql($param) . "'", $order_by_log, 1);
                        } else {
                            $order_by_log = preg_replace('/%d/', intval($param), $order_by_log, 1);
                        }
                    }
                    
                    $sql_log = "SELECT DISTINCT l.ID, l.lesson_title, l.post_id, l.slug, l.lesson_description, l.lesson_level, l.lesson_tags, l.lesson_style
                     FROM {$lessons_table} l
                     WHERE ({$where_clause_log})
                     AND l.post_id > 0
                     AND (l.status = 'published' OR l.status IS NULL)
                     ORDER BY {$order_by_log},
                        l.lesson_title ASC
                     LIMIT " . intval($db_limit);
                    
                    $this->debug_logger->log('lesson_search_sql', 'Database search SQL query', array(
                        'user_query' => $query,
                        'sql' => $sql_log,
                        'table' => $lessons_table,
                        'phrases_extracted' => $phrases,
                        'keywords_extracted' => $keywords,
                        'where_clause' => $where_clause_log,
                        'parameters' => $all_params
                    ));
                }
                
                $lessons = $wpdb->get_results($sql, ARRAY_A);
                
                if (!empty($lessons)) {
                    // Format results with URLs
                    foreach ($lessons as $lesson) {
                        // Skip if already in results (from phrase or keyword match)
                        $already_included = false;
                        foreach ($results as $existing) {
                            if ($existing['lesson_id'] == absint($lesson['ID'])) {
                                $already_included = true;
                                break;
                            }
                        }
                        
                        if ($already_included) {
                            continue;
                        }
                        
                        $post_id = absint($lesson['post_id']);
                        $post = get_post($post_id);
                        
                        // Only include if post exists and is published
                        if ($post && in_array($post->post_status, array('publish', 'private'))) {
                            $permalink = get_permalink($post_id);
                            
                            if ($permalink && !is_wp_error($permalink)) {
                                // Ensure absolute URL
                                if (strpos($permalink, 'http') !== 0) {
                                    $lesson_url = home_url($permalink);
                                } else {
                                    $lesson_url = $permalink;
                                }
                                
                                // Force jazzedge.academy domain
                                $lesson_url = str_replace(
                                    array('www.pianovideolessons.com', 'pianovideolessons.com'),
                                    'jazzedge.academy',
                                    $lesson_url
                                );
                                
                                $results[] = array(
                                    'lesson_id' => absint($lesson['ID']),
                                    'lesson_title' => $lesson['lesson_title'],
                                    'lesson_url' => $lesson_url,
                                    'lesson_description' => !empty($lesson['lesson_description']) ? wp_trim_words($lesson['lesson_description'], 30) : '',
                                    'match_type' => 'database_search',
                                    'priority' => 100 // Lower priority than phrase and keyword matches
                                );
                            }
                        }
                        
                        // Stop if we've hit the limit
                        if (count($results) >= $limit) {
                            break;
                        }
                    }
                }
            }
        }
        
        // Sort by priority (lower = higher priority), then by title
        usort($results, function($a, $b) {
            $priority_a = isset($a['priority']) ? $a['priority'] : 100;
            $priority_b = isset($b['priority']) ? $b['priority'] : 100;
            
            if ($priority_a != $priority_b) {
                return $priority_a - $priority_b;
            }
            
            return strcmp($a['lesson_title'], $b['lesson_title']);
        });
        
        // Limit results
        $results = array_slice($results, 0, $limit);
        
        // Performance: Cache results
        wp_cache_set($cache_key, $results, $this->cache_group, $this->cache_duration);
        
        return $results;
    }
    
    /**
     * Get lesson by ID with URL
     * 
     * @param int $lesson_id Lesson ID
     * @return array|false Lesson data or false
     */
    private function get_lesson_by_id($lesson_id) {
        if (!class_exists('ALM_Database')) {
            return false;
        }
        
        global $wpdb;
        $alm_db = new ALM_Database();
        $lessons_table = $alm_db->get_table_name('lessons');
        
        $sql = $wpdb->prepare(
            "SELECT ID, lesson_title, post_id, slug, lesson_description
             FROM {$lessons_table}
             WHERE ID = %d AND post_id > 0",
            absint($lesson_id)
        );
        
        // Log SQL for debugging
        if ($this->debug_logger && $this->debug_logger->is_enabled()) {
            // Build actual SQL with values
            $sql_log = "SELECT ID, lesson_title, post_id, slug, lesson_description
                        FROM {$lessons_table}
                        WHERE ID = " . absint($lesson_id) . " AND post_id > 0";
            
            $this->debug_logger->log('lesson_lookup_sql', 'Lesson lookup by ID', array(
                'lesson_id' => $lesson_id,
                'sql' => $sql_log,
                'table' => $lessons_table
            ));
        }
        
        $lesson = $wpdb->get_row($sql, ARRAY_A);
        
        if (empty($lesson)) {
            return false;
        }
        
        $post_id = absint($lesson['post_id']);
        $post = get_post($post_id);
        
        // Only include if post exists and is published
        if (!$post || !in_array($post->post_status, array('publish', 'private'))) {
            return false;
        }
        
        $permalink = get_permalink($post_id);
        
        if (!$permalink || is_wp_error($permalink)) {
            return false;
        }
        
        // Ensure absolute URL
        if (strpos($permalink, 'http') !== 0) {
            $lesson_url = home_url($permalink);
        } else {
            $lesson_url = $permalink;
        }
        
        // Force jazzedge.academy domain
        $lesson_url = str_replace(
            array('www.pianovideolessons.com', 'pianovideolessons.com'),
            'jazzedge.academy',
            $lesson_url
        );
        
        return array(
            'lesson_id' => absint($lesson['ID']),
            'lesson_title' => $lesson['lesson_title'],
            'lesson_url' => $lesson_url,
            'lesson_description' => !empty($lesson['lesson_description']) ? wp_trim_words($lesson['lesson_description'], 30) : ''
        );
    }
    
    /**
     * Extract keywords and phrases from search query
     * 
     * @param string $query Search query
     * @return array Array with 'phrases' and 'keywords' keys
     */
    private function extract_keywords($query) {
        // Extended stop words - includes common verbs, pronouns, and query-specific words
        $stop_words = array(
            // Articles
            'the', 'a', 'an',
            // Conjunctions
            'and', 'or', 'but', 'if', 'then', 'else',
            // Prepositions
            'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'into', 'onto', 'upon', 'about', 'across', 'through', 'during', 'under', 'over', 'above', 'below', 'between', 'among',
            // Pronouns
            'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them', 'this', 'that', 'these', 'those',
            // Common verbs
            'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'should', 'could', 'may', 'might', 'can', 'must',
            // Query-specific stop words
            'recommend', 'recommendation', 'recommendations', 'show', 'give', 'find', 'search', 'look', 'want', 'need', 'get', 'see', 'tell', 'help', 'please', 'thanks', 'thank',
            'lesson', 'lessons', 'learn', 'learning', 'teach', 'teaching', 'about', 'how', 'what', 'when', 'where', 'why',
            // Additional stop words
            'another', 'other', 'more', 'do', 'you', 'have'
        );
        
        $query_lower = strtolower(trim($query));
        $phrases = array();
        
        // STEP 1: Detect phrases (e.g., "on autumn leaves", "about autumn leaves")
        // Extract phrase after "on" (e.g., "on autumn leaves")
        if (preg_match('/\bon\s+([a-z]+(?:\s+[a-z]+){0,2})/i', $query_lower, $matches)) {
            $phrase = trim($matches[1]);
            // Remove stop words from phrase and check if meaningful
            $phrase_words = preg_split('/\s+/', $phrase);
            $meaningful_words = array();
            foreach ($phrase_words as $word) {
                $word = trim($word, '.,!?;:()[]{}"\'-');
                if (mb_strlen($word) >= 3 && !in_array($word, $stop_words, true)) {
                    $meaningful_words[] = $word;
                }
            }
            if (count($meaningful_words) >= 2) {
                $phrases[] = implode(' ', $meaningful_words);
            }
        }
        
        // Extract phrase after "about" (e.g., "about autumn leaves")
        if (preg_match('/\babout\s+([a-z]+(?:\s+[a-z]+){0,2})/i', $query_lower, $matches)) {
            $phrase = trim($matches[1]);
            $phrase_words = preg_split('/\s+/', $phrase);
            $meaningful_words = array();
            foreach ($phrase_words as $word) {
                $word = trim($word, '.,!?;:()[]{}"\'-');
                if (mb_strlen($word) >= 3 && !in_array($word, $stop_words, true)) {
                    $meaningful_words[] = $word;
                }
            }
            if (count($meaningful_words) >= 2) {
                $phrases[] = implode(' ', $meaningful_words);
            }
        }
        
        // Also detect potential 2-word phrases that might be song titles
        // Look for adjacent non-stop words
        $tokens = preg_split('/\s+/', $query_lower);
        $potential_phrases = array();
        
        for ($i = 0; $i < count($tokens) - 1; $i++) {
            $word1 = trim($tokens[$i], '.,!?;:()[]{}"\'-');
            $word2 = trim($tokens[$i + 1], '.,!?;:()[]{}"\'-');
            
            if (mb_strlen($word1) >= 3 && mb_strlen($word2) >= 3 && 
                !in_array($word1, $stop_words, true) && !in_array($word2, $stop_words, true)) {
                $potential_phrase = $word1 . ' ' . $word2;
                // Only add if not already in phrases
                if (!in_array($potential_phrase, $phrases, true)) {
                    $potential_phrases[] = $potential_phrase;
                }
            }
        }
        
        // Add potential phrases if they're meaningful (at least 5 characters total)
        foreach ($potential_phrases as $phrase) {
            if (mb_strlen($phrase) >= 5) {
                $phrases[] = $phrase;
            }
        }
        
        $phrases = array_unique($phrases);
        
        // STEP 2: Extract individual keywords (excluding words that are part of phrases)
        $keywords = array();
        $phrase_words = array();
        foreach ($phrases as $phrase) {
            $phrase_words = array_merge($phrase_words, explode(' ', $phrase));
        }
        
        foreach ($tokens as $token) {
            $token = trim($token, '.,!?;:()[]{}"\'-');
            if (empty($token)) continue;
            
            // Skip if part of a phrase (we'll search for the phrase separately)
            if (in_array($token, $phrase_words, true)) {
                continue;
            }
            
            // Only include words that are 3+ characters and not in stop words
            if (mb_strlen($token) >= 3 && !in_array($token, $stop_words, true)) {
                $keywords[] = $token;
            }
        }
        
        // Return structure with phrases and keywords
        return array(
            'phrases' => $phrases,
            'keywords' => $keywords
        );
    }
    
    /**
     * Check if lesson search is available
     * 
     * @return bool True if ALM is available
     */
    public function is_available() {
        return class_exists('ALM_Database');
    }
}

