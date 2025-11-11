/**
 * Academy Lesson Manager - Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    // Run Migration
    $('#run-migration').on('click', function() {
        var $button = $(this);
        var $log = $('#migration-log');
        
        $button.prop('disabled', true).text('Running Migration...');
        $log.append('<div class="alm-log-message">Starting migration...</div>');
        
        $.ajax({
            url: almAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_run_migration',
                nonce: almAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $log.append('<div class="alm-log-message" style="color: green;">Migration completed successfully!</div>');
                    updateStats(response.stats);
                } else {
                    $log.append('<div class="alm-log-message" style="color: red;">Migration failed!</div>');
                }
                
                // Update log with all messages
                response.log.forEach(function(message) {
                    $log.append('<div class="alm-log-message">' + message + '</div>');
                });
                
                $log.scrollTop($log[0].scrollHeight);
            },
            error: function() {
                $log.append('<div class="alm-log-message" style="color: red;">Migration failed due to server error!</div>');
            },
            complete: function() {
                $button.prop('disabled', false).text('Run Migration');
            }
        });
    });
    
    // Refresh Stats
    $('#get-stats').on('click', function() {
        var $button = $(this);
        
        $button.prop('disabled', true).text('Refreshing...');
        
        $.ajax({
            url: almAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_get_stats',
                nonce: almAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStats(response.stats);
                }
            },
            complete: function() {
                $button.prop('disabled', false).text('Refresh Stats');
            }
        });
    });
    
    // Cleanup Legacy Lessons
    $('#cleanup-legacy').on('click', function() {
        if (!confirm('Are you sure you want to delete all legacy lessons? This action cannot be undone.')) {
            return;
        }
        
        var $button = $(this);
        var $log = $('#migration-log');
        
        $button.prop('disabled', true).text('Cleaning Up...');
        
        $.ajax({
            url: almAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_cleanup_legacy',
                nonce: almAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $log.append('<div class="alm-log-message" style="color: green;">Deleted ' + response.deleted + ' legacy lessons</div>');
                    
                    // Update log with all messages
                    response.log.forEach(function(message) {
                        $log.append('<div class="alm-log-message">' + message + '</div>');
                    });
                    
                    $log.scrollTop($log[0].scrollHeight);
                } else {
                    $log.append('<div class="alm-log-message" style="color: red;">Cleanup failed!</div>');
                }
            },
            error: function() {
                $log.append('<div class="alm-log-message" style="color: red;">Cleanup failed due to server error!</div>');
            },
            complete: function() {
                $button.prop('disabled', false).text('Cleanup Legacy Lessons');
            }
        });
    });
    
    // Clear Log
    $('#clear-log').on('click', function() {
        $('#migration-log').empty();
    });
    
    // Copy Log
    $('#copy-log').on('click', function() {
        var logText = $('#migration-log').text();
        
        // Create temporary textarea
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(logText).select();
        
        try {
            document.execCommand('copy');
            $(this).text('Copied!').css('color', 'green');
            setTimeout(function() {
                $('#copy-log').text('Copy Log').css('color', '');
            }, 2000);
        } catch (err) {
            alert('Failed to copy log');
        }
        
        $temp.remove();
    });
    
    // Update stats display
    function updateStats(stats) {
        $('.alm-card').each(function() {
            var $card = $(this);
            var cardType = $card.find('h3').text().toLowerCase().replace(' ', '_');
            
            if (stats[cardType]) {
                $card.find('.alm-number').each(function(index) {
                    var $number = $(this);
                    var $label = $number.next('.alm-label');
                    
                    if (index === 0) {
                        $number.text(stats[cardType].original || 0);
                    } else if (index === 1) {
                        $number.text(stats[cardType].migrated || stats[cardType].prepared || 0);
                    }
                });
            }
        });
    }
    
    // Debug Stats button
    $('#debug-stats').on('click', function() {
        var $button = $(this);
        var $debugSection = $('.alm-debug-section');
        var $debugResults = $('#debug-results');
        
        $button.prop('disabled', true).text('Running Debug...');
        
        $.ajax({
            url: almAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_debug_stats',
                nonce: almAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $debugResults.text(response.debug_results);
                    $debugSection.show();
                }
            },
            complete: function() {
                $button.prop('disabled', false).text('Debug Stats');
            }
        });
    });
    
    // Migrate Studio Recordings button
    $('#migrate-recordings').on('click', function() {
        if (!confirm('This will migrate studio event recordings to lesson chapters. Continue?')) {
            return;
        }
        
        var $button = $(this);
        var $log = $('#migration-log');
        
        $button.prop('disabled', true).text('Migrating Recordings...');
        
        $.ajax({
            url: almAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_migrate_recordings',
                nonce: almAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $log.append('<div class="alm-log-message" style="color: green;">Studio recordings migration completed!</div>');
                    
                    // Update log with all messages
                    response.log.forEach(function(message) {
                        $log.append('<div class="alm-log-message">' + message + '</div>');
                    });
                    
                    $log.scrollTop($log[0].scrollHeight);
                } else {
                    $log.append('<div class="alm-log-message" style="color: red;">Studio recordings migration failed!</div>');
                }
            },
            complete: function() {
                $button.prop('disabled', false).text('Migrate Studio Recordings');
            }
        });
    });
    
    // Fix Studio Legacy IDs button
    $('#fix-legacy-ids').on('click', function() {
        console.log('Fix Legacy IDs button clicked');
        
        if (!confirm('This will fix missing legacy_id metadata for studio events. Continue?')) {
            console.log('User cancelled');
            return;
        }
        
        console.log('Starting AJAX call to fix legacy IDs');
        
        var $button = $(this);
        var $log = $('#migration-log');
        
        $button.prop('disabled', true).text('Fixing Legacy IDs...');
        
        $.ajax({
            url: almAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_fix_legacy_ids',
                nonce: almAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $log.append('<div class="alm-log-message" style="color: green;">Studio legacy IDs fix completed!</div>');
                    
                    // Update log with all messages
                    response.log.forEach(function(message) {
                        $log.append('<div class="alm-log-message">' + message + '</div>');
                    });
                    
                    $log.scrollTop($log[0].scrollHeight);
                } else {
                    $log.append('<div class="alm-log-message" style="color: red;">Studio legacy IDs fix failed!</div>');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', status, error);
                console.log('Response:', xhr.responseText);
                $log.append('<div class="alm-log-message" style="color: red;">AJAX Error: ' + error + '</div>');
            },
            complete: function() {
                console.log('AJAX call completed');
                $button.prop('disabled', false).text('Fix Studio Legacy IDs');
            }
        });
    });
    
    // Copy Debug Results button
    $('#copy-debug').on('click', function() {
        var $debugResults = $('#debug-results');
        var $temp = $('<textarea>');
        
        $('body').append($temp);
        $temp.val($debugResults.text()).select();
        
        try {
            document.execCommand('copy');
            $(this).text('Copied!');
            setTimeout(function() {
                $('#copy-debug').text('Copy Debug Results');
            }, 2000);
        } catch (err) {
            alert('Failed to copy debug results');
        }
        
        $temp.remove();
    });
});
