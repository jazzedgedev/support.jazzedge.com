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
});
