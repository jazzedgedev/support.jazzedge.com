<?php
/**
 * Simple AJAX test for Fluent Support AI Integration
 * This file tests if the AJAX endpoints are working
 */

// Load WordPress
require_once('../../../wp-config.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. Admin privileges required.');
}

echo '<h1>Fluent Support AI Integration - AJAX Test</h1>';

// Test AJAX endpoint
$ajax_url = admin_url('admin-ajax.php');
echo '<h2>AJAX Endpoint Test</h2>';
echo '<p><strong>AJAX URL:</strong> ' . $ajax_url . '</p>';

// Test nonce generation
$nonce = wp_create_nonce('fluent_support_ai_nonce');
echo '<p><strong>Nonce:</strong> ' . $nonce . '</p>';

// Test if the AJAX action is registered
global $wp_filter;
$ajax_actions = array(
    'fluent_support_ai_save_api_key',
    'fluent_support_ai_test_api',
    'fluent_support_ai_generate_reply',
    'fluent_support_ai_save_prompt',
    'fluent_support_ai_delete_prompt'
);

echo '<h2>AJAX Actions Registration</h2>';
foreach ($ajax_actions as $action) {
    $hook_name = 'wp_ajax_' . $action;
    if (isset($wp_filter[$hook_name])) {
        echo '<p style="color: green;">✅ ' . $action . ' is registered</p>';
    } else {
        echo '<p style="color: red;">❌ ' . $action . ' is NOT registered</p>';
    }
}

// Test JavaScript loading
echo '<h2>JavaScript Test</h2>';
echo '<p>Open browser console and check for JavaScript errors when clicking the save button.</p>';

// Test form HTML
echo '<h2>Form Test</h2>';
echo '<p>Check if the form elements exist:</p>';
echo '<ul>';
echo '<li>Button ID: save-api-key</li>';
echo '<li>Input ID: openai_api_key</li>';
echo '<li>Result ID: api-test-result</li>';
echo '</ul>';

echo '<h2>Manual Test</h2>';
echo '<p>Try this manual AJAX test:</p>';
echo '<button onclick="testSaveAPI()" class="button">Test Save API Key</button>';
echo '<div id="test-result"></div>';

?>
<script>
function testSaveAPI() {
    var testKey = 'sk-test12345678901234567890123456789012345678901234567890';
    
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: {
            action: 'fluent_support_ai_save_api_key',
            api_key: testKey,
            nonce: '<?php echo $nonce; ?>'
        },
        success: function(response) {
            console.log('Manual test success:', response);
            jQuery('#test-result').html('<p style="color: green;">✅ Manual test successful: ' + JSON.stringify(response) + '</p>');
        },
        error: function(xhr, status, error) {
            console.log('Manual test error:', xhr, status, error);
            jQuery('#test-result').html('<p style="color: red;">❌ Manual test failed: ' + error + '</p>');
        }
    });
}
</script>

<p><a href="<?php echo admin_url('admin.php?page=fluent-support-ai-settings'); ?>">← Back to Settings Page</a></p>
