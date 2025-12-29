<?php
/**
 * Stop Migration Script
 * 
 * Creates a stop file to halt the embeddings migration process
 * 
 * Usage:
 *   php stop-migration.php
 * 
 * Or via web browser:
 *   http://localhost/stop-migration.php
 */

// WordPress bootstrap (if running from WordPress root)
if (file_exists(__DIR__ . '/wp-load.php')) {
    require_once(__DIR__ . '/wp-load.php');
}

$stop_file = sys_get_temp_dir() . '/migrate-embeddings-stop.txt';

if (file_exists($stop_file)) {
    $message = "Migration stop signal already exists. Migration should stop on next batch check.";
    $status = "already_exists";
} else {
    // Create stop file
    if (file_put_contents($stop_file, date('Y-m-d H:i:s') . "\nMigration stop requested\n")) {
        $message = "Migration stop signal created. The migration will stop after the current batch completes.";
        $status = "created";
    } else {
        $message = "ERROR: Could not create stop file. Check file permissions.";
        $status = "error";
    }
}

// Output
if (php_sapi_name() === 'cli') {
    // Command line execution
    echo "=== Stop Migration ===\n";
    echo "Stop file: {$stop_file}\n";
    echo "Status: {$status}\n";
    echo "Message: {$message}\n";
} else {
    // Web browser execution
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Stop Migration</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            h1 {
                margin-top: 0;
                color: #333;
            }
            .status {
                padding: 15px;
                border-radius: 4px;
                margin: 20px 0;
            }
            .status.created {
                background: #fff3cd;
                border: 1px solid #ffc107;
                color: #856404;
            }
            .status.already_exists {
                background: #d1ecf1;
                border: 1px solid #bee5eb;
                color: #0c5460;
            }
            .status.error {
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
            }
            .file-path {
                background: #f8f9fa;
                padding: 10px;
                border-radius: 4px;
                font-family: monospace;
                font-size: 12px;
                margin: 10px 0;
                word-break: break-all;
            }
            .note {
                background: #e7f3ff;
                padding: 15px;
                border-left: 4px solid #2271b1;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Stop Embeddings Migration</h1>
            
            <div class="status <?php echo esc_attr($status); ?>">
                <strong>Status:</strong> <?php echo esc_html(ucfirst(str_replace('_', ' ', $status))); ?><br>
                <?php echo esc_html($message); ?>
            </div>
            
            <div class="file-path">
                <strong>Stop File Location:</strong><br>
                <?php echo esc_html($stop_file); ?>
            </div>
            
            <div class="note">
                <strong>Note:</strong> The migration will stop after the current batch completes processing. 
                This may take a few seconds depending on the batch size.
            </div>
            
            <p>
                <a href="javascript:location.reload();" class="button">Refresh Status</a>
            </p>
        </div>
    </body>
    </html>
    <?php
}

