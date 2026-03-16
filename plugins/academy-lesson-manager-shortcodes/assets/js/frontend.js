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

    initLessonTimestamps();

    function initLessonTimestamps() {
        var $toolbar = $('.alm-timestamp-toolbar');
        var $listCard = $('.alm-timestamps-card');
        var $modal = $('#alm-timestamp-modal');
        
        if (!$modal.length || (!$toolbar.length && !$listCard.length)) {
            if (window.console && console.warn) {
                console.warn('ALM timestamps: modal or container missing', {
                    hasModal: $modal.length > 0,
                    hasToolbar: $toolbar.length > 0,
                    hasListCard: $listCard.length > 0
                });
            }
            return;
        }
        
        var lessonId = $toolbar.data('lesson-id') || $listCard.data('lesson-id');
        var videoId = $toolbar.data('video-id') || $listCard.data('video-id');
        var $videoContainer = $('.alm-video-section');
        
        function getPlayerAPI() {
            if (typeof window.fv_player === 'function') {
                try {
                    var fvInstance = window.fv_player(0);
                    if (fvInstance) {
                        return {
                            getCurrentTime: function() {
                                if (typeof fvInstance.currentTime === 'number') {
                                    return fvInstance.currentTime;
                                }
                                if (typeof fvInstance.currentTime === 'function') {
                                    return fvInstance.currentTime();
                                }
                                if (typeof fvInstance.getTime === 'function') {
                                    return fvInstance.getTime();
                                }
                                return 0;
                            },
                            setCurrentTime: function(seconds) {
                                if (typeof fvInstance.currentTime === 'number') {
                                    fvInstance.currentTime = seconds;
                                } else if (typeof fvInstance.setCurrentTime === 'function') {
                                    fvInstance.setCurrentTime(seconds);
                                } else if (typeof fvInstance.seek === 'function') {
                                    fvInstance.seek(seconds);
                                }
                            },
                            play: function() {
                                if (typeof fvInstance.play === 'function') {
                                    fvInstance.play();
                                }
                            },
                            pause: function() {
                                if (typeof fvInstance.pause === 'function') {
                                    fvInstance.pause();
                                }
                            }
                        };
                    }
                } catch (e) {
                    // fall through to HTML5 video
                }
            }
            
            var videoEl = document.querySelector('.alm-video-section video');
            if (!videoEl) {
                return null;
            }
            return {
                getCurrentTime: function() { return videoEl.currentTime || 0; },
                setCurrentTime: function(seconds) { videoEl.currentTime = seconds; },
                play: function() { videoEl.play(); },
                pause: function() { videoEl.pause(); }
            };
        }
        
        function formatTime(seconds) {
            seconds = Math.max(0, Math.floor(seconds || 0));
            var hours = Math.floor(seconds / 3600);
            var minutes = Math.floor((seconds % 3600) / 60);
            var secs = seconds % 60;
            if (hours > 0) {
                return hours + ':' + String(minutes).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            }
            return minutes + ':' + String(secs).padStart(2, '0');
        }
        
        function openModal(seconds) {
            $('#alm-timestamp-current-time').text(formatTime(seconds));
            $('#alm-timestamp-seconds').val(Math.floor(seconds));
            $('#alm-timestamp-description').val('').focus();
            $modal.fadeIn(200);
        }
        
        function closeModal() {
            $modal.fadeOut(200);
        }
        
        function handleAddTimestampClick() {
            lessonId = $toolbar.data('lesson-id') || $listCard.data('lesson-id');
            videoId = $toolbar.data('video-id') || $listCard.data('video-id');
            if (window.console && console.log) {
                console.log('ALM timestamps: add clicked', { lessonId: lessonId, videoId: videoId });
            }
            var player = getPlayerAPI();
            if (!player) {
                if (window.console && console.warn) {
                    console.warn('ALM timestamps: player not ready');
                }
                alert('Start playing the video, pause the video then click add timestamp');
                return;
            }
            player.pause();
            var seconds = player.getCurrentTime() || 0;
            openModal(seconds);
        }

        $toolbar.on('click', '.alm-add-timestamp-btn', handleAddTimestampClick);
        $listCard.on('click', '.alm-add-timestamp-btn', handleAddTimestampClick);
        
        $modal.on('click', '.alm-timestamp-modal-close, .alm-timestamp-cancel-btn, .alm-timestamp-modal-overlay', function(e) {
            e.preventDefault();
            closeModal();
        });
        
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                closeModal();
            }
            if (e.key === 'Enter' && $modal.is(':visible')) {
                e.preventDefault();
                $modal.find('.alm-timestamp-save-btn').trigger('click');
            }
        });
        
        $modal.on('click', '.alm-timestamp-save-btn', function() {
            var seconds = parseInt($('#alm-timestamp-seconds').val(), 10) || 0;
            var description = $('#alm-timestamp-description').val().trim();
            
            $.ajax({
                url: almAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'alm_add_video_timestamp',
                    nonce: almAjax.timestamps_nonce,
                    lesson_id: lessonId,
                    video_id: videoId,
                    seconds: seconds,
                    description: description
                },
                success: function(response) {
                    if (response.success && response.data) {
                        appendTimestampRow(response.data);
                        closeModal();
                    } else {
                        alert(response.data || 'Failed to save timestamp.');
                    }
                },
                error: function() {
                    alert('Failed to save timestamp.');
                }
            });
        });
        
        $listCard.on('click', '.alm-timestamp-view-btn', function() {
            var $row = $(this).closest('.alm-timestamp-row');
            var seconds = parseInt($row.data('seconds'), 10) || 0;
            var player = getPlayerAPI();
            if (!player) {
                return;
            }
            player.setCurrentTime(seconds);
            player.play();
            if ($videoContainer.length) {
                $videoContainer.get(0).scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
        
        $listCard.on('click', '.alm-timestamp-delete-btn', function() {
            var $row = $(this).closest('.alm-timestamp-row');
            var timestampId = $row.data('timestamp-id');
            if (!timestampId) {
                return;
            }
            
            $.ajax({
                url: almAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'alm_delete_video_timestamp',
                    nonce: almAjax.timestamps_nonce,
                    lesson_id: lessonId,
                    timestamp_id: timestampId
                },
                success: function(response) {
                    if (response.success) {
                        $row.remove();
                        toggleEmptyState();
                    } else {
                        alert(response.data || 'Failed to delete timestamp.');
                    }
                },
                error: function() {
                    alert('Failed to delete timestamp.');
                }
            });
        });
        
        function appendTimestampRow(data) {
            var $list = $listCard.find('.alm-timestamp-list');
            if (!$list.length) {
                $list = $('<div class="alm-timestamp-list"></div>');
                $listCard.find('.alm-card-content').append($list);
            }
            
            var description = data.description ? data.description : '';
            var $row = $('<div class="alm-timestamp-row" data-timestamp-id="' + data.id + '" data-seconds="' + data.seconds + '"></div>');
            $row.append('<div class="alm-timestamp-time">' + data.time + '</div>');
            $row.append('<div class="alm-timestamp-description">' + description + '</div>');
            $row.append('<div class="alm-timestamp-actions"><button type="button" class="alm-timestamp-view-btn">View this section</button><button type="button" class="alm-timestamp-delete-btn" aria-label="Delete timestamp">Delete</button></div>');
            
            $list.append($row);
            sortTimestampList($list);
            toggleEmptyState();
        }
        
        function sortTimestampList($list) {
            var $rows = $list.children('.alm-timestamp-row').get();
            $rows.sort(function(a, b) {
                var aSec = parseInt($(a).data('seconds'), 10) || 0;
                var bSec = parseInt($(b).data('seconds'), 10) || 0;
                return aSec - bSec;
            });
            $.each($rows, function(idx, row) {
                $list.append(row);
            });
        }
        
        function toggleEmptyState() {
            var $empty = $listCard.find('.alm-timestamp-empty');
            var hasRows = $listCard.find('.alm-timestamp-row').length > 0;
            if (hasRows) {
                $empty.hide();
            } else {
                if (!$empty.length) {
                    $listCard.find('.alm-card-content').prepend('<div class="alm-timestamp-empty">No timestamps yet. Pause the video and click Add Timestamp to bookmark a moment.</div>');
                } else {
                    $empty.show();
                }
            }
        }
    }
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