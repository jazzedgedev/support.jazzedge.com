<?php
/**
 * JPC Migration Admin Page
 * 
 * Provides a simple interface for migrating JPC data from old tables to new system
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_JPC_Migration_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'aph-practice-hub',
            'JPC Migration',
            'JPC Migration',
            'manage_options',
            'jpc-migration',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        // Include the migration class
        if (!class_exists('JPH_JPC_Migration')) {
            require_once plugin_dir_path(__FILE__) . 'class-jpc-migration.php';
        }
        
        // Validate prerequisites
        $validation = JPH_JPC_Migration::validate_prerequisites();
        
        ?>
        <div class="wrap">
            <h1>JPC Data Migration</h1>
            <p>Migrate JPC data from old tables to the new Academy Practice Hub system.</p>
            
            <?php if (!$validation['valid']): ?>
                <div class="notice notice-error">
                    <p><strong>Migration Prerequisites Not Met:</strong></p>
                    <ul>
                        <?php foreach ($validation['issues'] as $issue): ?>
                            <li><?php echo esc_html($issue); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="notice notice-success">
                    <p><strong>✓ Prerequisites validated successfully!</strong></p>
                    <p>Found <?php echo $validation['old_tables_found']; ?> old JPC tables to migrate from.</p>
                </div>
                
                <div class="card">
                    <h2>Migration Options</h2>
                    <form id="jpc-migration-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">Migration Mode</th>
                                <td>
                                    <label>
                                        <input type="radio" name="dry_run" value="true" checked>
                                        <strong>Dry Run</strong> - Analyze data without making changes (recommended first)
                                    </label><br>
                                    <label>
                                        <input type="radio" name="dry_run" value="false">
                                        <strong>Live Migration</strong> - Actually migrate the data
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Skip Existing Records</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="skip_existing" value="true" checked>
                                        Skip records that already exist in the new system
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Include Milestone Submissions</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="include_milestones" value="true" checked>
                                        Include milestone submissions in migration
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Batch Size</th>
                                <td>
                                    <input type="number" name="batch_size" value="100" min="1" max="1000">
                                    <p class="description">Number of records to process per batch (lower = safer, higher = faster)</p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <button type="submit" class="button button-primary" id="start-migration">
                                Start Migration
                            </button>
                        </p>
                    </form>
                </div>
                
                <div id="migration-results" style="display: none;">
                    <div class="card">
                        <h2>Migration Results</h2>
                        <div id="migration-output"></div>
                    </div>
                </div>
                
                <script>
                jQuery(document).ready(function($) {
                    $('#jpc-migration-form').on('submit', function(e) {
                        e.preventDefault();
                        
                        var $button = $('#start-migration');
                        var $results = $('#migration-results');
                        var $output = $('#migration-output');
                        
                        // Disable button and show loading
                        $button.prop('disabled', true).text('Migrating...');
                        $results.show();
                        $output.html('<p>Starting migration...</p>');
                        
                        // Get form data
                        var formData = {
                            dry_run: $('input[name="dry_run"]:checked').val(),
                            skip_existing: $('input[name="skip_existing"]').is(':checked') ? 'true' : 'false',
                            include_milestones: $('input[name="include_milestones"]').is(':checked') ? 'true' : 'false',
                            batch_size: $('input[name="batch_size"]').val()
                        };
                        
                        // Make API call
                        $.ajax({
                            url: '<?php echo rest_url('aph/v1/jpc/migrate'); ?>',
                            method: 'POST',
                            data: formData,
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                            },
                            success: function(response) {
                                if (response.success) {
                                    var html = '<div class="notice notice-success"><p><strong>Migration completed successfully!</strong></p></div>';
                                    
                                    if (response.dry_run) {
                                        html += '<p><strong>Dry Run Results:</strong></p>';
                                    } else {
                                        html += '<p><strong>Migration Statistics:</strong></p>';
                                    }
                                    
                                    html += '<ul>';
                                    html += '<li>Curriculum Items: ' + (response.stats.curriculum_items || 0) + '</li>';
                                    html += '<li>Steps: ' + (response.stats.steps || 0) + '</li>';
                                    html += '<li>User Assignments: ' + (response.stats.user_assignments || 0) + '</li>';
                                    html += '<li>User Progress Records: ' + (response.stats.user_progress || 0) + '</li>';
                                    html += '<li>Milestone Submissions: ' + (response.stats.milestone_submissions || 0) + '</li>';
                                    html += '</ul>';
                                    
                                    if (response.stats.errors && response.stats.errors.length > 0) {
                                        html += '<div class="notice notice-warning"><p><strong>Errors encountered:</strong></p><ul>';
                                        response.stats.errors.forEach(function(error) {
                                            html += '<li>' + error + '</li>';
                                        });
                                        html += '</ul></div>';
                                    }
                                    
                                    $output.html(html);
                                } else {
                                    $output.html('<div class="notice notice-error"><p><strong>Migration failed:</strong> ' + response.message + '</p></div>');
                                }
                            },
                            error: function(xhr) {
                                var error = 'Unknown error occurred';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    error = xhr.responseJSON.message;
                                }
                                $output.html('<div class="notice notice-error"><p><strong>Migration failed:</strong> ' + error + '</p></div>');
                            },
                            complete: function() {
                                $button.prop('disabled', false).text('Start Migration');
                            }
                        });
                    });
                });
                </script>
            <?php endif; ?>
            
            <hr>
            
            <h2>Fix Student Progress</h2>
            <p>If students are showing at incorrect focuses after migration, use this tool to fix their assignments based on their actual progress.</p>
            
            <div style="background: #f0f0f1; padding: 15px; margin: 15px 0; border-left: 4px solid #0073aa;">
                <h3>Quick Fix for Specific Student</h3>
                <p>If you know a specific student is at the wrong focus or key, you can fix them directly:</p>
                <p>
                    <label for="fix-user-id">User ID:</label>
                    <input type="number" id="fix-user-id" placeholder="e.g., 2000" style="width: 100px; margin: 0 10px;">
                    <label for="fix-focus-select">Focus:</label>
                    <select id="fix-focus-select" style="width: 200px; margin: 0 10px;">
                        <option value="">Select Focus...</option>
                        <?php
                        // Get all curriculum items for dropdown
                        global $wpdb;
                        $curriculum_items = $wpdb->get_results(
                            "SELECT id, focus_order, focus_title FROM {$wpdb->prefix}jph_jpc_curriculum ORDER BY focus_order ASC",
                            ARRAY_A
                        );
                        
                        foreach ($curriculum_items as $item) {
                            echo '<option value="' . esc_attr($item['focus_order']) . '">' . esc_html($item['focus_order'] . ' - ' . $item['focus_title']) . '</option>';
                        }
                        ?>
                    </select>
                    <label for="fix-key-select">Key:</label>
                    <select id="fix-key-select" style="width: 120px; margin: 0 10px;">
                        <option value="">Select Key...</option>
                        <option value="1">C</option>
                        <option value="2">F</option>
                        <option value="3">G</option>
                        <option value="4">D</option>
                        <option value="5">B♭</option>
                        <option value="6">A</option>
                        <option value="7">E♭</option>
                        <option value="8">E</option>
                        <option value="9">A♭</option>
                        <option value="10">D♭</option>
                        <option value="11">F♯</option>
                        <option value="12">B</option>
                    </select>
                    <button id="fix-specific-student" class="button button-primary">Fix This Student</button>
                </p>
                <p><small><strong>Example:</strong> Student at focus 4.30, key of C, but should be at focus 4.40 → User ID: 2000, Focus: 4.40, Key: C</small></p>
            </div>
            
            <button id="analyze-progress" class="button button-primary">Analyze All Student Progress</button>
            <button id="fix-assignments" class="button button-secondary" disabled>Fix All Assignments</button>
            
            <div id="progress-results"></div>
            
            <script>
            jQuery(document).ready(function($) {
                var analysisResults = null;
                
                // Quick fix for specific student
                $('#fix-specific-student').click(function() {
                    var userId = $('#fix-user-id').val();
                    var focusOrder = $('#fix-focus-select').val();
                    var keyNumber = $('#fix-key-select').val();
                    var keyName = $('#fix-key-select option:selected').text();
                    
                    if (!userId || !focusOrder || !keyNumber) {
                        alert('Please fill in all fields: User ID, Focus, and Key');
                        return;
                    }
                    
                    if (!confirm('Are you sure you want to move user ' + userId + ' to focus ' + focusOrder + ', key ' + keyName + '?')) {
                        return;
                    }
                    
                    $('#progress-results').html('<p>Fixing specific student...</p>');
                    
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/jpc/fix-specific-student'); ?>',
                        method: 'POST',
                        data: {
                            user_id: userId,
                            focus_order: focusOrder,
                            key_number: keyNumber
                        },
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                        },
                        success: function(response) {
                            var html = '<div class="notice notice-success"><p><strong>Student Fixed!</strong></p>';
                            html += '<p>Successfully moved user ' + userId + ' to focus ' + focusOrder + ', key ' + keyName + '.</p>';
                            html += '<p>The student can now continue their progress correctly.</p>';
                            html += '</div>';
                            $('#progress-results').html(html);
                        },
                        error: function(xhr) {
                            $('#progress-results').html('<div class="notice notice-error"><p><strong>Fix failed:</strong> ' + (xhr.responseJSON?.message || 'Unknown error') + '</p></div>');
                        }
                    });
                });
                
                $('#analyze-progress').click(function() {
                    $('#progress-results').html('<p>Analyzing student progress...</p>');
                    
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/jpc/analyze-progress'); ?>',
                        method: 'POST',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                        },
                        success: function(response) {
                            analysisResults = response;
                            var html = '<div class="notice notice-info"><p><strong>Analysis Complete!</strong></p>';
                            html += '<p>Found ' + response.total_students + ' students with JPC data.</p>';
                            html += '<ul>';
                            html += '<li>Students at correct focus: ' + response.correct_assignments + '</li>';
                            html += '<li>Students needing fixes: ' + response.incorrect_assignments + '</li>';
                            html += '</ul>';
                            
                            if (response.incorrect_assignments > 0) {
                                html += '<p><strong>Students needing fixes:</strong></p>';
                                html += '<table class="wp-list-table widefat fixed striped">';
                                html += '<thead><tr><th>User ID</th><th>Current Focus</th><th>Should Be At</th><th>Completed Focuses</th></tr></thead>';
                                html += '<tbody>';
                                response.students_to_fix.forEach(function(student) {
                                    html += '<tr>';
                                    html += '<td>' + student.user_id + '</td>';
                                    html += '<td>' + student.current_focus + '</td>';
                                    html += '<td>' + student.correct_focus + '</td>';
                                    html += '<td>' + student.completed_focuses.join(', ') + '</td>';
                                    html += '</tr>';
                                });
                                html += '</tbody></table>';
                            }
                            
                            html += '</div>';
                            $('#progress-results').html(html);
                            $('#fix-assignments').prop('disabled', false);
                        },
                        error: function(xhr) {
                            $('#progress-results').html('<div class="notice notice-error"><p><strong>Analysis failed:</strong> ' + (xhr.responseJSON?.message || 'Unknown error') + '</p></div>');
                        }
                    });
                });
                
                $('#fix-assignments').click(function() {
                    if (!analysisResults || analysisResults.incorrect_assignments === 0) {
                        alert('No assignments to fix!');
                        return;
                    }
                    
                    if (!confirm('Are you sure you want to fix ' + analysisResults.incorrect_assignments + ' student assignments? This will update their current focus.')) {
                        return;
                    }
                    
                    $('#progress-results').html('<p>Fixing student assignments...</p>');
                    
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/jpc/fix-assignments'); ?>',
                        method: 'POST',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                        },
                        success: function(response) {
                            var html = '<div class="notice notice-success"><p><strong>Assignments Fixed!</strong></p>';
                            html += '<p>Successfully updated ' + response.fixed_count + ' student assignments.</p>';
                            html += '<p>Students can now progress correctly through their curriculum.</p>';
                            html += '</div>';
                            $('#progress-results').html(html);
                            $('#fix-assignments').prop('disabled', true);
                        },
                        error: function(xhr) {
                            $('#progress-results').html('<div class="notice notice-error"><p><strong>Fix failed:</strong> ' + (xhr.responseJSON?.message || 'Unknown error') + '</p></div>');
                        }
                    });
                });
            });
            </script>
        </div>
        <?php
    }
}
