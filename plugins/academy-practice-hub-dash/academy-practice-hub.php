<?php
/**
 * Plugin Name: Academy Practice Hub with Dashboard
 * Description: Complete practice tracking and gamification system with leaderboards, badges, and progress analytics for JazzEdge Academy students.
 * Version: 1.0
 * Author: JazzEdge
 * Text Domain: academy-practice-hub
 */
if (!defined('ABSPATH')) { exit; }

// Intentionally minimal by default. Enable wire‑through to test parity without moving code.

// Define a toggle constant in wp-config.php or here to enable wire-through mode.
if (!defined('APH_WIRE_THROUGH')) {
    define('APH_WIRE_THROUGH', false); // Disabled - Academy plugin is now self-contained
}

// Load required classes (we'll instantiate conditionally)
require_once __DIR__ . '/includes/database-schema.php';
require_once __DIR__ . '/includes/class-database.php';
require_once __DIR__ . '/includes/class-gamification.php';
require_once __DIR__ . '/includes/class-logger.php';
require_once __DIR__ . '/includes/class-rate-limiter.php';
require_once __DIR__ . '/includes/class-cache.php';
require_once __DIR__ . '/includes/class-validator.php';
require_once __DIR__ . '/includes/class-audit-logger.php';
require_once __DIR__ . '/includes/class-rest-api.php';
require_once __DIR__ . '/includes/class-admin-pages.php';
require_once __DIR__ . '/includes/class-frontend.php';
require_once __DIR__ . '/includes/class-jpc-handler.php';

// Initialize database schema on activation
register_activation_hook(__FILE__, 'aph_activate');

function aph_activate() {
    // Create tables
    APH_Database_Schema::create_tables();
    
    // Add leaderboard columns to existing tables
    APH_Database_Schema::add_leaderboard_columns();
    
    // Update badges schema to use image_url instead of icon
    APH_Database_Schema::update_badges_schema();
    
    // Add additional performance indexes
    APH_Database_Schema::add_additional_indexes();
    
    // Create milestone submissions table if it doesn't exist
    aph_create_milestone_submissions_table();
}

/**
 * Create milestone submissions table
 */
function aph_create_milestone_submissions_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'jph_jpc_milestone_submissions';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            curriculum_id int(11) NOT NULL,
            video_url text NOT NULL,
            submission_date datetime NOT NULL,
            grade varchar(10) DEFAULT NULL,
            graded_on date DEFAULT NULL,
            teacher_notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY curriculum_id (curriculum_id),
            KEY submission_date (submission_date),
            KEY grade (grade),
            KEY graded_on (graded_on)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        error_log("APH: Created milestone submissions table: $table_name");
    }
}

// Initialize REST API
new JPH_REST_API();

// Initialize Admin Pages
new JPH_Admin_Pages();

// Initialize Frontend (conditionally based on wire-through setting)
if (!defined('APH_FRONTEND_SEPARATED')) {
    define('APH_FRONTEND_SEPARATED', true); // Enable our frontend class
}
if (APH_FRONTEND_SEPARATED) {
    new JPH_Frontend();
}

// Register with Katahdin AI Hub if available
add_action('katahdin_ai_hub_init', function($hub) {
    $hub->register_plugin('academy-practice-hub', array(
        'name' => 'Academy Practice Hub',
        'version' => '4.0',
        'features' => array('chat', 'completions'),
        'quota_limit' => 5000 // tokens per month
    ));
});

// Also try to register on init in case the hook was already fired
add_action('init', function() {
    if (class_exists('Katahdin_AI_Hub') && function_exists('katahdin_ai_hub')) {
        $hub = katahdin_ai_hub();
        if ($hub && method_exists($hub, 'register_plugin')) {
            $hub->register_plugin('academy-practice-hub', array(
                'name' => 'Academy Practice Hub',
                'version' => '4.0',
                'features' => array('chat', 'completions'),
                'quota_limit' => 5000 // tokens per month
            ));
        }
    }
});

