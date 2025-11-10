/**
 * Jazzedge Docs Frontend JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Search functionality
        var searchTimeout;
        var $searchInput = $('#jazzedge-docs-search-input');
        var $searchResults = $('#jazzedge-docs-search-results');
        
        if ($searchInput.length) {
            $searchInput.on('input', function() {
                var query = $(this).val().trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    $searchResults.hide().empty();
                    return;
                }
                
                searchTimeout = setTimeout(function() {
                    performSearch(query);
                }, 300);
            });
            
            // Hide results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.jazzedge-docs-search-wrapper').length) {
                    $searchResults.hide();
                }
            });
        }
        
        function performSearch(query) {
            $.ajax({
                url: jazzedgeDocs.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'jazzedge_docs_search',
                    query: query,
                    nonce: jazzedgeDocs.nonce
                },
                success: function(response) {
                    if (response.success && response.data.results.length > 0) {
                        displaySearchResults(response.data.results);
                        $searchResults.show();
                    } else {
                        $searchResults.html('<div class="jazzedge-docs-search-result-item"><p>' + 
                            'No results found.</p></div>').show();
                    }
                },
                error: function() {
                    $searchResults.html('<div class="jazzedge-docs-search-result-item"><p>' + 
                        'Error performing search. Please try again.</p></div>').show();
                }
            });
        }
        
        function displaySearchResults(results) {
            var html = '';
            
            results.forEach(function(result) {
                html += '<div class="jazzedge-docs-search-result-item">';
                html += '<a href="' + result.url + '">';
                html += '<div class="jazzedge-docs-search-result-title">' + result.title + '</div>';
                html += '<div class="jazzedge-docs-search-result-excerpt">' + result.excerpt + '</div>';
                html += '</a>';
                html += '</div>';
            });
            
            $searchResults.html(html);
        }
        
        // Rating functionality
        var $ratingStars = $('.jazzedge-docs-rating-stars');
        var $ratingFeedback = $('.jazzedge-docs-rating-feedback');
        var currentRating = 0;
        var docId = null;
        
        if ($ratingStars.length) {
            docId = $ratingStars.data('doc-id');
            
            $ratingStars.on('click', '.jazzedge-docs-star', function() {
                var rating = parseInt($(this).data('rating'));
                currentRating = rating;
                
                // Update star display
                $ratingStars.find('.jazzedge-docs-star').each(function(index) {
                    if (index < rating) {
                        $(this).addClass('active');
                    } else {
                        $(this).removeClass('active');
                    }
                });
                
                // Show feedback form
                $ratingFeedback.slideDown();
            });
            
            // Hover effect
            $ratingStars.on('mouseenter', '.jazzedge-docs-star', function() {
                var rating = parseInt($(this).data('rating'));
                $ratingStars.find('.jazzedge-docs-star').each(function(index) {
                    if (index < rating) {
                        $(this).css('color', '#ffc107');
                    } else {
                        $(this).css('color', '#ddd');
                    }
                });
            }).on('mouseleave', '.jazzedge-docs-star', function() {
                $ratingStars.find('.jazzedge-docs-star').each(function(index) {
                    if ($(this).hasClass('active')) {
                        $(this).css('color', '#ffc107');
                    } else {
                        $(this).css('color', '#ddd');
                    }
                });
            });
            
            // Submit feedback
            $('.jazzedge-docs-submit-feedback').on('click', function() {
                var feedback = $('.jazzedge-docs-feedback-text').val();
                
                if (!currentRating) {
                    alert('Please select a rating first.');
                    return;
                }
                
                $.ajax({
                    url: jazzedgeDocs.restUrl + 'rating',
                    type: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', jazzedgeDocs.restNonce);
                    },
                    data: {
                        doc_id: docId,
                        rating: currentRating,
                        feedback: feedback
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Thank you for your feedback!');
                            $ratingFeedback.slideUp();
                            $('.jazzedge-docs-feedback-text').val('');
                            
                            // Update average rating display
                            if (response.average_rating && response.rating_count) {
                                var avgHtml = 'Average rating: ' + response.average_rating + 
                                    ' (' + response.rating_count + ' ratings)';
                                $('.jazzedge-docs-rating-average').html(avgHtml).show();
                            }
                        } else {
                            alert('Error submitting feedback. Please try again.');
                        }
                    },
                    error: function() {
                        alert('Error submitting feedback. Please try again.');
                    }
                });
            });
        }
        
        
    });
    
})(jQuery);

