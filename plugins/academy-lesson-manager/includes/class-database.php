<?php
/**
 * Database Management Class
 * 
 * Handles creation and management of Academy Lesson Manager database tables
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Database {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Table names
     */
    private $tables = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        // Set table names with prefix
        $this->tables = array(
            'collections' => $wpdb->prefix . 'alm_collections',
            'lessons' => $wpdb->prefix . 'alm_lessons',
            'chapters' => $wpdb->prefix . 'alm_chapters'
        );
    }
    
    /**
     * Create all database tables
     */
    public function create_tables() {
        $this->create_collections_table();
        $this->create_lessons_table();
        $this->create_chapters_table();
        
        // Check and add membership_level columns if they don't exist
        $this->check_and_add_membership_columns();
    }
    
    /**
     * Check and add membership_level columns to existing tables
     */
    public function check_and_add_membership_columns() {
        // Check collections table
        $collections_table = $this->tables['collections'];
        $collections_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $collections_table LIKE 'membership_level'");
        if (empty($collections_columns)) {
            $this->wpdb->query("ALTER TABLE $collections_table ADD COLUMN membership_level int(11) DEFAULT 2 AFTER collection_description, ADD KEY membership_level (membership_level)");
        }
        
        // Check lessons table
        $lessons_table = $this->tables['lessons'];
        $lessons_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $lessons_table LIKE 'membership_level'");
        if (empty($lessons_columns)) {
            $this->wpdb->query("ALTER TABLE $lessons_table ADD COLUMN membership_level int(11) DEFAULT 2 AFTER slug, ADD KEY membership_level (membership_level)");
        }
    }
    
    /**
     * Create collections table
     */
    private function create_collections_table() {
        $table_name = $this->tables['collections'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
            $sql = "CREATE TABLE $table_name (
                ID int(11) NOT NULL AUTO_INCREMENT,
                post_id int(11) DEFAULT 0,
                collection_title varchar(255) NOT NULL,
                collection_description text,
                membership_level int(11) DEFAULT 2,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (ID),
                KEY post_id (post_id),
                KEY collection_title (collection_title),
                KEY membership_level (membership_level)
            ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create lessons table
     */
    private function create_lessons_table() {
        $table_name = $this->tables['lessons'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
            $sql = "CREATE TABLE $table_name (
                ID int(11) NOT NULL AUTO_INCREMENT,
                post_id int(11) DEFAULT 0,
                collection_id int(11) DEFAULT 0,
                lesson_title varchar(255) NOT NULL,
                lesson_description text,
                post_date date DEFAULT NULL,
                duration int(11) DEFAULT 0,
                vtt varchar(100) DEFAULT '',
                resources text,
                assets longtext,
                song_lesson char(1) DEFAULT 'n',
                slug varchar(255) DEFAULT '',
                membership_level int(11) DEFAULT 2,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (ID),
                KEY post_id (post_id),
                KEY collection_id (collection_id),
                KEY lesson_title (lesson_title),
                KEY song_lesson (song_lesson),
                KEY membership_level (membership_level)
            ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create chapters table
     */
    private function create_chapters_table() {
        $table_name = $this->tables['chapters'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            ID int(11) NOT NULL AUTO_INCREMENT,
            lesson_id int(11) NOT NULL,
            chapter_title varchar(255) NOT NULL,
            menu_order int(11) DEFAULT 0,
            vimeo_id int(11) DEFAULT 0,
            bunny_url varchar(255) DEFAULT '',
            youtube_id varchar(50) DEFAULT '',
            duration int(11) DEFAULT 0,
            free char(1) DEFAULT 'n',
            slug varchar(255) DEFAULT '',
            post_date date DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            KEY lesson_id (lesson_id),
            KEY chapter_title (chapter_title),
            KEY menu_order (menu_order),
            KEY vimeo_id (vimeo_id),
            KEY youtube_id (youtube_id),
            KEY free (free)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Check if tables exist
     */
    public function tables_exist() {
        foreach ($this->tables as $table) {
            if ($this->wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Get table name
     */
    public function get_table_name($table) {
        return isset($this->tables[$table]) ? $this->tables[$table] : '';
    }
    
    /**
     * Get all table names
     */
    public function get_tables() {
        return $this->tables;
    }
    
    /**
     * Drop all tables (for uninstall)
     */
    public function drop_tables() {
        foreach ($this->tables as $table) {
            $this->wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
}
