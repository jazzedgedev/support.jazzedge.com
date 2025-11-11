jQuery(document).ready(function($) {
    let migrationInProgress = false;
    
    // Start Migration
    $('#start-migration').on('click', function() {
        if (migrationInProgress) {
            return;
        }
        
        const dryRun = $('#dry-run-checkbox').is(':checked');
        const batchSize = parseInt($('#batch-size').val()) || 100;
        
        if (!confirm('Are you sure you want to start the migration? This will create new posts and taxonomy terms.')) {
            return;
        }
        
        migrationInProgress = true;
        $(this).prop('disabled', true);
        $('#migration-progress').show();
        
        // Start migration
        $.ajax({
            url: jeMigration.ajaxUrl,
            type: 'POST',
            data: {
                action: 'je_start_migration',
                nonce: jeMigration.nonce,
                batch_size: batchSize,
                dry_run: dryRun
            },
            success: function(response) {
                if (response.success) {
                    updateProgress(100, 'Migration completed successfully!');
                    $('#rollback-migration').show();
                    refreshStats();
                } else {
                    updateProgress(0, 'Migration failed: ' + response.data.message);
                }
            },
            error: function() {
                updateProgress(0, 'Migration failed: Network error');
            },
            complete: function() {
                migrationInProgress = false;
                $('#start-migration').prop('disabled', false);
            }
        });
        
        // Simulate progress updates
        simulateProgress();
    });
    
    // Rollback Migration
    $('#rollback-migration').on('click', function() {
        if (!confirm('Are you sure you want to rollback the migration? This will delete all migrated posts and terms.')) {
            return;
        }
        
        $(this).prop('disabled', true);
        
        $.ajax({
            url: jeMigration.ajaxUrl,
            type: 'POST',
            data: {
                action: 'je_rollback_migration',
                nonce: jeMigration.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Rollback completed successfully!');
                    $('#rollback-migration').hide();
                    refreshStats();
                } else {
                    alert('Rollback failed: ' + response.data.message);
                }
            },
            error: function() {
                alert('Rollback failed: Network error');
            },
            complete: function() {
                $('#rollback-migration').prop('disabled', false);
            }
        });
    });
    
    // Update progress bar
    function updateProgress(percentage, text) {
        $('.progress-fill').css('width', percentage + '%');
        $('#progress-text').text(text);
    }
    
    // Simulate progress updates
    function simulateProgress() {
        let progress = 0;
        const interval = setInterval(function() {
            progress += Math.random() * 10;
            if (progress > 90) {
                progress = 90;
            }
            
            updateProgress(progress, 'Migration in progress... (' + Math.round(progress) + '%)');
            
            if (progress >= 90) {
                clearInterval(interval);
            }
        }, 1000);
    }
    
    // Refresh stats
    function refreshStats() {
        $.ajax({
            url: jeMigration.ajaxUrl,
            type: 'POST',
            data: {
                action: 'je_migration_status',
                nonce: jeMigration.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStatsTable(response.data);
                }
            }
        });
    }
    
    // Update stats table
    function updateStatsTable(stats) {
        // Update courses row
        updateStatsRow(0, stats.courses.original, stats.courses.migrated);
        // Update lessons row
        updateStatsRow(1, stats.lessons.original, stats.lessons.migrated);
        // Update chapters row
        updateStatsRow(2, stats.chapters.original, stats.chapters.migrated);
        // Update studio events row
        updateStatsRow(3, stats.studio_events.original, stats.studio_events.migrated);
    }
    
    // Update individual stats row
    function updateStatsRow(rowIndex, original, migrated) {
        const row = $('#migration-stats tbody tr').eq(rowIndex);
        row.find('td').eq(1).text(original);
        row.find('td').eq(2).text(migrated);
        
        let statusIcon, statusText;
        if (migrated == 0) {
            statusIcon = '<span class="status-icon status-none">✗</span>';
            statusText = 'Not Started';
        } else if (migrated == original) {
            statusIcon = '<span class="status-icon status-complete">✓</span>';
            statusText = 'Complete';
        } else {
            statusIcon = '<span class="status-icon status-partial">⚠</span>';
            statusText = 'Partial';
        }
        
        row.find('td').eq(3).html(statusIcon + ' ' + statusText);
    }
    
    // Auto-refresh stats every 30 seconds
    setInterval(refreshStats, 30000);
});
