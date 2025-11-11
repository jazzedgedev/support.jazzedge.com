<?php
/**
 * Form Prompts Manager for Katahdin AI Forms
 * Handles storage and retrieval of form-specific AI prompts with per-prompt email settings
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Forms_Form_Prompts {
    
    /**
     * Table name for form prompts
     */
    private $table_name;
    
    /**
     * Initialize the form prompts manager
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'katahdin_ai_forms_prompts';
    }
    
    /**
     * Create the prompts table
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            prompt_id varchar(100) NOT NULL,
            prompt text NOT NULL,
            email_address varchar(255) NOT NULL,
            email_subject varchar(255) NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY prompt_id (prompt_id),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get all prompts
     */
    public function get_all_prompts() {
        global $wpdb;
        
        try {
            $results = $wpdb->get_results(
                "SELECT * FROM {$this->table_name} ORDER BY created_at DESC",
                ARRAY_A
            );
            
            return $results ?: array();
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Form Prompts error: ' . $e->getMessage());
            }
            return array();
        }
    }
    
    /**
     * Get active prompts only
     */
    public function get_active_prompts() {
        global $wpdb;
        
        try {
            $results = $wpdb->get_results(
                "SELECT * FROM {$this->table_name} WHERE is_active = 1 ORDER BY created_at DESC",
                ARRAY_A
            );
            
            return $results ?: array();
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Form Prompts error: ' . $e->getMessage());
            }
            return array();
        }
    }
    
    /**
     * Get prompt by ID
     */
    public function get_prompt_by_id($id) {
        global $wpdb;
        
        try {
            $result = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", intval($id)),
                ARRAY_A
            );
            
            return $result;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Form Prompts error: ' . $e->getMessage());
            }
            return null;
        }
    }
    
    /**
     * Get prompt by prompt_id
     */
    public function get_prompt_by_prompt_id($prompt_id) {
        global $wpdb;
        
        try {
            $result = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$this->table_name} WHERE prompt_id = %s AND is_active = 1",
                    sanitize_text_field($prompt_id)
                ),
                ARRAY_A
            );
            
            return $result;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Form Prompts error: ' . $e->getMessage());
            }
            return null;
        }
    }
    
    /**
     * Add a new prompt
     */
    public function add_prompt($title, $prompt_id, $prompt, $email_address, $email_subject) {
        global $wpdb;
        
        try {
            // Validate inputs
            if (empty($title) || empty($prompt_id) || empty($prompt) || empty($email_address) || empty($email_subject)) {
                return new WP_Error('missing_fields', 'All fields are required');
            }
            
            if (!is_email($email_address)) {
                return new WP_Error('invalid_email', 'Invalid email address');
            }
            
            // Check if prompt_id already exists
            $existing = $this->get_prompt_by_prompt_id($prompt_id);
            if ($existing) {
                return new WP_Error('prompt_id_exists', 'A prompt for this prompt ID already exists');
            }
            
            $result = $wpdb->insert(
                $this->table_name,
                array(
                    'title' => sanitize_text_field($title),
                    'prompt_id' => sanitize_text_field($prompt_id),
                    'prompt' => sanitize_textarea_field($prompt),
                    'email_address' => sanitize_email($email_address),
                    'email_subject' => sanitize_text_field($email_subject),
                    'is_active' => 1,
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s', '%d', '%s')
            );
            
            if ($result === false) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Katahdin AI Forms Form Prompts: Failed to insert prompt - ' . $wpdb->last_error);
                }
                return new WP_Error('insert_failed', 'Failed to add prompt');
            }
            
            return $wpdb->insert_id;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Form Prompts error: ' . $e->getMessage());
            }
            return new WP_Error('exception', 'Error adding prompt: ' . $e->getMessage());
        }
    }
    
    /**
     * Update a prompt
     */
    public function update_prompt($id, $title, $prompt_id, $prompt, $email_address, $email_subject, $is_active = 1) {
        global $wpdb;
        
        try {
            // Validate inputs
            if (empty($title) || empty($prompt_id) || empty($prompt) || empty($email_address) || empty($email_subject)) {
                return new WP_Error('missing_fields', 'All fields are required');
            }
            
            if (!is_email($email_address)) {
                return new WP_Error('invalid_email', 'Invalid email address');
            }
            
            // Check if prompt_id already exists for a different prompt
            $existing = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id FROM {$this->table_name} WHERE prompt_id = %s AND id != %d",
                    sanitize_text_field($prompt_id),
                    intval($id)
                ),
                ARRAY_A
            );
            
            if ($existing) {
                return new WP_Error('prompt_id_exists', 'A prompt for this prompt ID already exists');
            }
            
            $result = $wpdb->update(
                $this->table_name,
                array(
                    'title' => sanitize_text_field($title),
                    'prompt_id' => sanitize_text_field($prompt_id),
                    'prompt' => sanitize_textarea_field($prompt),
                    'email_address' => sanitize_email($email_address),
                    'email_subject' => sanitize_text_field($email_subject),
                    'is_active' => intval($is_active),
                    'updated_at' => current_time('mysql')
                ),
                array('id' => intval($id)),
                array('%s', '%s', '%s', '%s', '%s', '%d', '%s'),
                array('%d')
            );
            
            if ($result === false) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Katahdin AI Forms Form Prompts: Failed to update prompt - ' . $wpdb->last_error);
                }
                return new WP_Error('update_failed', 'Failed to update prompt');
            }
            
            return true;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Form Prompts error: ' . $e->getMessage());
            }
            return new WP_Error('exception', 'Error updating prompt: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a prompt
     */
    public function delete_prompt($id) {
        global $wpdb;
        
        try {
            $result = $wpdb->delete(
                $this->table_name,
                array('id' => intval($id)),
                array('%d')
            );
            
            if ($result === false) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Katahdin AI Forms Form Prompts: Failed to delete prompt - ' . $wpdb->last_error);
                }
                return new WP_Error('delete_failed', 'Failed to delete prompt');
            }
            
            return true;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Form Prompts error: ' . $e->getMessage());
            }
            return new WP_Error('exception', 'Error deleting prompt: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle prompt active status
     */
    public function toggle_prompt_status($id) {
        global $wpdb;
        
        try {
            // Get current status
            $prompt = $this->get_prompt_by_id($id);
            if (!$prompt) {
                return new WP_Error('prompt_not_found', 'Prompt not found');
            }
            
            $new_status = $prompt['is_active'] ? 0 : 1;
            
            $result = $wpdb->update(
                $this->table_name,
                array(
                    'is_active' => $new_status,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => intval($id)),
                array('%d', '%s'),
                array('%d')
            );
            
            if ($result === false) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Katahdin AI Forms Form Prompts: Failed to toggle prompt status - ' . $wpdb->last_error);
                }
                return new WP_Error('update_failed', 'Failed to toggle prompt status');
            }
            
            return $new_status;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Form Prompts error: ' . $e->getMessage());
            }
            return new WP_Error('exception', 'Error toggling prompt status: ' . $e->getMessage());
        }
    }
    
    /**
     * Get prompt statistics
     */
    public function get_prompt_stats() {
        global $wpdb;
        
        try {
            $stats = $wpdb->get_row(
                "SELECT 
                    COUNT(*) as total_prompts,
                    SUM(is_active) as active_prompts,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_prompts
                FROM {$this->table_name}",
                ARRAY_A
            );
            
            return $stats ?: array(
                'total_prompts' => 0,
                'active_prompts' => 0,
                'inactive_prompts' => 0
            );
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Form Prompts error: ' . $e->getMessage());
            }
            return array(
                'total_prompts' => 0,
                'active_prompts' => 0,
                'inactive_prompts' => 0
            );
        }
    }
    
    /**
     * Validate prompt data
     */
    public function validate_prompt_data($data) {
        $errors = array();
        
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        } elseif (strlen($data['title']) > 255) {
            $errors[] = 'Title is too long (max 255 characters)';
        }
        
        if (empty($data['prompt_id'])) {
            $errors[] = 'Prompt ID is required';
        } elseif (strlen($data['prompt_id']) > 100) {
            $errors[] = 'Prompt ID is too long (max 100 characters)';
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $data['prompt_id'])) {
            $errors[] = 'Prompt ID can only contain letters, numbers, underscores, and hyphens';
        }
        
        if (empty($data['prompt'])) {
            $errors[] = 'Prompt is required';
        }
        
        if (empty($data['email_address'])) {
            $errors[] = 'Email address is required';
        } elseif (!is_email($data['email_address'])) {
            $errors[] = 'Invalid email address';
        }
        
        if (empty($data['email_subject'])) {
            $errors[] = 'Email subject is required';
        } elseif (strlen($data['email_subject']) > 255) {
            $errors[] = 'Email subject is too long (max 255 characters)';
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Get prompt for form processing (legacy method for compatibility)
     */
    public function get_prompt_for_form($prompt_id) {
        $prompt_data = $this->get_prompt_by_prompt_id($prompt_id);
        
        if ($prompt_data) {
            return array(
                'prompt' => $prompt_data['prompt'],
                'title' => $prompt_data['title'],
                'prompt_id' => $prompt_data['prompt_id'],
                'email_address' => $prompt_data['email_address'],
                'email_subject' => $prompt_data['email_subject']
            );
        }
        
        // Return null if no prompt found - forms must specify a valid prompt_id
        return null;
    }
    
    /**
     * Search prompts
     */
    public function search_prompts($search_term, $limit = 50) {
        global $wpdb;
        
        try {
            $search_term = '%' . $wpdb->esc_like(sanitize_text_field($search_term)) . '%';
            
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$this->table_name} 
                     WHERE (title LIKE %s OR prompt_id LIKE %s OR prompt LIKE %s) 
                     ORDER BY created_at DESC 
                     LIMIT %d",
                    $search_term,
                    $search_term,
                    $search_term,
                    intval($limit)
                ),
                ARRAY_A
            );
            
            return $results ?: array();
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Form Prompts error: ' . $e->getMessage());
            }
            return array();
        }
    }
    
    /**
     * Duplicate a prompt
     */
    public function duplicate_prompt($id) {
        try {
            $original = $this->get_prompt_by_id($id);
            if (!$original) {
                return new WP_Error('prompt_not_found', 'Original prompt not found');
            }
            
            $new_prompt_id = $original['prompt_id'] . '_copy_' . time();
            $new_title = $original['title'] . ' (Copy)';
            
            return $this->add_prompt(
                $new_title,
                $new_prompt_id,
                $original['prompt'],
                $original['email_address'],
                $original['email_subject']
            );
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms Form Prompts error: ' . $e->getMessage());
            }
            return new WP_Error('exception', 'Error duplicating prompt: ' . $e->getMessage());
        }
    }
}