// Register plugin on admin init (when admin permissions are available)
add_action('admin_init', function() {
    if (class_exists('Katahdin_AI_Hub') && function_exists('katahdin_ai_hub')) {
        $hub = katahdin_ai_hub();
        if ($hub && method_exists($hub, 'register_plugin')) {
            $hub->register_plugin('academy-practice-hub', array(
                'name' => 'Academy Practice Hub',
                'version' => '4.0',
                'features' => array('chat', 'completions'),
                'quota_limit' => 5000 // tokens per month
            ));
        }
    }
});

// Add asset enqueuing hooks to match original plugin
add_action('wp_enqueue_scripts', 'aph_enqueue_frontend_assets');
add_action('admin_enqueue_scripts', 'aph_enqueue_admin_assets');

// Add JPC modal functionality
add_action('wp_footer', 'aph_add_jpc_modal_scripts');

/**
 * Enqueue frontend assets
 */
function aph_enqueue_frontend_assets() {
    // Only enqueue on pages that might have our shortcode
    if (is_singular() || is_home() || is_front_page()) {
        wp_enqueue_script('jquery');
    }
}

/**
 * Enqueue admin assets
 */
function aph_enqueue_admin_assets() {
    // Enqueue jQuery for admin pages
    wp_enqueue_script('jquery');
}

/**
 * Add JPC modal scripts and styles
 */
