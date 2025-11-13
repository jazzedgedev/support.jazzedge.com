<?php
// Prevent direct access
if (!defined('ABSPATH')) { exit; }

class ALM_Frontend_Search {
    public function __construct() {
        add_shortcode('alm_lesson_search', array($this, 'render_shortcode'));
        add_shortcode('alm_lesson_search_compact', array($this, 'render_compact'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets() {
        // Enqueue microtip CSS for tooltips
        wp_enqueue_style('microtip', 'https://unpkg.com/microtip/microtip.css', array(), null);
        
        // Enqueue frontend search CSS
        wp_enqueue_style(
            'alm-frontend-search-css',
            plugins_url('assets/css/frontend-search.css', dirname(__FILE__)),
            array(),
            ALM_VERSION
        );
        
        // Minimal inline script; no separate file yet
        $handle = 'alm-lesson-search';
        wp_register_script($handle, false, array('wp-i18n'), ALM_VERSION, true);
        wp_enqueue_script($handle);
        
        // Add responsive CSS for search results
        $css_handle = 'alm-lesson-search-css';
        wp_register_style($css_handle, false);
        wp_enqueue_style($css_handle);
        $css = <<<'CSS'
<style id="alm-search-results-css">
/* Microtip Customization */
:root {
    --microtip-transition-duration: 0.15s;
    --microtip-transition-delay: 0.1s;
    --microtip-transition-easing: ease-out;
    --microtip-font-size: 12px;
    --microtip-font-weight: 600;
    --microtip-text-transform: none;
}

/* Ensure tooltips appear above all other elements */
[role="tooltip"]::before,
[role="tooltip"]::after {
    z-index: 9999 !important;
}

/* Search Page Container */
.alm-search-page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 40px 0 40px;
}

/* Search Results Grid */
.alm-search-results-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-top: 40px;
    padding: 0 0 60px 0;
}

/* Search Page Input Styling */
.alm-search-page-container input[type="search"]::placeholder {
    color: #9ca3af;
    font-weight: 400;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .alm-search-page-container {
        padding: 30px 20px 0 20px;
    }
    
    .alm-search-results-grid {
        grid-template-columns: repeat(2, 1fr);
        padding: 0 0 40px 0;
    }
}

@media (max-width: 768px) {
    .alm-search-page-container {
        padding: 16px 16px 0 16px;
    }
    
    .alm-search-results-grid {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 0 0 40px 0;
        margin-top: 24px;
    }
    
    .alm-search-page-container input[type="search"] {
        padding: 16px 48px 16px 18px;
        font-size: 15px;
    }
}
</style>
CSS;
        wp_add_inline_style($css_handle, $css);
        
        // Get REST endpoints and nonce for favorites - prepare for JS interpolation
        $ai_recommend_url_js = esc_js(rest_url('alm/v1/search-lessons/ai-recommend'));
        $rest_nonce_js = esc_js(wp_create_nonce('wp_rest'));
        $favorites_add_url_js = esc_js(rest_url('aph/v1/lesson-favorites'));
        $favorites_remove_url_js = esc_js(rest_url('aph/v1/lesson-favorites/remove'));
        $favorites_check_url_js = esc_js(rest_url('aph/v1/lesson-favorites/check'));
        $tags_url_js = esc_js(rest_url('alm/v1/tags'));
        $current_user_id = get_current_user_id();
        $show_debug_js = ($current_user_id > 0 && $current_user_id < 2005) ? 'true' : 'false';
        $is_user_logged_in = is_user_logged_in() ? 'true' : 'false';
        // Note: Level preference URLs removed - we no longer persist level selection
        
        $inline = <<<JS
document.addEventListener('DOMContentLoaded', function() {
  // Full page variant
  var root = document.querySelector('#alm-lesson-search');
  if (root) {
    var endpoint = root.getAttribute('data-endpoint');
    if (!endpoint) {
      console.error('ALM Search: Endpoint not found in data-endpoint attribute');
      return;
    }
    
    // Remove initial loading message if present
    var initialLoading = root.querySelector('.alm-search-loading-initial');
    if (initialLoading) {
      initialLoading.remove();
    }
    
    // Clear root content to prepare for search UI
    root.innerHTML = '';
    
    // Read URL parameters on page load
    var urlParams = new URLSearchParams(window.location.search);
    var initialQ = urlParams.get('q') || '';
    var initialPage = urlParams.get('page') ? parseInt(urlParams.get('page'), 10) : 1;
    var initialPerPage = urlParams.get('per_page') ? parseInt(urlParams.get('per_page'), 10) : 24;
    var initialLevel = urlParams.get('lesson_level') || '';
    var initialTag = urlParams.get('tag') || '';
    var initialStyle = urlParams.get('lesson_style') || '';
    var initialMembershipLevel = urlParams.get('membership_level') || '';
    var showDebug = {$show_debug_js}; // Show debug if user ID < 2005
    
    var state = { q: initialQ, page: initialPage, per_page: initialPerPage, lesson_level: initialLevel, tag: initialTag, lesson_style: initialStyle, membership_level: initialMembershipLevel };
    
    // Create styled search container
    var searchContainer = document.createElement('div');
    searchContainer.className = 'alm-search-page-container';
    searchContainer.style.maxWidth = '1200px';
    searchContainer.style.margin = '0 auto';
    searchContainer.style.padding = '20px 40px 0 40px';
    
    var form = document.createElement('form');
    form.style.position = 'relative';
    form.style.width = '100%';
    form.style.maxWidth = '100%';
    form.style.marginBottom = '40px';
    form.onsubmit = function(e){
      e.preventDefault();
      // Update state.q from input value before searching
      state.q = input.value.trim();
      state.tag = tagSelect ? tagSelect.value.trim() : '';
      state.lesson_style = styleSelect ? styleSelect.value.trim() : '';
      state.membership_level = membershipSelect ? membershipSelect.value.trim() : '';
      state.page = 1;
      fetchData();
      return false;
    };
    
    // First row: Search bar with label, input, and buttons
    var searchRow = document.createElement('div');
    searchRow.style.display = 'flex';
    searchRow.style.alignItems = 'center';
    searchRow.style.gap = '16px';
    searchRow.style.width = '100%';
    searchRow.style.marginBottom = '20px';
    
    // Add "Search" label before the input
    var searchLabel = document.createElement('label');
    searchLabel.textContent = 'Search';
    searchLabel.style.fontSize = '16px';
    searchLabel.style.fontWeight = '600';
    searchLabel.style.color = '#374151';
    searchLabel.style.flexShrink = '0';
    searchLabel.style.marginRight = '4px';
    
    var inputWrapper = document.createElement('div');
    inputWrapper.style.position = 'relative';
    inputWrapper.style.flex = '1';
    inputWrapper.style.minWidth = '0';
    
    var input = document.createElement('input');
    input.type = 'search';
    input.placeholder = 'Search lessons...';
    input.value = initialQ; // Set input value from URL parameter
    input.style.width = '100%';
    input.style.padding = '12px 56px 12px 20px';
    input.style.border = '2px solid #e5e7eb';
    input.style.borderRadius = '14px';
    input.style.background = '#ffffff';
    input.style.color = '#1f2937';
    input.style.fontSize = '16px';
    input.style.fontWeight = '400';
    input.style.lineHeight = '1.5';
    input.style.outline = 'none';
    input.style.transition = 'all 0.3s ease';
    input.style.boxSizing = 'border-box';
    input.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.04)';
    
    input.onmouseenter = function(){
      this.style.borderColor = '#3b82f6';
      this.style.boxShadow = '0 4px 12px rgba(59, 130, 246, 0.1)';
    };
    input.onmouseleave = function(){
      if (document.activeElement !== this) {
        this.style.borderColor = '#e5e7eb';
        this.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.04)';
      }
    };
    input.onfocus = function(){
      this.style.borderColor = '#3b82f6';
      this.style.boxShadow = '0 0 0 4px rgba(59, 130, 246, 0.1), 0 4px 16px rgba(59, 130, 246, 0.15)';
      this.style.transform = 'translateY(-1px)';
    };
    input.onblur = function(){
      this.style.borderColor = '#e5e7eb';
      this.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.04)';
      this.style.transform = '';
    };
    
    // Search icon - right aligned
    var searchIcon = document.createElement('div');
    searchIcon.style.position = 'absolute';
    searchIcon.style.right = '16px';
    searchIcon.style.top = '50%';
    searchIcon.style.transform = 'translateY(-50%)';
    searchIcon.style.width = '20px';
    searchIcon.style.height = '20px';
    searchIcon.style.pointerEvents = 'none';
    searchIcon.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg fill=\'none\' stroke=\'%236b7280\' stroke-width=\'2\' viewBox=\'0 0 24 24\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z\'/%3E%3C/svg%3E")';
    searchIcon.style.backgroundRepeat = 'no-repeat';
    searchIcon.style.backgroundPosition = 'center';
    searchIcon.style.backgroundSize = 'contain';
    
    inputWrapper.appendChild(input);
    inputWrapper.appendChild(searchIcon);
    
    // Level dropdown - horizontally aligned with search input (no label, text inside dropdown)
    var levelSelect = document.createElement('select');
    levelSelect.id = 'alm-lesson-level-filter';
    levelSelect.style.flex = '1';
    levelSelect.style.minWidth = '0';
    levelSelect.style.padding = '10px 16px';
    levelSelect.style.paddingRight = '40px';
    levelSelect.style.border = '2px solid #e5e7eb';
    levelSelect.style.borderRadius = '8px';
    levelSelect.style.background = '#ffffff';
    levelSelect.style.color = '#1f2937';
    levelSelect.style.fontSize = '15px';
    levelSelect.style.fontWeight = '500';
    levelSelect.style.cursor = 'pointer';
    levelSelect.style.outline = 'none';
    levelSelect.style.transition = 'all 0.2s ease';
    levelSelect.style.appearance = 'none';
    levelSelect.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E")';
    levelSelect.style.backgroundRepeat = 'no-repeat';
    levelSelect.style.backgroundPosition = 'right 12px center';
    levelSelect.style.backgroundSize = '16px';
    levelSelect.style.boxSizing = 'border-box';
    
    levelSelect.onmouseenter = function(){
      this.style.borderColor = '#3b82f6';
    };
    levelSelect.onmouseleave = function(){
      if (document.activeElement !== this) {
        this.style.borderColor = '#e5e7eb';
      }
    };
    levelSelect.onfocus = function(){
      this.style.borderColor = '#3b82f6';
      this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
    };
    levelSelect.onblur = function(){
      this.style.borderColor = '#e5e7eb';
      this.style.boxShadow = 'none';
    };
    
    var levelOptions = [
      {value: '', text: 'Filter by skill level'},
      {value: 'beginner', text: 'Beginner'},
      {value: 'intermediate', text: 'Intermediate'},
      {value: 'advanced', text: 'Advanced'},
      {value: 'pro', text: 'Pro'}
    ];
    
    levelOptions.forEach(function(opt){
      var option = document.createElement('option');
      option.value = opt.value;
      option.textContent = opt.text;
      // ALWAYS default to "All Skill Levels" - explicitly set selected
      if (opt.value === '') {
        option.selected = true;
      } else {
        option.selected = false;
      }
      levelSelect.appendChild(option);
    });
    
    // Force "All Skill Levels" to be selected and ensure state is empty
    levelSelect.value = '';
    state.lesson_level = '';
    
    // Second row: Filters with label
    var filtersRow = document.createElement('div');
    filtersRow.style.display = 'flex';
    filtersRow.style.alignItems = 'center';
    filtersRow.style.gap = '16px';
    filtersRow.style.width = '100%';
    filtersRow.style.marginBottom = '20px';
    
    // Add "Filters:" label before the dropdowns
    var filtersLabel = document.createElement('label');
    filtersLabel.textContent = 'Filters:';
    filtersLabel.style.fontSize = '16px';
    filtersLabel.style.fontWeight = '600';
    filtersLabel.style.color = '#374151';
    filtersLabel.style.flexShrink = '0';
    filtersLabel.style.marginRight = '4px';
    
    // Filter dropdowns container - flex to fill remaining width
    var filtersContainer = document.createElement('div');
    filtersContainer.style.display = 'flex';
    filtersContainer.style.alignItems = 'center';
    filtersContainer.style.gap = '16px';
    filtersContainer.style.flex = '1';
    filtersContainer.style.minWidth = '0';
    
    // Tag dropdown (flex to fill space)
    var tagSelect = document.createElement('select');
    tagSelect.id = 'alm-lesson-tag-filter';
    tagSelect.style.flex = '1';
    tagSelect.style.minWidth = '0';
    tagSelect.style.padding = '10px 16px';
    tagSelect.style.paddingRight = '40px';
    tagSelect.style.border = '2px solid #e5e7eb';
    tagSelect.style.borderRadius = '8px';
    tagSelect.style.background = '#ffffff';
    tagSelect.style.color = '#1f2937';
    tagSelect.style.fontSize = '15px';
    tagSelect.style.fontWeight = '500';
    tagSelect.style.cursor = 'pointer';
    tagSelect.style.outline = 'none';
    tagSelect.style.transition = 'all 0.2s ease';
    tagSelect.style.appearance = 'none';
    tagSelect.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E")';
    tagSelect.style.backgroundRepeat = 'no-repeat';
    tagSelect.style.backgroundPosition = 'right 12px center';
    tagSelect.style.backgroundSize = '16px';
    tagSelect.style.boxSizing = 'border-box';
    
    tagSelect.onmouseenter = function(){
      this.style.borderColor = '#3b82f6';
    };
    tagSelect.onmouseleave = function(){
      if (document.activeElement !== this) {
        this.style.borderColor = '#e5e7eb';
      }
    };
    tagSelect.onfocus = function(){
      this.style.borderColor = '#3b82f6';
      this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
    };
    tagSelect.onblur = function(){
      this.style.borderColor = '#e5e7eb';
      this.style.boxShadow = 'none';
    };
    
    // Add default "Filter by tag" option
    var tagDefaultOption = document.createElement('option');
    tagDefaultOption.value = '';
    tagDefaultOption.textContent = 'Filter by tag';
    tagDefaultOption.selected = true;
    tagSelect.appendChild(tagDefaultOption);
    
    // Load tags from API
    var tagsUrl = '{$tags_url_js}';
    fetch(tagsUrl).then(function(r){ return r.json(); }).then(function(tags){
      if (tags && Array.isArray(tags)) {
        tags.forEach(function(tag){
          var option = document.createElement('option');
          option.value = tag.name;
          option.textContent = tag.name;
          if (tag.name === initialTag) {
            option.selected = true;
            tagDefaultOption.selected = false;
          }
          tagSelect.appendChild(option);
        });
      }
    }).catch(function(err){
      console.error('ALM Search: Failed to load tags', err);
    });
    
    // Update tag when changed (don't auto-search, wait for button click)
    tagSelect.addEventListener('change', function(){
      state.tag = tagSelect.value.trim();
      state.page = 1;
    });
    
    // Style dropdown
    var styleSelect = document.createElement('select');
    styleSelect.id = 'alm-lesson-style-filter';
    styleSelect.style.flex = '1';
    styleSelect.style.minWidth = '0';
    styleSelect.style.padding = '10px 16px';
    styleSelect.style.paddingRight = '40px';
    styleSelect.style.border = '2px solid #e5e7eb';
    styleSelect.style.borderRadius = '8px';
    styleSelect.style.background = '#ffffff';
    styleSelect.style.color = '#1f2937';
    styleSelect.style.fontSize = '15px';
    styleSelect.style.fontWeight = '500';
    styleSelect.style.cursor = 'pointer';
    styleSelect.style.outline = 'none';
    styleSelect.style.transition = 'all 0.2s ease';
    styleSelect.style.appearance = 'none';
    styleSelect.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E")';
    styleSelect.style.backgroundRepeat = 'no-repeat';
    styleSelect.style.backgroundPosition = 'right 12px center';
    styleSelect.style.backgroundSize = '16px';
    styleSelect.style.boxSizing = 'border-box';
    
    styleSelect.onmouseenter = function(){
      this.style.borderColor = '#3b82f6';
    };
    styleSelect.onmouseleave = function(){
      if (document.activeElement !== this) {
        this.style.borderColor = '#e5e7eb';
      }
    };
    styleSelect.onfocus = function(){
      this.style.borderColor = '#3b82f6';
      this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
    };
    styleSelect.onblur = function(){
      this.style.borderColor = '#e5e7eb';
      this.style.boxShadow = 'none';
    };
    
    // Hard-coded styles list
    var styleOptions = [
      {value: '', text: 'Filter by style'},
      {value: 'Any', text: 'Any'},
      {value: 'Jazz', text: 'Jazz'},
      {value: 'Cocktail', text: 'Cocktail'},
      {value: 'Blues', text: 'Blues'},
      {value: 'Rock', text: 'Rock'},
      {value: 'Funk', text: 'Funk'},
      {value: 'Latin', text: 'Latin'},
      {value: 'Classical', text: 'Classical'},
      {value: 'Smooth Jazz', text: 'Smooth Jazz'},
      {value: 'Holiday', text: 'Holiday'},
      {value: 'Ballad', text: 'Ballad'},
      {value: 'Pop', text: 'Pop'},
      {value: 'New Age', text: 'New Age'},
      {value: 'Gospel', text: 'Gospel'},
      {value: 'New Orleans', text: 'New Orleans'},
      {value: 'Country', text: 'Country'},
      {value: 'Modal', text: 'Modal'},
      {value: 'Stride', text: 'Stride'},
      {value: 'Organ', text: 'Organ'},
      {value: 'Boogie', text: 'Boogie'}
    ];
    
    styleOptions.forEach(function(opt){
      var option = document.createElement('option');
      option.value = opt.value;
      option.textContent = opt.text;
      if (opt.value === initialStyle) {
        option.selected = true;
      }
      styleSelect.appendChild(option);
    });
    
    // Update style when changed (don't auto-search, wait for button click)
    styleSelect.addEventListener('change', function(){
      state.lesson_style = styleSelect.value.trim();
      state.page = 1;
    });
    
    // Membership Level dropdown
    var membershipSelect = document.createElement('select');
    membershipSelect.id = 'alm-lesson-membership-filter';
    membershipSelect.style.flex = '1';
    membershipSelect.style.minWidth = '0';
    membershipSelect.style.padding = '10px 16px';
    membershipSelect.style.paddingRight = '40px';
    membershipSelect.style.border = '2px solid #e5e7eb';
    membershipSelect.style.borderRadius = '8px';
    membershipSelect.style.background = '#ffffff';
    membershipSelect.style.color = '#1f2937';
    membershipSelect.style.fontSize = '15px';
    membershipSelect.style.fontWeight = '500';
    membershipSelect.style.cursor = 'pointer';
    membershipSelect.style.outline = 'none';
    membershipSelect.style.transition = 'all 0.2s ease';
    membershipSelect.style.appearance = 'none';
    membershipSelect.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E")';
    membershipSelect.style.backgroundRepeat = 'no-repeat';
    membershipSelect.style.backgroundPosition = 'right 12px center';
    membershipSelect.style.backgroundSize = '16px';
    membershipSelect.style.boxSizing = 'border-box';
    
    membershipSelect.onmouseenter = function(){
      this.style.borderColor = '#3b82f6';
    };
    membershipSelect.onmouseleave = function(){
      if (document.activeElement !== this) {
        this.style.borderColor = '#e5e7eb';
      }
    };
    membershipSelect.onfocus = function(){
      this.style.borderColor = '#3b82f6';
      this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
    };
    membershipSelect.onblur = function(){
      this.style.borderColor = '#e5e7eb';
      this.style.boxShadow = 'none';
    };
    
    // Hard-coded membership levels
    var membershipOptions = [
      {value: '', text: 'Filter by membership'},
      {value: '0', text: 'Free'},
      {value: '1', text: 'Essentials'},
      {value: '2', text: 'Studio'},
      {value: '3', text: 'Premier'}
    ];
    
    membershipOptions.forEach(function(opt){
      var option = document.createElement('option');
      option.value = opt.value;
      option.textContent = opt.text;
      if (opt.value === initialMembershipLevel) {
        option.selected = true;
      }
      membershipSelect.appendChild(option);
    });
    
    // Update membership level when changed (don't auto-search, wait for button click)
    membershipSelect.addEventListener('change', function(){
      state.membership_level = membershipSelect.value.trim();
      state.page = 1;
    });
    
    // Search button
    var searchButton = document.createElement('button');
    searchButton.type = 'submit';
    searchButton.textContent = 'Search';
    searchButton.style.padding = '12px 32px';
    searchButton.style.fontSize = '16px';
    searchButton.style.fontWeight = '600';
    searchButton.style.color = '#ffffff';
    searchButton.style.background = '#239B90';
    searchButton.style.border = '2px solid #239B90';
    searchButton.style.borderRadius = '8px';
    searchButton.style.cursor = 'pointer';
    searchButton.style.transition = 'all 0.2s ease';
    searchButton.style.flexShrink = '0';
    searchButton.style.fontFamily = 'inherit';
    searchButton.style.whiteSpace = 'nowrap';
    
    searchButton.onmouseenter = function(){
      this.style.background = '#1a7a6f';
      this.style.borderColor = '#1a7a6f';
      this.style.transform = 'translateY(-1px)';
    };
    searchButton.onmouseleave = function(){
      this.style.background = '#239B90';
      this.style.borderColor = '#239B90';
      this.style.transform = '';
    };
    
    // Clear search button
    var clearButton = document.createElement('button');
    clearButton.type = 'button';
    clearButton.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><path d="M6 18L18 6M6 6l12 12"/></svg>Clear';
    clearButton.style.padding = '12px 24px';
    clearButton.style.fontSize = '16px';
    clearButton.style.fontWeight = '600';
    clearButton.style.color = '#6b7280';
    clearButton.style.background = '#ffffff';
    clearButton.style.border = '2px solid #e5e7eb';
    clearButton.style.borderRadius = '8px';
    clearButton.style.cursor = 'pointer';
    clearButton.style.transition = 'all 0.2s ease';
    clearButton.style.flexShrink = '0';
    clearButton.style.fontFamily = 'inherit';
    clearButton.style.whiteSpace = 'nowrap';
    clearButton.style.display = 'flex';
    clearButton.style.alignItems = 'center';
    clearButton.style.justifyContent = 'center';
    
    clearButton.onmouseenter = function(){
      this.style.background = '#f9fafb';
      this.style.borderColor = '#d1d5db';
      this.style.color = '#374151';
    };
    clearButton.onmouseleave = function(){
      this.style.background = '#ffffff';
      this.style.borderColor = '#e5e7eb';
      this.style.color = '#6b7280';
    };
    
    clearButton.onclick = function(){
      // Clear all inputs
      input.value = '';
      levelSelect.value = '';
      tagSelect.value = '';
      styleSelect.value = '';
      membershipSelect.value = '';
      
      // Reset state
      state.q = '';
      state.lesson_level = '';
      state.tag = '';
      state.lesson_style = '';
      state.membership_level = '';
      state.page = 1;
      state.per_page = 24;
      
      // Clear URL parameters
      var url = new URL(window.location.href);
      url.searchParams.delete('q');
      url.searchParams.delete('lesson_level');
      url.searchParams.delete('tag');
      url.searchParams.delete('lesson_style');
      url.searchParams.delete('membership_level');
      url.searchParams.delete('page');
      url.searchParams.delete('per_page');
      window.history.pushState({}, '', url.toString());
      
      // Clear results
      results.innerHTML = '';
      pager.innerHTML = '';
      
      // Trigger a search to show all lessons (empty search)
      fetchData();
    };
    
    // Add elements to search row (first row)
    searchRow.appendChild(searchLabel);
    searchRow.appendChild(inputWrapper);
    searchRow.appendChild(searchButton);
    searchRow.appendChild(clearButton);
    
    // Add elements to filters row (second row)
    filtersRow.appendChild(filtersLabel);
    filtersContainer.appendChild(levelSelect);
    filtersContainer.appendChild(tagSelect);
    filtersContainer.appendChild(styleSelect);
    filtersContainer.appendChild(membershipSelect);
    filtersRow.appendChild(filtersContainer);
    
    form.appendChild(searchRow);
    form.appendChild(filtersRow);
    
    // SQL Query Debug Panel (will be appended after pager, below pagination)
    var sqlDebugPanel = document.createElement('div');
    sqlDebugPanel.id = 'alm-sql-debug-panel';
    sqlDebugPanel.style.display = 'none';
    sqlDebugPanel.style.marginTop = '20px';
    sqlDebugPanel.style.marginBottom = '20px';
    sqlDebugPanel.style.padding = '16px';
    sqlDebugPanel.style.background = '#f8f9fa';
    sqlDebugPanel.style.border = '1px solid #dee2e6';
    sqlDebugPanel.style.borderRadius = '8px';
    sqlDebugPanel.style.fontFamily = 'monospace';
    sqlDebugPanel.style.fontSize = '12px';
    
    var sqlTitleRow = document.createElement('div');
    sqlTitleRow.style.display = 'flex';
    sqlTitleRow.style.alignItems = 'center';
    sqlTitleRow.style.justifyContent = 'space-between';
    sqlTitleRow.style.marginBottom = '12px';
    
    var sqlTitle = document.createElement('div');
    sqlTitle.style.fontWeight = '700';
    sqlTitle.style.fontSize = '13px';
    sqlTitle.style.color = '#374151';
    sqlTitle.textContent = 'SQL Query Executed:';
    sqlTitleRow.appendChild(sqlTitle);
    
    var sqlCopyBtn = document.createElement('button');
    sqlCopyBtn.type = 'button';
    sqlCopyBtn.style.padding = '6px 16px';
    sqlCopyBtn.style.fontSize = '12px';
    sqlCopyBtn.style.fontWeight = '600';
    sqlCopyBtn.style.background = '#239B90';
    sqlCopyBtn.style.color = '#ffffff';
    sqlCopyBtn.style.border = 'none';
    sqlCopyBtn.style.borderRadius = '6px';
    sqlCopyBtn.style.cursor = 'pointer';
    sqlCopyBtn.style.transition = 'all 0.2s ease';
    sqlCopyBtn.style.fontFamily = 'inherit';
    sqlCopyBtn.textContent = 'Copy SQL';
    
    sqlCopyBtn.onmouseenter = function(){
      this.style.background = '#1a7a6f';
      this.style.transform = 'scale(1.05)';
    };
    sqlCopyBtn.onmouseleave = function(){
      this.style.background = '#239B90';
      this.style.transform = 'scale(1)';
    };
    
    var sqlQueryText = document.createElement('div');
    sqlQueryText.id = 'alm-sql-query-text';
    sqlQueryText.style.whiteSpace = 'pre-wrap';
    sqlQueryText.style.wordBreak = 'break-word';
    sqlQueryText.style.color = '#1f2937';
    sqlQueryText.style.lineHeight = '1.6';
    sqlQueryText.style.maxHeight = '300px';
    sqlQueryText.style.overflow = 'auto';
    sqlQueryText.style.background = '#ffffff';
    sqlQueryText.style.padding = '12px';
    sqlQueryText.style.borderRadius = '4px';
    sqlQueryText.style.border = '1px solid #dee2e6';
    
    sqlCopyBtn.onclick = function(){
      var sqlText = document.getElementById('alm-sql-query-text').textContent;
      navigator.clipboard.writeText(sqlText).then(function(){
        sqlCopyBtn.textContent = 'Copied!';
        sqlCopyBtn.style.background = '#10b981';
        setTimeout(function(){
          sqlCopyBtn.textContent = 'Copy SQL';
          sqlCopyBtn.style.background = '#239B90';
        }, 2000);
      }).catch(function(err){
        console.error('Failed to copy:', err);
        sqlCopyBtn.textContent = 'Failed';
        setTimeout(function(){
          sqlCopyBtn.textContent = 'Copy SQL';
        }, 2000);
      });
    };
    
    sqlTitleRow.appendChild(sqlCopyBtn);
    sqlDebugPanel.appendChild(sqlTitleRow);
    sqlDebugPanel.appendChild(sqlQueryText);
    
    // Function to display SQL query (accessible globally)
    // Show if user ID < 2005
    window.displaySQLQuery = function(sql) {
      if (!showDebug) {
        if (sqlDebugPanel) {
          sqlDebugPanel.style.display = 'none';
        }
        return;
      }
      if (sqlDebugPanel && sqlQueryText) {
        sqlDebugPanel.style.display = 'block';
        sqlQueryText.textContent = sql || 'No SQL query available';
      }
    };
    
    var results = document.createElement('div');
    results.style.marginTop = '0';
    
    // Show initial loading state
    results.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;gap:12px;padding:60px 20px;color:#475467;">\
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#239B90" stroke-width="2" style="animation: spin 1s linear infinite;">\
        <circle cx="12" cy="12" r="10" opacity="0.25"/>\
        <path d="M22 12a10 10 0 0 1-10 10" stroke-dasharray="31.416" stroke-dashoffset="31.416">\
          <animate attributeName="stroke-dasharray" dur="1s" values="0 31.416;15.708 15.708;0 31.416;0 31.416" repeatCount="indefinite"/>\
          <animate attributeName="stroke-dashoffset" dur="1s" values="0;-15.708;-31.416;-31.416" repeatCount="indefinite"/>\
        </path>\
      </svg>\
      <span style="font-size:16px;font-weight:500;">Loading lessons...</span>\
    </div>\
    <style>\
      @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }\
    </style>';
    
    function fmtDur(sec){
      sec = parseInt(sec||0,10); if (!sec || sec<0) return '';
      var h = Math.floor(sec/3600); var m = Math.floor((sec%3600)/60);
      if (h>0 && m>0) return h+'h '+m+'m';
      if (h>0) return h+'h';
      if (m>0) return m+'m';
      return '0m';
    }

    var pager = document.createElement('div');
    pager.className = 'alm-search-pager';
    pager.style.marginTop = '40px';
    pager.style.padding = '20px 40px';
    pager.style.display = 'flex';
    pager.style.flexDirection = 'column';
    pager.style.justifyContent = 'center';
    pager.style.alignItems = 'center';
    pager.style.gap = '12px';
    
    var prevBtn = document.createElement('button');
    prevBtn.textContent = '← Prev';
    prevBtn.type = 'button';
    prevBtn.className = 'alm-search-pager-btn';
    prevBtn.style.padding = '12px 24px';
    prevBtn.style.fontSize = '15px';
    prevBtn.style.fontWeight = '600';
    prevBtn.style.color = '#004555';
    prevBtn.style.background = '#ffffff';
    prevBtn.style.border = '2px solid #e9ecef';
    prevBtn.style.borderRadius = '8px';
    prevBtn.style.cursor = 'pointer';
    prevBtn.style.transition = 'all 0.3s ease';
    prevBtn.style.minWidth = '120px';
    
    // Remove the duplicate handlers - they're defined below
    
    var nextBtn = document.createElement('button');
    nextBtn.textContent = 'Next →';
    nextBtn.type = 'button';
    nextBtn.className = 'alm-search-pager-btn';
    nextBtn.style.padding = '12px 24px';
    nextBtn.style.fontSize = '15px';
    nextBtn.style.fontWeight = '600';
    nextBtn.style.color = '#ffffff';
    nextBtn.style.background = '#239B90';
    nextBtn.style.border = '2px solid #239B90';
    nextBtn.style.borderRadius = '8px';
    nextBtn.style.cursor = 'pointer';
    nextBtn.style.transition = 'all 0.3s ease';
    nextBtn.style.minWidth = '120px';
    
    nextBtn.onmouseenter = function(){
      if (!this.disabled) {
        this.style.background = '#1e7a6b';
        this.style.borderColor = '#1e7a6b';
        this.style.transform = 'translateY(-2px)';
        this.style.boxShadow = '0 4px 12px rgba(35, 155, 144, 0.3)';
      }
    };
    nextBtn.onmouseleave = function(){
      if (!this.disabled) {
        this.style.background = '#239B90';
        this.style.borderColor = '#239B90';
        this.style.transform = '';
        this.style.boxShadow = '';
      }
    };
    
    prevBtn.disabled = true;
    prevBtn.style.opacity = '0.5';
    prevBtn.style.cursor = 'not-allowed';
    prevBtn.onmouseenter = function(){
      if (!this.disabled) {
        this.style.background = '#f8f9fa';
        this.style.borderColor = '#239B90';
        this.style.color = '#239B90';
        this.style.transform = 'translateY(-2px)';
        this.style.boxShadow = '0 4px 12px rgba(35, 155, 144, 0.2)';
      }
    };
    prevBtn.onmouseleave = function(){
      if (!this.disabled) {
        this.style.background = '#ffffff';
        this.style.borderColor = '#e9ecef';
        this.style.color = '#004555';
        this.style.transform = '';
        this.style.boxShadow = '';
      }
    };
    
    // Button container to keep buttons horizontal
    var buttonContainer = document.createElement('div');
    buttonContainer.style.display = 'flex';
    buttonContainer.style.justifyContent = 'center';
    buttonContainer.style.alignItems = 'center';
    buttonContainer.style.gap = '12px';
    buttonContainer.appendChild(prevBtn);
    buttonContainer.appendChild(nextBtn);
    pager.appendChild(buttonContainer);
    
    function renderItems(data){
      results.innerHTML = '';
      var items = data.items || [];
      var total = data.total || 0;
      var currentPage = data.page || state.page || 1;
      // Use state.per_page to reflect what user selected, fallback to data.per_page
      var perPage = state.per_page || data.per_page || 24;
      var start = (currentPage - 1) * perPage + 1;
      var end = Math.min(currentPage * perPage, total);
      
      if (items.length === 0) { 
        results.innerHTML = '<p style="padding: 20px; text-align: center; color: #666;">No results found.</p>'; 
        return; 
      }
      
      // Results count header - styled better
      var resultsHeader = document.createElement('div');
      resultsHeader.style.marginTop = '24px';
      resultsHeader.style.marginBottom = '24px';
      resultsHeader.style.padding = '16px 20px';
      resultsHeader.style.background = '#f8f9fa';
      resultsHeader.style.border = '1px solid #e9ecef';
      resultsHeader.style.borderRadius = '8px';
      resultsHeader.style.color = '#495057';
      resultsHeader.style.fontSize = '15px';
      resultsHeader.style.fontWeight = '600';
      resultsHeader.style.display = 'flex';
      resultsHeader.style.justifyContent = 'space-between';
      resultsHeader.style.alignItems = 'center';
      resultsHeader.style.gap = '16px';
      
      // Left side: count and showing info
      var leftContent = document.createElement('div');
      leftContent.style.display = 'flex';
      leftContent.style.alignItems = 'center';
      leftContent.style.gap = '8px';
      if (total > 0) {
        leftContent.innerHTML = '<span style="color: #239B90; font-weight: 700;">' + total + '</span> <span style="color: #6c757d; font-weight: 500;">lessons found</span> <span style="color: #adb5bd; margin: 0 4px;">•</span> <span style="color: #6c757d; font-weight: 500;">Showing ' + start + '–' + end + '</span>';
      } else {
        leftContent.innerHTML = '<span style="color: #6c757d;">No lessons found</span>';
      }
      resultsHeader.appendChild(leftContent);
      
      // Right side: lessons per page dropdown
      if (total > 0) {
        var perPageContainer = document.createElement('div');
        perPageContainer.style.display = 'flex';
        perPageContainer.style.alignItems = 'center';
        perPageContainer.style.gap = '8px';
        
        var perPageLabel = document.createElement('span');
        perPageLabel.textContent = 'Per page:';
        perPageLabel.style.color = '#6c757d';
        perPageLabel.style.fontSize = '14px';
        perPageLabel.style.fontWeight = '500';
        
        var perPageSelect = document.createElement('select');
        perPageSelect.className = 'alm-per-page-select';
        perPageSelect.style.padding = '6px 28px 6px 10px';
        perPageSelect.style.border = '1px solid #ced4da';
        perPageSelect.style.borderRadius = '6px';
        perPageSelect.style.backgroundColor = '#ffffff';
        perPageSelect.style.fontSize = '14px';
        perPageSelect.style.fontWeight = '500';
        perPageSelect.style.color = '#495057';
        perPageSelect.style.cursor = 'pointer';
        perPageSelect.style.outline = 'none';
        perPageSelect.style.transition = 'all 0.2s ease';
        perPageSelect.style.appearance = 'none';
        perPageSelect.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23495057\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\'/%3E%3C/svg%3E")';
        perPageSelect.style.backgroundRepeat = 'no-repeat';
        perPageSelect.style.backgroundPosition = 'right 8px center';
        perPageSelect.style.backgroundSize = '16px';
        perPageSelect.style.minWidth = '70px';
        
        // Multiples of 3: 24, 48, 72
        var perPageOptions = [24, 48, 72];
        // Use state.per_page to ensure dropdown matches current selection
        var currentPerPage = state.per_page || perPage;
        perPageOptions.forEach(function(optionValue) {
          var option = document.createElement('option');
          option.value = optionValue;
          option.textContent = optionValue;
          if (optionValue === currentPerPage) {
            option.selected = true;
          }
          perPageSelect.appendChild(option);
        });
        
        perPageSelect.onmouseenter = function() {
          this.style.borderColor = '#239B90';
        };
        perPageSelect.onmouseleave = function() {
          this.style.borderColor = '#ced4da';
        };
        perPageSelect.onchange = function() {
          state.per_page = parseInt(this.value, 10);
          state.page = 1; // Reset to first page when per_page changes
          // Update URL to reflect per_page change
          var url = new URL(window.location.href);
          url.searchParams.set('per_page', state.per_page);
          url.searchParams.set('page', '1');
          window.history.pushState({}, '', url.toString());
          fetchData();
        };
        
        perPageContainer.appendChild(perPageLabel);
        perPageContainer.appendChild(perPageSelect);
        resultsHeader.appendChild(perPageContainer);
      }
      
      results.appendChild(resultsHeader);
      
      // Create grid container with 3 columns
      var grid = document.createElement('div');
      grid.className = 'alm-search-results-grid';
      grid.style.display = 'grid';
      grid.style.gridTemplateColumns = 'repeat(3, 1fr)';
      grid.style.gap = '30px';
      grid.style.marginTop = '0';
      grid.style.padding = '0 0 60px 0';
      
      items.forEach(function(it){
        var card = document.createElement('div');
        card.className = 'alm-lesson-card-course';
        
        var cardLink = document.createElement(it.permalink ? 'a' : 'div');
        if (it.permalink) {
          cardLink.href = it.permalink;
          cardLink.className = 'alm-lesson-card-link';
          // Allow buttons to prevent navigation
          cardLink.onclick = function(e){
            // If click is on a button, prevent navigation
            if (e.target.closest('button.alm-favorite-btn')) {
              e.preventDefault();
              e.stopPropagation();
            }
          };
        } else {
          cardLink.className = 'alm-lesson-card-link alm-lesson-card-link-disabled';
        }
        
        var cardContent = document.createElement('div');
        cardContent.className = 'alm-lesson-card-content';
        
        // Collection name badge at top - Dark Jazzedge blue background with white text (uniform larger size)
        if (it.collection_title) {
          var collectionBadge = document.createElement('div');
          collectionBadge.className = 'alm-lesson-collection-badge';
          collectionBadge.textContent = it.collection_title;
          cardContent.appendChild(collectionBadge);
        }
        
        // Inner content wrapper with padding
        var innerContent = document.createElement('div');
        innerContent.className = 'alm-lesson-inner-content';
        
        // Button container for top-right actions (favorite and video icon)
        var topRightActions = document.createElement('div');
        topRightActions.className = 'alm-lesson-top-actions';
        
        // Video icon button (if sample URL exists) - positioned before favorite button
        if (it.sample_video_url) {
          var videoIconBtn = document.createElement('button');
          videoIconBtn.type = 'button';
          videoIconBtn.className = 'alm-video-sample-btn';
          videoIconBtn.setAttribute('aria-label', 'View Sample Video');
          videoIconBtn.setAttribute('data-microtip-position', 'top-left');
          videoIconBtn.setAttribute('role', 'tooltip');
          
          // Heroicons play icon (for video preview)
          var videoIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
          videoIcon.setAttribute('width', '20');
          videoIcon.setAttribute('height', '20');
          videoIcon.setAttribute('viewBox', '0 0 24 24');
          videoIcon.setAttribute('fill', 'none');
          videoIcon.setAttribute('stroke', '#239B90');
          videoIcon.setAttribute('stroke-width', '2');
          videoIcon.setAttribute('stroke-linecap', 'round');
          videoIcon.setAttribute('stroke-linejoin', 'round');
          
          // Heroicons play icon path
          var playPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
          playPath.setAttribute('d', 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z');
          
          videoIcon.appendChild(playPath);
          videoIconBtn.appendChild(videoIcon);
          
          // Prevent card link navigation when clicking video icon
          videoIconBtn.onclick = function(e){
            e.preventDefault();
            e.stopPropagation();
            openSampleModal(it.sample_video_url, it.title || 'Sample Video');
          };
          
          topRightActions.appendChild(videoIconBtn);
        }
        
        // Favorite star button - positioned next to video icon (only show if user is logged in)
        if ({$is_user_logged_in}) {
          var favoriteBtn = document.createElement('button');
          favoriteBtn.type = 'button';
          favoriteBtn.className = 'alm-favorite-btn';
          favoriteBtn.setAttribute('data-title', it.title || '');
          favoriteBtn.setAttribute('data-url', it.permalink || '');
          favoriteBtn.setAttribute('data-description', it.description || '');
          favoriteBtn.setAttribute('aria-label', 'Add to Favorites');
          favoriteBtn.setAttribute('data-microtip-position', 'top-left');
          favoriteBtn.setAttribute('role', 'tooltip');
          
          // Star icon SVG (outline)
          var starIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
          starIcon.setAttribute('width', '20');
          starIcon.setAttribute('height', '20');
          starIcon.setAttribute('viewBox', '0 0 24 24');
          starIcon.setAttribute('fill', 'none');
          starIcon.setAttribute('stroke', '#6b7280');
          starIcon.setAttribute('stroke-width', '2');
          starIcon.setAttribute('stroke-linecap', 'round');
          starIcon.setAttribute('stroke-linejoin', 'round');
          var starPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
          starPath.setAttribute('d', 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z');
          starIcon.appendChild(starPath);
          favoriteBtn.appendChild(starIcon);
          
          // Click handler to toggle favorite
          favoriteBtn.onclick = function(e){
            e.preventDefault();
            e.stopPropagation();
            var btn = this;
            var isFavorited = btn.classList.contains('is-favorited');
            var title = btn.getAttribute('data-title');
            var url = btn.getAttribute('data-url');
            var description = btn.getAttribute('data-description');
            
            if (!url) {
              alert('This lesson is not available');
              return;
            }
            
            // Optimistic UI update - change immediately before AJAX
            var icon = btn.querySelector('svg path');
            if (isFavorited) {
              // Removing favorite - change to unfilled immediately
              btn.classList.remove('is-favorited');
              if (icon) {
                icon.setAttribute('fill', 'none');
                icon.setAttribute('stroke', '#6b7280');
              }
              btn.style.opacity = '0.6';
              btn.style.background = 'transparent';
              btn.setAttribute('aria-label', 'Add to Favorites');
            } else {
              // Adding favorite - change to filled immediately
              btn.classList.add('is-favorited');
              if (icon) {
                icon.setAttribute('fill', '#f04e23');
                icon.setAttribute('stroke', '#f04e23');
              }
              btn.style.opacity = '1';
              btn.style.background = 'rgba(240, 78, 35, 0.1)';
              btn.setAttribute('aria-label', 'Remove from Favorites');
            }
            
            // Disable button during request to prevent double-clicks
            btn.style.pointerEvents = 'none';
            
            var endpoint = isFavorited 
              ? '{$favorites_remove_url_js}'
              : '{$favorites_add_url_js}';
            
            var data = isFavorited 
              ? { title: title }
              : { title: title, url: url, description: description, category: 'lesson' };
            
            fetch(endpoint, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': '{$rest_nonce_js}'
              },
              body: JSON.stringify(data),
              credentials: 'same-origin'
            })
            .then(function(response){ return response.json(); })
            .then(function(result){
              if (!result.success) {
                // Revert UI change if request failed
                if (isFavorited) {
                  // Was removing, but failed - restore to favorited state
                  btn.classList.add('is-favorited');
                  if (icon) {
                    icon.setAttribute('fill', '#f04e23');
                    icon.setAttribute('stroke', '#f04e23');
                  }
                  btn.style.opacity = '1';
                  btn.style.background = 'rgba(240, 78, 35, 0.1)';
                  btn.setAttribute('aria-label', 'Remove from Favorites');
                } else {
                  // Was adding, but failed - restore to unfavorited state
                  btn.classList.remove('is-favorited');
                  if (icon) {
                    icon.setAttribute('fill', 'none');
                    icon.setAttribute('stroke', '#6b7280');
                  }
                  btn.style.opacity = '0.6';
                  btn.style.background = 'transparent';
                  btn.setAttribute('aria-label', 'Add to Favorites');
                }
                alert(result.message || 'Failed to update favorite');
              }
            })
            .catch(function(error){
              console.error('Favorite error:', error);
              // Revert UI change on error
              if (isFavorited) {
                // Was removing, but error - restore to favorited state
                btn.classList.add('is-favorited');
                if (icon) {
                  icon.setAttribute('fill', '#f04e23');
                  icon.setAttribute('stroke', '#f04e23');
                }
                btn.style.opacity = '1';
                btn.style.background = 'rgba(240, 78, 35, 0.1)';
                btn.setAttribute('aria-label', 'Remove from Favorites');
              } else {
                // Was adding, but error - restore to unfavorited state
                btn.classList.remove('is-favorited');
                if (icon) {
                  icon.setAttribute('fill', 'none');
                  icon.setAttribute('stroke', '#6b7280');
                }
                btn.style.opacity = '0.6';
                btn.style.background = 'transparent';
                btn.setAttribute('aria-label', 'Add to Favorites');
              }
              alert('Error updating favorite');
            })
            .finally(function(){
              btn.style.pointerEvents = 'auto';
            });
          };
          
          // Check if already favorited
          (function(btn, title){
            if (!title) return;
            
            fetch('{$favorites_check_url_js}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': '{$rest_nonce_js}'
              },
              body: JSON.stringify({ title: title }),
              credentials: 'same-origin'
            })
            .then(function(response){
              // Handle 403 gracefully - user not logged in or no permission
              if (response.status === 403) {
                return { success: false, favorited: false };
              }
              if (!response.ok) {
                return { success: false, favorited: false };
              }
              return response.json();
            })
            .then(function(result){
              if (result.success && result.is_favorited) {
                btn.classList.add('is-favorited');
                var icon = btn.querySelector('svg path');
                if (icon) {
                  icon.setAttribute('fill', '#f04e23');
                  icon.setAttribute('stroke', '#f04e23');
                }
                btn.style.opacity = '1';
                btn.style.background = 'rgba(240, 78, 35, 0.1)';
                btn.setAttribute('aria-label', 'Remove from Favorites');
              }
            })
            .catch(function(error){
              // Silently handle errors - don't log expected 403s
              if (error.name !== 'TypeError') {
                console.error('Check favorite error:', error);
              }
            });
          })(favoriteBtn, it.title);
          
          topRightActions.appendChild(favoriteBtn);
        }
        
        // Append the top-right actions container
        if (topRightActions.children.length > 0) {
          innerContent.appendChild(topRightActions);
        }
        
        // Title - MUST be appended first
        // Add padding-right to avoid clash with buttons (calculate based on number of buttons)
        var buttonCount = (it.sample_video_url ? 1 : 0) + ({$is_user_logged_in} ? 1 : 0);
        var paddingRight = buttonCount > 0 ? (buttonCount * 44) + 20 : '0'; // 36px button + 8px gap
        var title = document.createElement('h3');
        title.className = 'alm-lesson-title';
        title.style.paddingRight = paddingRight + 'px';
        title.textContent = it.title || 'Untitled Lesson';
        innerContent.appendChild(title);
        
        // Description (if available) - MUST be appended after title
        if (it.description) {
          var desc = document.createElement('div');
          desc.className = 'alm-lesson-description';
          desc.textContent = it.description;
          innerContent.appendChild(desc);
        }
        
        // Bottom section with duration and membership level - push to bottom
        var footer = document.createElement('div');
        footer.className = 'alm-lesson-footer';
        
        // Duration
        var durationContainer = document.createElement('div');
        var dStr = fmtDur(it.duration);
        if (dStr) {
          durationContainer.className = 'alm-lesson-duration';
          durationContainer.innerHTML = '<span class="dashicons dashicons-clock" style="font-size: 16px; width: 16px; height: 16px;"></span> ' + dStr;
        } else {
          durationContainer.className = 'alm-lesson-duration-empty';
        }
        
        // Badge container for membership and skill level
        var badgeContainer = document.createElement('div');
        badgeContainer.className = 'alm-lesson-badge-container';
        
        // Skill Level Badge
        if (it.lesson_level) {
          var skillLevelNames = {
            'beginner': 'Beg',
            'intermediate': 'Int',
            'advanced': 'Adv',
            'pro': 'Pro'
          };
          var skillLevelColors = {
            'beginner': '#46b450',
            'intermediate': '#239B90',
            'advanced': '#f0ad4e',
            'pro': '#dc3232'
          };
          var skillLevelName = skillLevelNames[it.lesson_level] || it.lesson_level;
          var skillLevelColor = skillLevelColors[it.lesson_level] || '#666';
          
          var skillLevelBadge = document.createElement('div');
          skillLevelBadge.className = 'alm-lesson-skill-badge';
          skillLevelBadge.style.background = skillLevelColor;
          skillLevelBadge.textContent = skillLevelName;
          badgeContainer.appendChild(skillLevelBadge);
        }
        
        // Membership Level Badge
        if (it.membership_level_name && it.membership_level_name !== 'Unknown') {
          var membershipBadge = document.createElement('div');
          membershipBadge.className = 'alm-lesson-membership-badge';
          membershipBadge.textContent = it.membership_level_name;
          badgeContainer.appendChild(membershipBadge);
        }
        
        footer.appendChild(durationContainer);
        footer.appendChild(badgeContainer);
        
        innerContent.appendChild(footer);
        
        cardContent.appendChild(innerContent);
        
        cardLink.appendChild(cardContent);
        card.appendChild(cardLink);
        grid.appendChild(card);
      });
      
