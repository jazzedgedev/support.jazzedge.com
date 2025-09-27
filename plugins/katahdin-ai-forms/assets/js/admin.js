/**
 * Katahdin AI Webhook Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize admin functionality
    initAdminInterface();
    
    function initAdminInterface() {
        // Copy webhook URL functionality
        initCopyUrlButton();
        
        // Regenerate secret functionality
        initRegenerateSecretButton();
        
        // Test webhook functionality
        initTestWebhookButton();
        
        // Test email functionality
        initTestEmailButton();
        
        // Form validation
        initFormValidation();
    }
    
    function initCopyUrlButton() {
        $('.copy-url-btn').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var url = $btn.data('url');
            
            if (navigator.clipboard && window.isSecureContext) {
                // Use modern clipboard API
                navigator.clipboard.writeText(url).then(function() {
                    showSuccessMessage('Webhook URL copied to clipboard!');
                }).catch(function(err) {
                    console.error('Failed to copy URL: ', err);
                    fallbackCopyTextToClipboard(url);
                });
            } else {
                // Fallback for older browsers
                fallbackCopyTextToClipboard(url);
            }
        });
    }
    
    function fallbackCopyTextToClipboard(text) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        
        // Avoid scrolling to bottom
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.position = 'fixed';
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                showSuccessMessage('Webhook URL copied to clipboard!');
            } else {
                showErrorMessage('Failed to copy URL to clipboard');
            }
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
            showErrorMessage('Failed to copy URL to clipboard');
        }
        
        document.body.removeChild(textArea);
    }
    
    function initRegenerateSecretButton() {
        $('.regenerate-secret-btn').on('click', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to regenerate the webhook secret? This will break existing integrations until they are updated.')) {
                var $btn = $(this);
                $btn.prop('disabled', true).text('Regenerating...');
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_webhook_regenerate_secret',
                    nonce: katahdin_ai_webhook.nonce
                }, function(response) {
                    $btn.prop('disabled', false).text('Regenerate');
                    
                    if (response.success) {
                        showSuccessMessage('Secret regenerated successfully!');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showErrorMessage('Error regenerating secret: ' + response.data);
                    }
                }).fail(function() {
                    $btn.prop('disabled', false).text('Regenerate');
                    showErrorMessage('Network error occurred while regenerating secret');
                });
            }
        });
    }
    
    function initTestWebhookButton() {
        $('.test-webhook-btn').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var $results = $('#test-results');
            var $output = $('#test-output');
            
            $btn.prop('disabled', true).text('Testing...');
            $results.show();
            $output.html('<div class="loading">Testing webhook...</div>');
            
            $.post(ajaxurl, {
                action: 'katahdin_ai_webhook_test_webhook',
                nonce: katahdin_ai_webhook.nonce
            }, function(response) {
                $btn.prop('disabled', false).text('Test Webhook');
                
                if (response.success) {
                    var html = '<div class="success-message">';
                    html += '<strong>✓ Webhook test successful!</strong><br>';
                    html += '<pre>' + JSON.stringify(response.data, null, 2) + '</pre>';
                    html += '</div>';
                    $output.html(html);
                } else {
                    var html = '<div class="error-message">';
                    html += '<strong>✗ Webhook test failed:</strong><br>';
                    html += '<pre>' + response.data + '</pre>';
                    html += '</div>';
                    $output.html(html);
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('Test Webhook');
                $output.html('<div class="error-message"><strong>✗ Network error occurred during webhook test</strong></div>');
            });
        });
    }
    
    function initTestEmailButton() {
        $('.test-email-btn').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var $results = $('#test-results');
            var $output = $('#test-output');
            
            $btn.prop('disabled', true).text('Testing...');
            $results.show();
            $output.html('<div class="loading">Testing email...</div>');
            
            $.post(ajaxurl, {
                action: 'katahdin_ai_webhook_test_email',
                nonce: katahdin_ai_webhook.nonce
            }, function(response) {
                $btn.prop('disabled', false).text('Test Email');
                
                if (response.success) {
                    var html = '<div class="success-message">';
                    html += '<strong>✓ Email test successful!</strong><br>';
                    html += '<pre>' + JSON.stringify(response.data, null, 2) + '</pre>';
                    html += '</div>';
                    $output.html(html);
                } else {
                    var html = '<div class="error-message">';
                    html += '<strong>✗ Email test failed:</strong><br>';
                    html += '<pre>' + response.data + '</pre>';
                    html += '</div>';
                    $output.html(html);
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('Test Email');
                $output.html('<div class="error-message"><strong>✗ Network error occurred during email test</strong></div>');
            });
        });
    }
    
    function initFormValidation() {
        // Validate email field
        $('input[name="katahdin_ai_webhook_email"]').on('blur', function() {
            var email = $(this).val();
            var $field = $(this);
            
            if (email && !isValidEmail(email)) {
                $field.addClass('error');
                showFieldError($field, 'Please enter a valid email address');
            } else {
                $field.removeClass('error');
                hideFieldError($field);
            }
        });
        
        // Validate max tokens
        $('input[name="katahdin_ai_webhook_max_tokens"]').on('blur', function() {
            var tokens = parseInt($(this).val());
            var $field = $(this);
            
            if (tokens < 1 || tokens > 4000) {
                $field.addClass('error');
                showFieldError($field, 'Max tokens must be between 1 and 4000');
            } else {
                $field.removeClass('error');
                hideFieldError($field);
            }
        });
        
        // Validate temperature
        $('input[name="katahdin_ai_webhook_temperature"]').on('blur', function() {
            var temp = parseFloat($(this).val());
            var $field = $(this);
            
            if (temp < 0 || temp > 2) {
                $field.addClass('error');
                showFieldError($field, 'Temperature must be between 0 and 2');
            } else {
                $field.removeClass('error');
                hideFieldError($field);
            }
        });
    }
    
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function showFieldError($field, message) {
        hideFieldError($field);
        $field.after('<div class="field-error" style="color: #dc3232; font-size: 12px; margin-top: 5px;">' + message + '</div>');
    }
    
    function hideFieldError($field) {
        $field.siblings('.field-error').remove();
    }
    
    function showSuccessMessage(message) {
        showMessage(message, 'success');
    }
    
    function showErrorMessage(message) {
        showMessage(message, 'error');
    }
    
    function showMessage(message, type) {
        var $message = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($message);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $message.fadeOut(function() {
                $message.remove();
            });
        }, 5000);
        
        // Make dismissible
        $message.on('click', '.notice-dismiss', function() {
            $message.fadeOut(function() {
                $message.remove();
            });
        });
    }
    
    // Auto-save settings on change (optional enhancement)
    function initAutoSave() {
        var autoSaveTimeout;
        
        $('.settings-form input, .settings-form textarea, .settings-form select').on('change', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(function() {
                // Auto-save could be implemented here
                console.log('Auto-save triggered');
            }, 2000);
        });
    }
    
    // Initialize auto-save if enabled
    // initAutoSave();
    
    // Handle form submission
    $('.settings-form').on('submit', function(e) {
        // Clear any existing field errors
        $('.field-error').remove();
        $('.error').removeClass('error');
        
        // Basic validation before submit
        var isValid = true;
        
        // Check email
        var email = $('input[name="katahdin_ai_webhook_email"]').val();
        if (email && !isValidEmail(email)) {
            showFieldError($('input[name="katahdin_ai_webhook_email"]'), 'Please enter a valid email address');
            isValid = false;
        }
        
        // Check max tokens
        var tokens = parseInt($('input[name="katahdin_ai_webhook_max_tokens"]').val());
        if (tokens < 1 || tokens > 4000) {
            showFieldError($('input[name="katahdin_ai_webhook_max_tokens"]'), 'Max tokens must be between 1 and 4000');
            isValid = false;
        }
        
        // Check temperature
        var temp = parseFloat($('input[name="katahdin_ai_webhook_temperature"]').val());
        if (temp < 0 || temp > 2) {
            showFieldError($('input[name="katahdin_ai_webhook_temperature"]'), 'Temperature must be between 0 and 2');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            showErrorMessage('Please fix the errors above before saving');
            return false;
        }
    });
});
