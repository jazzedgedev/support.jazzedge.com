<?php
/**
 * Database Schema Design for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database Schema Design
 * 
 * This file contains the complete database schema design for the Academy Practice Hub.
 * It defines all tables, columns, indexes, and relationships needed for the system.
 */

class APH_Database_Schema {
    
    /**
     * Get the complete database schema
     */
    public static function get_schema() {
        return array(
            'practice_items' => self::get_practice_items_schema(),
            'practice_sessions' => self::get_practice_sessions_schema(),
            'user_stats' => self::get_user_stats_schema(),
            'badges' => self::get_badges_schema(),
            'user_badges' => self::get_user_badges_schema(),
            'lesson_favorites' => self::get_lesson_favorites_schema(),
            'gems_transactions' => self::get_gems_transactions_schema()
        );
    }
    
    /**
     * Practice Items Table Schema
     * 
     * Stores practice items for each user:
     * - JazzEdge Practice Curriculum™ (always present)
     * - Up to 2 custom practice items
     */
    private static function get_practice_items_schema() {
        return array(
            'table_name' => 'jph_practice_items',
            'columns' => array(
                'id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                    'primary_key' => true,
                    'description' => 'Unique practice item ID'
                ),
                'user_id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'not_null' => true,
                    'description' => 'WordPress user ID'
                ),
                'name' => array(
                    'type' => 'VARCHAR',
                    'length' => 255,
                    'not_null' => true,
                    'description' => 'Practice item name'
                ),
                'category' => array(
                    'type' => 'VARCHAR',
                    'length' => 50,
                    'default' => "'custom'",
                    'description' => 'Item category (jpc, custom, etc.)'
                ),
                'description' => array(
                    'type' => 'TEXT',
                    'null' => true,
                    'description' => 'Optional description'
                ),
                'url' => array(
                    'type' => 'VARCHAR',
                    'length' => 500,
                    'null' => true,
                    'description' => 'Optional URL for the practice item'
                ),
                'is_active' => array(
                    'type' => 'TINYINT',
                    'length' => 1,
                    'default' => 1,
                    'description' => 'Whether item is active (0/1)'
                ),
                'sort_order' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'default' => 0,
                    'description' => 'Sort order for display'
                ),
                'created_at' => array(
                    'type' => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                    'description' => 'When item was created'
                ),
                'updated_at' => array(
                    'type' => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                    'description' => 'When item was last updated'
                )
            ),
            'indexes' => array(
                'user_id' => array('user_id'),
                'category' => array('category'),
                'user_category' => array('user_id', 'category')
            ),
            'constraints' => array(
                'unique_user_name' => 'UNIQUE KEY unique_user_name (user_id, name)'
            )
        );
    }
    
    /**
     * Practice Sessions Table Schema
     * 
     * Logs each practice session with performance data:
     * - Duration, sentiment score, improvement detection
     * - Notes and AI analysis results
     */
    private static function get_practice_sessions_schema() {
        return array(
            'table_name' => 'jph_practice_sessions',
            'columns' => array(
                'id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                    'primary_key' => true,
                    'description' => 'Unique session ID'
                ),
                'user_id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'not_null' => true,
                    'description' => 'WordPress user ID'
                ),
                'practice_item_id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'not_null' => true,
                    'description' => 'Reference to practice item'
                ),
                'duration_minutes' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'unsigned' => true,
                    'not_null' => true,
                    'description' => 'Practice duration in minutes'
                ),
                'sentiment_score' => array(
                    'type' => 'TINYINT',
                    'length' => 1,
                    'unsigned' => true,
                    'not_null' => true,
                    'description' => 'How well they did (1-5 scale)'
                ),
                'improvement_detected' => array(
                    'type' => 'TINYINT',
                    'length' => 1,
                    'default' => 0,
                    'description' => 'Did they improve? (0/1)'
                ),
                'notes' => array(
                    'type' => 'TEXT',
                    'null' => true,
                    'description' => 'Practice notes from student'
                ),
                'ai_analysis' => array(
                    'type' => 'TEXT',
                    'null' => true,
                    'description' => 'AI analysis results (JSON)'
                ),
                'xp_earned' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'default' => 0,
                    'description' => 'XP earned for this session'
                ),
                'session_hash' => array(
                    'type' => 'VARCHAR',
                    'length' => 64,
                    'not_null' => true,
                    'description' => 'Hash to prevent duplicate sessions'
                ),
                'created_at' => array(
                    'type' => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                    'description' => 'When session was logged'
                )
            ),
            'indexes' => array(
                'user_id' => array('user_id'),
                'practice_item_id' => array('practice_item_id'),
                'created_at' => array('created_at'),
                'user_date' => array('user_id', 'created_at'),
                'session_hash' => array('session_hash')
            ),
            'constraints' => array(
                'unique_session_hash' => 'UNIQUE KEY unique_session_hash (session_hash)',
                'foreign_key_item' => 'FOREIGN KEY (practice_item_id) REFERENCES jph_practice_items(id) ON DELETE CASCADE'
            )
        );
    }
    
    /**
     * User Stats Table Schema
     * 
     * Stores gamification data for each user:
     * - XP, level, streak, virtual currency
     * - Performance metrics and achievements
     * - Leaderboard display settings
     */
    private static function get_user_stats_schema() {
        return array(
            'table_name' => 'jph_user_stats',
            'columns' => array(
                'id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                    'primary_key' => true,
                    'description' => 'Unique stats record ID'
                ),
                'user_id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'not_null' => true,
                    'unique' => true,
                    'description' => 'WordPress user ID (one record per user)'
                ),
                'total_xp' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'description' => 'Total experience points earned'
                ),
                'current_level' => array(
                    'type' => 'TINYINT',
                    'length' => 3,
                    'unsigned' => true,
                    'default' => 1,
                    'description' => 'Current user level'
                ),
                'current_streak' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'description' => 'Current practice streak (days)'
                ),
                'longest_streak' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'description' => 'Longest streak achieved'
                ),
                'total_sessions' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'description' => 'Total practice sessions logged'
                ),
                'total_minutes' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'description' => 'Total practice minutes'
                ),
                'hearts_count' => array(
                    'type' => 'TINYINT',
                    'length' => 3,
                    'unsigned' => true,
                    'default' => 5,
                    'description' => 'Hearts currency (Duolingo-style)'
                ),
                'gems_balance' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'description' => 'Gems currency balance'
                ),
                'streak_shield_count' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'description' => 'Number of active streak shields'
                ),
                'last_streak_recovery_date' => array(
                    'type' => 'DATE',
                    'null' => true,
                    'description' => 'Last date streak recovery was used'
                ),
                'streak_recovery_count_this_week' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'description' => 'Number of streak recoveries used this week'
                ),
                'badges_earned' => array(
                    'type' => 'TINYINT',
                    'length' => 3,
                    'unsigned' => true,
                    'default' => 0,
                    'description' => 'Total badges earned'
                ),
                'last_practice_date' => array(
                    'type' => 'DATE',
                    'null' => true,
                    'description' => 'Last practice session date'
                ),
                'display_name' => array(
                    'type' => 'VARCHAR',
                    'length' => 100,
                    'null' => true,
                    'description' => 'Custom display name for leaderboard'
                ),
                'show_on_leaderboard' => array(
                    'type' => 'TINYINT',
                    'length' => 1,
                    'default' => 1,
                    'description' => 'Whether user appears on leaderboard (0/1)'
                ),
                'created_at' => array(
                    'type' => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                    'description' => 'When stats record was created'
                ),
                'updated_at' => array(
                    'type' => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                    'description' => 'When stats were last updated'
                )
            ),
            'indexes' => array(
                'total_xp' => array('total_xp'),
                'current_level' => array('current_level'),
                'current_streak' => array('current_streak'),
                'show_on_leaderboard' => array('show_on_leaderboard'),
                'leaderboard_sort' => array('show_on_leaderboard', 'total_xp')
            )
        );
    }
    
    /**
     * Badges Table Schema
     * 
     * Defines available badges in the system:
     * - Badge metadata, images, and requirements
     * - Admin-managed badge definitions
     */
    private static function get_badges_schema() {
        return array(
            'table_name' => 'jph_badges',
            'columns' => array(
                'badge_key' => array(
                    'type' => 'VARCHAR',
                    'length' => 50,
                    'not_null' => true,
                    'primary_key' => true,
                    'description' => 'Unique badge identifier (primary key)'
                ),
                'name' => array(
                    'type' => 'VARCHAR',
                    'length' => 100,
                    'not_null' => true,
                    'description' => 'Badge display name'
                ),
                'description' => array(
                    'type' => 'TEXT',
                    'null' => true,
                    'description' => 'Badge description'
                ),
                'image_url' => array(
                    'type' => 'VARCHAR',
                    'length' => 500,
                    'null' => true,
                    'description' => 'Badge image URL'
                ),
                'category' => array(
                    'type' => 'VARCHAR',
                    'length' => 50,
                    'default' => "'achievement'",
                    'description' => 'Badge category (achievement, milestone, special)'
                ),
                'criteria_type' => array(
                    'type' => 'VARCHAR',
                    'length' => 50,
                    'default' => "'manual'",
                    'description' => 'How badge is earned (manual, xp, streak, sessions)'
                ),
                'criteria_value' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'default' => 0,
                    'description' => 'Value needed to earn badge (XP, streak days, etc.)'
                ),
                'xp_reward' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'description' => 'XP reward for earning this badge'
                ),
                'gem_reward' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'description' => 'Gem reward for earning this badge'
                ),
                'is_active' => array(
                    'type' => 'TINYINT',
                    'length' => 1,
                    'default' => 1,
                    'description' => 'Whether badge is active (0/1)'
                ),
                'display_order' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'default' => 0,
                    'description' => 'Display order for dashboard (lower numbers first)'
                ),
                'created_at' => array(
                    'type' => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                    'description' => 'When badge was created'
                ),
                'updated_at' => array(
                    'type' => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                    'description' => 'When badge was last updated'
                ),
                'fluentcrm_enabled' => array(
                    'type' => 'TINYINT',
                    'length' => 1,
                    'default' => 0,
                    'description' => 'Whether FluentCRM event tracking is enabled (0/1)'
                ),
                'fluentcrm_event_key' => array(
                    'type' => 'VARCHAR',
                    'length' => 100,
                    'null' => true,
                    'description' => 'FluentCRM event key for this badge'
                ),
                'fluentcrm_event_title' => array(
                    'type' => 'VARCHAR',
                    'length' => 255,
                    'null' => true,
                    'description' => 'FluentCRM event title for this badge'
                )
            ),
            'indexes' => array(
                'badge_key' => array('badge_key'),
                'category' => array('category'),
                'is_active' => array('is_active'),
                'display_order' => array('display_order')
            )
        );
    }
    
    /**
     * User Badges Table Schema
     * 
     * Tracks which badges each user has earned:
     * - Many-to-many relationship between users and badges
     * - Includes earned date and badge metadata
     */
    private static function get_user_badges_schema() {
        return array(
            'table_name' => 'jph_user_badges',
            'columns' => array(
                'id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                    'primary_key' => true,
                    'description' => 'Unique user badge ID'
                ),
                'user_id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'not_null' => true,
                    'description' => 'WordPress user ID'
                ),
                'badge_key' => array(
                    'type' => 'VARCHAR',
                    'length' => 50,
                    'not_null' => true,
                    'description' => 'Badge identifier (foreign key to jph_badges.badge_key)'
                ),
                'earned_at' => array(
                    'type' => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                    'description' => 'When badge was earned'
                ),
                'earned_date' => array(
                    'type' => 'DATE',
                    'default' => 'CURRENT_DATE',
                    'description' => 'Date badge was earned (for easier querying)'
                )
            ),
            'indexes' => array(
                'user_id' => array('user_id'),
                'badge_key' => array('badge_key'),
                'earned_at' => array('earned_at'),
                'user_badge' => array('user_id', 'badge_key')
            ),
            'constraints' => array(
                'unique_user_badge' => 'UNIQUE KEY unique_user_badge (user_id, badge_key)'
            )
        );
    }
    
    /**
     * Lesson Favorites Table Schema
     * 
     * Stores user's favorite lessons with metadata:
     * - Title, URL, category, description
     * - User-specific favorites
     */
    private static function get_lesson_favorites_schema() {
        return array(
            'table_name' => 'jph_lesson_favorites',
            'columns' => array(
                'id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                    'primary_key' => true,
                    'description' => 'Unique lesson favorite ID'
                ),
                'user_id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'not_null' => true,
                    'description' => 'WordPress user ID'
                ),
                'title' => array(
                    'type' => 'VARCHAR',
                    'length' => 255,
                    'not_null' => true,
                    'description' => 'Lesson title'
                ),
                'url' => array(
                    'type' => 'VARCHAR',
                    'length' => 500,
                    'not_null' => true,
                    'description' => 'Lesson URL'
                ),
                'category' => array(
                    'type' => 'VARCHAR',
                    'length' => 50,
                    'default' => "'lesson'",
                    'description' => 'Lesson category (lesson, technique, theory, etc.)'
                ),
                'description' => array(
                    'type' => 'TEXT',
                    'null' => true,
                    'description' => 'Optional lesson description'
                ),
                'created_at' => array(
                    'type' => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                    'description' => 'When favorite was added'
                ),
                'updated_at' => array(
                    'type' => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                    'description' => 'When favorite was last updated'
                )
            ),
            'indexes' => array(
                'user_id' => array('user_id'),
                'category' => array('category'),
                'user_category' => array('user_id', 'category'),
                'created_at' => array('created_at')
            ),
            'constraints' => array(
                'unique_user_title' => 'UNIQUE KEY unique_user_title (user_id, title)'
            )
        );
    }
    
    /**
     * Gems Transactions Table Schema
     * 
     * Tracks all gem transactions for audit and history:
     * - Earned from badges, practice sessions, etc.
     * - Spent on items, features, etc.
     */
    private static function get_gems_transactions_schema() {
        return array(
            'table_name' => 'jph_gems_transactions',
            'columns' => array(
                'id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                    'primary_key' => true,
                    'description' => 'Unique transaction ID'
                ),
                'user_id' => array(
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'not_null' => true,
                    'description' => 'WordPress user ID'
                ),
                'transaction_type' => array(
                    'type' => 'VARCHAR',
                    'length' => 50,
                    'not_null' => true,
                    'description' => 'Type of transaction (earned, spent, bonus, etc.)'
                ),
                'amount' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'not_null' => true,
                    'description' => 'Amount of gems (positive for earned, negative for spent)'
                ),
                'source' => array(
                    'type' => 'VARCHAR',
                    'length' => 100,
                    'not_null' => true,
                    'description' => 'Source of transaction (badge_id, practice_session, manual, etc.)'
                ),
                'description' => array(
                    'type' => 'TEXT',
                    'null' => true,
                    'description' => 'Human-readable description of the transaction'
                ),
                'balance_after' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'not_null' => true,
                    'description' => 'User\'s gem balance after this transaction'
                ),
                'created_at' => array(
                    'type' => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                    'description' => 'When transaction occurred'
                )
            ),
            'indexes' => array(
                'user_id' => array('user_id'),
                'transaction_type' => array('transaction_type'),
                'source' => array('source'),
                'created_at' => array('created_at'),
                'user_type' => array('user_id', 'transaction_type')
            )
        );
    }
    
    /**
     * Get SQL CREATE TABLE statements
     */
    public static function get_create_statements() {
        $schema = self::get_schema();
        $statements = array();
        
        foreach ($schema as $table_name => $table_schema) {
            $statements[$table_name] = self::build_create_statement($table_schema);
        }
        
        return $statements;
    }
    
    /**
     * Build CREATE TABLE statement for a table
     */
    private static function build_create_statement($table_schema) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_schema['table_name'];
        $columns = $table_schema['columns'];
        $indexes = isset($table_schema['indexes']) ? $table_schema['indexes'] : array();
        $constraints = isset($table_schema['constraints']) ? $table_schema['constraints'] : array();
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (\n";
        
        // Add columns
        $column_definitions = array();
        foreach ($columns as $column_name => $column_def) {
            $column_definitions[] = self::build_column_definition($column_name, $column_def);
        }
        
        // Add indexes
        foreach ($indexes as $index_name => $index_columns) {
            $column_list = '`' . implode('`, `', $index_columns) . '`';
            $column_definitions[] = "KEY `{$index_name}` ({$column_list})";
        }
        
        // Add constraints
        foreach ($constraints as $constraint_name => $constraint_def) {
            $column_definitions[] = $constraint_def;
        }
        
        $sql .= "  " . implode(",\n  ", $column_definitions) . "\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        return $sql;
    }
    
    /**
     * Build column definition
     */
    private static function build_column_definition($column_name, $column_def) {
        $definition = "`{$column_name}` ";
        
        // Type and length
        $definition .= $column_def['type'];
        if (isset($column_def['length'])) {
            $definition .= "({$column_def['length']})";
        }
        
        // Unsigned
        if (isset($column_def['unsigned']) && $column_def['unsigned']) {
            $definition .= " UNSIGNED";
        }
        
        // Auto increment
        if (isset($column_def['auto_increment']) && $column_def['auto_increment']) {
            $definition .= " AUTO_INCREMENT";
        }
        
        // Not null
        if (isset($column_def['not_null']) && $column_def['not_null']) {
            $definition .= " NOT NULL";
        }
        
        // Default value
        if (isset($column_def['default'])) {
            $definition .= " DEFAULT " . $column_def['default'];
        }
        
        // Primary key
        if (isset($column_def['primary_key']) && $column_def['primary_key']) {
            $definition .= " PRIMARY KEY";
        }
        
        // Unique
        if (isset($column_def['unique']) && $column_def['unique']) {
            $definition .= " UNIQUE";
        }
        
        return $definition;
    }
    
    /**
     * Create all tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $create_statements = self::get_create_statements();
        
        foreach ($create_statements as $table_name => $sql) {
            $result = $wpdb->query($sql);
            if ($result === false) {
                error_log("Failed to create table {$table_name}: " . $wpdb->last_error);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Add leaderboard columns to existing user_stats table
     */
    public static function add_leaderboard_columns() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        
        // Check if columns already exist
        $columns = $wpdb->get_col("DESCRIBE {$table_name}");
        
        $alter_statements = array();
        
        if (!in_array('display_name', $columns)) {
            $alter_statements[] = "ADD COLUMN `display_name` VARCHAR(100) NULL COMMENT 'Custom display name for leaderboard'";
        }
        
        if (!in_array('show_on_leaderboard', $columns)) {
            $alter_statements[] = "ADD COLUMN `show_on_leaderboard` TINYINT(1) DEFAULT 1 COMMENT 'Whether user appears on leaderboard (0/1)'";
        }
        
        if (!empty($alter_statements)) {
            $sql = "ALTER TABLE `{$table_name}` " . implode(', ', $alter_statements);
            $result = $wpdb->query($sql);
            
            if ($result === false) {
                error_log("Failed to add leaderboard columns: " . $wpdb->last_error);
                return false;
            }
            
            // Add indexes for leaderboard performance
            $indexes_to_add = array();
            
            if (!in_array('show_on_leaderboard', $columns)) {
                $indexes_to_add[] = "ADD INDEX `show_on_leaderboard` (`show_on_leaderboard`)";
            }
            
            // Check if leaderboard_sort index exists
            $indexes = $wpdb->get_results("SHOW INDEX FROM {$table_name}");
            $leaderboard_sort_exists = false;
            foreach ($indexes as $index) {
                if ($index->Key_name === 'leaderboard_sort') {
                    $leaderboard_sort_exists = true;
                    break;
                }
            }
            
            if (!$leaderboard_sort_exists) {
                $indexes_to_add[] = "ADD INDEX `leaderboard_sort` (`show_on_leaderboard`, `total_xp`)";
            }
            
            if (!empty($indexes_to_add)) {
                $index_sql = "ALTER TABLE `{$table_name}` " . implode(', ', $indexes_to_add);
                $index_result = $wpdb->query($index_sql);
                
                if ($index_result === false) {
                    error_log("Failed to add leaderboard indexes: " . $wpdb->last_error);
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Update badges table schema to use image_url instead of icon
     */
    public static function update_badges_schema() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_badges';
        
        // Check if image_url column exists
        $columns = $wpdb->get_col("DESCRIBE {$table_name}");
        
        $alter_statements = array();
        
        // Add image_url column if it doesn't exist
        if (!in_array('image_url', $columns)) {
            $alter_statements[] = "ADD COLUMN `image_url` VARCHAR(500) NULL COMMENT 'Badge image URL'";
        }
        
        // Remove icon column if it exists and image_url exists
        if (in_array('icon', $columns) && in_array('image_url', $columns)) {
            $alter_statements[] = "DROP COLUMN `icon`";
        }
        
        if (!empty($alter_statements)) {
            $sql = "ALTER TABLE `{$table_name}` " . implode(', ', $alter_statements);
            $result = $wpdb->query($sql);
            
            if ($result === false) {
                error_log("Failed to update badges schema: " . $wpdb->last_error);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Add additional performance indexes for all tables
     */
    public static function add_additional_indexes() {
        global $wpdb;
        
        $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
        $items_table = $wpdb->prefix . 'jph_practice_items';
        $badges_table = $wpdb->prefix . 'jph_user_badges';
        $favorites_table = $wpdb->prefix . 'jph_lesson_favorites';
        
        // Practice sessions indexes
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_sessions_user_date ON {$sessions_table} (user_id, created_at DESC)");
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_sessions_date ON {$sessions_table} (created_at)");
        
        // Practice items indexes
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_items_user_active ON {$items_table} (user_id, is_active)");
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_items_session_id ON {$items_table} (session_id)");
        
        // User badges indexes
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_user_badges_user_id ON {$badges_table} (user_id)");
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_user_badges_type ON {$badges_table} (badge_type)");
        
        // Lesson favorites indexes
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_favorites_user_id ON {$favorites_table} (user_id)");
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_favorites_title ON {$favorites_table} (title)");
        
        return true;
    }
}

/**
 * Example usage:
 * 
 * $schema = APH_Database_Schema::get_schema();
 * $create_statements = APH_Database_Schema::get_create_statements();
 * 
 * foreach ($create_statements as $table_name => $sql) {
 *     $wpdb->query($sql);
 * }
 */
