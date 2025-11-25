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
        
        // Enqueue HLS.js for browsers that don't support HLS natively (Firefox, Chrome)
        wp_enqueue_script('hls.js', 'https://cdn.jsdelivr.net/npm/hls.js@latest', array(), null, true);
        
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

/* Light orange background for favorited lessons */
.alm-lesson-card-course.alm-favorited {
    background-color: #fff5e6 !important;
    border-color: #ffd89b !important;
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
/* Tablet: 2 columns (769px - 1024px) */
@media (max-width: 1024px) and (min-width: 769px) {
    .alm-search-page-container {
        padding: 30px 20px 0 20px;
    }
    
    .alm-search-results-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        padding: 0 0 40px 0;
    }
    
    /* Filters: 2 columns on tablet */
    .alm-search-page-container form > div:nth-child(2) > div:last-child > div {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

/* Mobile: 1 column (≤768px) */
@media (max-width: 768px) {
    .alm-search-page-container {
        padding: 16px 16px 0 16px;
    }
    
    /* Override inline styles for results grid - MUST use !important */
    .alm-search-results-grid,
    div.alm-search-results-grid,
    #alm-lesson-search .alm-search-results-grid,
    .alm-search-page-container .alm-search-results-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 20px !important;
        padding: 0 0 40px 0 !important;
        margin-top: 24px !important;
    }
    
    /* Force override any inline style attribute */
    div[class*="alm-search-results-grid"][style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
    
    .alm-search-page-container input[type="search"] {
        padding: 16px 48px 16px 18px;
        font-size: 15px;
    }
    
    /* Stack search row on mobile */
    .alm-search-page-container form > div:first-child {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
    }
    
    /* Hide search label on mobile or make it smaller */
    .alm-search-page-container form > div:first-child > label {
        margin-bottom: 4px;
    }
    
    /* Make search input full width on mobile */
    .alm-search-page-container form > div:first-child > div {
        width: 100% !important;
        flex: none !important;
    }
    
    /* Stack filter buttons on mobile */
    .alm-search-page-container form > div:first-child > button {
        width: 100% !important;
        margin: 0 !important;
    }
    
    /* Stack filters container - 1 column on mobile - MUST override inline styles */
    .alm-search-page-container form > div:nth-child(2) > div:last-child > div,
    .alm-search-page-container form > div:nth-child(2) div[style*="grid-template-columns"],
    .alm-search-page-container form > div:nth-child(2) > div:last-child > div[style*="grid"] {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 12px !important;
    }
    
    /* Force override any inline style for filters grid */
    .alm-search-page-container form div[style*="repeat(4, 1fr)"],
    .alm-search-page-container form div[style*="repeat(3, 1fr)"] {
        grid-template-columns: 1fr !important;
    }
    
    /* Adjust filters wrapper padding on mobile */
    .alm-search-page-container form > div:nth-child(2) > div:last-child {
        padding: 12px !important;
    }
    
    /* Make filter dropdowns smaller on mobile */
    .alm-search-page-container form select {
        font-size: 14px !important;
        padding: 8px 12px !important;
        padding-right: 36px !important;
    }
    
    /* Adjust results summary on mobile */
    .alm-search-page-container > div:last-child > div:first-child,
    .alm-search-page-container > div:last-child > div[style*="display: flex"][style*="justify-content: space-between"] {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 12px !important;
        font-size: 14px !important;
        padding: 12px 16px !important;
    }
    
    /* Stack results header right container on mobile */
    .alm-search-page-container > div:last-child > div:first-child > div:last-child,
    .alm-search-page-container > div:last-child > div:first-child > div[style*="display: flex"][style*="align-items: center"] {
        width: 100% !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 12px !important;
    }
    
    /* Stack per page container on mobile - put it on new line */
    .alm-search-page-container > div:last-child > div:first-child > div:last-child > div:last-child,
    .alm-search-page-container .alm-per-page-select {
        width: 100% !important;
        margin-top: 8px !important;
    }
    
    /* Make per page container and dropdown full width on mobile */
    .alm-search-page-container > div:last-child > div:first-child > div:last-child > div:last-child {
        width: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
    }
    
    .alm-search-page-container .alm-per-page-select,
    .alm-search-page-container > div:last-child > div:first-child > div:last-child > div:last-child select {
        width: 100% !important;
        min-width: auto !important;
    }
    
    /* Stack pagination buttons on mobile */
    .alm-search-pager {
        flex-direction: column !important;
        padding: 16px !important;
    }
    
    .alm-search-pager-btn {
        width: 100% !important;
        min-width: auto !important;
    }
    
    /* Adjust view toggle buttons on mobile */
    .alm-search-page-container button[data-view] {
        padding: 8px 12px !important;
        font-size: 14px !important;
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
        $favorites_get_all_url_js = esc_js(rest_url('aph/v1/lesson-favorites'));
        $tags_url_js = esc_js(rest_url('alm/v1/tags'));
        $teachers_url_js = esc_js(rest_url('alm/v1/teachers'));
        $keys_url_js = esc_js(rest_url('alm/v1/keys'));
        $is_user_logged_in = is_user_logged_in() ? 'true' : 'false';
        // Note: Level preference URLs removed - we no longer persist level selection
        
        $inline = <<<JS
document.addEventListener('DOMContentLoaded', function() {
  // Favorites cache management (define early for use throughout)
  var favoritesCache = {
    key: 'alm_lesson_favorites_cache',
    timestampKey: 'alm_lesson_favorites_timestamp',
    maxAge: 5 * 60 * 1000, // 5 minutes
    
    get: function() {
      try {
        var cached = sessionStorage.getItem(this.key);
        var timestamp = sessionStorage.getItem(this.timestampKey);
        if (cached && timestamp) {
          var age = Date.now() - parseInt(timestamp, 10);
          if (age < this.maxAge) {
            return JSON.parse(cached);
          }
        }
      } catch (e) {
        // Ignore storage errors
      }
      return null;
    },
    
    set: function(favorites) {
      try {
        sessionStorage.setItem(this.key, JSON.stringify(favorites));
        sessionStorage.setItem(this.timestampKey, Date.now().toString());
      } catch (e) {
        // Ignore storage errors
      }
    },
    
    clear: function() {
      try {
        sessionStorage.removeItem(this.key);
        sessionStorage.removeItem(this.timestampKey);
      } catch (e) {
        // Ignore storage errors
      }
    }
  };
  
  // Fetch favorites EARLY (as soon as page loads, if user is logged in)
  // This starts the request immediately, before any DOM manipulation
  var favoritesPromise = null;
  if ({$is_user_logged_in}) {
    favoritesPromise = fetch('{$favorites_get_all_url_js}', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': '{$rest_nonce_js}'
      },
      credentials: 'same-origin'
    })
    .then(function(response){
      if (response.status === 403) {
        return [];
      }
      if (!response.ok) {
        return [];
      }
      return response.json();
    })
    .then(function(result){
      if (result.success && result.favorites && Array.isArray(result.favorites)) {
        // Cache the favorites
        favoritesCache.set(result.favorites);
        return result.favorites;
      }
      return [];
    })
    .catch(function(error){
      if (error.name !== 'TypeError') {
        console.error('Favorites fetch error:', error);
      }
      return [];
    });
  }
  
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
    var initialTeacher = urlParams.get('teacher') || '';
    var initialKey = urlParams.get('key') || '';
    var initialHasSample = urlParams.get('has_sample') || '';
    var initialSongLesson = urlParams.get('song_lesson') || '';
    
    // Get view preference from localStorage or default to 'grid'
    var initialView = localStorage.getItem('alm_search_view') || 'grid';
    var state = { q: initialQ, page: initialPage, per_page: initialPerPage, lesson_level: initialLevel, tag: initialTag, lesson_style: initialStyle, membership_level: initialMembershipLevel, teacher: initialTeacher, key: initialKey, has_sample: initialHasSample, song_lesson: initialSongLesson, view: initialView };
    
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
      state.teacher = teacherSelect ? teacherSelect.value.trim() : '';
      state.key = keySelect ? keySelect.value.trim() : '';
      state.has_sample = hasSampleSelect ? hasSampleSelect.value.trim() : '';
      state.song_lesson = songLessonSelect ? songLessonSelect.value.trim() : '';
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
    levelSelect.style.width = '100%';
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
      {value: '', text: 'Skill level'},
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
    
    // Second row: Filters with pill label floating above
    var filtersRow = document.createElement('div');
    filtersRow.style.position = 'relative';
    filtersRow.style.width = '100%';
    filtersRow.style.marginBottom = '20px';
    
    // Add "Filters" pill label floating above the div
    var filtersLabel = document.createElement('div');
    filtersLabel.textContent = 'Filters';
    filtersLabel.style.position = 'absolute';
    filtersLabel.style.top = '-12px';
    filtersLabel.style.left = '0';
    filtersLabel.style.fontSize = '11px';
    filtersLabel.style.fontWeight = '600';
    filtersLabel.style.color = '#ffffff';
    filtersLabel.style.background = '#239B90';
    filtersLabel.style.padding = '4px 12px';
    filtersLabel.style.borderRadius = '12px';
    filtersLabel.style.whiteSpace = 'nowrap';
    filtersLabel.style.lineHeight = '1.5';
    filtersLabel.style.zIndex = '10';
    
    // Filter dropdowns container - wrapped in a light background div (full width)
    var filtersWrapper = document.createElement('div');
    filtersWrapper.style.background = '#f8f9fa';
    filtersWrapper.style.border = '1px solid #e9ecef';
    filtersWrapper.style.borderRadius = '8px';
    filtersWrapper.style.padding = '16px';
    filtersWrapper.style.width = '100%';
    filtersWrapper.style.boxSizing = 'border-box';
    
    var filtersContainer = document.createElement('div');
    filtersContainer.style.display = 'grid';
    // Set responsive columns based on window width
    var width = window.innerWidth || document.documentElement.clientWidth;
    if (width <= 768) {
      filtersContainer.style.gridTemplateColumns = '1fr';
    } else if (width <= 1024) {
      filtersContainer.style.gridTemplateColumns = 'repeat(2, 1fr)';
    } else {
    filtersContainer.style.gridTemplateColumns = 'repeat(4, 1fr)';
    }
    filtersContainer.style.gap = '16px';
    filtersContainer.style.alignItems = 'center';
    
    // Update on window resize
    var updateFiltersGrid = function() {
      var w = window.innerWidth || document.documentElement.clientWidth;
      if (w <= 768) {
        filtersContainer.style.gridTemplateColumns = '1fr';
      } else if (w <= 1024) {
        filtersContainer.style.gridTemplateColumns = 'repeat(2, 1fr)';
      } else {
        filtersContainer.style.gridTemplateColumns = 'repeat(4, 1fr)';
      }
    };
    window.addEventListener('resize', updateFiltersGrid);
    
    // Tag dropdown
    var tagSelect = document.createElement('select');
    tagSelect.id = 'alm-lesson-tag-filter';
    tagSelect.style.width = '100%';
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
    
    // Add default "Tag" option
    var tagDefaultOption = document.createElement('option');
    tagDefaultOption.value = '';
    tagDefaultOption.textContent = 'Tag';
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
    styleSelect.style.width = '100%';
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
      {value: '', text: 'Style'},
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
    membershipSelect.style.width = '100%';
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
      {value: '', text: 'Membership'},
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
      teacherSelect.value = '';
      keySelect.value = '';
      hasSampleSelect.value = '';
      songLessonSelect.value = '';
      
      // Reset state
      state.q = '';
      state.lesson_level = '';
      state.tag = '';
      state.lesson_style = '';
      state.membership_level = '';
      state.teacher = '';
      state.key = '';
      state.has_sample = '';
      state.song_lesson = '';
      state.page = 1;
      state.per_page = 24;
      
      // Clear URL parameters
      var url = new URL(window.location.href);
      url.searchParams.delete('q');
      url.searchParams.delete('lesson_level');
      url.searchParams.delete('tag');
      url.searchParams.delete('lesson_style');
      url.searchParams.delete('membership_level');
      url.searchParams.delete('teacher');
      url.searchParams.delete('key');
      url.searchParams.delete('has_sample');
      url.searchParams.delete('song_lesson');
      url.searchParams.delete('page');
      url.searchParams.delete('per_page');
      window.history.pushState({}, '', url.toString());
      
      // Clear results
      results.innerHTML = '';
      // Don't clear pager.innerHTML - just hide it, fetchData will show it if needed
      pager.style.display = 'none';
      
      // Trigger a search to show all lessons (empty search)
      fetchData();
    };
    
    // Add elements to search row (first row)
    searchRow.appendChild(searchLabel);
    searchRow.appendChild(inputWrapper);
    searchRow.appendChild(searchButton);
    searchRow.appendChild(clearButton);
    
    // Teacher dropdown
    var teacherSelect = document.createElement('select');
    teacherSelect.id = 'alm-lesson-teacher-filter';
    teacherSelect.style.width = '100%';
    teacherSelect.style.padding = '10px 16px';
    teacherSelect.style.paddingRight = '40px';
    teacherSelect.style.border = '2px solid #e5e7eb';
    teacherSelect.style.borderRadius = '8px';
    teacherSelect.style.background = '#ffffff';
    teacherSelect.style.color = '#1f2937';
    teacherSelect.style.fontSize = '15px';
    teacherSelect.style.fontWeight = '500';
    teacherSelect.style.cursor = 'pointer';
    teacherSelect.style.outline = 'none';
    teacherSelect.style.transition = 'all 0.2s ease';
    teacherSelect.style.appearance = 'none';
    teacherSelect.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E")';
    teacherSelect.style.backgroundRepeat = 'no-repeat';
    teacherSelect.style.backgroundPosition = 'right 12px center';
    teacherSelect.style.backgroundSize = '16px';
    teacherSelect.style.boxSizing = 'border-box';
    
    teacherSelect.onmouseenter = function(){
      this.style.borderColor = '#3b82f6';
    };
    teacherSelect.onmouseleave = function(){
      if (document.activeElement !== this) {
        this.style.borderColor = '#e5e7eb';
      }
    };
    teacherSelect.onfocus = function(){
      this.style.borderColor = '#3b82f6';
      this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
    };
    teacherSelect.onblur = function(){
      this.style.borderColor = '#e5e7eb';
      this.style.boxShadow = 'none';
    };
    
    // Add default "Teacher" option
    var teacherDefaultOption = document.createElement('option');
    teacherDefaultOption.value = '';
    teacherDefaultOption.textContent = 'Teacher';
    teacherDefaultOption.selected = true;
    teacherSelect.appendChild(teacherDefaultOption);
    
    // Load teachers from API
    var teachersUrl = '{$teachers_url_js}';
    fetch(teachersUrl).then(function(r){ return r.json(); }).then(function(teachers){
      if (teachers && Array.isArray(teachers)) {
        teachers.forEach(function(teacher){
          var option = document.createElement('option');
          option.value = teacher.name;
          option.textContent = teacher.name;
          if (teacher.name === initialTeacher) {
            option.selected = true;
            teacherDefaultOption.selected = false;
          }
          teacherSelect.appendChild(option);
        });
      }
    }).catch(function(err){
      console.error('ALM Search: Failed to load teachers', err);
    });
    
    // Update teacher when changed
    teacherSelect.addEventListener('change', function(){
      state.teacher = teacherSelect.value.trim();
      state.page = 1;
    });
    
    // Key dropdown
    var keySelect = document.createElement('select');
    keySelect.id = 'alm-lesson-key-filter';
    keySelect.style.width = '100%';
    keySelect.style.padding = '10px 16px';
    keySelect.style.paddingRight = '40px';
    keySelect.style.border = '2px solid #e5e7eb';
    keySelect.style.borderRadius = '8px';
    keySelect.style.background = '#ffffff';
    keySelect.style.color = '#1f2937';
    keySelect.style.fontSize = '15px';
    keySelect.style.fontWeight = '500';
    keySelect.style.cursor = 'pointer';
    keySelect.style.outline = 'none';
    keySelect.style.transition = 'all 0.2s ease';
    keySelect.style.appearance = 'none';
    keySelect.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E")';
    keySelect.style.backgroundRepeat = 'no-repeat';
    keySelect.style.backgroundPosition = 'right 12px center';
    keySelect.style.backgroundSize = '16px';
    keySelect.style.boxSizing = 'border-box';
    
    keySelect.onmouseenter = function(){
      this.style.borderColor = '#3b82f6';
    };
    keySelect.onmouseleave = function(){
      if (document.activeElement !== this) {
        this.style.borderColor = '#e5e7eb';
      }
    };
    keySelect.onfocus = function(){
      this.style.borderColor = '#3b82f6';
      this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
    };
    keySelect.onblur = function(){
      this.style.borderColor = '#e5e7eb';
      this.style.boxShadow = 'none';
    };
    
    // Add default "Key" option
    var keyDefaultOption = document.createElement('option');
    keyDefaultOption.value = '';
    keyDefaultOption.textContent = 'Key';
    keyDefaultOption.selected = true;
    keySelect.appendChild(keyDefaultOption);
    
    // Load keys from API
    var keysUrl = '{$keys_url_js}';
    fetch(keysUrl).then(function(r){ return r.json(); }).then(function(keys){
      if (keys && Array.isArray(keys)) {
        keys.forEach(function(key){
          var option = document.createElement('option');
          option.value = key.name;
          option.textContent = key.name;
          if (key.name === initialKey) {
            option.selected = true;
            keyDefaultOption.selected = false;
          }
          keySelect.appendChild(option);
        });
      }
    }).catch(function(err){
      console.error('ALM Search: Failed to load keys', err);
    });
    
    // Update key when changed
    keySelect.addEventListener('change', function(){
      state.key = keySelect.value.trim();
      state.page = 1;
    });
    
    // Sample Video dropdown
    var hasSampleSelect = document.createElement('select');
    hasSampleSelect.id = 'alm-lesson-has-sample-filter';
    hasSampleSelect.style.width = '100%';
    hasSampleSelect.style.padding = '10px 16px';
    hasSampleSelect.style.paddingRight = '40px';
    hasSampleSelect.style.border = '2px solid #e5e7eb';
    hasSampleSelect.style.borderRadius = '8px';
    hasSampleSelect.style.background = '#ffffff';
    hasSampleSelect.style.color = '#1f2937';
    hasSampleSelect.style.fontSize = '15px';
    hasSampleSelect.style.fontWeight = '500';
    hasSampleSelect.style.cursor = 'pointer';
    hasSampleSelect.style.outline = 'none';
    hasSampleSelect.style.transition = 'all 0.2s ease';
    hasSampleSelect.style.appearance = 'none';
    hasSampleSelect.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E")';
    hasSampleSelect.style.backgroundRepeat = 'no-repeat';
    hasSampleSelect.style.backgroundPosition = 'right 12px center';
    hasSampleSelect.style.backgroundSize = '16px';
    hasSampleSelect.style.boxSizing = 'border-box';
    
    hasSampleSelect.onmouseenter = function(){
      this.style.borderColor = '#3b82f6';
    };
    hasSampleSelect.onmouseleave = function(){
      if (document.activeElement !== this) {
        this.style.borderColor = '#e5e7eb';
      }
    };
    hasSampleSelect.onfocus = function(){
      this.style.borderColor = '#3b82f6';
      this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
    };
    hasSampleSelect.onblur = function(){
      this.style.borderColor = '#e5e7eb';
      this.style.boxShadow = 'none';
    };
    
    var hasSampleOptions = [
      {value: '', text: 'Sample video'},
      {value: 'y', text: 'Yes'},
      {value: 'n', text: 'No'}
    ];
    
    hasSampleOptions.forEach(function(opt){
      var option = document.createElement('option');
      option.value = opt.value;
      option.textContent = opt.text;
      if (opt.value === initialHasSample) {
        option.selected = true;
      }
      hasSampleSelect.appendChild(option);
    });
    
    // Update has_sample when changed
    hasSampleSelect.addEventListener('change', function(){
      state.has_sample = hasSampleSelect.value.trim();
      state.page = 1;
    });
    
    // Song Lesson dropdown
    var songLessonSelect = document.createElement('select');
    songLessonSelect.id = 'alm-lesson-song-lesson-filter';
    songLessonSelect.style.width = '100%';
    songLessonSelect.style.padding = '10px 16px';
    songLessonSelect.style.paddingRight = '40px';
    songLessonSelect.style.border = '2px solid #e5e7eb';
    songLessonSelect.style.borderRadius = '8px';
    songLessonSelect.style.background = '#ffffff';
    songLessonSelect.style.color = '#1f2937';
    songLessonSelect.style.fontSize = '15px';
    songLessonSelect.style.fontWeight = '500';
    songLessonSelect.style.cursor = 'pointer';
    songLessonSelect.style.outline = 'none';
    songLessonSelect.style.transition = 'all 0.2s ease';
    songLessonSelect.style.appearance = 'none';
    songLessonSelect.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E")';
    songLessonSelect.style.backgroundRepeat = 'no-repeat';
    songLessonSelect.style.backgroundPosition = 'right 12px center';
    songLessonSelect.style.backgroundSize = '16px';
    songLessonSelect.style.boxSizing = 'border-box';
    
    songLessonSelect.onmouseenter = function(){
      this.style.borderColor = '#3b82f6';
    };
    songLessonSelect.onmouseleave = function(){
      if (document.activeElement !== this) {
        this.style.borderColor = '#e5e7eb';
      }
    };
    songLessonSelect.onfocus = function(){
      this.style.borderColor = '#3b82f6';
      this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
    };
    songLessonSelect.onblur = function(){
      this.style.borderColor = '#e5e7eb';
      this.style.boxShadow = 'none';
    };
    
    var songLessonOptions = [
      {value: '', text: 'Song lesson'},
      {value: 'y', text: 'Yes'},
      {value: 'n', text: 'No'}
    ];
    
    songLessonOptions.forEach(function(opt){
      var option = document.createElement('option');
      option.value = opt.value;
      option.textContent = opt.text;
      if (opt.value === initialSongLesson) {
        option.selected = true;
      }
      songLessonSelect.appendChild(option);
    });
    
    // Update song_lesson when changed
    songLessonSelect.addEventListener('change', function(){
      state.song_lesson = songLessonSelect.value.trim();
      state.page = 1;
    });
    
    // Add elements to filters row (second row)
    filtersRow.appendChild(filtersLabel);
    filtersWrapper.appendChild(filtersContainer);
    filtersContainer.appendChild(levelSelect);
    filtersContainer.appendChild(tagSelect);
    filtersContainer.appendChild(styleSelect);
    filtersContainer.appendChild(membershipSelect);
    filtersContainer.appendChild(teacherSelect);
    filtersContainer.appendChild(keySelect);
    filtersContainer.appendChild(hasSampleSelect);
    filtersContainer.appendChild(songLessonSelect);
    filtersRow.appendChild(filtersWrapper);
    
    form.appendChild(searchRow);
    form.appendChild(filtersRow);
    
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
    pager.id = 'alm-search-pager';
    pager.style.marginTop = '40px';
    pager.style.padding = '20px 40px';
    pager.style.display = 'flex';
    pager.style.flexDirection = 'column';
    pager.style.justifyContent = 'center';
    pager.style.alignItems = 'center';
    pager.style.gap = '12px';
    pager.style.visibility = 'visible';
    
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
    
    // Button container to keep buttons horizontal with page numbers
    var buttonContainer = document.createElement('div');
    buttonContainer.style.display = 'flex';
    buttonContainer.style.justifyContent = 'center';
    buttonContainer.style.alignItems = 'center';
    buttonContainer.style.gap = '8px';
    buttonContainer.style.flexWrap = 'wrap';
    
    // Store reference to button container for updating page numbers
    window.almUpdatePagination = function(totalPages, currentPageNum) {
      // Clear existing page numbers container (keep prev/next buttons)
      // Find and remove any elements between prevBtn and nextBtn
      var children = Array.from(buttonContainer.children);
      children.forEach(function(child) {
        // Remove anything that's not prevBtn or nextBtn
        if (child !== prevBtn && child !== nextBtn) {
          child.remove();
        }
      });
      
      // Add page number buttons only if there's more than 1 page
      if (totalPages > 1) {
        var maxVisible = 7; // Show up to 7 page numbers
        var startPage = 1;
        var endPage = totalPages;
        
        // Calculate which pages to show
        if (totalPages > maxVisible) {
          if (currentPageNum <= 4) {
            // Show first pages
            endPage = maxVisible;
          } else if (currentPageNum >= totalPages - 3) {
            // Show last pages
            startPage = totalPages - maxVisible + 1;
          } else {
            // Show pages around current
            startPage = currentPageNum - 3;
            endPage = currentPageNum + 3;
          }
        }
        
        // Create a temporary container for page numbers
        var pageNumbersContainer = document.createElement('div');
        pageNumbersContainer.style.display = 'contents'; // Makes it invisible in layout
        
        // Add first page if not in range
        if (startPage > 1) {
          var firstBtn = createPageNumberBtn(1, currentPageNum, totalPages);
          pageNumbersContainer.appendChild(firstBtn);
          if (startPage > 2) {
            var ellipsis1 = document.createElement('span');
            ellipsis1.textContent = '...';
            ellipsis1.style.padding = '0 8px';
            ellipsis1.style.color = '#6c757d';
            pageNumbersContainer.appendChild(ellipsis1);
          }
        }
        
        // Add page number buttons in ascending order
        for (var i = startPage; i <= endPage; i++) {
          var pageBtn = createPageNumberBtn(i, currentPageNum, totalPages);
          pageNumbersContainer.appendChild(pageBtn);
        }
        
        // Add last page if not in range
        if (endPage < totalPages) {
          if (endPage < totalPages - 1) {
            var ellipsis2 = document.createElement('span');
            ellipsis2.textContent = '...';
            ellipsis2.style.padding = '0 8px';
            ellipsis2.style.color = '#6c757d';
            pageNumbersContainer.appendChild(ellipsis2);
          }
          var lastBtn = createPageNumberBtn(totalPages, currentPageNum, totalPages);
          pageNumbersContainer.appendChild(lastBtn);
        }
        
        // Insert the container after prevBtn
        buttonContainer.insertBefore(pageNumbersContainer, nextBtn);
      }
    };
    
    function createPageNumberBtn(pageNum, currentPage, totalPages) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'alm-page-number';
      btn.textContent = pageNum;
      btn.style.padding = '10px 16px';
      btn.style.fontSize = '15px';
      btn.style.fontWeight = '600';
      btn.style.border = '2px solid';
      btn.style.borderRadius = '8px';
      btn.style.cursor = 'pointer';
      btn.style.transition = 'all 0.2s ease';
      btn.style.minWidth = '44px';
      
      if (pageNum === currentPage) {
        btn.style.background = '#239B90';
        btn.style.borderColor = '#239B90';
        btn.style.color = '#ffffff';
      } else {
        btn.style.background = '#ffffff';
        btn.style.borderColor = '#e9ecef';
        btn.style.color = '#004555';
      }
      
      btn.onclick = function() {
        if (pageNum !== currentPage) {
          state.page = pageNum;
          var url = new URL(window.location.href);
          url.searchParams.set('page', pageNum);
          window.history.pushState({}, '', url.toString());
          fetchData();
        }
      };
      
      btn.onmouseenter = function() {
        if (pageNum !== currentPage) {
          this.style.background = '#f8f9fa';
          this.style.borderColor = '#239B90';
          this.style.color = '#239B90';
        }
      };
      
      btn.onmouseleave = function() {
        if (pageNum !== currentPage) {
          this.style.background = '#ffffff';
          this.style.borderColor = '#e9ecef';
          this.style.color = '#004555';
        }
      };
      
      return btn;
    }
    
    buttonContainer.appendChild(prevBtn);
    // Page numbers will be inserted here by almUpdatePagination
    buttonContainer.appendChild(nextBtn);
    pager.appendChild(buttonContainer);
    
    // Store current data for view toggle
    var currentData = null;
    
    function renderItems(data){
      // Store data for view toggle
      currentData = data;
      
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
      // Set responsive layout for mobile
      var headerWidth = window.innerWidth || document.documentElement.clientWidth;
      if (headerWidth <= 768) {
        resultsHeader.style.flexDirection = 'column';
        resultsHeader.style.alignItems = 'flex-start';
        resultsHeader.style.padding = '12px 16px';
      } else {
      resultsHeader.style.justifyContent = 'space-between';
      resultsHeader.style.alignItems = 'center';
      }
      resultsHeader.style.gap = '16px';
      
      // Update header on resize
      var updateResultsHeader = function() {
        var w = window.innerWidth || document.documentElement.clientWidth;
        if (w <= 768) {
          resultsHeader.style.flexDirection = 'column';
          resultsHeader.style.alignItems = 'flex-start';
          resultsHeader.style.justifyContent = 'flex-start';
          resultsHeader.style.padding = '12px 16px';
        } else {
          resultsHeader.style.flexDirection = 'row';
          resultsHeader.style.justifyContent = 'space-between';
          resultsHeader.style.alignItems = 'center';
          resultsHeader.style.padding = '16px 20px';
        }
      };
      var resizeTimeout5;
      window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout5);
        resizeTimeout5 = setTimeout(updateResultsHeader, 100);
      });
      
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
      
      // Right side: view toggle and lessons per page dropdown
      if (total > 0) {
        var rightContainer = document.createElement('div');
        rightContainer.style.display = 'flex';
        // Set responsive layout for mobile
        var width = window.innerWidth || document.documentElement.clientWidth;
        if (width <= 768) {
          rightContainer.style.flexDirection = 'column';
          rightContainer.style.alignItems = 'flex-start';
        } else {
        rightContainer.style.alignItems = 'center';
        }
        rightContainer.style.gap = '16px';
        
        // Update on resize
        var updateRightContainer = function() {
          var w = window.innerWidth || document.documentElement.clientWidth;
          if (w <= 768) {
            rightContainer.style.flexDirection = 'column';
            rightContainer.style.alignItems = 'flex-start';
          } else {
            rightContainer.style.flexDirection = 'row';
            rightContainer.style.alignItems = 'center';
          }
        };
        var resizeTimeout2;
        window.addEventListener('resize', function() {
          clearTimeout(resizeTimeout2);
          resizeTimeout2 = setTimeout(updateRightContainer, 100);
        });
        
        // View toggle buttons (List/Grid)
        var viewToggleContainer = document.createElement('div');
        viewToggleContainer.style.display = 'flex';
        viewToggleContainer.style.alignItems = 'center';
        viewToggleContainer.style.gap = '4px';
        viewToggleContainer.style.border = '1px solid #ced4da';
        viewToggleContainer.style.borderRadius = '6px';
        viewToggleContainer.style.overflow = 'hidden';
        viewToggleContainer.style.background = '#ffffff';
        
        // List view button
        var listViewBtn = document.createElement('button');
        listViewBtn.type = 'button';
        listViewBtn.className = 'alm-view-toggle-btn';
        listViewBtn.setAttribute('data-view', 'list');
        listViewBtn.style.padding = '6px 10px';
        listViewBtn.style.border = 'none';
        listViewBtn.style.background = state.view === 'list' ? '#239B90' : 'transparent';
        listViewBtn.style.color = state.view === 'list' ? '#ffffff' : '#6c757d';
        listViewBtn.style.cursor = 'pointer';
        listViewBtn.style.transition = 'all 0.2s ease';
        listViewBtn.style.display = 'flex';
        listViewBtn.style.alignItems = 'center';
        listViewBtn.style.justifyContent = 'center';
        listViewBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>';
        listViewBtn.onclick = function() {
          state.view = 'list';
          localStorage.setItem('alm_search_view', 'list');
          listViewBtn.style.background = '#239B90';
          listViewBtn.style.color = '#ffffff';
          gridViewBtn.style.background = 'transparent';
          gridViewBtn.style.color = '#6c757d';
          if (currentData) {
            renderItems(currentData);
          }
        };
        
        // Grid view button
        var gridViewBtn = document.createElement('button');
        gridViewBtn.type = 'button';
        gridViewBtn.className = 'alm-view-toggle-btn';
        gridViewBtn.setAttribute('data-view', 'grid');
        gridViewBtn.style.padding = '6px 10px';
        gridViewBtn.style.border = 'none';
        gridViewBtn.style.background = state.view === 'grid' ? '#239B90' : 'transparent';
        gridViewBtn.style.color = state.view === 'grid' ? '#ffffff' : '#6c757d';
        gridViewBtn.style.cursor = 'pointer';
        gridViewBtn.style.transition = 'all 0.2s ease';
        gridViewBtn.style.display = 'flex';
        gridViewBtn.style.alignItems = 'center';
        gridViewBtn.style.justifyContent = 'center';
        gridViewBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z" /></svg>';
        gridViewBtn.onclick = function() {
          state.view = 'grid';
          localStorage.setItem('alm_search_view', 'grid');
          gridViewBtn.style.background = '#239B90';
          gridViewBtn.style.color = '#ffffff';
          listViewBtn.style.background = 'transparent';
          listViewBtn.style.color = '#6c757d';
          if (currentData) {
            renderItems(currentData);
          }
        };
        
        viewToggleContainer.appendChild(listViewBtn);
        viewToggleContainer.appendChild(gridViewBtn);
        rightContainer.appendChild(viewToggleContainer);
        
        // Per page dropdown
        var perPageContainer = document.createElement('div');
        perPageContainer.style.display = 'flex';
        // Set responsive layout for mobile
        var width2 = window.innerWidth || document.documentElement.clientWidth;
        if (width2 <= 768) {
          perPageContainer.style.flexDirection = 'column';
          perPageContainer.style.alignItems = 'flex-start';
          perPageContainer.style.width = '100%';
        } else {
        perPageContainer.style.alignItems = 'center';
        }
        perPageContainer.style.gap = '8px';
        
        // Update per page container on resize
        var updatePerPageContainer = function() {
          var w = window.innerWidth || document.documentElement.clientWidth;
          if (w <= 768) {
            perPageContainer.style.flexDirection = 'column';
            perPageContainer.style.alignItems = 'flex-start';
            perPageContainer.style.width = '100%';
          } else {
            perPageContainer.style.flexDirection = 'row';
            perPageContainer.style.alignItems = 'center';
            perPageContainer.style.width = 'auto';
          }
        };
        var resizeTimeout3;
        window.addEventListener('resize', function() {
          clearTimeout(resizeTimeout3);
          resizeTimeout3 = setTimeout(updatePerPageContainer, 100);
        });
        
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
        // Set responsive width for mobile
        var width3 = window.innerWidth || document.documentElement.clientWidth;
        if (width3 <= 768) {
          perPageSelect.style.width = '100%';
          perPageSelect.style.minWidth = 'auto';
        } else {
        perPageSelect.style.minWidth = '70px';
        }
        
        // Update per page select on resize
        var updatePerPageSelect = function() {
          var w = window.innerWidth || document.documentElement.clientWidth;
          if (w <= 768) {
            perPageSelect.style.width = '100%';
            perPageSelect.style.minWidth = 'auto';
          } else {
            perPageSelect.style.width = 'auto';
            perPageSelect.style.minWidth = '70px';
          }
        };
        var resizeTimeout4;
        window.addEventListener('resize', function() {
          clearTimeout(resizeTimeout4);
          resizeTimeout4 = setTimeout(updatePerPageSelect, 100);
        });
        
        // Per page options: 12, 24, 48, 72
        var perPageOptions = [12, 24, 48, 72];
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
        rightContainer.appendChild(perPageContainer);
        resultsHeader.appendChild(rightContainer);
      }
      
      results.appendChild(resultsHeader);
      
      // Create container (grid or list based on view preference)
      var container = document.createElement('div');
      container.className = 'alm-search-results-container';
      
      if (state.view === 'list') {
        // List view - single column
        container.className = 'alm-search-results-list';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '16px';
        container.style.marginTop = '0';
        container.style.padding = '0 0 60px 0';
      } else {
        // Grid view - responsive columns based on window width
        container.className = 'alm-search-results-grid';
        container.style.display = 'grid';
        var width = window.innerWidth || document.documentElement.clientWidth;
        if (width <= 768) {
          container.style.gridTemplateColumns = '1fr';
          container.style.gap = '20px';
        } else if (width <= 1024) {
          container.style.gridTemplateColumns = 'repeat(2, 1fr)';
          container.style.gap = '30px';
        } else {
        container.style.gridTemplateColumns = 'repeat(3, 1fr)';
        container.style.gap = '30px';
        }
        container.style.marginTop = '0';
        container.style.padding = '0 0 60px 0';
        
        // Add resize listener to update grid columns on window resize
        var updateGridColumns = function() {
          if (container.className === 'alm-search-results-grid') {
            var w = window.innerWidth || document.documentElement.clientWidth;
            if (w <= 768) {
              container.style.gridTemplateColumns = '1fr';
              container.style.gap = '20px';
            } else if (w <= 1024) {
              container.style.gridTemplateColumns = 'repeat(2, 1fr)';
              container.style.gap = '30px';
            } else {
              container.style.gridTemplateColumns = 'repeat(3, 1fr)';
              container.style.gap = '30px';
            }
          }
        };
        // Update on resize (debounced)
        var resizeTimeout;
        window.addEventListener('resize', function() {
          clearTimeout(resizeTimeout);
          resizeTimeout = setTimeout(updateGridColumns, 100);
        });
      }
      
      // Store favorite buttons for batch processing
      var favoriteButtons = [];
      var favoriteButtonMap = {};
      
      items.forEach(function(it){
        var card = document.createElement('div');
        card.className = 'alm-lesson-card-course';
        // Will add 'alm-favorited' class later when favorites are loaded
        
        var cardLink = document.createElement(it.permalink ? 'a' : 'div');
        if (it.permalink) {
          cardLink.href = it.permalink;
          cardLink.className = 'alm-lesson-card-link';
          // Prevent navigation when clicking on overlay (buttons handle their own navigation)
          cardLink.onclick = function(e){
            // If click is on a button (not the View Lesson link), prevent card navigation
            var clickedBtn = e.target.closest('button.alm-overlay-btn');
            if (clickedBtn) {
              e.preventDefault();
              e.stopPropagation();
            }
            // If click is on the overlay area (but not on a button or link), prevent navigation
            else if (e.target.closest('.alm-search-card-overlay') && !e.target.closest('a.alm-overlay-btn')) {
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
        var collectionBadge = null;
        if (it.collection_title) {
          collectionBadge = document.createElement('div');
          collectionBadge.className = 'alm-lesson-collection-badge';
          collectionBadge.textContent = it.collection_title;
          cardContent.appendChild(collectionBadge);
        }
        
        // Inner content wrapper with padding
        var innerContent = document.createElement('div');
        innerContent.className = 'alm-lesson-inner-content';
        
        // Title - no padding needed
        var title = document.createElement('h3');
        title.className = 'alm-lesson-title';
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
        
        // Hover overlay with action buttons
        var hoverOverlay = document.createElement('div');
        hoverOverlay.className = 'alm-search-card-overlay';
        
        var overlayButtons = document.createElement('div');
        overlayButtons.className = 'alm-search-overlay-buttons';
        
        // View Lesson button (always show if permalink exists)
        if (it.permalink) {
          var viewLessonBtn = document.createElement('a');
          viewLessonBtn.href = it.permalink;
          viewLessonBtn.className = 'alm-overlay-btn alm-overlay-btn-primary';
          viewLessonBtn.textContent = 'View Lesson';
          viewLessonBtn.onclick = function(e) {
            // Stop propagation to prevent card link navigation, but allow this link to navigate normally
            e.stopPropagation();
            // Don't prevent default - allow normal anchor navigation
          };
          overlayButtons.appendChild(viewLessonBtn);
        }
        
        // View Sample button (only if sample exists)
        if (it.sample_video_url) {
          var viewSampleBtn = document.createElement('button');
          viewSampleBtn.type = 'button';
          viewSampleBtn.className = 'alm-overlay-btn alm-overlay-btn-secondary';
          viewSampleBtn.textContent = 'View Sample';
          viewSampleBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            openSampleModal(it.sample_video_url, it.title || 'Sample Video');
          };
          overlayButtons.appendChild(viewSampleBtn);
        }
        
        // Favorite button (only show if user is logged in)
        if ({$is_user_logged_in}) {
          var favoriteBtn = document.createElement('button');
          favoriteBtn.type = 'button';
          favoriteBtn.className = 'alm-overlay-btn alm-overlay-btn-favorite';
          favoriteBtn.setAttribute('data-title', it.title || '');
          favoriteBtn.setAttribute('data-url', it.permalink || '');
          favoriteBtn.setAttribute('data-description', it.description || '');
          favoriteBtn.setAttribute('aria-label', 'Add to Favorites');
          
          // Star icon SVG (outline)
          var starIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
          starIcon.setAttribute('width', '16');
          starIcon.setAttribute('height', '16');
          starIcon.setAttribute('viewBox', '0 0 24 24');
          starIcon.setAttribute('fill', 'none');
          starIcon.setAttribute('stroke', '#ffffff');
          starIcon.setAttribute('stroke-width', '2');
          starIcon.setAttribute('stroke-linecap', 'round');
          starIcon.setAttribute('stroke-linejoin', 'round');
          var starPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
          starPath.setAttribute('d', 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z');
          starIcon.appendChild(starPath);
          
          var favoriteText = document.createElement('span');
          favoriteText.className = 'alm-favorite-btn-text';
          favoriteText.textContent = 'Save Favorite';
          favoriteBtn.appendChild(starIcon);
          favoriteBtn.appendChild(favoriteText);
          
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
            
            // Get parent card for background styling
            var card = btn.closest('.alm-lesson-card-course');
            
            // Optimistic UI update - change immediately before AJAX
            var icon = btn.querySelector('svg path');
            if (isFavorited) {
              // Removing favorite - change to unfilled immediately
              btn.classList.remove('is-favorited');
              if (icon) {
                icon.setAttribute('fill', 'none');
                icon.setAttribute('stroke', '#ffffff');
              }
              btn.setAttribute('aria-label', 'Add to Favorites');
              var textSpan = btn.querySelector('.alm-favorite-btn-text');
              if (textSpan) {
                textSpan.textContent = 'Save Favorite';
              }
              // Remove favorited border from card
              if (card) {
                card.classList.remove('alm-favorited');
              }
            } else {
              // Adding favorite - change to filled immediately
              btn.classList.add('is-favorited');
              if (icon) {
                icon.setAttribute('fill', '#f04e23');
                icon.setAttribute('stroke', '#f04e23');
              }
              btn.setAttribute('aria-label', 'Remove from Favorites');
              var textSpan = btn.querySelector('.alm-favorite-btn-text');
              if (textSpan) {
                textSpan.textContent = 'Saved';
              }
              // Add favorited border to card
              if (card) {
                card.classList.add('alm-favorited');
              }
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
                  btn.setAttribute('aria-label', 'Remove from Favorites');
                  var textSpan = btn.querySelector('.alm-favorite-btn-text');
                  if (textSpan) {
                    textSpan.textContent = 'Saved';
                  }
                  // Restore favorited border to card
                  if (card) {
                    card.classList.add('alm-favorited');
                  }
                } else {
                  // Was adding, but failed - restore to unfavorited state
                  btn.classList.remove('is-favorited');
                  if (icon) {
                    icon.setAttribute('fill', 'none');
                    icon.setAttribute('stroke', '#ffffff');
                  }
                  btn.setAttribute('aria-label', 'Add to Favorites');
                  var textSpan = btn.querySelector('.alm-favorite-btn-text');
                  if (textSpan) {
                    textSpan.textContent = 'Save Favorite';
                  }
                  // Remove favorited border from card
                  if (card) {
                    card.classList.remove('alm-favorited');
                  }
                }
                alert(result.message || 'Failed to update favorite');
              } else {
                // Success - update cache by fetching fresh favorites
                // Clear cache to force refresh on next render
                favoritesCache.clear();
                // Fetch fresh favorites in background to update cache
                fetch('{$favorites_get_all_url_js}', {
                  method: 'GET',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '{$rest_nonce_js}'
                  },
                  credentials: 'same-origin'
                })
                .then(function(response){
                  if (response.status === 403 || !response.ok) {
                    return { success: false, favorites: [] };
                  }
                  return response.json();
                })
                .then(function(result){
                  if (result.success && result.favorites && Array.isArray(result.favorites)) {
                    favoritesCache.set(result.favorites);
                  }
                })
                .catch(function(error){
                  // Silently fail - cache will refresh on next page load
                });
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
                btn.setAttribute('aria-label', 'Remove from Favorites');
                var textSpan = btn.querySelector('.alm-favorite-btn-text');
                if (textSpan) {
                  textSpan.textContent = 'Saved';
                }
                // Restore favorited border to card
                if (card) {
                  card.classList.add('alm-favorited');
                }
              } else {
                // Was adding, but error - restore to unfavorited state
                btn.classList.remove('is-favorited');
                if (icon) {
                  icon.setAttribute('fill', 'none');
                  icon.setAttribute('stroke', '#ffffff');
                }
                btn.setAttribute('aria-label', 'Add to Favorites');
                var textSpan = btn.querySelector('.alm-favorite-btn-text');
                if (textSpan) {
                  textSpan.textContent = 'Save Favorite';
                }
                // Remove favorited border from card
                if (card) {
                  card.classList.remove('alm-favorited');
                }
              }
              alert('Error updating favorite');
            })
            .finally(function(){
              btn.style.pointerEvents = 'auto';
            });
          };
          
          // Store button for batch favorite checking
          if (it.title) {
            favoriteButtons.push(favoriteBtn);
            favoriteButtonMap[it.title] = favoriteBtn;
          }
          
          overlayButtons.appendChild(favoriteBtn);
        }
        
        hoverOverlay.appendChild(overlayButtons);
        cardContent.appendChild(hoverOverlay);
        
        cardLink.appendChild(cardContent);
        card.appendChild(cardLink);
        
        // Apply list view styles if needed
        if (state.view === 'list') {
          card.style.display = 'flex';
          card.style.flexDirection = 'row';
          card.style.height = 'auto';
          card.style.minHeight = '80px';
          card.style.maxHeight = 'none';
          card.style.alignItems = 'center';
          
          // Make cardLink flex row
          cardLink.style.flexDirection = 'row';
          cardLink.style.height = 'auto';
          cardLink.style.width = '100%';
          
          // Make cardContent flex row
          cardContent.style.flexDirection = 'row';
          cardContent.style.height = 'auto';
          cardContent.style.width = '100%';
          
          // Hide collection badge in list view
          if (collectionBadge) {
            collectionBadge.style.display = 'none';
          }
          
          // Hide description in list view
          if (desc) {
            desc.style.display = 'none';
          }
          
          // Adjust inner content for horizontal layout
          innerContent.style.flexDirection = 'row';
          innerContent.style.padding = '16px 20px';
          innerContent.style.flex = '1';
          innerContent.style.gap = '20px';
          innerContent.style.alignItems = 'center';
          innerContent.style.justifyContent = 'space-between';
          innerContent.style.width = '100%';
          
          // Title should be on left, take more space
          title.style.paddingRight = '0';
          title.style.minHeight = 'auto';
          title.style.flex = '1';
          title.style.marginBottom = '0';
          title.style.marginTop = '0';
          
          // Footer should be on right, aligned with title
          footer.style.marginTop = '0';
          footer.style.flexDirection = 'row';
          footer.style.alignItems = 'center';
          footer.style.gap = '12px';
          footer.style.flexShrink = '0';
          footer.style.marginLeft = 'auto';
          
          // Adjust hover overlay for list view
          if (hoverOverlay) {
            hoverOverlay.style.position = 'relative';
            hoverOverlay.style.opacity = '1';
            hoverOverlay.style.visibility = 'visible';
            hoverOverlay.style.background = 'transparent';
            hoverOverlay.style.pointerEvents = 'auto';
          }
          
          // Adjust overlay buttons for list view
          if (overlayButtons) {
            overlayButtons.style.flexDirection = 'row';
            overlayButtons.style.gap = '8px';
            overlayButtons.style.position = 'relative';
            overlayButtons.style.opacity = '1';
            overlayButtons.style.visibility = 'visible';
          }
          
          // Change tooltip position to right for list view
          var videoBtn = card.querySelector('.alm-overlay-btn-secondary');
          if (videoBtn) {
            videoBtn.setAttribute('data-microtip-position', 'right');
            videoBtn.style.position = 'relative';
            videoBtn.style.zIndex = '101';
          }
          var favBtn = card.querySelector('.alm-overlay-btn-favorite');
          if (favBtn) {
            favBtn.setAttribute('data-microtip-position', 'right');
            favBtn.style.position = 'relative';
            favBtn.style.zIndex = '101';
          }
        }
        
        container.appendChild(card);
      });
      
      // Apply favorites to buttons (use cache first, then update from API)
      if ({$is_user_logged_in} && favoriteButtons.length > 0) {
        // Function to apply favorites to buttons and indicators
        function applyFavoritesToButtons(favorites) {
          if (!favorites || !Array.isArray(favorites)) {
            return;
          }
          
          // Create a Set of favorited titles for fast lookup
          var favoritedTitles = new Set();
          favorites.forEach(function(fav){
            if (fav.title) {
              favoritedTitles.add(fav.title);
            }
          });
          
          // Apply favorite state to all buttons at once
          favoriteButtons.forEach(function(btn){
            var title = btn.getAttribute('data-title');
            if (title && favoritedTitles.has(title)) {
              btn.classList.add('is-favorited');
              var icon = btn.querySelector('svg path');
              if (icon) {
                icon.setAttribute('fill', '#f04e23');
                icon.setAttribute('stroke', '#f04e23');
              }
              btn.setAttribute('aria-label', 'Remove from Favorites');
              
              // Update overlay button text
              var textSpan = btn.querySelector('.alm-favorite-btn-text');
              if (textSpan) {
                textSpan.textContent = 'Saved';
              }
            }
          });
          
          // Apply favorite border to cards
          var cards = document.querySelectorAll('.alm-lesson-card-course');
          cards.forEach(function(card){
            var cardTitle = card.querySelector('.alm-lesson-title');
            if (cardTitle) {
              var title = cardTitle.textContent.trim();
              if (title && favoritedTitles.has(title)) {
                card.classList.add('alm-favorited');
              } else {
                card.classList.remove('alm-favorited');
              }
            }
          });
        }
        
        // First, try to use cached favorites for instant display
        var cachedFavorites = favoritesCache.get();
        if (cachedFavorites) {
          applyFavoritesToButtons(cachedFavorites);
        }
        
        // Then, update from API promise (if it's still pending or use the result)
        if (favoritesPromise) {
          favoritesPromise.then(function(favorites){
            if (favorites && favorites.length > 0) {
              applyFavoritesToButtons(favorites);
            }
          });
        } else {
          // If promise already resolved, fetch fresh data in background
          fetch('{$favorites_get_all_url_js}', {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': '{$rest_nonce_js}'
            },
            credentials: 'same-origin'
          })
          .then(function(response){
            if (response.status === 403) {
              return { success: false, favorites: [] };
            }
            if (!response.ok) {
              return { success: false, favorites: [] };
            }
            return response.json();
          })
          .then(function(result){
            if (result.success && result.favorites && Array.isArray(result.favorites)) {
              // Cache the favorites
              favoritesCache.set(result.favorites);
              // Update buttons
              applyFavoritesToButtons(result.favorites);
            }
          })
          .catch(function(error){
            // Silently handle errors - don't log expected 403s
            if (error.name !== 'TypeError') {
              console.error('Batch favorite check error:', error);
            }
          });
        }
      }
      
      results.appendChild(container);
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
      
      // Check if this is an m3u8 file (HLS playlist)
      var isM3U8 = videoUrl.toLowerCase().indexOf('.m3u8') !== -1 || embedUrl.toLowerCase().indexOf('.m3u8') !== -1;
      
      // Update modal content
      document.getElementById('alm-sample-modal-title').textContent = title;
      var modalBody = document.getElementById('alm-sample-modal-body');
      
      if (isM3U8) {
        // Use HTML5 video element for m3u8 files (HLS)
        var videoId = 'alm-sample-video-' + Date.now();
        modalBody.innerHTML = '<div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; background: #000;">' +
          '<video id="' + videoId + '" controls style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" preload="metadata">' +
          '<source src="' + escapeHtml(videoUrl) + '" type="application/x-mpegURL">' +
          '<p>Your browser does not support the video tag.</p>' +
          '</video>' +
          '</div>';
        
        // Initialize HLS.js for browsers that don't support HLS natively
        var video = document.getElementById(videoId);
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
        modalBody.innerHTML = '<div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; background: #000;">' +
          '<iframe src="' + escapeHtml(embedUrl) + '" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;" allowfullscreen></iframe>' +
          '</div>';
      }
      
      // Show modal
      modal.style.display = 'block';
      document.body.style.overflow = 'hidden';
    }
    
    function closeSampleModal() {
      var modal = document.getElementById('alm-sample-modal');
      if (modal) {
        // Stop any HLS playback
        var video = modal.querySelector('video');
        if (video) {
          if (video.hls && typeof video.hls.destroy === 'function') {
            video.hls.destroy();
          }
          video.pause();
          video.src = '';
        }
        
        modal.style.display = 'none';
        document.body.style.overflow = '';
        // Clear iframe/video to stop playback
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
      // Update teacher from dropdown value
      if (teacherSelect) {
        state.teacher = teacherSelect.value.trim();
      }
      // Update key from dropdown value
      if (keySelect) {
        state.key = keySelect.value.trim();
      }
      // Update has_sample from dropdown value
      if (hasSampleSelect) {
        state.has_sample = hasSampleSelect.value.trim();
      }
      // Update song_lesson from dropdown value
      if (songLessonSelect) {
        state.song_lesson = songLessonSelect.value.trim();
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
          // Include teacher only if it's not empty
          if (k === 'teacher' && (!state[k] || state[k] === '')) {
            return;
          }
          // Include key only if it's not empty
          if (k === 'key' && (!state[k] || state[k] === '')) {
            return;
          }
          // Include has_sample only if it's not empty
          if (k === 'has_sample' && (!state[k] || state[k] === '')) {
            return;
          }
          // Include song_lesson only if it's not empty
          if (k === 'song_lesson' && (!state[k] || state[k] === '')) {
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
      fetch(url.toString()).then(function(r){
        if (!r.ok) {
          throw new Error('HTTP error! status: ' + r.status);
        }
        return r.json();
      }).then(function(data){
        if (!data || typeof data !== 'object') {
          throw new Error('Invalid response format');
        }
        try {
          renderItems(data);
        } catch (renderError) {
          console.error('Error rendering items:', renderError);
          throw new Error('Error rendering results: ' + renderError.message);
        }
        
        var totalPages = data.total_pages || 1;
        var currentPageNum = data.page || state.page;
        var total = data.total || 0;
        // Use state.per_page to reflect what user selected, fallback to data.per_page
        var perPage = state.per_page || data.per_page || 24;
        var start = (currentPageNum - 1) * perPage + 1;
        var end = Math.min(currentPageNum * perPage, total);
        
        // Ensure pagination elements exist before using them
        if (!prevBtn || !nextBtn || !buttonContainer || !pager) {
          console.error('ALM Search: Pagination elements missing, cannot update pagination');
          return;
        }
        
        prevBtn.disabled = currentPageNum <= 1;
        nextBtn.disabled = currentPageNum >= totalPages || !data.items || data.items.length === 0;
        
        // Show pagination only when there are results
        if (total > 0 && data.items && data.items.length > 0) {
          // Ensure pager is visible and properly structured
          pager.style.display = 'flex';
          pager.style.visibility = 'visible';
          
          // Ensure buttonContainer exists and is in the pager
          if (!pager.contains(buttonContainer)) {
            pager.insertBefore(buttonContainer, pager.firstChild);
          }
          
          // Only show pagination controls (prev/next/page numbers) if there's more than 1 page
          if (totalPages > 1) {
            buttonContainer.style.display = 'flex';
            buttonContainer.style.visibility = 'visible';
            
            // Ensure buttons are in buttonContainer
            if (!buttonContainer.contains(prevBtn)) {
              buttonContainer.insertBefore(prevBtn, buttonContainer.firstChild);
            }
            if (!buttonContainer.contains(nextBtn)) {
              buttonContainer.appendChild(nextBtn);
            }
            
            // Update page number buttons
            if (window.almUpdatePagination) {
              window.almUpdatePagination(totalPages, currentPageNum);
            }
          } else {
            // Hide pagination controls when only 1 page, but still show count
            buttonContainer.style.display = 'none';
            
            // Clear any existing page numbers
            if (window.almUpdatePagination) {
              window.almUpdatePagination(1, 1);
            }
          }
          
          // Always show pagination count display below buttons (centered)
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
          pagerCount.style.display = 'block';
          pagerCount.innerHTML = '<span style="color: #239B90; font-weight: 700;">' + total + '</span> <span style="color: #6c757d; font-weight: 500;">lessons found</span> <span style="color: #adb5bd; margin: 0 4px;">•</span> <span style="color: #6c757d; font-weight: 500;">Showing ' + start + '–' + end + '</span>';
        } else {
          // Hide pagination when no results
          pager.style.display = 'none';
        }
        
        // Update button styles based on disabled state (only if buttons exist)
        if (prevBtn && nextBtn) {
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
        }
      }).catch(function(err){ 
        console.error('ALM Search fetch error:', err); 
        console.error('Error details:', {
          message: err.message,
          stack: err.stack,
          url: url.toString()
        });
        results.innerHTML = '<div style="padding: 40px 20px; text-align: center; color: #dc3545;"><p style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Error loading results.</p><p style="font-size: 14px; color: #6c757d;">Please try again or contact support if the problem persists.</p></div>'; 
      });
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
        state.teacher = teacherSelect ? teacherSelect.value.trim() : '';
        state.key = keySelect ? keySelect.value.trim() : '';
        state.has_sample = hasSampleSelect ? hasSampleSelect.value.trim() : '';
        state.song_lesson = songLessonSelect ? songLessonSelect.value.trim() : '';
        state.page = 1;
        fetchData();
      }
    });
    prevBtn.addEventListener('click', function(){ if (state.page > 1){ state.page--; fetchData(); }});
    nextBtn.addEventListener('click', function(){ state.page++; fetchData(); });

    searchContainer.appendChild(form);
    searchContainer.appendChild(results);
    searchContainer.appendChild(pager);
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
    
    // Set teacher dropdown from URL parameter
    if (teacherSelect && initialTeacher) {
      teacherSelect.value = initialTeacher;
      state.teacher = initialTeacher;
    } else if (teacherSelect) {
      teacherSelect.value = '';
      state.teacher = '';
    }
    
    // Set key dropdown from URL parameter
    if (keySelect && initialKey) {
      keySelect.value = initialKey;
      state.key = initialKey;
    } else if (keySelect) {
      keySelect.value = '';
      state.key = '';
    }
    
    // Set has_sample dropdown from URL parameter
    if (hasSampleSelect && initialHasSample) {
      hasSampleSelect.value = initialHasSample;
      state.has_sample = initialHasSample;
    } else if (hasSampleSelect) {
      hasSampleSelect.value = '';
      state.has_sample = '';
    }
    
    // Set song_lesson dropdown from URL parameter
    if (songLessonSelect && initialSongLesson) {
      songLessonSelect.value = initialSongLesson;
      state.song_lesson = initialSongLesson;
    } else if (songLessonSelect) {
      songLessonSelect.value = '';
      state.song_lesson = '';
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


