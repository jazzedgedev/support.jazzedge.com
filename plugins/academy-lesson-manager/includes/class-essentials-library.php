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
     * Days between selections
     */
    const DAYS_BETWEEN_SELECTIONS = 30;
    
    /**
     * Maximum selections per membership year
     */
    const SELECTIONS_PER_YEAR = 12;
    
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
     * Check if user is still an active Essentials member
     * 
     * @param int $user_id User ID
     * @return bool True if active Essentials member
     */
    private function is_active_essentials_member($user_id) {
        // Check if user has Essentials membership via Keap/Infusionsoft
        // First check if they have Studio or Premier (if so, they're not Essentials)
        $studio_access = false;
        $premier_access = false;
        
        if (function_exists('memb_hasAnyTags')) {
            $studio_access = memb_hasAnyTags([9954,10136,9807,9827,9819,9956,10136]);
            $premier_access = memb_hasAnyTags([9821,9813,10142]);
        }
        
        // If they have Studio or Premier, they're not Essentials
        if ($studio_access || $premier_access) {
            return false;
        }
        
        // Check for Essentials membership SKU
        $essentials_skus = array('JA_YEAR_ESSENTIALS', 'ACADEMY_ESSENTIALS');
        foreach ($essentials_skus as $sku) {
            if (function_exists('memb_hasMembership') && memb_hasMembership($sku) === true) {
                return true;
            }
        }
        
        // Fallback: Check if they have active membership but not Studio/Premier
        if (function_exists('je_return_active_member') && je_return_active_member() == 'true') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Calculate how many selections have been granted in the current membership year
     * 
     * @param string $membership_start_date Membership start date (Y-m-d)
     * @param string $last_granted_date Last granted date (Y-m-d)
     * @param string $today Current date (Y-m-d)
     * @return int Number of selections granted in current year
     */
    private function get_selections_granted_this_year($membership_start_date, $last_granted_date, $today) {
        // Calculate current membership year start (anniversary of membership_start_date)
        $start_timestamp = strtotime($membership_start_date);
        $today_timestamp = strtotime($today);
        
        // Calculate years since membership started
        $years_since_start = floor(($today_timestamp - $start_timestamp) / (365.25 * 24 * 60 * 60));
        
        // Calculate current year start date (anniversary)
        $current_year_start = date('Y-m-d', strtotime($membership_start_date . ' +' . $years_since_start . ' years'));
        
        // If today is before the anniversary, use previous year
        if ($today_timestamp < strtotime($current_year_start)) {
            $current_year_start = date('Y-m-d', strtotime($membership_start_date . ' +' . ($years_since_start - 1) . ' years'));
        }
        
        // Calculate how many 30-day periods have passed since year start
        // First selection is granted immediately on year start (or signup)
        $days_since_year_start = floor(($today_timestamp - strtotime($current_year_start)) / (24 * 60 * 60));
        
        // First selection is immediate, then one every 30 days
        // So if 0-29 days: 1 selection, 30-59: 2, 60-89: 3, etc.
        $selections_in_year = min(
            floor($days_since_year_start / self::DAYS_BETWEEN_SELECTIONS) + 1,
            self::SELECTIONS_PER_YEAR
        );
        
        return $selections_in_year;
    }
    
    /**
     * Check and grant selections if 30 days have passed
     * Enforces 12 selections per membership year maximum
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
        
        // Check if user is still an active Essentials member
        if (!$this->is_active_essentials_member($user_id)) {
            // Membership cancelled - don't grant new selections
            return false;
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
        $membership_start_date = $selection_data->membership_start_date;
        $last_granted_date = $selection_data->last_granted_date;
        
        // Check if it's time to grant a new selection (30 days have passed)
        if (!$next_grant_date || $today < $next_grant_date) {
            return false;
        }
        
        // Calculate how many selections have been granted in current membership year
        $selections_this_year = $this->get_selections_granted_this_year(
            $membership_start_date,
            $last_granted_date,
            $today
        );
        
        // Only grant if we haven't exceeded 12 selections this year
        if ($selections_this_year >= self::SELECTIONS_PER_YEAR) {
            // Already granted 12 this year - wait for next membership year
            // Update next_grant_date to the start of next membership year
            $start_timestamp = strtotime($membership_start_date);
            $today_timestamp = strtotime($today);
            $years_since_start = floor(($today_timestamp - $start_timestamp) / (365.25 * 24 * 60 * 60));
            $next_year_start = date('Y-m-d', strtotime($membership_start_date . ' +' . ($years_since_start + 1) . ' years'));
            
            $this->wpdb->update(
                $this->selections_table,
                array('next_grant_date' => $next_year_start),
                array('user_id' => $user_id),
                array('%s'),
                array('%d')
            );
            
            return false;
        }
        
        // Grant new selection
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
        // Removed the available_count cap - selections can accumulate indefinitely
        $users = $this->wpdb->get_col($this->wpdb->prepare(
            "SELECT user_id FROM {$this->selections_table} 
             WHERE next_grant_date <= %s",
            $today
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

