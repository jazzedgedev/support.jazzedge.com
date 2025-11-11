<?php
/**
 * Frontend Library Display Class
 * 
 * Handles frontend display for Essentials library selection page
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Frontend_Library {
    
    /**
     * Library management instance
     */
    private $library;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->library = new ALM_Essentials_Library();
        add_shortcode('alm_essentials_library', array($this, 'render_library_page'));
    }
    
    /**
     * Render the main library page
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_library_page($atts = array()) {
        // Only show to logged-in users
        if (!is_user_logged_in()) {
            return '<div class="alm-library-login-required">Please log in to access your lesson library.</div>';
        }
        
        $user_id = get_current_user_id();
        
        // Check if user is Essentials member
        if (!$this->is_essentials_member($user_id)) {
            return '<div class="alm-library-access-denied">This feature is available for Essentials members only.</div>';
        }
        
        // Enqueue scripts and styles
        wp_enqueue_script('jquery');
        wp_enqueue_style('alm-essentials-library', ALM_PLUGIN_URL . 'assets/css/essentials-library.css', array(), ALM_VERSION);
        wp_enqueue_script('alm-essentials-library', ALM_PLUGIN_URL . 'assets/js/essentials-library.js', array('jquery'), ALM_VERSION, true);
        
        // Enqueue microtip CSS for tooltips
        wp_enqueue_style('microtip', 'https://unpkg.com/microtip/microtip.css', array(), null);
        
        // Localize script
        wp_localize_script('alm-essentials-library', 'almLibrary', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('alm_library_nonce'),
            'strings' => array(
                'selecting' => 'Adding to library...',
                'success' => 'Lesson added to your library!',
                'error' => 'An error occurred. Please try again.',
                'noSelections' => 'No available selections. Please wait for your next selection period.',
                'alreadyInLibrary' => 'This lesson is already in your library.'
            )
        ));
        
        // Initialize membership if not already initialized (grants first selection)
        $this->library->initialize_membership($user_id);
        
        // Get available selections
        $available = $this->library->get_available_selections($user_id);
        $next_grant = $this->library->get_next_grant_date($user_id);
        
        // Get user's library
        $user_library = $this->library->get_user_library($user_id);
        
        // Get search term
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        
        // Get paged from query var (for pretty permalinks) or GET parameter
        $paged = get_query_var('paged');
        if (empty($paged)) {
            $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        } else {
            $paged = max(1, intval($paged));
        }
        
        $per_page = 21;
        $offset = ($paged - 1) * $per_page;
        
        // Get selectable lessons
        $selectable_lessons = $this->library->get_selectable_lessons($search, $per_page, $offset);
        $total_lessons = $this->library->get_selectable_lessons_count($search);
        $total_pages = ceil($total_lessons / $per_page);
        
        // Get library lesson IDs for filtering
        $library_lesson_ids = array();
        foreach ($user_library as $lib_lesson) {
            $library_lesson_ids[] = $lib_lesson->ID;
        }
        
        ob_start();
        ?>
        <div class="alm-essentials-library-page">
            <div class="alm-library-header">
                <h1 aria-label="My Essentials Library - Lessons you've selected from the Studio library" data-microtip-position="top" role="tooltip">My Essentials Library</h1>
                <div class="alm-library-stats">
                    <div class="alm-stat-item">
                        <span class="alm-stat-label">Available Selections:</span>
                        <span class="alm-stat-value"><?php echo esc_html($available); ?></span>
                    </div>
                    <?php if ($next_grant): ?>
                    <div class="alm-stat-item">
                        <span class="alm-stat-label">Next Selection:</span>
                        <span class="alm-stat-value"><?php echo esc_html(date('F j, Y', strtotime($next_grant))); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="alm-stat-item">
                        <span class="alm-stat-label">Total in Library:</span>
                        <span class="alm-stat-value"><?php echo count($user_library); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($available > 0): ?>
            <div class="alm-library-selection-section">
                <h2>Select a Lesson</h2>
                <p class="alm-library-description">Choose <?php echo $available > 1 ? 'one of your ' . $available . ' available selections' : 'your available selection'; ?> from the Studio library below.</p>
                
                <form method="get" class="alm-library-search-form">
                    <div style="position: relative; flex: 1; min-width: 0;">
                        <input type="search" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Search lessons..." class="alm-library-search-input">
                        <div style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; pointer-events: none; background-image: url('data:image/svg+xml,%3Csvg fill=\'none\' stroke=\'%236b7280\' stroke-width=\'2\' viewBox=\'0 0 24 24\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: center; background-size: contain;"></div>
                    </div>
                    <button type="submit" class="alm-library-search-button">Search</button>
                    <?php if ($search): ?>
                    <a href="?" class="alm-library-clear-search">Clear</a>
                    <?php endif; ?>
                </form>
                
                <?php if (!empty($selectable_lessons)): ?>
                <div class="alm-lessons-grid">
                    <?php foreach ($selectable_lessons as $lesson): 
                        $is_in_library = in_array($lesson->ID, $library_lesson_ids);
                        $lesson_url = '';
                        if ($lesson->post_id) {
                            $lesson_url = get_permalink($lesson->post_id);
                        } elseif ($lesson->slug) {
                            $lesson_url = home_url('/lesson/' . $lesson->slug . '/');
                        }
                    ?>
                    <div class="alm-lesson-card" data-lesson-id="<?php echo esc_attr($lesson->ID); ?>">
                        <div class="alm-lesson-card-content">
                            <div class="alm-lesson-inner-content">
                                <?php if ($is_in_library && !empty($lesson->sample_video_url)): ?>
                                <button type="button" class="alm-sample-icon-btn alm-view-sample" data-video-url="<?php echo esc_attr($lesson->sample_video_url); ?>" data-lesson-title="<?php echo esc_attr(stripslashes($lesson->lesson_title)); ?>" title="View Sample Video">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                                    </svg>
                                </button>
                                <?php endif; ?>
                                
                                <?php if (!empty($lesson->collection_title)): ?>
                                <div class="alm-lesson-collection">
                                    <?php echo esc_html(stripslashes($lesson->collection_title)); ?>
                                </div>
                                <?php endif; ?>
                                
                                <h3 class="alm-lesson-title"><?php echo esc_html(stripslashes($lesson->lesson_title)); ?></h3>
                                
                                <?php if (!empty($lesson->lesson_description)): ?>
                                <div class="alm-lesson-description">
                                    <?php echo esc_html(stripslashes($lesson->lesson_description)); ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="alm-lesson-meta">
                                    <div class="alm-lesson-duration">
                                        <?php if ($lesson->duration > 0): ?>
                                        <span class="dashicons dashicons-clock" style="font-size: 16px; width: 16px; height: 16px;"></span>
                                        <?php echo $this->format_duration($lesson->duration); ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="alm-lesson-level-badge-container">
                                        <?php if ($lesson->lesson_level): 
                                            $level_class = strtolower($lesson->lesson_level);
                                            $level_abbrev = array(
                                                'beginner' => 'Beg',
                                                'intermediate' => 'Int',
                                                'advanced' => 'Adv',
                                                'pro' => 'Pro'
                                            );
                                            $level_display = isset($level_abbrev[$level_class]) ? $level_abbrev[$level_class] : ucfirst($lesson->lesson_level);
                                        ?>
                                        <span class="alm-lesson-level <?php echo esc_attr($level_class); ?>">
                                            <?php echo esc_html($level_display); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="alm-lesson-actions">
                                    <?php if ($is_in_library): ?>
                                        <?php if ($lesson_url): ?>
                                        <a href="<?php echo esc_url($lesson_url); ?>" class="alm-button alm-button-in-library">View Lesson</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if (!empty($lesson->sample_video_url)): ?>
                                        <button type="button" class="alm-button alm-button-secondary alm-view-sample" data-video-url="<?php echo esc_attr($lesson->sample_video_url); ?>" data-lesson-title="<?php echo esc_attr(stripslashes($lesson->lesson_title)); ?>" style="margin-bottom: 12px;">View Sample</button>
                                        <?php endif; ?>
                                        <button class="alm-button alm-button-primary alm-add-to-library" data-lesson-id="<?php echo esc_attr($lesson->ID); ?>">
                                            Add to Library
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($total_pages > 1): ?>
                <div class="alm-library-pagination">
                    <?php
                    // Get the current page permalink
                    $current_page_url = get_permalink();
                    
                    // Check if using pretty permalinks (has /page/ in URL structure)
                    $using_pretty_permalinks = (strpos($current_page_url, '?') === false);
                    
                    // Build query args for pagination (search should always be query param)
                    $query_args = array();
                    if ($search) {
                        $query_args['search'] = $search;
                    }
                    
                    if ($using_pretty_permalinks) {
                        // Pretty permalinks: /my-library/page/%#%/
                        $base_url = trailingslashit($current_page_url) . 'page/%#%/';
                        $format = '';
                    } else {
                        // Plain permalinks: /my-library?paged=%#%
                        $base_url = $current_page_url;
                        $format = '?paged=%#%';
                        if (!empty($query_args)) {
                            $format = '?' . http_build_query($query_args) . '&paged=%#%';
                        }
                    }
                    
                    $pagination_args = array(
                        'base' => $base_url,
                        'format' => $format,
                        'prev_text' => '&laquo; Previous',
                        'next_text' => 'Next &raquo;',
                        'total' => $total_pages,
                        'current' => $paged
                    );
                    
                    // Add search as query arg if using pretty permalinks
                    if ($using_pretty_permalinks && !empty($query_args)) {
                        $pagination_args['add_args'] = $query_args;
                    }
                    
                    echo paginate_links($pagination_args);
                    ?>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="alm-library-no-results">
                    <p>No lessons found<?php echo $search ? ' matching "' . esc_html($search) . '"' : ''; ?>.</p>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="alm-library-no-selections">
                <p>You don't have any available selections at this time.</p>
                <?php if ($next_grant): ?>
                <p>Your next selection will be available on <?php echo esc_html(date('F j, Y', strtotime($next_grant))); ?>.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($user_library)): ?>
            <div class="alm-library-current-section">
                <h2>Your Library (<?php echo count($user_library); ?>)</h2>
                <div class="alm-library-list">
                    <?php foreach ($user_library as $lib_lesson): 
                        $lesson_url = '';
                        if ($lib_lesson->post_id) {
                            $lesson_url = get_permalink($lib_lesson->post_id);
                        } elseif ($lib_lesson->slug) {
                            $lesson_url = home_url('/lesson/' . $lib_lesson->slug . '/');
                        }
                    ?>
                    <div class="alm-library-item">
                        <div class="alm-library-item-content">
                            <h3 class="alm-library-item-title">
                                <?php if ($lesson_url): ?>
                                <a href="<?php echo esc_url($lesson_url); ?>"><?php echo esc_html(stripslashes($lib_lesson->lesson_title)); ?></a>
                                <?php else: ?>
                                <?php echo esc_html(stripslashes($lib_lesson->lesson_title)); ?>
                                <?php endif; ?>
                            </h3>
                            <div class="alm-library-item-meta">
                                <span class="alm-library-item-date">Added: <?php echo esc_html(date('F j, Y', strtotime($lib_lesson->selected_at))); ?></span>
                                <?php if ($lib_lesson->duration > 0): ?>
                                <span class="alm-library-item-duration"><?php echo $this->format_duration($lib_lesson->duration); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Check if user is Essentials member
     * 
     * @param int $user_id User ID
     * @return bool True if Essentials member
     */
    private function is_essentials_member($user_id) {
        // Check if user has Essentials membership via Keap/Infusionsoft
        // Essentials members should have membership level 1
        // They should NOT have Studio (2) or Premier (3) access
        
        // First check if they have Studio or Premier (if so, they're not Essentials)
        $studio_access = false;
        $premier_access = false;
        
        if (function_exists('memb_hasAnyTags')) {
            $studio_access = memb_hasAnyTags([9954,10136,9807,9827,9819,9956,10136]);
            $premier_access = memb_hasAnyTags([9821,9813,10142]);
        }
        
        // If they have Studio or Premier, they're not Essentials
        if ($studio_access || $premier_access) {
            return false;
        }
        
        // Check for Essentials membership SKU
        $essentials_skus = array('JA_YEAR_ESSENTIALS', 'ACADEMY_ESSENTIALS');
        foreach ($essentials_skus as $sku) {
            if (function_exists('memb_hasMembership') && memb_hasMembership($sku) === true) {
                return true;
            }
        }
        
        // Fallback: Check if they have active membership but not Studio/Premier
        // This assumes Essentials is the base paid membership
        if (function_exists('je_return_active_member') && je_return_active_member() == 'true') {
            // If they're an active member but don't have Studio/Premier, assume Essentials
            return true;
        }
        
        return false;
    }
    
    /**
     * Format duration in seconds to human-readable format
     * Matches search page format
     * 
     * @param int $seconds Duration in seconds
     * @return string Formatted duration
     */
    private function format_duration($seconds) {
        $seconds = intval($seconds);
        if (!$seconds || $seconds < 0) {
            return '';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours > 0 && $minutes > 0) {
            return $hours . 'h ' . $minutes . 'm';
        } elseif ($hours > 0) {
            return $hours . 'h';
        } elseif ($minutes > 0) {
            return $minutes . 'm';
        }
        
        return '0m';
    }
}

