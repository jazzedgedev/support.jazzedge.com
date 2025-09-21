<?php
/**
 * Test file for Fluent Support AI Integration Plugin
 * This file helps verify the plugin structure and basic functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class FluentSupportAI_Test {
    
    /**
     * Run basic plugin tests
     */
    public static function run_tests() {
        $tests = array();
        
        // Test 1: Check if main plugin class exists
        $tests['main_class'] = class_exists('FluentSupportAI');
        
        // Test 2: Check if required classes exist
        $tests['settings_class'] = class_exists('FluentSupportAI_Settings');
        $tests['prompt_manager_class'] = class_exists('FluentSupportAI_Prompt_Manager');
        $tests['openai_client_class'] = class_exists('FluentSupportAI_OpenAI_Client');
        $tests['reply_generator_class'] = class_exists('FluentSupportAI_Reply_Generator');
        
        // Test 3: Check if database table exists
        global $wpdb;
        $table_name = $wpdb->prefix . 'fluent_support_ai_prompts';
        $tests['database_table'] = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        // Test 4: Check if required WordPress functions exist
        $tests['wp_functions'] = function_exists('add_action') && function_exists('add_filter');
        
        // Test 5: Check if Fluent Support is active
        $tests['fluent_support_active'] = class_exists('FluentSupport');
        
        // Test 6: Check plugin constants
        $tests['plugin_constants'] = defined('FLUENT_SUPPORT_AI_VERSION') && defined('FLUENT_SUPPORT_AI_PLUGIN_DIR');
        
        return $tests;
    }
    
    /**
     * Display test results
     */
    public static function display_test_results() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $tests = self::run_tests();
        
        echo '<div class="wrap">';
        echo '<h1>Fluent Support AI Integration - Plugin Tests</h1>';
        echo '<div class="test-results">';
        
        foreach ($tests as $test_name => $result) {
            $status = $result ? 'PASS' : 'FAIL';
            $class = $result ? 'test-pass' : 'test-fail';
            echo '<div class="test-item ' . $class . '">';
            echo '<strong>' . ucwords(str_replace('_', ' ', $test_name)) . ':</strong> ';
            echo '<span class="status">' . $status . '</span>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '<style>';
        echo '.test-results { margin: 20px 0; }';
        echo '.test-item { padding: 10px; margin: 5px 0; border-radius: 3px; }';
        echo '.test-pass { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }';
        echo '.test-fail { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }';
        echo '.status { font-weight: bold; }';
        echo '</style>';
        echo '</div>';
    }
}

// Add test page to admin menu (only for debugging)
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_menu', function() {
        add_submenu_page(
            'fluent-support',
            'AI Plugin Tests',
            'AI Plugin Tests',
            'manage_options',
            'fluent-support-ai-tests',
            array('FluentSupportAI_Test', 'display_test_results')
        );
    });
}
