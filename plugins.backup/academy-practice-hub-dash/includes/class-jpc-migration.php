<?php
/**
 * JPC Migration Class
 * 
 * Handles comprehensive migration of JPC data from old tables to new Academy Practice Hub tables
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_JPC_Migration {
    
    /**
     * Migration statistics
     */
    private static $stats = array(
        'curriculum_items' => 0,
        'steps' => 0,
        'user_assignments' => 0,
        'user_progress' => 0,
        'milestone_submissions' => 0,
        'errors' => array()
    );
    
    /**
     * Perform complete JPC migration
     * 
     * @param bool $dry_run If true, only analyze without making changes
     * @param array $options Migration options
     * @return array Migration results and statistics
     */
    public static function migrate_all_jpc_data($dry_run = false, $options = array()) {
        global $wpdb;
        
        $default_options = array(
            'batch_size' => 100,
            'skip_existing' => true,
            'include_milestones' => true,
            'log_level' => 'info'
        );
        
        $options = array_merge($default_options, $options);
        
        self::log("Starting JPC migration" . ($dry_run ? " (DRY RUN)" : ""), 'info');
        
        try {
            // Step 1: Migrate curriculum data
            self::log("Step 1: Migrating curriculum data...", 'info');
            $curriculum_result = self::migrate_curriculum_data($dry_run, $options);
            
            // Step 2: Migrate steps data
            self::log("Step 2: Migrating steps data...", 'info');
            $steps_result = self::migrate_steps_data($dry_run, $options);
            
            // Step 3: Migrate user assignments
            self::log("Step 3: Migrating user assignments...", 'info');
            $assignments_result = self::migrate_user_assignments($dry_run, $options);
            
            // Step 4: Migrate user progress
            self::log("Step 4: Migrating user progress...", 'info');
            $progress_result = self::migrate_user_progress($dry_run, $options);
            
            // Step 5: Migrate milestone submissions (optional)
            if ($options['include_milestones']) {
                self::log("Step 5: Migrating milestone submissions...", 'info');
                $milestones_result = self::migrate_milestone_submissions($dry_run, $options);
            }
            
            // Step 6: Create practice items for JPC
            self::log("Step 6: Creating JPC practice items for users...", 'info');
            $practice_items_result = self::create_jpc_practice_items($dry_run, $options);
            
            self::log("Migration completed successfully!", 'success');
            
            return array(
                'success' => true,
                'dry_run' => $dry_run,
                'stats' => self::$stats,
                'results' => array(
                    'curriculum' => $curriculum_result,
                    'steps' => $steps_result,
                    'assignments' => $assignments_result,
                    'progress' => $progress_result,
                    'milestones' => isset($milestones_result) ? $milestones_result : null,
                    'practice_items' => $practice_items_result
                )
            );
            
        } catch (Exception $e) {
            self::log("Migration failed: " . $e->getMessage(), 'error');
            self::$stats['errors'][] = $e->getMessage();
            
            return array(
                'success' => false,
                'error' => $e->getMessage(),
                'stats' => self::$stats
            );
        }
    }
    
    /**
     * Migrate curriculum data from je_practice_curriculum to jph_jpc_curriculum
     */
    private static function migrate_curriculum_data($dry_run = false, $options = array()) {
        global $wpdb;
        
        $old_table = 'je_practice_curriculum';
        $new_table = $wpdb->prefix . 'jph_jpc_curriculum';
        
        // Check if old table exists
        if (!$wpdb->get_var("SHOW TABLES LIKE '$old_table'")) {
            self::log("Old curriculum table '$old_table' not found", 'warning');
            return array('skipped' => true, 'reason' => 'Table not found');
        }
        
        // Get all curriculum items
        $curriculum_items = $wpdb->get_results("SELECT * FROM $old_table ORDER BY ID ASC", ARRAY_A);
        
        if (empty($curriculum_items)) {
            self::log("No curriculum items found in old table", 'warning');
            return array('skipped' => true, 'reason' => 'No data found');
        }
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($curriculum_items as $item) {
            if ($options['skip_existing']) {
                // Check if already exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $new_table WHERE id = %d",
                    $item['ID']
                ));
                
                if ($exists) {
                    $skipped++;
                    continue;
                }
            }
            
            if (!$dry_run) {
                $result = $wpdb->replace(
                    $new_table,
                    array(
                        'id' => $item['ID'],
                        'focus_order' => $item['focus_order'],
                        'focus_title' => $item['focus_title'],
                        'focus_pillar' => $item['focus_pillar'],
                        'focus_element' => $item['focus_element'],
                        'tempo' => $item['tempo'],
                        'resource_pdf' => $item['resource_pdf'],
                        'resource_ireal' => $item['resource_ireal'],
                        'resource_mp3' => $item['resource_mp3'],
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ),
                    array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s')
                );
                
                if ($result === false) {
                    self::log("Failed to migrate curriculum item ID {$item['ID']}: " . $wpdb->last_error, 'error');
                    self::$stats['errors'][] = "Curriculum ID {$item['ID']}: " . $wpdb->last_error;
                } else {
                    $migrated++;
                }
            } else {
                $migrated++;
            }
        }
        
        self::$stats['curriculum_items'] = $migrated;
        
        self::log("Curriculum migration: $migrated migrated, $skipped skipped", 'info');
        
        return array(
            'total' => count($curriculum_items),
            'migrated' => $migrated,
            'skipped' => $skipped
        );
    }
    
    /**
     * Migrate steps data from je_practice_curriculum_steps to jph_jpc_steps
     */
    private static function migrate_steps_data($dry_run = false, $options = array()) {
        global $wpdb;
        
        $old_table = 'je_practice_curriculum_steps';
        $new_table = $wpdb->prefix . 'jph_jpc_steps';
        
        // Check if old table exists
        if (!$wpdb->get_var("SHOW TABLES LIKE '$old_table'")) {
            self::log("Old steps table '$old_table' not found", 'warning');
            return array('skipped' => true, 'reason' => 'Table not found');
        }
        
        // Get all steps
        $steps = $wpdb->get_results("SELECT * FROM $old_table ORDER BY step_id ASC", ARRAY_A);
        
        if (empty($steps)) {
            self::log("No steps found in old table", 'warning');
            return array('skipped' => true, 'reason' => 'No data found');
        }
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($steps as $step) {
            if ($options['skip_existing']) {
                // Check if already exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $new_table WHERE step_id = %d",
                    $step['step_id']
                ));
                
                if ($exists) {
                    $skipped++;
                    continue;
                }
            }
            
            if (!$dry_run) {
                $result = $wpdb->replace(
                    $new_table,
                    array(
                        'step_id' => $step['step_id'],
                        'curriculum_id' => $step['curriculum_id'],
                        'key_sig' => $step['key_sig'],
                        'key_sig_name' => $step['key_sig_name'],
                        'vimeo_id' => $step['vimeo_id'],
                        'resource' => $step['resource'],
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ),
                    array('%d', '%d', '%d', '%s', '%d', '%s', '%s', '%s')
                );
                
                if ($result === false) {
                    self::log("Failed to migrate step ID {$step['step_id']}: " . $wpdb->last_error, 'error');
                    self::$stats['errors'][] = "Step ID {$step['step_id']}: " . $wpdb->last_error;
                } else {
                    $migrated++;
                }
            } else {
                $migrated++;
            }
        }
        
        self::$stats['steps'] = $migrated;
        
        self::log("Steps migration: $migrated migrated, $skipped skipped", 'info');
        
        return array(
            'total' => count($steps),
            'migrated' => $migrated,
            'skipped' => $skipped
        );
    }
    
    /**
     * Migrate user assignments from je_practice_curriculum_assignments to jph_jpc_user_assignments
     */
    private static function migrate_user_assignments($dry_run = false, $options = array()) {
        global $wpdb;
        
        $old_table = 'je_practice_curriculum_assignments';
        $new_table = $wpdb->prefix . 'jph_jpc_user_assignments';
        
        // Check if old table exists
        if (!$wpdb->get_var("SHOW TABLES LIKE '$old_table'")) {
            self::log("Old assignments table '$old_table' not found", 'warning');
            return array('skipped' => true, 'reason' => 'Table not found');
        }
        
        // Get all assignments (only non-deleted ones)
        $assignments = $wpdb->get_results(
            "SELECT * FROM $old_table WHERE deleted_at IS NULL ORDER BY user_id, ID DESC",
            ARRAY_A
        );
        
        if (empty($assignments)) {
            self::log("No assignments found in old table", 'warning');
            return array('skipped' => true, 'reason' => 'No data found');
        }
        
        $migrated = 0;
        $skipped = 0;
        $processed_users = array();
        
        foreach ($assignments as $assignment) {
            // Only process the latest assignment per user
            if (in_array($assignment['user_id'], $processed_users)) {
                $skipped++;
                continue;
            }
            
            $processed_users[] = $assignment['user_id'];
            
            if ($options['skip_existing']) {
                // Check if already exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $new_table WHERE user_id = %d",
                    $assignment['user_id']
                ));
                
                if ($exists) {
                    $skipped++;
                    continue;
                }
            }
            
            if (!$dry_run) {
                // Determine the current step_id (first non-null step)
                $step_id = null;
                for ($i = 1; $i <= 3; $i++) {
                    if (!empty($assignment["step_id_$i"])) {
                        $step_id = $assignment["step_id_$i"];
                        break;
                    }
                }
                
                if ($step_id) {
                    // Get curriculum_id from step
                    $curriculum_id = $wpdb->get_var($wpdb->prepare(
                        "SELECT curriculum_id FROM {$wpdb->prefix}jph_jpc_steps WHERE step_id = %d",
                        $step_id
                    ));
                    
                    if ($curriculum_id) {
                        $result = $wpdb->replace(
                            $new_table,
                            array(
                                'user_id' => $assignment['user_id'],
                                'step_id' => $step_id,
                                'curriculum_id' => $curriculum_id,
                                'assigned_date' => $assignment['date'],
                                'completed_on' => null,
                                'deleted_at' => null
                            ),
                            array('%d', '%d', '%d', '%s', '%s', '%s')
                        );
                        
                        if ($result === false) {
                            self::log("Failed to migrate assignment for user {$assignment['user_id']}: " . $wpdb->last_error, 'error');
                            self::$stats['errors'][] = "Assignment user {$assignment['user_id']}: " . $wpdb->last_error;
                        } else {
                            $migrated++;
                        }
                    } else {
                        self::log("Could not find curriculum_id for step $step_id", 'warning');
                        $skipped++;
                    }
                } else {
                    self::log("No valid step_id found for assignment {$assignment['ID']}", 'warning');
                    $skipped++;
                }
            } else {
                $migrated++;
            }
        }
        
        self::$stats['user_assignments'] = $migrated;
        
        self::log("Assignments migration: $migrated migrated, $skipped skipped", 'info');
        
        return array(
            'total' => count($assignments),
            'migrated' => $migrated,
            'skipped' => $skipped
        );
    }
    
    /**
     * Migrate user progress from jpc_student_progress to jph_jpc_user_progress
     */
    private static function migrate_user_progress($dry_run = false, $options = array()) {
        global $wpdb;
        
        $old_table = 'jpc_student_progress';
        $new_table = $wpdb->prefix . 'jph_jpc_user_progress';
        
        // Check if old table exists
        if (!$wpdb->get_var("SHOW TABLES LIKE '$old_table'")) {
            self::log("Old progress table '$old_table' not found", 'warning');
            return array('skipped' => true, 'reason' => 'Table not found');
        }
        
        // Get all progress records
        $progress_records = $wpdb->get_results("SELECT * FROM $old_table ORDER BY user_id, curriculum_id", ARRAY_A);
        
        if (empty($progress_records)) {
            self::log("No progress records found in old table", 'warning');
            return array('skipped' => true, 'reason' => 'No data found');
        }
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($progress_records as $progress) {
            if ($options['skip_existing']) {
                // Check if already exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $new_table WHERE user_id = %d AND curriculum_id = %d",
                    $progress['user_id'], $progress['curriculum_id']
                ));
                
                if ($exists) {
                    $skipped++;
                    continue;
                }
            }
            
            if (!$dry_run) {
                $result = $wpdb->replace(
                    $new_table,
                    array(
                        'user_id' => $progress['user_id'],
                        'curriculum_id' => $progress['curriculum_id'],
                        'step_1' => $progress['step_1'],
                        'step_2' => $progress['step_2'],
                        'step_3' => $progress['step_3'],
                        'step_4' => $progress['step_4'],
                        'step_5' => $progress['step_5'],
                        'step_6' => $progress['step_6'],
                        'step_7' => $progress['step_7'],
                        'step_8' => $progress['step_8'],
                        'step_9' => $progress['step_9'],
                        'step_10' => $progress['step_10'],
                        'step_11' => $progress['step_11'],
                        'step_12' => $progress['step_12'],
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ),
                    array('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s')
                );
                
                if ($result === false) {
                    self::log("Failed to migrate progress for user {$progress['user_id']}, curriculum {$progress['curriculum_id']}: " . $wpdb->last_error, 'error');
                    self::$stats['errors'][] = "Progress user {$progress['user_id']}, curriculum {$progress['curriculum_id']}: " . $wpdb->last_error;
                } else {
                    $migrated++;
                }
            } else {
                $migrated++;
            }
        }
        
        self::$stats['user_progress'] = $migrated;
        
        self::log("Progress migration: $migrated migrated, $skipped skipped", 'info');
        
        return array(
            'total' => count($progress_records),
            'migrated' => $migrated,
            'skipped' => $skipped
        );
    }
    
    /**
     * Migrate milestone submissions from je_practice_milestone_submissions to jph_jpc_milestone_submissions
     */
    private static function migrate_milestone_submissions($dry_run = false, $options = array()) {
        global $wpdb;
        
        $old_table = 'je_practice_milestone_submissions';
        $new_table = $wpdb->prefix . 'jph_jpc_milestone_submissions';
        
        // Check if old table exists
        if (!$wpdb->get_var("SHOW TABLES LIKE '$old_table'")) {
            self::log("Old milestone submissions table '$old_table' not found", 'warning');
            return array('skipped' => true, 'reason' => 'Table not found');
        }
        
        // Get all milestone submissions
        $submissions = $wpdb->get_results("SELECT * FROM $old_table ORDER BY ID ASC", ARRAY_A);
        
        if (empty($submissions)) {
            self::log("No milestone submissions found in old table", 'warning');
            return array('skipped' => true, 'reason' => 'No data found');
        }
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($submissions as $submission) {
            if ($options['skip_existing']) {
                // Check if already exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $new_table WHERE user_id = %d AND curriculum_id = %d",
                    $submission['user_id'], $submission['curriculum_id']
                ));
                
                if ($exists) {
                    $skipped++;
                    continue;
                }
            }
            
            if (!$dry_run) {
                $result = $wpdb->replace(
                    $new_table,
                    array(
                        'user_id' => $submission['user_id'],
                        'curriculum_id' => $submission['curriculum_id'],
                        'video_url' => $submission['video_url'],
                        'submission_date' => $submission['submission_date'],
                        'grade' => $submission['grade'],
                        'graded_on' => $submission['graded_on'],
                        'teacher_notes' => $submission['feedback'],
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ),
                    array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                );
                
                if ($result === false) {
                    self::log("Failed to migrate milestone submission for user {$submission['user_id']}, curriculum {$submission['curriculum_id']}: " . $wpdb->last_error, 'error');
                    self::$stats['errors'][] = "Milestone user {$submission['user_id']}, curriculum {$submission['curriculum_id']}: " . $wpdb->last_error;
                } else {
                    $migrated++;
                }
            } else {
                $migrated++;
            }
        }
        
        self::$stats['milestone_submissions'] = $migrated;
        
        self::log("Milestone submissions migration: $migrated migrated, $skipped skipped", 'info');
        
        return array(
            'total' => count($submissions),
            'migrated' => $migrated,
            'skipped' => $skipped
        );
    }
    
    /**
     * Create JPC practice items for users who have JPC data
     */
    private static function create_jpc_practice_items($dry_run = false, $options = array()) {
        global $wpdb;
        
        $practice_items_table = $wpdb->prefix . 'jph_practice_items';
        $user_progress_table = $wpdb->prefix . 'jph_jpc_user_progress';
        
        // Get all users who have JPC progress
        $users_with_jpc = $wpdb->get_results(
            "SELECT DISTINCT user_id FROM $user_progress_table",
            ARRAY_A
        );
        
        if (empty($users_with_jpc)) {
            self::log("No users with JPC progress found", 'warning');
            return array('skipped' => true, 'reason' => 'No users found');
        }
        
        $created = 0;
        $skipped = 0;
        
        foreach ($users_with_jpc as $user_data) {
            $user_id = $user_data['user_id'];
            
            if ($options['skip_existing']) {
                // Check if JPC practice item already exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $practice_items_table WHERE user_id = %d AND category = 'jpc'",
                    $user_id
                ));
                
                if ($exists) {
                    $skipped++;
                    continue;
                }
            }
            
            if (!$dry_run) {
                $result = $wpdb->insert(
                    $practice_items_table,
                    array(
                        'user_id' => $user_id,
                        'name' => 'JazzEdge Practice Curriculum™',
                        'category' => 'jpc',
                        'description' => 'Complete jazz piano curriculum with 12 keys per focus',
                        'is_active' => 1,
                        'sort_order' => 1,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ),
                    array('%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
                );
                
                if ($result === false) {
                    self::log("Failed to create JPC practice item for user $user_id: " . $wpdb->last_error, 'error');
                    self::$stats['errors'][] = "Practice item user $user_id: " . $wpdb->last_error;
                } else {
                    $created++;
                }
            } else {
                $created++;
            }
        }
        
        self::log("JPC practice items: $created created, $skipped skipped", 'info');
        
        return array(
            'total_users' => count($users_with_jpc),
            'created' => $created,
            'skipped' => $skipped
        );
    }
    
    /**
     * Get migration statistics
     */
    public static function get_migration_stats() {
        return self::$stats;
    }
    
    /**
     * Reset migration statistics
     */
    public static function reset_stats() {
        self::$stats = array(
            'curriculum_items' => 0,
            'steps' => 0,
            'user_assignments' => 0,
            'user_progress' => 0,
            'milestone_submissions' => 0,
            'errors' => array()
        );
    }
    
    /**
     * Log migration messages
     */
    private static function log($message, $level = 'info') {
        $timestamp = current_time('Y-m-d H:i:s');
        $log_message = "[$timestamp] [$level] $message";
        
        // Log to WordPress error log
        error_log($log_message);
        
        // Also store in stats for return
        if ($level === 'error') {
            self::$stats['errors'][] = $message;
        }
    }
    
    /**
     * Validate migration prerequisites
     */
    public static function validate_prerequisites() {
        global $wpdb;
        
        $issues = array();
        
        // Check if new tables exist
        $required_tables = array(
            'jph_jpc_curriculum',
            'jph_jpc_steps', 
            'jph_jpc_user_assignments',
            'jph_jpc_user_progress',
            'jph_jpc_milestone_submissions',
            'jph_practice_items'
        );
        
        foreach ($required_tables as $table) {
            $full_table_name = $wpdb->prefix . $table;
            if (!$wpdb->get_var("SHOW TABLES LIKE '$full_table_name'")) {
                $issues[] = "Required table '$full_table_name' does not exist";
            }
        }
        
        // Check if old tables exist
        $old_tables = array(
            'je_practice_curriculum',
            'je_practice_curriculum_steps',
            'je_practice_curriculum_assignments',
            'jpc_student_progress',
            'je_practice_milestone_submissions'
        );
        
        $old_tables_found = 0;
        foreach ($old_tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'")) {
                $old_tables_found++;
            }
        }
        
        if ($old_tables_found === 0) {
            $issues[] = "No old JPC tables found - nothing to migrate";
        }
        
        return array(
            'valid' => empty($issues),
            'issues' => $issues,
            'old_tables_found' => $old_tables_found
        );
    }
}
