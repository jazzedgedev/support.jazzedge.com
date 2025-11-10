<?php
/**
 * AI Recommender for Lesson Search
 * 
 * Provides AI-powered recommendations based on real lesson data.
 * Never recommends external sources - only lessons in the database.
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_AI_Recommender {
    /** @var wpdb */
    private $wpdb;
    
    /** @var ALM_Database */
    private $database;
    
    /** @var string Last prompt sent to AI (for debugging) */
    private $last_prompt = '';
    
    /** @var array Last catalog sent to AI (for debugging) */
    private $last_catalog = array();
    
    /** @var string Last SQL query executed (for debugging) */
    private $last_sql_query = '';
    
    /** @var array SQL query parameters (for debugging) */
    private $last_sql_params = array();
    
    /** @var int Count of lessons in catalog (for debugging) */
    private $last_catalog_lessons_count = 0;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();
        
        // Hook into existing AI filters
        add_filter('alm_ai_extract_filters', array($this, 'extract_filters'), 10, 2);
        add_filter('alm_ai_rerank_items', array($this, 'rerank_items'), 10, 2);
    }
    
    /**
     * Extract structured filters from natural language query
     * 
     * @param array $filters Existing filters
     * @param string $query Search query
     * @return array Enhanced filters
     */
    public function extract_filters($filters, $query) {
        if (empty($query)) {
            return $filters;
        }
        
        // Basic keyword matching for common patterns
        // This is a lightweight implementation - can be enhanced with AI
        
        $query_lower = strtolower($query);
        
        // Detect duration preferences
        if (preg_match('/\b(short|quick|brief)\b/', $query_lower)) {
            $filters['max_duration'] = 600; // 10 minutes
        } elseif (preg_match('/\b(long|extended|comprehensive)\b/', $query_lower)) {
            $filters['min_duration'] = 1200; // 20 minutes
        }
        
        // Detect membership level mentions
        if (preg_match('/\b(beginner|starter|basic|free)\b/', $query_lower)) {
            $filters['membership_level'] = 0;
        } elseif (preg_match('/\b(essentials?|fundamentals?)\b/', $query_lower)) {
            $filters['membership_level'] = 1;
        } elseif (preg_match('/\b(studio|intermediate)\b/', $query_lower)) {
            $filters['membership_level'] = 2;
        } elseif (preg_match('/\b(premier|advanced|pro|all.?access)\b/', $query_lower)) {
            $filters['membership_level'] = 3;
        }
        
        return $filters;
    }
    
    /**
     * Re-rank search results using AI understanding
     * 
     * @param array $items Search results
     * @param string $query Search query
     * @return array Re-ranked items
     */
    public function rerank_items($items, $query) {
        if (empty($items) || empty($query)) {
            return $items;
        }
        
        // For now, return items as-is
        // Can be enhanced with semantic re-ranking later
        return $items;
    }
    
    /**
     * Get lesson catalog for AI context
     * 
     * @param int $user_membership_level User's membership level
     * @param int $limit Max lessons to include
     * @param string $search_query Optional search term to filter lessons
     * @return array Structured lesson catalog
     */
    public function get_lesson_catalog_for_ai($user_membership_level = 3, $limit = 100, $search_query = '') {
        $lessons_table = $this->database->get_table_name('lessons');
        $collections_table = $this->database->get_table_name('collections');
        
        // Build WHERE clause with search filtering if provided
        $where_clause = "WHERE l.membership_level <= %d";
        $params = array($user_membership_level);
        
        if (!empty($search_query)) {
            $search_term = '%' . $this->wpdb->esc_like($search_query) . '%';
            $where_clause .= " AND (
                l.lesson_title LIKE %s 
                OR l.lesson_description LIKE %s 
                OR c.collection_title LIKE %s
            )";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        // Get lessons the user can access, filtered by search term if provided
        $sql_template = "SELECT 
                l.ID,
                l.lesson_title,
                l.lesson_description,
                l.duration,
                l.membership_level,
                l.collection_id,
                c.collection_title,
                l.slug,
                l.post_id
            FROM {$lessons_table} l
            LEFT JOIN {$collections_table} c ON c.ID = l.collection_id
            {$where_clause}
            ORDER BY c.collection_title, l.lesson_title
            LIMIT %d";
        
        $params[] = $limit;
        
        // Store SQL template and parameters for debugging
        $this->last_sql_params = $params;
        
        $sql = $this->wpdb->prepare($sql_template, $params);
        
        // Store the actual SQL query (after prepare) for debugging
        $this->last_sql_query = $sql;
        
        $lessons = $this->wpdb->get_results($sql);
        
        // Store lesson count for debugging
        $this->last_catalog_lessons_count = count($lessons);
        
        // Group by collection
        $catalog = array();
        $collections = array();
        
        foreach ($lessons as $lesson) {
            $collection_title = !empty($lesson->collection_title) 
                ? stripslashes($lesson->collection_title) 
                : 'Uncategorized';
            
            if (!isset($collections[$collection_title])) {
                $collections[$collection_title] = array(
                    'title' => $collection_title,
                    'lessons' => array()
                );
            }
            
            $duration_min = round($lesson->duration / 60, 0);
            $membership_name = $this->get_membership_level_name($lesson->membership_level);
            
            $collections[$collection_title]['lessons'][] = array(
                'title' => stripslashes($lesson->lesson_title),
                'description' => stripslashes($lesson->lesson_description),
                'duration_minutes' => $duration_min,
                'membership_level' => $membership_name,
                'id' => $lesson->ID,
                'collection_id' => $lesson->collection_id
            );
        }
        
        return array_values($collections);
    }
    
    /**
     * Build AI prompt for path recommendations
     * 
     * @param string $query Student's search query
     * @param array $search_results Current search results
     * @param array $lesson_catalog Full lesson catalog
     * @param int $user_membership_level User's membership level
     * @return string AI prompt
     */
    public function build_path_recommendation_prompt($query, $search_results, $lesson_catalog, $user_membership_level = 3) {
        $membership_name = $this->get_membership_level_name($user_membership_level);
        
        // Build search results summary
        $results_summary = '';
        if (!empty($search_results)) {
            $results_summary = "\n\nCURRENT SEARCH RESULTS:\n";
            foreach (array_slice($search_results, 0, 10) as $idx => $lesson) {
                $results_summary .= ($idx + 1) . ". " . $lesson['title'];
                if (!empty($lesson['collection_title'])) {
                    $results_summary .= " (Collection: " . $lesson['collection_title'] . ")";
                }
                $results_summary .= "\n";
            }
        }
        
        // Build lesson catalog summary (limited to avoid token limits)
        $catalog_summary = "\n\nAVAILABLE LESSONS IN OUR SYSTEM:\n";
        $lesson_count = 0;
        foreach ($lesson_catalog as $collection) {
            $catalog_summary .= "\nCollection: " . $collection['title'] . "\n";
            foreach (array_slice($collection['lessons'], 0, 5) as $lesson) {
                $catalog_summary .= "  - " . $lesson['title'];
                if ($lesson['duration_minutes'] > 0) {
                    $catalog_summary .= " (" . $lesson['duration_minutes'] . " min, " . $lesson['membership_level'] . ")";
                }
                $catalog_summary .= "\n";
                $lesson_count++;
                if ($lesson_count >= 50) break 2; // Limit total lessons shown
            }
        }
        
        $prompt = "You are a helpful music lesson advisor for Jazzedge Academy. Your job is to recommend learning paths using ONLY lessons that exist in our system.

STUDENT QUERY: {$query}
STUDENT MEMBERSHIP LEVEL: {$membership_name}" . $results_summary . $catalog_summary . "

CRITICAL RULES:
1. ONLY recommend lessons that are listed above in 'AVAILABLE LESSONS IN OUR SYSTEM'
2. NEVER recommend external resources (YouTube, other websites, competitors, books, apps, etc.)
3. ONLY reference lesson titles exactly as they appear in the catalog above
4. Suggest a logical learning path using lessons from our catalog
5. Reference specific lesson titles, not generic concepts
6. If no relevant lessons exist, say so and suggest related collections

RESPONSE FORMAT:
Return a JSON object with this structure:
{
  \"recommended_path\": [
    {
      \"step\": 1,
      \"lesson_title\": \"Exact Lesson Title from Catalog\",
      \"reason\": \"Brief explanation why this lesson fits\"
    }
  ],
  \"alternative_lessons\": [
    {
      \"lesson_title\": \"Alternative Lesson Title\",
      \"reason\": \"Why this might be useful\"
    }
  ],
  \"summary\": \"Brief text summary (max 2 sentences)\"
}

