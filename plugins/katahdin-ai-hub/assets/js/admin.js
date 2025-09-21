/* Katahdin AI Hub Admin JavaScript */

jQuery(document).ready(function($) {
    'use strict';
    
    // Test API connection
    $('#test-api-connection').on('click', function() {
        var button = $(this);
        var result = $('#api-test-result');
        
        button.prop('disabled', true).text('Testing...');
        result.hide();
        
        $.post(ajaxurl, {
            action: 'katahdin_ai_hub_test_api',
            nonce: katahdin_ai_hub.nonce
        }, function(response) {
            if (response.success) {
                result.removeClass('error').addClass('success')
                    .html('<strong>Success:</strong> ' + response.data.message)
                    .show();
            } else {
                result.removeClass('success').addClass('error')
                    .html('<strong>Error:</strong> ' + response.data)
                    .show();
            }
        }).always(function() {
            button.prop('disabled', false).text('Test API Connection');
        });
    });
    
    // Reset quota
    $('.reset-quota').on('click', function() {
        var button = $(this);
        var pluginId = button.data('plugin-id');
        
        if (!confirm('Are you sure you want to reset the quota for this plugin?')) {
            return;
        }
        
        button.prop('disabled', true).text('Resetting...');
        
        $.post(ajaxurl, {
            action: 'katahdin_ai_hub_reset_quota',
            plugin_id: pluginId,
            nonce: katahdin_ai_hub.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.data);
                button.prop('disabled', false).text('Reset Quota');
            }
        });
    });
    
    // Activate/Deactivate plugin
    $('.activate-plugin, .deactivate-plugin').on('click', function() {
        var button = $(this);
        var pluginId = button.data('plugin-id');
        var action = button.hasClass('activate-plugin') ? 'activate' : 'deactivate';
        
        button.prop('disabled', true);
        
        $.post(ajaxurl, {
            action: 'katahdin_ai_hub_' + action + '_plugin',
            plugin_id: pluginId,
            nonce: katahdin_ai_hub.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.data);
                button.prop('disabled', false);
            }
        });
    });
    
    // Delete plugin
    $('.delete-plugin').on('click', function() {
        var button = $(this);
        var pluginId = button.data('plugin-id');
        
        if (!confirm('Are you sure you want to delete this plugin registration? This action cannot be undone.')) {
            return;
        }
        
        button.prop('disabled', true);
        
        $.post(ajaxurl, {
            action: 'katahdin_ai_hub_delete_plugin',
            plugin_id: pluginId,
            nonce: katahdin_ai_hub.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.data);
                button.prop('disabled', false);
            }
        });
    });
    
    // Export data
    $('.export-data').on('click', function() {
        var button = $(this);
        var pluginId = button.data('plugin-id') || '';
        var days = button.data('days') || 30;
        
        button.prop('disabled', true).text('Exporting...');
        
        var form = $('<form method="post" action="' + ajaxurl + '">');
        form.append('<input type="hidden" name="action" value="katahdin_ai_hub_export_data">');
        form.append('<input type="hidden" name="plugin_id" value="' + pluginId + '">');
        form.append('<input type="hidden" name="days" value="' + days + '">');
        form.append('<input type="hidden" name="nonce" value="' + katahdin_ai_hub.nonce + '">');
        
        $('body').append(form);
        form.submit();
        form.remove();
        
        setTimeout(function() {
            button.prop('disabled', false).text('Export Data');
        }, 2000);
    });
    
    // Auto-refresh stats every 30 seconds
    if ($('.katahdin-ai-hub-dashboard').length > 0) {
        setInterval(function() {
            // Only refresh if user is on dashboard page
            if (window.location.href.indexOf('katahdin-ai-hub') > -1 && 
                window.location.href.indexOf('analytics') === -1) {
                location.reload();
            }
        }, 30000);
    }
    
    // Form validation and success messages
    $('#katahdin-settings-form').on('submit', function() {
        var form = $(this);
        var submitBtn = form.find('input[type="submit"], button[type="submit"]');
        
        submitBtn.prop('disabled', true).val('Saving...');
        
        // Show success message after form submission
        setTimeout(function() {
            var successMsg = $('<div class="notice notice-success is-dismissible"><p><strong>Settings saved successfully!</strong></p></div>');
            $('.wrap h1').after(successMsg);
            
            // Auto-dismiss after 3 seconds
            setTimeout(function() {
                successMsg.fadeOut();
            }, 3000);
        }, 1000);
        
        setTimeout(function() {
            submitBtn.prop('disabled', false).val('Save Settings');
        }, 2000);
    });
    
    // Tooltips
    $('[data-tooltip]').hover(function() {
        var tooltip = $(this).data('tooltip');
        var tooltipEl = $('<div class="katahdin-tooltip">' + tooltip + '</div>');
        
        $('body').append(tooltipEl);
        
        var offset = $(this).offset();
        tooltipEl.css({
            position: 'absolute',
            top: offset.top - tooltipEl.outerHeight() - 5,
            left: offset.left + ($(this).outerWidth() / 2) - (tooltipEl.outerWidth() / 2),
            background: '#1d2327',
            color: 'white',
            padding: '5px 10px',
            borderRadius: '4px',
            fontSize: '12px',
            zIndex: 9999
        });
    }, function() {
        $('.katahdin-tooltip').remove();
    });
});
