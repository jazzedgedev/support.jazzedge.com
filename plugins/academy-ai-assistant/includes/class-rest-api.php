<?php
/**
 * REST API Class for Academy AI Assistant
 * 
 * Handles all REST API endpoints for chat functionality
 * Security: All endpoints require authentication and nonce verification
 */

if (!defined('ABSPATH')) {
    exit;
}

class AAA_REST_API {
    
    private $database;
    private $context_builder;
    private $embedding_search;
    private $lesson_search;
    private $debug_logger;
    private $feature_flags;
    private $token_limits;
    
    public function __construct() {
        $this->database = new AAA_Database();
        $this->context_builder = new AI_Context_Builder(); // Note: Uses AI_ prefix
        $this->debug_logger = new AAA_Debug_Logger();
        $this->embedding_search = new AI_Embedding_Search($this->debug_logger); // Pass debug logger
        $this->lesson_search = new AAA_Lesson_Search($this->debug_logger); // Pass debug logger
        $this->feature_flags = new AAA_Feature_Flags();
        $this->token_limits = new AAA_Token_Limits();
        
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        $namespace = 'academy-ai-assistant/v1';
        
        // Chat endpoint - send message and get AI response
        register_rest_route($namespace, '/chat', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_chat'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'message' => array(
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function($param) {
                        return !empty(trim($param)) && strlen($param) <= 2000;
                    }
                ),
                'location' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => 'main',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'use_context' => array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true
                ),
                'use_embeddings' => array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true
                ),
                'chip_id' => array(
                    'type' => 'string',
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        // Get conversations for a session
        register_rest_route($namespace, '/conversations', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_conversations'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'session_id' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 0
                ),
                'page' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 1,
                    'minimum' => 1
                ),
                'per_page' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 50,
                    'minimum' => 1,
                    'maximum' => 100
                )
            )
        ));
        
        // Get all sessions for current user
        register_rest_route($namespace, '/sessions', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_sessions'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'page' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 1,
                    'minimum' => 1
                ),
                'per_page' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 20,
                    'minimum' => 1,
                    'maximum' => 100
                )
            )
        ));
        
        // Create new session
        register_rest_route($namespace, '/sessions', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_create_session'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'location' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => 'main',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'session_name' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        // Get most recent session with conversations (optimized for page load)
        register_rest_route($namespace, '/session/recent', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_recent_session'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'conversation_limit' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 50,
                    'minimum' => 1,
                    'maximum' => 50
                ),
                'session_id' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 0,
                    'sanitize_callback' => 'absint'
                )
            )
        ));
        
        // Update session name
        register_rest_route($namespace, '/sessions/(?P<id>\d+)/name', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'handle_update_session_name'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'id' => array(
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ),
                'session_name' => array(
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        // Download transcript
        register_rest_route($namespace, '/transcript/(?P<session_id>\d+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_download_transcript'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'session_id' => array(
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ),
                'format' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => 'txt',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function($param) {
                        return in_array($param, array('txt', 'pdf'), true);
                    }
                )
            )
        ));
        
        
        // Delete conversation session
        register_rest_route($namespace, '/session/(?P<id>\d+)', array(
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => array($this, 'handle_delete_session'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'id' => array(
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));
        
        // Add lesson/collection to favorites
        register_rest_route($namespace, '/favorites', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_add_favorite'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'url' => array(
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'esc_url_raw'
                ),
                'title' => array(
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'description' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => '',
                    'sanitize_callback' => 'sanitize_textarea_field'
                ),
                'category' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => 'lesson',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function($param) {
                        return in_array($param, array('lesson', 'collection'), true);
                    }
                )
            )
        ));
        
        // Get user token usage stats
        register_rest_route($namespace, '/usage', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_usage'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Export embeddings data (for migration from source server)
        register_rest_route($namespace, '/embeddings/export', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_export_embeddings'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'batch_size' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 100,
                    'minimum' => 1,
                    'maximum' => 1000
                ),
                'offset' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 0,
                    'minimum' => 0
                ),
                'transcript_id' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 0
                )
            )
        ));
        
        // Import embeddings data (for migration to destination server)
        register_rest_route($namespace, '/embeddings/import', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_import_embeddings'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'embeddings' => array(
                    'type' => 'array',
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_array($param) && !empty($param);
                    }
                ),
                'overwrite' => array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => false
                )
            )
        ));
        
        // Get total count of embeddings (for migration progress tracking)
        register_rest_route($namespace, '/embeddings/count', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'handle_get_embeddings_count'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
    }
    
    /**
     * Check if user has permission
     * Also checks feature flags
     * 
     * For REST API, we need to check authentication differently
     * WordPress REST API uses cookie authentication by default
     */
    public function check_user_permission($request = null) {
        // For REST API, we need to check if user is authenticated
        // Try multiple methods to get user ID
        $user_id = get_current_user_id();
        
        // If user_id is 0, try to authenticate via cookies
        if ($user_id === 0) {
            // Check if we have a valid nonce in the request
            if ($request && method_exists($request, 'get_header')) {
                $nonce = $request->get_header('X-WP-Nonce');
                if ($nonce && wp_verify_nonce($nonce, 'wp_rest')) {
                    // Nonce is valid, but user still might not be logged in
                    // This is expected - nonce just verifies the request is from the site
                }
            }
        }
        
        // Log for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Academy AI Assistant: check_user_permission - User ID: ' . $user_id);
            error_log('Academy AI Assistant: check_user_permission - is_user_logged_in: ' . (is_user_logged_in() ? 'true' : 'false'));
            if ($request) {
                error_log('Academy AI Assistant: check_user_permission - Request method: ' . $request->get_method());
                error_log('Academy AI Assistant: check_user_permission - Request headers: ' . print_r($request->get_headers(), true));
            }
        }
        
        if ($user_id === 0) {
            // User not authenticated - return false to trigger 401
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Academy AI Assistant: check_user_permission - User not authenticated (user_id = 0)');
            }
            return false;
        }
        
        // Check feature flags
        if (!$this->feature_flags->user_can_access_ai()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Academy AI Assistant: check_user_permission - Feature not enabled for user ' . $user_id);
            }
            return new WP_Error(
                'feature_not_enabled',
                'AI Assistant is not enabled for your account.',
                array('status' => 403)
            );
        }
        
        return true;
    }
    
    /**
     * Handle chat message
     */
    public function handle_chat($request) {
        $user_id = get_current_user_id();
        $message = sanitize_text_field($request->get_param('message'));
        $session_id = absint($request->get_param('session_id'));
        $location = sanitize_text_field($request->get_param('location'));
        $use_context = (bool) $request->get_param('use_context');
        $use_embeddings = (bool) $request->get_param('use_embeddings');
        $chip_id = sanitize_text_field($request->get_param('chip_id'));
        
        // Default to 'main' if no location specified
        if (empty($location)) {
            $location = 'main';
        }
        
        // Check token limits before processing
        $token_check = $this->token_limits->check_token_limit($user_id, 0, 'daily');
        if (!$token_check['allowed']) {
            return new WP_Error(
                'token_limit_exceeded',
                $token_check['message'],
                array(
                    'status' => 429,
                    'current_usage' => $token_check['current_usage'],
                    'limit' => $token_check['limit'],
                    'remaining' => $token_check['remaining'],
                    'membership_level' => $token_check['membership_level']
                )
            );
        }
        
        // Get or create session
        if ($session_id === 0) {
            $session_id = $this->get_or_create_session($user_id, $location);
        } else {
            // Verify session belongs to user
            if (!$this->verify_session_ownership($session_id, $user_id)) {
                return new WP_Error(
                    'invalid_session',
                    'Session not found or access denied.',
                    array('status' => 403)
                );
            }
            // Get location from session if not provided
            if (empty($location)) {
                $session = $this->database->get_user_sessions($user_id, 1, 100);
                foreach ($session as $s) {
                    if ($s['id'] == $session_id) {
                        $location = isset($s['location']) ? $s['location'] : 'main';
                        break;
                    }
                }
            }
        }
        
        // Build context
        $context_data = array();
        if ($use_context) {
            $context_data = $this->context_builder->build_context($user_id, $message);
        }
        
        // Get conversation history to understand context for vague queries
        $conversation_history = $this->database->get_conversations($session_id, 1, 5); // Last 5 messages
        
        // Build enhanced query with context if the current query is vague
        $enhanced_query = $this->enhance_query_with_context($message, $conversation_history);
        
        // Search embeddings if enabled
        $embedding_results = array();
        if ($use_embeddings && $this->embedding_search->is_available()) {
            // Search with lower similarity threshold to catch more results
            $embedding_results = $this->embedding_search->search($enhanced_query, 10, 0.5);
            
            // Log results for debugging
            if ($this->debug_logger->is_enabled()) {
                if (empty($embedding_results)) {
                    $this->debug_logger->log('embedding_search', 'No embedding results found for query: "' . $message . '"', array(
                        'query' => $message,
                        'search_type' => 'embedding_search',
                        'similarity_threshold' => 0.5,
                        'max_results' => 10
                    ), get_current_user_id(), $session_id, $location);
                } else {
                    // Build detailed message with lesson/chapter info
                    $lesson_details = array();
                    $lessons_found = array();
                    
                    foreach ($embedding_results as $result) {
                        $lesson_title = !empty($result['lesson_title']) ? $result['lesson_title'] : 'N/A';
                        $chapter_title = !empty($result['chapter_title']) ? $result['chapter_title'] : '';
                        $start_time = !empty($result['start_time']) ? floatval($result['start_time']) : 0;
                        $url = !empty($result['timestamp_url']) ? $result['timestamp_url'] : (!empty($result['lesson_url']) ? $result['lesson_url'] : 'N/A');
                        
                        // Build display text
                        $detail_text = $lesson_title;
                        if (!empty($chapter_title)) {
                            $detail_text .= ' - Chapter: ' . $chapter_title;
                        }
                        if ($start_time > 0) {
                            $minutes = floor($start_time / 60);
                            $seconds = floor($start_time % 60);
                            $detail_text .= ' (at ' . sprintf('%d:%02d', $minutes, $seconds) . ')';
                        }
                        $detail_text .= ' | URL: ' . $url;
                        
                        $lesson_details[] = $detail_text;
                        
                        // Also store structured data
                        $lesson_info = array(
                            'lesson_id' => !empty($result['lesson_id']) ? $result['lesson_id'] : 'N/A',
                            'lesson_title' => $lesson_title,
                            'chapter_title' => $chapter_title,
                            'chapter_slug' => !empty($result['chapter_slug']) ? $result['chapter_slug'] : '',
                            'start_time' => $start_time,
                            'url' => $url
                        );
                        $lessons_found[] = $lesson_info;
                    }
                    
                    // Create message with lesson details
                    $log_message = 'Found ' . count($embedding_results) . ' embedding result(s) for query: "' . $message . '"';
                    if (!empty($lesson_details)) {
                        $log_message .= ' | Lessons: ' . implode('; ', $lesson_details);
                    }
                    
                    $this->debug_logger->log('embedding_search', $log_message, array(
                        'query' => $message,
                        'search_type' => 'embedding_search',
                        'similarity_threshold' => 0.5,
                        'max_results' => 10,
                        'results_count' => count($embedding_results),
                        'lessons_found' => $lessons_found
                    ), get_current_user_id(), $session_id, $location);
                }
            }
        }
        
        // Also search lesson database directly by title/keywords
        // Use AI preprocessing if query is vague or complex
        $lesson_search_query = $enhanced_query;
        $lesson_style_filter = '';
        $lesson_level_filter = '';
        $lesson_tag_filter = '';
        
        // Always use AI preprocessing to extract search intent and correct spelling
        // This handles both vague queries ("more", "another") and misspellings ("steala bue starlite" -> "Stella by Starlight")
        $ai_extracted = $this->ai_extract_search_intent($message, $conversation_history);
        if (!is_wp_error($ai_extracted)) {
            if (!empty($ai_extracted['primary_phrase'])) {
                $lesson_search_query = $ai_extracted['primary_phrase'];
            }
            if (!empty($ai_extracted['skill_level'])) {
                $lesson_level_filter = $ai_extracted['skill_level'];
            }
            // Extract style from AI preprocessing if available
            if (!empty($ai_extracted['lesson_style'])) {
                $lesson_style_filter = $ai_extracted['lesson_style'];
            }
            
            // Log AI preprocessing
            if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                $this->debug_logger->log('ai_search_preprocessing', 'AI extracted search intent and corrected spelling', array(
                    'original_query' => $message,
                    'enhanced_query' => $enhanced_query,
                    'ai_extracted' => $ai_extracted,
                    'final_search_query' => $lesson_search_query,
                    'filters' => array(
                        'style' => $lesson_style_filter,
                        'level' => $lesson_level_filter,
                        'tag' => $lesson_tag_filter
                    )
                ), get_current_user_id(), $session_id, $location);
            }
        }
        
        // Extract style/tag and search query from conversation history for vague queries like "more"
        // This runs AFTER AI preprocessing, so it can override if AI didn't extract properly
        
        // Comprehensive list of natural language variations for "more" / "continue" / "next" queries
        $more_query_patterns = array(
            // General "more" / continue
            'more', 'additional', 'extra', 'further', 'continued', 'next', 'follow-up', 'follow up', 'followon', 'another', 'others',
            // Progression / next step
            'next level', 'next step', 'advance', 'progressing', 'progression', 'step up', 'build on this',
            // Depth / detail
            'deeper', 'dive deeper', 'in depth', 'indepth', 'expanded', 'detailed', 'advanced', 'go further', 'break down',
            // Variation / alternatives
            'variations', 'alternatives', 'different', 'other approaches', 'other ways', 'options', 'similar', 'related',
            // Volume / quantity
            'more like this', 'more examples', 'more exercises', 'more practice', 'more drills', 'more ideas',
            // Reinforcement / review
            'review', 'reinforce', 'practice again', 'recap', 'repeat', 'strengthen',
            // Skill-level specific
            'beginner follow-ups', 'beginner followups', 'intermediate', 'harder', 'easier', 'simplified',
            // Natural student phrases
            'what else', 'what should i do next', 'where do i go from here', 'can you show me another', 'anything similar',
            'keep going', 'what\'s next', 'whats next', 'what is next'
        );
        
        $message_lower = strtolower(trim($message));
        $is_more_query = false;
        
        // Check for exact matches first (most common)
        if (in_array($message_lower, $more_query_patterns)) {
            $is_more_query = true;
        } else {
            // Check for partial matches (phrases containing these patterns)
            foreach ($more_query_patterns as $pattern) {
                // For multi-word patterns, check if they appear in the message
                if (strpos($pattern, ' ') !== false) {
                    // Multi-word pattern - check if all words appear
                    $pattern_words = explode(' ', $pattern);
                    $all_words_found = true;
                    foreach ($pattern_words as $word) {
                        if (strpos($message_lower, $word) === false) {
                            $all_words_found = false;
                            break;
                        }
                    }
                    if ($all_words_found) {
                        $is_more_query = true;
                        break;
                    }
                } else {
                    // Single word pattern - check if it appears as a whole word
                    if (preg_match('/\b' . preg_quote($pattern, '/') . '\b/i', $message_lower)) {
                        $is_more_query = true;
                        break;
                    }
                }
            }
        }
        
        if ($is_more_query) {
            // Common style names
            $styles = array('Blues', 'Jazz', 'Gospel', 'Pop', 'Classical', 'Rock', 'Funk', 'Latin', 'Swing', 'Bebop');
            $levels = array('beginner', 'intermediate', 'advanced', 'pro');
            
            // Look at previous conversation to extract filters and search terms
            // Go through last 10 messages to find context
            $found_query = false;
            $found_style = false;
            $extracted_query = '';
            $extracted_style = '';
            
            foreach (array_reverse($conversation_history) as $conv) {
                // Check previous user message for search query and filters
                if (!empty($conv['message'])) {
                    $prev_msg = $conv['message'];
                    $prev_msg_lower = strtolower($prev_msg);
                    
                    // Skip if this is also a "more" query or another vague query
                    $prev_msg_lower_trimmed = strtolower(trim($prev_msg));
                    $is_prev_vague = false;
                    
                    // Check if previous message is also a "more" query
                    foreach ($more_query_patterns as $pattern) {
                        if (strpos($pattern, ' ') !== false) {
                            // Multi-word pattern
                            $pattern_words = explode(' ', $pattern);
                            $all_words_found = true;
                            foreach ($pattern_words as $word) {
                                if (strpos($prev_msg_lower_trimmed, $word) === false) {
                                    $all_words_found = false;
                                    break;
                                }
                            }
                            if ($all_words_found) {
                                $is_prev_vague = true;
                                break;
                            }
                        } else {
                            // Single word - check as whole word
                            if (preg_match('/\b' . preg_quote($pattern, '/') . '\b/i', $prev_msg_lower_trimmed)) {
                                $is_prev_vague = true;
                                break;
                            }
                        }
                    }
                    
                    if ($is_prev_vague) {
                        continue;
                    }
                    
                    // FIRST: Check for style in user message (case-insensitive, but return proper case)
                    // Do this FIRST so we can exclude it from the query extraction
                    if (!$found_style) {
                        $prev_msg_lower_for_style = strtolower($prev_msg);
                        foreach ($styles as $style) {
                            $style_lower = strtolower($style);
                            // Check if style appears in the message (case-insensitive)
                            if (stripos($prev_msg, $style) !== false || strpos($prev_msg_lower_for_style, $style_lower) !== false) {
                                $extracted_style = $style; // Return with proper capitalization
                                $found_style = true;
                                break;
                            }
                        }
                    }
                    
                    // THEN: Extract search query - look for meaningful words (not stop words, not styles)
                    if (!$found_query) {
                        $stop_words = array('i', 'want', 'to', 'learn', 'the', 'a', 'an', 'do', 'you', 'have', 'can', 'show', 'me', 'about', 'on', 'for', 'with', 'recommend', 'lessons', 'lesson', 'more', 'please', 'any', 'are', 'there', 'got');
                        // Add all style names (lowercase) to stop words
                        $style_stop_words = array_map('strtolower', $styles);
                        $stop_words = array_merge($stop_words, $style_stop_words);
                        
                        $words = preg_split('/\s+/', $prev_msg_lower);
                        $meaningful_words = array();
                        foreach ($words as $word) {
                            $word = trim($word, '.,!?;:()[]{}"\'-');
                            $word_lower = strtolower($word);
                            // Check if word is meaningful (not stop word, not style, not level, at least 3 chars)
                            if (mb_strlen($word) >= 3 && 
                                !in_array($word_lower, $stop_words) && 
                                !in_array($word, $styles) && 
                                !in_array($word_lower, array_map('strtolower', $styles)) &&
                                !in_array($word_lower, $levels)) {
                                $meaningful_words[] = $word;
                            }
                        }
                        
                        if (!empty($meaningful_words)) {
                            // Use the meaningful words as the search query
                            $extracted_query = implode(' ', $meaningful_words);
                            $found_query = true;
                        } else {
                            // If no meaningful words found, try to extract from the full message
                            // Remove stop words and styles, keep the rest
                            $cleaned_msg = $prev_msg;
                            foreach ($stop_words as $stop) {
                                $cleaned_msg = preg_replace('/\b' . preg_quote($stop, '/') . '\b/i', '', $cleaned_msg);
                            }
                            foreach ($styles as $style) {
                                $cleaned_msg = preg_replace('/\b' . preg_quote($style, '/') . '\b/i', '', $cleaned_msg);
                            }
                            $cleaned_msg = trim(preg_replace('/\s+/', ' ', $cleaned_msg));
                            if (mb_strlen($cleaned_msg) >= 3) {
                                $extracted_query = $cleaned_msg;
                                $found_query = true;
                            }
                        }
                    }
                    
                    // Check for level
                    if (empty($lesson_level_filter)) {
                        foreach ($levels as $level) {
                            if (stripos($prev_msg_lower, $level) !== false) {
                                $lesson_level_filter = $level;
                                break;
                            }
                        }
                    }
                }
                
                // Also check AI response for style mentions (in case user didn't explicitly mention it)
                if (!empty($conv['response']) && !$found_style) {
                    foreach ($styles as $style) {
                        if (stripos($conv['response'], $style) !== false) {
                            $extracted_style = $style;
                            $found_style = true;
                            break;
                        }
                    }
                }
                
                // Stop if we found both query and style, or if we've checked enough messages
                if ($found_query && $found_style) {
                    break;
                }
            }
            
            // Override search query and style if we extracted better ones
            // Always override if we found a query (even if empty, to clear "more")
            if ($found_query) {
                if (!empty($extracted_query)) {
                    $lesson_search_query = $extracted_query;
                } else {
                    // If we couldn't extract a query, at least try to use the enhanced query
                    // which might have context from enhance_query_with_context
                }
            }
            if ($found_style && !empty($extracted_style)) {
                $lesson_style_filter = $extracted_style;
            }
            
            // If we found style but not query, and the enhanced_query contains the style word,
            // try to extract the query by removing the style word
            if ($found_style && !$found_query && !empty($extracted_style)) {
                $enhanced_lower = strtolower($enhanced_query);
                $style_lower = strtolower($extracted_style);
                // Remove the style word from enhanced query to get the actual search term
                $query_without_style = preg_replace('/\b' . preg_quote($extracted_style, '/') . '\b/i', '', $enhanced_query);
                $query_without_style = preg_replace('/\b' . preg_quote($style_lower, '/') . '\b/i', '', $query_without_style);
                $query_without_style = trim(preg_replace('/\s+/', ' ', $query_without_style));
                if (mb_strlen($query_without_style) >= 3) {
                    $lesson_search_query = $query_without_style;
                    $found_query = true;
                }
            }
            
            // Log the extraction for debugging
            if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                // Get sample of conversation history for debugging
                $history_sample = array();
                foreach (array_slice($conversation_history, -5) as $conv) {
                    $history_sample[] = array(
                        'message' => !empty($conv['message']) ? substr($conv['message'], 0, 100) : '',
                        'response' => !empty($conv['response']) ? substr($conv['response'], 0, 100) : ''
                    );
                }
                
                $this->debug_logger->log('context_extraction', 'Extracted context from "more" query', array(
                    'original_message' => $message,
                    'is_more_query' => $is_more_query,
                    'ai_extracted_query' => $lesson_search_query, // Before override
                    'extracted_query' => $extracted_query,
                    'final_query' => $found_query ? $extracted_query : $lesson_search_query,
                    'extracted_style' => $extracted_style,
                    'final_style' => $found_style ? $extracted_style : $lesson_style_filter,
                    'extracted_level' => $lesson_level_filter,
                    'conversation_history_count' => count($conversation_history),
                    'conversation_history_sample' => $history_sample,
                    'found_query' => $found_query,
                    'found_style' => $found_style
                ), get_current_user_id(), $session_id, $location);
            }
        }
        
        // Get chip suggestions (lessons or collections) for this chip
        // Extract the specific interest/style from the message for improve_skill and show_style chips
        $chip_suggestions = array();
        $interest_id = null;
        
        if (!empty($chip_id)) {
            if ($chip_id === 'improve_skill') {
                // Extract skill from message (e.g., "Help me improve my improvisation" -> "improvisation")
                $interest_id = $this->extract_skill_from_message($message);
            } elseif ($chip_id === 'show_style') {
                // Extract style from message (e.g., "Show me Jazz licks" -> "Jazz")
                $interest_id = $this->extract_style_from_message($message);
            }
            
            // Look up suggestions using the extracted interest/style ID
            if (!empty($interest_id)) {
                $chip_suggestions = $this->database->get_chip_suggestions($interest_id);
                
                // Log chip suggestions for debugging
                if ($this->debug_logger && $this->debug_logger->is_enabled() && !empty($chip_suggestions)) {
                    $this->debug_logger->log('chip_suggestions', 'Found chip suggestions', array(
                        'chip_id' => $chip_id,
                        'interest_id' => $interest_id,
                        'suggestions_count' => count($chip_suggestions),
                        'suggestions' => $chip_suggestions
                    ), get_current_user_id(), $session_id, $location);
                }
            }
        }
        
        $lesson_search_results = array();
        if ($this->lesson_search->is_available()) {
            $lesson_search_results = $this->lesson_search->search($lesson_search_query, 10, $lesson_style_filter, $lesson_level_filter, $lesson_tag_filter);
            
            // If we have assigned lessons, prioritize them by prepending to results
            // Process chip suggestions: add lessons/collections to results
            if (!empty($chip_suggestions)) {
                global $wpdb;
                $lessons_table = $wpdb->prefix . 'alm_lessons';
                $collections_table = $wpdb->prefix . 'alm_collections';
                
                $prioritized_results = array();
                $remaining_results = array();
                $suggestion_ids = array();
                
                // Collect all suggestion IDs
                foreach ($chip_suggestions as $suggestion) {
                    if ($suggestion['type'] === 'lesson') {
                        $suggestion_ids[] = absint($suggestion['id']);
                    }
                }
                
                // Separate suggested lessons from other results
                foreach ($lesson_search_results as $result) {
                    $result_lesson_id = !empty($result['lesson_id']) ? absint($result['lesson_id']) : 0;
                    $suggestion_index = array_search($result_lesson_id, $suggestion_ids);
                    if ($suggestion_index !== false) {
                        // Mark as chip suggestion and add priority
                        $result['match_type'] = 'chip_suggestion';
                        $result['priority'] = $chip_suggestions[$suggestion_index]['priority'];
                        $prioritized_results[] = $result;
                    } else {
                        $remaining_results[] = $result;
                    }
                }
                
                // Add any suggested lessons that weren't in search results
                foreach ($chip_suggestions as $suggestion) {
                    if ($suggestion['type'] !== 'lesson') {
                        continue; // Collections handled separately
                    }
                    
                    $suggestion_id = absint($suggestion['id']);
                    // Check if already in prioritized_results
                    $already_added = false;
                    foreach ($prioritized_results as $existing) {
                        if (!empty($existing['lesson_id']) && absint($existing['lesson_id']) === $suggestion_id) {
                            $already_added = true;
                            break;
                        }
                    }
                    
                    if (!$already_added) {
                        // Fetch lesson details
                        $lesson = $wpdb->get_row($wpdb->prepare(
                            "SELECT ID, lesson_title, post_id, slug, lesson_description, lesson_level, lesson_tags, lesson_style
                             FROM {$lessons_table}
                             WHERE ID = %d AND post_id > 0 AND (status = 'published' OR status IS NULL)",
                            $suggestion_id
                        ), ARRAY_A);
                        
                        if ($lesson) {
                            $lesson_url = 'https://jazzedge.academy/lesson/' . $lesson['slug'] . '/';
                            $prioritized_results[] = array(
                                'lesson_id' => $suggestion_id,
                                'lesson_title' => $lesson['lesson_title'],
                                'post_id' => $lesson['post_id'],
                                'slug' => $lesson['slug'],
                                'lesson_url' => $lesson_url,
                                'url' => $lesson_url,
                                'match_type' => 'chip_suggestion',
                                'priority' => $suggestion['priority']
                            );
                        }
                    }
                }
                
                // Sort prioritized by priority (lower = higher priority)
                usort($prioritized_results, function($a, $b) {
                    $priority_a = isset($a['priority']) ? $a['priority'] : 999;
                    $priority_b = isset($b['priority']) ? $b['priority'] : 999;
                    return $priority_a <=> $priority_b;
                });
                
                // Combine: chip suggestions first, then other results
                $lesson_search_results = array_merge($prioritized_results, $remaining_results);
                
                // Limit to 10 total
                $lesson_search_results = array_slice($lesson_search_results, 0, 10);
            }
            
            // Log database search results for debugging
            if ($this->debug_logger->is_enabled()) {
                if (empty($lesson_search_results)) {
                    $this->debug_logger->log('lesson_search', 'No database search results found for query: "' . $message . '"', array(
                        'query' => $message,
                        'search_type' => 'database_search',
                        'max_results' => 10
                    ), get_current_user_id(), $session_id, $location);
                } else {
                    // Log which lessons were found
                    $lessons_found = array();
                    foreach ($lesson_search_results as $result) {
                        $lesson_info = array(
                            'lesson_id' => !empty($result['lesson_id']) ? $result['lesson_id'] : 'N/A',
                            'lesson_title' => !empty($result['lesson_title']) ? $result['lesson_title'] : 'N/A',
                            'match_type' => !empty($result['match_type']) ? $result['match_type'] : 'N/A',
                            'url' => !empty($result['lesson_url']) ? $result['lesson_url'] : 'N/A'
                        );
                        if (!empty($result['keyword'])) {
                            $lesson_info['keyword_matched'] = $result['keyword'];
                        }
                        $lessons_found[] = $lesson_info;
                    }
                    
                    $log_message = 'Found ' . count($lesson_search_results) . ' database search result(s) for query: "' . $message . '"';
                    $lesson_titles = array();
                    foreach ($lesson_search_results as $result) {
                        $title = !empty($result['lesson_title']) ? $result['lesson_title'] : 'N/A';
                        if (!empty($result['match_type']) && $result['match_type'] === 'keyword_match') {
                            $title .= ' (keyword match)';
                        }
                        $lesson_titles[] = $title;
                    }
                    if (!empty($lesson_titles)) {
                        $log_message .= ' | Lessons: ' . implode('; ', $lesson_titles);
                    }
                    
                    $this->debug_logger->log('lesson_search', $log_message, array(
                        'query' => $message,
                        'search_type' => 'database_search',
                        'max_results' => 10,
                        'results_count' => count($lesson_search_results),
                        'lessons_found' => $lessons_found
                    ), get_current_user_id(), $session_id, $location);
                }
            }
        }
        
        // Combine results (prioritize embedding results, add database search results)
        $all_lesson_results = $this->combine_lesson_results($embedding_results, $lesson_search_results);
        
        // Build messages array for AI
        $messages = $this->build_messages_array($session_id, $message, $location, $context_data, $all_lesson_results, $chip_id);
        
        // Get AI response
        $ai_result = $this->get_ai_response($location, $messages);
        
        if (is_wp_error($ai_result)) {
            // Log error
            if ($this->debug_logger->is_enabled()) {
                $this->debug_logger->log('api_error', 'AI API error: ' . $ai_result->get_error_message(), array(
                    'user_id' => $user_id,
                    'session_id' => $session_id,
                    'error' => $ai_result->get_error_message(),
                    'error_code' => $ai_result->get_error_code()
                ), $user_id, $session_id, $location);
            }
            return $ai_result;
        }
        
        // Extract response and token usage
        $ai_response = is_array($ai_result) ? $ai_result['content'] : $ai_result;
        $tokens_used = is_array($ai_result) && isset($ai_result['tokens_used']) ? (int) $ai_result['tokens_used'] : 0;
        
        // Debug: Log token extraction
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Academy AI Assistant: Token extraction - ai_result type: ' . gettype($ai_result));
            if (is_array($ai_result)) {
                error_log('Academy AI Assistant: Token extraction - ai_result keys: ' . implode(', ', array_keys($ai_result)));
                error_log('Academy AI Assistant: Token extraction - tokens_used value: ' . $tokens_used);
            }
        }
        
        // Record token usage
        if ($tokens_used > 0) {
            $record_result = $this->token_limits->record_usage($user_id, $tokens_used);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Academy AI Assistant: Token recording - result: ' . ($record_result ? 'success' : 'failed') . ', tokens: ' . $tokens_used . ', user_id: ' . $user_id);
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Academy AI Assistant: Token recording skipped - tokens_used is 0 or not set');
            }
        }
        
        // CRITICAL: Validate AI response - if no lessons were found, ensure AI didn't make up lesson recommendations
        if (empty($all_lesson_results)) {
            $ai_response = $this->validate_no_hallucinated_lessons($ai_response, $all_lesson_results);
        }
        
        // Ensure community mentions are linked
        $ai_response = $this->ensure_community_links($ai_response);
        
        // Auto-link lesson titles that are mentioned but not linked
        if (!empty($all_lesson_results)) {
            $ai_response = $this->auto_link_mentioned_lessons($ai_response, $all_lesson_results);
        }
        
        // Note: "Add to Favorites" links are now added client-side in JavaScript
        // This ensures they work correctly with markdown conversion
        
        // Save conversation
        $conversation_id = $this->save_conversation(
            $user_id,
            $session_id,
            $location,
            $message,
            $ai_response,
            $context_data
        );
        
        // Log if conversation save failed
        if ($conversation_id === 0) {
            error_log('Academy AI Assistant: CRITICAL - Failed to save conversation! User: ' . $user_id . ', Session: ' . $session_id);
            // Don't fail the request, but log the error
        } else {
            // Log success for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Academy AI Assistant: Successfully saved conversation ID: ' . $conversation_id . ' for session ID: ' . $session_id);
            }
        }
        
        // Get usage stats for response
        $usage_stats = $this->token_limits->get_usage_stats($user_id);
        
        // Log debug info if enabled
        if ($this->debug_logger->is_enabled()) {
            $this->debug_logger->log('chat_message', 'Chat message processed', array(
                'user_id' => $user_id,
                'session_id' => $session_id,
                'conversation_id' => $conversation_id,
                'location' => $location,
                'message' => $message,
                'response' => $ai_response,
                'context_used' => $use_context,
                'embeddings_used' => $use_embeddings,
                'embedding_results_count' => count($embedding_results),
                'tokens_used' => $tokens_used
            ), $user_id, $session_id, $location, null, $tokens_used);
        }
        
        return rest_ensure_response(array(
            'conversation_id' => $conversation_id,
            'session_id' => $session_id,
            'response' => $ai_response,
            'location' => $location,
            'usage' => $usage_stats,
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Get conversations for a session
     */
    public function handle_get_conversations($request) {
        $user_id = get_current_user_id();
        $session_id = absint($request->get_param('session_id'));
        $page = absint($request->get_param('page'));
        $per_page = absint($request->get_param('per_page'));
        
        if ($session_id === 0) {
            return new WP_Error(
                'invalid_session',
                'Session ID is required.',
                array('status' => 400)
            );
        }
        
        // Verify session ownership
        if (!$this->verify_session_ownership($session_id, $user_id)) {
            return new WP_Error(
                'invalid_session',
                'Session not found or access denied.',
                array('status' => 403)
            );
        }
        
        $conversations = $this->database->get_conversations($session_id, $page, $per_page);
        $total = $this->database->get_conversation_count($session_id);
        
        return rest_ensure_response(array(
            'conversations' => $conversations,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ));
    }
    
    /**
     * Get all sessions for current user
     */
    public function handle_get_sessions($request) {
        $user_id = get_current_user_id();
        $page = absint($request->get_param('page'));
        $per_page = absint($request->get_param('per_page'));
        
        // Debug logging
        if ($this->debug_logger && $this->debug_logger->is_enabled()) {
            $this->debug_logger->log('get_sessions_request', 'Getting user sessions', array(
                'user_id' => $user_id,
                'page' => $page,
                'per_page' => $per_page
            ), $user_id, null, null);
        }
        
        if (!$user_id) {
            error_log('Academy AI Assistant: handle_get_sessions called but user not logged in');
            return new WP_Error(
                'not_authenticated',
                'User must be logged in to access sessions.',
                array('status' => 401)
            );
        }
        
        try {
            $sessions = $this->database->get_user_sessions($user_id, $page, $per_page);
            $total = $this->database->get_user_session_count($user_id);
            
            // Log raw results for debugging
            error_log('Academy AI Assistant: handle_get_sessions - Raw sessions from DB: ' . print_r($sessions, true));
            error_log('Academy AI Assistant: handle_get_sessions - Total count: ' . $total);
            error_log('Academy AI Assistant: handle_get_sessions - Sessions is array: ' . (is_array($sessions) ? 'yes' : 'no'));
            
            // Debug logging
            if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                $this->debug_logger->log('get_sessions_response', 'Returning sessions', array(
                    'user_id' => $user_id,
                    'sessions_count' => count($sessions),
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $per_page,
                    'sessions_data' => $sessions
                ), $user_id, null, null);
            }
            
            // Ensure sessions is always an array
            if (!is_array($sessions)) {
                error_log('Academy AI Assistant: get_user_sessions returned non-array: ' . gettype($sessions));
                $sessions = array();
            }
            
            $response = array(
                'sessions' => $sessions,
                'total' => $total,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => $per_page > 0 ? ceil($total / $per_page) : 1
            );
            
            error_log('Academy AI Assistant: handle_get_sessions - Final response: ' . print_r($response, true));
            
            return rest_ensure_response($response);
        } catch (Exception $e) {
            error_log('Academy AI Assistant: Exception in handle_get_sessions: ' . $e->getMessage());
            return new WP_Error(
                'database_error',
                'Error retrieving sessions: ' . $e->getMessage(),
                array('status' => 500)
            );
        }
    }
    
    /**
     * Get most recent session with conversations (optimized single call)
     * This combines session lookup + conversation loading for faster page load
     */
    public function handle_get_recent_session($request) {
        $user_id = get_current_user_id();
        $conversation_limit = min(absint($request->get_param('conversation_limit')), 50); // Max 50 conversations
        $session_id_param = absint($request->get_param('session_id')); // Optional: get specific session
        
        if ($conversation_limit === 0) {
            $conversation_limit = 50; // Default
        }
        
        $session_data = null;
        $conversations = array();
        
        if ($session_id_param > 0) {
            // Get specific session
            $sessions = $this->database->get_user_sessions($user_id, 1, 100);
            foreach ($sessions as $session) {
                if ($session['id'] == $session_id_param) {
                    $session_data = $session;
                    break;
                }
            }
            
            if ($session_data) {
                $conversations = $this->database->get_conversations($session_id_param, 1, $conversation_limit);
            }
        } else {
            // Get most recent session
            $sessions = $this->database->get_user_sessions($user_id, 1, 1);
            
            if (!empty($sessions) && isset($sessions[0]['id'])) {
                $session_data = $sessions[0];
                $session_id = absint($session_data['id']);
                
                // Get conversations for this session
                $conversations = $this->database->get_conversations($session_id, 1, $conversation_limit);
            }
        }
        
        // Format conversations (remove heavy fields like context_data for list view)
        $formatted_conversations = array();
        foreach ($conversations as $conv) {
            $formatted_conversations[] = array(
                'id' => $conv['id'],
                'message' => $conv['message'],
                'response' => $conv['response'],
                'location' => isset($conv['location']) ? $conv['location'] : 'main',
                'created_at' => $conv['created_at']
            );
        }
        
        $session_response = null;
        if ($session_data) {
            $session_response = array(
                'id' => $session_data['id'],
                'location' => isset($session_data['location']) ? $session_data['location'] : 'main',
                'session_name' => isset($session_data['session_name']) ? $session_data['session_name'] : null,
                'created_at' => isset($session_data['created_at']) ? $session_data['created_at'] : null,
                'updated_at' => isset($session_data['updated_at']) ? $session_data['updated_at'] : null
            );
        }
        
        return rest_ensure_response(array(
            'session' => $session_response,
            'conversations' => $formatted_conversations
        ));
    }
    
    /**
     * Create new conversation session
     */
    public function handle_create_session($request) {
        $user_id = get_current_user_id();
        $location = sanitize_text_field($request->get_param('location'));
        $session_name = sanitize_text_field($request->get_param('session_name'));
        
        // Default to 'main' if no location specified
        if (empty($location)) {
            $location = 'main';
        }
        
        $session_id = $this->create_session($user_id, $location, $session_name);
        
        if (is_wp_error($session_id)) {
            return $session_id;
        }
        
        // Get the created session
        $sessions = $this->database->get_user_sessions($user_id, 1, 100);
        $new_session = null;
        foreach ($sessions as $session) {
            if ($session['id'] == $session_id) {
                $new_session = $session;
                break;
            }
        }
        
        return rest_ensure_response(array(
            'session_id' => $session_id,
            'session' => $new_session
        ));
    }
    
    
    /**
     * Download chat transcript
     */
    public function handle_download_transcript($request) {
        $user_id = get_current_user_id();
        $session_id = absint($request->get_param('session_id'));
        $format = sanitize_text_field($request->get_param('format'));
        
        if ($session_id === 0) {
            return new WP_Error(
                'invalid_session',
                'Session ID is required.',
                array('status' => 400)
            );
        }
        
        // Verify session ownership
        if (!$this->verify_session_ownership($session_id, $user_id)) {
            return new WP_Error(
                'invalid_session',
                'Session not found or access denied.',
                array('status' => 403)
            );
        }
        
        // Get session info
        $sessions = $this->database->get_user_sessions($user_id, 1, 100);
        $session = null;
        foreach ($sessions as $s) {
            if ($s['id'] == $session_id) {
                $session = $s;
                break;
            }
        }
        
        if (!$session) {
            return new WP_Error(
                'session_not_found',
                'Session not found.',
                array('status' => 404)
            );
        }
        
        // Get all conversations for this session
        $conversations = $this->database->get_conversations($session_id, 1, 1000); // Get up to 1000 messages
        
        // Format transcript
        $transcript = $this->format_transcript($session, $conversations, $format);
        
        // Set headers for download
        $session_name = !empty($session['session_name']) ? sanitize_file_name($session['session_name']) : 'chat-transcript';
        $filename = $session_name . '-' . date('Y-m-d') . '.' . $format;
        
        if ($format === 'txt') {
            // Store transcript data for the filter
            $this->_transcript_data = array(
                'content' => $transcript,
                'filename' => $filename
            );
            
            // Use filter to intercept and serve file download
            add_filter('rest_pre_serve_request', array($this, 'serve_transcript_download'), 10, 4);
            
            // Return file content as response
            $response = new WP_REST_Response($transcript, 200);
            $response->header('Content-Type', 'text/plain; charset=utf-8');
            $response->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            $response->header('Content-Length', strlen($transcript));
            
            return $response;
        } else {
            // PDF format - for now, return error as PDF requires additional library
            return new WP_Error(
                'pdf_not_supported',
                'PDF format is not yet supported. Please use TXT format.',
                array('status' => 501)
            );
        }
    }
    
    /**
     * Format transcript for download
     */
    private function format_transcript($session, $conversations, $format = 'txt') {
        $session_name = !empty($session['session_name']) ? $session['session_name'] : 'New Chat';
        $created_date = !empty($session['created_at']) ? date('F j, Y g:i A', strtotime($session['created_at'])) : date('F j, Y g:i A');
        $updated_date = !empty($session['updated_at']) ? date('F j, Y g:i A', strtotime($session['updated_at'])) : $created_date;
        
        $transcript = "JAZZEDGE AI CHAT TRANSCRIPT\n";
        $transcript .= str_repeat("=", 60) . "\n\n";
        $transcript .= "Session: " . $session_name . "\n";
        $transcript .= "Created: " . $created_date . "\n";
        $transcript .= "Last Updated: " . $updated_date . "\n";
        $transcript .= "Total Messages: " . count($conversations) . "\n\n";
        $transcript .= str_repeat("=", 60) . "\n\n";
        
        if (empty($conversations)) {
            $transcript .= "No messages in this conversation.\n";
            return $transcript;
        }
        
        foreach ($conversations as $index => $conv) {
            $message_num = $index + 1;
            $timestamp = !empty($conv['created_at']) ? date('g:i A', strtotime($conv['created_at'])) : '';
            
            // User message
            $transcript .= "\n[" . $message_num . "] USER" . ($timestamp ? " (" . $timestamp . ")" : "") . ":\n";
            $transcript .= str_repeat("-", 60) . "\n";
            $transcript .= strip_tags($conv['message']) . "\n\n";
            
            // AI response
            $transcript .= "JAZZEDGE AI:\n";
            $transcript .= str_repeat("-", 60) . "\n";
            // Strip HTML tags and convert markdown-style formatting to plain text
            $response = $conv['response'];
            // Remove markdown links but keep the text
            $response = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $response);
            // Remove HTML tags
            $response = strip_tags($response);
            // Convert markdown bold/italic to plain text
            $response = preg_replace('/\*\*([^\*]+)\*\*/', '$1', $response);
            $response = preg_replace('/\*([^\*]+)\*/', '$1', $response);
            // Clean up multiple newlines
            $response = preg_replace('/\n{3,}/', "\n\n", $response);
            $transcript .= trim($response) . "\n\n";
            $transcript .= str_repeat("=", 60) . "\n";
        }
        
        $transcript .= "\n\n--- End of Transcript ---\n";
        $transcript .= "Generated: " . date('F j, Y g:i A') . "\n";
        
        return $transcript;
    }
    
    /**
     * Serve transcript download with proper headers
     */
    public function serve_transcript_download($served, $result, $request, $server) {
        // Only handle our transcript endpoint
        if (strpos($request->get_route(), '/transcript/') === false) {
            return $served;
        }
        
        // Check if we have transcript data
        if (empty($this->_transcript_data)) {
            return $served;
        }
        
        // Clear any output buffers
        if (function_exists('ob_get_level')) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
        
        // Send headers
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $this->_transcript_data['filename'] . '"');
        header('Content-Length: ' . strlen($this->_transcript_data['content']));
        header('Cache-Control: private, no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output content
        echo $this->_transcript_data['content'];
        
        // Clear transcript data
        $this->_transcript_data = null;
        
        // Mark as served
        return true;
    }
    
    /**
     * Delete conversation session
     */
    public function handle_delete_session($request) {
        $user_id = get_current_user_id();
        $session_id = absint($request->get_param('id'));
        
        // Verify session ownership
        if (!$this->verify_session_ownership($session_id, $user_id)) {
            return new WP_Error(
                'invalid_session',
                'Session not found or access denied.',
                array('status' => 403)
            );
        }
        
        $deleted = $this->database->delete_session($session_id);
        
        if (!$deleted) {
            return new WP_Error(
                'delete_failed',
                'Failed to delete session.',
                array('status' => 500)
            );
        }
        
        return rest_ensure_response(array(
            'deleted' => true,
            'session_id' => $session_id
        ));
    }
    
    /**
     * Get or create session for user
     */
    private function get_or_create_session($user_id, $location) {
        // Try to get most recent session
        $sessions = $this->database->get_user_sessions($user_id, 1, 1);
        
        if (!empty($sessions) && isset($sessions[0]['id'])) {
            return absint($sessions[0]['id']);
        }
        
        // Create new session
        return $this->create_session($user_id, $location);
    }
    
    /**
     * Update session name
     */
    public function handle_update_session_name($request) {
        $user_id = get_current_user_id();
        $session_id = absint($request->get_param('id'));
        $session_name = sanitize_text_field($request->get_param('session_name'));
        
        // Verify session ownership
        if (!$this->verify_session_ownership($session_id, $user_id)) {
            return new WP_Error(
                'invalid_session',
                'Session not found or access denied.',
                array('status' => 403)
            );
        }
        
        $updated = $this->database->update_session_name($session_id, $session_name);
        
        if (!$updated) {
            return new WP_Error(
                'update_failed',
                'Failed to update session name.',
                array('status' => 500)
            );
        }
        
        // Get updated session
        $sessions = $this->database->get_user_sessions($user_id, 1, 100);
        $updated_session = null;
        foreach ($sessions as $session) {
            if ($session['id'] == $session_id) {
                $updated_session = $session;
                break;
            }
        }
        
        return rest_ensure_response(array(
            'session_id' => $session_id,
            'session' => $updated_session
        ));
    }
    
    /**
     * Create new session (private helper)
     */
    private function create_session($user_id, $location, $session_name = '') {
        global $wpdb;
        
        // Sanitize session name and location
        $session_name = sanitize_text_field($session_name);
        $location = sanitize_text_field($location);
        if (strlen($session_name) > 255) {
            $session_name = substr($session_name, 0, 255);
        }
        if (empty(trim($session_name))) {
            $session_name = null;
        }
        if (empty($location)) {
            $location = 'main';
        }
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'aaa_conversation_sessions',
            array(
                'user_id' => $user_id,
                'location' => $location,
                'session_name' => $session_name
            ),
            array('%d', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error(
                'session_creation_failed',
                'Failed to create conversation session.',
                array('status' => 500)
            );
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get location prompt (custom or default)
     * 
     * @param string $location Location identifier (e.g., 'main', 'dashboard', 'sidebar')
     * @return string System prompt
     */
    private function get_location_prompt($location) {
        $custom_prompts = get_option('aaa_custom_prompts', array());
        
        // Return custom prompt if exists
        if (isset($custom_prompts[$location]) && !empty($custom_prompts[$location])) {
            // Remove any slashes that might have been added
            return wp_unslash($custom_prompts[$location]);
        }
        
        // Default prompt for Jazzedge AI
        return $this->get_default_prompt();
    }
    
    /**
     * Get default system prompt for Jazzedge AI
     * 
     * @return string Default system prompt
     */
    private function get_default_prompt() {
        return "You are Jazzedge AI, a knowledgeable and friendly music teacher and professional musician. " .
               "You help students learn jazz piano, music theory, and piano technique. " .
               "You are encouraging, patient, and provide clear explanations. " .
               "You recommend relevant lessons from the JazzEdge Academy when appropriate. " .
               "You always provide accurate music theory information and admit when you're uncertain.";
    }
    
    /**
     * Get music theory accuracy requirements section (customizable)
     * 
     * @return string Music theory accuracy section
     */
    /**
     * Get chip-specific prompt
     */
    private function get_chip_prompt($chip_id) {
        $chip_prompts = get_option('aaa_chip_prompts', array());
        return isset($chip_prompts[$chip_id]) ? wp_unslash($chip_prompts[$chip_id]) : '';
    }
    
    /**
     * Extract skill from improve_skill message
     * Example: "Help me improve my improvisation" -> "improvisation"
     * 
     * @param string $message User message
     * @return string Skill name or empty string
     */
    private function extract_skill_from_message($message) {
        // Normalize message
        $message = strtolower(trim($message));
        
        // List of all possible skills (alphabetized)
        $skills = array(
            'arranging jazz standards',
            'build jazz repertoire',
            'build rock repertoire',
            'chord voicings',
            'comping',
            'ear training',
            'improvisation',
            'jazz and blues licks',
            'rhythm',
            'sight reading',
            'slow blues',
            'step-by-step improvisation',
            'technique'
        );
        
        // Try to find skill in message (check longer phrases first)
        usort($skills, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        foreach ($skills as $skill) {
            if (stripos($message, $skill) !== false) {
                return $skill;
            }
        }
        
        // Fallback: try to extract after "improve my" or "improve"
        if (preg_match('/improve\s+(?:my\s+)?([a-z\s]+)/i', $message, $matches)) {
            $extracted = trim($matches[1]);
            // Normalize and check if it matches any skill
            foreach ($skills as $skill) {
                if (strtolower($skill) === strtolower($extracted) || 
                    strtolower($skill) === strtolower(str_replace(' ', '', $extracted))) {
                    return $skill;
                }
            }
        }
        
        return '';
    }
    
    /**
     * Extract style from show_style message
     * Example: "Show me Jazz licks" -> "Jazz"
     * 
     * @param string $message User message
     * @return string Style name or empty string
     */
    private function extract_style_from_message($message) {
        // Normalize message
        $message = strtolower(trim($message));
        
        // List of all possible styles
        $styles = array(
            'Jazz',
            'Cocktail',
            'Blues',
            'Rock',
            'Funk',
            'Latin',
            'Classical',
            'Smooth Jazz',
            'Holiday',
            'Ballad',
            'Pop',
            'New Age',
            'Gospel',
            'New Orleans',
            'Country',
            'Modal',
            'Stride',
            'Organ',
            'Boogie'
        );
        
        // Try to find style in message (check longer phrases first, case-insensitive)
        usort($styles, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        foreach ($styles as $style) {
            $style_lower = strtolower($style);
            // Check if style appears in message
            if (stripos($message, $style_lower) !== false) {
                return $style_lower; // Return lowercase for consistency
            }
        }
        
        return '';
    }
    
    private function get_music_theory_accuracy_section() {
        $custom_sections = get_option('aaa_custom_sections', array());
        
        if (isset($custom_sections['music_theory_accuracy']) && !empty($custom_sections['music_theory_accuracy'])) {
            return wp_unslash($custom_sections['music_theory_accuracy']);
        }
        
        // Default music theory accuracy section
        return "🎓 CRITICAL: MUSIC THEORY ACCURACY:\n" .
               "1. You MUST provide ACCURATE music theory information. If you're unsure about harmony, chord construction, scales, or music theory, you MUST say so rather than guessing.\n" .
               "2. For chord questions: Know that a minor 7th chord contains root, minor 3rd, perfect 5th, and minor 7th. For example, Em7 = E-G-B-D (D is the 7th, NOT F#).\n" .
               "3. F# over E minor would be the 9th (or 2nd), not the 7th. Be precise with interval names and chord construction.\n" .
               "4. If you make a mistake or are corrected, acknowledge it and provide the correct information.\n" .
               "5. When explaining music theory, use correct terminology and be precise. Students rely on your accuracy.";
    }
    
    /**
     * Get competitor blocking section (customizable)
     * 
     * @return string Competitor blocking section
     */
    private function get_competitor_blocking_section() {
        $custom_sections = get_option('aaa_custom_sections', array());
        
        if (isset($custom_sections['competitor_blocking']) && !empty($custom_sections['competitor_blocking'])) {
            return wp_unslash($custom_sections['competitor_blocking']);
        }
        
        // Default competitor blocking section
        return "🚫 CRITICAL: COMPETITOR BLOCKING:\n" .
               "1. You MUST NEVER mention, recommend, or discuss competitors or alternative piano learning platforms.\n" .
               "2. NEVER mention competitors such as: Pianote, PianoWithJonny, Playground Sessions, Flowkey, Simply Piano, Skoove, or any other piano learning platforms.\n" .
               "3. If a user asks about competitors, politely redirect them by saying something like: 'I focus on helping you with JazzEdge Academy content. How can I help you with jazz piano today?'\n" .
               "4. NEVER provide information about competitor platforms, their features, pricing, or content.\n" .
               "5. NEVER compare JazzEdge Academy to competitors.\n" .
               "6. If asked about competitors, simply state that you're here to help with JazzEdge Academy lessons and content only.\n" .
               "7. Focus exclusively on JazzEdge Academy lessons, content, and resources.";
    }
    
    /**
     * Get lesson recommendation instructions section (customizable)
     * 
     * @param bool $has_lessons Whether lessons were found
     * @return string Lesson recommendation section
     */
    private function get_lesson_recommendation_section($has_lessons) {
        $custom_sections = get_option('aaa_custom_sections', array());
        
        if ($has_lessons) {
            // Instructions when lessons are found
            if (isset($custom_sections['lesson_recommendations_found']) && !empty($custom_sections['lesson_recommendations_found'])) {
                return wp_unslash($custom_sections['lesson_recommendations_found']);
            }
            
            // Default lesson recommendations section (when lessons found)
            return "🎯 CRITICAL INSTRUCTIONS FOR LESSON RECOMMENDATIONS:\n" .
                   "1. The above lessons were found by searching the lesson database and are RELEVANT to the user's question.\n" .
                   "2. You MUST PROACTIVELY recommend these lessons whenever they are relevant, even if the user doesn't explicitly ask for recommendations.\n" .
                   "3. If the user asks a question about a topic (chords, scales, techniques, songs, etc.), and lessons are found above, you MUST include lesson recommendations in your response.\n" .
                   "4. If lessons are marked as 'keyword_match', prioritize those FIRST as they are specifically matched to the user's query.\n" .
                   "5. When recommending lessons, naturally integrate them into your answer. For example:\n" .
                   "   - 'To learn more about this, check out [Lesson Title](URL).'\n" .
                   "   - 'I recommend [Lesson Title](URL) which covers this topic in detail.'\n" .
                   "   - 'For hands-on practice, see [Lesson Title - Chapter Name](URL) which demonstrates this at [timestamp].'\n" .
                   "6. If a lesson has a chapter title listed (e.g., 'Chapter: Mixolydian Scale'), you MUST mention the chapter name in your response.\n" .
                   "7. If a lesson has a timestamp listed (e.g., 'starts at 0:54' or 'at 54 seconds'), you MUST mention this timestamp in your response text (e.g., 'at 54 seconds' or 'starting at 0:54').\n" .
                   "8. Chapter URLs use the format ?c=chapter-slug (e.g., ?c=176-part-2) - these link directly to the specific chapter.\n" .
                   "9. When creating links, use ONLY the exact URLs provided above in the format: [Lesson Title - Chapter Name](EXACT_URL_FROM_ABOVE).\n" .
                   "10. NEVER generate, guess, or create URLs from lesson titles. ONLY use the exact URLs listed above.\n" .
                   "11. If a lesson is listed above with a URL, you MUST use that exact URL - do not modify it.\n" .
                   "12. Do NOT say you don't have lessons if lessons are listed above with URLs.\n" .
                   "13. Copy the URL exactly as shown above - do not change /lesson/ to /lessons/ or modify the slug.\n" .
                   "14. PROACTIVELY suggest lessons - don't wait for the user to ask. If lessons are relevant to their question, include them naturally in your response.\n" .
                   "15. CRITICAL: If you mention a lesson title in your response, you MUST create a markdown link for it using the format [Lesson Title](URL). Never mention a lesson title without linking it if a URL is provided above.";
        } else {
            // Instructions when no lessons found
            if (isset($custom_sections['lesson_recommendations_none']) && !empty($custom_sections['lesson_recommendations_none'])) {
                return wp_unslash($custom_sections['lesson_recommendations_none']);
            }
            
            // Default lesson recommendations section (when no lessons found)
            return "🚨 CRITICAL: NO LESSONS WERE FOUND IN THE DATABASE SEARCH.\n\n" .
                   "ABSOLUTE PROHIBITION - YOU MUST FOLLOW THESE RULES:\n" .
                   "1. DO NOT recommend, suggest, mention, or reference ANY specific lesson titles.\n" .
                   "2. DO NOT make up, invent, or create lesson names like 'Blues Piano Basics', 'Essential Blues Piano Techniques', 'The 12-Bar Blues Structure', 'Jazz & Blues Improvisation Techniques', 'Advanced Blues Concepts', 'Jazz Piano Basics', 'Introduction to Swing', or ANY other lesson titles.\n" .
                   "3. DO NOT create or generate URLs for lessons that don't exist.\n" .
                   "4. DO NOT use markdown links [Lesson Title](url) for lessons that were not found.\n" .
                   "5. DO NOT reference lessons from previous conversations - those lessons may not exist or may not be relevant now.\n" .
                   "6. If the user asks for 'more' lessons or additional recommendations, you MUST say: 'I couldn't find any additional lessons matching your request in the database.'\n" .
                   "7. You can ONLY provide general guidance, advice, or explanations - NEVER specific lesson recommendations.\n" .
                   "8. If you mention lessons at all, you MUST explicitly state: 'I couldn't find specific lessons in the database for this topic.'\n" .
                   "9. REMEMBER: The database search returned ZERO results. There are NO lessons to recommend.\n" .
                   "10. If you violate these rules and recommend non-existent lessons, you are providing false information to the user.\n\n" .
                   "EXAMPLE OF CORRECT RESPONSE: 'I couldn't find any additional lessons matching your request in the database. However, I can provide some general guidance about [topic]...'\n" .
                   "EXAMPLE OF INCORRECT RESPONSE: 'Here are some additional lessons: [Blues Piano Basics](url)...' - THIS IS FORBIDDEN.";
        }
    }
    
    /**
     * Enhance query with context from conversation history
     * If query is vague (e.g., "this", "that", "it"), extract context from previous messages
     * 
     * @param string $current_query Current user query
     * @param array $conversation_history Recent conversation messages
     * @return string Enhanced query with context
     */
    private function enhance_query_with_context($current_query, $conversation_history) {
        // Check if query is vague (contains only pronouns or very short)
        $vague_indicators = array('this', 'that', 'it', 'them', 'those', 'these');
        $query_lower = strtolower(trim($current_query));
        $query_words = preg_split('/\s+/', $query_lower);
        
        // Check if query is too vague
        $is_vague = false;
        if (count($query_words) <= 3) {
            // Check if it contains vague indicators
            foreach ($vague_indicators as $indicator) {
                if (in_array($indicator, $query_words)) {
                    $is_vague = true;
                    break;
                }
            }
        }
        
        // If vague, extract context from conversation history
        if ($is_vague && !empty($conversation_history)) {
            // Get the most recent user message and AI response
            $context_parts = array();
            
            // Look at last few messages (user questions and AI responses)
            foreach (array_reverse($conversation_history) as $msg) {
                // Get user's previous question
                if (!empty($msg['message'])) {
                    $context_parts[] = $msg['message'];
                }
                // Get AI's response (might contain relevant keywords)
                if (!empty($msg['response'])) {
                    // Extract key terms from AI response (first 200 chars)
                    $response_snippet = wp_trim_words($msg['response'], 30);
                    $context_parts[] = $response_snippet;
                }
                
                // Only use last 2-3 messages for context
                if (count($context_parts) >= 2) {
                    break;
                }
            }
            
            if (!empty($context_parts)) {
                // Combine current query with context
                $context_text = implode(' ', array_reverse($context_parts));
                $enhanced = $current_query . ' ' . $context_text;
                
                // Log the enhancement
                if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                    $this->debug_logger->log('query_enhancement', 'Enhanced vague query with context', array(
                        'original_query' => $current_query,
                        'enhanced_query' => $enhanced,
                        'context_used' => $context_parts
                    ), get_current_user_id(), null, null);
                }
                
                return $enhanced;
            }
        }
        
        return $current_query;
    }
    
    /**
     * Check if query needs AI preprocessing
     * 
     * @param string $original_query Original user query
     * @param string $enhanced_query Enhanced query (after context enhancement)
     * @return bool True if AI preprocessing is recommended
     */
    private function query_needs_ai_preprocessing($original_query, $enhanced_query) {
        // Comprehensive list of vague indicators that suggest AI would help
        $vague_indicators = array(
            // General vague references
            'another', 'other', 'similar', 'like', 'this', 'that', 'it', 'them', 'these', 'those',
            // "More" variations (subset - full list is in context extraction)
            'more', 'additional', 'extra', 'further', 'next', 'another', 'others',
            // Progression
            'next level', 'next step', 'advance', 'progress',
            // Depth
            'deeper', 'expanded', 'detailed', 'advanced',
            // Variation
            'variations', 'alternatives', 'different', 'options',
            // Natural phrases
            'what else', 'what should', 'where do i', 'can you show', 'anything', 'keep going'
        );
        
        $query_lower = strtolower(trim($original_query));
        
        // Check if query contains vague references
        $has_vague = false;
        foreach ($vague_indicators as $indicator) {
            if (strpos($indicator, ' ') !== false) {
                // Multi-word indicator
                if (stripos($query_lower, $indicator) !== false) {
                    $has_vague = true;
                    break;
                }
            } else {
                // Single word - check as whole word
                if (preg_match('/\b' . preg_quote($indicator, '/') . '\b/i', $query_lower)) {
                    $has_vague = true;
                    break;
                }
            }
        }
        
        // Check if query is very conversational (long with many stop words)
        $query_words = preg_split('/\s+/', $query_lower);
        $stop_words = array('i', 'want', 'to', 'learn', 'the', 'a', 'an', 'do', 'you', 'have', 'can', 'show', 'me', 'about', 'on', 'for', 'with');
        $stop_word_count = 0;
        foreach ($query_words as $word) {
            if (in_array($word, $stop_words)) {
                $stop_word_count++;
            }
        }
        $is_very_conversational = count($query_words) > 8 && ($stop_word_count / count($query_words)) > 0.4;
        
        // Use AI if query is vague or very conversational
        return $has_vague || $is_very_conversational;
    }
    
    /**
     * Use AI to extract search intent from query
     * 
     * @param string $query User query
     * @param array $conversation_history Recent conversation history
     * @return array|WP_Error Extracted search intent or error
     */
    private function ai_extract_search_intent($query, $conversation_history = array()) {
        // Check if Katahdin AI Hub is available
        if (!function_exists('katahdin_ai_hub')) {
            return new WP_Error(
                'ai_hub_unavailable',
                'AI service is not available for query preprocessing.',
                array('status' => 503)
            );
        }
        
        $hub = katahdin_ai_hub();
        if (!$hub) {
            return new WP_Error(
                'ai_hub_unavailable',
                'AI service is not available for query preprocessing.',
                array('status' => 503)
            );
        }
        
        // Build prompt for search intent extraction
        $prompt = "Extract the primary search intent from this user query for finding music lessons. ";
        $prompt .= "Focus on identifying the main song title, topic, or lesson subject the user is asking about.\n\n";
        $prompt .= "CRITICAL: Correct any spelling errors in song titles or music terms. ";
        $prompt .= "For example, 'steala bue starlite' should be corrected to 'Stella by Starlight', ";
        $prompt .= "'autumn leves' should be 'Autumn Leaves', 'blues licks' should remain 'blues licks'.\n\n";
        $prompt .= "User Query: " . $query . "\n\n";
        
        // Add conversation context if available
        if (!empty($conversation_history)) {
            $prompt .= "Recent conversation context:\n";
            $context_count = 0;
            foreach (array_reverse($conversation_history) as $msg) {
                if ($context_count >= 2) break; // Only use last 2 messages
                if (!empty($msg['message'])) {
                    $prompt .= "- User: " . $msg['message'] . "\n";
                    $context_count++;
                }
            }
            $prompt .= "\n";
        }
        
        $prompt .= "Return ONLY a valid JSON object with this exact structure:\n";
        $prompt .= "{\n";
        $prompt .= '  "primary_phrase": "the main song title or topic with CORRECTED SPELLING (e.g., "Stella by Starlight" not "steala bue starlite", "Autumn Leaves" not "autumn leves")",' . "\n";
        $prompt .= '  "keywords": ["keyword1", "keyword2"],' . "\n";
        $prompt .= '  "skill_level": "beginner|intermediate|advanced|pro" or null,' . "\n";
        $prompt .= '  "lesson_style": "Blues|Jazz|Gospel|Pop|Classical|Rock|Funk|Latin|Swing|Bebop" or null' . "\n";
        $prompt .= "}\n\n";
        $prompt .= "Important: ALWAYS correct spelling errors in song titles and music terms. ";
        $prompt .= "Extract the actual search terms with proper spelling, not conversational filler. ";
        $prompt .= "If the query says 'another lesson on autumn leaves', return 'autumn leaves' as primary_phrase. ";
        $prompt .= "If the query is a continuation request (like 'more', 'next', 'additional', 'what else', 'any more', 'keep going', 'what's next', 'deeper', 'variations', 'similar', etc.), ";
        $prompt .= "you MUST look at the conversation context to extract the previous search topic and style. ";
        $prompt .= "For example, if the previous conversation was about 'blues licks', return 'licks' as primary_phrase and 'Blues' as lesson_style. ";
        $prompt .= "If the user asks for 'more like this', 'similar', 'variations', or 'alternatives', extract the topic they're referring to from context. ";
        $prompt .= "If the user asks for 'next level', 'harder', 'advanced', or 'deeper', extract the topic AND consider if skill_level should be updated. ";
        $prompt .= "If no clear search intent exists in the current query OR context, return empty strings.";
        
        // Prepare messages for API
        $messages = array(
            array(
                'role' => 'system',
                'content' => 'You are a search query extraction assistant. Extract only the search intent from user queries and return valid JSON.'
            ),
            array(
                'role' => 'user',
                'content' => $prompt
            )
        );
        
        // Prepare request data
        $data = array(
            'messages' => $messages
        );
        
        // Use efficient model for this lightweight task
        $options = array(
            'model' => 'gpt-4o-mini',
            'max_tokens' => 200,
            'temperature' => 0.3 // Lower temperature for more consistent extraction
        );
        
        // Make API call
        $result = $hub->make_api_call(AAA_PLUGIN_ID, 'chat/completions', $data, $options);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Extract response text
        if (!isset($result['choices'][0]['message']['content'])) {
            return new WP_Error(
                'invalid_response',
                'Invalid response from AI service for search intent extraction.',
                array('status' => 500)
            );
        }
        
        $response_text = $result['choices'][0]['message']['content'];
        
        // Try to extract JSON from response (might have markdown code blocks)
        $json_text = $response_text;
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $response_text, $matches)) {
            $json_text = $matches[1];
        } elseif (preg_match('/(\{.*?\})/s', $response_text, $matches)) {
            $json_text = $matches[1];
        }
        
        // Parse JSON
        $extracted = json_decode($json_text, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($extracted)) {
            // If JSON parsing fails, fall back to simple extraction
            return array(
                'primary_phrase' => '',
                'keywords' => array(),
                'skill_level' => null
            );
        }
        
        // Validate and return
        return array(
            'primary_phrase' => isset($extracted['primary_phrase']) ? trim($extracted['primary_phrase']) : '',
            'keywords' => isset($extracted['keywords']) && is_array($extracted['keywords']) ? $extracted['keywords'] : array(),
            'skill_level' => isset($extracted['skill_level']) && !empty($extracted['skill_level']) ? $extracted['skill_level'] : null,
            'lesson_style' => isset($extracted['lesson_style']) && !empty($extracted['lesson_style']) ? trim($extracted['lesson_style']) : null
        );
    }
    
    /**
     * Validate AI response to prevent hallucinated lesson recommendations
     * 
     * @param string $ai_response AI response text
     * @param array $lesson_results Actual lesson results (should be empty)
     * @return string Validated response
     */
    private function validate_no_hallucinated_lessons($ai_response, $lesson_results) {
        // Build list of valid URLs from search results
        $valid_urls = array();
        if (!empty($lesson_results)) {
            foreach ($lesson_results as $result) {
                // Check multiple possible URL fields
                $url = !empty($result['lesson_url']) ? $result['lesson_url'] : 
                       (!empty($result['url']) ? $result['url'] : '');
                if (!empty($url) && strpos($url, 'jazzedge.academy') !== false) {
                    // Normalize URL (remove query params, fragments, trailing slashes for comparison)
                    $normalized = rtrim(preg_replace('/[?#].*$/', '', $url), '/');
                    $valid_urls[] = $normalized;
                    // Also add with trailing slash
                    $valid_urls[] = $normalized . '/';
                }
            }
        }
        
        // Check for markdown links that look like lesson recommendations
        // Pattern: [Lesson Title](url)
        $markdown_link_pattern = '/\[([^\]]+)\]\(([^)]+)\)/';
        $has_lesson_links = preg_match_all($markdown_link_pattern, $ai_response, $matches);
        
        if ($has_lesson_links && count($matches[0]) > 0) {
            // Check if any links point to jazzedge.academy/lesson/ or /collection/
            $invalid_lesson_urls = array();
            foreach ($matches[2] as $url) {
                if (strpos($url, 'jazzedge.academy/lesson/') !== false || 
                    strpos($url, 'jazzedge.academy/collection/') !== false ||
                    strpos($url, 'jazzedge.academy/course/') !== false) {
                    // Normalize URL for comparison
                    $normalized = rtrim(preg_replace('/[?#].*$/', '', $url), '/');
                    $normalized_with_slash = $normalized . '/';
                    
                    // Check if this URL is in our valid URLs list
                    $is_valid = in_array($normalized, $valid_urls) || in_array($normalized_with_slash, $valid_urls);
                    
                    if (!$is_valid) {
                        $invalid_lesson_urls[] = $url;
                    }
                }
            }
            
            // If we found invalid lesson URLs, remove them
            if (!empty($invalid_lesson_urls)) {
                // CRITICAL: AI recommended lessons that don't exist in search results
                // Log this as an error
                if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                    $this->debug_logger->log('ai_hallucination_detected', 'AI recommended lessons not in search results', array(
                        'response_preview' => substr($ai_response, 0, 500),
                        'invalid_urls' => $invalid_lesson_urls,
                        'valid_urls_count' => count($valid_urls),
                        'lesson_results_count' => count($lesson_results)
                    ), get_current_user_id(), null, null);
                }
                
                // Remove invalid lesson links from response - replace with just the title
                foreach ($invalid_lesson_urls as $invalid_url) {
                    // Escape special regex characters in URL
                    $escaped_url = preg_quote($invalid_url, '/');
                    // Replace markdown link with just the title (no link)
                    $ai_response = preg_replace(
                        '/\[([^\]]+)\]\(' . $escaped_url . '\)/',
                        '$1', // Replace with just the title (no link)
                        $ai_response
                    );
                }
                
                return $ai_response; // Return cleaned response
            }
            
            // If we have lesson results but no invalid URLs, all links are valid - return as-is
            if (!empty($lesson_results)) {
                return $ai_response;
            }
            
            // Original logic: No lesson results and AI created lesson links
            // This means AI hallucinated lessons when none were found
            if (empty($lesson_results)) {
                // Remove all lesson recommendation sections
                // Find sections that contain lesson links and replace with warning
                $lines = explode("\n", $ai_response);
                $cleaned_lines = array();
                $in_lesson_section = false;
                $lesson_section_start = -1;
                
                foreach ($lines as $i => $line) {
                    // Check if line contains a lesson link
                    if (preg_match($markdown_link_pattern, $line) && 
                        (strpos($line, 'jazzedge.academy/lesson/') !== false || 
                         strpos($line, 'jazzedge.academy/lessons/') !== false)) {
                        // Start of lesson recommendation section
                        if (!$in_lesson_section) {
                            $in_lesson_section = true;
                            $lesson_section_start = $i;
                            // Add warning message before removing section
                            if ($lesson_section_start > 0) {
                                $cleaned_lines[] = "\n⚠️ I apologize, but I couldn't find any specific lessons matching your request in the database. I can provide general guidance instead.";
                            }
                        }
                        // Skip this line (don't add it to cleaned_lines)
                        continue;
                    }
                    
                    // Check if we're ending a lesson section (empty line or new topic)
                    if ($in_lesson_section) {
                        if (trim($line) === '' || preg_match('/^\d+\./', $line)) {
                            // End of numbered list or section
                            $in_lesson_section = false;
                        } elseif (!preg_match($markdown_link_pattern, $line)) {
                            // Not a link, might be end of section
                            $in_lesson_section = false;
                        }
                    }
                    
                    // Add line if not in lesson section
                    if (!$in_lesson_section) {
                        $cleaned_lines[] = $line;
                    }
                }
                
                $cleaned_response = implode("\n", $cleaned_lines);
                
                // If we removed content, add a note
                if (strlen($cleaned_response) < strlen($ai_response) * 0.7) {
                    $cleaned_response .= "\n\nNote: I couldn't find specific lessons in the database for this topic, but I'm happy to provide general guidance or answer questions.";
                }
                
                return $cleaned_response;
            }
        }
        
        // Check for common hallucinated lesson titles in the response
        $common_hallucinated_titles = array(
            'Blues Piano Basics',
            'Essential Blues Piano Techniques',
            'The 12-Bar Blues Structure',
            'Jazz & Blues Improvisation Techniques',
            'Advanced Blues Concepts',
            'Jazz Piano Basics',
            'Introduction to Swing',
            'Blues Piano Fundamentals',
            'Essential Jazz Piano',
            'Blues Improvisation Basics'
        );
        
        $response_lower = strtolower($ai_response);
        $found_hallucinated = false;
        foreach ($common_hallucinated_titles as $title) {
            if (stripos($ai_response, $title) !== false) {
                $found_hallucinated = true;
                
                // Log this
                if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                    $this->debug_logger->log('ai_hallucination_detected', 'AI mentioned non-existent lesson: ' . $title, array(
                        'hallucinated_title' => $title,
                        'response_preview' => substr($ai_response, 0, 500),
                        'lesson_results_count' => count($lesson_results)
                    ), get_current_user_id(), null, null);
                }
                
                // Replace the hallucinated title with a warning
                $ai_response = str_ireplace($title, '[lesson not found in database]', $ai_response);
            }
        }
        
        return $ai_response;
    }
    
    /**
     * Ensure community mentions are properly linked
     * 
     * @param string $ai_response AI response text
     * @return string Response with community links added
     */
    private function ensure_community_links($ai_response) {
        $community_url = 'https://jazzedge.academy/community';
        
        // Check if response already contains the community link
        if (strpos($ai_response, $community_url) !== false) {
            return $ai_response; // Already linked, no need to process
        }
        
        // Replace "JazzEdge Academy Community" with link (not already linked)
        // Pattern: Match "JazzEdge Academy Community" that is NOT already inside a markdown link
        $ai_response = preg_replace(
            '/(?<!\[)\b(JazzEdge Academy Community)\b(?!\]\()/i',
            '[$1](' . $community_url . ')',
            $ai_response
        );
        
        // Replace "the JazzEdge Academy Community" with link
        $ai_response = preg_replace(
            '/(?<!\[)\b(the JazzEdge Academy Community)\b(?!\]\()/i',
            '[$1](' . $community_url . ')',
            $ai_response
        );
        
        // Replace "JazzEdge Community" with link
        $ai_response = preg_replace(
            '/(?<!\[)\b(JazzEdge Community)\b(?!\]\()/i',
            '[$1](' . $community_url . ')',
            $ai_response
        );
        
        return $ai_response;
    }
    
    /**
     * Auto-link lesson titles that are mentioned but not linked
     * This ensures lessons are always linked even if AI forgets to create markdown links
     * 
     * @param string $ai_response AI response text
     * @param array $lesson_results Array of lesson results with title and url
     * @return string Response with lesson titles auto-linked
     */
    private function auto_link_mentioned_lessons($ai_response, $lesson_results) {
        if (empty($lesson_results)) {
            return $ai_response;
        }
        
        // Build a map of lesson titles to URLs
        $lesson_map = array();
        foreach ($lesson_results as $lesson) {
            $title = !empty($lesson['title']) ? $lesson['title'] : (!empty($lesson['lesson_title']) ? $lesson['lesson_title'] : '');
            $url = !empty($lesson['url']) ? $lesson['url'] : (!empty($lesson['lesson_url']) ? $lesson['lesson_url'] : '');
            
            if (!empty($title) && !empty($url) && strpos($url, 'jazzedge.academy') !== false) {
                // Store both exact title and variations
                $lesson_map[$title] = $url;
                
                // Also store with quotes removed (e.g., "10 Jazz and Blues Licks" -> 10 Jazz and Blues Licks)
                $title_no_quotes = trim($title, '"\'');
                if ($title_no_quotes !== $title) {
                    $lesson_map[$title_no_quotes] = $url;
                }
            }
        }
        
        if (empty($lesson_map)) {
            return $ai_response;
        }
        
        // Sort by title length (longest first) to match more specific titles first
        uksort($lesson_map, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        // For each lesson, check if title is mentioned but not linked
        foreach ($lesson_map as $title => $url) {
            // Escape special regex characters in title
            $escaped_title = preg_quote($title, '/');
            
            // Check if title is already linked (markdown link format)
            $link_pattern = '/\[' . preg_quote($title, '/') . '\]\([^\)]+\)/i';
            if (preg_match($link_pattern, $ai_response)) {
                // Already linked, skip
                continue;
            }
            
            // Check if title is mentioned but not linked
            // Look for title in bold (**title**) or plain text, but not already in a link
            $mention_patterns = array(
                // Bold: **10 Jazz and Blues Licks**
                '/\*\*' . $escaped_title . '\*\*/i',
                // Plain text (word boundaries to avoid partial matches)
                '/\b' . $escaped_title . '\b/i',
            );
            
            foreach ($mention_patterns as $pattern) {
                if (preg_match($pattern, $ai_response)) {
                    // Title is mentioned, create a link
                    // Replace bold mentions first
                    $ai_response = preg_replace(
                        '/\*\*' . $escaped_title . '\*\*/i',
                        '[' . $title . '](' . $url . ')',
                        $ai_response,
                        1 // Only replace first occurrence per title
                    );
                    
                    // If not replaced (wasn't bold), replace plain text
                    if (strpos($ai_response, '[' . $title . '](') === false) {
                        $ai_response = preg_replace(
                            '/\b' . $escaped_title . '\b/i',
                            '[' . $title . '](' . $url . ')',
                            $ai_response,
                            1 // Only replace first occurrence
                        );
                    }
                    
                    // Only process each title once
                    break;
                }
            }
        }
        
        return $ai_response;
    }
    
    /**
     * Add "Add to Favorites" links after lesson links in AI response
     * 
     * @param string $ai_response AI response text
     * @return string Response with favorite links added
     */
    private function add_favorite_links($ai_response) {
        // Pattern to match markdown links to jazzedge.academy lessons/collections
        // Matches: [Lesson Title](https://jazzedge.academy/lesson/slug/) or [Collection Title](https://jazzedge.academy/collection/slug/)
        $pattern = '/\[([^\]]+)\]\((https?:\/\/[^\)]+jazzedge\.academy\/(?:lesson|collection)\/[^\)]+)\)/';
        
        $ai_response = preg_replace_callback($pattern, function($matches) {
            $title = $matches[1];
            $url = $matches[2];
            
            // Determine if it's a lesson or collection
            $is_collection = strpos($url, '/collection/') !== false;
            $category = $is_collection ? 'collection' : 'lesson';
            
            // Create a data attribute for the favorite link
            // We'll use a special format that JavaScript can parse
            $favorite_link = ' <span class="aaa-add-favorite" data-url="' . esc_attr($url) . '" data-title="' . esc_attr($title) . '" data-category="' . esc_attr($category) . '" style="font-size: 0.9em; color: #2563eb; cursor: pointer; text-decoration: underline; margin-left: 8px;">★ Add to Favorites</span>';
            
            return $matches[0] . $favorite_link;
        }, $ai_response);
        
        return $ai_response;
    }
    
    /**
     * Handle adding a lesson/collection to favorites
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function handle_add_favorite($request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('unauthorized', 'You must be logged in to add favorites.', array('status' => 401));
        }
        
        $url = $request->get_param('url');
        $title = $request->get_param('title');
        $description = $request->get_param('description');
        $category = $request->get_param('category'); // Get category from request
        
        if (empty($url) || empty($title)) {
            return new WP_Error('invalid_params', 'URL and title are required.', array('status' => 400));
        }
        
        // Use category from request if provided, otherwise determine from URL
        // NOTE: /course/ links are collections
        if (empty($category) || !in_array($category, array('lesson', 'collection'), true)) {
            // Fallback: determine category from URL
            if (strpos($url, '/collection/') !== false || strpos($url, '/course/') !== false) {
                $category = 'collection';
            } else {
                $category = 'lesson';
            }
        }
        
        // Set resource_type to match category
        $resource_type = $category;
        
        // Check if already favorited
        global $wpdb;
        $favorites_table = $wpdb->prefix . 'jph_lesson_favorites';
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$favorites_table} 
             WHERE user_id = %d AND url = %s",
            $user_id,
            $url
        ));
        
        if ($existing) {
            return new WP_Error('already_favorited', 'This item is already in your favorites.', array('status' => 409));
        }
        
        // Get lesson/collection details from database if possible
        $resource_link = null;
        if (preg_match('/\/(?:lesson|collection)\/([^\/\?]+)/', $url, $url_matches)) {
            $slug = $url_matches[1];
            
            if ($category === 'lesson') {
                $lessons_table = $wpdb->prefix . 'alm_lessons';
                $lesson = $wpdb->get_row($wpdb->prepare(
                    "SELECT ID, post_id, lesson_description 
                     FROM {$lessons_table} 
                     WHERE slug = %s LIMIT 1",
                    $slug
                ));
                
                if ($lesson && empty($description)) {
                    $description = $lesson->lesson_description;
                }
                if ($lesson && $lesson->post_id) {
                    $resource_link = get_permalink($lesson->post_id);
                }
            } else {
                $collections_table = $wpdb->prefix . 'alm_collections';
                // Collections don't have a slug column, so we need to match by post slug
                // First, try to find the post by slug
                $post = get_page_by_path($slug, OBJECT, 'course');
                if ($post) {
                    $collection = $wpdb->get_row($wpdb->prepare(
                        "SELECT ID, post_id, collection_description 
                         FROM {$collections_table} 
                         WHERE post_id = %d LIMIT 1",
                        $post->ID
                    ));
                    
                    if ($collection && empty($description)) {
                        $description = $collection->collection_description;
                    }
                    if ($collection && $collection->post_id) {
                        $resource_link = get_permalink($collection->post_id);
                    }
                }
            }
        }
        
        // Insert into favorites
        $result = $wpdb->insert(
            $favorites_table,
            array(
                'user_id' => $user_id,
                'title' => $title,
                'url' => $url,
                'resource_link' => $resource_link,
                'category' => $category,
                'resource_type' => $resource_type,
                'description' => $description
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to add to favorites.', array('status' => 500));
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Added to favorites successfully.',
            'favorite_id' => $wpdb->insert_id
        ));
    }
    
    /**
     * Handle getting user token usage stats
     */
    public function handle_get_usage($request) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return new WP_Error(
                'unauthorized',
                'User not authenticated.',
                array('status' => 401)
            );
        }
        
        try {
            $usage_stats = $this->token_limits->get_usage_stats($user_id);
            
            // Ensure we always return a valid array structure
            if (!is_array($usage_stats) || empty($usage_stats)) {
                // Return default structure if stats are empty
                $usage_stats = array(
                    'membership_level' => 'free',
                    'daily_limit' => 0,
                    'daily_usage' => 0,
                    'daily_remaining' => 0,
                    'monthly_limit' => 0,
                    'monthly_usage' => 0,
                    'monthly_remaining' => 0,
                    'stats' => array()
                );
            }
            
            return rest_ensure_response($usage_stats);
        } catch (Exception $e) {
            error_log('Academy AI Assistant: Error getting usage stats: ' . $e->getMessage());
            return new WP_Error(
                'usage_error',
                'Failed to retrieve usage statistics.',
                array('status' => 500)
            );
        }
    }
    
    /**
     * Verify session belongs to user
     */
    private function verify_session_ownership($session_id, $user_id) {
        global $wpdb;
        
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}aaa_conversation_sessions WHERE id = %d AND user_id = %d",
            $session_id,
            $user_id
        ));
        
        return !empty($session);
    }
    
    /**
     * Build messages array for AI
     */
    private function build_messages_array($session_id, $current_message, $location, $context_data, $lesson_results, $chip_id = null) {
        $messages = array();
        
        // System message with location prompt (check for custom prompt first)
        $system_prompt = $this->get_location_prompt($location);
        
        // Add chip-specific prompt if this message came from a chip
        if (!empty($chip_id)) {
            $chip_prompt = $this->get_chip_prompt($chip_id);
            if (!empty($chip_prompt)) {
                $system_prompt .= "\n\n## Special Instructions for This Query:\n" . $chip_prompt;
            }
            
            // Get chip suggestions (lessons and collections) for this chip
            // Extract the specific interest/style from the message for improve_skill and show_style chips
            $chip_suggestions = array();
            $interest_id = null;
            
            if ($chip_id === 'improve_skill') {
                // Extract skill from message (e.g., "Help me improve my improvisation" -> "improvisation")
                $interest_id = $this->extract_skill_from_message($current_message);
            } elseif ($chip_id === 'show_style') {
                // Extract style from message (e.g., "Show me Jazz licks" -> "Jazz")
                $interest_id = $this->extract_style_from_message($current_message);
            }
            
            // Look up suggestions using the extracted interest/style ID
            if (!empty($interest_id)) {
                $chip_suggestions = $this->database->get_chip_suggestions($interest_id);
            }
            
            if (!empty($chip_suggestions)) {
                global $wpdb;
                $lessons_table = $wpdb->prefix . 'alm_lessons';
                $collections_table = $wpdb->prefix . 'alm_collections';
                
                // Sort by priority
                usort($chip_suggestions, function($a, $b) {
                    return $a['priority'] <=> $b['priority'];
                });
                
                $suggestions_list = array();
                $is_all_chips = false;
                foreach ($chip_suggestions as $suggestion) {
                    if ($suggestion['type'] === 'lesson') {
                        $lesson = $wpdb->get_row($wpdb->prepare(
                            "SELECT ID, lesson_title, post_id, slug, lesson_description
                             FROM {$lessons_table}
                             WHERE ID = %d AND post_id > 0 AND (status = 'published' OR status IS NULL)",
                            $suggestion['id']
                        ), ARRAY_A);
                        
                        if ($lesson) {
                            $lesson_url = 'https://jazzedge.academy/lesson/' . $lesson['slug'] . '/';
                            $suggestions_list[] = "- **" . $lesson['lesson_title'] . "** - " . $lesson_url;
                        }
                    } elseif ($suggestion['type'] === 'collection') {
                        $collection = $wpdb->get_row($wpdb->prepare(
                            "SELECT ID, collection_title, post_id, collection_description
                             FROM {$collections_table}
                             WHERE ID = %d AND post_id > 0",
                            $suggestion['id']
                        ), ARRAY_A);
                        
                        if ($collection) {
                            $collection_slug = get_post_field('post_name', $collection['post_id']);
                            if (empty($collection_slug)) {
                                $collection_slug = sanitize_title($collection['collection_title']);
                            }
                            $collection_url = 'https://jazzedge.academy/collection/' . $collection_slug . '/';
                            $suggestions_list[] = "- **" . $collection['collection_title'] . "** (Collection) - " . $collection_url;
                        }
                    }
                }
                
                // Check if these suggestions are from "all chips" (chip_id is null in suggestions)
                // We can determine this by checking if chip_id is null in the database query result
                // For now, we'll check if the chip_id passed is 'all' or if we should check the actual suggestion
                // Actually, get_chip_suggestions returns suggestions where chip_id matches OR is null
                // So we need to check if any of these came from "all chips"
                // For simplicity, if chip_id is not provided or is 'all', mark as all chips
                if (empty($chip_id) || $chip_id === 'all') {
                    $is_all_chips = true;
                }
                
                if (!empty($suggestions_list)) {
                    $chip_suggestions_text = "\n\n## Recommended Lessons/Collections for This Query:\n" . implode("\n", $suggestions_list);
                    if ($is_all_chips) {
                        $chip_suggestions_text .= "\n\nWhen responding, prioritize recommending these lessons/collections. For collections, recommend them as 'Jazzedge Practice Curriculum™'.";
                    } else {
                        $chip_suggestions_text .= "\n\nWhen responding, prioritize recommending these lessons/collections.";
                    }
                    $system_prompt .= $chip_suggestions_text;
                }
            }
        }
        
        // Add professional music knowledge requirement (customizable)
        $music_theory_section = $this->get_music_theory_accuracy_section();
        $system_prompt .= "\n\n" . $music_theory_section;
        
        // Add competitor blocking (customizable)
        $competitor_blocking_section = $this->get_competitor_blocking_section();
        $system_prompt .= "\n\n" . $competitor_blocking_section;
        
        // Add community link instructions
        $system_prompt .= "\n\n## Community Link Instructions:\n" .
                         "1. If you mention 'JazzEdge Academy Community', 'Community', 'the Community', or any reference to the JazzEdge Academy community forum, you MUST link it using this exact format: [JazzEdge Academy Community](https://jazzedge.academy/community)\n" .
                         "2. Always use the full URL: https://jazzedge.academy/community\n" .
                         "3. When recommending the community, use the link format: [JazzEdge Academy Community](https://jazzedge.academy/community)";
        
        // Add context to system prompt if available
        if (!empty($context_data)) {
            $context_text = $this->format_context_for_ai($context_data);
            $system_prompt .= "\n\n## User Context:\n" . $context_text;
        }
        
        // Add lesson search results if available
        // Split lesson_results back into embedding and database results for formatting
        $embedding_results = array();
        $lesson_search_results = array();
        foreach ($lesson_results as $result) {
            if (!empty($result['segment_text']) || !empty($result['transcript_id'])) {
                $embedding_results[] = $result;
            } else {
                $lesson_search_results[] = $result;
            }
        }
        
        // Debug: Log what we're checking
        if ($this->debug_logger && $this->debug_logger->is_enabled()) {
            $this->debug_logger->log('lesson_results_check', 'Checking lesson results for AI', array(
                'embedding_results_count' => count($embedding_results),
                'lesson_search_results_count' => count($lesson_search_results),
                'has_embedding' => !empty($embedding_results),
                'has_lesson_search' => !empty($lesson_search_results),
                'total_lesson_results' => count($lesson_results),
                'lesson_results_array' => $lesson_results
            ), get_current_user_id(), $session_id, $location);
        }
        
        // Only show lessons if we actually have valid results with URLs
        $has_valid_lessons = false;
        if (!empty($embedding_results)) {
            foreach ($embedding_results as $result) {
                if (!empty($result['lesson_url']) || !empty($result['timestamp_url'])) {
                    $has_valid_lessons = true;
                    break;
                }
            }
        }
        if (!$has_valid_lessons && !empty($lesson_search_results)) {
            foreach ($lesson_search_results as $result) {
                // Check for URL in multiple possible fields (lesson_url or url)
                $lesson_url = !empty($result['lesson_url']) ? $result['lesson_url'] : (!empty($result['url']) ? $result['url'] : '');
                if (!empty($lesson_url) && strpos($lesson_url, 'jazzedge.academy') !== false) {
                    $has_valid_lessons = true;
                    // Debug: Log that we found valid lessons
                    if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                        $this->debug_logger->log('valid_lessons_found', 'Found valid lessons for AI', array(
                            'lesson_id' => !empty($result['lesson_id']) ? $result['lesson_id'] : 0,
                            'lesson_title' => !empty($result['lesson_title']) ? $result['lesson_title'] : '',
                            'url_field_used' => !empty($result['lesson_url']) ? 'lesson_url' : 'url',
                            'url' => $lesson_url
                        ), get_current_user_id(), $session_id, $location);
                    }
                    break;
                }
            }
        }
        
        // Debug: Log final validation result
        if ($this->debug_logger && $this->debug_logger->is_enabled()) {
            $this->debug_logger->log('has_valid_lessons_check', 'Final check for valid lessons', array(
                'has_valid_lessons' => $has_valid_lessons,
                'embedding_results_count' => count($embedding_results),
                'lesson_search_results_count' => count($lesson_search_results),
                'sample_result_keys' => !empty($lesson_search_results[0]) ? array_keys($lesson_search_results[0]) : array()
            ), get_current_user_id(), $session_id, $location);
        }
        
        if ($has_valid_lessons) {
            $lessons_text = $this->format_lessons_for_ai($embedding_results, $lesson_search_results);
            
            // Double-check: Only proceed if we actually have lesson text with URLs
            if (empty(trim($lessons_text))) {
                $has_valid_lessons = false;
                
                // Debug: Log why lessons text is empty
                if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                    $this->debug_logger->log('lessons_text_empty', 'Lessons text is empty after formatting', array(
                        'embedding_results_count' => count($embedding_results),
                        'lesson_search_results_count' => count($lesson_search_results),
                        'sample_embedding_result' => !empty($embedding_results[0]) ? array_keys($embedding_results[0]) : array(),
                        'sample_search_result' => !empty($lesson_search_results[0]) ? array(
                            'keys' => array_keys($lesson_search_results[0]),
                            'lesson_id' => !empty($lesson_search_results[0]['lesson_id']) ? $lesson_search_results[0]['lesson_id'] : 'missing',
                            'lesson_title' => !empty($lesson_search_results[0]['lesson_title']) ? $lesson_search_results[0]['lesson_title'] : 'missing',
                            'lesson_url' => !empty($lesson_search_results[0]['lesson_url']) ? $lesson_search_results[0]['lesson_url'] : 'missing',
                            'url' => !empty($lesson_search_results[0]['url']) ? $lesson_search_results[0]['url'] : 'missing'
                        ) : array()
                    ), get_current_user_id(), $session_id, $location);
                }
            }
        }
        
        if ($has_valid_lessons) {
            // Debug: Log what we're sending to AI
            if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                    $this->debug_logger->log('lessons_formatted_for_ai', 'Lessons formatted for AI', array(
                        'lessons_text_length' => strlen($lessons_text),
                        'lessons_text_preview' => substr($lessons_text, 0, 500)
                    ), get_current_user_id(), $session_id, $location);
            }
            
            $system_prompt .= "\n\n## Available Lessons Found:\n" . $lessons_text;
            
            // Check if any lessons are keyword-matched (highest priority)
            $has_keyword_matches = false;
            foreach ($lesson_search_results as $result) {
                if (!empty($result['match_type']) && $result['match_type'] === 'keyword_match') {
                    $has_keyword_matches = true;
                    break;
                }
            }
            
            if ($has_keyword_matches) {
                $system_prompt .= "\n\n⚠️ IMPORTANT: Some lessons above are SPECIFICALLY MATCHED to the user's query based on keyword mappings. " .
                                 "These are the MOST RELEVANT lessons and should be prioritized in your response.";
            }
            
            // Add lesson recommendation instructions (customizable)
            $lesson_rec_section = $this->get_lesson_recommendation_section(true);
            $system_prompt .= "\n\n" . $lesson_rec_section;
        } else {
            // CRITICAL: No lessons found - AI must NOT make up lesson recommendations
            $system_prompt .= "\n\n## ⚠️ NO LESSONS FOUND IN DATABASE SEARCH\n";
            $system_prompt .= "The database search returned ZERO results. There are NO lessons available to recommend.\n";
            $lesson_rec_section = $this->get_lesson_recommendation_section(false);
            $system_prompt .= "\n" . $lesson_rec_section;
        }
        
        $messages[] = array(
            'role' => 'system',
            'content' => $system_prompt
        );
        
        // Get conversation history (last 10 messages for context)
        $history = $this->database->get_conversations($session_id, 1, 10);
        
        // Add history messages
        foreach ($history as $conv) {
            $messages[] = array(
                'role' => 'user',
                'content' => $conv['message']
            );
            $messages[] = array(
                'role' => 'assistant',
                'content' => $conv['response']
            );
        }
        
        // Add current message
        $messages[] = array(
            'role' => 'user',
            'content' => $current_message
        );
        
        return $messages;
    }
    
    /**
     * Format context data for AI
     */
    private function format_context_for_ai($context_data) {
        $text = '';
        
        if (!empty($context_data['membership_level'])) {
            $membership_names = array(0 => 'Free', 1 => 'Starter', 2 => 'Essentials', 3 => 'Premier', 4 => 'Studio');
            $level = isset($membership_names[$context_data['membership_level']]) 
                ? $membership_names[$context_data['membership_level']] 
                : 'Free';
            $text .= "Membership Level: {$level}\n";
        }
        
        if (!empty($context_data['practice_data']['stats'])) {
            $stats = $context_data['practice_data']['stats'];
            $text .= "Practice Level: {$stats['current_level']}\n";
            $text .= "Total Practice Sessions: {$stats['total_sessions']}\n";
            $text .= "Total Minutes Practiced: {$stats['total_minutes']}\n";
        }
        
        if (!empty($context_data['practice_data']['current_assignment'])) {
            $assignment = $context_data['practice_data']['current_assignment'];
            $text .= "Current Practice Focus: {$assignment['focus_title']}\n";
        }
        
        if (!empty($context_data['lesson_data']['favorite_lessons'])) {
            $favorites = $context_data['lesson_data']['favorite_lessons'];
            $text .= "Favorite Lessons: " . count($favorites) . " lessons\n";
        }
        
        return $text;
    }
    
    /**
     * Combine embedding search and database search results
     * Removes duplicates and prioritizes embedding results
     * 
     * @param array $embedding_results Results from embedding search
     * @param array $lesson_search_results Results from database search
     * @return array Combined and deduplicated results
     */
    private function combine_lesson_results($embedding_results, $lesson_search_results) {
        $combined = array();
        $seen_lesson_ids = array();
        
        // First, add embedding results (higher priority - they have transcript content)
        foreach ($embedding_results as $result) {
            $lesson_id = !empty($result['lesson_id']) ? absint($result['lesson_id']) : 0;
            if ($lesson_id && !isset($seen_lesson_ids[$lesson_id])) {
                $combined[] = $result;
                $seen_lesson_ids[$lesson_id] = true;
            }
        }
        
        // Then add database search results (if not already included)
        foreach ($lesson_search_results as $result) {
            $lesson_id = !empty($result['lesson_id']) ? absint($result['lesson_id']) : 0;
            if ($lesson_id && !isset($seen_lesson_ids[$lesson_id])) {
                $combined[] = $result;
                $seen_lesson_ids[$lesson_id] = true;
            }
        }
        
        return $combined;
    }
    
    /**
     * Format all lesson results (embedding + database) for AI
     * 
     * @param array $embedding_results Embedding search results
     * @param array $lesson_search_results Database search results
     * @return string Formatted text for AI
     */
    private function format_lessons_for_ai($embedding_results, $lesson_search_results) {
        $text = '';
        $all_lessons = array();
        
        // Debug: Log what we're receiving
        if ($this->debug_logger && $this->debug_logger->is_enabled()) {
            $this->debug_logger->log('format_lessons_start', 'Starting to format lessons for AI', array(
                'embedding_results_count' => count($embedding_results),
                'lesson_search_results_count' => count($lesson_search_results),
                'sample_search_result' => !empty($lesson_search_results[0]) ? array(
                    'lesson_id' => !empty($lesson_search_results[0]['lesson_id']) ? $lesson_search_results[0]['lesson_id'] : 'missing',
                    'lesson_title' => !empty($lesson_search_results[0]['lesson_title']) ? $lesson_search_results[0]['lesson_title'] : 'missing',
                    'lesson_url' => !empty($lesson_search_results[0]['lesson_url']) ? $lesson_search_results[0]['lesson_url'] : 'missing',
                    'url' => !empty($lesson_search_results[0]['url']) ? $lesson_search_results[0]['url'] : 'missing',
                    'all_keys' => array_keys($lesson_search_results[0])
                ) : 'no_results'
            ), get_current_user_id(), null, null);
        }
        
        // Process embedding results (have transcript content)
        foreach ($embedding_results as $result) {
            $lesson_id = !empty($result['lesson_id']) ? absint($result['lesson_id']) : 0;
            $lesson_title = !empty($result['lesson_title']) ? $result['lesson_title'] : "Lesson {$lesson_id}";
            $chapter_title = !empty($result['chapter_title']) ? $result['chapter_title'] : '';
            $chapter_id = !empty($result['chapter_id']) ? absint($result['chapter_id']) : 0;
            $start_time = !empty($result['start_time']) ? floatval($result['start_time']) : 0;
            
            if (!empty($result['lesson_url'])) {
                // Prefer timestamp URL (includes chapter timestamp) if available
                $url = !empty($result['timestamp_url']) ? $result['timestamp_url'] : $result['lesson_url'];
                $url = $this->ensure_correct_domain($url);
                
                if (strpos($url, 'jazzedge.academy') !== false && filter_var($url, FILTER_VALIDATE_URL)) {
                    // Build lesson info with chapter details
                    $lesson_info = array(
                        'title' => $lesson_title,
                        'url' => $url,
                        'content' => !empty($result['segment_text']) ? $result['segment_text'] : '',
                        'type' => 'embedding',
                        'chapter_title' => $chapter_title,
                        'chapter_id' => $chapter_id,
                        'chapter_slug' => !empty($result['chapter_slug']) ? $result['chapter_slug'] : '',
                        'start_time' => $start_time
                    );
                    
                    // If we already have this lesson, prefer the one with chapter info or earlier timestamp
                    if (isset($all_lessons[$lesson_id])) {
                        // Keep existing if it has chapter info and new one doesn't
                        if (!empty($all_lessons[$lesson_id]['chapter_title']) && empty($chapter_title)) {
                            continue;
                        }
                        // Prefer timestamp URL if available
                        if (!empty($result['timestamp_url']) && empty($all_lessons[$lesson_id]['start_time'])) {
                            $all_lessons[$lesson_id] = $lesson_info;
                        }
                    } else {
                        $all_lessons[$lesson_id] = $lesson_info;
                    }
                }
            }
        }
        
        // Process database search results (direct title matches)
        foreach ($lesson_search_results as $result) {
            $lesson_id = !empty($result['lesson_id']) ? absint($result['lesson_id']) : 0;
            $lesson_title = !empty($result['lesson_title']) ? $result['lesson_title'] : "Lesson {$lesson_id}";
            
            // Check for URL in multiple possible fields
            $lesson_url = !empty($result['lesson_url']) ? $result['lesson_url'] : (!empty($result['url']) ? $result['url'] : '');
            
            if (!empty($lesson_url) && strpos($lesson_url, 'jazzedge.academy') !== false) {
                // Add or update (database search might have better title match)
                if (!isset($all_lessons[$lesson_id])) {
                    $all_lessons[$lesson_id] = array(
                        'title' => $lesson_title,
                        'url' => $lesson_url,
                        'content' => !empty($result['lesson_description']) ? $result['lesson_description'] : '',
                        'type' => 'database',
                        'match_type' => !empty($result['match_type']) ? $result['match_type'] : 'database_search'
                    );
                } else {
                    // Update URL if database search has a valid one and embedding didn't
                    if (empty($all_lessons[$lesson_id]['url']) || !filter_var($all_lessons[$lesson_id]['url'], FILTER_VALIDATE_URL)) {
                        $all_lessons[$lesson_id]['url'] = $lesson_url;
                    }
                }
            } else {
                // Debug: Log lessons without valid URLs
                if ($this->debug_logger && $this->debug_logger->is_enabled()) {
                    $this->debug_logger->log('lesson_missing_url', 'Lesson found but missing valid URL', array(
                        'lesson_id' => $lesson_id,
                        'lesson_title' => $lesson_title,
                        'result_keys' => array_keys($result),
                        'lesson_url' => $lesson_url
                    ), get_current_user_id(), $session_id, $location);
                }
            }
        }
        
        // Debug: Log how many lessons we have after processing
        if ($this->debug_logger && $this->debug_logger->is_enabled()) {
            $this->debug_logger->log('format_lessons_after_processing', 'Lessons processed, ready to format', array(
                'all_lessons_count' => count($all_lessons),
                'all_lessons_ids' => array_keys($all_lessons),
                'sample_lesson' => !empty($all_lessons) ? array(
                    'first_lesson_id' => array_key_first($all_lessons),
                    'first_lesson_data' => $all_lessons[array_key_first($all_lessons)]
                ) : 'no_lessons'
            ), get_current_user_id(), null, null);
        }
        
        // Format for AI (prioritize keyword matches)
        $keyword_matches = array();
        $regular_matches = array();
        
        foreach ($all_lessons as $lesson_id => $lesson) {
            // Check if this is a keyword match
            $is_keyword_match = false;
            foreach ($lesson_search_results as $result) {
                if ($result['lesson_id'] == $lesson_id && !empty($result['match_type']) && $result['match_type'] === 'keyword_match') {
                    $is_keyword_match = true;
                    $lesson['keyword'] = !empty($result['keyword']) ? $result['keyword'] : '';
                    break;
                }
            }
            
            // Check if this is an embedding match (has transcript content)
            $is_embedding_match = ($lesson['type'] === 'embedding');
            
            if ($is_keyword_match) {
                $keyword_matches[] = $lesson;
            } elseif ($is_embedding_match) {
                // Embedding matches are handled separately below
                // Don't add to regular_matches to avoid duplication
            } else {
                $regular_matches[] = $lesson;
            }
        }
        
        // Show keyword matches first (highest priority)
        if (!empty($keyword_matches)) {
            $text .= "### ⭐ SPECIFICALLY RECOMMENDED LESSONS (Keyword Matched):\n\n";
            foreach ($keyword_matches as $lesson) {
                $text .= "- **{$lesson['title']}**";
                if (!empty($lesson['keyword'])) {
                    $text .= " (matched for: '{$lesson['keyword']}')";
                }
                $text .= "\n";
                
                // Add chapter information if available
                if (!empty($lesson['chapter_title'])) {
                    $text .= "  Chapter: **{$lesson['chapter_title']}**";
                    if (!empty($lesson['start_time']) && $lesson['start_time'] > 0) {
                        $minutes = floor($lesson['start_time'] / 60);
                        $seconds = floor($lesson['start_time'] % 60);
                        $text .= " (video content starts at " . sprintf('%d:%02d', $minutes, $seconds) . ")";
                    }
                    $text .= "\n";
                }
                
                $text .= "  EXACT URL TO USE: {$lesson['url']}\n";
                if (!empty($lesson['content'])) {
                    $text .= "  Content: " . wp_trim_words($lesson['content'], 20) . "\n";
                }
                $text .= "\n";
            }
        }
        
        // Then show embedding matches (which may have chapters)
        $embedding_matches = array();
        foreach ($all_lessons as $lesson_id => $lesson) {
            if ($lesson['type'] === 'embedding') {
                $embedding_matches[] = $lesson;
            }
        }
        
        if (!empty($embedding_matches)) {
            if (!empty($keyword_matches)) {
                $text .= "\n### 📚 Lessons Found in Transcripts:\n\n";
            } else {
                $text .= "### 📚 Lessons Found in Transcripts:\n\n";
            }
            foreach ($embedding_matches as $lesson) {
                $text .= "- **{$lesson['title']}**\n";
                
                // Add chapter information prominently
                if (!empty($lesson['chapter_title'])) {
                    $text .= "  📖 **Chapter: {$lesson['chapter_title']}**";
                    if (!empty($lesson['start_time']) && $lesson['start_time'] > 0) {
                        $minutes = floor($lesson['start_time'] / 60);
                        $seconds = floor($lesson['start_time'] % 60);
                        $text .= " (relevant content starts at " . sprintf('%d:%02d', $minutes, $seconds) . ")";
                    }
                    $text .= "\n";
                    $text .= "  ⚠️ IMPORTANT: The URL below links directly to this chapter. In your response, mention the chapter name and the timestamp (e.g., 'at 54 seconds' or 'at 0:54').\n";
                }
                
                $text .= "  EXACT URL TO USE: {$lesson['url']}\n";
                if (!empty($lesson['content'])) {
                    $text .= "  Relevant content: " . wp_trim_words($lesson['content'], 25) . "\n";
                }
                $text .= "\n";
            }
        }
        
        // Then show regular database matches
        if (!empty($regular_matches)) {
            if (!empty($keyword_matches) || !empty($embedding_matches)) {
                $text .= "\n### Other Relevant Lessons:\n\n";
            } else {
                // If no keyword or embedding matches, this is the main section
                $text .= "### 📚 Available Lessons Found:\n\n";
            }
            foreach ($regular_matches as $lesson) {
                $text .= "- **{$lesson['title']}**\n";
                $text .= "  ⚠️ CRITICAL: You MUST create a markdown link for this lesson using: [{$lesson['title']}]({$lesson['url']})\n";
                $text .= "  EXACT URL TO USE: {$lesson['url']}\n";
                if (!empty($lesson['content'])) {
                    $text .= "  Content: " . wp_trim_words($lesson['content'], 20) . "\n";
                }
                $text .= "\n";
            }
        }
        
        // Debug: Log final formatted text
        if ($this->debug_logger && $this->debug_logger->is_enabled()) {
            $this->debug_logger->log('format_lessons_complete', 'Finished formatting lessons for AI', array(
                'text_length' => strlen($text),
                'text_preview' => substr($text, 0, 500),
                'keyword_matches_count' => count($keyword_matches),
                'embedding_matches_count' => count($embedding_matches),
                'regular_matches_count' => count($regular_matches),
                'all_lessons_count' => count($all_lessons)
            ), get_current_user_id(), null, null);
        }
        
        return $text;
    }
    
    /**
     * Format embedding results for AI (legacy method - kept for compatibility)
     * Includes lesson URLs so AI can link to lessons in responses
     * IMPORTANT: Only includes URLs from jazzedge.academy domain
     */
    private function format_embeddings_for_ai($embedding_results) {
        $text = '';
        
        foreach ($embedding_results as $result) {
            $lesson_title = !empty($result['lesson_title']) ? $result['lesson_title'] : "Lesson {$result['lesson_id']}";
            
            // Include URL if available (AI can use this to create links)
            // CRITICAL: Only use URLs from jazzedge.academy domain
            if (!empty($result['lesson_url'])) {
                $url = !empty($result['timestamp_url']) ? $result['timestamp_url'] : $result['lesson_url'];
                
                // Validate and fix domain
                $url = $this->ensure_correct_domain($url);
                
                // Only include if it's a valid jazzedge.academy URL
                if (strpos($url, 'jazzedge.academy') !== false && filter_var($url, FILTER_VALIDATE_URL)) {
                    // Format: Put URL right after title so AI sees it immediately
                    // Use format: [Title](URL) so AI knows this is the link format to use
                    $text .= "- **{$lesson_title}** - USE THIS EXACT URL: {$url}\n";
                    $text .= "  Content excerpt: {$result['segment_text']}\n";
                } else {
                    // Skip invalid URLs - don't pass wrong domains to AI
                    error_log('Academy AI Assistant: Invalid lesson URL detected: ' . $url);
                    // Still include the lesson but without URL
                    $text .= "- {$lesson_title}: {$result['segment_text']}\n";
                    $text .= "  (Note: URL not available for this lesson)\n";
                }
            } else {
                // No URL available
                $text .= "- {$lesson_title}: {$result['segment_text']}\n";
                $text .= "  (Note: URL not available for this lesson)\n";
            }
            
            if (!empty($result['chapter_title'])) {
                $text .= "  Chapter: {$result['chapter_title']}\n";
            }
        }
        
        return $text;
    }
    
    /**
     * Ensure URL uses correct jazzedge.academy domain
     * 
     * @param string $url URL to validate/fix
     * @return string URL with correct domain
     */
    private function ensure_correct_domain($url) {
        if (empty($url)) {
            return '';
        }
        
        // Replace any old domains with jazzedge.academy
        $url = str_replace(
            array(
                'www.pianovideolessons.com',
                'pianovideolessons.com',
                'http://pianovideolessons.com',
                'https://pianovideolessons.com',
                'http://www.pianovideolessons.com',
                'https://www.pianovideolessons.com'
            ),
            'jazzedge.academy',
            $url
        );
        
        // If URL doesn't have a domain, add jazzedge.academy
        if (strpos($url, 'http') !== 0 && strpos($url, '/') === 0) {
            $url = 'https://jazzedge.academy' . $url;
        } elseif (strpos($url, 'http') !== 0) {
            $url = 'https://jazzedge.academy/' . ltrim($url, '/');
        }
        
        return $url;
    }
    
    /**
     * Get AI response from Katahdin AI Hub
     */
    private function get_ai_response($location, $messages) {
        // Check if Katahdin AI Hub is available
        if (!function_exists('katahdin_ai_hub')) {
            return new WP_Error(
                'ai_hub_unavailable',
                'AI service is not available. Please contact support.',
                array('status' => 503)
            );
        }
        
        $hub = katahdin_ai_hub();
        if (!$hub) {
            return new WP_Error(
                'ai_hub_unavailable',
                'AI service is not available. Please contact support.',
                array('status' => 503)
            );
        }
        
        // Prepare request data
        $data = array(
            'messages' => $messages
        );
        
        // Default AI settings for Jazzedge AI
        $options = array(
            'model' => 'gpt-4o-mini', // Use efficient model
            'max_tokens' => 2000,
            'temperature' => 0.7
        );
        
        // Make API call
        $result = $hub->make_api_call(AAA_PLUGIN_ID, 'chat/completions', $data, $options);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Extract response text
        if (isset($result['choices'][0]['message']['content'])) {
            $content = $result['choices'][0]['message']['content'];
            
            // Extract token usage if available
            $tokens_used = 0;
            if (isset($result['usage']['total_tokens'])) {
                $tokens_used = (int) $result['usage']['total_tokens'];
            } elseif (isset($result['usage']['prompt_tokens']) && isset($result['usage']['completion_tokens'])) {
                $tokens_used = (int) $result['usage']['prompt_tokens'] + (int) $result['usage']['completion_tokens'];
            }
            
            // Debug: Log what we're getting from AI Hub
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Academy AI Assistant: AI Hub response - has usage: ' . (isset($result['usage']) ? 'yes' : 'no'));
                if (isset($result['usage'])) {
                    error_log('Academy AI Assistant: AI Hub usage data: ' . json_encode($result['usage']));
                }
                error_log('Academy AI Assistant: AI Hub extracted tokens_used: ' . $tokens_used);
            }
            
            // Return both content and token usage
            return array(
                'content' => $content,
                'tokens_used' => $tokens_used
            );
        }
        
        return new WP_Error(
            'invalid_response',
            'Invalid response from AI service.',
            array('status' => 500)
        );
    }
    
    /**
     * Save conversation to database
     */
    private function save_conversation($user_id, $session_id, $location, $message, $response, $context_data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'aaa_conversations',
            array(
                'user_id' => $user_id,
                'session_id' => $session_id,
                'location' => $location,
                'message' => $message,
                'response' => $response,
                'context_data' => !empty($context_data) ? json_encode($context_data) : null
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            error_log('Academy AI Assistant: Failed to save conversation. Error: ' . $wpdb->last_error);
            error_log('Academy AI Assistant: Last query: ' . $wpdb->last_query);
            error_log('Academy AI Assistant: User ID: ' . $user_id . ', Session ID: ' . $session_id);
            return 0;
        }
        
        $conversation_id = $wpdb->insert_id;
        
        // CRITICAL: Update session timestamp so it shows as most recent
        // This ensures the session appears in the correct order and date is updated
        // Use UTC time to match database's ON UPDATE CURRENT_TIMESTAMP behavior
        // current_time('mysql', true) returns UTC time
        $update_result = $wpdb->update(
            $wpdb->prefix . 'aaa_conversation_sessions',
            array('updated_at' => current_time('mysql', true)), // true = UTC time
            array('id' => $session_id),
            array('%s'),
            array('%d')
        );
        
        if ($wpdb->last_error) {
            error_log('Academy AI Assistant: Failed to update session timestamp. Error: ' . $wpdb->last_error);
            error_log('Academy AI Assistant: Last query: ' . $wpdb->last_query);
        } else {
            // Log success for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $updated_time = current_time('mysql', true);
                error_log('Academy AI Assistant: Successfully saved conversation ID: ' . $conversation_id . ' and updated session ID: ' . $session_id . ' to: ' . $updated_time);
            }
        }
        
        return $conversation_id;
    }
    
    /**
     * Check if user has admin permission (for data migration endpoints)
     * Requires manage_options capability
     */
    public function check_admin_permission($request = null) {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'insufficient_permissions',
                'Administrator access required.',
                array('status' => 403)
            );
        }
        return true;
    }
    
    /**
     * Export embeddings data in batches
     * GET /wp-json/academy-ai-assistant/v1/embeddings/export?batch_size=100&offset=0
     */
    public function handle_export_embeddings($request) {
        global $wpdb;
        
        $embeddings_table = $wpdb->prefix . 'alm_transcript_embeddings';
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $embeddings_table
        ));
        
        if (!$table_exists) {
            return new WP_Error(
                'table_not_found',
                'Embeddings table does not exist.',
                array('status' => 404)
            );
        }
        
        $batch_size = absint($request->get_param('batch_size'));
        $offset = absint($request->get_param('offset'));
        $transcript_id = absint($request->get_param('transcript_id'));
        
        // Build query
        $where_clause = '';
        if ($transcript_id > 0) {
            $where_clause = $wpdb->prepare(" WHERE transcript_id = %d", $transcript_id);
        }
        
        // Get total count
        $total_count = $wpdb->get_var("SELECT COUNT(*) FROM {$embeddings_table}" . $where_clause);
        
        // Get batch of embeddings
        $query = "SELECT 
                    transcript_id,
                    segment_index,
                    embedding,
                    segment_text,
                    start_time,
                    end_time,
                    created_at
                  FROM {$embeddings_table}
                  {$where_clause}
                  ORDER BY transcript_id, segment_index
                  LIMIT %d OFFSET %d";
        
        $embeddings = $wpdb->get_results($wpdb->prepare($query, $batch_size, $offset), ARRAY_A);
        
        if ($wpdb->last_error) {
            return new WP_Error(
                'database_error',
                'Database error: ' . $wpdb->last_error,
                array('status' => 500)
            );
        }
        
        // Calculate if there are more batches
        $has_more = ($offset + count($embeddings)) < $total_count;
        
        return rest_ensure_response(array(
            'success' => true,
            'data' => $embeddings,
            'pagination' => array(
                'total' => (int) $total_count,
                'offset' => $offset,
                'batch_size' => $batch_size,
                'returned' => count($embeddings),
                'has_more' => $has_more
            ),
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Import embeddings data
     * POST /wp-json/academy-ai-assistant/v1/embeddings/import
     * Body: { "embeddings": [...], "overwrite": false }
     */
    public function handle_import_embeddings($request) {
        global $wpdb;
        
        // Use explicit table name to ensure we're updating wp_alm_transcript_embeddings
        // $wpdb->prefix should be 'wp_' on WPEngine, but we'll verify
        $embeddings_table = $wpdb->prefix . 'alm_transcript_embeddings';
        
        // Log table name for debugging
        error_log('Academy AI Assistant: Import embeddings - Using table: ' . $embeddings_table . ' (prefix: ' . $wpdb->prefix . ')');
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $embeddings_table
        ));
        
        if (!$table_exists) {
            // Try to find similar tables for debugging
            $all_tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
            $similar_tables = array();
            foreach ($all_tables as $table) {
                $table_name = $table[0];
                if (strpos($table_name, 'transcript_embeddings') !== false) {
                    $similar_tables[] = $table_name;
                }
            }
            
            $error_msg = 'Embeddings table does not exist. Expected: ' . $embeddings_table;
            if (!empty($similar_tables)) {
                $error_msg .= ' Found similar: ' . implode(', ', $similar_tables);
            }
            
            return new WP_Error(
                'table_not_found',
                $error_msg,
                array(
                    'status' => 404,
                    'expected_table' => $embeddings_table,
                    'table_prefix' => $wpdb->prefix,
                    'similar_tables' => $similar_tables
                )
            );
        }
        
        $embeddings = $request->get_param('embeddings');
        $overwrite = $request->get_param('overwrite');
        
        if (!is_array($embeddings) || empty($embeddings)) {
            return new WP_Error(
                'invalid_data',
                'Embeddings data must be a non-empty array.',
                array('status' => 400)
            );
        }
        
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errors = array();
        
        // Start transaction for better performance
        $wpdb->query('START TRANSACTION');
        
        foreach ($embeddings as $index => $embedding) {
            // Validate required fields
            if (!isset($embedding['transcript_id']) || !isset($embedding['segment_index'])) {
                $errors[] = "Row {$index}: Missing transcript_id or segment_index";
                $skipped++;
                continue;
            }
            
            $transcript_id = absint($embedding['transcript_id']);
            $segment_index = absint($embedding['segment_index']);
            $embedding_data = isset($embedding['embedding']) ? $embedding['embedding'] : '';
            $segment_text = isset($embedding['segment_text']) ? $embedding['segment_text'] : null;
            $start_time = isset($embedding['start_time']) ? floatval($embedding['start_time']) : null;
            $end_time = isset($embedding['end_time']) ? floatval($embedding['end_time']) : null;
            $created_at = isset($embedding['created_at']) ? $embedding['created_at'] : current_time('mysql');
            
            // Check if record exists
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$embeddings_table} 
                 WHERE transcript_id = %d AND segment_index = %d",
                $transcript_id,
                $segment_index
            ));
            
            if ($exists > 0) {
                if ($overwrite) {
                    // Update existing record
                    $result = $wpdb->update(
                        $embeddings_table,
                        array(
                            'embedding' => $embedding_data,
                            'segment_text' => $segment_text,
                            'start_time' => $start_time,
                            'end_time' => $end_time,
                            'created_at' => $created_at
                        ),
                        array(
                            'transcript_id' => $transcript_id,
                            'segment_index' => $segment_index
                        ),
                        array('%s', '%s', '%f', '%f', '%s'),
                        array('%d', '%d')
                    );
                    
                    if ($result !== false) {
                        $updated++;
                    } else {
                        $errors[] = "Row {$index}: Update failed - " . $wpdb->last_error;
                        $skipped++;
                    }
                } else {
                    $skipped++;
                }
            } else {
                // Insert new record
                $result = $wpdb->insert(
                    $embeddings_table,
                    array(
                        'transcript_id' => $transcript_id,
                        'segment_index' => $segment_index,
                        'embedding' => $embedding_data,
                        'segment_text' => $segment_text,
                        'start_time' => $start_time,
                        'end_time' => $end_time,
                        'created_at' => $created_at
                    ),
                    array('%d', '%d', '%s', '%s', '%f', '%f', '%s')
                );
                
                if ($result !== false) {
                    $inserted++;
                } else {
                    $errors[] = "Row {$index}: Insert failed - " . $wpdb->last_error;
                    $skipped++;
                }
            }
        }
        
        // Commit transaction
        if (empty($errors) || count($errors) < count($embeddings)) {
            $wpdb->query('COMMIT');
        } else {
            $wpdb->query('ROLLBACK');
            return new WP_Error(
                'import_failed',
                'Import failed. All records rolled back.',
                array(
                    'status' => 500,
                    'errors' => $errors
                )
            );
        }
        
        // Verify final count in table to confirm we're writing to the right place
        $final_count = $wpdb->get_var("SELECT COUNT(*) FROM {$embeddings_table}");
        
        return rest_ensure_response(array(
            'success' => true,
            'summary' => array(
                'total' => count($embeddings),
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => count($errors)
            ),
            'errors' => $errors,
            'table_used' => $embeddings_table,
            'table_count_after' => (int) $final_count,
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Get total count of embeddings
     * GET /wp-json/academy-ai-assistant/v1/embeddings/count
     */
    public function handle_get_embeddings_count($request) {
        global $wpdb;
        
        $embeddings_table = $wpdb->prefix . 'alm_transcript_embeddings';
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $embeddings_table
        ));
        
        if (!$table_exists) {
            return new WP_Error(
                'table_not_found',
                'Embeddings table does not exist.',
                array('status' => 404)
            );
        }
        
        $total_count = $wpdb->get_var("SELECT COUNT(*) FROM {$embeddings_table}");
        
        // Get count by transcript_id for more detailed info
        $transcript_counts = $wpdb->get_results(
            "SELECT transcript_id, COUNT(*) as segment_count 
             FROM {$embeddings_table} 
             GROUP BY transcript_id 
             ORDER BY transcript_id",
            ARRAY_A
        );
        
        return rest_ensure_response(array(
            'success' => true,
            'total_count' => (int) $total_count,
            'unique_transcripts' => count($transcript_counts),
            'transcript_counts' => $transcript_counts,
            'timestamp' => current_time('mysql')
        ));
    }
}
