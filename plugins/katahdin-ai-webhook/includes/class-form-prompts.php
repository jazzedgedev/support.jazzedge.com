<?php
/**
 * Form Prompts Manager for Katahdin AI Webhook
 * Handles storage and retrieval of form-specific AI prompts
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Webhook_Form_Prompts {
    
    /**
     * Table name for form prompts
     */
    private $table_name;
    
    /**
     * Initialize the form prompts manager
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'katahdin_ai_webhook_prompts';
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
            form_id varchar(100) NOT NULL,
            prompt text NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY form_id (form_id),
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
        
        $results = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC",
            ARRAY_A
        );
        
        return $results ? $results : array();
    }
    
    /**
     * Get active prompts
     */
    public function get_active_prompts() {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE is_active = 1 ORDER BY created_at DESC",
            ARRAY_A
        );
        
        return $results ? $results : array();
    }
    
    /**
     * Get prompt by form ID
     */
    public function get_prompt_by_form_id($form_id) {
        global $wpdb;
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE form_id = %s AND is_active = 1",
                $form_id
            ),
            ARRAY_A
        );
        
        return $result;
    }
    
    /**
     * Get prompt by ID
     */
    public function get_prompt_by_id($id) {
        global $wpdb;
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );
        
        return $result;
    }
    
    /**
     * Add new prompt
     */
    public function add_prompt($title, $form_id, $prompt) {
        global $wpdb;
        
        // Ensure table exists
        if (!$this->table_exists()) {
            $this->create_table();
        }
        
        // Check if form_id already exists
        $existing = $this->get_prompt_by_form_id($form_id);
        if ($existing) {
            return new WP_Error('form_id_exists', 'A prompt for this form ID already exists');
        }
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'title' => sanitize_text_field($title),
                'form_id' => sanitize_text_field($form_id),
                'prompt' => sanitize_textarea_field($prompt),
                'is_active' => 1
            ),
            array('%s', '%s', '%s', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to add prompt: ' . $wpdb->last_error);
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update prompt
     */
    public function update_prompt($id, $title, $form_id, $prompt, $is_active = 1) {
        global $wpdb;
        
        // Check if form_id already exists for a different prompt
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE form_id = %s AND id != %d",
                $form_id,
                $id
            )
        );
        
        if ($existing) {
            return new WP_Error('form_id_exists', 'A prompt for this form ID already exists');
        }
        
        $result = $wpdb->update(
            $this->table_name,
            array(
                'title' => sanitize_text_field($title),
                'form_id' => sanitize_text_field($form_id),
                'prompt' => sanitize_textarea_field($prompt),
                'is_active' => (int) $is_active
            ),
            array('id' => $id),
            array('%s', '%s', '%s', '%d'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update prompt');
        }
        
        return true;
    }
    
    /**
     * Delete prompt
     */
    public function delete_prompt($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('delete_failed', 'Failed to delete prompt');
        }
        
        return true;
    }
    
    /**
     * Toggle prompt active status
     */
    public function toggle_prompt_status($id) {
        global $wpdb;
        
        // Get current status
        $prompt = $this->get_prompt_by_id($id);
        if (!$prompt) {
            return new WP_Error('prompt_not_found', 'Prompt not found');
        }
        
        $new_status = $prompt['is_active'] ? 0 : 1;
        
        $result = $wpdb->update(
            $this->table_name,
            array('is_active' => $new_status),
            array('id' => $id),
            array('%d'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to toggle prompt status');
        }
        
        return $new_status;
    }
    
    /**
     * Get prompt for form processing
     */
    public function get_prompt_for_form($form_id) {
        $prompt_data = $this->get_prompt_by_form_id($form_id);
        
        if ($prompt_data) {
            return array(
                'prompt' => $prompt_data['prompt'],
                'title' => $prompt_data['title'],
                'form_id' => $prompt_data['form_id']
            );
        }
        
        // Return default prompt if no form-specific prompt found
        return array(
            'prompt' => get_option('katahdin_ai_webhook_prompt', 'Analyze the following form submission data and provide insights, recommendations, or summaries as appropriate. Be concise but informative.'),
            'title' => 'Default Prompt',
            'form_id' => 'default'
        );
    }
    
    /**
     * Check if table exists
     */
    public function table_exists() {
        global $wpdb;
        
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $this->table_name
            )
        );
        
        return $result === $this->table_name;
    }
    
    /**
     * Get table stats
     */
    public function get_stats() {
        global $wpdb;
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $active = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE is_active = 1");
        
        return array(
            'total' => (int) $total,
            'active' => (int) $active,
            'inactive' => (int) $total - (int) $active
        );
    }
    
    /**
     * Force create table (for debugging)
     */
    public function force_create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            form_id varchar(100) NOT NULL,
            prompt text NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY form_id (form_id),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        return $result;
    }
}
