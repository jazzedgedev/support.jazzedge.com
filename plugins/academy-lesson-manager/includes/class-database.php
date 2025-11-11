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
            'chapters' => $wpdb->prefix . 'alm_chapters',
            'transcripts' => $wpdb->prefix . 'alm_transcripts',
            'lesson_embeddings' => $wpdb->prefix . 'alm_lesson_embeddings',
            'ai_paths' => $wpdb->prefix . 'alm_ai_paths',
            'lesson_pathways' => $wpdb->prefix . 'alm_lesson_pathways',
            'tags' => $wpdb->prefix . 'alm_tags',
            'essentials_library' => $wpdb->prefix . 'alm_essentials_library',
            'essentials_selections' => $wpdb->prefix . 'alm_essentials_selections'
        );
    }
    
    /**
     * Create all database tables
     */
    public function create_tables() {
        $this->create_collections_table();
        $this->create_lessons_table();
        $this->create_chapters_table();
        $this->create_transcripts_table();
        $this->create_lesson_embeddings_table();
        $this->create_ai_paths_table();
        $this->create_lesson_pathways_table();
        $this->create_tags_table();
        $this->create_essentials_library_table();
        $this->create_essentials_selections_table();
        
        // Check and add membership_level columns if they don't exist
        $this->check_and_add_membership_columns();
        
        // Check and add menu_order column to lessons table if it doesn't exist
        $this->check_and_add_menu_order_column();
        
        // Check and add lesson_level and lesson_tags columns if they don't exist
        $this->check_and_add_lesson_level_and_tags();
        
        // Check and add lesson_style column if it doesn't exist
        $this->check_and_add_lesson_style();
        
        // Check and add sample_video_url column if it doesn't exist
        $this->check_and_add_sample_video_url();
        
        // Check and add sample_chapter_id column if it doesn't exist
        $this->check_and_add_sample_chapter_id();
        
        // Ensure lesson_pathways table exists (migration for existing installations)
        $this->ensure_lesson_pathways_table();
        
        // Ensure tags table exists (migration for existing installations)
        $this->ensure_tags_table();

        // Ensure FULLTEXT indexes for search performance
        $this->ensure_fulltext_indexes();
    }
    
    /**
     * Check and add membership_level columns to existing tables
     */
    public function check_and_add_membership_columns() {
        // Lessons table
        $lessons_table = $this->tables['lessons'];
        $lessons_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $lessons_table LIKE 'membership_level'");
        if (empty($lessons_columns)) {
            $this->wpdb->query("ALTER TABLE $lessons_table ADD COLUMN membership_level int(11) DEFAULT 2 AFTER slug, ADD KEY membership_level (membership_level)");
        }

        // Collections table
        $collections_table = $this->tables['collections'];
        $collections_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $collections_table LIKE 'membership_level'");
        if (empty($collections_columns)) {
            $this->wpdb->query("ALTER TABLE $collections_table ADD COLUMN membership_level int(11) DEFAULT 2 AFTER collection_description, ADD KEY membership_level (membership_level)");
        }
    }
    
    /**
     * Check and add menu_order column to lessons table
     */
    public function check_and_add_menu_order_column() {
        $lessons_table = $this->tables['lessons'];
        $lessons_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $lessons_table LIKE 'menu_order'");
        if (empty($lessons_columns)) {
            $this->wpdb->query("ALTER TABLE $lessons_table ADD COLUMN menu_order int(11) DEFAULT 0 AFTER collection_id, ADD KEY menu_order (menu_order)");
        }
    }
    
    /**
     * Check and add lesson_level and lesson_tags columns to lessons table
     */
    public function check_and_add_lesson_level_and_tags() {
        $lessons_table = $this->tables['lessons'];
        
        // Check for lesson_level column
        $level_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $lessons_table LIKE 'lesson_level'");
        if (empty($level_columns)) {
            $this->wpdb->query("ALTER TABLE $lessons_table ADD COLUMN lesson_level enum('beginner','intermediate','advanced','pro') DEFAULT 'intermediate' AFTER membership_level, ADD KEY lesson_level (lesson_level)");
        }
        
        // Check for lesson_tags column
        $tags_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $lessons_table LIKE 'lesson_tags'");
        if (empty($tags_columns)) {
            $this->wpdb->query("ALTER TABLE $lessons_table ADD COLUMN lesson_tags varchar(500) DEFAULT '' AFTER lesson_level");
        }
    }
    
    /**
     * Check and add lesson_style column to lessons table
     */
    public function check_and_add_lesson_style() {
        $lessons_table = $this->tables['lessons'];
        
        // Check for lesson_style column
        $style_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $lessons_table LIKE 'lesson_style'");
        if (empty($style_columns)) {
            $this->wpdb->query("ALTER TABLE $lessons_table ADD COLUMN lesson_style varchar(500) DEFAULT '' AFTER lesson_tags");
        }
    }
    
    /**
     * Check and add sample_video_url column to lessons table
     */
    public function check_and_add_sample_video_url() {
        $lessons_table = $this->tables['lessons'];
        
        // Check for sample_video_url column
        $sample_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $lessons_table LIKE 'sample_video_url'");
        if (empty($sample_columns)) {
            $this->wpdb->query("ALTER TABLE $lessons_table ADD COLUMN sample_video_url varchar(500) DEFAULT '' AFTER lesson_style");
        }
    }
    
    /**
     * Check and add sample_chapter_id column to lessons table
     */
    public function check_and_add_sample_chapter_id() {
        $lessons_table = $this->tables['lessons'];
        
        // Check for sample_chapter_id column
        $sample_chapter_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $lessons_table LIKE 'sample_chapter_id'");
        if (empty($sample_chapter_columns)) {
            $this->wpdb->query("ALTER TABLE $lessons_table ADD COLUMN sample_chapter_id int(11) DEFAULT 0 AFTER sample_video_url");
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
                lesson_level enum('beginner','intermediate','advanced','pro') DEFAULT 'intermediate',
                lesson_tags varchar(500) DEFAULT '',
                lesson_style varchar(500) DEFAULT '',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (ID),
                KEY post_id (post_id),
                KEY collection_id (collection_id),
                KEY lesson_title (lesson_title),
                KEY song_lesson (song_lesson),
                KEY membership_level (membership_level),
                KEY lesson_level (lesson_level)
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
     * Create transcripts table (stores parsed transcript text for each lesson)
     */
    private function create_transcripts_table() {
        $table_name = $this->tables['transcripts'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            ID int(11) NOT NULL AUTO_INCREMENT,
            lesson_id int(11) NOT NULL,
            source varchar(50) DEFAULT 'vtt',
            content longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            UNIQUE KEY uniq_lesson_source (lesson_id, source),
            KEY lesson_id (lesson_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create lesson embeddings table (stores vector embeddings for lessons)
     */
    private function create_lesson_embeddings_table() {
        $table_name = $this->tables['lesson_embeddings'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            lesson_id int(11) NOT NULL,
            embedding longtext NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (lesson_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create lesson pathways junction table (allows lessons to belong to multiple pathways)
     * Stores pathway assignments and rankings for AI recommendations
     */
    private function create_lesson_pathways_table() {
        $table_name = $this->tables['lesson_pathways'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            ID int(11) NOT NULL AUTO_INCREMENT,
            lesson_id int(11) NOT NULL,
            pathway VARCHAR(100) NOT NULL,
            pathway_rank tinyint(1) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            UNIQUE KEY unique_lesson_pathway (lesson_id, pathway),
            KEY lesson_id (lesson_id),
            KEY pathway (pathway),
            KEY pathway_rank (pathway_rank),
            KEY pathway_rank_composite (pathway, pathway_rank)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create AI paths table (stores AI-generated learning paths for users)
     */
    private function create_ai_paths_table() {
        $table_name = $this->tables['ai_paths'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            ID int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            search_query varchar(255) NOT NULL,
            path_name varchar(255) DEFAULT '',
            summary text,
            recommended_path longtext,
            alternative_lessons longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            KEY user_id (user_id),
            KEY search_query (search_query),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create tags table
     */
    private function create_tags_table() {
        $table_name = $this->tables['tags'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            ID int(11) NOT NULL AUTO_INCREMENT,
            tag_name varchar(255) NOT NULL,
            tag_slug varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            UNIQUE KEY tag_name (tag_name),
            UNIQUE KEY tag_slug (tag_slug),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Ensure tags table exists (for existing installations)
     */
    private function ensure_tags_table() {
        $table_name = $this->tables['tags'];
        $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
        
        if (!$table_exists) {
            $this->create_tags_table();
        }
    }
    
    /**
     * Ensure lesson_pathways table exists (for existing installations)
     */
    private function ensure_lesson_pathways_table() {
        $table_name = $this->tables['lesson_pathways'];
        $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
        
        if (!$table_exists) {
            $this->create_lesson_pathways_table();
        } else {
            // Check if pathway column is ENUM and convert to VARCHAR for dynamic pathways
            $this->convert_pathway_enum_to_varchar();
        }
    }
    
    /**
     * Convert pathway column from ENUM to VARCHAR to support dynamic pathways
     */
    private function convert_pathway_enum_to_varchar() {
        $table_name = $this->tables['lesson_pathways'];
        
        // Check current column type
        $column_info = $this->wpdb->get_row($this->wpdb->prepare(
            "SHOW COLUMNS FROM {$table_name} WHERE Field = %s",
            'pathway'
        ));
        
        if ($column_info && strpos($column_info->Type, 'enum') !== false) {
            // Convert ENUM to VARCHAR(100) to allow dynamic pathways
            $this->wpdb->query("ALTER TABLE {$table_name} MODIFY COLUMN pathway VARCHAR(100) NOT NULL");
        }
    }
    
    /**
     * Ensure FULLTEXT indexes exist for lessons, chapters, and transcripts
     */
    private function ensure_fulltext_indexes() {
        // Lessons FULLTEXT on title and description
        $lessons_table = $this->tables['lessons'];
        $has_ft_lessons = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND INDEX_NAME = 'ft_lessons'",
            $lessons_table
        ));
        if (intval($has_ft_lessons) === 0) {
            // Some MySQL setups require InnoDB + proper collation; ignore errors gracefully
            @$this->wpdb->query("ALTER TABLE $lessons_table ADD FULLTEXT KEY ft_lessons (lesson_title, lesson_description)");
        }

        // Chapters FULLTEXT on chapter_title
        $chapters_table = $this->tables['chapters'];
        $has_ft_chapters = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND INDEX_NAME = 'ft_chapters'",
            $chapters_table
        ));
        if (intval($has_ft_chapters) === 0) {
            @$this->wpdb->query("ALTER TABLE $chapters_table ADD FULLTEXT KEY ft_chapters (chapter_title)");
        }

        // Transcripts FULLTEXT on content
        $transcripts_table = $this->tables['transcripts'];
        $has_ft_transcripts = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND INDEX_NAME = 'ft_transcripts'",
            $transcripts_table
        ));
        if (intval($has_ft_transcripts) === 0) {
            @$this->wpdb->query("ALTER TABLE $transcripts_table ADD FULLTEXT KEY ft_transcripts (content)");
        }
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
     * Create essentials library table
     */
    private function create_essentials_library_table() {
        $table_name = $this->tables['essentials_library'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            lesson_id int(11) NOT NULL,
            selection_cycle int(11) DEFAULT 1,
            selected_at datetime DEFAULT CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user_lesson (user_id, lesson_id),
            KEY user_id (user_id),
            KEY lesson_id (lesson_id),
            KEY selection_cycle (selection_cycle)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create essentials selections table
     */
    private function create_essentials_selections_table() {
        $table_name = $this->tables['essentials_selections'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            membership_start_date date DEFAULT NULL,
            last_granted_date date DEFAULT NULL,
            next_grant_date date DEFAULT NULL,
            available_count int(11) DEFAULT 0,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user_id (user_id),
            KEY user_id (user_id),
            KEY next_grant_date (next_grant_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
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
