<?php
/**
 * Activation Test for Academy AI Assistant
 * 
 * Run this file directly to test database table creation
 * Access: /wp-content/plugins/academy-ai-assistant/test-activation.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. Admin privileges required.');
}

echo '<h1>Academy AI Assistant - Activation Test</h1>';
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    table { border-collapse: collapse; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>';

// Check if plugin is active
if (!class_exists('Academy_AI_Assistant')) {
    echo '<p class="error">❌ Plugin class not found. Plugin may not be active.</p>';
    echo '<p>Please activate the plugin first: <a href="' . admin_url('plugins.php') . '">Go to Plugins</a></p>';
    exit;
} else {
    echo '<p class="success">✅ Plugin class found.</p>';
}

// Check if classes are loaded
$classes_to_check = array(
    'AAA_Database',
    'AAA_Feature_Flags',
    'AAA_Debug_Logger',
    'AI_Personality_Manager',
    'AI_Personality_Base',
    'AI_Study_Buddy',
    'AI_Practice_Assistant'
);

echo '<h2>Class Loading Check</h2>';
echo '<table>';
echo '<tr><th>Class</th><th>Status</th></tr>';
foreach ($classes_to_check as $class) {
    $exists = class_exists($class);
    echo '<tr>';
    echo '<td>' . esc_html($class) . '</td>';
    echo '<td class="' . ($exists ? 'success' : 'error') . '">' . ($exists ? '✅ Loaded' : '❌ Not loaded') . '</td>';
    echo '</tr>';
}
echo '</table>';

// Check database tables
global $wpdb;
$tables = array(
    'conversations' => $wpdb->prefix . 'aaa_conversations',
    'sessions' => $wpdb->prefix . 'aaa_conversation_sessions',
    'debug_logs' => $wpdb->prefix . 'aaa_debug_logs'
);

echo '<h2>Database Tables Check</h2>';
echo '<table>';
echo '<tr><th>Table Name</th><th>Status</th><th>Row Count</th></tr>';

$all_exist = true;
foreach ($tables as $name => $table_name) {
    $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) == $table_name;
    $row_count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}") : 0;
    
    if (!$exists) {
        $all_exist = false;
    }
    
    echo '<tr>';
    echo '<td>' . esc_html($table_name) . '</td>';
    echo '<td class="' . ($exists ? 'success' : 'error') . '">' . ($exists ? '✅ Exists' : '❌ Missing') . '</td>';
    echo '<td>' . ($exists ? esc_html($row_count) : 'N/A') . '</td>';
    echo '</tr>';
}
echo '</table>';

// If tables are missing, try to create them
if (!$all_exist) {
    echo '<h2>Attempting to Create Missing Tables</h2>';
    
    try {
        require_once(AAA_PLUGIN_DIR . 'includes/class-database.php');
        $database = new AAA_Database();
        $results = $database->create_tables();
        
        echo '<p>Table creation attempted. Results:</p>';
        echo '<ul>';
        foreach ($results as $table_name => $created) {
            $status = $created ? '✅ Created' : '❌ Failed';
            $class = $created ? 'success' : 'error';
            echo '<li class="' . $class . '">' . esc_html($table_name) . ': ' . $status . '</li>';
        }
        echo '</ul>';
        
        // Check again
        echo '<h3>Re-checking Tables</h3>';
        echo '<table>';
        echo '<tr><th>Table Name</th><th>Status</th></tr>';
        foreach ($tables as $name => $table_name) {
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) == $table_name;
            echo '<tr>';
            echo '<td>' . esc_html($table_name) . '</td>';
            echo '<td class="' . ($exists ? 'success' : 'error') . '">' . ($exists ? '✅ Exists' : '❌ Still Missing') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        if (!empty($wpdb->last_error)) {
            echo '<p class="error"><strong>Database Error:</strong> ' . esc_html($wpdb->last_error) . '</p>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error"><strong>Exception:</strong> ' . esc_html($e->getMessage()) . '</p>';
    }
}

// Check plugin options
echo '<h2>Plugin Options</h2>';
$options = array(
    'aaa_enable_for_all' => get_option('aaa_enable_for_all'),
    'aaa_test_user_ids' => get_option('aaa_test_user_ids'),
    'aaa_debug_enabled' => get_option('aaa_debug_enabled'),
    'aaa_ai_quota_limit' => get_option('aaa_ai_quota_limit')
);

echo '<table>';
echo '<tr><th>Option</th><th>Value</th></tr>';
foreach ($options as $key => $value) {
    echo '<tr>';
    echo '<td>' . esc_html($key) . '</td>';
    echo '<td>' . ($value !== false ? esc_html(var_export($value, true)) : '<span class="warning">Not set</span>') . '</td>';
    echo '</tr>';
}
echo '</table>';

// Check Katahdin AI Hub
echo '<h2>Dependencies</h2>';
if (function_exists('katahdin_ai_hub')) {
    echo '<p class="success">✅ Katahdin AI Hub is available</p>';
} else {
    echo '<p class="error">❌ Katahdin AI Hub is NOT available</p>';
}

// Check personalities
echo '<h2>Personalities</h2>';
$plugin = academy_ai_assistant();
if ($plugin && isset($plugin->personality_manager)) {
    $manager = $plugin->personality_manager;
    $personalities = $manager->get_all_personality_metadata();
    
    if (empty($personalities)) {
        echo '<p class="warning">⚠️ No personalities registered</p>';
    } else {
        echo '<p class="success">✅ Found ' . count($personalities) . ' personality(ies)</p>';
        echo '<ul>';
        foreach ($personalities as $id => $meta) {
            echo '<li><strong>' . esc_html($meta['name']) . '</strong> (' . esc_html($id) . ')</li>';
        }
        echo '</ul>';
    }
} else {
    echo '<p class="error">❌ Personality manager not available</p>';
}

echo '<hr>';
echo '<p><a href="' . admin_url('admin.php?page=academy-ai-assistant') . '">Go to AI Assistant Admin</a></p>';

