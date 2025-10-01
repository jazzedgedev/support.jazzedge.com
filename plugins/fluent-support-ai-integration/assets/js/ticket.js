/**
 * Ticket JavaScript for Fluent Support AI Integration
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Generate AI Reply
    $('#generate-ai-reply').on('click', function() {
        var $button = $(this);
        var $loading = $('#ai-loading');
        var $select = $('#ai-prompt-select');
        var promptId = $select.val();
        var ticketId = $button.data('ticket-id');
        
        if (!promptId) {
            alert(fluentSupportAI.strings.selectPrompt);
            return;
        }
        
        if (!ticketId) {
            alert('Ticket ID not found');
            return;
        }
        
        $button.prop('disabled', true);
        $loading.show();
        
        $.ajax({
            url: fluentSupportAI.ajaxUrl,
            type: 'POST',
            data: {
                action: 'fluent_support_ai_generate_reply',
                ticket_id: ticketId,
                prompt_id: promptId,
                nonce: fluentSupportAI.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Insert the generated content into the editor
                    insertIntoEditor(response.data.content);
                    
                    // Show success message
                    showMessage(fluentSupportAI.strings.generated, 'success');
                } else {
                    showMessage(response.data || fluentSupportAI.strings.error, 'error');
                }
            },
            error: function() {
                showMessage(fluentSupportAI.strings.error, 'error');
            },
            complete: function() {
                $button.prop('disabled', false);
                $loading.hide();
            }
        });
    });
    
    /**
     * Insert content into the WordPress editor
     */
    function insertIntoEditor(content) {
        // Try to find the active editor
        var editorId = null;
        
        // Check for TinyMCE editor
        if (typeof tinymce !== 'undefined') {
            var activeEditor = tinymce.activeEditor;
            if (activeEditor && !activeEditor.isHidden()) {
                activeEditor.setContent(content);
                return;
            }
        }
        
        // Check for textarea editor
        var $textarea = $('textarea.wp-editor-area, textarea.wp_vue_editor');
        if ($textarea.length) {
            $textarea.val(content);
            return;
        }
        
        // Fallback: try to find any textarea in the reply form
        var $replyTextarea = $('.wp-editor-container textarea, .reply-form textarea');
        if ($replyTextarea.length) {
            $replyTextarea.val(content);
            return;
        }
        
        // If we can't find an editor, show the content in an alert
        alert('Generated content:\n\n' + content);
    }
    
    /**
     * Show message to user
     */
    function showMessage(message, type) {
        var $message = $('<div class="ai-message ai-message-' + type + '">' + message + '</div>');
        
        // Remove existing messages
        $('.ai-message').remove();
        
        // Add new message
        $('.fluent-support-ai-interface').append($message);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    /**
     * Auto-suggest prompts based on ticket content
     */
    function suggestPrompts() {
        // This could be enhanced to analyze ticket content and suggest relevant prompts
        // For now, we'll just ensure the interface is ready
        console.log('AI Reply interface loaded');
    }
    
    // Initialize suggestions
    suggestPrompts();
    
    // Add keyboard shortcut (Ctrl+Shift+A)
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.keyCode === 65) { // Ctrl+Shift+A
            e.preventDefault();
            $('#generate-ai-reply').click();
        }
    });
    
    // Add tooltip for keyboard shortcut
    $('#generate-ai-reply').attr('title', 'Generate AI Reply (Ctrl+Shift+A)');
    
    // Handle autologin link copy functionality
    $(document).on('click', '.copy-autologin-btn', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var $button = $(this);
        var $successDiv = $(this).siblings('.copy-success-message');
        var originalText = $button.html();
        
        // Try modern clipboard API first
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(function() {
                showCopySuccess($button, originalText);
            }).catch(function(err) {
                // Fallback to older method
                fallbackCopyToClipboard(url, $button, originalText);
            });
        } else {
            // Fallback for older browsers or non-secure contexts
            fallbackCopyToClipboard(url, $button, originalText);
        }
    });
    
    /**
     * Fallback copy method for older browsers
     */
    function fallbackCopyToClipboard(text, $button, originalText) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopySuccess($button, originalText);
        } catch (err) {
            console.error('Failed to copy text: ', err);
            alert('Failed to copy link. Please copy manually: ' + text);
        }
        
        document.body.removeChild(textArea);
    }
    
    /**
     * Show copy success message
     */
    function showCopySuccess($button, originalText) {
        // Change button text to "Copied!"
        $button.html('✅ Copied!');
        $button.css('background', '#28a745');
        
        // Reset button after 2 seconds
        setTimeout(function() {
            $button.html(originalText);
            $button.css('background', '#007cba');
        }, 2000);
    }
});
