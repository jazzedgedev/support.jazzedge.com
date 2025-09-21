/**
 * Admin JavaScript for Fluent Support AI Integration
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Test API Key
    $('#test-api-key').on('click', function() {
        var apiKey = $('#openai_api_key').val();
        var $result = $('#api-test-result');
        
        if (!apiKey) {
            $result.html('<span class="error">API key is required</span>');
            return;
        }
        
        $result.html('<span class="testing">Testing...</span>');
        
        $.ajax({
            url: fluentSupportAI.ajaxUrl,
            type: 'POST',
            data: {
                action: 'fluent_support_ai_test_api',
                api_key: apiKey,
                nonce: fluentSupportAI.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<span class="success">' + response.data + '</span>');
                } else {
                    $result.html('<span class="error">' + response.data + '</span>');
                }
            },
            error: function() {
                $result.html('<span class="error">Connection failed</span>');
            }
        });
    });
    
    // Add Prompt Form
    $('#add-prompt-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitButton = $form.find('button[type="submit"]');
        var formData = $form.serialize();
        
        $submitButton.prop('disabled', true).text('Adding...');
        
        $.ajax({
            url: fluentSupportAI.ajaxUrl,
            type: 'POST',
            data: formData + '&action=fluent_support_ai_save_prompt&nonce=' + fluentSupportAI.nonce,
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload to show new prompt
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while adding the prompt.');
            },
            complete: function() {
                $submitButton.prop('disabled', false).text('Add Prompt');
            }
        });
    });
    
    // Edit Prompt
    $('.edit-prompt').on('click', function() {
        var promptId = $(this).data('prompt-id');
        var $promptItem = $('.prompt-item[data-prompt-id="' + promptId + '"]');
        
        var name = $promptItem.find('h4').text();
        var description = $promptItem.find('.prompt-description').text();
        var content = $promptItem.find('pre').text();
        
        $('#edit_prompt_id').val(promptId);
        $('#edit_prompt_name').val(name);
        $('#edit_prompt_description').val(description);
        $('#edit_prompt_content').val(content);
        
        $('#edit-prompt-modal').show();
    });
    
    // Update Prompt
    $('#edit-prompt-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitButton = $form.find('button[type="submit"]');
        var formData = $form.serialize();
        
        $submitButton.prop('disabled', true).text('Updating...');
        
        $.ajax({
            url: fluentSupportAI.ajaxUrl,
            type: 'POST',
            data: formData + '&action=fluent_support_ai_save_prompt&nonce=' + fluentSupportAI.nonce,
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload to show updated prompt
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while updating the prompt.');
            },
            complete: function() {
                $submitButton.prop('disabled', false).text('Update Prompt');
            }
        });
    });
    
    // Delete Prompt
    $('.delete-prompt').on('click', function() {
        if (!confirm('Are you sure you want to delete this prompt?')) {
            return;
        }
        
        var promptId = $(this).data('prompt-id');
        var $button = $(this);
        
        $button.prop('disabled', true).text('Deleting...');
        
        $.ajax({
            url: fluentSupportAI.ajaxUrl,
            type: 'POST',
            data: {
                action: 'fluent_support_ai_delete_prompt',
                prompt_id: promptId,
                nonce: fluentSupportAI.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.prompt-item[data-prompt-id="' + promptId + '"]').fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while deleting the prompt.');
            },
            complete: function() {
                $button.prop('disabled', false).text('Delete');
            }
        });
    });
    
    // Close Modal
    $('.close, .cancel-edit').on('click', function() {
        $('#edit-prompt-modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(e) {
        if (e.target.id === 'edit-prompt-modal') {
            $('#edit-prompt-modal').hide();
        }
    });
    
    // Form validation
    $('#add-prompt-form, #edit-prompt-form').on('submit', function(e) {
        var $form = $(this);
        var name = $form.find('input[name="prompt_name"]').val();
        var content = $form.find('textarea[name="prompt_content"]').val();
        
        if (!name.trim()) {
            alert('Please enter a prompt name.');
            e.preventDefault();
            return false;
        }
        
        if (!content.trim()) {
            alert('Please enter prompt content.');
            e.preventDefault();
            return false;
        }
        
        if (content.indexOf('{ticket_content}') === -1) {
            alert('Prompt content must include {ticket_content} placeholder.');
            e.preventDefault();
            return false;
        }
    });
});
