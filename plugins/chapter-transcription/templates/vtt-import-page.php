<?php
/**
 * VTT Segments Import admin page template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>Import VTT Segments</h1>
    
    <div class="notice notice-info">
        <p><strong>What this does:</strong> This tool reads existing VTT files from the <code>alm_transcriptions</code> folder and extracts timestamped segments, storing them in the database for use with vector search. <strong>No existing data will be modified or deleted.</strong> Only the new <code>vtt_segments</code> column will be populated.</p>
    </div>
    
    <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 5px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <h2>Statistics</h2>
        <table class="widefat" style="margin-top: 10px;">
            <tbody>
                <tr>
                    <td><strong>Total transcripts with VTT files:</strong></td>
                    <td><?php echo number_format($total_with_vtt); ?></td>
                </tr>
                <tr>
                    <td><strong>Already have segments imported:</strong></td>
                    <td><?php echo number_format($total_with_segments); ?></td>
                </tr>
                <tr>
                    <td><strong>Need import:</strong></td>
                    <td><strong style="color: #d63638;"><?php echo number_format($total_needs_import); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 5px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <h2>Import Process</h2>
        
        <?php if ($is_importing): ?>
            <div id="ct-vtt-import-progress" style="margin: 20px 0;">
                <p><strong>Import in progress...</strong></p>
                <div style="background: #f0f0f1; border-radius: 4px; height: 30px; margin: 10px 0; position: relative; overflow: hidden;">
                    <div id="ct-vtt-progress-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>
                    <div id="ct-vtt-progress-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: bold; color: #1d2327;">
                        Processing...
                    </div>
                </div>
                <div id="ct-vtt-status-container" style="margin: 15px 0;">
                    <p id="ct-vtt-status-text"><strong>Status:</strong> Starting import...</p>
                    <p id="ct-vtt-batch-info" style="color: #646970; font-size: 13px;"></p>
                </div>
                <div id="ct-vtt-log-container" style="background: #f6f7f7; border: 1px solid #c3c4c7; border-radius: 4px; padding: 15px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; margin: 15px 0;">
                    <div id="ct-vtt-log" style="color: #1d2327;">
                        <div>Waiting for batch to start...</div>
                    </div>
                </div>
                <button type="button" class="button" id="ct-vtt-stop-import" style="margin-top: 10px;">Stop Import</button>
            </div>
        <?php else: ?>
            <div id="ct-vtt-import-ready">
                <p>Click the button below to start importing VTT segments. The process will run in batches of 50 transcripts at a time to avoid timeouts.</p>
                <?php if ($total_needs_import > 0): ?>
                    <button type="button" class="button button-primary button-large" id="ct-vtt-start-import">
                        Start Import (<?php echo number_format($total_needs_import); ?> transcripts)
                    </button>
                <?php else: ?>
                    <p style="color: #00a32a;"><strong>All transcripts already have segments imported!</strong></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div id="ct-vtt-import-results" style="margin-top: 20px; display: none;">
            <h3>Import Results</h3>
            <div id="ct-vtt-results-content"></div>
        </div>
        
        <div id="ct-vtt-debug-info" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 15px; margin-top: 20px; display: none;">
            <h3 style="margin-top: 0;">Debug Information</h3>
            <div id="ct-vtt-debug-content" style="font-family: monospace; font-size: 12px;"></div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let importInterval = null;
    let statusInterval = null;
    let isImporting = <?php echo $is_importing ? 'true' : 'false'; ?>;
    let currentOffset = 0;
    let batchNumber = 0;
    const totalNeedsImport = <?php echo $total_needs_import; ?>;
    
    function addLog(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const colors = {
            'info': '#2271b1',
            'success': '#00a32a',
            'error': '#d63638',
            'warning': '#dba617'
        };
        const color = colors[type] || colors.info;
        const logEntry = $('<div>').html(`[${timestamp}] <span style="color: ${color};">${message}</span>`);
        $('#ct-vtt-log').prepend(logEntry);
        
        // Keep only last 50 log entries
        const logEntries = $('#ct-vtt-log > div');
        if (logEntries.length > 50) {
            logEntries.slice(50).remove();
        }
        
        // Also log to console
        console.log(`[VTT Import] ${message}`);
    }
    
    function showDebugInfo(data) {
        $('#ct-vtt-debug-info').show();
        $('#ct-vtt-debug-content').html(JSON.stringify(data, null, 2));
    }
    
    function updateProgress() {
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_get_vtt_import_status',
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                showDebugInfo({ action: 'get_status', response: response });
                
                if (response.success && response.data && response.data.in_progress) {
                    // Calculate progress percentage (rough estimate)
                    const offset = response.data.offset || 0;
                    const percent = totalNeedsImport > 0 ? Math.min(100, Math.round((offset / totalNeedsImport) * 100)) : 0;
                    
                    $('#ct-vtt-progress-bar').css('width', percent + '%');
                    $('#ct-vtt-progress-text').text(percent + '%');
                    
                    const processed = response.data.processed || 0;
                    const success = response.data.success || 0;
                    const failed = response.data.failed || 0;
                    
                    $('#ct-vtt-status-text').html(
                        `<strong>Status:</strong> Processing batch ${batchNumber}... ` +
                        `<span style="color: #00a32a;">✓ ${success} success</span> | ` +
                        `<span style="color: #d63638;">✗ ${failed} failed</span> | ` +
                        `<span>${processed} total processed</span>`
                    );
                    
                    $('#ct-vtt-batch-info').text(
                        `Offset: ${offset} / ${totalNeedsImport} transcripts | ` +
                        `Estimated remaining: ${Math.max(0, totalNeedsImport - offset)}`
                    );
                } else {
                    // Import complete or not in progress
                    if (response.success && response.data && !response.data.in_progress) {
                        addLog('Import status check: Not in progress', 'info');
                    }
                }
            },
            error: function(xhr, status, error) {
                addLog(`Error checking status: ${error}`, 'error');
                showDebugInfo({ action: 'get_status_error', error: error, xhr: xhr });
            }
        });
    }
    
    function processBatch() {
        batchNumber++;
        addLog(`Starting batch #${batchNumber} (offset: ${currentOffset})`, 'info');
        
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_import_vtt_segments',
                nonce: ctAdmin.nonce,
                offset: currentOffset
            },
            beforeSend: function() {
                addLog(`Sending AJAX request for batch #${batchNumber}...`, 'info');
            },
            success: function(response) {
                showDebugInfo({ action: 'import_batch', batch: batchNumber, response: response });
                
                if (response.success) {
                    if (response.data.complete) {
                        // Check if it was stopped vs actually complete
                        if (response.data.stopped) {
                            clearInterval(importInterval);
                            clearInterval(statusInterval);
                            isImporting = false;
                            addLog('Import stopped by user', 'warning');
                            $('#ct-vtt-status-text').html('<strong>Status:</strong> <span style="color: #d63638;">Import stopped</span>');
                            $('#ct-vtt-start-import').prop('disabled', false).text('Start Import');
                        } else {
                            // All done!
                            clearInterval(importInterval);
                            clearInterval(statusInterval);
                            isImporting = false;
                            addLog('Import complete! All batches processed.', 'success');
                            $('#ct-vtt-import-progress').html(
                                '<div class="notice notice-success"><p><strong>Import complete!</strong> All VTT segments have been imported successfully.</p></div>'
                            );
                            setTimeout(() => location.reload(), 3000);
                        }
                        return;
                    } else {
                        // Continue with next batch
                        const processed = response.data.processed || 0;
                        const success = response.data.success || 0;
                        const failed = response.data.failed || 0;
                        
                        addLog(`Batch #${batchNumber} complete: ${success} success, ${failed} failed, ${processed} processed`, 
                            failed > 0 ? 'warning' : 'success');
                        
                        if (response.data.errors && response.data.errors.length > 0) {
                            response.data.errors.forEach(function(error) {
                                addLog(`Error: ${error}`, 'error');
                            });
                        }
                        
                        currentOffset = response.data.offset;
                        
                        // Process next batch after short delay
                        setTimeout(processBatch, 1000);
                    }
                } else {
                    clearInterval(importInterval);
                    clearInterval(statusInterval);
                    const errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error';
                    addLog(`Batch failed: ${errorMsg}`, 'error');
                    alert('Error: ' + errorMsg);
                }
            },
            error: function(xhr, status, error) {
                clearInterval(importInterval);
                clearInterval(statusInterval);
                addLog(`AJAX error: ${error} (Status: ${status})`, 'error');
                showDebugInfo({ action: 'import_batch_error', error: error, status: status, xhr: xhr });
                alert('AJAX error occurred: ' + error + '. Check the log above for details.');
            }
        });
    }
    
    $('#ct-vtt-start-import').on('click', function() {
        if (!confirm('Start importing VTT segments from existing VTT files? This may take a while depending on the number of transcripts.')) {
            return;
        }
        
        $(this).prop('disabled', true).text('Starting...');
        $('#ct-vtt-import-ready').hide();
        $('#ct-vtt-import-progress').show();
        $('#ct-vtt-debug-info').show();
        isImporting = true;
        currentOffset = 0;
        batchNumber = 0;
        
        addLog('Import started. Total transcripts to process: ' + totalNeedsImport, 'info');
        
        // Start processing
        processBatch();
        
        // Update status every 2 seconds
        statusInterval = setInterval(updateProgress, 2000);
    });
    
    $('#ct-vtt-stop-import').on('click', function() {
        if (!confirm('Stop the import process? This will clear the import status and you can start fresh.')) {
            return;
        }
        
        clearInterval(importInterval);
        clearInterval(statusInterval);
        addLog('Stopping import...', 'warning');
        
        // Call AJAX to properly stop and clear transient
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_stop_vtt_import',
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    addLog('Import stopped successfully', 'info');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    addLog('Error stopping import: ' + (response.data.message || 'Unknown error'), 'error');
                }
            },
            error: function() {
                addLog('AJAX error while stopping import', 'error');
                // Still reload to clear the UI
                setTimeout(() => location.reload(), 1000);
            }
        });
    });
    
    // Auto-update progress if import is in progress
    if (isImporting) {
        addLog('Resuming import (was already in progress)', 'info');
        statusInterval = setInterval(updateProgress, 2000);
        updateProgress();
    }
    
    // Show debug toggle
    $('<button>', {
        type: 'button',
        class: 'button',
        text: 'Toggle Debug Info',
        style: 'margin-top: 10px;',
        click: function() {
            $('#ct-vtt-debug-info').toggle();
        }
    }).insertAfter('#ct-vtt-stop-import');
});
</script>

