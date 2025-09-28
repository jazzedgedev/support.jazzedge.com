/**
 * Katahdin AI Forms Admin JavaScript
 * Handles AJAX interactions and UI enhancements
 */

(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        KatahdinAIForms.init();
    });
    
    // Main plugin object
    window.KatahdinAIForms = {
        
        // Initialize the plugin
        init: function() {
            this.bindEvents();
            this.initTooltips();
            this.initStatusUpdates();
        },
        
        // Bind event handlers
        bindEvents: function() {
            // Test buttons
            $(document).on('click', '#test-forms', this.testForms);
            $(document).on('click', '#test-email', this.testEmail);
            $(document).on('click', '#comprehensive-debug', this.comprehensiveDebug);
            $(document).on('click', '#regenerate-secret', this.regenerateSecret);
            
            // Prompt management
            $(document).on('submit', '#add-prompt-form', this.addPrompt);
            $(document).on('click', '.delete-prompt', this.deletePrompt);
            $(document).on('click', '.toggle-prompt', this.togglePrompt);
            
            // Log management
            $(document).on('click', '.view-log', this.viewLog);
            $(document).on('click', '.delete-log', this.deleteLog);
            $(document).on('click', '#cleanup-logs', this.cleanupLogs);
            
            // Form validation
            $(document).on('blur', 'input[required]', this.validateField);
            $(document).on('blur', 'textarea[required]', this.validateField);
        },
        
        // Initialize tooltips
        initTooltips: function() {
            if ($.fn.tooltip) {
                $('[data-tooltip]').tooltip();
            }
        },
        
        // Initialize status updates
        initStatusUpdates: function() {
            // Auto-refresh status every 30 seconds
            setInterval(this.updateStatus, 30000);
        },
        
        // Test forms endpoint
        testForms: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $results = $('#test-results');
            
            KatahdinAIForms.setButtonLoading($button, 'Testing...');
            
            $.ajax({
                url: katahdin_ai_forms.ajax_url,
                type: 'POST',
                data: {
                    action: 'katahdin_ai_forms_test_forms',
                    nonce: katahdin_ai_forms.nonce
                },
                success: function(response) {
                    if (response.success) {
                        KatahdinAIForms.showStatus($results, 'success', '<strong>Success:</strong> ' + response.data.message);
                    } else {
                        KatahdinAIForms.showStatus($results, 'error', '<strong>Error:</strong> ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    KatahdinAIForms.showStatus($results, 'error', '<strong>Error:</strong> Request failed - ' + error);
                },
                complete: function() {
                    KatahdinAIForms.setButtonNormal($button, 'Test Forms Endpoint');
                }
            });
        },
        
        // Test email functionality
        testEmail: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $results = $('#test-results');
            
            KatahdinAIForms.setButtonLoading($button, 'Testing...');
            
            $.ajax({
                url: katahdin_ai_forms.ajax_url,
                type: 'POST',
                data: {
                    action: 'katahdin_ai_forms_test_email',
                    nonce: katahdin_ai_forms.nonce
                },
                success: function(response) {
                    if (response.success) {
                        KatahdinAIForms.showStatus($results, 'success', '<strong>Success:</strong> ' + response.data.message);
                    } else {
                        KatahdinAIForms.showStatus($results, 'error', '<strong>Error:</strong> ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    KatahdinAIForms.showStatus($results, 'error', '<strong>Error:</strong> Request failed - ' + error);
                },
                complete: function() {
                    KatahdinAIForms.setButtonNormal($button, 'Test Email');
                }
            });
        },
        
        // Comprehensive debug
        comprehensiveDebug: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $results = $('#test-results');
            
            KatahdinAIForms.setButtonLoading($button, 'Debugging...');
            
            $.ajax({
                url: katahdin_ai_forms.ajax_url,
                type: 'POST',
                data: {
                    action: 'katahdin_ai_forms_comprehensive_debug',
                    nonce: katahdin_ai_forms.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var debugHtml = '<strong>Debug Complete:</strong><br><pre>' + 
                                       JSON.stringify(response.data, null, 2) + '</pre>';
                        KatahdinAIForms.showStatus($results, 'info', debugHtml);
                    } else {
                        KatahdinAIForms.showStatus($results, 'error', '<strong>Debug Error:</strong> ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    KatahdinAIForms.showStatus($results, 'error', '<strong>Error:</strong> Debug request failed - ' + error);
                },
                complete: function() {
                    KatahdinAIForms.setButtonNormal($button, 'Comprehensive Debug');
                }
            });
        },
        
        // Regenerate webhook secret
        regenerateSecret: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $results = $('#test-results');
            
            if (!confirm('Are you sure you want to regenerate the webhook secret? This will invalidate any existing webhook configurations.')) {
                return;
            }
            
            KatahdinAIForms.setButtonLoading($button, 'Regenerating...');
            
            $.ajax({
                url: katahdin_ai_forms.ajax_url,
                type: 'POST',
                data: {
                    action: 'katahdin_ai_forms_regenerate_secret',
                    nonce: katahdin_ai_forms.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('input[readonly]').val(response.data.secret);
                        KatahdinAIForms.showStatus($results, 'success', '<strong>Success:</strong> ' + response.data.message);
                    } else {
                        KatahdinAIForms.showStatus($results, 'error', '<strong>Error:</strong> ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    KatahdinAIForms.showStatus($results, 'error', '<strong>Error:</strong> Request failed - ' + error);
                },
                complete: function() {
                    KatahdinAIForms.setButtonNormal($button, 'Regenerate Secret');
                }
            });
        },
        
        // Add new prompt
        addPrompt: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            
            // Validate form
            if (!KatahdinAIForms.validateForm($form)) {
                return;
            }
            
            KatahdinAIForms.setButtonLoading($button, 'Adding...');
            
            var formData = {
                action: 'katahdin_ai_forms_add_prompt',
                nonce: katahdin_ai_forms.nonce,
                title: $('#prompt-title').val(),
                prompt_id: $('#prompt-prompt-id').val(),
                prompt: $('#prompt-text').val(),
                email_address: $('#prompt-email').val(),
                email_subject: $('#prompt-subject').val()
            };
            
            $.ajax({
                url: katahdin_ai_forms.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Clear form
                        $form[0].reset();
                        // Reload page to show new prompt
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                        KatahdinAIForms.setButtonNormal($button, 'Add Prompt');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Request failed: ' + error);
                    KatahdinAIForms.setButtonNormal($button, 'Add Prompt');
                }
            });
        },
        
        // Delete prompt
        deletePrompt: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var id = $button.data('id');
            
            if (!confirm('Are you sure you want to delete this prompt? This action cannot be undone.')) {
                return;
            }
            
            KatahdinAIForms.setButtonLoading($button, 'Deleting...');
            
            $.ajax({
                url: katahdin_ai_forms.ajax_url,
                type: 'POST',
                data: {
                    action: 'katahdin_ai_forms_delete_prompt',
                    nonce: katahdin_ai_forms.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                        KatahdinAIForms.setButtonNormal($button, 'Delete');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Request failed: ' + error);
                    KatahdinAIForms.setButtonNormal($button, 'Delete');
                }
            });
        },
        
        // Toggle prompt status
        togglePrompt: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var id = $button.data('id');
            
            KatahdinAIForms.setButtonLoading($button, 'Updating...');
            
            $.ajax({
                url: katahdin_ai_forms.ajax_url,
                type: 'POST',
                data: {
                    action: 'katahdin_ai_forms_toggle_prompt',
                    nonce: katahdin_ai_forms.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                        KatahdinAIForms.setButtonNormal($button, 'Toggle');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Request failed: ' + error);
                    KatahdinAIForms.setButtonNormal($button, 'Toggle');
                }
            });
        },
        
        // View log details
        viewLog: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var id = $button.data('id');
            
            KatahdinAIForms.setButtonLoading($button, 'Loading...');
            
            $.ajax({
                url: katahdin_ai_forms.ajax_url,
                type: 'POST',
                data: {
                    action: 'katahdin_ai_forms_get_log_details',
                    nonce: katahdin_ai_forms.nonce,
                    log_id: id
                },
                success: function(response) {
                    if (response.success) {
                        KatahdinAIForms.showLogModal(response.data);
                    } else {
                        alert('Error: ' + response.data);
                    }
                    KatahdinAIForms.setButtonNormal($button, 'View');
                },
                error: function(xhr, status, error) {
                    alert('Request failed: ' + error);
                    KatahdinAIForms.setButtonNormal($button, 'View');
                }
            });
        },
        
        // Delete log
        deleteLog: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var id = $button.data('id');
            
            if (!confirm('Are you sure you want to delete this log entry?')) {
                return;
            }
            
            KatahdinAIForms.setButtonLoading($button, 'Deleting...');
            
            $.ajax({
                url: katahdin_ai_forms.ajax_url,
                type: 'POST',
                data: {
                    action: 'katahdin_ai_forms_delete_log',
                    nonce: katahdin_ai_forms.nonce,
                    log_id: id
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                        KatahdinAIForms.setButtonNormal($button, 'Delete');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Request failed: ' + error);
                    KatahdinAIForms.setButtonNormal($button, 'Delete');
                }
            });
        },
        
        // Cleanup logs
        cleanupLogs: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var retentionDays = prompt('Enter retention days (default: 30):', '30');
            
            if (!retentionDays || isNaN(retentionDays)) {
                return;
            }
            
            if (!confirm('Are you sure you want to cleanup logs older than ' + retentionDays + ' days?')) {
                return;
            }
            
            KatahdinAIForms.setButtonLoading($button, 'Cleaning...');
            
            $.ajax({
                url: katahdin_ai_forms.ajax_url,
                type: 'POST',
                data: {
                    action: 'katahdin_ai_forms_cleanup_logs',
                    nonce: katahdin_ai_forms.nonce,
                    retention_days: parseInt(retentionDays)
                },
                success: function(response) {
                    if (response.success) {
                        alert('Cleanup completed. Deleted ' + response.data.deleted_count + ' log entries.');
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.error);
                        KatahdinAIForms.setButtonNormal($button, 'Cleanup Logs');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Request failed: ' + error);
                    KatahdinAIForms.setButtonNormal($button, 'Cleanup Logs');
                }
            });
        },
        
        // Validate form
        validateForm: function($form) {
            var isValid = true;
            
            $form.find('input[required], textarea[required]').each(function() {
                if (!KatahdinAIForms.validateField.call(this)) {
                    isValid = false;
                }
            });
            
            return isValid;
        },
        
        // Validate individual field
        validateField: function() {
            var $field = $(this);
            var value = $field.val().trim();
            var isValid = true;
            
            // Remove existing error styling
            $field.removeClass('error');
            $field.siblings('.field-error').remove();
            
            // Check if field is empty
            if (!value) {
                KatahdinAIForms.showFieldError($field, 'This field is required');
                isValid = false;
            }
            
            // Email validation
            if ($field.attr('type') === 'email' && value) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    KatahdinAIForms.showFieldError($field, 'Please enter a valid email address');
                    isValid = false;
                }
            }
            
            // Prompt ID validation
            if ($field.attr('name') === 'prompt_id' && value) {
                var promptIdRegex = /^[a-zA-Z0-9_-]+$/;
                if (!promptIdRegex.test(value)) {
                    KatahdinAIForms.showFieldError($field, 'Prompt ID can only contain letters, numbers, underscores, and hyphens');
                    isValid = false;
                }
            }
            
            return isValid;
        },
        
        // Show field error
        showFieldError: function($field, message) {
            $field.addClass('error');
            $field.after('<div class="field-error" style="color: #d63638; font-size: 12px; margin-top: 5px;">' + message + '</div>');
        },
        
        // Set button loading state
        setButtonLoading: function($button, text) {
            $button.prop('disabled', true).text(text);
        },
        
        // Set button normal state
        setButtonNormal: function($button, text) {
            $button.prop('disabled', false).text(text);
        },
        
        // Show status message
        showStatus: function($container, type, message) {
            $container.removeClass('success error warning info')
                     .addClass(type)
                     .html(message)
                     .show();
            
            // Auto-hide after 10 seconds
            setTimeout(function() {
                $container.fadeOut();
            }, 10000);
        },
        
        // Show log modal
        showLogModal: function(logData) {
            var modalHtml = '<div id="log-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">' +
                           '<div style="background: white; padding: 20px; border-radius: 5px; max-width: 80%; max-height: 80%; overflow: auto;">' +
                           '<h3>Log Details</h3>' +
                           '<pre>' + JSON.stringify(logData, null, 2) + '</pre>' +
                           '<button onclick="jQuery(\'#log-modal\').remove()" class="button">Close</button>' +
                           '</div></div>';
            
            $('body').append(modalHtml);
        },
        
        // Update status
        updateStatus: function() {
            // This could be used to periodically update status information
            // For now, it's a placeholder for future enhancements
        }
    };
    
})(jQuery);
