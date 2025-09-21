jQuery(document).ready(function($) {
    console.log('Admin bar JS loaded');
    
    // Simple test - just show alert when clicked
    $(document).on('click', '.ai-copy-ticket-item', function(e) {
        e.preventDefault();
        alert('Copy ticket clicked! Current URL: ' + window.location.href);
    });
    
    // Handle AI prompt clicks in admin bar
    $('.ai-prompt-item').on('click', function(e) {
        e.preventDefault();
        
        var promptId = $(this).data('prompt-id');
        var promptName = $(this).text().trim();
        
        // Show loading state
        $(this).html('⏳ Generating...');
        
        // Get ticket content from the page
        var ticketContent = '';
        $('.fs_thread_body p').each(function() {
            ticketContent += $(this).text() + ' ';
        });
        
        // Get ticket ID from URL
        var ticketId = 0;
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('ticket_id')) {
            ticketId = urlParams.get('ticket_id');
        }
        
        // Debug: Show what we're sending on screen
        var debugInfo = 'DEBUG: Sending AJAX request<br>Prompt ID: ' + promptId + '<br>Ticket ID: ' + ticketId + '<br>URL: ' + fluentSupportAI.ajaxUrl;
        $('body').prepend('<div id="debug-info" style="position: fixed; top: 0; left: 0; background: #000; color: #fff; padding: 10px; z-index: 999999; font-family: monospace; font-size: 12px;">' + debugInfo + '</div>');
        
        // Make AJAX request
        $.ajax({
            url: fluentSupportAI.ajaxUrl,
            type: 'POST',
            data: {
                action: 'fluent_support_ai_generate_reply',
                prompt_id: promptId,
                ticket_id: ticketId,
                nonce: fluentSupportAI.nonce
            },
            success: function(response) {
                // Update debug info on screen
                var responseDebug = 'DEBUG: AJAX Success Response<br>' + JSON.stringify(response, null, 2).replace(/\n/g, '<br>');
                $('#debug-info').html(responseDebug);
                
                if (response.success) {
                    // Get the AI reply content
                    var content = '';
                    if (response.data.reply) {
                        content = response.data.reply;
                    } else if (typeof response.data === 'string') {
                        content = response.data;
                    } else {
                        content = JSON.stringify(response.data, null, 2);
                    }
                    
                    // Copy to clipboard
                    navigator.clipboard.writeText(content).then(function() {
                        alert('AI Reply Generated and Copied to Clipboard!\n\n' + content);
                    }).catch(function(err) {
                        alert('AI Reply Generated!\n\n' + content + '\n\n(Clipboard copy failed: ' + err + ')');
                    });
                    
                    // Show content in debug box
                    $('#debug-info').html('AI REPLY COPIED TO CLIPBOARD:<br><br>' + content.replace(/\n/g, '<br>'));
                } else {
                    alert('Error: ' + (response.data || 'Failed to generate reply'));
                }
            },
            error: function(xhr, status, error) {
                // Update debug info on screen
                var errorDebug = 'DEBUG: AJAX Error<br>Status: ' + status + '<br>Error: ' + error + '<br>Response: ' + xhr.responseText;
                $('#debug-info').html(errorDebug);
            },
            complete: function() {
                // Restore original text
                $('.ai-prompt-item[data-prompt-id="' + promptId + '"]').html(promptName);
            }
        });
    });
});
