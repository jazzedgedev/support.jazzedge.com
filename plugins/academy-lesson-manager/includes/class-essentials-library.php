<?php
/**
 * Essentials Library Management Class
 * 
 * Handles lesson library functionality for Essentials members
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Essentials_Library {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Table names
     */
    private $library_table;
    private $selections_table;
    private $lessons_table;
    
    /**
     * Maximum available selections that can accumulate
     */
    const MAX_ACCUMULATED_SELECTIONS = 3;
    
    /**
     * Days between selections
     */
    const DAYS_BETWEEN_SELECTIONS = 30;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->library_table = $wpdb->prefix . 'alm_essentials_library';
        $this->selections_table = $wpdb->prefix . 'alm_essentials_selections';
        $this->lessons_table = $wpdb->prefix . 'alm_lessons';
    }
    
    /**
     * Initialize membership for a new Essentials member
     * Grants first selection immediately
     * 
     * @param int $user_id User ID
     * @return bool|WP_Error Success or error
     */
    public function initialize_membership($user_id) {
        $user_id = intval($user_id);
        if (!$user_id) {
            return new WP_Error('invalid_user', 'Invalid user ID');
        }
        
        // Check if already initialized
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$this->selections_table} WHERE user_id = %d",
            $user_id
        ));
        
        if ($existing) {
            // Already initialized, just ensure they have at least 1 selection if count is 0
            $this->check_and_grant_selections($user_id);
            return true;
        }
        
        // Initialize with first selection
        $today = current_time('Y-m-d');
        $next_grant = date('Y-m-d', strtotime($today . ' +' . self::DAYS_BETWEEN_SELECTIONS . ' days'));
        
        $result = $this->wpdb->insert(
            $this->selections_table,
            array(
                'user_id' => $user_id,
                'membership_start_date' => $today,
                'last_granted_date' => $today,
                'next_grant_date' => $next_grant,
                'available_count' => 1
            ),
            array('%d', '%s', '%s', '%s', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to initialize membership');
        }
        
        return true;
    }
    
    /**
     * Check and grant selections if 30 days have passed
     * Called by cron job and when user checks their library
     * 
     * @param int $user_id User ID
     * @return bool|WP_Error Success or error
     */
    public function check_and_grant_selections($user_id) {
        $user_id = intval($user_id);
        if (!$user_id) {
            return new WP_Error('invalid_user', 'Invalid user ID');
        }
        
        // Get current selection status
        $selection_data = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->selections_table} WHERE user_id = %d",
            $user_id
        ));
        
        // If not initialized, initialize now
        if (!$selection_data) {
            return $this->initialize_membership($user_id);
        }
        
        $today = current_time('Y-m-d');
        $next_grant_date = $selection_data->next_grant_date;
        $available_count = intval($selection_data->available_count);
        
        // Check if it's time to grant a new selection
        if ($next_grant_date && $today >= $next_grant_date && $available_count < self::MAX_ACCUMULATED_SELECTIONS) {
            $new_count = $available_count + 1;
            $new_next_grant = date('Y-m-d', strtotime($today . ' +' . self::DAYS_BETWEEN_SELECTIONS . ' days'));
            
            $this->wpdb->update(
                $this->selections_table,
                array(
                    'last_granted_date' => $today,
                    'next_grant_date' => $new_next_grant,
                    'available_count' => $new_count
                ),
                array('user_id' => $user_id),
                array('%s', '%s', '%d'),
                array('%d')
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get available selections count for a user
     * 
     * @param int $user_id User ID
     * @return int Available selections count
     */
    public function get_available_selections($user_id) {
        $user_id = intval($user_id);
        if (!$user_id) {
            return 0;
        }
        
        // Check and grant if needed
        $this->check_and_grant_selections($user_id);
        
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT available_count FROM {$this->selections_table} WHERE user_id = %d",
            $user_id
        ));
        
        return intval($count);
    }
    
    /**
     * Get next grant date for a user
     * 
     * @param int $user_id User ID
     * @return string|false Date string or false if not found
     */
    public function get_next_grant_date($user_id) {
        $user_id = intval($user_id);
        if (!$user_id) {
            return false;
        }
        
        $date = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT next_grant_date FROM {$this->selections_table} WHERE user_id = %d",
            $user_id
        ));
        
        return $date ? $date : false;
    }
    
    /**
     * Get user's library (all selected lessons)
     * 
     * @param int $user_id User ID
     * @return array Array of lesson objects
     */
    public function get_user_library($user_id) {
        $user_id = intval($user_id);
        if (!$user_id) {
            return array();
        }
        
        $lessons = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT l.*, el.selected_at, el.selection_cycle
             FROM {$this->library_table} el
             INNER JOIN {$this->lessons_table} l ON l.ID = el.lesson_id
             WHERE el.user_id = %d
             ORDER BY el.selected_at DESC",
            $user_id
        ));
        
        return $lessons ? $lessons : array();
    }
    
    /**
     * Add lesson to user's library
     * 
     * @param int $user_id User ID
     * @param int $lesson_id Lesson ID
     * @return bool|WP_Error Success or error
     */
    public function add_lesson_to_library($user_id, $lesson_id) {
        $user_id = intval($user_id);
        $lesson_id = intval($lesson_id);
        
        if (!$user_id || !$lesson_id) {
            return new WP_Error('invalid_params', 'Invalid user ID or lesson ID');
        }
        
        // Check if user has available selections
        $available = $this->get_available_selections($user_id);
        if ($available <= 0) {
            return new WP_Error('no_selections', 'No available selections. Please wait for your next selection period.');
        }
        
        // Verify lesson exists and is Studio level (level 2)
        $lesson = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT ID, membership_level FROM {$this->lessons_table} WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            return new WP_Error('lesson_not_found', 'Lesson not found');
        }
        
        if (intval($lesson->membership_level) !== 2) {
            return new WP_Error('invalid_lesson_level', 'Only Studio-level lessons can be added to your library');
        }
        
        // Check if already in library
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$this->library_table} WHERE user_id = %d AND lesson_id = %d",
            $user_id, $lesson_id
        ));
        
        if ($existing) {
            return new WP_Error('already_in_library', 'This lesson is already in your library');
        }
        
        // Get current cycle number
        $max_cycle = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT MAX(selection_cycle) FROM {$this->library_table} WHERE user_id = %d",
            $user_id
        ));
        $next_cycle = intval($max_cycle) + 1;
        
        // Add to library
        $result = $this->wpdb->insert(
            $this->library_table,
            array(
                'user_id' => $user_id,
                'lesson_id' => $lesson_id,
                'selection_cycle' => $next_cycle,
                'selected_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to add lesson to library');
        }
        
        // Decrement available count
        $this->wpdb->query($this->wpdb->prepare(
            "UPDATE {$this->selections_table} 
             SET available_count = available_count - 1 
             WHERE user_id = %d AND available_count > 0",
            $user_id
        ));
        
        return true;
    }
    
    /**
     * Remove lesson from user's library (optional feature)
     * 
     * @param int $user_id User ID
     * @param int $lesson_id Lesson ID
     * @return bool|WP_Error Success or error
     */
    public function remove_lesson_from_library($user_id, $lesson_id) {
        $user_id = intval($user_id);
        $lesson_id = intval($lesson_id);
        
        if (!$user_id || !$lesson_id) {
            return new WP_Error('invalid_params', 'Invalid user ID or lesson ID');
        }
        
        $result = $this->wpdb->delete(
            $this->library_table,
            array(
                'user_id' => $user_id,
                'lesson_id' => $lesson_id
            ),
            array('%d', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to remove lesson from library');
        }
        
        return true;
    }
    
    /**
     * Check if user has lesson in their library
     * 
     * @param int $user_id User ID
     * @param int $lesson_id Lesson ID
     * @return bool True if in library
     */
    public function has_lesson_in_library($user_id, $lesson_id) {
        $user_id = intval($user_id);
        $lesson_id = intval($lesson_id);
        
        if (!$user_id || !$lesson_id) {
            return false;
        }
        
        $exists = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$this->library_table} WHERE user_id = %d AND lesson_id = %d",
            $user_id, $lesson_id
        ));
        
        return !empty($exists);
    }
    
    /**
     * Get all Studio-level (level 2) lessons available for selection
     * 
     * @param string $search Optional search term
     * @param int $limit Optional limit
     * @param int $offset Optional offset
     * @return array Array of lesson objects
     */
    public function get_selectable_lessons($search = '', $limit = 100, $offset = 0) {
        $collections_table = $this->wpdb->prefix . 'alm_collections';
        $where = "WHERE l.membership_level = 2";
        
        if (!empty($search)) {
            $search_term = '%' . $this->wpdb->esc_like($search) . '%';
            $where .= $this->wpdb->prepare(
                " AND (l.lesson_title LIKE %s OR l.lesson_description LIKE %s)",
                $search_term, $search_term
            );
        }
        
        $limit_clause = '';
        if ($limit > 0) {
            $limit_clause = $this->wpdb->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
        }
        
        $lessons = $this->wpdb->get_results(
            "SELECT l.*, c.collection_title 
             FROM {$this->lessons_table} l
             LEFT JOIN {$collections_table} c ON c.ID = l.collection_id
             {$where} 
             ORDER BY l.lesson_title ASC 
             {$limit_clause}"
        );
        
        return $lessons ? $lessons : array();
    }
    
    /**
     * Get count of selectable lessons
     * 
     * @param string $search Optional search term
     * @return int Count
     */
    public function get_selectable_lessons_count($search = '') {
        $where = "WHERE membership_level = 2";
        
        if (!empty($search)) {
            $search_term = '%' . $this->wpdb->esc_like($search) . '%';
            $where .= $this->wpdb->prepare(
                " AND (lesson_title LIKE %s OR lesson_description LIKE %s)",
                $search_term, $search_term
            );
        }
        
        $count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->lessons_table} {$where}");
        
        return intval($count);
    }
    
    /**
     * Check all Essentials members and grant selections where due
     * Called by cron job
     * 
     * @return int Number of users processed
     */
    public function process_all_members() {
        $today = current_time('Y-m-d');
        
        // Get all users whose next_grant_date is today or past
        $users = $this->wpdb->get_col($this->wpdb->prepare(
            "SELECT user_id FROM {$this->selections_table} 
             WHERE next_grant_date <= %s AND available_count < %d",
            $today, self::MAX_ACCUMULATED_SELECTIONS
        ));
        
        $processed = 0;
        foreach ($users as $user_id) {
            if ($this->check_and_grant_selections($user_id)) {
                $processed++;
            }
        }
        
        return $processed;
    }
}

