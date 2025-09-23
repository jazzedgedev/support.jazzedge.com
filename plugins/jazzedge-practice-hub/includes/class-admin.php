<?php
/**
 * Admin interface for JazzEdge Practice Hub
 * 
 * @package JazzEdge_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Admin initialization
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Practice Hub',
            'Practice Hub',
            'manage_options',
            'jph-dashboard',
            array($this, 'dashboard_page'),
            'dashicons-music',
            30
        );
        
        add_submenu_page(
            'jph-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'jph-dashboard',
            array($this, 'dashboard_page')
        );
        
        add_submenu_page(
            'jph-dashboard',
            'User Stats',
            'User Stats',
            'manage_options',
            'jph-user-stats',
            array($this, 'user_stats_page')
        );
        
        add_submenu_page(
            'jph-dashboard',
            'Practice Sessions',
            'Practice Sessions',
            'manage_options',
            'jph-sessions',
            array($this, 'sessions_page')
        );
        
        add_submenu_page(
            'jph-dashboard',
            'Gamification Settings',
            'Gamification',
            'manage_options',
            'jph-gamification',
            array($this, 'gamification_page')
        );
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        $stats = $this->get_dashboard_stats();
        ?>
        <div class="wrap">
            <h1>🎹 JazzEdge Practice Hub - Admin Dashboard</h1>
            
            <div class="jph-admin-dashboard">
                <!-- Overview Stats -->
                <div class="jph-stats-grid">
                    <div class="jph-stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                    </div>
                    
                    <div class="jph-stat-card">
                        <div class="stat-icon">🎯</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo number_format($stats['total_sessions']); ?></div>
                            <div class="stat-label">Practice Sessions</div>
                        </div>
                    </div>
                    
                    <div class="jph-stat-card">
                        <div class="stat-icon">⏱️</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo number_format($stats['total_minutes']); ?></div>
                            <div class="stat-label">Total Minutes</div>
                        </div>
                    </div>
                    
                    <div class="jph-stat-card">
                        <div class="stat-icon">🔥</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo number_format($stats['avg_streak'], 1); ?></div>
                            <div class="stat-label">Avg Streak</div>
                        </div>
                    </div>
                </div>
                
                <!-- Gamification Overview -->
                <div class="jph-admin-section">
                    <h2>🎮 Gamification System Overview</h2>
                    <div class="jph-info-grid">
                        <div class="jph-info-card">
                            <h3>⭐ XP System</h3>
                            <ul>
                                <li><strong>Base XP:</strong> 1 XP per minute (max 60 XP)</li>
                                <li><strong>Sentiment Multiplier:</strong> 1-5 scale (20%-100%)</li>
                                <li><strong>Improvement Bonus:</strong> 25% extra XP</li>
                                <li><strong>Formula:</strong> <code>round(duration_xp × sentiment_multiplier × improvement_bonus)</code></li>
                            </ul>
                        </div>
                        
                        <div class="jph-info-card">
                            <h3>🎯 Level System</h3>
                            <ul>
                                <li><strong>Formula:</strong> <code>floor(sqrt(total_xp / 100)) + 1</code></li>
                                <li><strong>Level 1:</strong> 0-99 XP</li>
                                <li><strong>Level 2:</strong> 100-399 XP</li>
                                <li><strong>Level 3:</strong> 400-899 XP</li>
                                <li><strong>Exponential growth</strong> for higher levels</li>
                            </ul>
                        </div>
                        
                        <div class="jph-info-card">
                            <h3>🔥 Streak System</h3>
                            <ul>
                                <li><strong>Daily Practice:</strong> Maintains/extends streak</li>
                                <li><strong>Missed Day:</strong> Resets streak to 0</li>
                                <li><strong>Tracking:</strong> Current & longest streak</li>
                                <li><strong>Date-based:</strong> Based on last practice date</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="jph-admin-section">
                    <h2>📊 Recent Activity</h2>
                    <div class="jph-recent-activity">
                        <?php $this->display_recent_activity(); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .jph-admin-dashboard {
            max-width: 1200px;
        }
        
        .jph-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .jph-stat-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            font-size: 32px;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 50%;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .jph-admin-section {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .jph-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .jph-info-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
        }
        
        .jph-info-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .jph-info-card ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .jph-info-card li {
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .jph-info-card code {
            background: #e9ecef;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 12px;
        }
        </style>
        <?php
    }
    
    /**
     * User Stats page
     */
    public function user_stats_page() {
        global $wpdb;
        
        // Get all users with stats
        $users = $wpdb->get_results("
            SELECT u.ID, u.user_login, u.display_name, s.* 
            FROM {$wpdb->users} u 
            LEFT JOIN {$wpdb->prefix}jph_user_stats s ON u.ID = s.user_id 
            ORDER BY s.total_xp DESC, u.display_name ASC
        ");
        
        ?>
        <div class="wrap">
            <h1>👥 User Statistics</h1>
            
            <div class="jph-user-stats">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Level</th>
                            <th>Total XP</th>
                            <th>Current Streak</th>
                            <th>Longest Streak</th>
                            <th>Sessions</th>
                            <th>Total Minutes</th>
                            <th>Last Practice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($user->display_name ?: $user->user_login); ?></strong>
                                <br><small><?php echo esc_html($user->user_login); ?></small>
                            </td>
                            <td>
                                <span class="jph-level-badge">Level <?php echo $user->current_level ?: 1; ?></span>
                            </td>
                            <td><?php echo number_format($user->total_xp ?: 0); ?> XP</td>
                            <td><?php echo $user->current_streak ?: 0; ?> days</td>
                            <td><?php echo $user->longest_streak ?: 0; ?> days</td>
                            <td><?php echo number_format($user->total_sessions ?: 0); ?></td>
                            <td><?php echo number_format($user->total_minutes ?: 0); ?> min</td>
                            <td><?php echo $user->last_practice_date ? date('M j, Y', strtotime($user->last_practice_date)) : 'Never'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <style>
        .jph-level-badge {
            background: #00A8A8;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        </style>
        <?php
    }
    
    /**
     * Sessions page
     */
    public function sessions_page() {
        global $wpdb;
        
        // Get recent practice sessions
        $sessions = $wpdb->get_results("
            SELECT ps.*, pi.name as practice_item_name, u.display_name, u.user_login
            FROM {$wpdb->prefix}jph_practice_sessions ps
            LEFT JOIN {$wpdb->prefix}jph_practice_items pi ON ps.practice_item_id = pi.id
            LEFT JOIN {$wpdb->users} u ON ps.user_id = u.ID
            ORDER BY ps.created_at DESC
            LIMIT 100
        ");
        
        ?>
        <div class="wrap">
            <h1>📊 Practice Sessions</h1>
            
            <div class="jph-sessions">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Practice Item</th>
                            <th>Duration</th>
                            <th>Sentiment</th>
                            <th>Improvement</th>
                            <th>XP Earned</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?php echo esc_html($session->display_name ?: $session->user_login); ?></td>
                            <td><?php echo esc_html($session->practice_item_name); ?></td>
                            <td><?php echo $session->duration_minutes; ?> min</td>
                            <td>
                                <span class="jph-sentiment-<?php echo $session->sentiment_score; ?>">
                                    <?php echo $session->sentiment_score; ?>/5
                                </span>
                            </td>
                            <td><?php echo $session->improvement_detected ? '✅ Yes' : '❌ No'; ?></td>
                            <td><?php echo $session->xp_earned; ?> XP</td>
                            <td><?php echo date('M j, Y g:i A', strtotime($session->created_at)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <style>
        .jph-sentiment-1 { color: #e74c3c; font-weight: bold; }
        .jph-sentiment-2 { color: #f39c12; font-weight: bold; }
        .jph-sentiment-3 { color: #f1c40f; font-weight: bold; }
        .jph-sentiment-4 { color: #2ecc71; font-weight: bold; }
        .jph-sentiment-5 { color: #27ae60; font-weight: bold; }
        </style>
        <?php
    }
    
    /**
     * Gamification settings page
     */
    public function gamification_page() {
        ?>
        <div class="wrap">
            <h1>🎮 Gamification Settings</h1>
            
            <div class="jph-gamification-settings">
                <div class="jph-admin-section">
                    <h2>⚙️ Current Settings</h2>
                    <table class="form-table">
                        <tr>
                            <th>XP Calculation</th>
                            <td>
                                <strong>Base XP:</strong> 1 XP per minute (max 60 XP)<br>
                                <strong>Sentiment Multiplier:</strong> 1-5 scale (20%-100%)<br>
                                <strong>Improvement Bonus:</strong> 25% extra XP
                            </td>
                        </tr>
                        <tr>
                            <th>Level Formula</th>
                            <td><code>floor(sqrt(total_xp / 100)) + 1</code></td>
                        </tr>
                        <tr>
                            <th>Streak Rules</th>
                            <td>
                                <strong>Daily Practice:</strong> Maintains/extends streak<br>
                                <strong>Missed Day:</strong> Resets streak to 0<br>
                                <strong>Tracking:</strong> Current & longest streak
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="jph-admin-section">
                    <h2>🧪 Test Gamification</h2>
                    <p>Use the REST API endpoints to test the gamification system:</p>
                    <ul>
                        <li><strong>Get User Stats:</strong> <code>GET /wp-json/jph/v1/user-stats?user_id=1</code></li>
                        <li><strong>Test XP Addition:</strong> <code>POST /wp-json/jph/v1/test-gamification</code></li>
                        <li><strong>Log Practice Session:</strong> <code>POST /wp-json/jph/v1/practice-sessions</code></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display recent activity
     */
    private function display_recent_activity() {
        global $wpdb;
        
        // Get recent sessions
        $recent_sessions = $wpdb->get_results("
            SELECT ps.*, pi.name as practice_item_name, u.display_name
            FROM {$wpdb->prefix}jph_practice_sessions ps
            LEFT JOIN {$wpdb->prefix}jph_practice_items pi ON ps.practice_item_id = pi.id
            LEFT JOIN {$wpdb->users} u ON ps.user_id = u.ID
            ORDER BY ps.created_at DESC
            LIMIT 10
        ");
        
        if (empty($recent_sessions)) {
            echo '<p>No recent activity found.</p>';
            return;
        }
        
        echo '<div class="jph-activity-list">';
        foreach ($recent_sessions as $session) {
            echo '<div class="jph-activity-item">';
            echo '<div class="activity-icon">🎹</div>';
            echo '<div class="activity-content">';
            echo '<strong>' . esc_html($session->display_name) . '</strong> practiced <strong>' . esc_html($session->practice_item_name) . '</strong>';
            echo '<br><small>' . $session->duration_minutes . ' minutes • ' . $session->xp_earned . ' XP • ' . date('M j, g:i A', strtotime($session->created_at)) . '</small>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        
        echo '<style>
        .jph-activity-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .jph-activity-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .activity-icon {
            font-size: 20px;
            width: 30px;
            text-align: center;
        }
        
        .activity-content {
            flex: 1;
        }
        </style>';
    }
    
    /**
     * Get dashboard stats
     */
    public function get_dashboard_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Total users
        $stats['total_users'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
        
        // Active users (users with stats)
        $stats['active_users'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_user_stats");
        
        // Total sessions
        $stats['total_sessions'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_practice_sessions");
        
        // Total minutes
        $stats['total_minutes'] = $wpdb->get_var("SELECT SUM(duration_minutes) FROM {$wpdb->prefix}jph_practice_sessions");
        
        // Average streak
        $stats['avg_streak'] = $wpdb->get_var("SELECT AVG(current_streak) FROM {$wpdb->prefix}jph_user_stats");
        
        // Badges earned
        $stats['badges_earned'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_user_badges");
        
        return $stats;
    }
}
