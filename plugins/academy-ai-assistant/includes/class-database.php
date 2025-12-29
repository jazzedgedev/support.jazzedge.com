<?php
/**
 * Database Class for Academy AI Assistant
 * 
 * Handles all database operations with security and isolation
 */

if (!defined('ABSPATH')) {
    exit;
}

class AAA_Database {
    
    private $conversations_table;
    private $sessions_table;
    private $debug_logs_table;
    private $keyword_lessons_table;
    private $chip_suggestions_table;
    private $token_usage_table;
    
    public function __construct() {
        global $wpdb;
        $this->conversations_table = $wpdb->prefix . 'aaa_conversations';
        $this->sessions_table = $wpdb->prefix . 'aaa_conversation_sessions';
        $this->debug_logs_table = $wpdb->prefix . 'aaa_debug_logs';
        $this->keyword_lessons_table = $wpdb->prefix . 'aaa_keyword_lessons';
        $this->chip_suggestions_table = $wpdb->prefix . 'aaa_chip_suggestions';
        $this->token_usage_table = $wpdb->prefix . 'aaa_token_usage';
        
        // Run migrations on init (for existing installations)
        add_action('init', array($this, 'maybe_add_session_name_column'), 20);
        add_action('init', array($this, 'maybe_rename_personality_to_location'), 20);
    }
    
