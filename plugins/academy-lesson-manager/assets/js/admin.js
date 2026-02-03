/**
 * Academy Lesson Manager Admin JavaScript
 * 
 * Custom JavaScript for the Academy Lesson Manager admin interface
 */

jQuery(document).ready(function($) {
    
    // Search form enhancements
    $('.alm-search-form input[type="search"]').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            $(this).closest('form').submit();
        }
    });
    
    // Table row highlighting
    $('.wp-list-table tbody tr').hover(
        function() {
            $(this).addClass('hover');
        },
        function() {
            $(this).removeClass('hover');
        }
    );
    
    // Bulk actions (placeholder for future functionality)
    $('.wp-list-table thead input[type="checkbox"]').on('change', function() {
        var checked = $(this).is(':checked');
        $('.wp-list-table tbody input[type="checkbox"]').prop('checked', checked);
    });
    
    $('.wp-list-table tbody input[type="checkbox"]').on('change', function() {
        var totalCheckboxes = $('.wp-list-table tbody input[type="checkbox"]').length;
        var checkedCheckboxes = $('.wp-list-table tbody input[type="checkbox"]:checked').length;
        
        if (checkedCheckboxes === totalCheckboxes) {
            $('.wp-list-table thead input[type="checkbox"]').prop('checked', true);
        } else {
            $('.wp-list-table thead input[type="checkbox"]').prop('checked', false);
        }
    });
    
    // Confirmation dialogs for destructive actions (skip elements that already have their own onclick confirm)
    $('a[href*="delete"], button[data-action="delete"]').on('click', function(e) {
        var onclick = $(this).attr('onclick') || '';
        if (onclick.indexOf('confirm') !== -1) {
            return; // Already has its own confirm, don't add a second one
        }
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });
    
    // External link indicators
    $('a[target="_blank"]').each(function() {
        var $this = $(this);
        var href = $this.attr('href');
        
        // Add appropriate icons based on URL
        if (href.indexOf('vimeo.com') !== -1) {
            $this.addClass('vimeo-link');
        } else if (href.indexOf('youtube.com') !== -1 || href.indexOf('youtu.be') !== -1) {
            $this.addClass('youtube-link');
        } else if (href.indexOf('jazzedge.com') !== -1) {
            $this.addClass('jazzedge-link');
        }
    });
    
    // Auto-refresh for long-running operations (placeholder)
    function refreshPage() {
        if (window.location.search.indexOf('refresh=1') !== -1) {
            setTimeout(function() {
                window.location.reload();
            }, 5000);
        }
    }
    
    // Initialize refresh if needed
    refreshPage();
    
    // Tooltip functionality for truncated text
    $('.wp-list-table td').each(function() {
        var $this = $(this);
        var text = $this.text().trim();
        var width = $this.width();
        
        if (text.length > 30 && width < 200) {
            $this.attr('title', text);
        }
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + F for search focus
        if ((e.ctrlKey || e.metaKey) && e.which === 70) {
            e.preventDefault();
            $('.alm-search-form input[type="search"]').focus();
        }
        
        // Escape to clear search
        if (e.which === 27) { // Escape key
            $('.alm-search-form input[type="search"]').val('').closest('form').submit();
        }
    });
    
    // Loading states for forms
    $('form').on('submit', function() {
        var $form = $(this);
        var $submitBtn = $form.find('input[type="submit"], button[type="submit"]');
        
        if ($submitBtn.length) {
            $submitBtn.prop('disabled', true).addClass('loading');
            $form.addClass('alm-loading');
        }
    });
    
    // Remove loading state on page load
    $('.loading').removeClass('loading');
    $('.alm-loading').removeClass('alm-loading');
    
    // Responsive table handling
    function handleResponsiveTables() {
        if ($(window).width() < 782) {
            $('.wp-list-table').addClass('mobile-view');
        } else {
            $('.wp-list-table').removeClass('mobile-view');
        }
    }
    
    // Handle window resize
    $(window).on('resize', handleResponsiveTables);
    handleResponsiveTables(); // Initial call
    
    // Status indicators
    function updateStatusIndicators() {
        // Highlight rows with missing video sources
        $('.wp-list-table tbody tr').each(function() {
            var $row = $(this);
            var vimeoId = $row.find('.column-vimeo').text().trim();
            var youtubeId = $row.find('.column-youtube').text().trim();
            
            if (vimeoId === '—' && youtubeId === '—') {
                $row.addClass('no-video-source');
            }
        });
        
        // Highlight completed items
        $('.wp-list-table tbody tr').each(function() {
            var $row = $(this);
            var jamiDone = $row.find('.column-jami-done, td:contains("Yes")').length;
            
            if (jamiDone > 0) {
                $row.addClass('completed');
            }
        });
    }
    
    updateStatusIndicators();
    
    // Search suggestions (placeholder for future enhancement)
    function initSearchSuggestions() {
        // This would be enhanced with AJAX search suggestions
        // For now, just add basic functionality
        $('.alm-search-form input[type="search"]').on('input', function() {
            var query = $(this).val();
            if (query.length > 2) {
                // Future: AJAX search suggestions
            }
        });
    }
    
    initSearchSuggestions();
    
    // Print functionality
    $('.print-page').on('click', function(e) {
        e.preventDefault();
        window.print();
    });
    
    // Export functionality (placeholder)
    $('.export-data').on('click', function(e) {
        e.preventDefault();
        alert('Export functionality will be added in a future version.');
    });
    
    // Quick actions menu
    function initQuickActions() {
        $('.quick-actions').on('click', function(e) {
            e.preventDefault();
            var $menu = $(this).siblings('.quick-actions-menu');
            $menu.toggle();
        });
        
        // Close menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.quick-actions').length) {
                $('.quick-actions-menu').hide();
            }
        });
    }
    
    initQuickActions();
    
    // Accessibility improvements
    function improveAccessibility() {
        // Add ARIA labels to interactive elements
        $('.wp-list-table th a').attr('aria-label', function() {
            return 'Sort by ' + $(this).text();
        });
        
        // Add skip links
        if ($('.wp-list-table').length) {
            $('.wp-list-table').before('<a href="#main-content" class="screen-reader-text skip-link">Skip to main content</a>');
        }
    }
    
    improveAccessibility();
    
    // Transcription functionality
    function startTranscription(chapterId, $button, $statusContainer, $progressContainer) {
        
        // Show immediate feedback
        $button.prop('disabled', true);
        var originalText = $button.text();
        $button.text('Transcribing...');
        
        if ($statusContainer) {
            $statusContainer.html('<span style="color: #2271b1; font-weight: bold;">⏳ Transcription in progress... This may take several minutes. The page will refresh automatically when complete.</span>').show();
        }
        
        if ($progressContainer) {
            $progressContainer.show();
            var $progressBar = $progressContainer.find('#alm-transcribe-progress-bar');
            var $message = $progressContainer.find('#alm-transcribe-message');
            if ($progressBar.length) {
                $progressBar.css('width', '5%');
            }
            if ($message.length) {
                $message.text('Transcription in progress... Please wait.');
            }
        }
        
        // Start transcription
        $.ajax({
            url: alm_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_transcribe_chapter',
                chapter_id: chapterId,
                nonce: alm_admin.nonce
            },
            success: function(response) {
                    if (response.success) {
                        if ($statusContainer) {
                            $statusContainer.html('<span style="color: #2271b1; font-weight: bold;">⏳ Transcription in progress... This may take several minutes. The page will refresh automatically when complete.</span>');
                        }
                        if ($progressContainer) {
                            var $message = $progressContainer.find('#alm-transcribe-message');
                            if ($message.length) {
                                $message.text('Transcription in progress... Please wait.');
                            }
                        }
                        // Poll for VTT file (more reliable than status)
                        pollForVTTFile(chapterId, $button, $statusContainer, $progressContainer);
                    } else {
                    console.error('Transcription failed:', response);
                    $button.prop('disabled', false).text(originalText);
                    if ($statusContainer) {
                        $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ Error: ' + (response.data && response.data.message ? response.data.message : 'Failed to start transcription') + '</span>');
                    }
                    if ($progressContainer) {
                        $progressContainer.hide();
                    }
                }
            },
            error: function(xhr, status, error) {
                // Check if it's a JSON parse error but the response might still be valid
                var responseText = xhr.responseText || '';
                var errorStr = (typeof error === 'string') ? error : (error ? error.toString() : '');
                var isJsonError = errorStr.indexOf('JSON') !== -1 || errorStr.indexOf('parse') !== -1;
                
                // Try to parse the response anyway - sometimes there's trailing whitespace
                var parsedResponse = null;
                try {
                    // Try to extract JSON from response (in case there's trailing content)
                    var jsonMatch = responseText.match(/\{[\s\S]*\}/);
                    if (jsonMatch) {
                        parsedResponse = JSON.parse(jsonMatch[0]);
                    }
                } catch (e) {
                    // Ignore parse errors
                }
                
                // If we successfully parsed and it shows success, treat it as success
                if (parsedResponse && parsedResponse.success) {
                    if ($statusContainer) {
                        $statusContainer.html('<span style="color: #2271b1; font-weight: bold;">⏳ Transcription in progress... This may take several minutes. The page will refresh automatically when complete.</span>');
                    }
                    if ($progressContainer) {
                        var $message = $progressContainer.find('#alm-transcribe-message');
                        if ($message.length) {
                            $message.text('Transcription in progress... Please wait.');
                        }
                    }
                    // Poll for VTT file - transcription is actually working
                    pollForVTTFile(chapterId, $button, $statusContainer, $progressContainer);
                    return; // Don't show error
                }
                
                // If it's a JSON parse error, wait a moment and check if status exists
                // (transcription might have started despite the parse error)
                if (isJsonError) {
                    setTimeout(function() {
                        // Check if transcription status exists (means it started)
                        $.ajax({
                            url: alm_admin.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'alm_check_transcription_status',
                                chapter_id: chapterId,
                                nonce: alm_admin.nonce
                            },
                            success: function(statusResponse) {
                                if (statusResponse.success) {
                                    // Transcription is actually running! Don't show error
                                    if ($statusContainer) {
                                        $statusContainer.html('<span style="color: #2271b1; font-weight: bold;">⏳ Transcription in progress... This may take several minutes. The page will refresh automatically when complete.</span>');
                                    }
                                    if ($progressContainer) {
                                        var $message = $progressContainer.find('#alm-transcribe-message');
                                        if ($message.length) {
                                            $message.text('Transcription in progress... Please wait.');
                                        }
                                    }
                                    // Start polling for VTT file
                                    pollForVTTFile(chapterId, $button, $statusContainer, $progressContainer);
                                } else {
                                    // Status doesn't exist, show the error
                                    console.error('AJAX error:', status, error, xhr);
                                    $button.prop('disabled', false).text(originalText);
                                    if ($statusContainer) {
                                        $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ AJAX Error: ' + error + ' (Check browser console for details)</span>');
                                    }
                                    if ($progressContainer) {
                                        $progressContainer.hide();
                                    }
                                }
                            },
                            error: function() {
                                // Status check failed, show original error
                                console.error('AJAX error:', status, error, xhr);
                                $button.prop('disabled', false).text(originalText);
                                if ($statusContainer) {
                                    $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ AJAX Error: ' + error + ' (Check browser console for details)</span>');
                                }
                                if ($progressContainer) {
                                    $progressContainer.hide();
                                }
                            }
                        });
                    }, 500); // Wait 500ms for status to be set
                    return; // Don't show error immediately
                }
                
                // For other errors, show them normally
                console.error('AJAX error:', status, error, xhr);
                $button.prop('disabled', false).text(originalText);
                if ($statusContainer) {
                    $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ AJAX Error: ' + error + ' (Check browser console for details)</span>');
                }
                if ($progressContainer) {
                    $progressContainer.hide();
                }
            }
        });
    }
    
    function pollTranscriptionStatus(chapterId, $button, $statusContainer, $progressContainer) {
        var pollCount = 0;
        var pollInterval = setInterval(function() {
            pollCount++;
            $.ajax({
                url: alm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'alm_check_transcription_status',
                    chapter_id: chapterId,
                    nonce: alm_admin.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        var status = response.data.status;
                        var message = response.data.message || '';
                        var progress = response.data.progress || 0;
                        var elapsed = response.data.elapsed || 0;
                        var lastUpdate = response.data.last_update || 0;
                        
                        // Format elapsed time
                        var elapsedText = '';
                        if (elapsed > 0) {
                            var minutes = Math.floor(elapsed / 60);
                            var seconds = elapsed % 60;
                            if (minutes > 0) {
                                elapsedText = minutes + 'm ' + seconds + 's';
                            } else {
                                elapsedText = seconds + 's';
                            }
                        }
                        
                        // Format last update time
                        var lastUpdateText = '';
                        if (lastUpdate > 0) {
                            var now = Math.floor(Date.now() / 1000);
                            var secondsAgo = now - lastUpdate;
                            if (secondsAgo < 60) {
                                lastUpdateText = secondsAgo + 's ago';
                            } else if (secondsAgo < 3600) {
                                lastUpdateText = Math.floor(secondsAgo / 60) + 'm ago';
                            } else {
                                lastUpdateText = Math.floor(secondsAgo / 3600) + 'h ago';
                            }
                        }
                        
                        if ($statusContainer) {
                            var statusIcon = status === 'completed' ? '✅' : (status === 'failed' ? '❌' : '⏳');
                            var statusColor = status === 'completed' ? '#46b450' : (status === 'failed' ? '#dc3232' : '#2271b1');
                            var statusHtml = '<span style="color: ' + statusColor + '; font-weight: bold;">' + statusIcon + ' ' + message + '</span>';
                            
                            // Add elapsed time and last update info
                            if (status === 'processing' && elapsed > 0) {
                                statusHtml += '<br><small style="color: #666; font-size: 11px;">⏱️ Elapsed: ' + elapsedText;
                                if (lastUpdateText) {
                                    statusHtml += ' | Last update: ' + lastUpdateText;
                                }
                                statusHtml += '</small>';
                                
                                // Show warning and cancel button if stuck
                                var isStuck = false;
                                if (elapsed > 300) { // More than 5 minutes
                                    isStuck = true;
                                } else if (lastUpdate > 0) {
                                    var now = Math.floor(Date.now() / 1000);
                                    var secondsSinceUpdate = now - lastUpdate;
                                    if (secondsSinceUpdate > 120) { // No update for 2 minutes
                                        isStuck = true;
                                    }
                                }
                                
                                if (isStuck) {
                                    statusHtml += '<br><small style="color: #d63638; font-size: 11px;">⚠️ Process appears stuck.</small>';
                                    statusHtml += '<br><button class="button button-small alm-clear-status-btn" data-chapter-id="' + chapterId + '" style="margin-top: 5px; background: #dc3232; color: white; border: none; cursor: pointer;">Cancel & Retry</button>';
                                } else if (elapsed > 300) {
                                    statusHtml += '<br><small style="color: #d63638; font-size: 11px;">⚠️ This is taking longer than usual. If it continues, check server logs.</small>';
                                }
                                
                                // Show debug log toggle button
                                if (response.data.debug_log && response.data.debug_log.length > 0) {
                                    var debugLogId = 'alm-debug-log-' + chapterId;
                                    if (!$statusContainer.find('#' + debugLogId).length) {
                                        statusHtml += '<br><button class="button button-small alm-toggle-debug-btn" data-chapter-id="' + chapterId + '" style="margin-top: 5px; background: #666; color: white; border: none; cursor: pointer; font-size: 11px;">Show Debug Log</button>';
                                        statusHtml += '<div id="' + debugLogId + '" style="display: none; margin-top: 10px; padding: 10px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 11px;">';
                                        statusHtml += '<strong>Debug Log:</strong><br>';
                                        response.data.debug_log.forEach(function(logEntry) {
                                            var logTime = new Date(logEntry.time * 1000).toLocaleTimeString();
                                            var logColor = logEntry.status === 'failed' ? '#dc3232' : (logEntry.status === 'completed' ? '#46b450' : '#2271b1');
                                            statusHtml += '<span style="color: #666;">[' + logTime + ']</span> ';
                                            statusHtml += '<span style="color: ' + logColor + '; font-weight: bold;">[' + logEntry.status.toUpperCase() + ']</span> ';
                                            statusHtml += '<span>' + logEntry.message + '</span> ';
                                            statusHtml += '<span style="color: #999;">(' + logEntry.progress + '%)</span><br>';
                                        });
                                        statusHtml += '</div>';
                                    }
                                }
                            }
                            
                            $statusContainer.html(statusHtml);
                            
                            // Re-attach toggle handler if debug log exists
                            if (response.data.debug_log && response.data.debug_log.length > 0) {
                                $statusContainer.find('.alm-toggle-debug-btn').off('click').on('click', function() {
                                    var $debugLog = $statusContainer.find('#alm-debug-log-' + chapterId);
                                    if ($debugLog.is(':visible')) {
                                        $debugLog.hide();
                                        $(this).text('Show Debug Log');
                                    } else {
                                        $debugLog.show();
                                        $(this).text('Hide Debug Log');
                                    }
                                });
                            }
                        }
                        
                        if ($progressContainer) {
                            var $progressBar = $progressContainer.find('#alm-transcribe-progress-bar');
                            var $message = $progressContainer.find('#alm-transcribe-message');
                            if ($progressBar.length) {
                                $progressBar.css('width', progress + '%');
                            }
                            if ($message.length) {
                                // Always update message from server response
                                if (message) {
                                    var messageText = message + ' (' + progress + '%)';
                                    if (elapsed > 0) {
                                        messageText += ' - ' + elapsedText;
                                    }
                                    $message.text(messageText);
                                }
                            }
                        }
                        
                        if (status === 'completed') {
                            clearInterval(pollInterval);
                            var originalText = $button.data('original-text') || 'Re-transcribe Chapter';
                            $button.prop('disabled', false).text(originalText);
                            if ($statusContainer) {
                                $statusContainer.html('<span style="color: #46b450; font-weight: bold;">✅ Transcription completed successfully! Reloading page...</span>');
                            }
                            // Reload page immediately to show updated transcript
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else if (status === 'failed') {
                            clearInterval(pollInterval);
                            var originalText = $button.data('original-text') || 'Transcribe';
                            $button.prop('disabled', false).text(originalText);
                            if ($statusContainer) {
                                $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ ' + message + '</span>');
                            }
                            if ($progressContainer) {
                                $progressContainer.hide();
                            }
                        }
                    } else {
                        // Don't stop polling if status not found yet (might be starting)
                        if (pollCount > 10) {
                            console.error('No status found after 10 attempts, stopping polling');
                            clearInterval(pollInterval);
                            var originalText = $button.data('original-text') || 'Transcribe';
                            $button.prop('disabled', false).text(originalText);
                            if ($statusContainer) {
                                $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ Status not found. The background process may not have started. Check server logs.</span>');
                            }
                            return;
                        }
                    }
                    
                    // Add timeout - stop polling after 10 minutes (600 seconds)
                    if (pollCount > 600) {
                        console.error('Polling timeout after 10 minutes');
                        clearInterval(pollInterval);
                        var originalText = $button.data('original-text') || 'Transcribe';
                        $button.prop('disabled', false).text(originalText);
                        if ($statusContainer) {
                            $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ Transcription timed out after 10 minutes. Process may be stuck. Check server logs.</span>');
                        }
                        if ($progressContainer) {
                            $progressContainer.hide();
                        }
                        return;
                    }
                    
                    // If stuck at 5% for more than 2 minutes, show warning
                    if (response.success && response.data && response.data.progress === 5 && pollCount > 120) {
                        if ($statusContainer && !$statusContainer.find('.stuck-warning').length) {
                            $statusContainer.append('<div class="stuck-warning" style="color: #d63638; margin-top: 5px; font-size: 12px;">⚠️ Process appears stuck. WP-Cron may not be running. Check server logs.</div>');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Status check error:', status, error, xhr);
                    if (pollCount > 5) {
                        clearInterval(pollInterval);
                        var originalText = $button.data('original-text') || 'Transcribe';
                        $button.prop('disabled', false).text(originalText);
                        if ($statusContainer) {
                            $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ Error checking status: ' + error + '</span>');
                        }
                    }
                }
            });
        }, 2000); // Poll every 2 seconds
        
        // Stop polling after 30 minutes (timeout)
        setTimeout(function() {
            clearInterval(pollInterval);
            var originalText = $button.data('original-text') || 'Transcribe';
            $button.prop('disabled', false).text(originalText);
            if ($statusContainer) {
                $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ Transcription timeout (30 minutes)</span>');
            }
        }, 30 * 60 * 1000);
    }
    
    /**
     * Poll for VTT file existence - more reliable than status polling
     */
    function pollForVTTFile(chapterId, $button, $statusContainer, $progressContainer) {
        var pollCount = 0;
        var pollInterval = setInterval(function() {
            pollCount++;
            
            $.ajax({
                url: alm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'alm_check_vtt_file',
                    chapter_id: chapterId,
                    nonce: alm_admin.nonce
                },
                success: function(response) {
                    if (response.success && response.data && response.data.exists) {
                        // VTT file exists! Transcription is complete
                        clearInterval(pollInterval);
                        var originalText = $button.data('original-text') || 'Re-transcribe Chapter';
                        $button.prop('disabled', false).text(originalText);
                        
                        if ($statusContainer) {
                            $statusContainer.html('<span style="color: #46b450; font-weight: bold;">✅ Transcription completed! Reloading page...</span>');
                        }
                        
                        if ($progressContainer) {
                            var $progressBar = $progressContainer.find('#alm-transcribe-progress-bar');
                            var $message = $progressContainer.find('#alm-transcribe-message');
                            if ($progressBar.length) {
                                $progressBar.css('width', '100%');
                            }
                            if ($message.length) {
                                $message.text('Transcription completed! Reloading...');
                            }
                        }
                        
                        // Reload page after 1 second to show updated transcript
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // VTT file doesn't exist yet, keep polling
                        // Update progress message to show it's still working
                        var minutes = Math.floor(pollCount * 2 / 60);
                        var seconds = (pollCount * 2) % 60;
                        var progressText = 'Transcription in progress...';
                        if (minutes > 0) {
                            progressText += ' (' + minutes + 'm ' + seconds + 's elapsed)';
                        } else if (seconds > 0) {
                            progressText += ' (' + seconds + 's elapsed)';
                        }
                        
                        // Update progress message every poll
                        if ($progressContainer) {
                            var $message = $progressContainer.find('#alm-transcribe-message');
                            if ($message.length) {
                                $message.text(progressText);
                            }
                            // Update progress bar to show activity (oscillate between 5% and 15%)
                            var $progressBar = $progressContainer.find('#alm-transcribe-progress-bar');
                            if ($progressBar.length) {
                                var baseProgress = 5;
                                var pulseProgress = baseProgress + (Math.sin(pollCount * 0.1) * 5);
                                $progressBar.css('width', Math.max(5, Math.min(15, pulseProgress)) + '%');
                            }
                        }
                        
                        // Update status message periodically to show it's still working
                        if (pollCount % 15 === 0) { // Every 30 seconds (15 * 2 seconds)
                            if ($statusContainer) {
                                var statusText = '⏳ Transcription in progress... This may take several minutes.';
                                if (minutes > 0) {
                                    statusText += ' (' + minutes + ' minute' + (minutes > 1 ? 's' : '') + ' elapsed)';
                                }
                                $statusContainer.html('<span style="color: #2271b1; font-weight: bold;">' + statusText + '</span>');
                            }
                        }
                    }
                    
                    // Timeout after 30 minutes
                    if (pollCount > 900) { // 30 minutes = 900 * 2 seconds
                        clearInterval(pollInterval);
                        var originalText = $button.data('original-text') || 'Transcribe';
                        $button.prop('disabled', false).text(originalText);
                        if ($statusContainer) {
                            $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ Transcription timed out after 30 minutes. Please check server logs.</span>');
                        }
                        if ($progressContainer) {
                            $progressContainer.hide();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('VTT check error:', status, error, xhr);
                    // Don't stop polling on occasional errors, but stop after too many
                    if (pollCount > 10) {
                        clearInterval(pollInterval);
                        var originalText = $button.data('original-text') || 'Transcribe';
                        $button.prop('disabled', false).text(originalText);
                        var errorMsg = (typeof error === 'string') ? error : (error ? error.toString() : 'Unknown error');
                        if ($statusContainer) {
                            $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ Error checking transcription status: ' + errorMsg + '</span>');
                        }
                    }
                }
            });
        }, 2000); // Poll every 2 seconds
        
        // Stop polling after 30 minutes (timeout)
        setTimeout(function() {
            clearInterval(pollInterval);
            var originalText = $button.data('original-text') || 'Transcribe';
            $button.prop('disabled', false).text(originalText);
            if ($statusContainer) {
                $statusContainer.html('<span style="color: #dc3232; font-weight: bold;">❌ Transcription timeout (30 minutes)</span>');
            }
        }, 30 * 60 * 1000);
    }
    
    // Function to clear stuck transcription status
    function clearTranscriptionStatus(chapterId, button) {
        if (!confirm('Clear the stuck transcription status and allow retry?')) {
            return;
        }
        
        var $button = $(button);
        $button.prop('disabled', true).text('Clearing...');
        
        $.ajax({
            url: alm_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_clear_transcription_status',
                chapter_id: chapterId,
                nonce: alm_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Reload page to reset everything
                    window.location.reload();
                } else {
                    alert('Error: ' + (response.data && response.data.message ? response.data.message : 'Failed to clear status'));
                    $button.prop('disabled', false).text('Cancel & Retry');
                }
            },
            error: function() {
                alert('Error clearing status. Please refresh the page.');
                $button.prop('disabled', false).text('Cancel & Retry');
            }
        });
    }
    
    // Handle click on "Cancel & Retry" button
    $(document).on('click', '.alm-clear-status-btn', function(e) {
        e.preventDefault();
        var chapterId = $(this).data('chapter-id');
        clearTranscriptionStatus(chapterId, this);
    });
    
    // Handle transcribe button clicks on chapter edit page
    $(document).on('click', '#alm-transcribe-chapter', function(e) {
        e.preventDefault();
        var chapterId = $(this).data('chapter-id');
        var $button = $(this);
        if (!$button.data('original-text')) {
            $button.data('original-text', $button.text());
        }
        var $status = $('#alm-transcribe-status');
        var $progress = $('#alm-transcribe-progress');
        startTranscription(chapterId, $button, $status, $progress);
    });
    
    // Handle transcribe button clicks on lesson edit page
    $(document).on('click', '.alm-transcribe-chapter-btn', function(e) {
        e.preventDefault();
        var chapterId = $(this).data('chapter-id');
        var $button = $(this);
        if (!$button.data('original-text')) {
            $button.data('original-text', $button.text());
        }
        // Create a temporary status container for lesson page
        var $status = $('<span class="alm-transcribe-status-inline" style="margin-left: 10px; font-weight: bold;"></span>');
        $button.after($status);
        startTranscription(chapterId, $button, $status, null);
    });
    
    // Handle AI description generation
    $(document).on('click', '#alm-generate-description', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var lessonId = $button.data('lesson-id');
        var $status = $('#alm-generate-description-status');
        var $textarea = $('#lesson_description');
        
        if (!lessonId) {
            console.error('No lesson ID found');
            $status.html('<span style="color: #dc3232; font-weight: bold;">❌ Error: No lesson ID</span>');
            return;
        }
        
        // Store original button text
        var originalText = $button.html();
        
        // Update button and show status
        $button.prop('disabled', true);
        $button.html('<span class="spinner" style="float: none; margin: 0 5px 0 0; visibility: visible;"></span> Generating...');
        $status.html('<span style="color: #2271b1; font-weight: bold;">⏳ Generating description from transcripts... This may take 10-30 seconds.</span>');
        
        
        $.ajax({
            url: alm_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_generate_lesson_description',
                lesson_id: lessonId,
                nonce: alm_admin.nonce
            },
            timeout: 60000, // 60 second timeout
            success: function(response) {
                
                if (response.success) {
                    // Replace existing description with generated one
                    $textarea.val(response.data.description);
                    $status.html('<span style="color: #46b450; font-weight: bold;">✅ ' + response.data.message + '</span>');
                    
                    // Highlight the textarea briefly to show it was updated
                    $textarea.css('background-color', '#d4edda');
                    setTimeout(function() {
                        $textarea.css('background-color', '');
                    }, 2000);
                    
                    // Clear status after 5 seconds
                    setTimeout(function() {
                        $status.html('');
                    }, 5000);
                } else {
                    console.error('AJAX error:', response);
                    $status.html('<span style="color: #dc3232; font-weight: bold;">❌ ' + (response.data && response.data.message ? response.data.message : 'Error generating description') + '</span>');
                }
                
                $button.prop('disabled', false);
                $button.html(originalText);
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error, xhr);
                var errorMsg = 'AJAX error occurred';
                if (status === 'timeout') {
                    errorMsg = 'Request timed out. Please try again.';
                } else if (xhr.responseText) {
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.data && errorResponse.data.message) {
                            errorMsg = errorResponse.data.message;
                        }
                    } catch(e) {
                        // Ignore parse errors
                    }
                }
                $status.html('<span style="color: #dc3232; font-weight: bold;">❌ ' + errorMsg + '</span>');
                $button.prop('disabled', false);
                $button.html(originalText);
            }
        });
    });
    
    // Handle AI description expansion
    $(document).on('click', '#alm-expand-description', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var lessonId = $button.data('lesson-id');
        var $status = $('#alm-expand-description-status');
        var $textarea = $('#lesson_description');
        var currentDescription = $textarea.val().trim();
        
        if (!lessonId) {
            console.error('No lesson ID found');
            $status.html('<span style="color: #dc3232; font-weight: bold;">❌ Error: No lesson ID</span>');
            return;
        }
        
        if (!currentDescription) {
            $status.html('<span style="color: #dc3232; font-weight: bold;">❌ Please enter a description first to expand.</span>');
            return;
        }
        
        // Store original button text
        var originalText = $button.html();
        
        // Update button and show status
        $button.prop('disabled', true);
        $button.html('<span class="spinner" style="float: none; margin: 0 5px 0 0; visibility: visible;"></span> Expanding...');
        $status.html('<span style="color: #2271b1; font-weight: bold;">⏳ Expanding description with AI... This may take 10-30 seconds.</span>');
        
        $.ajax({
            url: alm_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_expand_lesson_description',
                lesson_id: lessonId,
                current_description: currentDescription,
                nonce: alm_admin.nonce
            },
            timeout: 60000, // 60 second timeout
            success: function(response) {
                
                if (response.success) {
                    // Replace existing description with expanded one
                    $textarea.val(response.data.description);
                    $status.html('<span style="color: #46b450; font-weight: bold;">✅ ' + response.data.message + '</span>');
                    
                    // Highlight the textarea briefly to show it was updated
                    $textarea.css('background-color', '#d4edda');
                    setTimeout(function() {
                        $textarea.css('background-color', '');
                    }, 2000);
                    
                    // Clear status after 5 seconds
                    setTimeout(function() {
                        $status.html('');
                    }, 5000);
                } else {
                    console.error('AJAX error:', response);
                    $status.html('<span style="color: #dc3232; font-weight: bold;">❌ ' + (response.data && response.data.message ? response.data.message : 'Error expanding description') + '</span>');
                }
                
                $button.prop('disabled', false);
                $button.html(originalText);
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error, xhr);
                var errorMsg = 'AJAX error occurred';
                if (status === 'timeout') {
                    errorMsg = 'Request timed out. Please try again.';
                } else if (xhr.responseText) {
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.data && errorResponse.data.message) {
                            errorMsg = errorResponse.data.message;
                        }
                    } catch(e) {
                        // Ignore parse errors
                    }
                }
                $status.html('<span style="color: #dc3232; font-weight: bold;">❌ ' + errorMsg + '</span>');
                $button.prop('disabled', false);
                $button.html(originalText);
            }
        });
    });
});
