<?php
/**
 * Simple activation test for Fluent Support AI Integration
 * This file can be accessed directly to test if the plugin is working
 */

// Load WordPress
require_once('../../../wp-config.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. Admin privileges required.');
}

echo '<h1>Fluent Support AI Integration - Activation Test</h1>';

// Check if plugin is active
if (!class_exists('FluentSupportAI')) {
    echo '<p style="color: red;">❌ Plugin class not found. Plugin may not be active.</p>';
} else {
    echo '<p style="color: green;">✅ Plugin class found.</p>';
}

// Check if classes are loaded
$classes_to_check = array(
    'FluentSupportAI_Settings',
    'FluentSupportAI_Prompt_Manager', 
    'FluentSupportAI_OpenAI_Client',
    'FluentSupportAI_Reply_Generator'
);

foreach ($classes_to_check as $class) {
    if (class_exists($class)) {
        echo '<p style="color: green;">✅ ' . $class . ' loaded</p>';
    } else {
        echo '<p style="color: red;">❌ ' . $class . ' not loaded</p>';
    }
}

// Check database table
global $wpdb;
$table_name = $wpdb->prefix . 'fluent_support_ai_prompts';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

if ($table_exists) {
    echo '<p style="color: green;">✅ Database table exists</p>';
} else {
    echo '<p style="color: red;">❌ Database table does not exist</p>';
}

// Check admin menu
$admin_url = admin_url('admin.php?page=fluent-support-ai-settings');
echo '<h2>Admin Menu Test</h2>';
echo '<p>Settings page should be available at: <a href="' . $admin_url . '" target="_blank">' . $admin_url . '</a></p>';

// Check if Fluent Support is active
if (class_exists('FluentSupport') || function_exists('fluentSupport')) {
    echo '<p style="color: green;">✅ Fluent Support detected</p>';
} else {
    echo '<p style="color: orange;">⚠️ Fluent Support not detected (plugin may still work)</p>';
}

echo '<h2>Plugin Information</h2>';
echo '<p><strong>Plugin Version:</strong> ' . (defined('FLUENT_SUPPORT_AI_VERSION') ? FLUENT_SUPPORT_AI_VERSION : 'Not defined') . '</p>';
echo '<p><strong>Plugin Directory:</strong> ' . (defined('FLUENT_SUPPORT_AI_PLUGIN_DIR') ? FLUENT_SUPPORT_AI_PLUGIN_DIR : 'Not defined') . '</p>';

echo '<h2>Next Steps</h2>';
echo '<ol>';
echo '<li>If all checks pass, go to WordPress Admin → Fluent Support AI</li>';
echo '<li>Enter your OpenAI API key</li>';
echo '<li>Test the API connection</li>';
echo '<li>Create or use default prompts</li>';
echo '<li>Try generating AI replies in tickets</li>';
echo '</ol>';

echo '<p><a href="' . admin_url() . '">← Back to WordPress Admin</a></p>';
?>
