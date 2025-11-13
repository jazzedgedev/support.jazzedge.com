<?php
/**
 * Cost Logger Class
 * 
 * Tracks and logs transcription costs
 */

class Transcription_Cost_Logger {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'alm_transcription_costs';
        $this->create_table_if_not_exists();
    }
    
    /**
     * Create cost logging table if it doesn't exist
     */
    private function create_table_if_not_exists() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            ID bigint(20) NOT NULL AUTO_INCREMENT,
            chapter_id int(11) NOT NULL,
            duration_minutes decimal(10,2) NOT NULL,
            cost decimal(10,4) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            KEY chapter_id (chapter_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Log a transcription cost
     * 
     * @param int $chapter_id Chapter ID
     * @param float $duration_minutes Duration in minutes
     * @param float $cost Cost in dollars
     */
    public function log_transcription($chapter_id, $duration_minutes, $cost) {
        global $wpdb;
        
        $wpdb->insert(
            $this->table_name,
            array(
                'chapter_id' => $chapter_id,
                'duration_minutes' => $duration_minutes,
                'cost' => $cost,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%f', '%f', '%s')
        );
    }
    
    /**
     * Get total cost
     * 
     * @return float Total cost
     */
    public function get_total_cost() {
        global $wpdb;
        
        $total = $wpdb->get_var("SELECT SUM(cost) FROM {$this->table_name}");
        return floatval($total);
    }
    
    /**
     * Get recent cost logs
     * 
     * @param int $limit Number of logs to return
     * @return array Array of log objects
     */
    public function get_recent_logs($limit = 10) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d",
            $limit
        ));
    }
    
    /**
     * Get cost by chapter
     * 
     * @param int $chapter_id Chapter ID
     * @return float Total cost for chapter
     */
    public function get_chapter_cost($chapter_id) {
        global $wpdb;
        
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(cost) FROM {$this->table_name} WHERE chapter_id = %d",
            $chapter_id
        ));
        return floatval($total);
    }
}

