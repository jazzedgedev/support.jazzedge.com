jQuery(document).ready(function($) {
    'use strict';
    
    // Handle favorites button clicks
    $(document).on('click', '.jf-favorites-btn', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $container = $button.closest('.jf-favorites-container');
        const $message = $container.find('.jf-favorites-message');
        
        // Disable button to prevent double-clicks
        $button.prop('disabled', true);
        
        // Get data from button attributes
        const favoriteData = {
            title: $button.data('title') || '',
            url: $button.data('url') || '',
            category: $button.data('category') || '',
            description: $button.data('description') || ''
        };
        
        // Validate required fields
        if (!favoriteData.title.trim()) {
            showMessage($message, 'Please provide a title for the favorite.', 'error');
            $button.prop('disabled', false);
            return;
        }
        
        // Show loading state
        $button.html('⏳ Adding...');
        
        // Make API request
        $.ajax({
            url: jazzedgeFavorites.restUrl + 'favorites',
            method: 'POST',
            headers: {
                'X-WP-Nonce': jazzedgeFavorites.restNonce
            },
            contentType: 'application/json',
            data: JSON.stringify(favoriteData),
            success: function(response) {
                if (response.success) {
                    showMessage($message, 'Favorite added successfully!', 'success');
                    $button.html('✅ Added to Favorites');
                    $button.addClass('jf-favorites-added');
                    
                    // Trigger custom event for other plugins to listen to
                    $(document).trigger('jazzedge_favorite_added', [response.favorite_id, favoriteData]);
                } else {
                    showMessage($message, 'Failed to add favorite. Please try again.', 'error');
                    $button.html('⭐ Add to Favorites');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to add favorite. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showMessage($message, errorMessage, 'error');
                $button.html('⭐ Add to Favorites');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
    
    // Function to show messages
    function showMessage($messageElement, text, type) {
        $messageElement
            .removeClass('jf-success jf-error jf-info')
            .addClass('jf-' + type)
            .text(text)
            .fadeIn();
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $messageElement.fadeOut();
        }, 5000);
    }
    
    // Handle manual favorite addition (for forms)
    window.addJazzedgeFavorite = function(title, url, category, description) {
        return new Promise(function(resolve, reject) {
            const favoriteData = {
                title: title || '',
                url: url || '',
                category: category || '',
                description: description || ''
            };
            
            $.ajax({
                url: jazzedgeFavorites.restUrl + 'favorites',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': jazzedgeFavorites.restNonce
                },
                contentType: 'application/json',
                data: JSON.stringify(favoriteData),
                success: function(response) {
                    if (response.success) {
                        resolve(response);
                    } else {
                        reject(new Error('Failed to add favorite'));
                    }
                },
                error: function(xhr) {
                    reject(new Error(xhr.responseJSON?.message || 'Failed to add favorite'));
                }
            });
        });
    };
    
    // Handle getting favorites for other plugins
    window.getJazzedgeFavorites = function() {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: jazzedgeFavorites.restUrl + 'favorites',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': jazzedgeFavorites.restNonce
                },
                success: function(response) {
                    if (response.success) {
                        resolve(response.favorites);
                    } else {
                        reject(new Error('Failed to get favorites'));
                    }
                },
                error: function(xhr) {
                    reject(new Error('Failed to get favorites'));
                }
            });
        });
    };
});
