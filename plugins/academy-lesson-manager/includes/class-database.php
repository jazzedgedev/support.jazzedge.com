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
            'essentials_selections' => $wpdb->prefix . 'alm_essentials_selections',
            'notifications' => $wpdb->prefix . 'alm_notifications',
            'notification_reads' => $wpdb->prefix . 'alm_notification_reads',
            'promotional_banners' => $wpdb->prefix . 'alm_promotional_banners',
            'faqs' => $wpdb->prefix . 'alm_faqs',
            'teachers' => $wpdb->prefix . 'alm_teachers'
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
        $this->create_notifications_table();
        $this->create_notification_reads_table();
        $this->create_promotional_banners_table();
        $this->create_faqs_table();
        $this->create_teachers_table();
        
        // Insert default FAQs if table is empty
        $this->insert_default_faqs();
        
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
        
        // Check and add chapter_id column to transcripts table if it doesn't exist
        $this->check_and_add_chapter_id_column();
        
        // Ensure lesson_pathways table exists (migration for existing installations)
        $this->ensure_lesson_pathways_table();
        
        // Ensure tags table exists (migration for existing installations)
        $this->ensure_tags_table();

        // Ensure FULLTEXT indexes for search performance
        $this->ensure_fulltext_indexes();
        
        // Check and add display location columns to promotional banners
        $this->check_and_add_promo_banner_display_columns();
        
        // Check and add social media columns to teachers table if they don't exist
        $this->check_and_add_teacher_social_columns();
        
        // Check and add status column to lessons table if it doesn't exist
        $this->check_and_add_lesson_status_column();

        // Ensure notifications table has a category column for tagging
        $this->check_and_add_notification_category_column();
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
     * Check and add chapter_id column to transcripts table
     */
    public function check_and_add_chapter_id_column() {
        $transcripts_table = $this->tables['transcripts'];
        
        // Check if table exists first
        $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$transcripts_table}'") == $transcripts_table;
        if (!$table_exists) {
            return; // Table doesn't exist yet, will be created with column
        }
        
        // Check for chapter_id column
        $chapter_id_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $transcripts_table LIKE 'chapter_id'");
        if (empty($chapter_id_columns)) {
            // Add chapter_id column
            $this->wpdb->query("ALTER TABLE $transcripts_table ADD COLUMN chapter_id int(11) DEFAULT 0 AFTER lesson_id, ADD KEY chapter_id (chapter_id)");
            
            // Drop old unique key if it exists and create new composite key
            $this->wpdb->query("ALTER TABLE $transcripts_table DROP INDEX IF EXISTS uniq_lesson_source");
            $this->wpdb->query("ALTER TABLE $transcripts_table ADD UNIQUE KEY uniq_lesson_chapter_source (lesson_id, chapter_id, source)");
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
            chapter_id int(11) DEFAULT 0,
            source varchar(50) DEFAULT 'vtt',
            content longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            UNIQUE KEY uniq_lesson_chapter_source (lesson_id, chapter_id, source),
            KEY lesson_id (lesson_id),
            KEY chapter_id (chapter_id)
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
     * Create notifications table
     */
    private function create_notifications_table() {
        $table_name = $this->tables['notifications'];

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            ID int(11) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            category varchar(50) NOT NULL DEFAULT 'site_update',
            content longtext,
            link_label varchar(100) DEFAULT '',
            link_url varchar(500) DEFAULT '',
            is_active tinyint(1) DEFAULT 1,
            publish_at datetime DEFAULT CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            KEY is_active (is_active),
            KEY publish_at (publish_at),
            KEY category (category)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create notification reads table
     */
    private function create_notification_reads_table() {
        $table_name = $this->tables['notification_reads'];

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            notification_id int(11) NOT NULL,
            user_id bigint(20) NOT NULL,
            read_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            UNIQUE KEY notif_user (notification_id, user_id),
            KEY user_id (user_id),
            KEY notification_id (notification_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Ensure the notifications table has a category column
     */
    private function check_and_add_notification_category_column() {
        $notifications_table = $this->tables['notifications'];

        $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$notifications_table}'") === $notifications_table;
        if (!$table_exists) {
            return;
        }

        $category_column = $this->wpdb->get_results("SHOW COLUMNS FROM {$notifications_table} LIKE 'category'");
        if (empty($category_column)) {
            $this->wpdb->query("ALTER TABLE {$notifications_table} ADD COLUMN category varchar(50) NOT NULL DEFAULT 'site_update' AFTER title, ADD KEY category (category)");
            $this->wpdb->query($this->wpdb->prepare("UPDATE {$notifications_table} SET category = %s WHERE category = '' OR category IS NULL", 'site_update'));
        }
    }

    /**
     * Create promotional banners table
     */
    private function create_promotional_banners_table() {
        $table_name = $this->tables['promotional_banners'];

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            ID int(11) NOT NULL AUTO_INCREMENT,
            banner_type enum('text','image') DEFAULT 'text',
            headline varchar(255) DEFAULT '',
            text_content text,
            button_text varchar(100) DEFAULT '',
            button_url varchar(500) DEFAULT '',
            image_id int(11) DEFAULT 0,
            start_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            KEY is_active (is_active),
            KEY start_date (start_date),
            KEY end_date (end_date)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create FAQs table
     */
    private function create_faqs_table() {
        $table_name = $this->tables['faqs'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            question text NOT NULL,
            answer longtext NOT NULL,
            category varchar(100) NOT NULL DEFAULT 'membership',
            display_order int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category),
            KEY is_active (is_active),
            KEY display_order (display_order)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create teachers table
     */
    private function create_teachers_table() {
        $table_name = $this->tables['teachers'];
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            ID int(11) NOT NULL AUTO_INCREMENT,
            teacher_name varchar(255) NOT NULL,
            teacher_slug varchar(255) NOT NULL,
            picture_id int(11) DEFAULT 0,
            short_bio text,
            long_bio longtext,
            website_url varchar(500) DEFAULT '',
            instagram_url varchar(500) DEFAULT '',
            tiktok_url varchar(500) DEFAULT '',
            facebook_url varchar(500) DEFAULT '',
            youtube_url varchar(500) DEFAULT '',
            linkedin_url varchar(500) DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            UNIQUE KEY teacher_name (teacher_name),
            UNIQUE KEY teacher_slug (teacher_slug),
            KEY picture_id (picture_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Check and add social media columns if they don't exist (for existing installations)
        $this->check_and_add_teacher_social_columns();
    }
    
    /**
     * Check and add social media columns to teachers table
     */
    public function check_and_add_teacher_social_columns() {
        $table_name = $this->tables['teachers'];
        
        // Check if columns exist
        $columns = $this->wpdb->get_col("SHOW COLUMNS FROM {$table_name}");
        
        $social_columns = array(
            'instagram_url' => "ALTER TABLE {$table_name} ADD COLUMN instagram_url varchar(500) DEFAULT '' AFTER website_url",
            'tiktok_url' => "ALTER TABLE {$table_name} ADD COLUMN tiktok_url varchar(500) DEFAULT '' AFTER instagram_url",
            'facebook_url' => "ALTER TABLE {$table_name} ADD COLUMN facebook_url varchar(500) DEFAULT '' AFTER tiktok_url",
            'youtube_url' => "ALTER TABLE {$table_name} ADD COLUMN youtube_url varchar(500) DEFAULT '' AFTER facebook_url",
            'linkedin_url' => "ALTER TABLE {$table_name} ADD COLUMN linkedin_url varchar(500) DEFAULT '' AFTER youtube_url"
        );
        
        foreach ($social_columns as $column_name => $alter_sql) {
            if (!in_array($column_name, $columns)) {
                $this->wpdb->query($alter_sql);
            }
        }
    }
    
    /**
     * Insert default FAQs for membership category
     */
    private function insert_default_faqs() {
        $table_name = $this->tables['faqs'];
        
        // Check if table exists
        $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
        if (!$table_exists) {
            return; // Table doesn't exist yet, skip
        }
        
        // Check if FAQs already exist
        $existing_count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        if ($existing_count > 0) {
            return; // Don't insert if FAQs already exist
        }
        
        // Default FAQs data
        $default_faqs = array(
            array(
                'question' => 'What is the 30-day money-back guarantee?',
                'answer' => '<p>We offer a <strong>30-day money-back guarantee</strong> on your first payment. If you\'re not satisfied with your membership within the first 30 days, you\'re eligible for a full refund. Simply contact us and we\'ll process your refund, no questions asked.</p><p><strong>Important:</strong> Renewals are non-refundable. Once your membership renews, the payment is final.</p>',
                'category' => 'membership',
                'display_order' => 0,
                'is_active' => 1
            ),
            array(
                'question' => 'Can I upgrade my membership at any time?',
                'answer' => '<p>Yes! You can upgrade your membership at any time by <a href="https://support.jazzedge.com/support/?site=academy" target="_blank">contacting us</a>. We\'ll help you upgrade to a higher tier (Essentials to Studio, or Studio to Premier) and adjust your billing accordingly.</p><p>When you upgrade, you\'ll immediately gain access to all the additional features and content available in your new membership level.</p>',
                'category' => 'membership',
                'display_order' => 1,
                'is_active' => 1
            ),
            array(
                'question' => 'How do I cancel my membership?',
                'answer' => '<p>You can cancel your membership at any time directly from your <strong>Account Area</strong>. Simply log in to your account, navigate to your membership settings, and click the cancel option.</p><p>Your membership will remain active until the end of your current billing period, and you\'ll continue to have access to all content until that time. No further charges will be made after cancellation.</p>',
                'category' => 'membership',
                'display_order' => 2,
                'is_active' => 1
            ),
            array(
                'question' => 'Do Studio and Premier members get new lessons?',
                'answer' => '<p>Yes! Both <strong>Studio</strong> and <strong>Premier</strong> members receive access to all new lessons as they\'re added to the Academy.</p><p>Studio members get access to all new Studio-level lessons, while Premier members get access to all new content including both Studio and Premier-exclusive lessons. New content is added regularly, so you\'ll always have fresh material to learn from.</p>',
                'category' => 'membership',
                'display_order' => 3,
                'is_active' => 1
            ),
            array(
                'question' => 'What\'s the difference between monthly and yearly billing?',
                'answer' => '<p><strong>Studio</strong> is the only membership tier that offers both monthly and yearly billing options. <strong>Essentials</strong> and <strong>Premier</strong> are yearly-only memberships.</p><p>With yearly billing, you pay once per year and typically save compared to monthly billing. Your membership will automatically renew each year on the same date as your initial purchase unless you cancel.</p><p><strong>Important:</strong> Our system cannot send renewal reminder emails, so please make a note of your renewal date in your Account Area.</p>',
                'category' => 'membership',
                'display_order' => 4,
                'is_active' => 1
            ),
            array(
                'question' => 'What happens if I cancel my membership?',
                'answer' => '<p>When you cancel your membership, you\'ll continue to have access to all content until the end of your current billing period. After that, your access will end and you\'ll no longer be charged.</p><p>If you decide to rejoin later, you can sign up again at any time. However, any special pricing or promotions you had may not be available when you return.</p>',
                'category' => 'membership',
                'display_order' => 5,
                'is_active' => 1
            ),
            array(
                'question' => 'Can I switch between monthly and yearly billing for Studio?',
                'answer' => '<p>Yes, Studio members can switch between monthly and yearly billing. To make this change, please <a href="https://support.jazzedge.com/support/?site=academy" target="_blank">contact us</a> and we\'ll help you switch your billing cycle.</p><p>If you\'re switching from monthly to yearly, you\'ll be charged for the yearly membership and your billing date will be updated. If switching from yearly to monthly, the change will take effect at your next renewal date.</p>',
                'category' => 'membership',
                'display_order' => 6,
                'is_active' => 1
            ),
            array(
                'question' => 'What payment methods do you accept?',
                'answer' => '<p>We accept all major credit cards (Visa, Mastercard, American Express), debit cards, and PayPal. Payments are processed securely through our payment processor.</p><p>All memberships are set to automatically renew unless you cancel. Make sure your payment method is up to date in your Account Area to avoid any interruption in service.</p>',
                'category' => 'membership',
                'display_order' => 7,
                'is_active' => 1
            )
        );
        
        // Insert each FAQ
        foreach ($default_faqs as $faq) {
            $this->wpdb->insert(
                $table_name,
                array(
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                    'category' => $faq['category'],
                    'display_order' => $faq['display_order'],
                    'is_active' => $faq['is_active'],
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%d', '%s', '%s')
            );
        }
    }
    
    /**
     * Check and add display location columns to promotional banners table
     */
    public function check_and_add_promo_banner_display_columns() {
        $banners_table = $this->tables['promotional_banners'];
        
        // Check if show_on_dashboard column exists
        $dashboard_column = $this->wpdb->get_results("SHOW COLUMNS FROM {$banners_table} LIKE 'show_on_dashboard'");
        if (empty($dashboard_column)) {
            $this->wpdb->query("ALTER TABLE {$banners_table} ADD COLUMN show_on_dashboard tinyint(1) DEFAULT 0 AFTER is_active");
        }
        
        // Check if show_on_join_page column exists
        $join_page_column = $this->wpdb->get_results("SHOW COLUMNS FROM {$banners_table} LIKE 'show_on_join_page'");
        if (empty($join_page_column)) {
            $this->wpdb->query("ALTER TABLE {$banners_table} ADD COLUMN show_on_join_page tinyint(1) DEFAULT 0 AFTER show_on_dashboard");
        }
    }
    
    /**
     * Check and add status column to lessons table
     */
    public function check_and_add_lesson_status_column() {
        $lessons_table = $this->tables['lessons'];
        
        // Check for status column
        $status_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $lessons_table LIKE 'status'");
        if (empty($status_columns)) {
            $this->wpdb->query("ALTER TABLE $lessons_table ADD COLUMN status enum('published','draft','archived') DEFAULT 'published' AFTER sample_chapter_id, ADD KEY status (status)");
        }
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
