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
        add_shortcode('jph_practice_items_widget', array($this, 'render_practice_items_widget'));
        add_shortcode('jph_progress_chart_widget', array($this, 'render_progress_chart_widget'));
        add_shortcode('jph_badges_widget', array($this, 'render_badges_widget'));
        add_shortcode('jph_gems_widget', array($this, 'render_gems_widget'));
        add_shortcode('jph_streak_widget', array($this, 'render_streak_widget'));
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
        
        .jph-membership-required {
            text-align: center;
            padding: 60px 40px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            margin: 40px auto;
            max-width: 600px;
            border: 1px solid #e9ecef;
            position: relative;
            overflow: hidden;
        }
        
        .jph-membership-required::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
        }
        
        .membership-notice h3 {
            color: #2c3e50;
            margin-bottom: 24px;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
            position: relative;
        }
        
        .membership-notice h3::after {
            content: '🔒';
            margin-left: 12px;
            font-size: 24px;
            opacity: 0.7;
        }
        
        .membership-notice p {
            color: #5a6c7d;
            margin-bottom: 20px;
            font-size: 18px;
            line-height: 1.6;
            font-weight: 400;
        }
        
        .membership-notice a {
            display: inline-block;
            background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 115, 170, 0.3);
            margin-top: 8px;
        }
        
        .membership-notice a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 115, 170, 0.4);
            background: linear-gradient(135deg, #005a87 0%, #004066 100%);
        }
        
        .membership-notice a:active {
            transform: translateY(0);
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
     * Get all lesson favorites for a user (all categories)
     */
    private function get_all_lesson_favorites($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        
        $favorites = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY category ASC, title ASC",
            $user_id
        ), ARRAY_A);
        
        return $favorites ?: array();
    }
    
    /**
     * Render the student dashboard
     */
    public function render_dashboard($atts) {
        // Only show to logged-in users
        if (!is_user_logged_in()) {
            return '<div class="jph-login-required">Please log in to access your practice dashboard.</div>';
        }
        
        // Check if user has active membership
        if (function_exists('je_return_active_member') && je_return_active_member() !== 'true') {
            return '<style>
                .jph-membership-required {
                    text-align: center;
                    padding: 60px 40px;
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                    border-radius: 16px;
                    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
                    margin: 40px auto;
                    max-width: 600px;
                    border: 1px solid #e9ecef;
                    position: relative;
                    overflow: hidden;
                }
                
                .jph-membership-required::before {
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
                }
                
                .membership-notice h3 {
                    color: #2c3e50;
                    margin-bottom: 24px;
                    font-size: 28px;
                    font-weight: 700;
                    letter-spacing: -0.5px;
                    position: relative;
                }
                
                .membership-notice h3::after {
                    content: "🔒";
                    margin-left: 12px;
                    font-size: 24px;
                    opacity: 0.7;
                }
                
                .membership-notice p {
                    color: #5a6c7d;
                    margin-bottom: 20px;
                    font-size: 18px;
                    line-height: 1.6;
                    font-weight: 400;
                }
                
                .membership-notice a {
                    display: inline-block;
                    background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
                    color: white;
                    text-decoration: none;
                    font-weight: 600;
                    padding: 14px 28px;
                    border-radius: 8px;
                    font-size: 16px;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 12px rgba(0, 115, 170, 0.3);
                    margin-top: 8px;
                }
                
                .membership-notice a:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(0, 115, 170, 0.4);
                    background: linear-gradient(135deg, #005a87 0%, #004066 100%);
                }
                
                .membership-notice a:active {
                    transform: translateY(0);
                }
            </style>
            <div class="jph-membership-required">
                <div class="membership-notice">
                    <h3>Membership Required</h3>
                    <p>You need an active JazzEdge Academy membership to access the Practice Hub dashboard.</p>
                    <p>Please contact us to activate your membership:</p>
                    <a href="mailto:support@jazzedge.com">Contact Support</a>
                </div>
            </div>';
        }
        
        $user_id = get_current_user_id();
        
        // Automatically add user to all community spaces on first load
        $this->auto_add_user_to_spaces($user_id);
        
        // Get user's practice items
        $practice_items = $this->database->get_user_practice_items($user_id);
        $practice_items = $this->sanitize_practice_items($practice_items);
        
        // Get user's lesson favorites for matching URLs (all categories)
        $lesson_favorites = $this->get_all_lesson_favorites($user_id);
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
                        <span class="beta-text">We're testing a new design! We'd love to hear your feedback on what you like and what we can improve.</span>
                    </div>
                    <button id="jph-feedback-btn-banner" type="button" class="jph-feedback-btn-banner">
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
                        <h3>✨ New Practice Hub Design</h3>
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
                        <p><strong>Welcome to the redesigned Practice Hub!</strong></p>
                        <p>We've been working hard on a fresh new look and improved experience. We'd love to hear what you think!</p>
                        <p><strong>What's new:</strong></p>
                        <ul>
                            <li>Cleaner, more intuitive dashboard design</li>
                            <li>Better organization of your practice items and favorites</li>
                            <li>Enhanced progress tracking and analytics</li>
                            <li>All your data is safe and secure</li>
                        </ul>
                        <p>Your feedback is invaluable as we continue to refine this new design. Please share your thoughts on what works well and what we can improve!</p>
                    </div>
                    <div class="jph-modal-footer">
                        <button id="jph-beta-disclaimer-understand" class="jph-btn jph-btn-primary">
                            Got It - Let's Go!
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
                        <h2 id="jph-welcome-title">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75s.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                            </svg>
                            Your Practice Dashboard
                        </h2>
                        <button id="jph-edit-name-btn" class="jph-edit-name-btn" title="Edit leaderboard name" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </button>
                    </div>
                    <div class="header-actions">
                        <!-- Tutorial Button -->
                        <button id="jph-tutorial-btn" type="button" class="jph-btn jph-btn-secondary jph-tutorial-btn" onclick="window.open('/help', '_blank')">
                            <span class="btn-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </span>
                            Tutorial
                        </button>
                        <!-- Leaderboard Button -->
                        <button id="jph-leaderboard-btn" type="button" class="jph-btn jph-btn-secondary jph-leaderboard-btn" onclick="openLeaderboard()">
                            <span class="btn-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                                </svg>
                            </span>
                            Leaderboard
                        </button>
                        <!-- About Stats Button -->
                        <button id="jph-stats-explanation-btn" type="button" class="jph-btn jph-btn-secondary jph-stats-help-btn">
                            <span class="btn-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                </svg>
                            </span>
                            About Stats
                        </button>
                    </div>
                </div>
                <div class="jph-stats">
                    <div class="stat">
                        <span class="stat-value stat-level">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffd700" width="28" height="28">
                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                            </svg>
                            <?php echo esc_html($user_stats['current_level']); ?>
                        </span>
                        <span class="stat-label">Level</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value stat-xp">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#3b82f6" width="28" height="28">
                                <path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 01.359.852L12.982 9.75h7.268a.75.75 0 01.548 1.262l-10.5 11.25a.75.75 0 01-1.272-.71L10.018 14.25H2.75a.75.75 0 01-.548-1.262l10.5-11.25a.75.75 0 01.913-.143z" clip-rule="evenodd" />
                            </svg>
                            <?php echo esc_html($user_stats['total_xp']); ?>
                        </span>
                        <span class="stat-label">XP</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value stat-streak">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f97316" width="28" height="28">
                                <path fill-rule="evenodd" d="M15.22 6.268a.75.75 0 01.968-.432l5.942 2.28a.75.75 0 01.431.97l-2.28 5.941a.75.75 0 11-1.4-.537l1.63-4.251-1.086.483a11.2 11.2 0 00-5.45 5.174.75.75 0 01-1.199.12L9 12.31l-6.22 6.22a.75.75 0 11-1.06-1.06l6.75-6.75a.75.75 0 011.06 0l3.606 3.605a12.694 12.694 0 015.68-4.973l1.086-.484-4.251-1.631a.75.75 0 01-.432-.97z" clip-rule="evenodd" />
                            </svg>
                            <?php echo esc_html($user_stats['current_streak']); ?>
                        </span>
                        <span class="stat-label">Streak</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value stat-gems">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#8b5cf6" width="28" height="28">
                                <path fill-rule="evenodd" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" clip-rule="evenodd" />
                            </svg>
                            <?php echo esc_html($user_stats['gems_balance']); ?>
                        </span>
                        <span class="stat-label">GEMS</span>
                    </div>
                </div>
                
                <!-- Search Section - Professional 2-Column Layout -->
                <div class="jph-search-section">
                    <div class="search-section-grid">
                        <!-- Left Column - Continue Learning & Dropdowns (40%) -->
                        <div class="search-left-column">
                            <h4 class="section-heading">Continue Learning</h4>
                            
                            <?php
                            // Get last viewed lesson
                            $last_lesson = get_user_meta($user_id, 'last_viewed_lesson', true);
                            
                            if (!empty($last_lesson)) {
                                $last_lesson_data = maybe_unserialize($last_lesson);
                                
                                if (is_array($last_lesson_data) && !empty($last_lesson_data['title'])) {
                                    $lesson_title = esc_html(stripslashes($last_lesson_data['title']));
                                    $permalink = esc_url($last_lesson_data['permalink']);
                                    ?>
                                    <div class="last-lesson-card">
                                        <div class="last-lesson-icon">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div class="last-lesson-info">
                                            <a href="<?php echo $permalink; ?>" target="_blank" rel="noopener noreferrer" class="last-lesson-link"><?php echo $lesson_title; ?></a>
                                            <span class="last-lesson-label">Last viewed lesson</span>
                                        </div>
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="last-lesson-card">
                                        <div class="last-lesson-icon">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div class="last-lesson-info">
                                            <a href="/paths" class="last-lesson-link">Choose a Learning Path</a>
                                            <span class="last-lesson-label">Ready to learn?</span>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                ?>
                                <div class="last-lesson-card">
                                    <div class="last-lesson-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                    <div class="last-lesson-info">
                                        <a href="/paths" class="last-lesson-link">Choose a Learning Path</a>
                                        <span class="last-lesson-label">Ready to learn?</span>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            
                            <?php
                            // Favorites Dropdown
                            if (!empty($lesson_favorites)) {
                                $favorites_by_category = array();
                                foreach ($lesson_favorites as $favorite_item) {
                                    if (empty($favorite_item['title']) || empty($favorite_item['url'])) {
                                        continue;
                                    }
                                    $category = !empty($favorite_item['category']) ? $favorite_item['category'] : 'lesson';
                                    $title = stripslashes($favorite_item['title']);
                                    $resource_type = !empty($favorite_item['resource_type']) ? $favorite_item['resource_type'] : '';
                                    
                                    $is_resource = false;
                                    if (!empty($resource_type)) {
                                        $is_resource = true;
                                    } elseif ($category !== 'lesson') {
                                        $is_resource = true;
                                    } else {
                                        $resource_indicators = array('(Sheet Music)', '(PDF)', '(Sheet)', '(Music)', '(Resource)');
                                        foreach ($resource_indicators as $indicator) {
                                            if (stripos($title, $indicator) !== false) {
                                                $is_resource = true;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    $display_category = $is_resource ? 'Resources' : 'Lessons';
                                    
                                    if (!isset($favorites_by_category[$display_category])) {
                                        $favorites_by_category[$display_category] = array();
                                    }
                                    
                                    $favorites_by_category[$display_category][] = $favorite_item;
                                }
                                
                                foreach ($favorites_by_category as $cat => &$items) {
                                    usort($items, function($a, $b) {
                                        return strcasecmp(stripslashes($a['title']), stripslashes($b['title']));
                                    });
                                }
                                unset($items);
                                
                                echo '<div class="favorites-dropdown-wrapper">';
                                echo '<select id="jph-favorites-dropdown" class="standard-dropdown">';
                                echo '<option value="">Select a favorite…</option>';
                                echo '<option value="' . esc_url(home_url('/my-favorites')) . '" data-view-all="true">📋 View all favorites</option>';
                                
                                $category_order = array('Lessons', 'Resources');
                                foreach ($category_order as $category_name) {
                                    if (isset($favorites_by_category[$category_name]) && !empty($favorites_by_category[$category_name])) {
                                        echo '<optgroup label="' . esc_attr($category_name) . '">';
                                        foreach ($favorites_by_category[$category_name] as $favorite_item) {
                                            $fav_title = esc_html(stripslashes($favorite_item['title']));
                                            
                                            $fav_url = $favorite_item['url'];
                                            if (!empty($favorite_item['resource_link'])) {
                                                if (strpos($fav_url, 'je_link.php') !== false) {
                                                    parse_str(parse_url($fav_url, PHP_URL_QUERY), $params);
                                                    if (!empty($params['id']) && !empty($favorite_item['resource_link'])) {
                                                        $fav_url = 'https://jazzedge.academy/je_link.php?id=' . intval($params['id']) . '&link=' . urlencode($favorite_item['resource_link']);
                                                    }
                                                } elseif (strpos($favorite_item['resource_link'], 'http://') !== 0 && strpos($favorite_item['resource_link'], 'https://') !== 0) {
                                                    if (strpos($fav_url, 's3.amazonaws.com') !== false && strpos($fav_url, $favorite_item['resource_link']) === false) {
                                                        $fav_url = 'https://s3.amazonaws.com/jazzedge-resources/' . $favorite_item['resource_link'];
                                                    }
                                                }
                                            }
                                            
                                            $fav_url = esc_url($fav_url);
                                            echo '<option value="' . $fav_url . '" title="' . $fav_title . '">' . $fav_title . '</option>';
                                        }
                                        echo '</optgroup>';
                                    }
                                }
                                
                                echo '</select>';
                                echo '</div>';
                            }
                            ?>
                            
                            <?php
                            // Collections Dropdown
                            global $wpdb;
                            $collections_table = $wpdb->prefix . 'alm_collections';
                            $collections = $wpdb->get_results(
                                "SELECT ID, collection_title, membership_level, post_id 
                                 FROM {$collections_table} 
                                 ORDER BY membership_level ASC, collection_title ASC"
                            );
                            
                            if (!empty($collections)) {
                                $membership_levels = array();
                                if (class_exists('ALM_Admin_Settings') && method_exists('ALM_Admin_Settings', 'get_membership_levels')) {
                                    $membership_levels = ALM_Admin_Settings::get_membership_levels();
                                }
                                
                                echo '<div class="collections-dropdown-wrapper">';
                                echo '<select id="jph-collections-dropdown" class="standard-dropdown" onchange="if(this.value) window.location.href=this.value;">';
                                echo '<option value="">Select a collection…</option>';
                                
                                $current_level = null;
                                foreach ($collections as $collection_row) {
                                    $post_id = intval($collection_row->post_id);
                                    if (!$post_id) { continue; }
                                    
                                    $collection_url = get_permalink($post_id);
                                    if (!$collection_url) { continue; }
                                    
                                    $membership_level = intval($collection_row->membership_level);
                                    
                                    $level_name = 'Unknown';
                                    if (!empty($membership_levels)) {
                                        foreach ($membership_levels as $level_key => $level_data) {
                                            if (isset($level_data['numeric']) && $level_data['numeric'] == $membership_level) {
                                                $level_name = isset($level_data['name']) ? $level_data['name'] : 'Unknown';
                                                break;
                                            }
                                        }
                                    }
                                    
                                    if ($current_level !== $membership_level) {
                                        if ($current_level !== null) {
                                            echo '</optgroup>';
                                        }
                                        echo '<optgroup label="' . esc_attr($level_name) . '">';
                                        $current_level = $membership_level;
                                    }
                                    
                                    $collection_title = esc_html(stripslashes($collection_row->collection_title));
                                    echo '<option value="' . esc_url($collection_url) . '" title="' . $collection_title . '">' . $collection_title . '</option>';
                                }
                                
                                if ($current_level !== null) {
                                    echo '</optgroup>';
                                }
                                
                                echo '</select>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        
                        <!-- Right Column - Search Lessons Prominent Section (60%) -->
                        <div class="search-right-column">
                            <div class="search-prominent-card">
                                <div class="search-header">
                                    <h4 class="search-heading">
                                        <svg class="search-heading-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        Search Lessons
                                    </h4>
                                    <p class="search-subtitle">Find lessons, resources, and more instantly</p>
                                </div>
                                
                                <div class="search-input-wrapper-prominent">
                                    <?php
                                    echo do_shortcode('[alm_lesson_search_compact view_all_url="/search" placeholder="Search lessons..." max_items="10"]');
                                    ?>
                                </div>
                                
                                <div class="search-tips">
                                    <h5 class="search-tips-heading">💡 Search Tips</h5>
                                    <ul class="search-tips-list">
                                        <li>Try searching by <strong>lesson title</strong>, <strong>song name</strong>, or <strong>topic</strong></li>
                                        <li>Use keywords like <strong>"chord"</strong>, <strong>"improvisation"</strong>, or <strong>"scales"</strong></li>
                                        <li>Search for specific artists or songs you want to learn</li>
                                        <li>Browse <strong>collections</strong> to discover curated lesson series</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
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
                        <span class="tab-title">Practice</span>
                    </button>
                    <button class="jph-tab-btn" data-tab="technique">
                        <span class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443a55.381 55.381 0 015.25 2.882V15" />
                            </svg>
                        </span>
                        <span class="tab-title">Foundational</span>
                    </button>
                    <button class="jph-tab-btn" data-tab="events">
                        <span class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </span>
                        <span class="tab-title">Events</span>
                    </button>
                    <button class="jph-tab-btn" data-tab="community">
                        <span class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM7.5 21a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </span>
                        <span class="tab-title">Community</span>
                    </button>
                    <button class="jph-tab-btn" data-tab="shield-protection">
                        <span class="tab-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </span>
                        <span class="tab-title">Shield</span>
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
                </div>
                
                <!-- Tab Content -->
                <div class="jph-tabs-content">
                    
                    <!-- Practice Items Tab -->
                    <div class="jph-tab-pane active" id="practice-items-tab">
                        <!-- Practice Items Section -->
                        <div class="jph-practice-items">
                            <div class="practice-items-header">
                                <h3>My Practice Items 
                                    <span class="item-count">(<?php echo count($practice_items); ?>/6)</span>
                                </h3>
                                <button type="button" class="jph-practice-history-btn" id="jph-practice-history-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                    </svg>
                                    Practice History
                                </button>
                            </div>
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
                        
                        <!-- Repertoire Section -->
                        <div class="jph-repertoire-section" style="margin-top: 40px;">
                            <div class="repertoire-header">
                                <h3>My Repertoire</h3>
                                <div class="repertoire-header-actions">
                                    <button type="button" class="jph-btn jph-btn-secondary" id="jph-print-repertoire-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                        </svg>
                                        Print
                                    </button>
                                    <button type="button" class="jph-btn jph-btn-primary" id="jph-add-repertoire-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Add Repertoire
                                    </button>
                                </div>
                            </div>
                            
                            <div class="repertoire-controls">
                                <div class="sort-controls">
                                    <label>Sort by:</label>
                                    <select id="repertoire-sort">
                                        <option value="last_practiced">Last Practice Date</option>
                                        <option value="title">Title</option>
                                        <option value="date_added">Date Added</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="repertoire-table-container">
                                <table class="repertoire-table" id="repertoire-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;"></th>
                                            <th>Title</th>
                                            <th>Composer</th>
                                            <th>Last Practice Date</th>
                                            <th>Notes</th>
                                            <th style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="repertoire-tbody">
                                        <!-- Repertoire items will be loaded here via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Events Tab -->
                    <div class="jph-tab-pane" id="events-tab">
                        <!-- Events Section -->
                        <div class="jph-events-section">
                            <div class="events-header">
                                <h3>Upcoming Events</h3>
                                <a href="/calendar" class="view-calendar-btn">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>View Full Calendar</span>
                                </a>
                            </div>
                            
                            <div class="events-content">
                                <?php
                                // Get upcoming events using the complete shortcode logic
                                if ($user_id > 0) {
                                    // Get user's membership level for filtering
                                    $user_level = '';
                                    $user_meta = get_user_meta($user_id, 'membership_level', true);
                                    if ($user_meta) {
                                        $user_level = sanitize_title($user_meta);
                                    }
                                    
                                    // Use the complete events shortcode logic
                                    $count = 5; // Show 5 events in events tab
                                    $teacher = '';
                                    $level = $user_level;
                                    $etype = '';
                                    
                                    // Pull a wide window and then use je_ev_local_dt() to normalize times
                                    $now_ts = current_time('timestamp');
                                    
                                    // Add some buffer time to catch events that might be in different timezone
                                    $buffer_hours = 6; // Look back 6 hours to catch events that might have timezone issues
                                    $now_with_buffer = $now_ts - ($buffer_hours * 3600);
                                    
                                    $start = new DateTimeImmutable('@' . $now_with_buffer);
                                    $end = new DateTimeImmutable('@' . $now_ts);
                                    $end = $end->modify('+365 days');
                                    
                                    // Get events using the actual function
                                    if (function_exists('je_get_events_between')) {
                                        $events = je_get_events_between($start, $end, [
                                            'teacher' => $teacher,
                                            'membership-level' => $level,
                                        ]);
                                        
                                        // Optional event-type filter
                                        if ($etype) {
                                            $events = array_values(array_filter($events, function($ev) use ($etype) {
                                                $slugs = wp_get_post_terms($ev->ID, 'event-type', ['fields' => 'slugs']);
                                                if (is_wp_error($slugs) || empty($slugs)) $slugs = wp_get_post_terms($ev->ID, 'event_type', ['fields' => 'slugs']);
                                                $slugs = is_wp_error($slugs) ? [] : array_map('sanitize_title', (array)$slugs);
                                                return in_array($etype, $slugs, true);
                                            }));
                                        }
                                        
                                        // Normalize start times with je_ev_local_dt() (prevents Dec 31 epoch bug)
                                        $items = [];
                                        foreach ($events as $ev) {
                                            $raw = get_post_meta($ev->ID, 'je_event_start', true);
                                            $dt = je_ev_local_dt($raw);
                                            if (!$dt) continue;
                                            $ts = $dt->getTimestamp();
                                            
                                            // Get event end time, or default to 2 hours after start if no end time
                                            $raw_end = get_post_meta($ev->ID, 'je_event_end', true);
                                            $end_dt = $raw_end ? je_ev_local_dt($raw_end) : null;
                                            $end_ts = $end_dt ? $end_dt->getTimestamp() : ($ts + (2 * 3600)); // Default 2 hours
                                            
                                            // Keep events visible for 6 hours after they end (increased buffer for timezone issues)
                                            $visible_until = $end_ts + (6 * 3600); // 6 hours after end
                                            
                                            // More inclusive filtering - show events that are either upcoming or recently ended
                                            if ($visible_until >= $now_ts) $items[] = ['id' => $ev->ID, 'ts' => $ts, 'end_ts' => $end_ts];
                                        }
                                        
                                        usort($items, fn($a, $b) => $a['ts'] <=> $b['ts']);
                                        $items = array_slice($items, 0, $count);
                                        
                                        if (!empty($items)) {
                                            foreach ($items as $it) {
                                                $pid = $it['id'];
                                                $event_title = get_the_title($pid);
                                                $event_permalink = get_permalink($pid);
                                                $event_date = wp_date('D, M j • g:i a', $it['ts']);
                                                
                                                // Get event taxonomies for rich data
                                                $event_types = wp_get_post_terms($pid, 'event-type', ['fields' => 'names']);
                                                if (is_wp_error($event_types) || empty($event_types)) {
                                                    $event_types = wp_get_post_terms($pid, 'event_type', ['fields' => 'names']);
                                                }
                                                $event_types = is_wp_error($event_types) ? [] : $event_types;
                                                
                                                $membership_levels = wp_get_post_terms($pid, 'membership-level', ['fields' => 'names']);
                                                if (is_wp_error($membership_levels) || empty($membership_levels)) {
                                                    $membership_levels = wp_get_post_terms($pid, 'membership_level', ['fields' => 'names']);
                                                }
                                                $membership_levels = is_wp_error($membership_levels) ? [] : $membership_levels;
                                                
                                                // Get event description/excerpt
                                                $event_excerpt = get_the_excerpt($pid);
                                                if (empty($event_excerpt)) {
                                                    $event_excerpt = wp_trim_words(get_the_content($pid), 20);
                                                }
                                                
                                                // Get event teacher/instructor
                                                $event_teacher = get_post_meta($pid, 'je_event_teacher', true);
                                                if (empty($event_teacher)) {
                                                    $event_teacher = get_post_meta($pid, 'event_teacher', true);
                                                }
                                                ?>
                                                <div class="event-item">
                                                    <div class="event-date">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        <span><?php echo esc_html($event_date); ?></span>
                                                        
                                                        <!-- Calendar Links -->
                                                        <div class="event-calendar-links">
                                                            <?php
                                                            // Generate calendar links
                                                            $s = je_ev_local_dt(get_post_meta($pid, 'je_event_start', true));
                                                            $e = je_ev_local_dt(get_post_meta($pid, 'je_event_end', true));
                                                            $tz = wp_timezone_string();
                                                            
                                                            if ($s) {
                                                                $title = wp_strip_all_tags($event_title);
                                                                $desc = wp_strip_all_tags($event_excerpt ?: get_post_field('post_content', $pid));
                                                                
                                                                // Google Calendar link
                                                                $gcal = add_query_arg([
                                                                    'action' => 'TEMPLATE',
                                                                    'text' => $title,
                                                                    'dates' => je_gcal_range($s, $e),
                                                                    'details' => $desc . "\n" . $event_permalink,
                                                                    'ctz' => $tz,
                                                                ], 'https://www.google.com/calendar/render');
                                                                
                                                                // iCal link
                                                                $ics = add_query_arg(['action' => 'je_ics', 'id' => $pid], admin_url('admin-ajax.php'));
                                                                ?>
                                                                <a class="calendar-link gcal-link" href="<?php echo esc_url($gcal); ?>" target="_blank" rel="noopener" title="Add to Google Calendar">
                                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                    </svg>
                                                                    <span>Google</span>
                                                                </a>
                                                                <a class="calendar-link ical-link" href="<?php echo esc_url($ics); ?>" title="Add to iCal/Outlook">
                                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                                    </svg>
                                                                    <span>iCal</span>
                                                                </a>
                                                                <?php
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <div class="event-info">
                                                        <h4><a href="<?php echo esc_url($event_permalink); ?>"><?php echo esc_html($event_title); ?></a></h4>
                                                        <?php if (!empty($event_excerpt)): ?>
                                                            <p class="event-description"><?php echo esc_html($event_excerpt); ?></p>
                                                        <?php endif; ?>
                                                        
                                                        <div class="event-meta">
                                                            <?php if (!empty($event_teacher)): ?>
                                                                <div class="event-teacher">
                                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                                    </svg>
                                                                    <span><?php echo esc_html($event_teacher); ?></span>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <?php if (!empty($event_types)): ?>
                                                                <div class="event-types">
                                                                    <?php foreach ($event_types as $type): ?>
                                                                        <span class="event-type-tag"><?php echo esc_html($type); ?></span>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <?php if (!empty($membership_levels)): ?>
                                                                <div class="event-membership">
                                                                    <?php foreach ($membership_levels as $level): ?>
                                                                        <span class="membership-level-tag membership-<?php echo esc_attr(strtolower($level)); ?>"><?php echo esc_html($level); ?></span>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        } else {
                                            // No events found
                                            ?>
                                            <div class="no-events-content">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <p>No upcoming events scheduled. Check back soon for new live sessions!</p>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        // Function not available
                                        ?>
                                        <div class="no-events-content">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <p>Events system not available. Please check back later.</p>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    // User not logged in
                                    ?>
                                    <div class="no-events-content">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p>Please log in to see upcoming events and live sessions.</p>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Technique Tab -->
                    <div class="jph-tab-pane" id="technique-tab">
                        <!-- JPC Current Focus Section -->
                        <div class="jpc-current-focus">
                            <?php
                            // Get current JPC assignment
                                if ($user_id > 0 && class_exists('JPH_JPC_Handler')) {
                                    $current_assignment = null;
                                    try {
                                        $current_assignment = JPH_JPC_Handler::get_user_current_assignment($user_id);
                                    } catch (Exception $e) {
                                        $current_assignment = null;
                                    }
                                
                                if ($current_assignment) {
                                    // Get progress to determine which key to show
                                    $progress = array();
                                    try {
                                        $progress = JPH_JPC_Handler::get_user_progress($user_id, $current_assignment['curriculum_id']);
                                    } catch (Exception $e) {
                                        $progress = array();
                                    }
                                    
                                    // Use the current assignment as-is (don't override with "next" logic)
                                    // The assignment is already the correct next step from the backend
                                    $focus_title = $current_assignment['focus_title'];
                                    $focus_order = $current_assignment['focus_order'];
                                    $key_name = $current_assignment['key_sig_name'];
                                    
                                    // Convert key names to proper musical symbols
                                    $key_name = str_replace(array('B flat', 'E flat', 'A flat', 'D flat', 'F sharp'), array('B♭', 'E♭', 'A♭', 'D♭', 'F♯'), $key_name);
                                    $tempo = $current_assignment['tempo'];
                                    $instructions = $current_assignment['instructions'];
                                    $vimeo_id = $current_assignment['vimeo_id'];
                                    $step_id = $current_assignment['step_id'];
                                    $curriculum_id = $current_assignment['curriculum_id'];
                                    $resource_pdf = $current_assignment['resource_pdf'];
                                    $resource_ireal = $current_assignment['resource_ireal'];
                                    $resource_mp3 = $current_assignment['resource_mp3'];
                                    
                                    // Get progress for this curriculum
                                    $progress = JPH_JPC_Handler::get_user_progress($user_id, $curriculum_id);
                                    $completed_keys = 0;
                                    for ($i = 1; $i <= 12; $i++) {
                                        if (!empty($progress['step_' . $i])) {
                                            $completed_keys++;
                                        }
                                    }
                                    ?>
                                    <!-- 2-Column Layout: Focus Details Left, Video Right -->
                                    <div class="jpc-main-layout">
                                        <!-- Left Column: Focus Details -->
                                        <div class="jpc-focus-column">
                                            <div class="jpc-focus-header">
                                                <h3>FOCUS: <?php echo esc_html($focus_order); ?></h3>
                                            </div>
                                            <div class="jpc-focus-details">
                                                <p><strong>KEY OF:</strong> <?php echo esc_html($key_name); ?></p>
                                                <p><strong>SUGGESTED TEMPO:</strong> <?php echo esc_html($tempo); ?> BPM 
                                                    <span class="tempo-info-icon" title="Tempo is only a suggestion. Focus on playing steady and accurately - you can go slower if needed!">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                                        </svg>
                                                    </span>
                                                </p>
                                                
                                                <?php if (!empty($resource_pdf)): ?>
                                                    <p><strong>SHEET MUSIC:</strong> <a href="/jpc_resources/<?php echo esc_attr($resource_pdf); ?>" target="_blank">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z" />
                                                        </svg>
                                                        Download
                                                    </a></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($resource_ireal)): ?>
                                                    <p><strong>iRealPro:</strong> <a href="/jpc_resources/<?php echo esc_attr($resource_ireal); ?>" target="_blank">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z" />
                                                        </svg>
                                                        Download
                                                    </a></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($resource_mp3)): ?>
                                                    <p><strong>MP3 Backing Track:</strong> <a href="/jpc_resources/<?php echo esc_attr($resource_mp3); ?>" target="_blank">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z" />
                                                        </svg>
                                                        Download
                                                    </a></p>
                                                <?php endif; ?>
                                                
                                                <p><strong>INSTRUCTIONS:</strong></p>
                                                <div class="jpc-instructions">
                                                    <p><?php echo esc_html($focus_title); ?></p>
                                                </div>
                                                
                                                <!-- Mark Complete Button -->
                                                <div class="jpc-actions">
                                                    <button class="jph-btn-primary jpc-mark-complete" 
                                                            data-step-id="<?php echo esc_attr($step_id); ?>" 
                                                            data-curriculum-id="<?php echo esc_attr($curriculum_id); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                                        </svg>
                                                        Mark Complete
                                                    </button>
                                                    
            <!-- Fix Progress Link -->
            <div class="jpc-fix-progress">
                <a href="#" class="jpc-fix-progress-link" 
                   data-user-id="<?php echo esc_attr($user_id); ?>"
                   data-current-focus="<?php echo esc_attr($focus_order); ?>"
                   data-current-key="<?php echo esc_attr($key_name); ?>">
                    Fix my progress
                </a>
            </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Right Column: Video -->
                                        <div class="jpc-video-column">
                                            <div class="jpc-video-container">
                                                <?php
                                                if (function_exists('do_shortcode')) {
                                                    echo do_shortcode("[fvplayer src='https://vimeo.com/{$vimeo_id}']");
                                                } else {
                                                    echo '<p>Video player not available. <a href="/jpc" target="_blank">View on JPC page</a></p>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- All Focuses Progress Table -->
                                    <div class="jpc-all-focuses-table">
                                        <h4>All Focuses Progress</h4>
                                        <div class="jpc-table-container">
                                            <table class="jpc-focuses-table">
                                                <thead class="jpc-sticky-header">
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Focus</th>
                                                        <th>C</th>
                                                        <th>F</th>
                                                        <th>G</th>
                                                        <th>D</th>
                                                        <th>B♭</th>
                                                        <th>A</th>
                                                        <th>E♭</th>
                                                        <th>E</th>
                                                        <th>A♭</th>
                                                        <th>D♭</th>
                                                        <th>F♯</th>
                                                        <th>B</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Get all curriculum focuses
                                                    global $wpdb;
                                                    
                                                    if (!$wpdb) {
                                                        echo '<tr><td colspan="15" style="text-align: center; padding: 20px; color: #666;">Database connection error. Please refresh the page.</td></tr>';
                                                    } else {
                                                        $all_curriculum = $wpdb->get_results(
                                                        "SELECT * FROM {$wpdb->prefix}jph_jpc_curriculum ORDER BY focus_order ASC",
                                                        ARRAY_A
                                                    );
                                                    
                                                    if (empty($all_curriculum)) {
                                                        echo '<tr><td colspan="15" style="text-align: center; padding: 20px; color: #666;">No JPC curriculum data available. Please contact support.</td></tr>';
                                                    } else {
                                                        foreach ($all_curriculum as $curriculum) {
                                                        $cur_id = $curriculum['id'];
                                                        $focus_order = $curriculum['focus_order'];
                                                        $focus_title = $curriculum['focus_title'];
                                                        $resource_pdf = $curriculum['resource_pdf'];
                                                        $resource_ireal = $curriculum['resource_ireal'];
                                                        $resource_mp3 = $curriculum['resource_mp3'];
                                                        
                                                        // Get progress for this curriculum
                                                        $cur_progress = array();
                                                        if (class_exists('JPH_JPC_Handler')) {
                                                            try {
                                                                $cur_progress = JPH_JPC_Handler::get_user_progress($user_id, $cur_id);
                                                            } catch (Exception $e) {
                                                                $cur_progress = array();
                                                            }
                                                        }
                                                        $is_current_focus = ($cur_id == $curriculum_id);
                                                        
                                                        // Check if user has started this focus (has any progress)
                                                        $has_started = false;
                                                        for ($i = 1; $i <= 12; $i++) {
                                                            if (!empty($cur_progress['step_' . $i])) {
                                                                $has_started = true;
                                                                break;
                                                            }
                                                        }
                                                        
                                                        echo '<tr' . ($is_current_focus ? ' class="current-focus"' : '') . '>';
                                                        echo '<td class="focus-id">' . $focus_order . '</td>';
                                                        echo '<td class="focus-content">';
                                                        echo '<div class="focus-title">' . $focus_title . '</div>';
                                                        
                                                        // Only show resource download links if user has started this focus
                                                        if ($has_started && !empty($resource_pdf)) {
                                                            echo '<div class="focus-resources">';
                                                            echo '<a href="/jpc_resources/' . $resource_pdf . '" target="_blank" class="resource-link">';
                                                            echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">';
                                                            echo '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />';
                                                            echo '</svg> Download PDF</a> ';
                                                            if (!empty($resource_ireal)) {
                                                                echo '<a href="/jpc_resources/' . $resource_ireal . '" target="_blank" class="resource-link">';
                                                                echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">';
                                                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />';
                                                                echo '</svg> Download iRealPro</a> ';
                                                            }
                                                            if (!empty($resource_mp3)) {
                                                                echo '<a href="/jpc_resources/' . $resource_mp3 . '" target="_blank" class="resource-link">';
                                                                echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">';
                                                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />';
                                                                echo '</svg> Download MP3</a>';
                                                            }
                                                            echo '</div>';
                                                        }
                                                        
                                                        // Check if this focus has been graded and show grade info below
                                                        $submission = $wpdb->get_row($wpdb->prepare(
                                                            "SELECT * FROM {$wpdb->prefix}jph_jpc_milestone_submissions 
                                                             WHERE user_id = %d AND curriculum_id = %d",
                                                            $user_id, $cur_id
                                                        ));
                                                        
                                                        if ($submission && !empty($submission->grade)) {
                                                            $grade_class = ($submission->grade === 'pass') ? 'grade-pass' : 'grade-redo';
                                                            $grade_text = strtoupper($submission->grade);
                                                            
                                                            echo '<div class="jpc-grade-summary">';
                                                            echo '<div class="jpc-grade-badge ' . $grade_class . '">' . $grade_text . '</div>';
                                                            
                                                            if ($submission->graded_on) {
                                                                echo '<span class="jpc-grade-date">' . date('M j, Y', strtotime($submission->graded_on)) . '</span>';
                                                            }
                                                            
                                                            if (!empty($submission->teacher_notes)) {
                                                                echo '<div class="jpc-teacher-notes-compact">';
                                                                echo '<strong>Notes:</strong> ' . esc_html($submission->teacher_notes);
                                                                echo '</div>';
                                                            }
                                                            
                                                            // Add "Submit Redo" button for redo grades
                                                            if ($submission->grade === 'redo') {
                                                                echo '<div class="jpc-submit-redo-compact">';
                                                                echo '<button class="jpc-submit-redo-btn jpc-submission-modal-trigger" 
                                                                        data-curriculum-id="' . $cur_id . '" 
                                                                        data-focus-title="' . esc_attr($focus_title) . '">';
                                                                echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">';
                                                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />';
                                                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />';
                                                                echo '</svg> Submit Redo';
                                                                echo '</button>';
                                                                echo '</div>';
                                                            }
                                                            
                                                            echo '</div>';
                                                        }
                                                        
                                                        echo '</td>';
                                                        
                                                        // Display progress for each key (C, F, G, D, B♭, A, E♭, E, A♭, D♭, F♯, B)
                                                        $key_order = array('C', 'F', 'G', 'D', 'B♭', 'A', 'E♭', 'E', 'A♭', 'D♭', 'F♯', 'B');
                                                        $completed_keys_count = 0;
                                                        for ($i = 1; $i <= 12; $i++) {
                                                            $is_completed = !empty($cur_progress['step_' . $i]);
                                                            if ($is_completed) $completed_keys_count++;
                                                            $key_name = $key_order[$i - 1];
                                                            $status_class = $is_completed ? 'completed' : 'incomplete';
                                                            
                                                            echo '<td class="center">';
                                                            if ($is_completed) {
                                                                // Get the step_id for this completed key to create video link
                                                                $step_id_for_key = $cur_progress['step_' . $i];
                                                                echo '<span class="play-link jpc-video-modal-trigger" 
                                                                        data-step-id="' . $step_id_for_key . '" 
                                                                        data-curriculum-id="' . $cur_id . '" 
                                                                        data-key-name="' . $key_name . '"
                                                                        data-focus-title="' . esc_attr($focus_title) . '"
                                                                        title="' . $key_name . ' - Click to watch video">';
                                                                echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981" width="16" height="16" class="play-icon ' . $status_class . '">';
                                                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />';
                                                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />';
                                                                echo '</svg>';
                                                                echo '</span>';
                                                            } else {
                                                                echo '<span title="' . $key_name . ' - Not completed yet">';
                                                                echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#d1d5db" width="16" height="16" class="play-icon ' . $status_class . '">';
                                                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />';
                                                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />';
                                                                echo '</svg>';
                                                                echo '</span>';
                                                            }
                                                            echo '</td>';
                                                        }
                                                        
                                                        // Add "Get Graded" column if all 12 keys completed
                                                        if ($completed_keys_count === 12) {
                                                            echo '<td class="center">';
                                                            
                                                            // Check if this focus has been graded
                                                            $submission = $wpdb->get_row($wpdb->prepare(
                                                                "SELECT * FROM {$wpdb->prefix}jph_jpc_milestone_submissions 
                                                                 WHERE user_id = %d AND curriculum_id = %d",
                                                                $user_id, $cur_id
                                                            ));
                                                            
                                                            if ($submission && !empty($submission->grade)) {
                                                                // Already graded - show dash
                                                                echo '-';
                                                            } elseif ($submission && empty($submission->grade)) {
                                                                // Submitted but not graded yet - show "Waiting..." button
                                                                echo '<span class="waiting-btn">';
                                                                echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">';
                                                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />';
                                                                echo '</svg> Waiting...';
                                                                echo '</span>';
                                                            } else {
                                                                // Not submitted yet - show "Get Graded" link (modal trigger)
                                                                echo '<a href="#" class="get-graded-btn jpc-submission-modal-trigger" 
                                                                        data-curriculum-id="' . $cur_id . '" 
                                                                        data-focus-title="' . esc_attr($focus_title) . '">';
                                                                echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">';
                                                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />';
                                                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />';
                                                                echo '</svg> Get Graded';
                                                                echo '</a>';
                                                            }
                                                            
                                                            echo '</td>';
                                                        } else {
                                                            // Not all keys completed - show dash
                                                            echo '<td class="center">-</td>';
                                                        }
                                                        echo '</tr>';
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- JPC Video Modal -->
                                    <div id="jpc-video-modal" class="jpc-modal" style="display: none;">
                                        <div class="jpc-modal-overlay"></div>
                                        <div class="jpc-modal-content">
                                            <div class="jpc-modal-header">
                                                <h3 id="jpc-modal-title">JPC Lesson Video</h3>
                                                <button type="button" class="jpc-modal-close" aria-label="Close modal">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="jpc-modal-body">
                                                <div id="jpc-video-container">
                                                    <div class="jpc-loading">Loading video...</div>
                                                </div>
                                                <div class="jpc-modal-info">
                                                    <p><strong>Focus:</strong> <span id="jpc-modal-focus"></span></p>
                                                    <p><strong>Key:</strong> <span id="jpc-modal-key"></span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- JPC Submission Modal -->
                                    <div id="jpc-submission-modal" class="jpc-modal" style="display: none;">
                                        <div class="jpc-modal-overlay"></div>
                                        <div class="jpc-modal-content">
                                            <div class="jpc-modal-header">
                                                <h3 id="jpc-submission-title">Submit for Grading</h3>
                                                <button type="button" class="jpc-modal-close" aria-label="Close modal">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="jpc-modal-body">
                                                <div id="jpc-submission-form">
                                                    <div class="jpc-submission-info">
                                                        <h4 id="jpc-submission-focus">Focus Title</h4>
                                                        <p>Please submit your YouTube video for grading. Make sure your video clearly shows you performing the focus requirements.</p>
                                                    </div>
                                                    
                                                    <form id="jpc-milestone-form">
                                                        <div class="jpc-form-group">
                                                            <label for="jpc-youtube-url">YouTube Video URL:</label>
                                                            <input type="url" id="jpc-youtube-url" name="youtube_url" required 
                                                                   placeholder="https://www.youtube.com/watch?v=..." />
                                                        </div>
                                                        
                                                        <div class="jpc-form-help">
                                                            <p><strong>Need help uploading to YouTube?</strong> 
                                                            <a href="https://jazzedge.academy/docs/uploading-to-youtube/" target="_blank">Click here for step-by-step instructions</a></p>
                                                        </div>
                                                        
                                                        <input type="hidden" id="jpc-curriculum-id" name="curriculum_id" value="" />
                                                    </form>
                                                </div>
                                                
                                                <div id="jpc-submission-success" style="display: none;">
                                                    <div class="jpc-success-message">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; color: #10b981; margin-bottom: 16px;">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                                        </svg>
                                                        <h4>Submission Successful!</h4>
                                                        <p>Your video has been submitted for grading. It may take a couple of weeks for your teacher to review and grade your submission.</p>
                                                        <p>You'll be notified when your grade is ready.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="jpc-modal-footer">
                                                <button type="button" class="jpc-btn jpc-btn-secondary jpc-modal-close">Cancel</button>
                                                <button type="button" class="jpc-btn jpc-btn-primary" id="jpc-submit-milestone">Submit for Grading</button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            } else {
                                ?>
                                <div class="jpc-login-required">
                                    <h3>Login Required</h3>
                                    <p>Please log in to access the Jazzedge Practice Curriculum™.</p>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Community Tab -->
                    <div class="jph-tab-pane" id="community-tab">
                        <!-- Community Section -->
                        <div class="jph-community-section">
                            <div class="community-header">
                                <h3>Community</h3>
                                <p>Connect with fellow musicians and share your progress!</p>
                            </div>
                            
                            <div class="community-content">
                                <?php
                                // Get current user profile data using Fluent Community ProfileHelper
                                $current_user_id = get_current_user_id();
                                $profile = null;
                                $profileSpaces = array();
                                
                                if ($current_user_id > 0 && class_exists('\FluentCommunity\App\Services\ProfileHelper')) {
                                    try {
                                        $profile = \FluentCommunity\App\Services\ProfileHelper::getProfile($current_user_id);
                                        if ($profile) {
                                            $profileSpaces = $profile->spaces;
                                        }
                                    } catch (Exception $e) {
                                    }
                                }
                                
                                // Get basic user data
                                $user_data = get_userdata($current_user_id);
                                $user_display_name = $user_data ? $user_data->display_name : 'User';
                                $user_email = $user_data ? $user_data->user_email : '';
                                $user_login = $user_data ? $user_data->user_login : '';
                                
                                // Clean username for community URL (remove @domain.com if present)
                                $clean_username = preg_replace('/@.*$/', '', $user_login);
                                
                                // Get extended profile data from wp_fcom_xprofile
                                $xprofile_data = $wpdb->get_row($wpdb->prepare("
                                    SELECT short_description, last_activity, is_verified, total_points
                                    FROM {$wpdb->prefix}fcom_xprofile 
                                    WHERE user_id = %d
                                ", $current_user_id));
                                
                                $has_description = !empty($xprofile_data->short_description);
                                $has_recent_activity = false;
                                $last_activity_days = 0;
                                
                                if (!empty($xprofile_data->last_activity)) {
                                    $last_activity_timestamp = strtotime($xprofile_data->last_activity);
                                    $last_activity_days = floor((time() - $last_activity_timestamp) / (24 * 60 * 60));
                                    $has_recent_activity = ($last_activity_days <= 7); // Active within last 7 days
                                }
                                ?>
                                
                                <!-- User Profile Section -->
                                <div class="user-profile-section">
                                    <div class="profile-header">
                                        <div class="profile-avatar">
                                            <?php echo get_avatar($current_user_id, 80, '', $user_display_name, array('class' => 'profile-avatar-img')); ?>
                                        </div>
                                        <div class="profile-info">
                                            <h4><?php echo esc_html($user_display_name); ?></h4>
                                            <p class="profile-email"><?php echo esc_html($user_email); ?></p>
                                            <div class="profile-status">
                                                <?php if ($profile): ?>
                                                    <span class="status-badge status-active">Active Member</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-pending">Profile Setup Required</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="profile-actions">
                                                <a href="https://jazzedge.academy/community/u/<?php echo esc_attr($clean_username); ?>" target="_blank" class="jph-btn jph-btn-secondary profile-update-btn">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                    </svg>
                                                    Complete Profile
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Profile Details -->
                                    <?php if ($profile): ?>
                                        <div class="profile-details">
                                            <div class="detail-grid">
                                                <?php if (!empty($profileSpaces)): ?>
                                                    <div class="detail-item">
                                                        <h6>Community Spaces</h6>
                                                        <div class="spaces-grid">
                                                            <?php foreach ($profileSpaces as $space): ?>
                                                                <a href="<?php echo esc_url('https://jazzedge.academy/community/space/' . ($space->slug ?? 'space-' . $space->id) . '/home'); ?>" target="_blank" class="space-card">
                                                                    <div class="space-icon">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                                                        </svg>
                                                                    </div>
                                                                    <div class="space-info">
                                                                        <span class="space-title"><?php echo esc_html($space->title ?? 'Untitled Space'); ?></span>
                                                                        <span class="space-visit">Visit Space</span>
                                                                    </div>
                                                                </a>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="detail-item">
                                                        <h6>Community Spaces</h6>
                                                        <div class="no-spaces-card">
                                                            <div class="no-spaces-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                                                </svg>
                                                            </div>
                                                            <p>No spaces joined yet</p>
                                                            <a href="https://jazzedge.academy/community/" target="_blank" class="jph-btn jph-btn-secondary">Explore Spaces</a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="profile-setup-prompt">
                                            <h5>Complete Your Community Profile</h5>
                                            <p>Set up your community profile to connect with other musicians and access exclusive features.</p>
                                            <a href="https://jazzedge.academy/community/u/<?php echo esc_attr($clean_username); ?>" target="_blank" class="jph-btn jph-btn-primary">Set Up Profile</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Profile Completion Encouragements -->
                                <div class="profile-encouragements">
                                    <?php if (!$has_recent_activity): ?>
                                        <div class="encouragement-card encouragement-activity">
                                            <div class="encouragement-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div class="encouragement-content">
                                                <h4>Get Active in the Community</h4>
                                                <?php if ($last_activity_days > 0): ?>
                                                    <p>You haven't been active in the community for <?php echo $last_activity_days; ?> days. Join the conversation!</p>
                                                <?php else: ?>
                                                    <p>Welcome to the community! Start engaging with fellow musicians by posting, commenting, or reacting to posts.</p>
                                                <?php endif; ?>
                                                <a href="https://jazzedge.academy/community/" target="_blank" class="jph-btn jph-btn-secondary encouragement-btn">
                                                    Visit Community
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Recent Community Videos Section -->
                                <?php
                                // Get recent community posts with videos from wp_fcom_posts table
                                global $wpdb;
                                $recent_posts = $wpdb->get_results($wpdb->prepare("
                                    SELECT p.id, p.user_id, p.title, p.slug, p.message, p.message_rendered, p.type, p.content_type, p.meta, p.created_at, p.updated_at, u.display_name, u.user_login
                                    FROM {$wpdb->prefix}fcom_posts p
                                    LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
                                    WHERE p.parent_id IS NULL 
                                    AND p.status = 'published'
                                    AND p.meta LIKE '%media_preview%'
                                    AND p.meta LIKE '%content_type%video%'
                                    ORDER BY p.created_at DESC
                                    LIMIT 8
                                "));
                                ?>
                                
                                <?php if (!empty($recent_posts)): ?>
                                    <div class="recent-posts-section">
                                        <div class="recent-posts-header">
                                            <h5>Recent Community Videos</h5>
                                            <a href="https://jazzedge.academy/community/" target="_blank" class="jph-btn jph-btn-secondary view-all-posts-btn">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                </svg>
                                                View All Posts
                                            </a>
                                        </div>
                                        <div class="posts-list">
                                            <?php foreach ($recent_posts as $post): ?>
                                                <?php
                                                // Determine post type and content
                                                $post_type = $post->type ?? 'text';
                                                $post_title = !empty($post->title) ? $post->title : '';
                                                $post_content = $post->message_rendered ?: $post->message;
                                                $post_excerpt = wp_trim_words(strip_tags($post_content), 25, '...');
                                                $post_date = date('M j, Y', strtotime($post->created_at));
                                                $post_time = date('g:i A', strtotime($post->created_at));
                                                $author_name = $post->display_name ?: $post->user_login;
                                                
                                                // Extract video data from meta field
                                                $video_embed_html = '';
                                                $video_title = '';
                                                $video_thumbnail = '';
                                                
                                                if (!empty($post->meta)) {
                                                    $meta_data = maybe_unserialize($post->meta);
                                                    if (isset($meta_data['media_preview']) && is_array($meta_data['media_preview'])) {
                                                        $media_preview = $meta_data['media_preview'];
                                                        
                                                        // Check if it's a video
                                                        if (isset($media_preview['content_type']) && $media_preview['content_type'] === 'video') {
                                                            $post_type = 'video';
                                                            
                                                            // Extract video information
                                                            $video_title = $media_preview['title'] ?? '';
                                                            $video_thumbnail = $media_preview['image'] ?? '';
                                                            
                                                            // Use the HTML embed if available, otherwise construct from URL
                                                            if (isset($media_preview['html'])) {
                                                                $video_embed_html = $media_preview['html'];
                                                            } elseif (isset($media_preview['url'])) {
                                                                $video_url = $media_preview['url'];
                                                                if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                                                                    // Extract YouTube video ID
                                                                    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $video_url, $matches);
                                                                    if (isset($matches[1])) {
                                                                        $video_id = $matches[1];
                                                                        $video_embed_html = '<iframe width="100%" height="200" src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allowfullscreen></iframe>';
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                
                                                // Get post reactions/comments count (simplified for now)
                                                $reactions_count = 0; // TODO: Fix when we know the correct column name
                                                $comments_count = 0;   // TODO: Fix when we know the correct column name
                                                ?>
                                                <div class="post-item post-type-<?php echo esc_attr($post_type); ?>">
                                                    <div class="post-header">
                                                        <div class="post-type-badge">
                                                            <?php if ($post_type === 'video'): ?>
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                                                                </svg>
                                                                Video
                                                            <?php elseif ($post_type === 'image'): ?>
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                                </svg>
                                                                Image
                                                            <?php else: ?>
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                                </svg>
                                                                Post
                                                            <?php endif; ?>
                                                        </div>
                                                        <h6 class="post-title">
                                                            <?php if (!empty($post_title)): ?>
                                                                <?php echo esc_html($post_title); ?>
                                                            <?php endif; ?>
                                                        </h6>
                                                    </div>
                                                    
                                                    <div class="post-content">
                                                        <?php if (!empty($post_excerpt)): ?>
                                                            <div class="post-excerpt">
                                                                <?php echo wp_kses_post($post_excerpt); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($post_type === 'video' && !empty($video_embed_html)): ?>
                                                            <div class="post-media">
                                                                <div class="video-container">
                                                                    <div class="video-embed"><?php echo wp_kses_post($video_embed_html); ?></div>
                                                                    <?php if (!empty($video_title)): ?>
                                                                        <div class="video-title"><?php echo esc_html($video_title); ?></div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Community Link -->
                                                        <div class="post-community-link">
                                                            <a href="https://jazzedge.academy/community/" target="_blank" class="community-link">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                                </svg>
                                                                View All Posts
                                                            </a>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="post-footer">
                                                        <div class="post-meta">
                                                            <div class="post-author">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                                                </svg>
                                                                <span><?php echo esc_html($author_name); ?></span>
                                                            </div>
                                                            <div class="post-date">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                                                </svg>
                                                                <span><?php echo esc_html($post_date); ?> at <?php echo esc_html($post_time); ?></span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="post-stats">
                                                            <?php if ($reactions_count > 0): ?>
                                                                <div class="post-stat">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558-.107 1.282.725 1.282m0 0 3.108.001a9.483 9.483 0 0 1-1.5 2.16c-.533.66-1.337 1.052-2.267 1.052H17.25M4.633 10.25a2.25 2.25 0 0 0-2.25 2.25c0 1.152.26 2.243.723 3.218.266.558.107 1.282-.725 1.282m0 0-3.108.001A9.483 9.483 0 0 1 3 13.75c.533-.66 1.337-1.052 2.267-1.052H6.75" />
                                                                    </svg>
                                                                    <span><?php echo intval($reactions_count); ?></span>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($comments_count > 0): ?>
                                                                <div class="post-stat">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                                                    </svg>
                                                                    <span><?php echo intval($comments_count); ?></span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shield Protection Tab -->
                    <div class="jph-tab-pane" id="shield-protection-tab">
                        <!-- Shield Protection Section -->
                        <div class="jph-shield-protection">
                            
                            <!-- Shield Stats and Actions -->
                            <div class="jph-protection-stats">
                                <div class="protection-item">
                                    <span class="protection-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                        </svg>
                                    </span>
                                    <span class="protection-label">Shields:</span>
                                    <span class="protection-value" id="shield-count"><?php echo esc_html($user_stats['streak_shield_count'] ?? 0); ?></span>
                                </div>
                                <div class="protection-actions">
                                    <button type="button" class="jph-btn jph-btn-primary" id="purchase-shield-btn-main" data-cost="50" data-nonce="<?php echo wp_create_nonce('purchase_shield'); ?>">
                                        <span class="btn-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                            </svg>
                                        </span>
                                        Purchase Shield (50 💎)
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Shield Information -->
                            <div class="shield-info-section">
                                <div class="shield-info-grid">
                                    <div class="shield-info-item">
                                        <h5>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16" style="display: inline-block; margin-right: 6px; vertical-align: middle;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                            </svg>
                                            What are Shields?
                                        </h5>
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
                            
                            <?php
                            $badges = $this->database->get_badges();
                            $user_badges = $this->database->get_user_badges($user_id);
                            $earned_badge_keys = array_column($user_badges, 'badge_key');
                            
                            // Group badges by category
                            $badge_categories = array(
                                'first_steps' => array('name' => 'First Steps', 'icon' => '🌟', 'description' => 'Your journey begins here'),
                                'streak_specialist' => array('name' => 'Streak Specialist', 'icon' => '🔥', 'description' => 'Consistency is key'),
                                'xp_collector' => array('name' => 'XP Collector', 'icon' => '⭐', 'description' => 'Points and progress'),
                                'session_warrior' => array('name' => 'Session Warrior', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59" /></svg>', 'description' => 'Practice makes perfect'),
                                'quality_quantity' => array('name' => 'Quality Over Quantity', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59" /></svg>', 'description' => 'Deep focus sessions'),
                                'special_achievements' => array('name' => 'Special Achievements', 'icon' => '🎭', 'description' => 'Unique accomplishments')
                            );
                            
                            $grouped_badges = array();
                            foreach ($badges as $badge) {
                                $category = $badge['category'] ?: 'achievement';
                                if (!isset($grouped_badges[$category])) {
                                    $grouped_badges[$category] = array();
                                }
                                $grouped_badges[$category][] = $badge;
                            }
                            
                            foreach ($badge_categories as $category_key => $category_info):
                                if (!isset($grouped_badges[$category_key])) continue;
                                $category_badges = $grouped_badges[$category_key];
                                
                                // Count earned badges in this category
                                $earned_in_category = 0;
                                foreach ($category_badges as $badge) {
                                    if (in_array($badge['badge_key'], $earned_badge_keys)) {
                                        $earned_in_category++;
                                    }
                                }
                            ?>
                            
                            <div class="jph-badge-category">
                                <div class="jph-category-header">
                                    <h3><?php echo $category_info['icon']; ?> <?php echo esc_html($category_info['name']); ?></h3>
                                    <p><?php echo esc_html($category_info['description']); ?></p>
                                    <div class="jph-category-progress">
                                        <span class="jph-progress-text"><?php echo $earned_in_category; ?> / <?php echo count($category_badges); ?> earned</span>
                                        <div class="jph-progress-bar">
                                            <div class="jph-progress-fill" style="width: <?php echo ($earned_in_category / count($category_badges)) * 100; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="jph-badges-table">
                                    <div class="jph-badges-header">
                                        <div class="jph-badge-status">Status</div>
                                        <div class="jph-badge-info">Badge</div>
                                        <div class="jph-badge-requirement">Requirement</div>
                                        <div class="jph-badge-rewards">Rewards</div>
                                    </div>
                                    <?php foreach ($category_badges as $badge):
                                        $is_earned = in_array($badge['badge_key'], $earned_badge_keys);
                                    ?>
                                    <div class="jph-badge-row <?php echo $is_earned ? 'earned' : 'locked'; ?>">
                                        <div class="jph-badge-status">
                                            <?php if ($is_earned): ?>
                                                <div class="jph-status-earned">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>Earned</span>
                                                </div>
                                            <?php else: ?>
                                                <div class="jph-status-locked">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                    </svg>
                                                    <span>Locked</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="jph-badge-info">
                                            <div class="jph-badge-icon">
                                                <?php if (!empty($badge['image_url'])): ?>
                                                    <img src="<?php echo esc_url($badge['image_url']); ?>" alt="<?php echo esc_attr($badge['name']); ?>" width="32" height="32">
                                                <?php else: ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path>
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                            <div class="jph-badge-details">
                                                <div class="jph-badge-title"><?php echo esc_html($badge['name']); ?></div>
                                                <div class="jph-badge-description"><?php echo esc_html($badge['description']); ?></div>
                                            </div>
                                        </div>
                                        <div class="jph-badge-requirement">
                                            <?php if (!$is_earned): ?>
                                                <div class="jph-requirement-text">
                                                    <?php
                                                    $criteria_text = '';
                                                    switch ($badge['criteria_type']) {
                                                        case 'practice_sessions':
                                                            $criteria_text = $badge['criteria_value'] . ' practice session' . ($badge['criteria_value'] > 1 ? 's' : '');
                                                            break;
                                                        case 'total_xp':
                                                            $criteria_text = $badge['criteria_value'] . ' total XP';
                                                            break;
                                                        case 'streak':
                                                            $criteria_text = $badge['criteria_value'] . '-day streak';
                                                            break;
                                                        case 'long_session_count':
                                                            $criteria_text = $badge['criteria_value'] . ' session' . ($badge['criteria_value'] > 1 ? 's' : '') . ' over 30 min';
                                                            break;
                                                        case 'comeback':
                                                            $criteria_text = 'Return after 7+ day break';
                                                            break;
                                                        case 'time_of_day':
                                                            $criteria_text = $badge['criteria_value'] == 1 ? '10 early morning sessions' : '10 night sessions';
                                                            break;
                                                        default:
                                                            $criteria_text = 'Complete requirements';
                                                    }
                                                    echo esc_html($criteria_text);
                                                    ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="jph-requirement-complete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                    </svg>
                                                    <span>Complete</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="jph-badge-rewards">
                                            <?php if ($badge['xp_reward'] > 0): ?>
                                                <div class="jph-reward-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                                    </svg>
                                                    <span><?php echo $badge['xp_reward']; ?> XP</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($badge['gem_reward'] > 0): ?>
                                                <div class="jph-reward-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                                                    </svg>
                                                    <span><?php echo $badge['gem_reward']; ?> Gems</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Analytics Tab -->
                    <div class="jph-tab-pane" id="analytics-tab">
                        <!-- Analytics Section -->
                        <div class="jph-analytics-section">
                            <div class="analytics-grid">
                                <!-- Practice Time Overview -->
                                <div class="analytics-card practice-time-card">
                                    <div class="card-header">
                                        <h4>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Practice Time
                                        </h4>
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
                                        <h4>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.773 4.773zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Practice Sessions
                                        </h4>
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
                                        <h4>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                            </svg>
                                            Practice Insights
                                        </h4>
                                        <span class="card-subtitle">Your practice patterns</span>
                                    </div>
                                    <div class="insights-list">
                                        <div class="insight-item">
                                            <span class="insight-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.773 4.773zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </span>
                                            <div class="insight-content">
                                                <span class="insight-label">Consistency Score</span>
                                                <span class="insight-value" id="analytics-consistency">-</span>
                                            </div>
                                        </div>
                                        <div class="insight-item">
                                            <span class="insight-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                                                </svg>
                                            </span>
                                            <div class="insight-content">
                                                <span class="insight-label">Improvement Rate</span>
                                                <span class="insight-value" id="analytics-improvement-rate">-</span>
                                            </div>
                                        </div>
                                        <div class="insight-item">
                                            <span class="insight-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
                                                </svg>
                                            </span>
                                            <div class="insight-content">
                                                <span class="insight-label">Mood Rating</span>
                                                <span class="insight-value" id="analytics-sentiment">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Practice Chart -->
                                    <div class="practice-chart-container">
                                        <div class="chart-header">
                                            <h5>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16" style="display: inline-block; margin-right: 6px; vertical-align: middle;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                                </svg>
                                                Practice Trends
                                            </h5>
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
                                        <h4>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                            </svg>
                                            AI Practice Analysis
                                        </h4>
                                        <span class="card-subtitle">Personalized insights from your practice data</span>
                                    </div>
                                    <div class="ai-analysis-content">
                                        <div class="ai-analysis-text" id="ai-analysis-text">
                                            <div class="ai-analysis-placeholder">
                                                <h5>Ready for AI Analysis</h5>
                                                <p>Click the button below to generate personalized insights from your practice data.</p>
                                                <p class="ai-placeholder-note">Analysis covers your last 30 days of practice sessions.</p>
                                            </div>
                                        </div>
                                        <div class="ai-analysis-footer">
                                            <div class="ai-data-period" id="ai-data-period">
                                                <span class="period-label">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                                    </svg>
                                                </span>
                                                <span class="period-text">Last 30 days</span>
                                            </div>
                                            <div class="ai-analysis-actions">
                                                <button type="button" class="ai-generate-btn" id="ai-generate-btn">
                                                    <span class="generate-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                                        </svg>
                                                    </span>
                                                    Generate AI Analysis
                                                </button>
                                                <button type="button" class="ai-print-btn" id="ai-print-btn" style="display: none;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m-10.56 0L5.34 5.19m8.92 8.64L18.66 5.19M9.88 8.625h4.24m-4.24 0a3 3 0 00-3 3v6a3 3 0 003 3h4.24a3 3 0 003-3v-6a3 3 0 00-3-3m-4.24 0V6.75a2.25 2.25 0 012.25-2.25h2.25a2.25 2.25 0 012.25 2.25v1.875" />
                                                    </svg>
                                                    Print Analysis
                                                </button>
                                            </div>
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
                                <div class="practice-history-title-section">
                                    <h3>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24" style="margin-right: 8px; vertical-align: middle;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                        </svg>
                                        Your Practice History
                                    </h3>
                                    <button type="button" class="jph-back-to-practice-btn" id="jph-back-to-practice-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                        </svg>
                                        See Practice Items
                                    </button>
                                </div>
                                <div class="practice-history-controls">
                                    <button id="export-history-btn" class="jph-btn jph-btn-secondary">
                                        <span class="btn-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                        </span>
                                        Export CSV
                                    </button>
                                </div>
                            </div>
                            <div class="practice-history-header">
                                <div class="practice-history-header-item">Date</div>
                                <div class="practice-history-header-item">Item</div>
                                <div class="practice-history-header-item center">Duration</div>
                                <div class="practice-history-header-item center mobile-hidden">How it felt</div>
                                <div class="practice-history-header-item center mobile-hidden">Improvement</div>
                                <div class="practice-history-header-item center mobile-hidden">Actions</div>
                            </div>
                            <div class="practice-history-list" id="practice-history-list">
                                <div class="loading-message">Loading practice history...</div>
                            </div>
                            <div id="load-more-container" style="text-align: center; margin-top: 20px; display: none;">
                                <button id="load-more-sessions-bottom" class="jph-btn jph-btn-secondary">
                                    <span class="btn-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                        </svg>
                                    </span>
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
                            <input type="text" id="jph-welcome-display-name-input" name="display_name" class="jph-input" placeholder="Enter your leaderboard name" maxlength="100">
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
                            <input type="text" id="jph-display-name-input" name="display_name" class="jph-input" placeholder="Enter your leaderboard name" maxlength="100">
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
                        <label>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16" style="display: inline-block; margin-right: 6px; vertical-align: middle;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Duration:
                        </label>
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
                        <label>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16" style="display: inline-block; margin-right: 6px; vertical-align: middle;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
                            </svg>
                            How did it go?
                        </label>
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
                                <div class="sentiment-emoji">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
                                    </svg>
                                </div>
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
                        <label>Did you notice improvement?</label>
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
                        <label>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16" style="display: inline-block; margin-right: 6px; vertical-align: middle;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Notes (optional):
                        </label>
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
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        Purchase Streak Shield
                    </h2>
                    <span class="jph-modal-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <p>A Streak Shield protects your current streak from being broken if you miss a day of practice.</p>
                    <p><strong>Cost:</strong> 50 💎</p>
                    <p><strong>Current Shields:</strong> <span id="shield-count-modal"><?php echo $user_stats['streak_shield_count'] ?? 0; ?></span></p>
                    <p><strong>Maximum:</strong> 3 shields</p>
                    
                    <div class="jph-modal-footer">
                        <button id="purchase-shield-btn" class="jph-btn jph-btn-primary" data-cost="50" data-nonce="<?php echo wp_create_nonce('purchase_shield'); ?>">Purchase Shield (50 💎)</button>
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
                                    <div class="card-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </div>
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
        
        <!-- Add Repertoire Modal -->
        <div id="jph-add-repertoire-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>Add Repertoire</h2>
                    <span class="jph-modal-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <form id="add-repertoire-form">
                        <div class="form-group">
                            <label>Title:</label>
                            <input type="text" name="title" placeholder="e.g., Autumn Leaves" required maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label>Composer:</label>
                            <input type="text" name="composer" placeholder="e.g., Joseph Kosma" required maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label>My Notes:</label>
                            <span style="color: #6c757d; font-size: 0.9em;">255 Characters</span>
                            <textarea name="notes" placeholder="Add any notes about this piece..." maxlength="255" style="resize: vertical; min-height: 100px;"></textarea>
                        </div>
                        
                        <div class="jph-modal-footer">
                            <button type="button" class="jph-btn jph-btn-secondary jph-modal-close">Cancel</button>
                            <button type="submit" class="jph-btn jph-btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16" style="display: inline-block; margin-right: 4px; vertical-align: middle;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                                Save Repertoire
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Edit Repertoire Modal -->
        <div id="jph-edit-repertoire-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>Edit Repertoire</h2>
                    <span class="jph-modal-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <form id="edit-repertoire-form">
                        <input type="hidden" name="item_id" id="edit-repertoire-id">
                        
                        <div class="form-group">
                            <label>Title:</label>
                            <input type="text" name="title" id="edit-repertoire-title" required maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label>Composer:</label>
                            <input type="text" name="composer" id="edit-repertoire-composer" required maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label>My Notes:</label>
                            <span style="color: #6c757d; font-size: 0.9em;">255 Characters</span>
                            <textarea name="notes" id="edit-repertoire-notes" maxlength="255" style="resize: vertical; min-height: 100px;"></textarea>
                        </div>
                        
                        <div class="jph-modal-footer">
                            <button type="button" class="jph-btn jph-btn-secondary jph-modal-close">Cancel</button>
                            <button type="submit" class="jph-btn jph-btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16" style="display: inline-block; margin-right: 4px; vertical-align: middle;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                                Update Repertoire
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Stats Explanation Modal -->
        <div id="stats-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        Stats Explanation
                    </h2>
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
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
            background: #F04E23 !important;
            color: white !important;
        }

        .jph-btn-primary:hover {
            background: #e0451f !important;
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
        
        .jph-tutorial-btn,
        .jph-leaderboard-btn,
        .jph-stats-help-btn {
            min-width: 120px;
            max-width: 140px;
            justify-content: center;
            padding: 10px 12px;
            white-space: nowrap;
            text-align: center;
            font-size: 14px;
            line-height: 1.2;
        }
        
        /* ============================================
           Search Section - Professional 2-Column Layout
           ============================================ */
        
        /* Main Container */
        .jph-search-section {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin: 30px 0 20px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(226, 232, 240, 0.8);
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Grid Layout - 40% Left, 60% Right */
        .search-section-grid {
            display: grid;
            grid-template-columns: 40% 60%;
            gap: 40px;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            align-items: start;
            box-sizing: border-box;
        }
        
        /* Base Component Variables */
        :root {
            --input-border: 1px solid #e5e7eb;
            --input-border-radius: 12px;
            --input-padding: 12px 14px;
            --input-bg: #ffffff;
            --input-color: #1f2937;
            --input-font-size: 15px;
            --input-font-weight: 400;
            --input-line-height: 1.5;
            --spacing-xs: 12px;
            --spacing-sm: 16px;
            --spacing-md: 32px;
            --card-bg: #f8fafc;
            --border-color-hover: #d1d5db;
        }

        /* Section Headings */
        .section-heading {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 var(--spacing-xs) 0;
            line-height: 1.5;
            padding: 0;
        }

        /* Column Containers - Ensure containment */
        .search-left-column,
        .search-right-column {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Base Input Component - ALL inputs inherit this */
        .base-input-component {
            width: 100%;
            padding: var(--input-padding);
            border: var(--input-border);
            border-radius: var(--input-border-radius);
            background: var(--input-bg);
            color: var(--input-color);
            font-size: var(--input-font-size);
            font-weight: var(--input-font-weight);
            line-height: var(--input-line-height);
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }

        .base-input-component:hover {
            border-color: var(--border-color-hover);
        }

        .base-input-component:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Last Lesson Card - Uses base styling */
        .last-lesson-card {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            padding: var(--input-padding);
            border: var(--input-border);
            border-radius: var(--input-border-radius);
            background: var(--card-bg);
            margin-bottom: var(--spacing-xs);
            transition: all 0.2s ease;
            cursor: pointer;
            box-sizing: border-box;
        }

        .last-lesson-card:hover {
            background: #f1f5f9;
            border-color: var(--border-color-hover);
        }

        .last-lesson-icon {
            width: 24px;
            height: 24px;
            color: #3b82f6;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .last-lesson-icon svg {
            width: 100%;
            height: 100%;
        }

        .last-lesson-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            min-width: 0;
        }

        .last-lesson-link {
            color: #1f2937;
            text-decoration: none;
            font-weight: 600;
            font-size: var(--input-font-size);
            line-height: 1.4;
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .last-lesson-link:hover {
            color: #3b82f6;
        }

        .last-lesson-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 400;
            line-height: 1.4;
        }

        /* Wrapper Elements - Consistent Spacing */
        .favorites-dropdown-wrapper,
        .collections-dropdown-wrapper {
            width: 100%;
            margin: 0 0 var(--spacing-xs) 0;
        }

        .search-input-wrapper {
            width: 100%;
            margin: 0 0 var(--spacing-xs) 0;
        }

        /* Standard Dropdown - Extends base component */
        .standard-dropdown {
            width: 100%;
            padding: var(--input-padding);
            padding-right: 40px;
            border: var(--input-border);
            border-radius: var(--input-border-radius);
            background: var(--input-bg);
            color: var(--input-color);
            font-size: var(--input-font-size);
            font-weight: var(--input-font-weight);
            line-height: var(--input-line-height);
            cursor: pointer;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%236B7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            transition: border-color 0.2s ease, background-color 0.2s ease;
            box-sizing: border-box;
        }

        .standard-dropdown:hover {
            border-color: var(--border-color-hover);
            background-color: var(--input-bg);
        }

        .standard-dropdown:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .standard-dropdown option {
            padding: 8px;
            color: #1f2937;
        }

        /* Search Input - Extends base component */
        .search-input-wrapper {
            position: relative;
        }

        .search-input-wrapper #alm-lesson-search-compact {
            width: 100%;
        }

        .search-input-wrapper #alm-lesson-search-compact input[type="search"] {
            width: 100%;
            padding: var(--input-padding);
            border: var(--input-border);
            border-radius: var(--input-border-radius);
            background: var(--input-bg);
            color: var(--input-color);
            font-size: var(--input-font-size);
            font-weight: var(--input-font-weight);
            line-height: var(--input-line-height);
            outline: none;
            transition: border-color 0.2s ease, background-color 0.2s ease;
            box-sizing: border-box;
        }

        .search-input-wrapper #alm-lesson-search-compact input[type="search"]:hover {
            border-color: var(--border-color-hover);
            background-color: var(--input-bg);
        }

        .search-input-wrapper #alm-lesson-search-compact input[type="search"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .search-input-wrapper #alm-lesson-search-compact input[type="search"]::placeholder {
            color: #9ca3af;
        }

        /* ============================================
           Prominent Search Card - Right Column (60%)
           Professional Design to Make It Pop
           ============================================ */
        
        .search-prominent-card {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(59, 130, 246, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: visible;
            box-sizing: border-box;
            width: 100%;
            max-width: 95%;
            margin-right: 16px;
        }
        
        .search-prominent-card * {
            box-sizing: border-box;
        }

        .search-prominent-card:hover {
            box-shadow: 0 8px 30px rgba(59, 130, 246, 0.12), 0 0 0 1px rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }

        /* Search Header */
        .search-header {
            margin-bottom: 24px;
        }

        .search-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }

        .search-heading-icon {
            width: 28px;
            height: 28px;
            color: #3b82f6;
            flex-shrink: 0;
        }

        .search-subtitle {
            font-size: 15px;
            color: #6b7280;
            margin: 0;
            line-height: 1.5;
            font-weight: 400;
        }

        /* Prominent Search Input - Force containment */
        .search-input-wrapper-prominent {
            width: 100% !important;
            max-width: 100% !important;
            margin-bottom: 28px;
            position: relative;
            overflow: visible !important;
            box-sizing: border-box !important;
            display: block !important;
        }

        .search-input-wrapper-prominent #alm-lesson-search-compact {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            position: relative !important;
            box-sizing: border-box !important;
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Ensure dropdown panel stays within bounds */
        .search-input-wrapper-prominent #alm-lesson-search-compact > div {
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
            width: 100% !important;
            left: 0 !important;
            right: 0 !important;
        }

        .search-input-wrapper-prominent #alm-lesson-search-compact input[type="search"],
        .search-input-wrapper-prominent #alm-lesson-search-compact input {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            padding: 18px 48px 18px 20px !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 14px !important;
            background: #ffffff !important;
            color: #1f2937 !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 1.5 !important;
            outline: none !important;
            transition: all 0.3s ease;
            box-sizing: border-box !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            margin: 0 !important;
            display: block !important;
        }

        .search-input-wrapper-prominent #alm-lesson-search-compact input[type="search"]:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .search-input-wrapper-prominent #alm-lesson-search-compact input[type="search"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1), 0 4px 16px rgba(59, 130, 246, 0.15);
            outline: none;
            transform: translateY(-1px);
        }

        .search-input-wrapper-prominent #alm-lesson-search-compact input[type="search"]::placeholder {
            color: #9ca3af;
            font-weight: 400;
        }

        /* Search Input Icon - Right Aligned Heroicon */
        .search-input-wrapper-prominent #alm-lesson-search-compact::after {
            content: '';
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            z-index: 10;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg fill='none' stroke='%236b7280' stroke-width='2' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
        }

        /* Search Tips Section */
        .search-tips {
            background: linear-gradient(135deg, #f0f9ff 0%, #f8fafc 100%);
            border: 1px solid #e0f2fe;
            border-radius: 12px;
            padding: 20px;
            margin-top: 0;
        }

        .search-tips-heading {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 14px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-tips-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .search-tips-list li {
            font-size: 14px;
            color: #475467;
            line-height: 1.6;
            padding-left: 24px;
            position: relative;
        }

        .search-tips-list li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: #3b82f6;
            font-weight: 600;
        }

        .search-tips-list li strong {
            color: #1f2937;
            font-weight: 600;
        }

        /* ============================================
           Responsive Design
           ============================================ */
        
        @media (max-width: 768px) {
            .jph-search-section {
                padding: 24px;
                border-radius: 12px;
            }

            .search-section-grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }

            .search-prominent-card {
                padding: 24px;
                border-radius: 12px;
                margin-right: 0;
            }

            .search-heading {
                font-size: 20px;
            }

            .search-subtitle {
                font-size: 14px;
            }

            .search-input-wrapper-prominent #alm-lesson-search-compact input[type="search"] {
                padding: 16px 18px;
                padding-left: 48px;
                font-size: 15px;
            }

            .search-tips {
                padding: 16px;
            }

            .search-tips-heading {
                font-size: 15px;
                margin-bottom: 12px;
            }

            .search-tips-list li {
                font-size: 13px;
            }

            .section-heading {
                font-size: 15px;
            }

            .last-lesson-card {
                padding: 14px;
            }

            .standard-dropdown {
                padding: 12px 14px;
                font-size: 14px;
            }
        }

        .search-placeholder {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            opacity: 0.7;
        }

        .search-icon {
            width: 24px;
            height: 24px;
            color: #6b7280;
            flex-shrink: 0;
        }

        .search-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .search-label {
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 500;
        }

        .search-description {
            font-size: 0.8rem;
            color: #9ca3af;
        }

        /* Responsive Design - Pixel Perfect */
        @media (max-width: 768px) {
            .lesson-search-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            
            .section-heading {
                font-size: 15px;
                margin: 0 0 14px 0;
            }
            
            .last-lesson-card {
                padding: 14px 16px;
                min-height: 76px;
                margin-bottom: 14px;
            }
            
            .favorites-dropdown-wrapper,
            .collections-dropdown-wrapper,
            .search-input-wrapper {
                margin-top: 14px;
                margin-bottom: 14px;
            }
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
            background: #459E90 !important;
            color: white !important;
            border: none !important;
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
            background: #3a8a7c !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(69, 158, 144, 0.3);
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
        
        .jph-badges-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        
        .jph-badges-header {
            display: grid;
            grid-template-columns: 120px 1fr 200px 150px;
            gap: 16px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-bottom: 2px solid #dee2e6;
            font-weight: 700;
            font-size: 0.9em;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jph-badge-row {
            display: grid;
            grid-template-columns: 120px 1fr 200px 150px;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f3f4;
            transition: all 0.2s ease;
            align-items: center;
        }
        
        .jph-badge-row:hover {
            background: #f8f9fa;
        }
        
        .jph-badge-row.earned {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.05), rgba(255, 235, 59, 0.05));
        }
        
        .jph-badge-row.earned:hover {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 235, 59, 0.1));
        }
        
        .jph-badge-row.locked {
            opacity: 0.7;
        }
        
        .jph-badge-row.locked:hover {
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .jph-badges-header,
            .jph-badge-row {
                grid-template-columns: 100px 1fr 180px 120px;
                gap: 12px;
                padding: 12px 16px;
            }
        }
        
        @media (max-width: 480px) {
            .jph-badges-header {
                display: none;
            }
            
            .jph-badge-row {
                display: block;
                padding: 16px;
                border-radius: 8px;
                margin-bottom: 8px;
                background: white;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            
            .jph-badge-row.earned {
                background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 235, 59, 0.1));
            }
        }
        
        /* Status Column Styles */
        .jph-badge-status {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .jph-status-earned {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #28a745;
            font-weight: 600;
            font-size: 0.85em;
        }
        
        .jph-status-locked {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.85em;
        }
        
        .jph-status-earned svg {
            color: #28a745;
        }
        
        .jph-status-locked svg {
            color: #6c757d;
        }
        
        /* Badge Info Column Styles */
        .jph-badge-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .jph-badge-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .jph-badge-row.earned .jph-badge-icon {
            background: linear-gradient(135deg, #fff8e1, #fff3cd);
            border-color: #ffd700;
        }
        
        .jph-badge-row.locked .jph-badge-icon {
            background: #e9ecef;
            border-color: #dee2e6;
            opacity: 0.6;
        }
        
        .jph-badge-icon img {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .jph-badge-icon svg {
            color: #6c757d;
        }
        
        .jph-badge-row.earned .jph-badge-icon svg {
            color: #b8860b;
        }
        
        .jph-badge-details {
            flex: 1;
            min-width: 0;
        }
        
        .jph-badge-title {
            font-weight: 700;
            color: #333;
            font-size: 1em;
            margin-bottom: 4px;
            line-height: 1.2;
        }
        
        .jph-badge-row.earned .jph-badge-title {
            color: #b8860b;
        }
        
        .jph-badge-row.locked .jph-badge-title {
            color: #6c757d;
        }
        
        .jph-badge-description {
            font-size: 0.85em;
            color: #666;
            line-height: 1.3;
        }
        
        .jph-badge-row.earned .jph-badge-description {
            color: #8b6914;
        }
        
        .jph-badge-row.locked .jph-badge-description {
            color: #6c757d;
        }
        
        /* Requirement Column Styles */
        .jph-badge-requirement {
            display: flex;
            align-items: center;
        }
        
        .jph-requirement-text {
            color: #495057;
            font-size: 0.85em;
            font-weight: 500;
            line-height: 1.3;
        }
        
        .jph-requirement-complete {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #28a745;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .jph-requirement-complete svg {
            color: #28a745;
        }
        
        /* Rewards Column Styles */
        .jph-badge-rewards {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .jph-reward-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8em;
            font-weight: 600;
            color: #495057;
        }
        
        .jph-badge-row.earned .jph-reward-item {
            color: #28a745;
        }
        
        .jph-badge-row.locked .jph-reward-item {
            color: #6c757d;
        }
        
        .jph-reward-item svg {
            color: #ffc107;
        }
        
        .jph-badge-row.earned .jph-reward-item svg {
            color: #ffc107;
        }
        
        .jph-badge-row.locked .jph-reward-item svg {
            color: #6c757d;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 480px) {
            .jph-badge-info {
                margin-bottom: 12px;
            }
            
            .jph-badge-icon {
                width: 48px;
                height: 48px;
            }
            
            .jph-badge-icon img {
                width: 40px;
                height: 40px;
            }
            
            .jph-badge-title {
                font-size: 1.1em;
                margin-bottom: 6px;
            }
            
            .jph-badge-description {
                font-size: 0.9em;
                margin-bottom: 8px;
            }
            
            .jph-badge-status {
                justify-content: flex-start;
                margin-bottom: 8px;
            }
            
            .jph-badge-requirement {
                margin-bottom: 8px;
            }
            
            .jph-badge-rewards {
                flex-direction: row;
                gap: 12px;
            }
        }
        
        
        .jph-badge-category {
            margin-bottom: 40px;
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .jph-category-header {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .jph-category-header h3 {
            margin: 0 0 8px 0;
            color: #004555;
            font-size: 1.4em;
            font-weight: 700;
        }
        
        .jph-category-header p {
            margin: 0 0 16px 0;
            color: #666;
            font-size: 0.95em;
        }
        
        .jph-category-progress {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .jph-progress-text {
            font-size: 0.9em;
            font-weight: 600;
            color: #004555;
        }
        
        .jph-progress-bar {
            width: 200px;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .jph-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #007cba 0%, #00a0d2 100%);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .jph-badge-earned-date {
            font-size: 0.85em;
            color: #28a745;
            font-weight: 600;
            margin-top: 10px;
            text-align: center;
        }
        
        /* Accessibility and Focus States */
        .jph-badge-row:focus {
            outline: 3px solid #007cba;
            outline-offset: 2px;
        }
        
        .jph-badge-row:focus-visible {
            outline: 3px solid #007cba;
            outline-offset: 2px;
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .jph-badge-row {
                transition: none !important;
            }
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .jph-badge-row {
                border: 2px solid #000;
            }
            
            .jph-badge-row.earned {
                border-color: #000;
                background: #fff;
            }
            
            .jph-badge-row.locked {
                border-color: #666;
                background: #f0f0f0;
            }
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
        
        /* Community Tab Styles */
        .jph-community-section {
            background: white;
            border-radius: 16px;
            border: 2px solid #e8f5f4;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 69, 85, 0.08);
        }
        
        .community-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .community-header h3 {
            color: #004555;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        
        .community-header p {
            color: #666;
            font-size: 16px;
            margin: 0;
        }
        
        .community-placeholder {
            text-align: center;
            padding: 40px 20px;
        }
        
        .placeholder-icon {
            margin-bottom: 20px;
            color: #459E90;
        }
        
        .community-placeholder h4 {
            color: #004555;
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 15px 0;
        }
        
        .community-placeholder p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 30px 0;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .coming-soon-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        
        .feature-item:hover {
            background: #e8f5f4;
            border-color: #459E90;
            transform: translateY(-2px);
        }
        
        .feature-icon {
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .feature-item span:last-child {
            color: #004555;
            font-weight: 500;
            font-size: 14px;
        }
        
        /* User Profile Section Styles */
        .user-profile-section {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .profile-avatar {
            flex-shrink: 0;
        }
        
        .profile-avatar-img {
            border-radius: 50%;
            border: 3px solid #459E90;
        }
        
        .profile-info h4 {
            margin: 0 0 5px 0;
            color: #004555;
            font-size: 24px;
            font-weight: 700;
        }
        
        .profile-email {
            margin: 0 0 10px 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .profile-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        
        .profile-update-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            padding: 8px 16px;
            min-width: 140px;
            flex-shrink: 0;
        }
        
        .spaces-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        
        .space-card {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .space-card:hover {
            background: #e8f5f4;
            border-color: #459E90;
            transform: translateY(-1px);
        }
        
        .space-icon {
            margin-right: 12px;
            color: #459E90;
        }
        
        .space-info {
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        
        .space-title {
            font-weight: 500;
            color: #004555;
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .space-visit {
            font-size: 12px;
            color: #6b7280;
        }
        
        .no-spaces-card {
            text-align: center;
            padding: 30px 20px;
            background: #f8fafc;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
        }
        
        .no-spaces-icon {
            margin-bottom: 15px;
            color: #9ca3af;
        }
        
        .no-spaces-card p {
            margin: 0 0 15px 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        .no-spaces-card .jph-btn {
            font-size: 14px;
            padding: 8px 16px;
        }
        
        .detail-item h6 {
            margin: 0 0 15px 0;
            color: #004555;
            font-size: 16px;
            font-weight: 600;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 12px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 2px 8px rgba(0, 69, 85, 0.1);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #F04E23;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .profile-details {
            background: white;
            border-radius: 12px;
            padding: 25px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 2px 8px rgba(0, 69, 85, 0.1);
        }
        
        .profile-details h5 {
            margin: 0 0 20px 0;
            color: #004555;
            font-size: 18px;
            font-weight: 600;
        }
        
        .profile-details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .profile-details-header h5 {
            margin: 0;
        }
        
        .profile-update-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            padding: 8px 16px;
        }
        
        .spaces-list {
            margin: 0;
            padding-left: 0;
            list-style: none;
        }
        
        .spaces-list li {
            margin-bottom: 8px;
        }
        
        .space-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #459E90;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        .space-link:hover {
            color: #3a8a7c;
            text-decoration: underline;
        }
        
        .space-link svg {
            flex-shrink: 0;
            opacity: 0.7;
        }
        
        .no-spaces {
            color: #6b7280;
            font-style: italic;
            margin: 0;
        }
        
        .no-spaces a {
            color: #459E90;
            text-decoration: none;
            font-weight: 500;
        }
        
        .no-spaces a:hover {
            text-decoration: underline;
        }
        
        /* Profile Encouragements */
        .profile-encouragements {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .encouragement-card {
            background: #fff;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            padding: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: all 0.2s ease;
        }
        
        .encouragement-card:hover {
            border-color: #3498db;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.1);
        }
        
        .encouragement-profile {
            border-left: 4px solid #F04E23;
        }
        
        .encouragement-activity {
            border-left: 4px solid #2ECC71;
        }
        
        .encouragement-icon {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        
        .encouragement-profile .encouragement-icon {
            background: rgba(240, 78, 35, 0.1);
            color: #F04E23;
        }
        
        .encouragement-activity .encouragement-icon {
            background: rgba(46, 204, 113, 0.1);
            color: #2ECC71;
        }
        
        .encouragement-content {
            flex: 1;
        }
        
        .encouragement-content h4 {
            color: #2c3e50;
            margin: 0 0 0.5rem 0;
            font-size: 1.125rem;
            font-weight: 600;
        }
        
        .encouragement-content p {
            color: #5a6c7d;
            margin: 0 0 1rem 0;
            font-size: 0.875rem;
            line-height: 1.4;
        }
        
        .encouragement-btn {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }

        /* Recent Posts Section Styles */
        .recent-posts-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 2px 8px rgba(0, 69, 85, 0.1);
            margin-top: 30px;
        }
        
        .recent-posts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .recent-posts-header h5 {
            margin: 0;
            color: #004555;
            font-size: 18px;
            font-weight: 600;
        }
        
        .view-all-posts-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            padding: 8px 16px;
        }
        
        .posts-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .post-item {
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        
        .post-item:hover {
            background: #e8f5f4;
            border-color: #459E90;
            transform: translateY(-1px);
        }
        
        .post-header {
            margin-bottom: 10px;
        }
        
        .post-title {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        .post-title a {
            color: #004555;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .post-title a:hover {
            color: #459E90;
            text-decoration: underline;
        }
        
        .post-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .post-author {
            font-weight: 500;
        }
        
        .post-date {
            font-style: italic;
        }
        
        .post-excerpt {
            color: #374151;
            font-size: 14px;
            line-height: 1.5;
        }
        
        /* Enhanced Post Styles */
        .post-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            background: #e8f5f4;
            color: #004555;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .post-type-badge svg {
            width: 12px;
            height: 12px;
        }
        
        .post-content {
            margin: 12px 0;
        }
        
        .post-excerpt {
            color: #374151;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 12px;
        }
        
        .post-media {
            margin-top: 12px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .post-media iframe,
        .post-media video {
            width: 100%;
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        
        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            margin-top: 12px;
        }
        
        .post-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .post-author,
        .post-date {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .post-author svg,
        .post-date svg {
            color: #9ca3af;
        }
        
        .post-stats {
            display: flex;
            gap: 12px;
        }
        
        .post-stat {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .post-stat svg {
            color: #9ca3af;
        }
        
        .post-type-video .post-type-badge {
            background: #fef3c7;
            color: #92400e;
        }
        
        .post-type-image .post-type-badge {
            background: #dbeafe;
            color: #1e40af;
        }
        
        /* Video Embed Styles */
        .video-container {
            margin: 12px 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            background: #000;
            position: relative;
        }
        
        .video-embed {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            overflow: hidden;
        }
        
        .video-embed iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .video-title {
            padding: 0.75rem 1rem;
            background: #fff;
            font-size: 0.875rem;
            color: #2c3e50;
            font-weight: 600;
            border-top: 1px solid #e1e8ed;
        }
        
        /* Community Link Styles */
        .post-community-link {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e1e8ed;
        }
        
        .community-link {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            color: #3498db;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .community-link:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        .community-link svg {
            flex-shrink: 0;
        }
        
        /* Responsive video embeds */
        @media (max-width: 768px) {
            .video-container {
                margin: 8px 0;
                border-radius: 8px;
            }
            
            .video-title {
                padding: 0.5rem 0.75rem;
                font-size: 0.8125rem;
            }
            
            .post-community-link {
                margin-top: 0.5rem;
                padding-top: 0.5rem;
            }
            
            .community-link {
                font-size: 0.6875rem;
            }
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .detail-item {
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .detail-item strong {
            color: #004555;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 10px;
        }
        
        .detail-item ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .detail-item li {
            color: #374151;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .profile-setup-prompt {
            text-align: center;
            padding: 40px 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #d1d5db;
        }
        
        .profile-setup-prompt h5 {
            margin: 0 0 15px 0;
            color: #004555;
            font-size: 20px;
            font-weight: 600;
        }
        
        .profile-setup-prompt p {
            margin: 0 0 25px 0;
            color: #6b7280;
            font-size: 16px;
        }
        
        /* Mobile Responsive Adjustments */
        @media (max-width: 768px) {
            /* Shield Tab - Mobile Layout */
            .jph-protection-stats {
                flex-direction: column !important;
                gap: 15px !important;
                align-items: stretch !important;
            }
            
            .protection-item {
                justify-content: center !important;
                padding: 15px !important;
                background: #f8fafc !important;
                border-radius: 8px !important;
                border: 1px solid #e2e8f0 !important;
            }
            
            .protection-actions {
                width: 100% !important;
            }
            
            #purchase-shield-btn-main {
                width: 100% !important;
                min-width: auto !important;
                font-size: 14px !important;
                padding: 12px 16px !important;
            }
            
            /* History Tab - Mobile Table */
            .jph-practice-history-table {
                font-size: 14px !important;
            }
            
            .jph-practice-history-table th,
            .jph-practice-history-table td {
                padding: 8px 4px !important;
            }
            
            /* Hide columns on mobile - only show Date, Item, Duration (hide Actions and other columns) */
            .jph-practice-history-table th:nth-child(4),
            .jph-practice-history-table th:nth-child(5),
            .jph-practice-history-table td:nth-child(4),
            .jph-practice-history-table td:nth-child(5) {
                display: none !important;
            }
            
            /* Make remaining columns full width on mobile */
            .jph-practice-history-table {
                width: 100% !important;
            }
            
            .jph-practice-history-table th:nth-child(1),
            .jph-practice-history-table td:nth-child(1) {
                width: 25% !important;
            }
            
            .jph-practice-history-table th:nth-child(2),
            .jph-practice-history-table td:nth-child(2) {
                width: 35% !important;
            }
            
            .jph-practice-history-table th:nth-child(3),
            .jph-practice-history-table td:nth-child(3) {
                width: 40% !important;
            }
            
            /* Additional mobile-specific adjustments for practice history */
            .practice-history-item .practice-history-item-content:nth-child(4),
            .practice-history-item .practice-history-item-content:nth-child(5) {
                display: none !important;
            }
            
            .practice-history-header .practice-history-header-item:nth-child(4),
            .practice-history-header .practice-history-header-item:nth-child(5) {
                display: none !important;
            }
            
            /* Hide mobile-hidden elements on mobile */
            .mobile-hidden {
                display: none !important;
            }
            
            /* Adjust grid layout for practice history items - mobile only shows 3 columns */
            .practice-history-item {
                grid-template-columns: 1fr 2fr 1fr !important;
                gap: 10px !important;
            }
            
            /* Welcome Message - Mobile Layout */
            .header-top {
                flex-direction: column !important;
                align-items: center !important;
                text-align: center !important;
                gap: 15px !important;
            }
            
            .welcome-section {
                order: 1 !important;
                width: 100% !important;
            }
            
            .header-actions {
                order: 2 !important;
                width: 100% !important;
                justify-content: center !important;
                flex-wrap: wrap !important;
                gap: 10px !important;
            }
            
            .jph-tutorial-btn,
            .jph-leaderboard-btn,
            .jph-stats-help-btn {
                flex: 1 !important;
                min-width: 120px !important;
                font-size: 13px !important;
                padding: 10px 12px !important;
            }
            
            /* Foundational Tab - Mobile Video Layout */
            .jpc-current-focus {
                padding: 15px !important;
                margin-bottom: 15px !important;
            }
            
            .jpc-main-layout {
                display: flex !important;
                flex-direction: column !important;
                gap: 15px !important;
            }
            
            .jpc-left-column,
            .jpc-right-column {
                width: 100% !important;
                max-width: none !important;
            }
            
            .jpc-focus-header {
                margin-bottom: 15px !important;
            }
            
            .jpc-focus-header h3 {
                font-size: 18px !important;
                margin-bottom: 10px !important;
            }
            
            .jpc-focus-details {
                padding: 12px !important;
                margin-bottom: 15px !important;
            }
            
            .jpc-focus-details p {
                font-size: 14px !important;
                margin-bottom: 8px !important;
            }
            
            .jpc-video-container {
                height: 180px !important;
                margin-bottom: 15px !important;
            }
            
            .jpc-video-container iframe {
                height: 180px !important;
            }
            
            .jpc-mark-complete {
                width: 100% !important;
                padding: 12px 16px !important;
                font-size: 14px !important;
                margin-top: 10px !important;
            }
            
            /* Focus Details Mobile Layout */
            .jpc-focus-details {
                display: flex !important;
                flex-direction: column !important;
                gap: 8px !important;
            }
            
            .jpc-focus-details .focus-detail-item {
                display: flex !important;
                flex-direction: column !important;
                gap: 4px !important;
                margin-bottom: 8px !important;
            }
            
            .jpc-focus-details .focus-detail-label {
                font-size: 12px !important;
                font-weight: 600 !important;
                color: #6b7280 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
            }
            
            .jpc-focus-details .focus-detail-value {
                font-size: 14px !important;
                color: #1f2937 !important;
                font-weight: 500 !important;
            }
            
            .tempo-info-icon {
                margin-left: 4px !important;
            }
            
            /* Practice History Button - Mobile */
            .practice-items-header {
                flex-direction: column !important;
                align-items: stretch !important;
                text-align: center !important;
            }
            
            .practice-items-header h3 {
                min-width: auto !important;
                margin-bottom: 10px !important;
            }
            
            .jph-practice-history-btn {
                width: 100% !important;
                justify-content: center !important;
                font-size: 16px !important;
                padding: 12px 20px !important;
            }
            
            /* Practice History - Mobile Layout */
            .practice-history-header-section {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 15px !important;
            }
            
            .practice-history-title-section {
                flex-direction: column !important;
                align-items: center !important;
                gap: 15px !important;
            }
            
            .practice-history-title-section h3 {
                font-size: 20px !important;
                text-align: center !important;
            }
            
            .jph-back-to-practice-btn {
                width: 100% !important;
                justify-content: center !important;
                font-size: 16px !important;
                padding: 12px 20px !important;
            }
            
            .practice-history-controls {
                width: 100% !important;
                justify-content: center !important;
            }
            
            /* Tab Navigation - Mobile */
            .jph-tabs-nav {
                flex-wrap: wrap !important;
                gap: 8px !important;
            }
            
            .jph-tab-btn {
                flex: 1 !important;
                min-width: calc(50% - 4px) !important;
                font-size: 13px !important;
                padding: 12px 8px !important;
            }
            
            .jph-tab-btn .tab-title {
                font-size: 12px !important;
            }
            
            .jph-tab-btn .tab-icon svg {
                width: 20px !important;
                height: 20px !important;
            }
        }
        
        /* Events Tab Styles */
        .jph-events-section {
            padding: 20px 0;
        }

        .events-header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
        }

        .events-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            text-align: center;
        }
        
        .view-calendar-btn {
            position: absolute;
            right: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #F04E23;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(240, 78, 35, 0.2);
        }

        .view-calendar-btn:hover {
            background: #e0451f;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(240, 78, 35, 0.3);
            color: white;
            text-decoration: none;
        }

        .view-calendar-btn svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .events-content {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }

        .event-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .event-item:hover {
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .event-date {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 0.9rem;
            min-width: 120px;
            text-align: center;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .event-date svg {
            width: 20px;
            height: 20px;
            color: #3b82f6;
        }

        .event-calendar-links {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 12px;
            width: 100%;
        }

        .calendar-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 10px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            transition: all 0.2s ease;
            width: 100%;
        }

        .calendar-link:hover {
            background: #f8fafc;
            border-color: #d1d5db;
            color: #374151;
            transform: translateY(-1px);
        }

        .calendar-link svg {
            width: 12px;
            height: 12px;
            flex-shrink: 0;
        }

        .gcal-link:hover {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }

        .ical-link:hover {
            background: #f0f9ff;
            border-color: #bae6fd;
            color: #0284c7;
        }

        /* Responsive Design for Events */
        @media (max-width: 768px) {
            .events-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .view-calendar-btn {
                width: 100%;
                justify-content: center;
            }
            
            .event-item {
                flex-direction: column;
                gap: 15px;
            }
            
            .event-date {
                min-width: auto;
                width: 100%;
            }
        }

        .event-info {
            flex: 1;
        }

        .event-info h4 {
            margin: 0 0 8px 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .event-info a {
            color: #1f2937;
            text-decoration: none;
        }

        .event-info a:hover {
            color: #3b82f6;
        }

        .event-description {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.5;
            margin: 0 0 12px 0;
        }

        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .event-teacher {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #6b7280;
            font-size: 0.85rem;
        }

        .event-teacher svg {
            width: 14px;
            height: 14px;
            color: #8b5cf6;
        }

        .event-types {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .event-type-tag {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .event-membership {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .membership-level-tag {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .membership-premier {
            background: #fef3c7;
            color: #92400e;
        }

        .membership-studio {
            background: #dbeafe;
            color: #1e40af;
        }

        .membership-free {
            background: #dcfce7;
            color: #166534;
        }

        .no-events-content {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .no-events-content svg {
            width: 48px;
            height: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .events-actions {
            text-align: center;
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
        
        /* Practice Items Header Layout */
        .practice-items-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .practice-items-header h3 {
            margin: 0;
            flex: 1;
            min-width: 200px;
        }
        
        .jph-practice-history-btn {
            background: #F04E23;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        
        .jph-practice-history-btn:hover {
            background: #e0451f;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(240, 78, 35, 0.3);
        }
        
        .jph-practice-history-btn svg {
            flex-shrink: 0;
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
            background: #F04E23;
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
            background: #e0451f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(240, 78, 35, 0.3);
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
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e8f5f4;
        }
        
        .practice-history-title-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .practice-history-title-section h3 {
            margin: 0;
            display: flex;
            align-items: center;
            color: #004555;
            font-size: 24px;
            font-weight: 700;
        }
        
        .jph-back-to-practice-btn {
            background: #F04E23;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        
        .jph-back-to-practice-btn:hover {
            background: #e0451f;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(240, 78, 35, 0.3);
        }
        
        .jph-back-to-practice-btn svg {
            flex-shrink: 0;
        }
        
        /* Practice History Button Uniform Heights */
        .practice-history-controls .jph-btn,
        .jph-back-to-practice-btn {
            height: 40px !important;
            min-height: 40px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 10px 16px !important;
            box-sizing: border-box !important;
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
        
        /* Welcome Modal Styling */
        #jph-welcome-modal .jph-modal-content {
            max-width: 500px;
            padding: 30px;
        }
        
        #jph-welcome-modal .jph-modal-header {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
            padding: 20px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: -30px -30px 0 -30px;
        }
        
        #jph-welcome-modal .jph-modal-header h3 {
            margin: 0;
            color: white;
            font-size: 1.4em;
            text-align: center;
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
            background: #F04E23 !important;
            color: white !important;
            padding: 12px 16px !important;
            height: auto !important;
            min-height: 48px !important;
            box-sizing: border-box !important;
            line-height: 1.2 !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            white-space: nowrap !important;
        }
        
        /* Shield purchase button specific styling */
        #purchase-shield-btn-main,
        #purchase-shield-btn {
            width: auto !important;
            min-width: 200px !important;
            max-width: none !important;
            padding: 12px 20px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
        }
        
        .jph-btn-primary:hover {
            background: #e0451f !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(240, 78, 35, 0.3);
        }
        
        .jph-btn-secondary {
            background: #459E90 !important;
            color: white !important;
            border: none !important;
        }
        
        .jph-btn-secondary:hover {
            background: #3a8a7c !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(69, 158, 144, 0.3);
        }
        
        /* Log Practice button (orange brand style) */
        .jph-log-practice-btn {
            background: #f04e23 !important;
            color: #ffffff !important;
            border: none !important;
            padding: 12px 16px !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-block !important;
            width: 100% !important;
            min-width: 180px !important;
            max-width: 180px !important;
            height: 48px !important;
            box-sizing: border-box !important;
            line-height: 1.2 !important;
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
        
        /* JPC Foundational Tab Styles */
        .jpc-current-focus {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }
        
        /* 2-Column Layout */
        .jpc-main-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: stretch;
        }
        
        .jpc-focus-column {
            display: flex;
            flex-direction: column;
        }
        
        .jpc-video-column {
            display: flex;
            flex-direction: column;
        }
        
        .jpc-focus-header {
            background: #374151;
            color: white;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
            margin: 0 0 0 0;
        }
        
        .jpc-focus-header h3 {
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            text-align: center;
        }
        
        .jpc-focus-details {
            background: white;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 20px;
            flex: 1;
        }
        
        .jpc-focus-details p {
            margin: 8px 0;
            color: #4b5563;
            font-size: 14px;
        }
        
        .jpc-focus-details a {
            color: #3b82f6;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }
        
        .jpc-focus-details a:hover {
            color: #2563eb;
            text-decoration: underline;
        }
        
        .jpc-instructions {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .jpc-instructions p {
            margin: 0;
            color: #374151;
            line-height: 1.6;
        }
        
        .jpc-video-container {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            width: 100%;
            height: auto;
        }
        
        .jpc-video-container iframe,
        .jpc-video-container video {
            width: 100%;
            height: auto;
            min-height: 300px;
        }
        
        .jpc-actions {
            margin: 20px 0;
            text-align: center;
        }
        
        .jpc-mark-complete {
            background: #239B90 !important;
            color: white !important;
            border: none !important;
            padding: 12px 24px !important;
            border-radius: 8px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            min-width: 180px !important;
        }
        
        .jpc-mark-complete:hover {
            background: #1e8a7a !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(35, 155, 144, 0.3);
        }
        
        .jpc-fix-progress {
            margin-top: 10px;
            text-align: center;
        }
        
        .jpc-fix-progress-link {
            color: #6b7280;
            font-size: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .jpc-fix-progress-link:hover {
            color: #374151;
            text-decoration: underline;
        }
        
        /* Notification styling */
        .jpc-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            font-weight: 500;
            z-index: 9999;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        
        .jpc-notification-show {
            transform: translateX(0);
        }
        
        .jpc-notification-success {
            background: #10b981;
            color: white;
            border-left: 4px solid #059669;
        }
        
        .jpc-notification-error {
            background: #ef4444;
            color: white;
            border-left: 4px solid #dc2626;
        }
        
        .jpc-notification-info {
            background: #3b82f6;
            color: white;
            border-left: 4px solid #2563eb;
        }
        
        .jpc-copy-btn {
            margin-top: 10px;
            padding: 5px 10px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            color: white;
            cursor: pointer;
            font-size: 12px;
        }
        
        .jpc-copy-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .jpc-progress-overview {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .jpc-progress-overview h4 {
            color: #1f2937;
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 15px 0;
        }
        
        .jpc-key-progress {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .key-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            transition: all 0.3s ease;
            cursor: pointer;
            margin: 0 6px;
            position: relative;
            border: 2px solid transparent;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .key-circle.completed {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-color: #10b981;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }
        
        .key-circle.current {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-color: #3b82f6;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
            animation: pulse 2s infinite;
        }
        
        .key-circle.incomplete {
            background: #f8fafc;
            color: #64748b;
            border-color: #e2e8f0;
        }
        
        .key-circle:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
            }
            50% {
                box-shadow: 0 2px 12px rgba(59, 130, 246, 0.5);
            }
        }
        
        .jpc-all-focuses-table {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            width: 100%;
        }
        
        .jpc-all-focuses-table h4 {
            color: #1f2937;
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 15px 0;
        }
        
        .jpc-table-container {
            overflow-x: auto;
        }
        
        .jpc-focuses-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .jpc-focuses-table th {
            background: #f8f9fa;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e1e5e9;
            white-space: nowrap;
        }
        
        /* Sticky header styling */
        .jpc-sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .jpc-sticky-header th {
            background: #f8f9fa !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Key column headers - make them more prominent */
        .jpc-sticky-header th:nth-child(n+3):nth-child(-n+14) {
            background: #e3f2fd !important;
            color: #1976d2;
            font-weight: bold;
            font-size: 15px;
        }
        
        .jpc-focuses-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }
        
        .jpc-focuses-table tr:hover {
            background: #f8f9fa;
        }
        
        .jpc-focuses-table tr.current-focus {
            background: #e3f2fd;
            font-weight: 500;
        }
        
        .jpc-focuses-table tr.current-focus:hover {
            background: #bbdefb;
        }
        
        .jpc-focuses-table .center {
            text-align: center;
        }
        
        /* Focus content styling */
        .jpc-focuses-table .focus-content {
            text-align: left !important;
            min-width: 300px;
        }
        
        .jpc-focuses-table .focus-title {
            font-weight: 500;
            color: #1f2937;
            line-height: 1.4;
            margin-bottom: 8px;
        }
        
        .jpc-focuses-table .focus-resources {
            margin-top: 8px;
        }
        
        .jpc-focuses-table .resource-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #3b82f6;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            margin-right: 12px;
        }
        
        .jpc-focuses-table .resource-link:hover {
            color: #1d4ed8;
        }
        
        .jpc-focuses-table .resource-link svg {
            width: 14px;
            height: 14px;
        }
        
        /* Key progress icons */
        .jpc-focuses-table .play-icon {
            transition: all 0.2s ease;
        }
        
        .jpc-focuses-table .play-link {
            cursor: pointer;
            display: inline-block;
        }
        
        .jpc-focuses-table .play-link:hover .play-icon.completed {
            color: #059669;
            transform: scale(1.1);
        }
        
        .jpc-focuses-table .play-icon.completed {
            color: #10b981;
        }
        
        .jpc-focuses-table .play-icon.incomplete {
            color: #d1d5db;
        }
        
        /* Enhanced tooltip styling */
        .jpc-focuses-table [title] {
            position: relative;
        }
        
        .jpc-focuses-table [title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            pointer-events: none;
        }
        
        .jpc-focuses-table [title]:hover::before {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #1f2937;
            z-index: 1000;
            pointer-events: none;
        }
        
        /* Action column styling */
        .jpc-focuses-table .get-graded-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #F04E23;
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }
        
        .jpc-focuses-table .get-graded-btn:hover {
            background: #e0451f;
        }
        
        .jpc-focuses-table .get-graded-btn svg {
            width: 14px;
            height: 14px;
        }
        
        /* JPC Video Modal Styles */
        .jpc-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
        }
        
        .jpc-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
        }
        
        .jpc-modal-content {
            position: relative;
            background: white;
            border-radius: 12px;
            max-width: 900px;
            max-height: 90vh;
            margin: 5vh auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .jpc-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
        }
        
        .jpc-modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }
        
        .jpc-modal-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            color: #6b7280;
            transition: all 0.2s ease;
        }
        
        .jpc-modal-close:hover {
            background: #e5e7eb;
            color: #374151;
        }
        
        .jpc-modal-close svg {
            width: 20px;
            height: 20px;
        }
        
        .jpc-modal-body {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
        }
        
        .jpc-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 24px;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
        }
        
        .jpc-btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .jpc-btn-primary {
            background: #F04E23;
            color: white;
        }
        
        .jpc-btn-primary:hover {
            background: #e0451f;
            color: white;
        }
        
        .jpc-btn-secondary {
            background: #459E90;
            color: white;
        }
        
        .jpc-btn-secondary:hover {
            background: #3a8a7c;
            color: white;
        }
        
        #jpc-video-container {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        #jpc-video-container iframe,
        #jpc-video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        
        #jpc-video-container .fvplayer,
        #jpc-video-container .fvplayer-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        /* JPC Submission Modal Styles */
        .jpc-submission-info {
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }
        
        .jpc-submission-info h4 {
            margin: 0 0 8px 0;
            color: #1f2937;
            font-size: 1.1rem;
        }
        
        .jpc-submission-info p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        .jpc-form-group {
            margin-bottom: 20px;
        }
        
        .jpc-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .jpc-form-group input[type="url"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }
        
        .jpc-form-group input[type="url"]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .jpc-form-help {
            background: #eff6ff;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #dbeafe;
            margin-bottom: 20px;
        }
        
        .jpc-form-help p {
            margin: 0;
            font-size: 13px;
            color: #1e40af;
        }
        
        .jpc-form-help a {
            color: #1d4ed8;
            text-decoration: underline;
        }
        
        .jpc-form-help a:hover {
            color: #1e3a8a;
        }
        
        .jpc-success-message {
            text-align: center;
            padding: 20px;
        }
        
        .jpc-success-message h4 {
            margin: 0 0 12px 0;
            color: #1f2937;
            font-size: 1.25rem;
        }
        
        .jpc-success-message p {
            margin: 0 0 8px 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        /* JPC Grade Display Styles */
        .jpc-grade-display {
            text-align: center;
            padding: 8px;
        }
        
        .jpc-grade-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4px;
        }
        
        .jpc-grade-badge.grade-pass {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .jpc-grade-badge.grade-redo {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .jpc-grade-date {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 6px;
        }
        
        .jpc-teacher-notes {
            text-align: left;
            background: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            margin-top: 6px;
        }
        
        .jpc-teacher-notes strong {
            font-size: 11px;
            color: #374151;
        }
        
        .jpc-notes-text {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.4;
        }
        
        /* Grade display below focus title */
        .jpc-grade-summary {
            margin-top: 8px;
            padding: 8px;
            background: #f9fafb;
            border-radius: 6px;
            border-left: 3px solid #e5e7eb;
        }
        
        .jpc-grade-summary .jpc-grade-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .jpc-grade-summary .jpc-grade-date {
            display: inline-block;
            font-size: 10px;
            color: #6b7280;
            font-weight: 500;
        }
        
        .jpc-teacher-notes-compact {
            margin-top: 6px;
            font-size: 11px;
            color: #374151;
            line-height: 1.3;
        }
        
        .jpc-teacher-notes-compact strong {
            color: #1f2937;
        }
        
        .jpc-submit-redo-compact {
            margin-top: 8px;
        }
        
        .jpc-teacher-notes-below strong {
            color: #1f2937;
        }
        
        /* Submit Redo button styling */
        .jpc-submit-redo-section {
            margin-top: 12px;
        }
        
        .jpc-submit-redo-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #002A34;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .jpc-submit-redo-btn:hover {
            background: #001f28;
        }
        
        .jpc-submit-redo-btn svg {
            width: 16px;
            height: 16px;
        }
        
        /* Waiting button styling */
        .waiting-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #fbbf24;
            color: #92400e;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            cursor: default;
            border: 1px solid #f59e0b;
        }
        
        .waiting-btn svg {
            color: #92400e;
        }
        
        /* Tempo info icon styling */
        .tempo-info-icon {
            display: inline-block;
            margin-left: 3px;
            cursor: help;
            color: #6b7280;
            transition: color 0.2s ease;
        }
        
        .tempo-info-icon:hover {
            color: #3b82f6;
        }
        
        .tempo-info-icon svg {
            vertical-align: middle;
        }
        
        .jpc-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 16px;
        }
        
        .jpc-modal-info {
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .jpc-modal-info p {
            margin: 0 0 8px 0;
            font-size: 14px;
        }
        
        .jpc-modal-info p:last-child {
            margin-bottom: 0;
        }
        
        /* Responsive modal */
        @media (max-width: 768px) {
            .jpc-modal-content {
                margin: 2vh auto;
                max-height: 96vh;
            }
            
            .jpc-modal-header,
            .jpc-modal-body,
            .jpc-modal-footer {
                padding: 16px;
            }
            
            .jpc-modal-footer {
                flex-direction: column;
            }
            
            .jpc-btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        
        .resource-link {
            color: #3b82f6;
            text-decoration: none;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-right: 8px;
        }
        
        .resource-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        
        .get-graded-btn {
            background: #F04E23;
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .get-graded-btn:hover {
            background: #e0451f;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(240, 78, 35, 0.3);
            color: white;
            text-decoration: none;
        }
        
        .jpc-progress-overview p {
            margin: 0;
            color: #4b5563;
            font-size: 14px;
            text-align: center;
        }
        
        .jpc-no-assignment,
        .jpc-login-required {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }
        
        .jpc-no-assignment h3,
        .jpc-login-required h3 {
            color: #374151;
            margin-bottom: 10px;
        }
        
        .jpc-no-assignment a,
        .jpc-login-required a {
            color: #3b82f6;
            text-decoration: none;
        }
        
        .jpc-no-assignment a:hover,
        .jpc-login-required a:hover {
            text-decoration: underline;
        }
        
        /* JPC Notification Styles */
        .jpc-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .jpc-notification-show {
            transform: translateX(0);
        }
        
        .jpc-notification-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .jpc-notification-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        /* Responsive JPC styles */
        @media (max-width: 768px) {
            .jpc-current-focus {
                padding: 20px 15px;
            }
            
            .jpc-main-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .jpc-focus-header h3 {
                font-size: 1.1rem;
            }
            
            .jpc-focus-details {
                padding: 15px;
            }
            
            .jpc-video-container iframe,
            .jpc-video-container video {
                min-height: 250px;
            }
            
            .jpc-key-progress {
                justify-content: center;
            }
            
            .key-circle {
                width: 35px;
                height: 35px;
                font-size: 11px;
            }
            
            .jpc-notification {
                right: 10px;
                left: 10px;
                max-width: none;
            }
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
            background: #459E90;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(69, 158, 144, 0.3);
        }
        
        .endpoint-test-btn:hover,
        .db-test-btn:hover,
        .ai-test-btn:hover {
            background: #3a8a7c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(69, 158, 144, 0.4);
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
            white-space: pre-wrap;
            position: relative;
        }
        
        .ai-analysis-text .ai-analysis-placeholder {
            white-space: normal;
            text-align: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: calc(100% - 40px);
        }
        
        .ai-analysis-text .ai-placeholder-icon {
            font-size: 2em;
            margin-bottom: 12px;
        }
        
        .ai-analysis-text .ai-analysis-placeholder h5 {
            margin: 0 0 8px 0;
            font-size: 1.2em;
            font-weight: 600;
        }
        
        .ai-analysis-text .ai-analysis-placeholder p {
            margin: 4px 0;
            font-size: 0.9em;
            color: #64748b;
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
        
        .ai-generate-btn {
            background: #F04E23;
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
        
        .ai-generate-btn:hover {
            background: #e0451f;
            transform: translateY(-1px);
        }
        
        .ai-generate-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
        }
        
        .generate-icon {
            font-size: 0.9em;
        }
        
        /* AI Analysis Actions */
        .ai-analysis-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .ai-print-btn {
            background: #6b7280;
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
        
        .ai-print-btn:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }
        
        .ai-print-btn svg {
            flex-shrink: 0;
        }
        
        .ai-analysis-placeholder {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }
        
        .ai-placeholder-icon {
            font-size: 3em;
            margin-bottom: 16px;
            opacity: 0.7;
        }
        
        .ai-analysis-placeholder h5 {
            margin: 0 0 12px 0;
            color: #374151;
            font-size: 1.2em;
            font-weight: 600;
        }
        
        .ai-analysis-placeholder p {
            margin: 0 0 8px 0;
            line-height: 1.5;
        }
        
        .ai-placeholder-note {
            font-size: 0.9em;
            opacity: 0.8;
            font-style: italic;
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
            
            .analytics-header {
                margin-bottom: 30px !important;
                padding: 0 15px !important;
            }
            
            .analytics-header h3 {
                font-size: 1.8em !important;
                margin-bottom: 8px !important;
                gap: 8px !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
            }
            
            .analytics-icon {
                width: 28px !important;
                height: 28px !important;
            }
            
            .analytics-header p {
                font-size: 1.1em !important;
                margin-bottom: 0 !important;
                line-height: 1.4 !important;
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
            
            .ai-generate-btn {
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
        
        <!-- Repertoire Styles -->
        <style>
        .jph-repertoire-section {
            margin-top: 40px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .repertoire-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .repertoire-header-actions {
            display: flex;
            gap: 10px;
        }
        
        .repertoire-header h3 {
            margin: 0;
            font-size: 1.5em;
            color: #004555;
        }
        
        .repertoire-controls {
            margin-bottom: 20px;
        }
        
        .sort-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sort-controls label {
            font-weight: 600;
            color: #004555;
        }
        
        .sort-controls select {
            padding: 8px 12px;
            border: 2px solid #e8f5f4;
            border-radius: 8px;
            font-size: 14px;
            color: #004555;
            background: white;
            cursor: pointer;
        }
        
        .repertoire-table-container {
            overflow-x: auto;
        }
        
        .repertoire-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .repertoire-table thead {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
        }
        
        .repertoire-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .repertoire-table tbody tr {
            border-bottom: 1px solid #e8f5f4;
            transition: background-color 0.2s ease;
        }
        
        .repertoire-table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .repertoire-table tbody tr.dragging {
            opacity: 0.5;
        }
        
        .repertoire-table tbody tr.drag-over {
            background-color: #e0f2fe;
        }
        
        .repertoire-table td {
            padding: 12px;
            vertical-align: middle;
        }
        
        .drag-handle-cell {
            text-align: center;
            width: 40px;
            cursor: move;
        }
        
        .repertoire-title {
            font-weight: 600;
            color: #004555;
        }
        
        .repertoire-composer {
            color: #666;
        }
        
        .repertoire-date {
            color: #666;
            font-size: 0.9em;
        }
        
        .repertoire-notes {
            color: #666;
            font-size: 0.9em;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .repertoire-actions {
            display: flex;
            gap: 8px;
        }
        
        .repertoire-actions button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s ease;
            color: #666;
        }
        
        .repertoire-actions button:hover {
            background-color: #f3f4f6;
            color: #004555;
        }
        
        .btn-mark-practiced:hover {
            color: #10b981 !important;
        }
        
        .btn-edit-repertoire:hover {
            color: #3b82f6 !important;
        }
        
        .btn-delete-repertoire:hover {
            color: #ef4444 !important;
        }
        
        /* Stat update animation */
        .stat-updated {
            animation: statPulse 0.6s ease-in-out;
        }
        
        @keyframes statPulse {
            0% {
                transform: scale(1);
                background-color: transparent;
            }
            50% {
                transform: scale(1.05);
                background-color: rgba(16, 185, 129, 0.1);
            }
            100% {
                transform: scale(1);
                background-color: transparent;
            }
        }
        
        @media (max-width: 768px) {
            .repertoire-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .repertoire-header-actions {
                width: 100%;
                flex-direction: column;
            }
            
            .repertoire-header-actions button {
                width: 100%;
            }
            
            .repertoire-table {
                font-size: 12px;
            }
            
            .repertoire-table th,
            .repertoire-table td {
                padding: 8px 4px;
            }
            
            .repertoire-notes {
                display: none;
            }
            
            .repertoire-actions {
                flex-direction: column;
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
            
            // Handle last lesson card click - make entire card clickable
            $('.last-lesson-card').on('click', function(e) {
                const link = $(this).find('.last-lesson-link');
                if (link.length && !$(e.target).is('a')) {
                    const url = link.attr('href');
                    if (url) {
                        window.open(url, link.attr('target') || '_self');
                    }
                }
            });
            
            // Handle favorites dropdown - open in current window
            $('#jph-favorites-dropdown').on('change', function() {
                const url = $(this).val();
                if (url) {
                    const selectedOption = $(this).find('option:selected');
                    const isViewAll = selectedOption.data('view-all');
                    
                    // Navigate to selected favorite in same window
                    window.location.href = url;
                    
                    // Reset dropdown
                    $(this).val('');
                }
            });
            
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
                        // Chart failed to load
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
            
            // Practice History Button functionality
            $('#jph-practice-history-btn').on('click', function(e) {
                e.preventDefault();
                
                // Switch to history tab content without showing history tab as active
                $('.jph-tab-pane').removeClass('active');
                $('#history-tab').addClass('active');
                
                // Keep Practice tab visually active
                $('.jph-tab-btn').removeClass('active');
                $('.jph-tab-btn[data-tab="practice-items"]').addClass('active');
                
                // Scroll to top of content
                $('html, body').animate({
                    scrollTop: $('.jph-tabs-content').offset().top - 100
                }, 500);
            });
            
            // Back to Practice Items Button functionality
            $('#jph-back-to-practice-btn').on('click', function(e) {
                e.preventDefault();
                
                // Switch back to practice items tab
                $('.jph-tab-pane').removeClass('active');
                $('#practice-items-tab').addClass('active');
                
                // Keep Practice tab visually active
                $('.jph-tab-btn').removeClass('active');
                $('.jph-tab-btn[data-tab="practice-items"]').addClass('active');
                
                // Scroll to top of content
                $('html, body').animate({
                    scrollTop: $('.jph-tabs-content').offset().top - 100
                }, 500);
            });
            
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
                            if (typeof initAIGenerateButton === 'function') {
                                initAIGenerateButton();
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
            
            // Initialize AI generate button
            initAIGenerateButton();
            
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
                        const sentimentIconMap = {
                            1: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" /></svg>',
                            2: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" /></svg>',
                            3: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" /></svg>',
                            4: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" /></svg>',
                            5: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" /></svg>'
                        };
                        const sentimentLabelMap = {1:'Struggled',2:'Difficult',3:'Okay',4:'Good',5:'Excellent'};
                        const sentiment = score ? (sentimentIconMap[score] + ' ' + sentimentLabelMap[score]) : 'Not specified';
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
                        
                    // Check if mobile (screen width <= 768px)
                    const isMobile = window.innerWidth <= 768;
                    
                    html += `
                            <div class="practice-history-item">
                                <div class="practice-history-item-content">${date}</div>
                                <div class="practice-history-item-content">${session.item_name}</div>
                                <div class="practice-history-item-content center">${session.duration_minutes} min</div>
                                ${!isMobile ? `<div class="practice-history-item-content center">${sentiment}</div>` : ''}
                                ${!isMobile ? `<div class="practice-history-item-content center">${improvement}</div>` : ''}
                                ${!isMobile ? `<div class="practice-history-item-content center">
                                    <button type="button" class="jph-delete-session-btn" data-session-id="${session.id}" data-item-name="${session.item_name || 'Unknown Item'}" title="Delete this practice session"><i class="fa-solid fa-circle-xmark"></i></button>
                                </div>` : ''}
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
                                const sentimentIconMap = {
                            1: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" /></svg>',
                            2: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" /></svg>',
                            3: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" /></svg>',
                            4: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" /></svg>',
                            5: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" /></svg>'
                        };
                                const sentimentLabelMap = {1:'Struggled',2:'Difficult',3:'Okay',4:'Good',5:'Excellent'};
                                const sentiment = score ? (sentimentIconMap[score] + ' ' + sentimentLabelMap[score]) : 'Not specified';
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
                                
                            // Check if mobile (screen width <= 768px)
                            const isMobile = window.innerWidth <= 768;
                                
                            html += `
                            <div class="practice-history-item">
                                <div class="practice-history-item-content">${date}</div>
                                <div class="practice-history-item-content">${session.item_name}</div>
                                <div class="practice-history-item-content center">${session.duration_minutes} min</div>
                                ${!isMobile ? `<div class="practice-history-item-content center">${sentiment}</div>` : ''}
                                ${!isMobile ? `<div class="practice-history-item-content center">${improvement}</div>` : ''}
                                ${!isMobile ? `<div class="practice-history-item-content center">
                                    <button type="button" class="jph-delete-session-btn" data-session-id="${session.id}" data-item-name="${session.item_name || 'Unknown Item'}" title="Delete this practice session"><i class="fa-solid fa-circle-xmark"></i></button>
                                </div>` : ''}
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
                        $('#load-more-sessions-bottom').prop('disabled', false).html('<span class="btn-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg></span>Load More Sessions');
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
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/badges'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            displayBadges(response.badges);
                        } else {
                            $('#jph-badges-grid').html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges available</div>');
                        }
                    },
                    error: function(xhr, status, error) {
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
                    html += '    <div class="jph-badge-content">';
                    html += '        <div class="jph-badge-title">' + escapeHtml(badge.name) + '</div>';
                    html += '        <div class="jph-badge-description">' + escapeHtml(badge.description || '') + '</div>';
                    html += '        <div class="jph-badge-rewards">+' + (badge.xp_reward || 0) + ' XP +' + (badge.gem_reward || 0) + ' 💎</div>';
                    html += '    </div>';
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
            
            // Generate AI analysis on demand
            function generateAIAnalysis() {
                console.log('Generating AI analysis...');
                
                // Show loading state
                $('#ai-analysis-text').html(`
                    <div class="loading-spinner">
                        <div class="spinner"></div>
                        <span>Generating AI analysis...</span>
                    </div>
                `);
                
                // Disable the button during generation
                $('#ai-generate-btn').prop('disabled', true).html('<span class="generate-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span> Generating...');
                
                // Make the API call
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/ai-analysis'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        console.log('AI Analysis response:', response);
                        if (response.success && response.data) {
                            displayAIAnalysis(response.data, false);
                            // Update button to allow regeneration
                            $('#ai-generate-btn').prop('disabled', false).html('<span class="generate-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 16.338l10.125-3.75m0 0l3.75 3.75m-3.75-3.75l-3.75 3.75" /></svg></span> Regenerate Analysis');
                        } else {
                            console.error('AI Analysis API error:', response);
                            displayAIAnalysisError('API Error: ' + (response.message || 'Unknown error'));
                            // Reset button
                            $('#ai-generate-btn').prop('disabled', false).html('<span class="generate-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" /></svg></span> Generate AI Analysis');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AI Analysis loading error:', error);
                        console.error('XHR response:', xhr.responseText);
                        displayAIAnalysisError('Connection Error: ' + error + ' (Status: ' + xhr.status + ')');
                        // Reset button
                        $('#ai-generate-btn').prop('disabled', false).html('<span class="generate-icon">✨</span> Generate AI Analysis');
                    }
                });
            }
            
            
            
            // Display AI analysis
            function displayAIAnalysis(data, isCached) {
                const $analysisText = $('#ai-analysis-text');
                const $dataPeriod = $('#ai-data-period .period-text');
                
                // Convert line breaks to HTML paragraphs
                let formattedText = data.analysis
                    .replace(/\n\n/g, '</p><p>')  // Double line breaks become paragraph breaks
                    .replace(/\n/g, '<br>');     // Single line breaks become <br>
                
                // Wrap in paragraph tags
                formattedText = '<p>' + formattedText + '</p>';
                
                // Display formatted AI text
                $analysisText.html(formattedText);
                
                // Update data period
                $dataPeriod.text(data.data_period);
                
                // Show cache indicator if cached
                if (isCached) {
                    $dataPeriod.text(data.data_period + ' (cached)');
                }
                
                // Show print button when analysis is displayed
                $('#ai-print-btn').show();
                
                // Initialize generate button
                initAIGenerateButton();
            }
            
            
            // Display AI analysis error
            function displayAIAnalysisError(message) {
                const $analysisText = $('#ai-analysis-text');
                $analysisText.html('<p style="color: #dc2626; text-align: center;">' + message + '</p>');
                
                // Initialize generate button
                initAIGenerateButton();
            }
            
            // Initialize AI generate button
            function initAIGenerateButton() {
                $('#ai-generate-btn').off('click').on('click', function() {
                    generateAIAnalysis();
                });
                
                // Initialize print button
                $('#ai-print-btn').off('click').on('click', function() {
                    printAIAnalysis();
                });
            }
            
            // Print AI Analysis function
            function printAIAnalysis() {
                var analysisContent = $('#ai-analysis-text').html();
                if (!analysisContent || analysisContent.includes('Ready for AI Analysis')) {
                    alert('No analysis to print. Please generate an analysis first.');
                    return;
                }
                
                // Create a new window for printing
                var printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>AI Practice Analysis - Jazzedge Academy</title>
                        <style>
                            body { 
                                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                                line-height: 1.6;
                                color: #333;
                                max-width: 800px;
                                margin: 0 auto;
                                padding: 20px;
                            }
                            h1 { color: #F04E23; border-bottom: 2px solid #F04E23; padding-bottom: 10px; }
                            h2 { color: #2c3e50; margin-top: 30px; }
                            h3 { color: #34495e; }
                            p { margin-bottom: 15px; }
                            .analysis-date { color: #666; font-style: italic; margin-bottom: 20px; }
                            .print-website {
                                text-align: center;
                                margin-bottom: 20px;
                                color: #666;
                                font-size: 12px;
                            }
                            .print-website a {
                                color: #F04E23;
                                text-decoration: none;
                            }
                            .print-website a:hover {
                                text-decoration: underline;
                            }
                            .insight-item { margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 5px; }
                            .insight-label { font-weight: bold; color: #2c3e50; }
                            .insight-value { color: #F04E23; font-weight: bold; }
                            @media print {
                                body { margin: 0; padding: 15px; }
                                .no-print { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        <h1>AI Practice Analysis</h1>
                        <div class="analysis-date">Generated on ${new Date().toLocaleDateString()}</div>
                        <div class="print-website">
                            <a href="https://jazzedge.academy/">https://jazzedge.academy/</a>
                        </div>
                        ${analysisContent}
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
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
                
                // Purchase shield button (handle both main and modal buttons)
                jQuery(document).on('click', '#purchase-shield-btn, #purchase-shield-btn-main', function() {
                    const $btn = jQuery(this);
                    const cost = $btn.data('cost');
                    const nonce = $btn.data('nonce');
                    
                    // Prevent multiple rapid clicks
                    if ($btn.hasClass('processing')) {
                        console.log('Purchase already in progress, ignoring click');
                        return;
                    }
                    
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
                    
                    // Mark button as processing
                    $btn.addClass('processing').prop('disabled', true).text('Processing...');
                    
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
                            // Reset button state
                            $btn.removeClass('processing').prop('disabled', false);
                            
                            if (response.success) {
                                // Update shield count display (both main and modal)
                                jQuery('#shield-count, #shield-count-modal').text(response.data.new_shield_count);
                                
                                // Update gem balance in all displays
                                console.log('Updating gem balance to:', response.data.new_gem_balance);
                                
                                // Update main dashboard gems stat (target only the gems stat specifically)
                                const gemsStatElement = jQuery('.stat-gems');
                                if (gemsStatElement.length > 0) {
                                    console.log('Found gems stat element with class');
                                    // Update only the text content, not the SVG
                                    gemsStatElement.contents().filter(function() {
                                        return this.nodeType === 3; // Text node
                                    }).each(function() {
                                        if (this.textContent.trim() !== '') {
                                            console.log('Updating gem balance from', this.textContent.trim(), 'to', response.data.new_gem_balance);
                                            this.textContent = response.data.new_gem_balance;
                                        }
                                    });
                                } else {
                                    console.log('Gems stat element not found');
                                }
                                
                                // Update gems widget
                                const gemsWidget = jQuery('.jph-gems-amount');
                                console.log('Found gems widget element:', gemsWidget.length);
                                gemsWidget.text(response.data.new_gem_balance);
                                
                                // Update button state if at max shields
                                if (response.data.new_shield_count >= 3) {
                                    $btn.prop('disabled', true).text('Max Shields (3)');
                                } else {
                                    $btn.text('Purchase Shield (50 💎)');
                                }
                                
                                alert('Shield purchased successfully! Your gem balance has been updated.');
                            } else {
                                $btn.text('Purchase Shield (50 💎)');
                                alert('Error purchasing shield: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function(xhr, status, error) {
                            // Reset button state
                            $btn.removeClass('processing').prop('disabled', false).text('Purchase Shield (50 💎)');
                            
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
            
            
            // Leaderboard tab management
            window.openLeaderboard = function() {
                // Calculate center position
                const width = 1200;
                const height = 800;
                const left = (screen.width - width) / 2;
                const top = (screen.height - height) / 2;
                
                const leaderboardWindow = window.open('/leaderboard', 'jph-leaderboard', 
                    `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`);
                if (leaderboardWindow) {
                    leaderboardWindow.focus();
                }
            };
            
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
                
                // Leaderboard button - handled by onclick in HTML
                
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
                
                $('#jph-welcome-title').html('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24" style="display: inline-block; margin-right: 8px; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75s.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" /></svg>Welcome, ' + nameToShow + '!');
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
            
            // JPC Mark Complete button handler
            $('.jpc-mark-complete').on('click', function() {
                const button = $(this);
                const stepId = button.data('step-id');
                const curriculumId = button.data('curriculum-id');
                
                // Disable button to prevent double-clicks
                button.prop('disabled', true);
                button.html('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Processing...');
                
                // Make AJAX request
                $.ajax({
                    url: '/wp-json/aph/v1/jpc/complete',
                    method: 'POST',
                    beforeSend: function(xhr, settings) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                        console.log('Sending JPC completion request:', settings.data);
                    },
                    data: {
                        step_id: stepId,
                        curriculum_id: curriculumId,
                        user_id: <?php echo get_current_user_id(); ?>
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            const message = response.data.all_keys_complete ? 
                                '🎉 Congratulations! You completed all 12 keys for this focus!' : 
                                '✅ Step completed successfully!';
                            
                            // Show notification
                            showNotification(message, 'success');
                            
                            // Update XP and gems display
                            if (response.data.xp_earned > 0) {
                                updateStatsDisplay('xp', response.data.xp_earned);
                            }
                            if (response.data.gems_earned > 0) {
                                updateStatsDisplay('gems', response.data.gems_earned);
                            }
                            
                            // Reload the page to show updated progress
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                            
                        } else {
                            // Show error message
                            showNotification(response.message || 'An error occurred', 'error');
                            
                            // Re-enable button
                            button.prop('disabled', false);
                            button.html('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg> Mark Complete');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('JPC completion error:', error);
                        console.error('XHR response:', xhr.responseText);
                        console.error('Status:', xhr.status);
                        
                        let errorMessage = 'An error occurred while processing your request';
                        if (xhr.responseText) {
                            try {
                                const errorData = JSON.parse(xhr.responseText);
                                if (errorData.message) {
                                    errorMessage = errorData.message;
                                }
                            } catch (e) {
                                // If not JSON, use the raw response
                                errorMessage = xhr.responseText;
                            }
                        }
                        
                        showNotification(errorMessage, 'error');
                        
                        // Re-enable button
                        button.prop('disabled', false);
                        button.html('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg> Mark Complete');
                    }
                });
            });
            
            // JPC Fix Progress link handler
            $('.jpc-fix-progress-link').on('click', function(e) {
                e.preventDefault();
                
                const link = $(this);
                const userId = link.data('user-id');
                const currentFocus = link.data('current-focus');
                const currentKey = link.data('current-key');
                
                // Show confirmation dialog
                if (!confirm('This will analyze your progress and move you to the lowest incomplete step. Any steps completed after that will be reset and need to be completed again. Continue?')) {
                    return;
                }
                
                // Show loading state
                link.text('Analyzing...').css('pointer-events', 'none');
                
                // Make AJAX request to analyze and fix progress
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/jpc/fix-my-progress'); ?>',
                    method: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                    },
                    data: {
                        user_id: userId,
                        current_focus: currentFocus,
                        current_key: currentKey
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.fixed) {
                                let message = '✅ Progress fixed! ';
                                if (response.fix_reason === 'wrong key') {
                                    message += 'Moved to the lowest incomplete step: ' + response.new_key + ' in focus ' + response.new_focus + '. Any steps completed after this have been reset.';
                                } else if (response.fix_reason === 'wrong focus') {
                                    message += 'Moved from focus ' + response.old_focus + ' to focus ' + response.new_focus + ', ' + response.new_key + '.';
                                } else {
                                    message += 'You are now at ' + response.new_focus + ', ' + response.new_key + '.';
                                }
                                showNotification(message, 'success');
                                // Reload page to show updated assignment
                                setTimeout(function() {
                                    window.location.reload();
                                }, 3000);
                            } else {
                                showNotification('ℹ️ Your progress is already correct. No changes needed.', 'info');
                            }
                        } else {
                            showNotification('❌ ' + (response.message || 'Unable to fix progress'), 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred while fixing your progress';
                        if (xhr.responseText) {
                            try {
                                const errorData = JSON.parse(xhr.responseText);
                                if (errorData.message) {
                                    errorMessage = errorData.message;
                                }
                            } catch (e) {
                                errorMessage = xhr.responseText;
                            }
                        }
                        showNotification('❌ ' + errorMessage, 'error');
                    },
                    complete: function() {
                        // Reset link state
                        link.text('Fix my progress').css('pointer-events', 'auto');
                    }
                });
            });
            
            // Helper function to show notifications
            function showNotification(message, type) {
                const notification = $('<div class="jpc-notification jpc-notification-' + type + '">' + message + '</div>');
                $('body').append(notification);
                
                // Animate in
                setTimeout(function() {
                    notification.addClass('jpc-notification-show');
                }, 100);
                
                // Remove after 5 seconds
                setTimeout(function() {
                    notification.removeClass('jpc-notification-show');
                    setTimeout(function() {
                        notification.remove();
                    }, 300);
                }, 5000);
            }
            
            // Helper function to update stats display
            function updateStatsDisplay(type, amount) {
                const statElement = $('.jph-stat-' + type + ' .jph-stat-value');
                if (statElement.length) {
                    const currentValue = parseInt(statElement.text().replace(/,/g, '')) || 0;
                    const newValue = currentValue + amount;
                    statElement.text(newValue.toLocaleString());
                }
            }
            
        });
        
        // Repertoire Management JavaScript
        (function($) {
            'use strict';
            
            let repertoireItems = [];
            let draggedItem = null;
            
            // Local showMessage function for repertoire
            function showMessage(message, type = 'success') {
                const messageElement = $('<div class="jph-message jph-message-' + type + '">' + message + '</div>');
                $('body').append(messageElement);
                messageElement.fadeIn(300);
                setTimeout(function() {
                    messageElement.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }
            
            // Load repertoire items on page load
            function loadRepertoireItems() {
                const orderBy = $('#repertoire-sort').val() || 'last_practiced';
                
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/repertoire'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    data: {
                        order_by: orderBy,
                        order: 'DESC'
                    },
                    success: function(response) {
                        if (response.success) {
                            repertoireItems = response.items;
                            renderRepertoireTable();
                        }
                    },
                    error: function() {
                        $('#repertoire-tbody').html('<tr><td colspan="6" style="text-align: center; padding: 20px;">Failed to load repertoire items.</td></tr>');
                    }
                });
            }
            
            // Render repertoire table
            function renderRepertoireTable() {
                const tbody = $('#repertoire-tbody');
                
                if (repertoireItems.length === 0) {
                    tbody.html('<tr><td colspan="6" style="text-align: center; padding: 20px;">No repertoire items yet. Click "Add Repertoire" to get started!</td></tr>');
                    return;
                }
                
                let html = '';
                repertoireItems.forEach(function(item) {
                    const lastPracticed = item.last_practiced ? formatDate(item.last_practiced) : 'Never';
                    const notesPreview = item.notes ? (item.notes.length > 50 ? item.notes.substring(0, 50) + '...' : item.notes) : '';
                    
                    html += `
                        <tr class="repertoire-row" data-item-id="${item.ID}" draggable="true">
                            <td class="drag-handle-cell">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="cursor: move; opacity: 0.5;">
                                    <circle cx="4" cy="4" r="1"/>
                                    <circle cx="8" cy="4" r="1"/>
                                    <circle cx="12" cy="4" r="1"/>
                                    <circle cx="4" cy="8" r="1"/>
                                    <circle cx="8" cy="8" r="1"/>
                                    <circle cx="12" cy="8" r="1"/>
                                    <circle cx="4" cy="12" r="1"/>
                                    <circle cx="8" cy="12" r="1"/>
                                    <circle cx="12" cy="12" r="1"/>
                                </svg>
                            </td>
                            <td class="repertoire-title">${escapeHtml(item.title)}</td>
                            <td class="repertoire-composer">${escapeHtml(item.composer)}</td>
                            <td class="repertoire-date">${lastPracticed}</td>
                            <td class="repertoire-notes">${escapeHtml(notesPreview)}</td>
                            <td class="repertoire-actions">
                                <button class="btn-mark-practiced" data-item-id="${item.ID}" title="Mark as practiced (25 XP)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </button>
                                <button class="btn-edit-repertoire" data-item-id="${item.ID}" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>
                                <button class="btn-delete-repertoire" data-item-id="${item.ID}" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                tbody.html(html);
                
                // Attach event handlers
                attachRepertoireHandlers();
            }
            
            // Attach event handlers
            function attachRepertoireHandlers() {
                // Mark as practiced
                $('.btn-mark-practiced').off('click').on('click', function() {
                    const itemId = $(this).data('item-id');
                    markRepertoirePracticed(itemId);
                });
                
                // Edit repertoire
                $('.btn-edit-repertoire').off('click').on('click', function() {
                    const itemId = $(this).data('item-id');
                    openEditModal(itemId);
                });
                
                // Delete repertoire
                $('.btn-delete-repertoire').off('click').on('click', function() {
                    const itemId = $(this).data('item-id');
                    deleteRepertoire(itemId);
                });
                
                // Drag and drop handlers
                $('.repertoire-row').off('dragstart dragend dragover drop').on('dragstart', function(e) {
                    draggedItem = $(this);
                    $(this).addClass('dragging');
                    e.originalEvent.dataTransfer.effectAllowed = 'move';
                }).on('dragend', function() {
                    $(this).removeClass('dragging');
                    draggedItem = null;
                }).on('dragover', function(e) {
                    e.preventDefault();
                    e.originalEvent.dataTransfer.dropEffect = 'move';
                    if (!$(this).hasClass('dragging')) {
                        $(this).addClass('drag-over');
                    }
                }).on('dragleave', function() {
                    $(this).removeClass('drag-over');
                }).on('drop', function(e) {
                    e.preventDefault();
                    $(this).removeClass('drag-over');
                    
                    if (draggedItem && draggedItem[0] !== this) {
                        const $target = $(this);
                        if (draggedItem.index() < $target.index()) {
                            $target.after(draggedItem);
                        } else {
                            $target.before(draggedItem);
                        }
                        updateRepertoireOrder();
                    }
                });
            }
            
            // Mark repertoire as practiced
            function markRepertoirePracticed(itemId) {
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/repertoire'); ?>/' + itemId + '/practice',
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            let message = 'Repertoire marked as practiced! You earned 25 XP! 🎉';
                            
                            // Update stats from server response
                            if (response.user_stats) {
                                updateXPStatFromServer(response.user_stats.total_xp);
                                
                                if (response.user_stats.current_level) {
                                    updateLevelStat(response.user_stats.current_level);
                                }
                                
                                if (response.user_stats.current_streak !== undefined) {
                                    updateStreakStat(response.user_stats.current_streak);
                                }
                            }
                            
                            // Check for level up
                            if (response.level_up && response.level_up.leveled_up) {
                                message += ' 🎉 LEVEL UP! You reached level ' + response.level_up.new_level + '!';
                            }
                            
                            // Update streak if needed
                            if (response.streak_update && response.streak_update.streak_updated) {
                                if (response.streak_update.streak_continued) {
                                    message += ' 🔥 STREAK ' + response.streak_update.current_streak + '-day streak!';
                                }
                            }
                            
                            showMessage(message, 'success');
                            loadRepertoireItems();
                        } else {
                            showMessage('Failed to mark as practiced: ' + (response.message || 'Unknown error'), 'error');
                        }
                    },
                    error: function() {
                        showMessage('Error marking repertoire as practiced. Please try again.', 'error');
                    }
                });
            }
            
            // Update XP stat in real-time from server value
            function updateXPStatFromServer(newTotalXP) {
                // Update widget stats
                const xpElement = $('.jph-stat-xp .jph-stat-value');
                if (xpElement.length) {
                    xpElement.text(newTotalXP.toLocaleString());
                    
                    // Add flash animation
                    xpElement.parent().parent().addClass('stat-updated');
                    setTimeout(function() {
                        xpElement.parent().parent().removeClass('stat-updated');
                    }, 1000);
                }
                
                // Update main dashboard stats
                const mainXPElement = $('.stat-xp.stat-value');
                if (mainXPElement.length) {
                    mainXPElement.text(newTotalXP);
                    
                    // Add flash animation
                    mainXPElement.parent().addClass('stat-updated');
                    setTimeout(function() {
                        mainXPElement.parent().removeClass('stat-updated');
                    }, 1000);
                }
            }
            
            // Update level stat in real-time
            function updateLevelStat(newLevel) {
                // Update widget stats
                const levelElement = $('.jph-stat-level .jph-stat-value');
                if (levelElement.length) {
                    levelElement.text(newLevel);
                    
                    // Add flash animation
                    levelElement.parent().parent().addClass('stat-updated');
                    setTimeout(function() {
                        levelElement.parent().parent().removeClass('stat-updated');
                    }, 1000);
                }
                
                // Update main dashboard stats
                const mainLevelElement = $('.stat-level.stat-value');
                if (mainLevelElement.length) {
                    mainLevelElement.text(newLevel);
                    
                    // Add flash animation
                    mainLevelElement.parent().addClass('stat-updated');
                    setTimeout(function() {
                        mainLevelElement.parent().removeClass('stat-updated');
                    }, 1000);
                }
            }
            
            // Update streak stat in real-time
            function updateStreakStat(newStreak) {
                // Update widget stats
                const streakElement = $('.jph-stat-streak .jph-stat-value');
                if (streakElement.length) {
                    streakElement.text(newStreak);
                    
                    // Add flash animation
                    streakElement.parent().parent().addClass('stat-updated');
                    setTimeout(function() {
                        streakElement.parent().parent().removeClass('stat-updated');
                    }, 1000);
                }
                
                // Update main dashboard stats
                const mainStreakElement = $('.stat-streak.stat-value');
                if (mainStreakElement.length) {
                    mainStreakElement.text(newStreak);
                    
                    // Add flash animation
                    mainStreakElement.parent().addClass('stat-updated');
                    setTimeout(function() {
                        mainStreakElement.parent().removeClass('stat-updated');
                    }, 1000);
                }
            }
            
            // Open edit modal
            function openEditModal(itemId) {
                const item = repertoireItems.find(i => i.ID == itemId);
                if (!item) return;
                
                $('#edit-repertoire-id').val(item.ID);
                $('#edit-repertoire-title').val(item.title);
                $('#edit-repertoire-composer').val(item.composer);
                $('#edit-repertoire-notes').val(item.notes);
                $('#jph-edit-repertoire-modal').show();
            }
            
            // Delete repertoire
            function deleteRepertoire(itemId) {
                if (!confirm('Are you sure you want to delete this repertoire item?')) {
                    return;
                }
                
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/repertoire'); ?>/' + itemId,
                    method: 'DELETE',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage('Repertoire item deleted successfully.', 'success');
                            loadRepertoireItems();
                        } else {
                            showMessage('Failed to delete repertoire: ' + (response.message || 'Unknown error'), 'error');
                        }
                    },
                    error: function() {
                        showMessage('Error deleting repertoire item. Please try again.', 'error');
                    }
                });
            }
            
            // Update repertoire order
            function updateRepertoireOrder() {
                const itemOrders = {};
                $('.repertoire-row').each(function(index) {
                    const itemId = $(this).data('item-id');
                    itemOrders[itemId] = index;
                });
                
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/repertoire/order'); ?>',
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    data: {
                        item_orders: itemOrders
                    },
                    success: function(response) {
                        if (!response.success) {
                            console.error('Failed to update order:', response);
                        }
                    },
                    error: function() {
                        console.error('Error updating repertoire order');
                    }
                });
            }
            
            // Format date helper
            function formatDate(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diffMs = now - date;
                const diffMins = Math.floor(diffMs / 60000);
                const diffHours = Math.floor(diffMs / 3600000);
                const diffDays = Math.floor(diffMs / 86400000);
                
                if (diffMins < 1) return 'Just now';
                if (diffMins < 60) return diffMins + ' min ago';
                if (diffHours < 24) return diffHours + ' hr ago';
                if (diffDays < 7) return diffDays + ' day' + (diffDays > 1 ? 's' : '') + ' ago';
                
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined });
            }
            
            // Escape HTML helper
            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, m => map[m]);
            }
            
            // Initialize repertoire handlers
            function initRepertoireHandlers() {
                // Add repertoire button
                $('#jph-add-repertoire-btn').on('click', function() {
                    $('#add-repertoire-form')[0].reset();
                    $('#jph-add-repertoire-modal').show();
                });
                
                // Print repertoire button
                $('#jph-print-repertoire-btn').on('click', function() {
                    printRepertoire();
                });
                
                // Sort dropdown
                $('#repertoire-sort').on('change', function() {
                    loadRepertoireItems();
                });
                
                // Add repertoire form submission
                $('#add-repertoire-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    const title = $(this).find('[name="title"]').val().trim();
                    const composer = $(this).find('[name="composer"]').val().trim();
                    const notes = $(this).find('[name="notes"]').val().trim();
                    
                    if (!title || !composer) {
                        showMessage('Title and composer are required.', 'error');
                        return;
                    }
                    
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/repertoire'); ?>',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: {
                            title: title,
                            composer: composer,
                            notes: notes
                        },
                        success: function(response) {
                            if (response.success) {
                                showMessage('Repertoire item added successfully!', 'success');
                                $('#jph-add-repertoire-modal').hide();
                                loadRepertoireItems();
                            } else {
                                showMessage('Failed to add repertoire: ' + (response.message || 'Unknown error'), 'error');
                            }
                        },
                        error: function() {
                            showMessage('Error adding repertoire item. Please try again.', 'error');
                        }
                    });
                });
                
                // Edit repertoire form submission
                $('#edit-repertoire-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    const itemId = $(this).find('[name="item_id"]').val();
                    const title = $(this).find('[name="title"]').val().trim();
                    const composer = $(this).find('[name="composer"]').val().trim();
                    const notes = $(this).find('[name="notes"]').val().trim();
                    
                    if (!title || !composer) {
                        showMessage('Title and composer are required.', 'error');
                        return;
                    }
                    
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/repertoire'); ?>/' + itemId,
                        method: 'PUT',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: {
                            title: title,
                            composer: composer,
                            notes: notes
                        },
                        success: function(response) {
                            if (response.success) {
                                showMessage('Repertoire item updated successfully!', 'success');
                                $('#jph-edit-repertoire-modal').hide();
                                loadRepertoireItems();
                            } else {
                                showMessage('Failed to update repertoire: ' + (response.message || 'Unknown error'), 'error');
                            }
                        },
                        error: function() {
                            showMessage('Error updating repertoire item. Please try again.', 'error');
                        }
                    });
                });
                
                // Close modals
                $('#jph-add-repertoire-modal .jph-modal-close, #jph-edit-repertoire-modal .jph-modal-close').on('click', function() {
                    $(this).closest('.jph-modal').hide();
                });
                
                // Close modals when clicking outside
                $('#jph-add-repertoire-modal, #jph-edit-repertoire-modal').on('click', function(e) {
                    if ($(e.target).hasClass('jph-modal')) {
                        $(this).hide();
                    }
                });
            }
            
            // Load repertoire items on page load
            if ($('#repertoire-table').length) {
                initRepertoireHandlers();
                loadRepertoireItems();
            }
            
            // Print repertoire function
            function printRepertoire() {
                if (repertoireItems.length === 0) {
                    showMessage('No repertoire items to print.', 'error');
                    return;
                }
                
                // Create print window
                const printWindow = window.open('', '_blank');
                const userName = '<?php echo esc_js(wp_get_current_user()->display_name); ?>';
                const printDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                
                let html = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>My Repertoire - ${userName}</title>
                        <style>
                            body {
                                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                                padding: 40px;
                                color: #333;
                            }
                            .print-header {
                                text-align: center;
                                margin-bottom: 30px;
                                border-bottom: 3px solid #004555;
                                padding-bottom: 20px;
                            }
                            .print-header h1 {
                                margin: 0;
                                color: #004555;
                                font-size: 28px;
                            }
                            .print-header .subtitle {
                                margin-top: 10px;
                                color: #666;
                                font-size: 14px;
                            }
                            .print-date {
                                text-align: right;
                                margin-bottom: 20px;
                                color: #666;
                                font-size: 12px;
                            }
                            .print-website {
                                text-align: center;
                                margin-bottom: 20px;
                                color: #666;
                                font-size: 12px;
                            }
                            .print-website a {
                                color: #004555;
                                text-decoration: none;
                            }
                            .print-website a:hover {
                                text-decoration: underline;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 20px;
                            }
                            th {
                                background-color: #004555;
                                color: white;
                                padding: 12px;
                                text-align: left;
                                font-weight: 600;
                                font-size: 12px;
                                text-transform: uppercase;
                                letter-spacing: 0.5px;
                            }
                            td {
                                padding: 12px;
                                border-bottom: 1px solid #e5e7eb;
                                font-size: 14px;
                            }
                            tr:hover {
                                background-color: #f9fafb;
                            }
                            .repertoire-title {
                                font-weight: 600;
                                color: #004555;
                            }
                            .repertoire-composer {
                                color: #666;
                            }
                            .repertoire-date {
                                color: #666;
                                font-size: 13px;
                            }
                            .repertoire-notes {
                                color: #666;
                                font-size: 13px;
                                max-width: 300px;
                            }
                            @media print {
                                body { padding: 20px; }
                                .no-print { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-header">
                            <h1>My Repertoire</h1>
                            <div class="subtitle">${userName}</div>
                        </div>
                        <div class="print-date">Printed: ${printDate}</div>
                        <div class="print-website">
                            <a href="https://jazzedge.academy/">https://jazzedge.academy/</a>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Composer</th>
                                    <th>Last Practice Date</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                repertoireItems.forEach(function(item) {
                    const lastPracticed = item.last_practiced ? formatDate(item.last_practiced) : 'Never';
                    const notes = item.notes || '';
                    
                    html += `
                        <tr>
                            <td class="repertoire-title">${escapeHtml(item.title)}</td>
                            <td class="repertoire-composer">${escapeHtml(item.composer)}</td>
                            <td class="repertoire-date">${lastPracticed}</td>
                            <td class="repertoire-notes">${escapeHtml(notes)}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </body>
                    </html>
                `;
                
                printWindow.document.write(html);
                printWindow.document.close();
                
                // Wait for content to load, then print
                setTimeout(function() {
                    printWindow.print();
                }, 250);
            }
            
        })(jQuery);
        
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
        
        // Enqueue scripts and styles - ensure jQuery is loaded
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
                    <div class="jph-leaderboard-title-section">
                        <h2>🏆 Practice Leaderboard</h2>
                        <div class="jph-leaderboard-actions">
                            <button id="jph-ranking-help-btn" type="button" class="jph-btn jph-btn-secondary jph-ranking-help-btn" onclick="showRankingHelp()">
                                <span class="btn-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                    </svg>
                                </span>
                                How does ranking work?
                            </button>
                            <button id="jph-back-to-hub-btn" type="button" class="jph-btn jph-btn-primary jph-back-to-hub-btn" onclick="goBackToHub()">
                                <span class="btn-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                    </svg>
                                </span>
                                Back To Practice Hub
                            </button>
                        </div>
                    </div>
                    
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
                        <!-- Sort controls will be added here -->
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
            margin-bottom: 30px;
        }
        
        .jph-leaderboard-title-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .jph-leaderboard-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .jph-leaderboard-header h2 {
            margin: 0;
            color: #1f2937;
            font-size: 28px;
            font-weight: 700;
        }
        
        .jph-back-to-hub-btn {
            background: #059669;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        
        .jph-back-to-hub-btn:hover {
            background: #047857;
            transform: translateY(-1px);
        }
        
        .jph-ranking-help-btn {
            background: #6b7280;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        
        .jph-ranking-help-btn:hover {
            background: #4b5563;
            transform: translateY(-1px);
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
        (function() {
            // Wait for jQuery to be available
            let retryCount = 0;
            const maxRetries = 50; // Max 5 seconds (50 * 100ms)
            
            function initLeaderboard() {
                if (typeof jQuery === 'undefined') {
                    retryCount++;
                    if (retryCount < maxRetries) {
                        // jQuery not loaded yet, wait a bit and try again
                        setTimeout(initLeaderboard, 100);
                    } else {
                        // jQuery failed to load - show error
                        console.error('jQuery failed to load after ' + maxRetries + ' retries');
                        document.getElementById('jph-leaderboard-error').style.display = 'block';
                        document.getElementById('jph-leaderboard-loading').style.display = 'none';
                    }
                    return;
                }
                
                jQuery(document).ready(function($) {
                    let currentPage = 0;
                    let currentSort = '<?php echo esc_js($atts['sort_by']); ?>';
                    // Ensure default sort is descending for XP
                    if (currentSort === 'total_xp' && !currentSort.startsWith('-')) {
                        currentSort = '-' + currentSort;
                    }
                    let currentLimit = <?php echo intval($atts['limit']); ?>;
                    let isLoading = false;
                    
                    // Back to Practice Hub function
                    window.goBackToHub = function() {
                // Try to find and focus the parent window (Practice Hub)
                if (window.opener && !window.opener.closed) {
                    window.opener.focus();
                    window.close();
                } else {
                    // Fallback: open Practice Hub in current window
                    window.location.href = '/practice-hub';
                }
            };
            
            // Ranking help function
            window.showRankingHelp = function() {
                alert('How Does Ranking Work?\n\n' +
                    '• Users are ranked by their Total XP in descending order\n' +
                    '• Higher XP = Better rank (lower number)\n' +
                    '• Ties are broken by earliest achievement date\n' +
                    '• Only users who choose to appear on the leaderboard are included\n' +
                    '• Rankings update automatically as you earn more XP\n\n' +
                    '💡 Tip: Practice regularly and complete challenges to climb the ranks!');
            };
            
            
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
                    error: function(xhr, status, error) {
                        console.error('Leaderboard AJAX error:', status, error, xhr);
                        showError('Failed to load leaderboard. Please check console for details.');
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
            
            // Initialize leaderboard
            loadLeaderboard();
            loadUserPosition();
            loadLeaderboardStats();
            updateSortIndicators();
                });
            }
            
            // Start initialization
            initLeaderboard();
        })();
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
     * Render practice items widget
     */
    public function render_practice_items_widget($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'limit' => '5',
            'title' => 'Practice Items',
            'show_title' => 'true',
            'show_log_button' => 'true',
            'style' => 'compact'
        ), $atts);
        
        // Validate parameters
        $limit = max(1, min(20, intval($atts['limit'])));
        
        // Get current user ID
        $current_user_id = is_user_logged_in() ? get_current_user_id() : 0;
        
        if (!$current_user_id) {
            return '<div class="jph-practice-items-widget jph-no-data">Please log in to view your practice items.</div>';
        }
        
        // Get user's practice items
        $practice_items = $this->database->get_user_practice_items($current_user_id, $limit);
        
        if (empty($practice_items)) {
            return '<div class="jph-practice-items-widget jph-no-data">No practice items found. Add some items to your practice list!</div>';
        }
        
        // Get Practice Hub page URL from settings
        $practice_hub_page_id = get_option('jph_practice_hub_page_id', '');
        $practice_hub_url = '';
        if ($practice_hub_page_id) {
            $practice_hub_url = get_permalink($practice_hub_page_id);
        }
        if (!$practice_hub_url) {
            $practice_hub_url = home_url('/practice-hub/'); // Fallback
        }
        
        // Styles are included inline in the widget
        
        ob_start();
        ?>
        <div class="jph-practice-items-widget jph-practice-items-widget-<?php echo esc_attr($atts['style']); ?>">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h3 class="jph-widget-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <div class="jph-practice-items-list">
                <?php foreach ($practice_items as $item): ?>
                    <div class="jph-practice-item" data-item-id="<?php echo esc_attr($item['id']); ?>">
                        <div class="jph-practice-item-content">
                            <div class="jph-practice-item-name">
                                <span class="jph-item-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z" />
                                    </svg>
                                </span>
                                <span class="jph-item-title"><?php echo esc_html($item['name']); ?></span>
                            </div>
                            <?php if (!empty($item['description'])): ?>
                                <div class="jph-practice-item-description">
                                    <?php echo esc_html($item['description']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="jph-practice-item-meta">
                                <span class="jph-item-difficulty"><?php echo esc_html(ucfirst($item['difficulty'])); ?></span>
                                <?php if ($item['last_practiced']): ?>
                                    <span class="jph-item-last-practiced">Last: <?php echo esc_html(date('M j', strtotime($item['last_practiced']))); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($atts['show_log_button'] === 'true'): ?>
                            <button class="jph-log-practice-btn" data-item-id="<?php echo esc_attr($item['id']); ?>" data-item-name="<?php echo esc_attr($item['name']); ?>">
                                <span class="btn-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </span>
                                Log Practice
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($practice_items) >= $limit): ?>
                <div class="jph-widget-footer">
                    <a href="/practice-hub" class="jph-view-all-link">View All Practice Items →</a>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .jph-practice-items-widget {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .jph-practice-items-widget .jph-widget-title {
            margin: 0 0 16px 0;
            color: #1f2937;
            font-size: 18px;
            font-weight: 600;
        }
        
        .jph-practice-items-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .jph-practice-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        
        .jph-practice-item:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }
        
        .jph-practice-item-content {
            flex: 1;
        }
        
        .jph-practice-item-name {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }
        
        .jph-item-icon {
            font-size: 16px;
        }
        
        .jph-item-title {
            font-weight: 500;
            color: #1f2937;
            font-size: 14px;
        }
        
        .jph-practice-item-description {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 6px;
            line-height: 1.4;
        }
        
        .jph-practice-item-meta {
            display: flex;
            gap: 12px;
            font-size: 11px;
            color: #9ca3af;
        }
        
        .jph-item-difficulty {
            background: #e5e7eb;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .jph-item-difficulty.beginner {
            background: #dcfce7;
            color: #166534;
        }
        
        .jph-item-difficulty.intermediate {
            background: #fef3c7;
            color: #92400e;
        }
        
        .jph-item-difficulty.advanced {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .jph-log-practice-btn {
            background: #F04E23;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        
        .jph-log-practice-btn:hover {
            background: #e0451f;
            transform: translateY(-1px);
        }
        
        .jph-widget-footer {
            margin-top: 16px;
            text-align: center;
        }
        
        .jph-view-all-link {
            color: #059669;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .jph-view-all-link:hover {
            color: #047857;
            text-decoration: underline;
        }
        
        .jph-practice-items-widget.jph-no-data {
            text-align: center;
            color: #6b7280;
            padding: 40px 20px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Log practice button handler
            $('.jph-log-practice-btn').on('click', function() {
                const itemId = $(this).data('item-id');
                const practiceHubUrl = '<?php echo esc_js($practice_hub_url); ?>';
                
                // Always redirect to Practice Hub
                window.location.href = practiceHubUrl + '?log_item=' + itemId;
            });
        });
        </script>
        
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
            font-weight: 700;
            color: #333;
            margin-bottom: 4px;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
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
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: left;
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
    
    /**
     * Render practice streak widget
     */
    public function render_streak_widget($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'user_id' => 'current',
            'style' => 'compact',
            'layout' => 'horizontal',
            'title' => 'Practice Streak',
            'show_title' => 'true',
            'show_longest_streak' => 'true',
            'show_streak_goal' => 'false',
            'streak_goal' => '30',
            'show_motivational_text' => 'true',
            'show_practice_hub_link' => 'false',
            'minimal' => 'false'
        ), $atts);
        
        // Determine user ID
        if ($atts['user_id'] === 'current') {
            if (!is_user_logged_in()) {
                return '<div class="jph-streak-widget jph-login-required">Please log in to view your practice streak.</div>';
            }
            $user_id = get_current_user_id();
        } else {
            $user_id = intval($atts['user_id']);
            if (!$user_id) {
                return '<div class="jph-streak-widget jph-error">Invalid user ID.</div>';
            }
        }
        
        // Get user stats
        $gamification = new APH_Gamification();
        $user_stats = $gamification->get_user_stats($user_id);
        $user_stats = $this->sanitize_user_stats($user_stats);
        
        // Get user info
        $user = get_user_by('id', $user_id);
        $display_name = $user_stats['display_name'] ?: $user->display_name ?: $user->user_login;
        
        // Calculate streak status and motivational text
        $current_streak = intval($user_stats['current_streak']);
        $longest_streak = intval($user_stats['longest_streak']);
        $streak_goal = intval($atts['streak_goal']);
        
        // Determine streak status
        $streak_status = 'active';
        $streak_message = '';
        
        if ($current_streak == 0) {
            $streak_status = 'broken';
            $streak_message = 'Start your practice streak today!';
        } elseif ($current_streak >= $streak_goal) {
            $streak_status = 'goal_achieved';
            $streak_message = 'Amazing! You\'ve reached your streak goal!';
        } elseif ($current_streak >= 7) {
            $streak_status = 'strong';
            $streak_message = 'Great consistency! Keep it up!';
        } elseif ($current_streak >= 3) {
            $streak_status = 'building';
            $streak_message = 'Nice start! Keep building that streak!';
        } else {
            $streak_status = 'new';
            $streak_message = 'Every streak starts with a single day!';
        }
        
        // Enqueue styles
        wp_enqueue_style('jph-widgets', plugin_dir_url(__FILE__) . '../assets/css/widgets.css', array(), '1.0.0');
        
        ob_start();
        ?>
        <div class="jph-streak-widget jph-streak-widget-<?php echo esc_attr($atts['style']); ?> jph-streak-layout-<?php echo esc_attr($atts['layout']); ?> jph-streak-status-<?php echo esc_attr($streak_status); ?> <?php echo $atts['minimal'] === 'true' ? 'jph-streak-minimal' : ''; ?>">
            <?php if ($atts['show_title'] === 'true' && $atts['minimal'] !== 'true'): ?>
                <h3 class="jph-widget-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <div class="jph-streak-content">
                <?php if ($atts['minimal'] === 'true'): ?>
                    <!-- Minimal Layout - Arrow, Number, and "Streak" -->
                    <div class="jph-streak-minimal-display">
                        <div class="jph-streak-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                            </svg>
                        </div>
                        <div class="jph-streak-info">
                            <div class="jph-streak-value"><?php echo $current_streak; ?></div>
                            <div class="jph-streak-label">Streak</div>
                        </div>
                    </div>
                <?php elseif ($atts['layout'] === 'vertical'): ?>
                    <!-- Vertical Layout -->
                    <div class="jph-streak-main">
                        <div class="jph-streak-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="48" height="48">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                            </svg>
                        </div>
                        <div class="jph-streak-value"><?php echo $current_streak; ?></div>
                        <div class="jph-streak-label">Day Streak</div>
                    </div>
                    
                    <?php if ($atts['show_longest_streak'] === 'true' && $longest_streak > $current_streak): ?>
                        <div class="jph-streak-secondary">
                            <div class="jph-streak-longest">
                                <span class="jph-streak-longest-label">Best:</span>
                                <span class="jph-streak-longest-value"><?php echo $longest_streak; ?> days</span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_streak_goal'] === 'true'): ?>
                        <div class="jph-streak-progress">
                            <div class="jph-streak-progress-bar">
                                <div class="jph-streak-progress-fill" style="width: <?php echo min(100, ($current_streak / $streak_goal) * 100); ?>%"></div>
                            </div>
                            <div class="jph-streak-progress-text">
                                <?php echo $current_streak; ?> / <?php echo $streak_goal; ?> days
                            </div>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Horizontal Layout -->
                    <div class="jph-streak-main">
                        <div class="jph-streak-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                            </svg>
                        </div>
                        <div class="jph-streak-info">
                            <div class="jph-streak-value"><?php echo $current_streak; ?></div>
                            <div class="jph-streak-label">Day Streak</div>
                        </div>
                        
                        <?php if ($atts['show_longest_streak'] === 'true' && $longest_streak > $current_streak): ?>
                            <div class="jph-streak-longest">
                                <span class="jph-streak-longest-label">Best:</span>
                                <span class="jph-streak-longest-value"><?php echo $longest_streak; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($atts['show_streak_goal'] === 'true'): ?>
                        <div class="jph-streak-progress">
                            <div class="jph-streak-progress-bar">
                                <div class="jph-streak-progress-fill" style="width: <?php echo min(100, ($current_streak / $streak_goal) * 100); ?>%"></div>
                            </div>
                            <div class="jph-streak-progress-text">
                                <?php echo $current_streak; ?> / <?php echo $streak_goal; ?> days
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($atts['show_motivational_text'] === 'true' && !empty($streak_message) && $atts['minimal'] !== 'true'): ?>
                    <div class="jph-streak-message">
                        <?php echo esc_html($streak_message); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($atts['show_practice_hub_link'] === 'true' && $atts['minimal'] !== 'true'): ?>
                <div class="jph-widget-footer">
                    <a href="<?php echo esc_url($this->get_practice_hub_url()); ?>" class="jph-practice-hub-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="jph-hub-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                        </svg>
                        Continue Your Streak
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .jph-streak-widget {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            padding: 24px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .jph-streak-widget:hover {
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .jph-widget-title {
            margin: 0 0 20px 0;
            font-size: 20px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        
        .jph-streak-content {
            text-align: center;
        }
        
        /* Vertical Layout */
        .jph-streak-layout-vertical .jph-streak-main {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .jph-streak-layout-vertical .jph-streak-icon {
            color: #f97316;
            margin-bottom: 8px;
        }
        
        .jph-streak-layout-vertical .jph-streak-value {
            font-size: 48px;
            font-weight: 700;
            color: #333;
            line-height: 1;
        }
        
        .jph-streak-layout-vertical .jph-streak-label {
            font-size: 16px;
            color: #666;
            font-weight: 500;
        }
        
        /* Horizontal Layout */
        .jph-streak-layout-horizontal .jph-streak-main {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 20px;
        }
        
        .jph-streak-layout-horizontal .jph-streak-icon {
            color: #f97316;
            flex-shrink: 0;
        }
        
        .jph-streak-layout-horizontal .jph-streak-info {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .jph-streak-layout-horizontal .jph-streak-value {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            line-height: 1;
        }
        
        .jph-streak-layout-horizontal .jph-streak-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        
        .jph-streak-layout-horizontal .jph-streak-longest {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-left: 20px;
            padding-left: 20px;
            border-left: 1px solid #e1e5e9;
        }
        
        .jph-streak-layout-horizontal .jph-streak-longest-label {
            font-size: 12px;
            color: #999;
            font-weight: 500;
        }
        
        .jph-streak-layout-horizontal .jph-streak-longest-value {
            font-size: 18px;
            font-weight: 600;
            color: #666;
        }
        
        /* Secondary Info */
        .jph-streak-secondary {
            margin-bottom: 20px;
        }
        
        .jph-streak-longest {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        
        .jph-streak-longest-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        
        .jph-streak-longest-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        /* Progress Bar */
        .jph-streak-progress {
            margin-bottom: 16px;
        }
        
        .jph-streak-progress-bar {
            width: 100%;
            height: 8px;
            background: #e1e5e9;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .jph-streak-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #f97316, #ea580c);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .jph-streak-progress-text {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        
        /* Motivational Message */
        .jph-streak-message {
            font-size: 16px;
            color: #333;
            font-weight: 500;
            margin-bottom: 16px;
            padding: 12px;
            background: #f0f9ff;
            border-radius: 8px;
            border-left: 4px solid #0ea5e9;
        }
        
        /* Status-based styling */
        .jph-streak-status-broken .jph-streak-icon {
            color: #dc2626;
        }
        
        .jph-streak-status-broken .jph-streak-value {
            color: #dc2626;
        }
        
        .jph-streak-status-broken .jph-streak-message {
            background: #fef2f2;
            border-left-color: #dc2626;
            color: #dc2626;
        }
        
        .jph-streak-status-goal_achieved .jph-streak-icon {
            color: #16a34a;
        }
        
        .jph-streak-status-goal_achieved .jph-streak-value {
            color: #16a34a;
        }
        
        .jph-streak-status-goal_achieved .jph-streak-message {
            background: #f0fdf4;
            border-left-color: #16a34a;
            color: #16a34a;
        }
        
        .jph-streak-status-strong .jph-streak-icon {
            color: #7c3aed;
        }
        
        .jph-streak-status-strong .jph-streak-value {
            color: #7c3aed;
        }
        
        .jph-streak-status-strong .jph-streak-message {
            background: #faf5ff;
            border-left-color: #7c3aed;
            color: #7c3aed;
        }
        
        /* Widget Footer */
        .jph-widget-footer {
            text-align: center;
            margin-top: 20px;
        }
        
        .jph-practice-hub-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #0ea5e9;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            gap: 8px;
        }
        
        .jph-practice-hub-link:hover {
            background: #0284c7;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .jph-hub-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .jph-streak-widget {
                padding: 20px;
            }
            
            .jph-streak-layout-horizontal .jph-streak-main {
                flex-direction: column;
                gap: 12px;
            }
            
            .jph-streak-layout-horizontal .jph-streak-longest {
                margin-left: 0;
                padding-left: 0;
                border-left: none;
                border-top: 1px solid #e1e5e9;
                padding-top: 12px;
            }
            
            .jph-streak-layout-vertical .jph-streak-value {
                font-size: 40px;
            }
            
            .jph-streak-layout-horizontal .jph-streak-value {
                font-size: 32px;
            }
        }
        
        /* Compact Style */
        .jph-streak-widget-compact {
            padding: 16px;
        }
        
        .jph-streak-widget-compact .jph-widget-title {
            font-size: 16px;
            margin-bottom: 12px;
        }
        
        .jph-streak-widget-compact .jph-streak-layout-vertical .jph-streak-value {
            font-size: 36px;
        }
        
        .jph-streak-widget-compact .jph-streak-layout-horizontal .jph-streak-value {
            font-size: 28px;
        }
        
        .jph-login-required,
        .jph-error {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        /* Minimal Layout */
        .jph-streak-minimal {
            padding: 12px;
            background: transparent;
            border: none;
            box-shadow: none;
            margin: 0;
        }
        
        .jph-streak-minimal:hover {
            box-shadow: none;
            transform: none;
        }
        
        .jph-streak-minimal-display {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .jph-streak-minimal .jph-streak-icon {
            color: #f97316;
            flex-shrink: 0;
        }
        
        .jph-streak-minimal .jph-streak-info {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .jph-streak-minimal .jph-streak-value {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            line-height: 1;
        }
        
        .jph-streak-minimal .jph-streak-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
            margin-top: 2px;
        }
        
        /* Minimal status colors */
        .jph-streak-minimal.jph-streak-status-broken .jph-streak-icon {
            color: #dc2626;
        }
        
        .jph-streak-minimal.jph-streak-status-broken .jph-streak-value {
            color: #dc2626;
        }
        
        .jph-streak-minimal.jph-streak-status-goal_achieved .jph-streak-icon {
            color: #16a34a;
        }
        
        .jph-streak-minimal.jph-streak-status-goal_achieved .jph-streak-value {
            color: #16a34a;
        }
        
        .jph-streak-minimal.jph-streak-status-strong .jph-streak-icon {
            color: #7c3aed;
        }
        
        .jph-streak-minimal.jph-streak-status-strong .jph-streak-value {
            color: #7c3aed;
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Convert video URLs to embeddable iframes
     */
    private function convert_video_urls_to_embeds($content) {
        // YouTube URL patterns
        $youtube_patterns = [
            '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/',
            '/(?:https?:\/\/)?(?:www\.)?youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/(?:https?:\/\/)?(?:www\.)?youtube\.com\/v\/([a-zA-Z0-9_-]+)/'
        ];
        
        // Vimeo URL patterns
        $vimeo_patterns = [
            '/(?:https?:\/\/)?(?:www\.)?vimeo\.com\/([0-9]+)/',
            '/(?:https?:\/\/)?(?:www\.)?vimeo\.com\/embed\/([0-9]+)/'
        ];
        
        // Convert YouTube URLs
        foreach ($youtube_patterns as $pattern) {
            $content = preg_replace_callback($pattern, function($matches) {
                $video_id = $matches[1];
                return '<div class="video-embed"><iframe width="100%" height="200" src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allowfullscreen></iframe></div>';
            }, $content);
        }
        
        // Convert Vimeo URLs
        foreach ($vimeo_patterns as $pattern) {
            $content = preg_replace_callback($pattern, function($matches) {
                $video_id = $matches[1];
                return '<div class="video-embed"><iframe width="100%" height="200" src="https://player.vimeo.com/video/' . $video_id . '" frameborder="0" allowfullscreen></iframe></div>';
            }, $content);
        }
        
        return $content;
    }

    /**
     * Automatically add user to all community spaces on first load
     * Uses user meta to track if spaces were already added
     */
    private function auto_add_user_to_spaces($user_id) {
        // Check if user has already been added to spaces
        $spaces_added = get_user_meta($user_id, 'jph_spaces_added', true);
        if ($spaces_added) {
            return; // Already added, skip
        }
        
        // Check if Fluent Community is available
        if (!class_exists('\\FluentCommunity\\App\\Services\\Helper')) {
            return;
        }
        
        global $wpdb;
        
        // Get all community spaces (excluding space_group and sidebar_link types)
        $spaces = $wpdb->get_results("
            SELECT id, title, type 
            FROM {$wpdb->prefix}fcom_spaces 
            WHERE type IN ('community') 
            AND status = 'published'
            ORDER BY id ASC
        ");
        
        if (empty($spaces)) {
            return;
        }
        
        $added_count = 0;
        $errors = array();
        
        foreach ($spaces as $space) {
            try {
                // Add user to space
                \FluentCommunity\App\Services\Helper::addToSpace($space->id, $user_id, 'member', 'by_admin');
                $added_count++;
                
                // Log the addition
                
            } catch (Exception $e) {
                $errors[] = "Failed to add to space '{$space->title}': " . $e->getMessage();
            }
        }
        
        // Mark that spaces have been added for this user
        update_user_meta($user_id, 'jph_spaces_added', true);
        
        // Log summary
    }
}
