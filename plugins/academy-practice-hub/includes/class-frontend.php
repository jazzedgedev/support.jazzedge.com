<?php
/**
 * Frontend display for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Frontend {
    
    private $database;
    
    public function __construct() {
        $this->database = new JPH_Database();
        add_shortcode('jph_dashboard', array($this, 'render_dashboard'));
        add_shortcode('jph_leaderboard', array($this, 'render_leaderboard'));
        add_shortcode('jph_stats_widget', array($this, 'render_stats_widget'));
        add_shortcode('jph_recent_practice_widget', array($this, 'render_recent_practice_widget'));
        add_shortcode('jph_leaderboard_widget', array($this, 'render_leaderboard_widget'));
        add_shortcode('jph_progress_chart_widget', array($this, 'render_progress_chart_widget'));
        add_shortcode('jph_badges_widget', array($this, 'render_badges_widget'));
        add_shortcode('jph_gems_widget', array($this, 'render_gems_widget'));
    }
    
    /**
     * Sanitize user stats for output
     */
    private function sanitize_user_stats($user_stats) {
        if (!is_array($user_stats)) {
            return $user_stats;
        }
        
        $sanitized = array();
        foreach ($user_stats as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = esc_html($value);
            } elseif (is_numeric($value)) {
                $sanitized[$key] = intval($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize practice items for output
     */
    private function sanitize_practice_items($items) {
        if (!is_array($items)) {
            return $items;
        }
        
        $sanitized = array();
        foreach ($items as $item) {
            if (is_array($item)) {
                $sanitized_item = array();
                foreach ($item as $key => $value) {
                    if (is_string($value)) {
                        $sanitized_item[$key] = esc_html($value);
                    } elseif ($key === 'url' && !empty($value)) {
                        $sanitized_item[$key] = esc_url($value);
                    } else {
                        $sanitized_item[$key] = $value;
                    }
                }
                $sanitized[] = $sanitized_item;
            } else {
                $sanitized[] = $item;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize leaderboard data for output
     */
    private function sanitize_leaderboard($leaderboard) {
        if (!is_array($leaderboard)) {
            return $leaderboard;
        }
        
        $sanitized = array();
        foreach ($leaderboard as $user) {
            if (is_array($user)) {
                $sanitized_user = array();
                foreach ($user as $key => $value) {
                    if (is_string($value)) {
                        $sanitized_user[$key] = esc_html($value);
                    } elseif (is_numeric($value)) {
                        $sanitized_user[$key] = intval($value);
                    } else {
                        $sanitized_user[$key] = $value;
                    }
                }
                $sanitized[] = $sanitized_user;
            } else {
                $sanitized[] = $user;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Render practice stats widget
     */
    public function render_stats_widget($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'user_id' => 'current',
            'show' => 'xp,level,streak,badges',
            'style' => 'compact',
            'title' => 'Practice Stats',
            'show_title' => 'true',
            'cache' => 'true',
            'show_practice_hub_link' => 'false'
        ), $atts);
        
        // Determine user ID
        if ($atts['user_id'] === 'current') {
            if (!is_user_logged_in()) {
                return '<div class="jph-stats-widget jph-login-required">Please log in to view practice stats.</div>';
            }
            $user_id = get_current_user_id();
        } else {
            $user_id = intval($atts['user_id']);
            if (!$user_id) {
                return '<div class="jph-stats-widget jph-error">Invalid user ID.</div>';
            }
        }
        
        // Parse show fields
        $show_fields = array_map('trim', explode(',', $atts['show']));
        $allowed_fields = array('xp', 'level', 'streak', 'badges', 'sessions', 'minutes', 'gems', 'hearts');
        $show_fields = array_intersect($show_fields, $allowed_fields);
        
        if (empty($show_fields)) {
            $show_fields = array('xp', 'level', 'streak', 'badges');
        }
        
        // Get user stats
        $gamification = new APH_Gamification();
        $user_stats = $gamification->get_user_stats($user_id);
        $user_stats = $this->sanitize_user_stats($user_stats);
        
        // Get user info
        $user = get_user_by('id', $user_id);
        $display_name = $user_stats['display_name'] ?: $user->display_name ?: $user->user_login;
        
        // Enqueue styles
        wp_enqueue_style('jph-widgets', plugin_dir_url(__FILE__) . '../assets/css/widgets.css', array(), '1.0.0');
        
        ob_start();
        ?>
        <div class="jph-stats-widget jph-stats-widget-<?php echo esc_attr($atts['style']); ?>">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h3 class="jph-widget-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <div class="jph-stats-grid">
                <?php if (in_array('xp', $show_fields)): ?>
                    <div class="jph-stat-item jph-stat-xp">
                        <div class="jph-stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                            </svg>
                        </div>
                        <div class="jph-stat-content">
                            <div class="jph-stat-value"><?php echo number_format($user_stats['total_xp']); ?></div>
                            <div class="jph-stat-label">Total XP</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array('level', $show_fields)): ?>
                    <div class="jph-stat-item jph-stat-level">
                        <div class="jph-stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                            </svg>
                        </div>
                        <div class="jph-stat-content">
                            <div class="jph-stat-value"><?php echo $user_stats['current_level']; ?></div>
                            <div class="jph-stat-label">Level</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array('streak', $show_fields)): ?>
                    <div class="jph-stat-item jph-stat-streak">
                        <div class="jph-stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                            </svg>
                        </div>
                        <div class="jph-stat-content">
                            <div class="jph-stat-value"><?php echo $user_stats['current_streak']; ?></div>
                            <div class="jph-stat-label">Day Streak</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array('badges', $show_fields)): ?>
                    <div class="jph-stat-item jph-stat-badges">
                        <div class="jph-stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path>
                            </svg>
                        </div>
                        <div class="jph-stat-content">
                            <div class="jph-stat-value"><?php echo $user_stats['badges_earned']; ?></div>
                            <div class="jph-stat-label">Badges</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array('sessions', $show_fields)): ?>
                    <div class="jph-stat-item jph-stat-sessions">
                        <div class="jph-stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2 2 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                            </svg>
                        </div>
                        <div class="jph-stat-content">
                            <div class="jph-stat-value"><?php echo $user_stats['total_sessions']; ?></div>
                            <div class="jph-stat-label">Sessions</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array('minutes', $show_fields)): ?>
                    <div class="jph-stat-item jph-stat-minutes">
                        <div class="jph-stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div class="jph-stat-content">
                            <div class="jph-stat-value"><?php echo number_format($user_stats['total_minutes']); ?></div>
                            <div class="jph-stat-label">Minutes</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array('gems', $show_fields)): ?>
                    <div class="jph-stat-item jph-stat-gems">
                        <div class="jph-stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                            </svg>
                        </div>
                        <div class="jph-stat-content">
                            <div class="jph-stat-value"><?php echo $user_stats['gems_balance']; ?></div>
                            <div class="jph-stat-label">Gems</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array('hearts', $show_fields)): ?>
                    <div class="jph-stat-item jph-stat-hearts">
                        <div class="jph-stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                            </svg>
                        </div>
                        <div class="jph-stat-content">
                            <div class="jph-stat-value"><?php echo $user_stats['hearts_count']; ?></div>
                            <div class="jph-stat-label">Hearts</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($atts['style'] === 'detailed'): ?>
                <div class="jph-stats-footer">
                    <div class="jph-user-info">
                        <span class="jph-user-name"><?php echo esc_html($display_name); ?></span>
                        <?php if ($user_stats['last_practice_date']): ?>
                            <span class="jph-last-practice">
                                Last practice: <?php echo human_time_diff(strtotime($user_stats['last_practice_date']), current_time('timestamp')); ?> ago
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($atts['show_practice_hub_link'] === 'true'): ?>
                <div class="jph-widget-footer">
                    <a href="<?php echo esc_url($this->get_practice_hub_url()); ?>" class="jph-practice-hub-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="jph-hub-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                        </svg>
                        Go to Practice Hub
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .jph-stats-widget {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-widget-title {
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        
        .jph-stats-grid {
            display: grid;
            gap: 15px;
        }
        
        .jph-stats-widget-compact .jph-stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        }
        
        .jph-stats-widget-detailed .jph-stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
        
        .jph-stat-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .jph-stat-item:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }
        
        .jph-stat-icon {
            font-size: 24px;
            margin-right: 12px;
            min-width: 24px;
        }
        
        .jph-stat-content {
            flex: 1;
        }
        
        .jph-stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            line-height: 1;
        }
        
        .jph-stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        
        .jph-stats-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e1e5e9;
        }
        
        .jph-user-info {
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        
        .jph-user-name {
            font-weight: 600;
            color: #333;
        }
        
        .jph-last-practice {
            display: block;
            margin-top: 5px;
            font-size: 12px;
        }
        
        .jph-login-required,
        .jph-error {
            text-align: center;
            color: #666;
            font-style: italic;
        }
        
        .jph-widget-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e1e5e9;
            text-align: center;
        }
        
        .jph-practice-hub-link {
            display: inline-flex;
            align-items: center;
            background: #0073aa;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            gap: 8px;
        }
        
        .jph-practice-hub-link:hover {
            background: #005a87;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .jph-hub-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        
        @media (max-width: 768px) {
            .jph-stats-widget-compact .jph-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .jph-stats-widget-detailed .jph-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render recent practice widget
     */
    public function render_recent_practice_widget($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'user_id' => 'current',
            'limit' => '5',
            'show' => 'date,duration,items,sentiment',
            'style' => 'compact',
            'title' => 'Recent Practice',
            'show_title' => 'true',
            'date_format' => 'relative',
            'show_practice_hub_link' => 'false'
        ), $atts);
        
        // Determine user ID
        if ($atts['user_id'] === 'current') {
            if (!is_user_logged_in()) {
                return '<div class="jph-recent-practice-widget jph-login-required">Please log in to view recent practice.</div>';
            }
            $user_id = get_current_user_id();
        } else {
            $user_id = intval($atts['user_id']);
            if (!$user_id) {
                return '<div class="jph-recent-practice-widget jph-error">Invalid user ID.</div>';
            }
        }
        
        // Parse show fields
        $show_fields = array_map('trim', explode(',', $atts['show']));
        $allowed_fields = array('date', 'duration', 'items', 'sentiment', 'notes', 'xp');
        $show_fields = array_intersect($show_fields, $allowed_fields);
        
        if (empty($show_fields)) {
            $show_fields = array('date', 'duration', 'items', 'sentiment');
        }
        
        // Get recent practice sessions
        $limit = max(1, min(20, intval($atts['limit'])));
        $practice_sessions = $this->database->get_practice_sessions($user_id, $limit);
        
        if (empty($practice_sessions)) {
            return '<div class="jph-recent-practice-widget jph-no-data">No recent practice sessions found.</div>';
        }
        
        // Get user info
        $user = get_user_by('id', $user_id);
        $display_name = $user->display_name ?: $user->user_login;
        
        // Enqueue styles
        wp_enqueue_style('jph-widgets', plugin_dir_url(__FILE__) . '../assets/css/widgets.css', array(), '1.0.0');
        
        ob_start();
        ?>
        <div class="jph-recent-practice-widget jph-recent-practice-widget-<?php echo esc_attr($atts['style']); ?>">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h3 class="jph-widget-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <div class="jph-practice-sessions">
                <?php foreach ($practice_sessions as $session): ?>
                    <div class="jph-practice-session">
                        <?php if (in_array('date', $show_fields)): ?>
                            <div class="jph-session-date">
                                <?php if ($atts['date_format'] === 'relative'): ?>
                                    <?php echo human_time_diff(strtotime($session['created_at']), current_time('timestamp')); ?> ago
                                <?php else: ?>
                                    <?php echo date('M j, Y', strtotime($session['created_at'])); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="jph-session-details">
                            <?php if (in_array('duration', $show_fields)): ?>
                                <div class="jph-session-duration">
                                    <span class="jph-duration-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </span>
                                    <span class="jph-duration-value"><?php echo intval($session['duration_minutes']); ?> min</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (in_array('items', $show_fields)): ?>
                                <div class="jph-session-items">
                                    <span class="jph-items-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
                                        </svg>
                                    </span>
                                    <span class="jph-items-value"><?php echo esc_html($session['item_name'] ?: 'Unknown Item'); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (in_array('sentiment', $show_fields)): ?>
                                <div class="jph-session-sentiment">
                                    <span class="jph-sentiment-icon"><?php echo $this->get_sentiment_icon($session['sentiment_score']); ?></span>
                                    <span class="jph-sentiment-value"><?php echo $this->get_sentiment_label($session['sentiment_score']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (in_array('xp', $show_fields)): ?>
                                <div class="jph-session-xp">
                                    <span class="jph-xp-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                                        </svg>
                                    </span>
                                    <span class="jph-xp-value">+<?php echo intval($session['xp_earned']); ?> XP</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (in_array('notes', $show_fields) && !empty($session['notes'])): ?>
                            <div class="jph-session-notes">
                                <span class="jph-notes-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </span>
                                <span class="jph-notes-text"><?php echo esc_html(wp_trim_words($session['notes'], 15)); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($atts['style'] === 'detailed'): ?>
                <div class="jph-recent-practice-footer">
                    <div class="jph-user-info">
                        <span class="jph-user-name"><?php echo esc_html($display_name); ?></span>
                        <span class="jph-session-count"><?php echo count($practice_sessions); ?> recent sessions</span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($atts['show_practice_hub_link'] === 'true'): ?>
                <div class="jph-widget-footer">
                    <a href="<?php echo esc_url($this->get_practice_hub_url()); ?>" class="jph-practice-hub-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="jph-hub-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                        </svg>
                        Go to Practice Hub
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .jph-recent-practice-widget {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-widget-title {
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        
        .jph-practice-sessions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .jph-practice-session {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            transition: all 0.2s ease;
            border-left: 4px solid #0073aa;
        }
        
        .jph-practice-session:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }
        
        .jph-session-date {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .jph-session-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 8px;
        }
        
        .jph-session-duration,
        .jph-session-items,
        .jph-session-sentiment,
        .jph-session-xp {
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .jph-duration-icon,
        .jph-items-icon,
        .jph-sentiment-icon,
        .jph-xp-icon {
            margin-right: 6px;
            font-size: 16px;
        }
        
        .jph-duration-value,
        .jph-items-value,
        .jph-sentiment-value,
        .jph-xp-value {
            font-weight: 500;
            color: #333;
        }
        
        .jph-session-notes {
            display: flex;
            align-items: flex-start;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e1e5e9;
        }
        
        .jph-notes-icon {
            margin-right: 6px;
            font-size: 14px;
            margin-top: 2px;
        }
        
        .jph-notes-text {
            font-size: 13px;
            color: #666;
            font-style: italic;
            line-height: 1.4;
        }
        
        .jph-recent-practice-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e1e5e9;
        }
        
        .jph-user-info {
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        
        .jph-user-name {
            font-weight: 600;
            color: #333;
            margin-right: 10px;
        }
        
        .jph-session-count {
            font-size: 12px;
        }
        
        .jph-login-required,
        .jph-error,
        .jph-no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        .jph-widget-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e1e5e9;
            text-align: center;
        }
        
        .jph-practice-hub-link {
            display: inline-flex;
            align-items: center;
            background: #0073aa;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            gap: 8px;
        }
        
        .jph-practice-hub-link:hover {
            background: #005a87;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .jph-hub-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        
        .jph-recent-practice-widget-compact .jph-session-details {
            gap: 10px;
        }
        
        .jph-recent-practice-widget-compact .jph-session-duration,
        .jph-recent-practice-widget-compact .jph-session-items,
        .jph-recent-practice-widget-compact .jph-session-sentiment,
        .jph-recent-practice-widget-compact .jph-session-xp {
            font-size: 13px;
        }
        
        .jph-recent-practice-widget-detailed .jph-practice-session {
            padding: 18px;
        }
        
        .jph-recent-practice-widget-detailed .jph-session-details {
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .jph-session-details {
                flex-direction: column;
                gap: 8px;
            }
            
            .jph-session-duration,
            .jph-session-items,
            .jph-session-sentiment,
            .jph-session-xp {
                justify-content: flex-start;
            }
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get sentiment icon based on score
     */
    private function get_sentiment_icon($score) {
        $score = intval($score);
        switch ($score) {
            case 5: 
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                </svg>';
            case 4: 
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                </svg>';
            case 3: 
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>';
            case 2: 
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                </svg>';
            case 1: 
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                </svg>';
            default: 
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>';
        }
    }
    
    /**
     * Get sentiment label based on score
     */
    private function get_sentiment_label($score) {
        $score = intval($score);
        switch ($score) {
            case 5: return 'Excellent';
            case 4: return 'Good';
            case 3: return 'Okay';
            case 2: return 'Poor';
            case 1: return 'Terrible';
            default: return 'Okay';
        }
    }
    
    /**
     * Get practice hub URL from settings
     */
    private function get_practice_hub_url() {
        $practice_hub_page_id = get_option('jph_practice_hub_page_id', '');
        
        if ($practice_hub_page_id) {
            $page_url = get_permalink($practice_hub_page_id);
            if ($page_url) {
                return $page_url;
            }
        }
        
        // Fallback to home URL if no page is set
        return home_url('/');
    }
    
    /**
     * Render the student dashboard
     */
    public function render_dashboard($atts) {
        // Only show to logged-in users
        if (!is_user_logged_in()) {
            return '<div class="jph-login-required">Please log in to access your practice dashboard.</div>';
        }
        
        $user_id = get_current_user_id();
        
        // Get user's practice items
        $practice_items = $this->database->get_user_practice_items($user_id);
        $practice_items = $this->sanitize_practice_items($practice_items);
        
        // Get user's lesson favorites for matching URLs
        $lesson_favorites = $this->database->get_lesson_favorites($user_id);
        $lesson_favorites = $this->sanitize_practice_items($lesson_favorites); // Reuse the same sanitization
        
        // Create a lookup array for practice item names to lesson URLs
        $lesson_urls = array();
        foreach ($lesson_favorites as $favorite) {
            if (!empty($favorite['url'])) {
                $lesson_urls[$favorite['title']] = $favorite['url'];
            }
        }
        
        // Get user stats using gamification system
        $gamification = new APH_Gamification();
        $user_stats = $gamification->get_user_stats($user_id);
        
        // Sanitize user stats for output
        $user_stats = $this->sanitize_user_stats($user_stats);
        
        // Enqueue scripts and styles
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        
        ob_start();
        ?>
        <div class="jph-student-dashboard">
            
            <!-- Beta Notice Banner -->
            <div class="jph-beta-notice">
                <div class="beta-notice-content">
                    <div class="beta-notice-text">
                        <span class="beta-icon">🚧</span>
                        <span class="beta-text">This is beta software. Your account is safe, but practice data may occasionally be affected.</span>
                    </div>
                    <button id="jph-feedback-btn-banner" type="button" class="jph-btn jph-btn-primary jph-feedback-btn-banner">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="btn-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                        </svg>
                        Share Feedback
                    </button>
                </div>
            </div>
            
            <!-- Beta Disclaimer Modal -->
            <div id="jph-beta-disclaimer-modal" class="jph-beta-disclaimer-modal" style="display: none;">
                <div class="jph-modal-overlay"></div>
                <div class="jph-modal-content">
                    <div class="jph-modal-header">
                        <h3>🚧 Beta Software Notice</h3>
                        <button class="jph-modal-close" id="jph-beta-disclaimer-close">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </button>
                    </div>
                    <div class="jph-modal-body">
                        <div class="jph-warning-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="48" height="48">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </div>
                        <p><strong>Welcome to the Practice Hub Beta!</strong></p>
                        <p>This is beta software that we're actively developing and improving. While we've done extensive testing, there's still a small possibility of data loss.</p>
                        <p><strong>What to know:</strong></p>
                        <ul>
                            <li>Your account and login information are completely safe</li>
                            <li>Only practice data (sessions, XP, streaks) could potentially be affected</li>
                            <li>We're continuously backing up your data</li>
                            <li>If anything happens, we'll work with you to restore your progress</li>
                        </ul>
                        <p>Thank you for being part of our beta testing community! Your feedback helps us make this tool better for everyone.</p>
                    </div>
                    <div class="jph-modal-footer">
                        <button id="jph-beta-disclaimer-understand" class="jph-btn jph-btn-primary">
                            I Understand - Continue to Practice Hub
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Success/Error Messages -->
            <div id="jph-messages" class="jph-messages" style="display: none;">
                <div class="jph-message-content">
                    <span class="jph-message-close"><i class="fa-solid fa-circle-xmark"></i></span>
                    <div class="jph-message-text"></div>
                </div>
            </div>
            
            <div class="jph-header">
                <div class="header-top">
                    <div class="welcome-title-container">
                        <h2 id="jph-welcome-title">🎹 Your Practice Dashboard</h2>
                        <button id="jph-edit-name-btn" class="jph-edit-name-btn" title="Edit leaderboard name" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </button>
                    </div>
                    <div class="header-actions">
                        <!-- Leaderboard Button -->
                        <button id="jph-leaderboard-btn" type="button" class="jph-btn jph-btn-secondary jph-leaderboard-btn">
                            <span class="btn-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                                </svg>
                            </span>
                            Leaderboard
                        </button>
                        <!-- Stats Explanation Button -->
                        <button id="jph-stats-explanation-btn" type="button" class="jph-btn jph-btn-secondary jph-stats-help-btn">
                            <span class="btn-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                </svg>
                            </span>
                            How do these stats work?
                        </button>
                    </div>
                </div>
                <div class="jph-stats">
                    <div class="stat">
                        <span class="stat-value">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffd700" width="28" height="28">
                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                            </svg>
                            <?php echo esc_html($user_stats['current_level']); ?>
                        </span>
                        <span class="stat-label">Level</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#3b82f6" width="28" height="28">
                                <path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 01.359.852L12.982 9.75h7.268a.75.75 0 01.548 1.262l-10.5 11.25a.75.75 0 01-1.272-.71L10.018 14.25H2.75a.75.75 0 01-.548-1.262l10.5-11.25a.75.75 0 01.913-.143z" clip-rule="evenodd" />
                            </svg>
                            <?php echo esc_html($user_stats['total_xp']); ?>
                        </span>
                        <span class="stat-label">XP</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f97316" width="28" height="28">
                                <path fill-rule="evenodd" d="M15.22 6.268a.75.75 0 01.968-.432l5.942 2.28a.75.75 0 01.431.97l-2.28 5.941a.75.75 0 11-1.4-.537l1.63-4.251-1.086.483a11.2 11.2 0 00-5.45 5.174.75.75 0 01-1.199.12L9 12.31l-6.22 6.22a.75.75 0 11-1.06-1.06l6.75-6.75a.75.75 0 011.06 0l3.606 3.605a12.694 12.694 0 015.68-4.973l1.086-.484-4.251-1.631a.75.75 0 01-.432-.97z" clip-rule="evenodd" />
                            </svg>
                            <?php echo esc_html($user_stats['current_streak']); ?>
                        </span>
                        <span class="stat-label">Streak</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#8b5cf6" width="28" height="28">
                                <path fill-rule="evenodd" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" clip-rule="evenodd" />
                            </svg>
                            <?php echo esc_html($user_stats['gems_balance']); ?>
                        </span>
                        <span class="stat-label">GEMS</span>
                    </div>
                </div>
                
                <!-- Neuroscience Practice Tip -->
                <div class="pro-tip-box">
                    <div class="pro-tip-content">
                        <span class="tip-icon">💡</span>
                        <span class="tip-label">Pro Tip:</span>
                        <span class="tip-text" id="neuro-tip-text">Loading practice insight...</span>
                    </div>
                </div>
            </div>
            
            <!-- Tabbed Navigation -->
            <div class="jph-tabs-container">
                <div class="jph-tabs-nav">
                    <button class="jph-tab-btn active" data-tab="practice-items">
                        <span class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z" />
                            </svg>
                        </span>
                        <span class="tab-title">Practice Items</span>
                    </button>
                    <button class="jph-tab-btn" data-tab="shield-protection">
                        <span class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </span>
                        <span class="tab-title">Shield Protection</span>
                    </button>
                    <button class="jph-tab-btn" data-tab="badges">
                        <span class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </span>
                        <span class="tab-title">Badges</span>
                    </button>
                    <button class="jph-tab-btn" data-tab="analytics">
                        <span class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </span>
                        <span class="tab-title">Analytics</span>
                    </button>
                    <button class="jph-tab-btn" data-tab="history">
                        <span class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <span class="tab-title">History</span>
                    </button>
                </div>
                
                <!-- Tab Content -->
                <div class="jph-tabs-content">
                    
                    <!-- Practice Items Tab -->
                    <div class="jph-tab-pane active" id="practice-items-tab">
                        <!-- Practice Items Section -->
                        <div class="jph-practice-items">
                            <h3>Your Practice Items 
                                <span class="item-count">(<?php echo count($practice_items); ?>/6)</span>
                            </h3>
                            <div class="jph-items-grid" id="sortable-practice-items">
                                <?php 
                                // Always show 6 cards
                                for ($i = 0; $i < 6; $i++): 
                                    if (isset($practice_items[$i])):
                                        $item = $practice_items[$i];
                                        
                                        // Get last practice date for this item
                                        $last_practice = $this->database->get_last_practice_session($user_id, $item['id']);
                                        $last_practice_date = $last_practice ? $last_practice['created_at'] : null;
                                        
                                        // Format the date for display
                                        $practice_date_display = '';
                                        if ($last_practice_date) {
                                            $db_timestamp = strtotime($last_practice_date . ' UTC');
                                            $current_utc_timestamp = current_time('timestamp', true);
                                            $time_ago = human_time_diff($db_timestamp, $current_utc_timestamp);
                                            
                                            // Shorten time units for better space usage
                                            $time_ago = str_replace('hours', 'hrs', $time_ago);
                                            $time_ago = str_replace('minutes', 'min', $time_ago);
                                            $time_ago = str_replace('seconds', 'sec', $time_ago);
                                            $practice_date_display = $time_ago . " ago";
                                        } else {
                                            $practice_date_display = "Never practiced";
                                        }
                                ?>
                                    <div class="jph-item sortable-practice-item" data-item-id="<?php echo esc_attr($item['id']); ?>" draggable="true" style="display:flex; flex-direction:column; min-height:240px;">
                                        <!-- Drag Handle -->
                                        <div class="drag-handle" title="Drag to reorder">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                                <circle cx="4" cy="4" r="1" fill="#666"/>
                                                <circle cx="8" cy="4" r="1" fill="#666"/>
                                                <circle cx="12" cy="4" r="1" fill="#666"/>
                                                <circle cx="4" cy="8" r="1" fill="#666"/>
                                                <circle cx="8" cy="8" r="1" fill="#666"/>
                                                <circle cx="12" cy="8" r="1" fill="#666"/>
                                                <circle cx="4" cy="12" r="1" fill="#666"/>
                                                <circle cx="8" cy="12" r="1" fill="#666"/>
                                                <circle cx="12" cy="12" r="1" fill="#666"/>
                                            </svg>
                                        </div>
                                        <!-- Card Header -->
                                        <div class="item-card-header">
                                            <h4><?php echo esc_html($item['name']); ?></h4>
                                        </div>
                                        
                                        <!-- Last Practiced Date -->
                                        <div class="item-last-practiced" style="margin-top:6px; color:#6c757d; font-size:0.9em;">
                                            Last practiced: <?php echo esc_html($practice_date_display); ?>
                                        </div>
                                        
                                        <!-- Description -->
                                    <div class="item-description" style="min-height:40px;">
                                        <p><?php echo esc_html($item['description']); ?></p>
                                    </div>
                                    
                                    <!-- Action Buttons at bottom -->
                                    <div class="item-actions" style="margin-top:auto; display:flex; flex-direction:column; gap:10px; align-items:center;">
                                            <button type="button" class="jph-log-practice-btn" data-item-id="<?php echo esc_attr($item['id']); ?>">
                                                Log Practice
                                            </button>
                                            <div class="item-controls">
                                                <?php if (isset($lesson_urls[$item['name']])): ?>
                                                <a href="<?php echo esc_url($lesson_urls[$item['name']]); ?>" target="_blank" class="lesson-link-icon" title="View Lesson">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor">
                                                        <path d="M384 64C366.3 64 352 78.3 352 96C352 113.7 366.3 128 384 128L466.7 128L265.3 329.4C252.8 341.9 252.8 362.2 265.3 374.7C277.8 387.2 298.1 387.2 310.6 374.7L512 173.3L512 256C512 273.7 526.3 288 544 288C561.7 288 576 273.7 576 256L576 96C576 78.3 561.7 64 544 64L384 64zM144 160C99.8 160 64 195.8 64 240L64 496C64 540.2 99.8 576 144 576L400 576C444.2 576 480 540.2 480 496L480 416C480 398.3 465.7 384 448 384C430.3 384 416 398.3 416 416L416 496C416 504.8 408.8 512 400 512L144 512C135.2 512 128 504.8 128 496L128 240C128 231.2 135.2 224 144 224L224 224C241.7 224 256 209.7 256 192C256 174.3 241.7 160 224 160L144 160z"/>
                                                    </svg>
                                                </a>
                                                <?php endif; ?>
                                                <button type="button" class="jph-edit-item-btn icon-btn" data-item-id="<?php echo esc_attr($item['id']); ?>" data-name="<?php echo esc_attr($item['name']); ?>" data-category="<?php echo esc_attr($item['category']); ?>" data-description="<?php echo esc_attr($item['description']); ?>" title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor">
                                                        <path d="M100.4 417.2C104.5 402.6 112.2 389.3 123 378.5L304.2 197.3L338.1 163.4C354.7 180 389.4 214.7 442.1 267.4L476 301.3L442.1 335.2L260.9 516.4C250.2 527.1 236.8 534.9 222.2 539L94.4 574.6C86.1 576.9 77.1 574.6 71 568.4C64.9 562.2 62.6 553.3 64.9 545L100.4 417.2zM156 413.5C151.6 418.2 148.4 423.9 146.7 430.1L122.6 517L209.5 492.9C215.9 491.1 221.7 487.8 226.5 483.2L155.9 413.5zM510 267.4C493.4 250.8 458.7 216.1 406 163.4L372 129.5C398.5 103 413.4 88.1 416.9 84.6C430.4 71 448.8 63.4 468 63.4C487.2 63.4 505.6 71 519.1 84.6L554.8 120.3C568.4 133.9 576 152.3 576 171.4C576 190.5 568.4 209 554.8 222.5C551.3 226 536.4 240.9 509.9 267.4z"/>
                                                    </svg>
                                                </button>
                                                <button type="button" class="jph-delete-item-btn icon-btn" data-item-id="<?php echo esc_attr($item['id']); ?>" data-name="<?php echo esc_attr($item['name']); ?>" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor">
                                                        <path d="M232.7 69.9L224 96L128 96C110.3 96 96 110.3 96 128C96 145.7 110.3 160 128 160L512 160C529.7 160 544 145.7 544 128C544 110.3 529.7 96 512 96L416 96L407.3 69.9C402.9 56.8 390.7 48 376.9 48L263.1 48C249.3 48 237.1 56.8 232.7 69.9zM512 208L128 208L149.1 531.1C150.7 556.4 171.7 576 197 576L443 576C468.3 576 489.3 556.4 490.9 531.1L512 208z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="jph-item jph-empty-item sortable-empty-slot">
                                        <div class="drag-handle disabled" title="Empty slot - not draggable">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                                <circle cx="4" cy="4" r="1" fill="#ccc"/>
                                                <circle cx="8" cy="4" r="1" fill="#ccc"/>
                                                <circle cx="12" cy="4" r="1" fill="#ccc"/>
                                                <circle cx="4" cy="8" r="1" fill="#ccc"/>
                                                <circle cx="8" cy="8" r="1" fill="#ccc"/>
                                                <circle cx="12" cy="8" r="1" fill="#ccc"/>
                                                <circle cx="4" cy="12" r="1" fill="#ccc"/>
                                                <circle cx="8" cy="12" r="1" fill="#ccc"/>
                                                <circle cx="12" cy="12" r="1" fill="#ccc"/>
                                            </svg>
                                        </div>
                                        <div class="item-info">
                                            <h4>Empty Slot</h4>
                                            <p>Add a new practice item to get started!</p>
                                        </div>
                                        <div class="item-actions">
                                            <button class="jph-btn jph-btn-primary jph-add-item-btn" type="button">
                                                Add Practice Item
                                            </button>
                                        </div>
                                    </div>
                                <?php 
                                    endif;
                                endfor; 
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shield Protection Tab -->
                    <div class="jph-tab-pane" id="shield-protection-tab">
                        <!-- Shield Protection Section -->
                        <div class="jph-shield-protection">
                            <h3>🛡️ Shield Protection</h3>
                            
                            <!-- Shield Stats and Actions -->
                            <div class="jph-protection-stats">
                                <div class="protection-item">
                                    <span class="protection-icon">🛡️</span>
                                    <span class="protection-label">Shields:</span>
                                    <span class="protection-value" id="shield-count"><?php echo esc_html($user_stats['streak_shield_count'] ?? 0); ?></span>
                                </div>
                                <div class="protection-actions">
                                    <button type="button" class="jph-btn jph-btn-primary" id="purchase-shield-btn">
                                        <span class="btn-icon">🛡️</span>
                                        Purchase Shield (50 💎)
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Shield Information -->
                            <div class="shield-info-section">
                                <div class="shield-info-grid">
                                    <div class="shield-info-item">
                                        <h5>🛡️ What are Shields?</h5>
                                        <p>Shield Protection prevents your streak from breaking if you miss a day of practice. Each shield protects you for one missed day.</p>
                                    </div>
                                    
                                    <div class="shield-info-item">
                                        <h5>⚡ How it Works</h5>
                                        <p>If you miss a practice day, your shield automatically activates to maintain your streak. No action required!</p>
                                    </div>
                                    
                                    <div class="shield-info-item">
                                        <h5>💎 Cost & Limits</h5>
                                        <p>Each shield costs 50 gems. You can hold a maximum of 3 shields at once for optimal protection.</p>
                                    </div>
                                    
                                    <div class="shield-info-item">
                                        <h5>💡 Pro Tips</h5>
                                        <p>Get the most out of your shield protection:</p>
                                        <ul>
                                            <li>Keep 1-2 shields active for peace of mind</li>
                                            <li>Practice regularly to minimize shield usage</li>
                                            <li>Balance shield purchases with other gem priorities</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (($user_stats['current_streak'] ?? 0) === 0 && ($user_stats['longest_streak'] ?? 0) > 0 && ($user_stats['total_sessions'] ?? 0) > 0): ?>
                            <div class="jph-streak-recovery">
                                <h4>🔧 Streak Recovery Available</h4>
                                <p>Your streak is broken. You can repair it using gems!</p>
                                <div class="recovery-options">
                                    <button type="button" class="button button-primary" id="repair-1-day" data-days="1" data-cost="25">
                                        Repair 1 Day (25 💎)
                                    </button>
                                    <button type="button" class="button button-primary" id="repair-3-days" data-days="3" data-cost="75">
                                        Repair 3 Days (75 💎)
                                    </button>
                                    <button type="button" class="button button-primary" id="repair-7-days" data-days="7" data-cost="175">
                                        Repair 7 Days (175 💎)
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Badges Tab -->
                    <div class="jph-tab-pane" id="badges-tab">
                        <!-- Badges Section -->
                        <div class="jph-badges-section">
                            <h2>🏆 Your Badges</h2>
                            <div id="jph-badges-grid" class="jph-badges-grid">
                                <?php
                                $badges = $this->database->get_badges();
                                $user_badges = $this->database->get_user_badges($user_id);
                                $earned_badge_keys = array_column($user_badges, 'badge_key');
                                
                                foreach ($badges as $badge):
                                    $is_earned = in_array($badge['badge_key'], $earned_badge_keys);
                                ?>
                                <div class="jph-badge-card <?php echo $is_earned ? 'earned' : 'locked'; ?>">
                                    <div class="jph-badge-image">
                                        <?php if (!empty($badge['image_url'])): ?>
                                            <img src="<?php echo esc_url($badge['image_url']); ?>" alt="<?php echo esc_attr($badge['name']); ?>">
                                        <?php else: ?>
                                            <div class="jph-badge-placeholder">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="jph-badge-content">
                                        <h4><?php echo esc_html($badge['name']); ?></h4>
                                        <p><?php echo esc_html($badge['description']); ?></p>
                                        <div class="jph-badge-rewards">
                                            <?php if ($badge['xp_reward'] > 0): ?>
                                                <span class="jph-reward">+<?php echo $badge['xp_reward']; ?> XP</span>
                                            <?php endif; ?>
                                            <?php if ($badge['gem_reward'] > 0): ?>
                                                <span class="jph-reward">+<?php echo $badge['gem_reward']; ?> 💎</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Analytics Tab -->
                    <div class="jph-tab-pane" id="analytics-tab">
                        <!-- Analytics Section -->
                        <div class="jph-analytics-section">
                            <div class="analytics-header">
                                <h3><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="analytics-icon"><!--!Font Awesome Pro v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2025 Fonticons, Inc.--><path d="M500 89c13.8-11 16-31.2 5-45s-31.2-16-45-5L319.4 151.5 211.2 70.4c-11.7-8.8-27.8-8.5-39.2 .6L12 199c-13.8 11-16 31.2-5 45s31.2 16 45 5l140.6-112.5 108.2 81.1c11.7 8.8 27.8 8.5 39.2-.6L500 89zM160 256l0 192c0 17.7 14.3 32 32 32s32-14.3 32-32l0-192c0-17.7-14.3-32-32-32s-32 14.3-32 32zM32 352l0 96c0 17.7 14.3 32 32 32s32-14.3 32-32l0-96c0-17.7-14.3-32-32-32s-32 14.3-32 32zm288-64c-17.7 0-32 14.3-32 32l0 128c0 17.7 14.3 32 32 32s32-14.3 32-32l0-128c0-17.7-14.3-32-32-32zm96-32l0 192c0 17.7 14.3 32 32 32s32-14.3 32-32l0-192c0-17.7-14.3-32-32-32s-32 14.3-32 32z"/></svg> Your Practice Analytics</h3>
                                <p>Track your progress and discover insights about your practice habits</p>
                            </div>
                            
                            <div class="analytics-grid">
                                <!-- Practice Time Overview -->
                                <div class="analytics-card practice-time-card">
                                    <div class="card-header">
                                        <h4>⏱️ Practice Time</h4>
                                        <span class="card-subtitle">Minutes practiced</span>
                                    </div>
                                    <div class="time-periods">
                                        <div class="time-period">
                                            <span class="period-label">Last 7 days</span>
                                            <span class="period-value count-up" id="analytics-7-days-minutes" data-target="0" data-start="0">0</span>
                                        </div>
                                        <div class="time-period">
                                            <span class="period-label">Last 30 days</span>
                                            <span class="period-value count-up" id="analytics-30-days-minutes" data-target="0" data-start="0">0</span>
                                        </div>
                                        <div class="time-period">
                                            <span class="period-label">Last 90 days</span>
                                            <span class="period-value count-up" id="analytics-90-days-minutes" data-target="0" data-start="0">0</span>
                                        </div>
                                        <div class="time-period">
                                            <span class="period-label">Last year</span>
                                            <span class="period-value count-up" id="analytics-365-days-minutes" data-target="0" data-start="0">0</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Practice Sessions -->
                                <div class="analytics-card practice-sessions-card">
                                    <div class="card-header">
                                        <h4>🎯 Practice Sessions</h4>
                                        <span class="card-subtitle">Number of sessions</span>
                                    </div>
                                    <div class="sessions-grid">
                                        <div class="session-stat">
                                            <span class="session-value count-up" id="analytics-7-days-sessions" data-target="0" data-start="0">0</span>
                                            <span class="session-label">7 days</span>
                                        </div>
                                        <div class="session-stat">
                                            <span class="session-value count-up" id="analytics-30-days-sessions" data-target="0" data-start="0">0</span>
                                            <span class="session-label">30 days</span>
                                        </div>
                                        <div class="session-stat">
                                            <span class="session-value count-up" id="analytics-90-days-sessions" data-target="0" data-start="0">0</span>
                                            <span class="session-label">90 days</span>
                                        </div>
                                        <div class="session-stat">
                                            <span class="session-value count-up" id="analytics-365-days-sessions" data-target="0" data-start="0">0</span>
                                            <span class="session-label">1 year</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Insights Card -->
                                <div class="analytics-card insights-card">
                                    <div class="card-header">
                                        <h4>🧠 Practice Insights</h4>
                                        <span class="card-subtitle">Your practice patterns</span>
                                    </div>
                                    <div class="insights-list">
                                        <div class="insight-item">
                                            <span class="insight-icon">🎯</span>
                                            <div class="insight-content">
                                                <span class="insight-label">Consistency Score</span>
                                                <span class="insight-value" id="analytics-consistency">-</span>
                                            </div>
                                        </div>
                                        <div class="insight-item">
                                            <span class="insight-icon">📈</span>
                                            <div class="insight-content">
                                                <span class="insight-label">Improvement Rate</span>
                                                <span class="insight-value" id="analytics-improvement-rate">-</span>
                                            </div>
                                        </div>
                                        <div class="insight-item">
                                            <span class="insight-icon">😊</span>
                                            <div class="insight-content">
                                                <span class="insight-label">Mood Rating</span>
                                                <span class="insight-value" id="analytics-sentiment">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Practice Chart -->
                                    <div class="practice-chart-container">
                                        <div class="chart-header">
                                            <h5>📊 Practice Trends</h5>
                                            <div class="chart-period-links">
                                                <a href="#" class="period-link" data-days="7">7 days</a>
                                                <a href="#" class="period-link active" data-days="30">30 days</a>
                                                <a href="#" class="period-link" data-days="90">90 days</a>
                                            </div>
                                        </div>
                                        <canvas id="practice-chart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                                
                                <!-- AI Analysis Card -->
                                <div class="analytics-card ai-analysis-card">
                                    <div class="card-header">
                                        <h4>🤖 AI Practice Analysis</h4>
                                        <span class="card-subtitle">Personalized insights from your practice data</span>
                                    </div>
                                    <div class="ai-analysis-content">
                                        <div class="ai-analysis-text" id="ai-analysis-text">
                                            <div class="loading-spinner">
                                                <div class="spinner"></div>
                                                <span>Analyzing your practice data...</span>
                                            </div>
                                        </div>
                                        <div class="ai-analysis-footer">
                                            <div class="ai-data-period" id="ai-data-period">
                                                <span class="period-label">📊</span>
                                                <span class="period-text">Last 30 days</span>
                                            </div>
                                            <button type="button" class="ai-refresh-btn" id="ai-refresh-btn">
                                                <span class="refresh-icon">🔄</span>
                                                Refresh
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- History Tab -->
                    <div class="jph-tab-pane" id="history-tab">
                        <!-- Full Width Practice History -->
                        <div class="jph-practice-history-full">
                            <div class="practice-history-header-section">
                                <h3>📊 Your Practice History</h3>
                                <div class="practice-history-controls">
                                    <button id="export-history-btn" class="jph-btn jph-btn-secondary">
                                        <span class="btn-icon">📥</span>
                                        Export CSV
                                    </button>
                                </div>
                            </div>
                            <div class="practice-history-header">
                                <div class="practice-history-header-item">Date</div>
                                <div class="practice-history-header-item">Item</div>
                                <div class="practice-history-header-item center">Duration</div>
                                <div class="practice-history-header-item center">How it felt</div>
                                <div class="practice-history-header-item center">Improvement</div>
                                <div class="practice-history-header-item center">Actions</div>
                            </div>
                            <div class="practice-history-list" id="practice-history-list">
                                <div class="loading-message">Loading practice history...</div>
                            </div>
                            <div id="load-more-container" style="text-align: center; margin-top: 20px; display: none;">
                                <button id="load-more-sessions-bottom" class="jph-btn jph-btn-secondary">
                                    <span class="btn-icon">📈</span>
                                    Load More Sessions
                                </button>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            
            
            <!-- Debug Information (for logged in users) -->
            <?php if (current_user_can('manage_options')): ?>
            <div class="jph-debug-section">
                <h2>🔍 Debug Information</h2>
                <div class="jph-debug-content">
                    <p><strong>User ID:</strong> <?php echo $user_id; ?></p>
                    <p><strong>Is Admin:</strong> <?php echo current_user_can('manage_options') ? 'Yes' : 'No'; ?></p>
                    <div class="jph-debug-actions">
                        <button id="view-all-data-btn" class="jph-btn jph-btn-secondary">All Table Data</button>
                        <button id="view-user-stats-btn" class="jph-btn jph-btn-secondary">User Stats</button>
                        <button id="view-user-badges-btn" class="jph-btn jph-btn-secondary">User Badges</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Welcome Modal for First-Time Users -->
        <div id="jph-welcome-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h3>Welcome to Your Practice Hub! 🎹</h3>
                </div>
                <div class="jph-modal-body">
                    <div class="jph-welcome-form">
                        <p>Let's personalize your experience! Choose how you'd like your name to appear on the leaderboard.</p>
                        
                        <div class="jph-form-group">
                            <label for="jph-welcome-display-name-input">Your Leaderboard Name:</label>
                            <input type="text" id="jph-welcome-display-name-input" class="jph-input" placeholder="Enter your leaderboard name" maxlength="100">
                            <small class="jph-help-text">This will be visible to other students on the leaderboard</small>
                        </div>
                        
                        <div class="jph-form-actions">
                            <button id="jph-save-welcome-name" class="jph-btn jph-btn-primary">Let's Go!</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Display Name Settings Modal -->
        <div id="jph-display-name-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h3>Leaderboard Display Name</h3>
                    <span class="jph-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <div class="jph-display-name-form">
                        <p>Set how your name appears on the leaderboard. This name will be visible to other users.</p>
                        
                        <div class="jph-form-group">
                            <label for="jph-display-name-input">Display Name:</label>
                            <input type="text" id="jph-display-name-input" class="jph-input" placeholder="Enter your leaderboard name" maxlength="100">
                            <small class="jph-help-text">Leave empty to use your WordPress display name</small>
                        </div>
                        
                        <div class="jph-form-actions">
                            <button id="jph-save-display-name" class="jph-btn jph-btn-primary">Save Name</button>
                            <button id="jph-cancel-display-name" class="jph-btn jph-btn-secondary">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Explanation Modal -->
        <div id="jph-stats-explanation-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h3>How Your Stats Work</h3>
                    <span class="jph-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <div class="explanation-grid">
                    <div class="explanation-item">
                        <h4>TARGET Level</h4>
                        <p>Your overall progress level. Practice regularly to level up!</p>
                        <ul>
                            <li>Higher levels = more experience</li>
                            <li>Level up by earning XP through practice</li>
                        </ul>
                    </div>
                    <div class="explanation-item">
                        <h4>⭐ XP (Experience Points)</h4>
                        <p>Points earned from practice sessions. More practice = more XP!</p>
                        <ul>
                            <li>Longer practice sessions earn more XP</li>
                            <li>Better performance increases XP earned</li>
                            <li>Noticing improvement gives bonus XP</li>
                        </ul>
                    </div>
                    <div class="explanation-item">
                        <h4>STREAK Streak</h4>
                        <p>Consecutive days of practice. Keep it going!</p>
                        <ul>
                            <li>Practice at least once per day to maintain</li>
                            <li>Missing a day resets your streak</li>
                            <li>Longer streaks show dedication</li>
                        </ul>
                    </div>
                    <div class="explanation-item">
                        <h4>💎 GEMS</h4>
                        <p>Your practice currency! Use gems for special features.</p>
                        <ul>
                            <li>Earn gems through consistent practice</li>
                            <li>Use gems for shield protection</li>
                            <li>Repair broken streaks with gems</li>
                        </ul>
                    </div>
                </div>
                </div>
            </div>
        </div>
        
        <!-- Practice Logging Modal -->
        <div id="jph-log-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content log-modal-compact">
                <div class="log-modal-header-compact">
                    <span class="jph-close"><i class="fa-solid fa-circle-xmark"></i></span>
                    <h3 id="log-practice-item-name">Practice Item</h3>
                </div>
                
                <form id="jph-log-form">
                    <input type="hidden" id="log-item-id" name="practice_item_id">
                    
                    <!-- Duration Section -->
                    <div class="form-group">
                        <label>⏱️ Duration:</label>
                        <div class="duration-options">
                            <div class="duration-quick-buttons">
                                <button type="button" class="duration-btn" data-minutes="5">5 min</button>
                                <button type="button" class="duration-btn" data-minutes="10">10 min</button>
                                <button type="button" class="duration-btn" data-minutes="15">15 min</button>
                                <button type="button" class="duration-btn" data-minutes="30">30 min</button>
                                <button type="button" class="duration-btn" data-minutes="45">45 min</button>
                                <button type="button" class="duration-btn" data-minutes="60">1 hour</button>
                            </div>
                            <div class="duration-custom">
                                <input type="number" name="duration_minutes" min="1" max="300" placeholder="Custom minutes" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sentiment Section -->
                    <div class="form-group">
                        <label>😊 How did it go?</label>
                        <div class="sentiment-options">
                            <div class="sentiment-option" data-score="1">
                                <div class="sentiment-emoji">😞</div>
                                <div class="sentiment-label">Struggled</div>
                            </div>
                            <div class="sentiment-option" data-score="2">
                                <div class="sentiment-emoji">😕</div>
                                <div class="sentiment-label">Difficult</div>
                            </div>
                            <div class="sentiment-option" data-score="3">
                                <div class="sentiment-emoji">😐</div>
                                <div class="sentiment-label">Okay</div>
                            </div>
                            <div class="sentiment-option" data-score="4">
                                <div class="sentiment-emoji">😊</div>
                                <div class="sentiment-label">Good</div>
                            </div>
                            <div class="sentiment-option" data-score="5">
                                <div class="sentiment-emoji">🤩</div>
                                <div class="sentiment-label">Excellent</div>
                            </div>
                        </div>
                        <input type="hidden" name="sentiment_score" required>
                    </div>
                    
                    <!-- Improvement Section -->
                    <div class="form-group">
                        <label>IMPROVE Did you notice improvement?</label>
                        <div class="improvement-toggle">
                            <input type="checkbox" name="improvement_detected" value="1" id="improvement-toggle">
                            <label for="improvement-toggle" class="toggle-slider">
                                <span class="toggle-slider-text">No</span>
                                <span class="toggle-slider-text">Yes</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Notes Section -->
                    <div class="form-group">
                        <label>📝 Notes (optional):</label>
                        <textarea name="notes" placeholder="Any notes about your practice session..."></textarea>
                    </div>
                    
                    <button type="submit" class="log-session-btn-compact">Log Practice</button>
                </form>
            </div>
        </div>
        
        <!-- Feedback Modal -->
        <div id="jph-feedback-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content feedback-modal">
                <div class="jph-modal-header">
                    <h2>💬 Share Your Feedback</h2>
                    <span class="jph-modal-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <p>We'd love to hear your thoughts on the Practice Hub! Share any bugs you've encountered, suggestions for improvements, or general feedback.</p>
                    <div class="feedback-form-container">
                        <?php echo do_shortcode('[fluentform id="46"]'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Shield Purchase Modal -->
        <div id="shield-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>🛡️ Purchase Streak Shield</h2>
                    <span class="jph-modal-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <p>A Streak Shield protects your current streak from being broken if you miss a day of practice.</p>
                    <p><strong>Cost:</strong> 50 💎</p>
                    <p><strong>Current Shields:</strong> <span id="shield-count"><?php echo $user_stats['streak_shield_count'] ?? 0; ?></span></p>
                    <p><strong>Maximum:</strong> 3 shields</p>
                    
                    <div class="jph-modal-footer">
                        <button id="purchase-shield-btn" class="jph-btn jph-btn-primary">Purchase Shield (50 💎)</button>
                        <button type="button" class="jph-btn jph-btn-secondary jph-modal-close">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Add Practice Item Modal -->
        <div id="jph-add-item-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>Add Practice Item</h2>
                    <span class="jph-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <form id="add-practice-item-form">
                        <div class="practice-type-selection">
                            <h4>Choose how to add your practice item:</h4>
                            <div class="practice-type-cards">
                                <div class="practice-type-card" data-type="custom">
                                    <div class="card-icon">✏️</div>
                                    <div class="card-content">
                                        <h4>Custom</h4>
                                        <p>Create your own practice item</p>
                                    </div>
                                    <div class="card-radio">
                                        <input type="radio" name="practice_type" value="custom" checked>
                                    </div>
                                </div>
                                <div class="practice-type-card" data-type="favorite">
                                    <div class="card-icon">⭐</div>
                                    <div class="card-content">
                                        <h4>From Favorites</h4>
                                        <p>Choose from lesson favorites</p>
                                    </div>
                                    <div class="card-radio">
                                        <input type="radio" name="practice_type" value="favorite">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group" id="custom-title-group">
                            <label>Title:</label>
                            <input type="text" name="item_name" placeholder="e.g., Major Scale Practice" required maxlength="50">
                        </div>
                        
                        <div class="form-group" id="favorite-selection-group" style="display: none;">
                            <label>Select Lesson Favorite:</label>
                            <select name="lesson_favorite" id="lesson-favorite-select">
                                <option value="">Loading favorites...</option>
                            </select>
                            <div class="form-help">
                                <small>💡 <strong>No favorites?</strong> Visit lesson pages to add favorites, then return here to create practice items.</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Description:</label>
                            <textarea name="item_description" placeholder="Describe what you'll practice (optional)" maxlength="200"></textarea>
                        </div>
                        
                        <div class="jph-modal-footer">
                            <button type="submit" class="jph-btn jph-btn-primary">Add Practice Item</button>
                            <button type="button" class="jph-btn jph-btn-secondary jph-close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Edit Practice Item Modal -->
        <div id="jph-edit-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>Edit Practice Item</h2>
                    <span class="jph-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <form id="edit-practice-item-form">
                        <input type="hidden" name="item_id" id="edit-item-id">
                        
                        <div class="form-group">
                            <label>Title:</label>
                            <input type="text" name="item_name" id="edit-item-name" required maxlength="50">
                        </div>
                        
                        <div class="form-group">
                            <label>Description:</label>
                            <textarea name="item_description" id="edit-item-description" maxlength="200"></textarea>
                        </div>
                        
                        <div class="jph-modal-footer">
                            <button type="submit" class="jph-btn jph-btn-primary">Update Practice Item</button>
                            <button type="button" class="jph-btn jph-btn-secondary jph-close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Stats Explanation Modal -->
        <div id="stats-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>📊 Stats Explanation</h2>
                    <span class="jph-modal-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <div class="jph-stats-explanation">
                        <h3>Practice Time</h3>
                        <p>Total time spent practicing across all sessions.</p>
                        
                        <h3>Practice Sessions</h3>
                        <p>Number of individual practice sessions logged.</p>
                        
                        <h3>Day Streak</h3>
                        <p>Consecutive days of practice. Miss a day and your streak resets!</p>
                        
                        <h3>Gems</h3>
                        <p>Virtual currency earned through practice. Use gems to purchase streak shields.</p>
                        
                        <h3>Level</h3>
                        <p>Your practice level based on total XP earned.</p>
                        
                        <h3>Badges</h3>
                        <p>Achievements earned for reaching practice milestones.</p>
                    </div>
                    
                    <div class="jph-modal-footer">
                        <button type="button" class="jph-btn jph-btn-secondary jph-modal-close">Close</button>
                    </div>
            </div>
        </div>
        
        </div>
        
        <style>
        .jph-student-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fffe 0%, #f0f8f7 100%);
            min-height: 100vh;
        }

        /* Beta Notice Banner */
        .jph-beta-notice {
            background: linear-gradient(135deg, #e0f2fe 0%, #bfdbfe 100%);
            border: 1px solid #60a5fa;
            border-radius: 12px;
            margin-bottom: 20px;
            padding: 16px 20px;
            box-shadow: 0 2px 4px rgba(96, 165, 250, 0.1);
        }

        .beta-notice-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .beta-notice-text {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .beta-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        .beta-text {
            color: #1e40af;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
        }

        .jph-feedback-btn-banner {
            background: #3b82f6;
            color: white;
            border: 1px solid #2563eb;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-direction: row;
        }

        .jph-feedback-btn-banner:hover {
            background: #2563eb;
            border-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }

        .jph-feedback-btn-banner .btn-icon {
            width: 16px;
            height: 16px;
            display: inline-block;
            flex-shrink: 0;
        }

        /* Responsive design for beta notice */
        @media (max-width: 768px) {
            .beta-notice-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .jph-feedback-btn-banner {
                align-self: stretch;
                text-align: center;
            }
        }

        /* Beta Disclaimer Modal Styles */
        .jph-beta-disclaimer-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .jph-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
        }

        .jph-modal-content {
            position: relative;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .jph-modal-header {
            padding: 24px 24px 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .jph-modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
        }

        .jph-modal-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            color: #6b7280;
            transition: all 0.2s ease;
        }

        .jph-modal-close:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .jph-modal-body {
            padding: 24px;
        }

        .jph-warning-icon {
            text-align: center;
            margin-bottom: 20px;
            color: #f59e0b;
        }

        .jph-modal-body p {
            margin: 0 0 16px 0;
            line-height: 1.6;
            color: #374151;
        }

        .jph-modal-body p:last-child {
            margin-bottom: 0;
        }

        .jph-modal-body ul {
            margin: 16px 0;
            padding-left: 20px;
        }

        .jph-modal-body li {
            margin-bottom: 8px;
            line-height: 1.5;
            color: #374151;
        }

        .jph-modal-footer {
            padding: 0 24px 24px 24px;
            text-align: center;
        }

        .jph-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .jph-btn-primary {
            background: #3b82f6;
            color: white;
        }

        .jph-btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .jph-btn-primary:active {
            transform: translateY(0);
        }
        
        /* Feedback Modal Styles */
        .feedback-modal {
            max-width: 700px;
        }
        
        .feedback-form-container {
            margin-top: 20px;
        }
        
        .feedback-form-container .ff-form {
            margin: 0;
        }
        
        .feedback-form-container .ff-form-wrapper {
            padding: 0;
        }
        
        .jph-header {
            margin-bottom: 40px;
            padding: 40px 30px;
            background: linear-gradient(135deg, #004555 0%, #002A34 100%);
            color: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 69, 85, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .welcome-title-container {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }
        
        .header-top h2 {
            margin: 0;
            font-size: 1.8em;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .jph-edit-name-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 6px;
            padding: 6px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0.7;
        }
        
        .jph-edit-name-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            opacity: 1;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .jph-leaderboard-btn {
            width: 200px;
            justify-content: flex-start;
            padding: 8px 24px;
        }
        
        .jph-stats-help-btn {
            background: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            backdrop-filter: blur(10px);
            padding: 14px 16px !important;
            font-size: 14px !important;
            white-space: nowrap;
        }
        
        .jph-stats-help-btn:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            transform: translateY(-1px);
        }
        
        /* Modern Pro Tip Styling */
        .pro-tip-box {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-left: 4px solid #f39c12;
            border-radius: 12px;
            padding: 20px 24px;
            margin: 30px 0 20px 0;
            box-shadow: 0 4px 20px rgba(243, 156, 18, 0.15);
            border: 1px solid rgba(243, 156, 18, 0.2);
        }
        
        .pro-tip-content {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #8b4513;
        }
        
        .tip-icon {
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .tip-label {
            font-weight: 700;
            font-size: 14px;
            color: #d35400;
        }
        
        .tip-text {
            font-size: 15px;
            font-weight: 500;
            line-height: 1.5;
            letter-spacing: 0.25px;
        }
        
        .jph-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat {
            background: white;
            padding: 30px 25px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 69, 85, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .stat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #239B90, #459E90);
        }
        
        .stat:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 69, 85, 0.2);
            border-color: #239B90;
        }
        
        .stat-value {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 2.5em;
            font-weight: 800;
            color: #004555;
            margin-bottom: 8px;
        }
        
        .stat-value svg {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
        }
        
        .stat-label {
            font-size: 1.1em;
            font-weight: 600;
            color: #666;
        }
        
        .jph-btn-secondary {
            background: linear-gradient(135deg, #f8fffe 0%, #e8f5f4 100%);
            color: #004555;
            border: 1px solid #00A8A8;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .jph-btn-secondary:hover {
            background: linear-gradient(135deg, #e8f5f4 0%, #d1e7e4 100%);
            color: #004555;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,168,168,0.2);
        }
        
        .btn-icon {
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-icon svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
        }
        
        .jph-badges-section {
            background: white;
            border-radius: 16px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 8px 25px rgba(0, 69, 85, 0.1);
            padding: 30px;
            margin: 30px 0;
        }
        
        .jph-badges-section h3 {
            margin-bottom: 20px;
            color: #004555;
            font-size: 1.4em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge-count {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #004555;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
        }
        
        .jph-badges-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        
        @media (max-width: 1200px) {
            .jph-badges-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .jph-badges-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .jph-badges-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .jph-badge-card {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 2px solid #e8f5f4;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .jph-badge-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 69, 85, 0.15);
            border-color: #004555;
        }
        
        .jph-badge-card.earned {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border-color: #ffd700;
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.2);
        }
        
        .jph-badge-card.earned:hover {
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.3);
        }
        
        .jph-badge-image {
            width: 64px;
            height: 64px;
            margin: 0 auto 15px;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            transition: all 0.3s ease;
        }
        
        .jph-badge-card.earned .jph-badge-image {
            background: transparent;
        }
        
        .jph-badge-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .jph-badge-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
            color: #6c757d;
        }
        
        .jph-badge-name {
            font-weight: 600;
            color: #004555;
            font-size: 0.9em;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        
        .jph-badge-description {
            font-size: 0.8em;
            color: #666;
            line-height: 1.3;
            margin-bottom: 10px;
        }
        
        .jph-badge-category {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jph-badge-category-achievement { background: #e3f2fd; color: #1976d2; }
        .jph-badge-category-milestone { background: #f3e5f5; color: #7b1fa2; }
        .jph-badge-category-special { background: #fff3e0; color: #f57c00; }
        .jph-badge-category-streak { background: #ffebee; color: #d32f2f; }
        .jph-badge-category-level { background: #e8f5e8; color: #388e3c; }
        .jph-badge-category-practice { background: #e0f2f1; color: #00796b; }
        .jph-badge-category-improvement { background: #fce4ec; color: #c2185b; }
        
        .jph-badge-rewards {
            font-size: 0.8em;
            color: #666;
            margin-top: 8px;
            font-weight: 500;
        }
        
        .jph-badge-earned-date {
            font-size: 0.7em;
            color: #28a745;
            font-weight: 600;
            margin-top: 8px;
        }
        
        .no-badges-message {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-size: 1.1em;
        }
        
        .no-badges-message .emoji {
            font-size: 2em;
            display: block;
            margin-bottom: 10px;
        }
        
        .jph-shield-protection {
            background: white;
            border-radius: 16px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 8px 25px rgba(0, 69, 85, 0.1);
            padding: 30px;
            margin: 30px 0;
        }
        
        .shield-accordion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            cursor: pointer;
            border-bottom: 1px solid #dee2e6;
            transition: background 0.2s ease;
        }
        
        .shield-accordion-header:hover {
            background: linear-gradient(135deg, #e9ecef, #dee2e6);
        }
        
        .shield-accordion-header h3 {
            color: #333;
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .shield-toggle-icon {
            font-size: 16px;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .shield-toggle-icon i {
            font-size: inherit;
        }
        
        .shield-accordion-content {
            padding: 20px;
            background: white;
        }
        
        .shield-explanation-section {
            margin-top: 25px;
            padding: 20px;
            background: #f8fffe;
            border-radius: 12px;
            border-left: 4px solid #00A8A8;
        }
        
        .shield-explanation-section h4 {
            margin: 0 0 10px 0;
            color: #004555;
            font-size: 18px;
            font-weight: 600;
        }
        
        .shield-explanation-section p {
            margin: 0 0 20px 0;
            color: #555;
            line-height: 1.5;
        }
        
        .shield-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .shield-info-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .shield-info-item h5 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }
        
        .shield-info-item p {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 13px;
            line-height: 1.4;
        }
        
        .shield-info-item ul {
            margin: 0;
            padding-left: 15px;
            color: #666;
            font-size: 12px;
        }
        
        .shield-info-item li {
            margin-bottom: 3px;
            line-height: 1.3;
        }
        
        @media (max-width: 768px) {
            .shield-info-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .jph-protection-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 25px;
            background: linear-gradient(145deg, #f8f9fa, #e9ecef);
            border-radius: 16px;
            border: 2px solid #e3f2fd;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        
        .protection-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            border: 2px solid #e1f5fe;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .protection-icon {
            font-size: 24px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        .protection-label {
            font-weight: 600;
            color: #2A3940;
            font-size: 16px;
        }
        
        .protection-value {
            background: linear-gradient(45deg, #239B90, #004555);
            color: white;
            padding: 8px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 3px 8px rgba(35, 155, 144, 0.4);
        }
        
        .protection-label {
            font-weight: 500;
            color: #666;
        }
        
        .protection-value {
            background: #007cba;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .protection-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .protection-actions .button {
            padding: 12px 20px !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            border: none !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1) !important;
            box-shadow: 0 3px 8px rgba(0,0,0,0.15) !important;
            opacity: 1 !important;
        }
        
        .protection-actions .button-secondary {
            background: linear-gradient(135deg, #FF6B35, #f04e23) !important;
            color: white !important;
            border: none !important;
        }
        
        .protection-actions .button-secondary:hover {
            background: linear-gradient(135deg, #e05a2b, #d63e1c) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4) !important;
        }
        
        .protection-actions .button-secondary:disabled {
            background: #ccc !important;
            color: #666 !important;
            cursor: not-allowed !important;
            transform: none !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        /* Practice Items Styles */
        .jph-practice-items {
            background: white;
            border-radius: 16px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 8px 25px rgba(0, 69, 85, 0.1);
            padding: 30px;
            margin: 30px 0;
        }
        
        .jph-practice-items h3 {
            margin-bottom: 20px;
            color: #004555;
            font-size: 1.4em;
        }
        
        .jph-items-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 1024px) {
            .jph-items-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .jph-items-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .jph-item-card {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 2px solid #e8f5f4;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .jph-item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 69, 85, 0.15);
            border-color: #004555;
        }
        
        .jph-item-name {
            font-size: 1.1em;
            font-weight: 600;
            color: #004555;
            margin-bottom: 15px;
        }
        
        .log-practice-btn {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .log-practice-btn:hover {
            background: linear-gradient(135deg, #006666, #008888);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 69, 85, 0.3);
        }
        
        /* Practice History Styles */
        .jph-practice-history-full {
            background: white;
            border-radius: 16px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 8px 25px rgba(0, 69, 85, 0.1);
            padding: 30px;
            margin: 30px 0;
        }
        
        .practice-history-header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .practice-history-header-section h3 {
            margin: 0;
            color: #004555;
            font-size: 1.4em;
        }
        
        .practice-history-controls {
            display: flex;
            gap: 10px;
        }
        
        .practice-history-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 2px solid #e8f5f4;
            font-weight: 600;
            color: #004555;
        }
        .practice-history-header .center { text-align: center; }
        
        .practice-history-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
            align-items: center;
        }
        .practice-history-item .center { text-align: center; }
        
        .practice-history-notes {
            grid-column: 1 / -1;
            padding: 8px 0 0 0;
            font-size: 0.9em;
            color: #666;
            font-style: italic;
            border-top: 1px solid #f8f8f8;
            margin-top: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .practice-history-item:hover {
            background: #f8f9fa;
        }
        
        .practice-history-item-content {
            font-size: 0.9em;
            color: #333;
        }
        
        .delete-session-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .delete-session-btn:hover {
            background: #c82333;
        }
        
        .loading-message {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-size: 1.1em;
        }
        
        .no-sessions {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-size: 1.1em;
        }
        
        .error-message {
            text-align: center;
            padding: 40px 20px;
            color: #dc3545;
            font-size: 1.1em;
        }
        
        /* Modal Styles */
        .jph-modal {
            display: none;
            position: fixed;
            z-index: 10001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        
        .jph-modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .jph-modal-header {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
            padding: 20px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .jph-modal-header h2 {
            margin: 0;
            font-size: 1.4em;
        }
        
        .jph-modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .jph-modal-close:hover {
            background: none;
        }
        
        .jph-modal-body {
            padding: 30px;
        }
        
        .jph-form-group {
            margin-bottom: 20px;
        }
        
        .jph-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #004555;
        }
        
        .jph-form-group input,
        .jph-form-group select,
        .jph-form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e8f5f4;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .jph-form-group input:focus,
        .jph-form-group select:focus,
        .jph-form-group textarea:focus {
            outline: none;
            border-color: #004555;
            box-shadow: 0 0 0 3px rgba(0, 69, 85, 0.1);
        }
        
        .jph-modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e8f5f4;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        /* Stats Explanation Modal Styles */
        #jph-stats-explanation-modal .jph-modal-content {
            max-width: 900px;
            padding: 30px;
        }
        
        #jph-stats-explanation-modal .jph-modal-header {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
            padding: 20px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -30px -30px 0 -30px;
        }
        
        #jph-stats-explanation-modal .jph-modal-header h3 {
            margin: 0;
            color: white;
            font-size: 1.4em;
        }
        
        #jph-stats-explanation-modal .jph-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        #jph-stats-explanation-modal .jph-close:hover {
            background: none;
        }
        
        #jph-stats-explanation-modal .jph-modal-body {
            padding: 30px 0;
        }
        
        /* Display Name Modal Styles */
        #jph-display-name-modal .jph-modal-content {
            max-width: 500px;
            padding: 30px;
        }
        
        #jph-display-name-modal .jph-modal-header {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
            padding: 20px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -30px -30px 0 -30px;
        }
        
        #jph-display-name-modal .jph-modal-header h3 {
            margin: 0;
            color: white;
            font-size: 1.4em;
        }
        
        #jph-display-name-modal .jph-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        #jph-display-name-modal .jph-close:hover {
            background: none;
        }
        
        #jph-ranking-explanation-modal .jph-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.4em;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        #jph-ranking-explanation-modal .jph-close:hover {
            background: none;
        }
        
        /* Ranking Explanation Modal Styles */
        #jph-ranking-explanation-modal .jph-modal-content {
            max-width: 700px;
            padding: 30px;
        }
        
        #jph-ranking-explanation-modal .jph-modal-header {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
            padding: 20px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -30px -30px 0 -30px;
        }
        
        #jph-ranking-explanation-modal .jph-modal-header h3 {
            margin: 0;
            font-size: 1.3em;
            font-weight: 600;
        }
        
        #jph-ranking-explanation-modal .jph-modal-body {
            padding: 30px 0;
        }
        
        
        
        #jph-display-name-modal .jph-modal-body {
            padding: 30px 0;
        }
        
        .jph-display-name-form p {
            margin-bottom: 25px;
            color: #666;
            line-height: 1.5;
        }
        
        .jph-form-group {
            margin-bottom: 25px;
        }
        
        .jph-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .jph-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        
        .jph-input:focus {
            outline: none;
            border-color: #004555;
        }
        
        .jph-help-text {
            display: block;
            margin-top: 5px;
            font-size: 14px;
            color: #666;
        }
        
        .jph-form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }
        
        /* Practice Type Selection Styles */
        .practice-type-selection {
            margin-bottom: 25px;
        }
        
        .practice-type-selection h4 {
            margin-bottom: 15px;
            color: #2A3940;
            font-size: 1.1em;
        }
        
        .practice-type-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .practice-type-card {
            border: 2px solid #e8f5f4;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            position: relative;
        }
        
        .practice-type-card:hover {
            border-color: #239B90;
            box-shadow: 0 4px 12px rgba(35, 155, 144, 0.15);
        }
        
        .practice-type-card.selected {
            border-color: #239B90;
            background: linear-gradient(145deg, #f0fdfc, #e6fffa);
            box-shadow: 0 4px 12px rgba(35, 155, 144, 0.2);
        }
        
        .card-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .card-content h4 {
            margin: 0 0 5px 0;
            color: #2A3940;
            font-size: 1.1em;
        }
        
        .card-content p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }
        
        .card-radio {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .card-radio input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: #239B90;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2A3940;
        }
        
        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e8f5f4;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input[type="text"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #239B90;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-help {
            margin-top: 8px;
        }
        
        .form-help small {
            color: #666;
            font-style: italic;
        }
        
        /* Message Notifications */
        .jph-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
        }
        
        .jph-message-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .jph-message-error {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        
        /* Compact Log Practice Modal Styles */
        .log-modal-compact {
            max-width: 500px;
            padding: 0;
        }
        
        .log-modal-header-compact {
            background: transparent;
            padding: 20px 24px 16px 24px;
            border-radius: 0;
            position: relative;
            margin: 0;
        }
        
        .log-modal-header-compact .jph-close {
            position: absolute;
            top: 16px;
            right: 20px;
            color: #666;
            font-size: 18px;
            opacity: 0.8;
            transition: all 0.2s ease;
        }
        
        .log-modal-header-compact .jph-close:hover {
            opacity: 1;
            color: #333;
        }
        
        .log-modal-header-compact h3 {
            margin: 0 40px 0 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            line-height: 1.3;
        }
        
        .log-modal-subtitle {
            margin: 4px 0 0 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }
        
        #jph-log-form {
            padding: 20px 24px 24px 24px;
        }
        
        /* Compact Duration Buttons */
        .duration-quick-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        
        .duration-btn {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-align: center;
            color: #495057;
        }
        
        .duration-btn:hover {
            background: #e9ecef;
            border-color: #dee2e6;
        }
        
        .duration-btn.active {
            background: #207bbd;
            color: white;
            border-color: #1a5f95;
        }
        
        .duration-custom input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            background: #f8f9fa;
        }
        
        /* Compact Sentiment Options - 5 in a row */
        .sentiment-options {
            display: flex;
            gap: 8px;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        
        .sentiment-option {
            flex: 1;
            text-align: center;
            padding: 12px 8px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #f8f9fa;
        }
        
        .sentiment-option:hover {
            background: #e9ecef;
            border-color: #dee2e6;
        }
        
        .sentiment-option.active {
            background: #207bbd;
            border-color: #1a5f95;
            color: white;
        }
        
        .sentiment-emoji {
            font-size: 20px;
            margin-bottom: 4px;
        }
        
        .sentiment-label {
            font-size: 11px;
            font-weight: 500;
            color: inherit;
        }
        
        .sentiment-option.active .sentiment-label {
            color: white;
        }
        
        /* Compact Form Groups */
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .form-group input[type="checkbox"] {
            margin-right: 8px;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 13px;
            background: #f8f9fa;
            resize: vertical;
            min-height: 60px;
        }
        
        /* Improvement Toggle */
        .improvement-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .toggle-slider {
            position: relative;
            display: inline-block;
            width: 140px;
            height: 44px;
            background: linear-gradient(135deg, #e9ecef 0%, #f1f3f4 100%);
            border-radius: 22px;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
            border: 2px solid #dee2e6;
        }
        
        .toggle-slider:before {
            content: '';
            position: absolute;
            top: 4px;
            left: 4px;
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 50%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 3px 8px rgba(0,0,0,0.15), 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .toggle-slider-text {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            font-weight: 600;
            color: #6c757d;
            transition: color 0.3s ease;
        }
        
        .toggle-slider-text:first-child {
            left: 16px;
        }
        
        .toggle-slider-text:last-child {
            right: 16px;
        }
        
        #improvement-toggle {
            display: none;
        }
        
        #improvement-toggle:checked + .toggle-slider {
            background: linear-gradient(135deg, #239B90 0%, #1e8279 100%);
            border-color: #239B90;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1), 0 0 0 3px rgba(35, 155, 144, 0.1);
        }
        
        #improvement-toggle:checked + .toggle-slider:before {
            transform: translateX(96px);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        #improvement-toggle:checked + .toggle-slider .toggle-slider-text:last-child {
            color: white;
        }
        
        #improvement-toggle + .toggle-slider .toggle-slider-text:first-child {
            color: white;
        }
        
        #improvement-toggle:checked + .toggle-slider:before {
            transform: translateX(80px);
        }
        
        #improvement-toggle:checked + .toggle-slider .toggle-slider-text:first-child {
            color: white;
        }
        
        #improvement-toggle:checked + .toggle-slider .toggle-slider-text:last-child {
            color: white;
        }
        
        /* Compact Log Button */
        .log-session-btn-compact {
            width: 100%;
            background: #f04e23 !important;
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
        }
        
        .log-session-btn-compact:hover {
            background: #e0451f !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(240, 78, 35, 0.3);
        }
        
        .log-session-btn-compact:active {
            transform: translateY(0);
        }
        
        .explanation-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin: 20px 0;
        }
        
        .explanation-item {
            background: #f8fffe;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #00A8A8;
        }
        
        .explanation-item h4 {
            margin: 0 0 10px 0;
            color: #004555;
            font-size: 18px;
            font-weight: 600;
        }
        
        .explanation-item p {
            margin: 0 0 15px 0;
            color: #555;
            line-height: 1.5;
        }
        
        .explanation-item ul {
            margin: 0;
            padding-left: 20px;
            list-style-position: outside;
            color: #666;
        }
        
        .explanation-item li {
            margin-bottom: 5px;
            line-height: 1.4;
        }
        
        @media (max-width: 768px) {
            .explanation-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .jph-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        
        .jph-btn-primary {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
        }
        
        .jph-btn-primary:hover {
            background: linear-gradient(135deg, #006666, #008888);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 69, 85, 0.3);
        }
        
        .jph-btn-secondary {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            color: #004555;
            border: 2px solid #e8f5f4;
        }
        
        .jph-btn-secondary:hover {
            background: linear-gradient(135deg, #e9ecef, #dee2e6);
            transform: translateY(-2px);
        }
        
        /* Log Practice button (orange brand style) */
        .jph-log-practice-btn {
            background: #f04e23 !important;
            color: #ffffff;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-block;
        }
        .jph-log-practice-btn:hover {
            background: #e0451f !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(240, 78, 35, 0.3);
        }
        .jph-log-practice-btn:active {
            transform: translateY(0);
        }

        /* Practice Items Drag and Drop Styles */
        .jph-item {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 2px solid #e8f5f4;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            cursor: move;
        }
        
        .jph-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 69, 85, 0.15);
            border-color: #004555;
        }
        
        .jph-item.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
            z-index: 1000;
        }
        
        .jph-item.drag-over {
            border-color: #004555;
            background: #f0f8ff;
        }
        
        .drag-handle {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: grab;
            opacity: 0.6;
            transition: opacity 0.3s ease;
        }
        
        .drag-handle:hover {
            opacity: 1;
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }
        
        .drag-handle.disabled {
            cursor: default;
            opacity: 0.3;
        }
        
        .item-card-header h4 {
            margin: 0 0 10px 0;
            font-size: 1.2em;
            font-weight: 600;
            color: #004555;
        }
        
        .item-last-practiced {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 10px;
        }
        
        .item-description p {
            margin: 0 0 15px 0;
            font-size: 0.9em;
            color: #555;
            line-height: 1.4;
        }
        
        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }
        
        .item-controls {
            display: flex;
            gap: 5px;
        }
        
        .lesson-link-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: none;
            color: #666;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 5px;
        }
        
        .lesson-link-icon:hover {
            background: rgba(0, 0, 0, 0.05);
            color: #333;
        }
        
        .lesson-link-icon svg {
            width: 16px;
            height: 16px;
        }
        
        .icon-btn {
            background: none;
            border: none;
            padding: 5px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s ease;
            color: #666;
        }
        
        .icon-btn:hover {
            background: #f0f0f0;
            color: #004555;
        }
        
        .jph-empty-item {
            background: linear-gradient(135deg, #f8f9fa, #f0f0f0);
            border: 2px dashed #ccc;
            opacity: 0.7;
        }
        
        .jph-empty-item:hover {
            opacity: 1;
            border-color: #004555;
        }
        
        .item-count {
            font-size: 0.8em;
            color: #666;
            font-weight: normal;
        }
        
        /* Delete Session Button */
        .jph-delete-session-btn {
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
            color: #dc3545;
        }
        
        .jph-delete-session-btn:hover {
            background: #ffebee;
            transform: scale(1.1);
            color: #c82333;
        }
        
        .jph-delete-session-btn i {
            font-size: 16px;
        }
        </style>
        <style>
        /* Improvement pills */
        .jph-pill { display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; }
        .jph-pill-progress { background:#e6f5f3; color:#1e8279; border:1px solid #bfe7e2; }
        .jph-pill-logged { background:#f3f4f6; color:#4b5563; border:1px solid #e5e7eb; }
        
        /* Analytics Section Styles */
        .jph-analytics-section {
            margin: 40px 0;
            padding: 40px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 69, 85, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .analytics-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .analytics-header h3 {
            color: #0f172a;
            font-size: 2.2em;
            margin-bottom: 12px;
            font-weight: 800;
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .analytics-icon {
            width: 32px;
            height: 32px;
            fill: #0f172a;
            flex-shrink: 0;
        }
        
        /* Counting Animation Styles */
        .count-up {
            transition: all 0.3s ease-in-out;
        }
        
        .count-up.animate {
            animation: countUp 1.5s ease-out forwards;
        }
        
        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Toast Notifications */
        .jph-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            min-width: 300px;
            max-width: 400px;
            animation: slideInRight 0.3s ease-out;
        }
        
        .jph-toast-success {
            border-left: 4px solid #28a745;
        }
        
        .jph-toast-error {
            border-left: 4px solid #dc3545;
        }
        
        .jph-toast-info {
            border-left: 4px solid #007cba;
        }
        
        .toast-content {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .toast-message {
            color: #333;
            font-size: 14px;
            font-weight: 500;
        }
        
        .toast-close {
            background: none;
            border: none;
            font-size: 18px;
            color: #666;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
            line-height: 1;
        }
        
        .toast-close:hover {
            color: #333;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        
        .analytics-header p {
            color: #64748b;
            font-size: 1.2em;
            margin: 0;
            font-weight: 500;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .analytics-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0, 69, 85, 0.06);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.8);
            position: relative;
            overflow: hidden;
        }
        
        .analytics-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #10b981, #f59e0b);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .analytics-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0, 69, 85, 0.12);
        }
        
        .analytics-card:hover::before {
            opacity: 1;
        }
        
        .card-header {
            margin-bottom: 24px;
            text-align: center;
        }
        
        .card-header h4 {
            color: #0f172a;
            font-size: 1.4em;
            margin: 0 0 8px 0;
            font-weight: 700;
        }
        
        .card-subtitle {
            color: #64748b;
            font-size: 0.95em;
            font-weight: 500;
        }
        
        /* Practice Time Card */
        .time-periods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .time-period {
            text-align: center;
            padding: 18px 16px;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-radius: 16px;
            border: 1px solid #93c5fd;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .time-period::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 197, 253, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .time-period:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.15);
        }
        
        .time-period:hover::before {
            opacity: 1;
        }
        
        .period-label {
            display: block;
            color: #1e40af;
            font-size: 0.85em;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        
        .period-value {
            display: block;
            color: #1e3a8a;
            font-size: 1.4em;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        /* Practice Sessions Card */
        .sessions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .session-stat {
            text-align: center;
            padding: 18px 16px;
            background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
            border-radius: 16px;
            border: 1px solid #c4b5fd;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .session-stat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(196, 181, 253, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .session-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.15);
        }
        
        .session-stat:hover::before {
            opacity: 1;
        }
        
        .session-value {
            display: block;
            color: #6b21a8;
            font-size: 1.4em;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        
        .session-label {
            display: block;
            color: #581c87;
            font-size: 0.85em;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        /* Insights Card */
        .insights-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .insight-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-radius: 16px;
            border: 1px solid #a7f3d0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .insight-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(167, 243, 208, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .insight-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.15);
        }
        
        .insight-item:hover::before {
            opacity: 1;
        }
        
        .insight-icon {
            font-size: 1.6em;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        
        .insight-content {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        
        .insight-label {
            color: #065f46;
            font-weight: 600;
            font-size: 1em;
        }
        
        .insight-value {
            color: #047857;
            font-weight: 800;
            font-size: 1.2em;
        }
        
        /* Debug Card */
        .debug-card {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
        }
        
        .debug-content {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        .debug-status-section {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid #e5e7eb;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 12px;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .status-label {
            font-weight: 600;
            color: #374151;
        }
        
        .status-value {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #059669;
            background: #ecfdf5;
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        .endpoint-testing-section,
        .database-info-section,
        .ai-analysis-section {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }
        
        .endpoint-testing-section h5,
        .database-info-section h5,
        .ai-analysis-section h5 {
            margin: 0 0 16px 0;
            color: #1f2937;
            font-size: 1.1em;
            font-weight: 700;
        }
        
        .endpoint-buttons,
        .db-buttons,
        .ai-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }
        
        .endpoint-test-btn,
        .db-test-btn,
        .ai-test-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }
        
        .endpoint-test-btn:hover,
        .db-test-btn:hover,
        .ai-test-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
        }
        
        .endpoint-test-btn:disabled,
        .db-test-btn:disabled,
        .ai-test-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .endpoint-results,
        .db-results,
        .ai-results {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #e2e8f0;
            border-bottom: 1px solid #cbd5e1;
        }
        
        .results-header h6 {
            margin: 0;
            color: #1e293b;
            font-size: 1em;
            font-weight: 600;
        }
        
        .results-buttons {
            display: flex;
            gap: 8px;
        }
        
        .copy-results-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .copy-results-btn:hover {
            background: #059669;
        }
        
        .clear-results-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .clear-results-btn:hover {
            background: #dc2626;
        }
        
        .results-content,
        .db-content,
        .ai-content {
            padding: 16px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .no-results {
            color: #6b7280;
            font-style: italic;
            margin: 0;
        }
        
        .test-result {
            margin-bottom: 12px;
            padding: 12px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
            background: white;
        }
        
        .test-result.success {
            border-left-color: #10b981;
            background: #f0fdf4;
        }
        
        .test-result.error {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        
        .test-result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .test-endpoint {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #1f2937;
        }
        
        .test-status {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
        }
        
        .test-status.success {
            background: #dcfce7;
            color: #166534;
        }
        
        .test-status.error {
            background: #fecaca;
            color: #991b1b;
        }
        
        .test-response {
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            background: #f8fafc;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 200px;
            overflow-y: auto;
        }
        
        /* AI Analysis Card Styles */
        .ai-analysis-card {
            grid-column: span 2;
        }
        
        /* Practice Insights Card - make it span full width when alone */
        .insights-card {
            grid-column: span 2;
        }
        
        /* Improve insights card layout for full width */
        .insights-card .insights-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .insights-card .insight-item {
            text-align: center;
            padding: 16px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .insights-card .insight-icon {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
        }
        
        /* Practice Chart Styles */
        .practice-chart-container {
            margin-top: 25px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .chart-header h5 {
            margin: 0;
            font-size: 1.1em;
            color: #1e293b;
        }
        
        .chart-period-links {
            display: flex;
            gap: 15px;
        }
        
        .period-link {
            color: #64748b;
            text-decoration: none;
            font-size: 0.9em;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .period-link:hover {
            color: #0c4a6e;
            background: rgba(12, 74, 110, 0.1);
        }
        
        .period-link.active {
            color: #0c4a6e;
            background: rgba(12, 74, 110, 0.15);
            font-weight: 600;
        }
        
        #practice-chart {
            width: 100% !important;
            height: 200px !important;
            max-width: 100%;
        }
        
        /* AI Analysis Text Styling */
        #ai-analysis-text {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1e293b;
        }
        
        #ai-analysis-text p {
            margin: 12px 0;
            line-height: 1.6;
            color: #1e293b;
        }
        
        #ai-analysis-text h5 {
            color: #0c4a6e;
            margin: 16px 0 8px 0;
            font-size: 1.1em;
            font-weight: 700;
        }
        
        #ai-analysis-text div {
            margin: 8px 0;
            padding-left: 12px;
            line-height: 1.5;
        }
        
        .insights-card .insight-label {
            font-size: 0.9em;
            color: #64748b;
            margin-bottom: 4px;
            display: block;
        }
        
        .insights-card .insight-value {
            font-size: 1.2em;
            font-weight: 700;
            color: #0f172a;
            display: block;
        }
        
        .ai-analysis-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .ai-analysis-text {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #bae6fd;
            min-height: 120px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .ai-analysis-text .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: #0c4a6e;
            text-align: center;
            min-height: 80px;
        }
        
        .ai-analysis-text p {
            color: #0c4a6e;
            font-size: 0.95em;
            line-height: 1.5;
            margin: 0;
            font-weight: 500;
        }
        
        .ai-analysis-text h5 {
            color: #0c4a6e;
            font-size: 1em;
            line-height: 1.4;
            margin: 0 0 8px 0;
            font-weight: 700;
        }
        
        .ai-analysis-text div {
            color: #0c4a6e;
            font-size: 0.9em;
            line-height: 1.4;
        }
        
        .ai-analysis-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 4px;
        }
        
        .ai-data-period {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 0.9em;
        }
        
        .ai-refresh-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        
        .ai-refresh-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-1px);
        }
        
        .ai-refresh-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
        }
        
        .refresh-icon {
            font-size: 0.9em;
        }
        
        
        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #bae6fd;
            border-top: 3px solid #0ea5e9;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .ai-analysis-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .data-period {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 0.9em;
            font-weight: 500;
        }
        
        .period-icon {
            font-size: 1.2em;
        }
        
        .ai-refresh-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .ai-refresh-btn:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }
        
        .ai-refresh-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .ai-refresh-btn svg {
            width: 16px;
            height: 16px;
        }
        
        /* Status Display */
        .ai-status-display {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 12px;
            margin-top: 12px;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 0.9em;
        }
        
        .status-item:last-child {
            margin-bottom: 0;
        }
        
        .status-item strong {
            color: #0c4a6e;
        }
        
        .status-item span {
            color: #0369a1;
        }
        
        /* Debug Section */
        .ai-debug-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-top: 16px;
        }
        
        .debug-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .debug-buttons {
            display: flex;
            gap: 8px;
        }
        
        .debug-header h5 {
            margin: 0;
            color: #374151;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .debug-toggle-btn {
            background: #6b7280;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .debug-toggle-btn:hover {
            background: #4b5563;
        }
        
        .debug-test-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .debug-test-btn:hover {
            background: #2563eb;
        }
        
        .debug-routes-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .debug-routes-btn:hover {
            background: #b91c1c;
        }
        
        .debug-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .debug-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.85em;
        }
        
        .debug-item:last-child {
            border-bottom: none;
        }
        
        .debug-item strong {
            color: #374151;
            min-width: 140px;
            flex-shrink: 0;
        }
        
        .debug-item span {
            color: #6b7280;
            text-align: right;
            flex: 1;
        }
        
        .debug-prompt,
        .debug-response,
        .debug-sql,
        .debug-tables,
        .debug-sessions {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px;
            font-family: monospace;
            font-size: 0.75em;
            line-height: 1.4;
            color: #374151;
            max-height: 100px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }
        
        /* Database Debug Section */
        .database-debug-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        
        .debug-copy-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8em;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .debug-copy-btn:hover {
            background: #059669;
        }
        
        .database-debug-content {
            margin-top: 12px;
        }
        
        .debug-loading {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 20px;
        }
        
        .debug-table-section {
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .debug-table-header {
            background: #f9fafb;
            padding: 12px;
            border-bottom: 1px solid #d1d5db;
            font-weight: 600;
            color: #374151;
        }
        
        .debug-table-content {
            background: white;
            padding: 12px;
            font-family: monospace;
            font-size: 0.75em;
            line-height: 1.4;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .debug-table-row {
            padding: 4px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .debug-table-row:last-child {
            border-bottom: none;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .analytics-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            
            .jph-analytics-section {
                padding: 32px;
            }
        }
        
        @media (max-width: 1024px) {
            .insights-card .insights-list {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
        }
        
        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .time-periods,
            .sessions-grid {
                grid-template-columns: 1fr;
            }
            
            .jph-analytics-section {
                padding: 24px;
                margin: 30px 0;
            }
            
            .analytics-header h3 {
                font-size: 1.8em;
            }
            
            .analytics-icon {
                width: 28px;
                height: 28px;
            }
            
            .analytics-header p {
                font-size: 1.1em;
            }
            
            .analytics-card {
                padding: 24px;
            }
            
            .ai-analysis-card {
                grid-column: span 1;
            }
            
            .insights-card {
                grid-column: span 1;
            }
            
            .insights-card .insights-list {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .ai-analysis-footer {
                flex-direction: column;
                gap: 12px;
            }
            
            .chart-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .chart-period-links {
                gap: 10px;
                align-items: stretch;
            }
            
            .ai-refresh-btn {
                justify-content: center;
            }
        }
        
        /* Tabbed Navigation Styles */
        .jph-tabs-container {
            margin: 30px 0;
        }
        
        .jph-tabs-nav {
            display: flex;
            background: white;
            border-radius: 16px;
            padding: 8px;
            box-shadow: 0 4px 20px rgba(0, 69, 85, 0.1);
            border: 2px solid #e8f5f4;
            margin-bottom: 30px;
            overflow-x: auto;
            gap: 4px;
        }
        
        .jph-tab-btn {
            flex: 1;
            min-width: 140px;
            background: transparent;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            color: #64748b;
            font-family: inherit;
        }
        
        .jph-tab-btn:hover {
            background: rgba(0, 69, 85, 0.05);
            color: #004555;
            transform: translateY(-2px);
        }
        
        .jph-tab-btn.active {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 69, 85, 0.3);
            transform: translateY(-2px);
        }
        
        .jph-tab-btn .tab-icon {
            font-size: 24px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            margin: 0 auto;
        }
        
        .jph-tab-btn .tab-icon svg {
            width: 24px;
            height: 24px;
            transition: all 0.3s ease;
            stroke: #666;
            flex-shrink: 0;
            display: block;
        }
        
        .jph-tab-btn.active .tab-icon svg {
            stroke: white;
            transform: scale(1.05);
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .jph-tab-btn:hover:not(.active) .tab-icon svg {
            stroke: #004555;
            transform: scale(1.05);
        }
        
        .jph-tab-btn:hover.active .tab-icon svg {
            stroke: white;
            transform: scale(1.05);
        }
        
        .jph-tab-btn .tab-title {
            font-size: 16px;
            font-weight: 600;
            line-height: 1.2;
            text-align: center;
            margin: 0;
            width: 100%;
            display: block;
        }
        
        .jph-tab-btn .tab-description {
            font-size: 12px;
            opacity: 0.8;
            line-height: 1.2;
        }
        
        .jph-tab-btn.active .tab-description {
            opacity: 0.9;
        }
        
        .jph-tabs-content {
            position: relative;
        }
        
        .jph-tab-pane {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }
        
        .jph-tab-pane.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Tab Design */
        @media (max-width: 1024px) {
            .jph-tab-btn {
                min-width: 150px;
                padding: 14px 16px;
            }
            
            .jph-tab-btn .tab-title {
                font-size: 15px;
            }
            
            .jph-tab-btn .tab-description {
                font-size: 11px;
            }
        }
        
        @media (max-width: 768px) {
            .jph-tabs-nav {
                flex-direction: column;
                gap: 8px;
                padding: 16px;
            }
            
            .jph-tab-btn {
                min-width: auto;
                flex-direction: row;
                justify-content: flex-start;
                text-align: left;
                padding: 16px 20px;
                gap: 16px;
            }
            
            .jph-tab-btn .tab-icon {
                font-size: 20px;
                flex-shrink: 0;
            }
            
            .jph-tab-btn .tab-title {
                font-size: 16px;
            }
            
            .jph-tab-btn .tab-description {
                font-size: 13px;
                margin-left: auto;
                text-align: right;
            }
        }
        
        @media (max-width: 480px) {
            .jph-tabs-nav {
                padding: 12px;
            }
            
            .jph-tab-btn {
                padding: 14px 16px;
                gap: 12px;
            }
            
            .jph-tab-btn .tab-title {
                font-size: 15px;
            }
            
            .jph-tab-btn .tab-description {
                font-size: 12px;
            }
        }
        </style>
        
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <script>
        jQuery(document).ready(function($) {
            
            // Toggle new improvement pill UI. Set false to revert to simple text.
            const useImprovementPills = true;

            // Initialize beta disclaimer modal
            initBetaDisclaimer();
            
            // Initialize feedback modal
            initFeedbackModal();
            
            // Initialize clean neuroscience tips
            initNeuroscienceTips();
            
            // Initialize practice chart
            initializePracticeChart();
            
            // Initialize period links
            initPeriodLinks();
            
            // Load practice history
            loadPracticeHistory();
            
            // Initialize tab functionality
            initTabs();
            
            // Function to initialize practice chart
            function initializePracticeChart() {
                const ctx = document.getElementById('practice-chart');
                if (!ctx) return;
                
                // Fetch practice data for the last 30 days
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/analytics'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            createPracticeChart(response.data, 30);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading chart data:', error);
                    }
                });
            }
            
            // Function to create the practice chart
            function createPracticeChart(data, selectedDays = 30) {
                const ctx = document.getElementById('practice-chart');
                if (!ctx) return;
                
                // Destroy existing chart if it exists
                if (window.practiceChart) {
                    window.practiceChart.destroy();
                }
                
                // Generate daily data for the selected period
                const dailyData = generateDailyData(selectedDays, data);
                const labels = dailyData.labels;
                const durations = dailyData.durations;
                const sentiments = dailyData.sentiments;
                
                // Create line chart
                window.practiceChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Practice Time (minutes)',
                            data: durations,
                            borderColor: '#0c4a6e',
                            backgroundColor: 'rgba(12, 74, 110, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y'
                        }, {
                            label: 'Sentiment',
                            data: sentiments,
                            borderColor: '#f04e23',
                            backgroundColor: 'rgba(240, 78, 35, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Minutes'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Sentiment (1-5)'
                                },
                                grid: {
                                    drawOnChartArea: false,
                                },
                            }
                        }
                    }
                });
            }
            
            // Function to generate daily data for the selected period using real practice sessions
            function generateDailyData(days, analyticsData) {
                const labels = [];
                const durations = [];
                const sentiments = [];
                
                // Fetch real practice sessions for the selected period
                const endDate = new Date();
                const startDate = new Date();
                startDate.setDate(startDate.getDate() - days);
                
                // Get practice sessions for the period
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/practice-sessions'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    data: { 
                        limit: 1000, // Get a large number to cover the period
                        start_date: startDate.toISOString().split('T')[0],
                        end_date: endDate.toISOString().split('T')[0]
                    },
                    success: function(response) {
                        if (response.success && response.sessions) {
                            // Group sessions by date
                            const dailyData = {};
                            
                            response.sessions.forEach(session => {
                                const sessionDate = new Date(session.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                                
                                if (!dailyData[sessionDate]) {
                                    dailyData[sessionDate] = {
                                        totalMinutes: 0,
                                        totalSentiment: 0,
                                        sessionCount: 0
                                    };
                                }
                                
                                dailyData[sessionDate].totalMinutes += parseInt(session.duration_minutes) || 0;
                                dailyData[sessionDate].totalSentiment += parseFloat(session.sentiment_score) || 0;
                                dailyData[sessionDate].sessionCount += 1;
                            });
                            
                            // Generate data for each day in the period
                            for (let i = days - 1; i >= 0; i--) {
                                const date = new Date();
                                date.setDate(date.getDate() - i);
                                const dateKey = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                                
                                labels.push(dateKey);
                                
                                if (dailyData[dateKey]) {
                                    durations.push(dailyData[dateKey].totalMinutes);
                                    const avgSentiment = dailyData[dateKey].totalSentiment / dailyData[dateKey].sessionCount;
                                    sentiments.push(Math.round(avgSentiment * 10) / 10);
                                } else {
                                    durations.push(0);
                                    sentiments.push(0);
                                }
                            }
                            
                            // Update the chart with real data
                            updateChartWithData(labels, durations, sentiments);
                        } else {
                            // Fallback to empty data if no sessions found
                            generateEmptyChartData(days, labels, durations, sentiments);
                        }
                    },
                    error: function() {
                        // Fallback to empty data if API call fails
                        generateEmptyChartData(days, labels, durations, sentiments);
                    }
                });
                
                return { labels, durations, sentiments };
            }
            
            // Helper function to generate empty chart data
            function generateEmptyChartData(days, labels, durations, sentiments) {
                for (let i = days - 1; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                    durations.push(0);
                    sentiments.push(0);
                }
                updateChartWithData(labels, durations, sentiments);
            }
            
            // Helper function to update chart with data
            function updateChartWithData(labels, durations, sentiments) {
                const ctx = document.getElementById('practice-chart');
                if (!ctx || !window.practiceChart) return;
                
                // Update chart data
                window.practiceChart.data.labels = labels;
                window.practiceChart.data.datasets[0].data = durations;
                window.practiceChart.data.datasets[1].data = sentiments;
                window.practiceChart.update();
            }
            
            // Initialize period link functionality
            function initPeriodLinks() {
                $('.period-link').on('click', function(e) {
                    e.preventDefault();
                    
                    // Update active state
                    $('.period-link').removeClass('active');
                    $(this).addClass('active');
                    
                    // Get selected days
                    const selectedDays = parseInt($(this).data('days'));
                    
                    // Re-fetch analytics data and update chart
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/analytics'); ?>',
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data) {
                                createPracticeChart(response.data, selectedDays);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading chart data:', error);
                        }
                    });
                });
            }
            
            // Initialize tab functionality
            function initTabs() {
                $('.jph-tab-btn').on('click', function(e) {
                    e.preventDefault();
                    
                    const targetTab = $(this).data('tab');
                    
                    // Remove active class from all tabs and panes
                    $('.jph-tab-btn').removeClass('active');
                    $('.jph-tab-pane').removeClass('active');
                    
                    // Add active class to clicked tab and corresponding pane
                    $(this).addClass('active');
                    $('#' + targetTab + '-tab').addClass('active');
                    
                    // Trigger any necessary content loading for the active tab
                    switch(targetTab) {
                        case 'analytics':
                            // Ensure analytics are loaded when switching to analytics tab
                            if (typeof loadAnalytics === 'function') {
                                loadAnalytics();
                            }
                            if (typeof loadAIAnalysis === 'function') {
                                loadAIAnalysis();
                            }
                            break;
                        case 'history':
                            // Ensure practice history is loaded when switching to history tab
                            if (typeof loadPracticeHistory === 'function') {
                                loadPracticeHistory();
                            }
                            break;
                        case 'badges':
                            // Ensure badges are loaded when switching to badges tab
                            if (typeof loadBadges === 'function') {
                                loadBadges();
                            }
                            break;
                    }
                });
            }
            
            // Load badges
            loadBadges();
            
            // Load lesson favorites
            loadLessonFavorites();
            
            // Load analytics
            loadAnalytics();
            
            // Load AI analysis
            loadAIAnalysis();
            
            // Initialize other functionality
            initModalHandlers();
            initPracticeSessionHandlers();
            initShieldHandlers();
            initStatsHandlers();
            initDisplayNameHandlers();
            
            // Clean Neuroscience Tips (Adult-oriented)
            function initNeuroscienceTips() {
                const practiceTips = [
                    "Memory consolidation occurs during sleep — practice 4 hours before bedtime for optimal retention.",
                    "Break practice into 20-25 minute sessions with 5-minute breaks to maximize focus and learning.",
                    "Sleep deprivation reduces motor learning efficiency by 40% — prioritize rest for better progress.",
                    "Practice at 85% difficulty level — challenging but achievable for maximum skill development.",
                    "Slow, perfect repetitions build neural pathways faster than rushed practice — quality over speed.",
                    "Morning practice shows 23% better retention — optimize for your brain's peak learning hours.",
                    "Mental practice activates identical brain areas as physical practice — visualization accelerates learning.",
                    "10 minutes of meditation before practice can improve focus by up to 67% during sessions.",
                    "Scale practice primes neural pathways for complex pieces — warm up cognitively, not just physically.",
                    "Playing simple pieces while multitasking engages implicit learning systems — passive absorption works.",
                    "More than 4 hours of daily practice can decrease accuracy by 25% — avoid diminishing returns.",
                    "Mental rehearsal triggers the same neural patterns as physical practice — utilize downtime effectively."
                ];
                
                // Show random tip on page load only
                const randomIndex = Math.floor(Math.random() * practiceTips.length);
                const tipElement = document.getElementById('neuro-tip-text');
                
                if (tipElement) {
                    tipElement.textContent = practiceTips[randomIndex];
                    
                    // Add subtle fade-in effect
                    tipElement.style.opacity = '0';
                    setTimeout(() => {
                        tipElement.style.transition = 'opacity 0.6s ease-in-out';
                        tipElement.style.opacity = '1';
                    }, 200);
                }
            }
            
            // Load practice history
            function loadPracticeHistory() {
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/practice-sessions'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    data: { limit: 10 },
                    success: function(response) {
                        if (response.success && response.sessions) {
                            displayPracticeHistory(response.sessions, response.has_more);
                        } else {
                            $('#practice-history-list').html('<div class="no-sessions">No practice sessions found.</div>');
                            $('#load-more-container').hide();
                        }
                    },
                    error: function() {
                        $('#practice-history-list').html('<div class="error-message">Error loading practice history.</div>');
                        $('#load-more-container').hide();
                    }
                });
            }
            
            // Helper function to truncate notes
            function truncateNotes(notes, maxLength = 50) {
                if (!notes || notes.trim() === '') {
                    return '';
                }
                if (notes.length <= maxLength) {
                    return notes;
                }
                return notes.substring(0, maxLength) + '...';
            }
            
            // Display practice history
            function displayPracticeHistory(sessions, hasMore = false) {
                let html = '';
                if (sessions.length === 0) {
                    html = '<div class="no-sessions">No practice sessions found.</div>';
                } else {
                    sessions.forEach(session => {
                        const date = new Date(session.created_at).toLocaleDateString();
                        const score = parseInt(session.sentiment_score, 10);
                        const sentimentEmojiMap = {1:'😞',2:'😕',3:'😐',4:'😊',5:'🤩'};
                        const sentimentLabelMap = {1:'Struggled',2:'Difficult',3:'Okay',4:'Good',5:'Excellent'};
                        const sentiment = score ? (sentimentEmojiMap[score] + ' ' + sentimentLabelMap[score]) : 'Not specified';
                        // DB may return '0'/'1' strings; ensure strict numeric/boolean check
                        const improvementDetected = (session.improvement_detected === true)
                            || (session.improvement_detected === 1)
                            || (session.improvement_detected === '1')
                            || (Number(session.improvement_detected) === 1);
                        let improvement;
                        if (useImprovementPills) {
                            improvement = improvementDetected
                                ? '<span class="jph-pill jph-pill-progress" title="You reported improvement today—nice!">↑ Progress</span>'
                                : '<span class="jph-pill jph-pill-logged" title="Consistency builds skills—great work!">• Logged</span>';
                        } else {
                            improvement = improvementDetected ? 'Yes' : 'No';
                        }
                        
                    html += `
                            <div class="practice-history-item">
                                <div class="practice-history-item-content">${date}</div>
                                <div class="practice-history-item-content">${session.item_name}</div>
                                <div class="practice-history-item-content center">${session.duration_minutes} min</div>
                                <div class="practice-history-item-content center">${sentiment}</div>
                                <div class="practice-history-item-content center">${improvement}</div>
                                <div class="practice-history-item-content center">
                                    <button type="button" class="jph-delete-session-btn" data-session-id="${session.id}" data-item-name="${session.item_name || 'Unknown Item'}" title="Delete this practice session"><i class="fa-solid fa-circle-xmark"></i></button>
                                </div>
                                ${session.notes ? `<div class="practice-history-notes">${truncateNotes(session.notes)}</div>` : ''}
                            </div>
                        `;
                    });
                }
                $('#practice-history-list').html(html);
                
                // Show/hide load more button based on whether there are more sessions
                if (hasMore && sessions.length >= 10) {
                    $('#load-more-container').show();
                } else {
                    $('#load-more-container').hide();
                }
            }
            
            // Export practice history to CSV
            function exportPracticeHistory() {
                // Use AJAX to get the CSV data and trigger download
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/export-practice-history'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(data, status, xhr) {
                        console.log('Export response:', data, 'Status:', status, 'XHR:', xhr);
                        
                        // Check if we got CSV data or an error
                        if (typeof data === 'string' && (data.includes('Date,Practice Item') || data.includes('"Date","Practice Item"'))) {
                            // We got CSV data, trigger download
                            const blob = new Blob([data], { type: 'text/csv;charset=utf-8;' });
                            const link = document.createElement('a');
                            const url = URL.createObjectURL(blob);
                            link.setAttribute('href', url);
                            link.setAttribute('download', 'practice-history-' + new Date().toISOString().split('T')[0] + '.csv');
                            link.style.visibility = 'hidden';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            
                            showToast('Practice history exported successfully!', 'success');
                        } else {
                            // Error response
                            let errorMsg = 'Unknown error';
                            if (typeof data === 'object' && data.message) {
                                errorMsg = data.message;
                            } else if (typeof data === 'string') {
                                errorMsg = data;
                            }
                            console.error('Export error:', errorMsg);
                            showToast('Error exporting practice history: ' + errorMsg, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Export AJAX error:', xhr, status, error);
                        let errorMsg = error;
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            errorMsg = xhr.responseText;
                        }
                        showToast('Error exporting practice history: ' + errorMsg, 'error');
                    }
                });
            }
            
            // Load more practice sessions
            function loadMorePracticeSessions() {
                const currentSessions = $('#practice-history-list .practice-history-item').length;
                const limit = 10;
                
                console.log('Load More: Current sessions count:', currentSessions, 'Offset:', currentSessions);
                
                $('#load-more-sessions-bottom').prop('disabled', true).text('Loading...');
                
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/practice-sessions'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    data: { 
                        limit: limit,
                        offset: currentSessions
                    },
                    success: function(response) {
                        console.log('Load More: Response received:', response);
                        if (response.success && response.sessions && response.sessions.length > 0) {
                            console.log('Load More: Adding', response.sessions.length, 'new sessions');
                            // Append new sessions to existing list
                            let html = '';
                            response.sessions.forEach(session => {
                                const date = new Date(session.created_at).toLocaleDateString();
                                const score = parseInt(session.sentiment_score, 10);
                                const sentimentEmojiMap = {1:'😞',2:'😕',3:'😐',4:'😊',5:'🤩'};
                                const sentimentLabelMap = {1:'Struggled',2:'Difficult',3:'Okay',4:'Good',5:'Excellent'};
                                const sentiment = score ? (sentimentEmojiMap[score] + ' ' + sentimentLabelMap[score]) : 'Not specified';
                                // DB may return '0'/'1' strings; ensure strict numeric/boolean check
                                const improvementDetected = (session.improvement_detected === true)
                                    || (session.improvement_detected === 1)
                                    || (session.improvement_detected === '1')
                                    || (Number(session.improvement_detected) === 1);
                                let improvement;
                                if (useImprovementPills) {
                                    improvement = improvementDetected
                                        ? '<span class="jph-pill jph-pill-progress" title="You reported improvement today—nice!">↑ Progress</span>'
                                        : '<span class="jph-pill jph-pill-logged" title="Consistency builds skills—great work!">• Logged</span>';
                                } else {
                                    improvement = improvementDetected ? 'Yes' : 'No';
                                }
                                
                    html += `
                            <div class="practice-history-item">
                                <div class="practice-history-item-content">${date}</div>
                                <div class="practice-history-item-content">${session.item_name}</div>
                                <div class="practice-history-item-content center">${session.duration_minutes} min</div>
                                <div class="practice-history-item-content center">${sentiment}</div>
                                <div class="practice-history-item-content center">${improvement}</div>
                                <div class="practice-history-item-content center">
                                    <button type="button" class="jph-delete-session-btn" data-session-id="${session.id}" data-item-name="${session.item_name || 'Unknown Item'}" title="Delete this practice session"><i class="fa-solid fa-circle-xmark"></i></button>
                                </div>
                                ${session.notes ? `<div class="practice-history-notes">${truncateNotes(session.notes)}</div>` : ''}
                            </div>
                        `;
                            });
                            
                            $('#practice-history-list').append(html);
                            
                            // Show/hide load more button based on whether we got a full batch
                            if (response.sessions.length < limit) {
                                $('#load-more-container').hide();
                            } else {
                                $('#load-more-container').show();
                            }
                            
                            showToast(`Loaded ${response.sessions.length} more sessions`, 'success');
                        } else {
                            $('#load-more-container').hide();
                            showToast('No more sessions to load', 'info');
                        }
                    },
                    error: function() {
                        showToast('Error loading more sessions', 'error');
                    },
                    complete: function() {
                        $('#load-more-sessions-bottom').prop('disabled', false).html('<span class="btn-icon">📈</span>Load More Sessions');
                    }
                });
            }
            
            // Show toast notification
            function showToast(message, type = 'info') {
                const toast = $(`
                    <div class="jph-toast jph-toast-${type}">
                        <div class="toast-content">
                            <span class="toast-message">${message}</span>
                            <button class="toast-close">&times;</button>
                        </div>
                    </div>
                `);
                
                $('body').append(toast);
                
                // Auto remove after 3 seconds
                setTimeout(() => {
                    toast.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
                
                // Manual close
                toast.find('.toast-close').on('click', function() {
                    toast.fadeOut(300, function() {
                        $(this).remove();
                    });
                });
            }
            
            // Initialize beta disclaimer modal
            function initBetaDisclaimer() {
                // Check if disclaimer has been shown before
                const disclaimerShown = localStorage.getItem('jph_beta_disclaimer_shown');
                
                if (!disclaimerShown) {
                    // Show the modal
                    $('#jph-beta-disclaimer-modal').show();
                    
                    // Handle close button
                    $('#jph-beta-disclaimer-close').on('click', function() {
                        closeBetaDisclaimer();
                    });
                    
                    // Handle overlay click
                    $('.jph-modal-overlay').on('click', function() {
                        closeBetaDisclaimer();
                    });
                    
                    // Handle "I Understand" button
                    $('#jph-beta-disclaimer-understand').on('click', function() {
                        markDisclaimerAsShown();
                        closeBetaDisclaimer();
                    });
                    
                    // Handle escape key
                    $(document).on('keydown.betaDisclaimer', function(e) {
                        if (e.key === 'Escape') {
                            closeBetaDisclaimer();
                        }
                    });
                }
            }
            
            // Close beta disclaimer modal
            function closeBetaDisclaimer() {
                $('#jph-beta-disclaimer-modal').fadeOut(300);
                $(document).off('keydown.betaDisclaimer');
            }
            
            // Mark disclaimer as shown
            function markDisclaimerAsShown() {
                // Store in localStorage for immediate effect
                localStorage.setItem('jph_beta_disclaimer_shown', 'true');
                
                // Also mark on server for persistence
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/beta-disclaimer/shown'); ?>',
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log('Beta disclaimer marked as shown on server');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to mark disclaimer as shown on server:', error);
                    }
                });
            }
            
            // Initialize feedback modal
            function initFeedbackModal() {
                // Handle feedback button click (banner button only)
                $('#jph-feedback-btn-banner').on('click', function() {
                    $('#jph-feedback-modal').show();
                });
                
                // Handle modal close
                $('#jph-feedback-modal .jph-modal-close').on('click', function() {
                    $('#jph-feedback-modal').hide();
                });
                
                // Handle overlay click
                $('#jph-feedback-modal').on('click', function(e) {
                    if (e.target === this) {
                        $(this).hide();
                    }
                });
                
                // Handle escape key
                $(document).on('keydown.feedbackModal', function(e) {
                    if (e.key === 'Escape' && $('#jph-feedback-modal').is(':visible')) {
                        $('#jph-feedback-modal').hide();
                    }
                });
            }
            
            // Load badges
            function loadBadges() {
                console.log('Loading badges...');
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/badges'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        console.log('Badges API response:', response);
                        if (response.success) {
                            console.log('Success! Displaying badges:', response.badges);
                            displayBadges(response.badges);
                        } else {
                            console.log('API returned success: false');
                            $('#jph-badges-grid').html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges available</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading badges:', error);
                        console.error('XHR response:', xhr.responseText);
                        $('#jph-badges-grid').html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges earned yet</div>');
                    }
                });
            }
            
            // Display badges
            function displayBadges(badges) {
                var $container = $('#jph-badges-grid');
                
                if (!badges || badges.length === 0) {
                    $container.html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges available yet. Keep practicing to earn your first badge!</div>');
                    return;
                }
                
                var html = '';
                var earnedCount = 0;
                badges.forEach(function(badge, index) {
                    var earnedClass = badge.is_earned ? 'earned' : 'locked';
                    if (badge.is_earned) earnedCount++;
                    
                    var badgeImage = badge.image_url && badge.image_url.startsWith('http') ? 
                        '<img src="' + badge.image_url + '" alt="' + badge.name + '">' : 
                        '<div class="jph-badge-placeholder"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path></svg></div>';
                    
                    var earnedDate = badge.is_earned && badge.earned_at ? 
                        '<div class="jph-badge-earned-date">Earned: ' + formatDate(badge.earned_at) + '</div>' : '';
                    
                    html += '<div class="jph-badge-card ' + earnedClass + '">';
                    html += '    <div class="jph-badge-image">' + badgeImage + '</div>';
                    html += '    <div class="jph-badge-name">' + escapeHtml(badge.name) + '</div>';
                    html += '    <div class="jph-badge-description">' + escapeHtml(badge.description || '') + '</div>';
                    html += '    <div class="jph-badge-rewards">+' + (badge.xp_reward || 0) + ' XP +' + (badge.gem_reward || 0) + ' 💎</div>';
                    html += earnedDate;
                    html += '</div>';
                });
                
                $container.html(html);
                
                // Update badge count if element exists
                if ($('#badge-count').length) {
                    $('#badge-count').text(earnedCount);
                }
            }
            
            // Load lesson favorites
            function loadLessonFavorites() {
                console.log('Loading lesson favorites...');
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/lesson-favorites'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var select = $('#lesson-favorite-select');
                            select.empty();
                            
                            if (response.favorites.length === 0) {
                                select.append('<option value="">No lesson favorites found</option>');
                            } else {
                                select.append('<option value="">Select a lesson favorite...</option>');
                                $.each(response.favorites, function(index, favorite) {
                                    select.append('<option value="' + favorite.id + '" data-title="' + escapeHtml(favorite.title) + '" data-category="' + escapeHtml(favorite.category) + '" data-description="' + escapeHtml(favorite.description || '') + '">' + escapeHtml(favorite.title) + '</option>');
                                });
                            }
                        } else {
                            $('#lesson-favorite-select').html('<option value="">Error loading favorites</option>');
                        }
                    },
                    error: function() {
                        $('#lesson-favorite-select').html('<option value="">Error loading favorites</option>');
                    }
                });
            }
            
            // Load analytics data
            function loadAnalytics() {
                console.log('Loading analytics...');
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/analytics'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            displayAnalytics(response.data);
                        } else {
                            console.error('Analytics API error:', response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Analytics loading error:', error);
                    }
                });
            }
            
            // Display analytics data
            function displayAnalytics(data) {
                // Set target values for counting animation
                $('#analytics-7-days-minutes').attr('data-target', data.periods['7_days'].total_minutes || '0');
                $('#analytics-30-days-minutes').attr('data-target', data.periods['30_days'].total_minutes || '0');
                $('#analytics-90-days-minutes').attr('data-target', data.periods['90_days'].total_minutes || '0');
                $('#analytics-365-days-minutes').attr('data-target', data.periods['365_days'].total_minutes || '0');
                
                $('#analytics-7-days-sessions').attr('data-target', data.periods['7_days'].sessions || '0');
                $('#analytics-30-days-sessions').attr('data-target', data.periods['30_days'].sessions || '0');
                $('#analytics-90-days-sessions').attr('data-target', data.periods['90_days'].sessions || '0');
                $('#analytics-365-days-sessions').attr('data-target', data.periods['365_days'].sessions || '0');
                
                // Set start values for realistic counting animation
                $('#analytics-7-days-minutes').attr('data-start', Math.max(0, Math.floor((data.periods['7_days'].total_minutes || 0) * 0.7)));
                $('#analytics-30-days-minutes').attr('data-start', Math.max(0, Math.floor((data.periods['30_days'].total_minutes || 0) * 0.7)));
                $('#analytics-90-days-minutes').attr('data-start', Math.max(0, Math.floor((data.periods['90_days'].total_minutes || 0) * 0.7)));
                $('#analytics-365-days-minutes').attr('data-start', Math.max(0, Math.floor((data.periods['365_days'].total_minutes || 0) * 0.7)));
                
                $('#analytics-7-days-sessions').attr('data-start', Math.max(0, Math.floor((data.periods['7_days'].sessions || 0) * 0.7)));
                $('#analytics-30-days-sessions').attr('data-start', Math.max(0, Math.floor((data.periods['30_days'].sessions || 0) * 0.7)));
                $('#analytics-90-days-sessions').attr('data-start', Math.max(0, Math.floor((data.periods['90_days'].sessions || 0) * 0.7)));
                $('#analytics-365-days-sessions').attr('data-start', Math.max(0, Math.floor((data.periods['365_days'].sessions || 0) * 0.7)));
                
                // Initialize counting animation
                initCountingAnimation();
                
                // Insights data
                $('#analytics-consistency').text(data.insights.consistency_score + '%');
                $('#analytics-improvement-rate').text(data.insights.improvement_rate + '%');
                $('#analytics-sentiment').text(data.insights.sentiment_rating);
                
                // Fun facts
                if (data.insights.best_day) {
                    const bestDay = new Date(data.insights.best_day.date).toLocaleDateString();
                    $('#analytics-best-day .fact-text').text('Best practice day: ' + bestDay + ' (' + data.insights.best_day.minutes + ' minutes)');
                } else {
                    $('#analytics-best-day .fact-text').text('Keep practicing to find your best day!');
                }
                
                if (data.insights.favorite_hour) {
                    const hour = data.insights.favorite_hour.hour;
                    const timeStr = hour === 0 ? '12 AM' : hour < 12 ? hour + ' AM' : hour === 12 ? '12 PM' : (hour - 12) + ' PM';
                    $('#analytics-favorite-time .fact-text').text('Favorite practice time: ' + timeStr + ' (' + data.insights.favorite_hour.sessions + ' sessions)');
                } else {
                    $('#analytics-favorite-time .fact-text').text('Practice more to discover your favorite time!');
                }
                
                if (data.insights.most_practiced) {
                    $('#analytics-most-practiced .fact-text').text('Most practiced: ' + data.insights.most_practiced.item_name + ' (' + data.insights.most_practiced.sessions + ' sessions)');
                } else {
                    $('#analytics-most-practiced .fact-text').text('Add practice items to see your favorites!');
                }
            }
            
            // Initialize counting animation with Intersection Observer
            function initCountingAnimation() {
                const observerOptions = {
                    threshold: 0.5,
                    rootMargin: '0px 0px -100px 0px'
                };
                
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                            animateCountUp(entry.target);
                            entry.target.classList.add('animated');
                        }
                    });
                }, observerOptions);
                
                // Observe all count-up elements
                document.querySelectorAll('.count-up').forEach(element => {
                    observer.observe(element);
                });
            }
            
            // Animate count up effect
            function animateCountUp(element) {
                const target = parseInt(element.getAttribute('data-target')) || 0;
                const start = parseInt(element.getAttribute('data-start')) || 0;
                const duration = 2000; // 2 seconds
                const increment = (target - start) / (duration / 16); // 60fps
                let current = start;
                
                element.classList.add('animate');
                
                // Set initial value
                const isHeroStat = element.classList.contains('stat-value');
                if (isHeroStat) {
                    // For hero stats, extract the emoji and add it back
                    const emoji = element.textContent.match(/[⭐⚡🔥💎]/)?.[0] || '';
                    element.textContent = emoji + start.toLocaleString();
                } else {
                    element.textContent = start.toLocaleString();
                }
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    if (isHeroStat) {
                        const emoji = element.textContent.match(/[⭐⚡🔥💎]/)?.[0] || '';
                        element.textContent = emoji + Math.floor(current).toLocaleString();
                    } else {
                        element.textContent = Math.floor(current).toLocaleString();
                    }
                }, 16);
            }
            
            // Load AI analysis
            function loadAIAnalysis() {
                console.log('Loading AI analysis...');
                
                // Show status display
                $('#ai-status-display').show();
                updateStatus('Testing API connection...', 'Checking if REST endpoints are accessible');
                
                // First test if the endpoint is accessible
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/test'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(testResponse) {
                        console.log('Test endpoint works:', testResponse);
                        updateStatus('API connection successful', 'Loading AI analysis...');
                        // Now try the AI analysis
                        loadAIAnalysisActual();
                    },
                    error: function(xhr, status, error) {
                        console.error('Test endpoint failed:', error);
                        console.error('XHR response:', xhr.responseText);
                        updateStatus('API connection failed', 'Error: ' + error + ' (Status: ' + xhr.status + ')');
                        
                        // Try to get more info about the error
                        if (xhr.status === 403) {
                            displayAIAnalysisError('Permission denied (403). Are you logged in? User ID: <?php echo get_current_user_id(); ?>');
                        } else if (xhr.status === 404) {
                            displayAIAnalysisError('Endpoint not found (404). REST API routes may not be registered.');
                        } else {
                            displayAIAnalysisError('REST API error: ' + error + ' (Status: ' + xhr.status + ')');
                        }
                    }
                });
            }
            
            // Update status display
            function updateStatus(status, step) {
                $('#ai-status-text').text(status);
                $('#ai-step-text').text(step);
            }
            
            // Actual AI analysis loading
            function loadAIAnalysisActual() {
                updateStatus('Loading AI analysis...', 'Fetching practice data and generating insights');
                
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/ai-analysis'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        console.log('AI Analysis response:', response);
                        if (response.success && response.data) {
                            updateStatus('Analysis complete', 'Displaying results...');
                            displayAIAnalysis(response.data, response.cached);
                        } else {
                            console.error('AI Analysis API error:', response);
                            updateStatus('Analysis failed', 'API Error: ' + (response.message || 'Unknown error'));
                            displayAIAnalysisError('API Error: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AI Analysis loading error:', error);
                        console.error('XHR response:', xhr.responseText);
                        updateStatus('Analysis failed', 'Connection Error: ' + error + ' (Status: ' + xhr.status + ')');
                        displayAIAnalysisError('Connection Error: ' + error + ' (Status: ' + xhr.status + ')');
                    }
                });
            }
            
            // Display AI analysis
            function displayAIAnalysis(data, isCached) {
                const $analysisText = $('#ai-analysis-text');
                const $dataPeriod = $('#ai-data-period .period-text');
                
                // Display raw AI text without formatting
                $analysisText.html('<p>' + data.analysis + '</p>');
                
                // Update data period
                $dataPeriod.text(data.data_period);
                
                // Show cache indicator if cached
                if (isCached) {
                    $dataPeriod.text(data.data_period + ' (cached)');
                }
                
                // Initialize refresh button
                initAIRefreshButton();
            }
            
            
            // Display AI analysis error
            function displayAIAnalysisError(message) {
                const $analysisText = $('#ai-analysis-text');
                $analysisText.html('<p style="color: #dc2626; text-align: center;">' + message + '</p>');
                
                // Initialize refresh button
                initAIRefreshButton();
            }
            
            // Initialize AI refresh button
            function initAIRefreshButton() {
                $('#ai-refresh-btn').off('click').on('click', function() {
                    const $btn = $(this);
                    const $analysisText = $('#ai-analysis-text');
                    
                    // Disable button and show loading
                    $btn.prop('disabled', true);
                    $btn.html('<div class="spinner" style="width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top: 2px solid white; border-radius: 50%; animation: spin 1s linear infinite;"></div> Refreshing...');
                    
                    // Show loading in analysis text
                    $analysisText.html('<div class="loading-spinner"><div class="spinner"></div><span>Generating fresh analysis...</span></div>');
                    
                    // Make refresh request (use regular endpoint with refresh parameter)
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/ai-analysis'); ?>?refresh=1',
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        success: function(response) {
                            console.log('AI Analysis refresh response:', response);
                            if (response.success && response.data) {
                                displayAIAnalysis(response.data, false);
                            } else {
                                displayAIAnalysisError('Failed to refresh AI analysis: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AI Analysis refresh error:', error);
                            console.error('XHR response:', xhr.responseText);
                            console.error('Status:', xhr.status);
                            displayAIAnalysisError('Failed to refresh AI analysis (Status: ' + xhr.status + ')');
                        },
                        complete: function() {
                            // Re-enable button
                            $btn.prop('disabled', false);
                            $btn.html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg> Refresh');
                        }
                    });
                });
            }
            
            // Populate debug information
            function populateDebugInfo(data) {
                // Show debug section
                $('#ai-debug-section').show();
                
                // Populate debug data
                if (data.data_summary) {
                    $('#debug-sessions-count').text(data.data_summary.total_sessions || '0');
                    $('#debug-total-minutes').text(data.data_summary.total_minutes || '0');
                    $('#debug-avg-sentiment').text(data.data_summary.avg_sentiment || '0');
                    $('#debug-improvement-rate').text(data.data_summary.improvement_rate + '%' || '0%');
                    $('#debug-most-day').text(data.data_summary.most_frequent_day || 'None');
                    $('#debug-most-item').text(data.data_summary.most_practiced_item || 'None');
                } else {
                    $('#debug-sessions-count').text('0');
                    $('#debug-total-minutes').text('0');
                    $('#debug-avg-sentiment').text('0');
                    $('#debug-improvement-rate').text('0%');
                    $('#debug-most-day').text('None');
                    $('#debug-most-item').text('None');
                }
                
                // Set date range
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                const now = new Date();
                $('#debug-date-range').text(thirtyDaysAgo.toLocaleDateString() + ' to ' + now.toLocaleDateString());
                
                // Set AI prompt and response
                $('#debug-prompt').text(data.debug_prompt || 'No prompt data available');
                $('#debug-response').text(data.analysis || 'No response data available');
                
                // Set additional debug info
                if (data.debug_info) {
                    $('#debug-user-id').text(data.debug_info.user_id || 'Unknown');
                    $('#debug-total-sessions').text(data.debug_info.total_sessions_user || '0');
                    $('#debug-sessions-30-days').text(data.debug_info.sessions_30_days || '0');
                    
                    // Set date range
                    $('#debug-date-start').text(data.debug_info.date_range_start || 'Unknown');
                    $('#debug-date-end').text(data.debug_info.date_range_end || 'Unknown');
                    
                    // Set table names
                    if (data.debug_info.table_names) {
                        const tableNames = Object.entries(data.debug_info.table_names)
                            .map(([key, value]) => `${key}: ${value}`)
                            .join('\n');
                        $('#debug-tables').text(tableNames);
                    }
                    
                    // Set SQL query
                    $('#debug-sql').text(data.debug_info.sql_query || 'No SQL query available');
                    
                    // Set sample sessions from database debug
                    loadSampleSessions(data.debug_info.user_id);
                } else {
                    $('#debug-user-id').text('Unknown');
                    $('#debug-total-sessions').text('0');
                    $('#debug-sessions-30-days').text('0');
                    $('#debug-date-start').text('Unknown');
                    $('#debug-date-end').text('Unknown');
                    $('#debug-tables').text('No table info available');
                    $('#debug-sql').text('No SQL query available');
                    $('#debug-sessions').text('No session data available');
                }
                
                // Set current times
                $('#debug-wp-time').text(new Date().toLocaleString());
                $('#debug-server-time').text(new Date().toISOString());
                
                // Initialize debug toggle
                initDebugToggle();
                
                // Load database debug info
                loadDatabaseDebug();
            }
            
            // Initialize debug toggle
            function initDebugToggle() {
                $('#debug-toggle-btn').off('click').on('click', function() {
                    const $content = $('#debug-content');
                    const $btn = $(this);
                    
                    if ($content.is(':visible')) {
                        $content.hide();
                        $btn.text('Show Debug');
                    } else {
                        $content.show();
                        $btn.text('Hide Debug');
                    }
                });
                
                // Initialize date test button
                $('#debug-test-btn').off('click').on('click', function() {
                    const $btn = $(this);
                    $btn.prop('disabled', true);
                    $btn.text('Testing...');
                    
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/debug/date-test'); ?>',
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                displayDateTestResults(response);
                            } else {
                                alert('Date test failed: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Date test error: ' + error);
                        },
                        complete: function() {
                            $btn.prop('disabled', false);
                            $btn.text('Test Date Queries');
                        }
                    });
                });
                
                // Initialize routes test button
                $('#debug-routes-btn').off('click').on('click', function() {
                    const $btn = $(this);
                    $btn.prop('disabled', true);
                    $btn.text('Testing...');
                    
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/debug/routes'); ?>',
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                displayRoutesTestResults(response);
                            } else {
                                alert('Routes test failed: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Routes test error: ' + error + ' (Status: ' + xhr.status + ')');
                        },
                        complete: function() {
                            $btn.prop('disabled', false);
                            $btn.text('Test Routes');
                        }
                    });
                });
            }
            
            // Display routes test results
            function displayRoutesTestResults(data) {
                let html = '<div class="routes-test-results">';
                html += '<h6>REST API Routes Test:</h6>';
                html += '<div class="debug-table-row"><strong>Total JPH Routes:</strong> ' + data.total_jph_routes + '</div>';
                html += '<div class="debug-table-row"><strong>WP REST Server:</strong> ' + (data.wp_rest_server_exists ? 'Available' : 'Not Available') + '</div>';
                
                if (data.registered_routes && data.registered_routes.length > 0) {
                    html += '<div style="margin-top: 10px;"><strong>Registered Routes:</strong></div>';
                    data.registered_routes.forEach(route => {
                        html += '<div class="debug-table-row">' + route + '</div>';
                    });
                } else {
                    html += '<div class="debug-table-row" style="color: #dc2626;">No JPH routes found!</div>';
                }
                
                html += '</div>';
                
                // Add to debug content
                $('#debug-content').append(html);
            }
            
            // Display date test results
            function displayDateTestResults(data) {
                let html = '<div class="date-test-results">';
                html += '<h6>Date Test Results:</h6>';
                
                // Show sample sessions
                if (data.all_sessions_sample && data.all_sessions_sample.length > 0) {
                    html += '<div><strong>Your Recent Sessions:</strong></div>';
                    data.all_sessions_sample.forEach((session, index) => {
                        html += '<div class="debug-table-row">' + (index + 1) + '. ' + session.created_at + '</div>';
                    });
                }
                
                // Show date test results
                html += '<div style="margin-top: 15px;"><strong>Date Query Tests:</strong></div>';
                Object.keys(data.date_tests).forEach(testName => {
                    const test = data.date_tests[testName];
                    html += '<div class="debug-table-row">';
                    html += '<strong>' + testName + ':</strong> ' + test.date + ' → ' + test.sessions_found + ' sessions';
                    html += '</div>';
                });
                
                html += '</div>';
                
                // Add to debug content
                $('#debug-content').append(html);
            }
            
            // Load database debug information
            function loadDatabaseDebug() {
                $.ajax({
                        url: '<?php echo rest_url('aph/v1/debug/database'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.tables) {
                            displayDatabaseDebug(response);
                        } else {
                            $('#database-debug-content').html('<div class="debug-error">Failed to load database information</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Database debug loading error:', error);
                        $('#database-debug-content').html('<div class="debug-error">Error loading database information: ' + error + '</div>');
                    }
                });
            }
            
            // Display database debug information
            function displayDatabaseDebug(data) {
                const $content = $('#database-debug-content');
                let html = '';
                
                // Add user info
                html += '<div class="debug-table-section">';
                html += '<div class="debug-table-header">User Information</div>';
                html += '<div class="debug-table-content">';
                html += '<div class="debug-table-row"><strong>User ID:</strong> ' + data.user_id + '</div>';
                html += '<div class="debug-table-row"><strong>Generated At:</strong> ' + data.generated_at + '</div>';
                html += '</div></div>';
                
                // Add each table
                Object.keys(data.tables).forEach(tableName => {
                    const table = data.tables[tableName];
                    
                    html += '<div class="debug-table-section">';
                    html += '<div class="debug-table-header">' + tableName + ' (' + (table.exists ? 'EXISTS' : 'MISSING') + ' - ' + table.row_count + ' rows)</div>';
                    html += '<div class="debug-table-content">';
                    
                    if (table.exists) {
                        // Table structure
                        html += '<div style="margin-bottom: 15px;"><strong>Structure:</strong></div>';
                        table.structure.forEach(column => {
                            html += '<div class="debug-table-row">' + column.Field + ' (' + column.Type + ') - ' + column.Null + ' - ' + column.Key + '</div>';
                        });
                        
                        // User data
                        if (table.user_data && table.user_data.length > 0) {
                            html += '<div style="margin: 15px 0;"><strong>Your Data (Last 10):</strong></div>';
                            table.user_data.forEach((row, index) => {
                                html += '<div class="debug-table-row"><strong>Row ' + (index + 1) + ':</strong> ' + JSON.stringify(row, null, 2) + '</div>';
                            });
                        }
                        
                        // Recent data for non-user tables
                        if (table.recent_data && table.recent_data.length > 0) {
                            html += '<div style="margin: 15px 0;"><strong>Recent Data (Last 10):</strong></div>';
                            table.recent_data.forEach((row, index) => {
                                html += '<div class="debug-table-row"><strong>Row ' + (index + 1) + ':</strong> ' + JSON.stringify(row, null, 2) + '</div>';
                            });
                        }
                    } else {
                        html += '<div class="debug-table-row" style="color: #dc2626;">Table does not exist</div>';
                    }
                    
                    html += '</div></div>';
                });
                
                $content.html(html);
                
                // Initialize copy button
                initCopyButton(data);
            }
            
            // Initialize copy button
            function initCopyButton(data) {
                $('#debug-copy-btn').off('click').on('click', function() {
                    const debugText = JSON.stringify(data, null, 2);
                    
                    // Copy to clipboard
                    navigator.clipboard.writeText(debugText).then(function() {
                        const $btn = $(this);
                        const originalText = $btn.text();
                        $btn.text('Copied!');
                        $btn.css('background', '#059669');
                        
                        setTimeout(function() {
                            $btn.text(originalText);
                            $btn.css('background', '#10b981');
                        }, 2000);
                    }.bind(this)).catch(function(err) {
                        console.error('Failed to copy: ', err);
                        alert('Failed to copy to clipboard. Please select and copy manually.');
                    });
                });
            }
            
            // Load sample sessions for debugging
            function loadSampleSessions(userId) {
                $.ajax({
                        url: '<?php echo rest_url('aph/v1/debug/database'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.tables && response.tables.jph_practice_sessions) {
                            const sessions = response.tables.jph_practice_sessions.user_data;
                            if (sessions && sessions.length > 0) {
                                let sessionText = '';
                                sessions.slice(0, 5).forEach((session, index) => {
                                    sessionText += `Session ${index + 1}: ${session.created_at} (${session.duration_minutes} min)\n`;
                                });
                                $('#debug-sessions').text(sessionText);
                            } else {
                                $('#debug-sessions').text('No sessions found for this user');
                            }
                        } else {
                            $('#debug-sessions').text('Failed to load session data');
                        }
                    },
                    error: function() {
                        $('#debug-sessions').text('Error loading session data');
                    }
                });
            }
            
            // Helper function to format dates
            function formatDate(dateString) {
                if (!dateString) return '';
                var date = new Date(dateString);
                return date.toLocaleDateString();
            }
            
            // Helper function to escape HTML
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
            
            // Initialize modal handlers
            function initModalHandlers() {
                // Close modal handlers
                $('.jph-modal-close, .jph-close').on('click', function() {
                    var $modal = $(this).closest('.jph-modal');
                    $modal.hide();
                    
                    // Reset log form when closing
                    if ($modal.attr('id') === 'jph-log-modal') {
                        $('#jph-log-form')[0].reset();
                        $('.duration-btn').removeClass('active');
                        $('.sentiment-option').removeClass('active');
                    }
                });
                
                // Click outside to close
                $('.jph-modal').on('click', function(e) {
                    if (e.target === this) {
                        $(this).hide();
                        
                        // Reset log form when closing
                        if ($(this).attr('id') === 'jph-log-modal') {
                            $('#jph-log-form')[0].reset();
                            $('.duration-btn').removeClass('active');
                            $('.sentiment-option').removeClass('active');
                        }
                    }
                });
                
                // Close modal on Escape key
                $(document).on('keydown', function(e) {
                    if (e.key === 'Escape') {
                        if ($('#jph-log-modal').is(':visible')) {
                            $('#jph-log-modal').hide();
                            // Reset log form when closing
                            $('#jph-log-form')[0].reset();
                            $('.duration-btn').removeClass('active');
                            $('.sentiment-option').removeClass('active');
                        }
                        if ($('#jph-edit-modal').is(':visible')) {
                            $('#jph-edit-modal').hide();
                        }
                        if ($('#jph-add-item-modal').is(':visible')) {
                            $('#jph-add-item-modal').hide();
                        }
                    }
                });
                
                // Add Practice Item button handler
                $('.jph-add-item-btn').on('click', function() {
                    $('#jph-add-item-modal').show();
                    loadLessonFavorites();
                });
                
                // Practice type selection
                $('.practice-type-card').on('click', function() {
                    $('.practice-type-card').removeClass('selected');
                    $(this).addClass('selected');
                    $(this).find('input[type="radio"]').prop('checked', true);
                    
                    var type = $(this).data('type');
                    if (type === 'favorite') {
                        $('#custom-title-group').hide();
                        $('#favorite-selection-group').show();
                        $('input[name="item_name"]').prop('required', false);
                        $('select[name="lesson_favorite"]').prop('required', true);
                    } else {
                        $('#favorite-selection-group').hide();
                        $('#custom-title-group').show();
                        $('select[name="lesson_favorite"]').prop('required', false);
                        $('input[name="item_name"]').prop('required', true);
                    }
                });
                
                // Handle lesson favorite selection
                $('#lesson-favorite-select').on('change', function() {
                    var selectedOption = $(this).find('option:selected');
                    if (selectedOption.val()) {
                        var title = selectedOption.data('title');
                        var description = selectedOption.data('description');
                        
                        // Auto-fill the form fields
                        $('input[name="item_name"]').val(title);
                        $('textarea[name="item_description"]').val(description);
                    }
                });
                
                // Add Practice Item form submission
                $('#add-practice-item-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    var $form = $(this);
                    var $button = $form.find('button[type="submit"]');
                    var formData = {};
                    
                    // Get form data
                    $form.find('input, textarea, select').each(function() {
                        if ($(this).attr('name')) {
                            formData[$(this).attr('name')] = $(this).val();
                        }
                    });
                    
                    // Validate required fields
                    if (!formData.item_name && !formData.lesson_favorite) {
                        alert('Please enter a title or select a lesson favorite.');
                        return;
                    }
                    
                    $button.prop('disabled', true).text('Adding...');
                    
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/practice-items'); ?>',
                        method: 'POST',
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                showMessage('Practice item added successfully!');
                                $form[0].reset();
                                $('#jph-add-item-modal').hide();
                                // Refresh the page to show the new item
                                location.reload();
                            } else {
                                showMessage('Error: ' + (response.message || 'Unknown error'), 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', xhr, status, error);
                            var errorMessage = 'Error adding practice item';
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                errorMessage = error;
                            }
                            showMessage(errorMessage, 'error');
                        },
                        complete: function() {
                            $button.prop('disabled', false).text('Add Practice Item');
                        }
                    });
                });
                
                // Edit Practice Item form submission
                $('#edit-practice-item-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    var $form = $(this);
                    var $button = $form.find('button[type="submit"]');
                    var formData = {};
                    var itemId = $('#edit-item-id').val();
                    
                    // Get form data
                    $form.find('input, textarea').each(function() {
                        if ($(this).attr('name') && $(this).attr('name') !== 'item_id') {
                            formData[$(this).attr('name')] = $(this).val();
                        }
                    });
                    
                    if (!formData.item_name) {
                        alert('Please enter a title.');
                        return;
                    }
                    
                    $button.prop('disabled', true).text('Updating...');
                    
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/practice-items/'); ?>' + itemId,
                        method: 'PUT',
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                showMessage('Practice item updated successfully!');
                                $('#jph-edit-modal').hide();
                                $form[0].reset();
                                // Update the item in the list
                                updateItemInList(itemId, formData.name, formData.description);
                            } else {
                                showMessage('Error: ' + (response.message || 'Unknown error'), 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Update Error:', xhr, status, error);
                            var errorMessage = 'Error updating practice item';
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                errorMessage = error;
                            }
                            showMessage(errorMessage, 'error');
                        },
                        complete: function() {
                            $button.prop('disabled', false).text('Update Practice Item');
                        }
                    });
                });
                
                // Delete practice item
                $(document).on('click', '.jph-delete-item-btn', function() {
                    var itemId = $(this).data('item-id');
                    var name = $(this).data('name');
                    
                    if (confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone. Note: Any practice sessions logged for this item will be preserved.')) {
                        $.ajax({
                            url: '<?php echo rest_url('aph/v1/practice-items/'); ?>' + itemId,
                            method: 'DELETE',
                            headers: {
                                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    showMessage('Practice item deleted successfully!');
                                    $('.jph-item[data-item-id="' + itemId + '"]').fadeOut(300, function() {
                                        $(this).remove();
                                        updateItemCount();
                                    });
                                } else {
                                    showMessage('Error: ' + (response.message || 'Unknown error'), 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Delete Error:', xhr, status, error);
                                var errorMessage = 'Error deleting practice item';
                                try {
                                    var response = JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    errorMessage = error;
                                }
                                showMessage(errorMessage, 'error');
                            }
                        });
                    }
                });
                
                // Edit practice item button handler
                $(document).on('click', '.jph-edit-item-btn', function() {
                    var itemId = $(this).data('item-id');
                    var name = $(this).data('name');
                    var description = $(this).data('description');
                    
                    $('#edit-item-id').val(itemId);
                    $('#edit-item-name').val(name);
                    $('#edit-item-description').val(description);
                    
                    $('#jph-edit-modal').show();
                });
            }
            
            // Load lesson favorites for practice item modal
            function loadLessonFavorites() {
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/lesson-favorites'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var select = $('#lesson-favorite-select');
                            select.empty();
                            
                            if (response.favorites.length === 0) {
                                select.append('<option value="">No lesson favorites found</option>');
                            } else {
                                select.append('<option value="">Select a lesson favorite...</option>');
                                $.each(response.favorites, function(index, favorite) {
                                    select.append('<option value="' + favorite.id + '" data-title="' + escapeHtml(favorite.title) + '" data-category="' + escapeHtml(favorite.category) + '" data-description="' + escapeHtml(favorite.description || '') + '">' + escapeHtml(favorite.title) + '</option>');
                                });
                            }
                        } else {
                            $('#lesson-favorite-select').html('<option value="">Error loading favorites</option>');
                        }
                    },
                    error: function() {
                        $('#lesson-favorite-select').html('<option value="">Error loading favorites</option>');
                    }
                });
            }
            
            // Update item in list after edit
            function updateItemInList(itemId, name, description) {
                var $item = $('.jph-item[data-item-id="' + itemId + '"]');
                $item.find('.item-card-header h4').text(name);
                $item.find('.item-description p').text(description || '');
            }
            
            // Update item count after delete
            function updateItemCount() {
                var itemCount = $('.jph-item').length;
                if (itemCount === 0) {
                    // Show empty state or reload page
                    location.reload();
                }
            }
            
            // Show message function
            function showMessage(message, type = 'success') {
                // Create message element
                var $message = $('<div class="jph-message jph-message-' + type + '">' + message + '</div>');
                
                // Add to page
                $('body').append($message);
                
                // Show with animation
                $message.fadeIn(300);
                
                // Hide after 3 seconds
                setTimeout(function() {
                    $message.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }
            
            // Initialize practice session handlers
            function initPracticeSessionHandlers() {
                // Export CSV functionality
                $(document).on('click', '#export-history-btn', function() {
                    exportPracticeHistory();
                });
                
                // Load more sessions functionality
                $(document).on('click', '#load-more-sessions-bottom', function() {
                    loadMorePracticeSessions();
                });
                
                // Open log practice modal
                $(document).on('click', '.jph-log-practice-btn', function() {
                    var itemId = $(this).data('item-id');
                    var itemName = $(this).closest('.jph-item').find('.item-card-header h4').text();
                    
                    $('#log-item-id').val(itemId);
                    $('#log-practice-item-name').text('Logging practice for: ' + itemName);
                    $('#jph-log-modal').show();
                    
                    // Set default sentiment score (good = 4) and duration (15 minutes)
                    $('input[name="sentiment_score"]').val('4');
                    $('input[name="duration_minutes"]').val('15');
                    $('.sentiment-option').removeClass('active');
                    $('.sentiment-option[data-score="4"]').addClass('active');
                    $('.duration-btn').removeClass('active');
                    $('.duration-btn[data-minutes="15"]').addClass('active');
                });
                
                // Duration button selection
                $(document).on('click', '.duration-btn', function() {
                    $('.duration-btn').removeClass('active');
                    $(this).addClass('active');
                    $('input[name="duration_minutes"]').val($(this).data('minutes'));
                });
                
                // Clear duration button selection when typing in custom field
                $(document).on('input', 'input[name="duration_minutes"]', function() {
                    $('.duration-btn').removeClass('active');
                });
                
                // Sentiment selection
                $(document).on('click', '.sentiment-option', function() {
                    $('.sentiment-option').removeClass('active');
                    $(this).addClass('active');
                    $('input[name="sentiment_score"]').val($(this).data('score'));
                });
                
                // Log practice session
                $('#jph-log-form').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $button = $form.find('button[type="submit"]');
                    
                    // Disable form during submission
                    $form.addClass('jph-loading');
                    $button.prop('disabled', true).text('Logging...');
                    
                    var formData = {
                        practice_item_id: $('#log-item-id').val(),
                        duration_minutes: $('input[name="duration_minutes"]').val(),
                        sentiment_score: $('input[name="sentiment_score"]').val(),
                        improvement_detected: $('input[name="improvement_detected"]').is(':checked'),
                        notes: $('textarea[name="notes"]').val()
                    };
                    
                    console.log('Log form data:', formData);
                    
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/practice-sessions'); ?>',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        success: function(response) {
                            if (response.success) {
                                var message = 'Practice session logged successfully!';
                                
                                // Show XP earned
                                if (response.xp_earned) {
                                    message += ' +' + response.xp_earned + ' XP';
                                }
                                
                                // Show level up message
                                if (response.level_up && response.level_up.leveled_up) {
                                    message += ' 🎉 LEVEL UP! You reached level ' + response.level_up.new_level + '!';
                                }
                                
                                // Show streak update
                                if (response.streak_update && response.streak_update.streak_updated) {
                                    if (response.streak_update.streak_continued) {
                                        message += ' STREAK ' + response.streak_update.current_streak + '-day streak!';
                                    } else {
                                        message += ' STREAK New streak started!';
                                    }
                                }
                                
                                showMessage(message);
                                $('#jph-log-modal').hide();
                                $form[0].reset();
                                // Reset UI elements
                                $('.sentiment-option').removeClass('active');
                                $('.duration-btn').removeClass('active');
                                location.reload(); // Refresh to show updated stats
                            } else {
                                showMessage('Error: ' + (response.message || 'Unknown error'), 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Log Error:', xhr, status, error);
                            var errorMessage = 'Error logging practice session';
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                errorMessage = error;
                            }
                            showMessage(errorMessage, 'error');
                        },
                        complete: function() {
                            $form.removeClass('jph-loading');
                            $button.prop('disabled', false).text('Log Practice');
                        }
                    });
                });
            }
            
            // Initialize shield handlers
            function initShieldHandlers() {
                // Shield accordion toggle
                jQuery(document).on('click', '.shield-accordion-header', function(e) {
                    e.preventDefault();
                    console.log('Shield accordion clicked');
                    
                    const content = jQuery('#shield-accordion-content');
                    const header = jQuery(this);
                    const icon = header.find('.shield-toggle-icon');
                    
                    console.log('Content found:', content.length);
                    console.log('Header found:', header.length);
                    console.log('Icon found:', icon.length);
                    
                    if (content.is(':visible')) {
                        content.slideUp(300);
                        header.removeClass('active');
                        icon.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    } else {
                        content.slideDown(300);
                        header.addClass('active');
                        icon.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    }
                });
                
                // Purchase shield button
                jQuery(document).on('click', '#purchase-shield-btn', function() {
                    const cost = jQuery(this).data('cost');
                    const nonce = jQuery(this).data('nonce');
                    
                    // Check current shield count from the display
                    const shieldCountText = jQuery('#shield-count').text();
                    const currentShieldCount = parseInt(shieldCountText) || 0;
                    
                    console.log('Current shield count from display:', currentShieldCount);
                    console.log('Button data:', { cost, nonce });
                    
                    // Prevent purchase if already at max shields
                    if (currentShieldCount >= 3) {
                        alert('You already have the maximum number of shields (3). You cannot purchase more shields.');
                        return;
                    }
                    
                    if (!confirm(`Purchase Streak Shield for ${cost} gems?`)) {
                        return;
                    }
                    
                    jQuery.ajax({
                        url: '<?php echo rest_url('aph/v1/purchase-shield'); ?>',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: {
                            cost: cost,
                            nonce: nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update shield count display
                                jQuery('#shield-count').text(response.data.new_shield_count);
                                
                                // Update gem balance in stats
                                jQuery('.stat-value').each(function() {
                                    if (jQuery(this).text().includes('💎')) {
                                        jQuery(this).text(response.data.new_gem_balance + ' 💎');
                                    }
                                });
                                
                                // Update button state if at max shields
                                if (response.data.new_shield_count >= 3) {
                                    jQuery('#purchase-shield-btn').prop('disabled', true).text('Max Shields (3)');
                                }
                                
                                alert('Shield purchased successfully!');
                            } else {
                                alert('Error purchasing shield: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('JPH: Purchase Shield error:', error);
                            console.error('JPH: XHR response:', xhr.responseText);
                            console.error('JPH: Status:', status);
                            
                            // Try to parse the error response
                            let errorMessage = 'Network error. Please try again.';
                            
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                try {
                                    const errorData = JSON.parse(xhr.responseText);
                                    if (errorData.message) {
                                        errorMessage = errorData.message;
                                    }
                                } catch (e) {
                                    // If it's not JSON, it might be an HTML error page
                                    if (xhr.responseText.includes('critical error')) {
                                        errorMessage = 'Server error occurred. Please try again later.';
                                    }
                                }
                            }
                            
                            alert(errorMessage);
                        }
                    });
                });
                
                // Insufficient gems button
                jQuery(document).on('click', '#purchase-shield-btn-insufficient', function() {
                    const cost = jQuery(this).data('cost');
                    const gemBalance = jQuery(this).data('gem-balance');
                    const needed = cost - gemBalance;
                    
                    alert(`Insufficient gems! You have ${gemBalance} 💎 but need ${cost} 💎. You need ${needed} more gems to purchase a shield.`);
                });
            }
            
            // Initialize stats handlers
            function initStatsHandlers() {
                // Stats explanation button
                $('#jph-stats-explanation-btn').on('click', function() {
                    $('#jph-stats-explanation-modal').show();
                });
                
                // Close modal when clicking the X
                $('#jph-stats-explanation-modal .jph-close').on('click', function() {
                    $('#jph-stats-explanation-modal').hide();
                });
                
                // Close modal when clicking outside
                $(window).on('click', function(event) {
                    if (event.target.id === 'jph-stats-explanation-modal') {
                        $('#jph-stats-explanation-modal').hide();
                    }
                });
            }
            
            // Initialize display name handlers
            function initDisplayNameHandlers() {
                // Check if user needs welcome modal
                checkFirstTimeUser();
                
                // Edit name button (subtle)
                $('#jph-edit-name-btn').on('click', function() {
                    openDisplayNameModal();
                });
                
                // Leaderboard button
                $('#jph-leaderboard-btn').on('click', function() {
                    window.location.href = '/leaderboard';
                });
                
                // Close modal when clicking the X
                $('#jph-display-name-modal .jph-close').on('click', function() {
                    $('#jph-display-name-modal').hide();
                });
                
                // Cancel button
                $('#jph-cancel-display-name').on('click', function() {
                    $('#jph-display-name-modal').hide();
                });
                
                // Save display name
                $('#jph-save-display-name').on('click', function() {
                    saveDisplayName();
                });
                
                // Welcome modal save button
                $('#jph-save-welcome-name').on('click', function() {
                    saveWelcomeDisplayName();
                });
                
                // Enter key to save in welcome modal
                $('#jph-welcome-display-name-input').on('keypress', function(e) {
                    if (e.which === 13) { // Enter key
                        saveWelcomeDisplayName();
                    }
                });
                
                // Close modal when clicking outside
                $(window).on('click', function(event) {
                    if (event.target.id === 'jph-display-name-modal') {
                        $('#jph-display-name-modal').hide();
                    }
                });
                
                // Enter key to save
                $('#jph-display-name-input').on('keypress', function(e) {
                    if (e.which === 13) { // Enter key
                        saveDisplayName();
                    }
                });
            }
            
            // Load current display name
            function loadCurrentDisplayName() {
                const wpDisplayName = '<?php echo esc_js(wp_get_current_user()->display_name ?: wp_get_current_user()->user_login); ?>';
                
                // Get current user's custom display name from user stats
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/user-stats'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data.display_name && response.data.display_name.trim() !== '') {
                            // Use custom display name if set
                            $('#jph-display-name-input').val(response.data.display_name);
                        } else {
                            // Use WordPress display name as fallback
                            $('#jph-display-name-input').val(wpDisplayName);
                        }
                    },
                    error: function() {
                        // Use WordPress display name as fallback
                        $('#jph-display-name-input').val(wpDisplayName);
                        console.log('Failed to load custom display name, using WordPress name');
                    }
                });
            }
            
            // Save display name
            function saveDisplayName() {
                const displayName = $('#jph-display-name-input').val().trim();
                const saveBtn = $('#jph-save-display-name');
                const messageDiv = $('#jph-display-name-message');
                
                // Disable save button
                saveBtn.prop('disabled', true).text('Saving...');
                messageDiv.hide();
                
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/leaderboard/display-name'); ?>',
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    data: {
                        display_name: displayName
                    },
                    success: function(response) {
                        if (response.success) {
                            showModalMessage('Display name updated successfully!', 'success');
                            $('#jph-display-name-modal').hide();
                            // Update welcome title with new name
                            updateWelcomeTitle(displayName);
                        } else {
                            showModalMessage('Failed to update display name: ' + (response.message || 'Unknown error'), 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to update display name';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showModalMessage(errorMessage, 'error');
                    },
                    complete: function() {
                        saveBtn.prop('disabled', false).text('Save Name');
                    }
                });
            }
            
            // Show message in display name modal (use main notification system)
            function showModalMessage(text, type) {
                // Use the main showMessage function for consistent styling
                showMessage(text, type);
            }
            
            // Check if user is first-time user
            function checkFirstTimeUser() {
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/user-stats'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            const hasDisplayName = response.data.display_name && response.data.display_name.trim() !== '';
                            const hasPracticeSessions = response.data.total_sessions > 0;
                            
                            // Show welcome modal if no display name set and no practice sessions
                            if (!hasDisplayName && !hasPracticeSessions) {
                                showWelcomeModal();
                            } else {
                                // Update welcome title with display name
                                updateWelcomeTitle(response.data.display_name);
                            }
                        }
                    },
                    error: function() {
                        // On error, show welcome modal to be safe
                        showWelcomeModal();
                    }
                });
            }
            
            // Show welcome modal
            function showWelcomeModal() {
                const wpDisplayName = '<?php echo esc_js(wp_get_current_user()->display_name ?: wp_get_current_user()->user_login); ?>';
                $('#jph-welcome-display-name-input').val(wpDisplayName);
                $('#jph-welcome-modal').show();
            }
            
            // Save welcome display name
            function saveWelcomeDisplayName() {
                const displayName = $('#jph-welcome-display-name-input').val().trim();
                const saveBtn = $('#jph-save-welcome-name');
                
                // Disable save button
                saveBtn.prop('disabled', true).text('Setting up...');
                
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/leaderboard/display-name'); ?>',
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    data: {
                        display_name: displayName
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#jph-welcome-modal').hide();
                            updateWelcomeTitle(displayName);
                            showMessage('Welcome to your Practice Hub! 🎹', 'success');
                        } else {
                            showMessage('Failed to set display name: ' + (response.message || 'Unknown error'), 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to set display name';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showMessage(errorMessage, 'error');
                    },
                    complete: function() {
                        saveBtn.prop('disabled', false).text('Let\'s Go!');
                    }
                });
            }
            
            // Update welcome title with display name
            function updateWelcomeTitle(displayName) {
                const wpDisplayName = '<?php echo esc_js(wp_get_current_user()->display_name ?: wp_get_current_user()->user_login); ?>';
                const nameToShow = displayName && displayName.trim() !== '' ? displayName : wpDisplayName;
                
                $('#jph-welcome-title').text('🎹 Welcome, ' + nameToShow + '!');
                $('#jph-edit-name-btn').show();
            }
            
            // Open display name modal for editing
            function openDisplayNameModal() {
                // Show modal first
                $('#jph-display-name-modal').show();
                
                // Load current display name (which will set the appropriate value)
                loadCurrentDisplayName();
            }
            
            // Initialize drag and drop for practice items
            function initDragAndDrop() {
                let draggedElement = null;
                
                // Remove any existing drag classes first
                $('.sortable-practice-item').removeClass('dragging drag-over');
                
                // Make practice items draggable
                $('.sortable-practice-item').on('dragstart', function(e) {
                    draggedElement = this;
                    $(this).addClass('dragging');
                    e.originalEvent.dataTransfer.effectAllowed = 'move';
                });
                
                $('.sortable-practice-item').on('dragend', function() {
                    // Remove dragging class from all elements
                    $('.sortable-practice-item').removeClass('dragging drag-over');
                    draggedElement = null;
                });
                
                // Handle drop zones
                $('.sortable-practice-item, .sortable-empty-slot').on('dragover', function(e) {
                    e.preventDefault();
                    e.originalEvent.dataTransfer.dropEffect = 'move';
                    
                    // Add visual feedback for drop target
                    if (this !== draggedElement) {
                        $(this).addClass('drag-over');
                    }
                });
                
                $('.sortable-practice-item, .sortable-empty-slot').on('dragleave', function(e) {
                    // Remove drag-over class when leaving
                    $(this).removeClass('drag-over');
                });
                
                $('.sortable-practice-item, .sortable-empty-slot').on('drop', function(e) {
                    e.preventDefault();
                    
                    // Remove all drag classes
                    $('.sortable-practice-item').removeClass('dragging drag-over');
                    
                    if (draggedElement && draggedElement !== this) {
                        // Swap the elements
                        const draggedHTML = draggedElement.outerHTML;
                        const targetHTML = this.outerHTML;
                        
                        $(draggedElement).replaceWith(targetHTML);
                        $(this).replaceWith(draggedHTML);
                        
                        // Re-initialize drag and drop for new elements
                        setTimeout(function() {
                            initDragAndDrop();
                        }, 100);
                        
                        // Update order in database
                        updatePracticeItemOrder();
                    }
                    
                    draggedElement = null;
                });
            }
            
            // Update practice item order
            function updatePracticeItemOrder() {
                const itemIds = [];
                $('.sortable-practice-item').each(function() {
                    const itemId = $(this).data('item-id');
                    if (itemId) {
                        itemIds.push(itemId);
                    }
                });
                
                // Send to server to update order
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    method: 'POST',
                    data: {
                        action: 'jph_update_practice_item_order',
                        item_ids: itemIds,
                        nonce: '<?php echo wp_create_nonce('jph_update_practice_item_order'); ?>'
                    },
                    success: function(response) {
                        if (!response.success) {
                            console.error('Failed to update practice item order');
                        }
                    },
                    error: function() {
                        console.error('Error updating practice item order');
                    }
                });
            }
            
            // Initialize delete session handlers
            function initDeleteSessionHandlers() {
                $(document).on('click', '.jph-delete-session-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const $btn = $(this);
                    const sessionId = $btn.data('session-id');
                    const itemName = $btn.data('item-name');
                    
                    if (confirm('Are you sure you want to delete this practice session for "' + itemName + '"? This action cannot be undone.')) {
                        $.ajax({
                            url: '<?php echo rest_url('aph/v1/practice-sessions/'); ?>' + sessionId,
                            method: 'DELETE',
                            headers: {
                                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Remove the session row from the table
                                    $btn.closest('.practice-history-item').fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                    // Optionally, update stats via AJAX later; avoid full page reload
                                } else {
                                    alert('Failed to delete practice session: ' + (response.message || 'Unknown error'));
                                }
                            },
                            error: function() {
                                alert('Error deleting practice session');
                            }
                        });
                    }
                });
            }
            
            // Initialize all functionality
            initDragAndDrop();
            initDeleteSessionHandlers();
            
        });
        
        // Debug functionality removed - no longer needed
        </script>
        <?php

        return ob_get_clean();
    }
    
    /**
     * Render the leaderboard
     */
    public function render_leaderboard($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'limit' => 50,
            'sort_by' => 'total_xp',
            'show_user_position' => 'true',
            'show_stats' => 'true'
        ), $atts);
        
        // Enqueue scripts and styles
        wp_enqueue_script('jquery');
        
        ob_start();
        ?>
        <div class="jph-leaderboard" data-limit="<?php echo esc_attr($atts['limit']); ?>" data-sort-by="<?php echo esc_attr($atts['sort_by']); ?>">
            
            <!-- Loading State -->
            <div id="jph-leaderboard-loading" class="jph-loading">
                <div class="jph-spinner"></div>
                <p>Loading leaderboard...</p>
            </div>
            
            <!-- Error State -->
            <div id="jph-leaderboard-error" class="jph-error" style="display: none;">
                <div class="jph-error-content">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <p>Failed to load leaderboard. Please try again later.</p>
                </div>
            </div>
            
            <!-- Leaderboard Content -->
            <div id="jph-leaderboard-content" style="display: none;">
                
                <!-- Header -->
                <div class="jph-leaderboard-header">
                    <h2>🏆 Practice Leaderboard</h2>
                    
                    <!-- Data Update Notice -->
                    <div class="jph-leaderboard-notice">
                        <div class="jph-notice-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="jph-notice-text">
                            <strong>Data Update:</strong> Leaderboard data is cached and may take a few minutes to reflect recent practice sessions.
                        </div>
                    </div>
                    
                    <!-- Ranking Help -->
                    <div class="jph-leaderboard-sort">
                        <button id="jph-ranking-explanation-btn" class="jph-btn jph-btn-secondary jph-ranking-help-btn" title="How does ranking work?">
                            <span class="btn-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                </svg>
                            </span>
                            How does ranking work?
                        </button>
                    </div>
                </div>
                
                
                <!-- Leaderboard Stats -->
                <?php if ($atts['show_stats'] === 'true'): ?>
                <div id="jph-leaderboard-stats" class="jph-leaderboard-stats" style="display: none;">
                    <div class="jph-stats-grid">
                        <?php if (is_user_logged_in() && $atts['show_user_position'] === 'true'): ?>
                        <div class="jph-stat-item">
                            <span class="jph-stat-value" id="jph-user-rank">--</span>
                            <span class="jph-stat-label">Your Position</span>
                        </div>
                        <?php endif; ?>
                        <div class="jph-stat-item">
                            <span class="jph-stat-value" id="jph-total-users">--</span>
                            <span class="jph-stat-label">Total Users</span>
                        </div>
                        <div class="jph-stat-item">
                            <span class="jph-stat-value" id="jph-leaderboard-users">--</span>
                            <span class="jph-stat-label">On Leaderboard</span>
                        </div>
                        <div class="jph-stat-item">
                            <span class="jph-stat-value" id="jph-avg-xp">--</span>
                            <span class="jph-stat-label">Average XP</span>
                        </div>
                        <div class="jph-stat-item">
                            <span class="jph-stat-value" id="jph-max-xp">--</span>
                            <span class="jph-stat-label">Highest XP</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Leaderboard Table -->
                <div class="jph-leaderboard-table-container">
                    <table class="jph-leaderboard-table">
                        <thead>
                            <tr>
                                <th class="jph-rank-col">Rank</th>
                                <th class="jph-name-col">Name</th>
                                <th class="jph-xp-col sortable" data-sort="total_xp">XP</th>
                                <th class="jph-level-col sortable" data-sort="current_level">Level</th>
                                <th class="jph-streak-col sortable" data-sort="current_streak">Streak</th>
                                <th class="jph-sessions-col sortable" data-sort="total_sessions">Sessions</th>
                                <th class="jph-minutes-col sortable" data-sort="total_minutes">Minutes</th>
                                <th class="jph-badges-col sortable" data-sort="badges_earned">Badges</th>
                            </tr>
                        </thead>
                        <tbody id="jph-leaderboard-tbody">
                            <!-- Leaderboard data will be populated here -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div id="jph-leaderboard-pagination" class="jph-pagination" style="display: none;">
                    <button id="jph-prev-page" class="jph-btn jph-btn-secondary" disabled>
                        <i class="fa-solid fa-chevron-left"></i> Previous
                    </button>
                    <span id="jph-page-info" class="jph-page-info">Page 1 of 1</span>
                    <button id="jph-next-page" class="jph-btn jph-btn-secondary" disabled>
                        Next <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
                
            </div>
            
        </div>
        
        <style>
        .jph-leaderboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .jph-leaderboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .jph-leaderboard-header h2 {
            margin: 0;
            color: #1f2937;
            font-size: 28px;
            font-weight: 700;
        }
        
        .jph-leaderboard-notice {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            padding: 12px 16px;
            margin: 15px 0;
            color: #1e40af;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .jph-notice-icon {
            flex-shrink: 0;
            color: #3b82f6;
        }
        
        .jph-notice-text {
            flex: 1;
        }
        
        .jph-notice-text strong {
            font-weight: 600;
        }
        
        .jph-leaderboard-sort {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .jph-ranking-help-btn {
            background: #f04e23;
            color: white;
            border: 1px solid #f04e23;
        }
        
        .jph-ranking-help-btn:hover {
            background: #d63e1a;
            border-color: #d63e1a;
        }
        
        /* Only apply these styles when inside the modal */
        #jph-ranking-explanation-modal .jph-ranking-explanation {
            line-height: 1.6;
        }
        
        #jph-ranking-explanation-modal .jph-ranking-explanation h4 {
            color: #0f766e;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        
        #jph-ranking-explanation-modal .jph-ranking-explanation h5 {
            color: #374151;
            margin: 20px 0 10px 0;
            font-size: 1em;
        }
        
        #jph-ranking-explanation-modal .explanation-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #0f766e;
        }
        
        #jph-ranking-explanation-modal .explanation-section ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        #jph-ranking-explanation-modal .explanation-section li {
            margin-bottom: 8px;
        }
        
        .jph-leaderboard-sort label {
            font-weight: 600;
            color: #374151;
        }
        
        .jph-sort-select {
            padding: 8px 12px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        
        .jph-sort-select:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        
        .jph-leaderboard-stats {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .jph-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        
        .jph-stat-item {
            text-align: center;
        }
        
        .jph-stat-value {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .jph-stat-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }
        
        .jph-leaderboard-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }
        
        .jph-leaderboard-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .jph-leaderboard-table th {
            background: #f8fafc;
            padding: 15px 12px;
            text-align: center;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            cursor: pointer;
            user-select: none;
            position: relative;
        }
        
        .jph-leaderboard-table th.sortable:hover {
            background: #f1f5f9;
        }
        
        .jph-leaderboard-table th.sortable::after {
            content: '↕';
            position: absolute;
            right: 8px;
            opacity: 0.5;
            font-size: 12px;
        }
        
        .jph-leaderboard-table th.sortable.sort-asc::after {
            content: '↑';
            opacity: 1;
            color: #3b82f6;
        }
        
        .jph-leaderboard-table th.sortable.sort-desc::after {
            content: '↓';
            opacity: 1;
            color: #3b82f6;
        }
        
        .jph-leaderboard-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        
        .jph-leaderboard-table tbody tr:hover {
            background: #f9fafb;
        }
        
        .jph-leaderboard-table tbody tr.current-user {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            color: white;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(15, 118, 110, 0.3);
        }
        
        .jph-leaderboard-table tbody tr.current-user:hover {
            background: linear-gradient(135deg, #0d6b63 0%, #0f766e 100%);
        }
        
        .jph-rank-col { width: 80px; text-align: center; }
        .jph-name-col { width: 200px; text-align: center; }
        .jph-xp-col { width: 100px; text-align: center; }
        .jph-level-col { width: 80px; text-align: center; }
        .jph-streak-col { width: 100px; text-align: center; }
        .jph-sessions-col { width: 100px; text-align: center; }
        .jph-minutes-col { width: 100px; text-align: center; }
        .jph-badges-col { width: 80px; text-align: center; }
        
        .jph-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 25px;
        }
        
        .jph-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .jph-btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        
        .jph-btn-secondary:hover:not(:disabled) {
            background: #e5e7eb;
        }
        
        .jph-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .jph-page-info {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }
        
        .jph-loading {
            text-align: center;
            padding: 40px 20px;
        }
        
        .jph-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .jph-error {
            text-align: center;
            padding: 40px 20px;
            color: #dc2626;
        }
        
        .jph-error-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .jph-leaderboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .jph-leaderboard-notice {
                margin: 10px 0;
                padding: 10px 12px;
                font-size: 13px;
            }
            
            .jph-leaderboard-table-container {
                overflow-x: auto;
            }
            
            .jph-leaderboard-table {
                min-width: 600px;
            }
            
            .jph-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            let currentPage = 0;
            let currentSort = '<?php echo esc_js($atts['sort_by']); ?>';
            // Ensure default sort is descending for XP
            if (currentSort === 'total_xp' && !currentSort.startsWith('-')) {
                currentSort = '-' + currentSort;
            }
            let currentLimit = <?php echo intval($atts['limit']); ?>;
            let isLoading = false;
            
            // Initialize leaderboard
            loadLeaderboard();
            loadUserPosition();
            loadLeaderboardStats();
            updateSortIndicators();
            
            // Ranking explanation button
            $('#jph-ranking-explanation-btn').on('click', function(e) {
                e.preventDefault();
                alert('How Does Ranking Work?\n\n' +
                    '• Users are ranked by their Total XP in descending order\n' +
                    '• Higher XP = Better rank (lower number)\n' +
                    '• Click any column header to sort by that metric\n' +
                    '• Click again to reverse the order\n' +
                    '• Your row is highlighted so you can easily find yourself\n' +
                    '• Only users who choose to appear on the leaderboard are included');
            });
            
            // Header click sorting
            $('.jph-leaderboard-table th.sortable').on('click', function() {
                const sortField = $(this).data('sort');
                if (sortField === currentSort || '-' + sortField === currentSort) {
                    // Toggle sort direction if clicking same field
                    currentSort = currentSort.startsWith('-') ? sortField : '-' + sortField;
                } else {
                    // Always start with descending (highest to lowest)
                    currentSort = '-' + sortField;
                }
                
                // Update visual indicators
                updateSortIndicators();
                
                // Reload data
                currentPage = 0;
                loadLeaderboard();
                loadUserPosition();
            });
            
            
            // Pagination handlers
            $('#jph-prev-page').on('click', function() {
                if (currentPage > 0 && !isLoading) {
                    currentPage--;
                    loadLeaderboard();
                }
            });
            
            $('#jph-next-page').on('click', function() {
                if (!isLoading) {
                    currentPage++;
                    loadLeaderboard();
                }
            });
            
            // Update sort indicators
            function updateSortIndicators() {
                $('.jph-leaderboard-table th.sortable').removeClass('sort-asc sort-desc');
                
                const sortField = currentSort.startsWith('-') ? currentSort.substring(1) : currentSort;
                const sortDirection = currentSort.startsWith('-') ? 'desc' : 'asc';
                
                $(`.jph-leaderboard-table th[data-sort="${sortField}"]`).addClass('sort-' + sortDirection);
            }
            
            function loadLeaderboard() {
                if (isLoading) return;
                
                isLoading = true;
                showLoading();
                
                const offset = currentPage * currentLimit;
                
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/leaderboard'); ?>',
                    method: 'GET',
                    data: {
                        limit: currentLimit,
                        offset: offset,
                        sort_by: currentSort.startsWith('-') ? currentSort.substring(1) : currentSort,
                        sort_order: currentSort.startsWith('-') ? 'desc' : 'asc'
                    },
                    success: function(response) {
                        if (response.success) {
                            renderLeaderboard(response.data);
                            updatePagination(response.pagination);
                            hideLoading();
                        } else {
                            showError('Failed to load leaderboard');
                        }
                    },
                    error: function() {
                        showError('Failed to load leaderboard');
                    },
                    complete: function() {
                        isLoading = false;
                    }
                });
            }
            
            function loadUserPosition() {
                <?php if (is_user_logged_in() && $atts['show_user_position'] === 'true'): ?>
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/leaderboard/position'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    data: {
                        sort_by: currentSort.startsWith('-') ? currentSort.substring(1) : currentSort,
                        sort_order: currentSort.startsWith('-') ? 'desc' : 'asc'
                    },
                    success: function(response) {
                        if (response.success && response.data.position) {
                            $('#jph-user-rank').text(response.data.position);
                        }
                    },
                    error: function() {
                        // Silently fail - user position is not critical
                        console.log('Failed to load user position');
                    }
                });
                <?php endif; ?>
            }
            
            function loadLeaderboardStats() {
                <?php if ($atts['show_stats'] === 'true'): ?>
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/leaderboard/stats'); ?>',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const stats = response.data;
                            $('#jph-total-users').text(Math.round(stats.total_users || 0));
                            $('#jph-leaderboard-users').text(Math.round(stats.leaderboard_users || 0));
                            $('#jph-avg-xp').text(Math.round(stats.avg_xp || 0));
                            $('#jph-max-xp').text(Math.round(stats.max_xp || 0));
                            $('#jph-leaderboard-stats').show();
                        }
                    }
                });
                <?php endif; ?>
            }
            
            function renderLeaderboard(data) {
                const tbody = $('#jph-leaderboard-tbody');
                tbody.empty();
                
                if (data.length === 0) {
                    tbody.append('<tr><td colspan="8" style="text-align: center; padding: 40px;">No users found</td></tr>');
                    return;
                }
                
                // Get current user ID for highlighting
                const currentUserId = <?php echo is_user_logged_in() ? get_current_user_id() : 'null'; ?>;
                
                data.forEach(function(user, index) {
                    const row = $('<tr>');
                    
                    // Add current-user class if this is the logged-in user
                    if (currentUserId && user.user_id == currentUserId) {
                        row.addClass('current-user');
                    }
                    
                    // Rank with medal emoji for top 3
                    let rankDisplay = user.position;
                    if (user.position === 1) rankDisplay = '🥇 1';
                    else if (user.position === 2) rankDisplay = '🥈 2';
                    else if (user.position === 3) rankDisplay = '🥉 3';
                    
                    row.append('<td class="jph-rank-col">' + rankDisplay + '</td>');
                    row.append('<td class="jph-name-col">' + escapeHtml(user.leaderboard_name) + '</td>');
                    row.append('<td class="jph-xp-col">' + numberFormat(user.total_xp) + '</td>');
                    row.append('<td class="jph-level-col">' + user.current_level + '</td>');
                    row.append('<td class="jph-streak-col">' + user.current_streak + '</td>');
                    row.append('<td class="jph-sessions-col">' + user.total_sessions + '</td>');
                    row.append('<td class="jph-minutes-col">' + numberFormat(user.total_minutes) + '</td>');
                    row.append('<td class="jph-badges-col">' + user.badges_earned + '</td>');
                    
                    tbody.append(row);
                });
                
                $('#jph-leaderboard-content').show();
            }
            
            function updatePagination(pagination) {
                const totalPages = Math.ceil(pagination.offset / pagination.limit) + 1;
                const currentPageNum = Math.floor(pagination.offset / pagination.limit) + 1;
                
                $('#jph-page-info').text('Page ' + currentPageNum + ' of ' + totalPages);
                
                $('#jph-prev-page').prop('disabled', currentPageNum <= 1);
                $('#jph-next-page').prop('disabled', currentPageNum >= totalPages);
                
                if (totalPages > 1) {
                    $('#jph-leaderboard-pagination').show();
                } else {
                    $('#jph-leaderboard-pagination').hide();
                }
            }
            
            function showLoading() {
                $('#jph-leaderboard-loading').show();
                $('#jph-leaderboard-error').hide();
                $('#jph-leaderboard-content').hide();
            }
            
            function hideLoading() {
                $('#jph-leaderboard-loading').hide();
            }
            
            function showError(message) {
                $('#jph-leaderboard-loading').hide();
                $('#jph-leaderboard-content').hide();
                $('#jph-leaderboard-error p').text(message);
                $('#jph-leaderboard-error').show();
            }
            
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            function numberFormat(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
        });
        </script>
        
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render leaderboard widget
     */
    public function render_leaderboard_widget($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'limit' => '10',
            'sort_by' => 'total_xp',
            'sort_order' => 'desc',
            'show' => 'rank,name,xp,level',
            'style' => 'compact',
            'title' => 'Leaderboard',
            'show_title' => 'true',
            'highlight_user' => 'true',
            'show_practice_hub_link' => 'false'
        ), $atts);
        
        // Validate parameters
        $limit = max(1, min(50, intval($atts['limit'])));
        $allowed_sorts = array('total_xp', 'current_level', 'current_streak', 'badges_earned');
        $sort_by = in_array($atts['sort_by'], $allowed_sorts) ? $atts['sort_by'] : 'total_xp';
        $sort_order = strtoupper($atts['sort_order']) === 'ASC' ? 'ASC' : 'DESC';
        
        // Parse show fields
        $show_fields = array_map('trim', explode(',', $atts['show']));
        $allowed_fields = array('rank', 'name', 'xp', 'level', 'streak', 'badges');
        $show_fields = array_intersect($show_fields, $allowed_fields);
        
        if (empty($show_fields)) {
            $show_fields = array('rank', 'name', 'xp', 'level');
        }
        
        // Get leaderboard data
        $leaderboard = $this->database->get_leaderboard($limit, 0, $sort_by, $sort_order);
        
        if (empty($leaderboard)) {
            return '<div class="jph-leaderboard-widget jph-no-data">No leaderboard data available.</div>';
        }
        
        // Get current user ID for highlighting
        $current_user_id = is_user_logged_in() ? get_current_user_id() : 0;
        
        // Enqueue styles
        wp_enqueue_style('jph-widgets', plugin_dir_url(__FILE__) . '../assets/css/widgets.css', array(), '1.0.0');
        
        ob_start();
        ?>
        <div class="jph-leaderboard-widget jph-leaderboard-widget-<?php echo esc_attr($atts['style']); ?>">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h3 class="jph-widget-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <!-- Data Update Notice -->
            <div class="jph-widget-notice">
                <div class="jph-widget-notice-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="jph-widget-notice-text">
                    Data may take a few minutes to update
                </div>
            </div>
            
            <div class="jph-leaderboard-table">
                <table>
                    <thead>
                        <tr>
                            <?php if (in_array('rank', $show_fields)): ?>
                                <th class="jph-rank-col">Rank</th>
                            <?php endif; ?>
                            <?php if (in_array('name', $show_fields)): ?>
                                <th class="jph-name-col">Name</th>
                            <?php endif; ?>
                            <?php if (in_array('xp', $show_fields)): ?>
                                <th class="jph-xp-col">XP</th>
                            <?php endif; ?>
                            <?php if (in_array('level', $show_fields)): ?>
                                <th class="jph-level-col">Level</th>
                            <?php endif; ?>
                            <?php if (in_array('streak', $show_fields)): ?>
                                <th class="jph-streak-col">Streak</th>
                            <?php endif; ?>
                            <?php if (in_array('badges', $show_fields)): ?>
                                <th class="jph-badges-col">Badges</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaderboard as $index => $user): ?>
                            <?php 
                            $is_current_user = ($atts['highlight_user'] === 'true' && $current_user_id && $user['user_id'] == $current_user_id);
                            $row_class = $is_current_user ? 'jph-current-user' : '';
                            ?>
                            <tr class="<?php echo $row_class; ?>">
                                <?php if (in_array('rank', $show_fields)): ?>
                                    <td class="jph-rank-col">
                                        <div class="jph-rank">
                                            <?php if ($index < 3): ?>
                                                <span class="jph-rank-medal jph-rank-<?php echo $index + 1; ?>">
                                                    <?php if ($index === 0): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                                        </svg>
                                                    <?php elseif ($index === 1): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M15.75 4.5c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M15.75 4.5l0 0a4.5 4.5 0 0 0-9 0l0 0" />
                                                        </svg>
                                                    <?php else: ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M15.75 4.5c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M15.75 4.5l0 0a4.5 4.5 0 0 0-9 0l0 0" />
                                                        </svg>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="jph-rank-number"><?php echo $index + 1; ?></span>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <?php if (in_array('name', $show_fields)): ?>
                                    <td class="jph-name-col">
                                        <div class="jph-user-name">
                                            <?php echo esc_html($user['leaderboard_name']); ?>
                                            <?php if ($is_current_user): ?>
                                                <span class="jph-you-badge">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <?php if (in_array('xp', $show_fields)): ?>
                                    <td class="jph-xp-col">
                                        <div class="jph-stat-value">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                                            </svg>
                                            <?php echo number_format($user['total_xp']); ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <?php if (in_array('level', $show_fields)): ?>
                                    <td class="jph-level-col">
                                        <div class="jph-stat-value">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                            </svg>
                                            <?php echo $user['current_level']; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <?php if (in_array('streak', $show_fields)): ?>
                                    <td class="jph-streak-col">
                                        <div class="jph-stat-value">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                                            </svg>
                                            <?php echo $user['current_streak']; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <?php if (in_array('badges', $show_fields)): ?>
                                    <td class="jph-badges-col">
                                        <div class="jph-stat-value">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path>
                                            </svg>
                                            <?php echo $user['badges_earned']; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($atts['show_practice_hub_link'] === 'true'): ?>
                <div class="jph-widget-footer">
                    <a href="<?php echo esc_url($this->get_practice_hub_url()); ?>" class="jph-practice-hub-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="jph-hub-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                        </svg>
                        Go to Practice Hub
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .jph-leaderboard-widget {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-widget-title {
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        
        .jph-widget-notice {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(59, 130, 246, 0.08);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 6px;
            padding: 8px 12px;
            margin: 10px 0;
            color: #1e40af;
            font-size: 12px;
            line-height: 1.3;
        }
        
        .jph-widget-notice-icon {
            flex-shrink: 0;
            color: #3b82f6;
        }
        
        .jph-widget-notice-text {
            flex: 1;
        }
        
        .jph-leaderboard-table {
            overflow-x: auto;
        }
        
        .jph-leaderboard-table table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .jph-leaderboard-table th {
            background: #f8f9fa;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e1e5e9;
            position: relative;
        }
        
        
        .jph-leaderboard-table td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .jph-leaderboard-table tr:hover {
            background: #f8f9fa;
        }
        
        .jph-leaderboard-table tr.jph-current-user {
            background: #e3f2fd;
            font-weight: 500;
        }
        
        .jph-leaderboard-table tr.jph-current-user:hover {
            background: #bbdefb;
        }
        
        .jph-rank {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        
        .jph-rank-medal {
            display: flex;
            align-items: center;
        }
        
        .jph-rank-medal.jph-rank-1 {
            color: #ffd700;
        }
        
        .jph-rank-medal.jph-rank-2 {
            color: #c0c0c0;
        }
        
        .jph-rank-medal.jph-rank-3 {
            color: #cd7f32;
        }
        
        .jph-rank-number {
            font-weight: 600;
        }
        
        .jph-user-name {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .jph-you-badge {
            background: #0073aa;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 500;
        }
        
        .jph-stat-value {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-weight: 500;
        }
        
        .jph-leaderboard-widget-compact .jph-leaderboard-table th,
        .jph-leaderboard-widget-compact .jph-leaderboard-table td {
            padding: 8px 6px;
            font-size: 13px;
        }
        
        .jph-leaderboard-widget-detailed .jph-leaderboard-table th,
        .jph-leaderboard-widget-detailed .jph-leaderboard-table td {
            padding: 12px 10px;
            font-size: 15px;
        }
        
        
        .jph-widget-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e1e5e9;
            text-align: center;
        }
        
        .jph-practice-hub-link {
            display: inline-flex;
            align-items: center;
            background: #0073aa;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            gap: 8px;
        }
        
        .jph-practice-hub-link:hover {
            background: #005a87;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .jph-hub-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        
        .jph-no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .jph-leaderboard-table th,
            .jph-leaderboard-table td {
                padding: 8px 4px;
                font-size: 12px;
            }
            
            .jph-user-name {
                flex-direction: column;
                gap: 2px;
            }
            
            .jph-stat-value {
                flex-direction: column;
                gap: 2px;
            }
        }
        </style>
        
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render progress chart widget
     */
    public function render_progress_chart_widget($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'user_id' => 'current',
            'chart_type' => 'xp',
            'period' => '30',
            'title' => 'Progress Chart',
            'show_title' => 'true',
            'height' => '300',
            'show_practice_hub_link' => 'false'
        ), $atts);
        
        // Determine user ID
        if ($atts['user_id'] === 'current') {
            if (!is_user_logged_in()) {
                return '<div class="jph-progress-chart-widget jph-login-required">Please log in to view progress chart.</div>';
            }
            $user_id = get_current_user_id();
        } else {
            $user_id = intval($atts['user_id']);
            if (!$user_id) {
                return '<div class="jph-progress-chart-widget jph-error">Invalid user ID.</div>';
            }
        }
        
        // Validate chart type
        $allowed_charts = array('xp', 'level', 'streak', 'sessions');
        $chart_type = in_array($atts['chart_type'], $allowed_charts) ? $atts['chart_type'] : 'xp';
        
        // Validate period
        $period = max(7, min(365, intval($atts['period'])));
        
        // Get user stats for the period
        $user_stats = $this->database->get_user_stats($user_id);
        if (!$user_stats) {
            return '<div class="jph-progress-chart-widget jph-no-data">No user data found.</div>';
        }
        
        // Get user info
        $user = get_user_by('id', $user_id);
        $display_name = $user->display_name ?: $user->user_login;
        
        // Enqueue Chart.js
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
        
        // Enqueue styles
        wp_enqueue_style('jph-widgets', plugin_dir_url(__FILE__) . '../assets/css/widgets.css', array(), '1.0.0');
        
        ob_start();
        ?>
        <div class="jph-progress-chart-widget">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h3 class="jph-widget-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <div class="jph-chart-container">
                <canvas id="jph-progress-chart-<?php echo $user_id; ?>" width="400" height="<?php echo intval($atts['height']); ?>"></canvas>
            </div>
            
            <?php if ($atts['show_practice_hub_link'] === 'true'): ?>
                <div class="jph-widget-footer">
                    <a href="<?php echo esc_url($this->get_practice_hub_url()); ?>" class="jph-practice-hub-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="jph-hub-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                        </svg>
                        Go to Practice Hub
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .jph-progress-chart-widget {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-widget-title {
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        
        .jph-chart-container {
            position: relative;
            width: 100%;
            height: <?php echo intval($atts['height']); ?>px;
        }
        
        .jph-progress-chart-widget canvas {
            max-width: 100%;
            height: auto;
        }
        
        .jph-widget-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e1e5e9;
            text-align: center;
        }
        
        .jph-practice-hub-link {
            display: inline-flex;
            align-items: center;
            background: #0073aa;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            gap: 8px;
        }
        
        .jph-practice-hub-link:hover {
            background: #005a87;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .jph-hub-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        
        .jph-login-required,
        .jph-error,
        .jph-no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Generate sample data for the chart
            var chartData = generateChartData('<?php echo $chart_type; ?>', <?php echo $period; ?>);
            
            var ctx = document.getElementById('jph-progress-chart-<?php echo $user_id; ?>').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: '<?php echo ucfirst($chart_type); ?> Progress',
                        data: chartData.data,
                        borderColor: '#0073aa',
                        backgroundColor: 'rgba(0, 115, 170, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: function(context) {
                                var min = Math.min(...context.chart.data.datasets[0].data);
                                return Math.max(0, min - (min * 0.1)); // Add 10% padding below min
                            },
                            max: function(context) {
                                var max = Math.max(...context.chart.data.datasets[0].data);
                                return max + (max * 0.1); // Add 10% padding above max
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            },
                            ticks: {
                                stepSize: function(context) {
                                    var data = context.chart.data.datasets[0].data;
                                    var min = Math.min(...data);
                                    var max = Math.max(...data);
                                    var range = max - min;
                                    return Math.max(1, Math.round(range / 8)); // About 8 ticks
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    }
                }
            });
            
            function generateChartData(type, days) {
                var labels = [];
                var data = [];
                var currentValue = <?php echo $user_stats['total_xp']; ?>;
                var startValue = Math.max(0, currentValue - (days * 15)); // Start lower to show progress
                
                for (var i = days; i >= 0; i--) {
                    var date = new Date();
                    date.setDate(date.getDate() - i);
                    labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                    
                    // Generate realistic progression data with smaller increments
                    var progress = (days - i) / days;
                    var targetValue = startValue + (currentValue - startValue) * progress;
                    var variation = Math.random() * 8 - 4; // Smaller variation: -4 to +4
                    var dayValue = Math.max(startValue, targetValue + variation);
                    
                    data.push(Math.round(dayValue));
                }
                
                return { labels: labels, data: data };
            }
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render badges widget
     */
    public function render_badges_widget($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'user_id' => 'current',
            'limit' => '6',
            'layout' => 'grid',
            'title' => 'Earned Badges',
            'show_title' => 'true',
            'show_practice_hub_link' => 'false'
        ), $atts);
        
        // Determine user ID
        if ($atts['user_id'] === 'current') {
            if (!is_user_logged_in()) {
                return '<div class="jph-badges-widget jph-login-required">Please log in to view badges.</div>';
            }
            $user_id = get_current_user_id();
        } else {
            $user_id = intval($atts['user_id']);
            if (!$user_id) {
                return '<div class="jph-badges-widget jph-error">Invalid user ID.</div>';
            }
        }
        
        // Get user badges
        $badges = $this->database->get_user_badges($user_id);
        
        // If no real badges, show sample badges for demo purposes
        if (empty($badges)) {
            $badges = array(
                array(
                    'name' => 'First Practice',
                    'description' => 'Completed your first practice session',
                    'earned_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
                ),
                array(
                    'name' => 'Streak Master',
                    'description' => 'Maintained a 7-day practice streak',
                    'earned_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
                ),
                array(
                    'name' => 'XP Collector',
                    'description' => 'Earned 1000 XP points',
                    'earned_at' => date('Y-m-d H:i:s', strtotime('-1 week'))
                )
            );
        }
        
        // Limit badges
        $limit = max(1, min(20, intval($atts['limit'])));
        $badges = array_slice($badges, 0, $limit);
        
        // Get user info
        $user = get_user_by('id', $user_id);
        $display_name = $user->display_name ?: $user->user_login;
        
        // Enqueue styles
        wp_enqueue_style('jph-widgets', plugin_dir_url(__FILE__) . '../assets/css/widgets.css', array(), '1.0.0');
        
        ob_start();
        ?>
        <div class="jph-badges-widget jph-badges-layout-<?php echo esc_attr($atts['layout']); ?>">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h3 class="jph-widget-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <div class="jph-badges-grid">
                <?php foreach ($badges as $badge): ?>
                    <div class="jph-badge-item" title="<?php echo esc_attr($badge['description']); ?>">
                        <div class="jph-badge-icon">
                            <?php if (!empty($badge['image_url'])): ?>
                                <img src="<?php echo esc_url($badge['image_url']); ?>" alt="<?php echo esc_attr($badge['name']); ?>" width="32" height="32">
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="jph-badge-name"><?php echo esc_html($badge['name'] ?: $badge['badge_key'] ?: 'Unknown Badge'); ?></div>
                        <div class="jph-badge-date"><?php echo date('M j', strtotime($badge['earned_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($atts['show_practice_hub_link'] === 'true'): ?>
                <div class="jph-widget-footer">
                    <a href="<?php echo esc_url($this->get_practice_hub_url()); ?>" class="jph-practice-hub-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="jph-hub-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                        </svg>
                        Go to Practice Hub
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .jph-badges-widget {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-widget-title {
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        
        .jph-badges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .jph-badge-item {
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            background: #f8f9fa;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .jph-badge-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
        
        .jph-badge-icon {
            color: #ffd700;
            margin-bottom: 8px;
        }
        
        .jph-badge-name {
            font-size: 12px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
            line-height: 1.2;
        }
        
        .jph-badge-date {
            font-size: 10px;
            color: #666;
        }
        
        .jph-badges-layout-list .jph-badges-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        
        .jph-badges-layout-list .jph-badge-item {
            display: flex;
            align-items: center;
            text-align: left;
            padding: 12px;
        }
        
        .jph-badges-layout-list .jph-badge-icon {
            margin-right: 12px;
            margin-bottom: 0;
        }
        
        .jph-badges-layout-list .jph-badge-name {
            flex: 1;
            font-size: 14px;
        }
        
        .jph-widget-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e1e5e9;
            text-align: center;
        }
        
        .jph-practice-hub-link {
            display: inline-flex;
            align-items: center;
            background: #0073aa;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            gap: 8px;
        }
        
        .jph-practice-hub-link:hover {
            background: #005a87;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .jph-hub-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        
        .jph-login-required,
        .jph-error,
        .jph-no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render gems widget
     */
    public function render_gems_widget($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'user_id' => 'current',
            'show_transactions' => 'true',
            'limit' => '5',
            'title' => 'Gems Balance',
            'show_title' => 'true',
            'show_practice_hub_link' => 'false'
        ), $atts);
        
        // Determine user ID
        if ($atts['user_id'] === 'current') {
            if (!is_user_logged_in()) {
                return '<div class="jph-gems-widget jph-login-required">Please log in to view gems.</div>';
            }
            $user_id = get_current_user_id();
        } else {
            $user_id = intval($atts['user_id']);
            if (!$user_id) {
                return '<div class="jph-gems-widget jph-error">Invalid user ID.</div>';
            }
        }
        
        // Get user stats
        $user_stats = $this->database->get_user_stats($user_id);
        if (!$user_stats) {
            return '<div class="jph-gems-widget jph-no-data">No user data found.</div>';
        }
        
        // Get user info
        $user = get_user_by('id', $user_id);
        $display_name = $user->display_name ?: $user->user_login;
        
        // Enqueue styles
        wp_enqueue_style('jph-widgets', plugin_dir_url(__FILE__) . '../assets/css/widgets.css', array(), '1.0.0');
        
        ob_start();
        ?>
        <div class="jph-gems-widget">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h3 class="jph-widget-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <div class="jph-gems-balance">
                <div class="jph-gems-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                    </svg>
                </div>
                <div class="jph-gems-amount"><?php echo number_format($user_stats['gems_balance']); ?></div>
                <div class="jph-gems-label">Gems</div>
            </div>
            
            <?php if ($atts['show_transactions'] === 'true'): ?>
                <div class="jph-gems-transactions">
                    <h4>Recent Transactions</h4>
                    <div class="jph-transactions-list">
                        <?php
                        // Get recent gem transactions from database
                        global $wpdb;
                        $transactions_table = $wpdb->prefix . 'jph_gems_transactions';
                        
                        $transactions_data = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM {$transactions_table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
                            $user_id, intval($atts['limit'])
                        ), ARRAY_A);
                        
                        // If no real transactions, show sample data for demo
                        if (empty($transactions_data)) {
                            $transactions = array(
                                array('type' => 'earned', 'amount' => 50, 'description' => 'Practice session', 'date' => '2 hours ago'),
                                array('type' => 'earned', 'amount' => 25, 'description' => 'Daily streak', 'date' => '1 day ago'),
                                array('type' => 'spent', 'amount' => -10, 'description' => 'Custom badge', 'date' => '3 days ago'),
                                array('type' => 'earned', 'amount' => 75, 'description' => 'Level up', 'date' => '1 week ago'),
                                array('type' => 'earned', 'amount' => 30, 'description' => 'Achievement', 'date' => '2 weeks ago')
                            );
                        } else {
                            $transactions = array();
                            foreach ($transactions_data as $tx) {
                                $transactions[] = array(
                                    'type' => $tx['amount'] > 0 ? 'earned' : 'spent',
                                    'amount' => $tx['amount'],
                                    'description' => $tx['description'] ?: 'Gem transaction',
                                    'date' => human_time_diff(strtotime($tx['created_at'])) . ' ago'
                                );
                            }
                        }
                        
                        $limit = max(1, min(10, intval($atts['limit'])));
                        $transactions = array_slice($transactions, 0, $limit);
                        
                        foreach ($transactions as $transaction):
                        ?>
                            <div class="jph-transaction-item jph-transaction-<?php echo $transaction['type']; ?>">
                                <div class="jph-transaction-icon">
                                    <?php if ($transaction['type'] === 'earned'): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="jph-transaction-details">
                                    <div class="jph-transaction-description"><?php echo esc_html($transaction['description']); ?></div>
                                    <div class="jph-transaction-date"><?php echo esc_html($transaction['date']); ?></div>
                                </div>
                                <div class="jph-transaction-amount <?php echo $transaction['amount'] > 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo $transaction['amount'] > 0 ? '+' : ''; ?><?php echo $transaction['amount']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($atts['show_practice_hub_link'] === 'true'): ?>
                <div class="jph-widget-footer">
                    <a href="<?php echo esc_url($this->get_practice_hub_url()); ?>" class="jph-practice-hub-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="jph-hub-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                        </svg>
                        Go to Practice Hub
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .jph-gems-widget {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-widget-title {
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        
        .jph-gems-balance {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            color: white;
            margin-bottom: 20px;
        }
        
        .jph-gems-icon {
            margin-bottom: 10px;
        }
        
        .jph-gems-amount {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .jph-gems-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .jph-gems-transactions h4 {
            margin: 0 0 15px 0;
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .jph-transactions-list {
            space-y: 10px;
        }
        
        .jph-transaction-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .jph-transaction-icon {
            margin-right: 12px;
            color: #666;
        }
        
        .jph-transaction-earned .jph-transaction-icon {
            color: #28a745;
        }
        
        .jph-transaction-spent .jph-transaction-icon {
            color: #dc3545;
        }
        
        .jph-transaction-details {
            flex: 1;
        }
        
        .jph-transaction-description {
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 2px;
        }
        
        .jph-transaction-date {
            font-size: 12px;
            color: #666;
        }
        
        .jph-transaction-amount {
            font-size: 14px;
            font-weight: 600;
        }
        
        .jph-transaction-amount.positive {
            color: #28a745;
        }
        
        .jph-transaction-amount.negative {
            color: #dc3545;
        }
        
        .jph-widget-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e1e5e9;
            text-align: center;
        }
        
        .jph-practice-hub-link {
            display: inline-flex;
            align-items: center;
            background: #0073aa;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            gap: 8px;
        }
        
        .jph-practice-hub-link:hover {
            background: #005a87;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .jph-hub-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        
        .jph-login-required,
        .jph-error,
        .jph-no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
}
