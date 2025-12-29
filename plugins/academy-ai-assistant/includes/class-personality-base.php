<?php
/**
 * Base Personality Class for Academy AI Assistant
 * 
 * Abstract base class that all AI personalities extend
 * Defines the interface and common functionality for all personalities
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class AI_Personality_Base {
    
    /**
     * Personality identifier (slug)
     * 
     * @return string
     */
    abstract public function get_id();
    
    /**
     * Personality display name
     * 
     * @return string
     */
    abstract public function get_name();
    
    /**
     * Personality description
     * 
     * @return string
     */
    abstract public function get_description();
    
    /**
     * Get system prompt for this personality
     * This defines how the AI should behave
     * 
     * @return string
     */
    abstract public function get_system_prompt();
    
    /**
     * Get avatar/icon for this personality
     * 
     * @return string HTML or icon class
     */
    abstract public function get_avatar();
    
    /**
     * Get color scheme for this personality
     * 
     * @return array Array with 'primary', 'secondary', 'accent' colors
     */
    abstract public function get_color_scheme();
    
    /**
     * Format AI response with personality style
     * Can be overridden for custom formatting
     * 
     * @param string $response Raw AI response
     * @return string Formatted response
     */
    public function format_response($response) {
        // Default: return as-is, but sanitize
        return wp_kses_post($response);
    }
    
    /**
     * Get context data for this personality
     * Can be overridden to customize what context each personality needs
     * 
     * @param int $user_id User ID
     * @param string $query User's query
     * @param array $additional_context Additional context data
     * @return array Context data array
     */
    public function get_context_data($user_id, $query, $additional_context = array()) {
        // Use context builder to gather data
        if (class_exists('AI_Context_Builder')) {
            $context_builder = new AI_Context_Builder();
            $context = $context_builder->build_context($user_id, $query, $additional_context);
            return $context;
        }
        
        // Fallback: return additional context as-is
        return $additional_context;
    }
    
    /**
     * Get temperature setting for AI model
     * Different personalities may use different creativity levels
     * 
     * @return float Temperature (0.0 to 2.0)
     */
    public function get_temperature() {
        return 0.7; // Default balanced temperature
    }
    
    /**
     * Get max tokens for responses
     * Some personalities may give longer/shorter responses
     * 
     * @return int Max tokens
     */
    public function get_max_tokens() {
        return 1000; // Default
    }
    
    /**
     * Get personality metadata
     * 
     * @return array
     */
    public function get_metadata() {
        return array(
            'id' => $this->get_id(),
            'name' => $this->get_name(),
            'description' => $this->get_description(),
            'avatar' => $this->get_avatar(),
            'colors' => $this->get_color_scheme(),
            'temperature' => $this->get_temperature(),
            'max_tokens' => $this->get_max_tokens()
        );
    }
}

