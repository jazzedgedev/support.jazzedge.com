<?php
/**
 * JPC Handler Class
 * 
 * Handles all Jazzedge Practice Curriculum™ operations for the Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_JPC_Handler {
    
    /**
     * Get user's current JPC assignment
     * 
     * @param int $user_id User ID
     * @return array|false Assignment data or false if none
     */
    public static function get_user_current_assignment($user_id) {
        global $wpdb;
        
        // Check Practice Hub JPC tables first
        $assignment = $wpdb->get_row($wpdb->prepare(
            "SELECT jua.*, jc.focus_title, jc.focus_order, jc.tempo, jc.resource_pdf, jc.resource_ireal, jc.resource_mp3,
                    js.key_sig, js.key_sig_name, js.vimeo_id, js.resource
             FROM {$wpdb->prefix}jph_jpc_user_assignments jua
             JOIN {$wpdb->prefix}jph_jpc_curriculum jc ON jua.curriculum_id = jc.id
             JOIN {$wpdb->prefix}jph_jpc_steps js ON jua.step_id = js.step_id
             WHERE jua.user_id = %d AND jua.deleted_at IS NULL
             ORDER BY jua.id DESC LIMIT 1",
            $user_id
        ), ARRAY_A);
        
        if ($assignment) {
            return $assignment;
        }
        
        // For testing: Skip original JPC data and create fresh assignment
        // TODO: Re-enable original JPC sync after testing
        /*
        $original_assignment = $wpdb->get_row($wpdb->prepare(
            "SELECT jpca.*, jpc.focus_title, jpc.focus_order, jpc.tempo, jpc.resource_pdf, jpc.resource_ireal, jpc.resource_mp3,
                    jpcs.key_sig, jpcs.key_sig_name, jpcs.vimeo_id, jpcs.resource
             FROM je_practice_curriculum_assignments jpca
             JOIN je_practice_curriculum_steps jpcs ON jpca.step_id = jpcs.step_id
             JOIN je_practice_curriculum jpc ON jpcs.curriculum_id = jpc.ID
             WHERE jpca.user_id = %d AND jpca.deleted_at IS NULL
             ORDER BY jpca.ID DESC LIMIT 1",
            $user_id
        ), ARRAY_A);
        
        if ($original_assignment) {
            // Sync to Practice Hub tables
            self::sync_user_assignment_to_hub($user_id, $original_assignment);
            return $original_assignment;
        }
        */
        
        // New user - create initial assignment
        error_log("JPC Handler: Creating initial assignment for user $user_id");
        $assignment = self::create_initial_assignment($user_id);
        if (!$assignment) {
            error_log("JPC Handler: Failed to create initial assignment for user $user_id");
            return false;
        }
        error_log("JPC Handler: Successfully created initial assignment: " . print_r($assignment, true));
        return $assignment;
    }
    
    /**
     * Get user's progress for a specific curriculum focus
     * 
     * @param int $user_id User ID
     * @param int $curriculum_id Curriculum focus ID
     * @return array Progress data with step_1 through step_12
     */
    public static function get_user_progress($user_id, $curriculum_id) {
        global $wpdb;
        
        // Check Practice Hub JPC tables first
        $progress = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}jph_jpc_user_progress 
             WHERE user_id = %d AND curriculum_id = %d",
            $user_id, $curriculum_id
        ), ARRAY_A);
        
        if ($progress) {
            return $progress;
        }
        
        // NO FALLBACK - Only use Practice Hub tables
        
        // No progress yet - return empty structure
        return array(
            'user_id' => $user_id,
            'curriculum_id' => $curriculum_id,
            'step_1' => null,
            'step_2' => null,
            'step_3' => null,
            'step_4' => null,
            'step_5' => null,
            'step_6' => null,
            'step_7' => null,
            'step_8' => null,
            'step_9' => null,
            'step_10' => null,
            'step_11' => null,
            'step_12' => null
        );
    }
    
    /**
     * Get curriculum details
     * 
     * @param int $curriculum_id Curriculum focus ID
     * @return array|false Curriculum data or false if not found
     */
    public static function get_curriculum_details($curriculum_id) {
        global $wpdb;
        
        // ALWAYS read from Practice Hub JPC curriculum table
        $curriculum = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}jph_jpc_curriculum WHERE id = %d",
            $curriculum_id
        ), ARRAY_A);
        
        return $curriculum;
    }
    
    /**
     * Get step details by curriculum and key
     * 
     * @param int $curriculum_id Curriculum focus ID
     * @param int $key_number Key number (1-12)
     * @return array|false Step data or false if not found
     */
    public static function get_step_details_by_curriculum_and_key($curriculum_id, $key_number) {
        global $wpdb;
        
        // ALWAYS read from Practice Hub JPC steps table
        $step = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}jph_jpc_steps 
             WHERE curriculum_id = %d AND key_sig = %d",
            $curriculum_id, $key_number
        ), ARRAY_A);
        
        return $step;
    }
    
    /**
     * Get step details
     * 
     * @param int $step_id Step ID
     * @return array|false Step data or false if not found
     */
    public static function get_step_details($step_id) {
        global $wpdb;
        
        // ALWAYS read from Practice Hub JPC steps table
        $step = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}jph_jpc_steps WHERE step_id = %d",
            $step_id
        ), ARRAY_A);
        
        return $step;
    }
    
    /**
     * Mark a step as complete and award XP/gems
     * 
     * @param int $user_id User ID
     * @param int $step_id Step ID
     * @param int $curriculum_id Curriculum focus ID
     * @return array Result with success status and rewards
     */
    public static function mark_step_complete($user_id, $step_id, $curriculum_id) {
        global $wpdb;
        
        // Get step details to determine which key this is
        $step = self::get_step_details($step_id);
        if (!$step) {
            return array('success' => false, 'message' => 'Step not found');
        }
        
        // Find the step sequence number (1-12) within this curriculum
        $step_sequence = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}jph_jpc_steps 
             WHERE curriculum_id = %d AND step_id <= %d",
            $curriculum_id, $step_id
        ));
        
        $key_number = $step_sequence; // 1-12 (step sequence, not key_sig)
        
        error_log("JPC Debug: step_id=$step_id, curriculum_id=$curriculum_id, step_sequence=$step_sequence, key_number=$key_number");
        
        // Get current progress
        $progress = self::get_user_progress($user_id, $curriculum_id);
        
        // Check if already completed
        $step_column = 'step_' . $key_number;
        if (!empty($progress[$step_column])) {
            return array('success' => false, 'message' => 'Step already completed');
        }
        
        // Mark step complete in Practice Hub tables
        if (empty($progress['id'])) {
            // Create new progress record
            $wpdb->insert(
                $wpdb->prefix . 'jph_jpc_user_progress',
                array(
                    'user_id' => $user_id,
                    'curriculum_id' => $curriculum_id,
                    $step_column => $step_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%d', '%d', '%d', '%s', '%s')
            );
        } else {
            // Update existing progress record
            $wpdb->update(
                $wpdb->prefix . 'jph_jpc_user_progress',
                array(
                    $step_column => $step_id,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $progress['id']),
                array('%d', '%s'),
                array('%d')
            );
        }
        
        // Award XP (25 per key completion)
        $xp_earned = 25;
        $gems_earned = 0;
        
        // Check if all 12 keys are now complete
        $updated_progress = self::get_user_progress($user_id, $curriculum_id);
        $completed_keys = 0;
        for ($i = 1; $i <= 12; $i++) {
            if (!empty($updated_progress['step_' . $i])) {
                $completed_keys++;
            }
        }
        
        // Award gems if all 12 keys completed
        if ($completed_keys === 12) {
            $gems_earned = 50;
        }
        
        // Award rewards through gamification system
        if (class_exists('APH_Gamification')) {
            error_log("JPCXP: Calling gamification system with user_id=$user_id, step_id=$step_id, curriculum_id=$curriculum_id, completed_keys=$completed_keys, xp_earned=$xp_earned, gems_earned=$gems_earned");
            $gamification_result = APH_Gamification::award_jpc_completion($user_id, $step_id, $curriculum_id, $completed_keys, $xp_earned, $gems_earned);
            error_log("JPCXP: Gamification result: " . print_r($gamification_result, true));
        } else {
            error_log("JPCXP: APH_Gamification class not found - XP and gems not awarded");
        }
        
        // Assign next step
        error_log("JPC Debug: About to call assign_next_step with user_id=$user_id, curriculum_id=$curriculum_id, key_number=$key_number");
        $next_assignment = self::assign_next_step($user_id, $curriculum_id, $key_number);
        error_log("JPC Debug: assign_next_step returned: " . print_r($next_assignment, true));
        
        return array(
            'success' => true,
            'message' => 'Step completed successfully',
            'xp_earned' => $xp_earned,
            'gems_earned' => $gems_earned,
            'keys_completed' => $completed_keys,
            'all_keys_complete' => $completed_keys === 12,
            'next_assignment' => $next_assignment
        );
    }
    
    /**
     * Get all curriculum progress for a user
     * 
     * @param int $user_id User ID
     * @return array All curriculum progress data
     */
    public static function get_all_curriculum_progress($user_id) {
        global $wpdb;
        
        // Get all curriculum items
        $curriculum_items = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}jph_jpc_curriculum ORDER BY focus_order ASC",
            ARRAY_A
        );
        
        if (empty($curriculum_items)) {
            // Fallback to original tables
            $curriculum_items = $wpdb->get_results(
                "SELECT * FROM je_practice_curriculum ORDER BY focus_order ASC",
                ARRAY_A
            );
        }
        
        $progress_data = array();
        
        foreach ($curriculum_items as $curriculum) {
            $curriculum_id = $curriculum['id'] ?? $curriculum['ID'];
            $progress = self::get_user_progress($user_id, $curriculum_id);
            
            // Count completed keys
            $completed_keys = 0;
            for ($i = 1; $i <= 12; $i++) {
                if (!empty($progress['step_' . $i])) {
                    $completed_keys++;
                }
            }
            
            $progress_data[] = array(
                'curriculum_id' => $curriculum_id,
                'focus_order' => $curriculum['focus_order'],
                'focus_title' => $curriculum['focus_title'],
                'tempo' => $curriculum['tempo'],
                'completed_keys' => $completed_keys,
                'total_keys' => 12,
                'is_complete' => $completed_keys === 12,
                'progress_percentage' => round(($completed_keys / 12) * 100)
            );
        }
        
        return $progress_data;
    }
    
    /**
     * Sync user's existing JPC data to Practice Hub tables
     * 
     * @param int $user_id User ID
     * @return bool Success status
     */
    public static function sync_from_existing_jpc($user_id) {
        global $wpdb;
        
        // Sync curriculum data
        $curriculum_items = $wpdb->get_results(
            "SELECT * FROM je_practice_curriculum ORDER BY ID ASC",
            ARRAY_A
        );
        
        foreach ($curriculum_items as $item) {
            self::sync_curriculum_to_hub($item);
        }
        
        // Sync steps data
        $steps = $wpdb->get_results(
            "SELECT * FROM je_practice_curriculum_steps ORDER BY step_id ASC",
            ARRAY_A
        );
        
        foreach ($steps as $step) {
            self::sync_step_to_hub($step);
        }
        
        // Sync user assignment
        $assignment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM je_practice_curriculum_assignments 
             WHERE user_id = %d AND deleted_at IS NULL 
             ORDER BY ID DESC LIMIT 1",
            $user_id
        ), ARRAY_A);
        
        if ($assignment) {
            self::sync_user_assignment_to_hub($user_id, $assignment);
        }
        
        // Sync user progress
        $progress_records = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM je_practice_curriculum_progress WHERE user_id = %d",
            $user_id
        ), ARRAY_A);
        
        foreach ($progress_records as $progress) {
            self::sync_user_progress_to_hub($user_id, $progress);
        }
        
        return true;
    }
    
    /**
     * Create initial assignment for new user
     * 
     * @param int $user_id User ID
     * @return array Initial assignment data
     */
    private static function create_initial_assignment($user_id) {
        global $wpdb;
        
        // Get first step (step_id = 1, curriculum_id = 1)
        $first_step = self::get_step_details(1);
        if (!$first_step) {
            return false;
        }
        
        // Create initial assignment
        $insert_result = $wpdb->insert(
            $wpdb->prefix . 'jph_jpc_user_assignments',
            array(
                'user_id' => $user_id,
                'step_id' => 1,
                'curriculum_id' => 1,
                'assigned_date' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%s')
        );
        
        if ($insert_result === false) {
            error_log("JPC Handler: Failed to insert initial assignment for user $user_id");
            return false;
        }
        
        // Also create the JPC practice item for new users
        self::ensure_jpc_practice_item($user_id);
        
        // Get curriculum and step details to return complete assignment data
        $curriculum = self::get_curriculum_details(1);
        if (!$curriculum) {
            error_log("JPC Handler: Failed to get curriculum details for curriculum_id=1");
            return false;
        }
        
        // Return the complete assignment data (avoiding recursive call)
        return array_merge($first_step, $curriculum, array(
            'user_id' => $user_id,
            'assigned_date' => current_time('mysql')
        ));
    }
    
    /**
     * Assign next step after completion
     * 
     * @param int $user_id User ID
     * @param int $curriculum_id Current curriculum ID
     * @param int $current_key Current key number (1-12)
     * @return array Next assignment data
     */
    private static function assign_next_step($user_id, $curriculum_id, $current_key) {
        global $wpdb;
        
        error_log("JPC Debug: assign_next_step called with user_id=$user_id, curriculum_id=$curriculum_id, current_key=$current_key");
        
        // Get the current assignment to find the actual step_id
        $current_assignment = $wpdb->get_row($wpdb->prepare(
            "SELECT step_id FROM {$wpdb->prefix}jph_jpc_user_assignments 
             WHERE user_id = %d AND curriculum_id = %d AND deleted_at IS NULL",
            $user_id, $curriculum_id
        ), ARRAY_A);
        
        if (!$current_assignment) {
            error_log("JPC Debug: No current assignment found for user $user_id, curriculum $curriculum_id");
            return false;
        }
        
        $current_step_id = $current_assignment['step_id'];
        error_log("JPC Debug: Current step_id from assignment = $current_step_id");
        
        // Find the next step in sequence
        $next_step = $wpdb->get_row($wpdb->prepare(
            "SELECT step_id FROM {$wpdb->prefix}jph_jpc_steps 
             WHERE curriculum_id = %d AND step_id > %d 
             ORDER BY step_id ASC LIMIT 1",
            $curriculum_id, $current_step_id
        ), ARRAY_A);
        
        error_log("JPC Debug: Looking for next step after step_id=$current_step_id in curriculum_id=$curriculum_id");
        error_log("JPC Debug: Found next step: " . print_r($next_step, true));
        
        if ($next_step) {
            error_log("JPC Debug: Updating assignment to step_id = {$next_step['step_id']}");
            
            // Update assignment
            $update_result = $wpdb->update(
                $wpdb->prefix . 'jph_jpc_user_assignments',
                array(
                    'step_id' => $next_step['step_id'],
                    'assigned_date' => current_time('mysql')
                ),
                array('user_id' => $user_id, 'deleted_at' => null),
                array('%d', '%s'),
                array('%d', '%s')
            );
            
            error_log("JPC Debug: Update result: " . print_r($update_result, true));
            
            $new_assignment = self::get_user_current_assignment($user_id);
            error_log("JPC Debug: New assignment: " . print_r($new_assignment, true));
            
            return $new_assignment;
        }
        
        // If last key or next key not found, move to next curriculum
        $next_curriculum = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}jph_jpc_curriculum 
             WHERE id > %d ORDER BY id ASC LIMIT 1",
            $curriculum_id
        ), ARRAY_A);
        
        if ($next_curriculum) {
            // Get first step of next curriculum
            $first_step = $wpdb->get_row($wpdb->prepare(
                "SELECT step_id FROM {$wpdb->prefix}jph_jpc_steps 
                 WHERE curriculum_id = %d AND key_sig = 1",
                $next_curriculum['id']
            ), ARRAY_A);
            
            if ($first_step) {
                // Update assignment
                $wpdb->update(
                    $wpdb->prefix . 'jph_jpc_user_assignments',
                    array(
                        'step_id' => $first_step['step_id'],
                        'curriculum_id' => $next_curriculum['id'],
                        'assigned_date' => current_time('mysql')
                    ),
                    array('user_id' => $user_id, 'deleted_at' => null),
                    array('%d', '%d', '%s'),
                    array('%d', '%s')
                );
                
                return self::get_user_current_assignment($user_id);
            }
        }
        
        // No more steps available
        return array('message' => 'Congratulations! You have completed all available curriculum focuses.');
    }
    
    /**
     * Sync curriculum data to Practice Hub tables
     */
    private static function sync_curriculum_to_hub($curriculum_data) {
        global $wpdb;
        
        $wpdb->replace(
            $wpdb->prefix . 'jph_jpc_curriculum',
            array(
                'id' => $curriculum_data['ID'],
                'focus_order' => $curriculum_data['focus_order'],
                'focus_title' => $curriculum_data['focus_title'],
                'focus_pillar' => $curriculum_data['focus_pillar'],
                'focus_element' => $curriculum_data['focus_element'],
                'tempo' => $curriculum_data['tempo'],
                'resource_pdf' => $curriculum_data['resource_pdf'],
                'resource_ireal' => $curriculum_data['resource_ireal'],
                'resource_mp3' => $curriculum_data['resource_mp3'],
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Sync step data to Practice Hub tables
     */
    private static function sync_step_to_hub($step_data) {
        global $wpdb;
        
        $wpdb->replace(
            $wpdb->prefix . 'jph_jpc_steps',
            array(
                'step_id' => $step_data['step_id'],
                'curriculum_id' => $step_data['curriculum_id'],
                'key_sig' => $step_data['key_sig'],
                'key_sig_name' => $step_data['key_sig_name'],
                'vimeo_id' => $step_data['vimeo_id'],
                'resource' => $step_data['resource'],
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%s', '%d', '%s', '%s', '%s')
        );
    }
    
    /**
     * Sync user assignment to Practice Hub tables
     * Only creates ONE assignment record per user (like original JPC)
     */
    private static function sync_user_assignment_to_hub($user_id, $assignment_data) {
        global $wpdb;
        
        // Check if user already has an assignment in Practice Hub tables
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}jph_jpc_user_assignments WHERE user_id = %d",
            $user_id
        ));
        
        if ($existing) {
            // Update existing assignment
            $wpdb->update(
                $wpdb->prefix . 'jph_jpc_user_assignments',
                array(
                    'step_id' => $assignment_data['step_id'],
                    'curriculum_id' => $assignment_data['curriculum_id'],
                    'assigned_date' => $assignment_data['date'],
                    'completed_on' => $assignment_data['completed_on'],
                    'deleted_at' => $assignment_data['deleted_at']
                ),
                array('user_id' => $user_id),
                array('%d', '%d', '%s', '%s', '%s'),
                array('%d')
            );
        } else {
            // Create new assignment
            $wpdb->insert(
                $wpdb->prefix . 'jph_jpc_user_assignments',
                array(
                    'user_id' => $user_id,
                    'step_id' => $assignment_data['step_id'],
                    'curriculum_id' => $assignment_data['curriculum_id'],
                    'assigned_date' => $assignment_data['date'],
                    'completed_on' => $assignment_data['completed_on'],
                    'deleted_at' => $assignment_data['deleted_at']
                ),
                array('%d', '%d', '%d', '%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Sync user progress to Practice Hub tables
     */
    private static function sync_user_progress_to_hub($user_id, $progress_data) {
        global $wpdb;
        
        $wpdb->replace(
            $wpdb->prefix . 'jph_jpc_user_progress',
            array(
                'user_id' => $user_id,
                'curriculum_id' => $progress_data['curriculum_id'],
                'step_1' => $progress_data['step_1'],
                'step_2' => $progress_data['step_2'],
                'step_3' => $progress_data['step_3'],
                'step_4' => $progress_data['step_4'],
                'step_5' => $progress_data['step_5'],
                'step_6' => $progress_data['step_6'],
                'step_7' => $progress_data['step_7'],
                'step_8' => $progress_data['step_8'],
                'step_9' => $progress_data['step_9'],
                'step_10' => $progress_data['step_10'],
                'step_11' => $progress_data['step_11'],
                'step_12' => $progress_data['step_12'],
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s')
        );
    }
    
    /**
     * Ensure JPC practice item exists for a user
     * 
     * @param int $user_id User ID
     * @return bool Success status
     */
    private static function ensure_jpc_practice_item($user_id) {
        global $wpdb;
        
        $practice_items_table = $wpdb->prefix . 'jph_practice_items';
        
        // Check if JPC practice item already exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $practice_items_table WHERE user_id = %d AND category = 'jpc'",
            $user_id
        ));
        
        if ($exists) {
            return true; // Already exists
        }
        
        // Create the JPC practice item
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
            error_log("JPC Handler: Failed to create JPC practice item for user $user_id: " . $wpdb->last_error);
            return false;
        }
        
        error_log("JPC Handler: Created JPC practice item for new user $user_id");
        return true;
    }
}
