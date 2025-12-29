<?php
/**
 * Database Class for Academy Analytics
 * 
 * Handles database table creation and CRUD operations
 * 
 * @package Academy_Analytics
 */

if (!defined('ABSPATH')) {
    exit;
}

class Academy_Analytics_Database {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Table name
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'academy_analytics_events';
    }
    
    /**
     * Get table name
     */
    public function get_table_name() {
        return $this->table_name;
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            email varchar(255) DEFAULT NULL,
            form_name varchar(255) DEFAULT NULL,
            page_title varchar(255) DEFAULT NULL,
            page_url varchar(500) DEFAULT NULL,
            referrer varchar(500) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY email (email),
            KEY form_name (form_name),
            KEY page_url (page_url(255)),
            KEY created_at (created_at),
            KEY event_date (event_type, created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Check if a similar event was recently recorded (throttle check)
     * 
     * @param array $data Event data
     * @param int $throttle_seconds Throttle period in seconds
     * @return bool True if recent event exists, false otherwise
     */
    public function has_recent_event($data, $throttle_seconds = 60) {
        if ($throttle_seconds <= 0) {
            return false; // No throttling
        }
        
        $where = array();
        $where_values = array();
        
        // Check by event_type (required)
        if (!empty($data['event_type'])) {
            $where[] = "event_type = %s";
            $where_values[] = sanitize_text_field($data['event_type']);
        } else {
            return false; // Can't throttle without event_type
        }
        
        // Check by email if provided
        if (!empty($data['email'])) {
            $where[] = "email = %s";
            $where_values[] = sanitize_email($data['email']);
        }
        
        // Check by user_id if provided
        if (!empty($data['user_id'])) {
            $where[] = "user_id = %d";
            $where_values[] = intval($data['user_id']);
        }
        
        // If no email or user_id, can't effectively throttle
        if (empty($where_values) || (empty($data['email']) && empty($data['user_id']))) {
            return false;
        }
        
        // Check within throttle period
        $throttle_date = date('Y-m-d H:i:s', current_time('timestamp') - $throttle_seconds);
        $where[] = "created_at >= %s";
        $where_values[] = $throttle_date;
        
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause} LIMIT 1";
        $query = $this->wpdb->prepare($query, $where_values);
        
        $count = $this->wpdb->get_var($query);
        
        return intval($count) > 0;
    }
    
    /**
     * Insert event
     * 
     * @param array $data Event data
     * @param bool $check_throttle Whether to check throttle before inserting
     * @return int|false Event ID on success, false on failure or if throttled
     */
    public function insert_event($data, $check_throttle = true) {
        $defaults = array(
            'event_type' => '',
            'user_id' => null,
            'email' => null,
            'form_name' => null,
            'page_title' => null,
            'page_url' => null,
            'referrer' => null,
            'ip_address' => null,
            'data' => null,
            'created_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Check throttle if enabled
        if ($check_throttle) {
            $throttle_seconds = get_option('academy_analytics_throttle_seconds', 60);
            if ($this->has_recent_event($data, $throttle_seconds)) {
                return false; // Throttled - don't insert
            }
        }
        
        // Sanitize data
        $insert_data = array(
            'event_type' => sanitize_text_field($data['event_type']),
            'user_id' => !empty($data['user_id']) ? intval($data['user_id']) : null,
            'email' => !empty($data['email']) ? sanitize_email($data['email']) : null,
            'form_name' => !empty($data['form_name']) ? sanitize_text_field($data['form_name']) : null,
            'page_title' => !empty($data['page_title']) ? sanitize_text_field($data['page_title']) : null,
            'page_url' => !empty($data['page_url']) ? esc_url_raw($data['page_url']) : null,
            'referrer' => !empty($data['referrer']) ? esc_url_raw($data['referrer']) : null,
            'ip_address' => !empty($data['ip_address']) ? sanitize_text_field($data['ip_address']) : null,
            'data' => !empty($data['data']) ? json_encode($data['data']) : null,
            'created_at' => $data['created_at']
        );
        
        $result = $this->wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            return $this->wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Get event by ID
     * 
     * @param int $id Event ID
     * @return object|null Event object or null
     */
    public function get_event($id) {
        $event = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
        
        if ($event && $event->data) {
            $event->data = json_decode($event->data, true);
        }
        
        return $event;
    }
    
    /**
     * Get events with filters
     * 
     * @param array $args Query arguments
     * @return array Events array
     */
    public function get_events($args = array()) {
        $defaults = array(
            'event_type' => '',
            'user_id' => null,
            'email' => '',
            'form_name' => '',
            'page_url' => '',
            'date_from' => '',
            'date_to' => '',
            'search' => '',
            'per_page' => 20,
            'page' => 1,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $where_values = array();
        
        if (!empty($args['event_type'])) {
            $where[] = "event_type = %s";
            $where_values[] = $args['event_type'];
        }
        
        if (!empty($args['user_id'])) {
            $where[] = "user_id = %d";
            $where_values[] = intval($args['user_id']);
        }
        
        if (!empty($args['email'])) {
            $where[] = "email = %s";
            $where_values[] = sanitize_email($args['email']);
        }
        
        if (!empty($args['form_name'])) {
            $where[] = "form_name = %s";
            $where_values[] = sanitize_text_field($args['form_name']);
        }
        
        if (!empty($args['page_url'])) {
            $where[] = "page_url LIKE %s";
            $where_values[] = '%' . $this->wpdb->esc_like($args['page_url']) . '%';
        }
        
        if (!empty($args['date_from'])) {
            $where[] = "created_at >= %s";
            $where_values[] = sanitize_text_field($args['date_from']);
        }
        
        if (!empty($args['date_to'])) {
            $where[] = "created_at <= %s";
            $where_values[] = sanitize_text_field($args['date_to']);
        }
        
        if (!empty($args['search'])) {
            $search = '%' . $this->wpdb->esc_like($args['search']) . '%';
            $where[] = "(email LIKE %s OR form_name LIKE %s OR page_title LIKE %s OR page_url LIKE %s)";
            $where_values[] = $search;
            $where_values[] = $search;
            $where_values[] = $search;
            $where_values[] = $search;
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";
        if (!empty($where_values)) {
            $count_query = $this->wpdb->prepare($count_query, $where_values);
        }
        $total = $this->wpdb->get_var($count_query);
        
        // Get events
        $offset = ($args['page'] - 1) * $args['per_page'];
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        $query = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY {$orderby} LIMIT %d OFFSET %d";
        $query_values = array_merge($where_values, array($args['per_page'], $offset));
        $query = $this->wpdb->prepare($query, $query_values);
        
        $events = $this->wpdb->get_results($query);
        
        // Decode JSON data
        foreach ($events as $event) {
            if ($event->data) {
                $event->data = json_decode($event->data, true);
            }
        }
        
        return array(
            'events' => $events,
            'total' => intval($total),
            'pages' => ceil($total / $args['per_page'])
        );
    }
    
    /**
     * Update event
     * 
     * @param int $id Event ID
     * @param array $data Event data
     * @return bool Success
     */
    public function update_event($id, $data) {
        $update_data = array();
        
        if (isset($data['event_type'])) {
            $update_data['event_type'] = sanitize_text_field($data['event_type']);
        }
        
        if (isset($data['user_id'])) {
            $update_data['user_id'] = !empty($data['user_id']) ? intval($data['user_id']) : null;
        }
        
        if (isset($data['email'])) {
            $update_data['email'] = !empty($data['email']) ? sanitize_email($data['email']) : null;
        }
        
        if (isset($data['form_name'])) {
            $update_data['form_name'] = !empty($data['form_name']) ? sanitize_text_field($data['form_name']) : null;
        }
        
        if (isset($data['page_title'])) {
            $update_data['page_title'] = !empty($data['page_title']) ? sanitize_text_field($data['page_title']) : null;
        }
        
        if (isset($data['page_url'])) {
            $update_data['page_url'] = !empty($data['page_url']) ? esc_url_raw($data['page_url']) : null;
        }
        
        if (isset($data['referrer'])) {
            $update_data['referrer'] = !empty($data['referrer']) ? esc_url_raw($data['referrer']) : null;
        }
        
        if (isset($data['ip_address'])) {
            $update_data['ip_address'] = !empty($data['ip_address']) ? sanitize_text_field($data['ip_address']) : null;
        }
        
        if (isset($data['data'])) {
            $update_data['data'] = is_array($data['data']) ? json_encode($data['data']) : $data['data'];
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $this->wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => intval($id)),
            null,
            array('%d')
        ) !== false;
    }
    
    /**
     * Delete event
     * 
     * @param int $id Event ID
     * @return bool Success
     */
    public function delete_event($id) {
        return $this->wpdb->delete(
            $this->table_name,
            array('id' => intval($id)),
            array('%d')
        ) !== false;
    }
    
    /**
     * Delete multiple events
     * 
     * @param array $ids Event IDs
     * @return int Number of deleted events
     */
    public function delete_events($ids) {
        if (empty($ids) || !is_array($ids)) {
            return 0;
        }
        
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);
        
        if (empty($ids)) {
            return 0;
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = $this->wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE id IN ($placeholders)",
            $ids
        );
        
        return $this->wpdb->query($query);
    }
    
    /**
     * Get statistics
     * 
     * @param array $args Query arguments
     * @return array Statistics
     */
    public function get_stats($args = array()) {
        $defaults = array(
            'event_type' => '',
            'date_from' => '',
            'date_to' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $where_values = array();
        
        if (!empty($args['event_type'])) {
            $where[] = "event_type = %s";
            $where_values[] = $args['event_type'];
        }
        
        if (!empty($args['date_from'])) {
            $where[] = "created_at >= %s";
            $where_values[] = sanitize_text_field($args['date_from']);
        }
        
        if (!empty($args['date_to'])) {
            $where[] = "created_at <= %s";
            $where_values[] = sanitize_text_field($args['date_to']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        $stats = array();
        
        // Total events
        $query = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";
        if (!empty($where_values)) {
            $query = $this->wpdb->prepare($query, $where_values);
        }
        $stats['total_events'] = intval($this->wpdb->get_var($query));
        
        // Events by type
        $query = "SELECT event_type, COUNT(*) as count FROM {$this->table_name} WHERE {$where_clause} GROUP BY event_type";
        if (!empty($where_values)) {
            $query = $this->wpdb->prepare($query, $where_values);
        }
        $stats['by_type'] = $this->wpdb->get_results($query, OBJECT_K);
        
        // Top forms
        $query = "SELECT form_name, COUNT(*) as count FROM {$this->table_name} WHERE {$where_clause} AND form_name IS NOT NULL GROUP BY form_name ORDER BY count DESC LIMIT 10";
        if (!empty($where_values)) {
            $query = $this->wpdb->prepare($query, $where_values);
        }
        $stats['top_forms'] = $this->wpdb->get_results($query);
        
        // Top pages
        $query = "SELECT page_title, page_url, COUNT(*) as count FROM {$this->table_name} WHERE {$where_clause} AND page_url IS NOT NULL GROUP BY page_url ORDER BY count DESC LIMIT 10";
        if (!empty($where_values)) {
            $query = $this->wpdb->prepare($query, $where_values);
        }
        $stats['top_pages'] = $this->wpdb->get_results($query);
        
        // Unique users
        $query = "SELECT COUNT(DISTINCT user_id) FROM {$this->table_name} WHERE {$where_clause} AND user_id IS NOT NULL";
        if (!empty($where_values)) {
            $query = $this->wpdb->prepare($query, $where_values);
        }
        $stats['unique_users'] = intval($this->wpdb->get_var($query));
        
        // Unique emails
        $query = "SELECT COUNT(DISTINCT email) FROM {$this->table_name} WHERE {$where_clause} AND email IS NOT NULL";
        if (!empty($where_values)) {
            $query = $this->wpdb->prepare($query, $where_values);
        }
        $stats['unique_emails'] = intval($this->wpdb->get_var($query));
        
        return $stats;
    }
    
    /**
     * Get time series data for charts
     * 
     * @param array $args Query arguments
     * @return array Time series data
     */
    public function get_time_series_data($args = array()) {
        $defaults = array(
            'event_type' => '',
            'date_from' => '',
            'date_to' => '',
            'group_by' => 'day' // day, week, month
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $where_values = array();
        
        if (!empty($args['event_type'])) {
            $where[] = "event_type = %s";
            $where_values[] = $args['event_type'];
        }
        
        if (!empty($args['date_from'])) {
            $where[] = "created_at >= %s";
            $where_values[] = sanitize_text_field($args['date_from']);
        }
        
        if (!empty($args['date_to'])) {
            $where[] = "created_at <= %s";
            $where_values[] = sanitize_text_field($args['date_to']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Determine date format based on group_by
        switch ($args['group_by']) {
            case 'week':
                $date_format = "DATE_FORMAT(created_at, '%Y-%u')";
                break;
            case 'month':
                $date_format = "DATE_FORMAT(created_at, '%Y-%m')";
                break;
            case 'day':
            default:
                $date_format = "DATE(created_at)";
                break;
        }
        
        $query = "SELECT {$date_format} as date, COUNT(*) as count FROM {$this->table_name} WHERE {$where_clause} GROUP BY {$date_format} ORDER BY date ASC";
        if (!empty($where_values)) {
            $query = $this->wpdb->prepare($query, $where_values);
        }
        
        $results = $this->wpdb->get_results($query);
        
        return $results;
    }
    
    /**
     * Calculate conversion rate between two events
     * 
     * @param array $args Conversion arguments
     * @return array Conversion rate data
     */
    public function get_conversion_rate($args = array()) {
        $defaults = array(
            'start_event_type' => '',
            'start_event_filter' => array(), // e.g., array('form_name' => 'starter_free')
            'conversion_event_type' => '',
            'conversion_event_filter' => array(),
            'date_from' => '',
            'date_to' => '',
            'time_window_days' => 7, // Conversion must happen within X days
            'match_by' => 'email' // 'email' or 'user_id'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Build WHERE clause for starting events
        $start_where = array('1=1');
        $start_values = array();
        
        $start_where[] = "event_type = %s";
        $start_values[] = $args['start_event_type'];
        
        if (!empty($args['date_from'])) {
            $start_where[] = "created_at >= %s";
            $start_values[] = sanitize_text_field($args['date_from']);
        }
        
        if (!empty($args['date_to'])) {
            $start_where[] = "created_at <= %s";
            $start_values[] = sanitize_text_field($args['date_to']);
        }
        
        // Add start event filters
        foreach ($args['start_event_filter'] as $field => $value) {
            if (!empty($value)) {
                $start_where[] = $field . " = %s";
                $start_values[] = sanitize_text_field($value);
            }
        }
        
        $start_where_clause = implode(' AND ', $start_where);
        
        // Build WHERE clause for conversion events
        $conv_where = array('1=1');
        $conv_values = array();
        
        $conv_where[] = "event_type = %s";
        $conv_values[] = $args['conversion_event_type'];
        
        if (!empty($args['date_from'])) {
            $conv_where[] = "created_at >= %s";
            $conv_values[] = sanitize_text_field($args['date_from']);
        }
        
        // Add conversion event filters
        foreach ($args['conversion_event_filter'] as $field => $value) {
            if (!empty($value)) {
                $conv_where[] = $field . " = %s";
                $conv_values[] = sanitize_text_field($value);
            }
        }
        
        $conv_where_clause = implode(' AND ', $conv_where);
        
        // Get unique users who did starting event
        $match_field = $args['match_by'] === 'user_id' ? 'user_id' : 'email';
        
        $start_query = "SELECT DISTINCT {$match_field}, created_at as start_time FROM {$this->table_name} WHERE {$start_where_clause} AND {$match_field} IS NOT NULL";
        if (!empty($start_values)) {
            $start_query = $this->wpdb->prepare($start_query, $start_values);
        }
        
        $starting_users = $this->wpdb->get_results($start_query);
        $total_starting = count($starting_users);
        
        if ($total_starting === 0) {
            return array(
                'total_starting' => 0,
                'total_conversions' => 0,
                'conversion_rate' => 0,
                'conversion_percentage' => '0.00%'
            );
        }
        
        // Get conversion events
        $conv_query = "SELECT {$match_field}, created_at as conv_time FROM {$this->table_name} WHERE {$conv_where_clause} AND {$match_field} IS NOT NULL";
        if (!empty($conv_values)) {
            $conv_query = $this->wpdb->prepare($conv_query, $conv_values);
        }
        
        $conversion_events = $this->wpdb->get_results($conv_query);
        
        // Match conversions to starting events
        $conversions = 0;
        $time_window_seconds = $args['time_window_days'] * 24 * 60 * 60;
        
        foreach ($starting_users as $starter) {
            $start_time = strtotime($starter->start_time);
            $starter_value = $starter->{$match_field};
            
            foreach ($conversion_events as $conv) {
                if ($conv->{$match_field} === $starter_value) {
                    $conv_time = strtotime($conv->conv_time);
                    $time_diff = $conv_time - $start_time;
                    
                    // Check if conversion happened after start and within time window
                    if ($time_diff > 0 && $time_diff <= $time_window_seconds) {
                        $conversions++;
                        break; // Count once per starting user
                    }
                }
            }
        }
        
        $conversion_rate = $total_starting > 0 ? ($conversions / $total_starting) * 100 : 0;
        
        return array(
            'total_starting' => $total_starting,
            'total_conversions' => $conversions,
            'conversion_rate' => $conversion_rate,
            'conversion_percentage' => number_format($conversion_rate, 2) . '%'
        );
    }
    
    /**
     * Get conversion rate over time (daily breakdown)
     * 
     * @param array $args Conversion arguments
     * @return array Daily conversion data
     */
    public function get_conversion_rate_over_time($args = array()) {
        $defaults = array(
            'start_event_type' => '',
            'start_event_filter' => array(),
            'conversion_event_type' => '',
            'conversion_event_filter' => array(),
            'date_from' => '',
            'date_to' => '',
            'time_window_days' => 7,
            'match_by' => 'email'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Get all starting events grouped by date
        $start_where = array('1=1');
        $start_values = array();
        
        $start_where[] = "event_type = %s";
        $start_values[] = $args['start_event_type'];
        
        if (!empty($args['date_from'])) {
            $start_where[] = "created_at >= %s";
            $start_values[] = sanitize_text_field($args['date_from']);
        }
        
        if (!empty($args['date_to'])) {
            $start_where[] = "created_at <= %s";
            $start_values[] = sanitize_text_field($args['date_to']);
        }
        
        foreach ($args['start_event_filter'] as $field => $value) {
            if (!empty($value)) {
                $start_where[] = $field . " = %s";
                $start_values[] = sanitize_text_field($value);
            }
        }
        
        $start_where_clause = implode(' AND ', $start_where);
        $match_field = $args['match_by'] === 'user_id' ? 'user_id' : 'email';
        
        // Get starting events by date
        $start_query = "SELECT DATE(created_at) as date, {$match_field}, created_at FROM {$this->table_name} WHERE {$start_where_clause} AND {$match_field} IS NOT NULL ORDER BY created_at ASC";
        if (!empty($start_values)) {
            $start_query = $this->wpdb->prepare($start_query, $start_values);
        }
        
        $starting_events = $this->wpdb->get_results($start_query);
        
        // Get all conversion events
        $conv_where = array('1=1');
        $conv_values = array();
        
        $conv_where[] = "event_type = %s";
        $conv_values[] = $args['conversion_event_type'];
        
        if (!empty($args['date_from'])) {
            $conv_where[] = "created_at >= %s";
            $conv_values[] = sanitize_text_field($args['date_from']);
        }
        
        foreach ($args['conversion_event_filter'] as $field => $value) {
            if (!empty($value)) {
                $conv_where[] = $field . " = %s";
                $conv_values[] = sanitize_text_field($value);
            }
        }
        
        $conv_where_clause = implode(' AND ', $conv_where);
        $conv_query = "SELECT {$match_field}, created_at FROM {$this->table_name} WHERE {$conv_where_clause} AND {$match_field} IS NOT NULL ORDER BY created_at ASC";
        if (!empty($conv_values)) {
            $conv_query = $this->wpdb->prepare($conv_query, $conv_values);
        }
        
        $conversion_events = $this->wpdb->get_results($conv_query);
        
        // Group by date and calculate conversions
        $daily_data = array();
        $time_window_seconds = $args['time_window_days'] * 24 * 60 * 60;
        
        foreach ($starting_events as $starter) {
            $date = $starter->date;
            if (!isset($daily_data[$date])) {
                $daily_data[$date] = array(
                    'date' => $date,
                    'starting' => 0,
                    'conversions' => 0
                );
            }
            
            $daily_data[$date]['starting']++;
            
            // Check if this user converted within time window
            $start_time = strtotime($starter->created_at);
            $starter_value = $starter->{$match_field};
            
            foreach ($conversion_events as $conv) {
                if ($conv->{$match_field} === $starter_value) {
                    $conv_time = strtotime($conv->created_at);
                    $time_diff = $conv_time - $start_time;
                    
                    if ($time_diff > 0 && $time_diff <= $time_window_seconds) {
                        $daily_data[$date]['conversions']++;
                        break;
                    }
                }
            }
        }
        
        // Calculate conversion rates
        foreach ($daily_data as &$day) {
            $day['conversion_rate'] = $day['starting'] > 0 ? ($day['conversions'] / $day['starting']) * 100 : 0;
        }
        
        return array_values($daily_data);
    }
}

