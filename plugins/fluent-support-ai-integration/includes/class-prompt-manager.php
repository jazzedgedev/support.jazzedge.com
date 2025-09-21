<?php
/**
 * Prompt Manager class for Fluent Support AI Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class FluentSupportAI_Prompt_Manager {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'fluent_support_ai_prompts';
    }
    
    /**
     * Get all prompts
     */
    public function get_prompts() {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE is_active = 1 ORDER BY name ASC",
            ARRAY_A
        );
        
        return $results ? $results : array();
    }
    
    /**
     * Get prompt by ID
     */
    public function get_prompt($id) {
        global $wpdb;
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d AND is_active = 1",
                $id
            ),
            ARRAY_A
        );
        
        return $result;
    }
    
    /**
     * Save prompt
     */
    public function save_prompt($data) {
        global $wpdb;
        
        // Sanitize input data
        $name = sanitize_text_field($data['prompt_name']);
        $description = sanitize_textarea_field($data['prompt_description']);
        $prompt_content = sanitize_textarea_field($data['prompt_content']);
        
        // Validate required fields
        if (empty($name) || empty($prompt_content)) {
            return array(
                'success' => false,
                'message' => __('Name and prompt content are required', 'fluent-support-ai')
            );
        }
        
        // Check if updating existing prompt
        if (isset($data['prompt_id']) && !empty($data['prompt_id'])) {
            $prompt_id = intval($data['prompt_id']);
            
            $result = $wpdb->update(
                $this->table_name,
                array(
                    'name' => $name,
                    'description' => $description,
                    'prompt' => $prompt_content,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $prompt_id),
                array('%s', '%s', '%s', '%s'),
                array('%d')
            );
            
            if ($result !== false) {
                return array(
                    'success' => true,
                    'message' => __('Prompt updated successfully', 'fluent-support-ai'),
                    'prompt_id' => $prompt_id
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('Failed to update prompt', 'fluent-support-ai')
                );
            }
        } else {
            // Insert new prompt
            $result = $wpdb->insert(
                $this->table_name,
                array(
                    'name' => $name,
                    'description' => $description,
                    'prompt' => $prompt_content,
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );
            
            if ($result !== false) {
                return array(
                    'success' => true,
                    'message' => __('Prompt added successfully', 'fluent-support-ai'),
                    'prompt_id' => $wpdb->insert_id
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('Failed to add prompt', 'fluent-support-ai')
                );
            }
        }
    }
    
    /**
     * Delete prompt (soft delete)
     */
    public function delete_prompt($id) {
        global $wpdb;
        
        $id = intval($id);
        
        if (empty($id)) {
            return array(
                'success' => false,
                'message' => __('Invalid prompt ID', 'fluent-support-ai')
            );
        }
        
        $result = $wpdb->update(
            $this->table_name,
            array('is_active' => 0),
            array('id' => $id),
            array('%d'),
            array('%d')
        );
        
        if ($result !== false) {
            return array(
                'success' => true,
                'message' => __('Prompt deleted successfully', 'fluent-support-ai')
            );
        } else {
            return array(
                'success' => false,
                'message' => __('Failed to delete prompt', 'fluent-support-ai')
            );
        }
    }
    
    /**
     * Get prompt for AI generation
     */
    public function get_prompt_for_generation($id) {
        $prompt = $this->get_prompt($id);
        
        if (!$prompt) {
            return false;
        }
        
        return $prompt['prompt'];
    }
    
    /**
     * Validate prompt content
     */
    public function validate_prompt_content($content) {
        // Check if prompt contains ticket content placeholder
        if (strpos($content, '{ticket_content}') === false) {
            return array(
                'valid' => false,
                'message' => __('Prompt must contain {ticket_content} placeholder', 'fluent-support-ai')
            );
        }
        
        // Check minimum length
        if (strlen($content) < 10) {
            return array(
                'valid' => false,
                'message' => __('Prompt content is too short', 'fluent-support-ai')
            );
        }
        
        return array(
            'valid' => true,
            'message' => __('Prompt content is valid', 'fluent-support-ai')
        );
    }
    
    /**
     * Get prompt statistics
     */
    public function get_prompt_stats() {
        global $wpdb;
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE is_active = 1");
        $recent = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE is_active = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        return array(
            'total' => intval($total),
            'recent' => intval($recent)
        );
    }
}
