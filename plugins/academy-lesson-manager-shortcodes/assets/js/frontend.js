jQuery(document).ready(function($) {
    'use strict';
    
    // Handle completion button clicks
    $(document).on('click', '.alm-completion-btn', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const action = $button.data('action');
        const lessonId = $button.data('lesson-id');
        const chapterId = $button.data('chapter-id');
        const collectionId = $button.data('collection-id');
        
        // IMMEDIATELY update UI optimistically (before server responds)
        if (action === 'mark_chapter_complete' || action === 'mark_lesson_complete') {
            // Change to restart button immediately
            $button.css('background', '#6c757d');
            $button.html('<svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg> ' + (action === 'mark_chapter_complete' ? 'Restart Chapter' : 'Restart Lesson'));
            $button.data('action', action === 'mark_chapter_complete' ? 'mark_chapter_incomplete' : 'mark_lesson_incomplete');
            
            // Update chapter completion status immediately
            if (action === 'mark_chapter_complete' && chapterId) {
                updateChapterCompletion(chapterId, true);
            }
            
            // Update progress bar immediately
            updateProgressBar();
        } else {
            // Change to mark complete button immediately
            $button.css('background', '#239B90');
            $button.html('<svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> ' + (action === 'mark_chapter_incomplete' ? 'Mark Complete' : 'Mark Lesson Complete'));
            $button.data('action', action === 'mark_chapter_incomplete' ? 'mark_chapter_complete' : 'mark_lesson_complete');
            
            // Update chapter completion status immediately
            if (action === 'mark_chapter_incomplete' && chapterId) {
                updateChapterCompletion(chapterId, false);
            }
            
            // Update progress bar immediately
            updateProgressBar();
        }
        
        // Disable button during request
        $button.prop('disabled', true);
        
        // Prepare data
        const data = {
            action: 'alm_' + action,
            nonce: almAjax.nonce,
            lesson_id: lessonId,
            collection_id: collectionId
        };
        
        // Add chapter_id if available
        if (chapterId) {
            data.chapter_id = chapterId;
        }
        
        // Make AJAX request
        $.ajax({
            url: almAjax.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    // Already updated UI optimistically, no need for success message
                    // Just re-enable button
                    $button.prop('disabled', false);
                } else {
                    // Rollback UI changes on error
                    if (action === 'mark_chapter_complete' || action === 'mark_lesson_complete') {
                        $button.css('background', '#239B90');
                        $button.html('<svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> ' + (action === 'mark_chapter_complete' ? 'Mark Complete' : 'Mark Lesson Complete'));
                        $button.data('action', action);
                        
                        if (action === 'mark_chapter_complete' && chapterId) {
                            updateChapterCompletion(chapterId, false);
                        }
                        updateProgressBar();
                    } else {
                        $button.css('background', '#6c757d');
                        $button.html('<svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg> ' + (action === 'mark_chapter_incomplete' ? 'Restart Chapter' : 'Restart Lesson'));
                        $button.data('action', action);
                        
                        if (action === 'mark_chapter_incomplete' && chapterId) {
                            updateChapterCompletion(chapterId, true);
                        }
                        updateProgressBar();
                    }
                    
                    // Show error message
                    $button.after('<div class="alm-error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-top: 10px;">Error: ' + response.data.message + '</div>');
                    
                    setTimeout(function() {
                        $('.alm-error-message').fadeOut(function() {
                            $(this).remove();
                        });
                    }, 5000);
                    
                    // Re-enable button
                    $button.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                $button.after('<div class="alm-error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-top: 10px;">Error: Network error occurred</div>');
                
                setTimeout(function() {
                    $('.alm-error-message').fadeOut(function() {
                        $(this).remove();
                    });
                }, 5000);
                
                // Re-enable button
                $button.prop('disabled', false);
            }
        });
    });
});

// Helper function to update chapter completion status
function updateChapterCompletion(chapterId, isComplete) {
    const $ = jQuery;
    
    // Find the chapter item by data attribute
    const $chapterItem = $('.alm-chapter-item[data-chapter-id="' + chapterId + '"]');
    
    if ($chapterItem.length) {
        if (isComplete) {
            // Add completed class
            $chapterItem.addClass('completed');
            
            // Remove "Current" badge if it exists
            $chapterItem.find('.alm-current-badge').remove();
            
            // Add "Completed" badge if it doesn't exist
            let $completedBadge = $chapterItem.find('.alm-completed-badge');
            if (!$completedBadge.length) {
                // Insert badge in the chapter meta section
                $chapterItem.find('.alm-chapter-meta').append('<span class="alm-completed-badge">Completed</span>');
            }
            
            // Update chapter number circle to show checkmark
            $chapterItem.find('.alm-chapter-number').html('<svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>');
        } else {
            // Remove completed class and badge
            $chapterItem.removeClass('completed');
            $chapterItem.find('.alm-completed-badge').remove();
            
            // Restore chapter number
            const chapterNumber = $chapterItem.find('.alm-chapter-number').data('chapter-number');
            if (chapterNumber) {
                $chapterItem.find('.alm-chapter-number').html('<span class="alm-status-icon pending">' + chapterNumber + '</span>');
            }
        }
    }
}

// Helper function to update progress bar
function updateProgressBar() {
    const $ = jQuery;
    
    // Count completed chapters
    const completedCount = $('.alm-chapter-item.completed').length;
    const totalCount = $('.alm-chapter-item').length;
    
    // Calculate percentage
    const percentage = totalCount > 0 ? (completedCount / totalCount) * 100 : 0;
    
    // Update progress bar fill
    $('.alm-progress-fill').css('width', percentage + '%');
    
    // Update progress text
    $('.alm-progress-text').text('Progress: ' + completedCount + ' of ' + totalCount + ' chapters completed');
    
    // Update progress badge in header
    updateProgressBadge(percentage);
}

// Helper function to update progress badge in header
function updateProgressBadge(percentage) {
    const $ = jQuery;
    
    // Update the progress badge text
    const $badge = $('.alm-progress-badge');
    if ($badge.length) {
        $badge.text(Math.round(percentage) + '% Complete');
        
        // Add smooth animation
        $badge.css({
            'transition': 'all 0.3s ease',
            'transform': 'scale(1.05)'
        });
        
        setTimeout(function() {
            $badge.css('transform', 'scale(1)');
        }, 300);
    }
}