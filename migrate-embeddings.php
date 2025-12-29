<?php
/**
 * Embeddings Migration Script
 * 
 * Transfers wp_alm_transcript_embeddings data from LOCAL server to WPEngine destination server
 * 
 * SETUP:
 *   - Place this file on your LOCAL server (where the database with embeddings is located)
 *   - Ensure chapter-transcription plugin is active on LOCAL server (for export endpoints)
 *   - Ensure academy-ai-assistant plugin is active on WPEngine server (for import endpoints)
 *   - Create Application Passwords on both servers (Users > Profile > Application Passwords)
 * 
 * Usage:
 *   php migrate-embeddings.php
 * 
 * Or via web browser (requires authentication):
 *   http://localhost/migrate-embeddings.php
 * 
 * Configuration:
 *   Set SOURCE_URL (your local server), DEST_URL (WPEngine), and authentication below
 */

// Configuration - UPDATE THESE VALUES
define('SOURCE_URL', 'http://localhost');  // Your LOCAL server URL (where database is)
define('DEST_URL', 'https://yoursite.wpengine.com');  // WPEngine destination URL
define('SOURCE_AUTH_USER', 'admin');  // WordPress admin username for source
define('SOURCE_AUTH_PASS', 'password');  // Application password for source
define('DEST_AUTH_USER', 'admin');  // WordPress admin username for destination
define('DEST_AUTH_PASS', 'password');  // Application password for destination
define('BATCH_SIZE', 100);  // Number of records per batch
define('OVERWRITE_EXISTING', false);  // Set to true to overwrite existing records

// WordPress bootstrap (if running from WordPress root)
if (file_exists(__DIR__ . '/wp-load.php')) {
    require_once(__DIR__ . '/wp-load.php');
}

/**
 * Get authentication credentials for WordPress REST API
 * Uses Application Passwords (WordPress 5.6+) or Basic Auth
 * 
 * Note: For Application Passwords, use the format: username:application_password
 * You can create Application Passwords in WordPress Admin > Users > Your Profile > Application Passwords
 * 
 * Returns username:password string for Basic Auth
 */
function get_auth_credentials($username, $password) {
    // If password contains colon, it's already in username:app_password format
    // Otherwise, assume it's an application password and combine with username
    if (strpos($password, ':') === false) {
        return $username . ':' . $password;
    }
    return $password; // Already in username:password format
}

/**
 * Make authenticated REST API request using Basic Auth
 * Uses Application Passwords for WordPress authentication
 */
function make_rest_request($url, $method = 'GET', $data = null, $auth_credentials = null) {
    $ch = curl_init();
    
    $headers = array(
        'Content-Type: application/json',
        'Accept: application/json'
    );
    
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ));
    
    // Add Basic Auth if credentials provided
    if ($auth_credentials) {
        curl_setopt($ch, CURLOPT_USERPWD, $auth_credentials);
    }
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return array('error' => $error, 'http_code' => 0);
    }
    
    $decoded = json_decode($response, true);
    
    return array(
        'data' => $decoded,
        'http_code' => $http_code,
        'raw' => $response
    );
}

/**
 * Main migration function
 */
