<?php
/**
 * Generate Embeddings admin page template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>Generate Embeddings</h1>
    
    <div class="notice notice-info">
        <p><strong>What this does:</strong> This tool generates vector embeddings for all transcript segments using OpenAI's embedding API. These embeddings enable semantic search to find topics across your 11 million words of transcripts. <strong>This process uses API credits and may take a while.</strong></p>
    </div>
    
    <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 5px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <h2>Statistics</h2>
        <table class="widefat" style="margin-top: 10px;">
            <tbody>
                <tr>
                    <td><strong>Total transcripts with segments:</strong></td>
                    <td><?php echo number_format($stats['total_transcripts']); ?></td>
                </tr>
                <tr>
                    <td><strong>Transcripts with embeddings:</strong></td>
                    <td><?php echo number_format($stats['transcripts_with_embeddings']); ?></td>
                </tr>
                <tr>
                    <td><strong>Transcripts with partial embeddings:</strong></td>
                    <td><?php echo number_format($stats['transcripts_with_partial']); ?> 
                        <?php if ($stats['transcripts_with_partial'] > 0): ?>
                            <button type="button" class="button button-small" id="ct-retry-failed" style="margin-left: 10px;">
                                Retry Failed Segments
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Total embeddings generated:</strong></td>
                    <td><?php echo number_format($stats['total_embeddings']); ?></td>
                </tr>
                <tr>
                    <td><strong>Total segments available:</strong></td>
                    <td><?php echo number_format($stats['total_segments']); ?></td>
                </tr>
                <tr>
                    <td><strong>Need embeddings:</strong></td>
                    <td><strong style="color: #d63638;"><?php echo number_format($stats['needs_embeddings']); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 5px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <h2>Generation Process</h2>
        
        <?php if ($is_generating): ?>
            <div id="ct-embeddings-progress" style="margin: 20px 0;">
                <p><strong>Generation in progress...</strong></p>
                <div style="background: #f0f0f1; border-radius: 4px; height: 30px; margin: 10px 0; position: relative; overflow: hidden;">
                    <div id="ct-embeddings-progress-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>
                    <div id="ct-embeddings-progress-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: bold; color: #1d2327;">
                        Processing...
                    </div>
                </div>
                <div id="ct-embeddings-status-container" style="margin: 15px 0;">
                    <p id="ct-embeddings-status-text"><strong>Status:</strong> Starting generation...</p>
                    <p id="ct-embeddings-batch-info" style="color: #646970; font-size: 13px;"></p>
                    <p id="ct-embeddings-current-transcript" style="color: #2271b1; font-size: 13px; margin-top: 5px;"></p>
                    <p id="ct-embeddings-current-segment" style="color: #646970; font-size: 12px; margin-top: 5px;"></p>
                    <p id="ct-embeddings-last-update" style="color: #8c8f94; font-size: 11px; margin-top: 5px; font-style: italic;"></p>
                </div>
                <div id="ct-embeddings-log-container" style="background: #f6f7f7; border: 1px solid #c3c4c7; border-radius: 4px; padding: 15px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; margin: 15px 0;">
                    <div id="ct-embeddings-log" style="color: #1d2327;">
                        <div>Waiting for batch to start...</div>
                    </div>
                </div>
                <button type="button" class="button" id="ct-embeddings-stop" style="margin-top: 10px;">Stop Generation</button>
            </div>
        <?php else: ?>
            <div id="ct-embeddings-ready">
                <p>Click the button below to start generating embeddings. The process will run in batches of 5 transcripts at a time to avoid API rate limits.</p>
                <p style="color: #d63638;"><strong>Note:</strong> This will use OpenAI API credits. Estimated cost: ~$0.13 per 1M tokens. With 11M words (~14M tokens), estimated cost: ~$1.82.</p>
                <?php if ($stats['needs_embeddings'] > 0): ?>
                    <button type="button" class="button button-primary button-large" id="ct-embeddings-start">
                        Start Generation (<?php echo number_format($stats['needs_embeddings']); ?> transcripts)
                    </button>
                <?php else: ?>
                    <p style="color: #00a32a;"><strong>All transcripts already have embeddings generated!</strong></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div id="ct-embeddings-debug-info" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 15px; margin-top: 20px; display: none;">
            <h3 style="margin-top: 0;">Debug Information</h3>
            <div id="ct-embeddings-debug-content" style="font-family: monospace; font-size: 12px;"></div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let generationInterval = null;
    let statusInterval = null;
    let isGenerating = <?php echo $is_generating ? 'true' : 'false'; ?>;
    let currentOffset = 0;
    let batchNumber = 0;
    const totalNeedsEmbeddings = <?php echo $stats['needs_embeddings']; ?>;
    
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
        $('#ct-embeddings-log').prepend(logEntry);
        
        // Keep only last 50 log entries
        const logEntries = $('#ct-embeddings-log > div');
        if (logEntries.length > 50) {
            logEntries.slice(50).remove();
        }
        
        console.log(`[Embeddings] ${message}`);
    }
    
    function showDebugInfo(data) {
        $('#ct-embeddings-debug-info').show();
        $('#ct-embeddings-debug-content').html(JSON.stringify(data, null, 2));
    }
    
    let lastUpdateTime = null;
    let hangDetectionCount = 0;
    
    function updateProgress() {
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_get_embeddings_status',
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                showDebugInfo({ action: 'get_status', response: response });
                
                if (response.success && response.data && response.data.in_progress) {
                    const offset = response.data.offset || 0;
                    const percent = totalNeedsEmbeddings > 0 ? Math.min(100, Math.round((offset / totalNeedsEmbeddings) * 100)) : 0;
                    
                    $('#ct-embeddings-progress-bar').css('width', percent + '%');
                    $('#ct-embeddings-progress-text').text(percent + '%');
                    
                    const processed = response.data.processed || 0;
                    const success = response.data.success || 0;
                    const failed = response.data.failed || 0;
                    const totalEmbeddings = response.data.total_embeddings || 0;
                    
                    // Check for hangs - if timestamp hasn't changed in 30 seconds
                    const currentTimestamp = response.data.timestamp || 0;
                    const now = Math.floor(Date.now() / 1000);
                    const timeSinceUpdate = now - currentTimestamp;
                    
                    if (timeSinceUpdate > 30) {
                        hangDetectionCount++;
                        if (hangDetectionCount > 2) {
                            addLog(`⚠️ WARNING: No update in ${timeSinceUpdate} seconds - process may be hung`, 'warning');
                        }
                    } else {
                        hangDetectionCount = 0;
                    }
                    
                    // Update main status
                    $('#ct-embeddings-status-text').html(
                        `<strong>Status:</strong> Processing batch ${batchNumber}... ` +
                        `<span style="color: #00a32a;">✓ ${success} success</span> | ` +
                        `<span style="color: #d63638;">✗ ${failed} failed</span> | ` +
                        `<span>${processed} transcripts processed</span> | ` +
                        `<span style="color: #2271b1;">${totalEmbeddings} embeddings created</span>`
                    );
                    
                    // Update batch info
                    $('#ct-embeddings-batch-info').text(
                        `Offset: ${offset} / ${totalNeedsEmbeddings} transcripts | ` +
                        `Estimated remaining: ${Math.max(0, totalNeedsEmbeddings - offset)}`
                    );
                    
                    // Update current transcript info
                    if (response.data.current_transcript) {
                        const ct = response.data.current_transcript;
                        $('#ct-embeddings-current-transcript').html(
                            `<strong>Current Transcript:</strong> Chapter ${ct.chapter_id} ` +
                            `(Transcript ${ct.index || ''} of ${ct.total_in_batch || ''} in batch)`
                        ).show();
                    } else {
                        $('#ct-embeddings-current-transcript').hide();
                    }
                    
                    // Update current segment progress
                    if (response.data.current_segment) {
                        const cs = response.data.current_segment;
                        $('#ct-embeddings-current-segment').html(
                            `📝 Segment ${cs.index} / ${cs.total} (${cs.percent}%) - ${cs.status || 'Processing...'}`
                        ).show();
                    } else {
                        $('#ct-embeddings-current-segment').hide();
                    }
                    
                    // Update last update time
                    if (response.data.last_update) {
                        const updateTime = new Date(response.data.last_update);
                        const timeAgo = Math.floor((Date.now() - updateTime.getTime()) / 1000);
                        let timeAgoText = '';
                        if (timeAgo < 60) {
                            timeAgoText = `${timeAgo} seconds ago`;
                        } else if (timeAgo < 3600) {
                            timeAgoText = `${Math.floor(timeAgo / 60)} minutes ago`;
                        } else {
                            timeAgoText = `${Math.floor(timeAgo / 3600)} hours ago`;
                        }
                        
                        $('#ct-embeddings-last-update').html(
                            `🕐 Last update: ${updateTime.toLocaleTimeString()} (${timeAgoText})`
                        ).show();
                        
                        // Color code based on recency
                        if (timeAgo > 30) {
                            $('#ct-embeddings-last-update').css('color', '#d63638');
                        } else if (timeAgo > 10) {
                            $('#ct-embeddings-last-update').css('color', '#dba617');
                        } else {
                            $('#ct-embeddings-last-update').css('color', '#00a32a');
                        }
                    } else {
                        $('#ct-embeddings-last-update').hide();
                    }
                }
            },
            error: function(xhr, status, error) {
                addLog(`Error checking status: ${error}`, 'error');
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
                action: 'ct_generate_embeddings',
                nonce: ctAdmin.nonce,
                offset: currentOffset
            },
            beforeSend: function() {
                addLog(`Sending AJAX request for batch #${batchNumber}...`, 'info');
            },
            success: function(response) {
                showDebugInfo({ action: 'generate_batch', batch: batchNumber, response: response });
                
                if (response.success) {
                    if (response.data.complete) {
                        if (response.data.stopped) {
                            clearInterval(generationInterval);
                            clearInterval(statusInterval);
                            isGenerating = false;
                            addLog('Generation stopped by user', 'warning');
                            $('#ct-embeddings-status-text').html('<strong>Status:</strong> <span style="color: #d63638;">Generation stopped</span>');
                        } else {
                            clearInterval(generationInterval);
                            clearInterval(statusInterval);
                            isGenerating = false;
                            addLog('Generation complete! All embeddings generated.', 'success');
                            $('#ct-embeddings-progress').html(
                                '<div class="notice notice-success"><p><strong>Generation complete!</strong> All embeddings have been generated successfully.</p></div>'
                            );
                            setTimeout(() => location.reload(), 3000);
                        }
                        return;
                    } else {
                        const processed = response.data.processed || 0;
                        const success = response.data.success || 0;
                        const failed = response.data.failed || 0;
                        const totalEmbeddings = response.data.total_embeddings || 0;
                        
                        addLog(`Batch #${batchNumber} complete: ${success} success, ${failed} failed, ${totalEmbeddings} embeddings created`, 
                            failed > 0 ? 'warning' : 'success');
                        
                        if (response.data.errors && response.data.errors.length > 0) {
                            response.data.errors.forEach(function(error) {
                                addLog(`Error: ${error}`, 'error');
                            });
                        }
                        
                        currentOffset = response.data.offset;
                        
                        // Process next batch after delay (API rate limiting)
                        setTimeout(processBatch, 2000);
                    }
                } else {
                    clearInterval(generationInterval);
                    clearInterval(statusInterval);
                    const errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error';
                    addLog(`Batch failed: ${errorMsg}`, 'error');
                    alert('Error: ' + errorMsg);
                }
            },
            error: function(xhr, status, error) {
                clearInterval(generationInterval);
                clearInterval(statusInterval);
                addLog(`AJAX error: ${error} (Status: ${status})`, 'error');
                alert('AJAX error occurred: ' + error);
            }
        });
    }
    
    $('#ct-embeddings-start').on('click', function() {
        if (!confirm('Start generating embeddings? This will use OpenAI API credits and may take a while.')) {
            return;
        }
        
        $(this).prop('disabled', true).text('Starting...');
        $('#ct-embeddings-ready').hide();
        $('#ct-embeddings-progress').show();
        $('#ct-embeddings-debug-info').show();
        isGenerating = true;
        currentOffset = 0;
        batchNumber = 0;
        
        addLog('Generation started. Total transcripts to process: ' + totalNeedsEmbeddings, 'info');
        
        processBatch();
        statusInterval = setInterval(updateProgress, 2000); // Check every 2 seconds for more responsive updates
    });
    
    $('#ct-embeddings-stop').on('click', function() {
        if (!confirm('Stop the generation process?')) {
            return;
        }
        
        clearInterval(generationInterval);
        clearInterval(statusInterval);
        addLog('Stopping generation...', 'warning');
        
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_stop_embeddings',
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    addLog('Generation stopped successfully', 'info');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    addLog('Error stopping generation: ' + (response.data.message || 'Unknown error'), 'error');
                }
            },
            error: function() {
                addLog('AJAX error while stopping generation', 'error');
                setTimeout(() => location.reload(), 1000);
            }
        });
    });
    
    if (isGenerating) {
        addLog('Resuming generation (was already in progress)', 'info');
        statusInterval = setInterval(updateProgress, 2000); // Check every 2 seconds
        updateProgress(); // Immediate update
    }
    
    $('<button>', {
        type: 'button',
        class: 'button',
        text: 'Toggle Debug Info',
        style: 'margin-top: 10px;',
        click: function() {
            $('#ct-embeddings-debug-info').toggle();
        }
    }).insertAfter('#ct-embeddings-stop');
    
    $('#ct-retry-failed').on('click', function() {
        if (!confirm('Retry failed segments for transcripts with partial embeddings? This will regenerate only the missing embeddings.')) {
            return;
        }
        
        $(this).prop('disabled', true).text('Retrying...');
        
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_retry_failed_embeddings',
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.complete) {
                        alert('All failed segments have been retried!');
                    } else {
                        alert(`Retried ${response.data.processed} transcripts: ${response.data.success} success, ${response.data.failed} failed. ${response.data.total_embeddings} new embeddings created.`);
                    }
                    location.reload();
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('AJAX error occurred');
            },
            complete: function() {
                $('#ct-retry-failed').prop('disabled', false).text('Retry Failed Segments');
            }
        });
    });
});
</script>

