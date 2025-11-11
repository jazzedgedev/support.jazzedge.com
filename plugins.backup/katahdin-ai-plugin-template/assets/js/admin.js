/* Katahdin AI Plugin Template Admin JavaScript */

jQuery(document).ready(function($) {
    
    // Plugin status refresh
    $('#refresh-status').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Refreshing...');
        
        loadPluginStatus().always(function() {
            $btn.prop('disabled', false).text('Refresh Status');
        });
    });
    
    // Test plugin functionality
    $('#test-plugin').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Testing...');
        
        $.post(ajaxurl, {
            action: 'katahdin_ai_plugin_template_test',
            nonce: katahdinAIPluginTemplate.nonce
        }, function(response) {
            if (response.success) {
                showTestResults('success', '✅ Test passed!', response.data);
            } else {
                showTestResults('error', '❌ Test failed: ' + response.data);
            }
        }).fail(function() {
            showTestResults('error', '❌ AJAX Error: Could not test plugin');
        }).always(function() {
            $btn.prop('disabled', false).text('Test Plugin');
        });
    });
    
    // Debug plugin
    $('#debug-plugin').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Debugging...');
        
        $.post(ajaxurl, {
            action: 'katahdin_ai_plugin_template_debug',
            nonce: katahdinAIPluginTemplate.nonce
        }, function(response) {
            if (response.success) {
                showDebugResults(response.data);
            } else {
                showTestResults('error', '❌ Debug failed: ' + response.data);
            }
        }).fail(function() {
            showTestResults('error', '❌ AJAX Error: Could not debug plugin');
        }).always(function() {
            $btn.prop('disabled', false).text('Debug Info');
        });
    });
    
    // Load initial data
    loadPluginStatus();
    loadUsageStats();
    
    // Helper functions
    function loadPluginStatus() {
        $('#plugin-status').html('<p>Loading status...</p>');
        
        return $.get(katahdinAIPluginTemplate.restUrl + 'status')
            .done(function(response) {
                if (response.success) {
                    var html = '<div class="katahdin-plugin-stats">';
                    html += '<div class="katahdin-plugin-stat"><strong>' + response.plugin + '</strong><span>Plugin Name</span></div>';
                    html += '<div class="katahdin-plugin-stat"><strong>' + response.version + '</strong><span>Version</span></div>';
                    html += '<div class="katahdin-plugin-stat ' + (response.hub_status === 'Connected' ? 'success' : 'error') + '"><strong>' + response.hub_status + '</strong><span>Hub Status</span></div>';
                    html += '</div>';
                    $('#plugin-status').html(html);
                } else {
                    $('#plugin-status').html('<p style="color: red;">Error loading status</p>');
                }
            })
            .fail(function() {
                $('#plugin-status').html('<p style="color: red;">AJAX Error loading status</p>');
            });
    }
    
    function loadUsageStats() {
        $('#usage-stats').html('<p>Loading statistics...</p>');
        
        // This would typically come from the hub's usage tracking
        var stats = {
            'total_requests': 0,
            'successful_requests': 0,
            'failed_requests': 0,
            'tokens_used': 0
        };
        
        var html = '<div class="katahdin-plugin-stats">';
        html += '<div class="katahdin-plugin-stat"><strong>' + stats.total_requests + '</strong><span>Total Requests</span></div>';
        html += '<div class="katahdin-plugin-stat success"><strong>' + stats.successful_requests + '</strong><span>Successful</span></div>';
        html += '<div class="katahdin-plugin-stat error"><strong>' + stats.failed_requests + '</strong><span>Failed</span></div>';
        html += '<div class="katahdin-plugin-stat warning"><strong>' + stats.tokens_used + '</strong><span>Tokens Used</span></div>';
        html += '</div>';
        $('#usage-stats').html(html);
    }
    
    function showTestResults(type, message, data) {
        var html = '<div class="notice notice-' + type + '"><p><strong>' + message + '</strong>';
        
        if (data) {
            html += '<br><pre style="background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; margin-top: 10px;">' + JSON.stringify(data, null, 2) + '</pre>';
        }
        
        html += '</p></div>';
        $('#test-results').html(html);
    }
    
    function showDebugResults(data) {
        var html = '<div class="katahdin-plugin-debug">';
        html += '<h3>🔍 Debug Information</h3>';
        html += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        html += '</div>';
        $('#test-results').html(html);
    }
    
    // Auto-refresh status every 30 seconds
    setInterval(function() {
        if ($('#plugin-status').is(':visible')) {
            loadPluginStatus();
        }
    }, 30000);
    
    // Copy to clipboard functionality
    $(document).on('click', '.copy-debug-btn', function() {
        var targetId = $(this).data('target');
        var content = $('#' + targetId).text();
        
        // Create a temporary textarea to copy the content
        var tempTextarea = $('<textarea>');
        tempTextarea.val(content);
        $('body').append(tempTextarea);
        tempTextarea.select();
        
        try {
            document.execCommand('copy');
            $(this).text('✅ Copied!').css('color', 'green');
            setTimeout(function() {
                $('.copy-debug-btn').text('📋 Copy Debug Results').css('color', '');
            }, 2000);
        } catch (err) {
            alert('Failed to copy. Please select and copy manually.');
        }
        
        tempTextarea.remove();
    });
    
    // Toggle debug sections
    $(document).on('click', '.toggle-debug-btn', function() {
        var $debugOutput = $(this).closest('.katahdin-plugin-debug').find('.debug-output');
        var $btn = $(this);
        
        if ($debugOutput.is(':visible')) {
            $debugOutput.slideUp();
            $btn.text('👁️ Show Debug');
        } else {
            $debugOutput.slideDown();
            $btn.text('👁️ Hide Debug');
        }
    });
});
