jQuery(document).ready(function($) {
    // Handle transcription button clicks
    $(document).on('click', '.ct-transcribe-btn', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var chapterId = $button.data('chapter-id');
        var $row = $button.closest('tr');
        var $statusCell = $row.find('.ct-transcription-status');
        
        // Disable button and show loading
        $button.prop('disabled', true).text('⏳ Starting...');
        $statusCell.html('<span class="ct-status-loading">⏳ Starting transcription...</span>');
        
        // Start transcription via AJAX
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_start_transcription',
                chapter_id: chapterId,
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Start polling for status
                    pollTranscriptionStatus(chapterId, $button, $statusCell);
                } else {
                    $button.prop('disabled', false).text('🎤 Transcribe');
                    $statusCell.html('<span class="ct-status-error">❌ ' + (response.data || 'Failed to start transcription') + '</span>');
                }
            },
            error: function() {
                $button.prop('disabled', false).text('🎤 Transcribe');
                $statusCell.html('<span class="ct-status-error">❌ Network error. Please try again.</span>');
            }
        });
    });
    
    // Poll for transcription status
    function pollTranscriptionStatus(chapterId, $button, $statusCell) {
        var pollInterval = setInterval(function() {
            $.ajax({
                url: ctAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ct_check_transcription_status',
                    chapter_id: chapterId,
                    nonce: ctAdmin.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        var status = response.data.status;
                        var message = response.data.message || '';
                        
                        if (status === 'completed') {
                            clearInterval(pollInterval);
                            $button.prop('disabled', false).text('🔄 Re-transcribe');
                            $statusCell.html('<span class="ct-status-success">✓ ' + message + '</span>');
                            // Reload page after 2 seconds to show updated transcript status
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else if (status === 'failed') {
                            clearInterval(pollInterval);
                            $button.prop('disabled', false).text('🎤 Transcribe');
                            $statusCell.html('<span class="ct-status-error">❌ ' + message + '</span>');
                        } else if (status === 'processing') {
                            // Update status message with elapsed time
                            var statusText = '⏳ Processing...';
                            if (message) {
                                statusText = '⏳ ' + message;
                            }
                            
                            // Add elapsed time if available
                            if (response.data.elapsed_seconds !== undefined) {
                                var elapsed = response.data.elapsed_seconds;
                                var minutes = Math.floor(elapsed / 60);
                                var seconds = elapsed % 60;
                                statusText += ' <small style="color: #666;">(' + minutes + 'm ' + seconds + 's)</small>';
                            }
                            
                            $statusCell.html('<span class="ct-status-processing">' + statusText + '</span>');
                            $button.text('⏳ Processing...');
                        }
                    } else {
                        // Status check failed, but keep polling
                        console.log('Status check failed:', response);
                    }
                },
                error: function() {
                    // Network error, but keep polling
                    console.log('Status check network error');
                }
            });
        }, 3000); // Poll every 3 seconds
        
        // Stop polling after 30 minutes (safety timeout)
        setTimeout(function() {
            clearInterval(pollInterval);
            if ($button.prop('disabled')) {
                $button.prop('disabled', false).text('🎤 Transcribe');
                $statusCell.html('<span class="ct-status-error">⏱️ Timeout - Check status manually</span>');
            }
        }, 30 * 60 * 1000);
    }
    
    // Bulk operations
    var selectedChapters = [];
    
    // Select all checkbox
    $('#ct-select-all-checkbox, #ct-select-all').on('click', function() {
        $('.ct-chapter-checkbox').prop('checked', true).trigger('change');
    });
    
    // Select none
    $('#ct-select-none').on('click', function() {
        $('.ct-chapter-checkbox').prop('checked', false).trigger('change');
    });
    
    // Update selected count
    function updateSelectedCount() {
        selectedChapters = $('.ct-chapter-checkbox:checked').map(function() {
            return parseInt($(this).val());
        }).get();
        
        var count = selectedChapters.length;
        $('#ct-selected-count').text(count + ' selected');
        $('#ct-bulk-download, #ct-bulk-transcribe').prop('disabled', count === 0);
    }
    
    // Chapter checkbox change
    $(document).on('change', '.ct-chapter-checkbox', function() {
        updateSelectedCount();
        $('#ct-select-all-checkbox').prop('checked', 
            $('.ct-chapter-checkbox:checked').length === $('.ct-chapter-checkbox').length
        );
    });
    
    // Bulk download
    $('#ct-bulk-download').on('click', function() {
        if (selectedChapters.length === 0) return;
        
        var $button = $(this);
        $button.prop('disabled', true).text('⏳ Starting...');
        
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_bulk_download',
                chapter_ids: selectedChapters,
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#ct-bulk-progress').show();
                    // Process items sequentially via AJAX
                    processBulkDownloadSequentially(response.data.bulk_id, response.data.chapter_ids, 0);
                } else {
                    console.error('Failed to start bulk download:', response.data || 'Unknown error');
                    $button.prop('disabled', false).text('⬇️ Bulk Download MP4');
                }
            },
            error: function() {
                console.error('Network error during bulk download');
                $button.prop('disabled', false).text('⬇️ Bulk Download MP4');
            }
        });
    });
    
    // Process bulk download sequentially
    function processBulkDownloadSequentially(bulkId, chapterIds, index) {
        if (index >= chapterIds.length) {
            // All done
            $('#ct-bulk-status').text('All downloads completed!');
            setTimeout(function() {
                location.reload();
            }, 2000);
            return;
        }
        
        var chapterId = chapterIds[index];
        
        // Update status
        $('#ct-bulk-status').text('Downloading ' + (index + 1) + ' of ' + chapterIds.length + '...');
        var progress = ((index) / chapterIds.length) * 100;
        $('#ct-bulk-progress-bar').css('width', progress + '%');
        $('#ct-bulk-progress-text').text(index + ' / ' + chapterIds.length);
        
        // Process this item
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_process_single_download',
                bulk_id: bulkId,
                chapter_id: chapterId,
                index: index,
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    
                    // Update progress
                    var newProgress = ((data.completed + data.failed) / data.total) * 100;
                    $('#ct-bulk-progress-bar').css('width', newProgress + '%');
                    $('#ct-bulk-progress-text').text((data.completed + data.failed) + ' / ' + data.total);
                    
                    if (data.has_more) {
                        // Process next item after a short delay
                        setTimeout(function() {
                            processBulkDownloadSequentially(bulkId, chapterIds, index + 1);
                        }, 500);
                    } else {
                        // All done
                        $('#ct-bulk-status').text('Completed! ' + data.completed + ' succeeded, ' + data.failed + ' failed.');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                } else {
                    console.error('Download failed for chapter ' + chapterId + ':', response.data);
                    // Continue with next item anyway
                    if (index + 1 < chapterIds.length) {
                        setTimeout(function() {
                            processBulkDownloadSequentially(bulkId, chapterIds, index + 1);
                        }, 500);
                    }
                }
            },
            error: function() {
                console.error('Network error downloading chapter ' + chapterId);
                // Continue with next item anyway
                if (index + 1 < chapterIds.length) {
                    setTimeout(function() {
                        processBulkDownloadSequentially(bulkId, chapterIds, index + 1);
                    }, 1000);
                }
            }
        });
    }
    
    // Bulk transcribe
    $('#ct-bulk-transcribe').on('click', function() {
        if (selectedChapters.length === 0) return;
        
        var $button = $(this);
        $button.prop('disabled', true).text('⏳ Starting...');
        
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_bulk_transcribe',
                chapter_ids: selectedChapters,
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#ct-bulk-progress').show();
                    pollBulkStatus(response.data.bulk_id, 'transcribe');
                } else {
                    console.error('Failed to start bulk transcription:', response.data || 'Unknown error');
                    $button.prop('disabled', false).text('🎤 Bulk Transcribe');
                }
            },
            error: function() {
                console.error('Network error during bulk transcription');
                $button.prop('disabled', false).text('🎤 Bulk Transcribe');
            }
        });
    });
    
    // Poll bulk operation status
    function pollBulkStatus(bulkId, type) {
        var pollInterval = setInterval(function() {
            $.ajax({
                url: ctAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ct_check_bulk_status',
                    bulk_id: bulkId,
                    nonce: ctAdmin.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        var data = response.data;
                        var total = data.total || 0;
                        var completed = data.completed || 0;
                        var failed = data.failed || 0;
                        var current = data.current || 0;
                        var status = data.status || 'unknown';
                        
                        // Update progress
                        var progress = total > 0 ? ((completed + failed) / total) * 100 : 0;
                        $('#ct-bulk-progress-bar').css('width', progress + '%');
                        $('#ct-bulk-progress-text').text((completed + failed) + ' / ' + total);
                        
                        // Update status text
                        var statusText = '';
                        if (status === 'processing') {
                            statusText = 'Processing ' + current + ' of ' + total + '...';
                            if (completed > 0 || failed > 0) {
                                statusText += ' (Completed: ' + completed + ', Failed: ' + failed + ')';
                            }
                            
                            // If stuck (current hasn't changed in a while), try to trigger next item
                            if (current > 0 && (completed + failed) < current) {
                                // Might be stuck, but let's wait a bit more
                                console.log('Bulk operation might be stuck at item ' + current);
                            }
                        } else if (status === 'completed') {
                            statusText = 'Completed! ' + completed + ' succeeded, ' + failed + ' failed.';
                            clearInterval(pollInterval);
                            setTimeout(function() {
                                location.reload();
                            }, 3000);
                        } else {
                            statusText = 'Status: ' + status;
                        }
                        $('#ct-bulk-status').text(statusText);
                        
                        if (status === 'completed') {
                            clearInterval(pollInterval);
                        }
                    } else {
                        console.log('Bulk status check: No data or failed', response);
                    }
                },
                error: function() {
                    console.log('Bulk status check error');
                }
            });
        }, 2000); // Poll every 2 seconds
        
        // Stop polling after 2 hours (safety timeout)
        setTimeout(function() {
            clearInterval(pollInterval);
        }, 2 * 60 * 60 * 1000);
    }
    
    // Retry stuck transcription
    $(document).on('click', '.ct-retry-transcription', function() {
        var $button = $(this);
        var chapterId = $button.data('chapter-id');
        
        if (!confirm('Retry transcription for chapter ' + chapterId + '? This will start a new transcription process.')) {
            return;
        }
        
        $button.prop('disabled', true).text('🔄 Retrying...');
        
        // Clear the stuck status
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_start_transcription',
                chapter_id: chapterId,
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Transcription restarted! The page will reload.');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert('Failed to restart: ' + (response.data || 'Unknown error'));
                    $button.prop('disabled', false).text('🔄 Retry Now');
                }
            },
            error: function() {
                alert('Network error. Please try again.');
                $button.prop('disabled', false).text('🔄 Retry Now');
            }
        });
    });
    
    // Clear all stuck transcriptions
    $('#ct-clear-all-stuck').on('click', function() {
        if (!confirm('Clear all stuck transcription statuses? This will reset the status for all active transcriptions.')) {
            return;
        }
        
        var $button = $(this);
        $button.prop('disabled', true).text('🗑️ Clearing...');
        
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_clear_all_stuck',
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Cleared ' + response.data.count + ' stuck transcription statuses. Page will reload.');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert('Failed to clear: ' + (response.data || 'Unknown error'));
                    $button.prop('disabled', false).text('🗑️ Clear All Stuck Transcriptions');
                }
            },
            error: function() {
                alert('Network error. Please try again.');
                $button.prop('disabled', false).text('🗑️ Clear All Stuck Transcriptions');
            }
        });
    });
    
    // View logs button
    $(document).on('click', '.ct-view-logs', function() {
        var $button = $(this);
        var chapterId = $button.data('chapter-id');
        var $logViewer = $('#ct-logs-' + chapterId);
        var $logContent = $logViewer.find('.ct-log-content');
        
        if ($logViewer.is(':visible')) {
            $logViewer.slideUp();
            $button.text('📋 View Logs');
            return;
        }
        
        $logViewer.slideDown();
        $button.text('📋 Hide Logs');
        $logContent.text('Loading logs...');
        
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_get_debug_log',
                chapter_id: chapterId,
                lines: 100,
                nonce: ctAdmin.nonce
            },
            success: function(response) {
                if (response.success && response.data.log_entries) {
                    if (response.data.log_entries.length > 0) {
                        var logHtml = response.data.log_entries.map(function(line) {
                            return '<div style="margin-bottom: 2px; padding: 2px 0; border-bottom: 1px solid #ddd;">' + $('<div>').text(line).html() + '</div>';
                        }).join('');
                        $logContent.html(logHtml);
                        // Scroll to bottom
                        $logViewer.scrollTop($logViewer[0].scrollHeight);
                    } else {
                        $logContent.html('<em>No transcription-related log entries found for this chapter.</em>');
                    }
                } else {
                    $logContent.html('<em>' + (response.data && response.data.log_entries && response.data.log_entries[0] || 'Unable to load logs') + '</em>');
                }
            },
            error: function() {
                $logContent.html('<em>Error loading logs</em>');
            }
        });
    });
});
