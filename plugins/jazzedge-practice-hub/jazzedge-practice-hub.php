<?php
/**
 * Plugin Name: JazzEdge Practice Hub
 * Plugin URI: https://academy.jazzedge.com
 * Description: A neuroscience-backed practice system for JazzEdge Academy, incorporating spaced repetition, gamification, and AI analysis.
 * Version: 2.1.0
 * Author: JazzEdge
 * Author URI: https://academy.jazzedge.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jazzedge-practice-hub
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JPH_VERSION', '2.1.0');
define('JPH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JPH_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('JPH_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files for activation
require_once JPH_PLUGIN_PATH . 'includes/class-database.php';

/**
 * Main JazzEdge Practice Hub Plugin Class
 */
class JazzEdge_Practice_Hub {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->register_shortcodes();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Register cron hooks
        add_action('jph_daily_milestone_check', array($this, 'daily_milestone_check'));
        
        // Register AJAX handlers
        add_action('wp_ajax_jph_award_first_steps_badge', array($this, 'ajax_award_first_steps_badge'));
        
        // Streak Shield & Recovery AJAX handlers
        add_action('wp_ajax_jph_purchase_streak_shield', array($this, 'ajax_purchase_streak_shield'));
        add_action('wp_ajax_jph_repair_streak', array($this, 'ajax_repair_streak'));
        add_action('wp_ajax_jph_test_auto_shield', array($this, 'ajax_test_auto_shield'));
        
        // Streak testing AJAX handlers
        add_action('wp_ajax_jph_reset_streak_test', array($this, 'ajax_reset_streak_test'));
        add_action('wp_ajax_jph_add_gems_test', array($this, 'ajax_add_gems_test'));
        
        // Add nonce for AJAX security
        add_action('wp_enqueue_scripts', array($this, 'enqueue_ajax_nonce'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_ajax_nonce'));
        
        // Event tracking AJAX handlers (legacy)
        add_action('wp_ajax_jph_test_event', array($this, 'ajax_test_event'));
        add_action('wp_ajax_jph_test_all_events', array($this, 'ajax_test_all_events'));
        
        // Badge event tracking AJAX handlers
        add_action('wp_ajax_jph_test_badge_event', array($this, 'ajax_test_badge_event'));
        add_action('wp_ajax_jph_test_all_badge_events', array($this, 'ajax_test_all_badge_events'));
        add_action('wp_ajax_jph_get_event_logs', array($this, 'ajax_get_event_logs'));
        add_action('wp_ajax_jph_clear_event_logs', array($this, 'ajax_clear_event_logs'));
        add_action('wp_ajax_jph_empty_event_tracking_table', array($this, 'ajax_empty_event_tracking_table'));
        
        // Badge event log handlers
        add_action('wp_ajax_jph_get_badge_event_logs', array($this, 'ajax_get_badge_event_logs'));
        
        // Badge debugging AJAX handlers
        add_action('wp_ajax_jph_debug_user_badges', array($this, 'ajax_debug_user_badges'));
        add_action('wp_ajax_jph_debug_badge_assignment', array($this, 'ajax_debug_badge_assignment'));
        add_action('wp_ajax_jph_debug_marathon_badge', array($this, 'ajax_debug_marathon_badge'));
        add_action('wp_ajax_jph_simulate_badge_check', array($this, 'ajax_simulate_badge_check'));
        add_action('wp_ajax_jph_inspect_badge_database', array($this, 'ajax_inspect_badge_database'));
        add_action('wp_ajax_jph_check_practice_sessions', array($this, 'ajax_check_practice_sessions'));
        add_action('wp_ajax_jph_clear_badge_event_logs', array($this, 'ajax_clear_badge_event_logs'));
        
        // Practice items reordering AJAX handler
        add_action('wp_ajax_jph_reorder_practice_items', array($this, 'ajax_reorder_practice_items'));
        
        // Practice items debugging AJAX handler
        add_action('wp_ajax_jph_debug_practice_items', array($this, 'ajax_debug_practice_items'));
        
        // Danger Zone AJAX handlers
        add_action('wp_ajax_jph_wipe_all_data', array($this, 'ajax_wipe_all_data'));
        add_action('wp_ajax_jph_reset_all_stats', array($this, 'ajax_reset_all_stats'));
        add_action('wp_ajax_jph_clear_all_badges', array($this, 'ajax_clear_all_badges'));
        add_action('wp_ajax_jph_clear_all_favorites', array($this, 'ajax_clear_all_favorites'));
        add_action('wp_ajax_jph_update_badge_order', array($this, 'ajax_update_badge_order'));
        add_action('wp_ajax_jph_get_database_status', array($this, 'ajax_get_database_status'));
        add_action('wp_ajax_jph_delete_all_badges', array($this, 'ajax_delete_all_badges'));
        
        // Individual data clearing AJAX handler
        add_action('wp_ajax_jph_clear_data_section', array($this, 'ajax_clear_data_section'));
        
        // Clear all user data AJAX handler
        add_action('wp_ajax_jph_clear_all_user_data', array($this, 'ajax_clear_all_user_data'));
        
        // REST API handles database operations (no AJAX needed)
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Add admin notice for badge key issues
        add_action('admin_notices', array($this, 'check_badge_key_issues'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('jazzedge-practice-hub', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Test Katahdin AI Hub connection
        $this->test_katahdin_connection();
        
        // Initialize database class
        require_once JPH_PLUGIN_PATH . 'includes/class-database.php';
        
        // Initialize gamification class
        require_once JPH_PLUGIN_PATH . 'includes/class-gamification.php';
    }
    
    /**
     * Enqueue AJAX nonce for frontend
     */
    public function enqueue_ajax_nonce() {
        // Enqueue on any page that might have the dashboard shortcode
        if (is_singular() || is_home() || is_front_page() || is_page('hub')) {
            // Ensure jQuery is enqueued first
            wp_enqueue_script('jquery');
            
            wp_localize_script('jquery', 'jph_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('jph_ajax_nonce')
            ));
        }
    }
    
    /**
     * Enqueue admin AJAX nonce
     */
    public function enqueue_admin_ajax_nonce() {
        // Ensure jQuery is enqueued first
        wp_enqueue_script('jquery');
        
        wp_localize_script('jquery', 'ajaxurl', admin_url('admin-ajax.php'));
        wp_localize_script('jquery', 'jph_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jph_ajax_nonce')
        ));
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        // Only enqueue on pages that might have our shortcode
        if (is_singular() || is_home() || is_front_page()) {
            wp_enqueue_script('jquery');
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Practice Hub', 'jazzedge-practice-hub'),
            __('Practice Hub', 'jazzedge-practice-hub'),
            'manage_options',
            'jazzedge-practice-hub',
            array($this, 'admin_page'),
            'dashicons-format-audio',
            30
        );
        
        add_submenu_page(
            'jazzedge-practice-hub',
            __('Dashboard', 'jazzedge-practice-hub'),
            __('Dashboard', 'jazzedge-practice-hub'),
            'manage_options',
            'jazzedge-practice-hub',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'jazzedge-practice-hub',
            __('Students', 'jazzedge-practice-hub'),
            __('Students', 'jazzedge-practice-hub'),
            'manage_options',
            'jph-students',
            array($this, 'students_page')
        );
        
        add_submenu_page(
            'jazzedge-practice-hub',
            __('Badges', 'jazzedge-practice-hub'),
            __('Badges', 'jazzedge-practice-hub'),
            'manage_options',
            'jph-badges',
            array($this, 'badges_page')
        );
        
        add_submenu_page(
            'jazzedge-practice-hub',
            __('Lesson Favorites', 'jazzedge-practice-hub'),
            __('Lesson Favorites', 'jazzedge-practice-hub'),
            'manage_options',
            'jph-lesson-favorites',
            array($this, 'lesson_favorites_page')
        );
        
        add_submenu_page(
            'jazzedge-practice-hub',
            __('Event Tracking', 'jazzedge-practice-hub'),
            __('Event Tracking', 'jazzedge-practice-hub'),
            'manage_options',
            'jph-fluent-crm-events',
            array($this, 'events_page')
        );
        
        add_submenu_page(
            'jazzedge-practice-hub',
            __('Documentation', 'jazzedge-practice-hub'),
            __('Documentation', 'jazzedge-practice-hub'),
            'manage_options',
            'jph-documentation',
            array($this, 'documentation_page')
        );
            
            add_submenu_page(
                'jazzedge-practice-hub',
                __('Settings', 'jazzedge-practice-hub'),
                __('Settings', 'jazzedge-practice-hub'),
                'manage_options',
                'jph-settings',
                array($this, 'settings_page')
            );
    }
    
    /**
     * Admin dashboard page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>🎹 JazzEdge Practice Hub Dashboard</h1>
            
            <div class="jph-status-cards">
                <div class="jph-status-card">
                    <h3>Plugin Status</h3>
                    <p>✅ Active</p>
                </div>
                
                <div class="jph-status-card">
                    <h3>Katahdin AI Hub</h3>
                    <p id="katahdin-status">🔄 Checking...</p>
                </div>
                
                <div class="jph-status-card">
                    <h3>Database</h3>
                    <p>📋 Coming Soon</p>
                </div>
                
                <div class="jph-status-card">
                    <h3>REST API</h3>
                    <p id="rest-api-status">🔗 Active</p>
                </div>
            </div>
            
            <div class="jph-test-section">
                <h2>🧪 Test Connections</h2>
                <div class="jph-test-buttons">
                    <button type="button" class="button button-primary" onclick="testRestAPI()">Test REST API</button>
                    <button type="button" class="button button-secondary" onclick="testKatahdinHub()">Test Katahdin AI Hub</button>
                    <button type="button" class="button button-secondary" onclick="testAllConnections()">Test All Connections</button>
                </div>
                <div id="jph-test-results" class="jph-test-results"></div>
            </div>
            
            <div class="jph-database-section">
                <h2>🗄️ Database Operations</h2>
                <div class="jph-database-buttons">
                    <button type="button" class="button button-primary" onclick="createTables()">Create Tables</button>
                    <button type="button" class="button button-secondary" onclick="checkTables()">Check Tables</button>
                    <button type="button" class="button button-secondary" onclick="showSchema()">Show Schema</button>
                    <button type="button" class="button button-secondary" onclick="runMigrations()">Run Migrations</button>
                </div>
                <div id="jph-database-results" class="jph-database-results"></div>
            </div>
            
            <div class="jph-badge-management-section">
                <h2>🏆 Badge Management</h2>
                <div class="jph-badge-buttons">
                    <button type="button" class="button button-primary" onclick="createDefaultBadges()">Create Default Badges</button>
                    <button type="button" class="button button-warning" onclick="resetBadgeCounts()">Reset Badge Counts</button>
                </div>
                <div id="jph-badge-results" class="jph-badge-results"></div>
            </div>
            
            <div class="jph-sections">
                <div class="jph-section">
                    <h2>📊 Statistics</h2>
                    <p><em>Coming Soon - Database integration needed</em></p>
                </div>
                
                <div class="jph-section">
                    <h2>🎯 Practice Items</h2>
                    <p><em>Coming Soon - CRUD operations needed</em></p>
                </div>
                
                <div class="jph-section">
                    <h2>🤖 AI Integration</h2>
                    <p id="ai-integration-status">🔄 Checking AI availability...</p>
                    <p><em>AI features require Katahdin AI Hub plugin to be installed and activated</em></p>
                </div>
                
                <div class="jph-section">
                    <h2>🎮 Gamification System</h2>
                    <div class="jph-gamification-info">
                        <h3>How It Works</h3>
                        <div class="jph-gamification-grid">
                            <div class="jph-gamification-item">
                                <h4>💎 XP System</h4>
                                <ul>
                                    <li><strong>Base XP:</strong> 1 XP per minute of practice (max 60 XP)</li>
                                    <li><strong>Sentiment Multiplier:</strong> 1-5 scale affects total XP</li>
                                    <li><strong>Improvement Bonus:</strong> 25% extra XP when improvement detected</li>
                                    <li><strong>Minimum:</strong> 1 XP for any session</li>
                                </ul>
                            </div>
                            <div class="jph-gamification-item">
                                <h4>📈 Level System</h4>
                                <ul>
                                    <li><strong>Formula:</strong> Level = floor(sqrt(XP / 100)) + 1</li>
                                    <li><strong>Level 1:</strong> 0-99 XP</li>
                                    <li><strong>Level 2:</strong> 100-399 XP</li>
                                    <li><strong>Level 3:</strong> 400-899 XP</li>
                                    <li><strong>Exponential growth</strong> for higher levels</li>
                                </ul>
                            </div>
                            <div class="jph-gamification-item">
                                <h4>🔥 Streak System</h4>
                                <ul>
                                    <li><strong>Daily Practice:</strong> Maintains current streak</li>
                                    <li><strong>Consecutive Days:</strong> Increases streak counter</li>
                                    <li><strong>Missed Day:</strong> Resets streak to 1</li>
                                    <li><strong>Longest Streak:</strong> Tracks personal best</li>
                                </ul>
                            </div>
                            <div class="jph-gamification-item">
                                <h4>🏆 Badges & Rewards</h4>
                                <ul>
                                    <li><strong>Hearts:</strong> 5 starting hearts (coming soon)</li>
                                    <li><strong>Gems:</strong> Currency system (coming soon)</li>
                                    <li><strong>Badges:</strong> Achievement system (coming soon)</li>
                                    <li><strong>Session Tracking:</strong> Total sessions & minutes</li>
                                </ul>
                            </div>
                        </div>
                <div class="jph-gamification-actions">
                    <button type="button" class="button button-primary" onclick="testGamification()">Test Gamification</button>
                    <button type="button" class="button button-secondary" onclick="showGamificationStats()">Show User Stats</button>
                    <button type="button" class="button button-secondary" onclick="simulatePractice()">Simulate Practice</button>
                    <button type="button" class="button button-secondary" onclick="backfillUserStats()">Backfill User Stats</button>
                    <button type="button" class="button button-secondary" onclick="checkAndAwardBadges()">Check & Award Badges</button>
                    <button type="button" class="button button-secondary" onclick="awardFirstStepsBadge()">Award First Steps Badge</button>
                </div>
                        <div id="jph-gamification-results" class="jph-gamification-results"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .jph-status-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .jph-status-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-status-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .jph-status-card p {
            margin: 0;
            font-size: 16px;
        }
        
        .jph-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .jph-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-section h2 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .jph-section p {
            margin: 0;
            color: #666;
            font-style: italic;
        }
        
        .jph-test-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-test-section h2 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .jph-test-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .jph-test-buttons .button {
            margin: 0;
        }
        
        .jph-test-results {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            min-height: 50px;
            font-family: monospace;
            font-size: 13px;
            white-space: pre-wrap;
            display: none;
        }
        
        .jph-test-results.show {
            display: block;
        }
        
        .jph-test-results.success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .jph-test-results.error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .jph-test-results.loading {
            background: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        
        .jph-database-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-database-section h2 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .jph-database-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .jph-database-buttons .button {
            margin: 0;
        }
        
        .jph-database-results {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            min-height: 50px;
            font-family: monospace;
            font-size: 13px;
            white-space: pre-wrap;
            display: none;
        }
        
        .jph-database-results.show {
            display: block;
        }
        
        .jph-database-results.success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .jph-database-results.error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .jph-database-results.loading {
            background: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        
        .jph-gamification-info {
            margin-top: 15px;
        }
        
        .jph-gamification-info h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 18px;
        }
        
        .jph-gamification-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .jph-gamification-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
        }
        
        .jph-gamification-item h4 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 16px;
        }
        
        .jph-gamification-item ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .jph-gamification-item li {
            margin-bottom: 5px;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .jph-gamification-item strong {
            color: #495057;
        }
        
        .jph-gamification-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .jph-gamification-actions .button {
            margin: 0;
        }
        
        .jph-gamification-results {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            min-height: 50px;
            font-family: monospace;
            font-size: 13px;
            white-space: pre-wrap;
            display: none;
        }
        
        .jph-gamification-results.show {
            display: block;
        }
        
        .jph-gamification-results.success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .jph-gamification-results.error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .jph-gamification-results.loading {
            background: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        
        /* Ensure admin menu icon displays properly */
        #toplevel_page_jazzedge-practice-hub .wp-menu-image:before {
            content: "\f127" !important;
            font-family: dashicons !important;
        }
        </style>
        
        <script>
        // Test Katahdin AI Hub connection
        document.addEventListener('DOMContentLoaded', function() {
            testKatahdinHub();
            
            // Display nonce in console for testing
            const nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
            // REST API endpoints available for testing
        });
        
        // Gamification testing functions
        function testGamification() {
            const resultsDiv = document.getElementById('jph-gamification-results');
            resultsDiv.className = 'jph-gamification-results show loading';
            resultsDiv.textContent = 'Testing gamification system...';
            
            fetch('<?php echo rest_url('jph/v1/gamification/test'); ?>', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                resultsDiv.className = 'jph-gamification-results show success';
                resultsDiv.textContent = 'Gamification System Test Results:\n\n' + JSON.stringify(data, null, 2);
            })
            .catch(error => {
                resultsDiv.className = 'jph-gamification-results show error';
                resultsDiv.textContent = 'Error testing gamification: ' + error.message;
            });
        }
        
        function showGamificationStats() {
            const resultsDiv = document.getElementById('jph-gamification-results');
            resultsDiv.className = 'jph-gamification-results show loading';
            resultsDiv.textContent = 'Loading user stats...';
            
            fetch('<?php echo rest_url('jph/v1/gamification/stats'); ?>', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                resultsDiv.className = 'jph-gamification-results show success';
                resultsDiv.textContent = 'User Gamification Stats:\n\n' + JSON.stringify(data, null, 2);
            })
            .catch(error => {
                resultsDiv.className = 'jph-gamification-results show error';
                resultsDiv.textContent = 'Error loading stats: ' + error.message;
            });
        }
        
        function simulatePractice() {
            const resultsDiv = document.getElementById('jph-gamification-results');
            resultsDiv.className = 'jph-gamification-results show loading';
            resultsDiv.textContent = 'Simulating practice session...';
            
            // Simulate a 30-minute practice session with good sentiment
            const practiceData = {
                duration_minutes: 30,
                sentiment_score: 4,
                improvement_detected: true,
                practice_item: 'Test Practice Session'
            };
            
            fetch('<?php echo rest_url('jph/v1/practice-sessions'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify(practiceData)
            })
            .then(response => response.json())
            .then(data => {
                resultsDiv.className = 'jph-gamification-results show success';
                resultsDiv.textContent = 'Practice Session Simulated:\n\n' + JSON.stringify(data, null, 2);
            })
            .catch(error => {
                resultsDiv.className = 'jph-gamification-results show error';
                resultsDiv.textContent = 'Error simulating practice: ' + error.message;
            });
        }
        
        function backfillUserStats() {
            const resultsDiv = document.getElementById('jph-gamification-results');
            resultsDiv.className = 'jph-gamification-results show loading';
            resultsDiv.textContent = 'Backfilling user stats from existing practice sessions...';
            
            fetch('<?php echo rest_url('jph/v1/backfill-stats'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                resultsDiv.className = 'jph-gamification-results show success';
                resultsDiv.textContent = 'User Stats Backfilled Successfully!\n\n' + 
                    'Sessions Processed: ' + data.stats.total_sessions_processed + '\n' +
                    'Total XP Added: ' + data.stats.total_xp_added + '\n' +
                    'Users Updated: ' + data.stats.users_updated + '\n\n' +
                    'User Details:\n' + JSON.stringify(data.stats.processed_users, null, 2);
            })
            .catch(error => {
                resultsDiv.className = 'jph-gamification-results show error';
                resultsDiv.textContent = 'Error backfilling user stats: ' + error.message;
            });
        }
        
        function checkAndAwardBadges() {
            const resultsDiv = document.getElementById('jph-gamification-results');
            resultsDiv.className = 'jph-gamification-results show loading';
            resultsDiv.textContent = 'Checking and awarding badges...';
            
            fetch('<?php echo rest_url('jph/v1/check-badges'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.count > 0) {
                        resultsDiv.className = 'jph-gamification-results show success';
                        resultsDiv.textContent = '🎉 ' + data.message + '\n\n' +
                            'Badges Awarded: ' + data.count + '\n' +
                            'New Badges:\n' + 
                            data.newly_awarded.map(badge => '- ' + badge.name + ' (' + badge.xp_reward + ' XP)').join('\n');
                    } else {
                        resultsDiv.className = 'jph-gamification-results show info';
                        resultsDiv.textContent = 'ℹ️ ' + data.message + '\n\nNo new badges to award at this time.';
                    }
                } else {
                    resultsDiv.className = 'jph-gamification-results show error';
                    resultsDiv.textContent = '❌ Error: ' + data.message;
                }
            })
            .catch(error => {
                resultsDiv.className = 'jph-gamification-results show error';
                resultsDiv.textContent = '❌ Error checking badges: ' + error.message;
            });
        }
        
        function awardFirstStepsBadge() {
            const resultsDiv = document.getElementById('jph-gamification-results');
            resultsDiv.className = 'jph-gamification-results show loading';
            resultsDiv.textContent = 'Awarding First Steps badge...';
            
            // Use AJAX to call a PHP function directly
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'jph_award_first_steps_badge',
                    nonce: '<?php echo wp_create_nonce('jph_award_badge'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        resultsDiv.className = 'jph-gamification-results show success';
                        resultsDiv.textContent = '🎉 ' + response.data.message + '\n\nBadge: First Steps\nXP Reward: 50 XP\nNew Total XP: ' + response.data.new_xp + '\n\nRefresh your dashboard to see the badge.';
                    } else {
                        resultsDiv.className = 'jph-gamification-results show error';
                        resultsDiv.textContent = '❌ Error awarding badge: ' + (response.data || 'Unknown error');
                    }
                },
                error: function(xhr, status, error) {
                    resultsDiv.className = 'jph-gamification-results show error';
                    resultsDiv.textContent = '❌ Error awarding badge: ' + error;
                }
            });
        }
        
        // Test REST API
        function testRestAPI() {
            const resultsDiv = document.getElementById('jph-test-results');
            resultsDiv.className = 'jph-test-results show loading';
            resultsDiv.textContent = 'Testing REST API...';
            
            fetch('<?php echo rest_url('jph/v1/test'); ?>')
                .then(response => response.json())
                .then(data => {
                    resultsDiv.className = 'jph-test-results show success';
                    resultsDiv.textContent = JSON.stringify(data, null, 2);
                    
                    // Update REST API status
                    const restStatus = document.getElementById('rest-api-status');
                    restStatus.innerHTML = '✅ Working';
                    restStatus.style.color = 'green';
                })
                .catch(error => {
                    resultsDiv.className = 'jph-test-results show error';
                    resultsDiv.textContent = 'REST API Test Failed:\n' + error.message;
                    
                    // Update REST API status
                    const restStatus = document.getElementById('rest-api-status');
                    restStatus.innerHTML = '❌ Error';
                    restStatus.style.color = 'red';
                });
        }
        
        // Test Katahdin AI Hub
        function testKatahdinHub() {
            const resultsDiv = document.getElementById('jph-test-results');
            const katahdinStatus = document.getElementById('katahdin-status');
            
            // First, let's check via REST API since that's more reliable
            fetch('<?php echo rest_url('jph/v1/test'); ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.katahdin_hub_available) {
                        katahdinStatus.innerHTML = '✅ Available';
                        katahdinStatus.style.color = 'green';
                        
                        if (resultsDiv.classList.contains('show')) {
                            resultsDiv.className = 'jph-test-results show success';
                            resultsDiv.textContent = 'Katahdin AI Hub Test Results:\n' + 
                                'Status: Available via REST API\n' +
                                'Function exists: ' + (typeof katahdin_ai_hub !== 'undefined' ? 'Yes' : 'No') + '\n' +
                                'Hub object: ' + (typeof katahdin_ai_hub !== 'undefined' && katahdin_ai_hub() ? 'Present' : 'Not accessible') + '\n' +
                                'REST API Response: ' + JSON.stringify(data, null, 2);
                        }
                    } else {
                        katahdinStatus.innerHTML = '❌ Not Available';
                        katahdinStatus.style.color = 'red';
                        
                        if (resultsDiv.classList.contains('show')) {
                            resultsDiv.className = 'jph-test-results show error';
                            resultsDiv.textContent = 'Katahdin AI Hub Test Failed:\n' + 
                                'REST API reports hub as not available\n' +
                                'Response: ' + JSON.stringify(data, null, 2);
                        }
                    }
                })
                .catch(error => {
                    katahdinStatus.innerHTML = '❌ Error';
                    katahdinStatus.style.color = 'red';
                    
                    if (resultsDiv.classList.contains('show')) {
                        resultsDiv.className = 'jph-test-results show error';
                        resultsDiv.textContent = 'Katahdin AI Hub Test Failed:\n' + error.message;
                    }
                });
        }
        
        // Test all connections
        function testAllConnections() {
            const resultsDiv = document.getElementById('jph-test-results');
            resultsDiv.className = 'jph-test-results show loading';
            resultsDiv.textContent = 'Testing all connections...';
            
            // Test REST API first
            fetch('<?php echo rest_url('jph/v1/test'); ?>')
                .then(response => response.json())
                .then(data => {
                    let results = '=== CONNECTION TEST RESULTS ===\n\n';
                    results += 'REST API: ✅ SUCCESS\n';
                    results += JSON.stringify(data, null, 2) + '\n\n';
                    
                    // Test Katahdin AI Hub based on REST API response
                    if (data.katahdin_hub_available) {
                        results += 'Katahdin AI Hub: ✅ AVAILABLE (via REST API)\n';
                        results += 'Function exists: ' + (typeof katahdin_ai_hub !== 'undefined' ? 'Yes' : 'No') + '\n';
                        results += 'Hub object accessible: ' + (typeof katahdin_ai_hub !== 'undefined' && katahdin_ai_hub() ? 'Yes' : 'No') + '\n';
                    } else {
                        results += 'Katahdin AI Hub: ❌ NOT AVAILABLE\n';
                        results += 'Function exists: ' + (typeof katahdin_ai_hub !== 'undefined' ? 'Yes' : 'No') + '\n';
                    }
                    
                    resultsDiv.className = 'jph-test-results show success';
                    resultsDiv.textContent = results;
                    
                    // Update status indicators based on REST API response
                    const restStatus = document.getElementById('rest-api-status');
                    restStatus.innerHTML = '✅ Working';
                    restStatus.style.color = 'green';
                    
                    const katahdinStatus = document.getElementById('katahdin-status');
                    const aiIntegrationStatus = document.getElementById('ai-integration-status');
                    const aiConfigStatus = document.getElementById('ai-config-status');
                    
                    if (data.katahdin_hub_available) {
                        katahdinStatus.innerHTML = '✅ Available';
                        katahdinStatus.style.color = 'green';
                        if (aiIntegrationStatus) {
                            aiIntegrationStatus.innerHTML = '✅ AI Integration Available';
                            aiIntegrationStatus.style.color = 'green';
                        }
                        if (aiConfigStatus) {
                            aiConfigStatus.innerHTML = '✅ AI Configuration Available';
                            aiConfigStatus.style.color = 'green';
                        }
                    } else {
                        katahdinStatus.innerHTML = '❌ Not Available (Required)';
                        katahdinStatus.style.color = 'red';
                        if (aiIntegrationStatus) {
                            aiIntegrationStatus.innerHTML = '❌ AI Integration Not Available (Install Katahdin AI Hub)';
                            aiIntegrationStatus.style.color = 'red';
                        }
                        if (aiConfigStatus) {
                            aiConfigStatus.innerHTML = '❌ AI Configuration Not Available (Install Katahdin AI Hub)';
                            aiConfigStatus.style.color = 'red';
                        }
                    }
                })
                .catch(error => {
                    resultsDiv.className = 'jph-test-results show error';
                    resultsDiv.textContent = 'Connection Test Failed:\n' + error.message;
                });
        }
        
        // Database operations using REST API
        function createTables() {
            const resultsDiv = document.getElementById('jph-database-results');
            resultsDiv.className = 'jph-database-results show loading';
            resultsDiv.textContent = 'Creating database tables...';
            
            fetch('<?php echo rest_url('jph/v1/database/create-tables'); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultsDiv.className = 'jph-database-results show success';
                    resultsDiv.textContent = '✅ Tables created successfully!\n\n' + JSON.stringify(data, null, 2);
                } else {
                    resultsDiv.className = 'jph-database-results show error';
                    resultsDiv.textContent = '❌ Error creating tables:\n' + JSON.stringify(data, null, 2);
                }
            })
            .catch(error => {
                resultsDiv.className = 'jph-database-results show error';
                resultsDiv.textContent = '❌ Network error:\n' + error.message;
            });
        }
        
        function checkTables() {
            const resultsDiv = document.getElementById('jph-database-results');
            resultsDiv.className = 'jph-database-results show loading';
            resultsDiv.textContent = 'Checking database tables...';
            
            fetch('<?php echo rest_url('jph/v1/database/check-tables'); ?>', {
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.tables_exist !== undefined) {
                    resultsDiv.className = 'jph-database-results show success';
                    resultsDiv.textContent = '✅ Table check results:\n\n' + JSON.stringify(data, null, 2);
                } else {
                    resultsDiv.className = 'jph-database-results show error';
                    resultsDiv.textContent = '❌ Error checking tables:\n' + JSON.stringify(data, null, 2);
                }
            })
            .catch(error => {
                resultsDiv.className = 'jph-database-results show error';
                resultsDiv.textContent = '❌ Network error:\n' + error.message;
            });
        }
        
        function showSchema() {
            const resultsDiv = document.getElementById('jph-database-results');
            resultsDiv.className = 'jph-database-results show loading';
            resultsDiv.textContent = 'Loading database schema...';
            
            fetch('<?php echo rest_url('jph/v1/database/schema'); ?>', {
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.schema) {
                    resultsDiv.className = 'jph-database-results show success';
                    resultsDiv.textContent = '📋 Database Schema:\n\n' + JSON.stringify(data, null, 2);
                } else {
                    resultsDiv.className = 'jph-database-results show error';
                    resultsDiv.textContent = '❌ Error loading schema:\n' + JSON.stringify(data, null, 2);
                }
            })
            .catch(error => {
                resultsDiv.className = 'jph-database-results show error';
                resultsDiv.textContent = '❌ Network error:\n' + error.message;
            });
        }
        
        function runMigrations() {
            const resultsDiv = document.getElementById('jph-database-results');
            resultsDiv.className = 'jph-database-results show loading';
            resultsDiv.textContent = 'Running database migrations...';
            
            fetch('<?php echo rest_url('jph/v1/run-migrations'); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultsDiv.className = 'jph-database-results show success';
                    resultsDiv.textContent = '✅ Migrations completed successfully!\n\n' + JSON.stringify(data, null, 2);
                } else {
                    resultsDiv.className = 'jph-database-results show error';
                    resultsDiv.textContent = '❌ Error running migrations:\n' + JSON.stringify(data, null, 2);
                }
            })
            .catch(error => {
                resultsDiv.className = 'jph-database-results show error';
                resultsDiv.textContent = '❌ Network error:\n' + error.message;
            });
        }
        
        function createDefaultBadges() {
            const resultsDiv = document.getElementById('jph-badge-results');
            resultsDiv.className = 'jph-badge-results show loading';
            resultsDiv.textContent = 'Creating default badges...';
            
            fetch('<?php echo rest_url('jph/v1/create-default-badges'); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultsDiv.className = 'jph-badge-results show success';
                    resultsDiv.textContent = '✅ Default badges created successfully!\n\n' + JSON.stringify(data, null, 2);
                } else {
                    resultsDiv.className = 'jph-badge-results show error';
                    resultsDiv.textContent = '❌ Error creating badges:\n' + JSON.stringify(data, null, 2);
                }
            })
            .catch(error => {
                resultsDiv.className = 'jph-badge-results show error';
                resultsDiv.textContent = '❌ Network error:\n' + error.message;
            });
        }
        
        function resetBadgeCounts() {
            const resultsDiv = document.getElementById('jph-badge-results');
            resultsDiv.className = 'jph-badge-results show loading';
            resultsDiv.textContent = 'Resetting badge counts...';
            
            fetch('<?php echo rest_url('jph/v1/reset-badge-counts'); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultsDiv.className = 'jph-badge-results show success';
                    resultsDiv.textContent = '✅ Badge counts reset successfully!\n\n' + JSON.stringify(data, null, 2);
                } else {
                    resultsDiv.className = 'jph-badge-results show error';
                    resultsDiv.textContent = '❌ Error resetting badge counts:\n' + JSON.stringify(data, null, 2);
                }
            })
            .catch(error => {
                resultsDiv.className = 'jph-badge-results show error';
                resultsDiv.textContent = '❌ Network error:\n' + error.message;
            });
        }
        </script>
        <?php
    }
    
    /**
     * Students page
     */
    public function students_page() {
        ?>
        <div class="wrap">
            <h1>👥 Practice Hub Students</h1>
            
            <div class="jph-students-overview">
                <div class="jph-students-stats">
                    <div class="jph-stat-card">
                        <h3>Total Students</h3>
                        <p id="total-students">Loading...</p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Active This Week</h3>
                        <p id="active-students">Loading...</p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Total Practice Hours</h3>
                        <p id="total-hours">Loading...</p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Average Level</h3>
                        <p id="average-level">Loading...</p>
                    </div>
                </div>
            </div>
            
            <div class="jph-students-filters">
                <div class="jph-filter-group">
                    <label for="student-search">Search Students:</label>
                    <input type="text" id="student-search" placeholder="Search by name or email...">
                </div>
                <div class="jph-filter-group">
                    <label for="level-filter">Filter by Level:</label>
                    <select id="level-filter">
                        <option value="">All Levels</option>
                        <option value="1">Level 1</option>
                        <option value="2">Level 2</option>
                        <option value="3">Level 3+</option>
                    </select>
                </div>
                <div class="jph-filter-group">
                    <label for="activity-filter">Activity Status:</label>
                    <select id="activity-filter">
                        <option value="">All Students</option>
                        <option value="active">Active (7 days)</option>
                        <option value="inactive">Inactive (30+ days)</option>
                    </select>
                </div>
                <div class="jph-filter-group">
                    <button type="button" class="button button-primary" id="search-students-btn">🔍 Search</button>
                    <button type="button" class="button button-secondary" id="clear-filters-btn">Clear Filters</button>
                </div>
            </div>
            
            <div class="jph-students-table-container">
                <table class="jph-students-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Level</th>
                            <th>XP</th>
                            <th>Current Streak</th>
                            <th>Longest Streak</th>
                            <th>Badges</th>
                            <th>Last Practice</th>
                            <th>Total Sessions</th>
                            <th>Total Hours</th>
                            <th>Gems</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="students-table-body">
                        <tr>
                            <td colspan="11" class="jph-loading">Loading students...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="jph-students-actions">
                <button type="button" class="button button-primary" onclick="refreshStudents()">Refresh Data</button>
                <button type="button" class="button button-secondary" onclick="exportStudents()">Export CSV</button>
                <button type="button" class="button button-secondary" onclick="showStudentAnalytics()">View Analytics</button>
            </div>
        </div>
        
        <!-- View Student Modal -->
        <div id="jph-view-student-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>👤 Student Details</h2>
                    <button class="jph-modal-close" onclick="closeViewStudentModal()">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="jph-modal-body" id="jph-view-student-content">
                    <div class="jph-loading">Loading student details...</div>
                </div>
            </div>
        </div>
        
        <!-- Badges Page -->
        <div id="jph-badges-page" style="display: none;">
            <div class="wrap">
                <h1>🏆 Badge Management</h1>
                
                <div class="jph-badges-overview">
                    <div class="jph-badges-stats">
                        <div class="jph-stat-card">
                            <h3>Total Badges</h3>
                            <p id="total-badges">Loading...</p>
                        </div>
                        <div class="jph-stat-card">
                            <h3>Active Badges</h3>
                            <p id="active-badges">Loading...</p>
                        </div>
                        <div class="jph-stat-card">
                            <h3>Categories</h3>
                            <p id="badge-categories">Loading...</p>
                        </div>
                        <div class="jph-stat-card">
                            <h3>Total Awards</h3>
                            <p id="total-awards">Loading...</p>
                        </div>
                    </div>
                </div>
                
                <div class="jph-badges-actions">
                    <button type="button" class="button button-primary" id="add-badge-btn">➕ Add New Badge</button>
                    <button type="button" class="button button-secondary" id="create-default-badges-btn">🏆 Create Default Badges</button>
                    <button type="button" class="button button-secondary" id="refresh-badges-btn">🔄 Refresh</button>
                    <button type="button" class="button button-secondary" id="test-api-btn">🧪 Test API</button>
                </div>
                
                <div class="jph-badges-table-container">
                    <table class="jph-badges-table">
                        <thead>
                            <tr>
                                <th>Badge</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>XP Reward</th>
                                <th>Status</th>
                                <th>Students</th>
                                <th>FluentCRM</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="badges-table-body">
                            <tr>
                                <td colspan="8" class="jph-loading">Loading badges...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Add Badge Modal -->
        <div id="jph-add-badge-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>🏆 Add New Badge</h2>
                    <button class="jph-modal-close" onclick="closeAddBadgeModal()">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="jph-modal-body">
                    <form id="jph-add-badge-form" action="#" method="post" enctype="multipart/form-data">
                        
                        <div class="jph-form-group">
                            <label for="badge-name">Badge Name:</label>
                            <input type="text" id="badge-name" name="name" required placeholder="e.g., First Session">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-description">Description:</label>
                            <textarea id="badge-description" name="description" rows="3" placeholder="Describe what this badge represents..."></textarea>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-image">Badge Image:</label>
                            <input type="file" id="badge-image" name="badge_image" accept="image/*">
                            <small>Recommended: 64x64px PNG with transparent background</small>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-category">Category:</label>
                            <select id="badge-category" name="category">
                                <option value="achievement">Achievement</option>
                                <option value="milestone">Milestone</option>
                                <option value="special">Special</option>
                                <option value="streak">Streak</option>
                                <option value="level">Level</option>
                            </select>
                        </div>
                        
                        
                        <div class="jph-form-group">
                            <label for="badge-xp-reward">⭐ XP Reward</label>
                            <input type="number" id="badge-xp-reward" name="xp_reward" min="0" value="0">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-gem-reward">💎 Gem Reward</label>
                            <input type="number" id="badge-gem-reward" name="gem_reward" min="0" value="0">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-criteria-type">How to Earn</label>
                            <select id="badge-criteria-type" name="criteria_type">
                                <option value="manual">Manual Award</option>
                                <option value="total_xp">Total XP ≥ value</option>
                                <option value="level_reached">Level Reached ≥ value</option>
                                <option value="practice_sessions">Practice Sessions ≥ value</option>
                                <option value="streak">Streak Days ≥ value</option>
                                <option value="total_time">Single Session Minutes ≥ value</option>
                                <option value="improvement_count">Improvements Reported ≥ value</option>
                                <option value="weekly_goal">Practice Days This Week ≥ value</option>
                                <option value="monthly_goal">Practice Days This Month ≥ value</option>
                                <option value="weekend_warrior">Weekend Sessions ≥ value</option>
                            </select>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-criteria-value">Criteria Value</label>
                            <input type="number" id="badge-criteria-value" name="criteria_value" min="0" value="0" placeholder="0">
                            <small>Depends on criteria type (XP, sessions, minutes, etc.)</small>
                        </div>
                        

                        <div class="jph-form-group">
                            <label for="badge-fluent-event-enabled" class="jph-checkbox-label">
                                <input type="checkbox" id="badge-fluent-event-enabled" name="fluent_event_enabled" value="1" onclick="toggleFluentEventFields('badge-fluent-event-enabled')">
                                Enable FluentCRM Event Tracking
                            </label>
                            <small>Track badge achievement in FluentCRM for automation triggers</small>
                            
                            <div class="jph-form-group fluent-event-fields" style="display:none;">
                                <label for="badge-fluent-event-key">Event Key</label>
                                <input type="text" id="badge-fluent-event-key" name="fluent_event_key" placeholder="jph_badge_key">
                                <small>Optional custom event key</small>
                            </div>
                            
                            <div class="jph-form-group fluent-event-fields" style="display:none;">
                                <label for="badge-fluent-event-title">Event Title</label>
                                <input type="text" id="badge-fluent-event-title" name="fluent_event_title" placeholder="Badge achievement">
                                <small>Optional custom event title</small>
                            </div>
                        </div>
                        
                        <div class="jph-form-actions">
                            <button type="button" class="button button-primary" onclick="addBadge()">🏆 Create Badge</button>
                            <button type="button" class="button button-secondary" onclick="closeAddBadgeModal()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Edit Student Modal -->
        <div id="jph-edit-student-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>✏️ Edit Student Stats</h2>
                    <button class="jph-modal-close" onclick="closeEditStudentModal()">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="jph-modal-body" id="jph-edit-student-content">
                    <div class="jph-loading">Loading student data...</div>
                </div>
            </div>
        </div>
        
        <style>
        .jph-students-overview {
            margin: 20px 0;
        }
        
        .jph-students-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .jph-stat-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-stat-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jph-stat-card p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .jph-students-filters {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: end;
        }
        
        .jph-filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }
        
        .jph-filter-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .jph-filter-group input,
        .jph-filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .jph-filter-group button {
            margin-top: 20px;
            margin-right: 10px;
        }
        
        /* Badges Page Styles */
        .jph-badges-overview {
            margin: 20px 0;
        }
        
        .jph-badges-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .jph-badges-actions {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        
        .jph-badges-table-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .jph-badges-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .jph-badges-table th,
        .jph-badges-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .jph-badges-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .jph-badge-image {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #ddd;
        }
        
        .jph-badge-rarity {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .jph-badge-rarity.common {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .jph-badge-rarity.rare {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .jph-badge-rarity.epic {
            background: #f8d7da;
            color: #721c24;
        }
        
        .jph-badge-rarity.legendary {
            background: #fff3cd;
            color: #856404;
        }
        
        .jph-badge-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .jph-badge-status.active {
            background: #d4edda;
            color: #155724;
        }
        
        .jph-badge-status.inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .jph-form-group {
            margin-bottom: 20px;
        }
        
        .jph-form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .jph-form-group input,
        .jph-form-group select,
        .jph-form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .jph-form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }
        
        .jph-form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .jph-students-table-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .jph-students-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .jph-students-table th {
            background: #f8f9fa;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        .jph-students-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .jph-students-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .jph-loading {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
        }
        
        .jph-student-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .jph-student-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0073aa;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .jph-student-details h4 {
            margin: 0;
            font-size: 14px;
            color: #333;
        }
        
        .jph-student-details p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }
        
        .jph-level-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        
        .jph-level-1 { background: #28a745; }
        .jph-level-2 { background: #007bff; }
        .jph-level-3 { background: #6f42c1; }
        .jph-level-4 { background: #fd7e14; }
        .jph-level-5 { background: #dc3545; }
        
        .jph-xp-display {
            font-weight: 600;
            color: #0073aa;
        }
        
        .jph-streak-display {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .jph-streak-fire {
            color: #ff6b35;
            font-size: 16px;
        }
        
        .jph-badges-display {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .jph-badge-icon {
            font-size: 16px;
        }
        
        .jph-last-practice {
            font-size: 12px;
            color: #666;
        }
        
        .jph-sessions-count {
            font-weight: 600;
            color: #28a745;
        }
        
        .jph-hours-display {
            font-weight: 600;
            color: #6f42c1;
        }
        
        .jph-student-actions {
            display: flex;
            gap: 5px;
        }
        
        .jph-student-actions .button {
            padding: 4px 8px;
            font-size: 12px;
            height: auto;
            line-height: 1.2;
        }
        
        .jph-students-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .jph-students-actions .button {
            margin: 0;
        }
        
        /* Modal Styles */
        .jph-modal {
            position: fixed;
            z-index: 999998;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .jph-modal-content {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .jph-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
            position: relative;
        }
        
        .jph-modal-header h2 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        
        .jph-modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .jph-modal-close:hover {
            background: #f5f5f5;
            border-color: #ccc;
            transform: scale(1.1);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .jph-modal-close:active {
            transform: scale(0.95);
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }
        
        .jph-modal-close svg {
            width: 20px;
            height: 20px;
            fill: #666;
            transition: fill 0.2s ease;
        }
        
        .jph-modal-close:hover svg {
            fill: #333;
        }
        
        .jph-modal-body {
            padding: 20px;
        }
        
        /* Shield Modal - Clean Design */
        .shield-modal-clean {
            max-width: 900px;
            width: 90%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
        }
        
        .shield-header-clean {
            background: #f8f9fa;
            padding: 30px 30px 20px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }
        
        .shield-icon-clean {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .shield-header-clean h2 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        
        .shield-header-clean p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .shield-content-clean {
            padding: 30px;
        }
        
        .shield-explanation-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin: 20px 0;
        }
        
        .shield-explanation-item {
            background: #f8fffe;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #00A8A8;
        }
        
        .shield-explanation-item h4 {
            margin: 0 0 10px 0;
            color: #004555;
            font-size: 18px;
            font-weight: 600;
        }
        
        .shield-explanation-item p {
            margin: 0 0 15px 0;
            color: #555;
            line-height: 1.5;
        }
        
        .shield-explanation-item ul {
            margin: 0;
            padding-left: 20px;
            list-style-position: outside;
            color: #666;
        }
        
        .shield-explanation-item li {
            margin-bottom: 5px;
            line-height: 1.4;
        }
        
        .shield-footer-clean {
            padding: 20px 30px 30px;
            text-align: center;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .btn-shield-got-it {
            background: linear-gradient(135deg, #007cba, #005a87);
            color: white;
            border: none;
            padding: 15px 35px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 124, 186, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-shield-got-it:hover {
            background: linear-gradient(135deg, #005a87, #004466);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 124, 186, 0.4);
        }
        
        .btn-shield-got-it:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 124, 186, 0.3);
        }
        
        @media (max-width: 768px) {
            .shield-modal-clean {
                width: 95%;
                margin: 20px;
            }
            
            .shield-explanation-grid {
                grid-template-columns: 1fr;
            }
            
            .shield-content-clean {
                padding: 20px;
            }
            
            .shield-header-clean {
                padding: 20px 20px 15px;
            }
        }
            margin-bottom: 25px;
        }
        
        .jph-student-detail-section h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 16px;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 5px;
        }
        
        .jph-student-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .jph-student-detail-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #0073aa;
        }
        
        .jph-student-detail-item label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jph-student-detail-item .value {
            font-size: 16px;
            color: #0073aa;
            font-weight: bold;
        }
        
        .jph-edit-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .jph-edit-form-group {
            display: flex;
            flex-direction: column;
        }
        
        .jph-edit-form-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .jph-edit-form-group input,
        .jph-edit-form-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .jph-edit-form-group input:focus,
        .jph-edit-form-group select:focus {
            outline: none;
            border-color: #0073aa;
            box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.2);
        }
        
        .jph-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .jph-modal-actions .button {
            margin: 0;
        }
        
        /* Edit modal button styling */
        .jph-modal button[type="submit"],
        .jph-modal .button-primary {
            background: linear-gradient(135deg, #F04E23, #e0451f);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(240, 78, 35, 0.3);
            min-width: 120px;
        }
        
        .jph-modal button[type="submit"]:hover,
        .jph-modal .button-primary:hover {
            background: linear-gradient(135deg, #e0451f, #d63e1c);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(240, 78, 35, 0.4);
        }
        
        .jph-modal .button-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            min-width: 120px;
            margin-right: 10px;
        }
        
        .jph-modal .button-secondary:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            transform: translateY(-1px);
        }
        
        /* Responsive modal adjustments */
        @media (max-width: 768px) {
            .jph-modal {
                padding: 10px;
            }
            .jph-modal-content {
                max-height: 90vh;
            }
        }
        
        /* WordPress admin bar adjustments */
        .admin-bar .jph-modal {
            padding: 20px;
        }
        
        @media (max-width: 782px) {
            .admin-bar .jph-modal {
                padding: 10px;
            }
        }
        </style>
        
        <script>
        // Load students data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadStudentsData();
            loadStudentsStats();
            
            // Add event listeners for search and filter buttons
            const searchBtn = document.getElementById('search-students-btn');
            if (searchBtn) {
                searchBtn.addEventListener('click', function() {
                const filters = {
                    search: document.getElementById('student-search').value.trim(),
                    level: document.getElementById('level-filter').value,
                    activity: document.getElementById('activity-filter').value
                };
                loadStudentsData(filters);
            });
            }
            
            // Clear filters button
            const clearBtn = document.getElementById('clear-filters-btn');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                document.getElementById('student-search').value = '';
                document.getElementById('level-filter').value = '';
                document.getElementById('activity-filter').value = '';
                loadStudentsData(); // Load all students
            });
            }
            
            // Allow Enter key to trigger search
            const searchInput = document.getElementById('student-search');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                        const searchBtn = document.getElementById('search-students-btn');
                        if (searchBtn) {
                            searchBtn.click();
                        }
                }
            });
            }
        });
        
        // Load students statistics
        function loadStudentsStats() {
            fetch('<?php echo rest_url('jph/v1/students/stats'); ?>', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('total-students').textContent = data.stats.total_students;
                    document.getElementById('active-students').textContent = data.stats.active_students;
                    document.getElementById('total-hours').textContent = data.stats.total_hours;
                    document.getElementById('average-level').textContent = data.stats.average_level;
                }
            })
            .catch(error => {
                console.error('Error loading stats:', error);
            });
        }
        
        // Load students table data
        function loadStudentsData(filters = {}) {
            const tbody = document.getElementById('students-table-body');
            tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">Loading students...</td></tr>';
            
            // Build query string from filters
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.level) params.append('level', filters.level);
            if (filters.activity) params.append('activity', filters.activity);
            
            const url = '<?php echo rest_url('jph/v1/students'); ?>' + (params.toString() ? '?' + params.toString() : '');
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderStudentsTable(data.students);
                } else {
                    tbody.innerHTML = '<tr><td colspan="10" class="jph-loading">Error loading students</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading students:', error);
                tbody.innerHTML = '<tr><td colspan="10" class="jph-loading">Error loading students</td></tr>';
            });
        }
        
        // Render students table
        function renderStudentsTable(students) {
            const tbody = document.getElementById('students-table-body');
            
            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="jph-loading">No students found</td></tr>';
                return;
            }
            
            tbody.innerHTML = students.map(student => `
                <tr>
                    <td>
                        <div class="jph-student-info">
                            <div class="jph-student-avatar">${student.display_name.charAt(0).toUpperCase()}</div>
                            <div class="jph-student-details">
                                <h4>${student.display_name}</h4>
                                <p>${student.user_email}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="jph-level-badge jph-level-${student.stats.current_level}">Level ${student.stats.current_level}</span></td>
                    <td><span class="jph-xp-display">${student.stats.total_xp} XP</span></td>
                    <td>
                        <div class="jph-streak-display">
                            <span class="jph-streak-fire">🔥</span>
                            <span>${student.stats.current_streak} days</span>
                        </div>
                    </td>
                    <td>
                        <div class="jph-streak-display">
                            <span class="jph-streak-fire">🏆</span>
                            <span>${student.stats.longest_streak} days</span>
                        </div>
                    </td>
                    <td>
                        <div class="jph-badges-display">
                            <span class="jph-badge-icon">🏅</span>
                            <span>${student.stats.badges_earned}</span>
                        </div>
                    </td>
                    <td><span class="jph-last-practice">${formatDate(student.stats.last_practice_date)}</span></td>
                    <td><span class="jph-sessions-count">${student.stats.total_sessions}</span></td>
                    <td><span class="jph-hours-display">${Math.round(student.stats.total_minutes / 60 * 10) / 10}h</span></td>
                    <td>
                        <div class="jph-gems-display">
                            <span class="jph-gem-icon">💎</span>
                            <span>${student.stats.gems_balance}</span>
                        </div>
                    </td>
                    <td>
                        <div class="jph-student-actions">
                            <button type="button" class="button button-small" onclick="viewStudentDetails(${student.ID})">View</button>
                            <button type="button" class="button button-small" onclick="editStudentStats(${student.ID})">Edit</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
        
        // Format date for display
        function formatDate(dateString) {
            if (!dateString) return 'Never';
            
            // Handle date-only strings (YYYY-MM-DD) by adding time
            let dateToCheck = dateString;
            if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
                dateToCheck = dateString + 'T00:00:00';
            }
            
            const date = new Date(dateToCheck);
            const now = new Date();
            
            // Check if it's today
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const checkDate = new Date(date);
            checkDate.setHours(0, 0, 0, 0);
            
            if (checkDate.getTime() === today.getTime()) {
                return 'Today';
            }
            
            // Check if it's yesterday
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            
            if (checkDate.getTime() === yesterday.getTime()) {
                return 'Yesterday';
            }
            
            // Calculate days difference
            const diffTime = Math.abs(now - date);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays < 7) return `${diffDays} days ago`;
            if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
            return date.toLocaleDateString();
        }
        
        // Student actions
        function viewStudentDetails(userId) {
            const modal = document.getElementById('jph-view-student-modal');
            const content = document.getElementById('jph-view-student-content');
            
            modal.style.display = 'flex';
            content.innerHTML = '<div class="jph-loading">Loading student details...</div>';
            
            // Load student details
            fetch(`<?php echo rest_url('jph/v1/students/'); ?>${userId}`, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderStudentDetails(data.student);
                } else {
                    content.innerHTML = '<div class="jph-loading">Error loading student details</div>';
                }
            })
            .catch(error => {
                console.error('Error loading student details:', error);
                content.innerHTML = '<div class="jph-loading">Error loading student details</div>';
            });
        }
        
        function editStudentStats(userId) {
            const modal = document.getElementById('jph-edit-student-modal');
            const content = document.getElementById('jph-edit-student-content');
            
            modal.style.display = 'flex';
            content.innerHTML = '<div class="jph-loading">Loading student data...</div>';
            
            // Load student data for editing
            fetch(`<?php echo rest_url('jph/v1/students/'); ?>${userId}`, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderEditStudentForm(data.student);
                } else {
                    content.innerHTML = '<div class="jph-loading">Error loading student data</div>';
                }
            })
            .catch(error => {
                console.error('Error loading student data:', error);
                content.innerHTML = '<div class="jph-loading">Error loading student data</div>';
            });
        }
        
        // Render student details in view modal
        function renderStudentDetails(student) {
            const content = document.getElementById('jph-view-student-content');
            
            content.innerHTML = `
                <div class="jph-student-detail-section">
                    <h3>👤 Student Information</h3>
                    <div class="jph-student-detail-grid">
                        <div class="jph-student-detail-item">
                            <label>Name</label>
                            <div class="value">${student.display_name}</div>
                        </div>
                        <div class="jph-student-detail-item">
                            <label>Email</label>
                            <div class="value">${student.user_email}</div>
                        </div>
                        <div class="jph-student-detail-item">
                            <label>User ID</label>
                            <div class="value">${student.ID}</div>
                        </div>
                    </div>
                </div>
                
                <div class="jph-student-detail-section">
                    <h3>🎮 Gamification Stats</h3>
                    <div class="jph-student-detail-grid">
                        <div class="jph-student-detail-item">
                            <label>Current Level</label>
                            <div class="value">Level ${student.stats.current_level}</div>
                        </div>
                        <div class="jph-student-detail-item">
                            <label>Total XP</label>
                            <div class="value">${student.stats.total_xp} XP</div>
                        </div>
                        <div class="jph-student-detail-item">
                            <label>Current Streak</label>
                            <div class="value">${student.stats.current_streak} days</div>
                        </div>
                        <div class="jph-student-detail-item">
                            <label>Longest Streak</label>
                            <div class="value">${student.stats.longest_streak} days</div>
                        </div>
                    </div>
                </div>
                
                <div class="jph-student-detail-section">
                    <h3>📊 Practice Statistics</h3>
                    <div class="jph-student-detail-grid">
                        <div class="jph-student-detail-item">
                            <label>Total Sessions</label>
                            <div class="value">${student.stats.total_sessions}</div>
                        </div>
                        <div class="jph-student-detail-item">
                            <label>Total Minutes</label>
                            <div class="value">${student.stats.total_minutes} min</div>
                        </div>
                        <div class="jph-student-detail-item">
                            <label>Total Hours</label>
                            <div class="value">${Math.round(student.stats.total_minutes / 60 * 10) / 10}h</div>
                        </div>
                        <div class="jph-student-detail-item">
                            <label>Last Practice</label>
                            <div class="value">${formatDate(student.stats.last_practice_date)}</div>
                        </div>
                    </div>
                </div>
                
                <div class="jph-student-detail-section">
                    <h3>🏆 Rewards & Badges</h3>
                    <div class="jph-student-detail-grid">
                        <div class="jph-student-detail-item">
                            <label>Hearts</label>
                            <div class="value">${student.stats.hearts_count}</div>
                        </div>
                        <div class="jph-student-detail-item">
                            <label>Badges Earned</label>
                            <div class="value">${student.stats.badges_earned}</div>
                        </div>
                        <div class="jph-student-detail-item">
                            <label>Gems Balance</label>
                            <div class="value">${student.stats.gems_balance} 💎</div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Render edit student form
        function renderEditStudentForm(student) {
            const content = document.getElementById('jph-edit-student-content');
            
            content.innerHTML = `
                <form id="jph-edit-student-form" onsubmit="saveStudentStats(event, ${student.ID})">
                    <div class="jph-edit-form">
                        <div class="jph-edit-form-group">
                            <label>Total XP</label>
                            <input type="number" name="total_xp" value="${student.stats.total_xp}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Current Level</label>
                            <input type="number" name="current_level" value="${student.stats.current_level}" min="1" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Current Streak</label>
                            <input type="number" name="current_streak" value="${student.stats.current_streak}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Longest Streak</label>
                            <input type="number" name="longest_streak" value="${student.stats.longest_streak}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Total Sessions</label>
                            <input type="number" name="total_sessions" value="${student.stats.total_sessions}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Total Minutes</label>
                            <input type="number" name="total_minutes" value="${student.stats.total_minutes}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Hearts</label>
                            <input type="number" name="hearts_count" value="${student.stats.hearts_count}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Gems</label>
                            <input type="number" name="gems_balance" value="${student.stats.gems_balance}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Badges Earned</label>
                            <input type="number" name="badges_earned" value="${student.stats.badges_earned}" min="0" required>
                        </div>
                    </div>
                    
                    <div class="jph-modal-actions">
                        <button type="button" class="button button-secondary" onclick="closeEditStudentModal()">Cancel</button>
                        <button type="submit" class="button button-primary">Save Changes</button>
                    </div>
                </form>
            `;
        }
        
        // Save student stats
        function saveStudentStats(event, userId) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // Convert string values to numbers
            Object.keys(data).forEach(key => {
                data[key] = parseInt(data[key]);
            });
            
            fetch(`<?php echo rest_url('jph/v1/students/'); ?>${userId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    closeEditStudentModal();
                    loadStudentsData(); // Refresh the table
                    showToast('Student stats updated successfully!', 'success');
                } else {
                    showToast('Error updating student stats: ' + (result.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error updating student stats:', error);
                showToast('Error updating student stats', 'error');
            });
        }
        
        // Modal close functions
        function closeViewStudentModal() {
            document.getElementById('jph-view-student-modal').style.display = 'none';
        }
        
        function closeEditStudentModal() {
            document.getElementById('jph-edit-student-modal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const viewModal = document.getElementById('jph-view-student-modal');
            const editModal = document.getElementById('jph-edit-student-modal');
            
            if (event.target === viewModal) {
                closeViewStudentModal();
            }
            if (event.target === editModal) {
                closeEditStudentModal();
            }
        }
        
        function refreshStudents() {
            loadStudentsData();
            loadStudentsStats();
        }
        
        function exportStudents() {
            showToast('Export students to CSV - Coming Soon', 'info');
        }
        
        function showStudentAnalytics() {
            showToast('Student analytics - Coming Soon', 'info');
        }
        </script>
        <?php
    }
    
    /**
     * Badges page JavaScript
     */
    private function badges_page_js() {
        ?>
        <script>
        // Toast notification system
        function showToast(message, type = 'info', duration = 4000) {
            // Remove existing toasts
            const existingToasts = document.querySelectorAll('.jph-toast');
            existingToasts.forEach(toast => toast.remove());
            
            const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
            
            const toast = document.createElement('div');
            toast.className = `jph-toast ${type}`;
            toast.innerHTML = `
                <div class="toast-content">
                    <span class="toast-icon">${icon}</span>
                    <span class="toast-message">${message}</span>
                    <span class="toast-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);
            
            // Auto-hide
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, duration);
            
            // Manual close
            const closeBtn = toast.querySelector('.toast-close');
            closeBtn.addEventListener('click', function() {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            });
        }
        
        // Load badges data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadBadgesData();
            loadBadgesStats();
            
            // Add event listeners
            const addBtn = document.getElementById('add-badge-btn');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    const modal = document.getElementById('jph-add-badge-modal');
                    if (modal) {
                        modal.style.display = 'flex';
                        
                        // Ensure modal is properly positioned
                        setTimeout(function() {
                            modal.scrollTop = 0;
                            modal.scrollLeft = 0;
                        }, 10);
                    }
                });
            }
            
            // Second instance of add button
            const addBtn2 = document.getElementById('add-badge-btn-2');
            if (addBtn2) {
                addBtn2.addEventListener('click', function() {
                    const modal = document.getElementById('jph-add-badge-modal');
                    if (modal) {
                        modal.style.display = 'flex';
                        
                        // Ensure modal is properly positioned
                        setTimeout(function() {
                            modal.scrollTop = 0;
                            modal.scrollLeft = 0;
                        }, 10);
                    }
                });
            }
            
            const refreshBtn = document.getElementById('refresh-badges-btn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                loadBadgesData();
                loadBadgesStats();
            });
            }
            
            // Second instance of refresh button
            const refreshBtn2 = document.getElementById('refresh-badges-btn-2');
            if (refreshBtn2) {
                refreshBtn2.addEventListener('click', function() {
                    loadBadgesData();
                    loadBadgesStats();
                });
            }
            
            // Sync all badge counts button
            const syncAllBadgeCountsBtn = document.getElementById('sync-all-badge-counts-btn');
            if (syncAllBadgeCountsBtn) {
                syncAllBadgeCountsBtn.addEventListener('click', function() {
                    syncAllBadgeCounts();
                });
            }
            
            
            // Create default badges button
            const createDefaultBtn = document.getElementById('create-default-badges-btn');
            if (createDefaultBtn) {
                createDefaultBtn.addEventListener('click', function() {
                createDefaultBadges();
            });
            }
            
            // Test API button
            const testApiBtn = document.getElementById('test-api-btn');
            if (testApiBtn) {
                testApiBtn.addEventListener('click', function() {
                    testBadgeUpdateAPI();
                });
            }
            
            // Test API button (second instance)
            
            // Reorder badges button
            const reorderBadgesBtn = document.getElementById('reorder-badges-btn');
            if (reorderBadgesBtn) {
                reorderBadgesBtn.addEventListener('click', function() {
                    openReorderModal();
                });
            }
            
            // Database status button
            const databaseStatusBtn = document.getElementById('database-status-btn');
            if (databaseStatusBtn) {
                databaseStatusBtn.addEventListener('click', function() {
                    showDatabaseStatus();
                });
            }
            
            // Delete all badges button
            const deleteAllBadgesBtn = document.getElementById('delete-all-badges-btn');
            if (deleteAllBadgesBtn) {
                deleteAllBadgesBtn.addEventListener('click', function() {
                    openDeleteAllModal();
                });
            }
            
            
            // Form submission handlers removed - using onclick handlers instead
        });
        
        // Delete All Badges Functions
        function openDeleteAllModal() {
            const modalHtml = `
                <div id="jph-delete-all-modal" class="jph-modal" style="display: flex;">
                    <div class="jph-modal-content">
                        <div class="jph-modal-header">
                            <h2>🗑️ Delete All Badges</h2>
                            <button class="jph-modal-close" onclick="closeDeleteAllModal()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="jph-modal-body">
                            <p><strong>⚠️ WARNING:</strong> This will permanently delete ALL badges from the system!</p>
                            <p>This includes:</p>
                            <ul>
                                <li>All badge definitions</li>
                                <li>All user badge rewards</li>
                                <li>All badge statistics</li>
                            </ul>
                            <p><strong>This action cannot be undone!</strong></p>
                            <label>
                                <input type="checkbox" id="confirm-delete-checkbox"> 
                                I understand this will delete all badges permanently
                            </label>
                        </div>
                        <div class="jph-modal-actions">
                            <button type="button" class="button button-secondary" onclick="closeDeleteAllModal()">Cancel</button>
                            <button type="button" class="button button-primary" id="confirm-delete-btn" onclick="deleteAllBadges()" disabled style="background: #dc3545; border-color: #dc3545;">Delete All Badges</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            document.getElementById('confirm-delete-checkbox').addEventListener('change', function() {
                document.getElementById('confirm-delete-btn').disabled = !this.checked;
            });
        }
        
        function closeDeleteAllModal() {
            const modal = document.getElementById('jph-delete-all-modal');
            if (modal) modal.remove();
        }
        
        function deleteAllBadges() {
            if (!confirm('Are you absolutely sure you want to delete ALL badges? This cannot be undone!')) {
                return;
            }
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'jph_delete_all_badges',
                    nonce: '<?php echo wp_create_nonce('jph_delete_all_badges'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('All badges deleted successfully', 'success');
                    loadBadgesData();
                    closeDeleteAllModal();
                } else {
                    showToast('Error deleting badges: ' + (data.data || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting badges:', error);
                showToast('Error deleting badges', 'error');
            });
        }
        
        // Load badges statistics
        function loadBadgesStats() {
            fetch('<?php echo rest_url('jph/v1/badges/stats'); ?>', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('total-badges').textContent = data.stats.total_badges;
                    document.getElementById('active-badges').textContent = data.stats.active_badges;
                    document.getElementById('badge-categories').textContent = data.stats.category_count;
                    document.getElementById('total-awards').textContent = data.stats.total_awards;
                }
            })
            .catch(error => {
                console.error('Error loading badge stats:', error);
            });
        }
        
        // Load badges table data
        function loadBadgesData() {
            const tbody = document.getElementById('badges-table-body');
            if (!tbody) {
                console.error('badges-table-body not found');
                return;
            }
            tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">Loading badges...</td></tr>';
            
            fetch('<?php echo rest_url('jph/v1/admin/badges'); ?>', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderBadgesTable(data.badges);
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">Error loading badges: ' + (data.message || 'Unknown error') + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading badges:', error);
                tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">Error loading badges: ' + error.message + '</td></tr>';
            });
        }
        
        // Render badges table
        function renderBadgesTable(badges) {
            const tbody = document.getElementById('badges-table-body');
            
            if (!badges || !Array.isArray(badges) || badges.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">No badges found</td></tr>';
                return;
            }
            
            tbody.innerHTML = badges.map(badge => `
                <tr class="jph-badge-row ${!badge.is_active ? 'inactive' : ''}" data-badge-key="${badge.badge_key}">
                    <td>
                        <div class="jph-badge-image-container">
                            ${badge.icon && badge.icon.startsWith('http') ? 
                                `<img src="${badge.icon}" alt="${badge.name}" class="jph-badge-image">` : 
                                `<div class="jph-badge-image jph-badge-placeholder">${badge.icon || '🏆'}</div>`
                            }
                        </div>
                    </td>
                    <td>
                        <div class="jph-badge-info">
                            <strong class="jph-badge-name">${badge.name}</strong>
                            ${badge.description ? `<div class="jph-badge-description">${badge.description}</div>` : ''}
                        </div>
                    </td>
                    <td>
                        <span class="jph-badge-category jph-badge-category-${badge.category}">${badge.category}</span>
                    </td>
                    <td>
                        <div class="jph-badge-rewards">
                            <span class="jph-xp-reward">${badge.xp_reward} XP</span>
                            ${badge.gem_reward ? `<span class="jph-gem-reward">${badge.gem_reward} 💎</span>` : ''}
                        </div>
                    </td>
                    <td>
                        <span class="jph-badge-status jph-badge-status-${badge.is_active ? 'active' : 'inactive'}">
                            ${badge.is_active ? '✅ Active' : '❌ Inactive'}
                        </span>
                    </td>
                    <td>
                        <div class="jph-badge-awarded-count">
                            <span class="jph-count-number">${badge.awarded_count || 0}</span>
                            <span class="jph-count-label">students</span>
                        </div>
                    </td>
                    <td>
                        <div class="jph-fluent-test">
                            ${badge.fluent_event_enabled == 1 ? 
                                `<button class="button button-small" onclick="testBadgeFluentEvent('${badge.badge_key}')" title="Test FluentCRM Event" style="background: #21759b; color: white;">🔗 Test Event</button>` :
                                `<span class="inactive">Disabled</span>`
                            }
                        </div>
                    </td>
                    <td>
                        <div class="jph-badge-actions">
                            ${badge.badge_key && badge.badge_key.trim() !== '' ? 
                                `<button class="button button-small button-primary" onclick="editBadge('${badge.badge_key}')" title="Edit Badge">✏️ Edit</button>
                                 <button class="button button-small button-link-delete" onclick="deleteBadge('${badge.badge_key}')" title="Delete Badge">🗑️ Delete</button>` :
                                `<span class="error-text">❌ No Badge Key</span>`
                            }
                        </div>
                    </td>
                </tr>
            `).join('');
        }
        
        // Simple reorder modal
        function openReorderModal() {
            const modalHtml = `
                <div id="jph-reorder-modal" class="jph-modal" style="display: flex;">
                    <div class="jph-modal-content" style="max-width: 600px;">
                        <div class="jph-modal-header">
                        <h2>📋 Reorder Badges</h2>
                            <button class="jph-modal-close" onclick="closeReorderModal()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="jph-modal-body" style="flex: 1; overflow-y: auto;">
                        <p>Drag and drop badges to reorder them.</p>
                        <div id="reorder-badges-list" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin: 20px 0;">
                            Loading badges...
                        </div>
                        </div>
                        <div class="jph-modal-actions" style="text-align: right; padding: 20px; border-top: 1px solid #ddd; flex-shrink: 0;">
                            <button type="button" class="button button-secondary" onclick="closeReorderModal()">Cancel</button>
                            <button type="button" class="button button-primary" onclick="saveBadgeOrder()">Save Order</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            loadBadgesForReorder();
        }
        
        function closeReorderModal() {
            const modal = document.getElementById('jph-reorder-modal');
            if (modal) modal.remove();
        }
        
        function loadBadgesForReorder() {
            const container = document.getElementById('reorder-badges-list');
            
            fetch('<?php echo rest_url('jph/v1/badges'); ?>', {
                method: 'GET',
                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.badges) {
                    renderReorderList(data.badges);
                } else {
                    container.innerHTML = 'Error loading badges';
                }
            })
            .catch(error => {
                console.error('Error loading badges for reorder:', error);
                container.innerHTML = 'Error loading badges';
            });
        }
        
        function renderReorderList(badges) {
            const container = document.getElementById('reorder-badges-list');
            
            container.innerHTML = badges.map((badge, index) => `
                <div class="reorder-item" data-badge-key="${badge.badge_key}" style="
                    display: flex; align-items: center; padding: 10px; margin: 5px 0; 
                    background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; cursor: move;
                ">
                    <div style="margin-right: 10px; font-weight: bold; color: #666;">${index + 1}.</div>
                    <div style="margin-right: 10px; font-size: 20px;">${badge.icon || '🏆'}</div>
                    <div style="flex: 1;">
                        <div style="font-weight: bold;">${badge.name}</div>
                        <div style="font-size: 12px; color: #666;">${badge.category} • ${badge.xp_reward} XP</div>
                    </div>
                    <div style="color: #999; font-size: 16px;">⋮⋮</div>
                </div>
            `).join('');
            
            makeSortable();
        }
        
        function makeSortable() {
            const container = document.getElementById('reorder-badges-list');
            let draggedElement = null;
            
            container.querySelectorAll('.reorder-item').forEach(item => {
                item.draggable = true;
                
                item.addEventListener('dragstart', function(e) {
                    draggedElement = this;
                    this.style.opacity = '0.5';
                });
                
                item.addEventListener('dragend', function(e) {
                    this.style.opacity = '1';
                    draggedElement = null;
                });
                
                item.addEventListener('dragover', function(e) {
                    e.preventDefault();
                });
                
                item.addEventListener('drop', function(e) {
                    e.preventDefault();
                    if (draggedElement && draggedElement !== this) {
                        if (this.nextSibling) {
                            container.insertBefore(draggedElement, this.nextSibling);
                        } else {
                            container.appendChild(draggedElement);
                        }
                        updateReorderNumbers();
                    }
                });
            });
        }
        
        function updateReorderNumbers() {
            document.querySelectorAll('.reorder-item').forEach((item, index) => {
                const numberDiv = item.querySelector('div');
                numberDiv.textContent = `${index + 1}.`;
            });
        }
        
        function saveBadgeOrder() {
            const items = document.querySelectorAll('.reorder-item');
            const badgeOrders = {};
            
            items.forEach((item, index) => {
                const badgeKey = item.getAttribute('data-badge-key');
                badgeOrders[badgeKey] = index + 1;
            });
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'jph_update_badge_order',
                    badge_orders: JSON.stringify(badgeOrders),
                    nonce: '<?php echo wp_create_nonce('jph_update_badge_order'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Badge order updated successfully!', 'success');
                    closeReorderModal();
                    loadBadgesData();
                } else {
                    showToast('Error updating badge order: ' + data.data, 'error');
                }
            })
            .catch(error => {
                console.error('Error updating badge order:', error);
                showToast('Error updating badge order', 'error');
            });
        }
        
        // Database status modal
        function showDatabaseStatus() {
            const modalHtml = `
                <div id="jph-database-status-modal" class="jph-modal jph-database-modal" style="display: flex;">
                    <div class="jph-modal-content">
                        <div class="jph-modal-header">
                        <h2>🔍 Database Status</h2>
                            <button class="jph-modal-close" onclick="closeDatabaseStatusModal()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="jph-modal-body">
                            <div id="database-status-content" class="jph-database-content">
                                <div class="jph-loading-spinner">Loading database status...</div>
                            </div>
                        </div>
                        <div class="jph-modal-footer">
                            <button type="button" class="button button-primary" onclick="closeDatabaseStatusModal()">✓ Close</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            loadDatabaseStatus();
        }
        
        function closeDatabaseStatusModal() {
            const modal = document.getElementById('jph-database-status-modal');
            if (modal) modal.remove();
        }
        
        function loadDatabaseStatus() {
            const container = document.getElementById('database-status-content');
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'jph_get_database_status',
                    nonce: '<?php echo wp_create_nonce('jph_get_database_status'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderDatabaseStatus(data.data);
                } else {
                    container.innerHTML = 'Error loading database status: ' + (data.data || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error loading database status:', error);
                container.innerHTML = 'Error loading database status';
            });
        }
        
        function renderDatabaseStatus(status) {
            const container = document.getElementById('database-status-content');
            
            let html = `
                <div class="jph-database-overview">
                    <div class="jph-overview-title">
                    <h3>📊 Database Overview</h3>
                        <div class="jph-status-badge ${status.missing_tables.length === 0 ? 'success' : 'warning'}">
                            ${status.missing_tables.length === 0 ? '✓ All Systems Operational' : '⚠ Issues Detected'}
                        </div>
                </div>
                
                    <div class="jph-overview-stats">
                        <div class="jph-stat-item">
                            <div class="jph-stat-icon">🔧</div>
                            <div class="jph-stat-content">
                                <div class="jph-stat-label">Plugin Version</div>
                                <div class="jph-stat-value">v${status.plugin_version}</div>
                            </div>
                        </div>
                        
                        <div class="jph-stat-item">
                            <div class="jph-stat-icon">📊</div>
                            <div class="jph-stat-content">
                                <div class="jph-stat-label">Total Tables</div>
                                <div class="jph-stat-value">${status.total_tables}</div>
                            </div>
                        </div>
                        
                        <div class="jph-stat-item">
                            <div class="jph-stat-icon">⚡</div>
                            <div class="jph-stat-content">
                                <div class="jph-stat-label">Missing Tables</div>
                                <div class="jph-stat-value ${status.missing_tables.length > 0 ? 'error' : ''}">${status.missing_tables.length}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="jph-tables-section">
                    <h3 class="jph-tables-title">📋 Database Tables</h3>
                    <div class="jph-tables-grid">
            `;
            
            Object.entries(status.tables).forEach(([key, table]) => {
                const statusClass = table.exists ? 'success' : 'error';
                const statusIcon = table.exists ? '✅' : '❌';
                const statusText = table.exists ? 'Active' : 'Missing';
                
                html += `
                    <div class="jph-table-card ${statusClass}">
                        <div class="jph-table-header">
                            <div class="jph-table-name">${table.name}</div>
                            <div class="jph-table-status ${statusClass}">
                                <span class="jph-status-icon">${statusIcon}</span>
                                ${statusText}
                            </div>
                        </div>
                        <div class="jph-table-info">
                            <div class="jph-table-metric">
                                <span class="jph-metric-label">Records:</span>
                                <span class="jph-metric-value">${table.row_count}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
            
            if (status.missing_tables.length > 0) {
                html += `
                    <div class="jph-alert jph-alert-warning">
                        <div class="jph-alert-icon">⚠️</div>
                        <div class="jph-alert-content">
                            <h4>Missing Tables Detected</h4>
                            <ul class="jph-missing-list">
                            ${status.missing_tables.map(table => `<li>${table}</li>`).join('')}
                        </ul>
                            <p class="jph-alert-action"><strong>Action Required:</strong> Please deactivate and reactivate the plugin to create missing tables.</p>
                        </div>
                    </div>
                `;
            }
            
            container.innerHTML = html;
        }
        
        // Sync all badge counts
        function syncAllBadgeCounts() {
            if (!confirm('This will sync badge counts for ALL users. This may take a moment. Continue?')) {
                return;
            }
            
            fetch('<?php echo rest_url('jph/v1/sync-all-badge-counts'); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast('Error: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error syncing all badge counts:', error);
                showToast('Error syncing all badge counts: ' + error, 'error');
            });
        }
        
        // Load webhook log
        
        // Add new badge
        function addBadge() {
            let form = document.getElementById('jph-add-badge-form');
            if (!form) {
                form = document.getElementById('jph-add-badge-form-2');
            }
            if (!form) {
                showToast('Form not found', 'error');
                return;
            }
            
            const formData = new FormData(form);
            const url = '<?php echo rest_url('jph/v1/badges'); ?>';
            const nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': nonce
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Badge created successfully!', 'success');
                    closeAddBadgeModal();
                    loadBadgesData();
                    loadBadgesStats();
                } else {
                    showToast('Error: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error creating badge:', error);
                showToast('Error creating badge: ' + error, 'error');
            });
        }
        
        // Edit badge
        function editBadge(badgeKey) {
            // Validate badge key before making API call
            if (!badgeKey || typeof badgeKey !== 'string' || badgeKey.trim() === '') {
                showToast('Invalid badge key', 'error');
                return;
            }
            
            console.log('EditBadge called with badgeKey:', badgeKey);
            
            // Get all badges and find the one we want to edit
            fetch('<?php echo rest_url('jph/v1/badges'); ?>', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('EditBadge badges response:', data);
                
                if (data.success && data.badges) {
                    const badge = data.badges.find(b => b.badge_key === badgeKey);
                    if (badge) {
                        openEditBadgeModal(badge);
                } else {
                        showToast('Badge not found', 'error');
                        console.error('Badge not found with key:', badgeKey);
                    }
                } else {
                    showToast('Error loading badges list', 'error');
                    console.error('Badge data error:', data);
                }
            })
            .catch(error => {
                console.error('Error loading badges:', error);
                showToast('Error loading badge data', 'error');
            });
        }
        
        // Open edit badge modal
        function openEditBadgeModal(badge) {
            // Populate the edit form with badge data
            document.getElementById('edit-badge-key').value = badge.badge_key;
            document.getElementById('edit-badge-name').value = badge.name || '';
            document.getElementById('edit-badge-description').value = badge.description || '';
            document.getElementById('edit-badge-category').value = badge.category || 'achievement';
            document.getElementById('edit-badge-xp-reward').value = badge.xp_reward || 0;
            document.getElementById('edit-badge-gem-reward').value = badge.gem_reward || 0;
            document.getElementById('edit-badge-is-active').checked = badge.is_active == 1;
            
            // Criteria fields
            const criteriaTypeEl = document.getElementById('edit-badge-criteria-type');
            const criteriaValueEl = document.getElementById('edit-badge-criteria-value');
            if (criteriaTypeEl) criteriaTypeEl.value = badge.criteria_type || 'manual';
            if (criteriaValueEl) criteriaValueEl.value = badge.criteria_value || 0;
            
            
            // FluentCRM event fields
            const fluentEventEnabledEl = document.getElementById('edit-badge-fluent-event-enabled');
            if (fluentEventEnabledEl) {
                fluentEventEnabledEl.checked = badge.fluent_event_enabled == 1;
                // Trigger the toggle function to show/hide fields
                toggleFluentEventFields('edit-badge-fluent-event-enabled');
            }
            
            const fluentEventKeyEl = document.getElementById('edit-badge-fluent-event-key');
            if (fluentEventKeyEl) fluentEventKeyEl.value = badge.fluent_event_key || '';
            
            const fluentEventTitleEl = document.getElementById('edit-badge-fluent-event-title');
            if (fluentEventTitleEl) fluentEventTitleEl.value = badge.fluent_event_title || '';
            
            // Show current image if exists
            const currentImageDiv = document.getElementById('edit-current-image');
            if (badge.icon) {
                currentImageDiv.innerHTML = `<img src="${badge.icon}" alt="${badge.name}" style="max-width: 64px; max-height: 64px;">`;
            } else {
                currentImageDiv.innerHTML = '<div style="background: #f0f0f0; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; font-size: 20px;">🏆</div>';
            }
            
            // Show the modal
            const modal = document.getElementById('jph-edit-badge-modal');
            modal.style.display = 'flex';
            
            // Ensure modal is properly positioned
            setTimeout(function() {
                modal.scrollTop = 0;
                modal.scrollLeft = 0;
            }, 10);
        }
        
        // Update badge
        function updateBadge() {
            console.log('updateBadge function called');
            
            const form = document.getElementById('jph-edit-badge-form');
            if (!form) {
                console.error('Edit form not found');
                showToast('Edit form not found', 'error');
                return;
            }
            
            
            const badgeId = document.getElementById('edit-badge-key').value;
            
            // Create JSON data instead of FormData
            const badgeData = {
                name: document.getElementById('edit-badge-name').value,
                description: document.getElementById('edit-badge-description').value,
                category: document.getElementById('edit-badge-category').value,
                xp_reward: parseInt(document.getElementById('edit-badge-xp-reward').value),
                gem_reward: parseInt(document.getElementById('edit-badge-gem-reward').value),
                criteria_type: document.getElementById('edit-badge-criteria-type') ? document.getElementById('edit-badge-criteria-type').value : 'manual',
                criteria_value: document.getElementById('edit-badge-criteria-value') ? parseInt(document.getElementById('edit-badge-criteria-value').value) : 0,
                fluent_event_enabled: document.getElementById('edit-badge-fluent-event-enabled') ? (document.getElementById('edit-badge-fluent-event-enabled').checked ? 1 : 0) : 0,
                fluent_event_key: document.getElementById('edit-badge-fluent-event-key') ? document.getElementById('edit-badge-fluent-event-key').value : '',
                fluent_event_title: document.getElementById('edit-badge-fluent-event-title') ? document.getElementById('edit-badge-fluent-event-title').value : '',
                is_active: document.getElementById('edit-badge-is-active').checked ? 1 : 0
            };
            
            
            fetch('<?php echo rest_url('jph/v1/badges/key/'); ?>' + badgeId, {
                method: 'PUT',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(badgeData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Badge updated successfully!', 'success');
                    closeEditBadgeModal();
                    loadBadgesData();
                    loadBadgesStats();
                } else {
                    showToast('Error: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error updating badge:', error);
                showToast('Error updating badge: ' + error, 'error');
            });
        }
        
        // Close edit badge modal
        function closeEditBadgeModal() {
            document.getElementById('jph-edit-badge-modal').style.display = 'none';
            document.getElementById('jph-edit-badge-form').reset();
        }
        
        // Close add badge modal
        function closeAddBadgeModal() {
            document.getElementById('jph-add-badge-modal').style.display = 'none';
            const form1 = document.getElementById('jph-add-badge-form');
            const form2 = document.getElementById('jph-add-badge-form-2');
            if (form1) form1.reset();
            if (form2) form2.reset();
        }
        
        // Delete badge
        function deleteBadge(badgeKey) {
            if (confirm('Are you sure you want to delete this badge?')) {
                console.log('Deleting badge key:', badgeKey);
                
                fetch('<?php echo rest_url('jph/v1/badges/'); ?>' + badgeKey, {
                    method: 'DELETE',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    }
                })
                .then(response => {
                    console.log('Delete response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Delete response data:', data);
                    if (data.success) {
                        showToast('Badge deleted successfully!', 'success');
                        loadBadgesData();
                        loadBadgesStats();
                    } else {
                        showToast('Error: ' + (data.message || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error deleting badge:', error);
                    showToast('Error deleting badge: ' + error, 'error');
                });
            }
        }
        
        
        // Run migrations
        function runMigrations() {
            if (!confirm('This will run database migrations to add the webhook_url column. Continue?')) {
                return;
            }
            
            fetch('<?php echo rest_url('jph/v1/run-migrations'); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Migrations completed successfully! You can now update badges with webhook URLs.', 'success');
                } else {
                    showToast('Migration failed: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Migration error:', error);
                showToast('Migration error: ' + error, 'error');
            });
        }
        
        // Test badge awarding
        function testBadgeAwarding() {
            if (!confirm('This will check and award any new badges for the current user. Continue?')) {
                return;
            }
            
            fetch('<?php echo rest_url('jph/v1/check-badges'); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Badge awarding response:', data);
                if (data.success) {
                    if (data.count > 0) {
                        showToast(`Success! ${data.count} new badge(s) awarded: ${data.newly_awarded.map(b => b.name).join(', ')}`, 'success');
                    } else {
                        showToast('No new badges to award. User already has all eligible badges.', 'info');
                    }
                    loadBadgesData();
                    loadBadgesStats();
                } else {
                    showToast('Badge awarding failed: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Badge awarding error:', error);
                showToast('Badge awarding error: ' + error, 'error');
            });
        }
        
        // Sync badge count
        function syncBadgeCount() {
            if (!confirm('This will sync the badge count in user stats with the actual number of badges earned. Continue?')) {
                return;
            }
            
            fetch('<?php echo rest_url('jph/v1/sync-badge-count'); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Sync badge count response:', data);
                if (data.success) {
                    showToast(`Badge count synced successfully! Updated from ${data.old_count} to ${data.new_count} badges.`, 'success');
                    loadBadgesData();
                    loadBadgesStats();
                } else {
                    showToast('Badge count sync failed: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Badge count sync error:', error);
                showToast('Badge count sync error: ' + error, 'error');
            });
        }
        
        // Close add badge modal
            function closeAddBadgeModal() {
                document.getElementById('jph-add-badge-modal').style.display = 'none';
                const form1 = document.getElementById('jph-add-badge-form');
                const form2 = document.getElementById('jph-add-badge-form-2');
                if (form1) form1.reset();
                if (form2) form2.reset();
            
            // Reset FluentCRM event fields visibility
            const fluentFields = document.querySelectorAll('.fluent-event-fields');
            fluentFields.forEach(function(field) {
                field.style.display = 'none';
            });
        }

        // Toggle FluentCRM event fields visibility based on checkbox
        function toggleFluentEventFields(id) {
            const checkbox = document.getElementById(id);
            if (!checkbox) return;
            
            // Find the closest modal and its fluent event fields
            const modal = checkbox.closest('.jph-modal');
            if (!modal) return;
            
            const fluentFields = modal.querySelectorAll('.fluent-event-fields');
            fluentFields.forEach(function(field) {
                field.style.display = checkbox.checked ? 'block' : 'none';
            });
        }
        
        // Test badge FluentCRM event
        function testBadgeFluentEvent(badgeKey) {
            if (!confirm('This will send a test FluentCRM event for this badge to the current user. This helps register the event in FluentCRM for automation setup. Continue?')) {
                return;
            }
            
            fetch('<?php echo rest_url('jph/v1/test-badge-event'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({
                    badge_key: badgeKey
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Test event triggered successfully! Event: ${data.event_key}`, 'success');
                } else {
                    showToast('Test event failed: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Test badge event error:', error);
                showToast('Test badge event error: ' + error.message, 'error');
            });
            }
            
            // Create default badges
            function createDefaultBadges() {
                if (!confirm('This will create 6 default badges. Continue?')) {
                    return;
                }
                
                fetch('<?php echo rest_url('jph/v1/create-default-badges'); ?>', {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Default badges created successfully!', 'success');
                        loadBadgesData();
                        loadBadgesStats();
                    } else {
                        showToast('Error creating default badges: ' + (data.message || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error creating default badges:', error);
                    showToast('Error creating default badges', 'error');
                });
            }
        </script>
        <?php
    }
    
    /**
     * Badges page
     */
    public function badges_page() {
        ?>
        <div class="wrap">
            <h1>🏆 Badge Management</h1>
            
            <div class="jph-badges-overview">
                <div class="jph-badges-stats">
                    <div class="jph-stat-card">
                        <h3>Total Badges</h3>
                        <p id="total-badges">Loading...</p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Active Badges</h3>
                        <p id="active-badges">Loading...</p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Categories</h3>
                        <p id="badge-categories">Loading...</p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Total Awards</h3>
                        <p id="total-awards">Loading...</p>
                    </div>
                </div>
            </div>
            
            
            <div class="jph-badges-actions">
                <button type="button" class="button button-primary" id="add-badge-btn-2">➕ Add New Badge</button>
                <button type="button" class="button button-secondary" id="refresh-badges-btn-2">🔄 Refresh</button>
                <button type="button" class="button button-secondary" id="sync-all-badge-counts-btn" style="background: #fd7e14; color: white;">🔄 Sync All Badge Counts</button>
                <button type="button" class="button button-secondary" id="reorder-badges-btn" style="background: #007cba; color: white;">📋 Reorder Badges</button>
                <button type="button" class="button button-secondary" id="database-status-btn" style="background: #17a2b8; color: white;">🔍 Database Status</button>
                <button type="button" class="button" id="delete-all-badges-btn" style="background: #dc3545; border-color: #dc3545; color: white;">🗑️ Delete All Badges</button>
            </div>
            
            
            
            <div class="jph-badges-table-container">
                <table class="jph-badges-table">
                    <thead>
                        <tr>
                            <th>Badge</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>XP Reward</th>
                            <th>Status</th>
                            <th>Students</th>
                            <th>Awarded To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="badges-table-body">
                        <tr>
                            <td colspan="8" class="jph-loading">Loading badges...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Add Badge Modal -->
        <div id="jph-add-badge-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>🏆 Add New Badge</h2>
                    <button class="jph-modal-close" onclick="closeAddBadgeModal()">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="jph-modal-body">
                    <form id="jph-add-badge-form" action="#" method="post" enctype="multipart/form-data">
                        
                        <div class="jph-form-group">
                            <label for="badge-name">Badge Name:</label>
                            <input type="text" id="badge-name" name="name" required placeholder="e.g., First Session">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-description">Description:</label>
                            <textarea id="badge-description" name="description" rows="3" placeholder="Describe what this badge represents..."></textarea>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-image">Badge Image:</label>
                            <input type="file" id="badge-image" name="badge_image" accept="image/*">
                            <small>Recommended: 64x64px PNG with transparent background</small>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-category">Category:</label>
                            <select id="badge-category" name="category">
                                <option value="achievement">Achievement</option>
                                <option value="milestone">Milestone</option>
                                <option value="special">Special</option>
                                <option value="streak">Streak</option>
                                <option value="level">Level</option>
                            </select>
                        </div>
                        
                        
                        <div class="jph-form-group">
                            <label for="badge-xp-reward">XP Reward:</label>
                            <input type="number" id="badge-xp-reward" name="xp_reward" min="0" value="0">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-gem-reward">Gem Reward:</label>
                            <input type="number" id="badge-gem-reward" name="gem_reward" min="0" value="0">
                            <small>Number of gems to award when this badge is earned.</small>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-criteria-type">Criteria Type:</label>
                            <select id="badge-criteria-type" name="criteria_type">
                                <option value="manual">Manual (no auto-award)</option>
                                <option value="total_xp">Total XP ≥ value</option>
                                <option value="practice_sessions">Practice sessions ≥ value</option>
                                <option value="streak_7">7-day streak</option>
                                <option value="streak_30">30-day streak</option>
                                <option value="streak_100">100-day streak</option>
                                <option value="long_session">Long session (≥ minutes)</option>
                                <option value="improvement_count">Improvements reported ≥ value</option>
                                <option value="first_session">First practice session</option>
                            </select>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-criteria-value">Criteria Value:</label>
                            <input type="number" id="badge-criteria-value" name="criteria_value" min="0" value="0">
                            <small>Meaning depends on criteria type (e.g., XP amount, session count, minutes, streak days).</small>
                        </div>
                        

                        <div class="jph-form-section">
                            <h3>🔗 FluentCRM Event Tracking</h3>
                            
                            <div class="jph-form-group">
                                <label for="badge-fluent-event-enabled">
                                    <input type="checkbox" id="badge-fluent-event-enabled" name="fluent_event_enabled" value="1" onclick="toggleFluentEventFields('badge-fluent-event-enabled')">
                                    Enable FluentCRM Event Tracking
                                </label>
                                <small>Track this badge achievement as an event in FluentCRM for automation triggers.</small>
                            </div>
                            
                            <div class="jph-form-group fluent-event-fields" style="display:none;">
                                <label for="badge-fluent-event-key">Event Key:</label>
                                <input type="text" id="badge-fluent-event-key" name="fluent_event_key" placeholder="jph_badge_key (auto-generated if empty)">
                                <small>Unique identifier for FluentCRM event (e.g., 'jph_first_steps', 'jazzedge_marathon')</small>
                            </div>
                            
                            <div class="jph-form-group fluent-event-fields" style="display:none;">
                                <label for="badge-fluent-event-title">Event Title:</label>
                                <input type="text" id="badge-fluent-event-title" name="fluent_event_title" placeholder="Badge name (auto-generated if empty)">
                                <small>Human-readable title for the FluentCRM event (appears in contact timeline)</small>
                            </div>
                        </div>
                        
                        <div class="jph-form-actions">
                            <button type="button" class="button button-primary" onclick="addBadge()">Create Badge</button>
                            <button type="button" class="button button-secondary" onclick="closeAddBadgeModal()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Edit Badge Modal -->
        <div id="jph-edit-badge-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>✏️ Edit Badge</h2>
                    <button class="jph-modal-close" onclick="closeEditBadgeModal()">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="jph-modal-body">
                    <form id="jph-edit-badge-form" enctype="multipart/form-data">
                        <input type="hidden" id="edit-badge-key" name="badge_key">
                        
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-name">Badge Name:</label>
                            <input type="text" id="edit-badge-name" name="name" required placeholder="e.g., First Session">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-description">Description:</label>
                            <textarea id="edit-badge-description" name="description" rows="3" placeholder="Describe what this badge represents..."></textarea>
                        </div>
                        
                        <div class="jph-form-group">
                            <label>Current Image:</label>
                            <div id="edit-current-image" style="margin: 10px 0;"></div>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-image">New Badge Image (optional):</label>
                            <input type="file" id="edit-badge-image" name="badge_image" accept="image/*">
                            <small>Leave empty to keep current image. Recommended: 64x64px PNG with transparent background</small>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-category">Category:</label>
                            <select id="edit-badge-category" name="category">
                                <option value="achievement">Achievement</option>
                                <option value="milestone">Milestone</option>
                                <option value="special">Special</option>
                                <option value="streak">Streak</option>
                                <option value="level">Level</option>
                                <option value="practice">Practice</option>
                                <option value="improvement">Improvement</option>
                            </select>
                        </div>
                        
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-xp-reward">XP Reward:</label>
                            <input type="number" id="edit-badge-xp-reward" name="xp_reward" min="0" value="0">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-gem-reward">Gem Reward:</label>
                            <input type="number" id="edit-badge-gem-reward" name="gem_reward" min="0" value="0">
                            <small>Number of gems to award when this badge is earned.</small>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-criteria-type">Criteria Type:</label>
                            <select id="edit-badge-criteria-type" name="criteria_type">
                                <option value="manual">Manual (no auto-award)</option>
                                <option value="total_xp">Total XP ≥ value</option>
                                <option value="level_reached">Level Reached ≥ value</option>
                                <option value="practice_sessions">Practice Sessions ≥ value</option>
                                <option value="streak">Streak Days ≥ value</option>
                                <option value="total_time">Single Session Minutes ≥ value</option>
                                <option value="improvement_count">Improvements Reported ≥ value</option>
                                <option value="weekly_goal">Practice Days This Week ≥ value</option>
                                <option value="monthly_goal">Practice Days This Month ≥ value</option>
                                <option value="weekend_warrior">Weekend Sessions ≥ value</option>
                            </select>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-criteria-value">Criteria Value:</label>
                            <input type="number" id="edit-badge-criteria-value" name="criteria_value" min="0" value="0">
                            <small>Meaning depends on criteria type (e.g., XP amount, session count, minutes, streak days).</small>
                        </div>
                        

                        <div class="jph-form-section">
                            <h3>🔗 FluentCRM Event Tracking</h3>
                            
                            <div class="jph-form-group">
                                <label for="edit-badge-fluent-event-enabled">
                                    <input type="checkbox" id="edit-badge-fluent-event-enabled" name="fluent_event_enabled" value="1" onclick="toggleFluentEventFields('edit-badge-fluent-event-enabled')">
                                    Enable FluentCRM Event Tracking
                                </label>
                                <small>Track this badge achievement as an event in FluentCRM for automation triggers.</small>
                            </div>
                            
                            <div class="jph-form-group fluent-event-fields" style="display:none;">
                                <label for="edit-badge-fluent-event-key">Event Key:</label>
                                <input type="text" id="edit-badge-fluent-event-key" name="fluent_event_key" placeholder="jph_badge_key (auto-generated if empty)">
                                <small>Unique identifier for FluentCRM event (e.g., 'jph_first_steps', 'jazzedge_marathon')</small>
                            </div>
                            
                            <div class="jph-form-group fluent-event-fields" style="display:none;">
                                <label for="edit-badge-fluent-event-title">Event Title:</label>
                                <input type="text" id="edit-badge-fluent-event-title" name="fluent_event_title" placeholder="Badge name (auto-generated if empty)">
                                <small>Human-readable title for the FluentCRM event (appears in contact timeline)</small>
                            </div>
                        </div>
                        
                        <div class="jph-form-group">
                            <label>
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" id="edit-badge-is-active" name="is_active" value="1" checked>
                                Active (badge can be earned)
                            </label>
                        </div>
                        
                        <div class="jph-form-actions">
                            <button type="button" class="button button-primary" onclick="updateBadge()">Update Badge</button>
                            <button type="button" class="button button-secondary" onclick="closeEditBadgeModal()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <style>
        /* Badge Management Styles */
        .jph-badges-overview {
            margin: 20px 0;
        }
        
        .jph-badges-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .jph-stat-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-stat-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jph-stat-card p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .jph-badges-actions {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .jph-badges-table-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-badges-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .jph-badges-table th {
            background: #f8f9fa;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        .jph-badges-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        .jph-badge-row.inactive {
            opacity: 0.6;
            background-color: #f8f9fa;
        }
        
        .jph-badge-image-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .jph-badge-image {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        .jph-badge-placeholder {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            border-radius: 8px;
        }
        
        .jph-badge-info {
            min-width: 200px;
        }
        
        .jph-badge-name {
            display: block;
            color: #333;
            margin-bottom: 4px;
        }
        
        .jph-badge-key {
            font-size: 12px;
            color: #666;
            font-family: monospace;
            background: #f1f3f4;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 4px;
        }
        
        .jph-badge-description {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
            max-width: 250px;
        }
        
        .jph-badge-category {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
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
        
        .jph-badge-rarity {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jph-badge-rarity-common { background: #f5f5f5; color: #666; }
        .jph-badge-rarity-uncommon { background: #e8f5e8; color: #2e7d32; }
        .jph-badge-rarity-rare { background: #e3f2fd; color: #1565c0; }
        .jph-badge-rarity-epic { background: #f3e5f5; color: #7b1fa2; }
        .jph-badge-rarity-legendary { background: #fff3e0; color: #ef6c00; }
        
        .jph-badge-rewards {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        /* Badge Modal Layout */
        .jph-badge-modal {
            max-width: 900px !important;
            width: 90% !important;
        }

        /* Database Status Modal */
        .jph-database-modal .jph-modal-content {
            max-width: 1000px !important;
            width: 95% !important;
            max-height: 90vh !important;
        }

        .jph-database-content {
            max-height: 70vh !important;
            overflow-y: auto !important;
        }

        .jph-loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            color: #666;
            font-size: 16px;
        }

        /* Database Overview */
        .jph-database-overview {
            margin-bottom: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 25px;
            color: white;
        }

        .jph-overview-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .jph-overview-title h3 {
            margin: 0;
            color: white;
            font-size: 20px;
            font-weight: 600;
        }

        .jph-status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .jph-status-badge.success {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .jph-status-badge.warning {
            background: rgba(255, 152, 0, 0.2);
            color: #ff9800;
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        .jph-overview-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .jph-stat-item {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .jph-stat-icon {
            font-size: 24px;
            margin-right: 15px;
        }

        .jph-stat-content {
            flex: 1;
        }

        .jph-stat-label {
            font-size: 12px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .jph-stat-value {
            font-size: 20px;
            font-weight: 700;
        }

        .jph-stat-value.error {
            color: #ff5722;
        }

        /* Tables Section */
        .jph-tables-section {
            margin-top: 30px;
        }

        .jph-tables-title {
            margin: 0 0 20px 0;
            color: #23282d;
            font-size: 18px;
            font-weight: 600;
        }

        .jph-tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }

        .jph-table-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .jph-table-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .jph-table-card.success {
            border-left-color: #28a745;
        }

        .jph-table-card.error {
            border-left-color: #dc3545;
        }

        .jph-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .jph-table-name {
            font-weight: 600;
            color: #23282d;
            font-size: 14px;
        }

        .jph-table-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 12px;
        }

        .jph-table-status.success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .jph-table-status.error {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .jph-status-icon {
            font-size: 12px;
        }

        .jph-table-info {
            border-top: 1px solid #f1f3f4;
            padding-top: 15px;
        }

        .jph-table-metric {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .jph-metric-label {
            color: #666;
            font-size: 12px;
        }

        .jph-metric-value {
            font-weight: 600;
            color: #23282d;
        }

        /* Alert Styles */
        .jph-alert {
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .jph-alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }

        .jph-alert-icon {
            font-size: 20px;
            margin-top: 2px;
        }

        .jph-alert-content h4 {
            margin: 0 0 10px 0;
            color: #856404;
            font-size: 16px;
        }

        .jph-missing-list {
            margin: 10px 0;
            padding-left: 20px;
        }

        .jph-missing-list li {
            margin-bottom: 5px;
        }

        .jph-alert-action {
            margin: 10px 0 0 0;
            font-size: 14px;
        }

        .jph-modal-body {
            padding: 20px !important;
        }

        .jph-form-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .jph-form-section:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
        }

        .jph-form-section h3 {
            margin: 0 0 15px 0;
            color: #23282d;
            font-size: 16px;
            font-weight: 600;
        }

        .jph-form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .jph-form-group-flex {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .jph-form-group-flex label {
            font-weight: 600;
            font-size: 14px;
            color: #23282d;
        }

        .jph-form-group-half {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .jph-form-group-half label {
            font-weight: 600;
            font-size: 14px;
            color: #23282d;
        }

        .jph-checkbox-label {
            display: flex !important;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .jph-checkbox-label input[type="checkbox"] {
            margin: 0;
        }

        .jph-form-group {
            margin-bottom: 15px;
        }

        .jph-form-group label {
            font-weight: 600;
            font-size: 14px;
            color: #23282d;
            display: block;
            margin-bottom: 5px;
        }

        .jph-form-group input[type="number"] {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
        }

        .jph-form-group input[type="text"] {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
        }

        .jph-form-group input[type="url"] {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
        }
    }

    .jph-form-group select {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 14px;
        height: auto;
    }

    .jph-form-group textarea {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 14px;
        resize: vertical;
    }

    .jph-form-group small {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #6c757d;
    }

    .jph-form-actions {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
        }
        
        .jph-xp-reward {
            font-weight: 600;
            color: #1976d2;
        }
        
        .jph-gem-reward {
            font-size: 12px;
            color: #7b1fa2;
        }
        
        .jph-gems-display {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .jph-gem-icon {
            font-size: 14px;
        }
        
        .jph-badge-awarded-count {
            text-align: center;
        }
        
        .jph-count-number {
            font-weight: bold;
            font-size: 16px;
            color: #2271b1;
        }
        
        .jph-count-label {
            font-size: 11px;
            color: #666;
            display: block;
        }
        
        /* Toast Notification System */
        .jph-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 12px 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            max-width: 400px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }
        
        .jph-toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .jph-toast.success {
            border-left: 4px solid #28a745;
        }
        
        .jph-toast.error {
            border-left: 4px solid #dc3545;
        }
        
        .jph-toast.info {
            border-left: 4px solid #17a2b8;
        }
        
        .jph-toast .toast-content {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .jph-toast .toast-icon {
            font-size: 16px;
        }
        
        .jph-toast .toast-message {
            flex: 1;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .jph-toast .toast-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #999;
            padding: 0;
            margin-left: 8px;
        }
        
        .jph-toast .toast-close:hover {
            color: #666;
        }
        
        .jph-badge-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .jph-badge-status-active {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .jph-badge-status-inactive {
            background: #ffebee;
            color: #d32f2f;
        }
        
        .jph-badge-actions {
            display: flex;
            gap: 5px;
        }
        
        .jph-badge-actions .button {
            font-size: 11px;
            padding: 4px 8px;
            height: auto;
        }
        
        /* Modal Styles */
        .jph-modal {
            position: fixed;
            z-index: 100000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 80px 20px 150px 20px;
            box-sizing: border-box;
            overflow: auto;
        }
        
        .jph-modal-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            max-height: 90vh;
            max-width: 90vw;
            width: 100%;
            overflow-y: auto;
            position: relative;
            margin: auto;
        }
        
        /* Responsive modal sizing */
        @media (min-width: 768px) {
            .jph-modal-content {
                max-width: 600px;
                width: 600px;
                min-width: 400px;
            }
        }
        
        @media (max-width: 767px) {
            .jph-modal {
                padding: 20px 10px 120px 10px;
                align-items: flex-start;
            }
            
            .jph-modal-content {
                max-width: 100%;
                width: 100%;
                max-height: 95vh;
                margin: 0;
            }
        }
        
        /* Ensure modal is always visible */
        @media (max-width: 480px) {
            .jph-modal {
                padding: 10px 5px 100px 5px;
                align-items: flex-start;
            }
            
            .jph-modal-content {
                max-height: 98vh;
            }
        }
        
        .jph-modal-header {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .jph-modal-header h2 {
            margin: 0;
            color: #333;
        }
        
        .jph-modal-close {
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .jph-modal-close:hover {
            color: #333;
        }
        
        .jph-modal-body {
            padding: 20px;
        }
        
        .jph-form-group {
            margin-bottom: 20px;
        }
        
        .jph-form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .jph-form-group input,
        .jph-form-group select,
        .jph-form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .jph-form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }
        
        .jph-form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .jph-loading {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        /* Additional form improvements */
        .jph-form-group input[type="file"] {
            padding: 4px;
        }
        
        .jph-form-group input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
        }
        
        /* Modal form improvements */
        .jph-modal .jph-form-group input,
        .jph-modal .jph-form-group select,
        .jph-modal .jph-form-group textarea {
            box-sizing: border-box;
        }
        
        /* WordPress admin compatibility */
        .jph-modal {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        
        .jph-modal * {
            box-sizing: border-box;
        }
        
        /* Ensure modal is above WordPress admin elements */
        .jph-modal {
            z-index: 100000 !important;
        }
        
        /* Fix any potential positioning issues */
        .jph-modal-content {
            transform: none !important;
            left: auto !important;
            right: auto !important;
            top: auto !important;
            bottom: auto !important;
        }
        
        /* Force modal bottom spacing to avoid dock overlap */
        .jph-modal {
            padding-bottom: 150px !important;
        }
        
        @media (max-width: 768px) {
            .jph-modal {
                padding-bottom: 120px !important;
            }
        }
        
        /* Admin modal specific styles - ensure proper height and centering */
        .jph-modal {
            align-items: center !important;
            padding: 20px !important;
            height: 100vh !important;
            max-height: 100vh !important;
        }
        
        .jph-modal-content {
            max-height: calc(100vh - 40px) !important;
            overflow-y: auto !important;
            margin: auto !important;
        }
        
        @media (max-width: 768px) {
            .jph-modal {
                padding: 10px !important;
            }
            
            .jph-modal-content {
                max-height: calc(100vh - 20px) !important;
            }
        }
        
        /* Shield Protection Modal Styles */
        .shield-info-content h3 {
            color: #0073aa;
            font-size: 18px;
            margin: 20px 0 10px 0;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 5px;
        }
        
        .shield-info-content h3:first-child {
            margin-top: 0;
        }
        
        .shield-info-content ul {
            margin: 10px 0 20px 20px;
        }
        
        .shield-info-content li {
            margin: 8px 0;
            line-height: 1.5;
        }
        
        .shield-info-content strong {
            color: #0073aa;
        }
        
        .shield-example {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #0073aa;
        }
        
        .example-box {
            background: white;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }
        
        .example-box p {
            margin: 8px 0;
            font-family: monospace;
            font-size: 14px;
        }
        
        .jph-modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #e0e0e0;
            text-align: right;
            background: #f8f9fa;
        }
        </style>
        
        <?php $this->badges_page_js(); ?>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>⚙️ Practice Hub Settings</h1>
            
            <div class="jph-settings-sections">
                <div class="jph-settings-section jph-danger-section">
                    <h2>🧪 DATA MANAGEMENT FOR TESTING</h2>
                    <p><strong>DEVELOPMENT/TESTING TOOL:</strong> This will permanently delete ALL user data and cannot be undone!</p>
                    
                    <div class="clear-all-section">
                        <p>This action will clear:</p>
                        <ul>
                            <li>📝 All practice sessions and items</li>
                            <li>👥 All user statistics (XP, levels, streaks)</li>
                            <li>🎖️ All earned badges (user badges)</li>
                            <li>💎 All gem transactions and balances</li>
                            <li>❤️ All lesson favorites</li>
                        </ul>
                        <p><strong>Note:</strong> This will NOT delete badge definitions or plugin settings.</p>
                        
                        <button type="button" class="button button-danger jph-clear-all-btn" onclick="confirmClearAllUserData()">
                            💥 CLEAR ALL USER DATA
                            </button>
                    </div>
                    
                    <div id="danger-results" class="danger-results"></div>
                </div>
            </div>
        </div>
        
        <style>
        .jph-settings-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
            margin: 25px 0;
            max-width: 900px;
        }
        
        .jph-settings-section {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .jph-settings-section:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        
        .jph-settings-section h2 {
            margin: 0 0 15px 0;
            color: #1e1e1e;
            font-size: 20px;
            font-weight: 600;
            border-bottom: 2px solid #f5f5f5;
            padding-bottom: 10px;
        }
        
        .jph-settings-section p {
            margin: 8px 0;
            color: #555;
            font-size: 15px;
            line-height: 1.5;
        }
        
        /* Danger Zone Styles */
        .jph-danger-section {
            border: 2px solid #dc3545 !important;
            background: #fff5f5 !important;
        }
        
        .jph-danger-section h2 {
            color: #dc3545 !important;
            font-weight: bold;
        }
        
        .jph-danger-section p {
            color: #721c24 !important;
            font-weight: 500;
        }
        
        /* Data Management Grid */
        .data-management-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
            margin-top: 20px;
        }
        
        .quick-clear-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
        }
        
        .quick-clear-section h3 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 16px;
        }
        
        .quick-clear-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 10px;
        }
        
        .quick-clear-buttons .button {
            justify-self: start;
            white-space: nowrap;
            padding: 8px 12px;
            font-size: 13px;
        }
        
        .nuclear-section {
            background: linear-gradient(135deg, #ff4444, #cc0000);
            border: 2px solid #dc3545;
            border-radius: 12px;
            padding: 25px;
            color: white;
            text-align: center;
        }
        
        .nuclear-section h3 {
            margin: 0 0 10px 0;
            color: white;
            font-size: 18px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .nuclear-section p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
        }
        
        .nuclear-confirmation {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .checkbox-confirm label {
            color: white !important;
            font-size: 14px;
        }
        
        .text-confirm label {
            color: white !important;
            font-size: 14px;
        }
        
        .text-confirm input {
            padding: 8px 12px;
            border-radius: 4px;
            border: 2px solid #fff;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .jph-nuclear-btn:disabled {
            background: #6c757d !important;
            border-color: #6c757d !important;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .jph-nuclear-btn:not(:disabled) {
            background: linear-gradient(135deg, #ff6b6b, #ff4444) !important;
            border-color: #ff4444 !important;
            box-shadow: 0 4px 15px rgba(255, 68, 68, 0.4);
            animation: pulse-danger 2s infinite;
        }
        
        @keyframes pulse-danger {
            0% { box-shadow: 0 4px 15px rgba(255, 68, 68, 0.4); }
            50% { box-shadow: 0 6px 25px rgba(255, 68, 68, 0.6); }
            100% { box-shadow: 0 4px 15px rgba(255, 68, 68, 0.4); }
        }
        
        /* Clear All Section */
        .clear-all-section {
            background: #fff5f5;
            padding: 25px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .clear-all-section p {
            margin: 10px 0;
            color: #721c24;
        }
        
        .clear-all-section ul {
            margin: 15px 0 20px 20px;
            color: #721c24;
        }
        
        .clear-all-section li {
            margin: 8px 0;
        }
        
        .jph-clear-all-btn {
            background: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
            padding: 15px 30px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            border-radius: 6px !important;
            cursor: pointer !important;
        }
        
        .jph-clear-all-btn:hover {
            background: #c82333 !important;
            border-color: #bd2130 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        }
        
        .danger-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .danger-action {
            background: #fff;
            border: 1px solid #dc3545;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .danger-action h3 {
            margin: 0 0 10px 0;
            color: #dc3545;
            font-size: 16px;
        }
        
        .danger-action p {
            margin: 0 0 15px 0;
            color: #666;
            font-size: 14px;
            font-style: normal;
        }
        
        .button-danger {
            background: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
        }
        
        .button-danger:hover {
            background: #c82333 !important;
            border-color: #bd2130 !important;
        }
        
        .danger-results {
            margin-top: 20px;
            padding: 15px;
            border-radius: 6px;
            display: none;
        }
        
        .danger-results.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .danger-results.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        </style>
        
        <script>
        function confirmClearAllUserData() {
            if (confirm('⚠️ DANGER: This will permanently delete ALL user data including:\n\n• All practice sessions and items\n• All user statistics (XP, levels, streaks)\n• All earned badges\n• All gem transactions and balances\n• All lesson favorites\n\nThis action CANNOT be undone!\n\nAre you absolutely sure you want to continue?')) {
                if (confirm('This is your FINAL WARNING!\n\nAll user data will be deleted forever.\n\nClick OK to proceed or Cancel to stop.')) {
                    clearAllUserData();
                }
            }
        }
        
        function clearAllUserData() {
            const resultsDiv = document.getElementById('danger-results');
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = `<div class="jph-loading">🔄 Clearing all user data...</div>`;
            
            jQuery.post(ajaxurl, {
                action: 'jph_clear_all_user_data',
                nonce: '<?php echo wp_create_nonce('jph_clear_all_data'); ?>'
            })
            .done(function(response) {
                console.log('Clear all data response:', response); // Debug log
                if (response.success) {
                    const message = response.data || response.message || 'All user data cleared';
                    resultsDiv.innerHTML = `<div class="notice notice-success"><p>✅ Successfully cleared all user data: ${message}</p></div>`;
                } else {
                    const errorMsg = response.data || response.message || 'Unknown error';
                    resultsDiv.innerHTML = `<div class="notice notice-error"><p>❌ Error clearing all data: ${errorMsg}</p></div>`;
                }
            })
            .fail(function() {
                resultsDiv.innerHTML = `<div class="notice notice-error"><p>❌ Network error while clearing all data</p></div>`;
            });
        }
        
        function confirmWipeAllData() {
            if (confirm('⚠️ DANGER: This will permanently delete ALL user data including:\n\n• All practice items\n• All practice sessions\n• All user statistics\n• All earned badges\n• All lesson favorites\n\nThis action CANNOT be undone!\n\nAre you absolutely sure you want to continue?')) {
                if (confirm('This is your FINAL WARNING!\n\nType "DELETE ALL DATA" to confirm:')) {
                    const confirmation = prompt('Type "DELETE ALL DATA" to confirm:');
                    if (confirmation === 'DELETE ALL DATA') {
                        wipeAllData();
                    } else {
                        alert('Confirmation text did not match. Operation cancelled.');
                    }
                }
            }
        }
        
        function confirmResetStats() {
            if (confirm('⚠️ WARNING: This will reset all user statistics (XP, levels, streaks) for ALL users.\n\nPractice items and sessions will be kept.\n\nAre you sure you want to continue?')) {
                resetAllStats();
            }
        }
        
        function confirmClearBadges() {
            if (confirm('⚠️ WARNING: This will remove all earned badges from ALL users.\n\nBadge definitions will be kept.\n\nAre you sure you want to continue?')) {
                clearAllBadges();
            }
        }
        
        function confirmClearFavorites() {
            if (confirm('⚠️ WARNING: This will remove all lesson favorites from ALL users.\n\nAre you sure you want to continue?')) {
                clearAllFavorites();
            }
        }
        
        function wipeAllData() {
            const resultsDiv = document.getElementById('danger-results');
            resultsDiv.style.display = 'block';
            resultsDiv.className = 'danger-results';
            resultsDiv.innerHTML = 'Wiping all user data...';
            
            jQuery.post(ajaxurl, {
                action: 'jph_wipe_all_data',
                nonce: '<?php echo wp_create_nonce('jph_wipe_all_data'); ?>'
            }, function(response) {
                if (response.success) {
                    resultsDiv.className = 'danger-results success';
                    resultsDiv.innerHTML = '<strong>Success:</strong> ' + response.data.message;
                } else {
                    resultsDiv.className = 'danger-results error';
                    resultsDiv.innerHTML = '<strong>Error:</strong> ' + response.data;
                }
            }).fail(function() {
                resultsDiv.className = 'danger-results error';
                resultsDiv.innerHTML = '<strong>Error:</strong> Failed to communicate with server.';
            });
        }
        
        function resetAllStats() {
            const resultsDiv = document.getElementById('danger-results');
            resultsDiv.style.display = 'block';
            resultsDiv.className = 'danger-results';
            resultsDiv.innerHTML = 'Resetting all user statistics...';
            
            jQuery.post(ajaxurl, {
                action: 'jph_reset_all_stats',
                nonce: '<?php echo wp_create_nonce('jph_reset_all_stats'); ?>'
            }, function(response) {
                if (response.success) {
                    resultsDiv.className = 'danger-results success';
                    resultsDiv.innerHTML = '<strong>Success:</strong> ' + response.data.message;
                } else {
                    resultsDiv.className = 'danger-results error';
                    resultsDiv.innerHTML = '<strong>Error:</strong> ' + response.data;
                }
            }).fail(function() {
                resultsDiv.className = 'danger-results error';
                resultsDiv.innerHTML = '<strong>Error:</strong> Failed to communicate with server.';
            });
        }
        
        function clearAllBadges() {
            const resultsDiv = document.getElementById('danger-results');
            resultsDiv.style.display = 'block';
            resultsDiv.className = 'danger-results';
            resultsDiv.innerHTML = 'Clearing all badges...';
            
            jQuery.post(ajaxurl, {
                action: 'jph_clear_all_badges',
                nonce: '<?php echo wp_create_nonce('jph_clear_all_badges'); ?>'
            }, function(response) {
                if (response.success) {
                    resultsDiv.className = 'danger-results success';
                    resultsDiv.innerHTML = '<strong>Success:</strong> ' + response.data.message;
                } else {
                    resultsDiv.className = 'danger-results error';
                    resultsDiv.innerHTML = '<strong>Error:</strong> ' + response.data;
                }
            }).fail(function() {
                resultsDiv.className = 'danger-results error';
                resultsDiv.innerHTML = '<strong>Error:</strong> Failed to communicate with server.';
            });
        }
        
        function clearAllFavorites() {
            const resultsDiv = document.getElementById('danger-results');
            resultsDiv.style.display = 'block';
            resultsDiv.className = 'danger-results';
            resultsDiv.innerHTML = 'Clearing all lesson favorites...';
            
            jQuery.post(ajaxurl, {
                action: 'jph_clear_all_favorites',
                nonce: '<?php echo wp_create_nonce('jph_clear_all_favorites'); ?>'
            }, function(response) {
                if (response.success) {
                    resultsDiv.className = 'danger-results success';
                    resultsDiv.innerHTML = '<strong>Success:</strong> ' + response.data.message;
                } else {
                    resultsDiv.className = 'danger-results error';
                    resultsDiv.innerHTML = '<strong>Error:</strong> ' + response.data;
                }
            }).fail(function() {
                resultsDiv.className = 'danger-results error';
                resultsDiv.innerHTML = '<strong>Error:</strong> Failed to communicate with server.';
            });
        }
        </script>
        <?php
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Test endpoint
        register_rest_route('jph/v1', '/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_test'),
            'permission_callback' => '__return_true'
        ));
        
        // Gamification endpoints
        register_rest_route('jph/v1', '/gamification/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_gamification_test'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('jph/v1', '/gamification/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_gamification_stats'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Students endpoints
        register_rest_route('jph/v1', '/students', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_students'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('jph/v1', '/students/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_students_stats'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('jph/v1', '/students/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_student'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                )
            )
        ));
        
        register_rest_route('jph/v1', '/students/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_student'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                )
            )
        ));
        
        // Backfill user stats from existing practice sessions
        register_rest_route('jph/v1', '/backfill-stats', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_backfill_user_stats'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Badge management endpoints - ADMIN ONLY
        register_rest_route('jph/v1', '/admin/badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_badges_admin'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('jph/v1', '/admin/badges', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_add_badge'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // User badges endpoint - LOGGED IN USERS ONLY
        register_rest_route('jph/v1', '/badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_user_badges'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/badges/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_badge'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param) && $param > 0;
                    }
                )
            )
        ));
        
        register_rest_route('jph/v1', '/badges/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_badge'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param) && $param > 0;
                    }
                )
            )
        ));
        
        register_rest_route('jph/v1', '/badges/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_badge'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param) && $param > 0;
                    }
                )
            )
        ));
        
        // Badge management by badge_key (new routes for migrated system)
        register_rest_route('jph/v1', '/badges/key/(?P<badge_key>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_badge_by_key'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('jph/v1', '/badges/key/(?P<badge_key>[a-zA-Z0-9_-]+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_badge_by_key'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('jph/v1', '/badges/key/(?P<badge_key>[a-zA-Z0-9_-]+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_badge_by_key'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('jph/v1', '/badges/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_badges_stats'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // User badges endpoint - LOGGED IN USERS ONLY
        register_rest_route('jph/v1', '/badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_user_badges'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Create default badges endpoint
        register_rest_route('jph/v1', '/create-default-badges', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_create_default_badges'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Badge debug information endpoint removed - debug section no longer needed
        
        // Run migrations endpoint
        register_rest_route('jph/v1', '/run-migrations', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_run_migrations'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Reset badge counts endpoint
        register_rest_route('jph/v1', '/reset-badge-counts', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_reset_badge_counts'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Export practice history endpoint
        register_rest_route('jph/v1', '/export-practice-history', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_export_practice_history'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        
        // Debug endpoints
        register_rest_route('jph/v1', '/debug-info', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_info'),
            'permission_callback' => '__return_true'
        ));
        
        
        
        
        // Check and award badges endpoint
        register_rest_route('jph/v1', '/check-badges', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_check_and_award_badges'),
            'permission_callback' => function() {
                return is_user_logged_in();
            }
        ));
        
        // Test badge awarding endpoint
        register_rest_route('jph/v1', '/test-badge-awarding', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_test_badge_awarding'),
            'permission_callback' => '__return_true'
        ));
        
        
        // Manual badge awarding endpoint
        register_rest_route('jph/v1', '/award-badge', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_award_badge_manually'),
            'permission_callback' => '__return_true'
        ));
        
        // Test badge FluentCRM event endpoint
        register_rest_route('jph/v1', '/test-badge-event', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_test_badge_event'),
            'permission_callback' => '__return_true'
        ));
        
        // Run migrations endpoint
        register_rest_route('jph/v1', '/run-migrations', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_run_migrations'),
            'permission_callback' => '__return_true'
        ));
        
        
        // Sync badge count endpoint
        register_rest_route('jph/v1', '/sync-badge-count', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_sync_badge_count'),
            'permission_callback' => '__return_true'
        ));
        
        
        // Sync all badge counts endpoint
        register_rest_route('jph/v1', '/sync-all-badge-counts', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_sync_all_badge_counts'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        
        // Direct badge awarding endpoint
        register_rest_route('jph/v1', '/award-first-steps', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_award_first_steps'),
            'permission_callback' => '__return_true'
        ));
        
        // Database operations endpoints
        register_rest_route('jph/v1', '/database/create-tables', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_create_tables'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('jph/v1', '/database/check-tables', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_check_tables'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('jph/v1', '/database/schema', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_show_schema'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Public test endpoint for database operations (no auth required)
        register_rest_route('jph/v1', '/database/test-create', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_test_create_tables'),
            'permission_callback' => '__return_true'
        ));
        
        // Public test endpoint to check tables (no auth required)
        register_rest_route('jph/v1', '/database/test-check', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_test_check_tables'),
            'permission_callback' => '__return_true'
        ));
        
        // CRUD operations endpoints (no auth required for testing)
        register_rest_route('jph/v1', '/practice-items', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_practice_items'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jph/v1', '/practice-items', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_add_practice_item'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jph/v1', '/practice-sessions', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_log_practice_session'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jph/v1', '/practice-sessions', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_practice_sessions'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jph/v1', '/practice-sessions/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_practice_session'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jph/v1', '/user-stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_user_stats'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jph/v1', '/test-gamification', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_test_gamification'),
            'permission_callback' => '__return_true'
        ));
        
        // Lesson Favorites endpoints
        register_rest_route('jph/v1', '/lesson-favorites', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_lesson_favorites'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/lesson-favorites', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_add_lesson_favorite'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jph/v1', '/lesson-favorites/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_lesson_favorite'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                )
            )
        ));
        
        register_rest_route('jph/v1', '/lesson-favorites/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_lesson_favorite'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                )
            )
        ));

        // Oxygen Builder endpoint for saving lesson favorites
        register_rest_route('jph/v1', '/save-lesson-favorite', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_save_lesson_favorite_from_page'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'title' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return !empty($param) && is_string($param);
                    }
                ),
                'url' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return filter_var($param, FILTER_VALIDATE_URL) !== false;
                    }
                ),
                'category' => array(
                    'required' => false,
                    'default' => 'lesson',
                    'validate_callback' => function($param, $request, $key) {
                        $valid_categories = array('lesson', 'technique', 'theory', 'ear-training', 'repertoire', 'improvisation', 'other');
                        return in_array($param, $valid_categories);
                    }
                ),
                'description' => array(
                    'required' => false,
                    'default' => '',
                    'validate_callback' => function($param, $request, $key) {
                        return is_string($param);
                    }
                )
            )
        ));
        
        // Check if lesson favorite exists
        register_rest_route('jph/v1', '/check-lesson-favorite', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_check_lesson_favorite'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'title' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return !empty($param) && is_string($param);
                    }
                ),
                'url' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return filter_var($param, FILTER_VALIDATE_URL) !== false;
                    }
                )
            )
        ));
        
        // Practice item management endpoints
        register_rest_route('jph/v1', '/practice-items/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_practice_item'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jph/v1', '/practice-items/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_practice_item'),
            'permission_callback' => '__return_true'
        ));
        
    }
    
    /**
     * Check admin permission for REST API
     */
    public function check_admin_permission($request) {
        return current_user_can('manage_options');
    }
    
    /**
     * Check if user has permission to access lesson favorites
     */
    public function check_user_permission($request) {
        return is_user_logged_in();
    }
    
    /**
     * REST API test endpoint
     */
    public function rest_test($request) {
        return rest_ensure_response(array(
            'status' => 'success',
            'message' => 'JazzEdge Practice Hub REST API is working!',
            'timestamp' => current_time('mysql'),
            'version' => JPH_VERSION,
            'katahdin_hub_available' => function_exists('katahdin_ai_hub'),
            'next_steps' => array(
                'Create database tables',
                'Implement CRUD operations',
                'Add Katahdin AI integration',
                'Build student dashboard'
            )
        ));
    }
    
    /**
     * Debug information endpoint
     */
    public function rest_debug_info($request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('not_logged_in', 'You must be logged in to view debug information', array('status' => 401));
        }
        
        $database = new JPH_Database();
        $gamification = new JPH_Gamification();
        
        // Get user stats
        $user_stats = $gamification->get_user_stats($user_id);
        
        // Get user badges
        $user_badges = $database->get_user_badges($user_id);
        
        // Debug: Log the user badges query results
        error_log("JPH Debug: rest_debug_info - user_id: $user_id");
        error_log("JPH Debug: rest_debug_info - user_badges count: " . count($user_badges));
        if (!empty($user_badges)) {
            error_log("JPH Debug: rest_debug_info - first user badge: " . print_r($user_badges[0], true));
        }
        
        // Get all badges
        $all_badges = $database->get_badges(true);
        
        // Get practice sessions
        $practice_sessions = $database->get_practice_sessions($user_id, 5);
        
        // Check database tables
        global $wpdb;
        $tables = array(
            'jph_badges' => $wpdb->prefix . 'jph_badges',
            'jph_user_badges' => $wpdb->prefix . 'jph_user_badges',
            'jph_user_stats' => $wpdb->prefix . 'jph_user_stats',
            'jph_practice_sessions' => $wpdb->prefix . 'jph_practice_sessions'
        );
        
        $table_info = array();
        foreach ($tables as $name => $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
            $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $table") : 0;
            $table_info[$name] = array(
                'exists' => $exists,
                'count' => $count,
                'table_name' => $table
            );
        }
        
        // Gather data from ALL tables
        $all_tables_data = array();
        
        // Get data from all tables
        $all_tables_data['jph_badges'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_badges ORDER BY display_order ASC", ARRAY_A);
        $all_tables_data['jph_user_badges'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_user_badges WHERE user_id = $user_id ORDER BY earned_at DESC", ARRAY_A);
        $all_tables_data['jph_user_stats'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_user_stats WHERE user_id = $user_id", ARRAY_A);
        $all_tables_data['jph_practice_sessions'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_practice_sessions WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 10", ARRAY_A);
        $all_tables_data['jph_practice_items'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_practice_items WHERE user_id = $user_id ORDER BY sort_order ASC", ARRAY_A);
        $all_tables_data['jph_gems_transactions'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_gems_transactions WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 20", ARRAY_A);
        $all_tables_data['jph_lesson_favorites'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_lesson_favorites WHERE user_id = $user_id ORDER BY created_at DESC", ARRAY_A);
        
        // Build debug HTML with accordion
        $debug_html = '<div style="font-family: monospace; font-size: 11px; line-height: 1.4;">';
        
        // Add CSS for accordions
        $debug_html .= '<style>
            .debug-accordion { margin: 10px 0; }
            .accordion-header { 
                background: #0073aa; 
                color: white; 
                padding: 8px 12px; 
                cursor: pointer; 
                border-radius: 4px 4px 0 0;
                margin: 0;
                display: block;
                width: 100%;
                text-align: left;
                font-weight: bold;
                border: none;
            }
            .accordion-header:hover { background: #005a87; }
            .accordion-content { 
                background: #f8f9fa; 
                border: 1px solid #dee2e6; 
                border-top: none; 
                padding: 15px; 
                display: none;
                border-radius: 0 0 4px 4px;
            }
            .accordion-content.active { display: block; }
            .accordion-content pre { 
                background: #fff; 
                padding: 10px; 
                border-radius: 4px; 
                overflow-x: auto; 
                max-height: 300px; 
                border: 1px solid #ddd;
            }
            .debug-table-count { font-size: 10px; color: #666; margin-left: 10px; }
            .debug-section-title { color: #0073aa; margin: 20px 0 10px 0; font-size: 14px; font-weight: bold; }
        </style>';
        
        $debug_html .= '<h5 style="color: #0073aa; margin: 10px 0 5px 0;">👤 User Information</h5>';
        $debug_html .= '<p><strong>User ID:</strong> ' . $user_id . '</p>';
        $debug_html .= '<p><strong>Is Admin:</strong> ' . (current_user_can('manage_options') ? 'Yes' : 'No') . '</p>';
        
        // Accordion sections for all table data
        $accordion_sections = array(
            'user_stats' => array('icon' => '📊', 'title' => 'User Stats', 'data' => $all_tables_data['jph_user_stats']),
            'user_badges' => array('icon' => '🏆', 'title' => 'User Badges', 'data' => $all_tables_data['jph_user_badges']),
            'available_badges' => array('icon' => '🎖️', 'title' => 'All Available Badges', 'data' => $all_tables_data['jph_badges']),
            'practice_sessions' => array('icon' => '📝', 'title' => 'Recent Practice Sessions', 'data' => $all_tables_data['jph_practice_sessions']),
            'practice_items' => array('icon' => '🎯', 'title' => 'Practice Items', 'data' => $all_tables_data['jph_practice_items']),
            'gems_transactions' => array('icon' => '💎', 'title' => 'Gems Transactions', 'data' => $all_tables_data['jph_gems_transactions']),
            'lesson_favorites' => array('icon' => '⭐', 'title' => 'Lesson Favorites', 'data' => $all_tables_data['jph_lesson_favorites'])
        );
        
        $debug_html .= '<div class="debug-section-title">📋 All Table Data</div>';
        foreach ($accordion_sections as $key => $section) {
            $count = count($section['data']);
            $debug_html .= '<div class="debug-accordion">';
            $debug_html .= '<button class="accordion-header" onclick="jphToggleAccordion(\'' . $key . '\')">';
            $debug_html .= $section['icon'] . ' ' . $section['title'] . '<span class="debug-table-count">(' . $count . ' entries)</span>';
            $debug_html .= '</button>';
            $debug_html .= '<div class="accordion-content" id="acc_' . $key . '">';
            
            if (!empty($section['data'])) {
                $debug_html .= '<pre>';
                $debug_html .= htmlspecialchars(print_r($section['data'], true));
            $debug_html .= '</pre>';
        } else {
                $debug_html .= '<p style="color: #d63638;">No data found</p>';
            }
            
            $debug_html .= '</div>';
            $debug_html .= '</div>';
        }
        
        $debug_html .= '<h5 style="color: #0073aa; margin: 15px 0 5px 0;">🗄️ Database Tables</h5>';
        foreach ($table_info as $name => $info) {
            $status = $info['exists'] ? '✅' : '❌';
            $debug_html .= '<p><strong>' . $name . ':</strong> ' . $status . ' ' . $info['table_name'] . ' (' . $info['count'] . ' rows)</p>';
        }
        
        // Add table structure validation
        $debug_html .= '<h5 style="color: #0073aa; margin: 15px 0 5px 0;">🔧 Table Structure Validation</h5>';
        $table_validation = $this->validate_table_structures();
        foreach ($table_validation as $table_name => $validation) {
            $status_icon = $validation['valid'] ? '✅' : '❌';
            $debug_html .= '<p><strong>' . $table_name . ':</strong> ' . $status_icon . ' ' . ($validation['valid'] ? 'Structure OK' : 'Structure Issues') . '</p>';
            if (!$validation['valid']) {
                $debug_html .= '<ul style="margin: 5px 0 10px 20px; color: #d63638;">';
                foreach ($validation['issues'] as $issue) {
                    $debug_html .= '<li>' . $issue . '</li>';
                }
                $debug_html .= '</ul>';
            }
        }
        
        $debug_html .= '<h5 style="color: #0073aa; margin: 15px 0 5px 0;">🔍 Badge System Analysis</h5>';
        
        // Check for badge_id vs badge_key issues
        if (!empty($all_badges)) {
            $badge_keys = array();
            $badge_ids = array();
            foreach ($all_badges as $badge) {
                if (isset($badge['badge_key'])) $badge_keys[] = $badge['badge_key'];
                if (isset($badge['id'])) $badge_ids[] = $badge['id'];
            }
            
            $debug_html .= '<p><strong>Badge Keys:</strong> ' . implode(', ', $badge_keys) . '</p>';
            $debug_html .= '<p><strong>Badge IDs:</strong> ' . implode(', ', $badge_ids) . '</p>';
            
            // Check user badges structure
            if (!empty($user_badges)) {
                $user_badge_keys = array();
                $user_badge_ids = array();
                foreach ($user_badges as $user_badge) {
                    if (isset($user_badge['badge_key'])) $user_badge_keys[] = $user_badge['badge_key'];
                    if (isset($user_badge['badge_id'])) $user_badge_ids[] = $user_badge['badge_id'];
                }
                $debug_html .= '<p><strong>User Badge Keys:</strong> ' . implode(', ', $user_badge_keys) . '</p>';
                $debug_html .= '<p><strong>User Badge IDs:</strong> ' . implode(', ', $user_badge_ids) . '</p>';
            }
        }
        
        // Add recent badge awards section
        $debug_html .= '<h5 style="color: #0073aa; margin: 15px 0 5px 0;">🏆 Recent Badge Awards</h5>';
        $debug_html .= '<p><em>Recent badge award events will appear here when badges are earned. Check the browser console for "New badges awarded" messages or look for badge award notifications.</em></p>';
        $debug_html .= '<div id="jph-badge-awards-debug" style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 4px; margin: 10px 0;">';
        $debug_html .= '<p style="margin: 0; color: #856404;">No recent badge awards detected. Try logging a practice session to trigger badge checks.</p>';
        $debug_html .= '</div>';
        
        // Add streak testing section
        $debug_html .= '<h5 style="color: #0073aa; margin: 15px 0 5px 0;">🔥 Streak Testing Tools</h5>';
        $debug_html .= '<div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 4px; margin: 10px 0;">';
        
        // Current streak info
        $current_streak = $user_stats['current_streak'] ?? 0;
        $longest_streak = $user_stats['longest_streak'] ?? 0;
        $shield_count = $user_stats['streak_shield_count'] ?? 0;
        $gems_balance = $user_stats['gems_balance'] ?? 0;
        
        $debug_html .= '<h6 style="margin: 0 0 10px 0; color: #495057;">Current Streak Status</h6>';
        $debug_html .= '<p style="margin: 5px 0;"><strong>Current Streak:</strong> ' . $current_streak . ' days</p>';
        $debug_html .= '<p style="margin: 5px 0;"><strong>Longest Streak:</strong> ' . $longest_streak . ' days</p>';
        $debug_html .= '<p style="margin: 5px 0;"><strong>Active Shields:</strong> ' . $shield_count . '/3</p>';
        $debug_html .= '<p style="margin: 5px 0;"><strong>Gems Balance:</strong> ' . $gems_balance . ' 💎</p>';
        
        // Streak testing buttons
        $debug_html .= '<h6 style="margin: 15px 0 10px 0; color: #495057;">Test Actions</h6>';
        $debug_html .= '<div style="display: flex; flex-wrap: wrap; gap: 10px; margin: 10px 0;">';
        
        // Purchase shield button
        if ($shield_count < 3) {
            $debug_html .= '<button type="button" class="button button-secondary" onclick="jphTestPurchaseShield()" style="margin: 2px;">Test Purchase Shield (50 💎)</button>';
        } else {
            $debug_html .= '<button type="button" class="button button-secondary" disabled style="margin: 2px;">Max Shields (3)</button>';
        }
        
        // Test auto-shield button
        $debug_html .= '<button type="button" class="button button-primary" onclick="jphTestAutoShield()" style="margin: 2px;">Test Auto-Shield</button>';
        
        // Streak recovery buttons (only show if streak is 0)
        if ($current_streak === 0) {
            $debug_html .= '<button type="button" class="button button-primary" onclick="jphTestRepairStreak(1)" style="margin: 2px;">Test Repair 1 Day (25 💎)</button>';
            $debug_html .= '<button type="button" class="button button-primary" onclick="jphTestRepairStreak(3)" style="margin: 2px;">Test Repair 3 Days (75 💎)</button>';
        }
        
        // Reset streak button (for testing)
        $debug_html .= '<button type="button" class="button button-secondary" onclick="jphTestResetStreak()" style="margin: 2px; background: #dc3545; color: white;">Reset Streak (Test)</button>';
        
        // Add gems button (for testing)
        $debug_html .= '<button type="button" class="button button-secondary" onclick="jphTestAddGems(100)" style="margin: 2px; background: #28a745; color: white;">Add 100 Gems (Test)</button>';
        
        $debug_html .= '</div>';
        
        // Test results area
        $debug_html .= '<h6 style="margin: 15px 0 10px 0; color: #495057;">Test Results</h6>';
        $debug_html .= '<div id="jph-streak-test-results" style="background: #e9ecef; border: 1px solid #ced4da; padding: 10px; border-radius: 4px; min-height: 50px; font-size: 12px;">';
        $debug_html .= '<p style="margin: 0; color: #6c757d;">Test results will appear here...</p>';
        $debug_html .= '</div>';
        
        $debug_html .= '</div>';
        
        
        
        $debug_html .= '</div>';
        
        return rest_ensure_response(array(
            'success' => true,
            'debug_html' => $debug_html,
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Validate table structures against expected schema
     */
    private function validate_table_structures() {
        global $wpdb;
        
        $validation_results = array();
        
        // Expected table structures
        $expected_structures = array(
            'jph_badges' => array(
                'required_columns' => array('badge_key', 'name', 'description', 'icon', 'category', 'rarity', 'xp_reward', 'gem_reward', 'criteria_type', 'criteria_value', 'is_active', 'created_at'),
                'primary_key' => 'badge_key',
                'forbidden_columns' => array('id') // Should not have id column anymore
            ),
            'jph_user_badges' => array(
                'required_columns' => array('user_id', 'badge_key', 'earned_at'),
                'primary_key' => array('user_id', 'badge_key'),
                'forbidden_columns' => array('id', 'badge_id', 'badge_name', 'badge_description', 'badge_icon', 'earned_date') // Old structure columns
            ),
            'jph_practice_items' => array(
                'required_columns' => array('id', 'user_id', 'name', 'category', 'description', 'is_active', 'created_at', 'updated_at'),
                'primary_key' => 'id'
            ),
            'jph_practice_sessions' => array(
                'required_columns' => array('id', 'user_id', 'practice_item_id', 'duration_minutes', 'sentiment_score', 'improvement_detected', 'notes', 'ai_analysis', 'xp_earned', 'session_hash', 'created_at'),
                'primary_key' => 'id'
            ),
            'jph_user_stats' => array(
                'required_columns' => array('id', 'user_id', 'total_xp', 'current_level', 'total_sessions', 'current_streak', 'longest_streak', 'badges_earned', 'gems_balance', 'streak_shield_count', 'last_practice_date', 'created_at', 'updated_at'),
                'primary_key' => 'id'
            ),
            'jph_gems_transactions' => array(
                'required_columns' => array('id', 'user_id', 'transaction_type', 'amount', 'source', 'description', 'balance_after', 'created_at'),
                'primary_key' => 'id'
            ),
            'jph_lesson_favorites' => array(
                'required_columns' => array('id', 'user_id', 'title', 'url', 'category', 'description', 'created_at', 'updated_at'),
                'primary_key' => 'id'
            )
        );
        
        foreach ($expected_structures as $table_name => $expected) {
            $full_table_name = $wpdb->prefix . $table_name;
            $validation_results[$table_name] = array(
                'valid' => true,
                'issues' => array()
            );
            
            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$full_table_name}'");
            if (!$table_exists) {
                $validation_results[$table_name]['valid'] = false;
                $validation_results[$table_name]['issues'][] = "Table does not exist";
                continue;
            }
            
            // Get actual table structure
            $columns = $wpdb->get_results("SHOW COLUMNS FROM {$full_table_name}", ARRAY_A);
            $actual_columns = array_column($columns, 'Field');
            
            // Check for required columns
            foreach ($expected['required_columns'] as $required_column) {
                if (!in_array($required_column, $actual_columns)) {
                    $validation_results[$table_name]['valid'] = false;
                    $validation_results[$table_name]['issues'][] = "Missing required column: {$required_column}";
                }
            }
            
            // Check for forbidden columns (old structure)
            foreach ($expected['forbidden_columns'] as $forbidden_column) {
                if (in_array($forbidden_column, $actual_columns)) {
                    $validation_results[$table_name]['valid'] = false;
                    $validation_results[$table_name]['issues'][] = "Has forbidden column (old structure): {$forbidden_column}";
                }
            }
            
            // Check primary key
            $key_info = $wpdb->get_results("SHOW KEYS FROM {$full_table_name} WHERE Key_name = 'PRIMARY'", ARRAY_A);
            $primary_keys = array_column($key_info, 'Column_name');
            
            if (is_array($expected['primary_key'])) {
                // Composite primary key
                $expected_pk = $expected['primary_key'];
                sort($expected_pk);
                sort($primary_keys);
                if ($expected_pk !== $primary_keys) {
                    $validation_results[$table_name]['valid'] = false;
                    $validation_results[$table_name]['issues'][] = "Primary key mismatch. Expected: " . implode(',', $expected['primary_key']) . ", Found: " . implode(',', $primary_keys);
                }
            } else {
                // Single primary key
                if (!in_array($expected['primary_key'], $primary_keys)) {
                    $validation_results[$table_name]['valid'] = false;
                    $validation_results[$table_name]['issues'][] = "Primary key mismatch. Expected: {$expected['primary_key']}, Found: " . implode(',', $primary_keys);
                }
            }
        }
        
        return $validation_results;
    }
    
    /**
     * REST API: Create tables
     */
    public function rest_create_tables($request) {
        try {
            $database = new JPH_Database();
            $result = $database->create_tables();
            
            if ($result) {
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Database tables created successfully!',
                    'timestamp' => current_time('mysql')
                ));
            } else {
                return new WP_Error('table_creation_failed', 'Failed to create database tables', array('status' => 500));
            }
        } catch (Exception $e) {
            return new WP_Error('table_creation_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Check tables
     */
    public function rest_check_tables($request) {
        try {
            $database = new JPH_Database();
            $tables_exist = $database->tables_exist();
            $table_names = $database->get_table_names();
            
            $result = array(
                'tables_exist' => $tables_exist,
                'table_names' => $table_names,
                'timestamp' => current_time('mysql')
            );
            
            return rest_ensure_response($result);
        } catch (Exception $e) {
            return new WP_Error('table_check_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Show schema
     */
    public function rest_show_schema($request) {
        try {
            require_once JPH_PLUGIN_PATH . 'includes/database-schema.php';
            $schema = JPH_Database_Schema::get_schema();
            
            $result = array(
                'schema' => $schema,
                'timestamp' => current_time('mysql')
            );
            
            return rest_ensure_response($result);
        } catch (Exception $e) {
            return new WP_Error('schema_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Test create tables (no auth required)
     */
    public function rest_test_create_tables($request) {
        try {
            $database = new JPH_Database();
            $result = $database->create_tables();
            
            if ($result) {
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Database tables created successfully!',
                    'timestamp' => current_time('mysql'),
                    'note' => 'This is a test endpoint - no authentication required'
                ));
            } else {
                return new WP_Error('table_creation_failed', 'Failed to create database tables', array('status' => 500));
            }
        } catch (Exception $e) {
            return new WP_Error('table_creation_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Test check tables (no auth required)
     */
    public function rest_test_check_tables($request) {
        try {
            $database = new JPH_Database();
            $tables_exist = $database->tables_exist();
            $table_names = $database->get_table_names();
            
            $result = array(
                'tables_exist' => $tables_exist,
                'table_names' => $table_names,
                'timestamp' => current_time('mysql'),
                'note' => 'This is a test endpoint - no authentication required'
            );
            
            return rest_ensure_response($result);
        } catch (Exception $e) {
            return new WP_Error('table_check_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Documentation page
     */
    public function documentation_page() {
        $file = isset($_GET['file']) ? sanitize_text_field($_GET['file']) : 'LESSON-FAVORITES-GUIDE.md';
        $valid_files = array(
            'LESSON-FAVORITES-GUIDE.md' => 'Lesson Favorites Guide',
            'OXYGEN-BUILDER-CODE.md' => 'Oxygen Builder Code'
        );
        
        if (!array_key_exists($file, $valid_files)) {
            $file = 'LESSON-FAVORITES-GUIDE.md';
        }
        
        $file_path = plugin_dir_path(__FILE__) . $file;
        $content = file_exists($file_path) ? file_get_contents($file_path) : 'File not found.';
        
        ?>
        <div class="wrap">
            <h1>📚 Documentation</h1>
            
            <!-- File Navigation -->
            <div class="jph-doc-nav">
                <h2>Available Documentation:</h2>
                <div class="jph-doc-links">
                    <?php foreach ($valid_files as $filename => $title): ?>
                        <a href="<?php echo admin_url('admin.php?page=jph-documentation&file=' . $filename); ?>" 
                           class="jph-doc-link <?php echo ($file === $filename) ? 'active' : ''; ?>">
                            📄 <?php echo esc_html($title); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Documentation Content -->
            <div class="jph-doc-content">
                <div class="jph-doc-header">
                    <h2><?php echo esc_html($valid_files[$file]); ?></h2>
                    <div class="jph-doc-actions">
                        <button onclick="toggleDocView()" class="jph-btn jph-btn-secondary">
                            <span id="view-toggle-text">Raw Markdown</span>
                        </button>
                        <a href="<?php echo plugin_dir_url(__FILE__) . $file; ?>" target="_blank" class="jph-btn jph-btn-secondary">
                            📥 Download
                        </a>
                    </div>
                </div>
                
                <div id="jph-doc-rendered" class="jph-doc-rendered">
                    <?php echo $this->render_markdown($content); ?>
                </div>
                
                <div id="jph-doc-raw" class="jph-doc-raw" style="display: none;">
                    <pre><code><?php echo esc_html($content); ?></code></pre>
                </div>
            </div>
        </div>
        
        <style>
        .jph-doc-nav {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .jph-doc-nav h2 {
            margin: 0 0 15px 0;
            color: #2A3940;
            font-size: 16px;
        }
        
        .jph-doc-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .jph-doc-link {
            display: inline-block;
            padding: 8px 16px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            text-decoration: none;
            color: #2A3940;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .jph-doc-link:hover {
            background: #e9ecef;
            border-color: #0073aa;
        }
        
        .jph-doc-link.active {
            background: #0073aa;
            color: white;
            border-color: #0073aa;
        }
        
        .jph-doc-content {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .jph-doc-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .jph-doc-header h2 {
            margin: 0;
            color: #2A3940;
            font-size: 18px;
        }
        
        .jph-doc-actions {
            display: flex;
            gap: 12px;
        }
        
        .jph-doc-rendered {
            padding: 30px;
            line-height: 1.6;
            color: #2A3940;
        }
        
        .jph-doc-rendered h1,
        .jph-doc-rendered h2,
        .jph-doc-rendered h3,
        .jph-doc-rendered h4 {
            color: #2A3940;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        .jph-doc-rendered h1 {
            font-size: 28px;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        
        .jph-doc-rendered h2 {
            font-size: 22px;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 8px;
        }
        
        .jph-doc-rendered h3 {
            font-size: 18px;
        }
        
        .jph-doc-rendered h4 {
            font-size: 16px;
        }
        
        .jph-doc-rendered p {
            margin-bottom: 15px;
        }
        
        .jph-doc-rendered ul,
        .jph-doc-rendered ol {
            margin-bottom: 15px;
            padding-left: 25px;
        }
        
        .jph-doc-rendered li {
            margin-bottom: 8px;
        }
        
        .jph-doc-rendered code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #d63384;
        }
        
        .jph-doc-rendered pre {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            overflow-x: auto;
            margin-bottom: 20px;
        }
        
        .jph-doc-rendered pre code {
            background: none;
            padding: 0;
            color: #2A3940;
            font-size: 14px;
        }
        
        .jph-doc-rendered blockquote {
            border-left: 4px solid #0073aa;
            padding-left: 20px;
            margin: 20px 0;
            color: #666;
            font-style: italic;
        }
        
        .jph-doc-rendered table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .jph-doc-rendered th,
        .jph-doc-rendered td {
            border: 1px solid #e9ecef;
            padding: 12px;
            text-align: left;
        }
        
        .jph-doc-rendered th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .jph-doc-raw {
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #ddd;
        }
        
        .jph-doc-raw pre {
            margin: 0;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            overflow-x: auto;
        }
        
        .jph-doc-raw code {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.4;
            color: #2A3940;
        }
        </style>
        
        <script>
        function toggleDocView() {
            const rendered = document.getElementById('jph-doc-rendered');
            const raw = document.getElementById('jph-doc-raw');
            const toggleText = document.getElementById('view-toggle-text');
            
            if (rendered.style.display === 'none') {
                rendered.style.display = 'block';
                raw.style.display = 'none';
                toggleText.textContent = 'Raw Markdown';
            } else {
                rendered.style.display = 'none';
                raw.style.display = 'block';
                toggleText.textContent = 'Rendered View';
            }
        }
        </script>
        <?php
    }
    
    /**
     * Render markdown content
     */
    private function render_markdown($content) {
        // Simple markdown rendering
        $content = htmlspecialchars($content);
        
        // Headers
        $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);
        $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^#### (.*$)/m', '<h4>$1</h4>', $content);
        
        // Bold and italic
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        
        // Code blocks
        $content = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $content);
        $content = preg_replace('/`(.*?)`/', '<code>$1</code>', $content);
        
        // Lists
        $content = preg_replace('/^\- (.*$)/m', '<li>$1</li>', $content);
        $content = preg_replace('/^(\d+)\. (.*$)/m', '<li>$2</li>', $content);
        
        // Wrap consecutive list items in ul/ol
        $content = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $content);
        
        // Links
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $content);
        
        // Paragraphs
        $content = preg_replace('/^(?!<[h1-6]|<ul|<ol|<pre|<li)(.*)$/m', '<p>$1</p>', $content);
        
        // Clean up empty paragraphs
        $content = preg_replace('/<p><\/p>/', '', $content);
        $content = preg_replace('/<p>\s*<\/p>/', '', $content);
        
        return $content;
    }
    
    /**
     * Email Templates admin page
     */
    public function email_templates_page() {
        $file_path = plugin_dir_path(__FILE__) . 'EMAIL-TEMPLATES.md';
        $content = file_exists($file_path) ? file_get_contents($file_path) : 'Email templates file not found.';
        
        // Parse email templates
        $templates = $this->parse_email_templates($content);
        
        ?>
        <div class="wrap">
            <h1>📧 Email Templates</h1>
            <p>Copy and paste these email templates for your milestone campaigns. Each template includes a subject line and body content.</p>
            
            <div class="jph-email-templates">
                <?php foreach ($templates as $category => $category_templates): ?>
                    <div class="jph-email-category">
                        <h2><?php echo esc_html($category); ?></h2>
                        <div class="jph-email-grid">
                            <?php foreach ($category_templates as $template): ?>
                                <div class="jph-email-template">
                                    <div class="jph-email-header">
                                        <h3><?php echo esc_html($template['title']); ?></h3>
                                        <div class="jph-email-actions">
                                            <button class="jph-copy-btn" onclick="copyToClipboard('subject-<?php echo esc_attr($template['id']); ?>')" title="Copy Subject">
                                                📋 Subject
                                            </button>
                                            <button class="jph-copy-btn" onclick="copyToClipboard('body-<?php echo esc_attr($template['id']); ?>')" title="Copy Body">
                                                📋 Body
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="jph-email-content">
                                        <div class="jph-email-subject">
                                            <label>Subject Line:</label>
                                            <div class="jph-copy-container">
                                                <input type="text" id="subject-<?php echo esc_attr($template['id']); ?>" 
                                                       value="<?php echo esc_attr($template['subject']); ?>" 
                                                       readonly class="jph-copy-input">
                                            </div>
                                        </div>
                                        
                                        <div class="jph-email-body">
                                            <label>Email Body:</label>
                                            <div class="jph-copy-container">
                                                <textarea id="body-<?php echo esc_attr($template['id']); ?>" 
                                                          readonly class="jph-copy-textarea"><?php echo esc_textarea($template['body']); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <style>
        .jph-email-templates {
            margin-top: 20px;
        }
        
        .jph-email-category {
            margin-bottom: 40px;
        }
        
        .jph-email-category h2 {
            color: #2A3940;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .jph-email-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
        }
        
        .jph-email-template {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-email-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .jph-email-header h3 {
            margin: 0;
            color: #2A3940;
            font-size: 18px;
        }
        
        .jph-email-actions {
            display: flex;
            gap: 8px;
        }
        
        .jph-copy-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s;
        }
        
        .jph-copy-btn:hover {
            background: #5a6fd8;
        }
        
        .jph-email-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .jph-email-subject,
        .jph-email-body {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .jph-email-subject label,
        .jph-email-body label {
            font-weight: 600;
            color: #2A3940;
            font-size: 14px;
        }
        
        .jph-copy-container {
            position: relative;
        }
        
        .jph-copy-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background: #f8f9fa;
        }
        
        .jph-copy-textarea {
            width: 100%;
            height: 200px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            font-family: monospace;
            background: #f8f9fa;
            resize: vertical;
        }
        
        .jph-copy-success {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .jph-copy-success.show {
            opacity: 1;
        }
        
        @media (max-width: 768px) {
            .jph-email-grid {
                grid-template-columns: 1fr;
            }
            
            .jph-email-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
        </style>
        
        <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            element.select();
            element.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                
                // Show success message
                const successMsg = document.createElement('div');
                successMsg.className = 'jph-copy-success show';
                successMsg.textContent = 'Copied!';
                element.parentNode.appendChild(successMsg);
                
                setTimeout(() => {
                    successMsg.remove();
                }, 2000);
                
            } catch (err) {
                console.error('Failed to copy: ', err);
                alert('Failed to copy to clipboard');
            }
        }
        </script>
        <?php
    }
    
    /**
     * Parse email templates from markdown content
     */
    private function parse_email_templates($content) {
        $templates = array();
        $current_category = '';
        $current_template = null;
        $template_id = 0;
        $in_code_block = false;
        $code_content = '';
        
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Category headers
            if (preg_match('/^## (.+)$/', $line, $matches)) {
                $current_category = $matches[1];
                if (!isset($templates[$current_category])) {
                    $templates[$current_category] = array();
                }
                continue;
            }
            
            // Template headers
            if (preg_match('/^### (.+)$/', $line, $matches)) {
                // Save previous template
                if ($current_template) {
                    $templates[$current_category][] = $current_template;
                }
                
                // Start new template
                $current_template = array(
                    'id' => ++$template_id,
                    'title' => $matches[1],
                    'subject' => '',
                    'body' => ''
                );
                continue;
            }
            
            // Subject line
            if (preg_match('/^\*\*Subject:\*\* (.+)$/', $line, $matches)) {
                if ($current_template) {
                    $current_template['subject'] = $matches[1];
                }
                continue;
            }
            
            // Code block start
            if ($line === '```') {
                if (!$in_code_block) {
                    $in_code_block = true;
                    $code_content = '';
                } else {
                    // Code block end
                    if ($current_template && $code_content) {
                        $current_template['body'] = trim($code_content);
                    }
                    $in_code_block = false;
                    $code_content = '';
                }
                continue;
            }
            
            // Collect code block content
            if ($in_code_block) {
                $code_content .= $line . "\n";
            }
        }
        
        // Save last template
        if ($current_template) {
            $templates[$current_category][] = $current_template;
        }
        
        return $templates;
    }
    
    /**
     * Event Tracking admin page
     */
    public function events_page() {
        ?>
        <div class="wrap">
            <h1>🔗 FluentCRM Event Tracking</h1>
            <p>Monitor FluentCRM event tracking from badge achievements and manage event logging.</p>
            
            <div class="jph-event-sections">
                    
                    <!-- Badge Event Information -->
                    <div class="jph-event-section">
                        <h2>🏆 Badge Event Configuration</h2>
                        <p>Badge events are now configured directly within each badge in the <a href="<?php echo admin_url('admin.php?page=jph-badges'); ?>">Badge Management</a> section.</p>
                        
                        <div class="badge-info-grid">
                            <div class="badge-info-item">
                                <h3>✅ Enabled Badges</h3>
                                <p>Badges with FluentCRM tracking enabled will automatically fire events when earned.</p>
                                        </div>
                            <div class="badge-info-item">
                                <h3>🔧 Individual Configuration</h3>
                                <p>Each badge can have its own custom event key and title for FluentCRM.</p>
                                    </div>
                            <div class="badge-info-item">
                                <h3>⚡ Automatic Tracking</h3>
                                <p>Events are fired automatically when badges are awarded to users.</p>
                                </div>
                            </div>
                            
                        <div style="margin-top: 20px; padding: 15px; background: #f0f8ff; border-left: 4px solid #007cba; border-radius: 4px;">
                            <strong>💡 Note:</strong> To configure event tracking for badges, go to <strong>Badge Management</strong> and enable "FluentCRM Event Tracking" for individual badges. This gives you granular control over which badge achievements trigger events.
                        </div>
                    </div>
                    
                    <!-- Event Tracking Testing -->
                    <div class="jph-event-section">
                        <h2>🧪 Event Tracking Testing</h2>
                        <p>Test your FluentCRM event tracking to ensure it's working correctly.</p>
                        
                        <div class="event-test-buttons">
                            <button type="button" class="button button-primary" onclick="testBadgeEvent('first_steps')">
                                🏆 Test First Steps Badge
                            </button>
                            <button type="button" class="button button-secondary" onclick="testBadgeEvent('marathon')">
                                🏆 Test Marathon Badge
                            </button>
                            <button type="button" class="button button-secondary" onclick="testBadgeEvent('streak_protector')">
                                🏆 Test Streak Protector Badge
                            </button>
                            <button type="button" class="button button-secondary" onclick="testAllBadgeEvents()">
                                🏆 Test All Badge Events
                            </button>
                        </div>
                        
                        <div id="webhook-test-results" class="webhook-test-results"></div>
                    </div>
                    
                    <div class="jph-debug-section">
                        <h2>🔍 Badge Assignment Debugging</h2>
                        <p>Comprehensive tools to debug and test badge assignment logic.</p>
                        
                        <div class="badge-debug-controls">
                            <h3>User Badge Status Check</h3>
                            <div class="debug-form-group">
                                <label for="debug-user-id">User ID:</label>
                                <input type="number" id="debug-user-id" value="<?php echo get_current_user_id(); ?>" min="1">
                                <button type="button" onclick="checkUserBadgeStatus()" class="button button-primary">
                                    🔍 Check Badge Status
                                </button>
                            </div>
                            
                            <h3>Badge Assignment Testing</h3>
                            <div class="debug-form-group">
                                <button type="button" onclick="runBadgeAssignmentTest()" class="button button-primary">
                                    ⚡ Run Badge Assignment Test
                                </button>
                                <button type="button" onclick="debugMarathonBadge()" class="button button-secondary">
                                    🏃 Debug Marathon Badge
                                </button>
                                <button type="button" onclick="simulateBadgeCheck()" class="button button-secondary">
                                    🎯 Simulate Badge Check
                                </button>
                            </div>
                            
                            <h3>Database Inspection</h3>
                            <div class="debug-form-group">
                                <button type="button" onclick="inspectBadgeDatabase()" class="button button-primary">
                                    📊 Inspect Badge Database
                                </button>
                                <button type="button" onclick="checkPracticeSessions()" class="button button-secondary">
                                    ⏱️ Check Practice Sessions
                                </button>
                            </div>
                        </div>
                        
                        <div id="badge-debug-results" class="badge-debug-results"></div>
                    </div>
                    
                    <div class="jph-debug-section">
                        <h2>🎵 Practice Items Debugging</h2>
                        <p>Inspect practice items in the database and troubleshoot display issues.</p>
                        
                        <div class="badge-debug-controls">
                            <h3>Database Inspection</h3>
                            <div class="debug-form-group">
                                <button type="button" onclick="debugPracticeItems()" class="button button-primary">
                                    🔍 Debug Practice Items Database
                                </button>
                                <button type="button" onclick="checkPracticeItemsQuery()" class="button button-secondary">
                                    📊 Test Database Query
                                </button>
                                <button type="button" onclick="showAllPracticeItems()" class="button button-secondary">
                                    📝 Show All Items
                                </button>
                            </div>
                            
                            <h3>Force Refresh</h3>
                            <div class="debug-form-group">
                                <button type="button" onclick="forceRefreshPracticeItems()" class="button button-primary">
                                    🔄 Force Refresh Practice Items
                                </button>
                            </div>
                        </div>
                        
                        <div id="practice-items-debug-results" class="badge-debug-results"></div>
                    </div>
                    
                    <!-- Event Tracking Logs -->
                    <div class="jph-webhook-section">
                        <h2>📋 Badge Event Tracking Logs</h2>
                        <p>View recent FluentCRM badge event activity and any tracking errors.</p>
                        
                        <div class="webhook-logs">
                            <div class="logs-controls">
                                <button type="button" class="button" onclick="refreshBadgeEventLogs()">🔄 Refresh Logs</button>
                                <button type="button" class="button button-secondary" onclick="clearBadgeEventLogs()">🗑️ Clear Logs</button>
                            </div>
                            <div id="badge-event-logs-content" class="webhook-logs-content">
                                <!-- Badge event logs will be loaded via AJAX -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- FluentCRM Event Tracking Logs -->
                    <div class="jph-webhook-section">
                        <h2>📋 FluentCRM Event Tracking Logs</h2>
                        <p>View and manage FluentCRM event tracking data from the wp_fc_event_tracking table.</p>
                        
                        <div class="webhook-logs">
                            <div class="logs-controls">
                                <button type="button" class="button" onclick="loadEventLogs()">🔄 Refresh Logs</button>
                                <button type="button" class="button button-secondary" onclick="emptyEventTrackingTable()">🗑️ Empty Event Tracking Table</button>
                            </div>
                            <div id="event-logs-results" class="webhook-logs-content">
                                <!-- Event logs will be loaded via AJAX -->
                            </div>
                        </div>
                    </div>
                    
                </div>
        </div>
        
        <style>
        .jph-webhook-sections {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .jph-webhook-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .jph-webhook-section h2 {
            margin: 0 0 15px 0;
            color: #2A3940;
            font-size: 18px;
        }
        
        .milestone-categories {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .milestone-category h3 {
            margin: 0 0 15px 0;
            color: #2A3940;
            font-size: 16px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 8px;
        }
        
        .milestone-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .milestone-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
        }
        
        .milestone-item label {
            font-weight: 500;
            color: #2A3940;
            display: block;
            margin-bottom: 8px;
        }
        
        .milestone-settings {
            font-size: 12px;
            color: #666;
        }
        
        .milestone-settings label {
            font-weight: normal;
            margin-bottom: 0;
        }
        
        .webhook-test-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .webhook-test-results {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            min-height: 100px;
            font-family: monospace;
            font-size: 12px;
            display: none;
        }
        
        /* Badge Debug Section */
        .jph-debug-section {
            margin: 30px 0;
            padding: 25px;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .badge-debug-controls {
            margin-bottom: 30px;
        }
        
        .badge-debug-controls h3 {
            margin: 25px 0 15px 0;
            color: #23282d;
            font-size: 16px;
            border-bottom: 2px solid #e1e1e1;
            padding-bottom: 8px;
        }
        
        .debug-form-group {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .debug-form-group label {
            font-weight: 600;
            color: #555;
            min-width: 80px;
        }
        
        .debug-form-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .badge-debug-results {
            margin-top: 25px;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-family: monospace;
            font-size: 13px;
            line-height: 1.5;
            max-height: 600px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
            display: none;
        }
        
        .webhook-logs {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
        }
        
        .logs-controls {
            margin-bottom: 15px;
        }
        
        .webhook-logs-content {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            max-height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
        
        .log-entry {
            margin-bottom: 10px;
            padding: 8px;
            border-radius: 4px;
            border-left: 4px solid #ddd;
        }
        
        .log-entry.success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .log-entry.error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        .log-entry.info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        
        .log-summary {
            background: #e9ecef;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .log-user-info {
            font-size: 11px;
            color: #666;
        }
        
        .log-entry.success .log-user-info {
            color: #28a745;
        }
        
        .log-entry.error .log-user-info {
            color: #dc3545;
        }
        
        .badge-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .badge-info-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .badge-info-item h3 {
            margin: 0 0 10px 0;
            color: #23282d;
            font-size: 16px;
            font-weight: 600;
        }
        
        .badge-info-item p {
            margin: 0;
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        </style>
        
        <script>
        function testBadgeEvent(badgeKey) {
            const resultsDiv = document.getElementById('webhook-test-results');
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = 'Testing badge event for: ' + badgeKey + '...';
            
            // AJAX call to test badge event tracking
            jQuery.post(ajaxurl, {
                action: 'jph_test_badge_event',
                badge_key: badgeKey,
                nonce: '<?php echo wp_create_nonce('jph_test_badge_event'); ?>'
            })
            .done(function(response) {
                console.log('Badge event test response:', response);
                if (response.success && response.data && response.data.message) {
                resultsDiv.innerHTML = '<strong>Test Result:</strong><br>' + response.data.message;
                } else if (response.data) {
                    resultsDiv.innerHTML = '<strong>Test Result:</strong><br>' + response.data;
                } else {
                    resultsDiv.innerHTML = '<strong>Test Result:</strong><br>Response: ' + JSON.stringify(response);
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Badge event test error:', xhr, status, error);
                resultsDiv.innerHTML = '<strong>Test Result:</strong><br>Error: ' + error + ' (Status: ' + status + ')';
            });
        }
        
        function testAllBadgeEvents() {
            const resultsDiv = document.getElementById('webhook-test-results');
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = 'Testing all badge event tracking...';
            
            jQuery.post(ajaxurl, {
                action: 'jph_test_all_badge_events',
                nonce: '<?php echo wp_create_nonce('jph_test_all_badge_events'); ?>'
            })
            .done(function(response) {
                console.log('All badge events test response:', response);
                if (response.success && response.data && response.data.message) {
                resultsDiv.innerHTML = '<strong>Test Results:</strong><br>' + response.data.message;
                } else if (response.data) {
                    resultsDiv.innerHTML = '<strong>Test Results:</strong><br>' + response.data;
                } else {
                    resultsDiv.innerHTML = '<strong>Test Results:</strong><br>Response: ' + JSON.stringify(response);
                }
            })
            .fail(function(xhr, status, error) {
                console.error('All badge events test error:', xhr, status, error);
                resultsDiv.innerHTML = '<strong>Test Results:</strong><br>Error: ' + error + ' (Status: ' + status + ')';
            });
        }
        
        // Badge Debugging Functions
        function showDebugResults(content) {
            const resultsDiv = document.getElementById('badge-debug-results');
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = content;
        }
        
        function checkUserBadgeStatus() {
            const userId = document.getElementById('debug-user-id').value;
            showDebugResults('Loading badge status for user ' + userId + '...');
            
            jQuery.post(ajaxurl, {
                action: 'jph_debug_user_badges',
                user_id: userId,
                nonce: '<?php echo wp_create_nonce('jph_debug_user_badges'); ?>'
            })
            .done(function(response) {
                showDebugResults('=== USER BADGE STATUS ===\n\n' + 
                    JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                showDebugResults('Error checking badge status:\n' + error + '\nStatus: ' + status);
            });
        }
        
        function runBadgeAssignmentTest() {
            const userId = document.getElementById('debug-user-id').value;
            showDebugResults('Running badge assignment test for user ' + userId + '...');
            
            jQuery.post(ajaxurl, {
                action: 'jph_debug_badge_assignment',
                user_id: userId,
                nonce: '<?php echo wp_create_nonce('jph_debug_badge_assignment'); ?>'
            })
            .done(function(response) {
                showDebugResults('=== BADGE ASSIGNMENT TEST ===\n\n' + 
                    JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                showDebugResults('Error running badge assignment test:\n' + error + '\nStatus: ' + status);
            });
        }
        
        function debugMarathonBadge() {
            const userId = document.getElementById('debug-user-id').value;
            showDebugResults('Debugging Marathon badge for user ' + userId + '...');
            
            jQuery.post(ajaxurl, {
                action: 'jph_debug_marathon_badge',
                user_id: userId,
                nonce: '<?php echo wp_create_nonce('jph_debug_marathon_badge'); ?>'
            })
            .done(function(response) {
                showDebugResults('=== MARATHON BADGE DEBUG ===\n\n' + 
                    JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                showDebugResults('Error debugging Marathon badge:\n' + error + '\nStatus: ' + status);
            });
        }
        
        function simulateBadgeCheck() {
            const userId = document.getElementById('debug-user-id').value;
            showDebugResults('Simulating badge check for user ' + userId + '...');
            
            jQuery.post(ajaxurl, {
                action: 'jph_simulate_badge_check',
                user_id: userId,
                nonce: '<?php echo wp_create_nonce('jph_simulate_badge_check'); ?>'
            })
            .done(function(response) {
                showDebugResults('=== BADGE CHECK SIMULATION ===\n\n' + 
                    JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                showDebugResults('Error simulating badge check:\n' + error + '\nStatus: ' + status);
            });
        }
        
        function inspectBadgeDatabase() {
            showDebugResults('Inspecting badge database...');
            
            jQuery.post(ajaxurl, {
                action: 'jph_inspect_badge_database',
                nonce: '<?php echo wp_create_nonce('jph_inspect_badge_database'); ?>'
            })
            .done(function(response) {
                showDebugResults('=== BADGE DATABASE INSPECTION ===\n\n' + 
                    JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                showDebugResults('Error inspecting database:\n' + error + '\nStatus: ' + status);
            });
        }
        
        function checkPracticeSessions() {
            const userId = document.getElementById('debug-user-id').value;
            showDebugResults('Checking practice sessions for user ' + userId + '...');
            
            jQuery.post(ajaxurl, {
                action: 'jph_check_practice_sessions',
                user_id: userId,
                nonce: '<?php echo wp_create_nonce('jph_check_practice_sessions'); ?>'
            })
            .done(function(response) {
                showDebugResults('=== PRACTICE SESSIONS CHECK ===\n\n' + 
                    JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                showDebugResults('Error checking practice sessions:\n' + error + '\nStatus: ' + status);
            });
        }
        
        // Practice Items Debugging Functions
        function showPracticeItemsDebugResults(content) {
            const resultsDiv = document.getElementById('practice-items-debug-results');
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = content;
        }
        
        function debugPracticeItems() {
            showPracticeItemsDebugResults('Debugging practice items database...');
            
            jQuery.post(ajaxurl, {
                action: 'jph_debug_practice_items',
                nonce: '<?php echo wp_create_nonce('jph_debug_practice_items'); ?>'
            })
            .done(function(response) {
                showPracticeItemsDebugResults('=== PRACTICE ITEMS DEBUG ===\n\n' + 
                    JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                showPracticeItemsDebugResults('Error debugging practice items:\n' + error + '\nStatus: ' + status);
            });
        }
        
        function checkPracticeItemsQuery() {
            showPracticeItemsDebugResults('Testing database query for practice items...');
            
            jQuery.post(ajaxurl, {
                action: 'jph_debug_practice_items',
                test_query: true,
                nonce: '<?php echo wp_create_nonce('jph_debug_practice_items'); ?>'
            })
            .done(function(response) {
                showPracticeItemsDebugResults('=== DATABASE QUERY TEST ===\n\n' + 
                    JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                showPracticeItemsDebugResults('Error testing query:\n' + error + '\nStatus: ' + status);
            });
        }
        
        function showAllPracticeItems() {
            showPracticeItemsDebugResults('Loading all practice items from database...');
            
            jQuery.post(ajaxurl, {
                action: 'jph_debug_practice_items',
                show_all: true,
                nonce: '<?php echo wp_create_nonce('jph_debug_practice_items'); ?>'
            })
            .done(function(response) {
                showPracticeItemsDebugResults('=== ALL PRACTICE ITEMS ===\n\n' + 
                    JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                showPracticeItemsDebugResults('Error loading practice items:\n' + error + '\nStatus: ' + status);
            });
        }
        
        function forceRefreshPracticeItems() {
            showPracticeItemsDebugResults('Forcing refresh of practice items...');
            
            // Reload the page to refresh the practice items display
            window.location.reload();
        }
        
        function refreshBadgeEventLogs() {
            const logsDiv = document.getElementById('badge-event-logs-content');
            logsDiv.innerHTML = 'Loading badge event logs...';
            
            jQuery.post(ajaxurl, {
                action: 'jph_get_badge_event_logs',
                nonce: '<?php echo wp_create_nonce('jph_get_badge_event_logs'); ?>'
            }, function(response) {
                if (response.success && response.data.logs) {
                logsDiv.innerHTML = response.data.logs;
                } else {
                    logsDiv.innerHTML = 'No badge event logs found or error loading logs.';
                }
            })
            .fail(function() {
                logsDiv.innerHTML = 'Error loading badge event logs.';
            });
        }
        
        function clearBadgeEventLogs() {
            if (confirm('Are you sure you want to clear all badge event tracking logs?')) {
                jQuery.post(ajaxurl, {
                    action: 'jph_clear_badge_event_logs',
                    nonce: '<?php echo wp_create_nonce('jph_clear_badge_event_logs'); ?>'
                }, function(response) {
                    refreshBadgeEventLogs();
                });
            }
        }
        
        // FluentCRM Event Tracking Functions
        function loadEventLogs() {
            const logsDiv = document.getElementById('event-logs-results');
            logsDiv.innerHTML = 'Loading FluentCRM event logs...';
            
            jQuery.post(ajaxurl, {
                action: 'jph_get_event_logs',
                nonce: '<?php echo wp_create_nonce('jph_get_event_logs'); ?>'
            }, function(response) {
                if (response.success) {
                    logsDiv.innerHTML = '<pre>' + response.data + '</pre>';
                } else {
                    logsDiv.innerHTML = 'Error loading logs: ' + response.data;
                }
            })
            .fail(function() {
                logsDiv.innerHTML = 'Error loading FluentCRM event logs.';
            });
        }
        
        function emptyEventTrackingTable() {
            if (confirm('⚠️ WARNING: This will permanently delete ALL data from the wp_fc_event_tracking table!\n\nThis action cannot be undone.\n\nAre you absolutely sure you want to continue?')) {
                const logsDiv = document.getElementById('event-logs-results');
                logsDiv.innerHTML = 'Emptying event tracking table...';
                
                jQuery.post(ajaxurl, {
                    action: 'jph_empty_event_tracking_table',
                    nonce: '<?php echo wp_create_nonce('jph_empty_event_tracking_table'); ?>'
                }, function(response) {
                    if (response.success) {
                        logsDiv.innerHTML = '✅ ' + response.data.message;
                    } else {
                        logsDiv.innerHTML = '❌ Error: ' + response.data;
                    }
                })
                .fail(function() {
                    logsDiv.innerHTML = '❌ Error emptying event tracking table.';
                });
            }
        }
        
        // Load logs on page load
        jQuery(document).ready(function() {
            refreshBadgeEventLogs();
                
                // Handle Shield Protection modal button
                jQuery(document).on('click', '#show-shield-modal-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Shield modal button clicked');
                    showShieldProtectionModal();
                });
                
                // Handle modal close button
                jQuery(document).on('click', '.jph-modal-close', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    jQuery(this).closest('.jph-modal').hide();
                });
                
                // Handle Got it button
                jQuery(document).on('click', '#close-shield-modal-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    jQuery('#jph-shield-protection-modal').hide();
                });
                
                // Shield Protection Info Modal Button Handler - Moved outside jQuery ready for global access
                // This will be handled by the global script below
            });
        
        </script>
        <?php
    }
    
    /**
     * Update event tracking settings
     */
    private function update_webhook_settings() {
        $settings = array(
            'enabled' => isset($_POST['event_tracking_enabled']),
            'provider' => sanitize_text_field($_POST['event_provider']),
            'prefix' => sanitize_text_field($_POST['event_prefix']),
            'milestones' => array()
        );
        
        // Process milestone settings
        if (isset($_POST['milestones']) && is_array($_POST['milestones'])) {
            foreach ($_POST['milestones'] as $key => $milestone) {
                $settings['milestones'][$key] = array(
                    'enabled' => isset($milestone['enabled']),
                    'delay' => intval($milestone['delay'])
                );
            }
        }
        
        update_option('jph_webhook_settings', $settings);
        
        echo '<div class="notice notice-success"><p>Event tracking settings updated successfully!</p></div>';
    }
    
    /**
     * REST API: Get practice items
     */
    public function rest_get_practice_items($request) {
        try {
            $database = new JPH_Database();
            $user_id = $request->get_param('user_id') ?: 1; // Default to user 1 for testing
            
            $items = $database->get_user_practice_items($user_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'user_id' => $user_id,
                'items' => $items,
                'count' => count($items),
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('get_items_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Add practice item
     */
    public function rest_add_practice_item($request) {
        try {
            $database = new JPH_Database();
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to add practice items', array('status' => 401));
            }
            
            $name = $request->get_param('name');
            $category = $request->get_param('category') ?: 'custom';
            $description = $request->get_param('description') ?: '';
            
            if (!$name) {
                return new WP_Error('missing_name', 'Practice item name is required', array('status' => 400));
            }
            
            $result = $database->add_practice_item($user_id, $name, $category, $description);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            // Check for practice item milestones
            $this->check_practice_item_milestones($user_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'item_id' => $result,
                'message' => 'Practice item added successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('add_item_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Log practice session
     */
    public function rest_log_practice_session($request) {
        try {
            $database = new JPH_Database();
            $user_id = get_current_user_id();
            
            // Only use request parameter if explicitly provided and user is admin
            if ($request->get_param('user_id') && current_user_can('manage_options')) {
                $user_id = $request->get_param('user_id');
            }
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to log practice sessions', array('status' => 401));
            }
            
            $practice_item_id = $request->get_param('practice_item_id');
            $duration_minutes = $request->get_param('duration_minutes');
            $sentiment_score = $request->get_param('sentiment_score');
            $improvement_detected = $request->get_param('improvement_detected') ?: false;
            $notes = $request->get_param('notes') ?: '';
            
            if (!$practice_item_id || !$duration_minutes || !$sentiment_score) {
                return new WP_Error('missing_params', 'practice_item_id, duration_minutes, and sentiment_score are required', array('status' => 400));
            }
            
            $result = $database->log_practice_session(
                $user_id, 
                $practice_item_id, 
                $duration_minutes, 
                $sentiment_score, 
                $improvement_detected, 
                $notes
            );
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            // Gamification integration
            $gamification = new JPH_Gamification();
            
            // Calculate and add XP
            $xp_earned = $gamification->calculate_xp($duration_minutes, $sentiment_score, $improvement_detected);
            $xp_result = $gamification->add_xp($user_id, $xp_earned);
            
            if (!$xp_result) {
                error_log('JPH: Failed to add XP for user ' . $user_id . ' - this will prevent badge checks');
                // Continue anyway, but log the issue
            }
            
            // Update the practice session with XP earned
            $database->update_practice_session_xp($result, $xp_earned);
            
            // Update streak
            $streak_result = $gamification->update_streak($user_id);
            
            // Check for level up
            $level_result = $gamification->check_level_up($user_id);
            
            // Update total minutes in stats (add to existing total)
            $current_stats = $gamification->get_user_stats($user_id);
            $new_total_minutes = $current_stats['total_minutes'] + $duration_minutes;
            $database->update_user_stats($user_id, array('total_minutes' => $new_total_minutes));
            
            // Get updated stats after all updates (including total_sessions increment from add_xp)
            $updated_stats = $gamification->get_user_stats($user_id);
            
            // Check for webhook milestones
            $this->check_practice_session_milestones($user_id, $updated_stats, $streak_result);
            
            // Check and award badges
            $this->check_and_award_badges($user_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'session_id' => $result,
                'message' => 'Practice session logged successfully',
                'xp_earned' => $xp_earned,
                'level_up' => $level_result,
                'streak_update' => $streak_result,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('log_session_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get practice sessions
     */
    public function rest_get_practice_sessions($request) {
        try {
            $database = new JPH_Database();
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to view practice sessions', array('status' => 401));
            }
            $limit = $request->get_param('limit') ?: 50; // Default to 50 recent sessions
            $offset = $request->get_param('offset') ?: 0; // Default to 0 for pagination
            
            $sessions = $database->get_practice_sessions($user_id, $limit, $offset);
            
            if (is_wp_error($sessions)) {
                return $sessions;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'sessions' => $sessions,
                'count' => count($sessions),
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('get_sessions_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get user stats
     */
    public function rest_get_user_stats($request) {
        try {
            $gamification = new JPH_Gamification();
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to view stats', array('status' => 401));
            }
            
            $stats = $gamification->get_user_stats($user_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'stats' => $stats,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('get_stats_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Test gamification system
     */
    public function rest_test_gamification($request) {
        try {
            $gamification = new JPH_Gamification();
            $user_id = $request->get_param('user_id') ?: 1;
            $xp_amount = $request->get_param('xp_amount') ?: 50;
            
            // Test adding XP
            $result = $gamification->add_xp($user_id, $xp_amount);
            
            // Get updated stats
            $stats = $gamification->get_user_stats($user_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Gamification test completed',
                'xp_added' => $xp_amount,
                'updated_stats' => $stats,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('test_gamification_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Test gamification system (admin dashboard)
     */
    public function rest_gamification_test($request) {
        try {
            $gamification = new JPH_Gamification();
            
            // Test XP calculation
            $xp_test_1 = $gamification->calculate_xp(30, 4, true); // 30 min, good sentiment, improvement
            $xp_test_2 = $gamification->calculate_xp(15, 2, false); // 15 min, poor sentiment, no improvement
            $xp_test_3 = $gamification->calculate_xp(60, 5, true); // 60 min, excellent sentiment, improvement
            
            // Test level calculation
            $level_1 = $this->calculate_level_from_xp(50);
            $level_2 = $this->calculate_level_from_xp(150);
            $level_3 = $this->calculate_level_from_xp(500);
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Gamification system test completed',
                'xp_calculations' => array(
                    'test_1' => array(
                        'duration' => 30,
                        'sentiment' => 4,
                        'improvement' => true,
                        'xp_earned' => $xp_test_1
                    ),
                    'test_2' => array(
                        'duration' => 15,
                        'sentiment' => 2,
                        'improvement' => false,
                        'xp_earned' => $xp_test_2
                    ),
                    'test_3' => array(
                        'duration' => 60,
                        'sentiment' => 5,
                        'improvement' => true,
                        'xp_earned' => $xp_test_3
                    )
                ),
                'level_calculations' => array(
                    '50_xp' => $level_1,
                    '150_xp' => $level_2,
                    '500_xp' => $level_3
                ),
                'system_info' => array(
                    'max_xp_per_session' => 60,
                    'sentiment_scale' => '1-5',
                    'improvement_bonus' => '25%',
                    'level_formula' => 'floor(sqrt(XP / 100)) + 1'
                ),
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('gamification_test_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get gamification stats for current user
     */
    public function rest_gamification_stats($request) {
        try {
            $gamification = new JPH_Gamification();
            $user_id = get_current_user_id() ?: 1; // Default to user 1 for testing
            
            $stats = $gamification->get_user_stats($user_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'user_id' => $user_id,
                'stats' => $stats,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('gamification_stats_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Helper function to calculate level from XP
     */
    private function calculate_level_from_xp($xp) {
        return floor(sqrt($xp / 100)) + 1;
    }
    
    /**
     * REST API: Get all students with their gamification stats
     */
    public function rest_get_students($request) {
        try {
            global $wpdb;
            
            // Get filter parameters
            $search = $request->get_param('search');
            $level = $request->get_param('level');
            $activity = $request->get_param('activity');
            
            // Get all users who have practice stats
            $table_name = $wpdb->prefix . 'jph_user_stats';
            $users_table = $wpdb->prefix . 'users';
            
            // Build WHERE conditions
            $where_conditions = array("s.user_id IS NOT NULL");
            $where_values = array();
            
            // Search filter
            if (!empty($search)) {
                $where_conditions[] = "(u.display_name LIKE %s OR u.user_email LIKE %s)";
                $search_term = '%' . $wpdb->esc_like($search) . '%';
                $where_values[] = $search_term;
                $where_values[] = $search_term;
            }
            
            // Level filter
            if (!empty($level)) {
                if ($level === '3') {
                    // Level 3+ (XP >= 500)
                    $where_conditions[] = "s.total_xp >= 500";
                } else {
                    // Specific level (XP range)
                    $min_xp = ($level - 1) * 100;
                    $max_xp = $level * 100 - 1;
                    $where_conditions[] = "s.total_xp >= %d AND s.total_xp <= %d";
                    $where_values[] = $min_xp;
                    $where_values[] = $max_xp;
                }
            }
            
            // Activity filter
            if (!empty($activity)) {
                if ($activity === 'active') {
                    // Active within 7 days
                    $where_conditions[] = "s.last_practice_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                } elseif ($activity === 'inactive') {
                    // Inactive for 30+ days
                    $where_conditions[] = "(s.last_practice_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY) OR s.last_practice_date IS NULL)";
                }
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            $query = $wpdb->prepare("
                SELECT u.ID, u.user_email, u.display_name, s.*
                FROM {$users_table} u
                LEFT JOIN {$table_name} s ON u.ID = s.user_id
                WHERE {$where_clause}
                ORDER BY s.total_xp DESC, u.display_name ASC
            ", $where_values);
            
            $results = $wpdb->get_results($query);
            
            $students = array();
            $gamification = new JPH_Gamification();
            $database = new JPH_Database();
            
            foreach ($results as $row) {
                // Calculate current level based on total XP
                $calculated_level = $gamification->calculate_level_from_xp($row->total_xp);
                
                // Get user's earned badges
                $user_badges = $database->get_user_badges($row->ID);
                $badge_count = count($user_badges);
                
                $students[] = array(
                    'ID' => (int) $row->ID,
                    'user_email' => $row->user_email,
                    'display_name' => $row->display_name ?: $row->user_email,
                    'stats' => array(
                        'total_xp' => (int) $row->total_xp,
                        'current_level' => $calculated_level,
                        'current_streak' => (int) $row->current_streak,
                        'longest_streak' => (int) $row->longest_streak,
                        'total_sessions' => (int) $row->total_sessions,
                        'total_minutes' => (int) $row->total_minutes,
                        'badges_earned' => $badge_count,
                        'hearts_count' => (int) $row->hearts_count,
                        'gems_balance' => (int) $row->gems_balance,
                        'last_practice_date' => $row->last_practice_date
                    ),
                    'badges' => $user_badges
                );
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'students' => $students,
                'total_count' => count($students),
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('get_students_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get students statistics
     */
    public function rest_get_students_stats($request) {
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'jph_user_stats';
            
            // Get total students
            $total_students = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
            
            // Get active students (practiced in last 7 days)
            $active_students = $wpdb->get_var("
                SELECT COUNT(*) FROM {$table_name} 
                WHERE last_practice_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            
            // Get total practice hours
            $total_minutes = $wpdb->get_var("SELECT SUM(total_minutes) FROM {$table_name}");
            $total_hours = round($total_minutes / 60, 1);
            
            // Get average level
            $average_level = $wpdb->get_var("SELECT AVG(current_level) FROM {$table_name}");
            $average_level = round($average_level, 1);
            
            // Get level distribution
            $level_distribution = $wpdb->get_results("
                SELECT current_level, COUNT(*) as count 
                FROM {$table_name} 
                GROUP BY current_level 
                ORDER BY current_level ASC
            ");
            
            // Get streak statistics
            $streak_stats = $wpdb->get_row("
                SELECT 
                    AVG(current_streak) as avg_streak,
                    MAX(current_streak) as max_streak,
                    AVG(longest_streak) as avg_longest_streak
                FROM {$table_name}
            ");
            
            return rest_ensure_response(array(
                'success' => true,
                'stats' => array(
                    'total_students' => (int) $total_students,
                    'active_students' => (int) $active_students,
                    'total_hours' => $total_hours,
                    'average_level' => $average_level,
                    'level_distribution' => $level_distribution,
                    'streak_stats' => array(
                        'average_current_streak' => round($streak_stats->avg_streak, 1),
                        'max_current_streak' => (int) $streak_stats->max_streak,
                        'average_longest_streak' => round($streak_stats->avg_longest_streak, 1)
                    )
                ),
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('get_students_stats_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get individual student details
     */
    public function rest_get_student($request) {
        try {
            global $wpdb;
            
            $user_id = $request->get_param('id');
            $table_name = $wpdb->prefix . 'jph_user_stats';
            $users_table = $wpdb->prefix . 'users';
            
            $query = $wpdb->prepare("
                SELECT u.ID, u.user_email, u.display_name, s.*
                FROM {$users_table} u
                LEFT JOIN {$table_name} s ON u.ID = s.user_id
                WHERE u.ID = %d
            ", $user_id);
            
            $result = $wpdb->get_row($query);
            
            if (!$result) {
                return new WP_Error('student_not_found', 'Student not found', array('status' => 404));
            }
            
            // Calculate current level based on total XP
            $gamification = new JPH_Gamification();
            $calculated_level = $gamification->calculate_level_from_xp($result->total_xp);
            
            $student = array(
                'ID' => (int) $result->ID,
                'user_email' => $result->user_email,
                'display_name' => $result->display_name ?: $result->user_email,
                'stats' => array(
                    'total_xp' => (int) $result->total_xp,
                    'current_level' => $calculated_level,
                    'current_streak' => (int) $result->current_streak,
                    'longest_streak' => (int) $result->longest_streak,
                    'total_sessions' => (int) $result->total_sessions,
                    'total_minutes' => (int) $result->total_minutes,
                    'badges_earned' => (int) $result->badges_earned,
                    'hearts_count' => (int) $result->hearts_count,
                    'gems_balance' => (int) $result->gems_balance,
                    'last_practice_date' => $result->last_practice_date
                )
            );
            
            return rest_ensure_response(array(
                'success' => true,
                'student' => $student,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('get_student_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Update individual student stats
     */
    public function rest_update_student($request) {
        try {
            global $wpdb;
            
            $user_id = $request->get_param('id');
            $table_name = $wpdb->prefix . 'jph_user_stats';
            
            // Get the request body
            $data = $request->get_json_params();
            
            // Validate required fields
            $required_fields = array('total_xp', 'current_level', 'current_streak', 'longest_streak', 'total_sessions', 'total_minutes', 'hearts_count', 'gems_balance', 'badges_earned');
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || !is_numeric($data[$field])) {
                    return new WP_Error('invalid_data', "Invalid or missing field: {$field}", array('status' => 400));
                }
            }
            
            // Check if student stats exist
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d",
                $user_id
            ));
            
            if (!$existing) {
                // Create new stats record
                $result = $wpdb->insert(
                    $table_name,
                    array(
                        'user_id' => $user_id,
                        'total_xp' => $data['total_xp'],
                        'current_level' => $data['current_level'],
                        'current_streak' => $data['current_streak'],
                        'longest_streak' => $data['longest_streak'],
                        'total_sessions' => $data['total_sessions'],
                        'total_minutes' => $data['total_minutes'],
                        'hearts_count' => $data['hearts_count'],
                        'gems_balance' => $data['gems_balance'],
                        'badges_earned' => $data['badges_earned'],
                        'last_practice_date' => current_time('Y-m-d')
                    ),
                    array('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s')
                );
            } else {
                // Update existing stats
                $result = $wpdb->update(
                    $table_name,
                    array(
                        'total_xp' => $data['total_xp'],
                        'current_level' => $data['current_level'],
                        'current_streak' => $data['current_streak'],
                        'longest_streak' => $data['longest_streak'],
                        'total_sessions' => $data['total_sessions'],
                        'total_minutes' => $data['total_minutes'],
                        'hearts_count' => $data['hearts_count'],
                        'gems_balance' => $data['gems_balance'],
                        'badges_earned' => $data['badges_earned']
                    ),
                    array('user_id' => $user_id),
                    array('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d'),
                    array('%d')
                );
            }
            
            if ($result === false) {
                return new WP_Error('update_failed', 'Failed to update student stats', array('status' => 500));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Student stats updated successfully',
                'user_id' => $user_id,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('update_student_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Backfill user stats from existing practice sessions
     */
    public function rest_backfill_user_stats($request) {
        try {
            global $wpdb;
            
            $database = new JPH_Database();
            $gamification = new JPH_Gamification();
            
            // Get all practice sessions
            $sessions_table = $wpdb->prefix . 'jph_practice_sessions';
            $sessions = $wpdb->get_results("SELECT * FROM {$sessions_table} ORDER BY created_at ASC");
            
            $processed_users = array();
            $total_sessions_processed = 0;
            $total_xp_added = 0;
            
            foreach ($sessions as $session) {
                $user_id = $session->user_id;
                
                // Initialize user stats if not exists
                if (!isset($processed_users[$user_id])) {
                    $processed_users[$user_id] = array(
                        'total_xp' => 0,
                        'total_sessions' => 0,
                        'total_minutes' => 0,
                        'last_practice_date' => null
                    );
                }
                
                // Calculate XP for this session
                $xp_earned = $gamification->calculate_xp(
                    $session->duration_minutes, 
                    $session->sentiment_score, 
                    $session->improvement_detected
                );
                
                // Update user stats
                $processed_users[$user_id]['total_xp'] += $xp_earned;
                $processed_users[$user_id]['total_sessions'] += 1;
                $processed_users[$user_id]['total_minutes'] += $session->duration_minutes;
                
                // Update last practice date
                if (!$processed_users[$user_id]['last_practice_date'] || 
                    $session->created_at > $processed_users[$user_id]['last_practice_date']) {
                    $processed_users[$user_id]['last_practice_date'] = $session->created_at;
                }
                
                $total_sessions_processed++;
                $total_xp_added += $xp_earned;
            }
            
            // Update database with calculated stats
            $user_stats_table = $wpdb->prefix . 'jph_user_stats';
            $users_updated = 0;
            
            foreach ($processed_users as $user_id => $stats) {
                // Calculate streak based on practice dates
                $streak_data = $this->calculate_streak_from_sessions($user_id, $sessions);
                
                // Check if user stats exist
                $existing = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$user_stats_table} WHERE user_id = %d",
                    $user_id
                ));
                
                if ($existing) {
                    // Update existing stats
                    $wpdb->update(
                        $user_stats_table,
                        array(
                            'total_xp' => $stats['total_xp'],
                            'total_sessions' => $stats['total_sessions'],
                            'total_minutes' => $stats['total_minutes'],
                            'last_practice_date' => date('Y-m-d', strtotime($stats['last_practice_date'])),
                            'current_streak' => $streak_data['current_streak'],
                            'longest_streak' => $streak_data['longest_streak']
                        ),
                        array('user_id' => $user_id),
                        array('%d', '%d', '%d', '%s'),
                        array('%d')
                    );
                } else {
                    // Create new stats record
                    $wpdb->insert(
                        $user_stats_table,
                        array(
                            'user_id' => $user_id,
                            'total_xp' => $stats['total_xp'],
                            'current_level' => $gamification->calculate_level_from_xp($stats['total_xp']),
                            'current_streak' => $streak_data['current_streak'],
                            'longest_streak' => $streak_data['longest_streak'],
                            'total_sessions' => $stats['total_sessions'],
                            'total_minutes' => $stats['total_minutes'],
                            'hearts_count' => 5,
                            'gems_balance' => 0,
                            'badges_earned' => 0,
                            'last_practice_date' => date('Y-m-d', strtotime($stats['last_practice_date']))
                        ),
                        array('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s')
                    );
                }
                
                $users_updated++;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'User stats backfilled successfully',
                'stats' => array(
                    'total_sessions_processed' => $total_sessions_processed,
                    'total_xp_added' => $total_xp_added,
                    'users_updated' => $users_updated,
                    'processed_users' => $processed_users
                ),
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('backfill_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get all badges (Admin)
     */
    public function rest_get_badges_admin($request) {
        try {
            global $wpdb;
            $database = new JPH_Database();
            $badges = $database->get_badges(true); // Get only active badges
            
            // Badges loaded successfully
            
            // Add awarded count to each badge
            $user_badges_table = $wpdb->prefix . 'jph_user_badges';
            foreach ($badges as &$badge) {
                $awarded_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT user_id) FROM {$user_badges_table} WHERE badge_key = %s",
                    $badge['badge_key']
                ));
                $badge['awarded_count'] = (int) $awarded_count;
            }
            
            $response = array(
                'success' => true,
                'badges' => $badges,
                'total_count' => count($badges),
                'timestamp' => current_time('mysql')
            );
            
            return rest_ensure_response($response);
        } catch (Exception $e) {
            return new WP_Error('get_badges_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get badge by key
     */
    public function rest_get_badge($request) {
        try {
            $badge_id = $request->get_param('id');
            $database = new JPH_Database();
            $badge = $database->get_badge_by_id($badge_id);
            
            if (!$badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'badge' => $badge,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('get_badge_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Add new badge
     */
    public function rest_add_badge($request) {
        try {
            $database = new JPH_Database();
            
            // Get form data
            $name = sanitize_text_field($request->get_param('name'));
            $description = sanitize_textarea_field($request->get_param('description'));
            
            // Generate badge_key automatically from name
            $badge_key = $this->generate_badge_key($name);
            $category = sanitize_text_field($request->get_param('category'));
            $rarity = sanitize_text_field($request->get_param('rarity'));
            $xp_reward = intval($request->get_param('xp_reward'));
            $gem_reward = intval($request->get_param('gem_reward'));
            $criteria_type = sanitize_text_field($request->get_param('criteria_type'));
            $criteria_value = intval($request->get_param('criteria_value'));
            $webhook_url = sanitize_url($request->get_param('webhook_url'));
            
            // Validate required fields
            if (empty($name)) {
                return new WP_Error('missing_fields', 'Badge name is required', array('status' => 400));
            }
            
            // Handle file upload
            $image_url = '';
            if (isset($_FILES['badge_image']) && $_FILES['badge_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = wp_upload_dir();
                $badge_dir = $upload_dir['basedir'] . '/jph-badges';
                
                // Create directory if it doesn't exist
                if (!file_exists($badge_dir)) {
                    wp_mkdir_p($badge_dir);
                }
                
                $file_extension = pathinfo($_FILES['badge_image']['name'], PATHINFO_EXTENSION);
                $filename = sanitize_file_name($badge_key . '.' . $file_extension);
                $file_path = $badge_dir . '/' . $filename;
                
                if (move_uploaded_file($_FILES['badge_image']['tmp_name'], $file_path)) {
                    $image_url = $upload_dir['baseurl'] . '/jph-badges/' . $filename;
                }
            }
            
            // Get FluentCRM event settings
            $fluent_event_enabled = intval($request->get_param('fluent_event_enabled'));
            $fluent_event_key = sanitize_text_field($request->get_param('fluent_event_key'));
            $fluent_event_title = sanitize_text_field($request->get_param('fluent_event_title'));
            
            $badge_data = array(
                'badge_key' => $badge_key, // THIS WAS MISSING!
                'name' => $name,
                'description' => $description,
                'icon' => $image_url ?: '🏆',
                'category' => $category ?: 'achievement',
                'xp_reward' => $xp_reward ?: 0,
                'gem_reward' => $gem_reward ?: 0,
                'criteria_type' => $criteria_type ?: 'manual',
                'criteria_value' => $criteria_value ?: 1,
                'webhook_url' => $webhook_url,
                'is_active' => 1,
                'fluent_event_enabled' => $fluent_event_enabled,
                'fluent_event_key' => $fluent_event_key,
                'fluent_event_title' => $fluent_event_title,
                'created_at' => current_time('mysql')
            );
            
            $result = $database->add_badge($badge_data);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Badge created successfully',
                'badge_id' => $result,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('add_badge_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Update badge
     */
    public function rest_update_badge($request) {
        try {
            $badge_id = $request->get_param('id');
            $database = new JPH_Database();
            
            // Validate badge id
            if (empty($badge_id) || !is_numeric($badge_id)) {
                return new WP_Error('invalid_badge_id', 'Valid badge ID is required', array('status' => 400));
            }
            
            // Check if badge exists
            $existing_badge = $database->get_badge_by_id($badge_id);
            if (!$existing_badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            
            // Get JSON data from request body
            $json_data = json_decode($request->get_body(), true);
            
            if (empty($json_data)) {
                return new WP_Error('no_data', 'No data provided to update', array('status' => 400));
            }
            
            
            // Get form data from JSON
            $badge_data = array();
            if (isset($json_data['name']) && !empty($json_data['name'])) {
                $badge_data['name'] = sanitize_text_field($json_data['name']);
            }
            if (isset($json_data['description'])) {
                $badge_data['description'] = sanitize_textarea_field($json_data['description']);
            }
            if (isset($json_data['category']) && !empty($json_data['category'])) {
                $badge_data['category'] = sanitize_text_field($json_data['category']);
            }
            if (isset($json_data['xp_reward']) && $json_data['xp_reward'] >= 0) {
                $badge_data['xp_reward'] = intval($json_data['xp_reward']);
            }
            if (isset($json_data['gem_reward']) && $json_data['gem_reward'] >= 0) {
                $badge_data['gem_reward'] = intval($json_data['gem_reward']);
            }
            if (isset($json_data['fluent_event_enabled']) && $json_data['fluent_event_enabled'] >= 0) {
                $badge_data['fluent_event_enabled'] = intval($json_data['fluent_event_enabled']);
            }
            if (isset($json_data['fluent_event_key'])) {
                $badge_data['fluent_event_key'] = sanitize_text_field($json_data['fluent_event_key']);
            }
            if (isset($json_data['fluent_event_title'])) {
                $badge_data['fluent_event_title'] = sanitize_text_field($json_data['fluent_event_title']);
            }
            if (isset($json_data['is_active'])) {
                $badge_data['is_active'] = intval($json_data['is_active']);
            }
            if (isset($json_data['criteria_type'])) {
                $badge_data['criteria_type'] = sanitize_text_field($json_data['criteria_type']);
            }
            if (isset($json_data['criteria_value'])) {
                $badge_data['criteria_value'] = intval($json_data['criteria_value']);
            }
            if (isset($json_data['webhook_url'])) {
                $badge_data['webhook_url'] = sanitize_url($json_data['webhook_url']);
            }
            
            
            // Validate that we have data to update
            if (empty($badge_data)) {
                return new WP_Error('no_data', 'No data provided to update', array('status' => 400));
            }
            
            // Handle file upload
            if (isset($_FILES['badge_image']) && $_FILES['badge_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = wp_upload_dir();
                $badge_dir = $upload_dir['basedir'] . '/jph-badges';
                
                // Create directory if it doesn't exist
                if (!file_exists($badge_dir)) {
                    wp_mkdir_p($badge_dir);
                }
                
                $file_extension = pathinfo($_FILES['badge_image']['name'], PATHINFO_EXTENSION);
                $filename = sanitize_file_name($badge_key . '.' . $file_extension);
                $file_path = $badge_dir . '/' . $filename;
                
                if (move_uploaded_file($_FILES['badge_image']['tmp_name'], $file_path)) {
                    $badge_data['icon'] = $upload_dir['baseurl'] . '/jph-badges/' . $filename;
                }
            }
            
            $result = $database->update_badge_by_id($badge_id, $badge_data);
            
            if (!$result) {
                return new WP_Error('update_failed', 'Failed to update badge', array('status' => 500));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Badge updated successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('update_badge_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Delete badge (soft delete)
     */
    public function rest_delete_badge($request) {
        try {
            $badge_id = $request->get_param('id');
            
            if (empty($badge_id) || !is_numeric($badge_id)) {
                return new WP_Error('invalid_badge_id', 'Valid badge ID is required', array('status' => 400));
            }
            
            $database = new JPH_Database();
            
            // Check if badge exists first
            $existing_badge = $database->get_badge_by_id($badge_id);
            if (!$existing_badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            $result = $database->delete_badge_by_id($badge_id);
            
            if (!$result) {
                return new WP_Error('delete_failed', 'Failed to delete badge', array('status' => 500));
            }
            
            // Update badge counts for all users who had this badge
            $this->update_badge_counts_after_deletion($badge_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Badge deleted successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('delete_badge_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get badge by badge_key
     */
    public function rest_get_badge_by_key($request) {
        try {
            $badge_key = $request->get_param('badge_key');
            $database = new JPH_Database();
            
            // Validate badge key
            if (empty($badge_key)) {
                return new WP_Error('invalid_badge_key', 'Valid badge key is required', array('status' => 400));
            }
            
            // Get badge by badge_key
            $badge = $database->get_badge($badge_key);
            if (!$badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'badge' => $badge,
                'timestamp' => current_time('mysql')
            ));
            
        } catch (Exception $e) {
            return new WP_Error('get_badge_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Update badge by badge_key
     */
    public function rest_update_badge_by_key($request) {
        try {
            $badge_key = $request->get_param('badge_key');
            $database = new JPH_Database();
            
            // Validate badge key
            if (empty($badge_key)) {
                return new WP_Error('invalid_badge_key', 'Valid badge key is required', array('status' => 400));
            }
            
            // Check if badge exists
            $existing_badge = $database->get_badge($badge_key);
            if (!$existing_badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            // Get JSON data
            $json_data = json_decode($request->get_body(), true);
            if ($json_data === null) {
                return new WP_Error('invalid_json', 'Invalid JSON data', array('status' => 400));
            }
            
            // Prepare update data
            $badge_data = array();
            if (isset($json_data['name']) && !empty($json_data['name'])) {
                $badge_data['name'] = sanitize_text_field($json_data['name']);
            }
            if (isset($json_data['description'])) {
                $badge_data['description'] = sanitize_textarea_field($json_data['description']);
            }
            if (isset($json_data['category']) && !empty($json_data['category'])) {
                $badge_data['category'] = sanitize_text_field($json_data['category']);
            }
            if (isset($json_data['xp_reward']) && $json_data['xp_reward'] >= 0) {
                $badge_data['xp_reward'] = intval($json_data['xp_reward']);
            }
            if (isset($json_data['gem_reward']) && $json_data['gem_reward'] >= 0) {
                $badge_data['gem_reward'] = intval($json_data['gem_reward']);
            }
            if (isset($json_data['fluent_event_enabled']) && $json_data['fluent_event_enabled'] >= 0) {
                $badge_data['fluent_event_enabled'] = intval($json_data['fluent_event_enabled']);
            }
            if (isset($json_data['fluent_event_key'])) {
                $badge_data['fluent_event_key'] = sanitize_text_field($json_data['fluent_event_key']);
            }
            if (isset($json_data['fluent_event_title'])) {
                $badge_data['fluent_event_title'] = sanitize_text_field($json_data['fluent_event_title']);
            }
            if (isset($json_data['is_active'])) {
                $badge_data['is_active'] = intval($json_data['is_active']);
            }
            if (isset($json_data['criteria_type'])) {
                $badge_data['criteria_type'] = sanitize_text_field($json_data['criteria_type']);
            }
            if (isset($json_data['criteria_value'])) {
                $badge_data['criteria_value'] = intval($json_data['criteria_value']);
            }
            if (isset($json_data['webhook_url'])) {
                $badge_data['webhook_url'] = sanitize_url($json_data['webhook_url']);
            }
            
            // Validate that we have data to update
            if (empty($badge_data)) {
                return new WP_Error('no_data', 'No data provided to update', array('status' => 400));
            }
            
            // Update badge using badge_key
            $result = $database->update_badge($badge_key, $badge_data);
            
            if (!$result) {
                return new WP_Error('update_failed', 'Failed to update badge', array('status' => 500));
            }
            
            // Return updated badge data
            $updated_badge = $database->get_badge($badge_key);
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Badge updated successfully',
                'badge' => $updated_badge,
                'timestamp' => current_time('mysql')
            ));
            
        } catch (Exception $e) {
            return new WP_Error('update_badge_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Delete badge by badge_key
     */
    public function rest_delete_badge_by_key($request) {
        try {
            $badge_key = $request->get_param('badge_key');
            $database = new JPH_Database();
            
            // Validate badge key
            if (empty($badge_key)) {
                return new WP_Error('invalid_badge_key', 'Valid badge key is required', array('status' => 400));
            }
            
            // Check if badge exists
            $existing_badge = $database->get_badge($badge_key);
            if (!$existing_badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            // Delete badge using badge_key
            $result = $database->delete_badge($badge_key);
            
            if (!$result) {
                return new WP_Error('delete_failed', 'Failed to delete badge', array('status' => 500));
            }
            
            // Update badge counts for all users who had this badge
            $this->update_badge_counts_after_deletion_by_key($badge_key);
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Badge deleted successfully',
                'timestamp' => current_time('mysql')
            ));
            
        } catch (Exception $e) {
            return new WP_Error('delete_badge_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get badge debug information - REMOVED (debug section no longer needed)
     */
    // Function removed - debug section deleted from admin area
    /*
    public function rest_get_badge_debug_info($request) {
        try {
            error_log('JPH Debug: rest_get_badge_debug_info called');
            
            global $wpdb;
            $database = new JPH_Database();
            
            // Get current user info
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            
            error_log("JPH Debug: Current user ID: {$user_id}");
            
            // Get badges from both the main database methods and direct SQL
            $badges_from_method = $database->get_badges(false); // Include inactive
            $badges_from_sql = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_badges ORDER BY created_at ASC", ARRAY_A);
            
            // Get user badges
            $user_badges = $database->get_user_badges($user_id);
            
            // Get badge stats
            $total_badges = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_badges");
            $active_badges = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_badges WHERE is_active = 1");
            $inactive_badges = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}jph_badges WHERE is_active = 0");
            
            // Check table structure
            $table_structure = $wpdb->get_results("DESCRIBE {$wpdb->prefix}jph_badges", ARRAY_A);
            $user_badges_table_structure = $wpdb->get_results("DESCRIBE {$wpdb->prefix}jph_user_badges", ARRAY_A);
            
            // Check if there are any badges with null badge_key
            $badges_with_null_key = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jph_badges WHERE badge_key IS NULL OR badge_key = ''", ARRAY_A);
            
            // Check if there are any badges with duplicate badge_key
            $duplicate_keys = $wpdb->get_results(
                "SELECT badge_key, COUNT(*) as count FROM {$wpdb->prefix}jph_badges GROUP BY badge_key HAVING COUNT(*) > 1", 
                ARRAY_A
            );
            
            // Get API test results
            $api_test_results = array(
                'main_badges_endpoint' => $this->test_badges_endpoint(),
                'badge_edit_endpoints' => $this->test_badge_edit_endpoints()
            );
            
            // Build comprehensive debug info
            $debug_info = array(
                'timestamp' => current_time('mysql'),
                'current_user' => array(
                    'id' => $user_id,
                    'login' => $current_user->user_login,
                    'email' => $current_user->user_email
                ),
                'server_info' => array(
                    'php_version' => phpversion(),
                    'wordpress_version' => get_bloginfo('version'),
                    'plugin_version' => JPH_VERSION,
                    'wpdb_last_error' => $wpdb->last_error,
                    'wpdb_num_queries' => $wpdb->num_queries
                ),
                'badges_data' => array(
                    'from_database_method' => $badges_from_method,
                    'from_direct_sql' => $badges_from_sql,
                    'method_vs_sql_match' => count($badges_from_method) === count($badges_from_sql)
                ),
                'statistics' => array(
                    'total_badges' => (int)$total_badges,
                    'active_badges' => (int)$active_badges,
                    'inactive_badges' => (int)$inactive_badges,
                    'user_badges_count' => count($user_badges),
                    'badges_with_structure_issues' => count($badges_with_null_key),
                    'duplicate_keys_count' => count($duplicate_keys)
                ),
                'table_structures' => array(
                    'jph_badges_table' => $table_structure,
                    'jph_user_badges_table' => $user_badges_table_structure
                ),
                'data_integrity_checks' => array(
                    'badges_with_null_key' => $badges_with_null_key,
                    'duplicate_badge_keys' => $duplicate_keys
                ),
                'api_endpoints' => array(
                    'available_routes' => array(
                        'GET /badges' => '/wp-json/jph/v1/badges',
                        'GET /badges/key/{badge_key}' => '/wp-json/jph/v1/badges/key/',
                        'PUT /badges/key/{badge_key}' => '/wp-json/jph/v1/badges/key/',
                        'DELETE /badges/key/{badge_key}' => '/wp-json/jph/v1/badges/key/'
                    ),
                    'test_results' => $api_test_results
                ),
                'user_badges' => $user_badges
            );
            
            return rest_ensure_response(array(
                'success' => true,
                'debug_info' => $debug_info,
                'timestamp' => current_time('mysql')
            ));
            
        } catch (Exception $e) {
            error_log('JPH Debug Error: ' . $e->getMessage());
            error_log('JPH Debug Error Trace: ' . $e->getTraceAsString());
            return new WP_Error('debug_error', 'Error getting debug info: ' . $e->getMessage(), array('status' => 500));
        }
    }
    */
    
    /* Helper functions removed - debug section no longer needed
    private function test_badges_endpoint() {
        try {
            // Simulate the badges endpoint call
            $database = new JPH_Database();
            $badges = $database->get_badges(false); // Include inactive badges
            
            return array(
                'success' => true,
                'badges_count' => count($badges),
                'sample_badge' => !empty($badges) ? array_keys($badges[0]) : null,
                'badge_keys' => array_column($badges, 'badge_key')
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Test badge edit endpoints
     */
    private function test_badge_edit_endpoints() {
        try {
            global $wpdb;
            
            // Get a sample badge to test
            $sample_badge = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}jph_badges LIMIT 1", ARRAY_A);
            
            if (!$sample_badge) {
                return array(
                    'success' => false,
                    'error' => 'No badges found to test'
                );
            }
            
            $badge_key = $sample_badge['badge_key'];
            
            // Test the database methods
            $database = new JPH_Database();
            
            $get_test = $database->get_badge($badge_key);
            $exists = !empty($get_test);
            
            return array(
                'success' => true,
                'test_badge_key' => $badge_key,
                'get_badge_works' => $exists,
                'sample_badge_data' => $sample_badge
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
  
/**
     * Update badge counts for all users after a badge is deleted (by badge_key)
     */
    private function update_badge_counts_after_deletion_by_key($deleted_badge_key) {
        global $wpdb;
        
        $user_badges_table = $wpdb->prefix . 'jph_user_badges';
        $user_stats_table = $wpdb->prefix . 'jph_user_stats';
        
        // Get all users who had this badge
        $users_with_badge = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$user_badges_table} WHERE badge_key = %s",
            $deleted_badge_key
        ));
        
        // Update badge count for each affected user
        foreach ($users_with_badge as $user_id) {
            $actual_badge_count = count($this->get_user_badges($user_id));
            
            $wpdb->update(
                $user_stats_table,
                array('badges_earned' => $actual_badge_count),
                array('user_id' => $user_id),
                array('%d'),
                array('%d')
            );
        }
    }
    
    /**
     * Update badge counts for all users after a badge is deleted (legacy - by id)
     */
    private function update_badge_counts_after_deletion($deleted_badge_key) {
        global $wpdb;
        
        $user_badges_table = $wpdb->prefix . 'jph_user_badges';
        $user_stats_table = $wpdb->prefix . 'jph_user_stats';
        
        // Get all users who had this badge
        $users_with_badge = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$user_badges_table} WHERE badge_key = %s",
            $deleted_badge_key
        ));
        
        // Update badge count for each affected user
        foreach ($users_with_badge as $user_id) {
            $actual_badge_count = count($this->get_user_badges($user_id));
            
            $wpdb->update(
                $user_stats_table,
                array('badges_earned' => $actual_badge_count),
                array('user_id' => $user_id),
                array('%d'),
                array('%d')
            );
        }
    }
    
    /**
     * Get user badges (helper method for update_badge_counts_after_deletion)
     */
    private function get_user_badges($user_id) {
        global $wpdb;
        
        $user_badges_table = $wpdb->prefix . 'jph_user_badges';
        $badges_table = $wpdb->prefix . 'jph_badges';
        
        // Get only badges that are still active
        $query = $wpdb->prepare(
            "SELECT ub.* FROM {$user_badges_table} ub 
             INNER JOIN {$badges_table} b ON ub.badge_key = b.badge_key 
             WHERE ub.user_id = %d AND b.is_active = 1",
            $user_id
        );
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * REST API: Sync all badge counts for all users
     */
    public function rest_sync_all_badge_counts($request) {
        try {
            global $wpdb;
            
            $user_stats_table = $wpdb->prefix . 'jph_user_stats';
            $user_badges_table = $wpdb->prefix . 'jph_user_badges';
            $badges_table = $wpdb->prefix . 'jph_badges';
            
            // Get all users with stats
            $users = $wpdb->get_results("SELECT user_id FROM {$user_stats_table}");
            
            $updated_count = 0;
            
            foreach ($users as $user) {
                $user_id = $user->user_id;
                
                // Count actual active badges for this user
                $actual_badge_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$user_badges_table} ub 
                     INNER JOIN {$badges_table} b ON ub.badge_key = b.badge_key 
                     WHERE ub.user_id = %d AND b.is_active = 1",
                    $user_id
                ));
                
                // Update the user's badge count
                $result = $wpdb->update(
                    $user_stats_table,
                    array('badges_earned' => $actual_badge_count),
                    array('user_id' => $user_id),
                    array('%d'),
                    array('%d')
                );
                
                if ($result !== false) {
                    $updated_count++;
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => "Updated badge counts for {$updated_count} users",
                'updated_users' => $updated_count,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('sync_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    
    
    /**
     * REST API: Get badge statistics
     */
    public function rest_get_badges_stats($request) {
        try {
            global $wpdb;
            
            $badges_table = $wpdb->prefix . 'jph_badges';
            $user_badges_table = $wpdb->prefix . 'jph_user_badges';
            
            // Get total badges
            $total_badges = $wpdb->get_var("SELECT COUNT(*) FROM {$badges_table}");
            
            // Get active badges
            $active_badges = $wpdb->get_var("SELECT COUNT(*) FROM {$badges_table} WHERE is_active = 1");
            
            // Get categories count
            $categories = $wpdb->get_results("SELECT category, COUNT(*) as count FROM {$badges_table} WHERE is_active = 1 GROUP BY category");
            $category_count = count($categories);
            
            // Get total awards
            $total_awards = $wpdb->get_var("SELECT COUNT(*) FROM {$user_badges_table}");
            
            $response = array(
                'success' => true,
                'stats' => array(
                    'total_badges' => (int) $total_badges,
                    'active_badges' => (int) $active_badges,
                    'category_count' => $category_count,
                    'total_awards' => (int) $total_awards,
                    'categories' => $categories
                ),
                'timestamp' => current_time('mysql')
            );
            
            return rest_ensure_response($response);
        } catch (Exception $e) {
            return new WP_Error('get_badges_stats_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get user's badges
     */
    public function rest_get_user_badges($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to view badges', array('status' => 401));
            }
            
            $database = new JPH_Database();
            
            // Get user's earned badges
            $user_badges = $database->get_user_badges($user_id);
            
            // Get all available badges
            $all_badges = $database->get_badges(true); // Only active badges
            
            // Create a map of earned badges by badge_key
            $earned_badges_map = array();
            foreach ($user_badges as $earned_badge) {
                $earned_badges_map[$earned_badge['badge_key']] = $earned_badge;
            }
            
            // Combine all badges with earned status
            $badges_with_status = array();
            foreach ($all_badges as $badge) {
                $is_earned = isset($earned_badges_map[$badge['badge_key']]);
                $badge_data = $badge;
                $badge_data['is_earned'] = $is_earned;
                
                if ($is_earned) {
                    // Use earned_at column (actual column name in database)
                    $badge_data['earned_at'] = $earned_badges_map[$badge['badge_key']]['earned_at'];
                }
                
                $badges_with_status[] = $badge_data;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'badges' => $badges_with_status,
                'earned_count' => count($user_badges),
                'total_count' => count($all_badges),
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('get_user_badges_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Check and award badges for a user
     */
    public function check_and_award_badges($user_id) {
        $database = new JPH_Database();
        $gamification = new JPH_Gamification();
        
        // Get user stats
        $user_stats = $gamification->get_user_stats($user_id);
        
        // Get user's practice sessions (ALL sessions for badge checking, not limited)
        $sessions = $database->get_all_practice_sessions($user_id);
        
        // Get all available badges
        $all_badges = $database->get_badges(true);
        
        // Get user's already earned badges
        $earned_badges = $database->get_user_badges($user_id);
        $earned_badge_keys = array_column($earned_badges, 'badge_key');
        
        $newly_awarded = array();
        
        foreach ($all_badges as $badge) {
            // Skip if already earned
            if (in_array($badge['badge_key'], $earned_badge_keys)) {
                continue;
            }
            
            $should_award = false;
            
            // Use criteria_type instead of badge_key
            $criteria_type = $badge['criteria_type'] ?? '';
            $criteria_value = intval($badge['criteria_value'] ?? 0);
            
            switch ($criteria_type) {
                case 'total_xp':
                    if ($user_stats['total_xp'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'level_reached':
                    if ($user_stats['current_level'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'practice_sessions':
                    if ($user_stats['total_sessions'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'total_time':
                case 'long_session':
                    // Check if user has any session >= criteria_value minutes
                    foreach ($sessions as $session) {
                        if ($session['duration_minutes'] >= $criteria_value) {
                            $should_award = true;
                            break;
                        }
                    }
                    break;
                    
                case 'improvement_count':
                    // Check sessions with improvement detected
                    $improvement_count = 0;
                    foreach ($sessions as $session) {
                        if ($session['improvement_detected']) {
                            $improvement_count++;
                        }
                    }
                    if ($improvement_count >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'streak':
                    if ($user_stats['current_streak'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'weekly_goal':
                    $days_this_week = $this->count_practice_days_in_period($user_id, 7);
                    if ($days_this_week >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'monthly_goal':
                    $days_this_month = $this->count_practice_days_in_period($user_id, 30);
                    if ($days_this_month >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'weekend_warrior':
                    $weekend_sessions = $this->count_weekend_practice_sessions($user_id);
                    if ($weekend_sessions >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
            }
            
            if ($should_award) {
                // Award the badge
                $database->award_badge(
                    $user_id,
                    $badge['badge_key']
                );
                
                // Update user stats with XP reward, gems reward, and badge count
                $update_data = array();
                if ($badge['xp_reward'] > 0) {
                    $update_data['total_xp'] = $user_stats['total_xp'] + $badge['xp_reward'];
                }
                if ($badge['gem_reward'] > 0) {
                    $update_data['gems_balance'] = $user_stats['gems_balance'] + $badge['gem_reward'];
                    // Record gems transaction
                    $database->record_gems_transaction(
                        $user_id,
                        'earned',
                        $badge['gem_reward'],
                        'badge_' . $badge['badge_key'],
                        'Earned ' . $badge['gem_reward'] . ' gems for earning badge: ' . $badge['name']
                    );
                }
                $update_data['badges_earned'] = $user_stats['badges_earned'] + 1;
                $database->update_user_stats($user_id, $update_data);
                
                // Trigger FluentCRM event if badge has event tracking enabled
                if ($badge['fluent_event_enabled'] == 1) {
                    $this->trigger_badge_fluentcrm_event($badge, $user_id, $user_stats);
                }
                
                $newly_awarded[] = $badge;
            }
        }
        
        // Check for badge milestones if any badges were awarded
        if (!empty($newly_awarded)) {
            $this->check_badge_milestones($user_id);
        }
        
        return $newly_awarded;
    }
    
    /**
     * Trigger FluentCRM event for badge achievement
     */
    private function trigger_badge_fluentcrm_event($badge, $user_id, $user_stats) {
        // Check if FluentCRM tracking is globally enabled
        if (!function_exists('do_action') || !function_exists('get_user_by')) {
            error_log("JPH Badge Event: FluentCRM not available");
            return array('success' => false, 'message' => 'FluentCRM not available');
        }
        
        // Get user data
        $user = get_user_by('id', $user_id);
        if (!$user) {
            error_log("JPH Badge Event: User not found for ID $user_id");
            return array('success' => false, 'message' => 'User not found');
        }
        
        // Prepare event key and title
        $event_key = !empty($badge['fluent_event_key']) ? $badge['fluent_event_key'] : 'jph_badge_' . $badge['badge_key'];
        $event_title = !empty($badge['fluent_event_title']) ? $badge['fluent_event_title'] : $badge['name'];
        
        // Get global FluentCRM settings
        $webhook_settings = get_option('jph_webhook_settings', array());
        $provider = $webhook_settings['provider'] ?? 'jazzedge-practice-hub';
        
        // Prepare event data
        $event_data = array(
            'event_key' => $event_key,
            'title' => $event_title,
            'value' => $badge['xp_reward'] . ' XP, ' . $badge['gem_reward'] . ' gems',
            'email' => $user->user_email,
            'provider' => $provider,
            'custom_data' => array(
                'badge_key' => $badge['badge_key'],
                'badge_name' => $badge['name'],
                'badge_category' => $badge['category'],
                'criteria_type' => $badge['criteria_type'],
                'criteria_value' => $badge['criteria_value'],
                'xp_reward' => $badge['xp_reward'],
                'gem_reward' => $badge['gem_reward'],
                'user_total_xp' => $user_stats['total_xp'],
                'user_level' => $user_stats['current_level'],
                'user_sessions' => $user_stats['total_sessions']
            )
        );
        
        try {
            error_log("JPH Badge Event: Triggering FluentCRM event '{$event_key}' for user {$user_id}");
            
            // Track the event using FluentCRM action hook
            do_action('fluent_crm/track_event_activity', $event_data, true);
            
            error_log("JPH Badge Event: FluentCRM event '{$event_key}' triggered successfully");
            
            // Log the event
            $this->log_badge_event($badge['badge_key'], $user_id, $event_data, true);
            
            return array('success' => true, 'message' => 'Badge FluentCRM event triggered successfully');
        } catch (Exception $e) {
            error_log("JPH Badge Event: Exception occurred: " . $e->getMessage());
            $this->log_badge_event($badge['badge_key'], $user_id, $event_data, false, $e->getMessage());
            return array('success' => false, 'message' => 'Badge FluentCRM event failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Log badge FluentCRM event activity
     */
    private function log_badge_event($badge_key, $user_id, $data, $success, $error_message = '') {
        $logs = get_option('jph_badge_events_log', array());
        
        // Get user information
        $user = get_user_by('id', $user_id);
        $user_email = $user ? $user->user_email : 'Unknown';
        $user_name = $user ? $user->display_name : 'Unknown';
        
        // Try to get FluentCRM contact ID
        $contact_id = $this->get_fluentcrm_contact_id($user_email);
        
        $log_entry = array(
            'timestamp' => time(),
            'badge_key' => $badge_key,
            'user_id' => $user_id,
            'user_email' => $user_email,
            'user_name' => $user_name,
            'contact_id' => $contact_id,
            'success' => $success,
            'message' => $success ? 'Badge event tracked successfully' : $error_message,
            'data' => $data
        );
        
        $logs[] = $log_entry;
        
        // Keep only last 1000 logs
        if (count($logs) > 1000) {
            $logs = array_slice($logs, -1000);
        }
        
        update_option('jph_badge_events_log', $logs);
    }
    
    /**
     * REST API: Test badge FluentCRM event
     */
    public function rest_test_badge_event($request) {
        try {
            $user_id = get_current_user_id();
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to test badge events', array('status' => 401));
            }
            
            $badge_key = $request->get_param('badge_key');
            if (empty($badge_key)) {
                return new WP_Error('missing_badge_key', 'Badge key is required', array('status' => 400));
            }
            
            // Get badge data
            $database = new JPH_Database();
            $badge = $database->get_badge($badge_key);
            
            if (!$badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            if ($badge['fluent_event_enabled'] != 1) {
                return new WP_Error('event_disabled', 'FluentCRM event tracking is disabled for this badge', array('status' => 400));
            }
            
            // Get current user stats for testing
            $gamification = new JPH_Gamification();
            $user_stats = $gamification->get_user_stats($user_id);
            
            // Trigger the test event
            $result = $this->trigger_badge_fluentcrm_event($badge, $user_id, $user_stats);
            
            if ($result['success']) {
                $event_key = !empty($badge['fluent_event_key']) ? $badge['fluent_event_key'] : 'jph_badge_' . $badge['badge_key'];
            
            return rest_ensure_response(array(
                'success' => true,
                    'message' => 'Test badge FluentCRM event triggered successfully',
                    'badge_key' => $badge_key,
                    'badge_name' => $badge['name'],
                    'event_key' => $event_key,
                'timestamp' => current_time('mysql')
            ));
            } else {
                return new WP_Error('event_failed', 'Failed to trigger badge event: ' . $result['message'], array('status' => 500));
            }
            
        } catch (Exception $e) {
            return new WP_Error('test_badge_event_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    
    /**
     * REST API: Create default badges
     */
    public function rest_create_default_badges($request) {
        try {
            $this->create_default_badges();
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Default badges created successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('create_default_badges_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Run database migrations
     */
    public function rest_run_migrations($request) {
        try {
            $database = new JPH_Database();
            $database->run_migrations();
            
                return rest_ensure_response(array(
                    'success' => true,
                'message' => 'Database migrations completed successfully!',
                    'timestamp' => current_time('mysql')
                ));
        } catch (Exception $e) {
            return new WP_Error('migration_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    
    /**
     * REST API: Sync badge count
     */
    public function rest_sync_badge_count($request) {
        try {
            $user_id = get_current_user_id() ?: 1; // Default to user 1 for testing
            $database = new JPH_Database();
            
            // Get current badge count in stats
            $current_badge_count = $database->get_user_stats($user_id)['badges_earned'];
            
            // Get actual badge count
            $actual_badge_count = count($database->get_user_badges($user_id));
            
            // Update the count if different
            if ($actual_badge_count != $current_badge_count) {
                $database->update_user_stats($user_id, array('badges_earned' => $actual_badge_count));
                
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Badge count synced successfully',
                    'old_count' => $current_badge_count,
                    'new_count' => $actual_badge_count,
                    'timestamp' => current_time('mysql')
                ));
            } else {
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Badge count is already correct',
                    'old_count' => $current_badge_count,
                    'new_count' => $actual_badge_count,
                    'timestamp' => current_time('mysql')
                ));
            }
        } catch (Exception $e) {
            return new WP_Error('sync_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    
    
    /**
     * REST API: Export practice history as CSV
     */
    public function rest_export_practice_history($request) {
        try {
            $user_id = get_current_user_id();
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to export your practice history', array('status' => 401));
            }
            
            $database = new JPH_Database();
            $sessions = $database->get_all_practice_sessions($user_id);
            
            if (is_wp_error($sessions)) {
                return $sessions;
            }
            
            // Check if we have any sessions to export
            if (empty($sessions)) {
                return new WP_Error('no_sessions', 'No practice sessions found to export', array('status' => 404));
            }
            
            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="practice-history-' . date('Y-m-d') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            
            // Create CSV output
            $output = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 compatibility with Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV headers
            fputcsv($output, array(
                'Date',
                'Practice Item',
                'Duration (minutes)',
                'Sentiment Score',
                'Improvement Detected',
                'XP Earned',
                'Notes'
            ));
            
            // Add session data
            foreach ($sessions as $session) {
                fputcsv($output, array(
                    date('Y-m-d H:i:s', strtotime($session['created_at'])),
                    $session['item_name'] ?: 'Unknown Item',
                    $session['duration_minutes'],
                    $session['sentiment_score'],
                    $session['improvement_detected'] ? 'Yes' : 'No',
                    $session['xp_earned'] ?: 0,
                    $session['notes'] ?: ''
                ));
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            // Log the error for debugging
            error_log('JPH Export Error: ' . $e->getMessage());
            return new WP_Error('export_error', 'Error exporting practice history: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Reset badge counts to match actual badges
     */
    public function rest_reset_badge_counts($request) {
        try {
            global $wpdb;
            $database = new JPH_Database();
            $table_names = $database->get_table_names();
            
            // Get all users with stats
            $users = $wpdb->get_results("SELECT user_id FROM {$table_names['user_stats']}");
            
            $reset_count = 0;
            $results = array();
            
            foreach ($users as $user) {
                $user_id = $user->user_id;
                
                // Get actual badge count for this user
                $actual_badge_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table_names['user_badges']} WHERE user_id = %d",
                    $user_id
                ));
                
                // Get current badge count in stats
                $current_badge_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT badges_earned FROM {$table_names['user_stats']} WHERE user_id = %d",
                    $user_id
                ));
                
                // Update if counts don't match
                if ($actual_badge_count != $current_badge_count) {
                    $wpdb->update(
                        $table_names['user_stats'],
                        array('badges_earned' => $actual_badge_count),
                        array('user_id' => $user_id),
                        array('%d'),
                        array('%d')
                    );
                    
                    $reset_count++;
                    $results[] = array(
                        'user_id' => $user_id,
                        'old_count' => $current_badge_count,
                        'new_count' => $actual_badge_count
                    );
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => "Badge counts reset for {$reset_count} users",
                'reset_count' => $reset_count,
                'results' => $results,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('reset_badge_counts_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    // Debug functions removed for production deployment
    
    /**
     * REST API: Check and award badges
     */
    public function rest_check_and_award_badges($request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'You must be logged in to check badges', array('status' => 401));
            }
            
            $newly_awarded = $this->check_and_award_badges($user_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'newly_awarded' => $newly_awarded,
                'count' => count($newly_awarded),
                'message' => count($newly_awarded) > 0 ? 'New badges awarded!' : 'No new badges to award',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('check_badges_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Test badge awarding
     */
    public function rest_test_badge_awarding($request) {
        try {
            $user_id = get_current_user_id() ?: 1;
            $database = new JPH_Database();
            $gamification = new JPH_Gamification();
            
            // Get user stats
            $user_stats = $gamification->get_user_stats($user_id);
            
            // Get user's practice sessions (ALL sessions for testing)
            $sessions = $database->get_all_practice_sessions($user_id);
            
            // Get all available badges
            $all_badges = $database->get_badges(true);
            
            // Get user's already earned badges
            $earned_badges = $database->get_user_badges($user_id);
            
            // Test First Steps badge
            $first_steps_badge = null;
            foreach ($all_badges as $badge) {
                if ($badge['criteria_type'] === 'first_session') {
                    $first_steps_badge = $badge;
                    break;
                }
            }
            
            $test_results = array();
            
            if ($first_steps_badge) {
                $should_award = $user_stats['total_sessions'] >= intval($first_steps_badge['criteria_value']);
                $test_results['first_steps'] = array(
                    'badge_key' => $first_steps_badge['badge_key'],
                    'badge_name' => $first_steps_badge['name'],
                    'criteria_type' => $first_steps_badge['criteria_type'],
                    'criteria_value' => $first_steps_badge['criteria_value'],
                    'user_sessions' => $user_stats['total_sessions'],
                    'should_award' => $should_award,
                    'already_earned' => false
                );
                
                // Check if already earned
                foreach ($earned_badges as $earned) {
                    if ($earned['badge_key'] == $first_steps_badge['badge_key']) {
                        $test_results['first_steps']['already_earned'] = true;
                        break;
                    }
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'user_stats' => $user_stats,
                'total_sessions' => count($sessions),
                'total_badges' => count($all_badges),
                'earned_badges' => count($earned_badges),
                'test_results' => $test_results,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('test_badge_awarding_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Manually award a badge
     */
    public function rest_award_badge_manually($request) {
        try {
            $user_id = get_current_user_id() ?: 1;
            $badge_key = $request->get_param('badge_key');
            
            if (!$badge_key) {
                return new WP_Error('missing_badge_key', 'Badge key is required', array('status' => 400));
            }
            
            $database = new JPH_Database();
            
            // Get the badge details
            $badge = $database->get_badge($badge_key);
            if (!$badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            // Check if user already has this badge
            $user_badges = $database->get_user_badges($user_id);
            foreach ($user_badges as $earned_badge) {
                if ($earned_badge['badge_key'] == $badge['badge_key']) {
                    return new WP_Error('badge_already_earned', 'User already has this badge', array('status' => 400));
                }
            }
            
            // Award the badge
            $result = $database->award_badge(
                $user_id,
                $badge['badge_key']
            );
            
            if ($result) {
                // Add XP reward, gems reward, and update badge count
                    $gamification = new JPH_Gamification();
                    $user_stats = $gamification->get_user_stats($user_id);
                $update_data = array();
                if ($badge['xp_reward'] > 0) {
                    $update_data['total_xp'] = $user_stats['total_xp'] + intval($badge['xp_reward']);
                }
                if ($badge['gem_reward'] > 0) {
                    $update_data['gems_balance'] = $user_stats['gems_balance'] + intval($badge['gem_reward']);
                    // Record gems transaction
                    $database->record_gems_transaction(
                        $user_id,
                        'earned',
                        intval($badge['gem_reward']),
                        'badge_' . $badge['badge_key'],
                        'Earned ' . intval($badge['gem_reward']) . ' gems for earning badge: ' . $badge['name']
                    );
                }
                $update_data['badges_earned'] = $user_stats['badges_earned'] + 1;
                $database->update_user_stats($user_id, $update_data);
                
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Badge awarded successfully',
                    'badge' => $badge,
                    'xp_reward' => $badge['xp_reward'],
                    'timestamp' => current_time('mysql')
                ));
            } else {
                return new WP_Error('award_failed', 'Failed to award badge', array('status' => 500));
            }
        } catch (Exception $e) {
            return new WP_Error('award_badge_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Award First Steps badge directly
     */
    public function rest_award_first_steps($request) {
        try {
            $user_id = get_current_user_id() ?: 1;
            global $wpdb;
            
            // Check if user already has this badge
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}jph_user_badges WHERE user_id = %d AND badge_key = %s",
                $user_id, '1'
            ));
            
            if ($existing) {
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'First Steps badge already earned',
                    'already_earned' => true,
                    'timestamp' => current_time('mysql')
                ));
            }
            
            // Insert the badge directly
            $result = $wpdb->insert(
                $wpdb->prefix . 'jph_user_badges',
                array(
                    'user_id' => $user_id,
                    'badge_key' => '1',
                    'badge_name' => 'First Steps',
                    'badge_description' => 'Complete your first practice session',
                    'badge_icon' => '🎯',
                    'earned_at' => current_time('mysql')
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s')
            );
            
            if ($result !== false) {
                // Add XP reward
                $gamification = new JPH_Gamification();
                $user_stats = $gamification->get_user_stats($user_id);
                $new_xp = $user_stats['total_xp'] + 50;
                
                $wpdb->update(
                    $wpdb->prefix . 'jph_user_stats',
                    array('total_xp' => $new_xp),
                    array('user_id' => $user_id),
                    array('%d'),
                    array('%d')
                );
                
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'First Steps badge awarded successfully!',
                    'badge' => array(
                        'name' => 'First Steps',
                        'description' => 'Complete your first practice session',
                        'xp_reward' => 50
                    ),
                    'new_xp' => $new_xp,
                    'timestamp' => current_time('mysql')
                ));
            } else {
                return new WP_Error('insert_failed', 'Failed to insert badge: ' . $wpdb->last_error, array('status' => 500));
            }
        } catch (Exception $e) {
            return new WP_Error('award_first_steps_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * AJAX: Award First Steps badge
     */
    public function ajax_award_first_steps_badge() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'jph_award_badge')) {
            wp_die('Security check failed');
        }
        
        try {
            $user_id = get_current_user_id() ?: 1;
            global $wpdb;
            
            // Check if user already has this badge
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}jph_user_badges WHERE user_id = %d AND badge_key = %s",
                $user_id, '1'
            ));
            
            if ($existing) {
                wp_send_json_success(array(
                    'message' => 'First Steps badge already earned',
                    'already_earned' => true
                ));
            }
            
            // Insert the badge directly
            $result = $wpdb->insert(
                $wpdb->prefix . 'jph_user_badges',
                array(
                    'user_id' => $user_id,
                    'badge_key' => '1',
                    'badge_name' => 'First Steps',
                    'badge_description' => 'Complete your first practice session',
                    'badge_icon' => '🎯',
                    'earned_at' => current_time('mysql')
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s')
            );
            
            if ($result !== false) {
                // Add XP reward, gems reward, and update badge count
                $gamification = new JPH_Gamification();
                $user_stats = $gamification->get_user_stats($user_id);
                $new_xp = $user_stats['total_xp'] + 50;
                $new_gems = $user_stats['gems_balance'] + 5; // First Steps badge gives 5 gems
                $new_badge_count = $user_stats['badges_earned'] + 1;
                
                $wpdb->update(
                    $wpdb->prefix . 'jph_user_stats',
                    array(
                        'total_xp' => $new_xp,
                        'gems_balance' => $new_gems,
                        'badges_earned' => $new_badge_count
                    ),
                    array('user_id' => $user_id),
                    array('%d', '%d', '%d'),
                    array('%d')
                );
                
                // Record gems transaction
                $database = new JPH_Database();
                $database->record_gems_transaction(
                    $user_id,
                    'earned',
                    5,
                    'badge_1',
                    'Earned 5 gems for earning badge: First Steps'
                );
                
                wp_send_json_success(array(
                    'message' => 'First Steps badge awarded successfully!',
                    'new_xp' => $new_xp,
                    'new_gems' => $new_gems
                ));
            } else {
                wp_send_json_error('Failed to insert badge: ' . $wpdb->last_error);
            }
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Test badge event tracking
     */
    public function ajax_test_badge_event() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_test_badge_event')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $badge_key = sanitize_text_field($_POST['badge_key'] ?? '');
            if (empty($badge_key)) {
                wp_send_json_error('Badge key is required');
            }
            
            $user_id = get_current_user_id();
            
            // Check if user exists
            $user = get_user_by('id', $user_id);
            if (!$user) {
                wp_send_json_error('Current user not found');
            }
            
            // Get badge data
            $database = new JPH_Database();
            $badge = $database->get_badge($badge_key);
        
            if (!$badge) {
                wp_send_json_error('Badge not found: ' . $badge_key);
            }
            
            // Check if badge has FluentCRM tracking enabled
            if (empty($badge['fluent_event_enabled'])) {
                wp_send_json_success(array(
                    'message' => "Badge '{$badge['name']}' has FluentCRM tracking disabled. Enable 'FluentCRM Event Tracking' in Badge Management to test event firing."
                ));
            }
            
            // Trigger the badge event manually
            $result = $this->trigger_badge_fluentcrm_event($badge, $user_id, $database->get_user_stats($user_id));
            
            if ($result['success']) {
                wp_send_json_success(array(
                    'message' => "Badge event test successful for '{$badge['name']}' (User: {$user->user_email}) - Event Key: {$badge['fluent_event_key']}"
                ));
            } else {
                wp_send_json_error($result['message']);
            }
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Test all badge events
     */
    public function ajax_test_all_badge_events() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_test_all_badge_events')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $user_id = get_current_user_id();
            $user = get_user_by('id', $user_id);
            if (!$user) {
                wp_send_json_error('Current user not found');
            }
            
            $database = new JPH_Database();
            $badges = $database->get_badges(false); // Get all badges
            
            $results = array();
            $success_count = 0;
            $total_count = count($badges);
            
            foreach ($badges as $badge) {
                if (!empty($badge['fluent_event_enabled'])) {
                    $result = $this->trigger_badge_fluentcrm_event($badge, $user_id, $database->get_user_stats($user_id));
                    $status = $result['success'] ? 'SUCCESS' : 'FAILED - ' . $result['message'];
                    $results[] = "{$badge['name']}: {$status}";
                    
                    if ($result['success']) {
                        $success_count++;
                    }
                } else {
                    $results[] = "{$badge['name']}: SKIPPED - FluentCRM tracking disabled";
                    $total_count--; // Don't count disabled badges
                }
           }
            
            $summary = "<strong>Badge Event Test Summary: {$success_count}/{$total_count} badges successful</strong><br><br>";
            $detailed_results = implode('<br>', $results);
            
            wp_send_json_success(array(
                'message' => $summary . $detailed_results
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Get event logs from wp_fc_event_tracking table
     */
    public function ajax_get_event_logs() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_get_event_logs')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            global $wpdb;
            
            // Check if FluentCRM event tracking table exists
            $table_name = $wpdb->prefix . 'fc_event_tracking';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
            
            if (!$table_exists) {
                wp_send_json_error('FluentCRM event tracking table (wp_fc_event_tracking) does not exist. Make sure FluentCRM plugin is installed and activated.');
            }
            
            // Get recent event logs (last 50 entries)
            $events = $wpdb->get_results(
                "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 50",
                ARRAY_A
            );
            
            if (empty($events)) {
                wp_send_json_success('No event tracking logs found in wp_fc_event_tracking table.');
            }
            
            $log_output = "Recent Event Tracking Logs (Last 50 entries):\n\n";
            $log_output .= "Total events found: " . count($events) . "\n\n";
            
            foreach ($events as $event) {
                $log_output .= "Event ID: {$event['id']}\n";
                $log_output .= "Contact ID: {$event['contact_id']}\n";
                $log_output .= "Event Key: {$event['event_key']}\n";
                $log_output .= "Event Title: {$event['event_title']}\n";
                $log_output .= "Provider: {$event['provider']}\n";
                $log_output .= "Created: {$event['created_at']}\n";
                $log_output .= "Properties: " . (empty($event['properties']) ? 'None' : $event['properties']) . "\n";
                $log_output .= "---\n";
            }
            
            wp_send_json_success($log_output);
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Empty wp_fc_event_tracking table
     */
    public function ajax_empty_event_tracking_table() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_empty_event_tracking_table')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            global $wpdb;
            
            // Check if FluentCRM event tracking table exists
            $table_name = $wpdb->prefix . 'fc_event_tracking';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
            
            if (!$table_exists) {
                wp_send_json_error('FluentCRM event tracking table (wp_fc_event_tracking) does not exist. Make sure FluentCRM plugin is installed and activated.');
            }
            
            // Get count before deletion
            $count_before = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
            
            // Empty the table
            $result = $wpdb->query("TRUNCATE TABLE {$table_name}");
            
            if ($result === false) {
                wp_send_json_error('Failed to empty the event tracking table. Error: ' . $wpdb->last_error);
            }
            
            wp_send_json_success(array(
                'message' => "Successfully emptied wp_fc_event_tracking table. Removed {$count_before} event records."
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Test all events
     */
    public function ajax_test_all_events() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_test_all_events')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            // All available milestones
            $milestones = array(
                // Onboarding milestones
                'first_practice_item',
                'first_practice_session',
                'first_badge_earned',
                'first_week_complete',
                'first_month_complete',
                
                // Progress milestones
                'practice_sessions_5',
                'practice_sessions_10',
                'practice_sessions_25',
                'practice_sessions_50',
                'practice_sessions_100',
                
                // Consistency milestones
                'streak_3_days',
                'streak_7_days',
                'streak_14_days',
                'streak_30_days',
                'streak_100_days'
            );
            
            $results = array();
            $success_count = 0;
            $total_count = count($milestones);
            $user_id = get_current_user_id();
            
            // Check if user exists
            $user = get_user_by('id', $user_id);
            if (!$user) {
                wp_send_json_error('Current user not found');
            }
            
            // Check if event tracking is enabled
            $settings = get_option('jph_webhook_settings', array());
            if (empty($settings['enabled'])) {
                wp_send_json_success(array(
                    'message' => "Event tracking is disabled. User: {$user->user_email}. Enable event tracking in settings to test actual tracking.<br><br>All milestones would be tested: " . implode(', ', $milestones)
                ));
            }
            
            foreach ($milestones as $milestone) {
                $result = $this->track_milestone_event($milestone, $user_id, array('test' => true));
                $status = $result['success'] ? 'SUCCESS' : 'FAILED - ' . $result['message'];
                $results[] = "{$milestone}: {$status}";
                
                if ($result['success']) {
                    $success_count++;
                }
            }
            
            $summary = "<strong>Test Summary: {$success_count}/{$total_count} events successful</strong><br><br>";
            $detailed_results = implode('<br>', $results);
            
            wp_send_json_success(array(
                'message' => $summary . $detailed_results
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Get badge event logs
     */
    public function ajax_get_badge_event_logs() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_get_badge_event_logs')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $logs = get_option('jph_badge_events_log', array());
            $logs = array_slice(array_reverse($logs), 0, 50); // Last 50 entries
            
            $log_html = '';
            if (empty($logs)) {
                $log_html = '<div class="log-entry info">';
                $log_html .= '<strong>No badge event tracking logs found.</strong><br>';
                $log_html .= 'Events will appear here when badges are awarded with FluentCRM tracking enabled.<br>';
                $log_html .= '<br><strong>To test:</strong><br>';
                $log_html .= '1. Go to Badge Management and enable "FluentCRM Event Tracking" for a badge<br>';
                $log_html .= '2. Use the "Test Event" button in the badge table<br>';
                $log_html .= '3. Or award the badge to a user through normal gameplay<br>';
                $log_html .= '</div>';
            } else {
                $log_html .= '<div class="log-summary">';
                $log_html .= '<strong>Total logs found: ' . count($logs) . '</strong><br><br>';
                $log_html .= '</div>';
                
                foreach ($logs as $log) {
                    $status_class = $log['success'] ? 'success' : 'error';
                    $log_html .= "<div class='log-entry {$status_class}'>";
                    $log_html .= "<strong>" . date('Y-m-d H:i:s', $log['timestamp']) . "</strong> ";
                    $log_html .= "[🏆 {$log['badge_key']}] ";
                    
                    // Add user information
                    $user_email = $log['user_email'] ?? 'Unknown';
                    $user_name = $log['user_name'] ?? 'Unknown';
                    $contact_id = $log['contact_id'] ?? 'N/A';
                    
                    $log_html .= "<br><span class='log-user-info'>";
                    $log_html .= "👤 {$user_name} ({$user_email})";
                    if ($contact_id !== 'N/A' && $contact_id !== 'Not Found' && $contact_id !== 'Error') {
                        $log_html .= " | 🆔 Contact ID: {$contact_id}";
                    } else {
                        $log_html .= " | 🆔 Contact: {$contact_id}";
                    }
                    $log_html .= "</span><br>";
                    
                    // Add event details
                    if (isset($log['data']['event_key'])) {
                        $log_html .= "<strong>Event Key:</strong> {$log['data']['event_key']}<br>";
                    }
                    if (isset($log['data']['title'])) {
                        $log_html .= "<strong>Event Title:</strong> {$log['data']['title']}<br>";
                    }
                    
                    $log_html .= $log['message'];
                    $log_html .= "</div>";
                }
            }
            
            wp_send_json_success(array('logs' => $log_html));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Clear badge event logs
     */
    public function ajax_clear_badge_event_logs() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_clear_badge_event_logs')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            delete_option('jph_badge_events_log');
            wp_send_json_success(array('message' => 'Badge event tracking logs cleared successfully'));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Debug user badges
     */
    public function ajax_debug_user_badges() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_debug_user_badges')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $user_id = intval($_POST['user_id']);
            $database = new JPH_Database();
            $gamification = new JPH_Gamification();
            
            // Get comprehensive user data
            $user_stats = $gamification->get_user_stats($user_id);
            $user_badges = $database->get_user_badges($user_id);
            $sessions = $database->get_all_practice_sessions($user_id);
            $all_badges = $database->get_badges(true);
            
            wp_send_json_success(array(
                'user_stats' => $user_stats,
                'user_badges' => $user_badges,
                'practice_sessions_count' => count($sessions),
                'practice_sessions_sample' => array_slice($sessions, 0, 5),
                'total_available_badges' => count($all_badges),
                'available_badges' => array_map(function($badge) {
                    return array(
                        'badge_key' => $badge['badge_key'],
                        'name' => $badge['name'],
                        'criteria_type' => $badge['criteria_type'],
                        'criteria_value' => $badge['criteria_value']
                    );
                }, $all_badges)
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Debug badge assignment process
     */
    public function ajax_debug_badge_assignment() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_debug_badge_assignment')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $user_id = intval($_POST['user_id']);
            $newly_awarded = $this->check_and_award_badges($user_id);
            
            wp_send_json_success(array(
                'new_badges_awarded' => count($newly_awarded),
                'badges' => $newly_awarded,
                'message' => count($newly_awarded) > 0 ? 
                    'Awarded ' . count($newly_awarded) . ' new badge(s)' : 
                    'No new badges awarded'
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Debug Marathon badge specifically
     */
    public function ajax_debug_marathon_badge() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_debug_marathon_badge')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $user_id = intval($_POST['user_id']);
            $database = new JPH_Database();
            
            // Get Marathon badge configuration
            $marathon_badge = $database->get_badge_by_key('marathon');
            
            // Get user's practice sessions
            $sessions = $database->get_all_practice_sessions($user_id);
            
            // Check if user already has Marathon badge
            $user_badges = $database->get_user_badges($user_id);
            $has_marathon = false;
            foreach ($user_badges as $user_badge) {
                if ($user_badge['badge_key'] === 'marathon') {
                    $has_marathon = true;
                    break;
                }
            }
            
            // Check marathon criteria
            $eligible_sessions = array();
            foreach ($sessions as $session) {
                if ($session['duration_minutes'] >= 60) {
                    $eligible_sessions[] = $session;
                }
            }
            
            wp_send_json_success(array(
                'marathon_badge_config' => $marathon_badge,
                'user_has_marathon' => $has_marathon,
                'total_sessions' => count($sessions),
                'eligible_sessions_count' => count($eligible_sessions),
                'eligible_sessions' => $eligible_sessions,
                'criteria_check' => array(
                    'criteria_type' => $marathon_badge ? $marathon_badge['criteria_type'] : 'unknown',
                    'criteria_value' => $marathon_badge ? $marathon_badge['criteria_value'] : 'unknown',
                    'user_meets_criteria' => count($eligible_sessions) > 0
                )
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Simulate badge check for a user
     */
    public function ajax_simulate_badge_check() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_simulate_badge_check')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $user_id = intval($_POST['user_id']);
            $database = new JPH_Database();
            $gamification = new JPH_Gamification();
            
            // Get user stats and sessions
            $user_stats = $gamification->get_user_stats($user_id);
            $sessions = $database->get_all_practice_sessions($user_id);
            $all_badges = $database->get_badges(true);
            $earned_badges = $database->get_user_badges($user_id);
            $earned_badge_keys = array_column($earned_badges, 'badge_key');
            
            $potential_badges = array();
            
            // Simulate badge checking logic
            foreach ($all_badges as $badge) {
                if (in_array($badge['badge_key'], $earned_badge_keys)) {
                    continue; // Skip already earned
                }
                
                $should_award = false;
                $criteria_type = $badge['criteria_type'] ?? '';
                $criteria_value = intval($badge['criteria_value'] ?? 0);
                
                switch ($criteria_type) {
                    case 'total_xp':
                        $should_award = $user_stats['total_xp'] >= $criteria_value;
                        break;
                    case 'level_reached':
                        $should_award = $user_stats['current_level'] >= $criteria_value;
                        break;
                    case 'practice_sessions':
                        $should_award = $user_stats['total_sessions'] >= $criteria_value;
                        break;
                    case 'total_time':
                    case 'long_session':
                        foreach ($sessions as $session) {
                            if ($session['duration_minutes'] >= $criteria_value) {
                                $should_award = true;
                                break;
                            }
                        }
                        break;
                    case 'improvement_count':
                        $improvement_count = 0;
                        foreach ($sessions as $session) {
                            if ($session['improvement_detected']) {
                                $improvement_count++;
                            }
                        }
                        $should_award = $improvement_count >= $criteria_value;
                        break;
                }
                
                $potential_badges[] = array(
                    'badge_key' => $badge['badge_key'],
                    'name' => $badge['name'],
                    'criteria_type' => $criteria_type,
                    'criteria_value' => $criteria_value,
                    'should_award' => $should_award,
                    'user_stats' => $user_stats
                );
            }
            
            wp_send_json_success(array(
                'potential_badges' => $potential_badges,
                'user_stats' => $user_stats,
                'total_sessions' => count($sessions)
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Inspect badge database
     */
    public function ajax_inspect_badge_database() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_inspect_badge_database')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $database = new JPH_Database();
            $all_badges = $database->get_badges(true);
            
            // Group badges by criteria type for analysis
            $badges_by_criteria = array();
            foreach ($all_badges as $badge) {
                $criteria_type = $badge['criteria_type'] ?? 'unknown';
                if (!isset($badges_by_criteria[$criteria_type])) {
                    $badges_by_criteria[$criteria_type] = array();
                }
                $badges_by_criteria[$criteria_type][] = $badge;
            }
            
            wp_send_json_success(array(
                'total_badges' => count($all_badges),
                'badges_by_criteria' => $badges_by_criteria,
                'all_badges' => $all_badges
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Check practice sessions
     */
    public function ajax_check_practice_sessions() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_check_practice_sessions')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $user_id = intval($_POST['user_id']);
            $database = new JPH_Database();
            $sessions = $database->get_all_practice_sessions($user_id);
            
            // Analyze sessions
            $session_stats = array(
                'total_sessions' => count($sessions),
                'total_minutes' => 0,
                'sessions_over_60min' => array(),
                'longest_session' => null,
                'most_recent_session' => null
            );
            
            foreach ($sessions as $session) {
                $session_stats['total_minutes'] += $session['duration_minutes'];
                
                if ($session['duration_minutes'] >= 60) {
                    $session_stats['sessions_over_60min'][] = $session;
                }
                
                if (!$session_stats['longest_session'] || 
                    $session['duration_minutes'] > $session_stats['longest_session']['duration_minutes']) {
                    $session_stats['longest_session'] = $session;
                }
                
                if (!$session_stats['most_recent_session'] || 
                    strtotime($session['created_at']) > strtotime($session_stats['most_recent_session']['created_at'])) {
                    $session_stats['most_recent_session'] = $session;
                }
            }
            
            wp_send_json_success(array(
                'sessions' => $sessions,
                'session_stats' => $session_stats
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Reorder practice items
     */
    public function ajax_reorder_practice_items() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_reorder_practice_items')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $items_json = $_POST['items'];
            $items = json_decode($items_json, true);
            
            if (!$items || !is_array($items)) {
                wp_send_json_error('Invalid items data');
            }
            
            global $wpdb;
            $table = $wpdb->prefix . 'jph_practice_items';
            $user_id = get_current_user_id();
            
            // Update sort order for each item
            foreach ($items as $item) {
                $item_id = intval($item['id']);
                $sort_order = intval($item['sort_order']);
                
                if ($item_id > 0 && $sort_order >= 0) {
                    $result = $wpdb->update(
                        $table,
                        array('sort_order' => $sort_order),
                        array(
                            'id' => $item_id,
                            'user_id' => $user_id
                        ),
                        array('%d'),
                        array('%d', '%d')
                    );
                    
                    if ($result === false) {
                        error_log("JPH: Failed to update sort order for item {$item_id}");
                    }
                }
            }
            
            wp_send_json_success(array(
                'message' => 'Practice items reordered successfully!',
                'items_count' => count($items)
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Debug practice items database
     */
    public function ajax_debug_practice_items() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_debug_practice_items')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            global $wpdb;
            $database = new JPH_Database();
            $user_id = get_current_user_id();
            
            $table_name = $wpdb->prefix . 'jph_practice_items';
            
            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
            
            // Check if sort_order column exists
            $sort_order_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'sort_order'");
            
            // Get all practice items for current user
            $all_items = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at ASC",
                $user_id
            ), ARRAY_A);
            
            // Get active practice items using database class method
            $active_items = $database->get_user_practice_items($user_id);
            
            // Test the exact query used by get_user_practice_items
            $test_query_items = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND is_active = 1 ORDER BY sort_order ASC, created_at ASC",
                $user_id
            ), ARRAY_A);
            
            // Check database structure
            $columns = $wpdb->get_col("DESCRIBE $table_name");
            
            wp_send_json_success(array(
                'debug_info' => array(
                    'table_name' => $table_name,
                    'table_exists' => (bool)$table_exists,
                    'sort_order_column_exists' => !empty($sort_order_exists),
                    'current_user_id' => $user_id,
                    'database_method_items' => $active_items,
                    'test_query_items' => $test_query_items,
                    'all_items_raw' => $all_items,
                    'columns' => $columns,
                    'total_items_count' => count($all_items),
                    'active_items_count' => count($active_items),
                    'test_query_count' => count($test_query_items)
                ),
                'items_summary' => array(
                    'database_method_count' => count($active_items),
                    'test_query_count' => count($test_query_items),
                    'all_items_count' => count($all_items),
                    'users_items' => array_filter($all_items, function($item) use ($user_id) {
                        return $item['user_id'] == $user_id;
                    })
                )
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Clear individual data sections
     */
    public function ajax_clear_data_section() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_data_management')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $data_type = sanitize_text_field($_POST['data_type']);
        global $wpdb;
        
        try {
            $count = 0;
            $message = '';
            
            switch ($data_type) {
                case 'practice_sessions':
                    $count = $wpdb->query("DELETE FROM {$wpdb->prefix}jph_practice_sessions");
                    // Also reset total_sessions counter in user stats
                    $stats_count = $wpdb->query("UPDATE {$wpdb->prefix}jph_user_stats SET total_sessions = 0, total_minutes = 0");
                    $message = "Deleted {$count} practice session records and reset counters for {$stats_count} users";
                    break;
                    
                case 'practice_items':
                    $count = $wpdb->query("DELETE FROM {$wpdb->prefix}jph_practice_items");
                    $message = "Deleted {$count} practice item definitions";
                    break;
                    
                case 'badges':
                    // Clear badge awards first, then badge definitions
                    $award_count = $wpdb->query("DELETE FROM {$wpdb->prefix}jph_user_badges");
                    $badge_count = $wpdb->query("DELETE FROM {$wpdb->prefix}jph_badges");
                    $message = "Deleted {$award_count} badge awards and {$badge_count} badge definitions";
                    break;
                    
                case 'user_stats':
                    $count = $wpdb->query("UPDATE {$wpdb->prefix}jph_user_stats SET 
                        total_xp = 0, 
                        current_level = 1, 
                        current_streak = 0, 
                        longest_streak = 0, 
                        total_sessions = 0, 
                        total_minutes = 0, 
                        badges_earned = 0,
                        gems_balance = 5");
                    $message = "Reset all statistics for {$count} users (kept hearts count and other settings)";
                    break;
                    
                case 'gems':
                    // Clear transactions and reset balances
                    $tx_count = $wpdb->query("DELETE FROM {$wpdb->prefix}jph_gem_transactions");
                    $balance_count = $wpdb->query("UPDATE {$wpdb->prefix}jph_user_stats SET gems_balance = 0");
                    $message = "Cleared {$tx_count} gem transactions and reset {$balance_count} user balances";
                    break;
                    
                case 'lesson_favorites':
                    $count = $wpdb->query("DELETE FROM {$wpdb->prefix}jph_lesson_favorites");
                    $message = "Deleted {$count} lesson favorite entries";
                    break;
                    
                default:
                    wp_send_json_error('Invalid data type');
                    return;
            }
            
            wp_send_json_success($message);
            
        } catch (Exception $e) {
            error_log('JPH Data Clear Error: ' . $e->getMessage());
            wp_send_json_error('Database error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Wipe all user data
     */
    public function ajax_wipe_all_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_wipe_all_data')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            global $wpdb;
            
            // Get table names
            $tables = array(
                'practice_items' => $wpdb->prefix . 'jph_practice_items',
                'practice_sessions' => $wpdb->prefix . 'jph_practice_sessions',
                'user_stats' => $wpdb->prefix . 'jph_user_stats',
                'user_badges' => $wpdb->prefix . 'jph_user_badges',
                'lesson_favorites' => $wpdb->prefix . 'jph_lesson_favorites'
            );
            
            $deleted_counts = array();
            
            // Delete all data from each table
            foreach ($tables as $table_name => $table) {
                $count = $wpdb->query("DELETE FROM {$table}");
                $deleted_counts[$table_name] = $count;
            }
            
            // Clear event logs
            delete_option('jph_event_logs');
            
            $message = 'All user data wiped successfully! Deleted: ';
            $message_parts = array();
            foreach ($deleted_counts as $table => $count) {
                $message_parts[] = "{$count} {$table}";
            }
            $message .= implode(', ', $message_parts);
            
            wp_send_json_success(array('message' => $message));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Clear all user data (testing)
     */
    public function ajax_clear_all_user_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_clear_all_data')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        
        try {
            $deleted_counts = array();
            
            // Clear practice sessions and reset counters
            $count = $wpdb->query("DELETE FROM {$wpdb->prefix}jph_practice_sessions");
            $stats_count = $wpdb->query("UPDATE {$wpdb->prefix}jph_user_stats SET total_sessions = 0, total_minutes = 0");
            $deleted_counts['practice sessions'] = $count;
            
            // Clear practice items
            $count = $wpdb->query("DELETE FROM {$wpdb->prefix}jph_practice_items");
            $deleted_counts['practice items'] = $count;
            
            // Clear user badges (earned badges only - keep badge definitions)
            $count = $wpdb->query("DELETE FROM {$wpdb->prefix}jph_user_badges");
            $deleted_counts['earned badges'] = $count;
            
            // Reset user statistics to defaults
            $stats_count = $wpdb->query("UPDATE {$wpdb->prefix}jph_user_stats SET 
                total_xp = 0, 
                current_level = 1, 
                current_streak = 0, 
                longest_streak = 0, 
                badges_earned = 0,
                gems_balance = 5");
            $deleted_counts['user statistics'] = $stats_count;
            
            // Clear gem transactions
            $count = $wpdb->query("DELETE FROM {$wpdb->prefix}jph_gem_transactions");
            $deleted_counts['gem transactions'] = $count;
            
            // Clear lesson favorites
            $count = $wpdb->query("DELETE FROM {$wpdb->prefix}jph_lesson_favorites");
            $deleted_counts['lesson favorites'] = $count;
            
            $message_parts = array();
            foreach ($deleted_counts as $type => $count) {
                $message_parts[] = "{$count} {$type}";
            }
            $message = "Cleared: " . implode(', ', $message_parts) . ". Badge definitions preserved.";
            
            wp_send_json_success($message);
            
        } catch (Exception $e) {
            error_log('JPH Clear All Data Error: ' . $e->getMessage());
            wp_send_json_error('Database error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Reset all user statistics
     */
    public function ajax_reset_all_stats() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_reset_all_stats')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            global $wpdb;
            
            $table = $wpdb->prefix . 'jph_user_stats';
            $count = $wpdb->query("DELETE FROM {$table}");
            
            wp_send_json_success(array('message' => "Reset all user statistics successfully! Deleted {$count} user stat records."));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Clear all badges
     */
    public function ajax_clear_all_badges() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_clear_all_badges')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            global $wpdb;
            
            $table = $wpdb->prefix . 'jph_user_badges';
            $count = $wpdb->query("DELETE FROM {$table}");
            
            wp_send_json_success(array('message' => "Cleared all badges successfully! Removed {$count} earned badges."));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Clear all lesson favorites
     */
    public function ajax_clear_all_favorites() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_clear_all_favorites')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            global $wpdb;
            
            $table = $wpdb->prefix . 'jph_lesson_favorites';
            $count = $wpdb->query("DELETE FROM {$table}");
            
            wp_send_json_success(array('message' => "Cleared all lesson favorites successfully! Removed {$count} favorites."));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Update badge display order
     */
    public function ajax_update_badge_order() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_update_badge_order')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $badge_orders = json_decode(stripslashes($_POST['badge_orders']), true);
            
            if (!is_array($badge_orders)) {
                wp_send_json_error('Invalid badge orders data');
            }
            
            $database = new JPH_Database();
            foreach ($badge_orders as $badge_key => $display_order) {
                $result = $database->update_badge_display_order($badge_key, $display_order);
                if (is_wp_error($result)) {
                    wp_send_json_error($result->get_error_message());
                }
            }
            
            wp_send_json_success(array('message' => 'Badge order updated successfully!'));
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Test Auto-Shield Activation
     */
    public function ajax_test_auto_shield() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_test_auto_shield')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('read')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $user_id = get_current_user_id();
        $database = new JPH_Database();
        $gamification = new JPH_Gamification();
        
        // Get current stats
        $user_stats = $gamification->get_user_stats($user_id);
        
        // Simulate missing practice by setting last_practice_date to 2 days ago
        $two_days_ago = date('Y-m-d', strtotime('-2 days'));
        
        $database->update_user_stats($user_id, array(
            'last_practice_date' => $two_days_ago
        ));
        
        // Run the broken streak check (this should trigger auto-shield if available)
        $shield_used = $this->check_and_reset_broken_streaks($user_id, $user_stats);
        
        // Get updated stats
        $updated_stats = $gamification->get_user_stats($user_id);
        
        if ($shield_used) {
            wp_send_json_success(array(
                'message' => 'Auto-shield activated successfully!',
                'shield_used' => true,
                'new_shield_count' => $updated_stats['streak_shield_count'],
                'streak_maintained' => $updated_stats['current_streak'],
                'last_practice_date' => $updated_stats['last_practice_date']
            ));
        } else {
            wp_send_json_success(array(
                'message' => 'No shield available or streak was reset',
                'shield_used' => false,
                'new_shield_count' => $updated_stats['streak_shield_count'],
                'streak_maintained' => $updated_stats['current_streak'],
                'last_practice_date' => $updated_stats['last_practice_date']
            ));
        }
    }
    
    /**
     * AJAX handler for resetting streak (testing only)
     */
    public function ajax_reset_streak_test() {
        if (!current_user_can('read')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $user_id = get_current_user_id();
        $database = new JPH_Database();
        $gamification = new JPH_Gamification();
        
        // Reset streak to 0 for testing
        $database->update_user_stats($user_id, array(
            'current_streak' => 0,
            'last_practice_date' => null
        ));
        
        // Get updated stats
        $updated_stats = $gamification->get_user_stats($user_id);
        
        wp_send_json_success(array(
            'message' => 'Streak reset for testing',
            'new_streak' => $updated_stats['current_streak']
        ));
    }
    
    /**
     * AJAX handler for adding gems (testing only)
     */
    public function ajax_add_gems_test() {
        if (!current_user_can('read')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $user_id = get_current_user_id();
        $amount = intval($_POST['amount']);
        
        if ($amount <= 0) {
            wp_send_json_error('Invalid amount');
        }
        
        $database = new JPH_Database();
        $gamification = new JPH_Gamification();
        
        // Get current stats
        $user_stats = $gamification->get_user_stats($user_id);
        $new_balance = $user_stats['gems_balance'] + $amount;
        
        // Update gems balance
        $database->update_user_stats($user_id, array(
            'gems_balance' => $new_balance
        ));
        
        // Record transaction
        $database->record_gems_transaction($user_id, 'earned', $amount, 'test_add', 'Added ' . $amount . ' gems for testing', $new_balance);
        
        wp_send_json_success(array(
            'message' => 'Added ' . $amount . ' gems for testing',
            'new_balance' => $new_balance
        ));
    }
    
    /**
     * AJAX: Purchase Streak Shield
     */
    public function ajax_purchase_streak_shield() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_purchase_streak_shield')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('read')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }
        
        $result = $this->purchase_streak_shield($user_id);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX: Repair Streak
     */
    public function ajax_repair_streak() {
        if (!wp_verify_nonce($_POST['nonce'], 'jph_repair_streak')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('read')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }
        
        $days_to_repair = intval($_POST['days_to_repair'] ?? 1);
        
        if ($days_to_repair < 1 || $days_to_repair > 7) {
            wp_send_json_error('Invalid number of days to repair');
        }
        
        $result = $this->repair_streak($user_id, $days_to_repair);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX handler for deleting all badges
     */
    public function ajax_delete_all_badges() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'jph_delete_all_badges')) {
            wp_send_json_error('Invalid nonce');
        }
        
        // Check admin permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            global $wpdb;
            
            // Delete all badge awards first (foreign key constraint)
            $badges_table = $wpdb->prefix . 'jph_user_badges';
            $wpdb->query("TRUNCATE TABLE {$badges_table}");
            
            // Delete all badges
            $table_name = $wpdb->prefix . 'jph_badges';
            $result = $wpdb->query("TRUNCATE TABLE {$table_name}");
            
            if ($result !== false) {
                wp_send_json_success('All badges deleted successfully');
            } else {
                wp_send_json_error('Failed to delete badges: ' . $wpdb->last_error);
            }
            
        } catch (Exception $e) {
            wp_send_json_error('Error deleting badges: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Get database status
     */
    public function ajax_get_database_status() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $database = new JPH_Database();
            $status = $database->get_database_status();
            
            wp_send_json_success($status);
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Track milestone event in FluentCRM
     */
    private function track_milestone_event($milestone, $user_id, $additional_data = array()) {
        error_log("JPH Event Tracking: Attempting to track milestone '$milestone' for user $user_id");
        
        $settings = get_option('jph_webhook_settings', array());
        
        if (empty($settings['enabled'])) {
            error_log("JPH Event Tracking: Event tracking is disabled");
            return array('success' => false, 'message' => 'Event tracking disabled');
        }
        
        $milestone_settings = $settings['milestones'][$milestone] ?? array();
        if (empty($milestone_settings['enabled'])) {
            error_log("JPH Event Tracking: Milestone '$milestone' is disabled");
            return array('success' => false, 'message' => "Milestone {$milestone} is disabled");
        }
        
        // Check if FluentCRM is available
        if (!function_exists('do_action') || !function_exists('get_user_by')) {
            return array('success' => false, 'message' => 'FluentCRM not available');
        }
        
        // Get user data
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return array('success' => false, 'message' => 'User not found');
        }
        
        // Prepare event data
        $event_key = $settings['prefix'] . '_' . $milestone;
        $provider = $settings['provider'] ?? 'jazzedge-practice-hub';
        
        $event_data = array(
            'event_key' => $event_key,
            'title' => $this->get_milestone_title($milestone),
            'value' => $this->get_milestone_value($milestone, $additional_data),
            'email' => $user->user_email,
            'provider' => $provider
        );
        
        // Add additional data as custom fields
        if (!empty($additional_data)) {
            $event_data['custom_data'] = $additional_data;
        }
        
        try {
            error_log("JPH Event Tracking: Calling FluentCRM action hook with data: " . print_r($event_data, true));
            
            // Track the event using FluentCRM action hook
            do_action('fluent_crm/track_event_activity', $event_data, true);
            
            error_log("JPH Event Tracking: FluentCRM action hook called successfully");
            
            // Log the event attempt
            $this->log_event($milestone, $user_id, $event_data, true);
            
            return array('success' => true, 'message' => 'Event tracked successfully in FluentCRM');
        } catch (Exception $e) {
            error_log("JPH Event Tracking: Exception occurred: " . $e->getMessage());
            $this->log_event($milestone, $user_id, $event_data, false, $e->getMessage());
            return array('success' => false, 'message' => 'Event tracking failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Log event tracking activity
     */
    private function log_event($milestone, $user_id, $data, $success, $error_message = '') {
        $logs = get_option('jph_event_logs', array());
        
        // Get user information
        $user = get_user_by('id', $user_id);
        $user_email = $user ? $user->user_email : 'Unknown';
        $user_display_name = $user ? $user->display_name : 'Unknown';
        
        // Try to get FluentCRM contact ID
        $contact_id = $this->get_fluentcrm_contact_id($user_email);
        
        $log_entry = array(
            'timestamp' => time(),
            'milestone' => $milestone,
            'user_id' => $user_id,
            'user_email' => $user_email,
            'user_display_name' => $user_display_name,
            'contact_id' => $contact_id,
            'success' => $success,
            'message' => $success ? 'Event tracked successfully' : $error_message,
            'data' => $data
        );
        
        $logs[] = $log_entry;
        
        // Keep only last 1000 logs
        if (count($logs) > 1000) {
            $logs = array_slice($logs, -1000);
        }
        
        update_option('jph_event_logs', $logs);
    }
    
    /**
     * Get FluentCRM contact ID by email
     */
    private function get_fluentcrm_contact_id($email) {
        if (!function_exists('FluentCrmApi')) {
            return 'N/A';
        }
        
        try {
            $contact = FluentCrmApi('contacts')->getContact($email);
            return $contact ? $contact->id : 'Not Found';
        } catch (Exception $e) {
            return 'Error';
        }
    }
    
    /**
     * Get milestone title for FluentCRM
     */
    private function get_milestone_title($milestone) {
        $titles = array(
            'first_practice_item' => 'First Practice Item Added',
            'first_practice_session' => 'First Practice Session Logged',
            'first_badge_earned' => 'First Badge Earned',
            'first_week_complete' => 'First Week Complete',
            'first_month_complete' => 'First Month Complete',
            'practice_sessions_5' => '5 Practice Sessions Completed',
            'practice_sessions_10' => '10 Practice Sessions Completed',
            'practice_sessions_25' => '25 Practice Sessions Completed',
            'practice_sessions_50' => '50 Practice Sessions Completed',
            'practice_sessions_100' => '100 Practice Sessions Completed',
            'streak_3_days' => '3-Day Practice Streak',
            'streak_7_days' => '7-Day Practice Streak',
            'streak_14_days' => '14-Day Practice Streak',
            'streak_30_days' => '30-Day Practice Streak',
            'streak_100_days' => '100-Day Practice Streak'
        );
        
        return $titles[$milestone] ?? ucwords(str_replace('_', ' ', $milestone));
    }
    
    /**
     * Get milestone value for FluentCRM
     */
    private function get_milestone_value($milestone, $additional_data) {
        if (isset($additional_data['test']) && $additional_data['test']) {
            return 'Test event - ' . $this->get_milestone_title($milestone);
        }
        
        $value_parts = array();
        
        // Add session count if available
        if (isset($additional_data['session_count'])) {
            $value_parts[] = $additional_data['session_count'] . ' sessions';
        }
        
        // Add streak count if available
        if (isset($additional_data['current_streak'])) {
            $value_parts[] = $additional_data['current_streak'] . ' day streak';
        }
        
        // Add item count if available
        if (isset($additional_data['item_count'])) {
            $value_parts[] = $additional_data['item_count'] . ' practice items';
        }
        
        // Add badge count if available
        if (isset($additional_data['badge_count'])) {
            $value_parts[] = $additional_data['badge_count'] . ' badges earned';
        }
        
        if (!empty($value_parts)) {
            return implode(', ', $value_parts);
        }
        
        return $this->get_milestone_title($milestone);
    }
    
    /**
     * Check for practice session milestones and trigger events
     */
    private function check_practice_session_milestones($user_id, $updated_stats, $streak_result) {
        $total_sessions = $updated_stats['total_sessions'] ?? 0;
        $current_streak = $streak_result['current_streak'] ?? 0;
        
        error_log("JPH Milestone Check: User $user_id has $total_sessions total sessions");
        
        // Check first practice session milestone
        if ($total_sessions === 1) {
            error_log("JPH Milestone: Triggering first_practice_session for user $user_id");
            $this->track_milestone_event('first_practice_session', $user_id, array(
                'session_count' => $total_sessions,
                'stats' => $updated_stats
            ));
        }
        
        // Check practice session count milestones
        $session_milestones = array(5, 10, 25, 50, 100);
        foreach ($session_milestones as $milestone) {
            if ($total_sessions === $milestone) {
                error_log("JPH Milestone: Triggering practice_sessions_{$milestone} for user $user_id");
                $this->track_milestone_event("practice_sessions_{$milestone}", $user_id, array(
                    'session_count' => $total_sessions,
                    'stats' => $updated_stats
                ));
            }
        }
        
        // Check streak milestones
        $streak_milestones = array(3, 7, 14, 30, 100);
        foreach ($streak_milestones as $milestone) {
            if ($current_streak === $milestone) {
                error_log("JPH Milestone: Triggering streak_{$milestone}_days for user $user_id");
                $this->track_milestone_event("streak_{$milestone}_days", $user_id, array(
                    'current_streak' => $current_streak,
                    'stats' => $updated_stats
                ));
            }
        }
        
        // Check time-based milestones (first week/month complete)
        $this->check_time_based_milestones($user_id, $updated_stats);
    }
    
    /**
     * Daily cron job to check time-based milestones for all users
     */
    public function daily_milestone_check() {
        error_log('JPH Cron: Starting daily milestone check');
        
        $database = new JPH_Database();
        $gamification = new JPH_Gamification();
        
        // Get all users who have practice sessions
        global $wpdb;
        $users_with_sessions = $wpdb->get_results("
            SELECT DISTINCT user_id 
            FROM {$wpdb->prefix}jph_practice_sessions 
            ORDER BY user_id
        ");
        
        $milestones_checked = 0;
        $milestones_triggered = 0;
        $streaks_reset = 0;
        
        foreach ($users_with_sessions as $user_data) {
            $user_id = $user_data->user_id;
            $user_stats = $gamification->get_user_stats($user_id);
            
            if ($user_stats) {
                // Check and reset broken streaks
                $streak_reset = $this->check_and_reset_broken_streaks($user_id, $user_stats);
                if ($streak_reset) {
                    $streaks_reset++;
                    // Get updated stats after streak reset
                    $user_stats = $gamification->get_user_stats($user_id);
                }
                
                // Check time-based milestones
                $result = $this->check_time_based_milestones($user_id, $user_stats, true);
                $milestones_checked++;
                
                if ($result['triggered']) {
                    $milestones_triggered += $result['count'];
                }
            }
        }
        
        error_log("JPH Cron: Checked $milestones_checked users, triggered $milestones_triggered milestones, reset $streaks_reset broken streaks");
    }
    
    /**
     * Purchase a Streak Shield
     */
    public function purchase_streak_shield($user_id) {
        error_log("JPH Shield: Purchase attempt for user $user_id");
        
        $database = new JPH_Database();
        $user_stats = $database->get_user_stats($user_id);
        
        if (!$user_stats) {
            error_log("JPH Shield: User $user_id not found");
            return array('success' => false, 'message' => 'User not found');
        }
        
        $shield_cost = 50;
        $current_gems = $user_stats['gems_balance'] ?? 0;
        $current_shields = $user_stats['streak_shield_count'] ?? 0;
        
        error_log("JPH Shield: User $user_id - Gems: $current_gems, Shields: $current_shields, Cost: $shield_cost");
        
        // Check if user has enough gems
        if ($current_gems < $shield_cost) {
            error_log("JPH Shield: User $user_id insufficient gems ($current_gems < $shield_cost)");
            return array('success' => false, 'message' => 'Not enough gems. Need ' . $shield_cost . ' gems.');
        }
        
        // Check if user already has maximum shields
        if ($current_shields >= 3) {
            error_log("JPH Shield: User $user_id already has maximum shields ($current_shields >= 3)");
            return array('success' => false, 'message' => 'You already have the maximum number of shields (3). You cannot purchase more shields.');
        }
        
        // Check monthly purchase limit
        $monthly_purchases = $database->get_monthly_shield_purchases($user_id);
        error_log("JPH Shield: User $user_id monthly purchases: $monthly_purchases");
        if ($monthly_purchases >= 5) {
            error_log("JPH Shield: User $user_id monthly limit reached ($monthly_purchases >= 5)");
            return array('success' => false, 'message' => 'Monthly shield limit reached (5 per month).');
        }
        
        // Deduct gems and add shield
        $new_gem_balance = $current_gems - $shield_cost;
        $new_shield_count = $current_shields + 1;
        
        error_log("JPH Shield: User $user_id purchasing - New gems: $new_gem_balance, New shields: $new_shield_count");
        
        $database->update_user_stats($user_id, array(
            'gems_balance' => $new_gem_balance,
            'streak_shield_count' => $new_shield_count
        ));
        
        // Record transaction
        $database->record_gems_transaction(
            $user_id,
            'spent',
            -$shield_cost,
            'streak_shield_purchase',
            'Purchased Streak Shield for ' . $shield_cost . ' gems'
        );
        
        error_log("JPH Shield: User $user_id purchase successful - Shield count: $new_shield_count, Gem balance: $new_gem_balance");
        
        return array(
            'success' => true, 
            'message' => 'Streak Shield purchased!',
            'new_gem_balance' => $new_gem_balance,
            'new_shield_count' => $new_shield_count
        );
    }
    
    /**
     * Use a Streak Shield (auto-called when streak would break)
     */
    public function use_streak_shield($user_id) {
        error_log("JPH Shield: Use attempt for user $user_id");
        
        $database = new JPH_Database();
        $user_stats = $database->get_user_stats($user_id);
        
        if (!$user_stats) {
            error_log("JPH Shield: User $user_id not found for shield use");
            return false;
        }
        
        $current_shields = $user_stats['streak_shield_count'] ?? 0;
        error_log("JPH Shield: User $user_id has $current_shields shields available");
        
        if ($current_shields <= 0) {
            error_log("JPH Shield: User $user_id has no shields to use");
            return false;
        }
        
        // Use one shield
        $new_shield_count = $current_shields - 1;
        error_log("JPH Shield: User $user_id using shield - New count: $new_shield_count");
        
        $database->update_user_stats($user_id, array(
            'streak_shield_count' => $new_shield_count
        ));
        
        // Record usage
        $database->record_gems_transaction(
            $user_id,
            'spent',
            0, // No gem cost for using
            'streak_shield_used',
            'Used Streak Shield to maintain streak'
        );
        
        error_log("JPH Shield: User $user_id shield used successfully - Remaining: $new_shield_count");
        return true;
    }
    
    /**
     * Repair a broken streak
     */
    public function repair_streak($user_id, $days_to_repair) {
        error_log("JPH Recovery: Repair attempt for user $user_id - Days: $days_to_repair");
        
        $database = new JPH_Database();
        $user_stats = $database->get_user_stats($user_id);
        
        if (!$user_stats) {
            error_log("JPH Recovery: User $user_id not found");
            return array('success' => false, 'message' => 'User not found');
        }
        
        $cost_per_day = 25;
        $total_cost = $days_to_repair * $cost_per_day;
        $current_gems = $user_stats['gems_balance'] ?? 0;
        $current_streak = $user_stats['current_streak'] ?? 0;
        $last_recovery = $user_stats['last_streak_recovery_date'] ?? null;
        $weekly_recoveries = $user_stats['streak_recovery_count_this_week'] ?? 0;
        
        error_log("JPH Recovery: User $user_id - Gems: $current_gems, Cost: $total_cost, Current streak: $current_streak, Last recovery: $last_recovery, Weekly: $weekly_recoveries");
        
        // Check if user has enough gems
        if ($current_gems < $total_cost) {
            error_log("JPH Recovery: User $user_id insufficient gems ($current_gems < $total_cost)");
            return array('success' => false, 'message' => 'Not enough gems. Need ' . $total_cost . ' gems.');
        }
        
        // Check limits (max 7 days, 24h cooldown, 2 per week)
        if ($days_to_repair > 7) {
            error_log("JPH Recovery: User $user_id exceeds max days ($days_to_repair > 7)");
            return array('success' => false, 'message' => 'Maximum 7 days can be repaired at once.');
        }
        
        if ($last_recovery && strtotime($last_recovery) > strtotime('-24 hours')) {
            error_log("JPH Recovery: User $user_id in cooldown (last recovery: $last_recovery)");
            return array('success' => false, 'message' => '24-hour cooldown active. Last recovery: ' . $last_recovery);
        }
        
        if ($weekly_recoveries >= 2) {
            error_log("JPH Recovery: User $user_id weekly limit reached ($weekly_recoveries >= 2)");
            return array('success' => false, 'message' => 'Weekly recovery limit reached (2 per week).');
        }
        
        // Calculate new streak
        $new_streak = $current_streak + $days_to_repair;
        
        // Deduct gems and update streak
        $new_gem_balance = $current_gems - $total_cost;
        $new_weekly_count = $weekly_recoveries + 1;
        
        error_log("JPH Recovery: User $user_id repairing - New streak: $new_streak, New gems: $new_gem_balance, New weekly count: $new_weekly_count");
        
        $database->update_user_stats($user_id, array(
            'gems_balance' => $new_gem_balance,
            'current_streak' => $new_streak,
            'last_streak_recovery_date' => current_time('Y-m-d'),
            'streak_recovery_count_this_week' => $new_weekly_count
        ));
        
        // Record transaction
        $database->record_gems_transaction(
            $user_id,
            'spent',
            -$total_cost,
            'streak_recovery',
            'Repaired ' . $days_to_repair . ' days of streak for ' . $total_cost . ' gems'
        );
        
        error_log("JPH Recovery: User $user_id repair successful - New streak: $new_streak, Remaining gems: $new_gem_balance");
        
        return array(
            'success' => true,
            'message' => 'Streak repaired! +' . $days_to_repair . ' days',
            'new_gem_balance' => $new_gem_balance,
            'new_streak' => $new_streak
        );
    }
    
    /**
     * Check and reset broken streaks for users who haven't practiced recently
     */
    private function check_and_reset_broken_streaks($user_id, $user_stats) {
        $last_practice_date = $user_stats['last_practice_date'] ?? null;
        $current_streak = $user_stats['current_streak'] ?? 0;
        
        if (!$last_practice_date || $current_streak === 0) {
            return false; // No streak to reset
        }
        
        $today = current_time('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // If last practice was more than 1 day ago, check for shield first
        if ($last_practice_date < $yesterday) {
            $current_shields = $user_stats['streak_shield_count'] ?? 0;
            
            if ($current_shields > 0) {
                // Use shield to maintain streak
                $this->use_streak_shield($user_id);
                error_log("JPH Cron: Used Streak Shield for user $user_id (last practice: $last_practice_date)");
                return true;
            } else {
                // No shield available, reset streak
                global $wpdb;
                $table_name = $wpdb->prefix . 'jph_user_stats';
                
                $wpdb->update(
                    $table_name,
                    array('current_streak' => 0),
                    array('user_id' => $user_id),
                    array('%d'),
                    array('%d')
                );
                
                error_log("JPH Cron: Reset broken streak for user $user_id (last practice: $last_practice_date)");
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for time-based milestones (first week/month complete)
     */
    private function check_time_based_milestones($user_id, $updated_stats, $from_cron = false) {
        $last_practice_date = $updated_stats['last_practice_date'] ?? null;
        
        if (!$last_practice_date) {
            return $from_cron ? array('triggered' => false, 'count' => 0) : null;
        }
        
        // Get user's first practice session to determine when they started
        $database = new JPH_Database();
        $sessions = $database->get_practice_sessions($user_id, 1, 0);
        
        if (empty($sessions)) {
            return $from_cron ? array('triggered' => false, 'count' => 0) : null;
        }
        
        $first_session = $sessions[0];
        
        if (!$first_session) {
            return $from_cron ? array('triggered' => false, 'count' => 0) : null;
        }
        
        $first_practice_date = new DateTime($first_session['created_at']);
        $current_date = new DateTime();
        $days_since_start = $first_practice_date->diff($current_date)->days;
        
        $milestones_triggered = 0;
        $context = $from_cron ? 'Cron' : 'Session';
        
        error_log("JPH Time Milestone ($context): User $user_id started $days_since_start days ago");
        
        // Check first week complete (7 days since first practice)
        if ($days_since_start === 7) {
            error_log("JPH Milestone ($context): Triggering first_week_complete for user $user_id");
            $this->track_milestone_event('first_week_complete', $user_id, array(
                'days_since_start' => $days_since_start,
                'stats' => $updated_stats,
                'triggered_by' => $from_cron ? 'cron' : 'session'
            ));
            $milestones_triggered++;
        }
        
        // Check first month complete (30 days since first practice)
        if ($days_since_start === 30) {
            error_log("JPH Milestone ($context): Triggering first_month_complete for user $user_id");
            $this->track_milestone_event('first_month_complete', $user_id, array(
                'days_since_start' => $days_since_start,
                'stats' => $updated_stats,
                'triggered_by' => $from_cron ? 'cron' : 'session'
            ));
            $milestones_triggered++;
        }
        
        if ($from_cron) {
            return array(
                'triggered' => $milestones_triggered > 0,
                'count' => $milestones_triggered
            );
        }
    }
    
    /**
     * Check for practice item milestones and trigger events
     */
    private function check_practice_item_milestones($user_id) {
        $database = new JPH_Database();
        $practice_items = $database->get_user_practice_items($user_id);
        $item_count = count($practice_items);
        
        // Check first practice item milestone
        if ($item_count === 1) {
            $this->track_milestone_event('first_practice_item', $user_id, array(
                'item_count' => $item_count,
                'practice_items' => $practice_items
            ));
        }
    }
    
    /**
     * Check for badge milestones and trigger events
     */
    private function check_badge_milestones($user_id) {
        $database = new JPH_Database();
        $user_badges = $database->get_user_badges($user_id);
        $badge_count = count($user_badges);
        
        // Check first badge milestone
        if ($badge_count === 1) {
            $this->track_milestone_event('first_badge_earned', $user_id, array(
                'badge_count' => $badge_count,
                'badges' => $user_badges
            ));
        }
    }
    
    /**
     * Calculate streak from practice sessions
     */
    private function calculate_streak_from_sessions($user_id, $sessions) {
        // Filter sessions for this user
        $user_sessions = array_filter($sessions, function($session) use ($user_id) {
            return $session->user_id == $user_id;
        });
        
        if (empty($user_sessions)) {
            return array('current_streak' => 0, 'longest_streak' => 0);
        }
        
        // Get unique practice dates
        $practice_dates = array();
        foreach ($user_sessions as $session) {
            $date = date('Y-m-d', strtotime($session->created_at));
            if (!in_array($date, $practice_dates)) {
                $practice_dates[] = $date;
            }
        }
        
        // Sort dates in descending order (most recent first)
        rsort($practice_dates);
        
        if (empty($practice_dates)) {
            return array('current_streak' => 0, 'longest_streak' => 0);
        }
        
        // Calculate current streak
        $current_streak = 0;
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Check if they practiced today or yesterday
        if (in_array($today, $practice_dates)) {
            $current_streak = 1;
            $check_date = $today;
        } elseif (in_array($yesterday, $practice_dates)) {
            $current_streak = 1;
            $check_date = $yesterday;
        } else {
            // No recent practice, streak is 0
            return array('current_streak' => 0, 'longest_streak' => $this->calculate_longest_streak($practice_dates));
        }
        
        // Count consecutive days
        for ($i = 1; $i < count($practice_dates); $i++) {
            $expected_date = date('Y-m-d', strtotime($check_date . ' -' . $i . ' days'));
            if (in_array($expected_date, $practice_dates)) {
                $current_streak++;
            } else {
                break;
            }
        }
        
        return array(
            'current_streak' => $current_streak,
            'longest_streak' => $this->calculate_longest_streak($practice_dates)
        );
    }
    
    /**
     * Calculate longest streak from practice dates
     */
    private function calculate_longest_streak($practice_dates) {
        if (empty($practice_dates)) {
            return 0;
        }
        
        // Sort dates in ascending order
        sort($practice_dates);
        
        $longest_streak = 1;
        $current_streak = 1;
        
        for ($i = 1; $i < count($practice_dates); $i++) {
            $prev_date = strtotime($practice_dates[$i - 1]);
            $curr_date = strtotime($practice_dates[$i]);
            
            // Check if dates are consecutive
            if ($curr_date - $prev_date == 86400) { // 86400 seconds = 1 day
                $current_streak++;
            } else {
                $longest_streak = max($longest_streak, $current_streak);
                $current_streak = 1;
            }
        }
        
        return max($longest_streak, $current_streak);
    }
    
    /**
     * REST API: Delete practice session
     */
    public function rest_delete_practice_session($request) {
        try {
            $database = new JPH_Database();
            $session_id = $request->get_param('id');
            
            if (!$session_id) {
                return new WP_Error('missing_id', 'Session ID is required', array('status' => 400));
            }
            
            $result = $database->delete_practice_session($session_id);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Practice session deleted successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('delete_session_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Update practice item
     */
    public function rest_update_practice_item($request) {
        try {
            $database = new JPH_Database();
            $item_id = $request->get_param('id');
            $name = $request->get_param('name');
            $category = $request->get_param('category');
            $description = $request->get_param('description');
            
            if (!$item_id) {
                return new WP_Error('missing_id', 'Item ID is required', array('status' => 400));
            }
            
            $result = $database->update_practice_item($item_id, $name, $category, $description);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Practice item updated successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('update_item_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Delete practice item
     */
    public function rest_delete_practice_item($request) {
        try {
            $database = new JPH_Database();
            $item_id = $request->get_param('id');
            
            if (!$item_id) {
                return new WP_Error('missing_id', 'Item ID is required', array('status' => 400));
            }
            
            $result = $database->delete_practice_item($item_id);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Practice item deleted successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('delete_item_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get lesson favorites
     */
    public function rest_get_lesson_favorites($request) {
        try {
            $database = new JPH_Database();
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
            }
            
            // Check if user is admin (can see all favorites)
            $is_admin = current_user_can('manage_options');
            
            if ($is_admin) {
                // Admin can see all favorites
                $favorites = $database->get_lesson_favorites();
            } else {
                // Regular user sees only their own favorites
                $favorites = $database->get_lesson_favorites($user_id);
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'favorites' => $favorites,
                'count' => count($favorites),
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('get_favorites_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    
    /**
     * REST API: Add lesson favorite
     */
    public function rest_add_lesson_favorite($request) {
        try {
            $database = new JPH_Database();
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
            }
            
            $title = sanitize_text_field($request->get_param('title'));
            $url = esc_url_raw($request->get_param('url'));
            $category = sanitize_text_field($request->get_param('category')) ?: 'lesson';
            $description = sanitize_textarea_field($request->get_param('description'));
            
            if (empty($title) || empty($url)) {
                return new WP_Error('missing_data', 'Title and URL are required', array('status' => 400));
            }
            
            $favorite_data = array(
                'user_id' => $user_id,
                'title' => $title,
                'url' => $url,
                'category' => $category,
                'description' => $description
            );
            
            $result = $database->add_lesson_favorite($favorite_data);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Lesson favorite added successfully',
                'favorite_id' => $result,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('add_favorite_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Update lesson favorite
     */
    public function rest_update_lesson_favorite($request) {
        try {
            $database = new JPH_Database();
            $user_id = get_current_user_id();
            $favorite_id = $request->get_param('id');
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
            }
            
            if (!$favorite_id) {
                return new WP_Error('missing_id', 'Favorite ID is required', array('status' => 400));
            }
            
            $favorite_data = array();
            
            if ($request->get_param('title')) {
                $favorite_data['title'] = sanitize_text_field($request->get_param('title'));
            }
            if ($request->get_param('url')) {
                $favorite_data['url'] = esc_url_raw($request->get_param('url'));
            }
            if ($request->get_param('category')) {
                $favorite_data['category'] = sanitize_text_field($request->get_param('category'));
            }
            if ($request->get_param('description')) {
                $favorite_data['description'] = sanitize_textarea_field($request->get_param('description'));
            }
            
            if (empty($favorite_data)) {
                return new WP_Error('no_data', 'No data provided to update', array('status' => 400));
            }
            
            $result = $database->update_lesson_favorite($favorite_id, $favorite_data);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Lesson favorite updated successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('update_favorite_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Delete lesson favorite
     */
    public function rest_delete_lesson_favorite($request) {
        try {
            $database = new JPH_Database();
            $user_id = get_current_user_id();
            $favorite_id = $request->get_param('id');
            
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
            }
            
            if (!$favorite_id) {
                return new WP_Error('missing_id', 'Favorite ID is required', array('status' => 400));
            }
            
            $result = $database->delete_lesson_favorite($favorite_id);
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Lesson favorite deleted successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('delete_favorite_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Save lesson favorite from Oxygen Builder page
     */
    public function rest_save_lesson_favorite_from_page($request) {
        try {
            $user_id = get_current_user_id();

            if (!$user_id) {
                return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
            }

            $title = sanitize_text_field($request['title']);
            $url = esc_url_raw($request['url']);
            $category = sanitize_text_field($request['category']);
            $description = sanitize_textarea_field($request['description']);

            // Validate URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return new WP_Error('invalid_url', 'Invalid URL format', array('status' => 400));
            }

            $database = new JPH_Database();
            $favorite_id = $database->add_lesson_favorite(array(
                'user_id' => $user_id,
                'title' => $title,
                'url' => $url,
                'category' => $category,
                'description' => $description
            ));

            if (is_wp_error($favorite_id)) {
                return $favorite_id;
            }

            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Lesson favorite saved successfully',
                'favorite_id' => $favorite_id,
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('save_favorite_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Check if lesson favorite exists
     */
    public function rest_check_lesson_favorite($request) {
        try {
            $user_id = get_current_user_id();
            if (!$user_id) {
                return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
            }
            
            $title = sanitize_text_field($request->get_param('title'));
            $url = esc_url_raw($request->get_param('url'));
            
            $database = new JPH_Database();
            $exists = $database->lesson_favorite_exists($user_id, $title, $url);
            
            return rest_ensure_response(array(
                'success' => true,
                'exists' => $exists,
                'title' => $title,
                'url' => $url
            ));
            
        } catch (Exception $e) {
            return new WP_Error('check_favorite_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Lesson Favorites admin page
     */
    public function lesson_favorites_page() {
        ?>
        <div class="wrap">
            <h1>📚 Lesson Favorites Management</h1>
            
            <div class="jph-admin-stats">
                <div class="jph-stat-card">
                    <h3>Total Favorites</h3>
                    <div class="jph-stat-number" id="total-favorites">Loading...</div>
                </div>
                <div class="jph-stat-card">
                    <h3>Active Users</h3>
                    <div class="jph-stat-number" id="active-users">Loading...</div>
                </div>
                <div class="jph-stat-card">
                    <h3>Most Popular Category</h3>
                    <div class="jph-stat-number" id="popular-category">Loading...</div>
                </div>
            </div>
            
            <div class="jph-admin-actions">
                <button type="button" class="button button-primary" id="refresh-favorites-btn">🔄 Refresh</button>
                <button type="button" class="button button-secondary" id="export-favorites-btn">📊 Export CSV</button>
            </div>
            
            <div class="jph-favorites-container">
                <div class="jph-favorites-filters">
                    <select id="user-filter">
                        <option value="">All Users</option>
                    </select>
                    <select id="category-filter">
                        <option value="">All Categories</option>
                        <option value="lesson">Lesson</option>
                        <option value="technique">Technique</option>
                        <option value="theory">Theory</option>
                        <option value="ear-training">Ear Training</option>
                        <option value="repertoire">Repertoire</option>
                        <option value="improvisation">Improvisation</option>
                        <option value="other">Other</option>
                    </select>
                    <input type="text" id="search-filter" placeholder="Search favorites...">
                </div>
                
                <div class="jph-favorites-table-container">
                    <table class="wp-list-table widefat fixed striped" id="favorites-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>URL</th>
                                <th>Description</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="favorites-tbody">
                            <tr>
                                <td colspan="7" class="loading">Loading lesson favorites...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <style>
        .jph-admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .jph-stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .jph-stat-card h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
            font-weight: 600;
        }
        
        .jph-stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #0073aa;
        }
        
        .jph-admin-actions {
            margin: 20px 0;
        }
        
        .jph-favorites-filters {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            align-items: center;
        }
        
        .jph-favorites-filters select,
        .jph-favorites-filters input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .jph-favorites-table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-favorites-table-container table {
            margin: 0;
        }
        
        .jph-favorites-table-container th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .jph-favorites-table-container td {
            vertical-align: top;
            padding: 12px 8px;
        }
        
        .jph-favorites-table-container .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .jph-favorites-table-container .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0073aa;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
        }
        
        .jph-favorites-table-container .lesson-url {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .jph-favorites-table-container .lesson-description {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .jph-favorites-table-container .actions {
            display: flex;
            gap: 5px;
        }
        
        .jph-favorites-table-container .btn-small {
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        
        .btn-view {
            background: #0073aa;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .loading {
            text-align: center;
            color: #666;
            font-style: italic;
        }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadLessonFavoritesStats();
            loadLessonFavorites();
            loadUsers();
            
            // Event listeners
            document.getElementById('refresh-favorites-btn').addEventListener('click', function() {
                loadLessonFavoritesStats();
                loadLessonFavorites();
            });
            
            document.getElementById('export-favorites-btn').addEventListener('click', exportFavorites);
            
            document.getElementById('user-filter').addEventListener('change', filterFavorites);
            document.getElementById('category-filter').addEventListener('change', filterFavorites);
            document.getElementById('search-filter').addEventListener('input', filterFavorites);
        });
        
        function loadLessonFavoritesStats() {
            // This would be a new endpoint to get stats
            // For now, we'll calculate from the favorites data
            fetch('<?php echo rest_url('jph/v1/lesson-favorites'); ?>', {
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const favorites = data.favorites;
                    const uniqueUsers = new Set(favorites.map(f => f.user_id)).size;
                    const categories = favorites.reduce((acc, f) => {
                        acc[f.category] = (acc[f.category] || 0) + 1;
                        return acc;
                    }, {});
                    const popularCategory = Object.keys(categories).reduce((a, b) => categories[a] > categories[b] ? a : b, 'lesson');
                    
                    document.getElementById('total-favorites').textContent = favorites.length;
                    document.getElementById('active-users').textContent = uniqueUsers;
                    document.getElementById('popular-category').textContent = popularCategory.charAt(0).toUpperCase() + popularCategory.slice(1);
                }
            })
            .catch(error => {
                console.error('Error loading stats:', error);
            });
        }
        
        function loadLessonFavorites() {
            fetch('<?php echo rest_url('jph/v1/lesson-favorites'); ?>', {
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderFavoritesTable(data.favorites);
                } else {
                    document.getElementById('favorites-tbody').innerHTML = '<tr><td colspan="7" class="loading">Error loading favorites</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading favorites:', error);
                document.getElementById('favorites-tbody').innerHTML = '<tr><td colspan="7" class="loading">Error loading favorites</td></tr>';
            });
        }
        
        function loadUsers() {
            fetch('<?php echo rest_url('jph/v1/students'); ?>', {
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const userSelect = document.getElementById('user-filter');
                    userSelect.innerHTML = '<option value="">All Users</option>';
                    data.students.forEach(student => {
                        const option = document.createElement('option');
                        option.value = student.id;
                        option.textContent = student.display_name;
                        userSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading users:', error);
            });
        }
        
        function renderFavoritesTable(favorites) {
            const tbody = document.getElementById('favorites-tbody');
            
            if (favorites.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="loading">No lesson favorites found</td></tr>';
                return;
            }
            
            tbody.innerHTML = favorites.map(favorite => `
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">${favorite.user_display_name ? favorite.user_display_name.charAt(0).toUpperCase() : 'U'}</div>
                            <div>
                                <div>${favorite.user_display_name || 'Unknown User'}</div>
                                <small>ID: ${favorite.user_id}</small>
                            </div>
                        </div>
                    </td>
                    <td><strong>${escapeHtml(favorite.title)}</strong></td>
                    <td><span class="category-badge">${escapeHtml(favorite.category)}</span></td>
                    <td class="lesson-url">
                        <a href="${escapeHtml(favorite.url)}" target="_blank" title="${escapeHtml(favorite.url)}">
                            ${escapeHtml(favorite.url.length > 30 ? favorite.url.substring(0, 30) + '...' : favorite.url)}
                        </a>
                    </td>
                    <td class="lesson-description" title="${escapeHtml(favorite.description || '')}">
                        ${escapeHtml(favorite.description || '')}
                    </td>
                    <td>${new Date(favorite.created_at).toLocaleDateString()}</td>
                    <td class="actions">
                        <a href="${escapeHtml(favorite.url)}" target="_blank" class="btn-small btn-view">View</a>
                        <button onclick="deleteFavorite(${favorite.id})" class="btn-small btn-delete">Delete</button>
                    </td>
                </tr>
            `).join('');
        }
        
        function filterFavorites() {
            const userFilter = document.getElementById('user-filter').value;
            const categoryFilter = document.getElementById('category-filter').value;
            const searchFilter = document.getElementById('search-filter').value.toLowerCase();
            
            // This would ideally be done server-side, but for now we'll do client-side filtering
            loadLessonFavorites();
        }
        
        function deleteFavorite(favoriteId) {
            if (!confirm('Are you sure you want to delete this lesson favorite?')) {
                return;
            }
            
            fetch('<?php echo rest_url('jph/v1/lesson-favorites/'); ?>' + favoriteId, {
                method: 'DELETE',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Lesson favorite deleted successfully!');
                    loadLessonFavorites();
                    loadLessonFavoritesStats();
                } else {
                    alert('Error deleting lesson favorite: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error deleting favorite:', error);
                alert('Error deleting lesson favorite. Please try again.');
            });
        }
        
        function exportFavorites() {
            fetch('<?php echo rest_url('jph/v1/lesson-favorites'); ?>', {
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const csv = convertToCSV(data.favorites);
                    downloadCSV(csv, 'lesson-favorites.csv');
                } else {
                    alert('Error exporting favorites');
                }
            })
            .catch(error => {
                console.error('Error exporting favorites:', error);
                alert('Error exporting favorites. Please try again.');
            });
        }
        
        function convertToCSV(favorites) {
            const headers = ['User ID', 'User Name', 'Title', 'Category', 'URL', 'Description', 'Date Added'];
            const rows = favorites.map(f => [
                f.user_id,
                f.user_display_name || 'Unknown',
                f.title,
                f.category,
                f.url,
                f.description || '',
                f.created_at
            ]);
            
            return [headers, ...rows].map(row => 
                row.map(field => `"${String(field).replace(/"/g, '""')}"`).join(',')
            ).join('\n');
        }
        
        function downloadCSV(csv, filename) {
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            window.URL.revokeObjectURL(url);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        </script>
        <?php
    }
    
    /**
     * Register shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('jph_dashboard', array($this, 'shortcode_student_dashboard'));
    }
    
    /**
     * Student Dashboard Shortcode
     */
    public function shortcode_student_dashboard($atts) {
        // Only show to logged-in users
        if (!is_user_logged_in()) {
            return '<div class="jph-login-required">Please log in to access your practice dashboard.</div>';
        }
        
        $user_id = get_current_user_id();
        $database = new JPH_Database();
        
        // Get user's practice items
        $practice_items = $database->get_user_practice_items($user_id);
        
        // Get user's lesson favorites for matching URLs
        $lesson_favorites = $database->get_lesson_favorites($user_id);
        
        // Create a lookup array for practice item names to lesson URLs
        $lesson_urls = array();
        foreach ($lesson_favorites as $favorite) {
            if (!empty($favorite['url'])) {
                $lesson_urls[$favorite['title']] = $favorite['url'];
            }
        }
        
        // Get user stats using gamification system
        $gamification = new JPH_Gamification();
        $user_stats = $gamification->get_user_stats($user_id);
        
        // Enqueue scripts and styles
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        
        
        ob_start();
        ?>
        <div class="jph-student-dashboard">
            
            <!-- Success/Error Messages -->
            <div id="jph-messages" class="jph-messages" style="display: none;">
                <div class="jph-message-content">
                    <span class="jph-message-close"><i class="fa-solid fa-circle-xmark"></i></span>
                    <div class="jph-message-text"></div>
                </div>
            </div>
            
            <div class="jph-header">
                <div class="header-top">
                    <h2>🎹 Your Practice Dashboard</h2>
                    <!-- Stats Explanation Button - Top Right -->
                    <button id="jph-stats-explanation-btn" type="button" class="jph-btn jph-btn-secondary jph-stats-help-btn">
                        <span class="btn-icon">📊</span>
                        How do these stats work?
                    </button>
                </div>
                <div class="jph-stats">
                    <div class="stat">
                        <span class="stat-value">⭐<?php echo esc_html($user_stats['current_level']); ?></span>
                        <span class="stat-label">Level</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">⚡<?php echo esc_html($user_stats['total_xp']); ?></span>
                        <span class="stat-label">XP</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">🔥<?php echo esc_html($user_stats['current_streak']); ?></span>
                        <span class="stat-label">Streak</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">💎 <?php echo esc_html($user_stats['gems_balance']); ?></span>
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
            
            <!-- Shield Protection Section - Moved outside hero section -->
            <div class="jph-shield-protection">
                <div class="shield-accordion-header">
                    <h3>🛡️ Shield Protection</h3>
                    <span class="shield-toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
                </div>
                
                <div class="shield-accordion-content" id="shield-accordion-content" style="display: none;">
                    <!-- Shield Stats and Actions -->
                    <div class="jph-protection-stats">
                        <div class="protection-item">
                            <span class="protection-icon">🛡️</span>
                            <span class="protection-label">Shields:</span>
                            <span class="protection-value" id="shield-count"><?php echo esc_html($user_stats['streak_shield_count'] ?? 0); ?></span>
                        </div>
                        <div class="protection-actions">
                            <?php 
                            $shield_count = $user_stats['streak_shield_count'] ?? 0;
                            $gem_balance = $user_stats['gems_balance'] ?? 0;
                            $shield_cost = 50;
                            $has_enough_gems = $gem_balance >= $shield_cost;
                            ?>
                            
                            <?php if ($shield_count < 3): ?>
                                <?php if ($has_enough_gems): ?>
                                <button type="button" class="button button-secondary" id="purchase-shield-btn" 
                                        data-cost="50" data-nonce="<?php echo wp_create_nonce('jph_purchase_streak_shield'); ?>">
                                    Buy Shield (50 💎)
                                </button>
                                <?php else: ?>
                                <button type="button" class="button button-secondary" id="purchase-shield-btn-insufficient" 
                                        data-cost="50" data-gem-balance="<?php echo $gem_balance; ?>" data-nonce="<?php echo wp_create_nonce('jph_purchase_streak_shield'); ?>">
                                    Buy Shield (50 💎)
                                </button>
                                <?php endif; ?>
                            <?php else: ?>
                            <button type="button" class="button button-secondary" disabled>
                                Max Shields (3)
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Shield Explanation -->
                    <div class="shield-explanation-section">
                        <h4>🛡️ How Shield Protection Works</h4>
                        <p>Shields automatically protect your practice streak when you miss practice days. Think of them as insurance for your streak!</p>
                        
                        <div class="shield-info-grid">
                            <div class="shield-info-item">
                                <h5>⚡ How It Works</h5>
                                <p>Shield protection follows a simple 3-step process:</p>
                                <ul>
                                    <li><strong>Step 1:</strong> You miss a practice day</li>
                                    <li><strong>Step 2:</strong> System checks for available shields</li>
                                    <li><strong>Step 3:</strong> Shield activates automatically</li>
                                </ul>
                            </div>
                            
                            <div class="shield-info-item">
                                <h5>💰 Cost & Limits</h5>
                                <p>Shields have clear pricing and usage limits:</p>
                                <ul>
                                    <li><strong>Cost:</strong> 50 💎 gems per shield</li>
                                    <li><strong>Limit:</strong> Maximum 3 shields at once</li>
                                    <li><strong>Activation:</strong> Completely automatic</li>
                                </ul>
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
                    <input type="hidden" id="repair-nonce" value="<?php echo wp_create_nonce('jph_repair_streak'); ?>">
                </div>
                <?php endif; ?>
            </div>
            
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
                            $database = new JPH_Database();
                            $last_practice = $database->get_last_practice_session($user_id, $item['id']);
                            $last_practice_date = $last_practice ? $last_practice['created_at'] : null;
                            
                            // Format the date for display
                            $practice_date_display = '';
                            if ($last_practice_date) {
                                // Proper timezone handling - use current UTC timestamp instead of WordPress local time
                                $db_timestamp = strtotime($last_practice_date . ' UTC'); // Ensure DB time is treated as UTC
                                $current_utc_timestamp = current_time('timestamp', true); // Get current UTC timestamp
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
                        <div class="jph-item sortable-practice-item" data-item-id="<?php echo esc_attr($item['id']); ?>" draggable="true">
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
                            <div class="item-last-practiced">
                                Last practiced: <?php echo esc_html($practice_date_display); ?>
                            </div>
                            
                            <!-- Description -->
                            <div class="item-description">
                                <p><?php echo esc_html($item['description']); ?></p>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="item-actions">
                                <button class="jph-log-practice-btn" data-item-id="<?php echo esc_attr($item['id']); ?>">
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
                                    <button class="jph-edit-item-btn icon-btn" data-item-id="<?php echo esc_attr($item['id']); ?>" data-name="<?php echo esc_attr($item['name']); ?>" data-category="<?php echo esc_attr($item['category']); ?>" data-description="<?php echo esc_attr($item['description']); ?>" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor">
                                            <path d="M100.4 417.2C104.5 402.6 112.2 389.3 123 378.5L304.2 197.3L338.1 163.4C354.7 180 389.4 214.7 442.1 267.4L476 301.3L442.1 335.2L260.9 516.4C250.2 527.1 236.8 534.9 222.2 539L94.4 574.6C86.1 576.9 77.1 574.6 71 568.4C64.9 562.2 62.6 553.3 64.9 545L100.4 417.2zM156 413.5C151.6 418.2 148.4 423.9 146.7 430.1L122.6 517L209.5 492.9C215.9 491.1 221.7 487.8 226.5 483.2L155.9 413.5zM510 267.4C493.4 250.8 458.7 216.1 406 163.4L372 129.5C398.5 103 413.4 88.1 416.9 84.6C430.4 71 448.8 63.4 468 63.4C487.2 63.4 505.6 71 519.1 84.6L554.8 120.3C568.4 133.9 576 152.3 576 171.4C576 190.5 568.4 209 554.8 222.5C551.3 226 536.4 240.9 509.9 267.4z"/>
                                        </svg>
                                    </button>
                                    <button class="jph-delete-item-btn icon-btn" data-item-id="<?php echo esc_attr($item['id']); ?>" data-name="<?php echo esc_attr($item['name']); ?>" title="Delete">
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
            
            <!-- Badges Section -->
            <div class="jph-badges-section">
                <div style="margin-bottom: 10px;">
                    <h3 style="margin: 0;">🏆 Your Badges 
                        <span class="badge-count" id="badge-count-display">(<?php echo esc_html($user_stats['badges_earned']); ?>)</span>
                </h3>
                </div>
                <div class="jph-badges-grid" id="jph-badges-grid">
                    <div class="loading-message">Loading badges...</div>
                </div>
                
            </div>
            
            <!-- Full Width Practice History -->
            <div class="jph-practice-history-full">
                <div class="practice-history-header-section">
                    <h3>📊 Your Practice History</h3>
                    <div class="practice-history-controls">
                        <button id="export-history-btn" class="jph-btn jph-btn-secondary">
                            <span class="btn-icon">📥</span>
                            Export CSV
                        </button>
                        <button id="view-all-sessions-btn" class="jph-btn jph-btn-secondary">
                            <span class="btn-icon">👁️</span>
                            View All
                        </button>
                        <button id="load-more-sessions" class="jph-btn jph-btn-secondary" style="display: none;">
                            <span class="btn-icon">📈</span>
                            Load More Sessions
                        </button>
                    </div>
                </div>
                <div class="practice-history-header">
                    <div class="practice-history-header-item">Practice Item</div>
                    <div class="practice-history-header-item">Duration</div>
                    <div class="practice-history-header-item">How it felt</div>
                    <div class="practice-history-header-item">Improvement</div>
                    <div class="practice-history-header-item">Date</div>
                    <div class="practice-history-header-item">Actions</div>
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
                            <label>📈 Did you notice improvement?</label>
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
        </div>
        
        <!-- Edit Practice Item Modal -->
        <div id="jph-edit-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <span class="jph-close"><i class="fa-solid fa-circle-xmark"></i></span>
                <h3>Edit Practice Item</h3>
                <form id="jph-edit-form">
                    <input type="hidden" id="edit-item-id" name="item_id">
                    <div class="form-group">
                        <label>Title:</label>
                        <input type="text" name="item_name" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="item_description" placeholder="Describe what you'll practice (optional)" maxlength="200"></textarea>
                    </div>
                    <button type="submit">Update Practice Item</button>
                </form>
            </div>
        </div>
        
        <!-- Add Practice Item Modal -->
        <div id="jph-add-item-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <span class="jph-close"><i class="fa-solid fa-circle-xmark"></i></span>
                <h3>➕ Add Practice Item</h3>
                <form id="jph-add-item-form">
                    <div class="form-group">
                        <label>Choose Practice Type:</label>
                        <div class="practice-type-cards">
                            <div class="practice-type-card" data-type="custom">
                                <div class="card-icon">✏️</div>
                                <div class="card-content">
                                    <h4>Create Custom</h4>
                                    <p>Enter your own practice item</p>
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
                    <button type="submit">Add Practice Item</button>
                </form>
            </div>
        </div>
        
        <!-- Stats Explanation Modal -->
        <div id="jph-stats-explanation-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <span class="jph-close"><i class="fa-solid fa-circle-xmark"></i></span>
                <h3>📊 How Your Stats Work</h3>
                <div class="explanation-grid">
                    <div class="explanation-item">
                        <h4>🎯 Level</h4>
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
                        <h4>🔥 Streak</h4>
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
        
        <style>
        .jph-student-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fffe 0%, #f0f8f7 100%);
            min-height: 100vh;
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
        
        
        .header-top h2 {
            margin: 0;
            flex: 1;
            font-size: 1.8em;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .jph-stats-help-btn {
            background: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            backdrop-filter: blur(10px);
            padding: 10px 16px !important;
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
        
        .jph-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .jph-header h1 {
            margin: 0;
            font-size: 2.8em;
            font-weight: 800;
            text-shadow: 0 3px 6px rgba(0,0,0,0.4);
            position: relative;
            z-index: 1;
        }
        
        .jph-header p {
            margin: 15px 0 0 0;
            font-size: 1.3em;
            opacity: 0.95;
            position: relative;
            z-index: 1;
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
            display: block;
            font-size: 2.5em;
            font-weight: 800;
            color: #004555;
            margin-bottom: 8px;
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
        }
        
        /* Modal Content Styles */
        .explanation-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin: 20px 0;
        }
        
        @media (max-width: 768px) {
            .explanation-grid {
                grid-template-columns: 1fr;
            }
            
            .jph-items-grid {
                grid-template-columns: 1fr;
            }
            
            .practice-history-header {
                grid-template-columns: 1fr;
                gap: 10px;
                text-align: center;
            }
            
            .practice-history-item {
                grid-template-columns: 1fr;
                gap: 10px;
                text-align: center;
            }
            
            .practice-history-item > * {
                justify-self: center;
            }
            
            .practice-notes {
                grid-column: 1;
                text-align: left;
            }
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
        
        
        /* Make stats explanation modal wider */
        #jph-stats-explanation-modal .jph-modal-content {
            max-width: 900px;
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
        
        /* Compact Sentiment Options */
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
            font-size: 13px;
            color: #495057;
            cursor: pointer;
        }
        
        .toggle-slider input[type="checkbox"] {
            margin-right: 6px;
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
        
        .jph-item {
            background: white;
            border: 2px solid #e8f5f4;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 69, 85, 0.08);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 180px;
        }
        
        
        .jph-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 69, 85, 0.15);
            border-color: #239B90;
        }
        
        /* Drag Handle Styling */
        .drag-handle {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: grab;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
        
        .drag-handle:hover {
            background: rgba(255, 255, 255, 1);
            cursor: grab;
            transform: scale(1.1);
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }
        
        .drag-handle.disabled {
            opacity: 0.3;
            cursor: default;
        }
        
        .drag-handle.disabled:hover {
            transform: none;
            background: rgba(255, 255, 255, 0.9);
        }
        
        /* Drag and Drop States */
        .sortable-practice-item[draggable="true"] {
            cursor: move;
        }
        
        .sortable-practice-item[draggable="true"]:hover {
            cursor: move;
        }
        
        .jph-item.drag-over {
            border: 3px dashed #239B90 !important;
            background: rgba(35, 155, 144, 0.05) !important;
            transform: none !important;
            box-shadow: 0 4px 15px rgba(35, 155, 144, 0.2) !important;
        }
        
        .jph-item.drag-over::after {
            content: 'Drop here';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #239B90;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            z-index: 100;
            pointer-events: none;
        }
        
        /* Empty slot drag styling */
        .sortable-empty-slot {
            opacity: 0.5;
            cursor: no-drop;
        }
        
        /* New Card Header Design */
        .item-card-header {
            background: #00323D;
            color: white;
            padding: 18px 25px;
            margin: -25px -25px 20px -25px;
            border-radius: 16px 16px 0 0;
            border-bottom: 3px solid rgba(255, 255, 255, 0.1);
            height: 65px;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        
        .item-card-header h4 {
            margin: 0;
            font-size: 1em;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            line-height: 1.2;
        }
        
        /* Last Practiced Section */
        .item-last-practiced {
            font-size: 14px;
            color: #004555;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e8f5f4;
        }
        
        /* Description Section */
        .item-description {
            margin-bottom: 20px;
            flex: 1;
        }
        
        .item-description p {
            margin: 0;
            color: #666;
            font-size: 0.95em;
            line-height: 1.5;
        }
        
        
        
        .item-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: auto;
            justify-content: space-between;
        }
        
        .jph-log-practice-btn {
            margin-right: auto;
        }
        
        .item-controls {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .jph-log-practice-btn {
            background: #f04e23;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .jph-log-practice-btn:hover {
            background: #e03e1a;
        }
        
        
        .item-count {
            color: #666;
            font-size: 14px;
            font-weight: normal;
        }
        
        
        .jph-practice-items {
            margin-bottom: 40px;
            margin-top: 35px;
        }
        
        .jph-practice-items h3 {
            margin: 0 0 25px 0;
            padding: 25px 0 15px 0;
            font-size: 28px;
            font-weight: 700;
            color: #2A3940;
            border-bottom: 3px solid #239B90;
            text-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .jph-practice-items .item-count {
            background: linear-gradient(135deg, #239B90, #004555);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(35, 155, 144, 0.3);
            text-shadow: none;
        }
        
        /* Lesson link icon styling */
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .lesson-link-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 4px;
            border-radius: 4px;
            text-decoration: none;
            opacity: 0.7;
            transition: all 0.2s ease;
        }
        
        .lesson-link-icon:hover {
            opacity: 1;
            background: rgba(0, 0, 0, 0.05);
        }
        
        .lesson-link-icon svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }
        
        /* Icon buttons styling - SPECIFIC OVERRIDES */
        .jph-item .icon-btn,
        .jph-item .lesson-link-icon {
            background: none !important;
            border: none !important;
            padding: 4px !important;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s ease;
            width: 24px !important;
            height: 24px !important;
            border-radius: 4px;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            font-weight: normal !important;
            box-shadow: none !important;
            transform: none !important;
        }
        
        .jph-item .icon-btn:hover,
        .jph-item .lesson-link-icon:hover {
            opacity: 1 !important;
            background: rgba(0, 0, 0, 0.05) !important;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .jph-item .jph-edit-item-btn:hover {
            background: rgba(33, 150, 243, 0.1) !important;
        }
        
        .jph-item .jph-delete-item-btn:hover {
            background: rgba(220, 53, 69, 0.1) !important;
        }
        
        .jph-item .icon-btn svg,
        .jph-item .lesson-link-icon svg {
            width: 16px !important;
            height: 16px !important;
            fill: currentColor !important;
        }
        
        /* Lesson Favorites Section */
        .jph-lesson-favorites-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .jph-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .jph-section-header h3 {
            margin: 0;
            color: #2A3940;
            font-size: 20px;
            font-weight: 700;
        }
        
        .jph-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .jph-btn-primary {
            background: #0073aa;
            color: white;
        }
        
        .jph-btn-primary:hover {
            background: #005a87;
            transform: translateY(-1px);
        }
        
        .jph-btn-secondary {
            background: #f0f0f1;
            color: #2A3940;
        }
        
        .jph-btn-secondary:hover {
            background: #e0e0e1;
        }
        
        .jph-icon {
            font-size: 16px;
            font-weight: bold;
        }
        
        .jph-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: #666;
        }
        
        .jph-spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #0073aa;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 16px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .jph-favorites-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .jph-favorite-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.2s ease;
        }
        
        .jph-favorite-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #0073aa;
        }
        
        .jph-favorite-title {
            font-size: 16px;
            font-weight: 600;
            color: #2A3940;
            margin-bottom: 8px;
            line-height: 1.4;
        }
        
        .jph-favorite-category {
            display: inline-block;
            background: #0073aa;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 12px;
        }
        
        .jph-favorite-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 16px;
        }
        
        .jph-favorite-actions {
            display: flex;
            gap: 8px;
        }
        
        .jph-favorite-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .jph-favorite-btn-primary {
            background: #0073aa;
            color: white;
        }
        
        .jph-favorite-btn-primary:hover {
            background: #005a87;
        }
        
        .jph-favorite-btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .jph-favorite-btn-danger:hover {
            background: #c82333;
        }
        
        .jph-favorites-empty {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .jph-favorites-empty h4 {
            margin: 0 0 8px 0;
            color: #2A3940;
            font-size: 18px;
        }
        
        .jph-favorites-empty p {
            margin: 0;
            font-size: 14px;
        }
        
        /* Modal Footer */
        .jph-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px;
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
            border-radius: 0 0 8px 8px;
        }
        
        /* Form Styles */
        .jph-form-group {
            margin-bottom: 20px;
        }
        
        .jph-form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2A3940;
        }
        
        .jph-form-group input,
        .jph-form-group select,
        .jph-form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }
        
        .jph-form-group input:focus,
        .jph-form-group select:focus,
        .jph-form-group textarea:focus {
            outline: none;
            border-color: #0073aa;
            box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.1);
        }
        
        .jph-btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .form-help {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e9ecef;
        }
        
        .form-help .jph-btn {
            margin: 0;
        }
        
        .jph-favorites-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
        }
        
        .jph-favorite-item {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 16px;
            transition: all 0.3s ease;
        }
        
        .jph-favorite-item:hover {
            border-color: #ff6b35;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.2);
        }
        
        .jph-favorite-title {
            font-size: 16px;
            font-weight: 700;
            color: #2A3940;
            margin-bottom: 8px;
        }
        
        .jph-favorite-category {
            display: inline-block;
            background: #ff6b35;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .jph-favorite-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        
        .jph-favorite-actions {
            display: flex;
            gap: 8px;
        }
        
        .jph-favorite-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .jph-favorite-btn-primary {
            background: #0073aa;
            color: white;
        }
        
        .jph-favorite-btn-primary:hover {
            background: #005a87;
            color: white;
            text-decoration: none;
        }
        
        .jph-favorite-btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .jph-favorite-btn-danger:hover {
            background: #c82333;
        }
        
        .jph-favorites-empty {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .jph-favorites-empty h4 {
            margin: 0 0 10px 0;
            color: #2A3940;
        }
        
        .jph-favorites-empty p {
            margin: 0 0 20px 0;
        }
        
        .jph-items-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .jph-empty-item {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            opacity: 0.7;
        }
        
        .jph-empty-item .item-info h4 {
            color: #6c757d;
        }
        
        .jph-empty-item .item-info p {
            color: #6c757d;
        }
        
        .jph-add-item-btn {
            background: #0073aa;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .jph-add-item-btn:hover {
            background: #005a87;
            transform: translateY(-2px);
        }
        
        .jph-add-favorite-btn {
            background: #ff6b35;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 10px;
        }
        
        .jph-add-favorite-btn:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        .practice-type-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 12px;
        }
        
        .practice-type-card {
            position: relative;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-height: 120px;
        }
        
        .practice-type-card:hover {
            border-color: #0073aa;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 115, 170, 0.15);
        }
        
        .practice-type-card.selected {
            border-color: #0073aa;
            background: #f0f8ff;
            box-shadow: 0 4px 15px rgba(0, 115, 170, 0.2);
        }
        
        .card-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
        
        .card-content h4 {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
            color: #2A3940;
        }
        
        .card-content p {
            margin: 0;
            font-size: 14px;
            color: #666;
            line-height: 1.4;
        }
        
        .card-radio {
            position: absolute;
            top: 12px;
            right: 12px;
        }
        
        .card-radio input[type="radio"] {
            margin: 0;
            width: 18px;
            height: 18px;
        }
        
        .card-radio input[type="radio"]:checked {
            accent-color: #0073aa;
        }
        
        /* Full Width Practice History */
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
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 20px;
            margin-top: 20px;
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
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            border: 3px solid #e8f5f4;
            transition: all 0.3s ease;
        }
        
        .jph-badge-card.earned .jph-badge-image {
            border-color: #ffd700;
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        }
        
        .jph-badge-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
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
        
        .jph-badge-rarity {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jph-badge-rarity.common {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .jph-badge-rarity.rare {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .jph-badge-rarity.epic {
            background: #f8d7da;
            color: #721c24;
        }
        
        .jph-badge-rarity.legendary {
            background: #fff3cd;
            color: #856404;
        }
        
        .jph-badge-earned-date {
            font-size: 0.7em;
            color: #28a745;
            font-weight: 600;
            margin-top: 8px;
        }
        
        
        .jph-badge-locked {
            opacity: 0.5;
            filter: grayscale(100%);
        }
        
        .jph-badge-locked .jph-badge-image {
            background: #f0f0f0;
            border-color: #ddd;
        }
        
        .jph-badge-locked .jph-badge-name {
            color: #999;
        }
        
        .jph-badge-locked .jph-badge-description {
            color: #ccc;
        }
        
        .no-badges-message {
            grid-column: 1 / -1;
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
            align-items: center;
        }
        
        .practice-history-controls .jph-btn {
            color: #333 !important;
            text-decoration: none;
        }
        
        .practice-history-controls .jph-btn:hover {
            color: #fff !important;
            text-decoration: none;
        }
        
        .practice-history-controls .jph-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .practice-history-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr;
            gap: 20px;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 2px solid #e8f5f4;
            font-weight: 600;
            color: #004555;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .practice-history-header-item {
            text-align: center;
        }
        
        /* Practice Item header - left aligned */
        .practice-history-header-item:nth-child(1) {
            text-align: left;
        }
        
        /* Duration header - center aligned */
        .practice-history-header-item:nth-child(2) {
            text-align: center;
        }
        
        /* How it felt header - center aligned */
        .practice-history-header-item:nth-child(3) {
            text-align: center;
        }
        
        /* Improvement header - center aligned */
        .practice-history-header-item:nth-child(4) {
            text-align: center;
        }
        
        /* Date header - center aligned */
        .practice-history-header-item:nth-child(5) {
            text-align: center;
        }
        
        /* Actions header - center aligned */
        .practice-history-header-item:nth-child(6) {
            text-align: center;
        }
        
        .practice-history-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .practice-history-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr;
            gap: 20px;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e8f5f4;
            transition: background-color 0.3s ease;
        }
        
        /* Explicit column alignment for data rows */
        .practice-history-item > *:nth-child(1) {
            justify-self: start; /* Practice Item - left aligned */
        }
        
        .practice-history-item > *:nth-child(2) {
            justify-self: center; /* Duration - center aligned */
        }
        
        .practice-history-item > *:nth-child(3) {
            justify-self: center; /* How it felt - center aligned */
        }
        
        .practice-history-item > *:nth-child(4) {
            justify-self: center; /* Improvement - center aligned */
        }
        
        .practice-history-item > *:nth-child(5) {
            justify-self: center; /* Date - center aligned */
        }
        
        .practice-history-item > *:nth-child(6) {
            justify-self: center; /* Actions - center aligned */
        }
        
        .practice-history-item:hover {
            background-color: #f8f9fa;
        }
        
        .practice-history-item:last-child {
            border-bottom: none;
        }
        
        .practice-item-name {
            font-weight: 600;
            color: #004555;
            text-decoration: none;
            transition: color 0.3s ease;
            text-align: left;
        }
        
        .practice-item-name:hover {
            color: #007cba !important;
            text-decoration: underline;
        }
        
        .practice-item-name:visited {
            color: #004555;
        }
        
        .practice-item-name:visited:hover {
            color: #007cba !important;
        }
        
        .practice-duration {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #239B90;
            font-weight: 500;
            text-align: center;
        }
        
        .practice-sentiment {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-align: center;
        }
        
        .practice-improvement {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-align: center;
        }
        
        .practice-date {
            color: #6c757d;
            text-align: center;
        }
        
        .practice-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .practice-actions a,
        .practice-actions button {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.3s ease;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
        }
        
        .practice-actions a:hover,
        .practice-actions button:hover {
            color: #dc3545 !important;
            background: #f8f9fa;
        }
        
        .practice-actions a:focus,
        .practice-actions button:focus {
            outline: 2px solid #007cba;
            outline-offset: 2px;
        }
            font-size: 0.9em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
        }
        
        .practice-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .jph-delete-session-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 32px;
        }
        
        .jph-delete-session-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .practice-notes {
            grid-column: 1 / -1;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-top: 12px;
            font-style: italic;
            color: #6c757d;
            border-left: 4px solid #239B90;
            line-height: 1.4;
        }
        
        /* Two Column Layout */
        .jph-two-column-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 30px 0;
            align-items: stretch;
        }
        
        .jph-left-column,
        .jph-right-column {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .jph-add-item {
            padding: 30px;
            background: white;
            border-radius: 16px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 8px 25px rgba(0, 69, 85, 0.1);
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        
        .jph-add-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #F04E23, #239B90);
        }
        
        .jph-add-item h3 {
            margin: 0 0 25px 0;
            color: #004555;
            font-size: 1.5em;
            font-weight: 700;
        }
        
        .jph-add-item form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 25px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #004555;
            font-size: 1em;
        }
        
        .jph-add-item input,
        .jph-add-item textarea,
        .jph-add-item select {
            padding: 15px 20px;
            border: 2px solid #e8f5f4;
            border-radius: 12px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #f8fffe;
            font-family: inherit;
        }
        
        .jph-add-item input:focus,
        .jph-add-item textarea:focus,
        .jph-add-item select:focus {
            outline: none;
            border-color: #239B90;
            box-shadow: 0 0 0 3px rgba(35, 155, 144, 0.1);
            background: white;
        }
        
        .jph-add-item button {
            background: linear-gradient(135deg, #F04E23, #e0451f);
            color: white;
            border: none;
            padding: 18px 30px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(240, 78, 35, 0.3);
            margin-top: 10px;
        }
        
        .jph-add-item button:hover {
            background: linear-gradient(135deg, #e0451f, #d63e1c);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(240, 78, 35, 0.4);
        }
        
        .jph-modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            padding: 80px 20px 150px 20px;
            box-sizing: border-box;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }
        
        .jph-modal-content {
            background-color: white;
            margin: 0 auto;
            padding: 40px;
            border-radius: 16px;
            width: 100%;
            max-width: 700px;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .jph-close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            transition: color 0.2s ease;
        }
        
        .jph-close:hover {
            color: #333;
        }
        
        .jph-close i {
            font-size: inherit;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            background: #ffffff;
            color: #495057;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.5;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #239B90;
            box-shadow: 0 0 0 3px rgba(35, 155, 144, 0.1), 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 28px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 12px;
            display: block;
            font-size: 16px;
        }
        
        /* Modal form button styles */
        #jph-add-item-form button {
            background: linear-gradient(135deg, #F04E23, #e0451f);
            color: white;
            border: none;
            padding: 18px 30px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(240, 78, 35, 0.3);
            margin-top: 10px;
            width: 100%;
        }
        
        #jph-add-item-form button:hover {
            background: linear-gradient(135deg, #e0451f, #d63e1c);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(240, 78, 35, 0.4);
        }
        
        #jph-add-item-form button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* Edit form button styles */
        #jph-edit-form button {
            background: linear-gradient(135deg, #F04E23, #e0451f);
            color: white;
            border: none;
            padding: 18px 30px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(240, 78, 35, 0.3);
            margin-top: 10px;
            width: 100%;
        }
        
        #jph-edit-form button:hover {
            background: linear-gradient(135deg, #e0451f, #d63e1c);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(240, 78, 35, 0.4);
        }
        
        #jph-edit-form button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        #jph-log-form button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        
        /* Duration Options */
        .duration-options {
            margin-top: 10px;
        }
        
        .duration-quick-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        /* Duration button styles moved to compact modal styles above */
        
        /* Duration button hover/active styles removed - using compact modal styles above */
        
        .duration-custom input {
            width: 100%;
            padding: 14px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            background: #ffffff;
            color: #495057;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .duration-custom input:focus {
            outline: none;
            border-color: #239B90;
            box-shadow: 0 0 0 3px rgba(35, 155, 144, 0.1), 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* Sentiment Options */
        .sentiment-options {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            gap: 12px;
        }
        
        .sentiment-option {
            flex: 1;
            text-align: center;
            padding: 20px 16px;
            border: 2px solid #e9ecef;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .sentiment-option:hover {
            border-color: #239B90;
            background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }
        
        .sentiment-option.active {
            border-color: #239B90;
            scope: rgba(35, 155, 144, 0.1), rgba(30, 130, 121, 0.05);
            box-shadow: 0 8px 24px rgba(35, 155, 144, 0.25);
            transform: translateY(-3px);
            background: linear-gradient(135deg, rgba(35, 155, 144, 0.08) 0%, rgba(30, 130, 121, 0.05) 100%);
        }
        
        .sentiment-emoji {
            font-size: 28px;
            margin-bottom: 8px;
            filter: grayscale(0.2);
            transition: filter 0.3s ease;
        }
        
        .sentiment-option:hover .sentiment-emoji,
        .sentiment-option.active .sentiment-emoji {
            filter: grayscale(0);
        }
        
        .sentiment-label {
            font-size: 13px;
            color: #6c757d;
            font-weight: 600;
        }
        
        .sentiment-option:hover .sentiment-label,
        .sentiment-option.active .sentiment-label {
            color: #495057;
        }
        
        /* Improvement Toggle */
        .improvement-toggle {
            margin-top: 10px;
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
        
        /* Log Session Button - Modernized */
        .log-session-btn {
            background: linear-gradient(135deg, #239B90 0%, #1e8279 100%);
            color: white;
            border: none;
            padding: 18px 32px;
            border-radius: 15px;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 6px 20px rgba(35, 155, 144, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }
        
        .log-session-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(35, 155, 144, 0.4);
            background: linear-gradient(135deg, #1e8279 0%, #1a7065 100%);
        }
        
        .log-session-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(35, 155, 144, 0.4);
        }
        
        .log-session-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 2px 8px rgba(35, 155, 144, 0.2);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                align-items: center;
                gap: 15px;
                text-align: center;
            }
            
            .header-top h2 {
                font-size: 1.5em;
            }
            
            .jph-stats-help-btn {
                font-size: 12px !important;
                padding: 8px 12px !important;
            }
            
            .jph-header {
                padding: 30px 20px;
            }
            
            .jph-modal {
                padding: 60px 10px 120px 10px;
            }
            
            .jph-modal-content {
                max-width: 100%;
                padding: 20px;
                max-height: calc(100vh - 150px);
            }
            
            .jph-two-column-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .jph-items-list {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .duration-quick-buttons {
                grid-template-columns: repeat(2, 1fr);
                gap: 4px;
            }
            
            .duration-btn {
                padding: 5px 8px;
                font-size: 11px;
                border-radius: 12px;
            }
            
            .sentiment-options {
                gap: 5px;
            }
            
            .sentiment-option {
                padding: 12px 4px;
            }
            
            .sentiment-emoji {
                font-size: 20px;
            }
            
            .sentiment-label {
                font-size: 10px;
            }
            
            .toggle-slider {
                width: 100px;
                height: 36px;
            }
            
            .toggle-slider:before {
                width: 28px;
                height: 28px;
            }
            
            #improvement-toggle:checked + .toggle-slider:before {
                transform: translateX(64px);
            }
        }
        
        .jph-empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        /* Practice History Styles */
        .jph-practice-history {
            background: white;
            border-radius: 16px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 8px 25px rgba(0, 69, 85, 0.1);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        
        .jph-practice-history h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 18px;
            padding: 25px 30px 0 30px;
        }
        
        .practice-history-list {
            flex: 1;
            overflow-y: auto;
            max-height: 400px;
            padding: 0 20px 20px 20px;
        }
        
        .practice-history-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .practice-session {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .practice-session:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        
        .session-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .session-item-name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .session-date {
            color: #666;
            font-size: 14px;
        }
        
        .session-details {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .session-detail {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .session-detail-icon {
            font-size: 16px;
        }
        
        .session-duration {
            color: #2196F3;
        }
        
        .session-sentiment {
            font-size: 18px;
        }
        
        .session-improvement {
            color: #4CAF50;
            font-weight: 500;
        }
        
        .session-notes {
            margin-top: auto;
            padding: 8px 12px;
            background: #f5f5f5;
            border-radius: 6px;
            font-size: 14px;
            color: #555;
            font-style: italic;
            max-height: 40px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .jph-delete-session-btn {
            background: transparent;
            color: #e74c3c;
            border: none;
            padding: 2px 6px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 10px;
            margin-left: 8px;
            transition: all 0.3s ease;
            display: inline-block;
            vertical-align: middle;
        }
        
        .jph-delete-session-btn:hover {
            color: #c0392b;
            transform: translateY(-1px);
        }
        
        .loading-message {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        
        .no-sessions-message {
            text-align: center;
            padding: 30px;
            color: #666;
        }
        
        .no-sessions-message .emoji {
            font-size: 48px;
            margin-bottom: 10px;
            display: block;
        }
        
        /* Mobile Responsive for Practice History */
        @media (max-width: 768px) {
            .session-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .session-details {
                gap: 10px;
            }
            
            .session-detail {
                font-size: 13px;
            }
            
            .session-sentiment {
                font-size: 16px;
            }
        }
        
        .jph-messages {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 999999;
            max-width: 400px;
        }
        
        .jph-message-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 15px;
            position: relative;
            border-left: 4px solid #4CAF50;
        }
        
        .jph-message-content.error {
            border-left-color: #f44336;
        }
        
        /* Responsive adjustments for messages */
        @media (max-width: 768px) {
            .jph-messages {
                top: 60px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }
        
        /* WordPress admin bar adjustments */
        .admin-bar .jph-messages {
            top: 100px;
        }
        
        @media (max-width: 782px) {
            .admin-bar .jph-messages {
                top: 80px;
            }
        }
        
        .jph-message-close {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
            font-size: 18px;
            color: #666;
        }
        
        .jph-message-text {
            padding-right: 20px;
        }
        
        .jph-loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .jph-shield-protection {
            background: white;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
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
            grid-template-columns: 1fr 1fr 1fr;
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
            box-shadow: 0 6px 16px rgba(240, 78, 35, 0.4) !important;
        }
        
        .protection-actions .button-primary {
            background: linear-gradient(135deg, #239B90, #004555) !important;
            color: white !important;
            border: none !important;
        }
        
        .protection-actions .button-primary:hover {
            background: linear-gradient(135deg, #1E827A, #002A34) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(35, 155, 144, 0.4) !important;
        }
        
        .protection-actions .button:disabled {
            background: linear-gradient(135deg, #ddd, #ccc);
            color: #888;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        #purchase-shield-btn-insufficient {
            cursor: pointer;
        }
        
        #purchase-shield-btn-insufficient:hover {
            cursor: pointer;
        }
        
        .jph-streak-recovery {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .jph-streak-recovery h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        
        .jph-streak-recovery p {
            margin: 0 0 15px 0;
            color: #856404;
        }
        
        .recovery-options {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .recovery-options .button {
            flex: 1;
            min-width: 120px;
            background: #f04e23 !important;
            border: none !important;
            color: white !important;
            padding: 10px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600 !important;
            cursor: pointer;
            transition: all 0.3s ease !important;
            box-shadow: 0 3px 8px rgba(240, 78, 35, 0.3) !important;
        }
        
        .recovery-options .button:hover {
            background: #d03a1a !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(240, 78, 35, 0.4) !important;
        }
        </style>
        
        <script>
        // Wait for jQuery to be available
        function jphInit() {
            if (typeof jQuery === 'undefined') {
                setTimeout(jphInit, 100);
                return;
            }
            
            jQuery(document).ready(function($) {
                
                // Initialize Streak Shield & Recovery system
                initStreakProtection();
                
                // Initialize clean neuroscience tips
                initNeuroscienceTips();
                
                // Load practice history
                loadPracticeHistory();
                
                // Load badges
                loadBadges();
                
                // Load lesson favorites
                loadLessonFavorites();
                
                // Initialize drag and drop functionality
                initializeDragAndDrop();
                
                // Initialize Shield Protection Accordion
                initShieldAccordion();
                
                // Load more sessions button events
                jQuery(document).on('click', '#load-more-sessions, #load-more-sessions-bottom', function() {
                    loadMoreSessions();
                });
                
                // Export history button event
                jQuery(document).on('click', '#export-history-btn', function() {
                    exportPracticeHistory();
                });
                
                // Alternative export method using direct URL
                function exportPracticeHistoryDirect() {
                    var exportUrl = '<?php echo rest_url('jph/v1/export-practice-history'); ?>';
                    var nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
                    var fullUrl = exportUrl + '?_wpnonce=' + nonce;
                    
                    // Try to open in new window/tab
                    var newWindow = window.open(fullUrl, '_blank');
                    if (!newWindow) {
                        // Fallback: create download link
                        var link = document.createElement('a');
                        link.href = fullUrl;
                        link.download = 'practice-history-<?php echo date('Y-m-d'); ?>.csv';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                }
                
                // View all sessions button event
                jQuery(document).on('click', '#view-all-sessions-btn', function() {
                    viewAllSessions();
                });
                
                // Check and award badges, then load them
                // checkAndAwardBadges(); // Removed automatic call to prevent 401 errors
                
                
                // Stats Explanation Modal
                jQuery('#jph-stats-explanation-btn').on('click', function() {
                    jQuery('#jph-stats-explanation-modal').show();
                });
                
                // Close modal when clicking the X
                jQuery('#jph-stats-explanation-modal .jph-close').on('click', function() {
                    jQuery('#jph-stats-explanation-modal').hide();
                });
                
                // Close modal when clicking outside
                jQuery(window).on('click', function(event) {
                    if (event.target.id === 'jph-stats-explanation-modal') {
                        jQuery('#jph-stats-explanation-modal').hide();
                    }
                });
                
                
                // Test gamification system
                function testGamification() {
                    console.log('Testing gamification system...');
                    
                    // Test adding XP manually
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            user_id: 1,
                            practice_item_id: 11,
                            duration_minutes: 15,
                            sentiment_score: 4,
                            improvement_detected: true,
                            notes: 'Test session for debugging'
                        }),
                        success: function(response) {
                            console.log('Practice session logged:', response);
                            
                            // Check stats after logging
                            $.ajax({
                                url: '<?php echo rest_url('jph/v1/user-stats'); ?>',
                                method: 'GET',
                                success: function(statsResponse) {
                                    console.log('Updated stats:', statsResponse);
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error getting stats:', error);
                                }
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('Error logging practice:', error);
                        }
                    });
                }
                
                // Add test button to console
                window.testGamification = testGamification;
                
                // Stats explanation is now always visible (no toggle function needed)
                
                // Test direct XP addition
                function testDirectXP() {
                    console.log('Testing direct XP addition...');
                    
                    // Test adding XP directly via AJAX
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            user_id: 1,
                            practice_item_id: 11,
                            duration_minutes: 20,
                            sentiment_score: 5,
                            improvement_detected: true,
                            notes: 'Direct XP test'
                        }),
                        success: function(response) {
                            console.log('Direct XP test result:', response);
                            
                            // Check stats immediately after
                            setTimeout(function() {
                                $.ajax({
                                    url: '<?php echo rest_url('jph/v1/user-stats'); ?>',
                                    method: 'GET',
                                    success: function(statsResponse) {
                                        console.log('Stats after direct XP:', statsResponse);
                                    },
                                    error: function(xhr, status, error) {
                                        console.error('Error getting stats:', error);
                                    }
                                });
                            }, 1000);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error in direct XP test:', error);
                        }
                    });
                }
                
                window.testDirectXP = testDirectXP;
                
                // Message system
                function showMessage(message, type = 'success') {
                    var $messages = jQuery('#jph-messages');
                    var $content = $messages.find('.jph-message-content');
                    var $text = $messages.find('.jph-message-text');
                    
                    $content.removeClass('error');
                    if (type === 'error') {
                        $content.addClass('error');
                    }
                    
                    $text.text(message);
                    $messages.show();
                    
                    // Auto-hide after 5 seconds
                    setTimeout(function() {
                        $messages.hide();
                    }, 5000);
                }
                
                // Close message
                jQuery(document).on('click', '.jph-message-close', function() {
                    jQuery('#jph-messages').hide();
                });
                
                // Global variables for pagination
                var currentSessions = [];
                var sessionsLoaded = 0;
                var sessionsPerLoad = 50;
                var isLoadingMore = false;
                
                // Load practice history
                function loadPracticeHistory() {
                    jQuery.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: { limit: sessionsPerLoad },
                        success: function(response) {
                            if (response.success) {
                                currentSessions = response.sessions;
                                sessionsLoaded = currentSessions.length;
                                displayPracticeHistory(currentSessions);
                                
                                // Show load more button if we have sessions and might have more
                                if (sessionsLoaded >= sessionsPerLoad) {
                                    jQuery('#load-more-container').show();
                                }
                            } else {
                                jQuery('#practice-history-list').html('<div class="no-sessions-message"><span class="emoji">📝</span>No practice sessions found</div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading practice history:', error);
                            jQuery('#practice-history-list').html('<div class="no-sessions-message"><span class="emoji">❌</span>Error loading practice history</div>');
                        }
                    });
                }
                
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
                
                // Load more practice sessions
                function loadMoreSessions() {
                    if (isLoadingMore) return;
                    
                    isLoadingMore = true;
                    jQuery('#load-more-sessions-bottom').html('<span class="btn-icon">⏳</span>Loading...');
                    
                    jQuery.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: { 
                            limit: sessionsPerLoad,
                            offset: sessionsLoaded
                        },
                        success: function(response) {
                            if (response.success && response.sessions.length > 0) {
                                // Append new sessions to existing ones
                                currentSessions = currentSessions.concat(response.sessions);
                                sessionsLoaded += response.sessions.length;
                                
                                // Re-display all sessions
                                displayPracticeHistory(currentSessions);
                                
                                // Hide load more button if we got fewer sessions than requested
                                if (response.sessions.length < sessionsPerLoad) {
                                    $('#load-more-container').hide();
                                }
                            } else {
                                // No more sessions to load
                                $('#load-more-container').hide();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading more sessions:', error);
                            showToast('Error loading more sessions. Please try again.', 'error');
                        },
                        complete: function() {
                            isLoadingMore = false;
                            $('#load-more-sessions-bottom').html('<span class="btn-icon">📈</span>Load More Sessions');
                        }
                    });
                }
                
                // Export practice history as CSV
                function exportPracticeHistory() {
                    var $btn = jQuery('#export-history-btn');
                    var originalText = $btn.html();
                    
                    // Show loading state
                    $btn.html('<span class="btn-icon">⏳</span>Exporting...');
                    $btn.prop('disabled', true);
                    
                    // Try the direct URL method first
                    try {
                        var exportUrl = '<?php echo rest_url('jph/v1/export-practice-history'); ?>';
                        var nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
                        var fullUrl = exportUrl + '?_wpnonce=' + nonce;
                        
                        // Create a temporary link to trigger download
                        var link = document.createElement('a');
                        link.href = fullUrl;
                        link.download = 'practice-history-<?php echo date('Y-m-d'); ?>.csv';
                        link.style.display = 'none';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        
                        // Reset button after a delay
                        setTimeout(function() {
                            $btn.html(originalText);
                            $btn.prop('disabled', false);
                        }, 2000);
                        
                    } catch (error) {
                        console.error('Export error:', error);
                        showToast('Export failed. Please try again or contact support.', 'error');
                        $btn.html(originalText);
                        $btn.prop('disabled', false);
                    }
                }
                
                // View all sessions at once
                function viewAllSessions() {
                    var $btn = jQuery('#view-all-sessions-btn');
                    var $container = jQuery('.practice-history-list');
                    
                    if ($btn.text().includes('View All')) {
                        // Load all sessions
                        $btn.html('<span class="btn-icon">⏳</span>Loading All Sessions...');
                        
                        jQuery.ajax({
                            url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                            method: 'GET',
                            headers: {
                                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                            },
                            data: { limit: 9999 }, // Get all sessions
                            success: function(response) {
                                if (response.success) {
                                    currentSessions = response.sessions;
                                    sessionsLoaded = currentSessions.length;
                                    displayPracticeHistory(currentSessions);
                                    
                                    // Hide load more button and show collapse option
                                    jQuery('#load-more-container').hide();
                                    $btn.html('<span class="btn-icon">📋</span>Show Recent Only');
                                    
                                    // Remove height limit to show all sessions
                                    $container.css('max-height', 'none');
                                } else {
                                    showToast('Error loading all sessions: ' + (response.message || 'Unknown error'), 'error');
                                    $btn.html('<span class="btn-icon">👁️</span>View All');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error loading all sessions:', error);
                                showToast('Error loading all sessions. Please try again.', 'error');
                                $btn.html('<span class="btn-icon">👁️</span>View All');
                            }
                        });
                    } else {
                        // Collapse back to recent sessions
                        loadPracticeHistory();
                        $btn.html('<span class="btn-icon">👁️</span>View All');
                        $container.css('max-height', '400px');
                    }
                }
                
                // Check and award badges
                function checkAndAwardBadges() {
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/check-badges'); ?>',
                        method: 'POST',
                        success: function(response) {
                            if (response.success) {
                                if (response.count > 0) {
                                    // Store badge award info for debug area instead of console
                                    window.jphDebugBadgeAwards = {
                                        timestamp: new Date().toISOString(),
                                        count: response.count,
                                        badges: response.newly_awarded,
                                        message: response.message
                                    };
                                    // Update debug area with badge award info
                                    jphUpdateBadgeAwardsDebug();
                                    // Show notification if new badges were awarded
                                    showMessage('🎉 ' + response.message, 'success');
                                }
                            }
                            // Always load badges after checking
                            loadBadges();
                            loadLessonFavorites();
                        },
                        error: function(xhr, status, error) {
                            console.error('Error checking badges:', error);
                            // Still load badges even if check fails
                            loadBadges();
                            loadLessonFavorites();
                        }
                    });
                }
                
                // Load lesson favorites for modal dropdown
                function loadLessonFavorites() {
                    jQuery.ajax({
                        url: '<?php echo rest_url('jph/v1/lesson-favorites'); ?>',
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                displayLessonFavorites(response.favorites);
                            } else {
                                console.error('Error loading lesson favorites:', response.message);
                                jQuery('#lesson-favorites-container').html('<div class="jph-favorites-empty"><h4>No lesson favorites found</h4><p>Start adding lessons to your favorites!</p></div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading lesson favorites:', error);
                            jQuery('#lesson-favorites-container').html('<div class="jph-favorites-empty"><h4>Error loading favorites</h4><p>Please try refreshing the page.</p></div>');
                        }
                    });
                }
                
                // Display lesson favorites
                function displayLessonFavorites(favorites) {
                    const container = jQuery('#lesson-favorites-container');
                    
                    if (favorites.length === 0) {
                        container.html(`
                            <div class="jph-favorites-empty">
                                <h4>No lesson favorites yet</h4>
                                <p>Start adding lessons to your favorites to build your personal learning library!</p>
                            </div>
                        `);
                        return;
                    }
                    
                    let html = '';
                    favorites.forEach(function(favorite) {
                        html += `
                            <div class="jph-favorite-item">
                                <div class="jph-favorite-title">${escapeHtml(favorite.title)}</div>
                                <div class="jph-favorite-category">${escapeHtml(favorite.category)}</div>
                                <div class="jph-favorite-description">${escapeHtml(favorite.description || '')}</div>
                                <div class="jph-favorite-actions">
                                    ${favorite.url && favorite.url.trim() !== '' ? 
                                        '<a href="' + escapeHtml(favorite.url) + '" target="_blank" class="jph-favorite-btn jph-favorite-btn-primary">View Lesson</a>' :
                                        '<span class="jph-favorite-btn jph-favorite-btn-primary" style="opacity: 0.5; cursor: not-allowed;">No URL</span>'
                                    }
                                    <button onclick="deleteLessonFavorite(${favorite.id})" class="jph-favorite-btn jph-favorite-btn-danger">Remove</button>
                                </div>
                            </div>
                        `;
                    });
                    
                    container.html(html);
                }
                
                // Show add favorite modal
                function showAddFavoriteModal() {
                    document.getElementById('add-favorite-modal').style.display = 'flex';
                    document.getElementById('favorite-title').focus();
                }
                
                // Close add favorite modal
                function closeAddFavoriteModal() {
                    document.getElementById('add-favorite-modal').style.display = 'none';
                    document.getElementById('add-favorite-form').reset();
                }
                
                // Submit add favorite form
                function submitAddFavorite() {
                    const form = document.getElementById('add-favorite-form');
                    const formData = new FormData(form);
                    
                    const data = {
                        title: formData.get('title'),
                        url: formData.get('url'),
                        category: formData.get('category'),
                        description: formData.get('description')
                    };
                    
                    if (!data.title || !data.url) {
                        alert('Please fill in the required fields (Title and URL)');
                        return;
                    }
                    
                    jQuery.ajax({
                        url: '<?php echo rest_url('jph/v1/lesson-favorites'); ?>',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: data,
                        success: function(response) {
                            if (response.success) {
                                showMessage('Lesson favorite added successfully!', 'success');
                                closeAddFavoriteModal();
                                loadLessonFavorites(); // Refresh the list
                            } else {
                                showMessage('Error: ' + (response.message || 'Unknown error'), 'error');
                            }
                        },
                        error: function(xhr) {
                            showMessage('Error adding lesson favorite. Please try again.', 'error');
                        }
                    });
                }
                
                // Delete lesson favorite
                function deleteLessonFavorite(favoriteId) {
                    if (!confirm('Are you sure you want to remove this lesson favorite?')) {
                        return;
                    }
                    
                    jQuery.ajax({
                        url: '<?php echo rest_url('jph/v1/lesson-favorites/'); ?>' + favoriteId,
                        method: 'DELETE',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('Lesson favorite removed successfully!', 'success');
                                loadLessonFavorites();
                            } else {
                                showToast('Error removing lesson favorite: ' + (response.message || 'Unknown error'), 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error removing lesson favorite:', error);
                            showToast('Error removing lesson favorite. Please try again.', 'error');
                        }
                    });
                }
                
                // Load badges
                function loadBadges() {
                    console.log('loadBadges() called');
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/badges'); ?>',
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
                                jQuery('#jph-badges-grid').html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges available</div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading badges:', error);
                            console.error('XHR response:', xhr.responseText);
                            jQuery('#jph-badges-grid').html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges earned yet</div>');
                        }
                    });
                }
                
                // Display badges
                function displayBadges(badges) {
                    var $container = jQuery('#jph-badges-grid');
                    
                    if (!badges || badges.length === 0) {
                        $container.html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges available yet. Keep practicing to earn your first badge!</div>');
                        return;
                    }
                    
                    var html = '';
                    var earnedCount = 0;
                    badges.forEach(function(badge, index) {
                        var earnedClass = badge.is_earned ? 'earned' : 'locked';
                        if (badge.is_earned) earnedCount++;
                        
                        var badgeImage = badge.image_url ? 
                            '<img src="' + badge.image_url + '" alt="' + badge.name + '">' : 
                            '<span class="badge-emoji">🏆</span>';
                        
                        var earnedDate = badge.is_earned && badge.earned_at ? 
                            '<div class="jph-badge-earned-date">Earned: ' + formatDate(badge.earned_at) + '</div>' : '';
                        
                        html += '<div class="jph-badge-card ' + earnedClass + '">';
                        html += '    <div class="jph-badge-image">' + badgeImage + '</div>';
                        html += '    <div class="jph-badge-name">' + escapeHtml(badge.name) + '</div>';
                        html += '    <div class="jph-badge-description">' + escapeHtml(badge.description || '') + '</div>';
                        html += earnedDate;
                        html += '</div>';
                    });
                    
                    $container.html(html);
                    
                    // Update the badge count display
                    updateBadgeCount(earnedCount);
                }
                
        // Update badge count display
        function updateBadgeCount(count) {
            var $badgeCount = jQuery('#badge-count-display');
            if ($badgeCount.length) {
                $badgeCount.text('(' + count + ')');
            }
        }
        
        // Toast notification system
        function showToast(message, type = 'info', duration = 4000) {
            // Remove existing toasts
            jQuery('.jph-toast').remove();
            
            const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
            
            const toast = jQuery(`
                <div class="jph-toast ${type}">
                    <div class="toast-content">
                        <span class="toast-icon">${icon}</span>
                        <span class="toast-message">${message}</span>
                        <button class="toast-close" onclick="jQuery(this).parent().parent().remove()"><i class="fa-solid fa-circle-xmark"></i></button>
                    </div>
                </div>
            `);
            
            jQuery('body').append(toast);
            
            // Show toast
            setTimeout(() => toast.addClass('show'), 100);
            
            // Auto-hide after duration
            setTimeout(() => {
                toast.removeClass('show');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
                
                
                // Get progress text for unearned badges
                
                
                // Display practice history
                function displayPracticeHistory(sessions) {
                    var $container = jQuery('#practice-history-list');
                    
                    if (!sessions || sessions.length === 0) {
                        $container.html('<div class="no-sessions-message"><span class="emoji">📝</span>No practice sessions yet. Start practicing to see your history!</div>');
                        return;
                    }
                    
                    var html = '';
                    sessions.forEach(function(session) {
                        var sentimentEmoji = getSentimentEmoji(session.sentiment_score);
                        var improvementText = session.improvement_detected == '1' ? 'Yes' : 'No';
                        var improvementClass = session.improvement_detected == '1' ? 'session-improvement' : '';
                        var formattedDate = formatDate(session.created_at);
                        
                        html += '<div class="practice-history-item">';
                        html += '<div class="practice-item-name">' + escapeHtml(session.item_name || 'Unknown Item') + '</div>';
                        html += '<div class="practice-duration">';
                        html += '<span class="session-detail-icon">⏱️</span>';
                        html += session.duration_minutes + ' min';
                        html += '</div>';
                        html += '<div class="practice-sentiment">';
                        html += '<span class="session-sentiment">' + sentimentEmoji + '</span>';
                        html += '</div>';
                        html += '<div class="practice-improvement ' + improvementClass + '">';
                        html += '<span class="session-detail-icon">📈</span>';
                        html += improvementText;
                        html += '</div>';
                        html += '<div class="practice-date">' + formattedDate + '</div>';
                        html += '<div class="practice-actions">';
                        html += '<button class="jph-delete-session-btn" data-session-id="' + session.id + '" data-item-name="' + escapeHtml(session.item_name || 'Unknown Item') + '" title="Delete this practice session">🗑️</button>';
                        html += '</div>';
                        html += '</div>';
                        
                        // Add notes if they exist
                        if (session.notes && session.notes.trim() !== '') {
                            html += '<div class="practice-notes">' + escapeHtml(session.notes) + '</div>';
                        }
                    });
                    
                    $container.html(html);
                }
                
                // Update stats display with response data
                function updateStatsDisplay(response) {
                    // Update XP (add the earned XP to current display)
                    var currentXP = parseInt(jQuery('.stat-value').eq(1).text()) || 0;
                    var newXP = currentXP + (response.xp_earned || 0);
                    jQuery('.stat-value').eq(1).text(newXP);
                    
                    // Update level if leveled up
                    if (response.level_up && response.level_up.leveled_up) {
                        jQuery('.stat-value').eq(0).text(response.level_up.new_level);
                    }
                    
                    // Update streak
                    if (response.streak_update) {
                        jQuery('.stat-value').eq(2).text(response.streak_update.current_streak);
                    }
                }
                
                // Refresh stats display
                function refreshStats() {
                    // For now, we'll reload the page to show updated stats
                    // In the future, we could make an AJAX call to get updated stats
                    setTimeout(function() {
                        location.reload();
                    }, 2000); // Wait 2 seconds to show the success message
                }
                
                // Helper functions
                function getSentimentEmoji(score) {
                    var emojis = {
                        '1': '😞',
                        '2': '😕', 
                        '3': '😐',
                        '4': '😊',
                        '5': '🤩'
                    };
                    return emojis[score] || '😐';
                }
                
                function formatDate(dateString) {
                    if (!dateString) return 'Never';
                    
                    // Handle date-only strings (YYYY-MM-DD) by adding time
                    var dateToCheck = dateString;
                    if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
                        dateToCheck = dateString + 'T00:00:00';
                    }
                    
                    var date = new Date(dateToCheck);
                    var now = new Date();
                    
                    // Check if it's today
                    var today = new Date();
                    today.setHours(0, 0, 0, 0);
                    var checkDate = new Date(date);
                    checkDate.setHours(0, 0, 0, 0);
                    
                    if (checkDate.getTime() === today.getTime()) {
                        return 'Today';
                    }
                    
                    // Check if it's yesterday
                    var yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    
                    if (checkDate.getTime() === yesterday.getTime()) {
                        return 'Yesterday';
                    }
                    
                    // Calculate days difference
                    var diffTime = Math.abs(now - date);
                    var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    if (diffDays <= 7) {
                        return diffDays + ' days ago';
                    } else {
                        return date.toLocaleDateString();
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
                
                // Handle Add Practice Item button clicks
                jQuery(document).on('click', '.jph-add-item-btn', function() {
                    jQuery('#jph-add-item-modal').show();
                    loadLessonFavorites();
                    
                    // Initialize card selection state
                    jQuery('.practice-type-card').removeClass('selected');
                    jQuery('.practice-type-card:has(input[type="radio"]:checked)').addClass('selected');
                });
                
                // Handle practice type card clicks
                jQuery(document).on('click', '.practice-type-card', function() {
                    var cardType = jQuery(this).data('type');
                    var radioInput = jQuery(this).find('input[type="radio"]');
                    
                    // Remove selected class from all cards
                    jQuery('.practice-type-card').removeClass('selected');
                    
                    // Add selected class to clicked card
                    jQuery(this).addClass('selected');
                    
                    // Check the radio button
                    radioInput.prop('checked', true);
                    
                    // Trigger the change event to update form visibility
                    radioInput.trigger('change');
                });
                
                // Handle practice type radio button changes
                jQuery(document).on('change', 'input[name="practice_type"]', function() {
                    var practiceType = jQuery(this).val();
                    if (practiceType === 'custom') {
                        jQuery('#custom-title-group').show();
                        jQuery('#favorite-selection-group').hide();
                        jQuery('input[name="item_name"]').prop('required', true);
                        jQuery('select[name="lesson_favorite"]').prop('required', false);
                    } else if (practiceType === 'favorite') {
                        jQuery('#custom-title-group').hide();
                        jQuery('#favorite-selection-group').show();
                        jQuery('input[name="item_name"]').prop('required', false);
                        jQuery('select[name="lesson_favorite"]').prop('required', true);
                    }
                });
                
                // Handle lesson favorite selection
                jQuery(document).on('change', '#lesson-favorite-select', function() {
                    var selectedOption = jQuery(this).find('option:selected');
                    if (selectedOption.val()) {
                        var title = selectedOption.data('title');
                        var description = selectedOption.data('description');
                        
                        // Auto-fill the form fields
                        jQuery('input[name="item_name"]').val(title);
                        jQuery('textarea[name="item_description"]').val(description);
                    }
                });
                
                // Load lesson favorites for practice item modal
                function loadLessonFavorites() {
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/lesson-favorites'); ?>',
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
                
                // Add lesson favorite function (called by button) - Updated to use modal
                function addLessonFavorite() {
                    showAddFavoriteModal();
                }
                
                // Close Add Practice Item modal
                jQuery(document).on('click', '#jph-add-item-modal .jph-close', function() {
                    jQuery('#jph-add-item-modal').hide();
                });
                
                // Close modal when clicking outside
                jQuery(document).on('click', '#jph-add-item-modal', function(e) {
                    if (e.target === this) {
                        jQuery(this).hide();
                    }
                });
                
                // Add practice item
                jQuery('#jph-add-item-form').on('submit', function(e) {
                    e.preventDefault();
                    var $form = jQuery(this);
                    var $button = $form.find('button[type="submit"]');
                    
                    // Disable form during submission
                    $form.addClass('jph-loading');
                    $button.prop('disabled', true).text('Adding...');
                    
                    var formData = {
                        name: $form.find('input[name="item_name"]').val(),
                        category: 'custom',
                        description: $form.find('textarea[name="item_description"]').val()
                    };
                    
                    // Validate form data
                    if (!formData.name) {
                        showMessage('Please enter a practice item name', 'error');
                        $form.removeClass('jph-loading');
                        $button.prop('disabled', false).text('Add Practice Item');
                        return;
                    }
                    
                    console.log('Sending data:', formData);
                    
                    jQuery.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-items'); ?>',
                        method: 'POST',
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                showMessage('Practice item added successfully!');
                                $form[0].reset(); // Clear form
                                jQuery('#jph-add-item-modal').hide(); // Close modal
                                // Refresh the page to show the new item in the grid
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
                            $form.removeClass('jph-loading');
                            $button.prop('disabled', false).text('Add Practice Item');
                        },
                        complete: function() {
                            $form.removeClass('jph-loading');
                            $button.prop('disabled', false).text('Add Practice Item');
                        }
                    });
                });
                
                // Add item to list dynamically
                function addItemToList(itemId, name, category, description) {
                    var $emptyState = $('.jph-empty-state');
                    if ($emptyState.length) {
                        $emptyState.remove();
                    }
                    
                    var itemHtml = '<div class="jph-item sortable-practice-item" data-item-id="' + itemId + '" draggable="true">' +
                        '<div class="drag-handle" title="Drag to reorder">' +
                        '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">' +
                        '<circle cx="4" cy="4" r="1" fill="#666"/>' +
                        '<circle cx="8" cy="4" r="1" fill="#666"/>' +
                        '<circle cx="12" cy="4" r="1" fill="#666"/>' +
                        '<circle cx="4" cy="8" r="1" fill="#666"/>' +
                        '<circle cx="8" cy="8" r="1" fill="#666"/>' +
                        '<circle cx="12" cy="8" r="1" fill="#666"/>' +
                        '<circle cx="4" cy="12" r="1" fill="#666"/>' +
                        '<circle cx="8" cy="12" r="1" fill="#666"/>' +
                        '<circle cx="12" cy="12" r="1" fill="#666"/>' +
                        '</svg></div>' +
                        '<div class="item-card-header">' +
                        '<h4>' + name + '</h4>' +
                        '</div>' +
                        '<div class="item-last-practiced">' +
                        'Last practiced: Never practiced' +
                        '</div>' +
                        '<div class="item-description">' +
                        '<p>' + (description || '') + '</p>' +
                        '</div>' +
                        '<div class="item-actions">' +
                        '<button class="jph-log-practice-btn" data-item-id="' + itemId + '">Log Practice</button>' +
                        '<div class="item-controls">' +
                        '<button class="jph-edit-item-btn icon-btn" data-item-id="' + itemId + '" data-name="' + name + '" data-category="' + category + '" data-description="' + (description || '') + '" title="Edit">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor"><path d="M100.4 417.2C104.5 402.6 112.2 389.3 123 378.5L304.2 197.3L338.1 163.4C354.7 180 389.4 214.7 442.1 267.4L476 301.3L442.1 335.2L260.9 516.4C250.2 527.1 236.8 534.9 222.2 539L94.4 574.6C86.1 576.9 77.1 574.6 71 568.4C64.9 562.2 62.6 553.3 64.9 545L100.4 417.2zM156 413.5C151.6 418.2 148.4 423.9 146.7 430.1L122.6 517L209.5 492.9C215.9 491.1 221.7 487.8 226.5 483.2L155.9 413.5zM510 267.4C493.4 250.8 458.7 216.1 406 163.4L372 129.5C398.5 103 413.4 88.1 416.9 84.6C430.4 71 448.8 63.4 468 63.4C487.2 63.4 505.6 71 519.1 84.6L554.8 120.3C568.4 133.9 576 152.3 576 171.4C576 190.5 568.4 209 554.8 222.5C551.3 226 536.4 240.9 509.9 267.4z"/></svg>' +
                        '</button>' +
                        '<button class="jph-delete-item-btn icon-btn" data-item-id="' + itemId + '" data-name="' + name + '" title="Delete">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor"><path d="M232.7 69.9L224 96L128 96C110.3 96 96 110.3 96 128C96 145.7 110.3 160 128 160L512 160C529.7 160 544 145.7 544 128C544 110.3 529.7 96 512 96L416 96L407.3 69.9C402.9 56.8 390.7 48 376.9 48L263.1 48C249.3 48 237.1 56.8 232.7 69.9zM512 208L128 208L149.1 531.1C150.7 556.4 171.7 576 197 576L443 576C468.3 576 489.3 556.4 490.9 531.1L512 208z"/></svg>' +
                        '</button>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                    
                    jQuery('.jph-items-list').append(itemHtml);
                }
                
                // Open log practice modal
                jQuery(document).on('click', '.jph-log-practice-btn', function() {
                    var itemId = jQuery(this).data('item-id');
                    var itemName = jQuery(this).closest('.jph-item').find('.item-card-header h4').text();
                    
                    jQuery('#log-item-id').val(itemId);
                    jQuery('#log-practice-item-name').text('Logging practice for: ' + itemName);
                    jQuery('#jph-log-modal').show();
                    
                    // Set default sentiment score (good = 4) and duration (15 minutes)
                    jQuery('input[name="sentiment_score"]').val('4');
                    jQuery('input[name="duration_minutes"]').val('15');
                    jQuery('.sentiment-option').removeClass('active');
                    jQuery('.sentiment-option[data-score="4"]').addClass('active');
                    jQuery('.duration-btn').removeClass('active');
                    jQuery('.duration-btn[data-minutes="15"]').addClass('active');
                });
                
                // Close modal
                jQuery(document).on('click', '.jph-close', function() {
                    jQuery('#jph-log-modal').hide();
                    jQuery('#jph-edit-modal').hide();
                    
                    // Reset log form when closing
                    jQuery('#jph-log-form')[0].reset();
                    jQuery('.duration-btn').removeClass('active');
                    jQuery('.sentiment-option').removeClass('active');
                });
                
                // Close modal when clicking outside
                jQuery(document).on('click', '#jph-log-modal', function(e) {
                    if (e.target === this) {
                        jQuery(this).hide();
                        // Reset log form when closing
                        jQuery('#jph-log-form')[0].reset();
                        jQuery('.duration-btn').removeClass('active');
                        jQuery('.sentiment-option').removeClass('active');
                    }
                });
                
                // Close edit modal when clicking outside
                jQuery(document).on('click', '#jph-edit-modal', function(e) {
                    if (e.target === this) {
                        jQuery(this).hide();
                    }
                });
                
                // Close modals when pressing Escape key
                jQuery(document).on('keydown', function(e) {
                    if (e.key === 'Escape') {
                        if (jQuery('#jph-log-modal').is(':visible')) {
                            jQuery('#jph-log-modal').hide();
                            // Reset log form when closing
                            jQuery('#jph-log-form')[0].reset();
                            jQuery('.duration-btn').removeClass('active');
                            jQuery('.sentiment-option').removeClass('active');
                        }
                        if (jQuery('#jph-edit-modal').is(':visible')) {
                            jQuery('#jph-edit-modal').hide();
                        }
                    }
                });
                
                // Edit practice item
                jQuery(document).on('click', '.jph-edit-item-btn', function() {
                    var itemId = jQuery(this).data('item-id');
                    var name = jQuery(this).data('name');
                    var category = jQuery(this).data('category');
                    var description = jQuery(this).data('description');
                    
                    console.log('Edit button clicked - Data:', {itemId, name, category, description});
                    
                    jQuery('#edit-item-id').val(itemId);
                    jQuery('#jph-edit-form input[name="item_name"]').val(name);
                    jQuery('#jph-edit-form select[name="item_category"]').val(category);
                    jQuery('#jph-edit-form textarea[name="item_description"]').val(description);
                    jQuery('#jph-edit-modal').show();
                });
                
                // Delete practice item
                jQuery(document).on('click', '.jph-delete-item-btn', function() {
                    var itemId = jQuery(this).data('item-id');
                    var name = jQuery(this).data('name');
                    
                    if (confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone. Note: Any practice sessions logged for this item will be preserved.')) {
                        jQuery.ajax({
                            url: '<?php echo rest_url('jph/v1/practice-items/'); ?>' + itemId,
                            method: 'DELETE',
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
                
                // Delete practice session
                $(document).on('click', '.jph-delete-session-btn', function() {
                    var sessionId = $(this).data('session-id');
                    var itemName = $(this).data('item-name');
                    
                    if (confirm('Are you sure you want to delete this practice session for "' + itemName + '"? This action cannot be undone.')) {
                        $.ajax({
                            url: '<?php echo rest_url('jph/v1/practice-sessions/'); ?>' + sessionId,
                            method: 'DELETE',
                            headers: {
                                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    showMessage('Practice session deleted successfully!');
                                    $('.practice-history-item').filter(function() {
                                        return $(this).find('.jph-delete-session-btn').data('session-id') == sessionId;
                                    }).fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                } else {
                                    showMessage('Error: ' + (response.message || 'Unknown error'), 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Delete Session Error:', xhr, status, error);
                                var errorMessage = 'Error deleting practice session';
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
                
                // Update practice item
                jQuery('#jph-edit-form').on('submit', function(e) {
                    e.preventDefault();
                    var $form = jQuery(this);
                    var $button = $form.find('button[type="submit"]');
                    var itemId = jQuery('#edit-item-id').val();
                    
                    // Disable form during submission
                    $form.addClass('jph-loading');
                    $button.prop('disabled', true).text('Updating...');
                    
                    var formData = {
                        name: $form.find('input[name="item_name"]').val(),
                        category: 'custom',
                        description: $form.find('textarea[name="item_description"]').val()
                    };
                    
                    console.log('Edit form data:', formData); // Debug logging
                    
                    // Validate form data
                    if (!formData.name) {
                        showMessage('Please enter a practice item name', 'error');
                        $form.removeClass('jph-loading');
                        $button.prop('disabled', false).text('Update Practice Item');
                        return;
                    }
                    
                    jQuery.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-items/'); ?>' + itemId,
                        method: 'PUT',
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                showMessage('Practice item updated successfully!');
                                jQuery('#jph-edit-modal').hide();
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
                            $form.removeClass('jph-loading');
                            $button.prop('disabled', false).text('Update Practice Item');
                        }
                    });
                });
                
                // Helper function to update item in list
                function updateItemInList(itemId, name, category, description) {
                    var $item = $('.jph-item[data-item-id="' + itemId + '"]');
                    $item.find('h4').text(name);
                    $item.find('p').text(description || '');
                    $item.find('.item-category').text(category);
                    
                    // Update data attributes
                    $item.find('.jph-edit-item-btn').attr('data-name', name);
                    $item.find('.jph-edit-item-btn').attr('data-category', category);
                    $item.find('.jph-edit-item-btn').attr('data-description', description);
                    $item.find('.jph-delete-item-btn').attr('data-name', name);
                }
                
                // Helper function to update item count
                function updateItemCount() {
                    var count = jQuery('.jph-item').length;
                    jQuery('.item-count').text('(' + count + '/6)');
                }
                
                // Initialize drag and drop functionality
                function initializeDragAndDrop() {
                    const container = document.getElementById('sortable-practice-items');
                    let draggedElement = null;
                    
                    // Add event listeners to existing items
                    container.querySelectorAll('.sortable-practice-item').forEach(item => {
                        setupDragEvents(item);
                    });
                    
                    // Observer for new items added dynamically
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            mutation.addedNodes.forEach(function(node) {
                                if (node.nodeType === 1 && node.classList.contains('sortable-practice-item')) {
                                    setupDragEvents(node);
                                }
                            });
                        });
                    });
                    
                    observer.observe(container, { childList: true });
                }
                
                // Setup drag events for individual items
                function setupDragEvents(item) {
                    item.addEventListener('dragstart', function(e) {
                        draggedElement = this;
                        this.style.opacity = '0.5';
                        this.style.transform = 'rotate(5deg)';
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/html', this.outerHTML);
                        this.classList.add('dragging');
                    });
                    
                    item.addEventListener('dragend', function(e) {
                        this.style.opacity = '1';
                        this.style.transform = 'none';
                        this.classList.remove('dragging');
                        draggedElement = null;
                        
                        // Remove all drag feedback
                        document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
                    });
                    
                    item.addEventListener('dragover', function(e) {
                        if (draggedElement && draggedElement !== this) {
                            e.preventDefault();
                            e.dataTransfer.dropEffect = 'move';
                            this.classList.add('drag-over');
                        }
                    });
                    
                    item.addEventListener('dragleave', function(e) {
                        // Only remove drag-over if we're actually leaving the element
                        if (!this.contains(e.relatedTarget)) {
                            this.classList.remove('drag-over');
                        }
                    });
                    
                    item.addEventListener('drop', function(e) {
                        e.preventDefault();
                        this.classList.remove('drag-over');
                        
                        if (draggedElement && draggedElement !== this) {
                            // Simple insertion logic: insert before the drop target
                            this.parentNode.insertBefore(draggedElement, this);
                            
                            // Save new order
                            savePracticeItemOrder();
                            
                            // Show success message
                            showMessage('Practice items reordered successfully!', 'success');
                        }
                    });
                }
                
                // Save the new order of practice items
                function savePracticeItemOrder() {
                    const items = [];
                    document.querySelectorAll('#sortable-practice-items .sortable-practice-item').forEach((item, index) => {
                        const itemId = item.getAttribute('data-item-id');
                        if (itemId && itemId !== '') {
                            items.push({
                                id: itemId,
                                sort_order: index
                            });
                        }
                    });
                    
                    if (items.length > 0) {
                        jQuery.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'jph_reorder_practice_items',
                                items: JSON.stringify(items),
                                nonce: '<?php echo wp_create_nonce('jph_reorder_practice_items'); ?>'
                            },
                            success: function(response) {
                                if (!response.success) {
                                    console.error('Failed to save order:', response.data);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error saving order:', error);
                            }
                        });
                    }
                }
                
                // Duration quick buttons
                jQuery(document).on('click', '.duration-btn', function() {
                    jQuery('.duration-btn').removeClass('active');
                    jQuery(this).addClass('active');
                    jQuery('input[name="duration_minutes"]').val(jQuery(this).data('minutes'));
                });
                
                // Sentiment selection
                jQuery(document).on('click', '.sentiment-option', function() {
                    jQuery('.sentiment-option').removeClass('active');
                    jQuery(this).addClass('active');
                    jQuery('input[name="sentiment_score"]').val(jQuery(this).data('score'));
                });
                
                // Log practice session
                jQuery('#jph-log-form').on('submit', function(e) {
                    e.preventDefault();
                    var $form = jQuery(this);
                    var $button = $form.find('button[type="submit"]');
                    
                    // Disable form during submission
                    $form.addClass('jph-loading');
                    $button.prop('disabled', true).text('Logging...');
                    
                    var formData = {
                        practice_item_id: jQuery('#log-item-id').val(),
                        duration_minutes: jQuery('input[name="duration_minutes"]').val(),
                        sentiment_score: jQuery('input[name="sentiment_score"]').val(),
                        improvement_detected: jQuery('input[name="improvement_detected"]').is(':checked'),
                        notes: jQuery('textarea[name="notes"]').val()
                    };
                    
                    console.log('Log form data:', formData);
                    
                    jQuery.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
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
                                        message += ' 🔥 ' + response.streak_update.current_streak + '-day streak!';
                                    } else {
                                        message += ' 🔥 New streak started!';
                                    }
                                }
                                
                                showMessage(message);
                                jQuery('#jph-log-modal').hide();
                                $form[0].reset();
                                // Reset UI elements
                                jQuery('.duration-btn').removeClass('active');
                                jQuery('.sentiment-option').removeClass('active');
                                // Refresh practice history
                                loadPracticeHistory();
                                // Update stats display with new values
                                updateStatsDisplay(response);
                            } else {
                                showMessage('Error: ' + (response.message || 'Unknown error'), 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Session AJAX Error:', xhr, status, error);
                            showMessage('Error logging practice session: ' + error, 'error');
                        },
                        complete: function() {
                            $form.removeClass('jph-loading');
                            // Always show "Log Practice" button text
                            $button.prop('disabled', false).text('Log Practice');
                        }
                    });
                });
            });
        }
        
        // Start initialization
        // Initialize Streak Shield & Recovery system
        function initStreakProtection() {
            // Ensure jQuery is available
            if (typeof jQuery === 'undefined') {
                console.error('JPH: jQuery not available for Streak Protection');
                return;
            }
            
            // Ensure jph_ajax is available
            if (typeof jph_ajax === 'undefined') {
                console.error('JPH: jph_ajax not available for Streak Protection');
                return;
            }
            
            // Purchase Shield button
            jQuery(document).on('click', '#purchase-shield-btn', function() {
                const cost = jQuery(this).data('cost');
                const nonce = jQuery(this).data('nonce');
                
                // Check current shield count from the display
                const shieldCountText = jQuery('#shield-count').text();
                const currentShieldCount = parseInt(shieldCountText) || 0;
                
                // Prevent purchase if already at max shields
                if (currentShieldCount >= 3) {
                    alert('You already have the maximum number of shields (3). You cannot purchase more shields.');
                    return;
                }
                
                if (!confirm(`Purchase Streak Shield for ${cost} gems?`)) {
                    return;
                }
                
                jQuery.ajax({
                    url: jph_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'jph_purchase_streak_shield',
                        nonce: nonce
                    },
                    success: function(response) {
                        
                        if (response.success) {
                            // Update UI
                            jQuery('#shield-count').text(response.data.new_shield_count);
                            jQuery('.stat-value:contains("💎")').text('💎 ' + response.data.new_gem_balance);
                            
                            // Show success message
                            showMessage('success', response.data.message);
                            
                            // Update button state
                            if (response.data.new_shield_count >= 3) {
                                jQuery('#purchase-shield-btn').prop('disabled', true).text('Max Shields (3)');
                            }
                        } else {
                            showMessage('error', response.data || 'Failed to purchase shield');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('JPH: Purchase Shield error:', error);
                        console.error('JPH: XHR response:', xhr.responseText);
                        console.error('JPH: Status:', status);
                        showMessage('error', 'Network error. Please try again.');
                    }
                });
            });
            
            // Purchase Shield button - Insufficient gems
            jQuery(document).on('click', '#purchase-shield-btn-insufficient', function() {
                const cost = jQuery(this).data('cost');
                const gemBalance = jQuery(this).data('gem-balance');
                const needed = cost - gemBalance;
                
                alert(`Insufficient gems! You have ${gemBalance} 💎 but need ${cost} 💎. You need ${needed} more gems to purchase a shield.`);
            });
            
            // Test Auto-Shield button
            jQuery(document).on('click', '#test-auto-shield-btn', function() {
                const nonce = jQuery(this).data('nonce');
                
                
                if (!confirm('Test auto-shield activation? This will simulate missing practice for 2 days.')) {
                    return;
                }
                
                jQuery.ajax({
                    url: jph_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'jph_test_auto_shield',
                        nonce: nonce
                    },
                    success: function(response) {
                        
                        if (response.success) {
                            // Update UI
                            jQuery('#shield-count').text(response.data.new_shield_count);
                            
                            // Show result message
                            if (response.data.shield_used) {
                                showMessage('success', response.data.message + ' Shield count: ' + response.data.new_shield_count + ', Streak: ' + response.data.streak_maintained);
                            } else {
                                showMessage('info', response.data.message + ' Shield count: ' + response.data.new_shield_count + ', Streak: ' + response.data.streak_maintained);
                            }
                            
                            // Reload page to update all stats
                            setTimeout(() => {
                                location.reload();
                            }, 3000);
                        } else {
                            showMessage('error', response.data || 'Failed to test auto-shield');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('JPH: Test Auto-Shield error:', error);
                        console.error('JPH: XHR response:', xhr.responseText);
                        showMessage('error', 'Network error. Please try again.');
                    }
                });
            });
            
            // Repair Streak buttons
            jQuery(document).on('click', '[id^="repair-"]', function() {
                const days = jQuery(this).data('days');
                const cost = jQuery(this).data('cost');
                const nonce = jQuery('#repair-nonce').val();
                
                
                if (!confirm(`Repair ${days} day(s) of streak for ${cost} gems?`)) {
                    return;
                }
                
                jQuery.ajax({
                    url: jph_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'jph_repair_streak',
                        nonce: nonce,
                        days_to_repair: days
                    },
                    success: function(response) {
                        
                        if (response.success) {
                            // Update UI
                            jQuery('.stat-value:contains("🔥")').text('🔥' + response.data.new_streak);
                            jQuery('.stat-value:contains("💎")').text('💎 ' + response.data.new_gem_balance);
                            
                            // Hide recovery section
                            jQuery('.jph-streak-recovery').hide();
                            
                            // Show success message
                            showMessage('success', response.data.message);
                            
                            // Reload page to update all stats
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            showMessage('error', response.data || 'Failed to repair streak');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('JPH: Repair Streak error:', error);
                        showMessage('error', 'Network error. Please try again.');
                    }
                });
            });
        }
        
        // Initialize Shield Protection Accordion
        function initShieldAccordion() {
            console.log('Initializing Shield Accordion...');
            
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
                    console.log('Accordion closed');
                } else {
                    content.slideDown(300);
                    header.addClass('active');
                    icon.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    console.log('Accordion opened');
                }
            });
            
            console.log('Shield accordion initialized');
        }
        
        jphInit();
        </script>
        
        <!-- Debug Information -->
        <div id="jph-debug-info" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; font-family: monospace; font-size: 12px;">
            <h4 style="margin: 0 0 15px 0; color: #495057;">🔍 Debug Information</h4>
            <div id="jph-debug-content">
                <p>Loading debug information...</p>
            </div>
            <div style="margin-top: 10px;">
                <button type="button" onclick="jphLoadDebugInfo()" style="padding: 5px 10px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">Refresh Debug</button>
                <button type="button" onclick="jphCopyDebugInfo()" style="padding: 5px 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">📋 Copy Debug Info</button>
            </div>
        </div>
        
        <script>
        function jphLoadDebugInfo() {
            jQuery('#jph-debug-content').html('<p>Loading...</p>');
            
            jQuery.ajax({
                url: '<?php echo rest_url('jph/v1/debug-info'); ?>',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        jQuery('#jph-debug-content').html(response.debug_html);
                    } else {
                        jQuery('#jph-debug-content').html('<p style="color: red;">Error: ' + (response.message || 'Unknown error') + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    jQuery('#jph-debug-content').html('<p style="color: red;">AJAX Error: ' + error + '</p>');
                }
            });
        }
        
        // Copy debug info to clipboard
        function jphCopyDebugInfo() {
            var debugContent = jQuery('#jph-debug-content').text();
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(debugContent).then(function() {
                    // Copy successful - no alert needed
                }).catch(function(err) {
                    console.error('Failed to copy: ', err);
                    jphFallbackCopy(debugContent);
                });
            } else {
                jphFallbackCopy(debugContent);
            }
        }
        
        // Fallback copy method for older browsers
        function jphFallbackCopy(text) {
            var textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                // Copy successful - no alert needed
            } catch (err) {
                console.error('Fallback copy failed: ', err);
                alert('Copy failed. Please select and copy the debug information manually.');
            }
            document.body.removeChild(textArea);
        }
        
        
        // Update badge awards debug section
        function jphUpdateBadgeAwardsDebug() {
            if (window.jphDebugBadgeAwards) {
                var debugDiv = jQuery('#jph-badge-awards-debug');
                if (debugDiv.length) {
                    var html = '<p style="margin: 0 0 10px 0; color: #155724; font-weight: bold;">✅ ' + window.jphDebugBadgeAwards.message + '</p>';
                    html += '<p style="margin: 0 0 10px 0; color: #856404;"><strong>Timestamp:</strong> ' + new Date(window.jphDebugBadgeAwards.timestamp).toLocaleString() + '</p>';
                    html += '<p style="margin: 0 0 10px 0; color: #856404;"><strong>Badges Awarded:</strong> ' + window.jphDebugBadgeAwards.count + '</p>';
                    
                    if (window.jphDebugBadgeAwards.badges && window.jphDebugBadgeAwards.badges.length > 0) {
                        html += '<div style="margin: 10px 0;">';
                        html += '<p style="margin: 0 0 5px 0; color: #856404; font-weight: bold;">Badge Details:</p>';
                        window.jphDebugBadgeAwards.badges.forEach(function(badge, index) {
                            html += '<div style="background: #fff; border: 1px solid #ffeaa7; padding: 8px; margin: 5px 0; border-radius: 4px;">';
                            html += '<p style="margin: 0; color: #155724;"><strong>' + badge.icon + ' ' + badge.name + '</strong></p>';
                            html += '<p style="margin: 2px 0 0 0; color: #856404; font-size: 11px;">' + badge.description + '</p>';
                            html += '<p style="margin: 2px 0 0 0; color: #856404; font-size: 11px;">XP Reward: ' + badge.xp_reward + '</p>';
                            html += '</div>';
                        });
                        html += '</div>';
                    }
                    
                    debugDiv.html(html);
                }
            }
        }
        
        // Streak testing functions
        function jphTestPurchaseShield() {
            jphUpdateTestResults('Testing shield purchase...');
            
            jQuery.ajax({
                url: jph_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jph_purchase_streak_shield',
                    nonce: jQuery('#purchase-shield-btn').data('nonce')
                },
                success: function(response) {
                    if (response.success) {
                        jphUpdateTestResults('✅ Shield purchased successfully! New shield count: ' + response.data.new_shield_count + ', New gem balance: ' + response.data.new_gem_balance);
                        jphRefreshDebugInfo();
                    } else {
                        jphUpdateTestResults('❌ Shield purchase failed: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    jphUpdateTestResults('❌ Shield purchase error: ' + error);
                }
            });
        }
        
        function jphTestAutoShield() {
            jphUpdateTestResults('Testing auto-shield...');
            
            jQuery.ajax({
                url: jph_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jph_test_auto_shield',
                    nonce: jQuery('#test-auto-shield-btn').data('nonce')
                },
                success: function(response) {
                    if (response.success) {
                        jphUpdateTestResults('✅ Auto-shield test successful: ' + response.data.message);
                        jphRefreshDebugInfo();
                    } else {
                        jphUpdateTestResults('❌ Auto-shield test failed: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    jphUpdateTestResults('❌ Auto-shield test error: ' + error);
                }
            });
        }
        
        function jphTestRepairStreak(days) {
            jphUpdateTestResults('Testing streak repair for ' + days + ' day(s)...');
            
            jQuery.ajax({
                url: jph_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jph_repair_streak',
                    days: days,
                    nonce: jQuery('[id^="repair-"]').data('nonce') || 'test_nonce'
                },
                success: function(response) {
                    if (response.success) {
                        jphUpdateTestResults('✅ Streak repair successful! New streak: ' + response.data.new_streak + ', New gem balance: ' + response.data.new_gem_balance);
                        jphRefreshDebugInfo();
                    } else {
                        jphUpdateTestResults('❌ Streak repair failed: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    jphUpdateTestResults('❌ Streak repair error: ' + error);
                }
            });
        }
        
        function jphTestResetStreak() {
            if (!confirm('Are you sure you want to reset the streak? This is for testing only.')) {
                return;
            }
            
            jphUpdateTestResults('Resetting streak for testing...');
            
            jQuery.ajax({
                url: jph_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jph_reset_streak_test',
                    nonce: 'test_nonce'
                },
                success: function(response) {
                    if (response.success) {
                        jphUpdateTestResults('✅ Streak reset successful! New streak: ' + response.data.new_streak);
                        jphRefreshDebugInfo();
                    } else {
                        jphUpdateTestResults('❌ Streak reset failed: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    jphUpdateTestResults('❌ Streak reset error: ' + error);
                }
            });
        }
        
        function jphTestAddGems(amount) {
            jphUpdateTestResults('Adding ' + amount + ' gems for testing...');
            
            jQuery.ajax({
                url: jph_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jph_add_gems_test',
                    amount: amount,
                    nonce: 'test_nonce'
                },
                success: function(response) {
                    if (response.success) {
                        jphUpdateTestResults('✅ Added ' + amount + ' gems! New balance: ' + response.data.new_balance);
                        jphRefreshDebugInfo();
                    } else {
                        jphUpdateTestResults('❌ Add gems failed: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    jphUpdateTestResults('❌ Add gems error: ' + error);
                }
            });
        }
        
        function jphUpdateTestResults(message) {
            var timestamp = new Date().toLocaleTimeString();
            var resultsDiv = jQuery('#jph-streak-test-results');
            var currentContent = resultsDiv.html();
            var newContent = '<p style="margin: 0 0 5px 0; color: #495057;"><strong>[' + timestamp + ']</strong> ' + message + '</p>';
            
            if (currentContent.includes('Test results will appear here')) {
                resultsDiv.html(newContent);
            } else {
                resultsDiv.html(currentContent + newContent);
            }
        }
        
        function jphRefreshDebugInfo() {
            // Refresh the debug info after a short delay to show updated stats
            setTimeout(function() {
                jphLoadDebugInfo();
            }, 1000);
        }
        
        // Migrate user badges table structure
        
        
        // Toggle accordion sections
        function jphToggleAccordion(sectionKey) {
            const content = document.getElementById('acc_' + sectionKey);
            if (content) {
                content.classList.toggle('active');
            }
        }
        
        // Load debug info on page load
        jQuery(document).ready(function() {
            jphLoadDebugInfo();
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Test Katahdin AI Hub connection
     */
    private function test_katahdin_connection() {
        // Check if Katahdin AI Hub is available
        // Removed debug logging to prevent log spam
        return function_exists('katahdin_ai_hub');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set plugin version for future migrations
        $current_version = get_option('jph_plugin_version', '0.0.0');
        $plugin_version = '3.0.0'; // Update this when releasing new versions
        
        error_log('JPH: Starting plugin activation - Version ' . $plugin_version);
        
        // Initialize database class first
        require_once JPH_PLUGIN_PATH . 'includes/class-database.php';
        
        // Create database tables with error handling
        $database = new JPH_Database();
        
        try {
        $tables_created = $database->create_tables();
            error_log('JPH: Database tables creation attempted');
        } catch (Exception $e) {
            error_log('JPH: Database creation failed: ' . $e->getMessage());
            $tables_created = false;
        }
        
        // Create lesson_favorites table specifically (most critical)
        global $wpdb;
        $lesson_favorites_table = $wpdb->prefix . 'jph_lesson_favorites';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$lesson_favorites_table}'");
        
        if (!$table_exists) {
            error_log('JPH: lesson_favorites table missing, creating manually...');
            $manual_creation = $this->create_lesson_favorites_table_manually();
            if (!$manual_creation) {
                error_log('JPH: Failed to create lesson_favorites table manually');
            } else {
                error_log('JPH: Successfully created lesson_favorites table manually');
            }
        } else {
            error_log('JPH: lesson_favorites table already exists');
        }
        
        // Create other critical tables manually if needed
        $this->create_missing_tables_manually();
        
        // Add missing columns to existing tables
        $this->add_missing_columns_to_tables();
        
        // Add hearts_count column to user_stats if missing
        $this->add_hearts_count_column();
        
        // Add total_minutes column to user_stats if missing
        $this->add_total_minutes_column();
        
        // Update badge criteria to simplified system
        $this->update_badge_criteria_system();
        
        // Fix badges with empty badge_key  
        $this->fix_empty_badge_keys();
        
        // Remove the old "First Steps" badge that shouldn't exist anymore
        // DISABLED: This was deleting user data on plugin activation!
        // $this->remove_first_steps_badge();
                
                // Add FluentCRM event columns to badges table if missing
                $this->add_badge_fluentcrm_columns();
        
        // Fix missing badge keys
        $this->fix_missing_badge_keys();
        
        // Ensure Marathon badge exists with correct config
        $this->ensure_marathon_badge();
        
        // Run additional migrations and setup
        $this->run_activation_migrations();
        
        // Add new columns for Shield Protection & Recovery system
        $this->add_shield_protection_columns();
        
        // Add sort_order column to practice_items table if missing
        $this->add_sort_order_column();
        
        // Update plugin version
        update_option('jph_plugin_version', $plugin_version);
        
        // Force database schema update to ensure all columns exist
        $this->force_database_schema_update();
        
        // Set default options
        $this->set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Schedule daily milestone check
        if (!wp_next_scheduled('jph_daily_milestone_check')) {
            wp_schedule_event(time(), 'daily', 'jph_daily_milestone_check');
        }
        
        error_log('JPH: Plugin activated successfully - Version ' . $plugin_version);
    }
    
    /**
     * Manually create lesson_favorites table if missing
     */
    private function create_lesson_favorites_table_manually() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT(20) UNSIGNED NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `url` VARCHAR(500) NOT NULL,
            `category` VARCHAR(50) DEFAULT 'lesson',
            `description` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `category` (`category`),
            KEY `user_category` (`user_id`, `category`),
            KEY `created_at` (`created_at`),
            UNIQUE KEY `unique_user_title` (`user_id`, `title`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            error_log('JPH: Failed to create lesson_favorites table manually: ' . $wpdb->last_error);
        } else {
            error_log('JPH: Successfully created lesson_favorites table manually');
        }
        
        return $result !== false;
    }
    
    /**
     * Create missing tables manually
     */
    private function create_missing_tables_manually() {
        global $wpdb;
        
        // NEVER drop existing tables with user data!
        // Only create tables if they don't exist
        
        $tables_to_create = array(
            'jph_practice_items' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jph_practice_items` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `category` VARCHAR(50) DEFAULT 'custom',
                `description` TEXT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `sort_order` INT(11) DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `is_active` (`is_active`),
                KEY `sort_order` (`sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'jph_practice_sessions' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jph_practice_sessions` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `practice_item_id` BIGINT(20) UNSIGNED NOT NULL,
                `duration_minutes` INT(11) UNSIGNED NOT NULL,
                `sentiment_score` TINYINT(1) UNSIGNED NOT NULL,
                `improvement_detected` TINYINT(1) DEFAULT 0,
                `notes` TEXT NULL,
                `ai_analysis` TEXT NULL,
                `xp_earned` INT(11) DEFAULT 0,
                `session_hash` VARCHAR(64) NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `practice_item_id` (`practice_item_id`),
                KEY `created_at` (`created_at`),
                KEY `session_hash` (`session_hash`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'jph_user_stats' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jph_user_stats` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `total_xp` INT(11) DEFAULT 0,
                `current_level` INT(11) DEFAULT 1,
                `total_sessions` INT(11) DEFAULT 0,
                `current_streak` INT(11) DEFAULT 0,
                `longest_streak` INT(11) DEFAULT 0,
                `badges_earned` INT(11) DEFAULT 0,
                `gems_balance` INT(11) DEFAULT 0,
                `hearts_count` INT(11) DEFAULT 5,
                `streak_shield_count` INT(11) DEFAULT 0,
                `last_practice_date` DATE NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'jph_badges' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jph_badges` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `badge_key` VARCHAR(50) NOT NULL UNIQUE,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `icon` VARCHAR(10) NOT NULL,
                `category` VARCHAR(50) NOT NULL,
                `rarity` VARCHAR(20) NOT NULL,
                `xp_reward` INT(11) DEFAULT 0,
                `gem_reward` INT(11) DEFAULT 0,
                `criteria_type` VARCHAR(50) NOT NULL,
                `criteria_value` INT(11) NOT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `fluent_event_enabled` TINYINT(1) DEFAULT 0,
                `fluent_event_key` VARCHAR(100) NULL,
                `fluent_event_title` VARCHAR(200) NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `badge_key` (`badge_key`),
                KEY `category` (`category`),
                KEY `is_active` (`is_active`),
                KEY `fluent_event_enabled` (`fluent_event_enabled`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'jph_user_badges' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jph_user_badges` (
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `badge_key` VARCHAR(50) NOT NULL,
                `earned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`user_id`, `badge_key`),
                KEY `user_id` (`user_id`),
                KEY `badge_key` (`badge_key`),
                KEY `earned_at` (`earned_at`),
                FOREIGN KEY (`badge_key`) REFERENCES `{$wpdb->prefix}jph_badges`(`badge_key`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'jph_gems_transactions' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jph_gems_transactions` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `transaction_type` ENUM('earned', 'spent') NOT NULL,
                `amount` INT(11) NOT NULL,
                `source` VARCHAR(100) NOT NULL DEFAULT '',
                `description` TEXT NULL,
                `balance_after` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `transaction_type` (`transaction_type`),
                KEY `source` (`source`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'jph_lesson_favorites' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jph_lesson_favorites` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `url` VARCHAR(500) NOT NULL,
                `category` VARCHAR(50) DEFAULT 'lesson',
                `description` TEXT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `category` (`category`),
                KEY `user_category` (`user_id`, `category`),
                KEY `created_at` (`created_at`),
                UNIQUE KEY `unique_user_title` (`user_id`, `title`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );
        
        foreach ($tables_to_create as $table_name => $sql) {
            $full_table_name = $wpdb->prefix . $table_name;
            error_log("JPH: Creating table: {$full_table_name}");
            $result = $wpdb->query($sql);
            
            if ($result === false) {
                error_log("JPH: Failed to create table {$full_table_name}: " . $wpdb->last_error);
            } else {
                error_log("JPH: Successfully created table {$full_table_name}");
            }
        }
        
        // Create default badges with correct structure
        $this->create_default_badges();
    }
    
    /**
     * Create default badges with correct structure
     */
    private function create_default_badges() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_badges';
        
        $default_badges = array(
            array(
                'badge_key' => 'first_steps',
                'name' => 'First Steps',
                'description' => 'Complete your first practice session',
                'icon' => '🎯',
                'category' => 'practice',
                'rarity' => 'common',
                'xp_reward' => 50,
                'gem_reward' => 5,
                'criteria_type' => 'first_session',
                'criteria_value' => 1,
                'is_active' => 1
            ),
            array(
                'badge_key' => 'hot_streak',
                'name' => 'Hot Streak',
                'description' => 'Practice for 7 days in a row',
                'icon' => '🔥',
                'category' => 'streak',
                'rarity' => 'uncommon',
                'xp_reward' => 100,
                'gem_reward' => 10,
                'criteria_type' => 'streak_7',
                'criteria_value' => 7,
                'is_active' => 1
            ),
            array(
                'badge_key' => 'lightning',
                'name' => 'Lightning',
                'description' => 'Practice for 30 days in a row',
                'icon' => '⚡',
                'category' => 'streak',
                'rarity' => 'rare',
                'xp_reward' => 500,
                'gem_reward' => 50,
                'criteria_type' => 'streak_30',
                'criteria_value' => 30,
                'is_active' => 1
            ),
            array(
                'badge_key' => 'legend',
                'name' => 'Legend',
                'description' => 'Practice for 100 days in a row',
                'icon' => '🏆',
                'category' => 'streak',
                'rarity' => 'legendary',
                'xp_reward' => 1000,
                'gem_reward' => 100,
                'criteria_type' => 'streak_100',
                'criteria_value' => 100,
                'is_active' => 1
            ),
            array(
                'badge_key' => 'marathon',
                'name' => 'Marathon',
                'description' => 'Practice for 60+ minutes in one session',
                'icon' => '⏰',
                'category' => 'achievement',
                'rarity' => 'uncommon',
                'xp_reward' => 75,
                'gem_reward' => 15,
                'criteria_type' => 'long_session',
                'criteria_value' => 60,
                'is_active' => 1
            ),
            array(
                'badge_key' => 'rising_star',
                'name' => 'Rising Star',
                'description' => 'Report improvement 10 times',
                'icon' => '📈',
                'category' => 'improvement',
                'rarity' => 'rare',
                'xp_reward' => 200,
                'gem_reward' => 25,
                'criteria_type' => 'improvement_count',
                'criteria_value' => 10,
                'is_active' => 1
            )
        );
        
        foreach ($default_badges as $badge) {
            $result = $wpdb->insert(
                $table_name,
                $badge,
                array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d')
            );
            
            if ($result === false) {
                error_log("JPH: Failed to create badge {$badge['badge_key']}: " . $wpdb->last_error);
            } else {
                error_log("JPH: Successfully created badge {$badge['badge_key']}");
            }
        }
    }
    
    /**
     * Add FluentCRM event columns to badges table if missing
     */
    private function add_badge_fluentcrm_columns() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_badges';
        
        // Check if table exists first
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
        if (!$table_exists) {
            error_log('JPH: badges table does not exist, will be created with FluentCRM columns');
            return;
        }
        
        // Check and add each column if missing
        $columns_to_add = array(
            'fluent_event_enabled' => "ADD COLUMN fluent_event_enabled TINYINT(1) DEFAULT 0 AFTER is_active",
            'fluent_event_key' => "ADD COLUMN fluent_event_key VARCHAR(100) NULL AFTER fluent_event_enabled",
            'fluent_event_title' => "ADD COLUMN fluent_event_title VARCHAR(200) NULL AFTER fluent_event_key"
        );
        
        foreach ($columns_to_add as $column_name => $sql_part) {
            $column_exists = $wpdb->get_results($wpdb->prepare(
                "SHOW COLUMNS FROM {$table_name} LIKE %s",
                $column_name
            ));
            
            if (empty($column_exists)) {
                $result = $wpdb->query("ALTER TABLE {$table_name} {$sql_part}");
                if ($result !== false) {
                    error_log("JPH: Added {$column_name} column to badges table");
                } else {
                    error_log("JPH: Failed to add {$column_name} column to badges table");
                }
            }
        }
        
        // Add index for fluent_event_enabled
        $index_exist = $wpdb->get_var("SHOW INDEX FROM {$table_name} WHERE Key_name = 'fluent_event_enabled'");
        if (empty($index_exist)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD INDEX fluent_event_enabled (fluent_event_enabled)");
        }
    }
    
    /**
     * Add hearts-count column to user_stats table if missing
     */
    private function add_hearts_count_column() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        
        // Check if hearts_count column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SHOW COLUMNS FROM {$table_name} LIKE %s",
            'hearts_count'
        ));
        
        if (empty($column_exists)) {
            // Add hearts_count column
            $result = $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN hearts_count INT(11) DEFAULT 5 AFTER gems_balance");
            
            if ($result !== false) {
                // Update existing records to have hearts_count = 5
                $wpdb->query("UPDATE {$table_name} SET hearts_count = 5 WHERE hearts_count IS NULL");
            }
        }
    }
    
    /**
     * Add total_minutes column to user_stats table if missing
     */
    private function add_total_minutes_column() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        
        // Check if total_minutes column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SHOW COLUMNS FROM {$table_name} LIKE %s",
            'total_minutes'
        ));
        
        if (empty($column_exists)) {
            // Add total_minutes column
            $result = $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN total_minutes INT(11) DEFAULT 0 AFTER total_sessions");
            
            if ($result !== false) {
                // Update existing records to have total_minutes = 0
                $wpdb->query("UPDATE {$table_name} SET total_minutes = 0 WHERE total_minutes IS NULL");
                error_log('JPH: Added total_minutes column to user_stats table');
            } else {
                error_log('JPH: Failed to add total_minutes column to user_stats table. Error: ' . $wpdb->last_error);
            }
        }
    }
    
    /**
     * Check for badge key issues and show admin notice
     */
    public function check_badge_key_issues() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        global $wpdb;
        
        // Check for badges with problematic keys
        $problematic_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}jph_badges WHERE badge_key = '' OR badge_key IS NULL OR TRIM(badge_key) = ''"
        );
        
        if ($problematic_count > 0) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>⚠️ JazzEdge Practice Hub:</strong> Found <?php echo $problematic_count; ?> badge(s) with missing badge keys. 
                <a href="<?php echo admin_url('admin.php?page=jph-badges'); ?>">Fix this issue</a> by reactivating the plugin or manually fixing the badges.</p>
            </div>
            <?php
        }
    }
    
    /**
     * Generate unique badge_key from badge name
     */
    private function generate_badge_key($name) {
        global $wpdb;
        
        // Create base key from name
        $base_key = sanitize_title($name);
        
        // Ensure uniqueness
        $counter = 0;
        $badge_key = $base_key;
        
        while ($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}jph_badges WHERE badge_key = %s",
            $badge_key
        )) > 0) {
            $counter++;
            $badge_key = $base_key . '_' . $counter;
        }
        
        return $badge_key;
    }
    
    /**
     * Fix badges with empty badge_key by preserving valid badges and removing broken ones
     */
    private function fix_empty_badge_keys() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_badges';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
        
        if (!$table_exists) {
            error_log('JPH: badges table does not exist for badge key fix');
            return;
        }
        
        // Find badges with empty, null, or whitespace-only badge_key
        $problematic_badges = $wpdb->get_results(
            "SELECT id, name, badge_key FROM {$table_name} WHERE badge_key = '' OR badge_key IS NULL OR TRIM(badge_key) = ''",
            ARRAY_A
        );
        
        if (!empty($problematic_badges)) {
            foreach ($problematic_badges as $badge) {
                // Try to generate a badge_key from the name
                $suggested_key = sanitize_title($badge['name']);
                
                if (!empty($suggested_key)) {
                    // Check if this key already exists
                    $existing_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$table_name} WHERE badge_key = %s AND id != %d",
                        $suggested_key,
                        $badge['id']
                    ));
                    
                    if ($existing_count == 0) {
                        // Update the badge with the generated key
                        $wpdb->update(
                            $table_name,
                            array('badge_key' => $suggested_key),
                            array('id' => $badge['id']),
                            array('%s'),
                            array('%d')
                        );
                        error_log("JPH: Fixed badge '{$badge['name']}' (ID: {$badge['id']}) with key: '{$suggested_key}'");
                    } else {
                        // Key conflicts, skip this badge to avoid deleting user data
                        error_log("JPH: Skipped badge '{$badge['name']}' (ID: {$badge['id']}) - cannot generate unique key (preserving user data)");
                    }
                } else {
                    // Cannot generate key, skip this badge to avoid deleting user data
                    error_log("JPH: Skipped badge '{$badge['name']}' (ID: {$badge['id']}) - cannot generate key from name (preserving user data)");
                }
            }
            error_log('JPH: Processed ' . count($problematic_badges) . ' badges with problematic keys');
        } else {
            error_log('JPH: No badges with problematic keys found');
        }
    }
    
    /**
     * Remove the old "First Steps" badge that shouldn't exist anymore
     */
    private function remove_first_steps_badge() {
        global $wpdb;
        
        $badge_result = $wpdb->get_row(
            "SELECT id, name FROM {$wpdb->prefix}jph_badges WHERE name = 'First Steps'",
            ARRAY_A
        );
        
        if ($badge_result) {
            // Delete user badge awards for this badge first
            $user_badges_deleted = $wpdb->delete(
                $wpdb->prefix . 'jph_user_badges',
                array('badge_key' => 'first_steps'),
                array('%s')
            );
            
            // Delete the badge itself
            $badge_deleted = $wpdb->delete(
                $wpdb->prefix . 'jph_badges',
                array('id' => $badge_result['id']),
                array('%d')
            );
            
            if ($badge_deleted) {
                error_log("JPH: Removed deprecated 'First Steps' badge (ID: {$badge_result['id']}) and {$user_badges_deleted} user awards");
            }
        }
    }
    
    /**
     * Delete badge and cascade delete related data
     */
    private function delete_badge_cascade($badge_id) {
        global $wpdb;
        
        // Delete user badge awards first (foreign key constraint)
        $wpdb->delete(
            $wpdb->prefix . 'jph_user_badges',
            array('badge_key' => ''), // This creates inconsistency, but it's cleanup
            array('%s')
        );
        
        // Delete orphaned badge awards for this badge
        $wpdb->delete(
            $wpdb->prefix . 'jph_user_badges',
            array('badge_key' => ''), // Will be cleaned up by cascade
            array('%s')
        );
        
        // Delete the badge itself
        $wpdb->delete(
            $wpdb->prefix . 'jph_badges',
            array('id' => $badge_id),
            array('%d')
        );
    }
    
    /**
     * Update badge criteria to simplified system
     */
    private function update_badge_criteria_system() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_badges';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
        
        if (!$table_exists) {
            error_log('JPH: badges table does not exist for criteria update');
            return;
        }
        
        // Update existing badges to use new simplified criteria system
        $badge_updates = array(
            'first_steps' => array(
                'criteria_type' => 'practice_sessions',
                'criteria_value' => 1,
                'name' => 'First Steps',
                'description' => 'Complete your first practice session'
            ),
            'marathon' => array(
                'criteria_type' => 'total_time',
                'criteria_value' => 60,
                'name' => 'Marathon',
                'description' => 'Practice for 60+ minutes in one session'
            ),
            'rising_star' => array(
                'criteria_type' => 'improvement_count',
                'criteria_value' => 10,
                'name' => 'Rising Star',
                'description' => 'Report improvement 10 times'
            ),
            'hot_streak' => array(
                'criteria_type' => 'streak',
                'criteria_value' => 7,
                'name' => 'Hot Streak',
                'description' => 'Practice for 7 days in a row'
            ),
            'legend' => array(
                'criteria_type' => 'streak',
                'criteria_value' => 100,
                'name' => 'Legend',
                'description' => 'Practice for 100 days in a row'
            ),
            'lightning' => array(
                'criteria_type' => 'streak',
                'criteria_value' => 30,
                'name' => 'Lightning', 
                'description' => 'Practice for 30 days in a row'
            )
        );
        
        foreach ($badge_updates as $badge_key => $update_data) {
            $result = $wpdb->update(
                $table_name,
                array(
                    'criteria_type' => $update_data['criteria_type'],
                    'criteria_value' => $update_data['criteria_value'],
                    'name' => $update_data['name'],
                    'description' => $update_data['description'],
                    'updated_at' => current_time('mysql')
                ),
                array('badge_key' => $badge_key),
                array('%s', '%d', '%s', '%s', '%s'),
                array('%s')
            );
            
            if ($result !== false) {
                error_log("JPH: Updated badge criteria for {$badge_key} to {$update_data['criteria_type']} >= {$update_data['criteria_value']}");
            } else {
                error_log("JPH: Failed to update badge criteria for {$badge_key}. Error: {$wpdb->last_error}");
            }
        }
    }
    
    /**
     * Count practice days in a given period (rolling window)
     */
    private function count_practice_days_in_period($user_id, $days) {
        global $wpdb;
        
        $start_date = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $sessions = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT DATE(created_at) as practice_date 
             FROM {$wpdb->prefix}jph_practice_sessions 
             WHERE user_id = %d AND created_at >= %s 
             ORDER BY practice_date",
            $user_id,
            $start_date
        ));
        
        return count($sessions);
    }
    
    /**
     * Count weekend practice sessions (Saturday/Sunday)
     */
    private function count_weekend_practice_sessions($user_id) {
        global $wpdb;
        
        $sessions = $wpdb->get_results($wpdb->prepare(
            "SELECT COUNT(*) as weekend_count 
             FROM {$wpdb->prefix}jph_practice_sessions 
             WHERE user_id = %d 
             AND DAYOFWEEK(created_at) IN (1, 7)",
            $user_id
        ));
        
        return intval($sessions[0]->weekend_count ?? 0);
    }
    
    /**
     * Add missing columns to existing tables
     */
    private function add_missing_columns_to_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_practice_sessions';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
        if (!$table_exists) {
            return; // Table doesn't exist, will be created by create_missing_tables_manually
        }
        
        // Check for missing columns and add them
        $columns_to_add = array(
            'sentiment_score' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 3',
            'improvement_detected' => 'TINYINT(1) DEFAULT 0',
            'ai_analysis' => 'TEXT NULL',
            'xp_earned' => 'INT(11) DEFAULT 0',
            'session_hash' => 'VARCHAR(64) NOT NULL DEFAULT ""'
        );
        
        // Also check gems_transactions table for missing columns
        $gems_table_name = $wpdb->prefix . 'jph_gems_transactions';
        $gems_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$gems_table_name}'");
        
        if ($gems_table_exists) {
            $gems_columns_to_add = array(
                'balance_after' => 'INT(11) NOT NULL DEFAULT 0'
            );
            
            foreach ($gems_columns_to_add as $column_name => $column_definition) {
                $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$gems_table_name} LIKE '{$column_name}'");
                
                if (empty($column_exists)) {
                    error_log("JPH: Adding missing column {$column_name} to {$gems_table_name}");
                    $sql = "ALTER TABLE {$gems_table_name} ADD COLUMN {$column_name} {$column_definition}";
                    $result = $wpdb->query($sql);
                    
                    if ($result === false) {
                        error_log("JPH: Failed to add column {$column_name}: " . $wpdb->last_error);
                    } else {
                        error_log("JPH: Successfully added column {$column_name}");
                    }
                }
            }
        }
        
        foreach ($columns_to_add as $column_name => $column_definition) {
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE '{$column_name}'");
            
            if (empty($column_exists)) {
                error_log("JPH: Adding missing column {$column_name} to {$table_name}");
                $sql = "ALTER TABLE {$table_name} ADD COLUMN {$column_name} {$column_definition}";
                $result = $wpdb->query($sql);
                
                if ($result === false) {
                    error_log("JPH: Failed to add column {$column_name}: " . $wpdb->last_error);
                } else {
                    error_log("JPH: Successfully added column {$column_name}");
                }
            }
        }
        
        // Add indexes for new columns
        $indexes_to_add = array(
            'session_hash' => 'KEY `session_hash` (`session_hash`)'
        );
        
        foreach ($indexes_to_add as $index_name => $index_definition) {
            $index_exists = $wpdb->get_results("SHOW INDEX FROM {$table_name} WHERE Key_name = '{$index_name}'");
            
            if (empty($index_exists)) {
                error_log("JPH: Adding missing index {$index_name} to {$table_name}");
                $sql = "ALTER TABLE {$table_name} ADD {$index_definition}";
                $result = $wpdb->query($sql);
                
                if ($result === false) {
                    error_log("JPH: Failed to add index {$index_name}: " . $wpdb->last_error);
                } else {
                    error_log("JPH: Successfully added index {$index_name}");
                }
            }
        }
    }
    
    /**
     * Fix missing badge keys for existing badges
     */
    private function fix_missing_badge_keys() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_badges';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
        if (!$table_exists) {
            return; // Table doesn't exist
        }
        
        // Check if badge_key column exists
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'badge_key'");
        if (empty($column_exists)) {
            error_log('JPH: Adding badge_key column to badges table');
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN badge_key VARCHAR(50) NOT NULL DEFAULT ''");
        }
        
        // Get all badges without badge_key
        $badges_without_key = $wpdb->get_results("SELECT id, name FROM {$table_name} WHERE badge_key = '' OR badge_key IS NULL", ARRAY_A);
        
        if (empty($badges_without_key)) {
            error_log('JPH: All badges already have badge_key values');
            return;
        }
        
        error_log('JPH: Found ' . count($badges_without_key) . ' badges without badge_key');
        
        // Define badge key mappings based on badge names
        $badge_key_mappings = array(
            'First Steps' => 'first_steps',
            'Marathon' => 'marathon',
            'Rising Star' => 'rising_star',
            'Hot Streak' => 'hot_streak',
            'Lightning' => 'lightning',
            'Legend' => 'legend'
        );
        
        foreach ($badges_without_key as $badge) {
            $badge_name = $badge['name'];
            $badge_id = $badge['id'];
            
            // Generate badge key from name if not in mapping
            if (isset($badge_key_mappings[$badge_name])) {
                $badge_key = $badge_key_mappings[$badge_name];
            } else {
                // Convert name to lowercase, replace spaces with underscores
                $badge_key = strtolower(str_replace(' ', '_', $badge_name));
            }
            
            // Update the badge with the key
            $result = $wpdb->update(
                $table_name,
                array('badge_key' => $badge_key),
                array('badge_key' => ''), // Update badges that don't have a key yet
                array('%s'),
                array('%s')
            );
            
            if ($result !== false) {
                error_log("JPH: Updated badge '{$badge_name}' with key '{$badge_key}'");
            } else {
                error_log("JPH: Failed to update badge '{$badge_name}': " . $wpdb->last_error);
            }
        }
    }
    
    /**
     * Ensure Marathon badge exists with correct configuration
     */
    private function ensure_marathon_badge() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_badges';
        
        // Check if Marathon badge exists
        $marathon_badge = $wpdb->get_row("SELECT * FROM {$table_name} WHERE name = 'Marathon'", ARRAY_A);
        
        if (!$marathon_badge) {
            // Create Marathon badge
            $wpdb->insert($table_name, array(
                'badge_key' => 'marathon',
                'name' => 'Marathon',
                'description' => 'Practice for 60+ minutes in one session',
                'icon' => '🏃',
                'category' => 'achievement',
                'rarity' => 'rare',
                'xp_reward' => 75,
                'gem_reward' => 15,
                'criteria_type' => 'long_session',
                'criteria_value' => 60,
                'is_active' => 1
            ));
        } else {
            // Update existing Marathon badge to ensure correct config
            $wpdb->update(
                $table_name,
                array(
                    'badge_key' => 'marathon',
                    'criteria_type' => 'long_session',
                    'criteria_value' => 60,
                    'is_active' => 1
                ),
                array('name' => 'Marathon')
            );
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear any scheduled events
        wp_clear_scheduled_hook('jph_daily_cleanup');
        wp_clear_scheduled_hook('jph_daily_milestone_check');
        
        error_log('JPH: Plugin deactivated');
    }
    
    /**
     * Add Shield Protection & Recovery columns to user stats table
     */
    private function add_shield_protection_columns() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        
        // Check if columns exist
        $columns = $wpdb->get_col("DESCRIBE {$table_name}");
        
        // Add streak_shield_count column if it doesn't exist
        if (!in_array('streak_shield_count', $columns)) {
            $result = $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN streak_shield_count INT(11) UNSIGNED DEFAULT 0 COMMENT 'Number of active streak shields'");
            if ($result !== false) {
                error_log('JPH: Added streak_shield_count column');
            } else {
                error_log('JPH: Failed to add streak_shield_count column: ' . $wpdb->last_error);
            }
        }
        
        // Add last_streak_recovery_date column if it doesn't exist
        if (!in_array('last_streak_recovery_date', $columns)) {
            $result = $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN last_streak_recovery_date DATE NULL COMMENT 'Last date streak recovery was used'");
            if ($result !== false) {
                error_log('JPH: Added last_streak_recovery_date column');
            } else {
                error_log('JPH: Failed to add last_streak_recovery_date column: ' . $wpdb->last_error);
            }
        }
        
        // Add streak_recovery_count_this_week column if it doesn't exist
        if (!in_array('streak_recovery_count_this_week', $columns)) {
            $result = $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN streak_recovery_count_this_week INT(11) UNSIGNED DEFAULT 0 COMMENT 'Number of streak recoveries used this week'");
            if ($result !== false) {
                error_log('JPH: Added streak_recovery_count_this_week column');
            } else {
                error_log('JPH: Failed to add streak_recovery_count_this_week column: ' . $wpdb->last_error);
            }
        }
        
        error_log('JPH: Streak protection columns migration completed');
    }
    
    /**
     * Add sort_order column to practice_items table if missing
     */
    private function add_sort_order_column() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_practice_items';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
        if (!$table_exists) {
            error_log('JPH: practice_items table does not exist, skipping sort_order migration');
            return;
        }
        
        // Check if sort_order column exists
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'sort_order'");
        
        if (empty($column_exists)) {
            error_log('JPH: Adding sort_order column to practice_items table');
            
            $sql = "ALTER TABLE {$table_name} ADD COLUMN sort_order INT(11) DEFAULT 0";
            $result = $wpdb->query($sql);
            
            if ($result === false) {
                error_log('JPH: Failed to add sort_order column: ' . $wpdb->last_error);
            } else {
                error_log('JPH: Successfully added sort_order column');
                
                // Add index for sort_order column
                $index_sql = "ALTER TABLE {$table_name} ADD INDEX sort_order (sort_order)";
                $index_result = $wpdb->query($index_sql);
                
                if ($index_result === false) {
                    error_log('JPH: Failed to add sort_order index: ' . $wpdb->last_error);
                } else {
                    error_log('JPH: Successfully added sort_order index');
                }
                
                // Initialize sort_order for existing items (order by creation date)
                $init_sql = "UPDATE {$table_name} SET sort_order = id WHERE sort_order = 0";
                $init_result = $wpdb->query($init_sql);
                
                if ($init_result === false) {
                    error_log('JPH: Failed to initialize sort_order values: ' . $wpdb->last_error);
                } else {
                    error_log('JPH: Successfully initialized sort_order values');
                }
            }
        } else {
            error_log('JPH: sort_order column already exists in practice_items table');
        }
    }
    
    /**
     * Force database schema update to ensure all columns exist
     */
    private function force_database_schema_update() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jph_user_stats';
        
        // Get current columns
        $columns = $wpdb->get_col("DESCRIBE {$table_name}");
        error_log('JPH: Current columns: ' . implode(', ', $columns));
        
        // Required columns for Streak Shield & Recovery
        $required_columns = array(
            'streak_shield_count' => "INT(11) UNSIGNED DEFAULT 0 COMMENT 'Number of active streak shields'",
            'last_streak_recovery_date' => "DATE NULL COMMENT 'Last date streak recovery was used'",
            'streak_recovery_count_this_week' => "INT(11) UNSIGNED DEFAULT 0 COMMENT 'Number of streak recoveries used this week'"
        );
        
        // Add missing columns
        foreach ($required_columns as $column_name => $column_definition) {
            if (!in_array($column_name, $columns)) {
                $sql = "ALTER TABLE {$table_name} ADD COLUMN {$column_name} {$column_definition}";
                $result = $wpdb->query($sql);
                
                if ($result !== false) {
                    error_log("JPH: Successfully added column: {$column_name}");
                } else {
                    error_log("JPH: Failed to add column {$column_name}: " . $wpdb->last_error);
                }
            } else {
                error_log("JPH: Column {$column_name} already exists");
            }
        }
        
        // Verify all columns exist
        $updated_columns = $wpdb->get_col("DESCRIBE {$table_name}");
        error_log('JPH: Updated columns: ' . implode(', ', $updated_columns));
        
        // Check if all required columns are present
        $missing_columns = array_diff(array_keys($required_columns), $updated_columns);
        if (empty($missing_columns)) {
            error_log('JPH: All required columns are present');
        } else {
            error_log('JPH: Missing columns: ' . implode(', ', $missing_columns));
        }
    }
    
    /**
     * Run activation migrations
     */
    private function run_activation_migrations() {
        // These migrations are now handled by the JPH_Database class
        // But we can add any additional setup here if needed
        
        // Ensure all tables have proper indexes and constraints
        $this->ensure_table_integrity();
        
        // Set up default badges if none exist
        $this->setup_default_badges();
        
        error_log('JPH: Activation migrations completed');
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        // Default webhook/event tracking settings
        $default_webhook_settings = array(
            'enabled' => false,
            'provider' => 'fluentcrm',
            'prefix' => 'jph_',
            'milestones' => array(
                'first_practice_logged' => true,
                'first_practice_item_added' => true,
                'first_badge_earned' => true,
                'practice_streak_3' => true,
                'practice_streak_7' => true,
                'practice_streak_30' => true,
                'practice_streak_100' => true,
                'total_practice_sessions_10' => true,
                'total_practice_sessions_50' => true,
                'total_practice_sessions_100' => true,
                'total_practice_sessions_500' => true,
                'total_practice_sessions_1000' => true,
                'total_practice_time_1_hour' => true,
                'total_practice_time_10_hours' => true,
                'total_practice_time_100_hours' => true
            )
        );
        
        if (!get_option('jph_webhook_settings')) {
            update_option('jph_webhook_settings', $default_webhook_settings);
        }
        
        // Default gamification settings
        $default_gamification_settings = array(
            'xp_per_minute' => 1,
            'streak_bonus_multiplier' => 1.5,
            'max_streak_bonus' => 2.0
        );
        
        if (!get_option('jph_gamification_settings')) {
            update_option('jph_gamification_settings', $default_gamification_settings);
        }
        
        error_log('JPH: Default options set');
    }
    
    /**
     * Ensure table integrity
     */
    private function ensure_table_integrity() {
        global $wpdb;
        
        // Check and add display_order column to badges table if missing
        $badges_table = $wpdb->prefix . 'jph_badges';
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$badges_table} LIKE 'display_order'");
        
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE {$badges_table} ADD COLUMN display_order INT(11) DEFAULT 0 AFTER is_active");
            $wpdb->query("ALTER TABLE {$badges_table} ADD INDEX display_order (display_order)");
            
            // Set initial display_order values for existing badges
            $badges = $wpdb->get_results("SELECT id FROM {$badges_table} ORDER BY id ASC");
            if ($badges) {
                $order = 1;
                foreach ($badges as $badge) {
                    $wpdb->update(
                        $badges_table,
                        array('display_order' => $order),
                        array('id' => $badge->id),
                        array('%d'),
                        array('%d')
                    );
                    $order++;
                }
            }
            
            error_log('JPH: Added display_order column to badges table');
        }
    }
    
    /**
     * Setup default badges if none exist
     */
    private function setup_default_badges() {
        global $wpdb;
        
        $badges_table = $wpdb->prefix . 'jph_badges';
        $badge_count = $wpdb->get_var("SELECT COUNT(*) FROM {$badges_table}");
        
        if ($badge_count == 0) {
            $default_badges = array(
                array(
                    'name' => 'First Steps',
                    'description' => 'Complete your first practice session',
                    'icon' => '🎵',
                    'category' => 'onboarding',
                    'rarity' => 'common',
                    'xp_reward' => 10,
                    'gem_reward' => 5,
                    'criteria_type' => 'practice_sessions',
                    'criteria_value' => 1,
                    'is_active' => 1,
                    'display_order' => 1
                ),
                array(
                    'name' => 'Getting Started',
                    'description' => 'Add your first practice item',
                    'icon' => '📝',
                    'category' => 'onboarding',
                    'rarity' => 'common',
                    'xp_reward' => 15,
                    'gem_reward' => 5,
                    'criteria_type' => 'practice_items',
                    'criteria_value' => 1,
                    'is_active' => 1,
                    'display_order' => 2
                ),
                array(
                    'name' => 'Streak Master',
                    'description' => 'Maintain a 7-day practice streak',
                    'icon' => '🔥',
                    'category' => 'consistency',
                    'rarity' => 'rare',
                    'xp_reward' => 100,
                    'gem_reward' => 25,
                    'criteria_type' => 'streak',
                    'criteria_value' => 7,
                    'is_active' => 1,
                    'display_order' => 3
                ),
                array(
                    'name' => 'Practice Warrior',
                    'description' => 'Complete 50 practice sessions',
                    'icon' => '⚔️',
                    'category' => 'progress',
                    'rarity' => 'epic',
                    'xp_reward' => 500,
                    'gem_reward' => 100,
                    'criteria_type' => 'total_sessions',
                    'criteria_value' => 50,
                    'is_active' => 1,
                    'display_order' => 4
                )
            );
            
            foreach ($default_badges as $badge) {
                $wpdb->insert($badges_table, $badge);
            }
            
            error_log('JPH: Created ' . count($default_badges) . ' default badges');
        }
    }
    
    /**
     * AJAX handler: Create tables
     */
    public function ajax_create_tables() {
        check_ajax_referer('jph_database', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $database = new JPH_Database();
            
            $result = $database->create_tables();
            
            if ($result) {
                wp_send_json_success('Database tables created successfully!');
            } else {
                wp_send_json_error('Failed to create database tables');
            }
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler: Check tables
     */
    public function ajax_check_tables() {
        check_ajax_referer('jph_database', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            require_once JPH_PLUGIN_PATH . 'includes/class-database.php';
            require_once JPH_PLUGIN_PATH . 'includes/class-gamification.php';
            $database = new JPH_Database();
            
            $tables_exist = $database->tables_exist();
            $table_names = $database->get_table_names();
            
            $result = "Tables exist: " . ($tables_exist ? "Yes" : "No") . "\n\n";
            $result .= "Table names:\n";
            foreach ($table_names as $key => $table_name) {
                $result .= "- {$key}: {$table_name}\n";
            }
            
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler: Show schema
     */
    public function ajax_show_schema() {
        check_ajax_referer('jph_database', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            require_once JPH_PLUGIN_PATH . 'includes/database-schema.php';
            $schema = JPH_Database_Schema::get_schema();
            
            $result = "Database Schema Overview:\n\n";
            foreach ($schema as $table_name => $table_schema) {
                $result .= "Table: {$table_name}\n";
                $result .= "Purpose: " . ($table_schema['columns']['id']['description'] ?? 'N/A') . "\n";
                $result .= "Columns: " . count($table_schema['columns']) . "\n";
                $result .= "Indexes: " . (isset($table_schema['indexes']) ? count($table_schema['indexes']) : 0) . "\n\n";
            }
            
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
}

// Initialize the plugin
function jazzedge_practice_hub() {
    return JazzEdge_Practice_Hub::get_instance();
}

// Start the plugin
jazzedge_practice_hub();