      results.appendChild(grid);
    }

    // Sample Video Modal Functions
    function openSampleModal(videoUrl, title) {
      // Create modal if it doesn't exist
      var modal = document.getElementById('alm-sample-modal');
      if (!modal) {
        modal = document.createElement('div');
        modal.id = 'alm-sample-modal';
        modal.style.cssText = 'display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.85); overflow: auto;';
        
        var modalContent = document.createElement('div');
        modalContent.style.cssText = 'position: relative; background-color: #ffffff; margin: 5% auto; padding: 0; width: 90%; max-width: 900px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);';
        
        var modalHeader = document.createElement('div');
        modalHeader.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #e9ecef;';
        
        var modalTitle = document.createElement('h2');
        modalTitle.id = 'alm-sample-modal-title';
        modalTitle.style.cssText = 'margin: 0; font-size: 20px; font-weight: 600; color: #004555;';
        modalTitle.textContent = title;
        
        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = 'background: none; border: none; font-size: 32px; font-weight: 300; color: #6c757d; cursor: pointer; padding: 0; width: 32px; height: 32px; line-height: 1;';
        closeBtn.onclick = closeSampleModal;
        
        modalHeader.appendChild(modalTitle);
        modalHeader.appendChild(closeBtn);
        
        var modalBody = document.createElement('div');
        modalBody.id = 'alm-sample-modal-body';
        modalBody.style.cssText = 'padding: 24px;';
        
        modalContent.appendChild(modalHeader);
        modalContent.appendChild(modalBody);
        modal.appendChild(modalContent);
        
        // Close on background click
        modal.onclick = function(e) {
          if (e.target === modal) {
            closeSampleModal();
          }
        };
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape' && modal.style.display === 'block') {
            closeSampleModal();
          }
        });
        
        document.body.appendChild(modal);
      }
      
      // Convert video URL to embed format if needed
      var embedUrl = convertToEmbedUrl(videoUrl);
      
      // Update modal content
      document.getElementById('alm-sample-modal-title').textContent = title;
      var modalBody = document.getElementById('alm-sample-modal-body');
      modalBody.innerHTML = '<div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; background: #000;">' +
        '<iframe src="' + escapeHtml(embedUrl) + '" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;" allowfullscreen></iframe>' +
        '</div>';
      
      // Show modal
      modal.style.display = 'block';
      document.body.style.overflow = 'hidden';
    }
    
    function closeSampleModal() {
      var modal = document.getElementById('alm-sample-modal');
      if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        // Clear iframe to stop video playback
        var modalBody = document.getElementById('alm-sample-modal-body');
        if (modalBody) {
          modalBody.innerHTML = '';
        }
      }
    }
    
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

    function fetchData(){
      if (!endpoint) {
        console.error('ALM Search: endpoint is not set');
        return;
      }
      
      // ALWAYS update state.q from input value before building URL
      state.q = input.value.trim();
      // Update tag from dropdown value
      if (tagSelect) {
        state.tag = tagSelect.value.trim();
      }
      // Update style from dropdown value
      if (styleSelect) {
        state.lesson_style = styleSelect.value.trim();
      }
      // Update membership level from dropdown value
      if (membershipSelect) {
        state.membership_level = membershipSelect.value.trim();
      }
      
      var url = new URL(endpoint, window.location.origin);
      Object.keys(state).forEach(function(k){
        // Include all state values (even empty strings, but not null/undefined)
        if (state[k] !== null && state[k] !== undefined) {
          // Always include lesson_level even if empty (for "All Levels")
          // Always include q even if empty (might be cleared)
          // Include tag only if it's not empty
          if (k === 'tag' && (!state[k] || state[k] === '')) {
            // Don't include empty tag in URL
            return;
          }
          // Include lesson_style only if it's not empty
          if (k === 'lesson_style' && (!state[k] || state[k] === '')) {
            // Don't include empty lesson_style in URL
            return;
          }
          // Include membership_level only if it's not empty
          if (k === 'membership_level' && (!state[k] || state[k] === '')) {
            // Don't include empty membership_level in URL
            return;
          }
          url.searchParams.set(k, state[k]);
        }
      });
      
      results.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;gap:12px;padding:60px 20px;color:#475467;">\
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#239B90" stroke-width="2" style="animation: spin 1s linear infinite;">\
          <circle cx="12" cy="12" r="10" opacity="0.25"/>\
          <path d="M22 12a10 10 0 0 1-10 10" stroke-dasharray="31.416" stroke-dashoffset="31.416">\
            <animate attributeName="stroke-dasharray" dur="1s" values="0 31.416;15.708 15.708;0 31.416;0 31.416" repeatCount="indefinite"/>\
            <animate attributeName="stroke-dashoffset" dur="1s" values="0;-15.708;-31.416;-31.416" repeatCount="indefinite"/>\
          </path>\
        </svg>\
        <span style="font-size:16px;font-weight:500;">Searching...</span>\
      </div>';
      fetch(url.toString()).then(function(r){ return r.json(); }).then(function(data){
        renderItems(data);
        
        // Display SQL query if available and user ID < 2005
        if (showDebug && data.debug && data.debug.sql) {
          displaySQLQuery(data.debug.sql);
        } else if (sqlDebugPanel) {
          sqlDebugPanel.style.display = 'none';
        }
        
        var totalPages = data.total_pages || 1;
        var currentPageNum = data.page || state.page;
        var total = data.total || 0;
        // Use state.per_page to reflect what user selected, fallback to data.per_page
        var perPage = state.per_page || data.per_page || 24;
        var start = (currentPageNum - 1) * perPage + 1;
        var end = Math.min(currentPageNum * perPage, total);
        
        prevBtn.disabled = currentPageNum <= 1;
        nextBtn.disabled = currentPageNum >= totalPages || !data.items || data.items.length === 0;
        
        // Update pagination count display below buttons (centered)
        var pagerCount = pager.querySelector('.alm-pager-count');
        if (!pagerCount) {
          pagerCount = document.createElement('div');
          pagerCount.className = 'alm-pager-count';
          pagerCount.style.width = '100%';
          pagerCount.style.textAlign = 'center';
          pagerCount.style.marginTop = '16px';
          pagerCount.style.padding = '12px 0';
          pagerCount.style.color = '#6c757d';
          pagerCount.style.fontSize = '14px';
          pagerCount.style.fontWeight = '500';
          pager.appendChild(pagerCount);
        }
        if (total > 0) {
          pagerCount.innerHTML = '<span style="color: #239B90; font-weight: 700;">' + total + '</span> <span style="color: #6c757d; font-weight: 500;">lessons found</span> <span style="color: #adb5bd; margin: 0 4px;">•</span> <span style="color: #6c757d; font-weight: 500;">Showing ' + start + '–' + end + '</span>';
        } else {
          pagerCount.innerHTML = '<span style="color: #6c757d;">No lessons found</span>';
        }
        
        // Update button styles based on disabled state
        if (prevBtn.disabled) {
          prevBtn.style.opacity = '0.5';
          prevBtn.style.cursor = 'not-allowed';
        } else {
          prevBtn.style.opacity = '1';
          prevBtn.style.cursor = 'pointer';
        }
        
        if (nextBtn.disabled) {
          nextBtn.style.opacity = '0.5';
          nextBtn.style.cursor = 'not-allowed';
          nextBtn.style.background = '#e9ecef';
          nextBtn.style.borderColor = '#e9ecef';
          nextBtn.style.color = '#6c757d';
        } else {
          nextBtn.style.opacity = '1';
          nextBtn.style.cursor = 'pointer';
          nextBtn.style.background = '#239B90';
          nextBtn.style.borderColor = '#239B90';
          nextBtn.style.color = '#ffffff';
        }
      }).catch(function(err){ console.error('ALM Search fetch error:', err); results.innerHTML = '<p>Error loading results.</p>'; });
    }

    // Update level when changed (don't auto-search, wait for button click)
    levelSelect.addEventListener('change', function(){
      var selectedLevel = levelSelect.value;
      state.lesson_level = selectedLevel || ''; // Empty string for "All Levels"
      state.page = 1;

      // Don't auto-search on level change - wait for search button click
      // Note: We no longer save level preference - always defaults to "All Levels"
    });
    
    // Search input - no auto-search, wait for button click or Enter
    input.addEventListener('keypress', function(e){
      if (e.key === 'Enter') {
        e.preventDefault();
        // Update state.q from input value before searching
        state.q = input.value.trim();
        state.tag = tagSelect ? tagSelect.value.trim() : '';
        state.lesson_style = styleSelect ? styleSelect.value.trim() : '';
        state.membership_level = membershipSelect ? membershipSelect.value.trim() : '';
        state.page = 1;
        fetchData();
      }
    });
    prevBtn.addEventListener('click', function(){ if (state.page > 1){ state.page--; fetchData(); }});
    nextBtn.addEventListener('click', function(){ state.page++; fetchData(); });

    searchContainer.appendChild(form);
    searchContainer.appendChild(results);
    searchContainer.appendChild(pager);
    // Append debug panel after pager (below pagination)
    searchContainer.appendChild(sqlDebugPanel);
    root.appendChild(searchContainer);

    // Set level dropdown from URL parameter (or default to "All Levels")
    if (initialLevel) {
      levelSelect.value = initialLevel;
    } else {
      state.lesson_level = '';
      levelSelect.value = '';
    }
    
    // Set tag dropdown from URL parameter
    if (tagSelect && initialTag) {
      tagSelect.value = initialTag;
      state.tag = initialTag;
    } else if (tagSelect) {
      tagSelect.value = '';
      state.tag = '';
    }
    
    // Set style dropdown from URL parameter
    if (styleSelect && initialStyle) {
      styleSelect.value = initialStyle;
      state.lesson_style = initialStyle;
    } else if (styleSelect) {
      styleSelect.value = '';
      state.lesson_style = '';
    }
    
    // Set membership level dropdown from URL parameter
    if (membershipSelect && initialMembershipLevel) {
      membershipSelect.value = initialMembershipLevel;
      state.membership_level = initialMembershipLevel;
    } else if (membershipSelect) {
      membershipSelect.value = '';
      state.membership_level = '';
    }
    
    // Trigger initial search (with URL parameters if present, otherwise empty search)
    fetchData();
  }

  // Compact dashboard variant
  var compact = document.querySelector('#alm-lesson-search-compact');
  if (compact) {
    var endpoint2 = compact.getAttribute('data-endpoint');
    var viewAllUrl = compact.getAttribute('data-view-all') || '';
    var maxItems = parseInt(compact.getAttribute('data-max-items') || '10', 10);
    compact.style.position = 'relative';
    var input2 = compact.querySelector('input[type="search"]');
    if (!input2) {
      console.error('ALM Search: Input element not found in compact search container');
      return;
    }
    var panel = document.createElement('div');
    panel.style.position = 'absolute';
    panel.style.top = '100%';
    panel.style.left = '0';
    panel.style.right = '0';
    panel.style.background = '#fff';
    panel.style.border = '1px solid #ddd';
    panel.style.borderTop = '0';
    panel.style.zIndex = '1000';
    panel.style.borderRadius = '0 0 8px 8px';
    panel.style.boxShadow = '0 8px 24px rgba(0,0,0,0.08)';
    panel.style.maxHeight = '70vh';
    panel.style.display = 'none';
    panel.style.flexDirection = 'column';
    panel.style.overflow = 'hidden';
    compact.appendChild(panel);

    var state2 = { q: '', page: 1, per_page: maxItems };
    var aborter;
    function fmtDur2(sec){
      sec = parseInt(sec||0,10); if (!sec || sec<0) return '';
      var h = Math.floor(sec/3600); var m = Math.floor((sec%3600)/60);
      if (h>0 && m>0) return h+'h '+m+'m';
      if (h>0) return h+'h';
      if (m>0) return m+'m';
      return '0m';
    }

    function renderCompact(data){
      panel.innerHTML = '';
      panel.style.display = 'flex';
      var items = data.items || [];
      if (items.length === 0) { panel.innerHTML = '<div style="padding:10px;color:#666;">No results.</div>'; panel.style.display='block'; return; }
      
      // Add "View all results" link at the top
      if (viewAllUrl){
        var header = document.createElement('div');
        header.style.textAlign='right';
        header.style.padding='12px 16px';
        header.style.background='#fff';
        header.style.borderBottom='1px solid #eee';
        header.style.flexShrink='0';
        header.style.position='relative';
        header.style.zIndex='10';
        var all = document.createElement('a');
        all.href = viewAllUrl + (state2.q ? ('?q='+encodeURIComponent(state2.q)) : '');
        all.textContent='View all results';
        all.style.color='#ffffff';
        all.style.textDecoration='none';
        all.style.fontWeight='400';
        all.style.fontSize='13px';
        all.style.display='inline-block';
        all.style.padding='6px 12px';
        all.style.background='#2271b1';
        all.style.borderRadius='6px';
        header.appendChild(all);
        panel.appendChild(header);
      }
      
      var ul = document.createElement('div');
      ul.style.overflowY = 'auto';
      ul.style.flex = '1 1 auto';
      ul.style.minHeight = '0';
      ul.style.maxWidth = '100%';
      items.forEach(function(it){
        var row = document.createElement('a');
        row.href = it.permalink || '#';
        row.style.display = 'block';
        row.style.padding = '10px 12px';
        row.style.borderBottom = '1px solid #eee';
        var dStr = fmtDur2(it.duration);
        row.innerHTML = '<div style="font-weight:600">'+ (it.title || '') +'</div>' +
                        '<div style="font-size:12px;color:#666">'+ (it.date || '') + (dStr ? ' • '+dStr : '') +'</div>';
        ul.appendChild(row);
      });
      panel.appendChild(ul);
    }

    function fetchCompact(){
      if (!endpoint2) {
        console.error('ALM Search: endpoint2 is not set');
        return;
      }
      if (aborter) { aborter.abort(); }
      aborter = new AbortController();
      var url = new URL(endpoint2, window.location.origin);
      Object.keys(state2).forEach(function(k){ if (state2[k] !== null && state2[k] !== undefined && state2[k] !== '') url.searchParams.set(k, state2[k]); });
      panel.innerHTML = '<div style="padding:10px;color:#475467;display:flex;align-items:center;gap:8px;">\
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#475467" stroke-width="2">\
          <circle cx="12" cy="12" r="10" opacity="0.25"/>\
          <path d="M22 12a10 10 0 0 1-10 10"/>\
        </svg> Searching…</div>';
      panel.style.display='flex';
      panel.style.flexDirection = 'column';
      fetch(url.toString(), { signal: aborter.signal }).then(function(r){ return r.json(); }).then(renderCompact).catch(function(e){ if (e.name !== 'AbortError'){ console.error('ALM Search fetch error:', e); panel.innerHTML = '<div style="padding:10px;color:#666;">Error loading results.</div>'; panel.style.display='block'; }});
    }

    input2.addEventListener('input', function(){
      var val = input2.value.trim();
      if (val === '') { panel.style.display='none'; return; }
      state2.q = val; state2.page = 1; fetchCompact();
    });
    input2.addEventListener('focus', function(){ if (input2.value.trim() !== '') { fetchCompact(); }});
    document.addEventListener('click', function(ev){ if (!compact.contains(ev.target)) { panel.style.display='none'; }});
  }
});
JS;
        wp_add_inline_script($handle, $inline);
    }

    public function render_shortcode($atts) {
        $endpoint = rest_url('alm/v1/search-lessons');
        ob_start();
        ?>
        <div id="alm-lesson-search" data-endpoint="<?php echo esc_attr($endpoint); ?>">
            <div class="alm-search-loading-initial" style="display: flex; align-items: center; justify-content: center; gap: 12px; padding: 60px 20px; color: #475467; min-height: 200px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#239B90" stroke-width="2" style="animation: alm-spin 1s linear infinite;">
                    <circle cx="12" cy="12" r="10" opacity="0.25"/>
                    <path d="M22 12a10 10 0 0 1-10 10" stroke-dasharray="31.416" stroke-dashoffset="31.416">
                        <animate attributeName="stroke-dasharray" dur="1s" values="0 31.416;15.708 15.708;0 31.416;0 31.416" repeatCount="indefinite"/>
                        <animate attributeName="stroke-dashoffset" dur="1s" values="0;-15.708;-31.416;-31.416" repeatCount="indefinite"/>
                    </path>
                </svg>
                <span style="font-size: 16px; font-weight: 500;">Loading search...</span>
            </div>
            <style>
                @keyframes alm-spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            </style>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_compact($atts) {
        $atts = shortcode_atts(array(
            'view_all_url' => '',
            'placeholder' => 'Search lessons…',
            'max_items' => 10,
        ), $atts, 'alm_lesson_search_compact');
        $endpoint = rest_url('alm/v1/search-lessons');
        ob_start();
        echo '<div id="alm-lesson-search-compact" data-endpoint="' . esc_attr($endpoint) . '" data-view-all="' . esc_attr($atts['view_all_url']) . '" data-max-items="' . intval($atts['max_items']) . '">';
        echo '<input type="search" placeholder="' . esc_attr($atts['placeholder']) . '" style="width:100%;max-width:480px;padding:10px;border:1px solid #ccc;border-radius:8px;" />';
        echo '</div>';
        return ob_get_clean();
    }
}

new ALM_Frontend_Search();