function aph_add_jpc_modal_scripts() {
    // Only add on pages that might have the JPC table
    if (is_singular() || is_home() || is_front_page()) {
        ?>
        <style>
        body.jpc-modal-open {
            overflow: hidden;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            console.log('JPC Modal script loaded');
            
            // JPC Video Modal functionality
            const modal = $('#jpc-video-modal');
            const modalTitle = $('#jpc-modal-title');
            const modalFocus = $('#jpc-modal-focus');
            const modalKey = $('#jpc-modal-key');
            const videoContainer = $('#jpc-video-container');
            const markCompleteBtn = $('#jpc-mark-complete');
            
            // JPC Submission Modal functionality
            const submissionModal = $('#jpc-submission-modal');
            const submissionTitle = $('#jpc-submission-title');
            const submissionFocus = $('#jpc-submission-focus');
            const submissionForm = $('#jpc-submission-form');
            const submissionSuccess = $('#jpc-submission-success');
            const youtubeUrlInput = $('#jpc-youtube-url');
            const curriculumIdInput = $('#jpc-curriculum-id');
            const submitMilestoneBtn = $('#jpc-submit-milestone');
            
            let currentStepData = null;
            let currentSubmissionData = null;
            
            console.log('Modal elements:', {
                modal: modal.length,
                modalTitle: modalTitle.length,
                videoContainer: videoContainer.length
            });
            
            // Open modal when clicking on completed key
            $(document).on('click', '.jpc-video-modal-trigger', function(e) {
                e.preventDefault();
                console.log('Modal trigger clicked');
                
                const stepId = $(this).data('step-id');
                const curriculumId = $(this).data('curriculum-id');
                const keyName = $(this).data('key-name');
                const focusTitle = $(this).data('focus-title');
                
                console.log('Modal data:', { stepId, curriculumId, keyName, focusTitle });
                
                currentStepData = {
                    stepId: stepId,
                    curriculumId: curriculumId,
                    keyName: keyName,
                    focusTitle: focusTitle
                };
                
                // Update modal content
                modalTitle.text('JPC Lesson Video');
                modalFocus.text(focusTitle);
                modalKey.text(keyName);
                
                // Show loading
                videoContainer.html('<div class="jpc-loading">Loading video...</div>');
                
                // Show modal
                modal.show();
                $('body').addClass('jpc-modal-open');
                
                console.log('Modal shown');
                
                // Load video
                loadVideo(stepId, curriculumId);
            });
            
            // Close modal
            $(document).on('click', '.jpc-modal-close', function(e) {
                e.preventDefault();
                if (modal.is(':visible')) {
                    closeModal();
                } else if (submissionModal.is(':visible')) {
                    closeSubmissionModal();
                }
            });
            
            $(document).on('click', '.jpc-modal-overlay', function(e) {
                if (e.target === this) {
                    if (modal.is(':visible')) {
                        closeModal();
                    } else if (submissionModal.is(':visible')) {
                        closeSubmissionModal();
                    }
                }
            });
            
            // Close modal on Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (modal.is(':visible')) {
                        closeModal();
                    } else if (submissionModal.is(':visible')) {
                        closeSubmissionModal();
                    }
                }
            });
            
            // Mark as complete functionality
            markCompleteBtn.on('click', function() {
                if (currentStepData) {
                    markStepComplete(currentStepData.stepId, currentStepData.curriculumId);
                }
            });
            
            // Open submission modal when clicking "Get Graded"
            $(document).on('click', '.jpc-submission-modal-trigger', function(e) {
                e.preventDefault();
                console.log('Submission modal trigger clicked');
                
                const curriculumId = $(this).data('curriculum-id');
                const focusTitle = $(this).data('focus-title');
                
                currentSubmissionData = {
                    curriculumId: curriculumId,
                    focusTitle: focusTitle
                };
                
                // Update modal content
                submissionFocus.text(focusTitle);
                curriculumIdInput.val(curriculumId);
                youtubeUrlInput.val('');
                
                // Show form, hide success message
                submissionForm.show();
                submissionSuccess.hide();
                
                // Show modal
                submissionModal.show();
                $('body').addClass('jpc-modal-open');
                
                console.log('Submission modal shown for curriculum:', curriculumId);
            });
            
            // Submit milestone functionality
            submitMilestoneBtn.on('click', function() {
                const youtubeUrl = youtubeUrlInput.val().trim();
                const curriculumId = curriculumIdInput.val();
                
                if (!youtubeUrl) {
                    alert('Please enter a YouTube URL');
                    return;
                }
                
                if (!curriculumId) {
                    alert('Error: Curriculum ID missing');
                    return;
                }
                
                // Disable button and show loading
                submitMilestoneBtn.prop('disabled', true).text('Submitting...');
                
                // Submit via AJAX
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'jpc_submit_milestone',
                        curriculum_id: curriculumId,
                        youtube_url: youtubeUrl,
                        nonce: '<?php echo wp_create_nonce('jpc_submit_milestone'); ?>'
                    },
                    success: function(response) {
                        console.log('Submission response:', response);
                        if (response.success) {
                            // Show success message
                            submissionForm.hide();
                            submissionSuccess.show();
                            
                            // Hide submit/cancel buttons and show only close button
                            $('.jpc-modal-footer').html(`
                                <button type="button" class="jpc-btn jpc-btn-primary jpc-modal-close">Close</button>
                            `);
                            
                            // Auto-close modal after 5 seconds
                            setTimeout(function() {
                                closeSubmissionModal();
                            }, 5000);
                        } else {
                            alert('Error submitting: ' + (response.data || 'Unknown error'));
                            submitMilestoneBtn.prop('disabled', false).text('Submit for Grading');
                        }
                    },
                    error: function() {
                        alert('Error submitting. Please try again.');
                        submitMilestoneBtn.prop('disabled', false).text('Submit for Grading');
                    }
                });
            });
            
            function closeModal() {
                modal.hide();
                $('body').removeClass('jpc-modal-open');
                videoContainer.html('<div class="jpc-loading">Loading video...</div>');
                markCompleteBtn.hide();
                currentStepData = null;
            }
            
            function closeSubmissionModal() {
                submissionModal.hide();
                $('body').removeClass('jpc-modal-open');
                submissionForm.show();
                submissionSuccess.hide();
                youtubeUrlInput.val('');
                submitMilestoneBtn.prop('disabled', false).text('Submit for Grading');
                
                // Restore original footer
                $('.jpc-modal-footer').html(`
                    <button type="button" class="jpc-btn jpc-btn-secondary jpc-modal-close">Cancel</button>
                    <button type="button" class="jpc-btn jpc-btn-primary" id="jpc-submit-milestone">Submit for Grading</button>
                `);
                
                currentSubmissionData = null;
            }
            
            function loadVideo(stepId, curriculumId) {
                console.log('Loading video for step:', stepId, 'curriculum:', curriculumId);
                
                // First verify the user has completed this step
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'jpc_verify_step_completion',
                        step_id: stepId,
                        curriculum_id: curriculumId,
                        nonce: '<?php echo wp_create_nonce('jpc_verify_step'); ?>'
                    },
                    success: function(response) {
                        console.log('AJAX response:', response);
                        if (response.success && response.data.completed) {
                            // Get the video URL from the step data
                            if (response.data.vimeo_id) {
                                // Load the Vimeo video directly
                                videoContainer.html(`
                                    <iframe src="https://player.vimeo.com/video/${response.data.vimeo_id}" 
                                            width="100%" 
                                            height="100%" 
                                            frameborder="0" 
                                            allow="autoplay; fullscreen; picture-in-picture" 
                                            allowfullscreen>
                                    </iframe>
                                `);
                            } else {
                                // Fallback to lesson page if no Vimeo ID
                                const videoUrl = `/jpc-lesson/?step_id=${stepId}&cid=${curriculumId}`;
                                videoContainer.html(`
                                    <iframe src="${videoUrl}" 
                                            allowfullscreen 
                                            webkitallowfullscreen 
                                            mozallowfullscreen>
                                    </iframe>
                                `);
                            }
                            
                            // Show mark complete button if this is the current step
                            if (response.data.is_current_step) {
                                markCompleteBtn.show();
                            }
                        } else {
                            // User hasn't completed this step - show error
                            videoContainer.html(`
                                <div style="text-align: center; padding: 40px; color: #dc3545;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; margin-bottom: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    <h4>Access Denied</h4>
                                    <p>You must complete this step before you can view the video.</p>
                                </div>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX error:', xhr, status, error);
                        videoContainer.html(`
                            <div style="text-align: center; padding: 40px; color: #dc3545;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; margin-bottom: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                                <h4>Error Loading Video</h4>
                                <p>There was an error verifying your progress. Please try again.</p>
                            </div>
                        `);
                    }
                });
            }
            
            function markStepComplete(stepId, curriculumId) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'jpc_mark_step_complete',
                        step_id: stepId,
                        curriculum_id: curriculumId,
                        nonce: '<?php echo wp_create_nonce('jpc_mark_complete'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update the UI
                            markCompleteBtn.hide();
                            
                            // Show success message
                            videoContainer.html(`
                                <div style="text-align: center; padding: 40px; color: #10b981;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; margin-bottom: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                    <h4>Step Completed!</h4>
                                    <p>Great job! You can now proceed to the next step.</p>
                                </div>
                            `);
                            
                            // Refresh the page after a short delay to update the table
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            alert('Error marking step as complete: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('Error marking step as complete. Please try again.');
                    }
                });
            }
        });
        </script>
        <?php
    }
}


// When enabled, bootstrap the existing JazzEdge Practice Hub code paths for parity testing.
if (APH_WIRE_THROUGH) {
    // Only run if the original plugin is not active to avoid double-loading.
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    $original_plugin_slug = 'jazzedge-practice-hub/jazzedge-practice-hub.php';
    if (!is_plugin_active($original_plugin_slug)) {
        // Load the original plugin main file to restore admin menus, assets, and shortcode rendering
        $original_main = WP_PLUGIN_DIR . '/jazzedge-practice-hub/jazzedge-practice-hub.php';
        if (file_exists($original_main)) {
            require_once $original_main;
        }
    }
}
