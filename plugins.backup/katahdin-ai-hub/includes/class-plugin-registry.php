<?php
/**
 * Plugin Registry for Katahdin AI Hub
 * Manages registered plugins and their configurations
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Hub_Plugin_Registry {
    
    /**
     * Registered plugins
     */
    private $registered_plugins = array();
    
    /**
     * Initialize Plugin Registry
     */
    public function init() {
        // Load registered plugins from database
        $this->load_registered_plugins();
    }
    
    /**
     * Register a plugin with the hub
     */
    public function register($plugin_id, $config) {
        // Validate configuration
        $validated_config = $this->validate_config($config);
        if (is_wp_error($validated_config)) {
            return $validated_config;
        }
        
        // Check if plugin already exists
        $existing_plugin = $this->get_plugin($plugin_id);
        
        if ($existing_plugin) {
            // Update existing plugin
            return $this->update_plugin($plugin_id, $validated_config);
        } else {
            // Register new plugin
            return $this->add_plugin($plugin_id, $validated_config);
        }
    }
    
    /**
     * Get a registered plugin
     */
    public function get_plugin($plugin_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $plugin = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE plugin_id = %s",
            $plugin_id
        ), ARRAY_A);
        
        if ($plugin) {
            $plugin['features'] = json_decode($plugin['features'], true);
            return $plugin;
        }
        
        return null;
    }
    
    /**
     * Get all registered plugins
     */
    public function get_all_plugins() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $plugins = $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY registered_at DESC",
            ARRAY_A
        );
        
        foreach ($plugins as &$plugin) {
            $plugin['features'] = json_decode($plugin['features'], true);
        }
        
        return $plugins;
    }
    
    /**
     * Check if plugin is registered
     */
    public function is_registered($plugin_id) {
        return $this->get_plugin($plugin_id) !== null;
    }
    
    /**
     * Check if plugin has quota available
     */
    public function has_quota_available($plugin_id) {
        $plugin = $this->get_plugin($plugin_id);
        
        if (!$plugin) {
            return false;
        }
        
        return $plugin['quota_used'] < $plugin['quota_limit'];
    }
    
    /**
     * Get plugin quota status
     */
    public function get_quota_status($plugin_id) {
        $plugin = $this->get_plugin($plugin_id);
        
        if (!$plugin) {
            return array(
                'available' => false,
                'used' => 0,
                'limit' => 0,
                'percentage' => 0
            );
        }
        
        $percentage = $plugin['quota_limit'] > 0 ? 
            round(($plugin['quota_used'] / $plugin['quota_limit']) * 100, 2) : 0;
        
        return array(
            'available' => $plugin['quota_used'] < $plugin['quota_limit'],
            'used' => $plugin['quota_used'],
            'limit' => $plugin['quota_limit'],
            'percentage' => $percentage
        );
    }
    
    /**
     * Update plugin quota
     */
    public function update_quota($plugin_id, $tokens_used) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $result = $wpdb->query($wpdb->prepare(
            "UPDATE $table_name 
             SET quota_used = quota_used + %d, last_used = %s 
             WHERE plugin_id = %s",
            $tokens_used, current_time('mysql'), $plugin_id
        ));
        
        return $result !== false;
    }
    
    /**
     * Reset plugin quota (monthly reset)
     */
    public function reset_quota($plugin_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $result = $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET quota_used = 0 WHERE plugin_id = %s",
            $plugin_id
        ));
        
        return $result !== false;
    }
    
    /**
     * Deactivate plugin
     */
    public function deactivate_plugin($plugin_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $result = $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET is_active = 0 WHERE plugin_id = %s",
            $plugin_id
        ));
        
        return $result !== false;
    }
    
    /**
     * Activate plugin
     */
    public function activate_plugin($plugin_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $result = $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET is_active = 1 WHERE plugin_id = %s",
            $plugin_id
        ));
        
        return $result !== false;
    }
    
    /**
     * Delete plugin registration
     */
    public function delete_plugin($plugin_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE plugin_id = %s",
            $plugin_id
        ));
        
        return $result !== false;
    }
    
    /**
     * Validate plugin configuration
     */
    private function validate_config($config) {
        $required_fields = array('name', 'version');
        $optional_fields = array(
            'features' => array(),
            'quota_limit' => 1000,
            'description' => '',
            'author' => '',
            'plugin_url' => ''
        );
        
        // Check required fields
        foreach ($required_fields as $field) {
            if (!isset($config[$field]) || empty($config[$field])) {
                return new WP_Error('missing_field', "Required field '{$field}' is missing");
            }
        }
        
        // Merge with defaults
        $validated_config = array_merge($optional_fields, $config);
        
        // Validate features
        if (!is_array($validated_config['features'])) {
            $validated_config['features'] = array();
        }
        
        // Validate quota limit
        $validated_config['quota_limit'] = max(0, intval($validated_config['quota_limit']));
        
        // Sanitize strings
        $validated_config['name'] = sanitize_text_field($validated_config['name']);
        $validated_config['version'] = sanitize_text_field($validated_config['version']);
        $validated_config['description'] = sanitize_textarea_field($validated_config['description']);
        $validated_config['author'] = sanitize_text_field($validated_config['author']);
        $validated_config['plugin_url'] = esc_url_raw($validated_config['plugin_url']);
        
        return $validated_config;
    }
    
    /**
     * Add new plugin to database
     */
    private function add_plugin($plugin_id, $config) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'plugin_id' => $plugin_id,
                'plugin_name' => $config['name'],
                'version' => $config['version'],
                'features' => json_encode($config['features']),
                'quota_limit' => $config['quota_limit'],
                'quota_used' => 0,
                'is_active' => 1,
                'registered_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s')
        );
        
        if ($result) {
            // Log registration
            katahdin_ai_hub()->log('info', "Plugin '{$plugin_id}' registered successfully", array(
                'plugin_id' => $plugin_id,
                'config' => $config
            ));
            
            return true;
        } else {
            return new WP_Error('registration_failed', 'Failed to register plugin');
        }
    }
    
    /**
     * Update existing plugin
     */
    private function update_plugin($plugin_id, $config) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'plugin_name' => $config['name'],
                'version' => $config['version'],
                'features' => json_encode($config['features']),
                'quota_limit' => $config['quota_limit']
            ),
            array('plugin_id' => $plugin_id),
            array('%s', '%s', '%s', '%d'),
            array('%s')
        );
        
        if ($result !== false) {
            // Log update
            katahdin_ai_hub()->log('info', "Plugin '{$plugin_id}' updated successfully", array(
                'plugin_id' => $plugin_id,
                'config' => $config
            ));
            
            return true;
        } else {
            return new WP_Error('update_failed', 'Failed to update plugin');
        }
    }
    
    /**
     * Load registered plugins from database
     */
    private function load_registered_plugins() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $plugins = $wpdb->get_results(
            "SELECT plugin_id, plugin_name, version, features, quota_limit, quota_used, is_active 
             FROM $table_name WHERE is_active = 1",
            ARRAY_A
        );
        
        foreach ($plugins as $plugin) {
            $this->registered_plugins[$plugin['plugin_id']] = array(
                'name' => $plugin['plugin_name'],
                'version' => $plugin['version'],
                'features' => json_decode($plugin['features'], true),
                'quota_limit' => $plugin['quota_limit'],
                'quota_used' => $plugin['quota_used'],
                'is_active' => $plugin['is_active']
            );
        }
    }
    
    /**
     * Get plugin statistics
     */
    public function get_plugin_stats($plugin_id) {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'katahdin_ai_usage';
        $plugins_table = $wpdb->prefix . 'katahdin_ai_plugins';
        
        // Get basic plugin info
        $plugin = $this->get_plugin($plugin_id);
        if (!$plugin) {
            return null;
        }
        
        // Get usage statistics for last 30 days
        $date_from = date('Y-m-d H:i:s', time() - (30 * 24 * 60 * 60));
        
        $usage_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_requests,
                SUM(tokens_used) as total_tokens,
                SUM(cost) as total_cost,
                AVG(response_time) as avg_response_time,
                SUM(success) as successful_requests,
                COUNT(*) - SUM(success) as failed_requests
             FROM $usage_table 
             WHERE plugin_id = %s AND created_at > %s",
            $plugin_id, $date_from
        ));
        
        return array(
            'plugin' => $plugin,
            'usage' => $usage_stats,
            'quota_status' => $this->get_quota_status($plugin_id)
        );
    }
}