function migrate_embeddings() {
    echo "=== Embeddings Migration Script ===\n\n";
    
    // Step 1: Get source count
    echo "Step 1: Checking source server...\n";
    // Try chapter-transcription endpoint first (local server), fallback to academy-ai-assistant
    $source_count_url = SOURCE_URL . '/wp-json/chapter-transcription/v1/embeddings/count';
    $source_auth = get_auth_credentials(SOURCE_AUTH_USER, SOURCE_AUTH_PASS);
    
    $source_count_response = make_rest_request($source_count_url, 'GET', null, $source_auth);
    
    // Fallback to academy-ai-assistant endpoint if chapter-transcription not available
    if ($source_count_response['http_code'] !== 200) {
        $source_count_url = SOURCE_URL . '/wp-json/academy-ai-assistant/v1/embeddings/count';
        $source_count_response = make_rest_request($source_count_url, 'GET', null, $source_auth);
    }
    
    if ($source_count_response['http_code'] !== 200 || !isset($source_count_response['data']['total_count'])) {
        die("ERROR: Failed to get source count. Response: " . print_r($source_count_response, true) . "\n");
    }
    
    $total_records = $source_count_response['data']['total_count'];
    echo "Found {$total_records} total embeddings on source server.\n\n";
    
    // Step 2: Get destination count
    echo "Step 2: Checking destination server...\n";
    $dest_count_url = DEST_URL . '/wp-json/academy-ai-assistant/v1/embeddings/count';
    $dest_auth = get_auth_credentials(DEST_AUTH_USER, DEST_AUTH_PASS);
    
    $dest_count_response = make_rest_request($dest_count_url, 'GET', null, $dest_auth);
    
    if ($dest_count_response['http_code'] !== 200) {
        echo "WARNING: Could not get destination count (table may not exist yet).\n";
        $dest_count = 0;
    } else {
        $dest_count = isset($dest_count_response['data']['total_count']) ? $dest_count_response['data']['total_count'] : 0;
        echo "Found {$dest_count} existing embeddings on destination server.\n\n";
    }
    
    // Step 3: Transfer data in batches
    echo "Step 3: Starting data transfer...\n";
    echo "Batch size: " . BATCH_SIZE . "\n";
    echo "Overwrite existing: " . (OVERWRITE_EXISTING ? 'Yes' : 'No') . "\n\n";
    
    $offset = 0;
    $total_transferred = 0;
    $total_inserted = 0;
    $total_updated = 0;
    $total_skipped = 0;
    $batch_number = 0;
    
    // Stop file path (create this file to stop migration)
    $stop_file = sys_get_temp_dir() . '/migrate-embeddings-stop.txt';
    
    // Remove any existing stop file
    if (file_exists($stop_file)) {
        @unlink($stop_file);
    }
    
    echo "To stop migration, create this file: {$stop_file}\n";
    echo "Or press Ctrl+C if running from command line.\n\n";
    
    while (true) {
        // Check for stop signal
        if (file_exists($stop_file)) {
            echo "\n=== Migration Stopped by User ===\n";
            echo "Stop file detected. Migration halted.\n";
            @unlink($stop_file);
            break;
        }
        
        $batch_number++;
        echo "Processing batch #{$batch_number} (offset: {$offset})...\n";
        
        // Fetch batch from source
        // Try chapter-transcription endpoint first (local server), fallback to academy-ai-assistant
        $export_url = SOURCE_URL . '/wp-json/chapter-transcription/v1/embeddings/export?' . http_build_query(array(
            'batch_size' => BATCH_SIZE,
            'offset' => $offset
        ));
        
        $export_response = make_rest_request($export_url, 'GET', null, $source_auth);
        
        // Fallback to academy-ai-assistant endpoint if chapter-transcription not available
        if ($export_response['http_code'] !== 200) {
            $export_url = SOURCE_URL . '/wp-json/academy-ai-assistant/v1/embeddings/export?' . http_build_query(array(
                'batch_size' => BATCH_SIZE,
                'offset' => $offset
            ));
            $export_response = make_rest_request($export_url, 'GET', null, $source_auth);
        }
        
        if ($export_response['http_code'] !== 200) {
            die("ERROR: Failed to export batch. Response: " . print_r($export_response, true) . "\n");
        }
        
        $export_data = $export_response['data'];
        
        if (empty($export_data['data'])) {
            echo "No more data to transfer.\n";
            break;
        }
        
        $batch_data = $export_data['data'];
        echo "  Fetched " . count($batch_data) . " records from source.\n";
        
        // Send batch to destination
        $import_url = DEST_URL . '/wp-json/academy-ai-assistant/v1/embeddings/import';
        $import_payload = array(
            'embeddings' => $batch_data,
            'overwrite' => OVERWRITE_EXISTING
        );
        
        $import_response = make_rest_request($import_url, 'POST', $import_payload, $dest_auth);
        
        if ($import_response['http_code'] !== 200) {
            die("ERROR: Failed to import batch. Response: " . print_r($import_response, true) . "\n");
        }
        
        $import_data = $import_response['data'];
        $summary = $import_data['summary'];
        
        // Show which table was used (for verification)
        if (isset($import_data['table_used'])) {
            echo "  Writing to table: " . $import_data['table_used'] . "\n";
        }
        
        echo "  Imported: " . $summary['inserted'] . " inserted, " . $summary['updated'] . " updated, " . $summary['skipped'] . " skipped\n";
        
        // Show table count after import (for verification)
        if (isset($import_data['table_count_after'])) {
            echo "  Total records in table after import: " . number_format($import_data['table_count_after']) . "\n";
        }
        
        if (!empty($import_data['errors'])) {
            echo "  WARNING: " . count($import_data['errors']) . " errors occurred:\n";
            foreach (array_slice($import_data['errors'], 0, 5) as $error) {
                echo "    - {$error}\n";
            }
            if (count($import_data['errors']) > 5) {
                echo "    ... and " . (count($import_data['errors']) - 5) . " more errors\n";
            }
        }
        
        $total_transferred += count($batch_data);
        $total_inserted += $summary['inserted'];
        $total_updated += $summary['updated'];
        $total_skipped += $summary['skipped'];
        
        // Check if there's more data
        if (!$export_data['pagination']['has_more']) {
            echo "\nAll data transferred!\n";
            break;
        }
        
        $offset += BATCH_SIZE;
        echo "  Progress: {$total_transferred} / {$total_records} records (" . round(($total_transferred / $total_records) * 100, 2) . "%)\n\n";
        
        // Check for stop signal before next batch
        if (file_exists($stop_file)) {
            echo "\n=== Migration Stopped by User ===\n";
            echo "Stop file detected. Migration halted.\n";
            @unlink($stop_file);
            break;
        }
        
        // Small delay to avoid overwhelming servers
        usleep(500000); // 0.5 second delay
    }
    
    // Final summary
    echo "\n=== Migration Complete ===\n";
    echo "Total records transferred: {$total_transferred}\n";
    echo "  - Inserted: {$total_inserted}\n";
    echo "  - Updated: {$total_updated}\n";
    echo "  - Skipped: {$total_skipped}\n";
    echo "\n";
    
    // Verify final count on destination
    echo "Verifying destination...\n";
    $final_dest_response = make_rest_request($dest_count_url, 'GET', null, $dest_auth);
    if ($final_dest_response['http_code'] === 200) {
        $final_dest_count = $final_dest_response['data']['total_count'];
        echo "Destination now has {$final_dest_count} total embeddings.\n";
    }
}

// Run migration
if (php_sapi_name() === 'cli') {
    // Command line execution
    migrate_embeddings();
} else {
    // Web browser execution - require authentication
    if (!defined('ABSPATH')) {
        die('This script must be run from WordPress root or via command line.');
    }
    
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to run this migration.');
    }
    
    // Output as HTML for web browser
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Embeddings Migration</title>';
    echo '<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}pre{background:#fff;padding:15px;border:1px solid #ddd;border-radius:4px;}</style>';
    echo '</head><body><h1>Embeddings Migration</h1><pre>';
    ob_start();
    migrate_embeddings();
    $output = ob_get_clean();
    echo htmlspecialchars($output);
    echo '</pre></body></html>';
}