    /**
     * Create database tables
     * Security: Uses dbDelta() with proper sanitization
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Conversations table
        $conversations_sql = "CREATE TABLE {$this->conversations_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            session_id bigint(20) NOT NULL,
            location varchar(50) NOT NULL,
            message text NOT NULL,
            response text NOT NULL,
            context_data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY session_id (session_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $result1 = dbDelta($conversations_sql);
        
        // Log if there were issues
        if (!empty($wpdb->last_error)) {
            error_log('Academy AI Assistant: Error creating conversations table: ' . $wpdb->last_error);
        }
        
        // Conversation sessions table
        $sessions_sql = "CREATE TABLE {$this->sessions_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            location varchar(50) DEFAULT 'main',
            session_name varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY updated_at (updated_at)
        ) $charset_collate;";
        
        $result2 = dbDelta($sessions_sql);
        
        // Log if there were issues
        if (!empty($wpdb->last_error)) {
            error_log('Academy AI Assistant: Error creating sessions table: ' . $wpdb->last_error);
        }
        
        // Migration: Add session_name column if it doesn't exist (for existing installations)
        $this->maybe_add_session_name_column();
        
        // Migration: Rename personality to location (for existing installations)
        $this->maybe_rename_personality_to_location();
        
        // Debug logs table
        $debug_logs_sql = "CREATE TABLE {$this->debug_logs_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            session_id bigint(20),
            location varchar(50),
            log_type varchar(50) NOT NULL,
            message text NOT NULL,
            data longtext,
            response_time int(11),
            tokens_used int(11),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY session_id (session_id),
            KEY log_type (log_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $result3 = dbDelta($debug_logs_sql);
        
        // Log if there were issues
        if (!empty($wpdb->last_error)) {
            error_log('Academy AI Assistant: Error creating debug_logs table: ' . $wpdb->last_error);
        }
        
        // Keyword-to-lesson mappings table
        $keyword_lessons_sql = "CREATE TABLE {$this->keyword_lessons_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            keyword varchar(255) NOT NULL,
            lesson_id bigint(20) NOT NULL,
            priority int(11) DEFAULT 10,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY keyword_lesson (keyword(100), lesson_id),
            KEY keyword (keyword(100)),
            KEY lesson_id (lesson_id),
            KEY priority (priority)
        ) $charset_collate;";
        
        $result4 = dbDelta($keyword_lessons_sql);
        
        // Log if there were issues
        if (!empty($wpdb->last_error)) {
            error_log('Academy AI Assistant: Error creating keyword_lessons table: ' . $wpdb->last_error);
        }
        
        // Chip suggestions table (lessons and collections assigned to chips)
        $chip_suggestions_sql = "CREATE TABLE {$this->chip_suggestions_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            chip_id varchar(100) DEFAULT NULL,
            suggestion_type varchar(20) NOT NULL,
            suggestion_id bigint(20) NOT NULL,
            priority int(11) DEFAULT 10,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY chip_suggestion (chip_id(50), suggestion_type(20), suggestion_id),
            KEY chip_id (chip_id(50)),
            KEY suggestion_type (suggestion_type(20)),
            KEY suggestion_id (suggestion_id),
            KEY priority (priority)
        ) $charset_collate;";
        
        $result5 = dbDelta($chip_suggestions_sql);
        
        // Log if there were issues
        if (!empty($wpdb->last_error)) {
            error_log('Academy AI Assistant: Error creating chip_suggestions table: ' . $wpdb->last_error);
        }
        
        // Token usage tracking table
        $token_usage_sql = "CREATE TABLE {$this->token_usage_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            membership_level varchar(50) DEFAULT 'free',
            tokens_used int(11) NOT NULL DEFAULT 0,
            usage_date date NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_date (user_id, usage_date),
            KEY user_id (user_id),
            KEY usage_date (usage_date),
            KEY membership_level (membership_level)
        ) $charset_collate;";
        
        $result6 = dbDelta($token_usage_sql);
        
        // Log if there were issues
        if (!empty($wpdb->last_error)) {
            error_log('Academy AI Assistant: Error creating token_usage table: ' . $wpdb->last_error);
        }
        
        // Verify tables were created
        $tables_created = array();
        $tables_created['conversations'] = $wpdb->get_var("SHOW TABLES LIKE '{$this->conversations_table}'") == $this->conversations_table;
        $tables_created['sessions'] = $wpdb->get_var("SHOW TABLES LIKE '{$this->sessions_table}'") == $this->sessions_table;
        $tables_created['debug_logs'] = $wpdb->get_var("SHOW TABLES LIKE '{$this->debug_logs_table}'") == $this->debug_logs_table;
        $tables_created['keyword_lessons'] = $wpdb->get_var("SHOW TABLES LIKE '{$this->keyword_lessons_table}'") == $this->keyword_lessons_table;
        $tables_created['chip_suggestions'] = $wpdb->get_var("SHOW TABLES LIKE '{$this->chip_suggestions_table}'") == $this->chip_suggestions_table;
        $tables_created['token_usage'] = $wpdb->get_var("SHOW TABLES LIKE '{$this->token_usage_table}'") == $this->token_usage_table;
        
        // Log results
        foreach ($tables_created as $table_name => $created) {
            if (!$created) {
                error_log("Academy AI Assistant: Table {$table_name} was not created successfully");
            }
        }
        
        return $tables_created;
    }
    
    /**
     * Get or create conversation session for user
     * Security: Validates user_id, uses prepared statements
     */
    public function get_or_create_session($user_id) {
        global $wpdb;
        
        // Security: Validate user_id
        $user_id = absint($user_id);
        if (!$user_id || !get_userdata($user_id)) {
            return false;
        }
        
        // Get existing active session (last 24 hours)
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->sessions_table} 
             WHERE user_id = %d 
             AND updated_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
             ORDER BY updated_at DESC 
             LIMIT 1",
            $user_id
        ));
        
        if ($session) {
            return $session;
        }
        
        // Create new session
        $wpdb->insert(
            $this->sessions_table,
            array(
                'user_id' => $user_id,
                'location' => 'main'
            ),
            array('%d', '%s')
        );
        
        if ($wpdb->insert_id) {
            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$this->sessions_table} WHERE id = %d",
                $wpdb->insert_id
            ));
        }
        
        return false;
    }
    
    /**
     * Save conversation message
     * Security: Sanitizes all input, validates user_id
     */
    public function save_conversation($user_id, $session_id, $location, $message, $response, $context_data = null) {
        global $wpdb;
        
        // Security: Validate and sanitize
        $user_id = absint($user_id);
        $session_id = absint($session_id);
        $location = sanitize_text_field($location);
        $message = sanitize_textarea_field($message);
        $response = wp_kses_post($response); // Allow HTML in AI response but sanitize
        
        if (!$user_id || !$session_id || empty($message) || empty($response)) {
            return false;
        }
        
        // Security: Verify user owns the session
        $session = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->sessions_table} WHERE id = %d AND user_id = %d",
            $session_id,
            $user_id
        ));
        
        if (!$session) {
            return false;
        }
        
        // Prepare context data
        $context_json = null;
        if ($context_data) {
            $context_json = wp_json_encode($context_data);
        }
        
        $result = $wpdb->insert(
            $this->conversations_table,
            array(
                'user_id' => $user_id,
                'session_id' => $session_id,
                'location' => $location,
                'message' => $message,
                'response' => $response,
                'context_data' => $context_json
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s')
        );
        
        // Update session timestamp
        // Use UTC time to match MySQL's ON UPDATE CURRENT_TIMESTAMP behavior
        // current_time('mysql', true) returns UTC time
        $wpdb->update(
            $this->sessions_table,
            array('updated_at' => current_time('mysql', true)), // true = UTC
            array('id' => $session_id),
            array('%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get conversation history for user
     * Security: Only returns conversations for specified user, with pagination
     * Performance: Limits results, uses indexes
     */
    public function get_conversation_history($user_id, $session_id = null, $limit = 50, $offset = 0) {
        global $wpdb;
        
        // Security: Validate user_id
        $user_id = absint($user_id);
        if (!$user_id) {
            return array();
        }
        
        // Performance: Limit results
        $limit = min(absint($limit), 100); // Max 100
        $offset = absint($offset);
        
        $where = $wpdb->prepare("user_id = %d", $user_id);
        
        if ($session_id) {
            $session_id = absint($session_id);
            $where .= $wpdb->prepare(" AND session_id = %d", $session_id);
        }
        
        $conversations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->conversations_table} 
             WHERE {$where}
             ORDER BY created_at DESC 
             LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
        
        return $conversations ? $conversations : array();
    }
    
    /**
     * Get conversations for a session (with pagination)
     */
    public function get_conversations($session_id, $page = 1, $per_page = 50) {
        global $wpdb;
        
        $session_id = absint($session_id);
        $page = absint($page);
        $per_page = min(absint($per_page), 100); // Max 100
        $offset = ($page - 1) * $per_page;
        
        // Optimized: Only select fields needed for display (exclude context_data which can be large)
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT id, user_id, session_id, location, message, response, created_at
             FROM {$this->conversations_table}
             WHERE session_id = %d
             ORDER BY created_at ASC
             LIMIT %d OFFSET %d",
            $session_id,
            $per_page,
            $offset
        ), ARRAY_A);
        
        return $results ? $results : array();
    }
    
    /**
     * Get conversation count for a session
     */
    public function get_conversation_count($session_id) {
        global $wpdb;
        
        $session_id = absint($session_id);
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->conversations_table} WHERE session_id = %d",
            $session_id
        ));
        
        return absint($count);
    }
    
    /**
     * Get user sessions (with pagination)
     */
    public function get_user_sessions($user_id, $page = 1, $per_page = 20) {
        global $wpdb;
        
        // Ensure migration has run
        $this->maybe_rename_personality_to_location();
        
        $user_id = absint($user_id);
        $page = absint($page);
        $per_page = min(absint($per_page), 100); // Max 100
        $offset = ($page - 1) * $per_page;
        
        // Check which column exists (location or personality) - use simpler method
        // Try to query with location first, if it fails, use personality
        $column_name = 'location';
        $test_query = "SELECT s.id FROM {$this->sessions_table} s WHERE s.user_id = %d LIMIT 1";
        $test_result = $wpdb->get_results($wpdb->prepare($test_query, $user_id));
        
        // If query fails, try checking column existence more directly
        $columns = $wpdb->get_col("DESCRIBE {$this->sessions_table}");
        if (!in_array('location', $columns) && in_array('personality', $columns)) {
            $column_name = 'personality';
        }
        
        // Security: Whitelist - only allow 'location' or 'personality'
        if (!in_array($column_name, array('location', 'personality'), true)) {
            $column_name = 'location'; // Default fallback
        }
        
        // Build query with proper column name
        // Note: Column name is whitelisted above, so safe to use directly
        // Use GROUP BY with all non-aggregated columns to avoid MySQL strict mode issues
        $query = $wpdb->prepare(
            "SELECT s.id, s.user_id, s.{$column_name} as location, s.session_name, s.created_at, s.updated_at,
                    COUNT(c.id) as message_count
             FROM {$this->sessions_table} s
             LEFT JOIN {$this->conversations_table} c ON s.id = c.session_id
             WHERE s.user_id = %d
             GROUP BY s.id, s.user_id, s.{$column_name}, s.session_name, s.created_at, s.updated_at
             ORDER BY s.updated_at DESC
             LIMIT %d OFFSET %d",
            $user_id,
            $per_page,
            $offset
        );
        
        // Log query for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Academy AI Assistant: get_user_sessions query: ' . $query);
            error_log('Academy AI Assistant: Using column: ' . $column_name);
        }
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        // Log any database errors
        if (!empty($wpdb->last_error)) {
            error_log('Academy AI Assistant: Database error in get_user_sessions: ' . $wpdb->last_error);
            error_log('Academy AI Assistant: Query was: ' . $query);
            error_log('Academy AI Assistant: Last query: ' . $wpdb->last_query);
            // Return empty array on error rather than failing completely
            return array();
        }
        
        // Ensure we return an array
        if (!is_array($results)) {
            error_log('Academy AI Assistant: get_user_sessions returned non-array: ' . gettype($results));
            return array();
        }
        
        // Normalize location values (convert old personality values to 'main' if needed)
        if ($results) {
            foreach ($results as &$result) {
                if (empty($result['location']) || !in_array($result['location'], array('main', 'dashboard', 'sidebar'))) {
                    $result['location'] = 'main';
                }
                // Ensure message_count is an integer
                if (isset($result['message_count'])) {
                    $result['message_count'] = absint($result['message_count']);
                } else {
                    $result['message_count'] = 0;
                }
            }
        }
        
        // Log results for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Academy AI Assistant: get_user_sessions returned ' . count($results) . ' sessions');
            if (!empty($results)) {
                error_log('Academy AI Assistant: First session data: ' . print_r($results[0], true));
            }
        }
        
        return $results ? $results : array();
    }
    
    /**
     * Get user session count
     */
    public function get_user_session_count($user_id) {
        global $wpdb;
        
        $user_id = absint($user_id);
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->sessions_table} WHERE user_id = %d",
            $user_id
        ));
        
        return absint($count);
    }
    
    /**
     * Update session location
     * Security: Validates location (user verification done in REST API)
     */
    public function update_session_location($session_id, $location) {
        global $wpdb;
        
        $session_id = absint($session_id);
        $location = sanitize_text_field($location);
        
        $result = $wpdb->update(
            $this->sessions_table,
            array('location' => $location),
            array('id' => $session_id),
            array('%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Update session name
     * Security: Validates session ownership (should be verified in REST API)
     */
    public function update_session_name($session_id, $session_name) {
        global $wpdb;
        
        $session_id = absint($session_id);
        $session_name = sanitize_text_field($session_name);
        
        // Limit length
        if (strlen($session_name) > 255) {
            $session_name = substr($session_name, 0, 255);
        }
        
        // If empty, set to NULL
        if (empty(trim($session_name))) {
            $session_name = null;
        }
        
        $result = $wpdb->update(
            $this->sessions_table,
            array('session_name' => $session_name),
            array('id' => $session_id),
            array('%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete session and all its conversations
     */
    public function delete_session($session_id) {
        global $wpdb;
        
        $session_id = absint($session_id);
        
        if ($session_id <= 0) {
            return false;
        }
        
        // Delete conversations first (foreign key constraint)
        $wpdb->delete(
            $this->conversations_table,
            array('session_id' => $session_id),
            array('%d')
        );
        
        // Delete session
        $result = $wpdb->delete(
            $this->sessions_table,
            array('id' => $session_id),
            array('%d')
        );
        
        // Return true only if rows were actually deleted
        return ($result !== false && $result > 0);
    }
    
    /**
     * Update session location (legacy method with user_id check)
     * Security: Validates user_id and location
     */
    public function update_session_location_with_user($session_id, $user_id, $location) {
        global $wpdb;
        
        // Security: Validate inputs
        $session_id = absint($session_id);
        $user_id = absint($user_id);
        $location = sanitize_text_field($location);
        
        // Security: Verify user owns the session
        $result = $wpdb->update(
            $this->sessions_table,
            array('location' => $location),
            array('id' => $session_id, 'user_id' => $user_id),
            array('%s'),
            array('%d', '%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get table status for debugging
     * Includes plugin tables and related embeddings table
     */
    public function get_table_status() {
        global $wpdb;
        
        $tables = array(
            'conversations' => $this->conversations_table,
            'sessions' => $this->sessions_table,
            'debug_logs' => $this->debug_logs_table,
            'transcript_embeddings' => $wpdb->prefix . 'alm_transcript_embeddings',
            'chip_suggestions' => $this->chip_suggestions_table
        );
        
        $status = array();
        
        foreach ($tables as $name => $table) {
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) == $table;
            $status[$name] = array(
                'table' => $table,
                'exists' => $exists
            );
        }
        
        return $status;
    }
    
    /**
     * Get chip suggestions (lessons or collections) for a specific chip or all chips
     * 
     * @param string|null $chip_id Chip ID (null for "all chips")
     * @return array Array of suggestions with type, id, and priority
     */
    public function get_chip_suggestions($chip_id = null) {
        global $wpdb;
        
        // Get suggestions for specific chip first (higher priority)
        $specific_suggestions = array();
        if (!empty($chip_id)) {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT suggestion_type, suggestion_id, priority 
                 FROM {$this->chip_suggestions_table} 
                 WHERE chip_id = %s 
                 ORDER BY priority ASC, id ASC",
                $chip_id
            ), ARRAY_A);
            
            if ($results) {
                $specific_suggestions = array_map(function($row) {
                    return array(
                        'type' => $row['suggestion_type'],
                        'id' => absint($row['suggestion_id']),
                        'priority' => absint($row['priority'])
                    );
                }, $results);
            }
        }
        
        // Get suggestions for "all chips" (lower priority, only if no specific suggestions)
        $all_chips_suggestions = array();
        $results = $wpdb->get_results(
            "SELECT suggestion_type, suggestion_id, priority 
             FROM {$this->chip_suggestions_table} 
             WHERE chip_id IS NULL 
             ORDER BY priority ASC, id ASC",
            ARRAY_A
        );
        
        if ($results) {
            $all_chips_suggestions = array_map(function($row) {
                return array(
                    'type' => $row['suggestion_type'],
                    'id' => absint($row['suggestion_id']),
                    'priority' => absint($row['priority'])
                );
            }, $results);
        }
        
        // Specific chip suggestions take precedence
        // If we have specific suggestions, only return those
        // Otherwise, return "all chips" suggestions
        if (!empty($specific_suggestions)) {
            return $specific_suggestions;
        }
        
        return $all_chips_suggestions;
    }
    
    /**
     * Get all chip suggestions with pagination
     * 
     * @param int $page Page number
     * @param int $per_page Items per page
     * @return array Array of suggestions
     */
    public function get_chip_suggestions_all($page = 1, $per_page = 50) {
        global $wpdb;
        
        $page = absint($page);
        $per_page = min(absint($per_page), 100);
        $offset = ($page - 1) * $per_page;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT s.id, s.chip_id, s.suggestion_type, s.suggestion_id, s.priority, s.created_at, s.updated_at,
                    l.lesson_title as lesson_title, l.post_id as lesson_post_id, l.slug as lesson_slug,
                    c.collection_title as collection_title, c.post_id as collection_post_id
             FROM {$this->chip_suggestions_table} s
             LEFT JOIN {$wpdb->prefix}alm_lessons l ON (s.suggestion_type = 'lesson' AND s.suggestion_id = l.ID)
             LEFT JOIN {$wpdb->prefix}alm_collections c ON (s.suggestion_type = 'collection' AND s.suggestion_id = c.ID)
             ORDER BY s.chip_id ASC, s.priority ASC, s.id ASC
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ), ARRAY_A);
        
        return $results ? $results : array();
    }
    
    /**
     * Get total count of chip suggestions
     * 
     * @return int Total count
     */
    public function get_chip_suggestions_count() {
        global $wpdb;
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->chip_suggestions_table}");
        
        return absint($count);
    }
    
    /**
     * Add chip suggestion (lesson or collection)
     * 
     * @param string|null $chip_id Chip ID (null for "all chips")
     * @param string $suggestion_type 'lesson' or 'collection'
     * @param int $suggestion_id Lesson ID or Collection ID
     * @param int $priority Priority (lower = higher priority)
     * @return int|WP_Error Suggestion ID or error
     */
    public function add_chip_suggestion($chip_id, $suggestion_type, $suggestion_id, $priority = 10) {
        global $wpdb;
        
        // Normalize chip_id: empty string or 'all' becomes NULL for "all chips"
        if (empty($chip_id) || $chip_id === 'all' || $chip_id === 'all_chips') {
            $chip_id = null;
        } else {
            $chip_id = sanitize_text_field($chip_id);
        }
        
        $suggestion_type = in_array($suggestion_type, array('lesson', 'collection')) ? $suggestion_type : 'lesson';
        $suggestion_id = absint($suggestion_id);
        $priority = absint($priority);
        
        if ($suggestion_id === 0) {
            return new WP_Error('invalid_suggestion', 'Invalid suggestion ID');
        }
        
        // Check if mapping already exists
        if ($chip_id === null) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->chip_suggestions_table} 
                 WHERE chip_id IS NULL AND suggestion_type = %s AND suggestion_id = %d",
                $suggestion_type,
                $suggestion_id
            ));
        } else {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->chip_suggestions_table} 
                 WHERE chip_id = %s AND suggestion_type = %s AND suggestion_id = %d",
                $chip_id,
                $suggestion_type,
                $suggestion_id
            ));
        }
        
        if ($existing) {
            // Update existing
            $updated = $wpdb->update(
                $this->chip_suggestions_table,
                array('priority' => $priority),
                array('id' => $existing),
                array('%d'),
                array('%d')
            );
            
            return $updated !== false ? $existing : new WP_Error('update_failed', 'Failed to update chip suggestion');
        }
        
        // Insert new
        $inserted = $wpdb->insert(
            $this->chip_suggestions_table,
            array(
                'chip_id' => $chip_id,
                'suggestion_type' => $suggestion_type,
                'suggestion_id' => $suggestion_id,
                'priority' => $priority
            ),
            array('%s', '%s', '%d', '%d')
        );
        
        if ($inserted === false) {
            return new WP_Error('insert_failed', 'Failed to add chip suggestion');
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Delete chip suggestion
     * 
     * @param int $suggestion_id Suggestion mapping ID
     * @return bool Success
     */
    public function delete_chip_suggestion($suggestion_id) {
        global $wpdb;
        
        $suggestion_id = absint($suggestion_id);
        
        $deleted = $wpdb->delete(
            $this->chip_suggestions_table,
            array('id' => $suggestion_id),
            array('%d')
        );
        
        return $deleted !== false;
    }
    
    /**
     * Migration: Add session_name column if it doesn't exist
     * This handles existing installations that don't have the column yet
     */
    public function maybe_add_session_name_column() {
        global $wpdb;
        
        // Check if column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s 
             AND TABLE_NAME = %s 
             AND COLUMN_NAME = 'session_name'",
            DB_NAME,
            $this->sessions_table
        ));
        
        if (empty($column_exists)) {
            // Check if we have personality or location column to determine where to add
            $personality_exists = $wpdb->get_results($wpdb->prepare(
                "SELECT COLUMN_NAME 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = %s 
                 AND TABLE_NAME = %s 
                 AND COLUMN_NAME = 'personality'",
                DB_NAME,
                $this->sessions_table
            ));
            
            $location_exists = $wpdb->get_results($wpdb->prepare(
                "SELECT COLUMN_NAME 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = %s 
                 AND TABLE_NAME = %s 
                 AND COLUMN_NAME = 'location'",
                DB_NAME,
                $this->sessions_table
            ));
            
            $after_column = !empty($location_exists) ? 'location' : (!empty($personality_exists) ? 'personality' : 'user_id');
            
            // Column doesn't exist, add it
            $wpdb->query("ALTER TABLE {$this->sessions_table} ADD COLUMN session_name varchar(255) DEFAULT NULL AFTER {$after_column}");
            
            if (!empty($wpdb->last_error)) {
                error_log('Academy AI Assistant: Error adding session_name column: ' . $wpdb->last_error);
            } else {
                error_log('Academy AI Assistant: Successfully added session_name column to sessions table');
            }
        }
    }
    
    /**
     * Migration: Rename personality column to location
     * This handles existing installations that still have the personality column
     */
    public function maybe_rename_personality_to_location() {
        global $wpdb;
        
        // Check if personality column exists and location doesn't
        $personality_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s 
             AND TABLE_NAME = %s 
             AND COLUMN_NAME = 'personality'",
            DB_NAME,
            $this->sessions_table
        ));
        
        $location_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s 
             AND TABLE_NAME = %s 
             AND COLUMN_NAME = 'location'",
            DB_NAME,
            $this->sessions_table
        ));
        
        // Rename in sessions table
        if (!empty($personality_exists) && empty($location_exists)) {
            $wpdb->query("ALTER TABLE {$this->sessions_table} CHANGE personality location varchar(50) DEFAULT 'main'");
            
            if (!empty($wpdb->last_error)) {
                error_log('Academy AI Assistant: Error renaming personality to location in sessions: ' . $wpdb->last_error);
            } else {
                error_log('Academy AI Assistant: Successfully renamed personality to location in sessions table');
            }
        }
        
        // Rename in conversations table
        $personality_exists_conv = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s 
             AND TABLE_NAME = %s 
             AND COLUMN_NAME = 'personality'",
            DB_NAME,
            $this->conversations_table
        ));
        
        $location_exists_conv = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s 
             AND TABLE_NAME = %s 
             AND COLUMN_NAME = 'location'",
            DB_NAME,
            $this->conversations_table
        ));
        
        if (!empty($personality_exists_conv) && empty($location_exists_conv)) {
            $wpdb->query("ALTER TABLE {$this->conversations_table} CHANGE personality location varchar(50) NOT NULL");
            
            if (!empty($wpdb->last_error)) {
                error_log('Academy AI Assistant: Error renaming personality to location in conversations: ' . $wpdb->last_error);
            } else {
                error_log('Academy AI Assistant: Successfully renamed personality to location in conversations table');
            }
        }
        
        // Rename in debug_logs table
        $personality_exists_logs = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s 
             AND TABLE_NAME = %s 
             AND COLUMN_NAME = 'personality'",
            DB_NAME,
            $this->debug_logs_table
        ));
        
        $location_exists_logs = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s 
             AND TABLE_NAME = %s 
             AND COLUMN_NAME = 'location'",
            DB_NAME,
            $this->debug_logs_table
        ));
        
        if (!empty($personality_exists_logs) && empty($location_exists_logs)) {
            $wpdb->query("ALTER TABLE {$this->debug_logs_table} CHANGE personality location varchar(50) DEFAULT NULL");
            
            if (!empty($wpdb->last_error)) {
                error_log('Academy AI Assistant: Error renaming personality to location in debug_logs: ' . $wpdb->last_error);
            } else {
                error_log('Academy AI Assistant: Successfully renamed personality to location in debug_logs table');
            }
        }
    }
    
    /**
     * Ensure token usage table exists
     * Creates the table if it doesn't exist
     */
    public function ensure_token_usage_table_exists() {
        global $wpdb;
        
        // Check if table exists
        $table_name = $this->token_usage_table;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
        
        if (!$table_exists) {
            // Create the table
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            $token_usage_sql = "CREATE TABLE {$this->token_usage_table} (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                membership_level varchar(50) DEFAULT 'free',
                tokens_used int(11) NOT NULL DEFAULT 0,
                usage_date date NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY user_date (user_id, usage_date),
                KEY user_id (user_id),
                KEY usage_date (usage_date),
                KEY membership_level (membership_level)
            ) $charset_collate;";
            
            dbDelta($token_usage_sql);
            
            // Log if there were issues
            if (!empty($wpdb->last_error)) {
                error_log('Academy AI Assistant: Error creating token_usage table: ' . $wpdb->last_error);
                return false;
            }
            
            // Verify table was created
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
            if (!$table_exists) {
                error_log('Academy AI Assistant: Failed to create token_usage table');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Record token usage for a user
     * @param int $user_id User ID
     * @param int $tokens_used Number of tokens used
     * @param string $membership_level Membership level (free, essentials, studio, premier)
     * @param string $usage_date Date in Y-m-d format (defaults to today)
     * @return bool Success
     */
    public function record_token_usage($user_id, $tokens_used, $membership_level = 'free', $usage_date = null) {
        global $wpdb;
        
        // Ensure table exists before using it
        $this->ensure_token_usage_table_exists();
        
        if (!$user_id || $tokens_used <= 0) {
            return false;
        }
        
        if (empty($usage_date)) {
            $usage_date = current_time('Y-m-d');
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $usage_date)) {
            $usage_date = current_time('Y-m-d');
        }
        
        // Sanitize membership level
        $valid_levels = array('free', 'essentials', 'studio', 'premier');
        if (!in_array($membership_level, $valid_levels)) {
            $membership_level = 'free';
        }
        
        // Insert or update token usage for this user/date
        $result = $wpdb->query($wpdb->prepare(
            "INSERT INTO {$this->token_usage_table} 
            (user_id, membership_level, tokens_used, usage_date) 
            VALUES (%d, %s, %d, %s)
            ON DUPLICATE KEY UPDATE 
            tokens_used = tokens_used + %d,
            membership_level = %s,
            updated_at = CURRENT_TIMESTAMP",
            $user_id,
            $membership_level,
            $tokens_used,
            $usage_date,
            $tokens_used,
            $membership_level
        ));
        
        return $result !== false;
    }
    
    /**
     * Get token usage for a user on a specific date
     * @param int $user_id User ID
     * @param string $usage_date Date in Y-m-d format (defaults to today)
     * @return int|false Token count or false on error
     */
    public function get_token_usage($user_id, $usage_date = null) {
        global $wpdb;
        
        // Ensure table exists before using it
        $this->ensure_token_usage_table_exists();
        
        if (!$user_id) {
            return false;
        }
        
        if (empty($usage_date)) {
            $usage_date = current_time('Y-m-d');
        }
        
        $tokens = $wpdb->get_var($wpdb->prepare(
            "SELECT tokens_used FROM {$this->token_usage_table}
             WHERE user_id = %d AND usage_date = %s",
            $user_id,
            $usage_date
        ));
        
        return $tokens !== null ? (int) $tokens : 0;
    }
    
    /**
     * Get token usage for a user for the current month
     * @param int $user_id User ID
     * @return int Token count
     */
    public function get_monthly_token_usage($user_id) {
        global $wpdb;
        
        // Ensure table exists before using it
        $this->ensure_token_usage_table_exists();
        
        if (!$user_id) {
            return 0;
        }
        
        $current_month = current_time('Y-m');
        
        $tokens = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(tokens_used) FROM {$this->token_usage_table}
             WHERE user_id = %d AND usage_date LIKE %s",
            $user_id,
            $current_month . '%'
        ));
        
        return $tokens !== null ? (int) $tokens : 0;
    }
    
    /**
     * Get token usage statistics for a user
     * @param int $user_id User ID
     * @param int $days Number of days to look back (default 30)
     * @return array Statistics
     */
    public function get_token_usage_stats($user_id, $days = 30) {
        global $wpdb;
        
        // Ensure table exists before using it
        $this->ensure_token_usage_table_exists();
        global $wpdb;
        
        if (!$user_id) {
            return array(
                'total_tokens' => 0,
                'daily_average' => 0,
                'days_used' => 0,
                'max_daily' => 0
            );
        }
        
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                SUM(tokens_used) as total_tokens,
                COUNT(DISTINCT usage_date) as days_used,
                MAX(tokens_used) as max_daily
             FROM {$this->token_usage_table}
             WHERE user_id = %d AND usage_date >= %s",
            $user_id,
            $start_date
        ), ARRAY_A);
        
        $total_tokens = isset($stats['total_tokens']) ? (int) $stats['total_tokens'] : 0;
        $days_used = isset($stats['days_used']) ? (int) $stats['days_used'] : 0;
        $max_daily = isset($stats['max_daily']) ? (int) $stats['max_daily'] : 0;
        $daily_average = $days_used > 0 ? round($total_tokens / $days_used) : 0;
        
        return array(
            'total_tokens' => $total_tokens,
            'daily_average' => $daily_average,
            'days_used' => $days_used,
            'max_daily' => $max_daily
        );
    }
}