Ensure all lesson_title values match EXACTLY the titles in the catalog above.";

        return $prompt;
    }
    
    /**
     * Get system message for AI
     * 
     * @return string System message
     */
    public function get_system_message() {
        return "You are a music lesson advisor for Jazzedge Academy. You recommend ONLY lessons that exist in our database. NEVER recommend external resources like YouTube, other websites, or competitors. Always reference exact lesson titles from our catalog.";
    }
    
    /**
     * Generate AI path recommendations
     * 
     * @param string $query Search query
     * @param array $search_results Search results
     * @param int $user_membership_level User's membership level
     * @return array|WP_Error Recommendations or error
     */
    public function generate_path_recommendations($query, $search_results, $user_membership_level = 3) {
        // Get lesson catalog filtered by the search query
        $lesson_catalog = $this->get_lesson_catalog_for_ai($user_membership_level, 100, $query);
        
        if (empty($lesson_catalog)) {
            return new WP_Error('no_catalog', 'No lessons available for recommendations');
        }
        
        // Build prompt
        $prompt = $this->build_path_recommendation_prompt($query, $search_results, $lesson_catalog, $user_membership_level);
        $system_message = $this->get_system_message();
        
        // Store for debugging
        $this->last_prompt = $prompt;
        $this->last_catalog = $lesson_catalog;
        
        // Call Katahdin AI Hub
        $ai_response = $this->call_katahdin_ai($prompt, $system_message);
        
        if (is_wp_error($ai_response)) {
            return $ai_response;
        }
        
        // Parse and validate AI response
        $recommendations = $this->parse_and_validate_ai_response($ai_response, $lesson_catalog);
        
        return $recommendations;
    }
    
    /**
     * Call Katahdin AI Hub
     * 
     * @param string $prompt User prompt
     * @param string $system_message System message
     * @param string $model Model name
     * @param int $max_tokens Max tokens
     * @param float $temperature Temperature
     * @return string|WP_Error AI response or error
     */
    private function call_katahdin_ai($prompt, $system_message, $model = 'gpt-4', $max_tokens = 800, $temperature = 0.7) {
        // Check if Katahdin AI Hub is available
        if (!class_exists('Katahdin_AI_Hub_REST_API')) {
            return new WP_Error('ai_hub_unavailable', 'Katahdin AI Hub is not available', array('status' => 503));
        }
        
        // Ensure plugin is registered with Katahdin AI Hub
        if (class_exists('Katahdin_AI_Hub') && function_exists('katahdin_ai_hub')) {
            $hub = katahdin_ai_hub();
            if ($hub && method_exists($hub, 'register_plugin')) {
                $quota_limit = get_option('alm_ai_quota_limit', 10000);
                $hub->register_plugin('academy-lesson-manager', array(
                    'name' => 'Academy Lesson Manager',
                    'version' => '1.0',
                    'features' => array('chat', 'completions'),
                    'quota_limit' => $quota_limit
                ));
            }
        }
        
        // Prepare the request
        $request_data = array(
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $system_message
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'model' => $model,
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
            'plugin_id' => 'academy-lesson-manager'
        );
        
        // Make the request to Katahdin AI Hub using internal WordPress REST API (same pattern as practice hub)
        $rest_server = rest_get_server();
        $request = new WP_REST_Request('POST', '/katahdin-ai-hub/v1/chat/completions');
        $request->set_header('Content-Type', 'application/json');
        $request->set_header('X-Plugin-ID', 'academy-lesson-manager');
        $request->set_body(json_encode($request_data));
        
        // Add the request data as parameters
        foreach ($request_data as $key => $value) {
            $request->set_param($key, $value);
        }
        
        $response = $rest_server->dispatch($request);
        
        if (is_wp_error($response)) {
            return new WP_Error('ai_request_failed', 'Failed to connect to AI service: ' . $response->get_error_message(), array('status' => 500));
        }
        
        // Get the response data
        $response_data = $response->get_data();
        $response_status = $response->get_status();
        
        if ($response_status !== 200) {
            $error_message = isset($response_data['message']) ? $response_data['message'] : 'Unknown error';
            return new WP_Error('ai_service_error', 'AI service returned error: ' . $response_status . ' - ' . $error_message, array('status' => $response_status));
        }
        
        if (!isset($response_data['choices'][0]['message']['content'])) {
            return new WP_Error('ai_response_invalid', 'Invalid response from AI service', array('status' => 500));
        }
        
        $ai_response = trim($response_data['choices'][0]['message']['content']);
        
        return $ai_response;
    }
    
    /**
     * Parse and validate AI response
     * 
     * @param string $ai_response Raw AI response
     * @param array $lesson_catalog Valid lessons
     * @return array Validated recommendations
     */
    private function parse_and_validate_ai_response($ai_response, $lesson_catalog) {
        // Extract JSON from response (might have extra text)
        $json_match = array();
        if (preg_match('/\{[\s\S]*\}/', $ai_response, $json_match)) {
            $json_str = $json_match[0];
            $parsed = json_decode($json_str, true);
            
            if ($parsed && isset($parsed['recommended_path'])) {
                // Validate all lesson titles exist in catalog
                $all_lessons = array();
                foreach ($lesson_catalog as $collection) {
                    foreach ($collection['lessons'] as $lesson) {
                        $all_lessons[strtolower($lesson['title'])] = $lesson;
                    }
                }
                
                $validated_path = array();
                foreach ($parsed['recommended_path'] as $item) {
                    if (isset($item['lesson_title'])) {
                        $lesson_key = strtolower($item['lesson_title']);
                        if (isset($all_lessons[$lesson_key])) {
                            // Get full lesson data
                            $validated_path[] = array(
                                'step' => $item['step'] ?? count($validated_path) + 1,
                                'lesson_title' => $all_lessons[$lesson_key]['title'],
                                'lesson_id' => $all_lessons[$lesson_key]['id'],
                                'collection_id' => $all_lessons[$lesson_key]['collection_id'],
                                'duration_minutes' => $all_lessons[$lesson_key]['duration_minutes'],
                                'membership_level' => $all_lessons[$lesson_key]['membership_level'],
                                'reason' => $item['reason'] ?? ''
                            );
                        }
                    }
                }
                
                // Validate alternative lessons
                $validated_alternatives = array();
                if (isset($parsed['alternative_lessons'])) {
                    foreach ($parsed['alternative_lessons'] as $item) {
                        if (isset($item['lesson_title'])) {
                            $lesson_key = strtolower($item['lesson_title']);
                            if (isset($all_lessons[$lesson_key])) {
                                $validated_alternatives[] = array(
                                    'lesson_title' => $all_lessons[$lesson_key]['title'],
                                    'lesson_id' => $all_lessons[$lesson_key]['id'],
                                    'collection_id' => $all_lessons[$lesson_key]['collection_id'],
                                    'reason' => $item['reason'] ?? ''
                                );
                            }
                        }
                    }
                }
                
                return array(
                    'recommended_path' => $validated_path,
                    'alternative_lessons' => $validated_alternatives,
                    'summary' => $parsed['summary'] ?? '',
                    'raw_response' => $ai_response
                );
            }
        }
        
        // Fallback: return simple text response if JSON parsing fails
        return array(
            'recommended_path' => array(),
            'alternative_lessons' => array(),
            'summary' => strip_tags($ai_response),
            'raw_response' => $ai_response,
            'note' => 'Could not parse structured recommendations'
        );
    }
    
    /**
     * Get membership level name
     * 
     * @param int $level Membership level numeric value
     * @return string Membership level name
     */
    private function get_membership_level_name($level) {
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-settings.php';
        return ALM_Admin_Settings::get_membership_level_name($level);
    }
    
    /**
     * Get last prompt sent (for debugging)
     * 
     * @return string Last prompt
     */
    public function get_last_prompt() {
        return $this->last_prompt;
    }
    
    /**
     * Get last catalog sent (for debugging)
     * 
     * @return array Last catalog
     */
    public function get_last_catalog() {
        return $this->last_catalog;
    }
    
    /**
     * Get last SQL query executed (for debugging)
     * 
     * @return string Last SQL query
     */
    public function get_last_sql_query() {
        return $this->last_sql_query;
    }
    
    /**
     * Get last SQL query parameters (for debugging)
     * 
     * @return array Last SQL parameters
     */
    public function get_last_sql_params() {
        return $this->last_sql_params;
    }
    
    /**
     * Get count of lessons in last catalog (for debugging)
     * 
     * @return int Lesson count
     */
    public function get_last_catalog_lessons_count() {
        return $this->last_catalog_lessons_count;
    }
}

// Initialize - only if not already instantiated
if (!isset($GLOBALS['alm_ai_recommender'])) {
    $GLOBALS['alm_ai_recommender'] = new ALM_AI_Recommender();
}

