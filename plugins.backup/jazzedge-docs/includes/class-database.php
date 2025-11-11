<?php
/**
 * Database Class for Jazzedge Docs
 * Handles all database operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jazzedge_Docs_Database {
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for doc ratings/feedback
        $table_ratings = $wpdb->prefix . 'jazzedge_docs_ratings';
        $sql_ratings = "CREATE TABLE $table_ratings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            doc_id bigint(20) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            user_ip varchar(45) DEFAULT NULL,
            rating tinyint(1) NOT NULL,
            feedback text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY doc_id (doc_id),
            KEY user_id (user_id),
            KEY user_ip (user_ip)
        ) $charset_collate;";
        
        // Table for doc analytics/views
        $table_analytics = $wpdb->prefix . 'jazzedge_docs_analytics';
        $sql_analytics = "CREATE TABLE $table_analytics (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            doc_id bigint(20) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            user_ip varchar(45) DEFAULT NULL,
            view_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY doc_id (doc_id),
            KEY user_id (user_id),
            KEY view_date (view_date)
        ) $charset_collate;";
        
        // Table for related articles (manual associations)
        $table_related = $wpdb->prefix . 'jazzedge_docs_related';
        $sql_related = "CREATE TABLE $table_related (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            doc_id bigint(20) NOT NULL,
            related_doc_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY doc_id (doc_id),
            KEY related_doc_id (related_doc_id),
            UNIQUE KEY unique_relation (doc_id, related_doc_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_ratings);
        dbDelta($sql_analytics);
        dbDelta($sql_related);
    }
    
    /**
     * Record a view for a doc
     */
    public function record_view($doc_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_analytics';
        $user_id = get_current_user_id();
        $user_ip = $this->get_user_ip();
        
        $wpdb->insert(
            $table,
            array(
                'doc_id' => $doc_id,
                'user_id' => $user_id ? $user_id : null,
                'user_ip' => $user_ip
            ),
            array('%d', '%d', '%s')
        );
    }
    
    /**
     * Record a rating/feedback
     */
    public function record_rating($doc_id, $rating, $feedback = '') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_ratings';
        $user_id = get_current_user_id();
        $user_ip = $this->get_user_ip();
        
        // Check if user already rated this doc
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE doc_id = %d AND (user_id = %d OR user_ip = %s)",
            $doc_id,
            $user_id ? $user_id : 0,
            $user_ip
        ));
        
        if ($existing) {
            // Update existing rating
            $wpdb->update(
                $table,
                array(
                    'rating' => $rating,
                    'feedback' => $feedback
                ),
                array('id' => $existing),
                array('%d', '%s'),
                array('%d')
            );
            return $existing;
        } else {
            // Insert new rating
            $wpdb->insert(
                $table,
                array(
                    'doc_id' => $doc_id,
                    'user_id' => $user_id ? $user_id : null,
                    'user_ip' => $user_ip,
                    'rating' => $rating,
                    'feedback' => $feedback
                ),
                array('%d', '%d', '%s', '%d', '%s')
            );
            return $wpdb->insert_id;
        }
    }
    
    /**
     * Get average rating for a doc
     */
    public function get_average_rating($doc_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_ratings';
        $average = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(rating) FROM $table WHERE doc_id = %d",
            $doc_id
        ));
        
        return $average ? round($average, 1) : 0;
    }
    
    /**
     * Get rating count for a doc
     */
    public function get_rating_count($doc_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_ratings';
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE doc_id = %d",
            $doc_id
        ));
        
        return (int) $count;
    }
    
    /**
     * Get feedback count for a doc (ratings with comments)
     */
    public function get_feedback_count($doc_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_ratings';
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE doc_id = %d AND feedback != '' AND feedback IS NOT NULL",
            $doc_id
        ));
        
        return (int) $count;
    }
    
    /**
     * Get user rating for a doc
     */
    public function get_user_rating($doc_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_ratings';
        $user_id = get_current_user_id();
        $user_ip = $this->get_user_ip();
        
        $rating = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE doc_id = %d AND (user_id = %d OR user_ip = %s) LIMIT 1",
            $doc_id,
            $user_id ? $user_id : 0,
            $user_ip
        ));
        
        return $rating;
    }
    
    /**
     * Get view count for a doc
     */
    public function get_view_count($doc_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_analytics';
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE doc_id = %d",
            $doc_id
        ));
        
        return (int) $count;
    }
    
    /**
     * Get popular docs
     */
    public function get_popular_docs($limit = 10) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_analytics';
        $docs = $wpdb->get_results($wpdb->prepare(
            "SELECT doc_id, COUNT(*) as view_count 
            FROM $table 
            GROUP BY doc_id 
            ORDER BY view_count DESC 
            LIMIT %d",
            $limit
        ));
        
        return $docs;
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }
    
    /**
     * Add related doc
     */
    public function add_related_doc($doc_id, $related_doc_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_related';
        
        $wpdb->insert(
            $table,
            array(
                'doc_id' => $doc_id,
                'related_doc_id' => $related_doc_id
            ),
            array('%d', '%d')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Remove related doc
     */
    public function remove_related_doc($doc_id, $related_doc_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_related';
        
        $wpdb->delete(
            $table,
            array(
                'doc_id' => $doc_id,
                'related_doc_id' => $related_doc_id
            ),
            array('%d', '%d')
        );
    }
    
    /**
     * Get related docs
     */
    public function get_related_docs($doc_id, $limit = 5) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_related';
        $docs = $wpdb->get_col($wpdb->prepare(
            "SELECT related_doc_id FROM $table WHERE doc_id = %d LIMIT %d",
            $doc_id,
            $limit
        ));
        
        return $docs;
    }
    
    /**
     * Get top-rated docs
     */
    public function get_top_rated_docs($limit = 10) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_ratings';
        $docs = $wpdb->get_results($wpdb->prepare(
            "SELECT doc_id, 
                    AVG(rating) as avg_rating, 
                    COUNT(*) as rating_count 
            FROM $table 
            GROUP BY doc_id 
            HAVING rating_count >= 1
            ORDER BY avg_rating DESC, rating_count DESC 
            LIMIT %d",
            $limit
        ));
        
        return $docs;
    }
    
    /**
     * Get all ratings for a doc (with feedback)
     */
    public function get_doc_ratings($doc_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'jazzedge_docs_ratings';
        $ratings = $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, u.user_email, u.display_name 
            FROM $table r
            LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
            WHERE r.doc_id = %d 
            ORDER BY r.created_at DESC",
            $doc_id
        ));
        
        return $ratings;
    }
}

