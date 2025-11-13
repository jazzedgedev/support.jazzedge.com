/**
 * Academy Lesson Manager Admin JavaScript
 * 
 * Custom JavaScript for the Academy Lesson Manager admin interface
 */

jQuery(document).ready(function($) {
    
    // Search form enhancements
    $('.alm-search-form input[type="search"]').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            $(this).closest('form').submit();
        }
    });
    
    // Table row highlighting
    $('.wp-list-table tbody tr').hover(
        function() {
            $(this).addClass('hover');
        },
        function() {
            $(this).removeClass('hover');
        }
    );
    
    // Bulk actions (placeholder for future functionality)
    $('.wp-list-table thead input[type="checkbox"]').on('change', function() {
        var checked = $(this).is(':checked');
        $('.wp-list-table tbody input[type="checkbox"]').prop('checked', checked);
    });
    
    $('.wp-list-table tbody input[type="checkbox"]').on('change', function() {
        var totalCheckboxes = $('.wp-list-table tbody input[type="checkbox"]').length;
        var checkedCheckboxes = $('.wp-list-table tbody input[type="checkbox"]:checked').length;
        
        if (checkedCheckboxes === totalCheckboxes) {
            $('.wp-list-table thead input[type="checkbox"]').prop('checked', true);
        } else {
            $('.wp-list-table thead input[type="checkbox"]').prop('checked', false);
        }
    });
    
    // Confirmation dialogs for destructive actions
    $('a[href*="delete"], button[data-action="delete"]').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });
    
    // External link indicators
    $('a[target="_blank"]').each(function() {
        var $this = $(this);
        var href = $this.attr('href');
        
        // Add appropriate icons based on URL
        if (href.indexOf('vimeo.com') !== -1) {
            $this.addClass('vimeo-link');
        } else if (href.indexOf('youtube.com') !== -1 || href.indexOf('youtu.be') !== -1) {
            $this.addClass('youtube-link');
        } else if (href.indexOf('jazzedge.com') !== -1) {
            $this.addClass('jazzedge-link');
        }
    });
    
    // Auto-refresh for long-running operations (placeholder)
    function refreshPage() {
        if (window.location.search.indexOf('refresh=1') !== -1) {
            setTimeout(function() {
                window.location.reload();
            }, 5000);
        }
    }
    
    // Initialize refresh if needed
    refreshPage();
    
    // Tooltip functionality for truncated text
    $('.wp-list-table td').each(function() {
        var $this = $(this);
        var text = $this.text().trim();
        var width = $this.width();
        
        if (text.length > 30 && width < 200) {
            $this.attr('title', text);
        }
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + F for search focus
        if ((e.ctrlKey || e.metaKey) && e.which === 70) {
            e.preventDefault();
            $('.alm-search-form input[type="search"]').focus();
        }
        
        // Escape to clear search
        if (e.which === 27) { // Escape key
            $('.alm-search-form input[type="search"]').val('').closest('form').submit();
        }
    });
    
    // Loading states for forms
    $('form').on('submit', function() {
        var $form = $(this);
        var $submitBtn = $form.find('input[type="submit"], button[type="submit"]');
        
        if ($submitBtn.length) {
            $submitBtn.prop('disabled', true).addClass('loading');
            $form.addClass('alm-loading');
        }
    });
    
    // Remove loading state on page load
    $('.loading').removeClass('loading');
    $('.alm-loading').removeClass('alm-loading');
    
    // Responsive table handling
    function handleResponsiveTables() {
        if ($(window).width() < 782) {
            $('.wp-list-table').addClass('mobile-view');
        } else {
            $('.wp-list-table').removeClass('mobile-view');
        }
    }
    
    // Handle window resize
    $(window).on('resize', handleResponsiveTables);
    handleResponsiveTables(); // Initial call
    
    // Status indicators
    function updateStatusIndicators() {
        // Highlight rows with missing video sources
        $('.wp-list-table tbody tr').each(function() {
            var $row = $(this);
            var vimeoId = $row.find('.column-vimeo').text().trim();
            var youtubeId = $row.find('.column-youtube').text().trim();
            
            if (vimeoId === '—' && youtubeId === '—') {
                $row.addClass('no-video-source');
            }
        });
        
        // Highlight completed items
        $('.wp-list-table tbody tr').each(function() {
            var $row = $(this);
            var jamiDone = $row.find('.column-jami-done, td:contains("Yes")').length;
            
            if (jamiDone > 0) {
                $row.addClass('completed');
            }
        });
    }
    
    updateStatusIndicators();
    
    // Search suggestions (placeholder for future enhancement)
    function initSearchSuggestions() {
        // This would be enhanced with AJAX search suggestions
        // For now, just add basic functionality
        $('.alm-search-form input[type="search"]').on('input', function() {
            var query = $(this).val();
            if (query.length > 2) {
                // Future: AJAX search suggestions
            }
        });
    }
    
    initSearchSuggestions();
    
    // Print functionality
    $('.print-page').on('click', function(e) {
        e.preventDefault();
        window.print();
    });
    
    // Export functionality (placeholder)
    $('.export-data').on('click', function(e) {
        e.preventDefault();
        alert('Export functionality will be added in a future version.');
    });
    
    // Quick actions menu
    function initQuickActions() {
        $('.quick-actions').on('click', function(e) {
            e.preventDefault();
            var $menu = $(this).siblings('.quick-actions-menu');
            $menu.toggle();
        });
        
        // Close menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.quick-actions').length) {
                $('.quick-actions-menu').hide();
            }
        });
    }
    
    initQuickActions();
    
    // Accessibility improvements
    function improveAccessibility() {
        // Add ARIA labels to interactive elements
        $('.wp-list-table th a').attr('aria-label', function() {
            return 'Sort by ' + $(this).text();
        });
        
        // Add skip links
        if ($('.wp-list-table').length) {
            $('.wp-list-table').before('<a href="#main-content" class="screen-reader-text skip-link">Skip to main content</a>');
        }
    }
    
    improveAccessibility();
});
