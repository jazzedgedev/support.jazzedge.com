<?php
/**
 * Context Builder Test for Academy AI Assistant
 * 
 * Run this file directly to test context building and embedding search
 * Access: /wp-content/plugins/academy-ai-assistant/test-context.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. Admin privileges required.');
}

echo '<h1>Academy AI Assistant - Context Builder Test</h1>';
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
    table { border-collapse: collapse; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>';

// Check if plugin is active
if (!class_exists('Academy_AI_Assistant')) {
    echo '<p class="error">❌ Plugin class not found. Plugin may not be active.</p>';
    exit;
}

$plugin = academy_ai_assistant();
$user_id = get_current_user_id();

echo '<h2>Context Builder Test</h2>';

// Test context building
if (isset($plugin->context_builder)) {
    $context_builder = $plugin->context_builder;
    
    echo '<h3>Building Context for User ID: ' . esc_html($user_id) . '</h3>';
    
    $test_query = "How do I play a minor 7th chord?";
    $context = $context_builder->build_context($user_id, $test_query);
    
    echo '<h4>Raw Context Data:</h4>';
    echo '<pre>' . esc_html(print_r($context, true)) . '</pre>';
    
    echo '<h4>Formatted Context for AI:</h4>';
    $formatted = $context_builder->format_context_for_ai($context);
    echo '<pre>' . esc_html($formatted) . '</pre>';
    
    // Test current page context
    echo '<h4>Current Page Context:</h4>';
    $page_context = $context_builder->get_current_page_context();
    echo '<pre>' . esc_html(print_r($page_context, true)) . '</pre>';
    
} else {
    echo '<p class="error">❌ Context Builder not available</p>';
}

echo '<hr>';

// Test embedding search
echo '<h2>Embedding Search Test</h2>';

if (isset($plugin->embedding_search)) {
    $embedding_search = $plugin->embedding_search;
    
    // Check availability
    $available = $embedding_search->is_available();
    echo '<p class="' . ($available ? 'success' : 'warning') . '">';
    echo $available ? '✅ Embedding search is available' : '⚠️ Embedding search is not available (tables may not exist)';
    echo '</p>';
    
    // Check individual tables
    global $wpdb;
    $embeddings_table = $wpdb->prefix . 'alm_transcript_embeddings';
    $transcripts_table = $wpdb->prefix . 'alm_transcripts';
    $embeddings_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $embeddings_table)) == $embeddings_table;
    $transcripts_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $transcripts_table)) == $transcripts_table;
    
    echo '<h3>Table Status:</h3>';
    echo '<table>';
    echo '<tr><th>Table</th><th>Status</th><th>Row Count</th></tr>';
    echo '<tr><td>' . esc_html($embeddings_table) . '</td><td class="' . ($embeddings_exists ? 'success' : 'error') . '">' . ($embeddings_exists ? '✅ Exists' : '❌ Missing') . '</td><td>' . ($embeddings_exists ? esc_html($wpdb->get_var("SELECT COUNT(*) FROM {$embeddings_table}")) : 'N/A') . '</td></tr>';
    echo '<tr><td>' . esc_html($transcripts_table) . '</td><td class="' . ($transcripts_exists ? 'success' : 'error') . '">' . ($transcripts_exists ? '✅ Exists' : '❌ Missing') . '</td><td>' . ($transcripts_exists ? esc_html($wpdb->get_var("SELECT COUNT(*) FROM {$transcripts_table}")) : 'N/A') . '</td></tr>';
    echo '</table>';
    
    if ($available) {
        // Get statistics
        $stats = $embedding_search->get_statistics();
        echo '<h3>Embedding Statistics:</h3>';
        echo '<table>';
        echo '<tr><th>Metric</th><th>Value</th></tr>';
        echo '<tr><td>Available</td><td>' . ($stats['available'] ? 'Yes' : 'No') . '</td></tr>';
        echo '<tr><td>Total Embeddings</td><td>' . esc_html($stats['total_embeddings']) . '</td></tr>';
        echo '<tr><td>Total Transcripts</td><td>' . esc_html($stats['total_transcripts']) . '</td></tr>';
        echo '</table>';
        
        // Test search (only if embeddings exist)
        if ($stats['total_embeddings'] > 0) {
            echo '<h3>Test Search</h3>';
            echo '<p class="info">Testing search for: "How do I play a minor 7th chord?"</p>';
            
            $start_time = microtime(true);
            $results = $embedding_search->search("How do I play a minor 7th chord?", 5, 0.7);
            $search_time = round((microtime(true) - $start_time) * 1000);
            
            echo '<p>Search completed in ' . esc_html($search_time) . 'ms</p>';
            
            if (empty($results)) {
                echo '<p class="warning">⚠️ No results found (this is OK if no matching content exists)</p>';
            } else {
                echo '<p class="success">✅ Found ' . count($results) . ' result(s)</p>';
                echo '<table>';
                echo '<tr><th>Similarity</th><th>Lesson</th><th>Segment Text</th><th>Time</th><th>URL</th></tr>';
                foreach ($results as $result) {
                    echo '<tr>';
                    echo '<td>' . number_format($result['similarity'], 3) . '</td>';
                    echo '<td>' . esc_html($result['lesson_title'] ?? 'N/A') . '</td>';
                    echo '<td>' . esc_html(wp_trim_words($result['segment_text'], 15)) . '</td>';
                    echo '<td>' . esc_html($result['start_time']) . 's</td>';
                    echo '<td><a href="' . esc_url($result['timestamp_url'] ?? '#') . '" target="_blank">View</a></td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        } else {
            echo '<p class="warning">⚠️ No embeddings found in database. Embedding search will not work until transcripts are processed.</p>';
        }
    }
} else {
    echo '<p class="error">❌ Embedding Search not available</p>';
}

echo '<hr>';
echo '<p><a href="' . admin_url('admin.php?page=academy-ai-assistant') . '">Go to AI Assistant Admin</a></p>';

