/**
 * Essentials Library JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Handle "Add to Library" button clicks
        $('.alm-add-to-library').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $card = $button.closest('.alm-lesson-card');
            var lessonId = $button.data('lesson-id');
            
            if (!lessonId) {
                alert('Invalid lesson ID');
                return;
            }
            
            // Show confirmation dialog
            var confirmMessage = 'Are you sure you want to add this lesson to your library?\n\n' +
                                '⚠️ IMPORTANT: Once added, this selection cannot be changed or removed. ' +
                                'This will use one of your available selections.';
            
            if (!confirm(confirmMessage)) {
                return; // User cancelled
            }
            
            // Disable button and show loading state
            $button.prop('disabled', true);
            $card.addClass('loading');
            $button.text(almLibrary.strings.selecting);
            
            // Make AJAX request
            $.ajax({
                url: almLibrary.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'alm_add_to_library',
                    lesson_id: lessonId,
                    nonce: almLibrary.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update UI
                        $button.replaceWith('<span class="alm-lesson-badge alm-in-library">In library</span>');
                        
                        // Update available count if element exists
                        if ($('.alm-stat-value').length) {
                            // Reload page to show updated stats and library
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            // Show success message
                            showMessage(response.data.message, 'success');
                        }
                    } else {
                        // Show error
                        showMessage(response.data.message || almLibrary.strings.error, 'error');
                        $button.prop('disabled', false);
                        $button.text('Add to Library');
                    }
                },
                error: function() {
                    showMessage(almLibrary.strings.error, 'error');
                    $button.prop('disabled', false);
                    $button.text('Add to Library');
                },
                complete: function() {
                    $card.removeClass('loading');
                }
            });
        });
        
        /**
         * Show message to user
         */
        function showMessage(message, type) {
            type = type || 'success';
            
            // Remove existing messages
            $('.alm-library-message').remove();
            
            // Create message element
            var $message = $('<div class="alm-library-message ' + type + '">' + message + '</div>');
            
            // Insert at top of library page
            $('.alm-essentials-library-page').prepend($message);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $message.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
        
        // Handle Essentials Library dropdown in practice hub
        $('#jph-essentials-library-dropdown').on('change', function() {
            var url = $(this).val();
            if (url) {
                window.location.href = url;
                // Reset dropdown
                $(this).val('');
            }
        });
        
        // Handle "View Sample" button clicks - open modal
        $(document).on('click', '.alm-view-sample', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var videoUrl = $(this).data('video-url');
            var lessonTitle = $(this).data('lesson-title') || 'Sample Video';
            
            if (videoUrl) {
                openSampleModal(videoUrl, lessonTitle);
            }
        });
    });
    
    /**
     * Open sample video modal
     */
    function openSampleModal(videoUrl, title) {
        // Create modal if it doesn't exist
        var modal = $('#alm-sample-modal');
        if (modal.length === 0) {
            modal = $('<div id="alm-sample-modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.85); overflow: auto;"></div>');
            
            var modalContent = $('<div style="position: relative; background-color: #ffffff; margin: 5% auto; padding: 0; width: 90%; max-width: 900px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);"></div>');
            
            var modalHeader = $('<div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #e9ecef;"></div>');
            
            var modalTitle = $('<h2 id="alm-sample-modal-title" style="margin: 0; font-size: 20px; font-weight: 600; color: #004555;"></h2>');
            
            var closeBtn = $('<button type="button" style="background: none; border: none; font-size: 32px; font-weight: 300; color: #6c757d; cursor: pointer; padding: 0; width: 32px; height: 32px; line-height: 1;">&times;</button>');
            closeBtn.on('click', closeSampleModal);
            
            modalHeader.append(modalTitle).append(closeBtn);
            
            var modalBody = $('<div id="alm-sample-modal-body" style="padding: 24px;"></div>');
            
            modalContent.append(modalHeader).append(modalBody);
            modal.append(modalContent);
            
            // Close on background click
            modal.on('click', function(e) {
                if (e.target === modal[0]) {
                    closeSampleModal();
                }
            });
            
            // Close on Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && modal.is(':visible')) {
                    closeSampleModal();
                }
            });
            
            $('body').append(modal);
        }
        
        // Convert video URL to embed format if needed
        var embedUrl = convertToEmbedUrl(videoUrl);
        
        // Check if this is an m3u8 file (HLS playlist)
        var isM3U8 = videoUrl.toLowerCase().indexOf('.m3u8') !== -1 || embedUrl.toLowerCase().indexOf('.m3u8') !== -1;
        
        // Update modal content
        $('#alm-sample-modal-title').text(title);
        var modalBody = $('#alm-sample-modal-body');
        
        if (isM3U8) {
            // Use HTML5 video element for m3u8 files (HLS)
            var videoHtml = '<div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; background: #000;">' +
                '<video id="alm-sample-video-' + Date.now() + '" controls style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" preload="metadata">' +
                '<source src="' + escapeHtml(videoUrl) + '" type="application/x-mpegURL">' +
                '<p>Your browser does not support the video tag.</p>' +
                '</video>' +
                '</div>';
            modalBody.html(videoHtml);
            
            // Initialize HLS.js for browsers that don't support HLS natively
            var video = modalBody.find('video')[0];
            if (video) {
                if (typeof Hls !== 'undefined' && Hls.isSupported()) {
                    // Use HLS.js for browsers that don't support HLS natively (Firefox, Chrome)
                    var hls = new Hls({
                        enableWorker: true,
                        lowLatencyMode: false
                    });
                    hls.loadSource(videoUrl);
                    hls.attachMedia(video);
                    // Store HLS instance on video element for cleanup
                    video.hls = hls;
                    hls.on(Hls.Events.MANIFEST_PARSED, function() {
                        video.play().catch(function(error) {
                            console.log('Autoplay prevented:', error);
                        });
                    });
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    // Native HLS support (Safari)
                    video.src = videoUrl;
                }
            }
        } else {
            // Use iframe for Vimeo, YouTube, and other embeddable URLs
            modalBody.html('<div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; background: #000;">' +
                '<iframe src="' + escapeHtml(embedUrl) + '" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;" allowfullscreen></iframe>' +
                '</div>');
        }
        
        // Show modal
        modal.fadeIn(200);
        $('body').css('overflow', 'hidden');
    }
    
    /**
     * Close sample video modal
     */
    function closeSampleModal() {
        var modal = $('#alm-sample-modal');
        if (modal.length) {
            // Stop any HLS playback
            var video = modal.find('video')[0];
            if (video) {
                if (video.hls && typeof video.hls.destroy === 'function') {
                    video.hls.destroy();
                }
                video.pause();
                video.src = '';
            }
            
            modal.fadeOut(200, function() {
                $('body').css('overflow', '');
                // Clear iframe/video to stop playback
                $('#alm-sample-modal-body').html('');
            });
        }
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    /**
     * Convert video URL to embed format
     */
    function convertToEmbedUrl(url) {
        // Vimeo: https://vimeo.com/123456 -> https://player.vimeo.com/video/123456
        var vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
        if (vimeoMatch) {
            return 'https://player.vimeo.com/video/' + vimeoMatch[1];
        }
        
        // YouTube: https://www.youtube.com/watch?v=abc123 -> https://www.youtube.com/embed/abc123
        var youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
        if (youtubeMatch) {
            return 'https://www.youtube.com/embed/' + youtubeMatch[1];
        }
        
        // Bunny.net or other direct URLs - use as-is
        return url;
    }
    
})(jQuery);

