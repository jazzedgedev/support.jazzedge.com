/**
 * Academy Lesson Manager - Frontend JavaScript
 */

jQuery(document).ready(function($) {
    
    // Chapter accordion functionality
    $('.alm-chapter-header').on('click', function() {
        var $chapter = $(this).closest('.alm-chapter');
        var $content = $chapter.find('.alm-chapter-content');
        
        // Close other chapters
        $('.alm-chapter').not($chapter).removeClass('active');
        $('.alm-chapter-content').not($content).slideUp();
        
        // Toggle current chapter
        $chapter.toggleClass('active');
        $content.slideToggle();
        
        // Track progress when chapter is opened
        if ($chapter.hasClass('active')) {
            trackChapterProgress($chapter);
        }
    });
    
    // Video progress tracking
    $('.alm-video-container iframe').on('load', function() {
        var $iframe = $(this);
        var $chapter = $iframe.closest('.alm-chapter');
        
        // Set up progress tracking for video
        setupVideoProgressTracking($iframe, $chapter);
    });
    
    // Track chapter progress
    function trackChapterProgress($chapter) {
        var lessonId = $('body').data('lesson-id');
        if (!lessonId) return;
        
        var chapterIndex = $('.alm-chapter').index($chapter);
        var totalChapters = $('.alm-chapter').length;
        var progress = Math.round((chapterIndex + 1) / totalChapters * 100);
        
        // Update progress bar
        $('.alm-progress-fill').css('width', progress + '%');
        
        // Send progress to server
        $.ajax({
            url: almAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_track_progress',
                nonce: almAjax.nonce,
                lesson_id: lessonId,
                progress: progress,
                completed: progress >= 100
            },
            success: function(response) {
                console.log('Progress tracked:', progress + '%');
            }
        });
    }
    
    // Setup video progress tracking
    function setupVideoProgressTracking($iframe, $chapter) {
        // This would integrate with video player APIs
        // For now, just track when video is loaded
        var lessonId = $('body').data('lesson-id');
        if (!lessonId) return;
        
        // Track that video was accessed
        $.ajax({
            url: almAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'alm_track_progress',
                nonce: almAjax.nonce,
                lesson_id: lessonId,
                progress: 10, // Minimal progress for video access
                completed: false
            }
        });
    }
    
    // Resource download tracking
    $('.alm-resource').on('click', function() {
        var $resource = $(this);
        var resourceName = $resource.text();
        
        // Track resource download
        console.log('Resource downloaded:', resourceName);
        
        // You could add analytics tracking here
    });
});
