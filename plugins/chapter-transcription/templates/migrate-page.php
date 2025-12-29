<?php
/**
 * Migrate Embeddings admin page template
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$embeddings_table = $wpdb->prefix . 'alm_transcript_embeddings';

// Get local count
$local_count = $wpdb->get_var("SELECT COUNT(*) FROM {$embeddings_table}");

// Check if migration is in progress
$migration_status = get_transient('ct_migration_status');
$is_migrating = ($migration_status && isset($migration_status['in_progress']) && $migration_status['in_progress']);

// Get nonce
$nonce = wp_create_nonce('ct_transcription_nonce');
?>
<div class="wrap">
    <h1>Migrate Embeddings to WPEngine</h1>
    
    <div class="notice notice-info">
        <p><strong>What this does:</strong> This tool transfers all embeddings data from your local database to your WPEngine server using REST API endpoints. The migration runs in batches to avoid timeouts.</p>
    </div>
    
    <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 5px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <h2>Local Server Statistics</h2>
        <table class="widefat" style="margin-top: 10px;">
            <tbody>
                <tr>
                    <td style="width: 300px;"><strong>Total embeddings on local server:</strong></td>
                    <td><?php echo number_format($local_count); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <?php if (!$is_migrating): ?>
        <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 5px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2>Migration Settings</h2>
            <form id="ct-migration-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="dest_url">WPEngine URL</label>
                        </th>
                        <td>
                            <input type="url" id="dest_url" name="dest_url" class="regular-text" 
                                   placeholder="https://yoursite.wpengine.com" required>
                            <p class="description">The full URL of your WPEngine site (must include https://)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="dest_user">WPEngine Username</label>
                        </th>
                        <td>
                            <input type="text" id="dest_user" name="dest_user" class="regular-text" required>
                            <p class="description">WordPress admin username on WPEngine</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="dest_pass">Application Password</label>
                        </th>
                        <td>
                            <input type="password" id="dest_pass" name="dest_pass" class="regular-text" required>
                            <p class="description">WordPress Application Password (created in WPEngine admin: Users > Profile > Application Passwords)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="batch_size">Batch Size</label>
                        </th>
                        <td>
                            <input type="number" id="batch_size" name="batch_size" value="100" min="10" max="1000" class="small-text">
                            <p class="description">Number of records to transfer per batch (default: 100)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="overwrite">Overwrite Existing</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="overwrite" name="overwrite" value="1">
                                Overwrite existing embeddings on destination (if unchecked, existing records will be skipped)
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary button-large" id="ct-start-migration">
                        Start Migration
                    </button>
                </p>
            </form>
        </div>
    <?php else: ?>
        <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 5px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2>Migration in Progress</h2>
            
            <div id="ct-migration-progress" style="margin: 20px 0;">
                <div style="background: #f0f0f1; border-radius: 4px; height: 30px; position: relative; overflow: hidden;">
                    <div id="ct-progress-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                        <span id="ct-progress-text">0%</span>
                    </div>
                </div>
                <p id="ct-progress-details" style="margin-top: 10px; font-size: 13px;"></p>
            </div>
            
            <table class="widefat" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th>Statistic</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Total Transferred</strong></td>
                        <td id="ct-stat-transferred">0</td>
                    </tr>
                    <tr>
                        <td><strong>Inserted</strong></td>
                        <td id="ct-stat-inserted">0</td>
                    </tr>
                    <tr>
                        <td><strong>Updated</strong></td>
                        <td id="ct-stat-updated">0</td>
                    </tr>
                    <tr>
                        <td><strong>Skipped</strong></td>
                        <td id="ct-stat-skipped">0</td>
                    </tr>
                    <tr>
                        <td><strong>Errors</strong></td>
                        <td id="ct-stat-errors">0</td>
                    </tr>
                    <tr id="ct-table-info-row" style="display: none;">
                        <td><strong>Table Being Updated</strong></td>
                        <td id="ct-table-name" style="font-family: monospace; font-size: 12px;">-</td>
                    </tr>
                    <tr id="ct-table-count-row" style="display: none;">
                        <td><strong>Total Records in Table</strong></td>
                        <td id="ct-table-count">-</td>
                    </tr>
                </tbody>
            </table>
            
            <div id="ct-migration-errors" style="margin-top: 20px; display: none;">
                <h3>Errors</h3>
                <div style="background: #f0f0f1; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                    <ul id="ct-errors-list" style="margin: 0; padding-left: 20px;"></ul>
                </div>
            </div>
            
            <p class="submit" style="margin-top: 20px;">
                <button type="button" class="button button-secondary" id="ct-stop-migration">
                    Stop Migration
                </button>
            </p>
        </div>
    <?php endif; ?>
    
    <div id="ct-migration-complete" style="display: none; background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin: 20px 0;">
        <h3 style="margin-top: 0; color: #155724;">✓ Migration Complete!</h3>
        <p id="ct-complete-message"></p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var migrationInterval = null;
    var isProcessing = false;
    
    // Start migration
    $('#ct-migration-form').on('submit', function(e) {
        e.preventDefault();
        
        if (isProcessing) return;
        
        var formData = {
            action: 'ct_migrate_embeddings',
            nonce: '<?php echo esc_js($nonce); ?>',
            dest_url: $('#dest_url').val(),
            dest_user: $('#dest_user').val(),
            dest_pass: $('#dest_pass').val(),
            batch_size: $('#batch_size').val(),
            overwrite: $('#overwrite').is(':checked') ? 1 : 0
        };
        
        isProcessing = true;
        $('#ct-start-migration').prop('disabled', true).text('Starting...');
        
        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + (response.data.message || 'Unknown error'));
                isProcessing = false;
                $('#ct-start-migration').prop('disabled', false).text('Start Migration');
            }
        });
    });
    
    // Check migration status
    function checkMigrationStatus() {
        if (isProcessing) return;
        
        isProcessing = true;
        
        $.post(ajaxurl, {
            action: 'ct_get_migration_status',
            nonce: '<?php echo esc_js($nonce); ?>'
        }, function(response) {
            isProcessing = false;
            
            if (response.success) {
                var status = response.data.status;
                var batch = response.data.batch_result;
                
                // Update progress
                var total = status.total_transferred || 0;
                var localTotal = <?php echo (int) $local_count; ?>;
                var percent = localTotal > 0 ? Math.min(100, Math.round((total / localTotal) * 100)) : 0;
                
                $('#ct-progress-bar').css('width', percent + '%');
                $('#ct-progress-text').text(percent + '%');
                $('#ct-progress-details').text('Transferred ' + total.toLocaleString() + ' of ' + localTotal.toLocaleString() + ' records');
                
                // Update stats
                $('#ct-stat-transferred').text((status.total_transferred || 0).toLocaleString());
                $('#ct-stat-inserted').text((status.total_inserted || 0).toLocaleString());
                $('#ct-stat-updated').text((status.total_updated || 0).toLocaleString());
                $('#ct-stat-skipped').text((status.total_skipped || 0).toLocaleString());
                $('#ct-stat-errors').text((status.errors ? status.errors.length : 0).toLocaleString());
                
                // Show table info if available
                if (batch.table_used) {
                    $('#ct-table-info-row').show();
                    $('#ct-table-name').text(batch.table_used);
                }
                if (batch.table_count_after !== null && batch.table_count_after !== undefined) {
                    $('#ct-table-count-row').show();
                    $('#ct-table-count').text(batch.table_count_after.toLocaleString());
                }
                
                // Show errors if any
                if (status.errors && status.errors.length > 0) {
                    $('#ct-migration-errors').show();
                    var errorsHtml = '';
                    status.errors.slice(0, 10).forEach(function(error) {
                        errorsHtml += '<li>' + error + '</li>';
                    });
                    if (status.errors.length > 10) {
                        errorsHtml += '<li><em>... and ' + (status.errors.length - 10) + ' more errors</em></li>';
                    }
                    $('#ct-errors-list').html(errorsHtml);
                }
                
                // Check if complete or stopped
                if (response.data.complete || !status.in_progress || response.data.stopped) {
                    clearInterval(migrationInterval);
                    $('#ct-migration-complete').show();
                    
                    if (response.data.stopped) {
                        $('#ct-complete-message').html(
                            '<strong>Migration Stopped</strong><br>' +
                            'Transferred <strong>' + (status.total_transferred || 0).toLocaleString() + '</strong> embeddings before stopping.<br>' +
                            'Inserted: ' + (status.total_inserted || 0).toLocaleString() + ', ' +
                            'Updated: ' + (status.total_updated || 0).toLocaleString() + ', ' +
                            'Skipped: ' + (status.total_skipped || 0).toLocaleString()
                        );
                    } else {
                        $('#ct-complete-message').html(
                            'Successfully transferred <strong>' + (status.total_transferred || 0).toLocaleString() + '</strong> embeddings.<br>' +
                            'Inserted: ' + (status.total_inserted || 0).toLocaleString() + ', ' +
                            'Updated: ' + (status.total_updated || 0).toLocaleString() + ', ' +
                            'Skipped: ' + (status.total_skipped || 0).toLocaleString()
                        );
                    }
                    $('#ct-stop-migration').hide();
                }
            } else {
                clearInterval(migrationInterval);
                alert('Error: ' + (response.data.message || 'Unknown error'));
            }
        });
    }
    
    // Stop migration
    $('#ct-stop-migration').on('click', function() {
        if (!confirm('Are you sure you want to stop the migration? The current batch will complete, then migration will halt.')) {
            return;
        }
        
        $.post(ajaxurl, {
            action: 'ct_stop_migration',
            nonce: ctAdmin.nonce
        }, function(response) {
            if (response.success) {
                alert('Migration stop signal sent. It will stop after the current batch completes.');
            } else {
                alert('Error: ' + (response.data.message || 'Unknown error'));
            }
        });
    });
    
    // Start checking status if migration is in progress
    <?php if ($is_migrating): ?>
    migrationInterval = setInterval(checkMigrationStatus, 2000); // Check every 2 seconds
    checkMigrationStatus(); // Check immediately
    <?php endif; ?>
});
</script>

