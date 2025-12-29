<?php
/**
 * Personality Manager Class for Academy AI Assistant
 * 
 * Manages all personality instances, routes queries, handles switching
 * Security: Validates personality IDs, sanitizes inputs
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Personality_Manager {
    
    /**
     * Registered personalities
     * 
     * @var array
     */
    private $personalities = array();
    
    /**
     * Default personality ID
     */
    private $default_personality = 'study_buddy';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->register_personalities();
    }
    
    /**
     * Register all available personalities
     * Only registers classes that exist
     */
    private function register_personalities() {
        $personality_classes = array(
            'study_buddy' => 'AI_Study_Buddy',
            'practice_assistant' => 'AI_Practice_Assistant',
            'coach' => 'AI_Coach',
            'professor' => 'AI_Professor',
            'mentor' => 'AI_Mentor',
            'cheerleader' => 'AI_Cheerleader'
        );
        
        foreach ($personality_classes as $id => $class_name) {
            // Only register if class exists (allows gradual implementation)
            if (class_exists($class_name)) {
                try {
                    $personality = new $class_name();
                    if ($personality instanceof AI_Personality_Base) {
                        $this->personalities[$id] = $personality;
                    }
                } catch (Exception $e) {
                    error_log("Academy AI Assistant: Failed to load personality {$id}: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Get personality by ID
     * Security: Validates personality ID against whitelist
     * 
     * @param string $personality_id Personality ID
     * @return AI_Personality_Base|false Personality instance or false
     */
    public function get_personality($personality_id) {
        // Security: Sanitize input
        $personality_id = sanitize_text_field($personality_id);
        
        // Security: Check if personality exists
        if (!isset($this->personalities[$personality_id])) {
            return false;
        }
        
        return $this->personalities[$personality_id];
    }
    
    /**
     * Get default personality
     * 
     * @return AI_Personality_Base|false
     */
    public function get_default_personality() {
        return $this->get_personality($this->default_personality);
    }
    
    /**
     * Get all registered personalities
     * 
     * @return array Array of personality instances
     */
    public function get_all_personalities() {
        return $this->personalities;
    }
    
    /**
     * Get list of available personality IDs
     * 
     * @return array Array of personality IDs
     */
    public function get_personality_ids() {
        return array_keys($this->personalities);
    }
    
    /**
     * Get personality metadata for all personalities
     * Used for frontend display
     * 
     * @return array Array of personality metadata
     */
    public function get_all_personality_metadata() {
        $metadata = array();
        
        foreach ($this->personalities as $id => $personality) {
            $metadata[$id] = $personality->get_metadata();
        }
        
        return $metadata;
    }
    
    /**
     * Check if personality exists
     * 
     * @param string $personality_id Personality ID
     * @return bool
     */
    public function personality_exists($personality_id) {
        $personality_id = sanitize_text_field($personality_id);
        return isset($this->personalities[$personality_id]);
    }
    
    /**
     * Get personality for user's current session
     * Falls back to default if session personality not found
     * 
     * @param string $session_personality Personality from session
     * @return AI_Personality_Base
     */
    public function get_personality_for_session($session_personality = null) {
        if ($session_personality && $this->personality_exists($session_personality)) {
            return $this->get_personality($session_personality);
        }
        
        return $this->get_default_personality();
    }
    
    /**
     * Validate personality ID
     * Security: Whitelist check
     * 
     * @param string $personality_id Personality ID to validate
     * @return bool True if valid
     */
    public function validate_personality_id($personality_id) {
        $personality_id = sanitize_text_field($personality_id);
        return $this->personality_exists($personality_id);
    }
}

