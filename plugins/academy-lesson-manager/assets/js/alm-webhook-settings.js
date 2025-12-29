/**
 * Academy Lesson Manager - Webhook Settings JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Copy log to clipboard
        $(document).on('click', '.copy-log-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var logId = $btn.attr('data-log-id') || $btn.data('log-id');
            
            if (!logId) {
                alert('Error: Could not find log ID.');
                return;
            }
            
            // Get JSON from hidden textarea
            var $textarea = $('#log-json-' + logId);
            
            if ($textarea.length === 0) {
                // Fallback: try to get from the details/pre section
                var $details = $btn.closest('.notice').find('details pre');
                if ($details.length > 0) {
                    var logJson = $details.text();
                    copyToClipboard(logJson, $btn);
                    return;
                }
                alert('Error: Could not find log data. Please use "View Full Debug Info" and copy manually.');
                return;
            }
            
            var logJson = $textarea.val() || $textarea.text();
            
            if (!logJson || logJson.trim() === '') {
                alert('No log data found to copy.');
                return;
            }
            
            copyToClipboard(logJson, $btn);
        });
        
        // Unified copy function
        function copyToClipboard(text, $btn) {
            // Use modern Clipboard API if available
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopySuccess($btn);
                }).catch(function(err) {
                    console.error('Clipboard API failed:', err);
                    // Fallback to execCommand
                    copyWithExecCommand(text, $btn);
                });
            } else {
                // Fallback to execCommand
                copyWithExecCommand(text, $btn);
            }
        }
        
        // Fallback copy function using execCommand
        function copyWithExecCommand(text, $btn) {
            var $temp = $('<textarea>');
            $temp.css({
                'position': 'fixed',
                'left': '-9999px',
                'top': '0'
            });
            $temp.val(text);
            $('body').append($temp);
            $temp[0].select();
            $temp[0].setSelectionRange(0, 99999); // For mobile devices
            
            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    showCopySuccess($btn);
                } else {
                    alert('Copy command failed. Please select and copy manually from the "View Full Debug Info" section.');
                }
            } catch (err) {
                console.error('execCommand failed:', err);
                alert('Failed to copy. Please select and copy manually from the "View Full Debug Info" section.');
            }
            
            $temp.remove();
        }
        
        // Show copy success message
        function showCopySuccess($btn) {
            var originalText = $btn.text();
            $btn.text('Copied!').prop('disabled', true);
            setTimeout(function() {
                $btn.text(originalText).prop('disabled', false);
            }, 2000);
        }
        
        // Refresh logs
        $('#refresh-logs-btn').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).text('Refreshing...');
            
            $.ajax({
                url: almWebhookSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'alm_get_webhook_logs',
                    nonce: almWebhookSettings.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to refresh logs: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('Failed to refresh logs. Please refresh the page.');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Refresh Logs');
                }
            });
        });
        
        // Clear logs
        $('#clear-logs-btn').on('click', function() {
            if (!confirm('Are you sure you want to clear all webhook logs? This cannot be undone.')) {
                return;
            }
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('Clearing...');
            
            $.ajax({
                url: almWebhookSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'alm_clear_webhook_logs',
                    nonce: almWebhookSettings.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to clear logs: ' + (response.data || 'Unknown error'));
                        $btn.prop('disabled', false).text('Clear Logs');
                    }
                },
                error: function() {
                    alert('Failed to clear logs. Please try again.');
                    $btn.prop('disabled', false).text('Clear Logs');
                }
            });
        });
        
        // Download logs as JSON
        $('#download-logs-btn').on('click', function() {
            $.ajax({
                url: almWebhookSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'alm_get_webhook_logs',
                    nonce: almWebhookSettings.nonce
                },
                success: function(response) {
                    if (response.success && response.data && response.data.length > 0) {
                        var jsonStr = JSON.stringify(response.data, null, 2);
                        var blob = new Blob([jsonStr], { type: 'application/json' });
                        var url = URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'alm-webhook-logs-' + new Date().toISOString().split('T')[0] + '.json';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                    } else {
                        alert('No logs to download.');
                    }
                },
                error: function() {
                    alert('Failed to download logs. Please try again.');
                }
            });
        });
        
    });
    
})(jQuery);

